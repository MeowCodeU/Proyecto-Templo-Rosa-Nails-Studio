<?php

$tituloPagina = 'Configuración del Sistema';

require_once __DIR__ . '/Layout/Header.php';

?>

<section class="module-card clientes-module configuracion-module">

    <div class="module-header">
        <h2>
            <i class="bi bi-gear-wide-connected"></i>
            Configuración del Sistema
        </h2>
    </div>

    <div class="configuracion-contenido">

        <!-- Pestañas de las tablas maestras -->
        <ul class="nav nav-tabs config-tabs" id="configTabs" role="tablist">

            <li class="nav-item" role="presentation">
                <button
                    class="nav-link active"
                    id="servicios-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#servicios"
                    type="button"
                    role="tab"
                    aria-controls="servicios"
                    aria-selected="true"
                >
                    <span class="config-tab-icon tab-servicio-icon" aria-hidden="true">
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
                                d="M32 45 C31 43.5 28 41.8 28 39.5 C28 37.7 29.4 36.5 31 36.5 C32 36.5 32.8 37 33.3 37.9 C33.8 37 34.6 36.5 35.6 36.5 C37.2 36.5 38.6 37.7 38.6 39.5 C38.6 41.8 35.6 43.5 32 46Z"
                            ></path>
                        </svg>
                    </span>
                    Servicios
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button
                    class="nav-link"
                    id="roles-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#roles"
                    type="button"
                    role="tab"
                    aria-controls="roles"
                    aria-selected="false"
                >
                    <i class="bi bi-person-heart"></i>
                    Roles
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button
                    class="nav-link"
                    id="estados-agendamiento-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#estados-agendamiento"
                    type="button"
                    role="tab"
                    aria-controls="estados-agendamiento"
                    aria-selected="false"
                >
                    <i class="bi bi-bookmark-heart"></i>
                    Estados de agendamiento
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button
                    class="nav-link"
                    id="tipos-agendamiento-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#tipos-agendamiento"
                    type="button"
                    role="tab"
                    aria-controls="tipos-agendamiento"
                    aria-selected="false"
                >
                    <i class="bi bi-calendar2-heart-fill"></i>
                    Tipos de agendamiento
                </button>
            </li>

        </ul>

        <div class="tab-content configuracion-tab-content" id="configTabsContent">

            <!-- ================================================= -->
            <!-- SERVICIOS -->
            <!-- ================================================= -->
            <div
                class="tab-pane fade show active"
                id="servicios"
                role="tabpanel"
                aria-labelledby="servicios-tab"
                tabindex="0"
            >
                <div class="config-tab-head">
                    <h5 class="config-title">Gestión de Servicios</h5>

                    <button
                        type="button"
                        class="btn-templo-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modalNuevoServicio"
                    >
                        <i class="bi bi-plus-circle"></i>
                        Nuevo Servicio
                    </button>
                </div>

                <div class="table-zone configuracion-table-zone">
                    <div class="templo-table-wrapper">
                        <div class="table-responsive">
                            <table
                                id="tablaConfigServicios"
                                class="table table-hover templo-table w-100"
                            >
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Descripción</th>
                                        <th>Precio</th>
                                        <th>Duración estimada</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <!-- Los registros vendrán del modelo. -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================================================= -->
            <!-- ROLES -->
            <!-- ================================================= -->
            <div
                class="tab-pane fade"
                id="roles"
                role="tabpanel"
                aria-labelledby="roles-tab"
                tabindex="0"
            >
                <div class="config-tab-head">
                    <h5 class="config-title">Gestión de Roles</h5>

                    <button
                        type="button"
                        class="btn-templo-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modalNuevoRol"
                    >
                        <i class="bi bi-plus-circle"></i>
                        Nuevo Rol
                    </button>
                </div>

                <div class="table-zone configuracion-table-zone">
                    <div class="templo-table-wrapper">
                        <div class="table-responsive">
                            <table
                                id="tablaConfigRoles"
                                class="table table-hover templo-table w-100"
                            >
                                <thead>
                                    <tr>
                                        <th>Rol</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <!-- Los registros vendrán del modelo. -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================================================= -->
            <!-- ESTADOS DE AGENDAMIENTO -->
            <!-- ================================================= -->
            <div
                class="tab-pane fade"
                id="estados-agendamiento"
                role="tabpanel"
                aria-labelledby="estados-agendamiento-tab"
                tabindex="0"
            >
                <div class="config-tab-head">
                    <h5 class="config-title">Gestión de Estados de Agendamiento</h5>

                    <button
                        type="button"
                        class="btn-templo-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modalNuevoEstadoAgendamiento"
                    >
                        <i class="bi bi-plus-circle"></i>
                        Nuevo Estado
                    </button>
                </div>

                <div class="table-zone configuracion-table-zone">
                    <div class="templo-table-wrapper">
                        <div class="table-responsive">
                            <table
                                id="tablaConfigEstadosAgendamiento"
                                class="table table-hover templo-table w-100"
                            >
                                <thead>
                                    <tr>
                                        <th>Estado</th>
                                        <th>Descripción</th>
                                        <th>Estado del registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <!-- Los registros vendrán del modelo. -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================================================= -->
            <!-- TIPOS DE AGENDAMIENTO -->
            <!-- ================================================= -->
            <div
                class="tab-pane fade"
                id="tipos-agendamiento"
                role="tabpanel"
                aria-labelledby="tipos-agendamiento-tab"
                tabindex="0"
            >
                <div class="config-tab-head">
                    <h5 class="config-title">Gestión de Tipos de Agendamiento</h5>

                    <button
                        type="button"
                        class="btn-templo-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modalNuevoTipoAgendamiento"
                    >
                        <i class="bi bi-plus-circle"></i>
                        Nuevo Tipo
                    </button>
                </div>

                <div class="table-zone configuracion-table-zone">
                    <div class="templo-table-wrapper">
                        <div class="table-responsive">
                            <table
                                id="tablaConfigTiposAgendamiento"
                                class="table table-hover templo-table w-100"
                            >
                                <thead>
                                    <tr>
                                        <th>Tipo de agendamiento</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <!-- Los registros vendrán del modelo. -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</section>


