<?php

use App\Model\RolM;
use App\Model\SeguridadM;


/* Variables utilizadas en la vista */
$mensajeRegistro = '';
$listaRoles = [];

$datosRegistro = [
    'nombres' => '',
    'apellidos' => '',
    'cedula' => '',
    'telefono' => '',
    'correo' => '',
    'id_rol' => ''
];


/* Comprobar que la clave universal haya sido validada previamente.*/
$autorizadoHasta = (int) (
    $_SESSION['registro_usuario_autorizado_hasta']
    ?? 0
);


if ($autorizadoHasta < time()) {

    unset(
        $_SESSION['registro_usuario_autorizado_hasta']
    );

    header(
        'Location: Index.php?url=claveAdmin'
    );

    exit;
}


try {

    /* Crear las instancias de los modelos */
    $modeloSeguridad = new SeguridadM();
    $modeloRol = new RolM();


    /* Consultar los roles activos */
    $listaRoles = $modeloRol->consultar();


    /* Procesar el formulario de registro */
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['accion']) &&
        $_POST['accion'] === 'registrar_usuario'
    ) {

        $datosRegistro = [
            'nombres' => trim(
                (string) ($_POST['nombres'] ?? '')
            ),
            'apellidos' => trim(
                (string) ($_POST['apellidos'] ?? '')
            ),
            'cedula' => trim(
                (string) ($_POST['cedula'] ?? '')
            ),
            'telefono' => trim(
                (string) ($_POST['telefono'] ?? '')
            ),
            'correo' => trim(
                (string) ($_POST['correo'] ?? '')
            ),
            'id_rol' => trim(
                (string) ($_POST['id_rol'] ?? '')
            )
        ];


        $clave = (string) (
            $_POST['clave']
            ?? ''
        );

        $confirmarClave = (string) (
            $_POST['confirmar_clave']
            ?? ''
        );


        /* Validar los nombres */
        if (
            !preg_match(
                "/^[\\p{L}\\s'-]{2,60}$/u",
                $datosRegistro['nombres']
            )
        ) {
            $mensajeRegistro =
                'Los nombres ingresados no son válidos.';

        /* Validar los apellidos */
        } elseif (
            !preg_match(
                "/^[\\p{L}\\s'-]{2,60}$/u",
                $datosRegistro['apellidos']
            )
        ) {
            $mensajeRegistro =
                'Los apellidos ingresados no son válidos.';

        /* Validar la cédula */
        } elseif (
            !preg_match(
                '/^[0-9]{6,20}$/',
                $datosRegistro['cedula']
            )
        ) {
            $mensajeRegistro =
                'La cédula debe contener solamente números.';

        /* Validar el teléfono cuando sea suministrado */
        } elseif (
            $datosRegistro['telefono'] !== '' &&
            !preg_match(
                '/^[0-9+()\-\s]{7,20}$/',
                $datosRegistro['telefono']
            )
        ) {
            $mensajeRegistro =
                'El teléfono ingresado no es válido.';

        /* Validar el correo electrónico */
        } elseif (
            !filter_var(
                $datosRegistro['correo'],
                FILTER_VALIDATE_EMAIL
            )
        ) {
            $mensajeRegistro =
                'El correo electrónico ingresado no es válido.';

        /* Validar la longitud de la contraseña */
        } elseif (strlen($clave) < 8) {
            $mensajeRegistro =
                'La contraseña debe contener al menos 8 caracteres.';

        /* Validar que ambas contraseñas coincidan */
        } elseif ($clave !== $confirmarClave) {
            $mensajeRegistro =
                'La contraseña y su confirmación no coinciden.';

        /* Validar el identificador del rol */
        } elseif (
            !ctype_digit(
                $datosRegistro['id_rol']
            ) ||
            (int) $datosRegistro['id_rol'] <= 0
        ) {
            $mensajeRegistro =
                'Debe seleccionar un rol válido.';

        } else {

            /* Comprobar que el rol exista y esté activo */
            $modeloRol->set_idRol(
                (int) $datosRegistro['id_rol']
            );

            $rolSeleccionado =
                $modeloRol->buscar();


            if (
                !$rolSeleccionado ||
                ($rolSeleccionado['estado_rol'] ?? '') !==
                    'ACTIVO'
            ) {
                $mensajeRegistro =
                    'El rol seleccionado no se encuentra disponible.';

            } else {

                /* Asignar los datos al modelo */
                $modeloSeguridad->set_cedula(
                    $datosRegistro['cedula']
                );

                $modeloSeguridad->set_nombre(
                    $datosRegistro['nombres']
                );

                $modeloSeguridad->set_apellido(
                    $datosRegistro['apellidos']
                );

                $modeloSeguridad->set_telefono(
                    $datosRegistro['telefono']
                );

                $modeloSeguridad->set_correo(
                    $datosRegistro['correo']
                );

                $modeloSeguridad->set_direccion('');
                $modeloSeguridad->set_ciudad('');

                $modeloSeguridad->set_idRol(
                    (int) $datosRegistro['id_rol']
                );

                $modeloSeguridad->set_nickname('');

                $modeloSeguridad->set_clave(
                    $clave
                );


                $registrado =
                    $modeloSeguridad->registrar();


                if ($registrado) {

                    /*La autorización se utiliza para registrar una sola cuenta.*/
                    unset(
                        $_SESSION[
                            'registro_usuario_autorizado_hasta'
                        ]
                    );


                    $_SESSION['mensaje_login'] =
                        'El usuario fue registrado exitosamente. Ya puede iniciar sesión.';


                    header(
                        'Location: Index.php?url=login'
                    );

                    exit;

                } else {
                    $mensajeRegistro =
                        'La persona ya posee una cuenta de usuario registrada.';
                }
            }
        }
    }

} catch (\Throwable $error) {

    error_log(
        $error->getMessage()
    );

    $mensajeRegistro =
        'No fue posible completar el registro. Intente nuevamente.';
}


/* Cargar la vista */
require_once __DIR__ . '/../View/RegistroUsuarioV.php';