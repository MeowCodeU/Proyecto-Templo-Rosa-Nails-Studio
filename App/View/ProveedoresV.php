<?php

$tituloPagina = 'Gestión de Proveedores';

require_once __DIR__ . '/Layout/Header.php';

?>

<section class="module-card clientes-module proveedores-module">

    <div class="module-header">
        <h2>
            <span class="title-icon custom-proveedores" aria-hidden="true">
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
                        <th colspan="2" scope="colgroup">
                             Datos de la empresa
                            </th>
                            <th colspan="4" scope="colgroup">
                                Persona de contacto
                            </th>
                            <th aria-hidden="true"></th>
                        </tr>
                        <tr class="proveedores-columns-row">
                            <th scope="col">RIF</th> 
                            <th scope="col" class="columna-empresa">Empresa</th>
                            <th scope="col">Nombre</th> 
                            <th scope="col">Apellido</th> 
                            <th scope="col">Teléfono</th> 
                            <th scope="col">Ciudad</th> 
                            <th scope="col">Acciones</th> 
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</section>


<!-- Modal Nuevo Proveedor -->
<div
    class="modal fade"
    id="modalNuevoProveedor"
    tabindex="-1"
    aria-labelledby="modalNuevoProveedorLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">

            <form action="Index.php?url=proveedores" method="POST">
                <input type="hidden" name="accion" value="registrar">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoProveedorLabel">
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
                            <label class="form-label">RIF</label>

                            <input
                                type="text"
                                name="rif"
                                class="form-control templo-input"
                                placeholder="Ingrese el RIF"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Nombre de la empresa
                            </label>

                            <input
                                type="text"
                                name="nombre_empresa"
                                class="form-control templo-input"
                                placeholder="Ingrese el nombre de la empresa"
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
                                class="form-control templo-input"
                                placeholder="Ingrese la cédula"
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
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Nombre del contacto
                            </label>

                            <input
                                type="text"
                                name="nombre"
                                class="form-control templo-input"
                                placeholder="Ingrese el nombre"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Apellido del contacto
                            </label>

                            <input
                                type="text"
                                name="apellido"
                                class="form-control templo-input"
                                placeholder="Ingrese el apellido"
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


<!-- Modal Editar Proveedor -->
<div
    class="modal fade"
    id="modalEditarProveedor"
    tabindex="-1"
    aria-labelledby="modalEditarProveedorLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">

            <form action="Index.php?url=proveedores" method="POST">
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
                            <label class="form-label">RIF</label>

                            <input
                                type="text"
                                name="rif"
                                id="editarRifProveedor"
                                class="form-control templo-input"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Nombre de la empresa
                            </label>

                            <input
                                type="text"
                                name="nombre_empresa"
                                id="editarNombreEmpresa"
                                class="form-control templo-input"
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
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Nombre del contacto
                            </label>

                            <input
                                type="text"
                                name="nombre"
                                id="editarNombreContacto"
                                class="form-control templo-input"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Apellido del contacto
                            </label>

                            <input
                                type="text"
                                name="apellido"
                                id="editarApellidoContacto"
                                class="form-control templo-input"
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


<!-- Modal Eliminar Proveedor -->
<div
    class="modal fade"
    id="modalEliminarProveedor"
    tabindex="-1"
    aria-labelledby="modalEliminarProveedorLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content templo-modal delete-modal">

            <form action="Index.php?url=proveedores" method="POST">
                <input type="hidden" name="accion" value="eliminar">

                <input
                    type="hidden"
                    name="id_proveedor"
                    id="eliminarIdProveedor"
                >

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEliminarProveedorLabel">
                        <i class="bi bi-trash-fill"></i>
                        Confirmar eliminación
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>
                </div>

                <div class="modal-body text-center">
                    <p class="delete-question">
                        ¿Deseas eliminar este proveedor?
                    </p>

                    <p
                        class="delete-client-name"
                        id="eliminarNombreProveedor"
                    ></p>

                    <p class="delete-warning">
                        Esta acción no se puede deshacer.
                    </p>
                </div>

                <div class="modal-footer">
                    <button
                        type="submit"
                        class="btn-templo-danger"
                    >
                        <i class="bi bi-trash-fill"></i>
                        Sí, eliminar
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
$scriptVista = 'Assets/js/proveedores.js';

require_once __DIR__ . '/Layout/Footer.php';
?>