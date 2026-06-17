<?php

namespace App\Model;

use App\Config\Database;
use PDO;

class ClientesM extends Database
{
    /* Atributo de conexión del modelo */
    private $conexionMD;
    
    /* Atributos de la tabla clientes */
    private $cedula;
    private $nombre;
    private $apellido;
    private $telefono;
    private $correo;
    private $direccion;
    private $ciudad;
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

    /* Consultar: obtiene los clientes activos */
    public function consultar()
    {
        $sql = "SELECT * FROM clientes WHERE estado = 'Activo'";
        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Registrar: inserta un nuevo cliente */
    public function registrar()
    {
        $sql = "INSERT INTO clientes (cedula, nombre, apellido, telefono, correo, direccion, ciudad, alergias, estado) 
                VALUES (:cedula, :nombre, :apellido, :telefono, :correo, :direccion, :ciudad, :alergias, 'Activo')";
        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':cedula', $this->cedula);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->bindParam(':direccion', $this->direccion);
        $stmt->bindParam(':ciudad', $this->ciudad);
        $stmt->bindParam(':alergias', $this->alergias);
        return $stmt->execute();
    }

    /* Buscar: obtiene un cliente específico por su cédula */
    public function buscar()
    {
        $sql = "SELECT * FROM clientes WHERE cedula = :cedula AND estado = 'Activo' LIMIT 1";
        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':cedula', $this->cedula);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* Modificar: actualiza los datos de un cliente específico */
    public function modificar()
    {
        $sql = "UPDATE clientes SET 
            nombre = :nombre,
            apellido = :apellido,
            telefono = :telefono,
            correo = :correo,
            direccion = :direccion,
            ciudad = :ciudad,
            alergias = :alergias
            WHERE cedula = :cedula";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':cedula', $this->cedula);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->bindParam(':direccion', $this->direccion);
        $stmt->bindParam(':ciudad', $this->ciudad);
        $stmt->bindParam(':alergias', $this->alergias);

        return $stmt->execute();
    }

    /* Eliminar: cambia el estado de un cliente a 'Inactivo' */
    public function eliminar()
    {
        $sql = "UPDATE clientes SET estado = 'Inactivo' WHERE cedula = :cedula";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':cedula', $this->cedula);

        return $stmt->execute();
    }
}