<?php

use App\Model\InsumosM;
use App\Components\Session;


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
                ['consultar', 'consultar_historial', 'consultar_proveedores', 'consultar_lotes', 'registrar', 'modificar', 'gestionar_existencias', 'desactivar'],
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
        /* CONSULTAR PROVEEDORES DEL INSUMO */
        /* ================================================= */

        /* Mostrar las empresas asociadas sin modificar los datos */
        if ($accionInsumos === 'consultar_proveedores') {

            if (!$esAjaxInsumos) {
                throw new InvalidArgumentException('La solicitud enviada no es válida.');
            }

            $idInsumoConsulta = $modeloInsumos->validarIdInsumo($datosInsumos['id_insumo'] ?? null);

            if (!is_int($idInsumoConsulta)) {
                throw new InvalidArgumentException('Seleccione un insumo válido.');
            }

            $responderInsumos([
                'ok' => true,
                'proveedores' => $modeloInsumos->consultarProveedoresInsumo($idInsumoConsulta)
            ]);
        }


        /* ================================================= */
        /* CONSULTAR LOTES DEL INSUMO */
        /* ================================================= */

        /* Obtener las cantidades y fechas de vencimiento de cada lote */
        if ($accionInsumos === 'consultar_lotes') {

            if (!$esAjaxInsumos) {
                throw new InvalidArgumentException('La solicitud enviada no es válida.');
            }

            $idInsumoConsulta = $modeloInsumos->validarIdInsumo($datosInsumos['id_insumo'] ?? null);

            if (!is_int($idInsumoConsulta)) {
                throw new InvalidArgumentException('Seleccione un insumo válido.');
            }

            $responderInsumos([
                'ok' => true,
                'lotes' => $modeloInsumos->consultarLotes($idInsumoConsulta)
            ]);
        }


        /* ================================================= */
        /* CONSULTAR HISTORIAL DE MOVIMIENTOS */
        /* ================================================= */

        /* Consultar fechas, tipo de movimiento y responsable */
        if ($accionInsumos === 'consultar_historial') {

            $filtros = $modeloInsumos->validarFiltrosHistorial($datosInsumos);
            $fechaDesde = $filtros['fecha_desde'] ?? '';
            $fechaHasta = $filtros['fecha_hasta'] ?? '';
            $tipoMovimiento = $filtros['tipo_movimiento'] ?? '';
            $erroresHistorial = $modeloInsumos->obtenerErroresValidacion();

            if ($erroresHistorial) {
                $responderInsumos(
                    [
                        'ok' => false,
                        'mensaje' => 'Revise los filtros del historial.',
                        'errores' => $erroresHistorial
                    ],
                    422
                );
            }

            $responderInsumos(
                [
                    'ok' => true,
                    'movimientos' => $modeloInsumos->consultarHistorial(
                        $fechaDesde !== '' ? $fechaDesde : null,
                        $fechaHasta !== '' ? $fechaHasta : null,
                        $tipoMovimiento !== '' ? $tipoMovimiento : null
                    )
                ]
            );
        }


        /* ================================================= */
        /* SOLICITAR LA VALIDACIÓN AL MODELO */
        /* ================================================= */

        $datosInsumos = $modeloInsumos->validarSolicitudInsumos($datosInsumos, $accionInsumos);
        $erroresInsumos = $modeloInsumos->obtenerErroresValidacion();

        if ($erroresInsumos) {
            $responderInsumos([
                'ok' => false,
                'mensaje' => 'Revise los campos señalados.',
                'errores' => $erroresInsumos
            ], 422);
        }

        $idProveedorInsumos = $datosInsumos['id_proveedor'] ?? null;
        $idLoteMovimiento = $datosInsumos['id_lote'] ?? null;
        $fechaVencimientoLote = $datosInsumos['fecha_vencimiento'] ?? null;
        $tipoMovimientoBD = $datosInsumos['tipo_movimiento_bd'] ?? null;
        $motivoMovimiento = $datosInsumos['motivo_movimiento'] ?? null;
        $operacionExistencias = $datosInsumos['tipo_movimiento'] ?? null;


        /* Obtener el usuario responsable de los movimientos de existencias */
        if ($accionInsumos === 'gestionar_existencias') {
            $usuarioAutenticado = Session\obtenerUsuario();

            $idUsuario = filter_var(
                $usuarioAutenticado['id_usuario'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if (!is_int($idUsuario)) {
                $responderInsumos(
                    [
                        'ok' => false,
                        'mensaje' => 'La sesión no tiene un usuario válido. Inicie sesión nuevamente.'
                    ],
                    401
                );
            }
        }


        /* ================================================= */
        /* REGISTRAR */
        /* ================================================= */

        /* Registrar un nuevo insumo */
        if ($accionInsumos === 'registrar') {

            $tipoControl = $datosInsumos['tipo_control'];

            $modeloInsumos->set_nombreInsumo($datosInsumos['nombre_insumo']);
            $modeloInsumos->set_descripcion($datosInsumos['descripcion'] ?? '');
            $modeloInsumos->set_presentacion($datosInsumos['presentacion'] ?? '');
            $modeloInsumos->set_tipoControl($tipoControl);
            $modeloInsumos->set_stockActual(0);
            $modeloInsumos->set_stockMinimo($datosInsumos['stock_minimo'] ?? 0);

            /* Crear el catálogo sin proveedor, vencimiento ni movimiento inicial */
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

            $tipoControl = $datosInsumos['tipo_control'];

            $modeloInsumos->set_nombreInsumo($datosInsumos['nombre_insumo']);
            $modeloInsumos->set_descripcion($datosInsumos['descripcion'] ?? '');
            $modeloInsumos->set_presentacion($datosInsumos['presentacion'] ?? '');
            $modeloInsumos->set_tipoControl($tipoControl);
            $modeloInsumos->set_stockActual($datosInsumos['stock_actual'] ?? 0);
            $modeloInsumos->set_stockOriginal($datosInsumos['stock_original']);
            $modeloInsumos->set_stockMinimo($datosInsumos['stock_minimo'] ?? 0);

            $resultadoInsumos = $modeloInsumos->modificar();

            $mensajeInsumos =
                'Insumo actualizado correctamente.';
        }


        /* ================================================= */
        /* GESTIONAR EXISTENCIAS */
        /* ================================================= */

        /* Agregar, reponer o descontar con un único método centralizado */
        if ($accionInsumos === 'gestionar_existencias') {

            $modeloInsumos->set_idInsumo($datosInsumos['id_insumo']);

            $datosMovimiento = $modeloInsumos->gestionarExistencias(
                $tipoMovimientoBD,
                (int) $datosInsumos['cantidad_movimiento'],
                $motivoMovimiento,
                (int) $idUsuario,
                $idProveedorInsumos,
                $fechaVencimientoLote,
                $idLoteMovimiento
            );

            $resultadoInsumos = true;

            $mensajeInsumos = match ($operacionExistencias) {
                'AGREGAR' => 'Existencias agregadas correctamente.',
                'REPONER' => 'Existencias repuestas correctamente.',
                'DESCONTAR' => 'Existencias descontadas correctamente.'
            };
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

        $erroresInsumos = $modeloInsumos->obtenerErroresValidacion();

        if ($erroresInsumos) {
            $responderInsumos([
                'ok' => false,
                'mensaje' => 'Revise los campos señalados.',
                'errores' => $erroresInsumos
            ], 422);
        }

        /* Comprobar el resultado del modelo */
        if ($resultadoInsumos !== true) {
            throw new RuntimeException(
                'El modelo no confirmó la operación de insumos.'
            );
        }

        /* Devolver el resultado de la operación AJAX */
        if ($esAjaxInsumos) {
            $respuestaInsumos = [
                'ok' => true,
                'mensaje' => $mensajeInsumos
            ];

            if (isset($datosMovimiento)) {
                $respuestaInsumos['movimiento'] = $datosMovimiento;
            }

            $responderInsumos($respuestaInsumos);
        }

        /* Redirigir cuando se utiliza un formulario sin AJAX */
        header('Location: Index.php?url=insumos');
        exit;
    }


    /* Consultar los datos iniciales para cargar la vista */
    $listaInsumos = $modeloInsumos->consultar();
    $listaProveedoresInsumos = $modeloInsumos->consultarProveedoresActivos();

} catch (JsonException | InvalidArgumentException $e) {

    /* Informar errores de la solicitud */
    $responderInsumos(
        [
            'ok' => false,
            'mensaje' => $e instanceof JsonException
                ? 'Los datos enviados no contienen un JSON válido.'
                : $e->getMessage(),
            'errores' => isset($modeloInsumos) ? $modeloInsumos->obtenerErroresValidacion() : []
        ],
        422
    );

} catch (DomainException $e) {

    /* Informar si las existencias cambiaron durante la edición */
    $respuestaInsumos = [
        'ok' => false,
        'mensaje' => $e->getMessage()
    ];

    if (str_starts_with($e->getMessage(), 'Las existencias cambiaron')) {
        $respuestaInsumos['errores'] = [
            'stock_actual' => $e->getMessage()
        ];
    }

    if (
        str_starts_with($e->getMessage(), 'No hay existencias suficientes') ||
        str_starts_with($e->getMessage(), 'El lote seleccionado no tiene existencias')
    ) {
        $respuestaInsumos['errores'] = [
            'cantidad_movimiento' => $e->getMessage()
        ];
    }

    if (str_starts_with($e->getMessage(), 'Seleccione el lote afectado')) {
        $respuestaInsumos['errores'] = [
            'id_lote' => $e->getMessage()
        ];
    }

    $responderInsumos($respuestaInsumos, 409);

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