<?php

namespace App\Model;

use App\Config\Database;
use App\Traits\FormatoReporteTrait;
use DateTimeImmutable;
use InvalidArgumentException;
use PDO;


/* ===================================================== */
/* GESTIÓN DE REPORTES */
/* ===================================================== */

class ReportesM extends Database
{
    /* Formato compartido por los reportes */
    use FormatoReporteTrait;

    /* Conexión del modelo */
    private PDO $conexionMD;

    /* Cantidad máxima de filas por reporte */
    private const LIMITE_FILAS = 2000;


    /* ================================================= */
    /* CATÁLOGO DE REPORTES */
    /* ================================================= */

    /* Los reportes pendientes no ejecutan consultas */
    private const CATALOGO = [

        /* Agenda */
        'agendamientos' => [
            'titulo' => 'Agenda de citas',
            'grupo' => 'Agenda',
            'fechas' => true,
            'descripcion' => 'Citas registradas durante el período seleccionado.',
            'filtros' => ['cliente', 'especialista', 'servicio', 'estado', 'tipo'],
            'nota' => 'El monto es el valor registrado en la cita; no acredita pagos ni precios finales por servicio.'
        ],

        'resumen_citas' => [
            'titulo' => 'Resumen de citas por estado y tipo',
            'grupo' => 'Agenda',
            'fechas' => true,
            'descripcion' => 'Cantidad de citas agrupadas por estado y tipo de agendamiento.',
            'filtros' => ['cliente', 'especialista', 'servicio', 'estado', 'tipo'],
            'nota' => 'Las cantidades corresponden a citas, aunque una cita incluya varios servicios.'
        ],

        'demanda' => [
            'titulo' => 'Citas por día y franja horaria',
            'grupo' => 'Agenda',
            'fechas' => true,
            'descripcion' => 'Distribución de las citas según su fecha y hora de inicio.',
            'filtros' => ['especialista', 'servicio', 'tipo'],
            'nota' => 'Agrupa por la hora de inicio prevista. No calcula ocupación ni tiempos reales de atención.'
        ],

        'bloqueos' => [
            'titulo' => 'Bloqueos de horario',
            'grupo' => 'Agenda',
            'fechas' => true,
            'descripcion' => 'Períodos de indisponibilidad registrados para las especialistas.',
            'filtros' => ['especialista', 'estado_bloqueo'],
            'nota' => 'Se muestra el estado actual de cada bloqueo, no un historial de sus modificaciones.'
        ],


        /* Servicios */
        'realizados' => [
            'titulo' => 'Atenciones realizadas',
            'grupo' => 'Servicios',
            'fechas' => true,
            'descripcion' => 'Pendiente de integrar Registro de Servicio.',
            'filtros' => ['cliente', 'especialista', 'servicio'],
            'disponible' => false,
            'nota' => 'Se habilitará con los datos finales de la atención y su fecha correspondiente.'
        ],

        'servicios_frecuentes' => [
            'titulo' => 'Servicios más realizados',
            'grupo' => 'Servicios',
            'fechas' => true,
            'descripcion' => 'Pendiente de integrar Registro de Servicio.',
            'filtros' => ['especialista', 'servicio'],
            'disponible' => false,
            'nota' => 'Contará los servicios efectivamente realizados, separados de las citas.'
        ],

        'ficha_atencion' => [
            'titulo' => 'Ficha técnica de una atención',
            'grupo' => 'Servicios',
            'fechas' => true,
            'descripcion' => 'Pendiente de integrar Registro de Servicio y la selección de una atención.',
            'filtros' => ['cliente', 'especialista'],
            'disponible' => false,
            'nota' => 'Reunirá notas, servicios, insumos y evidencias disponibles de la atención seleccionada.'
        ],

        'catalogo_servicios' => [
            'titulo' => 'Catálogo de servicios',
            'grupo' => 'Servicios',
            'fechas' => false,
            'descripcion' => 'Servicios ofrecidos y sus datos actuales de configuración.',
            'filtros' => ['servicio', 'estado_registro'],
            'nota' => 'Los precios y duraciones son los actuales del catálogo, no valores históricos aplicados.'
        ],


        /* Clientes */
        'clientes' => [
            'titulo' => 'Directorio de clientes',
            'grupo' => 'Clientes',
            'fechas' => false,
            'descripcion' => 'Identificación, contacto y estado de los clientes registrados.',
            'filtros' => ['cliente', 'estado_registro'],
            'nota' => 'No se filtra por fecha de alta porque la tabla clientes no registra ese dato.'
        ],

        'clientes_frecuentes' => [
            'titulo' => 'Clientes frecuentes',
            'grupo' => 'Clientes',
            'fechas' => true,
            'descripcion' => 'Pendiente de integrar las atenciones efectivamente realizadas.',
            'filtros' => ['cliente', 'especialista'],
            'disponible' => false,
            'nota' => 'La frecuencia se calculará con atenciones realizadas, no con reservas o cancelaciones.'
        ],

        'clientes_sin_atencion' => [
            'titulo' => 'Clientes sin atención reciente',
            'grupo' => 'Clientes',
            'fechas' => true,
            'descripcion' => 'Pendiente de integrar el historial real de atenciones.',
            'filtros' => ['cliente'],
            'disponible' => false,
            'nota' => 'Distinguirá la falta de atención reciente de los clientes que nunca han sido atendidos.'
        ],

        'historial' => [
            'titulo' => 'Historial de atenciones por cliente',
            'grupo' => 'Clientes',
            'fechas' => true,
            'descripcion' => 'Pendiente de integrar Registro de Servicio y el historial compartido.',
            'filtros' => ['cliente', 'servicio'],
            'disponible' => false,
            'nota' => 'Utilizará los datos históricos guardados de cada atención.'
        ],


        /* Especialistas */
        'especialistas' => [
            'titulo' => 'Actividad de especialistas',
            'grupo' => 'Especialistas',
            'fechas' => true,
            'descripcion' => 'Citas asignadas y sus estados registrados durante el período.',
            'filtros' => ['especialista', 'servicio'],
            'nota' => 'Los minutos son previstos. El monto corresponde a citas en estado REALIZADA; no acredita cobros ni precios finales por servicio.'
        ],


        /* Insumos */
        'existencias' => [
            'titulo' => 'Disponibilidad de insumos',
            'grupo' => 'Insumos',
            'fechas' => false,
            'descripcion' => 'Existencias actuales, mínimo registrado y proveedores vinculados.',
            'filtros' => ['insumo', 'proveedor', 'estado_registro', 'disponibilidad'],
            'nota' => 'Consulta el stock guardado actualmente; no reconstruye existencias históricas ni descuenta consumos.'
        ],

        'reposicion' => [
            'titulo' => 'Insumos que requieren reposición',
            'grupo' => 'Insumos',
            'fechas' => false,
            'descripcion' => 'Insumos cuyo stock está en el mínimo o por debajo.',
            'filtros' => ['insumo', 'proveedor', 'estado_registro'],
            'nota' => 'Es un listado para revisión y reposición; no genera órdenes de compra.'
        ],

        'vencimientos' => [
            'titulo' => 'Vencimiento de insumos',
            'grupo' => 'Insumos',
            'fechas' => true,
            'descripcion' => 'Insumos cuya fecha de vencimiento está dentro del rango elegido.',
            'filtros' => ['insumo', 'proveedor', 'estado_registro'],
            'nota' => 'El rango se aplica a la fecha de vencimiento. No representa fecha de compra o de registro.'
        ],

        'consumos' => [
            'titulo' => 'Consumos por atención',
            'grupo' => 'Insumos',
            'fechas' => true,
            'descripcion' => 'Pendiente de integrar el guardado de consumos en Registro de Servicio.',
            'filtros' => ['insumo', 'especialista'],
            'disponible' => false,
            'nota' => 'Utilizará las cantidades registradas de los insumos realmente empleados.'
        ],

        'consumos_acumulados' => [
            'titulo' => 'Consumo acumulado por insumo',
            'grupo' => 'Insumos',
            'fechas' => true,
            'descripcion' => 'Pendiente de integrar el guardado de consumos en Registro de Servicio.',
            'filtros' => ['insumo', 'especialista'],
            'disponible' => false,
            'nota' => 'Sumará cantidades por insumo; no mezclará unidades de materiales distintos.'
        ],


        /* Proveedores */
        'proveedores' => [
            'titulo' => 'Directorio de proveedores',
            'grupo' => 'Proveedores',
            'fechas' => false,
            'descripcion' => 'Empresa, contacto, estado e insumos vinculados.',
            'filtros' => ['proveedor', 'insumo', 'estado_registro'],
            'nota' => 'Las vinculaciones son las actuales; no representan compras ni entregas realizadas.'
        ],

        'proveedores_insumos' => [
            'titulo' => 'Relación de proveedores e insumos',
            'grupo' => 'Proveedores',
            'fechas' => false,
            'descripcion' => 'Insumos asociados a cada proveedor que cumple los filtros.',
            'filtros' => ['proveedor', 'insumo', 'estado_registro'],
            'nota' => 'El filtro de estado se aplica al proveedor. No se evalúan precios de compra ni calidad de abastecimiento.'
        ],


        /* General */
        'resumen_operativo' => [
            'titulo' => 'Resumen operativo',
            'grupo' => 'General',
            'fechas' => true,
            'descripcion' => 'Indicadores de citas del período y alertas actuales de insumos.',
            'filtros' => ['especialista'],
            'nota' => 'Las citas corresponden al período; las alertas de insumos corresponden al stock actual general, sin filtro por especialista.'
        ]
    ];


