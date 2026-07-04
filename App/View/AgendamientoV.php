<?php
$tituloPagina = 'Gestión de Agendamientos';

$usarFullCalendar = true;

require_once __DIR__ . '/Layout/Header.php';
?>

<section class="module-card agendamiento-module">

<div class="module-header">
    <h2>
        <i class="bi bi-calendar-heart"></i>
        Gestión de Agendamiento
    </h2>

    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
        <button
            type="button"
            class="btn-templo-secondary"
            id="btnBuscarAgendamientos"
        >
            <i class="bi bi-search-heart-fill"></i>
            Buscar Agendamientos
        </button>

        <button
            type="button"
            class="btn-templo-primary"
            data-bs-toggle="modal"
            data-bs-target="#modalNuevoAgendamiento"
        >
            <i class="bi bi-plus-circle"></i>
            Nuevo Agendamiento
        </button>
    </div>
</div>


    <div class="agendamiento-layout">

        <!-- Calendario -->
        <div class="agendamiento-card">

            <div class="agendamiento-card-header">
                <i class="bi bi-calendar-heart-fill"></i>
                Calendario de Agendamientos
            </div>

            <div class="agendamiento-card-body">
                <div id="calendar"></div>
            </div>

        </div>

        <!-- Lista de agendamientos -->
        <div class="agendamiento-card">

            <div class="agendamiento-card-header">
                <i class="bi bi-clipboard2-heart-fill"></i>
                Agendamientos de Hoy
            </div>

            <div class="agendamiento-card-body">

                <div
                    id="contenedorBusquedaAgendamientos"
                    class="agendamiento-search d-none"
                >
                    <div class="input-group">

                        <input
                            type="text"
                            id="buscarAgendamiento"
                            class="form-control"
                            placeholder="Buscar agendamientos..."
                        >

                        <button
                            type="button"
                            class="btn"
                            id="btnEjecutarBusquedaAgendamientos"
                        >
                            <i class="bi bi-search-heart-fill"></i>
                        </button>

                    </div>
                </div>

                <div class="agendamiento-info-row">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        Agendamientos correspondientes al día de hoy
                    </small>

                    <small id="ultima-actualizacion"></small>
                </div>

                <ul
                    class="list-group list-group-flush"
                    id="agendamientos-list"
                >
                    <li class="list-group-item text-center agendamiento-loading-text">
                        <i class="bi bi-calendar-x me-2"></i>
                        No hay agendamientos para mostrar.
                    </li>
                </ul>

            </div>

        </div>

    </div>

</section>


