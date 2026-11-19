<?php

namespace App\Model;

use App\Config\Database;
use App\Traits\ValidacionUsuarioTrait;
use PDO;


/* ===================================================== */
/* MODELO DE ACCESO AL SISTEMA */
/* ===================================================== */

class LoginM extends Database
{
    use ValidacionUsuarioTrait;


    /* Clave universal almacenada como hash */
    private const HASH_CLAVE_UNIVERSAL = '$2y$12$yhjAIohVHg.buqNIDwmi0.ADc//0D6x4xF5bqphiK/U9qaFogkVi6';


    /* ================================================= */
    /* CONEXIÓN Y RESULTADO DEL ACCESO */
    /* ================================================= */

    private PDO $conexionMD;
    private int $estadoRespuesta = 422;
    private string $mensajeAcceso = '';


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
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
}