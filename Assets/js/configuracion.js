/* ===================================================== */
/* DATATABLES DE CONFIGURACIÓN */
/* ===================================================== */

$(document).ready(function () {
    const tablasConfiguracion = [
        "#tablaConfigServicios",
        "#tablaConfigRoles",
        "#tablaConfigEstadosAgendamiento",
        "#tablaConfigTiposAgendamiento"
    ];

    tablasConfiguracion.forEach(function (selector) {
        const tabla = $(selector);

        if (
            tabla.length &&
            !$.fn.DataTable.isDataTable(selector)
        ) {
            tabla.DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json"
                },
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50],
                responsive: false,
                autoWidth: false,
                columnDefs: [
                    {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        }
    });

    /* Ajusta el ancho de DataTables cuando se abre una pestaña oculta. */
    document.querySelectorAll('#configTabs button[data-bs-toggle="tab"]').forEach(function (boton) {
        boton.addEventListener("shown.bs.tab", function () {
            $.fn.dataTable
                .tables({ visible: true, api: true })
                .columns.adjust();
        });
    });
});
