<?php


require_once('../Include/Librerias/TCPDF/tcpdf.php');
include_once('../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

global $DSN, $DSN_Ifx;
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

  $oIfx = new Dbo;
  $oIfx->DSN = $DSN_Ifx;
  $oIfx->Conectar();


$documento = $_SESSION['pdf'];
$idEmpresa = $_SESSION['U_EMPRESA'];
$idSucursal = $_SESSION['U_SUCURSAL'];

//print_r($documento);exit;
//PARAMETRO ORIENTACION DEL FORMATO

$sql="select prrh_val_prrh from saeprrh WHERE prrh_cod_empr = $idEmpresa and prrh_cod_prrh = 'ROHV'";

$orientacion=consulta_string($sql,'prrh_val_prrh', $oIfx,'');

if(empty($orientacion)){
    $orientacion='P';
}


    $pdf = new TCPDF($orientacion, 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetMargins(10,10, 10, true); 
    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    // set font
    $pdf->SetFont('helvetica', 'N', 8);
    // add a page
    $documento=explode('||||||||||',$documento);
    foreach($documento as $doc){

        if (!empty($doc)) {
            // add a page
            $pdf->AddPage();
            $pdf->writeHTMLCell(0, 0, '', '', $doc, 0, 1, 0, true, '', true);
        }

    }

    $pdf->writeHTMLCell(0, 0, '', '',$documento, 0, 1, 0, true, '', true); 
    ob_end_clean();
    $pdf->Output('documento.pdf','I');


?>

