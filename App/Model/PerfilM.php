<?php

namespace App\Model;

use App\Config\Database;
use PDO;


class PerfilM extends Database
{
    /*Conexión utilizada para ejecutar las operaciones relacionadas con el perfil del usuario autenticado.*/
    private PDO $conexionMD;


    /*Obtiene la conexión configurada para la base de datos del sistema.*/
    public function __construct()
    {
        $this->conexionMD =
            $this->getConnection();
    }


    /*Recupera únicamente la clave cifrada correspondiente al usuario autenticado.
    Esta información se utiliza para comprobar la contraseña actual mediante password_verify().*/
    public function obtenerClaveActual(
        int $idUsuario
    ): ?string {

        $sql = "SELECT clave

                FROM usuarios

                WHERE id_usuario = :id_usuario
                AND estado_usuario = 'ACTIVO'

                LIMIT 1";


        $stmt =
            $this->conexionMD->prepare(
                $sql
            );


        $stmt->bindValue(
            ':id_usuario',
            $idUsuario,
            PDO::PARAM_INT
        );


        $stmt->execute();


        $claveCifrada =
            $stmt->fetchColumn();


        if ($claveCifrada === false) {
            return null;
        }


        return (string) $claveCifrada;
    }


    /*Cifra y actualiza la nueva contraseña. El identificador se obtiene de la sesión y no desde el formulario,
    evitando queun usuario intente modificar la clave correspondiente a otra cuenta.*/
    public function cambiarClave(
        int $idUsuario,
        string $nuevaClave
    ): bool {

        $claveCifrada =
            password_hash(
                $nuevaClave,
                PASSWORD_DEFAULT
            );


        $sql = "UPDATE usuarios

                SET clave = :clave

                WHERE id_usuario = :id_usuario
                AND estado_usuario = 'ACTIVO'";


        $stmt =
            $this->conexionMD->prepare(
                $sql
            );


        $stmt->bindValue(
            ':clave',
            $claveCifrada,
            PDO::PARAM_STR
        );


        $stmt->bindValue(
            ':id_usuario',
            $idUsuario,
            PDO::PARAM_INT
        );


        $stmt->execute();


        return $stmt->rowCount() === 1;
    }
}