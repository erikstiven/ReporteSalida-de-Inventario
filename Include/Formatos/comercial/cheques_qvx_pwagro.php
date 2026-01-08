<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<?

include_once('../../../Include/config.inc.php');
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

//require_once '../html2pdf_v4.03/html2pdf.class.php';


//require_once 'html2pdf_v4.03/_tcpdf_5.0.002/tcpdf.php';
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
	
if($oIfx->Query($sql)){
	if($oIfx->NumFilas()>0){
		$formato_banc=$oIfx->f('banc_nom_banc');
		
	}
}

$oIfx->Free();

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
		$beneficiario=$oIfx->f('dchc_benf_dchc');
		$monto=$oIfx->f('dchc_val_dchc');
		$fecha=$oIfx->f('dchc_fec_dchc');
	}
}

$array_fec=explode('-', $fecha);
$fecha=$array_fec[0].'/'.$array_fec[1].'/'.$array_fec[2];
$oIfx->Free();

//VALIDACION FORMATO CHEQUE

if(empty($formato)){

	$formato=$formato_banc;
}
else{

$sqlf="select ftrn_des_ftrn from saeftrn where ftrn_cod_ftrn=$formato";
$formato=consulta_string($sqlf,'ftrn_des_ftrn',$oIfxA,'');
}


