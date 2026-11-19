<?php

use App\Components\Session;
use App\Model\PerfilM;

/* ===================================================== */
/* GESTIÓN DEL PERFIL */
/* ===================================================== */

$esPeticionAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
$accion = '';
$modeloPerfil = null;

/* Enviar la respuesta y finalizar la solicitud */
$responderJson = static function (array $datos, int $estado = 200): void {
    http_response_code($estado);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
};

/* Comunicar el resultado según el tipo de solicitud */
$responderOperacion = static function (
    bool $ok,
    string $codigo,
    string $mensaje,
    int $estado = 200,
    array $datos = []
) use ($esPeticionAjax, $responderJson, &$accion): void {
    if ($esPeticionAjax) {
        $respuesta = ['ok' => $ok, 'codigo' => $codigo, 'mensaje' => $mensaje, 'tipo' => $ok ? 'success' : 'danger'];
        $nombreToken = $accion === 'actualizar_perfil' ? 'token_actualizar_perfil' : 'token_cambiar_clave';

        if (isset($_SESSION[$nombreToken]) && is_string($_SESSION[$nombreToken])) {
            $respuesta['token_csrf'] = $_SESSION[$nombreToken];
        }

        $responderJson(array_merge($respuesta, $datos), $estado);
    }

    /* Conservar los formularios enviados sin JavaScript */
    if ($accion === 'actualizar_perfil') {
        $_SESSION['mensaje_perfil'] = ['tipo' => $ok ? 'success' : 'danger', 'texto' => $mensaje];
        header('Location: Index.php?url=inicio');
    } else {
        header('Location: Index.php?url=inicio&clave=' . urlencode($codigo));
    }

    exit;
};

/* ===================================================== */
/* COMPROBACIÓN DE LA SESIÓN */
/* ===================================================== */

if (!Session\estaAutenticada()) {
    if ($esPeticionAjax) {
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
/* RECEPCIÓN Y COMPROBACIÓN DE LA SOLICITUD */
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

    $accion = $datosFormulario['accion'] ?? '';

    if ($metodoSolicitud !== 'POST' || !is_string($accion) ||
        !in_array($accion, ['actualizar_perfil', 'cambiar_clave'], true)) {
        $accion = '';
        $responderOperacion(false, 'solicitud_invalida', 'La solicitud enviada no es válida.', 400);
    }

    /* Obtener la cuenta de la sesión y no del formulario */
    $usuarioActual = Session\obtenerUsuario();
    $idUsuario = (int) ($usuarioActual['id_usuario'] ?? 0);

    if ($idUsuario <= 0) {
        $responderOperacion(false, 'usuario_no_disponible', 'No fue posible identificar la cuenta del usuario autenticado.', 403);
    }

    /* Comprobar el token correspondiente al formulario */
    $nombreToken = $accion === 'actualizar_perfil' ? 'token_actualizar_perfil' : 'token_cambiar_clave';
    $tokenFormulario = $datosFormulario['token_csrf'] ?? '';
    $tokenSesion = $_SESSION[$nombreToken] ?? '';

    if (!is_string($tokenFormulario) || !is_string($tokenSesion) ||
        $tokenFormulario === '' || $tokenSesion === '' || !hash_equals($tokenSesion, $tokenFormulario)) {
        $responderOperacion(false, 'solicitud_invalida', 'La solicitud no es válida. Actualice la página e intente nuevamente.', 403);
    }

    $modeloPerfil = new PerfilM();
    $nuevoToken = bin2hex(random_bytes(32));

    /* ================================================= */
    /* CAMBIAR LA CONTRASEÑA */
    /* ================================================= */

    if ($accion === 'cambiar_clave') {
        $resultado = $modeloPerfil->cambiarClave(
            $idUsuario,
            $datosFormulario['clave_actual'] ?? '',
            $datosFormulario['nueva_clave'] ?? '',
            $datosFormulario['confirmar_clave'] ?? ''
        );

        if (!$resultado) {
            $errores = $modeloPerfil->obtenerErroresValidacion();
            $codigo = $modeloPerfil->obtenerCodigoResultado();

            if ($codigo === 'usuario_no_disponible') {
                $responderOperacion(false, $codigo, 'No fue posible identificar la cuenta del usuario autenticado.', 403);
            }

            $responderOperacion(false, $codigo, reset($errores) ?: 'Revise los campos señalados.', 422, [
                'campo' => array_key_first($errores),
                'errores' => $errores
            ]);
        }

        /* Renovar el token y el identificador de la sesión */
        $_SESSION['token_cambiar_clave'] = $nuevoToken;
        session_regenerate_id(true);
        $responderOperacion(true, 'actualizada', 'La contraseña fue actualizada correctamente.');
    }

    /* ================================================= */
    /* ACTUALIZAR LOS DATOS Y LA FOTOGRAFÍA */
    /* ================================================= */

    $resultado = $modeloPerfil->actualizarPerfil(
        $idUsuario,
        $datosFormulario['nickname'] ?? '',
        $datosFormulario['telefono'] ?? '',
        $datosFormulario['correo'] ?? '',
        $_FILES['foto'] ?? null
    );

    if ($resultado === null) {
        $errores = $modeloPerfil->obtenerErroresValidacion();

        if ($errores !== []) {
            $responderOperacion(false, $modeloPerfil->obtenerCodigoResultado(), reset($errores), 422, [
                'campo' => array_key_first($errores),
                'errores' => $errores
            ]);
        }

        $responderOperacion(false, 'usuario_no_disponible', 'No fue posible identificar la cuenta del usuario autenticado.', 403);
    }

    /* Conservar la sesión y excluir cualquier contraseña */
    $usuarioActualizado = $resultado['usuario'];
    $_SESSION['usuario'] = array_replace($usuarioActual, $usuarioActualizado);
    unset($_SESSION['usuario']['clave']);
    $_SESSION['token_actualizar_perfil'] = $nuevoToken;

    $responderOperacion(true, 'actualizado', 'El perfil fue actualizado correctamente.', 200, [
        'usuario' => [
            'nickname' => $usuarioActualizado['nickname'],
            'nombre' => $usuarioActualizado['nombre'],
            'apellido' => $usuarioActualizado['apellido'],
            'telefono' => $usuarioActualizado['telefono'],
            'correo' => $usuarioActualizado['correo'],
            'foto_perfil' => $usuarioActualizado['foto_perfil']
        ]
    ]);

} catch (\Throwable $error) {
    if ($error instanceof \JsonException || $error instanceof \InvalidArgumentException) {
        $responderOperacion(false, 'solicitud_invalida', 'Los datos enviados no tienen un formato válido.', 422);
    }

    error_log('PerfilC: ' . $error->getMessage());
    $mensaje = $accion === 'cambiar_clave'
        ? 'No fue posible actualizar la contraseña. Intente nuevamente.'
        : 'No fue posible actualizar el perfil. Intente nuevamente.';
    $responderOperacion(false, 'error', $mensaje, 500);
}