<!-- ===================================================== -->
<!-- MODALES DE SERVICIOS -->
<!-- ===================================================== -->

<div class="modal fade" id="modalNuevoServicio" tabindex="-1" aria-labelledby="modalNuevoServicioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">
            <form action="Index.php?url=configuracion" method="POST">
                <input type="hidden" name="accion" value="registrar_servicio">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoServicioLabel">
                        <i class="bi bi-plus-circle"></i>
                        Registrar Nuevo Servicio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del servicio <span class="required-mark" aria-hidden="true">*</span></label>
                            <input
                                type="text"
                                name="nombre_servicio"
                                class="form-control templo-input"
                                placeholder="Ingrese el nombre del servicio"
                                maxlength="120"
                                required
                            >
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Precio <span class="required-mark" aria-hidden="true">*</span></label>
                            <input
                                type="number"
                                name="precio"
                                class="form-control templo-input"
                                placeholder="0.00"
                                min="0"
                                step="0.01"
                                required
                            >
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Duración estimada</label>
                            <input
                                type="number"
                                name="duracion_estimada"
                                class="form-control templo-input"
                                placeholder="Minutos"
                                min="1"
                            >
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <textarea
                                name="descripcion"
                                class="form-control templo-input"
                                rows="3"
                                maxlength="255"
                                placeholder="Ingrese la descripción del servicio"
                            ></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado <span class="required-mark" aria-hidden="true">*</span></label>
                            <select name="estado_servicio" class="form-select templo-input" required>
                                <option value="ACTIVO" selected>ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>
                    </div>
                </div>

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

<div class="modal fade" id="modalEditarServicio" tabindex="-1" aria-labelledby="modalEditarServicioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">
            <form action="Index.php?url=configuracion" method="POST">
                <input type="hidden" name="accion" value="modificar_servicio">
                <input type="hidden" name="id_servicio" id="editarIdServicio">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarServicioLabel">
                        <i class="bi bi-pencil-square"></i>
                        Editar Servicio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del servicio <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="nombre_servicio" id="editarNombreServicio" class="form-control templo-input" maxlength="120" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Precio <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="number" name="precio" id="editarPrecioServicio" class="form-control templo-input" min="0" step="0.01" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Duración estimada</label>
                            <input type="number" name="duracion_estimada" id="editarDuracionServicio" class="form-control templo-input" min="1">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" id="editarDescripcionServicio" class="form-control templo-input" rows="3" maxlength="255"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado <span class="required-mark" aria-hidden="true">*</span></label>
                            <select name="estado_servicio" id="editarEstadoServicio" class="form-select templo-input" required>
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>
                    </div>
                </div>

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

