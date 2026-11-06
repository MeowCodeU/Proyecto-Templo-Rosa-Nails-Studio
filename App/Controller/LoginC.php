<?php

use App\Controller\Components\Session;
use App\Model\LoginM;


/* ===================================================== */
/* INICIO DE SESIÓN */
/* ===================================================== */

/* Variables utilizadas en la vista */
$mensajeLogin = '';
$cedulaLogin = '';


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

    unset($_SESSION['mensaje_login']);
}


/* Redirigir cuando ya existe una sesión activa */
if (Session::estaAutenticada()) {

    if ($esPeticionAjax) {
        $responderJson([
            'ok' => true,
            'redireccion' => 'Index.php?url=inicio'
        ]);
    }

    header('Location: Index.php?url=inicio');

    exit;
}


/* Mostrar el mensaje de vencimiento por inactividad */
if (($_GET['sesion'] ?? '') === 'expirada') {
    $mensajeLogin = 'La sesión expiró por inactividad. Inicie sesión nuevamente.';
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
        $datosFormulario = json_decode(
            file_get_contents('php://input'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

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


        /* Comprobar el tipo de los datos recibidos */
        if (!is_string($cedula) || !is_string($clave)) {
            throw new \InvalidArgumentException('La cédula o la clave suministrada no es válida.');
        }

        $cedulaLogin = trim($cedula);
        $estadoRespuesta = 401;


        /* Validar los datos ingresados */
        if (!preg_match('/^[0-9]{6,20}$/', $cedulaLogin) || $clave === '') {
            $mensajeLogin = 'La cédula o la clave suministrada no es válida.';

        } else {
            $modeloLogin = new LoginM();
            $usuario = $modeloLogin->buscarPorCedula($cedulaLogin);


            /* Validar la existencia de la cuenta y la contraseña */
            if (!$usuario || !password_verify($clave, (string) ($usuario['clave'] ?? ''))) {
                $mensajeLogin = 'La cédula o la clave suministrada no es válida.';

            /* Comprobar que la cuenta y el rol estén activos */
            } elseif (
                ($usuario['estado_usuario'] ?? '') !== 'ACTIVO' ||
                ($usuario['estado_rol'] ?? '') !== 'ACTIVO'
            ) {
                $estadoRespuesta = 403;
                $mensajeLogin = 'La cuenta o el rol asociado no se encuentra activo.';

            } else {

                /* Excluir la contraseña antes de crear la sesión */
                unset($usuario['clave']);


                /* Registrar los datos del usuario en la sesión */
                Session::registrarUsuario($usuario);


                /* Confirmar el acceso al sistema */
                if ($esPeticionAjax) {
                    $responderJson([
                        'ok' => true,
                        'redireccion' => 'Index.php?url=inicio'
                    ]);
                }

                header('Location: Index.php?url=inicio');

                exit;
            }
        }


        /* Informar los errores de acceso sin cargar la vista */
        if ($esPeticionAjax) {
            $responderJson([
                'ok' => false,
                'mensaje' => $mensajeLogin
            ], $estadoRespuesta);
        }

    } elseif ($esPeticionAjax) {
        $responderJson([
            'ok' => false,
            'mensaje' => 'La solicitud enviada no es válida.'
        ], 400);
    }

} catch (\Throwable $error) {

    /* Registrar el error técnico sin exponerlo en la respuesta */
    error_log($error->getMessage());

    $estadoRespuesta = 500;
    $mensajeLogin = 'No fue posible validar el acceso. Intente nuevamente.';


    /* Identificar los errores del formato o de los datos enviados */
    if ($error instanceof \JsonException) {
        $estadoRespuesta = 422;
        $mensajeLogin = 'Los datos enviados no tienen un formato válido.';

    } elseif ($error instanceof \InvalidArgumentException) {
        $estadoRespuesta = 422;
        $mensajeLogin = $error->getMessage();
    }


    /* Devolver el mensaje correspondiente a la solicitud */
    if ($esPeticionAjax) {
        $responderJson([
            'ok' => false,
            'mensaje' => $mensajeLogin
        ], $estadoRespuesta);
    }
}


/* ===================================================== */
/* CARGA DE LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/LoginV.php';