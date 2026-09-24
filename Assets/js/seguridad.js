/* ===================================================== */
/* DATATABLES DE SEGURIDAD */
/* ===================================================== */

$(document).ready(function () {

    /* ================================================= */
    /* TABLA DE USUARIOS */
    /* ================================================= */

    const selectorTablaUsuarios = "#tablaUsuarios";

    if (
        $(selectorTablaUsuarios).length &&
        !$.fn.DataTable.isDataTable(selectorTablaUsuarios)
    ) {
        $(selectorTablaUsuarios).DataTable({

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
    /* TABLA DE ROLES */
    /* ================================================= */

    const selectorTablaRoles = "#tablaRoles";

    if (
        $(selectorTablaRoles).length &&
        !$.fn.DataTable.isDataTable(selectorTablaRoles)
    ) {
        $(selectorTablaRoles).DataTable({

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


    /* ================================================= */
    /* AJUSTAR DATATABLES AL CAMBIAR DE PESTAÑA */
    /* ================================================= */

    document
        .querySelectorAll('#seguridadTabs button[data-bs-toggle="tab"]')
        .forEach(function (boton) {

            boton.addEventListener(
                "shown.bs.tab",
                function () {

                    $.fn.dataTable
                        .tables({
                            visible: true,
                            api: true
                        })
                        .columns
                        .adjust();

                }
            );

        });

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
/* BUSCAR PERSONA POR CÉDULA */
/* ===================================================== */

const nuevoUsuarioCedula =
    document.getElementById(
        "nuevoUsuarioCedula"
    );

const nuevoUsuarioIdPersona =
    document.getElementById(
        "nuevoUsuarioIdPersona"
    );

const nuevoUsuarioNombre =
    document.getElementById(
        "nuevoUsuarioNombre"
    );

const nuevoUsuarioApellido =
    document.getElementById(
        "nuevoUsuarioApellido"
    );

const nuevoUsuarioTelefono =
    document.getElementById(
        "nuevoUsuarioTelefono"
    );

const nuevoUsuarioCorreo =
    document.getElementById(
        "nuevoUsuarioCorreo"
    );

const nuevoUsuarioDireccion =
    document.getElementById(
        "nuevoUsuarioDireccion"
    );

const nuevoUsuarioCiudad =
    document.getElementById(
        "nuevoUsuarioCiudad"
    );

let datosPersonaAutocompletados = false;


function limpiarDatosPersonaUsuario() {

    if (nuevoUsuarioIdPersona) {
        nuevoUsuarioIdPersona.value = "";
    }

    if (nuevoUsuarioNombre) {
        nuevoUsuarioNombre.value = "";
    }

    if (nuevoUsuarioApellido) {
        nuevoUsuarioApellido.value = "";
    }

    if (nuevoUsuarioTelefono) {
        nuevoUsuarioTelefono.value = "";
    }

    if (nuevoUsuarioCorreo) {
        nuevoUsuarioCorreo.value = "";
    }

    if (nuevoUsuarioDireccion) {
        nuevoUsuarioDireccion.value = "";
    }

    if (nuevoUsuarioCiudad) {
        nuevoUsuarioCiudad.value = "";
    }


    datosPersonaAutocompletados = false;

}


function llenarDatosPersonaUsuario(persona) {

    if (!persona) {
        return;
    }


    if (nuevoUsuarioIdPersona) {
        nuevoUsuarioIdPersona.value =
            persona.id_persona || "";
    }

    if (nuevoUsuarioNombre) {
        nuevoUsuarioNombre.value =
            persona.nombre || "";
    }

    if (nuevoUsuarioApellido) {
        nuevoUsuarioApellido.value =
            persona.apellido || "";
    }

    if (nuevoUsuarioTelefono) {
        nuevoUsuarioTelefono.value =
            persona.telefono || "";
    }

    if (nuevoUsuarioCorreo) {
        nuevoUsuarioCorreo.value =
            persona.correo || "";
    }

    if (nuevoUsuarioDireccion) {
        nuevoUsuarioDireccion.value =
            persona.direccion || "";
    }

    if (nuevoUsuarioCiudad) {
        nuevoUsuarioCiudad.value =
            persona.ciudad || "";
    }


    datosPersonaAutocompletados = true;

}


function buscarPersonaUsuario() {

    if (!nuevoUsuarioCedula) {
        return;
    }


    const cedula =
        nuevoUsuarioCedula.value.trim();


    nuevoUsuarioCedula.setCustomValidity(
        ""
    );


    if (cedula === "") {

        if (datosPersonaAutocompletados) {
            limpiarDatosPersonaUsuario();
        } else if (nuevoUsuarioIdPersona) {
            nuevoUsuarioIdPersona.value = "";
        }

        return;
    }


    const datos = new FormData();

    datos.append(
        "accion",
        "buscar_persona"
    );

    datos.append(
        "cedula",
        cedula
    );


    fetch(
        "Index.php?url=seguridad",
        {
            method: "POST",
            body: datos
        }
    )
        .then(function (respuesta) {

            if (!respuesta.ok) {
                throw new Error(
                    "No se pudo consultar la persona."
                );
            }

            return respuesta.json();

        })
        .then(function (resultado) {

            /*
             * Si la cédula cambió mientras se hacía
             * la consulta, se ignora la respuesta.
             */
            if (
                nuevoUsuarioCedula.value.trim() !==
                cedula
            ) {
                return;
            }


            if (!resultado.encontrada) {

                if (datosPersonaAutocompletados) {
                    limpiarDatosPersonaUsuario();
                } else if (nuevoUsuarioIdPersona) {
                    nuevoUsuarioIdPersona.value = "";
                }

                return;
            }


            llenarDatosPersonaUsuario(
                resultado.persona
            );


            if (resultado.es_usuario) {

                nuevoUsuarioCedula.setCustomValidity(
                    "Esta persona ya se encuentra registrada como usuario."
                );

                nuevoUsuarioCedula.reportValidity();

            } else {

                nuevoUsuarioCedula.setCustomValidity(
                    ""
                );
            }

        })
        .catch(function (error) {

            console.error(error);

        });

}


if (nuevoUsuarioCedula) {

    nuevoUsuarioCedula.addEventListener(
        "input",
        function () {

            nuevoUsuarioCedula.setCustomValidity(
                ""
            );


            if (datosPersonaAutocompletados) {

                limpiarDatosPersonaUsuario();

            } else if (nuevoUsuarioIdPersona) {

                nuevoUsuarioIdPersona.value = "";
            }

        }
    );


    nuevoUsuarioCedula.addEventListener(
        "blur",
        buscarPersonaUsuario
    );

}


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

        const direccion =
            document.getElementById(
                "editarUsuarioDireccion"
            );

        const ciudad =
            document.getElementById(
                "editarUsuarioCiudad"
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

        if (direccion) {
            direccion.value =
                boton.dataset.direccion || "";
        }

        if (ciudad) {
            ciudad.value =
                boton.dataset.ciudad || "";
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
                    "bi bi-trash-fill";
            }

            if (botonConfirmar) {

                botonConfirmar.className =
                    "btn-templo-danger";

                botonConfirmar.innerHTML =
                    '<i class="bi bi-trash-fill"></i> Sí, desactivar';

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
/* MODAL EDITAR ROL */
/* ===================================================== */

document.addEventListener(
    "click",
    function (event) {

        const boton =
            event.target.closest(
                ".btnEditarRol"
            );


        if (!boton) {
            return;
        }


        const idRol =
            document.getElementById(
                "editarIdRol"
            );

        const nombreRol =
            document.getElementById(
                "editarNombreRol"
            );

        const descripcionRol =
            document.getElementById(
                "editarDescripcionRol"
            );

        const estadoRol =
            document.getElementById(
                "editarEstadoRol"
            );


        if (idRol) {
            idRol.value =
                boton.dataset.idRol || "";
        }

        if (nombreRol) {
            nombreRol.value =
                boton.dataset.nombreRol || "";
        }

        if (descripcionRol) {
            descripcionRol.value =
                boton.dataset.descripcion || "";
        }

        if (estadoRol) {
            estadoRol.value =
                boton.dataset.estadoRol || "";
        }

    }
);


/* ===================================================== */
/* MODAL CAMBIAR ESTADO DEL ROL */
/* ===================================================== */

document.addEventListener(
    "click",
    function (event) {

        const boton =
            event.target.closest(
                ".btnDesactivarRol"
            );


        if (!boton) {
            return;
        }


        const idRol =
            boton.dataset.idRol || "";

        const nombreRol =
            boton.dataset.nombreRol || "";

        const estadoActual =
            (
                boton.dataset.estadoRol ||
                "ACTIVO"
            ).toUpperCase();

        const nuevoEstado =
            estadoActual === "ACTIVO"
                ? "INACTIVO"
                : "ACTIVO";


        const campoId =
            document.getElementById(
                "estadoIdRol"
            );

        const campoNuevoEstado =
            document.getElementById(
                "nuevoEstadoRol"
            );

        const nombre =
            document.getElementById(
                "nombreEstadoRol"
            );


        if (campoId) {
            campoId.value = idRol;
        }

        if (campoNuevoEstado) {
            campoNuevoEstado.value =
                nuevoEstado;
        }

        if (nombre) {
            nombre.textContent = nombreRol;
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

const modalNuevoRol =
    document.getElementById(
        "modalNuevoRol"
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

            if (nuevoUsuarioCedula) {
                nuevoUsuarioCedula.setCustomValidity(
                    ""
                );
            }

            if (nuevoUsuarioIdPersona) {
                nuevoUsuarioIdPersona.value = "";
            }

            datosPersonaAutocompletados = false;

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


if (modalNuevoRol) {

    modalNuevoRol.addEventListener(
        "hidden.bs.modal",
        function () {

            const formulario =
                modalNuevoRol.querySelector("form");

            if (formulario) {
                formulario.reset();
            }

        }
    );

}