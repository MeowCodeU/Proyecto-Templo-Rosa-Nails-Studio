<?php

$tituloPagina = 'Seguridad';

require_once __DIR__ . '/Layout/Header.php';


$listaUsuarios = (
    isset($listaUsuarios) &&
    is_array($listaUsuarios)
)
    ? $listaUsuarios
    : [];


$listaRoles = (
    isset($listaRoles) &&
    is_array($listaRoles)
)
    ? $listaRoles
    : [];


$escaparSeguridad = static function ($valor): string {

    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );

};


/*Crear el token de seguridad utilizado por el formulario de restablecimiento de contraseña.*/
if (empty($_SESSION['token_restablecer_clave'])) {

    $_SESSION['token_restablecer_clave'] =
        bin2hex(
            random_bytes(32)
        );
}


$tokenRestablecerClave =
    $_SESSION['token_restablecer_clave'];


/*
 * Preparar el mensaje correspondiente al resultado
 * devuelto por el controlador de Seguridad.
 */
$resultadoRestablecimiento =
    (string) (
        $_GET['restablecer']
        ?? ''
    );


$mensajesRestablecimiento = [
    'exito' => [
        'tipo' => 'success',
        'texto' =>
            'La contraseña del usuario fue restablecida correctamente.'
    ],
    'clave_corta' => [
        'tipo' => 'warning',
        'texto' =>
            'La contraseña temporal debe contener un mínimo de ocho caracteres.'
    ],
    'no_coinciden' => [
        'tipo' => 'warning',
        'texto' =>
            'La contraseña temporal y su confirmación no coinciden.'
    ],
    'datos_incompletos' => [
        'tipo' => 'warning',
        'texto' =>
            'Debe completar todos los campos solicitados.'
    ],
    'token_invalido' => [
        'tipo' => 'danger',
        'texto' =>
            'La solicitud de restablecimiento no es válida.'
    ],
    'error' => [
        'tipo' => 'danger',
        'texto' =>
            'No fue posible restablecer la contraseña. Intente nuevamente.'
    ]
];


$mensajeRestablecimiento =
    $mensajesRestablecimiento[
        $resultadoRestablecimiento
    ] ?? null;

?>


<?php if ($mensajeRestablecimiento): ?>

    <!-- Resultado del restablecimiento de contraseña -->
    <div
        class="modal fade"
        id="modalResultadoRestablecimiento"
        tabindex="-1"
        aria-labelledby="tituloResultadoRestablecimiento"
        aria-describedby="textoResultadoRestablecimiento"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content templo-modal seguridad-resultado-modal">

                <div class="modal-header">
                    <h5 class="modal-title" id="tituloResultadoRestablecimiento">
                        <?= $resultadoRestablecimiento === 'exito'
                            ? 'Contraseña restablecida'
                            : 'Resultado del restablecimiento'; ?>
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>
                </div>

                <div class="modal-body seguridad-resultado-cuerpo">
                    <span
                        class="seguridad-resultado-icono"
                        aria-hidden="true"
                    >
                        <svg
                                            viewBox="0 0 64 64"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                        
                                            <path
                                                d="M32 6
                                                   C27 8, 19 10, 13 12
                                                   C11.5 23, 14 42, 32 56
                                                   C50 42, 52.5 23, 51 12
                                                   C45 10, 37 8, 32 6Z"
                                                class="quick-svg-fill-soft"
                                            ></path>
                        
                                            <path
                                                d="M32 6
                                                   C27 8, 19 10, 13 12
                                                   C11.5 23, 14 42, 32 56
                                                   Z"
                                                class="quick-svg-fill-soft-strong"
                                            ></path>
                        
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
                                            ></path>
                        
                                            <path
                                                d="M32 20
                                                   C30.7 18.1 27.9 18 26.6 19.8
                                                   C25.2 21.8 26.4 24.2 32 28.5
                                                   C37.6 24.2 38.8 21.8 37.4 19.8
                                                   C36.1 18 33.3 18.1 32 20Z"
                                                class="quick-svg-fill"
                                            ></path>
                        
                                            <path
                                                d="M32 28.5V41"
                                                class="quick-svg-stroke"
                                                fill="none"
                                                stroke-width="3"
                                                stroke-linecap="round"
                                            ></path>
                        
                                            <path
                                                d="M32 35H37"
                                                class="quick-svg-stroke"
                                                fill="none"
                                                stroke-width="3"
                                                stroke-linecap="round"
                                            ></path>
                        
                                            <path
                                                d="M32 41H40"
                                                class="quick-svg-stroke"
                                                fill="none"
                                                stroke-width="3"
                                                stroke-linecap="round"
                                            ></path>
                        
                                        </svg>
                    </span>

                    <p
                        class="seguridad-resultado-mensaje mb-0"
                        id="textoResultadoRestablecimiento"
                        role="<?= $resultadoRestablecimiento === 'exito'
                            ? 'status'
                            : 'alert'; ?>"
                    >
                        <?= $escaparSeguridad(
                            $mensajeRestablecimiento['texto']
                        ); ?>
                    </p>
                </div>

                <div class="modal-footer seguridad-resultado-pie">
                    <button
                        type="button"
                        class="btn-templo-primary seguridad-resultado-aceptar"
                        data-bs-dismiss="modal"
                    >
                        Aceptar
                    </button>
                </div>

            </div>
        </div>
    </div>

<?php endif; ?>


