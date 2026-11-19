<?php

namespace App\Model;

use App\Config\Database;
use App\Traits\FormatoReporteTrait;
use App\Traits\ReportesAtencionesTrait;
use App\Traits\ValidacionReporteTrait;
use InvalidArgumentException;
use PDO;


/* ===================================================== */
/* GESTIÓN DE REPORTES */
/* ===================================================== */

class ReportesM extends Database
{
    use FormatoReporteTrait;
    use ReportesAtencionesTrait;
    use ValidacionReporteTrait;

    /* Conexión del modelo */
    private PDO $conexionMD;

    /* Cantidad máxima de filas por reporte */
    private const LIMITE_FILAS = 2000;


    /* ================================================= */
    /* CATÁLOGO DE REPORTES */
    /* ================================================= */

    private const CATALOGO = [

        /* Agenda */
        'agendamientos' => [
            'titulo' => 'Agenda de citas',
            'grupo' => 'Agenda',
            'fechas' => true,
            'descripcion' => 'Citas cuya fecha programada está dentro del período seleccionado.',
            'filtros' => ['cliente', 'especialista', 'servicio', 'estado', 'tipo'],
            'nota' =>
                'El monto es el registrado en la cita: previsto en citas pendientes ' .
                'y actualizado por Registro de Servicio al finalizar la atención. ' .
                'No acredita pagos.'
        ],

        'resumen_citas' => [
            'titulo' => 'Resumen de citas por estado y tipo',
            'grupo' => 'Agenda',
            'fechas' => true,
            'descripcion' => 'Cantidad de citas agrupadas por estado y tipo de agendamiento.',
            'filtros' => ['cliente', 'especialista', 'servicio', 'estado', 'tipo'],
            'nota' =>
                'Las cantidades corresponden a citas, aunque una cita incluya varios servicios.'
        ],


        /* Servicios */
        'realizados' => [
            'titulo' => 'Atenciones realizadas',
            'grupo' => 'Servicios',
            'fechas' => true,
            'descripcion' => 'Atenciones realizadas, con duración real, según la fecha de cita del período.',
            'filtros' => ['cliente', 'especialista', 'servicio'],
            'nota' =>
                'Una fila por atención REALIZADA. La duración real se calcula ' .
                'entre el inicio y el fin registrados, no con el tiempo previsto. ' .
                'El filtro de servicio incluye la atención completa. ' .
                'El importe suma precios aplicados y no acredita pagos.'
        ],

        'servicios_frecuentes' => [
            'titulo' => 'Servicios más realizados',
            'grupo' => 'Servicios',
            'fechas' => true,
            'descripcion' => 'Servicios realizados por frecuencia y promedio de duración real disponible.',
            'filtros' => ['especialista', 'servicio'],
            'nota' =>
                'Cuenta los servicios de atenciones REALIZADAS. El promedio real ' .
                'solo considera citas de un servicio con inicio y fin registrados; ' .
                'las citas de varios servicios cuentan, pero no se promedian. ' .
                'Los importes son precios aplicados, no pagos; ' .
                'los totales incompletos se indican como no disponibles.'
        ],

        'catalogo_servicios' => [
            'titulo' => 'Catálogo de servicios',
            'grupo' => 'Servicios',
            'fechas' => false,
            'descripcion' => 'Servicios ofrecidos y sus datos actuales de configuración.',
            'filtros' => ['servicio', 'estado_registro'],
            'nota' =>
                'Los precios y duraciones son los actuales del catálogo, ' .
                'no valores históricos aplicados.'
        ],


        /* Clientes */
        'clientes' => [
            'titulo' => 'Directorio de clientes',
            'grupo' => 'Clientes',
            'fechas' => false,
            'descripcion' => 'Identificación, contacto y estado de los clientes registrados.',
            'filtros' => ['cliente', 'estado_registro'],
            'nota' => ''
        ],

        'clientes_frecuentes' => [
            'titulo' => 'Clientes frecuentes',
            'grupo' => 'Clientes',
            'fechas' => true,
            'descripcion' => 'Clientes ordenados por sus atenciones realizadas durante el período.',
            'filtros' => ['cliente', 'especialista'],
            'nota' =>
                'Cada atención se cuenta una vez, aunque incluya varios servicios. ' .
                'Los importes corresponden a precios aplicados; ' .
                'el estado del cliente es el actual.'
        ],

        'historial' => [
            'titulo' => 'Historial de atenciones por cliente',
            'grupo' => 'Clientes',
            'fechas' => true,
            'descripcion' =>
                'Servicios, notas técnicas y precios aplicados ' .
                'de las atenciones realizadas de un cliente.',
            'filtros' => ['cliente', 'servicio'],
            'requeridos' => ['cliente'],
            'nota' =>
                'Seleccione un cliente. Cada fila corresponde a un servicio realizado; ' .
                'el filtro de servicio limita los detalles mostrados. ' .
                'Se indica la cantidad de fotografías, sin incorporar sus imágenes.'
        ],


        /* Especialistas */
        'especialistas' => [
            'titulo' => 'Actividad de especialistas',
            'grupo' => 'Especialistas',
            'fechas' => true,
            'descripcion' => 'Citas asignadas y sus estados registrados durante el período.',
            'filtros' => ['especialista', 'servicio'],
            'nota' =>
                'Los minutos son previstos. El importe suma precios aplicados ' .
                'en atenciones REALIZADAS y no acredita cobros. ' .
                'Si faltan precios aplicados, se muestra un guion.'
        ],


        /* Insumos */
        'existencias' => [
            'titulo' => 'Existencias actuales de insumos',
            'grupo' => 'Insumos',
            'fechas' => false,
            'descripcion' => 'Existencias actuales, mínimo registrado, próximo vencimiento y proveedores vinculados.',
            'filtros' => ['insumo', 'proveedor', 'estado_registro', 'disponibilidad'],
            'nota' =>
                'El stock corresponde a las unidades registradas en inventario, ' .
                'incluidos los lotes vencidos que aún tengan existencias.'
        ],

        'reposicion' => [
            'titulo' => 'Insumos en mínimo o agotados',
            'grupo' => 'Insumos',
            'fechas' => false,
            'descripcion' => 'Insumos cuyo stock actual es igual o inferior al mínimo registrado.',
            'filtros' => ['insumo', 'proveedor', 'estado_registro'],
            'nota' =>
                'Es un listado para revisión y reposición; no genera órdenes de compra.'
        ],

        'vencimientos' => [
            'titulo' => 'Vencimiento de insumos',
            'grupo' => 'Insumos',
            'fechas' => true,
            'descripcion' => 'Lotes disponibles cuyo vencimiento está dentro del rango seleccionado.',
            'filtros' => ['insumo', 'proveedor', 'estado_registro'],
            'nota' =>
                'Se muestra una fila por lote con existencias y fecha de vencimiento ' .
                'dentro del período. Un mismo insumo puede aparecer varias veces.'
        ],

        'consumos' => [
            'titulo' => 'Consumos por atención',
            'grupo' => 'Insumos',
            'fechas' => true,
            'descripcion' => 'Cantidades enteras de insumos guardadas para cada atención no cancelada.',
            'filtros' => ['insumo', 'especialista'],
            'nota' =>
                'Incluye consumos guardados de atenciones en proceso o realizadas. ' .
                'El período corresponde a la fecha del agendamiento; ' .
                'consultar este reporte no vuelve a descontar existencias.'
        ],

        'consumos_acumulados' => [
            'titulo' => 'Consumo acumulado por insumo',
            'grupo' => 'Insumos',
            'fechas' => true,
            'descripcion' => 'Cantidades enteras utilizadas, agrupadas por insumo durante el período.',
            'filtros' => ['insumo', 'especialista'],
            'nota' =>
                'Incluye consumos guardados de atenciones no canceladas. ' .
                'Cada insumo conserva su presentación y tipo de control; ' .
                'no se suman cantidades de materiales diferentes.'
        ],


        /* Proveedores */
        'proveedores' => [
            'titulo' => 'Directorio de proveedores',
            'grupo' => 'Proveedores',
            'fechas' => false,
            'descripcion' => 'Empresa, contacto, estado e insumos vinculados.',
            'filtros' => ['proveedor', 'insumo', 'estado_registro'],
            'nota' =>
                'Las vinculaciones son las actuales; ' .
                'no representan compras ni entregas realizadas.'
        ]
    ];


