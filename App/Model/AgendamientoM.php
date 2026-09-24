<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;

class AgendamientoM extends Database implements CrudInterface
{
    /* Conexión */
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


    /* Setters */

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

        $this->servicios = array_values(
            array_unique($serviciosLimpios)
        );
    }


    /* Constructor */

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /*
     * Consultar:
     * obtiene los agendamientos con sus relaciones.
     */

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

                    TRIM(
                        CONCAT(
                            pc.nombre,
                            ' ',
                            pc.apellido
                        )
                    ) AS cliente,

                    pc.cedula AS cedula_cliente,
                    pc.telefono AS telefono_cliente,

                    TRIM(
                        CONCAT(
                            pe.nombre,
                            ' ',
                            pe.apellido
                        )
                    ) AS especialista,

                    TRIM(
                        CONCAT(
                            pr.nombre,
                            ' ',
                            pr.apellido
                        )
                    ) AS usuario_registra,

                    ea.nombre_estado AS estado,

                    ta.nombre_tipo_agendamiento
                        AS tipo_agendamiento,

                    (
                        SELECT GROUP_CONCAT(
                            d.id_servicio
                            ORDER BY d.id_servicio
                            SEPARATOR ','
                        )

                        FROM detalles_agendamientos d

                        WHERE d.id_agendamiento =
                            a.id_agendamiento
                    ) AS servicios_ids,

                    (
                        SELECT GROUP_CONCAT(
                            s.nombre_servicio
                            ORDER BY s.nombre_servicio
                            SEPARATOR '||'
                        )

                        FROM detalles_agendamientos d

                        INNER JOIN servicios s
                            ON s.id_servicio =
                                d.id_servicio

                        WHERE d.id_agendamiento =
                            a.id_agendamiento
                    ) AS servicios

                FROM agendamientos a

                LEFT JOIN clientes c
                    ON c.id_cliente =
                        a.id_cliente

                LEFT JOIN personas pc
                    ON pc.id_persona =
                        c.id_persona

                INNER JOIN usuarios ue
                    ON ue.id_usuario =
                        a.id_usuario_asignado

                INNER JOIN personas pe
                    ON pe.id_persona =
                        ue.id_persona

                INNER JOIN usuarios ur
                    ON ur.id_usuario =
                        a.id_usuario_registra

                INNER JOIN personas pr
                    ON pr.id_persona =
                        ur.id_persona

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento =
                        a.id_estado_agendamiento

                INNER JOIN tipos_agendamientos ta
                    ON ta.id_tipo_agendamiento =
                        a.id_tipo_agendamiento

                ORDER BY
                    a.fecha ASC,
                    a.hora ASC";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /*
     * Registrar:
     * guarda el agendamiento y sus servicios.
     */

    public function registrar()
    {
        try {

            $this->conexionMD->beginTransaction();


            $sqlAgendamiento =
                "INSERT INTO agendamientos (
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


            $stmtAgendamiento = $this->conexionMD->prepare(
                $sqlAgendamiento
            );


            if (
                $this->idCliente === null ||
                $this->idCliente === ''
            ) {
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


            /*
             * Guardar cada servicio seleccionado
             * en detalles_agendamientos.
             */

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


    /*
     * Modificar:
     * actualiza el agendamiento y reemplaza
     * los servicios seleccionados.
     */

    public function modificar()
    {
        try {

            $this->conexionMD->beginTransaction();


            $sqlAgendamiento =
                "UPDATE agendamientos

                 SET
                    id_cliente =
                        :id_cliente,

                    id_usuario_asignado =
                        :id_usuario_asignado,

                    id_estado_agendamiento =
                        :id_estado_agendamiento,

                    id_tipo_agendamiento =
                        :id_tipo_agendamiento,

                    fecha =
                        :fecha,

                    hora =
                        :hora,

                    duracion_minutos =
                        :duracion_minutos,

                    observacion_inicial =
                        :observacion_inicial,

                    monto_total =
                        :monto_total

                 WHERE id_agendamiento =
                    :id_agendamiento";


            $stmtAgendamiento = $this->conexionMD->prepare(
                $sqlAgendamiento
            );


            if (
                $this->idCliente === null ||
                $this->idCliente === ''
            ) {
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


            /*
             * Eliminar los servicios anteriores.
             */

            $sqlEliminarDetalles =
                "DELETE FROM
                    detalles_agendamientos

                 WHERE id_agendamiento =
                    :id_agendamiento";


            $stmtEliminarDetalles = $this->conexionMD->prepare(
                $sqlEliminarDetalles
            );

            $stmtEliminarDetalles->bindParam(':id_agendamiento', $this->idAgendamiento);

            $stmtEliminarDetalles->execute();


            /*
             * Guardar la nueva selección
             * de servicios.
             */

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


    /*
     * Eliminar lógico:
     * no borra el agendamiento.
     * Cambia su estado a CANCELADA.
     */

    public function eliminar()
    {
        $sql =
            "UPDATE agendamientos a

             INNER JOIN estados_agendamientos ea
                ON ea.nombre_estado =
                    'CANCELADA'

                AND ea.estado_registro =
                    'ACTIVO'

             SET a.id_estado_agendamiento =
                ea.id_estado_agendamiento

             WHERE a.id_agendamiento =
                :id_agendamiento";


        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_agendamiento', $this->idAgendamiento);

        return $stmt->execute();
    }


    /*
     * Método interno:
     * registra los servicios relacionados
     * con el agendamiento.
     */

    private function registrarServicios($idAgendamiento)
    {
        if (empty($this->servicios)) {
            return;
        }


        $sql =
            "INSERT INTO detalles_agendamientos (
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