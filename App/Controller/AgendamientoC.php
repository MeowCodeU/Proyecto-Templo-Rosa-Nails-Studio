<?php

use App\Controller\Components\Session;
use App\Model\AgendamientoM;
use App\Model\BloqueoHorarioM;
use App\Model\ClientesM;
use App\Model\SeguridadM;
use App\Model\ServicioM;
use App\Model\EstadoAgendamientoM;
use App\Model\TipoAgendamientoM;


/* Crear las instancias de los modelos */
$modeloAgendamiento = new AgendamientoM();
$modeloBloqueoHorario = new BloqueoHorarioM();
$modeloCliente = new ClientesM();
$modeloSeguridad = new SeguridadM();
$modeloServicio = new ServicioM();
$modeloEstadoAgendamiento = new EstadoAgendamientoM();
$modeloTipoAgendamiento = new TipoAgendamientoM();


/*Consultar el usuario autenticado y los usuarios activos.
De esta lista se obtiene:
  - El usuario autenticado que registra la operación.
 - Las especialistas en manicura que pueden ser asignadas.*/
$usuarioSesion = Session::obtenerUsuario();

$idUsuarioRegistra = isset(
    $usuarioSesion['id_usuario']
)
    ? (int) $usuarioSesion['id_usuario']
    : null;


$usuariosActivos = $modeloSeguridad->consultar();

$usuarios = [];


foreach ($usuariosActivos as $usuario) {

    $nombreRol = strtoupper(
        trim($usuario['nombre_rol'] ?? '')
    );


    /* Especialistas disponibles */
    if (
        $nombreRol ===
        'ESPECIALISTA EN MANICURA'
    ) {
        $usuarios[] = $usuario;
    }
}