<section class="module-card clientes-module seguridad-module configuracion-module">

    <div class="module-header">

        <h2>

            <span
                class="seguridad-title-icon"
                aria-hidden="true"
            >
                <svg
                    viewBox="0 0 64 64"
                    xmlns="http://www.w3.org/2000/svg"
                >

                    <path
                        d="M32 6
                           C27 8, 19 10, 13 12
                           C11.5 23, 14 42, 32 56
                           C50 42, 52.5 23, 51 12
                           C45 10, 37 8, 32 6Z"
                        class="quick-svg-fill-soft"
                    ></path>

                    <path
                        d="M32 6
                           C27 8, 19 10, 13 12
                           C11.5 23, 14 42, 32 56
                           Z"
                        class="quick-svg-fill-soft-strong"
                    ></path>

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
                    ></path>

                    <path
                        d="M32 20
                           C30.7 18.1 27.9 18 26.6 19.8
                           C25.2 21.8 26.4 24.2 32 28.5
                           C37.6 24.2 38.8 21.8 37.4 19.8
                           C36.1 18 33.3 18.1 32 20Z"
                        class="quick-svg-fill"
                    ></path>

                    <path
                        d="M32 28.5V41"
                        class="quick-svg-stroke"
                        fill="none"
                        stroke-width="3"
                        stroke-linecap="round"
                    ></path>

                    <path
                        d="M32 35H37"
                        class="quick-svg-stroke"
                        fill="none"
                        stroke-width="3"
                        stroke-linecap="round"
                    ></path>

                    <path
                        d="M32 41H40"
                        class="quick-svg-stroke"
                        fill="none"
                        stroke-width="3"
                        stroke-linecap="round"
                    ></path>

                </svg>
            </span>

            Seguridad

        </h2>

    </div>


    <div class="configuracion-contenido">

        <ul class="nav nav-tabs config-tabs" id="seguridadTabs" role="tablist">

            <li class="nav-item" role="presentation">
                <button
                    class="nav-link active"
                    id="gestion-usuarios-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#gestion-usuarios"
                    type="button"
                    role="tab"
                    aria-controls="gestion-usuarios"
                    aria-selected="true"
                >
                    <span
                        class="gestion-usuarios-tab-icon"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 82 64"
                            xmlns="http://www.w3.org/2000/svg"
                            width="38"
                            height="30"
                            focusable="false"
                        >
                            <!-- Grupo de usuarios -->
                            <circle
                                class="quick-svg-fill"
                                cx="18"
                                cy="30"
                                r="6.5"
                            ></circle>

                            <path
                                class="quick-svg-fill"
                                d="M7.5 47
                                   C7.5 39.9 12.1 35.2 18.4 35.2
                                   H20.1
                                   C22.1 35.2 24 35.7 25.6 36.6
                                   C22.7 39.2 20.9 42.8 20.6 47
                                   Z"
                            ></path>

                            <circle
                                class="quick-svg-fill"
                                cx="35"
                                cy="27"
                                r="8.5"
                            ></circle>

                            <path
                                class="quick-svg-fill"
                                d="M20.5 48
                                   C20.5 39.7 26.2 34 35 34
                                   C43.8 34 49.5 39.7 49.5 48
                                   V49.5
                                   H20.5
                                   Z"
                            ></path>

                            <!-- Tuerca Bootstrap gear-wide-connected adaptada -->
                            <g
                                class="gestion-usuarios-gear"
                                transform="translate(45.2 -0.2) scale(2.05)"
                            >
                                <defs>
                                    <mask id="gestionUsuariosGearMask">
                                        <rect
                                            x="0"
                                            y="0"
                                            width="16"
                                            height="16"
                                            fill="white"
                                        ></rect>

                                        <circle
                                            cx="8"
                                            cy="8"
                                            r="5.25"
                                            fill="black"
                                        ></circle>
                                    </mask>
                                </defs>

                                <!--
                                    Silueta exterior original de Bootstrap
                                    gear-wide-connected, con el centro abierto
                                    para conseguir una tuerca más fina.
                                -->
                                <path
                                    class="quick-svg-fill"
                                    mask="url(#gestionUsuariosGearMask)"
                                    d="M7.068.727c.243-.97 1.62-.97 1.864 0l.071.286a.96.96 0 0 0 1.622.434l.205-.211c.695-.719 1.888-.03 1.613.931l-.08.284a.96.96 0 0 0 1.187 1.187l.283-.081c.96-.275 1.65.918.931 1.613l-.211.205a.96.96 0 0 0 .434 1.622l.286.071c.97.243.97 1.62 0 1.864l-.286.071a.96.96 0 0 0-.434 1.622l.211.205c.719.695.03 1.888-.931 1.613l-.284-.08a.96.96 0 0 0-1.187 1.187l.081.283c.275.96-.918 1.65-1.613.931l-.205-.211a.96.96 0 0 0-1.622.434l-.071.286c-.243.97-1.62.97-1.864 0l-.071-.286a.96.96 0 0 0-1.622-.434l-.205.211c-.695.719-1.888.03-1.613-.931l.08-.284a.96.96 0 0 0-1.186-1.187l-.284.081c-.96.275-1.65-.918-.931-1.613l.211-.205a.96.96 0 0 0-.434-1.622l-.286-.071c-.97-.243-.97-1.62 0-1.864l.286-.071a.96.96 0 0 0 .434-1.622l-.211-.205c-.719-.695-.03-1.888.931-1.613l.284.08a.96.96 0 0 0 1.187-1.186l-.081-.284c-.275-.96.918-1.65 1.613-.931l.205.211a.96.96 0 0 0 1.622-.434z"
                                ></path>

                                <!--
                                    Corazón Bootstrap Icons heart-fill.
                                    Se usa la forma oficial para que sea
                                    reconocible incluso en tamaño pequeño.
                                -->
                                <svg
                                    x="4.5"
                                    y="4.5"
                                    width="7"
                                    height="7"
                                    viewBox="0 0 16 16"
                                    xmlns="http://www.w3.org/2000/svg"
                                    aria-hidden="true"
                                >
                                    <path
                                        class="quick-svg-fill gestion-usuarios-heart"
                                        fill-rule="evenodd"
                                        d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314"
                                    ></path>
                                </svg>
                            </g>
                        </svg>
                    </span>

                    Gestión de Usuarios
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button
                    class="nav-link"
                    id="roles-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#roles"
                    type="button"
                    role="tab"
                    aria-controls="roles"
                    aria-selected="false"
                >
                    <i class="bi bi-person-heart"></i>
                    Roles
                </button>
            </li>

        </ul>


        <div class="tab-content configuracion-tab-content" id="seguridadTabsContent">

            <!-- ================================================= -->
            <!-- GESTIÓN DE USUARIOS -->
            <!-- ================================================= -->
            <div
                class="tab-pane fade show active"
                id="gestion-usuarios"
                role="tabpanel"
                aria-labelledby="gestion-usuarios-tab"
                tabindex="0"
            >

                <div class="config-tab-head">
                    <h5 class="config-title">Gestión de Usuarios</h5>

                    <button
                        type="button"
                        class="btn-templo-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modalNuevoUsuario"
                    >
                        <i class="bi bi-person-plus"></i>
                        Registrar Usuario
                    </button>
                </div>

                <div class="table-zone configuracion-table-zone">
                
                    <div class="templo-table-wrapper">
                
                        <div class="table-responsive">
                
                            <table
                                id="tablaUsuarios"
                                class="table table-hover templo-table w-100"
                            >
                
                                <thead>
                
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Nombre y apellido</th>
                                        <th>Teléfono</th>
                                        <th>Correo electrónico</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th class="columna-acciones">Acciones</th>
                                    </tr>
                
                                </thead>
                
                
                                <tbody>
                
                                    <?php foreach ($listaUsuarios as $usuario): ?>
                
                                        <?php
                
                                        $idUsuario =
                                            $usuario['id_usuario'] ?? '';
                
                                        $idPersona =
                                            $usuario['id_persona'] ?? '';
                
                                        $cedula =
                                            $usuario['cedula'] ?? '';
                
                                        $nombre =
                                            $usuario['nombre'] ?? '';
                
                                        $apellido =
                                            $usuario['apellido'] ?? '';
                
                                        $telefono =
                                            $usuario['telefono'] ?? '';
                
                                        $correo =
                                            $usuario['correo'] ?? '';
                
                                        $direccion =
                                            $usuario['direccion'] ?? '';
                
                                        $ciudad =
                                            $usuario['ciudad'] ?? '';
                
                                        $nickname =
                                            $usuario['nickname'] ?? '';
                
                                        $idRol =
                                            $usuario['id_rol'] ?? '';
                
                                        $nombreRol =
                                            $usuario['nombre_rol'] ?? '';
                
                                        $estadoUsuario = strtoupper(
                                            trim(
                                                (string) (
                                                    $usuario['estado_usuario']
                                                    ?? 'ACTIVO'
                                                )
                                            )
                                        );
                
                                        $nombreCompleto = trim(
                                            $nombre . ' ' . $apellido
                                        );
                
                                        ?>
                
                                        <tr>
                
                                            <td>
                                                <?= $escaparSeguridad(
                                                    $cedula
                                                ); ?>
                                            </td>
                
                                            <td>
                                                <?= $escaparSeguridad(
                                                    $nombreCompleto
                                                ); ?>
                                            </td>
                
                                            <td>
                                                <?= $escaparSeguridad(
                                                    $telefono
                                                ); ?>
                                            </td>
                
                                            <td>
                                                <?= $escaparSeguridad(
                                                    $correo
                                                ); ?>
                                            </td>
                
                                            <td>
                                                <?= $escaparSeguridad(
                                                    $nombreRol
                                                ); ?>
                                            </td>
                
                                            <td>
                
                                                <span
                                                    class="badge estado-realizada"
                                                >
                                                    <?= $escaparSeguridad(
                                                        $estadoUsuario
                                                    ); ?>
                                                </span>
                
                                            </td>
                
                                            <td class="columna-acciones">
                
                                                <div class="btn-group-actions">
                
                                                    <!-- Ver detalles del usuario -->
                                                    <button
                                                        type="button"
                                                        class="btn-action btn-detail"
                                                        title="Ver detalles del usuario"
                                                        aria-label="Ver detalles del usuario"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalDetalleUsuario<?= (int) $idUsuario; ?>"
                                                    >
                                                        <span
                                                            class="detalle-accion-icono"
                                                            aria-hidden="true"
                                                        >
                                                            <svg
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                            >
                                                                <path
                                                                    d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z"
                                                                    stroke="currentColor"
                                                                    stroke-width="2"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    fill="none"
                                                                />
                                                                <path
                                                                    d="M12 16C10 14.8 8.5 13.5 8.5 11.8C8.5 10.5 9.5 9.5 10.8 9.5C11.5 9.5 12 9.9 12 10.4C12 9.9 12.5 9.5 13.2 9.5C14.5 9.5 15.5 10.5 15.5 11.8C15.5 13.5 14 14.8 12 16Z"
                                                                    fill="currentColor"
                                                                />
                                                            </svg>
                                                        </span>
                                                    </button>
                
                
                                                    <!-- Editar usuario -->
                                                    <button
                                                        type="button"
                                                        class="btn-action btn-edit btnEditarUsuario"
                                                        title="Editar usuario"
                
                                                        data-id-usuario="<?= $escaparSeguridad(
                                                            $idUsuario
                                                        ); ?>"
                
                                                        data-id-persona="<?= $escaparSeguridad(
                                                            $idPersona
                                                        ); ?>"
                
                                                        data-cedula="<?= $escaparSeguridad(
                                                            $cedula
                                                        ); ?>"
                
                                                        data-nombre="<?= $escaparSeguridad(
                                                            $nombre
                                                        ); ?>"
                
                                                        data-apellido="<?= $escaparSeguridad(
                                                            $apellido
                                                        ); ?>"
                
                                                        data-telefono="<?= $escaparSeguridad(
                                                            $telefono
                                                        ); ?>"
                
                                                        data-correo="<?= $escaparSeguridad(
                                                            $correo
                                                        ); ?>"
                
                                                        data-direccion="<?= $escaparSeguridad(
                                                            $direccion
                                                        ); ?>"
                
                                                        data-ciudad="<?= $escaparSeguridad(
                                                            $ciudad
                                                        ); ?>"
                
                                                        data-nickname="<?= $escaparSeguridad(
                                                            $nickname
                                                        ); ?>"
                
                                                        data-id-rol="<?= $escaparSeguridad(
                                                            $idRol
                                                        ); ?>"
                
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEditarUsuario"
                                                    >
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                
                
                                                    <!-- Restablecer contraseña -->
                                                    <button
                                                        type="button"
                                                        class="btn-action btn-reset-password btnRestablecerClave"
                                                        title="Restablecer contraseña"
                                                        aria-label="Restablecer contraseña"

                                                        data-id-usuario="<?= $escaparSeguridad(
                                                            $idUsuario
                                                        ); ?>"

                                                        data-nombre="<?= $escaparSeguridad(
                                                            $nombreCompleto
                                                        ); ?>"

                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalRestablecerClave"
                                                    >
                                                        <i class="bi bi-bag-heart"></i>
                                                    </button>


                                                    <!-- Desactivar usuario -->
                                                    <button
                                                        type="button"
                                                        class="btn-action btn-deactivate btnCambiarEstadoUsuario"
                                                        title="Desactivar usuario"
                
                                                        data-id-usuario="<?= $escaparSeguridad(
                                                            $idUsuario
                                                        ); ?>"
                
                                                        data-nombre="<?= $escaparSeguridad(
                                                            $nombreCompleto
                                                        ); ?>"
                
                                                        data-estado="ACTIVO"
                
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEstadoUsuario"
                                                    >
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                
                                                </div>
                
                                            </td>
                
                                        </tr>
                
                                    <?php endforeach; ?>
                
                                </tbody>
                
                            </table>
                
                        </div>
                
                    </div>
                
                </div>
                

            </div>

            <!-- ================================================= -->
            <!-- ROLES -->
            <!-- ================================================= -->
            <div
                class="tab-pane fade"
                id="roles"
                role="tabpanel"
                aria-labelledby="roles-tab"
                tabindex="0"
            >
                <div class="config-tab-head">
                    <h5 class="config-title">Gestión de Roles</h5>

                    <button
                        type="button"
                        class="btn-templo-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modalNuevoRol"
                    >
                        <i class="bi bi-plus-circle"></i>
                        Nuevo Rol
                    </button>
                </div>

                <div class="table-zone configuracion-table-zone">
                    <div class="templo-table-wrapper">
                        <div class="table-responsive">
                            <table
                                id="tablaRoles"
                                class="table table-hover templo-table w-100"
                            >
                                <thead>
                                    <tr>
                                        <th>Rol</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th class="columna-acciones">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach (($listaRoles ?? []) as $rol) { ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars(
                                                    $rol['nombre_rol'] ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars(
                                                    $rol['descripcion'] ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>
                                            </td>

                                            <td>
                                                <?php
                                                $estadoRolVista = strtoupper(trim((string) (
                                                    $rol['estado_rol'] ?? ''
                                                )));
                                                ?>

                                                <span class="badge <?php echo $estadoRolVista === 'ACTIVO'
                                                    ? 'estado-registro-activo'
                                                    : 'estado-registro-inactivo'; ?>">
                                                    <?php echo htmlspecialchars(
                                                        $estadoRolVista,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ); ?>
                                                </span>
                                            </td>

                                            <td class="columna-acciones">
                                                <div class="btn-group-actions">

                                                    <!-- Botón Editar -->
                                                    <button
                                                        type="button"
                                                        class="btn-action btn-edit btnEditarRol"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEditarRol"
                                                        data-id-rol="<?php echo htmlspecialchars(
                                                            (string) ($rol['id_rol'] ?? ''),
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ); ?>"
                                                        data-nombre-rol="<?php echo htmlspecialchars(
                                                            $rol['nombre_rol'] ?? '',
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ); ?>"
                                                        data-descripcion="<?php echo htmlspecialchars(
                                                            $rol['descripcion'] ?? '',
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ); ?>"
                                                        data-estado-rol="<?php echo htmlspecialchars(
                                                            $rol['estado_rol'] ?? '',
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ); ?>"
                                                        title="Editar rol"
                                                        aria-label="Editar rol"
                                                    >
                                                        <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                                                    </button>

                                                    <!-- Botón Desactivar -->
                                                    <button
                                                        type="button"
                                                        class="btn-action btn-deactivate btnDesactivarRol"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEstadoRol"
                                                        data-id-rol="<?php echo htmlspecialchars(
                                                            (string) ($rol['id_rol'] ?? ''),
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ); ?>"
                                                        data-nombre-rol="<?php echo htmlspecialchars(
                                                            $rol['nombre_rol'] ?? '',
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ); ?>"
                                                        data-estado-rol="<?php echo htmlspecialchars(
                                                            $rol['estado_rol'] ?? '',
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ); ?>"
                                                        title="Desactivar rol"
                                                        aria-label="Desactivar rol"
                                                    >
                                                        <i class="bi bi-trash-fill" aria-hidden="true"></i>
                                                    </button>

                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</section>


