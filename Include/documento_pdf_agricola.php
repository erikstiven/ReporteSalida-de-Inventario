<?php

// set_time_limit(300);
// ini_set('memory_limit', '200M');
require_once('../modulos/despacho_aplicaciones_plani/library/tcpdf.php');
require_once ('Clases/Dbo.class.php');

global $DSN, $DSN_Ifx;
session_start();

  $oIfx = new Dbo;
  $oIfx->DSN = $DSN_Ifx;
  $oIfx->Conectar();


$documento = $_SESSION['pdf'];
$idEmpresa = $_SESSION['U_EMPRESA'];
$idSucursal = $_SESSION['U_SUCURSAL'];



    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
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

