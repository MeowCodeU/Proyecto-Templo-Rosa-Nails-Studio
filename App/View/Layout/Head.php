<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <?php
    $tituloPagina = $tituloPagina ?? 'Inicio';
    
    $tituloCompleto =
    $tituloPagina . ' | Templo Rosa';
    ?>
    
    <title>
    <?= htmlspecialchars(
        $tituloCompleto,
        ENT_QUOTES,
        'UTF-8'
    ) ?>
    </title>

    <link
        rel="icon"
        type="image/png"
        href="Assets/img/LogoTR.png"
    >


    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- DataTables CSS -->
    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css"
    >


    <?php if (!empty($usarFullCalendar)): ?>

        <!-- FullCalendar CSS -->
        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css"
        >

    <?php endif; ?>


    <!-- CSS propio -->
    <link
        rel="stylesheet"
        href="Assets/css/styles.css"
    >

</head>


<body>

<div class="bg-overlay"></div>