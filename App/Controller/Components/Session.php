<?php

namespace App\Controller\Components;

final class Session
{
    /* Tiempo máximo permitido sin actividad.
    1800 segundos equivalen a 30 minutos.*/
    private const TIEMPO_MAXIMO_INACTIVIDAD = 1800;


    /*Impide crear objetos de esta clase.
    Sus métodos se utilizarán de forma estática.*/
    private function __construct()
    {
    }


    /*Inicia o recupera la sesión del sistema.*/
    public static function iniciar(): void
    {
        /*Evita iniciar nuevamente una sesión que ya se encuentra activa.*/
        if (
            session_status()
            === PHP_SESSION_ACTIVE
        ) {
            return;
        }


        /*Comprueba si la aplicación está funcionando mediante HTTPS.
        En localhost normalmente será false.*/
        $conexionSegura =
            !empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off';


        /*Configuraciones de seguridad se utilizan únicamente cookies 
         para transportar el identificador.*/
        ini_set(
            'session.use_strict_mode',
            '1'
        );

        ini_set(
            'session.use_only_cookies',
            '1'
        );

        ini_set(
            'session.gc_maxlifetime',
            (string) self::TIEMPO_MAXIMO_INACTIVIDAD
        );


        /*Nombre identificador de la cookie utilizada exclusivamente por Templo Rosa.*/
        session_name(
            'TEMPLO_ROSA_SESSID'
        );


        /*Configuración de la cookie de sesión.*/
        session_set_cookie_params(
            [
                /*Valor cero: la cookie desaparece cuando se cierra el navegador.*/
                'lifetime' => 0,

                /*La sesión será accesible desde todo el proyecto.*/
                'path' => '/',

                'domain' => '',

                /*En producción con HTTPS la cookie viajará solamente mediante conexiones seguras. */
                'secure' => $conexionSegura,

                /*Impide acceder a la cookie mediante JavaScript.*/
                'httponly' => true,

                /*Reduce el envío de la cookie en solicitudes provenientes de otros sitios.*/
                'samesite' => 'Lax'
            ]
        );


        /*Crea una sesión nueva o recuperala sesión identificada por la cookie.*/
        session_start();
    }


    /*Comprueba si existe un usuario correctamente autenticado.*/
    public static function estaAutenticada(): bool
    {
        return isset(
            $_SESSION['usuario']['id_usuario'],
            $_SESSION['usuario']['id_rol']
        );
    }


    /*Registra al usuario autenticado dentro de la sesión. */
    public static function registrarUsuario(
        array $usuario
    ): void {

        /*Genera un nuevo identificador después de autenticar al usuario.*/
        session_regenerate_id(true); 


        /*Guarda los datos permitidos, el controlador debe haber eliminado previamente la clave.*/
        $_SESSION['usuario'] = $usuario;


        /*
         * Guarda el momento de la última
         * actividad realizada.
         */
        $_SESSION['ultima_actividad'] =
            time();
    }


    /*Comprueba que la sesión no haya expirado por inactividad.*/
    public static function validarVigencia(): bool
    {
        if (!self::estaAutenticada()) {
            return false;
        }


        $ultimaActividad = (int) (
            $_SESSION['ultima_actividad']
            ?? time()
        );


        /*Si transcurrieron más de 30 minutos sin actividad, se cierra la sesión.*/
        if (
            time() - $ultimaActividad
            > self::TIEMPO_MAXIMO_INACTIVIDAD
        ) {
            self::cerrar();

            return false;
        }


        /* Actualiza el momento de actividad en cada solicitud válida.*/
        $_SESSION['ultima_actividad'] =
            time();


        return true;
    }


    /*Devuelve los datos del usuario autenticado.*/
    public static function obtenerUsuario(): ?array
    {
        if (!self::estaAutenticada()) {
            return null;
        }

        return $_SESSION['usuario'];
    }


    /*Finaliza completamente la sesión.*/
    public static function cerrar(): void
    {
        /*Elimina las variables almacenadas.*/
        $_SESSION = [];


        /*Elimina la cookie identificadora del navegador.*/
        if (ini_get('session.use_cookies')) {

            $parametros =
                session_get_cookie_params();


            setcookie(
                session_name(),
                '',
                [
                    'expires' =>
                        time() - 42000,

                    'path' =>
                        $parametros['path'],

                    'domain' =>
                        $parametros['domain'],

                    'secure' =>
                        $parametros['secure'],

                    'httponly' =>
                        $parametros['httponly'],

                    'samesite' =>
                        $parametros['samesite']
                        ?? 'Lax'
                ]
            );
        }


        /*Destruye los datos de la sesión almacenados en el servidor.*/
        if (
            session_status()
            === PHP_SESSION_ACTIVE
        ) {
            session_destroy();
        }
    }
}