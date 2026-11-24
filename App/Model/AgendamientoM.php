<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use App\Interfaces\DisponibilidadInterface;
use App\Traits\ValidacionAtencionTrait;
use PDO;


/* ===================================================== */
/* MODELO DE AGENDAMIENTO */
/* ===================================================== */

class AgendamientoM extends Database implements CrudInterface, DisponibilidadInterface
{
    use ValidacionAtencionTrait;


    /* ================================================= */
    /* ATRIBUTOS DEL MODELO */
    /* ================================================= */

    /* Conexión con la base de datos */
    private $conexionMD;

    /* Reglas del flujo de las citas */
    private $flujoCita;

    /* Atributos de la tabla agendamientos */
    private $idAgendamiento;
    private $idCliente;
    private $idUsuarioRegistra;
    private $idUsuarioAsignado;
    private $idEstadoAgendamiento;
    private $idTipoAgendamiento;
    private $fecha;
    private $hora;
    private $duracionMinutos;
    private $observacionInicial;
    private $montoTotal;

    /* Servicios seleccionados */
    private $servicios = [];

    /* Aviso de reposición después de guardar la cita */
    private string $advertenciaReserva = '';


    /* ================================================= */
    /* ASIGNACIÓN DE DATOS */
    /* ================================================= */

    public function set_idAgendamiento($idAgendamiento) { $this->idAgendamiento = $idAgendamiento; }
    public function set_idCliente($idCliente) { $this->idCliente = $idCliente; }
    public function set_idUsuarioRegistra($idUsuarioRegistra) { $this->idUsuarioRegistra = $idUsuarioRegistra; }
    public function set_idUsuarioAsignado($idUsuarioAsignado) { $this->idUsuarioAsignado = $idUsuarioAsignado; }
    public function set_idEstadoAgendamiento($idEstadoAgendamiento) { $this->idEstadoAgendamiento = $idEstadoAgendamiento; }
    public function set_idTipoAgendamiento($idTipoAgendamiento) { $this->idTipoAgendamiento = $idTipoAgendamiento; }
    public function set_fecha($fecha) { $this->fecha = $fecha; }
    public function set_hora($hora) { $this->hora = $hora; }
    public function set_duracionMinutos($duracionMinutos) { $this->duracionMinutos = $duracionMinutos; }
    public function set_observacionInicial($observacionInicial) { $this->observacionInicial = $observacionInicial; }
    public function set_montoTotal($montoTotal) { $this->montoTotal = $montoTotal; }


