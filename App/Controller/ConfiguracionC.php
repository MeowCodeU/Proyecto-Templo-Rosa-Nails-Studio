<?php

use App\Model\ServicioM;
use App\Model\ReservaInsumosM;
use App\Model\EstadoAgendamientoM;
use App\Model\TipoAgendamientoM;

/* ===================================================== */
/* CONFIGURACIÓN DEL SISTEMA */
/* ===================================================== */

$erroresValidacion = [];

$esPeticionAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

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
    $metodoSolicitud = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $tipoContenido = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));

    if ($metodoSolicitud === 'POST' && $tipoContenido === 'application/json') {
        $datosFormulario = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($datosFormulario)) {
            throw new \InvalidArgumentException('Los datos enviados no tienen un formato válido.');
        }
    }

    if (!in_array($metodoSolicitud, ['GET', 'POST'], true)) {
        header('Allow: GET, POST');
        $responderJson(['ok' => false, 'mensaje' => 'El método solicitado no está permitido.'], 405);
    }

    /* Proteger el formulario de asignación de insumos */
    if (empty($_SESSION['token_reserva_insumos']) || !is_string($_SESSION['token_reserva_insumos'])) {
        $_SESSION['token_reserva_insumos'] = bin2hex(random_bytes(32));
    }
    $tokenReservaInsumos = $_SESSION['token_reserva_insumos'];

    /* Modelos de Configuración */
    $modeloServicio = new ServicioM();
    $modeloEstadoAgendamiento = new EstadoAgendamientoM();
    $modeloTipoAgendamiento = new TipoAgendamientoM();

    /* Procesar acciones enviadas por los formularios */
    if ($metodoSolicitud === 'POST') {
        $accion = $datosFormulario['accion'] ?? null;

        if (!is_string($accion)) {
            throw new \InvalidArgumentException('La acción enviada no es válida.');
        }

        $accion = trim($accion);

        switch ($accion) {

            /* Consultar o guardar el conjunto de insumos de un servicio */
            case 'consultar_insumos_servicio':
                $modeloReserva = new ReservaInsumosM();
                $responderJson([
                    'ok' => true,
                    'insumos' => $modeloReserva->consultarConfiguracion($datosFormulario['id_servicio'] ?? null)
                ]);
                break;

            case 'guardar_insumos_servicio':
                $tokenFormulario = $datosFormulario['token_csrf'] ?? null;
                if (!is_string($tokenFormulario) || !hash_equals($tokenReservaInsumos, $tokenFormulario)) {
                    $responderJson(['ok' => false, 'mensaje' => 'El formulario expiró. Recargue la página antes de guardar.'], 403);
                }

                $modeloReserva = new ReservaInsumosM();
                $modeloReserva->guardarConfiguracion(
                    $datosFormulario['id_servicio'] ?? null, $datosFormulario['insumos'] ?? null
                );
                $responderJson(['ok' => true, 'mensaje' => 'Insumos del servicio actualizados. Se recalcularon las reservas de sus citas pendientes.']);
                break;


            /* ================================================= */
            /* CONSULTAR */
            /* ================================================= */

            case 'consultar':

                if (!$esPeticionAjax) {
                    throw new \InvalidArgumentException('La solicitud enviada no es válida.');
                }

                $catalogo = $datosFormulario['catalogo'] ?? null;

                if (!is_string($catalogo)) {
                    throw new \InvalidArgumentException('Seleccione un catálogo válido.');
                }

                $catalogo = trim($catalogo);

                switch ($catalogo) {
                    case 'servicios':
                        $registros = $modeloServicio->consultar();
                        break;

                    case 'estados_agendamiento':
                        $registros = $modeloEstadoAgendamiento->consultar();
                        break;

                    case 'tipos_agendamiento':
                        $registros = $modeloTipoAgendamiento->consultar();
                        break;

                    default:
                        throw new \InvalidArgumentException('Seleccione un catálogo válido.');
                }

                if (!is_array($registros)) {
                    throw new \RuntimeException('No fue posible consultar el catálogo.');
                }

                $responderJson(['ok' => true, 'catalogo' => $catalogo, 'registros' => array_values($registros)]);
                break;

            /* ================================================= */
            /* REGISTRAR */
            /* ================================================= */

            /* Registrar servicio */
            case 'registrar_servicio':

                $modeloServicio->set_nombreServicio($datosFormulario['nombre_servicio'] ?? '');
                $modeloServicio->set_descripcion($datosFormulario['descripcion'] ?? '');
                $modeloServicio->set_precio($datosFormulario['precio'] ?? '');
                $modeloServicio->set_duracionEstimada($datosFormulario['duracion_estimada'] ?? '');

                $resultado = $modeloServicio->registrar();
                $catalogo = 'servicios';
                $mensaje = 'Servicio registrado correctamente.';

                break;

            /* Registrar estado de agendamiento */
            case 'registrar_estado_agendamiento':

                $modeloEstadoAgendamiento->set_nombreEstado($datosFormulario['nombre_estado'] ?? '');
                $modeloEstadoAgendamiento->set_descripcion($datosFormulario['descripcion'] ?? '');

                $resultado = $modeloEstadoAgendamiento->registrar();
                $catalogo = 'estados_agendamiento';
                $mensaje = 'Estado de agendamiento registrado correctamente.';

                break;

            /* Registrar tipo de agendamiento */
            case 'registrar_tipo_agendamiento':

                $modeloTipoAgendamiento->set_nombreTipoAgendamiento($datosFormulario['nombre_tipo_agendamiento'] ?? '');
                $modeloTipoAgendamiento->set_descripcion($datosFormulario['descripcion'] ?? '');

                $resultado = $modeloTipoAgendamiento->registrar();
                $catalogo = 'tipos_agendamiento';
                $mensaje = 'Tipo de agendamiento registrado correctamente.';

                break;

            /* ================================================= */
            /* MODIFICAR */
            /* ================================================= */

            /* Modificar servicio */
            case 'modificar_servicio':

                $modeloServicio->set_idServicio($datosFormulario['id_servicio'] ?? null);
                $modeloServicio->set_nombreServicio($datosFormulario['nombre_servicio'] ?? '');
                $modeloServicio->set_descripcion($datosFormulario['descripcion'] ?? '');
                $modeloServicio->set_precio($datosFormulario['precio'] ?? '');
                $modeloServicio->set_duracionEstimada($datosFormulario['duracion_estimada'] ?? '');

                $resultado = $modeloServicio->modificar();
                $catalogo = 'servicios';
                $mensaje = 'Servicio modificado correctamente.';

                break;

            /* Modificar estado de agendamiento */
            case 'modificar_estado_agendamiento':

                $modeloEstadoAgendamiento->set_idEstadoAgendamiento($datosFormulario['id_estado_agendamiento'] ?? null);
                $modeloEstadoAgendamiento->set_nombreEstado($datosFormulario['nombre_estado'] ?? '');
                $modeloEstadoAgendamiento->set_descripcion($datosFormulario['descripcion'] ?? '');

                $resultado = $modeloEstadoAgendamiento->modificar();
                $catalogo = 'estados_agendamiento';
                $mensaje = 'Estado de agendamiento modificado correctamente.';

                break;

            /* Modificar tipo de agendamiento */
            case 'modificar_tipo_agendamiento':

                $modeloTipoAgendamiento->set_idTipoAgendamiento($datosFormulario['id_tipo_agendamiento'] ?? null);
                $modeloTipoAgendamiento->set_nombreTipoAgendamiento($datosFormulario['nombre_tipo_agendamiento'] ?? '');
                $modeloTipoAgendamiento->set_descripcion($datosFormulario['descripcion'] ?? '');

                $resultado = $modeloTipoAgendamiento->modificar();
                $catalogo = 'tipos_agendamiento';
                $mensaje = 'Tipo de agendamiento modificado correctamente.';

                break;

            /* ================================================= */
            /* DESACTIVAR */
            /* ================================================= */

            /* Desactivar servicio */
            case 'cambiar_estado_servicio':

                $modeloServicio->set_idServicio($datosFormulario['id_servicio'] ?? null);

                $resultado = $modeloServicio->eliminar();
                $catalogo = 'servicios';
                $mensaje = 'Servicio desactivado correctamente.';

                break;

            /* Desactivar estado de agendamiento */
            case 'cambiar_estado_agendamiento':

                $modeloEstadoAgendamiento->set_idEstadoAgendamiento($datosFormulario['id_estado_agendamiento'] ?? null);

                $resultado = $modeloEstadoAgendamiento->eliminar();
                $catalogo = 'estados_agendamiento';
                $mensaje = 'Estado de agendamiento desactivado correctamente.';

                break;

            /* Desactivar tipo de agendamiento */
            case 'cambiar_estado_tipo_agendamiento':

                $modeloTipoAgendamiento->set_idTipoAgendamiento($datosFormulario['id_tipo_agendamiento'] ?? null);

                $resultado = $modeloTipoAgendamiento->eliminar();
                $catalogo = 'tipos_agendamiento';
                $mensaje = 'Tipo de agendamiento desactivado correctamente.';

                break;

            default:
                throw new \InvalidArgumentException('La acción enviada no es válida.');
        }

        /* Recuperar los errores de validación del modelo utilizado */
        $modeloOperacion = match ($catalogo) {
            'servicios' => $modeloServicio,
            'estados_agendamiento' => $modeloEstadoAgendamiento,
            'tipos_agendamiento' => $modeloTipoAgendamiento
        };
        $erroresValidacion = $modeloOperacion->obtenerErroresValidacion();

        if ($erroresValidacion !== []) {
            throw new \InvalidArgumentException('Revise los campos señalados.');
        }

        /* Confirmar el resultado de la operación */
        if ($resultado === false) {
            throw new \RuntimeException('No fue posible completar la operación.');
        }

        if ($esPeticionAjax) {
            $responderJson(['ok' => true, 'catalogo' => $catalogo, 'mensaje' => $mensaje]);
        }

        /* Conservar la redirección para los envíos tradicionales */
        header('Location: Index.php?url=configuracion');
        exit;
    }

    if ($esPeticionAjax) {
        $responderJson(['ok' => false, 'mensaje' => 'La solicitud enviada no es válida.'], 400);
    }

    /* Consultar los registros activos para mostrarlos en la vista */
    $listaServicios = $modeloServicio->consultar();
    $listaEstadosAgendamiento = $modeloEstadoAgendamiento->consultar();
    $listaTiposAgendamiento = $modeloTipoAgendamiento->consultar();

} catch (\Throwable $e) {

    /* Preparar una respuesta sin exponer información interna */
    $estadoRespuesta = 500;
    $mensaje = 'No fue posible completar la operación de configuración. Intente nuevamente.';

    if ($e instanceof \JsonException) {
        $estadoRespuesta = 422;
        $mensaje = 'Los datos enviados no tienen un formato válido.';
    } elseif ($e instanceof \InvalidArgumentException) {
        $estadoRespuesta = 422;
        $mensaje = $e->getMessage();
    } elseif ($e instanceof \PDOException && (int) ($e->errorInfo[1] ?? 0) === 1062) {
        $estadoRespuesta = 409;
        $mensaje = 'Ya existe un registro con uno de los datos únicos ingresados. Revise los datos del formulario.';
    }

    if ($estadoRespuesta === 500) error_log('ConfiguracionC: ' . $e->getMessage());

    if ($esPeticionAjax) {
        $responderJson(['ok' => false, 'mensaje' => $mensaje, 'errores' => $erroresValidacion], $estadoRespuesta);
    }

    http_response_code($estadoRespuesta);
    exit(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'));
}

/* ===================================================== */
/* CARGA DE LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/ConfiguracionV.php';