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

$array_fec=explode('-', $fecha);
$fecha=$array_fec[0].'/'.$array_fec[1].'/'.$array_fec[2];
$oIfx->Free();

$sql="select ciud_nom_ciud from saesucu inner join saeciud on saesucu.sucu_cod_ciud=saeciud.ciud_cod_ciud where saesucu.sucu_cod_sucu='$sucursal'";
if($oIfx->Query($sql)){
	if($oIfx->NumFilas()>0){
		$ciudad=$oIfx->f('ciud_nom_ciud');
	}
}
$oIfx->Free();


//echo $monto;exit;
	$V = new EnLetras();
	//$letra = strtoupper($V->ValorEnLetras($monto, 'dolar'));
	$letra = strtoupper($V->ValorEnLetras($monto, ''));
	//$fecha = DATE("Y/m/d");
	
  ////
   if(preg_match("/PICHINCHA/i",$formato)||preg_match("/GUAYAQUIL/i",$formato)||preg_match("/PACIFICO/i",$formato)||preg_match("/BOLIVARIANO/i",$formato)){
	   
	$log = strlen($beneficiario);
		$log1 = strlen($beneficiario);
		$spa2 = '&nbsp;';
		$spa = '';
		//echo $log;exit; 
	  for($log; $log<=100; $log++){
		//echo $log;
		$spa .= $spa2;
		if($log == 100){
			$monto = number_format($monto,2,'.','');
			$linea = $beneficiario.$spa.$monto;  
		}
	  }
		 $tablePDF='<table border="0" style="width: 100%; margin: 0px;  margin-top: 69px;">
					   <tr >
							<td style=" width: 63%; height: 29px; font-size:14px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$beneficiario.'</td>
							<td style=" width:37%; height: 29px; font-size:14px;" >**'.number_format($monto, 2, '.', ',').'</td>
						</tr>
						<tr style="m">
							<td style="width: 100%; height: 24px; font-size:14px;" colspan="2"  >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$letra.'</td>
						</tr>
						<tr   >
							<td colspan="2" style="width: 100%;height: 24px; font-size:14px; " >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx xxxxxx</td>
						</tr>
						<tr>
							<td colspan="2" style="width: 100%;height: 8px; font-size:14px; " >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ciudad.','.$fecha.'</td>
						</tr>
					</table>';
	$html.='<page backimgw="10%" backtop="5mm" backbottom="10mm" backleft="10mm" backright="4mm">';
	$html.=$tablePDF;
	$html.= '</page>';		
	 
   }
   if(preg_match("/PRODUBANCO/i",$formato)){
	
	    $log = strlen($beneficiario);
		$log1 = strlen($beneficiario);
		$spa2 = '&nbsp;';
		$spa = '';
		//echo $log;exit; 
	  for($log; $log<=100; $log++){
		//echo $log;
		$spa .= $spa2;
		if($log == 100){
			$monto = number_format($monto,2,'.','');
			$linea = $beneficiario.$spa.$monto;  
		}
	  }
	$beneficiario=substr($beneficiario,0,48);
		 $tablePDF='<table border="0" style="width: 100%; margin: 0px;  margin-top: 39px;">
					   <tr >
							<td style=" width: 80%; height: 19px; font-size:11px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$beneficiario.'</td>
							<td style=" width: 20%; height: 19px; font-size:13px;" >&nbsp;&nbsp;&nbsp;&nbsp;**'.number_format($monto, 2, '.', ',').'</td>
						</tr>
						<tr style="m">
							<td style="width: 100%; height: 14px; font-size:11px;" colspan="2" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$letra.'</td>
						</tr>
						<tr>
							<td colspan="2" style="width: 100%;height: 18px; font-size:12px; " >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx xxxxxx xxxxxx</td>
						</tr>
						<tr>
							<td colspan="2" style="width: 100%;height: 8px; font-size:11px; "  >&nbsp;&nbsp;'.$ciudad.','.$fecha.'</td>
						</tr>
					</table>';
	$html.='<page backimgw="10%" backtop="5mm" backbottom="10mm" backleft="10mm" backright="4mm">';
	$html.=$tablePDF;
	$html.= '</page>';	

   }
   if(preg_match("/INTERNACIONAL/i",$formato)){
		$log = strlen($beneficiario);
		$log1 = strlen($beneficiario);
		$spa2 = '&nbsp;';
		$spa = '';
		//echo $log;exit; 
	  for($log; $log<=100; $log++){
		//echo $log;
		$spa .= $spa2;
		if($log == 100){
			$monto = number_format($monto,2,'.','');
			$linea = $beneficiario.$spa.$monto;  
		}
	  }
	$beneficiario=substr($beneficiario,0,48);
		 $tablePDF='<table border="0" style="width: 100%; margin: 0px;  margin-top: 35px;">
					   <tr >
							<td style=" width: 70%; height: 17px; font-size:11px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$beneficiario.'</td>
							<td style=" width: 30%; height: 17px; font-size:13px;" >**'.number_format($monto, 2, '.', ',').'</td>
						</tr>
						<tr style="m">
							<td style="width: 100%; height: 12px; font-size:11px;" colspan="2" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$letra.'</td>
						</tr>
						<tr>
							<td colspan="2" style="width: 100%;height: 16px; font-size:12px; " >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx xxxxxx xxxxxx</td>
						</tr>
						<tr>
							<td colspan="2" style="width: 100%;height: 8px; font-size:11px; "  >&nbsp;&nbsp;'.$ciudad.','.$fecha.'</td>
						</tr>
					</table>';
	$html.='<page backimgw="10%" backtop="8mm" backbottom="10mm" backleft="4mm" backright="4mm">';
	$html.=$tablePDF;
	$html.= '</page>';				
	}

   if(preg_match("/PROCREDIT/i",$formato)){
		$log = strlen($beneficiario);
		$log1 = strlen($beneficiario);
		$spa2 = '&nbsp;';
		$spa = '';
		
		  for($log; $log<=100; $log++){
			//echo $log;
			$spa .= $spa2;
			if($log == 100){
				$linea = $beneficiario.$spa.$monto;  
			}
		  }
	    $tablePDF='<table   align="left" style="margin-left: 65px; ">
					<tr style="font-size:12px;">
						<td height="40" valign="bottom">
							<p>'.$linea.'<br><br>
							   '.$letra.'<br><br>
							xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx xxxxxx<br><br>
							'.$ciudad.','.$fecha.'</p>
						</td>
					</tr>
					
				</table>';
			
	}

	
    $html2pdf = new HTML2PDF('P', 'C5', 'es');
    $html2pdf->WriteHTML($html);
    $html2pdf->Output('recibo_template.pdf', '');?>