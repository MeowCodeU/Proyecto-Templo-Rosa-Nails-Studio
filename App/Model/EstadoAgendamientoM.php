<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;


/* ===================================================== */
/* MODELO DE ESTADOS DE AGENDAMIENTO */
/* ===================================================== */

class EstadoAgendamientoM extends Database implements CrudInterface
{
    /* ================================================= */
    /* CONEXIÓN Y ATRIBUTOS */
    /* ================================================= */

    /* Conexión con la base de datos */
    private $conexionMD;

    /* Atributos de la tabla estados_agendamientos */
    private $idEstadoAgendamiento;
    private $nombreEstado;
    private $descripcion;
    private $estadoRegistro;


    /* ================================================= */
    /* SETTERS */
    /* ================================================= */

    public function set_idEstadoAgendamiento($idEstadoAgendamiento) {$this->idEstadoAgendamiento = $idEstadoAgendamiento;}
    public function set_nombreEstado($nombreEstado) {$this->nombreEstado = $nombreEstado;}
    public function set_descripcion($descripcion) {$this->descripcion = $descripcion;}
    public function set_estadoRegistro($estadoRegistro) {$this->estadoRegistro = $estadoRegistro;}


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* ================================================= */
    /* CONSULTAR ESTADOS DE AGENDAMIENTO */
    /* ================================================= */

    public function consultar()
    {
        /* Obtener los estados del catálogo que permanecen activos */
        $sql = "SELECT
                    id_estado_agendamiento,
                    nombre_estado,
                    descripcion,
                    estado_registro
                FROM estados_agendamientos
                WHERE estado_registro = 'ACTIVO'
                ORDER BY nombre_estado ASC";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* REGISTRAR ESTADO DE AGENDAMIENTO */
    /* ================================================= */

    public function registrar()
    {
        /* Registrar un nuevo estado activo en el catálogo */
        $sql = "INSERT INTO estados_agendamientos
                    (nombre_estado, descripcion, estado_registro)
                VALUES
                    (:nombre_estado, :descripcion, 'ACTIVO')";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':nombre_estado', $this->nombreEstado);
        $stmt->bindParam(':descripcion', $this->descripcion);

        return $stmt->execute();
    }


    /* ================================================= */
    /* BUSCAR ESTADO DE AGENDAMIENTO */
    /* ================================================= */

    public function buscar()
    {
        /* Obtener un estado del catálogo mediante su identificador */
        $sql = "SELECT
                    id_estado_agendamiento,
                    nombre_estado,
                    descripcion,
                    estado_registro
                FROM estados_agendamientos
                WHERE id_estado_agendamiento = :id_estado_agendamiento
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_estado_agendamiento', $this->idEstadoAgendamiento);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* MODIFICAR ESTADO DE AGENDAMIENTO */
    /* ================================================= */

    public function modificar()
    {
        /* Actualizar el nombre y la descripción del estado */
        $sql = "UPDATE estados_agendamientos SET
                    nombre_estado = :nombre_estado,
                    descripcion = :descripcion
                WHERE id_estado_agendamiento = :id_estado_agendamiento";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_estado_agendamiento', $this->idEstadoAgendamiento);
        $stmt->bindParam(':nombre_estado', $this->nombreEstado);
        $stmt->bindParam(':descripcion', $this->descripcion);

        return $stmt->execute();
    }


    /* ================================================= */
    /* DESACTIVAR ESTADO DE AGENDAMIENTO */
    /* ================================================= */

    public function eliminar()
    {
        /* Marcar el registro del catálogo como inactivo para conservarlo */
        $sql = "UPDATE estados_agendamientos
                SET estado_registro = 'INACTIVO'
                WHERE id_estado_agendamiento = :id_estado_agendamiento";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_estado_agendamiento', $this->idEstadoAgendamiento);

        return $stmt->execute();
    }
}