/* ===================================================== */
/* GESTIÓN DE REPORTES */
/* ===================================================== */

(function ($) {
    "use strict";

    $(function () {

        /* ================================================= */
        /* CONFIGURACIÓN DEL MÓDULO */
        /* ================================================= */

        const $configuracion = $("#configuracionReportes");

        if (!$configuracion.length) return;

        let config;

        /* Recuperar la configuración proporcionada por la vista */
        try {
            config = JSON.parse($configuracion.text());

        } catch (error) {
            $("#reporteMensaje")
                .text(
                    "No fue posible cargar la configuración de reportes. Actualice la página."
                )
                .addClass("reportes-mensaje-error");

            return;
        }

        const $formulario = $("#formReportes");
        const $tipo = $("#reporteTipo");
        const $consultar = $("#consultarReporte");
        const $pdf = $("#descargarReporte");
        const $categorias = $(".reportes-categoria");

        const filasPorPagina = 10;

        let grupoActual = config.grupo_inicial || "Agenda";
        let consulta = null;
        let tokenPDF = null;
        let pagina = 1;
        let solicitud = null;
        let secuencia = 0;
        let permisoVigente = true;
        let preparando = false;
        let validaciones = null;
        let cargaValidaciones = null;

        $formulario.prop("noValidate", true);


        /* ================================================= */
        /* OPCIONES DE LOS FILTROS */
        /* ================================================= */

        /* Identificar los filtros obligatorios sin modificar sus etiquetas */
        $formulario.find("[data-filtro] label").each(function () {
            if ($(this).find("[data-requerido-filtro]").length) return;

            $(this).append(
                $("<span>").attr({ "data-requerido-filtro": "", "aria-hidden": "true" })
                    .prop("hidden", true).text(" *")
            );
        });

        /* Insertar las opciones como texto para conservar sus caracteres */
        Object.entries(config.opciones).forEach(([campo, opciones]) => {
            const $select = $("#reporte-" + campo);

            $select.empty().append(
                $("<option>").val("").text("Todos")
            );

            opciones.forEach(opcion => {
                $select.append(
                    $("<option>")
                        .val(String(opcion.id))
                        .text(opcion.texto)
                );
            });
        });


        /* ================================================= */
        /* VALIDACIONES DE LOS FILTROS */
        /* ================================================= */

        function cargarValidaciones() {
            if (validaciones) return Promise.resolve(validaciones);

            if (!cargaValidaciones) {
                cargaValidaciones = import("./validaciones/validaciones.js")
                    .then(modulo => {
                        validaciones = modulo.Validaciones;
                        return validaciones;
                    })
                    .catch(error => {
                        cargaValidaciones = null;
                        throw error;
                    });
            }

            return cargaValidaciones;
        }

        function reglasReporte() {
            const reporte = reporteSeleccionado();

            const reglas = {
                reporte: {
                    comprobar: valor => typeof valor === "string" &&
                        Object.prototype.hasOwnProperty.call(config.reportes, valor) &&
                        config.reportes[valor].grupo === grupoActual &&
                        config.reportes[valor].disponible !== false
                        ? null : "Seleccione un tipo de reporte válido."
                }
            };

            if (!reporte || reporte.disponible === false) return reglas;

            /* Comprobar fechas reales y el orden del período */
            if (reporte.fechas) {
                reglas.fecha_inicio = {
                    comprobar: valor => {
                        if (!validaciones.validarFecha(valor)) {
                            return "Indique las dos fechas del período correctamente.";
                        }

                        const fin = $("#reporteFin").val();

                        return validaciones.validarFecha(fin) && valor > fin
                            ? "La fecha inicial no puede ser posterior a la fecha final." : null;
                    }
                };

                reglas.fecha_fin = {
                    comprobar: valor => validaciones.validarFecha(valor)
                        ? null : "Indique las dos fechas del período correctamente."
                };
            }

            /* Utilizar las opciones y los requisitos enviados por el modelo */
            reporte.filtros.forEach(campo => {
                reglas[campo] = {
                    comprobar: valor => {
                        if (typeof valor !== "string") return "Los filtros deben contener valores simples.";

                        if (valor === "") {
                            if (!(reporte.requeridos || []).includes(campo)) return null;

                            return "Seleccione el cliente cuyo historial desea consultar.";
                        }

                        return (config.opciones[campo] || []).some(opcion => String(opcion.id) === valor)
                            ? null : "Uno de los filtros seleccionados ya no está disponible. Actualice la página.";
                    }
                };
            });

            return reglas;
        }

        function limpiarValidaciones() {
            $formulario.find(".templo-validacion-feedback").remove();
            $formulario.find("[aria-invalid]").removeAttr("aria-invalid");
        }

        function comprobarCampo(campo) {
            if (!validaciones || !campo.name || campo.disabled) return;

            const reglas = reglasReporte();

            if (reglas[campo.name]) {
                validaciones.validarFormulario($formulario[0], { [campo.name]: reglas[campo.name] });
            }
        }

        function mostrarErroresReporte(respuesta) {
            if (!validaciones || !respuesta) return;

            const errores = respuesta.errores && typeof respuesta.errores === "object" &&
                !Array.isArray(respuesta.errores) ? respuesta.errores : {};
            const visibles = {};

            /* Asociar los errores únicamente con campos habilitados */
            $formulario.find("[name]:enabled").each(function () {
                if (typeof errores[this.name] === "string") visibles[this.name] = errores[this.name];
            });

            if (!Object.keys(visibles).length && typeof respuesta.campo === "string") {
                const campo = $formulario.find("[name]:enabled").filter(function () {
                    return this.name === respuesta.campo;
                });

                if (campo.length && typeof respuesta.mensaje === "string") {
                    visibles[respuesta.campo] = respuesta.mensaje;
                }
            }

            const primero = validaciones.mostrarErrores($formulario[0], visibles);

            if (primero) primero.focus();
        }


        /* ================================================= */
        /* ESTADO Y MENSAJES DE LA CONSULTA */
        /* ================================================= */

        function reporteSeleccionado() {
            return config.reportes[$tipo.val()] || null;
        }

        function mensaje(texto, esError = false) {
            $("#reporteMensaje")
                .text(texto)
                .toggleClass("reportes-mensaje-error", esError);
        }

        function ocupado(valor) {
            const reporte = reporteSeleccionado();

            const disponible = Boolean(
                reporte && reporte.disponible !== false
            );

            $("#reporteResultados").attr("aria-busy", String(valor));
            $formulario.attr("aria-busy", String(valor));

            $consultar.prop(
                "disabled",
                valor || !disponible || !permisoVigente
            );

            $pdf.prop(
                "disabled",
                valor || !tokenPDF || !permisoVigente
            );

            $consultar.empty()
                .append(
                    $("<i>")
                        .addClass(
                            valor
                                ? "bi bi-hourglass-split"
                                : "bi bi-search-heart-fill"
                        )
                        .attr("aria-hidden", "true")
                )
                .append(valor ? " Consultando…" : " Consultar");
        }

        /* Descartar los resultados al cambiar los filtros o la categoría */
        function invalidar(texto) {
            secuencia++;

            preparando = false;

            const anterior = solicitud;
            solicitud = null;

            if (anterior) anterior.abort();

            consulta = null;
            tokenPDF = null;
            pagina = 1;

            $("#tablaReportes, #reportePaginacion")
                .prop("hidden", true);

            $("#tablaReportes thead, #tablaReportes tbody, #reporteResumen")
                .empty();

            $("#reporteFiltrosAplicados, #reporteFecha, #reporteNota")
                .text("");

            $("#reporteConteo, #reportePagina").text("");

            $("#reporteAnterior, #reporteSiguiente")
                .prop("disabled", true);

            $("#reporteTitulo").text("Resultado de la consulta");

            $("#reporteVacio")
                .prop("hidden", false)
                .find("p")
                .text("Sus resultados aparecerán aquí.");

            ocupado(false);

            mensaje(
                permisoVigente
                    ? texto
                    : "Su cuenta ya no tiene permiso para consultar reportes. Actualice la página.",
                !permisoVigente
            );
        }


        /* ================================================= */
        /* CONFIGURACIÓN DE LOS FILTROS */
        /* ================================================= */

        function configurarFiltros() {
            limpiarValidaciones();

            const reporte = reporteSeleccionado();

            const pendiente = Boolean(
                reporte && reporte.disponible === false
            );

            const usaFechas = Boolean(
                reporte && reporte.fechas
            );

            /* Aplicar los filtros y campos obligatorios definidos por el modelo */
            $("[data-filtro]").each(function () {
                const campo = $(this).attr("data-filtro");

                const visible = Boolean(
                    reporte && reporte.filtros.includes(campo)
                );

                const requerido = visible &&
                    !pendiente &&
                    (reporte.requeridos || []).includes(campo);

                $(this)
                    .find("[data-requerido-filtro]")
                    .prop("hidden", !requerido);

                $(this)
                    .prop("hidden", !visible)
                    .find("select")
                    .prop("disabled", !visible || pendiente)
                    .prop("required", requerido)
                    .val("")
                    .find('option[value=""]')
                    .text(
                        requerido
                            ? "Seleccione una opción"
                            : "Todos"
                    );
            });

            /* Mostrar el período únicamente en los reportes que utilizan fechas */
            $("[data-fecha]")
                .prop("hidden", !usaFechas)
                .find("input")
                .prop("disabled", !usaFechas || pendiente)
                .prop("required", usaFechas && !pendiente);

            $("#reporteInicio").val(config.fecha_inicio);
            $("#reporteFin").val(config.fecha_fin);

            const etiquetasEstado = {
                clientes: "Estado del cliente",
                catalogo_servicios: "Estado del servicio",
                existencias: "Estado del insumo",
                reposicion: "Estado del insumo",
                vencimientos: "Estado del insumo",
                proveedores: "Estado del proveedor"
            };

            $("#etiqueta-reporte-estado_registro").text(
                etiquetasEstado[$tipo.val()] || "Estado del registro"
            );

            $("#reporteDescripcion").text(
                reporte
                    ? reporte.descripcion
                    : "Elija el tipo de reporte para ver sus filtros disponibles."
            );

            $("#reportePendiente").prop("hidden", !pendiente);

            $("#reportePendienteTexto").text(
                pendiente
                    ? "Este reporte estará disponible cuando se complete el registro de las atenciones y sus datos asociados."
                    : ""
            );

            invalidar(
                pendiente
                    ? "Reporte pendiente de habilitar."
                    : "Seleccione un tipo de reporte, ajuste sus filtros y pulse Consultar."
            );

            if (pendiente) {
                $("#reporteVacio").find("p").text(
                    "Puede consultar otro reporte disponible en las categorías superiores."
                );
            }
        }


        /* ================================================= */
        /* CATEGORÍAS DE REPORTES */
        /* ================================================= */

        function cargarCategoria(grupo) {
            grupoActual = grupo;

            $categorias.each(function () {
                const activa = $(this).attr("data-grupo") === grupoActual;

                $(this)
                    .toggleClass("active", activa)
                    .attr("aria-pressed", String(activa));
            });

            $tipo.empty().append(
                $("<option>")
                    .val("")
                    .text("Seleccione un reporte")
            );

            Object.entries(config.reportes).forEach(([clave, reporte]) => {
                if (reporte.grupo !== grupoActual) return;

                const titulo = reporte.titulo +
                    (reporte.disponible === false ? " (Pendiente)" : "");

                $tipo.append(
                    $("<option>").val(clave).text(titulo)
                );
            });

            $tipo.val("");

            configurarFiltros();
        }


        /* ================================================= */
        /* SOLICITUD DEL REPORTE */
        /* ================================================= */

        function solicitar(filtros) {
            return new Promise((resolve, reject) => {
                solicitud = $.ajax({
                    url: "Index.php?url=reportes",
                    method: "POST",
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    processData: false,

                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    },

                    data: JSON.stringify({
                        accion: "consultar",
                        filtros: filtros
                    }),

                    timeout: 30000,
                    success: resolve,

                    error: (xhr, estado) => {
                        let texto =
                            "No fue posible consultar el reporte. Compruebe la conexión.";

                        if (estado === "timeout") {
                            texto =
                                "La consulta tardó demasiado. Acote los filtros e intente nuevamente.";
                        }

                        const error = new Error(
                            xhr.responseJSON?.mensaje || texto
                        );

                        error.estado = xhr.status;
                        error.abortada = estado === "abort";
                        error.respuesta = xhr.responseJSON;

                        reject(error);
                    }
                });
            });
        }


        /* ================================================= */
        /* FORMATO DE LOS VALORES */
        /* ================================================= */

        function valorVisible(valor, formato) {

            /* Conservar el cero como un valor válido */
            if (
                valor === null ||
                valor === undefined ||
                valor === ""
            ) {
                return "—";
            }

            if (formato === "decimal" || formato === "entero") {
                const numero = Number(valor);

                if (!Number.isFinite(numero)) return "—";

                return numero.toLocaleString("es-VE", {
                    minimumFractionDigits: formato === "decimal" ? 2 : 0,
                    maximumFractionDigits: formato === "decimal" ? 2 : 0
                });
            }

            if (formato === "fecha") {
                return String(valor)
                    .slice(0, 10)
                    .split("-")
                    .reverse()
                    .join("/");
            }

            if (formato === "hora") {
                return String(valor).slice(0, 5);
            }

            return String(valor);
        }


        /* ================================================= */
        /* PAGINACIÓN DE LOS RESULTADOS */
        /* ================================================= */

        function mostrarPagina() {
            if (!consulta || !consulta.filas.length) return;

            const inicio = (pagina - 1) * filasPorPagina;
            const $cuerpo = $("#tablaReportes tbody").empty();

            /* Construir las filas de la página utilizando valores como texto */
            consulta.filas
                .slice(inicio, inicio + filasPorPagina)
                .forEach(fila => {
                    const $fila = $("<tr>");

                    consulta.columnas.forEach(columna => {
                        $fila.append(
                            $("<td>")
                                .text(
                                    valorVisible(
                                        fila[columna.clave],
                                        columna.formato
                                    )
                                )
                                .css(
                                    "white-space",
                                    ["nota_tecnica", "detalle"].includes(columna.clave)
                                        ? "pre-wrap"
                                        : ""
                                )
                                .toggleClass(
                                    "reportes-numero",
                                    ["decimal", "entero"].includes(columna.formato)
                                )
                        );
                    });

                    $cuerpo.append($fila);
                });

            const paginas = Math.ceil(
                consulta.filas.length / filasPorPagina
            );

            const fin = Math.min(
                inicio + filasPorPagina,
                consulta.filas.length
            );

            $("#reporteConteo").text(
                `${inicio + 1}–${fin} de ${consulta.filas.length}`
            );

            $("#reportePagina").text(`${pagina} / ${paginas}`);

            $("#reporteAnterior").prop("disabled", pagina === 1);
            $("#reporteSiguiente").prop("disabled", pagina === paginas);

            $(".reportes-tabla-contenedor").scrollTop(0);
        }


        /* ================================================= */
        /* PRESENTACIÓN DEL REPORTE */
        /* ================================================= */

        function mostrarResultado(reporte) {
            consulta = reporte;
            pagina = 1;

            $("#reporteTitulo").text(reporte.titulo);
            $("#reporteFecha").text("Consultado: " + reporte.generado);

            $("#reporteFiltrosAplicados").text(
                reporte.filtros_aplicados.join(" · ")
            );

            $("#reporteNota").text(reporte.nota);

            /* Presentar los indicadores calculados por el modelo */
            const $resumen = $("#reporteResumen").empty();

            reporte.resumen.forEach(indicador => {
                $resumen.append(
                    $("<div>")
                        .addClass("reportes-indicador")
                        .append(
                            $("<span>").text(indicador.titulo)
                        )
                        .append(
                            $("<strong>").text(String(indicador.valor))
                        )
                );
            });

            const $cabecera = $("<tr>");

            reporte.columnas.forEach(columna => {
                $cabecera.append(
                    $("<th>")
                        .attr("scope", "col")
                        .text(columna.titulo)
                );
            });

            $("#tablaReportes thead").empty().append($cabecera);

            const hayDatos = reporte.filas.length > 0;

            $("#tablaReportes, #reportePaginacion")
                .prop("hidden", !hayDatos);

            $("#reporteVacio")
                .prop("hidden", hayDatos)
                .find("p")
                .text("No se encontraron registros con estos filtros.");

            mensaje(
                hayDatos
                    ? "Consulta completada. El PDF incluirá todas las filas del resultado."
                    : "Consulta completada sin registros. Puede cambiar los filtros y volver a consultar."
            );

            mostrarPagina();
        }


        /* ================================================= */
        /* CONSULTA DEL REPORTE */
        /* ================================================= */

        $formulario.on("submit", async function (evento) {
            evento.preventDefault();

            if (solicitud || preparando) return;

            if (!permisoVigente) {
                mensaje("Su cuenta ya no tiene permiso para consultar reportes. Actualice la página.", true);
                return;
            }

            let turno = secuencia;

            preparando = true;
            ocupado(true);

            try {
                await cargarValidaciones();

                /* Descartar el envío si los filtros cambiaron durante la carga */
                if (turno !== secuencia) return;

                const resultado = validaciones.validarFormulario($formulario[0], reglasReporte());

                if (!resultado.valido) {
                    mensaje("Revise los filtros indicados antes de consultar.", true);
                    if (resultado.primerCampo) resultado.primerCampo.focus();
                    return;
                }

                /* Recoger únicamente los filtros habilitados del formulario */
                const filtros = {};

                $formulario.serializeArray().forEach(campo => {
                    filtros[campo.name] = campo.value;
                });

                invalidar("Consultando los registros…");
                turno = secuencia;
                preparando = true;
                ocupado(true);

                const respuesta = await solicitar(filtros);

                /* Ignorar las respuestas de consultas que dejaron de estar vigentes */
                if (turno !== secuencia) return;

                if (respuesta?.ok !== true) {
                    const error = new Error(respuesta?.mensaje || "La consulta no pudo completarse.");
                    error.respuesta = respuesta;
                    throw error;
                }

                const reporte = respuesta.reporte;

                if (
                    !reporte ||
                    !Array.isArray(reporte.filas) ||
                    !Array.isArray(reporte.columnas) ||
                    !Array.isArray(reporte.resumen) ||
                    !Array.isArray(reporte.filtros_aplicados)
                ) {
                    throw new Error("El servidor devolvió un resultado no válido.");
                }

                mostrarResultado(reporte);

                /* Asociar la descarga al resultado confirmado por el servidor */
                tokenPDF = reporte.filas.length > 0 && typeof respuesta.token_pdf === "string"
                    ? respuesta.token_pdf : null;

            } catch (error) {
                if (turno !== secuencia || error.abortada) return;

                if (error.estado === 401) {
                    window.location.assign("Index.php?url=login&sesion=expirada");
                    return;
                }

                if (error.estado === 403) permisoVigente = false;

                mensaje(
                    validaciones
                        ? error.message || "No fue posible consultar el reporte. Intente nuevamente."
                        : "No fue posible cargar las validaciones. Intente nuevamente o actualice la página.",
                    true
                );

                mostrarErroresReporte(error.respuesta);
                window.Alertas?.rechazo(error.message || "No fue posible consultar el reporte.", error.respuesta, $formulario);

            } finally {
                /* Recuperar los controles solamente para la consulta vigente */
                if (turno === secuencia) {
                    preparando = false;
                    solicitud = null;
                    ocupado(false);
                }
            }
        });


        /* ================================================= */
        /* EVENTOS DE CATEGORÍAS Y FILTROS */
        /* ================================================= */

        $categorias.on("click", function () {
            const grupo = $(this).attr("data-grupo");

            if (grupo !== grupoActual) {
                cargarCategoria(grupo);
            }
        });

        $tipo.on("change", configurarFiltros);

        /* Comprobar los campos al salir y al corregir una selección */
        $formulario.on("focusout", "input, select", function () {
            comprobarCampo(this);
        });

        /* Retirar el resultado anterior cuando cambie algún filtro */
        $formulario.on("input change", "input, select", function () {
            if (this === $tipo[0]) return;

            invalidar("Los filtros cambiaron. Pulse Consultar para actualizar el resultado.");

            if ($(this).next().hasClass("templo-validacion-feedback")) comprobarCampo(this);

            /* Actualizar el inicio cuando cambie la fecha final del período */
            if (this.name === "fecha_fin" && $("#reporteInicio").next().hasClass("templo-validacion-feedback")) {
                comprobarCampo($("#reporteInicio")[0]);
            }
        });

        $formulario.on("reset", function (evento) {
            evento.preventDefault();
            cargarCategoria(grupoActual);
        });

        $("#limpiarReporte").on("click", () => {
            cargarCategoria(grupoActual);
            $tipo.trigger("focus");
        });


        /* ================================================= */
        /* EVENTOS DE PAGINACIÓN */
        /* ================================================= */

        $("#reporteAnterior").on("click", () => {
            if (pagina > 1) {
                pagina--;
                mostrarPagina();
            }
        });

        $("#reporteSiguiente").on("click", () => {
            if (
                consulta &&
                pagina * filasPorPagina < consulta.filas.length
            ) {
                pagina++;
                mostrarPagina();
            }
        });


        /* ================================================= */
        /* DESCARGA DEL REPORTE */
        /* ================================================= */

        $pdf.on("click", () => {
            if (!tokenPDF || !permisoVigente) return;

            const $enlace = $("<a>", {
                href: "Index.php?url=reportes&pdf=" + encodeURIComponent(tokenPDF),
                target: "_blank",
                rel: "noopener"
            }).appendTo("body");

            $enlace.get(0).click();
            $enlace.remove();
        });


        /* ================================================= */
        /* CARGA INICIAL DEL MÓDULO */
        /* ================================================= */

        cargarCategoria(grupoActual);

        /* Preparar las validaciones sin interrumpir la carga de la vista */
        cargarValidaciones().catch(() => {});

        /* Recuperar los controles al volver desde una página conservada */
        $(window).on("pageshow", function (evento) {
            if (evento.originalEvent?.persisted) {
                invalidar("Pulse Consultar para actualizar el resultado.");
            }
        });
    });

})(jQuery);