<?php

namespace App\Model;

use App\Config\Database;
use App\Components\Correo;
use App\Traits\ValidacionUsuarioTrait;
use PDO;
use Throwable;


/* ===================================================== */
/* MODELO DE ACCESO AL SISTEMA */
/* ===================================================== */

class LoginM extends Database
{
    use ValidacionUsuarioTrait;


    /* Clave universal almacenada como hash */
    private const HASH_CLAVE_UNIVERSAL = '$2y$12$yhjAIohVHg.buqNIDwmi0.ADc//0D6x4xF5bqphiK/U9qaFogkVi6';

    /* Límites de la recuperación de contraseña */
    private const VIGENCIA_CODIGO = 600;
    private const INTERVALO_REENVIO = 60;
    private const VENTANA_RECUPERACION = 900;
    private const MAXIMO_ENVIOS = 3;
    private const MAXIMO_INTENTOS = 5;


    /* ================================================= */
    /* CONEXIÓN Y RESULTADO DEL ACCESO */
    /* ================================================= */

    private PDO $conexionMD;
    private int $estadoRespuesta = 422;
    private string $mensajeAcceso = '';
    private string $codigoResultado = '';
    private ?Correo $correoElectronico;


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct(?PDO $conexion = null, ?Correo $correoElectronico = null)
    {
        $this->conexionMD = $conexion ?? $this->getConnection();
        $this->correoElectronico = $correoElectronico;
    }


    /* ================================================= */
    /* CONSULTAR EL RESULTADO DEL ACCESO */
    /* ================================================= */

    public function obtenerEstadoRespuesta(): int
    {
        return $this->estadoRespuesta;
    }


    public function obtenerMensajeAcceso(): string
    {
        return $this->mensajeAcceso;
    }


    public function obtenerCodigoResultado(): string
    {
        return $this->codigoResultado;
    }


    /* ================================================= */
    /* AUTENTICAR AL USUARIO */
    /* ================================================= */

    public function autenticar(mixed $cedula, mixed $clave): ?array
    {
        $this->estadoRespuesta = 422;
        $this->mensajeAcceso = 'La cédula o la clave suministrada no es válida.';

        $datos = $this->validarDatosAcceso($cedula, $clave);

        if ($datos === null) {
            return null;
        }

        $usuario = $this->buscarPorCedula($datos['cedula']);


        /* Verificar la contraseña con el hash almacenado */
        if (!$usuario || !password_verify($datos['clave'], (string) ($usuario['clave'] ?? ''))) {
            $this->estadoRespuesta = 401;
            return null;
        }


        /* Comprobar el estado vigente de la cuenta y de su rol */
        if (
            ($usuario['estado_usuario'] ?? '') !== 'ACTIVO' ||
            ($usuario['estado_rol'] ?? '') !== 'ACTIVO'
        ) {
            $this->estadoRespuesta = 403;
            $this->mensajeAcceso = 'La cuenta o el rol asociado no se encuentra activo.';

            return null;
        }


        /* Excluir la contraseña antes de entregar los datos de la sesión */
        unset($usuario['clave']);

        $this->estadoRespuesta = 200;
        $this->mensajeAcceso = '';

        return $usuario;
    }


    /* ================================================= */
    /* COMPROBAR LA CLAVE UNIVERSAL */
    /* ================================================= */

    public function comprobarClaveUniversal(mixed $claveUniversal): bool
    {
        $this->estadoRespuesta = 422;
        $this->mensajeAcceso = 'La clave universal ingresada no es válida.';
        $clave = $this->validarDatosClaveUniversal($claveUniversal);

        if ($clave === null) {
            $this->mensajeAcceso = (string) reset($this->erroresValidacion);
            return false;
        }

        if (!password_verify($clave, self::HASH_CLAVE_UNIVERSAL)) {
            $this->erroresValidacion['clave_universal'] = $this->mensajeAcceso;
            return false;
        }

        $this->estadoRespuesta = 200;
        $this->mensajeAcceso = '';
        return true;
    }


    /* ================================================= */
    /* BUSCAR USUARIO POR CÉDULA */
    /* ================================================= */

