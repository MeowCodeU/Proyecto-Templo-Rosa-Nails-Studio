<?php

use App\Components\Session;
use App\Model\LoginM;


/* ===================================================== */
/* INICIO DE SESIÓN Y RECUPERACIÓN DE CONTRASEÑA */
/* ===================================================== */

/* Variables utilizadas en la vista */
$mensajeLogin = '';
$tipoMensajeLogin = 'error';
$cedulaLogin = '';
$erroresLogin = [];
$modeloLogin = null;
$accionLogin = '';
$esRecuperacion = false;

/* Datos utilizados por el modal de recuperación */
$mensajeRecuperacion = '';
$tipoMensajeRecuperacion = 'info';
$erroresRecuperacion = [];
$mostrarRecuperacion = false;
$pasoRecuperacion = 'solicitar';
$tokenRecuperacionClave = '';
$datosRecuperacion = ['cedula' => '', 'correo' => ''];


/* Identificar las solicitudes asíncronas */
$contenidoJsonLogin = stripos((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json') === 0;

$esPeticionAjax = $contenidoJsonLogin || strtolower(
    (string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')
) === 'xmlhttprequest';


/* Enviar la respuesta y finalizar la solicitud */
$responderJson = static function (array $datos, int $estado = 200) use (
    &$esRecuperacion,
    &$tokenRecuperacionClave
): void {
    if ($esRecuperacion) {
        $datos['token_csrf'] = $tokenRecuperacionClave;
    }

    http_response_code($estado);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    exit;
};


/* Mostrar mensajes temporales enviados por otros controladores */
if (isset($_SESSION['mensaje_login'])) {
    $mensajeLogin = (string) $_SESSION['mensaje_login'];
    $tipoMensajeLogin = $_SESSION['tipo_mensaje_login'] ?? 'info';

    unset($_SESSION['mensaje_login'], $_SESSION['tipo_mensaje_login']);
}


/* Redirigir cuando ya existe una sesión activa */
if (Session\estaAutenticada()) {
    if ($esPeticionAjax) {
        $responderJson([
            'ok' => true,
            'redireccion' => 'Index.php?url=inicio'
        ]);
    }

    header('Location: Index.php?url=inicio');
    exit;
}


/* Preparar el token de los formularios públicos de recuperación */
if (!isset($_SESSION['token_recuperacion_clave']) ||
    !is_string($_SESSION['token_recuperacion_clave']) ||
    strlen($_SESSION['token_recuperacion_clave']) !== 64) {
    $_SESSION['token_recuperacion_clave'] = bin2hex(random_bytes(32));
}

$tokenRecuperacionClave = $_SESSION['token_recuperacion_clave'];


/* Recuperar únicamente los datos de identificación, nunca las contraseñas */
$datosAnterioresRecuperacion = $_SESSION['datos_recuperacion'] ?? [];

if (is_array($datosAnterioresRecuperacion)) {
    foreach (array_keys($datosRecuperacion) as $campo) {
        $valor = $datosAnterioresRecuperacion[$campo] ?? '';
        $datosRecuperacion[$campo] = is_string($valor) ? $valor : '';
    }
}

if (isset($_SESSION['solicitud_recuperacion']) && is_string($_SESSION['solicitud_recuperacion'])) {
    $pasoRecuperacion = 'restablecer';
}


/* Conservar el motivo específico del cierre de sesión */
if ($mensajeLogin === '' && ($_GET['sesion'] ?? '') === 'expirada') {
    $mensajeLogin = 'La sesión expiró por inactividad. Inicie sesión nuevamente.';
    $tipoMensajeLogin = 'warning';
}


/* ===================================================== */
/* PROCESAR LOS FORMULARIOS DE ACCESO */
/* ===================================================== */

try {
    $datosFormulario = $_POST;
    $metodoSolicitud = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if (!in_array($metodoSolicitud, ['GET', 'POST'], true)) {
        header('Allow: GET, POST');
        $responderJson(['ok' => false, 'mensaje' => 'El método solicitado no está permitido.'], 405);
    }


    /* Leer los datos enviados en el cuerpo de la solicitud */
    if ($metodoSolicitud === 'POST' && $contenidoJsonLogin) {
        $entradaLogin = json_decode(file_get_contents('php://input'), false, 32, JSON_THROW_ON_ERROR);

        if (!$entradaLogin instanceof stdClass) {
            throw new InvalidArgumentException('Los datos enviados no tienen un formato válido.');
        }

        $datosFormulario = get_object_vars($entradaLogin);
    }


    if ($metodoSolicitud === 'POST') {
        $accionLogin = $datosFormulario['accion'] ?? '';

        if (!is_string($accionLogin) || !in_array(
            $accionLogin,
            ['iniciar_sesion', 'solicitar_recuperacion', 'recuperar_clave'],
            true
        )) {
            throw new InvalidArgumentException('La solicitud enviada no es válida.');
        }

        $esRecuperacion = $accionLogin !== 'iniciar_sesion';


        /* ================================================= */
        /* RECUPERACIÓN DE CONTRASEÑA */
        /* ================================================= */

        if ($esRecuperacion) {
            $mostrarRecuperacion = true;
            $estadoRecuperacion = 422;
            $tokenFormulario = $datosFormulario['token_csrf'] ?? '';

            /* Comprobar que el formulario corresponda a la sesión actual */
            if (!is_string($tokenFormulario) || $tokenFormulario === '' ||
                !hash_equals($tokenRecuperacionClave, $tokenFormulario)) {
                $estadoRecuperacion = 403;

                $respuestaRecuperacion = [
                    'ok' => false,
                    'codigo' => 'solicitud_invalida',
                    'mensaje' => 'La solicitud no es válida. Actualice la página e intente nuevamente.',
                    'errores' => [],
                    'paso' => $pasoRecuperacion,
                    'tipo' => 'error'
                ];

            } else {
                $ultimoEnvio = $_SESSION['recuperacion_ultimo_envio'] ?? 0;
                $ultimoEnvio = is_int($ultimoEnvio) ? $ultimoEnvio : 0;
                $esperaReenvio = max(0, $ultimoEnvio + 60 - time());

                /* Limitar también las solicitudes consecutivas de esta sesión */
                if ($accionLogin === 'solicitar_recuperacion' && $esperaReenvio > 0) {
                    $estadoRecuperacion = 429;

                    $respuestaRecuperacion = [
                        'ok' => false,
                        'codigo' => 'esperar_reenvio',
                        'mensaje' => 'Espere antes de solicitar otro código de recuperación.',
                        'errores' => [],
                        'paso' => $pasoRecuperacion,
                        'tipo' => 'warning',
                        'reintentar_en' => $esperaReenvio
                    ];

                } else {
                    $modeloLogin = new LoginM();

                    if ($accionLogin === 'solicitar_recuperacion') {
                        foreach (array_keys($datosRecuperacion) as $campo) {
                            $valor = $datosFormulario[$campo] ?? '';
                            $datosRecuperacion[$campo] = is_string($valor) ? trim($valor) : '';
                        }

                        /* El modelo valida la identificación y gestiona el envío */
                        $solicitud = $modeloLogin->solicitarRecuperacion(
                            $datosFormulario['cedula'] ?? '',
                            $datosFormulario['correo'] ?? '',
                            $_SESSION['solicitud_recuperacion'] ?? null
                        );

                        $correcto = $solicitud !== null;

                        if ($correcto) {
                            /* Mantener el identificador privado fuera de las respuestas JSON */
                            $_SESSION['solicitud_recuperacion'] = $solicitud;
                            $_SESSION['datos_recuperacion'] = $datosRecuperacion;
                            $_SESSION['recuperacion_ultimo_envio'] = time();
                            $pasoRecuperacion = 'restablecer';
                        }

                    } else {
                        /* Preparar la renovación antes de que se confirme el cambio */
                        $nuevoTokenRecuperacion = bin2hex(random_bytes(32));

                        $correcto = $modeloLogin->recuperarClave(
                            $_SESSION['solicitud_recuperacion'] ?? null,
                            $datosFormulario['codigo_recuperacion'] ?? '',
                            $datosFormulario['nueva_clave'] ?? '',
                            $datosFormulario['confirmar_clave'] ?? ''
                        );

                        if ($correcto) {
                            unset($_SESSION['solicitud_recuperacion'], $_SESSION['datos_recuperacion']);

                            $_SESSION['token_recuperacion_clave'] = $nuevoTokenRecuperacion;
                            $tokenRecuperacionClave = $nuevoTokenRecuperacion;
                            $datosRecuperacion = ['cedula' => '', 'correo' => ''];
                            $pasoRecuperacion = 'completado';

                            /* Conservar el resultado confirmado aunque falle la renovación de sesión */
                            if (!@session_regenerate_id(true)) {
                                error_log('Login: no fue posible renovar la sesión después de recuperar la contraseña.');
                            }
                        }
                    }

                    $estadoRecuperacion = $modeloLogin->obtenerEstadoRespuesta();
                    $erroresRecuperacion = $modeloLogin->obtenerErroresValidacion();

                    $respuestaRecuperacion = [
                        'ok' => $correcto,
                        'codigo' => $modeloLogin->obtenerCodigoResultado(),
                        'mensaje' => $modeloLogin->obtenerMensajeAcceso(),
                        'campo' => array_key_first($erroresRecuperacion),
                        'errores' => $erroresRecuperacion,
                        'paso' => $pasoRecuperacion,
                        'tipo' => $correcto ? ($accionLogin === 'solicitar_recuperacion' ? 'info' : 'success') : 'error'
                    ];

                    if ($correcto && $accionLogin === 'solicitar_recuperacion') {
                        $respuestaRecuperacion['reintentar_en'] = 60;
                    }
                }
            }

            if ($esPeticionAjax) {
                $responderJson($respuestaRecuperacion, $estadoRecuperacion);
            }

            /* Conservar el envío tradicional de los formularios */
            if ($respuestaRecuperacion['ok'] && $accionLogin === 'recuperar_clave') {
                $_SESSION['mensaje_login'] = $respuestaRecuperacion['mensaje'];
                $_SESSION['tipo_mensaje_login'] = 'success';

                header('Location: Index.php?url=login');
                exit;
            }

            $mensajeRecuperacion = $respuestaRecuperacion['mensaje'];
            $tipoMensajeRecuperacion = $respuestaRecuperacion['tipo'];
            $erroresRecuperacion = $respuestaRecuperacion['errores'];
            http_response_code($estadoRecuperacion);

        } else {

            /* ================================================= */
            /* INICIAR LA SESIÓN */
            /* ================================================= */

            $cedula = $datosFormulario['cedula'] ?? '';
            $clave = $datosFormulario['clave'] ?? '';

            /* Conservar la cédula como texto para volver a mostrar el formulario */
            $cedulaLogin = is_string($cedula) ? trim($cedula) : '';

            /* Delegar la validación y la autenticación al modelo */
            $modeloLogin = new LoginM();
            $usuario = $modeloLogin->autenticar($cedula, $clave);

            if ($usuario !== null) {
                Session\registrarUsuario($usuario);
                unset($_SESSION['solicitud_recuperacion'], $_SESSION['datos_recuperacion']);

                if ($esPeticionAjax) {
                    $responderJson([
                        'ok' => true,
                        'redireccion' => 'Index.php?url=inicio'
                    ]);
                }

                header('Location: Index.php?url=inicio');
                exit;
            }

            $mensajeLogin = $modeloLogin->obtenerMensajeAcceso();
            $erroresLogin = $modeloLogin->obtenerErroresValidacion();

            if ($esPeticionAjax) {
                $responderJson([
                    'ok' => false,
                    'mensaje' => $mensajeLogin,
                    'campo' => array_key_first($erroresLogin),
                    'errores' => $erroresLogin
                ], $modeloLogin->obtenerEstadoRespuesta());
            }
        }

    } elseif ($esPeticionAjax) {
        $responderJson([
            'ok' => false,
            'mensaje' => 'La solicitud enviada no es válida.'
        ], 400);
    }

} catch (Throwable $error) {
    $estadoRespuesta = 500;
    $mensajeError = $esRecuperacion
        ? 'No fue posible completar la recuperación de contraseña. Intente nuevamente.'
        : 'No fue posible validar el acceso. Intente nuevamente.';
    $erroresRespuesta = [];

    if ($error instanceof JsonException) {
        $estadoRespuesta = 422;
        $mensajeError = 'Los datos enviados no tienen un formato válido.';

    } elseif ($error instanceof InvalidArgumentException) {
        $estadoRespuesta = 422;
        $mensajeError = $error->getMessage();

        if ($modeloLogin instanceof LoginM) {
            $erroresRespuesta = $modeloLogin->obtenerErroresValidacion();
        }

    } else {
        error_log('Login: ' . $error->getMessage());
    }

    if ($esPeticionAjax) {
        $respuestaError = [
            'ok' => false,
            'mensaje' => $mensajeError,
            'campo' => array_key_first($erroresRespuesta),
            'errores' => $erroresRespuesta
        ];

        if ($esRecuperacion) {
            $respuestaError['codigo'] = 'operacion_no_confirmada';
            $respuestaError['paso'] = $pasoRecuperacion;
            $respuestaError['tipo'] = 'error';
        }

        $responderJson($respuestaError, $estadoRespuesta);
    }

    if ($esRecuperacion) {
        $mostrarRecuperacion = true;
        $mensajeRecuperacion = $mensajeError;
        $tipoMensajeRecuperacion = 'error';
        $erroresRecuperacion = $erroresRespuesta;
        http_response_code($estadoRespuesta);

    } else {
        $mensajeLogin = $mensajeError;
        $erroresLogin = $erroresRespuesta;
    }
}


/* ===================================================== */
/* CARGA DE LA VISTA */
/* ===================================================== */

/* Preparar los datos que presenta el modal de recuperación */
$mensajeRecuperacion = is_string($mensajeRecuperacion ?? null) ? $mensajeRecuperacion : '';
$tipoMensajeRecuperacion = in_array($tipoMensajeRecuperacion ?? '', ['info', 'success', 'warning', 'error'], true)
    ? $tipoMensajeRecuperacion : 'info';
$erroresRecuperacion = is_array($erroresRecuperacion ?? null)
    ? array_filter($erroresRecuperacion, 'is_string') : [];
$mostrarRecuperacion = ($mostrarRecuperacion ?? false) === true;
$pasoRecuperacion = ($pasoRecuperacion ?? '') === 'restablecer' ? 'restablecer' : 'solicitar';
$tokenRecuperacionClave = is_string($tokenRecuperacionClave ?? null) ? $tokenRecuperacionClave : '';

$cedulaRecuperacion = is_string($datosRecuperacion['cedula'] ?? null) ? $datosRecuperacion['cedula'] : '';
$correoRecuperacion = is_string($datosRecuperacion['correo'] ?? null) ? $datosRecuperacion['correo'] : '';

/* Presentar la espera restante sin decidir la autorización del reenvío */
$ultimoEnvioRecuperacion = $_SESSION['recuperacion_ultimo_envio'] ?? 0;
$reintentarRecuperacion = is_int($ultimoEnvioRecuperacion)
    ? max(0, $ultimoEnvioRecuperacion + 60 - time()) : 0;


require_once __DIR__ . '/../View/LoginV.php';