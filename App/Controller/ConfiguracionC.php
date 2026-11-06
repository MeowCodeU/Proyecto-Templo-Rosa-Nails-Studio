<?php

use App\Model\ServicioM;
use App\Model\EstadoAgendamientoM;
use App\Model\TipoAgendamientoM;


/* ===================================================== */
/* CONFIGURACIÓN DEL SISTEMA */
/* ===================================================== */

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

    $textoFormulario = static function (string $campo, bool $obligatorio = false) use ($datosFormulario): string {
        $valor = $datosFormulario[$campo] ?? '';

        if (!is_string($valor)) {
            throw new \InvalidArgumentException('Los datos enviados no son válidos.');
        }

        $valor = trim($valor);

        if ($obligatorio && $valor === '') {
            throw new \InvalidArgumentException('Complete los campos obligatorios del formulario.');
        }

        return $valor;
    };

    $identificadorFormulario = static function (string $campo) use ($datosFormulario): int {
        $identificador = filter_var($datosFormulario[$campo] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        if ($identificador === false) {
            throw new \InvalidArgumentException('Seleccione un registro válido.');
        }

        return $identificador;
    };


    /* Modelos de Configuración */
    $modeloServicio = new ServicioM();
    $modeloEstadoAgendamiento = new EstadoAgendamientoM();
    $modeloTipoAgendamiento = new TipoAgendamientoM();


    /* Procesar acciones enviadas por los formularios */
    if ($metodoSolicitud === 'POST') {
        $accion = $textoFormulario('accion', true);

        switch ($accion) {

            /* ================================================= */
            /* CONSULTAR */
            /* ================================================= */

            case 'consultar':

                if (!$esPeticionAjax) {
                    throw new \InvalidArgumentException('La solicitud enviada no es válida.');
                }

                $catalogo = $textoFormulario('catalogo', true);

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

                $modeloServicio->set_nombreServicio($textoFormulario('nombre_servicio', true));
                $modeloServicio->set_descripcion($textoFormulario('descripcion'));
                $modeloServicio->set_precio($textoFormulario('precio', true));
                $modeloServicio->set_duracionEstimada($textoFormulario('duracion_estimada'));

                $resultado = $modeloServicio->registrar();
                $catalogo = 'servicios';
                $mensaje = 'Servicio registrado correctamente.';

                break;

            /* Registrar estado de agendamiento */
            case 'registrar_estado_agendamiento':

                $modeloEstadoAgendamiento->set_nombreEstado($textoFormulario('nombre_estado', true));
                $modeloEstadoAgendamiento->set_descripcion($textoFormulario('descripcion'));

                $resultado = $modeloEstadoAgendamiento->registrar();
                $catalogo = 'estados_agendamiento';
                $mensaje = 'Estado de agendamiento registrado correctamente.';

                break;

            /* Registrar tipo de agendamiento */
            case 'registrar_tipo_agendamiento':

                $modeloTipoAgendamiento->set_nombreTipoAgendamiento($textoFormulario('nombre_tipo_agendamiento', true));
                $modeloTipoAgendamiento->set_descripcion($textoFormulario('descripcion'));

                $resultado = $modeloTipoAgendamiento->registrar();
                $catalogo = 'tipos_agendamiento';
                $mensaje = 'Tipo de agendamiento registrado correctamente.';

                break;


            /* ================================================= */
            /* MODIFICAR */
            /* ================================================= */

            /* Modificar servicio */
            case 'modificar_servicio':

                $modeloServicio->set_idServicio($identificadorFormulario('id_servicio'));
                $modeloServicio->set_nombreServicio($textoFormulario('nombre_servicio', true));
                $modeloServicio->set_descripcion($textoFormulario('descripcion'));
                $modeloServicio->set_precio($textoFormulario('precio', true));
                $modeloServicio->set_duracionEstimada($textoFormulario('duracion_estimada'));

                $resultado = $modeloServicio->modificar();
                $catalogo = 'servicios';
                $mensaje = 'Servicio modificado correctamente.';

                break;

            /* Modificar estado de agendamiento */
            case 'modificar_estado_agendamiento':

                $modeloEstadoAgendamiento->set_idEstadoAgendamiento($identificadorFormulario('id_estado_agendamiento'));
                $modeloEstadoAgendamiento->set_nombreEstado($textoFormulario('nombre_estado', true));
                $modeloEstadoAgendamiento->set_descripcion($textoFormulario('descripcion'));

                $resultado = $modeloEstadoAgendamiento->modificar();
                $catalogo = 'estados_agendamiento';
                $mensaje = 'Estado de agendamiento modificado correctamente.';

                break;

            /* Modificar tipo de agendamiento */
            case 'modificar_tipo_agendamiento':

                $modeloTipoAgendamiento->set_idTipoAgendamiento($identificadorFormulario('id_tipo_agendamiento'));
                $modeloTipoAgendamiento->set_nombreTipoAgendamiento($textoFormulario('nombre_tipo_agendamiento', true));
                $modeloTipoAgendamiento->set_descripcion($textoFormulario('descripcion'));

                $resultado = $modeloTipoAgendamiento->modificar();
                $catalogo = 'tipos_agendamiento';
                $mensaje = 'Tipo de agendamiento modificado correctamente.';

                break;


            /* ================================================= */
            /* DESACTIVAR */
            /* ================================================= */

            /* Desactivar servicio */
            case 'cambiar_estado_servicio':

                $modeloServicio->set_idServicio($identificadorFormulario('id_servicio'));

                $resultado = $modeloServicio->eliminar();
                $catalogo = 'servicios';
                $mensaje = 'Servicio desactivado correctamente.';

                break;

            /* Desactivar estado de agendamiento */
            case 'cambiar_estado_agendamiento':

                $modeloEstadoAgendamiento->set_idEstadoAgendamiento($identificadorFormulario('id_estado_agendamiento'));

                $resultado = $modeloEstadoAgendamiento->eliminar();
                $catalogo = 'estados_agendamiento';
                $mensaje = 'Estado de agendamiento desactivado correctamente.';

                break;

            /* Desactivar tipo de agendamiento */
            case 'cambiar_estado_tipo_agendamiento':

                $modeloTipoAgendamiento->set_idTipoAgendamiento($identificadorFormulario('id_tipo_agendamiento'));

                $resultado = $modeloTipoAgendamiento->eliminar();
                $catalogo = 'tipos_agendamiento';
                $mensaje = 'Tipo de agendamiento desactivado correctamente.';

                break;


            default:
                throw new \InvalidArgumentException('La acción enviada no es válida.');
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

    if ($esPeticionAjax) {
        $responderJson(['ok' => false, 'mensaje' => $mensaje], $estadoRespuesta);
    }

    http_response_code($estadoRespuesta);
    exit($mensaje);
}


/* ===================================================== */
/* CARGA DE LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/ConfiguracionV.php';