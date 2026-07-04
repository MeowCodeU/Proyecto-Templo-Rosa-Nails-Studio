<?php

$tituloPagina = 'Gestión de Usuarios';

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

?>


<section class="module-card clientes-module seguridad-module">

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

    Gestión de Usuarios

</h2>

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


    <div class="table-zone">

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
                            <th>Acciones</th>
                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($listaUsuarios as $usuario): ?>

                            <?php

                            $idUsuario = $usuario['id_usuario'] ?? '';
                            $idPersona = $usuario['id_persona'] ?? '';

                            $cedula = $usuario['cedula'] ?? '';
                            $nombre = $usuario['nombre'] ?? '';
                            $apellido = $usuario['apellido'] ?? '';
                            $telefono = $usuario['telefono'] ?? '';
                            $correo = $usuario['correo'] ?? '';

                            $idRol = $usuario['id_rol'] ?? '';
                            $nombreRol = $usuario['nombre_rol'] ?? '';

                            $estadoUsuario = strtoupper(
                                trim(
                                    (string) (
                                        $usuario['estado_usuario'] ??
                                        'ACTIVO'
                                    )
                                )
                            );

                            $nombreCompleto = trim(
                                $nombre . ' ' . $apellido
                            );

                            $usuarioActivo =
                                $estadoUsuario === 'ACTIVO';

                            ?>

                            <tr>

                                <td>
                                    <?= $escaparSeguridad($cedula); ?>
                                </td>

                                <td>
                                    <?= $escaparSeguridad($nombreCompleto); ?>
                                </td>

                                <td>
                                    <?= $escaparSeguridad($telefono); ?>
                                </td>

                                <td>
                                    <?= $escaparSeguridad($correo); ?>
                                </td>

                                <td>
                                    <?= $escaparSeguridad($nombreRol); ?>
                                </td>

                                <td>

                                    <span
                                        class="badge <?= $usuarioActivo
                                            ? 'estado-realizada'
                                            : 'estado-cancelada'; ?>"
                                    >
                                        <?= $escaparSeguridad(
                                            $estadoUsuario
                                        ); ?>
                                    </span>

                                </td>

                                <td>

                                    <div class="btn-group-actions">

                                        <!-- Editar usuario -->
                                        <button
                                            type="button"
                                            class="btn-action btn-edit btnEditarUsuario"
                                            title="Editar usuario"

                                            data-id-usuario="<?= $escaparSeguridad($idUsuario); ?>"
                                            data-id-persona="<?= $escaparSeguridad($idPersona); ?>"

                                            data-cedula="<?= $escaparSeguridad($cedula); ?>"
                                            data-nombre="<?= $escaparSeguridad($nombre); ?>"
                                            data-apellido="<?= $escaparSeguridad($apellido); ?>"
                                            data-telefono="<?= $escaparSeguridad($telefono); ?>"
                                            data-correo="<?= $escaparSeguridad($correo); ?>"

                                            data-id-rol="<?= $escaparSeguridad($idRol); ?>"

                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarUsuario"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                        </button>


                                        <!-- Restablecer contraseña -->
                                        <button
                                            type="button"
                                            class="btn-action btn-detail btnRestablecerClave"
                                            title="Restablecer contraseña"

                                            data-id-usuario="<?= $escaparSeguridad($idUsuario); ?>"
                                            data-nombre="<?= $escaparSeguridad($nombreCompleto); ?>"

                                            data-bs-toggle="modal"
                                            data-bs-target="#modalRestablecerClave"
                                        >
                                            <i class="bi bi-key"></i>
                                        </button>


                                        <!-- Activar o desactivar -->
                                        <button
                                            type="button"
                                            class="btn-action <?= $usuarioActivo
                                                ? 'btn-deactivate'
                                                : 'btn-detail'; ?> btnCambiarEstadoUsuario"

                                            title="<?= $usuarioActivo
                                                ? 'Desactivar usuario'
                                                : 'Activar usuario'; ?>"

                                            data-id-usuario="<?= $escaparSeguridad($idUsuario); ?>"
                                            data-nombre="<?= $escaparSeguridad($nombreCompleto); ?>"
                                            data-estado="<?= $escaparSeguridad($estadoUsuario); ?>"

                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEstadoUsuario"
                                        >

                                            <i class="bi <?= $usuarioActivo
                                                ? 'bi-person-dash'
                                                : 'bi-person-check'; ?>">
                                            </i>

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

