<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;

class EstadoAgendamientoM extends Database implements CrudInterface
{
    /* Atributo de conexión del modelo */
    private $conexionMD;

    /* Atributos de la tabla estados_agendamientos */
    private $idEstadoAgendamiento;
    private $nombreEstado;
    private $descripcion;
    private $estadoRegistro;

    /* Setters: asignan valores a los atributos privados */
    public function set_idEstadoAgendamiento($idEstadoAgendamiento) {$this->idEstadoAgendamiento = $idEstadoAgendamiento;}
    public function set_nombreEstado($nombreEstado) {$this->nombreEstado = $nombreEstado;}
    public function set_descripcion($descripcion) {$this->descripcion = $descripcion;}
    public function set_estadoRegistro($estadoRegistro) {$this->estadoRegistro = $estadoRegistro;}

    /* Constructor: obtiene la conexión heredada de Database */
    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }

    /* Consultar: obtiene los estados de agendamiento activos */
    public function consultar()
    {
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

    /* Registrar: inserta un nuevo estado de agendamiento */
    public function registrar()
    {
        $sql = "INSERT INTO estados_agendamientos
                    (nombre_estado, descripcion, estado_registro)
                VALUES
                    (:nombre_estado, :descripcion, 'ACTIVO')";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':nombre_estado', $this->nombreEstado);
        $stmt->bindParam(':descripcion', $this->descripcion);

        return $stmt->execute();
    }

    /* Buscar: obtiene un estado de agendamiento por su identificador */
    public function buscar()
    {
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

    /* Modificar: actualiza un estado de agendamiento */
    public function modificar()
    {
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

    /* Eliminar: cambia el estado del registro a INACTIVO */
    public function eliminar()
    {
        $sql = "UPDATE estados_agendamientos
                SET estado_registro = 'INACTIVO'
                WHERE id_estado_agendamiento = :id_estado_agendamiento";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':id_estado_agendamiento', $this->idEstadoAgendamiento);

        return $stmt->execute();
    }
}



