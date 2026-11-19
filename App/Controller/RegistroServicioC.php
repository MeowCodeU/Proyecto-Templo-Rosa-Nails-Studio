<?php

use App\Components\Session;
use App\Model\RegistroServicioM;


/* ===================================================== */
/* GESTIÓN DEL REGISTRO DE SERVICIO */
/* ===================================================== */

/* Identificar el método y el formato de la solicitud */
$metodoSolicitud = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$tipoContenido = strtolower(
    trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0])
);

$esContenidoJson = $tipoContenido === 'application/json';

$esPeticionAjax = $esContenidoJson ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

$esFotografia = $metodoSolicitud === 'GET' &&
    ($_GET['accion'] ?? '') === 'foto';

$modeloRegistroServicio = null;


/* ===================================================== */
/* RESPUESTAS DEL CONTROLADOR */
/* ===================================================== */

/* Enviar los resultados de las operaciones */
$responderJson = static function (array $datos, int $estado = 200): void {
    http_response_code($estado);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    /* Entregar el token utilizado por los formularios del módulo */
    $token = $_SESSION['token_registro_servicio'] ?? '';

    if (is_string($token) && $token !== '') {
        $datos['token_csrf'] = $token;
    }

    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
};


/* Comunicar errores al solicitar una imagen o abrir el módulo */
$responderTexto = static function (string $mensaje, int $estado): void {
    http_response_code($estado);

    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');

    echo $mensaje;

    exit;
};


/* ===================================================== */
/* COMPROBACIÓN DE LA SESIÓN */
/* ===================================================== */

