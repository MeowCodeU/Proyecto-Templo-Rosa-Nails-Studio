/* ===================================================== */
/* EXPRESIONES REGULARES COMPARTIDAS - TEMPLO ROSA */
/* ===================================================== */

/* Comprobar el formato de los datos ingresados al sistema */
export const Regex = Object.freeze({
    cedula: /^[0-9]{6,20}$/,
    nombre: /^[\p{L}\p{M}][\p{L}\p{M} .'’-]*$/u,
    apellido: /^[\p{L}\p{M}][\p{L}\p{M} .'’-]*$/u,
    ciudad: /^[\p{L}\p{M}][\p{L}\p{M} .'’-]*$/u,
    telefono: /^[+0-9() -]+$/,
    correo: /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/,
    identificador: /^\+?[1-9][0-9]*$/,
    entero: /^[0-9]+$/,
    precio: /^([0-9]*)(?:\.([0-9]{1,2}))?$/,
    cerosIniciales: /^0+/,
    digitos: /[0-9]/g,
    fecha: /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/,
    mes: /^[1-9][0-9]{3}-(0[1-9]|1[0-2])$/,
    hora: /^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/,
    clave: /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9\s])\S{8,}$/
});