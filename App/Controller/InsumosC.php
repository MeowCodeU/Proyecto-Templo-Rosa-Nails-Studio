<?php

use App\Model\InsumosM;

/* Crear una instancia del modelo de Insumos */
$modeloInsumos = new InsumosM();


/* Procesar las acciones enviadas por los formularios */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    /* Registrar un nuevo insumo */
    if ($_POST['accion'] === 'registrar') {

        $modeloInsumos->set_nombreInsumo(
            trim($_POST['nombre_insumo'] ?? '')
        );

        $modeloInsumos->set_descripcion(
            $_POST['descripcion'] ?? ''
        );

        $modeloInsumos->set_presentacion(
            $_POST['presentacion'] ?? ''
        );

        $tipoControl = strtoupper(
            trim($_POST['tipo_control'] ?? 'UNITARIO')
        );

        if (!in_array(
            $tipoControl,
            ['UNITARIO', 'POR_ENVASE'],
            true
        )) {
            $tipoControl = 'UNITARIO';
        }

        $modeloInsumos->set_tipoControl(
            $tipoControl
        );

        $modeloInsumos->set_stockActual(
            $_POST['stock_actual'] ?? 0
        );

        $modeloInsumos->set_stockMinimo(
            $_POST['stock_minimo'] ?? 0
        );

        $modeloInsumos->set_fechaVencimiento(
            $_POST['fecha_vencimiento'] ?? ''
        );

        $modeloInsumos->registrar();

        header('Location: Index.php?url=insumos');
        exit;
    }


    /* Modificar un insumo */
    if ($_POST['accion'] === 'modificar') {

        $modeloInsumos->set_idInsumo(
            $_POST['id_insumo'] ?? ''
        );

        $modeloInsumos->set_nombreInsumo(
            trim($_POST['nombre_insumo'] ?? '')
        );

        $modeloInsumos->set_descripcion(
            $_POST['descripcion'] ?? ''
        );

        $modeloInsumos->set_presentacion(
            $_POST['presentacion'] ?? ''
        );

        $tipoControl = strtoupper(
            trim($_POST['tipo_control'] ?? 'UNITARIO')
        );

        if (!in_array(
            $tipoControl,
            ['UNITARIO', 'POR_ENVASE'],
            true
        )) {
            $tipoControl = 'UNITARIO';
        }

        $modeloInsumos->set_tipoControl(
            $tipoControl
        );

        $modeloInsumos->set_stockActual(
            $_POST['stock_actual'] ?? 0
        );

        $modeloInsumos->set_stockMinimo(
            $_POST['stock_minimo'] ?? 0
        );

        $modeloInsumos->set_fechaVencimiento(
            $_POST['fecha_vencimiento'] ?? ''
        );

        $modeloInsumos->modificar();

        header('Location: Index.php?url=insumos');
        exit;
    }


    /* Desactivar un insumo */
    if ($_POST['accion'] === 'desactivar') {

        $modeloInsumos->set_idInsumo(
            $_POST['id_insumo'] ?? ''
        );

        $modeloInsumos->eliminar();

        header('Location: Index.php?url=insumos');
        exit;
    }
}


/* Consultar los insumos activos para mostrarlos en la vista */
$listaInsumos = $modeloInsumos->consultar();


/* Cargar la vista de Insumos */
require_once __DIR__ . '/../View/InsumosV.php';