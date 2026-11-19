/* ===================================================== */
/* COMPORTAMIENTO GENERAL DEL SISTEMA */
/* ===================================================== */

$(function () {
    "use strict";

    /* ================================================= */
    /* EFECTO DOCK / NAVBAR */
    /* ================================================= */

    const dockMenu = $("#dockMenu");

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    if (dockMenu.length) {
        const dockItems = dockMenu.find(".dock-item");


        /* Aplicar el efecto según la distancia del cursor */
        function applyDockEffect(mouseX) {
            dockItems.each(function () {
                const item = $(this);
                const rect = this.getBoundingClientRect();
                const itemCenter = rect.left + rect.width / 2;
                const distance = Math.abs(mouseX - itemCenter);

                const maxDistance = 170;
                const influence = clamp(1 - distance / maxDistance, 0, 1);

                const scale = 1 + influence * 0.34;
                const lift = influence * 12;
                const labelLift = influence * 2;

                const baseY = String(item.css("--base-y") || "").trim() || "0px";

                item.css("transform", `translateY(calc(${baseY} - ${lift}px))`);

                item.find(".icon").first().css("transform", `scale(${scale})`);

                item.find(".label").first().css({
                    transform: `translateY(${-labelLift}px) scale(${1 + influence * 0.04})`,
                    opacity: 0.86 + influence * 0.14
                });
            });
        }


        /* Recuperar la posición y el tamaño originales */
        function resetDockEffect() {
            dockItems.each(function () {
                const item = $(this);

                const baseY = String(item.css("--base-y") || "").trim() || "0px";

                item.css("transform", `translateY(${baseY})`);

                item.find(".icon").first().css("transform", "scale(1)");

                item.find(".label").first().css({
                    transform: "translateY(0) scale(1)",
                    opacity: 1
                });
            });
        }


        /* Actualizar el efecto al mover el cursor */
        dockMenu.on("mousemove", function (evento) {
            applyDockEffect(evento.clientX);
        });


        /* Restablecer el efecto al salir de la navegación */
        dockMenu.on("mouseleave", resetDockEffect);
    }


    /* ================================================= */
    /* ELEMENTOS DEL PERFIL */
    /* ================================================= */

    const formularioPerfil = $("#formPerfil");
    const formularioClave = $("#formCambiarClave");
    const modalPerfil = $("#modalPerfil");
    const modalCambiarClave = $("#modalCambiarClave");
    const inputFotoPerfil = $("#inputFotoPerfil");
    const previewFoto = $("#previewFoto");
    const botonesClavePerfil = $(".btnAlternarClavePerfil");
    const imagenPredeterminada = "Assets/img/LogoTR.png";

    let fotoGuardada = previewFoto.attr("src") || imagenPredeterminada;
    let lectorFoto = null;
    let versionFoto = 0;
    let enviando = false;
    let preparandoEnvio = false;
    let formularioEnviado = null;
    let redirigiendo = false;
    let validacionesCompartidas = null;
    let reglasPerfil = null;
    let reglasClave = null;


    /* Utilizar los mensajes de las validaciones compartidas */
    formularioPerfil.add(formularioClave).prop("noValidate", true);


    /* ================================================= */
    /* AVISOS DE LOS FORMULARIOS */
    /* ================================================= */

    function limpiarAviso(formulario) {
        formulario.find("#mensajePerfil, #mensajeCambioClave")
            .addClass("d-none")
            .removeClass("alert-success alert-warning")
            .addClass("alert-danger")
            .text("");
    }


    function limpiarValidaciones(formulario) {
        if (validacionesCompartidas && formulario.length) {
            validacionesCompartidas.limpiarFormulario(formulario[0]);
        }

        formulario.find("[aria-invalid]").removeAttr("aria-invalid");
    }


    function mostrarAviso(formulario, mensaje, campo, tipo = "danger") {
        const aviso = formulario.find("#mensajePerfil, #mensajeCambioClave");

        aviso.removeClass("d-none alert-danger alert-success alert-warning")
            .addClass("alert-" + tipo)
            .text(mensaje);

        if (typeof campo === "string") {
            formulario.find(":input[name]").filter(function () {
                return $(this).attr("name") === campo;
            }).attr("aria-invalid", "true");
        }

        formulario.find(".modal-body").scrollTop(0);
        formulario.closest(".modal").scrollTop(0);

        aviso.trigger("focus");
    }


    /* Mostrar los errores enviados por el modelo junto a sus campos */
    function mostrarRechazo(formulario, respuesta, mensaje) {
        window.Alertas?.rechazo(mensaje, respuesta, formulario);

        if (
            validacionesCompartidas &&
            formulario.length &&
            respuesta &&
            $.isPlainObject(respuesta.errores)
        ) {
            validacionesCompartidas.mostrarErrores(
                formulario[0],
                respuesta.errores
            );
        }

        mostrarAviso(
            formulario,
            mensaje,
            respuesta && respuesta.campo,
            respuesta?.ambiguo ? "warning" : "danger"
        );
    }


    /* ================================================= */
    /* VALIDACIONES COMPARTIDAS DEL PERFIL */
    /* ================================================= */

    function prepararValidaciones() {
        if (!formularioPerfil.length && !formularioClave.length) {
            return Promise.resolve(null);
        }

        return import("./validaciones/validaciones.js")
            .then(function (modulo) {
                const validaciones = modulo.Validaciones;


                /* Reutilizar las reglas de los datos personales */
                reglasPerfil = Object.freeze({
                    nickname: validaciones.reglasUsuarios.nickname,
                    telefono: validaciones.reglasClientes.telefono,
                    correo: validaciones.reglasClientes.correo
                });


                /* Comprobar las contraseñas sin eliminar sus espacios */
                reglasClave = Object.freeze({
                    clave_actual: {
                        comprobar: function (valor) {
                            return typeof valor !== "string" || valor === ""
                                ? "Ingrese su contraseña actual."
                                : null;
                        }
                    },

                    nueva_clave: {
                        comprobar: function (valor) {
                            if (typeof valor !== "string" || valor === "") {
                                return "Ingrese la nueva contraseña.";
                            }

                            if (validaciones.validarClave(valor)) {
                                return null;
                            }

                            if (new TextEncoder().encode(valor).length > 72) {
                                return "La contraseña es demasiado larga. Utilice una más corta.";
                            }

                            return "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo, sin espacios.";
                        }
                    },

                    confirmar_clave: {
                        comprobar: function (valor) {
                            if (typeof valor !== "string" || valor === "") {
                                return "Confirme la nueva contraseña.";
                            }

                            const nuevaClave = formularioClave
                                .find("[name='nueva_clave']")
                                .val();

                            return valor !== nuevaClave
                                ? "Las contraseñas no coinciden."
                                : null;
                        }
                    }
                });


                /* Mantener los límites de los campos acordes con el modelo */
                formularioPerfil.find("[name='nickname']").prop("maxLength", 50);
                formularioPerfil.find("[name='telefono']").prop("maxLength", 25);
                formularioPerfil.find("[name='correo']").prop("maxLength", 100);


                /* Retirar mensajes nativos anteriores de confirmación */
                formularioClave.find("[name='confirmar_clave']").each(function () {
                    this.setCustomValidity("");
                });


                /* Activar los mensajes compartidos en ambos formularios */
                if (formularioPerfil.length) {
                    validaciones.activar(formularioPerfil[0], reglasPerfil);
                }

                if (formularioClave.length) {
                    validaciones.activar(formularioClave[0], reglasClave);
                }

                validacionesCompartidas = validaciones;

                return validaciones;

            }).catch(function () {
                return null;
            });
    }


    const cargaValidaciones = prepararValidaciones();


    /* Comprobar el archivo antes de previsualizarlo o enviarlo */
    function validarFotoSeleccionada() {
        const archivos = inputFotoPerfil.prop("files");
        const archivo = archivos && archivos[0];

        if (!archivo) {
            return null;
        }

        const tiposPermitidos = ["image/jpeg", "image/png", "image/webp"];
        const tamanoMaximo = 2 * 1024 * 1024;

        if (archivo.type && !tiposPermitidos.includes(archivo.type)) {
            return "Seleccione una imagen JPEG, PNG o WebP.";
        }

        if (archivo.size <= 0 || archivo.size > tamanoMaximo) {
            return "Seleccione una fotografía de hasta 2 MB.";
        }

        return null;
    }


    /* Validar los campos con las reglas correspondientes */
    function validarFormularioPerfil(formulario, esPerfil) {
        const reglas = esPerfil ? reglasPerfil : reglasClave;

        limpiarValidaciones(formulario);

        const resultado = validacionesCompartidas.validarFormulario(
            formulario[0],
            reglas
        );

        if (esPerfil) {
            const errorFoto = validarFotoSeleccionada();

            if (errorFoto) {
                validacionesCompartidas.mostrarErrores(
                    formulario[0],
                    { foto: errorFoto }
                );

                resultado.valido = false;
                resultado.primerCampo = resultado.primerCampo || inputFotoPerfil[0];

                mostrarAviso(formulario, errorFoto, "foto");
            }
        }

        return resultado;
    }


    /* ================================================= */
    /* PREVISUALIZACIÓN DE LA FOTOGRAFÍA */
    /* ================================================= */

    function cancelarLecturaFoto() {
        versionFoto++;

        if (lectorFoto && lectorFoto.readyState === FileReader.LOADING) {
            lectorFoto.abort();
        }

        lectorFoto = null;
    }


    function restaurarFotoGuardada() {
        cancelarLecturaFoto();

        inputFotoPerfil.val("");
        previewFoto.attr("src", fotoGuardada);
    }


    function mostrarErrorFoto(mensaje) {
        restaurarFotoGuardada();

        if (validacionesCompartidas && formularioPerfil.length) {
            validacionesCompartidas.mostrarErrores(
                formularioPerfil[0],
                { foto: mensaje }
            );
        }

        mostrarAviso(formularioPerfil, mensaje, "foto");
    }


    if (inputFotoPerfil.length && previewFoto.length) {

        /* Abrir el selector desde la fotografía */
        previewFoto.on("click", function () {
            if (!enviando && !preparandoEnvio) {
                inputFotoPerfil.trigger("click");
            }
        });


        /* Leer la imagen sin modificar la fotografía de la cabecera */
        inputFotoPerfil.on("change", function () {
            cancelarLecturaFoto();
            limpiarAviso(formularioPerfil);

            inputFotoPerfil.removeAttr("aria-invalid")
                .next(".templo-validacion-feedback")
                .remove();

            const archivos = inputFotoPerfil.prop("files");
            const archivo = archivos && archivos[0];
            const versionActual = versionFoto;

            if (!archivo) {
                previewFoto.attr("src", fotoGuardada);
                return;
            }

            const errorFoto = validarFotoSeleccionada();

            if (errorFoto) {
                mostrarErrorFoto(errorFoto);
                return;
            }

            const lector = new FileReader();

            lectorFoto = lector;


            /* Mostrar únicamente la selección más reciente */
            lector.onload = function (evento) {
                if (versionActual !== versionFoto || inputFotoPerfil.prop("files")[0] !== archivo) {
                    return;
                }

                previewFoto.attr("src", evento.target.result);
                lectorFoto = null;
            };


            /* Recuperar la imagen guardada cuando falle la lectura */
            lector.onerror = function () {
                if (versionActual !== versionFoto) {
                    return;
                }

                mostrarErrorFoto(
                    "No fue posible leer la fotografía. Selecciónela nuevamente."
                );
            };

            try {
                lector.readAsDataURL(archivo);

            } catch (error) {
                mostrarErrorFoto(
                    "No fue posible leer la fotografía. Selecciónela nuevamente."
                );
            }
        });
    }


    /* ================================================= */
    /* VISIBILIDAD Y CONFIRMACIÓN DE LAS CONTRASEÑAS */
    /* ================================================= */

    function actualizarVisibilidadClave(boton, visible) {
        const idCampo = boton.attr("data-password-target");
        const campo = idCampo ? $("#" + $.escapeSelector(idCampo)) : $();

        if (!campo.length) {
            return;
        }

        const descripcion = boton.data("descripcionClave") || "contraseña";
        const texto = (visible ? "Ocultar " : "Mostrar ") + descripcion;

        campo.prop("type", visible ? "text" : "password");

        boton.find(".templo-eye-closed").toggle(!visible);
        boton.find(".templo-eye-open").toggle(visible);

        boton.attr({
            "aria-label": texto,
            "title": texto,
            "aria-pressed": String(visible)
        });
    }


    /* Conservar la descripción particular de cada botón */
    botonesClavePerfil.each(function () {
        const boton = $(this);

        const descripcion = (boton.attr("aria-label") || "Mostrar contraseña")
            .replace(/^(Mostrar|Ocultar)\s+/i, "");

        boton.data("descripcionClave", descripcion);

        actualizarVisibilidadClave(boton, false);
    });


    /* Alternar la visibilidad del campo asociado */
    botonesClavePerfil.on("click", function () {
        const boton = $(this);
        const idCampo = boton.attr("data-password-target");
        const campo = idCampo ? $("#" + $.escapeSelector(idCampo)) : $();

        actualizarVisibilidadClave(boton, campo.prop("type") === "password");
    });


    /* Revisar una confirmación ya validada cuando cambie la nueva contraseña */
    formularioClave.find("[name='nueva_clave']").on("input", function () {
        const confirmarClave = formularioClave
            .find("[name='confirmar_clave']")
            .first();

        if (validacionesCompartidas && confirmarClave.length) {
            confirmarClave[0].dispatchEvent(
                new Event("input", { bubbles: true })
            );
        }
    });


    /* Limpiar los campos y recuperar la visibilidad inicial */
    function limpiarFormularioClave() {
        if (formularioClave.length) {
            formularioClave[0].reset();
        }

        limpiarAviso(formularioClave);
        limpiarValidaciones(formularioClave);

        botonesClavePerfil.each(function () {
            actualizarVisibilidadClave($(this), false);
        });
    }


    /* ================================================= */
    /* ACTUALIZACIÓN DE LOS DATOS GUARDADOS */
    /* ================================================= */

    function actualizarToken(formulario, respuesta) {
        if (!respuesta || typeof respuesta.token_csrf !== "string") {
            return;
        }

        formulario.find("[name='token_csrf']")
            .val(respuesta.token_csrf)
            .prop("defaultValue", respuesta.token_csrf);
    }


    function actualizarPerfilGuardado(usuario) {

        /* Actualizar los valores que se recuperarán al reiniciar el formulario */
        ["nickname", "telefono", "correo"].forEach(function (nombre) {
            const valor = String(usuario[nombre] ?? "");

            formularioPerfil.find("[name='" + nombre + "']")
                .val(valor)
                .prop("defaultValue", valor);
        });


        /* Determinar el nombre visible del usuario */
        const nickname = String(usuario.nickname ?? "").trim();
        const nombreCompleto = [usuario.nombre, usuario.apellido].filter(Boolean).join(" ").trim();
        const nombreVisible = nickname || nombreCompleto || "Usuario";

        fotoGuardada = String(usuario.foto_perfil ?? "").trim() || imagenPredeterminada;

        restaurarFotoGuardada();


        /* Actualizar la cabecera después de confirmar el guardado */
        $(".user-session-photo").first().attr("src", fotoGuardada);
        $(".user-session-name").first().text(nombreVisible);

        limpiarValidaciones(formularioPerfil);
    }


    /* ================================================= */
    /* CIERRE Y RESULTADOS DE LOS MODALES */
    /* ================================================= */

    modalPerfil.add(modalCambiarClave)
        .on("show.bs.modal", function () {
            $(this).data("abriendo", true);

        }).on("shown.bs.modal", function () {
            $(this).data("abriendo", false);

        }).on("hide.bs.modal", function (evento) {

            /* Evitar cerrar el formulario mientras se procesa su envío */
            if (enviando && formularioEnviado && formularioEnviado.closest(".modal").is(this)) {
                evento.preventDefault();
            }
        });


    /* Recuperar los datos guardados al cerrar el perfil */
    modalPerfil.on("hidden.bs.modal", function () {
        if (formularioPerfil.length) {
            formularioPerfil[0].reset();
        }

        restaurarFotoGuardada();
        limpiarAviso(formularioPerfil);
        limpiarValidaciones(formularioPerfil);
    });


    /* Limpiar el formulario al cerrar el cambio de contraseña */
    modalCambiarClave.on("hidden.bs.modal", limpiarFormularioClave);


    function mostrarResultado(formulario, respuesta, esPerfil) {
        const modalOrigen = formulario.closest(".modal");
        const modalResultado = $(esPerfil ? "#modalResultadoPerfil" : "#modalResultadoCambioClave");
        const titulo = esPerfil ? "Perfil actualizado" : "Contraseña actualizada";

        if (!window.bootstrap || !window.bootstrap.Modal || !modalResultado.length) {
            mostrarAviso(formulario, respuesta.mensaje, null, "success");
            return;
        }

        modalResultado.find(".modal-title").text(titulo);

        modalResultado.find(".seguridad-resultado-mensaje")
            .text(respuesta.mensaje)
            .attr("role", "status");

        modalResultado.attr("data-mostrar-inicial", "0");

        const instanciaResultado = window.bootstrap.Modal.getOrCreateInstance(modalResultado[0]);

        function presentarResultado() {
            if (window.Alertas?.disponible()) {
                window.Alertas.notificar(respuesta.mensaje, "success", { titulo })
                    .then(function (mostrado) {
                        if (!mostrado) instanciaResultado.show();
                    });
            } else {
                instanciaResultado.show();
            }
        }


        /* Esperar el cierre del formulario antes de mostrar el resultado */
        function cerrarYMostrar() {
            if (modalOrigen.hasClass("show")) {
                modalOrigen.one("hidden.bs.modal", function () {
                    presentarResultado();
                });

                window.bootstrap.Modal.getOrCreateInstance(modalOrigen[0]).hide();

            } else {
                presentarResultado();
            }
        }


        /* Respetar la transición de apertura del formulario */
        if (modalOrigen.data("abriendo")) {
            modalOrigen.one("shown.bs.modal", cerrarYMostrar);
        } else {
            cerrarYMostrar();
        }
    }


    /* Mostrar los resultados recibidos mediante una redirección */
    const resultadosIniciales = $("#modalResultadoPerfil, #modalResultadoCambioClave")
        .filter('[data-mostrar-inicial="1"]')
        .toArray();


    function mostrarSiguienteResultado() {
        if (!resultadosIniciales.length || !window.bootstrap || !window.bootstrap.Modal) {
            return;
        }

        const elemento = resultadosIniciales.shift();

        if (window.Alertas?.disponible()) {
            $(elemento).attr("data-mostrar-inicial", "0");

            window.Alertas.desdeModal(elemento).then(function (mostrado) {
                if (mostrado) {
                    mostrarSiguienteResultado();
                } else {
                    $(elemento).one("hidden.bs.modal", mostrarSiguienteResultado);
                    window.bootstrap.Modal.getOrCreateInstance(elemento).show();
                }
            });

            return;
        }

        $(elemento).attr("data-mostrar-inicial", "0")
            .one("hidden.bs.modal", mostrarSiguienteResultado);

        window.bootstrap.Modal.getOrCreateInstance(elemento).show();
    }


    mostrarSiguienteResultado();


    /* ================================================= */
    /* SOLICITUDES DEL PERFIL */
    /* ================================================= */

    function solicitarPerfil(opciones) {
        return new Promise(function (resolve, reject) {
            $.ajax($.extend({}, opciones, {
                success: function (respuesta) {
                    resolve(respuesta);
                },

                error: function (solicitud, estado) {
                    const respuesta = solicitud.responseJSON;
                    const sinConfirmacion = solicitud.status === 0 ||
                        estado === "timeout" || estado === "parsererror";

                    const mensaje = respuesta && typeof respuesta.mensaje === "string"
                        ? respuesta.mensaje
                        : sinConfirmacion
                            ? "No se pudo confirmar el resultado. Compruebe los datos guardados antes de repetir la operación."
                            : "No fue posible completar la operación. Intente nuevamente.";

                    const error = new Error(mensaje);

                    error.status = solicitud.status;
                    error.respuesta = respuesta;
                    error.ambiguo = sinConfirmacion;

                    reject(error);
                }
            }));
        });
    }


    /* ================================================= */
    /* ENVÍO DE LOS FORMULARIOS DEL PERFIL */
    /* ================================================= */

    formularioPerfil.add(formularioClave).on("submit", async function (evento) {
        evento.preventDefault();

        if (enviando || preparandoEnvio || redirigiendo) {
            return;
        }

        const formulario = $(this);
        const esPerfil = formulario.attr("id") === "formPerfil";
        const modalOrigen = formulario.closest(".modal");
        const estabaAbierto = modalOrigen.hasClass("show");

        preparandoEnvio = true;

        let validaciones = null;

        try {
            validaciones = await cargaValidaciones;

        } finally {
            preparandoEnvio = false;
        }


        /* Respetar el cierre del formulario durante la carga inicial */
        if (estabaAbierto && !modalOrigen.hasClass("show")) {
            return;
        }

        limpiarAviso(formulario);


        /* Evitar el envío si no se pudieron cargar las reglas compartidas */
        if (!validaciones) {
            mostrarAviso(
                formulario,
                "No fue posible cargar las validaciones. Recargue la página antes de continuar.",
                null,
                "warning"
            );

            return;
        }

        const resultado = validarFormularioPerfil(formulario, esPerfil);

        if (!resultado.valido) {
            if (resultado.primerCampo && resultado.primerCampo.type !== "hidden") {
                resultado.primerCampo.focus();
            }

            return;
        }

        const opciones = {
            url: formulario.attr("action"),
            type: "POST",
            dataType: "json",
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            },
            processData: false,
            timeout: 30000
        };


        /* Enviar la fotografía y los campos mediante FormData */
        if (esPerfil) {
            opciones.data = new FormData(formulario[0]);
            opciones.contentType = false;

        } else {
            const datos = {};

            formulario.serializeArray().forEach(function (campo) {
                datos[campo.name] = campo.value;
            });

            opciones.data = JSON.stringify(datos);
            opciones.contentType = "application/json; charset=utf-8";
        }


        /* Conservar el estado de los controles antes de bloquearlos */
        const controles = formulario.closest(".modal").find(":input");
        const estadosControles = [];

        let respuestaExitosa = null;
        let respuestaRechazada = null;
        let mensajeError = null;

        controles.each(function () {
            const elemento = $(this);

            estadosControles.push({
                elemento,
                deshabilitado: elemento.prop("disabled")
            });
        });


        /* Impedir envíos repetidos durante la operación */
        enviando = true;
        formularioEnviado = formulario;

        formulario.attr("aria-busy", "true");
        controles.prop("disabled", true);

        try {
            const respuesta = await solicitarPerfil(opciones);

            actualizarToken(formulario, respuesta);


            /* Conservar los errores sin borrar los datos del formulario */
            if (!respuesta || respuesta.ok !== true) {
                respuestaRechazada = respuesta;

                mensajeError = respuesta && typeof respuesta.mensaje === "string"
                    ? respuesta.mensaje
                    : "No fue posible completar la operación. Intente nuevamente.";

            } else {
                respuestaExitosa = respuesta;
            }

        } catch (error) {
            const respuesta = error.respuesta;

            actualizarToken(formulario, respuesta);


            /* Volver al acceso cuando ya no exista una sesión válida */
            if (error.status === 401) {
                redirigiendo = true;
                window.location.assign("Index.php?url=login");

                return;
            }

            respuestaRechazada = {
                ...respuesta,
                status: error.status,
                ambiguo: error.ambiguo === true
            };

            mensajeError = error.message ||
                "No fue posible completar la operación. Intente nuevamente.";

        } finally {
            if (!redirigiendo) {

                /* Recuperar el estado original de los controles */
                estadosControles.forEach(function (control) {
                    control.elemento.prop("disabled", control.deshabilitado);
                });

                formulario.removeAttr("aria-busy");

                enviando = false;
                formularioEnviado = null;
            }
        }


        /* Mostrar los errores después de recuperar los controles */
        if (mensajeError && !redirigiendo) {
            mostrarRechazo(formulario, respuestaRechazada, mensajeError);
            return;
        }


        /* Presentar el resultado después de recuperar los controles */
        if (respuestaExitosa && !redirigiendo) {
            try {
                if (esPerfil && $.isPlainObject(respuestaExitosa.usuario)) {
                    actualizarPerfilGuardado(respuestaExitosa.usuario);
                }

                if (!esPerfil) {
                    limpiarFormularioClave();
                }

                mostrarResultado(formulario, respuestaExitosa, esPerfil);

            } catch (error) {
                const mensaje = "La operación fue confirmada, pero no se pudo actualizar la vista. Recargue la página para ver los datos guardados.";

                mostrarAviso(formulario, mensaje, null, "warning");
                window.Alertas?.notificar(mensaje, "warning");
            }
        }
    });

});