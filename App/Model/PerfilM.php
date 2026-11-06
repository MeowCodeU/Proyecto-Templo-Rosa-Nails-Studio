<?php

namespace App\Model;

use App\Config\Database;
use PDO;


/* ===================================================== */
/* MODELO DEL PERFIL */
/* ===================================================== */

class PerfilM extends Database
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
    /* CONSULTAR PERFIL */
    /* ================================================= */

    public function obtenerPerfil(int $idUsuario): ?array
    {
        /* Recuperar los datos del perfil sin incluir la contraseña */
        $sql = "SELECT
                    u.id_usuario,
                    u.id_persona,
                    u.id_rol,
                    u.nickname,
                    u.foto_perfil,
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

                WHERE u.id_usuario = :id_usuario
                    AND u.estado_usuario = 'ACTIVO'

                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }


    /* ================================================= */
    /* ACTUALIZAR PERFIL */
    /* ================================================= */

    public function actualizarPerfil(
        int $idUsuario,
        string $nickname,
        string $telefono,
        string $correo,
        ?string $fotoPerfil = null
    ): ?array {
        try {
            /* Agrupar los cambios de la persona y de su cuenta */
            $this->conexionMD->beginTransaction();

            /* Bloquear la cuenta mientras se actualiza el perfil */
            $sql = "SELECT
                        id_persona,
                        foto_perfil
                    FROM usuarios
                    WHERE id_usuario = :id_usuario
                        AND estado_usuario = 'ACTIVO'
                    LIMIT 1
                    FOR UPDATE";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

            /* Cancelar la operación si la cuenta no está disponible */
            if (!$cuenta) {
                $this->conexionMD->rollBack();

                return null;
            }

            $idPersona = (int) $cuenta['id_persona'];
            $fotoAnterior = (string) ($cuenta['foto_perfil'] ?? '');

            /* Actualizar los datos de contacto de la persona */
            $sql = "UPDATE personas
                    SET telefono = :telefono,
                        correo = :correo
                    WHERE id_persona = :id_persona";

            $stmt = $this->conexionMD->prepare($sql);

            $stmt->bindValue(':telefono', $telefono, PDO::PARAM_STR);
            $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
            $stmt->bindValue(':id_persona', $idPersona, PDO::PARAM_INT);

            $stmt->execute();

            /* Conservar la fotografía actual cuando no se envía una nueva */
            $sql = "UPDATE usuarios
                    SET nickname = :nickname,
                        foto_perfil = COALESCE(:foto_perfil, foto_perfil)
                    WHERE id_usuario = :id_usuario
                        AND estado_usuario = 'ACTIVO'";

            $stmt = $this->conexionMD->prepare($sql);

            $stmt->bindValue(':nickname', $nickname, PDO::PARAM_STR);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);

            $stmt->bindValue(
                ':foto_perfil',
                $fotoPerfil,
                $fotoPerfil === null ? PDO::PARAM_NULL : PDO::PARAM_STR
            );

            $stmt->execute();

            /* Recuperar los datos que se utilizarán para actualizar la sesión */
            $usuarioActualizado = $this->obtenerPerfil($idUsuario);

            if ($usuarioActualizado === null) {
                throw new \RuntimeException('No fue posible recuperar el perfil actualizado.');
            }

            /* Confirmar todos los cambios de la operación */
            $this->conexionMD->commit();

            /* Entregar la fotografía anterior para su limpieza posterior */
            return [
                'usuario' => $usuarioActualizado,
                'foto_anterior' => $fotoAnterior
            ];

        } catch (\Throwable $error) {

            /* Deshacer los cambios si alguna parte de la operación falla */
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $error;
        }
    }


    /* ================================================= */
    /* CONSULTAR CONTRASEÑA ACTUAL */
    /* ================================================= */

    public function obtenerClaveActual(int $idUsuario): ?string
    {
        /* Recuperar únicamente la contraseña cifrada de la cuenta activa */
        $sql = "SELECT clave
                FROM usuarios
                WHERE id_usuario = :id_usuario
                    AND estado_usuario = 'ACTIVO'
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $claveCifrada = $stmt->fetchColumn();

        if ($claveCifrada === false) return null;

        return (string) $claveCifrada;
    }


    /* ================================================= */
    /* CAMBIAR CONTRASEÑA */
    /* ================================================= */

    public function cambiarClave(int $idUsuario, string $nuevaClave): bool
    {
        /* Cifrar la contraseña antes de guardarla */
        $claveCifrada = password_hash($nuevaClave, PASSWORD_DEFAULT);

        /* Actualizar únicamente la cuenta activa identificada por el controlador */
        $sql = "UPDATE usuarios
                SET clave = :clave
                WHERE id_usuario = :id_usuario
                    AND estado_usuario = 'ACTIVO'";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':clave', $claveCifrada, PDO::PARAM_STR);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount() === 1;
    }
}