    /* ================================================= */
    /* CONEXIÓN CON LA BASE DE DATOS */
    /* ================================================= */

    /* Obtener la conexión heredada o utilizar la conexión suministrada */
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

    /* Entregar a la vista el catálogo y las opciones de los filtros */
    public function configuracion(): array
    {
        return [
            'reportes' => array_map(
                static function (array $reporte): array {
                    return array_merge(['disponible' => true], $reporte);
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
        /* Incluir los registros inactivos para las consultas históricas */
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

            'estado_bloqueo' => [
                ['id' => 'BLOQUEADO', 'texto' => 'Bloqueado'],
                ['id' => 'DESBLOQUEADO', 'texto' => 'Desbloqueado']
            ],

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
    /* VALIDACIÓN DE LOS FILTROS */
    /* ================================================= */

    private function validar(array $entrada, array $opciones): array
    {
        /* Comprobar el tipo de reporte solicitado */
        $tipo = $entrada['reporte'] ?? '';

        if (!is_string($tipo) || !isset(self::CATALOGO[$tipo])) {
            throw new InvalidArgumentException('Seleccione un tipo de reporte válido.');
        }

        $definicion = self::CATALOGO[$tipo];

        if (($definicion['disponible'] ?? true) === false) {
            throw new InvalidArgumentException($definicion['descripcion']);
        }


        /* Determinar los filtros permitidos para el reporte */
        $permitidos = array_merge(['reporte'], $definicion['filtros']);

        if ($definicion['fechas']) {
            $permitidos = array_merge($permitidos, ['fecha_inicio', 'fecha_fin']);
        }


        /* Rechazar campos ajenos al reporte y valores que no sean cadenas */
        foreach ($entrada as $campo => $valor) {
            if (!in_array($campo, $permitidos, true)) {
                throw new InvalidArgumentException('El reporte recibió un filtro que no corresponde.');
            }

            if (!is_string($valor)) {
                throw new InvalidArgumentException('Los filtros deben contener valores simples.');
            }
        }

        $filtros = ['reporte' => $tipo];


        /* Validar las fechas y el orden del período */
        if ($definicion['fechas']) {
            foreach (['fecha_inicio', 'fecha_fin'] as $campo) {
                $valor = $entrada[$campo] ?? '';

                if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $valor)) {
                    throw new InvalidArgumentException('Indique las dos fechas del período correctamente.');
                }

                $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);

                if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
                    throw new InvalidArgumentException('Indique las dos fechas del período correctamente.');
                }

                $filtros[$campo] = $valor;
            }

            if ($filtros['fecha_inicio'] > $filtros['fecha_fin']) {
                throw new InvalidArgumentException('La fecha inicial no puede ser posterior a la fecha final.');
            }
        }


        /* Comprobar que las opciones seleccionadas sigan disponibles */
        foreach ($definicion['filtros'] as $campo) {
            $valor = $entrada[$campo] ?? '';

            if ($valor === '') {
                continue;
            }

            $ids = array_map('strval', array_column($opciones[$campo], 'id'));

            if (!in_array($valor, $ids, true)) {
                throw new InvalidArgumentException(
                    'Uno de los filtros seleccionados ya no está disponible. Actualice la página.'
                );
            }

            $filtros[$campo] = $valor;
        }

        return $filtros;
    }


