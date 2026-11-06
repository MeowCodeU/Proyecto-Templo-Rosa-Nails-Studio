<?php

use App\Model\SeguridadM;
use App\Model\RolM;


/* ===================================================== */
/* GESTIÓN DE SEGURIDAD */
/* ===================================================== */

$esPeticionAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
$accion = '';
$resultadoRestablecimiento = 'error';

$responderJson = static function (array $datos, int $estado = 200) use (&$accion): void {

    /* Renovar el token consumido por el restablecimiento */
    if ($accion === 'restablecer_clave') {
        $_SESSION['token_restablecer_clave'] = bin2hex(random_bytes(32));
        $datos['token_restablecer_clave'] = $_SESSION['token_restablecer_clave'];
    }

    http_response_code($estado);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
};


/* ===================================================== */
/* LECTURA Y COMPROBACIÓN DE LOS DATOS */
/* ===================================================== */

try {
    $datosFormulario = $_POST;
    $metodoSolicitud = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $tipoContenido = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));

    if ($metodoSolicitud === 'POST' && $tipoContenido === 'application/json') {
        $datosFormulario = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($datosFormulario)) {
            throw new \InvalidArgumentException('Los datos enviados no tienen un formato válido.');
        }
    }

    $textoFormulario = static function (
        string $campo,
        bool $obligatorio = false,
        bool $recortar = true
    ) use ($datosFormulario): string {

        $valor = $datosFormulario[$campo] ?? '';

        if (!is_string($valor)) {
            throw new \InvalidArgumentException('Los datos enviados no son válidos.');
        }

        if ($recortar) $valor = trim($valor);

        if ($obligatorio && $valor === '') {
            throw new \InvalidArgumentException('Complete los campos obligatorios del formulario.');
        }

        return $valor;
    };

    $identificadorFormulario = static function (string $campo) use ($datosFormulario): int {
        $identificador = filter_var($datosFormulario[$campo] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        if ($identificador === false) {
            throw new \InvalidArgumentException('Seleccione un registro válido.');
        }

        return $identificador;
    };


    /* Crear las instancias de los modelos */
    $modeloSeguridad = new SeguridadM();
    $modeloRol = new RolM();


    /* Procesar las acciones enviadas por los formularios */
    if ($metodoSolicitud === 'POST') {
        $accion = $textoFormulario('accion', true);

        switch ($accion) {

            /* ================================================= */
            /* CONSULTAR USUARIOS Y ROLES */
            /* ================================================= */

            case 'consultar':

                if (!$esPeticionAjax) {
                    throw new \InvalidArgumentException('La solicitud enviada no es válida.');
                }

                $listaUsuarios = $modeloSeguridad->consultar();
                $listaRoles = $modeloRol->consultar();

                if (!is_array($listaUsuarios) || !is_array($listaRoles)) {
                    throw new \RuntimeException('No fue posible consultar los registros.');
                }

                /* Entregar únicamente los campos utilizados por las tablas y fichas */
                $camposUsuario = array_flip([
                    'id_usuario',
                    'id_persona',
                    'cedula',
                    'nombre',
                    'apellido',
                    'telefono',
                    'correo',
                    'direccion',
                    'ciudad',
                    'nickname',
                    'id_rol',
                    'nombre_rol',
                    'estado_usuario'
                ]);

                $camposRol = array_flip([
                    'id_rol',
                    'nombre_rol',
                    'descripcion',
                    'estado_rol'
                ]);

                $usuariosPublicos = array_map(
                    static function (array $usuario) use ($camposUsuario): array {
                        return array_intersect_key($usuario, $camposUsuario);
                    },
                    array_values($listaUsuarios)
                );

                $rolesPublicos = array_map(
                    static function (array $rol) use ($camposRol): array {
                        return array_intersect_key($rol, $camposRol);
                    },
                    array_values($listaRoles)
                );

                $responderJson(['ok' => true, 'usuarios' => $usuariosPublicos, 'roles' => $rolesPublicos]);
                break;


            /* ================================================= */
            /* BUSCAR PERSONA POR CÉDULA */
            /* ================================================= */

            case 'buscar_persona':

                $modeloSeguridad->set_cedula($textoFormulario('cedula', true));

                $persona = $modeloSeguridad->buscarPersonaPorCedula();
                $personaPublica = null;

                if ($persona) {
                    $personaPublica = [];

                    foreach ([
                        'id_persona',
                        'cedula',
                        'nombre',
                        'apellido',
                        'telefono',
                        'correo',
                        'direccion',
                        'ciudad'
                    ] as $campo) {
                        $personaPublica[$campo] = $persona[$campo] ?? '';
                    }
                }

                $responderJson([
                    'ok' => true,
                    'encontrada' => (bool) $persona,
                    'es_usuario' => $persona ? !empty($persona['id_usuario']) : false,
                    'persona' => $personaPublica
                ]);
                break;


            /* ================================================= */
            /* REGISTRAR USUARIO */
            /* ================================================= */

            case 'registrar_usuario':

                $clave = $textoFormulario('clave', true, false);
                $confirmarClave = $textoFormulario('confirmar_clave', true, false);

                if (strlen($clave) < 8) {
                    throw new \InvalidArgumentException('La contraseña debe contener un mínimo de ocho caracteres.');
                }

                if ($clave !== $confirmarClave) {
                    throw new \InvalidArgumentException('Las contraseñas no coinciden.');
                }

                $modeloSeguridad->set_cedula($textoFormulario('cedula', true));
                $modeloSeguridad->set_nombre($textoFormulario('nombre', true));
                $modeloSeguridad->set_apellido($textoFormulario('apellido', true));
                $modeloSeguridad->set_telefono($textoFormulario('telefono'));
                $modeloSeguridad->set_correo($textoFormulario('correo', true));
                $modeloSeguridad->set_direccion($textoFormulario('direccion'));
                $modeloSeguridad->set_ciudad($textoFormulario('ciudad'));
                $modeloSeguridad->set_idRol($identificadorFormulario('id_rol'));
                $modeloSeguridad->set_nickname($textoFormulario('nickname'));
                $modeloSeguridad->set_clave($clave);

                $resultado = $modeloSeguridad->registrar();
                $mensaje = 'Usuario registrado correctamente.';

                break;


            /* ================================================= */
            /* REGISTRAR ROL */
            /* ================================================= */

            case 'registrar_rol':

                $modeloRol->set_nombreRol($textoFormulario('nombre_rol', true));
                $modeloRol->set_descripcion($textoFormulario('descripcion'));

                $resultado = $modeloRol->registrar();
                $mensaje = 'Rol registrado correctamente.';

                break;


            /* ================================================= */
            /* MODIFICAR USUARIO */
            /* ================================================= */

            case 'modificar_usuario':

                $modeloSeguridad->set_idUsuario($identificadorFormulario('id_usuario'));
                $modeloSeguridad->set_idPersona($identificadorFormulario('id_persona'));
                $modeloSeguridad->set_nombre($textoFormulario('nombre', true));
                $modeloSeguridad->set_apellido($textoFormulario('apellido', true));
                $modeloSeguridad->set_telefono($textoFormulario('telefono'));
                $modeloSeguridad->set_correo($textoFormulario('correo', true));
                $modeloSeguridad->set_direccion($textoFormulario('direccion'));
                $modeloSeguridad->set_ciudad($textoFormulario('ciudad'));
                $modeloSeguridad->set_idRol($identificadorFormulario('id_rol'));
                $modeloSeguridad->set_nickname($textoFormulario('nickname'));

                $resultado = $modeloSeguridad->modificar();
                $mensaje = 'Usuario modificado correctamente.';

                break;


            /* ================================================= */
            /* RESTABLECER CONTRASEÑA DE USUARIO */
            /* ================================================= */

            case 'restablecer_clave':

                $tokenFormulario = $textoFormulario('token_csrf', false, false);
                $tokenSesion = (string) ($_SESSION['token_restablecer_clave'] ?? '');

                if (
                    $tokenFormulario === '' ||
                    $tokenSesion === '' ||
                    !hash_equals($tokenSesion, $tokenFormulario)
                ) {
                    $resultadoRestablecimiento = 'token_invalido';

                    throw new \InvalidArgumentException(
                        'La solicitud de restablecimiento no es válida. Intente nuevamente.'
                    );
                }

                $resultadoRestablecimiento = 'datos_incompletos';

                $idUsuario = $identificadorFormulario('id_usuario');
                $nuevaClave = $textoFormulario('nueva_clave', true, false);
                $confirmarClave = $textoFormulario('confirmar_clave', true, false);

                if (strlen($nuevaClave) < 8) {
                    $resultadoRestablecimiento = 'clave_corta';

                    throw new \InvalidArgumentException(
                        'La contraseña temporal debe contener un mínimo de ocho caracteres.'
                    );
                }

                if ($nuevaClave !== $confirmarClave) {
                    $resultadoRestablecimiento = 'no_coinciden';

                    throw new \InvalidArgumentException(
                        'La contraseña temporal y su confirmación no coinciden.'
                    );
                }

                $resultadoRestablecimiento = 'error';

                $modeloSeguridad->set_idUsuario($idUsuario);
                $modeloSeguridad->set_clave($nuevaClave);

                if (!$modeloSeguridad->restablecerClave()) {
                    throw new \RuntimeException('No fue posible restablecer la contraseña.');
                }

                $resultado = true;
                $resultadoRestablecimiento = 'exito';
                $mensaje = 'La contraseña del usuario fue restablecida correctamente.';

                break;


            /* ================================================= */
            /* MODIFICAR ROL */
            /* ================================================= */

            case 'modificar_rol':

                $modeloRol->set_idRol($identificadorFormulario('id_rol'));
                $modeloRol->set_nombreRol($textoFormulario('nombre_rol', true));
                $modeloRol->set_descripcion($textoFormulario('descripcion'));

                $resultado = $modeloRol->modificar();
                $mensaje = 'Rol modificado correctamente.';

                break;


            /* ================================================= */
            /* DESACTIVAR USUARIO */
            /* ================================================= */

            case 'cambiar_estado_usuario':

                if (strtoupper($textoFormulario('estado_usuario', true)) !== 'INACTIVO') {
                    throw new \InvalidArgumentException('Seleccione una desactivación válida.');
                }

                $modeloSeguridad->set_idUsuario($identificadorFormulario('id_usuario'));

                $resultado = $modeloSeguridad->eliminar();
                $mensaje = 'Usuario desactivado correctamente.';

                break;


            /* ================================================= */
            /* DESACTIVAR ROL */
            /* ================================================= */

            case 'cambiar_estado_rol':

                $modeloRol->set_idRol($identificadorFormulario('id_rol'));

                $resultado = $modeloRol->eliminar();
                $mensaje = 'Rol desactivado correctamente.';

                break;


            default:
                throw new \InvalidArgumentException('La acción enviada no es válida.');
        }


        /* Confirmar el resultado de la operación */
        if ($resultado === false) {
            throw new \RuntimeException('No fue posible completar la operación.');
        }

        if ($esPeticionAjax) {
            $responderJson(['ok' => true, 'mensaje' => $mensaje]);
        }


        /* Conservar la respuesta de los formularios tradicionales */
        if ($accion === 'restablecer_clave') {
            unset($_SESSION['token_restablecer_clave']);
            header('Location: Index.php?url=seguridad&restablecer=exito');
        } else {
            header('Location: Index.php?url=seguridad');
        }

        exit;
    }


    /* Rechazar solicitudes incompletas */
    if ($esPeticionAjax) {
        $responderJson(['ok' => false, 'mensaje' => 'La solicitud enviada no es válida.'], 400);
    }


    /* Consultar los usuarios y roles activos para la vista */
    $listaUsuarios = $modeloSeguridad->consultar();
    $listaRoles = $modeloRol->consultar();

} catch (\Throwable $e) {

    /* Preparar un mensaje sin exponer información interna */
    $estadoRespuesta = 500;
    $mensaje = 'No fue posible completar la operación de seguridad. Intente nuevamente.';

    if ($e instanceof \JsonException) {
        $estadoRespuesta = 422;
        $mensaje = 'Los datos enviados no tienen un formato válido.';

    } elseif ($e instanceof \InvalidArgumentException) {
        $estadoRespuesta = 422;
        $mensaje = $e->getMessage();

    } elseif (
        $e instanceof \PDOException &&
        (int) ($e->errorInfo[1] ?? 0) === 1062
    ) {
        $estadoRespuesta = 409;
        $mensaje = 'Ya existe un registro con uno de los datos únicos ingresados. Revise los datos del formulario.';
    }

    if ($esPeticionAjax) {
        $responderJson(['ok' => false, 'mensaje' => $mensaje], $estadoRespuesta);
    }

    if ($accion === 'restablecer_clave') {
        unset($_SESSION['token_restablecer_clave']);
        header('Location: Index.php?url=seguridad&restablecer=' . $resultadoRestablecimiento);
        exit;
    }

    http_response_code($estadoRespuesta);
    exit($mensaje);
}


/* ===================================================== */
/* CARGA DE LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/SeguridadV.php';