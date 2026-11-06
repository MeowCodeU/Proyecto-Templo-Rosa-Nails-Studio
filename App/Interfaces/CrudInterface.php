<?php

namespace App\Interfaces;

/* ===================================================== */
/* CONTRATO DE OPERACIONES CRUD */
/* ===================================================== */

interface CrudInterface
{
    /* Registrar un nuevo elemento */
    public function registrar();


    /* Modificar un elemento existente */
    public function modificar();


    /* Eliminar o desactivar un elemento según el modelo */
    public function eliminar();


    /* Consultar los elementos registrados */
    public function consultar();
}