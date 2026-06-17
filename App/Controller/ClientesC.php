<?php

use App\Model\ClientesM;

/* Objeto del modelo Clientes */
$modeloClientes = new ClientesM();

/* Procesar acciones enviadas por formulario */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    /* Registrar cliente */
    if ($_POST['accion'] === 'registrar') {

        $modeloClientes->set_Cedula($_POST['cedula'] ?? '');
        $modeloClientes->set_Nombre($_POST['nombre'] ?? '');
        $modeloClientes->set_Apellido($_POST['apellido'] ?? '');
        $modeloClientes->set_Telefono($_POST['telefono'] ?? '');
        $modeloClientes->set_Correo($_POST['correo'] ?? '');
        $modeloClientes->set_Direccion($_POST['direccion'] ?? '');
        $modeloClientes->set_Ciudad($_POST['ciudad'] ?? '');
        $modeloClientes->set_Alergias($_POST['alergias'] ?? '');

        $modeloClientes->registrar();

        header('Location: Index.php?url=clientes');
        exit;
    }

    /* Modificar cliente */
    if ($_POST['accion'] === 'modificar') {

        $modeloClientes->set_Cedula($_POST['cedula'] ?? '');
        $modeloClientes->set_Nombre($_POST['nombre'] ?? '');
        $modeloClientes->set_Apellido($_POST['apellido'] ?? '');
        $modeloClientes->set_Telefono($_POST['telefono'] ?? '');
        $modeloClientes->set_Correo($_POST['correo'] ?? '');
        $modeloClientes->set_Direccion($_POST['direccion'] ?? '');
        $modeloClientes->set_Ciudad($_POST['ciudad'] ?? '');
        $modeloClientes->set_Alergias($_POST['alergias'] ?? '');

        $modeloClientes->modificar();

        header('Location: Index.php?url=clientes');
        exit;
    }

    /* Desactivar cliente */
    if ($_POST['accion'] === 'desactivar' || $_POST['accion'] === 'eliminar') {

        $modeloClientes->set_Cedula($_POST['cedula'] ?? '');

        $modeloClientes->eliminar();

        header('Location: Index.php?url=clientes');
        exit;
    }
}

/* Consultar clientes activos */
$listaClientes = $modeloClientes->consultar();

/* Cargar vista */
require_once __DIR__ . '/../View/ClientesV.php';