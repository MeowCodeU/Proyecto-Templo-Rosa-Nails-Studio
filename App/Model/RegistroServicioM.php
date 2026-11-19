<?php

namespace App\Model;

use App\Config\Database;
use App\Traits\ValidacionAtencionTrait;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;


/* ===================================================== */
/* MODELO DE REGISTRO DE SERVICIO */
/* ===================================================== */

class RegistroServicioM extends Database
{
    use ValidacionAtencionTrait;


    /* ================================================= */
    /* CONEXIÓN */
    /* ================================================= */

    private PDO $conexionMD;

    /* Reglas de transición de las citas */
    private FlujoCita $flujoCita;


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
        $this->flujoCita = new FlujoCita();
    }


    /* ================================================= */
    /* CONSULTAR AGENDAMIENTOS PENDIENTES */
    /* ================================================= */

    public function consultar(): array
    {
        /* Recuperar las citas que todavía pueden atenderse */
        $sql = "SELECT
                    a.id_agendamiento,
                    a.id_cliente,
                    a.fecha,
                    a.hora,
                    a.monto_total,
                    ea.nombre_estado AS estado,

                    COALESCE(
                        NULLIF(
                            TRIM(CONCAT_WS(' ', p.nombre, p.apellido)),
                            ''
                        ),
                        'Sin cliente registrado'
                    ) AS clienta,

                    TRIM(
                        CONCAT_WS(' ', pe.nombre, pe.apellido)
                    ) AS especialista,

                    (
                        SELECT GROUP_CONCAT(
                            s.nombre_servicio
                            ORDER BY d.id_detalle_agendamiento
                            SEPARATOR ', '
                        )
                        FROM detalles_agendamientos d
                        INNER JOIN servicios s
                            ON s.id_servicio = d.id_servicio
                        WHERE d.id_agendamiento = a.id_agendamiento
                    ) AS servicios

                FROM agendamientos a

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento =
                        a.id_estado_agendamiento

                INNER JOIN tipos_agendamientos ta
                    ON ta.id_tipo_agendamiento = a.id_tipo_agendamiento

                LEFT JOIN clientes c
                    ON c.id_cliente = a.id_cliente

                LEFT JOIN personas p
                    ON p.id_persona = c.id_persona

                INNER JOIN usuarios u
                    ON u.id_usuario = a.id_usuario_asignado

                INNER JOIN personas pe
                    ON pe.id_persona = u.id_persona

                WHERE ea.nombre_estado NOT IN ('REALIZADA', 'CANCELADA')

                ORDER BY a.fecha ASC, a.hora ASC, a.id_agendamiento ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* CONSULTAR LA FICHA DE UNA ATENCIÓN */
    /* ================================================= */

    public function buscar(mixed $idAgendamiento): ?array
    {
        $idAgendamiento = $this->validarIdRegistroServicio($idAgendamiento, 'id_agendamiento');

        /* Recuperar la cita, la clienta y la especialista asignada */
        $sql = "SELECT
                    a.id_agendamiento,
                    a.id_cliente,
                    a.id_usuario_asignado,
                    a.fecha,
                    a.hora,
                    a.monto_total,
                    a.observacion_inicial,
                    ea.nombre_estado AS estado,
                    p.cedula,
                    c.alergias,

                    COALESCE(
                        NULLIF(
                            TRIM(CONCAT_WS(' ', p.nombre, p.apellido)),
                            ''
                        ),
                        'Sin cliente registrado'
                    ) AS clienta,

                    TRIM(
                        CONCAT_WS(' ', pe.nombre, pe.apellido)
                    ) AS especialista

                FROM agendamientos a

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento =
                        a.id_estado_agendamiento

                LEFT JOIN clientes c
                    ON c.id_cliente = a.id_cliente

                LEFT JOIN personas p
                    ON p.id_persona = c.id_persona

                INNER JOIN usuarios u
                    ON u.id_usuario = a.id_usuario_asignado

                INNER JOIN personas pe
                    ON pe.id_persona = u.id_persona

                WHERE a.id_agendamiento = :id_agendamiento

                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);
        $stmt->execute();

        $atencion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atencion) return null;

        /* Incluir los servicios y sus fotografías, sin cargar los archivos binarios */
        $sql = "SELECT
                    d.id_detalle_agendamiento,
                    d.id_servicio,
                    d.precio_aplicado,
                    d.nota_tecnica,
                    s.nombre_servicio,
                    s.precio AS precio_actual,

                    (
                        SELECT f.id_foto
                        FROM fotos_servicios f
                        WHERE f.id_detalle_agendamiento =
                            d.id_detalle_agendamiento
                        ORDER BY f.id_foto DESC
                        LIMIT 1
                    ) AS id_foto,

                    (
                        SELECT f.destacada_catalogo
                        FROM fotos_servicios f
                        WHERE f.id_detalle_agendamiento =
                            d.id_detalle_agendamiento
                        ORDER BY f.id_foto DESC
                        LIMIT 1
                    ) AS destacada_catalogo

                FROM detalles_agendamientos d

                INNER JOIN servicios s
                    ON s.id_servicio = d.id_servicio

                WHERE d.id_agendamiento = :id_agendamiento

                ORDER BY d.id_detalle_agendamiento ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);
        $stmt->execute();

        $atencion['servicios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /* Recuperar los consumos agrupados por insumo */
        $sql = "SELECT
                    ci.id_insumo,
                    SUM(ci.cantidad_usada) AS cantidad,
                    i.nombre_insumo,
                    i.presentacion,
                    i.tipo_control,
                    i.stock_actual,
                    i.estado_insumo

                FROM consumos_insumos ci

                INNER JOIN insumos i
                    ON i.id_insumo = ci.id_insumo

                WHERE ci.id_agendamiento = :id_agendamiento

                GROUP BY
                    ci.id_insumo,
                    i.nombre_insumo,
                    i.presentacion,
                    i.tipo_control,
                    i.stock_actual,
                    i.estado_insumo

                ORDER BY i.nombre_insumo ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);
        $stmt->execute();

        $atencion['insumos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $atencion['historial'] = $this->consultarHistorial(
            (int) ($atencion['id_cliente'] ?? 0),
            $idAgendamiento
        );

        return $atencion;
    }


    /* ================================================= */
    /* CONSULTAR INSUMOS DISPONIBLES */
    /* ================================================= */

    public function consultarInsumos(): array
    {
        $sql = "SELECT
                    id_insumo,
                    nombre_insumo,
                    presentacion,
                    tipo_control,
                    stock_actual
                FROM insumos
                WHERE estado_insumo = 'ACTIVO'
                ORDER BY nombre_insumo ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* CONSULTAR HISTORIAL DE LA CLIENTA */
    /* ================================================= */

    public function consultarHistorial(
        mixed $idCliente,
        mixed $idExcluir = 0
    ): array {
        $this->erroresValidacion = [];

        /* Conservar el historial vacío cuando la cita no tiene clienta asociada */
        if ($idCliente === 0 || $idCliente === '0') return [];

        $idCliente = $this->validarIdRegistroServicio($idCliente, 'id_cliente');

        $idExcluir = in_array($idExcluir, [0, '0'], true)
            ? 0
            : $this->validarIdRegistroServicio($idExcluir, 'id_excluir');

        /* Mostrar únicamente los servicios de atenciones finalizadas */
        $sql = "SELECT
                    a.id_agendamiento,
                    a.fecha,
                    a.hora,
                    d.id_detalle_agendamiento,
                    d.precio_aplicado,
                    d.nota_tecnica,
                    s.nombre_servicio,

                    TRIM(
                        CONCAT_WS(' ', p.nombre, p.apellido)
                    ) AS especialista,

                    (
                        SELECT f.id_foto
                        FROM fotos_servicios f
                        WHERE f.id_detalle_agendamiento =
                            d.id_detalle_agendamiento
                        ORDER BY f.id_foto DESC
                        LIMIT 1
                    ) AS id_foto

                FROM agendamientos a

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento =
                        a.id_estado_agendamiento

                INNER JOIN detalles_agendamientos d
                    ON d.id_agendamiento = a.id_agendamiento

                INNER JOIN servicios s
                    ON s.id_servicio = d.id_servicio

                INNER JOIN usuarios u
                    ON u.id_usuario = a.id_usuario_asignado

                INNER JOIN personas p
                    ON p.id_persona = u.id_persona

                WHERE a.id_cliente = :id_cliente
                    AND a.id_agendamiento <> :id_excluir
                    AND ea.nombre_estado = 'REALIZADA'

                ORDER BY
                    a.fecha DESC,
                    a.hora DESC,
                    a.id_agendamiento DESC,
                    d.id_detalle_agendamiento ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_cliente', $idCliente, PDO::PARAM_INT);
        $stmt->bindValue(':id_excluir', $idExcluir, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* CONSULTAR CATÁLOGO DE TRABAJOS */
    /* ================================================= */

    public function consultarCatalogo(): array
    {
        /* Publicar las fotografías destacadas de atenciones finalizadas */
        $sql = "SELECT
                    f.id_foto,
                    d.id_detalle_agendamiento,
                    s.id_servicio,
                    s.nombre_servicio,
                    a.fecha

                FROM fotos_servicios f

                INNER JOIN detalles_agendamientos d
                    ON d.id_detalle_agendamiento =
                        f.id_detalle_agendamiento

                INNER JOIN servicios s
                    ON s.id_servicio = d.id_servicio

                INNER JOIN agendamientos a
                    ON a.id_agendamiento = d.id_agendamiento

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento =
                        a.id_estado_agendamiento

                WHERE f.destacada_catalogo = 1
                    AND ea.nombre_estado = 'REALIZADA'

                ORDER BY a.fecha DESC, f.id_foto DESC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* CONSULTAR UNA FOTOGRAFÍA */
    /* ================================================= */

    public function obtenerFotografia(mixed $idFoto): ?array
    {
        $idFoto = $this->validarIdRegistroServicio($idFoto, 'id_foto');

        /* Entregar el archivo al controlador encargado de mostrar la imagen */
        $sql = "SELECT
                    id_foto,
                    id_detalle_agendamiento,
                    foto
                FROM fotos_servicios
                WHERE id_foto = :id_foto
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_foto', $idFoto, PDO::PARAM_INT);
        $stmt->execute();

        $fotografia = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fotografia ?: null;
    }


    /* ================================================= */
    /* INICIAR LA ATENCIÓN */
    /* ================================================= */

    /* Cambiar a EN ATENCIÓN cuando la especialista pulse Atender */
    public function iniciarAtencion(mixed $idAgendamiento): bool
    {
        $idAgendamiento = $this->validarIdRegistroServicio($idAgendamiento, 'id_agendamiento');

        try {
            $this->conexionMD->beginTransaction();

            $agendamiento = $this->bloquearAgendamiento($idAgendamiento);

            /* No repetir la actualización si la atención ya fue iniciada */
            if ($agendamiento['estado'] !== 'EN ATENCIÓN') {
                if (!$this->flujoCita->permiteTransicion($agendamiento['estado'], 'EN ATENCIÓN')) {
                    throw new InvalidArgumentException(
                        'El estado actual de la cita no permite iniciar la atención.'
                    );
                }

                $this->marcarEnAtencion($idAgendamiento);
            }

            $this->conexionMD->commit();

            return true;

        } catch (Throwable $error) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $error;
        }
    }


    /* ================================================= */
    /* GUARDAR EL SERVICIO REALIZADO */
    /* ================================================= */

    public function guardarServicio(
        mixed $idAgendamiento,
        mixed $idDetalleAgendamiento,
        mixed $precioAplicado,
        mixed $notaTecnica,
        mixed $fotografia = null,
        mixed $destacadaCatalogo = false
    ): bool {
        $datos = $this->validarDatosServicioRealizado(
            $idAgendamiento,
            $idDetalleAgendamiento,
            $precioAplicado,
            $notaTecnica,
            $fotografia,
            $destacadaCatalogo
        );

        $idAgendamiento = $datos['id_agendamiento'];
        $idDetalleAgendamiento = $datos['id_detalle_agendamiento'];
        $precioAplicado = $datos['precio_aplicado'];
        $notaTecnica = $datos['nota_tecnica'];
        $fotografia = $datos['fotografia'];
        $destacadaCatalogo = $datos['destacada_catalogo'];

        try {
            $this->conexionMD->beginTransaction();

            $agendamiento = $this->bloquearAgendamiento($idAgendamiento);
            $this->comprobarAtencionIniciada($agendamiento);

            /* Comprobar que el servicio pertenezca a la cita seleccionada */
            $sql = "SELECT id_detalle_agendamiento
                    FROM detalles_agendamientos
                    WHERE id_detalle_agendamiento = :id_detalle
                        AND id_agendamiento = :id_agendamiento
                    LIMIT 1
                    FOR UPDATE";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':id_detalle', $idDetalleAgendamiento, PDO::PARAM_INT);
            $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->fetchColumn() === false) {
                throw new InvalidArgumentException(
                    'El servicio seleccionado no pertenece a este agendamiento.'
                );
            }

            /* Actualizar el detalle existente y conservar su precio aplicado */
            $sql = "UPDATE detalles_agendamientos
                    SET precio_aplicado = :precio_aplicado,
                        nota_tecnica = :nota_tecnica
                    WHERE id_detalle_agendamiento = :id_detalle";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':precio_aplicado', $precioAplicado, PDO::PARAM_STR);
            $stmt->bindValue(':nota_tecnica', $notaTecnica, PDO::PARAM_STR);
            $stmt->bindValue(':id_detalle', $idDetalleAgendamiento, PDO::PARAM_INT);
            $stmt->execute();

            /* Guardar la fotografía y su selección para el catálogo */
            $this->guardarFotografia(
                $idDetalleAgendamiento,
                $fotografia,
                $destacadaCatalogo
            );

            $this->actualizarTotal($idAgendamiento);

            $this->conexionMD->commit();

            return true;

        } catch (Throwable $error) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $error;
        }
    }


    /* ================================================= */
    /* GUARDAR LOS INSUMOS UTILIZADOS */
    /* ================================================= */

    public function guardarInsumos(mixed $idAgendamiento, mixed $insumos): bool
    {
        $datos = $this->validarInsumosRegistro($idAgendamiento, $insumos);
        $idAgendamiento = $datos['id_agendamiento'];
        $seleccionados = $datos['insumos'];

        try {
            $this->conexionMD->beginTransaction();

            $agendamiento = $this->bloquearAgendamiento($idAgendamiento);
            $this->comprobarAtencionIniciada($agendamiento);

            /* Recuperar cada consumo y el lote del que se descontó */
            $sql = "SELECT
                        id_consumo,
                        id_insumo,
                        id_lote,
                        cantidad_usada
                    FROM consumos_insumos
                    WHERE id_agendamiento = :id_agendamiento
                    ORDER BY id_insumo ASC, id_consumo ASC
                    FOR UPDATE";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);
            $stmt->execute();

            $anteriores = [];
            $consumosAnteriores = [];

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $consumo) {

                /* No adivinar el lote de consumos anteriores a la migración */
                if ($consumo['id_lote'] === null) {
                    throw new InvalidArgumentException(
                        'La atención tiene consumos anteriores sin lote identificado. ' .
                        'Regularice esos registros antes de modificarlos.'
                    );
                }

                $cantidadGuardada = (string) $consumo['cantidad_usada'];

                if (
                    !preg_match('/\A[0-9]+(?:\.0{1,2})?\z/D', $cantidadGuardada) ||
                    (float) $cantidadGuardada < 1 ||
                    (float) $cantidadGuardada > 99999999
                ) {
                    throw new RuntimeException(
                        'Existe un consumo anterior con una cantidad incompatible con el control por unidades.'
                    );
                }

                $idInsumo = (int) $consumo['id_insumo'];
                $cantidadAnterior = (int) $cantidadGuardada;

                $anteriores[$idInsumo] =
                    ($anteriores[$idInsumo] ?? 0) + $cantidadAnterior;

                $consumosAnteriores[$idInsumo][] = [
                    'id_consumo' => (int) $consumo['id_consumo'],
                    'id_lote' => (int) $consumo['id_lote'],
                    'cantidad' => $cantidadAnterior
                ];
            }

            /* Bloquear los insumos involucrados siguiendo el mismo orden */
            $identificadores = array_unique(array_merge(
                array_keys($anteriores),
                array_keys($seleccionados)
            ));

            sort($identificadores, SORT_NUMERIC);

            if ($identificadores !== []) {
                $marcadores = implode(', ', array_fill(0, count($identificadores), '?'));

                $sql = "SELECT
                            id_insumo,
                            nombre_insumo,
                            stock_actual,
                            estado_insumo
                        FROM insumos
                        WHERE id_insumo IN ($marcadores)
                        ORDER BY id_insumo ASC
                        FOR UPDATE";

                $stmt = $this->conexionMD->prepare($sql);
                $stmt->execute($identificadores);

                $existencias = [];

                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $insumo) {
                    $existencias[(int) $insumo['id_insumo']] = $insumo;
                }

                /* Bloquear los lotes antes de modificar sus existencias */
                $sql = "SELECT
                            id_lote,
                            id_insumo,
                            cantidad_inicial,
                            cantidad_disponible,
                            fecha_vencimiento
                        FROM lotes_insumos
                        WHERE id_insumo IN ($marcadores)
                        ORDER BY id_insumo ASC, id_lote ASC
                        FOR UPDATE";

                $stmt = $this->conexionMD->prepare($sql);
                $stmt->execute($identificadores);

                $lotes = [];
                $lotesPorInsumo = [];

                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $lote) {
                    $idLote = (int) $lote['id_lote'];
                    $idInsumoLote = (int) $lote['id_insumo'];

                    $lotes[$idLote] = [
                        'id_insumo' => $idInsumoLote,
                        'cantidad_inicial' => (int) $lote['cantidad_inicial'],
                        'cantidad_disponible' => (int) $lote['cantidad_disponible'],
                        'fecha_vencimiento' => $lote['fecha_vencimiento']
                    ];

                    $lotesPorInsumo[$idInsumoLote][] = $idLote;
                }

                /* Comprobar que el stock general coincide con sus lotes */
                foreach ($identificadores as $idInsumo) {
                    if (!isset($existencias[$idInsumo])) {
                        throw new InvalidArgumentException(
                            'Uno de los insumos seleccionados ya no está disponible.'
                        );
                    }

                    $totalDisponible = 0;

                    foreach ($lotesPorInsumo[$idInsumo] ?? [] as $idLote) {
                        $totalDisponible += $lotes[$idLote]['cantidad_disponible'];
                    }

                    if ($totalDisponible !== (int) $existencias[$idInsumo]['stock_actual']) {
                        throw new RuntimeException(
                            'Las existencias de ' . $existencias[$idInsumo]['nombre_insumo'] .
                            ' no coinciden con sus lotes. Regularice el inventario.'
                        );
                    }
                }

                /* Comprobar la procedencia de los consumos anteriores */
                foreach ($consumosAnteriores as $idInsumo => $registros) {
                    foreach ($registros as $registro) {
                        $idLote = $registro['id_lote'];

                        if (
                            !isset($lotes[$idLote]) ||
                            $lotes[$idLote]['id_insumo'] !== (int) $idInsumo
                        ) {
                            throw new RuntimeException(
                                'Uno de los consumos existentes no corresponde al lote del insumo.'
                            );
                        }
                    }
                }

                /* Preparar la actualización de las cantidades por lote */
                $sql = "UPDATE lotes_insumos
                        SET cantidad_disponible = :cantidad_disponible
                        WHERE id_lote = :id_lote";

                $stmtLote = $this->conexionMD->prepare($sql);

                /* Preparar la actualización del stock general */
                $sql = "UPDATE insumos
                        SET stock_actual = :stock_actual
                        WHERE id_insumo = :id_insumo";

                $stmtStock = $this->conexionMD->prepare($sql);

                /* Preparar la modificación de un consumo anterior */
                $sql = "UPDATE consumos_insumos
                        SET cantidad_usada = :cantidad_usada
                        WHERE id_consumo = :id_consumo
                            AND id_agendamiento = :id_agendamiento";

                $stmtConsumo = $this->conexionMD->prepare($sql);

                /* Preparar la eliminación de un consumo anterior */
                $sql = "DELETE FROM consumos_insumos
                        WHERE id_consumo = :id_consumo
                            AND id_agendamiento = :id_agendamiento";

                $stmtEliminarConsumo = $this->conexionMD->prepare($sql);

                /* Preparar el registro de consumos por lote */
                $sql = "INSERT INTO consumos_insumos (
                            id_agendamiento,
                            id_insumo,
                            id_lote,
                            cantidad_usada
                        ) VALUES (
                            :id_agendamiento,
                            :id_insumo,
                            :id_lote,
                            :cantidad_usada
                        )";

                $stmtInsertarConsumo = $this->conexionMD->prepare($sql);

                /* Consultar la fecha actual de la base de datos */
                $fechaHoy = $this->conexionMD
                    ->query('SELECT CURRENT_DATE()')
                    ->fetchColumn();

                foreach ($identificadores as $idInsumo) {
                    $insumo = $existencias[$idInsumo];
                    $cantidadAnterior = $anteriores[$idInsumo] ?? 0;
                    $cantidadNueva = $seleccionados[$idInsumo] ?? 0;
                    $diferencia = $cantidadNueva - $cantidadAnterior;

                    /* Conservar los lotes cuando la cantidad no cambia */
                    if ($diferencia === 0) continue;

                    /* Impedir nuevos consumos de un insumo inactivo */
                    if ($diferencia > 0 && $insumo['estado_insumo'] !== 'ACTIVO') {
                        throw new InvalidArgumentException(
                            'El insumo ' . $insumo['nombre_insumo'] . ' está inactivo.'
                        );
                    }

                    $nuevoStock = (int) $insumo['stock_actual'] - $diferencia;

                    if ($nuevoStock < 0) {
                        throw new InvalidArgumentException(
                            'El insumo ' . $insumo['nombre_insumo'] .
                            ' no tiene existencias suficientes.'
                        );
                    }

                    if ($nuevoStock > 4294967295) {
                        throw new RuntimeException(
                            'La devolución supera el máximo permitido para las existencias.'
                        );
                    }

                    if ($diferencia < 0) {

                        /* ================================================= */
                        /* DEVOLVER UNIDADES A SUS LOTES ORIGINALES */
                        /* ================================================= */

                        $porDevolver = -$diferencia;
                        $registros = $consumosAnteriores[$idInsumo] ?? [];

                        /* Conservar primero el consumo de vencimientos próximos */
                        usort(
                            $registros,
                            static function (array $a, array $b) use (&$lotes): int {
                                $fechaA = $lotes[$a['id_lote']]['fecha_vencimiento']
                                    ?? '9999-12-31';

                                $fechaB = $lotes[$b['id_lote']]['fecha_vencimiento']
                                    ?? '9999-12-31';

                                return strcmp($fechaB, $fechaA)
                                    ?: ($b['id_lote'] <=> $a['id_lote'])
                                    ?: ($b['id_consumo'] <=> $a['id_consumo']);
                            }
                        );

                        foreach ($registros as $registro) {
                            if ($porDevolver === 0) break;

                            $idLote = $registro['id_lote'];
                            $devolucion = min($porDevolver, $registro['cantidad']);

                            $disponible =
                                $lotes[$idLote]['cantidad_disponible'] + $devolucion;

                            if ($disponible > $lotes[$idLote]['cantidad_inicial']) {
                                throw new RuntimeException(
                                    'No es posible devolver unidades al lote original. ' .
                                    'Revise las correcciones previas del inventario.'
                                );
                            }

                            /* Restaurar las existencias del lote */
                            $stmtLote->execute([
                                ':cantidad_disponible' => $disponible,
                                ':id_lote' => $idLote
                            ]);

                            $lotes[$idLote]['cantidad_disponible'] = $disponible;

                            $cantidadRestante = $registro['cantidad'] - $devolucion;

                            /* Eliminar o reducir el consumo anterior */
                            if ($cantidadRestante === 0) {
                                $stmtEliminarConsumo->execute([
                                    ':id_consumo' => $registro['id_consumo'],
                                    ':id_agendamiento' => $idAgendamiento
                                ]);

                            } else {
                                $stmtConsumo->execute([
                                    ':cantidad_usada' => $cantidadRestante,
                                    ':id_consumo' => $registro['id_consumo'],
                                    ':id_agendamiento' => $idAgendamiento
                                ]);
                            }

                            $porDevolver -= $devolucion;
                        }

                        if ($porDevolver !== 0) {
                            throw new RuntimeException(
                                'No fue posible restituir completamente el consumo anterior.'
                            );
                        }

                    } else {

                        /* ================================================= */
                        /* DESCONTAR NUEVOS CONSUMOS MEDIANTE FEFO */
                        /* ================================================= */

                        $porConsumir = $diferencia;
                        $idsLotes = $lotesPorInsumo[$idInsumo] ?? [];

                        /* Primero los lotes que vencen más pronto */
                        usort(
                            $idsLotes,
                            static function (int $a, int $b) use (&$lotes): int {
                                $fechaA = $lotes[$a]['fecha_vencimiento'] ?? '9999-12-31';
                                $fechaB = $lotes[$b]['fecha_vencimiento'] ?? '9999-12-31';

                                return strcmp($fechaA, $fechaB) ?: ($a <=> $b);
                            }
                        );

                        foreach ($idsLotes as $idLote) {
                            if ($porConsumir === 0) break;

                            $lote = $lotes[$idLote];

                            /* No utilizar lotes agotados ni vencidos */
                            if (
                                $lote['cantidad_disponible'] <= 0 ||
                                (
                                    $lote['fecha_vencimiento'] !== null &&
                                    $lote['fecha_vencimiento'] < $fechaHoy
                                )
                            ) {
                                continue;
                            }

                            $consumo = min($porConsumir, $lote['cantidad_disponible']);
                            $disponible = $lote['cantidad_disponible'] - $consumo;

                            /* Descontar las unidades del lote */
                            $stmtLote->execute([
                                ':cantidad_disponible' => $disponible,
                                ':id_lote' => $idLote
                            ]);

                            $lotes[$idLote]['cantidad_disponible'] = $disponible;

                            /* Guardar el lote que suministró las unidades */
                            $stmtInsertarConsumo->execute([
                                ':id_agendamiento' => $idAgendamiento,
                                ':id_insumo' => $idInsumo,
                                ':id_lote' => $idLote,
                                ':cantidad_usada' => $consumo
                            ]);

                            $porConsumir -= $consumo;
                        }

                        if ($porConsumir !== 0) {
                            throw new InvalidArgumentException(
                                'El insumo ' . $insumo['nombre_insumo'] .
                                ' no tiene suficientes unidades vigentes disponibles por lote.'
                            );
                        }
                    }

                    /* Sincronizar el stock total con la modificación */
                    $stmtStock->execute([
                        ':stock_actual' => $nuevoStock,
                        ':id_insumo' => $idInsumo
                    ]);
                }
            }

            $this->conexionMD->commit();

            return true;

        } catch (Throwable $error) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $error;
        }
    }


    /* ================================================= */
    /* FINALIZAR LA ATENCIÓN */
    /* ================================================= */

    public function finalizar(mixed $idAgendamiento): bool
    {
        $idAgendamiento = $this->validarIdRegistroServicio($idAgendamiento, 'id_agendamiento');

        try {
            $this->conexionMD->beginTransaction();

            $agendamiento = $this->bloquearAgendamiento($idAgendamiento, true);

            /* Reconocer una finalización que ya fue confirmada */
            if ($agendamiento['estado'] === 'REALIZADA') {
                $this->conexionMD->commit();
                return true;
            }

            /* Finalizar únicamente una atención que ya fue iniciada */
            if (!$this->flujoCita->permiteTransicion($agendamiento['estado'], 'REALIZADA')) {
                throw new InvalidArgumentException(
                    'La atención debe estar EN ATENCIÓN para poder finalizarla.'
                );
            }

            /* Comprobar los precios de todos los servicios */
            $sql = "SELECT precio_aplicado
                    FROM detalles_agendamientos
                    WHERE id_agendamiento = :id_agendamiento
                    FOR UPDATE";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);
            $stmt->execute();

            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($servicios === []) {
                throw new InvalidArgumentException(
                    'El agendamiento no tiene servicios asociados.'
                );
            }

            foreach ($servicios as $servicio) {
                if ($servicio['precio_aplicado'] === null) {
                    throw new InvalidArgumentException(
                        'Guarde cada servicio realizado antes de finalizar la atención.'
                    );
                }
            }

            $this->actualizarTotal($idAgendamiento);

            /* Registrar el cierre con la fecha y hora del negocio */
            $finAtencion = $this->fechaHoraActualAtencion();

            if ($agendamiento['inicio_atencion'] !== null && $finAtencion < $agendamiento['inicio_atencion']) {
                throw new RuntimeException(
                    'La hora de finalización es anterior al inicio. Revise la fecha y hora del servidor.'
                );
            }

            /* Cambiar el estado y conservar el primer cierre en la misma transacción */
            $idEstado = $this->obtenerEstado('REALIZADA');

            $sql = "UPDATE agendamientos
                    SET id_estado_agendamiento = :id_estado,
                        fin_atencion = COALESCE(fin_atencion, :fin_atencion)
                    WHERE id_agendamiento = :id_agendamiento";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':id_estado', $idEstado, PDO::PARAM_INT);
            $stmt->bindValue(':fin_atencion', $finAtencion, PDO::PARAM_STR);
            $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);
            $stmt->execute();

            $this->conexionMD->commit();

            return true;

        } catch (Throwable $error) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $error;
        }
    }


    /* ================================================= */
    /* BLOQUEAR Y COMPROBAR EL AGENDAMIENTO */
    /* ================================================= */

    private function bloquearAgendamiento(
        int $idAgendamiento,
        bool $permitirRealizada = false
    ): array {
        /* Evitar cambios simultáneos sobre una misma atención */
        $sql = "SELECT id_agendamiento, id_estado_agendamiento,
                       inicio_atencion, fin_atencion
                FROM agendamientos
                WHERE id_agendamiento = :id_agendamiento
                LIMIT 1
                FOR UPDATE";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);
        $stmt->execute();

        $agendamiento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$agendamiento) {
            throw new InvalidArgumentException('El agendamiento no está disponible.');
        }

        $sql = "SELECT nombre_estado
                FROM estados_agendamientos
                WHERE id_estado_agendamiento = :id_estado
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_estado', (int) $agendamiento['id_estado_agendamiento'], PDO::PARAM_INT);
        $stmt->execute();

        $agendamiento['estado'] = $stmt->fetchColumn();

        if ($agendamiento['estado'] === false) {
            throw new RuntimeException('No fue posible comprobar el estado de la cita.');
        }

        if ($agendamiento['estado'] === 'CANCELADA') {
            throw new InvalidArgumentException(
                'No puede registrar servicios en un agendamiento cancelado.'
            );
        }

        if (!$permitirRealizada && $agendamiento['estado'] === 'REALIZADA') {
            throw new InvalidArgumentException('La atención ya fue finalizada.');
        }

        return $agendamiento;
    }


    /* ================================================= */
    /* COMPROBAR QUE LA ATENCIÓN FUE INICIADA */
    /* ================================================= */

    /* Guardar servicios e insumos solo después de pulsar Atender */
    private function comprobarAtencionIniciada(array $agendamiento): void
    {
        if ($agendamiento['estado'] !== 'EN ATENCIÓN') {
            throw new InvalidArgumentException(
                'Pulse Atender para iniciar la atención antes de guardar información.'
            );
        }
    }


    /* ================================================= */
    /* GUARDAR LA FOTOGRAFÍA DEL SERVICIO */
    /* ================================================= */

    private function guardarFotografia(
        int $idDetalleAgendamiento,
        ?string $fotografia,
        bool $destacadaCatalogo
    ): void {
        $sql = "SELECT id_foto
                FROM fotos_servicios
                WHERE id_detalle_agendamiento = :id_detalle
                ORDER BY id_foto DESC
                LIMIT 1
                FOR UPDATE";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_detalle', $idDetalleAgendamiento, PDO::PARAM_INT);
        $stmt->execute();

        $idFoto = $stmt->fetchColumn();

        /* La fotografía es opcional, pero debe existir para destacarla */
        if ($idFoto === false && $fotografia === null) {
            if ($destacadaCatalogo) {
                throw new InvalidArgumentException(
                    'Seleccione una fotografía para destacarla en el catálogo.'
                );
            }

            return;
        }

        if ($idFoto === false) {
            $sql = "INSERT INTO fotos_servicios (
                        id_detalle_agendamiento,
                        foto,
                        destacada_catalogo
                    ) VALUES (
                        :id_detalle,
                        :foto,
                        :destacada
                    )";

            $stmt = $this->conexionMD->prepare($sql);
            $stmt->bindValue(':id_detalle', $idDetalleAgendamiento, PDO::PARAM_INT);
            $stmt->bindValue(':foto', $fotografia, PDO::PARAM_LOB);
            $stmt->bindValue(':destacada', (int) $destacadaCatalogo, PDO::PARAM_INT);
            $stmt->execute();

            return;
        }

        /* Conservar la imagen anterior cuando no se adjunta otra */
        $sql = "UPDATE fotos_servicios
                SET destacada_catalogo = :destacada";

        if ($fotografia !== null) {
            $sql .= ", foto = :foto";
        }

        $sql .= " WHERE id_foto = :id_foto";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':destacada', (int) $destacadaCatalogo, PDO::PARAM_INT);
        $stmt->bindValue(':id_foto', (int) $idFoto, PDO::PARAM_INT);

        if ($fotografia !== null) {
            $stmt->bindValue(':foto', $fotografia, PDO::PARAM_LOB);
        }

        $stmt->execute();
    }


    /* ================================================= */
    /* ACTUALIZAR EL MONTO TOTAL */
    /* ================================================= */

    private function actualizarTotal(int $idAgendamiento): void
    {
        /* Usar el precio aplicado y, mientras falte registrarlo, el precio del catálogo */
        $sql = "UPDATE agendamientos
                SET monto_total = (
                    SELECT COALESCE(
                        SUM(COALESCE(d.precio_aplicado, s.precio)),
                        0
                    )
                    FROM detalles_agendamientos d
                    INNER JOIN servicios s
                        ON s.id_servicio = d.id_servicio
                    WHERE d.id_agendamiento = :id_detalles
                )
                WHERE id_agendamiento = :id_agendamiento";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_detalles', $idAgendamiento, PDO::PARAM_INT);
        $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);
        $stmt->execute();
    }


    /* ================================================= */
    /* MARCAR LA ATENCIÓN EN PROCESO */
    /* ================================================= */

    private function marcarEnAtencion(int $idAgendamiento): void
    {
        $idEstado = $this->obtenerEstado('EN ATENCIÓN');

        /* Conservar el inicio original si la ficha vuelve a abrirse */
        $sql = "UPDATE agendamientos
                SET id_estado_agendamiento = :id_estado,
                    inicio_atencion = COALESCE(inicio_atencion, :inicio_atencion)
                WHERE id_agendamiento = :id_agendamiento";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':id_estado', $idEstado, PDO::PARAM_INT);
        $stmt->bindValue(':inicio_atencion', $this->fechaHoraActualAtencion(), PDO::PARAM_STR);
        $stmt->bindValue(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);
        $stmt->execute();
    }


    /* ================================================= */
    /* FECHA Y HORA REAL DE LA ATENCIÓN */
    /* ================================================= */

    private function fechaHoraActualAtencion(): string
    {
        return (new DateTimeImmutable('now', new DateTimeZone('America/Caracas')))
            ->format('Y-m-d H:i:s');
    }


    /* ================================================= */
    /* OBTENER EL ESTADO REQUERIDO */
    /* ================================================= */

    private function obtenerEstado(string $nombreEstado): int
    {
        /* Consultar por nombre para evitar depender de identificadores fijos */
        $sql = "SELECT id_estado_agendamiento
                FROM estados_agendamientos
                WHERE nombre_estado = :nombre_estado
                    AND estado_registro = 'ACTIVO'
                LIMIT 1";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':nombre_estado', $nombreEstado, PDO::PARAM_STR);
        $stmt->execute();

        $idEstado = $stmt->fetchColumn();

        if ($idEstado === false) {
            throw new RuntimeException(
                'El estado ' . $nombreEstado . ' debe existir y estar activo.'
            );
        }

        return (int) $idEstado;
    }
}