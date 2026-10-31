<?php

namespace App\Controller;


use App\Controller\Components\Session;


class FrontController
{
    /*
     * Rutas que pueden abrirse sin que
     * exista un usuario autenticado.
     */
    private const RUTAS_PUBLICAS = [
        'login',
        'claveAdmin',
        'registroUsuario'
    ];


    /*
     * Módulo solicitado mediante la URL.
     */
    private string $url;


    /*
     * Obtiene la ruta solicitada, comprueba
     * la sesión y carga el controlador.
     */
    public function __construct()
    {
        /*
         * Si se recibió una ruta,
         * se utiliza esa ruta.
         */
        if (
            isset($_GET['url']) &&
            !empty($_GET['url'])
        ) {
            $this->url = trim(
                (string) $_GET['url']
            );

        } else {

            /*
             * Si no se especificó una ruta:
             *
             * - Usuario autenticado: Inicio.
             * - Usuario no autenticado: Login.
             */
            $this->url =
                Session::estaAutenticada()
                    ? 'inicio'
                    : 'login';
        }


        /*
         * Solamente se permiten letras
         * y números en el nombre de la ruta.
         */
        if (
            !preg_match(
                '/^[a-zA-Z][a-zA-Z0-9]*$/',
                $this->url
            )
        ) {
            $this->mostrarError404();

            return;
        }


        /*
         * Procesar el cierre de sesión.
         */
        if ($this->url === 'logout') {

            Session::cerrar();

            header(
                'Location: Index.php?url=login'
            );

            exit;
        }


        /*
         * Comprobar si el usuario puede
         * continuar hacia la ruta solicitada.
         */
        $this->protegerRuta();


        /*
         * Cargar el controlador solicitado.
         */
        $this->cargarControlador();
    }


    /*
     * Impide acceder a los módulos internos
     * cuando no existe una sesión vigente.
     */
    private function protegerRuta(): void
    {
        /*
         * Las rutas públicas no requieren
         * autenticación previa.
         */
        if (
            in_array(
                $this->url,
                self::RUTAS_PUBLICAS,
                true
            )
        ) {
            return;
        }


        /*
         * Permite continuar cuando existe
         * una sesión activa y vigente.
         */
        $teniaSesion =
            Session::estaAutenticada();


        if (
            $teniaSesion &&
            Session::validarVigencia()
        ) {
            return;
        }


        /*
         * Si la solicitud fue realizada
         * mediante AJAX, se devuelve JSON.
         */
        if ($this->esPeticionAjax()) {

            http_response_code(401);

            header(
                'Content-Type: application/json; charset=utf-8'
            );

            echo json_encode(
                [
                    'ok' => false,
                    'mensaje' =>
                        'La sesión no se encuentra activa.'
                ],
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        /*
         * Una navegación normal vuelve
         * al formulario de inicio de sesión.
         */
        $destino =
            'Index.php?url=login';


        /*
         * Permite informar que la sesión
         * terminó por inactividad.
         */
        if ($teniaSesion) {
            $destino .=
                '&sesion=expirada';
        }


        header(
            'Location: ' . $destino
        );

        exit;
    }


    /*
     * Determina si la solicitud procede
     * de una operación asíncrona.
     */
    private function esPeticionAjax(): bool
    {
        /*
         * Solicitud identificada mediante
         * el encabezado convencional AJAX.
         */
        $encabezadoAjax =
            isset(
                $_SERVER[
                    'HTTP_X_REQUESTED_WITH'
                ]
            ) &&
            strtolower(
                (string) $_SERVER[
                    'HTTP_X_REQUESTED_WITH'
                ]
            ) === 'xmlhttprequest';


        /*
         * Actualmente esta acción se realiza
         * mediante fetch() en seguridad.js.
         */
        $buscarPersona =
            ($_POST['accion'] ?? '')
            === 'buscar_persona';


        return
            $encabezadoAjax ||
            $buscarPersona;
    }


    /*
     * Localiza y carga el controlador
     * correspondiente a la ruta.
     */
    private function cargarControlador(): void
    {
        $controlador =
            __DIR__ . '/' .
            ucfirst($this->url) .
            'C.php';


        if (file_exists($controlador)) {

            require_once $controlador;

            return;
        }


        $this->mostrarError404();
    }


    /*
     * Devuelve una respuesta cuando
     * el controlador solicitado no existe.
     */
    private function mostrarError404(): void
    {
        http_response_code(404);

        echo 'Error 404: controlador no encontrado.';
    }
}