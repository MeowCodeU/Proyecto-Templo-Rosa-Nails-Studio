<?php

namespace App\Components\RestriccionesRol;

use App\Components\Session;


/* ===================================================== */
/* RESTRICCIONES DE ACCESO POR ROL */
/* ===================================================== */

/* Rutas permitidas según el identificador del rol */
const RUTAS_POR_ROL = [

    /* Administradora del Negocio */
    1 => [
        'inicio',
        'clientes',
        'agendamiento',
        'registroServicio',
        'insumos',
        'proveedores',
        'reportes',
        'manual',
        'perfil'
    ],

    /* Especialista en Manicura */
    2 => [
        'inicio',
        'clientes',
        'agendamiento',
        'registroServicio',
        'manual',
        'perfil'
    ],

    /* Administrador del Sistema */
    3 => [
        'inicio',
        'seguridad',
        'configuracion',
        'manual',
        'perfil'
    ]
];


/* ================================================= */
/* OBTENER LAS RUTAS PERMITIDAS */
/* ================================================= */

function obtenerRutasPermitidas(): array
{
    /* Recuperar el rol del usuario autenticado */
    $usuario = Session\obtenerUsuario();

    if ($usuario === null) {
        return [];
    }

    $idRol = (int) ($usuario['id_rol'] ?? 0);

    /* Los roles no definidos carecen de rutas autorizadas */
    return RUTAS_POR_ROL[$idRol] ?? [];
}


/* ================================================= */
/* COMPROBAR EL ACCESO A UNA RUTA */
/* ================================================= */

function puedeAcceder(string $ruta): bool
{
    /* Permitir únicamente las rutas asignadas al rol */
    return in_array($ruta, obtenerRutasPermitidas(), true);
}