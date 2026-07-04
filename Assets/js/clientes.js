/* ===================================================== */
/* DATATABLE DE CLIENTES */
/* ===================================================== */

$(document).ready(function () {
    const tablaClientes = $("#tablaClientes");

    if (
        tablaClientes.length &&
        !$.fn.DataTable.isDataTable("#tablaClientes")
    ) {
        tablaClientes.DataTable({
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json"
            },
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            responsive: false
        });
    }
});


/* ===================================================== */
/* MODALES DE CLIENTES */
/* ===================================================== */

document.addEventListener("click", function (event) {

    /* Editar cliente */
    const botonEditar =
        event.target.closest(".btnEditarCliente");

    if (botonEditar) {
        colocarValor(
            "editarCedula",
            botonEditar.dataset.cedula
        );

        colocarValor(
            "editarNombre",
            botonEditar.dataset.nombre
        );

        colocarValor(
            "editarApellido",
            botonEditar.dataset.apellido
        );

        colocarValor(
            "editarTelefono",
            botonEditar.dataset.telefono
        );

        colocarValor(
            "editarCorreo",
            botonEditar.dataset.correo
        );

        colocarValor(
            "editarDireccion",
            botonEditar.dataset.direccion
        );

        colocarValor(
            "editarCiudad",
            botonEditar.dataset.ciudad
        );

        colocarValor(
            "editarAlergias",
            botonEditar.dataset.alergias
        );
    }


    /* Detalles del cliente */
    const botonDetalle =
        event.target.closest(".btnDetalleCliente");

    if (botonDetalle) {
        colocarTexto(
            "detalleCedula",
            botonDetalle.dataset.cedula
        );

        colocarTexto(
            "detalleNombre",
            botonDetalle.dataset.nombre
        );

        colocarTexto(
            "detalleApellido",
            botonDetalle.dataset.apellido
        );

        colocarTexto(
            "detalleTelefono",
            botonDetalle.dataset.telefono
        );

        colocarTexto(
            "detalleCorreo",
            botonDetalle.dataset.correo
        );

        colocarTexto(
            "detalleDireccion",
            botonDetalle.dataset.direccion
        );

        colocarTexto(
            "detalleCiudad",
            botonDetalle.dataset.ciudad
        );

        colocarTexto(
            "detalleAlergias",
            botonDetalle.dataset.alergias
        );

        colocarTexto(
            "detalleVisitas",
            botonDetalle.dataset.visitas || "0"
        );

        colocarTexto(
            "detalleEstado",
            botonDetalle.dataset.estado || "ACTIVO"
        );
    }


    /* Desactivar cliente */
    const botonDesactivar =
        event.target.closest(".btnDesactivarCliente");

    if (botonDesactivar) {
        colocarValor(
            "desactivarCedula",
            botonDesactivar.dataset.cedula
        );

        colocarTexto(
            "desactivarNombreCliente",
            botonDesactivar.dataset.nombre
        );
    }
});


/* ===================================================== */
/* FUNCIONES AUXILIARES */
/* ===================================================== */

function colocarValor(id, valor) {
    const campo = document.getElementById(id);

    if (campo) {
        campo.value = valor || "";
    }
}


function colocarTexto(id, valor) {
    const campo = document.getElementById(id);

    if (campo) {
        campo.textContent =
            valor || "No registrado";
    }
}
