/* ===================================================== */
/* DATATABLE DE INSUMOS */
/* ===================================================== */

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

            lengthMenu: [
                5,
                10,
                25,
                50
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


    /* ================================================= */
    /* LLENAR MODAL DE DETALLES */
    /* ================================================= */

    $(document).on("click", ".btnDetalleInsumo", function () {

        const boton = $(this);

        const estadoInsumo =
            boton.attr("data-estado-insumo") || "";

        const tipoControl =
            boton.attr("data-tipo-control") || "UNITARIO";

        $("#detalleNombreInsumo").text(
            boton.attr("data-nombre-insumo") ||
            "Sin información"
        );

        $("#detallePresentacionInsumo").text(
            boton.attr("data-presentacion") ||
            "Sin información"
        );

        $("#detalleTipoControl").text(
            tipoControl === "POR_ENVASE"
                ? "Por envase"
                : "Consumo unitario"
        );

        $("#detalleDescripcionInsumo").text(
            boton.attr("data-descripcion") ||
            "Sin descripción"
        );

        $("#detalleStockActual").text(
            boton.attr("data-stock-actual") || "0"
        );

        $("#detalleStockMinimo").text(
            boton.attr("data-stock-minimo") || "0"
        );

        $("#detalleFechaVencimiento").text(
            boton.attr("data-fecha-vencimiento-texto") ||
            "No aplica"
        );

        const badgeEstado = $("#detalleEstadoInsumo");

        badgeEstado
            .removeClass(
                "estado-registro-activo " +
                "estado-registro-inactivo"
            )
            .addClass(
                estadoInsumo === "INACTIVO"
                    ? "estado-registro-inactivo"
                    : "estado-registro-activo"
            )
            .text(
                estadoInsumo || "Sin estado"
            );
    });


    /* ================================================= */
    /* LLENAR MODAL EDITAR INSUMO */
    /* ================================================= */

    $(document).on("click", ".btnEditarInsumo", function () {

        $("#editarIdInsumo").val(
            $(this).attr("data-id-insumo")
        );

        $("#editarNombreInsumo").val(
            $(this).attr("data-nombre-insumo")
        );

        $("#editarDescripcionInsumo").val(
            $(this).attr("data-descripcion")
        );

        $("#editarPresentacionInsumo").val(
            $(this).attr("data-presentacion")
        );

        $("#editarTipoControl").val(
            $(this).attr("data-tipo-control") ||
            "UNITARIO"
        );

        $("#editarStockActual").val(
            $(this).attr("data-stock-actual")
        );

        $("#editarStockMinimo").val(
            $(this).attr("data-stock-minimo")
        );

        $("#editarFechaVencimiento").val(
            $(this).attr("data-fecha-vencimiento")
        );

        $("#editarEstadoInsumo").val(
            $(this).attr("data-estado-insumo")
        );
    });


    /* ================================================= */
    /* LLENAR MODAL DESACTIVAR INSUMO */
    /* ================================================= */

    $(document).on(
        "click",
        ".btnDesactivarInsumo",
        function () {

            const idInsumo =
                $(this).attr("data-id-insumo");

            const nombreInsumo =
                $(this).attr("data-nombre-insumo");

            $("#desactivarIdInsumo").val(idInsumo);

            $("#desactivarNombreInsumo").text(
                nombreInsumo
            );
        }
    );

});