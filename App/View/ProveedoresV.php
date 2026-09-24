<?php

$tituloPagina = 'Gestión de Proveedores';

require_once __DIR__ . '/Layout/Header.php';

$escaparProveedor = static function ($valor): string {
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
};

?>

<section class="module-card clientes-module proveedores-module">

    <div class="module-header">

        <h2>
            <span
                class="title-icon custom-proveedores"
                aria-hidden="true"
            >
                <svg viewBox="0 0 64 64">
                    <path d="M4 20A3 3 0 0 1 7 17H42A3 3 0 0 1 45 20V27H52A4 4 0 0 1 55.5 29.2L60.5 36.5A4 4 0 0 1 61 38.8V48A3 3 0 0 1 58 51H54"></path>
                    <path d="M4 20V48A3 3 0 0 0 7 51H12"></path>
                    <path d="M26 51H40"></path>
                    <path d="M45 27V44"></path>
                    <path d="M45 30H54L59 38H45V30Z" stroke-width="1.5"></path>
                    <circle cx="19" cy="51" r="7"></circle>
                    <circle cx="47" cy="51" r="7"></circle>
                    <path
                        d="M24.5 35
                           C23.5 33 21 32.5 21 30.5
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
            <i class="bi bi-plus-circle"></i>
            Nuevo Proveedor
        </button>

    </div>


    <div class="table-zone">

        <div class="templo-table-wrapper">

            <div class="table-responsive">

                <table
                    id="tablaProveedores"
                    class="table table-hover templo-table w-100"
                >

                    <thead>

                        <tr class="proveedores-group-row">

                            <th
                                colspan="2"
                                scope="colgroup"
                            >
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
                            <th scope="col" class="columna-empresa">
                                Empresa
                            </th>
                            <th scope="col" class="proveedores-contact-start">
                                Nombre
                            </th>
                            <th scope="col">Apellido</th>
                            <th scope="col">Teléfono</th>
                            <th scope="col">Ciudad</th>
                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($listaProveedores as $proveedor): ?>

                            <?php

                            $nombreCompleto = trim(
                                ($proveedor['nombre'] ?? '') . ' ' .
                                ($proveedor['apellido'] ?? '')
                            );

                            $estadoProveedor = strtoupper(
                                trim(
                                    (string) (
                                        $proveedor['estado_proveedor'] ??
                                        'ACTIVO'
                                    )
                                )
                            );

                            ?>

                            <tr>

                                <td>
                                    <?= $escaparProveedor(
                                        $proveedor['rif'] ?? ''
                                    ); ?>
                                </td>

                                <td class="columna-empresa">
                                    <?= $escaparProveedor(
                                        $proveedor['nombre_empresa'] ?? ''
                                    ); ?>
                                </td>

                                <td class="proveedores-contact-start">
                                    <?= $escaparProveedor(
                                        $proveedor['nombre'] ?? ''
                                    ); ?>
                                </td>

                                <td>
                                    <?= $escaparProveedor(
                                        $proveedor['apellido'] ?? ''
                                    ); ?>
                                </td>

                                <td>
                                    <?= $escaparProveedor(
                                        $proveedor['telefono'] ?? ''
                                    ); ?>
                                </td>

                                <td>
                                    <?= $escaparProveedor(
                                        $proveedor['ciudad'] ?? ''
                                    ); ?>
                                </td>

                                <td class="columna-acciones">

                                    <div class="btn-group-actions">

                                        <!-- Ver detalles -->
                                        <button
                                            type="button"
                                            class="btn-action btn-detail btnDetalleProveedor"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDetalleProveedor"

                                            data-rif="<?= $escaparProveedor(
                                                $proveedor['rif'] ?? ''
                                            ); ?>"

                                            data-empresa="<?= $escaparProveedor(
                                                $proveedor['nombre_empresa'] ?? ''
                                            ); ?>"

                                            data-cedula="<?= $escaparProveedor(
                                                $proveedor['cedula'] ?? ''
                                            ); ?>"

                                            data-nombre-completo="<?= $escaparProveedor(
                                                $nombreCompleto
                                            ); ?>"

                                            data-telefono="<?= $escaparProveedor(
                                                $proveedor['telefono'] ?? ''
                                            ); ?>"

                                            data-correo="<?= $escaparProveedor(
                                                $proveedor['correo'] ?? ''
                                            ); ?>"

                                            data-ciudad="<?= $escaparProveedor(
                                                $proveedor['ciudad'] ?? ''
                                            ); ?>"

                                            data-direccion="<?= $escaparProveedor(
                                                $proveedor['direccion'] ?? ''
                                            ); ?>"

                                            data-estado-proveedor="<?= $escaparProveedor(
                                                $estadoProveedor
                                            ); ?>"

                                            title="Ver detalles del proveedor"
                                            aria-label="Ver detalles del proveedor"
                                        >
                                            <span
                                                class="detalle-accion-icono"
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


                                        <!-- Editar -->
                                        <button
                                            type="button"
                                            class="btn-action btn-edit btnEditarProveedor"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarProveedor"

                                            data-id-proveedor="<?= $escaparProveedor(
                                                $proveedor['id_proveedor'] ?? ''
                                            ); ?>"

                                            data-id-persona-contacto="<?= $escaparProveedor(
                                                $proveedor['id_persona_contacto'] ?? ''
                                            ); ?>"

                                            data-rif="<?= $escaparProveedor(
                                                $proveedor['rif'] ?? ''
                                            ); ?>"

                                            data-empresa="<?= $escaparProveedor(
                                                $proveedor['nombre_empresa'] ?? ''
                                            ); ?>"

                                            data-cedula="<?= $escaparProveedor(
                                                $proveedor['cedula'] ?? ''
                                            ); ?>"

                                            data-nombre="<?= $escaparProveedor(
                                                $proveedor['nombre'] ?? ''
                                            ); ?>"

                                            data-apellido="<?= $escaparProveedor(
                                                $proveedor['apellido'] ?? ''
                                            ); ?>"

                                            data-telefono="<?= $escaparProveedor(
                                                $proveedor['telefono'] ?? ''
                                            ); ?>"

                                            data-correo="<?= $escaparProveedor(
                                                $proveedor['correo'] ?? ''
                                            ); ?>"

                                            data-ciudad="<?= $escaparProveedor(
                                                $proveedor['ciudad'] ?? ''
                                            ); ?>"

                                            data-direccion="<?= $escaparProveedor(
                                                $proveedor['direccion'] ?? ''
                                            ); ?>"

                                            title="Editar proveedor"
                                            aria-label="Editar proveedor"
                                        >
                                            <i
                                                class="bi bi-pencil-square"
                                                aria-hidden="true"
                                            ></i>
                                        </button>


                                        <!-- Desactivar -->
                                        <button
                                            type="button"
                                            class="btn-action btn-delete btnDesactivarProveedor"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDesactivarProveedor"

                                            data-id-proveedor="<?= $escaparProveedor(
                                                $proveedor['id_proveedor'] ?? ''
                                            ); ?>"

                                            data-empresa="<?= $escaparProveedor(
                                                $proveedor['nombre_empresa'] ?? ''
                                            ); ?>"

                                            title="Desactivar proveedor"
                                            aria-label="Desactivar proveedor"
                                        >
                                            <i
                                                class="bi bi-trash-fill"
                                                aria-hidden="true"
                                            ></i>
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
<!-- MODAL DETALLE PROVEEDOR -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalDetalleProveedor"
    tabindex="-1"
    aria-labelledby="modalDetalleProveedorLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content templo-modal detalle-ficha-modal">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="modalDetalleProveedorLabel"
                >
                    <span class="detalle-ficha-title-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
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

                    <div class="detalle-ficha-intro">

                        <span
                            class="detalle-ficha-intro-icon"
                            aria-hidden="true"
                        >
                            <svg
                                class="detalle-ficha-intro-svg"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <rect
                                    x="8"
                                    y="8"
                                    width="48"
                                    height="48"
                                    rx="8"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    fill="none"
                                ></rect>

                                <path d="M27 18H48" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                <path d="M27 28H48" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                <path d="M27 38H48" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                <path d="M27 48H48" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>

                                <path
                                    d="M18 14
                                       C16.9 12.4 14.4 12.3 13.4 14
                                       C12.3 15.8 13.3 17.9 18 21.5
                                       C22.7 17.9 23.7 15.8 22.6 14
                                       C21.6 12.3 19.1 12.4 18 14Z"
                                    fill="currentColor"
                                ></path>

                                <path
                                    d="M18 24
                                       C16.9 22.4 14.4 22.3 13.4 24
                                       C12.3 25.8 13.3 27.9 18 31.5
                                       C22.7 27.9 23.7 25.8 22.6 24
                                       C21.6 22.3 19.1 22.4 18 24Z"
                                    fill="currentColor"
                                ></path>

                                <path
                                    d="M18 34
                                       C16.9 32.4 14.4 32.3 13.4 34
                                       C12.3 35.8 13.3 37.9 18 41.5
                                       C22.7 37.9 23.7 35.8 22.6 34
                                       C21.6 32.3 19.1 32.4 18 34Z"
                                    fill="currentColor"
                                ></path>

                                <path
                                    d="M18 44
                                       C16.9 42.4 14.4 42.3 13.4 44
                                       C12.3 45.8 13.3 47.9 18 51.5
                                       C22.7 47.9 23.7 45.8 22.6 44
                                       C21.6 42.3 19.1 42.4 18 44Z"
                                    fill="currentColor"
                                ></path>
                            </svg>
                        </span>

                        <div class="detalle-ficha-intro-content">
                            <p class="detalle-ficha-intro-title mb-0">
                                Ficha del proveedor
                            </p>
                        </div>

                    </div>


                    <div class="row g-2 detalle-ficha-grid">

                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-postcard-heart"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">RIF</span>
                                    <p class="detalle-ficha-value mb-0" id="detalleRifProveedor"></p>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-shop"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Empresa</span>
                                    <p class="detalle-ficha-value mb-0" id="detalleNombreEmpresa"></p>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-person-vcard"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Cédula del contacto</span>
                                    <p class="detalle-ficha-value mb-0" id="detalleCedulaContacto"></p>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-person-heart"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Persona de contacto</span>
                                    <p class="detalle-ficha-value mb-0" id="detalleNombreContacto"></p>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-telephone-fill"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Teléfono</span>
                                    <p class="detalle-ficha-value mb-0" id="detalleTelefonoContacto"></p>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-envelope-heart"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Correo</span>
                                    <p class="detalle-ficha-value mb-0" id="detalleCorreoContacto"></p>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="detalle-ficha-card h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Ciudad</span>
                                    <p class="detalle-ficha-value mb-0" id="detalleCiudadContacto"></p>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="detalle-ficha-card detalle-ficha-card-emphasis h-100">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-check-circle"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Estado</span>
                                    <span
                                        class="badge detalle-ficha-status estado-registro-activo"
                                        id="detalleEstadoProveedor"
                                    ></span>
                                </div>
                            </div>
                        </div>


                        <div class="col-12">
                            <div class="detalle-ficha-card detalle-ficha-card-wide detalle-ficha-card-emphasis">
                                <span class="detalle-ficha-card-icon" aria-hidden="true">
                                    <i class="bi bi-house-heart-fill"></i>
                                </span>

                                <div class="detalle-ficha-card-content">
                                    <span class="detalle-ficha-label">Dirección</span>
                                    <p class="detalle-ficha-value mb-0" id="detalleDireccionContacto"></p>
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
                    <i class="bi bi-x-circle"></i>
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
    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content templo-modal">

            <form
                action="Index.php?url=proveedores"
                method="POST"
                autocomplete="off"
            >

                <input
                    type="hidden"
                    name="accion"
                    value="registrar"
                >

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="modalNuevoProveedorLabel"
                    >
                        <i class="bi bi-plus-circle"></i>
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


                        <div class="col-md-6">

                            <label class="form-label">
                                Cédula del contacto
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                name="cedula"
                                class="form-control templo-input"
                                placeholder="Ingrese la cédula"
                                maxlength="20"
                                required
                            >

                        </div>


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


                        <div class="col-md-12">

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
<!-- MODAL EDITAR PROVEEDOR -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalEditarProveedor"
    tabindex="-1"
    aria-labelledby="modalEditarProveedorLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content templo-modal">

            <form
                action="Index.php?url=proveedores"
                method="POST"
                autocomplete="off"
            >

                <input
                    type="hidden"
                    name="accion"
                    value="modificar"
                >

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

                    <h5
                        class="modal-title"
                        id="modalEditarProveedorLabel"
                    >
                        <i class="bi bi-pencil-square"></i>
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


                        <div class="col-md-12">

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
<!-- MODAL DESACTIVAR PROVEEDOR -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="modalDesactivarProveedor"
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

                <input
                    type="hidden"
                    name="accion"
                    value="desactivar"
                >

                <input
                    type="hidden"
                    name="id_proveedor"
                    id="desactivarIdProveedor"
                >

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="modalDesactivarProveedorLabel"
                    >
                        <i class="bi bi-exclamation-triangle"></i>
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

$scriptVista = 'Assets/js/proveedores.js';

require_once __DIR__ . '/Layout/Footer.php';

?>


