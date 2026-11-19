<?php

use App\Components\Session;
use App\Model\InicioM;


/* ===================================================== */
/* GESTIÓN DE INICIO */
/* ===================================================== */

/* Identificar el método y el formato de la solicitud */
$metodoSolicitud = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$tipoContenido = strtolower(
    trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0])
);

$esContenidoJson = $tipoContenido === 'application/json';

$esPeticionAjax = $esContenidoJson ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';


/* ===================================================== */
/* RESPUESTAS DEL CONTROLADOR */
/* ===================================================== */

$responderJson = static function (
    array $datos,
    int $estado = 200
): void {
    http_response_code($estado);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
};


/* ===================================================== */
/* COMPROBACIÓN DE LA SESIÓN */
/* ===================================================== */

if (!Session\estaAutenticada()) {
    if (
        $esPeticionAjax ||
        $metodoSolicitud !== 'GET' ||
        isset($_GET['accion'])
    ) {
        $responderJson([
            'ok' => false,
            'mensaje' => 'Debe iniciar sesión para continuar.',
            'redireccion' => 'Index.php?url=login'
        ], 401);
    }

    header('Location: Index.php?url=login');

    exit;
}


/* ===================================================== */
/* RECEPCIÓN DE LA SOLICITUD */
/* ===================================================== */

try {
    if (!in_array($metodoSolicitud, ['GET', 'POST'], true)) {
        header('Allow: GET, POST');

        $responderJson([
            'ok' => false,
            'mensaje' => 'Método no permitido.'
        ], 405);
    }


    /* Cargar la vista cuando se abre Inicio mediante una navegación normal */
    if (
        $metodoSolicitud === 'GET' &&
        !$esPeticionAjax &&
        !isset($_GET['accion'])
    ) {
        require_once __DIR__ . '/../View/InicioV.php';

        return;
    }


    $datosSolicitud = $metodoSolicitud === 'GET' ? $_GET : $_POST;


    /* Leer las consultas enviadas como un objeto JSON */
    if ($metodoSolicitud === 'POST' && $esContenidoJson) {
        $contenidoSolicitud = file_get_contents('php://input');

        if ($contenidoSolicitud === false) {
            throw new RuntimeException(
                'No fue posible leer el contenido de la solicitud.'
            );
        }

        $entrada = json_decode(
            $contenidoSolicitud,
            false,
            32,
            JSON_THROW_ON_ERROR
        );

        if (!$entrada instanceof stdClass) {
            throw new InvalidArgumentException(
                'La solicitud debe contener un objeto JSON.'
            );
        }

        $datosSolicitud = get_object_vars($entrada);
    }


    /* Aceptar únicamente la consulta de los datos del panel */
    $accion = $datosSolicitud['accion'] ??
        ($metodoSolicitud === 'GET' ? 'consultar' : '');

    if (!is_string($accion) || $accion !== 'consultar') {
        throw new InvalidArgumentException(
            'La acción solicitada no es válida.'
        );
    }


    /* El modelo utilizará el mes actual cuando no se indique otro */
    $mes = $datosSolicitud['mes'] ?? null;

    if ($mes !== null && !is_string($mes)) {
        throw new InvalidArgumentException(
            'El mes solicitado no tiene un formato válido.'
        );
    }

    if ($mes !== null) {
        $mes = trim($mes);
    }


    /* ================================================= */
    /* CONSULTAR LOS DATOS DE INICIO */
    /* ================================================= */

    /* Identificar al usuario para consultar únicamente sus citas personales */
    $usuarioSesion = Session\obtenerUsuario();
    $idRol = (int) ($usuarioSesion['id_rol'] ?? 0);

    /* El Administrador del Sistema no consulta estadísticas operativas */
    if ($idRol === 3) {
        $responderJson([
            'ok' => false,
            'mensaje' => 'No tiene permiso para consultar los indicadores operativos.'
        ], 403);
    }

    $idEspecialista = $idRol === 2
        ? (int) ($usuarioSesion['id_usuario'] ?? 0)
        : null;

    /* Liberar la sesión durante las consultas de lectura */
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    $modeloInicio = new InicioM();

    $datosInicio = $modeloInicio->consultar($mes, $idEspecialista);

    $responderJson([
        'ok' => true,
        'inicio' => $datosInicio
    ]);

} catch (JsonException $error) {
    $responderJson([
        'ok' => false,
        'mensaje' => 'Los datos enviados no contienen un JSON válido.'
    ], 422);

} catch (InvalidArgumentException $error) {
    $responderJson([
        'ok' => false,
        'mensaje' => $error->getMessage()
    ], 422);

} catch (Throwable $error) {
    /* Registrar el detalle técnico sin mostrar información interna */
    error_log('Inicio: ' . $error->getMessage());

    $responderJson([
        'ok' => false,
        'mensaje' => 'No fue posible consultar los datos de Inicio. Intente nuevamente.'
    ], 500);
}
