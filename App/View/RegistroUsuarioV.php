<!-- ===================================================== -->
<!-- DATOS DE LA VISTA -->
<!-- ===================================================== -->

<?php

$tituloPagina = 'Registro de Usuario | Templo Rosa';

$listaRoles = (isset($listaRoles) && is_array($listaRoles)) ? $listaRoles : [];

$mensajeRegistro = isset($mensajeRegistro) ? (string) $mensajeRegistro : '';

$datosRegistro = (isset($datosRegistro) && is_array($datosRegistro)) ? $datosRegistro : [];

$escaparRegistro = static function ($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};

?>


<!-- ===================================================== -->
<!-- ICONOS DE VISIBILIDAD DE CONTRASEÑA -->
<!-- ===================================================== -->

<!-- Iconos impresos por ambos botones de contraseña -->
<?php

$iconosVisibilidadRegistro = <<<'HTML'
<svg
    class="templo-eye-svg templo-eye-closed"
    viewBox="0 0 24 24"
    fill="none"
    aria-hidden="true"
    focusable="false"
>
    <path
        d="M2.68936 6.70456
           C2.52619 6.32384 2.08528 6.14747 1.70456 6.31064
           C1.32384 6.47381 1.14747 6.91472 1.31064 7.29544
           L2.68936 6.70456Z
           M15.5872 13.3287L15.3125 12.6308
           L15.5872 13.3287Z
           M9.04145 13.7377
           C9.26736 13.3906 9.16904 12.926 8.82185 12.7001
           C8.47466 12.4742 8.01008 12.5725 7.78417 12.9197
           L9.04145 13.7377Z
           M6.37136 15.091
           C6.14545 15.4381 6.24377 15.9027 6.59096 16.1286
           C6.93815 16.3545 7.40273 16.2562 7.62864 15.909
           L6.37136 15.091Z
           M22.6894 7.29544
           C22.8525 6.91472 22.6762 6.47381 22.2954 6.31064
           C21.9147 6.14747 21.4738 6.32384 21.3106 6.70456
           L22.6894 7.29544Z
           M19 11.1288L18.4867 10.582V10.582
           L19 11.1288Z
           M19.9697 13.1592
           C20.2626 13.4521 20.7374 13.4521 21.0303 13.1592
           C21.3232 12.8663 21.3232 12.3914 21.0303 12.0985
           L19.9697 13.1592Z
           M11.25 16.5
           C11.25 16.9142 11.5858 17.25 12 17.25
           C12.4142 17.25 12.75 16.9142 12.75 16.5
           H11.25Z
           M16.3714 15.909
           C16.5973 16.2562 17.0619 16.3545 17.409 16.1286
           C17.7562 15.9027 17.8545 15.4381 17.6286 15.091
           L16.3714 15.909Z
           M5.53033 11.6592
           C5.82322 11.3663 5.82322 10.8914 5.53033 10.5985
           C5.23744 10.3056 4.76256 10.3056 4.46967 10.5985
           L5.53033 11.6592Z
           M2.96967 12.0985
           C2.67678 12.3914 2.67678 12.8663 2.96967 13.1592
           C3.26256 13.4521 3.73744 13.4521 4.03033 13.1592
           L2.96967 12.0985Z
           M12 13.25
           C8.77611 13.25 6.46133 11.6446 4.9246 9.98966
           C4.15645 9.16243 3.59325 8.33284 3.22259 7.71014
           C3.03769 7.3995 2.90187 7.14232 2.8134 6.96537
           C2.76919 6.87696 2.73689 6.80875 2.71627 6.76411
           C2.70597 6.7418 2.69859 6.7254 2.69411 6.71533
           C2.69187 6.7103 2.69036 6.70684 2.68957 6.70503
           C2.68917 6.70413 2.68896 6.70363 2.68892 6.70355
           C2.68891 6.70351 2.68893 6.70357 2.68901 6.70374
           C2.68904 6.70382 2.68913 6.70403 2.68915 6.70407
           C2.68925 6.7043 2.68936 6.70456 2 7
           C1.31064 7.29544 1.31077 7.29575 1.31092 7.29609
           C1.31098 7.29624 1.31114 7.2966 1.31127 7.2969
           C1.31152 7.29749 1.31183 7.2982 1.31218 7.299
           C1.31287 7.30062 1.31376 7.30266 1.31483 7.30512
           C1.31698 7.31003 1.31988 7.31662 1.32353 7.32483
           C1.33083 7.34125 1.34115 7.36415 1.35453 7.39311
           C1.38127 7.45102 1.42026 7.5332 1.47176 7.63619
           C1.57469 7.84206 1.72794 8.13175 1.93366 8.47736
           C2.34425 9.16716 2.96855 10.0876 3.8254 11.0103
           C5.53867 12.8554 8.22389 14.75 12 14.75
           V13.25Z
           M15.3125 12.6308
           C14.3421 13.0128 13.2417 13.25 12 13.25
           V14.75
           C13.4382 14.75 14.7246 14.4742 15.8619 14.0266
           L15.3125 12.6308Z
           M7.78417 12.9197L6.37136 15.091
           L7.62864 15.909L9.04145 13.7377
           L7.78417 12.9197Z
           M22 7
           C21.3106 6.70456 21.3107 6.70441 21.3108 6.70427
           C21.3108 6.70423 21.3108 6.7041 21.3109 6.70402
           C21.3109 6.70388 21.311 6.70376 21.311 6.70368
           C21.3111 6.70352 21.3111 6.70349 21.3111 6.7036
           C21.311 6.7038 21.3107 6.70452 21.3101 6.70576
           C21.309 6.70823 21.307 6.71275 21.3041 6.71924
           C21.2983 6.73223 21.2889 6.75309 21.2758 6.78125
           C21.2495 6.83757 21.2086 6.92295 21.1526 7.03267
           C21.0406 7.25227 20.869 7.56831 20.6354 7.9432
           C20.1669 8.69516 19.4563 9.67197 18.4867 10.582
           L19.5133 11.6757
           C20.6023 10.6535 21.3917 9.56587 21.9085 8.73646
           C22.1676 8.32068 22.36 7.9668 22.4889 7.71415
           C22.5533 7.58775 22.602 7.48643 22.6353 7.41507
           C22.6519 7.37939 22.6647 7.35118 22.6737 7.33104
           C22.6782 7.32097 22.6818 7.31292 22.6844 7.30696
           C22.6857 7.30398 22.6867 7.30153 22.6876 7.2996
           C22.688 7.29864 22.6883 7.29781 22.6886 7.29712
           C22.6888 7.29677 22.6889 7.29646 22.689 7.29618
           C22.6891 7.29604 22.6892 7.29585 22.6892 7.29578
           C22.6893 7.29561 22.6894 7.29544 22 7Z
           M18.4867 10.582
           C17.6277 11.3882 16.5739 12.1343 15.3125 12.6308
           L15.8619 14.0266
           C17.3355 13.4466 18.5466 12.583 19.5133 11.6757
           L18.4867 10.582Z
           M18.4697 11.6592L19.9697 13.1592
           L21.0303 12.0985L19.5303 10.5985
           L18.4697 11.6592Z
           M11.25 14V16.5H12.75V14H11.25Z
           M14.9586 13.7377L16.3714 15.909
           L17.6286 15.091L16.2158 12.9197
           L14.9586 13.7377Z
           M4.46967 10.5985L2.96967 12.0985
           L4.03033 13.1592L5.53033 11.6592
           L4.46967 10.5985Z"
        fill="currentColor"
    ></path>