if (!Session\estaAutenticada()) {
    if ($esPeticionAjax || $metodoSolicitud === 'POST') {
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
/* PROCESAMIENTO DE LA SOLICITUD */
/* ===================================================== */

try {
    if (!in_array($metodoSolicitud, ['GET', 'POST'], true)) {
        header('Allow: GET, POST');

        $responderJson([
            'ok' => false,
            'mensaje' => 'Método no permitido.'
        ], 405);
    }

    /* Preparar el token sin invalidar otros formularios abiertos */
    if (
        !isset($_SESSION['token_registro_servicio']) ||
        !is_string($_SESSION['token_registro_servicio']) ||
        $_SESSION['token_registro_servicio'] === ''
    ) {
        $_SESSION['token_registro_servicio'] = bin2hex(random_bytes(32));
    }

    $tokenRegistroServicio = $_SESSION['token_registro_servicio'];
    $datosSolicitud = $metodoSolicitud === 'GET' ? $_GET : $_POST;

    /* Leer el cuerpo enviado como JSON */
    if ($metodoSolicitud === 'POST' && $esContenidoJson) {
        $contenidoSolicitud = file_get_contents('php://input');

        if ($contenidoSolicitud === false) {
            throw new RuntimeException(
                'No fue posible leer el contenido de la solicitud.'
            );
        }

        $datosJson = json_decode(
            $contenidoSolicitud,
            false,
            32,
            JSON_THROW_ON_ERROR
        );

        if (!$datosJson instanceof stdClass) {
            throw new InvalidArgumentException(
                'Los datos enviados no tienen un formato válido.'
            );
        }

        $datosSolicitud = get_object_vars($datosJson);

        /* Convertir las filas de las listas conservando la estructura del JSON */
        foreach ($datosSolicitud as $campo => $valor) {
            if (is_array($valor)) {
                $datosSolicitud[$campo] = array_map(
                    static fn($fila) => $fila instanceof stdClass ? get_object_vars($fila) : $fila,
                    $valor
                );
            }
        }
    }

    $accion = $datosSolicitud['accion'] ?? '';

    if (!is_string($accion)) {
        throw new InvalidArgumentException(
            'La acción solicitada no es válida.'
        );
    }

    $modeloRegistroServicio = new RegistroServicioM();


    /* ================================================= */
    /* MOSTRAR UNA FOTOGRAFÍA */
    /* ================================================= */

    if ($esFotografia) {
        $idFoto = $datosSolicitud['id_foto'] ?? null;

        /* Liberar la sesión mientras se consulta y entrega la imagen */
        session_write_close();

        $fotografia = $modeloRegistroServicio->obtenerFotografia($idFoto);

        if ($fotografia === null) {
            $responderTexto('La fotografía no está disponible.', 404);
        }

        $contenidoFoto = $fotografia['foto'];

        if (is_resource($contenidoFoto)) {
            $contenidoFoto = stream_get_contents($contenidoFoto);
        }

        if (!is_string($contenidoFoto) || $contenidoFoto === '') {
            throw new RuntimeException(
                'La fotografía almacenada no contiene datos válidos.'
            );
        }

        /* Determinar el Content-Type de la imagen almacenada que se entregará al navegador */
        $informacionArchivo = new finfo(FILEINFO_MIME_TYPE);
        $tipoFoto = $informacionArchivo->buffer($contenidoFoto);

        if (!in_array($tipoFoto, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $responderTexto('El formato de la fotografía no está disponible.', 415);
        }

        header('Content-Type: ' . $tipoFoto);
        header('Content-Length: ' . strlen($contenidoFoto));
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');

        echo $contenidoFoto;

        exit;
    }


    /* ================================================= */
    /* CARGAR LA VISTA */
    /* ================================================= */

    if ($metodoSolicitud === 'GET' && $accion === '' && !$esPeticionAjax) {
        require_once __DIR__ . '/../View/RegistroServicioV.php';

        return;
    }

    if ($metodoSolicitud === 'GET' && $accion === '') {
        $accion = 'consultar';
    }


    /* ================================================= */
    /* CONSULTAR DATOS DEL MÓDULO */
    /* ================================================= */

    switch ($accion) {
        case 'consultar':
            $responderJson([
                'ok' => true,
                'agendamientos' => $modeloRegistroServicio->consultar(),
                'insumos' => $modeloRegistroServicio->consultarInsumos()
            ]);

        case 'buscar':
            $idAgendamiento = $datosSolicitud['id_agendamiento'] ?? null;

            $atencion = $modeloRegistroServicio->buscar($idAgendamiento);

            if ($atencion === null) {
                $responderJson([
                    'ok' => false,
                    'mensaje' => 'El agendamiento no está disponible.'
                ], 404);
            }

            $responderJson([
                'ok' => true,
                'atencion' => $atencion,
                'insumos' => $modeloRegistroServicio->consultarInsumos()
            ]);

        case 'historial':
            $idCliente = $datosSolicitud['id_cliente'] ?? null;

            $responderJson([
                'ok' => true,
                'historial' => $modeloRegistroServicio->consultarHistorial($idCliente)
            ]);

        case 'catalogo':
            $responderJson([
                'ok' => true,
                'catalogo' => $modeloRegistroServicio->consultarCatalogo()
            ]);
    }


    /* ================================================= */
    /* COMPROBAR LA OPERACIÓN DE GUARDADO */
    /* ================================================= */

    $accionesPermitidas = [
        'iniciarAtencion',
        'guardarServicio',
        'guardarInsumos',
        'finalizarRegistro'
    ];

    if (!in_array($accion, $accionesPermitidas, true)) {
        throw new InvalidArgumentException(
            'La acción solicitada no es válida.'
        );
    }

    if ($metodoSolicitud !== 'POST') {
        header('Allow: POST');

        $responderJson([
            'ok' => false,
            'mensaje' => 'Esta operación debe enviarse mediante POST.'
        ], 405);
    }

    /* Comprobar el token antes de modificar la atención */
    $tokenFormulario = $datosSolicitud['token_csrf'] ?? '';

    if (
        !is_string($tokenFormulario) ||
        $tokenFormulario === '' ||
        !hash_equals($tokenRegistroServicio, $tokenFormulario)
    ) {
        $responderJson([
            'ok' => false,
            'codigo' => 'token_invalido',
            'mensaje' => 'La solicitud no es válida. Actualice la página e intente nuevamente.'
        ], 403);
    }

    $idAgendamiento = $datosSolicitud['id_agendamiento'] ?? null;


    /* ================================================= */
    /* INICIAR LA ATENCIÓN */
    /* ================================================= */

    /* El estado cambia cuando se pulsa Atender, no al guardar servicios */
    if ($accion === 'iniciarAtencion') {
        if (!$modeloRegistroServicio->iniciarAtencion($idAgendamiento)) {
            throw new RuntimeException(
                'El modelo no confirmó el inicio de la atención.'
            );
        }

        $responderJson([
            'ok' => true,
            'mensaje' => 'La atención fue iniciada correctamente.',
            'id_agendamiento' => (int) $idAgendamiento
        ]);
    }


    /* ================================================= */
    /* GUARDAR EL SERVICIO REALIZADO */
    /* ================================================= */

    if ($accion === 'guardarServicio') {
        $correcto = $modeloRegistroServicio->guardarServicio(
            $idAgendamiento,
            $datosSolicitud['id_detalle_agendamiento'] ?? null,
            $datosSolicitud['precio_aplicado'] ?? null,
            $datosSolicitud['nota_tecnica'] ?? '',
            $_FILES['foto_servicio'] ?? null,
            $datosSolicitud['destacada_catalogo'] ?? false
        );

        if (!$correcto) {
            throw new RuntimeException(
                'El modelo no confirmó el guardado del servicio.'
            );
        }

        $responderJson([
            'ok' => true,
            'mensaje' => 'El servicio fue guardado correctamente.',
            'id_agendamiento' => (int) $idAgendamiento
        ]);
    }


    /* ================================================= */
    /* GUARDAR LOS INSUMOS UTILIZADOS */
    /* ================================================= */

    if ($accion === 'guardarInsumos') {
        $correcto = $modeloRegistroServicio->guardarInsumos(
            $idAgendamiento,
            $datosSolicitud['insumos'] ?? null
        );

        if (!$correcto) {
            throw new RuntimeException(
                'El modelo no confirmó el guardado de los insumos.'
            );
        }

        $responderJson([
            'ok' => true,
            'mensaje' => 'Los insumos utilizados fueron guardados correctamente.',
            'id_agendamiento' => (int) $idAgendamiento
        ]);
    }


    /* ================================================= */
    /* FINALIZAR LA ATENCIÓN */
    /* ================================================= */

    if (!$modeloRegistroServicio->finalizar($idAgendamiento)) {
        throw new RuntimeException(
            'El modelo no confirmó la finalización de la atención.'
        );
    }

    $responderJson([
        'ok' => true,
        'mensaje' => 'La atención fue finalizada correctamente.',
        'id_agendamiento' => (int) $idAgendamiento
    ]);

} catch (JsonException $error) {
    $responderJson([
        'ok' => false,
        'mensaje' => 'Los datos enviados no contienen un JSON válido.'
    ], 422);

} catch (InvalidArgumentException $error) {
    if ($esFotografia) {
        $responderTexto($error->getMessage(), 422);
    }

    $respuesta = [
        'ok' => false,
        'mensaje' => $error->getMessage()
    ];

    $errores = $modeloRegistroServicio
        ? $modeloRegistroServicio->obtenerErroresValidacion()
        : [];

    if ($errores !== []) {
        $respuesta['campo'] = array_key_first($errores);
        $respuesta['errores'] = $errores;
    }

    $responderJson($respuesta, 422);

} catch (Throwable $error) {
    /* Registrar el detalle técnico sin mostrar información interna */
    error_log('Registro de Servicio: ' . $error->getMessage());

    if ($esFotografia) {
        $responderTexto('No fue posible mostrar la fotografía.', 500);
    }

    /* Comunicar conflictos transitorios de bloqueo */
    if (
        $error instanceof PDOException &&
        in_array((int) ($error->errorInfo[1] ?? 0), [1205, 1213], true)
    ) {
        $responderJson([
            'ok' => false,
            'mensaje' => 'La atención o el inventario están siendo actualizados. Intente nuevamente.'
        ], 409);
    }

    if ($metodoSolicitud === 'GET' && !$esPeticionAjax && !isset($_GET['accion'])) {
        $responderTexto(
            'No fue posible abrir Registro de Servicio. Intente nuevamente.',
            500
        );
    }

    $responderJson([
        'ok' => false,
        'mensaje' => 'No fue posible completar la operación. Intente nuevamente.'
    ], 500);
}