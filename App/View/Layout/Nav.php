<?php

$moduloActual = $moduloActual ??
    ($_GET['url'] ?? 'inicio');

?>


<!-- ===================================================== -->
<!-- NAVEGACIÓN DE ESCRITORIO -->
<!-- ===================================================== -->

<nav
    class="nav-dock nav-dock-compact d-none d-lg-flex"
    aria-label="Navegación principal"
>

    <ul
        class="dock-menu dock-menu-balanced"
        id="dockMenu"
    >

        <!-- Inicio -->
        <li class="dock-item <?= ($moduloActual === 'inicio') ? 'active' : ''; ?>">

            <a href="Index.php?url=inicio">

                <span
                    class="icon custom-pagoda"
                    aria-hidden="true"
                >

                    <svg viewBox="0 0 64 64">

                        <path d="M14 50H50"></path>
                        <path d="M18 46H46"></path>
                        <path d="M22 46V34"></path>
                        <path d="M32 46V34"></path>
                        <path d="M42 46V34"></path>
                        <path d="M16 34H48"></path>
                        <path d="M12 34L32 28L52 34"></path>
                        <path d="M20 28H44"></path>
                        <path d="M24 28V21"></path>
                        <path d="M32 28V21"></path>
                        <path d="M40 28V21"></path>
                        <path d="M18 21H46"></path>
                        <path d="M15 21L32 15L49 21"></path>
                        <path d="M23 15H41"></path>
                        <path d="M26 15V10"></path>
                        <path d="M32 15V10"></path>
                        <path d="M38 15V10"></path>
                        <path d="M21 10H43"></path>
                        <path d="M19 10L32 5L45 10"></path>

                        <path
                            d="M28 46V39
                               C28 36.8 29.8 35 32 35
                               C34.2 35 36 36.8 36 39
                               V46"
                        ></path>

                    </svg>

                </span>

                <span class="label">
                    Inicio
                </span>

            </a>

        </li>


        <!-- Clientes -->
        <li class="dock-item <?= ($moduloActual === 'clientes') ? 'active' : ''; ?>">

            <a href="Index.php?url=clientes">

                <span
                    class="icon"
                    aria-hidden="true"
                >
                    <i class="bi bi-person-hearts"></i>
                </span>

                <span class="label">
                    Clientes
                </span>

            </a>

        </li>


        <!-- Agendamiento -->
        <li class="dock-item <?= ($moduloActual === 'agendamiento') ? 'active' : ''; ?>">

            <a href="Index.php?url=agendamiento">

                <span
                    class="icon"
                    aria-hidden="true"
                >
                    <i class="bi bi-calendar-heart"></i>
                </span>

                <span class="label">
                    Agendamiento
                </span>

            </a>

        </li>


        <!-- Logo central -->
        <li class="dock-logo-item">

            <a
                href="Index.php?url=inicio"
                class="brand-center-link"
                aria-label="Ir al inicio"
                title="Ir al inicio"
            >

                <img
                    src="Assets/img/LogoTR.png"
                    alt="Logo Templo Rosa"
                    class="brand-logo-img"
                >

            </a>

        </li>


        <!-- Servicios -->
        <li class="dock-item <?= ($moduloActual === 'registroServicio') ? 'active' : ''; ?>">

            <a href="Index.php?url=registroServicio">

                <span
                    class="icon custom-servicios"
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
                            d="M18 31H46
                               L44 50
                               C43.7 53 41.4 55 38.4 55
                               H25.6
                               C22.6 55 20.3 53 20 50
                               L18 31Z"
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

                <span class="label">
                    Servicios
                </span>

            </a>

        </li>


        <!-- Insumos -->
        <li class="dock-item <?= ($moduloActual === 'insumos') ? 'active' : ''; ?>">

            <a href="Index.php?url=insumos">

                <span
                    class="icon"
                    aria-hidden="true"
                >
                    <i class="bi bi-box2-heart"></i>
                </span>

                <span class="label">
                    Insumos
                </span>

            </a>

        </li>


        <!-- Proveedores -->
        <li class="dock-item <?= ($moduloActual === 'proveedores') ? 'active' : ''; ?>">

            <a href="Index.php?url=proveedores">

                <span
                    class="icon custom-proveedores"
                    aria-hidden="true"
                >

                    <svg viewBox="0 0 64 64">

                        <path
                            d="M4 20
                               A3 3 0 0 1 7 17
                               H42
                               A3 3 0 0 1 45 20
                               V27
                               H52
                               A4 4 0 0 1 55.5 29.2
                               L60.5 36.5
                               A4 4 0 0 1 61 38.8
                               V48
                               A3 3 0 0 1 58 51
                               H54"
                        ></path>

                        <path
                            d="M4 20V48
                               A3 3 0 0 0 7 51
                               H12"
                        ></path>

                        <path d="M26 51H40"></path>
                        <path d="M45 27V44"></path>

                        <path
                            d="M45 30H54L59 38H45V30Z"
                            stroke-width="1.5"
                        ></path>

                        <circle
                            cx="19"
                            cy="51"
                            r="7"
                        ></circle>

                        <circle
                            cx="47"
                            cy="51"
                            r="7"
                        ></circle>

                        <path
                            class="truck-heart"
                            d="M24.5 35
                               C23.5 33 21 32.5 21 30.5
                               C21 29 22.5 28 24.5 28
                               C25.5 28 26.5 28.5 27 29.5
                               C27.5 28.5 28.5 28 29.5 28
                               C31.5 28 33 29 33 30.5
                               C33 32.5 30.5 33 29.5 35
                               L27 37.5
                               L24.5 35Z"
                        ></path>

                    </svg>

                </span>

                <span class="label">
                    Proveedores
                </span>

            </a>

        </li>

    </ul>

