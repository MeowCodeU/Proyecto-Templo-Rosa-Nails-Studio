/* ===================================================== */
/* DATATABLE DE PROVEEDORES */
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
            orderCellsTop: false,
            columnDefs: [
                {
                    targets: -1,
                    orderable: false
                }
            ]
        });
    }
});
