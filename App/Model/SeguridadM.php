<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;


/* ===================================================== */
/* MODELO DE SEGURIDAD */
/* ===================================================== */

class SeguridadM extends Database implements CrudInterface
{
    /* ================================================= */
    /* CONEXIÓN Y ATRIBUTOS */
    /* ================================================= */

    /* Conexión con la base de datos */
    private $conexionMD;

    /* Atributos de la tabla usuarios */
    private $idUsuario;
    private $idPersona;
    private $idRol;
    private $nickname;
    private $clave;

    /* Atributos de la tabla personas */
    private $cedula;
    private $nombre;
    private $apellido;
    private $telefono;
    private $correo;
    private $direccion;
    private $ciudad;


    /* ================================================= */
    /* SETTERS */
    /* ================================================= */

    public function set_idUsuario($idUsuario) {$this->idUsuario = $idUsuario;}
    public function set_idPersona($idPersona) {$this->idPersona = $idPersona;}
    public function set_idRol($idRol) {$this->idRol = $idRol;}
    public function set_nickname($nickname) {$this->nickname = $nickname;}
    public function set_clave($clave) {$this->clave = $clave;}
    public function set_cedula($cedula) {$this->cedula = $cedula;}
    public function set_nombre($nombre) {$this->nombre = $nombre;}
    public function set_apellido($apellido) {$this->apellido = $apellido;}
    public function set_telefono($telefono) {$this->telefono = $telefono;}
    public function set_correo($correo) {$this->correo = $correo;}
    public function set_direccion($direccion) {$this->direccion = $direccion;}
    public function set_ciudad($ciudad) {$this->ciudad = $ciudad;}


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* ================================================= */
    /* CONSULTAR USUARIOS */
    /* ================================================= */

