<?
include_once('Campo.class.php');

class Formulario{
	var $cCampos=array();
	var $nNeCampos=-1;
	var $sBDD='';
	var $sTablaBDD='';
	var $sTablaTmpBDD='';
	var $sTipoBDD='';
	var $nBloqueado=0;
	var $sCampoClave='';
	var $sCampoClavePrimaria='';
	var $oConBDD;
	var $DNS='';
	/**************************************************************
	@ Cosntructor de la clase Formulario
	 **************************************************************/
	function __construct($DSN = ''){
		$this->oConBDD = new Dbo;
		$this->DSN = ($DSN != '') ? $DSN : ''; // Asigna el valor o un valor predeterminado
		$this->oConBDD->DSN = $this->DSN;
	}

	/**************************************************************
	@ Bloqueado
	 **************************************************************/
	function Bloqueado(){
		if($this->nBloqueado==0)
			return false;
		else
			return true;
	}

	/**************************************************************
	@ Campos
	 **************************************************************/
	function Campos($iPos){
		if($iPos>$nNeCampos) $this->NuevoCampo($sNNomC);
		return $cCampos[$iPos];
	}

	/**************************************************************
	@ Nuevo Campo
	 **************************************************************/
	function NuevoCampo($sNNomC){
		$this->nNeCampos++;
		$this->cCampos[$sNNomC] = new Campo ($this->DSN);
		$this->cCampos[$sNNomC]->BDD=$this->sBDD;
		$this->cCampos[$sNNomC]->sTablaBDD=$this->sTablaBDD;
	}
	/**************************************************************
	@ Agregar Campo Texto
	 **************************************************************/
	function AgregarCampoTexto($sNNomC,$sNDes,$bNReq,$sNValDef,$nNTam,$nNLon,$boostrap = false){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,0,$bNReq,$sNValDef,'text',$nNTam,$nNLon,$boostrap);
	}

	//        Agregar campo texto con tipo de letra rojo
	function AgregarCampoTextoRojo($sNNomC,$sNDes,$bNReq,$sNValDef,$nNTam,$nNLon,$boostrap = false){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,0,$bNReq,$sNValDef,'textRojo',$nNTam,$nNLon,$boostrap);
	}

	/**************************************************************
	@ Agregar Campo Password
	 **************************************************************/
	function AgregarCampoPassword($sNNomC,$sNDes,$bNReq,$sNValDef,$nNTam,$nNLon){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,0,$bNReq,$sNValDef,'password',$nNTam,$nNLon);
	}
	/**************************************************************
	@ Agregar Campo Numerico
	 **************************************************************/
	function AgregarCampoNumerico($sNNomC,$sNDes,$bNReq,$sNValDef,$nNTam,$nNLon,$boostrap = false, $class_add = "text-right validarDecimalInput"){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,1,$bNReq,$sNValDef,'text',$nNTam,$nNLon,$boostrap,$class_add);
	}
	/**************************************************************
	@ Agregar Campo Email
	 **************************************************************/
	function AgregarCampoEmail($sNNomC,$sNDes,$bNReq,$sNValDef,$nNTam,$nNLon){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,4,$bNReq,$sNValDef,'text',$nNTam,$nNLon);
	}
	/**************************************************************
	@ Agregar Campo Memo
	 **************************************************************/
	function AgregarCampoMemo($sNNomC,$sNDes,$bNReq,$sNValDef,$nNCol,$nNFil,$boostrap = false){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,0,$bNReq,$sNValDef,'textarea',$nNCol,$nNFil,$boostrap);
	}
	/**************************************************************
	@ Agregar Campo Fecha
	 **************************************************************/
	function AgregarCampoFecha($sNNomC,$sNDes,$bNReq,$sNValDef,$nNTam='',$nNLon='',$boostrap = false){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,2,$bNReq,$sNValDef,'fecha',0,0,$boostrap);
	}
	/**************************************************************
	@ Agregar Campo Fecha Hora Label
	 **************************************************************/
	function AgregarCampoLabelFechaHora($sNNomC,$sNDes,$bNReq,$sNValDef){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,2,$bNReq,$sNValDef,'fechaHoraLBL',0,0);
	}
	/**************************************************************
	@ Agregar Campo Check Sql
	 **************************************************************/
	function AgregarCampoCheckSQL($sNNomC,$sNDes,$bNReq,$sNValDef){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,0,$bNReq,$sNValDef,'checkSQL',0,0);
	}
	/**************************************************************
	@ Agregar Campo Si No
	 **************************************************************/
	function AgregarCampoSi_No($sNNomC,$sNDes,$sNValDef){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,6,true,$sNValDef,'si_no',0,0);
	}

	/**************************************************************
	@ Agregar Campo CHECK
	 **************************************************************/
	function AgregarCampoCheck($sNNomC,$sNDes,$bNReq,$sNValDef){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,0,$bNReq,$sNValDef,'check',0,0);
	}

	/**************************************************************
	@ Agregar Campo Archivo
	 **************************************************************/
	function AgregarCampoArchivo($sNNomC,$sNDes,$bNReq,$sNPath,$nNTam,$sNLon,$sNTip=''){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->tipoObjetoArchivo = $sNTip;

		if ($sNPath == ""){
			$sNPath="../imagenes/sin-imagen.png";
		}
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,3,$bNReq,$sNPath,'file',$nNTam,$sNLon);
	}
	/**************************************************************
	@ Agregar Campo Lista Sql
	 **************************************************************/
	function AgregarCampoListaSQL($sNNomC,$sNDes,$sNSQL,$bNReq,$nNTam='',$nNLon='',$boostrap = false, $class_add = ''){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,0,$bNReq,$sNSQL,'selectSQL',$nNTam,$nNLon,$boostrap,$class_add);
	}
	/**************************************************************
	@ Agregar Campo de Lista Sql
	 **************************************************************/
	function AgregarCamposDeListaSQL($sNNomC,$sNCamVal,$sNCamDes){
		$this->cCampos[$sNNomC]->sCampoValueListaSQL=$sNCamVal;
		$this->cCampos[$sNNomC]->sCampoDescripcionListaSQL=$sNCamDes;
	}
	/**************************************************************
	@ Agregar Campo Lista
	 **************************************************************/
	function AgregarCampoLista($sNNomC,$sNDes,$bNReq,$nNTam='',$nNLon='',$boostrap = false, $class_add = ''){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,0,$bNReq,'','select',$nNTam,$nNLon,$boostrap,$class_add);
	}
	/**************************************************************
	@ Agregar Campo Texto Grupo de Opciones
	 **************************************************************/
	function AgregarCampoTextoGrupoOpciones($sNNomC,$sNDes,$sNAli){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,0,true,'','grupo',$sNAli,0);
	}
	/**************************************************************
	@ Agregar Campo Oculto
	 **************************************************************/
	function AgregarCampoOculto($sNNomC,$sNVal){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNNomC,0,false,$sNVal,"hidden",0,0);
	}
	/**************************************************************
	@ Agregar Campo Numerico Oculto
	 **************************************************************/
	function AgregarCampoNumericoOculto($sNNomC,$sNVal){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNNomC,1,false,$sNVal,"hidden",0,0);
	}
	/**************************************************************
	@ Agregar Label
	 **************************************************************/
	function AgregarLabel($sNNomC,$sNDes,$sNVal,$sNValMas){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,0,false,$sNVal,"label",$sNValMas,0);
	}
	/**************************************************************
	@ Agregar Label Sql
	 **************************************************************/
	function AgregarLabelSQL($sNNomC,$sNDes,$sNSQL){
		$this->NuevoCampo($sNNomC);
		$this->cCampos[$sNNomC]->AgregarCampo($sNNomC,$sNDes,0,false,$sNSQL,"labelSQL",0,0);
	}
	/**************************************************************
	@ Agregar Campo Opcion Sql
	 **************************************************************/
	function AgregarCampoOpcionSQL($sBuscarCampo,$sDes,$sTab,$sCam){
		if($sBuscarCampo!='') $this->cCampos[$sBuscarCampo]->AgregarOpcion('SQL',$sDes,$sTab,$sCam,0);
	}
	/**************************************************************
	@ Agregar Opcion Campo Lista
	 **************************************************************/
	function AgregarOpcionCampoLista($sBuscarCampo,$sDes,$sVal){
		if($sBuscarCampo!='') $this->cCampos[$sBuscarCampo]->AgregarVOpcion('select',$sDes,$sVal);
	}
	/**************************************************************
	@ Agregar Campo Opcion Texto
	 **************************************************************/
	function AgregarCampoOpcionTexto($sBuscarCampo,$sDes,$sDef,$nTam,$nLon){
		if($sBuscarCampo!='') $this->cCampos[$sBuscarCampo]->AgregarOpcion('texto',$sDes,$sDef,$nTam,$nLon);
	}
	/**************************************************************
	@ Agregar Campo Opcion Si No
	 **************************************************************/
	function AgregarCampoOpcionSi_No($sBuscarCampo,$sDes,$sDef){
		if($sBuscarCampo!='') $this->cCampos[$sBuscarCampo]->AgregarOpcion('si_no',$sDes,$sDef,0,0);
	}
	/**************************************************************
	@ Linea Detalle
	 **************************************************************/
	function LineaDetalle($iLin){
		$bAux=true;
		$vKeys=array_keys($this->cCampos);
		foreach($vKeys as $sNomCampo)
			$this->cCampos[$sNomCampo]->nPrefijoLinea = $iLin;
	}

	function AgregarValidarDuplicado($sBuscarCampo){
		if($sBuscarCampo!='') $this->cCampos[$sBuscarCampo]->bValidarDuplicado=true;
	}

	function AgregarValidarExistente($sBuscarCampo,$sNuevoTablaForanea,$sNuevoCampoForaneoCod,$sNuevoCampoForaneoDes){
		if($sBuscarCampo!=''){
			$this->cCampos[$sBuscarCampo]->bValidarExistente=true;
			$this->cCampos[$sBuscarCampo]->sTablaForanea=$sNuevoTablaForanea;
			$this->cCampos[$sBuscarCampo]->sCampoForaneoCod=$sNuevoCampoForaneoCod;
			$this->cCampos[$sBuscarCampo]->sCampoForaneoDes=$sNuevoCampoForaneoDes;
		}
	}

	function AgregarValidarValorMaximo($sBuscarCampo,$sValMax){
		if($sBuscarCampo!='') $this->cCampos[$sBuscarCampo]->sValorMaximo=$sValMax;
	}

	function AgregarValidarValorMinimo($sBuscarCampo,$sValMin){
		if($sBuscarCampo!='')$this->cCampos[$sBuscarCampo]->ValorMinimo=$sValMin;
	}

	function AgregarOpcionCrear($sBuscarCampo,$sComando){
		if($sBuscarCampo!='') $this->cCampos[$sBuscarCampo]->sOpcionCrear=$sComando;
	}

	function AgregarOpcionBuscar($sBuscarCampo,$sComando){
		if($sBuscarCampo!='') $this->cCampos[$sBuscarCampo]->OpcionBuscar($sComando);
	}

	function AgregarOpcionLista($nPos,$sBuscarCampo,$sValue,$sOpcion){
		if($sBuscarCampo!='') $this->cCampos[$sBuscarCampo]->vOpcionLista[$nPos]=$sValue.'|'.$sOpcion;
	}

	function AgregarOpcionBuscarAvanzado($sBuscarCampo,$sComando){
		if($sBuscarCampo!='') $this->cCampos[$sBuscarCampo]->OpcionBuscar('avanzado|'.$sComando);
	}

	function AgregarComandoAlQuitarEnfoque($sCampoBusqueda,$sComando){
		if($sCampoBusqueda!='') $this->cCampos[$sCampoBusqueda]->sComandoAlQuitarEnfoque=$sComando;
	}

	function AgregarComandoAlPonerEnfoque($sCampoBusqueda,$sComando){
		if($sCampoBusqueda!='') $this->cCampos[$sCampoBusqueda]->sComandoAlPonerEnfoque=$sComando;
	}

	function AgregarComandoAlCambiarValor($sCampoBusqueda,$sComando){
		if($sCampoBusqueda!='') $this->cCampos[$sCampoBusqueda]->sComandoAlCambiarValor=$sComando;
	}

	function AgregarComandoAlEscribir($sCampoBusqueda,$sComando){
		if($sCampoBusqueda!='') $this->cCampos[$sCampoBusqueda]->sComandoAlEscribir=$sComando;
	}

	function ObjetoHtml($sBuscarCampo){
		if($sBuscarCampo!='') return ($this->cCampos[$sBuscarCampo]->ObjetoHtml());
	}

	function ObjetoHtmlLBL($sBuscarCampo){
		if($sBuscarCampo!='') return ($this->cCampos[$sBuscarCampo]->ObjetoHtmlLBL());
	}

	function ObjetoHtmlERR($sBuscarCampo){
		if($sBuscarCampo!='') return ($this->cCampos[$sBuscarCampo]->ObjetoHtmlERR());
	}

	function ValidarCampos(){
		$bAux=true;
		$vKeys=array_keys($this->cCampos);
		foreach($vKeys as $sNomCampo){
			if($this->cCampos[$sNomCampo]->xValor!=""){
				if($this->cCampos[$sNomCampo]->Validar()===false)
					$bAux=false;
			}else{
				if($this->cCampos[$sNomCampo]->bRequerido){
					if($this->cCampos[$sNomCampo]->Validar()===false)
						$bAux=false;
				}
			}
		}
		return $bAux;
	}

	function LlenarValoresCamposFormulario(){
		$vKeys=array_keys($this->cCampos);
		foreach($vKeys as $sNomCampo)
		{
			//echo $sNomCampo."<br>";
			$this->cCampos[$sNomCampo]->LeerValorFormulario();
		}
	}

	//Cambia a Rojo los campos con valores invalidos
	function divCampos($sAux){
		$vKeys=array_keys($this->cCampos);
		$sMensaje='';
		foreach($vKeys as $sNomCampo){
			if($this->cCampos[$sNomCampo]->sMensajeError!=''){
				echo "document.getElementById('lbl".$this->cCampos[$sNomCampo]->Label()."').className='Rojo';";
				$sMensaje.=$this->cCampos[$sNomCampo]->sMensajeError.' en '.$this->cCampos[$sNomCampo]->sDescripcion.'\n';
				if($sAux=='' && $this->cCampos[$sNomCampo]->bBloqueo==false)
					$sAux=$this->cCampos[$sNomCampo]->Foco();
			}
		}
		if($sAux=='')
			return "foco('');";
		else{
			if($sMensaje!='')
				return "alert('".$sMensaje."');".$sAux;
			else
				return $sAux;
		}
	}

	// Guarda (inserta) los valores del formulario en un registro nuevo en la tabla
	function GuardarRegistro(){
		$sSql='';
		$sSqlValues='';
		$vKeys=array_keys($this->cCampos);
		foreach($vKeys as $sNomCampo){
			if($this->cCampos[$sNomCampo]->sNombreCampo!=''){
				if($sSql!='') $sSql.=',';
				if($sSqlValues!='') $sSqlValues.=',';
				$sSql.=$this->cCampos[$sNomCampo]->sNombreCampo;
				switch($this->cCampos[$sNomCampo]->nTipo){
					case 1:
						if($this->cCampos[$sNomCampo]->ValorHtmlEncode()=="")
							$this->cCampos[$sNomCampo]->xValor=0;

						$sSqlValues =$sSqlValues. $this->cCampos[$sNomCampo]->ValorHtmlEncode();


						break;
					default:
						$sSqlValues =$sSqlValues."'".$this->cCampos[$sNomCampo]->ValorHtmlEncode()."'";
				}
				$this->cCampos[$sNomCampo]->xValor = $this->cCampos[$sNomCampo]->xValorDefault;
				$this->cCampos[$sNomCampo]->xValorMascara = '';
			}
		}
		$sSql='INSERT INTO '.$this->sTablaBDD.'('.$sSql.') VALUES('.$sSqlValues.');';
		$this->oConBDD->Conectar();
		$this->oConBDD->Query($sSql);
		$this->oConBDD->Free();
		$this->oConBDD->Desconectar();
	}


	//Buscar un registro en la Base de Datos y asignar los valores de los campos a las propiedades de la clase y bloquea el registro para la edcion de otros usuarios
	function BuscarRegistro($sValorBusqueda){
		//echo $this->SqlHTMLEncode($sValorBusqueda);
		switch($this->cCampos[$this->sCampoClave]->nTipo){
			case 0: $sSql="SELECT * FROM comercial.".$this->sTablaBDD." WHERE ".$this->sCampoClave."='".$this->SqlHTMLEncode($sValorBusqueda)."'";break;
			case 1: $sSql="SELECT * FROM comercial.".$this->sTablaBDD." WHERE ".$this->sCampoClave."=".$this->SqlHTMLEncode($sValorBusqueda);break;
		}
		//echo $this->oConBDD->DSN;
		$this->oConBDD->Conectar();
		$this->oConBDD->Query($sSql);
		$vKeys=array_keys($this->cCampos);
		foreach($vKeys as $sNomCampo){
			$this->cCampos[$sNomCampo]->xValor=$this->oConBDD->f($sNomCampo);
			if($this->cCampos[$sNomCampo]->bValidarDuplicado==true)
				$this->cCampos[$sNomCampo]->xValorAnterior = $this->cCampos[$sNomCampo]->xValor;
			if($this->cCampos[$sNomCampo]->sTipoObjeto=='textSQL' && $this->oConBDD->f($sNomCampo) !='')
				$this->cCampos[$sNomCampo]->xValorMascara=$this->cCampos[$sNomCampo]->BuscarValorMascara($this->oConBDD->f($sNomCampo));
		}
		if($this->oConBDD->f('block')==1){
			if(($this->oConBDD->f('ultimoacceso')+150) < (date("YmdHis")+0)){
				switch($this->cCampos[$this->sCampoClave]->nTipo){
					case 0:
						$sSql="update ".$this->sTablaBDD." set block=1,UID=".$_SESSION['UID'].",ultimoacceso='".date("YmdHis")."' WHERE ".$this->cCampos[$this->sCampoClave]->sNombreCampo."='".$sValorBusqueda."'";
						break;
					case 1:
						$sSql="update ".$this->sTablaBDD." set block=1,UID=".$_SESSION['UID'].",ultimoacceso='".date("YmdHis")."' WHERE ".$this->cCampos[$this->sCampoClave]->sNombreCampo."=".$sValorBusqueda;
						break;
				}
				$this->oConBDD->Query($sSql);
			}else{
				$this->nBloqueado=1;
				$this->BloquearCampos();
			}
		}else{
			switch($this->cCampos[$this->sCampoClave]->nTipo){
				case 0:
					$sSql="update ".$this->sTablaBDD." set block=1,UID=".$_SESSION['UID'].",ultimoacceso='".date("YmdHis")."' WHERE ".$this->cCampos[$this->sCampoClave]->sNombreCampo."='".$sValorBusqueda."'";
					break;
				case 1:
					$sSql="update ".$this->sTablaBDD." set block=1,UID=".$_SESSION['UID'].",ultimoacceso='".date("YmdHis")."' WHERE ".$this->cCampos[$this->sCampoClave]->sNombreCampo."=".$sValorBusqueda;
					break;
			}
			$this->oConBDD->Query($sSql);
		}
		$this->oConBDD->Free();
		$this->oConBDD->Desconectar();
	}

	//DesBloquea un registro por salida abrupta
	function BloqueoRegistro($sValorBusqueda){
		switch($this->cCampos[$this->sCampoClave]->nTipo){
			case 0:
				$sSql="UPDATE ".$this->sTablaBDD." set block=0 where ".$this->sCampoClave."='".$sValorBusqueda."'";break;
			case 1:
				$sSql="UPDATE ".$this->sTablaBDD." set block=0 where ".$this->sCampoClave."='".$sValorBusqueda."'";break;
		}
		$this->oConBDD->Conectar();
		$this->oConBDD->Query($sSql);
		$this->oConBDD->Free();
		$this->oConBDD->Desconectar();
	}

	// Actualiza un registro con los nuevos valores enviados por el formulario
	function ActualizarRegistro($sValorBusqueda){
		$sSql="UPDATE ".$this->sTablaBDD." set ";
		$vKeys=array_keys($this->cCampos);
		foreach($vKeys as $sNomCampo){
			if($this->cCampos[$sNomCampo]->sNombreCampo !='' && $this->cCampos[$sNomCampo]->sNombreCampo!=$this->sCampoClave ){
				$sSql=$sSql. " " .$this->cCampos[$sNomCampo]->sNombreCampo."=";
				switch($this->cCampos[$sNomCampo]->nTipo){
					case 1:
						if($this->cCampos[$sNomCampo]->xValor=="")
							$this->cCampos[$sNomCampo]->xValor=0;
						$sSql =$sSql. $this->cCampos[$sNomCampo]->xValor.",";break;
					default:
						$sSql =$sSql."'".$this->cCampos[$sNomCampo]->ValorHtmlEncode()."',";
				}
				$this->cCampos[$sNomCampo]->xValor = '';
			}
		}
		$sSql=substr($sSql,0,strlen($sSql)-1);
		switch($this->cCampos[$this->sCampoClave]->nTipo){
			case 0:
				$sSql=$sSql.",block=0 WHERE ".$this->sCampoClave."='".$sValorBusqueda."'";break;
			case 1:
				$sSql=$sSql.",block=0 WHERE ".$this->sCampoClave."='".$sValorBusqueda."'";break;
		}

		$this->oConBDD->Conectar();
		$this->oConBDD->Query($sSql);
		$this->oConBDD->Free();
		$this->oConBDD->Desconectar();
	}

	function ValidarBorrado($sSql,$bValor){
		$this->oConBDD->Conectar();
		$this->oConBDD->SetFormatoResultado('ORDENADO');
		$this->oConBDD->Query($sSql);

		if($this->oConBDD->Fila>0){ //Si encontro registros
			$this->oConBDD->Free();
			$this->oConBDD->Desconectar();
			return $bValor;
		}else{        //No encontro registros
			$this->oConBDD->Free();
			$this->oConBDD->Desconectar();
			return !($bValor);
		}
	}

	function BorrarRegistro($sValorBusqueda){
		switch($this->cCampos[$this->sCampoClave]->nTipo){
			case 0:
				$sSql="delete from comercial.".$this->sTablaBDD." WHERE ".$this->sCampoClave."='".$sValorBusqueda."'";break;
			case 1:
				$sSql="delete from comercial.".$this->sTablaBDD." WHERE ".$this->sCampoClave."=".$sValorBusqueda;break;
		}
		$this->oConBDD->Conectar();
		$this->oConBDD->Query($sSql);
		$this->oConBDD->Free();
		$this->oConBDD->Desconectar();
	}

	/*function ScriptBloqueo(){
	$sAux='<script language="JavaScript" type="text/javascript">';
	$sAux.='function Cerrar(editar){';
	$sAux.='if(editar==true)';
	$sAux.="window.open('../bloqueo.php?ID=".$_REQUEST['ID']."&IDB=".$_REQUEST['IDB']."&tabla=".$this->sTablaBDD."&campo=".$this->sCampoClave."&cod=".$_REQUEST['cod']."&blck=1&bdd=".$this->oConBDD->InfoPac ."','bloqueo','width=100,height=100,scrollbar=NO,statusbar=NO,menubar=NO,location=NO');}";
	$sAux.='</script>';
	echo  $sAux;
	}

	function ScriptBloqueoPorValor($xVal){
	$sAux='<script language="JavaScript" type="text/javascript">';
	$sAux.='function Cerrar(editar){';
	$sAux.='if(editar==true)';
	$sAux.="window.open('../bloqueo.php?ID=".$_REQUEST['ID']."&IDB=".$_REQUEST['IDB']."&tabla=".$this->sTablaBDD."&campo=".$this->sCampoClave."&cod=".$xVal."&blck=1&bdd=".$this->oConBDD->InfoPac ."','bloqueo','width=100,height=100,scrollbar=NO,statusbar=NO,menubar=NO,location=NO');}";
	$sAux.='</script>';
	echo  $sAux;
	}*/

	function BloquearCampos(){
		$vKeys=array_keys($this->cCampos);
		foreach($vKeys as $sNomCampo)
			$this->cCampos[$sNomCampo]->bBloqueo=true;
	}

	function DesBloquearCampos(){
		$vKeys=array_keys($this->cCampos);
		foreach($vKeys as $sNomCampo)
			$this->cCampos[$sNomCampo]->bBloqueo=false;
	}

	function ScriptDoDesbloqueo(){
		$Aux='function doDesbloqueo(){'.chr(13);
		$vKeys=array_keys($this->cCampos);
		foreach($vKeys as $sNomCampo){
			$Aux.=$this->cCampos[$sNomCampo]->ScriptDoDesbloqueo().chr(13);
			//echo $sNomCampo." -- ";
		}
		$Aux.='}';
		return $Aux;
	}
	//------------------------------
	function ComandoAlCambiar($sCampoBusqueda,$sComando){
		$cCampos[BuscarCampo($sCampoBusqueda)]->sComandoAlCambiar=$sComando;
	}

	function ValoresCampos(){
		$Aux='';
		for($i=0;$i<count($cCampos)-1;$i++)
			if($Aux!='' ) $Aux.=',';
		$Aux.=' '.$cCampos[$i]->sNombre.'='.$cCampos[$i]->xValorReal;
	}


	function LimpiarValores(){
		$vKeys=array_keys($this->cCampos);
		foreach($vKeys as $sNomCampo){
			if($this->cCampos[$sNomCampo]->sNombreCampo !='' ){
				$this->cCampos[$sNomCampo]->xValor = $this->cCampos[$sNomCampo]->xValorDefault;
				$this->cCampos[$sNomCampo]->xValorMascara = '';
			}
		}
	}
	function ValidarClavePrimaria($sCampos,$sTabla){
		$vsCampos=explode('|',$sCampos);
		$sSql="Select ".$vsCampos[0]." from comcercial.".$sTabla;
		$sWhere='';
		for($i=0;$i<count($vsCampos);$i++){
			if($sWhere !='' ) $sWhere.=' and ';
			$sWhere =$sWhere.$vsCampos[$i]."='".$this->cCampos[$vsCampos[$i]]->ValorReal()."'";
		}
		$sSql=$sSql.' where '.$sWhere;
		$this->oConBDD->Conectar();
		$this->oConBDD->SetFormatoResultado('ORDENADO');
		$this->oConBDD->Query($sSql);
		if ($this->oConBDD->NumFilas()==0){
			$this->oConBDD->Free();
			return true;
		}else{
			$this->oConBDD->Free();
			$this->cCampos[$vsCampos[1]]->sMensajeError='El valor ya Existe';
			return false;
		}
		//set RS=Nothing
	}

	function SqlHTMLEncode($sval){
		$sval=str_replace(chr(34),'&#34;',$sval);
		$sval=str_replace(chr(39),'&#39;',$sval);
		return $sval;
	}

}
?>
