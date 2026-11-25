<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use App\Traits\ValidacionInsumosTrait;
use PDO;


/* ===================================================== */
/* MODELO DE INSUMOS */
/* ===================================================== */

class InsumosM extends Database implements CrudInterface
{
    use ValidacionInsumosTrait;


    private const STOCK_MAXIMO = 4294967295;

    /* ================================================= */
    /* CONEXIÓN Y ATRIBUTOS */
    /* ================================================= */

    /* Conexión con la base de datos */
    private $conexionMD;
    private ControlInventario $controlInventario;

    /* Atributos de la tabla insumos */
    private $idInsumo;
    private $nombreInsumo;
    private $descripcion;
    private $presentacion;
    private $tipoControl;
    private $stockActual;
    private $stockOriginal;
    private $stockMinimo;
    private $fechaVencimiento;
    private $estadoInsumo;


    /* ================================================= */
    /* SETTERS */
    /* ================================================= */

    public function set_idInsumo($idInsumo) { $this->idInsumo = $idInsumo; }
    public function set_nombreInsumo($nombreInsumo) { $this->nombreInsumo = $nombreInsumo; }
    public function set_descripcion($descripcion) { $this->descripcion = $descripcion; }
    public function set_presentacion($presentacion) { $this->presentacion = $presentacion; }
    public function set_tipoControl($tipoControl) { $this->tipoControl = $tipoControl; }
    public function set_stockActual($stockActual) { $this->stockActual = $stockActual; }
    public function set_stockOriginal($stockOriginal) { $this->stockOriginal = $stockOriginal; }
    public function set_stockMinimo($stockMinimo) { $this->stockMinimo = $stockMinimo; }
    public function set_fechaVencimiento($fechaVencimiento) { $this->fechaVencimiento = $fechaVencimiento; }
    public function set_estadoInsumo($estadoInsumo) { $this->estadoInsumo = $estadoInsumo; }


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
        $this->controlInventario = new ControlInventario();
    }


    /* ================================================= */
    /* CONSULTAR INSUMOS */
    /* ================================================= */

    public function consultar(mixed $estado = 'ACTIVO'): array
    {
        $estado = $this->validarFiltroEstado($estado);
        $condicionEstado = $estado === 'TODOS' ? '' : 'WHERE insumo.estado_insumo = :estado';

        /* Consultar el estado elegido y el vencimiento más próximo de sus lotes */
        $sql = "SELECT
                    insumo.id_insumo,
                    insumo.nombre_insumo,
                    insumo.descripcion,
                    insumo.presentacion,
                    insumo.tipo_control,
                    insumo.stock_actual,
                    insumo.stock_minimo,
                    (
                        SELECT MIN(lote.fecha_vencimiento)
                        FROM lotes_insumos AS lote
                        WHERE lote.id_insumo = insumo.id_insumo
                            AND lote.cantidad_disponible > 0
                    ) AS fecha_vencimiento,
                    insumo.estado_insumo
                FROM insumos AS insumo
                $condicionEstado
                ORDER BY insumo.nombre_insumo ASC";

        $stmt = $this->conexionMD->prepare($sql);

        if ($estado !== 'TODOS') {
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
        }

        $stmt->execute();

        $insumos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'agregarControlInventario'],
            (new ReservaInsumosM($this->conexionMD))->agregarDisponibilidad($insumos)
        );
    }


    /* ================================================= */
    /* CONSULTAR PROVEEDORES ACTIVOS */
    /* ================================================= */

    public function consultarProveedoresActivos(): array
    {
        /* Obtener las empresas disponibles para suministrar insumos */
        $sql = "SELECT
                    id_proveedor,
                    nombre_empresa
                FROM proveedores
                WHERE estado_proveedor = 'ACTIVO'
                ORDER BY nombre_empresa ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* CONSULTAR PROVEEDORES ASOCIADOS AL INSUMO */
    /* ================================================= */

    public function consultarProveedoresInsumo(mixed $idInsumo): array
    {
        $idInsumo = $this->validarIdInsumo($idInsumo);

        if ($idInsumo === null) {
            throw new \InvalidArgumentException('Seleccione un insumo válido.');
        }

        /* Comprobar que el insumo existe antes de consultar sus proveedores */
        $sql = "SELECT id_insumo
                FROM insumos
                WHERE id_insumo = :id_insumo
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn() === false) {
            throw new \InvalidArgumentException('El insumo no se encuentra registrado.');
        }

        /* Mostrar los proveedores relacionados, incluidos los desactivados */
        $sql = "SELECT
                    proveedor.id_proveedor,
                    proveedor.nombre_empresa,
                    proveedor.estado_proveedor
                FROM proveedores_insumos AS relacion
                INNER JOIN proveedores AS proveedor
                    ON proveedor.id_proveedor = relacion.id_proveedor
                WHERE relacion.id_insumo = :id_insumo
                ORDER BY proveedor.nombre_empresa ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* COMPROBAR Y ASOCIAR PROVEEDOR */
    /* ================================================= */

    private function comprobarProveedor(int $idProveedor): void
    {
        /* Comprobar que la empresa seleccionada está activa */
        $sql = "SELECT id_proveedor
                FROM proveedores
                WHERE id_proveedor = :id_proveedor
                    AND estado_proveedor = 'ACTIVO'
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_proveedor', $idProveedor, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn() === false) {
            throw new \DomainException('El proveedor seleccionado no está disponible. Actualice la página.');
        }
    }


    private function asociarProveedorInsumo(int $idInsumo, int $idProveedor): void
    {
        /* Mantener la relación sin duplicar el proveedor y el insumo */
        $sql = "INSERT INTO proveedores_insumos (
                    id_proveedor,
                    id_insumo
                ) VALUES (
                    :id_proveedor,
                    :id_insumo
                )
                ON DUPLICATE KEY UPDATE
                    id_proveedor_insumo = id_proveedor_insumo";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_proveedor', $idProveedor, PDO::PARAM_INT);
        $stmt->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
        $stmt->execute();
    }


    /* ================================================= */
    /* CONSULTAR HISTORIAL DE MOVIMIENTOS */
    /* ================================================= */

    public function consultarHistorial(
        ?string $fechaDesde = null,
        ?string $fechaHasta = null,
        ?string $tipoMovimiento = null
    ): array {
        $filtros = $this->validarFiltrosHistorial([
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'tipo_movimiento' => $tipoMovimiento
        ]);

        if ($this->erroresValidacion) {
            throw new \InvalidArgumentException(implode(' ', $this->erroresValidacion));
        }

        $fechaDesde = $filtros['fecha_desde'];
        $fechaHasta = $filtros['fecha_hasta'];
        $tipoMovimiento = $filtros['tipo_movimiento'];

        /* Mostrar también los movimientos de insumos inactivos */
        $sql = "SELECT
                    movimiento.id_movimiento,
                    movimiento.fecha_movimiento,
                    movimiento.id_insumo,
                    movimiento.id_lote,
                    insumo.nombre_insumo,
                    movimiento.tipo_movimiento,
                    movimiento.cantidad,
                    movimiento.stock_anterior,
                    movimiento.stock_resultante,
                    movimiento.motivo,
                    movimiento.id_proveedor,
                    proveedor.nombre_empresa AS nombre_proveedor,
                    lote.fecha_vencimiento AS vencimiento_lote,
                    movimiento.id_usuario,
                    COALESCE(
                        NULLIF(TRIM(CONCAT_WS(' ', persona.nombre, persona.apellido)), ''),
                        NULLIF(usuario.nickname, ''),
                        CONCAT('Usuario #', movimiento.id_usuario)
                    ) AS usuario_responsable
                FROM movimientos_insumos AS movimiento
                INNER JOIN insumos AS insumo
                    ON insumo.id_insumo = movimiento.id_insumo
                LEFT JOIN lotes_insumos AS lote
                    ON lote.id_lote = movimiento.id_lote
                LEFT JOIN proveedores AS proveedor
                    ON proveedor.id_proveedor = movimiento.id_proveedor
                LEFT JOIN usuarios AS usuario
                    ON usuario.id_usuario = movimiento.id_usuario
                LEFT JOIN personas AS persona
                    ON persona.id_persona = usuario.id_persona
                WHERE 1 = 1";

        /* Aplicar los filtros sin modificar los movimientos registrados */
        if ($fechaDesde !== null) {
            $sql .= " AND movimiento.fecha_movimiento >= :fecha_desde";
        }

        if ($fechaHasta !== null) {
            $sql .= " AND movimiento.fecha_movimiento < DATE_ADD(:fecha_hasta, INTERVAL 1 DAY)";
        }

        if ($tipoMovimiento !== null) {
            $sql .= " AND movimiento.tipo_movimiento = :tipo_movimiento";
        }

        $sql .= " ORDER BY movimiento.fecha_movimiento DESC,
                           movimiento.id_movimiento DESC";

        $stmt = $this->conexionMD->prepare($sql);

        if ($fechaDesde !== null) {
            $stmt->bindValue(':fecha_desde', $fechaDesde);
        }

        if ($fechaHasta !== null) {
            $stmt->bindValue(':fecha_hasta', $fechaHasta);
        }

        if ($tipoMovimiento !== null) {
            $stmt->bindValue(':tipo_movimiento', $tipoMovimiento);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* CONTROLAR EXISTENCIAS Y VENCIMIENTO */
    /* ================================================= */

    private function agregarControlInventario(array $insumo): array
    {
        $stock = $this->controlInventario->controlStock(
            (int) ($insumo['stock_disponible'] ?? $insumo['stock_actual']),
            (int) ($insumo['minimo_disponible'] ?? $insumo['stock_minimo'])
        );

        $vencimiento = $this->controlInventario->controlVencimiento(
            $insumo['fecha_vencimiento']
        );

        $insumo['estado_stock'] = $stock['estado'];
        $insumo['etiqueta_stock'] = $stock['etiqueta'];
        $insumo['estado_vencimiento'] = $vencimiento['estado'];
        $insumo['etiqueta_vencimiento'] = $vencimiento['etiqueta'];
        $insumo['dias_vencimiento'] = $vencimiento['dias_restantes'];

        return $insumo;
    }


    /* ================================================= */
    /* REGISTRAR INSUMO */
    /* ================================================= */

    public function registrar(?int $idProveedor = null, ?int $idUsuario = null): bool
    {
        /* Registrar únicamente el catálogo, sin ingresar todavía existencias */
        if ($idProveedor !== null) {
            throw new \InvalidArgumentException(
                'El proveedor se selecciona al agregar existencias, no al registrar el insumo.'
            );
        }

        if (!$this->validarCatalogoInsumo(false)) return false;

        /* Registrar los datos generales del insumo */
        $sql = "INSERT INTO insumos (
                    nombre_insumo,
                    descripcion,
                    presentacion,
                    tipo_control,
                    stock_actual,
                    stock_minimo,
                    estado_insumo
                ) VALUES (
                    :nombre_insumo,
                    :descripcion,
                    :presentacion,
                    :tipo_control,
                    0,
                    :stock_minimo,
                    'ACTIVO'
                )";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':nombre_insumo', $this->nombreInsumo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':presentacion', $this->presentacion);
        $stmt->bindParam(':tipo_control', $this->tipoControl);
        $stmt->bindParam(':stock_minimo', $this->stockMinimo);

        return $stmt->execute();
    }


    /* ================================================= */
    /* BUSCAR INSUMO */
    /* ================================================= */

    public function buscar()
    {
        $this->idInsumo = $this->validarIdInsumo($this->idInsumo);

        if ($this->idInsumo === null) return false;

        /* Obtener el insumo y la fecha más próxima de sus lotes disponibles */
        $sql = "SELECT
                    insumo.id_insumo,
                    insumo.nombre_insumo,
                    insumo.descripcion,
                    insumo.presentacion,
                    insumo.tipo_control,
                    insumo.stock_actual,
                    insumo.stock_minimo,
                    (
                        SELECT MIN(lote.fecha_vencimiento)
                        FROM lotes_insumos AS lote
                        WHERE lote.id_insumo = insumo.id_insumo
                            AND lote.cantidad_disponible > 0
                    ) AS fecha_vencimiento,
                    insumo.estado_insumo
                FROM insumos AS insumo
                WHERE insumo.id_insumo = :id_insumo
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindParam(':id_insumo', $this->idInsumo);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* CONSULTAR LOTES DEL INSUMO */
    /* ================================================= */

    public function consultarLotes(mixed $idInsumo): array
    {
        $idInsumo = $this->validarIdInsumo($idInsumo);

        if ($idInsumo === null) {
            throw new \InvalidArgumentException('Seleccione un insumo válido.');
        }

        /* Consultar los lotes sin alterar sus existencias */
        $sql = "SELECT
                    lote.id_lote,
                    lote.id_insumo,
                    lote.cantidad_inicial,
                    lote.cantidad_disponible,
                    lote.fecha_vencimiento,
                    lote.fecha_ingreso,
                    entrada.id_proveedor,
                    proveedor.nombre_empresa AS nombre_proveedor
                FROM lotes_insumos AS lote
                LEFT JOIN movimientos_insumos AS entrada
                    ON entrada.id_movimiento = (
                        SELECT MIN(ingreso.id_movimiento)
                        FROM movimientos_insumos AS ingreso
                        WHERE ingreso.id_lote = lote.id_lote
                            AND ingreso.tipo_movimiento = 'INCREMENTO'
                    )
                LEFT JOIN proveedores AS proveedor
                    ON proveedor.id_proveedor = entrada.id_proveedor
                WHERE lote.id_insumo = :id_insumo
                ORDER BY (lote.fecha_vencimiento IS NULL) ASC,
                         lote.fecha_vencimiento ASC,
                         lote.fecha_ingreso ASC,
                         lote.id_lote ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
        $stmt->execute();

        $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($lotes as &$lote) {
            $vencimiento = $this->controlInventario->controlVencimiento(
                $lote['fecha_vencimiento']
            );

            $lote['estado_vencimiento'] = $vencimiento['estado'];
            $lote['etiqueta_vencimiento'] = $vencimiento['etiqueta'];
            $lote['dias_vencimiento'] = $vencimiento['dias_restantes'];
            $lote['estado_lote'] = (int) $lote['cantidad_disponible'] === 0
                ? 'AGOTADO'
                : 'DISPONIBLE';
        }
        unset($lote);

        return $lotes;
    }


    /* ================================================= */
    /* BLOQUEAR Y COMPROBAR LOTES */
    /* ================================================= */

    private function bloquearLotes(int $idInsumo): array
    {
        /* Bloquear los lotes en un orden fijo para evitar cruces de operaciones */
        $sql = "SELECT
                    id_lote,
                    cantidad_inicial,
                    cantidad_disponible,
                    fecha_vencimiento
                FROM lotes_insumos
                WHERE id_insumo = :id_insumo
                ORDER BY id_lote ASC
                FOR UPDATE";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    private function comprobarStockLotes(int $stockActual, array $lotes): void
    {
        /* Impedir operaciones sobre existencias antiguas sin lotes registrados */
        $totalLotes = 0;

        foreach ($lotes as $lote) {
            $totalLotes += (int) $lote['cantidad_disponible'];
        }

        if ($totalLotes !== $stockActual) {
            throw new \DomainException(
                'Las existencias del insumo no coinciden con sus lotes. ' .
                'Regularice las existencias anteriores antes de continuar.'
            );
        }
    }


    /* ================================================= */
    /* MODIFICAR INSUMO */
    /* ================================================= */

    public function modificar(mixed $idUsuario = null): bool
    {
        if (!$this->validarCatalogoInsumo(true)) return false;

        $idInsumo = $this->idInsumo;
        $stockOriginal = $this->stockOriginal;
        $stockCorregido = $this->stockActual;

        /* Validar al responsable cuando se solicita una corrección de cantidades */
        if ($stockCorregido !== $stockOriginal) {
            $idUsuario = $this->validarIdentificador(
                $idUsuario, 'id_usuario', 'El usuario responsable no es válido. Inicie sesión nuevamente.'
            );

            if ($idUsuario === null) return false;
        }

        $this->conexionMD->beginTransaction();

        try {
            /* Comprobar las existencias antes de actualizar el insumo */
            $sqlBuscar = "SELECT stock_actual
                          FROM insumos
                          WHERE id_insumo = :id_insumo
                              AND estado_insumo = 'ACTIVO'
                          LIMIT 1
                          FOR UPDATE";

            $stmtBuscar = $this->conexionMD->prepare($sqlBuscar);
            $stmtBuscar->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
            $stmtBuscar->execute();

            $stockRegistrado = $stmtBuscar->fetchColumn();

            if ($stockRegistrado === false) {
                throw new \DomainException('El insumo no se encuentra activo. Actualice la lista.');
            }

            $stockActualBD = (int) $stockRegistrado;
            $corrigeStock = $stockCorregido !== $stockOriginal;

            if ($corrigeStock && $stockActualBD !== $stockOriginal) {
                throw new \DomainException(
                    "Las existencias cambiaron de {$stockOriginal} a {$stockActualBD} mientras realizaba la modificación. Revise la cantidad actual antes de guardar."
                );
            }

            /* Conservar la corrección manual, pero sin desajustar los lotes */
            if ($corrigeStock) {
                $lotes = $this->bloquearLotes($idInsumo);
                $this->comprobarStockLotes($stockActualBD, $lotes);

                if (count($lotes) !== 1) {
                    throw new \DomainException(
                        'Para corregir existencias con varios lotes es necesario ' .
                        'identificar el lote afectado. No se modificó el inventario.'
                    );
                }

                $lote = $lotes[0];
                $diferencia = $stockCorregido - $stockActualBD;
                $disponibleCorregido = (int) $lote['cantidad_disponible'] + $diferencia;

                if ($disponibleCorregido < 0) {
                    throw new \DomainException('La corrección supera las unidades disponibles del lote.');
                }

                $cantidadInicial = max((int) $lote['cantidad_inicial'], $disponibleCorregido);

                $sqlLote = "UPDATE lotes_insumos
                            SET cantidad_inicial = :cantidad_inicial,
                                cantidad_disponible = :cantidad_disponible
                            WHERE id_lote = :id_lote
                                AND id_insumo = :id_insumo";

                $stmtLote = $this->conexionMD->prepare($sqlLote);
                $stmtLote->bindValue(':cantidad_inicial', $cantidadInicial, PDO::PARAM_INT);
                $stmtLote->bindValue(':cantidad_disponible', $disponibleCorregido, PDO::PARAM_INT);
                $stmtLote->bindValue(':id_lote', (int) $lote['id_lote'], PDO::PARAM_INT);
                $stmtLote->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
                $stmtLote->execute();

                /* Identificar la corrección sin alterar el historial de ingresos */
                $this->registrarMovimientoExistencias(
                    $idInsumo,
                    (int) $lote['id_lote'],
                    null,
                    $idUsuario,
                    $diferencia > 0 ? 'INCREMENTO' : 'DECREMENTO',
                    abs($diferencia),
                    $stockActualBD,
                    $stockCorregido,
                    'Corrección manual de existencias'
                );
            }

            /* Los datos generales no cambian el vencimiento de cada lote */
            $sql = "UPDATE insumos SET
                        nombre_insumo = :nombre_insumo,
                        descripcion = :descripcion,
                        presentacion = :presentacion,
                        tipo_control = :tipo_control,
                        stock_minimo = :stock_minimo";

            if ($corrigeStock) {
                $sql .= ",
                        stock_actual = :stock_actual";
            }

            $sql .= "
                    WHERE id_insumo = :id_insumo
                        AND estado_insumo = 'ACTIVO'";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_insumo', $this->nombreInsumo);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->bindParam(':presentacion', $this->presentacion);
            $stmt->bindParam(':tipo_control', $this->tipoControl);
            $stmt->bindParam(':stock_minimo', $this->stockMinimo);

            if ($corrigeStock) {
                $stmt->bindValue(':stock_actual', $stockCorregido, PDO::PARAM_INT);
            }

            $stmt->execute();
            if (!$this->conexionMD->commit()) {
                throw new \RuntimeException('No fue posible confirmar la corrección del insumo.');
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
    /* GESTIONAR EXISTENCIAS */
    /* ================================================= */

    public function gestionarExistencias(
        string $tipoMovimiento,
        int $cantidad,
        string $motivo,
        int $idUsuario,
        ?int $idProveedor = null,
        ?string $fechaVencimiento = null,
        ?int $idLote = null
    ): array {
        $datosValidados = $this->validarMovimientoInsumo($tipoMovimiento, $cantidad, $motivo, $idUsuario, $idProveedor, $fechaVencimiento, $idLote);

        $idInsumo = $datosValidados['id_insumo'];
        $motivo = $datosValidados['motivo'];
        $fechaVencimiento = $datosValidados['fecha_vencimiento'];

        $this->conexionMD->beginTransaction();

        try {
            if ($idProveedor !== null) {
                $this->comprobarProveedor($idProveedor);
            }

            /* Bloquear el insumo y todos sus lotes antes de cambiar las existencias */
            $sql = "SELECT stock_actual
                    FROM insumos
                    WHERE id_insumo = :id_insumo
                        AND estado_insumo = 'ACTIVO'
                    LIMIT 1
                    FOR UPDATE";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
            $stmt->execute();

            $insumo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$insumo) {
                throw new \DomainException('El insumo no se encuentra activo. Actualice la lista.');
            }

            $stockAnterior = (int) $insumo['stock_actual'];
            $lotes = $this->bloquearLotes($idInsumo);
            $this->comprobarStockLotes($stockAnterior, $lotes);

            if ($tipoMovimiento === 'INCREMENTO') {
                if ($cantidad > self::STOCK_MAXIMO - $stockAnterior) {
                    throw new \InvalidArgumentException('El ingreso supera la cantidad máxima permitida.');
                }

                /* Distinguir el primer ingreso de las reposiciones posteriores */
                $sql = "SELECT 1
                        FROM movimientos_insumos
                        WHERE id_insumo = :id_insumo
                            AND tipo_movimiento = 'INCREMENTO'
                        LIMIT 1";

                $stmt = $this->conexionMD->prepare($sql);
                $stmt->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
                $stmt->execute();

                $tuvoIngresos = $stmt->fetchColumn() !== false || $lotes !== [];
                $esIngresoInicial = $motivo === 'Ingreso inicial';

                if ($esIngresoInicial && $tuvoIngresos) {
                    throw new \DomainException('Este insumo ya tuvo ingresos. Seleccione Reponer existencias.');
                }

                if (!$esIngresoInicial && !$tuvoIngresos) {
                    throw new \DomainException('Seleccione Agregar existencias para registrar las primeras unidades.');
                }

                $stockResultante = $stockAnterior + $cantidad;

                /* Cada ingreso genera un lote con su propio vencimiento */
                $sql = "INSERT INTO lotes_insumos (
                            id_insumo,
                            cantidad_inicial,
                            cantidad_disponible,
                            fecha_vencimiento
                        ) VALUES (
                            :id_insumo,
                            :cantidad_inicial,
                            :cantidad_disponible,
                            :fecha_vencimiento
                        )";

                $stmt = $this->conexionMD->prepare($sql);
                $stmt->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
                $stmt->bindValue(':cantidad_inicial', $cantidad, PDO::PARAM_INT);
                $stmt->bindValue(':cantidad_disponible', $cantidad, PDO::PARAM_INT);
                $stmt->bindValue(':fecha_vencimiento', $fechaVencimiento, $fechaVencimiento === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->execute();

                $idLoteMovimiento = (int) $this->conexionMD->lastInsertId();

                /* Incorporar el proveedor a los que suministran este insumo */
                if ($idProveedor !== null) {
                    $this->asociarProveedorInsumo($idInsumo, $idProveedor);
                }

            } else {
                if ($cantidad > $stockAnterior) {
                    throw new \DomainException('No hay existencias suficientes para el descuento.');
                }

                /* Para una merma se descuenta del lote realmente afectado */
                $disponibles = array_values(array_filter(
                    $lotes,
                    static fn (array $lote): bool => (int) $lote['cantidad_disponible'] > 0
                ));

                if ($idLote === null && count($disponibles) !== 1) {
                    throw new \DomainException(
                        'Seleccione el lote afectado para registrar el descuento.'
                    );
                }

                $loteSeleccionado = null;

                foreach ($lotes as $lote) {
                    if ((int) $lote['id_lote'] === ($idLote ?? (int) $disponibles[0]['id_lote'])) {
                        $loteSeleccionado = $lote;
                        break;
                    }
                }

                if ($loteSeleccionado === null) {
                    throw new \DomainException('El lote seleccionado no pertenece al insumo.');
                }

                if ($cantidad > (int) $loteSeleccionado['cantidad_disponible']) {
                    throw new \DomainException(
                        'El lote seleccionado no tiene existencias suficientes. ' .
                        'Registre un descuento separado por cada lote afectado.'
                    );
                }

                $idLoteMovimiento = (int) $loteSeleccionado['id_lote'];
                $stockResultante = $stockAnterior - $cantidad;

                $sql = "UPDATE lotes_insumos
                        SET cantidad_disponible = cantidad_disponible - :cantidad
                        WHERE id_lote = :id_lote
                            AND id_insumo = :id_insumo";

                $stmt = $this->conexionMD->prepare($sql);
                $stmt->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
                $stmt->bindValue(':id_lote', $idLoteMovimiento, PDO::PARAM_INT);
                $stmt->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
                $stmt->execute();
            }

            /* Mantener el stock total igual a la suma de los lotes */
            $sql = "UPDATE insumos
                    SET stock_actual = :stock_resultante
                    WHERE id_insumo = :id_insumo
                        AND estado_insumo = 'ACTIVO'";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':stock_resultante', $stockResultante, PDO::PARAM_INT);
            $stmt->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
            $stmt->execute();

            /* Guardar la operación y su lote dentro de la misma transacción */
            $this->registrarMovimientoExistencias(
                $idInsumo,
                $idLoteMovimiento,
                $idProveedor,
                $idUsuario,
                $tipoMovimiento,
                $cantidad,
                $stockAnterior,
                $stockResultante,
                $motivo
            );

            $this->conexionMD->commit();

            return [
                'tipo_movimiento' => $tipoMovimiento,
                'stock_anterior' => $stockAnterior,
                'cantidad' => $cantidad,
                'stock_actual' => $stockResultante,
                'motivo' => $motivo,
                'id_proveedor' => $idProveedor,
                'id_lote' => $idLoteMovimiento
            ];

        } catch (\Throwable $e) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $e;
        }
    }


    /* ================================================= */
    /* REGISTRAR EL MOVIMIENTO DE EXISTENCIAS */
    /* ================================================= */

    private function registrarMovimientoExistencias(
        int $idInsumo,
        int $idLote,
        ?int $idProveedor,
        int $idUsuario,
        string $tipoMovimiento,
        int $cantidad,
        int $stockAnterior,
        int $stockResultante,
        string $motivo
    ): void {
        $sql = "INSERT INTO movimientos_insumos (
                    id_insumo,
                    id_lote,
                    id_proveedor,
                    id_usuario,
                    tipo_movimiento,
                    cantidad,
                    stock_anterior,
                    stock_resultante,
                    motivo,
                    fecha_movimiento
                ) VALUES (
                    :id_insumo,
                    :id_lote,
                    :id_proveedor,
                    :id_usuario,
                    :tipo_movimiento,
                    :cantidad,
                    :stock_anterior,
                    :stock_resultante,
                    :motivo,
                    NOW()
                )";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_insumo', $idInsumo, PDO::PARAM_INT);
        $stmt->bindValue(':id_lote', $idLote, PDO::PARAM_INT);
        $stmt->bindValue(':id_proveedor', $idProveedor, $idProveedor === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':tipo_movimiento', $tipoMovimiento);
        $stmt->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
        $stmt->bindValue(':stock_anterior', $stockAnterior, PDO::PARAM_INT);
        $stmt->bindValue(':stock_resultante', $stockResultante, PDO::PARAM_INT);
        $stmt->bindValue(':motivo', $motivo);

        if (!$stmt->execute()) {
            throw new \RuntimeException('No fue posible registrar el movimiento de existencias.');
        }
    }


    /* ================================================= */
    /* DESACTIVAR INSUMO */
    /* ================================================= */

    public function eliminar(): bool
    {
        return $this->cambiarEstadoInsumo('ACTIVO', 'INACTIVO');
    }


    /* ================================================= */
    /* REACTIVAR INSUMO */
    /* ================================================= */

    public function reactivar(): bool
    {
        return $this->cambiarEstadoInsumo('INACTIVO', 'ACTIVO');
    }


    /* ================================================= */
    /* ACTUALIZAR EL ESTADO DEL INSUMO */
    /* ================================================= */

    private function cambiarEstadoInsumo(string $estadoActual, string $estadoNuevo): bool
    {
        $this->idInsumo = $this->validarIdInsumo($this->idInsumo);

        if ($this->idInsumo === null) return false;

        /* Conservar las existencias, los lotes, los movimientos y las reservas */
        $sql = "UPDATE insumos
                SET estado_insumo = :estado_nuevo
                WHERE id_insumo = :id_insumo
                    AND estado_insumo = :estado_actual";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_insumo', $this->idInsumo, PDO::PARAM_INT);
        $stmt->bindValue(':estado_actual', $estadoActual, PDO::PARAM_STR);
        $stmt->bindValue(':estado_nuevo', $estadoNuevo, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() !== 1) {
            throw new \DomainException(
                'El insumo ya no se encuentra en el estado esperado. Actualice la lista.'
            );
        }

        return true;
    }
}