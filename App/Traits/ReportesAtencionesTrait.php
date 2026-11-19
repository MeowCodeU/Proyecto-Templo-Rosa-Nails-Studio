<?php

namespace App\Traits;


/* ===================================================== */
/* REPORTES DE ATENCIONES Y CONSUMOS */
/* ===================================================== */

trait ReportesAtencionesTrait
{
    /* ================================================= */
    /* FILTROS DE ATENCIONES REALIZADAS */
    /* ================================================= */

    private function condicionesAtenciones(
        array $f,
        array &$p,
        bool $porServicio = false
    ): string {
        $servicio = $f['servicio'] ?? null;

        if ($porServicio) {
            unset($f['servicio']);
        }

        $where = $this->condicionesAgenda($f, $p);
        $where .= " AND e.nombre_estado = 'REALIZADA'";

        /* Limitar los detalles cuando se consulta un servicio específico */
        if ($porServicio && $servicio !== null) {
            $where .= ' AND d.id_servicio = :servicio';
            $p['servicio'] = $servicio;
        }

        return $where;
    }


    /* ================================================= */
    /* TOTALES DE PRECIOS APLICADOS */
    /* ================================================= */

    private function sumarPreciosAplicados(
        array $filas,
        string $campo
    ): string {
        $centavos = 0;

        foreach ($filas as $fila) {

            /* Evitar presentar una suma parcial como total completo */
            if (!isset($fila[$campo]) || !is_numeric($fila[$campo])) {
                return 'No disponible';
            }

            $centavos += (int) round((float) $fila[$campo] * 100);
        }

        return $this->formatearMonto($centavos / 100);
    }


    /* ================================================= */
    /* ATENCIONES REALIZADAS */
    /* ================================================= */

    private function atencionesRealizadas(array $f): array
    {
        $p = [];
        $where = $this->condicionesAtenciones($f, $p);

        /* Mantener una fila por atención, aunque incluya varios servicios */
        $filas = $this->seleccionar(
            "SELECT
                a.id_agendamiento,
                a.id_cliente,
                a.fecha,
                a.hora,
                CASE
                    WHEN a.inicio_atencion IS NOT NULL
                        AND a.fin_atencion IS NOT NULL
                        AND a.fin_atencion >= a.inicio_atencion
                    THEN TIMESTAMPDIFF(MINUTE, a.inicio_atencion, a.fin_atencion)
                    ELSE NULL
                END AS duracion_real_minutos,
                COALESCE(
                    CONCAT(pc.nombre, ' ', pc.apellido),
                    'Sin cliente asociado'
                ) AS cliente,
                CONCAT(pe.nombre, ' ', pe.apellido) AS especialista,
                (
                    SELECT COUNT(*)
                    FROM detalles_agendamientos d
                    WHERE d.id_agendamiento = a.id_agendamiento
                ) AS cantidad_servicios,
                (
                    SELECT CASE
                        WHEN COUNT(*) > 0
                            AND COUNT(*) = COUNT(d.precio_aplicado)
                        THEN SUM(d.precio_aplicado)
                        ELSE NULL
                    END
                    FROM detalles_agendamientos d
                    WHERE d.id_agendamiento = a.id_agendamiento
                ) AS monto

             FROM agendamientos a

             INNER JOIN estados_agendamientos e
                ON e.id_estado_agendamiento = a.id_estado_agendamiento

             LEFT JOIN clientes c
                ON c.id_cliente = a.id_cliente

             LEFT JOIN personas pc
                ON pc.id_persona = c.id_persona

             INNER JOIN usuarios u
                ON u.id_usuario = a.id_usuario_asignado

             INNER JOIN personas pe
                ON pe.id_persona = u.id_persona

             WHERE $where

             ORDER BY a.fecha DESC, a.hora DESC, a.id_agendamiento DESC

             LIMIT 2001",
            $p
        );

        $filas = $this->anexarLista(
            $filas,
            'id_agendamiento',
            'servicios',
            'SELECT d.id_agendamiento AS id, s.nombre_servicio AS texto
             FROM detalles_agendamientos d
             INNER JOIN servicios s ON s.id_servicio = d.id_servicio
             WHERE d.id_agendamiento IN (%s)
             ORDER BY s.nombre_servicio'
        );

        return [
            'columnas' => [
                $this->columna('id_agendamiento', 'Atención', 18, 'entero'),
                $this->columna('fecha', 'Fecha de la cita', 24, 'fecha'),
                $this->columna('hora', 'Hora prevista', 18, 'hora'),
                $this->columna('duracion_real_minutos', 'Duración real (min)', 28, 'entero'),
                $this->columna('cliente', 'Cliente', 47),
                $this->columna('especialista', 'Especialista', 47),
                $this->columna('servicios', 'Servicios realizados', 80),
                $this->columna('monto', 'Importe aplicado', 35, 'decimal')
            ],
            'filas' => $filas,
            'resumen' => [
                [
                    'titulo' => 'Atenciones realizadas',
                    'valor' => count($filas)
                ],
                [
                    'titulo' => 'Servicios realizados',
                    'valor' => array_sum(
                        array_column($filas, 'cantidad_servicios')
                    )
                ],
                [
                    'titulo' => 'Importe aplicado',
                    'valor' => $this->sumarPreciosAplicados($filas, 'monto')
                ]
            ]
        ];
    }


