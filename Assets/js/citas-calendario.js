/* ===================================================== */
/* CITAS-CALENDARIO.JS */
/* Demo estático para Gestión de Agendamiento */
/* Templo Rosa Nails Studio */
/* ===================================================== */

document.addEventListener("DOMContentLoaded", function () {
  const calendarEl = document.getElementById("calendar");
  const listaAgendamientos = document.getElementById("citas-list");
  const btnBuscarCitas = document.getElementById("btnBuscarCitas");
  const searchContainer = document.getElementById("search-container");
  const searchInput = document.getElementById("search-input");
  const searchButton = document.getElementById("search-button");
  const ultimaActualizacion = document.getElementById("ultima-actualizacion");

  const btnGuardarCita = document.getElementById("btnGuardarCita");
  const btnGuardarEdicion = document.getElementById("btnGuardarEdicion");
  const btnEditarCita = document.getElementById("btnEditarCita");
  const btnEliminarCita = document.getElementById("btnEliminarCita");

  const modalAgendarEl = document.getElementById("modalAgendarCita");
  const modalEditarEl = document.getElementById("modalEditarCita");
  const modalDetalleEl = document.getElementById("modalDetalleCita");

  const modalAgendar = modalAgendarEl ? new bootstrap.Modal(modalAgendarEl) : null;
  const modalEditar = modalEditarEl ? new bootstrap.Modal(modalEditarEl) : null;
  const modalDetalle = modalDetalleEl ? new bootstrap.Modal(modalDetalleEl) : null;

  let calendar = null;
  let agendamientoSeleccionadoId = null;

  let agendamientos = [
    {
      id: 1,
      cliente: "Camila Prado",
      telefono: "04129188876",
      ficha: "Camila Prado - Cliente",
      especialista: "Valentina Pérez",
      fecha: obtenerFechaActual(),
      hora: "10:00",
      modalidad: "Cita programada",
      servicio: "Manicura",
      motivo: "Aplicación de polygel",
      estado: "Confirmada"
    },
    {
      id: 2,
      cliente: "María Pérez",
      telefono: "04141234567",
      ficha: "María Pérez - Cliente",
      especialista: "Rosa Martínez",
      fecha: obtenerFechaActual(),
      hora: "14:30",
      modalidad: "Turno inmediato",
      servicio: "Pedicura",
      motivo: "Pedicura semipermanente",
      estado: "En Espera"
    },
    {
      id: 3,
      cliente: "Valentina Gómez",
      telefono: "04241234567",
      ficha: "Valentina Gómez - Cliente",
      especialista: "Andrea López",
      fecha: obtenerFechaManana(),
      hora: "09:30",
      modalidad: "Cita programada",
      servicio: "Manicura y pedicura",
      motivo: "Capping y esmaltado",
      estado: "Programada"
    }
  ];

  /* ===================================================== */
  /* FECHAS DE DEMO */
  /* ===================================================== */

  function obtenerFechaActual() {
    const hoy = new Date();
    return hoy.toISOString().split("T")[0];
  }

  function obtenerFechaManana() {
    const manana = new Date();
    manana.setDate(manana.getDate() + 1);
    return manana.toISOString().split("T")[0];
  }

  function formatearFecha(fechaISO) {
    const [anio, mes, dia] = fechaISO.split("-");
    return `${dia}/${mes}/${anio}`;
  }

  function formatearHora(hora24) {
    if (!hora24) return "";

    const [horas, minutos] = hora24.split(":");
    let h = parseInt(horas, 10);
    const periodo = h >= 12 ? "PM" : "AM";

    h = h % 12;
    h = h ? h : 12;

    return `${h}:${minutos} ${periodo}`;
  }

  function actualizarHoraTexto() {
    if (!ultimaActualizacion) return;

    const ahora = new Date();
    const hora = ahora.toLocaleTimeString("es-VE", {
      hour: "2-digit",
      minute: "2-digit"
    });

    ultimaActualizacion.textContent = `Actualizado: ${hora}`;
  }

  /* ===================================================== */
  /* ESTADOS */
  /* ===================================================== */

  function obtenerClaseEstado(estado) {
    switch (estado) {
      case "Programada":
        return "estado-programada";

      case "Confirmada":
        return "estado-confirmada";

      case "En Espera":
        return "estado-en-espera";

      case "Demorada":
        return "estado-demorada";

      case "Activa":
        return "estado-activa";

      case "Realizada":
        return "estado-realizada";

      case "Cancelada":
        return "estado-cancelada";

      default:
        return "estado-programada";
    }
  }

  function obtenerColorEvento(estado) {
  switch (estado) {
    case "Programada":
      return "#9b6b78";

    case "Confirmada":
      return "#7a5362";

    case "En Espera":
      return "#a86f12";

    case "Activa":
      return "#9b5f69";

    case "Demorada":
      return "#7f5c28";

    case "Realizada":
      return "#4f7f78";

    case "Cancelada":
      return "#9b5d73";

    default:
      return "#9b6b78";
    }
  }

  /* ===================================================== */
  /* FULLCALENDAR */
  /* ===================================================== */

  function convertirAEventosCalendario() {
    return agendamientos.map((item) => {
      return {
        id: String(item.id),
        title: `${item.hora} - ${item.cliente}`,
        start: `${item.fecha}T${item.hora}`,
        backgroundColor: obtenerColorEvento(item.estado),
        borderColor: obtenerColorEvento(item.estado),
        extendedProps: {
          ...item
        }
      };
    });
  }

  function inicializarCalendario() {
  if (!calendarEl) return;

  calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    locale: "es",

    /* Tamaño correcto del calendario */
    height: "auto",
    contentHeight: "auto",
    expandRows: false,

    /* Evita semanas y días sobrantes de otros meses */
    fixedWeekCount: false,
    showNonCurrentDates: false,

    /* Permite arrastrar agendamientos */
    editable: true,
    eventStartEditable: true,
    eventDurationEditable: true,

    events: convertirAEventosCalendario(),

    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay"
    },

    buttonText: {
      today: "Hoy",
      month: "Mes",
      week: "Semana",
      day: "Día"
    },

    eventClick: function (info) {
      const id = parseInt(info.event.id, 10);
      mostrarDetalleAgendamiento(id);
    },

    eventDrop: function (info) {
      const id = parseInt(info.event.id, 10);
      const agendamiento = agendamientos.find((item) => item.id === id);

      if (!agendamiento || !info.event.start) {
        info.revert();
        return;
      }

      const nuevaFecha = info.event.start.toISOString().split("T")[0];

      const nuevaHora = info.event.start.toLocaleTimeString("es-VE", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false
      });

      agendamiento.fecha = nuevaFecha;
      agendamiento.hora = nuevaHora;

      renderizarLista(searchInput ? searchInput.value : "");
      actualizarHoraTexto();

      mostrarAlerta(
        "Agendamiento movido",
        `El agendamiento de ${agendamiento.cliente} fue movido al ${formatearFecha(nuevaFecha)} a las ${formatearHora(nuevaHora)}.`,
        "success"
      );
    },

    eventResize: function (info) {
      mostrarAlerta(
        "Duración ajustada",
        `Se ajustó la duración del agendamiento de ${info.event.title}.`,
        "info"
      );
    }
  });

  calendar.render();
}

  function refrescarCalendario() {
    if (!calendar) return;

    calendar.removeAllEvents();
    calendar.addEventSource(convertirAEventosCalendario());
  }

  /* ===================================================== */
  /* LISTA DE AGENDAMIENTOS */
  /* ===================================================== */

  function obtenerAgendamientosDeHoy() {
    const hoy = obtenerFechaActual();

    return agendamientos.filter((item) => {
      return item.fecha === hoy && item.estado !== "Cancelada";
    });
  }

  function renderizarLista(filtro = "") {
    if (!listaAgendamientos) return;

    const textoFiltro = filtro.trim().toLowerCase();

    let datos = obtenerAgendamientosDeHoy();

    if (textoFiltro !== "") {
      datos = datos.filter((item) => {
        return (
          item.cliente.toLowerCase().includes(textoFiltro) ||
          item.especialista.toLowerCase().includes(textoFiltro) ||
          item.servicio.toLowerCase().includes(textoFiltro) ||
          item.motivo.toLowerCase().includes(textoFiltro) ||
          item.estado.toLowerCase().includes(textoFiltro)
        );
      });
    }

    listaAgendamientos.innerHTML = "";

    if (datos.length === 0) {
      listaAgendamientos.innerHTML = `
        <li class="list-group-item text-center agendamiento-loading-text">
          <i class="bi bi-calendar-x me-2"></i>
          No hay agendamientos para mostrar.
        </li>
      `;
      actualizarHoraTexto();
      return;
    }

    datos.forEach((item) => {
      const li = document.createElement("li");

      li.className = "list-group-item d-flex justify-content-between align-items-center";

      li.innerHTML = `
        <div>
          <h6 class="mb-1">
            ${formatearFecha(item.fecha)} - ${formatearHora(item.hora)} - ${item.cliente}
          </h6>

          <small class="d-block">
            <i class="bi bi-stars me-1"></i>
            ${item.servicio} - ${item.motivo}
          </small>

          <small class="d-block">
            <i class="bi bi-person-heart me-1"></i>
            ${item.especialista} | ${item.telefono}
          </small>
        </div>

        <div class="d-flex align-items-center gap-2">
          <span class="badge ${obtenerClaseEstado(item.estado)}">${item.estado}</span>

          <button class="btn btn-sm btn-outline-secondary" type="button" title="Ver detalle">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      `;

      const botonDetalle = li.querySelector("button");
      botonDetalle.addEventListener("click", function () {
        mostrarDetalleAgendamiento(item.id);
      });

      listaAgendamientos.appendChild(li);
    });

    actualizarHoraTexto();
  }

  /* ===================================================== */
  /* DETALLE */
  /* ===================================================== */

  function mostrarDetalleAgendamiento(id) {
    const item = agendamientos.find((agenda) => agenda.id === id);

    if (!item) return;

    agendamientoSeleccionadoId = id;

    colocarTexto("detalle-paciente", item.cliente);
    colocarTexto("detalle-motivo", `${item.servicio} - ${item.motivo}`);
    colocarTexto("detalle-fecha", `${formatearFecha(item.fecha)} ${formatearHora(item.hora)}`);
    colocarTexto("detalle-veterinario", item.especialista);
    colocarTexto("detalle-dueno", item.cliente);
    colocarTexto("detalle-telefono", item.telefono);
    colocarTexto("detalle-notas", item.motivo);

    const detalleEstado = document.getElementById("detalle-estado");

    if (detalleEstado) {
      detalleEstado.textContent = item.estado;
      detalleEstado.className = `badge ${obtenerClaseEstado(item.estado)}`;
    }

    if (modalDetalle) {
      modalDetalle.show();
    }
  }

  function colocarTexto(id, texto) {
    const elemento = document.getElementById(id);

    if (elemento) {
      elemento.textContent = texto;
    }
  }

  /* ===================================================== */
  /* FORMULARIO NUEVO */
  /* ===================================================== */

  function obtenerTextoSelect(id) {
    const select = document.getElementById(id);

    if (!select || select.selectedIndex < 0) return "";

    return select.options[select.selectedIndex].text;
  }

  function obtenerValor(id) {
    const elemento = document.getElementById(id);

    if (!elemento) return "";

    return elemento.value.trim();
  }

  function validarNuevoAgendamiento() {
    const campos = [
      "cedula",
      "id_mascota",
      "id_usuario",
      "citaFecha",
      "citaHora",
      "id_tipo_atencion",
      "id_tipo_servicio",
      "citaMotivo",
      "estado"
    ];

    let valido = true;

    campos.forEach((id) => {
      const campo = document.getElementById(id);

      if (!campo) return;

      if (!campo.value.trim()) {
        campo.classList.add("is-invalid");
        valido = false;
      } else {
        campo.classList.remove("is-invalid");
      }
    });

    return valido;
  }

  function guardarNuevoAgendamiento() {
    if (!validarNuevoAgendamiento()) {
      mostrarAlerta("Faltan datos", "Completa los campos requeridos para guardar el agendamiento.", "warning");
      return;
    }

    const nuevo = {
      id: generarNuevoId(),
      cliente: limpiarTextoCliente(obtenerTextoSelect("cedula")),
      telefono: "04140000000",
      ficha: obtenerTextoSelect("id_mascota"),
      especialista: obtenerTextoSelect("id_usuario"),
      fecha: obtenerValor("citaFecha"),
      hora: obtenerValor("citaHora"),
      modalidad: obtenerTextoSelect("id_tipo_atencion"),
      servicio: obtenerTextoSelect("id_tipo_servicio"),
      motivo: obtenerValor("citaMotivo"),
      estado: obtenerValor("estado")
    };

    agendamientos.push(nuevo);

    refrescarTodo();

    const form = document.getElementById("formAgendarCita");
    if (form) form.reset();

    if (modalAgendar) {
      modalAgendar.hide();
    }

    mostrarAlerta("Guardado", "El agendamiento fue registrado correctamente.", "success");
  }

  function generarNuevoId() {
    if (agendamientos.length === 0) return 1;

    return Math.max(...agendamientos.map((item) => item.id)) + 1;
  }

  function limpiarTextoCliente(texto) {
    if (!texto) return "";

    if (texto.includes(" - ")) {
      return texto.split(" - ")[1];
    }

    return texto;
  }

  /* ===================================================== */
  /* FORMULARIO EDITAR */
  /* ===================================================== */

  function abrirModalEditar() {
    if (!agendamientoSeleccionadoId) return;

    const item = agendamientos.find((agenda) => agenda.id === agendamientoSeleccionadoId);

    if (!item) return;

    asignarValor("editarIdAtencion", item.id);
    asignarValor("editarFecha", item.fecha);
    asignarValor("editarHora", item.hora);
    asignarValor("editarMotivo", item.motivo);
    asignarValor("editarEstado", item.estado);

    seleccionarPorTexto("editarCedula", item.cliente);
    seleccionarPorTexto("editarIdMascota", item.cliente);
    seleccionarPorTexto("editarIdUsuario", item.especialista);
    seleccionarPorTexto("editarIdTipoAtencion", item.modalidad);
    seleccionarPorTexto("editarIdTipoServicio", item.servicio);

    if (modalDetalle) {
      modalDetalle.hide();
    }

    if (modalEditar) {
      modalEditar.show();
    }
  }

  function asignarValor(id, valor) {
    const elemento = document.getElementById(id);

    if (elemento) {
      elemento.value = valor;
    }
  }

  function seleccionarPorTexto(id, textoBuscado) {
    const select = document.getElementById(id);

    if (!select) return;

    const texto = textoBuscado.toLowerCase();

    Array.from(select.options).forEach((option) => {
      if (option.text.toLowerCase().includes(texto)) {
        select.value = option.value;
      }
    });
  }

  function validarEdicionAgendamiento() {
    const campos = [
      "editarCedula",
      "editarIdMascota",
      "editarIdUsuario",
      "editarFecha",
      "editarHora",
      "editarIdTipoAtencion",
      "editarIdTipoServicio",
      "editarMotivo",
      "editarEstado"
    ];

    let valido = true;

    campos.forEach((id) => {
      const campo = document.getElementById(id);

      if (!campo) return;

      if (!campo.value.trim()) {
        campo.classList.add("is-invalid");
        valido = false;
      } else {
        campo.classList.remove("is-invalid");
      }
    });

    return valido;
  }

  function guardarEdicionAgendamiento() {
    if (!validarEdicionAgendamiento()) {
      mostrarAlerta("Faltan datos", "Completa los campos requeridos para guardar los cambios.", "warning");
      return;
    }

    const id = parseInt(obtenerValor("editarIdAtencion"), 10);

    const index = agendamientos.findIndex((item) => item.id === id);

    if (index === -1) return;

    agendamientos[index] = {
      ...agendamientos[index],
      cliente: limpiarTextoCliente(obtenerTextoSelect("editarCedula")),
      ficha: obtenerTextoSelect("editarIdMascota"),
      especialista: obtenerTextoSelect("editarIdUsuario"),
      fecha: obtenerValor("editarFecha"),
      hora: obtenerValor("editarHora"),
      modalidad: obtenerTextoSelect("editarIdTipoAtencion"),
      servicio: obtenerTextoSelect("editarIdTipoServicio"),
      motivo: obtenerValor("editarMotivo"),
      estado: obtenerValor("editarEstado")
    };

    refrescarTodo();

    if (modalEditar) {
      modalEditar.hide();
    }

    mostrarAlerta("Actualizado", "El agendamiento fue modificado correctamente.", "success");
  }

  /* ===================================================== */
  /* ELIMINAR */
  /* ===================================================== */

  function eliminarAgendamiento() {
    if (!agendamientoSeleccionadoId) return;

    const item = agendamientos.find((agenda) => agenda.id === agendamientoSeleccionadoId);

    if (!item) return;

    Swal.fire({
      title: "¿Eliminar agendamiento?",
      text: `Se eliminará el agendamiento de ${item.cliente}.`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#dc3545",
      cancelButtonColor: "#D8A7B1"
    }).then((result) => {
      if (result.isConfirmed) {
        agendamientos = agendamientos.filter((agenda) => agenda.id !== agendamientoSeleccionadoId);

        agendamientoSeleccionadoId = null;

        refrescarTodo();

        if (modalDetalle) {
          modalDetalle.hide();
        }

        mostrarAlerta("Eliminado", "El agendamiento fue eliminado correctamente.", "success");
      }
    });
  }

  /* ===================================================== */
  /* BUSCADOR */
  /* ===================================================== */

  function alternarBuscador() {
    if (!searchContainer) return;

    searchContainer.classList.toggle("d-none");

    if (!searchContainer.classList.contains("d-none") && searchInput) {
      searchInput.focus();
    }
  }

  function buscarAgendamientos() {
    if (!searchInput) return;

    renderizarLista(searchInput.value);
  }

  /* ===================================================== */
  /* ALERTAS */
  /* ===================================================== */

  function mostrarAlerta(titulo, texto, icono) {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: titulo,
        text: texto,
        icon: icono,
        confirmButtonColor: "#D8A7B1"
      });
    } else {
      alert(`${titulo}: ${texto}`);
    }
  }

  /* ===================================================== */
  /* REFRESCAR TODO */
  /* ===================================================== */

  function refrescarTodo() {
    refrescarCalendario();
    renderizarLista(searchInput ? searchInput.value : "");
    actualizarHoraTexto();
  }

  /* ===================================================== */
  /* EVENTOS */
  /* ===================================================== */

  if (btnBuscarCitas) {
    btnBuscarCitas.addEventListener("click", alternarBuscador);
  }

  if (searchInput) {
    searchInput.addEventListener("keyup", buscarAgendamientos);
  }

  if (searchButton) {
    searchButton.addEventListener("click", buscarAgendamientos);
  }

  if (btnGuardarCita) {
    btnGuardarCita.addEventListener("click", guardarNuevoAgendamiento);
  }

  if (btnEditarCita) {
    btnEditarCita.addEventListener("click", abrirModalEditar);
  }

  if (btnGuardarEdicion) {
    btnGuardarEdicion.addEventListener("click", guardarEdicionAgendamiento);
  }

  if (btnEliminarCita) {
    btnEliminarCita.addEventListener("click", eliminarAgendamiento);
  }

  document.querySelectorAll(".templo-input").forEach((campo) => {
    campo.addEventListener("input", function () {
      this.classList.remove("is-invalid");
    });

    campo.addEventListener("change", function () {
      this.classList.remove("is-invalid");
    });
  });

  /* ===================================================== */
  /* INICIO */
  /* ===================================================== */

  inicializarCalendario();
  renderizarLista();
  actualizarHoraTexto();
});