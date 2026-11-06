/* ===================================================== */
/* ACCESO AL SISTEMA */
/* ===================================================== */

$(function () {
    "use strict";

    /* ================================================= */
    /* VISIBILIDAD DE LA CONTRASEÑA */
    /* ================================================= */

    const campoClave = $("#clave");
    const botonMostrarClave = $("#btnMostrarClave");
    const ojoCerrado = botonMostrarClave.find(".ojo-cerrado");
    const ojoAbierto = botonMostrarClave.find(".ojo-abierto");

    if (campoClave.length && botonMostrarClave.length) {

        function actualizarVisibilidad(claveVisible) {
            const accion = claveVisible ? "Ocultar" : "Mostrar";

            campoClave.prop("type", claveVisible ? "text" : "password");

            ojoCerrado.toggle(!claveVisible);
            ojoAbierto.toggle(claveVisible);

            botonMostrarClave.attr({
                "aria-label": accion + " contraseña",
                title: accion + " contraseña",
                "aria-pressed": String(claveVisible)
            });
        }

        actualizarVisibilidad(false);

        botonMostrarClave.on("click", function () {
            actualizarVisibilidad(campoClave.prop("type") === "password");
        });
    }


    /* ================================================= */
    /* SOLICITUDES DE ACCESO */
    /* ================================================= */

    function solicitarAcceso(ruta, datos) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: ruta,
                type: "POST",
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                processData: false,
                data: JSON.stringify(datos),
                headers: { "X-Requested-With": "XMLHttpRequest" },
                timeout: 30000,

                success: function (respuesta) {
                    resolve(respuesta);
                },

                error: function (solicitud, estado) {
                    const respuesta = solicitud.responseJSON;

                    const mensaje = typeof respuesta?.mensaje === "string"
                        ? respuesta.mensaje
                        : estado === "timeout"
                            ? "La solicitud tardó demasiado. Intente nuevamente."
                            : "No fue posible completar la solicitud. Intente nuevamente.";

                    const error = new Error(mensaje);

                    error.status = solicitud.status;
                    error.respuesta = respuesta;

                    reject(error);
                }
            });
        });
    }


    /* ================================================= */
    /* ENVÍO DE LOS FORMULARIOS DE ACCESO */
    /* ================================================= */

    $("#formLogin, #formClaveAdmin").each(function () {
        const formulario = $(this);
        const esLogin = formulario.attr("id") === "formLogin";
        const aviso = $(".login-access-header .login-message").first();

        let enviando = false;
        let redirigiendo = false;


        /* Mostrar los mensajes en el espacio reservado de la vista */
        function mostrarMensaje(mensaje) {
            aviso.text(mensaje).prop("hidden", false);
        }


        /* Validar y enviar los datos del formulario */
        formulario.on("submit", async function (evento) {
            evento.preventDefault();

            if (enviando || redirigiendo || !this.reportValidity()) {
                return;
            }

            const datos = {};
            const estadosControles = [];
            const etiquetasBotones = [];


            /* Recoger los datos antes de deshabilitar los controles */
            formulario.serializeArray().forEach(function (campo) {
                datos[campo.name] = campo.value;
            });


            /* Conservar el estado original de los controles */
            formulario.find(":input").each(function () {
                const elemento = $(this);

                estadosControles.push({
                    elemento,
                    deshabilitado: elemento.prop("disabled")
                });
            });


            /* Conservar el contenido completo de los botones de envío */
            formulario.find(":submit").each(function () {
                const boton = $(this);
                const esInput = boton.is("input");

                etiquetasBotones.push({
                    elemento: boton,
                    esInput,
                    contenido: esInput ? boton.val() : boton.html()
                });

                if (esInput) {
                    boton.val("Validando...");
                } else {
                    boton.text("Validando...");
                }
            });


            /* Impedir envíos repetidos durante la solicitud */
            enviando = true;

            aviso.text("").prop("hidden", true);

            formulario.attr("aria-busy", "true");
            formulario.find(":input").prop("disabled", true);

            try {
                const respuesta = await solicitarAcceso(formulario.attr("action"), datos);


                /* Mantener el formulario abierto si los datos son rechazados */
                if (!respuesta || respuesta.ok !== true) {
                    mostrarMensaje(
                        typeof respuesta?.mensaje === "string"
                            ? respuesta.mensaje
                            : "No fue posible validar los datos. Intente nuevamente."
                    );

                    return;
                }


                /* Continuar hacia la ruta correspondiente al formulario */
                redirigiendo = true;

                window.location.assign(
                    esLogin
                        ? "Index.php?url=inicio"
                        : "Index.php?url=registroUsuario"
                );

            } catch (error) {
                mostrarMensaje(
                    error.message || "No fue posible completar la solicitud. Intente nuevamente."
                );

            } finally {

                /* Mantener el bloqueo mientras se realiza la redirección */
                if (!redirigiendo) {

                    /* Recuperar el estado original de los controles */
                    estadosControles.forEach(function (control) {
                        control.elemento.prop("disabled", control.deshabilitado);
                    });


                    /* Recuperar el contenido y los iconos de los botones */
                    etiquetasBotones.forEach(function (etiqueta) {
                        if (etiqueta.esInput) {
                            etiqueta.elemento.val(etiqueta.contenido);
                        } else {
                            etiqueta.elemento.html(etiqueta.contenido);
                        }
                    });

                    formulario.removeAttr("aria-busy");
                    enviando = false;
                }
            }
        });
    });


    /* ================================================= */
    /* AYUDA PARA RESTABLECER LA CONTRASEÑA */
    /* ================================================= */

    const modalAyudaClave = $("#modalAyudaClave");
    const botonAbrirAyuda = $("#btnAbrirAyudaClave");
    const botonCerrarAyuda = $("#btnCerrarAyudaClave");
    const botonAceptarAyuda = $("#btnAceptarAyudaClave");

    let elementoConFoco = $();

    if (modalAyudaClave.length && botonAbrirAyuda.length) {

        /* Abrir el modal y conservar el elemento que tenía el foco */
        function abrirAyuda() {
            elementoConFoco = $(":focus").first();

            if (!elementoConFoco.length) {
                elementoConFoco = botonAbrirAyuda;
            }

            modalAyudaClave
                .addClass("is-visible")
                .attr("aria-hidden", "false");

            $("body").addClass("login-modal-open");

            botonCerrarAyuda.trigger("focus");
        }


        /* Cerrar el modal y devolver el foco al elemento anterior */
        function cerrarAyuda() {
            modalAyudaClave
                .removeClass("is-visible")
                .attr("aria-hidden", "true");

            $("body").removeClass("login-modal-open");

            if (elementoConFoco.length && elementoConFoco.is(":visible")) {
                elementoConFoco.trigger("focus");
            }
        }


        /* Acciones de los botones del modal */
        botonAbrirAyuda.on("click", abrirAyuda);
        botonCerrarAyuda.on("click", cerrarAyuda);
        botonAceptarAyuda.on("click", cerrarAyuda);


        /* Cerrar al pulsar el fondo del modal */
        modalAyudaClave.on("click", function (evento) {
            if ($(evento.target).is("[data-cerrar-ayuda-clave]")) {
                cerrarAyuda();
            }
        });


        /* Cerrar al presionar la tecla Escape */
        $(document).on("keydown", function (evento) {
            if (evento.key === "Escape" && modalAyudaClave.hasClass("is-visible")) {
                cerrarAyuda();
            }
        });
    }


    /* ================================================= */
    /* AJUSTE DEL FRASCO A LA VENTANA */
    /* ================================================= */

    const escena = $(".login-scene").first();
    const frasco = $(".login-bottle").first();

    if (escena.length && frasco.length) {

        /* Calcular la escala utilizando las medidas sin transformar */
        function ajustarFrasco() {
            const ancho = frasco.prop("offsetWidth");
            const alto = frasco.prop("offsetHeight");

            if (!ancho || !alto) {
                return;
            }

            const escala = Math.min(
                1,
                escena.prop("clientWidth") / ancho,
                escena.prop("clientHeight") / alto
            );

            frasco.css("--login-escala", String(escala));
        }


        /* Ajustar al iniciar y cuando cambie el tamaño de la ventana */
        ajustarFrasco();

        $(window).on("load resize", ajustarFrasco);


        /* Detectar cambios en las dimensiones de la escena y del frasco */
        if ("ResizeObserver" in window) {
            const observador = new window.ResizeObserver(ajustarFrasco);

            observador.observe(escena[0]);
            observador.observe(frasco[0]);
        }
    }
});