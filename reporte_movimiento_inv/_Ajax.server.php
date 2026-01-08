<?php

require("_Ajax.comun.php"); // No modificar esta linea
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  // S E R V I D O R   A J A X //
  :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
ini_set('memory_limit', '1024M');

/* * ******************************************* */
/* FCA01 :: GENERA INGRESO TABLA PRESUPUESTO  */
/* * ******************************************* */
function formulario_etiqueta($id, $aForm)
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    //variables de session
    unset($_SESSION['ARRAY_ETIQUETAS']);
    $idempresa=$aForm['empresa'];
    if(empty($idempresa)) $idempresa = $_SESSION['U_EMPRESA'];

    $idsucursal=$aForm['sucursal'];
    if(empty($idsucursal)) $idsucursal = $_SESSION['U_SUCURSAL'];


    $sHtml  = '<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">LISTA DE PROFORMAS</h4>
		</div>
		<div class="modal-body">';

        $sHtml .= '
		<div class="row" style="margin-top: 15px">
			<div class="col-md-4">
				<div class="form-group">
					<label for="exampleInputEmail1">Ancho (cm)</label>
					<input type="number" class="form-control" id="ancho" name="ancho" placeholder="Ancho">
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label for="exampleInputPassword1">Alto (cm)</label>
					<input type="number" class="form-control" id="alto" name="alto" placeholder="Alto">
				</div>
			</div>
			<div class="col-md-3" style="text-align: center">
				<div class="btn btn-primary" style="margin-top:25px;"  onClick="javascript:procesar( )">Imprimir</div>
			</div>
		</div>
		
		
		
		
	';
    
 /*   $sHtml = '<fieldset style="border:#999999 1px solid; padding:2px; text-align:center; width:98%; margin-top:1px;" align="center">
                <legend class="Titulo" style="font-size: 9px;">Generar Etiquetas</legend>
                <table style="width:98%" align="center">
					<tr>
						<td align="left" colspan="2">
						<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/full_page24.png"
						title = "Presione para Cerrar";
						style="cursor: pointer;"
						onclick="genera_formulario();"
						alt="Imprimir"
						align="bottom" />
					   
						<td align="right">
						<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/ico-salir.png"
						title = "Presione para Cerrar";
						style="cursor: pointer;"
						onclick="parent.cerrar_ventana();"
						alt="Imprimir"
						align="bottom" />
						</td>
					</tr>
                <tr>
					<td class="labelFrm" align="left">' . $fu->ObjetoHtmlLBL('ancho') . '</td>
					<td  align="left">' . $fu->ObjetoHtml('ancho') . '</td>
                    <td class="labelFrm" align="left">' . $fu->ObjetoHtmlLBL('alto') . '</td>
					<td  align="left">' . $fu->ObjetoHtml('alto') . '</td>
					<td align="right">
						<input type="button" value="GENERAR"
						onClick="javascript:procesar( )"
						class="myButton_BT"
						style="width:100px; height: 25px;"/> 
					</td>
				</tr>
				</table>';*/

 
				
       $sHtml .= '<table  class="table table-striped table-bordered table-hover table-condensed" style=" width: 100%; margin-top: 0px;" align="left">
                </tr>
					<th class="diagrama">#</th>
					<th class="diagrama">CODIGO</th>
					<th class="diagrama">PRODUCTO</th>
					<th class="diagrama">MARCA</th>
					<th class="diagrama">COD. BARRAS</th>
					<th class="diagrama">PRECIO</th>
					<th class="diagrama">CANTIDAD</th>
					<th class="diagrama" style="width: 80px;">CHECK<br><input id="ch_imp" type="checkbox"  onclick="marcar(this);"></th>
                <tr>';

    $sql1 = "select minv_num_comp, minv_num_sec, dmov_can_dmov, prod_cod_barra, 
			prbo_cod_bode, prbo_dis_prod, prbo_uco_prod, prod_cod_colr,
			dmov_cod_prod, prod_nom_prod, prod_cod_talla, prod_cod_marc, dmov_cod_dmov, dmov_bod_envi
			from saeminv, saedmov, saeprod, saeprbo 
			where minv_num_comp = dmov_num_comp and 
			minv_cod_empr = dmov_cod_empr and
			minv_cod_sucu = dmov_cod_sucu and
			prod_cod_prod = prbo_cod_prod and
			prod_cod_prod = dmov_cod_prod and
            prod_cod_empr = dmov_cod_empr and 
			prod_cod_sucu = dmov_cod_sucu and 
            dmov_cod_bode = prbo_cod_bode and
			dmov_cod_empr = prbo_cod_empr and
            prbo_cod_sucu = $idsucursal and 
			minv_num_comp = $id and
			minv_cod_empr = $idempresa and
			minv_cod_sucu = $idsucursal and
			minv_est_minv <> '0'";
    if ($oIfx->Query($sql1)) {
        if ($oIfx->NumFilas() > 0) {
            $i = 1;
            unset($arrayEtiqueta);
            do {
                $minv_num_comp  = $oIfx->f('minv_num_comp');
                $prod_cod_prod  = $oIfx->f('dmov_cod_prod');
                $prod_nom_prod  = $oIfx->f('prod_nom_prod');
                $prod_cod_barra = $oIfx->f('prod_cod_barra');
                $prbo_cod_bode  = $oIfx->f('prbo_cod_bode');
                $prod_cod_talla = $oIfx->f('prod_cod_talla');
                $prbo_dis_prod  = $oIfx->f('prbo_dis_prod');
                $prbo_uco_prod  = $oIfx->f('prbo_uco_prod');
                $dmov_can_dmov  = $oIfx->f('dmov_can_dmov');
                $dmov_cod       = $oIfx->f('dmov_cod_dmov');

                //query precio
                $sql = "select ppr_pre_raun from saeppr where ppr_cod_bode = $prbo_cod_bode and ppr_cod_prod = '$prod_cod_prod' and ppr_cod_nomp = 1";
                $ppr_pre_raun = consulta_string_func($sql, 'ppr_pre_raun', $oIfxA, 0);

                if (empty($prod_cod_talla)) {
                    $nomtalla = '';
                } else {
                    $sqltalla = "select talla_cod_talla,talla_nom_talla from saetalla where talla_cod_talla= $prod_cod_talla  ";
                    $nomtalla = consulta_string_func($sqltalla, 'talla_nom_talla', $oIfxA, '');
                }

                $prod_cod_colr = $oIfx->f('prod_cod_colr');

                if (empty($prod_cod_colr)) {
                    $nomcolor = '';
                } else {
                    $sqlcolor = "select color_cod_serial,color_nom_color from saecolor where color_cod_serial = $prod_cod_colr  ";
                    $nomcolor = consulta_string_func($sqlcolor, 'color_nom_color', $oIfxA, '');
                }

                $prod_cod_marc = $oIfx->f('prod_cod_marc');
                $sqlmacr = "select marc_cod_marc,marc_des_marc from saemarc where marc_cod_marc = $prod_cod_marc";
                $marca = consulta_string_func($sqlmacr, 'marc_des_marc', $oIfxA, '');

                // SERIALES
                $serial = $minv_num_comp . '_' . $dmov_cod;

                $ifu->AgregarCampoCheck($serial . '_check', 'S/N',   false, 'N');
                $ifu->AgregarCampoNumerico($serial . '_stock', '',     false, $dmov_can_dmov, 100, 10);


                $bode_envi = $oIfx->f('dmov_bod_envi');
                if(empty($bode_envi)){
                    $bode_envi = $prbo_cod_bode;
                }

                $sqlsu="select subo_cod_sucu from saesubo where subo_cod_bode=$bode_envi and subo_cod_empr=$idempresa";
                $sucu_bode = consulta_string_func($sqlsu, 'subo_cod_sucu', $oIfxA, 0);

                $arrayEtiqueta[] = array($prod_cod_prod, $marca, $nomcolor, $nomtalla, $ppr_pre_raun, $serial,$idempresa,$sucu_bode, $bode_envi);

                if ($sClass == 'on')
                    $sClass = 'off';
                else
                    $sClass = 'on';

                $sHtml .= '<tr height="20"  class="' . $sClass . '"
                                onMouseOver="javascript:this.className=\'link\';"
                                onMouseOut="javascript:this.className=\'' . $sClass . '\';">
                                 <td width="10px;" class="fecha_letra">' . $i . '</td>                              
                                 <td align="left">' . $prod_cod_prod . '</td>
                                 <td align="left">' . $prod_nom_prod . '</td>
                                 <td align="left">' . $marca . '</td>
                                 <td align="left">' . $prod_cod_barra . '</td>
								 <td align="right">' . $ppr_pre_raun . '</td>
                                 <td align="right">' . $ifu->ObjetoHtml($serial . '_stock') . '</td>
                                 <td align="center">' . $ifu->ObjetoHtml($serial . '_check') . '</td>                             
                            </tr>';
                $i++;
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $sHtml .= '</table>';

    $_SESSION['ARRAY_ETIQUETAS'] = $arrayEtiqueta;

    
        $sHtml .= '</div>
				<div class="modal-footer">
				        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
				</div>
				</div>
			</div>
		</div>';

        $oReturn->assign("ModalEtiquetas", "innerHTML", $sHtml);
        //$oReturn->script("init('tbprof')");

        return $oReturn;
}

function enviar_etiquetas($aForm = '')
{

    global $DSN_Ifx, $DSN;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //variables de session
    unset($_SESSION['LIST_CHECK_ETIQUETAS']);
    $id_empresa = $_SESSION['U_EMPRESA'];
    $id_sucursal = $_SESSION['U_SUCURSAL'];
    $array_etiq = $_SESSION['ARRAY_ETIQUETAS'];

    $desde = $aForm['desde'];
    $cant = $aForm['cantidad'];
    $etiquetam = $aForm['etiquetam'];

    unset($etiqueta);
    if (count($array_etiq) > 0) {
        foreach ($array_etiq as $val) {
            $serial = $val[5];
            $check = $aForm[$serial . '_check'];
            if (!empty($check)) {
                $prod = $val[0];
                $marca = $val[1];
                $color = $val[2];
                $talla = $val[3];
                $preci = $val[4];

                $empr = $val[6];
                $sucu = $val[7];
                $bode = $val[8];
                $can = $aForm[$serial . '_stock'];

                $etiqueta[] = array($marca, $color, $talla, $preci, $can, $prod,$empr,$sucu,$bode);
            } // fin check


        } // fin foreach


        $_SESSION['LIST_CHECK_ETIQUETAS'] = $etiqueta;

        $oReturn->script('etiquetasPrint();');
    } else {
        $oReturn->alert('Por favor realice una Busqueda...');
    }

    return $oReturn;
}

function genera_cabecera_formulario($sAccion = 'nuevo', $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario();
    $fu->DSN = $DSN;

    $ifu = new Formulario();
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    // VARIABLES
    $idempresa = $_SESSION['U_EMPRESA'];

    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    //----------------------------------------------------
    // LEO SUPLIDOR
    //----------------------------------------------------
    $cliente        =  $aForm['cliente'];
    $cliente_nombre = $aForm['cliente_nombre'];
    //----------------------------------------------------
    // FIN SUPLIDOR
    //----------------------------------------------------

    
    switch ($sAccion) {
        case 'nuevo':
                                                                
            $ifu->AgregarCampoListaSQL('empresa', 'Empresa|left',"SELECT empr_cod_empr, empr_nom_empr from saeempr where empr_cod_empr = $idempresa", true, 170, 150,true);
            $ifu->AgregarComandoAlCambiarValor('empresa', 'cargar_sucursal()');

            $ifu->AgregarCampoListaSQL('sucursal', 'Sucursal|left', "", true, 170, 150, true);

            $ifu->AgregarCampoListaSQL('bodega', 'Bodega|left', "", true, 170, 150, true);

            $ifu->AgregarCampoFecha('fecha_inicio', 'Fecha Inicio|left', true, date('Y') . '/' . date('m') . '/' . date('d'), 70,20,true);

            $ifu->AgregarCampoFecha('fecha_fin', 'Fecha Final|left', true, date('Y') . '/' . date('m') . '/' . date('d'), 70,20,true);

            $select .= '<select multiple name="tran"></select>';

            break;

        case 'sucursal':
                                                                                
            $ifu->AgregarCampoListaSQL('empresa', 'Empresa|left',"SELECT empr_cod_empr, empr_nom_empr from saeempr where empr_cod_empr = $idempresa", true, 170, 150,true);
            $ifu->AgregarComandoAlCambiarValor('empresa', 'cargar_sucursal()');

            $ifu->AgregarCampoListaSQL('sucursal', 'Sucursal|left', "SELECT sucu_cod_sucu, sucu_nom_sucu from saesucu where sucu_cod_empr = $empresa", true, 170, 150,true);
            $ifu->AgregarComandoAlCambiarValor('sucursal', 'cargar_tran()');

            $ifu->AgregarCampoListaSQL('bodega', 'Bodega|left', "", true, 170, 150, true);

            $ifu->AgregarCampoFecha('fecha_inicio', 'Fecha Inicio|left', true, date('Y') . '/' . date('m') . '/' . date('d'), 70,20,true);

            $ifu->AgregarCampoFecha('fecha_fin', 'Fecha Final|left', true, date('Y') . '/' . date('m') . '/' . date('d'), 70,20,true);

            $ifu->cCampos["empresa"]->xValor = $empresa;

            $select .= '<select multiple name="tran"></select>';

            break;

        case 'tran':
                                                                        
            $ifu->AgregarCampoListaSQL('empresa', 'Empresa|left',"SELECT empr_cod_empr, empr_nom_empr from saeempr where empr_cod_empr = $idempresa", true, 170, 150,true);
            $ifu->AgregarComandoAlCambiarValor('empresa', 'cargar_sucursal()');

            $ifu->AgregarCampoListaSQL('sucursal', 'Sucursal|left',"SELECT sucu_cod_sucu, sucu_nom_sucu from saesucu where sucu_cod_empr = $empresa", true, 170, 150,true);
            $ifu->AgregarComandoAlCambiarValor('sucursal', 'cargar_tran()');

            $ifu->AgregarCampoListaSQL('bodega', 'Bodega|left', "select  b.bode_cod_bode, b.bode_nom_bode from saebode b, saesubo s where
																b.bode_cod_bode = s.subo_cod_bode and
																b.bode_cod_empr = $empresa and
																s.subo_cod_empr = $empresa and
																s.subo_cod_sucu = $sucursal", true, 170, 150,true);

            $select .= '<select multiple name="tran" size="5">';
            $query = "select tran_cod_tran, tran_des_tran from saetran where tran_cod_empr = $empresa and tran_cod_sucu = $sucursal and tran_cod_modu = 10 order by tran_des_tran";
            if ($oIfx->Query($query)) {
                if ($oIfx->NumFilas() > 0) {
                    do {

                        $select .= '<option style="font-size: 10px; color: blue" value="' . $oIfx->f('tran_cod_tran') . '">' . $oIfx->f('tran_des_tran') . '</option>';
                    } while ($oIfx->SiguienteRegistro());
                }
            }
            $select .= '</select>';
            $oIfx->Free();


            $ifu->AgregarCampoFecha('fecha_inicio', 'Fecha Inicio|left', true, date('Y') . '/' . date('m') . '/' . date('d'), 70,20,true);

            $ifu->AgregarCampoFecha('fecha_fin', 'Fecha Final|left', true, date('Y') . '/' . date('m') . '/' . date('d'), 70,20,true);

            $ifu->cCampos["empresa"]->xValor = $empresa;
            $ifu->cCampos["sucursal"]->xValor = $sucursal;

            break;
    }

    $sHtml .= '<table class="table table-striped table-condensed" style="width: 90%; margin-bottom: 0px;" align="center"> ';
    $sHtml .= '<tr>
						<td align="left" colspan="12">
							<div class="btn-group">
								<div class="btn btn-primary btn-sm" onclick="genera_formulario();">
									<span class="glyphicon glyphicon-file"></span>
									Nuevo
								</div>
																	
								<div class="btn btn-primary btn-sm" onclick="abrir();">
									<span class="glyphicon glyphicon-print"></span>
									Imprimir
								</div>	
								<div class="btn btn-primary btn-sm" onclick="consultar();">
								<span class="glyphicon glyphicon-search"></span>
								Consultar
							</div>
							</div>
						</td>
                </tr>					
				<tr>
						<td class="bg-primary" align="center" colspan="4">REPORTE MOVIMIENTOS DE INVENTARIO</td>
				</tr>
				<tr>
						<td>' . $ifu->ObjetoHtmlLBL('empresa') . '</td>
						<td>' . $ifu->ObjetoHtml('empresa') . '</td>
						<td>' . $ifu->ObjetoHtmlLBL('sucursal') . '</td>
						<td>' . $ifu->ObjetoHtml('sucursal') . '</td>
				</tr>
				<tr>
						<td>' . $ifu->ObjetoHtmlLBL('bodega') . '</td>
						<td>' . $ifu->ObjetoHtml('bodega') . '</td>
						<td>Transaccion:</td>
						<td colspan="3">' . $select . '</td>
				<tr>

                <!-- INICIO CAMPO SUPLIDOR -->
                <tr>
                    <td>
                        <label class="control-label" for="cliente_nombre">* Suplidor:</label>
                    </td>

                    <td colspan="4">
                        <div class="">
                            <div class="input-group">
                                
                                <!-- Código del proveedor oculto -->
                                <input type="hidden" id="cliente" name="cliente" value="' . $cliente . '">

                                <!-- Nombre del proveedor -->
                                <input type="text"
                                class="form-control input-sm"
                                id="cliente_nombre"
                                name="cliente_nombre"
                                value="' . $cliente_nombre . '"
                                placeholder="ESCRIBA SUPLIDOR Y PRESIONE ENTER"
                                onkeyup="autocompletar(' . $idempresa . ', event ); 
                                            form1.cliente_nombre.value=form1.cliente_nombre.value.toUpperCase();">

                                <span class="input-group-addon primary"
                                    style="cursor: pointer;"
                                    onclick="autocompletar_btn(' . $idempresa . ');">
                                    <i class="fa fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>
                <!-- FIN INICIO CAMPO SUPLIDOR -->

						<td>' . $ifu->ObjetoHtmlLBL('fecha_inicio') . '</td>
						<td>
							<input type="date" id="fecha_inicio" name="fecha_inicio" step="1" value="' . date("Y-m-d") . '" class="form-control" style="width: 170px;">   
						</td>
						<td>' . $ifu->ObjetoHtmlLBL('fecha_fin') . '</td>
						<td>
							<input type="date" id="fecha_fin" name="fecha_fin" step="1" value="' . date("Y-m-d") . '" class="form-control" style="width: 170px;">   
						</td>

				</tr>
						<td colspan="4" align="center">
							
						</td>
                </tr>';

    $sHtml .= '</table>';
    $oReturn->assign("DivPresupuesto", "innerHTML", $sHtml);
    $oReturn->assign("DivReporte", "innerHTML", '');
    return $oReturn;
}

//----------------------------------------------------------------------
//FUNCION AUTOCOMPLETAR SUPLIDORES
//----------------------------------------------------------------------
function clpv_reporte($aForm = '')
{
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo();
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $idempresa  = $aForm['empresa'];
    $idsucursal = $aForm['sucursal'];
    $clpv_nom   = $aForm['cliente_nombre'];
    $clpv_ruc   = $aForm['ruc'];

    $sql_tmp = '';
    if (!empty($clpv_nom)) {
        $sql_tmp = " and clpv_nom_clpv like '%$clpv_nom%' OR clpv_cod_char  ='$clpv_nom'";
    }

    $sql_tmp2 = '';
    if (!empty($clpv_ruc)) {
        $sql_tmp2 = " and clpv_ruc_clpv like '%$clpv_ruc%' ";
    }

    $oReturn = new xajaxResponse();
    
    //$sHtml = "";
    $sHtml  .= '<div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">SUPLIDORES</h4>
                        </div>
                        <div class="modal-body">';

    $sHtml .= ' <table id="tbclientes"  class="table table-striped table-condensed table-bordered table-hover" style="width: 98%; margin-top: 20px;" align="center">';
    $sHtml .= '<thead>';
    $sHtml .= ' <tr>
                        <td class="fecha_letra">No-</td>
						<td class="fecha_letra" align="center">Codigo</td>
                        <td class="fecha_letra" align="center">Nombre</td>
                        <td class="fecha_letra" align="center">Subcliente</td>
                        <td class="fecha_letra" align="center">Vendedor</td>
                        <td class="fecha_letra" align="center">Identicacion</td>             
                        <td class="fecha_letra" align="center">Contribuyente Especial</td>             
                        <td class="fecha_letra" align="center">Estado</td>   
                    </tr>';
    $sHtml .= '</thead>';
    $sHtml .= '<tbody>';



    // ---------------------------------------------------------------------------------------------------------
    // CONTROL CLPV POR USUARIO, SUCURSALES
    // ---------------------------------------------------------------------------------------------------------
    $id_usuario_comercial = $_SESSION['U_ID'];
    $bloqueo_sucu_sn = 'N';
    $sucursales_usuario = '';
    $sql_data_usuario_sucu = "SELECT bloqueo_sucu_sn, sucursales_usuario from comercial.usuario where usuario_id = $id_usuario_comercial";
    if ($oIfx->Query($sql_data_usuario_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $bloqueo_sucu_sn = $oIfx->f('bloqueo_sucu_sn');
                $sucursales_usuario = $oIfx->f('sucursales_usuario');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $sql_adicional_sucu = "";
    $oIfx->Free();
    if ($bloqueo_sucu_sn == 'S') {
        if (!empty($sucursales_usuario)) {
            $sql_adicional_sucu = ' and clpv_cod_sucu in (' . $sucursales_usuario . ')';
        }
    }
    // ---------------------------------------------------------------------------------------------------------
    // FIN CONTROL CLPV POR USUARIO, SUCURSALES
    // ---------------------------------------------------------------------------------------------------------


    $sql = "select clpv_cod_clpv, clpv_nom_clpv,  clpv_ruc_clpv, clpv_est_clpv,
                        clpv_cod_fpagop, clpv_cod_tpago, clpv_pro_pago, clpv_etu_clpv, clpv_cod_cuen,
                        clpv_cod_vend, clpv_cot_clpv, clpv_pre_ven from saeclpv where
                        clpv_cod_empr   = $idempresa and
                        clpv_clopv_clpv = 'PV'
                        and clpv_est_clpv = 'A'  
                        $sql_tmp 
                        $sql_tmp2  
                        $sql_adicional_sucu
                        order by 2 limit 50";

    $i = 1;
    $total = 0;
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $clpv_cod_clpv     = ($oIfx->f('clpv_cod_clpv'));
                $clpv_nom_clpv     = htmlentities($oIfx->f('clpv_nom_clpv'));
                $clpv_ruc_clpv     = $oIfx->f('clpv_ruc_clpv');
                $clpv_cod_fpagop = $oIfx->f('clpv_cod_fpagop');
                $clpv_cod_tpago = $oIfx->f('clpv_cod_tpago');
                $clpv_pro_pago  = $oIfx->f('clpv_pro_pago');
                $clpv_etu_clpv  = $oIfx->f('clpv_etu_clpv');
                $clpv_cod_vend  = $oIfx->f('clpv_cod_vend');
                $clpv_cot_clpv  = $oIfx->f('clpv_cot_clpv');
                $clpv_pre_ven   = $oIfx->f('clpv_pre_ven');


                $clpv_est_clpv = $oIfx->f('clpv_est_clpv');

                if ($clpv_est_clpv == 'A') {
                    $estado = 'ACTIVO';
                } elseif ($clpv_est_clpv == 'P') {
                    $estado = 'PENDIENTE';
                } elseif ($clpv_est_clpv == 'S') {
                    $estado = 'SUSPENDIDO';
                } else {
                    $estado = '--';
                }

                if ($clpv_etu_clpv == 1) {
                    $clpv_etu_clpv = 'S';
                } else {
                    $clpv_etu_clpv = 'N';
                }

                if (empty($clpv_pro_pago)) {
                    $clpv_pro_pago = 0;
                }

                /**
                 * Consulta Subcliente
                 */
                $sql_sub = "select count(*) as total from saeccli WHERE ccli_cod_clpv = '$clpv_cod_clpv' limit 1;";
                $sub_cliente = consulta_string_func($sql_sub, 'total', $oIfxA, 0);
                $sub_cliente_sn = ($sub_cliente > 0) ? 'SI' : 'NO';


                /**
                 * Consulta Vendedor
                 */
                $sql_vent = "select vend_cod_vend, vend_nom_vend from saevend where vend_cod_empr = $idempresa and vend_cod_vend = '$clpv_cod_vend'";
                $vendedor_info = consulta_string_func($sql_vent, 'vend_nom_vend', $oIfxA, '');


                // FECHA DE VENCIMIENTO
                $fecha_venc = (sumar_dias_func(date("Y-m-d"), $prove_dia)); //  Y/m/d
                list($a, $b, $c) = explode('-', $fecha_venc);
                $fecha_venc = $a . '-' . $b . '-' . $c;

                //direccion
                $sql = "select dire_dir_dire from saedire where dire_cod_empr = $idempresa and dire_cod_clpv = $clpv_cod_clpv";
                $dire = consulta_string_func($sql, 'dire_dir_dire', $oIfxA, '');

                //telefono
                $sql = "select tlcp_tlf_tlcp from saetlcp where tlcp_cod_empr = $idempresa and tlcp_cod_clpv = $clpv_cod_clpv";
                $telefono = consulta_string_func($sql, 'tlcp_tlf_tlcp', $oIfxA, '');

                // AUTORIZACION PROVE
                $sql = "select  max(coa_fec_vali) as coa_fec_vali, coa_aut_usua, coa_seri_docu, coa_fact_ini, coa_fact_fin
                            from saecoa where
                            clpv_cod_empr = $idempresa and
                            clpv_cod_clpv = $clpv_cod_clpv group by coa_fec_vali,2,3,4,5 ";
                $fec_cadu_prove = '';
                $auto_prove = '';
                $serie_prove = '';
                $ini_prove = '';
                $fin_prove = '';
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        $fec_cadu_prove = fecha_mysql_func2($oIfxA->f('coa_fec_vali'));
                        $auto_prove = $oIfxA->f('coa_aut_usua');
                        $serie_prove = $oIfxA->f('coa_seri_docu');
                        $ini_prove = $oIfxA->f('coa_fact_ini');
                        $fin_prove = $oIfxA->f('coa_fact_fin');
                    }
                }
                $oIfxA->Free();

                //correo
                $sql = "select emai_ema_emai from saeemai where
                            emai_cod_empr = $idempresa and
                            emai_cod_clpv = $clpv_cod_clpv ";
                $correo = consulta_string_func($sql, 'emai_ema_emai', $oIfxA, '');


                $fecha_compra = $aForm['fecha_pedido'];
                $fecha_final = date("Y-m-d", strtotime($fecha_compra . "+ " . $clpv_pro_pago . " days"));
                $oReturn->assign('fecha_entrega', 'value', $fecha_final);


                $sClass = ($sClass == 'off') ? $sClass = 'on' : $sClass = 'off';
                $sHtml .= '<tr height="20" style="cursor: pointer" 
                            onClick="javascript:datos_clpv( \'' . $clpv_cod_clpv . '\', \'' . $clpv_nom_clpv . '\' , \'' . $clpv_ruc_clpv . '\',  \'' . $dire . '\',
                                                            \'' . $telefono . '\',      \'' . $celular . '\',        \'' . $vendedor . '\',       \'' . $contacto . '\',
                                                            \'' . $precio . '\',        \'' . $clpv_cod_fpagop . '\', \'' . $clpv_cod_tpago . '\', \'' . $fec_cadu_prove . '\',
                                                            \'' . $auto_prove . '\',    \'' . $serie_prove . '\',     \'' . $fecha_venc . '\',     \'' . $clpv_pro_pago . '\',
                                                            \'' . $clpv_etu_clpv . '\', \'' . $ini_prove . '\',       \'' . $fin_prove . '\',      \'' . $clpv_cod_cuen . '\',
                                                            \'' . $correo . '\'
                                                          )"  >';


                $sHtml .= '<td>' . $i . '</td>';
                $sHtml .= '<td>' . $clpv_cod_clpv . '</td>';
                $sHtml .= '<td>' . $clpv_nom_clpv . '</td>';
                $sHtml .= '<td>' . $sub_cliente_sn . '</td>';
                $sHtml .= '<td>' . $vendedor_info . '</td>';
                $sHtml .= '<td>' . $clpv_ruc_clpv . '</td>';
                $sHtml .= '<td align="right">' . $clpv_etu_clpv . '</td>';
                $sHtml .= '<td>' . $estado . '</td>';
                $sHtml .= '</tr>';

                $i++;
                $total += $prbo_dis_prod;
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $sHtml .= '</tbody>';
    $sHtml .= '</table>';

    $sHtml .= '          </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
             </div>';



    $oReturn->assign("ModalClpv", "innerHTML", $sHtml);
    $oReturn->script("init()");

    return $oReturn;
}
//----------------------------------------------------------------------
//FIN FUNCION AUTOCOMPLETAR SUPLIDORES
//----------------------------------------------------------------------

function consultar($aForm = '', $op = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    //variables de sesion
    $idempresa = $_SESSION['U_EMPRESA'];

    //variables del formulario
    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    $bodega = $aForm['bodega'];
    $tran = $aForm['tran'];
    $fecha_inicio = ($aForm['fecha_inicio']);
    $fecha_fin = ($aForm['fecha_fin']);
    //---------------------------------------------------------------
    //INICIO LEO EL PROVEEDOR
    //---------------------------------------------------------------
    $cliente = $aForm['cliente'];
    //---------------------------------------------------------------
    //FIN LEO EL PROVEEDOR
    //---------------------------------------------------------------

    //---------------------------------------------------------------
    //INICIO FILTRO PROVEEDOR
    //---------------------------------------------------------------
    $filtro_clpv = "";
    if (!empty($cliente) && $cliente > 0) {
        $filtro_clpv = " AND mi.minv_cod_clpv = $cliente ";
    }
    //---------------------------------------------------------------
    //FIN FILTRO PROVEEDOR
    //---------------------------------------------------------------

    //$oReturn->alert($tran);
    

    if (count($tran) > 0) {
        foreach ($tran as $val) {
            $sqlTran .= "'" . $val . "'" . ",";
        }
    }
    $sqlTran = trim($sqlTran, ',');
    
    if (!empty($sqlTran))
    {
        $tmp = " and mi.minv_cod_tran in ($sqlTran)";
    }
    else
    {
        $tmp = "";
    }
    //echo $tmp; exit;
    //$oReturn->alert($tmp);

    $sHtml .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 90%; margin-bottom: 0px;" align="center">
				<tr>
					<td colspan="13" class="info">REPORTE MOVIMIENTO INVENTARIO</td>
				</tr>
				<tr>
					<td class="info">No.</td>
					<td class="info">FECHA</td>
					<td class="info">TRANSACCION</td>
					<td class="info">CODIGO</td>
                    <td class="info">SECUENCIAL</td>
					<td class="info">COMPROBANTE</td>
					<td class="info">FACTURA</td>
					<td class="info">CLIENTE/PROVEEDOR</td>
					<td class="info">TOTAL CANTIDAD</td>
					<td class="info">TOTAL COSTO</td>
					<td class="info">ESTADO</td>
					<td class="info">IMPRIMIR</td>
                    <td class="info">ETIQUETAS</td>
				</tr>';

    $query = "select mi.minv_fmov,     mi.minv_cod_clpv,
					 mi.minv_fac_prov, mi.minv_cod_tran, 
					( select tran_des_tran  from saetran where 
							tran_cod_tran = mi.minv_cod_tran and
							tran_cod_empr = $empresa ) as tran,
					mi.minv_num_sec, mi.minv_tot_minv, mi.minv_num_comp, minv_est_minv, minv_comp_cont
					from saeminv mi 
                    where 
					mi.minv_fmov between '$fecha_inicio'and '$fecha_fin' and
					mi.minv_cod_empr = $empresa and
					mi.minv_cod_sucu = $sucursal and
					mi.minv_est_minv <> '0' and dmov.dmov_cod_bode = $bodega
					$tmp 
					order by mi.minv_cod_tran ";
    $query = "SELECT 
                    mi.minv_fmov,     
                    mi.minv_cod_clpv,
                    mi.minv_fac_prov, 
                    mi.minv_cod_tran, 
                    ( select 
                            tran_des_tran  
                        from saetran 
                        where 
                            tran_cod_tran = mi.minv_cod_tran and
                            tran_cod_empr = $empresa 
                    ) as tran,
                    mi.minv_num_sec, 
                    mi.minv_tot_minv, 
                    mi.minv_num_comp, 
                    minv_est_minv, 
                    minv_comp_cont, (select sum(dmov_can_dmov * dmov_cun_dmov) from saedmov where  
                                    dmov_num_comp = mi.minv_num_comp and 
                                    dmov_cod_empr = mi.minv_cod_empr and 
                                    dmov_cod_sucu = mi.minv_cod_sucu and 
                                    dmov_cod_ejer = minv_cod_ejer and 
                                    dmov_num_prdo = mi.minv_num_prdo and dmov_cod_bode = $bodega) as total
                from saeminv mi 
                where
                    minv_num_comp in ( SELECT 
                                            mi.minv_num_comp
                                        from saeminv mi 
                                            left join saedmov as dmov
                                                on mi.minv_num_comp = dmov.dmov_num_comp
                                        where
                                            mi.minv_fmov between '$fecha_inicio' and '$fecha_fin' and
                                            mi.minv_cod_empr = $empresa and
                                            mi.minv_cod_sucu = $sucursal
                                            $filtro_clpv
                                            $tmp
                                            and dmov.dmov_cod_bode = $bodega

                                            
                                            
                                    )   and  minv_est_minv <> '0'                    
                        order by mi.minv_cod_tran
                        ;";
    //$oReturn->alert($query);
    $oReturn->alert('Buscando...');
    $i             = 1;
    $total         = 0;
    $totalCant     = 0;
    $totalCosto = 0;
    $granTotal     = 0;
    $granCantidadTotal     = 0;
    $dmov_cod_prod = '';
    $subtotal     = 0;
    $subtotal1     = 0;
    $subtotal2     = 0;
    if ($oIfx->Query($query)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $minv_fmov         = ($oIfx->f('minv_fmov'));
                $minv_cod_clpv  = $oIfx->f('minv_cod_clpv');
                $minv_fac_prov  = $oIfx->f('minv_fac_prov');
                $minv_cod_tran  = $oIfx->f('minv_cod_tran');
                $minv_secu      = $oIfx->f('minv_num_sec');
                //$minv_tot       = $oIfx->f('minv_tot_minv');
                $minv_tot       = $oIfx->f('total');
                $minv_cod       = $oIfx->f('minv_num_comp');
                $tran_nom       = $oIfx->f('tran');
                $minv_est_minv  = $oIfx->f('minv_est_minv');
                $minv_comp_cont  = $oIfx->f('minv_comp_cont');
                //query nombre del proveedor
                if (empty($minv_cod_clpv)) {
                    $minv_cod_clpv = 0;
                }

                $sql = "select clpv_nom_clpv from saeclpv where clpv_cod_empr = $empresa and clpv_cod_clpv = $minv_cod_clpv";
                $clpv_nom_clpv = consulta_string($sql, 'clpv_nom_clpv', $oIfxA, '');

                $sql_mov = "SELECT sum(dmov_can_dmov) as cantidad FROM saedmov WHERE dmov_num_comp = $minv_cod; ";
                $total_cantidad = number_format(consulta_string($sql_mov, 'cantidad', $oIfxA, 0), 0);

                $granCantidadTotal += $total_cantidad;

                $total = $dmov_can_dmov * $dmov_cun_dmov;

                if ($sClass == 'off')
                    $sClass = 'on';
                else
                    $sClass = 'off';

                $anio = substr($minv_fmov, 0, 4);
                $fecha_ejer     = $anio . '-12-31';
                $sql_ejer = "select ejer_cod_ejer from saeejer where ejer_fec_finl = '$fecha_ejer' and ejer_cod_empr = $empresa";
                $idejer = consulta_string($sql_ejer, 'ejer_cod_ejer', $oIfxA, 1);

                $idprdo         = (substr($minv_fmov, 5, 2)) * 1;



                $btn_eti = '<span class="btn btn-primary btn-sm" title="Imprimir Etiquetas" value="Imprimir Etiquetas" onClick="genera_etiquetas( ' . $minv_cod . ')">
                <i class="glyphicon glyphicon-print"></i>
                </span>';

                $sHtml .= '<tr height="20" class="' . $sClass . '"
                            onMouseOver="javascript:this.className=\'link\';"
                            onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                $sHtml .= '<td align="center">' . $i++ . '</td>';
                $sHtml .= '<td align="left">' . $minv_fmov . '</td>';
                $sHtml .= '<td align="left">' . $minv_cod_tran . ' | ' . $tran_nom . ' </td>';
                $sHtml .= '<td align="left">' . $minv_cod . '</td>';
                $sHtml .= '<td align="left">' . $minv_secu . '</td>';
                $sHtml .= '<td align="left"><a href="#" onclick="seleccionaItem(' . $empresa . ', ' . $sucursal . ', ' . $idejer . ', ' . $idprdo . ', \'' . $minv_comp_cont . '\');">' . $minv_comp_cont . '</a></td>';
                $sHtml .= '<td align="left">' . $minv_fac_prov . '</td>';
                $sHtml .= '<td align="left">' . $clpv_nom_clpv . '</td>';
                $sHtml .= '<td align="right">' . $total_cantidad . '</td>';
                $sHtml .= '<td align="right">' . number_format($minv_tot, 2) . '</td>';
                $sHtml .= '<td align="right">' . $minv_est_minv . '</td>';
                $sHtml .= '<td align="right">
								<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/print.png"
										style="cursor: hand !important; cursor: pointer !important;"
										onclick="javascript:vista_previa_( ' . $minv_cod . ', ' . $empresa . ',  ' . $sucursal . ' );"
										alt="Imprimir" />
								<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/print.png"
										style="cursor: hand !important; cursor: pointer !important;"
										onclick="javascript:vista_previa_totales( ' . $minv_cod . ', ' . $empresa . ',  ' . $sucursal . ' );"
										alt="Imprimir"  class="text-danger"/>
						  </td>';
                $sHtml .= '<td align="center">' . $btn_eti . '</td>';
                $sHtml .= '</tr>';

                $granTotal  += $minv_tot;
            } while ($oIfx->SiguienteRegistro());
            $sHtml .= '<tr></tr>';
            $sHtml .= '<tr></tr>';
            $sHtml .= '  <tr>
								<td align="right" colspan="7" class="font_face_2" style="color: red; font-size: 12px;">TOTAL :</td>
								<td align="right" class="font_face_2" style="color: red; font-size: 12px;">' . number_format($granCantidadTotal, 0) . '</td>
								<td align="right" class="font_face_2" style="color: red; font-size: 12px;">' . number_format($granTotal, 2) . '</td>
						   </tr>';
            $sHtml .= '</table>';
        } else {
            $sHtml = '<span>Sin Datos para mostrar...</span>';
        }
    }

    $_SESSION['reporte_excel'] = $sHtml;

    $oReturn->assign("DivReporte", "innerHTML", $sHtml);
    return $oReturn;
}

