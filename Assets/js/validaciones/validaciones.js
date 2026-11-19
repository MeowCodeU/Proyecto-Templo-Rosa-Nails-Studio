/* ===================================================== */
/* VALIDACIONES COMPARTIDAS - TEMPLO ROSA */
/* ===================================================== */

import { Regex } from "./regex.js";

/* Reglas comunes para los formularios de clientes */
const reglasClientes = Object.freeze({
    cedula: { obligatorio: true, minimo: 6, maximo: 20, expresion: Regex.cedula, etiqueta: "La cédula" },
    nombre: { obligatorio: true, minimo: 2, maximo: 60, expresion: Regex.nombre, etiqueta: "El nombre" },
    apellido: { obligatorio: true, minimo: 2, maximo: 60, expresion: Regex.apellido, etiqueta: "El apellido" },
    telefono: { obligatorio: false, minimo: 7, maximo: 25, expresion: Regex.telefono, etiqueta: "El teléfono" },
    correo: { obligatorio: false, minimo: 0, maximo: 100, expresion: Regex.correo, etiqueta: "El correo" },
    ciudad: { obligatorio: false, minimo: 2, maximo: 80, expresion: Regex.ciudad, etiqueta: "La ciudad" },
    direccion: { obligatorio: false, minimo: 0, maximo: 150, etiqueta: "La dirección" },
    alergias: { obligatorio: false, minimo: 0, maximo: 2000, etiqueta: "Las observaciones" }
});

/* Reglas comunes para los datos de una persona de contacto */
const reglasContacto = Object.freeze({
    cedula: reglasClientes.cedula,
    nombre: reglasClientes.nombre,
    apellido: reglasClientes.apellido,
    telefono: reglasClientes.telefono,
    correo: reglasClientes.correo,
    direccion: reglasClientes.direccion,
    ciudad: reglasClientes.ciudad
});

/* Reglas para registrar los datos de una empresa y su contacto */
const reglasProveedores = Object.freeze({
    rif: { obligatorio: true, minimo: 1, maximo: 25, etiqueta: "El RIF" },
    nombre_empresa: { obligatorio: true, minimo: 1, maximo: 100, etiqueta: "El nombre de la empresa" },
    ...reglasContacto
});

/* Reglas para los datos generales de un insumo */
const reglasInsumos = Object.freeze({
    nombre_insumo: { obligatorio: true, minimo: 1, maximo: 100, etiqueta: "El nombre del insumo" },
    presentacion: { obligatorio: false, minimo: 0, maximo: 80, etiqueta: "La presentación" },
    descripcion: { obligatorio: false, minimo: 0, maximo: 255, etiqueta: "La descripción" },
    stock_minimo: {
        comprobar: valor => validarEntero(valor)
            ? null : "Ingrese una cantidad entera entre 0 y 4294967295."
    },
    tipo_control: {
        comprobar: valor => ["UNITARIO", "POR_ENVASE"].includes(valor)
            ? null : "Seleccione un tipo de control válido."
    }
});

/* Reglas para el catálogo de servicios */
const reglasServicios = Object.freeze({
    nombre_servicio: { obligatorio: true, minimo: 1, maximo: 120, etiqueta: "El nombre del servicio" },
    descripcion: { obligatorio: false, minimo: 0, maximo: 255, etiqueta: "La descripción" },
    precio: {
        comprobar: valor => validarPrecio(valor)
            ? null : "Ingrese un precio entre 0 y 99999999.99, con un máximo de dos decimales."
    },
    duracion_estimada: {
        comprobar: valor => valor === "" || validarEntero(valor, 1, 65535)
            ? null : "Ingrese una duración entera entre 1 y 65535 minutos, o deje el campo vacío."
    }
});

/* Reglas para el catálogo de estados de agendamiento */
const reglasEstadosAgendamiento = Object.freeze({
    nombre_estado: { obligatorio: true, minimo: 1, maximo: 50, etiqueta: "El nombre del estado" },
    descripcion: { obligatorio: false, minimo: 0, maximo: 150, etiqueta: "La descripción" }
});

/* Reglas para el catálogo de tipos de agendamiento */
const reglasTiposAgendamiento = Object.freeze({
    nombre_tipo_agendamiento: { obligatorio: true, minimo: 1, maximo: 60, etiqueta: "El nombre del tipo de agendamiento" },
    descripcion: { obligatorio: false, minimo: 0, maximo: 150, etiqueta: "La descripción" }
});

/* Reglas para registrar los datos personales y el rol de un usuario */
const reglasUsuarios = Object.freeze({
    ...reglasContacto,
    correo: { ...reglasClientes.correo, obligatorio: true },
    nickname: { obligatorio: false, minimo: 0, maximo: 50, etiqueta: "El nickname" },
    id_rol: {
        comprobar: valor => validarEntero(valor, 1)
            ? null : "Seleccione un rol válido."
    }
});