    /* Conservar los datos originales para validarlos antes de guardar */
    public function set_servicios($servicios) { $this->servicios = $servicios; }


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
        $this->flujoCita = new FlujoCita();
    }


    /* ================================================= */
    /* ADVERTENCIAS DE INSUMOS */
    /* ================================================= */

    public function obtenerAdvertenciaReserva(): string
    {
        return $this->advertenciaReserva;
    }


    /* ================================================= */
    /* ACTUALIZAR CITAS DEMORADAS DEL DÍA */
    /* ================================================= */

    /* Marcar las citas que alcanzaron su hora sin comenzar la atención */
    public function actualizarDemorasDelDia(): int
    {
        $ahora = new \DateTimeImmutable();
        $fechaHoy = $ahora->format('Y-m-d');
        $horaActual = $ahora->format('H:i:s');

        /* Considerar solamente citas de hoy que aún no comenzaron */
        $sql = "SELECT
                    a.id_agendamiento,
                    a.id_estado_agendamiento,
                    a.fecha,
                    a.hora,
                    ea.nombre_estado AS estado

                FROM agendamientos a

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento = a.id_estado_agendamiento

                WHERE a.fecha = :fecha_hoy
                    AND a.hora <= :hora_actual
                    AND ea.nombre_estado IN ('AGENDADA', 'CONFIRMADA', 'EN ESPERA')";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':fecha_hoy', $fechaHoy);
        $stmt->bindValue(':hora_actual', $horaActual);

        $stmt->execute();

        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($citas === []) {
            return 0;
        }

        /* Obtener el identificador del estado DEMORADA */
        $sql = "SELECT id_estado_agendamiento

                FROM estados_agendamientos

                WHERE nombre_estado = 'DEMORADA'
                    AND estado_registro = 'ACTIVO'

                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();

        $idEstadoDemorada = $stmt->fetchColumn();

        if ($idEstadoDemorada === false) {
            throw new \DomainException(
                'El estado DEMORADA no se encuentra disponible.'
            );
        }

        /* Proteger el estado y horario si la cita cambia durante la consulta */
        $sql = "UPDATE agendamientos

                SET id_estado_agendamiento = :id_estado_demorada

                WHERE id_agendamiento = :id_agendamiento
                    AND id_estado_agendamiento = :id_estado_actual
                    AND fecha = :fecha_programada
                    AND hora = :hora_programada";

        $stmt = $this->conexionMD->prepare($sql);

        $actualizadas = 0;

        foreach ($citas as $cita) {
            $fechaHoraProgramada = new \DateTimeImmutable(
                $cita['fecha'] . ' ' . $cita['hora']
            );

            if (!$this->flujoCita->debeMarcarDemorada(
                (string) $cita['estado'],
                $fechaHoraProgramada,
                $ahora
            )) {
                continue;
            }

            $stmt->bindValue(':id_estado_demorada', (int) $idEstadoDemorada, PDO::PARAM_INT);
            $stmt->bindValue(':id_agendamiento', (int) $cita['id_agendamiento'], PDO::PARAM_INT);
            $stmt->bindValue(':id_estado_actual', (int) $cita['id_estado_agendamiento'], PDO::PARAM_INT);
            $stmt->bindValue(':fecha_programada', $cita['fecha']);
            $stmt->bindValue(':hora_programada', $cita['hora']);

            $stmt->execute();

            $actualizadas += $stmt->rowCount();
        }

        return $actualizadas;
    }


    /* ================================================= */
    /* CONSULTAR AGENDAMIENTOS */
    /* ================================================= */

    public function consultar(): array
    {
        /* Recuperar las citas y detectar si ya tienen una atención registrada */
        $sql = "SELECT
                    a.id_agendamiento,
                    a.id_cliente,
                    a.id_usuario_registra,
                    a.id_usuario_asignado,
                    a.id_estado_agendamiento,
                    a.id_tipo_agendamiento,
                    a.fecha,
                    a.hora,
                    a.duracion_minutos,
                    a.observacion_inicial,
                    a.monto_total,

                    TRIM(CONCAT(pc.nombre, ' ', pc.apellido)) AS cliente,

                    pc.cedula AS cedula_cliente,
                    pc.telefono AS telefono_cliente,

                    TRIM(CONCAT(pe.nombre, ' ', pe.apellido)) AS especialista,

                    TRIM(CONCAT(pr.nombre, ' ', pr.apellido)) AS usuario_registra,

                    ea.nombre_estado AS estado,

                    ta.nombre_tipo_agendamiento AS tipo_agendamiento,

                    (
                        EXISTS (
                            SELECT 1

                            FROM detalles_agendamientos d

                            WHERE d.id_agendamiento = a.id_agendamiento
                                AND (
                                    d.precio_aplicado IS NOT NULL OR
                                    NULLIF(TRIM(d.nota_tecnica), '') IS NOT NULL
                                )
                        ) OR EXISTS (
                            SELECT 1

                            FROM fotos_servicios f

                            INNER JOIN detalles_agendamientos d
                                ON d.id_detalle_agendamiento =
                                    f.id_detalle_agendamiento

                            WHERE d.id_agendamiento = a.id_agendamiento
                        ) OR EXISTS (
                            SELECT 1

                            FROM consumos_insumos ci

                            WHERE ci.id_agendamiento = a.id_agendamiento
                        )
                    ) AS tiene_registro_servicio,

                    (
                        SELECT GROUP_CONCAT(
                            d.id_servicio
                            ORDER BY d.id_servicio
                            SEPARATOR ','
                        )

                        FROM detalles_agendamientos d

                        WHERE d.id_agendamiento = a.id_agendamiento
                    ) AS servicios_ids,

                    (
                        SELECT GROUP_CONCAT(
                            s.nombre_servicio
                            ORDER BY s.nombre_servicio
                            SEPARATOR '||'
                        )

                        FROM detalles_agendamientos d

                        INNER JOIN servicios s
                            ON s.id_servicio = d.id_servicio

                        WHERE d.id_agendamiento = a.id_agendamiento
                    ) AS servicios

                FROM agendamientos a

                LEFT JOIN clientes c
                    ON c.id_cliente = a.id_cliente

                LEFT JOIN personas pc
                    ON pc.id_persona = c.id_persona

                INNER JOIN usuarios ue
                    ON ue.id_usuario = a.id_usuario_asignado

                INNER JOIN personas pe
                    ON pe.id_persona = ue.id_persona

                INNER JOIN usuarios ur
                    ON ur.id_usuario = a.id_usuario_registra

                INNER JOIN personas pr
                    ON pr.id_persona = ur.id_persona

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento = a.id_estado_agendamiento

                INNER JOIN tipos_agendamientos ta
                    ON ta.id_tipo_agendamiento = a.id_tipo_agendamiento

                ORDER BY a.fecha ASC, a.hora ASC";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->execute();

        $agendamientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /* Entregar la misma regla de protección a la lista y al calendario */
        foreach ($agendamientos as &$agendamiento) {
            $agendamiento['tiene_registro_servicio'] =
                (bool) $agendamiento['tiene_registro_servicio'];

            $agendamiento['motivo_bloqueo'] =
                $this->obtenerMotivoBloqueo($agendamiento);

            $agendamiento['gestion_permitida'] =
                $agendamiento['motivo_bloqueo'] === '';
        }

        unset($agendamiento);

        return $agendamientos;
    }


    /* ================================================= */
    /* REGISTRAR AGENDAMIENTO */
    /* ================================================= */

    public function registrar(): bool
    {
        if (!$this->validarDatosAgendamiento(false)) {
            throw new \InvalidArgumentException(implode(' ', $this->erroresValidacion));
        }

        try {
            $this->conexionMD->beginTransaction();

            $this->validarEstadoSeleccionado();

            /* Registrar la cita antes de incorporar sus servicios */
            $sqlAgendamiento = "INSERT INTO agendamientos (
                                    id_cliente,
                                    id_usuario_registra,
                                    id_usuario_asignado,
                                    id_estado_agendamiento,
                                    id_tipo_agendamiento,
                                    fecha,
                                    hora,
                                    duracion_minutos,
                                    observacion_inicial,
                                    monto_total
                                ) VALUES (
                                    :id_cliente,
                                    :id_usuario_registra,
                                    :id_usuario_asignado,
                                    :id_estado_agendamiento,
                                    :id_tipo_agendamiento,
                                    :fecha,
                                    :hora,
                                    :duracion_minutos,
                                    :observacion_inicial,
                                    :monto_total
                                )";

            $stmtAgendamiento = $this->conexionMD->prepare($sqlAgendamiento);

            if ($this->idCliente === null || $this->idCliente === '') {
                $stmtAgendamiento->bindValue(':id_cliente', null, PDO::PARAM_NULL);
            } else {
                $stmtAgendamiento->bindParam(':id_cliente', $this->idCliente);
            }

            $stmtAgendamiento->bindParam(':id_usuario_registra', $this->idUsuarioRegistra);
            $stmtAgendamiento->bindParam(':id_usuario_asignado', $this->idUsuarioAsignado);
            $stmtAgendamiento->bindParam(':id_estado_agendamiento', $this->idEstadoAgendamiento);
            $stmtAgendamiento->bindParam(':id_tipo_agendamiento', $this->idTipoAgendamiento);
            $stmtAgendamiento->bindParam(':fecha', $this->fecha);
            $stmtAgendamiento->bindParam(':hora', $this->hora);
            $stmtAgendamiento->bindParam(':duracion_minutos', $this->duracionMinutos);
            $stmtAgendamiento->bindParam(':observacion_inicial', $this->observacionInicial);
            $stmtAgendamiento->bindParam(':monto_total', $this->montoTotal);

            $stmtAgendamiento->execute();

            $idAgendamiento = $this->conexionMD->lastInsertId();

            $this->registrarServicios($idAgendamiento);

            $this->advertenciaReserva = (new ReservaInsumosM($this->conexionMD))
                ->advertenciaAgendamiento((int) $idAgendamiento);

            $this->conexionMD->commit();

            return true;

        } catch (\Throwable $e) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $e;
        }
    }


    /* ================================================= */
    /* MODIFICAR AGENDAMIENTO */
    /* ================================================= */

    public function modificar(): bool
    {
        if (!$this->validarDatosAgendamiento(true)) {
            throw new \InvalidArgumentException(implode(' ', $this->erroresValidacion));
        }

        try {
            $this->conexionMD->beginTransaction();

            /* Bloquear la cita y comprobar la transición antes de modificarla */
            $estadoActual = $this->bloquearAgendamientoGestionable();
            $this->validarEstadoSeleccionado($estadoActual);

            $sqlAgendamiento = "UPDATE agendamientos

                               SET id_cliente = :id_cliente,
                                   id_usuario_asignado = :id_usuario_asignado,
                                   id_estado_agendamiento = :id_estado_agendamiento,
                                   id_tipo_agendamiento = :id_tipo_agendamiento,
                                   fecha = :fecha,
                                   hora = :hora,
                                   duracion_minutos = :duracion_minutos,
                                   observacion_inicial = :observacion_inicial,
                                   monto_total = :monto_total

                               WHERE id_agendamiento = :id_agendamiento";

            $stmtAgendamiento = $this->conexionMD->prepare($sqlAgendamiento);

            if ($this->idCliente === null || $this->idCliente === '') {
                $stmtAgendamiento->bindValue(':id_cliente', null, PDO::PARAM_NULL);
            } else {
                $stmtAgendamiento->bindParam(':id_cliente', $this->idCliente);
            }

            $stmtAgendamiento->bindParam(':id_usuario_asignado', $this->idUsuarioAsignado);
            $stmtAgendamiento->bindParam(':id_estado_agendamiento', $this->idEstadoAgendamiento);
            $stmtAgendamiento->bindParam(':id_tipo_agendamiento', $this->idTipoAgendamiento);
            $stmtAgendamiento->bindParam(':fecha', $this->fecha);
            $stmtAgendamiento->bindParam(':hora', $this->hora);
            $stmtAgendamiento->bindParam(':duracion_minutos', $this->duracionMinutos);
            $stmtAgendamiento->bindParam(':observacion_inicial', $this->observacionInicial);
            $stmtAgendamiento->bindParam(':monto_total', $this->montoTotal);
            $stmtAgendamiento->bindParam(':id_agendamiento', $this->idAgendamiento);

            $stmtAgendamiento->execute();

            /* Conservar los detalles de los servicios que permanecen seleccionados */
            $this->actualizarServicios((int) $this->idAgendamiento);

            $this->advertenciaReserva = (new ReservaInsumosM($this->conexionMD))
                ->advertenciaAgendamiento((int) $this->idAgendamiento);

            $this->conexionMD->commit();

            return true;

        } catch (\Throwable $e) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $e;
        }
    }


    /* ================================================= */
    /* CANCELAR AGENDAMIENTO */
    /* ================================================= */

    public function eliminar(): bool
    {
        if (!$this->validarIdAgendamientoAgenda()) {
            throw new \InvalidArgumentException(implode(' ', $this->erroresValidacion));
        }

        try {
            $this->conexionMD->beginTransaction();

            /* Comprobar la protección y la cancelación dentro de la transacción */
            $estadoActual = $this->bloquearAgendamientoGestionable();

            if (!$this->flujoCita->permiteTransicion($estadoActual, 'CANCELADA')) {
                throw new \DomainException(
                    'El estado actual no permite cancelar esta cita.'
                );
            }

            $sql = "SELECT id_estado_agendamiento

                    FROM estados_agendamientos

                    WHERE nombre_estado = 'CANCELADA'
                        AND estado_registro = 'ACTIVO'

                    LIMIT 1";

            $stmt = $this->conexionMD->prepare($sql);

            $stmt->execute();

            $idEstadoCancelada = $stmt->fetchColumn();

            if ($idEstadoCancelada === false) {
                throw new \DomainException(
                    'El estado CANCELADA no se encuentra disponible.'
                );
            }

            /* Conservar la cita y sus relaciones mediante la cancelación lógica */
            $sql = "UPDATE agendamientos

                    SET id_estado_agendamiento = :id_estado

                    WHERE id_agendamiento = :id_agendamiento";

            $stmt = $this->conexionMD->prepare($sql);

            $stmt->bindValue(':id_estado', (int) $idEstadoCancelada, PDO::PARAM_INT);
            $stmt->bindValue(':id_agendamiento', (int) $this->idAgendamiento, PDO::PARAM_INT);

            $stmt->execute();

            if ($stmt->rowCount() !== 1) {
                throw new \RuntimeException(
                    'No fue posible confirmar la cancelación del agendamiento.'
                );
            }

            $this->conexionMD->commit();

            return true;

        } catch (\Throwable $e) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $e;
        }
    }


    /* ================================================= */
    /* COMPROBAR DISPONIBILIDAD */
    /* ================================================= */

    /* Implementar el contrato común utilizando la comprobación existente */
    public function existeConflictoHorario(
        int $idEspecialista,
        string $fecha,
        string $horaInicio,
        string $horaFin,
        ?int $idExcluir = null
    ): bool {
        return $this->existeConflictoConAgendamiento(
            $idEspecialista,
            $fecha,
            $horaInicio,
            $horaFin,
            $idExcluir
        );
    }


    /* Conservar la comprobación utilizada por el controlador de Agendamiento */
    public function existeConflictoConAgendamiento(
        $idUsuarioAsignado,
        $fecha,
        $horaInicio,
        $horaFin,
        $idExcluir = null
    ): bool {
        /* Detectar cruces sin contar citas canceladas ni la cita excluida */
        $sql = "SELECT COUNT(*)

                FROM agendamientos a

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento = a.id_estado_agendamiento

                WHERE a.id_usuario_asignado = :id_usuario_asignado
                    AND a.fecha = :fecha
                    AND UPPER(ea.nombre_estado) <> 'CANCELADA'
                    AND a.hora < :hora_fin
                    AND ADDTIME(
                        a.hora,
                        SEC_TO_TIME(a.duracion_minutos * 60)
                    ) > :hora_inicio";

        if ($idExcluir !== null) {
            $sql .= " AND a.id_agendamiento <> :id_excluir";
        }

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':id_usuario_asignado', (int) $idUsuarioAsignado, PDO::PARAM_INT);
        $stmt->bindValue(':fecha', $fecha);
        $stmt->bindValue(':hora_inicio', $horaInicio);
        $stmt->bindValue(':hora_fin', $horaFin);

        if ($idExcluir !== null) {
            $stmt->bindValue(':id_excluir', (int) $idExcluir, PDO::PARAM_INT);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }


    /* ================================================= */
    /* PROTEGER LA ATENCIÓN REGISTRADA */
    /* ================================================= */

    private function obtenerMotivoBloqueo(array $agendamiento): string
    {
        $estado = strtoupper(
            trim((string) ($agendamiento['estado'] ?? ''))
        );

        if ($estado === 'REALIZADA') {
            return 'La atención ya fue realizada. No puede modificarse ni cancelarse.';
        }

        if (in_array($estado, ['EN ATENCIÓN', 'EN ATENCION'], true)) {
            return 'La atención ya comenzó. No puede modificarse ni cancelarse desde Agendamiento.';
        }

        if ($estado === 'CANCELADA') {
            return 'El agendamiento ya se encuentra cancelado.';
        }

        if (!empty($agendamiento['tiene_registro_servicio'])) {
            return 'La cita ya tiene información de una atención registrada. ' .
                'No puede modificarse ni cancelarse desde Agendamiento.';
        }

        if (!$this->flujoCita->esGestionable($estado)) {
            return 'El estado actual del agendamiento no permite modificarlo ni cancelarlo.';
        }

        return '';
    }

    private function bloquearAgendamientoGestionable(): string
    {
        $idAgendamiento = (int) $this->idAgendamiento;

        if ($idAgendamiento <= 0) {
            throw new \InvalidArgumentException(
                'El identificador del agendamiento no es válido.'
            );
        }

        /* Compartir el bloqueo de la cita con Registro de Servicio */
        $sql = "SELECT ea.nombre_estado AS estado

                FROM agendamientos a

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento = a.id_estado_agendamiento

                WHERE a.id_agendamiento = :id_agendamiento

                LIMIT 1
                FOR UPDATE";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);

        $stmt->execute();

        $agendamiento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$agendamiento) {
            throw new \DomainException(
                'El agendamiento ya no se encuentra disponible. Actualice la lista.'
            );
        }

        $motivo = $this->obtenerMotivoBloqueo($agendamiento);

        if ($motivo !== '') {
            throw new \DomainException($motivo);
        }

        /* Proteger la información guardada aunque la cita tenga un estado pendiente */
        $sql = "SELECT (
                    EXISTS (
                        SELECT 1

                        FROM detalles_agendamientos d

                        WHERE d.id_agendamiento = a.id_agendamiento
                            AND (
                                d.precio_aplicado IS NOT NULL OR
                                NULLIF(TRIM(d.nota_tecnica), '') IS NOT NULL
                            )
                    ) OR EXISTS (
                        SELECT 1

                        FROM fotos_servicios f

                        INNER JOIN detalles_agendamientos d
                            ON d.id_detalle_agendamiento =
                                f.id_detalle_agendamiento

                        WHERE d.id_agendamiento = a.id_agendamiento
                    ) OR EXISTS (
                        SELECT 1

                        FROM consumos_insumos ci

                        WHERE ci.id_agendamiento = a.id_agendamiento
                    )
                )

                FROM agendamientos a

                WHERE a.id_agendamiento = :id_agendamiento";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);

        $stmt->execute();

        if ((bool) $stmt->fetchColumn()) {
            throw new \DomainException(
                'La cita ya tiene información de una atención registrada. ' .
                'No puede modificarse ni cancelarse desde Agendamiento.'
            );
        }

        return (string) $agendamiento['estado'];
    }

    private function validarEstadoSeleccionado(?string $estadoActual = null): void
    {
        $sql = "SELECT nombre_estado

                FROM estados_agendamientos

                WHERE id_estado_agendamiento = :id_estado
                    AND estado_registro = 'ACTIVO'

                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':id_estado', (int) $this->idEstadoAgendamiento, PDO::PARAM_INT);

        $stmt->execute();

        $estado = $stmt->fetchColumn();

        if ($estado === false) {
            throw new \InvalidArgumentException(
                'Seleccione un estado de agendamiento activo y válido.'
            );
        }

        if (!$this->flujoCita->esGestionable((string) $estado)) {
            throw new \DomainException(
                'Seleccione un estado previo a la atención. ' .
                'El inicio y la finalización se gestionan desde Registro de Servicio; ' .
                'la cancelación utiliza su propia acción.'
            );
        }

        /* Respetar el orden de los estados cuando se modifica una cita */
        if (
            $estadoActual !== null &&
            !$this->flujoCita->permiteTransicion($estadoActual, (string) $estado)
        ) {
            throw new \DomainException(
                'No se permite cambiar el estado de ' . $estadoActual .
                ' a ' . $estado . '. Revise el flujo de la cita.'
            );
        }
    }


    /* ================================================= */
    /* ACTUALIZAR LOS SERVICIOS SELECCIONADOS */
    /* ================================================= */

    private function actualizarServicios(int $idAgendamiento): void
    {
        $sql = "SELECT id_servicio

                FROM detalles_agendamientos

                WHERE id_agendamiento = :id_agendamiento

                FOR UPDATE";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);

        $stmt->execute();

        $serviciosActuales = array_map(
            'intval',
            $stmt->fetchAll(PDO::FETCH_COLUMN)
        );

        $serviciosRetirados = array_diff(
            $serviciosActuales,
            $this->servicios
        );

        $serviciosNuevos = array_diff(
            $this->servicios,
            $serviciosActuales
        );

        /* Retirar únicamente los servicios deseleccionados de una cita sin atención */
        if ($serviciosRetirados !== []) {
            $sql = "DELETE FROM detalles_agendamientos

                    WHERE id_agendamiento = :id_agendamiento
                        AND id_servicio = :id_servicio";

            $stmt = $this->conexionMD->prepare($sql);

            $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);

            foreach ($serviciosRetirados as $idServicio) {
                $stmt->bindValue(':id_servicio', $idServicio, PDO::PARAM_INT);

                $stmt->execute();
            }
        }

        $this->registrarServicios(
            $idAgendamiento,
            $serviciosNuevos
        );
    }


    /* ================================================= */
    /* REGISTRAR SERVICIOS ASOCIADOS */
    /* ================================================= */

    private function registrarServicios(
        $idAgendamiento,
        ?array $servicios = null
    ): void {
        $servicios = $servicios ?? $this->servicios;

        if ($servicios === []) {
            return;
        }

        $sql = "INSERT INTO detalles_agendamientos (
                    id_agendamiento,
                    id_servicio
                ) VALUES (
                    :id_agendamiento,
                    :id_servicio
                )";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':id_agendamiento', (int) $idAgendamiento, PDO::PARAM_INT);

        foreach ($servicios as $idServicio) {
            $stmt->bindValue(':id_servicio', (int) $idServicio, PDO::PARAM_INT);

            $stmt->execute();
        }
    }
}