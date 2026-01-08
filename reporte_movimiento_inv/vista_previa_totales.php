<?
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');


if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/general.css">
	<link href="<?= $_COOKIE["JIREH_INCLUDE"] ?>Clases/Formulario/Css/Formulario.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="css/estilo.css">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>MOVIEMIENTO INVENTARIO</title>
	<style type="text/css">
		<!--
		.Estilo1 {
			font-size: 12px;
			font-family: Georgia, "Times New Roman", Times, serif;
			color: #000000;
			font-weight: bold;
		}

		.Estilo2 {
			font-size: 10px;
			font-family: Georgia, "Times New Roman", Times, serif;
			color: #000000;
			font-weight: bold;
		}

		.Estilo3 {
			font-family: Verdana, Arial, Helvetica, sans-serif
		}

		.Estilo4 {
			font-size: 16px;
			font-weight: bold;
			color: #000000;
		}

		.fecha {
			font-family: Tahoma, Arial, sans-serif;
			font-size: 34px;
			font-weight: bold;
			color: #000000;
		}
		-->
	</style>

	<script>
		function formato() {
			document.getElementById('dos').style.display = "none";
			window.print();
		}

		function formato_excel() {
			window.opener.abrir_detalle_excel();
		}
	</script>
</head>

<body>

	<?
	$oCnx = new Dbo();
	$oCnx->DSN = $DSN_Ifx;
	$oCnx->Conectar();

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$serial_minv = $_GET['codigo'];
	$id_empresa  = $_GET['empr'];
	$id_sucursal = $_GET['sucu'];


	// USAUURO
	$sql 		= "select empr_nom_empr from saeempr where empr_cod_empr = $id_empresa ";
	$empr_nom 	= consulta_string($sql, 'empr_nom_empr', $oIfx, '');



	if ($serial_minv > 0) {
	?>

		<div id="uno">

			<table width="98%" height="95%" border="0" align="center">
				<tr>
					<td height="5">&nbsp;</td>
					<td height="20" colspan="2">
						<div align="center" class="fecha_balance"><? echo $empr_nom; ?></div>
					</td>
				</tr>
				<tr>
					<td colspan="4" class="Estilo2" align="left" width="90%">

						<?
						$sql_des = "select mi.minv_fmov,     mi.minv_cod_clpv,  minv_cm1_minv, minv_cm2_minv, minv_usu_minv, dmov_cod_lote, 
								dmov_cad_lote, dmov_ela_lote,
								mi.minv_fac_prov, mi.minv_cod_tran,
								( select tran_des_tran  from saetran where 
									tran_cod_tran = mi.minv_cod_tran and
									tran_cod_empr = $id_empresa AND
									tran_cod_modu = 10 ) as tran,
								mi.minv_num_sec, mi.minv_tot_minv, mi.minv_num_comp,
								dmov_cod_prod, dmov_cod_bode , dmov_can_dmov , dmov_cun_dmov, dmov_cod_unid,
								( select prod_cod_barra from saeprod where
									prod_cod_empr = $id_empresa and
									prod_cod_sucu = $id_sucursal and
									prod_cod_prod = dmov_cod_prod limit 1) as prod_cod_barra,
								( select prod_nom_prod from saeprod where
									prod_cod_empr = $id_empresa and
									prod_cod_sucu = $id_sucursal and
									prod_cod_prod = dmov_cod_prod limit 1 ) as dmov_nom_prod,
								( select bode_nom_bode from saebode where
									bode_cod_empr = $id_empresa and
									bode_cod_bode = dmov_cod_bode ) as 	 dmov_nom_bode,
								( select unid_nom_unid from saeunid where
									unid_cod_empr = $id_empresa and
									unid_cod_unid = dmov_cod_unid ) as dmov_nom_unid,
								( select clpv_nom_clpv from saeclpv where
									clpv_cod_empr = $id_empresa and
									clpv_cod_clpv = minv_cod_clpv ) as clpv_nom	,
								(  SELECT BODE_NOM_BODE FROM SAEBODE WHERE
									BODE_COD_EMPR = $id_empresa AND
									BODE_COD_BODE = DMOV_BOD_ENVI ) AS BOD_DES
								from saeminv mi, saedmov where
								minv_num_comp = dmov_num_comp and
								mi.minv_cod_empr = $id_empresa and
								mi.minv_cod_sucu = $id_sucursal and
								minv_num_comp    = $serial_minv ";
						//echo $sql_des;

						$sHtml_reporte_deta = '';

						$sHtml_reporte_deta .= '<fieldset style="border:#999999 1px solid; padding:2px; text-align:center; width:98%; background-color:#FFFFFF ">';
						$sHtml_reporte_deta .=  '<table align="center" border="0" cellpadding="2" cellspacing="1" width="99%" class="footable">';
						//   $sHtml_reporte_deta .=  $sql_des;    
						if ($oCnx->Query($sql_des)) {
							$sHtml_reporte_deta .=  '<tr>
							<td class="fecha_balance" scope="row" colspan="11">N.- ' . $oCnx->f('tran') . ' ' . $oCnx->f('minv_num_sec') . ' | ' . $oCnx->f('minv_num_comp') . '</td>
					  </tr>';
							$sHtml_reporte_deta .=  '<tr>
							<td class="fecha_balance" scope="row" align="left">PROVEEDOR:</td>
							<td class="fecha_balance" colspan="3" align="left">' . $oCnx->f('clpv_nom') . '</td>
							<td class="fecha_balance" colspan="8" align="left">FECHA: ' . ($oCnx->f('minv_fmov')) . '</td>
					  </tr>';
							$sHtml_reporte_deta .=  '<tr>
							<td class="fecha_balance" scope="row" align="left">OBSERVACION:</td>
							<td colspan="11" align="left"> ' . $oCnx->f('minv_cm1_minv') . ' - ' . $oCnx->f('minv_cm2_minv') . '</td>
					  </tr>';
							$sHtml_reporte_deta .=  '<tr>
							<td class="fecha_balance" scope="row" align="left">USUARIO:</td>
							<td class="fecha_balance" colspan="3" align="left">' . $oCnx->f('minv_usu_minv') . '</td>
							<td class="fecha_balance" colspan="8" align="left">FACTURA: ' . ($oCnx->f('minv_fac_prov')) . '</td>
					  </tr>';
							$sHtml_reporte_deta .=  '<tr>
							<td colspan="19"></td>
					  </tr>';
							$sHtml_reporte_deta .=  '<tr height="25">
							<th class="diagrama">N.-     </th>
							<th class="diagrama">CODIGO BARRA </th>
							<th class="diagrama">CODIGO  </th>
							<th class="diagrama">PRODUCTO</th>
							<th class="diagrama">LOTE/SERIE</th>
							<th class="diagrama">FECHA ELABO.</th>
							<th class="diagrama">FECHA CADUC.</th>
							<th class="diagrama">UNID. MEDI.</th>
							<th class="diagrama">ESTILO</th>
							<th class="diagrama">BODEGA  ORIGEN</th>                                                                               
							<th class="diagrama">BODEGA  DESTINO</th>                                                                               
							<th class="diagrama">CANTIDAD</th>
							
					  </tr>';
							$total = 0;
							$i 	   = 1;
							if ($oCnx->NumFilas() > 0) {
								do {
									$prod_cod = $oCnx->f('dmov_cod_prod');
									$sql = "select prod_cod_estilo from saeprod where prod_cod_prod = '$prod_cod' and prod_cod_empr = $id_empresa and prod_cod_sucu = $id_sucursal ";
									$estilo 	= consulta_string($sql, 'prod_cod_estilo', $oIfx, '');



									$dmov_cod_prod = $oCnx->f('dmov_cod_prod');
									$dmov_cod_bode = $oCnx->f('dmov_cod_bode');

									$sql = "SELECT prod_nom_prod, prbo_uco_prod, prbo_cod_unid, unid_nom_unid
													from saeprod, saeprbo, saeunid 
													where 
													prbo_cod_prod = prod_cod_prod and 
													prbo_cod_unid = unid_cod_unid and 
													prod_cod_prod = '$dmov_cod_prod' and
													prbo_cod_bode = $dmov_cod_bode
													";
									$prod_unid = consulta_string_func($sql, 'unid_nom_unid', $oIfx, '');


									$sHtml_reporte_deta .=  '<tr>';
									$sHtml_reporte_deta .=  '<td align="right">' . $i . '</td>';
									$sHtml_reporte_deta .=  '<td align="left" style="mso-number-format:\@;" >' . $oCnx->f('prod_cod_barra') . '</td>';
									$sHtml_reporte_deta .=  '<td align="left" style="mso-number-format:\@;" >' . $oCnx->f('dmov_cod_prod') . '</td>';
									$sHtml_reporte_deta .=  '<td align="left" >' . htmlentities($oCnx->f('dmov_nom_prod')) . '</td>';

									$sHtml_reporte_deta .=  '<td align="left" style="mso-number-format:\@;" >' . $oCnx->f('dmov_cod_lote') . '</td>';
									$sHtml_reporte_deta .=  '<td align="left" >' . $oCnx->f('dmov_ela_lote') . '</td>';
									$sHtml_reporte_deta .=  '<td align="left" >' . $oCnx->f('dmov_cad_lote') . '</td>';

									$sHtml_reporte_deta .=  '<td align="left" >' . htmlentities($prod_unid) . '</td>';
									$sHtml_reporte_deta .=  '<td align="left" >' . $estilo . '</td>';
									$sHtml_reporte_deta .=  '<td align="left" >' . $oCnx->f('dmov_nom_bode') . '</td>';
									$sHtml_reporte_deta .=  '<td align="left" >' . $oCnx->f('bod_des') . '</td>';
									$sHtml_reporte_deta .=  '<td align="right">' . $oCnx->f('dmov_can_dmov') . '</td>';

									$sHtml_reporte_deta .=  '</tr>';

									$total    += $oCnx->f('dmov_can_dmov');
									$i++;
								} while ($oCnx->SiguienteRegistro());
								$sHtml_reporte_deta .=  '<tr>';
								$sHtml_reporte_deta .=  '<td align="left"></td>';
								$sHtml_reporte_deta .=  '<td align="left"></td>';
								$sHtml_reporte_deta .=  '<td align="left"></td>';
								$sHtml_reporte_deta .=  '<td align="left"></td>';
								$sHtml_reporte_deta .=  '<td align="left"></td>';
								$sHtml_reporte_deta .=  '<td align="left"></td>';
								$sHtml_reporte_deta .=  '<td align="left"></td>';
								$sHtml_reporte_deta .=  '<td align="left"></td>';
								$sHtml_reporte_deta .=  '<td align="left"></td>';
								$sHtml_reporte_deta .=  '<td align="left"></td>';
								$sHtml_reporte_deta .=  '<td align="right" class="fecha_letra">TOTAL:</td>';
								$sHtml_reporte_deta .=  '<td align="right" class="fecha_letra">' . round($total, 6) . '</td>';
								$sHtml_reporte_deta .=  '</tr>';
							} else {
								$sHtml_reporte_deta .=  'Sin Productos...';
							}
						}
						$oCnx->Free();
						$sHtml_reporte_deta .=  '</table>';
						$sHtml_reporte_deta .=  '<table style="width:90%; margin-left:100px; margin-top: 100px" align="center" >
						<tr>
							<td style="width:30%; font-size:12px; text-align: center;border-top : 2px solid black;">Elaborado por:<br>' . $usuario_nombre . '<br><br><br><br><br><br></td>
							<td style="width:20%;"></td>
							<td style="width:30%;font-size:12px; text-align: center;border-top : 2px solid black;">Despachado por:<br><br><br><br><br><br></td>
							<td style="width:20%;"></td>					
						</tr>
						<tr>
						<td style="width:30%; font-size:12px; text-align: center;"></td>
							<td style="width:20%;"></td>
							<td style="width:30%;font-size:12px; text-align: center;"></td>
							<td style="width:20%;"></td>	

						</tr>
						
						<tr>
							<td style="width:30%; font-size:12px; text-align: center;border-top : 2px solid black;">Autorizado por:<br></td>
							<td style="width:20%;"></td>
							<td style="width:30%;font-size:12px; text-align: center;border-top : 2px solid black;">Recibido por:</td>
							<td style="width:20%;"></td>					
						</tr>
						</table>';


						echo ($sHtml_reporte_deta);

						unset($_SESSION['reporte_excel_detalle']);
						$_SESSION['reporte_excel_detalle'] = $sHtml_reporte_deta;


						?> </td>
				</tr>
				<tr>
					<td colspan="4" class="Estilo2" align="left">&nbsp;</td>
				</tr>

				<tr>
					<td colspan="4" class="Estilo2" align="left">&nbsp;</td>
				</tr>

			</table>

		</div>


		<div id="dos">

			<table width="464" border="0" align="center">
				<tr>
					<td align="center">
						<label>
							<input name="Submit2" type="submit" class="Estilo2" value="Imprimir" onclick="formato();" />
						</label>
					</td>
					<td align="center">
						<label>
							<input name="Submit2" type="submit" class="Estilo2" value="Excel" onclick="formato_excel();" />
						</label>
					</td>
				</tr>
			</table>


		<?

	} else {

		$sHtml_reporte_deta .=  '<div align="center" class="Estilo1">ERROR!!!! AUN NO INGRESA ORDEN COMPRA.... </div>';
	}

		?>

		</div>

</body>

</html>