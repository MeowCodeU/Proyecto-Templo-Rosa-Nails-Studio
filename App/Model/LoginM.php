<?php

namespace App\Model;

use App\Config\Database;
use PDO;


/* ===================================================== */
/* MODELO DE ACCESO AL SISTEMA */
/* ===================================================== */

class LoginM extends Database
{
    /* ================================================= */
    /* CONEXIÓN */
    /* ================================================= */

    /* Conexión con la base de datos */
    private PDO $conexionMD;


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* ================================================= */
    /* BUSCAR USUARIO POR CÉDULA */
    /* ================================================= */

    public function buscarPorCedula(string $cedula): ?array
    {
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

        /* La clave recuperada se utiliza para verificar las credenciales */
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        /* El controlador debe retirar la clave antes de guardar la sesión */
        return $usuario ?: null;
    }
}