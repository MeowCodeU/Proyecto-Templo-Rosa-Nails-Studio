<?php

namespace App\Traits;

use DateTimeImmutable;
use InvalidArgumentException;


/* ===================================================== */
/* VALIDACIONES DE LOS FILTROS DE REPORTES */
/* ===================================================== */

trait ValidacionReporteTrait
{
    use ValidacionTrait;


    /* ================================================= */
    /* VALIDAR LA ESTRUCTURA Y EL PERÍODO */
    /* ================================================= */

    protected function validarEstructuraReporte(mixed $entrada): array
    {
        $this->erroresValidacion = [];

        if (!is_array($entrada)) {
            $this->erroresValidacion['filtros'] = 'La solicitud del reporte no contiene filtros válidos.';
            throw new InvalidArgumentException($this->erroresValidacion['filtros']);
        }

        $tipo = $entrada['reporte'] ?? '';

        if (!is_string($tipo) || !isset(self::CATALOGO[$tipo])) {
            $this->erroresValidacion['reporte'] = 'Seleccione un tipo de reporte válido.';
            throw new InvalidArgumentException($this->erroresValidacion['reporte']);
        }

        $definicion = self::CATALOGO[$tipo];

        if (($definicion['disponible'] ?? true) === false) {
            $this->erroresValidacion['reporte'] = $definicion['descripcion'];
            throw new InvalidArgumentException($this->erroresValidacion['reporte']);
        }

        $permitidos = array_merge(['reporte'], $definicion['filtros']);

        if ($definicion['fechas']) {
            $permitidos = array_merge($permitidos, ['fecha_inicio', 'fecha_fin']);
        }

        /* Aceptar únicamente los campos definidos para el reporte */
        foreach ($entrada as $campo => $valor) {
            if (!in_array($campo, $permitidos, true)) {
                $this->erroresValidacion['filtros'] = 'El reporte recibió un filtro que no corresponde.';
                continue;
            }

            if (!is_string($valor)) {
                $this->erroresValidacion[$campo] = 'Los filtros deben contener valores simples.';
            }
        }

        $filtros = ['reporte' => $tipo];

        if ($definicion['fechas']) {
            foreach (['fecha_inicio', 'fecha_fin'] as $campo) {
                if (isset($this->erroresValidacion[$campo])) continue;

                $valor = $entrada[$campo] ?? '';

                if (
                    !is_string($valor) ||
                    preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $valor) !== 1
                ) {
                    $this->erroresValidacion[$campo] = 'Indique las dos fechas del período correctamente.';
                    continue;
                }

                $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);

                if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
                    $this->erroresValidacion[$campo] = 'Indique las dos fechas del período correctamente.';
                    continue;
                }

                $filtros[$campo] = $valor;
            }

            if (
                isset($filtros['fecha_inicio'], $filtros['fecha_fin']) &&
                $filtros['fecha_inicio'] > $filtros['fecha_fin']
            ) {
                $this->erroresValidacion['fecha_inicio'] = 'La fecha inicial no puede ser posterior a la fecha final.';
            }
        }

        /* Comprobar los filtros obligatorios antes de consultar sus opciones */
        foreach ($definicion['filtros'] as $campo) {
            if (isset($this->erroresValidacion[$campo])) continue;

            $valor = $entrada[$campo] ?? '';

            if ($valor === '') {
                if (in_array($campo, $definicion['requeridos'] ?? [], true)) {
                    $this->erroresValidacion[$campo] = $campo === 'atencion'
                        ? 'Seleccione la atención realizada que desea consultar.'
                        : 'Seleccione el cliente cuyo historial desea consultar.';
                }

                continue;
            }

            $filtros[$campo] = $valor;
        }

        if ($this->erroresValidacion !== []) {
            throw new InvalidArgumentException(
                implode(' ', array_unique($this->erroresValidacion))
            );
        }

        return $filtros;
    }


    /* ================================================= */
    /* VALIDAR LAS OPCIONES SELECCIONADAS */
    /* ================================================= */

    protected function validarFiltrosReporte(mixed $entrada, array $opciones): array
    {
        $filtros = $this->validarEstructuraReporte($entrada);
        $definicion = self::CATALOGO[$filtros['reporte']];

        foreach ($definicion['filtros'] as $campo) {
            if (!isset($filtros[$campo])) continue;

            $ids = array_map('strval', array_column($opciones[$campo] ?? [], 'id'));

            if (!in_array($filtros[$campo], $ids, true)) {
                $this->erroresValidacion[$campo] = 'Uno de los filtros seleccionados ya no está disponible. Actualice la página.';
            }
        }

        if ($this->erroresValidacion !== []) {
            throw new InvalidArgumentException(
                implode(' ', array_unique($this->erroresValidacion))
            );
        }

        return $filtros;
    }
}