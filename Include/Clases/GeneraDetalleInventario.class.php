<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class GeneraDetalleInventario
{

	var $oConexion;
	var $empr_nom_empr;

	function __construct()
	{
		//$this->empr_nom_empr = $empr_nom_empr; 		
	}

	function generaSaeminv($oConexion, $empr, $sucu, $ejer, $mes, $tran_cod, $minv_cod)
	{

		$sql = "select minv_num_sec, minv_cod_clpv,
					minv_fmov , minv_fac_prov, minv_tot_minv,
					minv_iva_valo, minv_cm1_minv, minv_hor_minv,minv_cod_mone
					from saeminv where
					minv_cod_empr = $empr and
					minv_cod_sucu = $sucu and
					minv_num_comp = $minv_cod and
					minv_cod_tran = '$tran_cod' ";


		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				unset($array);
				do {
					$minv_num_sec 	= $oConexion->f('minv_num_sec');
					$minv_cod_clpv 	= $oConexion->f('minv_cod_clpv');
					$minv_fmov 		= fecha_mysql_funcYmd($oConexion->f('minv_fmov'));
					$minv_fac_prov 	= $oConexion->f('minv_fac_prov');
					$minv_tot_minv 	= $oConexion->f('minv_tot_minv');
					$minv_iva_valo 	= $oConexion->f('minv_iva_valo');
					$minv_cm1_minv 	= $oConexion->f('minv_cm1_minv');
					$minv_hor_minv	 = $oConexion->f('minv_hor_minv');
					$minv_cod_mone	 = $oConexion->f('minv_cod_mone');

					$array[] = array($minv_num_sec, $minv_cod_clpv, $minv_fmov, $minv_fac_prov, $minv_tot_minv, $minv_iva_valo, $minv_cm1_minv, $minv_hor_minv, $minv_cod_mone);
				} while ($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();

		return $array;
	}

	function generaSaedmov($oConexion, $empr, $sucu, $ejer, $mes, $minv_cod)
	{

		$sql = "select 
					dmov_cod_prod, dmov_cod_bode, dmov_bod_envi, 
					dmov_cod_ccos, dmov_cod_cuen, dmov_can_dmov, 
					dmov_cun_dmov, dmov_cto_dmov, dmov_cad_lote, 
					dmov_ela_lote, dmov_cod_lote, dmov_prec_vent
					from saedmov where
					dmov_cod_empr = $empr and
					dmov_cod_sucu = $sucu and
					dmov_num_comp = $minv_cod ";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				unset($array);
				do {
					$dmov_cod_prod = $oConexion->f('dmov_cod_prod');
					$dmov_cod_bode = $oConexion->f('dmov_cod_bode');
					$dmov_bod_envi = $oConexion->f('dmov_bod_envi');
					$dmov_cod_ccos = $oConexion->f('dmov_cod_ccos');
					$dmov_cod_cuen = $oConexion->f('dmov_cod_cuen');
					$dmov_can_dmov = $oConexion->f('dmov_can_dmov');
					$dmov_cun_dmov = $oConexion->f('dmov_cun_dmov');
					$dmov_cto_dmov = $oConexion->f('dmov_cto_dmov');
					$dmov_cad_lote = $oConexion->f('dmov_cad_lote');
					$dmov_ela_lote = $oConexion->f('dmov_ela_lote');
					$dmov_cod_lote = $oConexion->f('dmov_cod_lote');
					$dmov_prec_vent = $oConexion->f('dmov_prec_vent');

					$array[] = array(
						$dmov_cod_prod,
						$dmov_cod_bode,
						$dmov_bod_envi,
						$dmov_cod_ccos,
						$dmov_cod_cuen,
						$dmov_can_dmov,
						$dmov_cun_dmov,
						$dmov_cto_dmov,
						$dmov_cad_lote,
						$dmov_ela_lote,
						$dmov_cod_lote,
						$dmov_prec_vent
					);
				} while ($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();

		return $array;
	}
}