/* Procesar las acciones enviadas por los formularios */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['accion'])
) {

    /* ================================================= */
    /* VALIDAR DISPONIBILIDAD PARA BLOQUEO (AJAX) */
    /* ================================================= */

    if (
        $_POST['accion'] ===
        'validar_bloqueo_horario'
    ) {
        header(
            'Content-Type: application/json; charset=utf-8'
        );

        $idUsuarioEspecialista =
            (int) (
                $_POST['id_usuario_especialista']
                ?? 0
            );

        $idBloqueo =
            (int) (
                $_POST['id_bloqueo']
                ?? 0
            );

        $fechaBloqueo = trim(
            $_POST['fecha']
            ?? ''
        );

        $horaInicio = trim(
            $_POST['hora_inicio']
            ?? ''
        );

        $horaFin = trim(
            $_POST['hora_fin']
            ?? ''
        );


        if (
            $idUsuarioEspecialista <= 0 ||
            $fechaBloqueo === '' ||
            $horaInicio === '' ||
            $horaFin === '' ||
            $horaInicio >= $horaFin
        ) {
            echo json_encode(
                [
                    'disponible' => false,
                    'tipo' => 'datos_invalidos',
                    'mensaje' =>
                        'Complete correctamente la especialista, la fecha y el horario.'
                ],
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        $modeloBloqueoHorario
            ->set_idUsuarioEspecialista(
                $idUsuarioEspecialista
            );

        $modeloBloqueoHorario->set_fecha(
            $fechaBloqueo
        );

        $modeloBloqueoHorario->set_horaInicio(
            $horaInicio
        );

        $modeloBloqueoHorario->set_horaFin(
            $horaFin
        );


        if (
            $modeloBloqueoHorario
                ->existeConflictoConBloqueo(
                    $idBloqueo > 0
                        ? $idBloqueo
                        : null
                )
        ) {
            echo json_encode(
                [
                    'disponible' => false,
                    'tipo' => 'bloqueo',
                    'mensaje' =>
                        'La especialista ya posee otro bloqueo dentro del horario seleccionado.'
                ],
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        if (
            $modeloBloqueoHorario
                ->existeConflictoConAgendamiento()
        ) {
            echo json_encode(
                [
                    'disponible' => false,
                    'tipo' => 'cita',
                    'mensaje' =>
                        'No es posible bloquear este período porque la especialista posee una cita dentro del horario seleccionado.'
                ],
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        echo json_encode(
            [
                'disponible' => true,
                'tipo' => 'disponible',
                'mensaje' =>
                    'El horario se encuentra disponible para realizar el bloqueo.'
            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    /* ================================================= */
    /* VALIDAR CITA CONTRA BLOQUEOS (AJAX) */
    /* ================================================= */

    if (
        $_POST['accion'] ===
        'validar_disponibilidad_agendamiento'
    ) {
        header(
            'Content-Type: application/json; charset=utf-8'
        );

        $idUsuarioAsignado =
            (int) (
                $_POST['id_usuario_asignado']
                ?? 0
            );

        $fechaAgendamiento = trim(
            $_POST['fecha']
            ?? ''
        );

        $horaAgendamiento = trim(
            $_POST['hora']
            ?? ''
        );

        $duracionAgendamiento =
            (int) (
                $_POST['duracion_minutos']
                ?? 0
            );


        if (
            $idUsuarioAsignado <= 0 ||
            $fechaAgendamiento === '' ||
            $horaAgendamiento === '' ||
            $duracionAgendamiento <= 0
        ) {
            echo json_encode(
                [
                    'disponible' => false,
                    'tipo' => 'datos_incompletos',
                    'mensaje' =>
                        'Seleccione la especialista, la fecha, la hora y al menos un servicio.'
                ],
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        try {
            $fechaFinAgendamiento = new DateTime(
                $fechaAgendamiento
                . ' '
                . $horaAgendamiento
            );

            $fechaFinAgendamiento->modify(
                '+'
                . $duracionAgendamiento
                . ' minutes'
            );

            $horaFinAgendamiento =
                $fechaFinAgendamiento->format(
                    'H:i:s'
                );

        } catch (\Exception $e) {
            echo json_encode(
                [
                    'disponible' => false,
                    'tipo' => 'datos_invalidos',
                    'mensaje' =>
                        'No fue posible interpretar el horario seleccionado.'
                ],
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        $existeBloqueo =
            $modeloBloqueoHorario
                ->existeBloqueoParaAgendamiento(
                    $idUsuarioAsignado,
                    $fechaAgendamiento,
                    $horaAgendamiento,
                    $horaFinAgendamiento
                );


        if ($existeBloqueo) {
            echo json_encode(
                [
                    'disponible' => false,
                    'tipo' => 'bloqueo',
                    'mensaje' =>
                        'La especialista no se encuentra disponible porque existe un bloqueo dentro del horario seleccionado.'
                ],
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        echo json_encode(
            [
                'disponible' => true,
                'tipo' => 'disponible',
                'mensaje' =>
                    'La especialista se encuentra disponible en el horario seleccionado.'
            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    /* ================================================= */
    /* REGISTRAR AGENDAMIENTO */
    /* ================================================= */

    if ($_POST['accion'] === 'registrar') {

        $idCliente = $_POST['id_cliente'] ?? '';


        /* Verificar que la cita no coincida con un horario bloqueado */
        $fechaAgendamiento = trim(
            $_POST['fecha'] ?? ''
        );

        $horaAgendamiento = trim(
            $_POST['hora'] ?? ''
        );

        $duracionAgendamiento =
            (int) ($_POST['duracion_minutos'] ?? 0);

        $horaFinAgendamiento = '';

        if (
            $fechaAgendamiento !== '' &&
            $horaAgendamiento !== '' &&
            $duracionAgendamiento > 0
        ) {
            try {
                $fechaFinAgendamiento = new DateTime(
                    $fechaAgendamiento
                    . ' '
                    . $horaAgendamiento
                );

                $fechaFinAgendamiento->modify(
                    '+'
                    . $duracionAgendamiento
                    . ' minutes'
                );

                $horaFinAgendamiento =
                    $fechaFinAgendamiento->format(
                        'H:i:s'
                    );

            } catch (\Exception $e) {
                $horaFinAgendamiento = '';
            }
        }


        if (
            $horaFinAgendamiento !== '' &&
            $modeloBloqueoHorario
                ->existeBloqueoParaAgendamiento(
                    (int) (
                        $_POST['id_usuario_asignado']
                        ?? 0
                    ),
                    $fechaAgendamiento,
                    $horaAgendamiento,
                    $horaFinAgendamiento
                )
        ) {
            header(
                'Location: Index.php?url=agendamiento'
                . '&mensaje=horario_bloqueado'
            );

            exit;
        }


        $modeloAgendamiento->set_idCliente(
            $idCliente !== ''
                ? (int) $idCliente
                : null
        );

        $modeloAgendamiento->set_idUsuarioRegistra(
            $idUsuarioRegistra
        );

        $modeloAgendamiento->set_idUsuarioAsignado(
            (int) (
                $_POST['id_usuario_asignado']
                ?? 0
            )
        );

        $modeloAgendamiento
            ->set_idEstadoAgendamiento(
                (int) (
                    $_POST['id_estado_agendamiento']
                    ?? 0
                )
            );

        $modeloAgendamiento
            ->set_idTipoAgendamiento(
                (int) (
                    $_POST['id_tipo_agendamiento']
                    ?? 0
                )
            );

        $modeloAgendamiento->set_fecha(
            trim($_POST['fecha'] ?? '')
        );

        $modeloAgendamiento->set_hora(
            trim($_POST['hora'] ?? '')
        );

        $modeloAgendamiento->set_duracionMinutos(
            (int) (
                $_POST['duracion_minutos']
                ?? 0
            )
        );

        $modeloAgendamiento->set_observacionInicial(
            trim(
                $_POST['observacion_inicial']
                ?? ''
            )
        );

        $modeloAgendamiento->set_montoTotal(
            (float) (
                $_POST['monto_total']
                ?? 0
            )
        );

        $modeloAgendamiento->set_servicios(
            $_POST['servicios'] ?? []
        );


        /* Registrar con el usuario autenticado */
        if ($idUsuarioRegistra !== null) {
            $modeloAgendamiento->registrar();
        }


        header(
            'Location: Index.php?url=agendamiento'
        );

        exit;
    }


    /* ================================================= */
    /* MODIFICAR AGENDAMIENTO */
    /* ================================================= */

    if ($_POST['accion'] === 'modificar') {

        $idCliente = $_POST['id_cliente'] ?? '';


        /* Verificar que la cita no se mueva a un horario bloqueado */
        $fechaAgendamiento = trim(
            $_POST['fecha'] ?? ''
        );

        $horaAgendamiento = trim(
            $_POST['hora'] ?? ''
        );

        $duracionAgendamiento =
            (int) ($_POST['duracion_minutos'] ?? 0);

        $horaFinAgendamiento = '';

        if (
            $fechaAgendamiento !== '' &&
            $horaAgendamiento !== '' &&
            $duracionAgendamiento > 0
        ) {
            try {
                $fechaFinAgendamiento = new DateTime(
                    $fechaAgendamiento
                    . ' '
                    . $horaAgendamiento
                );

                $fechaFinAgendamiento->modify(
                    '+'
                    . $duracionAgendamiento
                    . ' minutes'
                );

                $horaFinAgendamiento =
                    $fechaFinAgendamiento->format(
                        'H:i:s'
                    );

            } catch (\Exception $e) {
                $horaFinAgendamiento = '';
            }
        }


        if (
            $horaFinAgendamiento !== '' &&
            $modeloBloqueoHorario
                ->existeBloqueoParaAgendamiento(
                    (int) (
                        $_POST['id_usuario_asignado']
                        ?? 0
                    ),
                    $fechaAgendamiento,
                    $horaAgendamiento,
                    $horaFinAgendamiento
                )
        ) {
            header(
                'Location: Index.php?url=agendamiento'
                . '&mensaje=horario_bloqueado'
            );

            exit;
        }


        $modeloAgendamiento->set_idAgendamiento(
            (int) (
                $_POST['id_agendamiento']
                ?? 0
            )
        );

        $modeloAgendamiento->set_idCliente(
            $idCliente !== ''
                ? (int) $idCliente
                : null
        );

        $modeloAgendamiento->set_idUsuarioAsignado(
            (int) (
                $_POST['id_usuario_asignado']
                ?? 0
            )
        );

        $modeloAgendamiento
            ->set_idEstadoAgendamiento(
                (int) (
                    $_POST['id_estado_agendamiento']
                    ?? 0
                )
            );

        $modeloAgendamiento
            ->set_idTipoAgendamiento(
                (int) (
                    $_POST['id_tipo_agendamiento']
                    ?? 0
                )
            );

        $modeloAgendamiento->set_fecha(
            trim($_POST['fecha'] ?? '')
        );

        $modeloAgendamiento->set_hora(
            trim($_POST['hora'] ?? '')
        );

        $modeloAgendamiento->set_duracionMinutos(
            (int) (
                $_POST['duracion_minutos']
                ?? 0
            )
        );

        $modeloAgendamiento->set_observacionInicial(
            trim(
                $_POST['observacion_inicial']
                ?? ''
            )
        );

        $modeloAgendamiento->set_montoTotal(
            (float) (
                $_POST['monto_total']
                ?? 0
            )
        );

        $modeloAgendamiento->set_servicios(
            $_POST['servicios'] ?? []
        );


        $modeloAgendamiento->modificar();


        header(
            'Location: Index.php?url=agendamiento'
        );

        exit;
    }


    /* ================================================= */
    /* CANCELAR AGENDAMIENTO */
    /* ================================================= */

    if ($_POST['accion'] === 'cancelar') {

        $modeloAgendamiento->set_idAgendamiento(
            (int) (
                $_POST['id_agendamiento']
                ?? 0
            )
        );


        $modeloAgendamiento->eliminar();


        header(
            'Location: Index.php?url=agendamiento'
        );

        exit;
    }


    /* ================================================= */
    /* REGISTRAR BLOQUEO DE HORARIO */
    /* ================================================= */

    if ($_POST['accion'] === 'registrar_bloqueo') {

        $idUsuarioEspecialista =
            (int) (
                $_POST['id_usuario_especialista']
                ?? 0
            );

        $fechaBloqueo = trim(
            $_POST['fecha']
            ?? ''
        );

        $horaInicio = trim(
            $_POST['hora_inicio']
            ?? ''
        );

        $horaFin = trim(
            $_POST['hora_fin']
            ?? ''
        );

        $motivoBloqueo = trim(
            $_POST['motivo']
            ?? ''
        );


        $modeloBloqueoHorario
            ->set_idUsuarioEspecialista(
                $idUsuarioEspecialista
            );

        $modeloBloqueoHorario
            ->set_idUsuarioRegistra(
                $idUsuarioRegistra
            );

        $modeloBloqueoHorario->set_fecha(
            $fechaBloqueo
        );

        $modeloBloqueoHorario->set_horaInicio(
            $horaInicio
        );

        $modeloBloqueoHorario->set_horaFin(
            $horaFin
        );

        $modeloBloqueoHorario->set_motivo(
            $motivoBloqueo
        );


        if (
            $idUsuarioRegistra === null ||
            $idUsuarioEspecialista <= 0 ||
            $fechaBloqueo === '' ||
            $horaInicio === '' ||
            $horaFin === '' ||
            $horaInicio >= $horaFin
        ) {
            header(
                'Location: Index.php?url=agendamiento'
                . '&mensaje=bloqueo_datos_invalidos'
            );

            exit;
        }


        if (
            $modeloBloqueoHorario
                ->existeConflictoConBloqueo()
        ) {
            header(
                'Location: Index.php?url=agendamiento'
                . '&mensaje=bloqueo_conflicto_bloqueo'
            );

            exit;
        }


        if (
            $modeloBloqueoHorario
                ->existeConflictoConAgendamiento()
        ) {
            header(
                'Location: Index.php?url=agendamiento'
                . '&mensaje=bloqueo_conflicto_cita'
            );

            exit;
        }


        if ($modeloBloqueoHorario->registrar()) {
            header(
                'Location: Index.php?url=agendamiento'
                . '&mensaje=bloqueo_registrado'
            );

            exit;
        }


        header(
            'Location: Index.php?url=agendamiento'
            . '&mensaje=bloqueo_no_registrado'
        );

        exit;
    }


    /* ================================================= */
    /* MODIFICAR BLOQUEO DE HORARIO */
    /* ================================================= */

    if ($_POST['accion'] === 'modificar_bloqueo') {

        $idBloqueo =
            (int) (
                $_POST['id_bloqueo']
                ?? 0
            );

        $idUsuarioEspecialista =
            (int) (
                $_POST['id_usuario_especialista']
                ?? 0
            );

        $fechaBloqueo = trim(
            $_POST['fecha']
            ?? ''
        );

        $horaInicio = trim(
            $_POST['hora_inicio']
            ?? ''
        );

        $horaFin = trim(
            $_POST['hora_fin']
            ?? ''
        );

        $motivoBloqueo = trim(
            $_POST['motivo']
            ?? ''
        );


        $modeloBloqueoHorario->set_idBloqueo(
            $idBloqueo
        );

        $modeloBloqueoHorario
            ->set_idUsuarioEspecialista(
                $idUsuarioEspecialista
            );

        $modeloBloqueoHorario->set_fecha(
            $fechaBloqueo
        );

        $modeloBloqueoHorario->set_horaInicio(
            $horaInicio
        );

        $modeloBloqueoHorario->set_horaFin(
            $horaFin
        );

        $modeloBloqueoHorario->set_motivo(
            $motivoBloqueo
        );


        if (
            $idBloqueo <= 0 ||
            $idUsuarioEspecialista <= 0 ||
            $fechaBloqueo === '' ||
            $horaInicio === '' ||
            $horaFin === '' ||
            $horaInicio >= $horaFin
        ) {
            header(
                'Location: Index.php?url=agendamiento'
                . '&mensaje=bloqueo_datos_invalidos'
            );

            exit;
        }


        if (
            $modeloBloqueoHorario
                ->existeConflictoConBloqueo(
                    $idBloqueo
                )
        ) {
            header(
                'Location: Index.php?url=agendamiento'
                . '&mensaje=bloqueo_conflicto_bloqueo'
            );

            exit;
        }


        if (
            $modeloBloqueoHorario
                ->existeConflictoConAgendamiento()
        ) {
            header(
                'Location: Index.php?url=agendamiento'
                . '&mensaje=bloqueo_conflicto_cita'
            );

            exit;
        }


        if ($modeloBloqueoHorario->modificar()) {
            header(
                'Location: Index.php?url=agendamiento'
                . '&mensaje=bloqueo_modificado'
            );

            exit;
        }


        header(
            'Location: Index.php?url=agendamiento'
            . '&mensaje=bloqueo_no_modificado'
        );

        exit;
    }


    /* ================================================= */
    /* DESBLOQUEAR HORARIO */
    /* ================================================= */

    if (
        $_POST['accion'] ===
        'desbloquear_horario'
    ) {
        $modeloBloqueoHorario->set_idBloqueo(
            (int) (
                $_POST['id_bloqueo']
                ?? 0
            )
        );

        $modeloBloqueoHorario->eliminar();


        header(
            'Location: Index.php?url=agendamiento'
            . '&mensaje=horario_desbloqueado'
        );

        exit;
    }
}


/* ===================================================== */
/* CONSULTAS PARA LLENAR LOS FORMULARIOS */
/* ===================================================== */

$clientes = $modeloCliente->consultar();

$servicios = $modeloServicio->consultar();

$estadosAgendamientos =
    $modeloEstadoAgendamiento->consultar();

$tiposAgendamientos =
    $modeloTipoAgendamiento->consultar();


/* Consultar los agendamientos registrados */
$agendamientos =
    $modeloAgendamiento->consultar();


/* Consultar los bloqueos de horario activos */
$bloqueosHorario =
    $modeloBloqueoHorario->consultar();


/* ===================================================== */
/* PREPARAR DATOS PARA LA LISTA Y EL CALENDARIO */
/* ===================================================== */

$agendamientosCalendario = [];


foreach ($agendamientos as $agendamiento) {

    $fecha = trim(
        $agendamiento['fecha'] ?? ''
    );

    $hora = trim(
        $agendamiento['hora'] ?? ''
    );


    if ($fecha === '' || $hora === '') {
        continue;
    }


    $duracionMinutos = max(
        1,
        (int) (
            $agendamiento['duracion_minutos']
            ?? 0
        )
    );


    try {

        $fechaInicio = new DateTime(
            $fecha . ' ' . $hora
        );

        $fechaFin = clone $fechaInicio;

        $fechaFin->modify(
            '+' . $duracionMinutos . ' minutes'
        );

    } catch (\Exception $e) {

        continue;
    }


    /* Convertir los identificadores de servicios en arreglo */
    $serviciosIds = [];

    if (
        !empty(
            $agendamiento['servicios_ids']
        )
    ) {
        $serviciosIds = array_map(
            'intval',
            explode(
                ',',
                $agendamiento['servicios_ids']
            )
        );
    }


    /* Convertir los nombres de servicios en arreglo */
    $nombresServicios = [];

    if (
        !empty(
            $agendamiento['servicios']
        )
    ) {
        $nombresServicios = explode(
            '||',
            $agendamiento['servicios']
        );
    }


    $agendamientosCalendario[] = [

        'id' =>
            (int) $agendamiento['id_agendamiento'],

        'title' =>
            $agendamiento['cliente']
            ?? 'Agendamiento',

        'start' =>
            $fechaInicio->format(
                'Y-m-d\TH:i:s'
            ),

        'end' =>
            $fechaFin->format(
                'Y-m-d\TH:i:s'
            ),

        'extendedProps' => [

            'es_bloqueo' => false,

            'id_cliente' =>
                $agendamiento['id_cliente'],

            'id_usuario_asignado' =>
                $agendamiento[
                    'id_usuario_asignado'
                ],

            'id_estado_agendamiento' =>
                $agendamiento[
                    'id_estado_agendamiento'
                ],

            'id_tipo_agendamiento' =>
                $agendamiento[
                    'id_tipo_agendamiento'
                ],

            'cliente' =>
                $agendamiento['cliente'],

            'cedula_cliente' =>
                $agendamiento['cedula_cliente'],

            'telefono' =>
                $agendamiento['telefono_cliente'],

            'especialista' =>
                $agendamiento['especialista'],

            'usuario_registra' =>
                $agendamiento['usuario_registra'],

            'estado' =>
                $agendamiento['estado'],

            'tipo_agendamiento' =>
                $agendamiento[
                    'tipo_agendamiento'
                ],

            'observacion_inicial' =>
                $agendamiento[
                    'observacion_inicial'
                ],

            'duracion_minutos' =>
                $duracionMinutos,

            'monto_total' =>
                (float) $agendamiento[
                    'monto_total'
                ],

            'servicios_ids' =>
                $serviciosIds,

            'servicios' =>
                $nombresServicios
        ]
    ];
}


/* Agregar los bloqueos como eventos independientes del calendario */
foreach ($bloqueosHorario as $bloqueo) {

    $fecha = trim(
        $bloqueo['fecha'] ?? ''
    );

    $horaInicio = trim(
        $bloqueo['hora_inicio'] ?? ''
    );

    $horaFin = trim(
        $bloqueo['hora_fin'] ?? ''
    );


    if (
        $fecha === '' ||
        $horaInicio === '' ||
        $horaFin === ''
    ) {
        continue;
    }


    $agendamientosCalendario[] = [

        'id' =>
            'bloqueo-'
            . (int) $bloqueo['id_bloqueo'],

        'title' =>
            'Horario bloqueado',

        'start' =>
            $fecha . 'T' . $horaInicio,

        'end' =>
            $fecha . 'T' . $horaFin,

        'extendedProps' => [

            'es_bloqueo' => true,

            'id_bloqueo' =>
                (int) $bloqueo['id_bloqueo'],

            'id_usuario_especialista' =>
                (int) $bloqueo[
                    'id_usuario_especialista'
                ],

            'especialista' =>
                $bloqueo['especialista'],

            'usuario_registra' =>
                $bloqueo['usuario_registra'],

            'motivo' =>
                $bloqueo['motivo'],

            'estado_bloqueo' =>
                $bloqueo['estado_bloqueo']
        ]
    ];
}


/* Cargar la vista de Agendamiento */
require_once __DIR__ . '/../View/AgendamientoV.php';