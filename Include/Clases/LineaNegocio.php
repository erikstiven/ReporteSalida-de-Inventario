<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class LineaNegocio{
	
	var $oConexion; 
	
	/// GUARDAR
	function guardar($oConexion, $idEmpresa,$descripcion,  $estado, $tipo_admi ){
		
		$sql="insert into clinico.linea_negocio(descripcion, estado, empresa, id_tipo_admi)
								values('".$descripcion."', '".$estado."', '".$idEmpresa."', '".$tipo_admi."')";
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
		$sql="select * from clinico.linea_negocio where empresa='$idEmpresa' $con_sql";
		if($oConexion->Query($sql)){
			if($oConexion->Numfilas()>0){
				do{
					$id     		  = $oConexion->f('id');
					$descripcion      = $oConexion->f('descripcion');
					$estado           = $oConexion->f('estado');
					$tipo_admi        = $oConexion->f('id_tipo_admi');
					
					
					$array[$id]=array(
						    "id"=>$id,
							"descripcion"=>$descripcion,
							"estado"=>$estado,
							"tipo_admi"=>$tipo_admi
							
					);	

				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->QueryT($sql);
		return $array;
	}
	/// MODIFICAR
	function update($oConexion, $idEmpresa,   $descripcion, $estado, $id, $tipo_admi){
		
		$sql="update clinico.linea_negocio set
					descripcion='$descripcion',
					estado='$estado',
					id_tipo_admi='$tipo_admi'					
					
				 where empresa='$idEmpresa' and id='$id'";
		
		$oConexion->QueryT($sql);
		return 'Modificado Correctamente';
	}
	
	//////// CUENTAS POR LINEA DE NEGOIO
	function cuentas_linea_insert($oConexion, $idEmpresa,  $id_bodega, $linea, $array, $id){
		
		$sql="insert into clinico.cuentas_linea_negocio (id_linea_negocio, id_linea_producto, id_bodega) VALUES('$id', '$linea', '$id_bodega')";
		$oConexion->QueryT($sql);
		
		$sql="select max(id) as id from clinico.cuentas_linea_negocio where id_linea_negocio='$id' and id_linea_producto='$linea' and id_bodega='$id_bodega'";
		$id=consulta_string($sql, 'id', $oConexion, 0);
		
		foreach($array as $arreglo){
			$tipo=$arreglo[1];
			$cuenta=$arreglo[2];
			
			$sql="insert into clinico.cuentas_linea_negocio_detalle (id_cuentas_linea_negocio, tipo_cuenta, cuenta) VALUES('$id', '$tipo', '$cuenta')";
			$oConexion->QueryT($sql);
		}
		return 'Creado Correctamente';
		
	}
	
	/// CONSULTA CUENTAS POR LINEA DE NEGOCIO
	function consultas_cuentas_linea($oConexion, $idEmpresa, $id){
		$sql="SELECT
				cuentas_linea_negocio_detalle.cuenta,
				cuentas_linea_negocio_detalle.tipo_cuenta
			FROM
				clinico.linea_negocio
			INNER JOIN clinico.cuentas_linea_negocio ON linea_negocio.id = cuentas_linea_negocio.id_linea_negocio
			INNER JOIN clinico.cuentas_linea_negocio_detalle ON cuentas_linea_negocio.id_linea_negocio = cuentas_linea_negocio_detalle.id_cuentas_linea_negocio 
			where linea_negocio.id=$id and empresa='$idEmpresa'";
		unset($array_detalles);
		$i=1;
		if($oConexion->Query($sql)){
			if($oConexion->Numfilas()>0){
				do{
					$array_detalles[$i]=array($i, $oConexion->f('tipo_cuenta'), $oConexion->f('cuenta'));
					$i++;
				}while($oConexion->SiguienteRegistro());
				
			}
		}
		return $array_detalles;
	}
}
?>