    /* ================================================= */
    /* SERVICIOS MÁS REALIZADOS */
    /* ================================================= */

    private function serviciosFrecuentes(array $f): array
    {
        $p = [];
        $where = $this->condicionesAtenciones($f, $p, true);

        /* Medir únicamente atenciones de un servicio con tiempos reales */
        $filas = $this->seleccionar(
            "SELECT
                s.nombre_servicio AS servicio,
                COUNT(*) AS cantidad,
                COUNT(DISTINCT a.id_agendamiento) AS atenciones,
                CASE
                    WHEN COUNT(*) = COUNT(d.precio_aplicado)
                    THEN SUM(d.precio_aplicado)
                    ELSE NULL
                END AS monto,
                COUNT(CASE
                    WHEN total_detalles.cantidad_servicios = 1
                        AND a.inicio_atencion IS NOT NULL
                        AND a.fin_atencion IS NOT NULL
                        AND a.fin_atencion >= a.inicio_atencion
                    THEN 1
                END) AS atenciones_medidas,
                ROUND(AVG(CASE
                    WHEN total_detalles.cantidad_servicios = 1
                        AND a.inicio_atencion IS NOT NULL
                        AND a.fin_atencion IS NOT NULL
                        AND a.fin_atencion >= a.inicio_atencion
                    THEN TIMESTAMPDIFF(SECOND, a.inicio_atencion, a.fin_atencion) / 60
                    ELSE NULL
                END), 1) AS promedio_real_minutos

             FROM agendamientos a

             INNER JOIN estados_agendamientos e
                ON e.id_estado_agendamiento = a.id_estado_agendamiento

             INNER JOIN detalles_agendamientos d
                ON d.id_agendamiento = a.id_agendamiento

             INNER JOIN servicios s
                ON s.id_servicio = d.id_servicio

             INNER JOIN (
                SELECT id_agendamiento, COUNT(*) AS cantidad_servicios
                FROM detalles_agendamientos
                GROUP BY id_agendamiento
             ) total_detalles
                ON total_detalles.id_agendamiento = a.id_agendamiento

             WHERE $where

             GROUP BY s.id_servicio, s.nombre_servicio

             ORDER BY cantidad DESC, s.nombre_servicio, s.id_servicio

             LIMIT 2001",
            $p
        );

        return [
            'columnas' => [
                $this->columna('servicio', 'Servicio', 125),
                $this->columna('cantidad', 'Servicios realizados', 45, 'entero'),
                $this->columna('atenciones', 'Atenciones distintas', 45, 'entero'),
                $this->columna('atenciones_medidas', 'Con tiempo real', 39, 'entero'),
                $this->columna('promedio_real_minutos', 'Promedio real (min)', 46, 'decimal'),
                $this->columna('monto', 'Importe aplicado', 54, 'decimal')
            ],
            'filas' => $filas,
            'resumen' => [
                [
                    'titulo' => 'Tipos de servicio',
                    'valor' => count($filas)
                ],
                [
                    'titulo' => 'Servicios realizados',
                    'valor' => array_sum(array_column($filas, 'cantidad'))
                ],
                [
                    'titulo' => 'Importe aplicado',
                    'valor' => $this->sumarPreciosAplicados($filas, 'monto')
                ]
            ]
        ];
    }


