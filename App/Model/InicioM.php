<?php

namespace App\Model;

use App\Config\Database;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use PDO;
use Throwable;


/* ===================================================== */
/* MODELO DE INICIO */
/* ===================================================== */

class InicioM extends Database
{
    /* ================================================= */
    /* CONEXIÓN Y CONFIGURACIÓN */
    /* ================================================= */

    private PDO $conexionMD;

    /* Cantidad de servicios destacados en el resumen mensual */
    private const LIMITE_SERVICIOS = 5;


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* ================================================= */
    /* CONSULTAR EL PANEL DE INICIO */
    /* ================================================= */

    public function consultar(?string $mes = null, ?int $idEspecialista = null): array
    {
        /* Determinar el día del negocio y el mes que se desea consultar */
        $zonaHoraria = new DateTimeZone('America/Caracas');
        $ahora = new DateTimeImmutable('now', $zonaHoraria);
        $fechaActual = $ahora->format('Y-m-d');
        $mes = $mes === null || $mes === '' ? $ahora->format('Y-m') : $mes;

        if (!preg_match('/\A[1-9][0-9]{3}-(?:0[1-9]|1[0-2])\z/', $mes)) {
            throw new InvalidArgumentException('El mes solicitado no es válido.');
        }

        $primerDia = DateTimeImmutable::createFromFormat('!Y-m', $mes, $zonaHoraria);

        if ($primerDia === false || $primerDia->format('Y-m') !== $mes) {
            throw new InvalidArgumentException('El mes solicitado no es válido.');
        }

        $inicioMes = $primerDia->format('Y-m-d');
        $finMes = $primerDia->modify('last day of this month')->format('Y-m-d');

        try {
            /* Consultar los indicadores sobre una misma instantánea de los datos */
            $this->conexionMD->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $this->conexionMD->beginTransaction();

            $datos = [
                'fecha_actual' => $fechaActual,
                'mes_actual' => $ahora->format('Y-m'),
                'mes_consultado' => $mes,
                'resumen' => $this->consultarResumen($fechaActual),
                'agendamientos_especialista_hoy' => $idEspecialista === null
                    ? null
                    : $this->consultarAgendamientosEspecialista($fechaActual, $idEspecialista),
                'agendamientos_hoy' => $this->consultarAgendamientos($fechaActual),
                'alertas_stock' => $this->consultarStockBajo(),
                'servicios_solicitados' => $this->consultarServiciosSolicitados($inicioMes, $finMes),
                'resumen_mensual' => $this->consultarResumenMensual($inicioMes, $finMes),
                'calendario' => $this->consultarCalendario($inicioMes, $finMes)
            ];

            $this->conexionMD->commit();

            return $datos;

        } catch (Throwable $error) {
            if ($this->conexionMD->inTransaction()) {
                $this->conexionMD->rollBack();
            }

            throw $error;
        }
    }


    /* ================================================= */
    /* RESUMEN DEL DÍA */
    /* ================================================= */

    private function consultarResumen(string $fecha): array
    {
        /* Contar las citas del día excluyendo las canceladas */
        $sql = "SELECT
                    COUNT(*) AS agendamientos_hoy,
                    COALESCE(SUM(ea.nombre_estado = 'EN ATENCIÓN'), 0) AS en_atencion,
                    COALESCE(SUM(ea.nombre_estado = 'EN ESPERA'), 0) AS en_espera,
                    COALESCE(SUM(ea.nombre_estado = 'REALIZADA'), 0) AS finalizados_hoy

                FROM agendamientos a

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento = a.id_estado_agendamiento

                WHERE a.fecha = :fecha
                    AND ea.nombre_estado <> 'CANCELADA'";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();

        $resumen = $stmt->fetch(PDO::FETCH_ASSOC);

        /* Separar los servicios realizados del número de citas atendidas */
        $sql = "SELECT
                    (
                        SELECT COUNT(*)
                        FROM detalles_agendamientos d
                        INNER JOIN agendamientos a
                            ON a.id_agendamiento = d.id_agendamiento
                        INNER JOIN estados_agendamientos ea
                            ON ea.id_estado_agendamiento = a.id_estado_agendamiento
                        WHERE a.fecha = :fecha
                            AND ea.nombre_estado = 'REALIZADA'
                    ) AS servicios_hoy,

                    (
                        SELECT COUNT(*)
                        FROM clientes
                    ) AS clientas_registradas";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();

        $resumen = array_merge($resumen, $stmt->fetch(PDO::FETCH_ASSOC));

        $resumen['insumos_stock_bajo'] = count($this->consultarStockBajo());

        return array_map('intval', $resumen);
    }


    /* ================================================= */
    /* AGENDAMIENTOS DE LA ESPECIALISTA EN EL DÍA */
    /* ================================================= */

