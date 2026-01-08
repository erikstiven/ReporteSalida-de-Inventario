<?
require_once('codigo_de_barras/barcode.inc.php');
set_time_limit(3000);
ini_set('memory_limit', '20000M');
require_once 'html2pdf_v4.03/html2pdf.class.php';


function kardex_inv_func($idempresa = "", $idsucursal = "", $bode_cod = '', $prod_cod = '',  $prod_cod2 = '', $fecha_ini = '', $fecha_fin = '', $linea = '', $grupo = '', $cate = '', $marca = '')
{
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

	$oReturn = new xajaxResponse();

	$cost_visual        = $_SESSION['U_COSTO_MIRROR'];

	$sql = "select tran_cod_tran, tran_des_tran from saetran where tran_cod_empr = $idempresa ";
	unset($array_tran);
	$array_tran = array_dato($oIfx, $sql, 'tran_cod_tran', 'tran_des_tran');

	try {

		//

		$tabla_det = '';
		$tabla_det .= '<table class="table table-bordered table-hover table-striped table-condensed" style="width: 100%;">';

		$sql = "delete from tmp_kardex_web ";
		$oIfx->QueryT($sql);

		$sql_sp = "SELECT * from sp_kardex_inv_web( $idempresa, $idsucursal, $bode_cod, $linea, $grupo, $cate, $marca, '$fecha_ini', '$fecha_fin', '$prod_cod', '$prod_cod2', '2' )";

		$oIfx->Query($sql_sp);

		$cant_saldo = 0;
		unset($array);
		unset($array_prod);
		$i = 1;
		$total_cant_ingr = 0;
		$total_cant_egre = 0;
		$total_cost_ingr = 0;
		$total_cost_egre = 0;

		$sql_sp = "select * from tmp_kardex_web where
						empr_cod_empr = $idempresa and
						sucu_cod_sucu = $idsucursal and
						fecha_ini	  = '$fecha_ini' and
						fecha_fin     = '$fecha_fin' 	";




		if ($oIfx->Query($sql_sp)) {
			if ($oIfx->NumFilas() > 0) {
				do {
					$prod_cod  = $oIfx->f('prod_cod_prod');
					$fecha_mov = fecha_mysql_func($oIfx->f('minv_fmov'));
					$secu      = $oIfx->f('factura');
					$tipo      = $oIfx->f('tipo');
					$tran_nom  = $array_tran[$oIfx->f('tran_cod')];
					$tran_cod  = $oIfx->f('tran_cod');
					$cant      = $oIfx->f('cantidad');
					$costo     = $oIfx->f('costo');
					$prove     = vacios($oIfx->f('clpv_nom'), '_');
					$deta      = vacios($oIfx->f('detalle'), '_');
					$minv_num_comp  = $oIfx->f('minv_num_comp');
					$array_prod[$i] = $prod_cod;

					if ($cost_visual == 'N') {
						$costo = 0;
					}

					$cant_ingr = 0;
					$costo_ingr = 0;
					$cant_egre = 0;
					$costo_egre = 0;

					$classColor = '';
					if ($tipo == 0) {
						// INGRESO
						$classColor = 'bg-success';
						$cant_ingr   = $cant;
						$costo_ingr  = $costo;
						$cant_saldo += $cant_ingr;
					} else {
						// EGRESO
						$cant_egre   = $cant;
						$costo_egre  = $costo;
						$cant_saldo -= abs($cant_egre);
					}

					if ($i > 1) {
						$array[$i] = $array[$i - 1] +  abs($cant_ingr * $costo_ingr) - abs($cant_egre * $costo_egre);
					} else {
						$array[$i] = abs($cant_ingr * $costo_ingr) - abs($cant_egre * $costo_egre);
					}

					// COSTO PROMEDO
					$costo_prom = 0;
					if ($cant_saldo > 0) {
						$costo_prom = round(($array[$i] / $cant_saldo), 6);
					}

					if ($i > 1) {
						if ($array_prod[$i] == $array_prod[$i - 1]) {
						} elseif ($array_prod[$i] != $array_prod[$i - 1]) {
							$tabla_det .= '<tr>';
							$tabla_det .= '<td colspan="13">';

							$sql = "select bode_nom_bode, prod_cod_prod,prod_nom_prod, prod_des_prod,   medi_des_medi, 
											prbo_smi_prod, prbo_sma_prod, prbo_ped_prod, prbo_dis_prod,
											prbo_fec_ucom, prbo_pco_prod, prbo_fec_uven, prbo_pve_prod, unid_sigl_unid, prbo_uco_prod
											from saeprod, saeprbo, saebode, saemedi, saeunid
											where prod_cod_prod = prbo_cod_prod
											and prod_cod_empr = prbo_cod_empr
											and prod_cod_sucu = prbo_cod_sucu
											and prbo_cod_bode = bode_cod_bode
											and prbo_cod_empr = bode_cod_empr
											and prod_cod_medi = medi_cod_medi
											and prod_cod_empr = medi_cod_empr
											and prbo_cod_unid = unid_cod_unid
											and prbo_cod_empr = unid_cod_empr
											and prod_cod_empr = $idempresa
											and prod_cod_sucu = $idsucursal
											and prbo_cod_bode = $bode_cod
											and prod_cod_prod = '$prod_cod' ";
							if ($oIfxA->Query($sql)) {
								if ($oIfxA->NumFilas() > 0) {
									$bode_nom = $oIfxA->f('bode_nom_bode');
									$prod_nom = $oIfxA->f('prod_nom_prod');
									$min      = $oIfxA->f('prbo_smi_prod');
									$max      = $oIfxA->f('prbo_sma_prod');
									$pedido   = $oIfxA->f('prbo_ped_prod');
									$stock    = $oIfxA->f('prbo_dis_prod');
									$ult_comp = $oIfxA->f('prbo_fec_ucom');
									$cost_uco = $oIfxA->f('prbo_pco_prod');
									$unidad   = $oIfxA->f('unid_sigl_unid');
									$ult_costo = $oIfxA->f('prbo_uco_prod');

									$tabla_det .= '<table class="table table-bordered table-striped table-condensed" style="margin: 0px;">';
									$tabla_det .= '<tr>';
									$tabla_det .= '<td class="bg-info" align="right">BODEGA:</td>';
									$tabla_det .= '<td class="fecha_letra" align="right">' . $bode_nom . '</td>';
									$tabla_det .= '<td class="bg-info" align="right">CODIGO:</td>';
									$tabla_det .= '<td class="fecha_letra" align="right">' . $prod_cod . '</td>';
									$tabla_det .= '<td class="bg-info" align="right">PRODUCTO:</td>';
									$tabla_det .= '<td class="fecha_letra" align="right" colspan="2">' . $prod_nom . '</td>';
									$tabla_det .= '</tr>';

									$tabla_det .= '<tr>';
									$tabla_det .= '<td class="bg-info" align="right">EX. MINIMO:</td>';
									$tabla_det .= '<td class="fecha_letra" align="right">' . $min . '</td>';
									$tabla_det .= '<td class="bg-info" align="right">EX. MAXIMO:</td>';
									$tabla_det .= '<td class="fecha_letra" align="right">' . $max . '</td>';
									$tabla_det .= '<td class="bg-info" align="right">PEDIDO:</td>';
									$tabla_det .= '<td class="fecha_letra" align="right">' . $pedido . '</td>';
									$tabla_det .= '<td class="bg-info" align="right">EXISTENCIA:</td>';
									$tabla_det .= '<td class="fecha_letra" align="right">' . $stock . '</td>';
									$tabla_det .= '</tr>';

									$tabla_det .= '<tr>';
									$tabla_det .= '<td class="bg-info" align="right">ULT. COMPRA:</td>';
									$tabla_det .= '<td class="fecha_letra" align="right">' . $ult_comp . '</td>';
									$tabla_det .= '<td class="bg-info" align="right">COSTO:</td>';
									$tabla_det .= '<td class="fecha_letra" align="right">' . $cost_uco . '</td>';
									$tabla_det .= '<td class="bg-info" align="right">UNIDAD:</td>';
									$tabla_det .= '<td class="fecha_letra" align="right">' . $unidad . '</td>';
									$tabla_det .= '<td class="bg-info" align="right">ULTIMO COSTO:</td>';
									$tabla_det .= '<td class="fecha_letra" align="right">' . $ult_costo . '</td>';
									$tabla_det .= '</tr>';
									$tabla_det .= '</table>';
								}
							}

							$tabla_det .= '</td>';
							$tabla_det .= '</tr>';
						}
					} else {
						$tabla_det .= '<tr>';
						$tabla_det .= '<td colspan="13">';

						$sql = "select bode_nom_bode, prod_cod_prod,prod_nom_prod, prod_des_prod,   medi_des_medi, 
										prbo_smi_prod, prbo_sma_prod, prbo_ped_prod, prbo_dis_prod,
										prbo_fec_ucom, prbo_pco_prod, prbo_fec_uven, prbo_pve_prod, unid_sigl_unid, prbo_uco_prod
										from saeprod, saeprbo, saebode, saemedi, saeunid
										where prod_cod_prod = prbo_cod_prod
										and prod_cod_empr = prbo_cod_empr
										and prod_cod_sucu = prbo_cod_sucu
										and prbo_cod_bode = bode_cod_bode
										and prbo_cod_empr = bode_cod_empr
										and prod_cod_medi = medi_cod_medi
										and prod_cod_empr = medi_cod_empr
										and prbo_cod_unid = unid_cod_unid
										and prbo_cod_empr = unid_cod_empr
										and prod_cod_empr = $idempresa
										and prod_cod_sucu = $idsucursal
										and prbo_cod_bode = $bode_cod
										and prod_cod_prod = '$prod_cod' ";
						//$oReturn->alert($sql);
						if ($oIfxA->Query($sql)) {
							if ($oIfxA->NumFilas() > 0) {
								$bode_nom = $oIfxA->f('bode_nom_bode');
								$prod_nom = $oIfxA->f('prod_nom_prod');
								$min      = $oIfxA->f('prbo_smi_prod');
								$max      = $oIfxA->f('prbo_sma_prod');
								$pedido   = $oIfxA->f('prbo_ped_prod');
								$stock    = $oIfxA->f('prbo_dis_prod');
								$ult_comp = $oIfxA->f('prbo_fec_ucom');
								$cost_uco = $oIfxA->f('prbo_pco_prod');
								$unidad   = $oIfxA->f('unid_sigl_unid');
								$ult_costo = $oIfxA->f('prbo_uco_prod');

								$tabla_det .= '<table class="table table-bordered table-striped table-condensed" style="margin: 0px;">';
								$tabla_det .= '<tr>';
								$tabla_det .= '<td class="bg-info" align="right">BODEGA:</td>';
								$tabla_det .= '<td class="fecha_letra" align="right">' . $bode_nom . '</td>';
								$tabla_det .= '<td class="bg-info" align="right">CODIGO:</td>';
								$tabla_det .= '<td class="fecha_letra" align="right">' . $prod_cod . '</td>';
								$tabla_det .= '<td class="bg-info" align="right">PRODUCTO:</td>';
								$tabla_det .= '<td class="fecha_letra" align="right" colspan="2">' . $prod_nom . '</td>';
								$tabla_det .= '</tr>';

								$tabla_det .= '<tr>';
								$tabla_det .= '<td class="bg-info" align="right">EX. MINIMO:</td>';
								$tabla_det .= '<td class="fecha_letra" align="right">' . $min . '</td>';
								$tabla_det .= '<td class="bg-info" align="right">EX. MAXIMO:</td>';
								$tabla_det .= '<td class="fecha_letra" align="right">' . $max . '</td>';
								$tabla_det .= '<td class="bg-info" align="right">PEDIDO:</td>';
								$tabla_det .= '<td class="fecha_letra" align="right">' . $pedido . '</td>';
								$tabla_det .= '<td class="bg-info" align="right">EXISTENCIA:</td>';
								$tabla_det .= '<td class="fecha_letra" align="right">' . $stock . '</td>';
								$tabla_det .= '</tr>';

								$tabla_det .= '<tr>';
								$tabla_det .= '<td class="bg-info" align="right">ULT. COMPRA:</td>';
								$tabla_det .= '<td class="fecha_letra" align="right">' . $ult_comp . '</td>';
								$tabla_det .= '<td class="bg-info" align="right">COSTO:</td>';
								$tabla_det .= '<td class="fecha_letra" align="right">' . $cost_uco . '</td>';
								$tabla_det .= '<td class="bg-info" align="right">UNIDAD:</td>';
								$tabla_det .= '<td class="fecha_letra" align="right">' . $unidad . '</td>';
								$tabla_det .= '<td class="bg-info" align="right">ULTIMO COSTO:</td>';
								$tabla_det .= '<td class="fecha_letra" align="right">' . $ult_costo . '</td>';
								$tabla_det .= '</tr>';


								$tabla_det .= '</table>';
							}
						}

						$tabla_det .= '</td>';
						$tabla_det .= '</tr>';

						$tabla_det .= '<tr>
				                        <td colspan="4"></td>
				                        <td colspan="3" class="bg-danger fecha_letra" align="center">Entrada</td>
				                        <td colspan="3" class="bg-danger fecha_letra" align="center">Salida</td>
				                        <td colspan="3" class="bg-danger fecha_letra" align="center">Saldos</td>                        
					                    </tr>';
						$tabla_det .= '<tr>
				                        <td class="bg-info">Fecha</td>
				                        <td class="bg-info">Tipo</td>
				                        <td class="bg-info">Documento</td>  
				                        <td class="bg-info">Cliente/Suplidor</td>                      
				                        <td class="bg-info">Cantidad</td>
				                        <td class="bg-info">Costo</td>
				                        <td class="bg-info">Total</td>
				                        <td class="bg-info">Cantidad</td>
				                        <td class="bg-info">Costo</td>
				                        <td class="bg-info">Total</td>
				                        <td class="bg-info">Cantidad</td>
				                        <td class="bg-info">Costo</td>
				                        <td class="bg-info">Total</td>                        
				                    </tr>';
					}


					$tabla_det .= '<tr style="cursor: pointer;" onclick="verDetalleMovimiento(' . $minv_num_comp . ', \'' . $tran_cod . '\')">';
					$tabla_det .= '<td class="' . $classColor . '" align="right">' . $fecha_mov . '</td>               
	                                    <td class="' . $classColor . '">' . $tran_cod . ' - ' . $tran_nom . '</td> 
	                                    <td class="' . $classColor . '">' . $secu . '</td>   
	                                    <td class="' . $classColor . '" align="left">' . $prove . '</td>
	                                    <td class="' . $classColor . '" align="right">' . $cant_ingr . '</td>   
	                                    <td class="' . $classColor . '" align="right">' . $costo_ingr . '</td>   
	                                    <td class="' . $classColor . '" align="right">' . abs($cant_ingr * $costo_ingr) . '</td>
	                                    <td class="' . $classColor . '" align="right">' . $cant_egre . '</td>
	                                    <td class="' . $classColor . '" align="right">' . $costo_egre . '</td>
	                                    <td class="' . $classColor . '" align="right">' . abs($cant_egre * $costo_egre) . '</td>
	                                    <td class="' . $classColor . '" align="right">' . $cant_saldo . '</td>
	                                    <td class="' . $classColor . '" align="right">' . $costo_prom . '</td>
	                                    <td class="' . $classColor . '" align="right">' . $array[$i] . '</td>';
					$tabla_det .= '</tr>';

					$i++;
					$total_cant_ingr += $cant_ingr;
					$total_cant_egre += $cant_egre;
					$total_cost_ingr += abs($cant_ingr * $costo_ingr);
					$total_cost_egre += abs($cant_egre * $costo_egre);
				} while ($oIfx->SiguienteRegistro());

				$tabla_det .= '<tr >';
				$tabla_det .= '<td class="bg-danger fecha_letra" colspan="4">TOTAL:</td> 
                                <td class="bg-danger fecha_letra" align="right">' . $total_cant_ingr . '</td>   
                                <td class="bg-danger fecha_letra" align="right"></td>   
                                <td class="bg-danger fecha_letra" align="right">' . $total_cost_ingr . '</td>
                                <td class="bg-danger fecha_letra" align="right">' . $total_cant_egre . '</td>
                                <td class="bg-danger fecha_letra" align="right"></td>
                                <td class="bg-danger fecha_letra" align="right">' . $total_cost_egre . '</td>
                                <td class="bg-danger fecha_letra" align="right">' . $cant_saldo . '</td>
                                <td class="bg-danger fecha_letra" align="right">' . round($costo_prom, 4) . '</td>
                                <td class="bg-danger fecha_letra" align="right">' . $array[$i - 1] . '</td>';
				$tabla_det .= '</tr>';
			} else {
				$tabla_det .= 'Sin Datos...';
			} // fin if
		} // fin if      

		$tabla_det .= '</table>';
	} catch (Exception $e) {
		// rollback
		$oReturn->alert($e->getMessage());
	}

	return $tabla_det;
}

