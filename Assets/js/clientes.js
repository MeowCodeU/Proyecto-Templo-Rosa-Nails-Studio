/* ===================================================== */
/* GESTIÓN DE CLIENTES */
/* ===================================================== */

$(function () {
    "use strict";

    const urlClientes = "Index.php?url=clientes";
    const $tabla = $("#tablaClientes");
    const $modulo = $(".clientes-module");
    const $modales = $("#modalNuevoCliente, #modalEditarCliente, #modalDesactivarCliente");

    if (!$tabla.length) return;

    let operacionEnCurso = false;
    let redirigiendo = false;
    let versionLista = 0;

    /* Reutiliza los iconos originales, incluso si no hay clientes. */
    const $iconoDetalle = $("#modalDetalleClienteLabel svg").first().clone();
    const $iconoHistorial = $("#modalHistorialServiciosClienteLabel svg").first().clone();
    const $historialVacio = $("#historialServiciosClienteBody").children().clone();

    const $mensajeGeneral = $("<div>", {
        class: "alert alert-light border mb-3",
        role: "status",
        "aria-live": "polite",
        "aria-atomic": "true",
        hidden: true
    }).insertAfter($modulo.find(".module-header").first());


    /* ===================================================== */
    /* DATATABLE DE CLIENTES */
    /* ===================================================== */

    /* Espera a que DataTables termine de cargar su configuración e idioma. */
    let habilitarTabla;

    const tablaPreparada = new Promise(function (resolve) {
        habilitarTabla = resolve;
    });

    const tablaExistente = $.fn.DataTable.isDataTable("#tablaClientes");

    const tablaClientes = tablaExistente
        ? $tabla.DataTable()
        : $tabla.DataTable({
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json"
            },
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            responsive: false,
            autoWidth: false,
            columnDefs: [
                { targets: -1, orderable: false, searchable: false }
            ],
            initComplete: habilitarTabla
        });

    if (tablaExistente) habilitarTabla();


    /* ===================================================== */
    /* SOLICITUDES ASÍNCRONAS */
    /* ===================================================== */

    function solicitarClientes(datos) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: urlClientes,
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

                    const error = new Error(
                        typeof respuesta?.mensaje === "string"
                            ? respuesta.mensaje
                            : "La operación no fue confirmada."
                    );

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

                    const respuesta = xhr.responseJSON;

                    const error = new Error(
                        typeof respuesta?.mensaje === "string" ? respuesta.mensaje : mensaje
                    );

                    error.errores = $.isPlainObject(respuesta?.errores) ? respuesta.errores : {};
                    error.estado = xhr.status;

                    reject(error);
                }
            });
        });
    }


    /* ===================================================== */
    /* MENSAJES GENERALES */
    /* ===================================================== */

    function mostrarMensajeGeneral(texto, esError = false, reintentar = false) {
        $mensajeGeneral
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

                const $boton = $(this).prop("disabled", true);

                try {
                    const actualizada = await actualizarLista();

                    if (actualizada) {
                        mostrarMensajeGeneral("Lista de clientes actualizada.");
                    }
                } catch (error) {
                    mostrarMensajeGeneral(error.message, true, true);
                } finally {
                    $boton.prop("disabled", false);
                }
            }).appendTo($mensajeGeneral);
        }
    }


    /* ===================================================== */
    /* ACTUALIZAR LISTA DE CLIENTES */
    /* ===================================================== */

    async function actualizarLista() {
        if (redirigiendo) return false;

        const turno = ++versionLista;

        $tabla.attr("aria-busy", "true");

        try {
            await tablaPreparada;

            if (redirigiendo || turno !== versionLista) return false;

            const respuesta = await solicitarClientes({ accion: "consultar" });

            if (redirigiendo || turno !== versionLista) return false;

            if (
                !Array.isArray(respuesta.clientes) ||
                !respuesta.clientes.every(cliente => $.isPlainObject(cliente))
            ) {
                throw new Error("La lista recibida no tiene un formato válido.");
            }

            const filas = respuesta.clientes.map(crearFilaCliente);

            tablaClientes.clear();
            tablaClientes.rows.add(filas);
            tablaClientes.draw(false);

            /* Conserva búsqueda, orden y página; corrige una última página vacía. */
            const informacion = tablaClientes.page.info();

            if (
                informacion.pages > 0 &&
                informacion.page >= informacion.pages
            ) {
                tablaClientes.page(informacion.pages - 1).draw("page");
            }

            return true;

        } catch (error) {
            if (turno === versionLista) throw error;

            return false;

        } finally {
            if (turno === versionLista) {
                $tabla.attr("aria-busy", "false");
            }
        }
    }


    /* ===================================================== */
    /* CREAR BOTONES DE ACCIONES */
    /* ===================================================== */

    function crearBotonCliente(cliente, clase, modal, titulo, $icono) {
        const $boton = $("<button>", {
            type: "button",
            class: "btn-action " + clase,
            "data-bs-toggle": "modal",
            "data-bs-target": modal,
            title: titulo,
            "aria-label": titulo
        });

        const atributos = {
            "id-cliente": cliente.id_cliente,
            cedula: cliente.cedula,
            nombre: cliente.nombre,
            apellido: cliente.apellido,
            telefono: cliente.telefono,
            correo: cliente.correo,
            direccion: cliente.direccion,
            ciudad: cliente.ciudad,
            alergias: cliente.alergias,
            visitas: cliente.visitas ?? 0,
            estado: cliente.estado ?? "ACTIVO"
        };

        Object.entries(atributos).forEach(function ([campo, valor]) {
            $boton.attr("data-" + campo, valor ?? "");
        });

        return $boton.append($icono);
    }


    /* ===================================================== */
    /* CREAR FILAS DE LA TABLA */
    /* ===================================================== */

    function crearFilaCliente(cliente) {
        const $fila = $("<tr>");

        [
            "cedula",
            "nombre",
            "apellido",
            "telefono",
            "visitas",
            "alergias"
        ].forEach(function (campo) {
            $("<td>").text(cliente[campo] ?? (campo === "visitas" ? 0 : "")).appendTo($fila);
        });

        const $acciones = $("<div>", { class: "btn-group-actions" });

        const $detalle = $("<span>", {
            class: "detalle-accion-icono",
            "aria-hidden": "true"
        }).append($iconoDetalle.clone());

        const $historial = $("<span>", {
            class: "cliente-historial-icono",
            "aria-hidden": "true"
        }).append($iconoHistorial.clone());

        $acciones.append(
            crearBotonCliente(
                cliente,
                "btn-detail btnDetalleCliente",
                "#modalDetalleCliente",
                "Ver detalles del cliente",
                $detalle
            ),

            crearBotonCliente(
                cliente,
                "btn-history btnHistorialCliente",
                "#modalHistorialServiciosCliente",
                "Ver historial de servicios",
                $historial
            ),

            crearBotonCliente(
                cliente,
                "btn-edit btnEditarCliente",
                "#modalEditarCliente",
                "Editar cliente",
                $("<i>", {
                    class: "bi bi-pencil-fill",
                    "aria-hidden": "true"
                })
            ),

            crearBotonCliente(
                cliente,
                "btn-deactivate btnDesactivarCliente",
                "#modalDesactivarCliente",
                "Desactivar cliente",
                $("<i>", {
                    class: "bi bi-trash-fill",
                    "aria-hidden": "true"
                })
            )
        );

        $("<td>", { class: "columna-acciones" }).append($acciones).appendTo($fila);

        return $fila[0];
    }


    /* ===================================================== */
    /* MENSAJES Y ERRORES DE LOS FORMULARIOS */
    /* ===================================================== */

    function mensajeFormulario($formulario) {
        let $mensaje = $formulario.find(".clientes-mensaje-formulario");

        if (!$mensaje.length) {
            $mensaje = $("<div>", {
                class: "alert alert-danger clientes-mensaje-formulario",
                role: "alert",
                hidden: true
            }).prependTo($formulario.find(".modal-body").first());
        }

        return $mensaje;
    }

    function limpiarErrores($formulario) {
        $formulario.find("[data-error-clientes]")
            .removeClass("is-invalid")
            .removeAttr("aria-invalid data-error-clientes");

        $formulario.find(".clientes-error-servidor").remove();

        mensajeFormulario($formulario).empty().prop("hidden", true);
    }

    function mostrarErrores($formulario, error) {
        mensajeFormulario($formulario).text(error.message).prop("hidden", false);

        let primerCampo = null;

        Object.entries(error.errores || {}).forEach(function ([nombre, texto]) {
            const $campo = $formulario.find("[name]")
                .filter(function () {
                    return $(this).attr("name") === nombre;
                })
                .first();

            if (
                !$campo.length ||
                $campo.attr("type") === "hidden"
            ) {
                return;
            }

            $campo.addClass("is-invalid").attr({
                "aria-invalid": "true",
                "data-error-clientes": "true"
            });

            $("<div>", { class: "invalid-feedback clientes-error-servidor" })
                .text(texto).insertAfter($campo);

            if (!primerCampo) primerCampo = $campo;
        });

        $formulario.find(".modal-body").scrollTop(0);

        return primerCampo;
    }


    /* ===================================================== */
    /* CONTENIDO Y LIMPIEZA DE LOS MODALES */
    /* ===================================================== */

    function datoBoton($boton, nombre, alternativo = "") {
        return $boton.attr("data-" + nombre) ?? alternativo;
    }

    $modales.on("show.bs.modal", function (evento) {
        if (operacionEnCurso || redirigiendo) {
            evento.preventDefault();
            return;
        }

        const $modal = $(this);
        const $formulario = $modal.find("form").first();

        if (!$formulario.length) {
            evento.preventDefault();
            return;
        }

        $formulario[0].reset();
        limpiarErrores($formulario);

        const $boton = $(evento.relatedTarget);

        if ($modal.attr("id") === "modalEditarCliente") {
            [
                "cedula",
                "nombre",
                "apellido",
                "telefono",
                "correo",
                "direccion",
                "ciudad",
                "alergias"
            ].forEach(function (campo) {
                $formulario.find('[name="' + campo + '"]').val(datoBoton($boton, campo));
            });
        }

        if ($modal.attr("id") === "modalDesactivarCliente") {
            $("#desactivarCedula").val(datoBoton($boton, "cedula"));

            $("#desactivarNombreCliente").text([
                datoBoton($boton, "nombre"),
                datoBoton($boton, "apellido")
            ].filter(Boolean).join(" "));
        }
    });

    $modales.on("hide.bs.modal", function (evento) {
        if ($(this).find("form").data("enviando")) {
            evento.preventDefault();
        }
    });

    $modales.on("hidden.bs.modal", function () {
        const $formulario = $(this).find("form").first();

        if (!$formulario.length) return;

        $formulario[0].reset();
        limpiarErrores($formulario);
    });


    /* ===================================================== */
    /* LLENAR MODAL DE DETALLES */
    /* ===================================================== */

    $("#modalDetalleCliente").on("show.bs.modal", function (evento) {
        const $boton = $(evento.relatedTarget);

        const campos = {
            detalleCedula: "cedula",
            detalleNombre: "nombre",
            detalleApellido: "apellido",
            detalleTelefono: "telefono",
            detalleCorreo: "correo",
            detalleDireccion: "direccion",
            detalleCiudad: "ciudad",
            detalleAlergias: "alergias"
        };

        Object.entries(campos).forEach(function ([id, campo]) {
            $("#" + id).text(datoBoton($boton, campo) || "No registrado");
        });

        $("#detalleVisitas").text(datoBoton($boton, "visitas", "0"));
        $("#detalleEstado").text(datoBoton($boton, "estado", "ACTIVO"));
    });


    /* ===================================================== */
    /* HISTORIAL DE SERVICIOS */
    /* ===================================================== */

    /* Preparar los datos del cliente y restablecer el contenido inicial del historial */
    $("#modalHistorialServiciosCliente").on("show.bs.modal", function (evento) {
        const $boton = $(evento.relatedTarget);

        $("#historialNombreCliente").text([
            datoBoton($boton, "nombre"),
            datoBoton($boton, "apellido")
        ].filter(Boolean).join(" "));

        $(this).attr("data-id-cliente", datoBoton($boton, "id-cliente"));

        $("#historialServiciosClienteBody").empty().append($historialVacio.clone());
    });


    /* ===================================================== */
    /* REGISTRAR, MODIFICAR Y DESACTIVAR */
    /* ===================================================== */

    $modales.find("form").on("submit", async function (evento) {
        evento.preventDefault();

        if (operacionEnCurso || redirigiendo) return;

        const $formulario = $(this);
        limpiarErrores($formulario);

        if (!this.reportValidity()) return;

        const datos = {};

        $formulario.serializeArray().forEach(function (campo) {
            datos[campo.name] = campo.value;
        });

        const controles = [];

        $formulario.find("input, select, textarea, button").each(function () {
            const $elemento = $(this);

            controles.push({
                elemento: $elemento,
                deshabilitado: $elemento.prop("disabled")
            });

            $elemento.prop("disabled", true);
        });

        const $enviar = $formulario.find('[type="submit"]');
        const contenidoBoton = $enviar.html();

        $enviar.text("Procesando...");

        $formulario.data("enviando", true).attr("aria-busy", "true");

        operacionEnCurso = true;
        ++versionLista;

        $tabla.attr("aria-busy", "false");
        $mensajeGeneral.prop("hidden", true);

        let primerCampo = null;
        let respuestaConfirmada = null;
        let mensajeExito = "Operación completada.";

        try {
            const respuesta = await solicitarClientes(datos);

            respuestaConfirmada = respuesta;
            mensajeExito = typeof respuesta.mensaje === "string" ? respuesta.mensaje : mensajeExito;

            /* El modal se cierra únicamente después de confirmar el guardado. */
            $formulario.data("enviando", false);

            const $modal = $formulario.closest(".modal");

            const cerrarModal = function () {
                window.bootstrap.Modal.getOrCreateInstance($modal[0]).hide();
            };

            /* Si aún está terminando de abrirse, lo cierra al finalizar. */
            $modal.one("shown.bs.modal", cerrarModal);

            $modal.one("hidden.bs.modal", function () {
                $modal.off("shown.bs.modal", cerrarModal);
            });

            cerrarModal();

            mostrarMensajeGeneral(mensajeExito);

            try {
                await actualizarLista();

            } catch (error) {
                mostrarMensajeGeneral(
                    mensajeExito + " No se pudo actualizar la lista. " +
                    "Use Actualizar lista; la operación ya fue confirmada.",
                    true,
                    true
                );
            }

        } catch (error) {
            if (respuestaConfirmada) {
                mostrarMensajeGeneral(
                    mensajeExito + " No se pudo actualizar la vista. " +
                    "La operación ya fue confirmada; actualice la lista antes de repetirla.",
                    true,
                    true
                );

            } else if (!redirigiendo) {
                primerCampo = mostrarErrores($formulario, error);
            }

        } finally {
            controles.forEach(function (control) {
                control.elemento.prop("disabled", control.deshabilitado);
            });

            $enviar.html(contenidoBoton);

            $formulario.data("enviando", false).attr("aria-busy", "false");

            operacionEnCurso = false;

            if (primerCampo && !redirigiendo) {
                primerCampo.trigger("focus");
            }
        }
    });


    /* ===================================================== */
    /* CONSULTA INICIAL */
    /* ===================================================== */

    /* Actualiza también la consulta inicial mediante AJAX. */
    actualizarLista().catch(function (error) {
        mostrarMensajeGeneral(error.message, true, true);
    });
});