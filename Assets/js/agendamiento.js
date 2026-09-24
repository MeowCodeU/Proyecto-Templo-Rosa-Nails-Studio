document.addEventListener("DOMContentLoaded", function () {
    const formNuevo = document.getElementById(
        "formNuevoAgendamiento"
    );

    const formEditar = document.getElementById(
        "formEditarAgendamiento"
    );

    const tipoNuevo = document.getElementById(
        "nuevoTipoAgendamiento"
    );

    const tipoEditar = document.getElementById(
        "editarTipoAgendamiento"
    );

    const estadoNuevo = document.getElementById(
        "nuevoEstadoAgendamiento"
    );

    const clienteNuevo = document.getElementById(
        "nuevoCliente"
    );

    const clienteEditar = document.getElementById(
        "editarClienteAgendamiento"
    );

    const montoNuevo = document.getElementById(
        "nuevoMontoTotal"
    );

    const montoEditar = document.getElementById(
        "editarMontoTotal"
    );

    const duracionNueva = document.getElementById(
        "nuevaDuracionAgendamiento"
    );

    const duracionEditar = document.getElementById(
        "editarDuracionAgendamiento"
    );

    const fechaNueva = document.getElementById(
        "nuevaFechaAgendamiento"
    );

    const fechaEditar = document.getElementById(
        "editarFechaAgendamiento"
    );

    const modalNuevo = document.getElementById(
        "modalNuevoAgendamiento"
    );

    const modalEditar = document.getElementById(
        "modalEditarAgendamiento"
    );

    const formNuevoBloqueo = document.getElementById(
        "formNuevoBloqueoHorario"
    );

    const formEditarBloqueo = document.getElementById(
        "formEditarBloqueoHorario"
    );

    const fechaNuevoBloqueo = document.getElementById(
        "bloqueoFecha"
    );

    const fechaEditarBloqueo = document.getElementById(
        "editarBloqueoFecha"
    );

    const horaInicioNuevoBloqueo = document.getElementById(
        "bloqueoHoraInicio"
    );

    const horaFinNuevoBloqueo = document.getElementById(
        "bloqueoHoraFin"
    );

    const horaInicioEditarBloqueo = document.getElementById(
        "editarBloqueoHoraInicio"
    );

    const horaFinEditarBloqueo = document.getElementById(
        "editarBloqueoHoraFin"
    );

    const modalNuevoBloqueo = document.getElementById(
        "modalNuevoBloqueoHorario"
    );

    const modalEditarBloqueo = document.getElementById(
        "modalEditarBloqueoHorario"
    );


    /* ===================================================== */
    /* FECHA MÍNIMA */
    /* ===================================================== */

    function obtenerFechaActual() {
        const hoy = new Date();

        const anio = hoy.getFullYear();

        const mes = String(
            hoy.getMonth() + 1
        ).padStart(2, "0");

        const dia = String(
            hoy.getDate()
        ).padStart(2, "0");

        return `${anio}-${mes}-${dia}`;
    }


    function establecerFechaMinima() {
        const fechaActual = obtenerFechaActual();

        if (fechaNueva) {
            fechaNueva.min = fechaActual;
        }

        if (fechaEditar) {
            fechaEditar.min = fechaActual;
        }

        if (fechaNuevoBloqueo) {
            fechaNuevoBloqueo.min = fechaActual;
        }

        if (fechaEditarBloqueo) {
            fechaEditarBloqueo.min = fechaActual;
        }
    }


    /* ===================================================== */
    /* SELECTOR PERSONALIZADO DE SERVICIOS */
    /* ===================================================== */

    function crearSelectorServicios(configuracion) {
        const contenedor = document.getElementById(
            configuracion.contenedorId
        );

        const toggle = document.getElementById(
            configuracion.toggleId
        );

        const texto = document.getElementById(
            configuracion.textoId
        );

        const panel = document.getElementById(
            configuracion.panelId
        );

        const buscador = document.getElementById(
            configuracion.buscadorId
        );

        const lista = document.getElementById(
            configuracion.listaId
        );

        const sinResultados = document.getElementById(
            configuracion.sinResultadosId
        );

        const resumen = document.getElementById(
            configuracion.resumenId
        );

        const feedback = document.getElementById(
            configuracion.feedbackId
        );


        if (
            !contenedor ||
            !toggle ||
            !panel ||
            !lista
        ) {
            return null;
        }


        const checks = Array.from(
            contenedor.querySelectorAll(
                ".agendamiento-servicio-check"
            )
        );


        const opciones = Array.from(
            contenedor.querySelectorAll(
                ".agendamiento-servicio-opcion"
            )
        );


        let deshabilitado = false;


        function obtenerSeleccionados() {
            return checks.filter(
                function (check) {
                    return check.checked;
                }
            );
        }


        function normalizarTexto(valor) {
            return String(valor || "")
                .normalize("NFD")
                .replace(
                    /[\u0300-\u036f]/g,
                    ""
                )
                .trim()
                .toLowerCase();
        }


        function cerrar() {
            panel.hidden = true;

            toggle.setAttribute(
                "aria-expanded",
                "false"
            );

            contenedor.classList.remove(
                "is-open"
            );
        }


        function abrir() {
            if (deshabilitado) {
                return;
            }


            document
                .querySelectorAll(
                    ".agendamiento-servicios-selector"
                )
                .forEach(
                    function (otroSelector) {
                        if (
                            otroSelector ===
                            contenedor
                        ) {
                            return;
                        }


                        const otroPanel =
                            otroSelector.querySelector(
                                ".agendamiento-servicios-panel"
                            );


                        const otroToggle =
                            otroSelector.querySelector(
                                ".agendamiento-servicios-toggle"
                            );


                        if (otroPanel) {
                            otroPanel.hidden = true;
                        }


                        if (otroToggle) {
                            otroToggle.setAttribute(
                                "aria-expanded",
                                "false"
                            );
                        }


                        otroSelector.classList.remove(
                            "is-open"
                        );
                    }
                );


            panel.hidden = false;

            toggle.setAttribute(
                "aria-expanded",
                "true"
            );

            contenedor.classList.add(
                "is-open"
            );


            if (buscador) {
                window.setTimeout(
                    function () {
                        buscador.focus();
                    },
                    0
                );
            }
        }


        function alternar() {
            if (panel.hidden) {
                abrir();
            } else {
                cerrar();
            }
        }


        function mostrarError() {
            contenedor.classList.add(
                "is-invalid"
            );

            toggle.classList.add(
                "is-invalid"
            );


            if (feedback) {
                feedback.style.display =
                    "block";
            }
        }


        function quitarError() {
            contenedor.classList.remove(
                "is-invalid"
            );

            toggle.classList.remove(
                "is-invalid"
            );


            if (feedback) {
                feedback.style.display = "";
            }
        }


        function actualizarTexto() {
            if (!texto) {
                return;
            }


            const seleccionados =
                obtenerSeleccionados();


            if (seleccionados.length === 0) {
                texto.textContent =
                    "Seleccionar servicios...";

                return;
            }


            if (seleccionados.length === 1) {
                texto.textContent = String(
                    seleccionados[0]
                        .dataset.nombre ||
                    "1 servicio seleccionado"
                );

                return;
            }


            texto.textContent =
                `${seleccionados.length} servicios seleccionados`;
        }


        function actualizarResumen() {
            if (!resumen) {
                return;
            }


            const seleccionados =
                obtenerSeleccionados();


            resumen.innerHTML = "";


            /*
             * Cuando todavía no se ha seleccionado
             * ningún servicio.
             */
            if (seleccionados.length === 0) {
                const vacio =
                    document.createElement(
                        "span"
                    );


                vacio.className =
                    "agendamiento-servicios-resumen-vacio";


                vacio.textContent =
                    "Ningún servicio seleccionado.";


                resumen.appendChild(vacio);

                return;
            }


            /*
             * Mostrar únicamente los servicios
             * seleccionados con:
             *
             * - Nombre.
             * - Duración.
             * - Precio.
             */
            seleccionados.forEach(
                function (check) {
                    const nombreServicio =
                        String(
                            check.dataset.nombre ||
                            "Servicio"
                        );


                    const duracionServicio =
                        parseInt(
                            check.dataset.duracion ||
                            "0",
                            10
                        );


                    const precioServicio =
                        parseFloat(
                            check.dataset.precio ||
                            "0"
                        );


                    const item =
                        document.createElement(
                            "div"
                        );


                    item.className =
                        "agendamiento-servicio-resumen-item";


                    const nombre =
                        document.createElement(
                            "span"
                        );


                    nombre.className =
                        "agendamiento-servicio-resumen-nombre";


                    nombre.textContent =
                        nombreServicio;


                    const duracion =
                        document.createElement(
                            "span"
                        );


                    duracion.className =
                        "agendamiento-servicio-resumen-duracion";


                    duracion.textContent =
                        `${Number.isNaN(duracionServicio)
                            ? 0
                            : duracionServicio} min`;


                    const precio =
                        document.createElement(
                            "span"
                        );


                    precio.className =
                        "agendamiento-servicio-resumen-precio";


                    precio.textContent =
                        (
                            Number.isNaN(precioServicio)
                                ? 0
                                : precioServicio
                        ).toLocaleString(
                            "es-VE",
                            {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }
                        );


                    item.appendChild(nombre);

                    item.appendChild(duracion);

                    item.appendChild(precio);


                    resumen.appendChild(item);
                }
            );
        }


        function calcularTotales() {
            let montoTotal = 0;

            let duracionTotal = 0;


            obtenerSeleccionados().forEach(
                function (check) {
                    const precio = parseFloat(
                        check.dataset.precio ||
                        "0"
                    );


                    const duracion = parseInt(
                        check.dataset.duracion ||
                        "0",
                        10
                    );


                    if (
                        !Number.isNaN(precio)
                    ) {
                        montoTotal += precio;
                    }


                    if (
                        !Number.isNaN(duracion)
                    ) {
                        duracionTotal += duracion;
                    }
                }
            );


            if (configuracion.inputMonto) {
                configuracion.inputMonto.value =
                    montoTotal.toFixed(2);
            }


            if (
                configuracion.inputDuracion
            ) {
                configuracion
                    .inputDuracion
                    .value =
                    duracionTotal > 0
                        ? String(
                            duracionTotal
                        )
                        : "0";
            }
        }


        function actualizar(
            calcular = true
        ) {
            actualizarTexto();

            actualizarResumen();


            if (calcular) {
                calcularTotales();
            }


            if (
                obtenerSeleccionados()
                    .length > 0
            ) {
                quitarError();
            }
        }


        function filtrar() {
            const termino = normalizarTexto(
                buscador
                    ? buscador.value
                    : ""
            );


            let visibles = 0;


            opciones.forEach(
                function (opcion) {
                    const nombre =
                        normalizarTexto(
                            opcion.dataset
                                .servicioNombre ||
                            opcion.textContent
                        );


                    const coincide =
                        termino === "" ||
                        nombre.includes(
                            termino
                        );


                    opcion.hidden =
                        !coincide;


                    if (coincide) {
                        visibles += 1;
                    }
                }
            );


            if (sinResultados) {
                sinResultados.classList.toggle(
                    "d-none",
                    visibles > 0
                );
            }
        }


        function limpiarBusqueda() {
            if (buscador) {
                buscador.value = "";
            }


            opciones.forEach(
                function (opcion) {
                    opcion.hidden = false;
                }
            );


            if (sinResultados) {
                sinResultados.classList.add(
                    "d-none"
                );
            }
        }


        function limpiarSeleccion() {
            checks.forEach(
                function (check) {
                    check.checked = false;
                }
            );


            quitarError();

            actualizar();
        }


        function seleccionar(
            idsServicios
        ) {
            const ids = Array.isArray(
                idsServicios
            )
                ? idsServicios.map(String)
                : [];


            checks.forEach(
                function (check) {
                    check.checked =
                        ids.includes(
                            String(
                                check.value
                            )
                        );
                }
            );


            actualizar();
        }


        function establecerDeshabilitado(
            estado,
            limpiar = false
        ) {
            deshabilitado =
                Boolean(estado);


            contenedor.classList.toggle(
                "is-disabled",
                deshabilitado
            );


            toggle.disabled =
                deshabilitado;


            checks.forEach(
                function (check) {
                    check.disabled =
                        deshabilitado;
                }
            );


            if (buscador) {
                buscador.disabled =
                    deshabilitado;
            }


            if (deshabilitado) {
                cerrar();

                quitarError();


                if (limpiar) {
                    limpiarSeleccion();
                }
            }
        }


        function validar() {
            if (deshabilitado) {
                quitarError();

                return true;
            }


            const valido =
                obtenerSeleccionados()
                    .length > 0;


            if (valido) {
                quitarError();
            } else {
                mostrarError();
            }


            return valido;
        }


        toggle.addEventListener(
            "click",
            alternar
        );


        checks.forEach(
            function (check) {
                check.addEventListener(
                    "change",
                    function () {
                        actualizar();
                    }
                );
            }
        );


        if (buscador) {
            buscador.addEventListener(
                "input",
                filtrar
            );


            buscador.addEventListener(
                "keydown",
                function (evento) {
                    if (
                        evento.key ===
                        "Escape"
                    ) {
                        cerrar();

                        toggle.focus();
                    }
                }
            );
        }


        panel.addEventListener(
            "click",
            function (evento) {
                evento.stopPropagation();
            }
        );


        actualizar(false);


        return {
            contenedor:
                contenedor,

            toggle:
                toggle,

            abrir:
                abrir,

            cerrar:
                cerrar,

            actualizar:
                actualizar,

            calcularTotales:
                calcularTotales,

            limpiarBusqueda:
                limpiarBusqueda,

            limpiarSeleccion:
                limpiarSeleccion,

            seleccionar:
                seleccionar,

            establecerDeshabilitado:
                establecerDeshabilitado,

            obtenerSeleccionados:
                obtenerSeleccionados,

            validar:
                validar,

            quitarError:
                quitarError,

            enfocar:
                function () {
                    toggle.focus();
                }
        };
    }


    const selectorServiciosNuevo =
        crearSelectorServicios({
            contenedorId:
                "nuevosServiciosAgendamiento",

            toggleId:
                "nuevoServiciosToggle",

            textoId:
                "nuevoServiciosTexto",

            panelId:
                "nuevoServiciosPanel",

            buscadorId:
                "nuevoServiciosBuscar",

            listaId:
                "nuevoServiciosLista",

            sinResultadosId:
                "nuevoServiciosSinResultados",

            resumenId:
                "nuevoServiciosResumen",

            feedbackId:
                "nuevoServiciosFeedback",

            inputMonto:
                montoNuevo,

            inputDuracion:
                duracionNueva
        });


    const selectorServiciosEditar =
        crearSelectorServicios({
            contenedorId:
                "editarServiciosAgendamiento",

            toggleId:
                "editarServiciosToggle",

            textoId:
                "editarServiciosTexto",

            panelId:
                "editarServiciosPanel",

            buscadorId:
                "editarServiciosBuscar",

            listaId:
                "editarServiciosLista",

            sinResultadosId:
                "editarServiciosSinResultados",

            resumenId:
                "editarServiciosResumen",

            feedbackId:
                "editarServiciosFeedback",

            inputMonto:
                montoEditar,

            inputDuracion:
                duracionEditar
        });


    /* ===================================================== */
    /* VALIDACIONES */
    /* ===================================================== */

    function campoEstaVacio(campo) {
        if (
            !campo ||
            campo.disabled
        ) {
            return false;
        }


        return String(
            campo.value || ""
        ).trim() === "";
    }


    function validarDuracion(
        inputDuracion
    ) {
        if (!inputDuracion) {
            return true;
        }


        const duracion = parseInt(
            inputDuracion.value,
            10
        );


        if (
            Number.isNaN(duracion) ||
            duracion <= 0
        ) {
            inputDuracion.classList.add(
                "is-invalid"
            );

            return false;
        }


        inputDuracion.classList.remove(
            "is-invalid"
        );


        return true;
    }


    function validarFecha(
        inputFecha
    ) {
        if (
            !inputFecha ||
            !inputFecha.value
        ) {
            return true;
        }


        const fechaMinima =
            inputFecha.min ||
            obtenerFechaActual();


        if (
            inputFecha.value <
            fechaMinima
        ) {
            inputFecha.classList.add(
                "is-invalid"
            );

            return false;
        }


        inputFecha.classList.remove(
            "is-invalid"
        );


        return true;
    }


    function validarFormulario(
        formulario,
        selectTipo,
        selectCliente,
        selectorServicios,
        inputDuracion,
        inputFecha
    ) {
        if (!formulario) {
            return true;
        }


        let formularioValido =
            true;


        formulario
            .querySelectorAll(
                "input[required], select[required], textarea[required]"
            )
            .forEach(
                function (campo) {
                    if (
                        campoEstaVacio(
                            campo
                        )
                    ) {
                        campo.classList.add(
                            "is-invalid"
                        );

                        formularioValido =
                            false;
                    } else {
                        campo.classList.remove(
                            "is-invalid"
                        );
                    }
                }
            );


        if (
            selectCliente &&
            !selectCliente.value
        ) {
            selectCliente.classList.add(
                "is-invalid"
            );

            formularioValido =
                false;
        }


        if (
            selectorServicios &&
            !selectorServicios.validar()
        ) {
            formularioValido =
                false;
        }


        if (
            !validarDuracion(
                inputDuracion
            )
        ) {
            formularioValido =
                false;
        }


        if (
            !validarFecha(
                inputFecha
            )
        ) {
            formularioValido =
                false;
        }


        if (!formularioValido) {
            const primerCampoInvalido =
                formulario.querySelector(
                    ".is-invalid"
                );


            if (
                primerCampoInvalido
            ) {
                if (
                    primerCampoInvalido
                        .classList
                        .contains(
                            "agendamiento-servicios-selector"
                        ) &&
                    selectorServicios
                ) {
                    selectorServicios
                        .enfocar();
                } else {
                    primerCampoInvalido
                        .focus();
                }
            }
        }


        return formularioValido;
    }


    function validarFormularioBloqueo(
        formulario,
        inputFecha,
        inputHoraInicio,
        inputHoraFin
    ) {
        if (!formulario) {
            return true;
        }

        let formularioValido = true;

        formulario
            .querySelectorAll(
                "input[required], select[required], textarea[required]"
            )
            .forEach(function (campo) {
                if (campoEstaVacio(campo)) {
                    campo.classList.add("is-invalid");
                    formularioValido = false;
                } else {
                    campo.classList.remove("is-invalid");
                }
            });

        if (!validarFecha(inputFecha)) {
            formularioValido = false;
        }

        if (
            inputHoraInicio &&
            inputHoraFin &&
            inputHoraInicio.value &&
            inputHoraFin.value &&
            inputHoraFin.value <= inputHoraInicio.value
        ) {
            inputHoraFin.classList.add("is-invalid");
            formularioValido = false;
        } else if (inputHoraFin) {
            inputHoraFin.classList.remove("is-invalid");
        }

        if (!formularioValido) {
            const primerCampoInvalido =
                formulario.querySelector(".is-invalid");

            if (primerCampoInvalido) {
                primerCampoInvalido.focus();
            }
        }

        return formularioValido;
    }


    function limpiarValidaciones(
        formulario
    ) {
        if (!formulario) {
            return;
        }


        formulario
            .querySelectorAll(
                ".is-invalid"
            )
            .forEach(
                function (campo) {
                    campo.classList.remove(
                        "is-invalid"
                    );
                }
            );


        formulario
            .querySelectorAll(
                ".invalid-feedback"
            )
            .forEach(
                function (feedback) {
                    feedback.style.display =
                        "";
                }
            );
    }


    function quitarErrorCampo(
        campo
    ) {
        if (campo) {
            campo.classList.remove(
                "is-invalid"
            );
        }
    }


    /* ===================================================== */
    /* LIMPIEZA Y PREPARACIÓN DE FORMULARIOS */
    /* ===================================================== */

    function limpiarFormularioNuevo() {
        if (!formNuevo) {
            return;
        }


        formNuevo.reset();

        limpiarValidaciones(
            formNuevo
        );


        if (
            selectorServiciosNuevo
        ) {
            selectorServiciosNuevo
                .limpiarSeleccion();


            selectorServiciosNuevo
                .limpiarBusqueda();


            selectorServiciosNuevo
                .establecerDeshabilitado(
                    false
                );


            selectorServiciosNuevo
                .cerrar();
        }


        if (montoNuevo) {
            montoNuevo.value =
                "0.00";
        }


        if (duracionNueva) {
            duracionNueva.value =
                "0";


            duracionNueva.readOnly =
                true;
        }


        if (clienteNuevo) {
            clienteNuevo.disabled =
                false;


            clienteNuevo.required =
                true;
        }
    }


    function prepararFormularioEditar() {
        limpiarValidaciones(
            formEditar
        );


        if (
            selectorServiciosEditar
        ) {
            selectorServiciosEditar
                .limpiarBusqueda();


            selectorServiciosEditar
                .cerrar();


            selectorServiciosEditar
                .establecerDeshabilitado(
                    false
                );


            selectorServiciosEditar
                .actualizar();
        }


        if (duracionEditar) {
            duracionEditar.readOnly =
                true;
        }


        if (clienteEditar) {
            clienteEditar.disabled =
                false;

            clienteEditar.required =
                true;
        }
    }


    function seleccionarEstadoNuevo(
        nombreEstado
    ) {
        if (!estadoNuevo) {
            return;
        }

        const opcionEstado = Array.from(
            estadoNuevo.options
        ).find(
            function (opcion) {
                return String(
                    opcion.textContent || ""
                )
                    .trim()
                    .toUpperCase() ===
                    nombreEstado.toUpperCase();
            }
        );

        if (opcionEstado) {
            estadoNuevo.value =
                opcionEstado.value;

            quitarErrorCampo(
                estadoNuevo
            );
        }
    }

    function establecerEstadoInicialNuevo() {
        seleccionarEstadoNuevo(
            "AGENDADA"
        );
    }

    function actualizarEstadoSegunTipoNuevo() {
        if (!tipoNuevo) {
            return;
        }

        const opcionSeleccionada =
            tipoNuevo.options[
                tipoNuevo.selectedIndex
            ];

        const nombreTipo = String(
            opcionSeleccionada
                ?.dataset.nombre ||
            opcionSeleccionada
                ?.textContent ||
            ""
        )
            .trim()
            .toUpperCase();

        if (
            nombreTipo ===
            "CITA INMEDIATA"
        ) {
            seleccionarEstadoNuevo(
                "EN ESPERA"
            );

            return;
        }

        if (
            nombreTipo ===
            "CITA PROGRAMADA"
        ) {
            seleccionarEstadoNuevo(
                "AGENDADA"
            );
        }
    }

    /* ===================================================== */
    /* EVENTOS DE LOS CAMPOS */
    /* ===================================================== */

    if (tipoNuevo) {
        tipoNuevo.addEventListener(
            "change",
            function () {
                quitarErrorCampo(
                    tipoNuevo
                );

                actualizarEstadoSegunTipoNuevo();
            }
        );
    }


    if (tipoEditar) {
        tipoEditar.addEventListener(
            "change",
            function () {
                quitarErrorCampo(
                    tipoEditar
                );
            }
        );
    }


    if (clienteNuevo) {
        clienteNuevo.addEventListener(
            "change",
            function () {
                quitarErrorCampo(
                    clienteNuevo
                );
            }
        );
    }


    if (clienteEditar) {
        clienteEditar.addEventListener(
            "change",
            function () {
                quitarErrorCampo(
                    clienteEditar
                );
            }
        );
    }


    document
        .querySelectorAll(
            ".templo-input, .form-select"
        )
        .forEach(
            function (campo) {
                if (
                    campo.classList.contains(
                        "agendamiento-servicios-toggle"
                    )
                ) {
                    return;
                }


                campo.addEventListener(
                    "input",
                    function () {
                        quitarErrorCampo(
                            this
                        );
                    }
                );


                campo.addEventListener(
                    "change",
                    function () {
                        quitarErrorCampo(
                            this
                        );
                    }
                );
            }
        );


    /* ===================================================== */
    /* ENVÍO DE FORMULARIOS */
    /* ===================================================== */

    if (formNuevo) {
        formNuevo.addEventListener(
            "submit",
            function (evento) {
                const valido =
                    validarFormulario(
                        formNuevo,
                        tipoNuevo,
                        clienteNuevo,
                        selectorServiciosNuevo,
                        duracionNueva,
                        fechaNueva
                    );


                if (!valido) {
                    evento.preventDefault();

                    evento.stopPropagation();
                }
            }
        );
    }


    if (formEditar) {
        formEditar.addEventListener(
            "submit",
            function (evento) {
                const valido =
                    validarFormulario(
                        formEditar,
                        tipoEditar,
                        clienteEditar,
                        selectorServiciosEditar,
                        duracionEditar,
                        fechaEditar
                    );


                if (!valido) {
                    evento.preventDefault();

                    evento.stopPropagation();
                }
            }
        );
    }


    if (formNuevoBloqueo) {
        formNuevoBloqueo.addEventListener(
            "submit",
            function (evento) {
                const valido =
                    validarFormularioBloqueo(
                        formNuevoBloqueo,
                        fechaNuevoBloqueo,
                        horaInicioNuevoBloqueo,
                        horaFinNuevoBloqueo
                    );

                if (!valido) {
                    evento.preventDefault();
                    evento.stopPropagation();
                }
            }
        );
    }


    if (formEditarBloqueo) {
        formEditarBloqueo.addEventListener(
            "submit",
            function (evento) {
                const valido =
                    validarFormularioBloqueo(
                        formEditarBloqueo,
                        fechaEditarBloqueo,
                        horaInicioEditarBloqueo,
                        horaFinEditarBloqueo
                    );

                if (!valido) {
                    evento.preventDefault();
                    evento.stopPropagation();
                }
            }
        );
    }


    /* ===================================================== */
    /* MODALES */
    /* ===================================================== */

    if (modalNuevo) {
        modalNuevo.addEventListener(
            "shown.bs.modal",
            function () {
                establecerFechaMinima();

                establecerEstadoInicialNuevo();
            }
        );


        modalNuevo.addEventListener(
            "hidden.bs.modal",
            function () {
                limpiarFormularioNuevo();
            }
        );
    }


    if (modalEditar) {
        modalEditar.addEventListener(
            "shown.bs.modal",
            function () {
                establecerFechaMinima();

                prepararFormularioEditar();
            }
        );


        modalEditar.addEventListener(
            "hidden.bs.modal",
            function () {
                limpiarValidaciones(
                    formEditar
                );


                if (
                    selectorServiciosEditar
                ) {
                    selectorServiciosEditar
                        .cerrar();


                    selectorServiciosEditar
                        .limpiarBusqueda();
                }
            }
        );
    }


    if (modalNuevoBloqueo) {
        modalNuevoBloqueo.addEventListener(
            "shown.bs.modal",
            function () {
                establecerFechaMinima();
            }
        );

        modalNuevoBloqueo.addEventListener(
            "hidden.bs.modal",
            function () {
                if (formNuevoBloqueo) {
                    formNuevoBloqueo.reset();
                    limpiarValidaciones(formNuevoBloqueo);
                }
            }
        );
    }


    if (modalEditarBloqueo) {
        modalEditarBloqueo.addEventListener(
            "shown.bs.modal",
            function () {
                establecerFechaMinima();
            }
        );

        modalEditarBloqueo.addEventListener(
            "hidden.bs.modal",
            function () {
                limpiarValidaciones(formEditarBloqueo);
            }
        );
    }


    /* ===================================================== */
    /* CIERRE DEL SELECTOR */
    /* ===================================================== */

    document.addEventListener(
        "click",
        function (evento) {
            document
                .querySelectorAll(
                    ".agendamiento-servicios-selector"
                )
                .forEach(
                    function (selector) {
                        if (
                            selector.contains(
                                evento.target
                            )
                        ) {
                            return;
                        }


                        const panel =
                            selector.querySelector(
                                ".agendamiento-servicios-panel"
                            );


                        const boton =
                            selector.querySelector(
                                ".agendamiento-servicios-toggle"
                            );


                        if (panel) {
                            panel.hidden =
                                true;
                        }


                        if (boton) {
                            boton.setAttribute(
                                "aria-expanded",
                                "false"
                            );
                        }


                        selector.classList.remove(
                            "is-open"
                        );
                    }
                );
        }
    );


    document.addEventListener(
        "keydown",
        function (evento) {
            if (
                evento.key !==
                "Escape"
            ) {
                return;
            }


            if (
                selectorServiciosNuevo
            ) {
                selectorServiciosNuevo
                    .cerrar();
            }


            if (
                selectorServiciosEditar
            ) {
                selectorServiciosEditar
                    .cerrar();
            }
        }
    );


    /* ===================================================== */
    /* API PARA AGENDAMIENTO-CALENDARIO.JS */
    /* ===================================================== */

    window.AgendamientoServicios = {
        seleccionarNuevo:
            function (idsServicios) {
                if (
                    selectorServiciosNuevo
                ) {
                    selectorServiciosNuevo
                        .seleccionar(
                            idsServicios
                        );
                }
            },


        seleccionarEditar:
            function (idsServicios) {
                if (
                    selectorServiciosEditar
                ) {
                    selectorServiciosEditar
                        .seleccionar(
                            idsServicios
                        );
                }
            },


        actualizarNuevo:
            function () {
                if (
                    selectorServiciosNuevo
                ) {
                    selectorServiciosNuevo
                        .actualizar();
                }
            },


        actualizarEditar:
            function () {
                if (
                    selectorServiciosEditar
                ) {
                    selectorServiciosEditar
                        .actualizar();
                }
            },


        configurarEditar:
            function () {
                prepararFormularioEditar();
            },


        limpiarEditar:
            function () {
                if (
                    selectorServiciosEditar
                ) {
                    selectorServiciosEditar
                        .limpiarSeleccion();
                }
            }
    };


    /* ===================================================== */
    /* CONFIGURACIÓN INICIAL */
    /* ===================================================== */

    establecerFechaMinima();


    if (duracionNueva) {
        duracionNueva.readOnly =
            true;
    }


    if (duracionEditar) {
        duracionEditar.readOnly =
            true;
    }


    if (selectorServiciosNuevo) {
        selectorServiciosNuevo
            .actualizar();
    }


    if (selectorServiciosEditar) {
        selectorServiciosEditar
            .actualizar();
    }
});