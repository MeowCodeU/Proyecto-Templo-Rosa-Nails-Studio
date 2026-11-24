<?php

namespace App\Model;

use App\Interfaces\CrudInterface;
use App\Traits\ValidacionTrait;
use PDO;


/* ===================================================== */
/* MODELO DE CLIENTES */
/* ===================================================== */

class ClientesM extends PersonasM implements CrudInterface
{
    use ValidacionTrait;


    /* ================================================= */
    /* ATRIBUTOS */
    /* ================================================= */

    private $alergias;


    /* ================================================= */
    /* SETTERS */
    /* ================================================= */

    public function set_Alergias($alergias) { $this->alergias = $alergias; }


    /* ================================================= */
    /* VALIDAR LA IDENTIFICACIÓN DEL CLIENTE */
    /* ================================================= */

    public function validarIdentificacion(): bool
    {
        if (!$this->validarCampos(['cedula' => $this->cedula], ['cedula'])) return false;

        $this->cedula = trim($this->cedula);

        return true;
    }


    /* ================================================= */
    /* VALIDAR Y NORMALIZAR LOS DATOS DEL CLIENTE */
    /* ================================================= */

    private function validarDatosCliente(): bool
    {
        $datos = [
            'cedula' => $this->cedula,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'ciudad' => $this->ciudad,
            'direccion' => $this->direccion,
            'alergias' => $this->alergias
        ];

        if (!$this->validarCampos($datos, ['cedula', 'nombre', 'apellido'])) return false;

        foreach ($datos as $campo => $valor) {
            $this->$campo = trim($valor ?? '');
        }

        return true;
    }


    /* ================================================= */
    /* CONSULTAR CLIENTES */
    /* ================================================= */

    public function consultar(mixed $estado = 'ACTIVO'): array
    {
        $estado = $this->validarFiltroEstado($estado);

        /* Filtrar los clientes sin alterar sus datos ni su historial */
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
                            ON ea.id_estado_agendamiento =
                                a.id_estado_agendamiento

                        WHERE a.id_cliente = c.id_cliente
                            AND ea.nombre_estado = 'REALIZADA'
                    ) AS visitas

                FROM clientes c

                INNER JOIN personas p
                    ON p.id_persona = c.id_persona

                WHERE (:todos = 1 OR c.estado_cliente = :estado)

                ORDER BY p.nombre, p.apellido";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':todos', $estado === 'TODOS' ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* REGISTRAR CLIENTE */
    /* ================================================= */

