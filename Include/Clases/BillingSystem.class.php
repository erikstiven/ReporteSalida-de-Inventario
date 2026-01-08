<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class BillingSystem{
	
	var $oSou;
	
	function conexionDB () {
		
		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
		
		$mysqli = new mysqli('10.0.0.56', 'sistransfer', 'bbb66TRANSFER', 'dbSosua');
		if ($mysqli->connect_errno) {
			$msj = die("Connection failed: " . mysqli_connect_error());
		}else{
			$msj = 'OK';
		}
				
		$this->oSou = $mysqli; 
		
		return $this->oSou;
	}
	
	function cierraConexionDB () {
		
		mysqli_close($this->oSou);
	}

}
?>