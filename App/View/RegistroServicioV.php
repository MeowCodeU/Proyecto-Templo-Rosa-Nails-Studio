<!-- ===================================================== -->
<!-- DATOS DE LA VISTA -->
<!-- ===================================================== -->

<?php

$tituloPagina = 'Registro de Servicios';

$tokenVistaRegistroServicio = htmlspecialchars(
    (string) ($tokenRegistroServicio ?? ''),
    ENT_QUOTES,
    'UTF-8'
);

require_once __DIR__ . '/Layout/Header.php';

?>


<!-- ===================================================== -->
<!-- REGISTRO DE SERVICIOS -->
<!-- ===================================================== -->

<section class="module-card registro-servicio-module">

    <!-- Título y acciones del módulo -->
    <div class="module-header">

        <h2>
            <span class="servicio-icono" aria-hidden="true">
                <svg viewBox="0 0 64 64">
                    <rect x="24" y="6" width="16" height="20" rx="2"></rect>

                    <path d="M28 10V22"></path>
                    <path d="M32 10V22"></path>
                    <path d="M36 10V22"></path>
                    <path d="M24 28H29"></path>
                    <path d="M35 28H40"></path>
                    <path d="M29 28C29 30 28 31 26 31"></path>
                    <path d="M35 28C35 30 36 31 38 31"></path>

                    <path
                        d="M18 31H46L44 50
                           C43.7 53 41.4 55 38.4 55H25.6
                           C22.6 55 20.3 53 20 50L18 31Z"
                    ></path>

                    <path d="M22 50C26 52 38 52 42 50"></path>

                    <path
                        class="bottle-heart"
                        d="M32 45
                           C31 43.5 28 41.8 28 39.5
                           C28 37.7 29.4 36.5 31 36.5
                           C32 36.5 32.8 37 33.3 37.9
                           C33.8 37 34.6 36.5 35.6 36.5
                           C37.2 36.5 38.6 37.7 38.6 39.5
                           C38.6 41.8 35.6 43.5 32 46Z"
                    ></path>
                </svg>
            </span>

            Registro de Servicios
        </h2>

        <div class="d-flex gap-2 flex-wrap">

            <button
                class="btn-templo-secondary d-none"
                type="button"
                id="btnVolverAgendamientos"
            >
                <i class="bi bi-arrow-left-circle" aria-hidden="true"></i>
                Volver a la lista
            </button>

            <button
                class="btn-templo-secondary d-none"
                type="button"
                id="btnActualizarAtencionServicio"
            >
                <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                Actualizar atención
            </button>

            <button
                class="btn-templo-secondary"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#modalCatalogoTrabajos"
            >
                <i class="bi bi-images" aria-hidden="true"></i>
                Catálogo
            </button>

            <button
                class="btn-templo-primary"
                type="button"
                id="btnRefrescarRegistroServicio"
            >
                <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                Refrescar
            </button>

        </div>

    </div>


    <!-- Avisos de las consultas y operaciones del módulo -->
    <div
        id="mensajeRegistroServicio"
        class="alert py-2 small flex-shrink-0 d-none"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        hidden
    ></div>


    <!-- ===================================================== -->
    <!-- AGENDAMIENTOS PARA ATENDER -->
    <!-- ===================================================== -->

    <div id="panelAgendamientosServicio" class="registro-panel">

        <div class="templo-table-wrapper registro-servicio-table-wrapper">

            <div class="registro-table-head">
                <h5>
                    <i class="bi bi-calendar2-heart" aria-hidden="true"></i>
                    Agendamientos para atender
                </h5>

                <small>
                    Seleccione una clienta para registrar el servicio prestado.
                </small>
            </div>

            <div class="table-responsive registro-servicio-table-responsive">

                <table
                    class="table templo-table table-hover align-middle"
                    id="tablaServiciosPendientes"
                >
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Clienta</th>
                            <th>Servicios agendados</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>

                    <!-- Agendamientos cargados por el JavaScript del módulo -->
                    <tbody id="tbodyServiciosPendientes"></tbody>
                </table>

            </div>

        </div>

    </div>


    <!-- ===================================================== -->
    <!-- REGISTRO DEL SERVICIO SELECCIONADO -->
    <!-- ===================================================== -->

    <div id="panelRegistroServicio" class="registro-panel d-none">

        <input
            type="hidden"
            id="idAgendamientoServicio"
            name="id_agendamiento"
            value=""
        >

        <div class="registro-atencion-layout">

            <!-- Ficha de la clienta y datos de la atención -->
            <div class="registro-card">

                <div class="registro-card-header">
                    <i class="bi bi-person-hearts" aria-hidden="true"></i>
                    Ficha de la Clienta
                </div>

                <div class="registro-card-body text-center">

                    <div class="mb-4">
                        <h5 class="registro-ficha-nombre mb-0" id="fichaNombreClienta">—</h5>
                    </div>

                    <div class="registro-ficha-list">

                        <p>
                            <strong>
                                <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>
                                Alergias:
                            </strong>
                            <br>
                            <span id="fichaAlergias">—</span>
                        </p>

                        <p>
                            <strong>
                                <i class="bi bi-chat-heart me-1" aria-hidden="true"></i>
                                Nota técnica del último servicio:
                            </strong>
                            <br>
                            <span id="fichaNotaTecnicaAnterior">—</span>
                        </p>

                        <p>
                            <strong>
                                <span class="registro-icono-servicio" aria-hidden="true">
                                    <svg viewBox="0 0 64 64">
                                        <rect x="24" y="6" width="16" height="20" rx="2"></rect>

                                        <path d="M28 10V22"></path>
                                        <path d="M32 10V22"></path>
                                        <path d="M36 10V22"></path>
                                        <path d="M24 28H29"></path>
                                        <path d="M35 28H40"></path>
                                        <path d="M29 28C29 30 28 31 26 31"></path>
                                        <path d="M35 28C35 30 36 31 38 31"></path>

                                        <path
                                            d="M18 31H46L44 50
                                               C43.7 53 41.4 55 38.4 55H25.6
                                               C22.6 55 20.3 53 20 50L18 31Z"
                                        ></path>

                                        <path d="M22 50C26 52 38 52 42 50"></path>

                                        <path
                                            class="bottle-heart"
                                            d="M32 45
                                               C31 43.5 28 41.8 28 39.5
                                               C28 37.7 29.4 36.5 31 36.5
                                               C32 36.5 32.8 37 33.3 37.9
                                               C33.8 37 34.6 36.5 35.6 36.5
                                               C37.2 36.5 38.6 37.7 38.6 39.5
                                               C38.6 41.8 35.6 43.5 32 46Z"
                                        ></path>
                                    </svg>
                                </span>

                                Último servicio realizado:
                            </strong>
                            <br>
                            <span id="fichaUltimoServicio">—</span>
                        </p>

                        <p>
                            <strong>
                                <i class="bi bi-calendar2-heart" aria-hidden="true"></i>
                                Última atención:
                            </strong>
                            <br>
                            <span id="fichaUltimaAtencion">—</span>
                        </p>

                        <hr>

                        <p>
                            <strong>
                                <i class="bi bi-calendar2-heart-fill" aria-hidden="true"></i>
                                Servicios agendados:
                            </strong>
                            <br>
                            <span id="fichaServiciosAgendados">—</span>
                        </p>

                        <p>
                            <strong>
                                <i class="bi bi-cash-coin me-1" aria-hidden="true"></i>
                                Monto de la atención:
                            </strong>
                            <br>
                            <span id="fichaMontoAgendamiento">—</span>
                        </p>

                        <p>
                            <strong>
                                <i class="bi bi-person-badge me-1" aria-hidden="true"></i>
                                Especialista asignada:
                            </strong>
                            <br>
                            <span id="fichaEspecialistaAsignada">—</span>
                        </p>

                    </div>

                </div>

            </div>


            <!-- Pestañas del registro de servicio -->
            <div class="registro-card registro-tabs">

                <div class="registro-card-header">
                    <i class="bi bi-clipboard-heart-fill" aria-hidden="true"></i>
                    Registro del Servicio Prestado
                </div>

                <div class="registro-card-body">

                    <ul class="nav nav-tabs" id="registroServicioTabs" role="tablist">

                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link active"
                                id="servicio-realizado-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#servicio-realizado"
                                type="button"
                                role="tab"
                                aria-controls="servicio-realizado"
                                aria-selected="true"
                            >
                                <span class="registro-icono-servicio" aria-hidden="true">
                                    <svg viewBox="0 0 64 64">
                                        <rect x="24" y="6" width="16" height="20" rx="2"></rect>

                                        <path d="M28 10V22"></path>
                                        <path d="M32 10V22"></path>
                                        <path d="M36 10V22"></path>
                                        <path d="M24 28H29"></path>
                                        <path d="M35 28H40"></path>
                                        <path d="M29 28C29 30 28 31 26 31"></path>
                                        <path d="M35 28C35 30 36 31 38 31"></path>

                                        <path
                                            d="M18 31H46L44 50
                                               C43.7 53 41.4 55 38.4 55H25.6
                                               C22.6 55 20.3 53 20 50L18 31Z"
                                        ></path>

                                        <path d="M22 50C26 52 38 52 42 50"></path>

                                        <path
                                            class="bottle-heart"
                                            d="M32 45
                                               C31 43.5 28 41.8 28 39.5
                                               C28 37.7 29.4 36.5 31 36.5
                                               C32 36.5 32.8 37 33.3 37.9
                                               C33.8 37 34.6 36.5 35.6 36.5
                                               C37.2 36.5 38.6 37.7 38.6 39.5
                                               C38.6 41.8 35.6 43.5 32 46Z"
                                        ></path>
                                    </svg>
                                </span>

                                Servicio realizado
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="insumos-utilizados-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#insumos-utilizados"
                                type="button"
                                role="tab"
                                aria-controls="insumos-utilizados"
                                aria-selected="false"
                            >
                                <i class="bi bi-box2-heart-fill" aria-hidden="true"></i>
                                Insumos utilizados
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="historial-servicios-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#historial-servicios"
                                type="button"
                                role="tab"
                                aria-controls="historial-servicios"
                                aria-selected="false"
                            >
                                <i class="bi bi-postcard-heart" aria-hidden="true"></i>
                                Historial
                            </button>
                        </li>

                    </ul>


                    <div class="tab-content mt-4" id="registroServicioTabsContent">

                        <!-- ===================================================== -->
                        <!-- SERVICIO REALIZADO -->
                        <!-- ===================================================== -->

                        <div
                            class="tab-pane fade show active"
                            id="servicio-realizado"
                            role="tabpanel"
                            aria-labelledby="servicio-realizado-tab"
                            tabindex="0"
                        >

                            <form
                                id="formServicioRealizado"
                                action="Index.php?url=registroServicio"
                                method="POST"
                                enctype="multipart/form-data"
                            >

                                <!-- Identificación y seguridad del formulario -->
                                <input
                                    type="hidden"
                                    name="token_csrf"
                                    value="<?= $tokenVistaRegistroServicio; ?>"
                                >

                                <input type="hidden" name="accion" value="guardarServicio">

                                <input
                                    type="hidden"
                                    name="id_agendamiento"
                                    id="servicioIdAgendamiento"
                                    value=""
                                >

                                <div class="row">

                                    <div class="col-md-6 mb-3">

                                        <label for="servicioRealizado" class="form-label">
                                            Servicio realizado
                                            <span class="required-mark" aria-hidden="true">*</span>
                                        </label>

                                        <select
                                            class="form-select templo-input"
                                            id="servicioRealizado"
                                            name="id_detalle_agendamiento"
                                            required
                                        >
                                            <option value="">Seleccionar servicio agendado...</option>
                                        </select>

                                        <small class="templo-help-text">
                                            Seleccione el servicio al que pertenecerán la nota técnica y la fotografía.
                                        </small>

                                    </div>

                                    <div class="col-md-6 mb-3">

                                        <label for="totalServicio" class="form-label">
                                            Precio aplicado
                                            <span class="required-mark" aria-hidden="true">*</span>
                                        </label>

                                        <input
                                            type="number"
                                            class="form-control templo-input"
                                            id="totalServicio"
                                            name="precio_aplicado"
                                            min="0"
                                            max="99999999.99"
                                            step="0.01"
                                            value=""
                                            aria-describedby="ayudaPrecioServicio"
                                            required
                                        >

                                        <small class="templo-help-text" id="ayudaPrecioServicio">
                                            Precio correspondiente al servicio seleccionado.
                                        </small>

                                    </div>

                                    <div class="col-md-6 mb-3">

                                        <label for="fechaServicio" class="form-label">
                                            Fecha de atención
                                        </label>

                                        <input
                                            type="date"
                                            class="form-control templo-input"
                                            id="fechaServicio"
                                            readonly
                                            aria-describedby="ayudaFechaServicio"
                                        >

                                        <small class="templo-help-text" id="ayudaFechaServicio">
                                            Corresponde a la fecha del agendamiento.
                                        </small>

                                    </div>

                                    <div class="col-12 mb-3">

                                        <label for="notaTecnicaServicio" class="form-label">
                                            Nota técnica
                                        </label>

                                        <textarea
                                            class="form-control templo-input"
                                            id="notaTecnicaServicio"
                                            name="nota_tecnica"
                                            rows="4"
                                            placeholder="Ingrese la nota técnica correspondiente al servicio realizado"
                                        ></textarea>

                                    </div>


                                    <!-- Fotografía opcional del servicio seleccionado -->
                                    <div class="col-md-6 mb-3">

                                        <label for="fotoResultadoServicio" class="form-label">
                                            Fotografía del resultado
                                            <i class="bi bi-camera2" aria-hidden="true"></i>
                                        </label>

                                        <input
                                            type="file"
                                            class="form-control templo-input"
                                            id="fotoResultadoServicio"
                                            name="foto_servicio"
                                            accept="image/jpeg,image/png,image/webp"
                                            aria-describedby="ayudaFotoServicio"
                                        >

                                        <div
                                            id="previewResultadoVacio"
                                            class="registro-resultado-preview registro-preview-vacio"
                                        >
                                            <i class="bi bi-image" aria-hidden="true"></i>
                                            <span>Sin fotografía seleccionada</span>
                                        </div>

                                        <img
                                            id="previewResultadoServicio"
                                            src="Assets/img/LogoTR.png"
                                            alt="Vista previa del resultado"
                                            class="registro-resultado-preview d-none"
                                        >

                                        <small class="templo-help-text" id="ayudaFotoServicio">
                                            Fotografía opcional en JPEG, PNG o WebP, de hasta 2 MB.
                                        </small>

                                    </div>

                                    <div class="col-md-6 mb-3">

                                        <div class="form-check mt-md-4">

                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                id="destacarCatalogo"
                                                name="destacada_catalogo"
                                                value="1"
                                            >

                                            <label
                                                class="form-check-label fw-bold"
                                                for="destacarCatalogo"
                                                style="color: var(--brown-main);"
                                            >
                                                Destacar esta fotografía en el catálogo
                                            </label>

                                        </div>

                                        <small class="templo-help-text">
                                            Se mostrará en el catálogo cuando finalice la atención.
                                        </small>

                                    </div>

                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-3 flex-wrap">

                                    <button
                                        type="button"
                                        class="btn-templo-secondary"
                                        id="btnCancelarServicio"
                                    >
                                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                                        Cancelar
                                    </button>

                                    <button
                                        type="button"
                                        class="btn-templo-primary"
                                        id="btnGuardarServicio"
                                    >
                                        <i class="bi bi-save" aria-hidden="true"></i>
                                        Guardar servicio
                                    </button>

                                </div>

                            </form>

                        </div>


                        <!-- ===================================================== -->
                        <!-- INSUMOS UTILIZADOS -->
                        <!-- ===================================================== -->

                        <div
                            class="tab-pane fade"
                            id="insumos-utilizados"
                            role="tabpanel"
                            aria-labelledby="insumos-utilizados-tab"
                            tabindex="0"
                        >

                            <form
                                id="formInsumosUtilizados"
                                action="Index.php?url=registroServicio"
                                method="POST"
                            >

                                <!-- Identificación y seguridad del formulario -->
                                <input
                                    type="hidden"
                                    name="token_csrf"
                                    value="<?= $tokenVistaRegistroServicio; ?>"
                                >

                                <input type="hidden" name="accion" value="guardarInsumos">

                                <input
                                    type="hidden"
                                    name="id_agendamiento"
                                    id="insumosIdAgendamiento"
                                    value=""
                                >

                                <p
                                    class="mb-4"
                                    style="color: rgba(122, 101, 91, 0.78); font-weight: 600;"
                                >
                                    <i
                                        class="bi bi-info-circle me-1"
                                        style="color: var(--gold-main);"
                                        aria-hidden="true"
                                    ></i>

                                    Registre las unidades o los envases completos utilizados.
                                    Guarde la lista para actualizar los consumos y las existencias.
                                </p>


                                <!-- Selección del insumo y su cantidad entera -->
                                <div class="row">

                                    <div class="col-md-5 mb-3">

                                        <label for="insumoUtilizado" class="form-label">
                                            Insumo utilizado
                                        </label>

                                        <select
                                            class="form-select templo-input"
                                            id="insumoUtilizado"
                                            name="id_insumo"
                                        >
                                            <option value="">Seleccionar insumo...</option>
                                        </select>

                                    </div>

                                    <div class="col-md-2 mb-3">

                                        <label for="cantidadInsumo" class="form-label">
                                            Cantidad
                                        </label>

                                        <input
                                            type="number"
                                            class="form-control templo-input"
                                            id="cantidadInsumo"
                                            name="cantidad_utilizada"
                                            min="1"
                                            step="1"
                                            inputmode="numeric"
                                            placeholder="0"
                                        >

                                    </div>

                                    <div class="col-md-3 mb-3">

                                        <label for="presentacionInsumo" class="form-label">
                                            Presentación
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control templo-input"
                                            id="presentacionInsumo"
                                            value=""
                                            readonly
                                        >

                                    </div>

                                    <div class="col-md-2 mb-3">

                                        <label for="stockDisponibleInsumo" class="form-label">
                                            Disponible
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control templo-input"
                                            id="stockDisponibleInsumo"
                                            aria-describedby="ayudaDisponibleInsumo"
                                            value=""
                                            readonly
                                        >

                                    </div>

                                    <div class="col-12 mb-3 d-flex justify-content-end">

                                        <button
                                            type="button"
                                            class="btn-templo-secondary"
                                            id="btnAgregarInsumo"
                                        >
                                            <i class="bi bi-plus-circle" aria-hidden="true"></i>
                                            Agregar insumo
                                        </button>

                                    </div>

                                </div>

                                <p class="templo-help-text mb-3" id="ayudaDisponibleInsumo">
                                    La disponibilidad incluye las unidades ya asignadas a esta atención.
                                </p>


                                <!-- Lista de consumos que se guardará para la atención -->
                                <div class="table-responsive">

                                    <table
                                        class="table templo-table table-hover align-middle"
                                        id="tablaInsumosSeleccionados"
                                    >
                                        <thead>
                                            <tr>
                                                <th>Insumo</th>
                                                <th>Cantidad</th>
                                                <th>Presentación</th>
                                                <th class="text-center">Acción</th>
                                            </tr>
                                        </thead>

                                        <tbody id="tbodyInsumosSeleccionados">
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    No hay insumos agregados.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>

                                <div class="d-flex justify-content-end mt-3">

                                    <button
                                        type="button"
                                        class="btn-templo-primary"
                                        id="btnGuardarInsumos"
                                    >
                                        <i class="bi bi-save" aria-hidden="true"></i>
                                        Guardar insumos utilizados
                                    </button>

                                </div>

                            </form>

                        </div>


                        <!-- ===================================================== -->
                        <!-- HISTORIAL DE SERVICIOS -->
                        <!-- ===================================================== -->

                        <div
                            class="tab-pane fade"
                            id="historial-servicios"
                            role="tabpanel"
                            aria-labelledby="historial-servicios-tab"
                            tabindex="0"
                        >

                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                                <h5
                                    class="fw-bold mb-0"
                                    style="color: var(--brown-main);"
                                >
                                    <i
                                        class="bi bi-clock-history me-2"
                                        style="color: var(--gold-main);"
                                        aria-hidden="true"
                                    ></i>

                                    Historial de servicios
                                </h5>

                                <!-- Búsqueda e impresión del historial de la clienta -->
                                <div class="registro-historial-controles">

                                    <label
                                        class="registro-historial-busqueda"
                                        for="buscarHistorialServicios"
                                    >
                                        <span>Buscar:</span>

                                        <input
                                            type="search"
                                            id="buscarHistorialServicios"
                                            class="buscador-historial"
                                            aria-label="Buscar en el historial de servicios"
                                            autocomplete="off"
                                        >
                                    </label>

                                    <button
                                        type="button"
                                        class="btn-templo-secondary"
                                        id="btnImprimirHistorial"
                                    >
                                        <i class="bi bi-printer" aria-hidden="true"></i>
                                        Imprimir historial
                                    </button>

                                </div>

                            </div>

                            <div class="table-responsive">

                                <table
                                    class="table templo-table table-hover align-middle"
                                    id="tablaHistorialServicios"
                                >
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Foto</th>
                                            <th>Servicio realizado</th>
                                            <th>Precio aplicado</th>
                                            <th>Nota técnica</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>

                                    <tbody id="tbodyHistorialServicios">
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                No hay servicios registrados.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>

                            <div class="d-flex justify-content-end mt-3">

                                <button
                                    type="button"
                                    class="btn-templo-primary"
                                    id="btnFinalizarServicio"
                                >
                                    <i class="bi bi-check-circle" aria-hidden="true"></i>
                                    Finalizar registro
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- ===================================================== -->
<!-- CATÁLOGO DE TRABAJOS DESTACADOS -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalCatalogoTrabajos"
    tabindex="-1"
    aria-labelledby="modalCatalogoTrabajosLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content templo-modal">

            <div class="modal-header">

                <h5 class="modal-title" id="modalCatalogoTrabajosLabel">
                    <i class="bi bi-images" aria-hidden="true"></i>
                    Catálogo de Trabajos Destacados
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>

            <div class="modal-body">

                <!-- Avisos de carga y consulta del catálogo -->
                <p
                    id="mensajeCatalogoServicio"
                    class="small mb-3"
                    role="status"
                    aria-live="polite"
                    hidden
                ></p>

                <div class="row align-items-end mb-4">

                    <div class="col-md-6 mb-3 mb-md-0">

                        <label for="filtroCatalogoServicio" class="form-label">
                            Filtrar por servicio
                        </label>

                        <select
                            class="form-select templo-input"
                            id="filtroCatalogoServicio"
                        >
                            <option value="">Todos los trabajos</option>
                            <option value="Soft Gel">Soft Gel</option>
                            <option value="Polygel">Polygel</option>
                            <option value="Capping">Capping</option>
                            <option value="Rusa">Rusa</option>
                            <option value="Pedicura">Pedicura</option>
                        </select>

                    </div>

                    <div class="col-md-6 text-md-end">
                        <small class="templo-help-text">
                            Solo se mostrarán las fotografías marcadas como destacadas.
                        </small>
                    </div>

                </div>

                <!-- Fotografías cargadas por el JavaScript del módulo -->
                <div class="row g-4" id="catalogoTrabajosGrid">

                    <div
                        class="col-12 text-center d-none"
                        id="catalogoTrabajosVacio"
                    >
                        <i class="bi bi-images" aria-hidden="true"></i>

                        <p class="mb-0 mt-2">
                            No hay trabajos destacados para mostrar.
                        </p>
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle" aria-hidden="true"></i>
                    Cerrar
                </button>
            </div>

        </div>

    </div>
