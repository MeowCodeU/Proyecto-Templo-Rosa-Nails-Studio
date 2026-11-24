<?php

namespace App\Model;

use App\Config\Database;
use App\Traits\ValidacionConfiguracionTrait;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use PDO;
use Throwable;


/* ===================================================== */
/* RESERVA LÓGICA DE INSUMOS POR SERVICIO */
/* ===================================================== */

class ReservaInsumosM extends Database
{
    use ValidacionConfiguracionTrait;

    private PDO $conexionMD;


    public function __construct(?PDO $conexion = null)
    {
        $this->conexionMD = $conexion ?? $this->getConnection();
    }


    /* ================================================= */
    /* DISPONIBILIDAD Y UNIDADES COMPROMETIDAS */
    /* ================================================= */

    public function consultar(): array
    {
        /* Descontar el consumo una sola vez del requerimiento de cada cita */
        $sql = "SELECT i.*,
                    COALESCE(v.cantidad, 0) AS stock_vigente,
                    COALESCE(r.cantidad, 0) AS stock_reservado,
                    EXISTS (SELECT 1 FROM insumos_servicios si
                            WHERE si.id_insumo = i.id_insumo) AS reserva_configurada
                FROM insumos i
                LEFT JOIN (
                    SELECT id_insumo, SUM(cantidad_disponible) AS cantidad
                    FROM lotes_insumos
                    WHERE fecha_vencimiento IS NULL OR fecha_vencimiento >= :fecha
                    GROUP BY id_insumo
                ) v ON v.id_insumo = i.id_insumo
                LEFT JOIN (
                    SELECT demanda.id_insumo,
                        SUM(GREATEST(demanda.cantidad - COALESCE(c.cantidad, 0), 0)) AS cantidad
                    FROM (
                        SELECT a.id_agendamiento, si.id_insumo, SUM(si.cantidad) AS cantidad
                        FROM agendamientos a
                        INNER JOIN estados_agendamientos e
                            ON e.id_estado_agendamiento = a.id_estado_agendamiento
                        INNER JOIN detalles_agendamientos d
                            ON d.id_agendamiento = a.id_agendamiento
                        INNER JOIN insumos_servicios si ON si.id_servicio = d.id_servicio
                        WHERE e.nombre_estado NOT IN ('REALIZADA', 'CANCELADA')
                        GROUP BY a.id_agendamiento, si.id_insumo
                    ) demanda
                    LEFT JOIN (
                        SELECT id_agendamiento, id_insumo, SUM(cantidad_usada) AS cantidad
                        FROM consumos_insumos
                        GROUP BY id_agendamiento, id_insumo
                    ) c ON c.id_agendamiento = demanda.id_agendamiento
                        AND c.id_insumo = demanda.id_insumo
                    GROUP BY demanda.id_insumo
                ) r ON r.id_insumo = i.id_insumo
                ORDER BY i.nombre_insumo, i.id_insumo";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute([
            ':fecha' => (new DateTimeImmutable('now', new DateTimeZone('America/Caracas')))->format('Y-m-d')
        ]);

        return array_map(static function (array $insumo): array {
            $vigente = (int) $insumo['stock_vigente'];
            $reservado = (int) $insumo['stock_reservado'];

            $insumo['stock_disponible'] = max(0, $vigente - $reservado);
            $insumo['faltante_reserva'] = max(0, $reservado - $vigente);
            $insumo['minimo_disponible'] = $insumo['reserva_configurada']
                ? max(5, (int) $insumo['stock_minimo'])
                : (int) $insumo['stock_minimo'];

            return $insumo;
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    /* Incorporar la disponibilidad sin reemplazar el stock físico */
    public function agregarDisponibilidad(array $insumos): array
    {
        $disponibilidad = array_column($this->consultar(), null, 'id_insumo');

        foreach ($insumos as &$insumo) {
            $datos = $disponibilidad[$insumo['id_insumo']] ?? [];

            foreach (['stock_vigente', 'stock_reservado', 'stock_disponible', 'faltante_reserva', 'minimo_disponible'] as $campo) {
                $insumo[$campo] = (int) ($datos[$campo] ?? 0);
            }
        }

        return $insumos;
    }


    /* ================================================= */
    /* ALERTAS PARA INICIO E INVENTARIO */
    /* ================================================= */

    public function consultarAlertas(): array
    {
        $alertas = array_filter($this->consultar(), static fn (array $i): bool =>
            ($i['estado_insumo'] === 'ACTIVO' && $i['stock_disponible'] <= $i['minimo_disponible']) ||
            ($i['stock_reservado'] > 0 && ($i['estado_insumo'] !== 'ACTIVO' || $i['faltante_reserva'] > 0))
        );

        usort($alertas, static fn (array $a, array $b): int =>
            ($b['faltante_reserva'] <=> $a['faltante_reserva'])
                ?: ($a['stock_disponible'] <=> $b['stock_disponible'])
                ?: strcmp($a['nombre_insumo'], $b['nombre_insumo'])
        );

        return array_values($alertas);
    }


    /* Advertir sin rechazar una cita por falta de existencias */
    public function advertenciaAgendamiento(int $idAgendamiento): string
    {
        $stmt = $this->conexionMD->prepare(
            'SELECT DISTINCT si.id_insumo
             FROM detalles_agendamientos d
             INNER JOIN insumos_servicios si ON si.id_servicio = d.id_servicio
             WHERE d.id_agendamiento = :id'
        );
        $stmt->execute([':id' => $idAgendamiento]);
        $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        $mensajes = [];

        foreach ($this->consultar() as $insumo) {
            if (!in_array((int) $insumo['id_insumo'], $ids, true)) continue;

            if ($insumo['estado_insumo'] !== 'ACTIVO') {
                $mensajes[] = $insumo['nombre_insumo'] . ': insumo inactivo';
            } elseif ($insumo['faltante_reserva'] > 0) {
                $mensajes[] = $insumo['nombre_insumo'] . ': faltan ' . $insumo['faltante_reserva'] . ' unidades';
            } elseif ($insumo['stock_disponible'] <= $insumo['minimo_disponible']) {
                $mensajes[] = $insumo['nombre_insumo'] . ': disponibles ' . $insumo['stock_disponible'];
            }
        }

        return $mensajes ? ' Advertencia de insumos: ' . implode('; ', $mensajes) . '. Revise la reposición.' : '';
    }


    /* ================================================= */
    /* INSUMOS REQUERIDOS EN UNA ATENCIÓN */
    /* ================================================= */

    public function consultarRequerimientos(int $idAgendamiento): array
    {
        $stmt = $this->conexionMD->prepare(
            'SELECT si.id_insumo, i.nombre_insumo, SUM(si.cantidad) AS cantidad
             FROM detalles_agendamientos d
             INNER JOIN insumos_servicios si ON si.id_servicio = d.id_servicio
             INNER JOIN insumos i ON i.id_insumo = si.id_insumo
             WHERE d.id_agendamiento = :id
             GROUP BY si.id_insumo, i.nombre_insumo
             ORDER BY si.id_insumo'
        );
        $stmt->execute([':id' => $idAgendamiento]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function comprobarConsumos(int $idAgendamiento): void
    {
        $stmt = $this->conexionMD->prepare(
            'SELECT id_insumo, SUM(cantidad_usada) AS cantidad
             FROM consumos_insumos WHERE id_agendamiento = :id GROUP BY id_insumo'
        );
        $stmt->execute([':id' => $idAgendamiento]);
        $consumos = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'cantidad', 'id_insumo');

        foreach ($this->consultarRequerimientos($idAgendamiento) as $requerido) {
            if ((int) ($consumos[$requerido['id_insumo']] ?? 0) < (int) $requerido['cantidad']) {
                throw new InvalidArgumentException(
                    'Guarde los insumos requeridos antes de finalizar: ' . $requerido['nombre_insumo'] .
                    ' (' . $requerido['cantidad'] . ' unidades).'
                );
            }
        }
    }


    /* ================================================= */
    /* CONFIGURAR LOS INSUMOS DE CADA SERVICIO */
    /* ================================================= */

    public function consultarConfiguracion(mixed $idServicio): array
    {
        $id = $this->validarIdConfiguracion($idServicio, 'id_servicio');
        if ($id === null) throw new InvalidArgumentException('Seleccione un servicio válido.');

        $stmt = $this->conexionMD->prepare(
            "SELECT id_servicio FROM servicios WHERE id_servicio = :id AND estado_servicio = 'ACTIVO'"
        );
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetchColumn()) throw new InvalidArgumentException('El servicio no está disponible.');

        /* Conservar visibles las asignaciones de insumos desactivados */
        $stmt = $this->conexionMD->prepare(
            "SELECT i.id_insumo, i.nombre_insumo, i.presentacion, i.estado_insumo,
                    COALESCE(si.cantidad, 0) AS cantidad
             FROM insumos i
             LEFT JOIN insumos_servicios si ON si.id_insumo = i.id_insumo AND si.id_servicio = :id
             WHERE i.estado_insumo = 'ACTIVO' OR si.id_insumo IS NOT NULL
             ORDER BY i.nombre_insumo, i.id_insumo"
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function guardarConfiguracion(mixed $idServicio, mixed $insumos): bool
    {
        $datos = $this->validarInsumosServicio($idServicio, $insumos);

        try {
            $this->conexionMD->beginTransaction();

            /* Serializar los cambios de la configuración de un servicio */
            $stmt = $this->conexionMD->prepare(
                "SELECT id_servicio FROM servicios
                 WHERE id_servicio = :id AND estado_servicio = 'ACTIVO' FOR UPDATE"
            );
            $stmt->execute([':id' => $datos['id_servicio']]);
            if (!$stmt->fetchColumn()) throw new InvalidArgumentException('El servicio no está disponible.');

            /* Comprobar los insumos seleccionados antes de reemplazar la relación */
            $stmt = $this->conexionMD->prepare('SELECT estado_insumo FROM insumos WHERE id_insumo = :id FOR UPDATE');

            foreach ($datos['insumos'] as $id => $cantidad) {
                $stmt->execute([':id' => $id]);
                if ($stmt->fetchColumn() !== 'ACTIVO') {
                    throw new InvalidArgumentException('Seleccione únicamente insumos activos.');
                }
            }

            $stmt = $this->conexionMD->prepare('DELETE FROM insumos_servicios WHERE id_servicio = :id');
            $stmt->execute([':id' => $datos['id_servicio']]);

            $stmt = $this->conexionMD->prepare(
                'INSERT INTO insumos_servicios (id_servicio, id_insumo, cantidad) VALUES (:servicio, :insumo, :cantidad)'
            );

            foreach ($datos['insumos'] as $id => $cantidad) {
                $stmt->execute([':servicio' => $datos['id_servicio'], ':insumo' => $id, ':cantidad' => $cantidad]);
            }

            $this->conexionMD->commit();
            return true;

        } catch (Throwable $error) {
            if ($this->conexionMD->inTransaction()) $this->conexionMD->rollBack();
            throw $error;
        }
    }
}