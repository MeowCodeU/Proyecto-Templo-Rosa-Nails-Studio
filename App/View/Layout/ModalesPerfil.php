<?php

$datosPerfil = [];

if (
    isset($_SESSION['usuario']) &&
    is_array($_SESSION['usuario'])
) {
    $datosPerfil = $_SESSION['usuario'];
} elseif (
    isset($usuarioActual) &&
    is_array($usuarioActual)
) {
    $datosPerfil = $usuarioActual;
}

$nicknamePerfil = $datosPerfil['nickname'] ?? '';
$telefonoPerfil = $datosPerfil['telefono'] ?? '';
$correoPerfil = $datosPerfil['correo'] ?? '';
$fotoPerfil = $datosPerfil['foto_perfil'] ?? '';

if (trim((string) $fotoPerfil) === '') {
    $fotoPerfil = 'Assets/img/LogoTR.png';
}

$escaparPerfil = static function ($valor): string {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

?>


<!-- ===================================================== -->
<!-- MODAL EDITAR PERFIL -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalPerfil"
    tabindex="-1"
    aria-labelledby="modalPerfilLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content templo-modal">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="modalPerfilLabel"
                >
                    <i class="bi bi-person-circle"></i>
                    Editar Perfil
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>


            <form
                id="formPerfil"
                enctype="multipart/form-data"
                autocomplete="off"
            >

                <div class="modal-body">

                    <!-- Foto de perfil -->
                    <div class="text-center mb-4">

                        <img
                            id="previewFoto"
                            src="<?= $escaparPerfil($fotoPerfil); ?>"
                            alt="Foto de perfil"
                            class="profile-preview-photo"
                        >

                        <input
                            type="file"
                            id="inputFotoPerfil"
                            name="foto"
                            accept="image/jpeg, image/png, image/webp"
                            class="d-none"
                        >

                        <p class="profile-photo-help">
                            Haz clic sobre la imagen para cambiar la foto
                        </p>

                    </div>


                    <!-- Nickname -->
                    <div class="mb-3">

                        <label
                            for="nickname"
                            class="form-label"
                        >
                            Nickname
                            <span class="fw-normal">
                                (opcional)
                            </span>
                        </label>

                        <input
                            type="text"
                            id="nickname"
                            name="nickname"
                            class="form-control templo-input"
                            value="<?= $escaparPerfil($nicknamePerfil); ?>"
                            placeholder="Ej: Solecito"
                            maxlength="50"
                        >

                        <small class="templo-help-text">
                            Si no colocas un nickname, se mostrará tu nombre completo.
                        </small>

                    </div>


                    <!-- Teléfono -->
                    <div class="mb-3">

                        <label
                            for="telefono"
                            class="form-label"
                        >
                            Teléfono
                        </label>

                        <input
                            type="tel"
                            id="telefono"
                            name="telefono"
                            class="form-control templo-input"
                            value="<?= $escaparPerfil($telefonoPerfil); ?>"
                            placeholder="Ej: 04141234567"
                            maxlength="20"
                            autocomplete="tel"
                        >

                    </div>


                    <!-- Correo -->
                    <div class="mb-3">

                        <label
                            for="correo"
                            class="form-label"
                        >
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            id="correo"
                            name="correo"
                            class="form-control templo-input"
                            value="<?= $escaparPerfil($correoPerfil); ?>"
                            placeholder="Ej: usuario@email.com"
                            maxlength="100"
                            autocomplete="email"
                        >

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
                        type="button"
                        class="btn-templo-primary"
                        id="btnGuardarPerfil"
                    >
                        <i class="bi bi-save"></i>
                        Guardar cambios
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>


<!-- ===================================================== -->
<!-- MODAL CAMBIAR CONTRASEÑA -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalCambiarClave"
    tabindex="-1"
    aria-labelledby="modalCambiarClaveLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content templo-modal">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="modalCambiarClaveLabel"
                >
                    <i class="bi bi-key"></i>
                    Cambiar Contraseña
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>


            <form
                id="formCambiarClave"
                autocomplete="off"
            >

                <div class="modal-body">

                    <!-- Contraseña actual -->
                    <div class="mb-3">

                        <label
                            for="clave_actual"
                            class="form-label"
                        >
                            Contraseña actual
                        </label>

                        <input
                            type="password"
                            id="clave_actual"
                            name="clave_actual"
                            class="form-control templo-input"
                            placeholder="Ingrese su contraseña actual"
                            autocomplete="current-password"
                            required
                        >

                    </div>


                    <!-- Nueva contraseña -->
                    <div class="mb-3">

                        <label
                            for="nueva_clave"
                            class="form-label"
                        >
                            Nueva contraseña
                        </label>

                        <input
                            type="password"
                            id="nueva_clave"
                            name="nueva_clave"
                            class="form-control templo-input"
                            placeholder="Ingrese la nueva contraseña"
                            autocomplete="new-password"
                            required
                        >

                    </div>


                    <!-- Confirmar contraseña -->
                    <div class="mb-3">

                        <label
                            for="confirmar_clave"
                            class="form-label"
                        >
                            Confirmar nueva contraseña
                        </label>

                        <input
                            type="password"
                            id="confirmar_clave"
                            name="confirmar_clave"
                            class="form-control templo-input"
                            placeholder="Repita la nueva contraseña"
                            autocomplete="new-password"
                            required
                        >

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
                        type="button"
                        class="btn-templo-primary"
                        id="btnCambiarClave"
                    >
                        <i class="bi bi-check-circle"></i>
                        Cambiar contraseña
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>