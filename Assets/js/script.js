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
    let formularioEnviado = null;
    let redirigiendo = false;


    /* ================================================= */
    /* AVISOS DE LOS FORMULARIOS */
    /* ================================================= */

    function limpiarAviso(formulario) {
        formulario.find("#mensajePerfil, #mensajeCambioClave")
            .addClass("d-none")
            .removeClass("alert-success alert-warning")
            .addClass("alert-danger")
            .text("");

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


    if (inputFotoPerfil.length && previewFoto.length) {

        /* Abrir el selector desde la fotografía */
        previewFoto.on("click", function () {
            if (!enviando) {
                inputFotoPerfil.trigger("click");
            }
        });


        /* Leer la imagen sin modificar la fotografía de la cabecera */
        inputFotoPerfil.on("change", function () {
            cancelarLecturaFoto();
            limpiarAviso(formularioPerfil);

            const archivo = inputFotoPerfil.prop("files")[0];
            const versionActual = versionFoto;

            if (!archivo) {
                previewFoto.attr("src", fotoGuardada);
                return;
            }

            const tiposPermitidos = ["image/jpeg", "image/png", "image/webp"];
            const tamanoMaximo = 2 * 1024 * 1024;


            /* Comprobar el formato informado por el navegador */
            if (archivo.type && !tiposPermitidos.includes(archivo.type)) {
                restaurarFotoGuardada();

                mostrarAviso(
                    formularioPerfil,
                    "Seleccione una imagen JPEG, PNG o WebP.",
                    "foto"
                );

                return;
            }


            /* Comprobar el tamaño antes de leer la fotografía */
            if (archivo.size <= 0 || archivo.size > tamanoMaximo) {
                restaurarFotoGuardada();

                mostrarAviso(
                    formularioPerfil,
                    "Seleccione una fotografía de hasta 2 MB.",
                    "foto"
                );

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

                restaurarFotoGuardada();

                mostrarAviso(
                    formularioPerfil,
                    "No fue posible leer la fotografía. Selecciónela nuevamente.",
                    "foto"
                );
            };

            lector.readAsDataURL(archivo);
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


    /* Comprobar la coincidencia sin modificar las contraseñas */
    function validarCoincidenciaClave() {
        const nuevaClave = formularioClave.find("[name='nueva_clave']").first();
        const confirmarClave = formularioClave.find("[name='confirmar_clave']").first();

        if (!nuevaClave.length || !confirmarClave.length) {
            return;
        }

        const noCoinciden = confirmarClave.val() !== "" && nuevaClave.val() !== confirmarClave.val();

        confirmarClave[0].setCustomValidity(
            noCoinciden ? "Las contraseñas no coinciden." : ""
        );
    }


    formularioClave.find("[name='nueva_clave'], [name='confirmar_clave']")
        .on("input", validarCoincidenciaClave);


    /* Limpiar los campos y recuperar la visibilidad inicial */
    function limpiarFormularioClave() {
        if (formularioClave.length) {
            formularioClave[0].reset();
        }

        validarCoincidenciaClave();
        limpiarAviso(formularioClave);

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


        /* Esperar el cierre del formulario antes de mostrar el resultado */
        function cerrarYMostrar() {
            if (modalOrigen.hasClass("show")) {
                modalOrigen.one("hidden.bs.modal", function () {
                    instanciaResultado.show();
                });

                window.bootstrap.Modal.getOrCreateInstance(modalOrigen[0]).hide();

            } else {
                instanciaResultado.show();
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

        if (enviando || redirigiendo) {
            return;
        }

        const formulario = $(this);
        const esPerfil = formulario.attr("id") === "formPerfil";


        /* Comprobar la confirmación antes de enviar la contraseña */
        if (!esPerfil) {
            validarCoincidenciaClave();
        }

        if (!this.reportValidity()) {
            return;
        }

        limpiarAviso(formulario);

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


            /* Mantener los datos cuando el controlador rechace la solicitud */
            if (!respuesta || respuesta.ok !== true) {
                mostrarAviso(
                    formulario,
                    respuesta && respuesta.mensaje ||
                        "No fue posible completar la operación. Intente nuevamente.",
                    respuesta && respuesta.campo
                );

                return;
            }

            respuestaExitosa = respuesta;

        } catch (error) {
            const respuesta = error.respuesta;

            actualizarToken(formulario, respuesta);


            /* Volver al acceso cuando ya no exista una sesión válida */
            if (error.status === 401) {
                redirigiendo = true;
                window.location.assign("Index.php?url=login");

                return;
            }

            mostrarAviso(
                formulario,
                error.message || "No fue posible completar la operación. Intente nuevamente.",
                respuesta && respuesta.campo
            );

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
                mostrarAviso(
                    formulario,
                    "La operación fue confirmada, pero no se pudo actualizar la vista. Recargue la página para ver los datos guardados.",
                    null,
                    "warning"
                );
            }
        }
    });

});