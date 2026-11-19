/* ===================================================== */
/* AVISOS Y CONFIRMACIONES DEL SISTEMA */
/* ===================================================== */

(function () {
    "use strict";

    const tipos = {
        success: { icono: "success", titulo: "Operación realizada", color: "#4f7f78" },
        error: { icono: "error", titulo: "No se pudo completar la operación", color: "#c40056" },
        warning: { icono: "warning", titulo: "Revise la información", color: "#B89344" },
        info: { icono: "info", titulo: "Información", color: "#7a655b" },
        question: { icono: "question", titulo: "Confirmar acción", color: "#a65f6d" }
    };

    const imagenes = {};
    const pendientes = new Map();
    let cola = Promise.resolve();


    /* ================================================= */
    /* PREPARACIÓN DE LOS AVISOS */
    /* ================================================= */

    function disponible() {
        return typeof window.Swal?.fire === "function";
    }

    function normalizarTipo(tipo) {
        return ({ danger: "error", exito: "success", advertencia: "warning", informacion: "info" })[tipo] ||
            (Object.hasOwn(tipos, tipo) ? tipo : "error");
    }

    function obtenerOrigen(origen) {
        if (origen?.jquery) origen = origen[0];
        if (typeof origen === "string") origen = document.querySelector(origen);

        return origen instanceof HTMLElement && origen.isConnected ? origen : null;
    }

    function mostrar(opciones = {}) {
        if (!disponible()) return Promise.resolve(false);

        const tipo = normalizarTipo(opciones.tipo);
        const configuracion = tipos[tipo];
        const titulo = String(opciones.titulo || configuracion.titulo);
        const mensaje = String(opciones.mensaje || "");
        const confirmacion = opciones.confirmacion === true;
        const clave = opciones.clave || JSON.stringify([tipo, titulo, mensaje, confirmacion]);

        if (pendientes.has(clave)) return pendientes.get(clave);

        const tarea = cola.then(async function () {
            if (!disponible()) return false;

            /* Presentar el aviso después de recuperar los controles del formulario */
            await new Promise(resolve => setTimeout(resolve, 0));

            const origen = obtenerOrigen(opciones.origen);
            const modal = origen?.closest(".modal.show") || document.querySelector(".modal.show");
            const imagen = imagenes[tipo];

            const resultado = await window.Swal.fire({
                titleText: titulo,
                text: mensaje,
                icon: imagen ? undefined : configuracion.icono,
                iconColor: configuracion.color,
                imageUrl: imagen || undefined,
                imageWidth: imagen ? 150 : undefined,
                imageHeight: imagen ? 150 : undefined,
                imageAlt: imagen ? titulo : undefined,
                target: modal || document.body,
                heightAuto: false,
                scrollbarPadding: false,
                returnFocus: true,
                keydownListenerCapture: true,
                stopKeydownPropagation: true,
                allowOutsideClick: false,
                showCancelButton: confirmacion,
                confirmButtonText: opciones.textoConfirmar || (confirmacion ? "Sí, continuar" : "Entendido"),
                cancelButtonText: opciones.textoCancelar || "Cancelar",
                focusCancel: confirmacion,
                buttonsStyling: false,
                customClass: {
                    container: "templo-alerta-contenedor",
                    popup: "templo-alerta",
                    title: "templo-alerta-titulo",
                    htmlContainer: "templo-alerta-texto",
                    actions: "templo-alerta-acciones",
                    confirmButton: "templo-alerta-aceptar",
                    cancelButton: "templo-alerta-cancelar"
                }
            });

            return confirmacion ? resultado.isConfirmed === true : true;

        }).catch(function () {
            /* Una confirmación que no se pudo mostrar nunca autoriza una operación */
            return false;

        }).finally(function () {
            pendientes.delete(clave);
        });

        pendientes.set(clave, tarea);
        cola = tarea.then(function () {});

        return tarea;
    }


    /* ================================================= */
    /* INTERFAZ COMÚN DE LOS MÓDULOS */
    /* ================================================= */

    function notificar(mensaje, tipo = "error", opciones = {}) {
        return mostrar({ ...opciones, mensaje, tipo });
    }

    function confirmar(mensaje, opciones = {}) {
        return mostrar({ ...opciones, mensaje, tipo: "question", confirmacion: true });
    }

    function configurarImagenes(nuevasImagenes) {
        Object.keys(tipos).forEach(function (tipo) {
            const ruta = nuevasImagenes?.[tipo];

            if (ruta === null) delete imagenes[tipo];

            if (typeof ruta === "string" && /^Assets\/img\/[\w./-]+\.(png|webp)$/i.test(ruta)) {
                imagenes[tipo] = ruta;
            }
        });
    }

    function rechazo(mensaje, respuesta = {}, origen = null) {
        const formulario = obtenerOrigen(origen);
        const campos = Object.keys(respuesta?.errores || {});

        if (typeof respuesta?.campo === "string") campos.push(respuesta.campo);

        const errorEnCampo = formulario && campos.some(function (nombre) {
            return Array.from(formulario.querySelectorAll("[name]")).some(function (campo) {
                return campo.name === nombre && campo.type !== "hidden";
            });
        });

        const ambiguo = respuesta?.ambiguo === true || respuesta?.status === 0 || respuesta?.estado === 0;

        return errorEnCampo ? Promise.resolve(false) : notificar(mensaje, ambiguo ? "warning" : "error", { origen });
    }

    function desdeModal(origen, opciones = {}) {
        const modal = obtenerOrigen(origen);

        if (!modal) return Promise.resolve(false);

        const texto = modal.querySelector(".seguridad-resultado-mensaje")?.textContent.trim() ||
            Array.from(modal.querySelectorAll(".modal-body p")).map(p => p.textContent.trim()).join("\n\n");

        const tipo = modal.querySelector(".bi-heartbreak-fill, [role='alert']") ? "error" :
            modal.querySelector(".bi-exclamation-triangle-fill, .bi-exclamation-circle-fill") ? "warning" : "success";

        return notificar(texto, tipo, {
            titulo: modal.querySelector(".modal-title")?.textContent.trim(),
            ...opciones
        });
    }

    window.Alertas = Object.freeze({
        disponible,
        mostrar,
        notificar,
        confirmar,
        rechazo,
        desdeModal,
        configurarImagenes
    });


    /* ================================================= */
    /* CONFIRMACIONES EXISTENTES DE BOOTSTRAP */
    /* ================================================= */

    const confirmacionesAbiertas = new WeakSet();

    /* Reutilizar la preparación de los formularios y sus acciones originales */
    if (window.jQuery) {
        window.jQuery(function () {
            document.querySelectorAll("[data-alerta-inicial]").forEach(function (elemento) {
                const mensaje = elemento.textContent.trim();

                if (mensaje) notificar(mensaje, elemento.getAttribute("data-alerta-inicial") || "error");

                elemento.removeAttribute("data-alerta-inicial");
            });
        });

        window.jQuery(document).on("show.bs.modal", "[data-alerta-confirmacion]", function (evento) {
            if (!disponible() || evento.isDefaultPrevented()) return;

            evento.preventDefault();

            const modal = this;

            if (confirmacionesAbiertas.has(modal)) return;

            const selector = modal.getAttribute("data-alerta-aceptar") || "button[type='submit']";
            const boton = modal.querySelector(selector);

            if (!boton || boton.disabled) return;

            const parrafos = Array.from(modal.querySelectorAll(".modal-body p"))
                .filter(elemento => !elemento.closest(".alert, .d-none, [hidden]"))
                .map(elemento => elemento.textContent.trim())
                .filter(Boolean);

            confirmacionesAbiertas.add(modal);

            confirmar(parrafos.join("\n\n"), {
                titulo: modal.querySelector(".modal-title")?.textContent.trim() || "Confirmar acción",
                textoConfirmar: boton.textContent.trim(),
                origen: evento.relatedTarget

            }).then(function (aceptada) {
                if (aceptada && modal.isConnected && !boton.disabled) boton.click();

            }).finally(function () {
                confirmacionesAbiertas.delete(modal);
            });
        });
    }
})();