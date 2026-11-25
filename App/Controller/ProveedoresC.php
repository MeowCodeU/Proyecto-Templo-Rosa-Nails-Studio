<?php

namespace App\Controller;

use App\Model\ProveedoresM;


/* ===================================================== */
/* GESTIÓN DE PROVEEDORES */
/* ===================================================== */

$metodoSolicitud = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$tipoContenido = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
$esCuerpoJson = $metodoSolicitud === 'POST' && $tipoContenido === 'application/json';

$esPeticionAjax = $esCuerpoJson ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';


/* Enviar la respuesta y finalizar la solicitud */
$responderJson = static function (array $datos, int $estado = 200): void {
    http_response_code($estado);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    exit;
};


/* ===================================================== */
/* LECTURA Y COMPROBACIÓN DE LOS DATOS */
/* ===================================================== */

try {
    if (!in_array($metodoSolicitud, ['GET', 'POST'], true)) {
        header('Allow: GET, POST');
        $responderJson(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
    }

    $datosFormulario = $_POST;

    /* Recibir JSON o los datos de un formulario tradicional */
    if ($esCuerpoJson) {
        $datosFormulario = json_decode(
            file_get_contents('php://input'),
            false,
            32,
            JSON_THROW_ON_ERROR
        );

        if (!$datosFormulario instanceof \stdClass) {
            throw new \InvalidArgumentException('Los datos enviados no tienen un formato válido.');
        }

        $datosFormulario = get_object_vars($datosFormulario);
    }

    /* Crear el objeto del modelo */
    $modeloProveedores = new ProveedoresM();

    /* Consultar el estado solicitado desde una petición asíncrona de lectura */
    if ($metodoSolicitud === 'GET' && $esPeticionAjax) {
        $responderJson([
            'ok' => true,
            'proveedores' => $modeloProveedores->consultar($_GET['estado'] ?? 'ACTIVO')
        ]);
    }

    /* Procesar las acciones enviadas por los formularios */
    if ($metodoSolicitud === 'POST' && isset($datosFormulario['accion'])) {

        if (!is_string($datosFormulario['accion'])) {
            throw new \InvalidArgumentException('La acción enviada no es válida.');
        }

        switch ($datosFormulario['accion']) {

            /* ================================================= */
            /* CONSULTAR PROVEEDORES */
            /* ================================================= */

            case 'consultar':

                if (!$esPeticionAjax) {
                    throw new \InvalidArgumentException('La solicitud enviada no es válida.');
                }

                $listaProveedores = $modeloProveedores->consultar($datosFormulario['estado'] ?? 'ACTIVO');

                if (!is_array($listaProveedores)) {
                    throw new \RuntimeException('No fue posible consultar los proveedores.');
                }

                $responderJson([
                    'ok' => true,
                    'proveedores' => array_values($listaProveedores)
                ]);

                break;


            /* ================================================= */
            /* CONSULTAR PERSONA POR CÉDULA */
            /* ================================================= */

            case 'consultar_persona':

                if (!$esPeticionAjax) {
                    throw new \InvalidArgumentException('La solicitud enviada no es válida.');
                }

                $modeloProveedores->set_cedula($datosFormulario['cedula'] ?? '');

                if (!$modeloProveedores->validarIdentificacion()) {
                    throw new \InvalidArgumentException(implode(' ', $modeloProveedores->obtenerErroresValidacion()));
                }

                $persona = $modeloProveedores->consultarDatosPersona(trim($datosFormulario['cedula']));

                $responderJson([
                    'ok' => true,
                    'encontrada' => $persona !== null,
                    'persona' => $persona
                ]);

                break;


            /* ================================================= */
            /* CONSULTAR INSUMOS DISPONIBLES */
            /* ================================================= */

            case 'consultar_insumos_disponibles':

                if (!$esPeticionAjax) {
                    throw new \InvalidArgumentException('La solicitud enviada no es válida.');
                }

                $responderJson([
                    'ok' => true,
                    'insumos' => $modeloProveedores->consultarInsumosDisponibles()
                ]);

                break;


            /* ================================================= */
            /* REGISTRAR PROVEEDOR */
            /* ================================================= */

            case 'registrar':

                $modeloProveedores->set_rif($datosFormulario['rif'] ?? '');
                $modeloProveedores->set_nombreEmpresa($datosFormulario['nombre_empresa'] ?? '');
                $modeloProveedores->set_cedula($datosFormulario['cedula'] ?? '');
                $modeloProveedores->set_nombre($datosFormulario['nombre'] ?? '');
                $modeloProveedores->set_apellido($datosFormulario['apellido'] ?? '');
                $modeloProveedores->set_telefono($datosFormulario['telefono'] ?? '');
                $modeloProveedores->set_correo($datosFormulario['correo'] ?? '');
                $modeloProveedores->set_direccion($datosFormulario['direccion'] ?? '');
                $modeloProveedores->set_ciudad($datosFormulario['ciudad'] ?? '');

                /* Guardar también los insumos marcados en Registrar */
                $idsInsumos = $modeloProveedores->obtenerSeleccionInsumos($datosFormulario, false);

                $resultado = $modeloProveedores->registrar($idsInsumos);
                $mensaje = 'Proveedor registrado correctamente.';

                break;


            /* ================================================= */
            /* MODIFICAR PROVEEDOR */
            /* ================================================= */

            case 'modificar':

                $idProveedor = $datosFormulario['id_proveedor'] ?? null;
                $idPersonaContacto = $datosFormulario['id_persona_contacto'] ?? null;

                $modeloProveedores->set_idProveedor($idProveedor);
                $modeloProveedores->set_idPersonaContacto($idPersonaContacto);
                $modeloProveedores->set_rif($datosFormulario['rif'] ?? '');
                $modeloProveedores->set_nombreEmpresa($datosFormulario['nombre_empresa'] ?? '');
                $modeloProveedores->set_nombre($datosFormulario['nombre'] ?? '');
                $modeloProveedores->set_apellido($datosFormulario['apellido'] ?? '');
                $modeloProveedores->set_telefono($datosFormulario['telefono'] ?? '');
                $modeloProveedores->set_correo($datosFormulario['correo'] ?? '');
                $modeloProveedores->set_direccion($datosFormulario['direccion'] ?? '');
                $modeloProveedores->set_ciudad($datosFormulario['ciudad'] ?? '');

                /* Solo actualizar las asociaciones si el formulario las envió */
                $idsInsumos = $modeloProveedores->obtenerSeleccionInsumos($datosFormulario, true);

                $resultado = $modeloProveedores->modificar($idsInsumos);
                $mensaje = 'Proveedor modificado correctamente.';

                break;


            /* ================================================= */
            /* CAMBIAR PERSONA DE CONTACTO */
            /* ================================================= */

            case 'cambiar_contacto':

                $idProveedor = $datosFormulario['id_proveedor'] ?? null;
                $idPersonaContacto = $datosFormulario['id_persona_contacto'] ?? null;

                $modeloProveedores->set_idProveedor($idProveedor);
                $modeloProveedores->set_idPersonaContacto($idPersonaContacto);
                $modeloProveedores->set_cedula($datosFormulario['cedula'] ?? '');
                $modeloProveedores->set_nombre($datosFormulario['nombre'] ?? '');
                $modeloProveedores->set_apellido($datosFormulario['apellido'] ?? '');
                $modeloProveedores->set_telefono($datosFormulario['telefono'] ?? '');
                $modeloProveedores->set_correo($datosFormulario['correo'] ?? '');
                $modeloProveedores->set_direccion($datosFormulario['direccion'] ?? '');
                $modeloProveedores->set_ciudad($datosFormulario['ciudad'] ?? '');

                $resultado = $modeloProveedores->cambiarContacto();
                $mensaje = 'Persona de contacto cambiada correctamente.';

                break;


            /* ================================================= */
            /* CONSULTAR INSUMOS DEL PROVEEDOR */
            /* ================================================= */

            case 'consultar_insumos':
            case 'consultar_insumos_detalle':

                if (!$esPeticionAjax) {
                    throw new \InvalidArgumentException('La solicitud enviada no es válida.');
                }

                $idProveedor = $datosFormulario['id_proveedor'] ?? null;

                /* La consulta de Detalles también permite revisar un proveedor inactivo */
                $incluirInactivos = $datosFormulario['accion'] === 'consultar_insumos_detalle';
                $insumosProveedor = $modeloProveedores->consultarInsumosProveedor(
                    $idProveedor,
                    $incluirInactivos
                );

                $responderJson([
                    'ok' => true,
                    'insumos' => $insumosProveedor
                ]);

                break;


            /* ================================================= */
            /* ACTUALIZAR INSUMOS DEL PROVEEDOR */
            /* ================================================= */

            /* Compatibilidad temporal con el modal anterior de asociaciones */

            case 'actualizar_insumos':

                if (!$esPeticionAjax) {
                    throw new \InvalidArgumentException('La solicitud enviada no es válida.');
                }

                $idProveedor = $datosFormulario['id_proveedor'] ?? null;
                $idsInsumos = $datosFormulario['id_insumos'] ?? null;

                $cambios = $modeloProveedores->actualizarInsumosProveedor($idProveedor, $idsInsumos);

                $responderJson([
                    'ok' => true,
                    'mensaje' => 'Insumos del proveedor actualizados correctamente.',
                    'agregados' => $cambios['agregados'],
                    'retirados' => $cambios['retirados']
                ]);

                break;


            /* ================================================= */
            /* DESACTIVAR PROVEEDOR */
            /* ================================================= */

            case 'desactivar':
            case 'eliminar':

                $modeloProveedores->set_idProveedor($datosFormulario['id_proveedor'] ?? null);

                $resultado = $modeloProveedores->eliminar();
                $mensaje = 'Proveedor desactivado correctamente.';

                break;


            /* ================================================= */
            /* REACTIVAR PROVEEDOR */
            /* ================================================= */

            case 'reactivar':

                $modeloProveedores->set_idProveedor($datosFormulario['id_proveedor'] ?? null);

                $resultado = $modeloProveedores->reactivar();
                $mensaje = 'Proveedor reactivado correctamente.';

                break;


            default:
                throw new \InvalidArgumentException('La acción enviada no es válida.');
        }


        /* Recuperar los errores que el modelo detectó antes de guardar */
        $errores = $modeloProveedores->obtenerErroresValidacion();

        if ($errores) {
            throw new \InvalidArgumentException(implode(' ', $errores));
        }

        /* Confirmar el resultado de la operación */
        if ($resultado !== true) {
            throw new \RuntimeException('No fue posible completar la operación.');
        }

        if ($esPeticionAjax) {
            $responderJson(['ok' => true, 'mensaje' => $mensaje]);
        }

        /* Volver a la lista cuando el envío utiliza un formulario tradicional */
        header('Location: Index.php?url=proveedores');

        exit;
    }


    /* Rechazar las solicitudes incompletas del módulo */
    if ($esPeticionAjax) {
        $responderJson([
            'ok' => false,
            'mensaje' => 'La solicitud enviada no es válida.'
        ], 400);
    }

    /* Consultar los proveedores e insumos activos para cargar la vista */
    $listaProveedores = $modeloProveedores->consultar();
    $listaInsumosDisponibles = $modeloProveedores->consultarInsumosDisponibles();

} catch (\Throwable $e) {

    /* Preparar un mensaje sin exponer información interna de la base de datos */
    $estadoRespuesta = 500;
    $mensaje = 'No fue posible completar la operación de proveedores. Intente nuevamente.';

    if ($e instanceof \JsonException) {
        $estadoRespuesta = 422;
        $mensaje = 'Los datos enviados no contienen un JSON válido.';

    } elseif ($e instanceof \InvalidArgumentException) {
        $estadoRespuesta = 422;
        $mensaje = $e->getMessage();

    } elseif ($e instanceof \DomainException) {
        $estadoRespuesta = 409;
        $mensaje = $e->getMessage();

    } elseif ($e instanceof \PDOException && (int) ($e->errorInfo[1] ?? 0) === 1062) {
        $estadoRespuesta = 409;
        $mensaje = 'Ya existe un registro con uno de los datos únicos ingresados. Revise los datos del formulario.';
    }

    /* Conservar el detalle técnico de un fallo interno únicamente en el servidor */
    if ($estadoRespuesta === 500) {
        error_log('Proveedores: ' . $e->getMessage());
    }

    if ($esPeticionAjax) {
        $responderJson([
            'ok' => false,
            'mensaje' => $mensaje,
            'errores' => isset($modeloProveedores) ? $modeloProveedores->obtenerErroresValidacion() : []
        ], $estadoRespuesta);
    }

    http_response_code($estadoRespuesta);

    exit($mensaje);
}


/* ===================================================== */
/* CARGA DE LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/ProveedoresV.php';