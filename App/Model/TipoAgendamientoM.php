<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;

class TipoAgendamientoM extends Database implements CrudInterface
{
    /* Atributo de conexión del modelo */
    private $conexionMD;

    /* Atributos de la tabla tipos_agendamientos */
    private $idTipoAgendamiento;
    private $nombreTipoAgendamiento;
    private $descripcion;
    private $estadoTipoAgendamiento;

    /* Setters: asignan valores a los atributos privados */
    public function set_idTipoAgendamiento($idTipoAgendamiento) {$this->idTipoAgendamiento = $idTipoAgendamiento;}
    public function set_nombreTipoAgendamiento($nombreTipoAgendamiento) {$this->nombreTipoAgendamiento = $nombreTipoAgendamiento;}
    public function set_descripcion($descripcion) {$this->descripcion = $descripcion;}
    public function set_estadoTipoAgendamiento($estadoTipoAgendamiento) {$this->estadoTipoAgendamiento = $estadoTipoAgendamiento;}

    /* Constructor: obtiene la conexión heredada de Database */
    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }

    /* Consultar: obtiene los tipos de agendamiento activos */
    public function consultar()
    {
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

    /* Registrar: inserta un nuevo tipo de agendamiento */
    public function registrar()
    {
        $sql = "INSERT INTO tipos_agendamientos
                    (nombre_tipo_agendamiento, descripcion, estado_tipo_agendamiento)
                VALUES
                    (:nombre_tipo_agendamiento, :descripcion, 'ACTIVO')";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':nombre_tipo_agendamiento', $this->nombreTipoAgendamiento);
        $stmt->bindParam(':descripcion', $this->descripcion);

        return $stmt->execute();
    }

    /* Buscar: obtiene un tipo de agendamiento por su identificador */
    public function buscar()
    {
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

    /* Modificar: actualiza un tipo de agendamiento */
    public function modificar()
    {
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

    /* Eliminar: cambia el estado del tipo de agendamiento a INACTIVO */
    public function eliminar()
    {
        $sql = "UPDATE tipos_agendamientos
                SET estado_tipo_agendamiento = 'INACTIVO'
                WHERE id_tipo_agendamiento = :id_tipo_agendamiento";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':id_tipo_agendamiento', $this->idTipoAgendamiento);

        return $stmt->execute();
    }
}