    /* ================================================= */
    /* GENERACIÓN DEL REPORTE */
    /* ================================================= */

    public function generar(array $entrada): array
    {
        $opciones = $this->opciones();
        $filtros = $this->validar($entrada, $opciones);
        $tipo = $filtros['reporte'];


        /* Seleccionar el método correspondiente al reporte */
        $metodos = [
            'agendamientos' => 'agenda',
            'resumen_citas' => 'resumenCitas',
            'demanda' => 'demanda',
            'bloqueos' => 'bloqueos',
            'catalogo_servicios' => 'catalogoServicios',
            'clientes' => 'clientes',
            'especialistas' => 'especialistas',
            'existencias' => 'existencias',
            'reposicion' => 'existencias',
            'vencimientos' => 'existencias',
            'proveedores' => 'proveedores',
            'proveedores_insumos' => 'proveedores',
            'resumen_operativo' => 'resumenOperativo'
        ];

        $resultado = $this->{$metodos[$tipo]}($filtros);


        /* Comprobar el límite antes de preparar la salida */
        if (count($resultado['filas']) > self::LIMITE_FILAS) {
            throw new InvalidArgumentException(
                'La consulta supera 2000 filas. Acote el período o los filtros para consultar y exportar el resultado completo.'
            );
        }

        $etiquetas = ['Tipo de reporte: ' . self::CATALOGO[$tipo]['titulo']];


        /* Etiquetas utilizadas para describir los filtros aplicados */
        $nombres = [
            'cliente' => 'Cliente',
            'especialista' => 'Especialista',
            'servicio' => 'Servicio',
            'estado' => 'Estado de cita',
            'tipo' => 'Tipo de cita',
            'insumo' => 'Insumo',
            'proveedor' => 'Proveedor',
            'estado_registro' => 'Estado del registro',
            'disponibilidad' => 'Disponibilidad',
            'estado_bloqueo' => 'Estado del bloqueo'
        ];

        if (isset($filtros['fecha_inicio'])) {
            $etiquetas[] = 'Período: ' . $this->formatearFecha($filtros['fecha_inicio']) .
                ' al ' . $this->formatearFecha($filtros['fecha_fin']);

        } else {
            $etiquetas[] = 'Alcance: registros actuales';
        }

        foreach ($nombres as $campo => $nombre) {
            if (!isset($filtros[$campo])) {
                continue;
            }

            foreach ($opciones[$campo] as $opcion) {
                if ((string) $opcion['id'] === $filtros[$campo]) {
                    $etiquetas[] = $nombre . ': ' . $opcion['texto'];
                    break;
                }
            }
        }


        /* Preparar la estructura compartida por la vista y el documento */
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

    private function seleccionar(string $sql, array $parametros = []): array
    {
        $stmt = $this->conexionMD->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* DEFINICIÓN DE COLUMNAS */
    /* ================================================= */

    /* Describir las columnas utilizadas en pantalla y en el documento */
    private function columna(string $clave, string $titulo, int $ancho, string $formato = 'texto'): array
    {
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


        /* Incorporar los filtros que contienen un valor seleccionado */
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


        /* Incorporar los servicios asociados a cada cita */
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


        /* Sumar centavos enteros y contar cada cliente una sola vez */
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
                $this->columna('fecha', 'Fecha', 20, 'fecha'),
                $this->columna('hora', 'Hora', 15, 'hora'),
                $this->columna('cliente', 'Cliente', 35),
                $this->columna('especialista', 'Especialista', 32),
                $this->columna('servicios', 'Servicios', 49),
                $this->columna('tipo', 'Tipo', 29),
                $this->columna('estado', 'Estado', 25),
                $this->columna('minutos', 'Minutos', 18, 'entero'),
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
                        ON e.id_estado_agendamiento = a.id_estado_agendamiento
                    WHERE a.id_cliente = c.id_cliente
                      AND e.nombre_estado = 'REALIZADA'
                ) AS realizadas
             FROM clientes c
             INNER JOIN personas p ON p.id_persona = c.id_persona
             WHERE $where
             ORDER BY p.nombre, p.apellido, c.id_cliente
             LIMIT 2001",
            $p
        );


        /* Contar los clientes activos dentro del resultado */
        $activos = count(array_filter($filas, static function ($fila) {
            return $fila['estado'] === 'ACTIVO';
        }));

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


        /* Mantener las fechas en el LEFT JOIN para incluir especialistas sin citas */
        if (isset($f['especialista'])) {
            $where .= ' AND u.id_usuario = :usuario_filtro';
            $p['usuario_filtro'] = $f['especialista'];
        }

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
                COALESCE(
                    SUM(
                        CASE WHEN e.nombre_estado = 'REALIZADA'
                        THEN a.monto_total ELSE 0 END
                    ),
                    0
                ) AS monto
             FROM usuarios u
             INNER JOIN personas p ON p.id_persona = u.id_persona
             INNER JOIN roles r ON r.id_rol = u.id_rol
             LEFT JOIN agendamientos a
                ON a.id_usuario_asignado = u.id_usuario
                AND $condiciones
             LEFT JOIN estados_agendamientos e
                ON e.id_estado_agendamiento = a.id_estado_agendamiento
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
                $this->columna('monto', 'Monto en citas realizadas', 41, 'decimal')
            ],
            'filas' => $filas,
            'resumen' => [
                ['titulo' => 'Especialistas', 'valor' => count($filas)],
                ['titulo' => 'Citas', 'valor' => array_sum(array_column($filas, 'citas'))],
                ['titulo' => 'Realizadas', 'valor' => array_sum(array_column($filas, 'realizadas'))]
            ]
        ];
    }


    /* ================================================= */
    /* FILTROS DE INSUMOS */
    /* ================================================= */

    private function condicionesInsumos(array $f, array &$p, string $alias): string
    {
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


        /* Filtrar los insumos vinculados al proveedor seleccionado */
        if (isset($f['proveedor'])) {
            $where .= " AND EXISTS (
                SELECT 1
                FROM proveedores_insumos pi
                WHERE pi.id_insumo = $alias.id_insumo
                  AND pi.id_proveedor = :proveedor
            )";

            $p['proveedor'] = $f['proveedor'];
        }


        /* Aplicar la condición correspondiente a la disponibilidad */
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
        $p = [];
        $where = $this->condicionesInsumos($f, $p);


        /* Limitar el resultado a los insumos que requieren reposición */
        if ($f['reporte'] === 'reposicion') {
            $where .= ' AND i.stock_actual <= i.stock_minimo';
        }


        /* Aplicar el período a la fecha de vencimiento */
        if ($f['reporte'] === 'vencimientos') {
            $where .= ' AND i.fecha_vencimiento BETWEEN :inicio_vencimiento AND :fin_vencimiento';

            $p['inicio_vencimiento'] = $f['fecha_inicio'];
            $p['fin_vencimiento'] = $f['fecha_fin'];
        }

        $filas = $this->seleccionar(
            "SELECT
                i.id_insumo,
                i.nombre_insumo AS insumo,
                i.presentacion,
                i.tipo_control AS control,
                i.stock_actual AS stock,
                i.stock_minimo AS minimo,
                i.fecha_vencimiento AS vencimiento,
                i.estado_insumo AS estado
             FROM insumos i
             WHERE $where
             ORDER BY i.nombre_insumo, i.id_insumo
             LIMIT 2001",
            $p
        );


        /* Incorporar los proveedores vinculados a cada insumo */
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


        /* Calcular los indicadores del stock consultado */
        $bajos = count(array_filter($filas, static function ($fila) {
            return (int) $fila['stock'] <= (int) $fila['minimo'];
        }));

        $agotados = count(array_filter($filas, static function ($fila) {
            return (int) $fila['stock'] === 0;
        }));

        return [
            'columnas' => [
                $this->columna('insumo', 'Insumo', 44),
                $this->columna('presentacion', 'Presentación', 32),
                $this->columna('control', 'Control', 29),
                $this->columna('stock', 'Stock', 16, 'entero'),
                $this->columna('minimo', 'Mínimo', 17, 'entero'),
                $this->columna('vencimiento', 'Vencimiento', 27, 'fecha'),
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


        /* Filtrar los proveedores vinculados al insumo seleccionado */
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


        /* Incorporar los insumos vinculados a cada proveedor */
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

        $activos = count(array_filter($filas, static function ($fila) {
            return $fila['estado'] === 'ACTIVO';
        }));

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

    /* Agrupar las citas sin multiplicarlas por sus servicios */
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
                ['titulo' => 'Citas', 'valor' => array_sum(array_column($filas, 'cantidad'))]
            ]
        ];
    }


    /* ================================================= */
    /* CITAS POR DÍA Y FRANJA HORARIA */
    /* ================================================= */

    private function demanda(array $f): array
    {
        $p = [];
        $where = $this->condicionesAgenda($f, $p);

        $filas = $this->seleccionar(
            "SELECT
                a.fecha,
                CONCAT(SUBSTR(a.hora, 1, 2), ':00') AS franja,
                COUNT(*) AS citas,
                SUM(
                    CASE WHEN e.nombre_estado = 'REALIZADA'
                    THEN 1 ELSE 0 END
                ) AS realizadas,
                SUM(
                    CASE WHEN e.nombre_estado = 'CANCELADA'
                    THEN 1 ELSE 0 END
                ) AS canceladas
             FROM agendamientos a
             INNER JOIN estados_agendamientos e
                ON e.id_estado_agendamiento = a.id_estado_agendamiento
             WHERE $where
             GROUP BY a.fecha, SUBSTR(a.hora, 1, 2)
             ORDER BY a.fecha, franja
             LIMIT 2001",
            $p
        );

        return [
            'columnas' => [
                $this->columna('fecha', 'Fecha', 55, 'fecha'),
                $this->columna('franja', 'Franja de inicio', 55),
                $this->columna('citas', 'Citas registradas', 53, 'entero'),
                $this->columna('realizadas', 'En estado realizada', 53, 'entero'),
                $this->columna('canceladas', 'Canceladas', 53, 'entero')
            ],
            'filas' => $filas,
            'resumen' => [
                ['titulo' => 'Citas', 'valor' => array_sum(array_column($filas, 'citas'))]
            ]
        ];
    }


    /* ================================================= */
    /* REPORTE DE BLOQUEOS */
    /* ================================================= */

    private function bloqueos(array $f): array
    {
        $p = [
            'fecha_inicio' => $f['fecha_inicio'],
            'fecha_fin' => $f['fecha_fin']
        ];

        $where = 'b.fecha BETWEEN :fecha_inicio AND :fecha_fin';

        $columnas = [
            'especialista' => 'b.id_usuario_especialista',
            'estado_bloqueo' => 'b.estado_bloqueo'
        ];

        foreach ($columnas as $campo => $columna) {
            if (isset($f[$campo])) {
                $where .= " AND $columna = :$campo";
                $p[$campo] = $f[$campo];
            }
        }

        $filas = $this->seleccionar(
            "SELECT
                b.fecha,
                b.hora_inicio,
                b.hora_fin,
                CONCAT(p.nombre, ' ', p.apellido) AS especialista,
                b.motivo,
                b.estado_bloqueo AS estado
             FROM bloqueos_horarios b
             INNER JOIN usuarios u
                ON u.id_usuario = b.id_usuario_especialista
             INNER JOIN personas p
                ON p.id_persona = u.id_persona
             WHERE $where
             ORDER BY b.fecha, b.hora_inicio, b.id_bloqueo
             LIMIT 2001",
            $p
        );

        return [
            'columnas' => [
                $this->columna('fecha', 'Fecha', 28, 'fecha'),
                $this->columna('hora_inicio', 'Desde', 22, 'hora'),
                $this->columna('hora_fin', 'Hasta', 22, 'hora'),
                $this->columna('especialista', 'Especialista', 57),
                $this->columna('motivo', 'Motivo', 99),
                $this->columna('estado', 'Estado', 41)
            ],
            'filas' => $filas,
            'resumen' => [
                ['titulo' => 'Registros de bloqueo', 'valor' => count($filas)]
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
                ['titulo' => 'Servicios del catálogo', 'valor' => count($filas)]
            ]
        ];
    }


    /* ================================================= */
    /* RESUMEN OPERATIVO */
    /* ================================================= */

    private function resumenOperativo(array $f): array
    {
        $p = [];
        $where = $this->condicionesAgenda($f, $p);


        /* Recuperar los indicadores de citas correspondientes al período */
        $citas = $this->seleccionar(
            "SELECT
                COUNT(*) AS citas,
                COUNT(DISTINCT a.id_cliente) AS clientes,
                COALESCE(
                    SUM(
                        CASE WHEN e.nombre_estado = 'REALIZADA'
                        THEN 1 ELSE 0 END
                    ),
                    0
                ) AS realizadas,
                COALESCE(
                    SUM(
                        CASE WHEN e.nombre_estado = 'CANCELADA'
                        THEN 1 ELSE 0 END
                    ),
                    0
                ) AS canceladas
             FROM agendamientos a
             INNER JOIN estados_agendamientos e
                ON e.id_estado_agendamiento = a.id_estado_agendamiento
             WHERE $where",
            $p
        )[0];


        /* Recuperar las alertas correspondientes al stock actual general */
        $stock = $this->seleccionar(
            "SELECT
                COUNT(*) AS insumos,
                COALESCE(
                    SUM(
                        CASE WHEN stock_actual <= stock_minimo
                        THEN 1 ELSE 0 END
                    ),
                    0
                ) AS bajos,
                COALESCE(
                    SUM(
                        CASE WHEN stock_actual = 0
                        THEN 1 ELSE 0 END
                    ),
                    0
                ) AS agotados,
                COALESCE(
                    SUM(
                        CASE WHEN fecha_vencimiento < :hoy
                        THEN 1 ELSE 0 END
                    ),
                    0
                ) AS vencidos
             FROM insumos
             WHERE estado_insumo = 'ACTIVO'",
            ['hoy' => date('Y-m-d')]
        )[0];


        /* Preparar los indicadores que se mostrarán como filas */
        $filas = [
            [
                'indicador' => 'Citas registradas en el período',
                'cantidad' => $citas['citas']
            ],
            [
                'indicador' => 'Clientes distintos con citas en el período',
                'cantidad' => $citas['clientes']
            ],
            [
                'indicador' => 'Citas en estado REALIZADA',
                'cantidad' => $citas['realizadas']
            ],
            [
                'indicador' => 'Citas canceladas en el período',
                'cantidad' => $citas['canceladas']
            ],
            [
                'indicador' => 'Insumos activos actuales',
                'cantidad' => $stock['insumos']
            ],
            [
                'indicador' => 'Insumos activos en mínimo o por debajo',
                'cantidad' => $stock['bajos']
            ],
            [
                'indicador' => 'Insumos activos agotados',
                'cantidad' => $stock['agotados']
            ],
            [
                'indicador' => 'Insumos activos vencidos antes de hoy',
                'cantidad' => $stock['vencidos']
            ]
        ];

        return [
            'columnas' => [
                $this->columna('indicador', 'Indicador', 210),
                $this->columna('cantidad', 'Cantidad', 59, 'entero')
            ],
            'filas' => $filas,
            'resumen' => [
                [
                    'titulo' => 'Citas del período',
                    'valor' => $citas['citas']
                ],
                [
                    'titulo' => 'Insumos actuales en mínimo o por debajo',
                    'valor' => $stock['bajos']
                ]
            ]
        ];
    }


    /* ================================================= */
    /* DATOS ASOCIADOS A LOS REGISTROS */
    /* ================================================= */

    /* Recuperar los nombres asociados sin duplicar las filas principales */
    private function anexarLista(array $filas, string $clave, string $destino, string $sql): array
    {
        if (!$filas || count($filas) > self::LIMITE_FILAS) {
            return $filas;
        }

        $ids = array_column($filas, $clave);


        /* Preparar un marcador por cada identificador recibido */
        $marcadores = implode(',', array_fill(0, count($ids), '?'));

        $listas = [];

        foreach ($this->seleccionar(sprintf($sql, $marcadores), $ids) as $detalle) {
            $listas[$detalle['id']][$detalle['texto']] = $detalle['texto'];
        }


        /* Incorporar las listas de nombres al resultado principal */
        foreach ($filas as &$fila) {
            $fila[$destino] = implode(', ', array_values($listas[$fila[$clave]] ?? []));
        }

        unset($fila);

        return $filas;
    }
}