</svg>

<svg
    class="templo-eye-svg templo-eye-open"
    viewBox="0 0 24 24"
    fill="none"
    aria-hidden="true"
    focusable="false"
>
    <path
        d="M2 12
           C2 12 5.5 5 12 5
           C18.5 5 22 12 22 12
           C22 12 18.5 19 12 19
           C5.5 19 2 12 2 12Z"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        fill="none"
    ></path>

    <path
        d="M12 16
           C10 14.8 8.5 13.5 8.5 11.8
           C8.5 10.5 9.5 9.5 10.8 9.5
           C11.5 9.5 12 9.9 12 10.4
           C12 9.9 12.5 9.5 13.2 9.5
           C14.5 9.5 15.5 10.5 15.5 11.8
           C15.5 13.5 14 14.8 12 16Z"
        fill="currentColor"
    ></path>
</svg>
HTML;

?>


<!-- ===================================================== -->
<!-- CONFIGURACIÓN DEL DOCUMENTO -->
<!-- ===================================================== -->

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= $escaparRegistro($tituloPagina); ?></title>

    <link
        rel="icon"
        type="image/png"
        href="Assets/img/LogoTR.png"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- CSS principal de Templo Rosa -->
    <link
        rel="stylesheet"
        href="Assets/css/styles.css"
    >