<div class="modal fade" id="modalEstadoServicio" tabindex="-1" aria-labelledby="modalEstadoServicioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content templo-modal status-modal">
            <form action="Index.php?url=configuracion" method="POST">
                <input type="hidden" name="accion" value="cambiar_estado_servicio">
                <input type="hidden" name="id_servicio" id="estadoIdServicio">
                <input type="hidden" name="estado_servicio" id="nuevoEstadoServicio">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEstadoServicioLabel">
                        <i class="bi bi-exclamation-triangle"></i>
                        Cambiar estado del servicio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body text-center">
                    <p class="status-question" id="preguntaEstadoServicio">¿Deseas cambiar el estado de este servicio?</p>
                    <p class="status-client-name" id="nombreEstadoServicio"></p>
                    <p class="status-warning">El registro permanecerá guardado en el sistema.</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>

                    <button type="submit" class="btn-templo-danger">
                        <i class="bi bi-check-circle"></i>
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ===================================================== -->
<!-- MODALES DE ROLES -->
<!-- ===================================================== -->

<div class="modal fade" id="modalNuevoRol" tabindex="-1" aria-labelledby="modalNuevoRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">
            <form action="Index.php?url=configuracion" method="POST">
                <input type="hidden" name="accion" value="registrar_rol">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoRolLabel">
                        <i class="bi bi-plus-circle"></i>
                        Registrar Nuevo Rol
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del rol <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="nombre_rol" class="form-control templo-input" placeholder="Ingrese el nombre del rol" maxlength="40" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado <span class="required-mark" aria-hidden="true">*</span></label>
                            <select name="estado_rol" class="form-select templo-input" required>
                                <option value="ACTIVO" selected>ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control templo-input" rows="3" maxlength="150" placeholder="Ingrese la descripción del rol"></textarea>
                        </div>
                    </div>
                </div>

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

<div class="modal fade" id="modalEditarRol" tabindex="-1" aria-labelledby="modalEditarRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">
            <form action="Index.php?url=configuracion" method="POST">
                <input type="hidden" name="accion" value="modificar_rol">
                <input type="hidden" name="id_rol" id="editarIdRol">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarRolLabel">
                        <i class="bi bi-pencil-square"></i>
                        Editar Rol
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del rol <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="nombre_rol" id="editarNombreRol" class="form-control templo-input" maxlength="40" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado <span class="required-mark" aria-hidden="true">*</span></label>
                            <select name="estado_rol" id="editarEstadoRol" class="form-select templo-input" required>
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" id="editarDescripcionRol" class="form-control templo-input" rows="3" maxlength="150"></textarea>
                        </div>
                    </div>
                </div>

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

<div class="modal fade" id="modalEstadoRol" tabindex="-1" aria-labelledby="modalEstadoRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content templo-modal status-modal">
            <form action="Index.php?url=configuracion" method="POST">
                <input type="hidden" name="accion" value="cambiar_estado_rol">
                <input type="hidden" name="id_rol" id="estadoIdRol">
                <input type="hidden" name="estado_rol" id="nuevoEstadoRol">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEstadoRolLabel">
                        <i class="bi bi-exclamation-triangle"></i>
                        Cambiar estado del rol
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body text-center">
                    <p class="status-question">¿Deseas cambiar el estado de este rol?</p>
                    <p class="status-client-name" id="nombreEstadoRol"></p>
                    <p class="status-warning">El registro permanecerá guardado en el sistema.</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn-templo-danger">
                        <i class="bi bi-check-circle"></i>
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ===================================================== -->
<!-- MODALES DE ESTADOS DE AGENDAMIENTO -->
<!-- ===================================================== -->

<div class="modal fade" id="modalNuevoEstadoAgendamiento" tabindex="-1" aria-labelledby="modalNuevoEstadoAgendamientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">
            <form action="Index.php?url=configuracion" method="POST">
                <input type="hidden" name="accion" value="registrar_estado_agendamiento">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoEstadoAgendamientoLabel">
                        <i class="bi bi-plus-circle"></i>
                        Registrar Estado de Agendamiento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del estado <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="nombre_estado" class="form-control templo-input" placeholder="Ingrese el nombre del estado" maxlength="50" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado del registro <span class="required-mark" aria-hidden="true">*</span></label>
                            <select name="estado_registro" class="form-select templo-input" required>
                                <option value="ACTIVO" selected>ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control templo-input" rows="3" maxlength="150" placeholder="Ingrese la descripción del estado"></textarea>
                        </div>
                    </div>
                </div>

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

<div class="modal fade" id="modalEditarEstadoAgendamiento" tabindex="-1" aria-labelledby="modalEditarEstadoAgendamientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">
            <form action="Index.php?url=configuracion" method="POST">
                <input type="hidden" name="accion" value="modificar_estado_agendamiento">
                <input type="hidden" name="id_estado_agendamiento" id="editarIdEstadoAgendamiento">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarEstadoAgendamientoLabel">
                        <i class="bi bi-pencil-square"></i>
                        Editar Estado de Agendamiento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del estado <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="nombre_estado" id="editarNombreEstadoAgendamiento" class="form-control templo-input" maxlength="50" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado del registro <span class="required-mark" aria-hidden="true">*</span></label>
                            <select name="estado_registro" id="editarRegistroEstadoAgendamiento" class="form-select templo-input" required>
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" id="editarDescripcionEstadoAgendamiento" class="form-control templo-input" rows="3" maxlength="150"></textarea>
                        </div>
                    </div>
                </div>

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