    public function registrar(): bool
    {
        if (!$this->validarDatosCliente()) return false;

        try {
            $this->conexionMD->beginTransaction();

            /* Reconocer también los clientes inactivos antes de reutilizar sus datos */
            $clienteExistente = $this->buscar(true);

            if ($clienteExistente) {
                $this->erroresValidacion['cedula'] = $clienteExistente['estado'] === 'INACTIVO'
                    ? 'Este cliente está inactivo. Seleccione Inactivos en el listado y utilice Reactivar.'
                    : 'Esta cédula ya está registrada como cliente.';

                $this->conexionMD->rollBack();
                return false;
            }

            /* Reutilizar los datos personales existentes o registrar una nueva persona */
            $idPersona = $this->obtenerORegistrarPersona();

            /* Registrar el cliente relacionado con la persona */
            $sqlCliente = "INSERT INTO clientes (
                                id_persona,
                                alergias,
                                estado_cliente
                            ) VALUES (
                                :id_persona,
                                :alergias,
                                'ACTIVO'
                            )";

            $stmtCliente = $this->conexionMD->prepare($sqlCliente);

            $stmtCliente->bindValue(':id_persona', $idPersona, PDO::PARAM_INT);
            $stmtCliente->bindParam(':alergias', $this->alergias);

            $stmtCliente->execute();

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
    /* BUSCAR CLIENTE */
    /* ================================================= */

    public function buscar(bool $incluirInactivos = false)
    {
        if (!$this->validarIdentificacion()) return false;

        /* Incluir inactivos únicamente cuando la operación lo solicita */
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
                            ON ea.id_estado_agendamiento =
                                a.id_estado_agendamiento

                        WHERE a.id_cliente = c.id_cliente
                            AND ea.nombre_estado = 'REALIZADA'
                    ) AS visitas

                FROM clientes c

                INNER JOIN personas p
                    ON p.id_persona = c.id_persona

                WHERE p.cedula = :cedula
                    AND (:incluir_inactivos = 1 OR c.estado_cliente = 'ACTIVO')

                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':cedula', $this->cedula);
        $stmt->bindValue(':incluir_inactivos', $incluirInactivos ? 1 : 0, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* CONSULTAR EL HISTORIAL DE SERVICIOS */
    /* ================================================= */

    public function consultarHistorial(int $idCliente): array
    {
        if ($idCliente <= 0) {
            throw new \InvalidArgumentException(
                'El identificador del cliente no es válido.'
            );
        }

        /* Recuperar cada servicio de las atenciones realizadas del cliente */
        $sql = "SELECT
                    d.id_detalle_agendamiento,
                    a.id_agendamiento,
                    a.fecha,
                    a.hora,
                    s.nombre_servicio,
                    TRIM(
                        CONCAT(
                            COALESCE(p.nombre, ''),
                            ' ',
                            COALESCE(p.apellido, '')
                        )
                    ) AS especialista,
                    d.precio_aplicado,
                    d.nota_tecnica

                FROM agendamientos a

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento =
                        a.id_estado_agendamiento

                INNER JOIN detalles_agendamientos d
                    ON d.id_agendamiento = a.id_agendamiento

                INNER JOIN servicios s
                    ON s.id_servicio = d.id_servicio

                LEFT JOIN usuarios u
                    ON u.id_usuario = a.id_usuario_asignado

                LEFT JOIN personas p
                    ON p.id_persona = u.id_persona

                WHERE a.id_cliente = :id_cliente
                    AND ea.nombre_estado = 'REALIZADA'

                ORDER BY
                    a.fecha DESC,
                    a.hora DESC,
                    a.id_agendamiento DESC,
                    d.id_detalle_agendamiento ASC";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':id_cliente', $idCliente, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* MODIFICAR CLIENTE */
    /* ================================================= */

    public function modificar(): bool
    {
        if (!$this->validarDatosCliente()) return false;

        try {
            $this->conexionMD->beginTransaction();

            /* Identificar la persona relacionada con el cliente activo */
            $sqlPersona = "SELECT
                                c.id_cliente,
                                c.id_persona

                            FROM clientes c

                            INNER JOIN personas p
                                ON p.id_persona = c.id_persona

                            WHERE p.cedula = :cedula
                                AND c.estado_cliente = 'ACTIVO'

                            LIMIT 1
                            FOR UPDATE";

            $stmtPersona = $this->conexionMD->prepare($sqlPersona);
            $stmtPersona->bindValue(':cedula', (string) $this->cedula, PDO::PARAM_STR);
            $stmtPersona->execute();

            $cliente = $stmtPersona->fetch(PDO::FETCH_ASSOC);

            if (!$cliente) {
                $this->conexionMD->rollBack();
                return false;
            }

            /* Actualizar los datos personales mediante el modelo base */
            $this->actualizarPersona((int) $cliente['id_persona']);

            /* Actualizar las alergias del cliente relacionado */
            $sqlCliente = "UPDATE clientes

                           SET alergias = :alergias

                           WHERE id_cliente = :id_cliente";

            $stmtCliente = $this->conexionMD->prepare($sqlCliente);

            $stmtCliente->bindParam(':alergias', $this->alergias);
            $stmtCliente->bindValue(':id_cliente', (int) $cliente['id_cliente'], PDO::PARAM_INT);

            $stmtCliente->execute();

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
    /* DESACTIVAR CLIENTE */
    /* ================================================= */

    public function eliminar(): bool
    {
        return $this->cambiarEstadoCliente('ACTIVO', 'INACTIVO');
    }


    /* ================================================= */
    /* REACTIVAR CLIENTE */
    /* ================================================= */

    public function reactivar(): bool
    {
        return $this->cambiarEstadoCliente('INACTIVO', 'ACTIVO');
    }


    /* ================================================= */
    /* ACTUALIZAR EL ESTADO DEL CLIENTE */
    /* ================================================= */

    private function cambiarEstadoCliente(string $estadoActual, string $estadoNuevo): bool
    {
        if (!$this->validarIdentificacion()) return false;

        /* Cambiar únicamente el estado y conservar la identidad y las relaciones */
        $sql = "UPDATE clientes

                SET estado_cliente = :estado_nuevo

                WHERE id_persona = (
                    SELECT id_persona
                    FROM personas
                    WHERE cedula = :cedula
                )
                    AND estado_cliente = :estado_actual";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':cedula', $this->cedula, PDO::PARAM_STR);
        $stmt->bindValue(':estado_actual', $estadoActual, PDO::PARAM_STR);
        $stmt->bindValue(':estado_nuevo', $estadoNuevo, PDO::PARAM_STR);

        $stmt->execute();

        if ($stmt->rowCount() !== 1) {
            throw new \DomainException(
                'El cliente ya no se encuentra en el estado esperado. Actualice la lista.'
            );
        }

        return true;
    }
}