<!-- ===================================================== -->
<!-- MODALES DETALLE DE USUARIO -->
<!-- ===================================================== -->

<?php foreach ($listaUsuarios as $usuario): ?>

    <?php

    $detalleIdUsuario =
        $usuario['id_usuario'] ?? '';

    $detalleCedula =
        $usuario['cedula'] ?? '';

    $detalleNombre =
        $usuario['nombre'] ?? '';

    $detalleApellido =
        $usuario['apellido'] ?? '';

    $detalleTelefono =
        $usuario['telefono'] ?? '';

    $detalleCorreo =
        $usuario['correo'] ?? '';

    $detalleDireccion =
        $usuario['direccion'] ?? '';

    $detalleCiudad =
        $usuario['ciudad'] ?? '';

    $detalleNickname =
        $usuario['nickname'] ?? '';

    $detalleRol =
        $usuario['nombre_rol'] ?? '';

    $detalleEstado = strtoupper(
        trim(
            (string) (
                $usuario['estado_usuario']
                ?? 'ACTIVO'
            )
        )
    );

    ?>

    <div
        class="modal fade"
        id="modalDetalleUsuario<?= (int) $detalleIdUsuario; ?>"
        tabindex="-1"
        aria-labelledby="modalDetalleUsuarioLabel<?= (int) $detalleIdUsuario; ?>"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered">

            <div class="modal-content templo-modal detalle-ficha-modal">

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="modalDetalleUsuarioLabel<?= (int) $detalleIdUsuario; ?>"
                    >
                        <span
                            class="detalle-ficha-title-icon"
                            aria-hidden="true"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    fill="none"
                                />
                                <path
                                    d="M12 16C10 14.8 8.5 13.5 8.5 11.8C8.5 10.5 9.5 9.5 10.8 9.5C11.5 9.5 12 9.9 12 10.4C12 9.9 12.5 9.5 13.2 9.5C14.5 9.5 15.5 10.5 15.5 11.8C15.5 13.5 14 14.8 12 16Z"
                                    fill="currentColor"
                                />
                            </svg>
                        </span>

                        Detalles del Usuario
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>

                </div>


                <div class="modal-body detalle-ficha-modal-body">

                    <div class="detalle-ficha-panel">

                        <!-- Encabezado de la ficha -->
                        <div class="detalle-ficha-intro">

                            <span
                                class="detalle-ficha-intro-icon"
                                aria-hidden="true"
                            >
                                <svg
                                    class="detalle-ficha-intro-svg"
                                    viewBox="0 0 64 64"
                                    fill="none"
                                >
                                    <rect
                                        x="8"
                                        y="8"
                                        width="48"
                                        height="48"
                                        rx="8"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                        fill="none"
                                    ></rect>

                                    <path d="M27 18H48" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                    <path d="M27 28H48" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                    <path d="M27 38H48" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                    <path d="M27 48H48" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>

                                    <path
                                        d="M18 14
                                           C16.9 12.4 14.4 12.3 13.4 14
                                           C12.3 15.8 13.3 17.9 18 21.5
                                           C22.7 17.9 23.7 15.8 22.6 14
                                           C21.6 12.3 19.1 12.4 18 14Z"
                                        fill="currentColor"
                                    ></path>

                                    <path
                                        d="M18 24
                                           C16.9 22.4 14.4 22.3 13.4 24
                                           C12.3 25.8 13.3 27.9 18 31.5
                                           C22.7 27.9 23.7 25.8 22.6 24
                                           C21.6 22.3 19.1 22.4 18 24Z"
                                        fill="currentColor"
                                    ></path>

                                    <path
                                        d="M18 34
                                           C16.9 32.4 14.4 32.3 13.4 34
                                           C12.3 35.8 13.3 37.9 18 41.5
                                           C22.7 37.9 23.7 35.8 22.6 34
                                           C21.6 32.3 19.1 32.4 18 34Z"
                                        fill="currentColor"
                                    ></path>

                                    <path
                                        d="M18 44
                                           C16.9 42.4 14.4 42.3 13.4 44
                                           C12.3 45.8 13.3 47.9 18 51.5
                                           C22.7 47.9 23.7 45.8 22.6 44
                                           C21.6 42.3 19.1 42.4 18 44Z"
                                        fill="currentColor"
                                    ></path>
                                </svg>
                            </span>

                            <div class="detalle-ficha-intro-content">
                                <p class="detalle-ficha-intro-title mb-0">
                                    Ficha del usuario
                                </p>
                            </div>

                        </div>


                        <!-- Datos del usuario -->
                        <div class="row g-2 detalle-ficha-grid">

                            <!-- Cédula -->
                            <div class="col-md-6">
                                <div class="detalle-ficha-card h-100">
                                    <span
                                        class="detalle-ficha-card-icon"
                                        aria-hidden="true"
                                    >
                                        <i class="bi bi-person-vcard"></i>
                                    </span>

                                    <div class="detalle-ficha-card-content">
                                        <span class="detalle-ficha-label">
                                            Cédula
                                        </span>

                                        <p class="detalle-ficha-value mb-0">
                                            <?= $escaparSeguridad(
                                                $detalleCedula !== ''
                                                    ? $detalleCedula
                                                    : 'No registrado'
                                            ); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <!-- Nickname -->
                            <div class="col-md-6">
                                <div class="detalle-ficha-card h-100">
                                    <span
                                        class="detalle-ficha-card-icon"
                                        aria-hidden="true"
                                    >
                                        <i class="bi bi-person-badge"></i>
                                    </span>

                                    <div class="detalle-ficha-card-content">
                                        <span class="detalle-ficha-label">
                                            Nickname
                                        </span>

                                        <p class="detalle-ficha-value mb-0">
                                            <?= $escaparSeguridad(
                                                $detalleNickname !== ''
                                                    ? $detalleNickname
                                                    : 'No registrado'
                                            ); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <!-- Nombre -->
                            <div class="col-md-6">
                                <div class="detalle-ficha-card h-100">
                                    <span
                                        class="detalle-ficha-card-icon"
                                        aria-hidden="true"
                                    >
                                        <i class="bi bi-person-heart"></i>
                                    </span>

                                    <div class="detalle-ficha-card-content">
                                        <span class="detalle-ficha-label">
                                            Nombre
                                        </span>

                                        <p class="detalle-ficha-value mb-0">
                                            <?= $escaparSeguridad(
                                                $detalleNombre !== ''
                                                    ? $detalleNombre
                                                    : 'No registrado'
                                            ); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <!-- Apellido -->
                            <div class="col-md-6">
                                <div class="detalle-ficha-card h-100">
                                    <span
                                        class="detalle-ficha-card-icon"
                                        aria-hidden="true"
                                    >
                                        <i class="bi bi-person-heart"></i>
                                    </span>

                                    <div class="detalle-ficha-card-content">
                                        <span class="detalle-ficha-label">
                                            Apellido
                                        </span>

                                        <p class="detalle-ficha-value mb-0">
                                            <?= $escaparSeguridad(
                                                $detalleApellido !== ''
                                                    ? $detalleApellido
                                                    : 'No registrado'
                                            ); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <!-- Teléfono -->
                            <div class="col-md-6">
                                <div class="detalle-ficha-card h-100">
                                    <span
                                        class="detalle-ficha-card-icon"
                                        aria-hidden="true"
                                    >
                                        <i class="bi bi-telephone-fill"></i>
                                    </span>

                                    <div class="detalle-ficha-card-content">
                                        <span class="detalle-ficha-label">
                                            Teléfono
                                        </span>

                                        <p class="detalle-ficha-value mb-0">
                                            <?= $escaparSeguridad(
                                                $detalleTelefono !== ''
                                                    ? $detalleTelefono
                                                    : 'No registrado'
                                            ); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <!-- Correo electrónico -->
                            <div class="col-md-6">
                                <div class="detalle-ficha-card h-100">
                                    <span
                                        class="detalle-ficha-card-icon"
                                        aria-hidden="true"
                                    >
                                        <i class="bi bi-envelope-heart"></i>
                                    </span>

                                    <div class="detalle-ficha-card-content">
                                        <span class="detalle-ficha-label">
                                            Correo electrónico
                                        </span>

                                        <p class="detalle-ficha-value mb-0">
                                            <?= $escaparSeguridad(
                                                $detalleCorreo !== ''
                                                    ? $detalleCorreo
                                                    : 'No registrado'
                                            ); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <!-- Ciudad -->
                            <div class="col-md-6">
                                <div class="detalle-ficha-card h-100">
                                    <span
                                        class="detalle-ficha-card-icon"
                                        aria-hidden="true"
                                    >
                                        <i class="bi bi-geo-alt-fill"></i>
                                    </span>

                                    <div class="detalle-ficha-card-content">
                                        <span class="detalle-ficha-label">
                                            Ciudad
                                        </span>

                                        <p class="detalle-ficha-value mb-0">
                                            <?= $escaparSeguridad(
                                                $detalleCiudad !== ''
                                                    ? $detalleCiudad
                                                    : 'No registrado'
                                            ); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <!-- Rol -->
                            <div class="col-md-6">
                                <div class="detalle-ficha-card h-100">
                                    <span
                                        class="detalle-ficha-card-icon"
                                        aria-hidden="true"
                                    >
                                        <i class="bi bi-person-gear"></i>
                                    </span>

                                    <div class="detalle-ficha-card-content">
                                        <span class="detalle-ficha-label">
                                            Rol
                                        </span>

                                        <p class="detalle-ficha-value mb-0">
                                            <?= $escaparSeguridad(
                                                $detalleRol !== ''
                                                    ? $detalleRol
                                                    : 'No registrado'
                                            ); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <!-- Dirección -->
                            <div class="col-12">
                                <div class="detalle-ficha-card detalle-ficha-card-wide detalle-ficha-card-emphasis">
                                    <span
                                        class="detalle-ficha-card-icon"
                                        aria-hidden="true"
                                    >
                                        <i class="bi bi-house-heart-fill"></i>
                                    </span>

                                    <div class="detalle-ficha-card-content">
                                        <span class="detalle-ficha-label">
                                            Dirección
                                        </span>

                                        <p class="detalle-ficha-value mb-0">
                                            <?= $escaparSeguridad(
                                                $detalleDireccion !== ''
                                                    ? $detalleDireccion
                                                    : 'No registrado'
                                            ); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <!-- Clave -->
                            <div class="col-md-6">
                                <div class="detalle-ficha-card detalle-ficha-card-emphasis h-100">
                                    <span
                                        class="detalle-ficha-card-icon"
                                        aria-hidden="true"
                                    >
                                        <i class="bi bi-lock-fill"></i>
                                    </span>

                                    <div class="detalle-ficha-card-content">
                                        <span class="detalle-ficha-label">
                                            Clave
                                        </span>

                                        <p class="detalle-ficha-value required-mark mb-0">
                                            ********
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <!-- Estado -->
                            <div class="col-md-6">
                                <div class="detalle-ficha-card detalle-ficha-card-emphasis h-100">
                                    <span
                                        class="detalle-ficha-card-icon"
                                        aria-hidden="true"
                                    >
                                        <i class="bi bi-check-circle"></i>
                                    </span>

                                    <div class="detalle-ficha-card-content">
                                        <span class="detalle-ficha-label">
                                            Estado
                                        </span>

                                        <span class="detalle-ficha-status">
                                            <?= $escaparSeguridad(
                                                $detalleEstado
                                            ); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn-templo-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle"></i>
                        Cerrar
                    </button>

                </div>

            </div>

        </div>
    </div>

