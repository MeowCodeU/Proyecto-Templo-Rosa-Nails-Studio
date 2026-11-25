<?php

namespace App\Model;

use App\Interfaces\CrudInterface;
use App\Traits\ValidacionTrait;
use PDO;


/* ===================================================== */
/* MODELO DE PROVEEDORES */
/* ===================================================== */

class ProveedoresM extends PersonasM implements CrudInterface
{
    use ValidacionTrait;


    /* ================================================= */
    /* ATRIBUTOS */
    /* ================================================= */

    private $idProveedor;
    private $idPersonaContacto;
    private $rif;
    private $nombreEmpresa;


    /* ================================================= */
    /* SETTERS */
    /* ================================================= */

    public function set_idProveedor($idProveedor) { $this->idProveedor = $idProveedor; }
    public function set_idPersonaContacto($idPersonaContacto) { $this->idPersonaContacto = $idPersonaContacto; }
    public function set_rif($rif) { $this->rif = $rif; }
    public function set_nombreEmpresa($nombreEmpresa) { $this->nombreEmpresa = $nombreEmpresa; }


    /* ================================================= */
    /* VALIDAR LA IDENTIFICACIÓN DEL CONTACTO */
    /* ================================================= */

    public function validarIdentificacion(): bool
    {
        if (!$this->validarCampos(['cedula' => $this->cedula], ['cedula'])) return false;

        $this->cedula = trim($this->cedula);

        return true;
    }


    /* ================================================= */
    /* VALIDAR Y NORMALIZAR LOS DATOS DEL PROVEEDOR */
    /* ================================================= */

    private function validarDatosProveedor(bool $esRegistro, bool $soloContacto = false): bool
    {
        $datos = [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad
        ];

        $obligatorios = ['nombre', 'apellido'];

        if ($esRegistro || $soloContacto) {
            $datos['cedula'] = $this->cedula;
            $obligatorios[] = 'cedula';
        }

        if (!$soloContacto) {
            $datos['rif'] = $this->rif;
            $datos['nombre_empresa'] = $this->nombreEmpresa;
            $obligatorios[] = 'rif';
            $obligatorios[] = 'nombre_empresa';
        }

        $this->validarCampos($datos, $obligatorios);

        if (!$esRegistro) {
            $this->idProveedor = $this->validarIdentificador($this->idProveedor, 'id_proveedor', 'Seleccione un proveedor válido.');
            $this->idPersonaContacto = $this->validarIdentificador($this->idPersonaContacto, 'id_persona_contacto', 'Seleccione una persona de contacto válida.');
        }

        if ($this->erroresValidacion) return false;

        foreach ($datos as $campo => $valor) {
            $atributo = $campo === 'nombre_empresa' ? 'nombreEmpresa' : $campo;
            $this->$atributo = trim($valor ?? '');
        }

        return true;
    }


    /* ================================================= */
    /* RECIBIR LA SELECCIÓN DE INSUMOS */
    /* ================================================= */

    public function obtenerSeleccionInsumos(array $datos, bool $esModificacion): ?array
    {
        $this->erroresValidacion = [];
        $incluyeSeleccion = array_key_exists('insumos_presentes', $datos);
        $incluyeInsumos = array_key_exists('id_insumos', $datos);

        if ($incluyeSeleccion && !in_array($datos['insumos_presentes'], ['1', 1, true], true)) {
            $mensaje = 'La selección de insumos no es válida.';
            $this->erroresValidacion['id_insumos'] = $mensaje;
            throw new \InvalidArgumentException($mensaje);
        }

        /* Conservar las asociaciones cuando un formulario antiguo no las envía */
        if (!$incluyeSeleccion && !$incluyeInsumos) return $esModificacion ? null : [];

        $seleccion = $this->validarSeleccionInsumos($datos['id_insumos'] ?? []);

        return array_values($seleccion);
    }


