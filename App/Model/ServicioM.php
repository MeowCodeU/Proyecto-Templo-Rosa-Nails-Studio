<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use App\Traits\ValidacionConfiguracionTrait;
use PDO;

/* ===================================================== */
/* MODELO DE SERVICIOS */
/* ===================================================== */

class ServicioM extends Database implements CrudInterface
{
    use ValidacionConfiguracionTrait;

    /* ================================================= */
    /* CONEXIÓN Y ATRIBUTOS */
    /* ================================================= */

    /* Conexión con la base de datos */
    private $conexionMD;

    /* Atributos de la tabla servicios */
    private $idServicio;
    private $nombreServicio;
    private $descripcion;
    private $precio;
    private $duracionEstimada;
    private $estadoServicio;

    /* ================================================= */
    /* SETTERS */
    /* ================================================= */

    public function set_idServicio($idServicio) {$this->idServicio = $idServicio;}
    public function set_nombreServicio($nombreServicio) {$this->nombreServicio = $nombreServicio;}
    public function set_descripcion($descripcion) {$this->descripcion = $descripcion;}
    public function set_precio($precio) {$this->precio = $precio;}
    public function set_duracionEstimada($duracionEstimada) {$this->duracionEstimada = $duracionEstimada;}
    public function set_estadoServicio($estadoServicio) {$this->estadoServicio = $estadoServicio;}

    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct(?PDO $conexion = null)
    {
        $this->conexionMD = $conexion ?? $this->getConnection();
    }

    /* ================================================= */
    /* CONSULTAR SERVICIOS */
    /* ================================================= */