function verDetalleMovimiento_rd($idempresa = "", $id = "", $tran = '')
{
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

	$oReturn = new xajaxResponse();

	$cost_visual        = $_SESSION['U_COSTO_MIRROR'];

	try {



		$sql = "select minv_fmov, minv_fac_prov, minv_tot_minv,
				minv_usua_web, minv_usu_minv, minv_cod_clpv,
				minv_cod_sucu, minv_comp_cont, minv_cod_ejer, minv_num_sec, minv_user_web
				from saeminv 
				where
				minv_cod_empr = $idempresa and 
				minv_num_comp = $id";
		//$oReturn->alert($sql);
		if ($oIfx->Query($sql)) {
			if ($oIfx->NumFilas() > 0) {
				$minv_fmov = $oIfx->f('minv_fmov');
				$minv_fac_prov = $oIfx->f('minv_fac_prov');
				$minv_tot_minv = $oIfx->f('minv_tot_minv');
				$minv_usua_web = $oIfx->f('minv_usua_web');
				$minv_usu_minv = $oIfx->f('minv_usu_minv');
				$minv_cod_clpv = $oIfx->f('minv_cod_clpv');
				$minv_cod_sucu = $oIfx->f('minv_cod_sucu');
				$minv_comp_cont = $oIfx->f('minv_comp_cont');
				$minv_cod_ejer = $oIfx->f('minv_cod_ejer');
				$minv_num_sec  = $oIfx->f('minv_num_sec');
				$minv_user_web = $oIfx->f('minv_user_web');
			}
		}
		$oIfx->Free();


		if (empty($minv_cod_clpv)) {
			$minv_cod_clpv = 0;
		}

		$userWeb = '';
		if (!empty($minv_usua_web)) {
			$sql = "select concat(usuario_nombre, ' ', usuario_apellido) as user from comercial.usuario where usuario_id = $minv_usua_web";
			$userWeb = consulta_string_func($sql, 'user', $oCon, '');
		} else {
			$sql = "select concat(usuario_nombre, ' ', usuario_apellido) as user from comercial.usuario where usuario_id = $minv_user_web";
			$userWeb = consulta_string_func($sql, 'user', $oCon, '');
		}

		//clpv
		$sql = "select clpv_nom_clpv, clpv_ruc_clpv 
				from saeclpv
				where clpv_cod_empr = $idempresa and
				clpv_cod_clpv = $minv_cod_clpv";
		if ($oIfx->Query($sql)) {
			if ($oIfx->NumFilas() > 0) {
				$clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
				$clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
			}
		}
		$oIfx->Free();




		//defi
		$sql = "select defi_tip_defi from saedefi where defi_cod_empr = $idempresa and defi_cod_tran = '$tran'";
		$defi_tip_defi = consulta_string_func($sql, 'defi_tip_defi', $oIfx, '');

		$img = '';
		if ($defi_tip_defi == 1 && !empty($minv_fac_prov)) {
			$sql = "select fact_cod_fact 
					from saefact 
					where fact_cod_empr = $idempresa and 
					fact_cod_clpv = $minv_cod_clpv and 
					fact_cod_sucu = $minv_cod_sucu and 
					fact_num_preimp = '$minv_fac_prov'";
			$fact_cod_fact = consulta_string_func($sql, 'fact_cod_fact', $oIfx, '');
			$fact_clav_sri = '';

			$img = '<div class="btn btn-primary btn-sm" onclick="genera_documento(1, ' . $fact_cod_fact . ', \'' . $fact_clav_sri . '\', ' . $campo . ', ' . $campo . ', ' . $campo . ', ' . $campo . ', ' . $campo . ', ' . $minv_cod_sucu . ');">
						<span class="glyphicon glyphicon-print"></span>
					</div>';
		} elseif ($defi_tip_defi == 0 && !empty($minv_fac_prov)) {
			$minv_clav_sri = '';
			$img = '<div class="btn btn-primary btn-sm" onclick="genera_documento(6, \'' . $id . '\', \'' . $minv_clav_sri . '\',
												\'' . $minv_cod_clpv . '\' , \'' . $minv_fac_prov . '\', \'' . $minv_cod_ejer . '\',
												\'' . $minv_comp_cont . '\',  \'' . $minv_fmov . '\', ' . $minv_cod_sucu . ');">
						<span class="glyphicon glyphicon-print"></span>
					</div>';
		}

		//transaccion
		$sql = "select tran_des_tran from saetran where tran_cod_tran = '$tran' and tran_cod_empr = $idempresa";
		$tran_des_tran = consulta_string_func($sql, 'tran_des_tran', $oIfx, '');

		$sHtml  = '<table class="table table-striped table-condensed" style="width: 99%; margin-bottom: 0px;" align="center">';
		$sHtml .= '</tr>';
		$sHtml .= '<td colspan="4" class="bg-primary">MOVIMIENTO DE INVENTARIO ' . $tran . ' - ' . $tran_des_tran . '</td>';
		$sHtml .= '</tr>';
		$sHtml .= '<tr>';
		$sHtml .= '<td>Codigo:</td>';
		$sHtml .= '<td>' . $id . '</td>';
		$sHtml .= '<td>Fecha:</td>';
		$sHtml .= '<td>' . $minv_fmov . '</td>';
		$sHtml .= '</tr>';
		$sHtml .= '<tr>';
		$sHtml .= '<td>Cliente/Suplidor:</td>';
		$sHtml .= '<td>' . $clpv_ruc_clpv . ' - ' . $clpv_nom_clpv . '</td>';
		$sHtml .= '<td>Factura - N.- Movimiento:</td>';
		$sHtml .= '<td>' . $minv_fac_prov . ' - ' . $minv_num_sec . ' ' . $img . '</td>';
		$sHtml .= '</tr>';
		$sHtml .= '<tr>';
		$sHtml .= '<td>Usuario:</td>';
		$sHtml .= '<td>' . $userWeb . '</td>';
		$sHtml .= '<td class="bg-danger fecha_letra">Total:</td>';
		$sHtml .= '<td class="bg-danger fecha_letra" align="right">' . number_format($minv_tot_minv, 2, '.', ',') . '</td>';
		$sHtml .= '</tr>';
		$sHtml .= '</table>';

		//array bodega
		$sql = "select b.bode_cod_bode, b.bode_nom_bode from saebode b where  b.bode_cod_empr = $idempresa ";
		if ($oIfx->Query($sql)) {
			if ($oIfx->NumFilas() > 0) {
				unset($arrayBodega);
				do {
					$arrayBodega[$oIfx->f('bode_cod_bode')] = $oIfx->f('bode_nom_bode');
				} while ($oIfx->SiguienteRegistro());
			}
		}
		$oIfx->Free();

		//array unidad
		$sql = "select unid_cod_unid, unid_nom_unid
				from saeunid";
		if ($oIfx->Query($sql)) {
			if ($oIfx->NumFilas() > 0) {
				unset($arrayUnidad);
				do {
					$arrayUnidad[$oIfx->f('unid_cod_unid')] = $oIfx->f('unid_nom_unid');
				} while ($oIfx->SiguienteRegistro());
			}
		}
		$oIfx->Free();


		$sHtml .= '<table class="table table-striped table-condensed table-bordered table-hover" style="width: 99%; margin-top: 10px;" align="center">';
		$sHtml .= '<tr>';
		$sHtml .= '<td colspan="11" class="bg-primary">DETALLE MOVIMIENTO DE INVENTARIO</td>';
		$sHtml .= '</tr>';
		$sHtml .= '<tr>';
		$sHtml .= '<td class="bg-success fecha_letra">BODEGA ORIGEN</td>';
		$sHtml .= '<td class="bg-success fecha_letra">BODEGA DESTINO</td>';
		$sHtml .= '<td class="bg-success fecha_letra">CODIGO</td>';
		$sHtml .= '<td class="bg-success fecha_letra">PRODUCTO</td>';
		$sHtml .= '<td class="bg-success fecha_letra">UNIDAD</td>';
		$sHtml .= '<td class="bg-success fecha_letra">SERIE</td>';
		$sHtml .= '<td class="bg-success fecha_letra">SERIE TARJETA</td>';
		$sHtml .= '<td class="bg-success fecha_letra">CANTIDAD</td>';
		$sHtml .= '<td class="bg-success fecha_letra">COSTO</td>';
		$sHtml .= '<td class="bg-success fecha_letra">DESCUENTO</td>';
		$sHtml .= '<td class="bg-success fecha_letra">TOTAL</td>';
		$sHtml .= '</tr>';
		//detalle del movimiento
		$sql = "select dmov_cod_dmov, dmov_cod_bode, dmov_bod_envi, 
				dmov_cod_unid, dmov_cod_prod, dmov_ds1_dmov, dmov_cto_dmov,
				dmov_can_dmov, dmov_cun_dmov, dmov_cod_lote, dmov_cad_lote,
				dmov_ela_lote, dmov_bod_envi, dmov_serie_tarj
				from saedmov
				where dmov_cod_empr = $idempresa and
				dmov_num_comp = $id and
				dmov_cod_sucu = $minv_cod_sucu";
		if ($oIfx->Query($sql)) {
			if ($oIfx->NumFilas() > 0) {
				$totalCan = 0;
				$granTotal = 0;
				do {
					$dmov_cod_dmov = $oIfx->f('dmov_cod_dmov');
					$dmov_cod_bode = $oIfx->f('dmov_cod_bode');
					$dmov_bode_envi = $oIfx->f('dmov_bod_envi');
					$dmov_cod_prod = $oIfx->f('dmov_cod_prod');
					$dmov_cod_unid = $oIfx->f('dmov_cod_unid');
					$dmov_ds1_dmov = $oIfx->f('dmov_ds1_dmov');
					$dmov_can_dmov = round($oIfx->f('dmov_can_dmov'), 3);
					$dmov_pun_dmov = round($oIfx->f('dmov_cun_dmov'), 6);
					$dmov_cod_lote = $oIfx->f('dmov_cod_lote');
					$dmov_ela_lote = $oIfx->f('dmov_ela_lote');
					$dmov_cad_lote = $oIfx->f('dmov_cad_lote');
					$dmov_cto_dmov = $oIfx->f('dmov_cto_dmov');
					$dmov_serie_tarj = $oIfx->f('dmov_serie_tarj');

					if ($cost_visual == 'N') {
						$dmov_pun_dmov = 0;
					}

					//producto
					$sql = "select prod_nom_prod from saeprod where prod_cod_empr = $idempresa and prod_cod_sucu = $minv_cod_sucu and prod_cod_prod = '$dmov_cod_prod'";
					$prod_nom_prod = consulta_string_func($sql, 'prod_nom_prod', $oIfxA, '');

					$sHtml .= '<tr>';
					$sHtml .= '<td>' . $arrayBodega[$dmov_cod_bode] . '</td>';
					$sHtml .= '<td>' . $arrayBodega[$dmov_bode_envi] . '</td>';
					$sHtml .= '<td>' . $dmov_cod_prod . '</td>';
					$sHtml .= '<td>' . $prod_nom_prod . '</td>';
					$sHtml .= '<td>' . $arrayUnidad[$dmov_cod_unid] . '</td>';
					$sHtml .= '<td>' . $dmov_cod_lote . '</td>';
					$sHtml .= '<td>' . $dmov_serie_tarj . '</td>';
					$sHtml .= '<td align="right">' . number_format($dmov_can_dmov, 2, '.', ',') . '</td>';
					$sHtml .= '<td align="right">' . number_format($dmov_pun_dmov, 6, '.', ',') . '</td>';
					$sHtml .= '<td align="right">' . number_format($dmov_ds1_dmov, 3, '.', ',') . '</td>';
					$sHtml .= '<td align="right">' . number_format($dmov_cto_dmov, 2, '.', ',') . '</td>';
					$sHtml .= '</tr>';

					$totalCan += $dmov_can_dmov;
					$granTotal += $dmov_cto_dmov;
				} while ($oIfx->SiguienteRegistro());
				$sHtml .= '<tr>';
				$sHtml .= '<td colspan="7" align="right" class="bg-danger fecha_letra">TOTAL:</td>';
				$sHtml .= '<td class="bg-danger fecha_letra" align="right">' . number_format($totalCan, 2, '.', ',') . '</td>';
				$sHtml .= '<td colspan="2" class="bg-danger"></td>';
				$sHtml .= '<td class="bg-danger fecha_letra" align="right">' . number_format($granTotal, 2, '.', ',') . '</td>';
				$sHtml .= '</tr>';
			}
		}
		$oIfx->Free();

		$sHtml .= '</table>';
	} catch (Exception $e) {
		$oReturn->alert($e->getMessage());
	}


	return $sHtml;
}

