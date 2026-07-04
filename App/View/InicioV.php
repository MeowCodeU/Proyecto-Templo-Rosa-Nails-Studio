<?php

$tituloPagina = 'Inicio';

require_once __DIR__ . '/Layout/Header.php';

?>
<section class="module-card inicio-module">

    <div class="inicio-scroll">

        <div class="inicio-superior">

            <!-- Bienvenida -->
            <div class="inicio-bienvenida-wrap">

                <article class="inicio-bienvenida-card">

                    <div class="inicio-bienvenida-texto">

                      <h3>
                        ¡Bienvenida, <?= $escaparHeader($nombreUsuarioHeader); ?>!
                    </h3>

                        <p class="inicio-bienvenida-programadas">
                            Para hoy tienes programadas:
                        </p>

                        <div class="inicio-bienvenida-citas">

                            <strong id="cantidadCitasHoy">
                                0
                            </strong>

                            <span id="textoCitasHoy">
                                citas
                            </span>

                        </div>

                    </div>

                </article>

                <img
                    src="Assets/img/TR_Girl_Pant.png"
                    alt="Manicurista de Templo Rosa"
                    class="inicio-bienvenida-imagen"
                >

            </div>


            <!-- Mini calendario -->
            <article class="inicio-calendario-card">

                <div class="inicio-card-header">

                    <i class="bi bi-calendar-heart"></i>

                    Mini calendario

                </div>

                <div class="inicio-calendario-body">

                    <div class="inicio-calendario-navegacion">

                        <button
                            type="button"
                            class="inicio-calendario-btn"
                            id="btnMesAnterior"
                            aria-label="Mes anterior"
                        >
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <h4
                            id="miniCalendarioTitulo"
                            aria-live="polite"
                        ></h4>

                        <button
                            type="button"
                            class="inicio-calendario-btn"
                            id="btnMesSiguiente"
                            aria-label="Mes siguiente"
                        >
                            <i class="bi bi-chevron-right"></i>
                        </button>

                    </div>

                    <div class="inicio-calendario-grid">

                        <div class="inicio-calendario-semana">

                            <span>Dom</span>
                            <span>Lun</span>
                            <span>Mar</span>
                            <span>Mié</span>
                            <span>Jue</span>
                            <span>Vie</span>
                            <span>Sáb</span>

                        </div>

                        <div
                            class="inicio-calendario-dias"
                            id="miniCalendarioDias"
                        ></div>

                    </div>

                    <small class="inicio-calendario-ayuda">

                        <i class="bi bi-info-circle"></i>

                        Los números indican la cantidad de agendamientos de cada día.

                    </small>

                </div>

            </article>

        </div>


        <!-- Tarjetas de resumen -->
        <div class="inicio-resumen">

            <div class="inicio-resumen-grid">

                <article class="inicio-resumen-card">

                    <div class="inicio-resumen-icono">
                        <i class="bi bi-calendar2-heart"></i>
                    </div>

                    <div class="inicio-resumen-contenido">

                        <span class="inicio-resumen-titulo">
                            Agendamientos de hoy
                        </span>

                        <strong
                            class="inicio-resumen-cantidad"
                            id="resumenAgendamientosHoy"
                        >
                            0
                        </strong>

                        <small>
                            Citas y llegadas inmediatas
                        </small>

                    </div>

                </article>


                <article class="inicio-resumen-card">

                    <div class="inicio-resumen-icono">
                        <span class="servicio-icono" aria-hidden="true">
    <svg viewBox="0 0 64 64">
        <rect x="24" y="6" width="16" height="20" rx="2"></rect>
        <path d="M28 10V22"></path>
        <path d="M32 10V22"></path>
        <path d="M36 10V22"></path>
        <path d="M24 28H29"></path>
        <path d="M35 28H40"></path>
        <path d="M29 28C29 30 28 31 26 31"></path>
        <path d="M35 28C35 30 36 31 38 31"></path>
        <path d="M18 31H46L44 50C43.7 53 41.4 55 38.4 55H25.6C22.6 55 20.3 53 20 50L18 31Z"></path>
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
                    </div>

                    <div class="inicio-resumen-contenido">

                        <span class="inicio-resumen-titulo">
                            Servicios realizados
                        </span>

                        <strong
                            class="inicio-resumen-cantidad"
                            id="resumenServiciosHoy"
                        >
                            0
                        </strong>

                        <small>
                            Finalizados durante el día
                        </small>

                    </div>

                </article>


                <article class="inicio-resumen-card">

                    <div class="inicio-resumen-icono">
                        <i class="bi bi-person-hearts"></i>
                    </div>

                    <div class="inicio-resumen-contenido">

                        <span class="inicio-resumen-titulo">
                            Clientas registradas
                        </span>

                        <strong
                            class="inicio-resumen-cantidad"
                            id="resumenClientasRegistradas"
                        >
                            0
                        </strong>

                        <small>
                            Total registrado en el sistema
                        </small>

                    </div>

                </article>


                <article class="inicio-resumen-card">

                    <div class="inicio-resumen-icono">
                        <i class="bi bi-box2-heart"></i>
                    </div>

                    <div class="inicio-resumen-contenido">

                        <span class="inicio-resumen-titulo">
                            Insumos con stock bajo
                        </span>

                        <strong
                            class="inicio-resumen-cantidad"
                            id="resumenStockBajo"
                        >
                            0
                        </strong>

                        <small>
                            Requieren reposición
                        </small>

                    </div>

                </article>

            </div>

        </div>

        <!-- Gestión del día -->
