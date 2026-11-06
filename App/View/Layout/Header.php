<!-- ===================================================== -->
<!-- DATOS DE LA VISTA -->
<!-- ===================================================== -->

<?php

$moduloActual = $_GET['url'] ?? 'inicio';

require_once __DIR__ . '/Head.php';

?>


<!-- ===================================================== -->
<!-- ENCABEZADO DEL SISTEMA -->
<!-- ===================================================== -->

<header class="top-header header-compact">

    <!-- Usuario y opciones de sesión -->
    <?php require_once __DIR__ . '/Usuario.php'; ?>


    <!-- Navegación del sistema -->
    <?php require_once __DIR__ . '/Nav.php'; ?>

</header>


<!-- ===================================================== -->
<!-- CONTENIDO PRINCIPAL -->
<!-- ===================================================== -->

<main class="demo-content">