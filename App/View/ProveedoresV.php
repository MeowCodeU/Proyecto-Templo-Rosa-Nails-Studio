<?php

/* ===================================================== */
/* DATOS DE LA VISTA */
/* ===================================================== */

$tituloPagina = 'Gestión de Proveedores';

require_once __DIR__ . '/Layout/Header.php';

$escaparProveedor = static function ($valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

?>


<!-- ===================================================== -->
<!-- GESTIÓN DE PROVEEDORES -->
<!-- ===================================================== -->

<section class="module-card clientes-module proveedores-module">

    <!-- Encabezado del módulo -->
    <div class="module-header">

        <h2>
            <span class="title-icon custom-proveedores" aria-hidden="true">
                <svg viewBox="0 0 64 64">
                    <path
                        d="M4 20A3 3 0 0 1 7 17H42
                           A3 3 0 0 1 45 20V27H52
                           A4 4 0 0 1 55.5 29.2
                           L60.5 36.5A4 4 0 0 1 61 38.8
                           V48A3 3 0 0 1 58 51H54"
                    ></path>

                    <path d="M4 20V48 A3 3 0 0 0 7 51H12"></path>
                    <path d="M26 51H40"></path>
                    <path d="M45 27V44"></path>
                    <path d="M45 30H54L59 38H45V30Z" stroke-width="1.5"></path>

                    <circle cx="19" cy="51" r="7"></circle>
                    <circle cx="47" cy="51" r="7"></circle>

                    <path
                        d="M24.5 35 C23.5 33 21 32.5 21 30.5
                           C21 29 22.5 28 24.5 28
                           C25.5 28 26.5 28.5 27 29.5
                           C27.5 28.5 28.5 28 29.5 28
                           C31.5 28 33 29 33 30.5
                           C33 32.5 30.5 33 29.5 35
                           L27 37.5 24.5 35Z"
                        class="truck-heart"
                    ></path>
                </svg>
            </span>

            Gestión de Proveedores
        </h2>

        <button
            type="button"
            class="btn-templo-primary"
            data-bs-toggle="modal"
            data-bs-target="#modalNuevoProveedor"
        >
            <i class="bi bi-plus-circle" aria-hidden="true"></i>
            Nuevo Proveedor
        </button>

    </div>


    <!-- ===================================================== -->
    <!-- TABLA DE PROVEEDORES -->
    <!-- ===================================================== -->

    <div class="table-zone">
        <div class="templo-table-wrapper">
            <div class="table-responsive">

                <table
                    id="tablaProveedores"
                    class="table table-hover templo-table w-100"
                >
                    <thead>

                        <tr class="proveedores-group-row">
                            <th colspan="2" scope="colgroup">
                                Datos de la empresa
                            </th>

                            <th
                                colspan="4"
                                scope="colgroup"
                                class="proveedores-contact-group"
                            >
                                Persona de contacto
                            </th>

                            <th
                                rowspan="2"
                                scope="col"
                                class="proveedores-actions-header columna-acciones"
                            >
                                Acciones
                            </th>
                        </tr>

                        <tr class="proveedores-columns-row">
                            <th scope="col">RIF</th>
                            <th scope="col" class="columna-empresa">Empresa</th>
                            <th scope="col" class="proveedores-contact-start">Nombre</th>
                            <th scope="col">Apellido</th>
                            <th scope="col">Teléfono</th>
                            <th scope="col">Ciudad</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach (($listaProveedores ?? []) as $proveedor): ?>

                        <?php

                        $nombreCompleto = trim(
                            ($proveedor['nombre'] ?? '') . ' ' .
                            ($proveedor['apellido'] ?? '')
                        );

                        $estadoProveedor = strtoupper(
                            trim((string) ($proveedor['estado_proveedor'] ?? 'ACTIVO'))
                        );

                        ?>

                        <tr>

                            <td>
                                <?= $escaparProveedor($proveedor['rif'] ?? ''); ?>
                            </td>

                            <td class="columna-empresa">
                                <?= $escaparProveedor($proveedor['nombre_empresa'] ?? ''); ?>
                            </td>

                            <td class="proveedores-contact-start">
                                <?= $escaparProveedor($proveedor['nombre'] ?? ''); ?>
                            </td>

                            <td>
                                <?= $escaparProveedor($proveedor['apellido'] ?? ''); ?>
                            </td>

                            <td>
                                <?= $escaparProveedor($proveedor['telefono'] ?? ''); ?>
                            </td>

                            <td>
                                <?= $escaparProveedor($proveedor['ciudad'] ?? ''); ?>
                            </td>


                            <!-- Acciones del proveedor -->
                            <td class="columna-acciones">

                                <div class="btn-group-actions">

                                    <!-- Ver detalles -->
                                    <button
                                        type="button"
                                        class="btn-action btn-detail btnDetalleProveedor"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetalleProveedor"
                                        data-id-proveedor="<?= $escaparProveedor($proveedor['id_proveedor'] ?? ''); ?>"
                                        data-rif="<?= $escaparProveedor($proveedor['rif'] ?? ''); ?>"
                                        data-empresa="<?= $escaparProveedor($proveedor['nombre_empresa'] ?? ''); ?>"
                                        data-cedula="<?= $escaparProveedor($proveedor['cedula'] ?? ''); ?>"
                                        data-nombre-completo="<?= $escaparProveedor($nombreCompleto); ?>"
                                        data-telefono="<?= $escaparProveedor($proveedor['telefono'] ?? ''); ?>"
                                        data-correo="<?= $escaparProveedor($proveedor['correo'] ?? ''); ?>"
                                        data-ciudad="<?= $escaparProveedor($proveedor['ciudad'] ?? ''); ?>"
                                        data-direccion="<?= $escaparProveedor($proveedor['direccion'] ?? ''); ?>"
                                        data-estado-proveedor="<?= $escaparProveedor($estadoProveedor); ?>"
                                        title="Ver detalles del proveedor"
                                        aria-label="Ver detalles del proveedor"
                                    >
                                        <span class="detalle-accion-icono" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path
                                                    d="M2 12 C2 12 5.5 5 12 5
                                                       C18.5 5 22 12 22 12
                                                       C22 12 18.5 19 12 19
                                                       C5.5 19 2 12 2 12Z"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />

                                                <path
                                                    d="M12 16 C10 14.8 8.5 13.5 8.5 11.8
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


                                    <!-- Editar proveedor y contacto actual -->
                                    <button
                                        type="button"
                                        class="btn-action btn-edit btnEditarProveedor"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditarProveedor"
                                        data-id-proveedor="<?= $escaparProveedor($proveedor['id_proveedor'] ?? ''); ?>"
                                        data-id-persona-contacto="<?= $escaparProveedor($proveedor['id_persona_contacto'] ?? ''); ?>"
                                        data-rif="<?= $escaparProveedor($proveedor['rif'] ?? ''); ?>"
                                        data-empresa="<?= $escaparProveedor($proveedor['nombre_empresa'] ?? ''); ?>"
                                        data-cedula="<?= $escaparProveedor($proveedor['cedula'] ?? ''); ?>"
                                        data-nombre="<?= $escaparProveedor($proveedor['nombre'] ?? ''); ?>"
                                        data-apellido="<?= $escaparProveedor($proveedor['apellido'] ?? ''); ?>"
                                        data-telefono="<?= $escaparProveedor($proveedor['telefono'] ?? ''); ?>"
                                        data-correo="<?= $escaparProveedor($proveedor['correo'] ?? ''); ?>"
                                        data-ciudad="<?= $escaparProveedor($proveedor['ciudad'] ?? ''); ?>"
                                        data-direccion="<?= $escaparProveedor($proveedor['direccion'] ?? ''); ?>"
                                        title="Editar proveedor"
                                        aria-label="Editar proveedor"
                                    >
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                    </button>


                                    <!-- Cambiar persona de contacto -->
                                    <button
                                        type="button"
                                        class="btn-action btn-edit btnCambiarContactoProveedor"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalCambiarContactoProveedor"
                                        data-id-proveedor="<?= $escaparProveedor($proveedor['id_proveedor'] ?? ''); ?>"
                                        data-id-persona-contacto="<?= $escaparProveedor($proveedor['id_persona_contacto'] ?? ''); ?>"
                                        data-empresa="<?= $escaparProveedor($proveedor['nombre_empresa'] ?? ''); ?>"
                                        data-cedula="<?= $escaparProveedor($proveedor['cedula'] ?? ''); ?>"
                                        data-nombre-completo="<?= $escaparProveedor($nombreCompleto); ?>"
                                        title="Cambiar persona de contacto"
                                        aria-label="Cambiar persona de contacto"
                                    >
                                        <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
                                    </button>


                                    <!-- Desactivar proveedor -->
                                    <button
                                        type="button"
                                        class="btn-action btn-delete btnDesactivarProveedor"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDesactivarProveedor"
                                        data-id-proveedor="<?= $escaparProveedor($proveedor['id_proveedor'] ?? ''); ?>"
                                        data-empresa="<?= $escaparProveedor($proveedor['nombre_empresa'] ?? ''); ?>"
                                        title="Desactivar proveedor"
                                        aria-label="Desactivar proveedor"
                                    >
                                        <i class="bi bi-trash-fill" aria-hidden="true"></i>
                                    </button>

                                </div>

                            </td>

                        </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>

</section>


<!-- ===================================================== -->
<!-- MODAL DETALLES DEL PROVEEDOR -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalDetalleProveedor"
    tabindex="-1"
    aria-labelledby="modalDetalleProveedorLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content templo-modal detalle-ficha-modal">

            <div class="modal-header">

                <h5 class="modal-title" id="modalDetalleProveedorLabel">

                    <span class="detalle-ficha-title-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M2 12 C2 12 5.5 5 12 5
                                   C18.5 5 22 12 22 12
                                   C22 12 18.5 19 12 19
                                   C5.5 19 2 12 2 12Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <path
                                d="M12 16 C10 14.8 8.5 13.5 8.5 11.8
                                   C8.5 10.5 9.5 9.5 10.8 9.5
                                   C11.5 9.5 12 9.9 12 10.4
                                   C12 9.9 12.5 9.5 13.2 9.5
                                   C14.5 9.5 15.5 10.5 15.5 11.8
                                   C15.5 13.5 14 14.8 12 16Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    Detalles del Proveedor

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>


            <div class="modal-body detalle-ficha-modal-body">

                <div class="detalle-ficha-panel">

                    <!-- Encabezado de la ficha -->
                    <div class="detalle-ficha-intro">

                        <span class="detalle-ficha-intro-icon" aria-hidden="true">
                            <svg
                                class="detalle-ficha-intro-svg"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <rect
                                    x="8" y="8"
                                    width="48" height="48"
                                    rx="8"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                />

                                <path
                                    d="M27 18H48 M27 28H48 M27 38H48 M27 48H48"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                />

                                <path
                                    d="M18 14
                                       C16.9 12.4 14.4 12.3 13.4 14
                                       C12.3 15.8 13.3 17.9 18 21.5
                                       C22.7 17.9 23.7 15.8 22.6 14
                                       C21.6 12.3 19.1 12.4 18 14Z"
                                    fill="currentColor"
                                />

                                <path
                                    d="M18 24
                                       C16.9 22.4 14.4 22.3 13.4 24
                                       C12.3 25.8 13.3 27.9 18 31.5
                                       C22.7 27.9 23.7 25.8 22.6 24
                                       C21.6 22.3 19.1 22.4 18 24Z"
                                    fill="currentColor"
                                />

                                <path
                                    d="M18 34
                                       C16.9 32.4 14.4 32.3 13.4 34
                                       C12.3 35.8 13.3 37.9 18 41.5
                                       C22.7 37.9 23.7 35.8 22.6 34
                                       C21.6 32.3 19.1 32.4 18 34Z"
                                    fill="currentColor"
                                />

                                <path
                                    d="M18 44
                                       C16.9 42.4 14.4 42.3 13.4 44
                                       C12.3 45.8 13.3 47.9 18 51.5
                                       C22.7 47.9 23.7 45.8 22.6 44
                                       C21.6 42.3 19.1 42.4 18 44Z"
                                    fill="currentColor"
                                />
                            </svg>
                        </span>

                        <div class="detalle-ficha-intro-content">
                            <p class="detalle-ficha-intro-title mb-0">
                                Ficha del proveedor
                            </p>
                        </div>

                    </div>


                    <!-- Datos generales del proveedor -->
                    <div class="row g-2 detalle-ficha-grid">

                        <?php

                        $camposDetalleProveedor = [
                            [
                                'etiqueta' => 'RIF',
                                'id' => 'detalleRifProveedor',
                                'icono' => 'bi-postcard-heart'
                            ],
                            [
                                'etiqueta' => 'Empresa',
                                'id' => 'detalleNombreEmpresa',
                                'icono' => 'bi-shop'
                            ],
                            [
                                'etiqueta' => 'Cédula del contacto',
                                'id' => 'detalleCedulaContacto',
                                'icono' => 'bi-person-vcard'
                            ],
                            [
                                'etiqueta' => 'Persona de contacto',
                                'id' => 'detalleNombreContacto',
                                'icono' => 'bi-person-heart'
                            ],
                            [
                                'etiqueta' => 'Teléfono',
                                'id' => 'detalleTelefonoContacto',
                                'icono' => 'bi-telephone-fill'
                            ],
                            [
                                'etiqueta' => 'Correo',
                                'id' => 'detalleCorreoContacto',
                                'icono' => 'bi-envelope-heart'
                            ],
                            [
                                'etiqueta' => 'Ciudad',
                                'id' => 'detalleCiudadContacto',
                                'icono' => 'bi-geo-alt-fill'
                            ]
                        ];

                        ?>

                        <?php foreach ($camposDetalleProveedor as $campoDetalle): ?>

                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">

                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi <?= $escaparProveedor($campoDetalle['icono']); ?>"></i>
                                </span>

                                <div class="detalle-ficha-card-content">

                                    <span class="detalle-ficha-label">
                                        <?= $escaparProveedor($campoDetalle['etiqueta']); ?>
                                    </span>

                                    <p
                                        class="detalle-ficha-value mb-0"
                                        id="<?= $escaparProveedor($campoDetalle['id']); ?>"
                                    ></p>

                                </div>

                            </div>
                        </div>

                        <?php endforeach; ?>


                        <!-- Estado del proveedor -->
                        <div class="col-md-6">
                            <div class="detalle-ficha-card detalle-ficha-card-emphasis h-100">

                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-check-circle"></i>
                                </span>

                                <div class="detalle-ficha-card-content">

                                    <span class="detalle-ficha-label">
                                        Estado
                                    </span>

                                    <span
                                        class="badge detalle-ficha-status estado-registro-activo"
                                        id="detalleEstadoProveedor"
                                    ></span>

                                </div>

                            </div>
                        </div>


                        <!-- Dirección del contacto -->
                        <div class="col-12">
                            <div class="detalle-ficha-card detalle-ficha-card-wide detalle-ficha-card-emphasis">

                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-house-heart-fill"></i>
                                </span>

                                <div class="detalle-ficha-card-content">

                                    <span class="detalle-ficha-label">
                                        Dirección
                                    </span>

                                    <p
                                        class="detalle-ficha-value mb-0"
                                        id="detalleDireccionContacto"
                                    ></p>

                                </div>

                            </div>
                        </div>


                        <!-- Insumos asociados: únicamente consulta -->
                        <div class="col-12">
                            <div class="detalle-ficha-card detalle-ficha-card-wide">

                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-box-seam"></i>
                                </span>

                                <div class="detalle-ficha-card-content w-100">

                                    <span class="detalle-ficha-label">
                                        Insumos que suministra
                                    </span>

                                    <div
                                        id="detalleInsumosProveedor"
                                        class="d-flex flex-wrap gap-2 mt-2"
                                        role="status"
                                        aria-live="polite"
                                    >
                                        <span class="text-muted">
                                            Consultando insumos asociados...
                                        </span>
                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn-templo-secondary"
                    data-bs-dismiss="modal"
                >
                    <i class="bi bi-x-circle" aria-hidden="true"></i>
                    Cerrar
                </button>

            </div>

        </div>

    </div>
</div>


<!-- ===================================================== -->
<!-- MODAL NUEVO PROVEEDOR -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalNuevoProveedor"
    tabindex="-1"
    aria-labelledby="modalNuevoProveedorLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content templo-modal">

            <form
                action="Index.php?url=proveedores"
                method="POST"
                autocomplete="off"
            >

                <!-- Acción del controlador -->
                <input type="hidden" name="accion" value="registrar">


                <div class="modal-header">

                    <h5 class="modal-title" id="modalNuevoProveedorLabel">
                        <i class="bi bi-plus-circle" aria-hidden="true"></i>
                        Registrar Nuevo Proveedor
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

                        <!-- RIF -->
                        <div class="col-md-6">

                            <label class="form-label">
                                RIF
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="rif"
                                class="form-control templo-input"
                                placeholder="Ingrese el RIF"
                                maxlength="25"
                                required
                            >

                        </div>


                        <!-- Empresa -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Nombre de la empresa
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="nombre_empresa"
                                class="form-control templo-input"
                                placeholder="Ingrese el nombre de la empresa"
                                maxlength="100"
                                required
                            >

                        </div>


                        <!-- Cédula del contacto -->
                        <div class="col-md-6">

                            <label for="nuevoCedulaContacto" class="form-label">
                                Cédula del contacto
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="cedula"
                                id="nuevoCedulaContacto"
                                class="form-control templo-input"
                                placeholder="Ingrese la cédula"
                                minlength="6"
                                maxlength="20"
                                inputmode="numeric"
                                pattern="[0-9]{6,20}"
                                data-buscar-persona="true"
                                aria-describedby="mensajePersonaNuevoProveedor"
                                required
                            >

                            <small
                                id="mensajePersonaNuevoProveedor"
                                class="proveedores-mensaje-persona d-block mt-1"
                                role="status"
                                aria-live="polite"
                                hidden
                            ></small>

                        </div>


                        <!-- Teléfono -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Teléfono
                            </label>

                            <input
                                type="text"
                                name="telefono"
                                class="form-control templo-input"
                                placeholder="Ingrese el teléfono"
                                maxlength="25"
                            >

                        </div>


                        <!-- Nombre -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Nombre del contacto
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="nombre"
                                class="form-control templo-input"
                                placeholder="Ingrese el nombre"
                                maxlength="60"
                                required
                            >

                        </div>


                        <!-- Apellido -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Apellido del contacto
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="apellido"
                                class="form-control templo-input"
                                placeholder="Ingrese el apellido"
                                maxlength="60"
                                required
                            >

                        </div>


                        <!-- Correo -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Correo
                            </label>

                            <input
                                type="email"
                                name="correo"
                                class="form-control templo-input"
                                placeholder="Ingrese el correo"
                                maxlength="100"
                            >

                        </div>


                        <!-- Ciudad -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Ciudad
                            </label>

                            <input
                                type="text"
                                name="ciudad"
                                class="form-control templo-input"
                                placeholder="Ingrese la ciudad"
                                maxlength="80"
                            >

                        </div>


                        <!-- Dirección -->
                        <div class="col-12">

                            <label class="form-label">
                                Dirección
                            </label>

                            <input
                                type="text"
                                name="direccion"
                                class="form-control templo-input"
                                placeholder="Ingrese la dirección"
                                maxlength="150"
                            >

                        </div>


                        <!-- ================================================= -->
                        <!-- INSUMOS QUE SUMINISTRA -->
                        <!-- ================================================= -->

                        <div class="col-12">

                            <div class="proveedores-insumos-seccion mt-2">

                                <div class="d-flex align-items-center gap-2 mb-2">

                                    <i class="bi bi-box-seam" aria-hidden="true"></i>

                                    <span class="fw-semibold">
                                        Insumos que suministra
                                    </span>

                                </div>

                                <p class="form-text mt-0 mb-3">
                                    Seleccione los insumos de esta empresa.
                                    Esta asociación no modifica las existencias
                                    ni crea lotes.
                                </p>

                                <!-- Marcador que habilitará el JavaScript -->
                                <input
                                    type="hidden"
                                    name="insumos_presentes"
                                    value="1"
                                    id="insumosPresentesNuevoProveedor"
                                    disabled
                                >

                                <!-- Buscar insumos -->
                                <label
                                    for="buscarInsumosNuevoProveedor"
                                    class="form-label"
                                >
                                    Buscar insumo
                                </label>

                                <input
                                    type="search"
                                    id="buscarInsumosNuevoProveedor"
                                    class="form-control templo-input mb-3"
                                    placeholder="Escriba el nombre del insumo"
                                    autocomplete="off"
                                >

                                <!-- Casillas de los insumos activos -->
                                <div
                                    class="row g-2 proveedores-lista-insumos"
                                    id="listaInsumosNuevoProveedor"
                                    role="group"
                                    aria-label="Insumos que suministra el proveedor"
                                >

                                    <?php if (empty($listaInsumosDisponibles)): ?>

                                    <div class="col-12">
                                        <p class="text-muted mb-0">
                                            Todavía no hay insumos activos registrados.
                                        </p>
                                    </div>

                                    <?php else: ?>

                                    <?php foreach ($listaInsumosDisponibles as $insumoDisponible): ?>

                                    <div class="col-12 col-md-6 proveedores-insumo-item">

                                        <label
                                            class="proveedor-insumo-opcion d-flex align-items-center gap-2 border rounded-3 p-3 h-100"
                                            for="nuevoProveedorInsumo_<?= $escaparProveedor($insumoDisponible['id_insumo']); ?>"
                                        >

                                            <input
                                                type="checkbox"
                                                class="form-check-input m-0 proveedor-insumo-check"
                                                name="id_insumos[]"
                                                id="nuevoProveedorInsumo_<?= $escaparProveedor($insumoDisponible['id_insumo']); ?>"
                                                value="<?= $escaparProveedor($insumoDisponible['id_insumo']); ?>"
                                            >

                                            <span>

                                                <span class="d-block fw-semibold">
                                                    <?= $escaparProveedor($insumoDisponible['nombre_insumo']); ?>
                                                </span>

                                                <?php if (trim((string) ($insumoDisponible['presentacion'] ?? '')) !== ''): ?>

                                                <small class="d-block text-muted">
                                                    <?= $escaparProveedor($insumoDisponible['presentacion']); ?>
                                                </small>

                                                <?php endif; ?>

                                            </span>

                                        </label>

                                    </div>

                                    <?php endforeach; ?>

                                    <?php endif; ?>

                                </div>

                                <small
                                    class="proveedores-aviso-insumos d-block mt-2 text-muted"
                                    id="avisoInsumosNuevoProveedor"
                                    role="status"
                                    aria-live="polite"
                                >
                                    Marque los insumos que suministra este proveedor.
                                </small>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="submit" class="btn-templo-primary">
                        <i class="bi bi-save" aria-hidden="true"></i>
                        Guardar
                    </button>

                    <button
                        type="button"
                        class="btn-templo-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                        Cancelar
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>


<!-- ===================================================== -->
<!-- MODAL EDITAR PROVEEDOR -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalEditarProveedor"
    tabindex="-1"
    aria-labelledby="modalEditarProveedorLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content templo-modal">

            <form
                action="Index.php?url=proveedores"
                method="POST"
                autocomplete="off"
            >

                <!-- Acción del controlador -->
                <input type="hidden" name="accion" value="modificar">

                <input
                    type="hidden"
                    name="id_proveedor"
                    id="editarIdProveedor"
                >

                <input
                    type="hidden"
                    name="id_persona_contacto"
                    id="editarIdPersonaContacto"
                >


                <div class="modal-header">

                    <h5 class="modal-title" id="modalEditarProveedorLabel">
                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                        Editar Proveedor
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

                        <!-- RIF -->
                        <div class="col-md-6">

                            <label class="form-label">
                                RIF
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="rif"
                                id="editarRifProveedor"
                                class="form-control templo-input"
                                maxlength="25"
                                required
                            >

                        </div>


                        <!-- Empresa -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Nombre de la empresa
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="nombre_empresa"
                                id="editarNombreEmpresa"
                                class="form-control templo-input"
                                maxlength="100"
                                required
                            >

                        </div>


                        <!-- Cédula: conservar la identidad -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Cédula del contacto
                            </label>

                            <input
                                type="text"
                                name="cedula"
                                id="editarCedulaContacto"
                                class="form-control templo-input"
                                readonly
                            >

                        </div>


                        <!-- Teléfono -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Teléfono
                            </label>

                            <input
                                type="text"
                                name="telefono"
                                id="editarTelefonoContacto"
                                class="form-control templo-input"
                                maxlength="25"
                            >

                        </div>


                        <!-- Nombre -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Nombre del contacto
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="nombre"
                                id="editarNombreContacto"
                                class="form-control templo-input"
                                maxlength="60"
                                required
                            >

                        </div>


                        <!-- Apellido -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Apellido del contacto
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="apellido"
                                id="editarApellidoContacto"
                                class="form-control templo-input"
                                maxlength="60"
                                required
                            >

                        </div>


                        <!-- Correo -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Correo
                            </label>

                            <input
                                type="email"
                                name="correo"
                                id="editarCorreoContacto"
                                class="form-control templo-input"
                                maxlength="100"
                            >

                        </div>


                        <!-- Ciudad -->
                        <div class="col-md-6">

                            <label class="form-label">
                                Ciudad
                            </label>

                            <input
                                type="text"
                                name="ciudad"
                                id="editarCiudadContacto"
                                class="form-control templo-input"
                                maxlength="80"
                            >

                        </div>


                        <!-- Dirección -->
                        <div class="col-12">

                            <label class="form-label">
                                Dirección
                            </label>

                            <input
                                type="text"
                                name="direccion"
                                id="editarDireccionContacto"
                                class="form-control templo-input"
                                maxlength="150"
                            >

                        </div>


                        <!-- ================================================= -->
                        <!-- INSUMOS QUE SUMINISTRA -->
                        <!-- ================================================= -->

                        <div class="col-12">

                            <div class="proveedores-insumos-seccion mt-2">

                                <div class="d-flex align-items-center gap-2 mb-2">

                                    <i class="bi bi-box-seam" aria-hidden="true"></i>

                                    <span class="fw-semibold">
                                        Insumos que suministra
                                    </span>

                                </div>

                                <p class="form-text mt-0 mb-3">
                                    Marque los insumos que suministra esta empresa.
                                    Los que ya están asociados aparecerán seleccionados.
                                </p>

                                <!-- Impide borrar asociaciones antes de consultar -->
                                <input
                                    type="hidden"
                                    name="insumos_presentes"
                                    value="1"
                                    id="insumosPresentesEditarProveedor"
                                    disabled
                                >

                                <!-- Buscar insumos -->
                                <label
                                    for="buscarInsumosEditarProveedor"
                                    class="form-label"
                                >
                                    Buscar insumo
                                </label>

                                <input
                                    type="search"
                                    id="buscarInsumosEditarProveedor"
                                    class="form-control templo-input mb-3"
                                    placeholder="Escriba el nombre del insumo"
                                    autocomplete="off"
                                >

                                <!-- Casillas de los insumos activos -->
                                <div
                                    class="row g-2 proveedores-lista-insumos"
                                    id="listaInsumosEditarProveedor"
                                    role="group"
                                    aria-label="Insumos que suministra el proveedor"
                                >

                                    <?php if (empty($listaInsumosDisponibles)): ?>

                                    <div class="col-12">
                                        <p class="text-muted mb-0">
                                            Todavía no hay insumos activos registrados.
                                        </p>
                                    </div>

                                    <?php else: ?>

                                    <?php foreach ($listaInsumosDisponibles as $insumoDisponible): ?>

                                    <div class="col-12 col-md-6 proveedores-insumo-item">

                                        <label
                                            class="proveedor-insumo-opcion d-flex align-items-center gap-2 border rounded-3 p-3 h-100"
                                            for="editarProveedorInsumo_<?= $escaparProveedor($insumoDisponible['id_insumo']); ?>"
                                        >

                                            <input
                                                type="checkbox"
                                                class="form-check-input m-0 proveedor-insumo-check"
                                                name="id_insumos[]"
                                                id="editarProveedorInsumo_<?= $escaparProveedor($insumoDisponible['id_insumo']); ?>"
                                                value="<?= $escaparProveedor($insumoDisponible['id_insumo']); ?>"
                                            >

                                            <span>

                                                <span class="d-block fw-semibold">
                                                    <?= $escaparProveedor($insumoDisponible['nombre_insumo']); ?>
                                                </span>

                                                <?php if (trim((string) ($insumoDisponible['presentacion'] ?? '')) !== ''): ?>

                                                <small class="d-block text-muted">
                                                    <?= $escaparProveedor($insumoDisponible['presentacion']); ?>
                                                </small>

                                                <?php endif; ?>

                                            </span>

                                        </label>

                                    </div>

                                    <?php endforeach; ?>

                                    <?php endif; ?>

                                </div>

                                <!-- Mensajes de consulta -->
                                <small
                                    class="proveedores-aviso-insumos d-block mt-2 text-muted"
                                    id="avisoInsumosEditarProveedor"
                                    role="status"
                                    aria-live="polite"
                                >
                                    Se consultarán las asociaciones actuales antes de guardarlas.
                                </small>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="submit" class="btn-templo-primary">
                        <i class="bi bi-save" aria-hidden="true"></i>
                        Guardar Cambios
                    </button>

                    <button
                        type="button"
                        class="btn-templo-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                        Cancelar
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>


<!-- ===================================================== -->
<!-- MODAL CAMBIAR PERSONA DE CONTACTO -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalCambiarContactoProveedor"
    tabindex="-1"
    aria-labelledby="modalCambiarContactoProveedorLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content templo-modal">

            <form
                action="Index.php?url=proveedores"
                method="POST"
                autocomplete="off"
            >

                <!-- Acción del controlador -->
                <input
                    type="hidden"
                    name="accion"
                    value="cambiar_contacto"
                >

                <!-- Proveedor seleccionado -->
                <input
                    type="hidden"
                    name="id_proveedor"
                    id="cambiarIdProveedor"
                >

                <!-- Contacto anterior para comprobar la relación -->
                <input
                    type="hidden"
                    name="id_persona_contacto"
                    id="cambiarIdPersonaContacto"
                >


                <div class="modal-header">

                    <h5 class="modal-title" id="modalCambiarContactoProveedorLabel">
                        <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
                        Cambiar Persona de Contacto
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>

                </div>


                <div class="modal-body">

                    <!-- Empresa y persona de contacto actual -->
                    <div class="row g-3 mb-3">

                        <div class="col-md-6">

                            <label
                                for="cambiarEmpresaProveedor"
                                class="form-label"
                            >
                                Empresa
                            </label>

                            <input
                                type="text"
                                id="cambiarEmpresaProveedor"
                                class="form-control templo-input"
                                readonly
                            >

                        </div>

                        <div class="col-md-6">

                            <label
                                for="cambiarContactoActualProveedor"
                                class="form-label"
                            >
                                Contacto actual
                            </label>

                            <input
                                type="text"
                                id="cambiarContactoActualProveedor"
                                class="form-control templo-input"
                                readonly
                            >

                        </div>

                    </div>


                    <!-- Instrucciones -->
                    <p class="small mb-3">
                        Ingresa la cédula del nuevo contacto. Si la persona
                        ya está registrada, sus datos se recuperarán
                        automáticamente.
                    </p>


                    <!-- Datos del nuevo contacto -->
                    <div class="row g-3">

                        <!-- Cédula -->
                        <div class="col-md-6">

                            <label
                                for="cambiarCedulaContacto"
                                class="form-label"
                            >
                                Cédula del nuevo contacto
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="cedula"
                                id="cambiarCedulaContacto"
                                class="form-control templo-input"
                                placeholder="Ingrese la cédula"
                                minlength="6"
                                maxlength="20"
                                inputmode="numeric"
                                pattern="[0-9]{6,20}"
                                data-buscar-persona="true"
                                aria-describedby="mensajePersonaCambiarProveedor"
                                required
                            >

                            <!-- Mensaje sin fondo -->
                            <small
                                id="mensajePersonaCambiarProveedor"
                                class="proveedores-mensaje-persona d-block mt-1"
                                role="status"
                                aria-live="polite"
                                hidden
                            ></small>

                        </div>


                        <!-- Teléfono -->
                        <div class="col-md-6">

                            <label
                                for="cambiarTelefonoContacto"
                                class="form-label"
                            >
                                Teléfono
                            </label>

                            <input
                                type="text"
                                name="telefono"
                                id="cambiarTelefonoContacto"
                                class="form-control templo-input"
                                placeholder="Ingrese el teléfono"
                                maxlength="25"
                            >

                        </div>


                        <!-- Nombre -->
                        <div class="col-md-6">

                            <label
                                for="cambiarNombreContacto"
                                class="form-label"
                            >
                                Nombre del nuevo contacto
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="nombre"
                                id="cambiarNombreContacto"
                                class="form-control templo-input"
                                placeholder="Ingrese el nombre"
                                maxlength="60"
                                required
                            >

                        </div>


                        <!-- Apellido -->
                        <div class="col-md-6">

                            <label
                                for="cambiarApellidoContacto"
                                class="form-label"
                            >
                                Apellido del nuevo contacto
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="apellido"
                                id="cambiarApellidoContacto"
                                class="form-control templo-input"
                                placeholder="Ingrese el apellido"
                                maxlength="60"
                                required
                            >

                        </div>


                        <!-- Correo -->
                        <div class="col-md-6">

                            <label
                                for="cambiarCorreoContacto"
                                class="form-label"
                            >
                                Correo
                            </label>

                            <input
                                type="email"
                                name="correo"
                                id="cambiarCorreoContacto"
                                class="form-control templo-input"
                                placeholder="Ingrese el correo"
                                maxlength="100"
                            >

                        </div>


                        <!-- Ciudad -->
                        <div class="col-md-6">

                            <label
                                for="cambiarCiudadContacto"
                                class="form-label"
                            >
                                Ciudad
                            </label>

                            <input
                                type="text"
                                name="ciudad"
                                id="cambiarCiudadContacto"
                                class="form-control templo-input"
                                placeholder="Ingrese la ciudad"
                                maxlength="80"
                            >

                        </div>


                        <!-- Dirección -->
                        <div class="col-12">

                            <label
                                for="cambiarDireccionContacto"
                                class="form-label"
                            >
                                Dirección
                            </label>

                            <input
                                type="text"
                                name="direccion"
                                id="cambiarDireccionContacto"
                                class="form-control templo-input"
                                placeholder="Ingrese la dirección"
                                maxlength="150"
                            >

                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="submit" class="btn-templo-primary">
                        <i class="bi bi-save" aria-hidden="true"></i>
                        Guardar Nuevo Contacto
                    </button>

                    <button
                        type="button"
                        class="btn-templo-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                        Cancelar
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>


<!-- ===================================================== -->
<!-- MODAL DESACTIVAR PROVEEDOR -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalDesactivarProveedor"
    data-alerta-confirmacion
    data-alerta-aceptar="button[type='submit']"
    tabindex="-1"
    aria-labelledby="modalDesactivarProveedorLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content templo-modal status-modal">

            <form
                action="Index.php?url=proveedores"
                method="POST"
            >

                <input type="hidden" name="accion" value="desactivar">

                <input
                    type="hidden"
                    name="id_proveedor"
                    id="desactivarIdProveedor"
                >


                <div class="modal-header">

                    <h5 class="modal-title" id="modalDesactivarProveedorLabel">
                        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                        Desactivar Proveedor
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
                        ¿Deseas desactivar este proveedor?
                    </p>

                    <p
                        class="status-client-name"
                        id="desactivarNombreProveedor"
                    ></p>

                    <p class="status-warning">
                        El registro permanecerá guardado en el sistema.
                    </p>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn-templo-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn-templo-danger"
                    >
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        Confirmar
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>


<!-- ===================================================== -->
<!-- PLANTILLA DEL ICONO DE DETALLES -->
<!-- ===================================================== -->

<template id="plantillaIconoDetalleProveedor">

    <span class="detalle-accion-icono" aria-hidden="true">

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

</template>


<!-- ===================================================== -->
<!-- SCRIPT Y CIERRE DE LA VISTA -->
<!-- ===================================================== -->

<?php

$scriptVista = 'Assets/js/proveedores.js?v=4';
$scriptVistaModulo = true;

require_once __DIR__ . '/Layout/Footer.php';

?>