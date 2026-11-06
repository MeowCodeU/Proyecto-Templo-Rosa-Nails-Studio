/* ===================================================== */
/* CALENDARIO DE AGENDAMIENTO */
/* ===================================================== */

$(document).ready(function () {
    const $calendario = $("#calendar");
    const $lista = $("#agendamientos-list");
    const $btnBuscar = $("#btnBuscarAgendamientos");
    const $contenedorBusqueda = $("#contenedorBusquedaAgendamientos");
    const $inputBusqueda = $("#buscarAgendamiento");
    const $btnEjecutarBusqueda = $("#btnEjecutarBusquedaAgendamientos");
    const $ultimaActualizacion = $("#ultima-actualizacion");
    const $mensajeCalendario = $("#mensajeCalendarioAgendamiento");

    /* Detalles y edición de citas */
    const $modalDetalle = $("#modalDetalleAgendamiento");
    const $modalEditar = $("#modalEditarAgendamiento");
    const $btnEditar = $("#btnEditarAgendamiento");
    const $btnCancelar = $("#btnCancelarAgendamiento");
    const $formCancelar = $("#formCancelarAgendamiento");
    const $cancelarId = $("#cancelarIdAgendamiento");
    const $modalConfirmarCancelacion = $("#modalConfirmarCancelacionAgendamiento");
    const $btnConfirmarCancelacion = $("#btnConfirmarCancelacionAgendamiento");
    const $mensajeCancelacion = $("#mensajeConfirmarCancelacionAgendamiento");

    /* Detalles y edición de bloqueos */
    const $modalDetalleBloqueo = $("#modalDetalleBloqueoHorario");
    const $modalEditarBloqueo = $("#modalEditarBloqueoHorario");
    const $btnEditarBloqueo = $("#btnEditarBloqueoHorario");
    const $btnDesbloquear = $("#btnDesbloquearHorario");
    const $formDesbloquear = $("#formDesbloquearHorario");
    const $desbloquearId = $("#desbloquearIdBloqueo");
    const $modalConfirmarDesbloqueo = $("#modalConfirmarDesbloqueoHorario");
    const $btnConfirmarDesbloqueo = $("#btnConfirmarDesbloqueoHorario");
    const $mensajeDesbloqueo = $("#mensajeConfirmarDesbloqueoHorario");

    const LIMITE_CALENDARIO_MOVIL = 575.98;
    const LIMITE_CALENDARIO_TABLET = 1200;
    const FUENTE_CALENDARIO = "agenda-templo-rosa";

    let calendario = null;
    let eventoSeleccionado = null;
    let cancelacionEnviada = false;
    let desbloqueoEnviado = false;
    let transicionModal = false;
    let peticionCalendario = null;
    let numeroConsulta = 0;

    if (!$calendario.length || typeof FullCalendar === "undefined") {
        return;
    }

    const eventosIniciales = Array.isArray(window.agendamientosCalendario)
        ? window.agendamientosCalendario
        : [];


    /* ================================================= */
    /* CONFIGURACIÓN RESPONSIVE DEL CALENDARIO */
    /* ================================================= */

    function calendarioEsMovil() {
        return window.innerWidth <= LIMITE_CALENDARIO_MOVIL;
    }

    function obtenerToolbarCalendario() {
        return calendarioEsMovil()
            ? {
                left: "prev",
                center: "title",
                right: "next"
            }
            : {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay"
            };
    }

    function obtenerAlturaCalendario() {
        if (calendarioEsMovil()) {
            return 350;
        }

        return window.innerWidth <= LIMITE_CALENDARIO_TABLET ? 440 : 470;
    }

    let calendarioEstabaEnMovil = calendarioEsMovil();


    /* ================================================= */
    /* FECHAS, HORAS Y DURACIÓN */
    /* ================================================= */

    function obtenerFechaLocal(fecha) {
        const anio = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, "0");
        const dia = String(fecha.getDate()).padStart(2, "0");

        return `${anio}-${mes}-${dia}`;
    }

    function obtenerHoraLocal(fecha) {
        const horas = String(fecha.getHours()).padStart(2, "0");
        const minutos = String(fecha.getMinutes()).padStart(2, "0");

        return `${horas}:${minutos}`;
    }

    function formatearFecha(fecha) {
        return fecha
            ? fecha.toLocaleDateString("es-VE", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric"
            })
            : "—";
    }

    function formatearHora(fecha) {
        return fecha
            ? fecha.toLocaleTimeString("es-VE", {
                hour: "2-digit",
                minute: "2-digit"
            })
            : "—";
    }

    function calcularDuracionMinutos(evento) {
        if (evento.start && evento.end) {
            const diferencia = evento.end.getTime() - evento.start.getTime();

            return Math.max(1, Math.round(diferencia / 60000));
        }

        const duracion = parseInt(
            evento.extendedProps.duracion_minutos || 0,
            10
        );

        return Number.isNaN(duracion) ? 0 : duracion;
    }

    function actualizarHoraTexto() {
        $ultimaActualizacion.text(
            "Actualizado: " +
            new Date().toLocaleTimeString("es-VE", {
                hour: "2-digit",
                minute: "2-digit"
            })
        );
    }


    /* ================================================= */
    /* ESTADOS Y COLORES */
    /* ================================================= */

    function obtenerClaseEstado(estado) {
        const nombre = String(estado || "").trim().toUpperCase();

        switch (nombre) {
            case "CONFIRMADA":
                return "estado-confirmada";

            case "EN ESPERA":
                return "estado-en-espera";

            case "DEMORADA":
                return "estado-demorada";

            case "EN ATENCIÓN":
                return "estado-en-atencion";

            case "REALIZADA":
                return "estado-realizada";

            case "CANCELADA":
                return "estado-cancelada";

            case "AGENDADA":
            default:
                return "estado-programada";
        }
    }

    function obtenerColorEstado(estado) {
        const nombre = String(estado || "").trim().toUpperCase();

        switch (nombre) {
            case "CONFIRMADA":
                return "#7a5362";

            case "EN ESPERA":
                return "#a86f12";

            case "DEMORADA":
                return "#7f5c28";

            case "EN ATENCIÓN":
                return "#9b5f69";

            case "REALIZADA":
                return "#4f7f78";

            case "CANCELADA":
                return "#9b5d73";

            case "AGENDADA":
            default:
                return "#9b6b78";
        }
    }


    /* ================================================= */
    /* DATOS DE SERVICIOS Y EVENTOS */
    /* ================================================= */

    function obtenerServiciosTexto(propiedades) {
        if (Array.isArray(propiedades.servicios)) {
            return propiedades.servicios.map(function (servicio) {
                if (servicio !== null && typeof servicio === "object") {
                    return servicio.nombre_servicio || servicio.nombre || "";
                }

                return String(servicio);

            }).filter(Boolean).join(", ");
        }

        return propiedades.servicios || "Sin servicios";
    }

    function obtenerIdsServicios(propiedades) {
        if (Array.isArray(propiedades.servicios_ids)) {
            return propiedades.servicios_ids.map(String);
        }

        if (Array.isArray(propiedades.servicios)) {
            return propiedades.servicios.map(function (servicio) {
                return servicio !== null && typeof servicio === "object"
                    ? servicio.id_servicio
                    : servicio;

            }).filter(function (id) {
                return id !== undefined && id !== null && id !== "";

            }).map(String);
        }

        return [];
    }

    function esEventoBloqueo(evento) {
        return Boolean(evento?.extendedProps?.es_bloqueo);
    }

    function colocarTexto(id, valor) {
        $("#" + id).text(
            valor !== null && valor !== undefined && valor !== ""
                ? valor
                : "—"
        );
    }

    function seleccionarValor(id, valor) {
        $("#" + id).val(
            valor !== null && valor !== undefined
                ? String(valor)
                : ""
        );
    }

    function operacionEnCurso() {
        return Boolean(
            window.AgendamientoOperaciones?.estaProcesando?.()
        );
    }


    /* ================================================= */
    /* TRANSICIONES ENTRE MODALES */
    /* ================================================= */

    const $modales = $modalDetalle
        .add($modalDetalleBloqueo)
        .add($modalEditar)
        .add($modalEditarBloqueo)
        .add($modalConfirmarCancelacion)
        .add($modalConfirmarDesbloqueo);

    $modales.on("show.bs.modal", function (evento) {
        if (!evento.isDefaultPrevented()) {
            $(this).attr("data-abriendo-calendario", "1");
        }
    });

    $modales.on("shown.bs.modal", function () {
        $(this).removeAttr("data-abriendo-calendario");
    });

    $modales.on("hide.bs.modal", function (evento) {
        if (!evento.isDefaultPrevented()) {
            $(this).attr("data-cerrando-calendario", "1");
        }
    });

    $modales.on("hidden.bs.modal", function () {
        $(this).removeAttr(
            "data-abriendo-calendario data-cerrando-calendario"
        );
    });

    function mostrarModal($modal) {
        if ($modal.length && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance($modal.get(0)).show();
        }
    }

    function esperarCierreModal($modal) {
        return new Promise(function (resolve) {
            if (!$modal.length || !window.bootstrap) {
                resolve();
                return;
            }

            if ($modal.attr("data-cerrando-calendario") === "1") {
                $modal.one("hidden.bs.modal", resolve);
                return;
            }

            if (!$modal.hasClass("show")) {
                resolve();
                return;
            }

            $modal.one("hidden.bs.modal", resolve);

            function cerrar() {
                bootstrap.Modal.getOrCreateInstance($modal.get(0)).hide();
            }

            if ($modal.attr("data-abriendo-calendario") === "1") {
                $modal.one("shown.bs.modal", cerrar);
            } else {
                cerrar();
            }
        });
    }

    async function cambiarModal($origen, $destino) {
        if (
            transicionModal ||
            operacionEnCurso() ||
            !$destino.length
        ) {
            return;
        }

        transicionModal = true;

        try {
            await esperarCierreModal($origen);
            mostrarModal($destino);

        } finally {
            transicionModal = false;
        }
    }


    /* ================================================= */
    /* FORMULARIOS DE EDICIÓN */
    /* ================================================= */

    function seleccionarServicios(idsServicios) {
        const ids = Array.isArray(idsServicios)
            ? idsServicios.map(String)
            : [];

        if (
            typeof window.AgendamientoServicios?.seleccionarEditar ===
            "function"
        ) {
            window.AgendamientoServicios.seleccionarEditar(ids);
            return;
        }

        $("#editarServiciosAgendamiento .agendamiento-servicio-check")
            .each(function () {
                $(this).prop(
                    "checked",
                    ids.includes(String($(this).val()))
                );
            });
    }

    function llenarFormularioEditar(evento) {
        const propiedades = evento.extendedProps || {};

        seleccionarValor("editarIdAgendamiento", evento.id);
        seleccionarValor("editarClienteAgendamiento", propiedades.id_cliente);
        seleccionarValor("editarUsuarioAsignado", propiedades.id_usuario_asignado);
        seleccionarValor("editarTipoAgendamiento", propiedades.id_tipo_agendamiento);
        seleccionarValor("editarEstadoAgendamiento", propiedades.id_estado_agendamiento);

        seleccionarValor(
            "editarFechaAgendamiento",
            evento.start ? obtenerFechaLocal(evento.start) : ""
        );

        seleccionarValor(
            "editarHoraAgendamiento",
            evento.start ? obtenerHoraLocal(evento.start) : ""
        );

        seleccionarValor(
            "editarObservacionAgendamiento",
            propiedades.observacion_inicial
        );

        seleccionarServicios(obtenerIdsServicios(propiedades));

        seleccionarValor(
            "editarMontoTotal",
            Number(propiedades.monto_total || 0).toFixed(2)
        );

        seleccionarValor(
            "editarDuracionAgendamiento",
            calcularDuracionMinutos(evento)
        );

        window.AgendamientoServicios?.configurarEditar?.();
    }

    function llenarFormularioEditarBloqueo(evento) {
        const propiedades = evento.extendedProps || {};

        seleccionarValor("editarIdBloqueo", propiedades.id_bloqueo);

        seleccionarValor(
            "editarBloqueoUsuarioEspecialista",
            propiedades.id_usuario_especialista
        );

        seleccionarValor(
            "editarBloqueoFecha",
            evento.start ? obtenerFechaLocal(evento.start) : ""
        );

        seleccionarValor(
            "editarBloqueoHoraInicio",
            evento.start ? obtenerHoraLocal(evento.start) : ""
        );

        seleccionarValor(
            "editarBloqueoHoraFin",
            evento.end ? obtenerHoraLocal(evento.end) : ""
        );

        seleccionarValor("editarBloqueoMotivo", propiedades.motivo);
    }


    /* ================================================= */
    /* DETALLES DE BLOQUEOS Y AGENDAMIENTOS */
    /* ================================================= */

    function mostrarDetalleBloqueo(evento) {
        if (operacionEnCurso() || transicionModal) {
            return;
        }

        eventoSeleccionado = evento;

        const propiedades = evento.extendedProps || {};

        colocarTexto(
            "detalleEspecialistaBloqueo",
            propiedades.especialista
        );

        colocarTexto(
            "detalleFechaBloqueo",
            formatearFecha(evento.start)
        );

        colocarTexto(
            "detalleHorarioBloqueo",
            evento.start && evento.end
                ? `${formatearHora(evento.start)} - ${formatearHora(evento.end)}`
                : "—"
        );

        colocarTexto(
            "detalleMotivoBloqueo",
            propiedades.motivo || "Sin motivo registrado"
        );

        colocarTexto(
            "detalleEstadoBloqueo",
            propiedades.estado_bloqueo || "BLOQUEADO"
        );

        $desbloquearId.val(propiedades.id_bloqueo || "");

        llenarFormularioEditarBloqueo(evento);
        mostrarModal($modalDetalleBloqueo);
    }

    function mostrarDetalle(evento) {
        if (operacionEnCurso() || transicionModal) {
            return;
        }

        if (esEventoBloqueo(evento)) {
            mostrarDetalleBloqueo(evento);
            return;
        }

        eventoSeleccionado = evento;

        const propiedades = evento.extendedProps || {};

        colocarTexto(
            "detalleClienteAgendamiento",
            propiedades.cliente || evento.title || "Sin cliente"
        );

        colocarTexto(
            "detalleServiciosAgendamiento",
            obtenerServiciosTexto(propiedades)
        );

        colocarTexto(
            "detalleFechaHoraAgendamiento",
            evento.start
                ? `${formatearFecha(evento.start)} - ${formatearHora(evento.start)}`
                : "—"
        );

        colocarTexto(
            "detalleEspecialistaAgendamiento",
            propiedades.especialista
        );

        colocarTexto(
            "detalleTipoAgendamiento",
            propiedades.tipo_agendamiento || propiedades.tipo
        );

        colocarTexto(
            "detalleTelefonoAgendamiento",
            propiedades.telefono
        );

        colocarTexto(
            "detalleObservacionAgendamiento",
            propiedades.observacion_inicial || propiedades.observacion
        );

        colocarTexto(
            "detalleDuracionAgendamiento",
            `${calcularDuracionMinutos(evento)} minutos`
        );

        colocarTexto(
            "detalleMontoAgendamiento",
            Number(propiedades.monto_total || 0).toFixed(2)
        );

        const estado = propiedades.estado || "AGENDADA";

        $("#detalleEstadoAgendamiento")
            .attr("class", `badge ${obtenerClaseEstado(estado)}`)
            .text(estado);

        $cancelarId.val(evento.id);

        llenarFormularioEditar(evento);
        mostrarModal($modalDetalle);
    }


    /* ================================================= */
    /* LISTA DE AGENDAMIENTOS DE HOY */
    /* ================================================= */

    function obtenerEventosDeHoy() {
        const hoy = obtenerFechaLocal(new Date());

        return calendario.getEvents().filter(function (evento) {
            if (!evento.start || esEventoBloqueo(evento)) {
                return false;
            }

            const estado = String(
                evento.extendedProps.estado || ""
            ).trim().toUpperCase();

            return estado !== "CANCELADA" &&
                obtenerFechaLocal(evento.start) === hoy;

        }).sort(function (a, b) {
            return a.start - b.start;
        });
    }

    function crearEstadoVacio() {
        return $("<li>", {
            class: "list-group-item text-center agendamiento-loading-text"
        }).append(
            $("<i>", {
                class: "bi bi-calendar-x me-2",
                "aria-hidden": "true"
            }),

            $("<span>").text("No hay agendamientos para mostrar.")
        );
    }

    function crearBotonDetalle(evento) {
        const $boton = $("<button>", {
            type: "button",
            class: "btn btn-sm btn-outline-secondary btnDetalleAgendamientoLista",
            title: "Ver detalle",
            "aria-label": "Ver detalle del agendamiento",
            "data-id-agendamiento": String(evento.id)
        });

        const $plantilla = $("#plantillaIconoDetalleAgendamiento");

        /* El dibujo del ícono se conserva en la vista */
        if ($plantilla.length) {
            $boton.append($plantilla.contents().clone());
        } else {
            $boton.text("Ver detalle");
        }

        return $boton;
    }

    function renderizarLista() {
        if (!$lista.length || !calendario) {
            return;
        }

        const filtro = String(
            $inputBusqueda.val() || ""
        ).trim().toLowerCase();

        let eventos = obtenerEventosDeHoy();

        if (filtro) {
            eventos = eventos.filter(function (evento) {
                const propiedades = evento.extendedProps || {};

                const contenido = [
                    evento.title,
                    propiedades.cliente,
                    propiedades.especialista,
                    propiedades.estado,
                    propiedades.tipo_agendamiento,
                    propiedades.observacion_inicial,
                    obtenerServiciosTexto(propiedades)
                ].join(" ").toLowerCase();

                return contenido.includes(filtro);
            });
        }

        $lista.empty();

        if (!eventos.length) {
            $lista.append(crearEstadoVacio());
            return;
        }

        eventos.forEach(function (evento) {
            const propiedades = evento.extendedProps || {};
            const estado = propiedades.estado || "AGENDADA";

            const $informacion = $("<div>").append(
                $("<h6>", {
                    class: "mb-1"
                }).text(
                    `${formatearHora(evento.start)} - ${propiedades.cliente || evento.title}`
                ),

                $("<small>", {
                    class: "d-block"
                }).text(obtenerServiciosTexto(propiedades)),

                $("<small>", {
                    class: "d-block"
                }).text(propiedades.especialista || "")
            );

            const $acciones = $("<div>", {
                class: "d-flex align-items-center gap-2"
            }).append(
                $("<span>", {
                    class: `badge ${obtenerClaseEstado(estado)}`
                }).text(estado),

                crearBotonDetalle(evento)
            );

            $lista.append(
                $("<li>", {
                    class: "list-group-item d-flex justify-content-between align-items-center gap-3"
                }).append($informacion, $acciones)
            );
        });
    }

    $lista.on("click", ".btnDetalleAgendamientoLista", function () {
        const evento = calendario.getEventById(
            String($(this).attr("data-id-agendamiento"))
        );

        if (evento) {
            mostrarDetalle(evento);
        }
    });


    /* ================================================= */
    /* CONSULTAR Y ACTUALIZAR EL CALENDARIO */
    /* ================================================= */

    function ocultarMensaje($contenedor) {
        $contenedor.attr("class", "alert d-none mb-0").empty();
    }

    function mostrarError($contenedor, mensaje) {
        $contenedor
            .attr("class", "alert alert-danger mb-0")
            .attr("role", "alert")
            .text(mensaje);
    }

    function mostrarErrorCalendario(mensaje) {
        if ($mensajeCalendario.length) {
            mostrarError($mensajeCalendario, mensaje);
        } else {
            $ultimaActualizacion.text(mensaje);
        }
    }

    async function actualizarCalendario() {
        const operaciones = window.AgendamientoOperaciones;

        if (
            !calendario ||
            typeof operaciones?.solicitar !== "function"
        ) {
            mostrarErrorCalendario(
                "No fue posible preparar la actualización del calendario. Recargue la página."
            );

            return false;
        }

        numeroConsulta += 1;

        const consultaActual = numeroConsulta;

        if (peticionCalendario) {
            peticionCalendario.cancelar();
        }

        try {
            peticionCalendario = operaciones.solicitar(
                "Index.php?url=agendamiento",
                {
                    method: "GET",
                    data: {
                        accion: "consultar_calendario"
                    }
                }
            );

            const respuesta = await peticionCalendario;

            if (consultaActual !== numeroConsulta) {
                return false;
            }

            if (
                !respuesta ||
                respuesta.ok !== true ||
                !Array.isArray(respuesta.eventos)
            ) {
                throw new Error(
                    "La respuesta del calendario no es válida."
                );
            }

            const idSeleccionado = eventoSeleccionado
                ? String(eventoSeleccionado.id)
                : null;

            /* Reemplazar una sola fuente para evitar eventos duplicados */
            calendario.batchRendering(function () {
                calendario.getEventSourceById(FUENTE_CALENDARIO)?.remove();

                calendario.addEventSource({
                    id: FUENTE_CALENDARIO,
                    events: respuesta.eventos
                });
            });

            eventoSeleccionado = idSeleccionado
                ? calendario.getEventById(idSeleccionado)
                : null;

            ocultarMensaje($mensajeCalendario);
            actualizarHoraTexto();

            return true;

        } catch (error) {
            if (consultaActual !== numeroConsulta) {
                return false;
            }

            if (error.status === 401) {
                window.location.assign(
                    "Index.php?url=login&sesion=expirada"
                );

                return false;
            }

            mostrarErrorCalendario(
                error.responseJSON?.mensaje ||
                "No fue posible actualizar el calendario. Se conservan los datos mostrados anteriormente."
            );

            return false;

        } finally {
            if (consultaActual === numeroConsulta) {
                peticionCalendario = null;
            }
        }
    }

    window.AgendamientoCalendario = {
        actualizar: actualizarCalendario
    };


    /* ================================================= */
    /* INICIALIZACIÓN DE FULLCALENDAR */
    /* ================================================= */

    calendario = new FullCalendar.Calendar($calendario.get(0), {
        initialView: "dayGridMonth",
        locale: "es",
        timeZone: "local",
        height: obtenerAlturaCalendario(),
        expandRows: false,
        fixedWeekCount: false,
        showNonCurrentDates: false,

        slotMinTime: "06:00:00",
        slotMaxTime: "22:00:00",
        slotDuration: "00:30:00",
        scrollTime: "06:00:00",
        scrollTimeReset: false,

        editable: false,
        eventStartEditable: false,
        eventDurationEditable: false,

        eventSources: [
            {
                id: FUENTE_CALENDARIO,
                events: eventosIniciales
            }
        ],

        headerToolbar: obtenerToolbarCalendario(),

        buttonText: {
            today: "Hoy",
            month: "Mes",
            week: "Semana",
            day: "Día"
        },

        eventTimeFormat: {
            hour: "2-digit",
            minute: "2-digit",
            hour12: true
        },

        eventDidMount: function (info) {
            if (esEventoBloqueo(info.event)) {
                $(info.el).addClass("evento-bloqueo-horario");
            } else {
                const color = obtenerColorEstado(
                    info.event.extendedProps.estado
                );

                $(info.el).css({
                    backgroundColor: color,
                    borderColor: color
                });
            }
        },

        eventClick: function (info) {
            mostrarDetalle(info.event);
        },

        eventsSet: renderizarLista
    });

    calendario.render();
    renderizarLista();
    actualizarHoraTexto();


    /* ================================================= */
    /* AJUSTE DE TAMAÑO Y BÚSQUEDA */
    /* ================================================= */

    let temporizadorCalendario = null;

    $(window).on("resize", function () {
        window.clearTimeout(temporizadorCalendario);

        temporizadorCalendario = window.setTimeout(function () {
            const esMovil = calendarioEsMovil();

            calendario.setOption("height", obtenerAlturaCalendario());

            if (esMovil !== calendarioEstabaEnMovil) {
                calendarioEstabaEnMovil = esMovil;

                calendario.setOption(
                    "headerToolbar",
                    obtenerToolbarCalendario()
                );
            }

            calendario.updateSize();
        }, 180);
    });

    $btnBuscar.on("click", function () {
        $contenedorBusqueda.toggleClass("d-none");

        if (!$contenedorBusqueda.hasClass("d-none")) {
            $inputBusqueda.trigger("focus");
        }
    });

    $inputBusqueda.on("input", renderizarLista);
    $btnEjecutarBusqueda.on("click", renderizarLista);


    /* ================================================= */
    /* ABRIR FORMULARIOS DE EDICIÓN */
    /* ================================================= */

    $btnEditar.on("click", function () {
        if (
            !eventoSeleccionado ||
            esEventoBloqueo(eventoSeleccionado) ||
            operacionEnCurso()
        ) {
            return;
        }

        llenarFormularioEditar(eventoSeleccionado);

        cambiarModal($modalDetalle, $modalEditar);
    });

    $btnEditarBloqueo.on("click", function () {
        if (
            !esEventoBloqueo(eventoSeleccionado) ||
            operacionEnCurso()
        ) {
            return;
        }

        llenarFormularioEditarBloqueo(eventoSeleccionado);

        cambiarModal($modalDetalleBloqueo, $modalEditarBloqueo);
    });


    /* ================================================= */
    /* CONFIRMACIÓN DE CANCELACIÓN */
    /* ================================================= */

    $btnCancelar.on("click", function () {
        if (
            !eventoSeleccionado ||
            esEventoBloqueo(eventoSeleccionado) ||
            operacionEnCurso() ||
            transicionModal ||
            !$modalConfirmarCancelacion.length
        ) {
            return;
        }

        $cancelarId.val(eventoSeleccionado.id);
        cancelacionEnviada = false;

        $btnConfirmarCancelacion.prop("disabled", false);

        ocultarMensaje($mensajeCancelacion);
        cambiarModal($modalDetalle, $modalConfirmarCancelacion);
    });

    async function confirmarCancelacion() {
        const id = String($cancelarId.val() || "");
        const operaciones = window.AgendamientoOperaciones;

        if (!id || cancelacionEnviada || operacionEnCurso()) {
            return;
        }

        if (
            !$formCancelar.length ||
            typeof operaciones?.enviar !== "function"
        ) {
            mostrarError(
                $mensajeCancelacion,
                "No fue posible preparar la cancelación. Recargue la página."
            );

            return;
        }

        cancelacionEnviada = true;

        try {
            const correcto = await operaciones.enviar($formCancelar, {
                modal: $modalConfirmarCancelacion,
                contenedor: $mensajeCancelacion,
                titulo: "Agendamiento cancelado"
            });

            if (correcto) {
                /* Retirar la cita confirmada aunque falle la actualización general */
                calendario.getEventById(id)?.remove();

                if (String(eventoSeleccionado?.id || "") === id) {
                    eventoSeleccionado = null;
                }

                $cancelarId.val("");
            }

        } catch (error) {
            mostrarError(
                $mensajeCancelacion,
                "No se recibió confirmación de la cancelación. Actualice el calendario antes de intentar nuevamente."
            );

        } finally {
            cancelacionEnviada = false;
        }
    }

    $btnConfirmarCancelacion.on("click", confirmarCancelacion);

    $formCancelar.on("submit", function (evento) {
        evento.preventDefault();
        confirmarCancelacion();
    });

    $modalConfirmarCancelacion.on("hidden.bs.modal", function () {
        if (cancelacionEnviada) {
            return;
        }

        $cancelarId.val("");
        $btnConfirmarCancelacion.prop("disabled", false);

        ocultarMensaje($mensajeCancelacion);

        /* Volver al detalle cuando se cierra la confirmación */
        if (
            eventoSeleccionado &&
            !esEventoBloqueo(eventoSeleccionado)
        ) {
            mostrarModal($modalDetalle);
        }
    });


    /* ================================================= */
    /* CONFIRMACIÓN DE DESBLOQUEO */
    /* ================================================= */

    $btnDesbloquear.on("click", function () {
        if (
            !esEventoBloqueo(eventoSeleccionado) ||
            operacionEnCurso() ||
            transicionModal ||
            !$modalConfirmarDesbloqueo.length
        ) {
            return;
        }

        $desbloquearId.val(
            eventoSeleccionado.extendedProps.id_bloqueo
        );

        desbloqueoEnviado = false;

        $btnConfirmarDesbloqueo.prop("disabled", false);

        ocultarMensaje($mensajeDesbloqueo);

        cambiarModal(
            $modalDetalleBloqueo,
            $modalConfirmarDesbloqueo
        );
    });

    async function confirmarDesbloqueo() {
        const id = String($desbloquearId.val() || "");
        const operaciones = window.AgendamientoOperaciones;

        if (!id || desbloqueoEnviado || operacionEnCurso()) {
            return;
        }

        if (
            !$formDesbloquear.length ||
            typeof operaciones?.enviar !== "function"
        ) {
            mostrarError(
                $mensajeDesbloqueo,
                "No fue posible preparar el desbloqueo. Recargue la página."
            );

            return;
        }

        desbloqueoEnviado = true;

        try {
            const correcto = await operaciones.enviar($formDesbloquear, {
                modal: $modalConfirmarDesbloqueo,
                contenedor: $mensajeDesbloqueo,
                titulo: "Horario desbloqueado"
            });

            if (correcto) {
                const idEvento = "bloqueo-" + id;

                calendario.getEventById(idEvento)?.remove();

                if (String(eventoSeleccionado?.id || "") === idEvento) {
                    eventoSeleccionado = null;
                }

                $desbloquearId.val("");
            }

        } catch (error) {
            mostrarError(
                $mensajeDesbloqueo,
                "No se recibió confirmación del desbloqueo. Actualice el calendario antes de intentar nuevamente."
            );

        } finally {
            desbloqueoEnviado = false;
        }
    }

    $btnConfirmarDesbloqueo.on("click", confirmarDesbloqueo);

    $formDesbloquear.on("submit", function (evento) {
        evento.preventDefault();
        confirmarDesbloqueo();
    });

    $modalConfirmarDesbloqueo.on("hidden.bs.modal", function () {
        if (!desbloqueoEnviado) {
            $desbloquearId.val("");
            $btnConfirmarDesbloqueo.prop("disabled", false);

            ocultarMensaje($mensajeDesbloqueo);
        }
    });
});