function entrada_salida_inv_func($idempresa = "", $idsucursal = "", $bode_cod = '', $prod_cod = '',  $prod_cod2 = '', $fecha_ini = '', $fecha_fin = '', $linea = '', $grupo = '', $cate = '', $marca = '', $opcion = '')
{
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

	$cost_visual        = $_SESSION['U_COSTO_MIRROR'];

	$sql = "select tran_cod_tran, tran_des_tran from saetran where tran_cod_empr = $idempresa ";
	unset($array_tran);
	$array_tran = array_dato($oIfx, $sql, 'tran_cod_tran', 'tran_des_tran');

	$sql = "select unid_cod_unid, unid_nom_unid from saeunid  where unid_cod_empr = $idempresa ";
	unset($array_unid);
	$array_unid = array_dato($oIfx, $sql, 'unid_cod_unid', 'unid_nom_unid');

	try {

		//

		$tabla_det  = '<table class="table table-striped table-condensed table-bordered table-hover" style="width: 95%; margin-top: 20px;" align="center">';
		$tabla_det .= '<tr>
							<td colspan="11"></td>
							<td colspan="3" class="bg-danger fecha_letra" align="center">Entrada</td>
							<td colspan="3" class="bg-success fecha_letra" align="center">Salida</td>
							<td class="bg-info fecha_letra" align="center">Saldos</td>                        
					  </tr>';
		$tabla_det .= '<tr>
							<td class="bg-warning fecha_letra">Fecha</td>
							<td class="bg-warning fecha_letra">Bodega Origen</td>
							<td class="bg-warning fecha_letra">Bodega Destino</td>
							<td class="bg-warning fecha_letra">Tipo</td>
							<td class="bg-warning fecha_letra">Codigo</td>
							<td class="bg-warning fecha_letra">Producto</td>
							<td class="bg-warning fecha_letra">Unidad Medida</td>
							<td class="bg-warning fecha_letra">Serie/Lote</td>
							<td class="bg-warning fecha_letra">Documento</td>  
							<td class="bg-warning fecha_letra">Doc_Recep_Comp</td>  
							<td class="bg-warning fecha_letra">Cliente/Proveedor</td>                      
							<td class="bg-warning fecha_letra">Cantidad</td>
							<td class="bg-warning fecha_letra">Costo</td>
							<td class="bg-warning fecha_letra">Total</td>
							<td class="bg-warning fecha_letra">Cantidad</td>
							<td class="bg-warning fecha_letra">Costo</td>
							<td class="bg-warning fecha_letra">Total</td>
							<td class="bg-warning fecha_letra">Cantidad</td>                       
					  </tr>';

		// $sql = "DROP TABLE IF EXISTS tmp_kardex_io_web;";
		// $oIfx->QueryT($sql);

		// $sql_sp = "execute procedure sp_kardex_in_out_web( $idempresa, $idsucursal, $bode_cod, $linea, $grupo, $cate, $marca, '$fecha_ini', '$fecha_fin', '$prod_cod', '$prod_cod2', 2 )";
		// $oIfx->Query($sql_sp);

		$cant_saldo = 0;
		unset($array);
		unset($array_prod);
		$i = 1;
		$total_cant_ingr = 0;
		$total_cant_egre = 0;
		$total_cost_ingr = 0;
		$total_cost_egre = 0;

		/*
		Adrian47
		$sql_sp = "select * from tmp_kardex_io_web where
						empr_cod_empr = $idempresa and
						sucu_cod_sucu = $idsucursal and
						fecha_ini	  = '$fecha_ini' and
						fecha_fin     = '$fecha_fin' order by fecha_ini, prod_cod_prod, minv_fmov	";
						*/

		$sql_producto = "";
		$sql_linea = "";
		$sql_grupo = "";
		$sql_categoria = "";
		$sql_marca = "";
		$sql_bodega1 = "";
		$sql_bodega2 = "";
		$sql_bodega3 = "";

		if (!empty($linea)) {
			$sql_linea = "AND prod_cod_linp = $linea";
		}
		if (!empty($grupo)) {
			$sql_grupo = "AND prod_cod_grpr = $grupo";
		}
		if (!empty($cate)) {
			$sql_categoria = "AND prod_cod_cate = $cate";
		}
		if (!empty($marca)) {
			$sql_marca = "AND prod_cod_marc = $marca";
		}
		if (!empty($prod_cod)) {
			$sql_producto = "AND prod_cod_prod BETWEEN '$prod_cod' AND '$prod_cod2'";
		}

		if (!empty($bode_cod)) {
			$sql_bodega1 = "AND prbo_cod_bode = $bode_cod";
			$sql_bodega2 = "AND dmov_cod_bode = $bode_cod";
			$sql_bodega3 = "AND dmov_bod_envi = $bode_cod";
		}

		$sql_bodegas = "SELECT bode_cod_bode, bode_nom_bode from saebode where bode_cod_empr = $idempresa";
		$array_bodegas = array();
		if ($oIfx->Query($sql_bodegas)) {
			if ($oIfx->NumFilas() > 0) {
				do {
					$bode_cod_bode = $oIfx->f('bode_cod_bode');
					$bode_nom_bode = $oIfx->f('bode_nom_bode');
					$array_bodegas[$bode_cod_bode] = $bode_nom_bode;
				} while ($oIfx->SiguienteRegistro());
			}
		}
		$oIfx->Free();


		$sql_sucursales = "SELECT sucu_cod_sucu, subo_cod_bode, sucu_nom_sucu from saesubo
								inner join saesucu
									on subo_cod_sucu = sucu_cod_sucu
							where 
								sucu_cod_empr = $idempresa";
		$array_sucursales = array();
		if ($oIfx->Query($sql_sucursales)) {
			if ($oIfx->NumFilas() > 0) {
				do {
					$sucu_cod_sucu = $oIfx->f('sucu_cod_sucu');
					$subo_cod_bode = $oIfx->f('subo_cod_bode');
					$sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
					$array_sucursales[$subo_cod_bode] = $sucu_nom_sucu;
				} while ($oIfx->SiguienteRegistro());
			}
		}
		$oIfx->Free();

		//VALIDAR ESQUEMA ISP
		$array_movimientos = array();
		$sqlinf = "SELECT count(*) as conteo
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE  TABLE_NAME = 'instalacion_materiales' and table_schema='isp'";
		$ctralter = consulta_string($sqlinf, 'conteo', $oIfxA, 0);
		if ($ctralter != 0) {
			$sql_cliente_mov = "	SELECT 
									ic.id_clpv, 
									im.minv_num_comp, 
									clpv_nom_clpv,
									* 
								from isp.instalacion_materiales as im
									inner join isp.instalacion_clpv as ic
										on im.id_instalacion = ic.id
									inner join saeclpv as clpv
										on clpv.clpv_cod_clpv = ic.id_clpv
								where 
									im.minv_num_comp > 0
									and ic.id_empresa = $idempresa
									";
		
		if ($oIfx->Query($sql_cliente_mov)) {
			if ($oIfx->NumFilas() > 0) {
				do {
					$minv_num_comp = $oIfx->f('minv_num_comp');
					$clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
					$array_movimientos[$minv_num_comp] = $clpv_nom_clpv;
				} while ($oIfx->SiguienteRegistro());
			}
		}
		$oIfx->Free();

		}

		

		$sql_sp = "SELECT
							prod_cod_prod,
							minv_fmov,
							COALESCE ( minv_fac_prov, minv_num_sec ) as factura,
							minv_num_sec,
							dmov_bod_envi,
							minv_cm1_minv,
							minv_cm2_minv,
						CASE
								defi_tip_defi 
								WHEN '0' THEN
								'0' 
								WHEN '1' THEN
								'1' 
								WHEN '5' THEN
								'0' 
								WHEN '6' THEN
								'1' 
							END as  tipo,
							minv_cod_tran as tran_cod,
						CASE
							defi_tip_defi 
							WHEN '0' THEN
							dmov_can_dmov 
							WHEN '1' THEN
							- dmov_can_dmov 
							WHEN '5' THEN
							dmov_can_dmov 
							WHEN '6' THEN
							- dmov_can_dmov 
							END as cantidad,
							dmov_pun_dmov as costo,
							minv_num_comp,
							dmov_cod_dmov,
							(
								COALESCE ( ( SELECT ccos_nom_ccos FROM saeccos WHERE ccos_cod_ccos = saedmov.dmov_cod_ccos AND ccos_cod_empr = saedmov.dmov_cod_empr ), '' ) || COALESCE ( ( SELECT clpv_nom_clpv FROM saeclpv WHERE clpv_cod_empr = saedmov.dmov_cod_empr AND clpv_cod_clpv = saeminv.minv_cod_clpv ), '' ) 
							) as clpv_nom,
							prod_nom_prod,
							dmov_cod_unid as unid_cod_unid,
							dmov_cod_lote,
							dmov_serie_tarj,
							dmov_cod_bode,
							dmov_cod_sucu,
							minv_ser_docu,
							minv_fac_prov
						FROM
							saeprbo,
							saeprod,
							saedmov,
							saeminv,
							saetran,
							saedefi 
						WHERE
							prbo_cod_prod = prod_cod_prod 
							AND prbo_cod_empr = prod_cod_empr 
							AND prbo_cod_sucu = prod_cod_sucu 
							AND prod_cod_prod = dmov_cod_prod 
							AND prod_cod_empr = dmov_cod_empr 
							AND dmov_num_comp = minv_num_comp 
							AND dmov_num_prdo = minv_num_prdo 
							AND dmov_cod_empr = minv_cod_empr 
							AND dmov_cod_ejer = minv_cod_ejer 
							AND minv_cod_tran = tran_cod_tran 
							AND minv_cod_empr = tran_cod_empr 
							AND minv_cod_modu = tran_cod_modu 
							AND tran_cod_tran = defi_cod_tran 
							AND tran_cod_empr = defi_cod_empr 
							AND tran_cod_modu = defi_cod_modu 
							$sql_bodega1
							AND prbo_cod_empr = $idempresa
							AND prbo_cod_sucu = $idsucursal
							AND prbo_cos_prod = '2' 
							AND dmov_can_dmov <> 0 
							$sql_producto
							$sql_linea 
							$sql_grupo 
							$sql_categoria 
							$sql_marca
							$sql_bodega2
							AND minv_fmov BETWEEN '$fecha_ini' AND '$fecha_fin' 
							AND minv_cod_empr = $idempresa 
							AND defi_tip_defi IN ( '0', '1', '5', '6' ) UNION
						SELECT
							prod_cod_prod,
							minv_fmov,
							COALESCE ( minv_fac_prov, minv_num_sec ) minv_fac_prov,
							minv_num_sec,
							dmov_bod_envi,
							minv_cm1_minv,
							minv_cm2_minv,
						CASE
								defi_tip_defi 
								WHEN '0' THEN
								'0' 
								WHEN '1' THEN
								'1' 
								WHEN '5' THEN
								'0' 
								WHEN '6' THEN
								'0' 
							END defi_tip_defi,
							minv_cod_tran,
						CASE
							defi_tip_defi 
							WHEN '0' THEN
							dmov_can_dmov 
							WHEN '1' THEN
							- dmov_can_dmov 
							WHEN '5' THEN
							dmov_can_dmov 
							WHEN '6' THEN
							dmov_can_dmov 
							END dmov_can_dmov,
							dmov_pun_dmov,
							minv_num_comp,
							dmov_cod_dmov,
							(
								COALESCE ( ( SELECT ccos_nom_ccos FROM saeccos WHERE ccos_cod_ccos = saedmov.dmov_cod_ccos AND ccos_cod_empr = saedmov.dmov_cod_empr ), '' ) || COALESCE ( ( SELECT clpv_nom_clpv FROM saeclpv WHERE clpv_cod_empr = saedmov.dmov_cod_empr AND clpv_cod_clpv = saeminv.minv_cod_clpv ), '' ) 
							) costoprovee,
							prod_nom_prod,
							dmov_cod_unid,
							dmov_cod_lote,
							dmov_serie_tarj,
							dmov_cod_bode,
							dmov_cod_sucu,
							minv_ser_docu,
							minv_fac_prov
						FROM
							saeprbo,
							saeprod,
							saedmov,
							saeminv,
							saetran,
							saedefi 
						WHERE
							prbo_cod_prod = prod_cod_prod 
							AND prbo_cod_empr = prod_cod_empr 
							AND prbo_cod_sucu = prod_cod_sucu 
							AND prod_cod_prod = dmov_cod_prod 
							AND prod_cod_empr = dmov_cod_empr 
							AND dmov_num_comp = minv_num_comp 
							AND dmov_num_prdo = minv_num_prdo 
							AND dmov_cod_empr = minv_cod_empr 
							AND dmov_cod_ejer = minv_cod_ejer 
							AND minv_cod_tran = tran_cod_tran 
							AND minv_cod_empr = tran_cod_empr 
							AND minv_cod_modu = tran_cod_modu 
							AND tran_cod_tran = defi_cod_tran 
							AND tran_cod_empr = defi_cod_empr 
							AND tran_cod_modu = defi_cod_modu 
							$sql_bodega1
							AND prbo_cod_empr = $idempresa
							AND prbo_cod_sucu = $idsucursal
							AND prbo_cos_prod = '2' 
							AND dmov_can_dmov <> 0 
							$sql_producto 
							$sql_linea 
							$sql_grupo 
							$sql_categoria 
							$sql_marca
							$sql_bodega3
							AND minv_fmov BETWEEN '$fecha_ini' AND '$fecha_fin' 
							AND minv_cod_empr = $idempresa
							AND defi_tip_defi IN ( '0', '1', '5', '6' ) --order by 1,4,3
							
						ORDER BY
							1,
							2,
							4,
							3
							";


		if ($oIfx->Query($sql_sp)) {
			if ($oIfx->NumFilas() > 0) {
				do {
					$prod_cod  = $oIfx->f('prod_cod_prod');
					$fecha_mov = ($oIfx->f('minv_fmov'));
					$secu      = $oIfx->f('factura');
					if (empty($secu)) {
						$secu = $oIfx->f('minv_num_sec');
					}
					$tipo      = $oIfx->f('tipo');
					$tran_nom  = $array_tran[$oIfx->f('tran_cod')];
					$tran_cod  = $oIfx->f('tran_cod');
					$cant      = $oIfx->f('cantidad');
					$costo     = $oIfx->f('costo');
					$prove     = $oIfx->f('clpv_nom');
					$deta      = $oIfx->f('detalle');
					$minv_num_comp  = $oIfx->f('minv_num_comp');
					$prod_nom = $oIfx->f('prod_nom_prod');
					$unidad   = $array_unid[$oIfx->f('unid_cod_unid')];

					$dmov_cod_lote = $oIfx->f('dmov_cod_lote');
					$dmov_cod_tarj = $oIfx->f('dmov_cod_tarj');

					$dmov_cod_bode = $oIfx->f('dmov_cod_bode');
					$dmov_cod_sucu = $oIfx->f('dmov_cod_sucu');
					$dmov_bod_envi = $oIfx->f('dmov_bod_envi');

					$minv_ser_docu = $oIfx->f('minv_ser_docu');
					$minv_fac_prov = $oIfx->f('minv_fac_prov');
					$minv_cm2_minv = $oIfx->f('minv_cm2_minv');

					$serie_factura = '';
					$array_cm2_minv = explode("|", $minv_cm2_minv);
					foreach ($array_cm2_minv as $key => $cm2_minv) {
						$serie_factura .= '<code>' . $cm2_minv . '</code>';
					}


					//$serie_factura = $minv_ser_docu . '-' . $minv_fac_prov;


					$bode_nom_orig = $array_bodegas[$dmov_cod_bode];
					$sucu_nom_orig = $array_sucursales[$dmov_cod_bode];

					$bode_nom_dest = $array_bodegas[$dmov_bod_envi];
					$sucu_nom_dest = $array_sucursales[$dmov_bod_envi];

					$array_prod[$i] = $prod_cod;
					if ($i > 1) {
						if ($array_prod[$i] == $array_prod[$i - 1]) {
						} elseif ($array_prod[$i] != $array_prod[$i - 1]) {
							$cant_saldo = 0;
						}
					} else {
						$cant_saldo = 0;
					}

					if ($cost_visual == 'N') {
						$costo = 0;
					}

					$cant_ingr = 0;
					$costo_ingr = 0;
					$cant_egre = 0;
					$costo_egre = 0;

					$classColor = '';
					if ($tipo == 0) {
						// INGRESO
						$cant_ingr   = $cant;
						$costo_ingr  = $costo;
						$cant_saldo += $cant_ingr;
					} else {
						// EGRESO
						$cant_egre   = $cant;
						$costo_egre  = $costo;
						$cant_saldo -= abs($cant_egre);
					}

					if ($i > 1) {
						$array[$i] = $array[$i - 1] +  abs($cant_ingr * $costo_ingr) - abs($cant_egre * $costo_egre);
					} else {
						$array[$i] = abs($cant_ingr * $costo_ingr) - abs($cant_egre * $costo_egre);
					}

					// COSTO PROMEDO
					$costo_prom = 0;
					if ($cant_saldo > 0) {
						$costo_prom = round(($array[$i] / $cant_saldo), 6);
					}


					if (empty($dmov_cod_lote)) {
						$dmov_cod_lote = "<code>SIN LOTE</code>";
					}

					if (empty($prove)) {
						$prove = $array_movimientos[$minv_num_comp];
						if (empty($prove)) {
							$prove = "<code>SIN CLIENTE/PROVEEDOR</code>";
						} else {
							$prove = "<label style='color: green;'>$prove</label>";
						}
					}

					if (!empty($bode_nom_dest)) {
						$sucu_bode = '(' . $sucu_nom_dest . ') ' . $bode_nom_dest;
					} else {
						$sucu_bode = '';
					}

					if ($opcion == 0) {
						// ENTRADA
						if ($cant_ingr > 0) {
							$tabla_det .= '<tr style="cursor: pointer;" onclick="verDetalleMovimiento(' . $minv_num_comp . ', \'' . $tran_cod . '\')">';
							$tabla_det .= '<td class="' . $classColor . '" align="right">' . $fecha_mov . '</td>  
												<td class="' . $classColor . '">(' . $sucu_nom_orig . ') ' . $bode_nom_orig . '</td>              
												<td class="' . $classColor . '">' . $sucu_bode . '</td>              
												<td class="' . $classColor . '">' . $tran_cod . ' - ' . $tran_nom . '</td> <td class="' . $classColor . '">' . $tran_cod . ' - ' . $tran_nom . '</td> 
												<td class="' . $classColor . '">' . $prod_cod . '</td>   
												<td class="' . $classColor . '">' . $prod_nom . '</td>   
												<td class="' . $classColor . '">' . $unidad . '</td>   
												<td class="' . $classColor . '">' . $dmov_cod_lote . '</td>   
												<td class="' . $classColor . '">' . $secu . '</td>   
												<td class="' . $classColor . '">' . $serie_factura . '</td>   
												<td class="' . $classColor . '" align="left">' . $prove . '</td>
												<td class="' . $classColor . '" align="right">' . $cant_ingr . '</td>   
												<td class="' . $classColor . '" align="right">' . $costo_ingr . '</td>   
												<td class="' . $classColor . '" align="right">' . abs($cant_ingr * $costo_ingr) . '</td>
												<td class="' . $classColor . '" align="right">' . $cant_egre . '</td>
												<td class="' . $classColor . '" align="right">' . $costo_egre . '</td>
												<td class="' . $classColor . '" align="right">' . abs($cant_egre * $costo_egre) . '</td>
												<td class="' . $classColor . '" align="right">' . $cant_saldo . '</td>';
							$tabla_det .= '</tr>';

							$i++;
							$total_cant_ingr += $cant_ingr;
							$total_cost_ingr += abs($cant_ingr * $costo_ingr);
						}
					} elseif ($opcion == 1) {
						// SALIDA
						if ($cant_egre != 0) {
							$tabla_det .= '<tr style="cursor: pointer;" onclick="verDetalleMovimiento(' . $minv_num_comp . ', \'' . $tran_cod . '\')">';
							$tabla_det .= '<td class="' . $classColor . '" align="right">' . $fecha_mov . '</td>               
												<td class="' . $classColor . '">(' . $sucu_nom_orig . ') ' . $bode_nom_orig . '</td>              
												<td class="' . $classColor . '">' .   $sucu_bode  . '</td>              
												<td class="' . $classColor . '">' . $tran_cod . ' - ' . $tran_nom . '</td> 
												<td class="' . $classColor . '">' . $prod_cod . '</td>   
												<td class="' . $classColor . '">' . $prod_nom . '</td>   
												<td class="' . $classColor . '">' . $unidad . '</td>   
												<td class="' . $classColor . '">' . $dmov_cod_lote . '</td>   
												<td class="' . $classColor . '">' . $secu . '</td>  
												<td class="' . $classColor . '">' . $serie_factura . '</td>   
												<td class="' . $classColor . '" align="left">' . $prove . '</td>
												<td class="' . $classColor . '" align="right">' . $cant_ingr . '</td>   
												<td class="' . $classColor . '" align="right">' . $costo_ingr . '</td>   
												<td class="' . $classColor . '" align="right">' . abs($cant_ingr * $costo_ingr) . '</td>
												<td class="' . $classColor . '" align="right">' . $cant_egre . '</td>
												<td class="' . $classColor . '" align="right">' . $costo_egre . '</td>
												<td class="' . $classColor . '" align="right">' . abs($cant_egre * $costo_egre) . '</td>
												<td class="' . $classColor . '" align="right">' . $cant_saldo . '</td>';
							$tabla_det .= '</tr>';

							$i++;
							$total_cant_egre += $cant_egre;
							$total_cost_egre += abs($cant_egre * $costo_egre);
						}
					} elseif ($opcion == 2) {
						// ENTRADA - SALIDA
						$tabla_det .= '<tr style="cursor: pointer;" onclick="verDetalleMovimiento(' . $minv_num_comp . ', \'' . $tran_cod . '\')">';
						$tabla_det .= '<td class="' . $classColor . '" align="right">' . $fecha_mov . '</td>               
											<td class="' . $classColor . '">(' . $sucu_nom_orig . ') ' . $bode_nom_orig . '</td>              
											<td class="' . $classColor . '">' .   $sucu_bode   . '</td>              
											<td class="' . $classColor . '">' . $tran_cod . ' - ' . $tran_nom . '</td> 
											<td class="' . $classColor . '">' . $prod_cod . '</td>   
											<td class="' . $classColor . '">' . $prod_nom . '</td>   
											<td class="' . $classColor . '">' . $unidad . '</td>   
											<td class="' . $classColor . '">' . $dmov_cod_lote . '</td>   
											<td class="' . $classColor . '">' . $secu . '</td>   
											<td class="' . $classColor . '">' . $serie_factura . '</td>  
											<td class="' . $classColor . '" align="left">' . $prove . '</td>
											<td class="' . $classColor . '" align="right">' . $cant_ingr . '</td>   
											<td class="' . $classColor . '" align="right">' . $costo_ingr . '</td>   
											<td class="' . $classColor . '" align="right">' . abs($cant_ingr * $costo_ingr) . '</td>
											<td class="' . $classColor . '" align="right">' . $cant_egre . '</td>
											<td class="' . $classColor . '" align="right">' . $costo_egre . '</td>
											<td class="' . $classColor . '" align="right">' . abs($cant_egre * $costo_egre) . '</td>
											<td class="' . $classColor . '" align="right">' . $cant_saldo . '</td>';
						$tabla_det .= '</tr>';

						$i++;
						$total_cant_ingr += $cant_ingr;
						$total_cant_egre += $cant_egre;
						$total_cost_ingr += abs($cant_ingr * $costo_ingr);
						$total_cost_egre += abs($cant_egre * $costo_egre);
					}
				} while ($oIfx->SiguienteRegistro());

				$tabla_det .= '<tr >';
				$tabla_det .= '<td class="bg-danger fecha_letra" colspan="11">TOTAL:</td> 
                                <td class="bg-danger fecha_letra" align="right">' . $total_cant_ingr . '</td>   
                                <td class="bg-danger fecha_letra" align="right"></td>   
                                <td class="bg-danger fecha_letra" align="right">' . $total_cost_ingr . '</td>
                                <td class="bg-success fecha_letra" align="right">' . $total_cant_egre . '</td>
                                <td class="bg-success fecha_letra" align="right"></td>
                                <td class="bg-success fecha_letra" align="right">' . $total_cost_egre . '</td>
                                <td class="bg-info fecha_letra" align="right">' . ($total_cant_ingr + $total_cant_egre) . '</td>';
				$tabla_det .= '</tr>';
			} else {
				$tabla_det .= 'Sin Datos...';
			} // fin if
		} // fin if      

		$tabla_det .= '</table>';
	} catch (Exception $e) {
		// rollback
		$tabla_det = $e->getMessage();
	}

	return $tabla_det;
}

