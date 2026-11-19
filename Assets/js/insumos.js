/* ===================================================== */
/* GESTIÓN DE INSUMOS */
/* ===================================================== */

import { Validaciones } from "./validaciones/validaciones.js";

$(function () {
    "use strict";

    const urlInsumos = "Index.php?url=insumos";
    const $tabla = $("#tablaInsumos");
    const $modulo = $(".insumos-module");
    const $modales = $("#modalNuevoInsumo, #modalEditarInsumo, #modalGestionarExistencias, #modalDesactivarInsumo");

    if (!$tabla.length) return;

    let operacionEnCurso = false;
    let redirigiendo = false;
    let versionLista = 0;

    /* Reutiliza los iconos originales, incluso si no hay insumos */
    const $iconoDetalle = $("#modalDetalleInsumoLabel svg").first().clone();

    const $mensajeGeneral = $("<div>", {
        class: "alert alert-light border mb-3",
        role: "status",
        "aria-live": "polite",
        "aria-atomic": "true",
        hidden: true
    }).insertAfter($modulo.find(".module-header").first());


    /* ===================================================== */
    /* DATATABLE DE INSUMOS */
    /* ===================================================== */

    /* Esperar a que DataTables termine de cargar el idioma */
    let habilitarTabla;

    const tablaPreparada = new Promise(function (resolve) {
        habilitarTabla = resolve;
    });

    const tablaExistente = $.fn.DataTable.isDataTable("#tablaInsumos");

    const tablaInsumos = tablaExistente
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


    /* ===================================================== */
    /* SOLICITUDES ASÍNCRONAS */
    /* ===================================================== */

    function solicitarInsumos(datos) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: urlInsumos,
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
    /* HISTORIAL DE MOVIMIENTOS */
    /* ===================================================== */

    const $tablaHistorial = $("#tablaHistorialInsumos");
    const $mensajeHistorial = $("#mensajeHistorialInsumos");
    const $filtrosHistorial = $("#filtrosHistorialInsumos");

    let tablaHistorial = null;
    let historialPreparado = null;
    let versionHistorial = 0;


    /* Situar los filtros entre Mostrar registros y Buscar, dentro de DataTables */
    function ubicarFiltrosHistorial() {
        const $espacio = $tablaHistorial
            .closest(".dataTables_wrapper")
            .find(".insumos-historial-filtros-espacio")
            .first();

        if ($espacio.length) {
            $filtrosHistorial.appendTo($espacio);
        }
    }


    /* Preparar la tabla cuando se abra la pestaña por primera vez */
    function prepararTablaHistorial() {
        if (tablaHistorial) return;

        const existeTabla = $.fn.DataTable.isDataTable("#tablaHistorialInsumos");

        historialPreparado = new Promise(function (resolve) {
            tablaHistorial = existeTabla
                ? $tablaHistorial.DataTable()
                : $tablaHistorial.DataTable({
                    language: {
                        url: "Assets/libs/datatables/i18n/es-ES.json"
                    },
                    pageLength: 5,
                    lengthMenu: [5],
                    responsive: false,
                    autoWidth: false,
                    order: [[0, "desc"]],
                    dom: '<"insumos-historial-barra"l<"insumos-historial-filtros-espacio">f>t<"insumos-historial-pie"ip>',
                    initComplete: function () {
                        ubicarFiltrosHistorial();
                        resolve();
                    }
                });

            if (existeTabla) {
                ubicarFiltrosHistorial();
                resolve();
            }
        });
    }


    /* Construir cada fila con los datos consultados en la base de datos */
    function crearFilaHistorial(movimiento) {
        const $fila = $("<tr>");
        const fecha = String(movimiento.fecha_movimiento ?? "");
        const coincidencia = fecha.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);

        const fechaTexto = coincidencia
            ? coincidencia[3] + "/" + coincidencia[2] + "/" + coincidencia[1] +
                " " + coincidencia[4] + ":" + coincidencia[5]
            : fecha;

        const incremento = movimiento.tipo_movimiento === "INCREMENTO";
        const esRegistroInicial = incremento && ["Registro inicial", "Ingreso inicial"].includes(movimiento.motivo);
        const cantidad = Number(movimiento.cantidad ?? 0);

        $("<td>").attr("data-order", fecha).text(fechaTexto).appendTo($fila);
        $("<td>").text(movimiento.nombre_insumo ?? "").appendTo($fila);

        $("<td>").append(
            $("<span>", {
                class: "badge " + (incremento ? "text-bg-success" : "text-bg-danger"),
                text: esRegistroInicial ? "Registro inicial" : (incremento ? "Reposición" : "Descuento")
            })
        ).appendTo($fila);

        $("<td>")
            .attr("data-order", incremento ? cantidad : -cantidad)
            .text((incremento ? "+" : "−") + cantidad)
            .appendTo($fila);

        $("<td>").text(movimiento.stock_anterior ?? 0).appendTo($fila);
        $("<td>").text(movimiento.stock_resultante ?? 0).appendTo($fila);
        $("<td>").text(movimiento.motivo ?? "").appendTo($fila);

        $("<td>").text(
            movimiento.tipo_movimiento === "DECREMENTO"
                ? "No aplica"
                : (movimiento.nombre_proveedor || "Sin proveedor registrado")
        ).appendTo($fila);

        $("<td>").text(movimiento.usuario_responsable ?? "").appendTo($fila);

        return $fila[0];
    }


    /* Mostrar errores sin perder los movimientos consultados anteriormente */
    function mostrarErrorHistorial(error) {
        if (redirigiendo) return;

        window.Alertas?.rechazo(error.message, error, $filtrosHistorial);
        Validaciones.mostrarErrores($filtrosHistorial[0], error.errores);

        const errores = $.isPlainObject(error.errores)
            ? Object.values(error.errores)
            : [];

        $mensajeHistorial
            .text(errores[0] || error.message || "No se pudo consultar el historial de movimientos.")
            .prop("hidden", false);
    }


    /* Consultar los movimientos de las fechas y del tipo seleccionados */
    async function actualizarHistorial() {
        if (redirigiendo) return;

        const turno = ++versionHistorial;

        Validaciones.limpiarFormulario($filtrosHistorial[0]);

        const validacion = Validaciones.validarFormulario($filtrosHistorial[0], reglasHistorial);
        const desde = $("#historialFechaDesde").val();
        const hasta = $("#historialFechaHasta").val();

        $filtrosHistorial.find("button").prop("disabled", false);
        $tablaHistorial.attr("aria-busy", "false");

        if (!validacion.valido) {
            $mensajeHistorial.text("Revise los filtros del historial.").prop("hidden", false);
            validacion.primerCampo?.focus();
            return;
        }

        if (desde && hasta && desde > hasta) {
            Validaciones.mostrarErrores($filtrosHistorial[0], {
                fecha_hasta: "La fecha final no puede ser anterior a la fecha inicial."
            });

            $mensajeHistorial.text("Revise el intervalo de fechas.").prop("hidden", false);
            $("#historialFechaHasta").trigger("focus");

            return;
        }

        const $botones = $filtrosHistorial.find("button");

        $botones.prop("disabled", true);
        $mensajeHistorial.prop("hidden", true);
        $tablaHistorial.attr("aria-busy", "true");

        try {
            prepararTablaHistorial();
            await historialPreparado;

            const respuesta = await solicitarInsumos({
                accion: "consultar_historial",
                fecha_desde: $("#historialFechaDesde").val(),
                fecha_hasta: $("#historialFechaHasta").val(),
                tipo_movimiento: $("#historialTipoMovimiento").val()
            });

            if (turno !== versionHistorial) return;

            if (
                !Array.isArray(respuesta.movimientos) ||
                !respuesta.movimientos.every(movimiento => $.isPlainObject(movimiento))
            ) {
                throw new Error("Los movimientos recibidos no tienen un formato válido.");
            }

            tablaHistorial.clear();
            tablaHistorial.rows.add(respuesta.movimientos.map(crearFilaHistorial));
            tablaHistorial.draw();
            tablaHistorial.columns.adjust();

        } catch (error) {
            if (turno === versionHistorial) throw error;

        } finally {
            if (turno === versionHistorial) {
                $botones.prop("disabled", false);
                $tablaHistorial.attr("aria-busy", "false");
            }
        }
    }


    /* Consultar el historial al abrir su pestaña */
    $("#historial-insumos-tab").on("shown.bs.tab", function () {
        actualizarHistorial().catch(mostrarErrorHistorial);
    });


    /* Aplicar los filtros seleccionados */
    $filtrosHistorial.on("submit", function (evento) {
        evento.preventDefault();
        actualizarHistorial().catch(mostrarErrorHistorial);
    });


    /* Restablecer los filtros sin modificar el historial */
    $("#limpiarHistorialInsumos").on("click", function () {
        $filtrosHistorial[0].reset();
        actualizarHistorial().catch(mostrarErrorHistorial);
    });


    /* ===================================================== */
    /* MENSAJES GENERALES */
    /* ===================================================== */

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
                        mostrarMensajeGeneral("Lista de insumos actualizada.");
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
    /* ACTUALIZAR LISTA DE INSUMOS */
    /* ===================================================== */

    async function actualizarLista() {
        if (redirigiendo) return false;

        const turno = ++versionLista;

        $tabla.attr("aria-busy", "true");

        try {
            await tablaPreparada;

            if (turno !== versionLista) return false;

            const respuesta = await solicitarInsumos({ accion: "consultar" });

            if (turno !== versionLista) return false;

            if (
                !Array.isArray(respuesta.insumos) ||
                !respuesta.insumos.every(insumo => $.isPlainObject(insumo))
            ) {
                throw new Error("La lista recibida no tiene un formato válido.");
            }

            const filas = respuesta.insumos.map(crearFilaInsumo);

            tablaInsumos.clear();
            tablaInsumos.rows.add(filas);
            tablaInsumos.draw(false);

            /* Conserva búsqueda, orden y página; corrige una última página vacía */
            const informacion = tablaInsumos.page.info();

            if (informacion.pages > 0 && informacion.page >= informacion.pages) {
                tablaInsumos.page(informacion.pages - 1).draw("page");
            }

            return true;

        } catch (error) {
            if (turno === versionLista) throw error;

            return false;

        } finally {
            if (turno === versionLista) $tabla.attr("aria-busy", "false");
        }
    }


    /* ===================================================== */
    /* ESTADOS DEL INVENTARIO */
    /* ===================================================== */

    function claseEstadoStock(estado) {
        if (estado === "AGOTADO") return "text-bg-danger";
        if (estado === "BAJO") return "text-bg-warning";

        return "text-bg-success";
    }


    function claseEstadoVencimiento(estado) {
        if (estado === "VENCIDO" || estado === "URGENTE") return "text-bg-danger";
        if (estado === "PROXIMO") return "text-bg-warning";
        if (estado === "VIGENTE") return "text-bg-success";

        return "text-bg-secondary";
    }


    /* ===================================================== */
    /* CREAR BOTONES DE ACCIONES */
    /* ===================================================== */

    function crearBotonInsumo(insumo, clase, modal, titulo, $icono) {
        const $boton = $("<button>", {
            type: "button",
            class: "btn-action " + clase,
            "data-bs-toggle": "modal",
            "data-bs-target": modal,
            title: titulo,
            "aria-label": titulo
        });

        const fechaVencimiento = String(insumo.fecha_vencimiento ?? "").trim();

        const atributos = {
            "id-insumo": insumo.id_insumo,
            "nombre-insumo": insumo.nombre_insumo,
            descripcion: insumo.descripcion,
            presentacion: insumo.presentacion,
            "tipo-control": insumo.tipo_control ?? "UNITARIO",
            "stock-actual": insumo.stock_actual ?? 0,
            "stock-minimo": insumo.stock_minimo ?? 0,
            "fecha-vencimiento": fechaVencimiento,
            "fecha-vencimiento-texto": fechaVencimiento || "No aplica",
            "estado-stock": insumo.estado_stock ?? "NORMAL",
            "etiqueta-stock": insumo.etiqueta_stock ?? "Normal",
            "estado-vencimiento": insumo.estado_vencimiento ?? "SIN_FECHA",
            "etiqueta-vencimiento": insumo.etiqueta_vencimiento ?? "Sin fecha",
            "estado-insumo": insumo.estado_insumo ?? "ACTIVO"
        };

        Object.entries(atributos).forEach(function ([campo, valor]) {
            $boton.attr("data-" + campo, valor ?? "");
        });

        return $boton.append($icono);
    }


    /* ===================================================== */
    /* CREAR FILAS DE LA TABLA */
    /* ===================================================== */

    function crearFilaInsumo(insumo) {
        const $fila = $("<tr>");
        const tipoControl = String(insumo.tipo_control ?? "UNITARIO").trim().toUpperCase();
        const estado = String(insumo.estado_insumo ?? "ACTIVO").trim().toUpperCase();
        const fechaVencimiento = String(insumo.fecha_vencimiento ?? "").trim();
        const estadoVencimiento = String(insumo.estado_vencimiento ?? "SIN_FECHA").trim().toUpperCase();

        [
            insumo.nombre_insumo ?? "",
            insumo.presentacion ?? "",
            tipoControl === "POR_ENVASE" ? "Por envase" : "Consumo unitario"
        ].forEach(function (valor) {
            $("<td>").text(valor).appendTo($fila);
        });


        /* ================================================= */
        /* STOCK ACTUAL: CANTIDAD CON COLOR DISCRETO */
        /* ================================================= */

        /* Calcular el estado desde las cantidades para mantenerlo actualizado con AJAX */
        const stockActual = Number(insumo.stock_actual ?? 0);
        const stockMinimo = Number(insumo.stock_minimo ?? 0);

        let claseNumeroStock = "insumos-stock-disponible";
        let etiquetaStock = "Disponible";

        if (stockActual === 0) {
            claseNumeroStock = "insumos-stock-agotado";
            etiquetaStock = "Agotado";

        } else if (stockActual <= stockMinimo) {
            claseNumeroStock = "insumos-stock-bajo";
            etiquetaStock = "Stock bajo";
        }

        /* La columna muestra solo el número; el estado se informa con un título accesible */
        const $celdaStock = $("<td>").attr("data-order", stockActual);

        $("<span>", {
            class: "insumos-stock-numero " + claseNumeroStock,
            title: etiquetaStock,
            text: stockActual
        }).appendTo($celdaStock);

        $("<span>", {
            class: "visually-hidden",
            text: etiquetaStock + ": " + stockActual + " unidades."
        }).appendTo($celdaStock);

        $celdaStock.appendTo($fila);

        const $vencimiento = $("<td>")
            .attr("data-order", fechaVencimiento)
            .text(fechaVencimiento || "No aplica");

        if (estadoVencimiento !== "SIN_FECHA" && estadoVencimiento !== "VIGENTE") {
            $("<div>", { class: "mt-1" })
                .append($("<span>", {
                    class: "badge " + claseEstadoVencimiento(estadoVencimiento) + " insumos-alerta-vencimiento",
                    text: insumo.etiqueta_vencimiento ?? "Sin fecha"
                }))
                .appendTo($vencimiento);
        }

        $vencimiento.appendTo($fila);

        const $estado = $("<span>", {
            class: "badge " + (
                estado === "INACTIVO"
                    ? "estado-registro-inactivo"
                    : "estado-registro-activo"
            ),
            text: estado
        });

        $("<td>").append($estado).appendTo($fila);

        const $acciones = $("<div>", { class: "btn-group-actions" });

        const $detalle = $("<span>", {
            class: "cliente-detalle-icono",
            "aria-hidden": "true"
        }).append($iconoDetalle.clone());

        $acciones.append(
            crearBotonInsumo(
                insumo,
                "btn-detail btnDetalleInsumo",
                "#modalDetalleInsumo",
                "Ver detalles del insumo",
                $detalle
            ),

            crearBotonInsumo(
                insumo,
                "btn-edit btnEditarInsumo",
                "#modalEditarInsumo",
                "Editar insumo",
                $("<i>", {
                    class: "bi bi-pencil-fill",
                    "aria-hidden": "true"
                })
            ),

            crearBotonInsumo(
                insumo,
                "btn-reponer btnGestionarExistencias",
                "#modalGestionarExistencias",
                "Gestionar existencias",
                $("<i>", {
                    class: "bi bi-box-seam",
                    "aria-hidden": "true"
                })
            ),

            crearBotonInsumo(
                insumo,
                "btn-delete btnDesactivarInsumo",
                "#modalDesactivarInsumo",
                "Desactivar insumo",
                $("<i>", {
                    class: "bi bi-trash-fill",
                    "aria-hidden": "true"
                })
            )
        );

        $("<td>").append($acciones).appendTo($fila);

        return $fila[0];
    }


    /* ===================================================== */
    /* MENSAJES Y ERRORES DE LOS FORMULARIOS */
    /* ===================================================== */

    function mensajeFormulario($formulario) {
        let $mensaje = $formulario.find(".insumos-mensaje-formulario");

        if (!$mensaje.length) {
            $mensaje = $("<div>", {
                class: "estado-error insumos-mensaje-formulario mb-3",
                role: "alert",
                hidden: true
            }).prependTo($formulario.find(".modal-body").first());
        }

        return $mensaje;
    }


    function limpiarErrores($formulario) {
        Validaciones.limpiarFormulario($formulario[0]);
        mensajeFormulario($formulario).empty().prop("hidden", true);
    }


    function mostrarErrores($formulario, error) {
        window.Alertas?.rechazo(error.message, error, $formulario);

        mensajeFormulario($formulario).text(error.message).prop("hidden", false);

        const primero = Validaciones.mostrarErrores($formulario[0], error.errores);

        $formulario.find(".modal-body").scrollTop(0);

        return primero ? $(primero) : null;
    }


    /* ===================================================== */
    /* VALIDACIONES DE FORMULARIOS Y FILTROS */
    /* ===================================================== */

    const reglaIdentificador = {
        comprobar: valor => Validaciones.validarEntero(valor, 1)
            ? null : "No se pudo identificar el registro seleccionado."
    };

    const reglaCantidad = {
        comprobar: valor => Validaciones.validarEntero(valor)
            ? null : "Ingrese una cantidad entera entre 0 y 4294967295."
    };

    const reglaFechaOpcional = {
        comprobar: valor => valor === "" || Validaciones.validarFecha(valor)
            ? null : "Seleccione una fecha válida."
    };

    const reglasEditar = Object.freeze({
        ...Validaciones.reglasInsumos,
        id_insumo: reglaIdentificador,
        stock_actual: reglaCantidad,
        stock_original: reglaCantidad
    });

    const reglasMovimiento = Object.freeze({
        id_insumo: reglaIdentificador,

        tipo_movimiento: {
            comprobar: valor => ["AGREGAR", "REPONER", "DESCONTAR"].includes(valor)
                ? null : "Seleccione una operación válida."
        },

        cantidad_movimiento: {
            comprobar: valor => Validaciones.validarEntero(valor, 1)
                ? null : "Ingrese una cantidad entera entre 1 y 4294967295."
        },

        id_proveedor: {
            comprobar: valor => valor === "" || Validaciones.validarEntero(valor, 1)
                ? null : "Seleccione un proveedor válido."
        },

        fecha_vencimiento: {
            comprobar: valor => Validaciones.validarFecha(valor)
                ? null : "Seleccione una fecha válida o marque No aplica."
        },

        id_lote: {
            comprobar: valor => Validaciones.validarEntero(valor, 1)
                ? null : "Seleccione el lote afectado."
        },

        motivo: {
            comprobar: valor => ["Rotura", "Derrame", "Deterioro", "Daño", "Extravío", "Otro"].includes(valor)
                ? null : "Seleccione un motivo para el descuento."
        },

        detalle_motivo: {
            comprobar: valor => valor.trim() && new TextEncoder().encode(valor.trim()).length <= 249
                ? null : "Describa el motivo; reduzca el texto si es demasiado largo."
        }
    });

    const reglasHistorial = Object.freeze({
        fecha_desde: reglaFechaOpcional,
        fecha_hasta: reglaFechaOpcional,

        tipo_movimiento: {
            comprobar: valor => ["", "INCREMENTO", "DECREMENTO"].includes(valor)
                ? null : "Seleccione un tipo de movimiento válido."
        }
    });


    function reglasFormulario($formulario) {
        const accion = $formulario.find('[name="accion"]').val();

        if (accion === "registrar") return Validaciones.reglasInsumos;
        if (accion === "modificar") return reglasEditar;
        if (accion === "gestionar_existencias") return reglasMovimiento;

        return { id_insumo: reglaIdentificador };
    }


    $modales.find("form").each(function () {
        this.noValidate = true;
        Validaciones.activar(this, reglasFormulario($(this)));
    });

    $filtrosHistorial.prop("noValidate", true);

    Validaciones.activar($filtrosHistorial[0], reglasHistorial);


    /* Retirar mensajes de campos que dejan de participar en el movimiento */
    function limpiarCamposDeshabilitados($formulario) {
        $formulario.find(":disabled").each(function () {
            $(this).removeAttr("aria-invalid");
            $(this).next(".templo-validacion-feedback").remove();
        });
    }


    /* ===================================================== */
    /* FECHA DE VENCIMIENTO DEL LOTE */
    /* ===================================================== */

    function sincronizarFechaVencimiento() {
        const $casilla = $("#movimientoFechaNoAplica");
        const $fecha = $("#movimientoFechaVencimiento");
        const esEntrada = $("#movimientoTipoInsumo").val() !== "DESCONTAR";
        const noAplica = $casilla.prop("checked");

        if (noAplica) $fecha.val("");

        $casilla.prop("disabled", !esEntrada);
        $fecha.prop("disabled", !esEntrada || noAplica);

        limpiarCamposDeshabilitados($("#modalGestionarExistencias form"));
    }


    $("#modalGestionarExistencias").on("change", "#movimientoFechaNoAplica", function () {
        sincronizarFechaVencimiento();
    });


    /* ===================================================== */
    /* LOTES DEL INSUMO */
    /* ===================================================== */

    let versionLotesMovimiento = 0;
    let versionLotesDetalle = 0;
    let lotesMovimiento = [];
    let lotesCargados = false;


    /* Presentar las fechas con el mismo formato en todo el módulo */
    function fechaLegible(fecha, incluirHora = false) {
        const texto = String(fecha ?? "").trim();
        const coincidencia = texto.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);

        if (!coincidencia) return texto || "No aplica";

        const fechaTexto = coincidencia[3] + "/" + coincidencia[2] + "/" + coincidencia[1];

        return incluirHora && coincidencia[4]
            ? fechaTexto + " " + coincidencia[4] + ":" + coincidencia[5]
            : fechaTexto;
    }


    /* Comprobar los lotes recibidos antes de mostrarlos */
    async function consultarLotes(idInsumo) {
        const respuesta = await solicitarInsumos({
            accion: "consultar_lotes",
            id_insumo: idInsumo
        });

        if (
            !Array.isArray(respuesta.lotes) ||
            !respuesta.lotes.every(lote => $.isPlainObject(lote))
        ) {
            throw new Error("La información de los lotes no tiene un formato válido.");
        }

        return respuesta.lotes;
    }


    /* Preparar los lotes para identificar qué unidades se descontarán */
    function llenarLotesMovimiento() {
        const $selector = $("#movimientoLoteInsumo").empty();
        const disponibles = lotesMovimiento.filter(lote => Number(lote.cantidad_disponible) > 0);

        $("<option>", {
            value: "",
            text: "Seleccione el lote afectado"
        }).appendTo($selector);

        disponibles.forEach(function (lote) {
            const cantidad = Number(lote.cantidad_disponible);

            const vencimiento = lote.fecha_vencimiento
                ? " · Vence " + fechaLegible(lote.fecha_vencimiento)
                : " · Sin vencimiento";

            $("<option>", {
                value: String(lote.id_lote),
                text: "Lote #" + lote.id_lote + " · " + cantidad + " disponible(s)" + vencimiento
            }).attr("data-disponible", cantidad).appendTo($selector);
        });

        /* Seleccionar automáticamente solo si no hay ambigüedad */
        if (disponibles.length === 1) {
            $selector.val(String(disponibles[0].id_lote));
        }

        $("#movimientoLoteAyuda").text(
            disponibles.length
                ? "El descuento afectará únicamente al lote seleccionado."
                : "Este insumo no tiene lotes con existencias disponibles."
        );
    }


    /* Consultar lotes antes de habilitar el movimiento */
    async function cargarLotesMovimiento(idInsumo) {
        const turno = ++versionLotesMovimiento;
        const $formulario = $("#modalGestionarExistencias form");
        const $selectorOperacion = $("#movimientoTipoInsumo");

        lotesCargados = false;
        lotesMovimiento = [];

        $selectorOperacion.prop("disabled", true);
        $formulario.find('[type="submit"]').prop("disabled", true);
        $("#movimientoDescripcionOperacion").text("Consultando los lotes del insumo...");

        try {
            const lotes = await consultarLotes(idInsumo);

            if (turno !== versionLotesMovimiento) return;

            const stock = Number($("#movimientoStockActual").text());

            const totalLotes = lotes.reduce(
                (total, lote) => total + Number(lote.cantidad_disponible ?? 0), 0
            );

            if (!Number.isSafeInteger(totalLotes) || totalLotes !== stock) {
                throw new Error(
                    "Las existencias no coinciden con los lotes registrados. " +
                    "Es necesario regularizar el inventario antes de realizar movimientos."
                );
            }

            lotesMovimiento = lotes;
            lotesCargados = true;

            llenarLotesMovimiento();

            const hayIngresos = lotes.length > 0;

            /* Agregar solo para la primera entrada; Reponer para las siguientes */
            $selectorOperacion.find('option[value="AGREGAR"]').prop("disabled", hayIngresos);
            $selectorOperacion.find('option[value="REPONER"]').prop("disabled", !hayIngresos);
            $selectorOperacion.val(hayIngresos ? "REPONER" : "AGREGAR");
            $selectorOperacion.prop("disabled", false);

            sincronizarMovimientoExistencias();

        } catch (error) {
            if (turno !== versionLotesMovimiento || redirigiendo) return;

            lotesCargados = false;

            $selectorOperacion.prop("disabled", true);
            $formulario.find('[type="submit"]').prop("disabled", true);

            $("#movimientoDescripcionOperacion").text("No se pudo verificar el inventario.");

            mensajeFormulario($formulario)
                .text(error.message || "No fue posible consultar los lotes.")
                .prop("hidden", false);
        }
    }


    /* ===================================================== */
    /* GESTIONAR EXISTENCIAS */
    /* ===================================================== */

    function sincronizarMovimientoExistencias() {
        const operacion = $("#movimientoTipoInsumo").val();
        const esDescuento = operacion === "DESCONTAR";
        const esOtro = esDescuento && $("#movimientoMotivoInsumo").val() === "Otro";
        const $formulario = $("#modalGestionarExistencias form");
        const $lote = $("#movimientoLoteInsumo");
        const disponibles = lotesMovimiento.filter(lote => Number(lote.cantidad_disponible) > 0);

        /* Entradas: mostrar proveedor y fecha; descuentos: mostrar lote y motivo */
        $("#movimientoProveedorGrupo, #movimientoFechaGrupo").prop("hidden", esDescuento);
        $("#movimientoProveedorInsumo").prop("disabled", esDescuento);

        $("#movimientoLoteGrupo, #movimientoMotivoGrupo").prop("hidden", !esDescuento);

        $lote.prop("disabled", !esDescuento).prop("required", esDescuento && disponibles.length > 1);

        $("#movimientoMotivoInsumo").prop("disabled", !esDescuento).prop("required", esDescuento);

        $("#movimientoOtroMotivoGrupo").prop("hidden", !esOtro);
        $("#movimientoOtroMotivoInsumo").prop("disabled", !esOtro).prop("required", esOtro);

        sincronizarFechaVencimiento();

        const textos = {
            AGREGAR: {
                descripcion: "Primer ingreso de unidades: se creará el primer lote.",
                ayuda: "Se sumarán las unidades y se registrará su lote de ingreso.",
                boton: "Confirmar Ingreso"
            },

            REPONER: {
                descripcion: "Nueva entrada: se creará otro lote, con su propio vencimiento.",
                ayuda: "Se sumarán las unidades sin modificar los lotes anteriores.",
                boton: "Confirmar Reposición"
            },

            DESCONTAR: {
                descripcion: "Seleccione el lote afectado y el motivo de la merma.",
                ayuda: "Se restarán las unidades del lote seleccionado y del stock total.",
                boton: "Confirmar Descuento"
            }
        };

        const textosOperacion = textos[operacion] ?? textos.AGREGAR;

        $("#movimientoDescripcionOperacion").text(
            lotesCargados ? textosOperacion.descripcion : "Consultando los lotes del insumo..."
        );

        $("#movimientoAyudaInsumo").text(textosOperacion.ayuda);
        $("#movimientoTextoConfirmar").text(textosOperacion.boton);

        /* El resultado solo se calcula cuando la operación es válida */
        const stock = Number($("#movimientoStockActual").text());
        const textoCantidad = String($("#movimientoCantidadInsumo").val() ?? "");
        const cantidad = Number(textoCantidad);
        const resultado = stock + (esDescuento ? -cantidad : cantidad);
        const loteDisponible = Number($lote.find("option:selected").attr("data-disponible") ?? 0);

        const cantidadValida = textoCantidad !== "" &&
            Number.isInteger(stock) && Validaciones.validarEntero(textoCantidad, 1) &&
            cantidad > 0 && cantidad <= 4294967295 &&
            resultado >= 0 && resultado <= 4294967295;

        const loteValido = !esDescuento || (
            disponibles.length > 0 && $lote.val() !== "" && cantidad <= loteDisponible
        );

        $("#movimientoStockResultante").text(
            cantidadValida && loteValido ? resultado : "—"
        );

        $formulario.find('[type="submit"]').prop("disabled", !lotesCargados);
    }


    $("#modalGestionarExistencias").on(
        "change input",
        "#movimientoTipoInsumo, #movimientoCantidadInsumo, #movimientoMotivoInsumo, #movimientoLoteInsumo",
        sincronizarMovimientoExistencias
    );


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

        if ($modal.is("#modalEditarInsumo")) {
            const campos = {
                id_insumo: "id-insumo",
                nombre_insumo: "nombre-insumo",
                descripcion: "descripcion",
                presentacion: "presentacion",
                tipo_control: "tipo-control",
                stock_actual: "stock-actual",
                stock_original: "stock-actual",
                stock_minimo: "stock-minimo"
            };

            Object.entries(campos).forEach(function ([campo, atributo]) {
                $formulario.find('[name="' + campo + '"]').val(datoBoton($boton, atributo));
            });

            $("#editarEstadoInsumo").val(datoBoton($boton, "estado-insumo", "ACTIVO"));
        }

        if ($modal.is("#modalGestionarExistencias")) {
            const idInsumo = Number(datoBoton($boton, "id-insumo", "0"));

            ++versionLotesMovimiento;

            lotesCargados = false;
            lotesMovimiento = [];

            $("#movimientoIdInsumo").val(idInsumo);
            $("#movimientoNombreInsumo").text(datoBoton($boton, "nombre-insumo"));
            $("#movimientoStockActual").text(datoBoton($boton, "stock-actual", "0"));
            $("#movimientoStockResultante").text("—");

            $("#movimientoTipoInsumo").find("option").prop("disabled", false);
            $("#movimientoTipoInsumo").val("AGREGAR").prop("disabled", true);
            $("#movimientoFechaNoAplica").prop("checked", false);

            $("#movimientoLoteInsumo").empty().append(
                $("<option>", { value: "", text: "Consultando lotes..." })
            );

            $("#movimientoDescripcionOperacion").text("Consultando los lotes del insumo...");

            sincronizarMovimientoExistencias();

            if (Number.isSafeInteger(idInsumo) && idInsumo > 0) {
                cargarLotesMovimiento(idInsumo);

            } else {
                mensajeFormulario($formulario)
                    .text("No se pudo identificar el insumo seleccionado.")
                    .prop("hidden", false);
            }
        }

        if ($modal.is("#modalDesactivarInsumo")) {
            $("#desactivarIdInsumo").val(datoBoton($boton, "id-insumo"));
            $("#desactivarNombreInsumo").text(datoBoton($boton, "nombre-insumo"));
        }
    });


    /* Evitar cerrar un modal mientras se confirma la operación */
    $modales.on("hide.bs.modal", function (evento) {
        if ($(this).find("form").data("enviando")) evento.preventDefault();
    });


    $modales.on("hidden.bs.modal", function () {
        const $formulario = $(this).find("form").first();

        if (!$formulario.length) return;

        if ($(this).is("#modalGestionarExistencias")) {
            ++versionLotesMovimiento;

            lotesCargados = false;
            lotesMovimiento = [];

            $("#movimientoTipoInsumo").find("option").prop("disabled", false);
        }

        $formulario[0].reset();

        limpiarErrores($formulario);
    });


    /* ===================================================== */
    /* PROVEEDORES ASOCIADOS AL INSUMO */
    /* ===================================================== */

    let versionProveedoresDetalle = 0;


    function mostrarProveedoresDetalle(proveedores) {
        const $contenedor = $("#detalleProveedoresInsumo").empty();

        if (!proveedores.length) {
            $("<span>", { class: "text-muted" })
                .text("Este insumo no tiene proveedores asociados.")
                .appendTo($contenedor);

            return;
        }

        proveedores.forEach(function (proveedor) {
            const inactivo = String(proveedor.estado_proveedor || "").toUpperCase() === "INACTIVO";

            const $opcion = $("<span>", {
                class: "insumos-detalle-proveedor d-inline-flex align-items-center gap-2 rounded-pill px-3 py-2"
            });

            $("<i>", {
                class: "bi bi-check-circle-fill",
                "aria-hidden": "true"
            }).appendTo($opcion);

            $("<span>").text(proveedor.nombre_empresa || "Proveedor sin nombre").appendTo($opcion);

            if (inactivo) {
                $("<small>", { class: "insumos-detalle-proveedor-inactivo" })
                    .text("(Inactivo)")
                    .appendTo($opcion);
            }

            $opcion.appendTo($contenedor);
        });
    }


    /* ===================================================== */
    /* MOSTRAR LOTES EN DETALLES */
    /* ===================================================== */

    /* Reutilizar la misma ficha visual de Clientes y Proveedores */
    function mostrarLotesDetalle(lotes) {
        const $contenedor = $("#detalleLotesInsumo").empty();

        if (!lotes.length) {
            $("<div>", { class: "col-12" }).append(
                $("<p>", {
                    class: "detalle-ficha-value mb-0 text-muted",
                    text: "Todavía no se han registrado lotes para este insumo."
                })
            ).appendTo($contenedor);

            return;
        }

        lotes.forEach(function (lote) {
            const cantidad = Number(lote.cantidad_disponible ?? 0);

            const vencimiento = lote.fecha_vencimiento
                ? fechaLegible(lote.fecha_vencimiento)
                : "No aplica";

            const ingreso = fechaLegible(lote.fecha_ingreso, true);
            const proveedor = lote.nombre_proveedor || "Sin proveedor registrado";
            const estado = cantidad === 0 ? "AGOTADO" : "DISPONIBLE";

            const $tarjeta = $("<div>", { class: "detalle-ficha-card h-100" });

            const $icono = $("<span>", {
                class: "detalle-ficha-card-icon",
                "aria-hidden": "true"
            }).append($("<i>", { class: "bi bi-box-seam" }));

            const $contenido = $("<div>", { class: "detalle-ficha-card-content" });

            $("<span>", {
                class: "detalle-ficha-label",
                text: "Lote #" + lote.id_lote
            }).appendTo($contenido);

            $("<p>", {
                class: "detalle-ficha-value mb-1",
                text: "Disponibles: " + cantidad + " de " + lote.cantidad_inicial
            }).appendTo($contenido);

            $("<p>", {
                class: "detalle-ficha-value mb-1",
                text: "Proveedor: " + proveedor
            }).appendTo($contenido);

            $("<p>", {
                class: "detalle-ficha-value mb-1",
                text: "Ingreso: " + ingreso
            }).appendTo($contenido);

            $("<p>", {
                class: "detalle-ficha-value mb-1",
                text: "Vencimiento: " + vencimiento
            }).appendTo($contenido);

            $("<span>", {
                class: "badge " + (estado === "AGOTADO" ? "text-bg-secondary" : "text-bg-success"),
                text: estado
            }).appendTo($contenido);

            $("<div>", { class: "col-md-6" })
                .append($tarjeta.append($icono, $contenido))
                .appendTo($contenedor);
        });
    }


    /* ===================================================== */
    /* LLENAR MODAL DE DETALLES */
    /* ===================================================== */

    $("#modalDetalleInsumo").on("show.bs.modal", function (evento) {
        const $boton = $(evento.relatedTarget);
        const tipoControl = datoBoton($boton, "tipo-control", "UNITARIO");
        const estado = datoBoton($boton, "estado-insumo", "ACTIVO");

        $("#detalleNombreInsumo").text(datoBoton($boton, "nombre-insumo") || "Sin información");
        $("#detallePresentacionInsumo").text(datoBoton($boton, "presentacion") || "Sin información");
        $("#detalleDescripcionInsumo").text(datoBoton($boton, "descripcion").trim() || "Sin descripción");
        $("#detalleTipoControl").text(tipoControl === "POR_ENVASE" ? "Por envase" : "Consumo unitario");
        $("#detalleStockActual").text(datoBoton($boton, "stock-actual", "0"));
        $("#detalleStockMinimo").text(datoBoton($boton, "stock-minimo", "0"));
        $("#detalleFechaVencimiento").text(datoBoton($boton, "fecha-vencimiento-texto") || "No aplica");

        const estadoStock = datoBoton($boton, "estado-stock", "NORMAL").toUpperCase();
        const estadoVencimiento = datoBoton($boton, "estado-vencimiento", "SIN_FECHA").toUpperCase();

        $("#detalleEstadoStock")
            .removeClass("text-bg-danger text-bg-warning text-bg-success text-bg-secondary")
            .addClass(claseEstadoStock(estadoStock))
            .text(datoBoton($boton, "etiqueta-stock", "Normal"));

        $("#detalleEstadoVencimiento")
            .removeClass("text-bg-danger text-bg-warning text-bg-success text-bg-secondary")
            .addClass(claseEstadoVencimiento(estadoVencimiento))
            .text(datoBoton($boton, "etiqueta-vencimiento", "Sin fecha"));

        $("#detalleEstadoInsumo")
            .removeClass("estado-registro-activo estado-registro-inactivo")
            .addClass(estado === "INACTIVO" ? "estado-registro-inactivo" : "estado-registro-activo")
            .text(estado || "Sin estado");
    });


    /* Consultar por separado los lotes y las asociaciones de proveedores */
    $("#modalDetalleInsumo").on("show.bs.modal", async function (evento) {
        const turnoProveedores = ++versionProveedoresDetalle;
        const turnoLotes = ++versionLotesDetalle;
        const $boton = $(evento.relatedTarget);
        const idInsumo = Number(datoBoton($boton, "id-insumo", "0"));

        const $proveedores = $("#detalleProveedoresInsumo").empty();
        const $lotes = $("#detalleLotesInsumo").empty();

        $("<span>", { class: "text-muted", text: "Consultando proveedores..." })
            .appendTo($proveedores);

        $("<div>", { class: "col-12" }).append(
            $("<p>", {
                class: "detalle-ficha-value text-muted mb-0",
                text: "Consultando lotes..."
            })
        ).appendTo($lotes);

        if (!Number.isSafeInteger(idInsumo) || idInsumo < 1) {
            $proveedores.text("No se pudo identificar el insumo.");
            $lotes.text("No se pudo identificar el insumo.");

            return;
        }

        /* Se consultan independientemente para no ocultar datos si una falla */
        solicitarInsumos({
            accion: "consultar_proveedores",
            id_insumo: idInsumo

        }).then(function (respuesta) {
            if (turnoProveedores !== versionProveedoresDetalle) return;

            if (
                !Array.isArray(respuesta.proveedores) ||
                !respuesta.proveedores.every(proveedor => $.isPlainObject(proveedor))
            ) {
                throw new Error("La información de proveedores no es válida.");
            }

            mostrarProveedoresDetalle(respuesta.proveedores);

        }).catch(function (error) {
            if (turnoProveedores !== versionProveedoresDetalle || redirigiendo) return;

            window.Alertas?.notificar(
                error.message || "No fue posible consultar los proveedores.",
                "error",
                { origen: $proveedores }
            );

            $proveedores.text(error.message || "No fue posible consultar los proveedores.");
        });

        consultarLotes(idInsumo).then(function (lotes) {
            if (turnoLotes !== versionLotesDetalle) return;

            mostrarLotesDetalle(lotes);

        }).catch(function (error) {
            if (turnoLotes !== versionLotesDetalle || redirigiendo) return;

            window.Alertas?.notificar(
                error.message || "No fue posible consultar los lotes.",
                "error",
                { origen: $lotes }
            );

            $lotes.empty().append(
                $("<div>", { class: "col-12" }).append(
                    $("<p>", {
                        class: "detalle-ficha-value text-muted mb-0",
                        text: error.message || "No fue posible consultar los lotes."
                    })
                )
            );
        });
    });


    /* Invalidar respuestas anteriores al cerrar Detalles */
    $("#modalDetalleInsumo").on("hide.bs.modal", function () {
        ++versionProveedoresDetalle;
        ++versionLotesDetalle;
    });


    /* ===================================================== */
    /* REGISTRAR, MODIFICAR, GESTIONAR EXISTENCIAS Y DESACTIVAR */
    /* ===================================================== */

    $modales.find("form").on("submit", async function (evento) {
        evento.preventDefault();

        if (operacionEnCurso || redirigiendo) return;

        const $formulario = $(this);

        limpiarErrores($formulario);

        const validacion = Validaciones.validarFormulario(this, reglasFormulario($formulario));

        if (!validacion.valido) {
            mensajeFormulario($formulario).text("Revise los campos señalados.").prop("hidden", false);

            if (validacion.primerCampo?.type !== "hidden") validacion.primerCampo?.focus();

            return;
        }

        /* Comprobar que las entradas indiquen fecha o No aplica y las mermas un lote */
        if ($formulario.closest(".modal").is("#modalGestionarExistencias")) {
            if (!lotesCargados) {
                mensajeFormulario($formulario)
                    .text("Primero debemos comprobar los lotes disponibles.")
                    .prop("hidden", false);

                return;
            }

            const operacion = $("#movimientoTipoInsumo").val();
            const cantidad = Number($("#movimientoCantidadInsumo").val());
            const stock = Number($("#movimientoStockActual").text());

            if (operacion !== "DESCONTAR" && stock + cantidad > 4294967295) {
                mostrarErrores($formulario, {
                    message: "La cantidad supera el límite de existencias permitido.",
                    errores: {
                        cantidad_movimiento: "El stock resultante no puede superar 4294967295."
                    }
                });

                $("#movimientoCantidadInsumo").trigger("focus");

                return;
            }

            if (operacion === "DESCONTAR") {
                const $opcionLote = $("#movimientoLoteInsumo option:selected");
                const disponibles = Number($opcionLote.attr("data-disponible") ?? 0);

                if (!$opcionLote.val() || cantidad > disponibles || cantidad > stock) {
                    mostrarErrores($formulario, {
                        message: "Seleccione un lote que tenga unidades suficientes para este descuento.",
                        errores: {
                            id_lote: "El lote seleccionado no tiene suficientes existencias."
                        }
                    });

                    $("#movimientoLoteInsumo").trigger("focus");

                    return;
                }
            }
        }

        const datos = {};

        $formulario.serializeArray().forEach(function (campo) {
            datos[campo.name] = campo.value;
        });

        /* La casilla No aplica no tiene name, pero la fecha se envía vacía */
        if (
            $formulario.closest(".modal").is("#modalGestionarExistencias") &&
            $("#movimientoFechaNoAplica").prop("checked")
        ) {
            datos.fecha_vencimiento = "";
        }

        if (
            $formulario.closest(".modal").is("#modalGestionarExistencias") &&
            $("#movimientoTipoInsumo").val() === "DESCONTAR" &&
            window.Alertas?.disponible()
        ) {
            const confirmado = await window.Alertas.confirmar(
                $("#movimientoNombreInsumo").text() + ": se descontarán " +
                $("#movimientoCantidadInsumo").val() + " unidades del lote " +
                $("#movimientoLoteInsumo option:selected").text() + ".",
                {
                    titulo: "Confirmar descuento de existencias",
                    textoConfirmar: "Sí, descontar",
                    origen: $formulario
                }
            );

            if (!confirmado) return;
            if (operacionEnCurso || redirigiendo) return;
        }

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
            const respuesta = await solicitarInsumos(datos);

            respuestaConfirmada = respuesta;
            mensajeExito = typeof respuesta.mensaje === "string" ? respuesta.mensaje : mensajeExito;

            /* El modal se cierra únicamente después de confirmar el guardado */
            $formulario.data("enviando", false);

            const $modal = $formulario.closest(".modal");

            const cerrarModal = function () {
                window.bootstrap.Modal.getOrCreateInstance($modal[0]).hide();
            };

            /* Si aún está terminando de abrirse, lo cierra al finalizar */
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
                    mensajeExito + " No se pudo actualizar la lista. " +
                    "Use Actualizar lista; la operación ya fue confirmada.",
                    true,
                    true,
                    "warning"
                );
            }

        } catch (error) {
            if (respuestaConfirmada) {
                mostrarMensajeGeneral(
                    mensajeExito + " No se pudo actualizar la vista. " +
                    "La operación ya fue confirmada; actualice la lista antes de repetirla.",
                    true,
                    true,
                    "warning"
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

            if (primerCampo && !redirigiendo) primerCampo.trigger("focus");
        }
    });


    /* ===================================================== */
    /* DESCARGAR LISTADO DE INSUMOS EN PDF */
    /* ===================================================== */

    /* Conectar el botón PDF existente, sin modificar la vista */
    const $botonPDFInsumos = $modulo.find(".module-header .btn-templo-secondary").filter(function () {
        return $(this).find(".bi-file-earmark-pdf").length > 0;
    }).first();

    let preparandoPDFInsumos = false;

    $botonPDFInsumos.on("click", async function () {
        if (preparandoPDFInsumos || redirigiendo) return;

        preparandoPDFInsumos = true;

        const $boton = $(this);
        const contenidoOriginal = $boton.html();

        $boton.prop("disabled", true).attr("aria-busy", "true").html(
            '<i class="bi bi-hourglass-split" aria-hidden="true"></i> Preparando PDF...'
        );

        $mensajeGeneral.prop("hidden", true);

        try {
            /* Utilizar la consulta oficial de Reportes y solo los insumos activos */
            const respuesta = await new Promise(function (resolve, reject) {
                $.ajax({
                    url: "Index.php?url=reportes",
                    method: "POST",
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                    processData: false,
                    timeout: 30000,

                    data: JSON.stringify({
                        accion: "consultar",
                        filtros: {
                            reporte: "existencias",
                            estado_registro: "ACTIVO"
                        }
                    }),

                    success: resolve,

                    error: function (xhr, estado) {
                        const error = new Error(
                            typeof xhr.responseJSON?.mensaje === "string"
                                ? xhr.responseJSON.mensaje
                                : estado === "timeout"
                                    ? "La consulta tardó demasiado. Intente nuevamente."
                                    : "No fue posible preparar el PDF de insumos."
                        );

                        error.estado = xhr.status;

                        reject(error);
                    }
                });
            });

            if (respuesta?.ok !== true || respuesta.reporte?.tipo !== "existencias") {
                throw new Error(respuesta?.mensaje || "No fue posible confirmar el reporte de insumos.");
            }

            /* El controlador no emite un token cuando el resultado está vacío */
            if (respuesta.reporte.total === 0) {
                mostrarMensajeGeneral("Todavía no hay insumos activos para incluir en el PDF.");
                return;
            }

            if (
                typeof respuesta.token_pdf !== "string" ||
                !/^[a-f0-9]{48}$/i.test(respuesta.token_pdf)
            ) {
                throw new Error("El servidor no entregó un enlace válido para descargar el PDF.");
            }

            /* Descargar en la misma pestaña: el servidor devuelve un adjunto PDF */
            const $enlace = $("<a>", {
                href: "Index.php?url=reportes&pdf=" + encodeURIComponent(respuesta.token_pdf)
            }).appendTo("body");

            $enlace.get(0).click();
            $enlace.remove();

            mostrarMensajeGeneral("Se solicitó la descarga del reporte de insumos activos.");

        } catch (error) {
            if (error.estado === 401) {
                redirigiendo = true;
                window.location.assign("Index.php?url=login&sesion=expirada");

                return;
            }

            mostrarMensajeGeneral(error.message || "No fue posible generar el PDF.", true);

        } finally {
            $boton.prop("disabled", false).removeAttr("aria-busy").html(contenidoOriginal);

            preparandoPDFInsumos = false;
        }
    });


    /* ===================================================== */
    /* CONSULTA INICIAL */
    /* ===================================================== */

    /* Actualizar la lista mediante AJAX */
    actualizarLista().catch(function (error) {
        mostrarMensajeGeneral(error.message, true, true);
    });
});