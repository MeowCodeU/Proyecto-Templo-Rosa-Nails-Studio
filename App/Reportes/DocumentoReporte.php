<?php

namespace App\Reportes;

use App\Interfaces\ExportadorReporteInterface;
use App\Traits\FormatoReporteTrait;
use FPDF;
use RuntimeException;


/* Cargar la biblioteca de generación de documentos */
require_once __DIR__ . '/../../fpdf/fpdf.php';


/* ===================================================== */
/* ESTRUCTURA DE LOS REPORTES PDF */
/* ===================================================== */

abstract class DocumentoReporte extends FPDF implements ExportadorReporteInterface
{
    /* Formato compartido por los reportes */
    use FormatoReporteTrait;

    /* Información utilizada para construir el documento */
    protected array $reporte = [];

    /* Dimensiones y colores del diseño aprobado */
    protected const PAPEL_ANCHO = 279.4;
    protected const PAPEL_ALTO = 215.9;
    protected const MARGEN = 14;
    protected const ANCHO_UTIL = 251.4;
    protected const ORO = [203, 187, 148];
    protected const ROSA = [232, 198, 210];


    /* ================================================= */
    /* CONFIGURACIÓN DEL DOCUMENTO */
    /* ================================================= */

    public function __construct()
    {
        /* Configurar una página carta horizontal y sus márgenes */
        parent::__construct('L', 'mm', 'Letter');

        $this->SetMargins(self::MARGEN, self::MARGEN, self::MARGEN);
        $this->cMargin = 0;


        /* Permitir que la clase concreta controle los saltos de las tablas */
        $this->SetAutoPageBreak(false, 13);


        /* Habilitar el marcador del total de páginas */
        $this->AliasNbPages();


        /* Registrar la fuente Alta para los subtítulos */
        $directorioFuentes = __DIR__ . '/../../fpdf/font';

        if (
            !is_file($directorioFuentes . '/Alta-Regular.json') ||
            !is_file($directorioFuentes . '/Alta-Regular.z')
        ) {
            throw new RuntimeException('No se encontraron los archivos de la fuente Alta.');
        }

        $this->AddFont('Alta', '', 'Alta-Regular.json', $directorioFuentes);
    }


    /* ================================================= */
    /* GENERACIÓN DEL DOCUMENTO */
    /* ================================================= */

    final public function generar(array $reporte): string
    {
        $this->reporte = $reporte;


        /* Establecer los datos de identificación del archivo */
        $titulo = trim((string) ($reporte['titulo'] ?? 'Reporte'));

        $this->SetTitle($titulo . ' | Templo Rosa', true);
        $this->SetAuthor('Templo Rosa Nails Studio', true);

        if (!empty($reporte['generado'])) {
            $this->SetSubject('Consulta: ' . $reporte['generado'], true);
        }


        /* Crear la página inicial y dibujar el contenido del reporte */
        $this->AddPage();
        $this->dibujarContenido();


        /* Devolver el documento completo como una cadena */
        return $this->Output('S');
    }


    /* ================================================= */
    /* CONTENIDO DEFINIDO POR CADA REPORTE */
    /* ================================================= */

    abstract protected function dibujarContenido(): void;


    /* ================================================= */
    /* ENCABEZADO DE LAS PÁGINAS */
    /* ================================================= */

    public function Header(): void
    {
        /* Incorporar el fondo con el logo y la palabra Reporte */
        $fondo = __DIR__ . '/../../Assets/img/TR_Reporte_Horizontal.png';

        if (!is_file($fondo)) {
            throw new RuntimeException('No se encontró la imagen de fondo de los reportes.');
        }

        $this->Image($fondo, 0, 0, self::PAPEL_ANCHO, self::PAPEL_ALTO, 'PNG');


        /* Mostrar DE y el nombre dinámico debajo de la palabra Reporte */
        $titulo = trim((string) ($this->reporte['titulo'] ?? 'Reporte'));
        $this->escribirSubtitulo('DE ' . $titulo);


        /* Identificar la emisión del documento */
        $this->SetXY(207, 7.4);
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(74, 60, 49);
        $this->Cell(58.4, 5, $this->textoParaPDF('Emitido: ' . date('d/m/Y h:i A')), 0, 1, 'R');


        /* Mostrar el nombre y el rol del usuario emisor */
        $this->SetX(207);
        $this->SetFont('Helvetica', 'B', 8);
        $this->Cell(58.4, 5, 'Generado por:', 0, 1, 'R');

        $nombre = trim((string) ($this->reporte['usuario_emisor'] ?? 'Usuario'));
        $rol = trim((string) ($this->reporte['rol_emisor'] ?? ''));

        $this->escribirTextoDerecha($nombre, 7.8);

        if ($rol !== '') {
            $this->escribirTextoDerecha($rol, 7.5);
        }


        /* Separar el encabezado del contenido mediante la línea dorada */
        $this->SetDrawColor(...self::ORO);
        $this->SetLineWidth(0.22);
        $this->Line(self::MARGEN, 44.7, self::PAPEL_ANCHO - self::MARGEN, 44.7);

        $this->SetY(47.5);
    }


