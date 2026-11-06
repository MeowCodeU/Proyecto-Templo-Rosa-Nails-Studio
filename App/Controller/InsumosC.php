<?php

use App\Model\InsumosM;


/* ================================================= */
/* IDENTIFICAR SOLICITUD */
/* ================================================= */

/* Identificar las solicitudes AJAX y los datos enviados como JSON */
$contenidoJsonInsumos = stripos(
    (string) ($_SERVER['CONTENT_TYPE'] ?? ''),
    'application/json'
) === 0;

$esAjaxInsumos = $contenidoJsonInsumos || strtolower(
    (string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')
) === 'xmlhttprequest';


/* ================================================= */
/* RESPUESTAS JSON */
/* ================================================= */

/* Enviar una respuesta JSON y finalizar la solicitud */
$responderInsumos = static function (
    array $datos,
    int $codigo = 200
): void {
    http_response_code($codigo);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );

    exit;
};


/* ================================================= */
/* PROCESAR SOLICITUD */
/* ================================================= */

try {

    /* Comprobar el método de la solicitud */
    $metodoInsumos = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if (!in_array($metodoInsumos, ['GET', 'POST'], true)) {
        header('Allow: GET, POST');

        $responderInsumos(
            [
                'ok' => false,
                'mensaje' => 'Método no permitido.'
            ],
            405
        );
    }

    $datosInsumos = $_POST;

    /* Recibir datos JSON o formularios normales */
    if ($metodoInsumos === 'POST' && $contenidoJsonInsumos) {

        $entradaInsumos = json_decode(
            file_get_contents('php://input'),
            false,
            32,
            JSON_THROW_ON_ERROR
        );

        if (!$entradaInsumos instanceof stdClass) {
            throw new InvalidArgumentException(
                'La solicitud debe contener un objeto JSON.'
            );
        }

        $datosInsumos = get_object_vars($entradaInsumos);
    }

    /* Modelo de Insumos */
    $modeloInsumos = new InsumosM();


    /* ================================================= */
    /* CONSULTAR */
    /* ================================================= */

    /* Consultar la lista mediante AJAX */
    if ($metodoInsumos === 'GET' && $esAjaxInsumos) {

        $responderInsumos(
            [
                'ok' => true,
                'insumos' => $modeloInsumos->consultar()
            ]
        );
    }


    /* Procesar acciones enviadas por los formularios */
    if ($metodoInsumos === 'POST') {

        $accionInsumos = $datosInsumos['accion'] ?? '';

        if (
            !is_string($accionInsumos) ||
            !in_array(
                $accionInsumos,
                ['consultar', 'registrar', 'modificar', 'desactivar'],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'La acción solicitada no es válida.'
            );
        }

        /* Consultar insumos activos */
        if ($accionInsumos === 'consultar') {

            $responderInsumos(
                [
                    'ok' => true,
                    'insumos' => $modeloInsumos->consultar()
                ]
            );
        }


        /* ================================================= */
        /* COMPROBAR DATOS */
        /* ================================================= */

        $erroresInsumos = [];

        /* Comprobar el identificador para modificar o desactivar */
        if ($accionInsumos !== 'registrar') {

            $idInsumo = $datosInsumos['id_insumo'] ?? '';

            if (
                (!is_string($idInsumo) && !is_int($idInsumo)) ||
                !ctype_digit((string) $idInsumo) ||
                (float) $idInsumo < 1 ||
                (float) $idInsumo > 4294967295
            ) {
                $erroresInsumos['id_insumo'] =
                    'Seleccione un insumo válido.';
            }
        }

        if (
            in_array(
                $accionInsumos,
                ['registrar', 'modificar'],
                true
            )
        ) {

            /* Comprobar el nombre obligatorio */
            $nombreInsumo = $datosInsumos['nombre_insumo'] ?? '';

            if (
                !is_string($nombreInsumo) ||
                trim($nombreInsumo) === ''
            ) {
                $erroresInsumos['nombre_insumo'] =
                    'El nombre del insumo es obligatorio.';
            }

            /* Comprobar los campos de texto recibidos */
            foreach (
                ['descripcion', 'presentacion', 'tipo_control', 'fecha_vencimiento']
                as $campo
            ) {
                if (
                    isset($datosInsumos[$campo]) &&
                    !is_string($datosInsumos[$campo])
                ) {
                    $erroresInsumos[$campo] =
                        'El valor del campo no es válido.';
                }
            }

            /* Conservar las cantidades enteras y no negativas de la vista */
            foreach (['stock_actual', 'stock_minimo'] as $campo) {

                $cantidad = $datosInsumos[$campo] ?? 0;

                if (
                    (!is_string($cantidad) && !is_int($cantidad)) ||
                    !ctype_digit((string) $cantidad) ||
                    (float) $cantidad > 4294967295
                ) {
                    $erroresInsumos[$campo] =
                        'Indique una cantidad entera no negativa válida.';
                }
            }
        }

        /* Devolver los errores antes de guardar */
        if ($erroresInsumos) {
            $responderInsumos(
                [
                    'ok' => false,
                    'mensaje' => 'Revise los campos señalados.',
                    'errores' => $erroresInsumos
                ],
                422
            );
        }


        /* ================================================= */
        /* REGISTRAR */
        /* ================================================= */

        /* Registrar un nuevo insumo */
        if ($accionInsumos === 'registrar') {

            $tipoControl = strtoupper(
                trim($datosInsumos['tipo_control'] ?? 'UNITARIO')
            );

            if (!in_array($tipoControl, ['UNITARIO', 'POR_ENVASE'], true)) {
                $tipoControl = 'UNITARIO';
            }

            $modeloInsumos->set_nombreInsumo(trim($datosInsumos['nombre_insumo'] ?? ''));
            $modeloInsumos->set_descripcion($datosInsumos['descripcion'] ?? '');
            $modeloInsumos->set_presentacion($datosInsumos['presentacion'] ?? '');
            $modeloInsumos->set_tipoControl($tipoControl);
            $modeloInsumos->set_stockActual($datosInsumos['stock_actual'] ?? 0);
            $modeloInsumos->set_stockMinimo($datosInsumos['stock_minimo'] ?? 0);
            $modeloInsumos->set_fechaVencimiento($datosInsumos['fecha_vencimiento'] ?? '');

            $resultadoInsumos = $modeloInsumos->registrar();

            $mensajeInsumos =
                'Insumo registrado correctamente.';
        }


        /* ================================================= */
        /* MODIFICAR */
        /* ================================================= */

        /* Modificar un insumo activo */
        if ($accionInsumos === 'modificar') {

            $modeloInsumos->set_idInsumo($datosInsumos['id_insumo']);

            $insumoActual = $modeloInsumos->buscar();

            if (
                !$insumoActual ||
                $insumoActual['estado_insumo'] !== 'ACTIVO'
            ) {
                $responderInsumos(
                    [
                        'ok' => false,
                        'mensaje' =>
                            'El insumo no se encuentra activo. Actualice la lista.'
                    ],
                    404
                );
            }

            $tipoControl = strtoupper(
                trim($datosInsumos['tipo_control'] ?? 'UNITARIO')
            );

            if (!in_array($tipoControl, ['UNITARIO', 'POR_ENVASE'], true)) {
                $tipoControl = 'UNITARIO';
            }

            $modeloInsumos->set_nombreInsumo(trim($datosInsumos['nombre_insumo'] ?? ''));
            $modeloInsumos->set_descripcion($datosInsumos['descripcion'] ?? '');
            $modeloInsumos->set_presentacion($datosInsumos['presentacion'] ?? '');
            $modeloInsumos->set_tipoControl($tipoControl);
            $modeloInsumos->set_stockActual($datosInsumos['stock_actual'] ?? 0);
            $modeloInsumos->set_stockMinimo($datosInsumos['stock_minimo'] ?? 0);
            $modeloInsumos->set_fechaVencimiento($datosInsumos['fecha_vencimiento'] ?? '');

            $resultadoInsumos = $modeloInsumos->modificar();

            $mensajeInsumos =
                'Insumo actualizado correctamente.';
        }


        /* ================================================= */
        /* DESACTIVAR */
        /* ================================================= */

        /* Desactivar un insumo y conservar su registro */
        if ($accionInsumos === 'desactivar') {

            $modeloInsumos->set_idInsumo($datosInsumos['id_insumo']);

            $insumoActual = $modeloInsumos->buscar();

            if (
                !$insumoActual ||
                $insumoActual['estado_insumo'] !== 'ACTIVO'
            ) {
                $responderInsumos(
                    [
                        'ok' => false,
                        'mensaje' =>
                            'El insumo no se encuentra activo. Actualice la lista.'
                    ],
                    404
                );
            }

            $resultadoInsumos = $modeloInsumos->eliminar();

            $mensajeInsumos =
                'Insumo desactivado correctamente.';
        }


        /* ================================================= */
        /* CONFIRMAR OPERACIÓN */
        /* ================================================= */

        /* Comprobar el resultado del modelo */
        if ($resultadoInsumos !== true) {
            throw new RuntimeException(
                'El modelo no confirmó la operación de insumos.'
            );
        }

        /* Devolver el resultado de la operación AJAX */
        if ($esAjaxInsumos) {
            $responderInsumos(
                [
                    'ok' => true,
                    'mensaje' => $mensajeInsumos
                ]
            );
        }

        /* Redirigir cuando se utiliza un formulario sin AJAX */
        header('Location: Index.php?url=insumos');
        exit;
    }


    /* Consultar los datos iniciales para cargar la vista */
    $listaInsumos = $modeloInsumos->consultar();

} catch (JsonException | InvalidArgumentException $e) {

    /* Informar errores de la solicitud */
    $responderInsumos(
        [
            'ok' => false,
            'mensaje' => $e instanceof JsonException
                ? 'Los datos enviados no contienen un JSON válido.'
                : $e->getMessage()
        ],
        422
    );

} catch (Throwable $e) {

    /* Registrar el detalle técnico y devolver un mensaje al usuario */
    error_log('Insumos: ' . $e->getMessage());

    $responderInsumos(
        [
            'ok' => false,
            'mensaje' =>
                'No fue posible completar la operación de insumos. Intente nuevamente.'
        ],
        500
    );
}


/* ================================================= */
/* CARGAR VISTA */
/* ================================================= */

/* Mostrar la vista de Insumos */
require_once __DIR__ . '/../View/InsumosV.php';