    /* ================================================= */
    /* CONEXIÓN CON LA BASE DE DATOS */
    /* ================================================= */

    public function __construct(?PDO $conexion = null)
    {
        $this->conexionMD = $conexion ?? $this->getConnection();
    }


    /* ================================================= */
    /* COMPROBACIÓN DEL ACCESO */
    /* ================================================= */

    /* Comprobar el acceso definido en RF-10 para la Administradora del Negocio */
    public function puedeConsultar(int $idUsuario): bool
    {
        $filas = $this->seleccionar(
            "SELECT u.id_usuario
             FROM usuarios u
             INNER JOIN roles r ON r.id_rol = u.id_rol
             WHERE u.id_usuario = :usuario
                AND u.estado_usuario = 'ACTIVO'
                AND r.estado_rol = 'ACTIVO'
                AND r.nombre_rol = 'ADMINISTRADORA DEL NEGOCIO'",
            ['usuario' => $idUsuario]
        );

        return count($filas) === 1;
    }


    /* ================================================= */
    /* CONFIGURACIÓN DEL MÓDULO */
    /* ================================================= */

    public function configuracion(): array
    {
        return [
            'reportes' => array_map(
                static function (array $reporte): array {
                    return array_merge(
                        ['disponible' => true],
                        $reporte
                    );
                },
                self::CATALOGO
            ),
            'opciones' => $this->opciones(),
            'limite' => self::LIMITE_FILAS
        ];
    }


    /* ================================================= */
    /* OPCIONES DE LOS FILTROS */
    /* ================================================= */

