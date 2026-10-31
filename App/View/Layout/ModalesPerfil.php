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


$nicknamePerfil =
    $datosPerfil['nickname']
    ?? '';

$telefonoPerfil =
    $datosPerfil['telefono']
    ?? '';

$correoPerfil =
    $datosPerfil['correo']
    ?? '';

$fotoPerfil =
    $datosPerfil['foto_perfil']
    ?? '';


if (trim((string) $fotoPerfil) === '') {

    $fotoPerfil =
        'Assets/img/LogoTR.png';
}


/*
 * Escapar los datos que se muestran
 * dentro de las interfaces del perfil.
 */
$escaparPerfil =
    static function (
        $valor
    ): string {

        return htmlspecialchars(
            (string) $valor,
            ENT_QUOTES,
            'UTF-8'
        );
    };


/*
 * Ojos personalizados utilizados en el acceso.
 * Se imprimen directamente en la vista para que
 * siempre sean visibles, incluso antes de ejecutar JS.
 */
$iconosVisibilidadPerfil = <<<'HTML'
<svg class="templo-eye-svg templo-eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
    <path d="M2.68936 6.70456C2.52619 6.32384 2.08528 6.14747 1.70456 6.31064C1.32384 6.47381 1.14747 6.91472 1.31064 7.29544L2.68936 6.70456ZM15.5872 13.3287L15.3125 12.6308L15.5872 13.3287ZM9.04145 13.7377C9.26736 13.3906 9.16904 12.926 8.82185 12.7001C8.47466 12.4742 8.01008 12.5725 7.78417 12.9197L9.04145 13.7377ZM6.37136 15.091C6.14545 15.4381 6.24377 15.9027 6.59096 16.1286C6.93815 16.3545 7.40273 16.2562 7.62864 15.909L6.37136 15.091ZM22.6894 7.29544C22.8525 6.91472 22.6762 6.47381 22.2954 6.31064C21.9147 6.14747 21.4738 6.32384 21.3106 6.70456L22.6894 7.29544ZM19 11.1288L18.4867 10.582V10.582L19 11.1288ZM19.9697 13.1592C20.2626 13.4521 20.7374 13.4521 21.0303 13.1592C21.3232 12.8663 21.3232 12.3914 21.0303 12.0985L19.9697 13.1592ZM11.25 16.5C11.25 16.9142 11.5858 17.25 12 17.25C12.4142 17.25 12.75 16.9142 12.75 16.5H11.25ZM16.3714 15.909C16.5973 16.2562 17.0619 16.3545 17.409 16.1286C17.7562 15.9027 17.8545 15.4381 17.6286 15.091L16.3714 15.909ZM5.53033 11.6592C5.82322 11.3663 5.82322 10.8914 5.53033 10.5985C5.23744 10.3056 4.76256 10.3056 4.46967 10.5985L5.53033 11.6592ZM2.96967 12.0985C2.67678 12.3914 2.67678 12.8663 2.96967 13.1592C3.26256 13.4521 3.73744 13.4521 4.03033 13.1592L2.96967 12.0985ZM12 13.25C8.77611 13.25 6.46133 11.6446 4.9246 9.98966C4.15645 9.16243 3.59325 8.33284 3.22259 7.71014C3.03769 7.3995 2.90187 7.14232 2.8134 6.96537C2.76919 6.87696 2.73689 6.80875 2.71627 6.76411C2.70597 6.7418 2.69859 6.7254 2.69411 6.71533C2.69187 6.7103 2.69036 6.70684 2.68957 6.70503C2.68917 6.70413 2.68896 6.70363 2.68892 6.70355C2.68891 6.70351 2.68893 6.70357 2.68901 6.70374C2.68904 6.70382 2.68913 6.70403 2.68915 6.70407C2.68925 6.7043 2.68936 6.70456 2 7C1.31064 7.29544 1.31077 7.29575 1.31092 7.29609C1.31098 7.29624 1.31114 7.2966 1.31127 7.2969C1.31152 7.29749 1.31183 7.2982 1.31218 7.299C1.31287 7.30062 1.31376 7.30266 1.31483 7.30512C1.31698 7.31003 1.31988 7.31662 1.32353 7.32483C1.33083 7.34125 1.34115 7.36415 1.35453 7.39311C1.38127 7.45102 1.42026 7.5332 1.47176 7.63619C1.57469 7.84206 1.72794 8.13175 1.93366 8.47736C2.34425 9.16716 2.96855 10.0876 3.8254 11.0103C5.53867 12.8554 8.22389 14.75 12 14.75V13.25ZM15.3125 12.6308C14.3421 13.0128 13.2417 13.25 12 13.25V14.75C13.4382 14.75 14.7246 14.4742 15.8619 14.0266L15.3125 12.6308ZM7.78417 12.9197L6.37136 15.091L7.62864 15.909L9.04145 13.7377L7.78417 12.9197ZM22 7C21.3106 6.70456 21.3107 6.70441 21.3108 6.70427C21.3108 6.70423 21.3108 6.7041 21.3109 6.70402C21.3109 6.70388 21.311 6.70376 21.311 6.70368C21.3111 6.70352 21.3111 6.70349 21.3111 6.7036C21.311 6.7038 21.3107 6.70452 21.3101 6.70576C21.309 6.70823 21.307 6.71275 21.3041 6.71924C21.2983 6.73223 21.2889 6.75309 21.2758 6.78125C21.2495 6.83757 21.2086 6.92295 21.1526 7.03267C21.0406 7.25227 20.869 7.56831 20.6354 7.9432C20.1669 8.69516 19.4563 9.67197 18.4867 10.582L19.5133 11.6757C20.6023 10.6535 21.3917 9.56587 21.9085 8.73646C22.1676 8.32068 22.36 7.9668 22.4889 7.71415C22.5533 7.58775 22.602 7.48643 22.6353 7.41507C22.6519 7.37939 22.6647 7.35118 22.6737 7.33104C22.6782 7.32097 22.6818 7.31292 22.6844 7.30696C22.6857 7.30398 22.6867 7.30153 22.6876 7.2996C22.688 7.29864 22.6883 7.29781 22.6886 7.29712C22.6888 7.29677 22.6889 7.29646 22.689 7.29618C22.6891 7.29604 22.6892 7.29585 22.6892 7.29578C22.6893 7.29561 22.6894 7.29544 22 7ZM18.4867 10.582C17.6277 11.3882 16.5739 12.1343 15.3125 12.6308L15.8619 14.0266C17.3355 13.4466 18.5466 12.583 19.5133 11.6757L18.4867 10.582ZM18.4697 11.6592L19.9697 13.1592L21.0303 12.0985L19.5303 10.5985L18.4697 11.6592ZM11.25 14V16.5H12.75V14H11.25ZM14.9586 13.7377L16.3714 15.909L17.6286 15.091L16.2158 12.9197L14.9586 13.7377ZM4.46967 10.5985L2.96967 12.0985L4.03033 13.1592L5.53033 11.6592L4.46967 10.5985Z" fill="currentColor"></path>
