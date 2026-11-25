<?php

$tituloPagina = 'Gestión de Insumos';

require_once __DIR__ . '/Layout/Header.php';

?>

<!-- ===================================================== -->
<!-- GESTIÓN DE INSUMOS -->
<!-- ===================================================== -->

<section class="module-card clientes-module insumos-module">

    <!-- Encabezado y acciones del módulo -->
    <div class="module-header">
        <h2>
            <i class="bi bi-box2-heart"></i>
            Gestión de Insumos
        </h2>

        <!-- Acciones disponibles -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <label for="filtroEstadoInsumos" class="form-label mb-0">Estado</label>

            <select id="filtroEstadoInsumos" class="form-select templo-filtro-estado w-auto">
                <option value="ACTIVO" selected>Activos</option>
                <option value="INACTIVO">Inactivos</option>
                <option value="TODOS">Todos</option>
            </select>

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

    <!-- Pestañas del módulo de Insumos -->
    <ul class="nav nav-tabs insumos-tabs" id="insumosTabs" role="tablist">

        <!-- Pestaña del listado de insumos -->
        <li class="nav-item" role="presentation">
            <button
                class="nav-link active"
                id="listado-insumos-tab"
                data-bs-toggle="tab"
                data-bs-target="#listado-insumos"
                type="button"
                role="tab"
                aria-controls="listado-insumos"
                aria-selected="true"
            >
                <i class="bi bi-box-seam" aria-hidden="true"></i>
                Insumos
            </button>
        </li>

        <!-- Pestaña del historial de movimientos -->
        <li class="nav-item" role="presentation">
            <button
                class="nav-link"
                id="historial-insumos-tab"
                data-bs-toggle="tab"
                data-bs-target="#historial-insumos"
                type="button"
                role="tab"
                aria-controls="historial-insumos"
                aria-selected="false"
            >
                <i class="bi bi-clock-history" aria-hidden="true"></i>
                Historial de movimientos
            </button>
        </li>

    </ul>

    <!-- Contenido de las pestañas -->
    <div class="tab-content insumos-tab-content" id="insumosTabsContent">

        <!-- ===================================================== -->
        <!-- LISTADO DE INSUMOS -->
        <!-- ===================================================== -->

        <div
            class="tab-pane fade show active"
            id="listado-insumos"
            role="tabpanel"
            aria-labelledby="listado-insumos-tab"
            tabindex="0"
        >

            <!-- Tabla de insumos -->
            <div class="table-zone">
                <div class="templo-table-wrapper">
                    <div class="table-responsive">

                        <table id="tablaInsumos" class="table table-hover templo-table w-100">

                            <!-- Encabezados de la tabla -->
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Presentación</th>
                                    <th>Tipo de control</th>
                                    <th>Disponible / reservas</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>

                            <!-- Filas recibidas desde el controlador -->
                            <tbody>

                                <?php foreach (($listaInsumos ?? []) as $insumo) { ?>

                                    <!-- Preparar los datos y textos de cada insumo -->
                                    <?php

                                    $fechaVencimiento = trim((string) ($insumo['fecha_vencimiento'] ?? ''));
                                    $fechaVencimientoTexto = $fechaVencimiento !== '' ? $fechaVencimiento : 'No aplica';

                                    $descripcionInsumo = trim((string) ($insumo['descripcion'] ?? ''));
                                    $descripcionInsumoTexto = $descripcionInsumo !== '' ? $descripcionInsumo : 'Sin descripción';

                                    $tipoControlInsumo = strtoupper(trim((string) ($insumo['tipo_control'] ?? 'UNITARIO')));
                                    $tipoControlInsumoTexto = $tipoControlInsumo === 'POR_ENVASE' ? 'Por envase' : 'Consumo unitario';

                                    $estadoInsumo = strtoupper(trim((string) ($insumo['estado_insumo'] ?? '')));
                                    $claseEstadoInsumo = $estadoInsumo === 'INACTIVO' ? 'estado-registro-inactivo' : 'estado-registro-activo';

                                    /* ================================================= */
                                    /* COLOR DISCRETO DE LAS EXISTENCIAS */
                                    /* ================================================= */

                                    $stockActualInsumo = (int) ($insumo['stock_actual'] ?? 0);
                                    $stockDisponibleInsumo = (int) ($insumo['stock_disponible'] ?? $stockActualInsumo);
                                    $stockMinimoInsumo = (int) ($insumo['minimo_disponible'] ?? $insumo['stock_minimo'] ?? 0);

                                    if ($stockDisponibleInsumo === 0) {
                                        $estadoStockInsumo = 'AGOTADO';
                                        $etiquetaStockInsumo = 'Agotado';
                                        $claseNumeroStock = 'insumos-stock-agotado';

                                    } elseif ($stockDisponibleInsumo <= $stockMinimoInsumo) {
                                        $estadoStockInsumo = 'BAJO';
                                        $etiquetaStockInsumo = 'Stock bajo';
                                        $claseNumeroStock = 'insumos-stock-bajo';

                                    } else {
                                        $estadoStockInsumo = 'NORMAL';
                                        $etiquetaStockInsumo = 'Disponible';
                                        $claseNumeroStock = 'insumos-stock-disponible';
                                    }

                                    $estadoVencimientoInsumo = strtoupper((string) ($insumo['estado_vencimiento'] ?? 'SIN_FECHA'));
                                    $etiquetaVencimientoInsumo = (string) ($insumo['etiqueta_vencimiento'] ?? 'Sin fecha');
                                    $claseVencimientoInsumo = in_array($estadoVencimientoInsumo, ['VENCIDO', 'URGENTE'], true)
                                        ? 'text-bg-danger'
                                        : ($estadoVencimientoInsumo === 'FECHA_INVALIDA' ? 'text-bg-secondary' : 'text-bg-warning');

                                    ?>

                                    <tr>
                                        <td><?php echo htmlspecialchars($insumo['nombre_insumo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($insumo['presentacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($tipoControlInsumoTexto, ENT_QUOTES, 'UTF-8'); ?></td>

                                        <!-- La cantidad indica el nivel de existencias mediante su color -->
                                        <td data-order="<?php echo $stockDisponibleInsumo; ?>">
                                            <span
                                                class="insumos-stock-numero <?php echo $claseNumeroStock; ?>"
                                                title="<?php echo htmlspecialchars($etiquetaStockInsumo, ENT_QUOTES, 'UTF-8'); ?>"
                                            ><?php echo $stockDisponibleInsumo; ?></span>
                                            <small class="d-block">
                                                Físico: <?= $stockActualInsumo; ?> · Reservado: <?= (int) ($insumo['stock_reservado'] ?? 0); ?>
                                            </small>
                                            <?php if (($insumo['faltante_reserva'] ?? 0) > 0): ?>
                                                <strong class="d-block insumos-stock-agotado">Faltan <?= (int) $insumo['faltante_reserva']; ?> para las reservas</strong>
                                            <?php endif; ?>

                                            <span class="visually-hidden">
                                                <?php echo htmlspecialchars($etiquetaStockInsumo, ENT_QUOTES, 'UTF-8'); ?>:
                                                <?php echo $stockDisponibleInsumo; ?> unidades disponibles.
                                            </span>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($fechaVencimientoTexto, ENT_QUOTES, 'UTF-8'); ?>

                                            <?php if (!in_array($estadoVencimientoInsumo, ['SIN_FECHA', 'VIGENTE'], true)) { ?>
                                                <div class="mt-1">
                                                    <span class="badge <?php echo $claseVencimientoInsumo; ?> insumos-alerta-vencimiento">
                                                        <?php echo htmlspecialchars($etiquetaVencimientoInsumo, ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </div>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <span class="badge <?php echo $claseEstadoInsumo; ?>">
                                                <?php echo htmlspecialchars($estadoInsumo, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <!-- Acciones de la fila -->
                                            <div class="btn-group-actions">

                                                <!-- Botón Ver detalles -->
                                                <button
                                                    type="button"
                                                    class="btn-action btn-detail btnDetalleInsumo"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalDetalleInsumo"
                                                    data-id-insumo="<?php echo htmlspecialchars((string) ($insumo['id_insumo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-nombre-insumo="<?php echo htmlspecialchars($insumo['nombre_insumo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-presentacion="<?php echo htmlspecialchars($insumo['presentacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-tipo-control="<?php echo htmlspecialchars($tipoControlInsumo, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-descripcion="<?php echo htmlspecialchars($descripcionInsumoTexto, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-stock-actual="<?php echo htmlspecialchars((string) ($insumo['stock_actual'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-stock-minimo="<?php echo htmlspecialchars((string) ($insumo['stock_minimo'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-fecha-vencimiento-texto="<?php echo htmlspecialchars($fechaVencimientoTexto, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-estado-stock="<?php echo htmlspecialchars($estadoStockInsumo, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-etiqueta-stock="<?php echo htmlspecialchars($etiquetaStockInsumo, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-estado-vencimiento="<?php echo htmlspecialchars($estadoVencimientoInsumo, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-etiqueta-vencimiento="<?php echo htmlspecialchars($etiquetaVencimientoInsumo, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-estado-insumo="<?php echo htmlspecialchars($estadoInsumo, ENT_QUOTES, 'UTF-8'); ?>"
                                                    title="Ver detalles del insumo"
                                                    aria-label="Ver detalles del insumo"
                                                >
                                                    <span class="cliente-detalle-icono" aria-hidden="true">
                                                        <svg viewBox="0 0 24 24" fill="none">

                                                            <!-- Contorno del ojo -->
                                                            <path
                                                                d="M2 12
                                                                   C2 12 5.5 5 12 5
                                                                   C18.5 5 22 12 22 12
                                                                   C22 12 18.5 19 12 19
                                                                   C5.5 19 2 12 2 12Z"
                                                                stroke="currentColor"
                                                                stroke-width="2"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                fill="none"
                                                            />

                                                            <!-- Corazón central -->
                                                            <path
                                                                d="M12 16
                                                                   C10 14.8 8.5 13.5 8.5 11.8
                                                                   C8.5 10.5 9.5 9.5 10.8 9.5
                                                                   C11.5 9.5 12 9.9 12 10.4
                                                                   C12 9.9 12.5 9.5 13.2 9.5
                                                                   C14.5 9.5 15.5 10.5 15.5 11.8
                                                                   C15.5 13.5 14 14.8 12 16Z"
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
                                                    data-id-insumo="<?php echo htmlspecialchars((string) ($insumo['id_insumo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-nombre-insumo="<?php echo htmlspecialchars($insumo['nombre_insumo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-descripcion="<?php echo htmlspecialchars($descripcionInsumo, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-presentacion="<?php echo htmlspecialchars($insumo['presentacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-tipo-control="<?php echo htmlspecialchars($tipoControlInsumo, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-stock-actual="<?php echo htmlspecialchars((string) ($insumo['stock_actual'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-stock-minimo="<?php echo htmlspecialchars((string) ($insumo['stock_minimo'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-estado-insumo="<?php echo htmlspecialchars($estadoInsumo, ENT_QUOTES, 'UTF-8'); ?>"
                                                    title="Editar insumo"
                                                    aria-label="Editar insumo"
                                                >
                                                    <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                                                </button>

                                                <!-- Botón Gestionar existencias -->
                                                <button
                                                    type="button"
                                                    class="btn-action btn-reponer btnGestionarExistencias"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalGestionarExistencias"
                                                    data-id-insumo="<?php echo htmlspecialchars((string) ($insumo['id_insumo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-nombre-insumo="<?php echo htmlspecialchars($insumo['nombre_insumo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-stock-actual="<?php echo htmlspecialchars((string) ($insumo['stock_actual'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>"
                                                    title="Gestionar existencias"
                                                    aria-label="Gestionar existencias"
                                                >
                                                    <i class="bi bi-box-seam" aria-hidden="true"></i>
                                                </button>

                                                <!-- Botón Desactivar -->
                                                <button
                                                    type="button"
                                                    class="btn-action btn-deactivate btnDesactivarInsumo"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalDesactivarInsumo"
                                                    data-id-insumo="<?php echo htmlspecialchars((string) ($insumo['id_insumo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-nombre-insumo="<?php echo htmlspecialchars($insumo['nombre_insumo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    title="Desactivar insumo"
                                                    aria-label="Desactivar insumo"
                                                >
                                                    <i class="bi bi-trash-fill" aria-hidden="true"></i>
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

        </div>

        <!-- ===================================================== -->
        <!-- HISTORIAL DE MOVIMIENTOS -->
        <!-- ===================================================== -->

        <div
            class="tab-pane fade"
            id="historial-insumos"
            role="tabpanel"
            aria-labelledby="historial-insumos-tab"
            tabindex="0"
        >

            <!-- Filtros de consulta del historial -->
            <form id="filtrosHistorialInsumos" class="insumos-historial-filtros" autocomplete="off">
                <div class="insumos-historial-fila">

                    <div class="insumos-historial-campo insumos-historial-fecha">
                        <label for="historialFechaDesde" class="form-label">Desde</label>
                        <input
                            type="date"
                            class="form-control"
                            id="historialFechaDesde"
                            name="fecha_desde"
                        >
                    </div>

                    <div class="insumos-historial-campo insumos-historial-fecha">
                        <label for="historialFechaHasta" class="form-label">Hasta</label>
                        <input
                            type="date"
                            class="form-control"
                            id="historialFechaHasta"
                            name="fecha_hasta"
                        >
                    </div>

                    <div class="insumos-historial-campo insumos-historial-tipo">
                        <label for="historialTipoMovimiento" class="form-label">Tipo de movimiento</label>
                        <select
                            class="form-select"
                            id="historialTipoMovimiento"
                            name="tipo_movimiento"
                        >
                            <option value="">Todos los movimientos</option>
                            <option value="INCREMENTO">Incrementos (+)</option>
                            <option value="DECREMENTO">Descuentos (−)</option>
                        </select>
                    </div>

                    <div class="insumos-historial-acciones">
                        <button
                            type="submit"
                            class="btn-templo-primary insumos-historial-buscar"
                            id="buscarHistorialInsumos"
                            title="Buscar movimientos"
                            aria-label="Buscar movimientos"
                        >
                            <i class="bi bi-search-heart-fill" aria-hidden="true"></i>
                        </button>

                        <button
                            type="button"
                            class="btn-templo-secondary insumos-historial-limpiar"
                            id="limpiarHistorialInsumos"
                            title="Limpiar filtros"
                            aria-label="Limpiar filtros"
                        >
                            <i class="bi bi-eraser-fill" aria-hidden="true"></i>
                        </button>
                    </div>

                </div>
            </form>

            <!-- Mensajes de consulta del historial -->
            <div
                class="alert alert-danger insumos-historial-mensaje"
                id="mensajeHistorialInsumos"
                role="alert"
                hidden
            ></div>

            <!-- Tabla de movimientos registrados -->
            <div class="table-zone">
                <div class="templo-table-wrapper">
                    <div class="table-responsive">

                        <table id="tablaHistorialInsumos" class="table table-hover templo-table w-100">
                            <thead>
                                <tr>
                                    <th>Fecha y hora</th>
                                    <th>Insumo</th>
                                    <th>Movimiento</th>
                                    <th>Cantidad</th>
                                    <th>Stock anterior</th>
                                    <th>Stock resultante</th>
                                    <th>Motivo</th>
                                    <th>Proveedor</th>
                                    <th>Responsable</th>
                                </tr>
                            </thead>

                            <tbody>
                                <!-- Los movimientos se consultan al abrir esta pestaña -->
                            </tbody>
                        </table>

                    </div>
                </div>
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
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content templo-modal detalle-ficha-modal">

            <!-- Encabezado del modal -->
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleInsumoLabel">
                    <span class="detalle-ficha-title-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M2 12
                                   C2 12 5.5 5 12 5
                                   C18.5 5 22 12 22 12
                                   C22 12 18.5 19 12 19
                                   C5.5 19 2 12 2 12Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <path
                                d="M12 16
                                   C10 14.8 8.5 13.5 8.5 11.8
                                   C8.5 10.5 9.5 9.5 10.8 9.5
                                   C11.5 9.5 12 9.9 12 10.4
                                   C12 9.9 12.5 9.5 13.2 9.5
                                   C14.5 9.5 15.5 10.5 15.5 11.8
                                   C15.5 13.5 14 14.8 12 16Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    Detalles del Insumo
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Ficha de detalle con el mismo molde de Clientes y Proveedores -->
            <div class="modal-body detalle-ficha-modal-body">
                <div class="detalle-ficha-panel">

                    <!-- Encabezado de la ficha -->
                    <div class="detalle-ficha-intro">
                        <span class="detalle-ficha-intro-icon" aria-hidden="true">
                            <i class="bi bi-box2-heart"></i>
                        </span>

                        <div class="detalle-ficha-intro-content">
                            <p class="detalle-ficha-intro-title mb-0">Ficha del insumo</p>
                        </div>
                    </div>

                    <!-- Información general -->
                    <div class="row g-2 detalle-ficha-grid">

                        <!-- Nombre -->
                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-box2-heart"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Nombre del insumo</span>
                                    <p class="detalle-ficha-value mb-0" id="detalleNombreInsumo"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Presentación -->
                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-box"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Presentación</span>
                                    <p class="detalle-ficha-value mb-0" id="detallePresentacionInsumo"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <div class="detalle-ficha-card detalle-ficha-card-wide">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-card-text"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Descripción</span>
                                    <p class="detalle-ficha-value mb-0" id="detalleDescripcionInsumo"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Tipo de control -->
                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-ui-checks"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Tipo de control</span>
                                    <p class="detalle-ficha-value mb-0" id="detalleTipoControl"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Stock actual -->
                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-boxes"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Stock actual</span>
                                    <p class="detalle-ficha-value detalle-ficha-value-highlight mb-0" id="detalleStockActual"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Stock mínimo -->
                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-bar-chart-line"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Stock mínimo</span>
                                    <p class="detalle-ficha-value mb-0" id="detalleStockMinimo"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Próximo vencimiento de los lotes disponibles -->
                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-calendar-heart"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Próximo vencimiento</span>
                                    <p class="detalle-ficha-value mb-0" id="detalleFechaVencimiento"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Estado de las existencias -->
                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-clipboard-check"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Estado de las existencias</span>
                                    <span class="badge" id="detalleEstadoStock"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Estado del vencimiento -->
                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-calendar-check"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Estado del vencimiento</span>
                                    <span class="badge" id="detalleEstadoVencimiento"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Estado del insumo -->
                        <div class="col-md-6">
                            <div class="detalle-ficha-card detalle-ficha-card-emphasis h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-check-circle"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Estado</span>
                                    <span class="detalle-ficha-status" id="detalleEstadoInsumo"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Lotes registrados -->
                        <div class="col-12">
                            <div class="detalle-ficha-card detalle-ficha-card-wide">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-box-seam"></i>
                                </span>

                                <div class="detalle-ficha-card-content w-100">
                                    <span class="detalle-ficha-label">Lotes registrados</span>

                                    <div id="detalleLotesInsumo" class="row g-2 mt-1" aria-live="polite">
                                        <div class="col-12">
                                            <p class="detalle-ficha-value mb-0 text-muted">Consultando lotes...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Proveedores vinculados (solo lectura) -->
                        <div class="col-12">
                            <div class="detalle-ficha-card detalle-ficha-card-wide">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-truck"></i>
                                </span>

                                <div class="detalle-ficha-card-content w-100">
                                    <span class="detalle-ficha-label">Proveedores que suministran</span>

                                    <div
                                        id="detalleProveedoresInsumo"
                                        class="d-flex flex-wrap gap-2 mt-1"
                                        role="status"
                                        aria-live="polite"
                                    >
                                        <span class="text-muted">Consultando proveedores...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Botón de cierre -->
            <div class="modal-footer">
                <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
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

                <!-- Datos de la operación -->
                <input type="hidden" name="accion" value="registrar">

                <!-- Encabezado del modal -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoInsumoLabel">
                        <i class="bi bi-plus-circle"></i>
                        Registrar Nuevo Insumo
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Datos del catálogo de insumos -->
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Nombre -->
                        <div class="col-md-6">
                            <label class="form-label" for="registrarNombreInsumo">
                                Nombre del insumo
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="nombre_insumo"
                                id="registrarNombreInsumo"
                                class="form-control templo-input"
                                placeholder="Ingrese el nombre del insumo"
                                maxlength="100"
                                required
                            >
                        </div>

                        <!-- Presentación -->
                        <div class="col-md-6">
                            <label class="form-label" for="registrarPresentacionInsumo">Presentación</label>

                            <input
                                type="text"
                                name="presentacion"
                                id="registrarPresentacionInsumo"
                                class="form-control templo-input"
                                placeholder="Ingrese la presentación"
                                maxlength="80"
                            >
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <label class="form-label" for="registrarDescripcionInsumo">Descripción</label>

                            <textarea
                                name="descripcion"
                                id="registrarDescripcionInsumo"
                                class="form-control templo-input"
                                rows="3"
                                maxlength="255"
                                placeholder="Ingrese la descripción del insumo"
                            ></textarea>
                        </div>

                        <!-- Stock mínimo -->
                        <div class="col-md-6">
                            <label class="form-label" for="registrarStockMinimo">
                                Stock mínimo
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="number"
                                name="stock_minimo"
                                id="registrarStockMinimo"
                                class="form-control templo-input"
                                min="0"
                                max="4294967295"
                                step="1"
                                placeholder="0"
                                required
                            >
                        </div>

                        <!-- Tipo de control -->
                        <div class="col-md-6">
                            <label class="form-label" for="registrarTipoControl">
                                Tipo de control
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <select name="tipo_control" id="registrarTipoControl" class="form-select templo-input" required>
                                <option value="UNITARIO">Consumo unitario</option>
                                <option value="POR_ENVASE">Por envase</option>
                            </select>
                        </div>

                        <!-- Estado inicial -->
                        <div class="col-md-6">
                            <label class="form-label" for="registrarEstadoInsumo">Estado</label>

                            <input
                                type="text"
                                id="registrarEstadoInsumo"
                                class="form-control templo-input"
                                value="ACTIVO"
                                readonly
                            >
                        </div>

                        <!-- Las existencias se agregan luego de registrar el insumo -->
                        <div class="col-12">
                            <p class="form-text mb-0">
                                El insumo se registrará con <strong>0 existencias</strong>.
                                Después podrá agregar las primeras unidades, su proveedor y su fecha de vencimiento desde <strong>Gestionar Existencias</strong>.
                            </p>
                        </div>

                    </div>
                </div>

                <!-- Botones -->
                <div class="modal-footer">
                    <button type="submit" class="btn-templo-primary">
                        <i class="bi bi-save"></i>
                        Guardar
                    </button>

                    <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
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

                <!-- Datos de la operación -->
                <input type="hidden" name="accion" value="modificar">
                <input type="hidden" name="id_insumo" id="editarIdInsumo">
                <input type="hidden" name="stock_original" id="editarStockOriginal">

                <!-- Encabezado del modal -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarInsumoLabel">
                        <i class="bi bi-pencil-square"></i>
                        Editar Insumo
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Datos editables -->
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Nombre -->
                        <div class="col-md-6">
                            <label class="form-label" for="editarNombreInsumo">
                                Nombre del insumo
                                <span class="required-mark" aria-hidden="true">*</span>
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

                        <!-- Presentación -->
                        <div class="col-md-6">
                            <label class="form-label" for="editarPresentacionInsumo">Presentación</label>

                            <input
                                type="text"
                                name="presentacion"
                                id="editarPresentacionInsumo"
                                class="form-control templo-input"
                                maxlength="80"
                            >
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <label class="form-label" for="editarDescripcionInsumo">Descripción</label>

                            <textarea
                                name="descripcion"
                                id="editarDescripcionInsumo"
                                class="form-control templo-input"
                                rows="3"
                                maxlength="255"
                            ></textarea>
                        </div>

                        <!-- Corrección excepcional de stock -->
                        <div class="col-md-6">
                            <label class="form-label" for="editarStockActual">
                                Stock actual
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="number"
                                name="stock_actual"
                                id="editarStockActual"
                                class="form-control templo-input"
                                min="0"
                                max="4294967295"
                                step="1"
                                required
                            >

                            <div class="form-text">
                                Solo para corregir cantidades registradas por error.
                                Si el insumo tiene varios lotes, la corrección debe identificar el lote afectado.
                                Para nuevas unidades use Gestionar Existencias.
                            </div>
                        </div>

                        <!-- Stock mínimo -->
                        <div class="col-md-6">
                            <label class="form-label" for="editarStockMinimo">
                                Stock mínimo
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="number"
                                name="stock_minimo"
                                id="editarStockMinimo"
                                class="form-control templo-input"
                                min="0"
                                max="4294967295"
                                step="1"
                                required
                            >
                        </div>

                        <!-- Tipo de control -->
                        <div class="col-md-6">
                            <label class="form-label" for="editarTipoControl">
                                Tipo de control
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <select name="tipo_control" id="editarTipoControl" class="form-select templo-input" required>
                                <option value="UNITARIO">Consumo unitario</option>
                                <option value="POR_ENVASE">Por envase</option>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div class="col-md-6">
                            <label class="form-label" for="editarEstadoInsumo">Estado</label>

                            <input
                                type="text"
                                id="editarEstadoInsumo"
                                class="form-control templo-input"
                                readonly
                            >
                        </div>

                    </div>
                </div>

                <!-- Botones -->
                <div class="modal-footer">
                    <button type="submit" class="btn-templo-primary">
                        <i class="bi bi-save"></i>
                        Guardar Cambios
                    </button>

                    <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


<!-- ===================================================== -->
<!-- MODAL GESTIONAR EXISTENCIAS -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalGestionarExistencias"
    tabindex="-1"
    aria-labelledby="modalGestionarExistenciasLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content templo-modal">
            <form action="Index.php?url=insumos" method="POST">

                <!-- Datos de la operación -->
                <input type="hidden" name="accion" value="gestionar_existencias">
                <input type="hidden" name="id_insumo" id="movimientoIdInsumo">

                <!-- Encabezado del modal -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGestionarExistenciasLabel">
                        <i class="bi bi-box-seam"></i>
                        Gestionar Existencias
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Datos del movimiento -->
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Insumo y existencias -->
                        <div class="col-12">
                            <p class="fw-semibold mb-1" id="movimientoNombreInsumo"></p>

                            <p class="mb-0">
                                Existencias actuales:
                                <strong id="movimientoStockActual">0</strong>
                            </p>
                        </div>

                        <!-- Operación que solicita el usuario -->
                        <div class="col-12">
                            <label class="form-label" for="movimientoTipoInsumo">
                                Operación
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <select
                                name="tipo_movimiento"
                                id="movimientoTipoInsumo"
                                class="form-select templo-input"
                                required
                            >
                                <option value="AGREGAR">Agregar existencias (+)</option>
                                <option value="REPONER">Reponer existencias (+)</option>
                                <option value="DESCONTAR">Descontar existencias (−)</option>
                            </select>

                            <div class="form-text" id="movimientoDescripcionOperacion">
                                Agregar corresponde al primer ingreso de unidades del insumo.
                            </div>
                        </div>

                        <!-- Cantidad -->
                        <div class="col-12">
                            <label class="form-label" for="movimientoCantidadInsumo">
                                Cantidad
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="number"
                                name="cantidad_movimiento"
                                id="movimientoCantidadInsumo"
                                class="form-control templo-input"
                                min="1"
                                max="4294967295"
                                step="1"
                                placeholder="Ingrese la cantidad"
                                required
                            >

                            <div class="form-text" id="movimientoAyudaInsumo">
                                La cantidad indicada se sumará a las existencias actuales del insumo.
                            </div>
                        </div>

                        <!-- Proveedor del nuevo lote -->
                        <div class="col-md-6" id="movimientoProveedorGrupo">
                            <label class="form-label" for="movimientoProveedorInsumo">Proveedor</label>

                            <select
                                name="id_proveedor"
                                id="movimientoProveedorInsumo"
                                class="form-select templo-input"
                            >
                                <option value="">Sin proveedor registrado</option>

                                <?php foreach (($listaProveedoresInsumos ?? []) as $proveedorInsumo): ?>

                                    <option value="<?php echo htmlspecialchars((string) $proveedorInsumo['id_proveedor'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($proveedorInsumo['nombre_empresa'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>

                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Vencimiento del nuevo lote -->
                        <div class="col-md-6" id="movimientoFechaGrupo">
                            <label class="form-label" for="movimientoFechaVencimiento">Fecha de vencimiento</label>

                            <input
                                type="date"
                                name="fecha_vencimiento"
                                id="movimientoFechaVencimiento"
                                class="form-control templo-input"
                            >

                            <div class="form-check mt-2">
                                <input
                                    type="checkbox"
                                    class="form-check-input fecha-no-aplica"
                                    id="movimientoFechaNoAplica"
                                >

                                <label class="form-check-label" for="movimientoFechaNoAplica">No aplica</label>
                            </div>
                        </div>

                        <!-- Lote afectado por el descuento -->
                        <div class="col-12" id="movimientoLoteGrupo" hidden>
                            <label class="form-label" for="movimientoLoteInsumo">
                                Lote afectado
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <select
                                name="id_lote"
                                id="movimientoLoteInsumo"
                                class="form-select templo-input"
                                disabled
                            >
                                <option value="">Seleccione el lote afectado</option>
                            </select>

                            <div class="form-text" id="movimientoLoteAyuda">
                                El descuento se realizará únicamente sobre el lote seleccionado.
                            </div>
                        </div>

                        <!-- Motivo del descuento -->
                        <div class="col-12" id="movimientoMotivoGrupo" hidden>
                            <label class="form-label" for="movimientoMotivoInsumo">
                                Motivo del descuento
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <select
                                name="motivo"
                                id="movimientoMotivoInsumo"
                                class="form-select templo-input"
                                disabled
                            >
                                <option value="">Seleccione un motivo</option>
                                <option value="Rotura">Rotura</option>
                                <option value="Derrame">Derrame</option>
                                <option value="Deterioro">Deterioro</option>
                                <option value="Daño">Daño</option>
                                <option value="Extravío">Extravío</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <!-- Explicación adicional -->
                        <div class="col-12" id="movimientoOtroMotivoGrupo" hidden>
                            <label class="form-label" for="movimientoOtroMotivoInsumo">
                                Especifique el motivo
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <textarea
                                name="detalle_motivo"
                                id="movimientoOtroMotivoInsumo"
                                class="form-control templo-input"
                                maxlength="249"
                                rows="2"
                                placeholder="Describa qué ocurrió"
                                disabled
                            ></textarea>
                        </div>

                        <!-- Resultado -->
                        <div class="col-12">
                            <p class="mb-0" aria-live="polite">
                                Existencias resultantes:
                                <strong id="movimientoStockResultante">—</strong>
                            </p>
                        </div>

                    </div>
                </div>

                <!-- Botones -->
                <div class="modal-footer">
                    <button type="submit" class="btn-templo-primary">
                        <i class="bi bi-check-circle"></i>
                        <span id="movimientoTextoConfirmar">Confirmar Ingreso</span>
                    </button>

                    <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
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
    data-alerta-confirmacion
    data-alerta-aceptar="button[type='submit']"
    tabindex="-1"
    aria-labelledby="modalDesactivarInsumoLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content templo-modal status-modal">

            <form action="Index.php?url=insumos" method="POST">

                <!-- Datos de la operación -->
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

                <!-- Encabezado del modal -->
                <div class="modal-header">

                    <h5 class="modal-title" id="modalDesactivarInsumoLabel">
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

                <!-- Confirmación de la desactivación -->
                <div class="modal-body text-center">

                    <p class="status-question">
                        ¿Deseas desactivar este insumo?
                    </p>

                    <p class="status-client-name" id="desactivarNombreInsumo"></p>

                    <p class="status-warning">
                        El insumo no se borrará. Podrá consultarlo y reactivarlo desde Inactivos.
                    </p>

                </div>

                <!-- Botones del modal -->
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


<!-- ===================================================== -->
<!-- SCRIPT Y CIERRE DE LA VISTA -->
<!-- ===================================================== -->

<?php

$scriptVista = 'Assets/js/insumos.js?v=' . filemtime(__DIR__ . '/../../Assets/js/insumos.js');
$scriptVistaModulo = true;

require_once __DIR__ . '/Layout/Footer.php';

?>