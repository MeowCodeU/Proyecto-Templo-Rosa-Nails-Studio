<?php

use App\Controller\Components\Session;
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

/* Identificar el método y el formato de la solicitud */
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$esPeticionAjax = strtolower(
    (string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')
) === 'xmlhttprequest';

$tipoContenido = (string) ($_SERVER['CONTENT_TYPE'] ?? '');

$esCuerpoJson = strtolower(
    trim(explode(';', $tipoContenido)[0])
) === 'application/json';

$datosSolicitud = $_POST;


/* ===================================================== */
/* RESPUESTAS Y DATOS DE LA SOLICITUD */
/* ===================================================== */

/* Enviar los datos al navegador y finalizar la solicitud */
$responderJson = static function (array $datos, int $codigo = 200): void {
    http_response_code($codigo);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );

    exit;
};

/* Recibir los datos enviados como JSON */
if ($metodo === 'POST' && $esCuerpoJson) {
    try {
        $datosSolicitud = json_decode(
            (string) file_get_contents('php://input'),
            true,
            32,
            JSON_THROW_ON_ERROR
        );

        if (
            !is_array($datosSolicitud) ||
            !is_string($datosSolicitud['accion'] ?? null)
        ) {
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

/* Identificar la operación recibida */
$accion = $metodo === 'POST'
    ? ($datosSolicitud['accion'] ?? '')
    : ($_GET['accion'] ?? '');

$accion = is_string($accion) ? $accion : '';

$accionesDisponibilidad = [
    'validar_disponibilidad_agendamiento',
    'validar_bloqueo_horario'
];

$requiereRespuestaJson = $esPeticionAjax ||
    ($metodo === 'POST' && $esCuerpoJson) ||
    ($metodo === 'GET' && $accion === 'consultar_calendario') ||
    ($metodo === 'POST' && in_array($accion, $accionesDisponibilidad, true));

/* Responder a las peticiones o redirigir los formularios normales */
$finalizarOperacion = static function (
    bool $correcto,
    string $codigo,
    string $mensaje,
    int $estadoHttp = 200
) use ($requiereRespuestaJson, $responderJson): void {
    if ($requiereRespuestaJson) {
        $responderJson([
            'ok' => $correcto,
            'codigo' => $codigo,
            'mensaje' => $mensaje
        ], $estadoHttp);
    }

    $_SESSION['mensaje_agendamiento'] = [
        'ok' => $correcto,
        'codigo' => $codigo,
        'mensaje' => $mensaje
    ];

    header(
        'Location: Index.php?url=agendamiento&mensaje=' .
        urlencode($codigo)
    );

    exit;
};

/* Leer campos de texto sin aceptar arreglos en su lugar */
$leerTexto = static function (string $campo) use ($datosSolicitud): string {
    $valor = $datosSolicitud[$campo] ?? '';

    return is_string($valor) ? trim($valor) : '';
};

/* Obtener identificadores y cantidades enteras */
$leerEntero = static function (string $campo) use ($datosSolicitud): int {
    $valor = $datosSolicitud[$campo] ?? '';

    if (!is_string($valor) && !is_int($valor)) {
        return 0;
    }

    $numero = filter_var($valor, FILTER_VALIDATE_INT);

    return $numero === false ? 0 : $numero;
};

/* Comprobar fechas y horas antes de calcular la disponibilidad */
$obtenerInicio = static function (
    string $fecha,
    string $hora
): ?DateTimeImmutable {
    foreach (['Y-m-d H:i', 'Y-m-d H:i:s'] as $formato) {
        $inicio = DateTimeImmutable::createFromFormat(
            '!' . $formato,
            $fecha . ' ' . $hora
        );

        if (
            $inicio !== false &&
            $inicio->format($formato) === $fecha . ' ' . $hora
        ) {
            return $inicio;
        }
    }

    return null;
};

/* Localizar el registro que se desea modificar o retirar del calendario */
$buscarRegistro = static function (
    array $registros,
    string $campo,
    int $id
): ?array {
    foreach ($registros as $registro) {
        if ((int) ($registro[$campo] ?? 0) === $id) {
            return $registro;
        }
    }

    return null;
};


try {
    /* ================================================= */
    /* MODELOS Y USUARIO AUTENTICADO */
    /* ================================================= */

    $modeloAgendamiento = new AgendamientoM();
    $modeloBloqueoHorario = new BloqueoHorarioM();

    $usuarioSesion = Session::obtenerUsuario();

    $idUsuarioRegistra = (int) (
        $usuarioSesion['id_usuario'] ?? 0
    );


    /* ================================================= */
    /* DISPONIBILIDAD DE CITAS */
    /* ================================================= */

    /* Usar la misma comprobación al consultar y al guardar */
    $comprobarDisponibilidadCita = static function (
        int $idEspecialista,
        string $fecha,
        string $hora,
        int $duracion,
        ?int $idExcluir = null
    ) use (
        $modeloAgendamiento,
        $modeloBloqueoHorario,
        $obtenerInicio
    ): array {
        $inicio = $obtenerInicio($fecha, $hora);

        if (
            $idEspecialista <= 0 ||
            $inicio === null ||
            $duracion <= 0
        ) {
            return [
                'disponible' => false,
                'tipo' => 'datos_invalidos',
                'mensaje' =>
                    'Seleccione la especialista, una fecha y hora válidas y al menos un servicio.'
            ];
        }

        $horaFin = $inicio
            ->modify('+' . $duracion . ' minutes')
            ->format('H:i:s');

        if ($modeloBloqueoHorario->existeBloqueoParaAgendamiento(
            $idEspecialista,
            $fecha,
            $hora,
            $horaFin
        )) {
            return [
                'disponible' => false,
                'tipo' => 'bloqueo',
                'mensaje' =>
                    'La especialista posee un bloqueo dentro del horario seleccionado.'
            ];
        }

        if ($modeloAgendamiento->existeConflictoConAgendamiento(
            $idEspecialista,
            $fecha,
            $hora,
            $horaFin,
            $idExcluir
        )) {
            return [
                'disponible' => false,
                'tipo' => 'cita',
                'mensaje' =>
                    'La especialista ya tiene otra cita que coincide con el horario seleccionado. Elija otro horario u otra especialista.'
            ];
        }

        return [
            'disponible' => true,
            'tipo' => 'disponible',
            'mensaje' =>
                'La especialista se encuentra disponible en el horario seleccionado.'
        ];
    };


    /* ================================================= */
    /* DISPONIBILIDAD DE BLOQUEOS */
    /* ================================================= */

    /* Excluir el propio bloqueo cuando se comprueba una edición */
    $comprobarDisponibilidadBloqueo = static function (
        int $idEspecialista,
        string $fecha,
        string $horaInicio,
        string $horaFin,
        ?int $idExcluir = null
    ) use ($modeloBloqueoHorario, $obtenerInicio): array {
        $inicio = $obtenerInicio($fecha, $horaInicio);
        $fin = $obtenerInicio($fecha, $horaFin);

        if (
            $idEspecialista <= 0 ||
            $inicio === null ||
            $fin === null ||
            $fin <= $inicio
        ) {
            return [
                'disponible' => false,
                'tipo' => 'datos_invalidos',
                'mensaje' =>
                    'Verifique la especialista, la fecha y las horas. La hora final debe ser posterior a la inicial.'
            ];
        }

        $modeloBloqueoHorario->set_idUsuarioEspecialista($idEspecialista);
        $modeloBloqueoHorario->set_fecha($fecha);
        $modeloBloqueoHorario->set_horaInicio($horaInicio);
        $modeloBloqueoHorario->set_horaFin($horaFin);

        if ($modeloBloqueoHorario->existeConflictoConBloqueo($idExcluir)) {
            return [
                'disponible' => false,
                'tipo' => 'bloqueo',
                'mensaje' =>
                    'La especialista ya posee otro bloqueo dentro del horario seleccionado.'
            ];
        }

        if ($modeloBloqueoHorario->existeConflictoConAgendamiento()) {
            return [
                'disponible' => false,
                'tipo' => 'cita',
                'mensaje' =>
                    'No es posible bloquear este período porque la especialista posee una cita dentro del horario seleccionado.'
            ];
        }

        return [
            'disponible' => true,
            'tipo' => 'disponible',
            'mensaje' =>
                'El horario se encuentra disponible para realizar el bloqueo.'
        ];
    };


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

        /* Consultar disponibilidad de una cita */
        if ($accion === 'validar_disponibilidad_agendamiento') {
            $idExcluir = $leerEntero('id_agendamiento');

            $disponibilidad = $comprobarDisponibilidadCita(
                $leerEntero('id_usuario_asignado'),
                $leerTexto('fecha'),
                $leerTexto('hora'),
                $leerEntero('duracion_minutos'),
                $idExcluir > 0 ? $idExcluir : null
            );

            $responderJson(['ok' => true] + $disponibilidad);
        }

        /* Consultar disponibilidad de un bloqueo */
        if ($accion === 'validar_bloqueo_horario') {
            $idExcluir = $leerEntero('id_bloqueo');

            $disponibilidad = $comprobarDisponibilidadBloqueo(
                $leerEntero('id_usuario_especialista'),
                $leerTexto('fecha'),
                $leerTexto('hora_inicio'),
                $leerTexto('hora_fin'),
                $idExcluir > 0 ? $idExcluir : null
            );

            $responderJson(['ok' => true] + $disponibilidad);
        }


        /* ============================================= */
        /* DATOS Y DISPONIBILIDAD DEL AGENDAMIENTO */
        /* ============================================= */

        if ($accion === 'registrar' || $accion === 'modificar') {
            $idAgendamiento = $leerEntero('id_agendamiento');
            $idCliente = $leerEntero('id_cliente');
            $idEspecialista = $leerEntero('id_usuario_asignado');
            $idEstado = $leerEntero('id_estado_agendamiento');
            $idTipo = $leerEntero('id_tipo_agendamiento');
            $fecha = $leerTexto('fecha');
            $hora = $leerTexto('hora');
            $duracion = $leerEntero('duracion_minutos');
            $observacion = $leerTexto('observacion_inicial');
            $montoRecibido = $leerTexto('monto_total');
            $serviciosRecibidos = $datosSolicitud['servicios'] ?? [];

            if (
                $idCliente <= 0 ||
                $idEspecialista <= 0 ||
                $idEstado <= 0 ||
                $idTipo <= 0 ||
                $duracion <= 0 ||
                !is_numeric($montoRecibido) ||
                !is_finite((float) $montoRecibido) ||
                (float) $montoRecibido < 0 ||
                !is_array($serviciosRecibidos) ||
                $serviciosRecibidos === []
            ) {
                $finalizarOperacion(
                    false,
                    'datos_incompletos',
                    'Complete correctamente los datos obligatorios y seleccione al menos un servicio.',
                    422
                );
            }

            /* Comprobar los identificadores de los servicios seleccionados */
            $serviciosSeleccionados = [];

            foreach ($serviciosRecibidos as $idServicio) {
                if (!is_string($idServicio) && !is_int($idServicio)) {
                    $finalizarOperacion(
                        false,
                        'servicios_invalidos',
                        'La selección de servicios no es válida.',
                        422
                    );
                }

                $idServicio = filter_var(
                    $idServicio,
                    FILTER_VALIDATE_INT
                );

                if ($idServicio === false || $idServicio <= 0) {
                    $finalizarOperacion(
                        false,
                        'servicios_invalidos',
                        'La selección de servicios no es válida.',
                        422
                    );
                }

                $serviciosSeleccionados[] = $idServicio;
            }

            $serviciosSeleccionados = array_values(
                array_unique($serviciosSeleccionados)
            );

            $montoTotal = (float) $montoRecibido;

            /* Comprobar que la cita que se edita todavía exista */
            if ($accion === 'modificar') {
                $registro = $idAgendamiento > 0
                    ? $buscarRegistro(
                        $modeloAgendamiento->consultar(),
                        'id_agendamiento',
                        $idAgendamiento
                    )
                    : null;

                if ($registro === null) {
                    $finalizarOperacion(
                        false,
                        'cita_no_disponible',
                        'El agendamiento que intenta modificar no se encuentra disponible.',
                        404
                    );
                }
            }

            /* Repetir la comprobación inmediatamente antes de guardar */
            $disponibilidad = $comprobarDisponibilidadCita(
                $idEspecialista,
                $fecha,
                $hora,
                $duracion,
                $accion === 'modificar' ? $idAgendamiento : null
            );

            if (!$disponibilidad['disponible']) {
                $codigos = [
                    'bloqueo' => 'horario_bloqueado',
                    'cita' => 'horario_ocupado',
                    'datos_invalidos' => 'datos_incompletos'
                ];

                $finalizarOperacion(
                    false,
                    $codigos[$disponibilidad['tipo']],
                    $disponibilidad['mensaje'],
                    $disponibilidad['tipo'] === 'datos_invalidos'
                        ? 422
                        : 409
                );
            }
        }


        /* ============================================= */
        /* REGISTRAR AGENDAMIENTO */
        /* ============================================= */

        if ($accion === 'registrar') {
            $modeloAgendamiento->set_idCliente($idCliente);
            $modeloAgendamiento->set_idUsuarioRegistra($idUsuarioRegistra);
            $modeloAgendamiento->set_idUsuarioAsignado($idEspecialista);
            $modeloAgendamiento->set_idEstadoAgendamiento($idEstado);
            $modeloAgendamiento->set_idTipoAgendamiento($idTipo);
            $modeloAgendamiento->set_fecha($fecha);
            $modeloAgendamiento->set_hora($hora);
            $modeloAgendamiento->set_duracionMinutos($duracion);
            $modeloAgendamiento->set_observacionInicial($observacion);
            $modeloAgendamiento->set_montoTotal($montoTotal);
            $modeloAgendamiento->set_servicios($serviciosSeleccionados);

            if (!$modeloAgendamiento->registrar()) {
                throw new RuntimeException(
                    'El modelo no pudo registrar el agendamiento.'
                );
            }

            $finalizarOperacion(
                true,
                'agendamiento_registrado',
                'El agendamiento fue registrado correctamente.'
            );
        }


        /* ============================================= */
        /* MODIFICAR AGENDAMIENTO */
        /* ============================================= */

        if ($accion === 'modificar') {
            $modeloAgendamiento->set_idAgendamiento($idAgendamiento);
            $modeloAgendamiento->set_idCliente($idCliente);
            $modeloAgendamiento->set_idUsuarioAsignado($idEspecialista);
            $modeloAgendamiento->set_idEstadoAgendamiento($idEstado);
            $modeloAgendamiento->set_idTipoAgendamiento($idTipo);
            $modeloAgendamiento->set_fecha($fecha);
            $modeloAgendamiento->set_hora($hora);
            $modeloAgendamiento->set_duracionMinutos($duracion);
            $modeloAgendamiento->set_observacionInicial($observacion);
            $modeloAgendamiento->set_montoTotal($montoTotal);
            $modeloAgendamiento->set_servicios($serviciosSeleccionados);

            if (!$modeloAgendamiento->modificar()) {
                throw new RuntimeException(
                    'El modelo no pudo modificar el agendamiento.'
                );
            }

            $finalizarOperacion(
                true,
                'agendamiento_modificado',
                'El agendamiento fue modificado correctamente.'
            );
        }


        /* ============================================= */
        /* CANCELAR AGENDAMIENTO */
        /* ============================================= */

        if ($accion === 'cancelar') {
            $idAgendamiento = $leerEntero('id_agendamiento');

            $registro = $idAgendamiento > 0
                ? $buscarRegistro(
                    $modeloAgendamiento->consultar(),
                    'id_agendamiento',
                    $idAgendamiento
                )
                : null;

            if ($registro === null) {
                $finalizarOperacion(
                    false,
                    'cita_no_disponible',
                    'El agendamiento que intenta cancelar no se encuentra disponible.',
                    404
                );
            }

            $modeloAgendamiento->set_idAgendamiento($idAgendamiento);

            if (!$modeloAgendamiento->eliminar()) {
                throw new RuntimeException(
                    'El modelo no pudo cancelar el agendamiento.'
                );
            }

            /* Confirmar que la cita haya cambiado realmente a CANCELADA */
            $registro = $buscarRegistro(
                $modeloAgendamiento->consultar(),
                'id_agendamiento',
                $idAgendamiento
            );

            if (
                $registro === null ||
                strtoupper(trim($registro['estado'] ?? '')) !== 'CANCELADA'
            ) {
                throw new RuntimeException(
                    'No se pudo confirmar el estado CANCELADA del agendamiento.'
                );
            }

            $finalizarOperacion(
                true,
                'agendamiento_cancelado',
                'El agendamiento fue cancelado correctamente.'
            );
        }


        /* ============================================= */
        /* DATOS Y DISPONIBILIDAD DEL BLOQUEO */
        /* ============================================= */

        if (
            $accion === 'registrar_bloqueo' ||
            $accion === 'modificar_bloqueo'
        ) {
            $idBloqueo = $leerEntero('id_bloqueo');
            $idEspecialista = $leerEntero('id_usuario_especialista');
            $fecha = $leerTexto('fecha');
            $horaInicio = $leerTexto('hora_inicio');
            $horaFin = $leerTexto('hora_fin');
            $motivo = $leerTexto('motivo');

            /* Comprobar que el bloqueo todavía se encuentre activo */
            if ($accion === 'modificar_bloqueo') {
                $registro = $idBloqueo > 0
                    ? $buscarRegistro(
                        $modeloBloqueoHorario->consultar(),
                        'id_bloqueo',
                        $idBloqueo
                    )
                    : null;

                if ($registro === null) {
                    $finalizarOperacion(
                        false,
                        'bloqueo_no_disponible',
                        'El bloqueo que intenta modificar ya no se encuentra activo.',
                        404
                    );
                }
            }

            $disponibilidad = $comprobarDisponibilidadBloqueo(
                $idEspecialista,
                $fecha,
                $horaInicio,
                $horaFin,
                $accion === 'modificar_bloqueo' ? $idBloqueo : null
            );

            if (!$disponibilidad['disponible']) {
                $codigos = [
                    'bloqueo' => 'bloqueo_conflicto_bloqueo',
                    'cita' => 'bloqueo_conflicto_cita',
                    'datos_invalidos' => 'bloqueo_datos_invalidos'
                ];

                $finalizarOperacion(
                    false,
                    $codigos[$disponibilidad['tipo']],
                    $disponibilidad['mensaje'],
                    $disponibilidad['tipo'] === 'datos_invalidos'
                        ? 422
                        : 409
                );
            }
        }


        /* ============================================= */
        /* REGISTRAR BLOQUEO DE HORARIO */
        /* ============================================= */

        if ($accion === 'registrar_bloqueo') {
            $modeloBloqueoHorario->set_idUsuarioEspecialista($idEspecialista);
            $modeloBloqueoHorario->set_idUsuarioRegistra($idUsuarioRegistra);
            $modeloBloqueoHorario->set_fecha($fecha);
            $modeloBloqueoHorario->set_horaInicio($horaInicio);
            $modeloBloqueoHorario->set_horaFin($horaFin);
            $modeloBloqueoHorario->set_motivo($motivo);

            if (!$modeloBloqueoHorario->registrar()) {
                throw new RuntimeException(
                    'El modelo no pudo registrar el bloqueo.'
                );
            }

            $finalizarOperacion(
                true,
                'bloqueo_registrado',
                'El bloqueo de horario fue registrado correctamente.'
            );
        }


        /* ============================================= */
        /* MODIFICAR BLOQUEO DE HORARIO */
        /* ============================================= */

        if ($accion === 'modificar_bloqueo') {
            $modeloBloqueoHorario->set_idBloqueo($idBloqueo);
            $modeloBloqueoHorario->set_idUsuarioEspecialista($idEspecialista);
            $modeloBloqueoHorario->set_fecha($fecha);
            $modeloBloqueoHorario->set_horaInicio($horaInicio);
            $modeloBloqueoHorario->set_horaFin($horaFin);
            $modeloBloqueoHorario->set_motivo($motivo);

            if (!$modeloBloqueoHorario->modificar()) {
                throw new RuntimeException(
                    'El modelo no pudo modificar el bloqueo.'
                );
            }

            $finalizarOperacion(
                true,
                'bloqueo_modificado',
                'El bloqueo de horario fue modificado correctamente.'
            );
        }


        /* ============================================= */
        /* DESBLOQUEAR HORARIO */
        /* ============================================= */

        if ($accion === 'desbloquear_horario') {
            $idBloqueo = $leerEntero('id_bloqueo');

            $registro = $idBloqueo > 0
                ? $buscarRegistro(
                    $modeloBloqueoHorario->consultar(),
                    'id_bloqueo',
                    $idBloqueo
                )
                : null;

            if ($registro === null) {
                $finalizarOperacion(
                    false,
                    'bloqueo_no_disponible',
                    'El bloqueo ya no se encuentra activo. Actualice el calendario.',
                    404
                );
            }

            $modeloBloqueoHorario->set_idBloqueo($idBloqueo);

            if (!$modeloBloqueoHorario->eliminar()) {
                throw new RuntimeException(
                    'El modelo no pudo desbloquear el horario.'
                );
            }

            $finalizarOperacion(
                true,
                'horario_desbloqueado',
                'El horario fue desbloqueado correctamente.'
            );
        }

        /* Rechazar operaciones que no pertenecen al módulo */
        $finalizarOperacion(
            false,
            'solicitud_invalida',
            'La operación solicitada no es válida.',
            400
        );
    }

    /* El módulo admite lectura y operaciones mediante GET y POST */
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

    $agendamientos = $modeloAgendamiento->consultar();
    $bloqueosHorario = $modeloBloqueoHorario->consultar();

    $agendamientosCalendario = [];

    /* Las citas canceladas no ocupan la lista ni el calendario */
    foreach ($agendamientos as $agendamiento) {
        if (
            strtoupper(trim($agendamiento['estado'] ?? '')) ===
            'CANCELADA'
        ) {
            continue;
        }

        $fecha = trim($agendamiento['fecha'] ?? '');
        $hora = trim($agendamiento['hora'] ?? '');

        $fechaInicio = $obtenerInicio($fecha, $hora);

        if ($fechaInicio === null) {
            continue;
        }

        $duracionMinutos = max(
            1,
            (int) ($agendamiento['duracion_minutos'] ?? 0)
        );

        $fechaFin = $fechaInicio->modify(
            '+' . $duracionMinutos . ' minutes'
        );

        /* Conservar los servicios necesarios para editar y mostrar detalles */
        $serviciosIds = !empty($agendamiento['servicios_ids'])
            ? array_map(
                'intval',
                explode(',', $agendamiento['servicios_ids'])
            )
            : [];

        $nombresServicios = !empty($agendamiento['servicios'])
            ? explode('||', $agendamiento['servicios'])
            : [];

        $agendamientosCalendario[] = [
            'id' => (int) $agendamiento['id_agendamiento'],
            'title' => $agendamiento['cliente'] ?? 'Agendamiento',
            'start' => $fechaInicio->format('Y-m-d\TH:i:s'),
            'end' => $fechaFin->format('Y-m-d\TH:i:s'),

            'extendedProps' => [
                'es_bloqueo' => false,
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

        if ($fecha === '' || $horaInicio === '' || $horaFin === '') {
            continue;
        }

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

    /* Entregar los eventos actualizados sin volver a cargar la vista */
    if ($accion === 'consultar_calendario') {
        $responderJson([
            'ok' => true,
            'eventos' => $agendamientosCalendario
        ]);
    }


    /* ================================================= */
    /* CONSULTAS PARA LLENAR LOS FORMULARIOS */
    /* ================================================= */

    $modeloCliente = new ClientesM();
    $modeloSeguridad = new SeguridadM();
    $modeloServicio = new ServicioM();
    $modeloEstadoAgendamiento = new EstadoAgendamientoM();
    $modeloTipoAgendamiento = new TipoAgendamientoM();

    $clientes = $modeloCliente->consultar();
    $servicios = $modeloServicio->consultar();
    $estadosAgendamientos = $modeloEstadoAgendamiento->consultar();
    $tiposAgendamientos = $modeloTipoAgendamiento->consultar();
    $usuariosActivos = $modeloSeguridad->consultar();

    $usuarios = [];

    /* Conservar la lista de especialistas que utiliza el formulario */
    foreach ($usuariosActivos as $usuario) {
        $nombreRol = strtoupper(
            trim($usuario['nombre_rol'] ?? '')
        );

        if ($nombreRol === 'ESPECIALISTA EN MANICURA') {
            $usuarios[] = $usuario;
        }
    }

} catch (Throwable $error) {
    /* Registrar el detalle técnico sin exponerlo al navegador */
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
            'mensaje' =>
                'No fue posible actualizar el calendario. Intente nuevamente.'
        ], 500);
    }

    /* No presentar un calendario vacío como si la consulta hubiera sido correcta */
    http_response_code(500);

    echo 'No fue posible abrir el módulo de agendamiento. Intente nuevamente.';

    return;
}


/* ===================================================== */
/* CARGAR LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/AgendamientoV.php';