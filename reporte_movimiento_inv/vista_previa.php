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
	<title>MOVIMIENTO DE INVENTARIO</title>
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
	</script>
</head>

<body>

	<?
	$oCnx = new Dbo();
	$oCnx->DSN = $DSN_Ifx;
	$oCnx->Conectar();

	$oCnxA = new Dbo();
	$oCnxA->DSN = $DSN_Ifx;
	$oCnxA->Conectar();

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$serial_minv = $_GET['codigo'];
	$id_empresa  = $_GET['empr'];
	$id_sucursal = $_GET['sucu'];

	//LOGO PARA EL REPORTE
	if (empty($id_empresa)) {

		$id_empresa = $_SESSION['U_EMPRESA'];
	}

	$sql = "select empr_web_color, empr_path_logo,empr_img_rep from saeempr where empr_cod_empr =  $id_empresa ";


	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			$empr_path_logo = $oIfx->f('empr_img_rep');
			$empr_color = $oIfx->f('empr_web_color');
		}
	}
	$oIfx->Free();

	$path_img = explode("/", $empr_path_logo);
	$count = count($path_img) - 1;
	$arc_img = '../../Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];



	if (file_exists($arc_img)) {
		$imagen = $arc_img;
	} else {
		$imagen = '';
	}
	$logo = '';
	$x = '0px';
	if ($imagen != '') {

		$empr_logo = '<div>
        <img src="' . $imagen . '" style="
        width:170px;
        object-fit; contain;">
        </div>';
		$x = '0px';
	}
	// width:330px;
	// USAUURO
	$sql 		= "select empr_nom_empr from saeempr where empr_cod_empr = $id_empresa ";
	$empr_nom 	= consulta_string($sql, 'empr_nom_empr', $oIfx, '');

	$sqlBode = "select bode_nom_bode, bode_cod_bode
                from saebode
                where bode_cod_empr = $id_empresa";
	if ($oIfx->Query($sqlBode)) {
		if ($oIfx->NumFilas() > 0) {
			unset($arrayBode);
			do {
				$arrayBode[$oIfx->f('bode_cod_bode')] = $oIfx->f('bode_nom_bode');
			} while ($oIfx->SiguienteRegistro());
		}
	}
	$oIfx->Free();

	if ($serial_minv > 0) {
	?>

		<div id="uno">

			<table width="98%" height="95%" border="0" align="center">
				<tr>
					<td colspan="4" align="left"><? echo $empr_logo; ?></td>
				</tr>
				<tr>
					<td align="center" height="5">&nbsp;</td>
					<td height="20" colspan="1">
						<div align="left" class="fecha_balance"><? echo $empr_nom; ?></div>
					</td>
				</tr>
				<tr>
					<td colspan="4" class="Estilo2" align="left" width="90%">

						<?
						$sql_des = "select dmov_cod_pedi,dmov_det_dmov, dmov_cod_ccos, mi.minv_fmov,     mi.minv_cod_clpv,  minv_cm1_minv, minv_cm2_minv, mi.minv_usu_minv, mi.minv_cod_usua,
								mi.minv_user_web,
								mi.minv_fac_prov, mi.minv_cod_tran, dmov_cod_bode,
								( select tran_des_tran  from saetran where 
									tran_cod_tran = mi.minv_cod_tran and
									tran_cod_empr = mi.minv_cod_empr AND
									tran_cod_modu = 10 ) as tran,
								mi.minv_num_sec, mi.minv_tot_minv, mi.minv_num_comp,
								dmov_cod_prod, dmov_cod_bode , dmov_can_dmov , dmov_cun_dmov, dmov_cod_unid,
								( select prod_nom_prod from saeprod where
									prod_cod_empr = mi.minv_cod_empr and
									prod_cod_sucu = mi.minv_cod_sucu and
									prod_cod_prod = dmov_cod_prod limit 1 ) as dmov_nom_prod,
								( select bode_nom_bode from saebode where
									bode_cod_empr = mi.minv_cod_empr and
									bode_cod_bode = dmov_cod_bode ) as 	 dmov_nom_bode,
								( select unid_nom_unid from saeunid where
									unid_cod_empr = mi.minv_cod_empr and
									unid_cod_unid = dmov_cod_unid ) as dmov_nom_unid,
								( select clpv_nom_clpv from saeclpv where
									clpv_cod_empr = mi.minv_cod_empr and
									clpv_cod_clpv = minv_cod_clpv ) as clpv_nom	, dmov_bod_envi, dmov_cod_lote
								from saeminv mi, saedmov where
								minv_num_comp = dmov_num_comp and
								mi.minv_cod_empr = mi.minv_cod_empr and
								mi.minv_cod_sucu = mi.minv_cod_sucu and
								minv_num_comp    = $serial_minv ";
						//echo $sql_des;
						echo '<fieldset style="border:#999999 1px solid; padding:2px; text-align:center; width:98%; background-color:#FFFFFF ">';
						echo '<table align="center" border="0" cellpadding="2" cellspacing="1" width="99%" class="footable">';
						//echo $sql_des;    
						if ($oCnx->Query($sql_des)) {

							$codpedi = intval($oCnx->f('dmov_cod_pedi'));
							if (!empty($oCnx->f('minv_usu_minv'))) {
								$usuario_nombre = $oCnx->f('minv_usu_minv');
							} else {

								$minv_user_web = $oCnx->f('minv_user_web');
								if (empty($minv_user_web)) {
									$minv_user_web = 0;
								}

								$minv_cod_usua = $oCnx->f('minv_cod_usua');
								if (empty($minv_cod_usua)) {
									$minv_cod_usua = 0;
								}

								$sql_usuario = "SELECT (usuario_nombre || ' ' || usuario_apellido) AS nombres from comercial.usuario where usuario_id= " . $minv_user_web;
								$usuario_nombre = consulta_string($sql_usuario, 'nombres', $oCnxA, '');
								if (empty($usuario_nombre)) {
									$sql_usuario = "SELECT (usuario_nombre || ' ' || usuario_apellido) AS nombres from comercial.usuario where usua_cod_usua = " . $minv_cod_usua;
									$usuario_nombre = consulta_string($sql_usuario, 'nombres', $oCnxA, '');
								}
							}

							$dmov_det_dmov = $oCnx->f('dmov_det_dmov');

							echo '<tr>
							<td class="fecha_balance" scope="row" colspan="12">N.- ' . $oCnx->f('tran') . ' ' . $oCnx->f('minv_num_sec') . ' | ' . $oCnx->f('minv_num_comp') . '</td>
					  			</tr>';
							echo '<tr>
							<td class="fecha_balance" scope="row" align="left">PROVEEDOR:</td>
							<td class="fecha_balance" colspan="6" align="left">' . $oCnx->f('clpv_nom') . '</td>
							<td class="fecha_balance" colspan="5" align="left">FECHA: ' . $oCnx->f('minv_fmov') . '</td>
					  			</tr>';
							echo '<tr>			
							<td class="fecha_balance" scope="row" align="left">OBSERVACION:</td>
							<td colspan="11" align="left"> ' . $oCnx->f('minv_cm1_minv') . ' - ' . $oCnx->f('minv_cm2_minv') . '</td>
					  			</tr>';
							echo '<tr>
							<td class="fecha_balance" scope="row" align="left">USUARIO:</td>
							<td class="fecha_balance" colspan="6" align="left">' . $usuario_nombre . '</td>
							<td class="fecha_balance" colspan="5" align="left">FACTURA: ' . ($oCnx->f('minv_fac_prov')) . '</td>
					  			</tr>';
							echo '<tr>
							<td colspan="12"></td>
					  			</tr>';
//<th class="diagrama">N.-</th>
							echo '<tr height="25">
							<!-- <th class="diagrama">N.-</th> -->
							<th class="diagrama">CODIGO  </th>
							<th class="diagrama">PRODUCTO</th>
							<!-- <th class="diagrama">NUMERO LOTE</th> -->
							<th class="diagrama">UNID. MEDI.</th>
							<th class="diagrama">DETALLE</th>
							<th class="diagrama">C.COSTOS</th>
							<th class="diagrama">BODEGA ORIGEN </th>                                                                               
							<th class="diagrama">BODEGA DESTINO </th>  
							<th class="diagrama">CANTIDAD</th>
							<th class="diagrama">COSTO   </th>
							<th class="diagrama">TOTAL   </th>
					  		</tr>';
							$total = 0;
							$i 	   = 1;
							if ($oCnx->NumFilas() > 0) {
								do {
									$bode_envi = $arrayBode[$oCnx->f('dmov_bod_envi')];

									if (!empty($codpedi)) {
										$sql = "select dped_cod_ccos from saedped where dped_cod_pedi=$codpedi and dped_cod_ccos is not null";
										$ccos = consulta_string($sql, 'dped_cod_ccos', $oCnxA, 0);

										$sqlc = "select ccosn_nom_ccosn from saeccosn where ccosn_cod_ccosn='$ccos'";
										$proyecto = consulta_string($sqlc, 'ccosn_nom_ccosn', $oCnxA, '');
									} else {

										$ccos = $oCnx->f('dmov_cod_ccos');

										$sqlc = "select ccosn_nom_ccosn from saeccosn where ccosn_cod_ccosn='$ccos'";

										$proyecto = consulta_string($sqlc, 'ccosn_nom_ccosn', $oCnxA, '');
									}

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


									$bodega_destino = $oCnx->f('dmov_bod_envi');
									if (empty($bodega_destino)) {
										$bodega_destino = 0;
									}
									$sql_nombre_sucu_dest = "SELECT sucu_nom_sucu from saesucu where sucu_cod_sucu in (select subo_cod_sucu FROM saesubo where subo_cod_bode = $bodega_destino) limit 1";
									$sucursal_destino = consulta_string($sql_nombre_sucu_dest, 'sucu_nom_sucu', $oIfx, '');


									$bodega_origen = $oCnx->f('dmov_cod_bode');
									$sql_nombre_sucu_orig = "SELECT sucu_nom_sucu from saesucu where sucu_cod_sucu in (select subo_cod_sucu FROM saesubo where subo_cod_bode = $bodega_origen) limit 1";
									$sucursal_origen = consulta_string($sql_nombre_sucu_orig, 'sucu_nom_sucu', $oIfx, '');

									echo '<tr>';
									//echo '<td align="center">' . $i . '</td>';
									echo '<td align="center" >' . $oCnx->f('dmov_cod_prod') . '</td>';
									echo '<td align="left" >' . htmlentities($oCnx->f('dmov_nom_prod')) . '</td>';
									//echo '<td align="left" ></td>';
									echo '<td align="center" >' . htmlentities($prod_unid) . '</td>';
									echo '<td align="left" >' . htmlentities($oCnx->f('dmov_det_dmov')) . '</td>';
									echo '<td align="left" >' . htmlentities($proyecto) . '</td>';
									echo '<td align="left" >(' . $sucursal_origen . ') ' . $oCnx->f('dmov_nom_bode') . '</td>';
									echo '<td align="left" >(' . $sucursal_destino . ') ' . $bode_envi . '</td>';
									echo '<td align="right">' . $oCnx->f('dmov_can_dmov') . '</td>';
									echo '<td align="right">' . round($oCnx->f('dmov_cun_dmov'), 2) . '</td>';
									echo '<td align="right">' . round(($oCnx->f('dmov_can_dmov') * $oCnx->f('dmov_cun_dmov')), 2) . '</td>';
									echo '</tr>';
// . htmlentities($oCnx->f('dmov_nom_prod')) . 
									$total    += ($oCnx->f('dmov_can_dmov') * $oCnx->f('dmov_cun_dmov'));
									$i++;
								} while ($oCnx->SiguienteRegistro());
								echo '<tr>';
								//echo '<td align="left"></td>';
								echo '<td align="left"></td>';
								echo '<td align="left"></td>';
								//echo '<td align="left"></td>';
								echo '<td align="left"></td>';
								echo '<td align="left"></td>';
								echo '<td align="left"></td>';
								echo '<td align="left"></td>';
								echo '<td align="left"></td>';
								echo '<td align="left"></td>';
								echo '<td align="right" class="fecha_letra">TOTAL:</td>';
								echo '<td align="right" class="fecha_letra">$ ' . round($total, 2) . '</td>';
								echo '</tr>';
							} else {
								echo 'Sin Productos...';
							}
						}
						$oCnx->Free();
						echo '</table>';

						echo '<table style="width:90%; margin-left:100px; margin-top: 100px" align="center" >
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
					<td align="center"><label>
							<input name="Submit2" type="submit" class="Estilo2" value="Imprimir" onclick="formato();" />
						</label></td>
				</tr>

			</table>
		<?

	} else {

		echo '<div align="center" class="Estilo1">ERROR!!!! AUN NO INGRESA ORDEN COMPRA.... </div>';
	}

		?>

		</div>
</body>

</html>