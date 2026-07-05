/* ===================================================== */
/* DATATABLE DE CLIENTES */
/* ===================================================== */

$(document).ready(function () {

    const tablaClientes =
        $("#tablaClientes");

    if (
        tablaClientes.length &&
        !$.fn.DataTable.isDataTable(
            "#tablaClientes"
        )
    ) {
        tablaClientes.DataTable({
            language: {
                url:
                    "https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json"
            },

            pageLength: 5,

            lengthMenu: [
                5,
                10,
                25,
                50
            ],

            responsive: false
        });
    }

});


/* ===================================================== */
/* MODALES DE CLIENTES */
/* ===================================================== */

document.addEventListener(
    "click",
    function (event) {

        /* Editar cliente */

        const botonEditar =
            event.target.closest(
                ".btnEditarCliente"
            );

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
            event.target.closest(
                ".btnDetalleCliente"
            );

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
                botonDetalle.dataset.visitas ||
                    "0"
            );

            colocarTexto(
                "detalleEstado",
                botonDetalle.dataset.estado ||
                    "ACTIVO"
            );

        }


        /* Historial de servicios */

        const botonHistorial =
            event.target.closest(
                ".btnHistorialCliente"
            );

        if (botonHistorial) {

            const nombreCompleto = [
                botonHistorial.dataset.nombre,
                botonHistorial.dataset.apellido
            ]
                .filter(Boolean)
                .join(" ");

            colocarTexto(
                "historialNombreCliente",
                nombreCompleto
            );

            const modalHistorial =
                document.getElementById(
                    "modalHistorialServiciosCliente"
                );

            if (modalHistorial) {

                modalHistorial.dataset.idCliente =
                    botonHistorial.dataset
                        .idCliente || "";

            }

            mostrarHistorialVacio();

        }


        /* Desactivar cliente */

        const botonDesactivar =
            event.target.closest(
                ".btnDesactivarCliente"
            );

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

    }
);


/* ===================================================== */
/* FUNCIONES AUXILIARES */
/* ===================================================== */

function colocarValor(id, valor) {

    const campo =
        document.getElementById(id);

    if (campo) {

        campo.value =
            valor || "";

    }

}


function colocarTexto(id, valor) {

    const campo =
        document.getElementById(id);

    if (campo) {

        campo.textContent =
            valor || "No registrado";

    }

}


/*
 * Estado temporal del historial.
 *
 * Cuando conectemos el backend, esta función
 * será sustituida por el renderizado de los
 * servicios recibidos desde la base de datos.
 */
function mostrarHistorialVacio() {

    const cuerpoTabla =
        document.getElementById(
            "historialServiciosClienteBody"
        );

    if (!cuerpoTabla) {
        return;
    }

    cuerpoTabla.innerHTML = `
        <tr class="historial-servicios-vacio">
            <td colspan="5">

                <span
                    class="cliente-historial-icono"
                    aria-hidden="true"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <path
                            d="M5.5 3.5H13.2L17 7.3V18.5C17 19.6 16.1 20.5 15 20.5H7.5C6.4 20.5 5.5 19.6 5.5 18.5V3.5Z"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linejoin="round"
                        />

                        <path
                            d="M8 9.5H12.5M8 12.2H12"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                        />

                        <path
                            d="M16.8 12.7C19.45 12.7 21.5 14.8 21.5 17.35C21.5 19.95 19.4 22 16.8 22C14.75 22 13 20.7 12.35 18.85"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                        />

                        <path
                            d="M16.8 14.9V17.45L18.55 18.4"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                        />
                    </svg>
                </span>

                <span>
                    No hay servicios registrados para esta clienta.
                </span>

            </td>
        </tr>
    `;

}