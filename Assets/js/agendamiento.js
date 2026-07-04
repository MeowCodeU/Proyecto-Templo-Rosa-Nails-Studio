document.addEventListener("DOMContentLoaded", function () {
    const formNuevo = document.getElementById(
        "formNuevoAgendamiento"
    );

    const formEditar = document.getElementById(
        "formEditarAgendamiento"
    );

    const tipoNuevo = document.getElementById(
        "nuevoTipoAgendamiento"
    );

    const clienteNuevo = document.getElementById(
        "nuevoCliente"
    );

    const serviciosNuevos = document.getElementById(
        "nuevosServiciosAgendamiento"
    );

    const montoNuevo = document.getElementById(
        "nuevoMontoTotal"
    );

    const duracionNueva = document.getElementById(
        "nuevaDuracionAgendamiento"
    );

    const tipoEditar = document.getElementById(
        "editarTipoAgendamiento"
    );

    const clienteEditar = document.getElementById(
        "editarClienteAgendamiento"
    );

    const serviciosEditar = document.getElementById(
        "editarServiciosAgendamiento"
    );

    const montoEditar = document.getElementById(
        "editarMontoTotal"
    );

    const duracionEditar = document.getElementById(
        "editarDuracionAgendamiento"
    );

    const fechaNueva = document.getElementById(
        "nuevaFechaAgendamiento"
    );

    const fechaEditar = document.getElementById(
        "editarFechaAgendamiento"
    );

    const modalNuevo = document.getElementById(
        "modalNuevoAgendamiento"
    );

    const modalEditar = document.getElementById(
        "modalEditarAgendamiento"
    );

    const modalDetalle = document.getElementById(
        "modalDetalleAgendamiento"
    );

    const btnEditar = document.getElementById(
        "btnEditarAgendamiento"
    );

    function obtenerFechaActual() {
        const hoy = new Date();

        const anio = hoy.getFullYear();

        const mes = String(
            hoy.getMonth() + 1
        ).padStart(2, "0");

        const dia = String(
            hoy.getDate()
        ).padStart(2, "0");

        return `${anio}-${mes}-${dia}`;
    }

    function establecerFechaMinima() {
        const fechaActual = obtenerFechaActual();

        if (fechaNueva) {
            fechaNueva.min = fechaActual;
        }

        if (fechaEditar) {
            fechaEditar.min = fechaActual;
        }
    }

    function obtenerNombreTipo(selectTipo) {
        if (
            !selectTipo ||
            selectTipo.selectedIndex < 0
        ) {
            return "";
        }

        const opcionSeleccionada =
            selectTipo.options[
                selectTipo.selectedIndex
            ];

        return String(
            opcionSeleccionada.dataset.nombre ||
            opcionSeleccionada.textContent ||
            ""
        )
            .trim()
            .toUpperCase();
    }

    function esBloqueoHorario(selectTipo) {
        const nombreTipo =
            obtenerNombreTipo(selectTipo);

        return nombreTipo.includes("BLOQUEO");
    }

    function calcularTotales(
        selectServicios,
        inputMonto,
        inputDuracion
    ) {
        if (!selectServicios) {
            return;
        }

        let montoTotal = 0;
        let duracionTotal = 0;

        Array.from(
            selectServicios.selectedOptions
        ).forEach(function (opcion) {
            const precio = parseFloat(
                opcion.dataset.precio || "0"
            );

            const duracion = parseInt(
                opcion.dataset.duracion || "0",
                10
            );

            if (!Number.isNaN(precio)) {
                montoTotal += precio;
            }

            if (!Number.isNaN(duracion)) {
                duracionTotal += duracion;
            }
        });

        if (inputMonto) {
            inputMonto.value =
                montoTotal.toFixed(2);
        }

        if (inputDuracion) {
            inputDuracion.value =
                duracionTotal > 0
                    ? duracionTotal
                    : "0";
        }
    }

    function configurarTipoAgendamiento(
        selectTipo,
        selectCliente,
        selectServicios,
        inputMonto,
        inputDuracion
    ) {
        if (!selectTipo) {
            return;
        }

        const bloqueo =
            esBloqueoHorario(selectTipo);

        if (selectCliente) {
            selectCliente.disabled = bloqueo;
            selectCliente.required = !bloqueo;

            if (bloqueo) {
                selectCliente.value = "";
                selectCliente.classList.remove(
                    "is-invalid"
                );
            }
        }

        if (selectServicios) {
            selectServicios.disabled = bloqueo;
            selectServicios.required = !bloqueo;

            if (bloqueo) {
                Array.from(
                    selectServicios.options
                ).forEach(function (opcion) {
                    opcion.selected = false;
                });

                selectServicios.classList.remove(
                    "is-invalid"
                );
            }
        }

        if (inputDuracion) {
            inputDuracion.readOnly = !bloqueo;

            if (bloqueo) {
                inputDuracion.value = "";
                inputDuracion.focus();
            }
        }

        if (bloqueo) {
            if (inputMonto) {
                inputMonto.value = "0.00";
            }
        } else {
            calcularTotales(
                selectServicios,
                inputMonto,
                inputDuracion
            );
        }
    }

    function campoEstaVacio(campo) {
        if (!campo || campo.disabled) {
            return false;
        }

        if (
            campo.tagName === "SELECT" &&
            campo.multiple
        ) {
            return (
                campo.selectedOptions.length === 0
            );
        }

        return String(
            campo.value || ""
        ).trim() === "";
    }

    function validarDuracion(inputDuracion) {
        if (!inputDuracion) {
            return true;
        }

        const duracion = parseInt(
            inputDuracion.value,
            10
        );

        if (
            Number.isNaN(duracion) ||
            duracion <= 0
        ) {
            inputDuracion.classList.add(
                "is-invalid"
            );

            return false;
        }

        inputDuracion.classList.remove(
            "is-invalid"
        );

        return true;
    }

    function validarFormulario(
        formulario,
        selectTipo,
        selectCliente,
        selectServicios,
        inputDuracion
    ) {
        if (!formulario) {
            return true;
        }

        let formularioValido = true;

        formulario
            .querySelectorAll("[required]")
            .forEach(function (campo) {
                if (campo.disabled) {
                    campo.classList.remove(
                        "is-invalid"
                    );

                    return;
                }

                if (campoEstaVacio(campo)) {
                    campo.classList.add(
                        "is-invalid"
                    );

                    formularioValido = false;
                } else {
                    campo.classList.remove(
                        "is-invalid"
                    );
                }
            });

        const bloqueo =
            esBloqueoHorario(selectTipo);

        if (!bloqueo) {
            if (
                selectCliente &&
                !selectCliente.value
            ) {
                selectCliente.classList.add(
                    "is-invalid"
                );

                formularioValido = false;
            }

            if (
                selectServicios &&
                selectServicios.selectedOptions
                    .length === 0
            ) {
                selectServicios.classList.add(
                    "is-invalid"
                );

                formularioValido = false;
            }
        }

        if (!validarDuracion(inputDuracion)) {
            formularioValido = false;
        }

        if (!formularioValido) {
            const primerCampoInvalido =
                formulario.querySelector(
                    ".is-invalid"
                );

            if (primerCampoInvalido) {
                primerCampoInvalido.focus();
            }
        }

        return formularioValido;
    }

    function limpiarValidaciones(formulario) {
        if (!formulario) {
            return;
        }

        formulario
            .querySelectorAll(".is-invalid")
            .forEach(function (campo) {
                campo.classList.remove(
                    "is-invalid"
                );
            });
    }

    function limpiarFormularioNuevo() {
        if (!formNuevo) {
            return;
        }

        formNuevo.reset();

        limpiarValidaciones(formNuevo);

        if (montoNuevo) {
            montoNuevo.value = "0.00";
        }

        if (duracionNueva) {
            duracionNueva.value = "0";
            duracionNueva.readOnly = true;
        }

        if (clienteNuevo) {
            clienteNuevo.disabled = false;
            clienteNuevo.required = true;
        }

        if (serviciosNuevos) {
            serviciosNuevos.disabled = false;
            serviciosNuevos.required = true;
        }
    }

    function quitarErrorCampo(campo) {
        if (!campo) {
            return;
        }

        campo.classList.remove("is-invalid");
    }

    if (tipoNuevo) {
        tipoNuevo.addEventListener(
            "change",
            function () {
                quitarErrorCampo(tipoNuevo);

                configurarTipoAgendamiento(
                    tipoNuevo,
                    clienteNuevo,
                    serviciosNuevos,
                    montoNuevo,
                    duracionNueva
                );
            }
        );
    }

    if (tipoEditar) {
        tipoEditar.addEventListener(
            "change",
            function () {
                quitarErrorCampo(tipoEditar);

                configurarTipoAgendamiento(
                    tipoEditar,
                    clienteEditar,
                    serviciosEditar,
                    montoEditar,
                    duracionEditar
                );
            }
        );
    }

    if (serviciosNuevos) {
        serviciosNuevos.addEventListener(
            "change",
            function () {
                quitarErrorCampo(
                    serviciosNuevos
                );

                calcularTotales(
                    serviciosNuevos,
                    montoNuevo,
                    duracionNueva
                );
            }
        );
    }

    if (serviciosEditar) {
        serviciosEditar.addEventListener(
            "change",
            function () {
                quitarErrorCampo(
                    serviciosEditar
                );

                calcularTotales(
                    serviciosEditar,
                    montoEditar,
                    duracionEditar
                );
            }
        );
    }

    if (clienteNuevo) {
        clienteNuevo.addEventListener(
            "change",
            function () {
                quitarErrorCampo(clienteNuevo);
            }
        );
    }

    if (clienteEditar) {
        clienteEditar.addEventListener(
            "change",
            function () {
                quitarErrorCampo(clienteEditar);
            }
        );
    }

    document
        .querySelectorAll(
            ".templo-input, .form-select"
        )
        .forEach(function (campo) {
            campo.addEventListener(
                "input",
                function () {
                    quitarErrorCampo(this);
                }
            );

            campo.addEventListener(
                "change",
                function () {
                    quitarErrorCampo(this);
                }
            );
        });

    if (formNuevo) {
        formNuevo.addEventListener(
            "submit",
            function (evento) {
                const valido =
                    validarFormulario(
                        formNuevo,
                        tipoNuevo,
                        clienteNuevo,
                        serviciosNuevos,
                        duracionNueva
                    );

                if (!valido) {
                    evento.preventDefault();
                }
            }
        );
    }

    if (formEditar) {
        formEditar.addEventListener(
            "submit",
            function (evento) {
                const valido =
                    validarFormulario(
                        formEditar,
                        tipoEditar,
                        clienteEditar,
                        serviciosEditar,
                        duracionEditar
                    );

                if (!valido) {
                    evento.preventDefault();
                }
            }
        );
    }

    if (btnEditar && modalEditar) {
        btnEditar.addEventListener(
            "click",
            function () {
                if (modalDetalle) {
                    const instanciaDetalle =
                        bootstrap.Modal.getInstance(
                            modalDetalle
                        );

                    if (instanciaDetalle) {
                        instanciaDetalle.hide();
                    }
                }

                const instanciaEditar =
                    bootstrap.Modal.getOrCreateInstance(
                        modalEditar
                    );

                instanciaEditar.show();
            }
        );
    }

    if (modalNuevo) {
        modalNuevo.addEventListener(
            "hidden.bs.modal",
            function () {
                limpiarFormularioNuevo();
            }
        );
    }

    if (modalEditar) {
        modalEditar.addEventListener(
            "hidden.bs.modal",
            function () {
                limpiarValidaciones(
                    formEditar
                );
            }
        );
    }

    establecerFechaMinima();

    if (duracionNueva) {
        duracionNueva.readOnly = true;
    }

    if (duracionEditar) {
        duracionEditar.readOnly =
            !esBloqueoHorario(tipoEditar);
    }
});