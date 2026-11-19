/* ===================================================== */
/* BÚSQUEDA DE GUÍAS DEL MANUAL */
/* ===================================================== */

$(function () {
    "use strict";

    const buscador = $("#buscarManual");
    const tarjetas = $("[data-guia-manual]");

    if (!buscador.length || !tarjetas.length) {
        return;
    }


    /* Comparar palabras sin distinguir acentos ni mayúsculas */
    function normalizar(texto) {
        return String(texto)
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .trim();
    }


    /* Filtrar las guías disponibles y actualizar el resultado */
    function filtrarGuias() {
        const consulta = normalizar(buscador.val());
        let cantidad = 0;

        tarjetas.each(function () {
            const tarjeta = $(this);

            const coincide = normalizar(
                tarjeta.text()
            ).includes(consulta);

            tarjeta.prop("hidden", !coincide);

            if (coincide) {
                cantidad++;
            }
        });

        $("#sinResultadosManual").prop(
            "hidden",
            cantidad > 0
        );

        $("#resultadoBusquedaManual").text(
            cantidad === 1
                ? "1 guía disponible."
                : cantidad + " guías disponibles."
        );
    }


    /* Actualizar los resultados mientras se escribe */
    buscador.on("input search", filtrarGuias);


    /* Recuperar el listado completo y volver al buscador */
    $("#limpiarBusquedaManual").on("click", function () {
        buscador.val("");
        filtrarGuias();
        buscador.trigger("focus");
    });


    /* Habilitar la búsqueda cuando su comportamiento está disponible */
    $("#busquedaManual").prop("hidden", false);

    filtrarGuias();
});