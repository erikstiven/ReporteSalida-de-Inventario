<?

function formato_recibo($id, $cliente){
    global $DSN_Ifx, $DSN;
    session_start();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();
    
    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();
    
    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();
    
    $idempresa = $_SESSION['U_EMPRESA'];
    $sucursal = $_SESSION['U_SUCURSAL'];
    $fact_cod_fact = $id;

    
    unset($arrayMesNom);
    $arrayMesNom[1] = 'ENERO';
    $arrayMesNom[2] = 'FEBRERO';
    $arrayMesNom[3] = 'MARZO';
    $arrayMesNom[4] = 'ABRIL';
    $arrayMesNom[5] = 'MAYO';
    $arrayMesNom[6] = 'JUNIO';
    $arrayMesNom[7] = 'JULIO';
    $arrayMesNom[8] = 'AGOSTO';
    $arrayMesNom[9] = 'SEPTIEMBRE';
    $arrayMesNom[10] = 'OCTUBRE';
    $arrayMesNom[11] = 'NOVIEMBRE';
    $arrayMesNom[12] = 'DICIEMBRE';
    
    $sql = "select fact_cod_fact, fact_tip_vent from saefact where
                    fact_cod_empr = $idempresa and
                    fact_cod_fact = $fact_cod_fact and 
                    fact_cod_clpv = $cliente";
    $contador = consulta_string($sql, 'fact_cod_fact', $oIfx, 0);
    $comprobante = consulta_string($sql, 'fact_tip_vent', $oIfx, 0);
    
    if ($contador > 0) {
    
    
        $div .= '<div style="width: 58mm; margin: 0px; margin-left: -5mm;">'; //div padre
    
        $div .= '<div style="width: 58mm; margin: 0px;"></div>'; //div 1
    
        $div .= '<div style="width: 58mm; margin: 0px;">'; //div 2
    
        //factura
        $sql = "select fact_cod_contr, fact_cod_clpv,
                fact_clav_sri, fact_nom_cliente,
                fact_num_preimp, fact_nse_fact,
                fact_fech_fact, fact_user_web,
                fact_cm8_fac, fact_cm9_fac, 
                fact_hor_fin, fact_cod_mone,
                fact_cod_sucu, fact_cod_contr,
                fact_cm5_fac,
                COALESCE((sum(( COALESCE(fact_tot_fact,0) + COALESCE(fact_ice,0) + fact_iva + fact_val_irbp - fact_dsg_valo + fact_fle_fact + fact_otr_fact + fact_fin_fact))),0) as venta
                from saefact
                where fact_cod_empr = $idempresa and
                fact_cod_fact = $fact_cod_fact and 
                fact_cod_clpv = $cliente
                group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $fact_cod_contr = $oIfx->f('fact_cod_contr');
                $fact_cod_clpv = $oIfx->f('fact_cod_clpv');
                $fact_clav_sri = $oIfx->f('fact_clav_sri');
                $fact_nom_cliente = $oIfx->f('fact_nom_cliente');
                $venta = $oIfx->f('venta');
                $fact_num_preimp = $oIfx->f('fact_num_preimp');
                $fact_nse_fact = $oIfx->f('fact_nse_fact');
                $fact_fech_fact = fecha_mysql_func($oIfx->f('fact_fech_fact'));
                $fact_cm8_fac = $oIfx->f('fact_cm8_fac');
                $fact_cm9_fac = $oIfx->f('fact_cm9_fac');
                $fact_user_web = $oIfx->f('fact_user_web');
                $fact_hor_fin  = $oIfx->f('fact_hor_fin');
                $fact_cod_mone  = $oIfx->f('fact_cod_mone');
                $fact_cod_sucu  = $oIfx->f('fact_cod_sucu');
                $fact_cod_contr = $oIfx->f('fact_cod_contr');
                $fact_cm5_fac = $oIfx->f('fact_cm5_fac');
            }
        }
        $oIfx->Free();
    
        // SAEFXFP
        unset($array_fxfp);
        $sql = "select fxfp_cot_fpag, sum(fxfp_val_fxfp) as ventas
                from saefxfp where
                fxfp_cod_empr = $idempresa and
                fxfp_cod_sucu = $fact_cod_sucu and
                fxfp_cod_fact = $fact_cod_fact  
                group by 1";
        if($oIfx->Query($sql)){
            if($oIfx->NumFilas() > 0){
                do{
                    $array_fxfp[$oIfx->f('fxfp_cot_fpag')] = $oIfx->f('ventas');
                }while($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    
        $venta = consulta_string_func($sql, 'ventas', $oIfx, '');
    
        $sqlTipo = "select  sucu_tip_ambi, sucu_tip_emis ,
                    sucu_nom_sucu, sucu_dir_sucu,
                    sucu_telf_secu, sucu_email_secu
                    from saesucu where
                    sucu_cod_sucu = $fact_cod_sucu ";
        if ($oIfx->Query($sqlTipo)) {
            if ($oIfx->NumFilas() > 0) {
                $ambiente = $oIfx->f('sucu_tip_ambi');
                $tip_emis = $oIfx->f('sucu_tip_emis');
                $sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
                $sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');
                $sucu_telf_secu = $oIfx->f('sucu_telf_secu');
                $sucu_email_secu = $oIfx->f('sucu_email_secu');
            }
        }
        $oIfx->Free();
    
        $sql = "select empr_nom_empr, empr_ruc_empr, 
                empr_dir_empr, empr_conta_sn,
                empr_tel_resp, empr_num_resu, empr_path_logo
                from saeempr 
                where empr_cod_empr = $idempresa ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $nombre_empr = trim($oIfx->f('empr_nom_empr'));
                $ruc_empr = $oIfx->f('empr_ruc_empr');
                $dir_empr = trim($oIfx->f('empr_dir_empr'));
                $conta_sn = $oIfx->f('empr_conta_sn');
                $empr_num_resu = $oIfx->f('empr_num_resu');
                $empr_tel_resp = $oIfx->f('empr_tel_resp');
                $empr_path_logo = $oIfx->f('empr_path_logo');
            }
        }
        $oIfx->Free();
    
        $sql = "select mone_sgl_mone, mone_smb_mene from saemone where mone_cod_mone = $fact_cod_mone";
        $mone_sgl_mone = consulta_string_func($sql, 'mone_sgl_mone', $oIfx, '');
        $mone_smb_mene = consulta_string_func($sql, 'mone_smb_mene', $oIfx, '');
        $moneda = $mone_sgl_mone . ' ' . $mone_smb_mene;
        $usuario = '';
        if (!empty($fact_user_web)) {
            $sql = "select concat(usuario_nombre, ' ', usuario_apellido) as user
                    from comercial.usuario
                    where usuario_id = $fact_user_web";
            $usuario = consulta_string_func($sql, 'user', $oCon, '');
        }
    
        //datos del contrato
        $sql = "select codigo, direccion, observaciones, direccion_cobro, ruta, referencia
                from isp.contrato_clpv
                where id = $fact_cod_contr and
                id_clpv = $cliente";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $codigo = $oCon->f('codigo');
                $direccion = $oCon->f('direccion');
                $observaciones = $oCon->f('observaciones');
                $direccion_cobro = $oCon->f('direccion_cobro');
                $ruta = $oCon->f('ruta');
                $referencia = $oCon->f('referencia');
            }
        }
        $oCon->Free();
    
        //mes de factura
        $mesActual = date("m");
        $pagoAbono = false;
        $sql = "select dfac_cod_mes
                from saedfac 
                where dfac_cod_empr = $idempresa and
                dfac_cod_fact = $fact_cod_fact and
                dfac_cod_clpv = $fact_cod_clpv and
                dfac_cod_mes is not null
                group by 1
                order by 1";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $detalle_m = '';
                do {
    
                    $dfac_cod_mes = $oIfx->f('dfac_cod_mes');
    
                    if (!empty($dfac_cod_mes)) {
                        $sql = "SELECT mes, anio FROM isp.contrato_pago where id = $dfac_cod_mes";
                        $mes = consulta_string_func($sql, 'mes', $oCon, 0)*1;
                        $anio = consulta_string_func($sql, 'anio', $oCon, 0);
    
                        $mes_n = '';
                        if (!empty($mes)) {
                            $mes_n = $arrayMesNom[$mes];
                            //$detalle_m .= '<p style="margin: 0px;">' . $mes_n . ' - ' . $anio . '</p>';
                        }
    
                        if($mes >= $mesActual){
                            $pagoAbono = true;
                        }
                    }
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    
        //$detalle .= 'Pago Balance';
    
        //detalle de factura
        $sql = "select dfac_det_dfac, dfac_nom_prod
                from saedfac 
                where dfac_cod_empr = $idempresa and
                dfac_cod_fact = $fact_cod_fact and
                dfac_cod_clpv = $fact_cod_clpv group by 1,2
                order by 1 ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $dfac_det_dfac = htmlentities($oIfx->f('dfac_nom_prod'));
                    $dfac_cod_mes = $oIfx->f('dfac_det_dfac');
    
                    $detalle .= $dfac_cod_mes;
                    $detalle1.= $dfac_det_dfac;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    
        $reciboComp = ($comprobante == 99) ? 'RECIBO' : 'FACTURA';
    
        if ($comprobante == 98) {
    
            $div .= '<div style="width: 35%; height: 15mm;">';
            // $div .= '<div align="center" style="width: 100%; margin: 0px;">';
            // $div .= '<div style="float:left">';
            // $div .= '<div>';
            // // $div .= '<span align="center" style="text-align: center; font-size: 7.5px;">' . $razonSocial . '</span>';
            // $div .= '</div>';
            $div .= '<div style="text-align: center;"><span align="center" style="font-size: 9px;">' . $sucu_nom_sucu .'</span></div>';
            // $div .= '</div>';
            // $div .= '</div>';
    
            $div .='<div style="width: 100%; display: table; text-align: center;">
                    <span style="font-size: 10px; font-weight: bold; text-align: center;">AVISO DE COBRO</span>
                </div>
                <div style="width: 100%; display: table; margin-top: -2px;">
                    <span style="font-size: 10px; font-weight: bold;">FECHA: </span>
                    <span style="font-size: 9px;">' . $fact_fech_fact . ' ' . $fact_hor_fin . '</span>
                </div>
                <div style="width: 100%; display: table; margin-top: -2px;">
                    <span style="font-size: 10px; font-weight: bold;">CODIGO: </span>
                    <span style="font-size: 9px;">'.$codigo.'</span>
                    <span style="font-size: 10px; font-weight: bold; margin-left: 50px;">RUTA: </span>
                    <span style="font-size: 9px;">'.$ruta.'</span>
                </div>
                <div style="width: 100%; display: table; margin-top: -2px;">
                    <span style="font-size: 10px; font-weight: bold;">SUSCRIPTOR: </span>
                    <span style="font-size: 9px;">'.$fact_nom_cliente.'</span>
                </div>
                <div style="width: 100%; display: table; margin-top: -2px;">
                     <span style="font-size: 9px;">'.substr($direccion, 0, 80).'</span>
                </div>
                <div style="width: 100%; display: table; margin-top: -2px;">
                     <span style="font-size: 9px;">'.$referencia.'</span>
                </div>';
    
            if (!empty($direccion_cobro)) {
                $div .= '<div style="width: 100%; display: table; margin-top: -2px;">
                    <span style="font-size: 10px; font-weight: bold;">DIR COBRO: </span>
                    <span style="font-size: 9px;">'.$direccion_cobro.'</span>
                   </div>';
            }
    
            if (!empty($observaciones)) {
                $div .= '<div style="width: 100%; display: table; margin-top: -2px;">
                        <span style="font-size: 10px; font-weight: bold;">OBSERVACION: </span>
                        <span style="font-size: 9px;">'.strtoupper($observaciones).'</span>
                    </div>';
                // $div .= '<div style="width: 100%; border: 1px solid black; border-radius: 0 0 3px; padding: 0.6px; margin-top: 5px;">
                // 			<div style="font-size: 8px; margin: 4px;">'.$observaciones.'</div>
                // 		</div>';
            }
    
            $div .='<div style="width: 100%; display: table; margin-top: 5px;">
                     <span style="font-size: 7px;"></span>
                </div>';
    
            $div .=	'<div style="width: 100%; border: 1px solid black; border-radius: 0 0 3px;">
                    <div style="margin-left: 5px;">
                        <span style="font-size: 10px; font-weight: bold; margin-left: 0px;">Descripcion</span>
                        <span style="font-size: 9px; margin-left: 20px; "> '.$detalle_m.' '.$detalle.'</span>
    
                        <span style="float: right; font-size: 10px; margin-left: 135px; font-weight: bold; text-align: right;">'.$moneda.' '.number_format($venta, 2, '.', ',').'</span>
                    </div>
                </div>';
    
            $cero = 0;
            //objeto contratos
            $Contratos = new Contratos($oCon, $oIfx, $idempresa, $fact_cod_sucu, $fact_cod_clpv, $fact_cod_contr);
            $deuda = $Contratos->consultaMontoMesAdeuda();
    
            $div .='<div style="border: 1px solid black; border-radius: 0 0 3px; margin-top: 5px; ">
                <table align="left">
                    <tr>
                        <td style="font-size: 10px; width:180px;" align="right" >Balance Anterior:</td>
                        <td style="font-size: 10px;" width:100px;" align="right">' . number_format($fact_cm5_fac, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px;  width:180px;" align="right">Efectivo ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($array_fxfp['EFE'], 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px;  width:180px;" align="right">Tarjeta ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($array_fxfp['TAR'], 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px;  width:180px;" align="right">Cheque ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($array_fxfp['CHE'], 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px;  width:180px;" align="right">Deposito ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($array_fxfp['DEP'], 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px; width:180px;" align="right">Balance Actual RD $:</td>
                        <td style="font-size: 10px;" align="right">' . number_format($deuda, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px; width:180px;" align="right">Total ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($venta, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px; width:180px;" align="right">Pagado ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($fact_cm8_fac, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px; width:180px;" align="right">Cambio ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($fact_cm9_fac, 2, '.', ',') . '</td>
                    </tr>
                </table>
            </div>';
    
            $div .= '<div style="border: 1px solid black; border-radius: 0 0 0px; margin-top: 5px;">
                        <table>
                            <tr>
                                <td colspan="2">Su Factura la puede pasar recogiendo</td>
                            </tr>
                            <tr>
                                <td colspan="2">a nuestras oficinas.</td>
                            </tr>
                            <tr>
                                <td colspan="2">&nbsp;</td>
                            </tr>
                        </table>
                    </div>';
    
            // $div .= '<div style="width: 100%; margin-top: -8px;">';
            // $div .= '<span style="font-size: 8px; font-weight: bold;">SU FACTURA LA PUEDE RECOGER EN OFICINAS</span>';
            // $div .= '</div>';
            $div .= '</div>';
            $div .= '</div>';
            $div .= '</div>';
            // $div .= '</div>';
    
        } else {
    
            $div .= '<table align="left" style="font-size: 10px; width:58mm;" border=0px>'; //table cabecera
    
            /*$div .= '<tr>';
            $div .= '<td align="center" style="font-family: Courier;"><img width="58mm;" height="70px;" src="' . $empr_path_logo . '"></td>';
            $div .= '</tr>';*/
    
            $div .= '<tr>';
            $div .= '<td align="center" style="font-size: 13px; font-family: Courier; width: 10mm;">RUC ' . $ruc_empr . '</td>';
            $div .= '</tr>';
    
            /* $div .= '<tr>';
            $div .= '<td align="center" style="font-size: 13px; font-family: Courier;">MATRIZ:</td>';
            $div .= '</tr>'; */
    
    
            $div .= '<tr>';
            $div .= '<td align="center" style="font-size: 11px; font-family: Courier;width=58mm;">' . $dir_empr . '</td>';
            $div .= '</tr>';
    
            /*
            $div .= '<tr>';
            $div .= '<td align="center" style="font-size: 13px; margin-bottom: 3px; font-family: Courier; width=58mm;">SUCURSAL:</td>';
            $div .= '</tr>';
            $div .= '<tr>';
            $div .= '<td align="center" style="font-size: 11px; margin-bottom: 3px; font-family: Courier;width=58mm;">' . $sucu_dir_sucu . '</td>';
            $div .= '</tr>';
            $div .= '<tr>'; */
            $div .= '<td align="center" style="font-size: 13px; margin-bottom: 3px; font-family: Courier;width=58mm;">Tel.: ' . $sucu_telf_secu . '</td>';
            $div .= '</tr>';
            $div .= '<tr>';
            $div .= '<td align="center" style="font-size: 10px; margin-bottom: 1px; font-family: Courier;width=58mm;">Email: ' . $sucu_email_secu . '</td>';
            $div .= '</tr>';
    
    
    
            $div .= '</table>';
    
    
            $div .= '<table align="left" style="font-size: 10px; width:58mm;" border=0px>';
    
            //$div .= '<tr><td colspan="2"></td></tr>';
    
            $div .= '<tr>
                    <td  align="center" colspan="2"><h5 style="font-family: Courier; margin: 3px;width:58mm;">'.$reciboComp.'</h5></td>
                </tr>';
    
            /*$div .= "<tr>";*/
            $div .= "<tr>";
            $div .= '<td style="font-size: 10px; font-family: Courier;width:58mm;" align="center" colspan="2">' . $fact_nse_fact . ' - ' . $fact_num_preimp . '</td>';
            $div .= "</tr>";
    
            $div .= "<tr>";
            $div .= '<td style="font-size: 13px; font-family: Courier;width:58mm;" colspan="2"  align="center">*** ORIGINAL ***</td>';
            $div .= "</tr>";
            $div .= "<tr>";
            $div .= '<td style="font-size: 10px; font-family: Courier;width:58mm;" colspan="2" align="center">' . $fact_fech_fact . ' ' . $fact_hor_fin . '</td>';
            $div .= "</tr>";
    
    
            $div .= '<tr>
                        <td style="font-size: 10px; font-family: Courier;width:40mm;" colspan="2" >-------------------------------------</td>
                    </tr>';
            // $fact_nom_cliente = explode(" ", $fact_nom_cliente);
            $div .= "<tr>";
            $div .= '<td style="font-size: 13px; font-family: Courier;width:58mm;" colspan="2"  align="center">Cliente: </td>';
            $div .= "</tr>";
            $div .= "<tr>";
            $div .= '<td style="font-size: 13px; font-family: Courier;width:58mm;" colspan="2"  align="center"><b>' . $fact_nom_cliente . '</b></td>';
            // $div .= '<td style="font-size: 10px; font-family: Courier; width:10mm;" align="center"><b>' . htmlentities($fact_nom_cliente[2].' '.$fact_nom_cliente[3]) . '</b></td>';
            $div .= "</tr>";
    
    
            $div .= "<tr>";
            $div .= '<td style="font-size: 10px; font-family: Courier; width: 10mm;" align="center">Contrato:</td>';
            $div .= '<td style="font-size: 10px; font-family: Courier; width: 29mm;" align="center"><b>' . $codigo . '</b></td>';
            $div .= "</tr>";
    
    
            /* $div .= "<tr>";
            $div .= '<td style="font-size: 10px; font-family: Courier; width: 10mm;" align="center">Direccion:</td>';
            $div .= '<td style="font-size: 6px; font-family: Courier; width: 29mm;" align="center"><b>' . substr($direccion, 0, 20) . '</b></td>';
            $div .= "</tr>"; */
    
            $div .= "<tr>";
            $div .= '<td style="font-size: 10px; font-family: Courier; width: 10mm;" align="center">Telefono:</td>';
            $div .= '<td style="font-size: 10px; font-family: Courier; width: 29mm;" align="center"><b>' . substr($telefono, 0, 20) . '</b></td>';
            $div .= "</tr>";
    
            $div .= '<tr>
                    <td style="font-size: 12px; font-family: Courier;width: 58mm;" colspan="2">------------------------------</td>
                </tr>';
    
    
            $div .= "</table>";
    
            $div .= "<table align='left' style='font-size: 12px; font-family: Courier;width: 58mm;'>";
            $div .= "<tr>";
            $div .= '<td style="font-size: 10px; font-family: Courier;" align="center">Descripcion:</td>';
            $div .= "</tr>";
            $div .= "<tr>";
            $div .= '<td align="center" style="font-size: 10px; margin-bottom: 1px; font-family: Courier;width=58mm;">*' . strtolower($detalle1) . '</b></td>';
            $div .= "</tr>";
            $div .= "<tr>";
            $div .= '<td align="center" style="font-size: 10px; margin-bottom: 1px; font-family: Courier;width=58mm;">' . strtolower($detalle) . '</b></td>';
            $div .= "</tr>";
            /* $div .= '<tr>
                        <td align="center" style="font-size: 10px; font-family: Courier; width:60px;">' . $moneda . '</td>
                        <td align="center" style="font-size: 10px; font-family: Courier; width:50px;"><b>' . number_format($venta, 2, '.', ',') . '</b></td>
                    </tr>'; */
    
    
            $div .= "</table>";
    
            $cero = 0;
            //objeto contratos
            $Contratos = new Contratos($oCon, $oIfx, $idempresa, $fact_cod_sucu, $fact_cod_clpv, $fact_cod_contr);
            $deuda = $Contratos->consultaMontoMesAdeuda();
            //<tr>
            //<td style="font-size: 13px; font-family: Courier; width:150px;" align="right" >Saldo Anterior:</td>
            //<td style="font-size: 13px; font-family: Courier; width:50px;" align="right"><b>' . number_format($fact_cm5_fac, 2, '.', ',') . '</b></td>
            //</tr>
    
            $usuario = explode(" ", $usuario);
            $div .= '<table align="left">
                       
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">Efectivo ' . $moneda . ':</td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($array_fxfp['EFE'], 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">Tarjeta ' . $moneda . ':</td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($array_fxfp['TAR'], 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">Cheque ' . $moneda . ':</td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($array_fxfp['CHE'], 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">Deposito ' . $moneda . ':</td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($array_fxfp['DEP'], 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right" ><b>Total ' . $moneda . ':<b></td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($venta, 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">Pagado ' . $moneda . ':</td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($fact_cm8_fac, 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">Cambio ' . $moneda . ':</td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($fact_cm9_fac, 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">- Recibo no</td>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">- Abono a cuenta</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">  valido sin firma</td>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">  no evita .</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">  y sello.</td>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">  suspension.</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">User: </td>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">' . $usuario[0] . ' '. $usuario[2] .'</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; font-family: Courier; width:110px;" align="center" colspan="2"><h5 style="font-family: Courier; margin-top:10px;">GRACIAS POR SU PAGO!!</h5></td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; font-family: Courier; width:110px;" align="center" colspan="2"><h5 style="font-family: Courier; margin-top:10px;">_____________________</h5></td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; font-family: Courier; width:110px;" align="center" colspan="2"><h5 style="font-family: Courier; margin:0px;">Recibido Conforme</h5></td>
                        </tr>
                    </table>';
    
            $div .= "<table align='left'>";
            $div .= "<tr>";
            $div .= '<tr><td colspan="2"></td></tr>';
            $div .= '<tr><td colspan="2"></td></tr>';
            $div .= '<tr><td colspan="2"></td></tr>';
            $div .= "</tr>";
            $div .= "</table>";
    
            /* $div .= "<div align='left' style='margin-top: 10px;'>
                        <p style='margin: 0px; font-family: Courier; font-size: 10px;'>- Recibo no valido sin firma y sello.</p>
                        <p style='margin: 0px; font-family: Courier; font-size: 10px;'>- Abono a cuenta no evita suspension.</p>
                    </div>"; */
            /* $div .= "<div align='left' style='margin-top: 0x;'>
                        <h5 style='font-family: Courier; margin:0px;'>GRACIAS POR SU PAGO!!</h5>
                    </div>"; */
            $div .= '</div>'; //fin div 2
    
            /* $div .= '<div style="margin-top: 20px;">
                        <table align="left">
                            <tr>
                                <td align="center" style="margin: 0px; font-family: Courier;">_____________________</td>
                            </tr>
                            <tr>
                                <td align="center" style="margin: 0px; font-family: Courier;">Recibido Conforme</td>
                            </tr>
                        </table>
                    </div>'; */
    
            $div .= '</div>'; //fin div padre
        }
    
    } else {
        $table = '<div>No existe Factura...</div>';
    }
    
    //arma pdf
    $table .= $div;
    echo $table;
    

return $ruta;
}

function formato_recibo_elec($id, $cliente){
    global $DSN_Ifx, $DSN;
    session_start();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();
    
    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();
    
    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();
    
    $idempresa = $_SESSION['U_EMPRESA'];
    $sucursal = $_SESSION['U_SUCURSAL'];
    $fact_cod_fact = $id;
    
    unset($arrayMesNom);
    $arrayMesNom[1] = 'ENERO';
    $arrayMesNom[2] = 'FEBRERO';
    $arrayMesNom[3] = 'MARZO';
    $arrayMesNom[4] = 'ABRIL';
    $arrayMesNom[5] = 'MAYO';
    $arrayMesNom[6] = 'JUNIO';
    $arrayMesNom[7] = 'JULIO';
    $arrayMesNom[8] = 'AGOSTO';
    $arrayMesNom[9] = 'SEPTIEMBRE';
    $arrayMesNom[10] = 'OCTUBRE';
    $arrayMesNom[11] = 'NOVIEMBRE';
    $arrayMesNom[12] = 'DICIEMBRE';
    
    $sql = "select fact_cod_fact, fact_tip_vent from saefact where
                    fact_cod_empr = $idempresa and
                    fact_cod_fact = $fact_cod_fact and 
                    fact_cod_clpv = $cliente";
    $contador = consulta_string($sql, 'fact_cod_fact', $oIfx, 0);
    $comprobante = consulta_string($sql, 'fact_tip_vent', $oIfx, 0);
    
    if ($contador > 0) {
    
    
        $div .= '<div style="width: 58mm; margin: 0px; margin-left: -5mm;">'; //div padre
    
        $div .= '<div style="width: 58mm; margin: 0px;"></div>'; //div 1
    
        $div .= '<div style="width: 58mm; margin: 0px;">'; //div 2
    
        //factura
        $sql = "select fact_cod_contr, fact_cod_clpv,
                fact_clav_sri, fact_nom_cliente,
                fact_num_preimp, fact_nse_fact,
                fact_fech_fact, fact_user_web,
                fact_cm8_fac, fact_cm9_fac, 
                fact_hor_fin, fact_cod_mone,
                fact_cod_sucu, fact_cod_contr,
                fact_cm5_fac,
                COALESCE((sum(( COALESCE(fact_tot_fact,0) + COALESCE(fact_ice,0) + fact_iva + fact_val_irbp - fact_dsg_valo + fact_fle_fact + fact_otr_fact + fact_fin_fact))),0) as venta
                from saefact
                where fact_cod_empr = $idempresa and
                fact_cod_fact = $fact_cod_fact and 
                fact_cod_clpv = $cliente
                group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $fact_cod_contr = $oIfx->f('fact_cod_contr');
                $fact_cod_clpv = $oIfx->f('fact_cod_clpv');
                $fact_clav_sri = $oIfx->f('fact_clav_sri');
                $fact_nom_cliente = $oIfx->f('fact_nom_cliente');
                $venta = $oIfx->f('venta');
                $fact_num_preimp = $oIfx->f('fact_num_preimp');
                $fact_nse_fact = $oIfx->f('fact_nse_fact');
                $fact_fech_fact = fecha_mysql_func($oIfx->f('fact_fech_fact'));
                $fact_cm8_fac = $oIfx->f('fact_cm8_fac');
                $fact_cm9_fac = $oIfx->f('fact_cm9_fac');
                $fact_user_web = $oIfx->f('fact_user_web');
                $fact_hor_fin  = $oIfx->f('fact_hor_fin');
                $fact_cod_mone  = $oIfx->f('fact_cod_mone');
                $fact_cod_sucu  = $oIfx->f('fact_cod_sucu');
                $fact_cod_contr = $oIfx->f('fact_cod_contr');
                $fact_cm5_fac = $oIfx->f('fact_cm5_fac');
            }
        }
        $oIfx->Free();
    
        // SAEFXFP
        unset($array_fxfp);
        $sql = "select fxfp_cot_fpag, sum(fxfp_val_fxfp) as ventas
                from saefxfp where
                fxfp_cod_empr = $idempresa and
                fxfp_cod_sucu = $fact_cod_sucu and
                fxfp_cod_fact = $fact_cod_fact  
                group by 1";
        if($oIfx->Query($sql)){
            if($oIfx->NumFilas() > 0){
                do{
                    $array_fxfp[$oIfx->f('fxfp_cot_fpag')] = $oIfx->f('ventas');
                }while($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    
        $venta = consulta_string_func($sql, 'ventas', $oIfx, '');
    
        $sqlTipo = "select  sucu_tip_ambi, sucu_tip_emis ,
                    sucu_nom_sucu, sucu_dir_sucu,
                    sucu_telf_secu, sucu_email_secu
                    from saesucu where
                    sucu_cod_sucu = $fact_cod_sucu ";
        if ($oIfx->Query($sqlTipo)) {
            if ($oIfx->NumFilas() > 0) {
                $ambiente = $oIfx->f('sucu_tip_ambi');
                $tip_emis = $oIfx->f('sucu_tip_emis');
                $sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
                $sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');
                $sucu_telf_secu = $oIfx->f('sucu_telf_secu');
                $sucu_email_secu = $oIfx->f('sucu_email_secu');
            }
        }
        $oIfx->Free();
    
        $sql = "select empr_nom_empr, empr_ruc_empr, 
                empr_dir_empr, empr_conta_sn,
                empr_tel_resp, empr_num_resu, empr_path_logo
                from saeempr 
                where empr_cod_empr = $idempresa ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $nombre_empr = trim($oIfx->f('empr_nom_empr'));
                $ruc_empr = $oIfx->f('empr_ruc_empr');
                $dir_empr = trim($oIfx->f('empr_dir_empr'));
                $conta_sn = $oIfx->f('empr_conta_sn');
                $empr_num_resu = $oIfx->f('empr_num_resu');
                $empr_tel_resp = $oIfx->f('empr_tel_resp');
                $empr_path_logo = $oIfx->f('empr_path_logo');
            }
        }
        $oIfx->Free();
    
        $sql = "select mone_sgl_mone, mone_smb_mene from saemone where mone_cod_mone = $fact_cod_mone";
        $mone_sgl_mone = consulta_string_func($sql, 'mone_sgl_mone', $oIfx, '');
        $mone_smb_mene = consulta_string_func($sql, 'mone_smb_mene', $oIfx, '');
        $moneda = $mone_sgl_mone . ' ' . $mone_smb_mene;
        $usuario = '';
        if (!empty($fact_user_web)) {
            $sql = "select concat(usuario_nombre, ' ', usuario_apellido) as user
                    from comercial.usuario
                    where usuario_id = $fact_user_web";
            $usuario = consulta_string_func($sql, 'user', $oCon, '');
        }
    
        //datos del contrato
        $sql = "select codigo, direccion, observaciones, direccion_cobro, ruta, referencia
                from isp.contrato_clpv
                where id = $fact_cod_contr and
                id_clpv = $cliente";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $codigo = $oCon->f('codigo');
                $direccion = $oCon->f('direccion');
                $observaciones = $oCon->f('observaciones');
                $direccion_cobro = $oCon->f('direccion_cobro');
                $ruta = $oCon->f('ruta');
                $referencia = $oCon->f('referencia');
            }
        }
        $oCon->Free();
    
        //mes de factura
        $mesActual = date("m");
        $pagoAbono = false;
        $sql = "select dfac_cod_mes
                from saedfac 
                where dfac_cod_empr = $idempresa and
                dfac_cod_fact = $fact_cod_fact and
                dfac_cod_clpv = $fact_cod_clpv and
                dfac_cod_mes is not null
                group by 1
                order by 1";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $detalle_m = '';
                do {
    
                    $dfac_cod_mes = $oIfx->f('dfac_cod_mes');
    
                    if (!empty($dfac_cod_mes)) {
                        $sql = "SELECT mes, anio FROM isp.contrato_pago where id = $dfac_cod_mes";
                        $mes = consulta_string_func($sql, 'mes', $oCon, 0)*1;
                        $anio = consulta_string_func($sql, 'anio', $oCon, 0);
    
                        $mes_n = '';
                        if (!empty($mes)) {
                            $mes_n = $arrayMesNom[$mes];
                            //$detalle_m .= '<p style="margin: 0px;">' . $mes_n . ' - ' . $anio . '</p>';
                        }
    
                        if($mes >= $mesActual){
                            $pagoAbono = true;
                        }
                    }
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    
        //$detalle .= 'Pago Balance';
    
        //detalle de factura
        $sql = "select dfac_det_dfac, dfac_nom_prod
                from saedfac 
                where dfac_cod_empr = $idempresa and
                dfac_cod_fact = $fact_cod_fact and
                dfac_cod_clpv = $fact_cod_clpv group by 1,2
                order by 1 ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $dfac_det_dfac = htmlentities($oIfx->f('dfac_nom_prod'));
                    $dfac_cod_mes = $oIfx->f('dfac_det_dfac');
    
                    $detalle .= $dfac_cod_mes;
                    $detalle1.= $dfac_det_dfac;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    
        $reciboComp = ($comprobante == 99) ? 'RECIBO' : 'FACTURA';
    
        if ($comprobante == 98) {
    
            $div .= '<div style="width: 35%; height: 15mm;">';
            // $div .= '<div align="center" style="width: 100%; margin: 0px;">';
            // $div .= '<div style="float:left">';
            // $div .= '<div>';
            // // $div .= '<span align="center" style="text-align: center; font-size: 7.5px;">' . $razonSocial . '</span>';
            // $div .= '</div>';
            $div .= '<div style="text-align: center;"><span align="center" style="font-size: 9px;">' . $sucu_nom_sucu .'</span></div>';
            // $div .= '</div>';
            // $div .= '</div>';
    
            $div .='<div style="width: 100%; display: table; text-align: center;">
                    <span style="font-size: 10px; font-weight: bold; text-align: center;">AVISO DE COBRO</span>
                </div>
                <div style="width: 100%; display: table; margin-top: -2px;">
                    <span style="font-size: 10px; font-weight: bold;">FECHA: </span>
                    <span style="font-size: 9px;">' . $fact_fech_fact . ' ' . $fact_hor_fin . '</span>
                </div>
                <div style="width: 100%; display: table; margin-top: -2px;">
                    <span style="font-size: 10px; font-weight: bold;">CODIGO: </span>
                    <span style="font-size: 9px;">'.$codigo.'</span>
                    <span style="font-size: 10px; font-weight: bold; margin-left: 50px;">RUTA: </span>
                    <span style="font-size: 9px;">'.$ruta.'</span>
                </div>
                <div style="width: 100%; display: table; margin-top: -2px;">
                    <span style="font-size: 10px; font-weight: bold;">SUSCRIPTOR: </span>
                    <span style="font-size: 9px;">'.$fact_nom_cliente.'</span>
                </div>
                <div style="width: 100%; display: table; margin-top: -2px;">
                     <span style="font-size: 9px;">'.substr($direccion, 0, 80).'</span>
                </div>
                <div style="width: 100%; display: table; margin-top: -2px;">
                     <span style="font-size: 9px;">'.$referencia.'</span>
                </div>';
    
            if (!empty($direccion_cobro)) {
                $div .= '<div style="width: 100%; display: table; margin-top: -2px;">
                    <span style="font-size: 10px; font-weight: bold;">DIR COBRO: </span>
                    <span style="font-size: 9px;">'.$direccion_cobro.'</span>
                   </div>';
            }
    
            if (!empty($observaciones)) {
                $div .= '<div style="width: 100%; display: table; margin-top: -2px;">
                        <span style="font-size: 10px; font-weight: bold;">OBSERVACION: </span>
                        <span style="font-size: 9px;">'.strtoupper($observaciones).'</span>
                    </div>';
                // $div .= '<div style="width: 100%; border: 1px solid black; border-radius: 0 0 3px; padding: 0.6px; margin-top: 5px;">
                // 			<div style="font-size: 8px; margin: 4px;">'.$observaciones.'</div>
                // 		</div>';
            }
    
            $div .='<div style="width: 100%; display: table; margin-top: 5px;">
                     <span style="font-size: 7px;"></span>
                </div>';
    
            $div .=	'<div style="width: 100%; border: 1px solid black; border-radius: 0 0 3px;">
                    <div style="margin-left: 5px;">
                        <span style="font-size: 10px; font-weight: bold; margin-left: 0px;">Descripcion</span>
                        <span style="font-size: 9px; margin-left: 20px; "> '.$detalle_m.' '.$detalle.'</span>
    
                        <span style="float: right; font-size: 10px; margin-left: 135px; font-weight: bold; text-align: right;">'.$moneda.' '.number_format($venta, 2, '.', ',').'</span>
                    </div>
                </div>';
    
            $cero = 0;
            //objeto contratos
            $Contratos = new Contratos($oCon, $oIfx, $idempresa, $fact_cod_sucu, $fact_cod_clpv, $fact_cod_contr);
            $deuda = $Contratos->consultaMontoMesAdeuda();
    
            $div .='<div style="border: 1px solid black; border-radius: 0 0 3px; margin-top: 5px; ">
                <table align="left">
                    <tr>
                        <td style="font-size: 10px; width:180px;" align="right" >Balance Anterior:</td>
                        <td style="font-size: 10px;" width:100px;" align="right">' . number_format($fact_cm5_fac, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px;  width:180px;" align="right">Efectivo ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($array_fxfp['EFE'], 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px;  width:180px;" align="right">Tarjeta ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($array_fxfp['TAR'], 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px;  width:180px;" align="right">Cheque ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($array_fxfp['CHE'], 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px;  width:180px;" align="right">Deposito ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($array_fxfp['DEP'], 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px; width:180px;" align="right">Balance Actual RD $:</td>
                        <td style="font-size: 10px;" align="right">' . number_format($deuda, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px; width:180px;" align="right">Total ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($venta, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px; width:180px;" align="right">Pagado ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($fact_cm8_fac, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px; width:180px;" align="right">Cambio ' . $moneda . ':</td>
                        <td style="font-size: 10px;" align="right">' . number_format($fact_cm9_fac, 2, '.', ',') . '</td>
                    </tr>
                </table>
            </div>';
    
            $div .= '<div style="border: 1px solid black; border-radius: 0 0 0px; margin-top: 5px;">
                        <table>
                            <tr>
                                <td colspan="2">Su Factura la puede pasar recogiendo</td>
                            </tr>
                            <tr>
                                <td colspan="2">a nuestras oficinas.</td>
                            </tr>
                            <tr>
                                <td colspan="2">&nbsp;</td>
                            </tr>
                        </table>
                    </div>';
    
            // $div .= '<div style="width: 100%; margin-top: -8px;">';
            // $div .= '<span style="font-size: 8px; font-weight: bold;">SU FACTURA LA PUEDE RECOGER EN OFICINAS</span>';
            // $div .= '</div>';
            $div .= '</div>';
            $div .= '</div>';
            $div .= '</div>';
            // $div .= '</div>';
    
        } else {
    
            $div .= '<table align="left" style="font-size: 10px; width:58mm;" border=0px>'; //table cabecera
    
            /*$div .= '<tr>';
            $div .= '<td align="center" style="font-family: Courier;"><img width="58mm;" height="70px;" src="' . $empr_path_logo . '"></td>';
            $div .= '</tr>';*/
    
            $div .= '<tr>';
            $div .= '<td align="center" style="font-size: 13px; font-family: Courier; width: 10mm;">RUC ' . $ruc_empr . '</td>';
            $div .= '</tr>';
    
            /* $div .= '<tr>';
            $div .= '<td align="center" style="font-size: 13px; font-family: Courier;">MATRIZ:</td>';
            $div .= '</tr>'; */
    
    
            $div .= '<tr>';
            $div .= '<td align="center" style="font-size: 11px; font-family: Courier;width=58mm;">' . $dir_empr . '</td>';
            $div .= '</tr>';
    
            /*
            $div .= '<tr>';
            $div .= '<td align="center" style="font-size: 13px; margin-bottom: 3px; font-family: Courier; width=58mm;">SUCURSAL:</td>';
            $div .= '</tr>';
            $div .= '<tr>';
            $div .= '<td align="center" style="font-size: 11px; margin-bottom: 3px; font-family: Courier;width=58mm;">' . $sucu_dir_sucu . '</td>';
            $div .= '</tr>';
            $div .= '<tr>'; */
            $div .= '<td align="center" style="font-size: 13px; margin-bottom: 3px; font-family: Courier;width=58mm;">Tel.: ' . $sucu_telf_secu . '</td>';
            $div .= '</tr>';
            $div .= '<tr>';
            $div .= '<td align="center" style="font-size: 10px; margin-bottom: 1px; font-family: Courier;width=58mm;">Email: ' . $sucu_email_secu . '</td>';
            $div .= '</tr>';
    
    
    
            $div .= '</table>';
    
    
            $div .= '<table align="left" style="font-size: 10px; width:58mm;" border=0px>';
    
            //$div .= '<tr><td colspan="2"></td></tr>';
    
            $div .= '<tr>
                    <td  align="center" colspan="2"><h5 style="font-family: Courier; margin: 3px;width:58mm;">'.$reciboComp.'</h5></td>
                </tr>';
    
            /*$div .= "<tr>";*/
            $div .= "<tr>";
            $div .= '<td style="font-size: 10px; font-family: Courier;width:58mm;" align="center" colspan="2">' . $fact_nse_fact . ' - ' . $fact_num_preimp . '</td>';
            $div .= "</tr>";
    
            $div .= "<tr>";
            $div .= '<td style="font-size: 13px; font-family: Courier;width:58mm;" colspan="2"  align="center">*** ORIGINAL ***</td>';
            $div .= "</tr>";
            $div .= "<tr>";
            $div .= '<td style="font-size: 10px; font-family: Courier;width:58mm;" colspan="2" align="center">' . $fact_fech_fact . ' ' . $fact_hor_fin . '</td>';
            $div .= "</tr>";
    
    
            $div .= '<tr>
                        <td style="font-size: 10px; font-family: Courier;width:40mm;" colspan="2" >-------------------------------------</td>
                    </tr>';
            // $fact_nom_cliente = explode(" ", $fact_nom_cliente);
            $div .= "<tr>";
            $div .= '<td style="font-size: 13px; font-family: Courier;width:58mm;" colspan="2"  align="center">Cliente: </td>';
            $div .= "</tr>";
            $div .= "<tr>";
            $div .= '<td style="font-size: 13px; font-family: Courier;width:58mm;" colspan="2"  align="center"><b>' . $fact_nom_cliente . '</b></td>';
            // $div .= '<td style="font-size: 10px; font-family: Courier; width:10mm;" align="center"><b>' . htmlentities($fact_nom_cliente[2].' '.$fact_nom_cliente[3]) . '</b></td>';
            $div .= "</tr>";
    
    
            $div .= "<tr>";
            $div .= '<td style="font-size: 10px; font-family: Courier; width: 10mm;" align="center">Contrato:</td>';
            $div .= '<td style="font-size: 10px; font-family: Courier; width: 29mm;" align="center"><b>' . $codigo . '</b></td>';
            $div .= "</tr>";
    
    
            /* $div .= "<tr>";
            $div .= '<td style="font-size: 10px; font-family: Courier; width: 10mm;" align="center">Direccion:</td>';
            $div .= '<td style="font-size: 6px; font-family: Courier; width: 29mm;" align="center"><b>' . substr($direccion, 0, 20) . '</b></td>';
            $div .= "</tr>"; */
    
            $div .= "<tr>";
            $div .= '<td style="font-size: 10px; font-family: Courier; width: 10mm;" align="center">Telefono:</td>';
            $div .= '<td style="font-size: 10px; font-family: Courier; width: 29mm;" align="center"><b>' . substr($telefono, 0, 20) . '</b></td>';
            $div .= "</tr>";
    
            $div .= '<tr>
                    <td style="font-size: 12px; font-family: Courier;width: 58mm;" colspan="2">------------------------------</td>
                </tr>';
    
    
            $div .= "</table>";
    
            $div .= "<table align='left' style='font-size: 12px; font-family: Courier;width: 58mm;'>";
            $div .= "<tr>";
            $div .= '<td style="font-size: 10px; font-family: Courier;" align="center">Descripcion:</td>';
            $div .= "</tr>";
            $div .= "<tr>";
            $div .= '<td align="center" style="font-size: 10px; margin-bottom: 1px; font-family: Courier;width=58mm;">*' . strtolower($detalle1) . '</b></td>';
            $div .= "</tr>";
            $div .= "<tr>";
            $div .= '<td align="center" style="font-size: 10px; margin-bottom: 1px; font-family: Courier;width=58mm;">' . strtolower($detalle) . '</b></td>';
            $div .= "</tr>";
            /* $div .= '<tr>
                        <td align="center" style="font-size: 10px; font-family: Courier; width:60px;">' . $moneda . '</td>
                        <td align="center" style="font-size: 10px; font-family: Courier; width:50px;"><b>' . number_format($venta, 2, '.', ',') . '</b></td>
                    </tr>'; */
    
    
            $div .= "</table>";
    
            $cero = 0;
            //objeto contratos
            $Contratos = new Contratos($oCon, $oIfx, $idempresa, $fact_cod_sucu, $fact_cod_clpv, $fact_cod_contr);
            $deuda = $Contratos->consultaMontoMesAdeuda();
            //<tr>
            //<td style="font-size: 13px; font-family: Courier; width:150px;" align="right" >Saldo Anterior:</td>
            //<td style="font-size: 13px; font-family: Courier; width:50px;" align="right"><b>' . number_format($fact_cm5_fac, 2, '.', ',') . '</b></td>
            //</tr>
    
            $usuario = explode(" ", $usuario);
            $div .= '<table align="left">
                       
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">Efectivo ' . $moneda . ':</td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($array_fxfp['EFE'], 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">Tarjeta ' . $moneda . ':</td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($array_fxfp['TAR'], 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">Cheque ' . $moneda . ':</td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($array_fxfp['CHE'], 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">Deposito ' . $moneda . ':</td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($array_fxfp['DEP'], 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right" ><b>Total ' . $moneda . ':<b></td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($venta, 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">Pagado ' . $moneda . ':</td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($fact_cm8_fac, 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">Cambio ' . $moneda . ':</td>
                            <td style="font-size: 10px; font-family: Courier;" align="center"><b>' . number_format($fact_cm9_fac, 2, '.', ',') . '</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">- Recibo no</td>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">- Abono a cuenta</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">  valido sin firma</td>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">  no evita .</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">  y sello.</td>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">  suspension.</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">User: </td>
                            <td style="font-size: 10px; font-family: Courier; width:110px;" align="right">' . $usuario[0] . ' '. $usuario[2] .'</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; font-family: Courier; width:110px;" align="center" colspan="2"><h5 style="font-family: Courier; margin-top:10px;">GRACIAS POR SU PAGO!!</h5></td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; font-family: Courier; width:110px;" align="center" colspan="2"><h5 style="font-family: Courier; margin-top:10px;">_____________________</h5></td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; font-family: Courier; width:110px;" align="center" colspan="2"><h5 style="font-family: Courier; margin:0px;">Recibido Conforme</h5></td>
                        </tr>
                    </table>';
    
            $div .= "<table align='left'>";
            $div .= "<tr>";
            $div .= '<tr><td colspan="2"></td></tr>';
            $div .= '<tr><td colspan="2"></td></tr>';
            $div .= '<tr><td colspan="2"></td></tr>';
            $div .= "</tr>";
            $div .= "</table>";
    
            /* $div .= "<div align='left' style='margin-top: 10px;'>
                        <p style='margin: 0px; font-family: Courier; font-size: 10px;'>- Recibo no valido sin firma y sello.</p>
                        <p style='margin: 0px; font-family: Courier; font-size: 10px;'>- Abono a cuenta no evita suspension.</p>
                    </div>"; */
            /* $div .= "<div align='left' style='margin-top: 0x;'>
                        <h5 style='font-family: Courier; margin:0px;'>GRACIAS POR SU PAGO!!</h5>
                    </div>"; */
            $div .= '</div>'; //fin div 2
    
            /* $div .= '<div style="margin-top: 20px;">
                        <table align="left">
                            <tr>
                                <td align="center" style="margin: 0px; font-family: Courier;">_____________________</td>
                            </tr>
                            <tr>
                                <td align="center" style="margin: 0px; font-family: Courier;">Recibido Conforme</td>
                            </tr>
                        </table>
                    </div>'; */
    
            $div .= '</div>'; //fin div padre
        }
    
    } else {
        $table = '<div>No existe Factura...</div>';
    }
    
    //arma pdf
    $table .= $div;
    echo $table;
    return $ruta;

}


?>