function entrada_salida_inv_func_serie($idempresa = "", $idsucursal = "", $bode_cod = '', $prod_cod = '',  $prod_cod2 = '', $fecha_ini = '', $fecha_fin = '', $linea = '', $grupo = '', $cate = '', $marca = '', $opcion = '')
{
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

	$cost_visual        = $_SESSION['U_COSTO_MIRROR'];

	$sql = "select tran_cod_tran, tran_des_tran from saetran where tran_cod_empr = $idempresa ";
	unset($array_tran);
	$array_tran = array_dato($oIfx, $sql, 'tran_cod_tran', 'tran_des_tran');

	$sql = "select unid_cod_unid, unid_nom_unid from saeunid  where unid_cod_empr = $idempresa ";
	unset($array_unid);
	$array_unid = array_dato($oIfx, $sql, 'unid_cod_unid', 'unid_nom_unid');

	try {

		//

		$tabla_det  = '<table class="table table-striped table-condensed table-bordered table-hover" style="width: 95%; margin-top: 20px;" align="center">';
		$tabla_det .= '<tr>
							<td colspan="10"></td>
							<td colspan="3" class="bg-danger fecha_letra" align="center">Entrada</td>
							<td colspan="3" class="bg-success fecha_letra" align="center">Salida</td>
							<td class="bg-info fecha_letra" align="center">Saldos</td>                        
					  </tr>';
		$tabla_det .= '<tr>
							<td class="bg-warning fecha_letra">Fecha</td>
							<td class="bg-warning fecha_letra">Sucursal-Bodega</td>
							<td class="bg-warning fecha_letra">Tipo</td>
							<td class="bg-warning fecha_letra">Codigo</td>
							<td class="bg-warning fecha_letra">Producto</td>
							<td class="bg-warning fecha_letra">Unidad</td>
							<td class="bg-warning fecha_letra">SERIE</td>
							<td class="bg-warning fecha_letra">Serie Tarjeta</td>
							<td class="bg-warning fecha_letra">Documento</td>  
							<td class="bg-warning fecha_letra">Cliente/Suplidor</td>                      
							<td class="bg-warning fecha_letra">Cantidad</td>
							<td class="bg-warning fecha_letra">Costo</td>
							<td class="bg-warning fecha_letra">Total</td>
							<td class="bg-warning fecha_letra">Cantidad</td>
							<td class="bg-warning fecha_letra">Costo</td>
							<td class="bg-warning fecha_letra">Total</td>
							<td class="bg-warning fecha_letra">Cantidad</td>                       
					  </tr>';

		// $sql = "DROP TABLE IF EXISTS tmp_kardex_io_web;";
		// $oIfx->QueryT($sql);

		// $sql_sp = "execute procedure sp_kardex_in_out_web( $idempresa, $idsucursal, $bode_cod, $linea, $grupo, $cate, $marca, '$fecha_ini', '$fecha_fin', '$prod_cod', '$prod_cod2', 2 )";
		// $oIfx->Query($sql_sp);

		$cant_saldo = 0;
		unset($array);
		unset($array_prod);
		$i = 1;
		$total_cant_ingr = 0;
		$total_cant_egre = 0;
		$total_cost_ingr = 0;
		$total_cost_egre = 0;

		/*
		Adrian47
		$sql_sp = "select * from tmp_kardex_io_web where
						empr_cod_empr = $idempresa and
						sucu_cod_sucu = $idsucursal and
						fecha_ini	  = '$fecha_ini' and
						fecha_fin     = '$fecha_fin' order by fecha_ini, prod_cod_prod, minv_fmov	";
						*/

		$sql_producto = "";
		$sql_linea = "";
		$sql_grupo = "";
		$sql_categoria = "";
		$sql_marca = "";
		$sql_bodega1 = "";
		$sql_bodega2 = "";
		$sql_bodega3 = "";

		if (!empty($linea)) {
			$sql_linea = "AND prod_cod_linp = $linea";
		}
		if (!empty($grupo)) {
			$sql_grupo = "AND prod_cod_grpr = $grupo";
		}
		if (!empty($cate)) {
			$sql_categoria = "AND prod_cod_cate = $cate";
		}
		if (!empty($marca)) {
			$sql_marca = "AND prod_cod_marc = $marca";
		}
		if (!empty($prod_cod)) {
			$sql_producto = "AND prod_cod_prod BETWEEN '$prod_cod' AND '$prod_cod2'";
		}

		if (!empty($bode_cod)) {
			$sql_bodega1 = "AND prbo_cod_bode = $bode_cod";
			$sql_bodega2 = "AND dmov_cod_bode = $bode_cod";
			$sql_bodega3 = "AND dmov_bod_envi = $bode_cod";
		}


		$sql_sp = "SELECT
							prod_cod_prod,
							minv_fmov,
							COALESCE ( minv_fac_prov, minv_num_sec ) as factura,
							minv_num_sec,
							minv_cm1_minv,
						CASE
								defi_tip_defi 
								WHEN '0' THEN
								'0' 
								WHEN '1' THEN
								'1' 
								WHEN '5' THEN
								'0' 
								WHEN '6' THEN
								'1' 
							END as  tipo,
							minv_cod_tran as tran_cod,
						CASE
							defi_tip_defi 
							WHEN '0' THEN
							dmov_can_dmov 
							WHEN '1' THEN
							- dmov_can_dmov 
							WHEN '5' THEN
							dmov_can_dmov 
							WHEN '6' THEN
							- dmov_can_dmov 
							END as cantidad,
							dmov_pun_dmov as costo,
							minv_num_comp,
							dmov_cod_dmov,
							(
								COALESCE ( ( SELECT ccos_nom_ccos FROM saeccos WHERE ccos_cod_ccos = saedmov.dmov_cod_ccos AND ccos_cod_empr = saedmov.dmov_cod_empr ), '' ) || COALESCE ( ( SELECT clpv_nom_clpv FROM saeclpv WHERE clpv_cod_empr = saedmov.dmov_cod_empr AND clpv_cod_clpv = saeminv.minv_cod_clpv ), '' ) 
							) as clpv_nom,
							prod_nom_prod,
							dmov_cod_unid as unid_cod_unid,
							dmov_cod_lote,
							dmov_serie_tarj,
							dmov_cod_bode,
							dmov_cod_sucu
						FROM
							saeprbo,
							saeprod,
							saedmov,
							saeminv,
							saetran,
							saedefi 
						WHERE
							prbo_cod_prod = prod_cod_prod 
							AND prbo_cod_empr = prod_cod_empr 
							AND prbo_cod_sucu = prod_cod_sucu 
							AND prod_cod_prod = dmov_cod_prod 
							AND prod_cod_empr = dmov_cod_empr 
							AND dmov_num_comp = minv_num_comp 
							AND dmov_num_prdo = minv_num_prdo 
							AND dmov_cod_empr = minv_cod_empr 
							AND dmov_cod_ejer = minv_cod_ejer 
							AND minv_cod_tran = tran_cod_tran 
							AND minv_cod_empr = tran_cod_empr 
							AND minv_cod_modu = tran_cod_modu 
							AND tran_cod_tran = defi_cod_tran 
							AND tran_cod_empr = defi_cod_empr 
							AND tran_cod_modu = defi_cod_modu 
							$sql_bodega1
							AND prbo_cod_empr = $idempresa
							AND prbo_cod_sucu = $idsucursal
							AND prbo_cos_prod = '2' 
							AND dmov_can_dmov <> 0 
							$sql_producto
							$sql_linea 
							$sql_grupo 
							$sql_categoria 
							$sql_marca
							$sql_bodega2
							AND minv_fmov BETWEEN '$fecha_ini' AND '$fecha_fin' 
							AND minv_cod_empr = $idempresa 
							and dmov_cod_lote != ''
							and dmov_cod_lote is not null
							AND defi_tip_defi IN ( '0', '1', '5', '6' ) UNION
						SELECT
							prod_cod_prod,
							minv_fmov,
							COALESCE ( minv_fac_prov, minv_num_sec ) minv_fac_prov,
							minv_num_sec,
							minv_cm1_minv,
						CASE
								defi_tip_defi 
								WHEN '0' THEN
								'0' 
								WHEN '1' THEN
								'1' 
								WHEN '5' THEN
								'0' 
								WHEN '6' THEN
								'0' 
							END defi_tip_defi,
							minv_cod_tran,
						CASE
							defi_tip_defi 
							WHEN '0' THEN
							dmov_can_dmov 
							WHEN '1' THEN
							- dmov_can_dmov 
							WHEN '5' THEN
							dmov_can_dmov 
							WHEN '6' THEN
							dmov_can_dmov 
							END dmov_can_dmov,
							dmov_pun_dmov,
							minv_num_comp,
							dmov_cod_dmov,
							(
								COALESCE ( ( SELECT ccos_nom_ccos FROM saeccos WHERE ccos_cod_ccos = saedmov.dmov_cod_ccos AND ccos_cod_empr = saedmov.dmov_cod_empr ), '' ) || COALESCE ( ( SELECT clpv_nom_clpv FROM saeclpv WHERE clpv_cod_empr = saedmov.dmov_cod_empr AND clpv_cod_clpv = saeminv.minv_cod_clpv ), '' ) 
							) costoprovee,
							prod_nom_prod,
							dmov_cod_unid,
							dmov_cod_lote,
							dmov_serie_tarj,
							dmov_cod_bode,
							dmov_cod_sucu
						FROM
							saeprbo,
							saeprod,
							saedmov,
							saeminv,
							saetran,
							saedefi 
						WHERE
							prbo_cod_prod = prod_cod_prod 
							AND prbo_cod_empr = prod_cod_empr 
							AND prbo_cod_sucu = prod_cod_sucu 
							AND prod_cod_prod = dmov_cod_prod 
							AND prod_cod_empr = dmov_cod_empr 
							AND dmov_num_comp = minv_num_comp 
							AND dmov_num_prdo = minv_num_prdo 
							AND dmov_cod_empr = minv_cod_empr 
							AND dmov_cod_ejer = minv_cod_ejer 
							AND minv_cod_tran = tran_cod_tran 
							AND minv_cod_empr = tran_cod_empr 
							AND minv_cod_modu = tran_cod_modu 
							AND tran_cod_tran = defi_cod_tran 
							AND tran_cod_empr = defi_cod_empr 
							AND tran_cod_modu = defi_cod_modu 
							$sql_bodega1
							AND prbo_cod_empr = $idempresa
							AND prbo_cod_sucu = $idsucursal
							AND prbo_cos_prod = '2' 
							AND dmov_can_dmov <> 0 
							$sql_producto 
							$sql_linea 
							$sql_grupo 
							$sql_categoria 
							$sql_marca
							$sql_bodega3
							AND minv_fmov BETWEEN '$fecha_ini' AND '$fecha_fin' 
							AND minv_cod_empr = $idempresa
							and dmov_cod_lote != ''
							and dmov_cod_lote is not null
							AND defi_tip_defi IN ( '0', '1', '5', '6' ) --order by 1,4,3
							
						ORDER BY
							1,
							2,
							4,3";



		if ($oIfx->Query($sql_sp)) {
			if ($oIfx->NumFilas() > 0) {
				do {
					$prod_cod  = $oIfx->f('prod_cod_prod');
					$fecha_mov = ($oIfx->f('minv_fmov'));
					$secu      = $oIfx->f('factura');
					if (empty($secu)) {
						$secu = $oIfx->f('minv_num_sec');
					}
					$tipo      = $oIfx->f('tipo');
					$tran_nom  = $array_tran[$oIfx->f('tran_cod')];
					$tran_cod  = $oIfx->f('tran_cod');
					$cant      = $oIfx->f('cantidad');
					$costo     = $oIfx->f('costo');
					$prove     = $oIfx->f('clpv_nom');
					$deta      = $oIfx->f('detalle');
					$minv_num_comp  = $oIfx->f('minv_num_comp');
					$prod_nom = $oIfx->f('prod_nom_prod');
					$unidad   = $array_unid[$oIfx->f('unid_cod_unid')];

					$dmov_cod_lote = $oIfx->f('dmov_cod_lote');
					$dmov_cod_tarj = $oIfx->f('dmov_cod_tarj');

					$dmov_cod_bode = $oIfx->f('dmov_cod_bode');
					$dmov_cod_sucu = $oIfx->f('dmov_cod_sucu');

					$sql_nombre_bodega = "SELECT bode_nom_bode from saebode where bode_cod_bode = $dmov_cod_bode";
					$bode_nom_bode = consulta_string($sql_nombre_bodega, 'bode_nom_bode', $oIfxA, '');

					$sql_nombre_sucursal = "SELECT sucu_nom_sucu from saesucu where sucu_cod_sucu = $dmov_cod_sucu";
					$sucu_nom_sucu = consulta_string($sql_nombre_sucursal, 'sucu_nom_sucu', $oIfxA, '');


					$array_prod[$i] = $prod_cod;
					if ($i > 1) {
						if ($array_prod[$i] == $array_prod[$i - 1]) {
						} elseif ($array_prod[$i] != $array_prod[$i - 1]) {
							$cant_saldo = 0;
						}
					} else {
						$cant_saldo = 0;
					}

					if ($cost_visual == 'N') {
						$costo = 0;
					}

					$cant_ingr = 0;
					$costo_ingr = 0;
					$cant_egre = 0;
					$costo_egre = 0;

					$classColor = '';
					if ($tipo == 0) {
						// INGRESO
						$cant_ingr   = $cant;
						$costo_ingr  = $costo;
						$cant_saldo += $cant_ingr;
					} else {
						// EGRESO
						$cant_egre   = $cant;
						$costo_egre  = $costo;
						$cant_saldo -= abs($cant_egre);
					}

					if ($i > 1) {
						$array[$i] = $array[$i - 1] +  abs($cant_ingr * $costo_ingr) - abs($cant_egre * $costo_egre);
					} else {
						$array[$i] = abs($cant_ingr * $costo_ingr) - abs($cant_egre * $costo_egre);
					}

					// COSTO PROMEDO
					$costo_prom = 0;
					if ($cant_saldo > 0) {
						$costo_prom = round(($array[$i] / $cant_saldo), 6);
					}


					if (empty($dmov_cod_lote)) {
						$dmov_cod_lote = "<code>SIN LOTE</code>";
					}

					if (empty($prove)) {
						$prove = "<code>SIN PROVEEDOR</code>";
					}


					if ($opcion == 0) {
						// ENTRADA
						if ($cant_ingr > 0) {
							$tabla_det .= '<tr style="cursor: pointer;" onclick="verDetalleMovimiento(' . $minv_num_comp . ', \'' . $tran_cod . '\')">';
							$tabla_det .= '<td class="' . $classColor . '" align="right">' . $fecha_mov . '</td>  
												<td class="' . $classColor . '">' . $sucu_nom_sucu . ' - ' . $bode_nom_bode . '</td>              
												<td class="' . $classColor . '">' . $tran_cod . ' - ' . $tran_nom . '</td> <td class="' . $classColor . '">' . $tran_cod . ' - ' . $tran_nom . '</td> 
												<td class="' . $classColor . '">' . $prod_cod . '</td>   
												<td class="' . $classColor . '">' . $prod_nom . '</td>   
												<td class="' . $classColor . '">' . $unidad . '</td>   
												<td class="' . $classColor . '">' . $dmov_cod_lote . '</td>   
												<td class="' . $classColor . '">' . $dmov_cod_tarj . '</td>   
												<td class="' . $classColor . '">' . $secu . '</td>   
												<td class="' . $classColor . '" align="left">' . $prove . '</td>
												<td class="' . $classColor . '" align="right">' . $cant_ingr . '</td>   
												<td class="' . $classColor . '" align="right">' . $costo_ingr . '</td>   
												<td class="' . $classColor . '" align="right">' . abs($cant_ingr * $costo_ingr) . '</td>
												<td class="' . $classColor . '" align="right">' . $cant_egre . '</td>
												<td class="' . $classColor . '" align="right">' . $costo_egre . '</td>
												<td class="' . $classColor . '" align="right">' . abs($cant_egre * $costo_egre) . '</td>
												<td class="' . $classColor . '" align="right">' . $cant_saldo . '</td>';
							$tabla_det .= '</tr>';

							$i++;
							$total_cant_ingr += $cant_ingr;
							$total_cost_ingr += abs($cant_ingr * $costo_ingr);
						}
					} elseif ($opcion == 1) {
						// SALIDA
						if ($cant_egre != 0) {
							$tabla_det .= '<tr style="cursor: pointer;" onclick="verDetalleMovimiento(' . $minv_num_comp . ', \'' . $tran_cod . '\')">';
							$tabla_det .= '<td class="' . $classColor . '" align="right">' . $fecha_mov . '</td>               
												<td class="' . $classColor . '">' . $sucu_nom_sucu . ' - ' . $bode_nom_bode . '</td>              
												<td class="' . $classColor . '">' . $tran_cod . ' - ' . $tran_nom . '</td> 
												<td class="' . $classColor . '">' . $prod_cod . '</td>   
												<td class="' . $classColor . '">' . $prod_nom . '</td>   
												<td class="' . $classColor . '">' . $unidad . '</td>   
												<td class="' . $classColor . '">' . $dmov_cod_lote . '</td>   
												<td class="' . $classColor . '">' . $dmov_cod_tarj . '</td>   
												<td class="' . $classColor . '">' . $secu . '</td>   
												<td class="' . $classColor . '" align="left">' . $prove . '</td>
												<td class="' . $classColor . '" align="right">' . $cant_ingr . '</td>   
												<td class="' . $classColor . '" align="right">' . $costo_ingr . '</td>   
												<td class="' . $classColor . '" align="right">' . abs($cant_ingr * $costo_ingr) . '</td>
												<td class="' . $classColor . '" align="right">' . $cant_egre . '</td>
												<td class="' . $classColor . '" align="right">' . $costo_egre . '</td>
												<td class="' . $classColor . '" align="right">' . abs($cant_egre * $costo_egre) . '</td>
												<td class="' . $classColor . '" align="right">' . $cant_saldo . '</td>';
							$tabla_det .= '</tr>';

							$i++;
							$total_cant_egre += $cant_egre;
							$total_cost_egre += abs($cant_egre * $costo_egre);
						}
					} elseif ($opcion == 2) {
						// ENTRADA - SALIDA
						$tabla_det .= '<tr style="cursor: pointer;" onclick="verDetalleMovimiento(' . $minv_num_comp . ', \'' . $tran_cod . '\')">';
						$tabla_det .= '<td class="' . $classColor . '" align="right">' . $fecha_mov . '</td>               
											<td class="' . $classColor . '">' . $sucu_nom_sucu . ' - ' . $bode_nom_bode . '</td>              
											<td class="' . $classColor . '">' . $tran_cod . ' - ' . $tran_nom . '</td> 
											<td class="' . $classColor . '">' . $prod_cod . '</td>   
											<td class="' . $classColor . '">' . $prod_nom . '</td>   
											<td class="' . $classColor . '">' . $unidad . '</td>   
											<td class="' . $classColor . '">' . $dmov_cod_lote . '</td>   
											<td class="' . $classColor . '">' . $dmov_cod_tarj . '</td>   
											<td class="' . $classColor . '">' . $secu . '</td>   
											<td class="' . $classColor . '" align="left">' . $prove . '</td>
											<td class="' . $classColor . '" align="right">' . $cant_ingr . '</td>   
											<td class="' . $classColor . '" align="right">' . $costo_ingr . '</td>   
											<td class="' . $classColor . '" align="right">' . abs($cant_ingr * $costo_ingr) . '</td>
											<td class="' . $classColor . '" align="right">' . $cant_egre . '</td>
											<td class="' . $classColor . '" align="right">' . $costo_egre . '</td>
											<td class="' . $classColor . '" align="right">' . abs($cant_egre * $costo_egre) . '</td>
											<td class="' . $classColor . '" align="right">' . $cant_saldo . '</td>';
						$tabla_det .= '</tr>';

						$i++;
						$total_cant_ingr += $cant_ingr;
						$total_cant_egre += $cant_egre;
						$total_cost_ingr += abs($cant_ingr * $costo_ingr);
						$total_cost_egre += abs($cant_egre * $costo_egre);
					}
				} while ($oIfx->SiguienteRegistro());

				$tabla_det .= '<tr >';
				$tabla_det .= '<td class="bg-danger fecha_letra" colspan="10">TOTAL:</td> 
                                <td class="bg-danger fecha_letra" align="right">' . $total_cant_ingr . '</td>   
                                <td class="bg-danger fecha_letra" align="right"></td>   
                                <td class="bg-danger fecha_letra" align="right">' . $total_cost_ingr . '</td>
                                <td class="bg-success fecha_letra" align="right">' . $total_cant_egre . '</td>
                                <td class="bg-success fecha_letra" align="right"></td>
                                <td class="bg-success fecha_letra" align="right">' . $total_cost_egre . '</td>
                                <td class="bg-info fecha_letra" align="right">' . ($total_cant_ingr + $total_cant_egre) . '</td>';
				$tabla_det .= '</tr>';
			} else {
				$tabla_det .= 'Sin Datos...';
			} // fin if
		} // fin if      

		$tabla_det .= '</table>';
	} catch (Exception $e) {
		// rollback
		$tabla_det = $e->getMessage();
	}

	return $tabla_det;
}

