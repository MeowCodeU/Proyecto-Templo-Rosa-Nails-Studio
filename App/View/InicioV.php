<?php

/* ===================================================== */
/* DATOS DE LA VISTA */
/* ===================================================== */

$tituloPagina = 'Inicio';

require_once __DIR__ . '/Layout/Header.php';

/* Identificar el rol para personalizar la bienvenida */
$idRolInicio = (int) ($datosUsuarioHeader['id_rol'] ?? 0);
$esAdministradorSistemaInicio = $idRolInicio === 3;
$esEspecialistaInicio = $idRolInicio === 2;

/* Seleccionar la imagen correspondiente al rol */
$imagenBienvenidaInicio = 'Assets/img/TR_Girl_Pant.png';
$descripcionImagenInicio = 'Especialista en manicura de Templo Rosa';

if ($idRolInicio === 1) {
    $imagenBienvenidaInicio = 'Assets/img/TR_Administradora_Negocio.png';
    $descripcionImagenInicio = 'Administradora del Negocio de Templo Rosa';

} elseif ($esAdministradorSistemaInicio) {
    $imagenBienvenidaInicio = 'Assets/img/TR_Administradora_Sistema.png';
    $descripcionImagenInicio = 'Administradora del Sistema de Templo Rosa';
}

?>


<!-- ===================================================== -->
<!-- INICIO DEL SISTEMA -->
<!-- ===================================================== -->

<section
    class="module-card inicio-module"
    data-ruta="Index.php?url=inicio"
    aria-busy="<?= $esAdministradorSistemaInicio ? 'false' : 'true'; ?>"
