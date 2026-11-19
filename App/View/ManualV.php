<!DOCTYPE html>
<html lang="es">

<head>

    <!-- ================================================= -->
    <!-- CONFIGURACIÓN DE LA PÁGINA -->
    <!-- ================================================= -->

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manual de usuario | Templo Rosa</title>

    <link
        rel="icon"
        type="image/png"
        href="Assets/img/LogoTR.png"
    >

    <!-- Estilos exclusivos del manual -->
    <link
        rel="stylesheet"
        href="Assets/css/manual.css?v=<?= $escaparManual($versionCssManual); ?>"
    >

    <!-- Cargar los scripts respetando su orden -->
    <script
        src="Assets/libs/jquery/jquery-3.7.1.min.js"
        defer
    ></script>

    <script
        src="Assets/js/manual.js?v=<?= $escaparManual($versionJsManual); ?>"
        defer
    ></script>

</head>

<body class="manual-pagina" id="arriba">

    <!-- ================================================= -->
    <!-- CABECERA Y NAVEGACIÓN -->
    <!-- ================================================= -->

    <a class="manual-salto" href="#contenido">
        Ir al contenido
    </a>

    <header class="manual-cabecera">

        <!-- Identidad del manual -->
        <a
            class="manual-marca"
            href="Index.php?url=manual"
            aria-label="Portada del manual"
        >
            <img
                src="Assets/img/LogoTR.png"
                alt=""
                width="76"
                height="76"
            >

            <span>
                Templo Rosa
                <small>Manual de usuario</small>
            </span>
        </a>

        <!-- Accesos principales -->
        <nav
            class="manual-navegacion"
            aria-label="Navegación del manual"
        >
            <a href="Index.php?url=manual#explora">
                Explora
            </a>

            <a href="Index.php?url=manual#ayuda">
                Ayuda
            </a>

            <a
                class="manual-boton"
                href="Index.php?url=inicio"
            >
                Volver al sistema
            </a>
        </nav>

    </header>

    <main id="contenido">

        <!-- ============================================= -->
        <!-- PRESENTACIÓN DEL MANUAL -->
        <!-- ============================================= -->

        <section
            class="manual-portada manual-ancho"
            aria-labelledby="tituloManual"
        >
            <div>

                <p class="manual-etiqueta">
                    Manual de usuario
                </p>

                <h1 id="tituloManual">
                    Tu guía para crear
                    <em>una atención perfecta.</em>
                </h1>

                <p class="manual-introduccion">
                    Aprende a usar Templo Rosa, paso a paso.
                </p>

                <a class="manual-boton" href="#explora">
                    Comenzar el recorrido
                    <span aria-hidden="true">→</span>
                </a>

            </div>

            <!-- Personaje actual de Templo Rosa -->
            <img
                class="manual-personaje"
                src="Assets/img/TR_Girl_Pant.png"
                alt="Personaje de Templo Rosa con instrumentos de manicura"
                width="1024"
                height="1536"
                fetchpriority="high"
            >

        </section>


        <!-- ============================================= -->
        <!-- EXPLORACIÓN DE GUÍAS -->
        <!-- ============================================= -->

        <section
            class="manual-seccion manual-ancho"
            id="explora"
            aria-labelledby="tituloExplora"
        >
            <p class="manual-etiqueta">
                Explora
            </p>

            <h2 id="tituloExplora">
                Encuentra tu siguiente paso
            </h2>

            <!-- Buscar entre las guías disponibles -->
            <div
                class="manual-busqueda"
                id="busquedaManual"
                role="search"
                hidden
            >
                <label for="buscarManual">
                    ¿Qué necesitas aprender?
                </label>

                <div>
                    <input
                        id="buscarManual"
                        type="search"
                        placeholder="Busca una guía"
                        autocomplete="off"
                    >

                    <button
                        class="manual-boton manual-boton-claro"
                        id="limpiarBusquedaManual"
                        type="button"
                    >
                        Limpiar
                    </button>
                </div>

                <p
                    id="resultadoBusquedaManual"
                    class="manual-nota"
                    role="status"
                    aria-atomic="true"
                ></p>
            </div>

            <!-- Mostrar las guías entregadas por el controlador -->
            <div class="manual-guias">

                <?php foreach ($guiasManual as $clave => $guia): ?>

                    <article
                        class="manual-tarjeta"
                        data-guia-manual
                    >
                        <h3>
                            <?= $escaparManual($guia['titulo']); ?>
                        </h3>

                        <p>
                            <?= $escaparManual($guia['descripcion']); ?>
                        </p>

                        <a
                            class="manual-enlace"
                            href="Index.php?url=manual&amp;guia=<?= $escaparManual(rawurlencode($clave)); ?>#guia"
                            aria-label="<?= $escaparManual('Ver guía: ' . $guia['titulo']); ?>"
                        >
                            Ver guía
                            <span aria-hidden="true">→</span>
                        </a>
                    </article>

                <?php endforeach; ?>

            </div>

            <p id="sinResultadosManual" hidden>
                No hay guías que coincidan.
                Prueba con otra palabra.
            </p>

        </section>


        <!-- ============================================= -->
        <!-- LECTURA DE LA GUÍA SELECCIONADA -->
        <!-- ============================================= -->

        <?php if ($guiaManual !== null || $mensajeManual !== ''): ?>

            <section
                class="manual-seccion manual-ancho"
                id="guia"
                aria-labelledby="tituloGuiaManual"
                tabindex="-1"
            >
                <?php if ($guiaManual !== null): ?>

                    <p class="manual-etiqueta">
                        Paso a paso
                    </p>

                    <h2 id="tituloGuiaManual">
                        <?= $escaparManual($guiaManual['titulo']); ?>
                    </h2>

                    <p>
                        <?= $escaparManual($guiaManual['descripcion']); ?>
                    </p>

                    <!-- Instrucciones de la guía -->
                    <ol class="manual-pasos">

                        <?php foreach ($guiaManual['pasos'] as $paso): ?>

                            <li>
                                <?= $escaparManual($paso); ?>
                            </li>

                        <?php endforeach; ?>

                    </ol>

                <?php else: ?>

                    <h2 id="tituloGuiaManual">
                        Guía no disponible
                    </h2>

                    <p role="alert">
                        <?= $escaparManual($mensajeManual); ?>
                    </p>

                <?php endif; ?>

                <a
                    class="manual-enlace"
                    href="Index.php?url=manual#explora"
                >
                    ← Volver a Explora
                </a>

            </section>

        <?php endif; ?>


        <!-- ============================================= -->
        <!-- AYUDA DE NAVEGACIÓN -->
        <!-- ============================================= -->

        <section
            class="manual-seccion manual-ancho"
            id="ayuda"
            aria-labelledby="tituloAyudaManual"
        >
            <p class="manual-etiqueta">
                Ayuda
            </p>

            <h2 id="tituloAyudaManual">
                ¿Por dónde empezamos?
            </h2>

            <!-- Preguntas desplegables -->
            <details class="manual-pregunta">
                <summary>¿Cómo encuentro una guía?</summary>

                <p>
                    Abre Explora, revisa los temas disponibles
                    y selecciona Ver guía. También puedes
                    escribir una palabra en el buscador.
                </p>
            </details>

            <details class="manual-pregunta">
                <summary>¿Cómo regreso al listado?</summary>

                <p>
                    Selecciona Volver a Explora al terminar
                    una guía o utiliza Explora en la cabecera.
                </p>
            </details>

            <details class="manual-pregunta">
                <summary>
                    ¿Cómo continúo trabajando en el sistema?
                </summary>

                <p>
                    Selecciona Volver al sistema.
                    Ese enlace abre el Inicio de Templo Rosa.
                </p>
            </details>

        </section>

    </main>


    <!-- ================================================= -->
    <!-- PIE DEL MANUAL -->
    <!-- ================================================= -->

    <footer class="manual-pie manual-ancho">

        <p>
            <strong>Templo Rosa Nails Studio</strong>
            <br>
            Tu sistema, paso a paso.
        </p>

        <a class="manual-enlace" href="#arriba">
            Volver arriba ↑
        </a>

    </footer>

</body>
</html>