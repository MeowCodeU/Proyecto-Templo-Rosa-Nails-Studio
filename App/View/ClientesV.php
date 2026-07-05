<?php

$tituloPagina = 'Gestión de Clientes';

require_once __DIR__ . '/Layout/Header.php';

?>

<section class="module-card clientes-module">

    <div class="module-header">
        <h2>
            <i class="bi bi-person-hearts"></i>
            Gestión de Clientes
        </h2>

        <button type="button" class="btn-templo-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
            <i class="bi bi-plus-circle"></i>
            Nuevo Cliente
        </button>
    </div>

    <div class="table-zone">
        <div class="templo-table-wrapper">
            <div class="table-responsive">

                <table id="tablaClientes" class="table table-hover templo-table w-100">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Teléfono</th>
                            <th># Visitas</th>
                            <th>Alergias</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($listaClientes as $cliente) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cliente['cedula'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['apellido'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['visitas'] ?? 0, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['alergias'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>

                                <td>
                                    <div class="btn-group-actions">

                                        <!-- Botón Detalles -->
                                        <button
                                            type="button"
                                            class="btn-action btn-detail btnDetalleCliente"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDetalleCliente"
                                            data-cedula="<?php echo htmlspecialchars($cliente['cedula'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-nombre="<?php echo htmlspecialchars($cliente['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-apellido="<?php echo htmlspecialchars($cliente['apellido'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-telefono="<?php echo htmlspecialchars($cliente['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-correo="<?php echo htmlspecialchars($cliente['correo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-direccion="<?php echo htmlspecialchars($cliente['direccion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-ciudad="<?php echo htmlspecialchars($cliente['ciudad'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-alergias="<?php echo htmlspecialchars($cliente['alergias'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-visitas="<?php echo htmlspecialchars($cliente['visitas'] ?? 0, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-estado="<?php echo htmlspecialchars($cliente['estado'] ?? 'Activo', ENT_QUOTES, 'UTF-8'); ?>"
                                            title="Ver detalles"
                                            aria-label="Ver detalles del cliente"
                                        >
                                            <span class="cliente-detalle-icono" aria-hidden="true">
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
                                        </button>

                                        <!-- Botón Historial de Servicios -->
                                        <button
                                            type="button"
                                            class="btn-action btn-history btnHistorialCliente"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalHistorialServiciosCliente"
                                            data-id-cliente="<?php echo htmlspecialchars((string) ($cliente['id_cliente'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-cedula="<?php echo htmlspecialchars($cliente['cedula'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-nombre="<?php echo htmlspecialchars($cliente['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-apellido="<?php echo htmlspecialchars($cliente['apellido'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            title="Ver historial de servicios"
                                            aria-label="Ver historial de servicios del cliente"
                                        >
                                            <span class="cliente-historial-icono" aria-hidden="true">
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                >
                                                    <!-- Documento -->
                                                    <path
                                                        d="M4.25 2.75H12.5L16.25 6.5V12.1M4.25 2.75V20.75H11.4"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />

                                                    <!-- Esquina doblada -->
                                                    <path
                                                        d="M12.5 2.75V5.55C12.5 6.08 12.92 6.5 13.45 6.5H16.25"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />

                                                    <!-- Corazón -->
                                                    <path
                                                        d="M7.35 7.45C6.18 6.72 5.35 5.98 5.35 5.03C5.35 4.28 5.92 3.72 6.65 3.72C7.08 3.72 7.43 3.93 7.68 4.27C7.93 3.93 8.28 3.72 8.71 3.72C9.44 3.72 10.01 4.28 10.01 5.03C10.01 5.98 9.18 6.72 8.01 7.45L7.68 7.66L7.35 7.45Z"
                                                        fill="currentColor"
                                                    />

                                                    <!-- Líneas del documento -->
                                                    <path
                                                        d="M6.15 9.45H11.9M6.15 12.25H11.1M6.15 15.05H9.85"
                                                        stroke="currentColor"
                                                        stroke-width="1.55"
                                                        stroke-linecap="round"
                                                    />

                                                    <!-- Reloj -->
                                                    <circle
                                                        cx="16.65"
                                                        cy="16.65"
                                                        r="5.3"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                    />

                                                    <!-- Flecha circular -->
                                                    <path
                                                        d="M19.9 14.7C19.15 13.2 17.45 12.35 15.78 12.72C14.83 12.93 14.02 13.45 13.43 14.15"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />

                                                    <!-- Punta de la flecha -->
                                                    <path
                                                        d="M13.15 12.55L13.25 14.42L15.05 14.02"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />

                                                    <!-- Cola punteada de la flecha -->
                                                    <circle cx="13.05" cy="18.55" r="0.46" fill="currentColor" />
                                                    <circle cx="13.72" cy="19.42" r="0.40" fill="currentColor" />
                                                    <circle cx="14.62" cy="20.02" r="0.34" fill="currentColor" />

                                                    <!-- Manecillas -->
                                                    <path
                                                        d="M16.65 14.25V16.82L18.35 17.92"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />
                                                </svg>
                                            </span>
                                        </button>

                                        <!-- Botón Editar -->
                                        <button
                                            type="button"
                                            class="btn-action btn-edit btnEditarCliente"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarCliente"
                                            data-cedula="<?php echo htmlspecialchars($cliente['cedula'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-nombre="<?php echo htmlspecialchars($cliente['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-apellido="<?php echo htmlspecialchars($cliente['apellido'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-telefono="<?php echo htmlspecialchars($cliente['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-correo="<?php echo htmlspecialchars($cliente['correo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-direccion="<?php echo htmlspecialchars($cliente['direccion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-ciudad="<?php echo htmlspecialchars($cliente['ciudad'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-alergias="<?php echo htmlspecialchars($cliente['alergias'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            title="Editar cliente"
                                            aria-label="Editar cliente"
                                        >
                                            <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                                        </button>

                                        <!-- Botón Desactivar -->
                                        <button
                                            type="button"
                                            class="btn-action btn-deactivate btnDesactivarCliente"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDesactivarCliente"
                                            data-cedula="<?php echo htmlspecialchars($cliente['cedula'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-nombre="<?php echo htmlspecialchars(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                            title="Desactivar cliente"
                                            aria-label="Desactivar cliente"
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

</section>


<!-- Modal Nuevo Cliente -->
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-labelledby="modalNuevoClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">

            <form action="Index.php?url=clientes" method="POST">
                <input type="hidden" name="accion" value="registrar">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoClienteLabel">
                        <i class="bi bi-person-plus-fill"></i>
                        Registrar Nuevo Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Cédula <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="cedula" class="form-control templo-input" placeholder="Ej: 12345678" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control templo-input" placeholder="Ej: 04120000000">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nombre <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="nombre" class="form-control templo-input" placeholder="Ej: María" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Apellido <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="apellido" class="form-control templo-input" placeholder="Ej: Pérez" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control templo-input" placeholder="Ej: Barquisimeto">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Correo</label>
                            <input type="email" name="correo" class="form-control templo-input" placeholder="Ej: correo@email.com">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" class="form-control templo-input" placeholder="Ej: Av. Lara, Centro Comercial...">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Alergias u observaciones</label>
                            <textarea 
                                name="alergias" 
                                class="form-control templo-input" 
                                rows="3"
                                placeholder="Ej: alergia al acrílico, sensibilidad a productos, no presenta alergias..."
                            ></textarea>
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


<!-- Modal Editar Cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">

            <form action="Index.php?url=clientes" method="POST">
                <input type="hidden" name="accion" value="modificar">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarClienteLabel">
                        <i class="bi bi-pencil-square"></i>
                        Editar Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Cédula</label>
                            <input type="text" name="cedula" id="editarCedula" class="form-control templo-input" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" id="editarTelefono" class="form-control templo-input" placeholder="Ej: 04120000000">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nombre <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="nombre" id="editarNombre" class="form-control templo-input" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Apellido <span class="required-mark" aria-hidden="true">*</span></label>
                            <input type="text" name="apellido" id="editarApellido" class="form-control templo-input" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" id="editarCiudad" class="form-control templo-input" placeholder="Ej: Barquisimeto">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Correo</label>
                            <input type="email" name="correo" id="editarCorreo" class="form-control templo-input" placeholder="Ej: correo@email.com">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" id="editarDireccion" class="form-control templo-input" placeholder="Ej: Av. Lara, Centro Comercial...">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Alergias u observaciones</label>
                            <textarea 
                                name="alergias" 
                                id="editarAlergias" 
                                class="form-control templo-input" 
                                rows="3"
                                placeholder="Ej: alergia al acrílico, sensibilidad a productos, no presenta alergias..."
                            ></textarea>
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


<!-- Modal Detalle Cliente -->
<div class="modal fade" id="modalDetalleCliente" tabindex="-1" aria-labelledby="modalDetalleClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">

            <div class="modal-header">

    <h5
        class="modal-title"
        id="modalDetalleClienteLabel"
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

        Detalles del Cliente
    </h5>

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="modal"
        aria-label="Cerrar"
    ></button>

</div>

            <div class="modal-body">
                <div class="row g-3 detalle-cliente">

                    <div class="col-md-6">
                        <label class="form-label">Cédula</label>
                        <p id="detalleCedula" class="templo-input mb-0"></p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <p id="detalleTelefono" class="templo-input mb-0"></p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <p id="detalleNombre" class="templo-input mb-0"></p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Apellido</label>
                        <p id="detalleApellido" class="templo-input mb-0"></p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Ciudad</label>
                        <p id="detalleCiudad" class="templo-input mb-0"></p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Correo</label>
                        <p id="detalleCorreo" class="templo-input mb-0"></p>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Dirección</label>
                        <p id="detalleDireccion" class="templo-input mb-0"></p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Alergias u observaciones</label>
                        <p id="detalleAlergias" class="templo-input mb-0"></p>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label"># Visitas</label>
                        <p id="detalleVisitas" class="templo-input mb-0"></p>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <p id="detalleEstado" class="templo-input mb-0">Activo</p>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i>
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>


<!-- Modal Historial de Servicios del Cliente -->
<div
    class="modal fade"
    id="modalHistorialServiciosCliente"
    tabindex="-1"
    aria-labelledby="modalHistorialServiciosClienteLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content templo-modal">

            <div class="modal-header">
                <h5 class="modal-title" id="modalHistorialServiciosClienteLabel">
                    <span class="cliente-historial-icono" aria-hidden="true">
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                >
                                                    <!-- Documento -->
                                                    <path
                                                        d="M4.25 2.75H12.5L16.25 6.5V12.1M4.25 2.75V20.75H11.4"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />

                                                    <!-- Esquina doblada -->
                                                    <path
                                                        d="M12.5 2.75V5.55C12.5 6.08 12.92 6.5 13.45 6.5H16.25"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />

                                                    <!-- Corazón -->
                                                    <path
                                                        d="M7.35 7.45C6.18 6.72 5.35 5.98 5.35 5.03C5.35 4.28 5.92 3.72 6.65 3.72C7.08 3.72 7.43 3.93 7.68 4.27C7.93 3.93 8.28 3.72 8.71 3.72C9.44 3.72 10.01 4.28 10.01 5.03C10.01 5.98 9.18 6.72 8.01 7.45L7.68 7.66L7.35 7.45Z"
                                                        fill="currentColor"
                                                    />

                                                    <!-- Líneas del documento -->
                                                    <path
                                                        d="M6.15 9.45H11.9M6.15 12.25H11.1M6.15 15.05H9.85"
                                                        stroke="currentColor"
                                                        stroke-width="1.55"
                                                        stroke-linecap="round"
                                                    />

                                                    <!-- Reloj -->
                                                    <circle
                                                        cx="16.65"
                                                        cy="16.65"
                                                        r="5.3"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                    />

                                                    <!-- Flecha circular -->
                                                    <path
                                                        d="M19.9 14.7C19.15 13.2 17.45 12.35 15.78 12.72C14.83 12.93 14.02 13.45 13.43 14.15"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />

                                                    <!-- Punta de la flecha -->
                                                    <path
                                                        d="M13.15 12.55L13.25 14.42L15.05 14.02"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />

                                                    <!-- Cola punteada de la flecha -->
                                                    <circle cx="13.05" cy="18.55" r="0.46" fill="currentColor" />
                                                    <circle cx="13.72" cy="19.42" r="0.40" fill="currentColor" />
                                                    <circle cx="14.62" cy="20.02" r="0.34" fill="currentColor" />

                                                    <!-- Manecillas -->
                                                    <path
                                                        d="M16.65 14.25V16.82L18.35 17.92"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />
                                                </svg>
                                            </span>

                    Historial de Servicios
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>
            </div>

            <div class="modal-body">
                <div class="historial-cliente-resumen">
                    <span>Cliente:</span>
                    <strong id="historialNombreCliente">
                        Cliente seleccionado
                    </strong>
                </div>

                <div class="table-responsive">
                    <table
                        class="table table-hover templo-table historial-servicios-table"
                        id="tablaHistorialServiciosCliente"
                    >
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Servicio</th>
                                <th>Especialista</th>
                                <th>Monto</th>
                                <th>Nota técnica</th>
                            </tr>
                        </thead>

                        <tbody id="historialServiciosClienteBody">
                            <tr class="historial-servicios-vacio">
                                <td colspan="5">
                                    <span class="cliente-historial-icono" aria-hidden="true">
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                >
                                                    <!-- Documento -->
                                                    <path
                                                        d="M4.25 2.75H12.5L16.25 6.5V12.1M4.25 2.75V20.75H11.4"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />

                                                    <!-- Esquina doblada -->
                                                    <path
                                                        d="M12.5 2.75V5.55C12.5 6.08 12.92 6.5 13.45 6.5H16.25"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />

                                                    <!-- Corazón -->
                                                    <path
                                                        d="M7.35 7.45C6.18 6.72 5.35 5.98 5.35 5.03C5.35 4.28 5.92 3.72 6.65 3.72C7.08 3.72 7.43 3.93 7.68 4.27C7.93 3.93 8.28 3.72 8.71 3.72C9.44 3.72 10.01 4.28 10.01 5.03C10.01 5.98 9.18 6.72 8.01 7.45L7.68 7.66L7.35 7.45Z"
                                                        fill="currentColor"
                                                    />

                                                    <!-- Líneas del documento -->
                                                    <path
                                                        d="M6.15 9.45H11.9M6.15 12.25H11.1M6.15 15.05H9.85"
                                                        stroke="currentColor"
                                                        stroke-width="1.55"
                                                        stroke-linecap="round"
                                                    />

                                                    <!-- Reloj -->
                                                    <circle
                                                        cx="16.65"
                                                        cy="16.65"
                                                        r="5.3"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                    />

                                                    <!-- Flecha circular -->
                                                    <path
                                                        d="M19.9 14.7C19.15 13.2 17.45 12.35 15.78 12.72C14.83 12.93 14.02 13.45 13.43 14.15"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />

                                                    <!-- Punta de la flecha -->
                                                    <path
                                                        d="M13.15 12.55L13.25 14.42L15.05 14.02"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />

                                                    <!-- Cola punteada de la flecha -->
                                                    <circle cx="13.05" cy="18.55" r="0.46" fill="currentColor" />
                                                    <circle cx="13.72" cy="19.42" r="0.40" fill="currentColor" />
                                                    <circle cx="14.62" cy="20.02" r="0.34" fill="currentColor" />

                                                    <!-- Manecillas -->
                                                    <path
                                                        d="M16.65 14.25V16.82L18.35 17.92"
                                                        stroke="currentColor"
                                                        stroke-width="1.65"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />
                                                </svg>
                                            </span>

                                    <span>
                                        No hay servicios registrados para esta clienta.
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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

<!-- Modal Desactivar Cliente -->
<div class="modal fade" id="modalDesactivarCliente" tabindex="-1" aria-labelledby="modalDesactivarClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content templo-modal status-modal">

            <form action="Index.php?url=clientes" method="POST">
                <input type="hidden" name="accion" value="desactivar">
                <input type="hidden" name="cedula" id="desactivarCedula" value="">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalDesactivarClienteLabel">
                        <i class="bi bi-person-dash"></i>
                        Confirmar desactivación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body text-center">
                    <p class="status-question">¿Deseas desactivar este cliente?</p>
                    <p class="status-client-name" id="desactivarNombreCliente"></p>
                    <p class="status-warning">El cliente no se borrará. Solo dejará de aparecer en la lista principal.</p>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-templo-danger">
                        <i class="bi bi-person-dash"></i>
                        Sí, desactivar
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

<?php 
$scriptVista = 'Assets/js/clientes.js'; 
require_once __DIR__ . '/Layout/Footer.php'; 
?>