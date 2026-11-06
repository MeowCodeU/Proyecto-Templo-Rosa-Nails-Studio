<!-- ===================================================== -->
<!-- DATOS DEL USUARIO -->
<!-- ===================================================== -->

<?php

$datosUsuarioHeader = [];

if (isset($usuarioActual) && is_array($usuarioActual)) {
    $datosUsuarioHeader = $usuarioActual;

} elseif (isset($_SESSION['usuario']) && is_array($_SESSION['usuario'])) {
    $datosUsuarioHeader = $_SESSION['usuario'];
}

?>


<!-- Nombre que se muestra en la cabecera -->
<?php

$nicknameHeader = trim((string) ($datosUsuarioHeader['nickname'] ?? ''));
$nombreHeader = trim((string) ($datosUsuarioHeader['nombre'] ?? ''));
$apellidoHeader = trim((string) ($datosUsuarioHeader['apellido'] ?? ''));
$nombreCompletoHeader = trim($nombreHeader . ' ' . $apellidoHeader);

if ($nicknameHeader !== '') {
    $nombreUsuarioHeader = $nicknameHeader;

} elseif ($nombreCompletoHeader !== '') {
    $nombreUsuarioHeader = $nombreCompletoHeader;

} else {
    $nombreUsuarioHeader = 'Usuario';
}

?>


<!-- Rol del usuario -->
<?php

$rolUsuarioHeader = trim((string) ($datosUsuarioHeader['nombre_rol'] ?? $datosUsuarioHeader['rol'] ?? ''));

if ($rolUsuarioHeader === '') {
    $rolUsuarioHeader = 'SIN ROL';
}

?>


<!-- Foto de perfil e imagen predeterminada -->
<?php

$fotoUsuarioHeader = trim((string) ($datosUsuarioHeader['foto_perfil'] ?? ''));

if ($fotoUsuarioHeader === '') {
    $fotoUsuarioHeader = 'Assets/img/LogoTR.png';
}

?>


<!-- Escapar los datos que se muestran en la cabecera -->
<?php

$escaparHeader = static function ($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};

?>


<!-- ===================================================== -->
<!-- USUARIO Y ACCIONES DE SESIÓN -->
<!-- ===================================================== -->

<div class="session-actions">

    <div class="user-session dropdown">

        <!-- Botón que abre el menú del usuario -->
        <button
            class="user-session-btn"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >

            <!-- Foto de perfil -->
            <img
                src="<?= $escaparHeader($fotoUsuarioHeader); ?>"
                alt="Foto de perfil"
                class="user-session-photo"
            >

            <!-- Nombre y rol del usuario -->
            <div class="user-session-info">

                <span class="user-session-name">
                    <?= $escaparHeader($nombreUsuarioHeader); ?>
                </span>

                <small class="user-session-role">
                    <?= $escaparHeader(strtoupper($rolUsuarioHeader)); ?>
                </small>

            </div>

            <i class="bi bi-chevron-down user-session-arrow"></i>

        </button>


        <!-- Menú de perfil y cambio de contraseña -->
        <ul class="dropdown-menu dropdown-menu-end user-session-menu">

            <!-- Editar los datos del perfil -->
            <li>
                <a
                    class="dropdown-item"
                    href="#"
                    data-bs-toggle="modal"
                    data-bs-target="#modalPerfil"
                >
                    <i class="bi bi-person-circle me-2"></i>
                    Editar Perfil
                </a>
            </li>

            <li>
                <hr class="dropdown-divider">
            </li>

            <!-- Abrir el formulario de cambio de contraseña -->
            <li>
                <a
                    class="dropdown-item"
                    href="#"
                    data-bs-toggle="modal"
                    data-bs-target="#modalCambiarClave"
                >
                    <i class="bi bi-key me-2"></i>
                    Cambiar Contraseña
                </a>
            </li>

        </ul>

    </div>


    <!-- Cerrar sesión -->
    <a
        href="Index.php?url=logout"
        class="logout-header-btn"
        title="Cerrar sesión"
        aria-label="Cerrar sesión"
    >
        <i class="bi bi-box-arrow-right"></i>
    </a>

</div>