<?php
require("_Ajax.comun.php"); // No modificar esta linea
/*:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
// S E R V I D O R   A J A X //
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/**
Herramientas de apoyo 
 */
function genera_grid($aData = null, $aLabel = null, $sTitulo = 'Reporte', $iAncho = '400', $aAccion = null, $Totales = null, $aOrden = null)
{
    if (is_array($aData) && is_array($aLabel)) {
        $iLabel = count($aLabel);
        $iData = count($aData);
        $sClass = 'on';
        $sStyle = 'border:#999999 1px solid; padding:2px; width:' . $iAncho . '%';
        $sHtml = '';

        $sHtml .= '<form id="DataGrid">';
        $sHtml .= '<table align="center" border="0" class="table table-hover table-bordered table-striped table-condensed" style="width: 98%; margin-bottom: 0px;">';
        $sHtml .= '<tr class="warning" ><td colspan="' . $iLabel . '">Su consulta genero ' . $iData . ' registros de resultado</td></tr>';
        $sHtml .= '<tr>';
        // Genera Columnas de Grid
        for ($i = 0; $i < $iLabel; $i++) {
            $sLabel = explode('|', $aLabel[$i]);
            if ($sLabel[1] == '')
                //				$sHtml .= '<th class="diagrama" align="center">'.$sLabel[0].'</th>';
                if ($i == 13 || $i == 15 || $i == 16) {
                    $sHtml .= '<td class="info" align="center" style="display:none">' . $sLabel[0] . '</th>';
                } else {
                    $sHtml .= '<td class="info" align="center">' . $sLabel[0] . '</th>';
                }
            else {
                if ($sLabel[1] == $aOrden[0]) {
                    if ($aOrden[1] == 'ASC') {
                        $sLabel[1] .= '|DESC';
                        $sImg = '<img src="' . $_COOKIE["JIREH_IMAGENES"] . 'iconos/ico_down.png" align="absmiddle" />';
                    } else {
                        $sLabel[1] .= '|ASC';
                        $sImg = '<img src="' . $_COOKIE["JIREH_IMAGENES"] . 'iconos/ico_up.png" align="absmiddle" />';
                    }
                } else {
                    $sImg = '';
                    $sLabel[1] .= '|ASC';
                }

                $sHtml .= '<th onClick="xajax_' . $sLabel[2] . '(xajax.getFormValues(\'form1\'),\'' . $sLabel[1] . '\')" 
								style="cursor: hand !important; cursor: pointer !important;" >' . $sLabel[0] . ' ';
                $sHtml .= $sImg;
                $sHtml .= '</th>';
            }
        }
        $sHtml .= '</tr>';
        // Genera Filas de Grid

        for ($i = 0; $i < $iData; $i++) {
            if ($sClass == 'off') $sClass = 'on';
            else $sClass = 'off';

            $sHtml .= '<tr class="' . $sClass . '" 
							onMouseOver="javascript:this.className=\'link\';" 
							onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
            for ($j = 0; $j < $iLabel; $j++)
                if (is_float($aData[$i][$aLabel[$j]]))
                    $sHtml .= '<td align="right">' . number_format($aData[$i][$aLabel[$j]], 2, ',', '.') . '</td>';
                else
                    //				$sHtml .= '<td align="left">'.$aData[$i][$aLabel[$j]].'</td>';
                    if ($j == 13 || $j == 15 || $j == 16) {
                        $sHtml .= '<td align="left" style="display:none">' . $aData[$i][$aLabel[$j]] . '</td>';
                    } else {
                        $sHtml .= '<td align="left">' . $aData[$i][$aLabel[$j]] . '</td>';
                    }
            $sHtml .= '</tr>';
        }

        //Totales 
        $sHtml .= '<tr>';
        if (is_array($Totales)) {
            for ($i = 0; $i < $iLabel; $i++) {
                if ($i == 0)
                    $sHtml .= '<th class="total_reporte">Totales</th>';
                else {
                    if ($Totales[$i] == '')
                        if ($Totales[$i] == '0.00')
                            $sHtml .= '<th align="right" class="total_reporte">' . number_format($Totales[$i], 2, ',', '.') . '</th>';
                        else
                            $sHtml .= '<th align="right"></th>';
                    else
                        $sHtml .= '<th align="right" class="total_reporte">' . number_format($Totales[$i], 2, ',', '.') . '</th>';
                }
            }
        }

        $sHtml .= '</tr></table>';
        $sHtml .= '</form>';
    }
    return $sHtml;
}

/*******************************************************************/
/* DF01 :: G E N E R A    F O R M U L A R I O    P E D I D O       */
/*******************************************************************/
function genera_formulario_pedido($sAccion = 'nuevo', $aForm = '')
{
    //Definiciones
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

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    $idempresa  =  $aForm['empresa'];
    $idsucursal =  $aForm['sucursal'];
    $usuario_informix =  $_SESSION['U_USER_INFORMIX'];
    unset($_SESSION['U_OTROS']);
    $idperfil   = $_SESSION['U_PERFIL'];


    // D E T A L L E     D E S C R I P C I O N
    $aDataGrid = $_SESSION['aDataGird'];




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
            $sql_adicional_sucu = ' and sucu_cod_sucu in (' . $sucursales_usuario . ')';
        }
    }
    // ---------------------------------------------------------------------------------------------------------
    // FIN CONTROL CLPV POR USUARIO, SUCURSALES
    // ---------------------------------------------------------------------------------------------------------



    if ($idperfil == 4 || $idperfil == 8) {
        // CAJERO - ADMINISTRADOR PUNTO VENTA
        $sucursal      =  $_SESSION['U_SUCURSAL'];
        $sql = "select sucu_cod_sucu, sucu_nom_sucu from saesucu where sucu_cod_empr = $idempresa and sucu_cod_sucu = $sucursal $sql_adicional_sucu";
    } else {
        $sql = "select sucu_cod_sucu, sucu_nom_sucu from saesucu where sucu_cod_empr = $idempresa $sql_adicional_sucu";
    }


    switch ($sAccion) {
        case 'nuevo':
            // SECUENCIAL DEL PEDIDO DE LA SEAPARA						
            //Consulta de Clientes
            $idempresa       =  $_SESSION['U_EMPRESA'];
            $idsucursal      =  $_SESSION['U_SUCURSAL'];

            $ifu->AgregarCampoListaSQL('empresa', 'Empresa|left', "select empr_cod_empr , empr_nom_empr from saeempr where empr_cod_empr = $idempresa", false, '240', '', true);
            $ifu->AgregarComandoAlCambiarValor('empresa', 'cargar_sucursal();');
            $ifu->AgregarCampoListaSQL('sucursal', 'Sucursal|left', "select sucu_cod_sucu, sucu_nom_sucu from saesucu where sucu_cod_empr = $idempresa", false, '240', '', true);
            $ifu->AgregarComandoAlCambiarValor('sucursal', 'cargar_transaccion();');
            $ifu->AgregarCampoTexto('ruc', 'Ruc|left', false, '', 120, 120, true);
            $ifu->AgregarCampoTexto('cliente_nombre', 'Cliente - Proveedor|left', false, '', 850, 800, true);
            $ifu->AgregarComandoAlEscribir('cliente_nombre', 'autocompletar(' . $idempresa . ', event )');
            $lista_cliente = '<select class= "CampoFormulario" name="select" size="5" id="select" style="width: auto;display:none" onclick="envio_autocompletar();">
                                          </select>';
            $ifu->AgregarCampoTexto('cliente', 'Proveedor|left', false, '', 250, 250);
            $ifu->AgregarComandoAlPonerEnfoque('cliente', 'this.blur()');
            $ifu->AgregarComandoAlCambiarValor('cliente', 'cargar_datos()');
            $ifu->AgregarCampoTexto('cuenta_prove', 'Cuenta Prove|left', false, '', 50, 50);
            $ifu->AgregarCampoTexto('dir_prove', 'Direccion Prove|left', false, '', 250, 150);
            $ifu->AgregarCampoTexto('tel_prove', 'Telefono Prove|left', false, '', 250, 150);

            $ifu->AgregarCampoTexto('nota_compra', 'No- SECU|right', false, '', 200, 200, true);
            $ifu->AgregarComandoAlPonerEnfoque('nota_compra', 'this.blur()');

            $ifu->AgregarCampoFecha('fecha_pedido', 'Fecha Movimiento|left', true, date('Y') . '/' . date('m') . '/' . date('d'), '', '', true);
            $ifu->AgregarCampoFecha('fecha_entrega', 'Fecha Entrega|left', true, date('Y') . '/' . date('m') . '/' . date('d'));


            $ifu->AgregarCampoLista('tran', 'Tipo|left', true, 'auto', '', true);
            $sql = "select t.tran_cod_tran, t.tran_des_tran  from saetran t, saedefi d  where
                                    t.tran_cod_tran = d.defi_cod_tran and
                                    t.tran_cod_empr = $idempresa and
                                    t.tran_cod_sucu = $idsucursal and
                                    t.tran_cod_modu = 10 and
                                    d.defi_cod_empr = $idempresa and
                                    d.defi_tip_defi = '1' and
                                    d.defi_cod_modu = 10 order by 2";
            $ifu->AgregarCampoLista('tran', 'Tipo|left', false, '240', '', true);
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                    do {
                        $ifu->AgregarOpcionCampoLista('tran', $oIfx->f('tran_cod_tran') . ' - ' . $oIfx->f('tran_des_tran'), $oIfx->f('tran_cod_tran'));
                    } while ($oIfx->SiguienteRegistro());
                }
            }
            $oIfx->Free();
            $ifu->AgregarComandoAlCambiarValor('tran', 'control_tran();');


            $ifu->AgregarCampoListaSQL('moneda', 'Moneda|left', "select  mone_cod_mone , mone_des_mone  from saemone where
                                                                                mone_cod_empr = $idempresa ", true, '100', '', true);

            $sql      = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa ";
            $mone_cod = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');
            $ifu->cCampos["moneda"]->xValor = $mone_cod;

            $ifu->AgregarCampoTexto('factura', 'Fact/Guia|left', false, '', 180, 100);
            $ifu->AgregarCampoTexto('observaciones', 'Observaciones|left', false, '', 700, 1000, true);
            $ifu->AgregarCampoNumerico('plazo', 'No Plazo|left', false, '', 35, 50);
            $ifu->AgregarCampoTexto('contri_prove', 'Contribuyente Especial|left', true, '', 50, 100);

            // AUTORIZACION DEL PROVEEDOR
            $ifu->AgregarCampoTexto('auto_prove', 'No Autorizacion|left', false, '', 250, 100);
            $ifu->AgregarCampoTexto('serie_prove', 'Serie|left', false, '', 50, 100);
            $ifu->AgregarCampoTexto('fecha_validez', 'Fecha Validez|left', true, date('Y') . '/' . date('m') . '/' . date('d'), 70, 100);
            $ifu->AgregarCampoListaSQL('tipo_pago', 'Tipo Pago|left', "", false, '240');
            $ifu->AgregarCampoListaSQL('forma_pago1', 'Forma de Pago|left', "", false, '120');

            //
            // PRODUCTO
            $ifu->AgregarCampoTexto('producto', 'Producto|LEFT', false, '', 250, 200, true);
            $ifu->AgregarComandoAlEscribir('producto', 'autocompletar_producto(' . $idempresa . ', event, 1 )');
            $ifu->AgregarCampoTexto('codigo_producto', 'Cod. Prod - Barra|left', false, '', 120, 100, true);
            $ifu->AgregarComandoAlEscribir('codigo_producto', 'autocompletar_producto(' . $idempresa . ', event, 2)');
            $ifu->AgregarCampoNumerico('cantidad', 'Cantidad|LEFT', true, 1, 50, 40, true);
            $ifu->AgregarCampoNumerico('costo', 'Costo|LEFT', true, 0, 50, 40, true);
            $ifu->AgregarComandoAlPonerEnfoque('costo', 'this.blur()');
            $ifu->AgregarCampoNumerico('iva', 'Impuesto|LEFT', true, 0, 50, 40, true);
            $ifu->AgregarCampoListaSQL('bodega', 'Bodega|left', "select  b.bode_cod_bode, b.bode_nom_bode from saebode b, saesubo s where
                                                                                b.bode_cod_bode = s.subo_cod_bode and
                                                                                b.bode_cod_empr = $idempresa and
                                                                                s.subo_cod_empr = $idempresa and
                                                                                s.subo_cod_sucu = $idsucursal ", false, '240', '', true);
            $ifu->AgregarCampoTexto('cuenta_inv', 'Cuenta|LEFT', false, '', 100, 100);
            $ifu->AgregarCampoTexto('cuenta_iva', 'Cuenta Impuesto|LEFT', false, '', 100, 100);

            $op = '';
            unset($_SESSION['aDataGird']);
            unset($_SESSION['aDataGirdRete']);
            unset($_SESSION['aDataGird_Pago']);
            $cont = count($aDataGird);
            if ($cont > 0) {
                $sHtml2 = mostrar_grid(0);
            } else {
                $sHtml2 = "";
            }

            $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml2);
            $oReturn->assign("divFormularioDetalle_FP", "innerHTML", $sHtml2);
            $oReturn->assign("divFormularioDetalleRET", "innerHTML", $sHtml2);
            $oReturn->assign("divTotal", "innerHTML", "");

            // control
            $fu->AgregarCampoOculto('ctrl', 'Control');
            $fu->cCampos["ctrl"]->xValor = 1;
            $ifu->cCampos["sucursal"]->xValor = $idsucursal;

            // CENTRO DE COSTO -  CUENTA CONTABLE GAST0
            $ifu->AgregarCampoTexto('cuenta_gasto', 'Cuenta|LEFT', false, '', 100, 100);
            $ifu->AgregarCampoTexto('centro_costo', 'Centro Costo|LEFT', false, '', 100, 200);

            $ifu->AgregarCampoTexto('detalle', 'Detalle|LEFT', false, '', 150, 200, true);
            $ifu->AgregarCampoTexto('serial', 'Serial|left', false, '', 80, 80, true);
            $ifu->AgregarComandoAlPonerEnfoque('serial', 'this.blur()');

            $ifu->cCampos["empresa"]->xValor  = $idempresa;
            $ifu->cCampos["sucursal"]->xValor = $idsucursal;

            // LOTE
            $ifu->AgregarCampoTexto('loteProd', 'Lote/Serie|left', "", false, 120, 100, true);
            $ifu->AgregarComandoAlEscribir('loteProd', 'validaTeclaLote(event);');
            $ifu->AgregarCampoTexto('empr_nom_empr', 'Nombre|left', true, '', 550, 200);

            $ifu->AgregarCampoFecha('fCadLoteProd', 'Fecha Caduca|left', true, '', '', '', true);
            $ifu->AgregarCampoFecha('fElaLoteProd', 'Fecha Ela|left', true, '', '', '', true);
            $ifu->AgregarCampoNumerico('stock', 'Stock|LEFT', true, 0, 50, 40, true);
            $ifu->AgregarComandoAlPonerEnfoque('stock', 'this.blur()');

            // SERIE
            $ifu->AgregarCampoTexto('serieProd', 'Lote/Serie|left', "", false, 120, 100, true);
            $ifu->AgregarComandoAlEscribir('serieProd', 'validaTeclaLote(event);');

            // CENTRO COSTOS 
            $ifu->AgregarCampoListaSQL('ccosn', 'Centro Costo|left', "select ccosn_cod_ccosn, ccosn_nom_ccosn || ' - ' || ccosn_cod_ccosn from saeccosn where
																						ccosn_cod_empr  = $idempresa and
																						ccosn_mov_ccosn = 1
																						order by 1 ", false, '500', '500', true);

            break;
        case 'sucursal':
            // SECUENCIAL DEL PEDIDO DE LA SEAPARA						
            //Consulta de Clientes
            $ifu->AgregarCampoListaSQL('empresa', 'Empresa|left', "select empr_cod_empr , empr_nom_empr from saeempr where empr_cod_empr = $idempresa", false, '240', '', true);
            $ifu->AgregarComandoAlCambiarValor('empresa', 'cargar_sucursal();');
            $ifu->AgregarCampoListaSQL('sucursal', 'Sucursal|left', $sql, true, 'auto', '', true);
            $ifu->AgregarComandoAlCambiarValor('sucursal', 'cargar_transaccion();');
            $ifu->AgregarCampoTexto('ruc', 'Ruc|left', false, '', 120, 120, true);
            $ifu->AgregarCampoTexto('cliente_nombre', 'Proveedor|left', false, '', 250, 200, true);
            $ifu->AgregarComandoAlEscribir('cliente_nombre', 'autocompletar(' . $idempresa . ', event )');
            $lista_cliente = '<select class= "CampoFormulario" name="select" size="5" id="select" style="width: auto;display:none" onclick="envio_autocompletar();">
                                          </select>';
            $ifu->AgregarCampoTexto('cliente', 'Proveedor|left', false, '', 50, 50);
            $ifu->AgregarComandoAlPonerEnfoque('cliente', 'this.blur()');
            $ifu->AgregarComandoAlCambiarValor('cliente', 'cargar_datos()');
            $ifu->AgregarCampoTexto('cuenta_prove', 'Cuenta Prove|left', false, '', 50, 50);
            $ifu->AgregarCampoTexto('dir_prove', 'Direccion Prove|left', false, '', 250, 150);
            $ifu->AgregarCampoTexto('tel_prove', 'Telefono Prove|left', false, '', 250, 150);

            $ifu->AgregarCampoTexto('nota_compra', 'No- SECU|right', false, '', 200, 200, true);
            $ifu->AgregarComandoAlPonerEnfoque('nota_compra', 'this.blur()');

            $ifu->AgregarCampoFecha('fecha_pedido', 'Fecha Movimiento|left', true, date('Y') . '/' . date('m') . '/' . date('d'));
            $ifu->AgregarCampoFecha('fecha_entrega', 'Fecha Entrega|left', true, date('Y') . '/' . date('m') . '/' . date('d'));
            $ifu->AgregarCampoLista('tran', 'Tipo|left', true, 'auto', '', true);
            $ifu->AgregarComandoAlCambiarValor('tran', 'control_tran();');
            $ifu->AgregarCampoListaSQL('moneda', 'Moneda|left', "select  mone_cod_mone , mone_des_mone  from saemone where
                                                                                mone_cod_empr = $idempresa ", true, '150', '', true);

            $sql      = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa ";
            $mone_cod = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');
            $ifu->cCampos["moneda"]->xValor = $mone_cod;

            $ifu->AgregarCampoTexto('factura', 'Fact/Guia|left', false, '', 180, 100);
            $ifu->AgregarComandoAlEscribir('factura', 'autocompletar_factura(' . $idempresa . ', event )');
            $ifu->AgregarCampoTexto('observaciones', 'Observaciones|left', false, '', 700, 1000, true);
            $ifu->AgregarCampoNumerico('plazo', 'No Plazo|left', false, '', 35, 50);
            $ifu->AgregarCampoTexto('contri_prove', 'Contribuyente Especial|left', true, '', 50, 100);

            // AUTORIZACION DEL PROVEEDOR
            $ifu->AgregarCampoTexto('auto_prove', 'No Autorizacion|left', false, '', 250, 100);
            $ifu->AgregarCampoTexto('serie_prove', 'Serie|left', false, '', 50, 100);
            $ifu->AgregarComandoAlEscribir('serie_prove', 'auto_proveedor(' . $idempresa . ', event)');
            $ifu->AgregarCampoTexto('fecha_validez', 'Fecha Validez|left', true, date('Y') . '/' . date('m') . '/' . date('d'), 70, 100);
            $ifu->AgregarCampoListaSQL('tipo_pago', 'Tipo Pago|left', "select tpago_cod_tpago,
                                                                                        (saetpago.tpago_cod_tpago||' '||saetpago.tpago_des_tpago) as tipo_pago
                                                                                        from saetpago where
                                                                                        tpago_cod_empr = $idempresa ", false, '240');

            $ifu->AgregarCampoListaSQL('forma_pago1', 'Forma de Pago|left', "SELECT saefpagop.fpagop_cod_fpagop,
                                                                                             (saefpagop.fpagop_cod_fpagop||' '||saefpagop.fpagop_des_fpagop) as fpagop
                                                                                             FROM saefpagop   where
                                                                                             fpagop_cod_empr = $idempresa ", false, '120');

            //
            // PRODUCTO
            $ifu->AgregarCampoTexto('producto', 'Producto|LEFT', false, '', 250, 200, true);
            $ifu->AgregarComandoAlEscribir('producto', 'autocompletar_producto(' . $idempresa . ', event, 1 )');
            $ifu->AgregarCampoTexto('codigo_producto', 'Cod. Prod|left', false, '', 120, 100, true);
            $ifu->AgregarComandoAlEscribir('codigo_producto', 'autocompletar_producto(' . $idempresa . ', event, 2)');
            $ifu->AgregarCampoNumerico('cantidad', 'Cantidad|LEFT', true, 1, 50, 40, true);
            $ifu->AgregarCampoNumerico('costo', 'Costo|LEFT', true, 0, 50, 40, true);
            $ifu->AgregarComandoAlPonerEnfoque('costo', 'this.blur()');
            $ifu->AgregarCampoNumerico('iva', 'Impuesto|LEFT', true, 0, 50, 40, true);
            $ifu->AgregarCampoListaSQL('bodega', 'Bodega|left', "", false, '240', '', true);
            $ifu->AgregarCampoTexto('cuenta_inv', 'Cuenta|LEFT', false, '', 100, 100);
            $ifu->AgregarCampoTexto('cuenta_iva', 'Cuenta Impuesto|LEFT', false, '', 100, 100);

            $op = '';
            unset($_SESSION['aDataGird']);
            unset($_SESSION['aDataGirdRete']);
            unset($_SESSION['aDataGird_Pago']);
            $cont = count($aDataGird);
            if ($cont > 0) {
                $sHtml2 = mostrar_grid(0);
            } else {
                $sHtml2 = "";
            }

            $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml2);
            $oReturn->assign("divFormularioDetalle_FP", "innerHTML", $sHtml2);
            $oReturn->assign("divFormularioDetalleRET", "innerHTML", $sHtml2);
            $oReturn->assign("divTotal", "innerHTML", "");

            // control
            $fu->AgregarCampoOculto('ctrl', 'Control');
            $fu->cCampos["ctrl"]->xValor = 1;
            $ifu->cCampos["sucursal"]->xValor = $idsucursal;
            $ifu->cCampos["empresa"]->xValor = $idempresa;

            // OTROS
            $sql = "select  rcgo_cod_rcgo, rcgo_des_rcgo, rcgo_cta_debi ,
                                    ( select  cuen_nom_cuen  from saecuen where
                                            cuen_cod_empr = $idempresa and
                                            cuen_cod_cuen = rcgo_cta_debi ) as cuenta
                                    from saercgo where
                                    rcgo_cod_empr = $idempresa ";
            unset($array_otros);
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                    do {
                        $array_otros[] = array($oIfx->f('rcgo_cod_rcgo'), $oIfx->f('rcgo_des_rcgo'), $oIfx->f('rcgo_cta_debi'), $oIfx->f('cuenta'));
                    } while ($oIfx->SiguienteRegistro());
                }
            }
            $oIfx->Free();

            $_SESSION['U_OTROS'] = $array_otros;

            // CENTRO DE COSTO -  CUENTA CONTABLE GAST0
            $ifu->AgregarCampoTexto('cuenta_gasto', 'Cuenta|LEFT', false, '', 100, 100);
            $ifu->AgregarCampoTexto('centro_costo', 'Centro Costo|LEFT', false, '', 100, 100);
            $ifu->AgregarComandoAlEscribir('centro_costo', 'auto_ccosn(' . $idempresa . ', event, 1 );');

            $ifu->AgregarCampoTexto('detalle', 'Detalle|LEFT', false, '', 150, 200, true);
            $ifu->AgregarCampoTexto('serial', 'Serial|left', false, '', 80, 80, true);
            $ifu->AgregarComandoAlPonerEnfoque('serial', 'this.blur()');

            // LOTE
            $ifu->AgregarCampoTexto('loteProd', 'Lote/Serie|left', "", false, 120, 100, true);
            $ifu->AgregarComandoAlEscribir('loteProd', 'validaTeclaLote(event);');

            $ifu->AgregarCampoFecha('fCadLoteProd', 'Fecha Caduca|left', true, '', '', '', true);
            $ifu->AgregarCampoFecha('fElaLoteProd', 'Fecha Ela|left', true, '', '', '', true);
            $ifu->AgregarCampoNumerico('stock', 'Stock|LEFT', true, 0, 50, 40, true);
            $ifu->AgregarComandoAlPonerEnfoque('stock', 'this.blur()');

            // SERIE
            $ifu->AgregarCampoTexto('serieProd', 'Lote/Serie|left', "", false, 120, 100, true);
            $ifu->AgregarComandoAlEscribir('serieProd', 'validaTeclaLote(event);');

            // CENTRO COSTOS 
            $ifu->AgregarCampoListaSQL('ccosn', 'Centro Costo|left', "select ccosn_cod_ccosn, ccosn_nom_ccosn || ' - ' || ccosn_cod_ccosn from saeccosn where
																						ccosn_cod_empr  = $idempresa and
																						ccosn_mov_ccosn = 1
																						order by 1 ", false, '240', 150, true);


            break;
        case 'tran':
            // SECUENCIAL DEL PEDIDO DE LA SEAPARA						
            //Consulta de Clientes
            $ifu->AgregarCampoListaSQL('empresa', 'Empresa|left', "select empr_cod_empr , empr_nom_empr from saeempr where empr_cod_empr = $idempresa", false, '240', '', true);
            $ifu->AgregarComandoAlCambiarValor('empresa', 'cargar_sucursal();');
            $ifu->AgregarCampoListaSQL('sucursal', 'Sucursal|left', $sql, true, 'auto', '', true);
            $ifu->AgregarComandoAlCambiarValor('sucursal', 'cargar_transaccion();');
            $ifu->AgregarCampoTexto('ruc', 'Ruc|left', false, '', 120, 120, true);
            $ifu->AgregarCampoTexto('cliente_nombre', 'Proveedor|left', false, '', 250, 200, true);
            $ifu->AgregarComandoAlEscribir('cliente_nombre', 'autocompletar(' . $idempresa . ', event )');
            $lista_cliente = '<select class= "CampoFormulario" name="select" size="5" id="select" style="width: auto;display:none" onclick="envio_autocompletar();">
                                          </select>';
            $ifu->AgregarCampoTexto('cliente', 'Proveedor|left', false, '', 50, 50);
            $ifu->AgregarComandoAlPonerEnfoque('cliente', 'this.blur()');
            $ifu->AgregarComandoAlCambiarValor('cliente', 'cargar_datos()');
            $ifu->AgregarCampoTexto('cuenta_prove', 'Cuenta Prove|left', false, '', 50, 50);
            $ifu->AgregarCampoTexto('dir_prove', 'Direccion Prove|left', false, '', 250, 150);
            $ifu->AgregarCampoTexto('tel_prove', 'Telefono Prove|left', false, '', 250, 150);

            $ifu->AgregarCampoTexto('nota_compra', 'No- SECU|right', false, '', 200, 200, true);
            $ifu->AgregarComandoAlPonerEnfoque('nota_compra', 'this.blur()');

            $ifu->AgregarCampoFecha('fecha_pedido', 'Fecha Movimiento|left', true, date('Y') . '/' . date('m') . '/' . date('d'));
            $ifu->AgregarCampoFecha('fecha_entrega', 'Fecha Entrega|left', true, date('Y') . '/' . date('m') . '/' . date('d'));

            $sql = "select t.tran_cod_tran, t.tran_des_tran  from saetran t, saedefi d  where
                                    t.tran_cod_tran = d.defi_cod_tran and
                                    t.tran_cod_empr = $idempresa and
                                    t.tran_cod_sucu = $idsucursal and
                                    t.tran_cod_modu = 10 and
                                    d.defi_cod_empr = $idempresa and
                                    d.defi_tip_defi = '1' and
                                    d.defi_cod_modu = 10 order by 2";
            $ifu->AgregarCampoLista('tran', 'Tipo|left', false, '240', '', true);
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                    do {
                        $ifu->AgregarOpcionCampoLista('tran', $oIfx->f('crtr_cod_crtr') . ' ' . $oIfx->f('tran_des_tran'), $oIfx->f('tran_cod_tran'));
                    } while ($oIfx->SiguienteRegistro());
                }
            }
            $oIfx->Free();

            $ifu->AgregarComandoAlCambiarValor('tran', 'control_tran();');

            $ifu->AgregarCampoListaSQL('moneda', 'Moneda|left', "select  mone_cod_mone , mone_des_mone  from saemone where
                                                                                mone_cod_empr = $idempresa ", true, '150', '', true);
            $sql      = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa ";
            $mone_cod = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');
            $ifu->cCampos["moneda"]->xValor = $mone_cod;


            $ifu->AgregarCampoTexto('factura', 'Fact/Guia|left', false, '', 180, 100);
            $ifu->AgregarComandoAlEscribir('factura', 'autocompletar_factura(' . $idempresa . ', event )');
            $ifu->AgregarCampoTexto('observaciones', 'Observaciones|left', false, '', 700, 1000, true);
            $ifu->AgregarCampoNumerico('plazo', 'No Plazo|left', false, '', 35, 50);
            $ifu->AgregarCampoTexto('contri_prove', 'Contribuyente Especial|left', true, '', 50, 100);

            // AUTORIZACION DEL PROVEEDOR
            $ifu->AgregarCampoTexto('auto_prove', 'No Autorizacion|left', false, '', 250, 100);
            $ifu->AgregarCampoTexto('serie_prove', 'Serie|left', false, '', 50, 100);
            $ifu->AgregarComandoAlEscribir('serie_prove', 'auto_proveedor(' . $idempresa . ', event)');
            $ifu->AgregarCampoTexto('fecha_validez', 'Fecha Validez|left', true, date('Y') . '/' . date('m') . '/' . date('d'), 70, 100);
            $ifu->AgregarCampoListaSQL('tipo_pago', 'Tipo Pago|left', "select tpago_cod_tpago,
                                                                                        (saetpago.tpago_cod_tpago||' '||saetpago.tpago_des_tpago) as tipo_pago
                                                                                        from saetpago where
                                                                                        tpago_cod_empr = $idempresa ", false, '240');

            $ifu->AgregarCampoListaSQL('forma_pago1', 'Forma de Pago|left', "SELECT saefpagop.fpagop_cod_fpagop,
                                                                                             (saefpagop.fpagop_cod_fpagop||' '||saefpagop.fpagop_des_fpagop) as fpagop
                                                                                             FROM saefpagop   where
                                                                                             fpagop_cod_empr = $idempresa ", false, '120');

            //
            // PRODUCTO
            $ifu->AgregarCampoTexto('producto', 'Producto|LEFT', false, '', 250, 200, true);
            $ifu->AgregarComandoAlEscribir('producto', 'autocompletar_producto(' . $idempresa . ', event, 1 )');
            $ifu->AgregarCampoTexto('codigo_producto', 'Cod. Prod|left', false, '', 120, 100, true);
            $ifu->AgregarComandoAlEscribir('codigo_producto', 'autocompletar_producto(' . $idempresa . ', event, 2)');
            $ifu->AgregarCampoNumerico('cantidad', 'Cantidad|LEFT', true, 1, 50, 40, true);
            $ifu->AgregarCampoNumerico('costo', 'Costo|LEFT', true, 0, 50, 40, true);
            $ifu->AgregarComandoAlPonerEnfoque('costo', 'this.blur()');
            $ifu->AgregarCampoNumerico('iva', 'Impuesto|LEFT', true, 0, 50, 40, true);
            $ifu->AgregarCampoListaSQL('bodega', 'Bodega|left', "select  b.bode_cod_bode, b.bode_nom_bode from saebode b, saesubo s where
                                                                                b.bode_cod_bode = s.subo_cod_bode and
                                                                                b.bode_cod_empr = $idempresa and
                                                                                s.subo_cod_empr = $idempresa and
                                                                                s.subo_cod_sucu = $idsucursal ", false, '240', '', true);
            $ifu->AgregarCampoTexto('cuenta_inv', 'Cuenta|LEFT', false, '', 100, 100);
            $ifu->AgregarCampoTexto('cuenta_iva', 'Cuenta Impuesto|LEFT', false, '', 100, 100);



            $op = '';
            unset($_SESSION['aDataGird']);
            unset($_SESSION['aDataGirdRete']);
            unset($_SESSION['aDataGird_Pago']);
            $cont = count($aDataGird);
            if ($cont > 0) {
                $sHtml2 = mostrar_grid(0);
            } else {
                $sHtml2 = "";
            }

            $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml2);
            $oReturn->assign("divFormularioDetalle_FP", "innerHTML", $sHtml2);
            $oReturn->assign("divFormularioDetalleRET", "innerHTML", $sHtml2);
            $oReturn->assign("divTotal", "innerHTML", "");

            // control
            $fu->AgregarCampoOculto('ctrl', 'Control');
            $fu->cCampos["ctrl"]->xValor = 1;
            $ifu->cCampos["empresa"]->xValor = $idempresa;
            $ifu->cCampos["sucursal"]->xValor = $idsucursal;

            // OTROS
            $sql = "select  rcgo_cod_rcgo, rcgo_des_rcgo, rcgo_cta_debi ,
                                    ( select  cuen_nom_cuen  from saecuen where
                                            cuen_cod_empr = $idempresa and
                                            cuen_cod_cuen = rcgo_cta_debi ) as cuenta
                                    from saercgo where
                                    rcgo_cod_empr = $idempresa ";
            unset($array_otros);
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                    do {
                        $array_otros[] = array($oIfx->f('rcgo_cod_rcgo'), $oIfx->f('rcgo_des_rcgo'), $oIfx->f('rcgo_cta_debi'), $oIfx->f('cuenta'));
                    } while ($oIfx->SiguienteRegistro());
                }
            }
            $oIfx->Free();

            $_SESSION['U_OTROS'] = $array_otros;

            // CENTRO DE COSTO -  CUENTA CONTABLE GAST0
            $ifu->AgregarCampoTexto('cuenta_gasto', 'Cuenta|LEFT', false, '', 100, 100);
            $ifu->AgregarCampoTexto('centro_costo', 'Centro Costo|LEFT', false, '', 100, 100);
            $ifu->AgregarComandoAlEscribir('centro_costo', 'auto_ccosn(' . $idempresa . ', event, 1 );');

            $ifu->AgregarCampoTexto('detalle', 'Detalle|LEFT', false, '', 150, 200, true);

            $ifu->AgregarCampoTexto('serial', 'Serial|left', false, '', 80, 80, true);
            $ifu->AgregarComandoAlPonerEnfoque('serial', 'this.blur()');

            // LOTE
            $ifu->AgregarCampoTexto('loteProd', 'Lote/Serie|left', "", false, 120, 100, true);
            $ifu->AgregarComandoAlEscribir('loteProd', 'validaTeclaLote(event);');

            $ifu->AgregarCampoFecha('fCadLoteProd', 'Fecha Caduca|left', true, '', '', '', true);
            $ifu->AgregarCampoFecha('fElaLoteProd', 'Fecha Ela|left', true, '', '', '', true);

            // SERIE
            $ifu->AgregarCampoTexto('serieProd', 'Lote/Serie|left', "", false, 120, 100, true);
            $ifu->AgregarComandoAlEscribir('serieProd', 'validaTeclaLote(event);');

            // STOCK
            $ifu->AgregarCampoNumerico('stock', 'Stock|LEFT', true, 0, 50, 40, true);
            $ifu->AgregarComandoAlPonerEnfoque('stock', 'this.blur()');

            // CENTRO COSTOS 
            $ifu->AgregarCampoListaSQL('ccosn', 'Centro Costo|left', "select ccosn_cod_ccosn, ccosn_nom_ccosn || ' - ' || ccosn_cod_ccosn from saeccosn where
																						ccosn_cod_empr  = $idempresa and
																						ccosn_mov_ccosn = 1
																						order by 1 ", false, '240', 150, true);

            break;
    }

    $sHtml = '';
    $sHtml .= '<table class="table table-striped table-condensed" style="width: 98%; margin-bottom: 0px;" align="center">
                    <tr>
                            <td>
								<div class="btn-group">
									<div class="btn btn-primary btn-sm" onclick="genera_formulario();">
										<span class="glyphicon glyphicon-file"></span>
										Nuevo
									</div>
									<div class="btn btn-primary btn-sm" onclick="guardar_pedido(' . $opcion_tmp . ');" >
										<span class="glyphicon glyphicon-floppy-disk"></span>
										Guardar
									</div>
									<div class="btn btn-primary btn-sm"  onclick="javascript:vista_previa();"
										<span class="glyphicon glyphicon-print"></span>
										Imprimir
									</div>
								</div>
                            </td>
                    </tr>
              </table>';

    $sHtml .= '<table class="table table-striped table-condensed" style="width: 98%; margin-bottom: 0px;" align="center" >
					<tr><td colspan="4" align="center" class="bg-primary">EGRESO INVENTARIO ONLINE</td></tr>
					<tr><td colspan="4" align="center">Los campos con * son de ingreso obligatorio</td></tr>';
    $sHtml .= '<tr>						
                        <td  colspan="4" align="center">
                            <table>
                                <tr>									
                                    <td>' . $ifu->ObjetoHtml('empr_nom_empr') . '</td>
                                    <td>' . $ifu->ObjetoHtmlLBL('nota_compra') . '</td>
									<td>' . $ifu->ObjetoHtml('serial') . '</td>
                                    <td>' . $ifu->ObjetoHtml('nota_compra') . '</td>
                                </tr>
                            </table>
                        </td>
                   </tr>';
    $sHtml .= '<tr>
    </table>';

    // LINEA
    $sql = "select linp_cod_linp, linp_des_linp  from saelinp where	linp_cod_empr = $idempresa order by 2";
    $lista_linp = lista_boostrap_func($oIfx, $sql, 0, 'linp_cod_linp', 'linp_des_linp');


    $sHtml .= '
    
    <div class="row">
        <div class="col-md-12" style="margin-top: 5px !important">
            <div class="form-row">
                <div class="col-md-3">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('empresa') . '</label>
                    ' . $ifu->ObjetoHtml('empresa') . '
                </div>
                <div class="col-md-2">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('sucursal') . '</label>
                    ' . $ifu->ObjetoHtml('sucursal') . '
                </div>
                <div class="col-md-3">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('tran') . '</label>
                    ' . $ifu->ObjetoHtml('tran') . '
                </div>
                <div class="col-md-2">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('moneda') . '</label>
                    ' . $ifu->ObjetoHtml('moneda') . '
                </div>
                <div class="col-md-2">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('fecha_pedido') . '</label>
                    <input type="date" class="form-control input-sm" id="fecha_pedido" name="fecha_pedido" value="' . date('Y-m-d') . '" aria-describedby="emailHelp" placeholder="">
                </div>

                <div class="col-md-2" style="display:none">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('factura') . '</label>
                    ' . $ifu->ObjetoHtml('factura') . '
                </div>
                <div class="col-md-2" style="display:none">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('cuenta_prove') . '</label>
                    ' . $ifu->ObjetoHtml('cuenta_prove') . '
                </div>
                <div class="col-md-2" style="display:none">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('dir_prove') . '</label>
                    ' . $ifu->ObjetoHtml('dir_prove') . '
                </div>
                <div class="col-md-2" style="display:none">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('tel_prove') . '</label>
                    ' . $ifu->ObjetoHtml('tel_prove') . '
                </div>
                <div class="col-md-2" style="display:none">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('forma_pago1') . '</label>
                    ' . $ifu->ObjetoHtml('forma_pago1') . '
                </div>
                <div class="col-md-2" style="display:none">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('tipo_pago') . '</label>
                    ' . $ifu->ObjetoHtml('tipo_pago') . '
                </div>
            </div>
        </div>
        <div class="col-md-12" style="margin-top: 5px !important">
            <div class="form-row">
                <div class="col-md-6">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('cliente_nombre') . '</label>
                    ' . $ifu->ObjetoHtml('cliente_nombre') . '
                </div>
                <div class="col-md-3">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('ruc') . '</label>
                    ' . $ifu->ObjetoHtml('ruc') . '
                </div>
                <div class="col-md-6">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('observaciones') . '</label>
                    ' . $ifu->ObjetoHtml('observaciones') . '
                </div>
                <div class="col-md-2" style="display:none">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('fecha_entrega') . '</label>
                    ' . $ifu->ObjetoHtml('fecha_entrega') . '
                </div>
                <div class="col-md-2" style="display:none">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('plazo') . '</label>
                    ' . $ifu->ObjetoHtml('plazo') . '
                </div>
                <div class="col-md-2" style="display:none">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('cliente') . '</label>
                    ' . $ifu->ObjetoHtml('cliente') . '
                </div>
                <div class="col-md-2" style="display:none">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('auto_prove') . '</label>
                    ' . $ifu->ObjetoHtml('auto_prove') . '
                </div>
                <div class="col-md-2" style="display:none">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('serie_prove') . '</label>
                    ' . $ifu->ObjetoHtml('serie_prove') . '
                </div>
                <div class="col-md-2" style="display:none">
                    <label for="empresa">' . $ifu->ObjetoHtmlLBL('fecha_validez') . '</label>
                    ' . $ifu->ObjetoHtml('fecha_validez') . '
                </div>
            </div>
        </div>   
        <div class="col-md-12" style="margin-top: 20px">
            <table class="table table-striped table-condensed" style="width: 98%; margin-bottom: 0px;" align="center" >
                <tr><td colspan="4" align="center" class="bg-primary">PRODUCTOS</td></tr>
                <tr>
                    <td colspan="4" align="center">
                        Los campos con * son de ingreso obligatorio
                            <button class="btn btn-primary" type="button" onClick="modal_cargar_archivo()">
                                <span class="glyphicon glyphicon-th-list"></span>
                                    Cargar por archivo
                            </button>
                    </td>
                </tr>
            </table>
            </div>
                <div class="col-md-12" style="margin-top: 5px !important">
                    <div class="form-row">
                        <div class="col-md-2">
                            <label for="empresa">Filtros Adicionales</label>
                            <br>
                            <input class="form-check-input" type="checkbox" value="S" id="check_filtros_adicionales" name="check_filtros_adicionales" onchange="cargar_filtros_adicionales()" >
                        </div>

                        <div class="col-md-2" id="div_select_linp" name="div_select_linp" style="display: none">
							<label for="linea">* Linea:</label>
							<select id="linea" name="linea" class="form-control input-sm" onchange="cargar_arbol();" required>
								<option value="">Seleccione una opcion..</option>
								' . $lista_linp . '
							</select>
						</div>
						<div class="col-md-2" id="div_select_grpr" name="div_select_grpr" style="display: none">
							<label for="grupo">* Grupo:</label>
							<select id="grupo" name="grupo" class="form-control input-sm" onchange="cargar_arbol();" required>
								<option value="">Seleccione una opcion..</option>
							</select>
						</div>
						<div class="col-md-2" id="div_select_cate" name="div_select_cate" style="display: none">
							<label for="cate">* Categoria:</label>
							<select id="cate" name="cate" class="form-control input-sm" onchange="cargar_arbol();" required>
								<option value="">Seleccione una opcion..</option>
							</select>
						</div>
						<div class="col-md-2" id="div_select_marc" name="div_select_marc" style="display: none">
							<label for="marca">* Marca:</label>
							<select id="marca" name="marca" class="form-control input-sm"  required>
								<option value="">Seleccione una opcion..</option>
							</select>
						</div>
                    </div>

                    <br>    
                    <br>    
                    <br>    
                    <br>    
                    <br>    
                    <hr>    

                    <div class="form-row">
                        <div class="col-md-2">
                            <label for="empresa">' . $ifu->ObjetoHtmlLBL('bodega') . '</label>
                            ' . $ifu->ObjetoHtml('bodega') . '
                        </div>
                        <div class="col-md-2">
                            <label for="empresa">' . $ifu->ObjetoHtmlLBL('producto') . '</label>
                            ' . $ifu->ObjetoHtml('producto') . '
                        </div>
                        <div class="col-md-2">
                            <label for="empresa">' . $ifu->ObjetoHtmlLBL('codigo_producto') . '</label>
                            ' . $ifu->ObjetoHtml('codigo_producto') . '
                        </div>
                        <div class="col-md-2">
                            <label for="empresa">' . $ifu->ObjetoHtmlLBL('detalle') . '</label>
                            ' . $ifu->ObjetoHtml('detalle') . '
                        </div>
                        <div class="col-md-1">
                            <label for="empresa">' . $ifu->ObjetoHtmlLBL('stock') . '</label>
                            ' . $ifu->ObjetoHtml('stock') . '
                        </div>
                        <div class="col-md-1">
                            <label for="empresa">' . $ifu->ObjetoHtmlLBL('cantidad') . '</label>
                            ' . $ifu->ObjetoHtml('cantidad') . '
                        </div>
                        <div class="col-md-1">
                            <label for="empresa"><div id="div_label_costo" name="div_label_costo">' . $ifu->ObjetoHtmlLBL('costo') . '</div></label>
                            ' . $ifu->ObjetoHtml('costo') . '
                        </div>
                        <div class="col-md-1">
                            <label for="empresa">' . $ifu->ObjetoHtmlLBL('iva') . '</label>
                            ' . $ifu->ObjetoHtml('iva') . '
                        </div>
                        <div class="col-md-2">
                            <label for="empresa">' . $ifu->ObjetoHtmlLBL('ccosn') . '</label>
                            ' . $ifu->ObjetoHtml('ccosn') . '
                        </div>
                

                        <div class="col-md-2" style="display:none">
                            <label for="empresa">' . $ifu->ObjetoHtmlLBL('cuenta_inv') . '</label>
                            ' . $ifu->ObjetoHtml('cuenta_inv') . '
                        </div>
                        <div class="col-md-2" style="display:none">
                            <label for="empresa">' . $ifu->ObjetoHtmlLBL('cuenta_iva') . '</label>
                            ' . $ifu->ObjetoHtml('cuenta_iva') . '
                        </div>

                        <div class="col-md-2" style="display:none">
                            <input type="number" id="ctrl" id="control" value="1">
                        </div>
                        <div class="col-md-2" style="display:none">
                            ' . $ifu->ObjetoHtml('contri_prove') . '
                        </div>
                        <div class="col-md-2" style="display:none">
                            ' . $ifu->ObjetoHtml('cuenta_gasto') . '
                        </div>
                        <div class="col-md-2" style="display:none">
                            ' . $ifu->ObjetoHtml('centro_costo') . '
                        </div>
                        <div class="col-md-2" style="display:none">
                            <input type="text" id="fac_ini" name="fac_ini">
                        </div>
                        <div class="col-md-2" style="display:none">
                          <input type="text" id="fac_fin" name="fac_fin">
                        </div>
                      </div>
                  </div>

                  <div class="col-md-12" style="display:none" id="f1">
                        <div class="form-row">
                            <div class="col-md-2">
                                <label for="empresa">' . $ifu->ObjetoHtmlLBL('loteProd') . '</label>
                                ' . $ifu->ObjetoHtml('loteProd') . '
                            </div>
                            <div class="col-md-2">
                                <label for="empresa">' . $ifu->ObjetoHtmlLBL('fElaLoteProd') . '</label>
                                ' . $ifu->ObjetoHtml('fElaLoteProd') . '
                            </div>
                            <div class="col-md-2">
                                <label for="empresa">' . $ifu->ObjetoHtmlLBL('fCadLoteProd') . '</label>
                                ' . $ifu->ObjetoHtml('fCadLoteProd') . '
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12" style="display:none" id="f2">
                        <div class="form-row">
                            <div class="col-md-2">
                                <label for="empresa">' . $ifu->ObjetoHtmlLBL('serieProd') . '</label>
                                ' . $ifu->ObjetoHtml('serieProd') . '
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12" style="margin-top: 5px !important">
                        <div class="form-row">
                            <div class="col-md-5">
                            </div>
                            <div class="col-md-2" style="margin-top: 30px">
                                <div class="btn btn-success btn-sm"  onclick="javascript:cargar_producto();"
                                    <span class="glyphicon glyphicon-th-list"></span>
                                    Agregar Producto
                                </div>
                            </div>
                            <div class="col-md-5">
                            </div>
                        </div>
                    </div>
                  
    </div>
    <br>
    <br>

    ';


    $oReturn->assign("divFormularioCabecera", "innerHTML", $sHtml);
    //$oReturn->assign("nota_pedido", "disabled", true);
    $oReturn->assign("divReporte", "innerHTML", "");
    $oReturn->assign("divAbono", "innerHTML", "");
    $oReturn->assign("cliente_nombre", "placeholder", "ESCRIBA EL CLIENTE. Y PRESIONE F4 ....");
    $oReturn->assign("producto", "placeholder", "ESCRIBA EL PROD. Y PRESIONE F4 ....");
    $oReturn->assign("factura", "placeholder", "ESCRIBA LA FAC. Y PRESIONE F4 ..");
    $oReturn->assign("divFormularioFp", "innerHTML", $sHtml_Fp);
    return $oReturn;
}

function cargar_arbol($aForm = '', $cod = 0)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $idempresa = $_SESSION['U_EMPRESA'];
    $linea = $aForm['linea'];
    $grupo = $aForm['grupo'];
    $cate = $aForm['cate'];
    $marca = $aForm['marca'];

    if (!empty($linea)) {
        // GRUPO
        $sql = "select grpr_cod_grpr, grpr_des_grpr from saegrpr where
                    grpr_cod_empr = $idempresa and
                    grpr_cod_linp = $linea order by 2 ";
        $i = 0;
        $msn = "...Seleccione una Opcion...";
        $txt = 'grupo';
        $oReturn->script('borrar_lista( \'' . $txt . '\' )');

        // crear elemento vacio
        $id_elemento_0 = 0;
        $oReturn->script(('anadir_elemento(' . $i . ',' . $id_elemento_0 . ', \'' . $msn . '\', \'' . $txt . '\' )'));
        $i++;
        // fin crear elemento vacio

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $id = $oIfx->f('grpr_cod_grpr');
                    $nom = $oIfx->f('grpr_des_grpr');
                    $oReturn->script(('anadir_elemento(' . $i . ',' . $id . ', \'' . $nom . '\', \'' . $txt . '\' )'));
                    $i++;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
        $oReturn->assign("linea", "value", $linea);
        $oReturn->assign("grupo", "value", $cod);
        $oReturn->assign("cate", "value", '');
        $oReturn->assign("marca", "value", '');
    } else {
        $oReturn->assign("linea", "value", '');
    }

    if (!empty($grupo)) {
        // CATEGORIA
        $sql = "select  cate_cod_cate, cate_nom_cate from saecate where
                    cate_cod_empr = $idempresa and
                    cate_cod_grpr = $grupo order by 2 ";
        $i = 0;
        $msn = "...Seleccione una Opcion...";
        $txt = 'cate';
        $oReturn->script('borrar_lista( \'' . $txt . '\' )');

        // crear elemento vacio
        $id_elemento_0 = 0;
        $oReturn->script(('anadir_elemento(' . $i . ',' . $id_elemento_0 . ', \'' . $msn . '\', \'' . $txt . '\' )'));
        $i++;
        // fin crear elemento vacio

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $id = $oIfx->f('cate_cod_cate');
                    $nom = $oIfx->f('cate_nom_cate');
                    $oReturn->script(('anadir_elemento(' . $i . ',' . $id . ', \'' . $nom . '\', \'' . $txt . '\' )'));
                    $i++;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $oReturn->assign("grupo", "value", $grupo);
        $oReturn->assign("cate", "value", '');
        $oReturn->assign("marca", "value", '');
    } else {
        $oReturn->assign("grupo", "value", '');
    }

    if (!empty($cate)) {
        // MARCA
        $sql = "select   marc_cod_marc, marc_des_marc from saemarc where
                    marc_cod_empr = $idempresa and
                    marc_cod_cate = $cate order by 2 ";
        $i = 0;
        $msn = "...Seleccione una Opcion...";
        $txt = 'marca';
        $oReturn->script('borrar_lista( \'' . $txt . '\' )');

        // crear elemento vacio
        $id_elemento_0 = 0;
        $oReturn->script(('anadir_elemento(' . $i . ',' . $id_elemento_0 . ', \'' . $msn . '\', \'' . $txt . '\' )'));
        $i++;
        // fin crear elemento vacio

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $id = $oIfx->f('marc_cod_marc');
                    $nom = $oIfx->f('marc_des_marc');
                    $oReturn->script(('anadir_elemento(' . $i . ',' . $id . ', \'' . $nom . '\', \'' . $txt . '\' )'));
                    $i++;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
        $oReturn->assign("cate", "value", $cate);
        $oReturn->assign("marca", "value", '');
    } else {
        $oReturn->assign("cate", "value", '');
    }


    return $oReturn;
}

