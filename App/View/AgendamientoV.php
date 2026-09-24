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
            class="btn-templo-bloqueo"
            data-bs-toggle="modal"
            data-bs-target="#modalNuevoBloqueoHorario"
        >
<svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                class="templo-lock-heart"
                aria-hidden="true"
            >
                <path
                    d="M7.5 10V7a4.5 4.5 0 0 1 9 0v3"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                ></path>
                <rect
                    x="5"
                    y="9.5"
                    width="14"
                    height="11"
                    rx="2.5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                ></rect>
                <path
                    d="M12 17.45s-3.1-1.72-3.1-3.86c0-1.04.78-1.82 1.79-1.82.58 0 1.05.28 1.31.69.26-.41.73-.69 1.31-.69 1.01 0 1.79.78 1.79 1.82 0 2.14-3.1 3.86-3.1 3.86Z"
                    fill="currentColor"
                ></path>
            </svg>
            Bloquear Horario
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
                             <span class="required-mark" aria-hidden="true">*</span></label>

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
                             <span class="required-mark" aria-hidden="true">*</span></label>

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
                             <span class="required-mark" aria-hidden="true">*</span></label>

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
                             <span class="required-mark" aria-hidden="true">*</span></label>

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
                             <span class="required-mark" aria-hidden="true">*</span></label>

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
                                class="form-label"
                                id="nuevosServiciosAgendamientoLabel"
                            >
                                Servicios
                            </label>

                            <div
                                class="agendamiento-servicios-selector"
                                id="nuevosServiciosAgendamiento"
                                data-servicios-selector="nuevo"
                            >
                                <button
                                    type="button"
                                    class="form-select templo-input agendamiento-servicios-toggle"
                                    id="nuevoServiciosToggle"
                                    aria-expanded="false"
                                    aria-controls="nuevoServiciosPanel"
                                >
                                    <span id="nuevoServiciosTexto">
                                        Seleccionar servicios...
                                    </span>

                                    <i
                                        class="bi bi-chevron-down"
                                        aria-hidden="true"
                                    ></i>
                                </button>

                                <div
                                    class="agendamiento-servicios-panel"
                                    id="nuevoServiciosPanel"
                                    hidden
                                >
                                    <div class="agendamiento-servicios-buscador">
                                        <i class="bi bi-search"></i>

                                        <input
                                            type="search"
                                            class="form-control templo-input"
                                            id="nuevoServiciosBuscar"
                                            placeholder="Buscar servicio..."
                                            autocomplete="off"
                                        >
                                    </div>

                                    <div
                                        class="agendamiento-servicios-lista"
                                        id="nuevoServiciosLista"
                                    >
                                        <?php if (!empty($servicios)): ?>

                                            <?php foreach ($servicios as $servicio): ?>

                                                <?php

                                                $idServicio = (int) (
                                                    $servicio['id_servicio'] ?? 0
                                                );

                                                $nombreServicio = (string) (
                                                    $servicio['nombre_servicio'] ?? ''
                                                );

                                                $precioServicio = (float) (
                                                    $servicio['precio'] ?? 0
                                                );

                                                $duracionServicio = (int) (
                                                    $servicio['duracion_estimada'] ?? 0
                                                );

                                                ?>

                                                <label
                                                    class="agendamiento-servicio-opcion"
                                                    data-servicio-nombre="<?= htmlspecialchars(
                                                        $nombreServicio,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ); ?>"
                                                    for="nuevoServicio<?= $idServicio; ?>"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input agendamiento-servicio-check"
                                                        id="nuevoServicio<?= $idServicio; ?>"
                                                        name="servicios[]"
                                                        value="<?= $idServicio; ?>"
                                                        data-nombre="<?= htmlspecialchars(
                                                            $nombreServicio,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ); ?>"
                                                        data-precio="<?= number_format(
                                                            $precioServicio,
                                                            2,
                                                            '.',
                                                            ''
                                                        ); ?>"
                                                        data-duracion="<?= $duracionServicio; ?>"
                                                    >

                                                    <span class="agendamiento-servicio-contenido">
                                                        <span class="agendamiento-servicio-nombre">
                                                            <?= htmlspecialchars(
                                                                $nombreServicio,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ); ?>
                                                        </span>

                                                        <span class="agendamiento-servicio-duracion">
                                                            · <?= $duracionServicio; ?> min
                                                        </span>
                                                    </span>

                                                    <span class="agendamiento-servicio-precio">
                                                        <?= number_format(
                                                            $precioServicio,
                                                            2,
                                                            ',',
                                                            '.'
                                                        ); ?>
                                                    </span>
                                                </label>

                                            <?php endforeach; ?>

                                        <?php else: ?>

                                            <p class="agendamiento-servicios-sin-resultados">
                                                No hay servicios activos disponibles.
                                            </p>

                                        <?php endif; ?>
                                    </div>

                                    <p
                                        class="agendamiento-servicios-sin-resultados d-none"
                                        id="nuevoServiciosSinResultados"
                                    >
                                        No se encontraron servicios.
                                    </p>
                                </div>
                            </div>

                            <small class="templo-help-text">
                                Puede seleccionar uno o varios servicios.
                            </small>

                            <div
                                class="agendamiento-servicios-resumen"
                                id="nuevoServiciosResumen"
                            >
                                <span class="agendamiento-servicios-resumen-vacio">
                                    Ningún servicio seleccionado.
                                </span>
                            </div>

                            <div
                                class="invalid-feedback"
                                id="nuevoServiciosFeedback"
                            >
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
                                Se calcula según los servicios seleccionados.
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