</head>


<!-- ===================================================== -->
<!-- REGISTRO DE USUARIO -->
<!-- ===================================================== -->

<body class="auth-registro-page">

    <div class="bg-overlay"></div>

    <main class="auth-page">

        <!-- Logo de Templo Rosa -->
        <header class="auth-logo-wrap">

            <img
                src="Assets/img/LogoTR.png"
                alt="Logo Templo Rosa"
                class="brand-logo-img auth-logo"
            >

        </header>


        <!-- ===================================================== -->
        <!-- FORMULARIO DE REGISTRO -->
        <!-- ===================================================== -->

        <section class="auth-card">

            <h1 class="auth-title">
                <i class="bi bi-person-plus-fill" aria-hidden="true"></i>
                Registro de Usuario
            </h1>

            <!-- Aviso con el resultado del registro -->
            <?php if ($mensajeRegistro !== ''): ?>

                <p
                    role="alert"
                    style="
                        margin: 0 0 18px;
                        color: #8a2635;
                        font-size: 0.92rem;
                        text-align: center;
                    "
                >
                    <?= $escaparRegistro($mensajeRegistro); ?>
                </p>

            <?php endif; ?>

            <form
                class="auth-form"
                id="formRegistroUsuario"
                action="Index.php?url=registroUsuario"
                method="POST"
                autocomplete="off"
            >

                <!-- Acción enviada al controlador -->
                <input
                    type="hidden"
                    name="accion"
                    value="registrar_usuario"
                >

                <!-- Nombres -->
                <div class="auth-field">

                    <label for="nombres">
                        Nombres
                        <span class="required-mark" aria-hidden="true">*</span>
                    </label>

                    <input
                        type="text"
                        id="nombres"
                        name="nombres"
                        class="auth-input"
                        placeholder="Ingrese los nombres"
                        maxlength="60"
                        autocomplete="given-name"
                        value="<?= $escaparRegistro($datosRegistro['nombres'] ?? ''); ?>"
                        required
                    >

                </div>

                <!-- Apellidos -->
                <div class="auth-field">

                    <label for="apellidos">
                        Apellidos
                        <span class="required-mark" aria-hidden="true">*</span>
                    </label>

                    <input
                        type="text"
                        id="apellidos"
                        name="apellidos"
                        class="auth-input"
                        placeholder="Ingrese los apellidos"
                        maxlength="60"
                        autocomplete="family-name"
                        value="<?= $escaparRegistro($datosRegistro['apellidos'] ?? ''); ?>"
                        required
                    >

                </div>

                <!-- Cédula -->
                <div class="auth-field">

                    <label for="cedula">
                        Cédula
                        <span class="required-mark" aria-hidden="true">*</span>
                    </label>

                    <input
                        type="text"
                        id="cedula"
                        name="cedula"
                        class="auth-input"
                        placeholder="Ingrese la cédula"
                        maxlength="20"
                        inputmode="numeric"
                        pattern="[0-9]{6,20}"
                        autocomplete="off"
                        value="<?= $escaparRegistro($datosRegistro['cedula'] ?? ''); ?>"
                        required
                    >

                </div>

                <!-- Teléfono -->
                <div class="auth-field">

                    <label for="telefono">
                        Teléfono
                        <span>(Opcional)</span>
                    </label>

                    <input
                        type="tel"
                        id="telefono"
                        name="telefono"
                        class="auth-input"
                        placeholder="Ingrese el teléfono"
                        maxlength="20"
                        autocomplete="tel"
                        value="<?= $escaparRegistro($datosRegistro['telefono'] ?? ''); ?>"
                    >

                </div>

                <!-- Correo electrónico -->
                <div class="auth-field">

                    <label for="correo">
                        Correo electrónico
                        <span class="required-mark" aria-hidden="true">*</span>
                    </label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        class="auth-input"
                        placeholder="Ingrese el correo electrónico"
                        maxlength="100"
                        autocomplete="email"
                        value="<?= $escaparRegistro($datosRegistro['correo'] ?? ''); ?>"
                        required
                    >

                </div>

                <!-- Contraseña -->
                <div class="auth-field">

                    <label for="clave">
                        Contraseña
                        <span class="required-mark" aria-hidden="true">*</span>
                    </label>

                    <div class="templo-password-field">

                        <input
                            type="password"
                            id="clave"
                            name="clave"
                            class="auth-input templo-input"
                            placeholder="Ingrese la contraseña"
                            minlength="8"
                            maxlength="255"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="templo-password-toggle btnAlternarClaveRegistro"
                            data-password-target="clave"
                            aria-label="Mostrar contraseña"
                            title="Mostrar contraseña"
                        >
                            <?= $iconosVisibilidadRegistro; ?>
                        </button>

                    </div>

                </div>

                <!-- Confirmar contraseña -->
                <div class="auth-field">

                    <label for="confirmarClave">
                        Confirmar contraseña
                        <span class="required-mark" aria-hidden="true">*</span>
                    </label>

                    <div class="templo-password-field">

                        <input
                            type="password"
                            id="confirmarClave"
                            name="confirmar_clave"
                            class="auth-input templo-input"
                            placeholder="Repita la contraseña"
                            minlength="8"
                            maxlength="255"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="templo-password-toggle btnAlternarClaveRegistro"
                            data-password-target="confirmarClave"
                            aria-label="Mostrar confirmación de contraseña"
                            title="Mostrar confirmación de contraseña"
                        >
                            <?= $iconosVisibilidadRegistro; ?>
                        </button>

                    </div>

                </div>

                <!-- Rol -->
                <div class="auth-field">

                    <label for="rol">
                        Rol
                        <span class="required-mark" aria-hidden="true">*</span>
                    </label>

                    <select
                        id="rol"
                        name="id_rol"
                        class="auth-input"
                        required
                    >
                        <option value="">Seleccione un rol</option>

                        <?php foreach ($listaRoles as $rol): ?>

                            <option
                                value="<?= $escaparRegistro($rol['id_rol'] ?? ''); ?>"
                                <?= ((string) ($datosRegistro['id_rol'] ?? '') === (string) ($rol['id_rol'] ?? '')) ? 'selected' : ''; ?>
                            >
                                <?= $escaparRegistro(ucfirst(strtolower($rol['nombre_rol'] ?? ''))); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- Enviar el registro -->
                <button
                    type="submit"
                    class="auth-btn"
                >
                    Registrar Usuario
                </button>

                <!-- Regresar al inicio de sesión -->
                <p class="auth-link">
                    <a href="Index.php?url=login">Volver al inicio de sesión</a>
                </p>

            </form>

        </section>

    </main>


    <!-- ===================================================== -->
    <!-- SCRIPTS DE LA VISTA -->
    <!-- ===================================================== -->

    <!-- Dependencias de la vista -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Comportamiento del registro -->
    <script src="Assets/js/registro-usuario.js"></script>

</body>

</html>