    /* ================================================= */
    /* DETALLES DE LOS SERVICIOS REALIZADOS */
    /* ================================================= */

    private function detallesAtenciones(array $f): array
    {
        $p = [];
        $where = $this->condicionesAtenciones($f, $p, true);

        return $this->seleccionar(
            "SELECT
                a.id_agendamiento,
                a.fecha,
                a.hora,
                CONCAT(pe.nombre, ' ', pe.apellido) AS especialista,
                s.nombre_servicio AS servicio,
                d.precio_aplicado,
                d.nota_tecnica,
                (
                    SELECT COUNT(*)
                    FROM fotos_servicios fs
                    WHERE fs.id_detalle_agendamiento =
                        d.id_detalle_agendamiento
                ) AS fotografias

             FROM agendamientos a

             INNER JOIN estados_agendamientos e
                ON e.id_estado_agendamiento = a.id_estado_agendamiento

             INNER JOIN detalles_agendamientos d
                ON d.id_agendamiento = a.id_agendamiento

             INNER JOIN servicios s
                ON s.id_servicio = d.id_servicio

             INNER JOIN usuarios u
                ON u.id_usuario = a.id_usuario_asignado

             INNER JOIN personas pe
                ON pe.id_persona = u.id_persona

             WHERE $where

             ORDER BY
                a.fecha DESC,
                a.hora DESC,
                a.id_agendamiento DESC,
                d.id_detalle_agendamiento

             LIMIT 2001",
            $p
        );
    }


    /* ================================================= */
    /* HISTORIAL DE ATENCIONES POR CLIENTE */
    /* ================================================= */

    private function historialAtenciones(array $f): array
    {
        $filas = $this->detallesAtenciones($f);

        $atenciones = array_unique(
            array_column($filas, 'id_agendamiento')
        );

        return [
            'columnas' => [
                $this->columna('id_agendamiento', 'Atención', 18, 'entero'),
                $this->columna('fecha', 'Fecha de la cita', 24, 'fecha'),
                $this->columna('hora', 'Hora prevista', 17, 'hora'),
                $this->columna('servicio', 'Servicio realizado', 52),
                $this->columna('especialista', 'Especialista', 44),
                $this->columna('precio_aplicado', 'Precio aplicado', 30, 'decimal'),
                $this->columna('nota_tecnica', 'Nota técnica', 67),
                $this->columna('fotografias', 'Fotos registradas', 17, 'entero')
            ],
            'filas' => $filas,
            'resumen' => [
                [
                    'titulo' => 'Atenciones realizadas',
                    'valor' => count($atenciones)
                ],
                [
                    'titulo' => 'Servicios del historial',
                    'valor' => count($filas)
                ],
                [
                    'titulo' => 'Importe aplicado',
                    'valor' => $this->sumarPreciosAplicados(
                        $filas,
                        'precio_aplicado'
                    )
                ]
            ]
        ];
    }


    /* ================================================= */
    /* CLIENTES FRECUENTES */
    /* ================================================= */

