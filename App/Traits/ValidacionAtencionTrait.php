<?php

namespace App\Traits;

use App\Interfaces\DisponibilidadInterface;
use DateTimeImmutable;
use DomainException;
use finfo;
use InvalidArgumentException;
use RuntimeException;


/* ===================================================== */
/* VALIDACIONES DEL FLUJO DE ATENCIÓN */
/* ===================================================== */

trait ValidacionAtencionTrait
{
    use ValidacionTrait;


    /* ================================================= */
    /* VALIDAR LOS DATOS DE UNA CITA */
    /* ================================================= */

    protected function validarDatosAgendamiento(bool $esModificacion): bool
    {
        $this->erroresValidacion = [];
        $datos = [];

        $identificadores = [
            'idCliente' => ['id_cliente', 'Seleccione un cliente válido.'],
            'idUsuarioAsignado' => ['id_usuario_asignado', 'Seleccione una especialista válida.'],
            'idEstadoAgendamiento' => ['id_estado_agendamiento', 'Seleccione un estado válido.'],
            'idTipoAgendamiento' => ['id_tipo_agendamiento', 'Seleccione un tipo de agendamiento válido.']
        ];

        if ($esModificacion) {
            $identificadores['idAgendamiento'] = ['id_agendamiento', 'Seleccione un agendamiento válido.'];
        } else {
            $identificadores['idUsuarioRegistra'] = [
                'id_usuario_registra',
                'No fue posible identificar al usuario que registra la cita.'
            ];
        }

        foreach ($identificadores as $propiedad => [$campo, $mensaje]) {
            $datos[$propiedad] = $this->validarIdentificador($this->$propiedad, $campo, $mensaje);
        }

        $fecha = $this->validarFechaAgenda($this->fecha);
        $hora = $this->validarHoraAgenda($this->hora, 'hora');

        $duracion = $this->validarIdentificador(
            $this->duracionMinutos,
            'duracion_minutos',
            'Ingrese una duración entera entre 1 y 65535 minutos.'
        );

        if ($duracion !== null && $duracion > 65535) {
            $this->erroresValidacion['duracion_minutos'] = 'Ingrese una duración entera entre 1 y 65535 minutos.';
        }

        $monto = $this->validarMontoAgenda($this->montoTotal);
        $servicios = $this->validarServiciosAgenda($this->servicios);
        $observacion = $this->observacionInicial ?? '';

        if (!is_string($observacion) || preg_match('//u', $observacion) !== 1 || str_contains($observacion, "\0")) {
            $this->erroresValidacion['observacion_inicial'] = 'Ingrese una observación válida.';
        } elseif (strlen(trim($observacion)) > 65535) {
            $this->erroresValidacion['observacion_inicial'] = 'La observación supera el tamaño permitido para este campo.';
        }

        if ($this->erroresValidacion !== []) return false;

        foreach ($datos as $propiedad => $valor) $this->$propiedad = $valor;

        $this->fecha = $fecha;
        $this->hora = $hora;
        $this->duracionMinutos = $duracion;
        $this->montoTotal = $monto;
        $this->servicios = $servicios;
        $this->observacionInicial = trim($observacion);

        return true;
    }


    /* ================================================= */
    /* VALIDAR EL IDENTIFICADOR DE UNA CITA */
    /* ================================================= */

    protected function validarIdAgendamientoAgenda(): bool
    {
        $this->erroresValidacion = [];

        $id = $this->validarIdentificador(
            $this->idAgendamiento,
            'id_agendamiento',
            'Seleccione un agendamiento válido.'
        );

        if ($id === null) return false;

        $this->idAgendamiento = $id;

        return true;
    }


    /* ================================================= */
    /* VALIDAR EL MONTO DEL AGENDAMIENTO */
    /* ================================================= */

