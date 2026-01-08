<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class GeneraDetalleAsientoContable
{

	var $oConexion;
	var $empr_nom_empr;

	function __construct()
	{
		//$this->empr_nom_empr = $empr_nom_empr; 		
	}

	function generaAsientoContable($oConexion, $empr, $ejer, $mes, $cuenta)
	{

		if (empty($ejer)) {
			$ejer = 0;
		}

		if (empty($mes)) {
			$mes = 0;
		}

		$sql = "select a.asto_cod_sucu, d.asto_cod_asto, a.asto_vat_asto, a.asto_ben_asto, a.asto_fec_asto, a.asto_det_asto,
								a.asto_cod_modu, a.asto_cod_mone, asto_usu_asto, asto_user_web, asto_fec_serv, asto_cod_tidu
								from saedasi d, saeasto a
								where a.asto_cod_asto = d.asto_cod_asto and
								a.asto_cod_empr = d.asto_cod_empr and
								a.asto_cod_sucu = d.asto_cod_sucu and
								a.asto_cod_ejer = d.asto_cod_ejer and
								a.asto_num_prdo = d.dasi_num_prdo and 
								a.asto_cod_empr = $empr and
								a.asto_cod_ejer = $ejer and
								a.asto_num_prdo in ($mes) and
								a.asto_est_asto != 'AN' and
								d.dasi_cod_cuen = '$cuenta'
								group by a.asto_cod_sucu, d.asto_cod_asto, a.asto_vat_asto, a.asto_ben_asto, a.asto_fec_asto, a.asto_det_asto,
								a.asto_cod_modu, a.asto_cod_mone, asto_usu_asto, asto_user_web, asto_fec_serv, asto_cod_tidu
								order by 4,6,1";

		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				unset($array);
				do {
					$asto_cod_asto = $oConexion->f('asto_cod_asto');
					$asto_vat_asto = $oConexion->f('asto_vat_asto');
					$asto_ben_asto = $oConexion->f('asto_ben_asto');
					$asto_fec_asto = $oConexion->f('asto_fec_asto');
					$asto_det_asto = $oConexion->f('asto_det_asto');
					$asto_cod_modu = $oConexion->f('asto_cod_modu');
					$asto_cod_sucu = $oConexion->f('asto_cod_sucu');
					$asto_cod_mone = $oConexion->f('asto_cod_mone');

					$asto_usu_asto = $oConexion->f('asto_usu_asto');
					$asto_user_web = $oConexion->f('asto_user_web');
					$asto_fec_serv = $oConexion->f('asto_fec_serv');
					$asto_cod_tidu = $oConexion->f('asto_cod_tidu');

					$array[] = array(
						$asto_cod_asto, $asto_vat_asto, $asto_ben_asto, $asto_fec_asto, $asto_det_asto, $asto_cod_modu, $asto_cod_sucu, $asto_cod_mone,
						$asto_usu_asto, $asto_user_web, $asto_fec_serv, $asto_cod_tidu
					);
				} while ($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();

		return $array;
	}

	function informacionAsientoContable($oConexion, $empr, $sucu, $ejer, $mes, $asto)
	{

		if (empty($ejer)) {
			$ejer = 0;
		}

		if (empty($mes)) {
			$mes = 0;
		}




		$sql = "select a.asto_cod_asto, a.asto_cod_sucu, a.asto_vat_asto, a.asto_ben_asto, a.asto_fec_asto, a.asto_det_asto,
				a.asto_cod_modu, a.asto_usu_asto, a.asto_user_web, a.asto_fec_serv, a.asto_cod_tidu, asto_cod_mone, asto_cot_asto
				from saeasto a
				where 
				a.asto_cod_empr = $empr and
				a.asto_cod_sucu = $sucu and
				a.asto_cod_ejer = $ejer and
				a.asto_num_prdo = $mes and
				a.asto_cod_asto = '$asto'";


		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				unset($array);
				do {
					$asto_cod_asto = $oConexion->f('asto_cod_asto');
					$asto_vat_asto = $oConexion->f('asto_vat_asto');
					$asto_ben_asto = $oConexion->f('asto_ben_asto');
					$asto_fec_asto = $oConexion->f('asto_fec_asto');
					$asto_det_asto = $oConexion->f('asto_det_asto');
					$asto_cod_modu = $oConexion->f('asto_cod_modu');
					$asto_usu_asto = $oConexion->f('asto_usu_asto');
					$asto_user_web = $oConexion->f('asto_user_web');
					$asto_fec_serv = $oConexion->f('asto_fec_serv');
					$asto_cod_tidu = $oConexion->f('asto_cod_tidu');
					$asto_cod_mone = $oConexion->f('asto_cod_mone');
					$asto_cot_asto = $oConexion->f('asto_cot_asto');

					$array[] = array(
						$asto_cod_asto, $asto_vat_asto, $asto_ben_asto, $asto_fec_asto, $asto_det_asto, $asto_cod_modu,
						$asto_usu_asto, $asto_user_web, $asto_fec_serv, $asto_cod_tidu, $asto_cod_mone, $asto_cot_asto
					);
				} while ($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();

		return $array;
	}
// Agregado por B@nch
function diarioAsientoContableAux($oConexion, $empr, $sucu, $ejer, $mes, $asto)
{
	if (empty($ejer)) {
		$ejer = 0;
	}

	if (empty($mes)) {
		$mes = 0;
	}
	$sql = "select dasi_cod_cuen, dasi_cod_cact, ccos_cod_ccos, dasi_dml_dasi, dasi_cml_dasi,
			dasi_det_asi, dasi_num_depo, dasi_cod_dasi, dasi_cod_dir
			from saedasi
			where asto_cod_asto = '$asto' and
			asto_cod_empr = $empr and
			asto_cod_sucu = $sucu and
			asto_cod_ejer = $ejer and
			dasi_num_prdo = $mes
			order by 1,8";
	if ($oConexion->Query($sql)) {
		if ($oConexion->NumFilas() > 0) {
			unset($array);
			do {
				$dasi_cod_cuen = $oConexion->f('dasi_cod_cuen');
				$dasi_cod_cact = $oConexion->f('dasi_cod_cact');
				$ccos_cod_ccos = $oConexion->f('ccos_cod_ccos');
				$dasi_dml_dasi = $oConexion->f('dasi_dml_dasi');
				$dasi_cml_dasi = $oConexion->f('dasi_cml_dasi');
				$dasi_det_asi = $oConexion->f('dasi_det_asi');
				$dasi_num_depo = $oConexion->f('dasi_num_depo');
				$dasi_cod_dasi = $oConexion->f('dasi_cod_dasi');
				$dasi_cod_dir = $oConexion->f('dasi_cod_dir');
				$array[] = array(
					$dasi_cod_cuen, $dasi_cod_cact, $ccos_cod_ccos, $dasi_dml_dasi, $dasi_cml_dasi, $dasi_det_asi,
					$dasi_num_depo, $dasi_cod_dasi, $dasi_cod_dir
				);
			} while ($oConexion->SiguienteRegistro());
		}
	}
	$oConexion->Free();

	return $array;
}
//Terminado por B@nch

	function diarioAsientoContable($oConexion, $empr, $sucu, $ejer, $mes, $asto)
	{
		if (empty($ejer)) {
			$ejer = 0;
		}

		if (empty($mes)) {
			$mes = 0;
		}
		$sql = "select dasi_cod_cuen, dasi_cod_cact, ccos_cod_ccos, dasi_dml_dasi, dasi_cml_dasi,
				dasi_det_asi, dasi_num_depo
				from saedasi
				where asto_cod_asto = '$asto' and
				asto_cod_empr = $empr and
				asto_cod_sucu = $sucu and
				asto_cod_ejer = $ejer and
				dasi_num_prdo = $mes
				order by 1,3,4,5";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				unset($array);
				do {
					$dasi_cod_cuen = $oConexion->f('dasi_cod_cuen');
					$dasi_cod_cact = $oConexion->f('dasi_cod_cact');
					$ccos_cod_ccos = $oConexion->f('ccos_cod_ccos');
					$dasi_dml_dasi = $oConexion->f('dasi_dml_dasi');
					$dasi_cml_dasi = $oConexion->f('dasi_cml_dasi');
					$dasi_det_asi = $oConexion->f('dasi_det_asi');
					$dasi_num_depo = $oConexion->f('dasi_num_depo');

					$array[] = array(
						$dasi_cod_cuen, $dasi_cod_cact, $ccos_cod_ccos, $dasi_dml_dasi, $dasi_cml_dasi, $dasi_det_asi,
						$dasi_num_depo
					);
				} while ($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();

		return $array;
	}

	function directorioAsientoContable($oConexion, $empr, $sucu, $ejer, $mes, $asto)
	{

		if (empty($ejer)) {
			$ejer = 0;
		}

		if (empty($mes)) {
			$mes = 0;
		}

		$sql = "select dir_cod_dir, dir_cod_cli, tran_cod_modu, dir_cod_tran, dir_num_fact,
				dir_detalle, dir_fec_venc, dir_deb_ml, dir_cre_ml, dir_deb_mex, dir_cred_mex,
				dire_cod_asto, dire_cod_empr, dire_cod_sucu, asto_cod_ejer, asto_num_prdo
				from saedir
				where dire_cod_asto = '$asto' and
				dire_cod_empr = $empr and
				dire_cod_sucu = $sucu and
				asto_cod_ejer = $ejer and
				asto_num_prdo = $mes";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				unset($array);
				do {
					$dir_cod_dir = $oConexion->f('dir_cod_dir');
					$dir_cod_cli = $oConexion->f('dir_cod_cli');
					$tran_cod_modu = $oConexion->f('tran_cod_modu');
					$dir_cod_tran = $oConexion->f('dir_cod_tran');
					$dir_num_fact = $oConexion->f('dir_num_fact');
					$dir_detalle = $oConexion->f('dir_detalle');
					$dir_fec_venc = $oConexion->f('dir_fec_venc');
					$dir_deb_ml = $oConexion->f('dir_deb_ml');
					$dir_cre_ml = $oConexion->f('dir_cre_ml');
					$dir_deb_mex = $oConexion->f('dir_deb_mex');
					$dir_cred_mex = $oConexion->f('dir_cred_mex');
					$dire_cod_asto = $oConexion->f('dire_cod_asto');
					$dire_cod_empr = $oConexion->f('dire_cod_empr');
					$dire_cod_sucu = $oConexion->f('dire_cod_sucu');
					$asto_cod_ejer = $oConexion->f('asto_cod_ejer');
					$asto_num_prdo = $oConexion->f('asto_num_prdo');

					$array[] = array(
						$dir_cod_dir, $dir_cod_cli, $tran_cod_modu, $dir_cod_tran, $dir_num_fact, $dir_detalle,
						$dir_fec_venc, $dir_deb_ml, $dir_cre_ml, $dir_deb_mex, $dir_cred_mex, $dire_cod_asto,  
						$dire_cod_empr,  $dire_cod_sucu,  $asto_cod_ejer,  $asto_num_prdo
					);
				} while ($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();

		return $array;
	}

	function directorioAsientoContableDmcc($oConexion, $empr, $sucu, $ejer, $mes, $asto)
	{

		if (empty($ejer)) {
			$ejer = 0;
		}

		if (empty($mes)) {
			$mes = 0;
		}

		$sql = "SELECT dmcc_cod_dmcc, clpv_cod_clpv, dmcc_cod_modu, dmcc_cod_tran, dmcc_num_fac,
						dmcc_det_dmcc, dmcc_fec_ven, dmcc_deb_ml, dmcc_cre_ml, dmcc_deb_mext, dmcc_cre_mext,
						dmcc_cod_asto, dmcc_cod_empr, dmcc_cod_sucu, dmcc_cod_ejer
				from saedmcc
				where dmcc_cod_asto = '$asto' and
				dmcc_cod_empr = $empr and
				dmcc_cod_sucu = $sucu and
				dmcc_cod_ejer = $ejer";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				unset($array);
				do {
					$dmcc_cod_dmcc = $oConexion->f('dmcc_cod_dmcc');
					$clpv_cod_clpv = $oConexion->f('clpv_cod_clpv');
					$dmcc_cod_modu = $oConexion->f('dmcc_cod_modu');
					$dmcc_cod_tran = $oConexion->f('dmcc_cod_tran');
					$dmcc_num_fac = $oConexion->f('dmcc_num_fac');
					$dmcc_det_dmcc = $oConexion->f('dmcc_det_dmcc');
					$dmcc_fec_ven = $oConexion->f('dmcc_fec_ven');
					$dmcc_deb_ml = $oConexion->f('dmcc_deb_ml');
					$dmcc_cre_ml = $oConexion->f('dmcc_cre_ml');
					$dmcc_deb_mext = $oConexion->f('dmcc_deb_mext');
					$dmcc_cre_mext = $oConexion->f('dmcc_cre_mext');
					$dmcc_cod_asto = $oConexion->f('dmcc_cod_asto');
					$dmcc_cod_empr = $oConexion->f('dmcc_cod_empr');
					$dmcc_cod_sucu = $oConexion->f('dmcc_cod_sucu');
					$dmcc_cod_ejer = $oConexion->f('dmcc_cod_ejer');

					$array[] = array(
						$dmcc_cod_dmcc, $clpv_cod_clpv, $dmcc_cod_modu, $dmcc_cod_tran, $dmcc_num_fac, $dmcc_det_dmcc,
						$dmcc_fec_ven, $dmcc_deb_ml, $dmcc_cre_ml, $dmcc_deb_mext, $dmcc_cre_mext, $dmcc_cod_asto,  
						$dmcc_cod_empr,  $dmcc_cod_sucu,  $dmcc_cod_ejer,  ''
					);
				} while ($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();

		return $array;
	}

	function saedmccAsientoContable($oConexion, $empr, $sucu, $ejer, $mes, $asto)
	{

		if (empty($ejer)) {
			$ejer = 0;
		}

		if (empty($mes)) {
			$mes = 0;
		}

		$sql = "select dmcc_cod_dmcc, clpv_cod_clpv, dmcc_cod_modu, dmcc_cod_tran, dmcc_num_fac,
				dmcc_det_dmcc, dmcc_fec_ven, dmcc_deb_ml, dmcc_cre_ml, dmcc_deb_mext, dmcc_cre_mext,
				dmcc_cod_asto, dmcc_cod_empr, dmcc_cod_sucu, dmcc_cod_ejer
				from saedmcc
				where dmcc_cod_asto = '$asto' and
				dmcc_cod_empr = $empr and
				dmcc_cod_sucu = $sucu and
				dmcc_cod_ejer = $ejer ";

				//echo $sql;exit;
				
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				unset($array);
				do {
					$dir_cod_dir = $oConexion->f('dmcc_cod_dmcc');
					$dir_cod_cli = $oConexion->f('clpv_cod_clpv');
					$tran_cod_modu = $oConexion->f('dmcc_cod_modu');
					$dir_cod_tran = $oConexion->f('dmcc_cod_tran');
					$dir_num_fact = $oConexion->f('dmcc_num_fact');
					$dir_detalle = $oConexion->f('dmcc_det_dmcc');
					$dir_fec_venc = $oConexion->f('dmcc_fec_venc');
					$dir_deb_ml = $oConexion->f('dmcc_deb_ml');
					$dir_cre_ml = $oConexion->f('dmcc_cre_ml');
					$dir_deb_mex = $oConexion->f('dmcc_deb_mex');
					$dir_cred_mex = $oConexion->f('dmcc_cre_mext');
					$dire_cod_asto = $oConexion->f('dmcc_cod_asto');
					$dire_cod_empr = $oConexion->f('dmcc_cod_empr');
					$dire_cod_sucu = $oConexion->f('dmcc_cod_sucu');
					$asto_cod_ejer = $oConexion->f('dmcc_cod_ejer');
					$asto_num_prdo = $oConexion->f('asto_num_prdo');

					$array[] = array(
						$dir_cod_dir, $dir_cod_cli, $tran_cod_modu, $dir_cod_tran, $dir_num_fact, $dir_detalle,
						$dir_fec_venc, $dir_deb_ml, $dir_cre_ml, $dir_deb_mex, $dir_cred_mex, $dire_cod_asto,  
						$dire_cod_empr,  $dire_cod_sucu,  $asto_cod_ejer,  $asto_num_prdo
					);
				} while ($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();

		return $array;
	}


	function saedmcpAsientoContable($oConexion, $empr, $sucu, $ejer, $mes, $asto)
	{

		if (empty($ejer)) {
			$ejer = 0;
		}

		if (empty($mes)) {
			$mes = 0;
		}

		$sql = "SELECT
		dmcp_cod_dmcp,
		clpv_cod_clpv,
		dmcp_cod_modu,
		dmcp_cod_tran,
		dmcp_num_fac,
		dmcp_det_dcmp,
		dmcp_fec_ven,
		dcmp_deb_ml,
		dcmp_cre_ml,
		dmcp_deb_mext,
		dmcp_cre_mext,
		dmcp_cod_asto,
		dmcp_cod_empr,
		dmcp_cod_sucu,
		dmcp_cod_ejer 
		FROM
			saedmcp
				where dmcp_cod_asto = '$asto' and
				dmcp_cod_empr = $empr and
				dmcp_cod_sucu = $sucu and
				dmcp_cod_ejer = $ejer ";

				//echo $sql;exit;
				
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				unset($array);
				do {
					$dir_cod_dir = $oConexion->f('dmcp_cod_dmcp');
					$dir_cod_cli = $oConexion->f('clpv_cod_clpv');
					$tran_cod_modu = $oConexion->f('dmcp_cod_modu');
					$dir_cod_tran = $oConexion->f('dmcp_cod_tran');
					$dir_num_fact = $oConexion->f('dmcp_num_fac');
					$dir_detalle = $oConexion->f('dmcp_det_dcmp');
					$dir_fec_venc = $oConexion->f('dmcp_fec_ven');
					$dir_deb_ml = $oConexion->f('dcmp_deb_ml');
					$dir_cre_ml = $oConexion->f('dcmp_cre_ml');
					$dir_deb_mex = $oConexion->f('dmcc_deb_mex');
					$dir_cred_mex = $oConexion->f('dmcp_cre_mext');
					$dire_cod_asto = $oConexion->f('dmcp_cod_asto');
					$dire_cod_empr = $oConexion->f('dmcp_cod_empr');
					$dire_cod_sucu = $oConexion->f('dmcp_cod_sucu');
					$asto_cod_ejer = $oConexion->f('dmcp_cod_ejer');
					$asto_num_prdo = $oConexion->f('asto_num_prdo');

					$array[] = array(
						$dir_cod_dir, $dir_cod_cli, $tran_cod_modu, $dir_cod_tran, $dir_num_fact, $dir_detalle,
						$dir_fec_venc, $dir_deb_ml, $dir_cre_ml, $dir_deb_mex, $dir_cred_mex, $dire_cod_asto,  
						$dire_cod_empr,  $dire_cod_sucu,  $asto_cod_ejer,  $asto_num_prdo
					);
				} while ($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();

		return $array;
	}

	function retencionAsientoContable($oConexion, $empr, $sucu, $ejer, $mes, $asto)
	{

		if (empty($ejer)) {
			$ejer = 0;
		}

		if (empty($mes)) {
			$mes = 0;
		}

		$sql = "select ret_cta_ret, ret_porc_ret, ret_bas_imp, ret_valor, ret_num_ret,
				ret_detalle, ret_num_fact, ret_ser_ret, ret_cod_clpv, ret_fec_ret
				from saeret
				where rete_cod_asto = '$asto' and
				asto_cod_empr = $empr and
				asto_cod_sucu = $sucu and
				asto_cod_ejer = $ejer and
				asto_num_prdo = $mes";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				unset($array);
				do {
					$ret_cta_ret = $oConexion->f('ret_cta_ret');
					$ret_porc_ret = $oConexion->f('ret_porc_ret');
					$ret_bas_imp = $oConexion->f('ret_bas_imp');
					$ret_valor = $oConexion->f('ret_valor');
					$ret_num_ret = $oConexion->f('ret_num_ret');
					$ret_detalle = $oConexion->f('ret_detalle');
					$ret_num_fact = $oConexion->f('ret_num_fact');
					$ret_ser_ret = $oConexion->f('ret_ser_ret');
					$ret_cod_clpv = $oConexion->f('ret_cod_clpv');
					$ret_fec_ret = $oConexion->f('ret_fec_ret');

					$array[] = array(
						$ret_cta_ret, $ret_porc_ret, $ret_bas_imp, $ret_valor, $ret_num_ret, $ret_detalle,
						$ret_num_fact, $ret_ser_ret, $ret_cod_clpv, $ret_fec_ret
					);
				} while ($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();

		return $array;
	}

	function adjuntosAsientoContable($oConexion, $empr, $sucu, $ejer, $mes, $asto)
	{


		if (empty($ejer)) {
			$ejer = 0;
		}

		if (empty($mes)) {
			$mes = 0;
		}

		$sql = "select titulo, ruta
				from comercial.adjuntos
				where 
				id_empresa = $empr and
				id_sucursal = $sucu and
				id_ejer = $ejer and
				id_prdo = $mes and
				asto = '$asto'";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				unset($array);
				do {
					$titulo = $oConexion->f('titulo');
					$ruta = $oConexion->f('ruta');

					$array[] = array($titulo, $ruta);
				} while ($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();

		return $array;
	}
}
