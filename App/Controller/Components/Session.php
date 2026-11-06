<?php

namespace App\Controller\Components;


/* ===================================================== */
/* GESTIÓN DE SESIONES */
/* ===================================================== */

final class Session
{
    /* Tiempo máximo de inactividad: 1800 segundos equivalen a 30 minutos */
    private const TIEMPO_MAXIMO_INACTIVIDAD = 1800;


    /* Impedir la creación de objetos; la clase utiliza métodos estáticos */
    private function __construct()
    {
    }


    /* ================================================= */
    /* INICIO DE LA SESIÓN */
    /* ================================================= */

    public static function iniciar(): void
    {
        /* Evitar iniciar nuevamente una sesión que ya esté activa */
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }


        /* Identificar si la conexión utiliza HTTPS */
        $conexionSegura = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';


        /* Utilizar únicamente cookies y rechazar identificadores no inicializados */
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) self::TIEMPO_MAXIMO_INACTIVIDAD);


        /* Establecer el nombre de la cookie del sistema */
        session_name('TEMPLO_ROSA_SESSID');


        /* Configurar los atributos de la cookie de sesión */
        session_set_cookie_params([
            /* Cookie de sesión, sin una duración persistente configurada */
            'lifetime' => 0,

            /* Permitir su uso en las rutas del sitio */
            'path' => '/',

            'domain' => '',

            /* Restringir su envío a HTTPS cuando la conexión sea segura */
            'secure' => $conexionSegura,

            /* Impedir la lectura de la cookie mediante JavaScript */
            'httponly' => true,

            /* Limitar el envío de la cookie entre sitios */
            'samesite' => 'Lax'
        ]);


        /* Crear la sesión o recuperar la identificada por la cookie */
        session_start();
    }


    /* ================================================= */
    /* COMPROBACIÓN DE AUTENTICACIÓN */
    /* ================================================= */

    public static function estaAutenticada(): bool
    {
        /* Comprobar los identificadores del usuario y del rol */
        return isset(
            $_SESSION['usuario']['id_usuario'],
            $_SESSION['usuario']['id_rol']
        );
    }


    /* ================================================= */
    /* REGISTRO DEL USUARIO AUTENTICADO */
    /* ================================================= */

    public static function registrarUsuario(array $usuario): void
    {
        /* Renovar el identificador después de autenticar al usuario */
        session_regenerate_id(true);


        /* Guardar los datos recibidos; el controlador debe excluir la contraseña */
        $_SESSION['usuario'] = $usuario;


        /* Registrar el momento de la última actividad */
        $_SESSION['ultima_actividad'] = time();
    }


    /* ================================================= */
    /* CONTROL DE INACTIVIDAD */
    /* ================================================= */

    public static function validarVigencia(): bool
    {
        if (!self::estaAutenticada()) {
            return false;
        }

        $ultimaActividad = (int) ($_SESSION['ultima_actividad'] ?? time());


        /* Cerrar la sesión cuando se supere el tiempo máximo de inactividad */
        if (time() - $ultimaActividad > self::TIEMPO_MAXIMO_INACTIVIDAD) {
            self::cerrar();

            return false;
        }


        /* Actualizar el momento de actividad en cada solicitud válida */
        $_SESSION['ultima_actividad'] = time();

        return true;
    }


    /* ================================================= */
    /* DATOS DEL USUARIO AUTENTICADO */
    /* ================================================= */

    public static function obtenerUsuario(): ?array
    {
        if (!self::estaAutenticada()) {
            return null;
        }

        return $_SESSION['usuario'];
    }


    /* ================================================= */
    /* CIERRE DE LA SESIÓN */
    /* ================================================= */

    public static function cerrar(): void
    {
        /* Eliminar las variables almacenadas en la sesión */
        $_SESSION = [];


        /* Expirar la cookie utilizando sus atributos originales */
        if (ini_get('session.use_cookies')) {
            $parametros = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $parametros['path'],
                'domain' => $parametros['domain'],
                'secure' => $parametros['secure'],
                'httponly' => $parametros['httponly'],
                'samesite' => $parametros['samesite'] ?? 'Lax'
            ]);
        }


        /* Destruir los datos de la sesión almacenados en el servidor */
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}