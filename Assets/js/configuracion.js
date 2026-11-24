/* ===================================================== */
/* GESTIÓN DE CONFIGURACIÓN */
/* ===================================================== */

import { Validaciones } from "./validaciones/validaciones.js";

$(function () {
    "use strict";

    const modulo = $(".configuracion-module");
    const urlConfiguracion = "Index.php?url=configuracion";

    if (!modulo.length) return;

    let operacionEnCurso = false;
    let redirigiendo = false;


    /* ================================================= */
    /* CATÁLOGOS DEL MÓDULO */
    /* ================================================= */

    const catalogos = [
        {
            clave: "servicios",
            reglas: Validaciones.reglasServicios,
            titulo: "Servicios",
            registro: "servicio",
            tabla: "#tablaConfigServicios",
            nuevo: "#modalNuevoServicio",
            editar: "#modalEditarServicio",
            desactivar: "#modalEstadoServicio",
            botonEditar: "btnEditarServicio",
            botonDesactivar: "btnDesactivarServicio",
            identificador: "id_servicio",
            nombre: "nombre_servicio",
            estado: "estado_servicio",
            nombreConfirmacion: "#nombreEstadoServicio",
            columnas: ["nombre_servicio", "descripcion", "precio", "duracion_estimada"],
            atributos: {
                id_servicio: "id-servicio",
                nombre_servicio: "nombre-servicio",
                descripcion: "descripcion",
                precio: "precio",
                duracion_estimada: "duracion-estimada",
                estado_servicio: "estado-servicio"
            }
        },
        {
            clave: "estados_agendamiento",
            reglas: Validaciones.reglasEstadosAgendamiento,
            titulo: "Estados de agendamiento",
            registro: "estado de agendamiento",
            tabla: "#tablaConfigEstadosAgendamiento",
            nuevo: "#modalNuevoEstadoAgendamiento",
            editar: "#modalEditarEstadoAgendamiento",
            desactivar: "#modalEstadoEstadoAgendamiento",
            botonEditar: "btnEditarEstadoAgendamiento",
            botonDesactivar: "btnDesactivarEstadoAgendamiento",
            identificador: "id_estado_agendamiento",
            nombre: "nombre_estado",
            estado: "estado_registro",
            nombreConfirmacion: "#nombreEstadoEstadoAgendamiento",
            columnas: ["nombre_estado", "descripcion"],
            atributos: {
                id_estado_agendamiento: "id-estado-agendamiento",
                nombre_estado: "nombre-estado",
                descripcion: "descripcion",
                estado_registro: "estado-registro"
            }
        },
        {
            clave: "tipos_agendamiento",
            reglas: Validaciones.reglasTiposAgendamiento,
            titulo: "Tipos de agendamiento",
            registro: "tipo de agendamiento",
            tabla: "#tablaConfigTiposAgendamiento",
            nuevo: "#modalNuevoTipoAgendamiento",
            editar: "#modalEditarTipoAgendamiento",
            desactivar: "#modalEstadoTipoAgendamiento",
            botonEditar: "btnEditarTipoAgendamiento",
            botonDesactivar: "btnDesactivarTipoAgendamiento",
            identificador: "id_tipo_agendamiento",
            nombre: "nombre_tipo_agendamiento",
            estado: "estado_tipo_agendamiento",
            nombreConfirmacion: "#nombreEstadoTipoAgendamiento",
            columnas: ["nombre_tipo_agendamiento", "descripcion"],
            atributos: {
                id_tipo_agendamiento: "id-tipo-agendamiento",
                nombre_tipo_agendamiento: "nombre-tipo-agendamiento",
                descripcion: "descripcion",
                estado_tipo_agendamiento: "estado-tipo-agendamiento"
            }
        }
    ];


    /* ================================================= */
    /* INICIALIZACIÓN DE LAS TABLAS */
    /* ================================================= */

    catalogos.forEach(function (catalogo) {
        catalogo.elementoTabla = $(catalogo.tabla);
        catalogo.modales = $([catalogo.nuevo, catalogo.editar, catalogo.desactivar].join(", "));
        catalogo.versionLista = 0;

        catalogo.mensaje = $("<div>", {
            class: "alert alert-light border mb-3",
            role: "status",
            "aria-live": "polite",
            hidden: true
        }).insertAfter(catalogo.elementoTabla.closest(".tab-pane").find(".config-tab-head"));

        let habilitarTabla;

        catalogo.tablaPreparada = new Promise(function (resolve) {
            habilitarTabla = resolve;
        });

        const tablaExistente = $.fn.DataTable.isDataTable(catalogo.tabla);

        catalogo.instanciaTabla = tablaExistente
            ? catalogo.elementoTabla.DataTable()
            : catalogo.elementoTabla.DataTable({
                language: {
                    url: "Assets/libs/datatables/i18n/es-ES.json"
                },
                pageLength: 4,
                lengthMenu: [4, 8, 16, 20],
                responsive: false,
                autoWidth: false,
                columnDefs: [
                    { targets: -1, orderable: false, searchable: false }
                ],
                initComplete: habilitarTabla
            });

        if (tablaExistente) habilitarTabla();
    });


    /* Ajustar las columnas al mostrar una pestaña */
    $("#configTabs").on("shown.bs.tab", 'button[data-bs-toggle="tab"]', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });


    /* ================================================= */
    /* SOLICITUDES ASÍNCRONAS */
    /* ================================================= */

    function solicitarConfiguracion(datos) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: urlConfiguracion,
                method: "POST",
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                headers: { "X-Requested-With": "XMLHttpRequest" },
                data: JSON.stringify(datos),
                processData: false,
                timeout: 30000,

                success: function (respuesta) {
                    if (respuesta && respuesta.ok === true) {
                        resolve(respuesta);
                        return;
                    }

                    const mensaje = typeof respuesta?.mensaje === "string"
                        ? respuesta.mensaje
                        : "No se pudo confirmar la operación. Actualice la lista antes de repetirla.";

                    const error = new Error(mensaje);

                    error.errores = $.isPlainObject(respuesta?.errores) ? respuesta.errores : {};

                    reject(error);
                },

                error: function (xhr, estado) {
                    if (xhr.status === 401) {
                        redirigiendo = true;
                        window.location.assign("Index.php?url=login&sesion=expirada");
                    }

                    const ambiguo = xhr.status === 0 || estado === "timeout" || estado === "parsererror";

                    const mensaje = ambiguo
                        ? "No se pudo confirmar la respuesta. Actualice la lista antes de repetir la operación."
                        : "No fue posible completar la solicitud. Intente nuevamente.";

                    const error = new Error(
                        typeof xhr.responseJSON?.mensaje === "string" ? xhr.responseJSON.mensaje : mensaje
                    );

                    error.errores = $.isPlainObject(xhr.responseJSON?.errores) ? xhr.responseJSON.errores : {};
                    error.status = xhr.status;
                    error.respuesta = xhr.responseJSON;

                    reject(error);
                }
            });
        });
    }


    /* ================================================= */
    /* MENSAJES DEL CATÁLOGO Y DEL FORMULARIO */
    /* ================================================= */

    function mostrarMensaje(catalogo, texto, esError = false, reintentar = false, tipoAviso = null) {
        if (!redirigiendo && window.Alertas?.disponible()) {
            window.Alertas.notificar(texto, tipoAviso || (esError ? "error" : "success"));
        }

        catalogo.mensaje
            .empty()
            .text(texto)
            .prop("hidden", false)
            .toggleClass("alert-danger", esError)
            .toggleClass("alert-light", !esError)
            .attr("role", esError ? "alert" : "status");

        if (reintentar) {
            $("<button>", {
                type: "button",
                class: "btn-templo-secondary ms-2",
                text: "Actualizar lista"
            }).on("click", async function () {
                if (operacionEnCurso || redirigiendo) return;

                const boton = $(this).prop("disabled", true);

                try {
                    if (await actualizarLista(catalogo)) {
                        mostrarMensaje(catalogo, "Lista actualizada correctamente.");
                    }

                } catch (error) {
                    mostrarMensaje(catalogo, error.message, true, true);

                } finally {
                    boton.prop("disabled", false);
                }
            }).appendTo(catalogo.mensaje);
        }
    }


    function mensajeFormulario(formulario) {
        let mensaje = formulario.find(".configuracion-mensaje-formulario");

        if (!mensaje.length) {
            mensaje = $("<div>", {
                class: "estado-error configuracion-mensaje-formulario mb-3",
                role: "alert",
                hidden: true
            }).prependTo(formulario.find(".modal-body").first());
        }

        return mensaje;
    }


    /* ================================================= */
    /* VALIDACIONES DE LOS FORMULARIOS */
    /* ================================================= */

    function reglasFormulario(catalogo, formulario) {
        const modal = "#" + formulario.closest(".modal").attr("id");
        const reglas = modal === catalogo.desactivar ? {} : { ...catalogo.reglas };

        if (modal !== catalogo.nuevo) {
            reglas[catalogo.identificador] = {
                comprobar: valor => Validaciones.validarEntero(valor, 1)
                    ? null : "No se pudo identificar el registro seleccionado."
            };
        }

        return reglas;
    }


    catalogos.forEach(function (catalogo) {
        catalogo.modales.find("form").each(function () {
            this.noValidate = true;

            Validaciones.activar(this, reglasFormulario(catalogo, $(this)));
        });
    });


    /* ================================================= */
    /* BOTONES Y FILAS DEL LISTADO */
    /* ================================================= */

    function crearBoton(catalogo, registro, desactivar = false) {
        const boton = $("<button>", {
            type: "button",
            class: "btn-action " + (
                desactivar
                    ? "btn-deactivate " + catalogo.botonDesactivar
                    : "btn-edit " + catalogo.botonEditar
            ),
            "data-bs-toggle": "modal",
            "data-bs-target": desactivar ? catalogo.desactivar : catalogo.editar,
            title: (desactivar ? "Desactivar " : "Editar ") + catalogo.registro,
            "aria-label": (desactivar ? "Desactivar " : "Editar ") + catalogo.registro
        });

        Object.entries(catalogo.atributos).forEach(function ([campo, atributo]) {
            boton.attr("data-" + atributo, registro[campo] ?? "");
        });

        return boton.append($("<i>", {
            class: desactivar ? "bi bi-trash-fill" : "bi bi-pencil-fill",
            "aria-hidden": "true"
        }));
    }


    function crearFila(catalogo, registro) {
        const fila = $("<tr>");
        const estado = String(registro[catalogo.estado] ?? "").trim().toUpperCase();

        catalogo.columnas.forEach(function (campo) {
            $("<td>").text(registro[campo] ?? "").appendTo(fila);
        });

        const insignia = $("<span>", {
            class: "badge " + (estado === "ACTIVO" ? "estado-registro-activo" : "estado-registro-inactivo"),
            text: estado
        });

        $("<td>").append(insignia).appendTo(fila);

        const acciones = $("<div>", { class: "btn-group-actions" });

        acciones.append(
            crearBoton(catalogo, registro),
            crearBoton(catalogo, registro, true)
        );

        if (catalogo.clave === "servicios") {
            $("<button>", {
                type: "button",
                class: "btn-action btn-detail btnInsumosServicio",
                "data-bs-toggle": "modal",
                "data-bs-target": "#modalInsumosServicio",
                "data-id-servicio": registro.id_servicio,
                "data-nombre-servicio": registro.nombre_servicio,
                title: "Insumos del servicio",
                "aria-label": "Insumos del servicio"
            }).append($("<i>", { class: "bi bi-box-seam", "aria-hidden": "true" })).prependTo(acciones);
        }

        $("<td>").append(acciones).appendTo(fila);

        return fila[0];
    }


    /* ================================================= */
    /* ACTUALIZACIÓN DE LOS CATÁLOGOS */
    /* ================================================= */

    async function actualizarLista(catalogo) {
        if (redirigiendo) return false;

        const turno = ++catalogo.versionLista;

        catalogo.elementoTabla.attr("aria-busy", "true");

        try {
            await catalogo.tablaPreparada;

            if (redirigiendo || turno !== catalogo.versionLista) return false;

            const respuesta = await solicitarConfiguracion({
                accion: "consultar",
                catalogo: catalogo.clave
            });

            if (redirigiendo || turno !== catalogo.versionLista) return false;

            if (
                respuesta.catalogo !== catalogo.clave ||
                !Array.isArray(respuesta.registros) ||
                !respuesta.registros.every(registro => $.isPlainObject(registro))
            ) {
                throw new Error("La lista recibida no tiene un formato válido.");
            }

            const filas = respuesta.registros.map(registro => crearFila(catalogo, registro));
            const tabla = catalogo.instanciaTabla;
            const paginaActual = tabla.page();

            tabla.clear();
            tabla.rows.add(filas);
            tabla.draw(false);

            const ultimaPagina = Math.max(tabla.page.info().pages - 1, 0);

            if (paginaActual > ultimaPagina) tabla.page(ultimaPagina).draw("page");

            return true;

        } catch (error) {
            if (turno === catalogo.versionLista) throw error;

            return false;

        } finally {
            if (turno === catalogo.versionLista) {
                catalogo.elementoTabla.attr("aria-busy", "false");
            }
        }
    }


    /* ================================================= */
    /* CONTENIDO Y LIMPIEZA DE LOS MODALES */
    /* ================================================= */

    catalogos.forEach(function (catalogo) {
        catalogo.modales.on("show.bs.modal", function (evento) {
            if (operacionEnCurso || redirigiendo) {
                evento.preventDefault();
                return;
            }

            const modal = $(this);
            const formulario = modal.find("form").first();
            const boton = $(evento.relatedTarget);

            if (!formulario.length) {
                evento.preventDefault();
                return;
            }

            formulario[0].reset();

            Validaciones.limpiarFormulario(formulario[0]);

            mensajeFormulario(formulario).empty().prop("hidden", true);

            if ("#" + modal.attr("id") === catalogo.editar) {
                Object.entries(catalogo.atributos).forEach(function ([campo, atributo]) {
                    formulario.find('[name="' + campo + '"]')
                        .val(boton.attr("data-" + atributo) ?? "");
                });
            }

            if ("#" + modal.attr("id") === catalogo.desactivar) {
                const atributoId = catalogo.atributos[catalogo.identificador];
                const atributoNombre = catalogo.atributos[catalogo.nombre];

                formulario.find('[name="' + catalogo.identificador + '"]')
                    .val(boton.attr("data-" + atributoId) ?? "");

                formulario.find('[name="' + catalogo.estado + '"]').val("INACTIVO");

                $(catalogo.nombreConfirmacion).text(boton.attr("data-" + atributoNombre) ?? "");
            }
        });


        catalogo.modales.on("hide.bs.modal", function (evento) {
            if ($(this).find("form").data("enviando")) evento.preventDefault();
        });


        catalogo.modales.on("hidden.bs.modal", function () {
            const modal = $(this);
            const formulario = modal.find("form").first();

            if (!formulario.length) return;

            formulario[0].reset();

            Validaciones.limpiarFormulario(formulario[0]);

            mensajeFormulario(formulario).empty().prop("hidden", true);

            if ("#" + modal.attr("id") === catalogo.desactivar) {
                $(catalogo.nombreConfirmacion).empty();
            }
        });


        /* ================================================= */
        /* REGISTRAR, MODIFICAR Y DESACTIVAR */
        /* ================================================= */

        catalogo.modales.find("form").on("submit", async function (evento) {
            evento.preventDefault();

            if (operacionEnCurso || redirigiendo) return;

            const formulario = $(this);
            const aviso = mensajeFormulario(formulario).empty().prop("hidden", true);

            Validaciones.limpiarFormulario(this);

            const validacion = Validaciones.validarFormulario(
                this,
                reglasFormulario(catalogo, formulario)
            );

            if (!validacion.valido) {
                aviso.text("Revise los campos señalados.").prop("hidden", false);

                if (validacion.primerCampo?.type !== "hidden") validacion.primerCampo?.focus();

                return;
            }

            const datos = {};

            formulario.serializeArray().forEach(function (campo) {
                datos[campo.name] = campo.value;
            });

            const controles = [];

            formulario.find("input, select, textarea, button").each(function () {
                const elemento = $(this);

                controles.push({
                    elemento,
                    deshabilitado: elemento.prop("disabled")
                });

                elemento.prop("disabled", true);
            });

            const enviar = formulario.find('[type="submit"]');
            const contenidoBoton = enviar.html();

            enviar.text("Procesando...");

            formulario.data("enviando", true).attr("aria-busy", "true");

            operacionEnCurso = true;
            ++catalogo.versionLista;

            catalogo.elementoTabla.attr("aria-busy", "false");
            catalogo.mensaje.prop("hidden", true);

            let primerCampo = null;
            let respuestaConfirmada = null;
            let mensajeExito = "Operación completada.";

            try {
                const respuesta = await solicitarConfiguracion(datos);

                respuestaConfirmada = respuesta;
                mensajeExito = typeof respuesta.mensaje === "string" ? respuesta.mensaje : mensajeExito;

                formulario.data("enviando", false);

                const modal = formulario.closest(".modal");

                const cerrarModal = function () {
                    window.bootstrap.Modal.getOrCreateInstance(modal[0]).hide();
                };

                /* Completar el cierre si el formulario todavía estaba abriéndose */
                if (modal.hasClass("show")) modal.one("shown.bs.modal", cerrarModal);

                if (modal.hasClass("show")) modal.one("hidden.bs.modal", function () {
                    modal.off("shown.bs.modal", cerrarModal);
                });

                cerrarModal();

                try {
                    await actualizarLista(catalogo);

                    mostrarMensaje(catalogo, mensajeExito);

                } catch (error) {
                    mostrarMensaje(
                        catalogo,
                        mensajeExito + " No se pudo actualizar la lista. La operación ya fue confirmada.",
                        true,
                        true,
                        "warning"
                    );
                }

            } catch (error) {
                if (respuestaConfirmada) {
                    mostrarMensaje(
                        catalogo,
                        mensajeExito + " No se pudo actualizar la vista. " +
                        "La operación ya fue confirmada; actualice la lista antes de repetirla.",
                        true,
                        true,
                        "warning"
                    );

                } else if (!redirigiendo) {
                    window.Alertas?.rechazo(error.message, error, formulario);

                    aviso.text(error.message).prop("hidden", false);

                    primerCampo = Validaciones.mostrarErrores(this, error.errores);

                    formulario.find(".modal-body").scrollTop(0);
                }

            } finally {
                controles.forEach(function (control) {
                    control.elemento.prop("disabled", control.deshabilitado);
                });

                enviar.html(contenidoBoton);

                formulario.data("enviando", false).attr("aria-busy", "false");

                operacionEnCurso = false;

                if (primerCampo && !redirigiendo) primerCampo.focus();
            }
        });
    });


    /* ================================================= */
    /* CONSULTA INICIAL DE LOS CATÁLOGOS */
    /* ================================================= */

    catalogos.forEach(function (catalogo) {
        actualizarLista(catalogo).catch(function (error) {
            mostrarMensaje(catalogo, error.message, true, true);
        });
    });


    /* ================================================= */
    /* CONFIGURAR LA RESERVA DE INSUMOS POR SERVICIO */
    /* ================================================= */

    const modalInsumosServicio = $("#modalInsumosServicio");
    const formularioReserva = $("#formInsumosServicio");
    const avisoReserva = $("#mensajeInsumosServicio");
    const filasReserva = $("#filasInsumosServicio");
    const guardarReserva = $("#guardarInsumosServicio");

    let versionReserva = 0;
    let guardandoReserva = false;

    modalInsumosServicio.on("show.bs.modal", async function (evento) {
        if (operacionEnCurso || guardandoReserva || redirigiendo) {
            evento.preventDefault();
            return;
        }

        const turno = ++versionReserva;
        const boton = $(evento.relatedTarget);
        const id = boton.attr("data-id-servicio");

        $("#reservaIdServicio").val(id);
        $("#reservaNombreServicio").text(boton.attr("data-nombre-servicio") || "");

        filasReserva.empty();
        avisoReserva.empty().prop("hidden", true);
        guardarReserva.prop("disabled", true);

        try {
            const respuesta = await solicitarConfiguracion({
                accion: "consultar_insumos_servicio",
                id_servicio: id
            });

            if (turno !== versionReserva) return;

            if (!Array.isArray(respuesta.insumos)) {
                throw new Error("No se pudieron consultar los insumos.");
            }

            respuesta.insumos.forEach(function (insumo) {
                const fila = $("<tr>");
                const etiqueta = insumo.nombre_insumo +
                    (insumo.estado_insumo !== "ACTIVO" ? " (inactivo)" : "");

                $("<td>").text(etiqueta).appendTo(fila);
                $("<td>").text(insumo.presentacion || "—").appendTo(fila);

                $("<td>").append($("<input>", {
                    type: "number",
                    class: "form-control templo-input",
                    min: 0,
                    max: 99999999,
                    step: 1,
                    value: insumo.cantidad,
                    "data-insumo-reserva": insumo.id_insumo,
                    "aria-label": "Unidades de " + etiqueta,
                    required: true
                })).appendTo(fila);

                filasReserva.append(fila);
            });

            if (!respuesta.insumos.length) {
                avisoReserva.text("Registre primero los insumos del negocio.").prop("hidden", false);
            } else {
                guardarReserva.prop("disabled", false);
            }

        } catch (error) {
            if (turno === versionReserva) {
                avisoReserva.text(error.message).prop("hidden", false);
            }
        }
    });


    modalInsumosServicio.on("hide.bs.modal", function (evento) {
        if (guardandoReserva) evento.preventDefault();
        else versionReserva++;
    });


    formularioReserva.on("submit", async function (evento) {
        evento.preventDefault();

        if (guardandoReserva || operacionEnCurso || redirigiendo || guardarReserva.prop("disabled")) return;
        if (!this.reportValidity()) return;

        const insumos = [];

        filasReserva.find("[data-insumo-reserva]").each(function () {
            if (Number(this.value) > 0) {
                insumos.push({
                    id_insumo: this.getAttribute("data-insumo-reserva"),
                    cantidad: this.value
                });
            }
        });

        guardandoReserva = true;
        operacionEnCurso = true;

        guardarReserva.prop("disabled", true);
        formularioReserva.attr("aria-busy", "true");
        avisoReserva.empty().prop("hidden", true);
        filasReserva.find("input").prop("disabled", true);

        let respuestaGuardada = null;

        try {
            respuestaGuardada = await solicitarConfiguracion({
                accion: "guardar_insumos_servicio",
                token_csrf: formularioReserva.find('[name="token_csrf"]').val(),
                id_servicio: $("#reservaIdServicio").val(),
                insumos: insumos
            });

        } catch (error) {
            avisoReserva.text(error.message).prop("hidden", false);

        } finally {
            guardandoReserva = false;
            operacionEnCurso = false;

            guardarReserva.prop("disabled", false);
            filasReserva.find("input").prop("disabled", false);
            formularioReserva.removeAttr("aria-busy");
        }

        if (respuestaGuardada) {
            bootstrap.Modal.getOrCreateInstance(modalInsumosServicio[0]).hide();
            window.Alertas?.notificar(respuestaGuardada.mensaje, "success");
        }
    });

});