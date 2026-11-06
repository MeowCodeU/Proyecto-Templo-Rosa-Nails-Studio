<?php

use App\Controller\Components\Session;
use App\Model\PerfilM;


/* ===================================================== */
/* GESTIÓN DEL PERFIL */
/* ===================================================== */

$esPeticionAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

$accion = '';
$idUsuario = 0;
$fotoNueva = null;
$perfilGuardado = false;

$raizProyecto = dirname(__DIR__, 2);
$directorioFotos = $raizProyecto . '/Assets/uploads/perfiles';


/* Enviar la respuesta y finalizar la solicitud */
$responderJson = static function (array $datos, int $estado = 200): void {
    http_response_code($estado);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    exit;
};


/* Eliminar únicamente fotografías generadas para la cuenta autenticada */
$eliminarFoto = static function (string $rutaFoto) use ($raizProyecto, &$idUsuario): void {
    $patron = '/\AAssets\/uploads\/perfiles\/perfil_' . $idUsuario . '_[a-f0-9]{32}\.(?:jpg|png|webp)\z/';

    if (preg_match($patron, $rutaFoto) !== 1) {
        return;
    }

    $archivo = $raizProyecto . '/' . $rutaFoto;

    if (is_file($archivo) && !@unlink($archivo)) {
        error_log('No fue posible eliminar una fotografía del perfil: ' . $archivo);
    }
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
        $respuesta = [
            'ok' => $ok,
            'codigo' => $codigo,
            'mensaje' => $mensaje,
            'tipo' => $ok ? 'success' : 'danger'
        ];

        $nombreToken = $accion === 'actualizar_perfil'
            ? 'token_actualizar_perfil'
            : 'token_cambiar_clave';

        if (isset($_SESSION[$nombreToken]) && is_string($_SESSION[$nombreToken])) {
            $respuesta['token_csrf'] = $_SESSION[$nombreToken];
        }

        $responderJson(array_merge($respuesta, $datos), $estado);
    }


    /* Conservar la respuesta para los formularios enviados sin JavaScript */
    if ($accion === 'actualizar_perfil') {
        $_SESSION['mensaje_perfil'] = [
            'tipo' => $ok ? 'success' : 'danger',
            'texto' => $mensaje
        ];

        header('Location: Index.php?url=inicio');

    } else {
        header('Location: Index.php?url=inicio&clave=' . urlencode($codigo));
    }

    exit;
};


/* ===================================================== */
/* COMPROBACIÓN DE LA SESIÓN */
/* ===================================================== */

