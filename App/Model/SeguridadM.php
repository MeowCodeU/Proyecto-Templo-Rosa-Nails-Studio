<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;

class SeguridadM extends Database implements CrudInterface
{
    /* Atributo de conexión del modelo */
    private $conexionMD;

    /* Atributos de la tabla usuarios */
    private $idUsuario;
    private $idPersona;
    private $idRol;
    private $clave;

    /* Atributos de la tabla personas */
    private $cedula;
    private $nombre;
    private $apellido;
    private $telefono;
    private $correo;
    private $direccion;
    private $ciudad;


    /* Setters: asignan valores a los atributos privados */
    public function set_idUsuario($idUsuario) {$this->idUsuario = $idUsuario;}
    public function set_idPersona($idPersona) {$this->idPersona = $idPersona;}
    public function set_idRol($idRol) {$this->idRol = $idRol;}
    public function set_clave($clave) {$this->clave = $clave;}
    public function set_cedula($cedula) {$this->cedula = $cedula;}
    public function set_nombre($nombre) {$this->nombre = $nombre;}
    public function set_apellido($apellido) {$this->apellido = $apellido;}
    public function set_telefono($telefono) {$this->telefono = $telefono;}
    public function set_correo($correo) {$this->correo = $correo;}
    public function set_direccion($direccion) {$this->direccion = $direccion;}
    public function set_ciudad($ciudad) {$this->ciudad = $ciudad;}


