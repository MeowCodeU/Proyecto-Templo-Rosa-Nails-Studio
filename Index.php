<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/App/Components/Session.php';
require_once __DIR__ . '/App/Components/RestriccionesRol.php';

use App\Components\Session;
use App\Controller\FrontController;

/* Zona horaria utilizada por el sistema */
date_default_timezone_set('America/Caracas');

/* Iniciar o recuperar la sesión antes de cargar cualquier controlador */
Session\iniciar();

/* Recibir la ruta solicitada y cargar el controlador correspondiente */
$frontController = new FrontController();