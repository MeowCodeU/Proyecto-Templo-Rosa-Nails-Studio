<?php

$moduloActual = $_GET['url'] ?? 'inicio';

require_once __DIR__ . '/Head.php';

?>


<header class="top-header header-compact">

    <?php require_once __DIR__ . '/Usuario.php'; ?>

    <?php require_once __DIR__ . '/Nav.php'; ?>

</header>


<main class="demo-content">