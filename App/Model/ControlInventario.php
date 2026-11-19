<?php

namespace App\Model;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;


/* ===================================================== */
/* CONTROL DE INVENTARIO */
/* ===================================================== */

final class ControlInventario
{
    private DateTimeImmutable $fechaActual;
    private DateTimeZone $zonaHoraria;


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    public function __construct(?DateTimeImmutable $fechaReferencia = null)
    {
        $this->zonaHoraria = new DateTimeZone('America/Caracas');

        $fecha = $fechaReferencia
            ? $fechaReferencia->setTimezone($this->zonaHoraria)
            : new DateTimeImmutable('today', $this->zonaHoraria);

        $this->fechaActual = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $fecha->format('Y-m-d'),
            $this->zonaHoraria
        );
    }


    /* ================================================= */
    /* CONTROL DE STOCK */
    /* ================================================= */

    public function controlStock(int $stockActual, int $stockMinimo): array
    {
        if ($stockActual < 0 || $stockMinimo < 0) {
            throw new InvalidArgumentException('Las existencias y el mínimo no pueden ser negativos.');
        }

        if ($stockActual === 0) {
            return ['estado' => 'AGOTADO', 'etiqueta' => 'Agotado'];
        }

        if ($stockActual <= $stockMinimo) {
            return ['estado' => 'BAJO', 'etiqueta' => 'Stock bajo'];
        }

        return ['estado' => 'NORMAL', 'etiqueta' => 'Normal'];
    }


    /* ================================================= */
    /* CONTROL DE VENCIMIENTO */
    /* ================================================= */

    public function controlVencimiento(?string $fechaVencimiento): array
    {
        $fechaVencimiento = trim($fechaVencimiento ?? '');

        if ($fechaVencimiento === '') {
            return [
                'estado' => 'SIN_FECHA',
                'etiqueta' => 'Sin fecha',
                'dias_restantes' => null
            ];
        }

        $fecha = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $fechaVencimiento,
            $this->zonaHoraria
        );

        if (!$fecha || $fecha->format('Y-m-d') !== $fechaVencimiento) {
            return [
                'estado' => 'FECHA_INVALIDA',
                'etiqueta' => 'Fecha inválida',
                'dias_restantes' => null
            ];
        }

        $dias = (int) $this->fechaActual->diff($fecha)->format('%r%a');

        if ($dias < 0) {
            $estado = 'VENCIDO';
            $etiqueta = 'Vencido';
        } elseif ($dias <= 7) {
            $estado = 'URGENTE';
            $etiqueta = $dias === 0 ? 'Vence hoy' : 'Urgente';
        } elseif ($dias <= 30) {
            $estado = 'PROXIMO';
            $etiqueta = 'Próximo a vencer';
        } else {
            $estado = 'VIGENTE';
            $etiqueta = 'Vigente';
        }

        return [
            'estado' => $estado,
            'etiqueta' => $etiqueta,
            'dias_restantes' => $dias
        ];
    }
}
