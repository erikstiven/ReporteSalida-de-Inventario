<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
set_time_limit(3000);
ini_set('memory_limit', '20000M');
include_once('../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
require_once 'html2pdf_v4.03/html2pdf.class.php';

$documento = $_SESSION['pdf'];




if (isset($_GET['pdf2'])) {

    $html2pdf = new HTML2PDF('P', 'A4', 'es', true, 'UTF-8', 4);
    $html2pdf->WriteHTML($_SESSION['pdf_2']);
    $html2pdf->Output('documento2.pdf', 'I');
} elseif (isset($_GET['pdf3'])) {

    $html3pdf = new HTML2PDF('P', 'A4', 'es', true, 'UTF-8', 3);
    $html3pdf->pdf->SetDisplayMode('fullpage');
    $html3pdf->WriteHTML($documento);
    $html3pdf->Output('documento2.pdf', 'I');
} else {
    try {

        global $DSN, $DSN_Ifx;

        $oIfx = new Dbo;
        $oIfx->DSN = $DSN_Ifx;
        $oIfx->Conectar();

        $orientacion = 'P';
        $html2pdf = new HTML2PDF($orientacion, 'A3', 'es', true, 'UTF-8', 4);

        $html2pdf->WriteHTML($documento);
        ob_end_clean();
        $html2pdf->Output('documento.pdf', 'I');
    } catch (HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
}
