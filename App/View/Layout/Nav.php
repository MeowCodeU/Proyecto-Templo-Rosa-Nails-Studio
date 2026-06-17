<nav class="nav-dock" aria-label="Navegación principal">
    <ul class="dock-menu" id="dockMenu">

        <li class="dock-item <?php echo ($moduloActual == 'inicio') ? 'active' : ''; ?>">
            <a href="Index.php?url=inicio">
                <span class="icon custom-pagoda" aria-hidden="true">
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
                        <path d="M28 46V39C28 36.8 29.8 35 32 35C34.2 35 36 36.8 36 39V46"></path>
                    </svg>
                </span>
                <span class="label">Inicio</span>
            </a>
        </li>

        <li class="dock-item <?php echo ($moduloActual == 'clientes') ? 'active' : ''; ?>">
            <a href="Index.php?url=clientes">
                <span class="icon" aria-hidden="true">
                    <i class="bi bi-person-hearts"></i>
                </span>
                <span class="label">Clientes</span>
            </a>
        </li>

        <li class="dock-item <?php echo ($moduloActual == 'agendamiento') ? 'active' : ''; ?>">
            <a href="Index.php?url=agendamiento">
                <span class="icon" aria-hidden="true">
                    <i class="bi bi-calendar-heart"></i>
                </span>
                <span class="label">Agendamiento</span>
            </a>
        </li>

        <li class="dock-logo-item">
            <a href="Index.php?url=clientes" class="brand-center-link">
                <img src="Assets/img/LogoTR.png" alt="Logo Templo Rosa" class="brand-logo-img">
            </a>
        </li>

        <li class="dock-item <?php echo ($moduloActual == 'servicios') ? 'active' : ''; ?>">
            <a href="Index.php?url=servicios">
                <span class="icon custom-servicios" aria-hidden="true">
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
                        <path class="bottle-heart" d="M32 45 C31 43.5 28 41.8 28 39.5 C28 37.7 29.4 36.5 31 36.5 C32 36.5 32.8 37 33.3 37.9 C33.8 37 34.6 36.5 35.6 36.5 C37.2 36.5 38.6 37.7 38.6 39.5 C38.6 41.8 35.6 43.5 32 46Z"></path>
                    </svg>
                </span>
                <span class="label">Servicios</span>
            </a>
        </li>

        <li class="dock-item <?php echo ($moduloActual == 'insumos') ? 'active' : ''; ?>">
            <a href="Index.php?url=insumos">
                <span class="icon" aria-hidden="true">
                    <i class="bi bi-box2-heart"></i>
                </span>
                <span class="label">Insumos</span>
            </a>
        </li>

        <li class="dock-item <?php echo ($moduloActual == 'proveedores') ? 'active' : ''; ?>">
            <a href="Index.php?url=proveedores">
                <span class="icon custom-proveedores" aria-hidden="true">
                    <svg viewBox="0 0 64 64">
                        <path d="M4 20A3 3 0 0 1 7 17H42A3 3 0 0 1 45 20V27H52A4 4 0 0 1 55.5 29.2L60.5 36.5A4 4 0 0 1 61 38.8V48A3 3 0 0 1 58 51H54" />
                        <path d="M4 20V48A3 3 0 0 0 7 51H12" />
                        <path d="M26 51H40" />
                        <path d="M45 27V44" />
                        <path d="M45 30H54L59 38H45V30Z" stroke-width="1.5" />
                        <circle cx="19" cy="51" r="7" />
                        <circle cx="47" cy="51" r="7" />
                        <path d="M24.5 35 
                                 C23.5 33 21 32.5 21 30.5 
                                 C21 29 22.5 28 24.5 28 
                                 C25.5 28 26.5 28.5 27 29.5 
                                 C27.5 28.5 28.5 28 29.5 28 
                                 C31.5 28 33 29 33 30.5 
                                 C33 32.5 30.5 33 29.5 35 
                                 L27 37.5 24.5 35Z" class="truck-heart"></path>
                    </svg>
                </span>
                <span class="label">Proveedores</span>
            </a>
        </li>

    </ul>
</nav>