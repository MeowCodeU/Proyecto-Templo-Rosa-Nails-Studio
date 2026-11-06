/* ===================================================== */
/* GESTIÓN DE SEGURIDAD */
/* ===================================================== */

$(function () {
    "use strict";

    const modulo = $(".seguridad-module");
    const urlSeguridad = "Index.php?url=seguridad";

    if (!modulo.length) return;

    const modales = $(
        "#modalNuevoUsuario, #modalEditarUsuario, #modalEstadoUsuario, " +
        "#modalNuevoRol, #modalEditarRol, #modalEstadoRol, #modalRestablecerClave"
    );

    let operacionEnCurso = false;
    let versionLista = 0;
    let versionPersona = 0;
    let datosAutocompletados = false;
    let usuariosPorId = new Map();

    const mensajeGeneral = $("<div>", {
        class: "alert alert-light border mb-3",
        role: "status",
        "aria-live": "polite",
        hidden: true
    }).insertAfter(modulo.find(".module-header").first());


    /* ================================================= */
    /* TABLAS DE USUARIOS Y ROLES */
    /* ================================================= */

    function prepararTabla(selector, cantidad, opciones) {
        let habilitarTabla;

        const preparada = new Promise(resolve => {
            habilitarTabla = resolve;
        });

        const existente = $.fn.DataTable.isDataTable(selector);

        const instancia = existente
            ? $(selector).DataTable()
            : $(selector).DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json"
                },
                pageLength: cantidad,
                lengthMenu: opciones,
                responsive: false,
                autoWidth: false,
                columnDefs: [
                    { targets: -1, orderable: false, searchable: false }
                ],
                initComplete: habilitarTabla
            });

        if (existente) habilitarTabla();

        return { instancia, preparada };
    }

    const tablaUsuarios = prepararTabla("#tablaUsuarios", 5, [5, 10, 25, 50]);
    const tablaRoles = prepararTabla("#tablaRoles", 4, [4, 8, 16, 20]);

    $("#seguridadTabs").on("shown.bs.tab", 'button[data-bs-toggle="tab"]', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });


    /* ================================================= */
    /* SOLICITUDES Y TOKEN DE RESTABLECIMIENTO */
    /* ================================================= */

    function actualizarToken(respuesta) {
        const token = respuesta?.token_restablecer_clave;

        if (typeof token === "string" && token !== "") {
            $("#formRestablecerClave [name='token_csrf']")
                .val(token)
                .prop("defaultValue", token);
        }
    }

    function solicitarSeguridad(datos) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: urlSeguridad,
                method: "POST",
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                headers: { "X-Requested-With": "XMLHttpRequest" },
                data: JSON.stringify(datos),
                processData: false,
                timeout: 30000,

                success: function (respuesta) {
                    if (datos.accion === "restablecer_clave") {
                        actualizarToken(respuesta);
                    }

                    if (respuesta && respuesta.ok === true) {
                        resolve(respuesta);
                        return;
                    }

                    reject(new Error(
                        typeof respuesta?.mensaje === "string"
                            ? respuesta.mensaje
                            : "No se pudo confirmar la operación. Actualice la lista antes de repetirla."
                    ));
                },

                error: function (xhr, estado) {
                    if (datos.accion === "restablecer_clave") {
                        actualizarToken(xhr.responseJSON);
                    }

                    if (xhr.status === 401) {
                        window.location.assign("Index.php?url=login&sesion=expirada");
                    }

                    const ambiguo = xhr.status === 0 || estado === "timeout" || estado === "parsererror";
                    const mensaje = ambiguo
                        ? "No se pudo confirmar la respuesta. Actualice la lista antes de repetir la operación."
                        : "No fue posible completar la solicitud. Intente nuevamente.";

                    reject(new Error(
                        typeof xhr.responseJSON?.mensaje === "string"
                            ? xhr.responseJSON.mensaje
                            : mensaje
                    ));
                }
            });
        });
    }


    /* ================================================= */
    /* MENSAJES DEL MÓDULO Y DE LOS FORMULARIOS */
    /* ================================================= */

    function mostrarMensaje(texto, esError = false, reintentar = false) {
        mensajeGeneral
            .empty()
            .text(texto)
            .prop("hidden", false)
            .toggleClass("alert-danger", esError)
            .toggleClass("alert-light", !esError)
            .attr("role", esError ? "alert" : "status");

        if (reintentar) {
            $("<button>", {
                type: "button",
                class: "btn-templo-secondary ms-2",
                text: "Actualizar listas"
            }).on("click", async function () {
                if (operacionEnCurso) return;

                const boton = $(this).prop("disabled", true);

                try {
                    if (await actualizarListas()) {
                        mostrarMensaje("Listas actualizadas correctamente.");
                    }
                } catch (error) {
                    mostrarMensaje(error.message, true, true);
                } finally {
                    boton.prop("disabled", false);
                }
            }).appendTo(mensajeGeneral);
        }
    }

    function mensajeFormulario(formulario) {
        let mensaje = formulario.find(".seguridad-mensaje-formulario");

        if (!mensaje.length) {
            mensaje = $("<div>", {
                class: "alert alert-danger seguridad-mensaje-formulario",
                role: "alert",
                hidden: true
            }).prependTo(formulario.find(".modal-body").first());
        }

        return mensaje;
    }


    /* ================================================= */
    /* CONTRASEÑAS Y VISIBILIDAD DE LOS CAMPOS */
    /* ================================================= */

    function validarCoincidencia(formulario) {
        const clave = formulario.find('[name="clave"], [name="nueva_clave"]').first();
        const confirmar = formulario.find('[name="confirmar_clave"]').first();

        if (clave.length && confirmar.length) {
            confirmar[0].setCustomValidity(
                confirmar.val() !== "" && clave.val() !== confirmar.val()
                    ? "Las contraseñas no coinciden."
                    : ""
            );
        }
    }

    $("#formNuevoUsuario, #formRestablecerClave").on("input", "input", function () {
        validarCoincidencia($(this).closest("form"));
    });

    function establecerVisibilidad(boton, visible) {
        const idCampo = boton.attr("data-password-target");
        const campo = idCampo ? $("#" + $.escapeSelector(idCampo)) : $();

        if (!campo.length) return;

        campo.prop("type", visible ? "text" : "password");

        boton.find("i").attr("class", visible ? "bi bi-eye" : "bi bi-eye-slash");
        boton.find(".templo-eye-open").toggle(visible);
        boton.find(".templo-eye-closed").toggle(!visible);

        boton.attr({
            "aria-label": (visible ? "Ocultar" : "Mostrar") + " contraseña",
            title: (visible ? "Ocultar" : "Mostrar") + " contraseña"
        });
    }

    $(document).on("click", ".btnAlternarClave", function () {
        const boton = $(this);
        const idCampo = boton.attr("data-password-target");
        const campo = idCampo ? $("#" + $.escapeSelector(idCampo)) : $();

        if (campo.length) establecerVisibilidad(boton, campo.prop("type") !== "text");
    });


    /* ================================================= */
    /* BÚSQUEDA DE PERSONAS POR CÉDULA */
    /* ================================================= */

    const camposPersona = {
        id_persona: "#nuevoUsuarioIdPersona",
        nombre: "#nuevoUsuarioNombre",
        apellido: "#nuevoUsuarioApellido",
        telefono: "#nuevoUsuarioTelefono",
        correo: "#nuevoUsuarioCorreo",
        direccion: "#nuevoUsuarioDireccion",
        ciudad: "#nuevoUsuarioCiudad"
    };

    function limpiarPersona() {
        Object.values(camposPersona).forEach(selector => $(selector).val(""));
        datosAutocompletados = false;
    }

    async function buscarPersonaUsuario(completar = true) {
        const campoCedula = $("#nuevoUsuarioCedula");

        if (!campoCedula.length) return false;

        const cedula = String(campoCedula.val() || "").trim();
        const turno = ++versionPersona;

        campoCedula[0].setCustomValidity("");

        if (!cedula) {
            if (datosAutocompletados) limpiarPersona();

            $("#nuevoUsuarioIdPersona").val("");
            return false;
        }

        const resultado = await solicitarSeguridad({
            accion: "buscar_persona",
            cedula
        });

        if (
            turno !== versionPersona ||
            String(campoCedula.val() || "").trim() !== cedula
        ) {
            return false;
        }

        if (resultado.encontrada) {
            if (!$.isPlainObject(resultado.persona)) {
                throw new Error("Los datos de la persona no son válidos.");
            }

            if (completar) {
                Object.entries(camposPersona).forEach(function ([campo, selector]) {
                    $(selector).val(resultado.persona[campo] ?? "");
                });

                datosAutocompletados = true;
            } else {
                $("#nuevoUsuarioIdPersona").val(resultado.persona.id_persona ?? "");
            }
        } else {
            if (completar && datosAutocompletados) limpiarPersona();

            $("#nuevoUsuarioIdPersona").val("");
        }

        if (resultado.es_usuario) {
            campoCedula[0].setCustomValidity(
                "Esta persona ya se encuentra registrada como usuario."
            );

            throw new Error("Esta persona ya se encuentra registrada como usuario.");
        }

        return true;
    }

    $("#nuevoUsuarioCedula").on("input", function () {
        ++versionPersona;
        this.setCustomValidity("");

        if (datosAutocompletados) limpiarPersona();

        $("#nuevoUsuarioIdPersona").val("");
    }).on("blur", function () {
        if (operacionEnCurso) return;

        const turnoModal = versionPersona;

        buscarPersonaUsuario().catch(function (error) {
            if (versionPersona === turnoModal + 1) {
                mensajeFormulario($("#formNuevoUsuario"))
                    .text(error.message)
                    .prop("hidden", false);
            }
        });
    });


    /* ================================================= */
    /* BOTONES Y FILAS DE LOS LISTADOS */
    /* ================================================= */

    const atributosUsuario = {
        id_usuario: "id-usuario",
        id_persona: "id-persona",
        cedula: "cedula",
        nombre: "nombre",
        apellido: "apellido",
        telefono: "telefono",
        correo: "correo",
        direccion: "direccion",
        ciudad: "ciudad",
        nickname: "nickname",
        id_rol: "id-rol"
    };

    const iconoDetalleUsuario = $(
        "#tablaUsuarios .detalle-accion-icono svg, #formNuevoUsuario .templo-eye-open"
    ).first().clone().removeAttr("class");

    function crearBoton(clase, destino, titulo, atributos, icono) {
        return $("<button>", {
            type: "button",
            class: "btn-action " + clase,
            title: titulo,
            "aria-label": titulo,
            "data-bs-toggle": "modal",
            "data-bs-target": destino
        }).attr(atributos).append($("<i>", {
            class: "bi " + icono,
            "aria-hidden": "true"
        }));
    }

    function crearFilaUsuario(usuario) {
        const fila = $("<tr>");
        const nombreCompleto = ((usuario.nombre || "") + " " + (usuario.apellido || "")).trim();
        const estado = String(usuario.estado_usuario || "ACTIVO").trim().toUpperCase();

        [
            usuario.cedula,
            nombreCompleto,
            usuario.telefono,
            usuario.correo,
            usuario.nombre_rol
        ].forEach(function (valor) {
            $("<td>").text(valor ?? "").appendTo(fila);
        });

        $("<td>").append($("<span>", {
            class: "badge estado-realizada",
            text: estado
        })).appendTo(fila);

        const datos = {};

        Object.entries(atributosUsuario).forEach(function ([campo, atributo]) {
            datos["data-" + atributo] = String(usuario[campo] ?? "");
        });

        const referencia = {
            "data-id-usuario": datos["data-id-usuario"],
            "data-nombre": nombreCompleto,
            "data-estado": estado
        };

        const acciones = $("<div>", { class: "btn-group-actions" });

        const detalle = crearBoton(
            "btn-detail",
            "#modalDetalleUsuarioDinamico",
            "Ver detalles del usuario",
            referencia,
            "bi-eye"
        );

        if (iconoDetalleUsuario.length) {
            detalle.empty().append($("<span>", {
                class: "detalle-accion-icono",
                "aria-hidden": "true"
            }).append(iconoDetalleUsuario.clone()));
        }

        acciones.append(
            detalle,

            crearBoton(
                "btn-edit btnEditarUsuario",
                "#modalEditarUsuario",
                "Editar usuario",
                datos,
                "bi-pencil-square"
            ),

            crearBoton(
                "btn-reset-password btnRestablecerClave",
                "#modalRestablecerClave",
                "Restablecer contraseña",
                referencia,
                "bi-bag-heart"
            ),

            crearBoton(
                "btn-deactivate btnCambiarEstadoUsuario",
                "#modalEstadoUsuario",
                "Desactivar usuario",
                referencia,
                "bi-trash-fill"
            )
        );

        $("<td>", { class: "columna-acciones" }).append(acciones).appendTo(fila);

        return fila[0];
    }

    function crearFilaRol(rol) {
        const fila = $("<tr>");
        const estado = String(rol.estado_rol || "ACTIVO").trim().toUpperCase();

        const datos = {
            "data-id-rol": String(rol.id_rol ?? ""),
            "data-nombre-rol": String(rol.nombre_rol ?? ""),
            "data-descripcion": String(rol.descripcion ?? ""),
            "data-estado-rol": estado
        };

        $("<td>").text(rol.nombre_rol ?? "").appendTo(fila);
        $("<td>").text(rol.descripcion ?? "").appendTo(fila);

        $("<td>").append($("<span>", {
            class: "badge " + (
                estado === "ACTIVO"
                    ? "estado-registro-activo"
                    : "estado-registro-inactivo"
            ),
            text: estado
        })).appendTo(fila);

        const acciones = $("<div>", { class: "btn-group-actions" }).append(
            crearBoton(
                "btn-edit btnEditarRol",
                "#modalEditarRol",
                "Editar rol",
                datos,
                "bi-pencil-fill"
            ),

            crearBoton(
                "btn-deactivate btnDesactivarRol",
                "#modalEstadoRol",
                "Desactivar rol",
                datos,
                "bi-trash-fill"
            )
        );

        $("<td>", { class: "columna-acciones" }).append(acciones).appendTo(fila);

        return fila[0];
    }


    /* ================================================= */
    /* FICHA DE DETALLES DEL USUARIO */
    /* ================================================= */

    const camposDetalle = {
        "Cédula": ["cedula", "bi-person-vcard"],
        "Nickname": ["nickname", "bi-person-badge"],
        "Nombre": ["nombre", "bi-person-heart"],
        "Apellido": ["apellido", "bi-person-heart"],
        "Teléfono": ["telefono", "bi-telephone-fill"],
        "Correo electrónico": ["correo", "bi-envelope-heart"],
        "Ciudad": ["ciudad", "bi-geo-alt-fill"],
        "Rol": ["nombre_rol", "bi-person-gear"],
        "Dirección": ["direccion", "bi-house-heart-fill"],
        "Clave": ["clave_oculta", "bi-lock-fill"],
        "Estado": ["estado_usuario", "bi-check-circle"]
    };

    let modalDetalle = $(".modal[id^='modalDetalleUsuario']").first().clone();

    /* Utilizar la ficha existente como plantilla para los registros actualizados */
    if (!modalDetalle.length) {
        modalDetalle = $(
            '<div class="modal fade" tabindex="-1" aria-hidden="true">' +
                '<div class="modal-dialog modal-lg modal-dialog-centered">' +
                    '<div class="modal-content templo-modal detalle-ficha-modal">' +
                        '<div class="modal-header">' +
                            '<h5 class="modal-title">Detalles del Usuario</h5>' +
                            '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>' +
                        '</div>' +
                        '<div class="modal-body detalle-ficha-modal-body">' +
                            '<div class="detalle-ficha-panel">' +
                                '<div class="row g-2 detalle-ficha-grid"></div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="modal-footer">' +
                            '<button type="button" class="btn-templo-secondary" data-bs-dismiss="modal">Cerrar</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );

        Object.entries(camposDetalle).forEach(function ([etiqueta, datos]) {
            const contenido = $("<div>", {
                class: "detalle-ficha-card-content"
            }).append(
                $("<span>", { class: "detalle-ficha-label", text: etiqueta }),
                $("<p>", { class: "detalle-ficha-value mb-0" })
            );

            const tarjeta = $("<div>", {
                class: "detalle-ficha-card h-100"
            }).append(
                $("<span>", {
                    class: "detalle-ficha-card-icon",
                    "aria-hidden": "true"
                }).append($("<i>", { class: "bi " + datos[1] })),

                contenido
            );

            modalDetalle.find(".detalle-ficha-grid").append(
                $("<div>", {
                    class: etiqueta === "Dirección" ? "col-12" : "col-md-6"
                }).append(tarjeta)
            );
        });
    }

    modalDetalle.attr({
        id: "modalDetalleUsuarioDinamico",
        "aria-labelledby": "tituloDetalleUsuarioDinamico"
    });

    modalDetalle.find(".modal-title").attr("id", "tituloDetalleUsuarioDinamico");
    modalDetalle.appendTo("body");

    modalDetalle.on("show.bs.modal", function (evento) {
        const usuario = usuariosPorId.get(
            String($(evento.relatedTarget).attr("data-id-usuario"))
        );

        if (!usuario) {
            evento.preventDefault();
            return;
        }

        modalDetalle.find(".detalle-ficha-card").each(function () {
            const tarjeta = $(this);
            const etiqueta = tarjeta.find(".detalle-ficha-label").text().trim();
            const campo = camposDetalle[etiqueta]?.[0];
            const valor = campo === "clave_oculta" ? "********" : usuario[campo];

            tarjeta.find(".detalle-ficha-value, .detalle-ficha-status")
                .text(valor || "No registrado");
        });
    });


    /* ================================================= */
    /* ACTUALIZACIÓN DE LAS LISTAS Y SELECTORES */
    /* ================================================= */

    function reemplazarFilas(tabla, filas) {
        const pagina = tabla.page();

        tabla.clear();
        tabla.rows.add(filas);
        tabla.draw(false);

        const ultima = Math.max(tabla.page.info().pages - 1, 0);

        if (pagina > ultima) tabla.page(ultima).draw("page");
    }

    function actualizarSelectoresRoles(roles) {
        $("#nuevoUsuarioRol, #editarUsuarioRol").each(function () {
            const selector = $(this);
            const seleccionado = String(selector.val() || "");

            selector.empty().append($("<option>", {
                value: "",
                text: "Seleccione un rol"
            }));

            roles.forEach(function (rol) {
                selector.append($("<option>", {
                    value: String(rol.id_rol),
                    text: rol.nombre_rol ?? ""
                }));
            });

            selector.val(
                roles.some(rol => String(rol.id_rol) === seleccionado)
                    ? seleccionado
                    : ""
            );
        });
    }

    async function actualizarListas() {
        const turno = ++versionLista;

        try {
            await Promise.all([
                tablaUsuarios.preparada,
                tablaRoles.preparada
            ]);

            if (turno !== versionLista) return false;

            const respuesta = await solicitarSeguridad({ accion: "consultar" });

            if (turno !== versionLista) return false;

            if (
                !Array.isArray(respuesta.usuarios) ||
                !Array.isArray(respuesta.roles) ||
                !respuesta.usuarios.every(usuario => $.isPlainObject(usuario)) ||
                !respuesta.roles.every(rol => $.isPlainObject(rol))
            ) {
                throw new Error("Las listas recibidas no tienen un formato válido.");
            }

            const filasUsuarios = respuesta.usuarios.map(crearFilaUsuario);
            const filasRoles = respuesta.roles.map(crearFilaRol);

            usuariosPorId = new Map(
                respuesta.usuarios.map(usuario => [
                    String(usuario.id_usuario),
                    usuario
                ])
            );

            reemplazarFilas(tablaUsuarios.instancia, filasUsuarios);
            reemplazarFilas(tablaRoles.instancia, filasRoles);
            actualizarSelectoresRoles(respuesta.roles);

            return true;
        } catch (error) {
            if (turno === versionLista) throw error;

            return false;
        }
    }


    /* ================================================= */
    /* CONTENIDO Y LIMPIEZA DE LOS FORMULARIOS */
    /* ================================================= */

    function limpiarFormulario(modal) {
        const formulario = modal.find("form").first();

        if (!formulario.length) return;

        formulario[0].reset();

        formulario.find("input").each(function () {
            this.setCustomValidity("");
        });

        formulario.find(".btnAlternarClave").each(function () {
            establecerVisibilidad($(this), false);
        });

        mensajeFormulario(formulario).empty().prop("hidden", true);
    }

    modales.on("show.bs.modal", function (evento) {
        if (operacionEnCurso) {
            evento.preventDefault();
            return;
        }

        const modal = $(this);
        const idModal = modal.attr("id");
        const boton = $(evento.relatedTarget);
        const formulario = modal.find("form");

        limpiarFormulario(modal);

        if (idModal === "modalNuevoUsuario") {
            ++versionPersona;
            datosAutocompletados = false;
        }

        if (idModal === "modalEditarUsuario") {
            Object.entries(atributosUsuario).forEach(function ([campo, atributo]) {
                formulario.find('[name="' + campo + '"]')
                    .val(boton.attr("data-" + atributo) ?? "");
            });

            $("#editarUsuarioCedula").val(boton.attr("data-cedula") || "");
        }

        if (idModal === "modalRestablecerClave") {
            $("#restablecerClaveIdUsuario").val(boton.attr("data-id-usuario") || "");
            $("#restablecerClaveUsuario").val(boton.attr("data-nombre") || "");
        }

        if (idModal === "modalEstadoUsuario") {
            $("#estadoUsuarioId").val(boton.attr("data-id-usuario") || "");
            $("#nuevoEstadoUsuario").val("INACTIVO");
            $("#nombreEstadoUsuario").text(boton.attr("data-nombre") || "");
            $("#tituloEstadoUsuario").text("Desactivar Usuario");
            $("#preguntaEstadoUsuario").text("¿Deseas desactivar este usuario?");
            $("#advertenciaEstadoUsuario").text(
                "El usuario no podrá iniciar sesión mientras permanezca inactivo."
            );
        }

        if (idModal === "modalEditarRol") {
            $("#editarIdRol").val(boton.attr("data-id-rol") || "");
            $("#editarNombreRol").val(boton.attr("data-nombre-rol") || "");
            $("#editarDescripcionRol").val(boton.attr("data-descripcion") || "");
            $("#editarEstadoRol").val(boton.attr("data-estado-rol") || "");
        }

        if (idModal === "modalEstadoRol") {
            $("#estadoIdRol").val(boton.attr("data-id-rol") || "");
            $("#nuevoEstadoRol").val("INACTIVO");
            $("#nombreEstadoRol").text(boton.attr("data-nombre-rol") || "");
        }
    });

    modales.on("hide.bs.modal", function (evento) {
        if ($(this).find("form").data("enviando")) {
            evento.preventDefault();
        }
    });

    modales.on("hidden.bs.modal", function () {
        limpiarFormulario($(this));

        if ($(this).attr("id") === "modalNuevoUsuario") {
            ++versionPersona;
            datosAutocompletados = false;
        }
    });


    /* ================================================= */
    /* ENVÍO DE LAS OPERACIONES DE SEGURIDAD */
    /* ================================================= */

    modales.find("form").on("submit", async function (evento) {
        evento.preventDefault();

        if (operacionEnCurso) return;

        const formulario = $(this);
        const aviso = mensajeFormulario(formulario).empty().prop("hidden", true);

        validarCoincidencia(formulario);

        if (!this.reportValidity()) return;

        const controles = formulario.find("input, select, textarea, button")
            .map(function () {
                const elemento = $(this);

                return {
                    elemento,
                    deshabilitado: elemento.prop("disabled")
                };
            }).get();

        const enviar = formulario.find('[type="submit"]');
        const contenidoBoton = enviar.html();

        operacionEnCurso = true;
        formulario.data("enviando", true);
        enviar.prop("disabled", true).text("Procesando...");
        ++versionLista;
        mensajeGeneral.prop("hidden", true);

        try {
            if (formulario.attr("id") === "formNuevoUsuario") {
                if (!await buscarPersonaUsuario(false)) {
                    throw new Error("Revise la cédula y vuelva a enviar el formulario.");
                }

                validarCoincidencia(formulario);

                if (!this.reportValidity()) {
                    throw new Error("Revise los datos del formulario.");
                }
            }

            const datos = {};

            formulario.serializeArray().forEach(campo => {
                datos[campo.name] = campo.value;
            });

            controles.forEach(control => {
                control.elemento.prop("disabled", true);
            });

            const respuesta = await solicitarSeguridad(datos);

            formulario.data("enviando", false);

            const modal = formulario.closest(".modal");

            const cerrarModal = function () {
                window.bootstrap.Modal.getOrCreateInstance(modal[0]).hide();
            };

            modal.one("shown.bs.modal", cerrarModal);

            modal.one("hidden.bs.modal", function () {
                modal.off("shown.bs.modal", cerrarModal);
            });

            cerrarModal();
            mostrarMensaje(respuesta.mensaje);

            if (datos.accion !== "restablecer_clave") {
                try {
                    await actualizarListas();
                } catch (error) {
                    mostrarMensaje(
                        respuesta.mensaje +
                        " No se pudieron actualizar las listas. La operación ya fue confirmada.",
                        true,
                        true
                    );
                }
            }
        } catch (error) {
            aviso.text(error.message).prop("hidden", false);
            formulario.find(".modal-body").scrollTop(0);
        } finally {
            controles.forEach(control => {
                control.elemento.prop("disabled", control.deshabilitado);
            });

            enviar.html(contenidoBoton);
            formulario.data("enviando", false);
            operacionEnCurso = false;
        }
    });


    /* ================================================= */
    /* CONSULTA INICIAL */
    /* ================================================= */

    actualizarListas().catch(error => {
        mostrarMensaje(error.message, true, true);
    });
});