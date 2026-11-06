<?php

namespace App\Interfaces;


/* ===================================================== */
/* CONTRATO DE EXPORTACIÓN DE REPORTES */
/* ===================================================== */

interface ExportadorReporteInterface
{
    /* Generar el documento y devolver su contenido antes de enviarlo al navegador */
    public function generar(array $reporte): string;
}