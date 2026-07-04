/* ===================================================== */
/* MOSTRAR U OCULTAR CONTRASEÑA */
/* ===================================================== */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const inputClave =
            document.getElementById("clave");

        const botonMostrarClave =
            document.getElementById(
                "btnMostrarClave"
            );


        if (
            !inputClave ||
            !botonMostrarClave
        ) {
            return;
        }


        const icono =
            botonMostrarClave.querySelector("i");


        botonMostrarClave.addEventListener(
            "click",
            function () {

                const claveVisible =
                    inputClave.type === "text";


                if (claveVisible) {

                    inputClave.type = "password";

                    botonMostrarClave.setAttribute(
                        "aria-label",
                        "Mostrar contraseña"
                    );

                    botonMostrarClave.setAttribute(
                        "title",
                        "Mostrar contraseña"
                    );

                    if (icono) {
                        icono.className =
                            "bi bi-eye-slash";
                    }

                } else {

                    inputClave.type = "text";

                    botonMostrarClave.setAttribute(
                        "aria-label",
                        "Ocultar contraseña"
                    );

                    botonMostrarClave.setAttribute(
                        "title",
                        "Ocultar contraseña"
                    );

                    if (icono) {
                        icono.className =
                            "bi bi-eye";
                    }

                }

            }
        );

    }
);