    /* Constructor: obtiene la conexión heredada de Database */
    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* Consultar: obtiene únicamente los usuarios activos */
    public function consultar()
    {
        $sql = "SELECT
                    u.id_usuario,
                    u.id_persona,
                    u.id_rol,
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


    /* Buscar persona por cédula para reutilizar su registro */
    public function buscarPersonaPorCedula()
    {
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


    /* Registrar: busca o crea la persona y luego registra el usuario */
    public function registrar()
    {
        try {

            $this->conexionMD->beginTransaction();


            /*
             * Buscar si la persona ya existe.
             *
             * Si ya está registrada como cliente
             * o contacto de proveedor,
             * se reutiliza su id_persona.
             */

            $sqlBuscarPersona = "SELECT
                                    p.id_persona,
                                    u.id_usuario

                                 FROM personas p

                                 LEFT JOIN usuarios u
                                    ON u.id_persona = p.id_persona

                                 WHERE p.cedula = :cedula

                                 LIMIT 1";

            $stmtBuscarPersona = $this->conexionMD->prepare(
                $sqlBuscarPersona
            );

            $stmtBuscarPersona->bindParam(':cedula', $this->cedula);

            $stmtBuscarPersona->execute();

            $persona = $stmtBuscarPersona->fetch(
                PDO::FETCH_ASSOC
            );


            if ($persona) {

                /*
                 * Si la persona ya posee una cuenta
                 * de usuario, no se registra otra.
                 */

                if (!empty($persona['id_usuario'])) {

                    $this->conexionMD->rollBack();

                    return false;
                }


                /*
                 * Si la persona ya existe,
                 * se reutiliza su identificador.
                 */

                $idPersona = $persona['id_persona'];


                /*
                 * También se actualizan sus datos
                 * personales y de contacto.
                 */

                $sqlActualizarPersona = "UPDATE personas

                                         SET
                                            nombre = :nombre,
                                            apellido = :apellido,
                                            telefono = :telefono,
                                            correo = :correo,
                                            direccion = :direccion,
                                            ciudad = :ciudad

                                         WHERE id_persona = :id_persona";

                $stmtActualizarPersona = $this->conexionMD->prepare(
                    $sqlActualizarPersona
                );

                $stmtActualizarPersona->bindParam(':nombre', $this->nombre);
                $stmtActualizarPersona->bindParam(':apellido', $this->apellido);
                $stmtActualizarPersona->bindParam(':telefono', $this->telefono);
                $stmtActualizarPersona->bindParam(':correo', $this->correo);
                $stmtActualizarPersona->bindParam(':direccion', $this->direccion);
                $stmtActualizarPersona->bindParam(':ciudad', $this->ciudad);
                $stmtActualizarPersona->bindParam(':id_persona', $idPersona);

                $stmtActualizarPersona->execute();

            } else {

                /*
                 * Si la persona no existe,
                 * se registra primero.
                 */

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

                $stmtPersona = $this->conexionMD->prepare(
                    $sqlPersona
                );

                $stmtPersona->bindParam(':cedula', $this->cedula);
                $stmtPersona->bindParam(':nombre', $this->nombre);
                $stmtPersona->bindParam(':apellido', $this->apellido);
                $stmtPersona->bindParam(':telefono', $this->telefono);
                $stmtPersona->bindParam(':correo', $this->correo);
                $stmtPersona->bindParam(':direccion', $this->direccion);
                $stmtPersona->bindParam(':ciudad', $this->ciudad);

                $stmtPersona->execute();

                $idPersona = $this->conexionMD->lastInsertId();
            }


            /*
             * La contraseña se cifra antes
             * de guardarla en la base de datos.
             */

            $claveCifrada = password_hash(
                $this->clave,
                PASSWORD_DEFAULT
            );


            /* Registrar el usuario */

            $sqlUsuario = "INSERT INTO usuarios (
                                id_persona,
                                id_rol,
                                clave,
                                estado_usuario
                           ) VALUES (
                                :id_persona,
                                :id_rol,
                                :clave,
                                'ACTIVO'
                           )";

            $stmtUsuario = $this->conexionMD->prepare(
                $sqlUsuario
            );

            $stmtUsuario->bindParam(':id_persona', $idPersona);
            $stmtUsuario->bindParam(':id_rol', $this->idRol);
            $stmtUsuario->bindParam(':clave', $claveCifrada);

            $stmtUsuario->execute();


            $this->conexionMD->commit();

            return true;

        } catch (\Exception $e) {

            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $e;
        }
    }


    /* Modificar: actualiza los datos personales y los datos del usuario */
    public function modificar()
    {
        try {

            $this->conexionMD->beginTransaction();


            /* Modificar los datos de la persona */

            $sqlPersona = "UPDATE personas

                           SET
                                nombre = :nombre,
                                apellido = :apellido,
                                telefono = :telefono,
                                correo = :correo,
                                direccion = :direccion,
                                ciudad = :ciudad

                           WHERE id_persona = :id_persona";

            $stmtPersona = $this->conexionMD->prepare(
                $sqlPersona
            );

            $stmtPersona->bindParam(':nombre', $this->nombre);
            $stmtPersona->bindParam(':apellido', $this->apellido);
            $stmtPersona->bindParam(':telefono', $this->telefono);
            $stmtPersona->bindParam(':correo', $this->correo);
            $stmtPersona->bindParam(':direccion', $this->direccion);
            $stmtPersona->bindParam(':ciudad', $this->ciudad);
            $stmtPersona->bindParam(':id_persona', $this->idPersona);

            $stmtPersona->execute();


            /* Modificar los datos propios del usuario */

            $sqlUsuario = "UPDATE usuarios

                           SET
                                id_rol = :id_rol

                           WHERE id_usuario = :id_usuario";

            $stmtUsuario = $this->conexionMD->prepare(
                $sqlUsuario
            );

            $stmtUsuario->bindParam(':id_rol', $this->idRol);
            $stmtUsuario->bindParam(':id_usuario', $this->idUsuario);

            $stmtUsuario->execute();


            /*
             * Si se proporciona una nueva contraseña,
             * se cifra y se actualiza.
             */

            if (!empty($this->clave)) {

                $claveCifrada = password_hash(
                    $this->clave,
                    PASSWORD_DEFAULT
                );

                $sqlClave = "UPDATE usuarios

                             SET
                                clave = :clave

                             WHERE id_usuario = :id_usuario";

                $stmtClave = $this->conexionMD->prepare(
                    $sqlClave
                );

                $stmtClave->bindParam(':clave', $claveCifrada);
                $stmtClave->bindParam(':id_usuario', $this->idUsuario);

                $stmtClave->execute();
            }


            $this->conexionMD->commit();

            return true;

        } catch (\Exception $e) {

            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $e;
        }
    }


    /*
     * Eliminar lógico:
     * no borra al usuario de la base de datos.
     * Solamente cambia su estado a INACTIVO.
     */

    public function eliminar()
    {
        $sql = "UPDATE usuarios

                SET
                    estado_usuario = 'INACTIVO'

                WHERE id_usuario = :id_usuario";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_usuario', $this->idUsuario);

        return $stmt->execute();
    }
}

