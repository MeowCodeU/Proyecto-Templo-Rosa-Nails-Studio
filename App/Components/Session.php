<?php

namespace App\Components\Session;

use App\Model\LoginM;


/* ===================================================== */
/* GESTIÓN DE LA SESIÓN */
/* ===================================================== */

/* Tiempo máximo sin actividad: 30 minutos */
const TIEMPO_MAXIMO_INACTIVIDAD = 1800;


/* ================================================= */
/* INICIAR LA SESIÓN */
/* ================================================= */

function iniciar(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    /* Activar la cookie segura cuando se utiliza HTTPS */
    $conexionSegura = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    ini_set('session.gc_maxlifetime', (string) TIEMPO_MAXIMO_INACTIVIDAD);

    session_name('TEMPLO_ROSA_SESSID');

    /* Proteger el identificador almacenado en el navegador */
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $conexionSegura,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}


/* ================================================= */
/* COMPROBAR LA AUTENTICACIÓN */
/* ================================================= */

function estaAutenticada(): bool
{
    return isset($_SESSION['usuario']['id_usuario'], $_SESSION['usuario']['id_rol']);
}


/* ================================================= */
/* REGISTRAR AL USUARIO AUTENTICADO */
/* ================================================= */

function registrarUsuario(array $usuario): void
{
    session_regenerate_id(true);

    /* Evitar que la contraseña quede almacenada en la sesión */
    unset($usuario['clave']);

    $_SESSION['usuario'] = $usuario;
    $_SESSION['ultima_actividad'] = time();
}


/* ================================================= */
/* VALIDAR LA VIGENCIA DE LA SESIÓN */
/* ================================================= */

function validarVigencia(): bool
{
    if (!estaAutenticada()) {
        return false;
    }

    $ultimaActividad = (int) ($_SESSION['ultima_actividad'] ?? time());

    /* Cerrar la sesión cuando vence el tiempo de inactividad */
    if (time() - $ultimaActividad > TIEMPO_MAXIMO_INACTIVIDAD) {
        cerrarConMensaje('La sesión expiró por inactividad. Inicie sesión nuevamente.');

        return false;
    }

    /* Consultar el estado actual de la cuenta y de su rol */
    $modeloLogin = new LoginM();

    $cuenta = $modeloLogin->consultarEstadoCuenta((int) $_SESSION['usuario']['id_usuario']);

    if (
        !$cuenta ||
        ($cuenta['estado_usuario'] ?? '') !== 'ACTIVO'
    ) {
        cerrarConMensaje('La cuenta ya no se encuentra activa. Inicie sesión con una cuenta habilitada.');

        return false;
    }

    if (($cuenta['estado_rol'] ?? '') !== 'ACTIVO') {
        cerrarConMensaje('El rol asociado a su cuenta ya no se encuentra activo.');

        return false;
    }

    /* Mantener actualizados los datos del rol en la sesión */
    $_SESSION['usuario']['id_rol'] = (int) $cuenta['id_rol'];
    $_SESSION['usuario']['nombre_rol'] = $cuenta['nombre_rol'];
    $_SESSION['usuario']['estado_usuario'] = $cuenta['estado_usuario'];
    $_SESSION['usuario']['estado_rol'] = $cuenta['estado_rol'];

    $_SESSION['ultima_actividad'] = time();

    return true;
}


/* ================================================= */
/* OBTENER EL USUARIO AUTENTICADO */
/* ================================================= */

function obtenerUsuario(): ?array
{
    if (!estaAutenticada()) {
        return null;
    }

    return $_SESSION['usuario'];
}


/* ================================================= */
/* CERRAR LA SESIÓN CON UN MENSAJE */
/* ================================================= */

function cerrarConMensaje(string $mensaje): void
{
    cerrar();

    /* Crear una sesión anónima para conservar el aviso del login */
    session_id('');

    iniciar();

    $_SESSION['mensaje_login'] = $mensaje;
}


/* ================================================= */
/* CERRAR LA SESIÓN */
/* ================================================= */

function cerrar(): void
{
    $_SESSION = [];

    /* Retirar la cookie identificadora del navegador */
    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            [
                'expires' => time() - 42000,
                'path' => $parametros['path'],
                'domain' => $parametros['domain'],
                'secure' => $parametros['secure'],
                'httponly' => $parametros['httponly'],
                'samesite' => $parametros['samesite'] ?? 'Lax'
            ]
        );
    }

    /* Destruir los datos de la sesión anterior en el servidor */
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}