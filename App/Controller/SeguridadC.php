<?php

use App\Model\SeguridadM;
use App\Model\RolM;

/* ===================================================== */
/* GESTIÓN DE SEGURIDAD */
/* ===================================================== */

$esPeticionAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
$accion = '';
$resultadoRestablecimiento = 'error';
$erroresValidacion = [];
$modeloOperacion = null;

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

    if (!in_array($metodoSolicitud, ['GET', 'POST'], true)) {
        header('Allow: GET, POST');
        $responderJson(['ok' => false, 'mensaje' => 'El método solicitado no está permitido.'], 405);
    }

    /* Crear las instancias de los modelos */
    $modeloSeguridad = new SeguridadM();
    $modeloRol = new RolM();

    /* Procesar las acciones enviadas por los formularios */
    if ($metodoSolicitud === 'POST') {
        $accion = $datosFormulario['accion'] ?? null;

        if (!is_string($accion)) {
            $accion = '';
            throw new \InvalidArgumentException('La acción enviada no es válida.');
        }

        $accion = trim($accion);
        $modeloOperacion = in_array($accion, ['registrar_rol', 'modificar_rol', 'cambiar_estado_rol'], true)
            ? $modeloRol : $modeloSeguridad;

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

                $modeloSeguridad->set_cedula($datosFormulario['cedula'] ?? '');

                $persona = $modeloSeguridad->buscarPersonaPorCedula();

                if ($modeloSeguridad->obtenerErroresValidacion() !== []) {
                    throw new \InvalidArgumentException('Revise la cédula ingresada.');
                }

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

                $modeloSeguridad->set_cedula($datosFormulario['cedula'] ?? '');
                $modeloSeguridad->set_nombre($datosFormulario['nombre'] ?? '');
                $modeloSeguridad->set_apellido($datosFormulario['apellido'] ?? '');
                $modeloSeguridad->set_telefono($datosFormulario['telefono'] ?? '');
                $modeloSeguridad->set_correo($datosFormulario['correo'] ?? '');
                $modeloSeguridad->set_direccion($datosFormulario['direccion'] ?? '');
                $modeloSeguridad->set_ciudad($datosFormulario['ciudad'] ?? '');
                $modeloSeguridad->set_idRol($datosFormulario['id_rol'] ?? null);
                $modeloSeguridad->set_nickname($datosFormulario['nickname'] ?? '');
                $modeloSeguridad->set_clave($datosFormulario['clave'] ?? '');

                $resultado = $modeloSeguridad->registrar($datosFormulario['confirmar_clave'] ?? '');
                $mensaje = 'Usuario registrado correctamente.';

                break;

            /* ================================================= */
            /* REGISTRAR ROL */
            /* ================================================= */

            case 'registrar_rol':

                $modeloRol->set_nombreRol($datosFormulario['nombre_rol'] ?? '');
                $modeloRol->set_descripcion($datosFormulario['descripcion'] ?? '');

                $resultado = $modeloRol->registrar();
                $mensaje = 'Rol registrado correctamente.';

                break;

            /* ================================================= */
            /* MODIFICAR USUARIO */
            /* ================================================= */

            case 'modificar_usuario':

                $modeloSeguridad->set_idUsuario($datosFormulario['id_usuario'] ?? null);
                $modeloSeguridad->set_idPersona($datosFormulario['id_persona'] ?? '');
                $modeloSeguridad->set_nombre($datosFormulario['nombre'] ?? '');
                $modeloSeguridad->set_apellido($datosFormulario['apellido'] ?? '');
                $modeloSeguridad->set_telefono($datosFormulario['telefono'] ?? '');
                $modeloSeguridad->set_correo($datosFormulario['correo'] ?? '');
                $modeloSeguridad->set_direccion($datosFormulario['direccion'] ?? '');
                $modeloSeguridad->set_ciudad($datosFormulario['ciudad'] ?? '');
                $modeloSeguridad->set_idRol($datosFormulario['id_rol'] ?? null);
                $modeloSeguridad->set_nickname($datosFormulario['nickname'] ?? '');

                $resultado = $modeloSeguridad->modificar();
                $mensaje = 'Usuario modificado correctamente.';

                break;

            /* ================================================= */
            /* RESTABLECER CONTRASEÑA DE USUARIO */
            /* ================================================= */

            case 'restablecer_clave':

                $tokenFormulario = $datosFormulario['token_csrf'] ?? '';
                $tokenSesion = $_SESSION['token_restablecer_clave'] ?? '';

                if (!is_string($tokenFormulario) || !is_string($tokenSesion) ||
                    $tokenFormulario === '' || $tokenSesion === '' ||
                    !hash_equals($tokenSesion, $tokenFormulario)) {
                    $resultadoRestablecimiento = 'token_invalido';
                    throw new \InvalidArgumentException('La solicitud de restablecimiento no es válida. Intente nuevamente.');
                }

                $modeloSeguridad->set_idUsuario($datosFormulario['id_usuario'] ?? null);
                $modeloSeguridad->set_clave($datosFormulario['nueva_clave'] ?? '');
                $resultado = $modeloSeguridad->restablecerClave($datosFormulario['confirmar_clave'] ?? '');
                $erroresClave = $modeloSeguridad->obtenerErroresValidacion();

                if ($erroresClave !== []) {
                    $resultadoRestablecimiento = isset($erroresClave['id_usuario'])
                        ? 'datos_incompletos' : (isset($erroresClave['confirmar_clave']) ? 'no_coinciden' : 'error');
                } elseif ($resultado) {
                    $resultadoRestablecimiento = 'exito';
                }

                $mensaje = 'La contraseña del usuario fue restablecida correctamente.';

                break;

            /* ================================================= */
            /* MODIFICAR ROL */
            /* ================================================= */

            case 'modificar_rol':

                $modeloRol->set_idRol($datosFormulario['id_rol'] ?? null);
                $modeloRol->set_nombreRol($datosFormulario['nombre_rol'] ?? '');
                $modeloRol->set_descripcion($datosFormulario['descripcion'] ?? '');

                $resultado = $modeloRol->modificar();
                $mensaje = 'Rol modificado correctamente.';

                break;

            /* ================================================= */
            /* DESACTIVAR USUARIO */
            /* ================================================= */

            case 'cambiar_estado_usuario':

                $modeloSeguridad->set_estadoUsuario($datosFormulario['estado_usuario'] ?? '');
                $modeloSeguridad->set_idUsuario($datosFormulario['id_usuario'] ?? null);

                $resultado = $modeloSeguridad->eliminar();
                $mensaje = 'Usuario desactivado correctamente.';

                break;

            /* ================================================= */
            /* DESACTIVAR ROL */
            /* ================================================= */

            case 'cambiar_estado_rol':

                $modeloRol->set_idRol($datosFormulario['id_rol'] ?? null);

                $resultado = $modeloRol->eliminar();
                $mensaje = 'Rol desactivado correctamente.';

                break;

            default:
                throw new \InvalidArgumentException('La acción enviada no es válida.');
        }

        /* Recuperar los errores generados por las validaciones del modelo */
        $erroresValidacion = $modeloOperacion->obtenerErroresValidacion();

        if ($erroresValidacion !== []) {
            throw new \InvalidArgumentException('Revise los campos señalados.');
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

    if ($modeloOperacion !== null) $erroresValidacion = $modeloOperacion->obtenerErroresValidacion();

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

    if ($estadoRespuesta === 500) error_log('SeguridadC: ' . $e->getMessage());

    if ($esPeticionAjax) {
        $responderJson(['ok' => false, 'mensaje' => $mensaje, 'errores' => $erroresValidacion], $estadoRespuesta);
    }

    if ($accion === 'restablecer_clave') {
        unset($_SESSION['token_restablecer_clave']);
        header('Location: Index.php?url=seguridad&restablecer=' . $resultadoRestablecimiento);
        exit;
    }

    http_response_code($estadoRespuesta);
    exit(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'));
}

/* ===================================================== */
/* CARGA DE LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/SeguridadV.php';