<?php

namespace App\Model;

use App\Components\Correo;
use App\Interfaces\CrudInterface;
use App\Traits\ValidacionUsuarioTrait;
use PDO;

/* ===================================================== */
/* MODELO DE SEGURIDAD */
/* ===================================================== */

class SeguridadM extends PersonasM implements CrudInterface
{
    use ValidacionUsuarioTrait;

    /* ================================================= */
    /* ATRIBUTOS */
    /* ================================================= */

    private $idUsuario;
    private $idPersona;
    private $idRol;
    private $nickname;
    private $clave;
    private $estadoUsuario = 'INACTIVO';

    /* Servicio de correo y resultado de la notificación de cuenta creada */
    private ?Correo $correoElectronico = null;
    private string $resultadoCorreoBienvenida = 'pendiente';


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct(?PDO $conexion = null, ?Correo $correoElectronico = null)
    {
        if ($conexion === null) {
            parent::__construct();
        } else {
            $this->conexionMD = $conexion;
        }

        $this->correoElectronico = $correoElectronico;
    }


    /* ================================================= */
    /* CONSULTAR EL RESULTADO DEL CORREO DE BIENVENIDA */
    /* ================================================= */

    public function obtenerResultadoCorreoBienvenida(): string
    {
        return $this->resultadoCorreoBienvenida;
    }


    /* ================================================= */
    /* SETTERS */
    /* ================================================= */

    public function set_idUsuario($idUsuario) {$this->idUsuario = $idUsuario;}
    public function set_idPersona($idPersona) {$this->idPersona = $idPersona;}
    public function set_idRol($idRol) {$this->idRol = $idRol;}
    public function set_nickname($nickname) {$this->nickname = $nickname;}
    public function set_clave($clave) {$this->clave = $clave;}
    public function set_estadoUsuario($estadoUsuario) {$this->estadoUsuario = $estadoUsuario;}

    /* ================================================= */
    /* CONSULTAR USUARIOS */
    /* ================================================= */

    public function consultar(mixed $estado = 'ACTIVO'): array
    {
        $estado = $this->validarFiltroEstado($estado);

        /* Obtener los usuarios con sus datos personales y el estado de su rol */
        $sql = "SELECT
                    u.id_usuario,
                    u.id_persona,
                    u.id_rol,
                    u.nickname,
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
                    ON r.id_rol = u.id_rol";

        if ($estado !== 'TODOS') {
            $sql .= " WHERE u.estado_usuario = :estado_usuario";
        }

        $sql .= " ORDER BY p.nombre, p.apellido";

        $stmt = $this->conexionMD->prepare($sql);

        if ($estado !== 'TODOS') {
            $stmt->bindValue(':estado_usuario', $estado, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ================================================= */
    /* BUSCAR PERSONA POR CÉDULA */
    /* ================================================= */

    public function buscarPersonaPorCedula()
    {
        if (!$this->validarIdentificacion()) return false;

        /* Recuperar los datos de la persona y comprobar si tiene una cuenta */
        $sql = "SELECT
                    p.id_persona,
                    p.cedula,
                    p.nombre,
                    p.apellido,
                    p.telefono,
                    p.correo,
                    p.direccion,
                    p.ciudad,

                    u.id_usuario,
                    u.estado_usuario

                FROM personas p

                LEFT JOIN usuarios u
                    ON u.id_persona = p.id_persona

                WHERE p.cedula = :cedula

                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':cedula', $this->cedula);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ================================================= */
    /* REGISTRAR USUARIO */
    /* ================================================= */

    public function registrar(mixed $confirmarClave = null): bool
    {
        $this->resultadoCorreoBienvenida = 'pendiente';

        if (!$this->validarDatosUsuario(true, $confirmarClave)) return false;

        try {
            $this->conexionMD->beginTransaction();

            if (!$this->comprobarRolActivo()) {
                $this->conexionMD->rollBack();
                return false;
            }

            /* Impedir que una persona existente tenga dos cuentas */
            $persona = $this->obtenerPersonaPorCedula((string) $this->cedula);

            if ($persona !== null) {
                $sqlBuscarCuenta = "SELECT id_usuario, estado_usuario
                                    FROM usuarios
                                    WHERE id_persona = :id_persona
                                    LIMIT 1";

                $stmtBuscarCuenta = $this->conexionMD->prepare($sqlBuscarCuenta);
                $stmtBuscarCuenta->bindValue(':id_persona', (int) $persona['id_persona'], PDO::PARAM_INT);
                $stmtBuscarCuenta->execute();

                $cuentaExistente = $stmtBuscarCuenta->fetch(PDO::FETCH_ASSOC);

                if ($cuentaExistente !== false) {
                    $this->erroresValidacion['cedula'] = $cuentaExistente['estado_usuario'] === 'INACTIVO'
                        ? 'La persona ya tiene una cuenta inactiva. Reactívela desde Inactivos.'
                        : 'La persona seleccionada ya tiene una cuenta de usuario.';
                    $this->conexionMD->rollBack();
                    return false;
                }
            }

            /* Reutilizar la persona registrada o crearla si no existe */
            $idPersona = $this->obtenerORegistrarPersona();
            $claveCifrada = password_hash($this->clave, PASSWORD_DEFAULT);

            $sqlUsuario = "INSERT INTO usuarios (
                                id_persona,
                                id_rol,
                                nickname,
                                clave,
                                estado_usuario
                           ) VALUES (
                                :id_persona,
                                :id_rol,
                                :nickname,
                                :clave,
                                'ACTIVO'
                           )";

            $stmtUsuario = $this->conexionMD->prepare($sqlUsuario);
            $stmtUsuario->bindValue(':id_persona', $idPersona, PDO::PARAM_INT);
            $stmtUsuario->bindParam(':id_rol', $this->idRol);
            $stmtUsuario->bindParam(':nickname', $this->nickname);
            $stmtUsuario->bindParam(':clave', $claveCifrada);
            $stmtUsuario->execute();

            if (!$this->conexionMD->commit()) {
                throw new \RuntimeException('No fue posible confirmar el registro del usuario.');
            }

        } catch (\Throwable $e) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }
            throw $e;
        }

        /* Notificar únicamente después de confirmar el registro en la base de datos */
        $this->notificarCuentaCreada();
        return true;
    }

