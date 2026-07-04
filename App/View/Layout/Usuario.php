<?php

/* Datos del usuario */
$datosUsuarioHeader = [];

if (
    isset($usuarioActual) &&
    is_array($usuarioActual)
) {
    $datosUsuarioHeader = $usuarioActual;

} elseif (
    isset($_SESSION['usuario']) &&
    is_array($_SESSION['usuario'])
) {
    $datosUsuarioHeader = $_SESSION['usuario'];
}


/* Nombre visible */
$nicknameHeader = trim(
    (string) (
        $datosUsuarioHeader['nickname'] ??
        ''
    )
);

$nombreHeader = trim(
    (string) (
        $datosUsuarioHeader['nombre'] ??
        ''
    )
);

$apellidoHeader = trim(
    (string) (
        $datosUsuarioHeader['apellido'] ??
        ''
    )
);

$nombreCompletoHeader = trim(
    $nombreHeader . ' ' . $apellidoHeader
);

if ($nicknameHeader !== '') {
    $nombreUsuarioHeader = $nicknameHeader;

} elseif ($nombreCompletoHeader !== '') {
    $nombreUsuarioHeader = $nombreCompletoHeader;

} else {
    $nombreUsuarioHeader = 'Usuario';
}


/* Rol visible */
$rolUsuarioHeader = trim(
    (string) (
        $datosUsuarioHeader['nombre_rol'] ??
        $datosUsuarioHeader['rol'] ??
        ''
    )
);

if ($rolUsuarioHeader === '') {
    $rolUsuarioHeader = 'SIN ROL';
}


/* Foto visible */
$fotoUsuarioHeader = trim(
    (string) (
        $datosUsuarioHeader['foto_perfil'] ??
        ''
    )
);

if ($fotoUsuarioHeader === '') {
    $fotoUsuarioHeader = 'Assets/img/LogoTR.png';
}


/* Escape de salida */
$escaparHeader = static function ($valor): string {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

?>


<!-- Usuario / sesión -->
<div class="user-session dropdown">

    <button
        class="user-session-btn"
        type="button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
    >

        <img
            src="<?= $escaparHeader($fotoUsuarioHeader); ?>"
            alt="Foto de perfil"
            class="user-session-photo"
        >


        <div class="user-session-info">

            <span class="user-session-name">
                <?= $escaparHeader($nombreUsuarioHeader); ?>
            </span>

            <small class="user-session-role">
                <?= $escaparHeader(
                    strtoupper($rolUsuarioHeader)
                ); ?>
            </small>

        </div>


        <i class="bi bi-chevron-down user-session-arrow"></i>

    </button>


    <ul class="dropdown-menu dropdown-menu-end user-session-menu">

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