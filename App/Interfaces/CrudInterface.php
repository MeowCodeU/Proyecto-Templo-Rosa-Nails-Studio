<?php

namespace App\Interfaces;

interface CrudInterface
{
    public function registrar();

    public function modificar();

    public function eliminar();

    public function consultar();
}