    public function consultar()
    {
        /* Obtener los usuarios activos con sus datos personales y su rol */
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

                    r.nombre_rol

                FROM usuarios u

                INNER JOIN personas p
                    ON p.id_persona = u.id_persona

                INNER JOIN roles r
                    ON r.id_rol = u.id_rol

                WHERE u.estado_usuario = 'ACTIVO'

                ORDER BY
                    p.nombre,
                    p.apellido";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* BUSCAR PERSONA POR CÉDULA */
    /* ================================================= */

    public function buscarPersonaPorCedula()
    {
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

    public function registrar()
    {
        try {
            /* Agrupar el registro de la cuenta y los datos de la persona */
            $this->conexionMD->beginTransaction();

            /* Buscar una persona existente para reutilizar su identificador */
            $sqlBuscarPersona = "SELECT
                                    p.id_persona,
                                    u.id_usuario
                                 FROM personas p
                                 LEFT JOIN usuarios u
                                    ON u.id_persona = p.id_persona
                                 WHERE p.cedula = :cedula
                                 LIMIT 1";

            $stmtBuscarPersona = $this->conexionMD->prepare($sqlBuscarPersona);
            $stmtBuscarPersona->bindParam(':cedula', $this->cedula);
            $stmtBuscarPersona->execute();

            $persona = $stmtBuscarPersona->fetch(PDO::FETCH_ASSOC);

            if ($persona) {

                /* Impedir que la persona tenga otra cuenta de usuario */
                if (!empty($persona['id_usuario'])) {
                    $this->conexionMD->rollBack();

                    return false;
                }

                /* Reutilizar el identificador de la persona existente */
                $idPersona = $persona['id_persona'];

                /* Actualizar sus datos personales y de contacto */
                $sqlActualizarPersona = "UPDATE personas
                                         SET nombre = :nombre,
                                             apellido = :apellido,
                                             telefono = :telefono,
                                             correo = :correo,
                                             direccion = :direccion,
                                             ciudad = :ciudad
                                         WHERE id_persona = :id_persona";

                $stmtActualizarPersona = $this->conexionMD->prepare($sqlActualizarPersona);

                $stmtActualizarPersona->bindParam(':nombre', $this->nombre);
                $stmtActualizarPersona->bindParam(':apellido', $this->apellido);
                $stmtActualizarPersona->bindParam(':telefono', $this->telefono);
                $stmtActualizarPersona->bindParam(':correo', $this->correo);
                $stmtActualizarPersona->bindParam(':direccion', $this->direccion);
                $stmtActualizarPersona->bindParam(':ciudad', $this->ciudad);
                $stmtActualizarPersona->bindParam(':id_persona', $idPersona);

                $stmtActualizarPersona->execute();

            } else {

                /* Registrar a la persona cuando su cédula no existe */
                $sqlPersona = "INSERT INTO personas (
                                    cedula,
                                    nombre,
                                    apellido,
                                    telefono,
                                    correo,
                                    direccion,
                                    ciudad
                               ) VALUES (
                                    :cedula,
                                    :nombre,
                                    :apellido,
                                    :telefono,
                                    :correo,
                                    :direccion,
                                    :ciudad
                               )";

                $stmtPersona = $this->conexionMD->prepare($sqlPersona);

                $stmtPersona->bindParam(':cedula', $this->cedula);
                $stmtPersona->bindParam(':nombre', $this->nombre);
                $stmtPersona->bindParam(':apellido', $this->apellido);
                $stmtPersona->bindParam(':telefono', $this->telefono);
                $stmtPersona->bindParam(':correo', $this->correo);
                $stmtPersona->bindParam(':direccion', $this->direccion);
                $stmtPersona->bindParam(':ciudad', $this->ciudad);

                $stmtPersona->execute();

                /* Recuperar el identificador de la persona registrada */
                $idPersona = $this->conexionMD->lastInsertId();
            }

            /* Generar el hash de la contraseña antes de guardar la cuenta */
            $claveCifrada = password_hash($this->clave, PASSWORD_DEFAULT);

            /* Registrar el usuario relacionado con la persona y su rol */
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

            $stmtUsuario->bindParam(':id_persona', $idPersona);
            $stmtUsuario->bindParam(':id_rol', $this->idRol);
            $stmtUsuario->bindParam(':nickname', $this->nickname);
            $stmtUsuario->bindParam(':clave', $claveCifrada);

            $stmtUsuario->execute();

            /* Confirmar todos los cambios de la operación */
            $this->conexionMD->commit();

            return true;

        } catch (\Exception $e) {

            /* Revertir la transacción si alguna parte de la operación falla */
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $e;
        }
    }


    /* ================================================= */
    /* MODIFICAR USUARIO */
    /* ================================================= */

    public function modificar()
    {
        try {
            /* Agrupar los cambios de la persona y de su cuenta */
            $this->conexionMD->beginTransaction();

            /* Actualizar los datos personales y de contacto */
            $sqlPersona = "UPDATE personas
                           SET nombre = :nombre,
                               apellido = :apellido,
                               telefono = :telefono,
                               correo = :correo,
                               direccion = :direccion,
                               ciudad = :ciudad
                           WHERE id_persona = :id_persona";

            $stmtPersona = $this->conexionMD->prepare($sqlPersona);

            $stmtPersona->bindParam(':nombre', $this->nombre);
            $stmtPersona->bindParam(':apellido', $this->apellido);
            $stmtPersona->bindParam(':telefono', $this->telefono);
            $stmtPersona->bindParam(':correo', $this->correo);
            $stmtPersona->bindParam(':direccion', $this->direccion);
            $stmtPersona->bindParam(':ciudad', $this->ciudad);
            $stmtPersona->bindParam(':id_persona', $this->idPersona);

            $stmtPersona->execute();

            /* Actualizar el rol y el nickname de la cuenta */
            $sqlUsuario = "UPDATE usuarios
                           SET id_rol = :id_rol,
                               nickname = :nickname
                           WHERE id_usuario = :id_usuario";

            $stmtUsuario = $this->conexionMD->prepare($sqlUsuario);

            $stmtUsuario->bindParam(':id_rol', $this->idRol);
            $stmtUsuario->bindParam(':nickname', $this->nickname);
            $stmtUsuario->bindParam(':id_usuario', $this->idUsuario);

            $stmtUsuario->execute();

            /* Actualizar la contraseña cuando se haya suministrado una nueva */
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

            /* Confirmar la actualización de ambas tablas */
            $this->conexionMD->commit();

            return true;

        } catch (\Exception $e) {

            /* Revertir la transacción si alguna parte de la operación falla */
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $e;
        }
    }


    /* ================================================= */
    /* RESTABLECER CONTRASEÑA */
    /* ================================================= */

    public function restablecerClave()
    {
        /* Comprobar que se hayan asignado el usuario y la contraseña */
        if (empty($this->idUsuario) || empty($this->clave)) return false;

        /* Generar el hash de la contraseña temporal suministrada */
        $claveCifrada = password_hash($this->clave, PASSWORD_DEFAULT);

        /* Actualizar únicamente la cuenta activa seleccionada */
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

    public function eliminar()
    {
        /* Cambiar el estado del usuario para conservar su registro */
        $sql = "UPDATE usuarios
                SET estado_usuario = 'INACTIVO'
                WHERE id_usuario = :id_usuario";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':id_usuario', $this->idUsuario);

        return $stmt->execute();
    }
}