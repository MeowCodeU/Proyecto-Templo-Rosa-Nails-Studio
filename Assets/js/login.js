/* ===================================================== */
/* ACCESO AL SISTEMA */
/* ===================================================== */

$(function () {
    "use strict";

    let accesoOcupado = false;
    let recuperacionOcupada = false;
    let recuperacionRedirigiendo = false;


    /* Cargar las reglas compartidas sin cambiar los scripts de las vistas */
    const cargaValidaciones = import("./validaciones/validaciones.js")
        .then(function (modulo) {
            const validaciones = modulo.Validaciones;

            return {
                validaciones,

                login: Object.freeze({
                    cedula: validaciones.reglasClientes.cedula,
                    clave: {
                        comprobar: valor => typeof valor === "string" && valor !== "" && !valor.includes("\0")
                            ? null : "Ingrese una contraseña válida."
                    }
                }),

                universal: Object.freeze({
                    clave_universal: {
                        comprobar: function (valor) {
                            if (typeof valor !== "string" || valor.includes("\0")) {
                                return "La clave universal ingresada no es válida.";
                            }

                            return valor.trim() === "" ? "Debe ingresar la clave universal." : null;
                        }
                    }
                })
            };

        }).catch(function () {
            return null;
        });


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
                    error.ambiguo = solicitud.status === 0 || estado === "timeout" || estado === "parsererror";

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
        const aviso = formulario.closest(".login-content")
            .find(".login-access-header .login-message").first();

        let enviando = false;
        let preparandoEnvio = false;
        let redirigiendo = false;
        let revisionCedula = 0;
        let campoAvisoFormato = "";


        /* Utilizar el aviso reservado sin modificar la distribución del frasco */
        formulario.prop("noValidate", true);

        const mensajeInicial = aviso.text().trim();

        if (mensajeInicial && window.Alertas?.disponible()) {
            window.Alertas.notificar(
                mensajeInicial,
                aviso.attr("data-tipo-aviso") || "error"
            );
        }


        function limpiarMensajes() {
            revisionCedula++;
            campoAvisoFormato = "";
            aviso.empty().prop("hidden", true);
            formulario.find("[aria-invalid], [data-templo-validacion]")
                .removeAttr("aria-invalid data-templo-validacion");
        }


        /* Mostrar los mensajes en el espacio reservado de la vista */
        function mostrarMensaje(mensaje, errores = null, campoError = "") {
            revisionCedula++;
            campoAvisoFormato = "";
            aviso.text(mensaje).prop("hidden", false);

            let primerCampo = null;

            if ($.isPlainObject(errores)) {
                Object.keys(errores).forEach(function (nombre) {
                    const campo = formulario.find("[name]").filter(function () {
                        return this.name === nombre;
                    }).first();

                    if (!campo.length || campo.prop("type") === "hidden") return;

                    campo.attr({ "aria-invalid": "true", "data-templo-validacion": "invalido" });

                    if (!primerCampo) primerCampo = campo;
                });
            }

            if (!primerCampo && campoError) {
                const campo = formulario.find("[name]").filter(function () {
                    return this.name === campoError;
                }).first();

                if (campo.length && campo.prop("type") !== "hidden") {
                    campo.attr({ "aria-invalid": "true", "data-templo-validacion": "invalido" });
                    primerCampo = campo;
                }
            }

            if (primerCampo) primerCampo.trigger("focus");
        }


        /* Retirar el aviso anterior cuando se corrige la entrada */
        formulario.on("input change", "[name]", function () {
            revisionCedula++;
            campoAvisoFormato = "";
            $(this).removeAttr("aria-invalid data-templo-validacion");
            aviso.empty().prop("hidden", true);
        });


        /* Presentar la validación del formato sin consultar las credenciales */
        formulario.on("input change focusout", "input[name]:not([type='hidden'])", async function () {
            const campo = $(this);
            const nombre = campo.attr("name");
            const revisionActual = ++revisionCedula;

            if (enviando || preparandoEnvio || redirigiendo || campo.prop("disabled")) return;

            const configuracion = await cargaValidaciones;

            if (revisionActual !== revisionCedula || enviando || preparandoEnvio || redirigiendo) return;
            if (!configuracion) return;

            const reglas = esLogin ? configuracion.login : configuracion.universal;

            if (!Object.prototype.hasOwnProperty.call(reglas, nombre)) return;

            const mensaje = configuracion.validaciones.validarValor(nombre, campo.val(), reglas);

            if (mensaje) {
                campoAvisoFormato = nombre;
                campo.attr({ "aria-invalid": "true", "data-templo-validacion": "invalido" });

                aviso.attr("data-tipo-aviso", "error")
                    .text(mensaje)
                    .prop("hidden", false);

            } else {
                /* Respetar los errores del servidor hasta que se corrija el campo */
                if (campo.attr("aria-invalid") !== "true" || campoAvisoFormato === nombre) {
                    campo.removeAttr("aria-invalid")
                        .attr("data-templo-validacion", "valido");
                }

                if (campoAvisoFormato === nombre) {
                    campoAvisoFormato = "";
                    aviso.empty().prop("hidden", true);
                }
            }
        });


        /* Comprobar los valores con las reglas comunes */
        function validarAcceso(configuracion) {
            const reglas = esLogin ? configuracion.login : configuracion.universal;
            const errores = {};

            Object.keys(reglas).forEach(function (nombre) {
                const campo = formulario.find("[name]").filter(function () {
                    return this.name === nombre;
                }).first();

                if (!campo.length) return;

                const mensaje = configuracion.validaciones.validarValor(nombre, campo.val(), reglas);

                campo.attr("data-templo-validacion", mensaje ? "invalido" : "valido");

                if (mensaje) errores[nombre] = mensaje;
            });

            return errores;
        }


        formulario.on("submit", async function (evento) {
            evento.preventDefault();

            if (enviando || preparandoEnvio || redirigiendo || accesoOcupado || recuperacionOcupada || recuperacionRedirigiendo) return;

            preparandoEnvio = true;
            accesoOcupado = true;

            let configuracion = null;

            try {
                configuracion = await cargaValidaciones;

            } finally {
                preparandoEnvio = false;
            }

            limpiarMensajes();

            if (!configuracion) {
                accesoOcupado = false;
                mostrarMensaje("No se cargaron las validaciones. Recargue la página.");
                return;
            }

            const errores = validarAcceso(configuracion);

            if (Object.keys(errores).length) {
                accesoOcupado = false;
                mostrarMensaje(Object.values(errores)[0], errores);
                return;
            }

            const datos = {};
            const estadosControles = [];
            const etiquetasBotones = [];

            let avisoPendiente = null;


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

            formulario.attr("aria-busy", "true");
            formulario.find(":input").prop("disabled", true);

            try {
                const respuesta = await solicitarAcceso(formulario.attr("action"), datos);

                if (!respuesta || respuesta.ok !== true) {
                    avisoPendiente = {
                        mensaje: typeof respuesta?.mensaje === "string"
                            ? respuesta.mensaje : "No fue posible validar los datos. Intente nuevamente.",
                        errores: respuesta?.errores,
                        campo: typeof respuesta?.campo === "string" ? respuesta.campo : ""
                    };

                } else {
                    redirigiendo = true;

                    window.location.assign(
                        esLogin
                            ? "Index.php?url=inicio"
                            : "Index.php?url=registroUsuario"
                    );
                }

            } catch (error) {
                const respuesta = error.respuesta;

                avisoPendiente = {
                    mensaje: error.message || "No fue posible completar la solicitud. Intente nuevamente.",
                    errores: respuesta?.errores,
                    campo: typeof respuesta?.campo === "string" ? respuesta.campo : ""
                };

            } finally {

                /* Mantener el bloqueo mientras se realiza la redirección */
                if (!redirigiendo) {
                    estadosControles.forEach(function (control) {
                        control.elemento.prop("disabled", control.deshabilitado);
                    });

                    etiquetasBotones.forEach(function (etiqueta) {
                        if (etiqueta.esInput) {
                            etiqueta.elemento.val(etiqueta.contenido);
                        } else {
                            etiqueta.elemento.html(etiqueta.contenido);
                        }
                    });

                    formulario.removeAttr("aria-busy");

                    enviando = false;
                    accesoOcupado = false;

                    if (avisoPendiente) {
                        mostrarMensaje(
                            avisoPendiente.mensaje,
                            avisoPendiente.errores,
                            avisoPendiente.campo
                        );

                        window.Alertas?.rechazo(
                            avisoPendiente.mensaje,
                            { errores: avisoPendiente.errores },
                            formulario
                        );
                    }
                }
            }
        });
    });


    /* ================================================= */
    /* RECUPERACIÓN DE CONTRASEÑA */
    /* ================================================= */

    const modalAyudaClave = $("#modalAyudaClave");
    const botonAbrirAyuda = $("#btnAbrirAyudaClave");
    const botonCerrarAyuda = $("#btnCerrarAyudaClave");
    const formularioSolicitud = $("#formSolicitarRecuperacion");
    const formularioRecuperacion = $("#formRecuperarClave");
    const mensajeRecuperacion = $("#mensajeRecuperacionClave");
    const botonSolicitarCodigo = $("#btnSolicitarCodigoRecuperacion");
    const botonReenviarCodigo = $("#btnReenviarCodigoRecuperacion");
    const botonesClaveRecuperacion = $(".btnAlternarClaveRecuperacion");

    let elementoConFoco = botonAbrirAyuda;
    let validacionesRecuperacion = null;
    let reglasRecuperacion = null;
    let limiteReenvio = 0;
    let relojReenvio = null;

    if (modalAyudaClave.length && formularioSolicitud.length && formularioRecuperacion.length) {
        formularioSolicitud.add(formularioRecuperacion).prop("noValidate", true);


        /* Reutilizar el mismo ojito del formulario de acceso */
        botonesClaveRecuperacion.each(function () {
            const boton = $(this);
            const iconos = botonMostrarClave.find(".icono-eye").children().clone();

            if (iconos.length) boton.find(".icono-eye").empty().append(iconos);

            boton.data("descripcionClave", (boton.attr("aria-label") || "Mostrar contraseña").slice(8));
            cambiarVisibilidadRecuperacion(boton, false);
        });


        function cambiarVisibilidadRecuperacion(boton, visible) {
            const idCampo = boton.attr("data-password-target");
            const campo = idCampo ? document.getElementById(idCampo) : null;

            if (!campo || !formularioRecuperacion[0].contains(campo)) return;

            const texto = (visible ? "Ocultar " : "Mostrar ") + boton.data("descripcionClave");

            campo.type = visible ? "text" : "password";
            boton.find(".ojo-cerrado, .bi-eye-slash").toggle(!visible);
            boton.find(".ojo-abierto").toggle(visible);
            boton.attr({ "aria-label": texto, title: texto, "aria-pressed": String(visible) });
        }


        botonesClaveRecuperacion.on("click", function () {
            if (recuperacionOcupada) return;

            const boton = $(this);
            const campo = document.getElementById(boton.attr("data-password-target"));

            cambiarVisibilidadRecuperacion(boton, campo?.type === "password");
        });


        /* Eliminar los valores secretos cuando se termina o abandona un paso */
        function limpiarClavesRecuperacion() {
            formularioRecuperacion.find("[name='codigo_recuperacion'], [name='nueva_clave'], [name='confirmar_clave']").val("");

            botonesClaveRecuperacion.each(function () {
                cambiarVisibilidadRecuperacion($(this), false);
            });

            validacionesRecuperacion?.limpiarFormulario(formularioRecuperacion[0]);
        }


        function limpiarMensajeRecuperacion() {
            mensajeRecuperacion.empty().prop("hidden", true);
        }


        /* Mantener los mensajes dentro del formulario de recuperación */
        function mostrarMensajeRecuperacion(mensaje, tipo = "error") {
            const tipoSeguro = ["info", "success", "warning", "error"].includes(tipo) ? tipo : "error";

            mensajeRecuperacion.text(mensaje)
                .attr({ "data-tipo": tipoSeguro, role: tipoSeguro === "error" ? "alert" : "status" })
                .prop("hidden", false);
        }


        function formularioDelPaso() {
            return modalAyudaClave.attr("data-paso-recuperacion") === "restablecer"
                ? formularioRecuperacion : formularioSolicitud;
        }


        function enfocarPaso() {
            const campo = formularioDelPaso().find("input:not([type='hidden']):enabled").first();

            if (campo.length) campo.trigger("focus");
            else botonCerrarAyuda.trigger("focus");
        }


        /* Cambiar de formulario sin abrir una página adicional */
        function mostrarPasoRecuperacion(paso) {
            const restablecer = paso === "restablecer";
            const descripcion = $("#textoAyudaClave");

            modalAyudaClave.attr("data-paso-recuperacion", restablecer ? "restablecer" : "solicitar");
            formularioSolicitud.prop("hidden", restablecer);
            formularioRecuperacion.prop("hidden", !restablecer);
            descripcion.text(descripcion.attr(restablecer ? "data-texto-restablecer" : "data-texto-solicitar"));
        }


        /* Abrir el modal y retirar el acceso del recorrido del teclado */
        function abrirAyuda() {
            if (accesoOcupado || recuperacionOcupada || recuperacionRedirigiendo) return;

            const activo = document.activeElement;

            elementoConFoco = activo && !modalAyudaClave[0].contains(activo) ? $(activo) : botonAbrirAyuda;

            modalAyudaClave.addClass("is-visible").attr("aria-hidden", "false");
            botonAbrirAyuda.attr("aria-expanded", "true");
            $("body").addClass("login-modal-open");
            $(".login-page").prop("inert", true).attr("inert", "");

            enfocarPaso();
        }


        /* Conservar el paso y la identificación, retirando los valores secretos */
        function cerrarAyuda() {
            if (recuperacionOcupada || recuperacionRedirigiendo) return;

            modalAyudaClave.removeClass("is-visible").attr("aria-hidden", "true");
            botonAbrirAyuda.attr("aria-expanded", "false");
            $("body").removeClass("login-modal-open");
            $(".login-page").prop("inert", false).removeAttr("inert");
            limpiarClavesRecuperacion();
            limpiarMensajeRecuperacion();

            const destino = elementoConFoco[0];

            if (destino?.isConnected && destino !== document.body && !destino.disabled) {
                destino.focus();
            } else {
                botonAbrirAyuda.trigger("focus");
            }
        }


        botonAbrirAyuda.on("click", abrirAyuda);
        botonCerrarAyuda.on("click", cerrarAyuda);

        $("#btnVolverLoginRecuperacion").on("click", function (evento) {
            evento.preventDefault();
            cerrarAyuda();
        });

        modalAyudaClave.on("click", function (evento) {
            if ($(evento.target).is("[data-cerrar-ayuda-clave]")) cerrarAyuda();
        });


        /* Mantener el foco dentro del modal mientras permanezca abierto */
        $(document).on("keydown", function (evento) {
            if (!modalAyudaClave.hasClass("is-visible")) return;

            if (evento.key === "Escape") {
                evento.preventDefault();
                cerrarAyuda();
                return;
            }

            if (evento.key !== "Tab") return;

            const elementos = modalAyudaClave.find("button, a[href], input:not([type='hidden']), [tabindex='0']")
                .filter(function () {
                    return !this.disabled && !this.closest("[hidden]") && $(this).is(":visible");
                }).toArray();

            if (!elementos.length) {
                evento.preventDefault();
                return;
            }

            const indice = elementos.indexOf(document.activeElement);

            if (indice === -1 || (!evento.shiftKey && indice === elementos.length - 1) ||
                (evento.shiftKey && indice === 0)) {
                evento.preventDefault();
                elementos[evento.shiftKey ? elementos.length - 1 : 0].focus();
            }
        });


        /* Actualizar la espera visual sin reenviar solicitudes automáticamente */
        function actualizarEsperaReenvio() {
            const segundos = Math.max(0, Math.ceil((limiteReenvio - Date.now()) / 1000));

            modalAyudaClave.attr("data-reintentar-en", String(segundos));
            $("#segundosReenvioRecuperacion").text(segundos);
            $("#avisoReenvioRecuperacion").prop("hidden", segundos === 0);
            botonSolicitarCodigo.add(botonReenviarCodigo)
                .prop("disabled", recuperacionOcupada || recuperacionRedirigiendo || segundos > 0);

            if (segundos === 0 && relojReenvio !== null) {
                window.clearInterval(relojReenvio);
                relojReenvio = null;
            }
        }


        function iniciarEsperaReenvio(segundos) {
            if (!Number.isSafeInteger(segundos) || segundos < 0) return;

            limiteReenvio = Date.now() + segundos * 1000;

            if (relojReenvio !== null) window.clearInterval(relojReenvio);

            relojReenvio = segundos > 0 ? window.setInterval(actualizarEsperaReenvio, 1000) : null;
            actualizarEsperaReenvio();
        }


        /* Sincronizar los dos formularios con el token recibido del servidor */
        function actualizarTokenRecuperacion(respuesta) {
            if (typeof respuesta?.token_csrf !== "string" || respuesta.token_csrf === "") return;

            formularioSolicitud.add(formularioRecuperacion).find("[name='token_csrf']")
                .val(respuesta.token_csrf).prop("defaultValue", respuesta.token_csrf);
        }


        /* Preparar una sola vez las reglas compartidas de recuperación */
        const cargaRecuperacion = cargaValidaciones.then(function (configuracion) {
            if (!configuracion) return null;

            validacionesRecuperacion = configuracion.validaciones;
            reglasRecuperacion = validacionesRecuperacion.crearReglasRecuperacionClave(formularioRecuperacion[0]);

            validacionesRecuperacion.activar(formularioSolicitud[0], validacionesRecuperacion.reglasSolicitarRecuperacion);
            validacionesRecuperacion.activar(formularioRecuperacion[0], reglasRecuperacion);

            return validacionesRecuperacion;

        }).catch(function () {
            return null;
        });


        /* Comprobar otra vez la confirmación cuando cambia la nueva contraseña */
        formularioRecuperacion.find("[name='nueva_clave']").on("input", function () {
            const confirmar = formularioRecuperacion.find("[name='confirmar_clave']");

            if (confirmar.val() !== "") confirmar[0].dispatchEvent(new Event("input", { bubbles: true }));
        });


        formularioSolicitud.add(formularioRecuperacion).on("input change", "input:not([type='hidden'])", function () {
            limpiarMensajeRecuperacion();
        });


        $("#btnVolverSolicitudRecuperacion").on("click", function () {
            if (recuperacionOcupada || recuperacionRedirigiendo) return;

            limpiarClavesRecuperacion();
            limpiarMensajeRecuperacion();
            mostrarPasoRecuperacion("solicitar");
            enfocarPaso();
        });


        /* Presentar los errores del servidor después de recuperar los controles */
        function presentarErrorRecuperacion(respuesta, mensaje, ambiguo = false) {
            if (["solicitar", "restablecer"].includes(respuesta?.paso)) {
                mostrarPasoRecuperacion(respuesta.paso);
            }

            const formulario = formularioDelPaso();
            const errores = $.isPlainObject(respuesta?.errores) ? { ...respuesta.errores } : {};

            if (typeof respuesta?.campo === "string" && !Object.hasOwn(errores, respuesta.campo)) {
                errores[respuesta.campo] = mensaje;
            }

            const primerCampo = validacionesRecuperacion?.mostrarErrores(formulario[0], errores);

            mostrarMensajeRecuperacion(mensaje, ambiguo ? "warning" : respuesta?.tipo || "error");

            if (primerCampo && !primerCampo.closest("[hidden]")) primerCampo.focus();
            else mensajeRecuperacion.trigger("focus");
        }


        /* Solicitar, reenviar o confirmar únicamente por una acción del usuario */
        async function enviarRecuperacion(esSolicitud) {
            if (recuperacionOcupada || recuperacionRedirigiendo || accesoOcupado) return;

            if (esSolicitud && limiteReenvio > Date.now()) {
                actualizarEsperaReenvio();
                mostrarMensajeRecuperacion("Espere el tiempo indicado antes de solicitar otro código.", "warning");
                return;
            }

            recuperacionOcupada = true;

            const formulario = esSolicitud ? formularioSolicitud : formularioRecuperacion;
            const estadosControles = [];
            const botonEnvio = esSolicitud ? botonSolicitarCodigo : $("#btnRecuperarClave");
            const textoBoton = botonEnvio.text();

            let respuesta = null;
            let fallo = null;

            try {
                const validaciones = await cargaRecuperacion;

                limpiarMensajeRecuperacion();

                if (!validaciones) {
                    mostrarMensajeRecuperacion("No se cargaron las validaciones. Recargue la página.");
                    return;
                }

                const reglas = esSolicitud ? validaciones.reglasSolicitarRecuperacion : reglasRecuperacion;
                const resultado = validaciones.validarFormulario(formulario[0], reglas);

                if (!resultado.valido) {
                    if (esSolicitud) mostrarPasoRecuperacion("solicitar");

                    resultado.primerCampo?.focus();
                    return;
                }

                const datos = {};

                formulario.serializeArray().forEach(function (campo) {
                    datos[campo.name] = campo.value;
                });

                modalAyudaClave.find(":input").each(function () {
                    estadosControles.push({ elemento: $(this), deshabilitado: this.disabled });
                });

                modalAyudaClave.attr("aria-busy", "true").find(":input").prop("disabled", true);
                botonEnvio.text(esSolicitud ? "Enviando..." : "Guardando...");

                respuesta = await solicitarAcceso(formulario.attr("action"), datos);

            } catch (error) {
                fallo = error;
                respuesta = error.respuesta;

            } finally {
                recuperacionOcupada = false;

                estadosControles.forEach(function (control) {
                    control.elemento.prop("disabled", control.deshabilitado);
                });

                modalAyudaClave.removeAttr("aria-busy");
                botonEnvio.text(textoBoton);
                actualizarEsperaReenvio();
            }

            actualizarTokenRecuperacion(respuesta);

            if (Number.isSafeInteger(respuesta?.reintentar_en)) iniciarEsperaReenvio(respuesta.reintentar_en);


            /* Respetar la sesión que pudo iniciarse en otra pestaña */
            if (respuesta?.ok === true && respuesta.redireccion === "Index.php?url=inicio") {
                recuperacionRedirigiendo = true;
                modalAyudaClave.find(":input").prop("disabled", true);
                window.location.assign("Index.php?url=inicio");
                return;
            }


            if (!fallo && respuesta?.ok === true && esSolicitud && respuesta.paso === "restablecer") {
                limpiarClavesRecuperacion();
                mostrarPasoRecuperacion("restablecer");
                mostrarMensajeRecuperacion(respuesta.mensaje || "Si los datos corresponden a una cuenta activa, recibirá un código.", "info");
                enfocarPaso();
                return;
            }


            if (!fallo && respuesta?.ok === true && !esSolicitud && respuesta.paso === "completado") {
                const cedula = formularioSolicitud.find("[name='cedula']").val();
                const mensaje = respuesta.mensaje || "La contraseña fue actualizada. Inicie sesión con su nueva contraseña.";

                cerrarAyuda();
                formularioSolicitud[0].reset();
                formularioSolicitud.find("[name='cedula'], [name='correo']").val("");
                validacionesRecuperacion.limpiarFormulario(formularioSolicitud[0]);
                mostrarPasoRecuperacion("solicitar");
                campoClave.val("").prop("type", "password");
                botonMostrarClave.attr({ "aria-label": "Mostrar contraseña", title: "Mostrar contraseña", "aria-pressed": "false" });
                ojoCerrado.show();
                ojoAbierto.hide();
                $("#usuario").val(cedula).trigger("focus");
                $("#formLogin").closest(".login-content").find(".login-message")
                    .text(mensaje).attr("data-tipo-aviso", "success").prop("hidden", false);
                window.Alertas?.notificar(mensaje, "success");
                return;
            }


            /* Una respuesta perdida no permite saber si la operación terminó */
            const ambiguo = fallo?.ambiguo === true || (!fallo && respuesta?.ok !== false);
            const mensaje = ambiguo
                ? esSolicitud
                    ? "No se pudo confirmar el envío. Espere un minuto y revise su correo antes de solicitar otro código."
                    : "No se pudo confirmar el cambio. Intente iniciar sesión con la nueva contraseña antes de repetir la operación."
                : typeof respuesta?.mensaje === "string"
                    ? respuesta.mensaje : fallo?.message || "No fue posible completar la operación. Intente nuevamente.";

            if (ambiguo && esSolicitud) iniciarEsperaReenvio(60);

            presentarErrorRecuperacion(respuesta, mensaje, ambiguo);
        }


        formularioSolicitud.on("submit", function (evento) {
            evento.preventDefault();
            enviarRecuperacion(true);
        });

        formularioRecuperacion.on("submit", function (evento) {
            evento.preventDefault();
            enviarRecuperacion(false);
        });

        botonReenviarCodigo.on("click", function () {
            enviarRecuperacion(true);
        });


        /* Recuperar el paso presentado por PHP después de un envío sin AJAX */
        mostrarPasoRecuperacion(modalAyudaClave.attr("data-paso-recuperacion"));
        iniciarEsperaReenvio(Number(modalAyudaClave.attr("data-reintentar-en")) || 0);

        if (modalAyudaClave.attr("data-mostrar-inicial") === "1") {
            modalAyudaClave.attr("data-mostrar-inicial", "0");
            abrirAyuda();
        }
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