</nav>


<!-- ===================================================== -->
<!-- BARRA DE NAVEGACIÓN MÓVIL -->
<!-- ===================================================== -->

<div
    class="mobile-nav-bar d-flex d-lg-none"
    aria-label="Cabecera de navegación móvil"
>

    <button
        class="mobile-menu-btn"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#menuMovilTemploRosa"
        aria-controls="menuMovilTemploRosa"
        aria-label="Abrir menú"
    >
        <i class="bi bi-list" aria-hidden="true"></i>
    </button>


    <a
        href="Index.php?url=inicio"
        class="mobile-brand-link"
        aria-label="Ir al inicio"
        title="Ir al inicio"
    >

        <img
            src="Assets/img/LogoTR.png"
            alt="Logo Templo Rosa"
            class="mobile-brand-logo"
        >

    </a>

</div>


<!-- ===================================================== -->
<!-- MENÚ LATERAL MÓVIL -->
<!-- ===================================================== -->

<div
    class="offcanvas offcanvas-start templo-offcanvas"
    tabindex="-1"
    id="menuMovilTemploRosa"
    aria-labelledby="menuMovilTemploRosaLabel"
>

    <div class="offcanvas-header mobile-offcanvas-header">

        <a
            href="Index.php?url=inicio"
            class="mobile-offcanvas-brand"
            aria-label="Ir al inicio"
        >

            <img
                src="Assets/img/LogoTR.png"
                alt="Logo Templo Rosa"
                class="mobile-offcanvas-logo"
            >

            <span id="menuMovilTemploRosaLabel">
                Templo Rosa
            </span>

        </a>


        <button
            type="button"
            class="btn-close mobile-offcanvas-close"
            data-bs-dismiss="offcanvas"
            aria-label="Cerrar menú"
        ></button>

    </div>


    <div class="offcanvas-body mobile-offcanvas-body">

        <nav
            class="mobile-offcanvas-nav"
            aria-label="Navegación móvil"
        >

            <!-- Navegación principal -->
            <ul class="mobile-menu-list">

                <!-- Inicio -->
                <li>

                    <a
                        href="Index.php?url=inicio"
                        class="mobile-menu-link <?= ($moduloActual === 'inicio') ? 'active' : ''; ?>"
                        <?= ($moduloActual === 'inicio') ? 'aria-current="page"' : ''; ?>
                    >

                        <span
                            class="mobile-menu-icon"
                            aria-hidden="true"
                        >

                            <svg
                                class="mobile-menu-svg"
                                viewBox="0 0 64 64"
                            >

                                <path d="M14 50H50"></path>
                                <path d="M18 46H46"></path>
                                <path d="M22 46V34"></path>
                                <path d="M32 46V34"></path>
                                <path d="M42 46V34"></path>
                                <path d="M16 34H48"></path>
                                <path d="M12 34L32 28L52 34"></path>
                                <path d="M20 28H44"></path>
                                <path d="M24 28V21"></path>
                                <path d="M32 28V21"></path>
                                <path d="M40 28V21"></path>
                                <path d="M18 21H46"></path>
                                <path d="M15 21L32 15L49 21"></path>
                                <path d="M23 15H41"></path>
                                <path d="M26 15V10"></path>
                                <path d="M32 15V10"></path>
                                <path d="M38 15V10"></path>
                                <path d="M21 10H43"></path>
                                <path d="M19 10L32 5L45 10"></path>

                                <path
                                    d="M28 46V39
                                       C28 36.8 29.8 35 32 35
                                       C34.2 35 36 36.8 36 39
                                       V46"
                                ></path>

                            </svg>

                        </span>

                        <span>
                            Inicio
                        </span>

                    </a>

                </li>


                <!-- Clientes -->
                <li>

                    <a
                        href="Index.php?url=clientes"
                        class="mobile-menu-link <?= ($moduloActual === 'clientes') ? 'active' : ''; ?>"
                        <?= ($moduloActual === 'clientes') ? 'aria-current="page"' : ''; ?>
                    >

                        <span
                            class="mobile-menu-icon"
                            aria-hidden="true"
                        >
                            <i class="bi bi-person-hearts"></i>
                        </span>

                        <span>
                            Clientes
                        </span>

                    </a>

                </li>


                <!-- Agendamiento -->
                <li>

                    <a
                        href="Index.php?url=agendamiento"
                        class="mobile-menu-link <?= ($moduloActual === 'agendamiento') ? 'active' : ''; ?>"
                        <?= ($moduloActual === 'agendamiento') ? 'aria-current="page"' : ''; ?>
                    >

                        <span
                            class="mobile-menu-icon"
                            aria-hidden="true"
                        >
                            <i class="bi bi-calendar-heart"></i>
                        </span>

                        <span>
                            Agendamiento
                        </span>

                    </a>

                </li>


                <!-- Servicios -->
                <li>

                    <a
                        href="Index.php?url=registroServicio"
                        class="mobile-menu-link <?= ($moduloActual === 'registroServicio') ? 'active' : ''; ?>"
                        <?= ($moduloActual === 'registroServicio') ? 'aria-current="page"' : ''; ?>
                    >

                        <span
                            class="mobile-menu-icon"
                            aria-hidden="true"
                        >

                            <svg
                                class="mobile-menu-svg"
                                viewBox="0 0 64 64"
                            >

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
                                    d="M18 31H46
                                       L44 50
                                       C43.7 53 41.4 55 38.4 55
                                       H25.6
                                       C22.6 55 20.3 53 20 50
                                       L18 31Z"
                                ></path>

                                <path d="M22 50C26 52 38 52 42 50"></path>

                                <path
                                    class="mobile-menu-svg-fill"
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

                        <span>
                            Servicios
                        </span>

                    </a>

                </li>


                <!-- Insumos -->
                <li>

                    <a
                        href="Index.php?url=insumos"
                        class="mobile-menu-link <?= ($moduloActual === 'insumos') ? 'active' : ''; ?>"
                        <?= ($moduloActual === 'insumos') ? 'aria-current="page"' : ''; ?>
                    >

                        <span
                            class="mobile-menu-icon"
                            aria-hidden="true"
                        >
                            <i class="bi bi-box2-heart"></i>
                        </span>

                        <span>
                            Insumos
                        </span>

                    </a>

                </li>


                <!-- Proveedores -->
                <li>

                    <a
                        href="Index.php?url=proveedores"
                        class="mobile-menu-link <?= ($moduloActual === 'proveedores') ? 'active' : ''; ?>"
                        <?= ($moduloActual === 'proveedores') ? 'aria-current="page"' : ''; ?>
                    >

                        <span
                            class="mobile-menu-icon"
                            aria-hidden="true"
                        >

                            <svg
                                class="mobile-menu-svg"
                                viewBox="0 0 64 64"
                            >

                                <path
                                    d="M4 20
                                       A3 3 0 0 1 7 17
                                       H42
                                       A3 3 0 0 1 45 20
                                       V27
                                       H52
                                       A4 4 0 0 1 55.5 29.2
                                       L60.5 36.5
                                       A4 4 0 0 1 61 38.8
                                       V48
                                       A3 3 0 0 1 58 51
                                       H54"
                                ></path>

                                <path
                                    d="M4 20V48
                                       A3 3 0 0 0 7 51
                                       H12"
                                ></path>

                                <path d="M26 51H40"></path>
                                <path d="M45 27V44"></path>
                                <path d="M45 30H54L59 38H45V30Z"></path>

                                <circle
                                    cx="19"
                                    cy="51"
                                    r="7"
                                ></circle>

                                <circle
                                    cx="47"
                                    cy="51"
                                    r="7"
                                ></circle>

                                <path
                                    class="mobile-menu-svg-fill"
                                    d="M24.5 35
                                       C23.5 33 21 32.5 21 30.5
                                       C21 29 22.5 28 24.5 28
                                       C25.5 28 26.5 28.5 27 29.5
                                       C27.5 28.5 28.5 28 29.5 28
                                       C31.5 28 33 29 33 30.5
                                       C33 32.5 30.5 33 29.5 35
                                       L27 37.5
                                       L24.5 35Z"
                                ></path>

                            </svg>

                        </span>

                        <span>
                            Proveedores
                        </span>

                    </a>

                </li>

            </ul>


            <!-- Accesos inferiores -->
            <div class="mobile-access-area">

                <ul class="mobile-access-list">

                    <!-- Manual de usuario -->
                    <li>

                        <a
                            href="#"
                            class="mobile-access-link"
                            title="Manual de usuario"
                        >

                            <span
                                class="mobile-access-icon"
                                aria-hidden="true"
                            >

                                <svg
                                    class="mobile-menu-svg"
                                    viewBox="0 0 64 64"
                                >

                                    <rect
                                        x="8"
                                        y="8"
                                        width="48"
                                        height="48"
                                        rx="8"
                                    ></rect>

                                    <path d="M27 18H48"></path>
                                    <path d="M27 28H48"></path>
                                    <path d="M27 38H48"></path>
                                    <path d="M27 48H48"></path>

                                    <path
                                        class="mobile-menu-svg-fill"
                                        d="M18 14
                                           C16.9 12.4 14.4 12.3 13.4 14
                                           C12.3 15.8 13.3 17.9 18 21.5
                                           C22.7 17.9 23.7 15.8 22.6 14
                                           C21.6 12.3 19.1 12.4 18 14Z"
                                    ></path>

                                    <path
                                        class="mobile-menu-svg-fill"
                                        d="M18 24
                                           C16.9 22.4 14.4 22.3 13.4 24
                                           C12.3 25.8 13.3 27.9 18 31.5
                                           C22.7 27.9 23.7 25.8 22.6 24
                                           C21.6 22.3 19.1 22.4 18 24Z"
                                    ></path>

                                    <path
                                        class="mobile-menu-svg-fill"
                                        d="M18 34
                                           C16.9 32.4 14.4 32.3 13.4 34
                                           C12.3 35.8 13.3 37.9 18 41.5
                                           C22.7 37.9 23.7 35.8 22.6 34
                                           C21.6 32.3 19.1 32.4 18 34Z"
                                    ></path>

                                    <path
                                        class="mobile-menu-svg-fill"
                                        d="M18 44
                                           C16.9 42.4 14.4 42.3 13.4 44
                                           C12.3 45.8 13.3 47.9 18 51.5
                                           C22.7 47.9 23.7 45.8 22.6 44
                                           C21.6 42.3 19.1 42.4 18 44Z"
                                    ></path>

                                </svg>

                            </span>

                            <span>
                                Manual de usuario
                            </span>

                        </a>

                    </li>


                    <!-- Seguridad -->
                    <li>

                        <a
                            href="Index.php?url=seguridad"
                            class="mobile-access-link <?= ($moduloActual === 'seguridad') ? 'active' : ''; ?>"
                            <?= ($moduloActual === 'seguridad') ? 'aria-current="page"' : ''; ?>
                        >

                            <span
                                class="mobile-access-icon"
                                aria-hidden="true"
                            >

                                <svg
                                    class="mobile-menu-svg"
                                    viewBox="0 0 64 64"
                                >

                                    <path
                                        class="mobile-menu-svg-soft"
                                        d="M32 6
                                           C27 8 19 10 13 12
                                           C11.5 23 14 42 32 56
                                           C50 42 52.5 23 51 12
                                           C45 10 37 8 32 6Z"
                                    ></path>

                                    <path
                                        class="mobile-menu-svg-soft-strong"
                                        d="M32 6
                                           C27 8 19 10 13 12
                                           C11.5 23 14 42 32 56Z"
                                    ></path>

                                    <path
                                        d="M32 6
                                           C27 8 19 10 13 12
                                           C11.5 23 14 42 32 56
                                           C50 42 52.5 23 51 12
                                           C45 10 37 8 32 6Z"
                                    ></path>

                                    <path
                                        class="mobile-menu-svg-fill"
                                        d="M32 20
                                           C30.7 18.1 27.9 18 26.6 19.8
                                           C25.2 21.8 26.4 24.2 32 28.5
                                           C37.6 24.2 38.8 21.8 37.4 19.8
                                           C36.1 18 33.3 18.1 32 20Z"
                                    ></path>

                                    <path d="M32 28.5V41"></path>
                                    <path d="M32 35H37"></path>
                                    <path d="M32 41H40"></path>

                                </svg>

                            </span>

                            <span>
                                Seguridad
                            </span>

                        </a>

                    </li>


                    <!-- Configuración -->
                    <li>

                        <a
                            href="Index.php?url=configuracion"
                            class="mobile-access-link <?= ($moduloActual === 'configuracion') ? 'active' : ''; ?>"
                            <?= ($moduloActual === 'configuracion') ? 'aria-current="page"' : ''; ?>
                        >

                            <span
                                class="mobile-access-icon"
                                aria-hidden="true"
                            >
                                <i class="bi bi-gear-wide-connected"></i>
                            </span>

                            <span>
                                Configuración
                            </span>

                        </a>

                    </li>


                    <!-- Reportes -->
                    <li>

                        <a
                            href="#"
                            class="mobile-access-link"
                            title="Reportes"
                        >

                            <span
                                class="mobile-access-icon"
                                aria-hidden="true"
                            >
                                <i class="bi bi-bar-chart-line"></i>
                            </span>

                            <span>
                                Reportes
                            </span>

                        </a>

                    </li>

                </ul>

            </div>

        </nav>

    </div>

</div>