<?php endforeach; ?>


<!-- ===================================================== -->
<!-- MODAL REGISTRAR USUARIO -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalNuevoUsuario"
    tabindex="-1"
    aria-labelledby="modalNuevoUsuarioLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content templo-modal">

            <form
                id="formNuevoUsuario"
                action="Index.php?url=seguridad"
                method="POST"
                autocomplete="off"
            >

                <input
                    type="hidden"
                    name="accion"
                    value="registrar_usuario"
                >

                <input
                    type="hidden"
                    name="id_persona"
                    id="nuevoUsuarioIdPersona"
                >


                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="modalNuevoUsuarioLabel"
                    >
                        <i class="bi bi-person-plus"></i>
                        Registrar Usuario
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>

                </div>


                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-12">

                            <h6 class="fw-bold mb-1">
                                <i class="bi bi-person-heart me-1"></i>
                                Datos de la persona
                            </h6>

                        </div>


                        <div class="col-md-4">

                            <label
                                for="nuevoUsuarioCedula"
                                class="form-label"
                            >
                                Cédula
                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                type="text"
                                id="nuevoUsuarioCedula"
                                name="cedula"
                                class="form-control templo-input"
                                placeholder="Ingrese la cédula"
                                maxlength="20"
                                required
                            >

                        </div>


                        <div class="col-md-4">

                            <label
                                for="nuevoUsuarioNombre"
                                class="form-label"
                            >
                                Nombre
                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                type="text"
                                id="nuevoUsuarioNombre"
                                name="nombre"
                                class="form-control templo-input"
                                placeholder="Ingrese el nombre"
                                maxlength="60"
                                required
                            >

                        </div>


                        <div class="col-md-4">

                            <label
                                for="nuevoUsuarioApellido"
                                class="form-label"
                            >
                                Apellido
                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                type="text"
                                id="nuevoUsuarioApellido"
                                name="apellido"
                                class="form-control templo-input"
                                placeholder="Ingrese el apellido"
                                maxlength="60"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label
                                for="nuevoUsuarioTelefono"
                                class="form-label"
                            >
                                Teléfono

                                <span class="fw-normal">
                                    (opcional)
                                </span>
                            </label>

                            <input
                                type="tel"
                                id="nuevoUsuarioTelefono"
                                name="telefono"
                                class="form-control templo-input"
                                placeholder="Ingrese el teléfono"
                                maxlength="25"
                            >

                        </div>


                        <div class="col-md-6">

                            <label
                                for="nuevoUsuarioCorreo"
                                class="form-label"
                            >
                                Correo electrónico

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                type="email"
                                id="nuevoUsuarioCorreo"
                                name="correo"
                                class="form-control templo-input"
                                placeholder="Ingrese el correo electrónico"
                                maxlength="100"
                                required
                            >

                            <small class="templo-help-text">
                                Este correo quedará asociado a la cuenta del usuario.
                            </small>

                        </div>


                        <div class="col-md-6">

                            <label
                                for="nuevoUsuarioDireccion"
                                class="form-label"
                            >
                                Dirección
                            </label>

                            <input
                                type="text"
                                id="nuevoUsuarioDireccion"
                                name="direccion"
                                class="form-control templo-input"
                                placeholder="Ingrese la dirección"
                                maxlength="150"
                            >

                        </div>


                        <div class="col-md-6">

                            <label
                                for="nuevoUsuarioCiudad"
                                class="form-label"
                            >
                                Ciudad
                            </label>

                            <input
                                type="text"
                                id="nuevoUsuarioCiudad"
                                name="ciudad"
                                class="form-control templo-input"
                                placeholder="Ingrese la ciudad"
                                maxlength="80"
                            >

                        </div>


                        <div class="col-12 mt-4">

                            <h6 class="fw-bold mb-1">
                                <i class="bi bi-shield-heart me-1"></i>
                                Datos de la cuenta
                            </h6>

                        </div>


                        <div class="col-md-6">

                            <label
                                for="nuevoUsuarioNickname"
                                class="form-label"
                            >
                                Nickname (opcional)
                            </label>

                            <input
                                type="text"
                                id="nuevoUsuarioNickname"
                                name="nickname"
                                class="form-control templo-input"
                                placeholder="Ingrese un nickname"
                                maxlength="50"
                            >

                            <div class="form-text">
                                Si se deja vacío, se mostrará el nombre completo.
                            </div>

                        </div>


                        <div class="col-md-6">

                            <label
                                for="nuevoUsuarioRol"
                                class="form-label"
                            >
                                Rol

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <select
                                id="nuevoUsuarioRol"
                                name="id_rol"
                                class="form-select templo-input"
                                required
                            >

                                <option value="">
                                    Seleccione un rol
                                </option>

                                <?php foreach ($listaRoles as $rol): ?>

                                    <option
                                        value="<?= $escaparSeguridad(
                                            $rol['id_rol'] ?? ''
                                        ); ?>"
                                    >
                                        <?= $escaparSeguridad(
                                            $rol['nombre_rol'] ?? ''
                                        ); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="col-md-6">

                            <label
                                for="nuevoUsuarioClave"
                                class="form-label"
                            >
                                Contraseña

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <div class="templo-password-field">

                                <input
                                    type="password"
                                    id="nuevoUsuarioClave"
                                    name="clave"
                                    class="form-control templo-input"
                                    placeholder="Ingrese la contraseña"
                                    minlength="8"
                                    maxlength="255"
                                    autocomplete="new-password"
                                    required
                                >

                                <button
                                type="button"
                                class="templo-password-toggle btnAlternarClave"
                                data-password-target="nuevoUsuarioClave"
                                aria-label="Mostrar contraseña"
                                title="Mostrar contraseña"
                                >
                                <svg class="templo-eye-svg templo-eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <path d="M2.68936 6.70456C2.52619 6.32384 2.08528 6.14747 1.70456 6.31064C1.32384 6.47381 1.14747 6.91472 1.31064 7.29544L2.68936 6.70456ZM15.5872 13.3287L15.3125 12.6308L15.5872 13.3287ZM9.04145 13.7377C9.26736 13.3906 9.16904 12.926 8.82185 12.7001C8.47466 12.4742 8.01008 12.5725 7.78417 12.9197L9.04145 13.7377ZM6.37136 15.091C6.14545 15.4381 6.24377 15.9027 6.59096 16.1286C6.93815 16.3545 7.40273 16.2562 7.62864 15.909L6.37136 15.091ZM22.6894 7.29544C22.8525 6.91472 22.6762 6.47381 22.2954 6.31064C21.9147 6.14747 21.4738 6.32384 21.3106 6.70456L22.6894 7.29544ZM19 11.1288L18.4867 10.582V10.582L19 11.1288ZM19.9697 13.1592C20.2626 13.4521 20.7374 13.4521 21.0303 13.1592C21.3232 12.8663 21.3232 12.3914 21.0303 12.0985L19.9697 13.1592ZM11.25 16.5C11.25 16.9142 11.5858 17.25 12 17.25C12.4142 17.25 12.75 16.9142 12.75 16.5H11.25ZM16.3714 15.909C16.5973 16.2562 17.0619 16.3545 17.409 16.1286C17.7562 15.9027 17.8545 15.4381 17.6286 15.091L16.3714 15.909ZM5.53033 11.6592C5.82322 11.3663 5.82322 10.8914 5.53033 10.5985C5.23744 10.3056 4.76256 10.3056 4.46967 10.5985L5.53033 11.6592ZM2.96967 12.0985C2.67678 12.3914 2.67678 12.8663 2.96967 13.1592C3.26256 13.4521 3.73744 13.4521 4.03033 13.1592L2.96967 12.0985ZM12 13.25C8.77611 13.25 6.46133 11.6446 4.9246 9.98966C4.15645 9.16243 3.59325 8.33284 3.22259 7.71014C3.03769 7.3995 2.90187 7.14232 2.8134 6.96537C2.76919 6.87696 2.73689 6.80875 2.71627 6.76411C2.70597 6.7418 2.69859 6.7254 2.69411 6.71533C2.69187 6.7103 2.69036 6.70684 2.68957 6.70503C2.68917 6.70413 2.68896 6.70363 2.68892 6.70355C2.68891 6.70351 2.68893 6.70357 2.68901 6.70374C2.68904 6.70382 2.68913 6.70403 2.68915 6.70407C2.68925 6.7043 2.68936 6.70456 2 7C1.31064 7.29544 1.31077 7.29575 1.31092 7.29609C1.31098 7.29624 1.31114 7.2966 1.31127 7.2969C1.31152 7.29749 1.31183 7.2982 1.31218 7.299C1.31287 7.30062 1.31376 7.30266 1.31483 7.30512C1.31698 7.31003 1.31988 7.31662 1.32353 7.32483C1.33083 7.34125 1.34115 7.36415 1.35453 7.39311C1.38127 7.45102 1.42026 7.5332 1.47176 7.63619C1.57469 7.84206 1.72794 8.13175 1.93366 8.47736C2.34425 9.16716 2.96855 10.0876 3.8254 11.0103C5.53867 12.8554 8.22389 14.75 12 14.75V13.25ZM15.3125 12.6308C14.3421 13.0128 13.2417 13.25 12 13.25V14.75C13.4382 14.75 14.7246 14.4742 15.8619 14.0266L15.3125 12.6308ZM7.78417 12.9197L6.37136 15.091L7.62864 15.909L9.04145 13.7377L7.78417 12.9197ZM22 7C21.3106 6.70456 21.3107 6.70441 21.3108 6.70427C21.3108 6.70423 21.3108 6.7041 21.3109 6.70402C21.3109 6.70388 21.311 6.70376 21.311 6.70368C21.3111 6.70352 21.3111 6.70349 21.3111 6.7036C21.311 6.7038 21.3107 6.70452 21.3101 6.70576C21.309 6.70823 21.307 6.71275 21.3041 6.71924C21.2983 6.73223 21.2889 6.75309 21.2758 6.78125C21.2495 6.83757 21.2086 6.92295 21.1526 7.03267C21.0406 7.25227 20.869 7.56831 20.6354 7.9432C20.1669 8.69516 19.4563 9.67197 18.4867 10.582L19.5133 11.6757C20.6023 10.6535 21.3917 9.56587 21.9085 8.73646C22.1676 8.32068 22.36 7.9668 22.4889 7.71415C22.5533 7.58775 22.602 7.48643 22.6353 7.41507C22.6519 7.37939 22.6647 7.35118 22.6737 7.33104C22.6782 7.32097 22.6818 7.31292 22.6844 7.30696C22.6857 7.30398 22.6867 7.30153 22.6876 7.2996C22.688 7.29864 22.6883 7.29781 22.6886 7.29712C22.6888 7.29677 22.6889 7.29646 22.689 7.29618C22.6891 7.29604 22.6892 7.29585 22.6892 7.29578C22.6893 7.29561 22.6894 7.29544 22 7ZM18.4867 10.582C17.6277 11.3882 16.5739 12.1343 15.3125 12.6308L15.8619 14.0266C17.3355 13.4466 18.5466 12.583 19.5133 11.6757L18.4867 10.582ZM18.4697 11.6592L19.9697 13.1592L21.0303 12.0985L19.5303 10.5985L18.4697 11.6592ZM11.25 14V16.5H12.75V14H11.25ZM14.9586 13.7377L16.3714 15.909L17.6286 15.091L16.2158 12.9197L14.9586 13.7377ZM4.46967 10.5985L2.96967 12.0985L4.03033 13.1592L5.53033 11.6592L4.46967 10.5985Z" fill="currentColor" />
                                </svg>
                                <svg class="templo-eye-svg templo-eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <path d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                                <path d="M12 16C10 14.8 8.5 13.5 8.5 11.8C8.5 10.5 9.5 9.5 10.8 9.5C11.5 9.5 12 9.9 12 10.4C12 9.9 12.5 9.5 13.2 9.5C14.5 9.5 15.5 10.5 15.5 11.8C15.5 13.5 14 14.8 12 16Z" fill="currentColor" />
                                </svg>
                                </button>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label
                                for="nuevoUsuarioConfirmarClave"
                                class="form-label"
                            >
                                Confirmar contraseña

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <div class="templo-password-field">

                                <input
                                    type="password"
                                    id="nuevoUsuarioConfirmarClave"
                                    name="confirmar_clave"
                                    class="form-control templo-input"
                                    placeholder="Repita la contraseña"
                                    minlength="8"
                                    maxlength="255"
                                    autocomplete="new-password"
                                    required
                                >

                                <button
                                type="button"
                                class="templo-password-toggle btnAlternarClave"
                                data-password-target="nuevoUsuarioConfirmarClave"
                                aria-label="Mostrar confirmación de contraseña"
                                title="Mostrar confirmación de contraseña"
                                >
                                <svg class="templo-eye-svg templo-eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <path d="M2.68936 6.70456C2.52619 6.32384 2.08528 6.14747 1.70456 6.31064C1.32384 6.47381 1.14747 6.91472 1.31064 7.29544L2.68936 6.70456ZM15.5872 13.3287L15.3125 12.6308L15.5872 13.3287ZM9.04145 13.7377C9.26736 13.3906 9.16904 12.926 8.82185 12.7001C8.47466 12.4742 8.01008 12.5725 7.78417 12.9197L9.04145 13.7377ZM6.37136 15.091C6.14545 15.4381 6.24377 15.9027 6.59096 16.1286C6.93815 16.3545 7.40273 16.2562 7.62864 15.909L6.37136 15.091ZM22.6894 7.29544C22.8525 6.91472 22.6762 6.47381 22.2954 6.31064C21.9147 6.14747 21.4738 6.32384 21.3106 6.70456L22.6894 7.29544ZM19 11.1288L18.4867 10.582V10.582L19 11.1288ZM19.9697 13.1592C20.2626 13.4521 20.7374 13.4521 21.0303 13.1592C21.3232 12.8663 21.3232 12.3914 21.0303 12.0985L19.9697 13.1592ZM11.25 16.5C11.25 16.9142 11.5858 17.25 12 17.25C12.4142 17.25 12.75 16.9142 12.75 16.5H11.25ZM16.3714 15.909C16.5973 16.2562 17.0619 16.3545 17.409 16.1286C17.7562 15.9027 17.8545 15.4381 17.6286 15.091L16.3714 15.909ZM5.53033 11.6592C5.82322 11.3663 5.82322 10.8914 5.53033 10.5985C5.23744 10.3056 4.76256 10.3056 4.46967 10.5985L5.53033 11.6592ZM2.96967 12.0985C2.67678 12.3914 2.67678 12.8663 2.96967 13.1592C3.26256 13.4521 3.73744 13.4521 4.03033 13.1592L2.96967 12.0985ZM12 13.25C8.77611 13.25 6.46133 11.6446 4.9246 9.98966C4.15645 9.16243 3.59325 8.33284 3.22259 7.71014C3.03769 7.3995 2.90187 7.14232 2.8134 6.96537C2.76919 6.87696 2.73689 6.80875 2.71627 6.76411C2.70597 6.7418 2.69859 6.7254 2.69411 6.71533C2.69187 6.7103 2.69036 6.70684 2.68957 6.70503C2.68917 6.70413 2.68896 6.70363 2.68892 6.70355C2.68891 6.70351 2.68893 6.70357 2.68901 6.70374C2.68904 6.70382 2.68913 6.70403 2.68915 6.70407C2.68925 6.7043 2.68936 6.70456 2 7C1.31064 7.29544 1.31077 7.29575 1.31092 7.29609C1.31098 7.29624 1.31114 7.2966 1.31127 7.2969C1.31152 7.29749 1.31183 7.2982 1.31218 7.299C1.31287 7.30062 1.31376 7.30266 1.31483 7.30512C1.31698 7.31003 1.31988 7.31662 1.32353 7.32483C1.33083 7.34125 1.34115 7.36415 1.35453 7.39311C1.38127 7.45102 1.42026 7.5332 1.47176 7.63619C1.57469 7.84206 1.72794 8.13175 1.93366 8.47736C2.34425 9.16716 2.96855 10.0876 3.8254 11.0103C5.53867 12.8554 8.22389 14.75 12 14.75V13.25ZM15.3125 12.6308C14.3421 13.0128 13.2417 13.25 12 13.25V14.75C13.4382 14.75 14.7246 14.4742 15.8619 14.0266L15.3125 12.6308ZM7.78417 12.9197L6.37136 15.091L7.62864 15.909L9.04145 13.7377L7.78417 12.9197ZM22 7C21.3106 6.70456 21.3107 6.70441 21.3108 6.70427C21.3108 6.70423 21.3108 6.7041 21.3109 6.70402C21.3109 6.70388 21.311 6.70376 21.311 6.70368C21.3111 6.70352 21.3111 6.70349 21.3111 6.7036C21.311 6.7038 21.3107 6.70452 21.3101 6.70576C21.309 6.70823 21.307 6.71275 21.3041 6.71924C21.2983 6.73223 21.2889 6.75309 21.2758 6.78125C21.2495 6.83757 21.2086 6.92295 21.1526 7.03267C21.0406 7.25227 20.869 7.56831 20.6354 7.9432C20.1669 8.69516 19.4563 9.67197 18.4867 10.582L19.5133 11.6757C20.6023 10.6535 21.3917 9.56587 21.9085 8.73646C22.1676 8.32068 22.36 7.9668 22.4889 7.71415C22.5533 7.58775 22.602 7.48643 22.6353 7.41507C22.6519 7.37939 22.6647 7.35118 22.6737 7.33104C22.6782 7.32097 22.6818 7.31292 22.6844 7.30696C22.6857 7.30398 22.6867 7.30153 22.6876 7.2996C22.688 7.29864 22.6883 7.29781 22.6886 7.29712C22.6888 7.29677 22.6889 7.29646 22.689 7.29618C22.6891 7.29604 22.6892 7.29585 22.6892 7.29578C22.6893 7.29561 22.6894 7.29544 22 7ZM18.4867 10.582C17.6277 11.3882 16.5739 12.1343 15.3125 12.6308L15.8619 14.0266C17.3355 13.4466 18.5466 12.583 19.5133 11.6757L18.4867 10.582ZM18.4697 11.6592L19.9697 13.1592L21.0303 12.0985L19.5303 10.5985L18.4697 11.6592ZM11.25 14V16.5H12.75V14H11.25ZM14.9586 13.7377L16.3714 15.909L17.6286 15.091L16.2158 12.9197L14.9586 13.7377ZM4.46967 10.5985L2.96967 12.0985L4.03033 13.1592L5.53033 11.6592L4.46967 10.5985Z" fill="currentColor" />
                                </svg>
                                <svg class="templo-eye-svg templo-eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <path d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                                <path d="M12 16C10 14.8 8.5 13.5 8.5 11.8C8.5 10.5 9.5 9.5 10.8 9.5C11.5 9.5 12 9.9 12 10.4C12 9.9 12.5 9.5 13.2 9.5C14.5 9.5 15.5 10.5 15.5 11.8C15.5 13.5 14 14.8 12 16Z" fill="currentColor" />
                                </svg>
                                </button>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="submit"
                        class="btn-templo-primary"
                    >
                        <i class="bi bi-save"></i>
                        Guardar Usuario
                    </button>

                    <button
                        type="button"
                        class="btn-templo-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>


