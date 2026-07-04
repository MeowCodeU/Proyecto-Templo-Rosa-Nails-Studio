<?php

namespace App\Model;

use App\Config\Database;
use PDO;

class ClientesM extends Database
{
    /* Atributo de conexión del modelo */
    private $conexionMD;

    /* Atributos de la tabla personas */
    private $cedula;
    private $nombre;
    private $apellido;
    private $telefono;
    private $correo;
    private $direccion;
    private $ciudad;

    /* Atributos de la tabla clientes */
    private $alergias;

    /* Setters: asignan valores a los atributos privados */
    public function set_Cedula($cedula) {$this->cedula = $cedula;}
    public function set_Nombre($nombre) {$this->nombre = $nombre;}
    public function set_Apellido($apellido) {$this->apellido = $apellido;}
    public function set_Telefono($telefono) {$this->telefono = $telefono;}
    public function set_Correo($correo) {$this->correo = $correo;}
    public function set_Direccion($direccion) {$this->direccion = $direccion;}
    public function set_Ciudad($ciudad) {$this->ciudad = $ciudad;}
    public function set_Alergias($alergias) {$this->alergias = $alergias;}

    /* Constructor: obtiene la conexión heredada de Database */
    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }

    /* Consultar: obtiene los clientes activos y calcula sus visitas */
    public function consultar()
    {
        $sql = "SELECT
                    c.id_cliente,
                    p.id_persona,
                    p.cedula,
                    p.nombre,
                    p.apellido,
                    p.telefono,
                    p.correo,
                    p.direccion,
                    p.ciudad,
                    c.alergias,
                    c.estado_cliente AS estado,
                    (
                        SELECT COUNT(*)
                        FROM agendamientos a
                        INNER JOIN estados_agendamientos ea
                            ON ea.id_estado_agendamiento = a.id_estado_agendamiento
                        WHERE a.id_cliente = c.id_cliente
                        AND ea.nombre_estado = 'REALIZADO'
                    ) AS visitas
                FROM clientes c
                INNER JOIN personas p
                    ON p.id_persona = c.id_persona
                WHERE c.estado_cliente = 'ACTIVO'
                ORDER BY p.nombre, p.apellido";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Registrar: inserta primero la persona y luego el cliente */
    public function registrar()
    {
        try {
            $this->conexionMD->beginTransaction();

            $sqlPersona = "INSERT INTO personas
                            (cedula, nombre, apellido, telefono, correo, direccion, ciudad)
                           VALUES
                            (:cedula, :nombre, :apellido, :telefono, :correo, :direccion, :ciudad)";

            $stmtPersona = $this->conexionMD->prepare($sqlPersona);

            $stmtPersona->bindParam(':cedula', $this->cedula);
            $stmtPersona->bindParam(':nombre', $this->nombre);
            $stmtPersona->bindParam(':apellido', $this->apellido);
            $stmtPersona->bindParam(':telefono', $this->telefono);
            $stmtPersona->bindParam(':correo', $this->correo);
            $stmtPersona->bindParam(':direccion', $this->direccion);
            $stmtPersona->bindParam(':ciudad', $this->ciudad);

            $stmtPersona->execute();

            $idPersona = $this->conexionMD->lastInsertId();

            $sqlCliente = "INSERT INTO clientes
                            (id_persona, alergias, estado_cliente)
                           VALUES
                            (:id_persona, :alergias, 'ACTIVO')";

            $stmtCliente = $this->conexionMD->prepare($sqlCliente);

            $stmtCliente->bindParam(':id_persona', $idPersona);
            $stmtCliente->bindParam(':alergias', $this->alergias);

            $stmtCliente->execute();

            $this->conexionMD->commit();

            return true;

        } catch (\Exception $e) {

            $this->conexionMD->rollBack();

            throw $e;
        }
    }

    /* Buscar: obtiene un cliente específico por su cédula */
    public function buscar()
    {
        $sql = "SELECT
                    c.id_cliente,
                    p.id_persona,
                    p.cedula,
                    p.nombre,
                    p.apellido,
                    p.telefono,
                    p.correo,
                    p.direccion,
                    p.ciudad,
                    c.alergias,
                    c.estado_cliente AS estado,
                    (
                        SELECT COUNT(*)
                        FROM agendamientos a
                        INNER JOIN estados_agendamientos ea
                            ON ea.id_estado_agendamiento = a.id_estado_agendamiento
                        WHERE a.id_cliente = c.id_cliente
                        AND ea.nombre_estado = 'REALIZADO'
                    ) AS visitas
                FROM clientes c
                INNER JOIN personas p
                    ON p.id_persona = c.id_persona
                WHERE p.cedula = :cedula
                AND c.estado_cliente = 'ACTIVO'
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':cedula', $this->cedula);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* Modificar: actualiza los datos de la persona y del cliente */
    public function modificar()
    {
        try {
            $this->conexionMD->beginTransaction();

            $sqlPersona = "UPDATE personas SET
                                nombre = :nombre,
                                apellido = :apellido,
                                telefono = :telefono,
                                correo = :correo,
                                direccion = :direccion,
                                ciudad = :ciudad
                           WHERE cedula = :cedula";

            $stmtPersona = $this->conexionMD->prepare($sqlPersona);

            $stmtPersona->bindParam(':cedula', $this->cedula);
            $stmtPersona->bindParam(':nombre', $this->nombre);
            $stmtPersona->bindParam(':apellido', $this->apellido);
            $stmtPersona->bindParam(':telefono', $this->telefono);
            $stmtPersona->bindParam(':correo', $this->correo);
            $stmtPersona->bindParam(':direccion', $this->direccion);
            $stmtPersona->bindParam(':ciudad', $this->ciudad);

            $stmtPersona->execute();

            $sqlCliente = "UPDATE clientes c
                           INNER JOIN personas p
                               ON p.id_persona = c.id_persona
                           SET c.alergias = :alergias
                           WHERE p.cedula = :cedula";

            $stmtCliente = $this->conexionMD->prepare($sqlCliente);

            $stmtCliente->bindParam(':cedula', $this->cedula);
            $stmtCliente->bindParam(':alergias', $this->alergias);

            $stmtCliente->execute();

            $this->conexionMD->commit();

            return true;

        } catch (\Exception $e) {

            $this->conexionMD->rollBack();

            throw $e;
        }
    }

    /* Eliminar: cambia el estado del cliente a INACTIVO */
    public function eliminar()
    {
        $sql = "UPDATE clientes c
                INNER JOIN personas p
                    ON p.id_persona = c.id_persona
                SET c.estado_cliente = 'INACTIVO'
                WHERE p.cedula = :cedula";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':cedula', $this->cedula);

        return $stmt->execute();
    }
}