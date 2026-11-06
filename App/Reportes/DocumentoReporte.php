<?php

namespace App\Reportes;

use App\Interfaces\ExportadorReporteInterface;
use App\Traits\FormatoReporteTrait;
use FPDF;


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


    /* ================================================= */
    /* CONFIGURACIÓN DEL DOCUMENTO */
    /* ================================================= */

    public function __construct()
    {
        /* Configurar una página A4 horizontal y sus márgenes */
        parent::__construct('L', 'mm', 'A4');

        $this->SetMargins(14, 14, 14);
        $this->cMargin = 0;


        /* Permitir que la clase concreta controle los saltos de las tablas */
        $this->SetAutoPageBreak(false, 17);


        /* Habilitar el marcador del total de páginas */
        $this->AliasNbPages();
    }


    /* ================================================= */
    /* GENERACIÓN DEL DOCUMENTO */
    /* ================================================= */

    final public function generar(array $reporte): string
    {
        $this->reporte = $reporte;


        /* Establecer los datos de identificación del archivo */
        $this->SetTitle($reporte['titulo'] . ' | Templo Rosa', true);
        $this->SetAuthor('Templo Rosa Nails Studio', true);


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
        /* Incorporar el logo cuando el archivo esté disponible */
        $logo = __DIR__ . '/../../Assets/img/LogoTR.png';

        if (is_file($logo)) {
            $this->Image($logo, 14, 10, 19, 19);
        }


        /* Mostrar la identificación de la empresa */
        $this->SetXY(37, 12);
        $this->SetFont('Helvetica', 'B', 15);
        $this->SetTextColor(184, 147, 68);
        $this->Cell(0, 7, 'TEMPLO ROSA', 0, 1);

        $this->SetX(37);
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(122, 101, 91);
        $this->Cell(0, 5, 'Nails Studio | Reportes', 0, 1);


        /* Identificar la consulta original y la emisión del documento */
        $this->SetXY(190, 12);
        $this->SetFont('Helvetica', '', 8);

        $this->Cell(93, 6, $this->textoParaPDF('Consulta: ' . $this->reporte['generado']), 0, 1, 'R');

        $this->SetX(190);

        $this->Cell(93, 5, $this->textoParaPDF('Emitido: ' . date('d/m/Y H:i:s')), 0, 1, 'R');


        /* Separar la identificación del contenido del reporte */
        $this->SetDrawColor(216, 167, 177);
        $this->Line(14, 32, 283, 32);


        /* Mostrar el título del reporte */
        $this->SetXY(14, 35);
        $this->SetFont('Helvetica', 'B', 12);
        $this->Cell(0, 7, $this->textoParaPDF($this->reporte['titulo']), 0, 1);

        $this->Ln(3);
    }


    /* ================================================= */
    /* PIE DE LAS PÁGINAS */
    /* ================================================= */

    public function Footer(): void
    {
        /* Dibujar la separación del pie de página */
        $this->SetY(-13);
        $this->SetDrawColor(232, 206, 216);
        $this->Line(14, $this->GetY(), 283, $this->GetY());


        /* Configurar la presentación del texto */
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(122, 101, 91);


        /* Mostrar la identificación y la numeración de páginas */
        $this->Cell(210, 8, $this->textoParaPDF('Templo Rosa Nails Studio · Consulta de registros'), 0, 0);

        $this->Cell(59, 8, $this->textoParaPDF('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }
}