    protected function validarMontoAgenda(mixed $entrada): ?string
    {
        if ((!is_string($entrada) && !is_int($entrada) && !is_float($entrada)) ||
            (is_float($entrada) && !is_finite($entrada))) {
            $this->erroresValidacion['monto_total'] = 'Ingrese un monto entre 0 y 99999999.99, con un máximo de dos decimales.';
            return null;
        }

        $valor = trim((string) $entrada);

        if ($valor === '' || preg_match('/\A([0-9]*)(?:\.([0-9]{1,2}))?\z/', $valor, $partes) !== 1) {
            $this->erroresValidacion['monto_total'] = 'Ingrese un monto entre 0 y 99999999.99, con un máximo de dos decimales.';
            return null;
        }

        $entero = ltrim($partes[1], '0');
        $entero = $entero === '' ? '0' : $entero;

        if (strlen($entero) > 8) {
            $this->erroresValidacion['monto_total'] = 'Ingrese un monto entre 0 y 99999999.99, con un máximo de dos decimales.';
            return null;
        }

        return $entero . '.' . str_pad($partes[2] ?? '', 2, '0');
    }


    /* ================================================= */
    /* VALIDAR LOS SERVICIOS SELECCIONADOS */
    /* ================================================= */

    protected function validarServiciosAgenda(mixed $entrada): ?array
    {
        if (!is_array($entrada) || array_values($entrada) !== $entrada || $entrada === [] || count($entrada) > 1000) {
            $this->erroresValidacion['servicios'] = 'Seleccione al menos un servicio y utilice una selección válida.';
            return null;
        }

        $servicios = [];

        foreach ($entrada as $valor) {
            $id = $this->validarIdentificador(
                $valor,
                'servicios',
                'La selección de servicios contiene un identificador inválido.'
            );

            if ($id === null) return null;

            $servicios[$id] = $id;
        }

        return array_values($servicios);
    }


    /* ================================================= */
    /* VALIDAR LOS DATOS DE UN BLOQUEO */
    /* ================================================= */

    protected function validarDatosBloqueo(bool $esModificacion): bool
    {
        $this->erroresValidacion = [];

        $idBloqueo = $esModificacion
            ? $this->validarIdentificador($this->idBloqueo, 'id_bloqueo', 'Seleccione un bloqueo válido.')
            : null;

        $idEspecialista = $this->validarIdentificador(
            $this->idUsuarioEspecialista,
            'id_usuario_especialista',
            'Seleccione una especialista válida.'
        );

        $idRegistra = $esModificacion
            ? null
            : $this->validarIdentificador(
                $this->idUsuarioRegistra,
                'id_usuario_registra',
                'No fue posible identificar al usuario que registra el bloqueo.'
            );

        $fecha = $this->validarFechaAgenda($this->fecha);
        $inicio = $this->validarHoraAgenda($this->horaInicio, 'hora_inicio');
        $fin = $this->validarHoraAgenda($this->horaFin, 'hora_fin');
        $motivo = $this->motivo ?? '';

        if (!is_string($motivo) || preg_match('//u', $motivo) !== 1 || str_contains($motivo, "\0")) {
            $this->erroresValidacion['motivo'] = 'Ingrese un motivo válido.';
        } elseif ($this->longitud(trim($motivo)) > 255) {
            $this->erroresValidacion['motivo'] = 'El motivo no puede superar 255 caracteres.';
        }

        if ($inicio !== null && $fin !== null && $fin <= $inicio) {
            $this->erroresValidacion['hora_fin'] = 'La hora final debe ser posterior a la hora inicial.';
        }

        if ($this->erroresValidacion !== []) return false;

        if ($esModificacion) {
            $this->idBloqueo = $idBloqueo;
        } else {
            $this->idUsuarioRegistra = $idRegistra;
        }

        $this->idUsuarioEspecialista = $idEspecialista;
        $this->fecha = $fecha;
        $this->horaInicio = $inicio;
        $this->horaFin = $fin;
        $this->motivo = trim($motivo);

        return true;
    }


    /* ================================================= */
    /* VALIDAR EL BLOQUEO ANTES DE DESBLOQUEAR */
    /* ================================================= */

    protected function validarIdBloqueoAgenda(): bool
    {
        $this->erroresValidacion = [];

        $id = $this->validarIdentificador(
            $this->idBloqueo,
            'id_bloqueo',
            'Seleccione un bloqueo válido.'
        );

        if ($id === null) return false;

        $this->idBloqueo = $id;

        return true;
    }


