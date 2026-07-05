<?php

$tituloPagina = 'Gestión de Insumos';

require_once __DIR__ . '/Layout/Header.php';

?>

<section class="module-card clientes-module insumos-module">

    <div class="module-header">
        <h2>
            <i class="bi bi-box2-heart"></i>
            Gestión de Insumos
        </h2>

        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn-templo-secondary">
                <i class="bi bi-file-earmark-pdf"></i>
                PDF
            </button>

            <button
                type="button"
                class="btn-templo-primary"
                data-bs-toggle="modal"
                data-bs-target="#modalNuevoInsumo"
            >
                <i class="bi bi-plus-circle"></i>
                Nuevo Insumo
            </button>
        </div>
    </div>

    <div class="table-zone">
        <div class="templo-table-wrapper">
            <div class="table-responsive">

                <table
                    id="tablaInsumos"
                    class="table table-hover templo-table w-100"
                >
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Descripción</th>
                            <th>Presentación</th>
                            <th>Stock actual</th>
                            <th>Stock mínimo</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

</section>


<!-- Modal Nuevo Insumo -->
<div
    class="modal fade"
    id="modalNuevoInsumo"
    tabindex="-1"
    aria-labelledby="modalNuevoInsumoLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">

            <form action="Index.php?url=insumos" method="POST">
                <input type="hidden" name="accion" value="registrar">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoInsumoLabel">
                        <i class="bi bi-plus-circle"></i>
                        Registrar Nuevo Insumo
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">
                                Nombre del insumo
                             <span class="required-mark" aria-hidden="true">*</span></label>

                            <input
                                type="text"
                                name="nombre_insumo"
                                class="form-control templo-input"
                                placeholder="Ingrese el nombre del insumo"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Presentación
                            </label>

                            <input
                                type="text"
                                name="presentacion"
                                class="form-control templo-input"
                                placeholder="Ingrese la presentación"
                            >
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">
                                Descripción
                            </label>

                            <textarea
                                name="descripcion"
                                class="form-control templo-input"
                                rows="3"
                                placeholder="Ingrese la descripción del insumo"
                            ></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Stock actual
                             <span class="required-mark" aria-hidden="true">*</span></label>

                            <input
                                type="number"
                                name="stock_actual"
                                class="form-control templo-input"
                                min="0"
                                value="0"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Stock mínimo
                             <span class="required-mark" aria-hidden="true">*</span></label>

                            <input
                                type="number"
                                name="stock_minimo"
                                class="form-control templo-input"
                                min="0"
                                value="0"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Fecha de vencimiento
                            </label>

                            <input
                                type="date"
                                name="fecha_vencimiento"
                                class="form-control templo-input"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Estado
                             <span class="required-mark" aria-hidden="true">*</span></label>

                            <select
                                name="estado_insumo"
                                class="form-select templo-input"
                                required
                            >
                                <option value="ACTIVO" selected>
                                    ACTIVO
                                </option>

                                <option value="INACTIVO">
                                    INACTIVO
                                </option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="submit"
                        class="btn-templo-primary"
                    >
                        <i class="bi bi-save"></i>
                        Guardar
                    </button>

                    <button
                        type="button"
                        class="btn-templo-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- Modal Editar Insumo -->
<div
    class="modal fade"
    id="modalEditarInsumo"
    tabindex="-1"
    aria-labelledby="modalEditarInsumoLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">

            <form action="Index.php?url=insumos" method="POST">
                <input type="hidden" name="accion" value="modificar">

                <input
                    type="hidden"
                    name="id_insumo"
                    id="editarIdInsumo"
                >

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarInsumoLabel">
                        <i class="bi bi-pencil-square"></i>
                        Editar Insumo
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">
                                Nombre del insumo
                             <span class="required-mark" aria-hidden="true">*</span></label>

                            <input
                                type="text"
                                name="nombre_insumo"
                                id="editarNombreInsumo"
                                class="form-control templo-input"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Presentación
                            </label>

                            <input
                                type="text"
                                name="presentacion"
                                id="editarPresentacionInsumo"
                                class="form-control templo-input"
                            >
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">
                                Descripción
                            </label>

                            <textarea
                                name="descripcion"
                                id="editarDescripcionInsumo"
                                class="form-control templo-input"
                                rows="3"
                            ></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Stock actual
                             <span class="required-mark" aria-hidden="true">*</span></label>

                            <input
                                type="number"
                                name="stock_actual"
                                id="editarStockActual"
                                class="form-control templo-input"
                                min="0"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Stock mínimo
                             <span class="required-mark" aria-hidden="true">*</span></label>

                            <input
                                type="number"
                                name="stock_minimo"
                                id="editarStockMinimo"
                                class="form-control templo-input"
                                min="0"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Fecha de vencimiento
                            </label>

                            <input
                                type="date"
                                name="fecha_vencimiento"
                                id="editarFechaVencimiento"
                                class="form-control templo-input"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Estado
                             <span class="required-mark" aria-hidden="true">*</span></label>

                            <select
                                name="estado_insumo"
                                id="editarEstadoInsumo"
                                class="form-select templo-input"
                                required
                            >
                                <option value="ACTIVO">
                                    ACTIVO
                                </option>

                                <option value="INACTIVO">
                                    INACTIVO
                                </option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="submit"
                        class="btn-templo-primary"
                    >
                        <i class="bi bi-save"></i>
                        Guardar Cambios
                    </button>

                    <button
                        type="button"
                        class="btn-templo-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- Modal Desactivar Insumo -->
<div
    class="modal fade"
    id="modalDesactivarInsumo"
    tabindex="-1"
    aria-labelledby="modalDesactivarInsumoLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content templo-modal status-modal">

            <form action="Index.php?url=insumos" method="POST">
                <input type="hidden" name="accion" value="desactivar">

                <input
                    type="hidden"
                    name="id_insumo"
                    id="desactivarIdInsumo"
                >

                <div class="modal-header">
                    <h5 class="modal-title" id="modalDesactivarInsumoLabel">
                        <i class="bi bi-trash-fill"></i>
                        Confirmar desactivación
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>
                </div>

                <div class="modal-body text-center">
                    <p class="status-question">
                        ¿Deseas desactivar este insumo?
                    </p>

                    <p
                        class="status-client-name"
                        id="desactivarNombreInsumo"
                    ></p>

                    <p class="status-warning">
                        El insumo no se eliminará de la base de datos.
                    </p>
                </div>

                <div class="modal-footer">
                    <button
                        type="submit"
                        class="btn-templo-danger"
                    >
                        <i class="bi bi-trash-fill"></i>
                        Sí, desactivar
                    </button>

                    <button
                        type="button"
                        class="btn-templo-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<?php
$scriptVista = 'Assets/js/insumos.js';

require_once __DIR__ . '/Layout/Footer.php';
?>