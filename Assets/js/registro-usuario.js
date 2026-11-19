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
    let preparandoEnvio = false;
    let redirigiendo = false;
    let validacionesCompartidas = null;
    let reglasRegistro = null;
    let mensaje = formulario.prevAll('p[role="alert"]').first();


    /* Utilizar los mensajes de las validaciones compartidas */
    formulario.prop("noValidate", true);


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
            color: "#c40056",
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
    }


    /* ================================================= */
    /* VALIDACIONES COMPARTIDAS DEL REGISTRO */
    /* ================================================= */

    const cargaValidaciones = import("./validaciones/validaciones.js")
        .then(function (modulo) {
            const validaciones = modulo.Validaciones;

            reglasRegistro = Object.freeze({
                nombres: validaciones.reglasUsuarios.nombre,
                apellidos: validaciones.reglasUsuarios.apellido,
                cedula: validaciones.reglasUsuarios.cedula,
                telefono: validaciones.reglasUsuarios.telefono,
                correo: validaciones.reglasUsuarios.correo,

                clave: {
                    comprobar: function (valor) {
                        if (typeof valor !== "string" || valor === "") {
                            return "Ingrese la contraseña.";
                        }

                        if (validaciones.validarClave(valor)) return null;

                        if (new TextEncoder().encode(valor).length > 72) {
                            return "La contraseña es demasiado larga. Utilice una más corta.";
                        }

                        return "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo, sin espacios.";
                    }
                },

                confirmar_clave: {
                    comprobar: function (valor) {
                        if (typeof valor !== "string" || valor === "") {
                            return "Confirme la contraseña.";
                        }

                        return valor !== clave.val()
                            ? "Las contraseñas no coinciden."
                            : null;
                    }
                },

                id_rol: validaciones.reglasUsuarios.id_rol
            });


            /* Mantener el límite del teléfono acorde con el modelo */
            formulario.find("[name='telefono']").prop("maxLength", 25);

            if (confirmarClave.length) confirmarClave[0].setCustomValidity("");

            validaciones.activar(formulario[0], reglasRegistro);
            validacionesCompartidas = validaciones;

            return validaciones;

        }).catch(function () {
            return null;
        });


    /* Revisar una confirmación ya validada cuando cambie la contraseña */
    clave.on("input", function () {
        if (validacionesCompartidas && confirmarClave.length) {
            confirmarClave[0].dispatchEvent(
                new Event("input", { bubbles: true })
            );
        }
    });


    /* ================================================= */
    /* MOSTRAR U OCULTAR CONTRASEÑAS */
    /* ================================================= */

    formulario.find(".btnAlternarClaveRegistro").each(function () {
        const boton = $(this);
        const descripcion = (boton.attr("aria-label") || "Mostrar contraseña")
            .replace(/^(Mostrar|Ocultar)\s+/i, "");

        boton.data("descripcionClave", descripcion);
        boton.attr("aria-pressed", "false");
    });


    formulario.on("click", ".btnAlternarClaveRegistro", function () {
        const boton = $(this);
        const idCampo = boton.attr("data-password-target");
        const campo = idCampo ? $("#" + $.escapeSelector(idCampo)) : $();

        if (!campo.length) return;

        const mostrar = campo.prop("type") !== "text";
        const descripcion = boton.data("descripcionClave") || "contraseña";
        const texto = (mostrar ? "Ocultar " : "Mostrar ") + descripcion;

        campo.prop("type", mostrar ? "text" : "password");

        boton.find(".templo-eye-open").toggle(mostrar);
        boton.find(".templo-eye-closed").toggle(!mostrar);

        boton.attr({
            "aria-label": texto,
            title: texto,
            "aria-pressed": String(mostrar)
        });
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

        if (enviando || preparandoEnvio || redirigiendo) return;

        preparandoEnvio = true;

        let validaciones = null;

        try {
            validaciones = await cargaValidaciones;

        } finally {
            preparandoEnvio = false;
        }

        limpiarAvisos();

        if (!validaciones) {
            mostrarError("No fue posible cargar las validaciones. Recargue la página antes de continuar.");
            return;
        }

        validaciones.limpiarFormulario(formulario[0]);

        const resultado = validaciones.validarFormulario(formulario[0], reglasRegistro);

        if (!resultado.valido) {
            if (resultado.primerCampo) resultado.primerCampo.focus();
            return;
        }

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
                campo: typeof respuesta?.campo === "string" ? respuesta.campo : "",
                errores: respuesta?.errores
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
                campo: typeof respuesta?.campo === "string" ? respuesta.campo : "",
                errores: respuesta?.errores
            };

        } finally {
            if (!redirigiendo) {

                /* Recuperar los controles antes de presentar los errores */
                controles.forEach(control => {
                    control.elemento.prop("disabled", control.deshabilitado);
                });

                botonEnviar.html(contenidoBoton);
                formulario.removeAttr("aria-busy");
                enviando = false;

                if (avisoPendiente) {
                    let primerCampo = null;

                    if ($.isPlainObject(avisoPendiente.errores)) {
                        primerCampo = validaciones.mostrarErrores(
                            formulario[0],
                            avisoPendiente.errores
                        );
                    }

                    window.Alertas?.rechazo(
                        avisoPendiente.texto,
                        avisoPendiente,
                        formulario
                    );

                    mostrarError(
                        avisoPendiente.texto,
                        avisoPendiente.campo || primerCampo?.name || ""
                    );
                }
            }
        }
    });
});