</svg>
<svg class="templo-eye-svg templo-eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
    <path d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
    <path d="M12 16C10 14.8 8.5 13.5 8.5 11.8C8.5 10.5 9.5 9.5 10.8 9.5C11.5 9.5 12 9.9 12 10.4C12 9.9 12.5 9.5 13.2 9.5C14.5 9.5 15.5 10.5 15.5 11.8C15.5 13.5 14 14.8 12 16Z" fill="currentColor"></path>
</svg>
HTML;


/*
 * Crear un token de seguridad para el
 * formulario de cambio de contraseña.
 */
if (
    empty(
        $_SESSION[
            'token_cambiar_clave'
        ]
    )
) {
    $_SESSION[
        'token_cambiar_clave'
    ] =
        bin2hex(
            random_bytes(32)
        );
}


$tokenCambiarClave =
    $_SESSION[
        'token_cambiar_clave'
    ];


/*
 * Mensajes correspondientes al resultado
 * devuelto por el controlador del perfil.
 */
$resultadoCambiarClave =
    (string) (
        $_GET['clave']
        ?? ''
    );


$mensajesCambiarClave = [

    'actualizada' => [
        'tipo' => 'success',
        'texto' =>
            'La contraseña fue actualizada correctamente.'
    ],

    'actual_incorrecta' => [
        'tipo' => 'danger',
        'texto' =>
            'La contraseña actual suministrada no es correcta.'
    ],

    'clave_corta' => [
        'tipo' => 'warning',
        'texto' =>
            'La nueva contraseña debe contener un mínimo de ocho caracteres.'
    ],

    'no_coinciden' => [
        'tipo' => 'warning',
        'texto' =>
            'La nueva contraseña y su confirmación no coinciden.'
    ],

    'misma_clave' => [
        'tipo' => 'warning',
        'texto' =>
            'La nueva contraseña debe ser diferente de la contraseña actual.'
    ],

    'datos_incompletos' => [
        'tipo' => 'warning',
        'texto' =>
            'Debe completar todos los campos solicitados.'
    ],

    'usuario_no_disponible' => [
        'tipo' => 'danger',
        'texto' =>
            'No fue posible identificar la cuenta del usuario autenticado.'
    ],

    'solicitud_invalida' => [
        'tipo' => 'danger',
        'texto' =>
            'La solicitud de cambio de contraseña no es válida.'
    ],

    'error' => [
        'tipo' => 'danger',
        'texto' =>
            'No fue posible actualizar la contraseña. Intente nuevamente.'
    ]
];


$mensajeCambiarClave =
    $mensajesCambiarClave[
        $resultadoCambiarClave
    ] ?? null;

?>