    private function opciones(): array
    {
        /* Incluir registros inactivos para conservar las consultas históricas */
        return [
            'cliente' => $this->seleccionar(
                "SELECT c.id_cliente AS id,
                        CONCAT(
                            p.cedula, ' · ', p.nombre, ' ', p.apellido,
                            CASE
                                WHEN c.estado_cliente = 'INACTIVO'
                                THEN ' (inactivo)'
                                ELSE ''
                            END
                        ) AS texto
                 FROM clientes c
                 INNER JOIN personas p ON p.id_persona = c.id_persona
                 ORDER BY p.nombre, p.apellido"
            ),

            'especialista' => $this->seleccionar(
                "SELECT u.id_usuario AS id,
                        CONCAT(
                            p.nombre, ' ', p.apellido,
                            CASE
                                WHEN u.estado_usuario = 'INACTIVO'
                                THEN ' (inactivo)'
                                ELSE ''
                            END
                        ) AS texto
                 FROM usuarios u
                 INNER JOIN personas p ON p.id_persona = u.id_persona
                 INNER JOIN roles r ON r.id_rol = u.id_rol
                 WHERE r.nombre_rol = 'ESPECIALISTA EN MANICURA'
                 ORDER BY p.nombre, p.apellido"
            ),

            'servicio' => $this->seleccionar(
                'SELECT id_servicio AS id, nombre_servicio AS texto
                 FROM servicios
                 ORDER BY nombre_servicio'
            ),

            'estado' => $this->seleccionar(
                'SELECT id_estado_agendamiento AS id, nombre_estado AS texto
                 FROM estados_agendamientos
                 ORDER BY id_estado_agendamiento'
            ),

            'tipo' => $this->seleccionar(
                'SELECT id_tipo_agendamiento AS id, nombre_tipo_agendamiento AS texto
                 FROM tipos_agendamientos
                 ORDER BY id_tipo_agendamiento'
            ),

            'insumo' => $this->seleccionar(
                'SELECT id_insumo AS id, nombre_insumo AS texto
                 FROM insumos
                 ORDER BY nombre_insumo'
            ),

            'proveedor' => $this->seleccionar(
                'SELECT id_proveedor AS id, nombre_empresa AS texto
                 FROM proveedores
                 ORDER BY nombre_empresa'
            ),

            'estado_registro' => [
                ['id' => 'ACTIVO', 'texto' => 'Activo'],
                ['id' => 'INACTIVO', 'texto' => 'Inactivo']
            ],

            'disponibilidad' => [
                ['id' => 'BAJO', 'texto' => 'En mínimo o por debajo'],
                ['id' => 'AGOTADO', 'texto' => 'Agotado'],
                ['id' => 'DISPONIBLE', 'texto' => 'Por encima del mínimo']
            ]
        ];
    }


    /* ================================================= */
    /* GENERACIÓN DEL REPORTE */
    /* ================================================= */

    public function generar(mixed $entrada): array
    {
        /* Comprobar los campos y las fechas antes de consultar las opciones */
        $this->validarEstructuraReporte($entrada);

        $opciones = $this->opciones();
        $filtros = $this->validarFiltrosReporte($entrada, $opciones);
        $tipo = $filtros['reporte'];

        $metodos = [
            'agendamientos' => 'agenda',
            'resumen_citas' => 'resumenCitas',
            'realizados' => 'atencionesRealizadas',
            'servicios_frecuentes' => 'serviciosFrecuentes',
            'clientes_frecuentes' => 'clientesFrecuentes',
            'historial' => 'historialAtenciones',
            'consumos' => 'consumosAtenciones',
            'consumos_acumulados' => 'consumosAcumulados',
            'catalogo_servicios' => 'catalogoServicios',
            'clientes' => 'clientes',
            'especialistas' => 'especialistas',
            'existencias' => 'existencias',
            'reposicion' => 'existencias',
            'vencimientos' => 'existencias',
            'proveedores' => 'proveedores'
        ];

        $resultado = $this->{$metodos[$tipo]}($filtros);

        /* Exigir filtros más precisos cuando se supera el límite de salida */
        if (count($resultado['filas']) > self::LIMITE_FILAS) {
            throw new InvalidArgumentException(
                'La consulta supera 2000 filas. Acote el período o los filtros ' .
                'para consultar y exportar el resultado completo.'
            );
        }

        $etiquetas = [
            'Tipo de reporte: ' . self::CATALOGO[$tipo]['titulo']
        ];

        $nombres = [
            'cliente' => 'Cliente',
            'especialista' => 'Especialista',
            'servicio' => 'Servicio',
            'estado' => 'Estado de cita',
            'tipo' => 'Tipo de cita',
            'insumo' => 'Insumo',
            'proveedor' => 'Proveedor',
            'estado_registro' => 'Estado del registro',
            'disponibilidad' => 'Nivel de existencias'
        ];

        if (isset($filtros['fecha_inicio'])) {
            $etiquetas[] =
                'Período: ' .
                $this->formatearFecha($filtros['fecha_inicio']) .
                ' al ' .
                $this->formatearFecha($filtros['fecha_fin']);
        }

        foreach ($nombres as $campo => $nombre) {
            if (!isset($filtros[$campo])) continue;

            foreach ($opciones[$campo] as $opcion) {
                if ((string) $opcion['id'] === $filtros[$campo]) {
                    $etiquetas[] = $nombre . ': ' . $opcion['texto'];
                    break;
                }
            }
        }

        /* Compartir la misma estructura entre la pantalla y el PDF */
        return array_merge(
            $resultado,
            [
                'tipo' => $tipo,
                'titulo' => self::CATALOGO[$tipo]['titulo'],
                'nota' => self::CATALOGO[$tipo]['nota'],
                'filtros' => $filtros,
                'filtros_aplicados' => $etiquetas,
                'generado' => date('d/m/Y H:i:s'),
                'total' => count($resultado['filas'])
            ]
        );
    }


