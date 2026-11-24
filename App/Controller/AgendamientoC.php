<?php

use App\Components\Session;
use App\Model\AgendamientoM;
use App\Model\BloqueoHorarioM;
use App\Model\ClientesM;
use App\Model\SeguridadM;
use App\Model\ServicioM;
use App\Model\EstadoAgendamientoM;
use App\Model\TipoAgendamientoM;


/* ===================================================== */
/* GESTIÓN DE AGENDAMIENTO */
/* ===================================================== */


/* ================================================= */
/* IDENTIFICAR LA SOLICITUD */
/* ================================================= */

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$esPeticionAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
$tipoContenido = (string) ($_SERVER['CONTENT_TYPE'] ?? '');
$esCuerpoJson = strtolower(trim(explode(';', $tipoContenido)[0])) === 'application/json';
$datosSolicitud = $_POST;
$modeloOperacion = null;
$codigoNoDisponible = 'cita_no_disponible';


/* ================================================= */
/* RESPUESTAS Y DATOS DE LA SOLICITUD */
/* ================================================= */

$responderJson = static function (array $datos, int $codigo = 200): void {
    http_response_code($codigo);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    exit;
};


/* Recibir el formato de transporte antes de ejecutar la operación */
if ($metodo === 'POST' && $esCuerpoJson) {
    try {
        $datosSolicitud = json_decode(
            (string) file_get_contents('php://input'),
            true,
            32,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($datosSolicitud) || !is_string($datosSolicitud['accion'] ?? null)) {
            $responderJson([
                'ok' => false,
                'codigo' => 'solicitud_invalida',
                'mensaje' => 'La solicitud debe indicar una acción válida.'
            ], 400);
        }

    } catch (JsonException $error) {
        $responderJson([
            'ok' => false,
            'codigo' => 'json_invalido',
            'mensaje' => 'Los datos enviados no contienen un JSON válido.'
        ], 400);
    }
}

$accion = $metodo === 'POST' ? ($datosSolicitud['accion'] ?? '') : ($_GET['accion'] ?? '');
$accion = is_string($accion) ? $accion : '';

$requiereRespuestaJson = $esPeticionAjax ||
    ($metodo === 'POST' && $esCuerpoJson) ||
    ($metodo === 'GET' && $accion === 'consultar_calendario') ||
    (
        $metodo === 'POST' &&
        in_array($accion, ['validar_disponibilidad_agendamiento', 'validar_bloqueo_horario'], true)
    );


/* Responder a AJAX o conservar la redirección del formulario normal */
$finalizarOperacion = static function (
    bool $correcto,
    string $codigo,
    string $mensaje,
    int $estadoHttp = 200,
    array $errores = [],
    string $tipoAviso = 'success'
) use ($requiereRespuestaJson, $responderJson): void {
    $respuesta = [
        'ok' => $correcto,
        'codigo' => $codigo,
        'mensaje' => $mensaje,
        'tipo_aviso' => $correcto ? $tipoAviso : 'error'
    ];

    if ($errores !== []) {
        $respuesta['campo'] = array_key_first($errores);
        $respuesta['errores'] = $errores;
    }

    if ($requiereRespuestaJson) $responderJson($respuesta, $estadoHttp);

    $_SESSION['mensaje_agendamiento'] = $respuesta;

    header('Location: Index.php?url=agendamiento&mensaje=' . urlencode($codigo));

    exit;
};


/* Convertir las fechas consultadas al formato que presenta el calendario */
$obtenerInicio = static function (string $fecha, string $hora): ?DateTimeImmutable {
    foreach (['Y-m-d H:i', 'Y-m-d H:i:s'] as $formato) {
        $inicio = DateTimeImmutable::createFromFormat('!' . $formato, $fecha . ' ' . $hora);

        if ($inicio !== false && $inicio->format($formato) === $fecha . ' ' . $hora) return $inicio;
    }

    return null;
};


