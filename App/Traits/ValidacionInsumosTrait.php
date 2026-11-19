<?php

namespace App\Traits;

/* ===================================================== */
/* VALIDACIONES DE INSUMOS */
/* ===================================================== */

trait ValidacionInsumosTrait
{
    use ValidacionTrait;


    /* ================================================= */
    /* VALIDAR EL IDENTIFICADOR DEL INSUMO */
    /* ================================================= */

    public function validarIdInsumo(mixed $entrada): ?int
    {
        $this->erroresValidacion = [];

        return $this->validarIdentificador($entrada, 'id_insumo', 'Seleccione un insumo válido.');
    }


    /* ================================================= */
    /* VALIDAR FILTROS DEL HISTORIAL */
    /* ================================================= */

    public function validarFiltrosHistorial(array $datos): array
    {
        $this->erroresValidacion = [];
        $desde = $this->fechaInsumos($datos['fecha_desde'] ?? '', 'fecha_desde');
        $hasta = $this->fechaInsumos($datos['fecha_hasta'] ?? '', 'fecha_hasta');
        $tipo = $datos['tipo_movimiento'] ?? '';

        if ($desde !== null && $hasta !== null && $desde > $hasta) {
            $this->erroresValidacion['fecha_hasta'] = 'La fecha final no puede ser anterior a la fecha inicial.';
        }

        if (!is_string($tipo) || !in_array($tipo, ['', 'INCREMENTO', 'DECREMENTO'], true)) {
            $this->erroresValidacion['tipo_movimiento'] = 'Seleccione un tipo de movimiento válido.';
        }

        return [
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'tipo_movimiento' => is_string($tipo) && $tipo !== '' ? $tipo : null
        ];
    }


    /* ================================================= */
    /* VALIDAR LOS DATOS RECIBIDOS */
    /* ================================================= */

