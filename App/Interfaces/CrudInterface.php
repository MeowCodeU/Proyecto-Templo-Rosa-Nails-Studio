<?php

namespace App\Interfaces;

/* ===================================================== */
/* CONTRATO DE OPERACIONES CRUD */
/* ===================================================== */

interface CrudInterface
{
    /* Registrar un nuevo elemento */
    public function registrar(): bool;


    /* Modificar un elemento existente */
    public function modificar(): bool;


    /* Eliminar o desactivar un elemento según el modelo */
    public function eliminar(): bool;


    /* Consultar los elementos registrados */
    public function consultar(): array;
}