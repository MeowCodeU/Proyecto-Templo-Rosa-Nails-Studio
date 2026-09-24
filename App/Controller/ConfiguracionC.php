<?php

use App\Model\ServicioM;
use App\Model\EstadoAgendamientoM;
use App\Model\TipoAgendamientoM;


/* Modelos de Configuración */
$modeloServicio = new ServicioM();
$modeloEstadoAgendamiento = new EstadoAgendamientoM();
$modeloTipoAgendamiento = new TipoAgendamientoM();


/* Procesar acciones enviadas por los formularios */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    /* ================================================= */
    /* REGISTRAR */
    /* ================================================= */

    /* Registrar servicio */
    if ($_POST['accion'] === 'registrar_servicio') {

        $modeloServicio->set_nombreServicio(
            $_POST['nombre_servicio'] ?? ''
        );

        $modeloServicio->set_descripcion(
            $_POST['descripcion'] ?? ''
        );

        $modeloServicio->set_precio(
            $_POST['precio'] ?? ''
        );

        $modeloServicio->set_duracionEstimada(
            $_POST['duracion_estimada'] ?? ''
        );

        $modeloServicio->registrar();

        header('Location: Index.php?url=configuracion');
        exit;
    }


    /* Registrar estado de agendamiento */
    if ($_POST['accion'] === 'registrar_estado_agendamiento') {

        $modeloEstadoAgendamiento->set_nombreEstado(
            $_POST['nombre_estado'] ?? ''
        );

        $modeloEstadoAgendamiento->set_descripcion(
            $_POST['descripcion'] ?? ''
        );

        $modeloEstadoAgendamiento->registrar();

        header('Location: Index.php?url=configuracion');
        exit;
    }


    /* Registrar tipo de agendamiento */
    if ($_POST['accion'] === 'registrar_tipo_agendamiento') {

        $modeloTipoAgendamiento->set_nombreTipoAgendamiento(
            $_POST['nombre_tipo_agendamiento'] ?? ''
        );

        $modeloTipoAgendamiento->set_descripcion(
            $_POST['descripcion'] ?? ''
        );

        $modeloTipoAgendamiento->registrar();

        header('Location: Index.php?url=configuracion');
        exit;
    }


    /* ================================================= */
    /* MODIFICAR */
    /* ================================================= */

    /* Modificar servicio */
    if ($_POST['accion'] === 'modificar_servicio') {

        $modeloServicio->set_idServicio(
            $_POST['id_servicio'] ?? ''
        );

        $modeloServicio->set_nombreServicio(
            $_POST['nombre_servicio'] ?? ''
        );

        $modeloServicio->set_descripcion(
            $_POST['descripcion'] ?? ''
        );

        $modeloServicio->set_precio(
            $_POST['precio'] ?? ''
        );

        $modeloServicio->set_duracionEstimada(
            $_POST['duracion_estimada'] ?? ''
        );

        $modeloServicio->modificar();

        header('Location: Index.php?url=configuracion');
        exit;
    }


    /* Modificar estado de agendamiento */
    if ($_POST['accion'] === 'modificar_estado_agendamiento') {

        $modeloEstadoAgendamiento->set_idEstadoAgendamiento(
            $_POST['id_estado_agendamiento'] ?? ''
        );

        $modeloEstadoAgendamiento->set_nombreEstado(
            $_POST['nombre_estado'] ?? ''
        );

        $modeloEstadoAgendamiento->set_descripcion(
            $_POST['descripcion'] ?? ''
        );

        $modeloEstadoAgendamiento->modificar();

        header('Location: Index.php?url=configuracion');
        exit;
    }


    /* Modificar tipo de agendamiento */
    if ($_POST['accion'] === 'modificar_tipo_agendamiento') {

        $modeloTipoAgendamiento->set_idTipoAgendamiento(
            $_POST['id_tipo_agendamiento'] ?? ''
        );

        $modeloTipoAgendamiento->set_nombreTipoAgendamiento(
            $_POST['nombre_tipo_agendamiento'] ?? ''
        );

        $modeloTipoAgendamiento->set_descripcion(
            $_POST['descripcion'] ?? ''
        );

        $modeloTipoAgendamiento->modificar();

        header('Location: Index.php?url=configuracion');
        exit;
    }


    /* ================================================= */
    /* DESACTIVAR */
    /* ================================================= */

    /* Desactivar servicio */
    if ($_POST['accion'] === 'cambiar_estado_servicio') {

        $modeloServicio->set_idServicio(
            $_POST['id_servicio'] ?? ''
        );

        $modeloServicio->eliminar();

        header('Location: Index.php?url=configuracion');
        exit;
    }


    /* Desactivar estado de agendamiento */
    if ($_POST['accion'] === 'cambiar_estado_agendamiento') {

        $modeloEstadoAgendamiento->set_idEstadoAgendamiento(
            $_POST['id_estado_agendamiento'] ?? ''
        );

        $modeloEstadoAgendamiento->eliminar();

        header('Location: Index.php?url=configuracion');
        exit;
    }


    /* Desactivar tipo de agendamiento */
    if ($_POST['accion'] === 'cambiar_estado_tipo_agendamiento') {

        $modeloTipoAgendamiento->set_idTipoAgendamiento(
            $_POST['id_tipo_agendamiento'] ?? ''
        );

        $modeloTipoAgendamiento->eliminar();

        header('Location: Index.php?url=configuracion');
        exit;
    }
}


/* Consultar los registros activos para mostrarlos en la vista */
$listaServicios = $modeloServicio->consultar();
$listaEstadosAgendamiento = $modeloEstadoAgendamiento->consultar();
$listaTiposAgendamiento = $modeloTipoAgendamiento->consultar();


/* Cargar la vista de Configuración */
require_once __DIR__ . '/../View/ConfiguracionV.php';