/* Reglas para el catálogo de roles */
const reglasRoles = Object.freeze({
    nombre_rol: { obligatorio: true, minimo: 1, maximo: 40, etiqueta: "El nombre del rol" },
    descripcion: { obligatorio: false, minimo: 0, maximo: 150, etiqueta: "La descripción" }
});

/* Reglas para registrar o modificar una cita */
const reglasAgendamientos = Object.freeze({
    id_cliente: {
        comprobar: valor => validarIdentificador(valor)
            ? null : "Seleccione un cliente válido."
    },
    id_usuario_asignado: {
        comprobar: valor => validarIdentificador(valor)
            ? null : "Seleccione una especialista válida."
    },
    id_estado_agendamiento: {
        comprobar: valor => validarIdentificador(valor)
            ? null : "Seleccione un estado válido."
    },
    id_tipo_agendamiento: {
        comprobar: valor => validarIdentificador(valor)
            ? null : "Seleccione un tipo de agendamiento válido."
    },
    fecha: {
        comprobar: valor => validarFechaAgenda(valor)
            ? null : "Ingrese una fecha válida."
    },
    hora: {
        comprobar: valor => validarHora(valor)
            ? null : "Ingrese una hora válida."
    },
    duracion_minutos: {
        comprobar: valor => validarIdentificador(valor) && Number(valor) <= 65535
            ? null : "Ingrese una duración entera entre 1 y 65535 minutos."
    },
    monto_total: {
        comprobar: valor => validarPrecio(valor)
            ? null : "Ingrese un monto entre 0 y 99999999.99, con un máximo de dos decimales."
    },
    observacion_inicial: {
        comprobar: valor => typeof valor === "string" && !valor.includes("\0") &&
            new TextEncoder().encode(valor.trim()).length <= 65535
            ? null : "Ingrese una observación válida que no supere el tamaño permitido."
    }
});

/* Reglas para registrar o modificar un bloqueo de horario */
const reglasBloqueosHorario = Object.freeze({
    id_usuario_especialista: reglasAgendamientos.id_usuario_asignado,
    fecha: reglasAgendamientos.fecha,
    hora_inicio: reglasAgendamientos.hora,
    hora_fin: reglasAgendamientos.hora,
    motivo: {
        comprobar: valor => typeof valor === "string" && !valor.includes("\0") &&
            Array.from(valor.trim()).length <= 255
            ? null : "Ingrese un motivo válido de hasta 255 caracteres."
    }
});

/* Reglas para identificar la cita o el bloqueo que se modifica */
const reglasEditarAgendamiento = Object.freeze({
    id_agendamiento: {
        comprobar: valor => validarIdentificador(valor)
            ? null : "Seleccione un agendamiento válido."
    },
    ...reglasAgendamientos
});

const reglasEditarBloqueoHorario = Object.freeze({
    id_bloqueo: {
        comprobar: valor => validarIdentificador(valor)
            ? null : "Seleccione un bloqueo válido."
    },
    ...reglasBloqueosHorario
});

/* Comprueba la complejidad y el límite del hash de las contraseñas nuevas */
function validarClave(clave) {
    return typeof clave === "string" && !clave.includes("\0") &&
        Array.from(clave).length >= 8 && Regex.clave.test(clave) &&
        new TextEncoder().encode(clave).length <= 72;
}

/* Comprueba precios sin redondear decimales ni aceptar notación exponencial */
function validarPrecio(valor) {
    if (typeof valor !== "string") return false;

    const texto = valor.trim();
    const partes = texto.match(Regex.precio);

    if (!texto || !partes) return false;

    const entero = partes[1].replace(Regex.cerosIniciales, "") || "0";

    return entero.length <= 8;
}

/* Comprueba cantidades enteras sin aceptar decimales ni notación exponencial */
function validarEntero(valor, minimo = 0, maximo = 4294967295) {
    return typeof valor === "string" && Regex.entero.test(valor) &&
        Number.isSafeInteger(Number(valor)) &&
        Number(valor) >= minimo && Number(valor) <= maximo;
}