    /* ================================================= */
    /* EJECUCIÓN DE CONSULTAS */
    /* ================================================= */

    private function seleccionar(
        string $sql,
        array $parametros = []
    ): array {
        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* DEFINICIÓN DE COLUMNAS */
    /* ================================================= */

    private function columna(
        string $clave,
        string $titulo,
        int $ancho,
        string $formato = 'texto'
    ): array {
        return [
            'clave' => $clave,
            'titulo' => $titulo,
            'ancho' => $ancho,
            'formato' => $formato
        ];
    }


    /* ================================================= */
    /* FILTROS DE AGENDAMIENTO */
    /* ================================================= */

    private function condicionesAgenda(array $f, array &$p): string
    {
        $sql = 'a.fecha BETWEEN :fecha_inicio AND :fecha_fin';

        $p = [
            'fecha_inicio' => $f['fecha_inicio'],
            'fecha_fin' => $f['fecha_fin']
        ];

        $columnas = [
            'cliente' => 'a.id_cliente',
            'especialista' => 'a.id_usuario_asignado',
            'estado' => 'a.id_estado_agendamiento',
            'tipo' => 'a.id_tipo_agendamiento'
        ];

        foreach ($columnas as $campo => $columna) {
            if (isset($f[$campo])) {
                $sql .= " AND $columna = :$campo";
                $p[$campo] = $f[$campo];
            }
        }

        /* Filtrar por servicio sin multiplicar las filas de una cita */
        if (isset($f['servicio'])) {
            $sql .= ' AND EXISTS (
                SELECT 1
                FROM detalles_agendamientos df
                WHERE df.id_agendamiento = a.id_agendamiento
                    AND df.id_servicio = :servicio
            )';

            $p['servicio'] = $f['servicio'];
        }

        return $sql;
    }


    /* ================================================= */
    /* REPORTE DE AGENDAMIENTOS */
    /* ================================================= */

