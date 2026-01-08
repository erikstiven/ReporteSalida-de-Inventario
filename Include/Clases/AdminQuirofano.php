<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class AdminQuirofano{
	
	var $oConexion; 
	
	#### GUARDAR
	function guardar($oConexion, $idempresa, $user_web,  $fecha, $id_dato, 
					  $fecha_registro, $tipo_admision, $decripcion, $nom_clpv, $cod_clpv, $detalles ){
		try{
			$oConexion->QueryT('BEGIN');
			###inserta en la tabla padre
			$sql="insert into admin_quirofano(id_dato, cod_clpv, fecha, id_user, fecha_registro,
										observacion, nom_clpv, id_empresa)
										values('".$id_dato."', '".$cod_clpv."', '".$fecha."', '".$user_web."', '".$fecha_registro."',
											   '".$decripcion."',  '".$nom_clpv."', '".$idempresa."')";
			$oConexion->QueryT($sql);
			$sql="select max(id) as id from admin_quirofano where id_dato='$id_dato' and fecha='$fecha'";
			$id_admi_quiro= consulta_string($sql, 'id', $oConexion, 0);
	
			###inserta en la tabla detalle
			foreach($detalles as $arreglo){
				$cod_prod   = $arreglo['Codigo Item'];
				$cod_clpv   = $arreglo['Medico'];
				$cod_bodega = $arreglo['Bodega'];
				$precio 	= $arreglo['Precio'];
				$costo 		= $arreglo['Costo'];
				$cantidad   = $arreglo['Cantidad'];
				$total 		= $arreglo['Total'];

				$sql="insert into admin_quirofano_detalle(id_admin_quirofano, cod_prod, cod_clpv, cod_bodega, precio,
												 		 costo, cantidad, total_precio)
										values('".$id_admi_quiro."', '".$cod_prod."', '".$cod_clpv."', '".$cod_bodega."', '".$precio."',
											   '".$costo."',  '".$cantidad."', '".$total."')";
				$oConexion->QueryT($sql);
			}
			$oConexion->QueryT('COMMIT');
			$oReturn="Ingresado Correctamente";
		}catch (Exception $e){
			$oConexion->QueryT('ROLLBACK');
			$oReturn=($e->getMessage());
		}
		

						

		return $oReturn;
	}
	
	### CONSULTAR
	function cunsultar($oConexion, $idEmpresa, $id){
		
		unset($array);
		$con_sql='';
		if($id>0){
			$con_sql="and id ='$id'";
		}
		$sql="select * from admin_quirofano where id_empresa='$idEmpresa' $con_sql";
		if($oConexion->Query($sql)){
			if($oConexion->Numfilas()>0){
				do{
					$id     		  = $oConexion->f('id');
					$id_dato      	  = $oConexion->f('id_dato');
					$cod_clpv         = $oConexion->f('cod_clpv');
					$fecha		      = $oConexion->f('fecha');
					$id_user          = $oConexion->f('id_user');
					$fecha_registro   = $oConexion->f('fecha_registro');
					$observacion      = $oConexion->f('observacion');
					$nom_clpv         = $oConexion->f('nom_clpv');
					
					$array[$id]=array(
						    "id"=>$id,
							"observacion"=>$observacion,
							"id_user"=>$id_user,
							"anio"=>$anio,
							"fecha"=>$fecha, 
							"cod_clpv"=>$cod_clpv, 
							"id_dato"=>$id_dato,
							"fecha_registro"=>$fecha_registro,
							"paciente"=>$nom_clpv
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
		$sql="select * from admin_quirofano_detalle where  $condicion";
		//echo $sql;exit;
		if($oConexion->Query($sql)){
			if($oConexion->Numfilas()>0){
				do{
					$id     	  = $oConexion->f('id');
					$id_quirofano = $oConexion->f('id_admin_quirofano');
					$cod_prod     = $oConexion->f('cod_prod');
					$cod_clpv     = $oConexion->f('cod_clpv');
					$cod_bodega   = $oConexion->f('cod_bodega');
					$precio       = $oConexion->f('precio');
					$costo        = $oConexion->f('costo');
					$cantidad     = $oConexion->f('cantidad');
					$total_precio = $oConexion->f('total_precio');
					
					$array[$id]=array(
						    "id"=>$id,
							"id_quirofano"=>$id_quirofano,
							"cod_prod"=>$cod_prod,
							"cod_clpv"=>$cod_clpv,
							"cod_bodega"=>$cod_bodega, 
							"precio"=>$precio, 
							"costo"=>$costo,
							"cantidad"=>$cantidad,
							"total_precio"=>$total_precio
					);	

				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->QueryT($sql);
		return $array;
	}
	
}
?>