function abrir_modal_prod_filtro($aForm = '')
{
    //Definiciones
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

    $oReturn = new xajaxResponse();

    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    $bodega = $aForm['bodega'];
    $prod_nom = $aForm['producto'];

    $tran = $aForm['tran'];
    $linea = $aForm['linea'];
    $grupo = $aForm['grupo'];
    $categoria = $aForm['cate'];
    $marca = $aForm['marca'];

    unset($_SESSION['U_PROD_FILTRO']);

    $sql_tmp = '';
    if (!empty($prod_nom)) {
        $sql_tmp = " and ( prod_nom_prod like '%$prod_nom%' or   prod_cod_prod like '%$prod_nom%' ) ";
    }


    $sql_linea = '';
    if (!empty($linea)) {
        $sql_linea = " and prod_cod_linp = $linea";
    }

    $sql_grupo = '';
    if (!empty($grupo)) {
        $sql_grupo = " and prod_cod_grpr = $grupo";
    }

    $sql_categoria = '';
    if (!empty($categoria)) {
        $sql_categoria = " and prod_cod_cate = $categoria";
    }

    $sql_marca = '';
    if (!empty($marca)) {
        $sql_marca = " and prod_cod_marc = $marca";
    }

    try {

        if (empty($tran)) {
            throw new Exception('Debe seleccionar un Tipo para continuar');
        }

        // CUENTA CONTABLE Y CENTRO DE COSTOS POR DEFECTO SAEDEFI
        $sql_cuen = "SELECT defi_cod_cuen, defi_cco_defi FROM saedefi WHERE defi_cod_empr = $empresa order by 1";
        $defi_cod_cuen = '';
        $defi_cco_defi = '';
        if ($oIfx->Query($sql_cuen)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $defi_cod_cuen = $oIfx->f('defi_cod_cuen');
                    $defi_cco_defi = $oIfx->f('defi_cco_defi');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();


        // CUENTA CONTABLE
        $array_data_cuen = array();
        $sql_cuen = "SELECT cuen_cod_cuen, concat('(', cuen_cod_cuen, ') ', cuen_nom_cuen) as cuen_nom_cuen FROM saecuen WHERE cuen_cod_empr = $empresa AND cuen_cod_sucu = $sucursal order by 1";
        if ($oIfx->Query($sql_cuen)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $cuen_cod_cuen = $oIfx->f('cuen_cod_cuen');
                    $cuen_nom_cuen = $oIfx->f('cuen_nom_cuen');
                    $array_data_cuen[$cuen_cod_cuen] = $cuen_nom_cuen;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
        // $lista_cuen = lista_boostrap_func($oIfx, $sql_cuen, '', 'cuen_cod_cuen', 'cuen_nom_cuen');

        // CENTRO DE COSTO
        $array_data_ccosn = array();
        $sql_ccosn = "SELECT ccosn_cod_ccosn, concat('(', ccosn_cod_ccosn, ') ', ccosn_nom_ccosn) as ccosn_nom_ccosn FROM saeccosn WHERE ccosn_cod_empr = $empresa order by 1";
        if ($oIfx->Query($sql_ccosn)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $ccosn_cod_ccosn = $oIfx->f('ccosn_cod_ccosn');
                    $ccosn_nom_ccosn = $oIfx->f('ccosn_nom_ccosn');
                    $array_data_ccosn[$ccosn_cod_ccosn] = $ccosn_nom_ccosn;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
        //$lista_ccosn = lista_boostrap_func($oIfx, $sql_ccosn, '', 'ccosn_cod_ccosn', 'ccosn_nom_ccosn');





        $sHtml = '';
        $sHtml .= ' <div class="row">';
        $sHtml .= ' <div class="col-md-12">';
        $sHtml .= '     <div id ="imagen1" class="btn btn-success btn-sm" onclick="procesar_informacion_filtro()">
                            <span class="glyphicon glyphicon-list"></span> Procesar Productos
                        </div>';
        $sHtml .= ' </div>';
        $sHtml .= ' <div class="col-md-12">';
        $sHtml .= ' <table id="tbclientes_prod" class="table table-striped table-condensed table-bordered table-hover" style="width: 98%; margin-top: 20px;" align="center">';
        $sHtml .= '     <thead>';
        $sHtml .= '         <tr>
                                <td class="fecha_letra">No-</td>
                                <td class="fecha_letra" align="center">Bodega</td>
                                <td class="fecha_letra" align="center">Codigo</td>
                                <td class="fecha_letra" align="center">Producto</td>
                                <td class="fecha_letra" align="center">Tipo</td>
                                <td class="fecha_letra" align="center">Unidad Medida</td>
                                <!--
                                <td class="fecha_letra" align="center">lotes</td>
                                <td class="fecha_letra" align="center">Serie</td>
                                -->
                                <td class="fecha_letra" align="center">Stock</td>                        
                                <td class="fecha_letra" align="center">Cantidad</td> 
                                <td class="fecha_letra" align="center">Cuenta Contable</td> 
                                <td class="fecha_letra" align="center">Centro Costos</td> 
                                <!--                       
                                <td class="fecha_letra" align="center">Distribuir</td>
                                -->                        
                                <td class="fecha_letra" align="center">Bajar Producto</td>                        
                            </tr>';
        $sHtml .= '     </thead>';
        $sHtml .= '     <tbody>';


        $sql = "SELECT un.unid_nom_unid, tp.tpro_des_tpro, b.bode_nom_bode, pr.prbo_cod_prod, p.prod_nom_prod, pr.prbo_cta_inv, pr.prbo_cta_ideb,
                    pr.prbo_uco_prod, pr.prbo_iva_porc, prod_lot_sino, prod_ser_prod, COALESCE( pr.prbo_dis_prod,'0' ) as stock, pr.prbo_cod_unid,
                    pr.prbo_cta_cven, pr.prbo_cco_prbo
                    from saeprbo pr, saeprod p, saebode b, saetpro tp, saeunid un
                    where
                    p.prod_cod_prod     = pr.prbo_cod_prod and
                    pr.prbo_cod_bode     = b.bode_cod_bode and
                    tp.tpro_cod_tpro     = p.prod_cod_tpro and
                    un.unid_cod_unid     = pr.prbo_cod_unid and
                    p.prod_cod_empr     = $empresa and
                    p.prod_cod_sucu     = $sucursal and
                    pr.prbo_cod_empr    = $empresa and
                    pr.prbo_cod_bode    = '$bodega' and
                    COALESCE( pr.prbo_dis_prod,'0' ) > 0
                    $sql_tmp 
                    $sql_linea
                    $sql_grupo
                    $sql_categoria
                    $sql_marca
                    order by COALESCE( pr.prbo_dis_prod,'0' ) desc, tp.tpro_des_tpro
                    ";


        // No se hace uso de la vista porque no actualiza los campos al moemnto de utilizarlos. 
        // $sql = "select *from sp_obtener_todos_productos($idempresa , $idsucursal,$bode_cod,500,'$prod_nom');";

        $i = 1;
        $total = 0;
        unset($_SESSION['U_PROD_RSC']);
        unset($array_tmp);
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {

                    $prbo_cod_prod = ($oIfx->f('prbo_cod_prod'));
                    $stock = $oIfx->f('stock');
                    $nom_bode = ($oIfx->f('bode_nom_bode'));
                    $tipo_prod = ($oIfx->f('tpro_des_tpro'));
                    $detalle_prod = ($oIfx->f('prod_det_prod'));
                    $prod_nom_prod = htmlentities($oIfx->f('prod_nom_prod'));
                    $prbo_dis_prod = $stock;
                    $prbo_cta_inv = $oIfx->f('prbo_cta_inv');
                    $prbo_cta_ideb = $oIfx->f('prbo_cta_ideb');
                    $prbo_uco_prod = $oIfx->f('prbo_uco_prod');
                    $prbo_iva_porc = $oIfx->f('prbo_iva_porc');
                    $unidad_prod = $oIfx->f('unid_nom_unid');
                    $lote = $oIfx->f('prod_lot_sino');
                    $serie = $oIfx->f('prod_ser_prod');
                    $prbo_cod_unid = $oIfx->f('prbo_cod_unid');

                    $prbo_cta_cven = $oIfx->f('prbo_cta_cven');
                    $prbo_cco_prbo = $oIfx->f('prbo_cco_prbo');


                    if (!empty($defi_cod_cuen) || !empty($defi_cco_defi)) {
                        $prbo_cta_cven = $defi_cod_cuen;
                        $prbo_cco_prbo = $defi_cco_defi;
                    }

                    if (empty($prbo_cta_cven) && empty($prbo_cco_prbo)) {
                        // CUENTA CONTABLE Y CENTRO DE COSTOS POR DEFECTO SAEBODE
                        $sql_cuen = "SELECT bode_cta_cven, bode_cco_bode FROM saebode WHERE bode_cod_bode = $bodega order by 1";
                        if ($oIfxA->Query($sql_cuen)) {
                            if ($oIfxA->NumFilas() > 0) {
                                do {
                                    $prbo_cta_cven = $oIfxA->f('bode_cta_cven');
                                    $prbo_cco_prbo = $oIfxA->f('bode_cco_bode');
                                } while ($oIfxA->SiguienteRegistro());
                            }
                        }
                        $oIfxA->Free();
                    }

                    $array_tmp[$i] = array(
                        $prbo_cod_prod,
                        $prod_nom_prod,
                        $prbo_cta_inv,
                        $prbo_cta_ideb,
                        $prbo_uco_prod,
                        $prbo_iva_porc,
                        $lote,
                        $serie,
                        $prbo_cod_unid,
                        $prbo_cta_cven,
                        $prbo_cco_prbo
                    );

                    if ($lote == 1 || $lote == 'S') {
                        $lote = 'S';
                    } else {
                        $lote = 'N';
                    }

                    if ($serie == 1 || $serie == 'S') {
                        $serie = 'S';
                    } else {
                        $serie = 'N';
                    }

                    $add_select_cuen = '';
                    foreach ($array_data_cuen as $cuen_cod_cuen_ad => $cuen_nom_cuen_ad) {
                        if ($prbo_cta_cven == $cuen_cod_cuen_ad) {
                            $add_select_cuen .= '<option value="' . $cuen_cod_cuen_ad . '" selected>' . $cuen_nom_cuen_ad . '</option>';
                        } else {
                            $add_select_cuen .= '<option value="' . $cuen_cod_cuen_ad . '">' . $cuen_nom_cuen_ad . '</option>';
                        }
                    }


                    $add_select_ccosn = '';
                    foreach ($array_data_ccosn as $ccosn_cod_ccosn_ad => $ccosn_nom_ccosn_ad) {
                        if ($prbo_cco_prbo == $ccosn_cod_ccosn_ad) {
                            $add_select_ccosn .= '<option value="' . $ccosn_cod_ccosn_ad . '" selected>' . $ccosn_nom_ccosn_ad . '</option>';
                        } else {
                            $add_select_ccosn .= '<option value="' . $ccosn_cod_ccosn_ad . '">' . $ccosn_nom_ccosn_ad . '</option>';
                        }
                    }

                    $sHtml .= '<tr>';
                    $sHtml .= '<td>' . $i . '</td>';
                    $sHtml .= '<td>' . $nom_bode . '</td>';
                    $sHtml .= '<td>' . $prbo_cod_prod . '</td>';
                    $sHtml .= '<td>' . $prod_nom_prod . '</td>';
                    $sHtml .= '<td>' . $tipo_prod . '</td>';
                    $sHtml .= '<td>' . $unidad_prod . '</td>';
                    //$sHtml .= '<td>' . $lote . '</td>';
                    //$sHtml .= '<td>' . $serie . '</td>';
                    $sHtml .= '<td align="right">' . $prbo_dis_prod . '</td>';
                    $sHtml .= '<td>
                                    <input class="form-control input-sm" type="number" name="cantidad_prod_' . $prbo_cod_prod . '" id="cantidad_prod_' . $prbo_cod_prod . '" onchange="control_stock_filtro(\'' . $prbo_cod_prod . '\', \'' . $prbo_dis_prod . '\')" />
                                </td>';

                    $sHtml .= '<td>
                                    <select id="cuenta_contable_filt_' . $prbo_cod_prod . '" name="cuenta_contable_filt_' . $prbo_cod_prod . '" class="form-control input-sm" onchange="validar_cuenta_contable(\'' . $prbo_cod_prod . '\')" required>
                                        <option value="">Seleccione una opcion..</option>
                                        ' . $add_select_cuen . '
                                    </select>
                                </td>';

                    $sHtml .= '<td>
                                    <select id="centro_costos_filt_' . $prbo_cod_prod . '" name="centro_costos_filt_' . $prbo_cod_prod . '" class="form-control input-sm"  onchange="validar_centro_costos(\'' . $prbo_cod_prod . '\')" required>
                                        <option value="">Seleccione una opcion..</option>
                                        ' . $add_select_ccosn . '
                                    </select>
                                </td>';

                    /*
                    $sHtml .= '<td>
                                    <div id ="imagen1" class="btn btn-primary btn-sm" onclick="abrir_modal_distribucion(\'' . $prbo_cod_prod . '\', \'' . $prod_nom_prod . '\')">
                                        <span class="glyphicon glyphicon-list"></span>
                                    </div>
                                </td>';
                                */
                    $sHtml .= '<td>
                                    <div id ="imagen1" class="btn btn-success btn-sm" onclick="datos( \'' . $prbo_cod_prod . '\', \'' . $prod_nom_prod . '\', \'' . $prbo_cta_inv . '\', \'' . $prbo_cta_ideb . '\', \'' . $prbo_uco_prod . '\', \'' . $prbo_dis_prod . '\', \'' . $lote . '\', \'' . $serie . '\', \'' . $prod_nom_prod . '\', 0, \'' . $prbo_dis_prod . '\', \'' . $prbo_cta_inv . '\'   )">
                                        <span class="glyphicon glyphicon-check"></span>
                                    </div>
                                </td>';
                    $sHtml .= '</tr>';

                    $i++;
                    $total += $prbo_dis_prod;
                } while ($oIfx->SiguienteRegistro());
            }
        }

        $_SESSION['U_PROD_FILTRO'] = $array_tmp;

        $sHtml .= '</tbody>';
        $sHtml .= '</table>';
        $sHtml .= '</div>';


        $modal = '<div id="mostrarModalProdFiltro" class="modal fade" role="dialog" style="z-index: 9999;">
                 <div class="modal-dialog modal-lg" style="width:1100px;">
                     <div class="modal-content">
                         <div class="modal-header">
                             <button type="button" class="close" data-dismiss="modal">&times;</button>
                             <h4 class="modal-title"><b>Productos Linea - Grupo - Categoria - Marca</b></h4>
                         </div>
                         <div class="modal-body">';
        $modal .= $sHtml;
        $modal .= '          </div>
                         <div class="modal-footer">
                             <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                         </div>
                     </div>
                 </div>
              </div>';

        $oReturn->assign("divModalProductoFiltro", "innerHTML", $modal);
        $oReturn->script("abre_modal_prod_filtro();");
        $oReturn->script("generaSelect2();");
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function procesar_informacion_filtro($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    $bodega = $aForm['bodega'];
    $prod_nom = $aForm['producto'];

    $linea = $aForm['linea'];
    $grupo = $aForm['grupo'];
    $categoria = $aForm['cate'];
    $marca = $aForm['marca'];

    $data_prod_filtro_sesion = $_SESSION['U_PROD_FILTRO'];

    unset($aDataGrid);
    $aDataGrid = $_SESSION['aDataGird_INV_MRECO'];

    $aLabelGrid = array(
        'Id',
        'Bodega',
        'Codigo Item',
        'Descripcion',
        'Unidad',
        'Cantidad',
        'Costo',
        'Iva',
        'Dscto 1',
        'Dscto 2',
        'Dscto Gral',
        'Total',
        'Total Con Iva',
        'Modificar',
        'Eliminar',
        'Cuenta',
        'Cuenta Iva',
        'Centro Costo',
        'Cuenta Gasto',
        'Detalle',
        'Lote/Serie',
        'Elaboracion',
        'Caduca'
    );


    try {

        if (is_array($data_prod_filtro_sesion)) {
            //echo "La variable1 es un array.";
            foreach ($data_prod_filtro_sesion as $key => $data_prod) {
                $codigo_producto = $data_prod[0];
                $nombre_producto = $data_prod[1];
                $prbo_cta_inv = $data_prod[2];
                $prbo_cta_ideb = $data_prod[3];
                $prbo_uco_prod = $data_prod[4];
                $prbo_iva_porc = $data_prod[5];
                $lote = $data_prod[6];
                $serie = $data_prod[7];
                $cod_unid = $data_prod[8];
                $cantidad = $aForm['cantidad_prod_' . $codigo_producto];
                $cuenta_contable = $aForm['cuenta_contable_filt_' . $codigo_producto];
                $centro_costos = $aForm['centro_costos_filt_' . $codigo_producto];
                if (empty($prbo_uco_prod)) {
                    $prbo_uco_prod = 0;
                }


                if ($cantidad > 0) {

                    $descuento_general = 0;
                    $costo = $prbo_uco_prod;
                    $descuento = 0;
                    $descuento_2 = 0;
                    $iva = $prbo_iva_porc;
                    $idunidad = $cod_unid;
                    $detalle = 'EGRESO REALIZADO DESDE FILTROS';
                    $lote_serie = '';
                    $fela = '';
                    $fecad = '';
                    // TOTAL
                    $total_fac     = 0;
                    $dsc1         = ($costo * $cantidad * $descuento) / 100;
                    $dsc2         = ((($costo * $cantidad) - $dsc1) * $descuento_2) / 100;
                    if ($descuento_general > 0) {
                        // descto general
                        $dsc3                 = ((($costo * $cantidad) - $dsc1 - $dsc2) * $descuento_general) / 100;
                        $total_fact_tmp     = ((($costo * $cantidad) - ($dsc1 + $dsc2 + $dsc3)));
                        $tmp                 = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                    } else {
                        // sin descuento general
                        $total_fact_tmp     = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                        $tmp                 = $total_fact_tmp;
                    }

                    $total_fac = round($total_fact_tmp, 2);

                    // total con iva
                    if ($iva > 0) {
                        $total_con_iva = round((($total_fac * $iva) / 100), 2) + $total_fac;
                    } else {
                        $total_con_iva = $total_fac;
                    }




                    //GUARDA LOS DATOS DEL DETALLE
                    $cont = count($aDataGrid);
                    // cantidad
                    $fu->AgregarCampoNumerico($cont . '_cantidad', 'Cantidad|LEFT', false, $cantidad, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($cont . '_cantidad', 'cargar_update_cant(\'' . $cont . '\');');

                    // costo
                    $fu->AgregarCampoNumerico($cont . '_costo', 'Costo|LEFT', false, $costo, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($cont . '_costo', 'cargar_update_cant(\'' . $cont . '\');');

                    // iva
                    $fu->AgregarCampoNumerico($cont . '_iva', 'Impuesto|LEFT', false, $iva, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($cont . '_iva', 'cargar_update_cant(\'' . $cont . '\');');

                    // descto1
                    $fu->AgregarCampoNumerico($cont . '_desc1', 'Descto1|LEFT', false, 0, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($cont . '_desc1', 'cargar_update_cant(\'' . $cont . '\');');

                    // descto2
                    $fu->AgregarCampoNumerico($cont . '_desc2', 'Descto2|LEFT', false, 0, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($cont . '_desc2', 'cargar_update_cant(\'' . $cont . '\');');

                    // cuenta de gasto
                    $cta_gasto = $aForm['cuenta_gasto'];
                    $html_cta = '';
                    $fu->AgregarCampoTexto($cont . '_cta_gasto', 'Cuenta Gasto', false, $cuenta_contable, 100, 100);
                    $fu->AgregarComandoAlEscribir($cont . '_cta_gasto', 'cta_gasto_22(\'' . $cont . '_cta_gasto' . '\', event );');
                    $html_cta = $fu->ObjetoHtml($cont . '_cta_gasto');


                    // centro de costo
                    $ccos = $aForm['centro_costo'];
                    $html_ccos = '';
                    $fu->AgregarCampoTexto($cont . '_ccos', 'Centro Costo', false, $centro_costos, 100, 100);
                    $fu->AgregarComandoAlEscribir($cont . '_ccos', 'centro_costo_22( \'' . $cont . '_ccos' . '\', event );');
                    $html_ccos = $fu->ObjetoHtml($cont . '_ccos');

                    $aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
                    $aDataGrid[$cont][$aLabelGrid[1]] = $bodega;
                    $aDataGrid[$cont][$aLabelGrid[2]] = $codigo_producto;
                    $aDataGrid[$cont][$aLabelGrid[3]] = $prod_nom;
                    $aDataGrid[$cont][$aLabelGrid[4]] = $idunidad;

                    $aDataGrid[$cont][$aLabelGrid[5]] = $fu->ObjetoHtml($cont . '_cantidad');  //$cantidad;
                    $aDataGrid[$cont][$aLabelGrid[6]] = $fu->ObjetoHtml($cont . '_costo'); //costo;
                    $aDataGrid[$cont][$aLabelGrid[7]] = $fu->ObjetoHtml($cont . '_iva'); //iva
                    $aDataGrid[$cont][$aLabelGrid[8]] = $fu->ObjetoHtml($cont . '_desc1'); // desc1
                    $aDataGrid[$cont][$aLabelGrid[9]] = $fu->ObjetoHtml($cont . '_desc2'); // dec2

                    $aDataGrid[$cont][$aLabelGrid[10]] = $descuento_general;
                    $aDataGrid[$cont][$aLabelGrid[11]] = $total_fac;
                    $aDataGrid[$cont][$aLabelGrid[12]] = $total_con_iva;
                    $aDataGrid[$cont][$aLabelGrid[13]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil3.png"
                            title = "Presione aqui para Modificar"
                            style="cursor: hand !important; cursor: pointer !important;"
                            onclick="agregar_detalle(1);"
                            alt="Modificar"
                            align="bottom" />';
                    $aDataGrid[$cont][$aLabelGrid[14]] = '
 
                        <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                            title = "Presione aqui para Eliminar"
                            style="cursor: hand !important; cursor: pointer !important;"
                            onclick="javascript:elimina_detalle(' . $id . ');"
                            alt="Eliminar"
                            align="bottom" />
                            
                        ';
                    $aDataGrid[$cont][$aLabelGrid[15]] = '';
                    $aDataGrid[$cont][$aLabelGrid[16]] = '';
                    $aDataGrid[$cont][$aLabelGrid[17]] = $html_ccos . ' <div id ="imagen1" class="btn btn-primary btn-sm" onclick="abrir_modal_distribucion(\'' . $cont . '\')">
                                                                            <span class="glyphicon glyphicon-list"></span>
                                                                        </div>
                                                                        ';
                    $aDataGrid[$cont][$aLabelGrid[18]] = $html_cta;
                    $aDataGrid[$cont][$aLabelGrid[19]] = $detalle;
                    $aDataGrid[$cont][$aLabelGrid[20]] = $lote_serie;
                    $aDataGrid[$cont][$aLabelGrid[21]] = $fela;
                    $aDataGrid[$cont][$aLabelGrid[22]] = $fecad;


                    $_SESSION['aDataGird'] = $aDataGrid;
                }
            }

            $sHtml = mostrar_grid($empresa);
            $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
            // $oReturn->script('limpiar()');
            $oReturn->script('totales();');
            $oReturn->script('cierra_modal_prod_filtro();');
        } else {
            //echo "La variable1 no es un array.";
            throw new Exception('No existen productos para procesar');
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function modal_cargar_archivo($aForm = '')
{

    //Definiciones
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

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();


    $oReturn = new xajaxResponse();

    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];


    try {

        $sHtml = '';
        $sHtml .= ' <div  class="col-md-12 text-center" style="margin-top: 50px; margin-bottom: 10px; border: 2px solid black !important; padding: 30px; border-style: dotted !important;">                                
            
                        <div class="row">
                            <div class="col-md-12" style="margin-bottom: 20px">
                                <h4><b>Cargar Archivo con Egresos</b><h4>
                            </div>
                            <div class="col-md-4">
                                <input type="file" name="archivo" id="archivo" onchange="upload_image(id);" required>
                                <div class="upload-msg"></div>
                            </div>
                            <div class="col-md-1">
                            </div>
                            <div class="col-md-2" style="text-align:center;">
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Archivo Ejemplo</label><br>
                                    <div style="text-align:left;">
                                        <a href="compra_import.txt" download="compra_import.txt" id="txt">
                                            <span class="glyphicon glyphicon-download"></span> Ejemplo Archivo
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1">
                            </div>
                            <div class="col-md-4">
                                <div class="btn btn-primary btn-sm" onclick="consultar();" style="width: 100%">
                                    <span class="glyphicon glyphicon-search"></span>
                                    Consultar
                                </div>
                            </div>
                        </div>
                    </div>';


        $modal = '<div id="mostrarmodal" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">CARGAR EGRESO POR ARCHIVO</h4>
                            </div>
                            <div class="modal-body">';
        $modal .= $sHtml;
        $modal .= ' 
                    <div class="btn btn-white btn-sm" style="width: 20%">
                    </div>
                    <div id="div_procesar" class="btn btn-primary btn-sm" onclick="processar_archivo();" style="width: 20%; display: none">
                        <span class="glyphicon glyphicon-search"></span>
                        PROCESAR
                    </div>
                    <div id="divFormularioDetalle3" class="table-responsive" style="margin-bottom: 120px;"></div>';
        $modal .= '          </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                 </div>';

        $oReturn->assign("divFormularioTotal", "innerHTML", $modal);
        $oReturn->script("abre_modal();");
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function cargar_ord_compra_respaldo($aForm = '')
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

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();


    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $aForm['sucursal'];
    $mostrar_procesar = 'S';


    unset($_SESSION['U_PROD_COD_PRECIO']);

    //////////////

    try {

        // DATOS
        // BODEGA

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
                $sql_adicional_sucu = ' and subo_cod_sucu in (' . $sucursales_usuario . ')';
            } else {
                $sql_adicional_sucu = ' and subo_cod_sucu = ' . $idsucursal;
            }
        }
        // ---------------------------------------------------------------------------------------------------------
        // FIN CONTROL CLPV POR USUARIO, SUCURSALES
        // ---------------------------------------------------------------------------------------------------------

        $sql = "select bode_cod_bode, bode_nom_bode from saesubo, saebode where
                        bode_cod_bode = subo_cod_bode and
                        bode_cod_empr = $idempresa and
                        subo_cod_empr = $idempresa
                        $sql_adicional_sucu ";
        unset($array_bode);
        unset($array_bode_cod);
        $array_bode     = array_dato($oIfx, $sql, 'bode_nom_bode', 'bode_nom_bode');
        $array_bode_cod = array_dato($oIfx, $sql, 'bode_nom_bode', 'bode_cod_bode');


        // PRODUCTO
        $sql = "select prod_cod_prod, prod_nom_prod from saeprod where
                        prod_cod_empr = $idempresa and
                        prod_cod_sucu = $idsucursal
                        group by 1,2  ";
        unset($array_prod);
        unset($array_prod_cod);
        $array_prod     = array_dato($oIfx, $sql, 'prod_cod_prod', 'prod_cod_prod');


        // CENTRO DE COSTO
        $sql = "select ccosn_cod_ccosn,  ccosn_nom_ccosn
                from saeccosn where
                ccosn_cod_empr = $idempresa and
                ccosn_mov_ccosn = 1 order by 2";

        unset($array_prec);
        unset($array_prec_cod);
        $array_prec     = array_dato($oIfx, $sql, 'ccosn_nom_ccosn', 'ccosn_nom_ccosn');
        $array_prec_cod = array_dato($oIfx, $sql, 'ccosn_cod_ccosn', 'ccosn_cod_ccosn');

        $archivo = $aForm['archivo'];

        // archivo txt
        $archivo_real = substr($archivo, 12);
        list($xxxx, $exten) = explode(".", $archivo_real);

        if ($exten == 'txt') {
            $nombre_archivo = "upload/" . $archivo_real;

            $file       = fopen($nombre_archivo, "r");
            $datos      = file($nombre_archivo);
            $NumFilas   = count($datos);

            $table_cab  = '<br><br>';
            $table_cab  = '<h4>Lista del archivo exportado</h4>';
            $table_cab .= '<table class="table table-bordered table-striped table-condensed" style="width: 98%; margin-bottom: 0px;">';
            $table_cab .= '<tr>
                                            <td class="success" style="width: 4.5%;">N.-</td>
                                            <td class="success" style="width: 4.5%;">BODEGA</td>
                                            <td class="success" style="width: 4.5%;">CODIGO PRODUCTO</td>
                                            <td class="success" style="width: 9.5%;">PRODUCTO</td>
                                            
                                            <td class="success" style="width: 4.5%;">LOTE SERIE</td>
                                            <td class="success" style="width: 4.5%;">FECHA ELA</td>
                                            <td class="success" style="width: 4.5%;">FECHA CAD</td>

                                            <td class="success" style="width: 4.5%;">CANTIDAD</td>
                                            <td class="success" style="width: 4.5%;">PRECIO UNIT.</td>
                                            <td class="success" style="width: 4.5%;">CENTRO DE COSTO</td>
                                            <td class="success" style="width: 4.5%;">DETALLE</td>
                                            ';

            $cont = 0;
            $cont_pvp = 0;

            /*BODEGA	    CODIGO	        PRODUCTO	    CANTIDAD	        CENTRO DE COSTO	        FOB
                    */
            $datos_txt = explode("	", $datos[0]);
            foreach ($datos_txt as $val1) {
                if ($cont > 9) {
                    $cont_pvp++;
                }
                $cont++;
            }

            for ($i = 1; $i <= $cont_pvp; $i++) {
                $table_cab .= '<td class="success" style="width: 4.5%;">PVP' . $i . '</td>';
            }

            $table_cab .= '</tr>';
            $x = 1;
            // $oReturn->alert('Buscando ...');
            unset($array);
            foreach ($datos as $val) {
                /*BODEGA	    CODIGO	        PRODUCTO	    CANTIDAD	        CENTRO DE COSTO	        FOB
                        */

                list(
                    $bode_cod,
                    $prod_cod,
                    $prod_nom,
                    $cantidad,
                    $ccosto,
                    $detalle,
                    $lote_serie,
                    $fob,
                    $pvp1,
                    $pvp2,
                    $pvp3,
                    $pvp4,
                    $pvp5,
                    $pvp6,
                    $pvp7,
                    $pvp8,
                    $pvp9,
                    $pvp10
                ) = explode("	", $val);

                if ($x > 1 && !empty($bode_cod)) {
                    if ($sClass == 'off') $sClass = 'on';
                    else $sClass = 'off';
                    $table_cab .= '<tr>';
                    $table_cab .= '<td>' . ($x - 1) . '</td>';
                    if (!empty($array_bode[trim($bode_cod)])) {
                        $table_cab .= '<td>' . $array_bode[trim($bode_cod)] . '</td>';
                    } else {
                        $table_cab .= '<td style="background:yellow">' . $bode_cod . '</td>';
                        $mostrar_procesar = 'N';
                    }

                    if (!empty($array_prod[trim($prod_cod)])) {
                        $table_cab .= '<td>' . $array_prod[$prod_cod] . '</td>';
                        $sql_prod_nom = "SELECT prbo_cod_prod, prod_nom_prod, prbo_dis_prod, prod_stock_neg from 
                                        saeprbo, saeprod
                                        where 
                                        prbo_cod_prod = prod_cod_prod
                                        and prbo_cod_prod = '$prod_cod' 
                                        and prbo_cod_bode = " . $array_bode_cod[trim($bode_cod)];

                        // $sql_prod_nom = "select prod_nom_prod from saeprod where prod_cod_prod = '$prod_cod'";
                        $prod_nom = consulta_string($sql_prod_nom, 'prod_nom_prod', $oIfx, '');
                        $prbo_dis_prod = consulta_string($sql_prod_nom, 'prbo_dis_prod', $oIfx, 0);
                        $prod_stock_neg = consulta_string($sql_prod_nom, 'prod_stock_neg', $oIfx, 'N');
                    } else {
                        $table_cab .= '<td style="background:yellow">' . $prod_cod . '</td>';
                        $prbo_dis_prod = 0;
                        $mostrar_procesar = 'N';
                        $prod_stock_neg = 'N';
                    }

                    if (empty($prod_nom)) {
                        $table_cab .= '<td style="background:yellow">' . $prod_cod . ' (Producto no existe)</td>';
                        $mostrar_procesar = 'N';
                    } else {
                        $table_cab .= '<td>' . $prod_nom . '</td>';
                    }






                    // ------------------------------------------------------------------------------------------------
                    // CONSULTA EXISTE LOTE
                    //-------------------------------------------------------------------------------------------------

                    if (!empty($lote_serie)) {
                        $sql_cod_bode = "SELECT bode_cod_bode from saebode where bode_nom_bode = '$bode_cod'";
                        $bode_cod_bode = consulta_string($sql_cod_bode, 'bode_cod_bode', $oIfx, '');


                        $id_user = $_SESSION['U_ID'];
                        $fecha_ini = '2018-01-01';
                        $fecha_fin = '2030-01-01';

                        $sql = "delete from tmp_prod_lote_web where user_cod_web = $id_user";
                        $oIfx->QueryT($sql);

                        if (empty($bode_cod_bode)) {
                            throw new Exception('No existe la bodega: ' . $bode_cod);
                        }

                        $sql_sp = "select * from sp_lotes_productos_web( $idempresa, $idsucursal, $bode_cod_bode, '$fecha_ini', '$fecha_fin', '$prod_cod', '$prod_cod', '2' , $id_user, '$lote_serie') ";
                        $oIfx->Query($sql_sp);
                        // echo $sql_sp;

                        $sql_lote_existe = "select  sum(cant_lote) as cant, num_lote,  MAX(fecha_ela_lote) as felab, MAX(fecha_cad_lote) as fcad, 
                                prod_cod_prod, prod_nom_prod, costo
                                from tmp_prod_lote_web where
                                user_cod_web  = $id_user and
                                bode_cod_bode = $bode_cod_bode and
                                empr_cod_empr = $idempresa and
                                sucu_cod_sucu = $idsucursal
                                group by 2, 5, 6, 7
                                having  sum(cant_lote) <> 0
                                order by fcad 
                                limit 800
                                ";

                        $lote_consulta = consulta_string($sql_lote_existe, 'num_lote', $oIfx, '');
                        $fecha_ela_consulta = consulta_string($sql_lote_existe, 'felab', $oIfx, '');
                        $fecha_cad_consulta = consulta_string($sql_lote_existe, 'fcad', $oIfx, '');
                        $cantidad_lote = consulta_string($sql_lote_existe, 'cant', $oIfx, '');

                        if (!empty($lote_consulta)) {
                            $table_cab .= '<td align="left">' . $lote_consulta . '</td>';
                            $table_cab .= '<td align="left">' . $fecha_ela_consulta . '</td>';
                            $table_cab .= '<td align="left">' . $fecha_cad_consulta . '</td>';
                        } else {
                            $mostrar_procesar = 'N';
                            $table_cab .= '<td align="left" style="background:yellow">' . $lote_serie . '(No Existe)</td>';
                            $table_cab .= '<td align="left" style="background:yellow">' . $fecha_ela_consulta . '(No Existe)</td>';
                            $table_cab .= '<td align="left" style="background:yellow">' . $fecha_cad_consulta . '(No Existe)</td>';
                        }

                        if ($cantidad_lote < $cantidad) {
                            $table_cab .= '<td style="background:yellow">' . $cantidad . ' (Sin Stock ' . $cantidad_lote . ')</td>';
                            $mostrar_procesar = 'N';
                        } else {
                            $table_cab .= '<td align="right">' . $cantidad . '</td>';
                        }
                    } else {
                        $table_cab .= '<td align="left">' . $lote_serie . '</td>';
                        $table_cab .= '<td align="left"></td>';
                        $table_cab .= '<td align="left"></td>';

                        if ($prbo_dis_prod < $cantidad && $prod_stock_neg == 'N') {
                            $table_cab .= '<td style="background:yellow">' . $cantidad . ' (Sin Stock)</td>';
                            $mostrar_procesar = 'N';
                        } else {
                            $table_cab .= '<td align="right">' . $cantidad . '</td>';
                        }
                    }

                    // ------------------------------------------------------------------------------------------------
                    // FIN CONSULTA EXISTE LOTE
                    //-------------------------------------------------------------------------------------------------


                    $table_cab .= '<td>' . $fob . '</td>';

                    if (!empty($array_prec[trim($ccosto)])) {
                        $table_cab .= '<td>' . $array_prec[$ccosto] . '</td>';
                    } else {
                        $table_cab .= '<td style="background:yellow">' . $ccosto . ' (Centro Costos no existe)</td>';
                        $mostrar_procesar = 'N';
                    }
                    $table_cab .= '<td align="left">' . $detalle . '</td>';




                    for ($j = 1; $j <= $cont_pvp; $j++) {
                        $table_cab .= '<td>' . ${"pvp" . $j} . '</td>';
                    }



                    $table_cab .= '</tr>';

                    $array[] = array(
                        $array_bode_cod[$bode_cod],
                        $prod_cod,
                        $prod_nom,
                        $array_prec_cod[$ccosto],
                        $cantidad,
                        $fob
                    );
                }
                $x++;
            }

            $_SESSION['U_PROD_COD_PRECIO'] = $array;

            $html_tabla .= $table_cab;
            $html_tabla .= "</table>";

            if ($mostrar_procesar == 'S') {
                $oReturn->script("mostrar_procesar()");
            } else {
                $oReturn->script("ocultar_procesar()");
            }

            $oReturn->assign("divFormularioDetalle2", "innerHTML", $html_tabla);
            $oReturn->assign("divFormularioDetalle3", "innerHTML", $html_tabla);
        } else {
            $oReturn->script("Swal.fire({
                                            title: '<h3><strong>!!!!....Archivo Incorrecto, por favor subir Archivo con extension .txt...!!!!!</strong></h3>',
                                            width: 800,
                                            type: 'error',   
                                            timer: 3000   ,
                                            showConfirmButton: false
                                            })");
            $oReturn->assign("divFormularioDetalle2", "innerHTML", '');
            $oReturn->assign("divFormularioDetalle3", "innerHTML", '');
        }
    } catch (Exception $ex) {
        $oReturn->alert($ex->getMessage());
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}

function cargar_ord_compra($aForm = '')
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

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();


    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $aForm['sucursal'];


    unset($_SESSION['U_PROD_COD_PRECIO']);

    unset($_SESSION['aDataGird_INV_MRECO']);

    //////////////



    try {

        // DATOS
        // BODEGA

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
                $sql_adicional_sucu = ' and subo_cod_sucu in (' . $sucursales_usuario . ')';
            } else {
                $sql_adicional_sucu = ' and subo_cod_sucu = ' . $idsucursal;
            }
        }
        // ---------------------------------------------------------------------------------------------------------
        // FIN CONTROL CLPV POR USUARIO, SUCURSALES
        // ---------------------------------------------------------------------------------------------------------


        $sql = "select bode_cod_bode, bode_nom_bode from saesubo, saebode where
                        bode_cod_bode = subo_cod_bode and
                        bode_cod_empr = $idempresa and
                        subo_cod_empr = $idempresa 
                        $sql_adicional_sucu
                        ";
        unset($array_bode);
        unset($array_bode_cod);
        $array_bode     = array_dato($oIfx, $sql, 'bode_nom_bode', 'bode_nom_bode');
        $array_bode_cod = array_dato($oIfx, $sql, 'bode_nom_bode', 'bode_cod_bode');


        // PRODUCTO
        $sql = "select prod_cod_prod, prod_nom_prod from saeprod where
                        prod_cod_empr = $idempresa and
                        prod_cod_sucu = $idsucursal
                        group by 1,2  ";
        unset($array_prod);
        unset($array_prod_cod);
        $array_prod     = array_dato($oIfx, $sql, 'prod_cod_prod', 'prod_cod_prod');


        // CENTRO DE COSTO
        $sql = "select ccosn_cod_ccosn,  ccosn_nom_ccosn
                from saeccosn where
                ccosn_cod_empr = $idempresa and
                ccosn_mov_ccosn = 1 order by 2";

        unset($array_prec);
        unset($array_prec_cod);
        $array_prec     = array_dato($oIfx, $sql, 'ccosn_nom_ccosn', 'ccosn_nom_ccosn');
        $array_prec_cod = array_dato($oIfx, $sql, 'ccosn_nom_ccosn', 'ccosn_cod_ccosn');

        $archivo = $aForm['archivo'];

        // archivo txt
        $archivo_real = substr($archivo, 12);
        list($xxxx, $exten) = explode(".", $archivo_real);

        if ($exten == 'txt') {
            $nombre_archivo = "upload/" . $archivo_real;

            $file       = fopen($nombre_archivo, "r");
            $datos      = file($nombre_archivo);
            $NumFilas   = count($datos);

            unset($aDataGrid);
            unset($aDataPrecio);
            $aDataGrid  = $_SESSION['aDataGird_INV_MRECO'];
            $aDataPrecio  = $_SESSION['aDataGird_PRECIO'];
            $aLabelGrid = $_SESSION['aLabelGirdProd_INV_MRECO'];


            $aLabelGrid = array(
                'Id',
                'Bodega',
                'Codigo Item',
                'Descripcion',
                'Unidad',
                'Cantidad',
                'Costo',
                'Iva',
                'Dscto 1',
                'Dscto 2',
                'Dscto Gral',
                'Total',
                'Total Con Iva',
                'Modificar',
                'Eliminar',
                'Cuenta',
                'Cuenta Iva',
                'Centro Costo',
                'Cuenta Gasto',
                'Detalle',
                'Lote/Serie',
                'Elaboracion',
                'Caduca'
            );


            $cont = 0;
            $cont_pvp = 0;
            $datos_txt = explode("	", $datos[0]);
            foreach ($datos_txt as $val1) {
                if ($cont > 9) {
                    $cont_pvp++;
                }
                $cont++;
            }



            $x = 1;
            $oReturn->alert('Buscando ...');
            unset($array);
            foreach ($datos as $val) {
                /*BODEGA	    CODIGO	        PRODUCTO	    CANTIDAD	        CENTRO DE COSTO	        FOB
                        */





                list(
                    $bode_cod,
                    $prod_cod,
                    $prod_nom,
                    $cantidad,
                    $ccosto,
                    $detalle,
                    $lote_serie,
                    $fob,
                    $pvp1,
                    $pvp2,
                    $pvp3,
                    $pvp4,
                    $pvp5,
                    $pvp6,
                    $pvp7,
                    $pvp8,
                    $pvp9,
                    $pvp10
                ) = explode("	", $val);
                $costo_limpio = str_replace(',', '.', $fob);

                if (empty($costo_limpio)) {
                    $costo_limpio = 0;
                }

                if ($x > 1 && !empty($bode_cod)) {

                    $array[] = array(
                        $array_bode_cod[$bode_cod],
                        $prod_cod,
                        $prod_nom,
                        $array_prec_cod[$ccosto],
                        $cantidad,
                        $costo_limpio
                    );

                    // echo($array[0][0][0]);
                    // exit;


                    $sql_prod_nom = "select prod_nom_prod from saeprod where prod_cod_prod = '$prod_cod'";
                    $prod_nom = consulta_string($sql_prod_nom, 'prod_nom_prod', $oIfx, '');

                    $sql_prbo_cuentas = "select prbo_cta_inv, prbo_cta_ideb, prbo_cod_unid from saeprbo where prbo_cod_prod = '$prod_cod'";
                    $prbo_cta_inv = consulta_string($sql_prbo_cuentas, 'prbo_cta_inv', $oIfx, '');
                    $prbo_cta_ideb = consulta_string($sql_prbo_cuentas, 'prbo_cta_ideb', $oIfx, '');
                    $prbo_cod_unid = consulta_string($sql_prbo_cuentas, 'prbo_cod_unid', $oIfx, '');


                    $cantidad             = $cantidad;
                    $codigo_barra         = '';
                    $codigo_producto     = $prod_cod;
                    $costo                = $costo_limpio;
                    $iva                 = 0;
                    $iva                = 0;
                    $idbodega             = $array_bode_cod[$bode_cod];
                    $descuento             = 0;
                    $descuento_2         = 0;
                    $cuenta_inv         = $prbo_cta_inv;
                    $cuenta_iva         = $prbo_cta_ideb;
                    $peso                  = 0;
                    $idunidad         = $prbo_cod_unid;



                    $descuento_general = 0;
                    // TOTAL
                    $total_fac     = 0;
                    $dsc1         = ($costo * $cantidad * $descuento) / 100;
                    $dsc2         = ((($costo * $cantidad) - $dsc1) * $descuento_2) / 100;
                    if ($descuento_general > 0) {
                        // descto general
                        $dsc3                 = ((($costo * $cantidad) - $dsc1 - $dsc2) * $descuento_general) / 100;
                        $total_fact_tmp     = ((($costo * $cantidad) - ($dsc1 + $dsc2 + $dsc3)));
                        $tmp                 = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                    } else {
                        // sin descuento general
                        $total_fact_tmp     = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                        $tmp                 = $total_fact_tmp;
                    }

                    $total_fac = round($total_fact_tmp, 2);

                    // total con iva
                    if ($iva > 0) {
                        $total_con_iva = round((($total_fac * $iva) / 100), 2) + $total_fac;
                    } else {
                        $total_con_iva = $total_fac;
                    }




                    // ------------------------------------------------------------------------------------------------
                    // CONSULTA EXISTE LOTE
                    //-------------------------------------------------------------------------------------------------

                    if (!empty($lote_serie)) {
                        $sql_cod_bode = "SELECT bode_cod_bode from saebode where bode_nom_bode = '$bode_cod'";
                        $bode_cod_bode = consulta_string($sql_cod_bode, 'bode_cod_bode', $oIfx, '');


                        $id_user = $_SESSION['U_ID'];
                        $fecha_ini = '2018-01-01';
                        $fecha_fin = '2030-01-01';

                        $sql = "delete from tmp_prod_lote_web where user_cod_web = $id_user";
                        $oIfx->QueryT($sql);

                        $sql_sp = "select * from sp_lotes_productos_web( $idempresa, $idsucursal, $bode_cod_bode, '$fecha_ini', '$fecha_fin', '$prod_cod', '$prod_cod', '2' , $id_user, '$lote_serie') ";
                        $oIfx->Query($sql_sp);
                        // echo $sql_sp;

                        $sql_lote_existe = "select  sum(cant_lote) as cant, num_lote,  MAX(fecha_ela_lote) as felab, MAX(fecha_cad_lote) as fcad, 
                                prod_cod_prod, prod_nom_prod, costo
                                from tmp_prod_lote_web where
                                user_cod_web  = $id_user and
                                bode_cod_bode = $bode_cod_bode and
                                empr_cod_empr = $idempresa and
                                sucu_cod_sucu = $idsucursal
                                group by 2, 5, 6, 7
                                having  sum(cant_lote) <> 0
                                order by fcad 
                                limit 800
                                ";

                        $lote_consulta = consulta_string($sql_lote_existe, 'num_lote', $oIfx, '');
                        $fecha_ela_consulta = consulta_string($sql_lote_existe, 'felab', $oIfx, '');
                        $fecha_cad_consulta = consulta_string($sql_lote_existe, 'fcad', $oIfx, '');

                        if (!empty($lote_consulta)) {
                            $fela = $fecha_ela_consulta;
                            $fecad = $fecha_cad_consulta;
                        } else {
                            $fela = $fecha_ela_consulta;
                            $fecad = $fecha_cad_consulta;
                        }
                    } else {
                        $fela = '';
                        $fela = '';
                    }

                    // ------------------------------------------------------------------------------------------------
                    // FIN CONSULTA EXISTE LOTE
                    //-------------------------------------------------------------------------------------------------






                    //GUARDA LOS DATOS DEL DETALLE
                    $cont = count($aDataGrid);
                    // cantidad
                    $fu->AgregarCampoNumerico($cont . '_cantidad', 'Cantidad|LEFT', false, $cantidad, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($cont . '_cantidad', 'cargar_update_cant(\'' . $cont . '\');');

                    // costo
                    $fu->AgregarCampoNumerico($cont . '_costo', 'Costo|LEFT', false, $costo, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($cont . '_costo', 'cargar_update_cant(\'' . $cont . '\');');

                    // iva
                    $fu->AgregarCampoNumerico($cont . '_iva', 'Impuesto|LEFT', false, $iva, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($cont . '_iva', 'cargar_update_cant(\'' . $cont . '\');');

                    // descto1
                    $fu->AgregarCampoNumerico($cont . '_desc1', 'Descto1|LEFT', false, 0, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($cont . '_desc1', 'cargar_update_cant(\'' . $cont . '\');');

                    // descto2
                    $fu->AgregarCampoNumerico($cont . '_desc2', 'Descto2|LEFT', false, 0, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($cont . '_desc2', 'cargar_update_cant(\'' . $cont . '\');');

                    // cuenta de gasto
                    $cta_gasto = $aForm['cuenta_gasto'];
                    $html_cta = '';
                    $fu->AgregarCampoTexto($cont . '_cta_gasto', 'Cuenta Gasto', false, $cta_gasto, 100, 100);
                    $fu->AgregarComandoAlEscribir($cont . '_cta_gasto', 'cta_gasto_22(\'' . $cont . '_cta_gasto' . '\', event );');
                    $html_cta = $fu->ObjetoHtml($cont . '_cta_gasto');


                    // centro de costo
                    $ccos = $aForm['centro_costo'];
                    $html_ccos = '';
                    $fu->AgregarCampoTexto($cont . '_ccos', 'Centro Costo', false, $array_prec_cod[$ccosto], 100, 100);
                    $fu->AgregarComandoAlEscribir($cont . '_ccos', 'centro_costo_22( \'' . $cont . '_ccos' . '\', event );');
                    $html_ccos = $fu->ObjetoHtml($cont . '_ccos');

                    $aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
                    $aDataGrid[$cont][$aLabelGrid[1]] = $idbodega;
                    $aDataGrid[$cont][$aLabelGrid[2]] = $codigo_producto;
                    $aDataGrid[$cont][$aLabelGrid[3]] = $prod_nom;
                    $aDataGrid[$cont][$aLabelGrid[4]] = $idunidad;

                    $aDataGrid[$cont][$aLabelGrid[5]] = $fu->ObjetoHtml($cont . '_cantidad');  //$cantidad;
                    $aDataGrid[$cont][$aLabelGrid[6]] = $fu->ObjetoHtml($cont . '_costo'); //costo;
                    $aDataGrid[$cont][$aLabelGrid[7]] = $fu->ObjetoHtml($cont . '_iva'); //iva
                    $aDataGrid[$cont][$aLabelGrid[8]] = $fu->ObjetoHtml($cont . '_desc1'); // desc1
                    $aDataGrid[$cont][$aLabelGrid[9]] = $fu->ObjetoHtml($cont . '_desc2'); // dec2

                    $aDataGrid[$cont][$aLabelGrid[10]] = $descuento_general;
                    $aDataGrid[$cont][$aLabelGrid[11]] = $total_fac;
                    $aDataGrid[$cont][$aLabelGrid[12]] = $total_con_iva;
                    $aDataGrid[$cont][$aLabelGrid[13]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil3.png"
                                title = "Presione aqui para Modificar"
                                style="cursor: hand !important; cursor: pointer !important;"
                                onclick="agregar_detalle(1);"
                                alt="Modificar"
                                align="bottom" />';
                    $aDataGrid[$cont][$aLabelGrid[14]] = '
                            <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                                title = "Presione aqui para Eliminar"
                                style="cursor: hand !important; cursor: pointer !important;"
                                onclick="javascript:elimina_detalle(' . $id . ');"
                                alt="Eliminar"
                                align="bottom" />';
                    $aDataGrid[$cont][$aLabelGrid[15]] = '';
                    $aDataGrid[$cont][$aLabelGrid[16]] = '';
                    $aDataGrid[$cont][$aLabelGrid[17]] = $html_ccos . ' <div id ="imagen1" class="btn btn-primary btn-sm" onclick="abrir_modal_distribucion(\'' . $cont . '\')">
                                                                            <span class="glyphicon glyphicon-list"></span>
                                                                        </div>
                                                                        ';
                    $aDataGrid[$cont][$aLabelGrid[18]] = $html_cta;
                    $aDataGrid[$cont][$aLabelGrid[19]] = $detalle;
                    $aDataGrid[$cont][$aLabelGrid[20]] = $lote_serie;
                    $aDataGrid[$cont][$aLabelGrid[21]] = $fela;
                    $aDataGrid[$cont][$aLabelGrid[22]] = $fecad;
                    // $aDataGrid[$cont][$aLabelGrid[23]] = $serie_prod;



                    for ($j = 1; $j <= $cont_pvp; $j++) {
                        $aDataGrid[$cont]["pvp" . $j] = ${"pvp" . $j};
                    }

                    // $aDataGrid[$cont]['precio'] = 47;
                    // $aDataPrecio[$cont]['precio_1'] = 

                    //Final de la lectura del archivo
                }
                $x++;
            }


            $_SESSION['aDataGird'] = $aDataGrid;
            $sHtml = mostrar_grid($idempresa);
            $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
            // $oReturn->script('limpiar()');
            $oReturn->script('totales();');
            $oReturn->script('cerrar_modal();');
            $oReturn->script('cerrar_ventana();');
        } else {
            $oReturn->script("Swal.fire({
                                            title: '<h3><strong>!!!!....Archivo Incorrecto, por favor subir Archivo con extension .txt...!!!!!</strong></h3>',
                                            width: 800,
                                            type: 'error',   
                                            timer: 3000   ,
                                            showConfirmButton: false
                                            })");
            $oReturn->assign("divFormularioDetalle", "innerHTML", '');
        }
    } catch (Exception $ex) {
        $oReturn->alert($ex->getMessage());
    }
    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}


// CONTROL TRAN
function control_tran($aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    $tran = $aForm['tran'];
    $idempresa =  $aForm['empresa'];

    $sql = "select  defi_pro_defi, defi_tip_comp, defi_cod_cuen, defi_cco_defi, defi_prec_vent from saedefi where
                    defi_cod_empr = $idempresa and
                    defi_cod_tran = '$tran' ";
    $bloquear_cliente_prov = '';
    $ncre = '';
    $cta_gasto = '';
    $ccos = '';
    $defi_prec_vent = '';
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $bloquear_cliente_prov = $oIfx->f('defi_pro_defi');
            $ncre      = $oIfx->f('defi_tip_comp');
            $cta_gasto = $oIfx->f('defi_cod_cuen');
            $ccos      = $oIfx->f('defi_cco_defi');
            $defi_prec_vent = $oIfx->f('defi_prec_vent');
        }
    }
    //        $ncre = consulta_string($sql, 'defi_tip_comp', $oIfx, '');
    if (!empty($ncre)) {
        // bloqueo form
        $oReturn->script('habilitar_form();');
        // FORM SRI
        $ifu->AgregarCampoListaSQL('tran_tloc', 'Transaccion|left', "SELECT strs_cod_strs,  ( strs_cod_strs || '   ' || strs_des_strs ) as desc  FROM saestrs   ", true, '300');
        $ifu->AgregarCampoTexto('ruc_tloc', 'RUC/CI/PASS|left', true, '', 120, 120);
        $ifu->AgregarCampoTexto('cliente_nombre_tloc', 'Razon Social|left', false, '', 250, 200);
        $ifu->AgregarCampoListaSQL('tipo_tloc', 'Tipo Comp|left', "select tcmp_cod_tcmp, ( tcmp_cod_tcmp || '   ' || tcmp_des_tcmp  ) as tipo FROM saetcmp ", true, '300');
        $ifu->AgregarCampoFecha('fecha_emis_tloc', 'Fecha Emision|left', true, date('Y') . '/' . date('m') . '/' . date('d'));
        $ifu->AgregarCampoFecha('fecha_cont_tloc', 'Fecha Reg. Contable|left', true, date('Y') . '/' . date('m') . '/' . date('d'));
        $ifu->AgregarCampoTexto('secuencial_tloc', 'No.- Secuencial|left', true, '', 180, 100);
        $ifu->AgregarCampoTexto('serie_tloc', 'No.- Serie|left', true, '', 80, 80);
        $ifu->AgregarCampoTexto('auto_tloc', 'No.- Autorizacion|left', true, '', 180, 100);
        $ifu->AgregarCampoFecha('fecha_cad_tloc', 'Fecha Caducidad|left', true, date('Y') . '/' . date('m') . '/' . date('d'));

        $ifu->AgregarCampoTexto('factura_modi', 'No.- Factura|left', true, '', 180, 100);
        $ifu->AgregarCampoTexto('serie_modi', 'No.- Serie|left', true, '', 80, 80);
        $ifu->AgregarCampoTexto('auto_modi', 'No.- Autorizacion|left', true, '', 180, 100);
        $ifu->AgregarCampoFecha('fecha_cad_modi', 'Fecha Caducidad|left', true, date('Y') . '/' . date('m') . '/' . date('d'));
        $ifu->AgregarCampoListaSQL('tipo_modi', 'Tipo Comp. Modif.|left', "select tcmp_cod_tcmp, ( tcmp_cod_tcmp || '   ' || tcmp_des_tcmp  ) as tipo FROM saetcmp ", true, '300');

        $ifu->cCampos["tran_tloc"]->xValor = '01';
        $ifu->cCampos["tipo_tloc"]->xValor = '04';

        $sHtml .= '<fieldset style="border:#999999 1px solid; padding:2px; text-align:center; width:68%;">
      		      <legend class="Titulo">DATOS DE EGRESO DE NOTAS DE CREDITO / DEBITO</legend>';
        $sHtml .= '<table align="center" cellpadding="0" cellspacing="2" width="100%" border="0">
                            <tr class="msgFrm"><td colspan="4" align="center">Los campos con * son de ingreso obligatorio</td></tr>
                            <tr><th colspan="4" align="center" class="diagrama">DATOS SRI ONLINE</th></tr>';
        $sHtml .= '<tr>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('tran_tloc') . '</td>
                                <td>' . $ifu->ObjetoHtml('tran_tloc') . '</td>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('ruc_tloc') . '</td>
                                <td>' . $ifu->ObjetoHtml('ruc_tloc') . '</td>
                      </tr>';
        $sHtml .= '<tr>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('cliente_nombre_tloc') . '</td>
                                <td colspan="2">' . $ifu->ObjetoHtml('cliente_nombre_tloc') . '</td>
                      </tr>';
        $sHtml .= '<tr>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('tipo_tloc') . '</td>
                                <td colspan="2">' . $ifu->ObjetoHtml('tipo_tloc') . '</td>
                      </tr>';
        $sHtml .= '<tr>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('fecha_emis_tloc') . '</td>
                                <td>' . $ifu->ObjetoHtml('fecha_emis_tloc') . '</td>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('fecha_cont_tloc') . '</td>
                                <td>' . $ifu->ObjetoHtml('fecha_cont_tloc') . '</td>
                      </tr>';
        $sHtml .= '<tr>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('serie_tloc') . '</td>
                                <td>' . $ifu->ObjetoHtml('serie_tloc') . '</td>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('secuencial_tloc') . '</td>
                                <td>' . $ifu->ObjetoHtml('secuencial_tloc') . '</td>
                      </tr>';
        $sHtml .= '<tr>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('auto_tloc') . '</td>
                                <td>' . $ifu->ObjetoHtml('auto_tloc') . '</td>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('fecha_cad_tloc') . '</td>
                                <td>' . $ifu->ObjetoHtml('fecha_cad_tloc') . '</td>
                      </tr>';
        $sHtml .= '<tr><th colspan="4" align="center" class="diagrama">COMPROBANTE MODIFICADO</th></tr>';
        $sHtml .= '<tr>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('tipo_modi') . '</td>
                                <td colspan="2">' . $ifu->ObjetoHtml('tipo_modi') . '</td>
                      </tr>';
        $sHtml .= '<tr>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('serie_modi') . '</td>
                                <td>' . $ifu->ObjetoHtml('serie_modi') . '</td>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('factura_modi') . '</td>
                                <td>' . $ifu->ObjetoHtml('factura_modi') . '</td>
                      </tr>';
        $sHtml .= '<tr>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('auto_modi') . '</td>
                                <td>' . $ifu->ObjetoHtml('auto_modi') . '</td>
                                <td class="labelFrm">' . $ifu->ObjetoHtmlLBL('fecha_cad_modi') . '</td>
                                <td>' . $ifu->ObjetoHtml('fecha_cad_modi') . '</td>
                      </tr>';
        $sHtml .= '</table></fieldset>';
        $oReturn->assign("divFormularioSRI", "innerHTML", $sHtml);
    } else {
        // desbloqueo form
        $oReturn->script('deshabilitar_form();');
        $oReturn->clear("divFormularioSRI", "innerHTML");

        $aDataGrid = $_SESSION['aDataGird'];
        $op = '';
        unset($_SESSION['aDataGird']);
        unset($_SESSION['aDataGirdRete']);
        unset($_SESSION['aDataGird_Pago']);
        $cont = count($aDataGird);
        if ($cont > 0) {
            $sHtml2 = mostrar_grid(0);
        } else {
            $sHtml2 = "";
        }

        $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml2);
        $oReturn->assign("divFormularioDetalle_FP", "innerHTML", $sHtml2);
        $oReturn->assign("divFormularioDetalleRET", "innerHTML", $sHtml2);
        $oReturn->assign("divTotal", "innerHTML", "");
        $oReturn->assign("ruc", "value", '');
        $oReturn->assign("cliente", "value", '');
        $oReturn->assign("cliente_nombre", "value", '');
        $oReturn->assign("cuenta_prove", "value", '');
        $oReturn->assign("tel_prove", "value", '');
        $oReturn->assign("factura", "value", '');
        $oReturn->assign("tipo_pago", "value", '');
        $oReturn->assign("forma_pago1", "value", '');
        $oReturn->assign("auto_prove", "value", '');
        $oReturn->assign("fecha_validez", "value", date("Y-m-d"));
        $oReturn->assign("serie_prove", "value", '');
        $oReturn->assign("fecha_entrega", "value", date("Y-m-d"));
        $oReturn->assign("plazo", "value", '');
        $oReturn->assign("contri_prove", "value", '');
        $oReturn->assign("fac_ini", "innerHTML", '');
        $oReturn->assign("fac_fin", "innerHTML", '');
    }

    if ($bloquear_cliente_prov == 'NI') {
        $oReturn->script('deshabilitar_form();');
    } else {
        $oReturn->script('habilitar_form();');
    }

    // centro costo - cuetna contable gasto
    $oReturn->assign("cuenta_gasto", "value", $cta_gasto);
    $oReturn->assign("centro_costo", "value", $ccos);

    if ($defi_prec_vent == '0') {
        $oReturn->assign("div_label_costo", "innerHTML", '* Precio:');
    } else {
        $oReturn->assign("div_label_costo", "innerHTML", '* Costo:');
    }


    return $oReturn;
}

/****************************************/
/* DF01 :: G U A R D A      P E D I D O */
/****************************************/
function guarda_pedido($opcion_tmp, $aForm = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();
    //      VARIABLES
    $idempresa     = $aForm['empresa'];
    $sucursal     = $aForm['sucursal'];
    $aDataGrid     = $_SESSION['aDataGird'];
    $contdata     = count($aDataGrid);



    if ($contdata > 0) {
        // TRANSACCIONALIDAD
        try {
            // commit
            $oIfx->QueryT('BEGIN WORK;');
            // transaccion de informix
            /**************************************************************************/
            /* F E C H A     D E     P E D I D O     Y     V E N C I M I E N T O      */
            /**************************************************************************/
            $cliente = $aForm['cliente'];

            if (empty($cliente)) {
                $cliente = 'NULL';
            }



            $cliente_nom    = $aForm['cliente_nombre'];
            $ruc            = $aForm['ruc'];
            $fecha_pedido   = $aForm['fecha_pedido'];
            $fecha_entrega  = $aForm['fecha_entrega'];
            $plazo          = $aForm['plazo'];
            $tran           = $aForm['tran'];
            $moneda         = $aForm['moneda'];
            $factura        = $aForm['factura'];
            $serie_prove    = $aForm['serie_prove'];
            $auto_prove     = $aForm['auto_prove'];
            $fecha_prove    = $aForm['fecha_validez'];
            $tipo_pago      = $aForm['tipo_pago'];
            $fpago_prove    = $aForm['forma_pago1'];
            $detalle        = $aForm['observaciones'];
            $anio           = substr($aForm['fecha_pedido'], 0, 4);
            $idprdo         = (substr($aForm['fecha_pedido'], 5, 2)) * 1;
            $fecha_ejer     = $anio . '-12-31';
            $sql = "select ejer_cod_ejer from saeejer where ejer_fec_finl = '$fecha_ejer' and ejer_cod_empr = $idempresa ";
            $idejer = consulta_string($sql, 'ejer_cod_ejer', $oIfx, 1);
            $fecha_servidor = date("Y-m-d");
            $nombre_cliente = $aForm['cliente_nombre'];
            $usuario_informix =  $_SESSION['U_USER_INFORMIX'];
            $usuario_web =  $_SESSION['U_ID'];
            $sql2 = "SELECT usua_cod_empl FROM SAEUSUA WHERE USUA_COD_USUA = $usuario_informix";
            $empleado = consulta_string($sql2, 'usua_cod_empl', $oIfx, '');
            $sql_tcambio = "select tcam_fec_tcam, tcam_cod_tcam, tcam_val_tcam from saetcam where
                                                tcam_cod_mone = $moneda and
                                                mone_cod_empr = $idempresa and
                                                tcam_fec_tcam = (select max(tcam_fec_tcam) from saetcam where
                                                                        tcam_cod_mone = $moneda and
                                                                        mone_cod_empr = $idempresa) ";
            $tcambio     = consulta_string($sql_tcambio, 'tcam_cod_tcam', $oIfx, 1);
            $val_tcambio = consulta_string($sql_tcambio, 'tcam_val_tcam', $oIfx, 0);

            $desc_general = $aForm['descuento_general'];
            $desc_valor   = $aForm['descuento_valor'];
            $iva_total    = $aForm['iva_total'];
            $con_iva      = $aForm['con_iva'];
            $sin_iva      = $aForm['sin_iva'];
            $anticipo     = $aForm['anticipo'];
            $fact_tot     = $aForm['total_fac'];
            $fac_ini      = $aForm['fac_ini'];
            $fac_fin      = $aForm['fac_fin'];
            $cuenta_prove = $aForm['cuenta_prove'];
            $dir_prove    = $aForm['dir_prove'];
            $tel_prove    = $aForm['tel_prove'];
            $hora =  date("H:i:s");

            $sql       = "select tran_des_tran from saetran where
                                        tran_cod_tran = '$tran' and
                                        tran_cod_empr = $idempresa and
                                        tran_cod_sucu = $sucursal ";
            $des_tran = consulta_string($sql, 'tran_des_tran', $oIfx, '');

            // OTROS
            $total_otros = 0;
            /* if (count($array_otros) > 0) {
                $txt = '';
                $total_otros = 0;
                foreach ($array_otros as $val) {
                    $id_otro  = $val[0];
                    $det_otro = $val[1];
                    $txt = $id_otro . '_OTRO';
                    $val_txt = $aForm[$txt];
                    if (empty($val_txt)) {
                        $val_txt = 0;
                    }
                    $total_otros += $val_txt;
                } // fin foreach
            } // fin otros*/

            $total_compra = $fact_tot - $desc_valor + $iva_total + $total_otros;

            // ASIENTO CONTABLE
            // TIDU
            $sql = "select  defi_cod_tidu, defi_tip_comp   from saedefi where
                                    defi_cod_empr = $idempresa and
                                    defi_cod_sucu = $sucursal and
                                    defi_cod_tran = '$tran' ";
            $ncre = '';
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                    $tidu = $oIfx->f('defi_cod_tidu');
                    $ncre = $oIfx->f('defi_tip_comp');
                }
            }

            $estado_minv = '1';

            // SECUENCIAL MINV INGRESO COMPRA
            // defi_cos_defi => costos  (0 => si; 1 => no; 2 => calculado) ; defi_cost_defi => costeo (0 => unitario; 1 => total)
            // defi_prec_vent => 0 check_activo; 1 check inactivo
            $sql_defi = "SELECT DEFI_COD_MODU, DEFI_TRS_DEFI  , DEFI_TIP_DEFI, DEFI_FOR_DEFI, defi_cos_defi, defi_cost_defi, defi_prec_vent FROM SAEDEFI WHERE
                                        DEFI_COD_EMPR = $idempresa AND
                                        DEFI_COD_SUCU = $sucursal and
                                        defi_cod_modu = 10 and
                                        defi_tip_defi = '1' and
                                        defi_cod_tran = '$tran' ";
            $secu_minv = '';
            $formato = 0;
            $defi_cos_defi = '';
            $defi_cost_defi = '';
            $defi_prec_vent = '';
            if ($oIfx->Query($sql_defi)) {
                if ($oIfx->NumFilas() > 0) {
                    $secu_minv = $oIfx->f('defi_trs_defi');
                    $formato   = $oIfx->f('defi_for_defi');
                    $defi_cos_defi   = $oIfx->f('defi_cos_defi');
                    $defi_cost_defi   = $oIfx->f('defi_cost_defi');
                    $defi_prec_vent   = $oIfx->f('defi_prec_vent');
                }
            }
            $oIfx->Free();

            $costo_cero_ad = 'N';
            if ($defi_cos_defi == 1 && $defi_cost_defi == 0) {
                $costo_cero_ad = 'S';
            }

            $precio_venta_sn_ad = 'N';
            // check activo
            if ($defi_prec_vent == '0') {
                $precio_venta_sn_ad = 'S';
            }

            $secu_minv = secuencial(2, '0', $secu_minv, 8);
            $hora      =  date("Y-m-d H:i:s");


            $sql_ultimo_id = "select max(minv_num_comp) as minv_num_comp from saeminv";
            $ultimo_id = consulta_string($sql_ultimo_id, 'minv_num_comp', $oIfx, '') + 1;


            //INGRESO DEL MOVIMIENTO  SAEMINV 
            $sql_minv  = "insert into saeminv(minv_num_comp, minv_num_plaz,  minv_num_sec,       minv_cod_tcam,
                                                          minv_cod_mone,  minv_cod_empr,      minv_cod_sucu,
                                                          minv_cod_tran,  minv_cod_modu,      minv_cod_empl,
                                                          minv_cod_ftrn,  minv_fmov,          minv_dege_minv,
                                                          minv_cod_usua,  minv_num_prdo,      minv_cod_ejer,
                                                          minv_fac_prov,  minv_fec_entr,      minv_fec_ser,   
                                                          minv_est_minv,  minv_tot_minv,      minv_con_iva,
                                                          minv_sin_iva,   minv_dge_valo,      minv_iva_valo,
                                                          minv_otr_valo,  minv_fle_minv,      minv_aut_usua,
                                                          minv_aut_impr,  minv_fac_inic,      minv_fac_fina,
                                                          minv_ser_docu,  minv_fec_valo,      minv_sucu_clpv,
                                                          minv_sno_esta,  minv_usu_minv ,     minv_cm1_minv,
                                                          minv_fec_regc,  minv_cod_fpagop,    minv_cod_tpago,
                                                          minv_ani_minv,  minv_mes_minv,      minv_user_web,
                                                          minv_comp_cont, minv_tran_minv,     minv_cod_clpv,
														  minv_hor_minv )
                                                  values($ultimo_id, 0,             '$secu_minv',        $tcambio,
                                                          $moneda,        $idempresa,         $sucursal,
                                                          '$tran',        10,                '$empleado',
                                                          '$formato',     '$fecha_pedido',    0,
                                                          $usuario_informix, $idprdo,         $idejer,
                                                          '$factura',     '$fecha_pedido',    current_date,        
                                                          '$estado_minv',  $fact_tot,         0,
                                                          0,              $desc_valor,        $iva_total,
                                                          $total_otros,   0,                 '$auto_prove',
                                                          '',             '$fac_ini',         '$fac_fin',
                                                          '$serie_prove', '$fecha_prove',      $sucursal,
                                                          0,              '$usua_nom_usua',   '$detalle',
                                                          current_date,        '$fpago_prove',     '$tipo_pago',
                                                          $anio,           $idprdo,            $usuario_web,
                                                          '$secu_asto' ,  '$secu_asto',        $cliente,
														  '$hora'		) ";
            $oIfx->QueryT($sql_minv);

            //UPDATE AL SECUENCIAL SAEDEFI
            $sql_update = "UPDATE SAEDEFI SET DEFI_TRS_DEFI = '$secu_minv' WHERE
                                            DEFI_COD_EMPR = $idempresa AND
                                            DEFI_COD_SUCU = $sucursal and
                                            defi_cod_modu = 10 and
                                            defi_tip_defi = '1' and
                                            defi_cod_tran = '$tran' ";
            $oIfx->QueryT($sql_update);

            //SERIAL DEL SAEDMIV
            $serial_minv = 0;
            $sql_serial = "select minv_num_comp from saeminv where
                                            minv_num_sec = '$secu_minv' and
                                            minv_cod_empr = $idempresa and
                                            minv_cod_sucu = $sucursal and
                                            minv_cod_tran = '$tran' ";
            $serial_minv = consulta_string($sql_serial, 'minv_num_comp', $oIfx, 0);

            $serial_minv = $ultimo_id;

            //                      DETALLE SAEDMOV
            $x = 1;
            $j = 0;
            unset($array_dmov);
            foreach ($aDataGrid as $aValues) {

                $sql_precio_etiq_dmov = '';
                if ($precio_venta_sn_ad == 'S') {
                    $sql_precio_etiq_dmov = ', dmov_prec_vent';
                }



                $sql_d = 'insert into saedmov(dmov_cod_dmov,   dmov_cod_prod,     dmov_cod_sucu,
                                                dmov_cod_empr,   dmov_cod_bode,     dmov_cod_unid,
                                                dmov_cod_ejer,   dmov_num_comp,     dmov_num_prdo,
                                                dmov_can_dmov,   dmov_can_entr,     dmov_cun_dmov, 
                                                dmov_cost_pro, dmov_nuev_sal,
                                                dmov_cto_dmov,   dmov_pun_dmov,     dmov_pto_dmov,
                                                dmov_ds1_dmov,   dmov_ds2_dmov,     dmov_ds3_dmov,
                                                dmov_ds4_dmov,   dmov_des_tota,     dmov_imp_dmov,
                                                dmov_est_dmov,   dmov_iva_dmov,     dmov_iva_porc,
                                                dmov_dis_dmov,   dmov_ice_dmov,     dmov_hor_crea,
                                                dmov_cod_tran,   dmov_fac_prov,     dmov_cod_clpv,
                                                dmov_fmov,       dmov_pto1_dmov,    dmov_cod_ccos,
                                                dmov_cod_cuen,   dmov_det_dmov,		
												dmov_cod_lote,   dmov_cad_lote,	 dmov_ela_lote
                                                ' . $sql_precio_etiq_dmov . '             
                                                        )
                                                values ';
                $aux      = 0;
                $total    = 0;
                $nuevo_saldo = 0;
                $pedf_iva = 0;
                $sql_d .= "(";
                foreach ($aValues as $aVal) {
                    if ($aux == 0) {
                        $sql_d .= " " . $x . ",";                 //dmov cod dmov
                    } elseif ($aux == 1) {
                        $bod = $aVal;
                    } elseif ($aux == 2) {
                        $prod = $aVal;
                    } elseif ($aux == 4) {
                        $sql_d .= " '" . $prod . "',";
                        $sql_d .= " '" . $sucursal . "',";
                        $sql_d .= " '" . $idempresa . "',";
                        $sql_d .= " '" . $bod . "',";
                        $sql_d .= " " . $aVal . ",";                     //dpef_cod_unid   		UNIDAD
                        $sql_d .= " '" . $idejer . "',";
                        $sql_d .= " '" . $serial_minv . "',";
                        $sql_d .= " '" . $idprdo . "',";
                    } elseif ($aux == 5) {
                        $cant = $aForm[$j . '_cantidad'];
                    } elseif ($aux == 6) {
                        $costo = $aForm[$j . '_costo'];
                    } elseif ($aux == 7) {                                  //IVA
                        $iva   = $aForm[$j . '_iva'];
                    } elseif ($aux == 8) {                                  //DESCUENTO 1
                        $descuento = $aForm[$j . '_desc1'];
                    } elseif ($aux == 9) {                                  //DESCUENTO 2
                        $descuento_2 = $aForm[$j . '_desc2'];
                    } elseif ($aux == 10) {
                        $desc_gral = $aVal;
                    } elseif ($aux == 11) {                                 //SUB TOTAL
                        $total = $aVal;
                    } elseif ($aux == 12) {                                 //TOTAL CON IVA
                        $total_iva = $aVal;
                    } elseif ($aux == 15) {                                 // CUENTA PROD
                        $cuenta_prod = $aVal;
                    } elseif ($aux == 16) {                                 // CUENTA IVA
                        $cuenta_iva = $aVal;
                    } elseif ($aux == 17) {                                 // Centro Costo
                        $ccos = $aForm[$j . '_ccos'];
                    } elseif ($aux == 18) {                                 // Cuenta Gasto
                        $cta_gasto = $aForm[$j . '_cta_gasto'];
                    } elseif ($aux == 19) {                                 // detalle
                        $detalle_dmov  = $aVal;
                    } elseif ($aux == 20) {
                        $lote = $aVal;
                    } elseif ($aux == 21) {
                        $fela = $aVal;
                        if (empty($fela)) {
                            $fela = 'NULL';
                        } else {
                            $fela = "'" . $fela . "'";
                        }
                    } elseif ($aux == 22) {

                        $fcad = $aVal;
                        if (empty($fcad)) {
                            $fcad = 'NULL';
                        } else {
                            $fcad = "'" . $fcad . "'";
                        }


                        // ARRAY DE CUENTA PROD Y IVA
                        $arrray_dmov[$cuenta_prod] += $total;
                        $arrray_dmov[$cuenta_iva]  += ($total_iva - $total);



                        $sql_precio_valor_dmov = '';
                        if ($precio_venta_sn_ad == 'S') {
                            if (empty($costo)) {
                                $costo = 0;
                            }
                            $sql_precio_valor_dmov = " ," . $costo . "  ";;
                        }


                        if ($costo_cero_ad == 'S') {
                            $costo = 0;
                        }

                        // sctok bodega
                        $sql = "select prbo_dis_prod, prbo_uco_prod from saeprbo where
                                                            prbo_cod_empr = $idempresa and
                                                            prbo_cod_sucu = $sucursal and
                                                            prbo_cod_bode = $bod and
                                                            prbo_cod_prod = '$prod' ";
                        $stock = consulta_string($sql, 'prbo_dis_prod', $oIfx, 0);
                        $nuevo_saldo = round(($stock - $cant) * $costo, 6);
                        $cero   = 0;
                        $estado = 1;
                        $dis    = 'N';
                        $sql_d .= " " . $cant . ",";                      //
                        $sql_d .= " '" . $cero . "',";           //
                        $sql_d .= " '" . $costo . "',";             //
                        $sql_d .= " '" . $costo . "',";             //
                        $sql_d .= " '" . $nuevo_saldo . "',";             //
                        $sql_d .= " " . $total . ",";
                        $sql_d .= " " . $costo . ",";                 //
                        $sql_d .= " " . $cero . ",";                     //dpef_por_iva		IVA
                        $sql_d .= " '" . $descuento . "',";             //desc1
                        $sql_d .= " '" . $descuento_2 . "',";             //dsc2
                        $sql_d .= " '" . $cero . "',";             //dsc3
                        $sql_d .= " '" . $cero . "',";             //dsc4
                        $sql_d .= " '" . $desc_gral . "',";             //dsc general
                        $sql_d .= " '" . $cero . "',";             //imp
                        $sql_d .= " '" . $estado . "',";             //estado
                        $sql_d .= " '" . $cero . "',";             //iva
                        $sql_d .= " '" . $iva . "',";             //dsc1
                        $sql_d .= " '" . $dis . "',";             //dis
                        $sql_d .= " '" . $cero . "',";             //ic
                        $sql_d .= " '" . $hora . "',";             //hora
                        $sql_d .= " '" . $tran . "',";             //tran
                        $sql_d .= " '" . $factura . "',";             //fac prov
                        $sql_d .= " " . $cliente . ",";             //cliente
                        $sql_d .= " '" . $fecha_servidor . "',";             //fecha server
                        $sql_d .= " '" . $cero . "', ";             //pto1
                        $sql_d .= " '" . $ccos . "', ";             //centro de costo
                        $sql_d .= " '" . $cta_gasto . "', ";             //cuenta d gasto
                        $sql_d .= " '" . $detalle_dmov . "',  ";     // detalle
                        $sql_d .= " '" . $lote . "',  ";     // lote
                        $sql_d .= " " . $fcad . ",  ";     // fac
                        $sql_d .= " " . $fela . "  ";     // fela
                        $sql_d .= $sql_precio_valor_dmov;

                        // sctok bodega
                        /*$sql = "select prbo_dis_prod, prbo_uco_prod from saeprbo where
                                                            prbo_cod_empr = $idempresa and
                                                            prbo_cod_sucu = $sucursal and
                                                            prbo_cod_bode = $bod and
                                                            prbo_cod_prod = '$prod' ";
                        $stock = consulta_string($sql, 'prbo_dis_prod', $oIfx, 0);*/
                        if ($cant > $stock)
                        {
                            throw new Exception("Cantidad Ingresada es Mayor al Stock del Producto: " . $prod);
                            //throw new Exception('No existe la bodega: ' . $bode_cod);
                        }
                        // COSTO
                        $cost_act  = $costo;
                        $cant_real = $stock - $cant;

                        if (empty($costo)) {
                            $costo = 0;
                        }

                        // actualiza stock en bodega                                                
                        $sql = "update saeprbo set prbo_dis_prod = ($stock - $cant), prbo_uco_prod = $costo where
                                                                prbo_cod_empr = $idempresa and
                                                                prbo_cod_sucu = $sucursal and
                                                                prbo_cod_bode = $bod and
                                                                prbo_cod_prod = '$prod' ";
                        $oIfx->QueryT($sql);

                        // saecost
                        // ID DEL SAECOST
                        $sql_id_cost = "select max(cost_cod_cost) as maximo from saecost where
                                                                    cost_cod_prod = '$prod' and
                                                                    cost_cod_empr = $idempresa ";
                        $cost_cod_cost = consulta_string($sql_id_cost, 'maximo', $oIfx, 0);
                        // INGRESO SAECOST
                        $sql_cost = "insert into saecost(cost_cod_cost,       cost_cod_prod,      cost_num_comp,
                                                                                cost_cod_dmov,        cost_cod_bode,      cost_cod_sucu,
                                                                                cost_cod_empr,        cost_num_prdo,      cost_cod_ejer,
                                                                                cost_fec_cost,        cost_can_cost,      cost_val_unit,
                                                                                cost_est_cost,        cost_tip_cost )
                                                                        values(($cost_cod_cost+1),    '$prod',            $serial_minv,
                                                                                 ($x),                 $bod,              $sucursal,
                                                                                 $idempresa,          $idprdo,            $idejer,
                                                                                 '$fecha_pedido',     ($cant_real),       $cost_act,
                                                                                 1,                   'E' ) ";
                        $oIfx->QueryT($sql_cost);
                    }
                    $aux++;
                }
                $sql_d .= ");";
                $oIfx->QueryT($sql_d);

                // -----------------------------------------------------------------------------------------
                // NUEVO PROCESO CALCULO COSTO PROMEDIO ADRIAN47
                // -----------------------------------------------------------------------------------------
                //actualizar_costo_promedio_ponderado_prod($oIfx, $idempresa, $sucursal, $bod, $prod);
                // -----------------------------------------------------------------------------------------
                // FIN NUEVO PROCESO CALCULO COSTO PROMEDIO ADRIAN47
                // -----------------------------------------------------------------------------------------

                $x++;
                $j++;
            } // fin foreach dmov                       

            $oIfx->QueryT('COMMIT WORK;');

            $oReturn->script("Swal.fire({
                position: 'center',
                type: 'success',
                title: 'Egreso Realizado Correctamente',
                showConfirmButton: true,
                confirmButtonText: 'Aceptar',
                timer: 1200000
            })");

            $oReturn->assign("nota_compra", "value", $secu_minv);
            $oReturn->assign("serial", "value", $serial_minv);

            $oReturn->script('vista_previa();');
        } catch (Exception $e) {
            // rollback
            $oIfx->QueryT('ROLLBACK WORK;');
            $oReturn->alert($e->getMessage());
            $oReturn->assign("ctrl", "value", 1);
        }
    } else {
        $oReturn->alert('!!!!....Por favor selecciona un producto....!!!!!');
        $oReturn->assign("ctrl", "value", 1);
    }

    return $oReturn;
}

/****************************************************************************/
/* DF01 :: G E N E R A    EL   S E C U E N C I A L   D E L    P E D I D O   */
/****************************************************************************/
function secuencial_pedido($op, $serie, $as_codigo_pedido, $ceros_sql)
{
    //string 
    $ls_codigo;
    $ceros;
    $ls_codigos;

    //integer 
    $li_codigo;
    $ceros1;
    $ll_numeros;
    $ll_codigo;

    if (isset($as_codigo_pedido) or $as_codigo_pedido == '') {
        $li_codigo = ($as_codigo_pedido);

        $li_codigo = 0;
    } else {
        $li_codigo = $as_codigo_pedido;
    }

    $li_codigo = $as_codigo_pedido;

    $li_codigo = $li_codigo + 1;
    $ll_numeros = strlen(($li_codigo));
    $ceros = cero_mas('0', $ceros_sql);
    $ceros1 = strlen($ceros);
    $ll_codigo = $ceros1 - $ll_numeros;

    switch ($op) {
        case 1:
            // secuencial user
            $ls_codigos = $serie . '-' . (cero_mas('0', $ll_codigo)) . ($li_codigo);
            break;
        case 2:
            // secuencial normal					
            $ls_codigos = (cero_mas('0', $ll_codigo)) . ($li_codigo);
            break;
    }

    return $ls_codigos;
}

function cero_mas($caracter, $num)
{
    if ($num > 0) {
        for ($i = 1; $i <= $num; $i++) {
            $arreglo[$i] = $caracter;
        }

        while (list($i, $Valor) = each($arreglo)) {
            $cadena .= $Valor;
        }
    } else {
        $cadena = '';
    }

    return $cadena;
}

// ENVIO DE CORREO
function envio_correo($correo, $correo2, $correo3, $pedido, $vendedor, $cliente, $observaciones, $detalle, $usuario)
{
    include("class.phpmailer.php");
    include("class.smtp.php");

    $mail = new PHPMailer();
    $mail->IsSMTP();

    $mail->Host = "mail.sisconti.com.ec";
    //	$mail->From = "ruben.santacruz@sisconti.com.ec";
    //        $mail->Host = "mail.andinanet.net";
    $mail->From = "sistemasalitecno@alitecno.com.ec";
    $mail->FromName = "Sistema Web Alitecno Cliente: $cliente";
    $mail->Subject = "Bienvenidos al Sistema Web Alitecno";
    $mail->AltBody = "Bienvenidos.....";
    $mail->MsgHTML("Hola, Se realizo el siguiente Pedido Web:<br><br><br>
                            Pedido: $pedido <br>                            
                            Vendedor: $vendedor <br><br>
                            Usuario: $usuario <br><br>
                            Cliente: $cliente <br><br>
                            Observaciones: $observaciones <br><br>
                            Detalle: $detalle <br><br><br>
                            Recibe un cordial saludo,<br>
			    El equipo WebMaster Alitecno.<br>");
    $mail->AddAddress($correo, "Ventas");
    $mail->AddAddress($correo2, "Bodega");
    $mail->AddAddress($correo3, "Bodega2");
    //        $mail->AddAddress('ruben.santacruz@sisconti.com.ec',"Bodega2");
    $mail->IsHTML(true);
    $mail->Send();
}

/*********************************************/
/*   M O S T R A R     D A T A    G R I D    */
/********************************************/
function agrega_modifica_grid($nTipo = 0, $descuento_general = 0, $codigo_prod = '', $aForm = '', $id = '', $cant_update = 0, $costo_update = 0, $iva_up = 0, $desc1_up = 0, $desc2_up = 0, $bode_up = 0, $cuen1 = '', $cuen2 = '', $cuen3 = '', $cuen4 = '', $detalle_update = '', $lote_update = '', $fecha_elaboracion_update = '', $fecha_caducidad_update = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oCnx = new Dbo();
    $oCnx->DSN = $DSN;
    $oCnx->Conectar();
    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();
    $fu = new Formulario;
    $fu->DSN = $DSN;
    $aDataGrid = $_SESSION['aDataGird'];
    $aLabelGrid = array(
        'Id',
        'Bodega',
        'Codigo Item',
        'Descripcion',
        'Unidad',
        'Cantidad',
        'Costo',
        'Iva',
        'Dscto 1',
        'Dscto 2',
        'Dscto Gral',
        'Total',
        'Total Con Iva',
        'Modificar',
        'Eliminar',
        'Cuenta',
        'Cuenta Iva',
        'Centro Costo',
        'Cuenta Gasto',
        'Detalle',
        'Lote/Serie',
        'Elaboracion',
        'Caduca'
    );
    $oReturn = new xajaxResponse();
    $idempresa  =  $aForm['empresa'];
    $idsucursal =  $aForm['sucursal'];
    $decimal = 6;

    // P R E C I O S     D E     C A DA      P R O D U C T O     D E     T A B LA
    // S A E P P R      C O N     L A     T A B L A      S A E P R O D
    $cantidad             = $aForm['cantidad'];
    $codigo_barra         = $aForm['codigo_barra'];
    $codigo_producto     = $aForm['codigo_producto'];
    $costo                 = $aForm['costo'];
    $iva                 = $aForm['iva'];
    $idbodega             =  $aForm['bodega'];
    $descuento             = $aForm['descuento'];
    $descuento_2         = $aForm['descuento_2'];
    $cuenta_inv         = $aForm['cuenta_inv'];
    $cuenta_iva         = $aForm['cuenta_iva'];
    $detalle            = $aForm['detalle'];
    $stock               = $aForm['stock'];

    $lote                 = $aForm['loteProd'];
    $serie                 = $aForm['serieProd'];
    $fcad                 = $aForm['fCadLoteProd'];
    $fela                 = $aForm['fElaLoteProd'];

    // ------------------------------------------------------------------------------------------------
    // CONSULTA EXISTE LOTE
    //-------------------------------------------------------------------------------------------------

    $existe_lote = 'N';
    if (!empty($lote) || !empty($serie)) {
        if (!empty($lote)) {
            $temp_lote_serie = $lote;
        }
        if (!empty($serie)) {
            $temp_lote_serie = $serie;
        }

        $id_user = $_SESSION['U_ID'];
        $fecha_ini = '2018-01-01';
        $fecha_fin = '2030-01-01';

        $sql = "delete from tmp_prod_lote_web where user_cod_web = $id_user";
        $oIfx->QueryT($sql);

        $sql_sp = "select * from sp_lotes_productos_web( $idempresa, $idsucursal, $idbodega, '$fecha_ini', '$fecha_fin', '$codigo_producto', '$codigo_producto', '2' , $id_user, '$temp_lote_serie') ";
        $oIfx->Query($sql_sp);
        // echo $sql_sp;

        $sql_lote_existe = "select  sum(cant_lote) as cant, num_lote,  MAX(fecha_ela_lote) as felab, MAX(fecha_cad_lote) as fcad, 
                                prod_cod_prod, prod_nom_prod, costo
                                from tmp_prod_lote_web where
                                user_cod_web  = $id_user and
                                bode_cod_bode = $idbodega and
                                empr_cod_empr = $idempresa and
                                sucu_cod_sucu = $idsucursal
                                group by 2, 5, 6, 7
                                having  sum(cant_lote) <> 0
                                order by fcad 
                                limit 800
                                ";

        $lote_consulta = consulta_string($sql_lote_existe, 'num_lote', $oIfx, '');
        $fecha_ela_consulta = consulta_string($sql_lote_existe, 'felab', $oIfx, '');
        $fecha_cad_consulta = consulta_string($sql_lote_existe, 'fcad', $oIfx, '');

        if (!empty($lote_consulta)) {
            $existe_lote = 'S';
        } else {
            $existe_lote = 'N';
        }
    }

    // ------------------------------------------------------------------------------------------------
    // FIN CONSULTA EXISTE LOTE
    //-------------------------------------------------------------------------------------------------





    if ($existe_lote == 'N' && $lote != '') {
        $oReturn->alert('El lote ingresado no existe');
    } else if ($existe_lote == 'N' && $serie != '') {
        $oReturn->alert('La serie ingresada no existe');
    } else {

        $oReturn->script('limpiar_prod();');


        if (!empty($serie)) {
            $lote = $serie;
            $fcad = '';
            $fela = '';
        }

        //$oReturn->alert($fela);

        if (!empty($fcad)) {
            //$fcad = fecha_mysql($fcad);
        }

        if (!empty($fela)) {
            //$fela = fecha_mysql($fela);
        }

        if (empty($descuento)) {
            $descuento = 0;
        }
        if (empty($descuento_2)) {
            $descuento_2 =  0;
        }





        if ($nTipo == 1) {
            //actualiza
            $cantidad = $cant_update;
            $codigo_producto = $codigo_prod;
            $costo = $costo_update;
            $iva = $iva_up;
            $idbodega = $bode_up;
            $descuento = $desc1_up;
            $descuento_2 = $desc2_up;
            $cuenta_inv = $cuen1;
            $cuenta_iva = $cuen2;
            $cuenta_iva = $cuen2;
            $cuenta_iva = $cuen2;
            $cuenta_iva = $cuen2;
            $detalle = $detalle_update;
            $lote = $lote_update;
            $fela = $fecha_elaboracion_update;
            $fcad = $fecha_caducidad_update;
        }

        // saeprod
        $sql = "select  p.prod_cod_prod,   pr.prbo_cod_unid, pr.prbo_iva_porc as prbo_iva_porc,
                    pr.prbo_ice_porc as prbo_ice_porc,
                    pr.prbo_dis_prod as stock, prod_cod_tpro,
                    prod_stock_neg
                    from saeprod p, saeprbo pr where
                    p.prod_cod_prod = pr.prbo_cod_prod and
                    p.prod_cod_empr = $idempresa and
                    p.prod_cod_sucu = $idsucursal and
                    pr.prbo_cod_empr = $idempresa and
                    pr.prbo_cod_bode = $idbodega and
                    p.prod_cod_prod = '$codigo_producto' ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $idproducto = $oIfx->f('prod_cod_prod');
                $idunidad     = $oIfx->f('prbo_cod_unid');
                $stock        = $oIfx->f('stock');
                $prod_stock_neg = $oIfx->f('prod_stock_neg');
            } else {
                $idproducto    = '';
                $idunidad    = '';
                $stock        = 0;
                $prod_stock_neg = 'N';
            }
        }
        $oIfx->Free();


        // CTA GASTO
        $tran_cod = $aForm['tran'];

        $sql = "select defi_cod_cuen from saedefi where
					defi_cod_empr = $idempresa and
					defi_cod_sucu = $idsucursal and
					defi_cod_tran = '$tran_cod' ";
        $cta_gasto = consulta_string_func($sql, 'defi_cod_cuen', $oIfx, '');


        // TOTAL
        $total_fac    = 0;
        $dsc1         = ($costo * $cantidad * $descuento) / 100;
        $dsc2         = ((($costo * $cantidad) - $dsc1) * $descuento_2) / 100;
        if ($descuento_general > 0) {
            // descto general
            $dsc3     = ((($costo * $cantidad) - $dsc1 - $dsc2) * $descuento_general) / 100;
            $total_fact_tmp = ((($costo * $cantidad) - ($dsc1 + $dsc2 + $dsc3)));
            $tmp     = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
        } else {
            // sin descuento general
            $total_fact_tmp = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
            $tmp     = $total_fact_tmp;
        }

        $total_fac = round($total_fact_tmp, 2);

        // total con iva
        if ($iva > 0) {
            $total_con_iva = round((($total_fac * $iva)  / 100), 2) + $total_fac;
        } else {
            $total_con_iva = $total_fac;
        }


        if ($cantidad <= $stock || $prod_stock_neg == 'S') {

            if ($nTipo == 0) {

                //GUARDA LOS DATOS DEL DETALLE
                $cont = count($aDataGrid);
                // cantidad
                $fu->AgregarCampoNumerico($cont . '_cantidad', 'Cantidad|LEFT', false, $cantidad, 80, 80);
                $fu->AgregarComandoAlCambiarValor($cont . '_cantidad', 'cargar_update_cant(\'' . $cont . '\');');

                // costo
                $fu->AgregarCampoNumerico($cont . '_costo', 'Costo|LEFT', false, $costo, 80, 80);
                $fu->AgregarComandoAlCambiarValor($cont . '_costo', 'cargar_update_cant(\'' . $cont . '\');');
                $fu->AgregarComandoAlPonerEnfoque($cont . '_costo', 'this.blur()');
                // iva
                $fu->AgregarCampoNumerico($cont . '_iva', 'Impuesto|LEFT', false, $iva, 40, 40);
                $fu->AgregarComandoAlCambiarValor($cont . '_iva', 'cargar_update_cant(\'' . $cont . '\');');

                // descto1
                $fu->AgregarCampoNumerico($cont . '_desc1', 'Descto1|LEFT', false, 0, 40, 40);
                $fu->AgregarComandoAlCambiarValor($cont . '_desc1', 'cargar_update_cant(\'' . $cont . '\');');

                // descto2
                $fu->AgregarCampoNumerico($cont . '_desc2', 'Descto2|LEFT', false, 0, 40, 40);
                $fu->AgregarComandoAlCambiarValor($cont . '_desc2', 'cargar_update_cant(\'' . $cont . '\');');

                // cuenta de gasto
                //$cta_gasto = $aForm['cuenta_gasto'];
                $html_cta = '';
                $fu->AgregarCampoTexto($cont . '_cta_gasto', 'Cuenta Gasto', false, $cta_gasto, 100, 100);
                $fu->AgregarComandoAlEscribir($cont . '_cta_gasto', 'cta_gasto_22(\'' . $cont . '_cta_gasto' . '\', event );');
                $html_cta = $fu->ObjetoHtml($cont . '_cta_gasto');


                // centro de costo
                $ccos = $aForm['ccosn'];
                $html_ccos = '';
                $fu->AgregarCampoTexto($cont . '_ccos', 'Centro Costo', false, $ccos, 100, 100);
                $fu->AgregarComandoAlEscribir($cont . '_ccos', 'centro_costo_22( \'' . $cont . '_ccos' . '\', event );');
                $html_ccos = $fu->ObjetoHtml($cont . '_ccos');

                $aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
                $aDataGrid[$cont][$aLabelGrid[1]] = $idbodega;
                $aDataGrid[$cont][$aLabelGrid[2]] = $idproducto;
                $aDataGrid[$cont][$aLabelGrid[3]] = $idproducto;
                $aDataGrid[$cont][$aLabelGrid[4]] = $idunidad;
                $aDataGrid[$cont][$aLabelGrid[5]] = $fu->ObjetoHtml($cont . '_cantidad');  //$cantidad;
                $aDataGrid[$cont][$aLabelGrid[6]] = $fu->ObjetoHtml($cont . '_costo'); //costo;
                $aDataGrid[$cont][$aLabelGrid[7]] = $fu->ObjetoHtml($cont . '_iva'); //iva                
                $aDataGrid[$cont][$aLabelGrid[8]] = $fu->ObjetoHtml($cont . '_desc1'); // desc1
                $aDataGrid[$cont][$aLabelGrid[9]] = $fu->ObjetoHtml($cont . '_desc2'); // dec2
                $aDataGrid[$cont][$aLabelGrid[10]] = $descuento_general;
                $aDataGrid[$cont][$aLabelGrid[11]] = $total_fac;
                $aDataGrid[$cont][$aLabelGrid[12]] = $total_con_iva;
                $aDataGrid[$cont][$aLabelGrid[13]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil3.png"
																								title = "Presione aqui para Modificar"
																								style="cursor: hand !important; cursor: pointer !important;"
																								onclick="agregar_detalle();"
																								alt="Modificar"
																								align="bottom" />';
                $aDataGrid[$cont][$aLabelGrid[14]] = '
                                                        <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
																								onMouseOver="drc(\'Presione aqui para Eliminar\', \'Eliminar\'); return true;"
																								onMouseOut="javascript:nd(); return true;"
																								title = "Presione aqui para Eliminar"
																								style="cursor: hand !important; cursor: pointer !important;"
																								onclick="javascript:elimina_detalle(' . $cont . ');"
																								alt="Eliminar"
																								align="bottom" />';
                $aDataGrid[$cont][$aLabelGrid[15]] = $cuenta_inv;
                $aDataGrid[$cont][$aLabelGrid[16]] = $cuenta_iva;
                $aDataGrid[$cont][$aLabelGrid[17]] = $html_ccos . ' <div id ="imagen1" class="btn btn-primary btn-sm" onclick="abrir_modal_distribucion(\'' . $cont . '\')">
                                                                        <span class="glyphicon glyphicon-list"></span>
                                                                    </div>
                                                                    ';
                $aDataGrid[$cont][$aLabelGrid[18]] = $html_cta;
                $aDataGrid[$cont][$aLabelGrid[19]] = $detalle;
                $aDataGrid[$cont][$aLabelGrid[20]] = $lote;
                $aDataGrid[$cont][$aLabelGrid[21]] = $fela;
                $aDataGrid[$cont][$aLabelGrid[22]] = $fcad;
            } elseif ($nTipo == 1) {
                //MODIFICA Y EXTRAE LOS DATOS DEL DATAGRID A LA VENTANA  DETALLE
                // cantidad
                $fu->AgregarCampoNumerico($id . '_cantidad', 'Cantidad|LEFT', false, $cantidad, 80, 80);
                $fu->AgregarComandoAlCambiarValor($id . '_cantidad', 'cargar_update_cant(\'' . $id . '\');');

                // costo
                $fu->AgregarCampoNumerico($id . '_costo', 'Costo|LEFT', false, $costo, 80, 80);
                $fu->AgregarComandoAlCambiarValor($id . '_costo', 'cargar_update_cant(\'' . $id . '\');');
                $fu->AgregarComandoAlPonerEnfoque($id . '_costo', 'this.blur()');
                // iva
                $fu->AgregarCampoNumerico($id . '_iva', 'Impuesto|LEFT', false, $iva, 40, 40);
                $fu->AgregarComandoAlCambiarValor($id . '_iva', 'cargar_update_cant(\'' . $id . '\');');

                // descto1
                $fu->AgregarCampoNumerico($id . '_desc1', 'Descto1|LEFT', false, $descuento, 40, 40);
                $fu->AgregarComandoAlCambiarValor($id . '_desc1', 'cargar_update_cant(\'' . $id . '\');');

                // descto2
                $fu->AgregarCampoNumerico($id . '_desc2', 'Descto2|LEFT', false, $descuento_2, 40, 40);
                $fu->AgregarComandoAlCambiarValor($id . '_desc2', 'cargar_update_cant(\'' . $id . '\');');

                // cuenta de gasto
                $cta_gasto = $aForm['cuenta_gasto'];
                $html_cta = '';
                $fu->AgregarCampoTexto($id . '_cta_gasto', 'Cuenta Gasto', false, $cuen4, 100, 100);
                $fu->AgregarComandoAlEscribir($id . '_cta_gasto', 'cta_gasto_22(\'' . $id . '_cta_gasto' . '\', event );');
                $html_cta = $fu->ObjetoHtml($id . '_cta_gasto');


                // centro de costo
                $ccos = $aForm['centro_costo'];
                $html_ccos = '';

                $fu->AgregarCampoTexto($id . '_ccos', 'Centro Costo', false, $cuen3, 100, 100);
                $fu->AgregarComandoAlEscribir($id . '_ccos', 'centro_costo_22( \'' . $id . '_ccos' . '\', event );');
                $html_ccos = $fu->ObjetoHtml($id . '_ccos');


                $aDataGrid[$id][$aLabelGrid[0]] = floatval($id);
                $aDataGrid[$id][$aLabelGrid[1]] = $idbodega;
                $aDataGrid[$id][$aLabelGrid[2]] = $idproducto;
                $aDataGrid[$id][$aLabelGrid[3]] = $idproducto;
                $aDataGrid[$id][$aLabelGrid[4]] = $idunidad;
                $aDataGrid[$id][$aLabelGrid[5]] = $fu->ObjetoHtml($id . '_cantidad');  //$cantidad;
                $aDataGrid[$id][$aLabelGrid[6]] = $fu->ObjetoHtml($id . '_costo'); //costo;
                $aDataGrid[$id][$aLabelGrid[7]] = $fu->ObjetoHtml($id . '_iva'); //iva
                $aDataGrid[$id][$aLabelGrid[8]] = $fu->ObjetoHtml($id . '_desc1'); // desc1
                $aDataGrid[$id][$aLabelGrid[9]] = $fu->ObjetoHtml($id . '_desc2'); // dec2
                $aDataGrid[$id][$aLabelGrid[10]] = $descuento_general;
                $aDataGrid[$id][$aLabelGrid[11]] = $total_fac;
                $aDataGrid[$id][$aLabelGrid[12]] = $total_con_iva;
                $aDataGrid[$id][$aLabelGrid[13]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil3.png"
																								title = "Presione aqui para Modificar"
																								style="cursor: hand !important; cursor: pointer !important;"
																								onclick="agregar_detalle(1);"
																								alt="Modificar"
																								align="bottom" />';
                $aDataGrid[$id][$aLabelGrid[14]] = '
                                                    <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
																								title = "Presione aqui para Eliminar"
																								style="cursor: hand !important; cursor: pointer !important;"
																								onclick="javascript:elimina_detalle(' . $id . ');"
																								alt="Eliminar"
																								align="bottom" />';
                $aDataGrid[$id][$aLabelGrid[15]] = $cuenta_inv;
                $aDataGrid[$id][$aLabelGrid[16]] = $cuenta_iva;
                $aDataGrid[$id][$aLabelGrid[17]] = $html_ccos . ' <div id ="imagen1" class="btn btn-primary btn-sm" onclick="abrir_modal_distribucion(\'' . $id . '\')">
                                                                        <span class="glyphicon glyphicon-list"></span>
                                                                    </div>
                                                                    ';
                $aDataGrid[$id][$aLabelGrid[18]] = $html_cta;
                $aDataGrid[$id][$aLabelGrid[19]] = $detalle;
                $aDataGrid[$id][$aLabelGrid[20]] = $lote;
                $aDataGrid[$id][$aLabelGrid[21]] = $fela;
                $aDataGrid[$id][$aLabelGrid[22]] = $fcad;
            }
            $_SESSION['aDataGird'] = $aDataGrid;
            $sHtml = mostrar_grid($idempresa);
            $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
            $oReturn->script('totales();');
            $oReturn->script('cerrar_ventana();');
        } else {
            $oReturn->alert('!!! No puede Egresar mas del Stock....');
        }
    }


    return $oReturn;
}

/// actualiza grid producto
function actualiza_grid($id, $aForm)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $aDataGrid = $_SESSION['aDataGird'];
    $oReturn = new xajaxResponse();

    // variables
    $cantidad   = $aForm[$id . '_cantidad'];
    $costo      = $aForm[$id . '_costo'];
    $iva        = $aForm[$id . '_iva'];
    $desc1      = $aForm[$id . '_desc1'];
    $desc2      = $aForm[$id . '_desc2'];
    $producto   = $aDataGrid[$id]['Codigo Item'];
    $bodega     = $aDataGrid[$id]['Bodega'];
    $cuenta_prod  = $aDataGrid[$id]['Cuenta'];
    $cuenta_iva   = $aDataGrid[$id]['Cuenta Iva'];
    $cta_gasto    = $aForm[$id . '_cta_gasto'];
    $centro_costo = $aForm[$id . '_ccos'];
    $detalle     = $aDataGrid[$id]['Detalle'];
    $lote     = $aDataGrid[$id]['Lote/Serie'];
    $fecha_elaboracion     = $aDataGrid[$id]['Elaboracion'];
    $fecha_caducidad     = $aDataGrid[$id]['Caduca'];

    $oReturn->script('cargar_update_grid(\'' . $id . '\', \'' . $producto . '\', \'' . $cantidad . '\' , \'' . $costo . '\', \'' . $iva . '\', \'' . $desc1 . '\', \'' . $desc2 . '\', \'' . $bodega . '\', \'' . $cuenta_prod . '\', \'' . $cuenta_iva . '\', \'' . $centro_costo . '\', \'' . $cta_gasto . '\' , \'' . $detalle . '\' , \'' . $lote . '\' , \'' . $fecha_elaboracion . '\' , \'' . $fecha_caducidad . '\'   )');
    return $oReturn;
}

function agrega_modifica_grid_update($descuento_general, $aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oReturn = new xajaxResponse();

    $aDataGrid = $_SESSION['aDataGird'];
    $oReturn = new xajaxResponse();

    $idempresa = $aForm['empresa'];
    $cont = count($aDataGrid);
    $matriz = array();
    unset($matriz);
    if ($cont > 0) {
        $j = 0;
        foreach ($aDataGrid as $aValues) {
            $aux = 0;
            $total_fact = 0;
            $i = 0;
            foreach ($aValues as $aVal) {
                if ($aux == 0) {                    //id
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 1) {              //bodega
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 2) {              //codigo
                    $matriz[$j][$i] = $aVal;
                    $prod = $aVal;
                    $i++;
                } elseif ($aux == 3) {              //codigo
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 4) {              //unidad
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 5) {              //cantidad
                    $cant = $aForm[$j . '_cantidad'];
                    $matriz[$j][$i] = $aVal;
                    $i++;
                    $oReturn->alert($aVal);
                } elseif ($aux == 6) {              //costo
                    $costo = $aForm[$j . '_costo'];
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 7) {              //iva
                    $iva = $aForm[$j . '_iva'];
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 8) {              //desc1
                    $desc1 = $aForm[$j . '_desc1'];
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 9) {              //dsc2
                    $desc2 = $aForm[$j . '_desc2'];
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 10) {             // desc general
                    $desc3 = $descuento_general;
                    $matriz[$j][$i] = $desc3;
                    $i++;
                } elseif ($aux == 11) {             // total
                    $descuento1 = ($costo * $cant * $desc1) / 100;
                    $descuento2 = ((($costo * $cant) - $descuento1) * $desc2) / 100;
                    $descuento3 = ((($costo * $cant) - $descuento1 - $descuento2) * $desc3) / 100;
                    $total_fact = round((($costo * $cant) - ($descuento1 + $descuento2 + $descuento3)), 2);
                    $matriz[$j][$i] = $total_fact;
                    $i++;
                } elseif ($aux == 12) {             // total iva
                    // total con iva
                    if ($iva > 0) {
                        $total_con_iva = round((($total_fact * $iva)  / 100), 2) + $total_fact;
                    } else {
                        $total_con_iva = $total_fact;
                    }
                    $matriz[$j][$i] = $total_con_iva;
                    $i++;
                } elseif ($aux == 13) {             // actualizar
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 14) {             // eliminar
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 15) {             // cuenta prod
                    $matriz[$j][$i] = $aVal;
                } elseif ($aux == 16) {             // cuenta iva
                    $matriz[$j][$i] = $aVal;
                } elseif ($aux == 17) {             // centro costo
                    $matriz[$j][$i] = $aVal;
                } elseif ($aux == 18) {             // cuenta gasto
                    $matriz[$j][$i] = $aVal;
                } elseif ($aux == 19) {             // detalle
                    $matriz[$j][$i] = $aVal;
                } elseif ($aux == 20) {             // lote
                    $matriz[$j][$i] = $aVal;
                } elseif ($aux == 21) {             // fela
                    $matriz[$j][$i] = $aVal;
                } elseif ($aux == 22) {             // fcad
                    $matriz[$j][$i] = $aVal;
                }
                $aux++;
            }
            $j++;
        }

        unset($_SESSION['aDataGird']);
        // generacion del grid actualizado
        $aDataGrid = $_SESSION['aDataGird'];
        $aLabelGrid = array(
            'Id',
            'Bodega',
            'Codigo Item',
            'Descripcion',
            'Unidad',
            'Cantidad',
            'Costo',
            'Iva',
            'Dscto 1',
            'Dscto 2',
            'Dscto Gral',
            'Total',
            'Total Con Iva',
            'Modificar',
            'Eliminar',
            'Cuenta',
            'Cuenta Iva',
            'Centro Costo',
            'Cuenta Gasto',
            'Detalle',
            'Lote/Serie',
            'Elaboracion',
            'Caduca'
        );

        for ($x = 0; $x <= ($j - 1); $x++) {
            for ($y = 0; $y <= $i; $y++) {
                $aDataGrid[$x][$aLabelGrid[$y]] = $matriz[$x][$y];
            }
        }

        $_SESSION['aDataGird'] = $aDataGrid;
        $sHtml = mostrar_grid($idempresa);
        $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
        $oReturn->script('totales();');
    }

    return $oReturn;
}

function mostrar_grid($idempresa)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oCnx = new Dbo();
    $oCnx->DSN = $DSN;
    $oCnx->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $aDataGrid = $_SESSION['aDataGird'];
    $aLabelGrid = array(
        'Id',
        'Bodega',
        'Codigo Item',
        'Descripcion',
        'Unidad',
        'Cantidad',
        'Costo',
        'Iva',
        'Dscto 1',
        'Dscto 2',
        'Dscto Gral',
        'Total',
        'Total Con Iva',
        'Modificar',
        'Eliminar',
        'Cuenta',
        'Cuenta Iva',
        'Centro Costo',
        'Cuenta Gasto',
        'Detalle',
        'Lote/Serie',
        'Elaboracion',
        'Caduca'
    );

    $cont = 0;
    foreach ($aDataGrid as $aValues) {
        $aux = 0;
        foreach ($aValues as $aVal) {
            if ($aux == 0)
                $aDatos[$cont][$aLabelGrid[$aux]] = $cont + 1;
            elseif ($aux == 1) {
                //bodega
                $sql = 'select bode_nom_bode from saebode where bode_cod_bode = ? and bode_cod_empr = ?';
                $data = array($aVal, $idempresa);
                if ($oIfx->Query($sql, $data))
                    $bodega = $oIfx->f('bode_nom_bode');
                $oIfx->Free();
                $aDatos[$cont][$aLabelGrid[$aux]] = $bodega;
            } elseif ($aux == 2) {
                $cod_prod = $aVal;
                $aDatos[$cont][$aLabelGrid[$aux]] = $cod_prod;
            } elseif ($aux == 3) {
                $sql = "select prod_nom_prod from saeprod where prod_cod_empr = $idempresa and prod_cod_prod = '$cod_prod' ";
                $data = array($cod_prod);
                if ($oIfx->Query($sql))
                    $producto = $oIfx->f('prod_nom_prod');
                $oIfx->Free();
                $aDatos[$cont][$aLabelGrid[$aux]] = $producto;
            } elseif ($aux == 4) {
                if (empty($aVal)) {
                    $aVal = 0;
                }
                $sql = 'select unid_sigl_unid from saeunid where unid_cod_empr = ' . $idempresa . ' and unid_cod_unid = ? ';
                $data = array($aVal);
                if ($oIfx->Query($sql, $data))
                    $unidad = $oIfx->f('unid_sigl_unid');
                $oIfx->Free();
                $aDatos[$cont][$aLabelGrid[$aux]] = $unidad;
            } elseif ($aux == 5) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $aVal . '</div>';
            } elseif ($aux == 6) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $aVal . '</div>';
            } elseif ($aux == 7) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $aVal . '</div>';
            } elseif ($aux == 8) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $aVal . '</div>';
            } elseif ($aux == 9) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $aVal . '</div>';
            } elseif ($aux == 10) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $aVal . '</div>';
            } elseif ($aux == 11) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">
                                                                    <span class="fecha_letra">' . $aVal . '</span>
                                                                   </div>';
            } elseif ($aux == 12) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">
                                                                    <span class="fecha_letra">' . $aVal . '</span>
                                                                   </div>';
            } elseif ($aux == 13) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="center">
                                                                        <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil3.png"
                                                                        title = "Presione aqui para Modificar"
                                                                        style="cursor: hand !important; cursor: pointer !important;"
                                                                        onclick="agregar_detalle(1,' . $cont . ');"
                                                                        alt="Modificar"
                                                                        align="bottom" />
                                                                  </div>';
            } elseif ($aux == 14) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '
                                                    <div align="center">
                                                                        <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                                                                        title = "Presione aqui para Eliminar"
                                                                        style="cursor: hand !important; cursor: pointer !important;"
                                                                        onclick="javascript:elimina_detalle(' . $cont . ');"
                                                                        alt="Eliminar"
                                                                        align="bottom" />
                                                                    </div>';
            } elseif ($aux == 15) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $aVal . '</div>';
            } elseif ($aux == 16) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $aVal . '</div>';
            } elseif ($aux == 17) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $aVal . '</div>';
            } elseif ($aux == 18) {
                $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $aVal . '</div>';
            } else
                $aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
            $aux++;
        }
        $cont++;
    }
    return genera_grid($aDatos, $aLabelGrid, 'Lista de Productos', 98);
}

