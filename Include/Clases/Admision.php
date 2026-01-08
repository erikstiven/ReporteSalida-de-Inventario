<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class Admision{
	
	### UPDATE
	function update($oConexion, $idEmpresa, $datos, $campos, $condicion){
		$sql="update datos_clpv set ";
	
		foreach($campos as $arreglo){
			$campo=$arreglo;
			$dato=$datos[$arreglo];	
			if($campo!=$condicion){
				$sql.=$campo."='".$dato."', ";
			}
			
		}
		$sql=substr($sql,0,-2);
		$sql.= " where ".$condicion."= '".$dato=$datos[$condicion]."'";	

		$oConexion->Queryt($sql);
		$mensaje='Modificado correctamente';
		return $mensaje;
	}
	#### CONSULTAR
	function cunsultar($oConexion, $idEmpresa, $condicion){
		
		unset($array);
		
		$sql="select * from datos_clpv where $condicion";
	//	echo $sql;
		if($oConexion->Query($sql)){
			if($oConexion->Numfilas()>0){
				do{
					$id_dato      	  = $oConexion->f('id_dato');
					$id_clpv          = $oConexion->f('id_clpv');
					$id_tipo_adm      = $oConexion->f('id_tipo_adm');
					$nom_clpv         = $oConexion->f('nom_clpv');
					$ruc_clpv         = $oConexion->f('ruc_clpv');		
					$fecha_admision   = $oConexion->f('fecha_admision');		
					$resp_precio      = $oConexion->f('resp_precio');		
					
					$array[$id_dato]=array(
						    "id_dato"=>$id_dato,
							"id_clpv"=>$id_clpv,
							"id_tipo_adm"=>$id_tipo_adm,
							"nom_clpv"=>$nom_clpv,
							"ruc_clpv"=>$ruc_clpv,
							"fecha_admision"=>$fecha_admision,
							"resp_precio"=>$resp_precio
					);	

				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->QueryT($sql);
		return $array;
	}
	
}
?>