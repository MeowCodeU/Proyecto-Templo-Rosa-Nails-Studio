<?php

use App\Model\LoginM;


/* ===================================================== */
/* VALIDACIÓN DE LA CLAVE UNIVERSAL */
/* ===================================================== */

/* Variables utilizadas en la vista */
$mensajeClaveAdmin = '';
$tipoMensajeClaveAdmin = 'error';
$erroresClaveAdmin = [];
$modeloLogin = null;


/* Duración de la autorización para registrar una cuenta */
$duracionAutorizacion = 600;


/* Identificar las solicitudes asíncronas */
$esPeticionAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';


/* Enviar la respuesta y finalizar la solicitud */
$responderJson = static function (array $datos, int $estado = 200): void {
    http_response_code($estado);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    exit;
};


/* Retirar una autorización que haya vencido */
if (
    isset($_SESSION['registro_usuario_autorizado_hasta']) &&
    $_SESSION['registro_usuario_autorizado_hasta'] < time()
) {
    unset($_SESSION['registro_usuario_autorizado_hasta']);
}


/* ===================================================== */
/* PROCESAR EL FORMULARIO */
/* ===================================================== */

try {
    $datosFormulario = $_POST;
    $metodoSolicitud = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $tipoContenido = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));


    /* Leer los datos enviados en el cuerpo de la solicitud */
    if ($metodoSolicitud === 'POST' && $tipoContenido === 'application/json') {
        $datosFormulario = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($datosFormulario)) {
            throw new \InvalidArgumentException('Los datos enviados no tienen un formato válido.');
        }
    }


    /* Comprobar la acción solicitada */
    if ($metodoSolicitud === 'POST') {
        if (($datosFormulario['accion'] ?? '') !== 'validar_clave_universal') {
            throw new \InvalidArgumentException('La solicitud enviada no es válida.');
        }


        /* Retirar el permiso anterior antes de volver a comprobar la clave */
        unset($_SESSION['registro_usuario_autorizado_hasta']);


        /* Delegar la comprobación al modelo y al trait de usuarios */
        $modeloLogin = new LoginM();
        $autorizado = $modeloLogin->comprobarClaveUniversal($datosFormulario['clave_universal'] ?? '');

        if ($autorizado) {

            /* Renovar el identificador antes de conceder la autorización */
            if (!@session_regenerate_id(true)) {
                throw new \RuntimeException('No fue posible renovar el identificador de la sesión.');
            }

            $_SESSION['registro_usuario_autorizado_hasta'] = time() + $duracionAutorizacion;


            /* Confirmar el acceso al formulario de registro */
            if ($esPeticionAjax) {
                $responderJson([
                    'ok' => true,
                    'redireccion' => 'Index.php?url=registroUsuario'
                ]);
            }

            header('Location: Index.php?url=registroUsuario');
            exit;
        }

        $mensajeClaveAdmin = $modeloLogin->obtenerMensajeAcceso();
        $erroresClaveAdmin = $modeloLogin->obtenerErroresValidacion();


        /* Informar los errores sin cargar la vista */
        if ($esPeticionAjax) {
            $responderJson([
                'ok' => false,
                'mensaje' => $mensajeClaveAdmin,
                'campo' => array_key_first($erroresClaveAdmin),
                'errores' => $erroresClaveAdmin
            ], $modeloLogin->obtenerEstadoRespuesta());
        }

    } elseif ($esPeticionAjax) {
        $responderJson([
            'ok' => false,
            'mensaje' => 'La solicitud enviada no es válida.'
        ], 400);
    }

} catch (\Throwable $error) {
    $estadoRespuesta = 500;
    $mensajeClaveAdmin = 'No fue posible validar la clave universal. Intente nuevamente.';

    if ($error instanceof \JsonException) {
        $estadoRespuesta = 422;
        $mensajeClaveAdmin = 'Los datos enviados no tienen un formato válido.';

    } elseif ($error instanceof \InvalidArgumentException) {
        $estadoRespuesta = 422;
        $mensajeClaveAdmin = $error->getMessage();

        if ($modeloLogin instanceof LoginM) {
            $erroresClaveAdmin = $modeloLogin->obtenerErroresValidacion();
        }

    } else {
        error_log($error->getMessage());
    }

    if ($esPeticionAjax) {
        $responderJson([
            'ok' => false,
            'mensaje' => $mensajeClaveAdmin,
            'campo' => array_key_first($erroresClaveAdmin),
            'errores' => $erroresClaveAdmin
        ], $estadoRespuesta);
    }
}


/* ===================================================== */
/* CARGA DE LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/ClaveAdminV.php';