function cancelar_pedido()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $aDataGrid = $_SESSION['aDataGird'];
    $aDataPrueba = $_SESSION['aDataPrueba'];
    unset($_SESSION['aDataGird']);
    unset($_SESSION['aDataPrueba']);
    $sScript = "xajax_genera_formulario_pedido();";
    $oReturn = new xajaxResponse();
    $oReturn->clear("divFormularioDetalle", "innerHTML");
    $oReturn->script($sScript);
    return $oReturn;
}

function elimina_detalle($id = null, $aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    $aLabelGrid = array(
        'Id',
        'Bodega',
        'Codigo Item',
        'Descripcion',
        'Unidad',
        'Cantidad',
        'Costo',
        'Iva',
        'Dscto 1',
        'Dscto 2',
        'Dscto Gral',
        'Total',
        'Total Con Iva',
        'Modificar',
        'Eliminar',
        'Cuenta',
        'Cuenta Iva',
        'Centro Costo',
        'Cuenta Gasto',
        'Detalle',
        'Lote/Serie',
        'Elaboracion',
        'Caduca'
    );

    $aDataGrid = $_SESSION['aDataGird'];
    $contador  = count($aDataGrid);
    $idempresa = $aForm['empresa'];

    //$oReturn->alert('ru '.$idempresa);

    if ($contador > 1) {
        unset($aDataGrid[$id]);
        $aDataGrid = array_values($aDataGrid);
        $cont = 0;
        foreach ($aDataGrid as $aValues) {
            $aux = 0;
            foreach ($aValues as $aVal) {
                if ($aux == 0)
                    $aDatos[$cont][$aLabelGrid[$aux]] = $cont;
                elseif ($aux == 5) {
                    if ($cont < $id) {
                        $ifu->AgregarCampoNumerico($cont . '_cantidad', 'Cantidad|LEFT', false, $aForm[$cont . '_cantidad'], 40, 40);
                        $ifu->AgregarComandoAlCambiarValor($cont . '_cantidad', 'cargar_update_cant(\'' . $cont . '\');');
                    } else {
                        $aux_ = $cont + 1;
                        $ifu->AgregarCampoNumerico($cont . '_cantidad', 'Cantidad|LEFT', false, $aForm[$aux_ . '_cantidad'], 40, 40);
                        $ifu->AgregarComandoAlCambiarValor($cont . '_cantidad', 'cargar_update_cant(\'' . $cont . '\');');
                    }
                    $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $ifu->ObjetoHtml($cont . '_cantidad') . '</div>';
                } elseif ($aux == 6) {
                    if ($cont < $id) {
                        $ifu->AgregarCampoNumerico($cont . '_costo', '_costo', false, $aForm[$cont . '_costo'], 40, 60);
                        $ifu->AgregarComandoAlCambiarValor($cont . '_costo', 'cargar_update_cant(\'' . $cont . '\');');
                        $ifu->AgregarComandoAlPonerEnfoque($cont . '_costo', 'this.blur()');
                    } else {
                        $aux_ = $cont + 1;
                        $ifu->AgregarCampoNumerico($cont . '_costo', '_costo', false, $aForm[$aux_ . '_costo'], 40, 60);
                        if ($usua_cam_pcio == 'S') {
                            $ifu->AgregarComandoAlCambiarValor($cont . '_costo', 'cargar_update_cant(\'' . $cont . '\');');
                        } else {
                            $ifu->AgregarComandoAlPonerEnfoque($cont . '_costo', 'this.blur()');
                        }
                        $ifu->AgregarComandoAlPonerEnfoque($cont . '_costo', 'this.blur()');
                    }
                    $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $ifu->ObjetoHtml($cont . '_costo') . '</div>';
                } elseif ($aux == 7) {
                    if ($cont < $id) {
                        $ifu->AgregarCampoNumerico($cont . '_iva', 'iva', false, $aForm[$cont . '_iva'], 40, 40);
                        if ($usua_cam_ivaf == 'S') {
                            $ifu->AgregarComandoAlCambiarValor($cont . '_iva', 'cargar_update_cant(\'' . $cont . '\');');
                        } else {
                            $ifu->AgregarComandoAlPonerEnfoque($cont . '_iva', 'this.blur()');
                        }
                    } else {
                        $aux_ = $cont + 1;
                        $ifu->AgregarCampoNumerico($cont . '_iva', 'Iva', false, $aForm[$aux_ . '_iva'], 40, 40);
                        if ($usua_cam_ivaf == 'S') {
                            $ifu->AgregarComandoAlCambiarValor($cont . '_iva', 'cargar_update_cant(\'' . $cont . '\');');
                        } else {
                            $ifu->AgregarComandoAlPonerEnfoque($cont . '_iva', 'this.blur()');
                        }
                    }
                    $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $ifu->ObjetoHtml($cont . '_iva') . '</div>';
                } elseif ($aux == 8) {
                    if ($cont < $id) {
                        $ifu->AgregarCampoNumerico($cont . '_desc1', 'descuento', false, $aForm[$cont . '_desc1'], 40, 40);
                        if ($usua_cam_dscde == 'S') {
                            $ifu->AgregarComandoAlCambiarValor($cont . '_desc1', 'cargar_update_cant(\'' . $cont . '\');');
                        } else {
                            $ifu->AgregarComandoAlPonerEnfoque($cont . '_desc1', 'this.blur()');
                        }
                    } else {
                        $aux_ = $cont + 1;
                        $ifu->AgregarCampoNumerico($cont . '_desc1', 'descuento', false, $aForm[$aux_ . '_desc1'], 40, 40);
                        if ($usua_cam_dscde == 'S') {
                            $ifu->AgregarComandoAlCambiarValor($cont . '_desc1', 'cargar_update_cant(\'' . $cont . '\');');
                        } else {
                            $ifu->AgregarComandoAlPonerEnfoque($cont . '_desc1', 'this.blur()');
                        }
                    }
                    $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $ifu->ObjetoHtml($cont . '_desc1') . '</div>';
                } elseif ($aux == 9) {
                    if ($cont < $id) {
                        $ifu->AgregarCampoNumerico($cont . '_desc2', 'descuento', false, $aForm[$cont . '_desc2'], 40, 40);
                        if ($usua_cam_dscde == 'S') {
                            $ifu->AgregarComandoAlCambiarValor($cont . '_desc2', 'cargar_update_cant(\'' . $cont . '\');');
                        } else {
                            $ifu->AgregarComandoAlPonerEnfoque($cont . '_desc2', 'this.blur()');
                        }
                    } else {
                        $aux_ = $cont + 1;
                        $ifu->AgregarCampoNumerico($cont . '_desc2', 'descuento', false, $aForm[$aux_ . '_desc2'], 40, 40);
                        if ($usua_cam_dscde == 'S') {
                            $ifu->AgregarComandoAlCambiarValor($cont . '_desc2', 'cargar_update_cant(\'' . $cont . '\');');
                        } else {
                            $ifu->AgregarComandoAlPonerEnfoque($cont . '_desc2', 'this.blur()');
                        }
                    }
                    $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $ifu->ObjetoHtml($cont . '_desc2') . '</div>';
                } elseif ($aux == 17) {
                    if ($cont < $id) {
                        $ifu->AgregarCampoNumerico($cont . '_ccos', 'Centro Costo', false, $aForm[$cont . '_ccos'], 100, 100);
                        $ifu->AgregarComandoAlEscribir($cont . '_ccos', 'centro_costo_22(\'' . $cont . '_ccos' . '\', event );');
                    } else {
                        $aux_ = $cont + 1;
                        $ifu->AgregarCampoNumerico($cont . '_ccos', 'descuento', false, $aForm[$aux_ . '_ccos'], 100, 100);
                        $ifu->AgregarComandoAlEscribir($cont . '_ccos', 'centro_costo_22( \'' . $cont . '_ccos' . '\', event );');
                    }
                    $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $ifu->ObjetoHtml($cont . '_ccos') . '<div id ="imagen1" class="btn btn-primary btn-sm" onclick="abrir_modal_distribucion(\'' . $cont . '\')">
                                                                                                                            <span class="glyphicon glyphicon-list"></span>
                                                                                                                        </div>
                                                        </div>';
                } elseif ($aux == 18) {
                    if ($cont < $id) {
                        $ifu->AgregarCampoNumerico($cont . '_cta_gasto', 'Cuen gasto', false, $aForm[$cont . '_cta_gasto'], 100, 100);
                        $ifu->AgregarComandoAlEscribir($cont . '_cta_gasto', 'cta_gasto_22(\'' . $cont . '_cta_gasto' . '\', event );');
                    } else {
                        $aux_ = $cont + 1;
                        $ifu->AgregarCampoNumerico($cont . '_cta_gasto', 'descuento', false, $aForm[$aux_ . '_cta_gasto'], 100, 100);
                        $ifu->AgregarComandoAlEscribir($cont . '_cta_gasto', 'cta_gasto_22(\'' . $cont . '_cta_gasto' . '\', event );');
                    }
                    $aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $ifu->ObjetoHtml($cont . '_cta_gasto') . '</div>';
                } elseif ($aux == 14)
                    $aDatos[$cont][$aLabelGrid[$aux]] = '
                                                        <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
														title = "Presione aqui para Eliminar"
														style="cursor: hand !important; cursor: pointer !important;"
														onclick="javascript:elimina_detalle(' . $cont . ' );"
														alt="Eliminar"
														align="bottom"/>';
                else
                    $aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
                $aux++;
            }
            $cont++;
        }

        $_SESSION['aDataGird'] = $aDatos;

        $sHtml = mostrar_grid($idempresa);
        $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
        $oReturn->script('totales();');
    } else {
        unset($aDataGrid[0]);
        $_SESSION['aDataGird'] = $aDatos;
        $sHtml = "";
        $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
        $oReturn->script('totales();');
    }

    return $oReturn;
}

