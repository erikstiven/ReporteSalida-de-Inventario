<?
//include_once('Calendario/calen.inc.php');

class Campo
{
    var $sNombreCampo = '';    //nombre del campo
    var $sDescripcion = '';    //descripcion del campo
    var $sAlineacionDescripcion = 'right';    //alineacion de la descripcion right,left,center,justify
    var $nTipo = 0;    //tipo de valor, 0=texto 1=numero 2=fecha, 3=archivo/imagen, 4=email
    var $sTipoObjeto = '';    //tipo de objeto en el formulario, text,textarea,select, file, radio
    var $bRequerido = false;    //si el campo es requerido-> true o false
    var $xValor = '';    //valor del campo
    var $xValorAnterior = '';    //valor del campo en la tabla, se utiliza para la comparacion de duplicados al actualizar un registro
    var $xValorMascara = '';    //valor descripctivo de un campo Foraneo
    var $xValorDefault = '';    //valor por default campo
    var $nTamano = 0;    //tama� del objeto en el formulario
    var $sSelectM = '';    //Opcion lista multiple
    var $nSelectS = 5;    //tama� de la lsita multiple
    var $nLongitud = 0;    //numero maximo de caracteres del objeto en el formulario
    var $sPathArchivo = '';   //Path para subida de datos
    var $bValidarDuplicado = false;//True: Valida que el valor campo ingresado no este duplicado en la tabla
    var $bValidarExistente = false;//True: Valida que el valor campo ingresado exista en la  tabla foranea
    var $bBloqueo = false;//para bloquear el objeto del formulario
    var $oConBDD;
    var $sBDD = '';
    var $sTablaBDD = '';   //Tabla a la que pertenece el campo
    var $sTablaForanea = '';   //tabla para validar si el valor del campo existe
    var $sCampoForaneoDes = '';   //campo de la tabla foranea para realizar la comparacion
    var $sCampoForaneoCod = '';   //campo de la tabla foranea para almacenar en la registro
    var $sComandoSQL = '';   //Sentencia SQL para campos lista
    var $sOpcionCrear = '';   //Comando para Boton crear en el formulario
    var $sOpcionBuscar = '';   //Comando para Boton buscar/selecionar en el formulario
    var $vOpcionLista = array();
    var $nNeOpcionLista = -1;
    var $sMensajeError = '';   //mensaje de error despues de realizar la validacion
    var $nPrefijoLinea = -1;   //numero de linea del detalle
    var $sComandoAlQuitarEnfoque = '';   //ejecuta un comando al perder el enfoque un objeto
    var $sComandoAlPonerEnfoque = '';   //ejecuta un comando al poner el enfoque un objeto
    var $sComandoAlCambiarValor = '';   //Ejecuta un comando al cambiar el valor del campo//
    var $sComandoAlEscribir = '';   //Ejecuta un comando al escribir algo en un objeto//
    var $vsOpciones = array();//Arreglo de opciones, de las que se escoje un valor para el campo
    var $nNeOpciones = -1;   //Numero de elementos de vsOpciones
    var $sCompuesto = '';   //cuando es un campo compuesto por 2 o mas objetos (fecha), campo1,campo2,campo3 (dia,mes,ano)
    var $sValorMinimo = '';  //valor minimo en un rango
    var $sValorMaximo = '';  //valor maximo en un rango
    var $sNombreClase = 'CampoFormulario';
    var $sNombreClaseRojo = 'CampoFormulario_Rojo';
    var $sNombreClaseBoton = 'BotonFormulario';
    var $sNombreClaseBotonActivo = 'BotonFormularioActivo';
    var $sCampoValueListaSQL = '';
    var $sCampoDescripcionListaSQL = '';
    var $DSN = '';
    var $imagenCalendario = '../../Include/Clases/Formulario/Calendario/calendario.gif';
    var $tipoObjetoArchivo = '';    // valores img
    var $boostrap      			    = false;
    var $class_add      		    = '';

    function __construct($DSN = '') {
        $this->oConBDD = new Dbo;
        $this->oConBDD->DSN = $DSN;
        $this->DSN = $this->oConBDD->DSN;
    }

    function Label()
    {
        if ($this->nPrefijoLinea > -1)
            return '-ln0-' . $this->sNombreCampo;
        else
            return $this->sNombreCampo;
    }

    function ValorReal()
    {
        if ($this->sTipoObjeto == 'grupo')
            return $this->xValorMascara;
        else
            return $this->xValor;
    }

    function ValorHtmlEncode()
    {
        return $this->SqlHTMLEncode($this->xValor);
    }

    function Valor($xNuevoValor)
    {
        if ($this->sTipoObjeto == 'textSQL' && $xNuevoValor != '')
            $this->xValorMascara = $this->BuscarValorMascara($xNuevoValor);
        $this->xValor = $xNuevoValor;
    }

    function OpcionBuscar($sNuevoValor)
    {
        $this->sOpcionBuscar = $sNuevoValor;
        if ($this->sOpcionBuscar != '')
            $this->sTipoObjeto = 'textSQL';
    }

    function OpcionLista($iPos)
    {
        if ($iPos <= $this->nNeOpcionLista)
            return $this->vOpcionLista($iPos);
        else
            return '';
    }

    function Dia()
    {
        if ($this->xValor != '')
            return $this->ExtraerFecha($this->xValor, 'd');
        else
            return date('d');
    }

    function Mes()
    {
        if ($this->xValor != '')
            return $this->ExtraerFecha($this->xValor, 'm');
        else
            return date('m');
    }

    function Mes3Let()
    {
        if ($this->xValor != '')
            return $this->Mes3Letras($this->ExtraerFecha($this->xValor, 'm'));
        else
            return $this->Mes3Letras(date('m'));
    }

    function Ano()
    {
        if ($this->xValor != '')
            return $this->ExtraerFecha($this->xValor, 'a');
        else
            return date('Y');
    }

    function Hora()
    {
        return date('H');
    }

    function Minuto()
    {
        return date('i');
    }

    function Segundo()
    {
        return date('s');
    }

    function txtRequerido()
    {
        if ($this->bRequerido == true)
            return '* ';
        else
            return '';
    }

    function Bloqueado()
    {
        if ($this->bBloqueo == true)
            return ' disabled ';
        else
            return '';
    }

    function PrefijoLinea()
    {
        if ($this->nPrefijoLinea != -1)
            return '-ln' . $this->nPrefijoLinea . '-';
        else
            return '';
    }

    function Tamano()
    {
        $opSelect = NULL;
        $opSelect = explode('|', $this->nTamano);
        if (is_numeric($opSelect[0]))
            return $opSelect[0] . 'px';
        else
            return $opSelect[0];
    }

    function AgregarCampo($sNNomC, $sNDes, $nNTip, $bNReq, $sNValDef, $sNTipObj, $nNTam, $nNLon,$boostrap = false, $class_add = '')
    {
        $this->sNombreCampo = $sNNomC;
        if (strpos($sNDes, '|')) {
            $this->sDescripcion = substr($sNDes, 0, strpos($sNDes, '|'));
            $this->sAlineacionDescripcion = substr($sNDes, strpos($sNDes, '|') + 1);
        } else
            $this->sDescripcion = $sNDes;
        $this->nTipo = $nNTip;
        $this->bRequerido = $bNReq;
        $this->sTipoObjeto = $sNTipObj;
        $this->boostrap = $boostrap;
        $this->class_add = $class_add;
        switch ($this->sTipoObjeto) {
            case 'file':
                $this->sPathArchivo = $sNValDef;
                break;
            case 'selectSQL':
                $this->sComandoSQL = $sNValDef;
                break;
            case 'arbol':
                $this->sComandoSQL = $sNValDef;
                break;
            case 'arbol1':
                $this->sComandoSQL = $sNValDef;
                break;
            case 'barra':
                $this->sComandoSQL = $sNValDef;
                break;
            case 'checkSQL':
                $this->sComandoSQL = $sNValDef;
                break;
            case 'labelSQL':
                $this->sComandoSQL = $sNValDef;
                break;
            case 'label':
                $this->xValorMascara = $nNTam;
                $this->xValor = $sNValDef;
                break;
            default:
                $this->xValorDefault = $sNValDef;
                $this->xValor = $sNValDef;
        }

        $this->nTamano = $nNTam;
        $this->nLongitud = $nNLon;
    }

