<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class Facturacion{
	
	var $oConexion; 
	
	### CONSULTAR
	function cunsultar($oConexion, $idEmpresa, $condicion){
		
		unset($array);
		
		$sql="select * from saefact where $condicion";
		if($oConexion->Query($sql)){
			if($oConexion->Numfilas()>0){
				do{
					$fact_cod_fact	  = $oConexion->f('fact_cod_fact');
					$fact_cod_adm  	  = $oConexion->f('fact_cod_adm');
					$fact_fech_fact   = $oConexion->f('fact_fech_fact');
					$fact_cod_clpv    = $oConexion->f('fact_cod_clpv');
					$fact_num_preimp  = $oConexion->f('fact_num_preimp');
					$fact_nom_cliente = $oConexion->f('fact_nom_cliente');
					$fact_ruc_cliente = $oConexion->f('fact_ruc_clie');
					$fact_cod_pac     = $oConexion->f('fact_cod_pac');
					$fact_user_web    = $oConexion->f('fact_user_web');
					
					$array[$fact_cod_fact]=array(
						    "fact_cod_fact"=>$fact_cod_fact,
							"fact_cod_adm"=>$fact_cod_adm,
							"fact_fech_fact"=>$fact_fech_fact,
							"fact_cod_clpv"=>$fact_cod_clpv,
							"fact_num_preimp"=>$fact_num_preimp, 
							"fact_nom_cliente"=>$fact_nom_cliente, 
							"fact_ruc_cliente"=>$fact_ruc_cliente,
							"fact_cod_pac"=>$fact_cod_pac,
							"fact_user_web"=>$fact_user_web
					);	

				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->QueryT($sql);
		return $array;
	}
	####### CONSULTA TABLA DETALLE
	function cunsultar_detalle($oConexion, $idEmpresa, $condicion){
		
		unset($array);
		$sql="select dfac_cod_dfact, dfac_cod_prod, dfac_cant_dfac, dfac_precio_dfac, dfac_mont_total,
			         dfac_por_iva,  dfac_nom_prod, dfac_cod_med, dfac_des1_dfac from saedfac  $condicion";
		//echo $sql;exit;
		if($oConexion->Query($sql)){
			if($oConexion->Numfilas()>0){
				do{
					$dfac_cod_dfact    = $oConexion->f('dfac_cod_dfact');
					$dfac_cod_prod 	   = $oConexion->f('dfac_cod_prod');
					$dfac_cant_dfac    = $oConexion->f('dfac_cant_dfac');
					$dfac_precio_dfac  = $oConexion->f('dfac_precio_dfac');
					$dfac_mont_total   = $oConexion->f('dfac_mont_total');
					$dfac_por_iva      = $oConexion->f('dfac_por_iva');
					$dfac_nom_prod     = $oConexion->f('dfac_nom_prod');
					$dfac_cod_med      = $oConexion->f('dfac_cod_med');
					$dfac_des1_dfac    = $oConexion->f('dfac_des1_dfac');
					
					$array[$dfac_cod_dfact]=array(
						    "dfac_cod_dfact"=>$dfac_cod_dfact,
							"dfac_cod_prod"=>$dfac_cod_prod,
							"dfac_cant_dfac"=>$dfac_cant_dfac,
							"dfac_precio_dfac"=>$dfac_precio_dfac,
							"dfac_mont_total"=>$dfac_mont_total, 
							"dfac_por_iva"=>$dfac_por_iva, 
							"dfac_nom_prod"=>$dfac_nom_prod,
							"dfac_cod_med"=>$dfac_cod_med,
							"dfac_des1_dfac"=>$dfac_des1_dfac
					);	

				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->QueryT($sql);
		return $array;
	}
	
}
?>