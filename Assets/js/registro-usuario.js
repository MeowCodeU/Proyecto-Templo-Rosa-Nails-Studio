/* ===================================================== */
/* REGISTRO DE USUARIO */
/* ===================================================== */

$(function () {
    "use strict";

    const formulario = $("#formRegistroUsuario");

    if (!formulario.length) return;

    const clave = $("#clave");
    const confirmarClave = $("#confirmarClave");
    const botonEnviar = formulario.find('[type="submit"]');
    const urlRegistro = formulario.attr("action") || "Index.php?url=registroUsuario";

    let enviando = false;
    let redirigiendo = false;
    let mensaje = formulario.prevAll('p[role="alert"]').first();


    /* ================================================= */
    /* MENSAJES DEL FORMULARIO */
    /* ================================================= */

    /* Mantener la presentación del aviso que utiliza la vista */
    if (!mensaje.length) {
        mensaje = $("<p>", {
            role: "alert",
            hidden: true
        }).css({
            margin: "0 0 18px",
            color: "#8a2635",
            fontSize: "0.92rem",
            textAlign: "center"
        }).insertBefore(formulario);
    }

    mensaje.attr("id", "mensajeRegistroUsuario");

    function mostrarError(texto, campo = "") {
        mensaje.text(texto).prop("hidden", false);

        if (campo) {
            formulario.find("[name]").filter(function () {
                return $(this).attr("name") === campo;
            }).first().attr("aria-invalid", "true").trigger("focus");
        }

        mensaje[0].scrollIntoView({
            behavior: "smooth",
            block: "nearest"
        });
    }

    function limpiarAvisos() {
        mensaje.empty().prop("hidden", true);
        formulario.find('[aria-invalid="true"]').removeAttr("aria-invalid");
    }


    /* ================================================= */
    /* MOSTRAR U OCULTAR CONTRASEÑAS */
    /* ================================================= */

    formulario.on("click", ".btnAlternarClaveRegistro", function () {
        const boton = $(this);
        const idCampo = boton.attr("data-password-target");
        const campo = idCampo ? $("#" + $.escapeSelector(idCampo)) : $();

        if (!campo.length) return;

        const mostrar = campo.prop("type") !== "text";

        campo.prop("type", mostrar ? "text" : "password");

        boton.find(".templo-eye-open").toggle(mostrar);
        boton.find(".templo-eye-closed").toggle(!mostrar);

        boton.attr({
            "aria-label": (mostrar ? "Ocultar" : "Mostrar") + " contraseña",
            title: (mostrar ? "Ocultar" : "Mostrar") + " contraseña",
            "aria-pressed": String(mostrar)
        });
    });


    /* ================================================= */
    /* COINCIDENCIA DE LAS CONTRASEÑAS */
    /* ================================================= */

    function validarCoincidencia() {
        if (!clave.length || !confirmarClave.length) return;

        confirmarClave[0].setCustomValidity(
            confirmarClave.val() !== "" && clave.val() !== confirmarClave.val()
                ? "Las contraseñas no coinciden."
                : ""
        );
    }

    $("#clave, #confirmarClave").on("input", validarCoincidencia);

    formulario.on("input change", "[name]", function () {
        $(this).removeAttr("aria-invalid");
    });


    /* ================================================= */
    /* REDIRECCIONES DEL REGISTRO */
    /* ================================================= */

    function revisarAutorizacion(respuesta) {
        if (respuesta?.redireccion === "Index.php?url=claveAdmin") {
            redirigiendo = true;
            window.location.assign("Index.php?url=claveAdmin");
            return true;
        }

        return false;
    }


    /* ================================================= */
    /* SOLICITUD DE REGISTRO */
    /* ================================================= */

    function solicitarRegistro(datos) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: urlRegistro,
                method: "POST",
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                headers: { "X-Requested-With": "XMLHttpRequest" },
                data: JSON.stringify(datos),
                processData: false,
                timeout: 30000,

                success: function (respuesta) {
                    resolve(respuesta);
                },

                error: function (xhr, estado) {
                    const respuesta = xhr.responseJSON;
                    const ambiguo = xhr.status === 0 ||
                        estado === "timeout" || estado === "parsererror";

                    const texto = typeof respuesta?.mensaje === "string"
                        ? respuesta.mensaje
                        : ambiguo
                            ? "No se pudo confirmar el registro. Intente iniciar sesión antes de volver a registrarse."
                            : "No fue posible completar el registro. Intente nuevamente.";

                    const error = new Error(texto);

                    error.status = xhr.status;
                    error.respuesta = respuesta;

                    reject(error);
                }
            });
        });
    }


    /* ================================================= */
    /* ENVÍO DEL FORMULARIO */
    /* ================================================= */

    formulario.on("submit", async function (evento) {
        evento.preventDefault();

        if (enviando || redirigiendo) return;

        limpiarAvisos();
        validarCoincidencia();

        if (!this.reportValidity()) return;

        const datos = {};

        formulario.serializeArray().forEach(function (campo) {
            datos[campo.name] = campo.value;
        });

        const controles = formulario.find("input, select, textarea, button")
            .map(function () {
                const elemento = $(this);

                return {
                    elemento,
                    deshabilitado: elemento.prop("disabled")
                };
            }).get();

        const contenidoBoton = botonEnviar.html();

        let avisoPendiente = null;

        enviando = true;
        formulario.attr("aria-busy", "true");

        controles.forEach(control => {
            control.elemento.prop("disabled", true);
        });

        botonEnviar.text("Registrando...");

        try {
            const respuesta = await solicitarRegistro(datos);

            if (revisarAutorizacion(respuesta)) return;

            if (respuesta && respuesta.ok === true) {
                redirigiendo = true;
                window.location.assign("Index.php?url=login");
                return;
            }

            avisoPendiente = {
                texto: typeof respuesta?.mensaje === "string"
                    ? respuesta.mensaje
                    : "No se pudo confirmar el registro. Intente iniciar sesión antes de volver a registrarse.",
                campo: typeof respuesta?.campo === "string" ? respuesta.campo : ""
            };

        } catch (error) {
            const respuesta = error.respuesta;

            if (revisarAutorizacion(respuesta)) return;

            if (error.status === 401) {
                redirigiendo = true;
                window.location.assign("Index.php?url=claveAdmin");
                return;
            }

            avisoPendiente = {
                texto: error.message || "No fue posible completar el registro. Intente nuevamente.",
                campo: typeof respuesta?.campo === "string" ? respuesta.campo : ""
            };

        } finally {
            if (!redirigiendo) {

                /* Recuperar los controles antes de enfocar un campo con error */
                controles.forEach(control => {
                    control.elemento.prop("disabled", control.deshabilitado);
                });

                botonEnviar.html(contenidoBoton);
                formulario.attr("aria-busy", "false");
                enviando = false;

                if (avisoPendiente) {
                    mostrarError(avisoPendiente.texto, avisoPendiente.campo);
                }
            }
        }
    });
});