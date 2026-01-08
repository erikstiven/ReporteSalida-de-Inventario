<?php
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
set_time_limit(3000);
ini_set('memory_limit', '20000M');
require_once('../modulos/reporte_001_prueba/library/tcpdf.php');
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
$pdf->AddPage();

///////LOGO DEL REPORTE ///////////////
//$arc?img='../../../file/img/'.$imagen_i;
$arc_img="images/logo2.jpg";
if(file_exists($arc_img)){
    $imagen=$arc_img;
}else{
    $imagen='';
}
$logo='';
$x='0px';
if($imagen!=''){

    $logo='<div>
            <img src="'. $imagen .'" style="
            width:250px;
            height:100px;
            object-fit; contain;">
            </div>';
    $x='0px';
}


$encabezado='<table    cellpadding="1" border="0">
<tr>
<td rowspan="7" align="left">'.$logo.' </td>
<td></td>   
</tr>
<tr>
<td></td>
</tr>
<tr>
<td style="font-size:80%;"align="rigth"  >Edificio Fundación Médica Mosquera</td>
</tr>
<tr>
    <td style="font-size:80%;"align="rigth">Flores 912 y Manabí (Plaza de Teatro)</td>
</tr>
<tr>
    <td style="font-size:80%;"align="rigth">Teléfonos: 295 1225-295 3572 - Fax : 228 7794</td>
</tr>
<tr>
    <td style="font-size:80%;"align="rigth" >administracion@hospitaleninmosquera.org</td>
</tr>
<tr>
    <td style="font-size:80%;"align="rigth" height=200>www.hospitaleninmosquera.org</td>
</tr>
</table>';
$documento .= $_SESSION['pdf1'];


    $pdf->writeHTMLCell(0, 0, '', '', $documento, 0, 1, 0, true, '', true); 



$pdf->Output('documento.pdf','I');

?>