<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use App\Traits\ValidacionConfiguracionTrait;
use PDO;

/* ===================================================== */
/* MODELO DE ROLES */
/* ===================================================== */

class RolM extends Database implements CrudInterface
{
    use ValidacionConfiguracionTrait;

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

    public function consultar(): array
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

    public function registrar(): bool
    {
        if (!$this->validarDatosCatalogo(false)) return false;

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
        if (!$this->validarIdentificacion()) return false;

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

    public function modificar(): bool
    {
        if (!$this->validarDatosCatalogo(true)) return false;

        /* Actualizar el nombre y la descripción del rol */
        $sql = "UPDATE roles SET
                    nombre_rol = :nombre_rol,
                    descripcion = :descripcion
                WHERE id_rol = :id_rol";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_rol', $this->idRol);
        $stmt->bindParam(':nombre_rol', $this->nombreRol);
        $stmt->bindParam(':descripcion', $this->descripcion);

        return $stmt->execute();
    }

    /* ================================================= */
    /* DESACTIVAR ROL */
    /* ================================================= */

    public function eliminar(): bool
    {
        if (!$this->validarIdentificacion()) return false;

        /* Marcar el rol como inactivo para conservar su registro */
        $sql = "UPDATE roles
                SET estado_rol = 'INACTIVO'
                WHERE id_rol = :id_rol";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':id_rol', $this->idRol);

        return $stmt->execute();
    }

    /* ================================================= */
    /* VALIDAR LOS DATOS ANTES DE CONSULTAR O GUARDAR */
    /* ================================================= */

    public function validarIdentificacion(): bool
    {
        $id = $this->validarIdConfiguracion($this->idRol, 'id_rol');
        if ($id === null) return false;

        $this->idRol = $id;
        return true;
    }

    private function validarDatosCatalogo(bool $esModificacion): bool
    {
        $datos = $this->validarDatosConfiguracion([
            'id_rol' => $this->idRol,
            'nombre_rol' => $this->nombreRol,
            'descripcion' => $this->descripcion
        ], 'nombre_rol', 40, 150, $esModificacion ? 'id_rol' : null);

        if ($this->obtenerErroresValidacion() !== []) return false;

        $this->nombreRol = $datos['nombre_rol'];
        $this->descripcion = $datos['descripcion'];
        if ($esModificacion) $this->idRol = $datos['id_rol'];

        return true;
    }
}