//echo $formato;exit;
$sql="select ciud_nom_ciud from saesucu inner join saeciud on saesucu.sucu_cod_ciud=saeciud.ciud_cod_ciud where saesucu.sucu_cod_sucu='$sucursal'";
if($oIfx->Query($sql)){
	if($oIfx->NumFilas()>0){
		$ciudad=$oIfx->f('ciud_nom_ciud');
	}
}
$oIfx->Free();

	$V = new EnLetras();
	//$letra = strtoupper($V->ValorEnLetras($monto, 'dolar'));
	$letra = strtoupper($V->ValorEnLetras($monto, ''));
	//$fecha = DATE("Y/m/d");


	///FORMATOS VALIDADOS QUEVEXPORT
	if(preg_match("/PICHINCHA/i",$formato)){
		$log = strlen($beneficiario);
		$log1 = strlen($beneficiario);
		$spa2 = '&nbsp;';
		$spa = '';
	  for($log; $log<=100; $log++){
		$spa .= $spa2;
		if($log == 100){
			$monto = number_format($monto,2,'.','');
			$linea = $beneficiario.$spa.$monto;  
		}
	  }
		$tablePDF='<table border="0" style="width: 100%; margin: 0px;  margin-top: 43px; margin-left: 150px;">
		<tr >
			<td align="left" style=" width: 48%; height: 29px; font-size:13px;" ><FONT FACE="impact">'.$beneficiario.'</font></td>
			<td align="left" style=" width:52%; height: 29px; font-size:13px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.number_format($monto, 2, '.', ',').'</td>
		</tr>
		<tr >
			<td style="width: 100%; height: 11px; font-size:13px;" colspan="2"  >&nbsp;'.$letra.'&nbsp; xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx </td>
		</tr>
		<tr   >
			<td colspan="2" style="width: 100%;height: 20px; font-size:14px; " >&nbsp; xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx xxxxxx xxxxxx</td>
		</tr>
		<tr>
			<td colspan="2" style="width: 100%;height: 8px; font-size:13px; " >'.$ciudad.',&nbsp;&nbsp;'.$fecha.'</td>
		</tr>
	</table>';
	}

	if(preg_match("/PACIFICO/i",$formato)){
		$log = strlen($beneficiario);
		$log1 = strlen($beneficiario);
		$spa2 = '&nbsp;';
		$spa = '';

	  for($log; $log<=100; $log++){

		$spa .= $spa2;
		if($log == 100){
			$monto = number_format($monto,2,'.','');
			$linea = $beneficiario.$spa.$monto;  
		}
	  }
	  $tablePDF='<table border="0" style="width: 100%; margin: 0px;  margin-top: 54px; margin-left: 141px;">
	  <tr >
		  <td align="left" style=" width: 49%; height: 19px; font-size:13px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$beneficiario.'</td>
		  <td align="left" style=" width:51%; height: 16px; font-size:13px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.number_format($monto, 2, '.', ',').'</td>
	  </tr>
	  <tr style="m">
		  <td style="width: 100%; height: 6px; font-size:12px;" colspan="2"  >&nbsp;'.$letra.'&nbsp; xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx </td>
	  </tr>
	  <tr   >
		  <td colspan="2" style="width: 100%;height: 22px; font-size:14px; " >&nbsp; xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx xxxxxx xxxxxx</td>
	  </tr>
	  <tr>
		  <td colspan="2" style="width: 100%;height: 8px; font-size:13px; " >'.$ciudad.',&nbsp;&nbsp;'.$fecha.'</td>
	  </tr>
	  </table>';
	}

	if(preg_match("/PRODUBANCO/i",$formato)){
		$log = strlen($beneficiario);
		$log1 = strlen($beneficiario);
		$spa2 = '&nbsp;';
		$spa = '';

	  for($log; $log<=100; $log++){

		$spa .= $spa2;
		if($log == 100){
			$monto = number_format($monto,2,'.','');
			$linea = $beneficiario.$spa.$monto;  
		}
	  }
	  $tablePDF='<table border="0" style="width: 100%; margin: 0px;  margin-top: 61px; margin-left: 130px;">
	  <tr >
		  <td align="left" style=" width: 54%; height: 20px; font-size:13px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$beneficiario.'</td>
		  <td align="left" style=" width:46%; height: 16px; font-size:13px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.number_format($monto, 2, '.', ',').'</td>
	  </tr>
	  <tr style="m">
		  <td style="width: 100%; height: 6px; font-size:12px;" colspan="2"  >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$letra.'&nbsp; xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx </td>
	  </tr>
	  <tr   >
		  <td colspan="2" style="width: 100%;height: 19px; font-size:14px; " >&nbsp; xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx xxxxxx xxxxxx</td>
	  </tr>
	  <tr>
		  <td colspan="2" style="width: 100%;height: 8px; font-size:13px; " >'.$ciudad.',&nbsp;&nbsp;'.$fecha.'</td>
	  </tr>
	  </table>';
	}

	if(preg_match("/GUAYAQUIL/i",$formato)){
			$log = strlen($beneficiario);
			$log1 = strlen($beneficiario);
			$spa2 = '&nbsp;';
			$spa = '';

		  for($log; $log<=100; $log++){

			$spa .= $spa2;
			if($log == 100){
				$monto = number_format($monto,2,'.','');
				$linea = $beneficiario.$spa.$monto;  
			}
		  }
		  $tablePDF='<table border="0" style="width: 100%; margin: 0px;  margin-top: 50px; margin-left: 154px;">
		  <tr >
			   <td align="left" style=" width: 46%; height: 21px; font-size:12px;" >&nbsp;'.$beneficiario.'</td>
			   <td align="left" style=" width:54%; height: 16px; font-size:12px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.number_format($monto, 2, '.', ',').'</td>
		   </tr>
		   <tr style="m">
			   <td style="width: 100%; height: 10px; font-size:12px;" colspan="2"  >&nbsp;'.$letra.'&nbsp; xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx </td>
		   </tr>
		   <tr   >
			   <td colspan="2" style="width: 100%;height: 22px; font-size:14px; " >&nbsp; xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx xxxxxx xxxxxx</td>
		   </tr>
		   <tr>
			   <td colspan="2" style="width: 100%;height: 8px; font-size:12px; " >'.$ciudad.','.$fecha.'</td>
		   </tr>
	   </table>';
		}
		
		if(preg_match("/BOLIVARIANO/i",$formato)){
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
			  $tablePDF='<table border="0" style="width: 100%; margin: 0px;  margin-top: 44px; margin-left: 145px;">
			  <tr >
				   <td align="left" style=" width: 52%; height: 25px; font-size:11px;" >&nbsp;&nbsp;&nbsp;&nbsp;'.$beneficiario.'</td>
				   <td align="left" style=" width:48%; height: 27px; font-size:11px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.number_format($monto, 2, '.', ',').'</td>
			   </tr>
			   <tr style="m">
				   <td style="width: 100%; height: 9px; font-size:11px;" colspan="2"  >&nbsp;&nbsp;&nbsp;'.$letra.'&nbsp; xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx </td>
			   </tr>
			   <tr   >
				   <td colspan="2" style="width: 100%;height: 25px; font-size:14px; " >&nbsp; xxxxxxxxxxxxxxxxxxx xxxxx xxxxxxxxx xxxxxxxxxxx xxxxxx xxxxxx</td>
			   </tr>
			   <tr>
				   <td colspan="2" style="width: 100%;height: 8px; font-size:11px; " >'.$ciudad.',&nbsp;&nbsp;'.$fecha.'</td>
			   </tr>
		   </table>';
		}	
  //////////////////////////_______________________________\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\


   
	/*$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
	$pdf->SetFont('helvetica', 'I');
	$pdf->AddPage();
    $pdf->writeHTMLCell(0, 0, '', '',$tablePDF, 0, 1, 0, true, '', true); 
    $pdf->Output('recibo_template.pdf','I');*/

	$html2pdf = new HTML2PDF('P', 'A4', 'es');
	//$html2pdf->SetFont('helvetica', 'I', 10);
	$html2pdf->WriteHTML($tablePDF);
    $html2pdf->Output('recibo_template.pdf', 10);
	?>