<!-- ===================================================== -->
<!-- MODAL EDITAR USUARIO -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalEditarUsuario"
    tabindex="-1"
    aria-labelledby="modalEditarUsuarioLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content templo-modal">

            <form
                id="formEditarUsuario"
                action="Index.php?url=seguridad"
                method="POST"
                autocomplete="off"
            >

                <input
                    type="hidden"
                    name="accion"
                    value="modificar_usuario"
                >

                <input
                    type="hidden"
                    name="id_usuario"
                    id="editarUsuarioId"
                >

                <input
                    type="hidden"
                    name="id_persona"
                    id="editarUsuarioIdPersona"
                >


                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="modalEditarUsuarioLabel"
                    >
                        <i class="bi bi-pencil-square"></i>
                        Editar Usuario
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>

                </div>


                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-12">

                            <h6 class="fw-bold mb-1">
                                <i class="bi bi-person-heart me-1"></i>
                                Datos de la persona
                            </h6>

                        </div>


                        <div class="col-md-4">

                            <label
                                for="editarUsuarioCedula"
                                class="form-label"
                            >
                                Cédula
                            </label>

                            <input
                                type="text"
                                id="editarUsuarioCedula"
                                class="form-control templo-input"
                                readonly
                            >

                            <small class="templo-help-text">
                                La cédula identifica a la persona en el sistema.
                            </small>

                        </div>


                        <div class="col-md-4">

                            <label
                                for="editarUsuarioNombre"
                                class="form-label"
                            >
                                Nombre

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                type="text"
                                id="editarUsuarioNombre"
                                name="nombre"
                                class="form-control templo-input"
                                maxlength="60"
                                required
                            >

                        </div>


                        <div class="col-md-4">

                            <label
                                for="editarUsuarioApellido"
                                class="form-label"
                            >
                                Apellido

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                type="text"
                                id="editarUsuarioApellido"
                                name="apellido"
                                class="form-control templo-input"
                                maxlength="60"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label
                                for="editarUsuarioTelefono"
                                class="form-label"
                            >
                                Teléfono

                                <span class="fw-normal">
                                    (opcional)
                                </span>
                            </label>

                            <input
                                type="tel"
                                id="editarUsuarioTelefono"
                                name="telefono"
                                class="form-control templo-input"
                                maxlength="25"
                            >

                        </div>


                        <div class="col-md-6">

                            <label
                                for="editarUsuarioCorreo"
                                class="form-label"
                            >
                                Correo electrónico

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                type="email"
                                id="editarUsuarioCorreo"
                                name="correo"
                                class="form-control templo-input"
                                maxlength="100"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label
                                for="editarUsuarioDireccion"
                                class="form-label"
                            >
                                Dirección
                            </label>

                            <input
                                type="text"
                                id="editarUsuarioDireccion"
                                name="direccion"
                                class="form-control templo-input"
                                maxlength="150"
                            >

                        </div>


                        <div class="col-md-6">

                            <label
                                for="editarUsuarioCiudad"
                                class="form-label"
                            >
                                Ciudad
                            </label>

                            <input
                                type="text"
                                id="editarUsuarioCiudad"
                                name="ciudad"
                                class="form-control templo-input"
                                maxlength="80"
                            >

                        </div>


                        <div class="col-12 mt-4">

                            <h6 class="fw-bold mb-1">
                                <i class="bi bi-shield-heart me-1"></i>
                                Datos de la cuenta
                            </h6>

                        </div>


                        <div class="col-md-6">

                            <label
                                for="editarUsuarioNickname"
                                class="form-label"
                            >
                                Nickname (opcional)
                            </label>

                            <input
                                type="text"
                                id="editarUsuarioNickname"
                                name="nickname"
                                class="form-control templo-input"
                                maxlength="50"
                            >

                            <div class="form-text">
                                Si se deja vacío, se mostrará el nombre completo.
                            </div>

                        </div>


                        <div class="col-md-6">

                            <label
                                for="editarUsuarioRol"
                                class="form-label"
                            >
                                Rol

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <select
                                id="editarUsuarioRol"
                                name="id_rol"
                                class="form-select templo-input"
                                required
                            >

                                <option value="">
                                    Seleccione un rol
                                </option>

                                <?php foreach ($listaRoles as $rol): ?>

                                    <option
                                        value="<?= $escaparSeguridad(
                                            $rol['id_rol'] ?? ''
                                        ); ?>"
                                    >
                                        <?= $escaparSeguridad(
                                            $rol['nombre_rol'] ?? ''
                                        ); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="submit"
                        class="btn-templo-primary"
                    >
                        <i class="bi bi-save"></i>
                        Guardar Cambios
                    </button>

                    <button
                        type="button"
                        class="btn-templo-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>