/* Comprueba fechas reales con el formato utilizado por los formularios */
function validarFecha(valor) {
    if (typeof valor !== "string" || !Regex.fecha.test(valor)) return false;

    const [ano, mes, dia] = valor.split("-").map(Number);
    const bisiesto = ano % 4 === 0 && (ano % 100 !== 0 || ano % 400 === 0);
    const dias = [31, bisiesto ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    return mes >= 1 && mes <= 12 && dia >= 1 && dia <= dias[mes - 1];
}

/* Comprueba contadores recibidos como números o cadenas de texto */
function esEntero(valor, minimo = 0) {
    return (typeof valor === "number" || typeof valor === "string") &&
        String(valor).trim() !== "" &&
        Number.isSafeInteger(Number(valor)) &&
        Number(valor) >= minimo;
}

/* Comprueba meses entre los años 1000 y 9999 sin modificar su formato */
function esMes(valor) {
    return typeof valor === "string" && Regex.mes.test(valor);
}

/* Reutiliza la comprobación de fechas para los datos recibidos por Inicio */
function esFecha(valor) {
    return validarFecha(valor) && Number(valor.slice(0, 4)) >= 1000;
}

/* Comprueba identificadores positivos sin aceptar valores booleanos ni decimales */
function validarIdentificador(valor) {
    if (typeof valor !== "string" && typeof valor !== "number") return false;
    if (typeof valor === "number" && !Number.isSafeInteger(valor)) return false;

    const texto = String(valor).trim();

    return Regex.identificador.test(texto) && Number(texto) <= 4294967295;
}

/* Comprueba las fechas admitidas por los campos DATE del agendamiento */
function validarFechaAgenda(valor) {
    return typeof valor === "string" && validarFecha(valor.trim()) &&
        Number(valor.trim().slice(0, 4)) >= 1000;
}

/* Comprueba horas con minutos y segundos opcionales */
function validarHora(valor) {
    return typeof valor === "string" && Regex.hora.test(valor.trim());
}

/* Comprueba los servicios antes de enviar la selección como arreglo */
function validarServicios(valor) {
    return Array.isArray(valor) && valor.length > 0 && valor.length <= 1000 &&
        Array.from(valor).every(validarIdentificador);
}

/* Comprueba que el bloqueo termine después de su inicio */
function validarIntervaloHoras(inicio, fin) {
    if (!validarHora(inicio) || !validarHora(fin)) return false;

    const normalizar = hora => hora.trim().length === 5
        ? hora.trim() + ":00"
        : hora.trim();

    return normalizar(fin) > normalizar(inicio);
}

/* Comprueba que la duración no desplace la atención a otra fecha */
function validarHorarioAgenda(hora, duracion) {
    if (!validarHora(hora) || !validarIdentificador(duracion) || Number(duracion) > 65535) return false;

    const [horas, minutos, segundos = 0] = hora.trim().split(":").map(Number);
    const inicio = horas * 3600 + minutos * 60 + segundos;

    return inicio + Number(duracion) * 60 < 86400;
}

/* Comprueba el valor con la regla del campo */
function validarValor(nombre, entrada, reglas = reglasClientes) {
    const regla = reglas[nombre];

    if (!regla) return null;
    if (typeof regla.comprobar === "function") return regla.comprobar(entrada);
    if (typeof entrada !== "string") return "El valor del campo no es válido.";

    const valor = entrada.trim();

    if (!valor) {
        if (!regla.obligatorio) return null;

        return regla.etiqueta +
            (regla.etiqueta.startsWith("El ") ? " es obligatorio." : " es obligatoria.");
    }

    const longitud = Array.from(valor).length;

    if (longitud < regla.minimo || longitud > regla.maximo) {
        return regla.etiqueta + " debe tener entre " + regla.minimo +
            " y " + regla.maximo + " caracteres.";
    }

    if (regla.expresion && !regla.expresion.test(valor)) {
        if (nombre === "cedula") return "La cédula debe contener solo números (6 a 20 dígitos).";
        if (nombre === "telefono") return "El teléfono debe contener solo números, espacios, +, - o paréntesis.";
        if (nombre === "correo") return "Ingrese un correo electrónico válido.";

        return regla.etiqueta + " contiene caracteres no permitidos.";
    }

    if (nombre === "telefono" && (valor.match(Regex.digitos) || []).length < 7) {
        return "El teléfono debe incluir al menos 7 dígitos.";
    }

    return null;
}

/* Mantiene el mensaje fuera del contenedor que posiciona el ojito */
function referenciaCampo(campo) {
    return campo.closest(".templo-password-field") || campo;
}

/* Muestra solamente texto bajo el campo, sin cambiar sus bordes ni fondos */
function mostrarCampo(campo, mensaje, marcarCorrecto = false) {
    if (!campo || campo.type === "hidden") return;

    const referencia = referenciaCampo(campo);
    const siguiente = referencia.nextElementSibling;

    if (siguiente && siguiente.classList.contains("templo-validacion-feedback")) {
        siguiente.remove();
    }

    campo.removeAttribute("aria-invalid");

    if (!mensaje && (!marcarCorrecto || campo.readOnly || campo.disabled || !String(campo.value).trim())) return;

    const texto = document.createElement("div");

    texto.className = "templo-validacion-feedback " + (mensaje ? "is-invalid" : "is-valid");
    texto.textContent = mensaje || "¡Correcto!";

    referencia.insertAdjacentElement("afterend", texto);

    if (mensaje) campo.setAttribute("aria-invalid", "true");
}

/* Limpia los mensajes generados por el validador */
function limpiarFormulario(formulario) {
    if (!formulario) return;

    formulario.querySelectorAll(".templo-validacion-feedback").forEach(function (mensaje) {
        const anterior = mensaje.previousElementSibling;

        const campo = anterior?.matches(".templo-password-field")
            ? anterior.querySelector("input")
            : anterior;

        if (campo) campo.removeAttribute("aria-invalid");

        mensaje.remove();
    });
}

/* Localiza un campo por su atributo name */
function obtenerCampo(formulario, nombre) {
    return Array.from(formulario.querySelectorAll("[name]")).find(function (campo) {
        return campo.name === nombre;
    }) || null;
}

/* Valida los campos del formulario antes de enviar por AJAX */
function validarFormulario(formulario, reglas = reglasClientes) {
    const errores = {};
    let primerCampo = null;

    Object.keys(reglas).forEach(function (nombre) {
        const campo = obtenerCampo(formulario, nombre);

        if (!campo || campo.disabled) return;

        const mensaje = validarValor(nombre, campo.value, reglas);

        mostrarCampo(campo, mensaje, true);

        if (mensaje) {
            errores[nombre] = mensaje;

            if (!primerCampo) primerCampo = campo;
        }
    });

    return {
        valido: Object.keys(errores).length === 0,
        errores: errores,
        primerCampo: primerCampo
    };
}

/* Presenta los errores por campo recibidos desde PHP */
function mostrarErrores(formulario, errores) {
    let primero = null;

    Object.entries(errores || {}).forEach(function ([nombre, mensaje]) {
        const campo = obtenerCampo(formulario, nombre);

        if (!campo) return;

        mostrarCampo(campo, String(mensaje));

        if (!primero && campo.type !== "hidden") primero = campo;
    });

    return primero;
}

/* Activa la validación al salir y al corregir los campos */
function activar(formulario, reglas = reglasClientes) {
    if (!formulario || formulario.dataset.validacionesActivas === "si") return;

    formulario.dataset.validacionesActivas = "si";

    formulario.addEventListener("focusout", function (evento) {
        const campo = evento.target;

        if (campo.disabled || !campo.name || !reglas[campo.name]) return;

        mostrarCampo(campo, validarValor(campo.name, campo.value, reglas), true);
    });

    function actualizarCampo(evento) {
        const campo = evento.target;

        if (campo.disabled || !campo.name || !reglas[campo.name]) return;
        if (!referenciaCampo(campo).nextElementSibling?.classList.contains("templo-validacion-feedback")) return;

        mostrarCampo(campo, validarValor(campo.name, campo.value, reglas), true);
    }

    formulario.addEventListener("input", actualizarCampo);
    formulario.addEventListener("change", actualizarCampo);
}

/* Comparte las funciones con los demás módulos */
export const Validaciones = Object.freeze({
    reglasClientes: reglasClientes,
    reglasContacto: reglasContacto,
    reglasProveedores: reglasProveedores,
    reglasInsumos: reglasInsumos,
    reglasServicios: reglasServicios,
    reglasEstadosAgendamiento: reglasEstadosAgendamiento,
    reglasTiposAgendamiento: reglasTiposAgendamiento,
    reglasUsuarios: reglasUsuarios,
    reglasRoles: reglasRoles,
    reglasAgendamientos: reglasAgendamientos,
    reglasBloqueosHorario: reglasBloqueosHorario,
    reglasEditarAgendamiento: reglasEditarAgendamiento,
    reglasEditarBloqueoHorario: reglasEditarBloqueoHorario,
    validarPrecio: validarPrecio,
    validarEntero: validarEntero,
    validarFecha: validarFecha,
    esEntero: esEntero,
    esMes: esMes,
    esFecha: esFecha,
    validarIdentificador: validarIdentificador,
    validarFechaAgenda: validarFechaAgenda,
    validarHora: validarHora,
    validarServicios: validarServicios,
    validarIntervaloHoras: validarIntervaloHoras,
    validarHorarioAgenda: validarHorarioAgenda,
    validarValor: validarValor,
    validarFormulario: validarFormulario,
    mostrarErrores: mostrarErrores,
    limpiarFormulario: limpiarFormulario,
    activar: activar,
    validarClave: validarClave
});