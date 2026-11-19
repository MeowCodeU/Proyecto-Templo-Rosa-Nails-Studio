/* ===================================================== */
/* PANEL DE INICIO */
/* ===================================================== */

$(async function () {
    "use strict";

    const modulo = $(".inicio-module");

    if (!modulo.length) return;

    const mensaje = $("#mensajeInicio");
    const ruta = modulo.attr("data-ruta") || "Index.php?url=inicio";

    let validaciones;

    /* Cargar las comprobaciones compartidas antes de consultar el panel */
    try {
        const moduloValidaciones = await import("./validaciones/validaciones.js");
        validaciones = moduloValidaciones.Validaciones;

    } catch (error) {
        const aviso = "No fue posible cargar las validaciones de Inicio. Recargue la página.";

        mensaje
            .removeClass("d-none alert-danger alert-warning alert-info alert-success")
            .addClass("alert-danger")
            .attr("role", "alert")
            .prop("hidden", false)
            .text(aviso);

        modulo.attr("aria-busy", "false");

        $("#btnMesAnterior, #btnMesSiguiente, #btnMesActual, #btnActualizarInicio")
            .prop("disabled", true);

        window.Alertas?.notificar(aviso, "error");

        return;
    }

    const meses = [
        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];

    let datosActuales = null;
    let mesMostrado = null;
    let mesSolicitado = null;
    let seguirMesActual = true;
    let solicitudActiva = null;
    let secuencia = 0;
    let cargando = false;
    let redirigiendo = false;

    const camposResumen = {
        resumenAgendamientosHoy: "agendamientos_hoy",
        resumenServiciosHoy: "servicios_hoy",
        resumenClientasRegistradas: "clientas_registradas",
        resumenStockBajo: "insumos_stock_bajo",
        inicioEnAtencion: "en_atencion",
        inicioEnEspera: "en_espera",
        inicioFinalizadosHoy: "finalizados_hoy"
    };

    const camposMensuales = {
        inicioServiciosMes: "servicios_realizados",
        inicioClientasMes: "clientas_nuevas",
        inicioClientasAtendidasMes: "clientas_atendidas",
        inicioAgendamientosMes: "agendamientos_atendidos"
    };


    /* ================================================= */
    /* FORMATOS Y COMPROBACIONES */
    /* ================================================= */

    function formatearCantidad(valor) {
        return Number(valor).toLocaleString("es-VE");
    }

    function nombreMes(mes) {
        const [anio, numero] = mes.split("-").map(Number);

        return meses[numero - 1] + " " + anio;
    }

    function desplazarMes(mes, desplazamiento) {
        if (!validaciones.esMes(mes)) return null;

        const [anio, numero] = mes.split("-").map(Number);
        const indice = anio * 12 + numero - 1 + desplazamiento;
        const nuevoAnio = Math.floor(indice / 12);
        const nuevoMes = indice % 12 + 1;

        if (nuevoAnio < 1000 || nuevoAnio > 9999) return null;

        return nuevoAnio + "-" + String(nuevoMes).padStart(2, "0");
    }

    /* Comprobar toda la respuesta antes de sustituir los datos visibles */
    function comprobarDatos(datos, mesEsperado) {
        if (
            !$.isPlainObject(datos) ||
            !validaciones.esFecha(datos.fecha_actual) ||
            !validaciones.esMes(datos.mes_actual) ||
            !validaciones.esMes(datos.mes_consultado) ||
            (mesEsperado && datos.mes_consultado !== mesEsperado) ||
            !$.isPlainObject(datos.resumen) ||
            !$.isPlainObject(datos.resumen_mensual)
        ) {
            throw new Error(
                "Los datos recibidos de Inicio no tienen un formato válido."
            );
        }

        Object.values(camposResumen).forEach(function (clave) {
            if (!validaciones.esEntero(datos.resumen[clave])) {
                throw new Error(
                    "No fue posible comprobar los indicadores del día."
                );
            }
        });

        Object.values(camposMensuales).forEach(function (clave) {
            if (!validaciones.esEntero(datos.resumen_mensual[clave])) {
                throw new Error(
                    "No fue posible comprobar el resumen mensual."
                );
            }
        });

        /* Comprobar el contador personal cuando la vista es de especialista */
        if (
            $("#cantidadCitasEspecialistaHoy").length &&
            !validaciones.esEntero(datos.agendamientos_especialista_hoy)
        ) {
            throw new Error(
                "No fue posible comprobar los agendamientos de la especialista."
            );
        }

        [
            "agendamientos_hoy",
            "alertas_stock",
            "servicios_solicitados",
            "calendario"
        ].forEach(function (clave) {
            if (
                !Array.isArray(datos[clave]) ||
                !datos[clave].every($.isPlainObject)
            ) {
                throw new Error(
                    "Una de las listas recibidas no tiene un formato válido."
                );
            }
        });

        datos.agendamientos_hoy.forEach(function (cita) {
            if (
                !validaciones.esEntero(cita.id_agendamiento, 1) ||
                typeof cita.estado !== "string"
            ) {
                throw new Error(
                    "No fue posible comprobar los agendamientos recibidos."
                );
            }
        });

        datos.alertas_stock.forEach(function (insumo) {
            if (
                !validaciones.esEntero(insumo.id_insumo, 1) ||
                !validaciones.esEntero(insumo.stock_actual) ||
                !validaciones.esEntero(insumo.stock_minimo)
            ) {
                throw new Error(
                    "No fue posible comprobar las existencias recibidas."
                );
            }
        });

        datos.servicios_solicitados.forEach(function (servicio) {
            if (
                !validaciones.esEntero(servicio.id_servicio, 1) ||
                !validaciones.esEntero(servicio.cantidad, 1)
            ) {
                throw new Error(
                    "No fue posible comprobar los servicios solicitados."
                );
            }
        });

        datos.calendario.forEach(function (dia) {
            if (
                !validaciones.esFecha(dia.fecha) ||
                !dia.fecha.startsWith(datos.mes_consultado + "-") ||
                !validaciones.esEntero(dia.cantidad)
            ) {
                throw new Error(
                    "No fue posible comprobar los datos del calendario."
                );
            }
        });
    }


    /* ================================================= */
    /* MENSAJES Y CONTROLES */
    /* ================================================= */

    function mostrarMensaje(texto, tipo = "danger", notificar = false) {
        if (notificar && !redirigiendo) window.Alertas?.notificar(texto, tipo);

        mensaje
            .removeClass("d-none alert-danger alert-warning alert-info alert-success")
            .addClass("alert-" + tipo)
            .attr("role", tipo === "danger" || tipo === "warning" ? "alert" : "status")
            .prop("hidden", false)
            .text(texto);
    }

    function limpiarMensaje() {
        mensaje.empty().addClass("d-none").prop("hidden", true);
    }

    function actualizarControles() {
        const referencia = mesSolicitado || mesMostrado;
        const bloquear = redirigiendo || !referencia;

        $("#btnMesAnterior")
            .prop("disabled", bloquear || !desplazarMes(referencia, -1));

        $("#btnMesSiguiente")
            .prop("disabled", bloquear || !desplazarMes(referencia, 1));

        $("#btnMesActual").prop("disabled", redirigiendo || !datosActuales);
        $("#btnActualizarInicio").prop("disabled", cargando || redirigiendo);

        modulo.attr("aria-busy", cargando ? "true" : "false");
    }

    function crearVacio(icono, texto) {
        return $("<div>", {
            class: "inicio-vacio",
            role: "listitem"
        }).append(
            $("<i>", {
                class: "bi " + icono,
                "aria-hidden": "true"
            }),
            $("<p>").text(texto)
        );
    }


    /* ================================================= */
    /* CONSULTAR LOS DATOS DE INICIO */
    /* ================================================= */

    function solicitarInicio(mes) {
        let xhr;

        const promesa = new Promise(function (resolve, reject) {
            const datos = { accion: "consultar" };

            if (mes) datos.mes = mes;

            xhr = $.ajax({
                url: ruta,
                method: "POST",
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                processData: false,
                data: JSON.stringify(datos),
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                },
                timeout: 30000

            }).done(function (respuesta) {
                if ($.isPlainObject(respuesta) && respuesta.ok === true) {
                    resolve(respuesta.inicio);
                    return;
                }

                reject(new Error(
                    typeof respuesta?.mensaje === "string"
                        ? respuesta.mensaje
                        : "No fue posible confirmar los datos de Inicio."
                ));

            }).fail(function (solicitud, estado) {
                if (solicitud.status === 401) {
                    redirigiendo = true;
                    window.location.assign("Index.php?url=login&sesion=expirada");
                }

                const error = new Error(
                    typeof solicitud.responseJSON?.mensaje === "string"
                        ? solicitud.responseJSON.mensaje
                        : estado === "timeout"
                            ? "La consulta tardó demasiado. Intente actualizar nuevamente."
                            : "No fue posible consultar los datos de Inicio."
                );

                error.cancelado = estado === "abort";

                reject(error);
            });
        });

        /* Cancelar una lectura que fue sustituida por otra más reciente */
        promesa.cancelar = function () {
            if (xhr && xhr.readyState !== 4) xhr.abort();
        };

        return promesa;
    }


    /* ================================================= */
    /* AGENDAMIENTOS DEL DÍA */
    /* ================================================= */

    function crearEstado(nombre) {
        const clave = String(nombre || "").trim().toUpperCase();

        const clases = {
            AGENDADA: "estado-programada",
            CONFIRMADA: "estado-confirmada",
            "EN ESPERA": "estado-en-espera",
            DEMORADA: "estado-demorada",
            "EN ATENCIÓN": "estado-activa",
            REALIZADA: "estado-realizada",
            CANCELADA: "estado-cancelada"
        };

        return $("<span>", {
            class: "badge " + (clases[clave] || "estado-programada"),
            text: nombre || "Sin estado"
        });
    }

    function prepararAgendamientos(citas) {
        if (!citas.length) {
            return crearVacio(
                "bi-calendar2-x",
                "No hay agendamientos para hoy."
            );
        }

        return citas.map(function (cita) {
            const contenido = $("<div>", {
                class: "flex-grow-1 text-break"
            });

            const cabecera = $("<div>", {
                class: "d-flex align-items-center gap-2 flex-wrap mb-2"
            });

            cabecera.append(
                $("<strong>").text(String(cita.hora || "").slice(0, 5)),
                crearEstado(cita.estado)
            );

            contenido.append(
                cabecera,
                $("<p>", { class: "fw-bold mb-1" })
                    .text(cita.clienta || "Sin cliente registrado"),

                $("<p>", { class: "small mb-1" })
                    .text(cita.servicios || "Sin servicios asociados"),

                $("<small>")
                    .text("Especialista: " + (cita.especialista || "Sin información"))
            );

            return $("<article>", {
                class: "d-flex align-items-start gap-3 border rounded-4 p-3",
                role: "listitem"
            }).append(contenido)[0];
        });
    }


    /* ================================================= */
    /* ALERTAS DE INVENTARIO */
    /* ================================================= */

    function prepararStock(insumos) {
        if (!insumos.length) {
            return crearVacio(
                "bi-box2-heart-fill",
                "No hay insumos que requieran reposición."
            );
        }

        return insumos.map(function (insumo) {
            const agotado = Number(insumo.stock_actual) === 0;

            const contenido = $("<div>", {
                class: "flex-grow-1 text-break"
            });

            contenido.append(
                $("<p>", { class: "fw-bold mb-1" })
                    .text(insumo.nombre_insumo || "Insumo sin nombre"),

                $("<p>", { class: "small mb-1" })
                    .text(insumo.presentacion || "Sin presentación"),

                $("<small>").text(
                    "Disponible: " + formatearCantidad(insumo.stock_actual) +
                    " · Mínimo: " + formatearCantidad(insumo.stock_minimo)
                )
            );

            return $("<article>", {
                class: "d-flex align-items-start justify-content-between gap-2 border rounded-4 p-3",
                role: "listitem"
            }).append(
                contenido,
                $("<span>", {
                    class: "badge " + (agotado ? "text-bg-danger" : "text-bg-warning"),
                    text: agotado ? "Agotado" : "Stock bajo"
                })
            )[0];
        });
    }


    /* ================================================= */
    /* SERVICIOS MÁS SOLICITADOS */
    /* ================================================= */

    function prepararServicios(servicios) {
        if (!servicios.length) {
            return crearVacio(
                "bi-clipboard2-heart",
                "No hay solicitudes de servicios para este mes."
            );
        }

        return servicios.map(function (servicio, indice) {
            const cantidad = Number(servicio.cantidad);

            return $("<article>", {
                class: "d-flex align-items-center gap-3 border rounded-4 p-3",
                role: "listitem"
            }).append(
                $("<span>", { class: "badge text-bg-light" })
                    .text(indice + 1),

                $("<strong>", { class: "flex-grow-1 text-break" })
                    .text(servicio.nombre_servicio || "Servicio sin nombre"),

                $("<span>", { class: "badge estado-programada" })
                    .text(
                        formatearCantidad(cantidad) +
                        (cantidad === 1 ? " solicitud" : " solicitudes")
                    )
            )[0];
        });
    }


    /* ================================================= */
    /* MINI CALENDARIO */
    /* ================================================= */

    function prepararCalendario(datos) {
        const [anio, mes] = datos.mes_consultado.split("-").map(Number);
        const primerDia = new Date(Date.UTC(anio, mes - 1, 1)).getUTCDay();
        const diasDelMes = new Date(Date.UTC(anio, mes, 0)).getUTCDate();
        const totalCeldas = primerDia + diasDelMes > 35 ? 42 : 35;

        const cantidades = new Map(
            datos.calendario.map(dia => [dia.fecha, Number(dia.cantidad)])
        );

        const celdas = [];

        /* Usar fechas de calendario independientes de la zona horaria del navegador */
        for (let posicion = 0; posicion < totalCeldas; posicion++) {
            const fecha = new Date(Date.UTC(anio, mes - 1, 1 - primerDia + posicion));
            const anioCelda = fecha.getUTCFullYear();
            const mesCelda = fecha.getUTCMonth() + 1;
            const numeroDia = fecha.getUTCDate();
            const fueraMes = anioCelda !== anio || mesCelda !== mes;

            const clave = String(anioCelda).padStart(4, "0") + "-" +
                String(mesCelda).padStart(2, "0") + "-" +
                String(numeroDia).padStart(2, "0");

            const cantidad = fueraMes ? 0 : cantidades.get(clave) || 0;
            const esHoy = clave === datos.fecha_actual;

            const descripcion = numeroDia + " de " +
                meses[mesCelda - 1].toLowerCase() + " de " + anioCelda +
                (fueraMes
                    ? ", fuera del mes consultado"
                    : ", " + cantidad +
                        (cantidad === 1 ? " agendamiento" : " agendamientos"));

            const celda = $("<div>", {
                class: "inicio-calendario-dia",
                role: "listitem",
                "aria-label": descripcion,
                title: descripcion
            })
                .toggleClass("dia-fuera-mes", fueraMes)
                .toggleClass("dia-hoy", esHoy)
                .toggleClass("dia-con-citas", cantidad > 0);

            if (esHoy) celda.attr("aria-current", "date");

            celda.append($("<span>", {
                class: "inicio-calendario-numero-dia",
                "aria-hidden": "true",
                text: numeroDia
            }));

            if (cantidad > 0) {
                celda.append($("<span>", {
                    class: "inicio-calendario-cantidad",
                    "aria-hidden": "true",
                    text: formatearCantidad(cantidad)
                }));
            }

            celdas.push(celda[0]);
        }

        return celdas;
    }


    /* ================================================= */
    /* ACTUALIZACIÓN DEL PANEL */
    /* ================================================= */

    function aplicarDatos(datos) {

        /* Preparar las listas antes de modificar el contenido del panel */
        const contenido = {
            agendamientos: prepararAgendamientos(datos.agendamientos_hoy),
            stock: prepararStock(datos.alertas_stock),
            servicios: prepararServicios(datos.servicios_solicitados),
            calendario: prepararCalendario(datos)
        };

        $.each(camposResumen, function (id, clave) {
            $("#" + id).text(formatearCantidad(datos.resumen[clave]));
        });

        $.each(camposMensuales, function (id, clave) {
            $("#" + id).text(formatearCantidad(datos.resumen_mensual[clave]));
        });

        /* Mostrar los agendamientos propios solo en la bienvenida de la especialista */
        if ($("#cantidadCitasEspecialistaHoy").length) {
            const cantidadPersonal = Number(datos.agendamientos_especialista_hoy);

            $("#cantidadCitasEspecialistaHoy").text(formatearCantidad(cantidadPersonal));
            $("#textoCitasEspecialistaHoy").text(
                cantidadPersonal === 1 ? "agendamiento" : "agendamientos"
            );
        }

        $("#inicioListaAgendamientos")
            .attr("role", "list")
            .empty()
            .append(contenido.agendamientos);

        $("#inicioListaStock")
            .attr("role", "list")
            .empty()
            .append(contenido.stock);

        $("#inicioServiciosSolicitados")
            .attr("role", "list")
            .empty()
            .append(contenido.servicios);

        $("#miniCalendarioTitulo").text(nombreMes(datos.mes_consultado));

        $("#miniCalendarioDias")
            .attr({
                role: "list",
                "aria-label": nombreMes(datos.mes_consultado)
            })
            .empty()
            .append(contenido.calendario);

        modulo.find("[data-mes-inicio]").text(nombreMes(datos.mes_consultado));
    }

    async function cargarInicio(mes = null, silencioso = false) {
        if (redirigiendo) return;

        /* Ignorar las respuestas de meses que ya fueron sustituidos */
        const turno = ++secuencia;

        solicitudActiva?.cancelar();

        mesSolicitado = mes || datosActuales?.mes_actual || null;
        cargando = true;

        actualizarControles();

        const peticion = solicitarInicio(mes);

        solicitudActiva = peticion;

        try {
            const datos = await peticion;

            if (turno !== secuencia || redirigiendo) return;

            comprobarDatos(datos, mes);
            aplicarDatos(datos);

            datosActuales = datos;
            mesMostrado = datos.mes_consultado;
            mesSolicitado = datos.mes_consultado;

            limpiarMensaje();

        } catch (error) {
            if (turno !== secuencia || error.cancelado || redirigiendo) return;

            /* Conservar la última consulta correcta cuando falle una actualización */
            mostrarMensaje(
                datosActuales
                    ? "No se pudo actualizar el panel. Se conserva la información de la última consulta. " +
                        error.message
                    : error.message,
                datosActuales ? "warning" : "danger",
                !silencioso
            );

        } finally {
            if (turno === secuencia) {
                solicitudActiva = null;
                cargando = false;

                actualizarControles();
            }
        }
    }


    /* ================================================= */
    /* NAVEGACIÓN Y CONSULTAS POSTERIORES */
    /* ================================================= */

    $("#btnMesAnterior, #btnMesSiguiente").on("click", function () {
        const desplazamiento = $(this).attr("id") === "btnMesAnterior" ? -1 : 1;
        const siguiente = desplazarMes(mesSolicitado || mesMostrado, desplazamiento);

        if (!siguiente || redirigiendo) return;

        seguirMesActual = false;

        cargarInicio(siguiente);
    });

    $("#btnMesActual").on("click", function () {
        seguirMesActual = true;

        cargarInicio();
    });

    $("#btnActualizarInicio").on("click", function () {
        if (cargando || redirigiendo) return;

        cargarInicio(
            seguirMesActual ? null : mesSolicitado || mesMostrado
        );
    });

    /* Consultar nuevamente al regresar desde otra pestaña o módulo */
    function actualizarAlVolver() {
        if (
            document.hidden ||
            cargando ||
            redirigiendo ||
            !datosActuales
        ) return;

        cargarInicio(
            seguirMesActual ? null : mesSolicitado || mesMostrado,
            true
        );
    }

    $(window).on("focus", actualizarAlVolver);
    $(document).on("visibilitychange", actualizarAlVolver);

    $(window).on("pagehide", function () {
        secuencia++;

        solicitudActiva?.cancelar();

        solicitudActiva = null;
        cargando = false;
    });

    $(window).on("pageshow", function (evento) {
        if (!evento.originalEvent?.persisted) return;

        redirigiendo = false;

        cargarInicio(
            seguirMesActual ? null : mesSolicitado || mesMostrado,
            true
        );
    });


    /* ================================================= */
    /* CARGA INICIAL */
    /* ================================================= */

    /* Mostrar valores pendientes hasta recibir una consulta válida */
    $.each({ ...camposResumen, ...camposMensuales }, function (id) {
        $("#" + id).text("—");
    });

    $("#cantidadCitasEspecialistaHoy").text("—");

    actualizarControles();
    cargarInicio();
});