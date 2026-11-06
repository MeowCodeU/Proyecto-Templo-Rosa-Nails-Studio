<?php

use App\Model\ReportesM;
use App\Reportes\ReportePDF;


/* ===================================================== */
/* GESTIÓN DE REPORTES */
/* ===================================================== */

/* Identificar las solicitudes asíncronas */
$esAjaxReportes = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';


/* Enviar la respuesta y finalizar la solicitud */
$responderReportes = static function (array $datos, int $codigo = 200): void {
    http_response_code($codigo);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    exit;
};


try {
    $modeloReportes = new ReportesM();


    /* ================================================= */
    /* CONSULTAS GUARDADAS */
    /* ================================================= */

    $consultasGuardadas = $_SESSION['reportes_consultas'] ?? [];


    /* Retirar las consultas cuyo tiempo de disponibilidad haya vencido */
    foreach ($consultasGuardadas as $clave => $consulta) {
        if (($consulta['vence'] ?? 0) < time()) {
            unset($consultasGuardadas[$clave]);
        }
    }

    $_SESSION['reportes_consultas'] = $consultasGuardadas;


    /* ================================================= */
    /* DESCARGA DEL REPORTE */
    /* ================================================= */

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['pdf'])) {
        $token = is_string($_GET['pdf']) ? $_GET['pdf'] : '';
        $consulta = $consultasGuardadas[$token] ?? null;


        /* Comprobar que la consulta siga disponible para descargar */
        if (!$consulta) {
            http_response_code(410);

            $errorReportes = 'La consulta para descargar venció. Vuelva a Reportes y consulte nuevamente.';

            require __DIR__ . '/../View/ReportesV.php';

            return;
        }

        $reporte = $consulta['reporte'];


        /* Liberar la sesión mientras se construye el documento */
        session_write_close();


        /* Generar el PDF con los mismos resultados presentados en pantalla */
        $exportador = new ReportePDF();
        $documento = $exportador->generar($reporte);

        $nombreArchivo = 'Templo_Rosa_' . $reporte['tipo'] . '_' . date('Ymd_His') . '.pdf';


        /* Enviar el documento como archivo adjunto */
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Content-Length: ' . strlen($documento));
        header('Cache-Control: private, no-store');

        echo $documento;

        exit;
    }


    /* ================================================= */
    /* CONSULTA DEL REPORTE */
    /* ================================================= */

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        /* Recibir los filtros enviados por el formulario */
        $entrada = json_decode(
            file_get_contents('php://input'),
            true,
            32,
            JSON_THROW_ON_ERROR
        );


        /* Comprobar la acción y la estructura de la solicitud */
        if (
            !is_array($entrada) ||
            ($entrada['accion'] ?? '') !== 'consultar' ||
            !isset($entrada['filtros']) ||
            !is_array($entrada['filtros']) ||
            array_diff(array_keys($entrada), ['accion', 'filtros'])
        ) {
            throw new InvalidArgumentException('La solicitud del reporte no es válida.');
        }


        /* Validar los filtros y obtener los resultados mediante el modelo */
        $reporte = $modeloReportes->generar($entrada['filtros']);
        $token = null;


        /* Conservar hasta tres consultas durante quince minutos */
        if ($reporte['total'] > 0) {
            while (count($_SESSION['reportes_consultas']) >= 3) {
                array_shift($_SESSION['reportes_consultas']);
            }

            $token = bin2hex(random_bytes(24));

            $_SESSION['reportes_consultas'][$token] = [
                'vence' => time() + 900,
                'reporte' => $reporte
            ];
        }


        /* Entregar el reporte y su identificador de descarga */
        $responderReportes([
            'ok' => true,
            'reporte' => $reporte,
            'token_pdf' => $token
        ]);
    }


    /* ================================================= */
    /* CARGA DE LA VISTA */
    /* ================================================= */

    /* Aceptar únicamente los métodos disponibles del módulo */
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET, POST');

        $responderReportes([
            'ok' => false,
            'mensaje' => 'Método no permitido.'
        ], 405);
    }


    /* Preparar los tipos de reporte y sus filtros */
    $configuracionReportes = $modeloReportes->configuracion();

    require __DIR__ . '/../View/ReportesV.php';

} catch (InvalidArgumentException | JsonException $e) {

    /* ================================================= */
    /* ERRORES EN LA SOLICITUD */
    /* ================================================= */

    $mensaje = $e instanceof JsonException
        ? 'Los datos enviados no contienen un JSON válido.'
        : $e->getMessage();

    $responderReportes([
        'ok' => false,
        'mensaje' => $mensaje
    ], 422);

} catch (Throwable $e) {

    /* ================================================= */
    /* ERRORES DE GENERACIÓN */
    /* ================================================= */

    /* Registrar el detalle técnico sin mostrarlo al usuario */
    error_log('Reportes: ' . $e->getMessage());


    /* Informar el error correspondiente a una consulta asíncrona */
    if ($esAjaxReportes) {
        $responderReportes([
            'ok' => false,
            'mensaje' => 'No fue posible generar el reporte. Intente nuevamente.'
        ], 500);
    }


    /* Mostrar el aviso dentro de la vista */
    http_response_code(500);

    $errorReportes = 'No fue posible abrir el reporte. Intente nuevamente desde el módulo.';

    require __DIR__ . '/../View/ReportesV.php';
}