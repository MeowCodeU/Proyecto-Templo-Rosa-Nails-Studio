<?php

$tituloPagina = 'Registro de Usuario | Templo Rosa';

$listaRoles = (
    isset($listaRoles) &&
    is_array($listaRoles)
)
    ? $listaRoles
    : [];

$escaparRegistro = static function ($valor): string {

    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );

};

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= $escaparRegistro($tituloPagina); ?>
    </title>

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


        <!-- Tarjeta de registro -->
        <section class="auth-card">

            <h1 class="auth-title">

                <i
                    class="bi bi-person-plus-fill"
                    aria-hidden="true"
                ></i>

                Registro de Usuario

            </h1>


            <form
                class="auth-form"
                id="formRegistroUsuario"
                action="Index.php?url=registroUsuario"
                method="POST"
                autocomplete="off"
            >

                <input
                    type="hidden"
                    name="accion"
                    value="registrar_usuario"
                >


                <!-- Nombres -->
                <div class="auth-field">

                    <label for="nombres">
                        Nombres
                    </label>

                    <input
                        type="text"
                        id="nombres"
                        name="nombres"
                        class="auth-input"
                        placeholder="Ingrese los nombres"
                        maxlength="60"
                        autocomplete="given-name"
                        required
                    >

                </div>


                <!-- Apellidos -->
                <div class="auth-field">

                    <label for="apellidos">
                        Apellidos
                    </label>

                    <input
                        type="text"
                        id="apellidos"
                        name="apellidos"
                        class="auth-input"
                        placeholder="Ingrese los apellidos"
                        maxlength="60"
                        autocomplete="family-name"
                        required
                    >

                </div>


                <!-- Cédula -->
                <div class="auth-field">

                    <label for="cedula">
                        Cédula
                    </label>

                    <input
                        type="text"
                        id="cedula"
                        name="cedula"
                        class="auth-input"
                        placeholder="Ingrese la cédula"
                        maxlength="20"
                        inputmode="numeric"
                        autocomplete="off"
                        required
                    >

                </div>


                <!-- Teléfono -->
                <div class="auth-field">

                    <label for="telefono">

                        Teléfono

                        <span>
                            (Opcional)
                        </span>

                    </label>

                    <input
                        type="tel"
                        id="telefono"
                        name="telefono"
                        class="auth-input"
                        placeholder="Ingrese el teléfono"
                        maxlength="20"
                        autocomplete="tel"
                    >

                </div>


                <!-- Correo electrónico -->
                <div class="auth-field">

                    <label for="correo">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        class="auth-input"
                        placeholder="Ingrese el correo electrónico"
                        maxlength="100"
                        autocomplete="email"
                        required
                    >

                </div>


                <!-- Contraseña -->
                <div class="auth-field">

                    <label for="clave">
                        Contraseña
                    </label>

                    <input
                        type="password"
                        id="clave"
                        name="clave"
                        class="auth-input"
                        placeholder="Ingrese la contraseña"
                        maxlength="255"
                        autocomplete="new-password"
                        required
                    >

                </div>


                <!-- Rol -->
                <div class="auth-field">

                    <label for="rol">
                        Rol
                    </label>

                    <select
                        id="rol"
                        name="id_rol"
                        class="auth-input"
                        required
                    >

                        <option value="">
                            Seleccione un rol
                        </option>

                        <?php foreach ($listaRoles as $rol): ?>

                            <option
                                value="<?= $escaparRegistro(
                                    $rol['id_rol'] ?? ''
                                ); ?>"
                            >
                                <?= $escaparRegistro(
                                    ucfirst(
                                        strtolower(
                                            $rol['nombre_rol'] ?? ''
                                        )
                                    )
                                ); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <button
                    type="submit"
                    class="auth-btn"
                >
                    Registrar Usuario
                </button>


                <p class="auth-link">

                    <a href="Index.php?url=login">
                        Volver al inicio de sesión
                    </a>

                </p>

            </form>

        </section>

    </main>

</body>

</html>