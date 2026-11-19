/* ===================================================== */
/* GESTIÓN DE CLIENTES */
/* ===================================================== */

import { Regex } from "./validaciones/regex.js";
import { Validaciones } from "./validaciones/validaciones.js";

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

    /* Reutilizar los iconos definidos en la vista */
    const $iconoDetalle = $("#modalDetalleClienteLabel svg").first().clone();
    const $iconoHistorial = $("#modalHistorialServiciosClienteLabel svg").first().clone();

    const $modalHistorial = $("#modalHistorialServiciosCliente");
    const $cuerpoHistorial = $("#historialServiciosClienteBody");
    const $tablaHistorial = $("#tablaHistorialServiciosCliente");
    const $historialVacio = $cuerpoHistorial.children().clone();

    let clienteHistorial = null;
    let historialAbierto = false;
    let versionHistorial = 0;
    let solicitudHistorial = null;

    const $mensajeGeneral = $("<div>", {
        class: "alert alert-light border mb-3",
        role: "status",
        "aria-live": "polite",
        "aria-atomic": "true",
        hidden: true
    }).insertAfter($modulo.find(".module-header").first());


    /* ================================================= */
    /* DATATABLE DE CLIENTES */
    /* ================================================= */

    /* Esperar la configuración de la tabla */
    let habilitarTabla;

    const tablaPreparada = new Promise(function (resolve) {
        habilitarTabla = resolve;
    });

    const tablaExistente = $.fn.DataTable.isDataTable("#tablaClientes");

    const tablaClientes = tablaExistente
        ? $tabla.DataTable()
        : $tabla.DataTable({
            language: {
                url: "Assets/libs/datatables/i18n/es-ES.json"
            },
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            responsive: false,
            autoWidth: false,
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    searchable: false
                }
            ],
            initComplete: habilitarTabla
        });

    if (tablaExistente) habilitarTabla();


    /* ================================================= */
    /* SOLICITUDES ASÍNCRONAS */
    /* ================================================= */

    function solicitarClientes(datos) {
        let peticion;

        const esConsulta = [
            "consultar",
            "buscar",
            "consultar_persona",
            "historial"
        ].includes(datos.accion);

        const promesa = new Promise(function (resolve, reject) {
            peticion = $.ajax({
                url: urlClientes,
                method: "POST",
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                },
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
                            : "Operación no confirmada."
                    );

                    error.errores = $.isPlainObject(respuesta?.errores)
                        ? respuesta.errores
                        : {};

                    reject(error);
                },

                error: function (xhr, estado) {
                    if (xhr.status === 401) {
                        redirigiendo = true;

                        window.location.assign(
                            "Index.php?url=login&sesion=expirada"
                        );
                    }

                    const ambiguo = xhr.status === 0 ||
                        estado === "timeout" ||
                        estado === "parsererror";

                    const mensaje = esConsulta
                        ? "No se pudo consultar. Intenta de nuevo."
                        : ambiguo
                            ? "Respuesta sin confirmar. Actualiza la lista antes de reintentar."
                            : "No se completó la operación. Intenta de nuevo.";

                    const respuesta = xhr.responseJSON;

                    const error = new Error(
                        typeof respuesta?.mensaje === "string"
                            ? respuesta.mensaje
                            : mensaje
                    );

                    error.errores = $.isPlainObject(respuesta?.errores)
                        ? respuesta.errores
                        : {};

                    error.estado = xhr.status;
                    error.cancelado = estado === "abort";

                    reject(error);
                }
            });
        });

        /* Cancelar consultas pendientes */
        promesa.cancelar = function () {
            if (esConsulta && peticion && peticion.readyState !== 4) {
                peticion.abort();
            }
        };

        return promesa;
    }


    /* ================================================= */
    /* MENSAJES GENERALES */
    /* ================================================= */

    function mostrarMensajeGeneral(texto, esError = false, reintentar = false, tipoAviso = null) {
        if (!redirigiendo && window.Alertas?.disponible()) {
            window.Alertas.notificar(texto, tipoAviso || (esError ? "error" : "success"));
        }

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
                        mostrarMensajeGeneral("Lista actualizada.");
                    }

                } catch (error) {
                    mostrarMensajeGeneral(error.message, true, true);

                } finally {
                    $boton.prop("disabled", false);
                }
            }).appendTo($mensajeGeneral);
        }
    }


    /* ================================================= */
    /* ACTUALIZAR LA LISTA DE CLIENTES */
    /* ================================================= */

    async function actualizarLista() {
        if (redirigiendo) return false;

        const turno = ++versionLista;

        $tabla.attr("aria-busy", "true");

        try {
            await tablaPreparada;

            if (redirigiendo || turno !== versionLista) return false;

            const respuesta = await solicitarClientes({
                accion: "consultar"
            });

            if (redirigiendo || turno !== versionLista) return false;

            if (
                !Array.isArray(respuesta.clientes) ||
                !respuesta.clientes.every(cliente => $.isPlainObject(cliente))
            ) {
                throw new Error("No se pudo cargar la lista.");
            }

            const filas = respuesta.clientes.map(crearFilaCliente);

            tablaClientes.clear();
            tablaClientes.rows.add(filas);
            tablaClientes.draw(false);

            /* Conservar la página actual */
            const informacion = tablaClientes.page.info();

            if (
                informacion.pages > 0 &&
                informacion.page >= informacion.pages
            ) {
                tablaClientes
                    .page(informacion.pages - 1)
                    .draw("page");
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


    /* ================================================= */
    /* CREAR LOS BOTONES DE ACCIONES */
    /* ================================================= */

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


    /* ================================================= */
    /* CREAR LAS FILAS DE LA TABLA */
    /* ================================================= */

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
            $("<td>")
                .text(cliente[campo] ?? (campo === "visitas" ? 0 : ""))
                .appendTo($fila);
        });

        const $acciones = $("<div>", {
            class: "btn-group-actions"
        });

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

        $("<td>", {
            class: "columna-acciones"
        }).append($acciones).appendTo($fila);

        return $fila[0];
    }


    /* ================================================= */
    /* MENSAJES Y ERRORES DE LOS FORMULARIOS */
    /* ================================================= */

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
        if (Validaciones) {
            Validaciones.limpiarFormulario($formulario[0]);
        }

        $formulario.find("[data-error-clientes]")
            .removeClass("is-invalid")
            .removeAttr("aria-invalid data-error-clientes");

        $formulario.find(".clientes-error-servidor").remove();

        mensajeFormulario($formulario)
            .empty()
            .prop("hidden", true);
    }

    function mostrarErrores($formulario, error) {
        window.Alertas?.rechazo(error.message, error, $formulario);

        mensajeFormulario($formulario)
            .text(error.message)
            .prop("hidden", false);

        /* Mostrar errores por campo */
        if (Validaciones && (
            $formulario.is($formularioNuevo) ||
            $formulario.is($formularioEditar)
        )) {
            const primero = Validaciones.mostrarErrores(
                $formulario[0],
                error.errores || {}
            );

            $formulario.find(".modal-body").scrollTop(0);

            return primero ? $(primero) : null;
        }

        let primerCampo = null;

        Object.entries(error.errores || {}).forEach(function ([nombre, texto]) {
            const $campo = $formulario.find("[name]")
                .filter(function () {
                    return $(this).attr("name") === nombre;
                })
                .first();

            if (!$campo.length || $campo.attr("type") === "hidden") {
                return;
            }

            $campo.addClass("is-invalid").attr({
                "aria-invalid": "true",
                "data-error-clientes": "true"
            });

            $("<div>", {
                class: "invalid-feedback clientes-error-servidor"
            }).text(texto).insertAfter($campo);

            if (!primerCampo) primerCampo = $campo;
        });

        $formulario.find(".modal-body").scrollTop(0);

        return primerCampo;
    }


    /* ================================================= */
    /* AUTOCOMPLETAR DATOS PERSONALES */
    /* ================================================= */

    const $modalNuevoCliente = $("#modalNuevoCliente");
    const $formularioNuevo = $modalNuevoCliente.find("form");
    const $formularioEditar = $("#modalEditarCliente form");

    /* Activar las validaciones compartidas */
    if (Validaciones) {
        Validaciones.activar($formularioNuevo[0]);
        Validaciones.activar($formularioEditar[0]);
    }

    const $cedulaNuevo = $("#nuevoCedulaCliente");
    const $mensajePersona = $("#mensajePersonaNuevoCliente");

    const camposPersona = [
        "nombre",
        "apellido",
        "telefono",
        "correo",
        "direccion",
        "ciudad"
    ];

    let solicitudPersona = null;
    let versionPersona = 0;
    let cedulaComprobada = "";
    let cedulaRecuperada = "";
    let personaYaEsCliente = false;
    let buscandoPersona = false;

    /* Muestra los mensajes de búsqueda de personas. */
    function mostrarMensajePersona(texto, tipo = "normal") {
        $mensajePersona
            .removeClass("text-success text-danger text-muted estado-correcto estado-error")
            .addClass(tipo === "correcto" ? "estado-correcto" : tipo === "error" ? "estado-error" : "")
            .css("color", "")
            .text(texto)
            .prop("hidden", !texto);
    }

    function reiniciarBusquedaPersona() {
        ++versionPersona;

        if (solicitudPersona) solicitudPersona.cancelar();

        solicitudPersona = null;
        cedulaComprobada = "";
        cedulaRecuperada = "";
        personaYaEsCliente = false;
        buscandoPersona = false;

        mostrarMensajePersona("");
    }

    async function buscarPersonaPorCedula() {
        const cedula = String($cedulaNuevo.val() || "").trim();
        const turno = ++versionPersona;

        if (solicitudPersona) solicitudPersona.cancelar();

        solicitudPersona = null;
        cedulaComprobada = "";
        personaYaEsCliente = false;
        buscandoPersona = false;
        mostrarMensajePersona("");

        if (!cedula) return;

        /* Evitar duplicar el mensaje de validación */
        if (!Regex.cedula.test(cedula)) return;

        const valoresAnteriores = {};

        camposPersona.forEach(function (campo) {
            valoresAnteriores[campo] = $formularioNuevo
                .find('[name="' + campo + '"]')
                .val();
        });

        buscandoPersona = true;
        mostrarMensajePersona("Buscando persona...");

        try {
            solicitudPersona = solicitarClientes({
                accion: "consultar_persona",
                cedula: cedula
            });

            const respuesta = await solicitudPersona;

            if (
                turno !== versionPersona ||
                String($cedulaNuevo.val() || "").trim() !== cedula ||
                !$modalNuevoCliente.hasClass("show") ||
                redirigiendo
            ) return;

            if (
                typeof respuesta.encontrada !== "boolean" ||
                typeof respuesta.ya_es_cliente !== "boolean"
            ) {
                throw new Error("No se pudo consultar la cédula.");
            }

            if (respuesta.encontrada) {
                if (
                    !$.isPlainObject(respuesta.persona) ||
                    String(respuesta.persona.cedula) !== cedula
                ) {
                    throw new Error("La cédula consultada no coincide.");
                }

                /* Conservar los datos editados durante la búsqueda */
                const camposModificados = camposPersona.some(function (campo) {
                    return $formularioNuevo
                        .find('[name="' + campo + '"]')
                        .val() !== valoresAnteriores[campo];
                });

                if (camposModificados) {
                    mostrarMensajePersona(
                        "Datos modificados. Repite la búsqueda.",
                        "error"
                    );

                    return;
                }

                camposPersona.forEach(function (campo) {
                    $formularioNuevo
                        .find('[name="' + campo + '"]')
                        .val(respuesta.persona[campo] ?? "");
                });

                cedulaRecuperada = cedula;
            }

            cedulaComprobada = cedula;
            personaYaEsCliente = respuesta.ya_es_cliente;

            if (personaYaEsCliente) {
                mostrarMensajePersona(
                    "Cliente ya registrado.",
                    "error"
                );

            } else if (respuesta.encontrada) {
                mostrarMensajePersona(
                    "Datos recuperados: persona encontrada.",
                    "correcto"
                );

            } else {
                mostrarMensajePersona("Persona no registrada.", "correcto");
            }

        } catch (error) {
            if (
                turno !== versionPersona ||
                error.cancelado ||
                redirigiendo
            ) return;

            mostrarMensajePersona(
                error.message || "No se pudo consultar la cédula.",
                "error"
            );

        } finally {
            if (turno === versionPersona) {
                solicitudPersona = null;
                buscandoPersona = false;
            }
        }
    }

    $cedulaNuevo.on("input", function () {
        const cedula = String($(this).val() || "").trim();

        if (cedulaRecuperada && cedulaRecuperada !== cedula) {
            camposPersona.forEach(function (campo) {
                $formularioNuevo
                    .find('[name="' + campo + '"]')
                    .val("");
            });
        }

        reiniciarBusquedaPersona();
    });

    $cedulaNuevo.on("blur", function () {
        const cedula = String($(this).val() || "").trim();

        if (cedula && cedula !== cedulaComprobada) {
            buscarPersonaPorCedula();
        }
    });


    /* ================================================= */
    /* CONTENIDO Y LIMPIEZA DE LOS MODALES */
    /* ================================================= */

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

        if ($modal.is($modalNuevoCliente)) {
            reiniciarBusquedaPersona();
        }

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
                $formulario
                    .find('[name="' + campo + '"]')
                    .val(datoBoton($boton, campo));
            });
        }

        if ($modal.attr("id") === "modalDesactivarCliente") {
            $("#desactivarCedula").val(
                datoBoton($boton, "cedula")
            );

            $("#desactivarNombreCliente").text([
                datoBoton($boton, "nombre"),
                datoBoton($boton, "apellido")
            ].filter(Boolean).join(" "));
        }
    });

    /* Mantener el modal abierto durante el envío */
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

        if ($(this).is($modalNuevoCliente)) {
            reiniciarBusquedaPersona();
        }
    });


    /* ================================================= */
    /* LLENAR EL MODAL DE DETALLES */
    /* ================================================= */

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
            $("#" + id).text(
                datoBoton($boton, campo) || "No registrado"
            );
        });

        $("#detalleVisitas").text(
            datoBoton($boton, "visitas", "0")
        );

        $("#detalleEstado").text(
            datoBoton($boton, "estado", "ACTIVO")
        );
    });


    /* ================================================= */
    /* HISTORIAL DE SERVICIOS */
    /* ================================================= */

    function formatearFechaHistorial(fecha) {
        if (
            typeof fecha !== "string" ||
            !/^\d{4}-\d{2}-\d{2}$/.test(fecha)
        ) {
            return "—";
        }

        const [anio, mes, dia] = fecha.split("-");
        const comprobacion = new Date(fecha + "T00:00:00Z");

        if (
            Number.isNaN(comprobacion.getTime()) ||
            comprobacion.toISOString().slice(0, 10) !== fecha
        ) {
            return "—";
        }

        return dia + "/" + mes + "/" + anio;
    }

    function formatearMontoHistorial(monto) {
        if (
            !["string", "number"].includes(typeof monto) ||
            (typeof monto === "string" && monto.trim() === "")
        ) {
            return "No registrado";
        }

        const valor = Number(monto);

        if (!Number.isFinite(valor) || valor < 0) {
            return "No registrado";
        }

        return valor.toLocaleString("es-VE", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function mostrarEstadoHistorial(
        texto,
        esError = false,
        reintentar = false
    ) {
        const $celda = $("<td>", {
            colspan: 5,
            class: "text-center py-4",
            role: esError ? "alert" : "status"
        });

        $("<p>", {
            class: "mb-0",
            text: texto
        }).appendTo($celda);

        if (reintentar) {
            $("<button>", {
                type: "button",
                class: "btn-templo-secondary mt-3",
                text: "Volver a consultar"
            }).on("click", function () {
                if (
                    historialAbierto &&
                    clienteHistorial &&
                    !redirigiendo
                ) {
                    cargarHistorial();
                }
            }).appendTo($celda);
        }

        $cuerpoHistorial
            .empty()
            .append($("<tr>").append($celda));
    }

    function crearFilaHistorial(servicio) {
        const $fila = $("<tr>");

        const valores = [
            formatearFechaHistorial(servicio.fecha),
            servicio.nombre_servicio || "Servicio no disponible",
            servicio.especialista || "No registrado",
            formatearMontoHistorial(servicio.precio_aplicado),
            servicio.nota_tecnica || "Sin nota técnica"
        ];

        valores.forEach(function (valor, indice) {
            const $celda = $("<td>").text(valor);

            if (indice === 3) {
                $celda.addClass("text-nowrap");
            }

            if (indice === 4) {
                $celda
                    .addClass("text-break")
                    .css("white-space", "pre-wrap");
            }

            $fila.append($celda);
        });

        return $fila[0];
    }

    async function cargarHistorial() {
        if (
            !historialAbierto ||
            !clienteHistorial ||
            redirigiendo
        ) return;

        const turno = ++versionHistorial;
        const cedula = clienteHistorial.cedula;

        /* Cancelar consultas anteriores */
        if (solicitudHistorial) {
            solicitudHistorial.cancelar();
        }

        $tablaHistorial.attr("aria-busy", "true");

        mostrarEstadoHistorial("Consultando historial...");

        try {
            if (!cedula) {
                throw new Error(
                    "Cliente no identificado. Actualiza la lista."
                );
            }

            solicitudHistorial = solicitarClientes({
                accion: "historial",
                cedula: cedula
            });

            const respuesta = await solicitudHistorial;

            if (
                turno !== versionHistorial ||
                !historialAbierto ||
                redirigiendo
            ) return;

            if (
                !$.isPlainObject(respuesta.cliente) ||
                respuesta.cliente.cedula !== cedula ||
                !Array.isArray(respuesta.historial) ||
                !respuesta.historial.every(function (servicio) {
                    return $.isPlainObject(servicio) &&
                        typeof servicio.fecha === "string" &&
                        typeof servicio.nombre_servicio === "string" &&
                        (
                            servicio.especialista === null ||
                            typeof servicio.especialista === "string"
                        ) &&
                        (
                            servicio.nota_tecnica === null ||
                            typeof servicio.nota_tecnica === "string"
                        ) &&
                        Object.prototype.hasOwnProperty.call(
                            servicio,
                            "precio_aplicado"
                        );
                })
            ) {
                throw new Error("No se pudo cargar el historial.");
            }

            const nombre = [
                respuesta.cliente.nombre,
                respuesta.cliente.apellido
            ].filter(Boolean).join(" ");

            $("#historialNombreCliente").text(
                nombre || "Cliente seleccionado"
            );

            $modalHistorial.attr(
                "data-id-cliente",
                respuesta.cliente.id_cliente
            );

            /* Mostrar los servicios recuperados */
            if (respuesta.historial.length) {
                const filas = respuesta.historial.map(crearFilaHistorial);

                $cuerpoHistorial.empty().append(filas);

            } else {
                const $vacio = $historialVacio.clone();

                $vacio.find("td > span").last().text(
                    "Sin servicios registrados."
                );

                $cuerpoHistorial.empty().append($vacio);
            }

        } catch (error) {
            if (
                turno !== versionHistorial ||
                !historialAbierto ||
                redirigiendo ||
                error.cancelado
            ) return;

            mostrarEstadoHistorial(error.message, true, true);
            window.Alertas?.notificar(error.message, "error");

        } finally {
            if (turno === versionHistorial) {
                solicitudHistorial = null;
                $tablaHistorial.attr("aria-busy", "false");
            }
        }
    }

    $modalHistorial.on("show.bs.modal", function (evento) {
        if (operacionEnCurso || redirigiendo) {
            evento.preventDefault();
            return;
        }

        const $boton = $(evento.relatedTarget);

        clienteHistorial = {
            cedula: datoBoton($boton, "cedula").trim()
        };

        historialAbierto = true;

        $("#historialNombreCliente").text([
            datoBoton($boton, "nombre"),
            datoBoton($boton, "apellido")
        ].filter(Boolean).join(" ") || "Cliente seleccionado");

        $modalHistorial.attr(
            "data-id-cliente",
            datoBoton($boton, "id-cliente")
        );

        cargarHistorial();
    });

    $modalHistorial.on("hide.bs.modal", function () {
        historialAbierto = false;
        ++versionHistorial;

        /* Cancelar la consulta al cerrar el modal */
        if (solicitudHistorial) {
            solicitudHistorial.cancelar();
        }

        solicitudHistorial = null;
        clienteHistorial = null;

        $tablaHistorial.attr("aria-busy", "false");
    });

    $modalHistorial.on("hidden.bs.modal", function () {
        $cuerpoHistorial.empty().append($historialVacio.clone());

        $modalHistorial.removeAttr("data-id-cliente");

        $("#historialNombreCliente").text("Cliente seleccionado");
    });


    /* ================================================= */
    /* REGISTRAR, MODIFICAR Y DESACTIVAR */
    /* ================================================= */

    $modales.find("form").on("submit", async function (evento) {
        evento.preventDefault();

        if (operacionEnCurso || redirigiendo) return;

        const $formulario = $(this);

        limpiarErrores($formulario);

        /* Validar antes del envío AJAX */
        if (Validaciones && (
            $formulario.is($formularioNuevo) ||
            $formulario.is($formularioEditar)
        )) {
            const resultado = Validaciones.validarFormulario(this);

            if (!resultado.valido) {
                mensajeFormulario($formulario)
                    .text("Revisa los campos.")
                    .prop("hidden", false);

                if (resultado.primerCampo) {
                    resultado.primerCampo.focus();
                }

                return;
            }

        } else if (!this.reportValidity()) {
            return;
        }

        if ($formulario.is($formularioNuevo)) {
            const cedula = String($cedulaNuevo.val() || "").trim();

            if (buscandoPersona) {
                mostrarErrores(
                    $formulario,
                    new Error("Búsqueda en curso.")
                );

                return;
            }

            if (
                personaYaEsCliente &&
                cedula === cedulaComprobada
            ) {
                mostrarErrores(
                    $formulario,
                    new Error("Cliente ya registrado.")
                );

                return;
            }

            if (cedula !== cedulaComprobada) {
                mostrarErrores(
                    $formulario,
                    new Error("Verifica la cédula.")
                );

                buscarPersonaPorCedula();
                return;
            }
        }

        const datos = {};

        $formulario.serializeArray().forEach(function (campo) {
            datos[campo.name] = campo.value;
        });

        const controles = [];

        /* Conservar el estado de los controles */
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

        $formulario
            .data("enviando", true)
            .attr("aria-busy", "true");

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

            mensajeExito = typeof respuesta.mensaje === "string"
                ? respuesta.mensaje
                : mensajeExito;

            /* Cerrar el modal después de confirmar el guardado */
            $formulario.data("enviando", false);

            const $modal = $formulario.closest(".modal");

            const cerrarModal = function () {
                window.bootstrap.Modal
                    .getOrCreateInstance($modal[0])
                    .hide();
            };

            /* Esperar el cierre de la animación */
            if ($modal.hasClass("show")) $modal.one("shown.bs.modal", cerrarModal);

            if ($modal.hasClass("show")) $modal.one("hidden.bs.modal", function () {
                $modal.off("shown.bs.modal", cerrarModal);
            });

            cerrarModal();

            try {
                await actualizarLista();
                mostrarMensajeGeneral(mensajeExito);

            } catch (error) {
                mostrarMensajeGeneral(
                    "Operación guardada. No se actualizó la lista.",
                    true,
                    true,
                    "warning"
                );
            }

        } catch (error) {
            if (respuestaConfirmada) {
                mostrarMensajeGeneral(
                    "Operación guardada. No se actualizó la vista.",
                    true,
                    true,
                    "warning"
                );

            } else if (!redirigiendo) {
                primerCampo = mostrarErrores($formulario, error);
            }

        } finally {
            controles.forEach(function (control) {
                control.elemento.prop(
                    "disabled",
                    control.deshabilitado
                );
            });

            $enviar.html(contenidoBoton);

            $formulario
                .data("enviando", false)
                .attr("aria-busy", "false");

            operacionEnCurso = false;

            if (primerCampo && !redirigiendo) {
                primerCampo.trigger("focus");
            }
        }
    });


    /* ================================================= */
    /* CONSULTA INICIAL */
    /* ================================================= */

    actualizarLista().catch(function (error) {
        mostrarMensajeGeneral(error.message, true, true);
    });
});