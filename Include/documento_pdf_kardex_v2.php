<?php
ini_set('memory_limit', '-1');

session_start();
//require_once 'html2pdf_v4.03/html2pdf.class.php';

require_once('../Include/Librerias/TCPDF/tcpdf.php');

$documento = $_SESSION['reporte_excel_kardex_v2'];

$pdf = new TCPDF('L', 'mm', 'A5', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(8, 8, 8, true);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// set image scale factor
//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// set font
$pdf->SetFont('helvetica', 'N', 5);
// add a page
$pdf->AddPage();
$pdf->writeHTMLCell(0, 0, '', '', $documento, 0, 1, 0, true, '', true);
$pdf->Output('documento.pdf', 'I');


?>