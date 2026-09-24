<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;

class RolM extends Database implements CrudInterface
{
    /* Atributo de conexión del modelo */
    private $conexionMD;

    /* Atributos de la tabla roles */
    private $idRol;
    private $nombreRol;
    private $descripcion;
    private $estadoRol;


    /* Setters: asignan valores a los atributos privados */
    public function set_idRol($idRol) {$this->idRol = $idRol;}
    public function set_nombreRol($nombreRol) {$this->nombreRol = $nombreRol;}
    public function set_descripcion($descripcion) {$this->descripcion = $descripcion;}
    public function set_estadoRol($estadoRol) {$this->estadoRol = $estadoRol;}


    /* Constructor: obtiene la conexión heredada de Database */
    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* Consultar: obtiene los roles activos */
    public function consultar()
    {
        $sql = "SELECT
                    id_rol,
                    nombre_rol,
                    descripcion,
                    estado_rol
                FROM roles
                WHERE estado_rol = 'ACTIVO'
                ORDER BY nombre_rol ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* Registrar: inserta un nuevo rol */
    public function registrar()
    {
        $sql = "INSERT INTO roles (
                    nombre_rol,
                    descripcion,
                    estado_rol
                ) VALUES (
                    :nombre_rol,
                    :descripcion,
                    'ACTIVO'
                )";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':nombre_rol', $this->nombreRol);
        $stmt->bindParam(':descripcion', $this->descripcion);

        return $stmt->execute();
    }


    /* Buscar: obtiene un rol por su identificador */
    public function buscar()
    {
        $sql = "SELECT
                    id_rol,
                    nombre_rol,
                    descripcion,
                    estado_rol
                FROM roles
                WHERE id_rol = :id_rol
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_rol', $this->idRol);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /* Modificar: actualiza los datos de un rol */
    public function modificar()
    {
        $sql = "UPDATE roles
                SET
                    nombre_rol = :nombre_rol,
                    descripcion = :descripcion
                WHERE id_rol = :id_rol";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':nombre_rol', $this->nombreRol);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':id_rol', $this->idRol);

        return $stmt->execute();
    }


    /* Eliminar: cambia el estado del rol a INACTIVO */
    public function eliminar()
    {
        $sql = "UPDATE roles
                SET estado_rol = 'INACTIVO'
                WHERE id_rol = :id_rol";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_rol', $this->idRol);

        return $stmt->execute();
    }
}