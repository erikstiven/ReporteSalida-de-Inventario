<?php
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
//set_time_limit(3000);
//ini_set('memory_limit', '20000M');
//require_once 'html2pdf_v4.03/html2pdf.class.php';
require_once('../Include/Librerias/TCPDF/tcpdf.php');
$documento = $_SESSION['pdf'];

    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetMargins(10,10, 10, true); 
    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    // set image scale factor
    //$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    // set font
    $pdf->SetFont('helvetica', 'N', 10);
    // add a page
    $pdf->AddPage();
    $pdf->writeHTMLCell(0, 0, '', '',$documento, 0, 1, 0, true, '', true); 
    $pdf->Output('documento.pdf','I');


?>