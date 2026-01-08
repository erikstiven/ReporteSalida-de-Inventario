<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<?

include_once('../../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
	if (isset($_REQUEST['dato'])){
		$dato = $_REQUEST['dato'];
	}else{
		$dato = '';
	}
	if (isset($_REQUEST['formato'])){
		$formato = trim($_REQUEST['formato']);
	}else{
		$formato = '';
	}

	if (isset($_REQUEST['fecha'])){
		$fecha_pago = trim($_REQUEST['fecha']);
	}


if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

global $DSN_Ifx, $DSN;

// conexxion

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();


$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();

$arreglo = explode('/', $dato);
$empresa = $arreglo[0];
$sucursal = $arreglo[1];
$asto = $arreglo[2];
$ejer = $arreglo[3];
$prdo = $arreglo[4];

$sql="select banc_nom_banc
	from  saedchc 
	inner join saectab on  saedchc.dchc_cod_ctab = saectab.ctab_cod_ctab
	inner join saebanc on saectab.ctab_cod_banc = saebanc.banc_cod_banc
	where 
	dchc_cod_asto = '$asto' and 
	asto_cod_empr = '$empresa' and 
	asto_cod_sucu= '$sucursal' and 
	asto_cod_ejer= '$ejer' and  
	asto_num_prdo='$prdo'"; 
	//echo $sql;exit;
	
if($oIfx->Query($sql)){
	if($oIfx->NumFilas()>0){
		$formato_banc=$oIfx->f('banc_nom_banc');
		
	}
}

$oIfx->Free();


//VALIDACION FORMATO CHEQUE

if(empty($formato)){

	$formato=$formato_banc;
}
else{

$sqlf="select ftrn_des_ftrn from saeftrn where ftrn_cod_ftrn=$formato";
$formato=consulta_string($sqlf,'ftrn_des_ftrn',$oIfxA,'');
}


$sql="select dchc_fec_dchc,dchc_benf_dchc, dchc_val_dchc
		
		from saedchc where
		asto_cod_empr = $empresa and
		dchc_cod_asto='$asto' and 
		asto_cod_sucu='$sucursal' and 
		asto_cod_ejer ='$ejer' and
		asto_num_prdo ='$prdo'
		
		order by 1 ";
//echo $sql;exit;
if($oIfx->Query($sql)){
	if($oIfx->NumFilas()>0){
		$beneficiario=trim($oIfx->f('dchc_benf_dchc'));
		$monto=$oIfx->f('dchc_val_dchc');
		$fecha=$oIfx->f('dchc_fec_dchc');
	}
}

/*$array_fec=explode('/', $fecha);
$fecha=$array_fec[2].'/'.$array_fec[0].'/'.$array_fec[1];
$oIfx->Free();*/

$mes=date('m',strtotime($fecha_pago));
$dia=date('d',strtotime($fecha_pago));
$anio=date('y',strtotime($fecha_pago));

$mes_hoy=date('m');
$dia_hoy=date('d');
$anio_hoy=date('y');




$array_fec=explode('-', $fecha);
$fecha=$array_fec[0].'/'.$array_fec[1].'/'.$array_fec[2];
$oIfx->Free();

$sql="select ciud_nom_ciud from saeempr inner join saeciud on saeempr.empr_cod_ciud=saeciud.ciud_cod_ciud where saeempr.empr_cod_empr='$empresa'";
if($oIfx->Query($sql)){
	if($oIfx->NumFilas()>0){
		$ciudad=$oIfx->f('ciud_nom_ciud');
	}
}
$oIfx->Free();

//echo $monto;exit;
	$V = new EnLetras();
$letra = strtoupper($V->ValorEnLetrasMonePeru($monto, 'SOLES'));
	//$letra = strtoupper($V->ValorEnLetras($monto, ''));
	//$fecha = DATE("Y/m/d");
	
  ////
   
   
   if(preg_match("/BBVA/i",$formato)){

	$beneficiario=substr($beneficiario,0,48);
	$vacios    = cero_mas_func('X', 63 - strlen($letra));


		 $tablePDF='<table border="0" style="width: 100%; margin: 0px;  margin-top: 39px;" >
	  					<tr>
						  <td style=" width: 37%; height: 19px; font-size:12px;" align="right">'.$dia_hoy.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$mes_hoy.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$anio_hoy.'</td>
						  <td style=" width: 15%; height: 19px; font-size:12px;" >&nbsp;&nbsp;&nbsp;&nbsp;'.$dia.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$mes.'&nbsp;&nbsp;&nbsp;&nbsp;'.$anio.'</td>
						  <td style=" width: 58%; height: 19px; font-size:12px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.number_format($monto, 2, '.', ',').'</td>
						</tr>
					</table>
					<table border="0" style="width: 100%; margin: 0px; margin-top: 24px;">
					   <tr >
							<td style=" width: 100%; height: 28px; font-size:12px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$beneficiario.'</td>
							
						</tr>
						<tr >
							<td style="width: 100%; height: 14px; font-size:12px;" >&nbsp;&nbsp;&nbsp;&nbsp;'.$letra.' '.$vacios.'</td>
						</tr>
						
					</table>';

	$html.='<page backimgw="10%" backtop="11mm" backbottom="10mm" backleft="18mm" backright="4mm">';
	$html.=$tablePDF;
	$html.= '</page>';	
	

					
   }

   if(preg_match("/BCP/i",$formato)){
	$beneficiario=substr($beneficiario,0,48);
	$vacios    = cero_mas_func('X', 57 - strlen($letra));


		 $tablePDF='<table border="0" style="width: 100%; margin: 0px;  margin-top: 38px;" >
	  					<tr>
						  <td style=" width: 38%; height: 19px; font-size:12px;" align="right">'.$dia_hoy.'&nbsp;&nbsp;&nbsp;'.$mes_hoy.'&nbsp;&nbsp;&nbsp;&nbsp;'.$anio_hoy.'</td>
						  <td style=" width: 15%; height: 19px; font-size:12px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$dia.'&nbsp;&nbsp;'.$mes.'&nbsp;&nbsp;&nbsp;'.$anio.'</td>
						  <td style=" width: 57%; height: 19px; font-size:12px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.number_format($monto, 2, '.', ',').'</td>
						</tr>
					</table>
					<table border="0" style="width: 100%; margin: 0px; margin-top: 21px;">
					   <tr >
							<td style=" width: 100%; height: 28px; font-size:12px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$beneficiario.'</td>
							
						</tr>
						<tr >
							<td style="width: 100%; height: 14px; font-size:12px;" >&nbsp;&nbsp;&nbsp;&nbsp;'.$letra.' '.$vacios.'</td>
						</tr>
						
					</table>';

	$html.='<page backimgw="10%" backtop="7mm" backbottom="10mm" backleft="18mm" backright="4mm">';
	$html.=$tablePDF;
	$html.= '</page>';	
	
   }

   if(preg_match("/SCOTIABANK/i",$formato)){

	$beneficiario=substr($beneficiario,0,48);
	$vacios    = cero_mas_func('X', 53 - strlen($letra));


		 $tablePDF='<table border="0" style="width: 100%; margin: 0px;  margin-top: 25px;" >
	  					<tr>
						  <td style=" width: 39%; height: 19px; font-size:12px;" align="right">'.$dia_hoy.'&nbsp;&nbsp;&nbsp;'.$mes_hoy.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$anio_hoy.'</td>
						  
						  <td style=" width: 61%; height: 19px; font-size:12px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.number_format($monto, 2, '.', ',').'</td>
						</tr>
						<tr>
							<td colspan="2" style="height: 17px; font-size:12px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$dia.'&nbsp;&nbsp;&nbsp;'.$mes.'&nbsp;&nbsp;&nbsp;'.$anio.'</td>
						</tr>
					</table>
					<table border="0" style="width: 100%; margin: 0px; margin-top: 20px;">
					   <tr >
							<td style=" width: 100%; height: 30px; font-size:12px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$beneficiario.'</td>
							
						</tr>
						<tr >
							<td style="width: 100%; height: 14px; font-size:12px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$letra.' '.$vacios.'</td>
						</tr>
						
					</table>';

	$html.='<page backimgw="10%" backtop="7mm" backbottom="10mm" backleft="18mm" backright="4mm">';
	$html.=$tablePDF;
	$html.= '</page>';
	

					
   }

   

   

	
    $html2pdf = new HTML2PDF('L', 'C5', 'es');
    $html2pdf->WriteHTML($html);
    $html2pdf->Output('recibo_template.pdf', '');?>