<!-- Modal Nuevo Bloqueo de Horario -->
<div
    class="modal fade"
    id="modalNuevoBloqueoHorario"
    tabindex="-1"
    aria-labelledby="modalNuevoBloqueoHorarioLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content templo-modal bloqueo-horario-modal">

            <form
                id="formNuevoBloqueoHorario"
                action="Index.php?url=agendamiento"
                method="POST"
            >
                <input type="hidden" name="accion" value="registrar_bloqueo">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoBloqueoHorarioLabel">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            class="templo-lock-heart"
                            aria-hidden="true"
                        >
                            <path
                                d="M7.5 10V7a4.5 4.5 0 0 1 9 0v3"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            ></path>
                            <rect
                                x="5"
                                y="9.5"
                                width="14"
                                height="11"
                                rx="2.5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            ></rect>
                            <path
                                d="M12 17.45s-3.1-1.72-3.1-3.86c0-1.04.78-1.82 1.79-1.82.58 0 1.05.28 1.31.69.26-.41.73-.69 1.31-.69 1.01 0 1.79.78 1.79 1.82 0 2.14-3.1 3.86-3.1 3.86Z"
                                fill="currentColor"
                            ></path>
                        </svg>
                        Bloquear Horario
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

                        <div class="col-md-12">
                            <label for="bloqueoUsuarioEspecialista" class="form-label">
                                Especialista
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <select
                                class="form-select templo-input"
                                id="bloqueoUsuarioEspecialista"
                                name="id_usuario_especialista"
                                required
                            >
                                <option value="">Seleccionar especialista...</option>

                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= (int) $usuario['id_usuario']; ?>">
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
                                Seleccione la especialista.
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="bloqueoFecha" class="form-label">
                                Fecha
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="date"
                                class="form-control templo-input"
                                id="bloqueoFecha"
                                name="fecha"
                                required
                            >

                            <div class="invalid-feedback">
                                Seleccione una fecha válida.
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="bloqueoHoraInicio" class="form-label">
                                Hora inicio
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="time"
                                class="form-control templo-input"
                                id="bloqueoHoraInicio"
                                name="hora_inicio"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label for="bloqueoHoraFin" class="form-label">
                                Hora fin
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="time"
                                class="form-control templo-input"
                                id="bloqueoHoraFin"
                                name="hora_fin"
                                required
                            >

                            <div class="invalid-feedback">
                                La hora fin debe ser posterior a la hora inicio.
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="bloqueoMotivo" class="form-label">
                                Motivo
                            </label>

                            <textarea
                                class="form-control templo-input"
                                id="bloqueoMotivo"
                                name="motivo"
                                rows="3"
                                placeholder="Indique el motivo del bloqueo"
                            ></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-templo-primary">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            class="templo-lock-heart"
                            aria-hidden="true"
                        >
                            <path
                                d="M7.5 10V7a4.5 4.5 0 0 1 9 0v3"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            ></path>
                            <rect
                                x="5"
                                y="9.5"
                                width="14"
                                height="11"
                                rx="2.5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            ></rect>
                            <path
                                d="M12 17.45s-3.1-1.72-3.1-3.86c0-1.04.78-1.82 1.79-1.82.58 0 1.05.28 1.31.69.26-.41.73-.69 1.31-.69 1.01 0 1.79.78 1.79 1.82 0 2.14-3.1 3.86-3.1 3.86Z"
                                fill="currentColor"
                            ></path>
                        </svg>
                        Bloquear Horario
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


