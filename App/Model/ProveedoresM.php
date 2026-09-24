<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;

class ProveedoresM extends Database implements CrudInterface
{
    /* Atributo de conexión del modelo */
    private $conexionMD;

    /* Atributos de la tabla proveedores */
    private $idProveedor;
    private $idPersonaContacto;
    private $rif;
    private $nombreEmpresa;

    /* Atributos de la tabla personas */
    private $cedula;
    private $nombre;
    private $apellido;
    private $telefono;
    private $correo;
    private $direccion;
    private $ciudad;

    /* Setters: asignan valores a los atributos privados */
    public function set_idProveedor($idProveedor) {$this->idProveedor = $idProveedor;}
    public function set_idPersonaContacto($idPersonaContacto) {$this->idPersonaContacto = $idPersonaContacto;}
    public function set_rif($rif) {$this->rif = $rif;}
    public function set_nombreEmpresa($nombreEmpresa) {$this->nombreEmpresa = $nombreEmpresa;}
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

    /* Consultar: obtiene únicamente los proveedores activos */
    public function consultar()
    {
        $sql = "SELECT
                    pr.id_proveedor,
                    pr.id_persona_contacto,
                    pr.rif,
                    pr.nombre_empresa,
                    pr.estado_proveedor,

                    p.cedula,
                    p.nombre,
                    p.apellido,
                    p.telefono,
                    p.correo,
                    p.direccion,
                    p.ciudad

                FROM proveedores pr

                INNER JOIN personas p
                    ON p.id_persona = pr.id_persona_contacto

                WHERE pr.estado_proveedor = 'ACTIVO'

                ORDER BY
                    pr.nombre_empresa,
                    p.nombre,
                    p.apellido";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Registrar: busca o crea la persona y luego registra el proveedor */
    public function registrar()
    {
        try {
            $this->conexionMD->beginTransaction();

            /*
             * Buscar si la persona de contacto ya existe.
             * Si existe, se reutiliza su id_persona.
             */
            $sqlBuscarPersona = "SELECT
                                    id_persona

                                 FROM personas

                                 WHERE cedula = :cedula

                                 LIMIT 1";

            $stmtBuscarPersona = $this->conexionMD->prepare(
                $sqlBuscarPersona
            );

            $stmtBuscarPersona->bindParam(
                ':cedula',
                $this->cedula
            );

            $stmtBuscarPersona->execute();

            $persona = $stmtBuscarPersona->fetch(
                PDO::FETCH_ASSOC
            );

            if ($persona) {

                /*
                 * Si la persona ya existe,
                 * se reutiliza su identificador.
                 */
                $idPersonaContacto = $persona['id_persona'];

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
                $stmtActualizarPersona->bindParam(':id_persona', $idPersonaContacto);

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

                $idPersonaContacto =
                    $this->conexionMD->lastInsertId();
            }

            /*
             * Registrar el proveedor relacionado
             * con la persona de contacto.
             */
            $sqlProveedor = "INSERT INTO proveedores (
                                id_persona_contacto,
                                rif,
                                nombre_empresa,
                                estado_proveedor
                             ) VALUES (
                                :id_persona_contacto,
                                :rif,
                                :nombre_empresa,
                                'ACTIVO'
                             )";

            $stmtProveedor = $this->conexionMD->prepare(
                $sqlProveedor
            );

            $stmtProveedor->bindParam(
                ':id_persona_contacto',
                $idPersonaContacto
            );

            $stmtProveedor->bindParam(
                ':rif',
                $this->rif
            );

            $stmtProveedor->bindParam(
                ':nombre_empresa',
                $this->nombreEmpresa
            );

            $stmtProveedor->execute();

            $this->conexionMD->commit();

            return true;

        } catch (\Throwable $e) {

            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $e;
        }
    }

    /* Modificar: actualiza la persona de contacto y el proveedor */
    public function modificar()
    {
        try {
            $this->conexionMD->beginTransaction();

            /* Actualizar los datos de la persona de contacto */
            $sqlPersona = "UPDATE personas

                           SET
                                nombre = :nombre,
                                apellido = :apellido,
                                telefono = :telefono,
                                correo = :correo,
                                direccion = :direccion,
                                ciudad = :ciudad

                           WHERE id_persona = :id_persona_contacto";

            $stmtPersona = $this->conexionMD->prepare(
                $sqlPersona
            );

            $stmtPersona->bindParam(':nombre', $this->nombre);
            $stmtPersona->bindParam(':apellido', $this->apellido);
            $stmtPersona->bindParam(':telefono', $this->telefono);
            $stmtPersona->bindParam(':correo', $this->correo);
            $stmtPersona->bindParam(':direccion', $this->direccion);
            $stmtPersona->bindParam(':ciudad', $this->ciudad);
            $stmtPersona->bindParam(':id_persona_contacto', $this->idPersonaContacto);

            $stmtPersona->execute();

            /* Actualizar los datos propios del proveedor */
            $sqlProveedor = "UPDATE proveedores

                             SET
                                rif = :rif,
                                nombre_empresa = :nombre_empresa

                             WHERE id_proveedor = :id_proveedor";

            $stmtProveedor = $this->conexionMD->prepare(
                $sqlProveedor
            );

            $stmtProveedor->bindParam(':rif', $this->rif);
            $stmtProveedor->bindParam(':nombre_empresa', $this->nombreEmpresa);
            $stmtProveedor->bindParam(':id_proveedor', $this->idProveedor);

            $stmtProveedor->execute();

            $this->conexionMD->commit();

            return true;

        } catch (\Throwable $e) {

            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $e;
        }
    }

    /*
     * Eliminar lógico:
     * no borra al proveedor de la base de datos.
     * Solamente cambia su estado a INACTIVO.
     */
    public function eliminar()
    {
        $sql = "UPDATE proveedores

                SET
                    estado_proveedor = 'INACTIVO'

                WHERE id_proveedor = :id_proveedor";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(
            ':id_proveedor',
            $this->idProveedor
        );

        return $stmt->execute();
    }
}