/* ===================================================== */
/* DATATABLES DE CONFIGURACIÓN */
/* ===================================================== */

$(document).ready(function () {

    const tablasConfiguracion = [
        "#tablaConfigServicios",
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

                pageLength: 4,

                lengthMenu: [
                    4,
                    8,
                    16,
                    20
                ],

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


    /* Ajustar las tablas cuando se abre una pestaña */
    document
        .querySelectorAll('#configTabs button[data-bs-toggle="tab"]')
        .forEach(function (boton) {

            boton.addEventListener("shown.bs.tab", function () {

                $.fn.dataTable
                    .tables({
                        visible: true,
                        api: true
                    })
                    .columns
                    .adjust();
            });
        });


    /* ================================================= */
    /* SERVICIOS */
    /* ================================================= */

    /* Llenar modal Editar Servicio */
    $(document).on("click", ".btnEditarServicio", function () {

        $("#editarIdServicio").val(
            $(this).attr("data-id-servicio")
        );

        $("#editarNombreServicio").val(
            $(this).attr("data-nombre-servicio")
        );

        $("#editarDescripcionServicio").val(
            $(this).attr("data-descripcion")
        );

        $("#editarPrecioServicio").val(
            $(this).attr("data-precio")
        );

        $("#editarDuracionServicio").val(
            $(this).attr("data-duracion-estimada")
        );

        $("#editarEstadoServicio").val(
            $(this).attr("data-estado-servicio")
        );
    });


    /* Llenar modal Desactivar Servicio */
    $(document).on("click", ".btnDesactivarServicio", function () {

        const idServicio = $(this).attr("data-id-servicio");
        const nombreServicio = $(this).attr("data-nombre-servicio");

        $("#estadoIdServicio").val(idServicio);

        $("#nuevoEstadoServicio").val("INACTIVO");

        $("#nombreEstadoServicio").text(nombreServicio);
    });


    /* ================================================= */
    /* ESTADOS DE AGENDAMIENTO */
    /* ================================================= */

    /* Llenar modal Editar Estado de Agendamiento */
    $(document).on(
        "click",
        ".btnEditarEstadoAgendamiento",
        function () {

            $("#editarIdEstadoAgendamiento").val(
                $(this).attr("data-id-estado-agendamiento")
            );

            $("#editarNombreEstadoAgendamiento").val(
                $(this).attr("data-nombre-estado")
            );

            $("#editarDescripcionEstadoAgendamiento").val(
                $(this).attr("data-descripcion")
            );

            $("#editarRegistroEstadoAgendamiento").val(
                $(this).attr("data-estado-registro")
            );
        }
    );


    /* Llenar modal Desactivar Estado de Agendamiento */
    $(document).on(
        "click",
        ".btnDesactivarEstadoAgendamiento",
        function () {

            const idEstadoAgendamiento = $(this).attr(
                "data-id-estado-agendamiento"
            );

            const nombreEstado = $(this).attr(
                "data-nombre-estado"
            );

            $("#estadoIdEstadoAgendamiento").val(
                idEstadoAgendamiento
            );

            $("#nuevoRegistroEstadoAgendamiento").val(
                "INACTIVO"
            );

            $("#nombreEstadoEstadoAgendamiento").text(
                nombreEstado
            );
        }
    );


    /* ================================================= */
    /* TIPOS DE AGENDAMIENTO */
    /* ================================================= */

    /* Llenar modal Editar Tipo de Agendamiento */
    $(document).on(
        "click",
        ".btnEditarTipoAgendamiento",
        function () {

            $("#editarIdTipoAgendamiento").val(
                $(this).attr("data-id-tipo-agendamiento")
            );

            $("#editarNombreTipoAgendamiento").val(
                $(this).attr("data-nombre-tipo-agendamiento")
            );

            $("#editarDescripcionTipoAgendamiento").val(
                $(this).attr("data-descripcion")
            );

            $("#editarEstadoTipoAgendamiento").val(
                $(this).attr("data-estado-tipo-agendamiento")
            );
        }
    );


    /* Llenar modal Desactivar Tipo de Agendamiento */
    $(document).on(
        "click",
        ".btnDesactivarTipoAgendamiento",
        function () {

            const idTipoAgendamiento = $(this).attr(
                "data-id-tipo-agendamiento"
            );

            const nombreTipoAgendamiento = $(this).attr(
                "data-nombre-tipo-agendamiento"
            );

            $("#estadoIdTipoAgendamiento").val(
                idTipoAgendamiento
            );

            $("#nuevoEstadoTipoAgendamiento").val(
                "INACTIVO"
            );

            $("#nombreEstadoTipoAgendamiento").text(
                nombreTipoAgendamiento
            );
        }
    );

});