<!-- Modal Editar Bloqueo de Horario -->
<div
    class="modal fade"
    id="modalEditarBloqueoHorario"
    tabindex="-1"
    aria-labelledby="modalEditarBloqueoHorarioLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content templo-modal bloqueo-horario-modal">

            <form
                id="formEditarBloqueoHorario"
                action="Index.php?url=agendamiento"
                method="POST"
            >
                <input type="hidden" name="accion" value="modificar_bloqueo">
                <input type="hidden" name="id_bloqueo" id="editarIdBloqueo">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarBloqueoHorarioLabel">
                        <i class="bi bi-pencil-square"></i>
                        Editar Bloqueo de Horario
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

                        <div class="col-md-12">
                            <label for="editarBloqueoUsuarioEspecialista" class="form-label">
                                Especialista
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <select
                                class="form-select templo-input"
                                id="editarBloqueoUsuarioEspecialista"
                                name="id_usuario_especialista"
                                required
                            >
                                <option value="">Seleccionar especialista...</option>

                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= (int) $usuario['id_usuario']; ?>">
                                            <?= htmlspecialchars(
                                                $usuario['nombre']
                                                . ' '
                                                . $usuario['apellido']
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="editarBloqueoFecha" class="form-label">
                                Fecha
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="date"
                                class="form-control templo-input"
                                id="editarBloqueoFecha"
                                name="fecha"
                                required
                            >

                            <div class="invalid-feedback">
                                Seleccione una fecha válida.
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="editarBloqueoHoraInicio" class="form-label">
                                Hora inicio
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="time"
                                class="form-control templo-input"
                                id="editarBloqueoHoraInicio"
                                name="hora_inicio"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label for="editarBloqueoHoraFin" class="form-label">
                                Hora fin
                                <span class="required-mark" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="time"
                                class="form-control templo-input"
                                id="editarBloqueoHoraFin"
                                name="hora_fin"
                                required
                            >

                            <div class="invalid-feedback">
                                La hora fin debe ser posterior a la hora inicio.
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="editarBloqueoMotivo" class="form-label">
                                Motivo
                            </label>

                            <textarea
                                class="form-control templo-input"
                                id="editarBloqueoMotivo"
                                name="motivo"
                                rows="3"
                            ></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-templo-primary">
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


<!-- Modal Detalle del Bloqueo de Horario -->
<div
    class="modal fade"
    id="modalDetalleBloqueoHorario"
    tabindex="-1"
    aria-labelledby="modalDetalleBloqueoHorarioLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content templo-modal bloqueo-horario-modal">

            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleBloqueoHorarioLabel">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        class="templo-lock-heart"
                        aria-hidden="true"
                    >
                        <path
                            d="M7.5 10V7a4.5 4.5 0 0 1 9 0v3"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        ></path>
                        <rect
                            x="5"
                            y="9.5"
                            width="14"
                            height="11"
                            rx="2.5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        ></rect>
                        <path
                            d="M12 17.45s-3.1-1.72-3.1-3.86c0-1.04.78-1.82 1.79-1.82.58 0 1.05.28 1.31.69.26-.41.73-.69 1.31-.69 1.01 0 1.79.78 1.79 1.82 0 2.14-3.1 3.86-3.1 3.86Z"
                            fill="currentColor"
                        ></path>
                    </svg>
                    Detalle del Bloqueo
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>
            </div>

            <div class="modal-body detalle-agendamiento detalle-bloqueo-horario">
                <div class="bloqueo-estado-wrap">
                    <span id="detalleEstadoBloqueo" class="badge estado-bloqueado">
                        BLOQUEADO
                    </span>
                </div>

                <p>
                    <strong>Especialista:</strong>
                    <span id="detalleEspecialistaBloqueo">—</span>
                </p>

                <p>
                    <strong>Fecha:</strong>
                    <span id="detalleFechaBloqueo">—</span>
                </p>

                <p>
                    <strong>Horario:</strong>
                    <span id="detalleHorarioBloqueo">—</span>
                </p>

                <p>
                    <strong>Motivo:</strong>
                    <span id="detalleMotivoBloqueo">—</span>
                </p>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn-templo-secondary"
                    id="btnEditarBloqueoHorario"
                >
                    <i class="bi bi-pencil"></i>
                    Editar
                </button>

                <button
                    type="button"
                    class="btn-templo-desbloquear"
                    id="btnDesbloquearHorario"
                >
                    <i class="bi bi-unlock-fill"></i>
                    Desbloquear
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
                             <span class="required-mark" aria-hidden="true">*</span></label>

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
                             <span class="required-mark" aria-hidden="true">*</span></label>

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
                             <span class="required-mark" aria-hidden="true">*</span></label>

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
                             <span class="required-mark" aria-hidden="true">*</span></label>

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
                             <span class="required-mark" aria-hidden="true">*</span></label>

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
                                class="form-label"
                                id="editarServiciosAgendamientoLabel"
                            >
                                Servicios
                            </label>

                            <div
                                class="agendamiento-servicios-selector"
                                id="editarServiciosAgendamiento"
                                data-servicios-selector="editar"
                            >
                                <button
                                    type="button"
                                    class="form-select templo-input agendamiento-servicios-toggle"
                                    id="editarServiciosToggle"
                                    aria-expanded="false"
                                    aria-controls="editarServiciosPanel"
                                >
                                    <span id="editarServiciosTexto">
                                        Seleccionar servicios...
                                    </span>

                                    <i
                                        class="bi bi-chevron-down"
                                        aria-hidden="true"
                                    ></i>
                                </button>

                                <div
                                    class="agendamiento-servicios-panel"
                                    id="editarServiciosPanel"
                                    hidden
                                >
                                    <div class="agendamiento-servicios-buscador">
                                        <i class="bi bi-search"></i>

                                        <input
                                            type="search"
                                            class="form-control templo-input"
                                            id="editarServiciosBuscar"
                                            placeholder="Buscar servicio..."
                                            autocomplete="off"
                                        >
                                    </div>

                                    <div
                                        class="agendamiento-servicios-lista"
                                        id="editarServiciosLista"
                                    >
                                        <?php if (!empty($servicios)): ?>

                                            <?php foreach ($servicios as $servicio): ?>

                                                <?php

                                                $idServicio = (int) (
                                                    $servicio['id_servicio'] ?? 0
                                                );

                                                $nombreServicio = (string) (
                                                    $servicio['nombre_servicio'] ?? ''
                                                );

                                                $precioServicio = (float) (
                                                    $servicio['precio'] ?? 0
                                                );

                                                $duracionServicio = (int) (
                                                    $servicio['duracion_estimada'] ?? 0
                                                );

                                                ?>

                                                <label
                                                    class="agendamiento-servicio-opcion"
                                                    data-servicio-nombre="<?= htmlspecialchars(
                                                        $nombreServicio,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ); ?>"
                                                    for="editarServicio<?= $idServicio; ?>"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input agendamiento-servicio-check"
                                                        id="editarServicio<?= $idServicio; ?>"
                                                        name="servicios[]"
                                                        value="<?= $idServicio; ?>"
                                                        data-nombre="<?= htmlspecialchars(
                                                            $nombreServicio,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ); ?>"
                                                        data-precio="<?= number_format(
                                                            $precioServicio,
                                                            2,
                                                            '.',
                                                            ''
                                                        ); ?>"
                                                        data-duracion="<?= $duracionServicio; ?>"
                                                    >

                                                    <span class="agendamiento-servicio-contenido">
                                                        <span class="agendamiento-servicio-nombre">
                                                            <?= htmlspecialchars(
                                                                $nombreServicio,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ); ?>
                                                        </span>

                                                        <span class="agendamiento-servicio-duracion">
                                                            · <?= $duracionServicio; ?> min
                                                        </span>
                                                    </span>

                                                    <span class="agendamiento-servicio-precio">
                                                        <?= number_format(
                                                            $precioServicio,
                                                            2,
                                                            ',',
                                                            '.'
                                                        ); ?>
                                                    </span>
                                                </label>

                                            <?php endforeach; ?>

                                        <?php else: ?>

                                            <p class="agendamiento-servicios-sin-resultados">
                                                No hay servicios activos disponibles.
                                            </p>

                                        <?php endif; ?>
                                    </div>

                                    <p
                                        class="agendamiento-servicios-sin-resultados d-none"
                                        id="editarServiciosSinResultados"
                                    >
                                        No se encontraron servicios.
                                    </p>
                                </div>
                            </div>

                            <small class="templo-help-text">
                                Puede seleccionar uno o varios servicios.
                            </small>

                            <div
                                class="agendamiento-servicios-resumen"
                                id="editarServiciosResumen"
                            >
                                <span class="agendamiento-servicios-resumen-vacio">
                                    Ningún servicio seleccionado.
                                </span>
                            </div>

                            <div
                                class="invalid-feedback"
                                id="editarServiciosFeedback"
                            >
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
                                Se calcula según los servicios seleccionados.
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
                    <span
                        class="agendamiento-detalle-icono"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                fill="none"
                            ></path>

                            <path
                                d="M12 16C10 14.8 8.5 13.5 8.5 11.8C8.5 10.5 9.5 9.5 10.8 9.5C11.5 9.5 12 9.9 12 10.4C12 9.9 12.5 9.5 13.2 9.5C14.5 9.5 15.5 10.5 15.5 11.8C15.5 13.5 14 14.8 12 16Z"
                                fill="currentColor"
                            ></path>
                        </svg>
                    </span>

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


<!-- Formulario oculto para desbloquear un horario -->
<form
    id="formDesbloquearHorario"
    action="Index.php?url=agendamiento"
    method="POST"
    class="d-none"
>
    <input type="hidden" name="accion" value="desbloquear_horario">
    <input type="hidden" name="id_bloqueo" id="desbloquearIdBloqueo">
</form>

<script>
    window.agendamientosCalendario = <?= json_encode(
        $agendamientosCalendario ?? [],
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
    ); ?>;
</script>


<?php
$scriptsVista = [
    'Assets/js/agendamiento.js?v=5',
    'Assets/js/agendamiento-calendario.js?v=5'
];

require_once __DIR__ . '/Layout/Footer.php';
?>