<!-- Modal Nuevo Agendamiento -->
<div
    class="modal fade"
    id="modalNuevoAgendamiento"
    tabindex="-1"
    aria-labelledby="modalNuevoAgendamientoLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">

            <form
                id="formNuevoAgendamiento"
                action="Index.php?url=agendamiento"
                method="POST"
            >
                <input
                    type="hidden"
                    name="accion"
                    value="registrar"
                >

                <div class="modal-header">
                    <h5
                        class="modal-title"
                        id="modalNuevoAgendamientoLabel"
                    >
                        <i class="bi bi-calendar-plus"></i>
                        Nuevo Agendamiento
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
                            <label
                                for="nuevoCliente"
                                class="form-label"
                            >
                                Cliente
                            </label>

                            <select
                                class="form-select templo-input"
                                id="nuevoCliente"
                                name="id_cliente"
                            >
                                <option value="">
                                    Seleccionar cliente...
                                </option>

                                <?php if (!empty($clientes)): ?>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option
                                            value="<?= (int) $cliente['id_cliente']; ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $cliente['cedula']
                                                . ' - '
                                                . $cliente['nombre']
                                                . ' '
                                                . $cliente['apellido']
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <small class="templo-help-text">
                                Puede quedar vacío cuando se registre un bloqueo de horario.
                            </small>

                            <div class="invalid-feedback">
                                Seleccione un cliente.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="nuevoUsuarioAsignado"
                                class="form-label"
                            >
                                Especialista asignada
                            </label>

                            <select
                                class="form-select templo-input"
                                id="nuevoUsuarioAsignado"
                                name="id_usuario_asignado"
                                required
                            >
                                <option value="">
                                    Seleccionar especialista...
                                </option>

                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option
                                            value="<?= (int) $usuario['id_usuario']; ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $usuario['nombre']
                                                . ' '
                                                . $usuario['apellido']
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <div class="invalid-feedback">
                                Seleccione la especialista asignada.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="nuevoTipoAgendamiento"
                                class="form-label"
                            >
                                Tipo de agendamiento
                            </label>

                            <select
                                class="form-select templo-input"
                                id="nuevoTipoAgendamiento"
                                name="id_tipo_agendamiento"
                                required
                            >
                                <option value="">
                                    Seleccionar tipo...
                                </option>

                                <?php if (!empty($tiposAgendamientos)): ?>
                                    <?php foreach ($tiposAgendamientos as $tipo): ?>
                                        <option
                                            value="<?= (int) $tipo['id_tipo_agendamiento']; ?>"
                                            data-nombre="<?= htmlspecialchars(
                                                $tipo['nombre_tipo_agendamiento']
                                            ); ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $tipo['nombre_tipo_agendamiento']
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <div class="invalid-feedback">
                                Seleccione el tipo de agendamiento.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="nuevoEstadoAgendamiento"
                                class="form-label"
                            >
                                Estado
                            </label>

                            <select
                                class="form-select templo-input"
                                id="nuevoEstadoAgendamiento"
                                name="id_estado_agendamiento"
                                required
                            >
                                <option value="">
                                    Seleccionar estado...
                                </option>

                                <?php if (!empty($estadosAgendamientos)): ?>
                                    <?php foreach ($estadosAgendamientos as $estado): ?>
                                        <option
                                            value="<?= (int) $estado['id_estado_agendamiento']; ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $estado['nombre_estado']
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <div class="invalid-feedback">
                                Seleccione el estado.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="nuevaFechaAgendamiento"
                                class="form-label"
                            >
                                Fecha
                            </label>

                            <input
                                type="date"
                                class="form-control templo-input"
                                id="nuevaFechaAgendamiento"
                                name="fecha"
                                required
                            >

                            <div class="invalid-feedback">
                                Seleccione una fecha válida.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="nuevaHoraAgendamiento"
                                class="form-label"
                            >
                                Hora
                            </label>

                            <input
                                type="time"
                                class="form-control templo-input"
                                id="nuevaHoraAgendamiento"
                                name="hora"
                                required
                            >

                            <div class="invalid-feedback">
                                Seleccione una hora válida.
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label
                                for="nuevosServiciosAgendamiento"
                                class="form-label"
                            >
                                Servicios
                            </label>

                            <select
                                class="form-select templo-input"
                                id="nuevosServiciosAgendamiento"
                                name="servicios[]"
                                multiple
                                size="4"
                            >
                                <?php if (!empty($servicios)): ?>
                                    <?php foreach ($servicios as $servicio): ?>
                                        <option
                                            value="<?= (int) $servicio['id_servicio']; ?>"
                                            data-precio="<?= htmlspecialchars(
                                                $servicio['precio']
                                            ); ?>"
                                            data-duracion="<?= (int) (
                                                $servicio['duracion_estimada'] ?? 0
                                            ); ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $servicio['nombre_servicio']
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <small class="templo-help-text">
                                Puede seleccionar uno o varios servicios.
                            </small>

                            <div class="invalid-feedback">
                                Seleccione al menos un servicio.
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label
                                for="nuevaObservacionAgendamiento"
                                class="form-label"
                            >
                                Observación inicial
                            </label>

                            <textarea
                                class="form-control templo-input"
                                id="nuevaObservacionAgendamiento"
                                name="observacion_inicial"
                                rows="3"
                                placeholder="Ingrese una observación"
                            ></textarea>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="nuevaDuracionAgendamiento"
                                class="form-label"
                            >
                                Duración total
                            </label>

                            <input
                                type="number"
                                class="form-control templo-input"
                                id="nuevaDuracionAgendamiento"
                                name="duracion_minutos"
                                min="1"
                                value="0"
                                readonly
                                required
                            >

                            <small class="templo-help-text">
                                Se calcula según los servicios. En un bloqueo de horario se indica manualmente.
                            </small>

                            <div class="invalid-feedback">
                                Indique la duración del agendamiento.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="nuevoMontoTotal"
                                class="form-label"
                            >
                                Monto total
                            </label>

                            <input
                                type="number"
                                class="form-control templo-input"
                                id="nuevoMontoTotal"
                                name="monto_total"
                                min="0"
                                step="0.01"
                                value="0.00"
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
                        Guardar Agendamiento
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


