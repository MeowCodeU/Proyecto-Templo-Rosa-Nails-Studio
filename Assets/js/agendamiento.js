/* ===================================================== */
/* GESTIÓN DE AGENDAMIENTO */
/* ===================================================== */

$(function () {
    "use strict";

    /* Formularios de citas */
    const $formNuevo = $("#formNuevoAgendamiento");
    const $formEditar = $("#formEditarAgendamiento");
    const $tipoNuevo = $("#nuevoTipoAgendamiento");
    const $tipoEditar = $("#editarTipoAgendamiento");
    const $estadoNuevo = $("#nuevoEstadoAgendamiento");
    const $clienteNuevo = $("#nuevoCliente");
    const $clienteEditar = $("#editarClienteAgendamiento");
    const $montoNuevo = $("#nuevoMontoTotal");
    const $montoEditar = $("#editarMontoTotal");
    const $duracionNueva = $("#nuevaDuracionAgendamiento");
    const $duracionEditar = $("#editarDuracionAgendamiento");
    const $fechaNueva = $("#nuevaFechaAgendamiento");
    const $fechaEditar = $("#editarFechaAgendamiento");
    const $modalNuevo = $("#modalNuevoAgendamiento");
    const $modalEditar = $("#modalEditarAgendamiento");

    /* Formularios de bloqueos */
    const $formNuevoBloqueo = $("#formNuevoBloqueoHorario");
    const $formEditarBloqueo = $("#formEditarBloqueoHorario");
    const $fechaNuevoBloqueo = $("#bloqueoFecha");
    const $fechaEditarBloqueo = $("#editarBloqueoFecha");
    const $horaInicioNuevoBloqueo = $("#bloqueoHoraInicio");
    const $horaFinNuevoBloqueo = $("#bloqueoHoraFin");
    const $horaInicioEditarBloqueo = $("#editarBloqueoHoraInicio");
    const $horaFinEditarBloqueo = $("#editarBloqueoHoraFin");
    const $modalNuevoBloqueo = $("#modalNuevoBloqueoHorario");
    const $modalEditarBloqueo = $("#modalEditarBloqueoHorario");

    const urlAgendamiento = "Index.php?url=agendamiento";
    const $modalMensaje = $("#modalMensajeAgendamiento");
    const citasProtegidas = new Map();
    const reglasFormularios = new WeakMap();

    let operacionEnCurso = false;
    let modalPermitidoCerrar = null;
    let redirigiendo = false;
    let validacionesAgenda = null;

    /* Cargar las reglas compartidas conservando los scripts actuales de la vista */
    const cargaValidaciones = import("./validaciones/validaciones.js")
        .then(function (modulo) {
            validacionesAgenda = modulo.Validaciones;

            $formNuevo.add($formEditar).add($formNuevoBloqueo).add($formEditarBloqueo).each(function () {
                validacionesAgenda.activar(this, obtenerReglasFormulario($(this)));
            });

            return validacionesAgenda;
        })
        .catch(function () {
            return null;
        });


    /* ================================================= */
    /* MENSAJES DEL MÓDULO */
    /* ================================================= */

    function ocultarAviso(contenedor) {
        $(contenedor).attr("class", "alert agendamiento-aviso d-none mb-0").empty();
    }

    function mostrarAviso(contenedor, tipo, mensaje, icono) {
        const $contenedor = $(contenedor);

        if (!$contenedor.length) return;

        /* Mostrar los rechazos de una confirmación que SweetAlert2 sustituyó */
        const $confirmacion = $contenedor.closest("[data-alerta-confirmacion]");

        if ($confirmacion.length && !$confirmacion.hasClass("show") && window.Alertas?.disponible()) {
            if (tipo !== "info") {
                window.Alertas.notificar(mensaje, tipo, { titulo: "No se pudo completar la operación" });
            }

            return;
        }

        const $icono = $("<i>", {
            class: `bi ${icono} me-2 flex-shrink-0`,
            "aria-hidden": "true"
        });

        const $texto = $("<span>").text(mensaje);

        $contenedor
            .attr("class", `alert agendamiento-aviso alert-${tipo} d-flex align-items-center mb-0`)
            .attr("role", "status")
            .empty()
            .append($icono, $texto);
    }

    function enfocarAviso(contenedor) {
        const $contenedor = $(contenedor);

        if (!$contenedor.length) return;
        if ($contenedor.closest("[data-alerta-confirmacion]:not(.show)").length) return;

        $contenedor.attr("tabindex", "-1");

        $contenedor.get(0).focus({
            preventScroll: true
        });

        $contenedor.get(0).scrollIntoView({
            behavior: "smooth",
            block: "nearest"
        });
    }

    function redirigirAlAcceso() {
        if (redirigiendo) return;

        redirigiendo = true;
        window.location.assign("Index.php?url=login&sesion=expirada");
    }

    function mostrarResultado(titulo, mensaje, tipo = "success") {
        if (window.Alertas?.disponible()) {
            window.Alertas.notificar(mensaje, tipo, { titulo });
            return;
        }

        if (!$modalMensaje.length || !window.bootstrap) return;

        $modalMensaje.find(".modal-title").text(titulo);
        $modalMensaje.find(".modal-body p").text(mensaje);

        $modalMensaje.find(".modal-body i").attr(
            "class",
            tipo === "success"
                ? "bi bi-check-circle-fill"
                : "bi bi-exclamation-triangle-fill"
        );

        bootstrap.Modal.getOrCreateInstance($modalMensaje.get(0)).show();
    }

    function cerrarModal(modal) {
        const $modal = $(modal);

        return new Promise(function (resolver) {
            if (!$modal.length || !window.bootstrap || !$modal.hasClass("show")) {
                resolver();
                return;
            }

            modalPermitidoCerrar = $modal.get(0);
            $modal.one("hidden.bs.modal", resolver);

            function cerrar() {
                bootstrap.Modal.getOrCreateInstance($modal.get(0)).hide();
            }

            if ($modal.attr("data-abriendo-agendamiento") === "1") {
                $modal.one("shown.bs.modal", cerrar);
            } else {
                cerrar();
            }
        });
    }


    /* ================================================= */
    /* PROTECCIÓN DE LAS ATENCIONES */
    /* ================================================= */

    function evaluarGestionCita(evento) {
        const id = String(evento?.id || "");
        const propiedades = evento?.extendedProps || {};

        if (citasProtegidas.has(id)) {
            return {
                permitida: false,
                mensaje: citasProtegidas.get(id)
            };
        }

        const permitida = Boolean(id) &&
            !propiedades.es_bloqueo &&
            propiedades.gestion_permitida === true &&
            propiedades.tiene_registro_servicio !== true;

        return {
            permitida: permitida,
            mensaje: permitida
                ? ""
                : propiedades.motivo_bloqueo ||
                    "La cita no permite modificaciones ni cancelaciones. Actualice el calendario."
        };
    }

    function obtenerGestionCita(idAgendamiento) {
        const id = String(idAgendamiento || "");
        let evento = null;

        if (typeof window.AgendamientoCalendario?.obtenerEvento === "function") {
            evento = window.AgendamientoCalendario.obtenerEvento(id);

        } else if (Array.isArray(window.agendamientosCalendario)) {
            evento = window.agendamientosCalendario.find(function (cita) {
                return String(cita.id) === id;
            });
        }

        /* Conservar el rechazo confirmado aunque la información visible sea anterior */
        if (citasProtegidas.has(id)) {
            return {
                permitida: false,
                mensaje: citasProtegidas.get(id)
            };
        }

        return evaluarGestionCita(evento);
    }

    function comprobarGestionFormulario($formulario, contenedor) {
        const accion = $formulario.find('[name="accion"]').val();

        if (!["modificar", "cancelar"].includes(accion)) return true;

        const id = $formulario.find('[name="id_agendamiento"]').val();
        const gestion = obtenerGestionCita(id);

        if (gestion.permitida) {
            $formulario.removeAttr("data-cita-protegida data-motivo-proteccion");
            return true;
        }

        $formulario.attr({
            "data-cita-protegida": "1",
            "data-motivo-proteccion": gestion.mensaje
        });

        $formulario.find('[type="submit"]').prop("disabled", true);

        if (accion === "cancelar") {
            $("#btnConfirmarCancelacionAgendamiento").prop("disabled", true);
        }

        mostrarAviso(contenedor, "warning", gestion.mensaje, "bi-shield-lock");
        enfocarAviso(contenedor);

        return false;
    }

    function esRechazoDeGestion(respuesta) {
        return [
            "agendamiento_no_gestionable",
            "cita_no_disponible"
        ].includes(respuesta?.codigo);
    }

    function registrarProteccion($formulario, mensaje) {
        const id = String($formulario.find('[name="id_agendamiento"]').val() || "");

        if (!id) return;

        citasProtegidas.set(id, mensaje);

        $formulario.attr({
            "data-cita-protegida": "1",
            "data-motivo-proteccion": mensaje
        });
    }


    /* ================================================= */
    /* ENVÍO Y RESULTADO DE LAS OPERACIONES */
    /* ================================================= */

    /* Preparar los campos y conservar los servicios como arreglo */
    function obtenerDatosFormulario($formulario) {
        const datos = {};

        $formulario.serializeArray().forEach(function (campo) {
            if (campo.name.endsWith("[]")) {
                const nombre = campo.name.slice(0, -2);

                if (!Array.isArray(datos[nombre])) {
                    datos[nombre] = [];
                }

                datos[nombre].push(campo.value);

            } else {
                datos[campo.name] = campo.value;
            }
        });

        return datos;
    }

    function solicitarDatos(url, opciones = {}) {
        let peticion = null;

        const promesa = new Promise(function (resolve, reject) {
            const metodo = String(opciones.method || "GET").toUpperCase();
            const esLectura = metodo === "GET" || metodo === "HEAD";

            peticion = $.ajax({
                url: url,
                type: metodo,

                data: esLectura
                    ? (opciones.data || null)
                    : JSON.stringify(opciones.data || {}),

                dataType: "json",

                contentType: esLectura
                    ? "application/x-www-form-urlencoded; charset=UTF-8"
                    : "application/json; charset=UTF-8",

                processData: esLectura,
                timeout: 30000,

                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                },

                success: function (respuesta) {
                    resolve(respuesta);
                },

                error: function (xhr, estado) {
                    reject({
                        status: xhr.status,
                        statusText: estado,
                        responseJSON: xhr.responseJSON || null
                    });
                }
            });
        });

        /* Cancelar las consultas de disponibilidad reemplazadas */
        promesa.cancelar = function () {
            if (peticion) peticion.abort();
        };

        return promesa;
    }

    async function actualizarCalendario() {
        if (typeof window.AgendamientoCalendario?.actualizar !== "function") {
            return false;
        }

        try {
            return await window.AgendamientoCalendario.actualizar() === true;

        } catch (error) {
            return false;
        }
    }

    /* Compartir el envío con las acciones de cancelar y desbloquear */
    async function enviarFormulario(formulario, configuracion = {}) {
        const $formulario = $(formulario);

        if (
            !$formulario.length ||
            operacionEnCurso ||
            redirigiendo ||
            $formulario.attr("data-enviando") === "1"
        ) {
            return false;
        }

        if (!comprobarGestionFormulario($formulario, configuracion.contenedor)) {
            return false;
        }

        if (configuracion.validar && !await configuracion.validar()) return false;

        /* Comprobar nuevamente el estado después de cargar las validaciones */
        if (operacionEnCurso || redirigiendo || $formulario.attr("data-enviando") === "1") {
            return false;
        }

        if (configuracion.validador && !configuracion.validador.permiteEnviar()) {
            return false;
        }

        const datos = obtenerDatosFormulario($formulario);

        const $modal = configuracion.modal
            ? $(configuracion.modal)
            : $formulario.closest(".modal");

        const $controles = $formulario
            .find("input, select, textarea, button")
            .add($modal.find("button"));

        const estados = $controles.map(function () {
            return {
                elemento: this,
                deshabilitado: $(this).prop("disabled")
            };
        }).get();

        operacionEnCurso = true;

        $formulario
            .attr("data-enviando", "1")
            .attr("aria-busy", "true");

        if (configuracion.validador) {
            configuracion.validador.detener();
        }

        $controles.prop("disabled", true);

        mostrarAviso(
            configuracion.contenedor,
            "info",
            "Guardando los cambios...",
            "bi-hourglass-split"
        );

        let respuesta;
        let operacionConfirmada = false;
        let calendarioActualizado = false;
        let campoRechazado = null;

        try {
            respuesta = await solicitarDatos(
                $formulario.attr("action") || urlAgendamiento,
                {
                    method: "POST",
                    data: datos
                }
            );

            if (!respuesta || respuesta.ok !== true) {
                const error = new Error("La operación no fue confirmada.");
                error.responseJSON = respuesta;
                throw error;
            }

            operacionConfirmada = true;

            /* Separar la confirmación del guardado de la actualización visual */
            calendarioActualizado = await actualizarCalendario();

            await cerrarModal($modal);

        } catch (error) {
            if (!operacionConfirmada) {
                if (error.status === 401) {
                    redirigirAlAcceso();
                    return false;
                }

                const mensaje = typeof error.responseJSON?.mensaje === "string"
                    ? error.responseJSON.mensaje
                    : "No se recibió una confirmación válida. Actualice el calendario antes de intentar guardar de nuevo.";

                if (esRechazoDeGestion(error.responseJSON)) {
                    registrarProteccion($formulario, mensaje);
                }

                campoRechazado = mostrarErroresFormulario($formulario, error.responseJSON);

                const tipoError = !error.responseJSON && (
                    !error.status || error.statusText === "timeout" || error.statusText === "parsererror"
                ) ? "warning" : "danger";

                if (configuracion.validador) {
                    configuracion.validador.rechazar(mensaje, error.status === 409);

                    /* Conservar los errores de campos y los conflictos junto al formulario */
                    if (!campoRechazado && error.status !== 409 && !esRechazoDeGestion(error.responseJSON) && window.Alertas?.disponible()) {
                        window.Alertas.notificar(mensaje, tipoError, { origen: $formulario });
                    }

                } else {
                    mostrarAviso(
                        configuracion.contenedor,
                        tipoError,
                        mensaje,
                        "bi-heartbreak-fill"
                    );

                    enfocarAviso(configuracion.contenedor);
                }

                return false;
            }

            calendarioActualizado = false;

        } finally {
            estados.forEach(function (estado) {
                $(estado.elemento).prop("disabled", estado.deshabilitado);
            });

            if (campoRechazado && !redirigiendo) campoRechazado.focus();

            $formulario.removeAttr("data-enviando aria-busy");

            modalPermitidoCerrar = null;
            operacionEnCurso = false;

            if (configuracion.validador) {
                configuracion.validador.actualizarBoton();
            }

            /* Conservar el bloqueo cuando el servidor rechaza la gestión de la cita */
            if (!operacionConfirmada && $formulario.attr("data-cita-protegida") === "1") {
                $formulario.find('[type="submit"]').prop("disabled", true);

                if (datos.accion === "cancelar") {
                    $("#btnConfirmarCancelacionAgendamiento").prop("disabled", true);
                }
            }
        }

        const mensaje = respuesta.mensaje || "La operación se completó correctamente.";

        mostrarResultado(
            configuracion.titulo || "Operación completada",
            calendarioActualizado
                ? mensaje
                : mensaje +
                    " No fue posible actualizar el calendario. Recargue la página para ver los cambios; la operación ya fue guardada.",
            calendarioActualizado && respuesta.tipo_aviso !== "warning" ? "success" : "warning"
        );

        return true;
    }

    window.AgendamientoOperaciones = {
        solicitar: solicitarDatos,
        enviar: enviarFormulario,
        evaluarGestion: evaluarGestionCita,
        obtenerGestion: obtenerGestionCita,

        estaProcesando: function () {
            return operacionEnCurso;
        }
    };


    /* Impedir cambios de modal mientras se guarda una operación */
    const $modalesOperaciones = $modalNuevo
        .add($modalEditar)
        .add($modalNuevoBloqueo)
        .add($modalEditarBloqueo)
        .add("#modalConfirmarCancelacionAgendamiento")
        .add("#modalConfirmarDesbloqueoHorario");

    $modalesOperaciones.on("show.bs.modal", function (evento) {
        if (operacionEnCurso) {
            evento.preventDefault();
            return;
        }

        $(this).attr("data-abriendo-agendamiento", "1");
    });

    $modalesOperaciones.on("shown.bs.modal", function () {
        $(this).removeAttr("data-abriendo-agendamiento");
    });

    $modalesOperaciones.on("hide.bs.modal", function (evento) {
        if (operacionEnCurso && modalPermitidoCerrar !== this) {
            evento.preventDefault();
        }
    });


    /* Mostrar los mensajes iniciales preparados por la vista */
    if (
        $modalMensaje.length &&
        $modalMensaje.attr("data-mostrar-inicial") !== "0" &&
        window.bootstrap
    ) {
        if (window.Alertas?.disponible()) window.Alertas.desdeModal($modalMensaje);
        else bootstrap.Modal.getOrCreateInstance($modalMensaje.get(0)).show();
    }


    /* ================================================= */
    /* FECHA MÍNIMA */
    /* ================================================= */

    function obtenerFechaActual() {
        const hoy = new Date();
        const anio = hoy.getFullYear();
        const mes = String(hoy.getMonth() + 1).padStart(2, "0");
        const dia = String(hoy.getDate()).padStart(2, "0");

        return `${anio}-${mes}-${dia}`;
    }

    function establecerFechaMinima() {
        const fechaActual = obtenerFechaActual();

        $fechaNueva
            .add($fechaEditar)
            .add($fechaNuevoBloqueo)
            .add($fechaEditarBloqueo)
            .attr("min", fechaActual);
    }


    /* ================================================= */
    /* SELECTOR PERSONALIZADO DE SERVICIOS */
    /* ================================================= */

    function crearSelectorServicios(configuracion) {
        const $contenedor = $("#" + configuracion.contenedorId);
        const $toggle = $("#" + configuracion.toggleId);
        const $texto = $("#" + configuracion.textoId);
        const $panel = $("#" + configuracion.panelId);
        const $buscador = $("#" + configuracion.buscadorId);
        const $lista = $("#" + configuracion.listaId);
        const $sinResultados = $("#" + configuracion.sinResultadosId);
        const $resumen = $("#" + configuracion.resumenId);
        const $feedback = $("#" + configuracion.feedbackId);

        if (!$contenedor.length || !$toggle.length || !$panel.length || !$lista.length) {
            return null;
        }

        const $checks = $contenedor.find(".agendamiento-servicio-check");
        const $opciones = $contenedor.find(".agendamiento-servicio-opcion");

        let deshabilitado = false;

        function obtenerSeleccionados() {
            return $checks.filter(":checked");
        }

        function normalizarTexto(valor) {
            return String(valor || "")
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .trim()
                .toLowerCase();
        }

        function cerrar() {
            $panel.prop("hidden", true);
            $toggle.attr("aria-expanded", "false");
            $contenedor.removeClass("is-open");
        }

        function abrir() {
            if (deshabilitado) return;

            const $otros = $(".agendamiento-servicios-selector").not($contenedor);

            $otros.removeClass("is-open");
            $otros.find(".agendamiento-servicios-panel").prop("hidden", true);
            $otros.find(".agendamiento-servicios-toggle").attr("aria-expanded", "false");

            $panel.prop("hidden", false);
            $toggle.attr("aria-expanded", "true");
            $contenedor.addClass("is-open");

            window.setTimeout(function () {
                $buscador.trigger("focus");
            }, 0);
        }

        function alternar() {
            if ($panel.prop("hidden")) {
                abrir();
            } else {
                cerrar();
            }
        }

        function mostrarError(mensaje = "Seleccione al menos un servicio y utilice una selección válida.") {
            $contenedor.add($toggle).removeClass("is-invalid").attr("aria-invalid", "true");

            $feedback
                .removeClass("invalid-feedback")
                .addClass("templo-validacion-feedback is-invalid")
                .text(mensaje)
                .css("display", "block");
        }

        function quitarError() {
            $contenedor.add($toggle).removeClass("is-invalid").removeAttr("aria-invalid");

            $feedback
                .removeClass("templo-validacion-feedback is-invalid")
                .addClass("invalid-feedback")
                .text("Seleccione al menos un servicio.")
                .css("display", "");
        }

        function actualizarTexto() {
            const $seleccionados = obtenerSeleccionados();

            if (!$seleccionados.length) {
                $texto.text("Seleccionar servicios...");

            } else if ($seleccionados.length === 1) {
                $texto.text($seleccionados.attr("data-nombre") || "1 servicio seleccionado");

            } else {
                $texto.text(`${$seleccionados.length} servicios seleccionados`);
            }
        }

        function actualizarResumen() {
            if (!$resumen.length) return;

            const $seleccionados = obtenerSeleccionados();

            $resumen.empty();

            if (!$seleccionados.length) {
                $resumen.append(
                    $("<span>", {
                        class: "agendamiento-servicios-resumen-vacio"
                    }).text("Ningún servicio seleccionado.")
                );

                return;
            }

            /* Mostrar nombre, duración y precio de cada servicio */
            $seleccionados.each(function () {
                const $check = $(this);
                const nombreServicio = $check.attr("data-nombre") || "Servicio";
                const duracionServicio = parseInt($check.attr("data-duracion") || "0", 10);
                const precioServicio = parseFloat($check.attr("data-precio") || "0");

                const $nombre = $("<span>", {
                    class: "agendamiento-servicio-resumen-nombre"
                }).text(nombreServicio);

                const $duracion = $("<span>", {
                    class: "agendamiento-servicio-resumen-duracion"
                }).text(`${Number.isNaN(duracionServicio) ? 0 : duracionServicio} min`);

                const $precio = $("<span>", {
                    class: "agendamiento-servicio-resumen-precio"
                }).text(
                    (Number.isNaN(precioServicio) ? 0 : precioServicio).toLocaleString("es-VE", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                );

                $resumen.append(
                    $("<div>", {
                        class: "agendamiento-servicio-resumen-item"
                    }).append($nombre, $duracion, $precio)
                );
            });
        }

        function calcularTotales() {
            let montoTotal = 0;
            let duracionTotal = 0;

            obtenerSeleccionados().each(function () {
                const precio = parseFloat($(this).attr("data-precio") || "0");
                const duracion = parseInt($(this).attr("data-duracion") || "0", 10);

                if (!Number.isNaN(precio)) montoTotal += precio;
                if (!Number.isNaN(duracion)) duracionTotal += duracion;
            });

            $(configuracion.inputMonto).val(montoTotal.toFixed(2));
            $(configuracion.inputDuracion).val(duracionTotal > 0 ? String(duracionTotal) : "0");

            notificarCambio($(configuracion.inputMonto));
            notificarCambio($(configuracion.inputDuracion));
        }

        function actualizar(calcular = true) {
            actualizarTexto();
            actualizarResumen();

            if (calcular) calcularTotales();
            if (obtenerSeleccionados().length) quitarError();
        }

        function filtrar() {
            const termino = normalizarTexto($buscador.val());
            let visibles = 0;

            $opciones.each(function () {
                const $opcion = $(this);
                const nombre = normalizarTexto($opcion.attr("data-servicio-nombre") || $opcion.text());
                const coincide = termino === "" || nombre.includes(termino);

                $opcion.prop("hidden", !coincide);

                if (coincide) visibles += 1;
            });

            $sinResultados.toggleClass("d-none", visibles > 0);
        }

        function limpiarBusqueda() {
            $buscador.val("");
            $opciones.prop("hidden", false);
            $sinResultados.addClass("d-none");
        }

        function limpiarSeleccion() {
            $checks.prop("checked", false);
            quitarError();
            actualizar();
        }

        function seleccionar(idsServicios) {
            const ids = Array.isArray(idsServicios) ? idsServicios.map(String) : [];

            $checks.each(function () {
                $(this).prop("checked", ids.includes(String($(this).val())));
            });

            actualizar();
        }

        function establecerDeshabilitado(estado, limpiar = false) {
            deshabilitado = Boolean(estado);

            $contenedor.toggleClass("is-disabled", deshabilitado);

            $toggle
                .add($checks)
                .add($buscador)
                .prop("disabled", deshabilitado);

            if (deshabilitado) {
                cerrar();
                quitarError();

                if (limpiar) limpiarSeleccion();
            }
        }

        function validar() {
            if (deshabilitado) {
                quitarError();
                return true;
            }

            const ids = obtenerSeleccionados().map(function () {
                return $(this).val();
            }).get();

            const valido = validacionesAgenda && validacionesAgenda.validarServicios(ids);

            if (valido) {
                quitarError();
            } else {
                mostrarError();
            }

            return valido;
        }

        $toggle.on("click", alternar);

        $checks.on("change", function () {
            actualizar();
        });

        $buscador.on("input", filtrar);

        $buscador.on("keydown", function (evento) {
            if (evento.key === "Escape") {
                cerrar();
                $toggle.trigger("focus");
            }
        });

        $panel.on("click", function (evento) {
            evento.stopPropagation();
        });

        actualizar(false);

        return {
            contenedor: $contenedor,
            toggle: $toggle,
            abrir: abrir,
            cerrar: cerrar,
            actualizar: actualizar,
            calcularTotales: calcularTotales,
            limpiarBusqueda: limpiarBusqueda,
            limpiarSeleccion: limpiarSeleccion,
            seleccionar: seleccionar,
            establecerDeshabilitado: establecerDeshabilitado,
            obtenerSeleccionados: obtenerSeleccionados,
            validar: validar,
            mostrarError: mostrarError,
            quitarError: quitarError,

            enfocar: function () {
                $toggle.trigger("focus");
            }
        };
    }

    const selectorServiciosNuevo = crearSelectorServicios({
        contenedorId: "nuevosServiciosAgendamiento",
        toggleId: "nuevoServiciosToggle",
        textoId: "nuevoServiciosTexto",
        panelId: "nuevoServiciosPanel",
        buscadorId: "nuevoServiciosBuscar",
        listaId: "nuevoServiciosLista",
        sinResultadosId: "nuevoServiciosSinResultados",
        resumenId: "nuevoServiciosResumen",
        feedbackId: "nuevoServiciosFeedback",
        inputMonto: $montoNuevo,
        inputDuracion: $duracionNueva
    });

    const selectorServiciosEditar = crearSelectorServicios({
        contenedorId: "editarServiciosAgendamiento",
        toggleId: "editarServiciosToggle",
        textoId: "editarServiciosTexto",
        panelId: "editarServiciosPanel",
        buscadorId: "editarServiciosBuscar",
        listaId: "editarServiciosLista",
        sinResultadosId: "editarServiciosSinResultados",
        resumenId: "editarServiciosResumen",
        feedbackId: "editarServiciosFeedback",
        inputMonto: $montoEditar,
        inputDuracion: $duracionEditar
    });


    /* ================================================= */
    /* VALIDACIONES DE LOS FORMULARIOS */
    /* ================================================= */

    /* Notificar los cambios calculados a los validadores nativos del formulario */
    function notificarCambio($campos, tipo = "input") {
        $campos.each(function () {
            this.dispatchEvent(new Event(tipo, { bubbles: true }));
        });
    }

    function obtenerSelectorFormulario($formulario) {
        if ($formulario.is($formNuevo)) return selectorServiciosNuevo;
        if ($formulario.is($formEditar)) return selectorServiciosEditar;

        return null;
    }

    function obtenerReglasFormulario($formulario) {
        const formulario = $formulario.get(0);

        if (!formulario || !validacionesAgenda) return null;
        if (reglasFormularios.has(formulario)) return reglasFormularios.get(formulario);

        const v = validacionesAgenda;
        const esBloqueo = $formulario.is($formNuevoBloqueo.add($formEditarBloqueo));
        const esEdicion = $formulario.is($formEditar.add($formEditarBloqueo));

        const base = esBloqueo
            ? (esEdicion ? v.reglasEditarBloqueoHorario : v.reglasBloqueosHorario)
            : (esEdicion ? v.reglasEditarAgendamiento : v.reglasAgendamientos);

        const reglas = {
            ...base,
            fecha: {
                comprobar: function (valor) {
                    const mensaje = base.fecha.comprobar(valor);

                    if (mensaje) return mensaje;

                    const minima = $formulario.find('[name="fecha"]').attr("min") || obtenerFechaActual();

                    return valor >= minima
                        ? null : "Seleccione una fecha igual o posterior a la fecha actual.";
                }
            }
        };

        if (esBloqueo) {
            reglas.hora_fin = {
                comprobar: function (valor) {
                    const mensaje = base.hora_fin.comprobar(valor);
                    const inicio = $formulario.find('[name="hora_inicio"]').val();

                    if (mensaje) return mensaje;

                    return !v.validarHora(inicio) || v.validarIntervaloHoras(inicio, valor)
                        ? null : "La hora final debe ser posterior a la hora inicial.";
                }
            };

        } else {
            reglas.duracion_minutos = {
                comprobar: function (valor) {
                    const mensaje = base.duracion_minutos.comprobar(valor);
                    const hora = $formulario.find('[name="hora"]').val();

                    if (mensaje) return mensaje;

                    return !v.validarHora(hora) || v.validarHorarioAgenda(hora, valor)
                        ? null : "La atención debe finalizar dentro de la fecha seleccionada.";
                }
            };
        }

        reglasFormularios.set(formulario, Object.freeze(reglas));

        return reglasFormularios.get(formulario);
    }

    function mostrarErroresFormulario($formulario, respuesta) {
        if (!validacionesAgenda || !respuesta) return null;

        const errores = $.isPlainObject(respuesta.errores)
            ? { ...respuesta.errores }
            : {};

        if (
            typeof respuesta.campo === "string" &&
            typeof respuesta.mensaje === "string" &&
            !errores[respuesta.campo]
        ) {
            errores[respuesta.campo] = respuesta.mensaje;
        }

        const selector = obtenerSelectorFormulario($formulario);

        if (typeof errores.servicios === "string" && selector) {
            selector.mostrarError(errores.servicios);
        }

        const primero = validacionesAgenda.mostrarErrores($formulario.get(0), errores);

        return primero || (errores.servicios && selector ? selector.toggle.get(0) : null);
    }

    async function validarFormularioAgenda($formulario) {
        const v = await cargaValidaciones;

        if (!v) {
            const $aviso = $formulario.find('[id^="mensajeDisponibilidad"]').first();

            mostrarAviso(
                $aviso,
                "danger",
                "No fue posible cargar las validaciones. Recargue la página antes de guardar.",
                "bi-exclamation-triangle-fill"
            );

            enfocarAviso($aviso);

            return false;
        }

        const resultado = v.validarFormulario(
            $formulario.get(0),
            obtenerReglasFormulario($formulario)
        );

        const selector = obtenerSelectorFormulario($formulario);
        const serviciosValidos = !selector || selector.validar();

        if (!resultado.valido || !serviciosValidos) {
            if (resultado.primerCampo && resultado.primerCampo.type !== "hidden") {
                resultado.primerCampo.focus();

            } else if (resultado.primerCampo?.type === "hidden") {
                const $aviso = $formulario.find('[id^="mensajeDisponibilidad"]').first();

                mostrarAviso(
                    $aviso,
                    "danger",
                    resultado.errores[resultado.primerCampo.name],
                    "bi-exclamation-triangle-fill"
                );

                enfocarAviso($aviso);

            } else if (!serviciosValidos && selector) {
                selector.enfocar();
            }

            return false;
        }

        return true;
    }

    function limpiarValidaciones($formulario) {
        /* Conservar el elemento de aviso que pertenece al selector de servicios */
        obtenerSelectorFormulario($formulario)?.quitarError();
        validacionesAgenda?.limpiarFormulario($formulario.get(0));

        $formulario.find(".is-invalid").removeClass("is-invalid");
        $formulario.find("[aria-invalid]").removeAttr("aria-invalid");
        $formulario.find(".invalid-feedback").css("display", "");
    }

    /* Volver a comprobar los campos calculados o dependientes que ya muestran un mensaje */
    $formNuevo.add($formEditar).add($formNuevoBloqueo).add($formEditarBloqueo)
        .on("input change", '[name="hora"], [name="hora_inicio"]', function () {
            if (!validacionesAgenda) return;

            const $formulario = $(this.form);
            const nombre = this.name === "hora_inicio" ? "hora_fin" : "duracion_minutos";
            const campo = $formulario.find('[name="' + nombre + '"]').get(0);

            if (!campo || !campo.nextElementSibling?.classList.contains("templo-validacion-feedback")) {
                return;
            }

            const reglas = obtenerReglasFormulario($formulario);

            validacionesAgenda.validarFormulario(this.form, {
                [nombre]: reglas[nombre]
            });
        });


    /* ================================================= */
    /* LIMPIEZA Y PREPARACIÓN DE FORMULARIOS */
    /* ================================================= */

    function limpiarFormularioNuevo() {
        if (!$formNuevo.length) return;

        $formNuevo.get(0).reset();

        limpiarValidaciones($formNuevo);

        if (selectorServiciosNuevo) {
            selectorServiciosNuevo.limpiarSeleccion();
            selectorServiciosNuevo.limpiarBusqueda();
            selectorServiciosNuevo.establecerDeshabilitado(false);
            selectorServiciosNuevo.cerrar();
        }

        $montoNuevo.val("0.00");
        $duracionNueva.val("0").prop("readOnly", true);
        $clienteNuevo.prop("disabled", false).prop("required", true);
    }

    function prepararFormularioEditar() {
        limpiarValidaciones($formEditar);

        if (selectorServiciosEditar) {
            selectorServiciosEditar.limpiarBusqueda();
            selectorServiciosEditar.cerrar();
            selectorServiciosEditar.establecerDeshabilitado(false);
            selectorServiciosEditar.actualizar();
        }

        $duracionEditar.prop("readOnly", true);
        $clienteEditar.prop("disabled", false).prop("required", true);
    }

    function seleccionarEstadoNuevo(nombreEstado) {
        const $opcion = $estadoNuevo.find("option").filter(function () {
            return $(this).text().trim().toUpperCase() === nombreEstado.toUpperCase();
        }).first();

        if ($opcion.length) {
            $estadoNuevo.val($opcion.val()).removeClass("is-invalid");
            notificarCambio($estadoNuevo, "change");
        }
    }

    function establecerEstadoInicialNuevo() {
        $estadoNuevo.val("").removeClass("is-invalid");
        notificarCambio($estadoNuevo, "change");
    }

    function actualizarEstadoSegunTipoNuevo() {
        const $opcion = $tipoNuevo.find("option:selected");

        const nombreTipo = String(
            $opcion.attr("data-nombre") || $opcion.text() || ""
        ).trim().toUpperCase();

        if (nombreTipo === "CITA INMEDIATA") {
            seleccionarEstadoNuevo("EN ESPERA");

        } else if (nombreTipo === "CITA PROGRAMADA") {
            seleccionarEstadoNuevo("AGENDADA");

        } else {
            establecerEstadoInicialNuevo();
        }
    }


    /* ================================================= */
    /* EVENTOS DE CAMPOS Y MODALES */
    /* ================================================= */

    $tipoNuevo.on("change", function () {
        $(this).removeClass("is-invalid");
        actualizarEstadoSegunTipoNuevo();
    });

    $tipoEditar.add($clienteNuevo).add($clienteEditar).on("change", function () {
        $(this).removeClass("is-invalid");
    });

    $(".templo-input, .form-select")
        .not(".agendamiento-servicios-toggle")
        .on("input change", function () {
            $(this).removeClass("is-invalid");
        });

    $modalNuevo.on("shown.bs.modal", function () {
        establecerFechaMinima();
        establecerEstadoInicialNuevo();
    });

    $modalNuevo.on("hidden.bs.modal", limpiarFormularioNuevo);

    $modalEditar.on("shown.bs.modal", function () {
        establecerFechaMinima();
        prepararFormularioEditar();

        comprobarGestionFormulario(
            $formEditar,
            $("#mensajeDisponibilidadEditarAgendamiento")
        );
    });

    $modalEditar.on("hidden.bs.modal", function () {
        $formEditar.removeAttr("data-cita-protegida data-motivo-proteccion");

        limpiarValidaciones($formEditar);

        if (selectorServiciosEditar) {
            selectorServiciosEditar.cerrar();
            selectorServiciosEditar.limpiarBusqueda();
        }
    });

    $modalNuevoBloqueo.add($modalEditarBloqueo).on("shown.bs.modal", establecerFechaMinima);

    $modalNuevoBloqueo.on("hidden.bs.modal", function () {
        if ($formNuevoBloqueo.length) {
            $formNuevoBloqueo.get(0).reset();
            limpiarValidaciones($formNuevoBloqueo);
        }
    });

    $modalEditarBloqueo.on("hidden.bs.modal", function () {
        limpiarValidaciones($formEditarBloqueo);
    });


    /* Cerrar los selectores al pulsar fuera de ellos */
    $(document).on("click", function (evento) {
        $(".agendamiento-servicios-selector").each(function () {
            const $selector = $(this);

            if ($selector.is(evento.target) || $selector.has(evento.target).length) {
                return;
            }

            $selector.find(".agendamiento-servicios-panel").prop("hidden", true);
            $selector.find(".agendamiento-servicios-toggle").attr("aria-expanded", "false");
            $selector.removeClass("is-open");
        });
    });

    $(document).on("keydown", function (evento) {
        if (evento.key === "Escape") {
            selectorServiciosNuevo?.cerrar();
            selectorServiciosEditar?.cerrar();
        }
    });


    /* ================================================= */
    /* COMUNICACIÓN CON EL CALENDARIO */
    /* ================================================= */

    window.AgendamientoServicios = {
        seleccionarNuevo: function (ids) {
            selectorServiciosNuevo?.seleccionar(ids);
        },

        seleccionarEditar: function (ids) {
            selectorServiciosEditar?.seleccionar(ids);
        },

        actualizarNuevo: function () {
            selectorServiciosNuevo?.actualizar();
        },

        actualizarEditar: function () {
            selectorServiciosEditar?.actualizar();
        },

        configurarEditar: prepararFormularioEditar,

        limpiarEditar: function () {
            selectorServiciosEditar?.limpiarSeleccion();
        }
    };


    /* ================================================= */
    /* DISPONIBILIDAD DE CITAS Y BLOQUEOS */
    /* ================================================= */

    function crearValidadorDisponibilidad(configuracion) {
        const $formulario = $(configuracion.formulario);
        const $contenedor = $(configuracion.contenedor);
        const $boton = $formulario.find('button[type="submit"]').first();

        if (!$formulario.length || !$contenedor.length) return null;

        let temporizador = null;
        let numeroSolicitud = 0;
        let peticion = null;
        let pendiente = false;
        let disponible = null;

        function actualizarBoton() {
            const deshabilitado =
                $formulario.attr("data-enviando") === "1" ||
                $formulario.attr("data-cita-protegida") === "1" ||
                pendiente ||
                disponible === false;

            $boton
                .prop("disabled", deshabilitado)
                .attr("aria-disabled", String(deshabilitado));
        }

        function detener() {
            window.clearTimeout(temporizador);

            numeroSolicitud += 1;
            pendiente = false;

            if (peticion) {
                peticion.cancelar();
                peticion = null;
            }
        }

        function limpiar() {
            detener();
            disponible = null;

            ocultarAviso($contenedor);
            actualizarBoton();
        }

        function rechazar(mensaje, bloquear = false) {
            if (bloquear) disponible = false;

            mostrarAviso($contenedor, "danger", mensaje, "bi-heartbreak-fill");

            actualizarBoton();
            enfocarAviso($contenedor);
        }

        async function consultar(datos, solicitudActual) {
            try {
                peticion = solicitarDatos(urlAgendamiento, {
                    method: "POST",
                    data: datos
                });

                const resultado = await peticion;

                /* Descartar respuestas de un horario que ya fue cambiado */
                if (solicitudActual !== numeroSolicitud) return;

                if (!resultado || typeof resultado.disponible !== "boolean") {
                    throw new Error("La respuesta de disponibilidad no es válida.");
                }

                pendiente = false;
                disponible = resultado.disponible;

                if (!disponible) mostrarErroresFormulario($formulario, resultado);

                mostrarAviso(
                    $contenedor,
                    disponible ? "success" : "danger",
                    resultado.mensaje || (
                        disponible
                            ? "El horario se encuentra disponible."
                            : "El horario seleccionado no se encuentra disponible."
                    ),
                    disponible ? "bi-check-circle-fill" : "bi-heartbreak-fill"
                );

            } catch (error) {
                if (solicitudActual !== numeroSolicitud) return;

                pendiente = false;
                disponible = null;

                if (error.status === 401) {
                    redirigirAlAcceso();
                    return;
                }

                /* Conservar la protección detectada mientras el formulario estaba abierto */
                if (esRechazoDeGestion(error.responseJSON)) {
                    const mensaje = typeof error.responseJSON.mensaje === "string"
                        ? error.responseJSON.mensaje
                        : "La cita ya no permite modificaciones ni cancelaciones.";

                    registrarProteccion($formulario, mensaje);
                    disponible = false;

                    mostrarAviso($contenedor, "danger", mensaje, "bi-shield-lock");

                    return;
                }

                mostrarErroresFormulario($formulario, error.responseJSON);

                if (error.responseJSON?.codigo === "bloqueo_no_disponible") {
                    disponible = false;
                }

                mostrarAviso(
                    $contenedor,
                    "warning",
                    error.responseJSON?.mensaje ||
                        "No fue posible comprobar el horario en este momento. La validación se realizará nuevamente al guardar.",
                    "bi-exclamation-triangle-fill"
                );

            } finally {
                if (solicitudActual === numeroSolicitud) {
                    peticion = null;
                    actualizarBoton();
                }
            }
        }

        function programar() {
            if (redirigiendo || $formulario.attr("data-enviando") === "1") return;

            detener();

            if ($formulario.attr("data-cita-protegida") === "1") {
                rechazar(
                    $formulario.attr("data-motivo-proteccion") ||
                        "La cita ya no permite modificaciones ni cancelaciones.",
                    true
                );

                return;
            }

            const solicitudActual = numeroSolicitud;
            const datos = configuracion.obtenerDatos();

            disponible = null;

            if (!configuracion.datosValidos(datos)) {
                ocultarAviso($contenedor);
                actualizarBoton();
                return;
            }

            pendiente = true;
            actualizarBoton();

            mostrarAviso(
                $contenedor,
                "warning",
                "Comprobando disponibilidad del horario...",
                "bi-hourglass-split"
            );

            temporizador = window.setTimeout(function () {
                consultar(datos, solicitudActual);
            }, 350);
        }

        function permiteEnviar() {
            if (
                $formulario.attr("data-cita-protegida") !== "1" &&
                !pendiente &&
                disponible !== false
            ) {
                return true;
            }

            enfocarAviso($contenedor);

            return false;
        }

        $(configuracion.campos).on("input change", programar);

        return {
            programar: programar,
            limpiar: limpiar,
            detener: detener,
            actualizarBoton: actualizarBoton,
            permiteEnviar: permiteEnviar,
            rechazar: rechazar
        };
    }

    function valorCampo(idCampo) {
        return $("#" + idCampo).val() || "";
    }

    function datosCitaValidos(datos) {
        const v = validacionesAgenda;

        return Boolean(
            v &&
            v.validarIdentificador(datos.id_usuario_asignado) &&
            v.validarFechaAgenda(datos.fecha) &&
            v.validarHora(datos.hora) &&
            v.validarHorarioAgenda(datos.hora, datos.duracion_minutos)
        );
    }

    function datosBloqueoValidos(datos) {
        const v = validacionesAgenda;

        return Boolean(
            v &&
            v.validarIdentificador(datos.id_usuario_especialista) &&
            v.validarFechaAgenda(datos.fecha) &&
            v.validarIntervaloHoras(datos.hora_inicio, datos.hora_fin)
        );
    }

    const validadorNuevaCita = crearValidadorDisponibilidad({
        formulario: $formNuevo,
        contenedor: $("#mensajeDisponibilidadNuevoAgendamiento"),

        campos: $(
            "#nuevoUsuarioAsignado, #nuevaFechaAgendamiento, #nuevaHoraAgendamiento"
        ).add($formNuevo.find(".agendamiento-servicio-check")),

        obtenerDatos: function () {
            return {
                accion: "validar_disponibilidad_agendamiento",
                id_usuario_asignado: valorCampo("nuevoUsuarioAsignado"),
                fecha: valorCampo("nuevaFechaAgendamiento"),
                hora: valorCampo("nuevaHoraAgendamiento"),
                duracion_minutos: valorCampo("nuevaDuracionAgendamiento")
            };
        },

        datosValidos: datosCitaValidos
    });

    const validadorEditarCita = crearValidadorDisponibilidad({
        formulario: $formEditar,
        contenedor: $("#mensajeDisponibilidadEditarAgendamiento"),

        campos: $(
            "#editarUsuarioAsignado, #editarFechaAgendamiento, #editarHoraAgendamiento"
        ).add($formEditar.find(".agendamiento-servicio-check")),

        obtenerDatos: function () {
            return {
                accion: "validar_disponibilidad_agendamiento",

                /* Excluir la propia cita al comprobar conflictos */
                id_agendamiento: valorCampo("editarIdAgendamiento"),

                id_usuario_asignado: valorCampo("editarUsuarioAsignado"),
                fecha: valorCampo("editarFechaAgendamiento"),
                hora: valorCampo("editarHoraAgendamiento"),
                duracion_minutos: valorCampo("editarDuracionAgendamiento")
            };
        },

        datosValidos: datosCitaValidos
    });

    const validadorNuevoBloqueo = crearValidadorDisponibilidad({
        formulario: $formNuevoBloqueo,
        contenedor: $("#mensajeDisponibilidadNuevoBloqueo"),

        campos: $(
            "#bloqueoUsuarioEspecialista, #bloqueoFecha, #bloqueoHoraInicio, #bloqueoHoraFin"
        ),

        obtenerDatos: function () {
            return {
                accion: "validar_bloqueo_horario",
                id_usuario_especialista: valorCampo("bloqueoUsuarioEspecialista"),
                fecha: valorCampo("bloqueoFecha"),
                hora_inicio: valorCampo("bloqueoHoraInicio"),
                hora_fin: valorCampo("bloqueoHoraFin")
            };
        },

        datosValidos: datosBloqueoValidos
    });

    const validadorEditarBloqueo = crearValidadorDisponibilidad({
        formulario: $formEditarBloqueo,
        contenedor: $("#mensajeDisponibilidadEditarBloqueo"),

        campos: $(
            "#editarBloqueoUsuarioEspecialista, #editarBloqueoFecha, #editarBloqueoHoraInicio, #editarBloqueoHoraFin"
        ),

        obtenerDatos: function () {
            return {
                accion: "validar_bloqueo_horario",
                id_bloqueo: valorCampo("editarIdBloqueo"),
                id_usuario_especialista: valorCampo("editarBloqueoUsuarioEspecialista"),
                fecha: valorCampo("editarBloqueoFecha"),
                hora_inicio: valorCampo("editarBloqueoHoraInicio"),
                hora_fin: valorCampo("editarBloqueoHoraFin")
            };
        },

        datosValidos: datosBloqueoValidos
    });


    /* ================================================= */
    /* CONECTAR LOS FORMULARIOS */
    /* ================================================= */

    function conectarFormulario($formulario, configuracion) {
        $formulario.prop("noValidate", true).on("submit", function (evento) {
            evento.preventDefault();
            enviarFormulario($formulario, configuracion);
        });
    }

    conectarFormulario($formNuevo, {
        modal: $modalNuevo,
        contenedor: $("#mensajeDisponibilidadNuevoAgendamiento"),
        validador: validadorNuevaCita,
        titulo: "Agendamiento registrado",

        validar: function () {
            return validarFormularioAgenda($formNuevo);
        }
    });

    conectarFormulario($formEditar, {
        modal: $modalEditar,
        contenedor: $("#mensajeDisponibilidadEditarAgendamiento"),
        validador: validadorEditarCita,
        titulo: "Agendamiento actualizado",

        validar: function () {
            return validarFormularioAgenda($formEditar);
        }
    });

    conectarFormulario($formNuevoBloqueo, {
        modal: $modalNuevoBloqueo,
        contenedor: $("#mensajeDisponibilidadNuevoBloqueo"),
        validador: validadorNuevoBloqueo,
        titulo: "Horario bloqueado",

        validar: function () {
            return validarFormularioAgenda($formNuevoBloqueo);
        }
    });

    conectarFormulario($formEditarBloqueo, {
        modal: $modalEditarBloqueo,
        contenedor: $("#mensajeDisponibilidadEditarBloqueo"),
        validador: validadorEditarBloqueo,
        titulo: "Bloqueo actualizado",

        validar: function () {
            return validarFormularioAgenda($formEditarBloqueo);
        }
    });


    /* Preparar y limpiar la disponibilidad al abrir o cerrar cada modal */
    function conectarValidadorConModal($modal, validador) {
        if (!validador) return;

        $modal.on("shown.bs.modal", validador.programar);
        $modal.on("hidden.bs.modal", validador.limpiar);
    }

    conectarValidadorConModal($modalNuevo, validadorNuevaCita);
    conectarValidadorConModal($modalEditar, validadorEditarCita);
    conectarValidadorConModal($modalNuevoBloqueo, validadorNuevoBloqueo);
    conectarValidadorConModal($modalEditarBloqueo, validadorEditarBloqueo);

    cargaValidaciones.then(function (v) {
        if (!v) return;

        [
            [$modalNuevo, validadorNuevaCita],
            [$modalEditar, validadorEditarCita],
            [$modalNuevoBloqueo, validadorNuevoBloqueo],
            [$modalEditarBloqueo, validadorEditarBloqueo]
        ].forEach(function ([$modal, validador]) {
            if ($modal.hasClass("show")) validador?.programar();
        });
    });


    /* ================================================= */
    /* CONFIGURACIÓN INICIAL */
    /* ================================================= */

    establecerFechaMinima();

    $duracionNueva.add($duracionEditar).prop("readOnly", true);

    selectorServiciosNuevo?.actualizar();
    selectorServiciosEditar?.actualizar();
});