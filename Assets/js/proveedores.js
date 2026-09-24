/* ===================================================== */
/* DATATABLE Y MODALES DE PROVEEDORES */
/* ===================================================== */

$(document).ready(function () {
    const tablaProveedores = $("#tablaProveedores");

    if (
        tablaProveedores.length &&
        !$.fn.DataTable.isDataTable("#tablaProveedores")
    ) {
        tablaProveedores.DataTable({
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json"
            },
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            responsive: false,
            autoWidth: false,
            orderCellsTop: false,
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    searchable: false
                }
            ]
        });
    }

    /* ================================================= */
    /* LLENAR MODAL DE DETALLES */
    /* ================================================= */

    $(document).on(
        "click",
        ".btnDetalleProveedor",
        function () {
            const boton = $(this);

            $("#detalleRifProveedor").text(
                boton.attr("data-rif") ||
                "Sin información"
            );

            $("#detalleNombreEmpresa").text(
                boton.attr("data-empresa") ||
                "Sin información"
            );

            $("#detalleCedulaContacto").text(
                boton.attr("data-cedula") ||
                "Sin información"
            );

            $("#detalleNombreContacto").text(
                boton.attr("data-nombre-completo") ||
                "Sin información"
            );

            $("#detalleTelefonoContacto").text(
                boton.attr("data-telefono") ||
                "Sin información"
            );

            $("#detalleCorreoContacto").text(
                boton.attr("data-correo") ||
                "Sin información"
            );

            $("#detalleCiudadContacto").text(
                boton.attr("data-ciudad") ||
                "Sin información"
            );

            $("#detalleDireccionContacto").text(
                boton.attr("data-direccion") ||
                "Sin información"
            );

            const estadoProveedor =
                boton.attr("data-estado-proveedor") ||
                "ACTIVO";

            $("#detalleEstadoProveedor")
                .removeClass(
                    "estado-registro-activo " +
                    "estado-registro-inactivo"
                )
                .addClass(
                    estadoProveedor === "INACTIVO"
                        ? "estado-registro-inactivo"
                        : "estado-registro-activo"
                )
                .text(estadoProveedor);
        }
    );


    /* ================================================= */
    /* LLENAR MODAL EDITAR PROVEEDOR */
    /* ================================================= */

    $(document).on(
        "click",
        ".btnEditarProveedor",
        function () {
            const boton = $(this);

            $("#editarIdProveedor").val(
                boton.attr("data-id-proveedor")
            );

            $("#editarIdPersonaContacto").val(
                boton.attr(
                    "data-id-persona-contacto"
                )
            );

            $("#editarRifProveedor").val(
                boton.attr("data-rif")
            );

            $("#editarNombreEmpresa").val(
                boton.attr("data-empresa")
            );

            $("#editarCedulaContacto").val(
                boton.attr("data-cedula")
            );

            $("#editarNombreContacto").val(
                boton.attr("data-nombre")
            );

            $("#editarApellidoContacto").val(
                boton.attr("data-apellido")
            );

            $("#editarTelefonoContacto").val(
                boton.attr("data-telefono")
            );

            $("#editarCorreoContacto").val(
                boton.attr("data-correo")
            );

            $("#editarCiudadContacto").val(
                boton.attr("data-ciudad")
            );

            $("#editarDireccionContacto").val(
                boton.attr("data-direccion")
            );
        }
    );


    /* ================================================= */
    /* LLENAR MODAL DESACTIVAR PROVEEDOR */
    /* ================================================= */

    $(document).on(
        "click",
        ".btnDesactivarProveedor",
        function () {
            $("#desactivarIdProveedor").val(
                $(this).attr("data-id-proveedor")
            );

            $("#desactivarNombreProveedor").text(
                $(this).attr("data-empresa") || ""
            );
        }
    );


    /* ================================================= */
    /* LIMPIAR MODAL DE REGISTRO */
    /* ================================================= */

    $("#modalNuevoProveedor").on(
        "hidden.bs.modal",
        function () {
            const formulario =
                $(this).find("form")[0];

            if (formulario) {
                formulario.reset();
            }
        }
    );
});