<!-- Modal Editar Agendamiento -->
<div
    class="modal fade"
    id="modalEditarAgendamiento"
    tabindex="-1"
    aria-labelledby="modalEditarAgendamientoLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content templo-modal">

            <form
                id="formEditarAgendamiento"
                action="Index.php?url=agendamiento"
                method="POST"
            >
                <input
                    type="hidden"
                    name="accion"
                    value="modificar"
                >

                <input
                    type="hidden"
                    name="id_agendamiento"
                    id="editarIdAgendamiento"
                >

                <div class="modal-header">
                    <h5
                        class="modal-title"
                        id="modalEditarAgendamientoLabel"
                    >
                        <i class="bi bi-pencil-square"></i>
                        Editar Agendamiento
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
                            <label
                                for="editarClienteAgendamiento"
                                class="form-label"
                            >
                                Cliente
                            </label>

                            <select
                                class="form-select templo-input"
                                id="editarClienteAgendamiento"
                                name="id_cliente"
                            >
                                <option value="">
                                    Seleccionar cliente...
                                </option>

                                <?php if (!empty($clientes)): ?>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option
                                            value="<?= (int) $cliente['id_cliente']; ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $cliente['cedula']
                                                . ' - '
                                                . $cliente['nombre']
                                                . ' '
                                                . $cliente['apellido']
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <small class="templo-help-text">
                                Puede quedar vacío cuando sea un bloqueo de horario.
                            </small>

                            <div class="invalid-feedback">
                                Seleccione un cliente.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="editarUsuarioAsignado"
                                class="form-label"
                            >
                                Especialista asignada
                            </label>

                            <select
                                class="form-select templo-input"
                                id="editarUsuarioAsignado"
                                name="id_usuario_asignado"
                                required
                            >
                                <option value="">
                                    Seleccionar especialista...
                                </option>

                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option
                                            value="<?= (int) $usuario['id_usuario']; ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $usuario['nombre']
                                                . ' '
                                                . $usuario['apellido']
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <div class="invalid-feedback">
                                Seleccione la especialista asignada.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="editarTipoAgendamiento"
                                class="form-label"
                            >
                                Tipo de agendamiento
                            </label>

                            <select
                                class="form-select templo-input"
                                id="editarTipoAgendamiento"
                                name="id_tipo_agendamiento"
                                required
                            >
                                <option value="">
                                    Seleccionar tipo...
                                </option>

                                <?php if (!empty($tiposAgendamientos)): ?>
                                    <?php foreach ($tiposAgendamientos as $tipo): ?>
                                        <option
                                            value="<?= (int) $tipo['id_tipo_agendamiento']; ?>"
                                            data-nombre="<?= htmlspecialchars(
                                                $tipo['nombre_tipo_agendamiento']
                                            ); ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $tipo['nombre_tipo_agendamiento']
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <div class="invalid-feedback">
                                Seleccione el tipo de agendamiento.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="editarEstadoAgendamiento"
                                class="form-label"
                            >
                                Estado
                            </label>

                            <select
                                class="form-select templo-input"
                                id="editarEstadoAgendamiento"
                                name="id_estado_agendamiento"
                                required
                            >
                                <option value="">
                                    Seleccionar estado...
                                </option>

                                <?php if (!empty($estadosAgendamientos)): ?>
                                    <?php foreach ($estadosAgendamientos as $estado): ?>
                                        <option
                                            value="<?= (int) $estado['id_estado_agendamiento']; ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $estado['nombre_estado']
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <div class="invalid-feedback">
                                Seleccione el estado.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="editarFechaAgendamiento"
                                class="form-label"
                            >
                                Fecha
                            </label>

                            <input
                                type="date"
                                class="form-control templo-input"
                                id="editarFechaAgendamiento"
                                name="fecha"
                                required
                            >

                            <div class="invalid-feedback">
                                Seleccione una fecha válida.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="editarHoraAgendamiento"
                                class="form-label"
                            >
                                Hora
                            </label>

                            <input
                                type="time"
                                class="form-control templo-input"
                                id="editarHoraAgendamiento"
                                name="hora"
                                required
                            >

                            <div class="invalid-feedback">
                                Seleccione una hora válida.
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label
                                for="editarServiciosAgendamiento"
                                class="form-label"
                            >
                                Servicios
                            </label>

                            <select
                                class="form-select templo-input"
                                id="editarServiciosAgendamiento"
                                name="servicios[]"
                                multiple
                                size="4"
                            >
                                <?php if (!empty($servicios)): ?>
                                    <?php foreach ($servicios as $servicio): ?>
                                        <option
                                            value="<?= (int) $servicio['id_servicio']; ?>"
                                            data-precio="<?= htmlspecialchars(
                                                $servicio['precio']
                                            ); ?>"
                                            data-duracion="<?= (int) (
                                                $servicio['duracion_estimada'] ?? 0
                                            ); ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $servicio['nombre_servicio']
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <small class="templo-help-text">
                                Puede seleccionar uno o varios servicios.
                            </small>

                            <div class="invalid-feedback">
                                Seleccione al menos un servicio.
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label
                                for="editarObservacionAgendamiento"
                                class="form-label"
                            >
                                Observación inicial
                            </label>

                            <textarea
                                class="form-control templo-input"
                                id="editarObservacionAgendamiento"
                                name="observacion_inicial"
                                rows="3"
                            ></textarea>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="editarDuracionAgendamiento"
                                class="form-label"
                            >
                                Duración total
                            </label>

                            <input
                                type="number"
                                class="form-control templo-input"
                                id="editarDuracionAgendamiento"
                                name="duracion_minutos"
                                min="1"
                                value="0"
                                readonly
                                required
                            >

                            <small class="templo-help-text">
                                Se calcula según los servicios. En un bloqueo de horario se indica manualmente.
                            </small>

                            <div class="invalid-feedback">
                                Indique la duración del agendamiento.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label
                                for="editarMontoTotal"
                                class="form-label"
                            >
                                Monto total
                            </label>

                            <input
                                type="number"
                                class="form-control templo-input"
                                id="editarMontoTotal"
                                name="monto_total"
                                min="0"
                                step="0.01"
                                value="0.00"
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


