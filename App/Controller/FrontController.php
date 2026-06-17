<?php

namespace App\Controller;

class FrontController
{
    /* Atributo que guarda el módulo solicitado en la URL */
    private $url;

    /* Constructor: recibe la url y carga el controlador correspondiente */
    public function __construct()
    {
        if (isset($_GET['url']) && !empty($_GET['url'])) {
            $this->url = $_GET['url'];
        } else {
            $this->url = 'clientes';
        }

        $this->cargarControlador();
    }

    /* Cargar controlador: busca el archivo del controlador solicitado */
    private function cargarControlador()
    {
        $controlador = __DIR__ . '/' . ucfirst($this->url) . 'C.php';

        if (file_exists($controlador)) {
            require_once $controlador;
        } else {
            echo "Error 404: controlador no encontrado.";
        }
    }
}