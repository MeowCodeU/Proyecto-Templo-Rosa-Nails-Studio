<!-- ===================================================== -->
<!-- DATOS DE LA VISTA -->
<!-- ===================================================== -->

<?php

$tituloPagina = 'Clave Universal | Templo Rosa';

$escaparClaveAdmin = static function ($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};

?>

<!DOCTYPE html>
<html lang="es">

<!-- ===================================================== -->
<!-- CONFIGURACIÓN DEL DOCUMENTO -->
<!-- ===================================================== -->

<head>

    <!-- Codificación y adaptación a dispositivos -->
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Título de la página -->
    <title><?= $escaparClaveAdmin($tituloPagina); ?></title>

    <!-- Ícono de la página -->
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

    <!-- CSS de las vistas externas -->
    <link
        rel="stylesheet"
        href="Assets/css/login.css?v=<?= filemtime(__DIR__ . '/../../Assets/css/login.css'); ?>"
    >

</head>


<body>

    <!-- ===================================================== -->
    <!-- ACCESO CON CLAVE UNIVERSAL -->
    <!-- ===================================================== -->

    <main class="login-page">

        <section class="login-scene">

            <!-- Frasco -->
            <section class="login-bottle">

                <!-- Tapa -->
                <div class="bottle-cap">

                    <span class="cap-gloss"></span>

                </div>


                <!-- Cuello -->
                <div class="bottle-neck"></div>


                <!-- Cuerpo -->
                <div class="bottle-body">

                    <!-- Capas que forman el cuerpo del frasco -->
                    <svg
                        class="bottle-svg"
                        viewBox="0 0 380 470"
                        preserveAspectRatio="none"
                        aria-hidden="true"
                    >

                        <!-- Vidrio externo -->
                        <path
                            class="glass-shadow"
                            d="M98 34C76 38, 56 50, 46 78
                               C36 108, 34 180, 38 270L44 356
                               C48 410, 82 440, 124 446L256 446
                               C298 440, 332 410, 336 356L342 270
                               C346 180, 344 108, 334 78
                               C324 50, 304 38, 282 34
                               C250 30, 130 30, 98 34Z"
                        ></path>


                        <!-- Pintura interna -->
                        <path
                            class="pink-fill"
                            d="M92 54C70 58, 58 68, 52 90
                               C46 114, 46 178, 50 258L56 336
                               C60 382, 88 405, 128 410L252 410
                               C292 405, 320 382, 324 336L330 258
                               C334 178, 334 114, 328 90
                               C322 68, 310 58, 288 54
                               C258 50, 122 50, 92 54Z"
                        ></path>


                        <!-- Zona interior de vidrio -->
                        <path
                            class="inner-glass-zone"
                            d="M112 76C90 80, 80 92, 76 112
                               C72 134, 72 188, 76 252L80 328
                               C84 364, 106 383, 138 387L242 387
                               C274 383, 296 364, 300 328L304 252
                               C308 188, 308 134, 304 112
                               C300 92, 290 80, 268 76
                               C242 73, 138 73, 112 76Z"
                        ></path>


                        <!-- Borde externo -->
                        <path
                            class="glass-border"
                            d="M98 34C76 38, 56 50, 46 78
                               C36 108, 34 180, 38 270L44 356
                               C48 410, 82 440, 124 446L256 446
                               C298 440, 332 410, 336 356L342 270
                               C346 180, 344 108, 334 78
                               C324 50, 304 38, 282 34
                               C250 30, 130 30, 98 34Z"
                        ></path>


                        <!-- Borde interno blanco -->
                        <path
                            class="inner-border"
                            d="M112 76C90 80, 80 92, 76 112
                               C72 134, 72 188, 76 252L80 328
                               C84 364, 106 383, 138 387L242 387
                               C274 383, 296 364, 300 328L304 252
                               C308 188, 308 134, 304 112
                               C300 92, 290 80, 268 76
                               C242 73, 138 73, 112 76Z"
                        ></path>


                        <!-- Base -->
                        <path
                            class="bottle-base"
                            d="M128 418C156 426, 224 426, 252 418"
                        ></path>

                    </svg>


                    <!-- Contenido del acceso -->
                    <div class="login-content">

                        <!-- Logo -->
                        <div class="logo-back">

                            <img
                                src="Assets/img/LogoTR.png"
                                alt="Logo Templo Rosa"
                                class="login-logo"
                            >

                        </div>


                        <!-- Título y espacio reservado para los avisos del acceso -->
                        <div class="login-access-header">

                            <h1 class="login-title">Clave Universal</h1>

                            <p
                                class="login-message"
                                role="alert"
                                <?= $mensajeClaveAdmin === '' ? 'hidden' : ''; ?>
                            >
                                <?= $escaparClaveAdmin($mensajeClaveAdmin); ?>
                            </p>

                        </div>


                        <!-- Indicaciones para utilizar la clave universal -->
                        <p class="clave-universal-info">

                            <i
                                class="bi bi-info-circle"
                                aria-hidden="true"
                            ></i>

                            <span>
                                Ingrese la clave universal para acceder al registro de usuarios.
                            </span>

                        </p>


                        <!-- ===================================================== -->
                        <!-- FORMULARIO DE CLAVE UNIVERSAL -->
                        <!-- ===================================================== -->

                        <form
                            class="login-form"
                            id="formClaveAdmin"
                            action="Index.php?url=claveAdmin"
                            method="POST"
                            autocomplete="off"
                        >

                            <!-- Acción enviada al controlador -->
                            <input
                                type="hidden"
                                name="accion"
                                value="validar_clave_universal"
                            >


                            <!-- Clave universal -->
                            <div class="input-wrap">

                                <i
                                    class="bi bi-key"
                                    aria-hidden="true"
                                ></i>

                                <input
                                    type="password"
                                    id="claveUniversal"
                                    name="clave_universal"
                                    placeholder="Clave Universal"
                                    aria-label="Clave Universal"
                                    maxlength="100"
                                    autocomplete="off"
                                    required
                                >

                            </div>


                            <!-- Validar la clave universal -->
                            <button
                                type="submit"
                                class="login-btn"
                            >
                                Acceder
                            </button>


                            <!-- Regresar al formulario de inicio de sesión -->
                            <div class="login-links">

                                <a href="Index.php?url=login">
                                    Volver al inicio de sesión
                                </a>

                            </div>

                        </form>

                    </div>

                </div>

            </section>

        </section>

    </main>


    <!-- ===================================================== -->
    <!-- SCRIPTS DE LA VISTA -->
    <!-- ===================================================== -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Comportamiento del acceso -->
    <script src="Assets/js/login.js?v=<?= filemtime(__DIR__ . '/../../Assets/js/login.js'); ?>"></script>

</body>

</html>