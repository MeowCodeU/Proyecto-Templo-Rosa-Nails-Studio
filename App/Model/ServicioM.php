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

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }

    /* ================================================= */
    /* CONSULTAR SERVICIOS */
    /* ================================================= */

    public function consultar(): array
    {
        /* Obtener los servicios activos ordenados por su nombre */
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

    /* ================================================= */
    /* REGISTRAR SERVICIO */
    /* ================================================= */

    public function registrar(): bool
    {
        if (!$this->validarDatosCatalogo(false)) return false;

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

        /* Actualizar los datos, el precio y la duración del servicio */
        $sql = "UPDATE servicios SET
                    nombre_servicio = :nombre_servicio,
                    descripcion = :descripcion,
                    precio = :precio,
                    duracion_estimada = :duracion_estimada
                WHERE id_servicio = :id_servicio";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':nombre_servicio', $this->nombreServicio);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':precio', $this->precio);
        $stmt->bindValue(':duracion_estimada', $this->duracionEstimada, $this->duracionEstimada === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(':id_servicio', $this->idServicio);

        return $stmt->execute();
    }

    /* ================================================= */
    /* DESACTIVAR SERVICIO */
    /* ================================================= */

    public function eliminar(): bool
    {
        if (!$this->validarIdentificacion()) return false;

        /* Cambiar el estado del servicio para conservar su registro */
        $sql = "UPDATE servicios
                SET estado_servicio = 'INACTIVO'
                WHERE id_servicio = :id_servicio";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':id_servicio', $this->idServicio);

        return $stmt->execute();
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