<?php if ($mensajeCambiarClave): ?>

    <!-- Resultado del cambio de contraseña -->
    <div
        class="modal fade"
        id="modalResultadoCambioClave"
        tabindex="-1"
        aria-labelledby="tituloResultadoCambioClave"
        aria-describedby="textoResultadoCambioClave"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content templo-modal seguridad-resultado-modal">

                <div class="modal-header">
                    <h5 class="modal-title" id="tituloResultadoCambioClave">
                        <?= $resultadoCambiarClave === 'actualizada'
                            ? 'Contraseña actualizada'
                            : 'Resultado del cambio de contraseña'; ?>
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
                                d="M32 6 C27 8, 19 10, 13 12 C11.5 23, 14 42, 32 56 C50 42, 52.5 23, 51 12 C45 10, 37 8, 32 6Z"
                                class="quick-svg-fill-soft"
                            ></path>
                            <path
                                d="M32 6 C27 8, 19 10, 13 12 C11.5 23, 14 42, 32 56Z"
                                class="quick-svg-fill-soft-strong"
                            ></path>
                            <path
                                d="M32 6 C27 8, 19 10, 13 12 C11.5 23, 14 42, 32 56 C50 42, 52.5 23, 51 12 C45 10, 37 8, 32 6Z"
                                class="quick-svg-stroke"
                                fill="none"
                                stroke-width="3"
                                stroke-linejoin="round"
                            ></path>
                            <path
                                d="M32 20 C30.7 18.1 27.9 18 26.6 19.8 C25.2 21.8 26.4 24.2 32 28.5 C37.6 24.2 38.8 21.8 37.4 19.8 C36.1 18 33.3 18.1 32 20Z"
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
                        id="textoResultadoCambioClave"
                        role="<?= $resultadoCambiarClave === 'actualizada'
                            ? 'status'
                            : 'alert'; ?>"
                    >
                        <?= $escaparPerfil(
                            $mensajeCambiarClave['texto']
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
                            src="<?= $escaparPerfil(
                                $fotoPerfil
                            ); ?>"
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
                            value="<?= $escaparPerfil(
                                $nicknamePerfil
                            ); ?>"
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
                            value="<?= $escaparPerfil(
                                $telefonoPerfil
                            ); ?>"
                            placeholder="Ej: 04141234567"
                            maxlength="20"
                            autocomplete="tel"
                        >

                    </div>


                    <!-- Correo electrónico -->
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
                            value="<?= $escaparPerfil(
                                $correoPerfil
                            ); ?>"
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


<?php if ($mensajeCambiarClave): ?>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const modalElemento =
                    document.getElementById(
                        'modalResultadoCambioClave'
                    );

                if (
                    modalElemento &&
                    window.bootstrap &&
                    window.bootstrap.Modal
                ) {
                    const modalResultado =
                        new bootstrap.Modal(
                            modalElemento
                        );

                    modalResultado.show();
                }
            }
        );
    </script>

<?php endif; ?>


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
                action="Index.php?url=perfil"
                method="POST"
                autocomplete="off"
            >

                <input
                    type="hidden"
                    name="accion"
                    value="cambiar_clave"
                >

                <input
                    type="hidden"
                    name="token_csrf"
                    value="<?= $escaparPerfil(
                        $tokenCambiarClave
                    ); ?>"
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

                        <div class="templo-password-field">

                            <input
                                type="password"
                                id="clave_actual"
                                name="clave_actual"
                                class="form-control templo-input"
                                placeholder="Ingrese su contraseña actual"
                                maxlength="255"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                type="button"
                                class="templo-password-toggle btnAlternarClavePerfil"
                                data-password-target="clave_actual"
                                aria-label="Mostrar contraseña actual"
                                title="Mostrar contraseña actual"
                            >
                                <?= $iconosVisibilidadPerfil; ?>
                            </button>

                        </div>

                    </div>


                    <!-- Nueva contraseña -->
                    <div class="mb-3">

                        <label
                            for="nueva_clave"
                            class="form-label"
                        >
                            Nueva contraseña
                        </label>

                        <div class="templo-password-field">

                            <input
                                type="password"
                                id="nueva_clave"
                                name="nueva_clave"
                                class="form-control templo-input"
                                placeholder="Ingrese la nueva contraseña"
                                minlength="8"
                                maxlength="255"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="templo-password-toggle btnAlternarClavePerfil"
                                data-password-target="nueva_clave"
                                aria-label="Mostrar nueva contraseña"
                                title="Mostrar nueva contraseña"
                            >
                                <?= $iconosVisibilidadPerfil; ?>
                            </button>

                        </div>

                    </div>


                    <!-- Confirmar nueva contraseña -->
                    <div class="mb-3">

                        <label
                            for="confirmar_clave"
                            class="form-label"
                        >
                            Confirmar nueva contraseña
                        </label>

                        <div class="templo-password-field">

                            <input
                                type="password"
                                id="confirmar_clave"
                                name="confirmar_clave"
                                class="form-control templo-input"
                                placeholder="Repita la nueva contraseña"
                                minlength="8"
                                maxlength="255"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="templo-password-toggle btnAlternarClavePerfil"
                                data-password-target="confirmar_clave"
                                aria-label="Mostrar confirmación de contraseña"
                                title="Mostrar confirmación de contraseña"
                            >
                                <?= $iconosVisibilidadPerfil; ?>
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