    public function buscarPorCedula(mixed $cedula): ?array
    {
        $cedula = $this->validarCedulaAcceso($cedula);

        if ($cedula === null) {
            return null;
        }


        /* Obtener la cuenta, los datos personales y el rol asociado */
        $sql = "SELECT
                    u.id_usuario,
                    u.id_persona,
                    u.id_rol,
                    u.nickname,
                    u.foto_perfil,
                    u.clave,
                    u.estado_usuario,

                    p.cedula,
                    p.nombre,
                    p.apellido,
                    p.telefono,
                    p.correo,
                    p.direccion,
                    p.ciudad,

                    r.nombre_rol,
                    r.estado_rol

                FROM usuarios u

                INNER JOIN personas p
                    ON p.id_persona = u.id_persona

                INNER JOIN roles r
                    ON r.id_rol = u.id_rol

                WHERE p.cedula = :cedula

                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':cedula', $cedula, PDO::PARAM_STR);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }


    /* ================================================= */
    /* CONSULTAR EL ESTADO ACTUAL DE LA CUENTA */
    /* ================================================= */

    public function consultarEstadoCuenta(int $idUsuario): ?array
    {
        if ($idUsuario <= 0) {
            return null;
        }


        /* Recuperar el estado vigente sin consultar la contraseña */
        $sql = "SELECT
                    u.id_usuario,
                    u.id_rol,
                    u.estado_usuario,
                    r.nombre_rol,
                    r.estado_rol

                FROM usuarios u

                INNER JOIN roles r
                    ON r.id_rol = u.id_rol

                WHERE u.id_usuario = :id_usuario

                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

        return $cuenta ?: null;
    }


    /* ================================================= */
    /* SOLICITAR EL CÓDIGO DE RECUPERACIÓN */
    /* ================================================= */

    public function solicitarRecuperacion(
        mixed $cedula,
        mixed $correo,
        mixed $solicitudActual = null
    ): ?string {
        $this->estadoRespuesta = 422;
        $this->mensajeAcceso = 'Revise los campos señalados.';
        $this->codigoResultado = 'datos_invalidos';

        $datos = $this->validarSolicitudRecuperacion($cedula, $correo);

        if ($datos === null) return null;

        $correoElectronico = $this->correoElectronico ??= new Correo();

        if (!$correoElectronico->estaDisponible()) {
            $this->estadoRespuesta = 503;
            $this->mensajeAcceso = 'El servicio de correo no está disponible. Intente más tarde o contacte al Administrador del Sistema.';
            $this->codigoResultado = 'correo_no_disponible';
            return null;
        }

        /* Generar el código y un identificador privado para esta solicitud */
        $solicitud = bin2hex(random_bytes(32));
        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $codigoHash = password_hash($codigo, PASSWORD_DEFAULT);
        $ahora = $this->ahoraRecuperacion();
        $fechaActual = gmdate('Y-m-d H:i:s', $ahora);
        $solicitudHash = hash('sha256', $solicitud);

        try {
            $this->conexionMD->beginTransaction();

            /* Bloquear primero la cuenta para coordinar solicitudes simultáneas */
            $cuenta = $this->bloquearCuentaRecuperacion($datos['cedula']);

            if (!$cuenta || !$this->cuentaRecuperable($cuenta) ||
                strcasecmp(trim((string) $cuenta['correo']), $datos['correo']) !== 0) {
                $this->conexionMD->commit();
                return $this->confirmarSolicitudRecuperacion($solicitud);
            }

            $idUsuario = (int) $cuenta['id_usuario'];
            $recuperacion = $this->bloquearRecuperacion($idUsuario);

            $mismaVentana = $recuperacion &&
                $recuperacion['ventana_inicio'] > gmdate('Y-m-d H:i:s', $ahora - self::VENTANA_RECUPERACION);

            $envios = $mismaVentana ? (int) $recuperacion['envios'] : 0;
            $intentos = $mismaVentana ? (int) $recuperacion['intentos'] : 0;

            $esperandoReenvio = $recuperacion &&
                $recuperacion['solicitado_en'] > gmdate('Y-m-d H:i:s', $ahora - self::INTERVALO_REENVIO);

            /* Mantener los límites aunque se solicite otro código desde otro navegador */
            if ($esperandoReenvio || $envios >= self::MAXIMO_ENVIOS || $intentos >= self::MAXIMO_INTENTOS) {
                if (is_string($solicitudActual) && strlen($solicitudActual) === 64 &&
                    hash_equals($recuperacion['solicitud_hash'], hash('sha256', $solicitudActual))) {
                    $solicitud = $solicitudActual;
                }

                $this->conexionMD->commit();
                return $this->confirmarSolicitudRecuperacion($solicitud);
            }

            $valores = [
                'id_usuario' => $idUsuario,
                'solicitud_hash' => $solicitudHash,
                'codigo_hash' => $codigoHash,
                'correo_destino' => trim((string) $cuenta['correo']),
                'clave_vigente_hash' => hash('sha256', (string) $cuenta['clave']),
                'solicitado_en' => $fechaActual,
                'vence_en' => gmdate('Y-m-d H:i:s', $ahora + self::VIGENCIA_CODIGO),
                'ventana_inicio' => $mismaVentana ? $recuperacion['ventana_inicio'] : $fechaActual,
                'envios' => $envios + 1,
                'intentos' => $intentos
            ];

            if ($recuperacion) {
                $sql = "UPDATE recuperaciones_clave
                        SET solicitud_hash = :solicitud_hash,
                            codigo_hash = :codigo_hash,
                            correo_destino = :correo_destino,
                            clave_vigente_hash = :clave_vigente_hash,
                            solicitado_en = :solicitado_en,
                            vence_en = :vence_en,
                            ventana_inicio = :ventana_inicio,
                            envios = :envios,
                            intentos = :intentos,
                            estado = 'PENDIENTE'
                        WHERE id_usuario = :id_usuario";

            } else {
                $sql = "INSERT INTO recuperaciones_clave (
                            id_usuario, solicitud_hash, codigo_hash,
                            correo_destino, clave_vigente_hash,
                            solicitado_en, vence_en, ventana_inicio,
                            envios, intentos, estado
                        ) VALUES (
                            :id_usuario, :solicitud_hash, :codigo_hash,
                            :correo_destino, :clave_vigente_hash,
                            :solicitado_en, :vence_en, :ventana_inicio,
                            :envios, :intentos, 'PENDIENTE'
                        )";
            }

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->execute($valores);

            /* Liberar los bloqueos antes de conectarse al servidor de correo */
            $this->conexionMD->commit();

        } catch (Throwable $error) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $error;
        }

        $enviado = $correoElectronico->enviarCodigoRecuperacion(
            $valores['correo_destino'],
            trim($cuenta['nombre'] . ' ' . $cuenta['apellido']),
            $codigo,
            intdiv(self::VIGENCIA_CODIGO, 60)
        );

        /* Actualizar únicamente la solicitud que originó este envío */
        $sql = "UPDATE recuperaciones_clave
                SET estado = :estado
                WHERE id_usuario = :id_usuario
                    AND solicitud_hash = :solicitud_hash
                    AND estado = 'PENDIENTE'";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute([
            'estado' => $enviado ? 'ENVIADO' : 'FALLIDO',
            'id_usuario' => $idUsuario,
            'solicitud_hash' => $solicitudHash
        ]);

        return $this->confirmarSolicitudRecuperacion($solicitud);
    }


    /* ================================================= */
    /* RECUPERAR LA CONTRASEÑA */
    /* ================================================= */

    public function recuperarClave(
        mixed $solicitud,
        mixed $codigo,
        mixed $nuevaClave,
        mixed $confirmarClave
    ): bool {
        $this->estadoRespuesta = 422;
        $this->mensajeAcceso = 'Revise los campos señalados.';
        $this->codigoResultado = 'datos_invalidos';

        $datos = $this->validarRestablecimientoRecuperacion($codigo, $nuevaClave, $confirmarClave);

        if ($datos === null) return false;

        /* El controlador obtiene este identificador de la sesión, no del formulario */
        if (!is_string($solicitud) || strlen($solicitud) !== 64 || !ctype_xdigit($solicitud)) {
            return $this->rechazarCodigoRecuperacion();
        }

        $solicitudHash = hash('sha256', $solicitud);

        $sql = "SELECT id_usuario
                FROM recuperaciones_clave
                WHERE solicitud_hash = :solicitud_hash
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute(['solicitud_hash' => $solicitudHash]);
        $idUsuario = $stmt->fetchColumn();

        if ($idUsuario === false) return $this->rechazarCodigoRecuperacion();

        try {
            $this->conexionMD->beginTransaction();

            /* Conservar el mismo orden de bloqueo utilizado al solicitar el código */
            $cuenta = $this->bloquearCuentaRecuperacion((int) $idUsuario, true);
            $recuperacion = $this->bloquearRecuperacion((int) $idUsuario);
            $ahora = gmdate('Y-m-d H:i:s', $this->ahoraRecuperacion());

            if (!$cuenta || !$recuperacion || !$this->cuentaRecuperable($cuenta) ||
                !hash_equals($recuperacion['solicitud_hash'], $solicitudHash) ||
                $recuperacion['estado'] !== 'ENVIADO' ||
                $recuperacion['vence_en'] <= $ahora ||
                (int) $recuperacion['intentos'] >= self::MAXIMO_INTENTOS ||
                strcasecmp(trim((string) $cuenta['correo']), $recuperacion['correo_destino']) !== 0 ||
                !hash_equals($recuperacion['clave_vigente_hash'], hash('sha256', (string) $cuenta['clave']))) {
                $this->conexionMD->commit();
                return $this->rechazarCodigoRecuperacion();
            }

            if (!password_verify($datos['codigo_recuperacion'], $recuperacion['codigo_hash'])) {
                $sql = "UPDATE recuperaciones_clave
                        SET intentos = intentos + 1
                        WHERE id_usuario = :id_usuario";

                $stmt = $this->conexionMD->prepare($sql);
                $stmt->execute(['id_usuario' => (int) $idUsuario]);

                /* Confirmar el intento fallido para que no se pierda al responder */
                $this->conexionMD->commit();
                return $this->rechazarCodigoRecuperacion();
            }

            /* Guardar el hash de la nueva contraseña sin modificar la cuenta antes */
            $claveHash = password_hash($datos['nueva_clave'], PASSWORD_DEFAULT);

            $sql = "UPDATE usuarios
                    SET clave = :clave
                    WHERE id_usuario = :id_usuario";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->execute(['clave' => $claveHash, 'id_usuario' => (int) $idUsuario]);

            /* Consumir el código en la misma transacción del cambio de contraseña */
            $sql = "UPDATE recuperaciones_clave
                    SET estado = 'UTILIZADO', codigo_hash = ''
                    WHERE id_usuario = :id_usuario";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->execute(['id_usuario' => (int) $idUsuario]);

            $this->conexionMD->commit();

            $this->estadoRespuesta = 200;
            $this->mensajeAcceso = 'La contraseña fue actualizada correctamente. Inicie sesión con su nueva contraseña.';
            $this->codigoResultado = 'clave_recuperada';
            return true;

        } catch (Throwable $error) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $error;
        }
    }


    /* ================================================= */
    /* COMPROBACIONES INTERNAS DE RECUPERACIÓN */
    /* ================================================= */

    private function bloquearCuentaRecuperacion(string|int $identificador, bool $porId = false): ?array
    {
        $columna = $porId ? 'u.id_usuario' : 'p.cedula';

        $sql = "SELECT u.id_usuario, u.clave, u.estado_usuario,
                    p.cedula, p.correo, p.nombre, p.apellido,
                    r.estado_rol
                FROM usuarios u
                INNER JOIN personas p ON p.id_persona = u.id_persona
                INNER JOIN roles r ON r.id_rol = u.id_rol
                WHERE " . $columna . " = :identificador
                LIMIT 1
                FOR UPDATE";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':identificador', $identificador, $porId ? PDO::PARAM_INT : PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }


    private function bloquearRecuperacion(int $idUsuario): ?array
    {
        $sql = "SELECT id_usuario, solicitud_hash, codigo_hash,
                    correo_destino, clave_vigente_hash,
                    solicitado_en, vence_en, ventana_inicio,
                    envios, intentos, estado
                FROM recuperaciones_clave
                WHERE id_usuario = :id_usuario
                LIMIT 1
                FOR UPDATE";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute(['id_usuario' => $idUsuario]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }


    private function cuentaRecuperable(array $cuenta): bool
    {
        return $cuenta['estado_usuario'] === 'ACTIVO' && $cuenta['estado_rol'] === 'ACTIVO';
    }


    private function confirmarSolicitudRecuperacion(string $solicitud): string
    {
        /* Mantener la misma respuesta para cuentas inexistentes, inactivas o con límites */
        $this->estadoRespuesta = 200;
        $this->mensajeAcceso = 'Si la cédula y el correo corresponden a una cuenta habilitada, recibirá un código de recuperación. Revise su correo y la carpeta de spam.';
        $this->codigoResultado = 'solicitud_recibida';
        return $solicitud;
    }


    private function rechazarCodigoRecuperacion(): bool
    {
        $this->estadoRespuesta = 422;
        $this->mensajeAcceso = 'El código no es válido o ha vencido. Revíselo o solicite uno nuevo.';
        $this->codigoResultado = 'codigo_no_valido';
        $this->erroresValidacion['codigo_recuperacion'] = $this->mensajeAcceso;
        return false;
    }


    protected function ahoraRecuperacion(): int
    {
        return time();
    }

}