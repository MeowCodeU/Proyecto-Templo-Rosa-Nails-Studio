<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Iniciar Sesión | Templo Rosa</title>

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


    <!-- CSS propio del Login -->
    <link
        rel="stylesheet"
        href="Assets/css/login.css"
    >

</head>


<body>

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


                    <svg
                        class="bottle-svg"
                        viewBox="0 0 380 470"
                        preserveAspectRatio="none"
                        aria-hidden="true"
                    >

                        <!-- Vidrio externo -->
                        <path
                            class="glass-shadow"
                            d="
                                M98 34
                                C76 38, 56 50, 46 78
                                C36 108, 34 180, 38 270
                                L44 356
                                C48 410, 82 440, 124 446
                                L256 446
                                C298 440, 332 410, 336 356
                                L342 270
                                C346 180, 344 108, 334 78
                                C324 50, 304 38, 282 34
                                C250 30, 130 30, 98 34
                                Z
                            "
                        ></path>


                        <!-- Pintura interna -->
                        <path
                            class="pink-fill"
                            d="
                                M92 54
                                C70 58, 58 68, 52 90
                                C46 114, 46 178, 50 258
                                L56 336
                                C60 382, 88 405, 128 410
                                L252 410
                                C292 405, 320 382, 324 336
                                L330 258
                                C334 178, 334 114, 328 90
                                C322 68, 310 58, 288 54
                                C258 50, 122 50, 92 54
                                Z
                            "
                        ></path>


                        <!-- Zona de vidrio interior -->
                        <path
                            class="inner-glass-zone"
                            d="
                                M112 76
                                C90 80, 80 92, 76 112
                                C72 134, 72 188, 76 252
                                L80 328
                                C84 364, 106 383, 138 387
                                L242 387
                                C274 383, 296 364, 300 328
                                L304 252
                                C308 188, 308 134, 304 112
                                C300 92, 290 80, 268 76
                                C242 73, 138 73, 112 76
                                Z
                            "
                        ></path>


                        <!-- Borde externo -->
                        <path
                            class="glass-border"
                            d="
                                M98 34
                                C76 38, 56 50, 46 78
                                C36 108, 34 180, 38 270
                                L44 356
                                C48 410, 82 440, 124 446
                                L256 446
                                C298 440, 332 410, 336 356
                                L342 270
                                C346 180, 344 108, 334 78
                                C324 50, 304 38, 282 34
                                C250 30, 130 30, 98 34
                                Z
                            "
                        ></path>


                        <!-- Borde interno blanco -->
                        <path
                            class="inner-border"
                            d="
                                M112 76
                                C90 80, 80 92, 76 112
                                C72 134, 72 188, 76 252
                                L80 328
                                C84 364, 106 383, 138 387
                                L242 387
                                C274 383, 296 364, 300 328
                                L304 252
                                C308 188, 308 134, 304 112
                                C300 92, 290 80, 268 76
                                C242 73, 138 73, 112 76
                                Z
                            "
                        ></path>


                        <!-- Base -->
                        <path
                            class="bottle-base"
                            d="
                                M128 418
                                C156 426, 224 426, 252 418
                            "
                        ></path>

                    </svg>


                    <div class="login-content">


                        <!-- Logo -->
                        <div class="logo-back">

                            <img
                                src="Assets/img/LogoTR.png"
                                alt="Logo Templo Rosa"
                                class="login-logo"
                            >

                        </div>


                        <h1 class="login-title">
                            Iniciar Sesión
                        </h1>


                        <form
                            class="login-form"
                            id="formLogin"
                            action="Index.php?url=login"
                            method="POST"
                            autocomplete="off"
                        >

                            <input
                                type="hidden"
                                name="accion"
                                value="iniciar_sesion"
                            >


                            <!-- Usuario -->
                            <div class="input-wrap">

                                <i
                                    class="bi bi-person-heart"
                                    aria-hidden="true"
                                ></i>

                                <input
                                    type="text"
                                    id="usuario"
                                    name="usuario"
                                    placeholder="Usuario"
                                    aria-label="Usuario"
                                    maxlength="20"
                                    autocomplete="username"
                                    required
                                >

                            </div>


                            <!-- Contraseña -->
                            <div class="input-wrap">

                                <i
                                    class="bi bi-bag-heart"
                                    aria-hidden="true"
                                ></i>

                                <input
                                    type="password"
                                    id="clave"
                                    name="clave"
                                    placeholder="Contraseña"
                                    aria-label="Contraseña"
                                    autocomplete="current-password"
                                    required
                                >

                                <button
                                    type="button"
                                    class="input-eye"
                                    id="btnMostrarClave"
                                    aria-label="Mostrar contraseña"
                                    title="Mostrar contraseña"
                                >
                                    <i
                                        class="bi bi-eye-slash"
                                        aria-hidden="true"
                                    ></i>
                                </button>

                            </div>


                            <button
                                type="submit"
                                class="login-btn"
                            >
                                Ingresar
                            </button>


                            <div class="login-links">
                                
                                <a href="Index.php?url=claveAdmin">
                                    Crear una cuenta
                                </a>

                                <a href="Index.php?url=recuperar">
                                    ¿Olvidaste tu contraseña?
                                </a>

                            </div>

                        </form>

                    </div>

                </div>

            </section>

        </section>

    </main>


    <script src="Assets/js/login.js"></script>

</body>

</html>