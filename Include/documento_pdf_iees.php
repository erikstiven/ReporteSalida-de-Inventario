<?php
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
require_once('../Include/Librerias/TCPDF/tcpdf.php');
include_once('../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'funciones_clinico.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

global $DSN_Ifx, $DSN;
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();

$idempresa = $_SESSION['U_EMPRESA'];


//DATOS DE LA EMPRESA

    $sql = "select 
    empr_path_logo
    from saeempr where empr_cod_empr = $idempresa ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                
                    $empr_path_logo = $oIfx->f('empr_path_logo');            
    }
    }
    $oIfx->Free();

    $ruta=basename($empr_path_logo);
    ///////LOGO DEL REPORTE ///////////////
    //$arc?img='../../../file/img/'.$imagen_i;
    $arc_img=DIR_FACTELEC."Include/Clases/Formulario/Plugins/reloj/$ruta";

    $logo='';

    if(file_exists($arc_img)){
        $imagen=$arc_img;
    }else{
        $imagen='';
    }

    $x='0px';
    if($imagen!=''){

        $logo='<div><img src="'. $imagen .'" style="width:180px;object-fit; contain;"></div>';
        $x='0px'; 
    }
    else{
        $logo='';

    }


    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetMargins(10,5, 10, true);
    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    // set font
    $pdf->SetFont('helvetica', 'N', 10);
    // add a page

    $encabezado=encabezado_reportes();

    $documento = $_SESSION['pdf'];



    if(preg_match("/ACTA DE ENTREGA/",$documento))
    {
        $doc='<div><table    cellpadding="1" border="1"><tr><td align="center"> '.$logo.'</td>';
        $doc.=$documento;
        $pdf->AddPage();
        $pdf->writeHTMLCell(0, 0, '', '', $doc, 0, 1, 0, true, '', true);

    }

    else
        {
            $documento=explode('||||||||||',$documento);

            foreach($documento as $doc){
                if(!empty($doc)){
                    $html=$encabezado;
                    $html.=$doc;
                    $pdf->AddPage();
                    
                    $pdf->writeHTMLCell(0, 0, '', '',$html, 0, 1, 0, true, '', true); 
                }
            
            }
        }


    $pdf->Output('documento.pdf','I');

?>

