/* ===================================================== */
/* GESTIÓN DE PROVEEDORES */
/* ===================================================== */

$(document).ready(function () {
    "use strict";

    const tablaProveedores = $("#tablaProveedores");
    const modalesFormulario = $("#modalNuevoProveedor, #modalEditarProveedor, #modalDesactivarProveedor");
    const formularios = modalesFormulario.find("form");
    const rutaProveedores = formularios.first().attr("action") || "Index.php?url=proveedores";

    if (!tablaProveedores.length) return;

    let tabla = null;
    let guardando = false;
    let consultando = false;
    let redirigiendo = false;
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
                    url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json"
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
    /* MENSAJES DEL MÓDULO */
    /* ================================================= */

    function mostrarMensaje(contenedor, mensaje, tipo = "danger") {
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
        $("<td>", { class: "columna-empresa" }).text(proveedor.nombre_empresa ?? "").appendTo(fila);
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
            "Ver detalles del proveedor", "#modalDetalleProveedor",
            datos, crearIconoDetalle()
        ));

        acciones.append(crearBoton(
            "btn-action btn-edit btnEditarProveedor",
            "Editar proveedor", "#modalEditarProveedor", datos,
            '<i class="bi bi-pencil-square" aria-hidden="true"></i>'
        ));

        acciones.append(crearBoton(
            "btn-action btn-delete btnDesactivarProveedor",
            "Desactivar proveedor", "#modalDesactivarProveedor",
            {
                "data-id-proveedor": datos["data-id-proveedor"],
                "data-empresa": datos["data-empresa"]
            },
            '<i class="bi bi-trash-fill" aria-hidden="true"></i>'
        ));

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

        const botones = formularios.find(":submit");

        const estadosBotones = botones.map(function () {
            return $(this).prop("disabled");
        }).get();

        consultando = true;
        botones.prop("disabled", true);
        mostrarMensaje(avisoListado, "Actualizando listado…", "info");

        try {
            await tablaPreparada;

            const respuesta = await solicitarProveedores({ accion: "consultar" });

            const listaValida = respuesta && respuesta.ok === true &&
                Array.isArray(respuesta.proveedores) &&
                respuesta.proveedores.every(proveedor => $.isPlainObject(proveedor));

            if (!listaValida) {
                throw new Error(
                    typeof respuesta?.mensaje === "string"
                        ? respuesta.mensaje
                        : "No fue posible actualizar el listado. La respuesta recibida no es válida."
                );
            }

            actualizarTabla(respuesta.proveedores);
            mostrarMensaje(avisoListado, mensajeExito || "Listado actualizado.", "success");

            return true;

        } catch (error) {
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

            if (!redirigiendo) {
                botones.each(function (indice) {
                    $(this).prop("disabled", estadosBotones[indice]);
                });
            }
        }
    }


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

    $(document).on("click", ".btnDesactivarProveedor", function () {
        if (guardando || redirigiendo) return;

        $("#desactivarIdProveedor").val($(this).attr("data-id-proveedor") || "");
        $("#desactivarNombreProveedor").text($(this).attr("data-empresa") || "");
    });


    /* ================================================= */
    /* ENVÍO DE LOS FORMULARIOS */
    /* ================================================= */

    formularios.on("submit", async function (evento) {
        evento.preventDefault();

        if (guardando || consultando || redirigiendo || !this.reportValidity()) return;

        const formulario = $(this);
        const modal = formulario.closest(".modal");
        const aviso = avisoFormulario(formulario);
        const datos = {};

        formulario.serializeArray().forEach(function (campo) {
            datos[campo.name] = campo.value;
        });

        const controles = formulario.find(":input")
            .add(modal.find("[data-bs-dismiss='modal']"));

        const estadosControles = controles.map(function () {
            return $(this).prop("disabled");
        }).get();

        let completada = false;
        let mensajeExito = "Operación completada.";

        guardando = true;
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
                throw new Error(
                    typeof respuesta?.mensaje === "string"
                        ? respuesta.mensaje
                        : "No fue posible confirmar la operación. Actualice el listado antes de volver a enviarla."
                );
            }

            completada = true;
            mensajeExito = typeof respuesta.mensaje === "string"
                ? respuesta.mensaje
                : mensajeExito;

        } catch (error) {
            mostrarMensaje(aviso, error.message || "No fue posible confirmar la operación.");

            if (error.status !== 401) permitirActualizar(aviso);

        } finally {
            guardando = false;

            if (!redirigiendo) {
                controles.each(function (indice) {
                    $(this).prop("disabled", estadosControles[indice]);
                });

                formulario.removeAttr("aria-busy");
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
        if (guardando || redirigiendo) {
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

        if (formulario.length) formulario[0].reset();

        modal.find(".proveedores-aviso-formulario").remove();
        modal.data("abriendo", false);

        if (modal.attr("id") === "modalDesactivarProveedor") {
            $("#desactivarNombreProveedor").empty();
        }
    });
});