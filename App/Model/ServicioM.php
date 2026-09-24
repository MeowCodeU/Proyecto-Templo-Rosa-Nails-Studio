<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;

class ServicioM extends Database implements CrudInterface
{
    /* Atributo de conexión del modelo */
    private $conexionMD;

    /* Atributos de la tabla servicios */
    private $idServicio;
    private $nombreServicio;
    private $descripcion;
    private $precio;
    private $duracionEstimada;
    private $estadoServicio;


    /* Setters: asignan valores a los atributos privados */
    public function set_idServicio($idServicio) {$this->idServicio = $idServicio;}
    public function set_nombreServicio($nombreServicio) {$this->nombreServicio = $nombreServicio;}
    public function set_descripcion($descripcion) {$this->descripcion = $descripcion;}
    public function set_precio($precio) {$this->precio = $precio;}
    public function set_duracionEstimada($duracionEstimada) {$this->duracionEstimada = $duracionEstimada;}
    public function set_estadoServicio($estadoServicio) {$this->estadoServicio = $estadoServicio;}


    /* Constructor: obtiene la conexión heredada de Database */
    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* Consultar: obtiene los servicios activos */
    public function consultar()
    {
        $sql = "SELECT
                    id_servicio,
                    nombre_servicio,
                    descripcion,
                    precio,
                    duracion_estimada,
                    estado_servicio
                FROM servicios
                WHERE estado_servicio = 'ACTIVO'
                ORDER BY nombre_servicio ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* Registrar: inserta un nuevo servicio */
    public function registrar()
    {
        $sql = "INSERT INTO servicios (
                    nombre_servicio,
                    descripcion,
                    precio,
                    duracion_estimada,
                    estado_servicio
                ) VALUES (
                    :nombre_servicio,
                    :descripcion,
                    :precio,
                    :duracion_estimada,
                    'ACTIVO'
                )";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':nombre_servicio', $this->nombreServicio);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':precio', $this->precio);
        $stmt->bindParam(':duracion_estimada', $this->duracionEstimada);

        return $stmt->execute();
    }


    /* Buscar: obtiene un servicio por su identificador */
    public function buscar()
    {
        $sql = "SELECT
                    id_servicio,
                    nombre_servicio,
                    descripcion,
                    precio,
                    duracion_estimada,
                    estado_servicio
                FROM servicios
                WHERE id_servicio = :id_servicio
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_servicio', $this->idServicio);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /* Modificar: actualiza los datos de un servicio */
    public function modificar()
    {
        $sql = "UPDATE servicios
                SET
                    nombre_servicio = :nombre_servicio,
                    descripcion = :descripcion,
                    precio = :precio,
                    duracion_estimada = :duracion_estimada
                WHERE id_servicio = :id_servicio";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':nombre_servicio', $this->nombreServicio);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':precio', $this->precio);
        $stmt->bindParam(':duracion_estimada', $this->duracionEstimada);
        $stmt->bindParam(':id_servicio', $this->idServicio);

        return $stmt->execute();
    }


    /* Eliminar: cambia el estado del servicio a INACTIVO */
    public function eliminar()
    {
        $sql = "UPDATE servicios
                SET estado_servicio = 'INACTIVO'
                WHERE id_servicio = :id_servicio";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_servicio', $this->idServicio);

        return $stmt->execute();
    }
}