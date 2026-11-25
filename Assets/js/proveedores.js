/* ===================================================== */
/* GESTIÓN DE PROVEEDORES */
/* ===================================================== */

import { Regex } from "./validaciones/regex.js";
import { Validaciones } from "./validaciones/validaciones.js";

$(document).ready(function () {
    "use strict";

    const tablaProveedores = $("#tablaProveedores");
    const filtroEstado = $("#filtroEstadoProveedores");
    const modalesFormulario = $("#modalNuevoProveedor, #modalEditarProveedor, #modalCambiarContactoProveedor, #modalDesactivarProveedor");
    const formularios = modalesFormulario.find("form");
    const rutaProveedores = formularios.first().attr("action") || "Index.php?url=proveedores";

    if (!tablaProveedores.length) return;

    let tabla = null;
    let guardando = false;
    let consultando = false;
    let redirigiendo = false;
    let estadoListado = "ACTIVO";
    let estadoConsulta = "ACTIVO";
    let habilitarTabla;

    const tablaPreparada = new Promise(resolve => {
        habilitarTabla = resolve;
    });

    const avisoListado = $("<div>", {
        class: "alert py-2 small d-none",
        role: "status",
        "aria-live": "polite"
    }).insertAfter(tablaProveedores.closest(".module-card").find(".module-header"));


    /* ================================================= */
    /* INICIALIZACIÓN DEL LISTADO */
    /* ================================================= */

    if ($.fn.DataTable) {
        const existente = $.fn.DataTable.isDataTable("#tablaProveedores");

        tabla = existente
            ? tablaProveedores.DataTable()
            : tablaProveedores.DataTable({
                language: {
                    url: "Assets/libs/datatables/i18n/es-ES.json"
                },
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50],
                responsive: false,
                autoWidth: false,
                orderCellsTop: false,
                columnDefs: [
                    { targets: -1, orderable: false, searchable: false }
                ],
                initComplete: function () {
                    habilitarTabla();
                }
            });

        if (existente) habilitarTabla();

    } else {
        habilitarTabla();
    }


    /* ================================================= */
    /* VALIDACIONES DE LOS FORMULARIOS */
    /* ================================================= */

    const reglasEditarProveedor = { ...Validaciones.reglasProveedores };

    /* La cédula del contacto actual se conserva al modificar la empresa */
    delete reglasEditarProveedor.cedula;

    Object.freeze(reglasEditarProveedor);


    function obtenerReglasFormulario(formulario) {
        const accion = formulario.querySelector('[name="accion"]')?.value;

        switch (accion) {
            case "registrar": return Validaciones.reglasProveedores;
            case "modificar": return reglasEditarProveedor;
            case "cambiar_contacto": return Validaciones.reglasContacto;
            default: return null;
        }
    }


    formularios.each(function () {
        const reglas = obtenerReglasFormulario(this);

        if (!reglas) return;

        this.noValidate = true;

        Validaciones.activar(this, reglas);
    });


    /* ================================================= */
    /* MENSAJES DEL MÓDULO */
    /* ================================================= */

    function mostrarMensaje(contenedor, mensaje, tipo = "danger") {
        if (
            !redirigiendo &&
            contenedor.is(avisoListado) &&
            tipo !== "info"
        ) {
            window.Alertas?.notificar(mensaje, tipo);
        }

        contenedor
            .removeClass("d-none alert-success alert-danger alert-warning alert-info")
            .addClass("alert-" + tipo)
            .attr("role", tipo === "danger" || tipo === "warning" ? "alert" : "status")
            .text(mensaje);
    }


    function avisoFormulario(formulario) {
        let aviso = formulario.find(".proveedores-aviso-formulario");

        if (!aviso.length) {
            aviso = $("<div>", {
                class: "alert py-2 small d-none proveedores-aviso-formulario",
                role: "status",
                "aria-live": "polite"
            }).prependTo(formulario.find(".modal-body").first());
        }

        return aviso;
    }


    function permitirActualizar(contenedor, mensajeExito = "") {
        $("<button>", {
            type: "button",
            class: "btn-templo-secondary ms-2",
            text: "Actualizar listado"
        }).appendTo(contenedor).on("click", async function () {
            if (guardando || consultando || redirigiendo) return;

            await consultarProveedores(mensajeExito);
        });
    }


    function mensajeError(xhr, estado, escritura = false) {
        if (xhr.status === 401) {
            redirigiendo = true;

            window.location.assign("Index.php?url=login&sesion=expirada");

            return "La sesión finalizó. Inicie sesión nuevamente.";
        }

        if (xhr.responseJSON && typeof xhr.responseJSON.mensaje === "string") {
            return xhr.responseJSON.mensaje;
        }

        if (escritura) {
            return "No fue posible confirmar la operación. Actualice el listado y compruebe el resultado antes de volver a enviarla.";
        }

        return estado === "timeout"
            ? "La consulta tardó demasiado. Intente actualizar el listado nuevamente."
            : "No fue posible actualizar el listado. Intente nuevamente.";
    }


    /* ================================================= */
    /* SOLICITUDES DE PROVEEDORES */
    /* ================================================= */

    function solicitarProveedores(datos, ruta = rutaProveedores, escritura = false) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: ruta,
                type: "POST",
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                data: JSON.stringify(datos),
                processData: false,
                headers: { "X-Requested-With": "XMLHttpRequest" },
                timeout: 20000,

                success: function (respuesta) {
                    resolve(respuesta);
                },

                error: function (xhr, estado) {
                    const error = new Error(mensajeError(xhr, estado, escritura));

                    error.status = xhr.status;
                    error.respuesta = xhr.responseJSON;

                    reject(error);
                }
            });
        });
    }


    /* ================================================= */
    /* CREACIÓN DE LAS FILAS DEL LISTADO */
    /* ================================================= */

    const plantillaIconoDetalle = $("#plantillaIconoDetalleProveedor");

    const iconoDetalleVista = tablaProveedores
        .find(".btnDetalleProveedor .detalle-accion-icono")
        .first().clone();


    function crearIconoDetalle() {
        if (plantillaIconoDetalle.length) {
            return plantillaIconoDetalle.contents().clone();
        }

        if (iconoDetalleVista.length) {
            return iconoDetalleVista.clone();
        }

        return $("<i>", { class: "bi bi-eye", "aria-hidden": "true" });
    }


    function crearBoton(clases, titulo, destino, datos, icono) {
        return $("<button>", {
            type: "button",
            class: clases,
            title: titulo,
            "aria-label": titulo,
            "data-bs-toggle": "modal",
            "data-bs-target": destino
        }).attr(datos).append(icono);
    }


    function crearFila(proveedor) {
        const fila = $("<tr>");
        const nombreCompleto = ((proveedor.nombre || "") + " " + (proveedor.apellido || "")).trim();
        const estadoProveedor = String(proveedor.estado_proveedor || "ACTIVO").trim().toUpperCase();

        $("<td>").text(proveedor.rif ?? "").appendTo(fila);
        const empresa = $("<td>", { class: "columna-empresa" })
            .text(proveedor.nombre_empresa ?? "")
            .appendTo(fila);

        if (estadoProveedor === "INACTIVO") {
            $("<span>", {
                class: "badge rounded-pill estado-registro-inactivo ms-2",
                text: "Inactivo"
            }).appendTo(empresa);
        }
        $("<td>", { class: "proveedores-contact-start" }).text(proveedor.nombre ?? "").appendTo(fila);
        $("<td>").text(proveedor.apellido ?? "").appendTo(fila);
        $("<td>").text(proveedor.telefono ?? "").appendTo(fila);
        $("<td>").text(proveedor.ciudad ?? "").appendTo(fila);

        const datos = {
            "data-id-proveedor": String(proveedor.id_proveedor ?? ""),
            "data-id-persona-contacto": String(proveedor.id_persona_contacto ?? ""),
            "data-rif": String(proveedor.rif ?? ""),
            "data-empresa": String(proveedor.nombre_empresa ?? ""),
            "data-cedula": String(proveedor.cedula ?? ""),
            "data-nombre": String(proveedor.nombre ?? ""),
            "data-apellido": String(proveedor.apellido ?? ""),
            "data-nombre-completo": nombreCompleto,
            "data-telefono": String(proveedor.telefono ?? ""),
            "data-correo": String(proveedor.correo ?? ""),
            "data-ciudad": String(proveedor.ciudad ?? ""),
            "data-direccion": String(proveedor.direccion ?? ""),
            "data-estado-proveedor": estadoProveedor
        };

        const acciones = $("<div>", { class: "btn-group-actions" });

        acciones.append(crearBoton(
            "btn-action btn-detail btnDetalleProveedor",
            "Ver detalles del proveedor",
            "#modalDetalleProveedor",
            datos,
            crearIconoDetalle()
        ));

        if (estadoProveedor === "ACTIVO") {
            acciones.append(crearBoton(
                "btn-action btn-edit btnEditarProveedor",
                "Editar proveedor",
                "#modalEditarProveedor",
                datos,
                '<i class="bi bi-pencil-square" aria-hidden="true"></i>'
            ));

            acciones.append(crearBoton(
                "btn-action btn-edit btnCambiarContactoProveedor",
                "Cambiar persona de contacto",
                "#modalCambiarContactoProveedor",
                {
                    "data-id-proveedor": datos["data-id-proveedor"],
                    "data-id-persona-contacto": datos["data-id-persona-contacto"],
                    "data-empresa": datos["data-empresa"],
                    "data-cedula": datos["data-cedula"],
                    "data-nombre-completo": datos["data-nombre-completo"]
                },
                '<i class="bi bi-arrow-left-right" aria-hidden="true"></i>'
            ));

            acciones.append(crearBoton(
                "btn-action btn-deactivate btnDesactivarProveedor",
                "Desactivar proveedor",
                "#modalDesactivarProveedor",
                {
                    "data-id-proveedor": datos["data-id-proveedor"],
                    "data-empresa": datos["data-empresa"]
                },
                '<i class="bi bi-trash-fill" aria-hidden="true"></i>'
            ));

        } else {
            acciones.append(crearBoton(
                "btn-action btn-reactivate btnReactivarProveedor",
                "Reactivar proveedor",
                "#modalDesactivarProveedor",
                {
                    "data-id-proveedor": datos["data-id-proveedor"],
                    "data-empresa": datos["data-empresa"]
                },
                '<i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>'
            ));
        }

        $("<td>", { class: "columna-acciones" }).append(acciones).appendTo(fila);

        return fila[0];
    }


    function actualizarTabla(proveedores) {
        const filas = proveedores.map(crearFila);

        if (tabla) {
            const paginaActual = tabla.page();

            tabla.clear();
            tabla.rows.add(filas);
            tabla.draw(false);

            const ultimaPagina = Math.max(tabla.page.info().pages - 1, 0);

            if (paginaActual > ultimaPagina) {
                tabla.page(ultimaPagina).draw("page");
            }

        } else {
            tablaProveedores.find("tbody").empty().append(filas);
        }
    }


    /* ================================================= */
    /* CONSULTA Y ACTUALIZACIÓN DE PROVEEDORES */
    /* ================================================= */

    async function consultarProveedores(mensajeExito = "") {
        if (guardando || consultando || redirigiendo) return false;

        const estadoSolicitado = String(filtroEstado.val() || "ACTIVO");
        const filtroDeshabilitado = filtroEstado.prop("disabled");
        const botones = formularios.find(":submit");

        const estadosBotones = botones.map(function () {
            return $(this).prop("disabled");
        }).get();

        consultando = true;
        estadoConsulta = estadoSolicitado;

        botones.prop("disabled", true);
        filtroEstado.prop("disabled", true);
        tablaProveedores.attr("aria-busy", "true");

        avisoListado.empty().addClass("d-none");

        try {
            await tablaPreparada;

            const respuesta = await solicitarProveedores({
                accion: "consultar",
                estado: estadoSolicitado
            });

            const listaValida = respuesta && respuesta.ok === true &&
                Array.isArray(respuesta.proveedores) &&
                respuesta.proveedores.every(proveedor =>
                    $.isPlainObject(proveedor) &&
                    ["ACTIVO", "INACTIVO"].includes(proveedor.estado_proveedor) &&
                    (estadoSolicitado === "TODOS" || proveedor.estado_proveedor === estadoSolicitado)
                );

            if (!listaValida) {
                throw new Error(
                    typeof respuesta?.mensaje === "string"
                        ? respuesta.mensaje
                        : "No fue posible actualizar el listado. La respuesta recibida no es válida."
                );
            }

            actualizarTabla(respuesta.proveedores);
            estadoListado = estadoSolicitado;

            if (mensajeExito) {
                if (window.Alertas?.disponible()) {
                    window.Alertas.notificar(mensajeExito, "success");
                } else {
                    mostrarMensaje(avisoListado, mensajeExito, "success");
                }
            }

            return true;

        } catch (error) {
            filtroEstado.val(estadoListado);

            const mensaje = error.message || "No fue posible actualizar el listado.";

            mostrarMensaje(
                avisoListado,
                (mensajeExito ? mensajeExito + " " : "") + mensaje,
                mensajeExito ? "warning" : "danger"
            );

            if (error.status !== 401) permitirActualizar(avisoListado, mensajeExito);

            return false;

        } finally {
            consultando = false;
            tablaProveedores.attr("aria-busy", "false");

            if (!redirigiendo) {
                filtroEstado.prop("disabled", filtroDeshabilitado);

                botones.each(function (indice) {
                    $(this).prop("disabled", estadosBotones[indice]);
                });
            }
        }
    }


    /* Consultar el filtro elegido sin superponer consultas ni escrituras */
    filtroEstado.on("change", function () {
        if (guardando || consultando || redirigiendo) {
            filtroEstado.val(consultando ? estadoConsulta : estadoListado);
            return;
        }

        consultarProveedores();
    });


    /* ================================================= */
    /* LLENADO DE LOS MODALES */
    /* ================================================= */

    $(document).on("click", ".btnDetalleProveedor", function () {
        const boton = $(this);

        $("#detalleRifProveedor").text(boton.attr("data-rif") || "Sin información");
        $("#detalleNombreEmpresa").text(boton.attr("data-empresa") || "Sin información");
        $("#detalleCedulaContacto").text(boton.attr("data-cedula") || "Sin información");
        $("#detalleNombreContacto").text(boton.attr("data-nombre-completo") || "Sin información");
        $("#detalleTelefonoContacto").text(boton.attr("data-telefono") || "Sin información");
        $("#detalleCorreoContacto").text(boton.attr("data-correo") || "Sin información");
        $("#detalleCiudadContacto").text(boton.attr("data-ciudad") || "Sin información");
        $("#detalleDireccionContacto").text(boton.attr("data-direccion") || "Sin información");

        const estadoProveedor = boton.attr("data-estado-proveedor") || "ACTIVO";

        $("#detalleEstadoProveedor")
            .removeClass("estado-registro-activo estado-registro-inactivo")
            .addClass(estadoProveedor === "INACTIVO" ? "estado-registro-inactivo" : "estado-registro-activo")
            .text(estadoProveedor);
    });


    $(document).on("click", ".btnEditarProveedor", function () {
        if (guardando || redirigiendo) return;

        const boton = $(this);

        $("#editarIdProveedor").val(boton.attr("data-id-proveedor") || "");
        $("#editarIdPersonaContacto").val(boton.attr("data-id-persona-contacto") || "");
        $("#editarRifProveedor").val(boton.attr("data-rif") || "");
        $("#editarNombreEmpresa").val(boton.attr("data-empresa") || "");
        $("#editarCedulaContacto").val(boton.attr("data-cedula") || "");
        $("#editarNombreContacto").val(boton.attr("data-nombre") || "");
        $("#editarApellidoContacto").val(boton.attr("data-apellido") || "");
        $("#editarTelefonoContacto").val(boton.attr("data-telefono") || "");
        $("#editarCorreoContacto").val(boton.attr("data-correo") || "");
        $("#editarCiudadContacto").val(boton.attr("data-ciudad") || "");
        $("#editarDireccionContacto").val(boton.attr("data-direccion") || "");
    });


    $(document).on("click", ".btnCambiarContactoProveedor", function () {
        if (guardando || redirigiendo) return;

        const boton = $(this);

        $("#cambiarIdProveedor").val(boton.attr("data-id-proveedor") || "");
        $("#cambiarIdPersonaContacto").val(boton.attr("data-id-persona-contacto") || "");
        $("#cambiarEmpresaProveedor").val(boton.attr("data-empresa") || "");
        $("#cambiarContactoActualProveedor").val(boton.attr("data-nombre-completo") || "");
    });


    $("#modalDesactivarProveedor").on("show.bs.modal", function (evento) {
        if (guardando || consultando || redirigiendo) {
            evento.preventDefault();
            return;
        }

        const modal = $(this);
        const formulario = modal.find("form").first();
        const boton = $(evento.relatedTarget);
        const reactivar = boton.hasClass("btnReactivarProveedor");
        const icono = reactivar ? "bi-arrow-counterclockwise" : "bi-person-dash";

        formulario.find('[name="accion"]').val(reactivar ? "reactivar" : "desactivar");

        $("#desactivarIdProveedor").val(boton.attr("data-id-proveedor") || "");
        $("#desactivarNombreProveedor").text(boton.attr("data-empresa") || "");

        $("#modalDesactivarProveedorLabel").empty().append(
            $("<i>", { class: "bi " + icono, "aria-hidden": "true" }),
            document.createTextNode(reactivar ? " Confirmar reactivación" : " Confirmar desactivación")
        );

        modal.find(".status-question").text(
            reactivar ? "¿Deseas reactivar este proveedor?" : "¿Deseas desactivar este proveedor?"
        );

        modal.find(".status-warning").text(
            reactivar
                ? "El proveedor volverá a estar disponible conservando su contacto, sus insumos y su historial."
                : "El proveedor no se borrará. Podrá consultarlo y reactivarlo desde Inactivos."
        );

        formulario.find('[type="submit"]')
            .toggleClass("btn-templo-primary", reactivar)
            .toggleClass("btn-templo-danger", !reactivar)
            .empty().append(
                $("<i>", { class: "bi " + icono, "aria-hidden": "true" }),
                document.createTextNode(reactivar ? " Sí, reactivar" : " Sí, desactivar")
            );
    });


    /* ================================================= */
    /* SELECTOR DE INSUMOS DE PROVEEDORES */
    /* ================================================= */

    /* Reutilizar el aspecto y el comportamiento del selector de Agendamiento */
    const modalesConInsumos = $("#modalNuevoProveedor, #modalEditarProveedor");
    const selectoresInsumos = new WeakMap();
    const versionesAsociaciones = new WeakMap();

    let versionDetalleInsumos = 0;


    function validarListaInsumos(insumos) {
        if (!Array.isArray(insumos)) {
            throw new Error("La lista de insumos recibida no es válida.");
        }

        const identificadores = new Set();

        insumos.forEach(function (insumo) {
            const id = Number(insumo?.id_insumo);

            if (
                !$.isPlainObject(insumo) ||
                !Number.isSafeInteger(id) ||
                id < 1 ||
                identificadores.has(id)
            ) {
                throw new Error("La lista de insumos contiene datos inválidos o repetidos.");
            }

            identificadores.add(id);
        });

        return insumos;
    }


    function normalizarBusqueda(texto) {
        return String(texto ?? "")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .trim()
            .toLocaleLowerCase("es");
    }


    /* Conservar los campos originales de la vista sin duplicar formularios */
    function prepararSelectorInsumos($modal) {
        const nuevo = $modal.is("#modalNuevoProveedor");
        const sufijo = nuevo ? "NuevoProveedor" : "EditarProveedor";
        const idLista = nuevo ? "listaInsumosNuevoProveedor" : "listaInsumosEditarProveedor";
        const idBuscar = nuevo ? "buscarInsumosNuevoProveedor" : "buscarInsumosEditarProveedor";
        const idMarca = nuevo ? "insumosPresentesNuevoProveedor" : "insumosPresentesEditarProveedor";
        const idAviso = nuevo ? "avisoInsumosNuevoProveedor" : "avisoInsumosEditarProveedor";

        const $lista = $("#" + idLista);
        const $buscar = $("#" + idBuscar);
        const $marca = $("#" + idMarca);
        const $aviso = $("#" + idAviso);
        const $seccion = $lista.closest(".proveedores-insumos-seccion");
        const $formulario = $modal.find("form").first();

        if (!$lista.length || !$buscar.length || !$seccion.length) return null;

        /* La búsqueda pasa a estar dentro del desplegable, como en Servicios */
        $seccion.find('label[for="' + idBuscar + '"]').remove();

        const idPanel = "proveedoresInsumosPanel" + sufijo;

        const $selector = $("<div>", {
            class: "agendamiento-servicios-selector proveedores-insumos-selector",
            id: "proveedoresInsumosSelector" + sufijo
        });

        const $texto = $("<span>", { text: "Seleccionar insumos..." });

        const $toggle = $("<button>", {
            type: "button",
            class: "form-select templo-input agendamiento-servicios-toggle",
            "aria-expanded": "false",
            "aria-controls": idPanel,
            "aria-label": "Seleccionar insumos que suministra el proveedor"
        }).append($texto, $("<i>", {
            class: "bi bi-chevron-down",
            "aria-hidden": "true"
        }));

        const $panel = $("<div>", {
            class: "agendamiento-servicios-panel",
            id: idPanel,
            hidden: true
        });

        const $buscador = $("<div>", {
            class: "agendamiento-servicios-buscador"
        });

        $buscar.removeClass("mb-3").attr("placeholder", "Buscar insumo...");

        $buscador.append(
            $("<i>", { class: "bi bi-search", "aria-hidden": "true" }),
            $buscar
        );

        /* La lista conserva su identificador para las solicitudes AJAX */
        $lista.removeClass("row g-2").addClass("agendamiento-servicios-lista");

        const $sinResultados = $("<p>", {
            class: "agendamiento-servicios-sin-resultados d-none",
            text: "No se encontraron insumos."
        });

        $panel.append($buscador, $lista, $sinResultados);
        $selector.append($toggle, $panel);

        const $resumen = $("<div>", {
            class: "agendamiento-servicios-resumen proveedores-insumos-resumen",
            "aria-live": "polite"
        });

        const $descripcion = $seccion.find(".form-text").first();

        if ($descripcion.length) {
            $descripcion.after($selector, $resumen);

        } else {
            $seccion.prepend($selector, $resumen);
        }

        const selector = {
            nuevo: nuevo,
            modal: $modal,
            formulario: $formulario,
            toggle: $toggle,
            texto: $texto,
            panel: $panel,
            buscar: $buscar,
            lista: $lista,
            marca: $marca,
            aviso: $aviso,
            sinResultados: $sinResultados,
            resumen: $resumen,
            contenedor: $selector
        };

        selectoresInsumos.set($modal[0], selector);


        /* Cerrar un desplegable no desmarca ningún insumo */
        function cerrarSelector() {
            $panel.prop("hidden", true);
            $toggle.attr("aria-expanded", "false");
            $selector.removeClass("is-open");
        }

        selector.cerrar = cerrarSelector;

        $toggle.on("click", function () {
            if ($toggle.prop("disabled")) return;

            if (!$panel.prop("hidden")) {
                cerrarSelector();
                return;
            }

            modalesConInsumos.each(function () {
                const otro = selectoresInsumos.get(this);

                if (otro && otro !== selector) otro.cerrar();
            });

            $panel.prop("hidden", false);
            $toggle.attr("aria-expanded", "true");
            $selector.addClass("is-open");
            $buscar.trigger("focus");
        });


        /* Buscar sin modificar las selecciones, incluso si están ocultas */
        $buscar.on("input", function () {
            const termino = normalizarBusqueda($buscar.val());

            let encontrados = 0;

            $lista.find(".agendamiento-servicio-opcion").each(function () {
                const $opcion = $(this);

                const coincide = normalizarBusqueda($opcion.attr("data-servicio-nombre"))
                    .includes(termino);

                $opcion.toggle(coincide);

                if (coincide) encontrados++;
            });

            $sinResultados.toggleClass(
                "d-none",
                encontrados > 0 || !$lista.find(".agendamiento-servicio-opcion").length
            );
        });

        $buscar.on("keydown", function (evento) {
            if (evento.key === "Escape") {
                evento.stopPropagation();

                cerrarSelector();

                $toggle.trigger("focus");
            }
        });

        $lista.on("change", ".proveedor-insumo-check", function () {
            actualizarResumenSelector(selector);
        });


        /* Evitar que pulsar en el buscador cierre el desplegable */
        $panel.on("click", function (evento) {
            evento.stopPropagation();
        });

        $modal.on("click", function (evento) {
            if (!$(evento.target).closest($selector).length) cerrarSelector();
        });

        $toggle.prop("disabled", true);

        actualizarResumenSelector(selector);

        return selector;
    }


    function avisarInsumos(selector, mensaje, error = false) {
        selector.aviso
            .removeClass("text-muted text-danger text-success estado-error")
            .addClass(error ? "estado-error" : "text-muted")
            .empty()
            .text(mensaje);
    }


    /* Mostrar un resumen igual al de los servicios seleccionados */
    function actualizarResumenSelector(selector) {
        const $marcados = selector.lista.find(".proveedor-insumo-check:checked");

        selector.resumen.empty();

        if (!$marcados.length) {
            selector.texto.text("Seleccionar insumos...");

            $("<span>", {
                class: "agendamiento-servicios-resumen-vacio",
                text: "Ningún insumo seleccionado."
            }).appendTo(selector.resumen);

            return;
        }

        selector.texto.text(
            $marcados.length === 1
                ? String($marcados.first().attr("data-nombre") || "1 insumo seleccionado")
                : $marcados.length + " insumos seleccionados"
        );

        $marcados.each(function () {
            const $check = $(this);
            const nombre = String($check.attr("data-nombre") || "Insumo");
            const presentacion = String($check.attr("data-presentacion") || "");
            const $item = $("<div>", { class: "agendamiento-servicio-resumen-item" });

            $("<span>", {
                class: "agendamiento-servicio-resumen-nombre",
                text: nombre
            }).appendTo($item);

            if (presentacion) {
                $("<span>", {
                    class: "agendamiento-servicio-resumen-duracion",
                    text: presentacion
                }).appendTo($item);
            }

            $item.appendTo(selector.resumen);
        });
    }


    /* Mostrar los insumos activos, tengan o no existencias */
    function mostrarCasillasInsumos(selector, insumos) {
        const prefijo = selector.nuevo ? "nuevoProveedorInsumo_" : "editarProveedorInsumo_";

        selector.lista.empty();

        if (!insumos.length) {
            $("<p>", {
                class: "agendamiento-servicios-sin-resultados",
                text: "Todavía no hay insumos activos registrados."
            }).appendTo(selector.lista);

            actualizarResumenSelector(selector);

            return;
        }

        insumos.forEach(function (insumo) {
            const id = Number(insumo.id_insumo);
            const nombre = String(insumo.nombre_insumo ?? "Insumo sin nombre");
            const presentacion = String(insumo.presentacion ?? "").trim();

            const $opcion = $("<label>", {
                class: "agendamiento-servicio-opcion proveedor-insumo-opcion",
                for: prefijo + id,
                "data-servicio-nombre": nombre
            });

            $("<input>", {
                type: "checkbox",
                class: "form-check-input agendamiento-servicio-check proveedor-insumo-check",
                id: prefijo + id,
                name: "id_insumos[]",
                value: String(id),
                "data-nombre": nombre,
                "data-presentacion": presentacion
            }).prop("checked", Number(insumo.seleccionado ?? 0) === 1)
                .appendTo($opcion);

            const $contenido = $("<span>", { class: "agendamiento-servicio-contenido" });

            $("<span>", {
                class: "agendamiento-servicio-nombre",
                text: nombre
            }).appendTo($contenido);

            if (presentacion) {
                $("<span>", {
                    class: "agendamiento-servicio-duracion",
                    text: "· " + presentacion
                }).appendTo($contenido);
            }

            $opcion.append($contenido).appendTo(selector.lista);
        });

        selector.buscar.val("");
        selector.sinResultados.addClass("d-none");

        actualizarResumenSelector(selector);
    }


    /* Validar la consulta por versión, independientemente de la animación */
    async function cargarAsociaciones($modal, idProveedor = null) {
        const selector = selectoresInsumos.get($modal[0]);

        if (!selector) return;

        const elemento = $modal[0];
        const version = (versionesAsociaciones.get(elemento) || 0) + 1;

        versionesAsociaciones.set(elemento, version);

        selector.cerrar();
        selector.formulario.data("insumosVerificados", false);
        selector.marca.prop("disabled", true);
        selector.toggle.prop("disabled", true);
        selector.buscar.prop("disabled", true).val("");

        selector.lista.empty().append(
            $("<p>", {
                class: "agendamiento-servicios-sin-resultados",
                text: "Consultando insumos..."
            })
        );

        selector.resumen.empty();
        selector.texto.text("Consultando insumos...");
        selector.sinResultados.addClass("d-none");

        avisarInsumos(selector, "Verificando los insumos disponibles...");

        try {
            const datos = selector.nuevo
                ? { accion: "consultar_insumos_disponibles" }
                : { accion: "consultar_insumos", id_proveedor: idProveedor };

            const respuesta = await solicitarProveedores(datos);

            /* Ignorar respuestas anteriores si el modal se cerró o cambió */
            if (versionesAsociaciones.get(elemento) !== version || redirigiendo) return;

            if (!respuesta || respuesta.ok !== true) {
                throw new Error(respuesta?.mensaje || "No se pudieron consultar los insumos.");
            }

            const insumos = validarListaInsumos(respuesta.insumos);

            mostrarCasillasInsumos(selector, insumos);

            selector.formulario.data("insumosVerificados", true);
            selector.marca.prop("disabled", false);
            selector.toggle.prop("disabled", insumos.length === 0);
            selector.buscar.prop("disabled", false);

            const marcados = insumos.filter(insumo => Number(insumo.seleccionado ?? 0) === 1).length;

            avisarInsumos(
                selector,
                insumos.length === 0
                    ? "No hay insumos activos disponibles para asociar."
                    : selector.nuevo
                        ? "Puede seleccionar varios insumos y verlos en el resumen."
                        : "Asociaciones actuales: " + marcados + ". Puede modificarlas."
            );

        } catch (error) {
            if (versionesAsociaciones.get(elemento) !== version || redirigiendo) return;

            selector.formulario.data("insumosVerificados", false);
            selector.marca.prop("disabled", true);
            selector.toggle.prop("disabled", true);
            selector.buscar.prop("disabled", true);
            selector.lista.empty();
            selector.texto.text("No se pudieron cargar los insumos");
            selector.resumen.empty();

            avisarInsumos(
                selector,
                (error.message || "Error al consultar los insumos.") + " ",
                true
            );

            $("<button>", {
                type: "button",
                class: "btn-templo-secondary ms-2",
                text: "Reintentar"
            }).on("click", function () {
                cargarAsociaciones($modal, idProveedor);
            }).appendTo(selector.aviso);
        }
    }


    /* Crear los dos selectores con el mismo diseño de Agendamiento */
    modalesConInsumos.each(function () {
        prepararSelectorInsumos($(this));
    });


    /* Iniciar cada consulta una vez, al abrir Registrar o Modificar */
    modalesConInsumos.on("show.bs.modal", function (evento) {
        const $modal = $(this);
        const selector = selectoresInsumos.get(this);
        const idProveedor = Number($(evento.relatedTarget).attr("data-id-proveedor"));

        if (!selector) return;

        if (!selector.nuevo && (!Number.isSafeInteger(idProveedor) || idProveedor < 1)) {
            selector.formulario.data("insumosVerificados", false);
            selector.toggle.prop("disabled", true);

            avisarInsumos(selector, "No se pudo identificar el proveedor.", true);

            return;
        }

        cargarAsociaciones($modal, selector.nuevo ? null : idProveedor);
    });


    /* Invalidar consultas al cerrar sin perder asociaciones guardadas */
    modalesConInsumos.on("hide.bs.modal", function () {
        if (guardando) return;

        versionesAsociaciones.set(this, (versionesAsociaciones.get(this) || 0) + 1);

        const selector = selectoresInsumos.get(this);

        if (selector) {
            selector.cerrar();
            selector.marca.prop("disabled", true);
            selector.formulario.data("insumosVerificados", false);
        }
    });


    /* En Detalles solo se muestran los insumos relacionados con la empresa */
    $("#modalDetalleProveedor").on("show.bs.modal", async function (evento) {
        const turno = ++versionDetalleInsumos;
        const idProveedor = Number($(evento.relatedTarget).attr("data-id-proveedor"));
        const $contenedor = $("#detalleInsumosProveedor").empty();

        $("<span>", {
            class: "text-muted",
            text: "Consultando insumos asociados..."
        }).appendTo($contenedor);

        if (!Number.isSafeInteger(idProveedor) || idProveedor < 1) {
            $contenedor.text("No se pudo identificar el proveedor.");
            return;
        }

        try {
            const respuesta = await solicitarProveedores({
                accion: "consultar_insumos_detalle",
                id_proveedor: idProveedor
            });

            if (turno !== versionDetalleInsumos || redirigiendo) return;

            if (!respuesta || respuesta.ok !== true) {
                throw new Error(respuesta?.mensaje || "No se pudieron consultar los insumos.");
            }

            const insumos = validarListaInsumos(respuesta.insumos);
            const asociados = insumos.filter(insumo => Number(insumo.seleccionado) === 1);

            $contenedor.empty();

            if (!asociados.length) {
                $("<span>", {
                    class: "text-muted",
                    text: "Este proveedor aún no tiene insumos asociados."
                }).appendTo($contenedor);

                return;
            }

            asociados.forEach(function (insumo) {
                $("<span>", {
                    class: "badge estado-registro-activo",
                    text: String(insumo.nombre_insumo ?? "Insumo")
                }).appendTo($contenedor);
            });

        } catch (error) {
            if (turno !== versionDetalleInsumos || redirigiendo) return;

            window.Alertas?.notificar(
                error.message || "No se pudieron consultar los insumos asociados.",
                "error",
                { origen: $contenedor }
            );

            $contenedor.text(error.message || "No se pudieron consultar los insumos asociados.");
        }
    });


    $("#modalDetalleProveedor").on("hide.bs.modal", function () {
        ++versionDetalleInsumos;
    });


    /* ================================================= */
    /* BÚSQUEDA DE PERSONAS POR CÉDULA */
    /* ================================================= */

    const camposPersona = ["nombre", "apellido", "telefono", "correo", "direccion", "ciudad"];


    function mensajePersona(campoCedula, mensaje, tipo = "info") {
        const idMensaje = campoCedula.attr("aria-describedby");
        const contenedor = idMensaje ? $("#" + idMensaje) : $();

        contenedor
            .removeClass("text-success text-danger text-muted estado-correcto estado-error")
            .addClass(tipo === "success" ? "estado-correcto" : tipo === "danger" ? "estado-error" : "text-muted")
            .text(mensaje)
            .prop("hidden", !mensaje);
    }


    function limpiarDatosPersona(formulario) {
        camposPersona.forEach(function (campo) {
            formulario.find('[name="' + campo + '"]').val("");
        });
    }


    async function buscarPersona(campoCedula) {
        const formulario = campoCedula.closest("form");
        const cedula = String(campoCedula.val() || "").trim();
        const secuencia = (formulario.data("secuenciaPersona") || 0) + 1;

        formulario.data("secuenciaPersona", secuencia);
        formulario.data("buscandoPersona", false);
        formulario.removeData("cedulaComprobada");

        mensajePersona(campoCedula, "");

        if (!cedula) return;

        /* El validador compartido muestra el error bajo la cédula */
        if (!Regex.cedula.test(cedula)) return;

        const valoresIniciales = {};

        camposPersona.forEach(function (campo) {
            valoresIniciales[campo] = formulario.find('[name="' + campo + '"]').val();
        });

        formulario.data("buscandoPersona", true);

        mensajePersona(campoCedula, "Buscando persona…");

        try {
            const respuesta = await solicitarProveedores({
                accion: "consultar_persona",
                cedula: cedula
            });

            if (
                formulario.data("secuenciaPersona") !== secuencia ||
                String(campoCedula.val() || "").trim() !== cedula ||
                !formulario.closest(".modal").hasClass("show")
            ) return;

            if (!respuesta || respuesta.ok !== true || typeof respuesta.encontrada !== "boolean") {
                throw new Error("No fue posible comprobar los datos de la persona.");
            }

            if (!respuesta.encontrada) {
                formulario.data("cedulaComprobada", cedula);

                mensajePersona(campoCedula, "Persona no registrada. Complete sus datos.");

                return;
            }

            if (!$.isPlainObject(respuesta.persona) || String(respuesta.persona.cedula) !== cedula) {
                throw new Error("Los datos recibidos no corresponden a la cédula consultada.");
            }

            const camposModificados = camposPersona.some(function (campo) {
                return formulario.find('[name="' + campo + '"]').val() !== valoresIniciales[campo];
            });

            if (camposModificados) {
                mensajePersona(
                    campoCedula,
                    "Se modificaron datos durante la búsqueda. Consulte nuevamente la cédula.",
                    "danger"
                );

                return;
            }

            camposPersona.forEach(function (campo) {
                const entrada = formulario.find('[name="' + campo + '"]').val(respuesta.persona[campo] ?? "");

                if (entrada[0]) entrada[0].dispatchEvent(new Event("input", { bubbles: true }));
            });

            campoCedula.data("cedulaRecuperada", cedula);
            formulario.data("cedulaComprobada", cedula);

            mensajePersona(campoCedula, "Persona encontrada. Datos recuperados.", "success");

        } catch (error) {
            if (formulario.data("secuenciaPersona") === secuencia) {
                mensajePersona(
                    campoCedula,
                    error.message || "No fue posible buscar la persona.",
                    "danger"
                );
            }

        } finally {
            if (formulario.data("secuenciaPersona") === secuencia) {
                formulario.data("buscandoPersona", false);
            }
        }
    }


    modalesFormulario.on("input", '[data-buscar-persona="true"]', function () {
        const campoCedula = $(this);
        const formulario = campoCedula.closest("form");
        const cedulaAnterior = campoCedula.data("cedulaRecuperada");
        const cedulaActual = String(campoCedula.val() || "").trim();

        formulario.data("secuenciaPersona", (formulario.data("secuenciaPersona") || 0) + 1);
        formulario.data("buscandoPersona", false);
        formulario.removeData("cedulaComprobada");

        mensajePersona(campoCedula, "");

        if (cedulaAnterior && cedulaAnterior !== cedulaActual) {
            limpiarDatosPersona(formulario);
            campoCedula.removeData("cedulaRecuperada");
        }
    });


    modalesFormulario.on("focusout", '[data-buscar-persona="true"]', function () {
        const campoCedula = $(this);
        const cedula = String(campoCedula.val() || "").trim();

        if (cedula && campoCedula.data("cedulaRecuperada") === cedula) return;

        buscarPersona(campoCedula);
    });


    /* ================================================= */
    /* ENVÍO DE LOS FORMULARIOS */
    /* ================================================= */

    formularios.on("submit", async function (evento) {
        evento.preventDefault();

        if (guardando || consultando || redirigiendo) return;

        const formulario = $(this);
        const modal = formulario.closest(".modal");
        const aviso = avisoFormulario(formulario);
        const reglas = obtenerReglasFormulario(this);

        Validaciones.limpiarFormulario(this);

        /* Mostrar los errores de los campos antes de consultar o guardar */
        if (reglas) {
            const resultado = Validaciones.validarFormulario(this, reglas);

            if (!resultado.valido) {
                mostrarMensaje(aviso, "Revise los campos señalados.");

                if (resultado.primerCampo) resultado.primerCampo.focus();

                return;
            }

        } else if (!this.reportValidity()) {
            return;
        }

        if (formulario.data("buscandoPersona")) {
            mostrarMensaje(aviso, "Espere a que termine la búsqueda de la persona.", "warning");
            return;
        }

        const campoCedula = formulario.find('[data-buscar-persona="true"]');

        if (
            campoCedula.length &&
            formulario.data("cedulaComprobada") !== String(campoCedula.val() || "").trim()
        ) {
            mostrarMensaje(aviso, "Compruebe la cédula y revise los datos antes de guardar.", "warning");

            buscarPersona(campoCedula);

            return;
        }

        /* Evitar cambios parciales si no se pudieron consultar las asociaciones */
        const administraInsumos = modal.is("#modalNuevoProveedor, #modalEditarProveedor");

        if (administraInsumos && formulario.data("insumosVerificados") !== true) {
            mostrarMensaje(
                aviso,
                "Primero debemos consultar los insumos y sus asociaciones. " +
                "Cierre y vuelva a abrir el formulario si la consulta falló.",
                "warning"
            );

            return;
        }

        const datos = {};

        /* Convertir id_insumos[] en un arreglo JSON sin perder casillas múltiples */
        formulario.serializeArray().forEach(function (campo) {
            if (campo.name === "id_insumos[]") {
                if (!Array.isArray(datos.id_insumos)) datos.id_insumos = [];

                datos.id_insumos.push(campo.value);

            } else {
                datos[campo.name] = campo.value;
            }
        });

        if (administraInsumos) {
            /* Arreglo vacío significa que se desmarcaron todos los insumos */
            datos.id_insumos ??= [];
            datos.insumos_presentes = "1";
        }

        if (modal.is("#modalCambiarContactoProveedor") && window.Alertas?.disponible()) {
            const nuevoContacto = [
                $("#cambiarNombreContacto").val(),
                $("#cambiarApellidoContacto").val()
            ].filter(Boolean).join(" ");

            const confirmado = await window.Alertas.confirmar(
                "Se asignará a " + nuevoContacto +
                " como contacto de este proveedor. Se conservará el registro de la persona anterior.",
                {
                    titulo: "Cambiar contacto del proveedor",
                    textoConfirmar: "Sí, cambiar contacto",
                    origen: formulario
                }
            );

            if (!confirmado) return;
            if (guardando || consultando || redirigiendo) return;
        }

        const controles = formulario.find(":input")
            .add(modal.find("[data-bs-dismiss='modal']"));

        const estadosControles = controles.map(function () {
            return $(this).prop("disabled");
        }).get();

        let completada = false;
        let mensajeExito = "Operación completada.";
        let primerCampo = null;

        const filtroDeshabilitado = filtroEstado.prop("disabled");

        guardando = true;
        filtroEstado.prop("disabled", true);

        formulario.attr("aria-busy", "true");
        controles.prop("disabled", true);

        mostrarMensaje(aviso, "Procesando…", "info");

        try {
            const respuesta = await solicitarProveedores(
                datos,
                formulario.attr("action") || rutaProveedores,
                true
            );

            if (!respuesta || respuesta.ok !== true) {
                const error = new Error(
                    typeof respuesta?.mensaje === "string"
                        ? respuesta.mensaje
                        : "No fue posible confirmar la operación. Actualice el listado antes de volver a enviarla."
                );

                error.respuesta = respuesta;

                throw error;
            }

            completada = true;

            mensajeExito = typeof respuesta.mensaje === "string"
                ? respuesta.mensaje
                : mensajeExito;

        } catch (error) {
            mostrarMensaje(aviso, error.message || "No fue posible confirmar la operación.");

            window.Alertas?.rechazo(error.message, error.respuesta, formulario);

            primerCampo = Validaciones.mostrarErrores(this, error.respuesta?.errores);

            if (error.respuesta?.errores?.id_insumos) {
                const selector = selectoresInsumos.get(modal[0]);

                if (selector) avisarInsumos(selector, error.respuesta.errores.id_insumos, true);
            }

            if (error.status !== 401) permitirActualizar(aviso);

        } finally {
            guardando = false;

            if (!redirigiendo) {
                filtroEstado.prop("disabled", filtroDeshabilitado);

                controles.each(function (indice) {
                    $(this).prop("disabled", estadosControles[indice]);
                });

                formulario.removeAttr("aria-busy");

                if (primerCampo) primerCampo.focus();
            }
        }


        /* Actualizar el listado después de confirmar el guardado */
        if (completada && !redirigiendo) {
            if (window.bootstrap && window.bootstrap.Modal) {
                const cerrarModal = function () {
                    window.bootstrap.Modal.getOrCreateInstance(modal[0]).hide();
                };

                if (modal.data("abriendo")) {
                    modal.one("shown.bs.modal", cerrarModal);

                } else {
                    cerrarModal();
                }
            }

            await consultarProveedores(mensajeExito);
        }
    });


    /* ================================================= */
    /* CIERRE Y LIMPIEZA DE LOS FORMULARIOS */
    /* ================================================= */

    modalesFormulario.on("show.bs.modal", function (evento) {
        if (guardando || consultando || redirigiendo) {
            evento.preventDefault();
            return;
        }

        $(this).data("abriendo", true);
    });


    modalesFormulario.on("shown.bs.modal", function () {
        $(this).data("abriendo", false);
    });


    modalesFormulario.on("hide.bs.modal", function (evento) {
        if (guardando) evento.preventDefault();
    });


    modalesFormulario.on("hidden.bs.modal", function () {
        const modal = $(this);
        const formulario = modal.find("form").first();

        if (formulario.length) {
            formulario[0].reset();

            Validaciones.limpiarFormulario(formulario[0]);
        }

        formulario.data("secuenciaPersona", (formulario.data("secuenciaPersona") || 0) + 1);
        formulario.removeData("buscandoPersona cedulaComprobada");

        modal.find('[data-buscar-persona="true"]').removeData("cedulaRecuperada").each(function () {
            mensajePersona($(this), "");
        });

        modal.find(".proveedores-aviso-formulario").remove();
        modal.data("abriendo", false);

        if (modal.attr("id") === "modalDesactivarProveedor") {
            $("#desactivarNombreProveedor").empty();
        }
    });


    /* ================================================= */
    /* CONSULTA INICIAL */
    /* ================================================= */

    consultarProveedores();
});