<div class="modal fade" id="modalEstadoEstadoAgendamiento" tabindex="-1" aria-labelledby="modalEstadoEstadoAgendamientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content templo-modal status-modal">
            <form action="Index.php?url=configuracion" method="POST">
                <input type="hidden" name="accion" value="cambiar_estado_agendamiento">
                <input type="hidden" name="id_estado_agendamiento" id="estadoIdEstadoAgendamiento">
                <input type="hidden" name="estado_registro" id="nuevoRegistroEstadoAgendamiento">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEstadoEstadoAgendamientoLabel">
                        <i class="bi bi-exclamation-triangle"></i>
                        Cambiar estado del registro
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body text-center">
                    <p class="status-question">¿Deseas cambiar el estado de este registro?</p>
                    <p class="status-client-name" id="nombreEstadoEstadoAgendamiento"></p>
                    <p class="status-warning">El estado de agendamiento no será eliminado de la base de datos.</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn-templo-danger">
                        <i class="bi bi-check-circle"></i>
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ===================================================== -->
<!-- MODALES DE TIPOS DE AGENDAMIENTO -->
<!-- ===================================================== -->

<div class="modal fade" id="modalNuevoTipoAgendamiento" tabindex="-1" aria-labelledby="modalNuevoTipoAgendamientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">
            <form action="Index.php?url=configuracion" method="POST">
                <input type="hidden" name="accion" value="registrar_tipo_agendamiento">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoTipoAgendamientoLabel">
                        <i class="bi bi-plus-circle"></i>
                        Registrar Tipo de Agendamiento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del tipo <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="nombre_tipo_agendamiento" class="form-control templo-input" placeholder="Ingrese el nombre del tipo" maxlength="60" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado <span class="required-mark" aria-hidden="true">*</span></label>
                            <select name="estado_tipo_agendamiento" class="form-select templo-input" required>
                                <option value="ACTIVO" selected>ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control templo-input" rows="3" maxlength="150" placeholder="Ingrese la descripción del tipo de agendamiento"></textarea>
                        </div>
                    </div>
                </div>

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

<div class="modal fade" id="modalEditarTipoAgendamiento" tabindex="-1" aria-labelledby="modalEditarTipoAgendamientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">
            <form action="Index.php?url=configuracion" method="POST">
                <input type="hidden" name="accion" value="modificar_tipo_agendamiento">
                <input type="hidden" name="id_tipo_agendamiento" id="editarIdTipoAgendamiento">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarTipoAgendamientoLabel">
                        <i class="bi bi-pencil-square"></i>
                        Editar Tipo de Agendamiento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del tipo <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="nombre_tipo_agendamiento" id="editarNombreTipoAgendamiento" class="form-control templo-input" maxlength="60" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado <span class="required-mark" aria-hidden="true">*</span></label>
                            <select name="estado_tipo_agendamiento" id="editarEstadoTipoAgendamiento" class="form-select templo-input" required>
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" id="editarDescripcionTipoAgendamiento" class="form-control templo-input" rows="3" maxlength="150"></textarea>
                        </div>
                    </div>
                </div>

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

<div class="modal fade" id="modalEstadoTipoAgendamiento" tabindex="-1" aria-labelledby="modalEstadoTipoAgendamientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content templo-modal status-modal">
            <form action="Index.php?url=configuracion" method="POST">
                <input type="hidden" name="accion" value="cambiar_estado_tipo_agendamiento">
                <input type="hidden" name="id_tipo_agendamiento" id="estadoIdTipoAgendamiento">
                <input type="hidden" name="estado_tipo_agendamiento" id="nuevoEstadoTipoAgendamiento">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEstadoTipoAgendamientoLabel">
                        <i class="bi bi-exclamation-triangle"></i>
                        Cambiar estado del tipo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body text-center">
                    <p class="status-question">¿Deseas cambiar el estado de este tipo de agendamiento?</p>
                    <p class="status-client-name" id="nombreEstadoTipoAgendamiento"></p>
                    <p class="status-warning">El registro permanecerá guardado en el sistema.</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn-templo-danger">
                        <i class="bi bi-check-circle"></i>
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$scriptVista = 'Assets/js/configuracion.js';
require_once __DIR__ . '/Layout/Footer.php';
?>