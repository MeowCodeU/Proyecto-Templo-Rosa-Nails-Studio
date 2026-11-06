<?php

namespace App\Reportes;


/* ===================================================== */
/* GENERACIÓN DEL REPORTE PDF */
/* ===================================================== */

class ReportePDF extends DocumentoReporte
{
    /* Dimensiones utilizadas para distribuir la tabla */
    private array $anchos = [];
    private float $inicioPagina = 0;
    private float $altoCabecera = 0;

    /* Medidas para las líneas y los saltos de página */
    private const ALTO_LINEA = 4.2;
    private const LIMITE_INFERIOR = 190;


    /* ================================================= */
    /* CONTENIDO DEL REPORTE */
    /* ================================================= */

    protected function dibujarContenido(): void
    {
        $this->inicioPagina = $this->GetY();


        /* Distribuir el ancho disponible según el peso de cada columna */
        $pesoTotal = array_sum(array_column($this->reporte['columnas'], 'ancho'));

        foreach ($this->reporte['columnas'] as $columna) {
            $this->anchos[] = 269 * $columna['ancho'] / $pesoTotal;
        }


        /* Mostrar los filtros aplicados */
        $this->SetFont('Helvetica', '', 9);

        foreach ($this->reporte['filtros_aplicados'] as $filtro) {
            $this->MultiCell(269, 5, $this->textoParaPDF($filtro));
        }


        /* Mostrar la nota correspondiente al reporte */
        $this->Ln(2);
        $this->SetFont('Helvetica', 'I', 8);

        $this->MultiCell(269, 4.5, $this->textoParaPDF($this->reporte['nota']));


        /* Mostrar los indicadores del resumen */
        $this->Ln(3);
        $this->SetFont('Helvetica', 'B', 9);

        foreach ($this->reporte['resumen'] as $indicador) {
            $this->MultiCell(
                269,
                5,
                $this->textoParaPDF($indicador['titulo'] . ': ' . $indicador['valor'])
            );
        }


        /* Preparar la cabecera de la tabla */
        $this->Ln(3);
        $this->cabeceraTabla();


        /* Ajustar los valores de cada fila al ancho de sus columnas */
        foreach ($this->reporte['filas'] as $indice => $fila) {
            $lineas = [];

            $this->SetFont('Helvetica', '', 8);

            foreach ($this->reporte['columnas'] as $n => $columna) {
                $valor = $this->formatearValor(
                    $fila[$columna['clave']] ?? null,
                    $columna['formato']
                );

                $lineas[] = $this->envolverTexto(
                    $this->textoParaPDF($valor),
                    $this->anchos[$n] - 3
                );
            }

            $this->filaTabla($lineas, $indice % 2 === 0);
        }
    }


    /* ================================================= */
    /* FORMATO DE LOS VALORES */
    /* ================================================= */

    private function formatearValor($valor, string $formato): string
    {
        /* Diferenciar los valores vacíos del número cero */
        if ($valor === null || $valor === '') {
            return '—';
        }


        /* Aplicar el formato correspondiente a cada columna */
        if ($formato === 'decimal') {
            return $this->formatearMonto($valor);
        }

        if ($formato === 'entero') {
            return is_numeric($valor)
                ? number_format((float) $valor, 0, ',', '.')
                : '—';
        }

        if ($formato === 'fecha') {
            return $this->formatearFecha((string) $valor);
        }

        if ($formato === 'hora') {
            return substr((string) $valor, 0, 5);
        }

        return (string) $valor;
    }


    /* ================================================= */
    /* AJUSTE DEL TEXTO A LAS CELDAS */
    /* ================================================= */

    private function envolverTexto(string $texto, float $ancho): array
    {
        $resultado = [];


        /* Procesar cada párrafo conservando sus saltos de línea */
        foreach (explode("\n", $texto) as $parrafo) {
            $linea = '';
            $ultimoEspacio = -1;
            $longitud = strlen($parrafo);

            for ($i = 0; $i < $longitud; $i++) {
                $linea .= $parrafo[$i];

                if ($parrafo[$i] === ' ') {
                    $ultimoEspacio = strlen($linea) - 1;
                }


                /* Dividir la línea cuando supere el ancho disponible */
                if ($this->GetStringWidth($linea) > $ancho && strlen($linea) > 1) {
                    if ($ultimoEspacio > 0) {
                        $resultado[] = substr($linea, 0, $ultimoEspacio);
                        $linea = substr($linea, $ultimoEspacio + 1);

                    } else {
                        $resultado[] = substr($linea, 0, -1);
                        $linea = substr($linea, -1);
                    }

                    $ultimoEspacio = -1;
                }
            }

            $resultado[] = $linea;
        }

        return $resultado ?: [''];
    }


