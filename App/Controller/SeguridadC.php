<?php

use App\Model\SeguridadM;
use App\Model\RolM;


/* Crear las instancias de los modelos */
$modeloSeguridad = new SeguridadM();
$modeloRol = new RolM();


/* Procesar las acciones enviadas por los formularios */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['accion'])
) {

    /* ================================================= */
    /* BUSCAR PERSONA POR CÉDULA */
    /* ================================================= */

    if ($_POST['accion'] === 'buscar_persona') {

        header(
            'Content-Type: application/json; charset=utf-8'
        );


        $modeloSeguridad->set_cedula(
            trim($_POST['cedula'] ?? '')
        );


        $persona =
            $modeloSeguridad->buscarPersonaPorCedula();


        if ($persona) {

            echo json_encode(
                [
                    'encontrada' => true,
                    'es_usuario' => !empty(
                        $persona['id_usuario']
                    ),
                    'persona' => [
                        'id_persona' =>
                            $persona['id_persona'] ?? '',
                        'cedula' =>
                            $persona['cedula'] ?? '',
                        'nombre' =>
                            $persona['nombre'] ?? '',
                        'apellido' =>
                            $persona['apellido'] ?? '',
                        'telefono' =>
                            $persona['telefono'] ?? '',
                        'correo' =>
                            $persona['correo'] ?? '',
                        'direccion' =>
                            $persona['direccion'] ?? '',
                        'ciudad' =>
                            $persona['ciudad'] ?? ''
                    ]
                ],
                JSON_UNESCAPED_UNICODE
            );

        } else {

            echo json_encode(
                [
                    'encontrada' => false,
                    'es_usuario' => false,
                    'persona' => null
                ],
                JSON_UNESCAPED_UNICODE
            );
        }


        exit;
    }


    /* ================================================= */
    /* REGISTRAR USUARIO */
    /* ================================================= */

    if ($_POST['accion'] === 'registrar_usuario') {

        $clave = $_POST['clave'] ?? '';

        $confirmarClave =
            $_POST['confirmar_clave'] ?? '';


        /*
         * Registrar solamente cuando
         * las dos contraseñas coincidan.
         */
        if ($clave === $confirmarClave) {

            $modeloSeguridad->set_cedula(
                trim($_POST['cedula'] ?? '')
            );

            $modeloSeguridad->set_nombre(
                trim($_POST['nombre'] ?? '')
            );

            $modeloSeguridad->set_apellido(
                trim($_POST['apellido'] ?? '')
            );

            $modeloSeguridad->set_telefono(
                trim($_POST['telefono'] ?? '')
            );

            $modeloSeguridad->set_correo(
                trim($_POST['correo'] ?? '')
            );

            $modeloSeguridad->set_direccion(
                trim($_POST['direccion'] ?? '')
            );

            $modeloSeguridad->set_ciudad(
                trim($_POST['ciudad'] ?? '')
            );


            $modeloSeguridad->set_idRol(
                $_POST['id_rol'] ?? ''
            );

            $modeloSeguridad->set_clave(
                $clave
            );


            $modeloSeguridad->registrar();
        }


        header(
            'Location: Index.php?url=seguridad'
        );

        exit;
    }


    /* ================================================= */
    /* REGISTRAR ROL */
    /* ================================================= */

    if ($_POST['accion'] === 'registrar_rol') {

        $modeloRol->set_nombreRol(
            trim($_POST['nombre_rol'] ?? '')
        );

        $modeloRol->set_descripcion(
            trim($_POST['descripcion'] ?? '')
        );

        $modeloRol->registrar();


        header(
            'Location: Index.php?url=seguridad'
        );

        exit;
    }


    /* ================================================= */
    /* MODIFICAR USUARIO */
    /* ================================================= */

    if ($_POST['accion'] === 'modificar_usuario') {

        $modeloSeguridad->set_idUsuario(
            $_POST['id_usuario'] ?? ''
        );

        $modeloSeguridad->set_idPersona(
            $_POST['id_persona'] ?? ''
        );

        $modeloSeguridad->set_nombre(
            trim($_POST['nombre'] ?? '')
        );

        $modeloSeguridad->set_apellido(
            trim($_POST['apellido'] ?? '')
        );

        $modeloSeguridad->set_telefono(
            trim($_POST['telefono'] ?? '')
        );

        $modeloSeguridad->set_correo(
            trim($_POST['correo'] ?? '')
        );

        $modeloSeguridad->set_direccion(
            trim($_POST['direccion'] ?? '')
        );

        $modeloSeguridad->set_ciudad(
            trim($_POST['ciudad'] ?? '')
        );


        $modeloSeguridad->set_idRol(
            $_POST['id_rol'] ?? ''
        );


        $modeloSeguridad->modificar();


        header(
            'Location: Index.php?url=seguridad'
        );

        exit;
    }


    /* ================================================= */
    /* MODIFICAR ROL */
    /* ================================================= */

    if ($_POST['accion'] === 'modificar_rol') {

        $modeloRol->set_idRol(
            $_POST['id_rol'] ?? ''
        );

        $modeloRol->set_nombreRol(
            trim($_POST['nombre_rol'] ?? '')
        );

        $modeloRol->set_descripcion(
            trim($_POST['descripcion'] ?? '')
        );

        $modeloRol->modificar();


        header(
            'Location: Index.php?url=seguridad'
        );

        exit;
    }


    /* ================================================= */
    /* DESACTIVAR USUARIO */
    /* ================================================= */

    if ($_POST['accion'] === 'cambiar_estado_usuario') {

        $nuevoEstado = strtoupper(
            trim($_POST['estado_usuario'] ?? '')
        );


        /*
         * La tabla muestra solamente usuarios activos.
         * Por ahora únicamente se procesa la
         * desactivación del usuario.
         */
        if ($nuevoEstado === 'INACTIVO') {

            $modeloSeguridad->set_idUsuario(
                $_POST['id_usuario'] ?? ''
            );


            $modeloSeguridad->eliminar();
        }


        header(
            'Location: Index.php?url=seguridad'
        );

        exit;
    }


    /* ================================================= */
    /* DESACTIVAR ROL */
    /* ================================================= */

    if ($_POST['accion'] === 'cambiar_estado_rol') {

        $modeloRol->set_idRol(
            $_POST['id_rol'] ?? ''
        );

        $modeloRol->eliminar();


        header(
            'Location: Index.php?url=seguridad'
        );

        exit;
    }
}


/* Consultar los usuarios activos para llenar la tabla */
$listaUsuarios =
    $modeloSeguridad->consultar();


/* Consultar los roles activos para llenar los select y la pestaña Roles */
$listaRoles =
    $modeloRol->consultar();


/* Cargar la vista de Seguridad */
require_once __DIR__ . '/../View/SeguridadV.php';
