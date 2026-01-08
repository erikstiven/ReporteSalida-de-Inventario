<?php

// set_time_limit(300);
// ini_set('memory_limit', '200M');
require_once('../Include/Librerias/TCPDF/tcpdf.php');
require_once ('Clases/Dbo.class.php');

global $DSN, $DSN_Ifx;
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

  $oIfx = new Dbo;
  $oIfx->DSN = $DSN_Ifx;
  $oIfx->Conectar();


$documento = $_SESSION['pdf'];
$idEmpresa = $_SESSION['U_EMPRESA'];
$idSucursal = $_SESSION['U_SUCURSAL'];

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    $pdf->SetMargins(10,10, 10, true); 
    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    $pdf->SetFont('helvetica', 'N', 10);
    // add a page
    $pdf->AddPage();
    $pdf->writeHTMLCell(0, 0, '', '',$documento, 0, 1, 0, true, '', true); 
    $pdf->Output('documento.pdf','I');



?>

