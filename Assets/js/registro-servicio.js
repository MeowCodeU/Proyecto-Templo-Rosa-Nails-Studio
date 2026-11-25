/* ===================================================== */
/* GESTIÓN DEL REGISTRO DE SERVICIO */
/* ===================================================== */

$(function () {
    "use strict";

    const modulo = $(".registro-servicio-module");

    if (!modulo.length) return;


    /* Elementos utilizados por los formularios y el listado */
    const formularioServicio = $("#formServicioRealizado");
    const formularioInsumos = $("#formInsumosUtilizados");
    const selectorServicio = $("#servicioRealizado");
    const campoPrecio = $("#totalServicio");
    const campoNota = $("#notaTecnicaServicio");
    const campoFoto = $("#fotoResultadoServicio");
    const campoDestacada = $("#destacarCatalogo");
    const tablaElemento = $("#tablaServiciosPendientes");
    const mensaje = $("#mensajeRegistroServicio");
    const ruta = formularioServicio.attr("action") || "Index.php?url=registroServicio";


    /* Datos de la atención y sus formularios */
    let tabla = null;
    let atencion = null;
    let detalleSeleccionado = "";
    let firmaServicioInicial = "";
    let token = formularioServicio.find('[name="token_csrf"]').val() || "";
    let insumosSeleccionados = [];
    let insumosGuardados = new Map();
    let inventario = new Map();
    let historial = [];
    let trabajos = [];
    let urlTemporal = null;


    /* Estados utilizados para controlar las operaciones */
    let cargando = false;
    let guardando = false;
    let cargandoCatalogo = false;
    let imprimiendo = false;
    let redirigiendo = false;
    let requiereVerificacion = false;
    let confirmacionAbierta = false;
    let habilitarTabla;
    let preparando = false;
    let validacionesRegistro = null;
    let reglasServicio = null;
    let reglasAgregarInsumo = null;


    /* Preparar las reglas del módulo con las funciones compartidas */
    const cargaValidaciones = import("./validaciones/validaciones.js")
        .then(function (moduloValidaciones) {
            const v = moduloValidaciones.Validaciones;
            validacionesRegistro = v;

            reglasServicio = Object.freeze({
                id_agendamiento: {
                    comprobar: valor => v.validarIdentificador(valor)
                        ? null : "Seleccione una atención válida."
                },
                id_detalle_agendamiento: {
                    comprobar: valor => v.validarIdentificador(valor)
                        ? null : "Seleccione un servicio válido de la atención."
                },
                precio_aplicado: {
                    comprobar: function (valor) {
                        const aviso = "Ingrese un precio entre 0 y 99999999.99, con un máximo de dos decimales.";

                        if (!v.validarPrecio(valor)) return aviso;

                        const entero = valor.trim().split(".")[0];

                        return entero.length >= 1 && entero.length <= 8 ? null : aviso;
                    }
                },
                nota_tecnica: {
                    comprobar: valor => typeof valor === "string" && !valor.includes("\0") &&
                        new TextEncoder().encode(valor).length <= 65535
                        ? null : "Ingrese una nota técnica válida que no supere el tamaño permitido."
                },
                foto_servicio: {
                    comprobar: () => comprobarFotografia(campoFoto.prop("files")?.[0] || null)
                }
            });

            reglasAgregarInsumo = Object.freeze({
                id_insumo: {
                    comprobar: valor => v.validarIdentificador(valor) && inventario.has(Number(valor))
                        ? null : "Seleccione un insumo válido."
                },
                cantidad_utilizada: {
                    comprobar: valor => v.validarIdentificador(valor) && Number(valor) <= 99999999
                        ? null : "Ingrese una cantidad entera entre 1 y 99999999."
                }
            });

            const reglasEdicionServicio = { ...reglasServicio };
            delete reglasEdicionServicio.foto_servicio;

            v.activar(formularioServicio[0], reglasEdicionServicio);

            /* Los campos para agregar una fila pueden quedar vacíos al guardar la lista */
            const reglasEdicionInsumos = {};

            Object.entries(reglasAgregarInsumo).forEach(function ([nombre, regla]) {
                reglasEdicionInsumos[nombre] = {
                    comprobar: valor => valor === "" ? null : regla.comprobar(valor)
                };
            });

            v.activar(formularioInsumos[0], reglasEdicionInsumos);

            return v;
        })
        .catch(function () {
            return null;
        });

    formularioServicio.add(formularioInsumos).prop("noValidate", true);


    /* Esperar la inicialización de DataTables antes de actualizar sus filas */
    const tablaPreparada = new Promise(resolve => {
        habilitarTabla = resolve;
    });


    /* ================================================= */
    /* FORMATOS Y COMPROBACIONES */
    /* ================================================= */

    function enteroPositivo(valor) {
        const numero = Number(valor);

        return /^\d+$/.test(String(valor)) &&
            Number.isSafeInteger(numero) &&
            numero > 0;
    }

    function comprobarLista(datos) {
        if (!Array.isArray(datos) || !datos.every($.isPlainObject)) {
            throw new Error("La respuesta recibida no contiene una lista válida.");
        }

        return datos;
    }

    /* Presentar las fechas sin alterar el día por la zona horaria */
    function formatearFecha(fecha) {
        const partes = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(fecha || ""));

        return partes ? partes[3] + "/" + partes[2] + "/" + partes[1] : "—";
    }

    function formatearMonto(valor) {
        if (valor === null || valor === "" || !Number.isFinite(Number(valor))) {
            return "—";
        }

        return Number(valor).toLocaleString("es-VE", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    /* Comparar importes equivalentes escritos con distintos decimales */
    function normalizarPrecio(valor) {
        return valor === "" || valor === null ? "" : String(Number(valor));
    }

    function urlFotografia(idFoto) {
        const direccion = new URL(ruta, window.location.href);

        direccion.searchParams.set("accion", "foto");
        direccion.searchParams.set("id_foto", String(idFoto));

        return direccion.href;
    }

    function estaOcupado() {
        return cargando || guardando || preparando || redirigiendo ||
            imprimiendo || confirmacionAbierta;
    }

    function atencionCerrada() {
        return atencion && ["REALIZADA", "CANCELADA"].includes(atencion.estado);
    }


    /* ================================================= */
    /* MENSAJES Y TOKEN DEL FORMULARIO */
    /* ================================================= */

    function mostrarMensaje(texto, tipo = "danger", notificar = false) {
        if (!redirigiendo && (tipo === "success" || notificar)) window.Alertas?.notificar(texto, tipo);

        mensaje
            .removeClass("d-none alert-success alert-danger alert-warning alert-info")
            .addClass("alert-" + tipo)
            .attr("role", tipo === "danger" || tipo === "warning" ? "alert" : "status")
            .prop("hidden", false)
            .text(texto);
    }

    function limpiarMensaje() {
        mensaje.empty().prop("hidden", true).addClass("d-none");
    }

    /* Actualizar el token recibido y su valor original en los formularios */
    function actualizarToken(respuesta) {
        if (typeof respuesta?.token_csrf !== "string" || !respuesta.token_csrf) return;

        token = respuesta.token_csrf;

        formularioServicio.add(formularioInsumos)
            .find('[name="token_csrf"]').each(function () {
                $(this).val(token).prop("defaultValue", token);
            });
    }


    /* ================================================= */
    /* SOLICITUDES DEL MÓDULO */
    /* ================================================= */

    function solicitar(datos, { fotografia = null, escritura = false } = {}) {
        return new Promise(function (resolve, reject) {
            const contenido = { ...datos, token_csrf: token };

            let cuerpo = JSON.stringify(contenido);
            let tipoContenido = "application/json; charset=utf-8";

            /* Adjuntar la imagen mediante FormData cuando corresponde */
            if (fotografia) {
                cuerpo = new FormData();

                $.each(contenido, function (nombre, valor) {
                    cuerpo.append(nombre, String(valor));
                });

                cuerpo.append("foto_servicio", fotografia);
                tipoContenido = false;
            }

            $.ajax({
                url: ruta,
                method: "POST",
                contentType: tipoContenido,
                processData: false,
                dataType: "json",
                data: cuerpo,
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                },
                timeout: 30000

            }).done(function (respuesta) {
                actualizarToken(respuesta);

                if ($.isPlainObject(respuesta) && respuesta.ok === true) {
                    resolve(respuesta);
                    return;
                }

                const error = new Error(
                    typeof respuesta?.mensaje === "string"
                        ? respuesta.mensaje
                        : "No se pudo confirmar el resultado de la solicitud."
                );

                error.ambiguo = escritura && respuesta?.ok !== false;
                error.respuesta = respuesta;

                reject(error);

            }).fail(function (xhr, estado) {
                const respuesta = xhr.responseJSON;

                actualizarToken(respuesta);

                if (xhr.status === 401 && !redirigiendo) {
                    redirigiendo = true;
                    window.location.assign("Index.php?url=login&sesion=expirada");
                }

                /* Identificar los fallos que impiden confirmar un guardado */
                const ambiguo = escritura && (
                    xhr.status === 0 ||
                    xhr.status >= 500 ||
                    estado === "timeout" ||
                    estado === "parsererror"
                );

                const error = new Error(
                    ambiguo
                        ? datos.accion === "iniciarAtencion"
                            ? "No se pudo confirmar el inicio de la atención. Pulse Refrescar y revise el estado de la cita antes de intentarlo nuevamente."
                            : "No se pudo confirmar el guardado. Actualice la atención y revise el resultado antes de enviarlo nuevamente."
                        : typeof respuesta?.mensaje === "string"
                            ? respuesta.mensaje
                            : "No fue posible completar la solicitud. Intente nuevamente."
                );

                error.ambiguo = ambiguo;
                error.estado = xhr.status;
                error.respuesta = respuesta;

                reject(error);
            });
        });
    }


    /* ================================================= */
    /* VALIDACIONES DE LOS FORMULARIOS */
    /* ================================================= */

    async function obtenerValidaciones() {
        preparando = true;

        let v;

        try {
            v = await cargaValidaciones;
        } finally {
            preparando = false;
        }

        if (!v) {
            mostrarMensaje("No fue posible cargar las validaciones. Recargue la página antes de guardar.");
        }

        return v;
    }

    function limpiarValidacionesFormulario(formulario) {
        validacionesRegistro?.limpiarFormulario(formulario[0]);
        formulario.find("[aria-invalid]").removeAttr("aria-invalid");
    }

    function comprobarFotografia(archivo) {
        if (!archivo) return null;

        if (
            (archivo.type && !["image/jpeg", "image/png", "image/webp"].includes(archivo.type)) ||
            archivo.size <= 0 ||
            archivo.size > 2 * 1024 * 1024
        ) {
            return "Seleccione una fotografía JPEG, PNG o WebP de hasta 2 MB.";
        }

        return null;
    }

    function mostrarErrorCatalogo(texto) {
        const referencia = campoDestacada.closest(".form-check");
        let aviso = referencia.next(".registro-catalogo-feedback");

        campoDestacada.removeAttr("aria-invalid");

        if (!texto) {
            aviso.remove();
            return;
        }

        if (!aviso.length) {
            aviso = $("<div>", {
                class: "templo-validacion-feedback is-invalid registro-catalogo-feedback"
            }).insertAfter(referencia);
        }

        aviso.text(texto);
        campoDestacada.attr("aria-invalid", "true");
    }

    function validarCatalogo() {
        const existeFoto = enteroPositivo(obtenerDetalle()?.id_foto);
        const nuevaFoto = campoFoto.prop("files")?.[0];

        const valido = !campoDestacada.prop("checked") ||
            existeFoto || Boolean(nuevaFoto);

        mostrarErrorCatalogo(
            valido ? "" : "Seleccione una fotografía para destacarla en el catálogo."
        );

        return valido;
    }

    function mostrarErrorInsumos(texto) {
        let aviso = formularioInsumos.find(".registro-insumos-feedback");

        if (!texto) {
            aviso.remove();
            return null;
        }

        if (!aviso.length) {
            aviso = $("<div>", {
                class: "templo-validacion-feedback is-invalid registro-insumos-feedback",
                tabindex: "-1"
            }).insertAfter(
                $("#tbodyInsumosSeleccionados").closest(".table-responsive")
            );
        }

        aviso.text(texto);

        return aviso[0];
    }

    function mostrarErroresServidor(respuesta, esServicio) {
        if (!validacionesRegistro || !$.isPlainObject(respuesta)) return null;

        const errores = $.isPlainObject(respuesta.errores)
            ? { ...respuesta.errores }
            : {};

        if (
            typeof respuesta.campo === "string" &&
            typeof respuesta.mensaje === "string" &&
            !errores[respuesta.campo]
        ) {
            errores[respuesta.campo] = respuesta.mensaje;
        }

        if (esServicio && typeof errores.destacada_catalogo === "string") {
            mostrarErrorCatalogo(errores.destacada_catalogo);
            delete errores.destacada_catalogo;
        }

        const formulario = esServicio ? formularioServicio : formularioInsumos;
        const primero = validacionesRegistro.mostrarErrores(formulario[0], errores);

        const lista = !esServicio && typeof errores.insumos === "string"
            ? mostrarErrorInsumos(errores.insumos)
            : null;

        return primero || lista ||
            (campoDestacada.attr("aria-invalid") === "true" && esServicio ? campoDestacada[0] : null);
    }

    function validarListaInsumos(v) {
        const ids = new Set();
        let error = "";

        if (insumosSeleccionados.length > 1000) {
            error = "La lista de insumos supera el máximo permitido.";
        }

        for (const fila of insumosSeleccionados) {
            if (error) break;

            if (
                !v.validarIdentificador(fila.id_insumo) ||
                !v.validarIdentificador(fila.cantidad) ||
                Number(fila.cantidad) > 99999999 ||
                ids.has(Number(fila.id_insumo))
            ) {
                error = "La lista contiene insumos repetidos o cantidades que no son válidas.";
                break;
            }

            const insumo = inventario.get(Number(fila.id_insumo));

            if (!insumo || Number(fila.cantidad) > cantidadDisponible(insumo)) {
                error = "Revise las cantidades de la lista: uno de los insumos no tiene suficientes unidades disponibles para esta atención.";
                break;
            }

            ids.add(Number(fila.id_insumo));
        }

        const aviso = mostrarErrorInsumos(error);

        if (aviso) aviso.focus();

        return !error;
    }

    campoDestacada.on("change", validarCatalogo);


    /* ================================================= */
    /* CONTROL DE LOS FORMULARIOS */
    /* ================================================= */

    function actualizarPermisos() {
        if (cargando || guardando || redirigiendo) return;

        const bloquearEdicion = !atencion ||
            atencionCerrada() || requiereVerificacion;

        formularioServicio
            .find("input:not([type='hidden']), textarea")
            .prop("disabled", bloquearEdicion || !detalleSeleccionado);

        selectorServicio.prop("disabled", !atencion);
        formularioInsumos.find(":input").prop("disabled", bloquearEdicion);

        $("#btnGuardarServicio")
            .prop("disabled", bloquearEdicion || !detalleSeleccionado);

        $("#btnFinalizarServicio").prop("disabled", bloquearEdicion);
        $("#fechaServicio").prop("readOnly", true);
        $("#btnActualizarAtencionServicio").prop("disabled", !atencion);

        $("#btnImprimirHistorial")
            .prop("disabled", !atencion || historial.length === 0);
    }

    function bloquearControles(boton, texto) {
        /* Conservar el estado de los controles y el contenido del botón */
        const controles = modulo.find(":input").map(function () {
            return {
                elemento: this,
                deshabilitado: $(this).prop("disabled")
            };
        }).get();

        const contenidoBoton = boton?.html();

        modulo.attr("aria-busy", "true");
        modulo.find(":input").prop("disabled", true);

        if (boton?.length && texto) boton.text(texto);

        return function () {
            if (redirigiendo) return;

            controles.forEach(function (control) {
                $(control.elemento).prop("disabled", control.deshabilitado);
            });

            if (boton?.length && contenidoBoton !== undefined) {
                boton.html(contenidoBoton);
            }

            modulo.removeAttr("aria-busy");
            actualizarPermisos();
        };
    }


    /* ================================================= */
    /* CONFIRMACIÓN DE LAS ACCIONES */
    /* ================================================= */

    function confirmarAccion(texto) {
        if (window.Alertas?.disponible()) {
            confirmacionAbierta = true;
            return window.Alertas.confirmar(texto).finally(function () {
                confirmacionAbierta = false;
            });
        }

        return new Promise(function (resolve) {
            const modal = $("#modalConfirmacionRegistroServicio");
            const aceptar = $("#btnConfirmarRegistroServicio");

            if (!modal.length || !window.bootstrap?.Modal) {
                mostrarMensaje("No fue posible abrir la confirmación. Actualice la vista del módulo.");
                resolve(false);
                return;
            }

            let confirmado = false;

            const instancia = window.bootstrap.Modal.getOrCreateInstance(modal[0]);

            confirmacionAbierta = true;
            $("#textoConfirmacionRegistroServicio").text(texto);

            aceptar.off("click.confirmacionServicio")
                .one("click.confirmacionServicio", function () {
                    confirmado = true;
                    instancia.hide();
                });

            /* Resolver también cuando el usuario cierre o cancele el modal */
            modal.one("hidden.bs.modal", function () {
                aceptar.off("click.confirmacionServicio");
                confirmacionAbierta = false;
                resolve(confirmado);
            });

            instancia.show();
        });
    }


    /* ================================================= */
    /* COMPROBAR CAMBIOS SIN GUARDAR */
    /* ================================================= */

    function datosServicioActual() {
        return {
            id_detalle_agendamiento: detalleSeleccionado,
            precio_aplicado: normalizarPrecio(campoPrecio.val()),
            nota_tecnica: campoNota.val() || "",
            destacada_catalogo: campoDestacada.prop("checked") ? 1 : 0
        };
    }

    function firmaDetalle(detalle) {
        return JSON.stringify({
            id_detalle_agendamiento: String(detalle.id_detalle_agendamiento),
            precio_aplicado: normalizarPrecio(detalle.precio_aplicado ?? detalle.precio_actual),
            nota_tecnica: detalle.nota_tecnica || "",
            destacada_catalogo: Number(detalle.destacada_catalogo) === 1 ? 1 : 0
        });
    }

    function servicioModificado() {
        return Boolean(detalleSeleccionado) && (
            JSON.stringify(datosServicioActual()) !== firmaServicioInicial ||
            Boolean(campoFoto.prop("files")?.length)
        );
    }

    /* Comparar las cantidades sin depender del orden de las filas */
    function firmaInsumos(datos) {
        return JSON.stringify(
            datos.map(insumo => [
                Number(insumo.id_insumo),
                Number(insumo.cantidad)
            ]).sort((primero, segundo) => primero[0] - segundo[0])
        );
    }

    function insumosModificados() {
        const anteriores = Array.from(
            insumosGuardados,
            ([id_insumo, cantidad]) => ({ id_insumo, cantidad })
        );

        return firmaInsumos(insumosSeleccionados) !== firmaInsumos(anteriores);
    }


    /* ================================================= */
    /* TABLA DE AGENDAMIENTOS */
    /* ================================================= */

    if ($.fn.DataTable) {
        const existente = $.fn.DataTable.isDataTable("#tablaServiciosPendientes");

        tabla = existente
            ? tablaElemento.DataTable()
            : tablaElemento.DataTable({
                language: {
                    url: "Assets/libs/datatables/i18n/es-ES.json",
                    emptyTable: "No hay agendamientos para atender."
                },
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50],
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
                initComplete: function () {
                    habilitarTabla();
                }
            });

        if (existente) habilitarTabla();

    } else {
        habilitarTabla();
    }

    function crearFilaAgendamiento(cita) {
        const clases = {
            AGENDADA: "estado-programada",
            CONFIRMADA: "estado-confirmada",
            "EN ESPERA": "estado-en-espera",
            DEMORADA: "estado-demorada",
            "EN ATENCIÓN": "estado-activa"
        };

        const fila = $("<tr>");

        $("<td>")
            .attr("data-order", cita.fecha)
            .text(formatearFecha(cita.fecha))
            .appendTo(fila);

        $("<td>").text(String(cita.hora || "").slice(0, 5)).appendTo(fila);
        $("<td>").text(cita.clienta || "Sin información").appendTo(fila);
        $("<td>").text(cita.servicios || "Sin servicios asociados").appendTo(fila);

        $("<td>")
            .attr("data-order", cita.monto_total)
            .text(formatearMonto(cita.monto_total))
            .appendTo(fila);

        $("<td>").append($("<span>", {
            class: "badge " + (clases[cita.estado] || "estado-programada"),
            text: cita.estado || "Sin información"
        })).appendTo(fila);

        $("<td>", { class: "text-center" }).append(
            $("<button>", {
                type: "button",
                class: "btn-templo-secondary btn-registrar-servicio",
                "data-id-agendamiento": cita.id_agendamiento,
                text: cita.estado === "EN ATENCIÓN" ? "Continuar atención" : "Atender"
            })
        ).appendTo(fila);

        return fila[0];
    }

    async function actualizarListado() {
        const [respuesta] = await Promise.all([
            solicitar({ accion: "consultar" }),
            tablaPreparada
        ]);

        const citas = comprobarLista(respuesta.agendamientos);

        comprobarLista(respuesta.insumos);

        /* Validar la lista antes de sustituir las filas existentes */
        if (!citas.every(cita =>
            enteroPositivo(cita.id_agendamiento) &&
            ["EN ESPERA", "DEMORADA", "EN ATENCIÓN"].includes(cita.estado)
        )) {
            throw new Error("No fue posible comprobar los agendamientos recibidos.");
        }

        const filas = citas.map(crearFilaAgendamiento);

        /* Conservar la búsqueda, la ordenación y una página válida */
        if (tabla) {
            const pagina = tabla.page();

            tabla.clear().rows.add(filas).draw(false);

            tabla.page(
                Math.min(pagina, Math.max(0, tabla.page.info().pages - 1))
            ).draw("page");

        } else {
            $("#tbodyServiciosPendientes").empty().append(filas);

            if (!filas.length) {
                $("<tr>").append($("<td>", {
                    colspan: 7,
                    class: "text-center",
                    text: "No hay agendamientos para atender."
                })).appendTo("#tbodyServiciosPendientes");
            }
        }
    }

    async function cargarListado() {
        if (estaOcupado()) return;

        cargando = true;

        const restaurar = bloquearControles(
            $("#btnRefrescarRegistroServicio"),
            "Actualizando..."
        );

        try {
            await actualizarListado();
            limpiarMensaje();

        } catch (error) {
            mostrarMensaje(error.message, "danger", true);

        } finally {
            cargando = false;
            restaurar();
        }
    }


    /* ================================================= */
    /* FOTOGRAFÍA DEL SERVICIO */
    /* ================================================= */

    function liberarVistaPrevia() {
        if (urlTemporal) URL.revokeObjectURL(urlTemporal);
        urlTemporal = null;
    }

    function mostrarFotografia(direccion = "") {
        $("#previewResultadoServicio")
            .toggleClass("d-none", !direccion)
            .attr("src", direccion || null);

        $("#previewResultadoVacio")
            .toggleClass("d-none", Boolean(direccion));
    }

    function obtenerDetalle() {
        return atencion?.servicios.find(detalle =>
            String(detalle.id_detalle_agendamiento) === detalleSeleccionado
        );
    }

    function cargarServicio(idDetalle) {
        limpiarValidacionesFormulario(formularioServicio);

        detalleSeleccionado = String(idDetalle || "");
        selectorServicio.val(detalleSeleccionado);

        /* Retirar la selección de archivo del servicio anterior */
        liberarVistaPrevia();
        campoFoto.val("");

        const detalle = obtenerDetalle();

        /* Utilizar el precio aplicado o proponer el precio actual del catálogo */
        campoPrecio.val(
            detalle ? detalle.precio_aplicado ?? detalle.precio_actual : ""
        );

        campoNota.val(detalle?.nota_tecnica || "");

        campoDestacada.prop(
            "checked",
            Number(detalle?.destacada_catalogo) === 1
        );

        mostrarFotografia(
            enteroPositivo(detalle?.id_foto) ? urlFotografia(detalle.id_foto) : ""
        );

        firmaServicioInicial = detalle ? firmaDetalle(detalle) : "";

        actualizarPermisos();
    }

    campoFoto.on("change", function () {
        const archivo = $(this).prop("files")?.[0];
        const error = comprobarFotografia(archivo);

        liberarVistaPrevia();

        if (error) {
            campoFoto.val("");

            validacionesRegistro?.mostrarErrores(formularioServicio[0], {
                foto_servicio: error
            });

            mostrarMensaje("La fotografía se descartó. " + error);

        } else if (archivo) {
            validacionesRegistro?.validarFormulario(formularioServicio[0], {
                foto_servicio: reglasServicio.foto_servicio
            });

            urlTemporal = URL.createObjectURL(archivo);
            mostrarFotografia(urlTemporal);
            validarCatalogo();

            return;

        } else {
            validacionesRegistro?.validarFormulario(formularioServicio[0], {
                foto_servicio: reglasServicio.foto_servicio
            });
        }

        const detalle = obtenerDetalle();

        mostrarFotografia(
            enteroPositivo(detalle?.id_foto) ? urlFotografia(detalle.id_foto) : ""
        );

        validarCatalogo();
    });

    $("#previewResultadoServicio").on("error", function () {
        mostrarFotografia();
    });


    /* ================================================= */
    /* CONSUMOS DE INSUMOS */
    /* ================================================= */

    /* Incluir las unidades ya asignadas a esta misma atención */
    function cantidadDisponible(insumo) {
        const anterior = insumosGuardados.get(Number(insumo.id_insumo)) || 0;

        return insumo.estado_insumo === "INACTIVO"
            ? anterior
            : Number(insumo.stock_actual) + anterior;
    }

    function actualizarDatosInsumo() {
        const insumo = inventario.get(Number($("#insumoUtilizado").val()));

        $("#presentacionInsumo").val(insumo?.presentacion || "");

        $("#stockDisponibleInsumo")
            .val(insumo ? cantidadDisponible(insumo) : "");
    }

    function prepararInventario(insumos, registrados) {
        inventario = new Map(insumos.map(insumo => [
            Number(insumo.id_insumo),
            { ...insumo, estado_insumo: "ACTIVO" }
        ]));

        insumosGuardados = new Map(registrados.map(insumo => [
            Number(insumo.id_insumo),
            Number(insumo.cantidad)
        ]));

        /* Conservar los insumos utilizados aunque después se desactiven */
        registrados.forEach(function (insumo) {
            if (!inventario.has(Number(insumo.id_insumo))) {
                inventario.set(Number(insumo.id_insumo), { ...insumo });
            }
        });

        const selector = $("#insumoUtilizado");
        const seleccionado = selector.val();

        selector.empty().append($("<option>", {
            value: "",
            text: "Seleccionar insumo..."
        }));

        Array.from(inventario.values()).sort((a, b) =>
            String(a.nombre_insumo).localeCompare(String(b.nombre_insumo), "es")
        ).forEach(function (insumo) {
            $("<option>", {
                value: insumo.id_insumo,
                text: insumo.nombre_insumo +
                    (insumo.estado_insumo === "INACTIVO" ? " (inactivo)" : "")
            }).appendTo(selector);
        });

        selector.val(seleccionado || "");
        actualizarDatosInsumo();
    }

    function renderizarInsumos() {
        const cuerpo = $("#tbodyInsumosSeleccionados").empty();

        if (!insumosSeleccionados.length) {
            $("<tr>").append($("<td>", {
                colspan: 4,
                class: "text-center",
                text: "No hay insumos agregados."
            })).appendTo(cuerpo);

            return;
        }

        insumosSeleccionados.forEach(function (seleccionado) {
            const insumo = inventario.get(Number(seleccionado.id_insumo));
            const fila = $("<tr>");

            $("<td>")
                .text(insumo?.nombre_insumo || "Insumo no disponible")
                .appendTo(fila);

            $("<td>").text(seleccionado.cantidad).appendTo(fila);
            $("<td>").text(insumo?.presentacion || "—").appendTo(fila);

            $("<td>", { class: "text-center" }).append(
                $("<button>", {
                    type: "button",
                    class: "btn-action btn-delete btn-eliminar-insumo",
                    "data-id-insumo": seleccionado.id_insumo,
                    "aria-label": "Quitar insumo",
                    title: "Quitar insumo"
                }).append($("<i>", {
                    class: "bi bi-trash",
                    "aria-hidden": "true"
                }))
            ).appendTo(fila);

            fila.appendTo(cuerpo);
        });
    }

    $("#insumoUtilizado").on("change", actualizarDatosInsumo);

    $("#btnAgregarInsumo").on("click", async function () {
        if (estaOcupado() || !atencion || atencionCerrada() || requiereVerificacion) return;

        const v = await obtenerValidaciones();

        if (!v || estaOcupado() || !atencion || atencionCerrada() || requiereVerificacion) return;

        const resultado = v.validarFormulario(formularioInsumos[0], reglasAgregarInsumo);

        if (!resultado.valido) {
            resultado.primerCampo?.focus();
            return;
        }

        const idInsumo = Number($("#insumoUtilizado").val());
        const cantidad = $("#cantidadInsumo").val();
        const insumo = inventario.get(idInsumo);

        if (insumosSeleccionados.some(fila => fila.id_insumo === idInsumo)) {
            mostrarMensaje(
                "Ese insumo ya está agregado. Quítelo de la lista para cambiar su cantidad.",
                "warning"
            );

            return;
        }

        if (Number(cantidad) > cantidadDisponible(insumo)) {
            const aviso = "La cantidad indicada supera la cantidad disponible para esta atención.";

            v.mostrarErrores(formularioInsumos[0], {
                cantidad_utilizada: aviso
            });

            mostrarMensaje(aviso);
            $("#cantidadInsumo").trigger("focus");

            return;
        }

        insumosSeleccionados.push({
            id_insumo: idInsumo,
            cantidad: Number(cantidad)
        });

        $("#insumoUtilizado, #cantidadInsumo").val("");

        limpiarValidacionesFormulario(formularioInsumos);
        actualizarDatosInsumo();
        renderizarInsumos();
        limpiarMensaje();
    });

    $("#tbodyInsumosSeleccionados").on("click", ".btn-eliminar-insumo", function () {
        if (estaOcupado() || atencionCerrada() || requiereVerificacion) return;

        const idInsumo = Number($(this).attr("data-id-insumo"));

        const anterior = insumosSeleccionados.find(
            insumo => insumo.id_insumo === idInsumo
        );

        /* Retirar la fila del borrador sin modificar todavía el inventario */
        insumosSeleccionados = insumosSeleccionados.filter(
            insumo => insumo.id_insumo !== idInsumo
        );

        $("#insumoUtilizado").val(idInsumo).trigger("change");

        $("#cantidadInsumo").val(anterior?.cantidad || "");

        mostrarErrorInsumos("");
        renderizarInsumos();
    });


    /* ================================================= */
    /* HISTORIAL Y DETALLES */
    /* ================================================= */

    function imagenServicio(idFoto, nombre, miniatura = false) {
        const imagen = $("<img>", {
            src: urlFotografia(idFoto),
            alt: nombre || "Resultado del servicio",
            class: "rounded object-fit-cover",
            loading: "lazy"
        }).on("error", function () {
            $(this).replaceWith($("<span>", {
                text: "Imagen no disponible"
            }));
        });

        if (miniatura) imagen.attr({ width: 96, height: 72 });

        return imagen;
    }

    function renderizarHistorial() {
        const cuerpo = $("#tbodyHistorialServicios").empty();

        historial.forEach(function (servicio, indice) {
            const fila = $("<tr>");
            const foto = $("<td>");

            $("<td>").text(formatearFecha(servicio.fecha)).appendTo(fila);

            foto.append(
                enteroPositivo(servicio.id_foto)
                    ? imagenServicio(servicio.id_foto, servicio.nombre_servicio, true)
                    : $("<span>", { text: "Sin fotografía" })
            ).appendTo(fila);

            $("<td>").text(servicio.nombre_servicio).appendTo(fila);

            $("<td>")
                .text(formatearMonto(servicio.precio_aplicado))
                .appendTo(fila);

            $("<td>")
                .text(servicio.nota_tecnica || "Sin nota técnica")
                .appendTo(fila);

            $("<td>", { class: "text-center" }).append(
                $("<button>", {
                    type: "button",
                    class: "btn-templo-secondary btn-detalle-historial-servicio",
                    "data-indice": indice,
                    text: "Ver detalles"
                })
            ).appendTo(fila);

            fila.appendTo(cuerpo);
        });

        if (!historial.length) {
            $("<tr>").append($("<td>", {
                colspan: 6,
                class: "text-center",
                text: "No hay servicios registrados."
            })).appendTo(cuerpo);
        }

        filtrarHistorial();
    }

    function filtrarHistorial() {
        const filtro = String($("#buscarHistorialServicios").val() || "")
            .trim()
            .toLocaleLowerCase("es");

        $("#tbodyHistorialServicios tr").each(function () {
            const fila = $(this);

            fila.toggle(
                Boolean(fila.find("td[colspan]").length) ||
                fila.text().toLocaleLowerCase("es").includes(filtro)
            );
        });
    }

    function mostrarDetalle(servicio) {
        const modal = $("#modalDetalleServicio");

        if (!modal.length || !window.bootstrap?.Modal) {
            mostrarMensaje("No fue posible abrir los detalles del servicio.");
            return;
        }

        $("#detalleServicioTitulo")
            .text(servicio.nombre_servicio || "Resultado del servicio");

        $("#detalleServicioFecha").text(formatearFecha(servicio.fecha));
        $("#detalleServicioEspecialista").text(servicio.especialista || "—");

        $("#detalleServicioPrecio").text(formatearMonto(servicio.precio_aplicado));
        $("#detalleServicioNota").text(servicio.nota_tecnica || "Sin nota técnica");

        const tieneFoto = enteroPositivo(servicio.id_foto);

        $("#detalleServicioImagen")
            .toggleClass("d-none", !tieneFoto)
            .attr("src", tieneFoto ? urlFotografia(servicio.id_foto) : null);

        $("#detalleServicioSinImagen").toggleClass("d-none", tieneFoto);

        window.bootstrap.Modal.getOrCreateInstance(modal[0]).show();
    }

    $("#buscarHistorialServicios").on("input", filtrarHistorial);

    $("#tbodyHistorialServicios").on(
        "click",
        ".btn-detalle-historial-servicio",
        function () {
            const servicio = historial[Number($(this).attr("data-indice"))];

            if (servicio) mostrarDetalle(servicio);
        }
    );

    $("#detalleServicioImagen").on("error", function () {
        $(this).addClass("d-none").removeAttr("src");
        $("#detalleServicioSinImagen").removeClass("d-none");
    });


    /* ================================================= */
    /* FICHA Y PANELES DE ATENCIÓN */
    /* ================================================= */

    function mostrarPanelRegistro() {
        $("#panelAgendamientosServicio, #btnRefrescarRegistroServicio")
            .addClass("d-none");

        $("#panelRegistroServicio, #btnVolverAgendamientos, #btnActualizarAtencionServicio")
            .removeClass("d-none");
    }

    function mostrarPanelListado() {
        limpiarValidacionesFormulario(formularioServicio);
        limpiarValidacionesFormulario(formularioInsumos);

        liberarVistaPrevia();
        campoFoto.val("");

        atencion = null;
        detalleSeleccionado = "";
        firmaServicioInicial = "";
        insumosSeleccionados = [];
        insumosGuardados = new Map();
        historial = [];
        requiereVerificacion = false;

        $("#panelRegistroServicio, #btnVolverAgendamientos, #btnActualizarAtencionServicio")
            .addClass("d-none");

        $("#panelAgendamientosServicio, #btnRefrescarRegistroServicio")
            .removeClass("d-none");

        if (tabla) tabla.columns.adjust();
    }

    function aplicarAtencion(
        respuesta,
        conservarServicio = false,
        conservarInsumos = false
    ) {
        const datos = respuesta.atencion;

        if (!$.isPlainObject(datos) || !enteroPositivo(datos.id_agendamiento)) {
            throw new Error("No fue posible comprobar la atención recibida.");
        }

        comprobarLista(datos.servicios);
        comprobarLista(datos.insumos);
        comprobarLista(datos.historial);
        comprobarLista(respuesta.insumos);

        atencion = datos;
        historial = datos.historial;
        requiereVerificacion = false;

        $("#idAgendamientoServicio, #servicioIdAgendamiento, #insumosIdAgendamiento")
            .val(datos.id_agendamiento);

        $("#fechaServicio").val(datos.fecha).prop("readOnly", true);

        /* Llenar la ficha de la clienta y los datos de la atención */
        $("#fichaNombreClienta").text(datos.clienta || "Sin información");
        $("#fichaAlergias").text(datos.alergias || "Sin alergias registradas");

        $("#fichaServiciosAgendados")
            .text(datos.servicios.map(servicio => servicio.nombre_servicio).join(", ") || "—");

        $("#fichaMontoAgendamiento").text(formatearMonto(datos.monto_total));
        $("#fichaEspecialistaAsignada").text(datos.especialista || "—");

        $("#fichaUltimoServicio")
            .text(historial[0]?.nombre_servicio || "Sin atención anterior");

        $("#fichaUltimaAtencion").text(formatearFecha(historial[0]?.fecha));

        $("#fichaNotaTecnicaAnterior")
            .text(historial[0]?.nota_tecnica || "Sin nota técnica anterior");

        selectorServicio.empty().append($("<option>", {
            value: "",
            text: "Seleccionar servicio agendado..."
        }));

        datos.servicios.forEach(function (servicio) {
            $("<option>", {
                value: servicio.id_detalle_agendamiento,
                text: servicio.nombre_servicio +
                    (servicio.precio_aplicado !== null ? " — Guardado" : "")
            }).appendTo(selectorServicio);
        });

        /* Actualizar las existencias conservando el borrador cuando corresponda */
        prepararInventario(respuesta.insumos, datos.insumos);

        if (!conservarInsumos) {
            /* Proponer el conjunto requerido sin registrarlo como consumo hasta guardar */
            const insumosIniciales = !datos.insumos.length && !atencionCerrada()
                ? (datos.insumos_requeridos || []) : datos.insumos;

            insumosSeleccionados = insumosIniciales.map(insumo => ({
                id_insumo: Number(insumo.id_insumo),
                cantidad: Number(insumo.cantidad)
            }));
        }

        /* Mantener el servicio seleccionado si todavía pertenece a la atención */
        const existeDetalle = datos.servicios.some(servicio =>
            String(servicio.id_detalle_agendamiento) === detalleSeleccionado
        );

        if (conservarServicio && existeDetalle) {
            selectorServicio.val(detalleSeleccionado);
            firmaServicioInicial = firmaDetalle(obtenerDetalle());

        } else {
            cargarServicio(
                existeDetalle
                    ? detalleSeleccionado
                    : datos.servicios[0]?.id_detalle_agendamiento
            );
        }

        renderizarInsumos();
        renderizarHistorial();
        mostrarPanelRegistro();
        actualizarPermisos();
    }

    async function actualizarAtencion(
        conservarServicio = false,
        conservarInsumos = false
    ) {
        const respuesta = await solicitar({
            accion: "buscar",
            id_agendamiento: atencion.id_agendamiento
        });

        aplicarAtencion(respuesta, conservarServicio, conservarInsumos);
    }

    tablaElemento.on("click", ".btn-registrar-servicio", async function () {
        if (estaOcupado()) return;

        const idAgendamiento = $(this).attr("data-id-agendamiento");

        cargando = true;

        const restaurar = bloquearControles($(this), "Cargando...");

        try {
            /* Recuperar el estado vigente antes de decidir si corresponde iniciar */
            let respuesta = await solicitar({
                accion: "buscar",
                id_agendamiento: idAgendamiento
            });

            const datosAtencion = respuesta.atencion;

            if (
                !$.isPlainObject(datosAtencion) ||
                String(datosAtencion.id_agendamiento) !== String(idAgendamiento) ||
                !["EN ESPERA", "DEMORADA", "EN ATENCIÓN"].includes(datosAtencion.estado)
            ) {
                throw new Error("La cita ya no está disponible para atender. Pulse Refrescar para actualizar la lista.");
            }

            /* Continuar una atención abierta no vuelve a solicitar su inicio */
            if (datosAtencion.estado !== "EN ATENCIÓN") {
                await solicitar({
                    accion: "iniciarAtencion",
                    id_agendamiento: idAgendamiento
                }, {
                    escritura: true
                });

                respuesta = await solicitar({
                    accion: "buscar",
                    id_agendamiento: idAgendamiento
                });
            }

            detalleSeleccionado = "";

            $("#buscarHistorialServicios").val("");

            aplicarAtencion(respuesta);
            limpiarMensaje();

            if (window.bootstrap?.Tab) {
                window.bootstrap.Tab.getOrCreateInstance(
                    $("#servicio-realizado-tab")[0]
                ).show();
            }

            if (atencionCerrada()) {
                mostrarMensaje(
                    "Esta atención ya fue cerrada. Puede consultar sus datos.",
                    "info"
                );
            }

        } catch (error) {
            mostrarMensaje(error.message, error.ambiguo ? "warning" : "danger", true);

        } finally {
            cargando = false;
            restaurar();
        }
    });

    /* Confirmar el descarte de cambios al seleccionar otro servicio */
    selectorServicio.on("change", async function () {
        const nuevoDetalle = $(this).val();

        if (
            servicioModificado() &&
            !await confirmarAccion(
                "Hay cambios del servicio sin guardar. ¿Desea descartarlos y cambiar de servicio?"
            )
        ) {
            selectorServicio.val(detalleSeleccionado);
            return;
        }

        cargarServicio(nuevoDetalle);
    });

    /* Volver al listado comprobando los borradores pendientes */
    $("#btnVolverAgendamientos, #btnCancelarServicio").on("click", async function () {
        if (estaOcupado()) return;

        if (
            (servicioModificado() || insumosModificados()) &&
            !await confirmarAccion(
                "Hay cambios sin guardar. ¿Desea descartarlos y volver a la lista?"
            )
        ) return;

        mostrarPanelListado();

        await cargarListado();
    });

    $("#btnRefrescarRegistroServicio").on("click", cargarListado);

    /* Actualizar la atención conservando los valores escritos */
    $("#btnActualizarAtencionServicio").on("click", async function () {
        if (estaOcupado() || !atencion) return;

        cargando = true;

        const restaurar = bloquearControles($(this), "Actualizando...");

        try {
            await actualizarAtencion(true, true);

            limpiarMensaje();

        } catch (error) {
            mostrarMensaje(error.message, "danger", true);

        } finally {
            cargando = false;
            restaurar();
        }
    });


    /* ================================================= */
    /* GUARDAR SERVICIO E INSUMOS */
    /* ================================================= */

    async function guardarOperacion(accion, boton) {
        if (estaOcupado() || !atencion || atencionCerrada() || requiereVerificacion) return;

        const v = await obtenerValidaciones();

        if (!v || estaOcupado() || !atencion || atencionCerrada() || requiereVerificacion) return;

        const esServicio = accion === "guardarServicio";

        let datos;
        let fotografia = null;

        if (esServicio) {
            const resultado = v.validarFormulario(formularioServicio[0], reglasServicio);
            const catalogoValido = validarCatalogo();

            if (!resultado.valido || !catalogoValido) {
                if (resultado.primerCampo && resultado.primerCampo.type !== "hidden") {
                    resultado.primerCampo.focus();

                } else if (!catalogoValido) {
                    campoDestacada.trigger("focus");

                } else {
                    mostrarMensaje("Seleccione una atención y un servicio válidos antes de guardar.");
                }

                return;
            }

            const precio = String(campoPrecio.val() || "").trim();

            datos = {
                ...datosServicioActual(),
                precio_aplicado: precio
            };

            fotografia = campoFoto.prop("files")?.[0] || null;

        } else {
            if (!validarListaInsumos(v)) return;

            /* Enviar la lista completa de consumos de esta atención */
            datos = {
                insumos: insumosSeleccionados.map(insumo => ({ ...insumo }))
            };
        }

        guardando = true;

        const restaurar = bloquearControles(boton, "Guardando...");

        let guardado = false;
        let campoConError = null;

        try {
            const respuesta = await solicitar({
                ...datos,
                accion,
                id_agendamiento: atencion.id_agendamiento
            }, {
                fotografia,
                escritura: true
            });

            guardado = true;

            /* Retirar el archivo temporal después de confirmar su guardado */
            if (esServicio) {
                campoFoto.val("");
                liberarVistaPrevia();
                mostrarFotografia();
            }

            /* Refrescar los datos conservando el borrador de la otra pestaña */
            await actualizarAtencion(!esServicio, esServicio);

            mostrarMensaje(
                respuesta.mensaje || "Los cambios fueron guardados correctamente.",
                "success"
            );

        } catch (error) {
            /* Exigir una consulta cuando el resultado deba comprobarse */
            requiereVerificacion = guardado || error.ambiguo === true;

            mostrarMensaje(
                guardado
                    ? "Los cambios fueron guardados, pero no se pudo actualizar la ficha. Pulse Actualizar atención antes de continuar."
                    : error.message,
                requiereVerificacion ? "warning" : "danger"
            );

            if (!guardado && !error.ambiguo) {
                campoConError = mostrarErroresServidor(error.respuesta, esServicio);
            }

            if (!campoConError && !redirigiendo) {
                window.Alertas?.notificar(
                    guardado ? "Los cambios fueron guardados, pero no se pudo actualizar la ficha. Actualice la atención antes de continuar." : error.message,
                    requiereVerificacion ? "warning" : "error"
                );
            }


        } finally {
            guardando = false;
            restaurar();

            if (campoConError && !redirigiendo) campoConError.focus();
        }
    }

    formularioServicio.on("submit", async function (evento) {
        evento.preventDefault();
        await guardarOperacion("guardarServicio", $("#btnGuardarServicio"));
    });

    formularioInsumos.on("submit", async function (evento) {
        evento.preventDefault();
        await guardarOperacion("guardarInsumos", $("#btnGuardarInsumos"));
    });

    $("#btnGuardarServicio").on("click", function () {
        formularioServicio.trigger("submit");
    });

    $("#btnGuardarInsumos").on("click", function () {
        formularioInsumos.trigger("submit");
    });


    /* ================================================= */
    /* FINALIZAR LA ATENCIÓN */
    /* ================================================= */

    $("#btnFinalizarServicio").on("click", async function () {
        if (estaOcupado() || !atencion || atencionCerrada() || requiereVerificacion) return;

        if (servicioModificado() || insumosModificados()) {
            mostrarMensaje(
                "Guarde los cambios del servicio y de los insumos antes de finalizar.",
                "warning"
            );

            return;
        }

        /* Comprobar que todos los servicios tengan su precio aplicado */
        if (
            !atencion.servicios.length ||
            atencion.servicios.some(servicio => servicio.precio_aplicado === null)
        ) {
            mostrarMensaje(
                "Guarde cada servicio realizado antes de finalizar la atención.",
                "warning"
            );

            return;
        }

        if (!await confirmarAccion(
            "¿Desea finalizar esta atención? Quedará registrada como REALIZADA."
        )) return;

        guardando = true;

        const restaurar = bloquearControles($(this), "Finalizando...");

        let finalizada = false;

        try {
            const respuesta = await solicitar({
                accion: "finalizarRegistro",
                id_agendamiento: atencion.id_agendamiento
            }, {
                escritura: true
            });

            finalizada = true;

            mostrarPanelListado();

            await actualizarListado();

            mostrarMensaje(
                respuesta.mensaje || "La atención fue finalizada correctamente.",
                "success"
            );

        } catch (error) {
            /* Distinguir el cierre confirmado de un fallo al refrescar la lista */
            requiereVerificacion = !finalizada && error.ambiguo === true;

            mostrarMensaje(
                finalizada
                    ? "La atención fue finalizada, pero no se pudo actualizar la lista. Pulse Refrescar para consultarla."
                    : error.message,
                finalizada || requiereVerificacion ? "warning" : "danger",
                true
            );

        } finally {
            guardando = false;
            restaurar();
        }
    });


    /* ================================================= */
    /* CATÁLOGO DE TRABAJOS DESTACADOS */
    /* ================================================= */

    function filtrarCatalogo() {
        const filtro = String($("#filtroCatalogoServicio").val() || "")
            .toLocaleLowerCase("es");

        let visibles = 0;

        $("#catalogoTrabajosGrid .catalogo-item").each(function () {
            const trabajo = $(this);

            const coincide = String(trabajo.attr("data-servicio") || "")
                .toLocaleLowerCase("es")
                .includes(filtro);

            trabajo.toggleClass("d-none", !coincide);

            if (coincide) visibles++;
        });

        $("#catalogoTrabajosVacio").toggleClass("d-none", visibles > 0);
    }

    $("#modalCatalogoTrabajos").on("show.bs.modal", async function () {
        if (cargandoCatalogo) return;

        cargandoCatalogo = true;

        const aviso = $("#mensajeCatalogoServicio")
            .text("Cargando trabajos...")
            .prop("hidden", false);

        try {
            const respuesta = await solicitar({ accion: "catalogo" });
            const nuevosTrabajos = comprobarLista(respuesta.catalogo);

            /* Preparar las tarjetas antes de sustituir el catálogo visible */
            const tarjetas = nuevosTrabajos.map(function (trabajo, indice) {
                const columna = $("<div>", {
                    class: "col-md-6 col-lg-4 catalogo-item",
                    "data-servicio": trabajo.nombre_servicio
                });

                const tarjeta = $("<div>", {
                    class: "registro-catalogo-card"
                });

                const boton = $("<button>", {
                    type: "button",
                    class: "btn btn-link p-0 w-100 btn-foto-catalogo",
                    "data-indice": indice,
                    "aria-label": "Ver fotografía de " + trabajo.nombre_servicio
                }).append(imagenServicio(trabajo.id_foto, trabajo.nombre_servicio));

                const cuerpo = $("<div>", {
                    class: "registro-catalogo-card-body"
                }).append(
                    $("<h6>").text(trabajo.nombre_servicio),
                    $("<small>").text(formatearFecha(trabajo.fecha))
                );

                return columna.append(tarjeta.append(boton, cuerpo))[0];
            });

            trabajos = nuevosTrabajos;

            $("#catalogoTrabajosGrid .catalogo-item").remove();
            $("#catalogoTrabajosGrid").append(tarjetas);

            filtrarCatalogo();

            aviso.empty().prop("hidden", true);

        } catch (error) {
            aviso.text(error.message).prop("hidden", false);

        } finally {
            cargandoCatalogo = false;
        }
    });

    $("#filtroCatalogoServicio").on("change", filtrarCatalogo);

    /* Cerrar el catálogo antes de ampliar una fotografía */
    $("#catalogoTrabajosGrid").on("click", ".btn-foto-catalogo", function () {
        const trabajo = trabajos[Number($(this).attr("data-indice"))];
        const modalCatalogo = $("#modalCatalogoTrabajos");

        if (!trabajo || !window.bootstrap?.Modal) return;

        modalCatalogo.one("hidden.bs.modal", function () {
            mostrarDetalle(trabajo);
        });

        window.bootstrap.Modal.getInstance(modalCatalogo[0])?.hide();
    });


    /* ================================================= */
    /* IMPRESIÓN DEL HISTORIAL */
    /* ================================================= */

    function esperarImagen(imagen) {
        return new Promise(function (resolve) {
            const elemento = $(imagen);

            if (elemento.prop("complete")) {
                resolve();
                return;
            }

            const temporizador = window.setTimeout(terminar, 8000);

            function terminar() {
                window.clearTimeout(temporizador);
                elemento.off(".impresionServicio");
                resolve();
            }

            elemento.one("load.impresionServicio error.impresionServicio", terminar);

            if (elemento.prop("complete")) terminar();
        });
    }

    $("#btnImprimirHistorial").on("click", async function () {
        if (estaOcupado() || !atencion || !historial.length) return;

        imprimiendo = true;

        const boton = $(this);

        boton.prop("disabled", true);

        try {
            /* Recuperar la plantilla y el marco definidos en la vista */
            const plantilla = $("#plantillaImpresionHistorialServicio");
            const marco = $("#marcoImpresionHistorialServicio");

            if (!plantilla.length || !marco.length) {
                throw new Error("No fue posible preparar la impresión del historial.");
            }

            const documento = marco.contents();

            documento.find("body")
                .empty()
                .append($(plantilla[0].content).clone());

            documento.find("[data-impresion-clienta]")
                .text(atencion.clienta || "Sin información");

            const filas = $("#tbodyHistorialServicios tr").clone();

            filas.find("td:last-child").remove();
            filas.show();
            filas.find("img").removeAttr("loading");

            documento.find("[data-impresion-filas]").empty().append(filas);

            /* Esperar las fotografías antes de abrir el diálogo de impresión */
            await Promise.all(
                documento.find("img").map(function () {
                    return esperarImagen(this);
                }).get()
            );

            const ventana = marco.prop("contentWindow");

            ventana.focus();
            ventana.print();

        } catch (error) {
            mostrarMensaje(error.message, "danger", true);

        } finally {
            imprimiendo = false;
            actualizarPermisos();
        }
    });


    /* ================================================= */
    /* INICIALIZACIÓN Y SALIDA DE LA PÁGINA */
    /* ================================================= */

    /* Advertir cuando se intente salir con cambios pendientes */
    $(window).on("beforeunload", function (evento) {
        if (
            redirigiendo ||
            (!guardando && !servicioModificado() && !insumosModificados())
        ) return;

        evento.preventDefault();

        if (evento.originalEvent) {
            evento.originalEvent.returnValue = "";
        }
    });

    $(window).on("pagehide", liberarVistaPrevia);

    /* Comprobar los datos al recuperar una página desde la caché del navegador */
    $(window).on("pageshow", function (evento) {
        if (!evento.originalEvent?.persisted) return;

        cargando = false;
        guardando = false;
        preparando = false;
        redirigiendo = false;
        requiereVerificacion = Boolean(atencion);

        modulo.find(":input").prop("disabled", false);

        actualizarPermisos();

        if (atencion) {
            campoFoto.val("");
            mostrarFotografia();

            mostrarMensaje("Actualice la atención antes de continuar.", "info");
        }
    });

    renderizarInsumos();
    actualizarPermisos();
    cargarListado();
});