    /* ================================================= */
    /* SUBTÍTULO DINÁMICO CON FUENTE ALTA */
    /* ================================================= */

    private function escribirSubtitulo(string $titulo): void
    {
        /* Conservar la presentación de Alta utilizada en las pruebas */
        $texto = $this->textoParaPDF(strtolower($titulo));
        $anchoMaximo = 68.5;
        $tamano = 15.3;

        $this->SetTextColor(74, 60, 49);


        /* Reducir la fuente cuando el nombre del reporte sea extenso */
        do {
            $this->SetFont('Alta', '', $tamano);

            if ($this->GetStringWidth($texto) <= $anchoMaximo || $tamano <= 8) {
                break;
            }

            $tamano -= 0.5;
        } while (true);


        /* Alinear el subtítulo con la R de Reporte, incluido el ajuste de 1 mm */
        $this->SetXY(39.6, 21.1);

        if ($this->GetStringWidth($texto) <= $anchoMaximo) {
            $this->Cell($anchoMaximo, 6, $texto);
            return;
        }


        /* Dividir un título muy largo en dos líneas dentro del encabezado */
        $lineas = [];
        $linea = '';

        foreach (explode(' ', $texto) as $palabra) {
            $prueba = trim($linea . ' ' . $palabra);

            if ($linea !== '' && $this->GetStringWidth($prueba) > $anchoMaximo) {
                $lineas[] = $linea;
                $linea = $palabra;

            } else {
                $linea = $prueba;
            }
        }

        if ($linea !== '') {
            $lineas[] = $linea;
        }

        foreach ($lineas as $indice => $linea) {
            $this->SetXY(39.6, 20.7 + $indice * 4);
            $this->Cell($anchoMaximo, 4, $linea);
        }
    }


    /* ================================================= */
    /* IDENTIFICACIÓN DEL USUARIO EMISOR */
    /* ================================================= */

    private function escribirTextoDerecha(string $texto, float $tamano): void
    {
        $contenido = $this->textoParaPDF($texto);

        $this->SetFont('Helvetica', '', $tamano);


        /* Ajustar nombres extensos al espacio del encabezado */
        while ($tamano > 6 && $this->GetStringWidth($contenido) > 58.4) {
            $tamano -= 0.3;
            $this->SetFont('Helvetica', '', $tamano);
        }

        $this->SetX(207);
        $this->Cell(58.4, 4.7, $contenido, 0, 1, 'R');
    }


    /* ================================================= */
    /* PIE DE LAS PÁGINAS */
    /* ================================================= */

    public function Footer(): void
    {
        /* Dibujar la separación dorada del pie de página */
        $this->SetDrawColor(...self::ORO);
        $this->SetLineWidth(0.22);
        $this->Line(self::MARGEN, 202.7, self::PAPEL_ANCHO - self::MARGEN, 202.7);


        /* Mostrar la empresa y el nombre dinámico del reporte */
        $titulo = trim((string) ($this->reporte['titulo'] ?? 'Reporte'));
        $pie = $this->textoParaPDF('Templo Rosa Nails Studio - ' . $titulo);
        $tamano = 7.7;

        $this->SetFont('Helvetica', '', $tamano);
        $this->SetTextColor(74, 60, 49);

        while ($tamano > 6.2 && $this->GetStringWidth($pie) > 195) {
            $tamano -= 0.3;
            $this->SetFont('Helvetica', '', $tamano);
        }

        $this->SetXY(self::MARGEN, 204);
        $this->Cell(195, 5, $pie);


        /* Mostrar la numeración automática de las páginas */
        $this->SetXY(209, 204);
        $this->SetFont('Helvetica', '', 7.7);
        $this->Cell(56.4, 5, $this->textoParaPDF('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }
}
