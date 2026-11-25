<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use App\Traits\ValidacionConfiguracionTrait;
use PDO;

/* ===================================================== */
/* MODELO DE TIPOS DE AGENDAMIENTO */
/* ===================================================== */

class TipoAgendamientoM extends Database implements CrudInterface
{
    use ValidacionConfiguracionTrait;

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

    public function __construct(?PDO $conexion = null)
    {
        $this->conexionMD = $conexion ?? $this->getConnection();
    }

    /* ================================================= */
    /* CONSULTAR TIPOS DE AGENDAMIENTO */
    /* ================================================= */

    public function consultar(mixed $estado = 'ACTIVO'): array
    {
        $estado = $this->validarFiltroEstado($estado);

        /* Obtener los tipos del catálogo según el filtro seleccionado */
        $sql = "SELECT
                    id_tipo_agendamiento,
                    nombre_tipo_agendamiento,
                    descripcion,
                    estado_tipo_agendamiento
                FROM tipos_agendamientos";

        if ($estado !== 'TODOS') {
            $sql .= " WHERE estado_tipo_agendamiento = :estado_tipo_agendamiento";
        }

        $sql .= " ORDER BY nombre_tipo_agendamiento ASC";

        $stmt = $this->conexionMD->prepare($sql);

        if ($estado !== 'TODOS') {
            $stmt->bindValue(':estado_tipo_agendamiento', $estado, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ================================================= */
    /* REGISTRAR TIPO DE AGENDAMIENTO */
    /* ================================================= */

    public function registrar(): bool
    {
        if (!$this->validarDatosCatalogo(false)) return false;
        if (!$this->comprobarNombreDisponible()) return false;

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
        if (!$this->validarIdentificacion()) return false;

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

    public function modificar(): bool
    {
        if (!$this->validarDatosCatalogo(true)) return false;

        try {
            $this->conexionMD->beginTransaction();

            /* Comprobar que el tipo de agendamiento siga activo antes de modificarlo */
            $sqlBuscar = "SELECT id_tipo_agendamiento
                          FROM tipos_agendamientos
                          WHERE id_tipo_agendamiento = :id_tipo_agendamiento
                            AND estado_tipo_agendamiento = 'ACTIVO'
                          LIMIT 1
                          FOR UPDATE";

            $stmtBuscar = $this->conexionMD->prepare($sqlBuscar);
            $stmtBuscar->bindValue(':id_tipo_agendamiento', $this->idTipoAgendamiento, PDO::PARAM_INT);
            $stmtBuscar->execute();

            if ($stmtBuscar->fetchColumn() === false) {
                $this->erroresValidacion['id_tipo_agendamiento'] = 'El tipo de agendamiento está inactivo o no se encuentra disponible. Actualice la lista.';
                $this->conexionMD->rollBack();
                return false;
            }

            if (!$this->comprobarNombreDisponible($this->idTipoAgendamiento)) {
                $this->conexionMD->rollBack();
                return false;
            }

            /* Actualizar el nombre y la descripción conservando el identificador */
            $sql = "UPDATE tipos_agendamientos SET
                        nombre_tipo_agendamiento = :nombre_tipo_agendamiento,
                        descripcion = :descripcion
                    WHERE id_tipo_agendamiento = :id_tipo_agendamiento
                      AND estado_tipo_agendamiento = 'ACTIVO'";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':id_tipo_agendamiento', $this->idTipoAgendamiento, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_tipo_agendamiento', $this->nombreTipoAgendamiento);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->execute();

            if (!$this->conexionMD->commit()) {
                throw new \RuntimeException('No fue posible confirmar la modificación del tipo de agendamiento.');
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
    /* DESACTIVAR TIPO DE AGENDAMIENTO */
    /* ================================================= */

    public function eliminar(): bool
    {
        return $this->cambiarEstadoRegistro('ACTIVO', 'INACTIVO');
    }

    /* ================================================= */
    /* REACTIVAR TIPO DE AGENDAMIENTO */
    /* ================================================= */

    public function reactivar(): bool
    {
        return $this->cambiarEstadoRegistro('INACTIVO', 'ACTIVO');
    }

    /* ================================================= */
    /* CAMBIAR LA DISPONIBILIDAD DEL TIPO EN EL CATÁLOGO */
    /* ================================================= */

    private function cambiarEstadoRegistro(string $estadoActual, string $estadoNuevo): bool
    {
        if (!$this->validarIdentificacion()) return false;

        /* Cambiar únicamente la disponibilidad del registro en el catálogo */
        $sql = "UPDATE tipos_agendamientos
                SET estado_tipo_agendamiento = :estado_nuevo
                WHERE id_tipo_agendamiento = :id_tipo_agendamiento
                  AND estado_tipo_agendamiento = :estado_actual";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_tipo_agendamiento', $this->idTipoAgendamiento, PDO::PARAM_INT);
        $stmt->bindValue(':estado_actual', $estadoActual, PDO::PARAM_STR);
        $stmt->bindValue(':estado_nuevo', $estadoNuevo, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() !== 1) {
            throw new \DomainException('La disponibilidad del tipo cambió o el registro no se encuentra disponible. Actualice la lista.');
        }

        return true;
    }

    /* ================================================= */
    /* COMPROBAR QUE EL NOMBRE NO PERTENEZCA A OTRO TIPO DE AGENDAMIENTO */
    /* ================================================= */

    private function comprobarNombreDisponible(?int $idExcluir = null): bool
    {
        $sql = "SELECT estado_tipo_agendamiento
                FROM tipos_agendamientos
                WHERE nombre_tipo_agendamiento = :nombre_tipo_agendamiento";

        if ($idExcluir !== null) {
            $sql .= " AND id_tipo_agendamiento <> :id_tipo_agendamiento";
        }

        $sql .= " LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':nombre_tipo_agendamiento', $this->nombreTipoAgendamiento);

        if ($idExcluir !== null) {
            $stmt->bindValue(':id_tipo_agendamiento', $idExcluir, PDO::PARAM_INT);
        }

        $stmt->execute();
        $estadoExistente = $stmt->fetchColumn();

        if ($estadoExistente === false) return true;

        $this->erroresValidacion['nombre_tipo_agendamiento'] = $estadoExistente === 'INACTIVO'
            ? 'Ya existe un tipo de agendamiento inactivo con ese nombre. Reactívelo desde Inactivos.'
            : 'Ya existe un tipo de agendamiento con ese nombre.';

        return false;
    }

    /* ================================================= */
    /* VALIDAR LOS DATOS ANTES DE CONSULTAR O GUARDAR */
    /* ================================================= */

    public function validarIdentificacion(): bool
    {
        $id = $this->validarIdConfiguracion($this->idTipoAgendamiento, 'id_tipo_agendamiento');
        if ($id === null) return false;

        $this->idTipoAgendamiento = $id;
        return true;
    }

    private function validarDatosCatalogo(bool $esModificacion): bool
    {
        $datos = $this->validarDatosConfiguracion([
            'id_tipo_agendamiento' => $this->idTipoAgendamiento,
            'nombre_tipo_agendamiento' => $this->nombreTipoAgendamiento,
            'descripcion' => $this->descripcion
        ], 'nombre_tipo_agendamiento', 60, 150, $esModificacion ? 'id_tipo_agendamiento' : null);

        if ($this->obtenerErroresValidacion() !== []) return false;

        $this->nombreTipoAgendamiento = $datos['nombre_tipo_agendamiento'];
        $this->descripcion = $datos['descripcion'];
        if ($esModificacion) $this->idTipoAgendamiento = $datos['id_tipo_agendamiento'];

        return true;
    }
}