    private function clientesFrecuentes(array $f): array
    {
        $p = [];
        $where = $this->condicionesAtenciones($f, $p);

        $filas = $this->seleccionar(
            "SELECT
                pc.cedula,
                CONCAT(pc.nombre, ' ', pc.apellido) AS cliente,
                c.estado_cliente AS estado,
                COUNT(DISTINCT a.id_agendamiento) AS atenciones,
                COUNT(d.id_detalle_agendamiento) AS servicios,
                MIN(a.fecha) AS primera,
                MAX(a.fecha) AS ultima,
                CASE
                    WHEN COUNT(d.id_detalle_agendamiento) > 0
                        AND COUNT(d.id_detalle_agendamiento) =
                            COUNT(d.precio_aplicado)
                        AND COUNT(DISTINCT a.id_agendamiento) =
                            COUNT(DISTINCT d.id_agendamiento)
                    THEN SUM(d.precio_aplicado)
                    ELSE NULL
                END AS monto

             FROM agendamientos a

             INNER JOIN estados_agendamientos e
                ON e.id_estado_agendamiento = a.id_estado_agendamiento

             INNER JOIN clientes c
                ON c.id_cliente = a.id_cliente

             INNER JOIN personas pc
                ON pc.id_persona = c.id_persona

             LEFT JOIN detalles_agendamientos d
                ON d.id_agendamiento = a.id_agendamiento

             WHERE $where

             GROUP BY
                c.id_cliente,
                pc.cedula,
                pc.nombre,
                pc.apellido,
                c.estado_cliente

             ORDER BY
                atenciones DESC,
                ultima DESC,
                pc.nombre,
                pc.apellido,
                c.id_cliente

             LIMIT 2001",
            $p
        );

        return [
            'columnas' => [
                $this->columna('cedula', 'Cédula', 25),
                $this->columna('cliente', 'Cliente', 61),
                $this->columna('estado', 'Estado actual', 25),
                $this->columna('atenciones', 'Atenciones', 23, 'entero'),
                $this->columna('servicios', 'Servicios', 23, 'entero'),
                $this->columna('primera', 'Primera del período', 37, 'fecha'),
                $this->columna('ultima', 'Última del período', 37, 'fecha'),
                $this->columna('monto', 'Importe aplicado', 38, 'decimal')
            ],
            'filas' => $filas,
            'resumen' => [
                [
                    'titulo' => 'Clientes atendidos',
                    'valor' => count($filas)
                ],
                [
                    'titulo' => 'Atenciones realizadas',
                    'valor' => array_sum(array_column($filas, 'atenciones'))
                ],
                [
                    'titulo' => 'Importe aplicado',
                    'valor' => $this->sumarPreciosAplicados($filas, 'monto')
                ]
            ]
        ];
    }


    /* ================================================= */
    /* CONSUMOS POR ATENCIÓN */
    /* ================================================= */