    /* ================================================= */
    /* NOTIFICAR LA CREACIÓN DE LA CUENTA */
    /* ================================================= */

    private function notificarCuentaCreada(): void
    {
        $this->resultadoCorreoBienvenida = 'no_enviado';

        try {
            $this->correoElectronico ??= new Correo();
            $nombreCompleto = trim($this->nombre . ' ' . $this->apellido);

            if ($this->correoElectronico->enviarBienvenida($this->correo, $nombreCompleto)) {
                $this->resultadoCorreoBienvenida = 'enviado';
            }

        } catch (\Throwable $error) {
            /* Conservar la cuenta creada aunque falle el servicio de correo */
            error_log('SeguridadM: la cuenta fue creada, pero no se pudo enviar el correo de bienvenida.');
        }
    }


    /* ================================================= */
    /* MODIFICAR USUARIO */
    /* ================================================= */

    public function modificar(): bool
    {
        if (!$this->validarDatosUsuario(false)) return false;

        try {
            $this->conexionMD->beginTransaction();

            if (!$this->comprobarRolActivo()) {
                $this->conexionMD->rollBack();
                return false;
            }

            /* Comprobar la persona asociada a la cuenta seleccionada */
            $sqlBuscarPersona = "SELECT id_persona
                                 FROM usuarios
                                 WHERE id_usuario = :id_usuario
                                   AND estado_usuario = 'ACTIVO'
                                 LIMIT 1
                                 FOR UPDATE";

            $stmtBuscarPersona = $this->conexionMD->prepare($sqlBuscarPersona);
            $stmtBuscarPersona->bindValue(':id_usuario', $this->idUsuario, PDO::PARAM_INT);
            $stmtBuscarPersona->execute();

            $idPersona = $stmtBuscarPersona->fetchColumn();

            if ($idPersona === false) {
                $this->erroresValidacion['id_usuario'] = 'El usuario seleccionado no se encuentra disponible.';
                $this->conexionMD->rollBack();
                return false;
            }

            if ($this->idPersona !== null && (int) $this->idPersona !== (int) $idPersona) {
                $this->erroresValidacion['id_persona'] = 'La persona no corresponde a la cuenta seleccionada.';
                throw new \InvalidArgumentException('La persona no corresponde a la cuenta seleccionada.');
            }

            $this->actualizarPersona((int) $idPersona);

            $sqlUsuario = "UPDATE usuarios
                           SET id_rol = :id_rol,
                               nickname = :nickname
                           WHERE id_usuario = :id_usuario";

            $stmtUsuario = $this->conexionMD->prepare($sqlUsuario);
            $stmtUsuario->bindParam(':id_rol', $this->idRol);
            $stmtUsuario->bindParam(':nickname', $this->nickname);
            $stmtUsuario->bindParam(':id_usuario', $this->idUsuario);
            $stmtUsuario->execute();

            /* La contraseña solo cambia cuando se proporciona una nueva */
            if (!empty($this->clave)) {
                $claveCifrada = password_hash($this->clave, PASSWORD_DEFAULT);

                $sqlClave = "UPDATE usuarios
                             SET clave = :clave
                             WHERE id_usuario = :id_usuario";

                $stmtClave = $this->conexionMD->prepare($sqlClave);
                $stmtClave->bindParam(':clave', $claveCifrada);
                $stmtClave->bindParam(':id_usuario', $this->idUsuario);
                $stmtClave->execute();
            }

            $this->conexionMD->commit();
            return true;

        } catch (\Throwable $e) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }
            throw $e;
        }
    }

    /* ================================================= */
    /* RESTABLECER CONTRASEÑA */
    /* ================================================= */

    public function restablecerClave(mixed $confirmarClave = null): bool
    {
        $this->validarIdUsuarioSeguridad();
        $this->validarClaveSeguridad($this->clave, 'nueva_clave', $confirmarClave);

        if ($this->erroresValidacion !== []) return false;

        /* Generar el hash de la contraseña antes de actualizarla */
        $claveCifrada = password_hash($this->clave, PASSWORD_DEFAULT);

        $sql = "UPDATE usuarios
                SET clave = :clave
                WHERE id_usuario = :id_usuario
                  AND estado_usuario = 'ACTIVO'";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':clave', $claveCifrada);
        $stmt->bindParam(':id_usuario', $this->idUsuario);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /* ================================================= */
    /* DESACTIVAR USUARIO */
    /* ================================================= */

    public function eliminar(): bool
    {
        if (!$this->validarDesactivacionUsuario()) return false;

        /* Cambiar el estado del usuario sin eliminar su registro */
        $sql = "UPDATE usuarios
                SET estado_usuario = 'INACTIVO'
                WHERE id_usuario = :id_usuario
                  AND estado_usuario = 'ACTIVO'";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_usuario', $this->idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() !== 1) {
            throw new \DomainException('El usuario ya está inactivo o no se encuentra disponible. Actualice la lista.');
        }

        return true;
    }

    /* ================================================= */
    /* REACTIVAR USUARIO */
    /* ================================================= */

    public function reactivar(): bool
    {
        if (!$this->validarIdUsuarioSeguridad()) return false;

        try {
            $this->conexionMD->beginTransaction();

            /* Consultar el rol que ya pertenece a la cuenta inactiva */
            $sqlCuenta = "SELECT id_rol
                          FROM usuarios
                          WHERE id_usuario = :id_usuario
                            AND estado_usuario = 'INACTIVO'
                          LIMIT 1";

            $stmtCuenta = $this->conexionMD->prepare($sqlCuenta);
            $stmtCuenta->bindValue(':id_usuario', $this->idUsuario, PDO::PARAM_INT);
            $stmtCuenta->execute();

            $idRol = $stmtCuenta->fetchColumn();

            if ($idRol === false) {
                throw new \DomainException('El usuario ya está activo o no se encuentra disponible. Actualice la lista.');
            }

            /* Mantener el rol bloqueado hasta confirmar la reactivación */
            if (!$this->comprobarRolActivo((int) $idRol)) {
                $this->erroresValidacion['id_rol'] = 'El rol asignado está inactivo o no existe. Reactive primero el rol.';
                $this->conexionMD->rollBack();
                return false;
            }

            /* Conservar la contraseña, la persona y el rol de la cuenta */
            $sql = "UPDATE usuarios
                    SET estado_usuario = 'ACTIVO'
                    WHERE id_usuario = :id_usuario
                      AND id_rol = :id_rol
                      AND estado_usuario = 'INACTIVO'";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':id_usuario', $this->idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':id_rol', (int) $idRol, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() !== 1) {
                throw new \DomainException('La cuenta cambió durante la operación. Actualice la lista antes de reactivarla.');
            }

            if (!$this->conexionMD->commit()) {
                throw new \RuntimeException('No fue posible confirmar la reactivación del usuario.');
            }

            return true;

        } catch (\Throwable $e) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }
            throw $e;
        }
    }

    /* ================================================= */
    /* VALIDAR LA CÉDULA ANTES DE BUSCAR UNA PERSONA */
    /* ================================================= */

    public function validarIdentificacion(): bool
    {
        if (!$this->validarCampos(['cedula' => $this->cedula], ['cedula'])) return false;

        $this->cedula = trim($this->cedula);
        return true;
    }

    /* ================================================= */
    /* COMPROBAR EL ROL ASIGNADO DENTRO DE LA TRANSACCIÓN */
    /* ================================================= */

    private function comprobarRolActivo(?int $idRol = null): bool
    {
        $sql = "SELECT id_rol
                FROM roles
                WHERE id_rol = :id_rol
                  AND estado_rol = 'ACTIVO'
                LIMIT 1
                FOR UPDATE";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_rol', $idRol ?? $this->idRol, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn() !== false) return true;

        $this->erroresValidacion['id_rol'] = 'El rol seleccionado no está activo o no existe.';
        return false;
    }
}