    private function agenda(array $f): array
    {
        $p = [];
        $where = $this->condicionesAgenda($f, $p);

        /* Recuperar cada cita una sola vez */
        $filas = $this->seleccionar(
            "SELECT
                a.id_agendamiento,
                a.id_cliente,
                a.fecha,
                a.hora,
                COALESCE(
                    CONCAT(pc.nombre, ' ', pc.apellido),
                    'Sin cliente asociado'
                ) AS cliente,
                CONCAT(pe.nombre, ' ', pe.apellido) AS especialista,
                t.nombre_tipo_agendamiento AS tipo,
                e.nombre_estado AS estado,
                a.duracion_minutos AS minutos,
                a.monto_total AS monto

             FROM agendamientos a

             LEFT JOIN clientes c
                ON c.id_cliente = a.id_cliente

             LEFT JOIN personas pc
                ON pc.id_persona = c.id_persona

             INNER JOIN usuarios u
                ON u.id_usuario = a.id_usuario_asignado

             INNER JOIN personas pe
                ON pe.id_persona = u.id_persona

             INNER JOIN estados_agendamientos e
                ON e.id_estado_agendamiento = a.id_estado_agendamiento

             INNER JOIN tipos_agendamientos t
                ON t.id_tipo_agendamiento = a.id_tipo_agendamiento

             WHERE $where

             ORDER BY a.fecha, a.hora, a.id_agendamiento

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

        /* Sumar los montos y contar cada cliente una sola vez */
        $centavos = 0;
        $clientes = [];

        foreach ($filas as $fila) {
            $centavos += (int) round((float) $fila['monto'] * 100);

            if ($fila['id_cliente'] !== null) {
                $clientes[(string) $fila['id_cliente']] = true;
            }
        }

        return [
            'columnas' => [
                $this->columna('fecha', 'Fecha de la cita', 20, 'fecha'),
                $this->columna('hora', 'Hora prevista', 15, 'hora'),
                $this->columna('cliente', 'Cliente', 35),
                $this->columna('especialista', 'Especialista', 32),
                $this->columna('servicios', 'Servicios', 49),
                $this->columna('tipo', 'Tipo', 29),
                $this->columna('estado', 'Estado', 25),
                $this->columna('minutos', 'Minutos previstos', 18, 'entero'),
                $this->columna('monto', 'Monto registrado', 27, 'decimal')
            ],
            'filas' => $filas,
            'resumen' => [
                ['titulo' => 'Citas', 'valor' => count($filas)],
                ['titulo' => 'Clientes distintos', 'valor' => count($clientes)],
                [
                    'titulo' => 'Monto registrado',
                    'valor' => $this->formatearMonto($centavos / 100)
                ]
            ]
        ];
    }


    /* ================================================= */
    /* DIRECTORIO DE CLIENTES */
    /* ================================================= */

    private function clientes(array $f): array
    {
        $p = [];
        $where = '1 = 1';

        $columnas = [
            'cliente' => 'c.id_cliente',
            'estado_registro' => 'c.estado_cliente'
        ];

        foreach ($columnas as $campo => $columna) {
            if (isset($f[$campo])) {
                $where .= " AND $columna = :$campo";
                $p[$campo] = $f[$campo];
            }
        }

        $filas = $this->seleccionar(
            "SELECT
                p.cedula,
                CONCAT(p.nombre, ' ', p.apellido) AS cliente,
                p.telefono,
                p.correo,
                p.ciudad,
                c.estado_cliente AS estado,
                (
                    SELECT COUNT(*)
                    FROM agendamientos a
                    INNER JOIN estados_agendamientos e
                        ON e.id_estado_agendamiento =
                            a.id_estado_agendamiento
                    WHERE a.id_cliente = c.id_cliente
                        AND e.nombre_estado = 'REALIZADA'
                ) AS realizadas

             FROM clientes c

             INNER JOIN personas p
                ON p.id_persona = c.id_persona

             WHERE $where

             ORDER BY p.nombre, p.apellido, c.id_cliente

             LIMIT 2001",
            $p
        );

        $activos = count(
            array_filter(
                $filas,
                static function ($fila) {
                    return $fila['estado'] === 'ACTIVO';
                }
            )
        );

        return [
            'columnas' => [
                $this->columna('cedula', 'Cédula', 25),
                $this->columna('cliente', 'Cliente', 52),
                $this->columna('telefono', 'Teléfono', 30),
                $this->columna('correo', 'Correo', 61),
                $this->columna('ciudad', 'Ciudad', 35),
                $this->columna('estado', 'Estado', 22),
                $this->columna('realizadas', 'Citas en estado realizada', 25, 'entero')
            ],
            'filas' => $filas,
            'resumen' => [
                ['titulo' => 'Clientes', 'valor' => count($filas)],
                ['titulo' => 'Activos', 'valor' => $activos]
            ]
        ];
    }


    /* ================================================= */
    /* ACTIVIDAD DE ESPECIALISTAS */
    /* ================================================= */

    private function especialistas(array $f): array
    {
        $p = [];
        $condiciones = $this->condicionesAgenda($f, $p);

        $where = "r.nombre_rol = 'ESPECIALISTA EN MANICURA'";

        if (isset($f['especialista'])) {
            $where .= ' AND u.id_usuario = :usuario_filtro';
            $p['usuario_filtro'] = $f['especialista'];
        }

        /* Agrupar primero los precios para evitar multiplicar las citas */
        $filas = $this->seleccionar(
            "SELECT
                CONCAT(p.nombre, ' ', p.apellido) AS especialista,
                u.estado_usuario AS estado,
                COUNT(a.id_agendamiento) AS citas,
                SUM(
                    CASE WHEN e.nombre_estado = 'REALIZADA'
                    THEN 1 ELSE 0 END
                ) AS realizadas,
                SUM(
                    CASE WHEN e.nombre_estado = 'CANCELADA'
                    THEN 1 ELSE 0 END
                ) AS canceladas,
                COALESCE(
                    SUM(
                        CASE WHEN e.nombre_estado <> 'CANCELADA'
                        THEN a.duracion_minutos ELSE 0 END
                    ),
                    0
                ) AS minutos,
                CASE
                    WHEN SUM(
                        CASE
                            WHEN e.nombre_estado = 'REALIZADA'
                                AND da.monto IS NULL
                            THEN 1 ELSE 0
                        END
                    ) > 0 THEN NULL
                    ELSE COALESCE(
                        SUM(
                            CASE WHEN e.nombre_estado = 'REALIZADA'
                            THEN da.monto ELSE 0 END
                        ),
                        0
                    )
                END AS monto

             FROM usuarios u

             INNER JOIN personas p
                ON p.id_persona = u.id_persona

             INNER JOIN roles r
                ON r.id_rol = u.id_rol

             LEFT JOIN agendamientos a
                ON a.id_usuario_asignado = u.id_usuario
                AND $condiciones

             LEFT JOIN estados_agendamientos e
                ON e.id_estado_agendamiento = a.id_estado_agendamiento

             LEFT JOIN (
                SELECT
                    id_agendamiento,
                    CASE
                        WHEN COUNT(*) = COUNT(precio_aplicado)
                        THEN SUM(precio_aplicado)
                        ELSE NULL
                    END AS monto
                FROM detalles_agendamientos
                GROUP BY id_agendamiento
             ) da ON da.id_agendamiento = a.id_agendamiento

             WHERE $where

             GROUP BY u.id_usuario, p.nombre, p.apellido, u.estado_usuario

             ORDER BY p.nombre, p.apellido, u.id_usuario

             LIMIT 2001",
            $p
        );

        return [
            'columnas' => [
                $this->columna('especialista', 'Especialista', 65),
                $this->columna('estado', 'Estado', 28),
                $this->columna('citas', 'Citas', 22, 'entero'),
                $this->columna('realizadas', 'Realizadas', 29, 'entero'),
                $this->columna('canceladas', 'Canceladas', 29, 'entero'),
                $this->columna('minutos', 'Minutos previstos', 36, 'entero'),
                $this->columna('monto', 'Importe aplicado', 41, 'decimal')
            ],
            'filas' => $filas,
            'resumen' => [
                ['titulo' => 'Especialistas', 'valor' => count($filas)],
                [
                    'titulo' => 'Citas',
                    'valor' => array_sum(array_column($filas, 'citas'))
                ],
                [
                    'titulo' => 'Realizadas',
                    'valor' => array_sum(array_column($filas, 'realizadas'))
                ]
            ]
        ];
    }


    /* ================================================= */
    /* FILTROS DE INSUMOS */
    /* ================================================= */

    private function condicionesInsumos(
        array $f,
        array &$p,
        string $alias
    ): string {
        $where = '1 = 1';

        $columnas = [
            'insumo' => "$alias.id_insumo",
            'estado_registro' => "$alias.estado_insumo"
        ];

        foreach ($columnas as $campo => $columna) {
            if (isset($f[$campo])) {
                $where .= " AND $columna = :$campo";
                $p[$campo] = $f[$campo];
            }
        }

        if (isset($f['proveedor'])) {
            $where .= " AND EXISTS (
                SELECT 1
                FROM proveedores_insumos pi
                WHERE pi.id_insumo = $alias.id_insumo
                    AND pi.id_proveedor = :proveedor
            )";

            $p['proveedor'] = $f['proveedor'];
        }

        if (isset($f['disponibilidad'])) {
            $condiciones = [
                'BAJO' => "$alias.stock_actual <= $alias.stock_minimo",
                'AGOTADO' => "$alias.stock_actual = 0",
                'DISPONIBLE' => "$alias.stock_actual > $alias.stock_minimo"
            ];

            $where .= ' AND ' . $condiciones[$f['disponibilidad']];
        }

        return $where;
    }