    public function validarSolicitudInsumos(array $datos, string $accion): array
    {
        $this->erroresValidacion = [];
        $validos = $datos;

        if ($accion !== 'registrar') {
            $validos['id_insumo'] = $this->validarIdentificador($datos['id_insumo'] ?? null, 'id_insumo', 'Seleccione un insumo válido.');
        }

        if (in_array($accion, ['registrar', 'modificar'], true)) {
            $textos = ['nombre_insumo' => 100, 'descripcion' => 255, 'presentacion' => 80];

            foreach ($textos as $campo => $limite) {
                $valor = $datos[$campo] ?? '';

                if (!is_string($valor)) {
                    $this->erroresValidacion[$campo] = $campo === 'nombre_insumo'
                        ? 'El nombre del insumo es obligatorio.'
                        : 'El valor del campo no es válido.';
                    continue;
                }

                $valor = trim($valor);

                if ($campo === 'nombre_insumo' && $valor === '') {
                    $this->erroresValidacion[$campo] = 'El nombre del insumo es obligatorio.';
                } elseif ($this->longitud($valor) > $limite) {
                    $this->erroresValidacion[$campo] = "El campo debe tener como máximo $limite caracteres.";
                }

                $validos[$campo] = $valor;
            }

            $tipo = $datos['tipo_control'] ?? 'UNITARIO';

            if (!is_string($tipo)) {
                $this->erroresValidacion['tipo_control'] = 'El valor del campo no es válido.';
                $tipo = 'UNITARIO';
            }

            $tipo = strtoupper(trim($tipo));

            $validos['tipo_control'] = in_array($tipo, ['UNITARIO', 'POR_ENVASE'], true) ? $tipo : 'UNITARIO';
            $validos['stock_minimo'] = $this->enteroInsumos($datos['stock_minimo'] ?? 0, 0, 'stock_minimo', 'Indique una cantidad entera no negativa válida.');

            if ($accion === 'modificar') {
                $validos['stock_actual'] = $this->enteroInsumos($datos['stock_actual'] ?? 0, 0, 'stock_actual', 'Indique una cantidad entera no negativa válida.');
                $validos['stock_original'] = $this->enteroInsumos($datos['stock_original'] ?? null, 0, 'stock_original', 'Actualice la lista y vuelva a abrir el insumo antes de modificarlo.');
            } else {
                $validos['stock_actual'] = 0;
            }
        }

        if ($accion === 'gestionar_existencias') {
            $operacion = $datos['tipo_movimiento'] ?? '';

            $validos['tipo_movimiento_bd'] = null;
            $validos['motivo_movimiento'] = null;
            $validos['id_proveedor'] = null;
            $validos['id_lote'] = null;
            $validos['fecha_vencimiento'] = null;

            if (!is_string($operacion) || !in_array($operacion, ['AGREGAR', 'REPONER', 'DESCONTAR'], true)) {
                $this->erroresValidacion['tipo_movimiento'] = 'Seleccione Agregar, Reponer o Descontar existencias.';
            } else {
                $validos['tipo_movimiento_bd'] = $operacion === 'DESCONTAR' ? 'DECREMENTO' : 'INCREMENTO';
            }

            $validos['cantidad_movimiento'] = $this->enteroInsumos($datos['cantidad_movimiento'] ?? null, 1, 'cantidad_movimiento', 'Indique una cantidad entera positiva válida.');

            if ($validos['tipo_movimiento_bd'] === 'INCREMENTO') {
                $validos['motivo_movimiento'] = $operacion === 'AGREGAR' ? 'Ingreso inicial' : 'Reposición';
                $proveedor = $datos['id_proveedor'] ?? '';

                if ($proveedor !== '') {
                    $validos['id_proveedor'] = $this->validarIdentificador($proveedor, 'id_proveedor', 'Seleccione un proveedor válido.');
                }

                $validos['fecha_vencimiento'] = $this->fechaInsumos($datos['fecha_vencimiento'] ?? '', 'fecha_vencimiento');
            }

            if ($validos['tipo_movimiento_bd'] === 'DECREMENTO') {
                $motivo = $datos['motivo'] ?? '';
                $permitidos = ['Rotura', 'Derrame', 'Deterioro', 'Daño', 'Extravío', 'Otro'];

                if (!is_string($motivo) || !in_array($motivo, $permitidos, true)) {
                    $this->erroresValidacion['motivo'] = 'Seleccione el motivo del descuento.';

                } elseif ($motivo === 'Otro') {
                    $detalle = $datos['detalle_motivo'] ?? '';

                    if (!is_string($detalle) || trim($detalle) === '' || strlen(trim($detalle)) > 249) {
                        $this->erroresValidacion['detalle_motivo'] = 'Especifique el motivo en un máximo de 249 caracteres.';
                    } else {
                        $validos['motivo_movimiento'] = 'Otro: ' . trim($detalle);
                    }

                } else {
                    $validos['motivo_movimiento'] = $motivo;
                }

                $lote = $datos['id_lote'] ?? '';

                if ($lote !== '') {
                    $validos['id_lote'] = $this->validarIdentificador($lote, 'id_lote', 'Seleccione un lote válido.');
                }
            }
        }

        return $validos;
    }


    /* ================================================= */
    /* VALIDAR LOS ATRIBUTOS ANTES DE GUARDAR */
    /* ================================================= */

    protected function validarCatalogoInsumo(bool $esModificacion): bool
    {
        $datos = $this->validarSolicitudInsumos([
            'id_insumo' => $this->idInsumo,
            'nombre_insumo' => $this->nombreInsumo,
            'descripcion' => $this->descripcion,
            'presentacion' => $this->presentacion,
            'tipo_control' => $this->tipoControl,
            'stock_actual' => $this->stockActual,
            'stock_original' => $this->stockOriginal,
            'stock_minimo' => $this->stockMinimo
        ], $esModificacion ? 'modificar' : 'registrar');

        if (!$esModificacion) {
            $stock = $this->enteroInsumos($this->stockActual ?? 0, 0, 'stock_actual', 'Indique una cantidad entera no negativa válida.');

            if ($stock !== null && $stock !== 0) {
                $this->erroresValidacion['stock_actual'] = 'El insumo se registra con cero existencias. Use Gestionar Existencias para ingresar unidades.';
            }
        }

        if ($this->erroresValidacion) return false;

        $this->nombreInsumo = $datos['nombre_insumo'];
        $this->descripcion = $datos['descripcion'];
        $this->presentacion = $datos['presentacion'];
        $this->tipoControl = $datos['tipo_control'];
        $this->stockActual = $datos['stock_actual'];
        $this->stockMinimo = $datos['stock_minimo'];

        if ($esModificacion) {
            $this->idInsumo = $datos['id_insumo'];
            $this->stockOriginal = $datos['stock_original'];
        }

        return true;
    }


