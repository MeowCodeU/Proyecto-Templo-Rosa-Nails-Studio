<?php

namespace App\Model;

use App\Config\Database;
use PDO;

class LoginM extends Database
{
    private PDO $conexionMD;

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }

    /* Busca la cuenta asociada a la cédula suministrada durante el inicio de sesión. 
    La clave se recupera únicamente para comprobarla mediante password_verify(). Nunca debe almacenarse en la sesión.*/
    public function buscarPorCedula(string $cedula): ?array
    {
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

        $stmt->bindValue(
            ':cedula',
            $cedula,
            PDO::PARAM_STR
        );

        $stmt->execute();

        $usuario = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        return $usuario ?: null;
    }
}