    /* ================================================= */
    /* REPORTES DE INSUMOS */
    /* ================================================= */

    private function existencias(array $f): array
    {
        /* Los vencimientos se consultan por lote para no perder fechas distintas */
        if ($f['reporte'] === 'vencimientos') {
            return $this->vencimientosPorLote($f);
        }

        $p = [];
        $where = $this->condicionesInsumos($f, $p, 'i');

        if ($f['reporte'] === 'reposicion') {
            $where .= ' AND i.stock_actual <= i.stock_minimo';
        }

        /* Recuperar únicamente el vencimiento más próximo con unidades disponibles */
        $filas = $this->seleccionar(
            "SELECT
                i.id_insumo,
                i.nombre_insumo AS insumo,
                i.presentacion,
                i.tipo_control AS control,
                i.stock_actual AS stock,
                i.stock_minimo AS minimo,
                (
                    SELECT MIN(l.fecha_vencimiento)
                    FROM lotes_insumos AS l
                    WHERE l.id_insumo = i.id_insumo
                        AND l.cantidad_disponible > 0
                ) AS vencimiento,
                i.estado_insumo AS estado

             FROM insumos AS i

             WHERE $where

             ORDER BY i.nombre_insumo, i.id_insumo

             LIMIT 2001",
            $p
        );

        $filas = $this->anexarLista(
            $filas,
            'id_insumo',
            'proveedores',
            'SELECT pi.id_insumo AS id, p.nombre_empresa AS texto
             FROM proveedores_insumos pi
             INNER JOIN proveedores p ON p.id_proveedor = pi.id_proveedor
             WHERE pi.id_insumo IN (%s)
             ORDER BY p.nombre_empresa'
        );

        $bajos = count(
            array_filter(
                $filas,
                static function ($fila) {
                    return (int) $fila['stock'] <= (int) $fila['minimo'];
                }
            )
        );

        $agotados = count(
            array_filter(
                $filas,
                static function ($fila) {
                    return (int) $fila['stock'] === 0;
                }
            )
        );

        return [
            'columnas' => [
                $this->columna('insumo', 'Insumo', 44),
                $this->columna('presentacion', 'Presentación', 32),
                $this->columna('control', 'Control', 29),
                $this->columna('stock', 'Stock', 16, 'entero'),
                $this->columna('minimo', 'Mínimo', 17, 'entero'),
                $this->columna('vencimiento', 'Próx. vencimiento', 27, 'fecha'),
                $this->columna('estado', 'Estado', 23),
                $this->columna('proveedores', 'Proveedores', 62)
            ],
            'filas' => $filas,
            'resumen' => [
                ['titulo' => 'Insumos', 'valor' => count($filas)],
                ['titulo' => 'En mínimo o por debajo', 'valor' => $bajos],
                ['titulo' => 'Agotados', 'valor' => $agotados]
            ]
        ];
    }


