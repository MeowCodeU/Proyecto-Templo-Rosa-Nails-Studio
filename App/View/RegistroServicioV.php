<?php

$tituloPagina = 'Registro de Servicios';

require_once __DIR__ . '/Layout/Header.php';

?>

<section class="module-card registro-servicio-module">

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
            <path d="M18 31H46L44 50C43.7 53 41.4 55 38.4 55H25.6C22.6 55 20.3 53 20 50L18 31Z"></path>
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
                <i class="bi bi-arrow-left-circle"></i>
                Volver a la lista
            </button>

            <button
                class="btn-templo-secondary"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#modalCatalogoTrabajos"
            >
                <i class="bi bi-images"></i>
                Catálogo
            </button>

            <button
            class="btn-templo-primary"
            type="button"
            id="btnRefrescarRegistroServicio"
            >

                <i class="bi bi-arrow-clockwise"></i>
                Refrescar
            </button>

        </div>
    </div>


    <!-- Lista de agendamientos -->
    <div
        id="panelAgendamientosServicio"
        class="registro-panel"
    >

        <div class="templo-table-wrapper registro-servicio-table-wrapper">

            <div class="registro-table-head">
                <h5>
                    <i class="bi bi-calendar2-heart"></i>
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

                    <tbody id="tbodyServiciosPendientes"></tbody>
                </table>

            </div>
        </div>

    </div>


    <!-- Registro del servicio seleccionado -->
    <div
        id="panelRegistroServicio"
        class="registro-panel d-none"
    >

        <input
            type="hidden"
            id="idAgendamientoServicio"
            name="id_agendamiento"
            value=""
        >

        <div class="registro-atencion-layout">


            <!-- Ficha de la clienta -->
            <div class="registro-card">

                <div class="registro-card-header">
                    <i class="bi bi-person-hearts"></i>
                    Ficha de la Clienta
                </div>

                <div class="registro-card-body text-center">

                    <div class="mb-4">
                        <h5
                            class="registro-ficha-nombre mb-0"
                            id="fichaNombreClienta"
                        >
                            —
                        </h5>
                    </div>

                    <div class="registro-ficha-list">

                        <p>
                            <strong>
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Alergias:
                            </strong>

                            <br>

                            <span id="fichaAlergias">
                                —
                            </span>
                        </p>

                        <p>
                            <strong>
                                <i class="bi bi-chat-heart me-1"></i>
                                Nota técnica del último servicio:
                            </strong>

                            <br>

                            <span id="fichaNotaTecnicaAnterior">
                                —
                            </span>
                        </p>

                        <p>
                            <strong>
    <span
        class="registro-icono-servicio"
        aria-hidden="true"
    >
        <svg viewBox="0 0 64 64">
            <rect
                x="24"
                y="6"
                width="16"
                height="20"
                rx="2"
            ></rect>

            <path d="M28 10V22"></path>
            <path d="M32 10V22"></path>
            <path d="M36 10V22"></path>

            <path d="M24 28H29"></path>
            <path d="M35 28H40"></path>

            <path d="M29 28C29 30 28 31 26 31"></path>
            <path d="M35 28C35 30 36 31 38 31"></path>

            <path
                d="M18 31H46L44 50C43.7 53 41.4 55 38.4 55H25.6C22.6 55 20.3 53 20 50L18 31Z"
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

                            <span id="fichaUltimoServicio">
                                —
                            </span>
                        </p>

                        <p>
                            <strong>
                                <i class="bi bi-calendar2-heart"></i>
                                Última atención:
                            </strong>

                            <br>

                            <span id="fichaUltimaAtencion">
                                —
                            </span>
                        </p>

                        <hr>

                        <p>
                            <strong>
                                <i class="bi bi-calendar2-heart-fill"></i>
                                Servicios agendados:
                            </strong>

                            <br>

                            <span id="fichaServiciosAgendados">
                                —
                            </span>
                        </p>

                        <p>
                            <strong>
                                <i class="bi bi-cash-coin me-1"></i>
                                Monto del agendamiento:
                            </strong>

                            <br>

                            <span id="fichaMontoAgendamiento">
                                —
                            </span>
                        </p>

                        <p>
                            <strong>
                                <i class="bi bi-person-badge me-1"></i>
                                Especialista asignada:
                            </strong>

                            <br>

                            <span id="fichaEspecialistaAsignada">
                                —
                            </span>
                        </p>

                    </div>

                </div>
            </div>


            <!-- Pestañas -->
            <div class="registro-card registro-tabs">

                <div class="registro-card-header">
                    <i class="bi bi-clipboard-heart-fill"></i>
                    Registro del Servicio Prestado
                </div>

                <div class="registro-card-body">

                    <ul
                        class="nav nav-tabs"
                        id="registroServicioTabs"
                        role="tablist"
                    >

                        <li
                            class="nav-item"
                            role="presentation"
                        >
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
            <path d="M18 31H46L44 50C43.7 53 41.4 55 38.4 55H25.6C22.6 55 20.3 53 20 50L18 31Z"></path>
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

                        <li
                            class="nav-item"
                            role="presentation"
                        >
                            <button class="nav-link" 
                            id="insumos-utilizados-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#insumos-utilizados" 
                            type="button" 
                            role="tab" 
                            aria-controls="insumos-utilizados" 
                            aria-selected="false" > 
                            <i class="bi bi-box2-heart-fill" 
                            aria-hidden="true">
                        </i> Insumos utilizados 
                    </button>
                        </li>

                        <li
                            class="nav-item"
                            role="presentation"
                        >
                            <button class="nav-link" 
                            id="historial-servicios-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#historial-servicios" 
                            type="button" 
                            role="tab" 
                            aria-controls="historial-servicios" 
                            aria-selected="false" > 
                            <i class="bi bi-postcard-heart" 
                            aria-hidden="true">
                        </i> Historial </button>
                        </li>

                    </ul>


                    <div
                        class="tab-content mt-4"
                        id="registroServicioTabsContent"
                    >


                        <!-- Servicio realizado -->
                        <div
                            class="tab-pane fade show active"
                            id="servicio-realizado"
                            role="tabpanel"
                            aria-labelledby="servicio-realizado-tab"
                            tabindex="0"
                        >

                            <form
                                id="formServicioRealizado"
                                method="POST"
                                enctype="multipart/form-data"
                            >

                                <input
                                    type="hidden"
                                    name="accion"
                                    value="guardarServicio"
                                >

                                <input
                                    type="hidden"
                                    name="id_agendamiento"
                                    id="servicioIdAgendamiento"
                                    value=""
                                >

                                <div class="row">

                                    <div class="col-md-6 mb-3">

                                        <label
                                            for="servicioRealizado"
                                            class="form-label"
                                        >
                                            Servicio realizado
                                         <span class="required-mark" aria-hidden="true">*</span></label>

                                        <select
                                            class="form-select templo-input"
                                            id="servicioRealizado"
                                            name="id_detalle_agendamiento"
                                            required
                                        >
                                            <option value="">
                                                Seleccionar servicio agendado...
                                            </option>
                                        </select>

                                        <small class="templo-help-text">
                                            Seleccione el servicio al que pertenecerán la nota técnica y la fotografía.
                                        </small>

                                    </div>


                                    <div class="col-md-6 mb-3">

                                        <label
                                            for="totalServicio"
                                            class="form-label"
                                        >
                                            Total a cobrar
                                        </label>

                                        <input
                                            type="number"
                                            class="form-control templo-input"
                                            id="totalServicio"
                                            name="monto_total"
                                            min="0"
                                            step="0.01"
                                            value=""
                                        >

                                    </div>


                                    <div class="col-md-6 mb-3">

                                        <label
                                            for="fechaServicio"
                                            class="form-label"
                                        >
                                            Fecha de atención
                                        </label>

                                        <input
                                            type="date"
                                            class="form-control templo-input"
                                            id="fechaServicio"
                                            name="fecha_atencion"
                                        >

                                    </div>


                                    <div class="col-12 mb-3">

                                        <label
                                            for="notaTecnicaServicio"
                                            class="form-label"
                                        >
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


                                    <div class="col-md-6 mb-3">

                                        <label
                                            for="fotoResultadoServicio"
                                            class="form-label"
                                        >
                                            Fotografía del resultado
                                            <i class="bi bi-camera2"></i>
                                        </label>

                                        <input
                                            type="file"
                                            class="form-control templo-input"
                                            id="fotoResultadoServicio"
                                            name="foto_servicio"
                                            accept="image/*"
                                        >

                                        <div
                                            id="previewResultadoVacio"
                                            class="registro-resultado-preview registro-preview-vacio"
                                        >
                                            <i class="bi bi-image"></i>

                                            <span>
                                                Sin fotografía seleccionada
                                            </span>
                                        </div>

                                        <img
                                            id="previewResultadoServicio"
                                            src=""
                                            alt="Vista previa del resultado"
                                            class="registro-resultado-preview d-none"
                                        >

                                        <small class="templo-help-text">
                                            La fotografía quedará relacionada con el servicio seleccionado.
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
                                            El catálogo podrá filtrarla utilizando palabras contenidas en el nombre del servicio.
                                        </small>

                                    </div>

                                </div>


                                <div class="d-flex justify-content-end gap-2 mt-3 flex-wrap">

                                    <button
                                        type="button"
                                        class="btn-templo-secondary"
                                        id="btnCancelarServicio"
                                    >
                                        <i class="bi bi-x-circle"></i>
                                        Cancelar
                                    </button>

                                    <button
                                        type="button"
                                        class="btn-templo-primary"
                                        id="btnGuardarServicio"
                                    >
                                        <i class="bi bi-save"></i>
                                        Guardar servicio
                                    </button>

                                </div>

                            </form>

                        </div>


                        <!-- Insumos utilizados -->
                        <div
                            class="tab-pane fade"
                            id="insumos-utilizados"
                            role="tabpanel"
                            aria-labelledby="insumos-utilizados-tab"
                            tabindex="0"
                        >

                            <form
                                id="formInsumosUtilizados"
                                method="POST"
                            >

                                <input
                                    type="hidden"
                                    name="accion"
                                    value="guardarInsumos"
                                >

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
                                    ></i>

                                    Registre los insumos utilizados durante el servicio.
                                    Al guardar, las cantidades serán descontadas del inventario.
                                </p>


                                <div class="row">

                                    <div class="col-md-5 mb-3">

                                        <label
                                            for="insumoUtilizado"
                                            class="form-label"
                                        >
                                            Insumo utilizado
                                        </label>

                                        <select
                                            class="form-select templo-input"
                                            id="insumoUtilizado"
                                            name="id_insumo"
                                        >
                                            <option value="">
                                                Seleccionar insumo...
                                            </option>
                                        </select>

                                    </div>


                                    <div class="col-md-2 mb-3">

                                        <label
                                            for="cantidadInsumo"
                                            class="form-label"
                                        >
                                            Cantidad
                                        </label>

                                        <input
                                            type="number"
                                            class="form-control templo-input"
                                            id="cantidadInsumo"
                                            name="cantidad_utilizada"
                                            min="0.01"
                                            step="0.01"
                                            placeholder="0"
                                        >

                                    </div>


                                    <div class="col-md-3 mb-3">

                                        <label
                                            for="presentacionInsumo"
                                            class="form-label"
                                        >
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

                                        <label
                                            for="stockDisponibleInsumo"
                                            class="form-label"
                                        >
                                            Disponible
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control templo-input"
                                            id="stockDisponibleInsumo"
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
                                            <i class="bi bi-plus-circle"></i>
                                            Agregar insumo
                                        </button>

                                    </div>

                                </div>


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
                                                <th class="text-center">
                                                    Acción
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody id="tbodyInsumosSeleccionados">
                                            <tr>
                                                <td
                                                    colspan="4"
                                                    class="text-center"
                                                >
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
                                        <i class="bi bi-save"></i>
                                        Guardar insumos utilizados
                                    </button>

                                </div>

                            </form>

                        </div>


                        <!-- Historial -->
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
                                    ></i>

                                    Historial de servicios
                                </h5>

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
        <i class="bi bi-printer"></i>
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
                                            <th>Total</th>
                                            <th>Nota técnica</th>
                                            <th class="text-center">
                                                Acción
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody id="tbodyHistorialServicios">
                                        <tr>
                                            <td
                                                colspan="6"
                                                class="text-center"
                                            >
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
                                    <i class="bi bi-check-circle"></i>
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


