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

    public function __construct(?PDO $conexion = null)
    {
        $this->conexionMD = $conexion ?? $this->getConnection();
    }

    /* ================================================= */
    /* CONSULTAR ROLES */
    /* ================================================= */

    public function consultar(mixed $estado = 'ACTIVO'): array
    {
        $estado = $this->validarFiltroEstado($estado);

        /* Obtener los roles del estado seleccionado */
        $sql = "SELECT
                    id_rol,
                    nombre_rol,
                    descripcion,
                    estado_rol
                FROM roles";

        if ($estado !== 'TODOS') {
            $sql .= " WHERE estado_rol = :estado_rol";
        }

        $sql .= " ORDER BY nombre_rol ASC";

        $stmt = $this->conexionMD->prepare($sql);

        if ($estado !== 'TODOS') {
            $stmt->bindValue(':estado_rol', $estado, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ================================================= */
    /* REGISTRAR ROL */
    /* ================================================= */

    public function registrar(): bool
    {
        if (!$this->validarDatosCatalogo(false)) return false;
        if (!$this->comprobarNombreDisponible()) return false;

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

        try {
            $this->conexionMD->beginTransaction();

            /* Comprobar que el rol siga activo antes de modificarlo */
            $sqlBuscar = "SELECT id_rol
                          FROM roles
                          WHERE id_rol = :id_rol
                            AND estado_rol = 'ACTIVO'
                          LIMIT 1
                          FOR UPDATE";

            $stmtBuscar = $this->conexionMD->prepare($sqlBuscar);
            $stmtBuscar->bindValue(':id_rol', $this->idRol, PDO::PARAM_INT);
            $stmtBuscar->execute();

            if ($stmtBuscar->fetchColumn() === false) {
                $this->erroresValidacion['id_rol'] = 'El rol está inactivo o no se encuentra disponible. Actualice la lista.';
                $this->conexionMD->rollBack();
                return false;
            }

            if (!$this->comprobarNombreDisponible($this->idRol)) {
                $this->conexionMD->rollBack();
                return false;
            }

            /* Actualizar el nombre y la descripción conservando el identificador */
            $sql = "UPDATE roles SET
                        nombre_rol = :nombre_rol,
                        descripcion = :descripcion
                    WHERE id_rol = :id_rol
                      AND estado_rol = 'ACTIVO'";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':id_rol', $this->idRol, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_rol', $this->nombreRol);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->execute();

            if (!$this->conexionMD->commit()) {
                throw new \RuntimeException('No fue posible confirmar la modificación del rol.');
            }

            return true;

        } catch (\Throwable $e) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }
            throw $e;
        }
    }

    /* ================================================= */
    /* DESACTIVAR ROL */
    /* ================================================= */

    public function eliminar(): bool
    {
        return $this->cambiarEstadoRol('ACTIVO', 'INACTIVO');
    }

    /* ================================================= */
    /* REACTIVAR ROL */
    /* ================================================= */

    public function reactivar(): bool
    {
        return $this->cambiarEstadoRol('INACTIVO', 'ACTIVO');
    }

    /* ================================================= */
    /* CAMBIAR EL ESTADO SIN ALTERAR LOS USUARIOS ASOCIADOS */
    /* ================================================= */

    private function cambiarEstadoRol(string $estadoActual, string $estadoNuevo): bool
    {
        if (!$this->validarIdentificacion()) return false;

        /* Cambiar únicamente el estado del rol seleccionado */
        $sql = "UPDATE roles
                SET estado_rol = :estado_nuevo
                WHERE id_rol = :id_rol
                  AND estado_rol = :estado_actual";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_rol', $this->idRol, PDO::PARAM_INT);
        $stmt->bindValue(':estado_actual', $estadoActual, PDO::PARAM_STR);
        $stmt->bindValue(':estado_nuevo', $estadoNuevo, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() !== 1) {
            throw new \DomainException('El estado del rol cambió o el registro no se encuentra disponible. Actualice la lista.');
        }

        return true;
    }

    /* ================================================= */
    /* COMPROBAR QUE EL NOMBRE NO PERTENEZCA A OTRO ROL */
    /* ================================================= */

    private function comprobarNombreDisponible(?int $idExcluir = null): bool
    {
        $sql = "SELECT estado_rol
                FROM roles
                WHERE nombre_rol = :nombre_rol";

        if ($idExcluir !== null) {
            $sql .= " AND id_rol <> :id_rol";
        }

        $sql .= " LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':nombre_rol', $this->nombreRol);

        if ($idExcluir !== null) {
            $stmt->bindValue(':id_rol', $idExcluir, PDO::PARAM_INT);
        }

        $stmt->execute();
        $estadoExistente = $stmt->fetchColumn();

        if ($estadoExistente === false) return true;

        $this->erroresValidacion['nombre_rol'] = $estadoExistente === 'INACTIVO'
            ? 'Ya existe un rol inactivo con ese nombre. Reactívelo desde Inactivos.'
            : 'Ya existe un rol con ese nombre.';

        return false;
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