function cargar_grid($descuento_general, $aForm = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    $aDataGrid = $_SESSION['aDataGird'];
    $oReturn = new xajaxResponse();
    $empresa =  $aForm['empresa'];

    $cont = count($aDataGrid);
    $matriz = array();
    unset($matriz);
    if ($cont > 0) {
        $j = 0;
        foreach ($aDataGrid as $aValues) {
            $aux = 0;
            $total_fact = 0;
            $i = 0;
            foreach ($aValues as $aVal) {
                if ($aux == 0) {                    //id
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 1) {              //bodega
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 2) {              //codigo
                    $matriz[$j][$i] = $aVal;
                    $prod = $aVal;
                    $i++;
                } elseif ($aux == 3) {              //codigo
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 4) {              //unidad
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 5) {              //cantidad
                    $cant = $aForm[$j . '_cantidad'];
                    $fu->AgregarCampoNumerico($j . '_cantidad', 'Cantidad|LEFT', false, $cant, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($j . '_cantidad', 'cargar_update_cant(\'' . $j . '\');');
                    $matriz[$j][$i] = $fu->ObjetoHtml($j . '_cantidad');
                    $i++;
                } elseif ($aux == 6) {              //costo
                    $costo = $aForm[$j . '_costo'];
                    // costo
                    $fu->AgregarCampoNumerico($j . '_costo', 'Costo|LEFT', false, $costo, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($j . '_costo', 'cargar_update_cant(\'' . $j . '\');');
                    $matriz[$j][$i] = $fu->ObjetoHtml($j . '_costo');
                    $i++;
                } elseif ($aux == 7) {              //iva
                    $iva = $aForm[$j . '_iva'];
                    // iva
                    $fu->AgregarCampoNumerico($j . '_iva', 'Impuesto|LEFT', false, $iva, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($j . '_iva', 'cargar_update_cant(\'' . $j . '\');');
                    $matriz[$j][$i] = $fu->ObjetoHtml($j . '_iva');
                    $i++;
                } elseif ($aux == 8) {              //desc1
                    $desc1 = $aForm[$j . '_desc1'];
                    // descto1
                    $fu->AgregarCampoNumerico($j . '_desc1', 'Descto1|LEFT', false, $desc1, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($j . '_desc1', 'cargar_update_cant(\'' . $j . '\');');
                    $matriz[$j][$i] = $fu->ObjetoHtml($j . '_desc1');
                    $i++;
                } elseif ($aux == 9) {              //dsc2
                    $desc2 = $aForm[$j . '_desc2'];
                    // descto2
                    $fu->AgregarCampoNumerico($j . '_desc2', 'Descto2|LEFT', false, $desc2, 40, 40);
                    $fu->AgregarComandoAlCambiarValor($j . '_desc2', 'cargar_update_cant(\'' . $j . '\');');
                    $matriz[$j][$i] = $fu->ObjetoHtml($j . '_desc2');
                    $i++;
                } elseif ($aux == 10) {             // desc general
                    $desc3 = $descuento_general;
                    $matriz[$j][$i] = $desc3;
                    $i++;
                } elseif ($aux == 11) {             // total
                    $descuento1 = ($costo * $cant * $desc1) / 100;
                    $descuento2 = ((($costo * $cant) - $descuento1) * $desc2) / 100;
                    $descuento3 = ((($costo * $cant) - $descuento1 - $descuento2) * $desc3) / 100;
                    $total_fact = round((($costo * $cant) - ($descuento1 + $descuento2 + $descuento3)), 2);
                    $matriz[$j][$i] = $total_fact;
                    $i++;
                } elseif ($aux == 12) {             // total iva
                    // total con iva
                    if ($iva > 0) {
                        $total_con_iva = round((($total_fact * $iva)  / 100), 2) + $total_fact;
                    } else {
                        $total_con_iva = $total_fact;
                    }
                    $matriz[$j][$i] = $total_con_iva;
                    $i++;
                } elseif ($aux == 13) {             // actualizar
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 14) {             // eliminar
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 15) {             // cuenta prod
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 16) {             // cuenta iva
                    $matriz[$j][$i] = $aVal;
                    $i++;
                } elseif ($aux == 17) {             // centro de costo
                    $ccos = $aForm[$j . '_ccos'];
                    // centro de costo
                    $fu->AgregarCampoNumerico($j . '_ccos', 'Centro de Costo|LEFT', false, $ccos, 100, 100);
                    $fu->AgregarComandoAlEscribir($j . '_ccos', 'centro_costo_22(\'' . $j . '_ccos' . '\', event );');
                    $matriz[$j][$i] = $fu->ObjetoHtml($j . '_ccos') . ' <div id ="imagen1" class="btn btn-primary btn-sm" onclick="abrir_modal_distribucion(\'' . $j . '\')">
                                                                            <span class="glyphicon glyphicon-list"></span>
                                                                        </div>
                                                                        ';
                    $i++;
                } elseif ($aux == 18) {             // cuenta gasto
                    $cta = $aForm[$j . '_cta_gasto'];
                    // centro de costo
                    $fu->AgregarCampoNumerico($j . '_cta_gasto', 'Cuenta de Costo|LEFT', false, $cta, 100, 100);
                    $fu->AgregarComandoAlEscribir($j . '_cta_gasto', 'cta_gasto_22(\'' . $j . '_cta_gasto' . '\', event );');
                    $matriz[$j][$i] = $fu->ObjetoHtml($j . '_cta_gasto');
                    $i++;
                } elseif ($aux == 19) {             // detalle
                    $matriz[$j][$i] = $aVal;
                    $i++;
                }
                $aux++;
            }
            $j++;
        }

        unset($_SESSION['aDataGird']);
        // generacion del grid actualizado
        $aDataGrid = $_SESSION['aDataGird'];
        $aLabelGrid = array(
            'Id',
            'Bodega',
            'Codigo Item',
            'Descripcion',
            'Unidad',
            'Cantidad',
            'Costo',
            'Iva',
            'Dscto 1',
            'Dscto 2',
            'Dscto Gral',
            'Total',
            'Total Con Iva',
            'Modificar',
            'Eliminar',
            'Cuenta',
            'Cuenta Iva',
            'Centro Costo',
            'Cuenta Gasto',
            'Detalle'
        );

        for ($x = 0; $x <= ($j - 1); $x++) {
            for ($y = 0; $y <= $i; $y++) {
                $aDataGrid[$x][$aLabelGrid[$y]] = $matriz[$x][$y];
            }
        }

        $_SESSION['aDataGird'] = $aDataGrid;
        $sHtml = mostrar_grid($empresa);
        $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
        $oReturn->script('totales();');
    }

    return $oReturn;
}


/************************************************************************/
/* T O T A L       D E L      P A G O       D E L       P E D I D O     */
/************************************************************************/
function total_grid($descuento_general_tmp, $flete_tmp, $otro_tmp, $anticipo_tmp, $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    $idempresa =  $_SESSION['U_EMPRESA'];
    $usuario_informix =  $_SESSION['U_USER_INFORMIX'];
    $aDataGrid = $_SESSION['aDataGird'];
    $contdata = count($aDataGrid);
    $sucursal  = $aForm['sucursal'];
    $cod_prove = $aForm['cliente'];
    $cod_tran  = $aForm['tran'];
    $contri    = $aForm['contri_prove'];
    $array_otros = $_SESSION['U_OTROS'];

    if ($contdata > 0) {

        $total_iva = 0;
        $total_sin_iva = 0;
        $pedf_iva = 0;
        $total = 0;
        $con_iva = 0;
        $sin_iva = 0;
        $x = 0;
        foreach ($aDataGrid as $aValues) {
            $aux = 0;
            foreach ($aValues as $aVal) {
                if ($aux == 5) {                        //CANTIDAD
                    $var_cant = $x . '_cantidad';
                    $cant = $aForm[$var_cant];
                } elseif ($aux == 6) {                    //COSTO
                    $var_cos = $x . '_costo';
                    $costo = $aForm[$var_cos];
                } elseif ($aux == 7) {                    //IVA
                    $var_iva = $x . '_iva';
                    $iva = $aForm[$var_iva];
                } elseif ($aux == 8) {                    //DESCUENTO 1
                    $var_desc1 = $x . '_desc1';
                    $descuento_1 = $aForm[$var_desc1];
                } elseif ($aux == 9) {                    //DESCUENTO 2
                    $var_desc2 = $x . '_desc2';
                    $descuento_2 = $aForm[$var_desc2];
                } elseif ($aux == 10) {                                     //DESCUENTO GENERAL
                    $descuento_3 = $aVal;
                    $dsc1 = ($costo * $cant * $descuento_1) / 100;
                    $dsc2 = ((($costo * $cant) - $dsc1) * $descuento_2) / 100;
                    if ($descuento_3 > 0) {
                        // descto general
                        $dsc3 = ((($costo * $cant) - $dsc1 - $dsc2) * $descuento_3) / 100;
                        $total_fact_tmp = ((($costo * $cant) - ($dsc1 + $dsc2 + $dsc3)));
                        $tmp = ((($costo * $cant) - ($dsc1 + $dsc2)));
                    } else {
                        // sin descuento general
                        $total_fact_tmp = ((($costo * $cant) - ($dsc1 + $dsc2)));
                        $tmp = $total_fact_tmp;
                    }

                    $subtotal += round($tmp, 2);
                    $total_fac += round($total_fact_tmp, 2);
                    if ($iva == 12) {
                        //                                        $total_iva += round(((($total_fact_tmp*$iva)/100)),2);
                        $total_iva += (($total_fact_tmp * $iva) / 100);
                        $con_iva += round($total_fact_tmp, 2);
                    } else {
                        $sin_iva += round($total_fact_tmp, 2);
                    }
                }
                $aux++;
            }
            $x++;
        }

        //descuento general por usuario
        $sql_desc = "select usua_por_boni
                                from saeusua where
                                usua_cod_usua = $usuario_informix ";
        $desc_general = consulta_string($sql_desc, 'usua_por_boni', $oIfx, 0);

        // form total
        $fu->AgregarCampoNumerico('descuento_general', 'Descuento General|left', false, 0, 70, 2);
        $fu->AgregarComandoAlCambiarValor('descuento_general', 'cargar_descuento(' . $desc_general . ', ' . $total_fac . ', ' . $total_iva . ' )');
        $fu->AgregarCampoNumerico('descuento_valor', 'Descuento General Valor|left', false, 0, 70, 10);
        $fu->AgregarComandoAlPonerEnfoque('descuento_valor', 'this.blur()');

        $fu->AgregarCampoNumerico('anticipo', 'Anticipo|left', false, 0, 70, 10);
        $fu->AgregarCampoNumerico('iva_total', 'Impuesto|left', false, 0, 70, 10);
        $fu->AgregarComandoAlPonerEnfoque('iva', 'this.blur()');
        $fu->AgregarCampoNumerico('total_fac', 'Total|left', false, 0, 70, 10);
        $fu->AgregarComandoAlPonerEnfoque('total_fac', 'this.blur()');
        $fu->AgregarCampoNumerico('total_fac', 'Total|left', false, 0, 70, 10);
        $fu->AgregarComandoAlPonerEnfoque('total_fac', 'this.blur()');
        $fu->AgregarCampoNumerico('con_iva', 'Monto con Impuesto|left', false, 0, 70, 10);
        $fu->AgregarComandoAlPonerEnfoque('con_iva', 'this.blur()');
        $fu->AgregarCampoNumerico('sin_iva', 'Monto sin Impuesto|left', false, 0, 70, 10);
        $fu->AgregarComandoAlPonerEnfoque('sin_iva', 'this.blur()');

        // OTROS
        if (count($array_otros) > 0) {
            $html_txt = '';
            $txt = '';
            $total_otros = 0;
            foreach ($array_otros as $val) {
                $id_otro  = $val[0];
                $det_otro = $val[1];
                $txt = $id_otro . '_OTRO';
                $val_txt = $aForm[$txt];
                if (empty($val_txt)) {
                    $val_txt = 0;
                }
                $fu->AgregarCampoNumerico($txt, $det_otro . '|left', false, $val_txt, 70, 10);
                $fu->AgregarComandoAlCambiarValor($txt, 'totales( )');
                $html_txt .= '<table cellspacing="2" width="100%" border="0">
                                            <tr>
                                                <td  bgcolor="#EBEBEB" class="fecha_grande">' . $fu->ObjetoHtmlLBL($txt) . '</td>
                                                <td  bgcolor="#EBEBEB" class="fecha_grande" align="right">' . $fu->ObjetoHtml($txt) . '</td>
                                            </tr>
                                     </table>';
                $total_otros += $val_txt;
            } // fin foreach
        } // fin otros

        $fu->cCampos["descuento_general"]->xValor = $descuento_general_tmp;
        $fu->cCampos["descuento_valor"]->xValor = round(($subtotal * $descuento_general_tmp / 100), 2);
        $fu->cCampos["anticipo"]->xValor = $anticipo_tmp;
        $fu->cCampos["total_fac"]->xValor = round($subtotal, 2);
        $fu->cCampos["con_iva"]->xValor = round($con_iva, 2);
        $fu->cCampos["sin_iva"]->xValor = round($sin_iva, 2);
        $total_fac_total = round((round($subtotal, 2) - round(($subtotal * $descuento_general_tmp / 100), 2)), 2);
        $fu->cCampos["iva_total"]->xValor = round($total_iva, 2);

        // $sHtml .='<fieldset style="border:#FFFFFF 1px solid; padding:2px; text-align:center; width:98%;">';
        $sHtml .= '<table class="table table-bordered table-hover" align="right" cellpadding="0" cellspacing="2" width="30%" border="0">
                            <tr>
                                            <td  class="iniciativa"   height="25">MONTO CON IVA:</td>
                                            <td   class="fecha_grande" align="right">$</td>
                                            <td   class="fecha_grande" align="right">' . $fu->ObjetoHtml('con_iva') . '</td>
                                            <td   class="fecha_grande" width="1%" ></td>
                                            <td  class="iniciativa"   height="25">DESCUENTO GRAL:</td>
                                            <td   class="fecha_grande" align="right"></td>
                                            <td   class="fecha_grande" align="right">' . $fu->ObjetoHtml('descuento_general') . '</td>
                                            <td   class="fecha_grande" align="right">%</td>
                                            <td   class="fecha_grande" width="5%" ></td>
                                            <td  class="iniciativa"   height="25">SUMAN:</td>
                                            <td   class="fecha_grande" align="right">$</td>
                                            <td   class="fecha_grande" align="right">' . $fu->ObjetoHtml('total_fac') . '</td>
                                            <td   class="fecha_grande" align="right"></td>
                            </tr>
                            <tr>
                                            <td  class="iniciativa"   height="25">MONTO SIN IVA:</td>
                                            <td   class="fecha_grande" align="right">$</td>
                                            <td   class="fecha_grande" align="right">' . $fu->ObjetoHtml('sin_iva') . '</td>
                                            <td   class="fecha_grande" width="1%" ></td>

                                            <td  class="iniciativa"   height="25">DSCTO GRAL (VALOR):</td>
                                            <td   class="fecha_grande" align="right">$</td>
                                            <td   class="fecha_grande" align="right">' . $fu->ObjetoHtml('descuento_valor') . '</td>
                                            <td   class="fecha_grande" align="right"></td>

                                            <td   class="fecha_grande" width="5%"></td>

                                            <td  class="iniciativa"   height="25">DSCTO GRAL (VALOR):</td>
                                            <td   class="fecha_grande" align="right">$</td>
                                            <td   class="fecha_grande" align="right">' . round(($subtotal * $descuento_general_tmp / 100), 2) . '</td>
                                            <td   class="fecha_grande" align="right"></td>
                            </tr>
                            <tr>
                                            <td  class="iniciativa"   height="25"></td>
                                            <td   class="fecha_grande" align="right"></td>
                                            <td   class="fecha_grande" align="right"></td>
                                            <td   class="fecha_grande" width="1%" ></td>

                                            <td  class="iniciativa"   colspan="3" rowspan="4" valign="top" >' . $html_txt . '</td>

                                            <td   class="fecha_grande" align="right"></td>
                                            <td   class="fecha_grande" width="5%"></td>
                                            <td  class="iniciativa"   height="25">SUBTOTAL:</td>
                                            <td   class="fecha_grande" align="right">$</td>
                                            <td   class="fecha_grande" align="right">' . $total_fac_total . '</td>
                                            <td   class="fecha_grande" align="right"></td>
                            </tr>
                            <tr>
                                            <td  class="iniciativa"   height="25"></td>
                                            <td   class="fecha_grande" align="right"></td>
                                            <td   class="fecha_grande" align="right"></td>
                                            <td   class="fecha_grande" width="1%" ></td>
                                            <td   class="fecha_grande"></td>
                                            <td   class="fecha_grande"></td>

                                            <td  class="iniciativa"   height="25">IVA:</td>
                                            <td   class="fecha_grande" align="right">$</td>
                                            <td   class="fecha_grande" align="right">' . $fu->ObjetoHtml('iva_total') . '</td>
                                            <td   class="fecha_grande" align="right"></td>
                            </tr>
                            <tr>
                                            <td  class="iniciativa"   height="25"></td>
                                            <td   class="fecha_grande" align="right"></td>
                                            <td   class="fecha_grande" align="right"></td>
                                            <td   class="fecha_grande" width="1%"></td>
                                            <td   class="fecha_grande"></td>
                                            <td   class="fecha_grande"></td>
                                            <td  class="iniciativa"   height="25">OTROS VALORES:</td>
                                            <td   class="fecha_grande" align="right">$</td>
                                            <td   class="fecha_grande" align="right">' . round($total_otros, 2) . '</td>
                                            <td   class="fecha_grande" align="right"></td>
                            </tr>
                            <tr>
                                            <td   class="fecha_grande"></td>
                                            <td  class="iniciativa"   height="25"></td>
                                            <td   class="fecha_grande" align="right"></td>
                                            <td   class="fecha_grande" align="right" width="1%"></td>
                                            <td   class="fecha_grande" align="right"></td>
                                            <td   class="fecha_grande"></td>
                                            <td class="total_fact"   height="25">TOTAL:</td>
                                            <td   class="total_fact" align="right">$</td>
                                            <td   class="total_fact" align="right">' . round(($total_fac_total  + $total_iva + $total_otros), 2) . '</td>
                                            <td   class="total_fact" align="right"></td>
                            </tr>';
        $sHtml .= '</table>';
    } else {
        $sHtml = "";
    }

    $oReturn->assign("divTotal", "innerHTML", $sHtml);
    $oReturn->assign("total_fact_fp", "value", round(($total_fac  + $total_iva + $total_otros + $ice_total), 2));
    $oReturn->assign("valor", "value", round(($total_fac  + $total_iva + $total_otros + $ice_total), 2));
    return $oReturn;
}

function num_digito($op, $id, $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    //Definiciones
    $oReturn = new xajaxResponse();
    $idempresa =  $aForm['empresa'];

    // VARIABLES
    $form = $aForm[$id];
    $tran = $aForm['tran'];
    $cliente = $aForm['cliente'];
    $sql  = "select  pccp_num_digi from saepccp where
                    pccp_cod_empr = $idempresa ";
    $num_digito = consulta_string($sql, 'pccp_num_digi', $oIfx, 9);
    $len = strlen($form);
    $ceros = cero_mas('0', abs($num_digito - $len));
    $valor = $ceros . $form;

    // CONTROL SI EXISTE ESA FACTURA
    $anio           = substr($aForm['fecha_pedido'], 0, 4);
    $idprdo         = (substr($aForm['fecha_pedido'], 5, 2)) * 1;
    $fecha_ejer     = $anio . '-12-31';
    $sql = "select ejer_cod_ejer from saeejer where ejer_fec_finl = '$fecha_ejer' and ejer_cod_empr = $idempresa ";
    $idejer = consulta_string($sql, 'ejer_cod_ejer', $oIfx, 1);

    $sql = "select count(*) as cont from saeminv where
                    minv_cod_tran   = '$tran' and
                    minv_cod_clpv   = $cliente  and
                    minv_cod_empr   = $idempresa and
                    minv_fac_prov   = '$valor'  and
                    minv_cod_ejer   = $idejer and
                    minv_est_minv  <> '0' ";
    $cont = consulta_string($sql, 'cont', $oIfx, 0);
    if ($cont > 0) {
        $oReturn->alert('Ya ingres� esta factura en este Movimiento..');
        $valor = '';
    }
    $oReturn->assign($id, "value", $valor);
    return $oReturn;
}


function fecha_mysql($fecha)
{
    $fecha_array = explode('/', $fecha);
    $m = $fecha_array[0];
    $y = $fecha_array[2];
    $d = $fecha_array[1];

    return ($d . '/' . $m . '/' . $y);
}

function getDiasMes($mes, $anio)
{
    if (is_callable("cal_days_in_month")) {
        return cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
    } else {
        //Lo hacemos a mi manera. 
        return date("d", mktime(0, 0, 0, $mes + 1, 0, $anio));
    }
}

function restaFechas($dFecIni, $dFecFin)
{
    $dFecIni = str_replace("-", "", $dFecIni);
    $dFecIni = str_replace("/", "", $dFecIni);
    $dFecFin = str_replace("-", "", $dFecFin);
    $dFecFin = str_replace("/", "", $dFecFin);

    ereg("([0-9]{1,2})([0-9]{1,2})([0-9]{2,4})", $dFecIni, $aFecIni);

    ereg("([0-9]{1,2})([0-9]{1,2})([0-9]{2,4})", $dFecFin, $aFecFin);

    $date1 = mktime(0, 0, 0, $aFecIni[2], $aFecIni[1], $aFecIni[3]);
    $date2 = mktime(0, 0, 0, $aFecFin[2], $aFecFin[1], $aFecFin[3]);

    return round(($date2 - $date1) / (60 * 60 * 24));
}

function consulta_string1($sql, $campo, $Conexion, $defecto)
{

    $total_mes_stock = 0;
    if ($Conexion->Query($sql)) {
        if ($Conexion->NumFilas() > 0) {
            $total_mes_stock = $Conexion->f($campo);
            if (empty($total_mes_stock)) {
                $total_mes_stock = $defecto;
            }
        } else {
            $total_mes_stock = $defecto;
        }
    }
    $Conexion->Free();
    return $total_mes_stock;
}

function consulta($sql, $campo, $Conexion)
{

    $total_mes_stock = 0;
    if ($Conexion->Query($sql)) {
        if ($Conexion->NumFilas() > 0) {
            $total_mes_stock = $Conexion->f($campo);
            if (empty($total_mes_stock)) {
                $total_mes_stock = 0;
            }
        } else {
            $total_mes_stock = 0;
        }
    }
    $Conexion->Free();
    //$Conexion->Desconectar();

    return $total_mes_stock;
}

// LISTA DE FACTURAS PROVEEDOR
function lista_factura($factura = '', $sucursal = '', $cliente = '', $idempresa = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    $usuario_informix =  $_SESSION['U_USER_INFORMIX'];
    unset($_SESSION['U_FACT']);

    $Html_reporte .= '<fieldset style="border:#999999 1px solid; padding:2px; text-align:center; width:98%;">';
    $Html_reporte .= '<legend class="Titulo">FACTURAS</legend>';
    $Html_reporte .= '<table align="center" border="0" cellpadding="2" cellspacing="1" width="98%">';
    $Html_reporte .= '<tr>
                                <th class="diagrama">N.-</th>
                                <th class="diagrama">Factura</th>
                                <th class="diagrama">Fecha</th>
                                <th class="diagrama">Seleccionar</th>
                        </tr>';

    $sql = "SELECT saedmcp.dmcp_num_fac, min(saedmcp.dcmp_fec_emis)  as dcmp_fec_emis, saedmcp.dmcp_cod_fact
                        FROM saedmcp  WHERE
                        ( saedmcp.dmcp_cod_empr = $idempresa ) AND
                        ( saedmcp.dmcp_cod_sucu = $sucursal  ) and
                        ( saedmcp.clpv_cod_clpv = '$cliente' ) AND
                        ( saedmcp.dmcp_est_dcmp <> 'AN' ) AND
                        saedmcp.dmcp_cod_tran in (  SELECT saetran.tran_cod_tran    FROM saetran,    saedefi   WHERE
                                                        ( saedefi.defi_cod_tran = saetran.tran_cod_tran ) and
                                                        ( saedefi.defi_cod_modu = saetran.tran_cod_modu ) and
                                                        ( saedefi.defi_cod_empr = saetran.tran_cod_empr ) and
                                                        ( ( saedefi.defi_tip_defi = '0' ) )  ) and
                        saedmcp.dmcp_num_fac like '%$factura%'
                        GROUP BY saedmcp.dmcp_num_fac , saedmcp.dmcp_cod_fact
                        ORDER BY  saedmcp.dmcp_num_fac ";
    $i = 1;
    unset($array);
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $ifu->AgregarCampoCheck($oIfx->f('dmcp_cod_fact'), '', false, 1);
                if ($sClass == 'off') $sClass = 'on';
                else $sClass = 'off';
                $Html_reporte .= '<tr height="20" class="' . $sClass . '"
                                        onMouseOver="javascript:this.className=\'link\';"
                                        onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                $Html_reporte .= '<td align="right">' . $i . '</td>';
                $Html_reporte .= '<td align="right">' . $oIfx->f('dmcp_num_fac') . '</td>';
                $Html_reporte .= '<td align="right">' . fecha_mysql_Ymd($oIfx->f('dcmp_fec_emis')) . '</td>';
                $Html_reporte .= '<td align="right">' . $ifu->ObjetoHtml($oIfx->f('dmcp_cod_fact')) . '</td>';
                $Html_reporte .= '</tr>';
                $array[] = array($oIfx->f('dmcp_cod_fact'), $oIfx->f('dmcp_num_fac'), $oIfx->f('dcmp_fec_emis'));
                $i++;
            } while ($oIfx->SiguienteRegistro());
            $Html_reporte .= '<tr>
                                            <td align="center" colspan="6">
                                                <input type="button" value="Cargar"
                                                    onClick="javascript:cargar_factura( )"
                                                    style="width:100px; background-color:#EBEBEB"
                                                    id="BuscaBtn" class="BotonFormulario" />
                                            </td>
                                     </tr>';
        } else {
            $Html_reporte = '';
        }
    }
    $oIfx->Free();
    $_SESSION['U_FACT'] = $array;

    $oReturn->assign("Utilidades", "innerHTML", $Html_reporte);
    return $oReturn;
}

// CARGAR FACTURA
function cargar_factura($factura = '', $sucursal = '', $aForm = '', $idempresa = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN, $DSN_Ifx;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    unset($_SESSION['aDataGird']);
    $aDataGrid = $_SESSION['aDataGird'];

    $aLabelGrid = array('Id', 'Bodega', 'Codigo Item', 'Descripcion', 'Unidad', 'Cantidad', 'Costo', 'Iva', 'Dscto 1', 'Dscto 2', 'Dscto Gral', 'Total', 'Total Con Iva', 'Modificar', 'Eliminar', 'Cuenta', 'Cuenta Iva');
    $oReturn = new xajaxResponse();

    $array = $_SESSION['U_FACT'];
    unset($_SESSION['U_FACT_NCRE']);



    $oReturn->script('cerrar_ventana();');

    if (count($array) > 0) {
        //GUARDA LOS DATOS DEL DETALLE
        $id_minv = '';
        $precio = 0;
        $desc = 0;
        $flete = 0;
        $otro = 0;
        unset($array_aprob);
        foreach ($array as $val) {
            $id_minv = $val[0];
            $factura = $val[1];
            $fecha   = $val[2];
            $check = $aForm[$id_minv];
            if (!empty($check)) {
                $array_aprob[] = $id_minv;
                $sql = "select  dmov_cod_dmov,   dmov_cod_prod,  dmov_cod_bode,
                                 dmov_cod_unid,   dmov_can_dmov,     dmov_can_entr,
                                 dmov_cun_dmov,   dmov_cto_dmov,     dmov_pun_dmov,
                                 dmov_ds1_dmov,   dmov_ds2_dmov,     dmov_ds3_dmov,
                                 dmov_ds4_dmov,   dmov_des_tota,
                                 dmov_est_dmov,   dmov_iva_dmov,     dmov_iva_porc,
                                 p.prbo_cta_inv, p.prbo_cta_ideb
                                 from  saedmov d , saeprbo p where
                                 p.prbo_cod_bode = d.dmov_cod_bode and
                                 p.prbo_cod_prod = d.dmov_cod_prod and
                                 p.prbo_cod_empr = $idempresa and
                                 p.prbo_cod_sucu = $sucursal and
                                 dmov_cod_empr   = $idempresa and
                                 dmov_cod_sucu   = $sucursal and
                                 dmov_num_comp   = $id_minv ";
                if ($oIfx->Query($sql)) {
                    if ($oIfx->NumFilas() > 0) {
                        do {
                            $cont = count($aDataGrid);
                            $cantidad = $oIfx->f('dmov_can_dmov');
                            $costo    = $oIfx->f('dmov_cun_dmov');
                            $iva      = $oIfx->f('dmov_iva_porc');
                            $iva      = $oIfx->f('dmov_iva_porc');
                            $desc1    = $oIfx->f('dmov_ds1_dmov');
                            $desc2    = $oIfx->f('dmov_ds2_dmov');
                            $descuento_general    = $oIfx->f('dmov_des_tota');
                            $idbodega = $oIfx->f('dmov_cod_bode');
                            $idproducto = $oIfx->f('dmov_cod_prod');
                            $idunidad   = $oIfx->f('dmov_cod_unid');
                            $total_fac  = $oIfx->f('dmov_cto_dmov');
                            $cta_inv    = $oIfx->f('prbo_cta_inv');
                            $cta_iva    = $oIfx->f('prbo_cta_ideb');

                            // total con iva
                            if ($iva > 0) {
                                $total_con_iva = round((($total_fac * $iva)  / 100), 2) + $total_fac;
                            } else {
                                $total_con_iva = $total_fac;
                            }

                            // cantidad
                            $fu->AgregarCampoNumerico($cont . '_cantidad', 'Cantidad|LEFT', false, $cantidad, 40, 40);
                            $fu->AgregarComandoAlCambiarValor($cont . '_cantidad', 'cargar_update_cant(\'' . $cont . '\');');

                            // costo
                            $fu->AgregarCampoNumerico($cont . '_costo', 'Costo|LEFT', false, $costo, 40, 40);
                            $fu->AgregarComandoAlCambiarValor($cont . '_costo', 'cargar_update_cant(\'' . $cont . '\');');

                            // iva
                            $fu->AgregarCampoNumerico($cont . '_iva', 'Impuesto|LEFT', false, $iva, 40, 40);
                            $fu->AgregarComandoAlCambiarValor($cont . '_iva', 'cargar_update_cant(\'' . $cont . '\');');

                            // descto1
                            $fu->AgregarCampoNumerico($cont . '_desc1', 'Descto1|LEFT', false, $desc1, 40, 40);
                            $fu->AgregarComandoAlCambiarValor($cont . '_desc1', 'cargar_update_cant(\'' . $cont . '\');');

                            // descto2
                            $fu->AgregarCampoNumerico($cont . '_desc2', 'Descto2|LEFT', false, $desc2, 40, 40);
                            $fu->AgregarComandoAlCambiarValor($cont . '_desc2', 'cargar_update_cant(\'' . $cont . '\');');

                            $aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
                            $aDataGrid[$cont][$aLabelGrid[1]] = $idbodega;
                            $aDataGrid[$cont][$aLabelGrid[2]] = $idproducto;
                            $aDataGrid[$cont][$aLabelGrid[3]] = $idproducto;
                            $aDataGrid[$cont][$aLabelGrid[4]] = $idunidad;
                            $aDataGrid[$cont][$aLabelGrid[5]] = $fu->ObjetoHtml($cont . '_cantidad');  //$cantidad;
                            $aDataGrid[$cont][$aLabelGrid[6]] = $fu->ObjetoHtml($cont . '_costo'); //costo;
                            $aDataGrid[$cont][$aLabelGrid[7]] = $fu->ObjetoHtml($cont . '_iva'); //iva
                            $aDataGrid[$cont][$aLabelGrid[8]] = $fu->ObjetoHtml($cont . '_desc1'); // desc1
                            $aDataGrid[$cont][$aLabelGrid[9]] = $fu->ObjetoHtml($cont . '_desc2'); // dec2
                            $aDataGrid[$cont][$aLabelGrid[10]] = $descuento_general;
                            $aDataGrid[$cont][$aLabelGrid[11]] = $total_fac;
                            $aDataGrid[$cont][$aLabelGrid[12]] = $total_con_iva;
                            $aDataGrid[$cont][$aLabelGrid[13]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil3.png"
                                                                                                        title = "Presione aqui para Modificar"
                                                                                                        style="cursor: hand !important; cursor: pointer !important;"
                                                                                                        onclick="agregar_detalle();"
                                                                                                        alt="Modificar"
                                                                                                        align="bottom" />';
                            $aDataGrid[$cont][$aLabelGrid[14]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                                                                                                        onMouseOver="drc(\'Presione aqui para Eliminar\', \'Eliminar\'); return true;"
                                                                                                        onMouseOut="javascript:nd(); return true;"
                                                                                                        title = "Presione aqui para Eliminar"
                                                                                                        style="cursor: hand !important; cursor: pointer !important;"
                                                                                                        onclick="javascript:xajax_elimina_detalle(' . $cont . ');"
                                                                                                        alt="Eliminar"
                                                                                                        align="bottom" />';
                            $aDataGrid[$cont][$aLabelGrid[15]] = $cta_inv;
                            $aDataGrid[$cont][$aLabelGrid[16]] = $cta_iva;
                            $_SESSION['aDataGird'] = $aDataGrid;
                            $sHtml = mostrar_grid($idempresa);
                            $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
                        } while ($oIfx->SiguienteRegistro());
                    }
                }
                $oIfx->Free();
                $oReturn->script('totales();');
                $oReturn->assign("factura", "value", $factura);
                $factura = substr($factura, 7, 9);
                $oReturn->assign("factura_modi", "value", $factura);
                $_SESSION['U_FACT_NCRE'] = $array_aprob;
            } // fin if
        } // fin foreach               

    } else {
        $oReturn->alert('Por favor seleccione una Factura....');
    }

    return $oReturn;
}



function genera_pdf_doc($aForm = '')
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

    $idempresa     = $aForm['empresa'];
    $idsucursal    = $aForm['sucursal'];
    $tran_cod      = $aForm['tran'];
    $minv_cod      = $aForm['serial'];

    $diario = generar_mov_inv_pdf($idempresa, $idsucursal, $minv_cod, $tran_cod, 0, 0);
    $_SESSION['pdf'] = $diario;

    $oReturn->script('generar_pdf()');
    return $oReturn;
}


function abrir_modal_distribucion($cont_data_grid, $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    $bodega = $aForm['bodega'];
    $prod_nom = $aForm['producto'];

    $linea = $aForm['linea'];
    $grupo = $aForm['grupo'];
    $categoria = $aForm['cate'];
    $marca = $aForm['marca'];

    $cantidad_a_distribuir = $aForm[$cont_data_grid . '_cantidad'];
    $cuenta_contable = $aForm[$cont_data_grid . '_cta_gasto'];

    try {

        // CENTRO DE COSTOS
        $table_op = '';
        $table_op .= '<table class="table table-striped table-condensed table-bordered table-hover" style="width: 90%; margin-top: 20px;" align="center">';
        $table_op .= '
                <tr>
						<td class="fecha_letra" colspan="5">CANTIDAD TOTAL: ' . $cantidad_a_distribuir . '</td>
                        <input value="' . $cantidad_a_distribuir . '" type="number" name="cantidad_total_dist_ad" id="cantidad_total_dist_ad" class="form-control input-sm" style="display: none">
				</tr>
                <tr>
						<td class="fecha_letra">CANTIDAD RESTANTE:</td>
						<td class="fecha_letra" id="saldo_ccosn">
                            <input value="' . $cantidad_a_distribuir . '" type="number" name="valor_total_dist_ad" id="valor_total_dist_ad" class="form-control input-sm" readonly>
                        </td>
						<td class="fecha_letra">PORCENTAJE RESTANTE:</td>
						<td class="fecha_letra" id="porcen_ccosn">
                            <input value="100" type="number" name="porcentaje_total_dist_ad" id="porcentaje_total_dist_ad" class="form-control input-sm" readonly>
                        </td>
						<td colspan="1" align="right">
							<div class="modal-footer">                    
								<button type="button" class="btn btn-info" onclick="agregar_distribucion(\'' . $cont_data_grid . '\');">Procesar</button>
								<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
							</div>
						</td>
				</tr>
				<tr>
						<td align="center" class="fecha_letra">N.-</th>
						<td align="center" class="fecha_letra">Codigo</th>
						<td align="center" class="fecha_letra">Centro Costo</th>
						<td align="center" class="fecha_letra">%</th>
						<td align="center" class="fecha_letra">Cantidad</th>
				</tr>';

        $sql = "select  ccosn_cod_ccosn,  ccosn_nom_ccosn, *
					from saeccosn where
					ccosn_cod_empr  = $empresa and
					ccosn_mov_ccosn = '1' and
					ccosn_impr_sn   = 'N' 
					order by 1 ";
        $i = 1;
        unset($array_ccosn);
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $ccosn_cod_ccosn  = $oIfx->f('ccosn_cod_ccosn');
                    $ccosn_nom_ccosn  = $oIfx->f('ccosn_nom_ccosn');

                    $table_op .= '<tr>';
                    $table_op .= '<td align="right">' . $i . '</td>';
                    $table_op .= '<td align="right">' . $ccosn_cod_ccosn . '</td>';
                    $table_op .= '<td align="left" >' . $ccosn_nom_ccosn . '</td>';
                    $table_op .= '<td align="right" >
                                    <input type="number" name="porcentaje_distribucion_' . $ccosn_cod_ccosn . '" id="porcentaje_distribucion_' . $ccosn_cod_ccosn . '" class="form-control input-sm" onchange="calcular_valor_porcentaje(\'' . $ccosn_cod_ccosn . '\', \'' . $cont_data_grid . '\', 1)">
                                  </td>';
                    $table_op .= '<td align="right" >
                                    <input type="number" name="valor_distribucion_' . $ccosn_cod_ccosn . '" id="valor_distribucion_' . $ccosn_cod_ccosn . '" class="form-control input-sm" onchange="calcular_valor_porcentaje(\'' . $ccosn_cod_ccosn . '\', \'' . $cont_data_grid . '\', 2)">
                                  </td>';

                    $table_op .= '</tr>';

                    $i++;

                    $array_ccosn[] =  array($ccosn_cod_ccosn,  $ccosn_nom_ccosn);
                } while ($oIfx->SiguienteRegistro());
            }
        }

        $table_op .= '</table>';

        unset($_SESSION['U_ARRAY_CCOSN_EGRESO']);
        $_SESSION['U_ARRAY_CCOSN_EGRESO'] = $array_ccosn;



        $modal = '<div id="mostrarModalProdDistribucion" class="modal fade" role="dialog" style="z-index: 99999;">
                 <div class="modal-dialog modal-lg" style="width:1100px;">
                     <div class="modal-content">
                         <div class="modal-header">
                             <button type="button" class="close" data-dismiss="modal">&times;</button>
                             <h4 class="modal-title"><b>Distribucion por Producto:</b></h4>
                         </div>
                         <div class="modal-body">';
        $modal .= $table_op;
        $modal .= '          </div>
                         <div class="modal-footer">
                             <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                         </div>
                     </div>
                 </div>
              </div>';

        $oReturn->assign("divModalProdDistribucion", "innerHTML", $modal);
        $oReturn->script("abre_modal_prod_distribucion();");
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function calcular_valor_porcentaje($ccosn_cod_ccosn, $cont_data_grid, $tipo, $aForm = '')
{

    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    $empresa = $aForm['empresa'];
    $cantidad_total_dist_ad = $aForm['cantidad_total_dist_ad'];
    $array_ccosn = $_SESSION['U_ARRAY_CCOSN_EGRESO'];


    try {
        if ($tipo == 1) {
            // porcentaje
            $porcentaje = $aForm['porcentaje_distribucion_' . $ccosn_cod_ccosn];
            $calculo = ($porcentaje * $cantidad_total_dist_ad) / 100;
            $calculo = round($calculo, 2);
            $oReturn->assign('valor_distribucion_' . $ccosn_cod_ccosn, 'value', $calculo);
        } else {
            // valor
            $valor = $aForm['valor_distribucion_' . $ccosn_cod_ccosn];
            $calculo = ($valor * 100) / $cantidad_total_dist_ad;
            $calculo = round($calculo, 2);
            $oReturn->assign('porcentaje_distribucion_' . $ccosn_cod_ccosn, 'value', $calculo);
        }

        $oReturn->script('calcular_totales_distri(\'' . $ccosn_cod_ccosn . '\', \'' . $cont_data_grid . '\', \'' . $tipo . '\')');
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function calcular_totales_distri($ccosn_cod_ccosn, $cont_data_grid, $tipo, $aForm = '')
{

    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    $empresa = $aForm['empresa'];
    $cantidad_total_dist_ad = $aForm['cantidad_total_dist_ad'];
    $array_ccosn = $_SESSION['U_ARRAY_CCOSN_EGRESO'];


    try {

        // CALCULAR TOTALES
        $total_porcentaje = 0;
        $total_valor = 0;
        if (is_array($array_ccosn)) {
            if (count($array_ccosn) > 0) {
                foreach ($array_ccosn as $val) {
                    $ccosn_cod_ccosn_ad  = $val[0];
                    $ccosn_nom_ccosn_ad  = $val[1];
                    $porcentaje = $aForm['porcentaje_distribucion_' . $ccosn_cod_ccosn_ad];
                    $valor = $aForm['valor_distribucion_' . $ccosn_cod_ccosn_ad];
                    $total_porcentaje += $porcentaje;
                    $total_valor += $valor;
                }
            }
        }



        $diferencia_valor = round($cantidad_total_dist_ad - $total_valor, 2);
        $oReturn->assign('valor_total_dist_ad', 'value', $diferencia_valor);

        $diferencia_porcentaje = round(100 - $total_porcentaje, 2);
        $oReturn->assign('porcentaje_total_dist_ad', 'value', $diferencia_porcentaje);

        if ($diferencia_valor < 0 || $diferencia_porcentaje < 0) {
            $oReturn->alert("La cantidad no puede ser menor a $cantidad_total_dist_ad y el porcentaje no puede se mayor a 100");
            $oReturn->assign('valor_distribucion_' . $ccosn_cod_ccosn, 'value', 0);
            $oReturn->assign('porcentaje_distribucion_' . $ccosn_cod_ccosn, 'value', 0);
            $oReturn->script('calcular_totales_distri(\'' . $ccosn_cod_ccosn . '\', \'' . $cont_data_grid . '\', \'' . $tipo . '\')');
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function agregar_distribucion($cont_data_grid, $aForm = '')
{

    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    $empresa = $aForm['empresa'];
    $cantidad_total_dist_ad = $aForm['cantidad_total_dist_ad'];
    $valor_total_dist_ad = $aForm['valor_total_dist_ad'];
    $porcentaje_total_dist_ad = $aForm['porcentaje_total_dist_ad'];
    $array_ccosn = $_SESSION['U_ARRAY_CCOSN_EGRESO'];


    try {

        // validamos que los porcentajes y valores sean 0 para poder procesar es decir completar el 100% de todo
        if ($valor_total_dist_ad != 0 && $porcentaje_total_dist_ad != 0) {
            throw new Exception('Debe completar toda la cantidad y el porcentaje del 100%');
        }

        // RECORRER DISTRIBUCION POR DISTRIBUCION E IR CREANDO LA DATA
        $aDataGrid = $_SESSION['aDataGird'];
        $aLabelGrid = array(
            'Id',
            'Bodega',
            'Codigo Item',
            'Descripcion',
            'Unidad',
            'Cantidad',
            'Costo',
            'Iva',
            'Dscto 1',
            'Dscto 2',
            'Dscto Gral',
            'Total',
            'Total Con Iva',
            'Modificar',
            'Eliminar',
            'Cuenta',
            'Cuenta Iva',
            'Centro Costo',
            'Cuenta Gasto',
            'Detalle',
            'Lote/Serie',
            'Elaboracion',
            'Caduca'
        );


        // DATA ACTUAL DE LA LINEA YA INSERTADA
        $bodega = $aDataGrid[$cont_data_grid]['Bodega'];
        $codigo_producto = $aDataGrid[$cont_data_grid]['Codigo Item'];
        $prod_nom = $aDataGrid[$cont_data_grid]['Descripcion'];
        $idunidad = $aDataGrid[$cont_data_grid]['Unidad'];

        $cuenta_contable = $aForm[$cont_data_grid . '_cta_gasto'];
        $costo = $aForm[$cont_data_grid . '_costo'];
        $iva = $aForm[$cont_data_grid . '_iva'];

        if (is_array($array_ccosn)) {
            if (count($array_ccosn) > 0) {
                foreach ($array_ccosn as $val) {
                    $ccosn_cod_ccosn_ad  = $val[0];
                    $ccosn_nom_ccosn_ad  = $val[1];
                    $porcentaje = $aForm['porcentaje_distribucion_' . $ccosn_cod_ccosn_ad];
                    $valor = $aForm['valor_distribucion_' . $ccosn_cod_ccosn_ad];
                    if ($porcentaje > 0 && $valor > 0) {

                        $cantidad = $valor;
                        $centro_costos = $ccosn_cod_ccosn_ad;

                        $descuento_general = 0;
                        $descuento = 0;
                        $descuento_2 = 0;
                        $detalle = 'EGRESO REALIZADO DESDE FILTROS';
                        $lote_serie = '';
                        $fela = '';
                        $fecad = '';
                        // TOTAL
                        $total_fac     = 0;
                        $dsc1         = ($costo * $cantidad * $descuento) / 100;
                        $dsc2         = ((($costo * $cantidad) - $dsc1) * $descuento_2) / 100;
                        if ($descuento_general > 0) {
                            // descto general
                            $dsc3                 = ((($costo * $cantidad) - $dsc1 - $dsc2) * $descuento_general) / 100;
                            $total_fact_tmp     = ((($costo * $cantidad) - ($dsc1 + $dsc2 + $dsc3)));
                            $tmp                 = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                        } else {
                            // sin descuento general
                            $total_fact_tmp     = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                            $tmp                 = $total_fact_tmp;
                        }

                        $total_fac = round($total_fact_tmp, 2);

                        // total con iva
                        if ($iva > 0) {
                            $total_con_iva = round((($total_fac * $iva) / 100), 2) + $total_fac;
                        } else {
                            $total_con_iva = $total_fac;
                        }




                        //GUARDA LOS DATOS DEL DETALLE
                        $cont = count($aDataGrid);
                        // cantidad
                        $fu->AgregarCampoNumerico($cont . '_cantidad', 'Cantidad|LEFT', false, $cantidad, 40, 40);
                        $fu->AgregarComandoAlCambiarValor($cont . '_cantidad', 'cargar_update_cant(\'' . $cont . '\');');

                        // costo
                        $fu->AgregarCampoNumerico($cont . '_costo', 'Costo|LEFT', false, $costo, 40, 40);
                        $fu->AgregarComandoAlCambiarValor($cont . '_costo', 'cargar_update_cant(\'' . $cont . '\');');

                        // iva
                        $fu->AgregarCampoNumerico($cont . '_iva', 'Impuesto|LEFT', false, $iva, 40, 40);
                        $fu->AgregarComandoAlCambiarValor($cont . '_iva', 'cargar_update_cant(\'' . $cont . '\');');

                        // descto1
                        $fu->AgregarCampoNumerico($cont . '_desc1', 'Descto1|LEFT', false, 0, 40, 40);
                        $fu->AgregarComandoAlCambiarValor($cont . '_desc1', 'cargar_update_cant(\'' . $cont . '\');');

                        // descto2
                        $fu->AgregarCampoNumerico($cont . '_desc2', 'Descto2|LEFT', false, 0, 40, 40);
                        $fu->AgregarComandoAlCambiarValor($cont . '_desc2', 'cargar_update_cant(\'' . $cont . '\');');

                        // cuenta de gasto
                        $html_cta = '';
                        $fu->AgregarCampoTexto($cont . '_cta_gasto', 'Cuenta Gasto', false, $cuenta_contable, 100, 100);
                        $fu->AgregarComandoAlEscribir($cont . '_cta_gasto', 'cta_gasto_22(\'' . $cont . '_cta_gasto' . '\', event );');
                        $html_cta = $fu->ObjetoHtml($cont . '_cta_gasto');


                        // centro de costo
                        $html_ccos = '';
                        $fu->AgregarCampoTexto($cont . '_ccos', 'Centro Costo', false, $centro_costos, 100, 100);
                        $fu->AgregarComandoAlEscribir($cont . '_ccos', 'centro_costo_22( \'' . $cont . '_ccos' . '\', event );');
                        $html_ccos = $fu->ObjetoHtml($cont . '_ccos');

                        $aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
                        $aDataGrid[$cont][$aLabelGrid[1]] = $bodega;
                        $aDataGrid[$cont][$aLabelGrid[2]] = $codigo_producto;
                        $aDataGrid[$cont][$aLabelGrid[3]] = $prod_nom;
                        $aDataGrid[$cont][$aLabelGrid[4]] = $idunidad;

                        $aDataGrid[$cont][$aLabelGrid[5]] = $fu->ObjetoHtml($cont . '_cantidad');  //$cantidad;
                        $aDataGrid[$cont][$aLabelGrid[6]] = $fu->ObjetoHtml($cont . '_costo'); //costo;
                        $aDataGrid[$cont][$aLabelGrid[7]] = $fu->ObjetoHtml($cont . '_iva'); //iva
                        $aDataGrid[$cont][$aLabelGrid[8]] = $fu->ObjetoHtml($cont . '_desc1'); // desc1
                        $aDataGrid[$cont][$aLabelGrid[9]] = $fu->ObjetoHtml($cont . '_desc2'); // dec2

                        $aDataGrid[$cont][$aLabelGrid[10]] = $descuento_general;
                        $aDataGrid[$cont][$aLabelGrid[11]] = $total_fac;
                        $aDataGrid[$cont][$aLabelGrid[12]] = $total_con_iva;
                        $aDataGrid[$cont][$aLabelGrid[13]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil3.png"
                                    title = "Presione aqui para Modificar"
                                    style="cursor: hand !important; cursor: pointer !important;"
                                    onclick="agregar_detalle(1);"
                                    alt="Modificar"
                                    align="bottom" />';
                        $aDataGrid[$cont][$aLabelGrid[14]] = '
         
                                <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
                                    title = "Presione aqui para Eliminar"
                                    style="cursor: hand !important; cursor: pointer !important;"
                                    onclick="javascript:elimina_detalle(' . $cont . ');"
                                    alt="Eliminar"
                                    align="bottom" />
                                    
                                ';
                        $aDataGrid[$cont][$aLabelGrid[15]] = '';
                        $aDataGrid[$cont][$aLabelGrid[16]] = '';
                        $aDataGrid[$cont][$aLabelGrid[17]] = $html_ccos . ' <div id ="imagen1" class="btn btn-primary btn-sm" onclick="abrir_modal_distribucion(\'' . $cont . '\')">
                                                                                    <span class="glyphicon glyphicon-list"></span>
                                                                                </div>
                                                                                ';
                        $aDataGrid[$cont][$aLabelGrid[18]] = $html_cta;
                        $aDataGrid[$cont][$aLabelGrid[19]] = $detalle;
                        $aDataGrid[$cont][$aLabelGrid[20]] = $lote_serie;
                        $aDataGrid[$cont][$aLabelGrid[21]] = $fela;
                        $aDataGrid[$cont][$aLabelGrid[22]] = $fecad;


                        $_SESSION['aDataGird'] = $aDataGrid;
                    }
                }
            }
        }


        $sHtml = mostrar_grid($empresa);
        $oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);
        $oReturn->script('totales();');
        $oReturn->script("cierra_modal_prod_distribucion();");
        $oReturn->script('elimina_detalle(\'' . $cont_data_grid . '\');');
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function validar_cuenta_contable($cod_prod, $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    $bodega = $aForm['bodega'];
    $prod_nom = $aForm['producto'];

    $linea = $aForm['linea'];
    $grupo = $aForm['grupo'];
    $categoria = $aForm['cate'];
    $marca = $aForm['marca'];

    $cuenta_contable_filt_ = $aForm['cuenta_contable_filt_' . $cod_prod];

    try {
        $sql_cuenta_contable_mov = "SELECT cuen_mov_cuen from saecuen where cuen_cod_cuen = '$cuenta_contable_filt_' and cuen_cod_empr = $empresa ";
        $cuen_mov_cuen = consulta_string($sql_cuenta_contable_mov, 'cuen_mov_cuen', $oIfx, '0');
        if ($cuen_mov_cuen != 1) {
            $oReturn->alert('La cuenta contable debe ser de movimiento...!');
            $oReturn->assign('cuenta_contable_filt_' . $cod_prod, 'value', '');
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function validar_centro_costos($cod_prod, $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();

    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    $bodega = $aForm['bodega'];
    $prod_nom = $aForm['producto'];

    $linea = $aForm['linea'];
    $grupo = $aForm['grupo'];
    $categoria = $aForm['cate'];
    $marca = $aForm['marca'];

    $centro_costos_filt_ = $aForm['centro_costos_filt_' . $cod_prod];

    try {
        $sql_centro_costos_mov = "SELECT ccosn_mov_ccosn from saeccosn where ccosn_cod_ccosn = '$centro_costos_filt_' and ccosn_cod_empr = $empresa ";
        $ccosn_mov_ccosn = consulta_string($sql_centro_costos_mov, 'ccosn_mov_ccosn', $oIfx, '0');
        if ($ccosn_mov_ccosn != 1) {
            $oReturn->alert('El centro de costos debe ser de movimiento...!');
            $oReturn->assign('centro_costos_filt_' . $cod_prod, 'value', '');
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}


/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
