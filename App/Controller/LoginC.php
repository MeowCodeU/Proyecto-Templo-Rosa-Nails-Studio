<?php

use App\Controller\Components\Session;
use App\Model\LoginM;


/* Variables utilizadas en la vista */
$mensajeLogin = '';
$cedulaLogin = '';


/* Mostrar mensajes temporales enviados por otros controladores */
if (isset($_SESSION['mensaje_login'])) {

    $mensajeLogin = (string) (
        $_SESSION['mensaje_login']
    );

    unset(
        $_SESSION['mensaje_login']
    );
}


/* Redirigir cuando ya existe una sesión activa */
if (Session::estaAutenticada()) {

    header(
        'Location: Index.php?url=inicio'
    );

    exit;
}


/* Mostrar mensaje cuando la sesión expire */
if (
    isset($_GET['sesion']) &&
    $_GET['sesion'] === 'expirada'
) {
    $mensajeLogin =
        'La sesión expiró por inactividad. Inicie sesión nuevamente.';
}


/* Procesar el formulario de inicio de sesión */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['accion']) &&
    $_POST['accion'] === 'iniciar_sesion'
) {

    $cedulaLogin = trim(
        $_POST['cedula'] ?? ''
    );

    $clave = $_POST['clave'] ?? '';


    /* Validar los datos ingresados */
    if (
        !preg_match(
            '/^[0-9]{6,20}$/',
            $cedulaLogin
        ) ||
        $clave === ''
    ) {
        $mensajeLogin =
            'La cédula o la clave suministrada no es válida.';

    } else {

        try {

            $modeloLogin = new LoginM();

            $usuario =
                $modeloLogin->buscarPorCedula(
                    $cedulaLogin
                );


            /* Validar la existencia de la cuenta y la clave */
            if (
                !$usuario ||
                !password_verify(
                    $clave,
                    $usuario['clave']
                )
            ) {
                $mensajeLogin =
                    'La cédula o la clave suministrada no es válida.';

            } elseif (
                ($usuario['estado_usuario'] ?? '') !==
                    'ACTIVO' ||
                ($usuario['estado_rol'] ?? '') !==
                    'ACTIVO'
            ) {

                /* Validar que la cuenta y el rol estén activos */
                $mensajeLogin =
                    'La cuenta o el rol asociado no se encuentra activo.';

            } else {

                /* No guardar la clave dentro de la sesión */
                unset($usuario['clave']);


                /* Crear la sesión del usuario */
                Session::registrarUsuario(
                    $usuario
                );


                header(
                    'Location: Index.php?url=inicio'
                );

                exit;
            }

        } catch (\Throwable $error) {

            error_log(
                $error->getMessage()
            );

            $mensajeLogin =
                'No fue posible validar el acceso. Intente nuevamente.';
        }
    }
}

/* Cargar la vista */
require_once __DIR__ . '/../View/LoginV.php';