/* Traducir el resultado del modelo a los códigos que utiliza la interfaz */
$responderDisponibilidadRechazada = static function (
    array $disponibilidad,
    bool $esBloqueo
) use ($finalizarOperacion): void {
    if ($disponibilidad['disponible']) return;

    $codigos = $esBloqueo
        ? [
            'bloqueo' => 'bloqueo_conflicto_bloqueo',
            'cita' => 'bloqueo_conflicto_cita',
            'datos_invalidos' => 'bloqueo_datos_invalidos'
        ]
        : [
            'bloqueo' => 'horario_bloqueado',
            'cita' => 'horario_ocupado',
            'datos_invalidos' => 'datos_incompletos'
        ];

    $finalizarOperacion(
        false,
        $codigos[$disponibilidad['tipo']],
        $disponibilidad['mensaje'],
        $disponibilidad['tipo'] === 'datos_invalidos' ? 422 : 409,
        $disponibilidad['errores'] ?? []
    );
};


try {

    /* ================================================= */
    /* MODELOS Y USUARIO AUTENTICADO */
    /* ================================================= */

    $modeloAgendamiento = new AgendamientoM();
    $modeloBloqueoHorario = new BloqueoHorarioM();

    $usuarioSesion = Session\obtenerUsuario();
    $idUsuarioRegistra = (int) ($usuarioSesion['id_usuario'] ?? 0);


    /* ================================================= */
    /* PROCESAR LAS OPERACIONES DEL MÓDULO */
    /* ================================================= */

    if ($metodo === 'POST') {
        if ($idUsuarioRegistra <= 0) {
            $finalizarOperacion(
                false,
                'sesion_no_disponible',
                'La sesión no se encuentra activa.',
                401
            );
        }


        /* Consultar la disponibilidad sin transformar los datos recibidos */
        if ($accion === 'validar_disponibilidad_agendamiento' || $accion === 'validar_bloqueo_horario') {
            $esBloqueo = $accion === 'validar_bloqueo_horario';
            $modeloOperacion = $esBloqueo ? $modeloBloqueoHorario : $modeloAgendamiento;
            $codigoNoDisponible = $esBloqueo ? 'bloqueo_no_disponible' : 'cita_no_disponible';

            $disponibilidad = $modeloOperacion->comprobarDisponibilidadAgenda(
                $datosSolicitud,
                $modeloAgendamiento,
                $modeloBloqueoHorario,
                $esBloqueo
            );

            $responderJson(['ok' => true] + $disponibilidad);
        }


        /* ============================================= */
        /* REGISTRAR O MODIFICAR AGENDAMIENTO */
        /* ============================================= */

        if ($accion === 'registrar' || $accion === 'modificar') {
            $modeloOperacion = $modeloAgendamiento;
            $esModificacion = $accion === 'modificar';

            if ($esModificacion) {
                $modeloAgendamiento->comprobarGestionAgenda($datosSolicitud['id_agendamiento'] ?? null);
            }

            $datosDisponibilidad = $datosSolicitud;
            $datosDisponibilidad['id_agendamiento'] = $esModificacion
                ? ($datosSolicitud['id_agendamiento'] ?? null)
                : null;

            $disponibilidad = $modeloAgendamiento->comprobarDisponibilidadAgenda(
                $datosDisponibilidad,
                $modeloAgendamiento,
                $modeloBloqueoHorario
            );

            $responderDisponibilidadRechazada($disponibilidad, false);

            $modeloAgendamiento->set_idAgendamiento($datosSolicitud['id_agendamiento'] ?? null);
            $modeloAgendamiento->set_idCliente($datosSolicitud['id_cliente'] ?? null);
            $modeloAgendamiento->set_idUsuarioRegistra($idUsuarioRegistra);
            $modeloAgendamiento->set_idUsuarioAsignado($datosSolicitud['id_usuario_asignado'] ?? null);
            $modeloAgendamiento->set_idEstadoAgendamiento($datosSolicitud['id_estado_agendamiento'] ?? null);
            $modeloAgendamiento->set_idTipoAgendamiento($datosSolicitud['id_tipo_agendamiento'] ?? null);
            $modeloAgendamiento->set_fecha($datosSolicitud['fecha'] ?? null);
            $modeloAgendamiento->set_hora($datosSolicitud['hora'] ?? null);
            $modeloAgendamiento->set_duracionMinutos($datosSolicitud['duracion_minutos'] ?? null);
            $modeloAgendamiento->set_observacionInicial($datosSolicitud['observacion_inicial'] ?? '');
            $modeloAgendamiento->set_montoTotal($datosSolicitud['monto_total'] ?? null);
            $modeloAgendamiento->set_servicios($datosSolicitud['servicios'] ?? []);

            /* El modelo conserva las transacciones y la protección de la atención */
            $correcto = $esModificacion
                ? $modeloAgendamiento->modificar()
                : $modeloAgendamiento->registrar();

            if (!$correcto) throw new RuntimeException('El modelo no pudo guardar el agendamiento.');

            $finalizarOperacion(
                true,
                $esModificacion ? 'agendamiento_modificado' : 'agendamiento_registrado',
                ($esModificacion
                    ? 'El agendamiento fue modificado correctamente.'
                    : 'El agendamiento fue registrado correctamente.') .
                    $modeloAgendamiento->obtenerAdvertenciaReserva(),
                200,
                [],
                $modeloAgendamiento->obtenerAdvertenciaReserva() === '' ? 'success' : 'warning'
            );
        }


        /* ============================================= */
        /* CANCELAR AGENDAMIENTO */
        /* ============================================= */

        if ($accion === 'cancelar') {
            $modeloOperacion = $modeloAgendamiento;
            $idAgendamiento = $datosSolicitud['id_agendamiento'] ?? null;

            $modeloAgendamiento->comprobarGestionAgenda($idAgendamiento);
            $modeloAgendamiento->set_idAgendamiento($idAgendamiento);

            if (!$modeloAgendamiento->eliminar()) {
                throw new RuntimeException('El modelo no pudo cancelar el agendamiento.');
            }

            $finalizarOperacion(
                true,
                'agendamiento_cancelado',
                'El agendamiento fue cancelado correctamente.'
            );
        }


        /* ============================================= */
        /* REGISTRAR O MODIFICAR BLOQUEO */
        /* ============================================= */

        if ($accion === 'registrar_bloqueo' || $accion === 'modificar_bloqueo') {
            $modeloOperacion = $modeloBloqueoHorario;
            $codigoNoDisponible = 'bloqueo_no_disponible';
            $esModificacion = $accion === 'modificar_bloqueo';

            if ($esModificacion) {
                $modeloBloqueoHorario->comprobarGestionAgenda($datosSolicitud['id_bloqueo'] ?? null, true);
            }

            $datosDisponibilidad = $datosSolicitud;
            $datosDisponibilidad['id_bloqueo'] = $esModificacion
                ? ($datosSolicitud['id_bloqueo'] ?? null)
                : null;

            $disponibilidad = $modeloBloqueoHorario->comprobarDisponibilidadAgenda(
                $datosDisponibilidad,
                $modeloAgendamiento,
                $modeloBloqueoHorario,
                true
            );

            $responderDisponibilidadRechazada($disponibilidad, true);

            $modeloBloqueoHorario->set_idBloqueo($datosSolicitud['id_bloqueo'] ?? null);
            $modeloBloqueoHorario->set_idUsuarioEspecialista($datosSolicitud['id_usuario_especialista'] ?? null);
            $modeloBloqueoHorario->set_idUsuarioRegistra($idUsuarioRegistra);
            $modeloBloqueoHorario->set_fecha($datosSolicitud['fecha'] ?? null);
            $modeloBloqueoHorario->set_horaInicio($datosSolicitud['hora_inicio'] ?? null);
            $modeloBloqueoHorario->set_horaFin($datosSolicitud['hora_fin'] ?? null);
            $modeloBloqueoHorario->set_motivo($datosSolicitud['motivo'] ?? '');

            $correcto = $esModificacion
                ? $modeloBloqueoHorario->modificar()
                : $modeloBloqueoHorario->registrar();

            if (!$correcto) throw new RuntimeException('El modelo no pudo guardar el bloqueo.');

            $finalizarOperacion(
                true,
                $esModificacion ? 'bloqueo_modificado' : 'bloqueo_registrado',
                $esModificacion
                    ? 'El bloqueo de horario fue modificado correctamente.'
                    : 'El bloqueo de horario fue registrado correctamente.'
            );
        }


        /* ============================================= */
        /* DESBLOQUEAR HORARIO */
        /* ============================================= */

        if ($accion === 'desbloquear_horario') {
            $modeloOperacion = $modeloBloqueoHorario;
            $codigoNoDisponible = 'bloqueo_no_disponible';
            $idBloqueo = $datosSolicitud['id_bloqueo'] ?? null;

            $modeloBloqueoHorario->comprobarGestionAgenda($idBloqueo, true);
            $modeloBloqueoHorario->set_idBloqueo($idBloqueo);

            if (!$modeloBloqueoHorario->eliminar()) {
                throw new RuntimeException('El modelo no pudo desbloquear el horario.');
            }

            $finalizarOperacion(
                true,
                'horario_desbloqueado',
                'El horario fue desbloqueado correctamente.'
            );
        }

        $finalizarOperacion(
            false,
            'solicitud_invalida',
            'La operación solicitada no es válida.',
            400
        );
    }


    if ($metodo !== 'GET') {
        header('Allow: GET, POST');

        $responderJson([
            'ok' => false,
            'mensaje' => 'Método no permitido.'
        ], 405);
    }


    /* ================================================= */
    /* DATOS PARA LA LISTA Y EL CALENDARIO */
    /* ================================================= */

    /* Actualizar las citas demoradas del día antes de consultar los eventos */
    $modeloAgendamiento->actualizarDemorasDelDia();

    $agendamientos = $modeloAgendamiento->consultar();
    $bloqueosHorario = $modeloBloqueoHorario->consultar();

    $agendamientosCalendario = [];

    /* Excluir las citas canceladas y realizadas del calendario y la lista derecha */
    foreach ($agendamientos as $agendamiento) {
        if (
            in_array(
                strtoupper(trim((string) ($agendamiento['estado'] ?? ''))),
                ['CANCELADA', 'REALIZADA'],
                true
            )
        ) {
            continue;
        }

        $fecha = trim($agendamiento['fecha'] ?? '');
        $hora = trim($agendamiento['hora'] ?? '');
        $fechaInicio = $obtenerInicio($fecha, $hora);

        if ($fechaInicio === null) continue;

        $duracionMinutos = max(1, (int) ($agendamiento['duracion_minutos'] ?? 0));
        $fechaFin = $fechaInicio->modify('+' . $duracionMinutos . ' minutes');

        $serviciosIds = !empty($agendamiento['servicios_ids'])
            ? array_map('intval', explode(',', $agendamiento['servicios_ids']))
            : [];

        $nombresServicios = !empty($agendamiento['servicios'])
            ? explode('||', $agendamiento['servicios'])
            : [];


        /* Compartir la protección con las acciones de la lista y del calendario */
        $agendamientosCalendario[] = [
            'id' => (int) $agendamiento['id_agendamiento'],
            'title' => $agendamiento['cliente'] ?? 'Agendamiento',
            'start' => $fechaInicio->format('Y-m-d\TH:i:s'),
            'end' => $fechaFin->format('Y-m-d\TH:i:s'),

            'extendedProps' => [
                'es_bloqueo' => false,
                'gestion_permitida' => $agendamiento['gestion_permitida'],
                'tiene_registro_servicio' => $agendamiento['tiene_registro_servicio'],
                'motivo_bloqueo' => $agendamiento['motivo_bloqueo'],
                'id_cliente' => $agendamiento['id_cliente'],
                'id_usuario_asignado' => $agendamiento['id_usuario_asignado'],
                'id_estado_agendamiento' => $agendamiento['id_estado_agendamiento'],
                'id_tipo_agendamiento' => $agendamiento['id_tipo_agendamiento'],
                'cliente' => $agendamiento['cliente'],
                'cedula_cliente' => $agendamiento['cedula_cliente'],
                'telefono' => $agendamiento['telefono_cliente'],
                'especialista' => $agendamiento['especialista'],
                'usuario_registra' => $agendamiento['usuario_registra'],
                'estado' => $agendamiento['estado'],
                'tipo_agendamiento' => $agendamiento['tipo_agendamiento'],
                'observacion_inicial' => $agendamiento['observacion_inicial'],
                'duracion_minutos' => $duracionMinutos,
                'monto_total' => (float) $agendamiento['monto_total'],
                'servicios_ids' => $serviciosIds,
                'servicios' => $nombresServicios
            ]
        ];
    }


    /* Representar los bloqueos como eventos independientes */
    foreach ($bloqueosHorario as $bloqueo) {
        $fecha = trim($bloqueo['fecha'] ?? '');
        $horaInicio = trim($bloqueo['hora_inicio'] ?? '');
        $horaFin = trim($bloqueo['hora_fin'] ?? '');

        if ($fecha === '' || $horaInicio === '' || $horaFin === '') continue;

        $agendamientosCalendario[] = [
            'id' => 'bloqueo-' . (int) $bloqueo['id_bloqueo'],
            'title' => 'Horario bloqueado',
            'start' => $fecha . 'T' . $horaInicio,
            'end' => $fecha . 'T' . $horaFin,

            'extendedProps' => [
                'es_bloqueo' => true,
                'id_bloqueo' => (int) $bloqueo['id_bloqueo'],
                'id_usuario_especialista' => (int) $bloqueo['id_usuario_especialista'],
                'especialista' => $bloqueo['especialista'],
                'usuario_registra' => $bloqueo['usuario_registra'],
                'motivo' => $bloqueo['motivo'],
                'estado_bloqueo' => $bloqueo['estado_bloqueo']
            ]
        ];
    }


    if ($accion === 'consultar_calendario') {
        $responderJson([
            'ok' => true,
            'eventos' => $agendamientosCalendario
        ]);
    }


    /* ================================================= */
    /* DATOS PARA LOS FORMULARIOS */
    /* ================================================= */

    $modeloCliente = new ClientesM();
    $modeloSeguridad = new SeguridadM();
    $modeloServicio = new ServicioM();
    $modeloEstadoAgendamiento = new EstadoAgendamientoM();
    $modeloTipoAgendamiento = new TipoAgendamientoM();

    $clientes = $modeloCliente->consultar();
    $servicios = $modeloServicio->consultar();
    $tiposAgendamientos = $modeloTipoAgendamiento->consultar();
    $usuariosActivos = $modeloSeguridad->consultar();


    /* Ofrecer los estados previos al inicio de la atención */
    $estadosAgendamientos = array_values(
        array_filter(
            $modeloEstadoAgendamiento->consultar(),
            static function (array $estado): bool {
                return in_array(
                    strtoupper(trim((string) ($estado['nombre_estado'] ?? ''))),
                    ['AGENDADA', 'CONFIRMADA', 'EN ESPERA', 'DEMORADA'],
                    true
                );
            }
        )
    );


    /* Conservar la lista de especialistas utilizada por los formularios */
    $usuarios = [];

    foreach ($usuariosActivos as $usuario) {
        $nombreRol = strtoupper(trim($usuario['nombre_rol'] ?? ''));

        if ($nombreRol === 'ESPECIALISTA EN MANICURA') {
            $usuarios[] = $usuario;
        }
    }

} catch (DomainException $error) {
    $estadoHttp = $error->getCode() === 404 ? 404 : 409;

    $finalizarOperacion(
        false,
        $estadoHttp === 404 ? $codigoNoDisponible : 'agendamiento_no_gestionable',
        $error->getMessage(),
        $estadoHttp
    );

} catch (InvalidArgumentException $error) {
    $finalizarOperacion(
        false,
        'datos_invalidos',
        $error->getMessage(),
        422,
        $modeloOperacion ? $modeloOperacion->obtenerErroresValidacion() : []
    );

} catch (Throwable $error) {

    /* ================================================= */
    /* ERRORES DE LA OPERACIÓN */
    /* ================================================= */

    error_log('Agendamiento: ' . $error->getMessage());

    if ($metodo === 'POST') {
        $finalizarOperacion(
            false,
            'operacion_error',
            'No fue posible completar la operación. Intente nuevamente.',
            500
        );
    }

    if ($requiereRespuestaJson) {
        $responderJson([
            'ok' => false,
            'mensaje' => 'No fue posible actualizar el calendario. Intente nuevamente.'
        ], 500);
    }

    /* Evitar presentar un calendario vacío cuando falló la consulta */
    http_response_code(500);

    echo 'No fue posible abrir el módulo de agendamiento. Intente nuevamente.';

    return;
}


/* ===================================================== */
/* CARGAR LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/AgendamientoV.php';