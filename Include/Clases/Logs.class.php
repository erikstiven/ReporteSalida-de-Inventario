<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class Logs{
	
	var $idModulo; 
	var $pathLog;
	var $nameLog;
	
	function __construct($idModulo){
		
		if($idModulo == 4){
			$file = 'CXP';
		}
		
		$this->pathLog = path(DIR_INCLUDE).'/Logs/'.$file;
		$this->nameLog = 'log_'.date("dmY").'.txt';
    } 

	function crearLog($factura, $asto, $log){
		
//		$pathLog = $this->pathLog;
//		$nameLog = $this->nameLog;
//
//		$a = fopen($pathLog.'/'.$nameLog, 'a');
//
//		fwrite($a,"[".date("d/m/Y H:i:s")."][$factura][$asto]: $log\r\n");
//		fclose($a);
	}
	
	
}
?>