    /* ================================================= */
    /* CONSULTAR PROVEEDORES */
    /* ================================================= */

    public function consultar(mixed $estado = 'ACTIVO'): array
    {
        $estado = $this->validarFiltroEstado($estado);
        $condicionEstado = $estado === 'TODOS' ? '' : 'WHERE pr.estado_proveedor = :estado';

        /* Obtener los proveedores del estado elegido y los datos de sus contactos */
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

                $condicionEstado

                ORDER BY
                    pr.nombre_empresa,
                    p.nombre,
                    p.apellido";

        $stmt = $this->conexionMD->prepare($sql);

        if ($estado !== 'TODOS') {
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* REGISTRAR PROVEEDOR */
    /* ================================================= */

    public function registrar(array $idsInsumos = []): bool
    {
        if (!$this->validarDatosProveedor(true)) return false;

        /* Comprobar la selección antes de crear el proveedor */
        $insumosSeleccionados = $this->validarSeleccionInsumos($idsInsumos);

        try {
            $this->conexionMD->beginTransaction();

            /* Reconocer el RIF antes de modificar los datos de la persona de contacto */
            $sqlExistente = "SELECT estado_proveedor
                             FROM proveedores
                             WHERE rif = :rif
                             LIMIT 1
                             FOR UPDATE";

            $stmtExistente = $this->conexionMD->prepare($sqlExistente);
            $stmtExistente->bindValue(':rif', $this->rif, PDO::PARAM_STR);
            $stmtExistente->execute();

            $estadoExistente = $stmtExistente->fetchColumn();

            if ($estadoExistente !== false) {
                $this->erroresValidacion['rif'] = $estadoExistente === 'INACTIVO'
                    ? 'Este proveedor está inactivo. Seleccione Inactivos en el listado y utilice Reactivar.'
                    : 'Este RIF ya está registrado como proveedor.';

                $this->conexionMD->rollBack();
                return false;
            }

            /* Reutilizar o registrar a la persona de contacto */
            $idPersonaContacto = $this->obtenerORegistrarPersona();

            /* Registrar el proveedor relacionado con la persona de contacto */
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

            $stmtProveedor = $this->conexionMD->prepare($sqlProveedor);

            $stmtProveedor->bindValue(':id_persona_contacto', $idPersonaContacto, PDO::PARAM_INT);
            $stmtProveedor->bindParam(':rif', $this->rif);
            $stmtProveedor->bindParam(':nombre_empresa', $this->nombreEmpresa);

            $stmtProveedor->execute();
            $idProveedor = (int) $this->conexionMD->lastInsertId();

            /* Guardar los insumos seleccionados en la misma transacción */
            $this->sincronizarInsumosEnTransaccion($idProveedor, $insumosSeleccionados);

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
    /* MODIFICAR PROVEEDOR */
    /* ================================================= */

    public function modificar(?array $idsInsumos = null): bool
    {
        if (!$this->validarDatosProveedor(false)) return false;

        /* NULL conserva las asociaciones hasta adaptar el controlador anterior */
        $insumosSeleccionados = $idsInsumos === null
            ? null
            : $this->validarSeleccionInsumos($idsInsumos);

        try {
            $this->conexionMD->beginTransaction();

            /* Comprobar que se está editando al contacto actual */
            $sqlBuscarContacto = "SELECT id_persona_contacto
                                 FROM proveedores
                                 WHERE id_proveedor = :id_proveedor
                                     AND estado_proveedor = 'ACTIVO'
                                 LIMIT 1
                                 FOR UPDATE";

            $stmtBuscarContacto = $this->conexionMD->prepare($sqlBuscarContacto);
            $stmtBuscarContacto->bindValue(':id_proveedor', $this->idProveedor, PDO::PARAM_INT);
            $stmtBuscarContacto->execute();

            $idContactoActual = $stmtBuscarContacto->fetchColumn();

            if ($idContactoActual === false) {
                $this->conexionMD->rollBack();
                return false;
            }

            if ((int) $idContactoActual !== (int) $this->idPersonaContacto) {
                throw new \InvalidArgumentException('La persona de contacto cambió. Actualice el listado antes de editar.');
            }

            $this->actualizarPersona((int) $idContactoActual);

            /* Actualizar los datos propios del proveedor */
            $sqlProveedor = "UPDATE proveedores
                             SET rif = :rif,
                                 nombre_empresa = :nombre_empresa
                             WHERE id_proveedor = :id_proveedor
                                 AND estado_proveedor = 'ACTIVO'";

            $stmtProveedor = $this->conexionMD->prepare($sqlProveedor);

            $stmtProveedor->bindParam(':rif', $this->rif);
            $stmtProveedor->bindParam(':nombre_empresa', $this->nombreEmpresa);
            $stmtProveedor->bindParam(':id_proveedor', $this->idProveedor);

            $stmtProveedor->execute();

            /* Las casillas modifican las asociaciones dentro de la misma operación */
            if ($insumosSeleccionados !== null) {
                $this->sincronizarInsumosEnTransaccion((int) $this->idProveedor, $insumosSeleccionados);
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
    /* CAMBIAR PERSONA DE CONTACTO */
    /* ================================================= */

    public function cambiarContacto(): bool
    {
        if (!$this->validarDatosProveedor(false, true)) return false;

        try {
            $this->conexionMD->beginTransaction();

            /* Comprobar que la empresa conserva el contacto seleccionado */
            $sqlBuscarContacto = "SELECT id_persona_contacto
                                 FROM proveedores
                                 WHERE id_proveedor = :id_proveedor
                                     AND estado_proveedor = 'ACTIVO'
                                 LIMIT 1
                                 FOR UPDATE";

            $stmtBuscarContacto = $this->conexionMD->prepare($sqlBuscarContacto);
            $stmtBuscarContacto->bindValue(':id_proveedor', $this->idProveedor, PDO::PARAM_INT);
            $stmtBuscarContacto->execute();

            $idContactoActual = $stmtBuscarContacto->fetchColumn();

            if ($idContactoActual === false) {
                $this->conexionMD->rollBack();
                return false;
            }

            if ((int) $idContactoActual !== (int) $this->idPersonaContacto) {
                throw new \InvalidArgumentException('La persona de contacto cambió. Actualice el listado antes de continuar.');
            }

            $persona = $this->obtenerPersonaPorCedula($this->cedula);

            if ($persona !== null && (int) $persona['id_persona'] === (int) $idContactoActual) {
                throw new \InvalidArgumentException('La persona seleccionada ya es el contacto de este proveedor.');
            }

            $idNuevoContacto = $this->obtenerORegistrarPersona();

            /* Cambiar la relación sin eliminar a la persona anterior */
            $sqlProveedor = "UPDATE proveedores
                             SET id_persona_contacto = :id_persona_contacto
                             WHERE id_proveedor = :id_proveedor
                                 AND estado_proveedor = 'ACTIVO'";

            $stmtProveedor = $this->conexionMD->prepare($sqlProveedor);
            $stmtProveedor->bindValue(':id_persona_contacto', $idNuevoContacto, PDO::PARAM_INT);
            $stmtProveedor->bindValue(':id_proveedor', $this->idProveedor, PDO::PARAM_INT);
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


    /* ================================================= */
    /* CONSULTAR INSUMOS DISPONIBLES */
    /* ================================================= */

    public function consultarInsumosDisponibles(): array
    {
        /* Mostrar los insumos activos para el registro de proveedores */
        $sql = "SELECT
                    id_insumo,
                    nombre_insumo,
                    presentacion
                FROM insumos
                WHERE estado_insumo = 'ACTIVO'
                ORDER BY nombre_insumo ASC, id_insumo ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* CONSULTAR INSUMOS DEL PROVEEDOR */
    /* ================================================= */

    public function consultarInsumosProveedor(mixed $idProveedor, bool $incluirInactivos = false): array
    {
        $this->erroresValidacion = [];
        $idProveedor = $this->validarIdentificador($idProveedor, 'id_proveedor', 'Seleccione un proveedor válido.');

        if ($idProveedor === null) {
            throw new \InvalidArgumentException('Seleccione un proveedor válido.');
        }

        $condicionEstado = $incluirInactivos ? '' : "AND estado_proveedor = 'ACTIVO'";

        /* Permitir consultar las asociaciones de un inactivo únicamente desde Detalles */
        $sql = "SELECT id_proveedor
                FROM proveedores
                WHERE id_proveedor = :id_proveedor
                    $condicionEstado
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_proveedor', $idProveedor, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn() === false) {
            throw new \InvalidArgumentException('El proveedor no está disponible para esta consulta. Actualice el listado.');
        }

        /* Consultar el catálogo e identificar los insumos asociados */
        $sql = "SELECT
                    insumo.id_insumo,
                    insumo.nombre_insumo,
                    insumo.presentacion,
                    CASE WHEN relacion.id_proveedor_insumo IS NULL THEN 0 ELSE 1 END AS seleccionado
                FROM insumos AS insumo
                LEFT JOIN proveedores_insumos AS relacion
                    ON relacion.id_insumo = insumo.id_insumo
                    AND relacion.id_proveedor = :id_proveedor
                WHERE insumo.estado_insumo = 'ACTIVO'
                ORDER BY insumo.nombre_insumo ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_proveedor', $idProveedor, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* SINCRONIZAR INSUMOS EN UNA TRANSACCIÓN */
    /* ================================================= */

    private function sincronizarInsumosEnTransaccion(int $idProveedor, array $insumosSeleccionados): array
    {
        /* Este método forma parte de la transacción de Registrar o Modificar */
        if (!$this->conexionMD->inTransaction()) {
            throw new \LogicException('La asociación de insumos requiere una transacción activa.');
        }

        /* Bloquear el proveedor para impedir modificaciones concurrentes */
        $sql = "SELECT id_proveedor
                FROM proveedores
                WHERE id_proveedor = :id_proveedor
                    AND estado_proveedor = 'ACTIVO'
                LIMIT 1
                FOR UPDATE";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_proveedor', $idProveedor, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn() === false) {
            throw new \InvalidArgumentException('El proveedor no se encuentra activo. Actualice el listado.');
        }

        /* Admitir únicamente insumos activos existentes */
        if ($insumosSeleccionados) {
            $marcadores = implode(', ', array_fill(0, count($insumosSeleccionados), '?'));

            $sql = "SELECT id_insumo
                    FROM insumos
                    WHERE estado_insumo = 'ACTIVO'
                        AND id_insumo IN ($marcadores)
                    ORDER BY id_insumo ASC
                    FOR UPDATE";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->execute(array_values($insumosSeleccionados));

            $insumosValidos = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

            if (count($insumosValidos) !== count($insumosSeleccionados)) {
                throw new \InvalidArgumentException('Uno de los insumos ya no está disponible. Actualice la selección.');
            }
        }

        /* Conservar las asociaciones históricas de insumos inactivos */
        $sql = "SELECT relacion.id_insumo
                FROM proveedores_insumos AS relacion
                INNER JOIN insumos AS insumo
                    ON insumo.id_insumo = relacion.id_insumo
                WHERE relacion.id_proveedor = :id_proveedor
                    AND insumo.estado_insumo = 'ACTIVO'
                ORDER BY relacion.id_insumo ASC
                FOR UPDATE";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_proveedor', $idProveedor, PDO::PARAM_INT);
        $stmt->execute();

        $insumosAnteriores = array_fill_keys(
            array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)),
            true
        );

        $agregar = array_diff_key($insumosSeleccionados, $insumosAnteriores);
        $retirar = array_diff_key($insumosAnteriores, $insumosSeleccionados);

        /* Retirar solamente las asociaciones que se desmarcaron */
        $sqlEliminar = "DELETE FROM proveedores_insumos
                       WHERE id_proveedor = :id_proveedor
                           AND id_insumo = :id_insumo";

        $stmtEliminar = $this->conexionMD->prepare($sqlEliminar);

        foreach (array_keys($retirar) as $idInsumo) {
            $stmtEliminar->bindValue(':id_proveedor', $idProveedor, PDO::PARAM_INT);
            $stmtEliminar->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
            $stmtEliminar->execute();
        }

        /* Agregar nuevas asociaciones sin alterar existencias ni lotes */
        $sqlAgregar = "INSERT INTO proveedores_insumos (id_proveedor, id_insumo)
                       VALUES (:id_proveedor, :id_insumo)";

        $stmtAgregar = $this->conexionMD->prepare($sqlAgregar);

        foreach ($agregar as $idInsumo) {
            $stmtAgregar->bindValue(':id_proveedor', $idProveedor, PDO::PARAM_INT);
            $stmtAgregar->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
            $stmtAgregar->execute();
        }

        return [
            'agregados' => count($agregar),
            'retirados' => count($retirar)
        ];
    }


    /* ================================================= */
    /* ACTUALIZAR INSUMOS DEL PROVEEDOR */
    /* ================================================= */

    public function actualizarInsumosProveedor(mixed $idProveedor, mixed $idsInsumos): array
    {
        $this->erroresValidacion = [];
        $idProveedor = $this->validarIdentificador($idProveedor, 'id_proveedor', 'Seleccione un proveedor válido.');

        if ($idProveedor === null) {
            throw new \InvalidArgumentException('Seleccione un proveedor válido.');
        }

        /* Mantener compatibilidad con el controlador mientras se actualiza la vista */
        $insumosSeleccionados = $this->validarSeleccionInsumos($idsInsumos);

        $this->conexionMD->beginTransaction();

        try {
            $cambios = $this->sincronizarInsumosEnTransaccion($idProveedor, $insumosSeleccionados);
            $this->conexionMD->commit();

            return $cambios;

        } catch (\Throwable $e) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $e;
        }
    }


    /* ================================================= */
    /* DESACTIVAR PROVEEDOR */
    /* ================================================= */

    public function eliminar(): bool
    {
        return $this->cambiarEstadoProveedor('ACTIVO', 'INACTIVO');
    }


    /* ================================================= */
    /* REACTIVAR PROVEEDOR */
    /* ================================================= */

    public function reactivar(): bool
    {
        return $this->cambiarEstadoProveedor('INACTIVO', 'ACTIVO');
    }


    /* ================================================= */
    /* ACTUALIZAR EL ESTADO DEL PROVEEDOR */
    /* ================================================= */

    private function cambiarEstadoProveedor(string $estadoActual, string $estadoNuevo): bool
    {
        $this->erroresValidacion = [];
        $this->idProveedor = $this->validarIdentificador($this->idProveedor, 'id_proveedor', 'Seleccione un proveedor válido.');

        if ($this->idProveedor === null) return false;

        /* Conservar la empresa, su contacto, sus insumos y sus relaciones históricas */
        $sql = "UPDATE proveedores
                SET estado_proveedor = :estado_nuevo
                WHERE id_proveedor = :id_proveedor
                    AND estado_proveedor = :estado_actual";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_proveedor', $this->idProveedor, PDO::PARAM_INT);
        $stmt->bindValue(':estado_actual', $estadoActual, PDO::PARAM_STR);
        $stmt->bindValue(':estado_nuevo', $estadoNuevo, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() !== 1) {
            throw new \DomainException(
                'El proveedor ya no se encuentra en el estado esperado. Actualice el listado.'
            );
        }

        return true;
    }
}