    /* ================================================= */
    /* VALIDAR UN MOVIMIENTO ANTES DE LA TRANSACCIÓN */
    /* ================================================= */

    protected function validarMovimientoInsumo(string $tipo, int $cantidad, string $motivo, int $usuario, ?int $proveedor, ?string $fecha, ?int $lote): array
    {
        $this->erroresValidacion = [];

        $id = $this->validarIdentificador($this->idInsumo, 'id_insumo', 'Seleccione un insumo válido.');
        $this->enteroInsumos($cantidad, 1, 'cantidad_movimiento', 'Indique una cantidad entera positiva válida.');
        $this->validarIdentificador($usuario, 'id_usuario', 'El usuario responsable no es válido.');

        if (!in_array($tipo, ['INCREMENTO', 'DECREMENTO'], true)) {
            $this->erroresValidacion['tipo_movimiento'] = 'Seleccione un tipo de movimiento válido.';
        }

        if ($proveedor !== null) $this->validarIdentificador($proveedor, 'id_proveedor', 'Seleccione un proveedor válido.');
        if ($lote !== null) $this->validarIdentificador($lote, 'id_lote', 'Seleccione un lote válido.');

        if ($tipo === 'DECREMENTO' && $proveedor !== null) {
            $this->erroresValidacion['id_proveedor'] = 'Los descuentos no llevan proveedor.';
        }

        if ($tipo === 'INCREMENTO' && $lote !== null) {
            $this->erroresValidacion['id_lote'] = 'Cada entrada debe crear un lote nuevo.';
        }

        if ($tipo === 'DECREMENTO' && trim($fecha ?? '') !== '') {
            $this->erroresValidacion['fecha_vencimiento'] = 'La fecha de vencimiento corresponde a las entradas de insumos.';
        }

        $motivo = trim($motivo);

        if ($motivo === '' || strlen($motivo) > 255) {
            $this->erroresValidacion['motivo'] = 'Indique un motivo válido para el movimiento.';
        } elseif ($tipo === 'INCREMENTO' && !in_array($motivo, ['Ingreso inicial', 'Reposición'], true)) {
            $this->erroresValidacion['motivo'] = 'Seleccione Agregar o Reponer existencias.';
        }

        if ($tipo === 'INCREMENTO') $fecha = $this->fechaInsumos(trim($fecha ?? ''), 'fecha_vencimiento');

        if ($this->erroresValidacion) {
            throw new \InvalidArgumentException(implode(' ', $this->erroresValidacion));
        }

        return ['id_insumo' => $id, 'motivo' => $motivo, 'fecha_vencimiento' => $fecha];
    }


    /* ================================================= */
    /* COMPROBAR CANTIDADES Y FECHAS */
    /* ================================================= */

    private function enteroInsumos(mixed $valor, int $minimo, string $campo, string $mensaje): ?int
    {
        if ((!is_string($valor) && !is_int($valor)) || !ctype_digit((string) $valor) || (float) $valor < $minimo || (float) $valor > 4294967295) {
            $this->erroresValidacion[$campo] = $mensaje;
            return null;
        }

        return (int) $valor;
    }


    private function fechaInsumos(mixed $valor, string $campo): ?string
    {
        if ($valor === '') return null;

        if (is_string($valor) && preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $valor)) {
            $fecha = \DateTimeImmutable::createFromFormat('!Y-m-d', $valor);

            if ($fecha && $fecha->format('Y-m-d') === $valor) return $valor;
        }

        $this->erroresValidacion[$campo] = 'Seleccione una fecha válida.';

        return null;
    }
}