<!-- ===================================================== -->
<!-- MODAL DESACTIVAR USUARIO -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalEstadoUsuario"
    tabindex="-1"
    aria-labelledby="modalEstadoUsuarioLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content templo-modal status-modal">

            <form
                id="formEstadoUsuario"
                action="Index.php?url=seguridad"
                method="POST"
            >

                <input
                    type="hidden"
                    name="accion"
                    value="cambiar_estado_usuario"
                >

                <input
                    type="hidden"
                    name="id_usuario"
                    id="estadoUsuarioId"
                >

                <input
                    type="hidden"
                    name="estado_usuario"
                    id="nuevoEstadoUsuario"
                    value="INACTIVO"
                >


                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="modalEstadoUsuarioLabel"
                    >
                        <i
                            class="bi bi-trash-fill"
                            id="iconoEstadoUsuario"
                        ></i>

                        <span id="tituloEstadoUsuario">
                            Desactivar Usuario
                        </span>
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>

                </div>


                <div class="modal-body text-center">

                    <p
                        class="status-question"
                        id="preguntaEstadoUsuario"
                    >
                        ¿Deseas desactivar este usuario?
                    </p>

                    <p
                        class="status-client-name"
                        id="nombreEstadoUsuario"
                    ></p>

                    <p
                        class="status-warning"
                        id="advertenciaEstadoUsuario"
                    >
                        El usuario permanecerá registrado, pero dejará de aparecer en la tabla de usuarios activos.
                    </p>

                </div>


                <div class="modal-footer">

                    <button
                        type="submit"
                        class="btn-templo-danger"
                        id="btnConfirmarEstadoUsuario"
                    >
                        <i class="bi bi-trash-fill"></i>
                        Sí, desactivar
                    </button>

                    <button
                        type="button"
                        class="btn-templo-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>


