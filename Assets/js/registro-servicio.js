document.addEventListener("DOMContentLoaded", function () {
    const panelAgendamientos = document.getElementById(
        "panelAgendamientosServicio"
    );

    const panelRegistro = document.getElementById(
        "panelRegistroServicio"
    );

    const btnVolver = document.getElementById(
        "btnVolverAgendamientos"
    );

    const btnRefrescar = document.getElementById(
        "btnRefrescarRegistroServicio"
    );

    const formServicio = document.getElementById(
        "formServicioRealizado"
    );

    const formInsumos = document.getElementById(
        "formInsumosUtilizados"
    );

    const btnCancelarServicio = document.getElementById(
        "btnCancelarServicio"
    );

    const btnGuardarServicio = document.getElementById(
        "btnGuardarServicio"
    );

    const btnGuardarInsumos = document.getElementById(
        "btnGuardarInsumos"
    );

    const btnFinalizarServicio = document.getElementById(
        "btnFinalizarServicio"
    );

    const fotoResultado = document.getElementById(
        "fotoResultadoServicio"
    );

    const previewResultado = document.getElementById(
        "previewResultadoServicio"
    );

    const previewVacio = document.getElementById(
        "previewResultadoVacio"
    );

    const insumoUtilizado = document.getElementById(
        "insumoUtilizado"
    );

    const cantidadInsumo = document.getElementById(
        "cantidadInsumo"
    );

    const presentacionInsumo = document.getElementById(
        "presentacionInsumo"
    );

    const stockDisponible = document.getElementById(
        "stockDisponibleInsumo"
    );

    const btnAgregarInsumo = document.getElementById(
        "btnAgregarInsumo"
    );

    const tbodyInsumos = document.getElementById(
        "tbodyInsumosSeleccionados"
    );

    const filtroCatalogo = document.getElementById(
        "filtroCatalogoServicio"
    );

    const catalogoVacio = document.getElementById(
        "catalogoTrabajosVacio"
    );

    const tablaServiciosPendientesElemento =
        document.getElementById(
            "tablaServiciosPendientes"
        );

    let tablaServiciosPendientes = null;
    let urlVistaPrevia = null;
    let insumosSeleccionados = [];

    /* ===================================================== */
    /* DATATABLE DE AGENDAMIENTOS PARA ATENDER */
    /* ===================================================== */

    function normalizarEstadoServicio(estado) {
        return String(estado || "")
            .trim()
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
    }

    function obtenerClaseEstadoServicio(estado) {
        const clasesEstado = {
            programada: "estado-programada",
            confirmada: "estado-confirmada",
            "en espera": "estado-en-espera",
            demorada: "estado-demorada",
            activa: "estado-activa",
            activo: "estado-activa",
            realizada: "estado-realizada",
            cancelada: "estado-cancelada",
            cancelado: "estado-cancelada"
        };

        return (
            clasesEstado[
                normalizarEstadoServicio(estado)
            ] || "estado-programada"
        );
    }

    function aplicarBadgesEstadoServicio() {
        if (!tablaServiciosPendientesElemento) {
            return;
        }

        const celdasEstado =
            tablaServiciosPendientesElemento.querySelectorAll(
                "tbody tr td:nth-child(6)"
            );

        celdasEstado.forEach(function (celda) {
            if (
                celda.classList.contains(
                    "dataTables_empty"
                )
            ) {
                return;
            }

            const badgeExistente =
                celda.querySelector(".badge");

            const estado = String(
                celda.dataset.estadoAgendamiento ||
                (
                    badgeExistente
                        ? badgeExistente.textContent
                        : celda.textContent
                ) ||
                ""
            ).trim();

            if (estado === "") {
                return;
            }

            const badge =
                badgeExistente ||
                document.createElement("span");

            badge.className =
                `badge ${obtenerClaseEstadoServicio(estado)}`;

            badge.textContent = estado;

            if (!badgeExistente) {
                celda.textContent = "";
                celda.appendChild(badge);
            }
        });
    }

    function inicializarDataTableServiciosPendientes() {
        if (
            !tablaServiciosPendientesElemento ||
            typeof window.jQuery === "undefined" ||
            !window.jQuery.fn.DataTable
        ) {
            return;
        }

        const selector =
            "#tablaServiciosPendientes";

        if (
            window.jQuery.fn.DataTable.isDataTable(
                selector
            )
        ) {
            tablaServiciosPendientes =
                window.jQuery(selector).DataTable();

            aplicarBadgesEstadoServicio();

            return;
        }

        tablaServiciosPendientes =
            window.jQuery(selector).DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json",
                    emptyTable:
                        "No hay agendamientos para atender."
                },

                pageLength: 5,

                lengthMenu: [
                    5,
                    10,
                    25,
                    50
                ],

                responsive: false,
                autoWidth: false,
                order: [],

                columnDefs: [
                    {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }
                ],

                drawCallback: function () {
                    aplicarBadgesEstadoServicio();
                }
            });

        aplicarBadgesEstadoServicio();
    }

    function mostrarPanelRegistro() {
        if (panelAgendamientos) {
            panelAgendamientos.classList.add(
                "d-none"
            );
        }

        if (panelRegistro) {
            panelRegistro.classList.remove(
                "d-none"
            );
        }

        if (btnVolver) {
            btnVolver.classList.remove(
                "d-none"
            );
        }

        if (btnRefrescar) {
            btnRefrescar.classList.add(
                "d-none"
            );
        }
    }

    function mostrarPanelAgendamientos() {
        if (panelRegistro) {
            panelRegistro.classList.add(
                "d-none"
            );
        }

        if (panelAgendamientos) {
            panelAgendamientos.classList.remove(
                "d-none"
            );
        }

        if (btnVolver) {
            btnVolver.classList.add(
                "d-none"
            );
        }

        if (btnRefrescar) {
            btnRefrescar.classList.remove(
                "d-none"
            );
        }

        if (tablaServiciosPendientes) {
            window.requestAnimationFrame(
                function () {
                    tablaServiciosPendientes
                        .columns
                        .adjust();
                }
            );
        }
    }

    function limpiarVistaPrevia() {
        if (urlVistaPrevia) {
            URL.revokeObjectURL(
                urlVistaPrevia
            );

            urlVistaPrevia = null;
        }

        if (previewResultado) {
            previewResultado.src = "";

            previewResultado.classList.add(
                "d-none"
            );
        }

        if (previewVacio) {
            previewVacio.classList.remove(
                "d-none"
            );
        }

        if (fotoResultado) {
            fotoResultado.value = "";
        }
    }

    function actualizarDatosInsumo() {
        if (
            !insumoUtilizado ||
            !presentacionInsumo ||
            !stockDisponible
        ) {
            return;
        }

        const opcion =
            insumoUtilizado.options[
                insumoUtilizado.selectedIndex
            ];

        if (
            !opcion ||
            insumoUtilizado.value === ""
        ) {
            presentacionInsumo.value = "";
            stockDisponible.value = "";

            return;
        }

        presentacionInsumo.value =
            opcion.dataset.presentacion || "";

        stockDisponible.value =
            opcion.dataset.stock || "";
    }

    function crearCelda(texto) {
        const celda =
            document.createElement("td");

        celda.textContent = texto;

        return celda;
    }

    function crearCampoOculto(nombre, valor) {
        const campo =
            document.createElement("input");

        campo.type = "hidden";
        campo.name = nombre;
        campo.value = valor;

        return campo;
    }

    function mostrarFilaVaciaInsumos() {
        if (!tbodyInsumos) {
            return;
        }

        const fila =
            document.createElement("tr");

        const celda =
            document.createElement("td");

        celda.colSpan = 4;
        celda.className = "text-center";

        celda.textContent =
            "No hay insumos agregados.";

        fila.appendChild(celda);

        tbodyInsumos.appendChild(fila);
    }

    function renderizarInsumos() {
        if (!tbodyInsumos) {
            return;
        }

        tbodyInsumos.innerHTML = "";

        if (
            insumosSeleccionados.length === 0
        ) {
            mostrarFilaVaciaInsumos();

            return;
        }

        insumosSeleccionados.forEach(
            function (insumo, indice) {
                const fila =
                    document.createElement("tr");

                fila.appendChild(
                    crearCelda(insumo.nombre)
                );

                fila.appendChild(
                    crearCelda(insumo.cantidad)
                );

                fila.appendChild(
                    crearCelda(insumo.presentacion)
                );

                const celdaAccion =
                    document.createElement("td");

                celdaAccion.className =
                    "text-center";

                const botonEliminar =
                    document.createElement(
                        "button"
                    );

                botonEliminar.type = "button";

                botonEliminar.className =
                    "btn-action " +
                    "btn-delete " +
                    "btn-eliminar-insumo";

                botonEliminar.dataset.index =
                    indice;

                botonEliminar.title =
                    "Eliminar insumo";

                botonEliminar.innerHTML =
                    '<i class="bi bi-trash"></i>';

                celdaAccion.appendChild(
                    botonEliminar
                );

                fila.appendChild(
                    celdaAccion
                );

                fila.appendChild(
                    crearCampoOculto(
                        `insumos[${indice}][id_insumo]`,
                        insumo.id
                    )
                );

                fila.appendChild(
                    crearCampoOculto(
                        `insumos[${indice}][cantidad]`,
                        insumo.cantidad
                    )
                );

                tbodyInsumos.appendChild(
                    fila
                );
            }
        );
    }

    function agregarInsumo() {
        if (
            !insumoUtilizado ||
            !cantidadInsumo
        ) {
            return;
        }

        const idInsumo =
            insumoUtilizado.value;

        const cantidad = parseFloat(
            cantidadInsumo.value
        );

        const opcion =
            insumoUtilizado.options[
                insumoUtilizado.selectedIndex
            ];

        if (
            idInsumo === "" ||
            !opcion ||
            Number.isNaN(cantidad) ||
            cantidad <= 0
        ) {
            window.alert(
                "Seleccione un insumo e indique una cantidad válida."
            );

            return;
        }

        const stock = parseFloat(
            opcion.dataset.stock || ""
        );

        if (
            !Number.isNaN(stock) &&
            cantidad > stock
        ) {
            window.alert(
                "La cantidad indicada supera el stock disponible."
            );

            return;
        }

        const yaAgregado =
            insumosSeleccionados.some(
                function (insumo) {
                    return (
                        String(insumo.id) ===
                        String(idInsumo)
                    );
                }
            );

        if (yaAgregado) {
            window.alert(
                "Ese insumo ya fue agregado. Elimínelo primero para cambiar la cantidad."
            );

            return;
        }

        insumosSeleccionados.push({
            id: idInsumo,
            nombre:
                opcion.textContent.trim(),
            cantidad: cantidad,
            presentacion:
                opcion.dataset.presentacion || ""
        });

        insumoUtilizado.value = "";
        cantidadInsumo.value = "";

        if (presentacionInsumo) {
            presentacionInsumo.value = "";
        }

        if (stockDisponible) {
            stockDisponible.value = "";
        }

        renderizarInsumos();
    }

    function eliminarInsumo(indice) {
        if (
            indice < 0 ||
            indice >= insumosSeleccionados.length
        ) {
            return;
        }

        insumosSeleccionados.splice(
            indice,
            1
        );

        renderizarInsumos();
    }

    function filtrarCatalogo() {
        if (!filtroCatalogo) {
            return;
        }

        const filtro =
            filtroCatalogo.value
                .trim()
                .toLowerCase();

        const trabajos =
            document.querySelectorAll(
                "#catalogoTrabajosGrid " +
                ".catalogo-item"
            );

        let visibles = 0;

        trabajos.forEach(
            function (trabajo) {
                const nombreServicio =
                    String(
                        trabajo.dataset.servicio ||
                        ""
                    ).toLowerCase();

                const coincide =
                    filtro === "" ||
                    nombreServicio.includes(
                        filtro
                    );

                trabajo.classList.toggle(
                    "d-none",
                    !coincide
                );

                if (coincide) {
                    visibles++;
                }
            }
        );

        if (catalogoVacio) {
            catalogoVacio.classList.toggle(
                "d-none",
                visibles > 0
            );
        }
    }

    function enviarFormularioServicio(accion) {
        if (!formServicio) {
            return;
        }

        const campoAccion =
            formServicio.querySelector(
                'input[name="accion"]'
            );

        if (campoAccion) {
            campoAccion.value = accion;
        }

        formServicio.requestSubmit();
    }

    if (btnVolver) {
        btnVolver.addEventListener(
            "click",
            mostrarPanelAgendamientos
        );
    }

    if (btnRefrescar) {
        btnRefrescar.addEventListener(
            "click",
            function () {
                window.location.reload();
            }
        );
    }

    document.addEventListener(
        "click",
        function (event) {
            const botonRegistrar =
                event.target.closest(
                    ".btn-registrar-servicio"
                );

            if (botonRegistrar) {
                mostrarPanelRegistro();
            }

            const botonEliminar =
                event.target.closest(
                    ".btn-eliminar-insumo"
                );

            if (botonEliminar) {
                const indice = parseInt(
                    botonEliminar.dataset.index,
                    10
                );

                eliminarInsumo(indice);
            }
        }
    );

    if (fotoResultado) {
        fotoResultado.addEventListener(
            "change",
            function () {
                const archivo =
                    fotoResultado.files[0];

                limpiarVistaPrevia();

                if (!archivo) {
                    return;
                }

                urlVistaPrevia =
                    URL.createObjectURL(
                        archivo
                    );

                previewResultado.src =
                    urlVistaPrevia;

                previewResultado.classList.remove(
                    "d-none"
                );

                previewVacio.classList.add(
                    "d-none"
                );
            }
        );
    }

    if (insumoUtilizado) {
        insumoUtilizado.addEventListener(
            "change",
            actualizarDatosInsumo
        );
    }

    if (btnAgregarInsumo) {
        btnAgregarInsumo.addEventListener(
            "click",
            agregarInsumo
        );
    }

    if (btnCancelarServicio) {
        btnCancelarServicio.addEventListener(
            "click",
            mostrarPanelAgendamientos
        );
    }

    if (btnGuardarServicio) {
        btnGuardarServicio.addEventListener(
            "click",
            function () {
                enviarFormularioServicio(
                    "guardarServicio"
                );
            }
        );
    }

    if (
        btnGuardarInsumos &&
        formInsumos
    ) {
        btnGuardarInsumos.addEventListener(
            "click",
            function () {
                if (
                    insumosSeleccionados.length ===
                    0
                ) {
                    window.alert(
                        "Agregue al menos un insumo utilizado."
                    );

                    return;
                }

                formInsumos.requestSubmit();
            }
        );
    }

    if (btnFinalizarServicio) {
        btnFinalizarServicio.addEventListener(
            "click",
            function () {
                enviarFormularioServicio(
                    "finalizarRegistro"
                );
            }
        );
    }

    if (filtroCatalogo) {
        filtroCatalogo.addEventListener(
            "change",
            filtrarCatalogo
        );
    }

    /* ===================================================== */
    /* BUSCADOR DEL HISTORIAL DE SERVICIOS */
    /* ===================================================== */

    const buscarHistorialServicios =
        document.getElementById(
            "buscarHistorialServicios"
        );

    const tbodyHistorialServicios =
        document.getElementById(
            "tbodyHistorialServicios"
        );

    if (
        buscarHistorialServicios &&
        tbodyHistorialServicios
    ) {
        buscarHistorialServicios.addEventListener(
            "input",
            function () {
                const filtro =
                    buscarHistorialServicios.value
                        .trim()
                        .toLowerCase();

                const filas =
                    tbodyHistorialServicios
                        .querySelectorAll(
                            "tr"
                        );

                filas.forEach(
                    function (fila) {
                        /*
                         * Conserva visible la fila que informa
                         * que todavía no existen servicios.
                         */
                        const celdaVacia =
                            fila.querySelector(
                                "td[colspan]"
                            );

                        if (celdaVacia) {
                            return;
                        }

                        const contenidoFila =
                            fila.textContent
                                .trim()
                                .toLowerCase();

                        const coincide =
                            filtro === "" ||
                            contenidoFila.includes(
                                filtro
                            );

                        fila.style.display =
                            coincide
                                ? ""
                                : "none";
                    }
                );
            }
        );
    }

    inicializarDataTableServiciosPendientes();
    renderizarInsumos();
    filtrarCatalogo();
});