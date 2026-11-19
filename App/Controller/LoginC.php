<?php

use App\Components\Session;
use App\Model\LoginM;


/* ===================================================== */
/* INICIO DE SESIÓN */
/* ===================================================== */

/* Variables utilizadas en la vista */
$mensajeLogin = '';
$tipoMensajeLogin = 'error';
$cedulaLogin = '';
$erroresLogin = [];
$modeloLogin = null;


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


/* Mostrar mensajes temporales enviados por otros controladores */
if (isset($_SESSION['mensaje_login'])) {
    $mensajeLogin = (string) $_SESSION['mensaje_login'];
    $tipoMensajeLogin = $_SESSION['tipo_mensaje_login'] ?? 'info';

    unset($_SESSION['mensaje_login'], $_SESSION['tipo_mensaje_login']);
}


/* Redirigir cuando ya existe una sesión activa */
if (Session\estaAutenticada()) {
    if ($esPeticionAjax) {
        $responderJson([
            'ok' => true,
            'redireccion' => 'Index.php?url=inicio'
        ]);
    }

    header('Location: Index.php?url=inicio');
    exit;
}


/* Conservar el motivo específico del cierre de sesión */
if ($mensajeLogin === '' && ($_GET['sesion'] ?? '') === 'expirada') {
    $mensajeLogin = 'La sesión expiró por inactividad. Inicie sesión nuevamente.';
    $tipoMensajeLogin = 'warning';
}


/* ===================================================== */
/* PROCESAR EL FORMULARIO DE ACCESO */
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
        if (($datosFormulario['accion'] ?? '') !== 'iniciar_sesion') {
            throw new \InvalidArgumentException('La solicitud enviada no es válida.');
        }

        $cedula = $datosFormulario['cedula'] ?? '';
        $clave = $datosFormulario['clave'] ?? '';


        /* Conservar la cédula como texto para volver a mostrar el formulario */
        $cedulaLogin = is_string($cedula) ? trim($cedula) : '';


        /* Delegar la validación y la autenticación al modelo */
        $modeloLogin = new LoginM();
        $usuario = $modeloLogin->autenticar($cedula, $clave);


        /* Registrar la sesión únicamente después de confirmar el acceso */
        if ($usuario !== null) {
            Session\registrarUsuario($usuario);

            if ($esPeticionAjax) {
                $responderJson([
                    'ok' => true,
                    'redireccion' => 'Index.php?url=inicio'
                ]);
            }

            header('Location: Index.php?url=inicio');
            exit;
        }

        $mensajeLogin = $modeloLogin->obtenerMensajeAcceso();
        $erroresLogin = $modeloLogin->obtenerErroresValidacion();


        /* Informar los errores de acceso sin cargar la vista */
        if ($esPeticionAjax) {
            $responderJson([
                'ok' => false,
                'mensaje' => $mensajeLogin,
                'campo' => array_key_first($erroresLogin),
                'errores' => $erroresLogin
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
    $mensajeLogin = 'No fue posible validar el acceso. Intente nuevamente.';


    /* Identificar los errores del formato o de los datos enviados */
    if ($error instanceof \JsonException) {
        $estadoRespuesta = 422;
        $mensajeLogin = 'Los datos enviados no tienen un formato válido.';

    } elseif ($error instanceof \InvalidArgumentException) {
        $estadoRespuesta = 422;
        $mensajeLogin = $error->getMessage();

        if ($modeloLogin instanceof LoginM) {
            $erroresLogin = $modeloLogin->obtenerErroresValidacion();
        }

    } else {
        error_log($error->getMessage());
    }


    /* Devolver el mensaje correspondiente a la solicitud */
    if ($esPeticionAjax) {
        $responderJson([
            'ok' => false,
            'mensaje' => $mensajeLogin,
            'campo' => array_key_first($erroresLogin),
            'errores' => $erroresLogin
        ], $estadoRespuesta);
    }
}


/* ===================================================== */
/* CARGA DE LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/LoginV.php';