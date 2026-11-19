<?php

namespace App\Interfaces;


/* ===================================================== */
/* CONTRATO DE DISPONIBILIDAD DE HORARIOS */
/* ===================================================== */

interface DisponibilidadInterface
{
    /* Comprobar si el horario coincide con otra ocupación */
    public function existeConflictoHorario(
        int $idEspecialista,
        string $fecha,
        string $horaInicio,
        string $horaFin,
        ?int $idExcluir = null
    ): bool;
}