<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;


/* ===================================================== */
/* MODELO DE INSUMOS */
/* ===================================================== */

class InsumosM extends Database implements CrudInterface
{
    /* ================================================= */
    /* CONEXIÓN Y ATRIBUTOS */
    /* ================================================= */

    /* Conexión con la base de datos */
    private $conexionMD;

    /* Atributos de la tabla insumos */
    private $idInsumo;
    private $nombreInsumo;
    private $descripcion;
    private $presentacion;
    private $tipoControl;
    private $stockActual;
    private $stockMinimo;
    private $fechaVencimiento;
    private $estadoInsumo;


    /* ================================================= */
    /* SETTERS */
    /* ================================================= */

    public function set_idInsumo($idInsumo) {$this->idInsumo = $idInsumo;}
    public function set_nombreInsumo($nombreInsumo) {$this->nombreInsumo = $nombreInsumo;}
    public function set_descripcion($descripcion) {$this->descripcion = $descripcion;}
    public function set_presentacion($presentacion) {$this->presentacion = $presentacion;}
    public function set_tipoControl($tipoControl) {$this->tipoControl = $tipoControl;}
    public function set_stockActual($stockActual) {$this->stockActual = $stockActual;}
    public function set_stockMinimo($stockMinimo) {$this->stockMinimo = $stockMinimo;}
    public function set_fechaVencimiento($fechaVencimiento) {$this->fechaVencimiento = $fechaVencimiento;}
    public function set_estadoInsumo($estadoInsumo) {$this->estadoInsumo = $estadoInsumo;}


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* ================================================= */
    /* CONSULTAR INSUMOS */
    /* ================================================= */

    public function consultar()
    {
        /* Obtener los insumos activos ordenados por su nombre */
        $sql = "SELECT
                    id_insumo,
                    nombre_insumo,
                    descripcion,
                    presentacion,
                    tipo_control,
                    stock_actual,
                    stock_minimo,
                    fecha_vencimiento,
                    estado_insumo
                FROM insumos
                WHERE estado_insumo = 'ACTIVO'
                ORDER BY nombre_insumo ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* REGISTRAR INSUMO */
    /* ================================================= */

    public function registrar()
    {
        /* Registrar el insumo activo y convertir una fecha vacía en NULL */
        $sql = "INSERT INTO insumos (
                    nombre_insumo,
                    descripcion,
                    presentacion,
                    tipo_control,
                    stock_actual,
                    stock_minimo,
                    fecha_vencimiento,
                    estado_insumo
                ) VALUES (
                    :nombre_insumo,
                    :descripcion,
                    :presentacion,
                    :tipo_control,
                    :stock_actual,
                    :stock_minimo,
                    NULLIF(:fecha_vencimiento, ''),
                    'ACTIVO'
                )";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':nombre_insumo', $this->nombreInsumo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':presentacion', $this->presentacion);
        $stmt->bindParam(':tipo_control', $this->tipoControl);
        $stmt->bindParam(':stock_actual', $this->stockActual);
        $stmt->bindParam(':stock_minimo', $this->stockMinimo);
        $stmt->bindParam(':fecha_vencimiento', $this->fechaVencimiento);

        return $stmt->execute();
    }


    /* ================================================= */
    /* BUSCAR INSUMO */
    /* ================================================= */

    public function buscar()
    {
        /* Obtener un insumo mediante su identificador */
        $sql = "SELECT
                    id_insumo,
                    nombre_insumo,
                    descripcion,
                    presentacion,
                    tipo_control,
                    stock_actual,
                    stock_minimo,
                    fecha_vencimiento,
                    estado_insumo
                FROM insumos
                WHERE id_insumo = :id_insumo
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':id_insumo', $this->idInsumo);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* MODIFICAR INSUMO */
    /* ================================================= */

    public function modificar()
    {
        /* Actualizar los datos y las existencias del insumo */
        $sql = "UPDATE insumos SET
                    nombre_insumo = :nombre_insumo,
                    descripcion = :descripcion,
                    presentacion = :presentacion,
                    tipo_control = :tipo_control,
                    stock_actual = :stock_actual,
                    stock_minimo = :stock_minimo,
                    fecha_vencimiento = NULLIF(:fecha_vencimiento, '')
                WHERE id_insumo = :id_insumo";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_insumo', $this->idInsumo);
        $stmt->bindParam(':nombre_insumo', $this->nombreInsumo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':presentacion', $this->presentacion);
        $stmt->bindParam(':tipo_control', $this->tipoControl);
        $stmt->bindParam(':stock_actual', $this->stockActual);
        $stmt->bindParam(':stock_minimo', $this->stockMinimo);
        $stmt->bindParam(':fecha_vencimiento', $this->fechaVencimiento);

        return $stmt->execute();
    }


    /* ================================================= */
    /* DESACTIVAR INSUMO */
    /* ================================================= */

    public function eliminar()
    {
        /* Cambiar el estado del insumo para conservar su registro */
        $sql = "UPDATE insumos
                SET estado_insumo = 'INACTIVO'
                WHERE id_insumo = :id_insumo";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':id_insumo', $this->idInsumo);

        return $stmt->execute();
    }
}