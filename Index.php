<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Controller\Components\Session;
use App\Controller\FrontController;

/*Zona horaria utilizada por el sistema.*/
date_default_timezone_set(
    'America/Caracas'
);

/*Inicia o recupera la sesión antes de cargar cualquier controlador.*/
Session::iniciar();

/*Recibe la ruta solicitada y carga el controlador correspondiente.*/
$frontController = new FrontController();

