<?php

namespace App\Model;

use DateTimeImmutable;


/* ===================================================== */
/* CONTROL DE TRANSICIONES DE ESTADOS DE CITAS */
/* ===================================================== */

final class FlujoCita
{
    /* ================================================= */
    /* ESTADOS PREVIOS A LA ATENCIÓN */
    /* ================================================= */

    private const ESTADOS_PREVIOS = [
        'AGENDADA',
        'CONFIRMADA',
        'EN ESPERA',
        'DEMORADA'
    ];


    /* ================================================= */
    /* TRANSICIONES PERMITIDAS */
    /* ================================================= */

    private const TRANSICIONES = [
        'AGENDADA' => ['CONFIRMADA', 'EN ESPERA', 'DEMORADA', 'EN ATENCIÓN', 'CANCELADA'],
        'CONFIRMADA' => ['EN ESPERA', 'DEMORADA', 'EN ATENCIÓN', 'CANCELADA'],
        'EN ESPERA' => ['DEMORADA', 'EN ATENCIÓN', 'CANCELADA'],
        'DEMORADA' => ['EN ATENCIÓN', 'CANCELADA'],
        'EN ATENCIÓN' => ['REALIZADA'],
        'REALIZADA' => [],
        'CANCELADA' => []
    ];


    /* ================================================= */
    /* ESTADOS DISPONIBLES EN AGENDAMIENTO */
    /* ================================================= */

    /* Obtener los estados anteriores al inicio de la atención */
    public function estadosPrevios(): array
    {
        return self::ESTADOS_PREVIOS;
    }


    /* Comprobar si una cita puede gestionarse desde Agendamiento */
    public function esGestionable(string $estado): bool
    {
        return in_array(
            $this->normalizarEstado($estado),
            self::ESTADOS_PREVIOS,
            true
        );
    }


    /* ================================================= */
    /* COMPROBAR TRANSICIONES DE ESTADOS */
    /* ================================================= */

    /* Permitir únicamente los cambios correspondientes al flujo de la cita */
    public function permiteTransicion(string $estadoActual, string $estadoNuevo): bool
    {
        $actual = $this->normalizarEstado($estadoActual);
        $nuevo = $this->normalizarEstado($estadoNuevo);

        /* Conservar el estado al editar datos o guardar una atención en curso */
        if ($actual === $nuevo) {
            return $this->esGestionable($actual) ||
                in_array($actual, ['EN ATENCIÓN', 'REALIZADA'], true);
        }

        return in_array(
            $nuevo,
            self::TRANSICIONES[$actual] ?? [],
            true
        );
    }


    /* ================================================= */
    /* COMPROBAR DEMORA DE LA CITA */
    /* ================================================= */

    /* Detectar retrasos cuando la atención todavía no ha comenzado */
    public function debeMarcarDemorada(
        string $estadoActual,
        DateTimeImmutable $fechaHoraProgramada,
        ?DateTimeImmutable $ahora = null
    ): bool {
        $estado = $this->normalizarEstado($estadoActual);

        if (!in_array($estado, ['AGENDADA', 'CONFIRMADA', 'EN ESPERA'], true)) {
            return false;
        }

        $ahora = $ahora ?? new DateTimeImmutable();

        return $ahora >= $fechaHoraProgramada;
    }


    /* ================================================= */
    /* NORMALIZAR EL ESTADO RECIBIDO */
    /* ================================================= */

    private function normalizarEstado(string $estado): string
    {
        $estado = strtoupper(trim($estado));

        return $estado === 'EN ATENCION' ? 'EN ATENCIÓN' : $estado;
    }
}