<div class="inicio-operativo">

    <!-- Agendamientos de hoy -->
    <article class="inicio-panel">

        <div class="inicio-panel-header">

            <div class="inicio-panel-titulo">
                <i class="bi bi-calendar2-heart"></i>
                <span>Agendamientos de hoy</span>
            </div>

            <a
                href="Index.php?url=agendamiento"
                class="inicio-panel-enlace"
            >
                Ver agendamientos
                <i class="bi bi-arrow-right"></i>
            </a>

        </div>

        <div class="inicio-panel-body">

            <div class="inicio-estados">

                <div class="inicio-estado inicio-estado-activo">

                    <span>
                        En atención
                    </span>

                    <strong id="inicioEnAtencion">
                        0
                    </strong>

                </div>

                <div class="inicio-estado inicio-estado-espera">

                    <span>
                        En espera
                    </span>

                    <strong id="inicioEnEspera">
                        0
                    </strong>

                </div>

                <div class="inicio-estado inicio-estado-finalizado">

                    <span>
                        Finalizados
                    </span>

                    <strong id="inicioFinalizadosHoy">
                        0
                    </strong>

                </div>

            </div>

            <div
                class="inicio-lista"
                id="inicioListaAgendamientos"
            >

                <div class="inicio-vacio">

                    <i class="bi bi-calendar2-x"></i>

                    <p>
                        No hay agendamientos registrados para hoy.
                    </p>

                </div>

            </div>

        </div>

    </article>


    <!-- Alertas de stock -->
    <article class="inicio-panel">

        <div class="inicio-panel-header">

            <div class="inicio-panel-titulo">
                <i class="bi bi-box2-heart"></i>
                <span>Alertas de stock</span>
            </div>

            <a
                href="Index.php?url=insumos"
                class="inicio-panel-enlace"
            >
                Ver insumos
                <i class="bi bi-arrow-right"></i>
            </a>

        </div>

        <div class="inicio-panel-body">

            <div
                class="inicio-lista"
                id="inicioListaStock"
            >

                <div class="inicio-vacio">

                    <i class="bi bi-box2-heart-fill"></i>

                    <p>
                        No hay alertas de stock por mostrar.
                    </p>

                </div>

            </div>

        </div>

    </article>

</div>


<!-- Información mensual -->
<div class="inicio-inferior">

    <!-- Servicios más solicitados -->
    <article class="inicio-panel">

        <div class="inicio-panel-header">

            <div class="inicio-panel-titulo">
                <span class="servicio-icono" aria-hidden="true">
    <svg viewBox="0 0 64 64">
        <rect x="24" y="6" width="16" height="20" rx="2"></rect>
        <path d="M28 10V22"></path>
        <path d="M32 10V22"></path>
        <path d="M36 10V22"></path>
        <path d="M24 28H29"></path>
        <path d="M35 28H40"></path>
        <path d="M29 28C29 30 28 31 26 31"></path>
        <path d="M35 28C35 30 36 31 38 31"></path>
        <path d="M18 31H46L44 50C43.7 53 41.4 55 38.4 55H25.6C22.6 55 20.3 53 20 50L18 31Z"></path>
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
                <span>Servicios más solicitados</span>
            </div>

        </div>

        <div class="inicio-panel-body">

            <div
                class="inicio-lista"
                id="inicioServiciosSolicitados"
            >

                <div class="inicio-vacio">

                    <i class="bi bi-clipboard2-heart"></i>

                    <p>
                        Todavía no hay servicios registrados para mostrar.
                    </p>

                </div>

            </div>

        </div>

    </article>


    <!-- Resumen mensual -->
    <article class="inicio-panel">

        <div class="inicio-panel-header">

            <div class="inicio-panel-titulo">
                <i class="bi bi-bar-chart-line"></i>
                <span>Resumen mensual</span>
            </div>

        </div>

        <div class="inicio-panel-body">

            <div class="inicio-metricas">

                <div class="inicio-metrica">

                    <span class="servicio-icono" aria-hidden="true">
    <svg viewBox="0 0 64 64">
        <rect x="24" y="6" width="16" height="20" rx="2"></rect>
        <path d="M28 10V22"></path>
        <path d="M32 10V22"></path>
        <path d="M36 10V22"></path>
        <path d="M24 28H29"></path>
        <path d="M35 28H40"></path>
        <path d="M29 28C29 30 28 31 26 31"></path>
        <path d="M35 28C35 30 36 31 38 31"></path>
        <path d="M18 31H46L44 50C43.7 53 41.4 55 38.4 55H25.6C22.6 55 20.3 53 20 50L18 31Z"></path>
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

                    <strong id="inicioServiciosMes">
                        0
                    </strong>

                    <span>
                        Servicios realizados
                    </span>

                </div>

                <div class="inicio-metrica">

                   <i class="bi bi-person-heart"></i>

                    <strong id="inicioClientasMes">
                        0
                    </strong>

                    <span>
                        Clientas nuevas
                    </span>

                </div>

                <div class="inicio-metrica">

                    <i class="bi bi-calendar2-heart-fill"></i>

                    <strong id="inicioAgendamientosMes">
                        0
                    </strong>

                    <span>
                        Agendamientos atendidos
                    </span>

                </div>

            </div>

        </div>

    </article>

</div>

    </div>

</section>

<?php

$scriptVista = 'Assets/js/inicio.js';

require_once __DIR__ . '/Layout/Footer.php';

?>