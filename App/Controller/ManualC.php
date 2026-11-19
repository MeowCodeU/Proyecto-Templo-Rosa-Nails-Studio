<?php

use App\Components\Session;


/* ===================================================== */
/* MANUAL DE USUARIO */
/* ===================================================== */

/* La ruta permanece protegida por el FrontController */
if (!Session\estaAutenticada()) {
    header('Location: Index.php?url=login');
    exit;
}

if (
    !in_array(
        $_SERVER['REQUEST_METHOD'] ?? 'GET',
        ['GET', 'HEAD'],
        true
    )
) {
    header('Allow: GET, HEAD');
    http_response_code(405);

    exit('Método no permitido.');
}

header('Cache-Control: private, no-store');


/* ===================================================== */
/* GUÍAS COMUNES */
/* ===================================================== */

/* Las guías de módulos se incorporarán con sus permisos correspondientes */
$guiasManual = [
    'bienvenida' => [
        'titulo' => 'Conoce el manual',
        'descripcion' =>
            'Encuentra orientación para utilizar Templo Rosa.',
        'pasos' => [
            'Utiliza Explora para consultar las guías disponibles.',
            'Selecciona Ver guía para abrir las instrucciones de un tema.',
            'Regresa al listado cuando necesites consultar otra guía.'
        ]
    ],

    'navegacion' => [
        'titulo' => 'Encuentra una guía',
        'descripcion' =>
            'Busca un tema y abre sus instrucciones.',
        'pasos' => [
            'Selecciona Explora en el menú superior.',
            'Escribe una palabra del título o la descripción en el buscador.',
            'Selecciona Ver guía en uno de los resultados.',
            'Utiliza Volver a Explora para regresar al listado completo.'
        ]
    ],

    'regresar' => [
        'titulo' => 'Vuelve al sistema',
        'descripcion' =>
            'Continúa trabajando después de consultar el manual.',
        'pasos' => [
            'Localiza Volver al sistema en la cabecera.',
            'Selecciona ese enlace para abrir el Inicio de Templo Rosa.',
            'Desde el sistema podrás abrir nuevamente el manual cuando lo necesites.'
        ]
    ]
];


/* ===================================================== */
/* SELECCIÓN DE LA GUÍA */
/* ===================================================== */

$guiaManual = null;
$mensajeManual = '';
$claveGuiaManual = $_GET['guia'] ?? '';

/* Resolver únicamente las guías incluidas en el catálogo disponible */
if ($claveGuiaManual !== '') {
    if (
        !is_string($claveGuiaManual) ||
        !isset($guiasManual[$claveGuiaManual])
    ) {
        http_response_code(404);

        $mensajeManual =
            'La guía solicitada no está disponible.';

    } else {
        $guiaManual = $guiasManual[$claveGuiaManual];
    }
}


/* ===================================================== */
/* PREPARACIÓN DE LA VISTA */
/* ===================================================== */

$escaparManual = static function ($valor): string {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

/* Actualizar la versión de los recursos cuando cambien sus archivos */
$rutaCssManual =
    __DIR__ . '/../../Assets/css/manual.css';

$rutaJsManual =
    __DIR__ . '/../../Assets/js/manual.js';

$versionCssManual = is_file($rutaCssManual)
    ? filemtime($rutaCssManual)
    : 1;

$versionJsManual = is_file($rutaJsManual)
    ? filemtime($rutaJsManual)
    : 1;


/* Cargar la página del manual */
require_once __DIR__ . '/../View/ManualV.php';