function envio_correo_oc($correo = '', $ride = '', $pdf = '', $nom_clpv = '', $idTipo = 0, $title = '', $secu_num = '', $serial)
{
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	//variables de session
	$idEmpresa  = $_SESSION['U_EMPRESA'];
	$idSucursal = $_SESSION['U_SUCURSAL'];

	//tipo documento
	$sqlDocu = "select documento from doc_time where id_time = '$idTipo' ";
	$docu = consulta_string_func($sqlDocu, 'documento', $oCon, '');

	$sqlEmpr = "select empr_nom_empr, empr_dir_empr, empr_tel_resp from saeempr where empr_cod_empr = $idEmpresa";
	if ($oIfx->Query($sqlEmpr)) {
		$compania 		= $oIfx->f("empr_nom_empr");
		$dirMatriz 		= $oIfx->f('empr_dir_empr');
		$empr_tel_resp 	= $oIfx->f("empr_tel_resp");
	}
	$oIfx->Free();

	//consulta correos y mensaje
	$sql = "select prfa_sub_prfa, prfa_men_prfa from saeprfa where prfa_cod_empr = $idEmpresa and prfa_est_prfa = 'S' and prfa_cod_prfa = 5";
	if ($oIfx->Query($sql)) {
		$prfa_sub_prfa = $oIfx->f("prfa_sub_prfa");
		$prfa_men_prfa = $oIfx->f("prfa_men_prfa");
	}
	$oIfx->Free();

	$sqlSmtp = "select server, port, auth, user, pass, ssltls, mail 	from config_email 	where 
                    id_empresa   = $idEmpresa and
			        id_tipo      = '$idTipo' ";
	if ($oCon->Query($sqlSmtp)) {
		if ($oCon->NumFilas() > 0) {
			$host       = $oCon->f('server');
			$port       = $oCon->f('port');
			$smtpauth   = $oCon->f('auth');
			$userid     = $oCon->f('user');
			$smtpsecure = $oCon->f('ssltls');
			$mailenvio  = $oCon->f('mail');
			$password   = $oCon->f('pass');
		}
	}
	$oCon->Free();

	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->Mailer = "smtp";

	if ($smtpauth == 'S') {
		$mail->SMTPAuth = true;
	}

	if ($smtpsecure == 'ssl') {
		$mail->SMTPSecure = "ssl";
	} elseif ($smtpsecure == 'tls') {
		$mail->SMTPSecure = "tls";
	}

	$mail->Host = $host;
	$mail->Port = $port;
	$mail->Username = $userid;
	$mail->Password = $password;
	$mail->From = $mailenvio;

	$mail->FromName = $compania;
	$mail->Subject = $prfa_sub_prfa . ' ' . $docu . ' ' . $nom_clpv;
	$mail->AltBody = $title;
	$mail->IsHTML(true);
	$mail->CharSet = 'UTF-8';

	$sHtml = "<div style='width: 900px;'>
				<table style='width:850px;'> 
					<tr>
						<td>Estimado Suplidor, <span style='font-weight: bold'>$nom_clpv</span></td>
					</tr>
					<tr>
						<td>Le informamos que ha sido generado la siguiente Orden de Compra N.- <span style='font-weight: bold'>$secu_num</span>, a continuacion adjuntamos el archivo</td>
					</tr>
				</table>
				<br /> 
				<br><br>
				<table style='width:850px;'>
					<tr> 
						<td>Atentamente,</td>
					</tr> 
					<tr>&nbsp;</tr>
					<tr>&nbsp;</tr>
					<tr>
						<td style='font-weight: bold; font-size: 13px;'>$compania</td>
					</tr>
					<tr>&nbsp;</tr>
					<tr>
						<td style='font-weight: bold;'>Dire.: $dirMatriz</td>
					</tr>
					<tr>
						<td style='font-weight: bold;'>Telf.: $empr_tel_resp</td>
					</tr>
					 <tr>&nbsp;</tr>
				</table>
			</div>";

	$mail->MsgHTML($sHtml);

	$correoArray = explode(";", $correo);

	if (count($correoArray) > 0) {
		for ($j = 0; $j < count($correoArray); $j++) {
			$correoTmp = trim($correoArray[$j]);

			if (verificaremail($correoTmp)) {
				$mail->AddAddress($correoTmp, $correoTmp);
			}
		}
	} else {
		if (verificaremail($correo)) {
			$mail->AddAddress($correo, $correo);
		}
	}

	//consulta correos
	$sql_correo = "select prfa_mail1, prfa_mail2, prfa_mail3 from saeprfa where prfa_cod_empr = $idEmpresa and prfa_est_prfa = 'S' and prfa_cod_prfa = 5";
	if ($oIfx->Query($sql_correo)) {
		if ($oIfx->NumFilas() > 0) {
			$prfa_mail1 = $oIfx->f('prfa_mail1');
			$prfa_mail2 = $oIfx->f('prfa_mail2');
			$prfa_mail3 = $oIfx->f('prfa_mail3');

			//envia correo
			if (!empty($prfa_mail1))
				$mail->AddBCC($prfa_mail1, $prfa_mail1);

			if (!empty($prfa_mail2))
				$mail->AddBCC($prfa_mail2, $prfa_mail2);

			if (!empty($prfa_mail3))
				$mail->AddBCC($prfa_mail3, $prfa_mail3);
		}
	}
	$oIfx->Free();

	$rutaRide = array(split("/", $ride));
	$nombDocu = $rutaRide[count($rutaRide)];

	$ruta = DIR_FACTELEC . 'include/orden_compra/' . $serial . '.pdf';
	$mail->AddAttachment($ruta, $nombDocu);

	if (!$mail->Send())
		return "Error: " . $mail->ErrorInfo . $sqlSmtp;
	else
		return 'Mail enviado!';
}

function envio_correo_cierre_caja($correo = '', $ride = '', $pdf = '', $nom_clpv = '', $idTipo = 0, $title = '', $usuario = '', $info)
{
	include_once(path(DIR_INCLUDE) . "class.phpmailer.php");
	include_once(path(DIR_INCLUDE) . "class.smtp.php");
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	//variables de session
	$idEmpresa  = $_SESSION['U_EMPRESA'];
	$idSucursal = $_SESSION['U_SUCURSAL'];

	//tipo documento
	$sqlDocu = "select documento from doc_time where id_time = '$idTipo' ";
	$docu = consulta_string_func($sqlDocu, 'documento', $oCon, '');

	$sqlEmpr = "select empr_nom_empr, empr_dir_empr, empr_tel_resp from saeempr where empr_cod_empr = $idEmpresa";
	if ($oIfx->Query($sqlEmpr)) {
		$compania 		= $oIfx->f("empr_nom_empr");
		$dirMatriz 		= $oIfx->f('empr_dir_empr');
		$empr_tel_resp 	= $oIfx->f("empr_tel_resp");
	}
	$oIfx->Free();

	//consulta correos y mensaje
	$sql = "select prfa_sub_prfa, prfa_men_prfa from saeprfa where prfa_cod_empr = $idEmpresa and prfa_est_prfa = 'S' and prfa_cod_prfa = 5";
	if ($oIfx->Query($sql)) {
		$prfa_sub_prfa = $oIfx->f("prfa_sub_prfa");
		$prfa_men_prfa = $oIfx->f("prfa_men_prfa");
	}
	$oIfx->Free();

	$sqlSmtp = "select server, port, auth, user, pass, ssltls, mail 	from config_email 	where 
                    id_empresa   = $idEmpresa and
			        id_tipo      = '$idTipo' ";
	if ($oCon->Query($sqlSmtp)) {
		if ($oCon->NumFilas() > 0) {
			$host       = $oCon->f('server');
			$port       = $oCon->f('port');
			$smtpauth   = $oCon->f('auth');
			$userid     = $oCon->f('user');
			$smtpsecure = $oCon->f('ssltls');
			$mailenvio  = $oCon->f('mail');
			$password   = $oCon->f('pass');
		}
	}
	$oCon->Free();

	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->Mailer = "smtp";

	if ($smtpauth == 'S') {
		$mail->SMTPAuth = true;
	}

	if ($smtpsecure == 'ssl') {
		$mail->SMTPSecure = "ssl";
	} elseif ($smtpsecure == 'tls') {
		$mail->SMTPSecure = "tls";
	}

	$mail->Host = $host;
	$mail->Port = $port;
	$mail->Username = $userid;
	$mail->Password = $password;
	$mail->From = $mailenvio;

	$mail->FromName = $compania;
	$mail->Subject = $prfa_sub_prfa . ' ' . $docu . ' ' . $nom_clpv;
	$mail->AltBody = $title;
	$mail->IsHTML(true);
	$mail->CharSet = 'UTF-8';

	$sHtml = "<div style='width: 900px;'>
				<table style='width:850px;'> 
					<tr>
						<td><span style='font-weight: bold; font-size: 15px;'>Estimado, $nom_clpv</span></td>
					</tr>
					<tr>
						<td>
							<span style='font-weight: bold; font-size: 15px;'>
								Le informamos que ha sido generado Cierre de Caja del Usuario: $usuario , a continuacion detallamos informacion
							</span>
						</td>
					</tr>
				</table>
				$info
				<br /> 
				<table style='width:850px;'>
					<tr> 
						<td>Atentamente,</td>
					</tr> 
					<tr>&nbsp;</tr>
					<tr>&nbsp;</tr>
					<tr>
						<td style='font-weight: bold; font-size: 13px;'>$compania</td>
					</tr>
					<tr>&nbsp;</tr>
					<tr>
						<td style='font-weight: bold;'>Dire.: $dirMatriz</td>
					</tr>
					<tr>
						<td style='font-weight: bold;'>Telf.: $empr_tel_resp</td>
					</tr>
					 <tr>&nbsp;</tr>
				</table>
			</div>";

	$mail->MsgHTML($sHtml);

	$correoArray = explode(";", $correo);

	if (count($correoArray) > 0) {
		for ($j = 0; $j < count($correoArray); $j++) {
			$correoTmp = trim($correoArray[$j]);

			if (verificaremail($correoTmp)) {
				$mail->AddAddress($correoTmp, $correoTmp);
			}
		}
	} else {
		if (verificaremail($correo)) {
			$mail->AddAddress($correo, $correo);
		}
	}

	//consulta correos
	$sql_correo = "select prfa_mail1, prfa_mail2, prfa_mail3 from saeprfa where prfa_cod_empr = $idEmpresa and prfa_est_prfa = 'S' and prfa_cod_prfa = 5";
	if ($oIfx->Query($sql_correo)) {
		if ($oIfx->NumFilas() > 0) {
			$prfa_mail1 = $oIfx->f('prfa_mail1');
			$prfa_mail2 = $oIfx->f('prfa_mail2');
			$prfa_mail3 = $oIfx->f('prfa_mail3');

			//envia correo
			if (!empty($prfa_mail1))
				$mail->AddBCC($prfa_mail1, $prfa_mail1);

			if (!empty($prfa_mail2))
				$mail->AddBCC($prfa_mail2, $prfa_mail2);

			if (!empty($prfa_mail3))
				$mail->AddBCC($prfa_mail3, $prfa_mail3);
		}
	}
	$oIfx->Free();

	$mail->AddBCC('d-aniel300_89@hotmail.com', 'd-aniel300_89@hotmail.com');

	if (!$mail->Send())
		return "Error: " . $mail->ErrorInfo . $sqlSmtp;
	else
		return 'Mail enviado!';
}

