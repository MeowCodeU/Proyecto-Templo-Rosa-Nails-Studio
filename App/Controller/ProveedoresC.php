<?php

namespace App\Controller;

use App\Model\ProveedoresM;

/* Crear el objeto del modelo */
$modeloProveedores = new ProveedoresM();


/* Procesar las acciones enviadas por los formularios */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['accion'])
) {
    try {

        switch ($_POST['accion']) {

            /* ================================================= */
            /* REGISTRAR PROVEEDOR */
            /* ================================================= */

            case 'registrar':

                $modeloProveedores->set_rif(
                    trim($_POST['rif'] ?? '')
                );

                $modeloProveedores->set_nombreEmpresa(
                    trim($_POST['nombre_empresa'] ?? '')
                );

                $modeloProveedores->set_cedula(
                    trim($_POST['cedula'] ?? '')
                );

                $modeloProveedores->set_nombre(
                    trim($_POST['nombre'] ?? '')
                );

                $modeloProveedores->set_apellido(
                    trim($_POST['apellido'] ?? '')
                );

                $modeloProveedores->set_telefono(
                    trim($_POST['telefono'] ?? '')
                );

                $modeloProveedores->set_correo(
                    trim($_POST['correo'] ?? '')
                );

                $modeloProveedores->set_direccion(
                    trim($_POST['direccion'] ?? '')
                );

                $modeloProveedores->set_ciudad(
                    trim($_POST['ciudad'] ?? '')
                );

                $modeloProveedores->registrar();

                break;


            /* ================================================= */
            /* MODIFICAR PROVEEDOR */
            /* ================================================= */

            case 'modificar':

                $modeloProveedores->set_idProveedor(
                    (int) ($_POST['id_proveedor'] ?? 0)
                );

                $modeloProveedores->set_idPersonaContacto(
                    (int) ($_POST['id_persona_contacto'] ?? 0)
                );

                $modeloProveedores->set_rif(
                    trim($_POST['rif'] ?? '')
                );

                $modeloProveedores->set_nombreEmpresa(
                    trim($_POST['nombre_empresa'] ?? '')
                );

                $modeloProveedores->set_nombre(
                    trim($_POST['nombre'] ?? '')
                );

                $modeloProveedores->set_apellido(
                    trim($_POST['apellido'] ?? '')
                );

                $modeloProveedores->set_telefono(
                    trim($_POST['telefono'] ?? '')
                );

                $modeloProveedores->set_correo(
                    trim($_POST['correo'] ?? '')
                );

                $modeloProveedores->set_direccion(
                    trim($_POST['direccion'] ?? '')
                );

                $modeloProveedores->set_ciudad(
                    trim($_POST['ciudad'] ?? '')
                );

                $modeloProveedores->modificar();

                break;


            /* ================================================= */
            /* DESACTIVAR PROVEEDOR */
            /* ================================================= */

            case 'desactivar':
            case 'eliminar':

                $modeloProveedores->set_idProveedor(
                    (int) ($_POST['id_proveedor'] ?? 0)
                );

                $modeloProveedores->eliminar();

                break;
        }


        /* Volver a la lista de proveedores */
        header(
            'Location: Index.php?url=proveedores'
        );

        exit;

    } catch (\Throwable $e) {

        die(
            'ERROR EN EL MÓDULO DE PROVEEDORES: '
            . $e->getMessage()
        );
    }
}


/* Consultar los proveedores activos */
$listaProveedores = $modeloProveedores->consultar();


/* Cargar la vista del módulo */
require_once __DIR__ . '/../View/ProveedoresV.php';