<?php

namespace App\Model;

use App\Config\Database;
use PDO;


/* ===================================================== */
/* MODELO BASE DE PERSONAS */
/* ===================================================== */

abstract class PersonasM extends Database
{
    /* ================================================= */
    /* CONEXIÓN Y ATRIBUTOS */
    /* ================================================= */

    /* Conexión compartida con los modelos relacionados con personas */
    protected PDO $conexionMD;

    /* Atributos de la tabla personas */
    protected $cedula;
    protected $nombre;
    protected $apellido;
    protected $telefono;
    protected $correo;
    protected $direccion;
    protected $ciudad;


    /* ================================================= */
    /* SETTERS */
    /* ================================================= */

    public function set_Cedula($cedula) { $this->cedula = $cedula; }
    public function set_Nombre($nombre) { $this->nombre = $nombre; }
    public function set_Apellido($apellido) { $this->apellido = $apellido; }
    public function set_Telefono($telefono) { $this->telefono = $telefono; }
    public function set_Correo($correo) { $this->correo = $correo; }
    public function set_Direccion($direccion) { $this->direccion = $direccion; }
    public function set_Ciudad($ciudad) { $this->ciudad = $ciudad; }


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* ================================================= */
    /* BUSCAR PERSONA POR CÉDULA */
    /* ================================================= */

    protected function obtenerPersonaPorCedula(string $cedula): ?array
    {
        $sql = "SELECT
                    id_persona,
                    cedula,
                    nombre,
                    apellido,
                    telefono,
                    correo,
                    direccion,
                    ciudad
                FROM personas
                WHERE cedula = :cedula
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':cedula', $cedula, PDO::PARAM_STR);
        $stmt->execute();

        $persona = $stmt->fetch(PDO::FETCH_ASSOC);

        return $persona ?: null;
    }


    /* ================================================= */
    /* CONSULTAR DATOS PERSONALES */
    /* ================================================= */

    public function consultarDatosPersona(string $cedula): ?array
    {
        $cedula = trim($cedula);

        if (!preg_match('/\A[0-9]{6,20}\z/', $cedula)) {
            throw new \InvalidArgumentException('La cédula debe contener entre 6 y 20 dígitos.');
        }

        $persona = $this->obtenerPersonaPorCedula($cedula);

        if ($persona === null) return null;

        return [
            'cedula' => $persona['cedula'],
            'nombre' => $persona['nombre'],
            'apellido' => $persona['apellido'],
            'telefono' => $persona['telefono'],
            'correo' => $persona['correo'],
            'direccion' => $persona['direccion'],
            'ciudad' => $persona['ciudad']
        ];
    }


    /* ================================================= */
    /* REGISTRAR PERSONA */
    /* ================================================= */

    protected function registrarPersona(): int
    {
        $sql = "INSERT INTO personas (
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

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':cedula', $this->cedula);
        $stmt->bindValue(':nombre', $this->nombre);
        $stmt->bindValue(':apellido', $this->apellido);
        $stmt->bindValue(':telefono', $this->telefono);
        $stmt->bindValue(':correo', $this->correo);
        $stmt->bindValue(':direccion', $this->direccion);
        $stmt->bindValue(':ciudad', $this->ciudad);
        $stmt->execute();

        return (int) $this->conexionMD->lastInsertId();
    }


    /* ================================================= */
    /* ACTUALIZAR DATOS PERSONALES */
    /* ================================================= */

    protected function actualizarPersona(int $idPersona, bool $conservarVacios = false): void
    {
        $datos = [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad
        ];

        /* Evitar borrar datos opcionales existentes al reutilizar una persona */
        if ($conservarVacios) {
            foreach (['telefono', 'correo', 'direccion', 'ciudad'] as $campo) {
                if ($datos[$campo] === null || trim((string) $datos[$campo]) === '') {
                    unset($datos[$campo]);
                }
            }
        }

        $asignaciones = [];

        foreach (array_keys($datos) as $campo) {
            $asignaciones[] = $campo . ' = :' . $campo;
        }

        $sql = "UPDATE personas
                SET " . implode(', ', $asignaciones) . "
                WHERE id_persona = :id_persona";

        $stmt = $this->conexionMD->prepare($sql);

        foreach ($datos as $campo => $valor) {
            $stmt->bindValue(':' . $campo, $valor);
        }

        $stmt->bindValue(':id_persona', $idPersona, PDO::PARAM_INT);
        $stmt->execute();
    }


    /* ================================================= */
    /* REGISTRAR O REUTILIZAR UNA PERSONA */
    /* ================================================= */

    protected function obtenerORegistrarPersona(): int
    {
        $persona = $this->obtenerPersonaPorCedula((string) $this->cedula);

        if ($persona !== null) {
            $idPersona = (int) $persona['id_persona'];
            $this->actualizarPersona($idPersona, true);

            return $idPersona;
        }

        return $this->registrarPersona();
    }


    /* ================================================= */
    /* ACTUALIZAR CONTACTO DESDE PERFIL */
    /* ================================================= */

    protected function actualizarContactoPersona(
        int $idPersona,
        string $telefono,
        string $correo
    ): void {
        $sql = "UPDATE personas
                SET telefono = :telefono,
                    correo = :correo
                WHERE id_persona = :id_persona";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':telefono', $telefono, PDO::PARAM_STR);
        $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
        $stmt->bindValue(':id_persona', $idPersona, PDO::PARAM_INT);
        $stmt->execute();
    }
}