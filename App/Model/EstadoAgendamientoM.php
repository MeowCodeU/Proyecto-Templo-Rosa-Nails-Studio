<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use App\Traits\ValidacionConfiguracionTrait;
use PDO;

/* ===================================================== */
/* MODELO DE ESTADOS DE AGENDAMIENTO */
/* ===================================================== */

class EstadoAgendamientoM extends Database implements CrudInterface
{
    use ValidacionConfiguracionTrait;

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

    public function __construct(?PDO $conexion = null)
    {
        $this->conexionMD = $conexion ?? $this->getConnection();
    }

    /* ================================================= */
    /* CONSULTAR ESTADOS DE AGENDAMIENTO */
    /* ================================================= */

    public function consultar(mixed $estado = 'ACTIVO'): array
    {
        $estado = $this->validarFiltroEstado($estado);

        /* Obtener los estados del catálogo según el filtro seleccionado */
        $sql = "SELECT
                    id_estado_agendamiento,
                    nombre_estado,
                    descripcion,
                    estado_registro
                FROM estados_agendamientos";

        if ($estado !== 'TODOS') {
            $sql .= " WHERE estado_registro = :estado_registro";
        }

        $sql .= " ORDER BY nombre_estado ASC";

        $stmt = $this->conexionMD->prepare($sql);

        if ($estado !== 'TODOS') {
            $stmt->bindValue(':estado_registro', $estado, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ================================================= */
    /* REGISTRAR ESTADO DE AGENDAMIENTO */
    /* ================================================= */

    public function registrar(): bool
    {
        if (!$this->validarDatosCatalogo(false)) return false;
        if (!$this->comprobarNombreDisponible()) return false;

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
        if (!$this->validarIdentificacion()) return false;

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

    public function modificar(): bool
    {
        if (!$this->validarDatosCatalogo(true)) return false;

        try {
            $this->conexionMD->beginTransaction();

            /* Comprobar que el estado de agendamiento siga activo antes de modificarlo */
            $sqlBuscar = "SELECT id_estado_agendamiento
                          FROM estados_agendamientos
                          WHERE id_estado_agendamiento = :id_estado_agendamiento
                            AND estado_registro = 'ACTIVO'
                          LIMIT 1
                          FOR UPDATE";

            $stmtBuscar = $this->conexionMD->prepare($sqlBuscar);
            $stmtBuscar->bindValue(':id_estado_agendamiento', $this->idEstadoAgendamiento, PDO::PARAM_INT);
            $stmtBuscar->execute();

            if ($stmtBuscar->fetchColumn() === false) {
                $this->erroresValidacion['id_estado_agendamiento'] = 'El estado de agendamiento está inactivo o no se encuentra disponible. Actualice la lista.';
                $this->conexionMD->rollBack();
                return false;
            }

            if (!$this->comprobarNombreDisponible($this->idEstadoAgendamiento)) {
                $this->conexionMD->rollBack();
                return false;
            }

            /* Actualizar el nombre y la descripción conservando el identificador */
            $sql = "UPDATE estados_agendamientos SET
                        nombre_estado = :nombre_estado,
                        descripcion = :descripcion
                    WHERE id_estado_agendamiento = :id_estado_agendamiento
                      AND estado_registro = 'ACTIVO'";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':id_estado_agendamiento', $this->idEstadoAgendamiento, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_estado', $this->nombreEstado);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->execute();

            if (!$this->conexionMD->commit()) {
                throw new \RuntimeException('No fue posible confirmar la modificación del estado de agendamiento.');
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
    /* DESACTIVAR ESTADO DE AGENDAMIENTO */
    /* ================================================= */

    public function eliminar(): bool
    {
        return $this->cambiarEstadoRegistro('ACTIVO', 'INACTIVO');
    }

    /* ================================================= */
    /* REACTIVAR ESTADO DE AGENDAMIENTO */
    /* ================================================= */

    public function reactivar(): bool
    {
        return $this->cambiarEstadoRegistro('INACTIVO', 'ACTIVO');
    }

    /* ================================================= */
    /* CAMBIAR LA DISPONIBILIDAD DEL ESTADO EN EL CATÁLOGO */
    /* ================================================= */

    private function cambiarEstadoRegistro(string $estadoActual, string $estadoNuevo): bool
    {
        if (!$this->validarIdentificacion()) return false;

        /* Cambiar únicamente la disponibilidad del registro en el catálogo */
        $sql = "UPDATE estados_agendamientos
                SET estado_registro = :estado_nuevo
                WHERE id_estado_agendamiento = :id_estado_agendamiento
                  AND estado_registro = :estado_actual";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_estado_agendamiento', $this->idEstadoAgendamiento, PDO::PARAM_INT);
        $stmt->bindValue(':estado_actual', $estadoActual, PDO::PARAM_STR);
        $stmt->bindValue(':estado_nuevo', $estadoNuevo, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() !== 1) {
            throw new \DomainException('La disponibilidad del estado cambió o el registro no se encuentra disponible. Actualice la lista.');
        }

        return true;
    }

    /* ================================================= */
    /* COMPROBAR QUE EL NOMBRE NO PERTENEZCA A OTRO ESTADO DE AGENDAMIENTO */
    /* ================================================= */

    private function comprobarNombreDisponible(?int $idExcluir = null): bool
    {
        $sql = "SELECT estado_registro
                FROM estados_agendamientos
                WHERE nombre_estado = :nombre_estado";

        if ($idExcluir !== null) {
            $sql .= " AND id_estado_agendamiento <> :id_estado_agendamiento";
        }

        $sql .= " LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':nombre_estado', $this->nombreEstado);

        if ($idExcluir !== null) {
            $stmt->bindValue(':id_estado_agendamiento', $idExcluir, PDO::PARAM_INT);
        }

        $stmt->execute();
        $estadoExistente = $stmt->fetchColumn();

        if ($estadoExistente === false) return true;

        $this->erroresValidacion['nombre_estado'] = $estadoExistente === 'INACTIVO'
            ? 'Ya existe un estado de agendamiento inactivo con ese nombre. Reactívelo desde Inactivos.'
            : 'Ya existe un estado de agendamiento con ese nombre.';

        return false;
    }

    /* ================================================= */
    /* VALIDAR LOS DATOS ANTES DE CONSULTAR O GUARDAR */
    /* ================================================= */

    public function validarIdentificacion(): bool
    {
        $id = $this->validarIdConfiguracion($this->idEstadoAgendamiento, 'id_estado_agendamiento');
        if ($id === null) return false;

        $this->idEstadoAgendamiento = $id;
        return true;
    }

    private function validarDatosCatalogo(bool $esModificacion): bool
    {
        $datos = $this->validarDatosConfiguracion([
            'id_estado_agendamiento' => $this->idEstadoAgendamiento,
            'nombre_estado' => $this->nombreEstado,
            'descripcion' => $this->descripcion
        ], 'nombre_estado', 50, 150, $esModificacion ? 'id_estado_agendamiento' : null);

        if ($this->obtenerErroresValidacion() !== []) return false;

        $this->nombreEstado = $datos['nombre_estado'];
        $this->descripcion = $datos['descripcion'];
        if ($esModificacion) $this->idEstadoAgendamiento = $datos['id_estado_agendamiento'];

        return true;
    }
}