    /* ================================================= */
    /* COMPROBAR LA DISPONIBILIDAD DEL HORARIO */
    /* ================================================= */

    public function comprobarDisponibilidadAgenda(
        array $datos,
        DisponibilidadInterface $citas,
        DisponibilidadInterface $bloqueos,
        bool $esBloqueo = false
    ): array {
        $this->erroresValidacion = [];

        $campoEspecialista = $esBloqueo ? 'id_usuario_especialista' : 'id_usuario_asignado';
        $campoId = $esBloqueo ? 'id_bloqueo' : 'id_agendamiento';
        $campoInicio = $esBloqueo ? 'hora_inicio' : 'hora';

        $idEspecialista = $this->validarIdentificador(
            $datos[$campoEspecialista] ?? null,
            $campoEspecialista,
            'Seleccione una especialista válida.'
        );

        $fecha = $this->validarFechaAgenda($datos['fecha'] ?? null);
        $horaInicio = $this->validarHoraAgenda($datos[$campoInicio] ?? null, $campoInicio);
        $idRecibido = $datos[$campoId] ?? null;
        $idExcluir = null;

        if (!in_array($idRecibido, [null, '', 0, '0'], true)) {
            $idExcluir = $this->validarIdentificador(
                $idRecibido,
                $campoId,
                'Seleccione un registro válido.'
            );
        }

        if ($esBloqueo) {
            $horaFin = $this->validarHoraAgenda($datos['hora_fin'] ?? null, 'hora_fin');

            if ($horaInicio !== null && $horaFin !== null && $horaFin <= $horaInicio) {
                $this->erroresValidacion['hora_fin'] = 'La hora final debe ser posterior a la hora inicial.';
            }

        } else {
            $horaFin = null;

            $duracion = $this->validarIdentificador(
                $datos['duracion_minutos'] ?? null,
                'duracion_minutos',
                'Ingrese una duración entera entre 1 y 65535 minutos.'
            );

            if ($duracion !== null && $duracion > 65535) {
                $this->erroresValidacion['duracion_minutos'] = 'Ingrese una duración entera entre 1 y 65535 minutos.';
            }

            if ($fecha !== null && $horaInicio !== null && $duracion !== null && $duracion <= 65535) {
                $inicio = new DateTimeImmutable($fecha . ' ' . $horaInicio);
                $fin = $inicio->modify('+' . $duracion . ' minutes');
                $horaFin = $fin->format('H:i:s');

                /* Evitar consultar como si la hora final perteneciera al mismo día */
                if ($fin->format('Y-m-d') !== $fecha) {
                    $this->erroresValidacion['duracion_minutos'] = 'La atención debe finalizar dentro de la fecha seleccionada.';
                }
            }
        }

        if ($this->erroresValidacion !== []) {
            return [
                'disponible' => false,
                'tipo' => 'datos_invalidos',
                'mensaje' => implode(' ', $this->erroresValidacion),
                'campo' => array_key_first($this->erroresValidacion),
                'errores' => $this->erroresValidacion
            ];
        }

        if ($idExcluir !== null) $this->comprobarGestionAgenda($idExcluir, $esBloqueo);

        /* Aplicar el contrato compartido a las dos fuentes de ocupación */
        if ($bloqueos->existeConflictoHorario(
            $idEspecialista,
            $fecha,
            $horaInicio,
            $horaFin,
            $esBloqueo ? $idExcluir : null
        )) {
            return [
                'disponible' => false,
                'tipo' => 'bloqueo',
                'mensaje' => $esBloqueo
                    ? 'La especialista ya posee otro bloqueo dentro del horario seleccionado.'
                    : 'La especialista posee un bloqueo dentro del horario seleccionado.'
            ];
        }

        if ($citas->existeConflictoHorario(
            $idEspecialista,
            $fecha,
            $horaInicio,
            $horaFin,
            $esBloqueo ? null : $idExcluir
        )) {
            return [
                'disponible' => false,
                'tipo' => 'cita',
                'mensaje' => $esBloqueo
                    ? 'No es posible bloquear este período porque la especialista posee una cita dentro del horario seleccionado.'
                    : 'La especialista ya tiene otra cita que coincide con el horario seleccionado. Elija otro horario u otra especialista.'
            ];
        }

        return [
            'disponible' => true,
            'tipo' => 'disponible',
            'mensaje' => $esBloqueo
                ? 'El horario se encuentra disponible para realizar el bloqueo.'
                : 'La especialista se encuentra disponible en el horario seleccionado.'
        ];
    }


