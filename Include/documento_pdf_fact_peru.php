<?php
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
set_time_limit(3000);
ini_set('memory_limit', '20000M');
include_once('../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');




$documento = $_SESSION['pdf'];

$S_PAIS_API_SRI = $_SESSION['S_PAIS_API_SRI'];
$idempresa = $_SESSION['U_EMPRESA'];

try{

    global $DSN, $DSN_Ifx;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    //VALIDACION FORMATO PERSONALIZADO
    $sql = "select ftrn_cod_html from saeftrn  where ftrn_cod_empr=$idempresa and ftrn_des_ftrn = 'FACTURA' and ftrn_cod_modu=7 
    and (ftrn_ubi_web is not null or ftrn_ubi_web != '') and ftrn_ubi_web like '%Formatos%'";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $html = $oIfx->f('ftrn_cod_html');      
        }
    }
    $orientacion='P';
    if(preg_match("/formHorizontal/i",$html)){
        $orientacion='L';
    }



//ECUADOR
if ($S_PAIS_API_SRI == '593') {

    $html2pdf = new HTML2PDF($orientacion,'A3','es');
}
else{
    $html2pdf = new HTML2PDF($orientacion,'A4','es');
}

if(preg_match("/TipoA4/i",$html)){
    $html2pdf = new HTML2PDF($orientacion,'A4','es');
}


$html2pdf->WriteHTML($documento);
ob_end_clean();
$html2pdf->Output('documento.pdf','I');
}
catch(HTML2PDF_exception $e){    
    echo $e;
    exit;

}


?>