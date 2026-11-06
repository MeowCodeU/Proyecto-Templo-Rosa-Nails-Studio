/* ===================================================== */
/* GESTIÓN DE REPORTES */
/* ===================================================== */

(function ($) {
    'use strict';

    $(function () {

        /* ================================================= */
        /* CONFIGURACIÓN DEL MÓDULO */
        /* ================================================= */

        const elementoConfiguracion = document.getElementById('configuracionReportes');

        if (!elementoConfiguracion) return;

        let config;


        /* Recuperar la configuración proporcionada por la vista */
        try {
            config = JSON.parse(elementoConfiguracion.textContent);

        } catch (error) {
            $('#reporteMensaje')
                .text('No fue posible cargar la configuración de reportes. Actualice la página.')
                .addClass('reportes-mensaje-error');

            return;
        }

        const $formulario = $('#formReportes');
        const $tipo = $('#reporteTipo');
        const $consultar = $('#consultarReporte');
        const $pdf = $('#descargarReporte');
        const $categorias = $('.reportes-categoria');
        const filasPorPagina = 10;

        let grupoActual = config.grupo_inicial || 'Agenda';
        let consulta = null;
        let tokenPDF = null;
        let pagina = 1;
        let solicitud = null;
        let secuencia = 0;
        let permisoVigente = true;


        /* ================================================= */
        /* OPCIONES DE LOS FILTROS */
        /* ================================================= */

        /* Insertar las opciones como texto y conservar sus caracteres */
        Object.entries(config.opciones).forEach(([campo, opciones]) => {
            const $select = $('#reporte-' + campo);

            $select.empty().append($('<option>').val('').text('Todos'));

            opciones.forEach(opcion => {
                $select.append(
                    $('<option>').val(String(opcion.id)).text(opcion.texto)
                );
            });
        });


        /* ================================================= */
        /* ESTADO Y MENSAJES DE LA CONSULTA */
        /* ================================================= */

        /* Obtener la configuración del reporte seleccionado */
        function reporteSeleccionado() {
            return config.reportes[$tipo.val()] || null;
        }


        /* Mostrar el mensaje correspondiente a la operación */
        function mensaje(texto, esError = false) {
            $('#reporteMensaje')
                .text(texto)
                .toggleClass('reportes-mensaje-error', esError);
        }


        /* Actualizar los botones y el estado accesible durante la consulta */
        function ocupado(valor) {
            const reporte = reporteSeleccionado();
            const disponible = Boolean(reporte && reporte.disponible !== false);

            $('#reporteResultados').attr('aria-busy', String(valor));

            $consultar.prop('disabled', valor || !disponible || !permisoVigente);
            $pdf.prop('disabled', valor || !tokenPDF || !permisoVigente);

            $consultar.empty()
                .append(
                    $('<i>')
                        .addClass(valor ? 'bi bi-hourglass-split' : 'bi bi-search-heart-fill')
                        .attr('aria-hidden', 'true')
                )
                .append(document.createTextNode(valor ? ' Consultando…' : ' Consultar'));
        }


        /* Descartar los resultados al cambiar filtros o categoría */
        function invalidar(texto) {
            secuencia++;

            if (solicitud) {
                solicitud.abort();
            }

            solicitud = null;
            consulta = null;
            tokenPDF = null;
            pagina = 1;

            $('#tablaReportes, #reportePaginacion').prop('hidden', true);
            $('#tablaReportes thead, #tablaReportes tbody, #reporteResumen').empty();
            $('#reporteFiltrosAplicados, #reporteFecha, #reporteNota').text('');
            $('#reporteConteo, #reportePagina').text('');
            $('#reporteAnterior, #reporteSiguiente').prop('disabled', true);
            $('#reporteTitulo').text('Resultado de la consulta');

            $('#reporteVacio')
                .prop('hidden', false)
                .find('p')
                .text('Sus resultados aparecerán aquí.');

            ocupado(false);

            mensaje(
                permisoVigente
                    ? texto
                    : 'Su cuenta ya no tiene permiso para consultar reportes. Actualice la página.',
                !permisoVigente
            );
        }


        /* ================================================= */
        /* CONFIGURACIÓN DE LOS FILTROS */
        /* ================================================= */

        function configurarFiltros() {
            const reporte = reporteSeleccionado();
            const pendiente = Boolean(reporte && reporte.disponible === false);
            const usaFechas = Boolean(reporte && reporte.fechas);


            /* Mostrar únicamente los filtros que utiliza el reporte */
            $('[data-filtro]').each(function () {
                const visible = Boolean(reporte && reporte.filtros.includes(this.dataset.filtro));

                $(this)
                    .prop('hidden', !visible)
                    .find('select')
                    .prop('disabled', !visible || pendiente)
                    .val('');
            });


            /* Preparar el intervalo de fechas cuando corresponda */
            $('[data-fecha]')
                .prop('hidden', !usaFechas)
                .find('input')
                .prop('disabled', !usaFechas || pendiente)
                .prop('required', usaFechas && !pendiente);

            $('#reporteInicio').val(config.fecha_inicio);
            $('#reporteFin').val(config.fecha_fin);

            const etiquetasEstado = {
                clientes: 'Estado del cliente',
                catalogo_servicios: 'Estado del servicio',
                existencias: 'Estado del insumo',
                reposicion: 'Estado del insumo',
                vencimientos: 'Estado del insumo',
                proveedores: 'Estado del proveedor',
                proveedores_insumos: 'Estado del proveedor'
            };

            $('#etiqueta-reporte-estado_registro').text(
                etiquetasEstado[$tipo.val()] || 'Estado del registro'
            );

            $('#reporteDescripcion').text(
                reporte
                    ? reporte.descripcion
                    : 'Elija el tipo de reporte para ver sus filtros disponibles.'
            );


            /* Informar cuando el reporte todavía esté pendiente */
            $('#reportePendiente').prop('hidden', !pendiente);

            $('#reportePendienteTexto').text(
                pendiente
                    ? 'Este reporte estará disponible cuando se complete el registro de las atenciones y sus datos asociados.'
                    : ''
            );

            invalidar(
                pendiente
                    ? 'Reporte pendiente de habilitar.'
                    : 'Seleccione un tipo de reporte, ajuste sus filtros y pulse Consultar.'
            );

            if (pendiente) {
                $('#reporteVacio').find('p').text(
                    'Puede consultar otro reporte disponible en las categorías superiores.'
                );
            }
        }


        /* ================================================= */
        /* CATEGORÍAS DE REPORTES */
        /* ================================================= */

        function cargarCategoria(grupo) {
            grupoActual = grupo;


            /* Identificar visualmente la categoría seleccionada */
            $categorias.each(function () {
                const activa = this.dataset.grupo === grupoActual;

                $(this)
                    .toggleClass('active', activa)
                    .attr('aria-pressed', String(activa));
            });


            /* Preparar los tipos de reporte disponibles para la categoría */
            $tipo.empty().append($('<option>').val('').text('Seleccione un reporte'));

            Object.entries(config.reportes).forEach(([clave, reporte]) => {
                if (reporte.grupo !== grupoActual) return;

                const titulo = reporte.titulo + (reporte.disponible === false ? ' (Pendiente)' : '');

                $tipo.append($('<option>').val(clave).text(titulo));
            });

            $tipo.val('');

            configurarFiltros();
        }


        /* ================================================= */
        /* SOLICITUD DEL REPORTE */
        /* ================================================= */

        function solicitar(filtros) {
            return new Promise((resolve, reject) => {
                solicitud = $.ajax({
                    url: 'Index.php?url=reportes',
                    method: 'POST',
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'json',

                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },

                    data: JSON.stringify({
                        accion: 'consultar',
                        filtros
                    }),

                    timeout: 30000,
                    success: resolve,


                    /* Comunicar los errores de respuesta o de conexión */
                    error: (xhr, estado) => {
                        let texto = 'No fue posible consultar el reporte. Compruebe la conexión.';

                        if (estado === 'timeout') {
                            texto = 'La consulta tardó demasiado. Acote los filtros e intente nuevamente.';
                        }

                        const error = new Error(xhr.responseJSON?.mensaje || texto);

                        error.estado = xhr.status;
                        error.abortada = estado === 'abort';

                        reject(error);
                    }
                });
            });
        }


        /* ================================================= */
        /* FORMATO DE LOS VALORES */
        /* ================================================= */

        function valorVisible(valor, formato) {

            /* Diferenciar los valores vacíos del número cero */
            if (valor === null || valor === undefined || valor === '') {
                return '—';
            }


            /* Aplicar el formato correspondiente a los números */
            if (formato === 'decimal' || formato === 'entero') {
                const numero = Number(valor);

                if (!Number.isFinite(numero)) return '—';

                return numero.toLocaleString('es-VE', {
                    minimumFractionDigits: formato === 'decimal' ? 2 : 0,
                    maximumFractionDigits: formato === 'decimal' ? 2 : 0
                });
            }


            /* Mostrar las fechas en orden de día, mes y año */
            if (formato === 'fecha') {
                return String(valor).slice(0, 10).split('-').reverse().join('/');
            }


            /* Mostrar únicamente la hora y los minutos */
            if (formato === 'hora') {
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
            const $cuerpo = $('#tablaReportes tbody').empty();


            /* Construir las filas correspondientes a la página actual */
            consulta.filas.slice(inicio, inicio + filasPorPagina).forEach(fila => {
                const $fila = $('<tr>');

                consulta.columnas.forEach(columna => {
                    $fila.append(
                        $('<td>')
                            .text(valorVisible(fila[columna.clave], columna.formato))
                            .toggleClass('reportes-numero', ['decimal', 'entero'].includes(columna.formato))
                    );
                });

                $cuerpo.append($fila);
            });


            /* Actualizar el conteo y los controles de navegación */
            const paginas = Math.ceil(consulta.filas.length / filasPorPagina);
            const fin = Math.min(inicio + filasPorPagina, consulta.filas.length);

            $('#reporteConteo').text(`${inicio + 1}–${fin} de ${consulta.filas.length}`);
            $('#reportePagina').text(`${pagina} / ${paginas}`);
            $('#reporteAnterior').prop('disabled', pagina === 1);
            $('#reporteSiguiente').prop('disabled', pagina === paginas);

            $('.reportes-tabla-contenedor').scrollTop(0);
        }


        /* ================================================= */
        /* PRESENTACIÓN DEL REPORTE */
        /* ================================================= */

        function mostrarResultado(reporte) {
            consulta = reporte;
            pagina = 1;


            /* Mostrar los datos generales de la consulta */
            $('#reporteTitulo').text(reporte.titulo);
            $('#reporteFecha').text('Consultado: ' + reporte.generado);
            $('#reporteFiltrosAplicados').text(reporte.filtros_aplicados.join(' · '));
            $('#reporteNota').text(reporte.nota);


            /* Presentar los indicadores del resumen */
            const $resumen = $('#reporteResumen').empty();

            reporte.resumen.forEach(indicador => {
                $resumen.append(
                    $('<div>')
                        .addClass('reportes-indicador')
                        .append($('<span>').text(indicador.titulo))
                        .append($('<strong>').text(String(indicador.valor)))
                );
            });


            /* Construir las columnas del reporte */
            const $cabecera = $('<tr>');

            reporte.columnas.forEach(columna => {
                $cabecera.append(
                    $('<th>').attr('scope', 'col').text(columna.titulo)
                );
            });

            $('#tablaReportes thead').empty().append($cabecera);


            /* Alternar entre la tabla y el mensaje de resultados vacíos */
            const hayDatos = reporte.filas.length > 0;

            $('#tablaReportes, #reportePaginacion').prop('hidden', !hayDatos);

            $('#reporteVacio')
                .prop('hidden', hayDatos)
                .find('p')
                .text('No se encontraron registros con estos filtros.');

            mensaje(
                hayDatos
                    ? 'Consulta completada. El PDF incluirá todas las filas del resultado.'
                    : 'Consulta completada sin registros. Puede cambiar los filtros y volver a consultar.'
            );

            mostrarPagina();
        }


        /* ================================================= */
        /* CONSULTA DEL REPORTE */
        /* ================================================= */

        $formulario.on('submit', async function (evento) {
            evento.preventDefault();

            if (solicitud) return;


            /* Comprobar que el acceso al módulo siga disponible */
            if (!permisoVigente) {
                mensaje(
                    'Su cuenta ya no tiene permiso para consultar reportes. Actualice la página.',
                    true
                );

                return;
            }

            const seleccionado = reporteSeleccionado();

            if (!seleccionado || seleccionado.disponible === false) {
                return;
            }

            if (!this.reportValidity()) return;


            /* Comprobar el orden del intervalo de fechas */
            if (seleccionado.fechas && $('#reporteInicio').val() > $('#reporteFin').val()) {
                mensaje('La fecha inicial no puede ser posterior a la fecha final.', true);

                $('#reporteInicio').trigger('focus');

                return;
            }


            /* Recoger los filtros habilitados del formulario */
            const filtros = {};

            $formulario.serializeArray().forEach(campo => {
                filtros[campo.name] = campo.value;
            });

            invalidar('Consultando los registros…');

            const turno = secuencia;

            ocupado(true);

            try {
                const respuesta = await solicitar(filtros);


                /* Ignorar respuestas de consultas que dejaron de estar vigentes */
                if (turno !== secuencia) return;

                if (!respuesta.ok) {
                    throw new Error(respuesta.mensaje || 'La consulta no pudo completarse.');
                }


                /* Comprobar la estructura del reporte recibido */
                const reporte = respuesta.reporte;

                if (
                    !reporte ||
                    !Array.isArray(reporte.filas) ||
                    !Array.isArray(reporte.columnas) ||
                    !Array.isArray(reporte.resumen) ||
                    !Array.isArray(reporte.filtros_aplicados)
                ) {
                    throw new Error('El servidor devolvió un resultado no válido.');
                }

                mostrarResultado(reporte);


                /* Habilitar el PDF únicamente cuando existan filas y un token */
                tokenPDF = reporte.filas.length > 0 && typeof respuesta.token_pdf === 'string'
                    ? respuesta.token_pdf
                    : null;

            } catch (error) {
                if (turno !== secuencia || error.abortada) {
                    return;
                }


                /* Volver al acceso cuando la sesión haya vencido */
                if (error.estado === 401) {
                    window.location.assign('Index.php?url=login&sesion=expirada');
                    return;
                }


                /* Deshabilitar las consultas cuando el servidor rechace el permiso */
                if (error.estado === 403) {
                    permisoVigente = false;
                }

                mensaje(error.message, true);

            } finally {

                /* Recuperar los controles únicamente para la consulta vigente */
                if (turno === secuencia) {
                    solicitud = null;
                    ocupado(false);
                }
            }
        });


        /* ================================================= */
        /* EVENTOS DE CATEGORÍAS Y FILTROS */
        /* ================================================= */

        /* Cambiar la categoría seleccionada */
        $categorias.on('click', function () {
            if (this.dataset.grupo !== grupoActual) {
                cargarCategoria(this.dataset.grupo);
            }
        });


        /* Preparar los filtros del tipo de reporte seleccionado */
        $tipo.on('change', configurarFiltros);


        /* Invalidar los resultados cuando cambie algún filtro */
        $formulario.find('input, select').not($tipo).on('input change', () => {
            invalidar('Los filtros cambiaron. Pulse Consultar para actualizar el resultado.');
        });


        /* Limpiar la selección y los resultados de la categoría actual */
        $('#limpiarReporte').on('click', () => {
            cargarCategoria(grupoActual);
            $tipo.trigger('focus');
        });


        /* ================================================= */
        /* EVENTOS DE PAGINACIÓN */
        /* ================================================= */

        $('#reporteAnterior').on('click', () => {
            if (pagina > 1) {
                pagina--;
                mostrarPagina();
            }
        });

        $('#reporteSiguiente').on('click', () => {
            if (consulta && pagina * filasPorPagina < consulta.filas.length) {
                pagina++;
                mostrarPagina();
            }
        });


        /* ================================================= */
        /* DESCARGA DEL REPORTE */
        /* ================================================= */

        $pdf.on('click', () => {
            if (!tokenPDF || !permisoVigente) return;

            const enlace = document.createElement('a');

            enlace.href = 'Index.php?url=reportes&pdf=' + encodeURIComponent(tokenPDF);
            enlace.target = '_blank';
            enlace.rel = 'noopener';

            document.body.appendChild(enlace);

            enlace.click();
            enlace.remove();
        });


        /* ================================================= */
        /* CARGA INICIAL DEL MÓDULO */
        /* ================================================= */

        cargarCategoria(grupoActual);
    });

})(jQuery);