function envio_correo_deposito($correo = '', $idTipo = 0, $title = '', $usuario = '', $info)
{
	include_once(path(DIR_INCLUDE) . "class.phpmailer.php");
	include_once(path(DIR_INCLUDE) . "class.smtp.php");
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	//variables de session
	$idEmpresa  = $_SESSION['U_EMPRESA'];
	$idSucursal = $_SESSION['U_SUCURSAL'];

	//tipo documento
	$sqlDocu = "select documento from doc_time where id_time = '$idTipo' ";
	$docu = consulta_string_func($sqlDocu, 'documento', $oCon, '');

	$sqlEmpr = "select empr_nom_empr, empr_dir_empr, empr_tel_resp from saeempr where empr_cod_empr = $idEmpresa";
	if ($oIfx->Query($sqlEmpr)) {
		$compania 		= $oIfx->f("empr_nom_empr");
		$dirMatriz 		= $oIfx->f('empr_dir_empr');
		$empr_tel_resp 	= $oIfx->f("empr_tel_resp");
	}
	$oIfx->Free();

	//consulta correos y mensaje
	$sql = "select prfa_sub_prfa, prfa_men_prfa from saeprfa where prfa_cod_empr = $idEmpresa and prfa_est_prfa = 'S' and prfa_cod_prfa = 5";
	if ($oIfx->Query($sql)) {
		$prfa_sub_prfa = $oIfx->f("prfa_sub_prfa");
		$prfa_men_prfa = $oIfx->f("prfa_men_prfa");
	}
	$oIfx->Free();

	$sqlSmtp = "select server, port, auth, user, pass, ssltls, mail 	from config_email 	where 
                    id_empresa   = $idEmpresa and
			        id_tipo      = '$idTipo' ";
	if ($oCon->Query($sqlSmtp)) {
		if ($oCon->NumFilas() > 0) {
			$host       = $oCon->f('server');
			$port       = $oCon->f('port');
			$smtpauth   = $oCon->f('auth');
			$userid     = $oCon->f('user');
			$smtpsecure = $oCon->f('ssltls');
			$mailenvio  = $oCon->f('mail');
			$password   = $oCon->f('pass');
		}
	}
	$oCon->Free();

	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->Mailer = "smtp";

	if ($smtpauth == 'S') {
		$mail->SMTPAuth = true;
	}

	if ($smtpsecure == 'ssl') {
		$mail->SMTPSecure = "ssl";
	} elseif ($smtpsecure == 'tls') {
		$mail->SMTPSecure = "tls";
	}

	$mail->Host = $host;
	$mail->Port = $port;
	$mail->Username = $userid;
	$mail->Password = $password;
	$mail->From = $mailenvio;

	$mail->FromName = $compania;
	$mail->Subject = $prfa_sub_prfa . ' ' . $docu . ' ' . $usuario;
	$mail->AltBody = $title;
	$mail->IsHTML(true);
	$mail->CharSet = 'UTF-8';

	$sHtml = "<div style='width: 900px;'>
				<table style='width:850px;'> 
					<tr>
						<td><span style='font-weight: bold; font-size: 15px;'>Estimado, </span></td>
					</tr>
					<tr>
						<td>
							<span style='font-weight: bold; font-size: 15px;'>
								Le informamos que ha sido generado un Deposito de Caja del Usuario: $usuario , a continuacion detallamos informacion
							</span>
						</td>
					</tr>
				</table>
				$info
				<br /> 
				<table style='width:850px;'>
					<tr> 
						<td>Atentamente,</td>
					</tr> 
					<tr>&nbsp;</tr>
					<tr>&nbsp;</tr>
					<tr>
						<td style='font-weight: bold; font-size: 13px;'>$compania</td>
					</tr>
					<tr>&nbsp;</tr>
					<tr>
						<td style='font-weight: bold;'>Dire.: $dirMatriz</td>
					</tr>
					<tr>
						<td style='font-weight: bold;'>Telf.: $empr_tel_resp</td>
					</tr>
					 <tr>&nbsp;</tr>
				</table>
			</div>";

	$mail->MsgHTML($sHtml);

	$correoArray = explode(";", $correo);

	if (count($correoArray) > 0) {
		for ($j = 0; $j < count($correoArray); $j++) {
			$correoTmp = trim($correoArray[$j]);

			if (verificaremail($correoTmp)) {
				$mail->AddAddress($correoTmp, $correoTmp);
			}
		}
	} else {
		if (verificaremail($correo)) {
			$mail->AddAddress($correo, $correo);
		}
	}

	//consulta correos
	$sql_correo = "select prfa_mail1, prfa_mail2, prfa_mail3 from saeprfa where prfa_cod_empr = $idEmpresa and prfa_est_prfa = 'S' and prfa_cod_prfa = 5";
	if ($oIfx->Query($sql_correo)) {
		if ($oIfx->NumFilas() > 0) {
			$prfa_mail1 = $oIfx->f('prfa_mail1');
			$prfa_mail2 = $oIfx->f('prfa_mail2');
			$prfa_mail3 = $oIfx->f('prfa_mail3');

			//envia correo
			if (!empty($prfa_mail1))
				$mail->AddBCC($prfa_mail1, $prfa_mail1);

			if (!empty($prfa_mail2))
				$mail->AddBCC($prfa_mail2, $prfa_mail2);

			if (!empty($prfa_mail3))
				$mail->AddBCC($prfa_mail3, $prfa_mail3);
		}
	}
	$oIfx->Free();

	$mail->AddBCC('d-aniel300_89@hotmail.com', 'd-aniel300_89@hotmail.com');

	if (!$mail->Send())
		return "Error: " . $mail->ErrorInfo . $sqlSmtp;
	else
		return 'Mail enviado!';
}


function equipos_ctrl_almacen_rd($oCon, $prod_cod, $serie_equ, $serie_tarj)
{
	$sql = "SELECT c.caja, c.tarjeta, c.caja_dig, c.tarj_dig FROM int_marcas c WHERE c.prod_cod_prod = '$prod_cod' ";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$caja     = $oCon->f('caja');
			$tarjeta  = $oCon->f('tarjeta');
			$caja_dig = $oCon->f('caja_dig');
			$tarj_dig = $oCon->f('tarj_dig');
		}
	}
	$oCon->free();

	$len_serie = strlen($serie_equ);
	$len_tarj  = strlen($serie_tarj);

	$ctrl = 0;
	if ($caja_dig == $len_serie && $tarj_dig == $len_tarj) {
		$ctrl = 1;
	}

	return $ctrl;
}

