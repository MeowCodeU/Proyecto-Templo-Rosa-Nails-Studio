<?php

namespace App\Components;

use PHPMailer\PHPMailer\PHPMailer;
use Throwable;


/* ===================================================== */
/* ENVÍO DE CORREOS ELECTRÓNICOS */
/* ===================================================== */

class Correo
{
    /* Configuración privada y resultado del último envío */
    private array $configuracion = [];
    private string $resultado = 'pendiente';


    /* ================================================= */
    /* CARGAR LA CONFIGURACIÓN */
    /* ================================================= */

    public function __construct(?array $configuracion = null)
    {
        if ($configuracion === null) {
            $ruta = __DIR__ . '/../Config/Correo.php';

            try {
                $configuracion = is_file($ruta) ? require $ruta : [];

            } catch (Throwable $error) {
                error_log('Correo: no fue posible cargar la configuración privada.');
                $configuracion = [];
            }
        }

        $this->configuracion = is_array($configuracion) ? $configuracion : [];
    }


    /* ================================================= */
    /* CONSULTAR LA DISPONIBILIDAD DEL CORREO */
    /* ================================================= */

    public function estaDisponible(): bool
    {
        if (($this->configuracion['habilitado'] ?? false) !== true) {
            $this->resultado = 'deshabilitado';
            return false;
        }

        /* Comprobar los datos técnicos antes de intentar una conexión */
        foreach (['servidor', 'cifrado', 'usuario', 'clave_aplicacion', 'nombre_remitente'] as $campo) {
            $valor = $this->configuracion[$campo] ?? null;

            if (!is_string($valor) || trim($valor) === '' ||
                str_contains($valor, "\r") || str_contains($valor, "\n") || str_contains($valor, "\0")) {
                $this->resultado = 'configuracion_incompleta';
                return false;
            }
        }

        $usuario = trim($this->configuracion['usuario']);
        $puerto = $this->configuracion['puerto'] ?? null;
        $espera = $this->configuracion['tiempo_espera'] ?? null;
        $cifrado = $this->configuracion['cifrado'];

        if (!filter_var($usuario, FILTER_VALIDATE_EMAIL) ||
            !is_int($puerto) || !is_int($espera) || $espera < 1 || $espera > 30 ||
            !(($cifrado === 'tls' && $puerto === 587) || ($cifrado === 'ssl' && $puerto === 465))) {
            $this->resultado = 'configuracion_incompleta';
            return false;
        }

        $this->resultado = 'preparado';
        return true;
    }


    public function obtenerResultado(): string
    {
        return $this->resultado;
    }


    /* ================================================= */
    /* ENVIAR EL CÓDIGO DE RECUPERACIÓN */
    /* ================================================= */

    public function enviarCodigoRecuperacion(
        string $destinatario,
        string $nombre,
        string $codigo,
        int $minutosVigencia = 10
    ): bool {
        $saludo = $this->escapar(trim($nombre) ?: 'Usuario');
        $codigoVisible = $this->escapar($codigo);

        $contenido = '<p>Hola, ' . $saludo . '.</p>' .
            '<p>Recibimos una solicitud para recuperar el acceso a tu cuenta de Templo Rosa.</p>' .
            '<p>Escribe este código en el formulario de recuperación:</p>' .
            '<div style="margin:24px 0;padding:18px;background:#f8e8e6;border-radius:12px;' .
            'color:#6b4b3e;text-align:center;font-size:30px;font-weight:bold;letter-spacing:6px;">' .
            $codigoVisible . '</div>' .
            '<p>El código tiene una vigencia de <strong>' . $minutosVigencia . ' minutos</strong> ' .
            'y solo puede utilizarse una vez.</p>' .
            '<p>No lo compartas. Si no solicitaste este cambio, puedes ignorar este mensaje.</p>';

        $texto = "Hola, " . (trim($nombre) ?: 'Usuario') . ".\n\n" .
            "Recibimos una solicitud para recuperar tu cuenta de Templo Rosa.\n" .
            "Código de recuperación: " . $codigo . "\n" .
            "Vigencia: " . $minutosVigencia . " minutos. Solo puede utilizarse una vez.\n\n" .
            "No lo compartas. Si no solicitaste este cambio, ignora este mensaje.";

        return $this->enviar(
            $destinatario,
            $nombre,
            'Código de recuperación — Templo Rosa',
            $this->crearContenido('Recupera tu contraseña', $contenido),
            $texto
        );
    }


    /* ================================================= */
    /* ENVIAR LA NOTIFICACIÓN DE CUENTA CREADA */
    /* ================================================= */

