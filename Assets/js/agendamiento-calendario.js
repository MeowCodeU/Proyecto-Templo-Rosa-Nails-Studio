document.addEventListener("DOMContentLoaded", function () {
    const calendarEl = document.getElementById("calendar");

    const listaAgendamientos = document.getElementById(
        "agendamientos-list"
    );

    const btnBuscar = document.getElementById(
        "btnBuscarAgendamientos"
    );

    const contenedorBusqueda = document.getElementById(
        "contenedorBusquedaAgendamientos"
    );

    const inputBusqueda = document.getElementById(
        "buscarAgendamiento"
    );

    const btnEjecutarBusqueda = document.getElementById(
        "btnEjecutarBusquedaAgendamientos"
    );

    const ultimaActualizacion = document.getElementById(
        "ultima-actualizacion"
    );

    const modalDetalleEl = document.getElementById(
        "modalDetalleAgendamiento"
    );

    const modalEditarEl = document.getElementById(
        "modalEditarAgendamiento"
    );

    const btnEditar = document.getElementById(
        "btnEditarAgendamiento"
    );

    const btnCancelar = document.getElementById(
        "btnCancelarAgendamiento"
    );

    const modalDetalleBloqueoEl = document.getElementById(
        "modalDetalleBloqueoHorario"
    );

    const modalEditarBloqueoEl = document.getElementById(
        "modalEditarBloqueoHorario"
    );

    const btnEditarBloqueo = document.getElementById(
        "btnEditarBloqueoHorario"
    );

    const btnDesbloquear = document.getElementById(
        "btnDesbloquearHorario"
    );

    const formDesbloquear = document.getElementById(
        "formDesbloquearHorario"
    );

    const desbloquearId = document.getElementById(
        "desbloquearIdBloqueo"
    );

    const formMover = document.getElementById(
        "formMoverAgendamiento"
    );

    const moverId = document.getElementById(
        "moverIdAgendamiento"
    );

    const moverFecha = document.getElementById(
        "moverFechaAgendamiento"
    );

    const moverHora = document.getElementById(
        "moverHoraAgendamiento"
    );

    const moverDuracion = document.getElementById(
        "moverDuracionAgendamiento"
    );

    const formCancelar = document.getElementById(
        "formCancelarAgendamiento"
    );

    const cancelarId = document.getElementById(
        "cancelarIdAgendamiento"
    );

    let eventoSeleccionado = null;
    let calendar = null;

    const LIMITE_CALENDARIO_MOVIL = 575.98;
    const LIMITE_CALENDARIO_TABLET = 1200;

    function calendarioEsMovil() {
        return (
            window.innerWidth <=
            LIMITE_CALENDARIO_MOVIL
        );
    }

    function obtenerToolbarCalendario() {
        if (calendarioEsMovil()) {
            return {
                left: "prev",
                center: "title",
                right: "next"
            };
        }

        return {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay"
        };
    }

    function obtenerAlturaCalendario() {
        if (calendarioEsMovil()) {
            return 350;
        }

        if (
            window.innerWidth <=
            LIMITE_CALENDARIO_TABLET
        ) {
            return 440;
        }

        return 470;
    }

    let calendarioEstabaEnMovil =
        calendarioEsMovil();

    if (
        !calendarEl ||
        typeof FullCalendar === "undefined"
    ) {
        return;
    }

    const eventosIniciales = Array.isArray(
        window.agendamientosCalendario
    )
        ? window.agendamientosCalendario
        : [];

    function obtenerFechaLocal(fecha) {
        const anio = fecha.getFullYear();

        const mes = String(
            fecha.getMonth() + 1
        ).padStart(2, "0");

        const dia = String(
            fecha.getDate()
        ).padStart(2, "0");

        return `${anio}-${mes}-${dia}`;
    }

    function obtenerHoraLocal(fecha) {
        const horas = String(
            fecha.getHours()
        ).padStart(2, "0");

        const minutos = String(
            fecha.getMinutes()
        ).padStart(2, "0");

        return `${horas}:${minutos}`;
    }

    function formatearFecha(fecha) {
        if (!fecha) {
            return "—";
        }

        return fecha.toLocaleDateString("es-VE", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric"
        });
    }

    function formatearHora(fecha) {
        if (!fecha) {
            return "—";
        }

        return fecha.toLocaleTimeString("es-VE", {
            hour: "2-digit",
            minute: "2-digit"
        });
    }

    function calcularDuracionMinutos(evento) {
        if (evento.start && evento.end) {
            const diferencia =
                evento.end.getTime() -
                evento.start.getTime();

            return Math.max(
                1,
                Math.round(diferencia / 60000)
            );
        }

        const duracion = parseInt(
            evento.extendedProps.duracion_minutos || 0,
            10
        );

        return Number.isNaN(duracion)
            ? 0
            : duracion;
    }

    function actualizarHoraTexto() {
        if (!ultimaActualizacion) {
            return;
        }

        const ahora = new Date();

        ultimaActualizacion.textContent =
            "Actualizado: " +
            ahora.toLocaleTimeString("es-VE", {
                hour: "2-digit",
                minute: "2-digit"
            });
    }

    function obtenerClaseEstado(estado) {
        const nombre = String(
            estado || ""
        )
            .trim()
            .toUpperCase();

        switch (nombre) {
            case "AGENDADA":
                return "estado-programada";

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

            default:
                return "estado-programada";
        }
    }

    function obtenerColorEstado(estado) {
        const nombre = String(
            estado || ""
        )
            .trim()
            .toUpperCase();

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

    function obtenerServiciosTexto(propiedades) {
        const servicios = propiedades.servicios;

        if (Array.isArray(servicios)) {
            return servicios
                .map(function (servicio) {
                    if (
                        typeof servicio === "object" &&
                        servicio !== null
                    ) {
                        return (
                            servicio.nombre_servicio ||
                            servicio.nombre ||
                            ""
                        );
                    }

                    return String(servicio);
                })
                .filter(Boolean)
                .join(", ");
        }

        return servicios || "Sin servicios";
    }

    function obtenerIdsServicios(propiedades) {
        if (
            Array.isArray(
                propiedades.servicios_ids
            )
        ) {
            return propiedades.servicios_ids.map(
                String
            );
        }

        if (Array.isArray(propiedades.servicios)) {
            return propiedades.servicios
                .map(function (servicio) {
                    if (
                        typeof servicio === "object" &&
                        servicio !== null
                    ) {
                        return servicio.id_servicio;
                    }

                    return servicio;
                })
                .filter(function (id) {
                    return (
                        id !== undefined &&
                        id !== null &&
                        id !== ""
                    );
                })
                .map(String);
        }

        return [];
    }

    function esEventoBloqueo(evento) {
        return Boolean(
            evento &&
            evento.extendedProps &&
            evento.extendedProps.es_bloqueo
        );
    }


    function colocarTexto(id, valor) {
        const elemento = document.getElementById(id);

        if (!elemento) {
            return;
        }

        elemento.textContent =
            valor !== null &&
            valor !== undefined &&
            valor !== ""
                ? valor
                : "—";
    }

    function seleccionarValor(id, valor) {
        const campo = document.getElementById(id);

        if (!campo) {
            return;
        }

        campo.value =
            valor !== null &&
            valor !== undefined
                ? String(valor)
                : "";
    }

    /* ===================================================== */
    /* SERVICIOS DEL FORMULARIO EDITAR */
    /* ===================================================== */

    function seleccionarServicios(idsServicios) {
        const ids = Array.isArray(idsServicios)
            ? idsServicios.map(String)
            : [];


        /*
         * agendamiento.js controla ahora el selector
         * personalizado de servicios.
         */
        if (
            window.AgendamientoServicios &&
            typeof window.AgendamientoServicios
                .seleccionarEditar === "function"
        ) {
            window.AgendamientoServicios
                .seleccionarEditar(ids);

            return;
        }


        /*
         * Respaldo por si la API de agendamiento.js
         * todavía no estuviera disponible.
         */
        const contenedor = document.getElementById(
            "editarServiciosAgendamiento"
        );


        if (!contenedor) {
            return;
        }


        const checks = contenedor.querySelectorAll(
            ".agendamiento-servicio-check"
        );


        checks.forEach(
            function (check) {
                check.checked = ids.includes(
                    String(check.value)
                );
            }
        );
    }


    /* ===================================================== */
    /* LLENAR FORMULARIO EDITAR */
    /* ===================================================== */

    function llenarFormularioEditar(evento) {
        const propiedades =
            evento.extendedProps || {};


        seleccionarValor(
            "editarIdAgendamiento",
            evento.id
        );


        seleccionarValor(
            "editarClienteAgendamiento",
            propiedades.id_cliente
        );


        seleccionarValor(
            "editarUsuarioAsignado",
            propiedades.id_usuario_asignado
        );


        seleccionarValor(
            "editarTipoAgendamiento",
            propiedades.id_tipo_agendamiento
        );


        seleccionarValor(
            "editarEstadoAgendamiento",
            propiedades.id_estado_agendamiento
        );


        seleccionarValor(
            "editarFechaAgendamiento",
            evento.start
                ? obtenerFechaLocal(evento.start)
                : ""
        );


        seleccionarValor(
            "editarHoraAgendamiento",
            evento.start
                ? obtenerHoraLocal(evento.start)
                : ""
        );


        seleccionarValor(
            "editarObservacionAgendamiento",
            propiedades.observacion_inicial
        );


        seleccionarServicios(
            obtenerIdsServicios(propiedades)
        );


        seleccionarValor(
            "editarMontoTotal",
            Number(
                propiedades.monto_total || 0
            ).toFixed(2)
        );


        seleccionarValor(
            "editarDuracionAgendamiento",
            calcularDuracionMinutos(evento)
        );


        /*
         * Después de colocar los datos, se informa
         * a agendamiento.js para actualizar el selector
         * personalizado de servicios y la duración.
         */
        if (
            window.AgendamientoServicios &&
            typeof window.AgendamientoServicios
                .configurarEditar === "function"
        ) {
            window.AgendamientoServicios
                .configurarEditar();
        }
    }

    function llenarFormularioEditarBloqueo(evento) {
        const propiedades =
            evento.extendedProps || {};

        seleccionarValor(
            "editarIdBloqueo",
            propiedades.id_bloqueo
        );

        seleccionarValor(
            "editarBloqueoUsuarioEspecialista",
            propiedades.id_usuario_especialista
        );

        seleccionarValor(
            "editarBloqueoFecha",
            evento.start
                ? obtenerFechaLocal(evento.start)
                : ""
        );

        seleccionarValor(
            "editarBloqueoHoraInicio",
            evento.start
                ? obtenerHoraLocal(evento.start)
                : ""
        );

        seleccionarValor(
            "editarBloqueoHoraFin",
            evento.end
                ? obtenerHoraLocal(evento.end)
                : ""
        );

        seleccionarValor(
            "editarBloqueoMotivo",
            propiedades.motivo
        );
    }


    function mostrarDetalleBloqueo(evento) {
        eventoSeleccionado = evento;

        const propiedades =
            evento.extendedProps || {};

        colocarTexto(
            "detalleEspecialistaBloqueo",
            propiedades.especialista
        );

        colocarTexto(
            "detalleFechaBloqueo",
            evento.start
                ? formatearFecha(evento.start)
                : "—"
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

        const estadoBloqueo = document.getElementById(
            "detalleEstadoBloqueo"
        );

        if (estadoBloqueo) {
            estadoBloqueo.textContent =
                propiedades.estado_bloqueo ||
                "BLOQUEADO";
        }

        if (desbloquearId) {
            desbloquearId.value =
                propiedades.id_bloqueo || "";
        }

        llenarFormularioEditarBloqueo(evento);

        if (modalDetalleBloqueoEl) {
            bootstrap.Modal
                .getOrCreateInstance(
                    modalDetalleBloqueoEl
                )
                .show();
        }
    }


    function mostrarDetalle(evento) {
        if (esEventoBloqueo(evento)) {
            mostrarDetalleBloqueo(evento);
            return;
        }

        eventoSeleccionado = evento;

        const propiedades =
            evento.extendedProps || {};

        const cliente =
            propiedades.cliente ||
            evento.title ||
            "Sin cliente";

        colocarTexto(
            "detalleClienteAgendamiento",
            cliente
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
            propiedades.tipo_agendamiento ||
                propiedades.tipo
        );

        colocarTexto(
            "detalleTelefonoAgendamiento",
            propiedades.telefono
        );

        colocarTexto(
            "detalleObservacionAgendamiento",
            propiedades.observacion_inicial ||
                propiedades.observacion
        );

        colocarTexto(
            "detalleDuracionAgendamiento",
            `${calcularDuracionMinutos(evento)} minutos`
        );

        colocarTexto(
            "detalleMontoAgendamiento",
            Number(
                propiedades.monto_total || 0
            ).toFixed(2)
        );

        const estado =
            propiedades.estado || "AGENDADA";

        const detalleEstado = document.getElementById(
            "detalleEstadoAgendamiento"
        );

        if (detalleEstado) {
            detalleEstado.textContent = estado;

            detalleEstado.className =
                `badge ${obtenerClaseEstado(estado)}`;
        }

        if (cancelarId) {
            cancelarId.value = evento.id;
        }

        llenarFormularioEditar(evento);

        if (modalDetalleEl) {
            bootstrap.Modal
                .getOrCreateInstance(
                    modalDetalleEl
                )
                .show();
        }
    }

    function obtenerEventosDeHoy() {
        if (!calendar) {
            return [];
        }

        const hoy = obtenerFechaLocal(
            new Date()
        );

        return calendar
            .getEvents()
            .filter(function (evento) {
                if (
                    !evento.start ||
                    esEventoBloqueo(evento)
                ) {
                    return false;
                }

                return (
                    obtenerFechaLocal(evento.start) ===
                    hoy
                );
            })
            .sort(function (a, b) {
                return a.start - b.start;
            });
    }

    function crearEstadoVacio() {
        const itemVacio =
            document.createElement("li");

        itemVacio.className =
            "list-group-item text-center agendamiento-loading-text";

        const icono =
            document.createElement("i");

        icono.className =
            "bi bi-calendar-x me-2";

        itemVacio.appendChild(icono);

        itemVacio.appendChild(
            document.createTextNode(
                "No hay agendamientos para mostrar."
            )
        );

        return itemVacio;
    }

    function renderizarLista() {
        if (!listaAgendamientos) {
            return;
        }

        const filtro = inputBusqueda
            ? inputBusqueda.value
                  .trim()
                  .toLowerCase()
            : "";

        let eventos = obtenerEventosDeHoy();

        if (filtro !== "") {
            eventos = eventos.filter(
                function (evento) {
                    const propiedades =
                        evento.extendedProps || {};

                    const contenido = [
                        evento.title,
                        propiedades.cliente,
                        propiedades.especialista,
                        propiedades.estado,
                        propiedades.tipo_agendamiento,
                        propiedades.observacion_inicial,
                        obtenerServiciosTexto(
                            propiedades
                        )
                    ]
                        .join(" ")
                        .toLowerCase();

                    return contenido.includes(
                        filtro
                    );
                }
            );
        }

        listaAgendamientos.innerHTML = "";

        if (eventos.length === 0) {
            listaAgendamientos.appendChild(
                crearEstadoVacio()
            );

            actualizarHoraTexto();

            return;
        }

        eventos.forEach(function (evento) {
            const propiedades =
                evento.extendedProps || {};

            const item =
                document.createElement("li");

            item.className =
                "list-group-item d-flex justify-content-between align-items-center gap-3";

            const informacion =
                document.createElement("div");

            const titulo =
                document.createElement("h6");

            titulo.className = "mb-1";

            titulo.textContent =
                `${formatearHora(evento.start)} - ` +
                `${propiedades.cliente || evento.title}`;

            const servicios =
                document.createElement("small");

            servicios.className = "d-block";

            servicios.textContent =
                obtenerServiciosTexto(
                    propiedades
                );

            const especialista =
                document.createElement("small");

            especialista.className = "d-block";

            especialista.textContent =
                propiedades.especialista || "";

            informacion.appendChild(titulo);
            informacion.appendChild(servicios);
            informacion.appendChild(
                especialista
            );

            const acciones =
                document.createElement("div");

            acciones.className =
                "d-flex align-items-center gap-2";

            const badge =
                document.createElement("span");

            const estado =
                propiedades.estado || "AGENDADA";

            badge.className =
                `badge ${obtenerClaseEstado(estado)}`;

            badge.textContent = estado;

            const boton =
                document.createElement("button");

            boton.type = "button";

            boton.className =
                "btn btn-sm btn-outline-secondary";

            boton.title = "Ver detalle";

            boton.setAttribute(
                "aria-label",
                "Ver detalle del agendamiento"
            );

            boton.innerHTML = `
                <span
                    class="agendamiento-detalle-icono"
                    aria-hidden="true"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <path
                            d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            fill="none"
                        />

                        <path
                            d="M12 16C10 14.8 8.5 13.5 8.5 11.8C8.5 10.5 9.5 9.5 10.8 9.5C11.5 9.5 12 9.9 12 10.4C12 9.9 12.5 9.5 13.2 9.5C14.5 9.5 15.5 10.5 15.5 11.8C15.5 13.5 14 14.8 12 16Z"
                            fill="currentColor"
                        />
                    </svg>
                </span>
            `;

            boton.addEventListener(
                "click",
                function () {
                    mostrarDetalle(evento);
                }
            );

            acciones.appendChild(badge);
            acciones.appendChild(boton);

            item.appendChild(informacion);
            item.appendChild(acciones);

            listaAgendamientos.appendChild(
                item
            );
        });

        actualizarHoraTexto();
    }

    function enviarCambio(evento) {
        if (
            !formMover ||
            !moverId ||
            !moverFecha ||
            !moverHora ||
            !moverDuracion ||
            !evento.start
        ) {
            return false;
        }

        moverId.value = evento.id;

        moverFecha.value = obtenerFechaLocal(
            evento.start
        );

        moverHora.value = obtenerHoraLocal(
            evento.start
        );

        moverDuracion.value =
            calcularDuracionMinutos(evento);

        formMover.submit();

        return true;
    }

    calendar = new FullCalendar.Calendar(
        calendarEl,
        {
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

            /*
             * Semana y Día se abren desde las 6:00 a. m.
             * y conservan la posición al cambiar de fecha.
             */
            scrollTime: "06:00:00",

            scrollTimeReset: false,

            editable: false,

            eventStartEditable: false,

            eventDurationEditable: false,

            events: eventosIniciales,

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
                    info.el.classList.add(
                        "evento-bloqueo-horario"
                    );

                    return;
                }

                const color =
                    obtenerColorEstado(
                        info.event.extendedProps
                            .estado
                    );

                info.el.style.backgroundColor =
                    color;

                info.el.style.borderColor =
                    color;
            },

            eventClick: function (info) {
                mostrarDetalle(info.event);
            },

            eventsSet: function () {
                renderizarLista();
            }
        }
    );

    calendar.render();

    let temporizadorCalendario = null;

    window.addEventListener(
        "resize",
        function () {
            window.clearTimeout(
                temporizadorCalendario
            );

            temporizadorCalendario =
                window.setTimeout(
                    function () {
                        const calendarioAhoraEsMovil =
                            calendarioEsMovil();

                        calendar.setOption(
                            "height",
                            obtenerAlturaCalendario()
                        );

                        if (
                            calendarioAhoraEsMovil !==
                            calendarioEstabaEnMovil
                        ) {
                            calendarioEstabaEnMovil =
                                calendarioAhoraEsMovil;

                            calendar.setOption(
                                "headerToolbar",
                                obtenerToolbarCalendario()
                            );
                        }

                        calendar.updateSize();
                    },
                    180
                );
        }
    );

    renderizarLista();

    actualizarHoraTexto();

    if (btnBuscar && contenedorBusqueda) {
        btnBuscar.addEventListener(
            "click",
            function () {
                contenedorBusqueda.classList.toggle(
                    "d-none"
                );

                if (
                    !contenedorBusqueda.classList.contains(
                        "d-none"
                    ) &&
                    inputBusqueda
                ) {
                    inputBusqueda.focus();
                }
            }
        );
    }

    if (inputBusqueda) {
        inputBusqueda.addEventListener(
            "input",
            renderizarLista
        );
    }

    if (btnEjecutarBusqueda) {
        btnEjecutarBusqueda.addEventListener(
            "click",
            renderizarLista
        );
    }

    if (btnEditar) {
        btnEditar.addEventListener(
            "click",
            function () {
                if (!eventoSeleccionado) {
                    return;
                }

                llenarFormularioEditar(
                    eventoSeleccionado
                );

                if (modalDetalleEl) {
                    const instanciaDetalle =
                        bootstrap.Modal.getInstance(
                            modalDetalleEl
                        );

                    if (instanciaDetalle) {
                        instanciaDetalle.hide();
                    }
                }

                if (modalEditarEl) {
                    bootstrap.Modal
                        .getOrCreateInstance(
                            modalEditarEl
                        )
                        .show();
                }
            }
        );
    }

    if (btnEditarBloqueo) {
        btnEditarBloqueo.addEventListener(
            "click",
            function () {
                if (
                    !eventoSeleccionado ||
                    !esEventoBloqueo(
                        eventoSeleccionado
                    )
                ) {
                    return;
                }

                llenarFormularioEditarBloqueo(
                    eventoSeleccionado
                );

                if (modalDetalleBloqueoEl) {
                    const instanciaDetalleBloqueo =
                        bootstrap.Modal.getInstance(
                            modalDetalleBloqueoEl
                        );

                    if (instanciaDetalleBloqueo) {
                        instanciaDetalleBloqueo.hide();
                    }
                }

                if (modalEditarBloqueoEl) {
                    bootstrap.Modal
                        .getOrCreateInstance(
                            modalEditarBloqueoEl
                        )
                        .show();
                }
            }
        );
    }


    if (
        btnDesbloquear &&
        formDesbloquear &&
        desbloquearId
    ) {
        btnDesbloquear.addEventListener(
            "click",
            function () {
                if (
                    !eventoSeleccionado ||
                    !esEventoBloqueo(
                        eventoSeleccionado
                    )
                ) {
                    return;
                }

                const confirmar = window.confirm(
                    "¿Desea desbloquear este horario?"
                );

                if (!confirmar) {
                    return;
                }

                desbloquearId.value =
                    eventoSeleccionado
                        .extendedProps
                        .id_bloqueo;

                formDesbloquear.submit();
            }
        );
    }


    if (
        btnCancelar &&
        formCancelar &&
        cancelarId
    ) {
        btnCancelar.addEventListener(
            "click",
            function () {
                if (!eventoSeleccionado) {
                    return;
                }

                const confirmar = window.confirm(
                    "¿Desea cancelar este agendamiento?"
                );

                if (!confirmar) {
                    return;
                }

                cancelarId.value =
                    eventoSeleccionado.id;

                formCancelar.submit();
            }
        );
    }
});