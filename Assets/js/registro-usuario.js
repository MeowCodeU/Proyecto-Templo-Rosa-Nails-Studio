/* ===================================================== */
/* MOSTRAR U OCULTAR CONTRASEÑAS DEL REGISTRO */
/* ===================================================== */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const botonesClave =
            document.querySelectorAll(
                ".btnAlternarClaveRegistro"
            );


        botonesClave.forEach(function (boton) {

            boton.addEventListener(
                "click",
                function () {

                    const idCampo =
                        boton.getAttribute(
                            "data-password-target"
                        );

                    const campo =
                        document.getElementById(
                            idCampo
                        );


                    if (!campo) {
                        return;
                    }


                    const claveVisible =
                        campo.type === "text";


                    campo.type = claveVisible
                        ? "password"
                        : "text";


                    const accion = claveVisible
                        ? "Mostrar"
                        : "Ocultar";


                    boton.setAttribute(
                        "aria-label",
                        accion + " contraseña"
                    );

                    boton.setAttribute(
                        "title",
                        accion + " contraseña"
                    );

                }
            );

        });

    }
);


/* ===================================================== */
/* VALIDAR COINCIDENCIA DE CONTRASEÑAS */
/* ===================================================== */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const formulario =
            document.getElementById(
                "formRegistroUsuario"
            );

        const clave =
            document.getElementById(
                "clave"
            );

        const confirmarClave =
            document.getElementById(
                "confirmarClave"
            );


        if (
            !formulario ||
            !clave ||
            !confirmarClave
        ) {
            return;
        }


        function validarCoincidencia() {

            if (
                confirmarClave.value !== "" &&
                clave.value !== confirmarClave.value
            ) {
                confirmarClave.setCustomValidity(
                    "Las contraseñas no coinciden."
                );

            } else {
                confirmarClave.setCustomValidity("");
            }

        }


        clave.addEventListener(
            "input",
            validarCoincidencia
        );

        confirmarClave.addEventListener(
            "input",
            validarCoincidencia
        );

        formulario.addEventListener(
            "submit",
            validarCoincidencia
        );

    }
);