function fecha_informix($fecha)
{
    $m = substr($fecha, 5, 2);
    $y = substr($fecha, 0, 4);
    $d = substr($fecha, 8, 2);
    return ($d . '/' . $m . '/' . $y);
}



function verDiarioContable($aForm = '', $empr = 0, $sucu = 0, $ejer = 0, $mes = 0, $asto = '')
{

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx, $DSN;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //variables del formulario
    $empresa = $aForm['empresa'];
    $anio = $aForm['anio'];
    $mes_1 = $aForm['mes_1'];
    $mes_2 = $aForm['mes_2'];
    $nivel = $aForm['nivel'];
    $campo = 0;

    $class = new GeneraDetalleAsientoContable();

    $arrayAsto = $class->informacionAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayDiario = $class->diarioAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayDirectorio = $class->directorioAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayRetencion = $class->retencionAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayAdjuntos = $class->adjuntosAsientoContable($oCon, $empr, $sucu, $ejer, $mes, $asto);

    try {

        //LECTURA SUCIA1
        // 


        //sucursal
        $sql = "select sucu_nom_sucu from saesucu where sucu_cod_sucu = $sucu";
        $sucu_nom_sucu = consulta_string_func($sql, 'sucu_nom_sucu', $oIfx, '');


        $oReturn->assign("divTituloAsto", "innerHTML", $asto . ' - ' . $sucu_nom_sucu);

        if (count($arrayAsto) > 0) {

            $table .= '<table class="table table-striped table-condensed" align="center" width="98%">';
            $table .= '<tr>';
            $table .= '<td colspan="4" class="bg-primary">DIARIO CONTABLE</td>';
            $table .= '</tr>';

            foreach ($arrayAsto as $val) {
                $asto_cod_asto = $val[0];
                $asto_vat_asto = $val[1];
                $asto_ben_asto = $val[2];
                $asto_fec_asto = $val[3];
                $asto_det_asto = $val[4];
                $asto_cod_modu = $val[5];
                $asto_usu_asto = $val[6];
                $asto_user_web = $val[7];
                $asto_fec_serv = $val[8];
                $asto_cod_tidu = $val[9];

                //modulo
                $sql = "select modu_des_modu from saemodu where modu_cod_modu = $asto_cod_modu";
                $modu_des_modu = consulta_string_func($sql, 'modu_des_modu', $oIfx, '');

                //tipo documento
                $sql = "select tidu_des_tidu from saetidu where tidu_cod_tidu = '$asto_cod_tidu'";
                $tidu_des_tidu = consulta_string_func($sql, 'tidu_des_tidu', $oIfx, '');

                $table .= '<tr>';
                $table .= '<td>Diario:</td>';
                $table .= '<td>' . $asto_cod_asto . '</td>';
                $table .= '<td>Fecha:</td>';
                $table .= '<td>' . $asto_fec_asto . '</td>';
                $table .= '</tr>';

                $table .= '<tr>';
                $table .= '<td>Beneficiario:</td>';
                $table .= '<td colspan="3">' . $asto_ben_asto . '</td>';
                $table .= '</tr>';

                $table .= '<tr>';
                $table .= '<td>Modulo:</td>';
                $table .= '<td>' . $modu_des_modu . '</td>';
                $table .= '<td>Documento:</td>';
                $table .= '<td>' . $asto_cod_tidu . ' - ' . $tidu_des_tidu . '</td>';
                $table .= '</tr>';

                $table .= '<tr>';
                $table .= '<td>Detalle:</td>';
                $table .= '<td colspan="3">' . $asto_det_asto . '</td>';
                $table .= '</tr>';
                //sucursal, cod_prove, asto_cod, ejer_cod, prdo_cod
                $table .= '<tr>';
                $table .= '<td>Formato:</td>';
                $table .= '<td align="left">
							<div class="btn btn-primary btn-sm" onclick="vista_previa_diario(' . $empresa . ',' . $sucu . ', 0, \'' . $asto . '\', ' . $ejer . ', ' . $mes . ');">
								<span class="glyphicon glyphicon-print"></span>
							</div>
						</td>';
                $table .= '<td>Valor:</td>';
                $table .= '<td class="bg-danger fecha_letra" align="left">' . number_format($asto_vat_asto, 2, '.', ',') . '</td>';
                $table .= '</tr>';
            } //fin foreach

            $table .= '</table>';

            $oReturn->assign("divInfo", "innerHTML", $table);
        }

        //directorio
        if (count($arrayDiario) > 0) {

            $tableDia .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
            $tableDia .= '<tr>';
            $tableDia .= '<td colspan="5" class="bg-primary">DIARIO</td>
						<td align="center">
							<div class="btn btn-primary btn-sm" onclick="vista_previa_diario(' . $empresa . ',' . $sucu . ', 0, \'' . $asto . '\', ' . $ejer . ', ' . $mes . ');">
								<span class="glyphicon glyphicon-print"></span>
							</div>
						</td>';
            $tableDia .= '</tr>';
            $tableDia .= '<tr>';
            $tableDia .= '<td>Cuenta Contable</td>';
            $tableDia .= '<td>Centro Costos</td>';
            $tableDia .= '<td>Centro Actividad</td>';
            $tableDia .= '<td>Documento</td>';
            $tableDia .= '<td>Debito</td>';
            $tableDia .= '<td>Credito</td>';
            $tableDia .= '</tr>';
            $totalDeb = 0;
            $totalCre = 0;
            foreach ($arrayDiario as $val) {
                $dasi_cod_cuen = $val[0];
                $dasi_cod_cact = $val[1];
                $ccos_cod_ccos = $val[2];
                $dasi_dml_dasi = $val[3];
                $dasi_cml_dasi = $val[4];
                $dasi_det_asi = $val[5];
                $dasi_num_depo = $val[6];

                //clpv
                $cuen_nom_cuen = '';
                if (!empty($dasi_cod_cuen)) {
                    $sql = "select cuen_nom_cuen from saecuen where cuen_cod_cuen = '$dasi_cod_cuen' and cuen_cod_empr = $empr";
                    $cuen_nom_cuen = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');
                }

                $ccosn_nom_ccosn = '';
                if (!empty($ccos_cod_ccos)) {
                    $sql = "select ccosn_nom_ccosn from saeccosn where ccosn_cod_ccosn = '$ccos_cod_ccos' and ccosn_cod_empr = $empr";
                    $ccosn_nom_ccosn = consulta_string_func($sql, 'ccosn_nom_ccosn', $oIfx, '');
                }

                $cact_nom_cact = '';
                if (!empty($dasi_cod_cact)) {
                    $sql = "select cact_nom_cact from saecact where cact_cod_cact = '$dasi_cod_cact' and cact_cod_empr = $empr";
                    $cact_nom_cact = consulta_string_func($sql, 'cact_nom_cact', $oIfx, '');
                }

                $tableDia .= '<tr>';
                $tableDia .= '<td>' . $dasi_cod_cuen . ' - ' . $cuen_nom_cuen . '</td>';
                $tableDia .= '<td>' . $ccos_cod_ccos . ' - ' . $ccosn_nom_ccosn . '</td>';
                $tableDia .= '<td>' . $dasi_cod_cact . ' - ' . $cact_nom_cact . '</td>';
                $tableDia .= '<td>' . $dasi_num_depo . '</td>';
                $tableDia .= '<td align="right">' . number_format($dasi_dml_dasi, 2, '.', ',') . '</td>';
                $tableDia .= '<td align="right">' . number_format($dasi_cml_dasi, 2, '.', ',') . '</td>';
                $tableDia .= '</tr>';

                $totalDeb += $dasi_dml_dasi;
                $totalCre += $dasi_cml_dasi;
            } //fin foreach
            $tableDia .= '<tr>';
            $tableDia .= '<td align="right" class="bg-danger fecha_letra" colspan="4">TOTAL:</td>';
            $tableDia .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalDeb, 2, '.', ',') . '</td>';
            $tableDia .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalCre, 2, '.', ',') . '</td>';
            $tableDia .= '</tr>';
            $tableDia .= '</table>';

            $oReturn->assign("divDiario", "innerHTML", $tableDia);
        }

        //directorio
        if (count($arrayDirectorio) > 0) {

            $tableDir .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
            $tableDir .= '<tr>';
            $tableDir .= '<td colspan="6" class="bg-primary">DIRECTORIO</td>';
            $tableDir .= '</tr>';
            $tableDir .= '<tr>';
            $tableDir .= '<td>No.</td>';
            $tableDir .= '<td>Cliente/Proveedor</td>';
            $tableDir .= '<td>Transaccion</td>';
            $tableDir .= '<td>Factura</td>';
            $tableDir .= '<td>Credito</td>';
            $tableDir .= '<td>Debito</td>';
            $tableDir .= '</tr>';
            $totalDeb = 0;
            $totalCre = 0;
            foreach ($arrayDirectorio as $val) {
                $dir_cod_dir = $val[0];
                $dir_cod_cli = $val[1];
                $tran_cod_modu = $val[2];
                $dir_cod_tran = $val[3];
                $dir_num_fact = $val[4];
                $dir_detalle = $val[5];
                $dir_fec_venc = $val[6];
                $dir_deb_ml = $val[7];
                $dir_cre_ml = $val[8];

                //clpv
                $clpv_nom_clpv = '';
                if (!empty($dir_cod_cli)) {
                    $sql = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $dir_cod_cli";
                    $clpv_nom_clpv = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');
                }

                $tableDir .= '<tr>';
                $tableDir .= '<td>' . $dir_cod_dir . '</td>';
                $tableDir .= '<td>' . $clpv_nom_clpv . '</td>';
                $tableDir .= '<td>' . $dir_cod_tran . '</td>';
                $tableDir .= '<td>' . $dir_num_fact . '</td>';
                $tableDir .= '<td align="right">' . number_format($dir_cre_ml, 2, '.', ',') . '</td>';
                $tableDir .= '<td align="right">' . number_format($dir_deb_ml, 2, '.', ',') . '</td>';
                $tableDir .= '</tr>';

                $totalCre += $dir_cre_ml;
                $totalDeb += $dir_deb_ml;
            } //fin foreach
            $tableDir .= '<tr>';
            $tableDir .= '<td align="right" class="bg-danger fecha_letra" colspan="4">TOTAL:</td>';
            $tableDir .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalCre, 2, '.', ',') . '</td>';
            $tableDir .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalDeb, 2, '.', ',') . '</td>';
            $tableDir .= '</tr>';
            $tableDir .= '</table>';

            $oReturn->assign("divDirectorio", "innerHTML", $tableDir);
        }

        //retencion
        if (count($arrayRetencion) > 0) {

            $tableRet .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
            $tableRet .= '<tr>';
            $tableRet .= '<td colspan="8" class="bg-primary">RETENCION</td>';
            $tableRet .= '</tr>';
            $tableRet .= '<tr>';
            $tableRet .= '<td>Cliente/Proveedor</td>';
            $tableRet .= '<td>Factura</td>';
            $tableRet .= '<td>Retencion</td>';
            $tableRet .= '<td>Codigo</td>';
            $tableRet .= '<td>Porcentaje</td>';
            $tableRet .= '<td>Base Imp.</td>';
            $tableRet .= '<td>Valor</td>';
            $tableRet .= '<td>Print</td>';
            $tableRet .= '</tr>';
            foreach ($arrayRetencion as $val) {
                $ret_cta_ret = $val[0];
                $ret_porc_ret = $val[1];
                $ret_bas_imp = $val[2];
                $ret_valor = $val[3];
                $ret_num_ret = $val[4];
                $ret_detalle = $val[5];
                $ret_num_fact = $val[6];
                $ret_ser_ret = $val[7];
                $ret_cod_clpv = $val[8];
                $ret_fec_ret = $val[9];

                //clpv
                $clpv_nom_clpv = '';
                if (!empty($ret_cod_clpv)) {
                    $sql = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $ret_cod_clpv";
                    $clpv_nom_clpv = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');
                }

                //fprv
                $printRet = '';
                if ($asto_cod_modu == 4 || $asto_cod_modu == 6) {

                    //fecha fprv o minv
                    if ($asto_cod_modu == 4) {
                        $sql = "select fprv_fec_emis 
								from saefprv
								where fprv_cod_clpv = $ret_cod_clpv and
								fprv_num_fact = '$ret_num_fact' and
								fprv_cod_asto = '$asto' and
								fprv_cod_ejer = $ejer and
								fprv_cod_empr = $empr and
								fprv_cod_sucu = $sucu";
                        $fechaEmis = consulta_string_func($sql, 'fprv_fec_emis', $oIfx, '');
                    } elseif ($asto_cod_modu == 6) {
                        $sql = "select minv_fmov 
								from saeminv
								where minv_cod_clpv = $ret_cod_clpv and
								minv_fac_prov = '$ret_num_fact' and
								minv_comp_cont = '$asto' and
								minv_cod_ejer = $ejer and
								minv_cod_empr = $empr and
								minv_cod_sucu = $sucu";
                        $fechaEmis = consulta_string_func($sql, 'minv_fmov', $oIfx, '');
                    }

                    $printRet = '<div class="btn btn-primary btn-sm" onclick="genera_documento(5, \'' . $campo . '\',\'' . $fprv_clav_sri . '\' ,
																				 \'' . $ret_cod_clpv . '\'  , \'' . $ret_num_fact . '\', \'' . $ejer . '\',
																				 \'' . $asto . '\',  \'' . $fechaEmis . '\', ' . $sucu . ');">
									<span class="glyphicon glyphicon-print"></span>
								</div>';
                }

                $tableRet .= '<tr>';
                $tableRet .= '<td>' . $clpv_nom_clpv . '</td>';
                $tableRet .= '<td>' . $ret_num_fact . '</td>';
                $tableRet .= '<td>' . $ret_ser_ret . ' - ' . $ret_num_ret . '</td>';
                $tableRet .= '<td>' . $ret_cta_ret . '</td>';
                $tableRet .= '<td align="right">' . $ret_porc_ret . '</td>';
                $tableRet .= '<td align="right">' . number_format($ret_bas_imp, 2, '.', ',') . '</td>';
                $tableRet .= '<td align="right">' . number_format($ret_valor, 2, '.', ',') . '</td>';
                $tableRet .= '<td align="center">' . $printRet . '</td>';
                $tableRet .= '</tr>';
            } //fin foreach

            $tableRet .= '</table>';

            $oReturn->assign("divRetencion", "innerHTML", $tableRet);
        }

        //adjuntos
        if (count($arrayAdjuntos) > 0) {

            $tableAdj .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
            $tableAdj .= '<tr>';
            $tableAdj .= '<td colspan="2" class="bg-primary">ARCHIVOS ADJUNTOS</td>';
            $tableAdj .= '</tr>';
            $tableAdj .= '<tr>';
            $tableAdj .= '<td>Titulo</td>';
            $tableAdj .= '<td>Ruta</td>';
            $tableAdj .= '</tr>';
            foreach ($arrayAdjuntos as $val) {
                $titulo = $val[0];
                $ruta = $val[1];

                $tableAdj .= '<tr>';
                $tableAdj .= '<td>' . $titulo . '</td>';
                $tableAdj .= '<td><a href="#" onclick="dowloand(\'' . $ruta . '\')">' . $ruta . '</a></td>';
                $tableAdj .= '</tr>';
            } //fin foreach

            $tableAdj .= '</table>';

            $oReturn->assign("divAdjuntos", "innerHTML", $tableAdj);
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}


function genera_pdf_doc_compras($idempresa, $idsucursal, $asto_cod, $ejer_cod, $prdo_cod)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx;

    $oIfxA = new Dbo();
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();
    unset($_SESSION['pdf']);
    $oReturn = new xajaxResponse();

    $tipo     = $aForm['documento'];
    $usuario = $_SESSION['U_NOMBRECOMPLETO'];

    $diario = generar_diarios_ingresos_pdf($idempresa, $idsucursal, $asto_cod, $ejer_cod, $prdo_cod);
    $_SESSION['pdf'] = $diario;

    $oReturn->script('generar_pdf_compras()');
    return $oReturn;
}


/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