function generar_precierre_pdf_rd($idempresa = "", $sucursal = "", $usuario = "", $fecha = "")
{
	global $DSN_Ifx, $DSN;

	session_start();

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$idEmpresa = $_SESSION['U_EMPRESA'];
	$mone_sigla = $_SESSION['U_MONE_SIGLA'];
	$fecha_ifx = ($fecha);

	//$oIfx->QueryT('set isolation to dirty read;');

	$class = new GeneraCierreCaja();

	$arrayCierreCab   = $class->generaCierreCab($oCon, $idempresa, $sucursal, $usuario, $fecha);
	if (count($arrayCierreCab) > 0) {
		foreach ($arrayCierreCab as $val) {
			$usuario_id 	= $val[0];
			$sucu_cod_sucu 	= $val[1];
			$fecha_cierre 	= $val[2];
			$hora 			= $val[3];
			$estado 		= $val[4];
			$val_gasto 		= $val[5];
			$val_cierre_rd 	= $val[6];
			$usuario_modi 	= $val[7];
			$fecha_modi 	= $val[8];
			$id_cierre 		= $val[9];
			if ($estado == 'PE') {
				$estado = 'PENDIENTE';
			} else {
				$estado = 'TERMINADO';
			}
		}
	}

	$arrayCierreGasto = $class->generaCierreGasto($oCon, $idempresa, $sucursal, $usuario, $id_cierre);
	$arrayBilleteUSD  = $class->generaCierreBilleteUSD($oCon, $idempresa, $sucursal, $usuario, $fecha);
	$arrayMonedaUSD   = $class->generaCierreMonedaUSD($oCon, $idempresa, $sucursal, $usuario, $fecha);
	$arrayBilleteRD   = $class->generaCierreBilleteRD($oCon, $idempresa, $sucursal, $usuario, $fecha);
	$arrayMonedaRD    = $class->generaCierreMonedaRD($oCon, $idempresa, $sucursal, $usuario, $fecha);
	$arrayCheque      = $class->generaCierreFormaPago($oIfx, $idempresa, $sucursal, $usuario, $fecha_ifx, 'CHE');
	$arrayTarjeta     = $class->generaCierreFormaPago($oIfx, $idempresa, $sucursal, $usuario, $fecha_ifx, 'TAR');
	$arrayTrans       = $class->generaCierreFormaPago($oIfx, $idempresa, $sucursal, $usuario, $fecha_ifx, 'DEP');
	$arrayCredito     = $class->generaCierreFormaPago($oIfx, $idempresa, $sucursal, $usuario, $fecha_ifx, 'CRE');
	$arrayRetencion   = $class->generaCierreFormaPago($oIfx, $idempresa, $sucursal, $usuario, $fecha_ifx, 'RET');

	$sql = "SELECT concat(u.USUARIO_NOMBRE, ' ',u.USUARIO_APELLIDO) AS nombre FROM comercial.usuario u WHERE u.USUARIO_ID = '$usuario_id' ";
	$user_web = consulta_string_func($sql, 'nombre', $oCon, '');

	$sql = "select empr_ruc_empr, empr_dir_empr, empr_nom_empr, empr_path_logo from saeempr where empr_cod_empr = $idempresa ";
	if ($oIfx->Query($sql)) {
		$empr_ruc = $oIfx->f('empr_ruc_empr');
		$empr_dir = $oIfx->f('empr_dir_empr');
		$empr_nom = $oIfx->f('empr_nom_empr');
		$empr_path_logo = $oIfx->f('empr_path_logo');
		$empr_nom .= ' ';
	}
	$sql = "SELECT sucu_nom_sucu FROM saesucu WHERE sucu_cod_sucu='$sucursal'";
	if ($oIfx->Query($sql)) {
		$sucu_nom = $oIfx->f('sucu_nom_sucu');
	}
	$oIfx->Free();


	$html .= '<table style="margin-left:50px; margin-right:50px; margin-top:30px; font-family: Courier;" >
				<tr >
					<td style="font-size:18px; text-align: left">' . $empr_nom . '<br><br></td>
				</tr>				
				<tr>
					<td  style="font-size:12px; text-align: left">DIRECCION:' . $empr_dir . '<br><br></td>
				</tr>
				<tr>
					<td style="font-size:12px; text-align: left">RNC:' . $empr_ruc . '<br><br></td>
				</tr>
				<tr>
					<td style="font-size:14px; text-align: left">CIERRE CAJA<br><br></td>
				</tr>				
			</table>
			<table border="0"  style="width: 100%; margin-left:50px; margin-top:5px; font-family: Courier;">
				<tr>
					<td  style="width: 100%;font-size:12px; text-align: left">Usuario: ' . $user_web . '</td>					
				</tr>
				<tr>
					<td  style="width: 100%;font-size:12px; text-align: left">Sucursal:' . $sucu_nom . '<br><br></td>
				</tr>
				<tr>
					<td  style="width: 100%;font-size:12px; text-align: left">Fecha: ' . $fecha_cierre . ' &nbsp; ' . $hora . '</td>					
				</tr>				
				<tr>
					<td  style="width: 100%; font-size:12px; text-align: left">Estado: &nbsp;' . $estado . '</td>
                </tr>				
			</table>';

	//GASTO RD
	if (count($arrayCierreGasto) > 0) {
		$html .= '<table  style="margin-left:50px; width:90%;border:1px solid black; border-radius: 5px; margin-top:5px; font-family: Courier;" align="left">
					<tr>
					<td colspan="5" style="width:100%;font-size:16px; text-align: center; border-bottom:1px solid ">Gastos ' . $mone_sigla . '</td>
					</tr>
					<tr>
						<td  style="width:3%; font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">N</td>
						<td  style="width:30%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid">GASTO</td>
						<td  style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid">FACTURA</td>
						<td  style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid">DETALLE</td>
						<td  style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid">VALOR</td>
					</tr>';
		$i = 1;
		$subt_gasto = 0;
		foreach ($arrayCierreGasto as $val) {
			$id_gasto_pre   = $val[0];
			$gasto          = $val[1];
			$valor          = $val[2];
			$factura        = $val[3];
			$detalle        = $val[4];
			$id_gasto       = $val[5];

			$html .= '<tr>';
			$html .= '<td style="width:3%;font-size:12px;  text-align: right;  border-right:1px solid; border-bottom:1px solid ">' . $i . '</td>';
			$html .= '<td style="width:30%;font-size:12px; text-align: center; border-right:1px solid; border-bottom:1px solid">' . $gasto . '</td>';
			$html .= '<td style="width:10%;font-size:12px; text-align: center; border-right:1px solid; border-bottom:1px solid ">' . $factura . '</td>';
			$html .= '<td style="width:20%;font-size:12px; text-align: center; border-right:1px solid; border-bottom:1px solid ">' . $detalle . '</td>';
			$html .= '<td style="width:20%;font-size:12px; text-right: center; border-right:1px solid; border-bottom:1px solid ">' . number_format($valor, 2, '.', ',') . '</td>';
			$html .= '</tr>';

			$subt_gasto += $valor;
			$i++;
		}
		$html .= '<tr>';
		$html .= '<td style="width:3%;font-size:12px;  text-align: right;  border-right:1px solid; border-bottom:1px solid"></td>';
		$html .= '<td style="width:30%;font-size:12px; text-align: center; border-right:1px solid; border-bottom:1px solid"></td>';
		$html .= '<td style="width:10%;font-size:12px; text-align: center; border-right:1px solid; border-bottom:1px solid "></td>';
		$html .= '<td style="width:20%;font-size:12px; text-align: center; border-right:1px solid; border-bottom:1px solid ">TOTAL:</td>';
		$html .= '<td style="width:20%;font-size:12px; text-right: center; border-right:1px solid; border-bottom:1px solid ">' . number_format($subt_gasto, 2, '.', ',') . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
	}

	//BILLETE RD
	if (count($arrayBilleteRD) > 0) {
		$html .= '<table  style="margin-left:50px; width:90%;border:1px solid black; border-radius: 5px; margin-top:30px; font-family: Courier;" align="left">';
		$html .= '<tr>';
		$html .= '<td colspan="5" style="width:100%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">EFECTIVO ' . $mone_sigla . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid " >N.-</td>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Cantidad</td>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Valor ' . $mone_sigla . '</td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Total ' . $mone_sigla . '</td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Estado</td>';
		$html .= '</tr>';
		$i = 1;
		$subt_bille_rd = 0;
		foreach ($arrayBilleteRD as $val) {
			$id_det_caja = $val[0];
			$forma_pago  = $val[1];
			$cantidad    = $val[2];
			$valor       = $val[3];
			$total       = $val[4];
			$tipo        = $val[5];
			$estado      = $val[6];

			if ($estado == 'PE') {
				$estado = 'PENDIENTE';
			} elseif ($estado == 'TE') {
				$estado = "TERMINADO";
			}

			$html .= '<tr>';
			$html .= '<td style="width:20%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . $i . '</td>';
			$html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" >' . $cantidad . '</td>';
			$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($valor, 2, '.', ',') . '</td>';
			$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($total, 2, '.', ',') . '</td>';
			$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $estado . '</td>';
			$html .= '</tr>';

			$i++;
			$subt_bille_rd += $total;
		} //fin foreach
		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" ></td>';
		$html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid"></td>';
		$html .= '<td style="width:20%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid">TOTAL:</td>';
		$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($subt_bille_rd, 2, '.', ',') . '</td>';
		$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right"></td>';
		$html .= '</tr>';
		$html .= '</table>';
	}

	// MONEDA RD
	if (count($arrayMonedaRD) > 0) {
		$html .= '<table  style="margin-left:50px; width:90%;border:1px solid black; border-radius: 5px; margin-top:30px; font-family: Courier;" align="left">';
		$html .= '<tr>';
		$html .= '<td colspan="5" style="width:100%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">MONEDA ' . $mone_sigla . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid " >N.-</td>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Cantidad</td>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Ctvs ' . $mone_sigla . '</td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Total ' . $mone_sigla . '</td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Estado</td>';
		$html .= '</tr>';
		$i = 1;
		$subt_mone_rd = 0;
		foreach ($arrayMonedaRD as $val) {
			$id_det_caja = $val[0];
			$forma_pago  = $val[1];
			$cantidad    = $val[2];
			$valor       = $val[3];
			$total       = $val[4];
			$tipo        = $val[5];
			$estado      = $val[6];

			if ($estado == 'PE') {
				$estado = 'PENDIENTE';
			} elseif ($estado == 'TE') {
				$estado = "TERMINADO";
			}

			$html .= '<tr>';
			$html .= '<td style="width:20%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . $i . '</td>';
			$html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" >' . $cantidad . '</td>';
			$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($valor, 2, '.', ',') . '</td>';
			$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($total, 2, '.', ',') . '</td>';
			$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $estado . '</td>';
			$html .= '</tr>';

			$i++;
			$subt_mone_rd += $total;
		} //fin foreach
		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" ></td>';
		$html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" ></td>';
		$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">TOTAL:</td>';
		$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($subt_mone_rd, 2, '.', ',') . '</td>';
		$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid"></td>';
		$html .= '</tr>';
		$html .= '</table>';
	}


	// CHEQUE
	if (count($arrayCheque) > 0) {
		$html .= '<table  style="margin-left:50px; width:90%;border:1px solid black; border-radius: 5px; margin-top:30px; font-family: Courier;" align="left">';
		$html .= '<tr>';
		$html .= '<td colspan="5" style="width:100%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">CHEQUE ' . $mone_sigla . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">N.-			</td>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Transaccion </td>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Cheque      </td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Factura     </td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Valor       </td>';
		$html .= '</tr>';
		$i = 1;
		$subt_cheq = 0;
		foreach ($arrayCheque as $val) {
			$fact_mum_preimp = $val[0];
			$fact_nse_fact   = $val[1];
			$fpag_des_fpag   = $val[2];
			$fxfp_val_fxfp   = $val[3];
			$fact_cod_fact   = $val[4];
			$fxfp_num_rete   = $val[5];

			$html .= '<tr>';
			$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" >' . $i . '</td>';
			$html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" >' . $fpag_des_fpag . '</td>';
			$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $fxfp_num_rete . '</td>';
			$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $fact_nse_fact . '-' . $fact_mum_preimp . '</td>';
			$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($fxfp_val_fxfp, 2, '.', ',') . '</td>';
			$html .= '</tr>';

			$i++;
			$subt_cheq += $fxfp_val_fxfp;
		} //fin foreach

		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" ></td>';
		$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid"></td>';
		$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid"></td>';
		$html .= '<td style="width:20%;font-size:12px; text-align: center; border-right:1px solid; border-bottom:1px solid">TOTAL:</td>';
		$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($subt_cheq, 2, '.', ',') . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
	}

	// TARJETA
	if (count($arrayTarjeta) > 0) {
		$html .= '<table  style="margin-left:50px; width:90%;border:1px solid black; border-radius: 5px; margin-top:30px; font-family: Courier;" align="left">';
		$html .= '<tr>';
		$html .= '<td colspan="5" style="width:100%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">TARJETA ' . $mone_sigla . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid " >N.-</td>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Transaccion</td>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Voucher</td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Factura</td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Valor</td>';
		$html .= '</tr>';
		$i = 1;
		$subt_tar = 0;
		foreach ($arrayTarjeta as $val) {
			$fact_mum_preimp = $val[0];
			$fact_nse_fact   = $val[1];
			$fpag_des_fpag   = $val[2];
			$fxfp_val_fxfp   = $val[3];
			$fact_cod_fact   = $val[4];
			$fxfp_num_rete   = $val[5];

			$html .= '<tr>';
			$html .= '<td style="width:20%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . $i . '</td>';
			$html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" >' . $fpag_des_fpag . '</td>';
			$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $fxfp_num_rete . '</td>';
			$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $fact_nse_fact . '-' . $fact_mum_preimp . '</td>';
			$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($fxfp_val_fxfp, 2, '.', ',') . '</td>';

			$html .= '</tr>';

			$i++;
			$subt_tar += $fxfp_val_fxfp;
		} //fin foreach
		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid" ></td>';
		$html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" ></td>';
		$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid"></td>';
		$html .= '<td style="width:20%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid">TOTAL:</td>';
		$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($subt_tar, 2, '.', ',') . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
	}

	// DEPOSITO
	if (count($arrayTrans) > 0) {
		$html .= '<table  style="margin-left:50px; width:90%;border:1px solid black; border-radius: 5px; margin-top:30px; font-family: Courier;" align="left">';
		$html .= '<tr>';
		$html .= '<td colspan="5" style="width:100%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">TRANSFERENCIA ' . $mone_sigla . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid " >N.-</td>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Transaccion</td>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Voucher</td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Factura</td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Valor</td>';
		$html .= '</tr>';
		$i = 1;
		$subt_depo = 0;
		foreach ($arrayTrans as $val) {
			$fact_mum_preimp = $val[0];
			$fact_nse_fact   = $val[1];
			$fpag_des_fpag   = $val[2];
			$fxfp_val_fxfp   = $val[3];
			$fact_cod_fact   = $val[4];
			$fxfp_num_rete   = $val[5];

			$html .= '<tr>';
			$html .= '<td style="width:20%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . $i . '</td>';
			$html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" >' . $fpag_des_fpag . '</td>';
			$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $fxfp_num_rete . '</td>';
			$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $fact_nse_fact . '-' . $fact_mum_preimp . '</td>';
			$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($fxfp_val_fxfp, 2, '.', ',') . '</td>';

			$html .= '</tr>';

			$i++;
			$subt_depo += $fxfp_val_fxfp;
		} //fin foreach
		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" ></td>';
		$html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" ></td>';
		$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid"></td>';
		$html .= '<td style="width:20%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid">TOTAL:</td>';
		$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($subt_depo, 2, '.', ',') . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
	}

	// CREDITO
	if (count($arrayCredito) > 0) {
		$html .= '<table  style="margin-left:50px; width:90%;border:1px solid black; border-radius: 5px; margin-top:30px; font-family: Courier;" align="left">';
		$html .= '<tr>';
		$html .= '<td colspan="4" style="width:100%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">CREDITO ' . $mone_sigla . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid " >N.-</td>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Transaccion</td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Factura</td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Valor</td>';
		$html .= '</tr>';
		$i = 1;
		$subt_cre = 0;
		foreach ($arrayCredito as $val) {
			$fact_mum_preimp = $val[0];
			$fact_nse_fact   = $val[1];
			$fpag_des_fpag   = $val[2];
			$fxfp_val_fxfp   = $val[3];
			$fact_cod_fact   = $val[4];
			$fxfp_num_rete   = $val[5];

			$html .= '<tr>';
			$html .= '<td style="width:20%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . $i . '</td>';
			$html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" >' . $fpag_des_fpag . '</td>';
			$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $fact_nse_fact . '-' . $fact_mum_preimp . '</td>';
			$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($fxfp_val_fxfp, 2, '.', ',') . '</td>';

			$html .= '</tr>';

			$i++;
			$subt_cre += $fxfp_val_fxfp;
		} //fin foreach
		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid" ></td>';
		$html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" ></td>';
		$html .= '<td style="width:20%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid">TOTAL:</td>';
		$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($subt_cre, 2, '.', ',') . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
	}


	// RETENCION
	if (count($arrayRetencion) > 0) {
		$html .= '<table  style="margin-left:50px; width:90%;border:1px solid black; border-radius: 5px; margin-top:30px; font-family: Courier;" align="left">';
		$html .= '<tr>';
		$html .= '<td colspan="4" style="width:100%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">RETENCION ' . $mone_sigla . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid " >N.-</td>';
		$html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Transaccion</td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Factura</td>';
		$html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid ">Valor</td>';
		$html .= '</tr>';
		$i = 1;
		$subt_ret = 0;
		foreach ($arrayRetencion as $val) {
			$fact_mum_preimp = $val[0];
			$fact_nse_fact   = $val[1];
			$fpag_des_fpag   = $val[2];
			$fxfp_val_fxfp   = $val[3];
			$fact_cod_fact   = $val[4];
			$fxfp_num_rete   = $val[5];

			$html .= '<tr>';
			$html .= '<td style="width:20%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . $i . '</td>';
			$html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" >' . $fpag_des_fpag . '</td>';
			$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $fact_nse_fact . '-' . $fact_mum_preimp . '</td>';
			$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($fxfp_val_fxfp, 2, '.', ',') . '</td>';

			$html .= '</tr>';

			$i++;
			$subt_ret += $fxfp_val_fxfp;
		} //fin foreach
		$html .= '<tr>';
		$html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" ></td>';
		$html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" ></td>';
		$html .= '<td style="width:20%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid">TOTAL:</td>';
		$html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($subt_ret, 2, '.', ',') . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
	}


	$sql = "SELECT SUM(c.valor) AS valor FROM comercial.cierre_depo c WHERE
					c.empr_cod_empr = $idempresa AND
					c.sucu_cod_sucu = $sucursal AND
					c.usuario_id    = $usuario AND
					c.fecha_depo    = '$fecha'  ";
	$deposito = consulta_string($sql, 'valor', $oCon, 0);

	// EFECTIVO USD

	$table_total  = '<table style="margin-left:50px; margin-right:50px; margin-top:30px; font-family: Courier;" >';
	$table_total .= '<tr height="20">';
	$table_total .= '<td colspan="6"></td>';
	$table_total .= '<td align="right" style="font-size: 11px; color: black">EFECTIVO USD$:</td>';
	$table_total .= '<td align="right" style="font-size: 11px; color: black">&nbsp;' . number_format($subt_bille_usd + $subt_mone_usd, 2, '.', ',') . '</td>';
	$table_total .= '</tr>';

	// EFECTIVO RD
	$table_total .= '<tr height="20">';
	$table_total .= '<td colspan="6"></td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black">EFECTIVO ' . $mone_sigla . '$:</td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black">&nbsp;' . number_format($subt_bille_rd + $subt_mone_rd, 2, '.', ',') . '</td>';
	$table_total .= '</tr>';


	// CHEQUE
	$table_total .= '<tr height="20">';
	$table_total .= '<td colspan="6"></td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black">CHEQUE ' . $mone_sigla . '$:</td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black">&nbsp;' . number_format($subt_cheq, 2, '.', ',')   . '</td>';
	$table_total .= '</tr>';

	// TARJETAS
	$table_total .= '<tr height="20">';
	$table_total .= '<td colspan="6"></td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black">TARJETA CRE. ' . $mone_sigla . '$:</td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black">&nbsp;' . number_format($subt_tar, 2, '.', ',')  . '</td>';
	$table_total .= '</tr>';

	// DEPOSITO . TRANSFERENCIAS
	$table_total .= '<tr height="20">';
	$table_total .= '<td colspan="6"></td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black">TRANSFERENCIAS. ' . $mone_sigla . '$:</td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black">&nbsp;' . number_format($subt_depo, 2, '.', ',')  . '</td>';
	$table_total .= '</tr>';

	// CREDITO
	$table_total .= '<tr height="20">';
	$table_total .= '<td colspan="6"></td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black">CREDITO ' . $mone_sigla . '$:</td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black">&nbsp;' . number_format($subt_cre, 2, '.', ',')  . '</td>';
	$table_total .= '</tr>';

	// RETENCIONES
	$table_total .= '<tr height="20">';
	$table_total .= '<td colspan="6"></td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black">RETENCIONES ' . $mone_sigla . '$.:</td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black">&nbsp;' . number_format($subt_ret, 2, '.', ',')  . '</td>';
	$table_total .= '</tr>';

	$table_total .= '<tr height="20">';
	$table_total .= '<td colspan="6"></td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black;">GASTO ' . $mone_sigla . '$:</td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black;;">&nbsp;' . number_format($Sval_gasto, 2, '.', ',') . '</td>';
	$table_total .= '</tr>';

	$table_total .= '<tr height="20">';
	$table_total .= '<td colspan="6"></td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black;">DEPOSITO RD$:</td>';
	$table_total .= '<td align="right" class="font_face_2" style="font-size: 11px; color: black;;">&nbsp;' . number_format($deposito, 2, '.', ',') . '</td>';
	$table_total .= '</tr>';

	// TOTAL CIERRE CAJA
	$total_cierre = ($total_bille_rd + $total_mone_rd + $total_che + $totalTarjeta + $total_cre + $totalRetencion + $val_gasto + $totalDeposito + $deposito);

	$table_total .= '<tr height="20">';
	$table_total .= '<td colspan="6"></td>';
	$table_total .= '<td align="right" style="font-size: 12px; color: blue">TOTAL CIERRE CAJA RD$:</td>';
	$table_total .= '<td align="right" style="font-size: 12px; color: blue; border-bottom: 2px solid #000">&nbsp;' . number_format($total_cierre, 2, '.', ',')  . '</td>';
	$table_total .= '</tr>';
	$table_total .= '</table>';

	return $html . $table_total;
}

function equipos_ctrl_activos_rd($oCon, $idempresa,  $idtarjeta, $id_caja)
{
	$sql = "SELECT c.id, c.id_contrato , a.codigo 
					FROM int_contrato_caja c, contrato_clpv a WHERE
					a.id 			= c.id_contrato AND
					a.id_empresa    = $idempresa and
					c.id_empresa    = $idempresa AND
					c.id_tarjeta    = '$idtarjeta' AND
					c.estado IN ( 'A', 'C' ) AND
					c.id           <> $id_caja ";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$id       	  = $oCon->f('id');
			$id_contrato  = $oCon->f('id_contrato');
			$codigo       = $oCon->f('codigo');
		}
	}
	$oCon->free();

	return $codigo;
}

function saetran_costo($oIfx, $idempresa,  $idsucursal, $prod_cod, $bode_cod, $tran_cod, $costo, $cantidad)
{
	$sql = "select defi_cost_defi ,  defi_iva_incl  from saedefi where
				defi_cod_empr = $idempresa and
				defi_cod_sucu = $idsucursal and
				defi_cod_tran  = '$tran_cod' ";
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			$defi_cost_defi = $oIfx->f('defi_cost_defi');
			$defi_iva_incl  = $oIfx->f('defi_iva_incl');
		}
	}
	$oIfx->free();

	// defi_cost_defi   0 COSTO UNIT  1 COSTO TOTAL
	if ($defi_cost_defi == '1') {
		$costo = round(($costo / $cantidad), 6);
	}

	// defi_iva_incl   N NO INCLUYE IVA     S INCLUYE IVA
	if ($defi_iva_incl == 'S') {
		$sql = "select prbo_iva_porc from saeprbo where
					prbo_cod_empr = $idempresa and
					prbo_cod_bode = $bode_cod and
					prbo_cod_prod = '$prod_cod'  ";
		$iva_porc = consulta_string_func($sql, 'prbo_iva_porc', $oIfx, 0);
		$iva_val  = ($iva_porc / 100) + 1;
		$costo    = round(($costo / $iva_val), 6);
	}

	return $costo;
}

function get_nombre_dia($fecha)
{
	$fechats = strtotime($fecha); //pasamos a timestamp

	//el parametro w en la funcion date indica que queremos el dia de la semana
	//lo devuelve en numero 0 domingo, 1 lunes,....
	switch (date('w', $fechats)) {
		case 0:
			return "Do";
			break;
		case 1:
			return "Lu";
			break;
		case 2:
			return "Ma";
			break;
		case 3:
			return "Mi";
			break;
		case 4:
			return "Ju";
			break;
		case 5:
			return "Vi";
			break;
		case 6:
			return "Sa";
			break;
	}
}