    //' Procedimientos y funciones internos de la Clase
    //'---------------------------------------------
    function AgregarOpcion($sTip, $sNDes, $sNTab, $sNCam, $nNLon)
    {
        $this->nNeOpciones++;
        $this->vsOpciones[$this->nNeOpciones] = $sTip . '|' . $sNDes . '|' . $sNTab . '|' . $sNCam . '|' . $nNLon;
    }

    function AgregarVOpcion($sTip, $sNDes, $sNVal)
    {
        $this->nNeOpciones++;
        $this->vOpcionLista[$this->nNeOpciones] = $sNVal . '|' . $sNDes;
    }

    /***************************************************
     * @ Funcion ObjetoHtml
     * Devuelve la cadena del objeto html generado
     ***************************************************/
    function ObjetoHtml()
    {
        switch ($this->sTipoObjeto) {
            case 'password':
            case 'text':
                return $this->ObjetoHTMLTexto();
                break;
            case 'textRojo':
                return $this->ObjetoHTMLTextoRojo();
                break;
            case 'textarea':
                return $this->ObjetoHTMLTextarea();
                break;
            case 'fecha':
                return $this->ObjetoHTMLFechaPlus();
                break;
            case 'fechaLBL':
                return $this->ObjetoHTMLFechaLBL();
                break;
            case 'fechaHoraLBL':
                return $this->ObjetoHTMLFechaHoraLBL();
                break;
            case 'selectSQL':
                return $this->ObjetoHTMLSelectSQL();
                break;
            case 'barra':
                return $this->ObjetoHTMLBarra();
                break;
            case 'select':
                return $this->ObjetoHTMLSelect();
                break;
            case 'textSQL':
                return $this->ObjetoHTMLTextSQL();
                break;
            case 'file':
                return $this->ObjetoHTMLFile();
                break;
            case 'si_no':
                return $this->ObjetoHTMLSi_No();
                break;
            case 'hidden':
                return $this->ObjetoHTMLHidden();
                break;
            case 'grupo':
                return $this->ObjetoHTMLGrupoOpciones();
                break;
            case 'label':
                return $this->ObjetoHTMLLabel();
                break;
            case 'labelSQL':
                return $this->ObjetoHTMLLabelSQL();
                break;
            case 'checkSQL':
                return $this->ObjetoHTMLCheckSQL();
                break;
            case 'check':
                return $this->ObjetoHTMLCheck();
                break;
        }
    }

    /***************************************************
     * @ Funcion ObjetoHtmlTexto
     * Genera un objeto input tipo texto
     ***************************************************/
    function ObjetoHtmlTexto(){
        if($this->boostrap){
            $readonly_ = '';
            if($this->sComandoAlPonerEnfoque == 'this.blur()'){
                $readonly_ = 'readonly';
            }
            switch($this->nTipo){
                case 1:
                    return '<input name="'.$this->PrefijoLinea().$this->sNombreCampo.'" 
						   type="'.$this->sTipoObjeto.'" 
						   id="'.$this->PrefijoLinea().$this->sNombreCampo.'"'.$this->Bloqueado().$this->ScriptObjetoHTML().' 
						   value="'.str_replace(",",".",$this->xValor).'" 
						   class="form-control input-sm '.$this->class_add.' " '.$readonly_.'>'.$this->DivValidarHTML($this->PrefijoLinea().$this->sNombreCampo,'');
                    break;
                default:
                    return '<input name="'.$this->PrefijoLinea().$this->sNombreCampo.'" 
						   type="'.$this->sTipoObjeto.'" 
						   id="'.$this->PrefijoLinea().$this->sNombreCampo.'"'.$this->Bloqueado().$this->ScriptObjetoHTML().' 
						   value="'.$this->xValor.'" 
						   class="form-control input-sm '.$this->class_add.' " '.$readonly_.'
						   >'.$this->DivValidarHTML($this->PrefijoLinea().$this->sNombreCampo,'').$this->ObjetoHTMLValorAnterior();
            }
        }else{
            switch($this->nTipo){
                case 1:
                    return '<input name="'.$this->PrefijoLinea().$this->sNombreCampo.'" 
						   type="'.$this->sTipoObjeto.'" 
						   id="'.$this->PrefijoLinea().$this->sNombreCampo.'"'.$this->Bloqueado().$this->ScriptObjetoHTML().' 
						   value="'.str_replace(",",".",$this->xValor).'" 
						   maxlength="'.$this->nLongitud.'" 
						   class="'.$this->sNombreClase.' validarDecimalInput" 
						   style="width:'.$this->nTamano.'px;text-align:right;">'.$this->DivValidarHTML($this->PrefijoLinea().$this->sNombreCampo,'');
                    break;
                default:
                    return '<input name="'.$this->PrefijoLinea().$this->sNombreCampo.'" 
						   type="'.$this->sTipoObjeto.'" 
						   id="'.$this->PrefijoLinea().$this->sNombreCampo.'"'.$this->Bloqueado().$this->ScriptObjetoHTML().' 
						   value="'.$this->xValor.'" 
						   maxlength="'.$this->nLongitud.'" 
						   class="'.$this->sNombreClase.'" 
						   style="width:'.$this->nTamano.'px;">'.$this->DivValidarHTML($this->PrefijoLinea().$this->sNombreCampo,'').$this->ObjetoHTMLValorAnterior();
            }
        }
    }

    function ObjetoHtmlTextoRojo(){

        if($this->boostrap) {
            return '<input name="'.$this->PrefijoLinea().$this->sNombreCampo.'"
                       type="text"
                       id="'.$this->PrefijoLinea().$this->sNombreCampo.'"'.$this->Bloqueado().$this->ScriptObjetoHTML().'
                       value="'.str_replace(",",".",$this->xValor).'"
                       onkeydown = "valorant(this)"
                       onkeyup="ValidarTeclas(45,57,event);
                       validarletra(this);"
                       class="form-control input-sm '.$this->class_add.' ">'.$this->DivValidarHTML($this->PrefijoLinea().$this->sNombreCampo,'');
        }else{
            return '<input name="'.$this->PrefijoLinea().$this->sNombreCampo.'"
                       type="text"
                       id="'.$this->PrefijoLinea().$this->sNombreCampo.'"'.$this->Bloqueado().$this->ScriptObjetoHTML().'
                       value="'.str_replace(",",".",$this->xValor).'"
                       onkeydown = "valorant(this)"
                       onkeyup="ValidarTeclas(45,57,event);
                       validarletra(this);"
                       maxlength="'.$this->nLongitud.'"
                       class="'.$this->sNombreClaseRojo.'"
                       style="width:'.$this->nTamano.'px;text-align:right;">'.$this->DivValidarHTML($this->PrefijoLinea().$this->sNombreCampo,'');
        }

    }

    /***************************************************
     * @ Funcion ObjetoHtmlTextarea
     * Genera un objeto input tipo textarea
     ***************************************************/
    function ObjetoHtmlTextarea(){
        if($this->boostrap) {
            return '<textarea name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
					  id="' . $this->PrefijoLinea() . $this->sNombreCampo . '" ' . $this->Bloqueado() . ' 
					  rows="' . $this->nLongitud . '" 
					  class="form-control input-sm '.$this->class_add.' " 
					  style="width:' . $this->nTamano . 'px; 
					  height:' . $this->nLongitud . 'px;" 
					  lang="es">' . $this->xValor . '</textarea>' . $this->DivValidarHTML($this->PrefijoLinea() . $this->sNombreCampo, '');
        }else{
            return '<textarea name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
					  id="' . $this->PrefijoLinea() . $this->sNombreCampo . '" ' . $this->Bloqueado() . ' 
					  rows="' . $this->nLongitud . '" 
					  class="' . $this->sNombreClase . '" 
					  style="width:' . $this->nTamano . 'px; 
					  height:' . $this->nLongitud . 'px;" 
					  lang="es">' . $this->xValor . '</textarea>' . $this->DivValidarHTML($this->PrefijoLinea() . $this->sNombreCampo, '');
        }
    }

