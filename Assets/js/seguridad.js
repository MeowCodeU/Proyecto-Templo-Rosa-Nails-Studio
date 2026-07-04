/* ===================================================== */
/* DATATABLE DE USUARIOS */
/* ===================================================== */

$(document).ready(function () {

    const selectorTabla = "#tablaUsuarios";

    if (
        $(selectorTabla).length &&
        !$.fn.DataTable.isDataTable(selectorTabla)
    ) {
        $(selectorTabla).DataTable({

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

});


/* ===================================================== */
/* VALIDACIÓN DE CONTRASEÑAS */
/* ===================================================== */

function prepararConfirmacionClave(
    formularioId,
    claveId,
    confirmarClaveId
) {

    const formulario =
        document.getElementById(formularioId);

    const clave =
        document.getElementById(claveId);

    const confirmarClave =
        document.getElementById(confirmarClaveId);


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
        function () {

            validarCoincidencia();

        }
    );

}


prepararConfirmacionClave(
    "formNuevoUsuario",
    "nuevoUsuarioClave",
    "nuevoUsuarioConfirmarClave"
);


prepararConfirmacionClave(
    "formRestablecerClave",
    "restablecerNuevaClave",
    "restablecerConfirmarClave"
);


/* ===================================================== */
/* MODAL EDITAR USUARIO */
/* ===================================================== */

document.addEventListener(
    "click",
    function (event) {

        const boton =
            event.target.closest(
                ".btnEditarUsuario"
            );


        if (!boton) {
            return;
        }


        const idUsuario =
            document.getElementById(
                "editarUsuarioId"
            );

        const idPersona =
            document.getElementById(
                "editarUsuarioIdPersona"
            );

        const cedula =
            document.getElementById(
                "editarUsuarioCedula"
            );

        const nombre =
            document.getElementById(
                "editarUsuarioNombre"
            );

        const apellido =
            document.getElementById(
                "editarUsuarioApellido"
            );

        const telefono =
            document.getElementById(
                "editarUsuarioTelefono"
            );

        const correo =
            document.getElementById(
                "editarUsuarioCorreo"
            );

        const rol =
            document.getElementById(
                "editarUsuarioRol"
            );


        if (idUsuario) {
            idUsuario.value =
                boton.dataset.idUsuario || "";
        }

        if (idPersona) {
            idPersona.value =
                boton.dataset.idPersona || "";
        }

        if (cedula) {
            cedula.value =
                boton.dataset.cedula || "";
        }

        if (nombre) {
            nombre.value =
                boton.dataset.nombre || "";
        }

        if (apellido) {
            apellido.value =
                boton.dataset.apellido || "";
        }

        if (telefono) {
            telefono.value =
                boton.dataset.telefono || "";
        }

        if (correo) {
            correo.value =
                boton.dataset.correo || "";
        }

        if (rol) {
            rol.value =
                boton.dataset.idRol || "";
        }

    }
);


/* ===================================================== */
/* MODAL RESTABLECER CONTRASEÑA */
/* ===================================================== */

document.addEventListener(
    "click",
    function (event) {

        const boton =
            event.target.closest(
                ".btnRestablecerClave"
            );


        if (!boton) {
            return;
        }


        const idUsuario =
            document.getElementById(
                "restablecerClaveIdUsuario"
            );

        const nombreUsuario =
            document.getElementById(
                "restablecerClaveUsuario"
            );


        if (idUsuario) {
            idUsuario.value =
                boton.dataset.idUsuario || "";
        }

        if (nombreUsuario) {
            nombreUsuario.value =
                boton.dataset.nombre || "";
        }

    }
);


/* ===================================================== */
/* MODAL ACTIVAR O DESACTIVAR USUARIO */
/* ===================================================== */

document.addEventListener(
    "click",
    function (event) {

        const boton =
            event.target.closest(
                ".btnCambiarEstadoUsuario"
            );


        if (!boton) {
            return;
        }


        const idUsuario =
            boton.dataset.idUsuario || "";

        const nombreUsuario =
            boton.dataset.nombre || "";

        const estadoActual =
            (
                boton.dataset.estado ||
                "ACTIVO"
            ).toUpperCase();

        const nuevoEstado =
            estadoActual === "ACTIVO"
                ? "INACTIVO"
                : "ACTIVO";


        const campoId =
            document.getElementById(
                "estadoUsuarioId"
            );

        const campoNuevoEstado =
            document.getElementById(
                "nuevoEstadoUsuario"
            );

        const nombre =
            document.getElementById(
                "nombreEstadoUsuario"
            );

        const titulo =
            document.getElementById(
                "tituloEstadoUsuario"
            );

        const pregunta =
            document.getElementById(
                "preguntaEstadoUsuario"
            );

        const advertencia =
            document.getElementById(
                "advertenciaEstadoUsuario"
            );

        const icono =
            document.getElementById(
                "iconoEstadoUsuario"
            );

        const botonConfirmar =
            document.getElementById(
                "btnConfirmarEstadoUsuario"
            );


        if (campoId) {
            campoId.value = idUsuario;
        }

        if (campoNuevoEstado) {
            campoNuevoEstado.value =
                nuevoEstado;
        }

        if (nombre) {
            nombre.textContent =
                nombreUsuario;
        }


        if (nuevoEstado === "INACTIVO") {

            if (titulo) {
                titulo.textContent =
                    "Desactivar Usuario";
            }

            if (pregunta) {
                pregunta.textContent =
                    "¿Deseas desactivar este usuario?";
            }

            if (advertencia) {
                advertencia.textContent =
                    "El usuario no podrá iniciar sesión mientras permanezca inactivo.";
            }

            if (icono) {
                icono.className =
                    "bi bi-person-dash";
            }

            if (botonConfirmar) {

                botonConfirmar.className =
                    "btn-templo-danger";

                botonConfirmar.innerHTML =
                    '<i class="bi bi-person-dash"></i> Sí, desactivar';

            }

        } else {

            if (titulo) {
                titulo.textContent =
                    "Activar Usuario";
            }

            if (pregunta) {
                pregunta.textContent =
                    "¿Deseas activar este usuario?";
            }

            if (advertencia) {
                advertencia.textContent =
                    "El usuario podrá volver a iniciar sesión en el sistema.";
            }

            if (icono) {
                icono.className =
                    "bi bi-person-check";
            }

            if (botonConfirmar) {

                botonConfirmar.className =
                    "btn-templo-primary";

                botonConfirmar.innerHTML =
                    '<i class="bi bi-person-check"></i> Sí, activar';

            }

        }

    }
);


/* ===================================================== */
/* LIMPIAR FORMULARIOS AL CERRAR LOS MODALES */
/* ===================================================== */

const modalNuevoUsuario =
    document.getElementById(
        "modalNuevoUsuario"
    );

const modalRestablecerClave =
    document.getElementById(
        "modalRestablecerClave"
    );


if (modalNuevoUsuario) {

    modalNuevoUsuario.addEventListener(
        "hidden.bs.modal",
        function () {

            const formulario =
                document.getElementById(
                    "formNuevoUsuario"
                );

            if (formulario) {
                formulario.reset();
            }

        }
    );

}


if (modalRestablecerClave) {

    modalRestablecerClave.addEventListener(
        "hidden.bs.modal",
        function () {

            const formulario =
                document.getElementById(
                    "formRestablecerClave"
                );

            if (formulario) {
                formulario.reset();
            }

            const usuario =
                document.getElementById(
                    "restablecerClaveUsuario"
                );

            if (usuario) {
                usuario.value = "";
            }

        }
    );

}