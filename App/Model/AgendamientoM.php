<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;

/* ===================================================== */
/* MODELO DE AGENDAMIENTO */
/* ===================================================== */

class AgendamientoM extends Database implements CrudInterface
{
    /* ================================================= */
    /* ATRIBUTOS DEL MODELO */
    /* ================================================= */

    /* Conexión con la base de datos */
    private $conexionMD;

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


    /* ================================================= */
    /* ASIGNACIÓN DE DATOS */
    /* ================================================= */

    /* Asignar los valores recibidos a los atributos privados */
    public function set_idAgendamiento($idAgendamiento) {$this->idAgendamiento = $idAgendamiento;}
    public function set_idCliente($idCliente) {$this->idCliente = $idCliente;}
    public function set_idUsuarioRegistra($idUsuarioRegistra) {$this->idUsuarioRegistra = $idUsuarioRegistra;}
    public function set_idUsuarioAsignado($idUsuarioAsignado) {$this->idUsuarioAsignado = $idUsuarioAsignado;}
    public function set_idEstadoAgendamiento($idEstadoAgendamiento) {$this->idEstadoAgendamiento = $idEstadoAgendamiento;}
    public function set_idTipoAgendamiento($idTipoAgendamiento) {$this->idTipoAgendamiento = $idTipoAgendamiento;}
    public function set_fecha($fecha) {$this->fecha = $fecha;}
    public function set_hora($hora) {$this->hora = $hora;}
    public function set_duracionMinutos($duracionMinutos) {$this->duracionMinutos = $duracionMinutos;}
    public function set_observacionInicial($observacionInicial) {$this->observacionInicial = $observacionInicial;}
    public function set_montoTotal($montoTotal) {$this->montoTotal = $montoTotal;}

    /* Conservar identificadores de servicios positivos y sin duplicados */
    public function set_servicios($servicios)
    {
        if (!is_array($servicios)) {
            $this->servicios = [];
            return;
        }

        $serviciosLimpios = [];

        foreach ($servicios as $idServicio) {
            $idServicio = (int) $idServicio;

            if ($idServicio > 0) {
                $serviciosLimpios[] = $idServicio;
            }
        }

        $this->servicios = array_values(array_unique($serviciosLimpios));
    }


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    /* Obtener la conexión heredada de Database */
    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* ================================================= */
    /* CONSULTAR AGENDAMIENTOS */
    /* ================================================= */

    /* Obtener las citas con sus clientes, especialistas, estados y servicios */
    public function consultar()
    {
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

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* REGISTRAR AGENDAMIENTO */
    /* ================================================= */

    /* Guardar la cita y sus servicios dentro de una misma transacción */
    public function registrar()
    {
        try {
            $this->conexionMD->beginTransaction();

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

            /* Guardar los servicios asociados a la nueva cita */
            $this->registrarServicios($idAgendamiento);

            $this->conexionMD->commit();

            return true;

        } catch (\Exception $e) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $e;
        }
    }


    /* ================================================= */
    /* MODIFICAR AGENDAMIENTO */
    /* ================================================= */

    /* Actualizar la cita y reemplazar los servicios seleccionados */
    public function modificar()
    {
        try {
            $this->conexionMD->beginTransaction();

            $sqlAgendamiento = "UPDATE agendamientos SET
                        id_cliente = :id_cliente,
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

            /* Retirar la selección anterior de servicios */
            $sqlEliminarDetalles = "DELETE FROM detalles_agendamientos
                    WHERE id_agendamiento = :id_agendamiento";

            $stmtEliminarDetalles = $this->conexionMD->prepare($sqlEliminarDetalles);

            $stmtEliminarDetalles->bindParam(':id_agendamiento', $this->idAgendamiento);

            $stmtEliminarDetalles->execute();

            /* Registrar la nueva selección de servicios */
            $this->registrarServicios($this->idAgendamiento);

            $this->conexionMD->commit();

            return true;

        } catch (\Exception $e) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $e;
        }
    }


    /* ================================================= */
    /* CANCELAR AGENDAMIENTO */
    /* ================================================= */

    /* Cambiar el estado a CANCELADA conservando el registro */
    public function eliminar()
    {
        $sql = "UPDATE agendamientos a

                INNER JOIN estados_agendamientos ea
                    ON ea.nombre_estado = 'CANCELADA'
                    AND ea.estado_registro = 'ACTIVO'

                SET a.id_estado_agendamiento = ea.id_estado_agendamiento

                WHERE a.id_agendamiento = :id_agendamiento";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_agendamiento', $this->idAgendamiento);

        return $stmt->execute();
    }


    /* ================================================= */
    /* COMPROBAR DISPONIBILIDAD */
    /* ================================================= */

    /* Detectar cruces de horario sin contar citas canceladas ni la cita excluida */
    public function existeConflictoConAgendamiento(
        $idUsuarioAsignado,
        $fecha,
        $horaInicio,
        $horaFin,
        $idExcluir = null
    ): bool {
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
    /* REGISTRAR SERVICIOS ASOCIADOS */
    /* ================================================= */

    /* Insertar cada servicio seleccionado en detalles_agendamientos */
    private function registrarServicios($idAgendamiento)
    {
        if (empty($this->servicios)) {
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

        foreach ($this->servicios as $idServicio) {
            $stmt->bindValue(':id_agendamiento', $idAgendamiento);
            $stmt->bindValue(':id_servicio', $idServicio);

            $stmt->execute();
        }
    }
}