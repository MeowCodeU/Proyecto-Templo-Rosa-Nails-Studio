<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;

class InsumosM extends Database implements CrudInterface
{
    /* Atributo de conexión del modelo */
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


    /* Setters: asignan valores a los atributos privados */
    public function set_idInsumo($idInsumo)
    {
        $this->idInsumo = $idInsumo;
    }

    public function set_nombreInsumo($nombreInsumo)
    {
        $this->nombreInsumo = $nombreInsumo;
    }

    public function set_descripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }

    public function set_presentacion($presentacion)
    {
        $this->presentacion = $presentacion;
    }

    public function set_tipoControl($tipoControl)
    {
        $this->tipoControl = $tipoControl;
    }

    public function set_stockActual($stockActual)
    {
        $this->stockActual = $stockActual;
    }

    public function set_stockMinimo($stockMinimo)
    {
        $this->stockMinimo = $stockMinimo;
    }

    public function set_fechaVencimiento($fechaVencimiento)
    {
        $this->fechaVencimiento = $fechaVencimiento;
    }

    public function set_estadoInsumo($estadoInsumo)
    {
        $this->estadoInsumo = $estadoInsumo;
    }


    /* Constructor: obtiene la conexión heredada de Database */
    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* Consultar: obtiene los insumos activos */
    public function consultar()
    {
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


    /* Registrar: inserta un nuevo insumo */
    public function registrar()
    {
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


    /* Buscar: obtiene un insumo por su identificador */
    public function buscar()
    {
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


    /* Modificar: actualiza los datos de un insumo */
    public function modificar()
    {
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


    /* Eliminar: cambia el estado del insumo a INACTIVO */
    public function eliminar()
    {
        $sql = "UPDATE insumos
                SET estado_insumo = 'INACTIVO'
                WHERE id_insumo = :id_insumo";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':id_insumo', $this->idInsumo);

        return $stmt->execute();
    }
}