    /* ================================================= */
    /* VENCIMIENTO DE INSUMOS POR LOTE */
    /* ================================================= */

    private function vencimientosPorLote(array $f): array
    {
        $p = [];

        /* El proveedor se filtra por el ingreso real del lote, no por el catálogo */
        $filtrosInsumo = $f;
        unset($filtrosInsumo['proveedor']);

        $where = $this->condicionesInsumos($filtrosInsumo, $p, 'i');
        $where .= ' AND l.cantidad_disponible > 0';
        $where .= ' AND l.fecha_vencimiento BETWEEN :inicio_vencimiento AND :fin_vencimiento';

        $p['inicio_vencimiento'] = $f['fecha_inicio'];
        $p['fin_vencimiento'] = $f['fecha_fin'];

        if (isset($f['proveedor'])) {
            $where .= ' AND movimiento.id_proveedor = :proveedor';
            $p['proveedor'] = $f['proveedor'];
        }

        /* Cada lote aporta sus unidades y su propia fecha de vencimiento */
        $filas = $this->seleccionar(
            "SELECT
                i.id_insumo,
                i.nombre_insumo AS insumo,
                l.id_lote AS lote,
                i.presentacion,
                l.cantidad_disponible AS disponibles,
                l.fecha_vencimiento AS vencimiento,
                COALESCE(proveedor.nombre_empresa, 'Sin proveedor registrado') AS proveedores,
                i.estado_insumo AS estado

             FROM lotes_insumos AS l

             INNER JOIN insumos AS i
                 ON i.id_insumo = l.id_insumo

             LEFT JOIN movimientos_insumos AS movimiento
                 ON movimiento.id_movimiento = (
                     SELECT MIN(entrada.id_movimiento)
                     FROM movimientos_insumos AS entrada
                     WHERE entrada.id_lote = l.id_lote
                         AND entrada.tipo_movimiento = 'INCREMENTO'
                 )

             LEFT JOIN proveedores AS proveedor
                 ON proveedor.id_proveedor = movimiento.id_proveedor

             WHERE $where

             ORDER BY l.fecha_vencimiento, i.nombre_insumo, l.id_lote

             LIMIT 2001",
            $p
        );

        $insumosDistintos = array_unique(array_column($filas, 'id_insumo'));
        $unidadesDisponibles = array_sum(array_map(
            static fn (array $fila): int => (int) $fila['disponibles'],
            $filas
        ));

        return [
            'columnas' => [
                $this->columna('insumo', 'Insumo', 53),
                $this->columna('lote', 'Lote', 18, 'entero'),
                $this->columna('presentacion', 'Presentación', 38),
                $this->columna('disponibles', 'Unidades del lote', 30, 'entero'),
                $this->columna('vencimiento', 'Vencimiento', 32, 'fecha'),
                $this->columna('estado', 'Estado', 25),
                $this->columna('proveedores', 'Proveedor del lote', 56)
            ],
            'filas' => $filas,
            'resumen' => [
                ['titulo' => 'Lotes', 'valor' => count($filas)],
                ['titulo' => 'Insumos', 'valor' => count($insumosDistintos)],
                ['titulo' => 'Unidades en lotes', 'valor' => $unidadesDisponibles]
            ]
        ];
    }


    /* ================================================= */
    /* REPORTES DE PROVEEDORES */
    /* ================================================= */

    private function proveedores(array $f): array
    {
        $p = [];
        $where = '1 = 1';

        $columnas = [
            'proveedor' => 'p.id_proveedor',
            'estado_registro' => 'p.estado_proveedor'
        ];

        foreach ($columnas as $campo => $columna) {
            if (isset($f[$campo])) {
                $where .= " AND $columna = :$campo";
                $p[$campo] = $f[$campo];
            }
        }

        if (isset($f['insumo'])) {
            $where .= ' AND EXISTS (
                SELECT 1
                FROM proveedores_insumos pi
                WHERE pi.id_proveedor = p.id_proveedor
                    AND pi.id_insumo = :insumo
            )';

            $p['insumo'] = $f['insumo'];
        }

        $filas = $this->seleccionar(
            "SELECT
                p.id_proveedor,
                p.rif,
                p.nombre_empresa AS proveedor,
                CONCAT(pc.nombre, ' ', pc.apellido) AS contacto,
                pc.telefono,
                pc.correo,
                p.estado_proveedor AS estado

             FROM proveedores p

             INNER JOIN personas pc
                ON pc.id_persona = p.id_persona_contacto

             WHERE $where

             ORDER BY p.nombre_empresa, p.id_proveedor

             LIMIT 2001",
            $p
        );