    /* ================================================= */
    /* COMPROBAR EL REGISTRO ANTES DE GESTIONARLO */
    /* ================================================= */

    public function comprobarGestionAgenda(mixed $entrada, bool $esBloqueo = false): array
    {
        $this->erroresValidacion = [];

        $campo = $esBloqueo ? 'id_bloqueo' : 'id_agendamiento';
        $id = $this->validarIdentificador($entrada, $campo, 'Seleccione un registro válido.');

        if ($id === null) throw new InvalidArgumentException(implode(' ', $this->erroresValidacion));

        foreach ($this->consultar() as $registro) {
            if ((int) ($registro[$campo] ?? 0) !== $id) continue;

            if (!$esBloqueo && ($registro['gestion_permitida'] ?? false) !== true) {
                throw new DomainException(
                    ($registro['motivo_bloqueo'] ?? '') ?: 'La cita no permite modificaciones ni cancelaciones.'
                );
            }

            return $registro;
        }

        throw new DomainException(
            $esBloqueo
                ? 'El bloqueo ya no se encuentra activo. Actualice el calendario.'
                : 'El agendamiento ya no se encuentra disponible. Actualice el calendario.',
            404
        );
    }


    /* ================================================= */
    /* VALIDAR UNA FECHA DEL CALENDARIO */
    /* ================================================= */