function generar_precierre_pdf_rd_term($idempresa = "", $sucursal = "", $usuario = "", $fecha = "")
{
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oIfxA = new Dbo;
	$oIfxA->DSN = $DSN_Ifx;
	$oIfxA->Conectar();

	$idEmpresa = $_SESSION['U_EMPRESA'];
	$mone_sigla = $_SESSION['U_MONE_SIGLA'];
	$fecha_ifx = fecha_informix_func($fecha);

	//

	$class = new GeneraCierreCaja();

	$arrayCierreCab   = $class->generaCierreCab($oCon, $idempresa, $sucursal, $usuario, $fecha);
	if (count($arrayCierreCab) > 0) {
		foreach ($arrayCierreCab as $val) {
			$usuario_id 	= $val[0];
			$sucu_cod_sucu 	= $val[1];
			$fecha_cierre 	= $val[2];
			$hora 			= $val[3];
			$estado 		= $val[4];
			$val_gasto 		= $val[5];
			$val_cierre_rd 	= $val[6];
			$usuario_modi 	= $val[7];
			$fecha_modi 	= $val[8];
			$id_cierre 		= $val[9];
			if ($estado == 'PE') {
				$estado = 'PENDIENTE';
			} else {
				$estado = 'TERMINADO';
			}
		}
	}

	$arrayCierreGasto = $class->generaCierreGasto($oCon, $idempresa, $sucursal, $usuario, $id_cierre);
	$arrayBilleteUSD  = $class->generaCierreBilleteUSD($oCon, $idempresa, $sucursal, $usuario, $fecha);
	$arrayMonedaUSD   = $class->generaCierreMonedaUSD($oCon, $idempresa, $sucursal, $usuario, $fecha);
	$arrayBilleteRD   = $class->generaCierreBilleteRD($oCon, $idempresa, $sucursal, $usuario, $fecha);
	$arrayMonedaRD    = $class->generaCierreMonedaRD($oCon, $idempresa, $sucursal, $usuario, $fecha);


	$sql = "SELECT concat(u.USUARIO_NOMBRE, ' ',u.USUARIO_APELLIDO) AS nombre, empl_cod_empl FROM usuario u WHERE u.USUARIO_ID = '$usuario_id' ";
	$user_web = consulta_string_func($sql, 'nombre', $oCon, '');
	$empl_cod_empl = consulta_string_func($sql, 'empl_cod_empl', $oCon, '');

	$sql = "select empr_ruc_empr, empr_dir_empr, empr_nom_empr, empr_path_logo from saeempr where empr_cod_empr = $idempresa ";
	if ($oIfx->Query($sql)) {
		$empr_ruc = $oIfx->f('empr_ruc_empr');
		$empr_dir = $oIfx->f('empr_dir_empr');
		$empr_nom = $oIfx->f('empr_nom_empr');
		$empr_path_logo = $oIfx->f('empr_path_logo');
		$empr_nom .= ' ';
	}
	$sql = "SELECT sucu_nom_sucu FROM saesucu WHERE sucu_cod_sucu='$sucursal'";
	if ($oIfx->Query($sql)) {
		$sucu_nom = $oIfx->f('sucu_nom_sucu');
	}
	$oIfx->Free();


	$html .= '<table style=" margin-right:50px; margin-top:30px; " >
				<tr >
					<td style="font-size:14px; text-align: left"><strong>' . $empr_nom . '</strong><br><br></td>
				</tr>				
				<tr>
					<td  style="font-size:12px; text-align: left">DIRECCION:' . $empr_dir . '<br><br></td>
				</tr>
				<tr>
					<td style="font-size:12px; text-align: left">NIT:' . $empr_ruc . '<br><br></td>
				</tr>
				<tr>
					<td style="font-size:12px; text-align: left"><strong>CIERRE CAJA<br><br></strong></td>
				</tr>				
			</table>
			<table border="0"  style="width: 100%; margin-top:5px; ">
				<tr>
					<td  style="width: 100%;font-size:12px; text-align: left"><strong>Usuario:</strong> ' . $user_web . '</td>					
				</tr>
				<tr>
					<td  style="width: 100%;font-size:12px; text-align: left"><strong>Sucursal:</strong>' . $sucu_nom . '<br><br></td>
				</tr>
				<tr>
					<td  style="width: 100%;font-size:12px; text-align: left"><strong>Fecha:</strong> ' . $fecha_cierre . ' &nbsp; ' . $hora . '</td>					
				</tr>				
				<tr>
					<td  style="width: 100%; font-size:12px; text-align: left"><strong>Estado: &nbsp;</strong>' . $estado . '</td>
                </tr>				
			</table>';

	//GASTO RD
	if (count($arrayCierreGasto) > 0) {
		$html .= '<table  style=" width:90%;border:1px solid black; border-radius: 5px; margin-top:5px; " align="left">
					<tr>
					<td colspan="5" style="width:100%;font-size:11px; text-align: center; border-bottom:1px solid "><strong>Gastos ' . $mone_sigla . '</strong></td>
					</tr>
					<tr>
						<td  style="width:5%; font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>N</strong></td>
						<td  style="width:10%;font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid"><strong>GASTO</strong></td>
						<td  style="width:35%;font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid"><strong>FACTURA</strong></td>
						<td  style="width:10%;font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid"><strong>VALOR</strong></td>
					</tr>';
		$i = 1;
		$subt_gasto = 0;
		foreach ($arrayCierreGasto as $val) {
			//var_dump($val);exit;
			$id_gasto_pre   = $val[0];
			$gasto          = $val[1];
			$valor          = $val[2];
			$factura        = $val[3];
			$detalle        = $val[4];
			$id_gasto       = $val[5];

			$html .= '<tr>';
			$html .= '<td style="width:5%; font-size:11px; text-align: right;  border-right:1px solid; border-bottom:1px solid ">' . $i . '</td>';
			$html .= '<td style="width:10%;font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid">' . $gasto . '</td>';
			$html .= '<td style="width:35%;font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid ">' . $factura . '</td>';
			$html .= '<td style="width:20%;font-size:11px; text-align: center;  border-bottom:1px solid ">' . number_format($valor, 2, '.', ',') . '</td>';
			$html .= '</tr>';

			$subt_gasto += $valor;
			$i++;
		}
		$html .= '<tr>';
		$html .= '<td style="width:3%; font-size:11px;  text-align: right;  border-right:1px solid;"></td>';
		$html .= '<td style="width:30%;font-size:11px; text-align: center; border-right:1px solid;"></td>';
		$html .= '<td style="width:20%;font-size:11px; text-align: center; border-right:1px solid;"><strong>TOTAL:</strong></td>';
		$html .= '<td style="width:20%;font-size:11px; text-align: center; "><strong>' . number_format($subt_gasto, 2, '.', ',') . '</strong></td>';
		$html .= '</tr>';
		$html .= '</table>';
	}

	//BILLETE RD
	if (count($arrayBilleteRD) > 0) {
		$html .= '<table  style=" width:70%;border:1px solid black; border-radius: 5px; margin-top:30px; " align="left">';
		$html .= '<tr>';
		$html .= '<td colspan="4" style="width:100%;font-size:14px; text-align: center; border-bottom:1px solid "><strong>EFECTIVO ' . $mone_sigla . '</strong></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid " ><strong>N</strong></td>';
		$html .= '<td style="font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Cant</strong></td>';
		$html .= '<td style="font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Valor</strong></td>';
		$html .= '<td style="font-size:11px; text-align: center;  border-bottom:1px solid "><strong>Total </strong></td>';
		$html .= '</tr>';
		$i = 1;
		$subt_bille_rd = 0;
		foreach ($arrayBilleteRD as $val) {
			$id_det_caja = $val[0];
			$forma_pago  = $val[1];
			$cantidad    = $val[2];
			$valor       = $val[3];
			$total       = $val[4];
			$tipo        = $val[5];
			$estado      = $val[6];

			if ($estado == 'PE') {
				$estado = 'PENDIENTE';
			} elseif ($estado == 'TE') {
				$estado = "TERMINADO";
			}

			$html .= '<tr>';
			$html .= '<td style="font-size:11px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . $i . '</td>';
			$html .= '<td style="font-size:11px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . round($cantidad, 2) . '</td>';
			$html .= '<td style="font-size:11px; text-align: right; border-right:1px solid; border-bottom:1px solid" align="right">' . number_format($valor, 2, '.', ',') . '</td>';
			$html .= '<td style="font-size:11px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($total, 2, '.', ',') . '</td>';
			$html .= '</tr>';

			$i++;
			$subt_bille_rd += $total;
		} //fin foreach
		$html .= '<tr>';
		$html .= '<td style="font-size:11px; text-align: left; " ></td>';
		$html .= '<td style="font-size:11px; text-align: left; "></td>';
		$html .= '<td style="font-size:11px; text-align: right; border-right:1px solid; "><strong>TOTAL:</strong></td>';
		$html .= '<td style="font-size:11px; text-align: right; " align="right"><strong>' . number_format($subt_bille_rd, 2, '.', ',') . '</strong></td>';
		$html .= '</tr>';
		$html .= '</table>';
	}

	// MONEDA RD
	if (count($arrayMonedaRD) > 0) {
		$html .= '<table  style="width:70%;border:1px solid black; border-radius: 5px; margin-top:30px; " align="left">';
		$html .= '<tr>';
		$html .= '<td colspan="4" style="width:100%;font-size:14px; text-align: center;  border-bottom:1px solid "><strong>MONEDA ' . $mone_sigla . '</strong></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid " ><strong>N</strong></td>';
		$html .= '<td style="font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Cant</strong></td>';
		$html .= '<td style="font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Ctvs</strong></td>';
		$html .= '<td style="font-size:11px; text-align: center; border-bottom:1px solid "><strong>Total </strong></td>';
		$html .= '</tr>';
		$i = 1;
		$subt_mone_rd = 0;
		foreach ($arrayMonedaRD as $val) {
			$id_det_caja = $val[0];
			$forma_pago  = $val[1];
			$cantidad    = $val[2];
			$valor       = $val[3];
			$total       = $val[4];
			$tipo        = $val[5];
			$estado      = $val[6];

			if ($estado == 'PE') {
				$estado = 'PENDIENTE';
			} elseif ($estado == 'TE') {
				$estado = "TERMINADO";
			}

			$html .= '<tr>';
			$html .= '<td style="font-size:11px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . $i . '</td>';
			$html .= '<td style="font-size:11px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . $cantidad . '</td>';
			$html .= '<td style="font-size:11px; text-align: right; border-right:1px solid; border-bottom:1px solid" align="right">' . number_format($valor, 2, '.', ',') . '</td>';
			$html .= '<td style="font-size:11px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($total, 2, '.', ',') . '</td>';
			$html .= '</tr>';

			$i++;
			$subt_mone_rd += $total;
		} //fin foreach
		$html .= '<tr>';
		$html .= '<td style="font-size:11px; text-align: left;" ></td>';
		$html .= '<td style="font-size:11px; text-align: left;" ></td>';
		$html .= '<td style="font-size:11px; text-align: right; " align="right"><strong>TOTAL:</strong></td>';
		$html .= '<td style="font-size:11px; text-align: right; " align="right"><strong>' . number_format($subt_mone_rd, 2, '.', ',') . '</strong></td>';
		$html .= '</tr>';
		$html .= '</table>';
	}

	//BILLETE USD
	if (count($arrayBilleteUSD) > 0) {
		$html .= '<table  style=" width:70%;border:1px solid black; border-radius: 5px; margin-top:30px;" align="left">';
		$html .= '<tr>';
		$html .= '<td colspan="4" style="width:100%;font-size:14px; text-align: center; border-bottom:1px solid "><strong>EFECTIVO USD</strong></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid " ><strong>N</strong></td>';
		$html .= '<td style="font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Cant</strong></td>';
		$html .= '<td style="font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Valor</strong></td>';
		$html .= '<td style="font-size:11px; text-align: center;  border-bottom:1px solid "><strong>Total</strong></td>';
		$html .= '</tr>';
		$i = 1;
		$subt_bille_usd = 0;
		foreach ($arrayBilleteUSD as $val) {
			$id_det_caja = $val[0];
			$forma_pago  = $val[1];
			$cantidad    = $val[2];
			$valor       = $val[3];
			$total       = $val[4];
			$tipo        = $val[5];
			$estado      = $val[6];

			if ($estado == 'PE') {
				$estado = 'PENDIENTE';
			} elseif ($estado == 'TE') {
				$estado = "TERMINADO";
			}

			$html .= '<tr>';
			$html .= '<td style="font-size:11px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . $i . '</td>';
			$html .= '<td style="font-size:11px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . round($cantidad, 2) . '</td>';
			$html .= '<td style="font-size:11px; text-align: right; border-right:1px solid; border-bottom:1px solid" align="right">' . number_format($valor, 2, '.', ',') . '</td>';
			$html .= '<td style="font-size:11px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($total, 2, '.', ',') . '</td>';
			$html .= '</tr>';

			$i++;
			$subt_bille_usd += $total;
		} //fin foreach
		$html .= '<tr>';
		$html .= '<td style="font-size:11px; text-align: left; " ></td>';
		$html .= '<td style="font-size:11px; text-align: left; " ></td>';
		$html .= '<td style="font-size:11px; text-align: center; "><strong>TOTAL:</strong></td>';
		$html .= '<td style="font-size:11px; text-align: right; " align="right"><strong>' . number_format($subt_bille_usd, 2, '.', ',') . '</strong></td>';
		$html .= '</tr>';
		$html .= '</table>';
	}

	// MONEDA USD
	if (count($arrayMonedaUSD) > 0) {
		$html .= '<table  style=" width:70%;border:1px solid black; border-radius: 5px; margin-top:30px;" align="left">';
		$html .= '<tr>';
		$html .= '<td colspan="4" style="width:100%;font-size:14px; text-align: center;  border-bottom:1px solid "><strong>MONEDA USD</strong></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid " ><strong>N</strong></td>';
		$html .= '<td style="font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Cant</strong></td>';
		$html .= '<td style="font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Ctvs USD</strong></td>';
		$html .= '<td style="font-size:11px; text-align: center;  border-bottom:1px solid "><strong>Total USD</strong></td>';
		$html .= '</tr>';
		$i = 1;
		$subt_mone_usd = 0;
		foreach ($arrayMonedaUSD as $val) {
			$id_det_caja = $val[0];
			$forma_pago  = $val[1];
			$cantidad    = $val[2];
			$valor       = $val[3];
			$total       = $val[4];
			$tipo        = $val[5];
			$estado      = $val[6];

			if ($estado == 'PE') {
				$estado = 'PENDIENTE';
			} elseif ($estado == 'TE') {
				$estado = "TERMINADO";
			}

			$html .= '<tr>';
			$html .= '<td style="font-size:11px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . $i . '</td>';
			$html .= '<td style="font-size:11px; text-align: right; border-right:1px solid; border-bottom:1px solid" >' . round($cantidad, 2) . '</td>';
			$html .= '<td style="font-size:11px; text-align: right; border-right:1px solid; border-bottom:1px solid" align="right">' . number_format($valor, 2, '.', ',') . '</td>';
			$html .= '<td style="font-size:11px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($total, 2, '.', ',') . '</td>';
			$html .= '</tr>';

			$i++;
			$subt_mone_usd += $total;
		} //fin foreach
		$html .= '<tr>';
		$html .= '<td style="font-size:11px; text-align: left; " ></td>';
		$html .= '<td style="font-size:11px; text-align: left;" ></td>';
		$html .= '<td style="font-size:11px; text-align: center;"><strong>TOTAL:</strong></td>';
		$html .= '<td style="font-size:11px; text-align: right;  " align="right"><strong>' . number_format($subt_mone_usd, 2, '.', ',') . '</strong></td>';
		$html .= '</tr>';
		$html .= '</table>';
	}


	$sql = "select * from saefpag where fpag_cod_modu=3 and fpag_cod_empr=$idempresa and fpag_cod_sucu=$sucursal and fpag_des_fpag<>'EFECTIVO'";
	//echo $sql; exit;
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {

			unset($array_depo);
			do {
				$descripcion = $oIfx->f('fpag_des_fpag');
				$codigo = $oIfx->f('fpag_cod_fpag');
				$sigla = $oIfx->f('fpag_cot_fpag');
				$array      = $class->generaCierreFormaPago($oIfxA, $idempresa, $sucursal, $usuario, $fecha_ifx, $sigla, $codigo);
				if (!empty($array)) {
					$html .= '<table  style="width:90%;border:1px solid black; border-radius: 5px; margin-top:30px; " align="left">';
					$html .= '<tr>';
					$html .= '<td colspan="4" style="width:100%;font-size:14px; text-align: center; border-bottom:1px solid "><strong>' . $descripcion . ' ' . $mone_sigla . '</strong></td>';
					$html .= '</tr>';
					$html .= '<tr>';
					$html .= '<td style="width:8%;font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Tran. </strong></td>';
					$html .= '<td style="width:10%;font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Doc.</strong></td>';
					$html .= '<td style="width:12%;font-size:11px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Factura </strong></td>';
					$html .= '<td style="width:15%;font-size:11px; text-align: center; border-bottom:1px solid "><strong>Valor</strong></td>';
					$html .= '</tr>';
					$i = 1;
					$subt_cheq = 0;
					foreach ($array as $val) {
						$fact_mum_preimp = $val[0];
						$fact_nse_fact   = $val[1];
						$fpag_des_fpag   = $val[2];
						$fxfp_val_fxfp   = $val[3];
						$fact_cod_fact   = $val[4];
						$fxfp_num_rete   = $val[5];

						$html .= '<tr>';
						$html .= '<td style="width:8%;font-size:11px; text-align: left; border-right:1px solid; border-bottom:1px solid" >' . $sigla . '</td>';
						$html .= '<td style="width:10%;font-size:11px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $fxfp_num_rete . '</td>';
						$html .= '<td style="width:12%;font-size:11px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $fact_mum_preimp . '</td>';
						$html .= '<td style="width:15%;font-size:11px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($fxfp_val_fxfp, 2, '.', ',') . '</td>';
						$html .= '</tr>';

						$i++;
						$subt_cheq += $fxfp_val_fxfp;
					} //fin foreach

					$html .= '<tr>';
					$html .= '<td style="width:8%;font-size:11px; text-align: left; border-right:1px solid; "></td>';
					$html .= '<td style="width:10%;font-size:11px; text-align: left; border-right:1px solid; "></td>';
					$html .= '<td style="width:12%;font-size:11px; text-align: center; border-right:1px solid; ">TOTAL:</td>';
					$html .= '<td style="width:15%;font-size:11px; text-align: right; " align="right">' . number_format($subt_cheq, 2, '.', ',') . '</td>';
					$html .= '</tr>';
					$html .= '</table>';
				}
			} while ($oIfx->SiguienteRegistro());
		}
	}


	$html_div  = '<div style="width: 80mm; margin: 0px; height: 155mm;" >'; //div padre
	$html_div .= $html;
	$html_div .= '<table style="margin-top:20mm;">
					<tr><td style="border-top:1 px solid;">' . $user_web . '<br>' . $empl_cod_empl . '<br>' . $fecha_cierre . ' &nbsp; ' . $hora . '</td></tr>
				 </table></div>';


	return $html_div;
}