    public function enviarBienvenida(string $destinatario, string $nombre): bool
    {
        $saludo = $this->escapar(trim($nombre) ?: 'Usuario');

        $contenido = '<p>Hola, ' . $saludo . '.</p>' .
            '<p>Tu cuenta de <strong>Templo Rosa Nails Studio</strong> fue creada correctamente.</p>' .
            '<p>Ya puedes iniciar sesión con tu cédula y la contraseña asignada durante el registro.</p>' .
            '<p>Desde tu perfil podrás consultar y actualizar tus datos.</p>' .
            '<p>Si no reconoces la creación de esta cuenta, comunícate con el Administrador del Sistema.</p>';

        $texto = "Hola, " . (trim($nombre) ?: 'Usuario') . ".\n\n" .
            "Tu cuenta de Templo Rosa Nails Studio fue creada correctamente.\n" .
            "Puedes iniciar sesión con tu cédula y la contraseña asignada durante el registro.\n" .
            "Desde tu perfil podrás consultar y actualizar tus datos.\n\n" .
            "Si no reconoces esta cuenta, comunícate con el Administrador del Sistema.";

        return $this->enviar(
            $destinatario,
            $nombre,
            'Tu cuenta fue creada — Templo Rosa',
            $this->crearContenido('Te damos la bienvenida', $contenido),
            $texto
        );
    }


    /* ================================================= */
    /* CONFIGURAR PHPMailer */
    /* ================================================= */

    protected function crearTransporte(): PHPMailer
    {
        $correo = new PHPMailer(true);

        $correo->isSMTP();
        $correo->SMTPDebug = 0;
        $correo->SMTPAuth = true;
        $correo->Host = trim($this->configuracion['servidor']);
        $correo->Port = $this->configuracion['puerto'];
        $correo->SMTPSecure = $this->configuracion['cifrado'] === 'tls'
            ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;

        $correo->Username = trim($this->configuracion['usuario']);

        /* Admitir la contraseña de aplicación copiada con espacios entre grupos */
        $correo->Password = str_replace(' ', '', $this->configuracion['clave_aplicacion']);

        $correo->Timeout = $this->configuracion['tiempo_espera'];
        $correo->CharSet = PHPMailer::CHARSET_UTF8;
        $correo->Encoding = PHPMailer::ENCODING_BASE64;

        $correo->setFrom($correo->Username, trim($this->configuracion['nombre_remitente']));
        $correo->isHTML(true);

        return $correo;
    }


    /* ================================================= */
    /* EJECUTAR EL ENVÍO */
    /* ================================================= */

    private function enviar(
        string $destinatario,
        string $nombre,
        string $asunto,
        string $contenido,
        string $texto
    ): bool {
        if (!$this->estaDisponible()) return false;

        $destinatario = trim($destinatario);

        if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL) ||
            str_contains($nombre, "\r") || str_contains($nombre, "\n") || str_contains($nombre, "\0")) {
            $this->resultado = 'destinatario_invalido';
            return false;
        }

        try {
            /* Preparar un mensaje independiente para cada operación */
            $correo = $this->crearTransporte();
            $correo->addAddress($destinatario, trim($nombre));
            $correo->Subject = $asunto;
            $correo->Body = $contenido;
            $correo->AltBody = $texto;

            if (!$correo->send()) {
                $this->resultado = 'envio_fallido';
                error_log('Correo: el servidor SMTP no confirmó el envío.');
                return false;
            }

            $this->resultado = 'enviado';
            return true;

        } catch (Throwable $error) {
            $this->resultado = 'envio_fallido';

            /* Conservar el motivo técnico sin registrar la contraseña de aplicación */
            $clave = $this->configuracion['clave_aplicacion'];
            $detalle = str_replace([$clave, str_replace(' ', '', $clave)], '[omitida]', $error->getMessage());
            $detalle = str_replace(["\r", "\n"], ' ', $detalle);

            error_log('Correo SMTP: ' . $detalle);
            return false;
        }
    }


    /* ================================================= */
    /* FORMATO DE LOS MENSAJES */
    /* ================================================= */

    private function escapar(string $texto): string
    {
        return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }


    private function crearContenido(string $titulo, string $contenido): string
    {
        return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"></head>' .
            '<body style="margin:0;padding:24px;background:#fff8f3;color:#6b4b3e;' .
            'font-family:Arial,sans-serif;font-size:15px;line-height:1.6;">' .
            '<div style="max-width:560px;margin:0 auto;padding:28px;background:#fffdf9;' .
            'border:1px solid #ead4cc;border-radius:18px;">' .
            '<p style="margin:0;color:#a78c58;font-family:Georgia,serif;font-size:25px;' .
            'font-weight:bold;">Templo Rosa</p>' .
            '<p style="margin:0 0 24px;font-size:12px;letter-spacing:3px;">NAILS STUDIO</p>' .
            '<h1 style="font-size:21px;color:#6b4b3e;">' . $this->escapar($titulo) . '</h1>' .
            $contenido .
            '<p style="margin:24px 0 0;padding-top:16px;border-top:1px solid #ead4cc;' .
            'font-size:12px;">Este es un mensaje automático de Templo Rosa Nails Studio.</p>' .
            '</div></body></html>';
    }
}