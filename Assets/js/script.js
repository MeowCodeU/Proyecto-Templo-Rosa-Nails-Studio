/* ===================================================== */
/* EFECTO DOCK / NAVBAR */
/* ===================================================== */

const dockMenu = document.getElementById("dockMenu");

function clamp(value, min, max) {
  return Math.max(min, Math.min(max, value));
}

if (dockMenu) {
  const dockItems = Array.from(dockMenu.querySelectorAll(".dock-item"));

  function applyDockEffect(mouseX) {
    dockItems.forEach((item) => {
      const rect = item.getBoundingClientRect();
      const itemCenter = rect.left + rect.width / 2;
      const distance = Math.abs(mouseX - itemCenter);

      const maxDistance = 170;
      const influence = clamp(1 - distance / maxDistance, 0, 1);

      const scale = 1 + influence * 0.34;
      const lift = influence * 12;
      const labelLift = influence * 2;

      const baseY = getComputedStyle(item).getPropertyValue("--base-y").trim() || "0px";

      item.style.transform = `translateY(calc(${baseY} - ${lift}px))`;

      const icon = item.querySelector(".icon");
      const label = item.querySelector(".label");

      if (icon) {
        icon.style.transform = `scale(${scale})`;
      }

      if (label) {
        label.style.transform = `translateY(${-labelLift}px) scale(${1 + influence * 0.04})`;
        label.style.opacity = `${0.86 + influence * 0.14}`;
      }
    });
  }

  function resetDockEffect() {
    dockItems.forEach((item) => {
      const baseY = getComputedStyle(item).getPropertyValue("--base-y").trim() || "0px";

      item.style.transform = `translateY(${baseY})`;

      const icon = item.querySelector(".icon");
      const label = item.querySelector(".label");

      if (icon) {
        icon.style.transform = "scale(1)";
      }

      if (label) {
        label.style.transform = "translateY(0) scale(1)";
        label.style.opacity = "1";
      }
    });
  }

  dockMenu.addEventListener("mousemove", (event) => {
    applyDockEffect(event.clientX);
  });

  dockMenu.addEventListener("mouseleave", () => {
    resetDockEffect();
  });
}


/* ===================================================== */
/* DATATABLES GLOBALES */
/* ===================================================== */

$(document).ready(function () {

  const dataTableConfigBase = {
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json"
    },
    pageLength: 5,
    lengthMenu: [5, 10, 25, 50],
    responsive: false
  };

  if ($("#tablaClientes").length && !$.fn.DataTable.isDataTable("#tablaClientes")) {
    $("#tablaClientes").DataTable(dataTableConfigBase);
  }

  if ($("#tablaConfigServicios").length && !$.fn.DataTable.isDataTable("#tablaConfigServicios")) {
    $("#tablaConfigServicios").DataTable(dataTableConfigBase);
  }

  if ($("#tablaConfigRoles").length && !$.fn.DataTable.isDataTable("#tablaConfigRoles")) {
    $("#tablaConfigRoles").DataTable(dataTableConfigBase);
  }

  if ($("#tablaConfigTiposAgendamiento").length && !$.fn.DataTable.isDataTable("#tablaConfigTiposAgendamiento")) {
    $("#tablaConfigTiposAgendamiento").DataTable(dataTableConfigBase);
  }

});


/* ===================================================== */
/* PREVIEW DE FOTO DE PERFIL */
/* ===================================================== */

const inputFotoPerfil = document.getElementById("inputFotoPerfil");
const previewFoto = document.getElementById("previewFoto");

if (inputFotoPerfil && previewFoto) {
  previewFoto.addEventListener("click", () => {
    inputFotoPerfil.click();
  });

  inputFotoPerfil.addEventListener("change", function () {
    const archivo = this.files[0];

    if (archivo) {
      const lector = new FileReader();

      lector.onload = function (e) {
        previewFoto.src = e.target.result;

        const fotoHeader = document.querySelector(".user-session-photo");

        if (fotoHeader) {
          fotoHeader.src = e.target.result;
        }
      };

      lector.readAsDataURL(archivo);
    }
  });
}

/* ===================================================== */
/* MODALES CLIENTES */
/* ===================================================== */

document.addEventListener("click", function (event) {

  /* EDITAR CLIENTE */
  const botonEditar = event.target.closest(".btnEditarCliente");

  if (botonEditar) {
    colocarValor("editarCedula", botonEditar.dataset.cedula);
    colocarValor("editarNombre", botonEditar.dataset.nombre);
    colocarValor("editarApellido", botonEditar.dataset.apellido);
    colocarValor("editarTelefono", botonEditar.dataset.telefono);
    colocarValor("editarCorreo", botonEditar.dataset.correo);
    colocarValor("editarDireccion", botonEditar.dataset.direccion);
    colocarValor("editarCiudad", botonEditar.dataset.ciudad);
    colocarValor("editarAlergias", botonEditar.dataset.alergias);
  }

  /* DETALLE CLIENTE */
  const botonDetalle = event.target.closest(".btnDetalleCliente");

  if (botonDetalle) {
    colocarTexto("detalleCedula", botonDetalle.dataset.cedula);
    colocarTexto("detalleNombre", botonDetalle.dataset.nombre);
    colocarTexto("detalleApellido", botonDetalle.dataset.apellido);
    colocarTexto("detalleTelefono", botonDetalle.dataset.telefono);
    colocarTexto("detalleCorreo", botonDetalle.dataset.correo);
    colocarTexto("detalleDireccion", botonDetalle.dataset.direccion);
    colocarTexto("detalleCiudad", botonDetalle.dataset.ciudad);
    colocarTexto("detalleAlergias", botonDetalle.dataset.alergias);
    colocarTexto("detalleVisitas", botonDetalle.dataset.visitas || "0");
    colocarTexto("detalleEstado", botonDetalle.dataset.estado || "Activo");
  }

  /* DESACTIVAR CLIENTE */
  const botonDesactivar = event.target.closest(".btnDesactivarCliente");

  if (botonDesactivar) {
    colocarValor("desactivarCedula", botonDesactivar.dataset.cedula);
    colocarTexto("desactivarNombreCliente", botonDesactivar.dataset.nombre);
  }

});


/* Coloca valores en inputs */
function colocarValor(id, valor) {
  const campo = document.getElementById(id);

  if (campo) {
    campo.value = valor || "";
  }
}


/* Coloca texto en elementos del modal */
function colocarTexto(id, valor) {
  const campo = document.getElementById(id);

  if (campo) {
    campo.textContent = valor || "No registrado";
  }
}