    /* ================================================= */
    /* CABECERA DE LA TABLA */
    /* ================================================= */

    private function cabeceraTabla(): void
    {
        $this->SetFont('Helvetica', 'B', 8);

        $lineas = [];


        /* Ajustar los títulos al ancho de sus columnas */
        foreach ($this->reporte['columnas'] as $n => $columna) {
            $lineas[] = $this->envolverTexto(
                $this->textoParaPDF($columna['titulo']),
                $this->anchos[$n] - 3
            );
        }

        $cantidad = max(array_map('count', $lineas));

        $this->altoCabecera = $cantidad * self::ALTO_LINEA + 3;


        /* Crear una página cuando la cabecera no quepa */
        if ($this->GetY() + $this->altoCabecera > self::LIMITE_INFERIOR) {
            $this->AddPage();
        }


        /* Dibujar la cabecera con los colores del sistema */
        $this->SetFillColor(232, 206, 216);
        $this->SetTextColor(74, 60, 49);

        $this->dibujarFragmento($lineas, $cantidad);

        $this->SetFont('Helvetica', '', 8);
    }


    /* ================================================= */
    /* DISTRIBUCIÓN DE LAS FILAS */
    /* ================================================= */

    private function filaTabla(array $lineas, bool $sombreada): void
    {
        $pendientes = max(array_map('count', $lineas));
        $altoTotal = $pendientes * self::ALTO_LINEA + 3;

        $espacioPaginaNueva = self::LIMITE_INFERIOR - $this->inicioPagina - $this->altoCabecera;


        /* Mantener la fila completa cuando pueda caber en una página nueva */
        if (
            $this->GetY() + $altoTotal > self::LIMITE_INFERIOR &&
            $altoTotal <= $espacioPaginaNueva
        ) {
            $this->AddPage();
            $this->cabeceraTabla();
        }

        $offset = 0;


        /* Continuar las filas extensas en otras páginas sin perder texto */
        while ($pendientes > 0) {
            $disponibles = (int) floor(
                (self::LIMITE_INFERIOR - $this->GetY() - 3) / self::ALTO_LINEA
            );

            if ($disponibles < 1) {
                $this->AddPage();
                $this->cabeceraTabla();

                continue;
            }

            $cantidad = min($disponibles, $pendientes);


            /* Obtener las líneas que caben en el fragmento actual */
            $fragmento = array_map(
                static function ($celda) use ($offset, $cantidad) {
                    return array_slice($celda, $offset, $cantidad);
                },
                $lineas
            );


            /* Alternar el fondo de las filas */
            $this->SetFillColor(
                $sombreada ? 253 : 255,
                $sombreada ? 245 : 255,
                $sombreada ? 247 : 255
            );

            $this->dibujarFragmento($fragmento, $cantidad);

            $offset += $cantidad;
            $pendientes -= $cantidad;
        }
    }


    /* ================================================= */
    /* DIBUJO DE LAS CELDAS */
    /* ================================================= */

    private function dibujarFragmento(array $lineas, int $cantidad): void
    {
        $x = 14;
        $y = $this->GetY();

        $alto = $cantidad * self::ALTO_LINEA + 3;

        $this->SetDrawColor(238, 224, 227);


        /* Dibujar todas las celdas del fragmento con la misma altura */
        foreach ($this->anchos as $n => $ancho) {
            $this->Rect($x, $y, $ancho, $alto, 'DF');

            $this->SetXY($x + 1.5, $y + 1.5);

            foreach ($lineas[$n] as $linea) {
                $this->Cell($ancho - 3, self::ALTO_LINEA, $linea, 0, 2);
            }

            $x += $ancho;
        }


        /* Situar el cursor al inicio de la siguiente fila */
        $this->SetXY(14, $y + $alto);
    }
}