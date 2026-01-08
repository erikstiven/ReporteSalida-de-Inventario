<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');
require_once  path(DIR_INCLUDE).'Clases/Dbo.class.php';

class SecuencialDocLi{
	function ConsultaSecuLi( $tiponcf ){	
		global $DSN_S;
		$secu = '';
		try {		
			$con = mysqli_connect("127.0.0.1","root","root","dbcomprobantes");

			$sql = "LOCK TABLE tblncfs AS bsist READ;";		
			//mysqli_query($con,$sql);
			
			$sql = "SELECT MAX(NCF) AS secu FROM tblncfs AS bsist WHERE Tipo_NCF = '$tiponcf' ";
			if ($result = mysqli_query($con,$sql)){
				while ($fieldinfo = mysqli_fetch_assoc($result)){
					$secu = $fieldinfo["secu"];
				}
				// Free result set
				mysqli_free_result($result);
			}
			
			$sql = "UNLOCK TABLES";			
			
			//sleep(10);
			
			mysqli_close($con);
			
		} catch (Exception $e) {
			// rollback
			$secu = $e->getMessage();
		}
		return $secu;		
	}	
	
}
?>