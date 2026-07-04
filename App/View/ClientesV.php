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
                                        >
                                            <i class="bi bi-eye-fill"></i>
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
                                        >
                                            <i class="bi bi-pencil-fill"></i>
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
                                        >
                                            <i class="bi bi-trash-fill"></i>
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
                            <label class="form-label">Cédula</label>
                            <input type="text" name="cedula" class="form-control templo-input" placeholder="Ej: 12345678" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control templo-input" placeholder="Ej: 04120000000">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control templo-input" placeholder="Ej: María" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Apellido</label>
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
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="editarNombre" class="form-control templo-input" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Apellido</label>
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
                <h5 class="modal-title" id="modalDetalleClienteLabel">
                    <i class="bi bi-eye-fill"></i>
                    Detalles del Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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

<<?php 
$scriptVista = 'Assets/js/clientes.js'; 
require_once __DIR__ . '/Layout/Footer.php'; 
?>