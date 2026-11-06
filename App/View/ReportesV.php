<?php

$tituloPagina = 'Gestión de Reportes';

require_once __DIR__ . '/Layout/Header.php';

?>

<!-- ===================================================== -->
<!-- DATOS DE LA VISTA -->
<!-- ===================================================== -->

<!-- Escape de los valores mostrados en el HTML -->
<?php

$escaparReporte = static function ($valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

?>

<!-- Categorías e iconos del sistema -->
<?php

$categoriasReportes = [
    'Agenda' => 'bi-calendar-heart',
    'Servicios' => 'servicios',
    'Clientes' => 'bi-person-hearts',
    'Especialistas' => 'bi-person-badge',
    'Insumos' => 'bi-box2-heart',
    'Proveedores' => 'proveedores',
    'General' => 'bi-bar-chart-line'
];

$grupoInicial = 'Agenda';

$camposReportes = [
    'cliente' => 'Cliente',
    'especialista' => 'Especialista',
    'servicio' => 'Servicio',
    'estado' => 'Estado de cita',
    'tipo' => 'Tipo de cita',
    'insumo' => 'Insumo',
    'proveedor' => 'Proveedor',
    'estado_registro' => 'Estado del registro',
    'disponibilidad' => 'Disponibilidad',
    'estado_bloqueo' => 'Estado del bloqueo'
];

?>


<!-- ===================================================== -->
<!-- GESTIÓN DE REPORTES -->
<!-- ===================================================== -->

<section class="module-card reportes-module">

    <!-- Título del módulo -->
    <div class="module-header">

        <h2>
            <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
            Gestión de Reportes
        </h2>

    </div>

    <!-- Aviso cuando el módulo no puede mostrar los reportes -->
    <?php if (isset($errorReportes)): ?>

        <div class="reportes-vacio" role="alert">

            <i class="bi bi-info-circle" aria-hidden="true"></i>

            <p>
                <?= $escaparReporte($errorReportes) ?>
            </p>

            <a
                href="Index.php?url=inicio"
                class="btn-templo-primary"
            >
                <i class="bi bi-house-heart" aria-hidden="true"></i>
                Volver al inicio
            </a>

        </div>

    <?php else: ?>

        <!-- ===================================================== -->
        <!-- CATEGORÍAS DE REPORTES -->
        <!-- ===================================================== -->

        <nav
            class="reportes-categorias"
            aria-label="Categorías de reportes"
        >

            <?php foreach ($categoriasReportes as $grupo => $icono): ?>

                <button
                    type="button"
                    class="reportes-categoria<?= $grupo === $grupoInicial ? ' active' : '' ?>"
                    data-grupo="<?= $escaparReporte($grupo) ?>"
                    aria-pressed="<?= $grupo === $grupoInicial ? 'true' : 'false' ?>"
                >
                    <?php if ($icono === 'servicios'): ?>

                        <!-- Icono personalizado de servicios -->
                        <span
                            class="servicio-icono"
                            aria-hidden="true"
                        >
                            <svg viewBox="0 0 64 64">
                                <rect
                                    x="24"
                                    y="6"
                                    width="16"
                                    height="20"
                                    rx="2"
                                ></rect>

                                <path d="M28 10V22"></path>
                                <path d="M32 10V22"></path>
                                <path d="M36 10V22"></path>
                                <path d="M24 28H29"></path>
                                <path d="M35 28H40"></path>
                                <path d="M29 28C29 30 28 31 26 31"></path>
                                <path d="M35 28C35 30 36 31 38 31"></path>

                                <path
                                    d="M18 31H46L44 50
                                       C43.7 53 41.4 55 38.4 55H25.6
                                       C22.6 55 20.3 53 20 50L18 31Z"
                                ></path>

                                <path d="M22 50C26 52 38 52 42 50"></path>

                                <path
                                    class="bottle-heart"
                                    d="M32 45
                                       C31 43.5 28 41.8 28 39.5
                                       C28 37.7 29.4 36.5 31 36.5
                                       C32 36.5 32.8 37 33.3 37.9
                                       C33.8 37 34.6 36.5 35.6 36.5
                                       C37.2 36.5 38.6 37.7 38.6 39.5
                                       C38.6 41.8 35.6 43.5 32 46Z"
                                ></path>
                            </svg>
                        </span>

                    <?php elseif ($icono === 'proveedores'): ?>

                        <!-- Icono personalizado de proveedores -->
                        <span
                            class="title-icon custom-proveedores"
                            aria-hidden="true"
                        >
                            <svg viewBox="0 0 64 64">
                                <path
                                    d="M4 20A3 3 0 0 1 7 17H42
                                       A3 3 0 0 1 45 20V27H52
                                       A4 4 0 0 1 55.5 29.2
                                       L60.5 36.5A4 4 0 0 1 61 38.8
                                       V48A3 3 0 0 1 58 51H54"
                                ></path>

                                <path
                                    d="M4 20V48
                                       A3 3 0 0 0 7 51H12"
                                ></path>

                                <path d="M26 51H40"></path>
                                <path d="M45 27V44"></path>
                                <path d="M45 30H54L59 38H45V30Z" stroke-width="1.5"></path>

                                <circle cx="19" cy="51" r="7"></circle>
                                <circle cx="47" cy="51" r="7"></circle>

                                <path
                                    class="truck-heart"
                                    d="M24.5 35
                                       C23.5 33 21 32.5 21 30.5
                                       C21 29 22.5 28 24.5 28
                                       C25.5 28 26.5 28.5 27 29.5
                                       C27.5 28.5 28.5 28 29.5 28
                                       C31.5 28 33 29 33 30.5
                                       C33 32.5 30.5 33 29.5 35
                                       L27 37.5Z"
                                ></path>
                            </svg>
                        </span>

                    <?php else: ?>

                        <!-- Icono correspondiente a las demás categorías -->
                        <i
                            class="bi <?= $escaparReporte($icono) ?>"
                            aria-hidden="true"
                        ></i>

                    <?php endif; ?>

                    <span>
                        <?= $escaparReporte($grupo) ?>
                    </span>
                </button>

            <?php endforeach; ?>

        </nav>

        <!-- Avisos de consulta por encima de los filtros y resultados -->
        <p
            id="reporteMensaje"
            class="reportes-mensaje"
            role="status"
            aria-live="polite"
            aria-atomic="true"
        >
            Seleccione un tipo de reporte y pulse Consultar.
        </p>

        <div class="reportes-layout">

            <!-- ===================================================== -->
            <!-- TIPO DE REPORTE Y FILTROS -->
            <!-- ===================================================== -->

            <aside
                class="reportes-panel reportes-filtros"
                aria-labelledby="tituloFiltrosReportes"
            >

                <h3 id="tituloFiltrosReportes">
                    <i class="bi bi-search-heart-fill" aria-hidden="true"></i>
                    Tipo y filtros
                </h3>

                <form
                    id="formReportes"
                    method="post"
                    action="Index.php?url=reportes"
                >

                    <!-- Selección del tipo de reporte -->
                    <div class="reportes-campo reportes-ancho">

                        <label for="reporteTipo">
                            Tipo de reporte
                            <span aria-hidden="true">*</span>
                        </label>

                        <select
                            id="reporteTipo"
                            name="reporte"
                            required
                            aria-describedby="reporteDescripcion"
                        >
                            <option value="">Seleccione un reporte</option>

                            <?php
                            foreach ($configuracionReportes['reportes'] as $clave => $definicion):
                                if ($definicion['grupo'] !== $grupoInicial) {
                                    continue;
                                }
                            ?>

                                <option value="<?= $escaparReporte($clave) ?>">
                                    <?= $escaparReporte($definicion['titulo']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <!-- Rango de fechas de la consulta -->
                    <div class="reportes-campo" data-fecha hidden>

                        <label for="reporteInicio">
                            Desde
                            <span aria-hidden="true">*</span>
                        </label>

                        <input
                            type="date"
                            id="reporteInicio"
                            name="fecha_inicio"
                            disabled
                        >

                    </div>

                    <div class="reportes-campo" data-fecha hidden>

                        <label for="reporteFin">
                            Hasta
                            <span aria-hidden="true">*</span>
                        </label>

                        <input
                            type="date"
                            id="reporteFin"
                            name="fecha_fin"
                            disabled
                        >

                    </div>

                    <!-- Filtros que se habilitan según el reporte seleccionado -->
                    <?php foreach ($camposReportes as $campo => $etiqueta): ?>

                        <div
                            class="reportes-campo"
                            data-filtro="<?= $escaparReporte($campo) ?>"
                            hidden
                        >

                            <label
                                id="etiqueta-reporte-<?= $escaparReporte($campo) ?>"
                                for="reporte-<?= $escaparReporte($campo) ?>"
                            >
                                <?= $escaparReporte($etiqueta) ?>
                            </label>

                            <select
                                id="reporte-<?= $escaparReporte($campo) ?>"
                                name="<?= $escaparReporte($campo) ?>"
                                disabled
                            >
                                <option value="">Todos</option>
                            </select>

                        </div>

                    <?php endforeach; ?>

                    <!-- Descripción del reporte seleccionado -->
                    <p
                        id="reporteDescripcion"
                        class="reportes-descripcion reportes-ancho"
                    >
                        Elija el tipo de reporte para ver sus filtros disponibles.
                    </p>

                    <!-- Información de reportes pendientes de habilitar -->
                    <div
                        id="reportePendiente"
                        class="reportes-pendiente reportes-ancho"
                        hidden
                    >

                        <i class="bi bi-hourglass-split" aria-hidden="true"></i>

                        <p id="reportePendienteTexto"></p>

                    </div>

                    <!-- Botones de consulta y limpieza -->
                    <div class="reportes-form-acciones reportes-ancho">

                        <button
                            id="consultarReporte"
                            type="submit"
                            class="btn-templo-primary"
                            disabled
                        >
                            <i class="bi bi-search-heart-fill" aria-hidden="true"></i>
                            Consultar
                        </button>

                        <button
                            id="limpiarReporte"
                            type="button"
                            class="btn-templo-secondary"
                        >
                            <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                            Limpiar
                        </button>

                    </div>

                </form>

            </aside>


            <!-- ===================================================== -->
            <!-- RESULTADOS DE LA CONSULTA -->
            <!-- ===================================================== -->

            <div
                class="reportes-panel reportes-resultados"
                id="reporteResultados"
                aria-busy="false"
            >

                <!-- Título del resultado y descarga en PDF -->
                <div class="reportes-resultados-cabecera">

                    <div class="reportes-resultados-titulo">

                        <i class="bi bi-clipboard2-heart-fill" aria-hidden="true"></i>

                        <div>

                            <h3 id="reporteTitulo">Resultado de la consulta</h3>

                            <p
                                id="reporteFecha"
                                class="reportes-fecha"
                            ></p>

                        </div>

                    </div>

                    <button
                        id="descargarReporte"
                        type="button"
                        class="btn-templo-primary"
                        disabled
                    >
                        <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>
                        PDF
                    </button>

                </div>

                <!-- Filtros aplicados y resumen del reporte -->
                <p
                    id="reporteFiltrosAplicados"
                    class="reportes-aplicados"
                ></p>

                <div
                    id="reporteResumen"
                    class="reportes-resumen"
                    aria-label="Resumen del reporte"
                ></div>

                <!-- Tabla de resultados y mensaje inicial -->
                <div
                    class="reportes-tabla-contenedor"
                    tabindex="0"
                    aria-label="Tabla de resultados del reporte"
                >

                    <table
                        id="tablaReportes"
                        class="reportes-tabla"
                        hidden
                    >
                        <caption class="visually-hidden">
                            Resultados correspondientes a los filtros consultados.
                        </caption>

                        <thead></thead>
                        <tbody></tbody>
                    </table>

                    <div
                        id="reporteVacio"
                        class="reportes-vacio"
                    >

                        <i class="bi bi-clipboard2-heart-fill" aria-hidden="true"></i>

                        <p>Sus resultados aparecerán aquí.</p>

                    </div>

                </div>

                <!-- Controles de paginación -->
                <div
                    id="reportePaginacion"
                    class="reportes-paginacion"
                    hidden
                >

                    <span id="reporteConteo"></span>

                    <div>

                        <button
                            id="reporteAnterior"
                            type="button"
                            class="btn-templo-secondary"
                            aria-label="Página anterior"
                            disabled
                        >
                            <i class="bi bi-chevron-left" aria-hidden="true"></i>
                        </button>

                        <span id="reportePagina"></span>

                        <button
                            id="reporteSiguiente"
                            type="button"
                            class="btn-templo-secondary"
                            aria-label="Página siguiente"
                            disabled
                        >
                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </button>

                    </div>

                </div>

                <!-- Nota complementaria del reporte -->
                <p
                    id="reporteNota"
                    class="reportes-nota"
                ></p>

            </div>

        </div>

        <!-- Aviso cuando JavaScript está desactivado -->
        <noscript>
            <p class="reportes-mensaje reportes-mensaje-error">
                Active JavaScript en el navegador para consultar los reportes.
            </p>
        </noscript>


        <!-- ===================================================== -->
        <!-- CONFIGURACIÓN INICIAL PARA JAVASCRIPT -->
        <!-- ===================================================== -->

        <!-- Datos de reportes, fechas y categoría inicial en formato JSON -->
        <script type="application/json" id="configuracionReportes">
            <?= json_encode(
                array_merge(
                    $configuracionReportes,
                    [
                        'fecha_inicio' => date('Y-m-01'),
                        'fecha_fin' => date('Y-m-d'),
                        'grupo_inicial' => $grupoInicial
                    ]
                ),
                JSON_HEX_TAG |
                JSON_HEX_AMP |
                JSON_HEX_APOS |
                JSON_HEX_QUOT |
                JSON_UNESCAPED_UNICODE |
                JSON_THROW_ON_ERROR
            ) ?>
        </script>

        <!-- Script del módulo y versión para actualizar la caché -->
        <?php

        $scriptVista = 'Assets/js/reportes.js';
        $rutaScriptReportes = __DIR__ . '/../../Assets/js/reportes.js';

        if (is_file($rutaScriptReportes)) {
            $scriptVista .= '?v=' . filemtime($rutaScriptReportes);
        }

        ?>

    <?php endif; ?>

</section>


<!-- ===================================================== -->
<!-- CIERRE DE LA VISTA -->
<!-- ===================================================== -->

<?php require_once __DIR__ . '/Layout/Footer.php'; ?>