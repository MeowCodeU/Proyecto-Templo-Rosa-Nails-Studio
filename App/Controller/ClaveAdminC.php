<?php

/* ===================================================== */
/* VALIDACIÓN DE LA CLAVE UNIVERSAL */
/* ===================================================== */

/* Variables utilizadas en la vista */
$mensajeClaveAdmin = '';
$tipoMensajeClaveAdmin = 'error';


/* Configuración de la autorización de registro */
$hashClaveUniversal = '$2y$12$yhjAIohVHg.buqNIDwmi0.ADc//0D6x4xF5bqphiK/U9qaFogkVi6';
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

        if (($datosFormulario['accion'] ?? '') !== 'validar_clave_universal') {
            throw new \InvalidArgumentException('La solicitud enviada no es válida.');
        }

        $claveUniversal = $datosFormulario['clave_universal'] ?? '';


        /* Comprobar el tipo del dato recibido */
        if (!is_string($claveUniversal)) {
            throw new \InvalidArgumentException('La clave universal ingresada no es válida.');
        }

        $claveUniversal = trim($claveUniversal);


        /* Validar la clave universal */
        if ($claveUniversal === '') {
            $mensajeClaveAdmin = 'Debe ingresar la clave universal.';

        } elseif (password_verify($claveUniversal, $hashClaveUniversal)) {

            /* Renovar el identificador de la sesión */
            session_regenerate_id(true);


            /* Autorizar el registro durante diez minutos */
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

        } else {

            /* Retirar la autorización cuando la clave sea incorrecta */
            unset($_SESSION['registro_usuario_autorizado_hasta']);

            $mensajeClaveAdmin = 'La clave universal ingresada no es válida.';
        }


        /* Informar los errores sin cargar la vista */
        if ($esPeticionAjax) {
            $responderJson([
                'ok' => false,
                'mensaje' => $mensajeClaveAdmin
            ], 422);
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
    $mensajeClaveAdmin = 'No fue posible validar la clave universal. Intente nuevamente.';


    /* Identificar los errores del formato o de los datos enviados */
    if ($error instanceof \JsonException) {
        $estadoRespuesta = 422;
        $mensajeClaveAdmin = 'Los datos enviados no tienen un formato válido.';

    } elseif ($error instanceof \InvalidArgumentException) {
        $estadoRespuesta = 422;
        $mensajeClaveAdmin = $error->getMessage();
    }


    /* Devolver el mensaje correspondiente a la solicitud */
    if ($esPeticionAjax) {
        $responderJson([
            'ok' => false,
            'mensaje' => $mensajeClaveAdmin
        ], $estadoRespuesta);
    }
}


/* ===================================================== */
/* CARGA DE LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/ClaveAdminV.php';