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

            <button
                type="button"
                class="btn-templo-secondary"
            >
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
                            <th>Presentación</th>
                            <th>Tipo de control</th>
                            <th>Stock actual</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach (($listaInsumos ?? []) as $insumo) { ?>

                            <?php

                            $fechaVencimiento = trim(
                                (string) ($insumo['fecha_vencimiento'] ?? '')
                            );

                            $fechaVencimientoTexto = $fechaVencimiento !== ''
                                ? $fechaVencimiento
                                : 'No aplica';

                            $descripcionInsumo = trim(
                                (string) ($insumo['descripcion'] ?? '')
                            );

                            $descripcionInsumoTexto = $descripcionInsumo !== ''
                                ? $descripcionInsumo
                                : 'Sin descripción';

                            $tipoControlInsumo = strtoupper(
                                trim(
                                    (string) (
                                        $insumo['tipo_control']
                                        ?? 'UNITARIO'
                                    )
                                )
                            );

                            $tipoControlInsumoTexto =
                                $tipoControlInsumo === 'POR_ENVASE'
                                    ? 'Por envase'
                                    : 'Consumo unitario';

                            $estadoInsumo = strtoupper(
                                trim(
                                    (string) (
                                        $insumo['estado_insumo']
                                        ?? ''
                                    )
                                )
                            );

                            $claseEstadoInsumo =
                                $estadoInsumo === 'INACTIVO'
                                    ? 'estado-registro-inactivo'
                                    : 'estado-registro-activo';

                            ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $insumo['nombre_insumo'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $insumo['presentacion'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $tipoControlInsumoTexto,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        (string) (
                                            $insumo['stock_actual']
                                            ?? 0
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $fechaVencimientoTexto,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <span
                                        class="badge <?php
                                            echo $claseEstadoInsumo;
                                        ?>"
                                    >
                                        <?php echo htmlspecialchars(
                                            $estadoInsumo,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="btn-group-actions">

                                        <!-- Botón Ver detalles -->
                                        <button
                                            type="button"
                                            class="btn-action btn-detail btnDetalleInsumo"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDetalleInsumo"

                                            data-nombre-insumo="<?php
                                                echo htmlspecialchars(
                                                    $insumo['nombre_insumo']
                                                    ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-presentacion="<?php
                                                echo htmlspecialchars(
                                                    $insumo['presentacion']
                                                    ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-tipo-control="<?php
                                                echo htmlspecialchars(
                                                    $tipoControlInsumo,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-descripcion="<?php
                                                echo htmlspecialchars(
                                                    $descripcionInsumoTexto,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-stock-actual="<?php
                                                echo htmlspecialchars(
                                                    (string) (
                                                        $insumo['stock_actual']
                                                        ?? 0
                                                    ),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-stock-minimo="<?php
                                                echo htmlspecialchars(
                                                    (string) (
                                                        $insumo['stock_minimo']
                                                        ?? 0
                                                    ),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-fecha-vencimiento-texto="<?php
                                                echo htmlspecialchars(
                                                    $fechaVencimientoTexto,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-estado-insumo="<?php
                                                echo htmlspecialchars(
                                                    $estadoInsumo,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            title="Ver detalles del insumo"
                                            aria-label="Ver detalles del insumo"
                                        >
                                            <span
                                                class="cliente-detalle-icono"
                                                aria-hidden="true"
                                            >
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                >
                                                    <path
                                                        d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        fill="none"
                                                    />

                                                    <path
                                                        d="M12 16C10 14.8 8.5 13.5 8.5 11.8C8.5 10.5 9.5 9.5 10.8 9.5C11.5 9.5 12 9.9 12 10.4C12 9.9 12.5 9.5 13.2 9.5C14.5 9.5 15.5 10.5 15.5 11.8C15.5 13.5 14 14.8 12 16Z"
                                                        fill="currentColor"
                                                    />
                                                </svg>
                                            </span>
                                        </button>


                                        <!-- Botón Editar -->
                                        <button
                                            type="button"
                                            class="btn-action btn-edit btnEditarInsumo"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarInsumo"

                                            data-id-insumo="<?php
                                                echo htmlspecialchars(
                                                    (string) (
                                                        $insumo['id_insumo']
                                                        ?? ''
                                                    ),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-nombre-insumo="<?php
                                                echo htmlspecialchars(
                                                    $insumo['nombre_insumo']
                                                    ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-descripcion="<?php
                                                echo htmlspecialchars(
                                                    $descripcionInsumo,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-presentacion="<?php
                                                echo htmlspecialchars(
                                                    $insumo['presentacion']
                                                    ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-tipo-control="<?php
                                                echo htmlspecialchars(
                                                    $tipoControlInsumo,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-stock-actual="<?php
                                                echo htmlspecialchars(
                                                    (string) (
                                                        $insumo['stock_actual']
                                                        ?? 0
                                                    ),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-stock-minimo="<?php
                                                echo htmlspecialchars(
                                                    (string) (
                                                        $insumo['stock_minimo']
                                                        ?? 0
                                                    ),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-fecha-vencimiento="<?php
                                                echo htmlspecialchars(
                                                    $fechaVencimiento,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-estado-insumo="<?php
                                                echo htmlspecialchars(
                                                    $estadoInsumo,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            title="Editar insumo"
                                            aria-label="Editar insumo"
                                        >
                                            <i
                                                class="bi bi-pencil-fill"
                                                aria-hidden="true"
                                            ></i>
                                        </button>


                                        <!-- Botón Desactivar -->
                                        <button
                                            type="button"
                                            class="btn-action btn-delete btnDesactivarInsumo"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDesactivarInsumo"

                                            data-id-insumo="<?php
                                                echo htmlspecialchars(
                                                    (string) (
                                                        $insumo['id_insumo']
                                                        ?? ''
                                                    ),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            data-nombre-insumo="<?php
                                                echo htmlspecialchars(
                                                    $insumo['nombre_insumo']
                                                    ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>"

                                            title="Desactivar insumo"
                                            aria-label="Desactivar insumo"
                                        >
                                            <i
                                                class="bi bi-trash-fill"
                                                aria-hidden="true"
                                            ></i>
                                        </button>

                                    </div>
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>

</section>


<!-- ===================================================== -->
<!-- MODAL DETALLE DEL INSUMO -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalDetalleInsumo"
    tabindex="-1"
    aria-labelledby="modalDetalleInsumoLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="modalDetalleInsumoLabel"
                >
                    <span
                        class="cliente-detalle-icono"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <path
                                d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                fill="none"
                            />

                            <path
                                d="M12 16C10 14.8 8.5 13.5 8.5 11.8C8.5 10.5 9.5 9.5 10.8 9.5C11.5 9.5 12 9.9 12 10.4C12 9.9 12.5 9.5 13.2 9.5C14.5 9.5 15.5 10.5 15.5 11.8C15.5 13.5 14 14.8 12 16Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    Detalles del Insumo
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>


            <div class="modal-body">

                <div class="row g-4">

                    <div class="col-md-6">

                        <p class="fw-semibold mb-1">
                            Nombre del insumo
                        </p>

                        <p
                            class="mb-0"
                            id="detalleNombreInsumo"
                        ></p>

                    </div>


                    <div class="col-md-6">

                        <p class="fw-semibold mb-1">
                            Presentación
                        </p>

                        <p
                            class="mb-0"
                            id="detallePresentacionInsumo"
                        ></p>

                    </div>


                    <div class="col-md-12">

                        <p class="fw-semibold mb-1">
                            Descripción
                        </p>

                        <p
                            class="mb-0"
                            id="detalleDescripcionInsumo"
                        ></p>

                    </div>


                    <div class="col-md-4">

                        <p class="fw-semibold mb-1">
                            Stock actual
                        </p>

                        <p
                            class="mb-0"
                            id="detalleStockActual"
                        ></p>

                    </div>


                    <div class="col-md-4">

                        <p class="fw-semibold mb-1">
                            Stock mínimo
                        </p>

                        <p
                            class="mb-0"
                            id="detalleStockMinimo"
                        ></p>

                    </div>


                    <div class="col-md-4">

                        <p class="fw-semibold mb-1">
                            Fecha de vencimiento
                        </p>

                        <p
                            class="mb-0"
                            id="detalleFechaVencimiento"
                        ></p>

                    </div>


                    <div class="col-md-6">

                        <p class="fw-semibold mb-1">
                            Tipo de control
                        </p>

                        <p
                            class="mb-0"
                            id="detalleTipoControl"
                        ></p>

                    </div>


                    <div class="col-md-6">

                        <p class="fw-semibold mb-2">
                            Estado
                        </p>

                        <span
                            class="badge estado-registro-activo"
                            id="detalleEstadoInsumo"
                        ></span>

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


<!-- ===================================================== -->
<!-- MODAL NUEVO INSUMO -->
<!-- ===================================================== -->

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

                <input
                    type="hidden"
                    name="accion"
                    value="registrar"
                >

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="modalNuevoInsumoLabel"
                    >
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

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                type="text"
                                name="nombre_insumo"
                                class="form-control templo-input"
                                placeholder="Ingrese el nombre del insumo"
                                maxlength="100"
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
                                maxlength="80"
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
                                maxlength="255"
                                placeholder="Ingrese la descripción del insumo"
                            ></textarea>

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                Stock actual

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                type="number"
                                name="stock_actual"
                                class="form-control templo-input"
                                min="0"
                                placeholder="0"
                                required
                            >

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                Stock mínimo

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                type="number"
                                name="stock_minimo"
                                class="form-control templo-input"
                                min="0"
                                placeholder="0"
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
                                Tipo de control

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <select
                                name="tipo_control"
                                class="form-select templo-input"
                                required
                            >
                                <option value="UNITARIO">
                                    Consumo unitario
                                </option>

                                <option value="POR_ENVASE">
                                    Por envase
                                </option>
                            </select>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Estado
                            </label>

                            <input
                                type="text"
                                name="estado_insumo"
                                class="form-control templo-input"
                                value="ACTIVO"
                                readonly
                            >

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


<!-- ===================================================== -->
<!-- MODAL EDITAR INSUMO -->
<!-- ===================================================== -->

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

                <input
                    type="hidden"
                    name="accion"
                    value="modificar"
                >

                <input
                    type="hidden"
                    name="id_insumo"
                    id="editarIdInsumo"
                >

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="modalEditarInsumoLabel"
                    >
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

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                type="text"
                                name="nombre_insumo"
                                id="editarNombreInsumo"
                                class="form-control templo-input"
                                maxlength="100"
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
                                maxlength="80"
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
                                maxlength="255"
                            ></textarea>

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                Stock actual

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

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

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

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
                                Tipo de control

                                <span
                                    class="required-mark"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            </label>

                            <select
                                name="tipo_control"
                                id="editarTipoControl"
                                class="form-select templo-input"
                                required
                            >
                                <option value="UNITARIO">
                                    Consumo unitario
                                </option>

                                <option value="POR_ENVASE">
                                    Por envase
                                </option>
                            </select>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Estado
                            </label>

                            <input
                                type="text"
                                name="estado_insumo"
                                id="editarEstadoInsumo"
                                class="form-control templo-input"
                                readonly
                            >

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


<!-- ===================================================== -->
<!-- MODAL DESACTIVAR INSUMO -->
<!-- ===================================================== -->

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

                <input
                    type="hidden"
                    name="accion"
                    value="desactivar"
                >

                <input
                    type="hidden"
                    name="id_insumo"
                    id="desactivarIdInsumo"
                >

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="modalDesactivarInsumoLabel"
                    >
                        <i class="bi bi-exclamation-triangle"></i>
                        Desactivar Insumo
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
                        El insumo permanecerá guardado en la base de datos.
                    </p>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn-templo-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn-templo-danger"
                    >
                        <i class="bi bi-check-circle"></i>
                        Confirmar
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