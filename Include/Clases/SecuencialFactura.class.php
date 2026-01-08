<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');
require_once  path(DIR_INCLUDE).'Clases/Dbo.class.php';
require_once  path(DIR_INCLUDE).'conexiones/mysql_secu.inc.php';

class SecuencialDoc{
	function ConsultaSecu( $tiponcf ){	
		global $DSN_S;
		$secu = '';
		try {		
			$oConS = new Dbo;
			$oConS->DSN = $DSN_S;
			$oConS->Conectar();

			$sql = "LOCK TABLE tblncfs AS bsist READ";			
			$oConS->QueryT($sql);		

			$sql = "SELECT MAX(NCF) AS secu FROM tblncfs AS bsist WHERE Tipo_NCF = '$tiponcf' ";
			$secu = consulta_string_func($sql, 'secu', $oConS, 0);
		
			//sleep(10);
			
			$sql = "UNLOCK TABLES";			
			$oConS->QueryT($sql);		
			
		} catch (Exception $e) {
			// rollback
			$secu = $e->getMessage();
		}
		return $secu;		
	}
	
	
	function InsertarSecu( $documento, $tipo_ncf,  $ncf,	$clpv_cod,	$clpv_nom,	$clpv_ruc,	$id_empresa, $id_sucursal, $total,  $importe, $itbis, $isc, $cdt , $cnf_mod){
		global $DSN_S;
		$secu = '';
		try {		
			$oConS = new Dbo;
			$oConS->DSN = $DSN_S;
			$oConS->Conectar();	

			$sql = "LOCK TABLE tblncfs WRITE";			
			$oConS->QueryT($sql);	
			
			$sql = "insert into tblncfs ( 	fecha, 			documento, 			tipo_ncf, 			ncf, 			cliente, 
											nombre, 		cedula, 			localidad, 			estatus, 		cia ,
											total,			importe,			itbis,				isc, 			cdt,
											ncf_modificado	)  
									values( now(), 			'$documento',  		'$tipo_ncf',		'$ncf',			'$clpv_cod',
											'$clpv_nom',	'$clpv_ruc',		$id_sucursal,		'1',			$id_empresa,
											$total,			$importe,			$itbis,				$isc,			$cdt,
											'$cnf_mod'
											)";
			$oConS->QueryT($sql);		
		
			//sleep(10);
			
			$sql = "UNLOCK TABLES";			
			$oConS->QueryT($sql);		
			
			$secu = 'Ingresado....';
			
		} catch (Exception $e) {
			// rollback
			$secu = $e->getMessage();
		}
		return $secu;
	}
	
	
}
?>