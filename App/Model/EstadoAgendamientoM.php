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

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }

    /* ================================================= */
    /* CONSULTAR ESTADOS DE AGENDAMIENTO */
    /* ================================================= */

    public function consultar(): array
    {
        /* Obtener los estados del catálogo que permanecen activos */
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

    /* ================================================= */
    /* REGISTRAR ESTADO DE AGENDAMIENTO */
    /* ================================================= */

    public function registrar(): bool
    {
        if (!$this->validarDatosCatalogo(false)) return false;

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

        /* Actualizar el nombre y la descripción del estado */
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

    /* ================================================= */
    /* DESACTIVAR ESTADO DE AGENDAMIENTO */
    /* ================================================= */

    public function eliminar(): bool
    {
        if (!$this->validarIdentificacion()) return false;

        /* Marcar el registro del catálogo como inactivo para conservarlo */
        $sql = "UPDATE estados_agendamientos
                SET estado_registro = 'INACTIVO'
                WHERE id_estado_agendamiento = :id_estado_agendamiento";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_estado_agendamiento', $this->idEstadoAgendamiento);

        return $stmt->execute();
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