    /***************************************************
     * @ Funcion ObjetoFechaPlus
     * Genera un objeto input tipo texto con una agregado
     * popCalendar Lite para seleccionar la fecha
     ***************************************************/
    function ObjetoHtmlFechaPlus(){
        if($this->boostrap) {
            $readonly_ = '';
            if($this->sComandoAlPonerEnfoque == 'this.blur()'){
                $readonly_ = 'readonly';
            }

            $sOBJ = '<input name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
				  id="' . $this->PrefijoLinea() . $this->sNombreCampo . '" ' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' 
				  class="form-control input-sm '.$this->class_add.' " 
				  Value="' . $this->Ano() . '-' . $this->Mes() . '-' . $this->Dia() . '" 
				  type="date" '.$readonly_.'>';
        }else{
            $sOBJ = '<input name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
				  id="' . $this->PrefijoLinea() . $this->sNombreCampo . '" ' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' 
				  class="' . $this->sNombreClase . '" 
                                  style="width:80px";
				  Value="' . $this->Ano() . '/' . $this->Mes() . '/' . $this->Dia() . '" 
				  type="text">';
            $sOBJ .= $this->DivValidarHTML($this->PrefijoLinea() . $this->sNombreCampo, '');
            if ($this->bBloqueo == false) {
                $sOBJ .= '<img align="top" 
					   src="' . $this->imagenCalendario . '" 
					   width="34" 
					   border="0" 
					   name="popcal" 
					   height="22" 
					   hspace="2" 
					   alt="Calendario" 
					   onClick="';
                $sOBJ .= "gfPop.fDemoPopDepart(
					document.getElementById( '" . $this->PrefijoLinea() . $this->sNombreCampo . "'),
											 document.getElementById('" . $this->PrefijoLinea() . $this->sNombreCampo . "'),
											 '1900-01-01');";
                $sOBJ .= '" onMouseOver = "this.className=' . "'Hand';" . '">';
            }
        }
        return $sOBJ;
    }

    /***************************************************
     * @ Funcion ObjetoHtmlFechaHoraLBL
     * Genera la fecha completa y su hora y un campo
     * oculto con la fecha formato yy/mm/dd hh:mm:ss
     ***************************************************/
    function ObjetoHtmlFechaHoraLBL()
    {
        return '<div id="label' . $this->PrefijoLinea() . $this->sNombreCampo . '">
				&nbsp;' . $this->Dia() . ' de ' . $this->MesLetras($this->Mes()) . ' de ' . $this->Ano() . ' - ' . $this->Hora() . ':' . $this->Minuto() . '
			</div>
			<input name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
				   type="hidden" 
				   id="' . $this->PrefijoLinea() . $this->sNombreCampo . '" ' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' 
				   Value="' . $this->Ano() . '/' . $this->Mes() . '/' . $this->Dia() . ' ' . $this->Hora() . ':' . $this->Minuto() . ':' . $this->Segundo() . '">';
    }

    /***************************************************
     * @ Funcion ObjetoHtmlSelectSQL
     * Genera un campo select desde una base de datos
     ***************************************************/
    function ObjetoHtmlSelectSQL()
    {
        $opSelect = NULL;
        $opSelect = explode('|', $this->nTamano);

        $this->sSelectM = (isset($opSelect[1]) ? $opSelect[1] : '');
        $this->nSelectS = (isset($opSelect[2]) ? $opSelect[2] : '');

        if($this->boostrap) {
            $sOBJ = '<select name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
				   id="' . $this->PrefijoLinea() . $this->sNombreCampo . '" ' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' 
				   class="form-control input-sm '.$this->class_add.' select2 " ';
            if ($this->sSelectM == 'M')
                $sOBJ .= ' multiple="multiple">';
            else {
                $sOBJ .= ' >';
                $sOBJ .= '<option value="">-- Seleccione una Opcion --</option>';
            }
            $sOBJ .= $this->OpcionesCampoListaSQL() . '</select>' . $this->DivValidarHTML($this->PrefijoLinea() . $this->sNombreCampo, '');
            $sOBJ .= '<script language="JavaScript" type="text/javascript">' . "
				document.getElementById('" . $this->PrefijoLinea() . $this->sNombreCampo . "').value='" . $this->xValor . "';
			  </script>";
            $sOBJ .= $this->ScriptOpcionBuscar() . $this->ScriptOpcionCrear();
        }else{
            $sOBJ = '<select name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
				   id="' . $this->PrefijoLinea() . $this->sNombreCampo . '" ' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' 
				   class="' . $this->sNombreClase . ' from-control select2" 
				   style="width:' . $this->Tamano() . ';"';
            if ($this->sSelectM == 'M')
                $sOBJ .= ' multiple="multiple" size="' . $this->nSelectS . '">';
            else {
                $sOBJ .= ' >';
                $sOBJ .= '<option value="">-- Seleccione una Opcion --</option>';
            }
            $sOBJ .= $this->OpcionesCampoListaSQL() . '</select>' . $this->DivValidarHTML($this->PrefijoLinea() . $this->sNombreCampo, '');
            $sOBJ .= '<script language="JavaScript" type="text/javascript">' . "
				document.getElementById('" . $this->PrefijoLinea() . $this->sNombreCampo . "').value='" . $this->xValor . "';
			  </script>";
            $sOBJ .= $this->ScriptOpcionBuscar() . $this->ScriptOpcionCrear();
        }



        return $sOBJ;
    }

    /***************************************************
     * @ Funcion ObjetoHtmlSelect
     * Genera un campo select
     ***************************************************/
    function ObjetoHtmlSelect(){
        if($this->boostrap) {
            $opSelect = NULL;
            $opSelect = explode('|', $this->nTamano);
            $this->sSelectM = $opSelect[1];
            $this->nSelectS = $opSelect[2];
            $sOBJ = '<select name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
				id="' . $this->PrefijoLinea() . $this->sNombreCampo . '" ' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' 
				class="form-control input-sm '.$this->class_add.' select2" ';

            if ($this->sSelectM == 'M')
                $sOBJ .= ' multiple="multiple">';
            else {
                $sOBJ .= ' >';
                $sOBJ .= '<option value="">-- Seleccione una Opcion --</option>';
            }



            foreach ($this->vOpcionLista as $Aux) {
                $sVal = substr($Aux, 0, strpos($Aux, "|"));
                $sDes = substr($Aux, strpos($Aux, "|") + 1);
                if ($this->xValor == $sVal)
                    $sOBJ .= '<option selected Value="' . $sVal . '">' . $sDes . '</option>';
                else
                    $sOBJ .= '<option Value="' . $sVal . '">' . $sDes . '</option>';
            }

            $sOBJ .= '</select>' . $this->DivValidarHTML($this->PrefijoLinea() . $this->sNombreCampo, '');
            $sOBJ .= $this->ScriptOpcionBuscar() . $this->ScriptOpcionCrear();
        }else{
            $opSelect = NULL;
            $opSelect = explode('|', $this->nTamano);
            $this->sSelectM = $opSelect[1];
            $this->nSelectS = $opSelect[2];
            $sOBJ = '<select name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
				id="' . $this->PrefijoLinea() . $this->sNombreCampo . '" ' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' 
				class="' . $this->sNombreClase . '" 
				style="width:' . $this->Tamano() . ';" ';
            if ($this->sSelectM == 'M')
                $sOBJ .= ' multiple="multiple" size="' . $this->nSelectS . '">';
            else {
                $sOBJ .= ' >';
                $sOBJ .= '<option value="">-- Seleccione una Opcion --</option>';
            }
            foreach ($this->vOpcionLista as $Aux) {
                $sVal = substr($Aux, 0, strpos($Aux, "|"));
                $sDes = substr($Aux, strpos($Aux, "|") + 1);
                if ($this->xValor == $sVal)
                    $sOBJ .= '<option selected Value="' . $sVal . '">' . $sDes . '</option>';
                else
                    $sOBJ .= '<option Value="' . $sVal . '">' . $sDes . '</option>';
            }
            $sOBJ .= '</select>' . $this->DivValidarHTML($this->PrefijoLinea() . $this->sNombreCampo, '');
            $sOBJ .= $this->ScriptOpcionBuscar() . $this->ScriptOpcionCrear();
        }
        return $sOBJ;
    }

    /***************************************************
     * @ Funcion ObjetoHtmlTextSql
     * Genera un campo input tipo texto desde la base
     * de datos
     ***************************************************/
    function ObjetoHtmlTextSQL()
    {
        $sOBJ = '<input name="des' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
				type="text" 
				id="des' . $this->PrefijoLinea() . $this->sNombreCampo . '" ' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' 
				Value="' . $this->xValorMascara . '" 
				maxlength="' . $this->nLongitud . '" 
				class="' . $this->sNombreClase . '" 
				style="width:' . $this->nTamano . 'px;">';
        $sOBJ .= '<input name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
				type="hidden" id="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
				value="' . $this->xValor . '">';
        return $sOBJ . $this->DivValidarHTML('des' . $this->PrefijoLinea() . $this->sNombreCampo, '') . $this->ScriptOpcionBuscar() . $this->ScriptOpcionCrear();
    }

    /***************************************************
     * @ Funcion ObjetoHtmlFile
     * Genera un campo input tipo file y si es para imagen
     * aparecera la imagen previsualizada
     ***************************************************/
    function ObjetoHtmlFile()
    {
        if ($this->tipoObjetoArchivo == 'img') {
            if (strpos($this->sPathArchivo, '../') === false)
                $sAuxPath = $this->xValor;
            else
                $sAuxPath = '../' . $this->sPathArchivo;

            $sOBJ = '<img id="img' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
					name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
					src="' . $sAuxPath . '" 
					width="100" 
					height="100"><br>';
            $sOBJ .= '<input name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
						type="text" 
						id="' . $this->PrefijoLinea() . $this->sNombreCampo . '" ' . $this->Bloqueado() . ' 
						value="' . $this->xValor . '"
						onFocus="this.blur();"
						onKeyUp="this.blur();" 
						maxlength="' . $this->nLongitud . '" 
						class="' . $this->sNombreClase . '" 
						style="width:' . $this->nTamano . 'px;">' . $this->DivValidarHTML($this->PrefijoLinea() . $this->sNombreCampo, '');
            if ($this->bBloqueo == false)
                $sOBJ .= '<input name="adjuntar" 
						type="button" 
						id="adjuntar" 
						Value="Seleccionar" ' . $this->Bloqueado() . ' 
						onClick="' . "ventanaCarga('" . $_COOKIE["JIREH_INCLUDE"] . "Clases/Formulario/Plugins/cargar.php?control=" . $this->PrefijoLinea() . $this->sNombreCampo . "&path=" . $this->sPathArchivo . "&tipo=" . $this->tipoObjetoArchivo . "');" . '" 
						class="' . $this->sNombreClaseBoton . '" 
						onMouseOver="' . "javascript:this.className='" . $this->sNombreClaseBotonActivo . "';" . '" 
						onMouseOut="' . "javascript:this.className='" . $this->sNombreClaseBoton . "';" . '">';
        } else {
            if (strpos($this->sPathArchivo, '../') === false)
                $sAuxPath = $this->xValor;
            else
                $sAuxPath = '../' . $this->xValor;

            $sOBJ = '';
            $sOBJ .= '<input name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" 
						type="text" 
						id="' . $this->PrefijoLinea() . $this->sNombreCampo . '" ' . $this->Bloqueado() . ' 
						onFocus="this.blur();"
						onKeyUp="this.blur();"
						value="' . $this->xValor . '" 
						maxlength="' . $this->nLongitud . '" 
						class="' . $this->sNombreClase . '" 
						style="width:' . $this->nTamano . 'px;">' . $this->DivValidarHTML($this->PrefijoLinea() . $this->sNombreCampo, '');
            if ($this->bBloqueo == false)
                $sOBJ .= '<input name="adjuntar" 
						type="button" 
						id="adjuntar" 
						Value="Seleccionar" ' . $this->Bloqueado() . ' 
						onClick="' . "ventanaCarga('" . $_COOKIE["JIREH_INCLUDE"] . "Clases/Formulario/Plugins/cargar.php?control=" . $this->PrefijoLinea() . $this->sNombreCampo . "&path=" . $this->sPathArchivo . "&tipo=" . $this->tipoObjetoArchivo . "');" . '" 
						class="' . $this->sNombreClaseBoton . '" 
						onMouseOver="' . "javascript:this.className='" . $this->sNombreClaseBotonActivo . "';" . '" 
						onMouseOut="' . "javascript:this.className='" . $this->sNombreClaseBoton . "';" . '">';
        }
        return $sOBJ;
    }

    function ObjetoHtmlSi_No()
    {
        $sOBJ = ' Si <input name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" type="radio" id="' . $this->PrefijoLinea() . $this->sNombreCampo . '"' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' Value="S"';
        if ($this->xValor == 'S')
            $sOBJ .= ' checked>';
        else
            $sOBJ .= '>';
        $sOBJ .= ' No <input name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" type="radio" id="' . $this->PrefijoLinea() . $this->sNombreCampo . '"' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' Value="N"';
        if ($this->xValor == 'N')
            $sOBJ .= ' checked>';
        else
            $sOBJ .= '>';
        return $sOBJ . $this->DivValidarHTML($this->PrefijoLinea() . $this->sNombreCampo, '');
    }

    function ObjetoHTMLCheckSQL()
    {
        if ($this->sComandoSQL != '') {
            $Aux = '';
            $i = 0;
            $sSql = $this->sComandoSQL;
            $this->oConBDD->Conectar();
            $this->oConBDD->SetFormatoResultado('ORDENADO');
            if ($this->oConBDD->Query($sSql)) {
                do {
                    if ($Aux != "") $Aux .= "<br>";
                    if ($this->xValor != '') $this->xValorMascara = $this->oConBDD->f(1);
                    $Aux .= '<input name="' . $this->PrefijoLinea() . str_replace(".", "", $this->sNombreCampo . $this->oConBDD->f(0)) . '" type="checkbox" id="' . $this->PrefijoLinea() . str_replace(".", "", $this->sNombreCampo . $this->oConBDD->f(0)) . '" ' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' value="' . $this->oConBDD->f(0) . '" class="' . $this->sNombreClase . '" >  ' . $this->oConBDD->f(0) . " -- " . $this->oConBDD->f(1);
                    $i++;
                } while ($this->oConBDD->SiguienteRegistro());
                $this->oConBDD->Free();
            }
            $this->oConBDD->Desconectar();
            $sOBJ = $Aux;
            //$sOBJ.=$this->DivValidarHTML(.$this->PrefijoLinea().$this->sNombreCampo,'');
        }
        return $sOBJ;
    }

    function ObjetoHTMLCheck()
    {
        if ($this->xValor == 'S') {
            $check = 'checked = "true" ';
        } else {
            $check = '';
        }

        $Aux = '<input name="' . $this->PrefijoLinea() . str_replace(".", "", $this->sNombreCampo) . '"
                    type="checkbox"
                    id="' . $this->PrefijoLinea() . str_replace(".", "", $this->sNombreCampo) . '" ' . $this->Bloqueado() . $this->ScriptObjetoHTML() . '
                    value="' . str_replace(",", ".", $this->xValor) . '"
                    class="' . $this->sNombreClase . '"
                    ' . $check . ' > ';
        $sOBJ = $Aux;
        return $sOBJ;
    }


    function ObjetoHtmlHidden()
    {
        return '<input name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" type="hidden" id="' . $this->PrefijoLinea() . $this->sNombreCampo . '" value="' . $this->xValor . '">';
    }

    function ObjetoHTMLGrupoOpciones()
    {
        $Aux = '';
        for ($i = 0; $i < count($this->vsOpciones); $i++) {
            $sAuxOpcion = explode('|', $this->vsOpciones[$i]);
            $Aux .= '<input name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" type="radio" id="' . $this->PrefijoLinea() . $this->sNombreCampo . $i . '" ' . $this->Bloqueado() . ' Value="' . $sAuxOpcion[1] . '"';
            if ($this->xValor != '') {
                if ($this->xValor == $sAuxOpcion[1])
                    $Aux .= ' checked ';
            } else {
                if ($i == 0)
                    $Aux .= ' checked ';
            }
            if ($sAuxOpcion[0] == 'texto') {
                switch ($this->nTipo) {
                    case 1:
                        $sOBJ = '<input name="otext-' . $this->PrefijoLinea() . $this->sNombreCampo . $i . '" type="text" id="otext-' . $this->PrefijoLinea() . $this->sNombreCampo . $i . '" ' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' Value="' . $this->xValorMascara . '" maxlength="' . $sAuxOpcion[4] . '" class="' . $this->sNombreClase . '" style="width:' . $sAuxOpcion[3] . 'px;text-align:right;"';
                        break;
                    default:
                        $sOBJ = '<input name="otext-' . $this->PrefijoLinea() . $this->sNombreCampo . $i . '" type="text" id="otext-' . $this->PrefijoLinea() . $this->sNombreCampo . $i . '" ' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' Value="' . $this->xValorMascara . '" maxlength="' . $sAuxOpcion[4] . '" class="' . $this->sNombreClase . '" style="width:' . $sAuxOpcion[3] . 'px;"';
                        if ($this->xValor == $sAuxOpcion[1])
                            $sOBJ .= '>';
                        else {
                            if (!$this->bBloqueo) $sOBJ .= ' disabled >';
                            else $sOBJ .= '>';
                        }
                        $sOBJ .= $this->DivValidarHTML($this->PrefijoLinea() . $this->sNombreCampo, '') . $this->DivValidarHTML("otext-" . $this->PrefijoLinea() . $this->sNombreCampo . $i, "if(document.getElementById('" . $this->PrefijoLinea() . $this->sNombreCampo . $i . "').checked==true)");
                }
                $Aux .= 'onClick="' . "try{document.getElementById('otext-" . $this->PrefijoLinea() . $this->sNombreCampo . $i . "').disabled=false;document.getElementById('otext-" . $this->PrefijoLinea() . $this->sNombreCampo . $i . "').focus()}catch(err){}" . '">' . $sAuxOpcion[1] . $sOBJ;
            } else {
                $sOBJ = '<input name="otext-' . $this->PrefijoLinea() . $this->sNombreCampo . $i . '" type="hidden" id="otext-' . $this->PrefijoLinea() . $this->sNombreCampo . $i . '" ' . $this->Bloqueado() . $this->ScriptObjetoHTML() . ' Value="' . $this->xValorMascara . '">';
                $Aux .= 'onClick="' . "try{document.getElementById('otext-" . $this->PrefijoLinea() . $this->sNombreCampo . $i . "').disabled=true;}catch(err){}" . '">' . $sAuxOpcion[1] . $this->DivValidarHTML($this->PrefijoLinea() . $this->sNombreCampo . $i, "if(document.getElementById('" . $this->PrefijoLinea() . $this->sNombreCampo . $i . "').checked==true) ") . $sOBJ;
            }
        }
        return $Aux;
    }

    function ObjetoHtmlLabel()
    {
        return '<div id="label' . $this->PrefijoLinea() . $this->sNombreCampo . '" align="' . $this->sAlineacionDescripcion . '">' . $this->xValorMascara . '</div><input name="' . $this->PrefijoLinea() . $this->sNombreCampo . '" type="hidden" id="' . $this->PrefijoLinea() . $this->sNombreCampo . '"' . $this->Bloqueado() . ' value="' . $this->xValor . '" onChange="' . "document.getElementById('label" . $this->PrefijoLinea() . $this->sNombreCampo . "').innerHTML=this.value;" . '">';
    }

    function ObjetoHtmlLabelSQL()
    {
        if ($this->xValorMascara <= 0) {
            if ($this->sComandoSQL != '') {
                $sSql = $this->sComandoSQL;
                $this->oConBDD->Conectar();
                $this->oConBDD->SetFormatoResultado('ORDENADO');
                if ($this->oConBDD->Query($sSql)) {
                    $this->xValor = $this->ComaPorPunto($this->oConBDD->f(0));
                    $this->xValorMascara = $this->xValor;
                }
                $this->oConBDD->Free();
                $this->oConBDD->Desconectar();
            } else
                $this->xValor = '';
        }
        return $this->ObjetoHtmlLabel();
    }

    function ObjetoHtmlLBL(){
        if($this->boostrap){
            if($this->sDescripcion=='') return '';
            else return '<label class="control-label" id="lbl'.$this->PrefijoLinea().$this->sNombreCampo.'" align="'.$this->sAlineacionDescripcion.'">'.$this->txtRequerido().$this->sDescripcion.':</label>';
        }else{
            if($this->sDescripcion=='') return '';
            else return '<div id="lbl'.$this->PrefijoLinea().$this->sNombreCampo.'" align="'.$this->sAlineacionDescripcion.'">'.$this->txtRequerido().$this->sDescripcion.':</div>';
        }

    }

    function ObjetoHtmlERR()
    {
        return '<div id="err' . $this->PrefijoLinea() . $this->sNombreCampo . '" class="CampoError">' . $this->sMensajeError . '</div>';
    }
    /*********************************************/

    /*********************************************
     * @ OpcionesCampoListaSQL()
     * Agrega las opciones a un campo lista SQl
     *********************************************/
    function OpcionesCampoListaSQL()
    {
        if ($this->sComandoSQL == '') return '';
        $Aux = '';
        if ($this->sCampoValueListaSQL == '' && $this->sCampoDescripcionListaSQL == '') {
            $Aux = '';
            $sSql = $this->sComandoSQL;
            $this->oConBDD->Conectar();
            $this->oConBDD->SetFormatoResultado('ORDENADO');
            if ($this->oConBDD->Query($sSql)) {
                if ($this->oConBDD->NumFilas() > 0) {
                    $obtener_keys = array_keys($this->oConBDD->ResRow);
                    $camp_sel = $obtener_keys[0];
                    $camp_val = $obtener_keys[1];

                    do {
                        if ($this->xValor != '') $this->xValorMascara = $this->oConBDD->f("$camp_val");
                        if ($this->xValor == $this->oConBDD->f("$camp_sel"))
                            $Aux .= '<option selected Value="' . $this->oConBDD->f("$camp_sel") . '">' . ($this->oConBDD->f("$camp_val") == null ? $this->oConBDD->f("$camp_sel") : $this->oConBDD->f("$camp_val")) . '</option>';
                        else
                            $Aux .= '<option Value="' . $this->oConBDD->f("$camp_sel") . '">' . ($this->oConBDD->f("$camp_val") == null ? $this->oConBDD->f("$camp_sel") : $this->oConBDD->f("$camp_val")) . '</option>';
                    } while ($this->oConBDD->SiguienteRegistro());
                }
                $this->oConBDD->Free();
            }
            $this->oConBDD->Desconectar();
        } else {
            $sSql = 'select ' . $this->sCampoValueListaSQL . ',' . $this->sCampoDescripcionListaSQL . ' ' . strstr(strtolower($this->sComandoSQL), 'from');
            $this->oConBDD->Conectar();
            $this->oConBDD->Query($sSql);
            $sCampos = split(',', $this->sCampoValueListaSQL);
            do {
                $sAuxVal = '';
                foreach ($sCampos as $sCamAux)
                    $sAuxVal .= $this->oConBDD->f($sCamAux);
                if ($this->xValor == $sAuxVal)
                    $Aux .= '<option selected Value="' . $sAuxVal . '">' . $this->oConBDD->f($this->sCampoDescripcionListaSQL) . '</option>';
                else
                    $Aux .= '<option Value="' . $sAuxVal . '">' . $this->oConBDD->f($this->sCampoDescripcionListaSQL) . '</option>';
            } while ($this->oConBDD->SiguienteRegistro());
            $this->oConBDD->Free();
            $this->oConBDD->Desconectar();
        }
        return $Aux;
    }
    /*********************************************/


    /*
    function OpcionesArbol(){
        if($this->sComandoSQL=='') return '';
        $Aux='';
        $sSql=$this->sComandoSQL;
        $this->oConBDD->queryN($sSql);
        if($this->oConBDD->Fila>0){
            $Aux=$this->arbol($this->oConBDD,0);
        }
        return $Aux;
    }

    function arbol($Conn,$nivel){
        $oConGeneral2 = new clsBDD;
        $oConGeneral2->DSN = $Conn->DSN;
        $texto="";
        if ($Conn->num_rows()>0){
            for($i=0;$i<$Conn->num_rows();$i++){
                $texto.="<option value='".$Conn->f(0)."'>".str_pad("",$nivel*3,".").$Conn->f(0)." - ".$Conn->f(1)."</option>";
                if($Conn->f(3)=="S"){
                    $sql2="Select codcate as cod,desccate as des,codcatep as pad,hijos from categorias where tipocate='".$this->nTipo."' and codcatep='".$Conn->f(0)."' order by codcate";
                    $oConGeneral2->query($sql2);
                    $texto.=$this->arbol($oConGeneral2,$nivel+1);
                }
                $Conn->SiguienteRegistro();
            }
        }
        return $texto;
    }

    //nuevo arbol
    function OpcionesArbol1(){
        if($this->sComandoSQL=='') return '';
        $Aux='';
        $sSql=$this->sComandoSQL;
        $this->oConBDD->queryN($sSql);
        if($this->oConBDD->Fila>0){
            $Aux=$this->arbol1($this->oConBDD,0);
        }
        return $Aux;
    }

    function OpcionesBarra(){
        if($this->sComandoSQL=='') return '';
        $Aux='';

        $sSql=$this->sComandoSQL;
    //	echo $sSql;
        $this->oConBDD->queryN($sSql);

        if($this->oConBDD->Fila>0){
            $Aux=$this->barra($this->oConBDD,0);
        }
        return $Aux;
    }


    function arbol1($Conn,$nivel){
        $oConGeneral2 = new clsBDD;
        $oConGeneral2->DSN = $Conn->DSN;
        $texto="";
        if ($Conn->num_rows()>0){
            for($i=0;$i<$Conn->num_rows();$i++){
                $sql2="Select codcate as cod,desccate as des,codcatep as pad from categorias where tipocate='".$this->nTipo."' and codcatep='".$Conn->f(0)."' order by codcate";
                $oConGeneral2->query($sql2);
                $texto.="<option value='".$Conn->f(0)."'>".str_pad("",$nivel*3,".")." ".$Conn->f(1)."</option>";
                $texto.=$this->arbol1($oConGeneral2,$nivel+1);
                $Conn->SiguienteRegistro();
            }
        }
        return $texto;
    }

    function barra($Conn,$nivel){
        $oConGeneral2 = new clsBDD;
        $oConGeneral2->DSN = $Conn->DSN;
        $texto="";
        if ($Conn->num_rows()>0){
            for($i=0;$i<$Conn->num_rows();$i++){
                $sql2="Select codcate as cod,desccate as des,codcatep as pad from categorias where tipocate='".$this->nTipo."' and codcatep='".$Conn->f(0)."' order by codcate";
    //			echo $sql2;
                $oConGeneral2->query($sql2);
                $texto.="<option value='".$Conn->f(0)."'>".str_pad("",$nivel*3,".")." ".$Conn->f(1)."</option>";
    //			$texto.=$this->barra($oConGeneral2,$nivel+1);
                $Conn->SiguienteRegistro();
            }
        }
        return $texto;
    }

    //fin nuevo arbol
    */
    function DivValidarHTML($sAuxNombre,$sCond){
        if ($this->bRequerido == true )
            $nAuxRequerido=1;
        else{
            if ($this->nTipo==0 || $this->nTipo==3 )
                return '';
            $nAuxRequerido=0;
        }
        $sAuxValidar=$sCond."ValidarCampo('".$this->PrefijoLinea().$this->sNombreCampo."',document.getElementById('".$sAuxNombre."'),".$nAuxRequerido.",".$this->nTipo.",'',";
        if($this->sValorMaximo!='')
            $sAuxValidar.=$this->sValorMaximo.',';
        else
            $sAuxValidar.="'',";
        if ($this->sValorMinimo!='')
            $sAuxValidar.=$this->sValorMinimo.',"'.$this->boostrap.'");';
        else
            $sAuxValidar.='"","'.$this->boostrap.'");';
        return '<div class="TextoValidar" id="val-'.$sAuxNombre.'" style="visibility:hidden;position:absolute;top:0px; left:0px;">'.$sAuxValidar.'</div>';
    }


    function ObjetoHTMLValorAnterior()
    {
        if ($this->bValidarDuplicado == true)
            return '<input name="valant-' . $this->PrefijoLinea() . $this->sNombreCampo . '" type="hidden" id="valant-' . $this->PrefijoLinea() . $this->sNombreCampo . '" Value="' . $this->xValorAnterior . '">';
        else
            return '';
    }

    function ScriptComandoAlQuitarEnfoque()
    {
        if ($this->sComandoAlQuitarEnfoque != '')
            return ' ' . $this->sComandoAlQuitarEnfoque . ' ';
        else
            return '';
    }

    function ScriptComandoAlPonerEnfoque()
    {
        if ($this->sComandoAlPonerEnfoque != '')
            return ' ' . $this->sComandoAlPonerEnfoque . ' ';
        else
            return '';
    }


    function ScriptObjetoHTML(){
        $Aux='';
        if($this->boostrap){
            switch($this->sTipoObjeto){
                case 'password':
                case 'text':
                case 'selectSQL':
                case 'textSQL':
                    $Aux.=' onBlur="'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    $Aux.=' onFocus="'.$this->ScriptComandoAlPonerEnfoque().'"';
                    if($this->sComandoAlCambiarValor!='')
                        $Aux.=' onChange="'.$this->sComandoAlCambiarValor.'"';
                    if($this->nTipo==1 ){
                        $Aux.=' onKeyUp="ValidarTeclas(45,57,event);validarletra(this);';
                        if($this->sComandoAlEscribir!="" )
                            $Aux.=$this->sComandoAlEscribir.';"';
                        else
                            $Aux.='"';
                        $Aux.=' onKeyDown="valorant(this)" ';
                    }else
                        $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    break;
                case 'arbol':
                    $Aux=' onChange="'.$this->sComandoAlCambiarValor.'"';
                    $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    $Aux.=' onBlur="'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    $Aux.=' onFocus="'.$this->ScriptComandoAlPonerEnfoque().'"';
                    break;
                case 'arbol1':
                    $Aux=' onChange="'.$this->sComandoAlCambiarValor.'"';
                    $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    $Aux.=' onBlur="'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    $Aux.=' onFocus="'.$this->ScriptComandoAlPonerEnfoque().'"';
                    break;

                case 'select':
                    $Aux=' onChange="'.$this->sComandoAlCambiarValor.'"';
                    $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    $Aux.=' onBlur="'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    $Aux.=' onFocus="'.$this->ScriptComandoAlPonerEnfoque().'"';
                    break;
                case 'fecha':
                    $Aux='onBlur="'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    $Aux.=' onFocus="'.$this->ScriptComandoAlPonerEnfoque().'"';
                    $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    $Aux.=' onBlur="'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    break;
                case 'si_no':
                    $Aux.=' onClick="'.$this->sComandoAlCambiarValor.'"';
                    $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    $Aux.=' onBlur="'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    break;
                case 'check':
                    $Aux.=' onClick="'.$this->sComandoAlCambiarValor.'"';
                    $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    $Aux.=' onBlur="'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    break;
            }
        }else{
            switch($this->sTipoObjeto){
                case 'password':
                case 'text':
                case 'selectSQL':
                case 'textSQL':
                    $Aux.=' onBlur="'."try{eval(document.getElementById('val-'+this.id).innerHTML)}catch(err){};borrar_buffer();".$this->ScriptComandoAlQuitarEnfoque().'"';
                    $Aux.=' onFocus="'.$this->ScriptComandoAlPonerEnfoque().'"';
                    if($this->sComandoAlCambiarValor!='')
                        $Aux.=' onChange="'.$this->sComandoAlCambiarValor.'"';
                    if($this->nTipo==1 ){
                        $Aux.=' onKeyUp="ValidarTeclas(45,57,event);validarletra(this);';
                        if($this->sComandoAlEscribir!="" )
                            $Aux.=$this->sComandoAlEscribir.';"';
                        else
                            $Aux.='"';
                        $Aux.=' onKeyDown="valorant(this)" ';
                    }else
                        $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    break;
                case 'arbol':
                    $Aux=' onChange="'.$this->sComandoAlCambiarValor.'"';
                    $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    $Aux.=' onBlur="'." try{eval(document.getElementById('val-'+this.id).innerHTML)}catch(err){};borrar_buffer();".$this->ScriptComandoAlQuitarEnfoque().'"';
                    $Aux.=' onFocus="'.$this->ScriptComandoAlPonerEnfoque().'"';
                    break;
                case 'arbol1':
                    $Aux=' onChange="'.$this->sComandoAlCambiarValor.'"';
                    $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    $Aux.=' onBlur="borrar_buffer();'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    $Aux.=' onFocus="'.$this->ScriptComandoAlPonerEnfoque().'"';
                    break;

                case 'select':
                    $Aux=' onChange="'.$this->sComandoAlCambiarValor.'"';
                    $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    $Aux.=' onBlur="borrar_buffer();'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    $Aux.=' onFocus="'.$this->ScriptComandoAlPonerEnfoque().'"';
                    break;
                case 'fecha':
                    $Aux=' onChange="'."try{eval(document.getElementById('val-'+this.id).innerHTML)}catch(err){};".'" onBlur="'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    $Aux.=' onFocus="'."try{eval(document.getElementById('val-'+this.id).innerHTML)}catch(err){};".$this->ScriptComandoAlPonerEnfoque().'"';
                    $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    $Aux.=' onBlur="borrar_buffer();"'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    break;
                case 'si_no':
                    $Aux.=' onClick="'.$this->sComandoAlCambiarValor.'"';
                    $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    $Aux.=' onBlur="borrar_buffer();"'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    break;
                case 'check':
                    $Aux.=' onClick="'.$this->sComandoAlCambiarValor.'"';
                    $Aux.=' onKeyUp="'.$this->sComandoAlEscribir.'"';
                    $Aux.=' onBlur="borrar_buffer();"'.$this->ScriptComandoAlQuitarEnfoque().'"';
                    break;
            }
        }

        return $Aux;
    }

    function ScriptOpcionCrear()
    {
        if ($this->bBloqueo == false && $this->sOpcionCrear != '') {
            $sAuxOpcionCrear = "AbriVentanaModal('" . $this->sOpcionCrear . "&control=" . $this->PrefijoLinea() . $this->sNombreCampo . "&ID=" . $_REQUEST['ID'] . "&IDB=" . $_REQUEST['IDB'] . "','inggenopc" . $this->PrefijoLinea() . $this->sNombreCampo . $_REQUEST['ID'] . "',300,120);";
            return '<img alt="CREAR" src="../iconos/categories.png"  onClick="' . $sAuxOpcionCrear . '" onMouseOver="this.className=' . "'Hand';" . '">';
        } else
            return '';
    }

    function ScriptOpcionBuscar()
    {
        if ($this->bBloqueo == false && $this->sOpcionBuscar != '') {
            if (strpos($this->sOpcionBuscar, '|')) {
                $sAuxOpcionBuscar = substr($this->sOpcionBuscar, strpos($this->sOpcionBuscar, '|') + 1);
                $sAuxOpcionBuscar = "AbriVentanaModalConScroll('" . $sAuxOpcionBuscar . "&ID=" . $_REQUEST['ID'] . "&IDB=" . $_REQUEST['IDB'] . "','buscar" . str_replace('-', '', $this->PrefijoLinea()) . str_replace('-', '', $this->sNombreCampo) . $_REQUEST['ID'] . "',600,400);";
            } else
                $sAuxOpcionBuscar = "AbriVentanaModalConScroll('" . $this->sOpcionBuscar . "&control=" . $this->PrefijoLinea() . $this->sNombreCampo . "&ID=" . $_REQUEST['ID'] . "&IDB=" . $_REQUEST['IDB'] . "','buscar" . str_replace('-', '', $this->PrefijoLinea()) . str_replace('-', '', $this->sNombreCampo) . $_REQUEST['ID'] . "',600,400);";
            return '<img name="imgBuscar' . $this->sNombreCampo . '" alt="BUSCAR" src="../iconos/query.png"  onClick="' . $sAuxOpcionBuscar . '" onMouseOver="this.className=' . "'Hand';" . '">';
        } else
            return '';
    }


    function ScriptComandoAlCambiarValor()
    {
        if ($bBloqueo == false && ScriptComandoAlCambiarValor != '')
            return ' ' . $sComandoAlCambiarValor . ' ';
        else
            return '';
    }

    function Validar()
    {

    //echo $this->sTipoObjeto;
        if ($this->sTipoObjeto == 'textSQL')
            $sAuxValor = $this->xValorMascara;
        else
            $sAuxValor = $this->xValor;
        $this->sMensajeError = '';

        if ($this->bRequerido && $sAuxValor == '') //Si es un campo requerido y no tiene ningun valor
            $this->sMensajeError = 'Ingrese un Valor';
        else {
            if (($this->sTipoObjeto != 'arbol') && ($this->sTipoObjeto != 'arbol1') && ($this->sTipoObjeto != 'barra')) {
                switch ($this->nTipo) { //Validacion de numeros y fechas
                    case 1:
                        if ($this->ValidarNumero($sAuxValor) == false) $this->sMensajeError = 'Valor numerico invalido';
                        break;
                    case 2:
                        if ($this->ValidarFecha($sAuxValor) == false) $this->sMensajeError = 'Fecha.....';
                        break;
                }
            }

            //echo 'm:'.$this->sMensajeError.'-dup:'.$this->bValidarDuplicado.'-nom:'.$this->sNombreCampo.'<br>';
            if ($this->sMensajeError == '' && $this->bValidarDuplicado == true) {
                if ($this->SQLValidarDuplicado($sAuxValor) > 0)
                    $this->sMensajeError = 'El Valor ya Existe';
            }
            if ($this->sMensajeError == '' && $this->bValidarExistente == true) {
                if ($sAuxValor != '') {
                    if ($this->SQLValidarExistente($sAuxValor) == false)
                        $this->sMensajeError = 'El Valor No Existe';
                }
            }
            //echo 'm:'.$this->sMensajeError.'-dup:'.$this->bValidarDuplicado.'-nom:'.$this->sNombreCampo.'<br>';
        }
    //echo $this->sNombreCampo." -- ".$this->bRequerido." -- ".$sAuxValor." -- ".$this->sMensajeError."<br>";

        if ($this->sMensajeError == '')
            return true; //el valor esta bien
        else
            return false;


    }

    //Realiza la validacion de un valor numerico, devuelve -TRUE- si el valor es correcto
    function ValidarNumero($sAuxValor)
    {
        if ($sAuxValor == '')
            return true;
        else {
            if (is_numeric($sAuxValor))
                return true;
            else
                return false;
        }
    }

    //Realiza la validacion de una fecha, devuelve -TRUE- si el valor es correcto
    function ValidarFecha($sAuxValor)
    {
        if ($sAuxValor == '')
            return true;
        else {
            if (checkdate($this->ExtraerFecha($sAuxValor, 'm'), $this->ExtraerFecha($sAuxValor, 'd'), $this->ExtraerFecha($sAuxValor, 'a')) > 0)
                return true;
            else
                return false;
        }
    }

    //Realiza una busqueda en el campo de una tabla para verificar que un valor existe y evitar duplicados, devuelve -TRUE- si el valor no existe
    function SQLValidarDuplicado($sAuxValor)
    {
        $sSql = "Select count(" . $this->sNombreCampo . ") as numreg from comcercial." . $this->sTablaBDD . " where " . $this->sNombreCampo . " ='" . $this->SqlHTMLEncode($sAuxValor) . "'";
        if ($this->xValorAnterior != '') {
            if ($this->xValorAnterior == $sAuxValor)
                $sSql .= " and " . $this->sNombreCampo . " <>'" . $this->SqlHTMLEncode($this->xValorAnterior) . "'";
        }
        $this->oConBDD->Conectar();
        $this->oConBDD->Query($sSql);
        $sAux = $this->oConBDD->f('numreg');
        $this->oConBDD->Free();
        $this->oConBDD->Desconectar();
        return $sAux;
    }

    //Realiza una busqueda en el campo de una tabla para verificar que un valor existe y evitar duplicados, devuelve -TRUE- si el valor no existe
    function SQLValidarExistente($sAuxValor)
    {
        $sSql = "Select " . $this->sCampoForaneoCod . "," . $this->sCampoForaneoDes . " from comercial." . $this->sTablaForanea . " where " . $this->sCampoForaneoDes . " ='" . $this->SqlHTMLEncode($sAuxValor) . "'";
        $this->oConBDD->Conectar();
        $this->oConBDD->SetFormatoResultado('ORDENADO');
        $this->oConBDD->Query($sSql);
        //echo $sSql." <br>".$this->oConBDD->num_rows()."<br>";
        if ($this->oConBDD->Fila > 0) {
            $xValor = $this->oConBDD->f(0);
            $this->oConBDD->Free();
            $this->oConBDD->Desconectar();
            return true;
        } else {
            $this->oConBDD->Free();
            $this->oConBDD->Desconectar();
            return false;
        }
    }

    function BuscarValorMascara($sAuxValor)
    {
        $sSql = "Select " . $this->sCampoForaneoCod . "," . $this->sCampoForaneoDes . " from comercial." . $this->sTablaForanea . " where " . $this->sCampoForaneoCod . " ='" . $this->SqlHTMLEncode($sAuxValor) . "'";
        //echo $sSql." AQUI";
        $this->oConBDD->Conectar();
        $this->oConBDD->SetFormatoResultado('ORDENADO');
        $this->oConBDD->Query($sSql);
        if ($this->oConBDD->Fila > 0) {
            $sAux = $this->oConBDD->f(1);
            $this->oConBDD->Free();
            $this->oConBDD->Desconectar();
            return $sAux;
        } else {
            $this->oConBDD->Free();
            $this->oConBDD->Desconectar();
            return '';
        }
    }

    function BuscarValorConMascara($sAuxValor)
    {
        if (($this->sNombreCampo != "txtnomcte31") && ($this->sNombreCampo != "nocte31_2")) {
            $sSql = "Select " . $this->sCampoForaneoCod . " from comercial." . $this->sTablaForanea . " where " . $this->sCampoForaneoDes . " ='" . $this->SqlHTMLEncode($sAuxValor) . "'";
            //echo $sSql." AQUI";
            $this->oConBDD->Conectar();
            $this->oConBDD->SetFormatoResultado('ORDENADO');
            $this->oConBDD->Query($sSql);
            if ($this->oConBDD->Fila > 0) {
                $sAux = $this->oConBDD->f(0);
                $this->oConBDD->Free();
                $this->oConBDD->Desconectar();
                return $sAux;
            } else {
                $this->oConBDD->Free();
                $this->oConBDD->Desconectar();
                return '';
            }
        }
    }

    function Foco()
    {
        switch ($this->sTipoObjeto) {
            case 'fecha':
                $Aux = "foco('dia" . $this->PrefijoLinea() . $this->sNombreCampo . "');";
                break;
            case 'textSQL':
                $Aux = "foco('des" . $this->PrefijoLinea() . $this->sNombreCampo . "');";
                break;
            default:
                $Aux = "foco('" . $this->PrefijoLinea() . $this->sNombreCampo . "');";
        }
        return $Aux;
    }

    //Asigna el valor enviado por el campo del Formulario al hacer -SUBMIT- a la popiedad $xValor y se asigna un valor por default en caso de que el formulario no haya enviado ningun valor y el campo no sea requerido.
    //Si el campo es compuesto se recoge los valores de todos los campo compuestos y se los asigna a la propiedad $xValor
    function LeerValorFormulario()
    {
        if (isset($_POST[$this->PrefijoLinea() . $this->sNombreCampo])) $this->xValor = $this->SqlHTMLEncode($_POST[$this->PrefijoLinea() . $this->sNombreCampo]);
        //else $this->xValor=$this->xValorDefault;
        switch ($this->sTipoObjeto) {
            case 'label':
                if (isset($_POST['des' . $this->PrefijoLinea() . $this->sNombreCampo]))
                    $this->xValorMascara = $this->SqlHTMLEncode($_POST['des' . $this->PrefijoLinea() . $this->sNombreCampo]);
                break;
            case 'textSQL':
                if (isset($_POST['des' . $this->PrefijoLinea() . $this->sNombreCampo]))
                    $this->xValorMascara = $this->SqlHTMLEncode($_POST['des' . $this->PrefijoLinea() . $this->sNombreCampo]);
                if ($this->xValor == '' && $this->xValorMascara != '') {
                    $this->xValor = $this->BuscarValorConMascara($this->xValorMascara);
                }
                break;
            case 'grupo':
                $i = 0;
                foreach ($this->vsOpciones as $sAuxvsOpcion) {
                    $sAuxOpcion = explode('|', $sAuxvsOpcion);
                    if ($sAuxOpcion[1] == $this->xValor) {
                        if ($sAuxOpcion[0] != 'si_no')
                            $this->xValorMascara = $this->SqlHTMLEncode($_POST['otext-' . $this->PrefijoLinea() . $this->sNombreCampo . $i]);
                        break;
                    }
                    $i++;
                }
        }
        if ($this->xValor == '' && $this->bRequerido == false && $this->sTipoObjeto != 'text')
            $this->xValor = $this->xValorDefault;

        switch ($this->nTipo) {
            case 1:
                $this->xValor = str_replace(',', '.', $this->xValor);
                break;
            case 3:
                if (($this->sTipoObjeto != 'arbol') && ($this->sTipoObjeto != 'arbol1')) {
                    $this->xValor = str_replace(92, '/', $this->xValor);

                } else {
                    if ($this->sTipoObjeto == '') {
                        $this->xValor = str_replace(92, '/', $this->xValor);
                    }
                }
        }
        if ($this->bValidarDuplicado == true && isset($_POST['valant-' . $this->PrefijoLinea() . $this->sNombreCampo])) $this->xValorAnterior = $this->SqlHTMLEncode($_POST['valant-' . $this->PrefijoLinea() . $this->sNombreCampo]);
    //echo " (".$this->sNombreCampo."=" .$this->xValor. ") ";
    }

    function MesLetras($iMes)
    {
        switch ($iMes) {
            case 1:
                return 'Enero';
                break;
            case 2:
                return 'Febrero';
                break;
            case 3:
                return 'Marzo';
                break;
            case 4:
                return 'Abril';
                break;
            case 5:
                return 'Mayo';
                break;
            case 6:
                return 'Junio';
                break;
            case 7:
                return 'Julio';
                break;
            case 8:
                return 'Agosto';
                break;
            case 9:
                return 'Septiembre';
                break;
            case 10:
                return 'Octubre';
                break;
            case 11:
                return 'Noviembre';
                break;
            case 12:
                return 'Diciembre';
                break;
        }
    }

    function Mes3Letras($iMes)
    {
        switch ($iMes) {
            case 1:
                return 'Ene';
                break;
            case 2:
                return 'Feb';
                break;
            case 3:
                return 'Mar';
                break;
            case 4:
                return 'Abr';
                break;
            case 5:
                return 'May';
                break;
            case 6:
                return 'Jun';
                break;
            case 7:
                return 'Jul';
                break;
            case 8:
                return 'Ago';
                break;
            case 9:
                return 'Sep';
                break;
            case 10:
                return 'Oct';
                break;
            case 11:
                return 'Nov';
                break;
            case 12:
                return 'Dic';
                break;
        }
    }

    function ScriptDoDesbloqueo()
    {
        $Aux = 'try{';
        switch ($this->sTipoObjeto) {
            case 'textSQL':
                $Aux .= "document.getElementById('" . $this->PrefijoLinea() . $this->sNombreCampo . "').disabled=false;";
                $Aux .= "document.getElementById('des" . $this->PrefijoLinea() . $this->sNombreCampo . "').disabled=false;";
                break;
            case 'text':
            case 'arbol':
            case 'arbol1':
            case 'textarea':
            case 'selectSQL':
            case 'select':
            case 'file':
            case 'textSQL':
            case 'si_no':
            case 'fechaLBL':
            case 'label':
            case 'fechaHoraLBL':
                $Aux .= "document.getElementById('" . $this->PrefijoLinea() . $this->sNombreCampo . "').disabled=false;";
                break;
            case 'fecha':
                $Aux .= "document.getElementById('" . $this->PrefijoLinea() . $this->sNombreCampo . "').disabled=false;";
                break;
            case 'hidden':
            case 'labelSQL':
                return '';
                break;
            case 'grupo':
                for ($i = 0; $i < count($this->vsOpciones); $i++) {
                    $Aux .= "document.getElementById('" . $this->PrefijoLinea() . $this->sNombreCampo . $i . "').disabled=false;";
                    $Aux .= "document.getElementById('otext-" . $this->PrefijoLinea() . $this->sNombreCampo . $i . "').disabled=false;";
                }
        }
        return $Aux .= '} catch(err){};';
    }


    function ComaPorPunto($sval)
    {
        if (is_null($sval))
            return '0.0';
        else {
            if (strpos($sval, ',')) {
                $sval = ereg_replace($sval, ',', '.');
                if (strpos($sval, '.')) {
                    $iPos = strpos($sval, '.');
                    if ($iPos + 2 < strlen($sval))
                        $sval = substr($sval, 0, $iPos + 2);
                }
            }
            return $sval;
        }
        //echo $sval;
    }

    function SqlHTMLEncode($sval)
    {
        $sval = str_replace(chr(34), '&#34;', $sval);
        $sval = str_replace(chr(39), '&#39;', $sval);
        return $sval;
    }

    function ExtraerFecha($sval, $op)
    {

        $explode_fecha = explode('/', $sval);
        if (count($explode_fecha) < 3) {
            /** Viene formato (-) 2022-06-31 */

            $sval = str_replace("-", "/", $sval);
        }

        if (strpos($sval, ' '))
            if (strpos($sval, '/'))
                $Aux = explode('/', substr($sval, 0, strpos($sval, ' ')));
            else
                $Aux = explode('/', substr($sval, 0, strpos($sval, ' ')));
        else
            if (strpos($sval, '/'))
                $Aux = explode('/', $sval);
            else
                $Aux = explode('/', $sval);
        switch ($op) {
            case 'd':
                return $Aux[2];
            case 'm':
                return $Aux[1];
            case 'a':
                return $Aux[0];
        }
    }
}

?>