</div>


<!-- ===================================================== -->
<!-- DETALLES DEL SERVICIO -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalDetalleServicio"
    tabindex="-1"
    aria-labelledby="detalleServicioTitulo"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content templo-modal">

            <div class="modal-header">

                <h5 class="modal-title" id="detalleServicioTitulo">
                    Detalle del servicio
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>

            <div class="modal-body">

                <!-- Fotografía y datos del servicio consultado -->
                <div class="row g-4">

                    <div class="col-md-6">

                        <img
                            id="detalleServicioImagen"
                            src="Assets/img/LogoTR.png"
                            alt="Resultado del servicio"
                            class="img-fluid rounded w-100 object-fit-contain d-none"
                            style="max-height: 60vh;"
                        >

                        <div id="detalleServicioSinImagen" class="registro-preview-vacio">
                            <i class="bi bi-image" aria-hidden="true"></i>
                            <span>Fotografía no disponible</span>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <dl class="mb-0">

                            <dt>Fecha de atención</dt>
                            <dd id="detalleServicioFecha">—</dd>

                            <dt>Especialista</dt>
                            <dd id="detalleServicioEspecialista">—</dd>

                            <dt>Precio aplicado</dt>
                            <dd id="detalleServicioPrecio">—</dd>

                            <dt>Nota técnica</dt>
                            <dd
                                id="detalleServicioNota"
                                class="text-break mb-0"
                                style="white-space: pre-wrap;"
                            >—</dd>

                        </dl>

                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle" aria-hidden="true"></i>
                    Cerrar
                </button>
            </div>

        </div>

    </div>
