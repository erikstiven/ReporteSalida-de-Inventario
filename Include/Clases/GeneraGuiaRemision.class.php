<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class GeneraGuiaRemision{
	
	var $oConexion; 
	var $empr_nom_empr; 
	
	function __construct(){
		//$this->empr_nom_empr = $empr_nom_empr; 		
    } 
	
	function generaSaeguia($oConexion, $empr, $sucu, $guia_cod ){
		
		$sql = "select 
					guia_num_preimp, guia_cod_clpv, guia_fech_guia, guia_fec_servidor,
					guia_hor_fin, guia_cod_trta
					 from saeguia where
					guia_cod_empr = $empr and
					guia_cod_sucu = $sucu and
					guia_cod_guia = $guia_cod";		
		if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
				unset($array);
				do{
					$guia_num_preimp 	= $oConexion->f('guia_num_preimp');
					$guia_cod_clpv	 	= $oConexion->f('guia_cod_clpv');
					$guia_fech_guia		= fecha_mysql_funcYmd( $oConexion->f('guia_fech_guia') );
					$guia_fec_servidor 	= fecha_mysql_funcYmd( $oConexion->f('guia_fec_servidor') );
					$guia_hor_fin	 	= $oConexion->f('guia_hor_fin');
					$guia_cod_trta	 	= $oConexion->f('guia_cod_trta');
					
					$array[] = array($guia_num_preimp, $guia_cod_clpv, $guia_fech_guia, $guia_fec_servidor, $guia_hor_fin, $guia_cod_trta );
				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		return $array;
	}
	
	function generaSaedgui($oConexion, $empr, $sucu, $guia_cod ){
		
		$sql = "select  dgui_fac_dgui
					from saedgui where
					dgui_cod_empr = $empr and
					dgui_cod_sucu = $sucu and
					dgui_cod_guia = $guia_cod group by 1 ";
		if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
				unset($array);
				do{
					$dgui_cod_prod = $oConexion->f('dgui_cod_prod');
					$dgui_cod_bode = $oConexion->f('dgui_cod_bode');
					$dgui_cant_dgui= $oConexion->f('dgui_cant_dgui');
					$dgui_cod_unid = $oConexion->f('dgui_cod_unid');
					$dgui_fac_dgui = $oConexion->f('dgui_fac_dgui');
					$dgui_nom_prod = $oConexion->f('dgui_nom_prod');
					
					$array[] = array($dgui_cod_prod, $dgui_cod_bode, $dgui_cant_dgui, $dgui_cod_unid, $dgui_fac_dgui, $dgui_nom_prod  );
				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		return $array;
	}
		
	
	
}
?>