    public function consultar(mixed $estado = 'ACTIVO'): array
    {
        $estado = $this->validarFiltroEstado($estado);

        /* Obtener los servicios del estado seleccionado */
        $sql = "SELECT
                    id_servicio,
                    nombre_servicio,
                    descripcion,
                    precio,
                    duracion_estimada,
                    estado_servicio
                FROM servicios";

        if ($estado !== 'TODOS') {
            $sql .= " WHERE estado_servicio = :estado_servicio";
        }

        $sql .= " ORDER BY nombre_servicio ASC";

        $stmt = $this->conexionMD->prepare($sql);

        if ($estado !== 'TODOS') {
            $stmt->bindValue(':estado_servicio', $estado, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ================================================= */
    /* REGISTRAR SERVICIO */
    /* ================================================= */

    public function registrar(): bool
    {
        if (!$this->validarDatosCatalogo(false)) return false;
        if (!$this->comprobarNombreDisponible()) return false;

        /* Registrar el servicio con su precio, duración y estado ACTIVO */
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
        $stmt->bindValue(':duracion_estimada', $this->duracionEstimada, $this->duracionEstimada === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

        return $stmt->execute();
    }

    /* ================================================= */
    /* BUSCAR SERVICIO */
    /* ================================================= */

    public function buscar()
    {
        if (!$this->validarIdentificacion()) return false;

        /* Obtener un servicio mediante su identificador */
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

    /* ================================================= */
    /* MODIFICAR SERVICIO */
    /* ================================================= */

    public function modificar(): bool
    {
        if (!$this->validarDatosCatalogo(true)) return false;

        try {
            $this->conexionMD->beginTransaction();

            /* Comprobar que el servicio siga activo antes de modificarlo */
            $sqlBuscar = "SELECT id_servicio
                          FROM servicios
                          WHERE id_servicio = :id_servicio
                            AND estado_servicio = 'ACTIVO'
                          LIMIT 1
                          FOR UPDATE";

            $stmtBuscar = $this->conexionMD->prepare($sqlBuscar);
            $stmtBuscar->bindValue(':id_servicio', $this->idServicio, PDO::PARAM_INT);
            $stmtBuscar->execute();

            if ($stmtBuscar->fetchColumn() === false) {
                $this->erroresValidacion['id_servicio'] = 'El servicio está inactivo o no se encuentra disponible. Actualice la lista.';
                $this->conexionMD->rollBack();
                return false;
            }

            if (!$this->comprobarNombreDisponible($this->idServicio)) {
                $this->conexionMD->rollBack();
                return false;
            }

            /* Actualizar los datos, el precio y la duración conservando el identificador */
            $sql = "UPDATE servicios SET
                        nombre_servicio = :nombre_servicio,
                        descripcion = :descripcion,
                        precio = :precio,
                        duracion_estimada = :duracion_estimada
                    WHERE id_servicio = :id_servicio
                      AND estado_servicio = 'ACTIVO'";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':id_servicio', $this->idServicio, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_servicio', $this->nombreServicio);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->bindParam(':precio', $this->precio);
            $stmt->bindValue(':duracion_estimada', $this->duracionEstimada, $this->duracionEstimada === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->execute();

            if (!$this->conexionMD->commit()) {
                throw new \RuntimeException('No fue posible confirmar la modificación del servicio.');
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
    /* DESACTIVAR SERVICIO */
    /* ================================================= */

    public function eliminar(): bool
    {
        return $this->cambiarEstadoServicio('ACTIVO', 'INACTIVO');
    }

    /* ================================================= */
    /* REACTIVAR SERVICIO */
    /* ================================================= */

    public function reactivar(): bool
    {
        return $this->cambiarEstadoServicio('INACTIVO', 'ACTIVO');
    }

    /* ================================================= */
    /* CAMBIAR EL ESTADO SIN ALTERAR LAS CITAS NI LAS RESERVAS */
    /* ================================================= */

    private function cambiarEstadoServicio(string $estadoActual, string $estadoNuevo): bool
    {
        if (!$this->validarIdentificacion()) return false;

        /* Cambiar únicamente el estado del servicio seleccionado */
        $sql = "UPDATE servicios
                SET estado_servicio = :estado_nuevo
                WHERE id_servicio = :id_servicio
                  AND estado_servicio = :estado_actual";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_servicio', $this->idServicio, PDO::PARAM_INT);
        $stmt->bindValue(':estado_actual', $estadoActual, PDO::PARAM_STR);
        $stmt->bindValue(':estado_nuevo', $estadoNuevo, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() !== 1) {
            throw new \DomainException('El estado del servicio cambió o el registro no se encuentra disponible. Actualice la lista.');
        }

        return true;
    }

    /* ================================================= */
    /* COMPROBAR QUE EL NOMBRE NO PERTENEZCA A OTRO SERVICIO */
    /* ================================================= */

    private function comprobarNombreDisponible(?int $idExcluir = null): bool
    {
        $sql = "SELECT estado_servicio
                FROM servicios
                WHERE nombre_servicio = :nombre_servicio";

        if ($idExcluir !== null) {
            $sql .= " AND id_servicio <> :id_servicio";
        }

        $sql .= " LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':nombre_servicio', $this->nombreServicio);

        if ($idExcluir !== null) {
            $stmt->bindValue(':id_servicio', $idExcluir, PDO::PARAM_INT);
        }

        $stmt->execute();
        $estadoExistente = $stmt->fetchColumn();

        if ($estadoExistente === false) return true;

        $this->erroresValidacion['nombre_servicio'] = $estadoExistente === 'INACTIVO'
            ? 'Ya existe un servicio inactivo con ese nombre. Reactívelo desde Inactivos.'
            : 'Ya existe un servicio con ese nombre.';

        return false;
    }

    /* ================================================= */
    /* VALIDAR LOS DATOS ANTES DE CONSULTAR O GUARDAR */
    /* ================================================= */

    public function validarIdentificacion(): bool
    {
        $id = $this->validarIdConfiguracion($this->idServicio, 'id_servicio');
        if ($id === null) return false;

        $this->idServicio = $id;
        return true;
    }

    private function validarDatosCatalogo(bool $esModificacion): bool
    {
        $datos = $this->validarDatosConfiguracion([
            'id_servicio' => $this->idServicio,
            'nombre_servicio' => $this->nombreServicio,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'duracion_estimada' => $this->duracionEstimada
        ], 'nombre_servicio', 120, 255, $esModificacion ? 'id_servicio' : null);

        if ($this->obtenerErroresValidacion() !== []) return false;

        $this->nombreServicio = $datos['nombre_servicio'];
        $this->descripcion = $datos['descripcion'];
        $this->precio = $datos['precio'];
        $this->duracionEstimada = $datos['duracion_estimada'];
        if ($esModificacion) $this->idServicio = $datos['id_servicio'];

        return true;
    }
}