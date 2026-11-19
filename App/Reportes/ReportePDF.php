<?php

namespace App\Reportes;


/* ===================================================== */
/* GENERACIÓN DEL REPORTE PDF */
/* ===================================================== */

class ReportePDF extends DocumentoReporte
{
    /* Dimensiones utilizadas para distribuir la tabla */
    private array $anchos = [];
    private float $altoCabecera = 0;

    /* Medidas para las líneas y los saltos de página */
    private const ALTO_LINEA = 4.2;
    private const INICIO_TABLA = 58.5;
    private const LIMITE_INFERIOR = 198;


    /* ================================================= */
    /* CONTENIDO DEL REPORTE */
    /* ================================================= */

    protected function dibujarContenido(): void
    {
        $columnas = $this->reporte['columnas'] ?? [];
        $this->anchos = [];

        if (empty($columnas)) {
            return;
        }


        /* Distribuir el ancho de la hoja carta según el peso de cada columna */
        $pesoTotal = array_sum(array_column($columnas, 'ancho'));

        if ($pesoTotal <= 0) {
            throw new \InvalidArgumentException('Los anchos de las columnas del reporte son inválidos.');
        }

        foreach ($columnas as $columna) {
            $this->anchos[] = self::ANCHO_UTIL * $columna['ancho'] / $pesoTotal;
        }


        /* Mostrar los indicadores antes de la tabla */
        $this->mostrarResumen();


        /* Preparar la cabecera de la tabla */
        $this->cabeceraTabla();


        /* Ajustar los valores de cada fila al ancho de sus columnas */
        foreach (($this->reporte['filas'] ?? []) as $indice => $fila) {
            $lineas = [];

            $this->SetFont('Helvetica', '', 8);

            foreach ($columnas as $n => $columna) {
                $valor = $this->formatearValor(
                    $fila[$columna['clave']] ?? null,
                    $columna['formato'] ?? 'texto'
                );

                $lineas[] = $this->envolverTexto(
                    $this->textoParaPDF($valor),
                    $this->anchos[$n] - 3
                );
            }

            $this->filaTabla($lineas, $indice % 2 === 0);
        }


        /* Conservar los filtros y las notas después de la tabla */
        $this->mostrarNotas();
    }


    /* ================================================= */
    /* INDICADORES DEL REPORTE */
    /* ================================================= */

    private function mostrarResumen(): void
    {
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetTextColor(74, 60, 49);

        $x = self::MARGEN;
        $y = 48.4;


        /* Distribuir los indicadores sin superar el margen derecho */
        foreach (($this->reporte['resumen'] ?? []) as $indicador) {
            $texto = $this->textoParaPDF($indicador['titulo'] . ': ' . $indicador['valor']);
            $ancho = $this->GetStringWidth($texto) + 8;

            if ($x > self::MARGEN && $x + $ancho > self::PAPEL_ANCHO - self::MARGEN) {
                $x = self::MARGEN;
                $y += 5.1;
            }

            $this->SetXY($x, $y);
            $this->Cell($ancho, 5.1, $texto);

            $x += $ancho;
        }

        $this->SetY(max(self::INICIO_TABLA, $y + 10.1));
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


        /* Crear otra página cuando la cabecera no quepa */
        if ($this->GetY() + $this->altoCabecera > self::LIMITE_INFERIOR) {
            $this->AddPage();
            $this->SetY(self::INICIO_TABLA);
        }


        /* Dibujar la cabecera rosa con los bordes dorados */
        $this->SetFont('Helvetica', 'B', 8);
        $this->SetFillColor(...self::ROSA);
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
        $espacioPaginaNueva = self::LIMITE_INFERIOR - self::INICIO_TABLA - $this->altoCabecera;


        /* Mantener la fila completa cuando pueda caber en una página nueva */
        if (
            $this->GetY() + $altoTotal > self::LIMITE_INFERIOR &&
            $altoTotal <= $espacioPaginaNueva
        ) {
            $this->AddPage();
            $this->SetY(self::INICIO_TABLA);
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
                $this->SetY(self::INICIO_TABLA);
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


            /* Alternar el fondo suave de las filas */
            $this->SetFillColor(
                $sombreada ? 252 : 255,
                $sombreada ? 244 : 249,
                $sombreada ? 247 : 251
            );

            $this->SetFont('Helvetica', '', 8);
            $this->SetTextColor(74, 60, 49);

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
        $x = self::MARGEN;
        $y = $this->GetY();
        $alto = $cantidad * self::ALTO_LINEA + 3;


        /* Utilizar el dorado aprobado en todos los bordes */
        $this->SetDrawColor(...self::ORO);
        $this->SetLineWidth(0.20);


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
        $this->SetXY(self::MARGEN, $y + $alto);
    }


    /* ================================================= */
    /* FILTROS Y NOTAS DEL REPORTE */
    /* ================================================= */

    private function mostrarNotas(): void
    {
        $textos = [];


        /* Conservar los filtros sin repetir el título del reporte */
        foreach (($this->reporte['filtros_aplicados'] ?? []) as $filtro) {
            if (str_starts_with((string) $filtro, 'Tipo de reporte: ')) {
                continue;
            }

            $textos[] = (string) $filtro;
        }

        if (!empty($this->reporte['nota'])) {
            $textos[] = (string) $this->reporte['nota'];
        }


        /* Mostrar la información debajo de la tabla */
        foreach ($textos as $texto) {
            $this->SetFont('Helvetica', '', 7);
            $this->SetTextColor(122, 101, 91);

            $lineas = $this->envolverTexto(
                $this->textoParaPDF($texto),
                self::ANCHO_UTIL
            );

            $this->Ln(1);

            foreach ($lineas as $linea) {
                if ($this->GetY() + 3.7 > self::LIMITE_INFERIOR) {
                    $this->AddPage();
                    $this->SetY(self::INICIO_TABLA);
                    $this->SetFont('Helvetica', '', 7);
                    $this->SetTextColor(122, 101, 91);
                }

                $this->SetX(self::MARGEN);
                $this->Cell(self::ANCHO_UTIL, 3.7, $linea, 0, 1);
            }
        }
    }
}