if (!Session::estaAutenticada()) {

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
/* RECEPCIÓN Y VALIDACIÓN DE LA SOLICITUD */
/* ===================================================== */

try {
    $datosFormulario = $_POST;
    $metodoSolicitud = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $tipoContenido = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));


    /* Leer las solicitudes enviadas como JSON */
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


    /* Aceptar únicamente las operaciones disponibles del perfil */
    $accion = $datosFormulario['accion'] ?? '';

    if (
        $metodoSolicitud !== 'POST' ||
        !is_string($accion) ||
        !in_array($accion, ['actualizar_perfil', 'cambiar_clave'], true)
    ) {
        $accion = '';

        $responderOperacion(
            false,
            'solicitud_invalida',
            'La solicitud enviada no es válida.',
            400
        );
    }


    /* Obtener la cuenta desde la sesión, sin recibir su identificador del formulario */
    $usuarioActual = Session::obtenerUsuario();
    $idUsuario = (int) ($usuarioActual['id_usuario'] ?? 0);

    if ($idUsuario <= 0) {
        $responderOperacion(
            false,
            'usuario_no_disponible',
            'No fue posible identificar la cuenta del usuario autenticado.',
            403
        );
    }


    /* Comprobar el token correspondiente al formulario */
    $nombreToken = $accion === 'actualizar_perfil'
        ? 'token_actualizar_perfil'
        : 'token_cambiar_clave';

    $tokenFormulario = $datosFormulario['token_csrf'] ?? '';
    $tokenSesion = $_SESSION[$nombreToken] ?? '';

    if (
        !is_string($tokenFormulario) ||
        !is_string($tokenSesion) ||
        $tokenFormulario === '' ||
        $tokenSesion === '' ||
        !hash_equals($tokenSesion, $tokenFormulario)
    ) {
        $responderOperacion(
            false,
            'solicitud_invalida',
            'La solicitud no es válida. Actualice la página e intente nuevamente.',
            403
        );
    }

    $modeloPerfil = new PerfilM();


    /* ================================================= */
    /* CAMBIAR LA CONTRASEÑA */
    /* ================================================= */

    if ($accion === 'cambiar_clave') {

        /* Recibir las contraseñas sin eliminar sus espacios */
        $claveActual = $datosFormulario['clave_actual'] ?? '';
        $nuevaClave = $datosFormulario['nueva_clave'] ?? '';
        $confirmarClave = $datosFormulario['confirmar_clave'] ?? '';

        if (
            !is_string($claveActual) ||
            !is_string($nuevaClave) ||
            !is_string($confirmarClave)
        ) {
            $responderOperacion(
                false,
                'solicitud_invalida',
                'Los datos enviados no tienen un formato válido.',
                422
            );
        }


        /* Comprobar que todos los campos estén completos */
        if ($claveActual === '' || $nuevaClave === '' || $confirmarClave === '') {
            $responderOperacion(
                false,
                'datos_incompletos',
                'Debe completar todos los campos solicitados.',
                422
            );
        }


        /* Comprobar la longitud de la nueva contraseña */
        if (strlen($nuevaClave) < 8) {
            $responderOperacion(
                false,
                'clave_corta',
                'La nueva contraseña debe contener un mínimo de ocho caracteres.',
                422,
                ['campo' => 'nueva_clave']
            );
        }


        /* Comprobar la confirmación de la contraseña */
        if ($nuevaClave !== $confirmarClave) {
            $responderOperacion(
                false,
                'no_coinciden',
                'La nueva contraseña y su confirmación no coinciden.',
                422,
                ['campo' => 'confirmar_clave']
            );
        }


        /* Recuperar la contraseña cifrada de la cuenta activa */
        $claveCifradaActual = $modeloPerfil->obtenerClaveActual($idUsuario);

        if ($claveCifradaActual === null) {
            $responderOperacion(
                false,
                'usuario_no_disponible',
                'No fue posible identificar la cuenta del usuario autenticado.',
                403
            );
        }


        /* Comprobar la contraseña actual */
        if (!password_verify($claveActual, $claveCifradaActual)) {
            $responderOperacion(
                false,
                'actual_incorrecta',
                'La contraseña actual suministrada no es correcta.',
                422,
                ['campo' => 'clave_actual']
            );
        }


        /* Impedir que se guarde nuevamente la misma contraseña */
        if (password_verify($nuevaClave, $claveCifradaActual)) {
            $responderOperacion(
                false,
                'misma_clave',
                'La nueva contraseña debe ser diferente de la contraseña actual.',
                422,
                ['campo' => 'nueva_clave']
            );
        }


        /* Preparar la renovación del token antes de modificar la contraseña */
        $nuevoToken = bin2hex(random_bytes(32));

        if (!$modeloPerfil->cambiarClave($idUsuario, $nuevaClave)) {
            throw new \RuntimeException('No fue posible actualizar la contraseña de la cuenta.');
        }


        /* Renovar el token y el identificador de la sesión */
        $_SESSION['token_cambiar_clave'] = $nuevoToken;

        session_regenerate_id(true);

        $responderOperacion(
            true,
            'actualizada',
            'La contraseña fue actualizada correctamente.'
        );
    }


    /* ================================================= */
    /* VALIDAR LOS DATOS DEL PERFIL */
    /* ================================================= */

    $nickname = $datosFormulario['nickname'] ?? '';
    $telefono = $datosFormulario['telefono'] ?? '';
    $correo = $datosFormulario['correo'] ?? '';

    if (!is_string($nickname) || !is_string($telefono) || !is_string($correo)) {
        $responderOperacion(
            false,
            'datos_invalidos',
            'Los datos enviados no tienen un formato válido.',
            422
        );
    }

    $nickname = trim($nickname);
    $telefono = trim($telefono);
    $correo = trim($correo);


    /* Comprobar la longitud del nickname */
    if (preg_match('/\A.{0,50}\z/us', $nickname) !== 1) {
        $responderOperacion(
            false,
            'datos_invalidos',
            'El nickname debe contener un máximo de 50 caracteres.',
            422,
            ['campo' => 'nickname']
        );
    }


    /* Validar el teléfono cuando sea suministrado */
    if ($telefono !== '' && !preg_match('/^[0-9+()\-\s]{7,20}$/', $telefono)) {
        $responderOperacion(
            false,
            'datos_invalidos',
            'El teléfono ingresado no es válido.',
            422,
            ['campo' => 'telefono']
        );
    }


    /* Validar el correo cuando sea suministrado */
    if ($correo !== '' && (strlen($correo) > 100 || !filter_var($correo, FILTER_VALIDATE_EMAIL))) {
        $responderOperacion(
            false,
            'datos_invalidos',
            'El correo electrónico ingresado no es válido.',
            422,
            ['campo' => 'correo']
        );
    }


    /* ================================================= */
    /* RECIBIR Y GUARDAR LA FOTOGRAFÍA */
    /* ================================================= */

    $nuevoToken = bin2hex(random_bytes(32));
    $archivoFoto = $_FILES['foto'] ?? null;

    if ($archivoFoto !== null) {

        /* Comprobar la estructura del archivo recibido */
        if (!is_array($archivoFoto) || !isset($archivoFoto['error']) || !is_int($archivoFoto['error'])) {
            $responderOperacion(
                false,
                'foto_invalida',
                'No fue posible recibir la fotografía seleccionada.',
                422,
                ['campo' => 'foto']
            );
        }

        $errorArchivo = $archivoFoto['error'];


        /* Permitir guardar los datos sin seleccionar una fotografía nueva */
        if ($errorArchivo !== UPLOAD_ERR_NO_FILE) {

            if (in_array($errorArchivo, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                $responderOperacion(
                    false,
                    'foto_invalida',
                    'La fotografía supera el tamaño permitido para la carga.',
                    422,
                    ['campo' => 'foto']
                );
            }

            if ($errorArchivo !== UPLOAD_ERR_OK) {
                $responderOperacion(
                    false,
                    'foto_invalida',
                    'La fotografía no se recibió correctamente. Selecciónela nuevamente.',
                    422,
                    ['campo' => 'foto']
                );
            }

            $rutaTemporal = $archivoFoto['tmp_name'] ?? '';

            if (!is_string($rutaTemporal) || !is_uploaded_file($rutaTemporal)) {
                $responderOperacion(
                    false,
                    'foto_invalida',
                    'El archivo recibido no es una carga válida.',
                    422,
                    ['campo' => 'foto']
                );
            }


            /* Comprobar el tamaño real del archivo temporal */
            $tamanoMaximo = 2 * 1024 * 1024;
            $tamanoArchivo = filesize($rutaTemporal);

            if ($tamanoArchivo === false) {
                throw new \RuntimeException('No fue posible consultar el tamaño de la fotografía.');
            }

            if ($tamanoArchivo <= 0 || $tamanoArchivo > $tamanoMaximo) {
                $responderOperacion(
                    false,
                    'foto_invalida',
                    'Seleccione una fotografía de hasta 2 MB.',
                    422,
                    ['campo' => 'foto']
                );
            }


            /* Identificar el formato por el contenido del archivo */
            $tiposPermitidos = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            $informacionArchivo = new \finfo(FILEINFO_MIME_TYPE);
            $tipoArchivo = $informacionArchivo->file($rutaTemporal);
            $dimensiones = @getimagesize($rutaTemporal);

            if (
                !isset($tiposPermitidos[$tipoArchivo]) ||
                $dimensiones === false ||
                ($dimensiones['mime'] ?? '') !== $tipoArchivo ||
                $dimensiones[0] <= 0 ||
                $dimensiones[1] <= 0
            ) {
                $responderOperacion(
                    false,
                    'foto_invalida',
                    'La fotografía debe ser una imagen JPEG, PNG o WebP válida.',
                    422,
                    ['campo' => 'foto']
                );
            }


            /* Crear la carpeta de fotografías cuando todavía no exista */
            if (!is_dir($directorioFotos)) {
                if (!@mkdir($directorioFotos, 0755, true) && !is_dir($directorioFotos)) {
                    throw new \RuntimeException('No fue posible crear la carpeta de fotografías del perfil.');
                }
            }


            /* Generar el nombre y la extensión sin utilizar el nombre original */
            $nombreFoto = 'perfil_' . $idUsuario . '_' . bin2hex(random_bytes(16));
            $nombreFoto .= '.' . $tiposPermitidos[$tipoArchivo];

            $fotoNueva = 'Assets/uploads/perfiles/' . $nombreFoto;
            $destinoFoto = $raizProyecto . '/' . $fotoNueva;

            if (!@move_uploaded_file($rutaTemporal, $destinoFoto)) {
                throw new \RuntimeException('No fue posible guardar la fotografía recibida.');
            }
        }
    }


    /* ================================================= */
    /* ACTUALIZAR EL PERFIL Y LA SESIÓN */
    /* ================================================= */

    $resultado = $modeloPerfil->actualizarPerfil(
        $idUsuario,
        $nickname,
        $telefono,
        $correo,
        $fotoNueva
    );

    if ($resultado === null) {

        /* Retirar el archivo nuevo cuando la cuenta ya no esté disponible */
        if ($fotoNueva !== null) {
            $eliminarFoto($fotoNueva);
        }

        $responderOperacion(
            false,
            'usuario_no_disponible',
            'No fue posible identificar la cuenta del usuario autenticado.',
            403
        );
    }

    $perfilGuardado = true;
    $usuarioActualizado = $resultado['usuario'];


    /* Conservar los demás datos de la sesión y excluir la contraseña */
    $_SESSION['usuario'] = array_replace($usuarioActual, $usuarioActualizado);

    unset($_SESSION['usuario']['clave']);


    /* Renovar el token después del guardado */
    $_SESSION['token_actualizar_perfil'] = $nuevoToken;


    /* Retirar la fotografía anterior únicamente después de confirmar el guardado */
    $fotoAnterior = (string) ($resultado['foto_anterior'] ?? '');

    if ($fotoNueva !== null && $fotoAnterior !== '' && $fotoAnterior !== $fotoNueva) {
        $eliminarFoto($fotoAnterior);
    }


    /* Entregar únicamente los datos necesarios para actualizar la interfaz */
    $responderOperacion(
        true,
        'actualizado',
        'El perfil fue actualizado correctamente.',
        200,
        [
            'usuario' => [
                'nickname' => $usuarioActualizado['nickname'],
                'nombre' => $usuarioActualizado['nombre'],
                'apellido' => $usuarioActualizado['apellido'],
                'telefono' => $usuarioActualizado['telefono'],
                'correo' => $usuarioActualizado['correo'],
                'foto_perfil' => $usuarioActualizado['foto_perfil']
            ]
        ]
    );

} catch (\Throwable $error) {

    /* Retirar la fotografía nueva si no se confirmó la actualización */
    if (!$perfilGuardado && $fotoNueva !== null) {
        $eliminarFoto($fotoNueva);
    }


    /* Registrar el error técnico sin exponerlo en la respuesta */
    error_log($error->getMessage());

    if ($error instanceof \JsonException || $error instanceof \InvalidArgumentException) {
        $responderOperacion(
            false,
            'solicitud_invalida',
            'Los datos enviados no tienen un formato válido.',
            422
        );
    }

    $mensajeError = $accion === 'cambiar_clave'
        ? 'No fue posible actualizar la contraseña. Intente nuevamente.'
        : 'No fue posible actualizar el perfil. Intente nuevamente.';

    $responderOperacion(false, 'error', $mensajeError, 500);
}