<!-- Catálogo de trabajos -->
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

                <h5
                    class="modal-title"
                    id="modalCatalogoTrabajosLabel"
                >
                    <i class="bi bi-images"></i>
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

                <div class="row align-items-end mb-4">

                    <div class="col-md-6 mb-3 mb-md-0">

                        <label
                            for="filtroCatalogoServicio"
                            class="form-label"
                        >
                            Filtrar por servicio
                        </label>

                        <select
                            class="form-select templo-input"
                            id="filtroCatalogoServicio"
                        >
                            <option value="">
                                Todos los trabajos
                            </option>

                            <option value="Soft Gel">
                                Soft Gel
                            </option>

                            <option value="Polygel">
                                Polygel
                            </option>

                            <option value="Capping">
                                Capping
                            </option>

                            <option value="Rusa">
                                Rusa
                            </option>

                            <option value="Pedicura">
                                Pedicura
                            </option>
                        </select>

                    </div>


                    <div class="col-md-6 text-md-end">

                        <small class="templo-help-text">
                            Solo se mostrarán las fotografías marcadas como destacadas.
                        </small>

                    </div>

                </div>


                <div
                    class="row g-4"
                    id="catalogoTrabajosGrid"
                >

                    <div
                        class="col-12 text-center"
                        id="catalogoTrabajosVacio"
                    >
                        <i class="bi bi-images"></i>

                        <p class="mb-0 mt-2">
                            No hay trabajos destacados para mostrar.
                        </p>
                    </div>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn-templo-secondary"
                    data-bs-dismiss="modal"
                >
                    <i class="bi bi-x-circle"></i>
                    Cerrar
                </button>

            </div>

        </div>
    </div>
</div>


<?php

$scriptVista = 'Assets/js/registro-servicio.js';

require_once __DIR__ . '/Layout/Footer.php';

?>