<!-- Modal Detalle del Agendamiento -->
<div
    class="modal fade"
    id="modalDetalleAgendamiento"
    tabindex="-1"
    aria-labelledby="modalDetalleAgendamientoLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content templo-modal">

            <div class="modal-header">
                <h5
                    class="modal-title"
                    id="modalDetalleAgendamientoLabel"
                >
                    <i class="bi bi-info-circle"></i>
                    Detalles del Agendamiento
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>
            </div>

            <div class="modal-body detalle-agendamiento">

                <p>
                    <strong>Cliente:</strong>
                    <span id="detalleClienteAgendamiento">—</span>
                </p>

                <p>
                    <strong>Servicios:</strong>
                    <span id="detalleServiciosAgendamiento">—</span>
                </p>

                <p>
                    <strong>Fecha y hora:</strong>
                    <span id="detalleFechaHoraAgendamiento">—</span>
                </p>

                <p>
                    <strong>Especialista:</strong>
                    <span id="detalleEspecialistaAgendamiento">—</span>
                </p>

                <p>
                    <strong>Tipo:</strong>
                    <span id="detalleTipoAgendamiento">—</span>
                </p>

                <p>
                    <strong>Estado:</strong>
                    <span
                        id="detalleEstadoAgendamiento"
                        class="badge estado-programada"
                    >
                        —
                    </span>
                </p>

                <p>
                    <strong>Teléfono:</strong>
                    <span id="detalleTelefonoAgendamiento">—</span>
                </p>

                <p>
                    <strong>Observación:</strong>
                    <span id="detalleObservacionAgendamiento">—</span>
                </p>

                <p>
                    <strong>Duración:</strong>
                    <span id="detalleDuracionAgendamiento">—</span>
                </p>

                <p>
                    <strong>Monto total:</strong>
                    <span id="detalleMontoAgendamiento">0.00</span>
                </p>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn-templo-secondary"
                    id="btnEditarAgendamiento"
                >
                    <i class="bi bi-pencil"></i>
                    Editar
                </button>

                <button
                    type="button"
                    class="btn-templo-danger"
                    id="btnCancelarAgendamiento"
                >
                    <i class="bi bi-calendar-x"></i>
                    Cancelar Agendamiento
                </button>

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


<!-- Formulario oculto para mover un agendamiento -->
<form
    id="formMoverAgendamiento"
    action="Index.php?url=agendamiento"
    method="POST"
    class="d-none"
>
    <input
        type="hidden"
        name="accion"
        value="mover"
    >

    <input
        type="hidden"
        name="id_agendamiento"
        id="moverIdAgendamiento"
    >

    <input
        type="hidden"
        name="fecha"
        id="moverFechaAgendamiento"
    >

    <input
        type="hidden"
        name="hora"
        id="moverHoraAgendamiento"
    >

    <input
        type="hidden"
        name="duracion_minutos"
        id="moverDuracionAgendamiento"
    >
</form>


<!-- Formulario oculto para cancelar un agendamiento -->
<form
    id="formCancelarAgendamiento"
    action="Index.php?url=agendamiento"
    method="POST"
    class="d-none"
>
    <input
        type="hidden"
        name="accion"
        value="cancelar"
    >

    <input
        type="hidden"
        name="id_agendamiento"
        id="cancelarIdAgendamiento"
    >
</form>


<?php
$scriptsVista = [
    'Assets/js/agendamiento.js',
    'Assets/js/agendamiento-calendario.js?v=4'
];

require_once __DIR__ . '/Layout/Footer.php';
?>