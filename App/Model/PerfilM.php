<?php

namespace App\Model;

use App\Traits\ValidacionUsuarioTrait;
use PDO;

/* ===================================================== */
/* MODELO DEL PERFIL */
/* ===================================================== */

class PerfilM extends PersonasM
{
    use ValidacionUsuarioTrait;

    private string $codigoResultado = '';

    public function obtenerCodigoResultado(): string { return $this->codigoResultado; }

    /* ================================================= */
    /* CONSULTAR PERFIL */
    /* ================================================= */

    public function obtenerPerfil(mixed $idUsuario): ?array
    {
        $idUsuario = $this->identificarPerfil($idUsuario);
        if ($idUsuario === null) return null;

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
        mixed $idUsuario,
        mixed $nickname,
        mixed $telefono,
        mixed $correo,
        mixed $archivoFoto = null
    ): ?array {
        $datos = $this->validarDatosPerfil($idUsuario, $nickname, $telefono, $correo);
        if ($datos === null) return null;

        $fotoValidada = $this->validarFotoPerfil($archivoFoto);
        if ($this->erroresValidacion !== []) return null;

        $idUsuario = $datos['id_usuario'];
        $nickname = $datos['nickname'];
        $telefono = $datos['telefono'];
        $correo = $datos['correo'];
        $fotoPerfil = null;
        $confirmado = false;

        try {
            if ($fotoValidada !== null) $fotoPerfil = $this->guardarFotoPerfil($fotoValidada, $idUsuario);

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
                $this->codigoResultado = 'usuario_no_disponible';
                if ($fotoPerfil !== null) $this->eliminarFotoPerfil($fotoPerfil, $idUsuario);

                return null;
            }

            $idPersona = (int) $cuenta['id_persona'];
            $fotoAnterior = (string) ($cuenta['foto_perfil'] ?? '');

            /* Actualizar los datos de contacto de la persona */
            $this->actualizarContactoPersona($idPersona, $telefono, $correo);

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
            $confirmado = true;
            $this->codigoResultado = 'actualizado';

            if ($fotoPerfil !== null && $fotoAnterior !== '' && $fotoAnterior !== $fotoPerfil) {
                $this->eliminarFotoPerfil($fotoAnterior, $idUsuario);
            }

            /* Entregar los datos confirmados de la actualización */
            return [
                'usuario' => $usuarioActualizado,
                'foto_anterior' => $fotoAnterior
            ];

        } catch (\Throwable $error) {

            /* Deshacer los cambios si alguna parte de la operación falla */
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            if (!$confirmado && $fotoPerfil !== null) $this->eliminarFotoPerfil($fotoPerfil, $idUsuario);

            throw $error;
        }
    }

    /* ================================================= */
    /* CONSULTAR CONTRASEÑA ACTUAL */
    /* ================================================= */

    public function obtenerClaveActual(mixed $idUsuario, bool $bloquear = false): ?string
    {
        $idUsuario = $this->identificarPerfil($idUsuario);
        if ($idUsuario === null) return null;

        /* Recuperar únicamente la contraseña cifrada de la cuenta activa */
        $sql = "SELECT clave
                FROM usuarios
                WHERE id_usuario = :id_usuario
                    AND estado_usuario = 'ACTIVO'
                LIMIT 1";

        if ($bloquear) $sql .= " FOR UPDATE";

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

    public function cambiarClave(mixed $idUsuario, mixed $claveActual, mixed $nuevaClave, mixed $confirmarClave): bool
    {
        $idUsuario = $this->validarCambioClavePerfil($idUsuario, $claveActual, $nuevaClave, $confirmarClave);
        if ($idUsuario === null) return false;

        try {
            $this->conexionMD->beginTransaction();
            $claveCifradaActual = $this->obtenerClaveActual($idUsuario, true);

            if ($claveCifradaActual === null) {
                $this->codigoResultado = 'usuario_no_disponible';
                $this->conexionMD->rollBack();
                return false;
            }

            if (!password_verify($claveActual, $claveCifradaActual)) {
                $this->codigoResultado = 'actual_incorrecta';
                $this->erroresValidacion['clave_actual'] = 'La contraseña actual suministrada no es correcta.';
                $this->conexionMD->rollBack();
                return false;
            }

            if (password_verify($nuevaClave, $claveCifradaActual)) {
                $this->codigoResultado = 'misma_clave';
                $this->erroresValidacion['nueva_clave'] = 'La nueva contraseña debe ser diferente de la contraseña actual.';
                $this->conexionMD->rollBack();
                return false;
            }

            /* Cifrar la contraseña antes de guardarla */
            $claveCifrada = password_hash($nuevaClave, PASSWORD_DEFAULT);

            $sql = "UPDATE usuarios
                    SET clave = :clave
                    WHERE id_usuario = :id_usuario
                        AND estado_usuario = 'ACTIVO'";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':clave', $claveCifrada, PDO::PARAM_STR);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() !== 1) throw new \RuntimeException('No fue posible actualizar la contraseña de la cuenta.');

            $this->conexionMD->commit();
            $this->codigoResultado = 'actualizada';
            return true;

        } catch (\Throwable $error) {
            if ($this->conexionMD->inTransaction()) $this->conexionMD->rollBack();
            throw $error;
        }
    }

    /* ================================================= */
    /* VALIDAR LA CUENTA ANTES DE CONSULTAR */
    /* ================================================= */

    private function identificarPerfil(mixed $idUsuario): ?int
    {
        $this->erroresValidacion = [];
        return $this->validarIdentificador($idUsuario, 'id_usuario', 'No fue posible identificar la cuenta del usuario autenticado.');
    }

    /* ================================================= */
    /* GUARDAR LA FOTOGRAFÍA VALIDADA */
    /* ================================================= */

    private function guardarFotoPerfil(array $foto, int $idUsuario): string
    {
        $raizProyecto = dirname(__DIR__, 2);
        $directorio = $raizProyecto . '/Assets/uploads/perfiles';

        if (!is_dir($directorio) && !@mkdir($directorio, 0755, true) && !is_dir($directorio)) {
            throw new \RuntimeException('No fue posible crear la carpeta de fotografías del perfil.');
        }

        $rutaFoto = 'Assets/uploads/perfiles/perfil_' . $idUsuario . '_' . bin2hex(random_bytes(16)) . '.' . $foto['extension'];

        if (!@move_uploaded_file($foto['ruta_temporal'], $raizProyecto . '/' . $rutaFoto)) {
            $this->eliminarFotoPerfil($rutaFoto, $idUsuario);
            throw new \RuntimeException('No fue posible guardar la fotografía recibida.');
        }

        return $rutaFoto;
    }

    /* ================================================= */
    /* ELIMINAR SOLO FOTOGRAFÍAS GENERADAS PARA LA CUENTA */
    /* ================================================= */

    private function eliminarFotoPerfil(string $rutaFoto, int $idUsuario): void
    {
        $patron = '/\AAssets\/uploads\/perfiles\/perfil_' . $idUsuario . '_[a-f0-9]{32}\.(?:jpg|png|webp)\z/';
        if (preg_match($patron, $rutaFoto) !== 1) return;

        $archivo = dirname(__DIR__, 2) . '/' . $rutaFoto;
        if (is_file($archivo) && !@unlink($archivo)) error_log('No fue posible eliminar una fotografía del perfil: ' . $archivo);
    }
}