</section>


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

                        <div class="col-md-4">

                            <label
                                for="nuevoUsuarioCedula"
                                class="form-label"
                            >
                                Cédula
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
                                Este correo se utilizará para recuperar la contraseña.
                            </small>

                        </div>


                        <div class="col-md-12">

                            <label
                                for="nuevoUsuarioRol"
                                class="form-label"
                            >
                                Rol
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
                            </label>

                            <input
                                type="password"
                                id="nuevoUsuarioClave"
                                name="clave"
                                class="form-control templo-input"
                                placeholder="Ingrese la contraseña"
                                maxlength="255"
                                autocomplete="new-password"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label
                                for="nuevoUsuarioConfirmarClave"
                                class="form-label"
                            >
                                Confirmar contraseña
                            </label>

                            <input
                                type="password"
                                id="nuevoUsuarioConfirmarClave"
                                name="confirmar_clave"
                                class="form-control templo-input"
                                placeholder="Repita la contraseña"
                                maxlength="255"
                                autocomplete="new-password"
                                required
                            >

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
                                La cédula se utiliza para iniciar sesión.
                            </small>

                        </div>


                        <div class="col-md-4">

                            <label
                                for="editarUsuarioNombre"
                                class="form-label"
                            >
                                Nombre
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


                        <div class="col-md-12">

                            <label
                                for="editarUsuarioRol"
                                class="form-label"
                            >
                                Rol
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
                    name="id_usuario"
                    id="restablecerClaveIdUsuario"
                >


                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="modalRestablecerClaveLabel"
                    >
                        <i class="bi bi-key"></i>
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
                            Nueva contraseña
                        </label>

                        <input
                            type="password"
                            id="restablecerNuevaClave"
                            name="nueva_clave"
                            class="form-control templo-input"
                            placeholder="Ingrese la nueva contraseña"
                            maxlength="255"
                            autocomplete="new-password"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label
                            for="restablecerConfirmarClave"
                            class="form-label"
                        >
                            Confirmar nueva contraseña
                        </label>

                        <input
                            type="password"
                            id="restablecerConfirmarClave"
                            name="confirmar_clave"
                            class="form-control templo-input"
                            placeholder="Repita la nueva contraseña"
                            maxlength="255"
                            autocomplete="new-password"
                            required
                        >

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="submit"
                        class="btn-templo-primary"
                    >
                        <i class="bi bi-check-circle"></i>
                        Restablecer
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
<!-- MODAL ACTIVAR O DESACTIVAR USUARIO -->
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
                >


                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="modalEstadoUsuarioLabel"
                    >
                        <i
                            class="bi bi-person-dash"
                            id="iconoEstadoUsuario"
                        ></i>

                        <span id="tituloEstadoUsuario">
                            Cambiar estado del usuario
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
                        ¿Deseas cambiar el estado de este usuario?
                    </p>

                    <p
                        class="status-client-name"
                        id="nombreEstadoUsuario"
                    ></p>

                    <p
                        class="status-warning"
                        id="advertenciaEstadoUsuario"
                    >
                        El usuario permanecerá registrado en el sistema.
                    </p>

                </div>


                <div class="modal-footer">

                    <button
                        type="submit"
                        class="btn-templo-danger"
                        id="btnConfirmarEstadoUsuario"
                    >
                        <i class="bi bi-check-circle"></i>
                        Confirmar
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


<?php

$scriptVista = 'Assets/js/seguridad.js';

require_once __DIR__ . '/Layout/Footer.php';

?>