<?php

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


/*
 * Consultar los usuarios activos.
 *
 * De esta lista se obtiene:
 * - La administradora que registra temporalmente.
 * - Las especialistas en manicura que pueden ser asignadas.
 */
$usuariosActivos = $modeloSeguridad->consultar();

$usuarios = [];
$idUsuarioRegistra = null;


foreach ($usuariosActivos as $usuario) {

    $nombreRol = strtoupper(
        trim($usuario['nombre_rol'] ?? '')
    );


    /* Administradora temporal que registra */
    if (
        $idUsuarioRegistra === null &&
        in_array(
            $nombreRol,
            [
                'ADMINISTRADORA DEL NEGOCIO',
                'ADMINISTRADOR DEL SISTEMA'
            ],
            true
        )
    ) {
        $idUsuarioRegistra =
            (int) $usuario['id_usuario'];
    }


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
                'Location: Index.php?url=agendamiento&mensaje=horario_bloqueado'
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


        /*
         * Mientras no existan sesiones,
         * debe existir una administradora activa.
         */
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
                'Location: Index.php?url=agendamiento&mensaje=horario_bloqueado'
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

        $modeloBloqueoHorario
            ->set_idUsuarioEspecialista(
                (int) (
                    $_POST['id_usuario_especialista']
                    ?? 0
                )
            );

        $modeloBloqueoHorario
            ->set_idUsuarioRegistra(
                $idUsuarioRegistra
            );

        $modeloBloqueoHorario->set_fecha(
            trim($_POST['fecha'] ?? '')
        );

        $modeloBloqueoHorario->set_horaInicio(
            trim($_POST['hora_inicio'] ?? '')
        );

        $modeloBloqueoHorario->set_horaFin(
            trim($_POST['hora_fin'] ?? '')
        );

        $modeloBloqueoHorario->set_motivo(
            trim($_POST['motivo'] ?? '')
        );


        $horaInicio = trim(
            $_POST['hora_inicio'] ?? ''
        );

        $horaFin = trim(
            $_POST['hora_fin'] ?? ''
        );


        if (
            $idUsuarioRegistra !== null &&
            $horaInicio !== '' &&
            $horaFin !== '' &&
            $horaInicio < $horaFin &&
            !$modeloBloqueoHorario
                ->existeConflictoConBloqueo() &&
            !$modeloBloqueoHorario
                ->existeConflictoConAgendamiento()
        ) {
            $modeloBloqueoHorario->registrar();

            header(
                'Location: Index.php?url=agendamiento&mensaje=bloqueo_registrado'
            );

            exit;
        }


        header(
            'Location: Index.php?url=agendamiento&mensaje=bloqueo_no_registrado'
        );

        exit;
    }


    /* ================================================= */
    /* MODIFICAR BLOQUEO DE HORARIO */
    /* ================================================= */

    if ($_POST['accion'] === 'modificar_bloqueo') {

        $idBloqueo =
            (int) ($_POST['id_bloqueo'] ?? 0);

        $modeloBloqueoHorario->set_idBloqueo(
            $idBloqueo
        );

        $modeloBloqueoHorario
            ->set_idUsuarioEspecialista(
                (int) (
                    $_POST['id_usuario_especialista']
                    ?? 0
                )
            );

        $modeloBloqueoHorario->set_fecha(
            trim($_POST['fecha'] ?? '')
        );

        $modeloBloqueoHorario->set_horaInicio(
            trim($_POST['hora_inicio'] ?? '')
        );

        $modeloBloqueoHorario->set_horaFin(
            trim($_POST['hora_fin'] ?? '')
        );

        $modeloBloqueoHorario->set_motivo(
            trim($_POST['motivo'] ?? '')
        );


        $horaInicio = trim(
            $_POST['hora_inicio'] ?? ''
        );

        $horaFin = trim(
            $_POST['hora_fin'] ?? ''
        );


        if (
            $idBloqueo > 0 &&
            $horaInicio !== '' &&
            $horaFin !== '' &&
            $horaInicio < $horaFin &&
            !$modeloBloqueoHorario
                ->existeConflictoConBloqueo(
                    $idBloqueo
                ) &&
            !$modeloBloqueoHorario
                ->existeConflictoConAgendamiento()
        ) {
            $modeloBloqueoHorario->modificar();

            header(
                'Location: Index.php?url=agendamiento&mensaje=bloqueo_modificado'
            );

            exit;
        }


        header(
            'Location: Index.php?url=agendamiento&mensaje=bloqueo_no_modificado'
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
            'Location: Index.php?url=agendamiento&mensaje=horario_desbloqueado'
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