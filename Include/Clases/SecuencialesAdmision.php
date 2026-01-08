<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class SecuencialesAdmision{
	
	var $oConexion; 
	
	/// GUARDAR
	function guardar($oConexion, $idEmpresa,  $idUser, $sigla, $anio, $secuencia, 
					$anio_uso, $estado, $fecha_registro, $tipo_admin, $descripcion){
		
		$sql="insert into secuenciales_admision(descripcion, siglas, anio, secuencia, anio_uso, estado,
											   fecha_registro, user_web, id_tipo_admision, id_empresa)
								values('".$descripcion."', '".$sigla."', '".$anio."', '".$secuencia."', '".$anio_uso."','".$estado."',
									   '".$fecha_registro."', '".$idUser."', '".$tipo_admin."', '".$idEmpresa."')";
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
		$sql="select * from secuenciales_admision where id_empresa='$idEmpresa' $con_sql";
		if($oConexion->Query($sql)){
			if($oConexion->Numfilas()>0){
				do{
					$id     		  = $oConexion->f('id');
					$descripcion      = $oConexion->f('descripcion');
					$siglas           = $oConexion->f('siglas');
					$anio		      = $oConexion->f('anio');
					$secuencia        = $oConexion->f('secuencia');
					$anio_uso    	  = $oConexion->f('anio_uso');
					$estado      	  = $oConexion->f('estado');
					$fecha_registro   = $oConexion->f('fecha_registro');
					$id_tipo_admision = $oConexion->f('id_tipo_admision');
					
					$array[$id]=array(
						    "id"=>$id,
							"descripcion"=>$descripcion,
							"siglas"=>$siglas,
							"anio"=>$anio,
							"secuencia"=>$secuencia, 
							"anio_uso"=>$anio_uso, 
							"estado"=>$estado,
							"fecha_registro"=>$fecha_registro,
							"id_tipo_admision"=>$id_tipo_admision 
					);	

				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->QueryT($sql);
		return $array;
	}
	/// MODIFICAR
	function update($oConexion, $idEmpresa,  $idUser, $sigla, $anio, $secuencia, 
					$anio_uso, $estado, $fecha_registro, $tipo_admin, $descripcion,$id){
		
		$sql="update secuenciales_admision set
					descripcion='$descripcion',
					siglas='$siglas',
					anio='$anio',
					secuencia='$secuencia', 
					anio_uso='$anio_uso', 
					estado='$estado',
					fecha_registro='$fecha_registro',
					id_tipo_admision='$id_tipo_admision'	
				 where id_empresa='$idEmpresa' and id='$id'";
		
		$oConexion->QueryT($sql);
		return 'Modificado Correctamente';
	}
	
}
?>