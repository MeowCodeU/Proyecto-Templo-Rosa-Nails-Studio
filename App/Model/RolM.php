<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;


/* ===================================================== */
/* MODELO DE ROLES */
/* ===================================================== */

class RolM extends Database implements CrudInterface
{
    /* ================================================= */
    /* CONEXIÓN Y ATRIBUTOS */
    /* ================================================= */

    /* Conexión con la base de datos */
    private $conexionMD;

    /* Atributos de la tabla roles */
    private $idRol;
    private $nombreRol;
    private $descripcion;
    private $estadoRol;


    /* ================================================= */
    /* SETTERS */
    /* ================================================= */

    public function set_idRol($idRol) {$this->idRol = $idRol;}
    public function set_nombreRol($nombreRol) {$this->nombreRol = $nombreRol;}
    public function set_descripcion($descripcion) {$this->descripcion = $descripcion;}
    public function set_estadoRol($estadoRol) {$this->estadoRol = $estadoRol;}


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* ================================================= */
    /* CONSULTAR ROLES */
    /* ================================================= */

    public function consultar()
    {
        /* Obtener los roles activos ordenados por su nombre */
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


    /* ================================================= */
    /* REGISTRAR ROL */
    /* ================================================= */

    public function registrar()
    {
        /* Registrar un nuevo rol con estado ACTIVO */
        $sql = "INSERT INTO roles
                    (nombre_rol, descripcion, estado_rol)
                VALUES
                    (:nombre_rol, :descripcion, 'ACTIVO')";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':nombre_rol', $this->nombreRol);
        $stmt->bindParam(':descripcion', $this->descripcion);

        return $stmt->execute();
    }


    /* ================================================= */
    /* BUSCAR ROL */
    /* ================================================= */

    public function buscar()
    {
        /* Obtener un rol mediante su identificador */
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


    /* ================================================= */
    /* MODIFICAR ROL */
    /* ================================================= */

    public function modificar()
    {
        /* Actualizar el nombre y la descripción del rol */
        $sql = "UPDATE roles SET
                    nombre_rol = :nombre_rol,
                    descripcion = :descripcion
                WHERE id_rol = :id_rol";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':nombre_rol', $this->nombreRol);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':id_rol', $this->idRol);

        return $stmt->execute();
    }


    /* ================================================= */
    /* DESACTIVAR ROL */
    /* ================================================= */

    public function eliminar()
    {
        /* Marcar el rol como inactivo para conservar su registro */
        $sql = "UPDATE roles
                SET estado_rol = 'INACTIVO'
                WHERE id_rol = :id_rol";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':id_rol', $this->idRol);

        return $stmt->execute();
    }
}