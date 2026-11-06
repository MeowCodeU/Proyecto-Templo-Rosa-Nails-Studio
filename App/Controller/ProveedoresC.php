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
    $datosFormulario = $_POST;

    /* Recibir JSON o los datos de un formulario tradicional */
    if ($esCuerpoJson) {
        $datosFormulario = json_decode(
            file_get_contents('php://input'),
            true,
            32,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($datosFormulario)) {
            throw new \InvalidArgumentException('Los datos enviados no tienen un formato válido.');
        }
    }

    /* Comprobar los campos obligatorios y los tipos recibidos */
    $validarCampos = static function (array $obligatorios) use ($datosFormulario): void {
        $campos = [
            'rif', 'nombre_empresa', 'cedula', 'nombre', 'apellido',
            'telefono', 'correo', 'direccion', 'ciudad'
        ];

        foreach ($campos as $campo) {
            if (isset($datosFormulario[$campo]) && !is_string($datosFormulario[$campo])) {
                throw new \InvalidArgumentException('Los datos enviados no son válidos.');
            }
        }

        foreach ($obligatorios as $campo) {
            if (trim($datosFormulario[$campo] ?? '') === '') {
                throw new \InvalidArgumentException('Complete los campos obligatorios del formulario.');
            }
        }
    };

    /* Comprobar los identificadores recibidos */
    $validarIdentificador = static function (string $campo) use ($datosFormulario): int {
        $identificador = filter_var($datosFormulario[$campo] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        if ($identificador === false) {
            throw new \InvalidArgumentException('Seleccione un proveedor válido.');
        }

        return $identificador;
    };


    /* Crear el objeto del modelo */
    $modeloProveedores = new ProveedoresM();

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

                $listaProveedores = $modeloProveedores->consultar();

                if (!is_array($listaProveedores)) {
                    throw new \RuntimeException('No fue posible consultar los proveedores.');
                }

                $responderJson([
                    'ok' => true,
                    'proveedores' => array_values($listaProveedores)
                ]);

                break;


            /* ================================================= */
            /* REGISTRAR PROVEEDOR */
            /* ================================================= */

            case 'registrar':

                $validarCampos(['rif', 'nombre_empresa', 'cedula', 'nombre', 'apellido']);

                $modeloProveedores->set_rif(trim($datosFormulario['rif'] ?? ''));
                $modeloProveedores->set_nombreEmpresa(trim($datosFormulario['nombre_empresa'] ?? ''));
                $modeloProveedores->set_cedula(trim($datosFormulario['cedula'] ?? ''));
                $modeloProveedores->set_nombre(trim($datosFormulario['nombre'] ?? ''));
                $modeloProveedores->set_apellido(trim($datosFormulario['apellido'] ?? ''));
                $modeloProveedores->set_telefono(trim($datosFormulario['telefono'] ?? ''));
                $modeloProveedores->set_correo(trim($datosFormulario['correo'] ?? ''));
                $modeloProveedores->set_direccion(trim($datosFormulario['direccion'] ?? ''));
                $modeloProveedores->set_ciudad(trim($datosFormulario['ciudad'] ?? ''));

                $resultado = $modeloProveedores->registrar();
                $mensaje = 'Proveedor registrado correctamente.';

                break;


            /* ================================================= */
            /* MODIFICAR PROVEEDOR */
            /* ================================================= */

            case 'modificar':

                $idProveedor = $validarIdentificador('id_proveedor');
                $idPersonaContacto = $validarIdentificador('id_persona_contacto');

                $validarCampos(['rif', 'nombre_empresa', 'nombre', 'apellido']);

                $modeloProveedores->set_idProveedor($idProveedor);
                $modeloProveedores->set_idPersonaContacto($idPersonaContacto);
                $modeloProveedores->set_rif(trim($datosFormulario['rif'] ?? ''));
                $modeloProveedores->set_nombreEmpresa(trim($datosFormulario['nombre_empresa'] ?? ''));
                $modeloProveedores->set_nombre(trim($datosFormulario['nombre'] ?? ''));
                $modeloProveedores->set_apellido(trim($datosFormulario['apellido'] ?? ''));
                $modeloProveedores->set_telefono(trim($datosFormulario['telefono'] ?? ''));
                $modeloProveedores->set_correo(trim($datosFormulario['correo'] ?? ''));
                $modeloProveedores->set_direccion(trim($datosFormulario['direccion'] ?? ''));
                $modeloProveedores->set_ciudad(trim($datosFormulario['ciudad'] ?? ''));

                $resultado = $modeloProveedores->modificar();
                $mensaje = 'Proveedor modificado correctamente.';

                break;


            /* ================================================= */
            /* DESACTIVAR PROVEEDOR */
            /* ================================================= */

            case 'desactivar':
            case 'eliminar':

                $modeloProveedores->set_idProveedor($validarIdentificador('id_proveedor'));

                $resultado = $modeloProveedores->eliminar();
                $mensaje = 'Proveedor desactivado correctamente.';

                break;


            default:
                throw new \InvalidArgumentException('La acción enviada no es válida.');
        }


        /* Confirmar el resultado de la operación */
        if ($resultado === false) {
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

    /* Consultar los proveedores activos */
    $listaProveedores = $modeloProveedores->consultar();

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

    } elseif ($e instanceof \PDOException && (int) ($e->errorInfo[1] ?? 0) === 1062) {
        $estadoRespuesta = 409;
        $mensaje = 'Ya existe un registro con uno de los datos únicos ingresados. Revise los datos del formulario.';
    }

    if ($esPeticionAjax) {
        $responderJson(['ok' => false, 'mensaje' => $mensaje], $estadoRespuesta);
    }

    http_response_code($estadoRespuesta);

    exit($mensaje);
}


/* ===================================================== */
/* CARGA DE LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/ProveedoresV.php';