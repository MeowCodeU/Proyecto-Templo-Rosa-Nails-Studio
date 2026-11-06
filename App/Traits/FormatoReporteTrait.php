<?php

namespace App\Traits;

use DateTimeImmutable;
use RuntimeException;


/* ===================================================== */
/* FORMATO DE REPORTES */
/* ===================================================== */

trait FormatoReporteTrait
{
    /* ================================================= */
    /* FORMATO DE FECHAS */
    /* ================================================= */

    protected function formatearFecha(?string $fecha): string
    {
        $fecha = trim($fecha ?? '');

        if ($fecha === '') {
            return '—';
        }


        /* Reconocer las fechas con y sin información de hora */
        foreach (['Y-m-d', 'Y-m-d H:i:s'] as $formato) {
            $fechaConvertida = DateTimeImmutable::createFromFormat('!' . $formato, $fecha);

            if (
                $fechaConvertida !== false &&
                $fechaConvertida->format($formato) === $fecha
            ) {
                return $fechaConvertida->format('d/m/Y');
            }
        }

        return '—';
    }


    /* ================================================= */
    /* FORMATO DE MONTOS */
    /* ================================================= */

    protected function formatearMonto($monto): string
    {
        /* Diferenciar los valores vacíos de los importes iguales a cero */
        if ($monto === null || $monto === '' || !is_numeric($monto)) {
            return '—';
        }

        return number_format((float) $monto, 2, ',', '.');
    }


    /* ================================================= */
    /* PREPARACIÓN DEL TEXTO PARA PDF */
    /* ================================================= */

    protected function textoParaPDF(string $texto): string
    {
        /* Normalizar los saltos de línea y las tabulaciones */
        $texto = str_replace(["\r\n", "\r", "\t"], ["\n", "\n", ' '], $texto);


        /* Convertir el texto a la codificación de las fuentes estándar de FPDF */
        $textoConvertido = iconv('UTF-8', 'Windows-1252//TRANSLIT', $texto);

        if ($textoConvertido === false) {
            throw new RuntimeException('No se pudo preparar el texto del reporte PDF.');
        }

        return $textoConvertido;
    }
}