    private function consumosAtenciones(array $f): array
    {
        $p = [];
        $where = $this->condicionesAgenda($f, $p);
        $where .= " AND e.nombre_estado <> 'CANCELADA'";

        if (isset($f['insumo'])) {
            $where .= ' AND ci.id_insumo = :insumo';
            $p['insumo'] = $f['insumo'];
        }

        /* Reunir los lotes para mostrar una fila por atención e insumo */
        $filas = $this->seleccionar(
            "SELECT
                a.id_agendamiento,
                a.fecha,
                a.hora,
                COALESCE(
                    CONCAT(pc.nombre, ' ', pc.apellido),
                    'Sin cliente asociado'
                ) AS cliente,
                CONCAT(pe.nombre, ' ', pe.apellido) AS especialista,
                e.nombre_estado AS estado,
                i.id_insumo,
                i.nombre_insumo AS insumo,
                i.presentacion,
                i.tipo_control AS control,
                ci.cantidad_usada AS cantidad

             FROM (
                SELECT id_agendamiento, id_insumo, SUM(cantidad_usada) AS cantidad_usada
                FROM consumos_insumos
                GROUP BY id_agendamiento, id_insumo
             ) ci

             INNER JOIN insumos i
                ON i.id_insumo = ci.id_insumo

             INNER JOIN agendamientos a
                ON a.id_agendamiento = ci.id_agendamiento

             INNER JOIN estados_agendamientos e
                ON e.id_estado_agendamiento = a.id_estado_agendamiento

             LEFT JOIN clientes c
                ON c.id_cliente = a.id_cliente

             LEFT JOIN personas pc
                ON pc.id_persona = c.id_persona

             INNER JOIN usuarios u
                ON u.id_usuario = a.id_usuario_asignado

             INNER JOIN personas pe
                ON pe.id_persona = u.id_persona

             WHERE $where

             ORDER BY
                a.fecha DESC,
                a.hora DESC,
                a.id_agendamiento DESC,
                i.nombre_insumo,
                i.id_insumo

             LIMIT 2001",
            $p
        );

        return [
            'columnas' => [
                $this->columna('id_agendamiento', 'Atención', 18, 'entero'),
                $this->columna('fecha', 'Fecha de la cita', 24, 'fecha'),
                $this->columna('cliente', 'Cliente', 40),
                $this->columna('especialista', 'Especialista', 40),
                $this->columna('estado', 'Estado de atención', 30),
                $this->columna('insumo', 'Insumo', 46),
                $this->columna('presentacion', 'Presentación', 30),
                $this->columna('control', 'Control', 24),
                $this->columna('cantidad', 'Cantidad usada', 17, 'entero')
            ],
            'filas' => $filas,
            'resumen' => [
                [
                    'titulo' => 'Atenciones con consumos',
                    'valor' => count(
                        array_unique(
                            array_column($filas, 'id_agendamiento')
                        )
                    )
                ],
                [
                    'titulo' => 'Insumos distintos',
                    'valor' => count(
                        array_unique(
                            array_column($filas, 'id_insumo')
                        )
                    )
                ]
            ]
        ];
    }


    /* ================================================= */
    /* CONSUMO ACUMULADO POR INSUMO */
    /* ================================================= */

    private function consumosAcumulados(array $f): array
    {
        $p = [];
        $where = $this->condicionesAgenda($f, $p);
        $where .= " AND e.nombre_estado <> 'CANCELADA'";

        if (isset($f['insumo'])) {
            $where .= ' AND ci.id_insumo = :insumo';
            $p['insumo'] = $f['insumo'];
        }

        /* Agrupar cada material con su presentación y tipo de control */
        $filas = $this->seleccionar(
            "SELECT
                i.nombre_insumo AS insumo,
                i.presentacion,
                i.tipo_control AS control,
                COUNT(DISTINCT a.id_agendamiento) AS atenciones,
                SUM(ci.cantidad_usada) AS cantidad

             FROM consumos_insumos ci

             INNER JOIN insumos i
                ON i.id_insumo = ci.id_insumo

             INNER JOIN agendamientos a
                ON a.id_agendamiento = ci.id_agendamiento

             INNER JOIN estados_agendamientos e
                ON e.id_estado_agendamiento = a.id_estado_agendamiento

             WHERE $where

             GROUP BY
                i.id_insumo,
                i.nombre_insumo,
                i.presentacion,
                i.tipo_control

             ORDER BY i.nombre_insumo, i.id_insumo

             LIMIT 2001",
            $p
        );

        return [
            'columnas' => [
                $this->columna('insumo', 'Insumo', 95),
                $this->columna('presentacion', 'Presentación', 55),
                $this->columna('control', 'Control', 39),
                $this->columna('atenciones', 'Atenciones distintas', 40, 'entero'),
                $this->columna('cantidad', 'Cantidad usada acumulada', 40, 'entero')
            ],
            'filas' => $filas,
            'resumen' => [
                [
                    'titulo' => 'Insumos con consumo registrado',
                    'valor' => count($filas)
                ]
            ]
        ];
    }
}