    private function consultarAgendamientosEspecialista(string $fecha, int $idEspecialista): int
    {
        /* Contar solo las citas asignadas a la especialista autenticada */
        $sql = "SELECT COUNT(*)
                FROM agendamientos a
                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento = a.id_estado_agendamiento
                WHERE a.fecha = :fecha
                    AND a.id_usuario_asignado = :id_especialista
                    AND ea.nombre_estado <> 'CANCELADA'";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->bindValue(':id_especialista', $idEspecialista, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }


    /* ================================================= */
    /* AGENDAMIENTOS DEL DÍA */
    /* ================================================= */

    private function consultarAgendamientos(string $fecha): array
    {
        $sql = "SELECT
                    a.id_agendamiento,
                    a.hora,
                    a.duracion_minutos,
                    a.monto_total,
                    ea.nombre_estado AS estado,
                    ta.nombre_tipo_agendamiento AS tipo,

                    COALESCE(
                        NULLIF(TRIM(CONCAT_WS(' ', p.nombre, p.apellido)), ''),
                        'Sin cliente registrado'
                    ) AS clienta,

                    TRIM(CONCAT_WS(' ', pe.nombre, pe.apellido)) AS especialista,

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
                    ON ea.id_estado_agendamiento = a.id_estado_agendamiento

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

                WHERE a.fecha = :fecha
                    AND ea.nombre_estado <> 'CANCELADA'

                ORDER BY a.hora ASC, a.id_agendamiento ASC";

        $stmt = $this->conexionMD->prepare($sql);
        $stmt->bindValue(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* ALERTAS DE INVENTARIO */
    /* ================================================= */

    private function consultarStockBajo(): array
    {
        return (new ReservaInsumosM($this->conexionMD))->consultarAlertas();
    }


    /* ================================================= */
    /* SERVICIOS MÁS SOLICITADOS DEL MES */
    /* ================================================= */

    private function consultarServiciosSolicitados(string $inicio, string $fin): array
    {
        /* Contar las solicitudes del mes sin incluir citas canceladas */
        $sql = "SELECT
                    s.id_servicio,
                    s.nombre_servicio,
                    COUNT(*) AS cantidad

                FROM detalles_agendamientos d

                INNER JOIN agendamientos a
                    ON a.id_agendamiento = d.id_agendamiento

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento = a.id_estado_agendamiento

                INNER JOIN servicios s
                    ON s.id_servicio = d.id_servicio

                WHERE a.fecha BETWEEN :inicio AND :fin
                    AND ea.nombre_estado <> 'CANCELADA'

                GROUP BY s.id_servicio, s.nombre_servicio

                ORDER BY cantidad DESC, s.nombre_servicio ASC, s.id_servicio ASC

                LIMIT :limite";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':inicio', $inicio, PDO::PARAM_STR);
        $stmt->bindValue(':fin', $fin, PDO::PARAM_STR);
        $stmt->bindValue(':limite', self::LIMITE_SERVICIOS, PDO::PARAM_INT);

        $stmt->execute();

        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($servicios as &$servicio) {
            $servicio['cantidad'] = (int) $servicio['cantidad'];
        }

        unset($servicio);

        return $servicios;
    }


    /* ================================================= */
    /* RESUMEN MENSUAL */
    /* ================================================= */

    private function consultarResumenMensual(string $inicio, string $fin): array
    {
        /* Una misma clienta puede tener varias citas y varios servicios */
        $sql = "SELECT
                    COUNT(DISTINCT a.id_agendamiento) AS agendamientos_atendidos,
                    COUNT(DISTINCT a.id_cliente) AS clientas_atendidas,
                    COUNT(d.id_detalle_agendamiento) AS servicios_realizados

                FROM agendamientos a

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento = a.id_estado_agendamiento

                LEFT JOIN detalles_agendamientos d
                    ON d.id_agendamiento = a.id_agendamiento

                WHERE a.fecha BETWEEN :inicio AND :fin
                    AND ea.nombre_estado = 'REALIZADA'";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':inicio', $inicio, PDO::PARAM_STR);
        $stmt->bindValue(':fin', $fin, PDO::PARAM_STR);

        $stmt->execute();

        $resumen = array_map('intval', $stmt->fetch(PDO::FETCH_ASSOC));

        /* Buscar la primera atención de todo el historial antes de filtrar el mes */
        $sql = "SELECT COUNT(*)
                FROM (
                    SELECT
                        a.id_cliente,
                        MIN(a.fecha) AS primera_atencion

                    FROM agendamientos a

                    INNER JOIN estados_agendamientos ea
                        ON ea.id_estado_agendamiento = a.id_estado_agendamiento

                    WHERE ea.nombre_estado = 'REALIZADA'
                        AND a.id_cliente IS NOT NULL

                    GROUP BY a.id_cliente
                ) primeras_atenciones

                WHERE primera_atencion BETWEEN :inicio AND :fin";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':inicio', $inicio, PDO::PARAM_STR);
        $stmt->bindValue(':fin', $fin, PDO::PARAM_STR);

        $stmt->execute();

        $resumen['clientas_nuevas'] = (int) $stmt->fetchColumn();

        return $resumen;
    }


    /* ================================================= */
    /* AGENDAMIENTOS DEL MINI CALENDARIO */
    /* ================================================= */

    private function consultarCalendario(string $inicio, string $fin): array
    {
        $sql = "SELECT
                    a.fecha,
                    COUNT(*) AS cantidad
                FROM agendamientos a
                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento = a.id_estado_agendamiento
                WHERE a.fecha BETWEEN :inicio AND :fin
                    AND ea.nombre_estado <> 'CANCELADA'
                GROUP BY a.fecha
                ORDER BY a.fecha ASC";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':inicio', $inicio, PDO::PARAM_STR);
        $stmt->bindValue(':fin', $fin, PDO::PARAM_STR);

        $stmt->execute();

        $dias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dias as &$dia) {
            $dia['cantidad'] = (int) $dia['cantidad'];
        }

        unset($dia);

        return $dias;
    }
}