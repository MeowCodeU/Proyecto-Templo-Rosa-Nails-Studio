</main>

<!-- ===================================================== -->
<!-- BOTONES FLOTANTES INFERIORES -->
<!-- ===================================================== -->

<div class="quick-actions">

    <!-- Manual de usuario -->
    <a href="#" class="quick-btn" title="Manual de usuario">
        <svg
            class="quick-svg-icon"
            viewBox="0 0 64 64"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >

            <!-- Contorno del manual -->
            <rect
                x="8"
                y="8"
                width="48"
                height="48"
                rx="8"
                class="quick-svg-stroke"
                fill="none"
                stroke-width="3"
            />

            <!-- Líneas del contenido -->
            <path
                d="M27 18
                   H48"
                class="quick-svg-stroke"
                fill="none"
                stroke-width="3"
                stroke-linecap="round"
            />

            <path
                d="M27 28
                   H48"
                class="quick-svg-stroke"
                fill="none"
                stroke-width="3"
                stroke-linecap="round"
            />

            <path
                d="M27 38
                   H48"
                class="quick-svg-stroke"
                fill="none"
                stroke-width="3"
                stroke-linecap="round"
            />

            <path
                d="M27 48
                   H48"
                class="quick-svg-stroke"
                fill="none"
                stroke-width="3"
                stroke-linecap="round"
            />

            <!-- Corazones del contenido -->
            <path
                d="M18 14
                   C16.9 12.4 14.4 12.3 13.4 14
                   C12.3 15.8 13.3 17.9 18 21.5
                   C22.7 17.9 23.7 15.8 22.6 14
                   C21.6 12.3 19.1 12.4 18 14Z"
                class="quick-svg-fill"
            />

            <path
                d="M18 24
                   C16.9 22.4 14.4 22.3 13.4 24
                   C12.3 25.8 13.3 27.9 18 31.5
                   C22.7 27.9 23.7 25.8 22.6 24
                   C21.6 22.3 19.1 22.4 18 24Z"
                class="quick-svg-fill"
            />

            <path
                d="M18 34
                   C16.9 32.4 14.4 32.3 13.4 34
                   C12.3 35.8 13.3 37.9 18 41.5
                   C22.7 37.9 23.7 35.8 22.6 34
                   C21.6 32.3 19.1 32.4 18 34Z"
                class="quick-svg-fill"
            />

            <path
                d="M18 44
                   C16.9 42.4 14.4 42.3 13.4 44
                   C12.3 45.8 13.3 47.9 18 51.5
                   C22.7 47.9 23.7 45.8 22.6 44
                   C21.6 42.3 19.1 42.4 18 44Z"
                class="quick-svg-fill"
            />

        </svg>
    </a>

    <!-- Seguridad -->
    <a
        href="Index.php?url=seguridad"
        class="quick-btn"
        title="Seguridad"
    >
        <svg
            class="quick-svg-icon"
            viewBox="0 0 64 64"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >

            <!-- Fondo del escudo -->
            <path
                d="M32 6
                   C27 8, 19 10, 13 12
                   C11.5 23, 14 42, 32 56
                   C50 42, 52.5 23, 51 12
                   C45 10, 37 8, 32 6Z"
                class="quick-svg-fill-soft"
            />

            <!-- Sombra lateral del escudo -->
            <path
                d="M32 6
                   C27 8, 19 10, 13 12
                   C11.5 23, 14 42, 32 56
                   Z"
                class="quick-svg-fill-soft-strong"
            />

            <!-- Contorno del escudo -->
            <path
                d="M32 6
                   C27 8, 19 10, 13 12
                   C11.5 23, 14 42, 32 56
                   C50 42, 52.5 23, 51 12
                   C45 10, 37 8, 32 6Z"
                class="quick-svg-stroke"
                fill="none"
                stroke-width="3"
                stroke-linejoin="round"
            />

            <!-- Corazón de la llave -->
            <path
                d="M32 20
                   C30.7 18.1 27.9 18 26.6 19.8
                   C25.2 21.8 26.4 24.2 32 28.5
                   C37.6 24.2 38.8 21.8 37.4 19.8
                   C36.1 18 33.3 18.1 32 20Z"
                class="quick-svg-fill"
            />

            <!-- Cuerpo de la llave -->
            <path
                d="M32 28.5
                   V41"
                class="quick-svg-stroke"
                fill="none"
                stroke-width="3"
                stroke-linecap="round"
            />

            <!-- Dientes de la llave -->
            <path
                d="M32 35
                   H37"
                class="quick-svg-stroke"
                fill="none"
                stroke-width="3"
                stroke-linecap="round"
            />

            <path
                d="M32 41
                   H40"
                class="quick-svg-stroke"
                fill="none"
                stroke-width="3"
                stroke-linecap="round"
            />

        </svg>
    </a>

    <!-- Configuración -->
    <a
        href="Index.php?url=configuracion"
        class="quick-btn"
        title="Configuración"
    >
        <i class="bi bi-gear-wide-connected"></i>
    </a>

    <!-- Reportes -->
    <a href="Index.php?url=reportes" class="quick-btn" title="Reportes">
        <i class="bi bi-bar-chart-line"></i>
    </a>

</div>


<!-- ===================================================== -->
<!-- MODALES DEL PERFIL -->
<!-- ===================================================== -->

<?php require_once __DIR__ . '/ModalesPerfil.php'; ?>


<!-- ===================================================== -->
<!-- LIBRERÍAS JAVASCRIPT -->
<!-- ===================================================== -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap: componentes interactivos -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables: tablas e integración con Bootstrap -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<!-- FullCalendar: carga opcional para las vistas que lo utilizan -->
<?php if (!empty($usarFullCalendar)): ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
<?php endif; ?>


<!-- ===================================================== -->
<!-- SCRIPTS DEL SISTEMA Y DE LA VISTA -->
<!-- ===================================================== -->

<!-- Script general del sistema -->
<script src="Assets/js/script.js"></script>

<!-- Script particular de una vista -->
<?php if (!empty($scriptVista)): ?>
    <script src="<?= htmlspecialchars($scriptVista, ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php endif; ?>

<!-- Varios scripts particulares de una vista -->
<?php if (!empty($scriptsVista) && is_array($scriptsVista)): ?>
    <?php foreach ($scriptsVista as $script): ?>
        <script src="<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8'); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>