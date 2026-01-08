<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class GeneraGuiaRemision{
	
	var $oConexion; 
	var $empr_nom_empr; 
	
	function __construct(){
		//$this->empr_nom_empr = $empr_nom_empr; 		
    } 
	
	function generaCabConteo($oConexion, $empr, $sucu, $conteo_cod ){
		
		$sql = "select c.preimpreso, c.fecha, c.hora, c.user_cod_user,
						c.fecha_server
						 from comercial.conteo_fisico c where
						c.empr_cod_empr = $empr and
						c.sucu_cod_sucu = $sucu and
						c.preimpreso    = '$conteo_cod'";		
		if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
				unset($array);
				do{
					$preimpreso 	= $oConexion->f('preimpreso');
					$fecha	 		= $oConexion->f('fecha');
					$hora	 		= $oConexion->f('hora');
					$fecha_server	= $oConexion->f('fecha_server');
					$user_cod_user	= $oConexion->f('user_cod_user');
					
					$array[] = array($preimpreso, $fecha, $hora, $fecha_server, $user_cod_user );
				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		return $array;
	}
	
	function generaDetConteo($oConexion, $empr, $sucu, $conteo_cod ){
		
		$sql = "select c.prod_cod_prod, c.bode_cod_bode, c.unid_cod_unid, c.cantidad
					 from comercial.conteo_fisico c where
					c.empr_cod_empr = $empr and
					c.sucu_cod_sucu = $sucu and
					c.preimpreso   = '$conteo_cod'
					order by 1  ";
		if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
				unset($array);
				do{
					$prod_cod_prod 	= $oConexion->f('prod_cod_prod');
					$bode_cod_bode 	= $oConexion->f('bode_cod_bode');
					$unid_cod_unid	= $oConexion->f('unid_cod_unid');
					$cantidad       = $oConexion->f('cantidad');
					
					$array[] = array($prod_cod_prod, $bode_cod_bode, $unid_cod_unid, $cantidad  );
				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		return $array;
	}
		
	
	
}
?>