        $filas = $this->anexarLista(
            $filas,
            'id_proveedor',
            'insumos',
            'SELECT pi.id_proveedor AS id, i.nombre_insumo AS texto
             FROM proveedores_insumos pi
             INNER JOIN insumos i ON i.id_insumo = pi.id_insumo
             WHERE pi.id_proveedor IN (%s)
             ORDER BY i.nombre_insumo'
        );

        $activos = count(
            array_filter(
                $filas,
                static function ($fila) {
                    return $fila['estado'] === 'ACTIVO';
                }
            )
        );

        return [
            'columnas' => [
                $this->columna('rif', 'RIF', 24),
                $this->columna('proveedor', 'Empresa', 44),
                $this->columna('contacto', 'Contacto', 37),
                $this->columna('telefono', 'Teléfono', 28),
                $this->columna('correo', 'Correo', 48),
                $this->columna('estado', 'Estado', 22),
                $this->columna('insumos', 'Insumos vinculados', 47)
            ],
            'filas' => $filas,
            'resumen' => [
                ['titulo' => 'Proveedores', 'valor' => count($filas)],
                ['titulo' => 'Activos', 'valor' => $activos]
            ]
        ];
    }


    /* ================================================= */
    /* RESUMEN DE CITAS */
    /* ================================================= */

    private function resumenCitas(array $f): array
    {
        $p = [];
        $where = $this->condicionesAgenda($f, $p);

        $filas = $this->seleccionar(
            "SELECT
                e.nombre_estado AS estado,
                t.nombre_tipo_agendamiento AS tipo,
                COUNT(*) AS cantidad

             FROM agendamientos a

             INNER JOIN estados_agendamientos e
                ON e.id_estado_agendamiento = a.id_estado_agendamiento

             INNER JOIN tipos_agendamientos t
                ON t.id_tipo_agendamiento = a.id_tipo_agendamiento

             WHERE $where

             GROUP BY
                e.id_estado_agendamiento,
                e.nombre_estado,
                t.id_tipo_agendamiento,
                t.nombre_tipo_agendamiento

             ORDER BY e.id_estado_agendamiento, t.id_tipo_agendamiento

             LIMIT 2001",
            $p
        );

        return [
            'columnas' => [
                $this->columna('estado', 'Estado', 90),
                $this->columna('tipo', 'Tipo de cita', 120),
                $this->columna('cantidad', 'Cantidad de citas', 59, 'entero')
            ],
            'filas' => $filas,
            'resumen' => [
                [
                    'titulo' => 'Citas',
                    'valor' => array_sum(array_column($filas, 'cantidad'))
                ]
            ]
        ];
    }


    /* ================================================= */
    /* CATÁLOGO DE SERVICIOS */
    /* ================================================= */

    private function catalogoServicios(array $f): array
    {
        $p = [];
        $where = '1 = 1';

        $columnas = [
            'servicio' => 's.id_servicio',
            'estado_registro' => 's.estado_servicio'
        ];

        foreach ($columnas as $campo => $columna) {
            if (isset($f[$campo])) {
                $where .= " AND $columna = :$campo";
                $p[$campo] = $f[$campo];
            }
        }

        $filas = $this->seleccionar(
            "SELECT
                s.nombre_servicio AS servicio,
                s.descripcion,
                s.precio,
                s.duracion_estimada AS minutos,
                s.estado_servicio AS estado

             FROM servicios s

             WHERE $where

             ORDER BY s.nombre_servicio, s.id_servicio

             LIMIT 2001",
            $p
        );

        return [
            'columnas' => [
                $this->columna('servicio', 'Servicio', 65),
                $this->columna('descripcion', 'Descripción', 110),
                $this->columna('precio', 'Precio actual', 34, 'decimal'),
                $this->columna('minutos', 'Minutos estimados', 34, 'entero'),
                $this->columna('estado', 'Estado', 26)
            ],
            'filas' => $filas,
            'resumen' => [
                [
                    'titulo' => 'Servicios del catálogo',
                    'valor' => count($filas)
                ]
            ]
        ];
    }


    /* ================================================= */
    /* DATOS ASOCIADOS A LOS REGISTROS */
    /* ================================================= */

    private function anexarLista(
        array $filas,
        string $clave,
        string $destino,
        string $sql
    ): array {
        if (!$filas || count($filas) > self::LIMITE_FILAS) {
            return $filas;
        }

        $ids = array_column($filas, $clave);

        /* Preparar un marcador por cada identificador consultado */
        $marcadores = implode(
            ',',
            array_fill(0, count($ids), '?')
        );

        $listas = [];

        foreach (
            $this->seleccionar(sprintf($sql, $marcadores), $ids)
            as $detalle
        ) {
            $listas[$detalle['id']][$detalle['texto']] = $detalle['texto'];
        }

        foreach ($filas as &$fila) {
            $fila[$destino] = implode(
                ', ',
                array_values($listas[$fila[$clave]] ?? [])
            );
        }

        unset($fila);

        return $filas;
    }
}