    protected function validarFechaAgenda(mixed $entrada, string $campo = 'fecha'): ?string
    {
        if (!is_string($entrada) || str_contains($entrada, "\0")) {
            $this->erroresValidacion[$campo] = 'Ingrese una fecha válida.';
            return null;
        }

        $fecha = trim($entrada);

        if (preg_match('/\A([0-9]{4})-([0-9]{2})-([0-9]{2})\z/', $fecha, $partes) !== 1 ||
            (int) $partes[1] < 1000 || !checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1])) {
            $this->erroresValidacion[$campo] = 'Ingrese una fecha válida.';
            return null;
        }

        return $fecha;
    }


    /* ================================================= */
    /* VALIDAR UNA HORA DEL CALENDARIO */
    /* ================================================= */

    protected function validarHoraAgenda(mixed $entrada, string $campo): ?string
    {
        if (!is_string($entrada) || str_contains($entrada, "\0")) {
            $this->erroresValidacion[$campo] = 'Ingrese una hora válida.';
            return null;
        }

        $hora = trim($entrada);

        if (preg_match('/\A(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?\z/', $hora) !== 1) {
            $this->erroresValidacion[$campo] = 'Ingrese una hora válida.';
            return null;
        }

        return strlen($hora) === 5 ? $hora . ':00' : $hora;
    }


    /* ================================================= */
    /* VALIDAR LOS IDENTIFICADORES DEL MÓDULO */
    /* ================================================= */

    protected function validarIdRegistroServicio(mixed $entrada, string $campo = 'id_agendamiento'): int
    {
        $this->erroresValidacion = [];

        $mensajes = [
            'id_agendamiento' => 'Seleccione un agendamiento válido.',
            'id_detalle_agendamiento' => 'Seleccione un servicio válido de la atención.',
            'id_cliente' => 'Seleccione una clienta válida.',
            'id_foto' => 'Seleccione una fotografía válida.'
        ];

        $id = $this->validarIdentificador(
            $entrada,
            $campo,
            $mensajes[$campo] ?? 'Seleccione un identificador válido.'
        );

        if ($id === null) throw new InvalidArgumentException(implode(' ', $this->erroresValidacion));

        return $id;
    }


    /* ================================================= */
    /* VALIDAR LOS DATOS DEL SERVICIO REALIZADO */
    /* ================================================= */

    protected function validarDatosServicioRealizado(
        mixed $idAgendamiento,
        mixed $idDetalleAgendamiento,
        mixed $precioAplicado,
        mixed $notaTecnica,
        mixed $fotografia = null,
        mixed $destacadaCatalogo = false
    ): array {
        $this->erroresValidacion = [];

        $idCita = $this->validarIdentificador(
            $idAgendamiento,
            'id_agendamiento',
            'Seleccione un agendamiento válido.'
        );

        $idDetalle = $this->validarIdentificador(
            $idDetalleAgendamiento,
            'id_detalle_agendamiento',
            'Seleccione un servicio válido de la atención.'
        );

        $precio = $this->validarPrecioRegistro($precioAplicado);
        $nota = $notaTecnica ?? '';

        if (!is_string($nota) || preg_match('//u', $nota) !== 1 || str_contains($nota, "\0")) {
            $this->erroresValidacion['nota_tecnica'] = 'Ingrese una nota técnica válida.';
        } elseif (strlen($nota) > 65535) {
            $this->erroresValidacion['nota_tecnica'] = 'La nota técnica supera el tamaño permitido para este campo.';
        }

        if (!in_array($destacadaCatalogo, [0, 1, '0', '1', false, true], true)) {
            $this->erroresValidacion['destacada_catalogo'] = 'La selección de la fotografía para el catálogo no es válida.';
        }

        $contenidoFoto = $this->validarFotografiaRegistro($fotografia);

        if ($this->erroresValidacion !== []) throw new InvalidArgumentException(implode(' ', $this->erroresValidacion));

        return [
            'id_agendamiento' => $idCita,
            'id_detalle_agendamiento' => $idDetalle,
            'precio_aplicado' => $precio,
            'nota_tecnica' => trim($nota),
            'fotografia' => $contenidoFoto,
            'destacada_catalogo' => in_array($destacadaCatalogo, [1, '1', true], true)
        ];
    }


    /* ================================================= */
    /* VALIDAR EL PRECIO APLICADO */
    /* ================================================= */

    protected function validarPrecioRegistro(mixed $entrada): ?string
    {
        $mensaje = 'Ingrese un precio entre 0 y 99999999.99, con un máximo de dos decimales.';

        if ((!is_string($entrada) && !is_int($entrada) && !is_float($entrada)) ||
            (is_float($entrada) && !is_finite($entrada))) {
            $this->erroresValidacion['precio_aplicado'] = $mensaje;
            return null;
        }

        if (is_string($entrada) && str_contains($entrada, "\0")) {
            $this->erroresValidacion['precio_aplicado'] = $mensaje;
            return null;
        }

        $precio = trim((string) $entrada);

        if (preg_match('/\A([0-9]{1,8})(?:\.([0-9]{1,2}))?\z/', $precio, $partes) !== 1) {
            $this->erroresValidacion['precio_aplicado'] = $mensaje;
            return null;
        }

        $entero = ltrim($partes[1], '0');
        $entero = $entero === '' ? '0' : $entero;

        return $entero . '.' . str_pad($partes[2] ?? '', 2, '0');
    }


    /* ================================================= */
    /* VALIDAR LA LISTA DE INSUMOS UTILIZADOS */
    /* ================================================= */

    protected function validarInsumosRegistro(mixed $idAgendamiento, mixed $insumos): array
    {
        $this->erroresValidacion = [];
        $seleccionados = [];

        $idCita = $this->validarIdentificador(
            $idAgendamiento,
            'id_agendamiento',
            'Seleccione un agendamiento válido.'
        );

        if (!is_array($insumos) || array_values($insumos) !== $insumos || count($insumos) > 1000) {
            $this->erroresValidacion['insumos'] = 'Envíe una lista válida de los insumos utilizados.';

        } else {
            foreach ($insumos as $indice => $insumo) {
                $fila = $indice + 1;

                if (!is_array($insumo)) {
                    $this->erroresValidacion['insumos'] = "Los datos del insumo de la fila $fila no son válidos.";
                    break;
                }

                $idInsumo = $this->validarIdentificador(
                    $insumo['id_insumo'] ?? null,
                    'insumos',
                    "Seleccione un insumo válido en la fila $fila."
                );

                if ($idInsumo === null) break;

                $cantidad = $this->validarIdentificador(
                    $insumo['cantidad'] ?? null,
                    'insumos',
                    "Ingrese una cantidad entera mayor que cero en la fila $fila."
                );

                if ($cantidad === null) break;

                if ($cantidad > 99999999) {
                    $this->erroresValidacion['insumos'] = "La cantidad de la fila $fila supera el máximo permitido.";
                    break;
                }

                if (isset($seleccionados[$idInsumo])) {
                    $this->erroresValidacion['insumos'] = 'Un mismo insumo no puede aparecer más de una vez.';
                    break;
                }

                $seleccionados[$idInsumo] = $cantidad;
            }
        }

        if ($this->erroresValidacion !== []) throw new InvalidArgumentException(implode(' ', $this->erroresValidacion));

        return [
            'id_agendamiento' => $idCita,
            'insumos' => $seleccionados
        ];
    }


    /* ================================================= */
    /* VALIDAR LA FOTOGRAFÍA DEL SERVICIO */
    /* ================================================= */

    protected function validarFotografiaRegistro(mixed $entrada): ?string
    {
        if ($entrada === null) return null;

        if (is_array($entrada)) {
            if (!isset($entrada['error']) || !is_int($entrada['error'])) {
                $this->erroresValidacion['foto_servicio'] = 'No fue posible recibir la fotografía seleccionada.';
                return null;
            }

            if ($entrada['error'] === UPLOAD_ERR_NO_FILE) return null;

            if (in_array($entrada['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                $this->erroresValidacion['foto_servicio'] = 'La fotografía supera el tamaño permitido para la carga.';
                return null;
            }

            if ($entrada['error'] !== UPLOAD_ERR_OK) {
                $this->erroresValidacion['foto_servicio'] = 'La fotografía no se recibió correctamente. Selecciónela nuevamente.';
                return null;
            }

            $rutaTemporal = $entrada['tmp_name'] ?? '';

            if (!is_string($rutaTemporal) || str_contains($rutaTemporal, "\0") || !is_uploaded_file($rutaTemporal)) {
                $this->erroresValidacion['foto_servicio'] = 'El archivo recibido no es una carga válida.';
                return null;
            }

            $tamano = filesize($rutaTemporal);

            if ($tamano === false) {
                throw new RuntimeException('No fue posible consultar el tamaño de la fotografía.');
            }

            if ($tamano <= 0 || $tamano > 2 * 1024 * 1024) {
                $this->erroresValidacion['foto_servicio'] = 'Seleccione una fotografía de hasta 2 MB.';
                return null;
            }

            $contenido = file_get_contents($rutaTemporal);

            if ($contenido === false) {
                throw new RuntimeException('No fue posible leer la fotografía recibida.');
            }

        } elseif (is_string($entrada)) {
            $contenido = $entrada;

        } else {
            $this->erroresValidacion['foto_servicio'] = 'Seleccione una fotografía válida.';
            return null;
        }

        if ($contenido === '' || strlen($contenido) > 2 * 1024 * 1024) {
            $this->erroresValidacion['foto_servicio'] = 'Seleccione una fotografía de hasta 2 MB.';
            return null;
        }

        /* Comprobar el contenido real sin confiar en la extensión ni en el nombre */
        $tipo = (new finfo(FILEINFO_MIME_TYPE))->buffer($contenido);
        $dimensiones = @getimagesizefromstring($contenido);

        if (!in_array($tipo, ['image/jpeg', 'image/png', 'image/webp'], true) ||
            $dimensiones === false || ($dimensiones['mime'] ?? '') !== $tipo ||
            $dimensiones[0] <= 0 || $dimensiones[1] <= 0) {
            $this->erroresValidacion['foto_servicio'] = 'La fotografía debe ser una imagen JPEG, PNG o WebP válida.';
            return null;
        }

        return $contenido;
    }
}