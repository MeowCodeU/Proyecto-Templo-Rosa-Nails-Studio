<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;


/* ===================================================== */
/* MODELO DE TIPOS DE AGENDAMIENTO */
/* ===================================================== */

class TipoAgendamientoM extends Database implements CrudInterface
{
    /* ================================================= */
    /* CONEXIÓN Y ATRIBUTOS */
    /* ================================================= */

    /* Conexión con la base de datos */
    private $conexionMD;

    /* Atributos de la tabla tipos_agendamientos */
    private $idTipoAgendamiento;
    private $nombreTipoAgendamiento;
    private $descripcion;
    private $estadoTipoAgendamiento;


    /* ================================================= */
    /* SETTERS */
    /* ================================================= */

    public function set_idTipoAgendamiento($idTipoAgendamiento) {$this->idTipoAgendamiento = $idTipoAgendamiento;}
    public function set_nombreTipoAgendamiento($nombreTipoAgendamiento) {$this->nombreTipoAgendamiento = $nombreTipoAgendamiento;}
    public function set_descripcion($descripcion) {$this->descripcion = $descripcion;}
    public function set_estadoTipoAgendamiento($estadoTipoAgendamiento) {$this->estadoTipoAgendamiento = $estadoTipoAgendamiento;}


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* ================================================= */
    /* CONSULTAR TIPOS DE AGENDAMIENTO */
    /* ================================================= */

    public function consultar()
    {
        /* Obtener los tipos activos ordenados por su nombre */
        $sql = "SELECT
                    id_tipo_agendamiento,
                    nombre_tipo_agendamiento,
                    descripcion,
                    estado_tipo_agendamiento
                FROM tipos_agendamientos
                WHERE estado_tipo_agendamiento = 'ACTIVO'
                ORDER BY nombre_tipo_agendamiento ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* REGISTRAR TIPO DE AGENDAMIENTO */
    /* ================================================= */

    public function registrar()
    {
        /* Registrar un nuevo tipo activo en el catálogo */
        $sql = "INSERT INTO tipos_agendamientos
                    (nombre_tipo_agendamiento, descripcion, estado_tipo_agendamiento)
                VALUES
                    (:nombre_tipo_agendamiento, :descripcion, 'ACTIVO')";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':nombre_tipo_agendamiento', $this->nombreTipoAgendamiento);
        $stmt->bindParam(':descripcion', $this->descripcion);

        return $stmt->execute();
    }


    /* ================================================= */
    /* BUSCAR TIPO DE AGENDAMIENTO */
    /* ================================================= */

    public function buscar()
    {
        /* Obtener un tipo del catálogo mediante su identificador */
        $sql = "SELECT
                    id_tipo_agendamiento,
                    nombre_tipo_agendamiento,
                    descripcion,
                    estado_tipo_agendamiento
                FROM tipos_agendamientos
                WHERE id_tipo_agendamiento = :id_tipo_agendamiento
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':id_tipo_agendamiento', $this->idTipoAgendamiento);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* MODIFICAR TIPO DE AGENDAMIENTO */
    /* ================================================= */

    public function modificar()
    {
        /* Actualizar el nombre y la descripción del tipo de agendamiento */
        $sql = "UPDATE tipos_agendamientos SET
                    nombre_tipo_agendamiento = :nombre_tipo_agendamiento,
                    descripcion = :descripcion
                WHERE id_tipo_agendamiento = :id_tipo_agendamiento";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_tipo_agendamiento', $this->idTipoAgendamiento);
        $stmt->bindParam(':nombre_tipo_agendamiento', $this->nombreTipoAgendamiento);
        $stmt->bindParam(':descripcion', $this->descripcion);

        return $stmt->execute();
    }


    /* ================================================= */
    /* DESACTIVAR TIPO DE AGENDAMIENTO */
    /* ================================================= */

    public function eliminar()
    {
        /* Marcar el tipo del catálogo como inactivo para conservar su registro */
        $sql = "UPDATE tipos_agendamientos
                SET estado_tipo_agendamiento = 'INACTIVO'
                WHERE id_tipo_agendamiento = :id_tipo_agendamiento";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':id_tipo_agendamiento', $this->idTipoAgendamiento);

        return $stmt->execute();
    }
}