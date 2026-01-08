<?php
include_once('../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
include_once(path(DIR_INCLUDE) . 'html2pdf_v4.03/_tcpdf_5.0.002_old/tcpdf.php');

global $DSN_Ifx, $DSN;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();

$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();

$oCon = new Dbo;
$oCon->DSN = $DSN;
$oCon->Conectar();

$idEmpresa = $_SESSION['U_EMPRESA'];
$idSucursal = $_SESSION['U_SUCURSAL'];
$tipo = $_GET['tipo'];


//DATOS DE LA EMPRESA
$sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr, 
			empr_conta_sn, empr_num_resu, empr_path_logo
			from saeempr 
			where empr_cod_empr = $idEmpresa ";
if ($oIfx->Query($sql)) {
    if ($oIfx->NumFilas() > 0) {
        $razonSocial = trim($oIfx->f('empr_nom_empr'));
        $ruc_empr = $oIfx->f('empr_ruc_empr');
        $dirMatriz = trim($oIfx->f('empr_dir_empr'));
        $empr_path_logo = $oIfx->f('empr_path_logo');
    }
}
$oIfx->Free();

//LOGO DEL REPORTE
$path_img = explode("/", $empr_path_logo);
$count = count($path_img) - 1;
$arc_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

if (file_exists($arc_img)) {
    $imagen = $arc_img;
} else {
    $imagen = '';
}
$logo = '';
$x = '0px';
if ($imagen != '') {

    $empr_logo = '<div align="center">
            <img src="' . $imagen . '" style="
            width:100px;
            object-fit; contain;">
            </div>';
    $x = '0px';
} else {
    $empr_logo = 'LOGO NO CARGADO';
}


if ($tipo == 1) {
    //CABECERA
    $html = '<table style="font-size: 120%;" border="1" width="100%;" cellpadding="3">';
    $html .= '<tr>';
    $html .= '<td width="15%" rowspan="4">' .  $empr_logo . '</td>';
    $html .= '<td width="65%" align="center" rowspan="4"><b>ANÁLISIS DE TRABAJO SEGURO</b></td>';
    $html .= '<td width="20%"><b>CODIGO</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td  ><b>FECHA:</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td ><b>VERSION</b>_</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td><b>PAGINA:</b> de 1 de 2</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="100%" style="font-size:10%"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td bgcolor="#d8d8d8" style="font-size: 85%;" width="7%" align="center"><b>Fecha</b></td>';
    $html .= '<td style="font-size: 85%;" width="20%"></td>';
    $html .= '<td bgcolor="#d8d8d8" style="font-size: 85%;" width="30%" align="center"><b>Lugar donde se realizará el trabajo:</b></td>';
    $html .= '<td style="font-size: 85%;" width="43%"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td style="font-size:10%" width="100%" ></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td bgcolor="#d8d8d8" style="font-size: 85%;" width="100%" align="center"><b>TIPO DE ACTIVIDAD</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td  style="font-size: 85%;" width="20%" align="center"><b>Descripción de las<br>tareas a realizar</b></td>';
    $html .= '<td  style="font-size: 85%;" width="80%" align="center"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td  style="font-size: 85%;" width="100%"><b>Equipo y herramientas a utilizar:</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td  bgcolor="#d8d8d8" style="font-size: 85%;" width="20%" align="center"><b>Jefe de cuadrilla</b></td>';
    $html .= '<td  style="font-size: 85%;" width="80%" align="center"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="100%" style="font-size:10%" ></td>';
    $html .= '</tr>';

    $html .= '</table>';

    $html .= '<table style="font-size: 85%;" border="1" width="100%;" cellpadding="3">';

    $html .= '<tr>';
    $html .= '<td bgcolor="#d8d8d8"  width="100%" align="center"><b>PARA ESTE TRABAJO SE REQUIERE PERMISO DE:</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td  width="25%" >TRABAJO EN ALTURAS' . espacios_pro(5) . 'SI___NO___ </td>';
    $html .= '<td  width="20%" >ESPACIO CONFINADO SI___NO___</td>';
    $html .= '<td  width="15%" align="center">CALIENTE SI___NO___</td>';
    $html .= '<td  width="12%" align="center">OTRO SI___NO___</td>';
    $html .= '<td  width="28%" >CUAL?</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="100%" style="font-size:10%" ></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td bgcolor="#d8d8d8"  width="100%" align="center"><b>ANÁLISIS DE LA TAREA</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="25%">¿Qué tan alto se encuentra el lugar de trabajo?</td>';
    $html .= '<td width="25%"></td>';
    $html .= '<td width="25%">¿Cuáles son los elementos de protección requeridos?</td>';
    $html .= '<td width="25%"></td>'; 
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="25%">¿Cuál es el sistema de acceso al lugar de trabajo?</td>';
    $html .= '<td width="25%"></td>';
    $html .= '<td width="25%">¿Cuántos trabajadores se requieren?</td>';
    $html .= '<td width="25%"></td>'; 
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="25%">¿Se han establecido los puntos de anclaje?</td>';
    $html .= '<td width="25%"></td>';
    $html .= '<td width="25%">¿Qué materiales y recursos van a utilizarse?</td>';
    $html .= '<td width="25%"></td>'; 
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="25%">¿Se han realizado los cálculos de la distancia de caída?</td>';
    $html .= '<td width="25%"></td>';
    $html .= '<td width="25%">¿Existen hoyos o grietas debajo del área de trabajo?</td>';
    $html .= '<td width="25%"></td>'; 
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="25%">¿Cuáles son los sistemas de prevención y protección requeridos?</td>';
    $html .= '<td width="25%"></td>';
    $html .= '<td width="25%">¿Hay peligro de resbalar o tropezar alrededor del área de trabajo?</td>';
    $html .= '<td width="25%"></td>'; 
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="25%">¿Se han inspeccionado el equipo de trabajo en alturas?</td>';
    $html .= '<td width="25%"></td>';
    $html .= '<td width="25%">¿Cuenta con los equipos de emergencia (extintor, botiquin, camilla,  kit de rescate)</td>';
    $html .= '<td width="25%"></td>'; 
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="100%" style="font-size:10%" ></td>';
    $html .= '</tr>';

   

    $html .= '</table>';

    $html .= '<table style="font-size: 80%;" border="0" width="100%;" cellpadding="3">';

    $html .= '<tr>';
    $html .= '<td bgcolor="#d8d8d8"  width="100%" style="border:1px solid black; " align="center"><b>PANORAMA DE RIESGOS</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="2%" style="border:1px solid black;" ></td>'; 
    $html .= '<td bgcolor="#d8d8d8"  width="15%" style="border:1px solid black;" colspan="2" align="center"><b>PANORAMA DE RIESGOS</b></td>';
    $html .= '<td bgcolor="#d8d8d8"  width="15%" style="border:1px solid black;" colspan="2" align="center"><b>RIESGOS</b></td>';
    $html .= '<td bgcolor="#d8d8d8"  width="18%" style="border:1px solid black;" colspan="2" align="center"><b>MEDIDAS DE CONTROL SI__NO__</b></td>';
    $html .= '<td width="2%"></td>';
    $html .= '<td bgcolor="#d8d8d8"  width="15%" style="border:1px solid black;" colspan="2" align="center"><b>PELIGROS</b></td>';
    $html .= '<td bgcolor="#d8d8d8"  width="15%" style="border:1px solid black;" colspan="2" align="center"><b>RIESGOS</b></td>';
    $html .= '<td bgcolor="#d8d8d8"  width="18%" style="border:1px solid black;" colspan="2" align="center"><b>MEDIDAS DE CONTROL SI__NO__</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="2%"  style="border:1px solid black;"rowspan="8" align="center"><b>Q<br>U<br>Í<br>M<br>I<br>C<br>O<br>S</b></td>';
    $html .= '<td width="15%" style="font-size:5%; border-right:1px solid black;" colspan="2"></td>';
    $html .= '<td width="15%" style="font-size:5%; border-right:1px solid black;" colspan="2"></td>';
    $html .= '<td width="18%" style="font-size:5%; border-right:1px solid black;" colspan="2"></td>';
    $html .= '<td width="2%"  style="border:1px solid black;" align="center" rowspan="6"><b>B<br>I<br>O<br>L<br>O<br>G<br>I<br>C<br>O<br>S</b></td>';
    $html .= '<td width="15%" style="font-size:5%; border-right:1px solid black;" colspan="2"></td>';
    $html .= '<td width="15%" style="font-size:5%; border-right:1px solid black;" colspan="2"></td>';
    $html .= '<td width="18%" style="font-size:5%; border-right:1px solid black;" colspan="2"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="4%" style="border:1px solid black;"></td>';
    $html .= '<td width="11%">Gases y vapores</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%">Irritación en piel y/o mucosa</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="16%">Identificación rotulación del producto químico</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%">Virus y/o parásitos</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%">Mordeduras </td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="16%" style="border-right:1px solid black;">Inspección del área</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="4%" style="border:1px solid black;"></td>';
    $html .= '<td width="11%">Líquidos (productos químicos)</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%">Quemaduras</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="16%">Revisión de la MSDS del producto químico</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%">Hongos</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%">Picaduras de insectos</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="16%" style="border-right:1px solid black;">Reportar la presencia de insectos o animales</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="4%" style="border:1px solid black;"></td>';
    $html .= '<td width="11%">Partículas / Polvos químicos </td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%">Afecciones respiratorias</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="16%">Uso de delantal impermeable</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%">Bacterias</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%">Dermatitis</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="16%" style="border-right:1px solid black;">Controlar presencia de animales e insectos</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="4%" style="border:1px solid black;"></td>';
    $html .= '<td width="11%">Humos (combustión/soldadura) </td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%">Dermatitis</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="16%">Uso de guantes según el riesgo</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%">Animales</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%" style="border-right:1px solid black;">Enfermedades</td>';
    $html .= '<td width="2%" ></td>';
    $html .= '<td width="16%" style="border-right:1px solid black;"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="4%" style="border:1px solid black;"></td>';
    $html .= '<td width="11%">Transporte productos químicos</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%">Fatalidad</td>';
    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="16%">Uso de elementos de protección respiratoria</td>';
    $html .= '<td width="2%" ></td>';
    $html .= '<td width="13%" style="border-right:1px solid black;"></td>';
    $html .= '<td width="2%" ></td>';
    $html .= '<td width="13%" style="border-right:1px solid black;" ></td>';
    $html .= '<td width="5%" align="right">Otros:</td>';
    $html .= '<td width="13%" style="border-right:1px solid black;">___________________</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="4%" style="border:1px solid black;"></td>';
    $html .= '<td width="11%">Subproductos derivados del petróleo</td>';

    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%" style="border-right:1px solid black;">Derrames</td>';
    
    $html .= '<td width="18%" rowspan="2"  style="border-bottom:1px solid black;">Otros:_______________<br>_______________<br>________</td>';
    
    $html .= '<td width="2%" style="border-right:1px solid black;border-left:1px solid black;" rowspan="2"></td>';

    $html .= '<td bgcolor="#d8d8d8"  width="15%" style="border:1px solid black;" colspan="2" align="center"><b>PELIGROS</b></td>';
    $html .= '<td bgcolor="#d8d8d8"  width="15%" style="border:1px solid black;" colspan="2" align="center"><b>RIESGOS</b></td>';
    $html .= '<td bgcolor="#d8d8d8"  width="18%" style="border:1px solid black;" colspan="2" align="center"><b>MEDIDAS DE CONTROL SI__NO__</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="4%" style="border-bottom:1px solid black;"></td>';
    $html .= '<td width="11%" style="border-bottom:1px solid black;"></td>';

    $html .= '<td width="2%" style="border:1px solid black;"></td>';
    $html .= '<td width="13%" style="border-right:1px solid black; border-bottom:1px solid black;" >Afectación / impacto ambiental</td>';

    $html .= '<td width="2%" style="border:1px solid black;" ></td>';
    $html .= '<td width="13%">Mecanismos en movimiento</td>';

    $html .= '<td width="2%" style="border:1px solid black;" ></td>';
    $html .= '<td width="13%">Golpes / traumatismo</td>';

    $html .= '<td width="2%" style="border:1px solid black;" ></td>';
    $html .= '<td width="16%" style="border-right:1px solid black;">Uso de elementos de protección contra caídas</td>';

    $html .= '</tr>';
    $html .= '</table>';
   




    $html .= '<br><br><br><table style="font-size: 80%;" border="0" width="100%;" cellpadding="3">';

    $html .= '<tr>';
    $html .= '<td width="2%"  style="border:1px solid black;"rowspan="12" align="center"><b>E<br>R<br>G<br>O<br>N<br>Ó<br>M<br>I<br>C<br>O<br>S</b></td>';
    $html .= '<td bgcolor="#d8d8d8"  width="15%" style="border:1px solid black;" colspan="2" align="center"><b>PELIGROS</b></td>';
    $html .= '<td bgcolor="#d8d8d8"  width="15%" style="border:1px solid black;" colspan="2" align="center"><b>RIESGOS</b></td>';
    $html .= '<td bgcolor="#d8d8d8"  width="18%" style="border:1px solid black;" colspan="2" align="center"><b>MEDIDAS DE CONTROL SI__NO__</b></td>';

    $html .= '<td width="2%"  style="border-right:1px solid black;" align="center" rowspan="12"></td>';

    $html .= '<td width="2%" style="border:1px solid black;" ></td>';
    $html .= '<td width="13%">Superficies lisas o irregulares</td>';

    $html .= '<td width="2%" style="border:1px solid black;" ></td>';
    $html .= '<td width="13%">Fracturas de huesos</td>';

    $html .= '<td width="2%" style="border:1px solid black;" ></td>';
    $html .= '<td width="16%">Verificar que las superficies de trabajo estén en orden </td>';
    
    $html .= '</tr>';

    $html .= '</table>';

    
    $orientacion = 'L';
} //CIERRE IF TIPO 1

//CONTRATO AZOCADO
elseif ($tipo == 2) {
   
    $orientacion = 'P';
}

//ORDEN DE PRODUCCION
elseif ($tipo == 3) {
   
    $orientacion = 'L';
} //CIERRE IF TIPO 3




$documento = <<<EOD
    $html
EOD;

$pdf = new TCPDF2($orientacion, 'mm', 'A4', true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(5,5,5, true); 
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// set font
$pdf->SetFont('helvetica', 'N', 10);
// add a page
$pdf->AddPage();
$pdf->writeHTMLCell(0, 0, '', '', $documento, 0, 1, 0, true, '', true);
$pdf->Output($num_ped . '.pdf', 'I');

//ESPACIOS EN BLANCO
function espacios_pro($cant)
{
    $n = "";
    for ($i = 0; $i <= $cant; $i++) {
        $n .= "&nbsp;";
    }
    return $n;
}
