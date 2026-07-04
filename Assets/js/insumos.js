/* DataTable de Insumos */

$(document).ready(function () {
    const tablaInsumos = $("#tablaInsumos");

    if (
        tablaInsumos.length &&
        !$.fn.DataTable.isDataTable("#tablaInsumos")
    ) {
        tablaInsumos.DataTable({
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json"
            },
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            responsive: false,
            columnDefs: [
                {
                    targets: -1,
                    orderable: false
                }
            ]
        });
    }
});