>

    <div class="inicio-scroll">

        <!-- Avisos de la consulta -->
        <div
            id="mensajeInicio"
            class="alert py-2 small d-none"
            role="status"
            aria-live="polite"
            aria-atomic="true"
            hidden
        ></div>


        <!-- ===================================================== -->
        <!-- BIENVENIDA Y CALENDARIO -->
        <!-- ===================================================== -->

        <div class="inicio-superior"<?= $esAdministradorSistemaInicio ? ' style="grid-template-columns: minmax(0, 1fr);"' : ''; ?>>

            <!-- Bienvenida -->
            <div class="inicio-bienvenida-wrap">

                <article class="inicio-bienvenida-card">

                    <div class="inicio-bienvenida-texto">

                        <h3>
                            ¡Bienvenida, <?= $escaparHeader($nombreUsuarioHeader); ?>!
                        </h3>

                        <?php if ($esEspecialistaInicio): ?>
                        <!-- Agendamientos personales de la especialista -->
                        <p class="inicio-bienvenida-programadas">
                            Agendamientos para hoy:
                        </p>

                        <div class="inicio-bienvenida-citas">

                            <strong id="cantidadCitasEspecialistaHoy">—</strong>
                            <span id="textoCitasEspecialistaHoy">agendamientos</span>

                        </div>
                        <?php endif; ?>

                    </div>

                </article>

                <img
                    src="<?= $escaparHeader($imagenBienvenidaInicio); ?>"
                    alt="<?= $escaparHeader($descripcionImagenInicio); ?>"
                    class="inicio-bienvenida-imagen"
                >

            </div>


            <?php if (!$esAdministradorSistemaInicio): ?>
            <!-- Mini calendario -->
            <article class="inicio-calendario-card">

                <div class="inicio-card-header">

                    <span class="d-inline-flex align-items-center gap-2">
                        <i class="bi bi-calendar-heart" aria-hidden="true"></i>
                        Mini calendario
                    </span>

                    <button
                        type="button"
                        class="inicio-panel-enlace ms-auto border-0 bg-transparent p-0"
                        id="btnMesActual"
                        aria-controls="miniCalendarioDias"
                        disabled
                    >
                        Mes actual
                    </button>

                </div>

                <div class="inicio-calendario-body">

                    <!-- Controles para cambiar el mes -->
                    <div class="inicio-calendario-navegacion">

                        <button
                            type="button"
                            class="inicio-calendario-btn"
                            id="btnMesAnterior"
                            aria-label="Mes anterior"
                            aria-controls="miniCalendarioDias"
                            disabled
                        >
                            <i class="bi bi-chevron-left" aria-hidden="true"></i>
                        </button>

                        <h4 id="miniCalendarioTitulo" aria-live="polite">—</h4>

                        <button
                            type="button"
                            class="inicio-calendario-btn"
                            id="btnMesSiguiente"
                            aria-label="Mes siguiente"
                            aria-controls="miniCalendarioDias"
                            disabled
                        >
                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </button>

                    </div>

                    <div class="inicio-calendario-grid">

                        <!-- Encabezados de los días de la semana -->
                        <div class="inicio-calendario-semana">
                            <span>Dom</span>
                            <span>Lun</span>
                            <span>Mar</span>
                            <span>Mié</span>
                            <span>Jue</span>
                            <span>Vie</span>
                            <span>Sáb</span>
                        </div>

                        <!-- Días generados por el script de inicio -->
                        <div
                            class="inicio-calendario-dias"
                            id="miniCalendarioDias"
                            role="list"
                            aria-label="Agendamientos por día"
                        ></div>

                    </div>

                    <small class="inicio-calendario-ayuda">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        Los números indican la cantidad de agendamientos de cada día
                    </small>

                </div>

            </article>
            <?php endif; ?>

        </div>


        <?php if (!$esAdministradorSistemaInicio): ?>
        <!-- ===================================================== -->
        <!-- TARJETAS DE RESUMEN -->
        <!-- ===================================================== -->

        <div class="inicio-resumen">

            <div class="inicio-resumen-grid">

                <!-- Agendamientos de hoy: total global -->
                <article class="inicio-resumen-card">

                    <div class="inicio-resumen-icono">
                        <i class="bi bi-calendar2-heart" aria-hidden="true"></i>
                    </div>

                    <div class="inicio-resumen-contenido">

                        <span class="inicio-resumen-titulo">
                            Agendamientos de hoy
                        </span>

                        <strong
                            class="inicio-resumen-cantidad"
                            id="resumenAgendamientosHoy"
                        >—</strong>

                        <small>Citas y llegadas inmediatas</small>

                    </div>

                </article>


                <!-- Servicios realizados durante el día -->
                <article class="inicio-resumen-card">

                    <div class="inicio-resumen-icono">

                        <span class="servicio-icono" aria-hidden="true">
                            <svg viewBox="0 0 64 64">

                                <rect x="24" y="6" width="16" height="20" rx="2"></rect>

                                <path d="M28 10V22"></path>
                                <path d="M32 10V22"></path>
                                <path d="M36 10V22"></path>
                                <path d="M24 28H29"></path>
                                <path d="M35 28C35 30 36 31 38 31"></path>
                                <path d="M29 28C29 30 28 31 26 31"></path>
                                <path d="M35 28H40"></path>

                                <!-- Cuerpo del frasco -->
                                <path
                                    d="M18 31H46L44 50
                                       C43.7 53 41.4 55 38.4 55H25.6
                                       C22.6 55 20.3 53 20 50L18 31Z"
                                ></path>

                                <path d="M22 50C26 52 38 52 42 50"></path>

                                <!-- Corazón del frasco -->
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

                    </div>

                    <div class="inicio-resumen-contenido">

                        <span class="inicio-resumen-titulo">
                            Servicios realizados
                        </span>

                        <strong
                            class="inicio-resumen-cantidad"
                            id="resumenServiciosHoy"
                        >—</strong>

                        <small>Finalizados durante el día</small>

                    </div>

                </article>


                <!-- Clientas registradas -->
                <article class="inicio-resumen-card">

                    <div class="inicio-resumen-icono">
                        <i class="bi bi-person-hearts" aria-hidden="true"></i>
                    </div>

                    <div class="inicio-resumen-contenido">

                        <span class="inicio-resumen-titulo">
                            Clientas registradas
                        </span>

                        <strong
                            class="inicio-resumen-cantidad"
                            id="resumenClientasRegistradas"
                        >—</strong>

                        <small>Total registrado en el sistema</small>

                    </div>

                </article>


                <!-- Insumos con stock bajo -->
                <article class="inicio-resumen-card">

                    <div class="inicio-resumen-icono">
                        <i class="bi bi-box2-heart" aria-hidden="true"></i>
                    </div>

                    <div class="inicio-resumen-contenido">

                        <span class="inicio-resumen-titulo">
                            Insumos con stock bajo
                        </span>

                        <strong
                            class="inicio-resumen-cantidad"
                            id="resumenStockBajo"
                        >—</strong>

                        <small>Requieren reposición</small>

                    </div>

                </article>

            </div>

        </div>


        <!-- ===================================================== -->
        <!-- GESTIÓN DEL DÍA -->
        <!-- ===================================================== -->

        <div class="inicio-operativo">

            <!-- Agendamientos de hoy -->
            <article class="inicio-panel">

                <div class="inicio-panel-header">

                    <div class="inicio-panel-titulo">
                        <i class="bi bi-calendar2-heart" aria-hidden="true"></i>
                        <span>Agendamientos de hoy</span>
                    </div>

                    <a
                        href="Index.php?url=agendamiento"
                        class="inicio-panel-enlace"
                    >
                        Ver agendamientos
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>

                </div>

                <div class="inicio-panel-body">

                    <!-- Cantidades por estado del agendamiento -->
                    <div class="inicio-estados">

                        <div class="inicio-estado inicio-estado-activo">
                            <span>En atención</span>
                            <strong id="inicioEnAtencion">—</strong>
                        </div>

                        <div class="inicio-estado inicio-estado-espera">
                            <span>En espera</span>
                            <strong id="inicioEnEspera">—</strong>
                        </div>

                        <div class="inicio-estado inicio-estado-finalizado">
                            <span>Finalizados</span>
                            <strong id="inicioFinalizadosHoy">—</strong>
                        </div>

                    </div>

                    <!-- Lista de agendamientos del día -->
                    <div
                        class="inicio-lista"
                        id="inicioListaAgendamientos"
                        role="list"
                    >

                        <div class="inicio-vacio" role="listitem">
                            <i class="bi bi-calendar2-x" aria-hidden="true"></i>

                            <p>
                                El listado aparecerá al completar la consulta.
                            </p>
                        </div>

                    </div>

                </div>

            </article>


            <!-- Alertas de stock -->
            <article class="inicio-panel">

                <div class="inicio-panel-header">

                    <div class="inicio-panel-titulo">
                        <i class="bi bi-box2-heart" aria-hidden="true"></i>
                        <span>Alertas de stock</span>
                    </div>

                    <?php if (!$esEspecialistaInicio): ?>
                    <!-- Acceso a Insumos para la Administradora del Negocio -->
                    <a
                        href="Index.php?url=insumos"
                        class="inicio-panel-enlace"
                    >
                        Ver insumos
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                    <?php endif; ?>

                </div>

                <div class="inicio-panel-body">

                    <!-- Lista de insumos que requieren reposición -->
                    <div
                        class="inicio-lista"
                        id="inicioListaStock"
                        role="list"
                    >

                        <div class="inicio-vacio" role="listitem">
                            <i class="bi bi-box2-heart-fill" aria-hidden="true"></i>

                            <p>
                                Las alertas aparecerán al completar la consulta.
                            </p>
                        </div>

                    </div>

                </div>

            </article>

        </div>


        <!-- ===================================================== -->
        <!-- INFORMACIÓN MENSUAL -->
        <!-- ===================================================== -->

        <div class="inicio-inferior">

            <!-- Servicios más solicitados -->
            <article class="inicio-panel">

                <div class="inicio-panel-header flex-wrap">

                    <div class="inicio-panel-titulo">

                        <span class="servicio-icono" aria-hidden="true">
                            <svg viewBox="0 0 64 64">

                                <rect x="24" y="6" width="16" height="20" rx="2"></rect>

                                <path d="M28 10V22"></path>
                                <path d="M32 10V22"></path>
                                <path d="M36 10V22"></path>
                                <path d="M24 28H29"></path>
                                <path d="M35 28C35 30 36 31 38 31"></path>
                                <path d="M29 28C29 30 28 31 26 31"></path>
                                <path d="M35 28H40"></path>

                                <!-- Cuerpo del frasco -->
                                <path
                                    d="M18 31H46L44 50
                                       C43.7 53 41.4 55 38.4 55H25.6
                                       C22.6 55 20.3 53 20 50L18 31Z"
                                ></path>

                                <path d="M22 50C26 52 38 52 42 50"></path>

                                <!-- Corazón del frasco -->
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

                        <span>Servicios más solicitados</span>

                    </div>

                    <!-- Mes consultado -->
                    <small
                        class="inicio-panel-enlace inicio-mes-etiqueta text-nowrap"
                        data-mes-inicio
                    >—</small>

                </div>

                <div class="inicio-panel-body">

                    <!-- Listado de servicios más solicitados -->
                    <div
                        class="inicio-lista"
                        id="inicioServiciosSolicitados"
                        role="list"
                    >

                        <div class="inicio-vacio" role="listitem">
                            <i class="bi bi-clipboard2-heart" aria-hidden="true"></i>

                            <p>
                                Los servicios aparecerán al completar la consulta.
                            </p>
                        </div>

                    </div>

                </div>

            </article>


            <!-- Resumen mensual -->
            <article class="inicio-panel">

                <div class="inicio-panel-header flex-wrap">

                    <div class="inicio-panel-titulo">
                        <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                        <span>Resumen mensual</span>
                    </div>

                    <!-- Mes consultado -->
                    <small
                        class="inicio-panel-enlace inicio-mes-etiqueta text-nowrap"
                        data-mes-inicio
                    >—</small>

                </div>

                <div class="inicio-panel-body">

                    <div
                        class="inicio-metricas"
                        style="grid-template-columns: repeat(2, minmax(0, 1fr));"
                    >

                        <!-- Servicios realizados en el mes -->
                        <div class="inicio-metrica">

                            <span class="servicio-icono" aria-hidden="true">
                                <svg viewBox="0 0 64 64">

                                    <rect x="24" y="6" width="16" height="20" rx="2"></rect>

                                    <path d="M28 10V22"></path>
                                    <path d="M32 10V22"></path>
                                    <path d="M36 10V22"></path>
                                    <path d="M24 28H29"></path>
                                    <path d="M35 28C35 30 36 31 38 31"></path>
                                    <path d="M29 28C29 30 28 31 26 31"></path>
                                    <path d="M35 28H40"></path>

                                    <!-- Cuerpo del frasco -->
                                    <path
                                        d="M18 31H46L44 50
                                           C43.7 53 41.4 55 38.4 55H25.6
                                           C22.6 55 20.3 53 20 50L18 31Z"
                                    ></path>

                                    <path d="M22 50C26 52 38 52 42 50"></path>

                                    <!-- Corazón del frasco -->
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

                            <strong id="inicioServiciosMes">—</strong>

                            <span>Servicios realizados</span>

                        </div>


                        <!-- Clientas en su primera atención realizada -->
                        <div class="inicio-metrica">

                            <i class="bi bi-person-heart" aria-hidden="true"></i>

                            <strong id="inicioClientasMes">—</strong>

                            <span>Clientas nuevas</span>

                        </div>


                        <!-- Clientas distintas atendidas durante el mes -->
                        <div class="inicio-metrica">

                            <i class="bi bi-person-check" aria-hidden="true"></i>

                            <strong id="inicioClientasAtendidasMes">—</strong>

                            <span>Clientas atendidas</span>

                        </div>


                        <!-- Agendamientos atendidos en el mes -->
                        <div class="inicio-metrica">

                            <i class="bi bi-calendar2-heart-fill" aria-hidden="true"></i>

                            <strong id="inicioAgendamientosMes">—</strong>

                            <span>Agendamientos atendidos</span>

                        </div>

                    </div>

                </div>

            </article>

        </div>


        <!-- ===================================================== -->
        <!-- ACTUALIZAR LOS DATOS DEL INICIO -->
        <!-- ===================================================== -->

        <div class="inicio-acciones d-flex justify-content-end mt-3">

            <button
                type="button"
                class="btn-templo-secondary inicio-actualizar-btn"
                id="btnActualizarInicio"
            >
                <svg
                    class="icono-recargar-corazon"
                    width="32"
                    height="32"
                    viewBox="0 0 64 64"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                    focusable="false"
                >
                    <!-- Trazo circular de recarga -->
                    <path
                        d="M51 42A21.5 21.5 0 1 1 47 17"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="4"
                        stroke-linecap="round"
                    />

                    <!-- La curva se une al centro superior del corazón -->
                    <path
                        d="M47 17
                           C44 12 37 13 37 19
                           C37 24 43 28 47 31
                           C51 28 57 24 57 19
                           C57 13 50 12 47 17Z"
                        transform="rotate(-45 47 17)"
                        fill="currentColor"
                    />
                </svg>

                <span>Actualizar</span>
            </button>

        </div>
        <?php endif; ?>

    </div>

</section>


<!-- ===================================================== -->
<!-- SCRIPT Y CIERRE DE LA VISTA -->
<!-- ===================================================== -->

<?php

$scriptVista = $esAdministradorSistemaInicio ? null : 'Assets/js/inicio.js?v=2';

require_once __DIR__ . '/Layout/Footer.php';

?>