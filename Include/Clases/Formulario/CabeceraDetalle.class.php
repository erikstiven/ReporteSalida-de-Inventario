<? include('Formulario.class.php');


class CabeceraDetalle{
	var $Cabecera = '';
	var $Detalle = array();
	var $oRetencion = array();
	var $oPago = array();
	var $oCuotas = array();
	var $nNeDetalle=-1; 
	var $nNePago=-1; 
	var $nNeRetencion=-1; 
	var $nNeCuota=-1; 
	var $sTablaBDD='';
	var $sTablaTmpBDD='';
	function CabeceraDetalle(){
		$this->Cabecera = new Formulario;
	}
	function NuevaLineaDetalle($iPos){
		if($iPos>$this->nNeDetalle) 
			$this->NuevoRegistroDetalle();
	}	
	function NuevoRegistroDetalle(){
		$this->nNeDetalle++;
		$this->Detalle[$this->nNeDetalle] = new Formulario;
	}
	function EliminarLineaDetalle($iPos){
	   for($i=$iPos;$i<=$this->nNeDetalle-1;$i++){
			$this->Detalle[$i]=$this->Detalle[$i+1];
			$this->Detalle[$i]->LineaDetalle($i);
	   }
	   $this->nNeDetalle--;
	}
	function NuevaLineaPago($iPos){
		if($iPos>$this->nNePago) 
			$this->NuevoRegistroPago();
	}	
	function NuevoRegistroPago(){
		$this->nNePago++;
		$this->oPago[$this->nNePago] = new Formulario;
	}
	function EliminarLineaPago($iPos){
	   for($i=$iPos;$i<=$this->nNePago-1;$i++){
			$this->oPago[$i]=$this->oPago[$i+1];
			$this->oPago[$i]->LineaDetalle($i);
	   }
	   $this->nNePago--;
	}
	function NuevaLineaRetencion($iPos){
		if($iPos>$this->nNeRetencion) 
			$this->NuevoRegistroRetencion();
	}	
	function NuevoRegistroRetencion(){
		$this->nNeRetencion++;
		$this->oRetencion[$this->nNeRetencion] = new Fomulario;
	}
	function EliminarLineaRetencion($iPos){
	   for($i=$iPos;$i<=$this->nNeRetencion-1;$i++){
			$this->oRetencion[$i]=$this->oRetencion[$i+1];
			$this->oRetencion[$i]->LineaDetalle($i);
	   }
	   $this->nNeRetencion--;
	}
	function NuevaLineaCuota($iPos){
		if($iPos>$this->nNeCuota) 
			$this->NuevoRegistroCuota();
	}	
	function NuevoRegistroCuota(){
		$this->nNeCuota++;
		$this->oCuota[$this->nNeCuota] = new Formulario;
	}
	function EliminarLineaCuota($iPos){
	   for($i=$iPos;$i<=$this->nNeCuota-1;$i++){
			$this->oCuota[$i]=$this->oCuota[$i+1];
			$this->oCuota[$i]->LineaDetalle($i);
	   }
	   $this->nNeDetalle--;
	}
}
?>