<?php

namespace App\Controller;

use App\Controller\Components\Session;


/* ===================================================== */
/* ENRUTAMIENTO DEL SISTEMA */
/* ===================================================== */

class FrontController
{
    /* Rutas disponibles sin autenticación previa */
    private const RUTAS_PUBLICAS = [
        'login',
        'claveAdmin',
        'registroUsuario'
    ];

    /* Módulo solicitado mediante la URL */
    private string $url;


    /* ================================================= */
    /* RECEPCIÓN DE LA RUTA */
    /* ================================================= */

    public function __construct()
    {
        /* Utilizar la ruta recibida o determinar la ruta inicial */
        if (isset($_GET['url']) && !empty($_GET['url'])) {
            $this->url = trim((string) $_GET['url']);

        } else {
            $this->url = Session::estaAutenticada() ? 'inicio' : 'login';
        }


        /* Aceptar únicamente letras y números en el nombre de la ruta */
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $this->url)) {
            $this->mostrarError404();

            return;
        }


        /* Procesar el cierre de sesión */
        if ($this->url === 'logout') {
            Session::cerrar();

            header('Location: Index.php?url=login');

            exit;
        }


        /* Comprobar el acceso y cargar el controlador solicitado */
        $this->protegerRuta();
        $this->cargarControlador();
    }


    /* ================================================= */
    /* PROTECCIÓN DE LAS RUTAS */
    /* ================================================= */

    private function protegerRuta(): void
    {
        /* Permitir el acceso directo a las rutas públicas */
        if (in_array($this->url, self::RUTAS_PUBLICAS, true)) {
            return;
        }


        /* Permitir el acceso cuando la sesión esté activa y vigente */
        $teniaSesion = Session::estaAutenticada();

        if ($teniaSesion && Session::validarVigencia()) {
            return;
        }


        /* Informar la falta de sesión mediante una respuesta asíncrona */
        if ($this->esPeticionAjax()) {
            http_response_code(401);

            header('Content-Type: application/json; charset=utf-8');

            echo json_encode(
                [
                    'ok' => false,
                    'mensaje' => 'La sesión no se encuentra activa.'
                ],
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        /* Preparar el regreso al formulario de inicio de sesión */
        $destino = 'Index.php?url=login';

        if ($teniaSesion) {
            $destino .= '&sesion=expirada';
        }

        header('Location: ' . $destino);

        exit;
    }


    /* ================================================= */
    /* IDENTIFICACIÓN DE SOLICITUDES ASÍNCRONAS */
    /* ================================================= */

    private function esPeticionAjax(): bool
    {
        /* Comprobar el encabezado utilizado por las solicitudes asíncronas */
        $encabezadoAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';


        /* Reconocer también la acción de búsqueda de personas */
        $buscarPersona = ($_POST['accion'] ?? '') === 'buscar_persona';

        return $encabezadoAjax || $buscarPersona;
    }


    /* ================================================= */
    /* CARGA DEL CONTROLADOR */
    /* ================================================= */

    private function cargarControlador(): void
    {
        $controlador = __DIR__ . '/' . ucfirst($this->url) . 'C.php';

        if (file_exists($controlador)) {
            require_once $controlador;

            return;
        }

        $this->mostrarError404();
    }


    /* ================================================= */
    /* RESPUESTA DE RUTA NO ENCONTRADA */
    /* ================================================= */

    private function mostrarError404(): void
    {
        http_response_code(404);

        echo 'Error 404: controlador no encontrado.';
    }
}