</div>


<!-- ===================================================== -->
<!-- CONFIRMACIÓN DE LAS ACCIONES -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalConfirmacionRegistroServicio"
    tabindex="-1"
    aria-labelledby="tituloConfirmacionRegistroServicio"
    aria-describedby="textoConfirmacionRegistroServicio"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content templo-modal">

            <div class="modal-header">

                <h5 class="modal-title" id="tituloConfirmacionRegistroServicio">
                    <i class="bi bi-question-circle" aria-hidden="true"></i>
                    Confirmar acción
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>

            <div class="modal-body">
                <p id="textoConfirmacionRegistroServicio" class="mb-0"></p>
            </div>

            <div class="modal-footer">

                <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button
                    type="button"
                    class="btn-templo-primary"
                    id="btnConfirmarRegistroServicio"
                >
                    Confirmar
                </button>

            </div>

        </div>

    </div>
</div>


<!-- ===================================================== -->
<!-- PLANTILLA DE IMPRESIÓN DEL HISTORIAL -->
<!-- ===================================================== -->

<template id="plantillaImpresionHistorialServicio">

    <style>
        @page {
            size: A4 portrait;
            margin: 14mm;
        }

        body {
            margin: 0;
            color: #4a3c31;
            background: #fff;
            font: 11pt Arial, sans-serif;
        }

        .historial-encabezado {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 12px;
            border-bottom: 2px solid #d8a7b1;
        }

        .historial-encabezado img {
            width: 64px;
            height: 64px;
            object-fit: contain;
        }

        h1 {
            margin: 0 0 5px;
            color: #b89344;
            font-size: 19pt;
        }

        h2 {
            margin: 18px 0 10px;
            font-size: 14pt;
        }

        p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9pt;
        }

        thead {
            display: table-header-group;
        }

        th, td {
            padding: 8px;
            border: 1px solid #e8ced8;
            vertical-align: top;
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        th {
            background: #e8ced8;
            text-align: left;
        }

        tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        td img {
            display: block;
            width: 80px;
            max-width: 100%;
            height: 60px;
            object-fit: cover;
        }
    </style>

    <section>

        <header class="historial-encabezado">

            <img
                src="Assets/img/LogoTR.png"
                alt="Templo Rosa"
                width="64"
                height="64"
            >

            <div>
                <h1>Templo Rosa</h1>
                <p>Nails Studio</p>
            </div>

        </header>

        <h2>Historial de servicios</h2>

        <p>
            <strong>Clienta:</strong>
            <span data-impresion-clienta></span>
        </p>

        <table>

            <colgroup>
                <col style="width: 15%;">
                <col style="width: 18%;">
                <col style="width: 23%;">
                <col style="width: 16%;">
                <col style="width: 28%;">
            </colgroup>

            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Foto</th>
                    <th>Servicio realizado</th>
                    <th>Precio aplicado</th>
                    <th>Nota técnica</th>
                </tr>
            </thead>

            <tbody data-impresion-filas></tbody>

        </table>

    </section>

</template>


<!-- Documento independiente utilizado para imprimir el historial -->
<iframe
    id="marcoImpresionHistorialServicio"
    title="Impresión del historial de servicios"
    src="about:blank"
    width="0"
    height="0"
    tabindex="-1"
    aria-hidden="true"
    style="position: fixed; left: 0; bottom: 0; border: 0;"
></iframe>


<!-- ===================================================== -->
<!-- SCRIPT Y CIERRE DE LA VISTA -->
<!-- ===================================================== -->

<?php

$scriptVista = 'Assets/js/registro-servicio.js?v=2';

require_once __DIR__ . '/Layout/Footer.php';

?>