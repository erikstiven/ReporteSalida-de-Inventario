<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class GeneraRequisicionInv{
	
	var $oConexion; 
	var $empr_nom_empr; 
	
	function __construct(){
		//$this->empr_nom_empr = $empr_nom_empr; 		
    } 
	
	function generaSaerequ($oConexion, $empr, $sucu, $requ_cod ){
		
		$sql = "select r.requ_cod_treq, r.requ_cod_empl, r.requ_est_requ,
					r.requ_fec_requ, r.requ_cod_bode, r.requ_bode_sol, 
					requ_user_web, requ_num_preimp, requ_fec_aprob, requ_hora_aprob
					from saerequ r where
					r.requ_cod_empr = $empr and
					r.requ_cod_sucu = $sucu and
					r.requ_cod_requ = $requ_cod ";		
		if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
				unset($array);
				do{
					$requ_cod_treq 	= $oConexion->f('requ_cod_treq');
					$requ_cod_empl 	= $oConexion->f('requ_cod_empl');
					$requ_est_requ 	= $oConexion->f('requ_est_requ');
					$requ_fec_requ 	= $oConexion->f('requ_fec_requ');
					$requ_cod_bode 	= $oConexion->f('requ_cod_bode');
					$requ_bode_sol 	= $oConexion->f('requ_bode_sol');
					$requ_user_web  = $oConexion->f('requ_user_web');
					$requ_num_preimp= $oConexion->f('requ_num_preimp');
					$requ_fec_aprob = $oConexion->f('requ_fec_aprob');
					$requ_hora_aprob= $oConexion->f('requ_hora_aprob');
					
					$array[] = array($requ_cod_treq, $requ_cod_empl, $requ_est_requ, $requ_fec_requ, $requ_cod_bode, $requ_bode_sol, $requ_user_web, $requ_num_preimp,
									 $requ_fec_aprob,$requ_hora_aprob );
				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		return $array;
	}
	
	function generaSaedreq($oConexion, $empr, $sucu, $requ_cod ){
		
		$sql = "select 
						dreq_cod_prod, dreq_cod_bode, dreq_can_dreq, dreq_cod_unid
						from saedreq where
						dreq_cod_empr = $empr and
						dreq_cod_sucu = $sucu and
						dreq_cod_requ = $requ_cod  ";
		if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
				unset($array);
				do{
					$dreq_cod_prod = $oConexion->f('dreq_cod_prod');
					$dreq_cod_bode = $oConexion->f('dreq_cod_bode');
					$dreq_can_dreq = $oConexion->f('dreq_can_dreq');
					$dreq_cod_unid = $oConexion->f('dreq_cod_unid');
					
					$array[] = array($dreq_cod_prod, $dreq_cod_bode, $dreq_can_dreq, $dreq_cod_unid );
				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		return $array;
	}
		
	
	
}
?>