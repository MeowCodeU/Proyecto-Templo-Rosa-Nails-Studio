document.addEventListener("DOMContentLoaded", function () {
    const titulo = document.getElementById(
        "miniCalendarioTitulo"
    );

    const contenedorDias = document.getElementById(
        "miniCalendarioDias"
    );

    const btnAnterior = document.getElementById(
        "btnMesAnterior"
    );

    const btnSiguiente = document.getElementById(
        "btnMesSiguiente"
    );

    if (
        !titulo ||
        !contenedorDias ||
        !btnAnterior ||
        !btnSiguiente
    ) {
        return;
    }

    const meses = [
        "Enero",
        "Febrero",
        "Marzo",
        "Abril",
        "Mayo",
        "Junio",
        "Julio",
        "Agosto",
        "Septiembre",
        "Octubre",
        "Noviembre",
        "Diciembre"
    ];

    /*
     * Más adelante estos datos serán enviados
     * por el controlador desde la base de datos.
     */
    const citasPorFecha =
        window.citasMiniCalendario &&
        typeof window.citasMiniCalendario === "object"
            ? window.citasMiniCalendario
            : {};

    const hoy = new Date();

    let fechaVisible = new Date(
        hoy.getFullYear(),
        hoy.getMonth(),
        1
    );

    function obtenerClaveFecha(
        anio,
        mes,
        dia
    ) {
        return (
            `${anio}-` +
            `${String(mes + 1).padStart(2, "0")}-` +
            `${String(dia).padStart(2, "0")}`
        );
    }

    function obtenerCantidadCitas(claveFecha) {
        const cantidad = parseInt(
            citasPorFecha[claveFecha] || 0,
            10
        );

        return Number.isNaN(cantidad)
            ? 0
            : cantidad;
    }

    function crearDia(
        numero,
        opciones = {}
    ) {
        const {
            fueraMes = false,
            esHoy = false,
            claveFecha = ""
        } = opciones;

        const celda =
            document.createElement("div");

        celda.className =
            "inicio-calendario-dia";

        if (fueraMes) {
            celda.classList.add(
                "dia-fuera-mes"
            );
        }

        if (esHoy) {
            celda.classList.add(
                "dia-hoy"
            );
        }

        const numeroDia =
            document.createElement("span");

        numeroDia.className =
            "inicio-calendario-numero-dia";

        numeroDia.textContent = numero;

        celda.appendChild(numeroDia);

        if (
            !fueraMes &&
            claveFecha !== ""
        ) {
            const cantidadCitas =
                obtenerCantidadCitas(
                    claveFecha
                );

            if (cantidadCitas > 0) {
                const indicador =
                    document.createElement(
                        "span"
                    );

                indicador.className =
                    "inicio-calendario-cantidad";

                indicador.textContent =
                    cantidadCitas;

                const palabraCita =
                    cantidadCitas === 1
                        ? "cita programada"
                        : "citas programadas";

                celda.title =
                    `${cantidadCitas} ${palabraCita}`;

                celda.classList.add(
                    "dia-con-citas"
                );

                celda.appendChild(
                    indicador
                );
            }
        }

        return celda;
    }

    function renderizarCalendario() {
        const anio =
            fechaVisible.getFullYear();

        const mes =
            fechaVisible.getMonth();

        titulo.textContent =
            `${meses[mes]} ${anio}`;

        contenedorDias.innerHTML = "";

        const primerDiaSemana =
            new Date(
                anio,
                mes,
                1
            ).getDay();

        const diasMesActual =
            new Date(
                anio,
                mes + 1,
                0
            ).getDate();

        const diasMesAnterior =
            new Date(
                anio,
                mes,
                0
            ).getDate();

        for (
            let posicion =
                primerDiaSemana - 1;
            posicion >= 0;
            posicion--
        ) {
            const diaAnterior =
                diasMesAnterior -
                posicion;

            contenedorDias.appendChild(
                crearDia(
                    diaAnterior,
                    {
                        fueraMes: true
                    }
                )
            );
        }

        for (
            let dia = 1;
            dia <= diasMesActual;
            dia++
        ) {
            const claveFecha =
                obtenerClaveFecha(
                    anio,
                    mes,
                    dia
                );

            const esHoy =
                dia === hoy.getDate() &&
                mes === hoy.getMonth() &&
                anio === hoy.getFullYear();

            contenedorDias.appendChild(
                crearDia(
                    dia,
                    {
                        esHoy: esHoy,
                        claveFecha: claveFecha
                    }
                )
            );
        }

        const cantidadActual =
            contenedorDias.children.length;

        const totalCeldas =
            cantidadActual > 35
                ? 42
                : 35;

        let diaSiguiente = 1;

        while (
            contenedorDias.children.length <
            totalCeldas
        ) {
            contenedorDias.appendChild(
                crearDia(
                    diaSiguiente,
                    {
                        fueraMes: true
                    }
                )
            );

            diaSiguiente++;
        }
    }

    btnAnterior.addEventListener(
        "click",
        function () {
            fechaVisible = new Date(
                fechaVisible.getFullYear(),
                fechaVisible.getMonth() - 1,
                1
            );

            renderizarCalendario();
        }
    );

    btnSiguiente.addEventListener(
        "click",
        function () {
            fechaVisible = new Date(
                fechaVisible.getFullYear(),
                fechaVisible.getMonth() + 1,
                1
            );

            renderizarCalendario();
        }
    );

    renderizarCalendario();
});