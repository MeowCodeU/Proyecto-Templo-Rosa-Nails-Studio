<!DOCTYPE html>
<html lang="es">

<!-- ===================================================== -->
<!-- CONFIGURACIÓN DEL DOCUMENTO -->
<!-- ===================================================== -->

<head>

    <!-- Codificación y adaptación a dispositivos -->
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <!-- Título de la página -->
    <?php

    $tituloPagina = $tituloPagina ?? 'Inicio';
    $tituloCompleto = $tituloPagina . ' | Templo Rosa';

    ?>

    <title><?= htmlspecialchars($tituloCompleto, ENT_QUOTES, 'UTF-8'); ?></title>


    <!-- Ícono de la página -->
    <link
        rel="icon"
        type="image/png"
        href="Assets/img/LogoTR.png"
    >


    <!-- ===================================================== -->
    <!-- HOJAS DE ESTILO -->
    <!-- ===================================================== -->

    <!-- Bootstrap CSS -->
    <link
        href="Assets/libs/bootstrap/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="Assets/libs/bootstrap-icons/font/bootstrap-icons.min.css"
    >


    <!-- DataTables CSS -->
    <link
        rel="stylesheet"
        href="Assets/libs/datatables/css/dataTables.bootstrap5.min.css"
    >


    <!-- Estilos del calendario cuando la vista los requiere -->
    <?php if (!empty($usarFullCalendar)): ?>

        <link
            rel="stylesheet"
            href="Assets/libs/fullcalendar/main.min.css"
        >

    <?php endif; ?>


    <!-- Estilos generales del sistema -->
    <link
        rel="stylesheet"
        href="Assets/css/styles.css"
    >


    <!-- SweetAlert2 CSS -->
    <link
        rel="stylesheet"
        href="Assets/libs/sweetalert2/sweetalert2.min.css"
    >


    <!-- Estilos propios de los avisos y confirmaciones -->
    <link
        rel="stylesheet"
        href="Assets/css/alertas.css?v=<?= filemtime(__DIR__ . '/../../../Assets/css/alertas.css'); ?>"
    >

</head>


<!-- ===================================================== -->
<!-- INICIO DE LA ESTRUCTURA VISUAL -->
<!-- ===================================================== -->

<body>

    <!-- Capa visual del fondo -->
    <div class="bg-overlay"></div>