<!-- ===================================================== -->
<!-- MODALES DE ROLES -->
<!-- ===================================================== -->

<div class="modal fade" id="modalNuevoRol" tabindex="-1" aria-labelledby="modalNuevoRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">
            <form action="Index.php?url=seguridad" method="POST">
                <input type="hidden" name="accion" value="registrar_rol">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoRolLabel">
                        <i class="bi bi-plus-circle"></i>
                        Registrar Nuevo Rol
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del rol <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="nombre_rol" class="form-control templo-input" placeholder="Ingrese el nombre del rol" maxlength="40" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <input
                                type="text"
                                name="estado_rol"
                                class="form-control templo-input"
                                value="ACTIVO"
                                readonly
                            >
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control templo-input" rows="3" maxlength="150" placeholder="Ingrese la descripción del rol"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-templo-primary">
                        <i class="bi bi-save"></i>
                        Guardar
                    </button>
                    <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarRol" tabindex="-1" aria-labelledby="modalEditarRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">
            <form action="Index.php?url=seguridad" method="POST">
                <input type="hidden" name="accion" value="modificar_rol">
                <input type="hidden" name="id_rol" id="editarIdRol">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarRolLabel">
                        <i class="bi bi-pencil-square"></i>
                        Editar Rol
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del rol <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="nombre_rol" id="editarNombreRol" class="form-control templo-input" maxlength="40" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <input
                                type="text"
                                name="estado_rol"
                                id="editarEstadoRol"
                                class="form-control templo-input"
                                readonly
                            >
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" id="editarDescripcionRol" class="form-control templo-input" rows="3" maxlength="150"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-templo-primary">
                        <i class="bi bi-save"></i>
                        Guardar Cambios
                    </button>
                    <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEstadoRol" tabindex="-1" aria-labelledby="modalEstadoRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content templo-modal status-modal">
            <form action="Index.php?url=seguridad" method="POST">
                <input type="hidden" name="accion" value="cambiar_estado_rol">
                <input type="hidden" name="id_rol" id="estadoIdRol">
                <input type="hidden" name="estado_rol" id="nuevoEstadoRol">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEstadoRolLabel">
                        <i class="bi bi-exclamation-triangle"></i>
                        Cambiar estado del rol
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body text-center">
                    <p class="status-question">¿Deseas cambiar el estado de este rol?</p>
                    <p class="status-client-name" id="nombreEstadoRol"></p>
                    <p class="status-warning">El registro permanecerá guardado en el sistema.</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn-templo-danger">
                        <i class="bi bi-check-circle"></i>
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ===================================================== -->
<!-- MODAL RESTABLECER CONTRASEÑA -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalRestablecerClave"
    tabindex="-1"
    aria-labelledby="modalRestablecerClaveLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content templo-modal">

            <form
                id="formRestablecerClave"
                action="Index.php?url=seguridad"
                method="POST"
                autocomplete="off"
            >

                <input
                    type="hidden"
                    name="accion"
                    value="restablecer_clave"
                >

                <input
                    type="hidden"
                    id="restablecerClaveIdUsuario"
                    name="id_usuario"
                >

                <input
                    type="hidden"
                    name="token_csrf"
                    value="<?= $escaparSeguridad(
                        $tokenRestablecerClave
                    ); ?>"
                >


                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="modalRestablecerClaveLabel"
                    >
                        <i class="bi bi-bag-heart"></i>

                        Restablecer Contraseña
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>

                </div>


                <div class="modal-body">

                    <p class="mb-4">

                        Asigne una contraseña temporal al usuario
                        seleccionado. Posteriormente, el usuario podrá
                        cambiarla desde las opciones de su perfil.

                    </p>


                    <div class="mb-3">

                        <label
                            for="restablecerClaveUsuario"
                            class="form-label"
                        >
                            Usuario
                        </label>

                        <input
                            type="text"
                            id="restablecerClaveUsuario"
                            class="form-control templo-input"
                            readonly
                        >

                    </div>


                    <div class="mb-3">

                        <label
                            for="restablecerNuevaClave"
                            class="form-label"
                        >
                            Contraseña temporal

                            <span
                                class="required-mark"
                                aria-hidden="true"
                            >
                                *
                            </span>
                        </label>

                        <div class="templo-password-field">

                            <input
                                type="password"
                                id="restablecerNuevaClave"
                                name="nueva_clave"
                                class="form-control templo-input"
                                placeholder="Ingrese la contraseña temporal"
                                minlength="8"
                                maxlength="255"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="templo-password-toggle btnAlternarClave"
                                data-password-target="restablecerNuevaClave"
                                aria-label="Mostrar contraseña temporal"
                                title="Mostrar contraseña temporal"
                            >
                                <svg class="templo-eye-svg templo-eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                    <path d="M2.68936 6.70456C2.52619 6.32384 2.08528 6.14747 1.70456 6.31064C1.32384 6.47381 1.14747 6.91472 1.31064 7.29544L2.68936 6.70456ZM15.5872 13.3287L15.3125 12.6308L15.5872 13.3287ZM9.04145 13.7377C9.26736 13.3906 9.16904 12.926 8.82185 12.7001C8.47466 12.4742 8.01008 12.5725 7.78417 12.9197L9.04145 13.7377ZM6.37136 15.091C6.14545 15.4381 6.24377 15.9027 6.59096 16.1286C6.93815 16.3545 7.40273 16.2562 7.62864 15.909L6.37136 15.091ZM22.6894 7.29544C22.8525 6.91472 22.6762 6.47381 22.2954 6.31064C21.9147 6.14747 21.4738 6.32384 21.3106 6.70456L22.6894 7.29544ZM19 11.1288L18.4867 10.582V10.582L19 11.1288ZM19.9697 13.1592C20.2626 13.4521 20.7374 13.4521 21.0303 13.1592C21.3232 12.8663 21.3232 12.3914 21.0303 12.0985L19.9697 13.1592ZM11.25 16.5C11.25 16.9142 11.5858 17.25 12 17.25C12.4142 17.25 12.75 16.9142 12.75 16.5H11.25ZM16.3714 15.909C16.5973 16.2562 17.0619 16.3545 17.409 16.1286C17.7562 15.9027 17.8545 15.4381 17.6286 15.091L16.3714 15.909ZM5.53033 11.6592C5.82322 11.3663 5.82322 10.8914 5.53033 10.5985C5.23744 10.3056 4.76256 10.3056 4.46967 10.5985L5.53033 11.6592ZM2.96967 12.0985C2.67678 12.3914 2.67678 12.8663 2.96967 13.1592C3.26256 13.4521 3.73744 13.4521 4.03033 13.1592L2.96967 12.0985ZM12 13.25C8.77611 13.25 6.46133 11.6446 4.9246 9.98966C4.15645 9.16243 3.59325 8.33284 3.22259 7.71014C3.03769 7.3995 2.90187 7.14232 2.8134 6.96537C2.76919 6.87696 2.73689 6.80875 2.71627 6.76411C2.70597 6.7418 2.69859 6.7254 2.69411 6.71533C2.69187 6.7103 2.69036 6.70684 2.68957 6.70503C2.68917 6.70413 2.68896 6.70363 2.68892 6.70355C2.68891 6.70351 2.68893 6.70357 2.68901 6.70374C2.68904 6.70382 2.68913 6.70403 2.68915 6.70407C2.68925 6.7043 2.68936 6.70456 2 7C1.31064 7.29544 1.31077 7.29575 1.31092 7.29609C1.31098 7.29624 1.31114 7.2966 1.31127 7.2969C1.31152 7.29749 1.31183 7.2982 1.31218 7.299C1.31287 7.30062 1.31376 7.30266 1.31483 7.30512C1.31698 7.31003 1.31988 7.31662 1.32353 7.32483C1.33083 7.34125 1.34115 7.36415 1.35453 7.39311C1.38127 7.45102 1.42026 7.5332 1.47176 7.63619C1.57469 7.84206 1.72794 8.13175 1.93366 8.47736C2.34425 9.16716 2.96855 10.0876 3.8254 11.0103C5.53867 12.8554 8.22389 14.75 12 14.75V13.25ZM15.3125 12.6308C14.3421 13.0128 13.2417 13.25 12 13.25V14.75C13.4382 14.75 14.7246 14.4742 15.8619 14.0266L15.3125 12.6308ZM7.78417 12.9197L6.37136 15.091L7.62864 15.909L9.04145 13.7377L7.78417 12.9197ZM22 7C21.3106 6.70456 21.3107 6.70441 21.3108 6.70427C21.3108 6.70423 21.3108 6.7041 21.3109 6.70402C21.3109 6.70388 21.311 6.70376 21.311 6.70368C21.3111 6.70352 21.3111 6.70349 21.3111 6.7036C21.311 6.7038 21.3107 6.70452 21.3101 6.70576C21.309 6.70823 21.307 6.71275 21.3041 6.71924C21.2983 6.73223 21.2889 6.75309 21.2758 6.78125C21.2495 6.83757 21.2086 6.92295 21.1526 7.03267C21.0406 7.25227 20.869 7.56831 20.6354 7.9432C20.1669 8.69516 19.4563 9.67197 18.4867 10.582L19.5133 11.6757C20.6023 10.6535 21.3917 9.56587 21.9085 8.73646C22.1676 8.32068 22.36 7.9668 22.4889 7.71415C22.5533 7.58775 22.602 7.48643 22.6353 7.41507C22.6519 7.37939 22.6647 7.35118 22.6737 7.33104C22.6782 7.32097 22.6818 7.31292 22.6844 7.30696C22.6857 7.30398 22.6867 7.30153 22.6876 7.2996C22.688 7.29864 22.6883 7.29781 22.6886 7.29712C22.6888 7.29677 22.6889 7.29646 22.689 7.29618C22.6891 7.29604 22.6892 7.29585 22.6892 7.29578C22.6893 7.29561 22.6894 7.29544 22 7ZM18.4867 10.582C17.6277 11.3882 16.5739 12.1343 15.3125 12.6308L15.8619 14.0266C17.3355 13.4466 18.5466 12.583 19.5133 11.6757L18.4867 10.582ZM18.4697 11.6592L19.9697 13.1592L21.0303 12.0985L19.5303 10.5985L18.4697 11.6592ZM11.25 14V16.5H12.75V14H11.25ZM14.9586 13.7377L16.3714 15.909L17.6286 15.091L16.2158 12.9197L14.9586 13.7377ZM4.46967 10.5985L2.96967 12.0985L4.03033 13.1592L5.53033 11.6592L4.46967 10.5985Z" fill="currentColor" />
                                </svg>
                                <svg class="templo-eye-svg templo-eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                    <path d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                                    <path d="M12 16C10 14.8 8.5 13.5 8.5 11.8C8.5 10.5 9.5 9.5 10.8 9.5C11.5 9.5 12 9.9 12 10.4C12 9.9 12.5 9.5 13.2 9.5C14.5 9.5 15.5 10.5 15.5 11.8C15.5 13.5 14 14.8 12 16Z" fill="currentColor" />
                                </svg>
                            </button>

                        </div>

                    </div>


                    <div class="mb-3">

                        <label
                            for="restablecerConfirmarClave"
                            class="form-label"
                        >
                            Confirmar contraseña temporal

                            <span
                                class="required-mark"
                                aria-hidden="true"
                            >
                                *
                            </span>
                        </label>

                        <div class="templo-password-field">

                            <input
                                type="password"
                                id="restablecerConfirmarClave"
                                name="confirmar_clave"
                                class="form-control templo-input"
                                placeholder="Repita la contraseña temporal"
                                minlength="8"
                                maxlength="255"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="templo-password-toggle btnAlternarClave"
                                data-password-target="restablecerConfirmarClave"
                                aria-label="Mostrar confirmación de contraseña"
                                title="Mostrar confirmación de contraseña"
                            >
                                <svg class="templo-eye-svg templo-eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                    <path d="M2.68936 6.70456C2.52619 6.32384 2.08528 6.14747 1.70456 6.31064C1.32384 6.47381 1.14747 6.91472 1.31064 7.29544L2.68936 6.70456ZM15.5872 13.3287L15.3125 12.6308L15.5872 13.3287ZM9.04145 13.7377C9.26736 13.3906 9.16904 12.926 8.82185 12.7001C8.47466 12.4742 8.01008 12.5725 7.78417 12.9197L9.04145 13.7377ZM6.37136 15.091C6.14545 15.4381 6.24377 15.9027 6.59096 16.1286C6.93815 16.3545 7.40273 16.2562 7.62864 15.909L6.37136 15.091ZM22.6894 7.29544C22.8525 6.91472 22.6762 6.47381 22.2954 6.31064C21.9147 6.14747 21.4738 6.32384 21.3106 6.70456L22.6894 7.29544ZM19 11.1288L18.4867 10.582V10.582L19 11.1288ZM19.9697 13.1592C20.2626 13.4521 20.7374 13.4521 21.0303 13.1592C21.3232 12.8663 21.3232 12.3914 21.0303 12.0985L19.9697 13.1592ZM11.25 16.5C11.25 16.9142 11.5858 17.25 12 17.25C12.4142 17.25 12.75 16.9142 12.75 16.5H11.25ZM16.3714 15.909C16.5973 16.2562 17.0619 16.3545 17.409 16.1286C17.7562 15.9027 17.8545 15.4381 17.6286 15.091L16.3714 15.909ZM5.53033 11.6592C5.82322 11.3663 5.82322 10.8914 5.53033 10.5985C5.23744 10.3056 4.76256 10.3056 4.46967 10.5985L5.53033 11.6592ZM2.96967 12.0985C2.67678 12.3914 2.67678 12.8663 2.96967 13.1592C3.26256 13.4521 3.73744 13.4521 4.03033 13.1592L2.96967 12.0985ZM12 13.25C8.77611 13.25 6.46133 11.6446 4.9246 9.98966C4.15645 9.16243 3.59325 8.33284 3.22259 7.71014C3.03769 7.3995 2.90187 7.14232 2.8134 6.96537C2.76919 6.87696 2.73689 6.80875 2.71627 6.76411C2.70597 6.7418 2.69859 6.7254 2.69411 6.71533C2.69187 6.7103 2.69036 6.70684 2.68957 6.70503C2.68917 6.70413 2.68896 6.70363 2.68892 6.70355C2.68891 6.70351 2.68893 6.70357 2.68901 6.70374C2.68904 6.70382 2.68913 6.70403 2.68915 6.70407C2.68925 6.7043 2.68936 6.70456 2 7C1.31064 7.29544 1.31077 7.29575 1.31092 7.29609C1.31098 7.29624 1.31114 7.2966 1.31127 7.2969C1.31152 7.29749 1.31183 7.2982 1.31218 7.299C1.31287 7.30062 1.31376 7.30266 1.31483 7.30512C1.31698 7.31003 1.31988 7.31662 1.32353 7.32483C1.33083 7.34125 1.34115 7.36415 1.35453 7.39311C1.38127 7.45102 1.42026 7.5332 1.47176 7.63619C1.57469 7.84206 1.72794 8.13175 1.93366 8.47736C2.34425 9.16716 2.96855 10.0876 3.8254 11.0103C5.53867 12.8554 8.22389 14.75 12 14.75V13.25ZM15.3125 12.6308C14.3421 13.0128 13.2417 13.25 12 13.25V14.75C13.4382 14.75 14.7246 14.4742 15.8619 14.0266L15.3125 12.6308ZM7.78417 12.9197L6.37136 15.091L7.62864 15.909L9.04145 13.7377L7.78417 12.9197ZM22 7C21.3106 6.70456 21.3107 6.70441 21.3108 6.70427C21.3108 6.70423 21.3108 6.7041 21.3109 6.70402C21.3109 6.70388 21.311 6.70376 21.311 6.70368C21.3111 6.70352 21.3111 6.70349 21.3111 6.7036C21.311 6.7038 21.3107 6.70452 21.3101 6.70576C21.309 6.70823 21.307 6.71275 21.3041 6.71924C21.2983 6.73223 21.2889 6.75309 21.2758 6.78125C21.2495 6.83757 21.2086 6.92295 21.1526 7.03267C21.0406 7.25227 20.869 7.56831 20.6354 7.9432C20.1669 8.69516 19.4563 9.67197 18.4867 10.582L19.5133 11.6757C20.6023 10.6535 21.3917 9.56587 21.9085 8.73646C22.1676 8.32068 22.36 7.9668 22.4889 7.71415C22.5533 7.58775 22.602 7.48643 22.6353 7.41507C22.6519 7.37939 22.6647 7.35118 22.6737 7.33104C22.6782 7.32097 22.6818 7.31292 22.6844 7.30696C22.6857 7.30398 22.6867 7.30153 22.6876 7.2996C22.688 7.29864 22.6883 7.29781 22.6886 7.29712C22.6888 7.29677 22.6889 7.29646 22.689 7.29618C22.6891 7.29604 22.6892 7.29585 22.6892 7.29578C22.6893 7.29561 22.6894 7.29544 22 7ZM18.4867 10.582C17.6277 11.3882 16.5739 12.1343 15.3125 12.6308L15.8619 14.0266C17.3355 13.4466 18.5466 12.583 19.5133 11.6757L18.4867 10.582ZM18.4697 11.6592L19.9697 13.1592L21.0303 12.0985L19.5303 10.5985L18.4697 11.6592ZM11.25 14V16.5H12.75V14H11.25ZM14.9586 13.7377L16.3714 15.909L17.6286 15.091L16.2158 12.9197L14.9586 13.7377ZM4.46967 10.5985L2.96967 12.0985L4.03033 13.1592L5.53033 11.6592L4.46967 10.5985Z" fill="currentColor" />
                                </svg>
                                <svg class="templo-eye-svg templo-eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                    <path d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                                    <path d="M12 16C10 14.8 8.5 13.5 8.5 11.8C8.5 10.5 9.5 9.5 10.8 9.5C11.5 9.5 12 9.9 12 10.4C12 9.9 12.5 9.5 13.2 9.5C14.5 9.5 15.5 10.5 15.5 11.8C15.5 13.5 14 14.8 12 16Z" fill="currentColor" />
                                </svg>
                            </button>

                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn-templo-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle"></i>

                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn-templo-primary"
                    >
                        <i class="bi bi-bag-heart"></i>

                        Restablecer Contraseña
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>


<?php

$scriptVista = 'Assets/js/seguridad.js';

// El script registra la apertura al terminar de cargar Bootstrap en Footer.php.
?>

<?php if ($mensajeRestablecimiento): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalElemento = document.getElementById('modalResultadoRestablecimiento');

            if (modalElemento && window.bootstrap && window.bootstrap.Modal) {
                const modalResultado = new bootstrap.Modal(modalElemento);
                modalResultado.show();
            }
        });
    </script>
<?php endif; ?>

<?php
require_once __DIR__ . '/Layout/Footer.php';

?>