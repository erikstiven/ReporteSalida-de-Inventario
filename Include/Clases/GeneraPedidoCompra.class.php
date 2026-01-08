<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class GeneraPedidoCompra{
	
	var $oConexion; 
	var $empr_nom_empr; 
	
	function __construct(){
		//$this->empr_nom_empr = $empr_nom_empr; 		
    } 
	
	function generaSaepedi($oConexion, $empr, $sucu, $pedi_num ){
		
		$sql = "select pedi_cod_pedi, pedi_res_pedi, pedi_det_pedi, pedi_fec_pedi,
					pedi_are_soli, pedi_lug_entr, pedi_uso_pedi, pedi_des_cons
					from saepedi where
					pedi_cod_empr = $empr and
					pedi_cod_sucu = $sucu and
					pedi_cod_pedi = '$pedi_num' ";		
		if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
				unset($array);
				do{
					$pedi_cod_pedi 	= $oConexion->f('pedi_cod_pedi');
					$pedi_res_pedi 	= $oConexion->f('pedi_res_pedi');
					$pedi_fec_pedi	= fecha_mysql_funcYmd( $oConexion->f('pedi_fec_pedi') );
					$pedi_det_pedi 	= $oConexion->f('pedi_det_pedi');
					$pedi_are_soli 	= $oConexion->f('pedi_are_soli');
					$pedi_lug_entr 	= $oConexion->f('pedi_lug_entr');
					$pedi_uso_pedi 	= $oConexion->f('pedi_uso_pedi');
					$pedi_des_cons  = $oConexion->f('pedi_des_cons');
					
					$array[] = array($pedi_cod_pedi, $pedi_res_pedi, $pedi_fec_pedi, $pedi_det_pedi, $pedi_are_soli, $pedi_lug_entr, $pedi_uso_pedi, $pedi_des_cons );
				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		return $array;
	}
	
	function generaSaedped($oConexion, $empr, $sucu, $pedi_num ){
		
		$sql = "select dped_cod_prod, dped_cod_bode, dped_cod_unid, 
					dped_can_ped, dped_prod_nom
					from saedped where
					dped_cod_empr =  $empr and
					dped_cod_sucu =  $sucu and
					dped_cod_pedi = '$pedi_num' ";
		if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
				unset($array);
				do{
					$dped_cod_prod = $oConexion->f('dped_cod_prod');
					$dped_cod_bode = $oConexion->f('dped_cod_bode');
					$dped_cod_unid = $oConexion->f('dped_cod_unid');
					$dped_can_ped  = $oConexion->f('dped_can_ped');
					$dped_prod_nom = $oConexion->f('dped_prod_nom');
					
					$array[] = array($dped_cod_prod, $dped_cod_bode, $dped_cod_unid, $dped_can_ped, $dped_prod_nom  );
				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		return $array;
	}
		
	
	
}
?>