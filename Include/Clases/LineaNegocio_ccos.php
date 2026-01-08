<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class LineaNegocio_ccos{
	
	var $oConexion; 
	
	/// GUARDAR
	function guardar($oConexion, $idempresa, $costo, $convenio ){
		
		$sql="insert into clinico.centro_costo_responsables(id_responsable_cuenta, centro_costo, id_empresa)
								values('".$convenio."', '".$costo."', '".$idempresa."')";
		$oConexion->QueryT($sql);
		return 'Registrado Correctamente';
	}
	
	/// CONSULTAR
	function cunsultar($oConexion, $idEmpresa, $id){
		
		unset($array);
		$con_sql='';
		if($id>0){
			$con_sql="and id ='$id'";
		}
		$sql="select * from clinico.centro_costo_responsables where id_empresa='$idEmpresa' $con_sql";
		if($oConexion->Query($sql)){
			if($oConexion->Numfilas()>0){
				do{
					$id     	  = $oConexion->f('id');
					$convenio     = $oConexion->f('id_responsable_cuenta');
					$centro_costo = $oConexion->f('centro_costo');
					
					
					$array[$id]=array(
						    "id"=>$id,
							"convenio"=>$convenio,
							"centro_costo"=>$centro_costo
							
					);	

				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->QueryT($sql);
		return $array;
	}
	/// MODIFICAR
	function update($oConexion, $idEmpresa,   $centro_costo, $convenio, $id){
		
		$sql="update clinico.centro_costo_responsables set
					id_responsable_cuenta='$convenio',
					centro_costo='$centro_costo'					
					
				 where id_empresa='$idEmpresa' and id='$id'";
		
		$oConexion->QueryT($sql);
		return 'Modificado Correctamente';
	}
	
}
?>