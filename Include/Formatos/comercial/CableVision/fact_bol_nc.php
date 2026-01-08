


<?

function reporte_factura_personalizado($id = '', $nombre_archivo = '', $idSucursal ='', &$rutaPdf = '') {
    global $DSN_Ifx, $DSN;
    include_once DIR_FACTELEC."Include/Librerias/barcode1/vendor/autoload.php";
    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();


    $idEmpresa = $_SESSION['U_EMPRESA'];
    //$idSucursal = $_SESSION['U_SUCURSAL'];
    $sql = "select empr_iva_empr, empr_cod_pais,  * from saeempr where empr_cod_empr = $idEmpresa ";
    $empr_cod_pais = round(consulta_string($sql, 'empr_cod_pais', $oIfx, 0));
   
       // IMPUESTOS POR PAIS
       $sql = "select p.impuesto, p.etiqueta, p.porcentaje from comercial.pais_etiq_imp p where
       p.pais_cod_pais = $empr_cod_pais ";
       unset($array_imp);
       unset($array_porc);
       if ($oIfx->Query($sql)) {
           if ($oIfx->NumFilas() > 0) {
               do {
                       $impuesto      = $oIfx->f('impuesto');
                       $etiqueta     = $oIfx->f('etiqueta');
                       $porcentaje = $oIfx->f('porcentaje');
                       $array_imp[$impuesto] = $etiqueta;
                       $array_porc[$impuesto] = $porcentaje;
   
                   }while ($oIfx->SiguienteRegistro());
           }
       }
       $oIfx->Free();

  $sql = "SELECT clpv_cod_clpv, clv_con_clpv from saeclpv";
  $arrayTipDoc = array_dato($oCon, $sql, 'clpv_cod_clpv', 'clv_con_clpv');


    $sql = "select empr_web_color, empr_cod_pais,empr_cm1_empr, empr_rimp_sn, empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_img_rep, empr_iva_empr,empr_tel_resp, empr_ac1_empr, empr_ac2_empr, empr_mai_empr, empr_tip_empr
                                            from saeempr where empr_cod_empr = $idEmpresa ";


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_img_rep');
            $tel_empresa = $oIfx->f('empr_tel_resp');
            $empr_mai_empr = $oIfx->f('empr_mai_empr');
            if ($oIfx->f('empr_conta_sn') == 'S')
                $empr_conta_sn = 'SI';
            else
                $empr_conta_sn = 'NO';
            $empr_web_color = $oIfx->f('empr_web_color');
            $empr_rimp_sn = $oIfx->f('empr_rimp_sn');
            $empr_num_resu = $oIfx->f('empr_num_resu');
            $empr_iva_empr = $oIfx->f('empr_iva_empr');
            $empr_ac1_empr = $oIfx->f('empr_ac1_empr');
            $empr_ac2_empr = $oIfx->f('empr_ac2_empr');
            $empr_cm1_empr = $oIfx->f('empr_cm1_empr');
            $empr_cod_pais = $oIfx->f('empr_cod_pais');
            $empr_tip_empr = $oIfx->f('empr_tip_empr');
        }
    }
    $oIfx->Free();

    if(empty($empr_web_color)){
        $empr_web_color='black';
    }


    

    //  AMBIENTE - EMISION
    $sql = "select sucu_tip_ambi, sucu_tip_emis, sucu_telf_secu  from saesucu where sucu_cod_empr = $idEmpresa and sucu_cod_sucu = $idSucursal ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $ambiente_sri = $oIfx->f('sucu_tip_ambi');
            $emision_sri = $oIfx->f('sucu_tip_emis');
            $sucu_telf_secu = $oIfx->f('sucu_telf_secu');
        }
    }
    $oIfx->Free();

    //VALIDACION SUSCURSALES

    $sqls="select count(*) as cont from saesucu";
    $contsucu=consulta_string($sqls,'cont',$oIfx,0);

    if ($ambiente_sri == 1) {
        $ambiente_sri = 'PRUEBAS';
    } elseif ($ambiente_sri == 2) {
        $ambiente_sri = 'PRODUCCION';
    }

    if ($emision_sri == 1) {
        $emision_sri = 'NORMAL';
    } elseif ($emision_sri == 2) {
        $emision_sri = 'POR INDISPONIBLIDAD DEL SISTEMA';
    }

    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;


    //CABECERA DE LA FACTURA
    
    // $path_logo_img = DIR_FACTELEC . 'imagenes/logos/' . $path_img[$count];
    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];


    if (file_exists($path_logo_img)) {
        $logo_empresa='<img width="100px;"  src="' . $path_logo_img . '">';
    }
    else{
        $logo_empresa='<div style="color:red;">LOGO NO CARGADO</div>';
    }


   
    ////DATOS DE LA FACTURA

    $sqlFac = "select * from saefact where fact_cod_fact = $id;";
    if ($oIfx->Query($sqlFac)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $fact_nse_fact = $oIfx->f('fact_nse_fact');
                $nse_fact = substr($fact_nse_fact, 3, 9);
                $tipo_pdf=substr($fact_nse_fact, 3, 1);
                

                if($tipo_pdf=='F'){
                    $titulo='FACTURA ELECTRÓNICA';
                    $idcli='RUC:';  
                }
                elseif($tipo_pdf=='B'){
                    $titulo='BOLETA DE VENTA <br> ELECTRÓNICA';
                    $idcli='DNI:';
                }
          
                $fact_num_preimp = $oIfx->f('fact_num_preimp');
                $fact_auto_sri = $oIfx->f('fact_auto_sri');
                $fact_fech_sri = $oIfx->f('fact_fech_sri');
                $fact_nom_cliente = $oIfx->f('fact_nom_cliente');
                $fact_fech_fact = fecha_mysql_func($oIfx->f('fact_fech_fact'));
                $fact_fech_fact =date('d/m/Y',strtotime($fact_fech_fact));
                $fact_ruc_clie = $oIfx->f('fact_ruc_clie');
                $fact_tlf_cliente = $oIfx->f('fact_tlf_cliente');
                $fact_dir_clie = htmlentities($oIfx->f('fact_dir_clie'));
                $fact_email_clpv = str_replace(' ', '', $oIfx->f("fact_email_clpv"));
                $fact_con_miva = $oIfx->f('fact_con_miva');
                $fact_cod_mone = $oIfx->f('fact_cod_mone');

                $fact_val_tcam = $oIfx->f('fact_val_tcam');
                
                $sql = "select mone_des_mone, mone_sgl_mone, mone_smb_mene from saemone where mone_cod_mone =  $fact_cod_mone;";
                $moneda= consulta_string($sql,'mone_des_mone', $oCon,'');
                $smbmone= consulta_string($sql,'mone_smb_mene', $oCon,'');
                $sigmone= consulta_string($sql,'mone_sgl_mone', $oCon,'');
               

                ///VALIDACION MONEDA
                $sqlmon="select pcon_mon_base, pcon_seg_mone from saepcon where pcon_cod_empr=$idEmpresa";
                $pcon_seg_mone= consulta_string($sqlmon,'pcon_seg_mone', $oCon,'');

                $pcon_mon_base=consulta_string($sqlmon,'pcon_mon_base', $oCon,'');

                if($fact_cod_mone!=$pcon_mon_base){
                $eti_mone=  $sigmone.$smbmone;
                }
                else{
                    $eti_mone= $smbmone;
                }

                //ETIQUETA LOCAL
                $sql = "select mone_des_mone, mone_sgl_mone, mone_smb_mene from saemone where mone_cod_mone =  $pcon_mon_base;";
                $sigmoneprin= consulta_string($sql,'mone_smb_mene', $oCon,'');
                
                $fact_cod_hash = $oIfx->f('fact_cod_hash');

                $fact_sin_miva = $oIfx->f('fact_sin_miva');
                $fact_tot_fact = $oIfx->f('fact_tot_fact');
                $fact_iva = $oIfx->f('fact_iva');
                $fact_ice = $oIfx->f('fact_ice');
                $fact_clav_sri  = $oIfx->f('fact_clav_sri');
                $fact_dsg_valo  = $oIfx->f('fact_dsg_valo');
                $fact_cm1_fact  = strtoupper(trim($oIfx->f("fact_cm1_fact")));
                $fact_cm2_fact  = $oIfx->f("fact_cm2_fact");
                $fact_cm4_fact  = $oIfx->f("fact_cm4_fact");
                $orden_compra   = $oIfx->f("fact_opc_fact");
                $fact_cod_clpv  = $oIfx->f("fact_cod_clpv");
                $fact_cod_detra  = $oIfx->f("fact_cod_detra");
                $cod_contrato =  $oIfx->f('fact_cod_contr');
                if(empty($cod_contrato)){
                    $cod_contrato='NULL';
                }

                //CODIGO CID CLIENTES

                $sql_cid="select codigo_cid from  isp.int_contrato_caja_pack where id_clpv=$fact_cod_clpv and id_contrato = $cod_contrato
                and estado in ('A','C','P') and activo='S'";
                if ($oIfxA->Query($sql_cid)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $codigo_cid= $oIfxA->f('codigo_cid').'<br>';
                        }while ($oIfxA->SiguienteRegistro());
                    }

                }
                $oIfxA->Free();

                             

                $tipo_doc = $arrayTipDoc[$fact_cod_clpv];

                if (intval($tipo_doc) == 1) {
                    $tipo_docu = '6';
                    $tipo_envio = '01';
                } else if (intval($tipo_doc) == 2) {
                    $tipo_docu = '1';
                    $tipo_envio = '03';
                } else if (intval($tipo_doc) == 3) {
                    $tipo_docu = '7';
                    $tipo_envio = '03';
                } else {
                    $tipo_docu = '0';
                }



                $fact_cod_ccli  = $oIfx->f("fact_cod_ccli");
                $fact_dia_plazo = $oIfx->f("fact_dia_fact");
                $fact_fech_venc = fecha_mysql_func($oIfx->f("fact_fech_venc"));
                $fact_fech_venc =date('d/m/Y',strtotime($fact_fech_venc));
                $fact_cm3_fact  = $oIfx->f("fact_cm3_fact");
                $fact_cod_contr = $oIfx->f("fact_cod_contr");


              

                //$fact_tip_vent = $oIfx->f("fact_tip_vent");
               // $sql = "select tcmp_cod_tcmp, tcmp_des_tcmp from saetcmp where tcmp_cod_tcmp = '$fact_tip_vent';";
                //$tipo_text_fac= consulta_string($sql,'tcmp_des_tcmp', $oCon,'FACTURA');


                $logo ='<table border="0"  style="width: 100%;"  cellspacing="0">';
                $logo .= '<tr>';
            
                $logo .= '<td align="left" width="420">';
                $logo .= '<table  style="margin: 0px;">';
                $logo .= '<tr>';
                $logo .= '<td align="center">'.$logo_empresa.'</td>';
                $logo .= '<td width="335" style="font-size:15px;"><div style="margin-left:10px"><b>' . $razonSocial . '</b><br>'.$dirMatriz.'</div></td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
                
                $logo .= '<td align="left" width="260">';
                $logo .= '<table  style="border: '.$empr_web_color.' 1px solid ; border-radius: 5px; " cellspacing=0>';
            
                $logo .= '<tr style="font-size:16px;">';
                $logo .= '<td width="260" height="35" align="center"><b>R.U.C. N° ' . $ruc_empr . '</b></td>';
                $logo .= '</tr>';
            
                $logo .= '<tr style="font-size:16px;">';
                $logo .= '<td width="260"  height="35" style="background: '.$empr_web_color.'; color:white;" align="center"><b>'.$titulo.'</b></td>';
                $logo .= '</tr>';
                
                $logo .= '<tr style="font-size:16px;">';
                $logo .= '<td width="260" height="35" align="center" ><b>Nro. '.$nse_fact.'-' . $fact_num_preimp . '</b></td>';
                //$logo .= '<td align="center" width=220>Nro. '.$nse_fact.'-0000000000000012</td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
            
                $logo .= '</tr>';
                $logo.='</table>';


                $logo .='<table border="0" style="width: 80%; margin-top:4px;font-size:12px;">';
                $logo .= '<tr>';
            
                $logo .= '<td  width="424" >';
                $logo .= '<table  style="margin: 0px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px; width:100%;" >';
                $logo .= '<tr>';
                $logo .= '<td width="424"><div style="margin-left:3px"><b>Facturado a:</b> '.$fact_nom_cliente.'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="424"><div style="margin-left:3px"><b>Dirección:</b> '.$fact_dir_clie .'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="424"><div style="margin-left:3px"><b>'.$idcli.'</b>  '.$fact_ruc_clie.'</div></td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
            
                $logo .= '<td align="center" width="1"></td>';
            
                $logo .= '<td  width="257">';
                $logo .= '<table  style="margin: 0px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px; width:100%;" >';
                $logo .= '<tr>';
                $logo .= '<td width="257"><b>Fecha de emisi&oacute;n:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$fact_fech_fact.'</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="257"><b>Fecha de vencimiento:</b>&nbsp;&nbsp;'. $fact_fech_venc.'</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="257"><b>Moneda:</b> '.$moneda.'</td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
            
                $logo .= '</tr>';
                $logo.='</table>';




            }while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $titulo=str_replace('<br>',' ',$titulo);
    //DETALLE DE LA FACTURA
    
    $sqlDeta = "select * from saedfac where dfac_cod_fact = $id and 
                dfac_cod_sucu = $idSucursal and 
                dfac_cod_empr = $idEmpresa  ";

    $deta .= ' <table style="width: 99%;  font-size: 13px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:10px;margin-left:2px;" cellspacing=0>';
    $deta .= ' <tr>';
    $deta .= ' <b> <td style="border-top-left-radius: 4px; background: '.$empr_web_color.'; color:white; width: 55%; font-size:13px;" align="center" height="30">DESCRIPCIÓN</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 5%; font-size:13px;" align="center" height="30">UM</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width:  14%; font-size:13px;" align="center" height="30">CANTIDAD</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width:  12%; font-size:13px;" align="center" height="30">PRECIO</td> </b>';
    $deta .= ' <b> <td style="border-top-right-radius: 4px; background: '.$empr_web_color.'; color:white; width: 14%; font-size:13px;" align="center" height="30">IMPORTE</td> </b>';
    $deta .= ' </tr>';

    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            $ctrl=0;
            $porcIva = '';
            $tot_opgrav=0;
            $tot_opinafe=0;
            $tot_opexo=0;
            do {
                $dfac_cod_prod = $oIfx->f('dfac_cod_prod');
                $dfac_nom_prod = $oIfx->f('dfac_nom_prod');
                $dfac_cant_dfac = $oIfx->f('dfac_cant_dfac');
                $dfac_precio_dfac = $oIfx->f('dfac_precio_dfac');

               

                $dfac_des1_dfac = $oIfx->f('dfac_des1_dfac');
                $dfac_des2_dfac = $oIfx->f('dfac_des2_dfac');
                $dfac_por_dsg = $oIfx->f('dfac_por_dsg');
                $dfac_mont_total = $oIfx->f('dfac_mont_total');
                $dfac_num_comp = $oIfx->f('dfac_tip_dfac');
                $dfac_por_iva = $oIfx->f('dfac_por_iva');
                $dfac_det_dfac = $oIfx->f('dfac_det_dfac');

                //CAMPOS NUEVOS GRABADAS Y NO AFECTAS
                $dfac_obj_iva = $oIfx->f('dfac_obj_iva');
                $dfac_exc_iva = $oIfx->f('dfac_exc_iva');
                

                $dfac_cod_unid = $oIfx->f('dfac_cod_unid');
                if(!empty($dfac_cod_unid)){
                $sqlu="select unid_sigl_unid from saeunid where unid_cod_unid=$dfac_cod_unid";
                $unidad= consulta_string($sqlu,'unid_sigl_unid', $oCon,'');
                }
                else{
                    $unidad='';
                }

                if ($dfac_por_iva > 0) {
                    $porcIva = $dfac_por_iva;
                }

                $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;
                if ($descuento > 0)
                    $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                else
                    $descuento = 0;


                $totalDescuento = $totalDescuento + $descuento;


                if ($dfac_por_dsg > 0) {
                    $descuento1 = ($dfac_precio_dfac * $dfac_cant_dfac * $dfac_des1_dfac) / 100;
                    $descuento2 = 0;
                    $descuento3 = 0;

                    $dfac_mont_total = number_format((($dfac_precio_dfac * $dfac_cant_dfac) - ($descuento1 + $descuento2 + $descuento3)), 2, '.', '');

                }

                if($fact_cod_mone!=$pcon_mon_base){
                    $dfac_precio_dfac=$dfac_precio_dfac/$fact_val_tcam;
                    $dfac_mont_total=$dfac_mont_total/$fact_val_tcam;
                }

                

                if($dfac_obj_iva ==0 && $dfac_exc_iva!=1){
                    $tot_opgrav+=$dfac_precio_dfac;
                }
                elseif($dfac_obj_iva ==1 && $dfac_exc_iva!=1){
                    $tot_opinafe+=$dfac_precio_dfac;
                   
                }
                else{
                    $tot_opexo+=$dfac_precio_dfac;
                
                }

               



                $deta .= ' <tr>';
                $deta .= ' <td style="width: 55%;">'.$dfac_det_dfac.'</td>';
                $deta .= ' <td style="width: 5%;" align="center" style="border-left: '.$empr_web_color.' 0.7px solid;">' . $unidad . '</td>';
                $deta .= ' <td style="width: 14%;" align="right" style="border-left: '.$empr_web_color.' 0.7px solid;">' . number_format($dfac_cant_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td style="width: 14%;" align="right" style="border-left: '.$empr_web_color.' 0.7px solid;">' . number_format($dfac_precio_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td style="width: 14%;" align="right" style="border-left: '.$empr_web_color.' 0.7px solid;">' . number_format($dfac_mont_total, 2, '.', ',') . '</td>';
                $deta .= ' </tr>';
                $ctrl++;
            }while ($oIfx->SiguienteRegistro());
             $deta .= ' <tr>';
                $deta .= ' <td style="width: 55%;" align="justify">'.$fact_cm1_fact.'</td>';
                $deta .= ' <td style="width: 5%;" align="center" style="border-left: '.$empr_web_color.' 0.7px solid;"></td>';
                $deta .= ' <td style="width: 14%;" align="right" style="border-left: '.$empr_web_color.' 0.7px solid;"></td>';
                $deta .= ' <td style="width: 14%;" align="right" style="border-left: '.$empr_web_color.' 0.7px solid;"></td>';
                $deta .= ' <td style="width: 14%;" align="right" style="border-left: '.$empr_web_color.' 0.7px solid;"></td>';
                $deta .= ' </tr>';
            for ($i=$ctrl; $i <=100 ; $i++) { 
                # code...
                $deta .= ' <tr>';
                $deta .= ' <td style="width: 55%;"></td>';
                $deta .= ' <td style="width: 5%;" align="center" style="border-left: '.$empr_web_color.' 0.7px solid;"></td>';
                $deta .= ' <td style="width: 14%;" align="right" style="border-left: '.$empr_web_color.' 0.7px solid;"></td>';
                $deta .= ' <td style="width: 12%;" align="right" style="border-left: '.$empr_web_color.' 0.7px solid;"></td>';
                $deta .= ' <td style="width: 14%;" align="right" style="border-left: '.$empr_web_color.' 0.7px solid;"></td>';
                $deta .= ' </tr>';
            }
        }
    }

    $deta .= ' </table>';


    $total = number_format($fact_con_miva + $fact_sin_miva + $fact_iva + $fact_fle_fact + $fact_otr_fact + $fact_fin_fact - $totalDescuento, 2, '.', '');
    $V = new EnLetras();
    if($fact_cod_mone!=$pcon_mon_base)
    {
        $total = $total/$fact_val_tcam;
        $con_letra = strtoupper($V->ValorEnLetras($total, $moneda));
    }
    else{
        $con_letra = strtoupper($V->ValorEnLetrasMone($total, $moneda));
    }
    
   

    $tablepago='';

 

    $tablepago.='<table  style="width:100%" border="0" cellspacing="5">';
    $tablepago .= '<tr>';


    $tablepago .= '<td valign="top" width="347">';

    //query forma de pago
    $sqlFPago = "select fx.fxfp_cod_fxfp, fx.fxfp_fec_fin, fp.fpag_cod_fpagop, fx.fxfp_val_fxfp, fx.fxfp_num_dias,
				fpg.fpagop_des_fpagop,fp.fpag_des_fpag
				from saefact f, saefxfp fx, saefpag fp, saefpagop fpg
				where 
                f.fact_cod_fact = fx.fxfp_cod_fact and
				fp.fpag_cod_fpag = fx.fxfp_cod_fpag and
                f.fact_cod_empr = fpg.fpagop_cod_empr and
				fp.fpag_cod_fpagop = fpg.fpagop_cod_fpagop and
				f.fact_cod_empr = $idEmpresa and
				f.fact_cod_sucu = $idSucursal and
				f.fact_cod_fact =$id order by 1";
     
    if ($oIfx->Query($sqlFPago)) {
        if ($oIfx->NumFilas() > 0) {
            $tablepago .= '<table style="  font-size: 11px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:5px;" cellspacing="0">';
            $tablepago .= '<tr>';
            $tablepago .= '<td  style="border-top-left-radius: 4px;background: '.$empr_web_color.'; color:white;" width="111" ><b>CUOTA</b></td>';
            $tablepago .= '<td style="background: '.$empr_web_color.'; color:white;" width="111" ><b>FECHA</b></td>';
            $tablepago .= '<td  style="border-top-right-radius: 4px; background: '.$empr_web_color.'; color:white;" width="111" ><b>IMPORTE</b></td>';
            $tablepago .= '</tr>';    
           
            do {
                $fpag_cod_fpagop    = $oIfx->f('fpag_cod_fpagop');
                $fxfp_val_fxfp      = $oIfx->f('fxfp_val_fxfp');
                if($fact_cod_mone!=$pcon_mon_base)
                {
                    $fxfp_val_fxfp = $fxfp_val_fxfp/$fact_val_tcam;
                }
                $fxfp_num_dias      = $oIfx->f('fxfp_num_dias');
                $fpagop_des_fpagop  = $oIfx->f('fpag_des_fpag');
                $fxfp_cod_fxfp   = $oIfx->f('fxfp_cod_fxfp');
                $fxfp_fec_fin = date('d/m/Y',strtotime($oIfx->f('fxfp_fec_fin')));

                $tablepago .= '<tr>';
                $tablepago .= '<td width="111" style=" border-right: '.$empr_web_color.' 0.5px solid; border-bottom: '.$empr_web_color.' 0.5px solid;" align="right">'.$fxfp_cod_fxfp.'</td>';
                $tablepago .= '<td width="111" style=" border-right: '.$empr_web_color.' 0.5px solid;border-bottom: '.$empr_web_color.' 0.5px solid;" align="center">'.$fxfp_fec_fin.'</td>';
                $tablepago .= '<td width="111" style=" border-bottom: '.$empr_web_color.' 0.5px solid;" align="right">'.number_format($fxfp_val_fxfp, 2, '.', ',').'</td>';
                $tablepago .= '</tr>';
             
            } while ($oIfx->SiguienteRegistro());

            $tablepago.='</table>';
   
        
        }
        else{
            $tablepago .='<span><font style="color:red" >FORMA DE PAGO NO INGRESADA</font></span><br>';
        }
    }
    $oIfx->Free();


    $tablepago .= '</td>';
    $tablepago .= '</tr>';
    $tablepago .= '</table>';




    $fact_tot_fact = $fact_con_miva + $fact_iva+$fact_sin_miva + $fact_val_irbp;

    
    $totales ='<table style="width: 100%;  font-size: 11px; margin-top: 5px;" cellspacing="0"  align="left">';
    $totales .='<tr>
    <td  valign="top" width="390" > 
       
    <table  style="margin: 0px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px; width:100%" >
    <tr>
    <td width="390" >SON: '.$con_letra.'</td>
    </tr>
    <tr>
    <td width="390"><b>Forma Pago:</b> '. $fpagop_des_fpagop.'</td>
    </tr>';
    
    if(!empty($fact_cod_detra)){


        $sql="select tret_porct, tret_mont_min, tret_cod_banc from saetret  where tret_cod='$fact_cod_detra'";
        $fact_porc_detra=consulta_string($sql,'tret_porct',$oIfx,0);
        $tret_mon_min=consulta_string($sql,'tret_mont_min',$oIfx,0);
        $tret_cod_cuen=consulta_string($sql,'tret_cod_banc',$oIfx,0);
        if(empty($tret_cod_cuen)){
            $tret_cod_cuen='NULL';
        }

        $sqlc="select ctab_num_ctab from saectab where ctab_cod_ctab= $tret_cod_cuen";
        $num_cuenta_detra=consulta_string($sqlc,'ctab_num_ctab',$oIfx,'');
        if(empty($num_cuenta_detra)){
            $num_cuenta_detra='00-381-284875';
        }



        if(round($fact_tot_fact,2)>round($tret_mon_min,2)){
            if($fact_cod_mone!=$pcon_mon_base)
            {
                $fact_tot_detra = $fact_tot_fact/$fact_val_tcam;
    
            }
            else{
                $fact_tot_detra = $fact_tot_fact;
            }
           
            //VALOR NETO EN MOENDA EXTRANJERA
            $valdetra_cam=$fact_tot_detra* ($fact_porc_detra/100);
            $valor_neto=$fact_tot_detra-$valdetra_cam;

            //PORCENTAJE DE DETRACCIOJN
            $valdetra=$fact_tot_fact* ($fact_porc_detra/100);

            $totales .='<tr>
            <td width="390"><b>Detracción al '.round($fact_porc_detra,2).'%:</b> '.$sigmoneprin.' '.number_format($valdetra, 2, '.', ',').' </td>
            </tr>
            <tr>
            <td width="390"><b>Neto a Pagar:</b> '.$eti_mone.' '.number_format($valor_neto, 2, '.', ',').'</td>
            </tr>';
        }

        
    }
    //OBSERVACIONES
    /*if(!empty($fact_cm1_fact)){
    $totales .= '<tr>';
    $totales .= '<td width="390"  ><b>Observación:</b> '.$fact_cm1_fact.'</td>';
    $totales .= '</tr>';
    }


    if($fxfp_num_dias>0){
    $totales.='<table  style="margin-top: 5px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px; width:100%" >
    <tr>
    <td width="416" ><b>Condición de Pago: '.$fpagop_des_fpagop.' '.$fxfp_num_dias.' DÃ­as</b></td>
    </tr>
    </table>';
    }*/
    

    if(!empty($fact_cod_detra)){

        $totales .= '<tr>';
        $totales .= '<td style=" border-top: '.$empr_web_color.' 0.5px solid;" ></td>';
        $totales .= '</tr>';
        $totales .= '<tr>';
        $totales .= '<td width="390" height="56" ><b>CUENTA DE DETRACCIÓN: '.$num_cuenta_detra.'</b><br>
        Servicio sujeto a detracción en montos mayores a: <b>S/ 700</b><br>
        Porcentaje de detracción: <b>12%</b>
        </td>';
        $totales .= '</tr>';

    }


    if($fact_cod_mone!=$pcon_mon_base)
    {
        $fact_iva = $fact_iva/$fact_val_tcam;
        $fact_tot_fact = $fact_tot_fact/$fact_val_tcam;
    }
    

    
    $totales .='</table>';
    
    $totales.='</td>

    <td  valign="top" width="0.5" > 
    </td>

    <td valign="top" width="300" >';

    $totales .= ' <table style=" font-size: 11px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px;"   cellspacing="0" align="left">';

    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18" align="right">Total Op. Gravadas '.$eti_mone.'</td> </b>';
    $totales .= ' <td  width="110" align="right">' . number_format($tot_opgrav, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

   

    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18" align="right">Total Op. Inafectas '.$eti_mone.'</td> </b>';
    $totales .= ' <td width="110" align="right">' . number_format( $tot_opinafe, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';


    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18" align="right">Total Op. Exoneradas '.$eti_mone.'</td> </b>';
    $totales .= ' <td width="110" align="right">' . number_format($tot_opexo, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    

    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18" align="right">Total '.$array_imp ['IVA'].' '. number_format($array_porc ['IVA'],2).'%:</td> </b>';
    $totales .= ' <td width="110" align="right">' . number_format($fact_iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    /*$totales .= ' <tr>';
    $totales .= ' <b> <td>ICE:</td> </b>';
    $totales .= ' <td align="right">' . number_format($fact_ice, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td>IRBP:</td> </b>';
    $totales .= ' <td align="right">' . number_format($fact_val_irbp, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';*/
  
    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18" align="right">Total Descuentos '.$eti_mone.'</td> </b>';
    $totales .= ' <td width="110" align="right">' . number_format($fact_dsg_valo, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18" align="right">Total ISC '.$eti_mone.'</td> </b>';
    $totales .= ' <td width="110" align="right">0.00</td>';
    $totales .= ' </tr>';


    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18" align="right">Importe Total '.$eti_mone.'</td> </b>';
    
    $totales .= ' <td width="110" align="right">' . number_format($fact_tot_fact, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    $totales .= ' </table>';


    $totales.='</td>
    </tr>';
    $totales .='</table>';



    $tableLeyenda ='<table border="0"  style="width: 100%;font-size: 11px;" cellspacing="0">';
    $tableLeyenda .= '<tr>';

    $tableLeyenda .= '<td valign="top" width="500">';
    $tableLeyenda .= '<table  height="500" style="font-size: 13px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:10px;" cellspacing="0" >';
    
    $tableLeyenda .= '<tr>';

    //PRUEBA HASH 
    /*$nombre_documento =$ruc_empr . '-' . $tipo_envio . '-' . $fact_nse_fact . '-' . $fact_num_preimp;
    $ruta_xml = 'modulos/envio_documentos/upload/xml/fac_' . $nombre_documento . '.xml';
    $hash= DIR_FACTELEC .$ruta_xml;
    $xml = new SimpleXMLElement( file_get_contents($hash) );

    $fact_cod_hash = utf8_decode(current($xml->xpath("//ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestValue")));

    $sql="UPDATE saefact SET fact_cod_hash='$fact_cod_hash' WHERE fact_cod_fact = $id;";
    $oIfx->QueryT($sql);*/

    $tableLeyenda .= '<td width="500" height="88"><div style="margin-left:5px;margin-top:10px;">Representación impresa de la <b>'.$titulo.'</b><br>
    Consulta tu comprobante en: <a href ="www.sunat.gob.ec" target="_blank"><b>www.sunat.gob.pe</b></a><br>
    Resumen: <b>'.$fact_cod_hash.'</b></div></td>';
    $tableLeyenda .= '</tr>';
    
   


    $tableLeyenda.='</table>';
    $tableLeyenda .= '</td>';


    $tableLeyenda .= '<td valign="top" width="0.5"></td>
    <td valign="top" width="192">';
    $tableLeyenda .= '<table style="  font-size: 11px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:10px;" cellspacing="0" >';

    //CODIGO QR

    $barcode = new \Com\Tecnick\Barcode\Barcode();

    $datosqr=$ruc_empr.'|'.$tipo_envio.'|'.$nse_fact.'|'.$fact_num_preimp.'|'.number_format($fact_iva, 2, '.', ',').'|'.number_format($fact_tot_fact, 2, '.', ',').'|'.$fact_fech_fact.'|'.$tipo_docu.'|'.$fact_ruc_clie;

    $bobj = $barcode->getBarcodeObj(
        'QRCODE,H',                     // Tipo de Barcode o Qr
        $datosqr,          // Datos
        -2.5,                             // Width 
        -2.5,                             // Height
        'black',                        // Color del codigo
        array(-2, -2, -2, -2)           // Padding
        )->setBackgroundColor('white'); // Color de fondo

    $imageData = $bobj->getPngData(); // Obtenemos el resultado en formato PNG
        

    $ruta_dir = DIR_FACTELEC . 'modulos/envio_documentos/qr_facturas';
        if (!file_exists($ruta_dir)){
            mkdir($ruta_dir,0777,true);
        }
        
    file_put_contents(DIR_FACTELEC . 'modulos/envio_documentos/qr_facturas/FAC_'.$id.'.png', $imageData); // Guardamos el resultado
    
    $ruta=DIR_FACTELEC . 'modulos/envio_documentos/qr_facturas/FAC_'.$id.'.png';


    
    $tableLeyenda .= '<tr>';
    $tableLeyenda .= '<td width="192" align="center"><img src="'.$ruta.'"></td>';

    $tableLeyenda .= '</tr>';
    $tableLeyenda.='</table>';
    $tableLeyenda .= '</td>';

    $tableLeyenda .= '</tr>';
    $tableLeyenda .= '</table>';



    
    $tableLeyenda .='<table border="0"  style="width: 85%;" cellspacing="2">';
    $tableLeyenda .= '<tr>';

    
    

    /*CONSULTA CUENTAS CONFIGURADAS POR EMPRESA */ 

    if($tipo_pdf=='B'){
        $doc='BOLETA';
        $tip='BOL';
    }
    elseif($tipo_pdf=='F'){
        $doc='FACTURA';
        $tip='FAC';
    }
    $sql_cont="select count(*) as conteo from saeipdf where ipdf_cod_empr=$idEmpresa and ipdf_tip_ipdf in (select 
    emifa_cod_emifa from saeemifa  where emifa_cod_empr = $idEmpresa
    and emifa_tip_doc = '$tip' and emifa_est_emifa = 'S' 
    and emifa_cod_emifa=ipdf_tip_ipdf) and ipdf_est_deleted ='S'";
    $num_items=consulta_string($sql_cont,'conteo',$oIfx,1);


    $sqlpdf="select * from saeipdf where ipdf_cod_empr=$idEmpresa and ipdf_tip_ipdf in (select 
        emifa_cod_emifa from saeemifa  where emifa_cod_empr = $idEmpresa
        and emifa_tip_doc = '$tip'  and emifa_est_emifa = 'S' 
        and emifa_cod_emifa=ipdf_tip_ipdf) and ipdf_est_deleted ='S'  order by ipdf_ord_ipdf";
        if ($oIfx->Query($sqlpdf)) {
        if ($oIfx->NumFilas() > 0) {
            do{
            $titulo = $oIfx->f('ipdf_tit_ipdf');
            $detalle = $oIfx->f('ipdf_det_ipdf');
                
                    $detalle =str_replace('COD_CLIENTE',$codigo_cid,$detalle);
                
            $formato = $oIfx->f('ipdf_tip_ipdf');
                $width=677/$num_items;
		if($num_items==2){
			$width=688/$num_items;
		}
                $tableLeyenda .= '<td valign="top" style="width: 20%;">';
                $tableLeyenda .= '<table style="  font-size: 11px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:5px;" cellspacing="0">';
                $tableLeyenda .= '<tr>';
                $tableLeyenda .= '<td style="border-top-left-radius: 4px;border-top-right-radius: 4px; background: '.$empr_web_color.'; color:white;" height="25" width="'.$width.'" valign="middle">&nbsp;<b>'.$titulo.'</b></td>';
                $tableLeyenda .= '</tr>';    
                $tableLeyenda .= '<tr>';
                $tableLeyenda .= '<td width="'.$width.'" height="80"><div style="margin-left:3px;margin-top:10px;"><b>'.$detalle.'</b></div></td>';
                $tableLeyenda .= '</tr>';
                $tableLeyenda.='</table>';
                $tableLeyenda .= '</td>';
                $tableLeyenda .= '<td valign="top" style="width: 0.5%;"></td>';

           
            
            
            }while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $tableLeyenda .= '</tr>';
    
    $tableLeyenda .= '</table>';

   


    $legend = '<page_footer>
        <table align="center" style="width: 80%">
            <tr>
                <td style="font-size: 12px; color: #6B6565; background-color: transparent;" align="center">Este comprobante electronico ha sido generado a traves de Sisconti S.A. - Facturacion Electronica</td>
            </tr>
			<tr>
                <td style="font-size: 12px; color: #6B6565; background-color: transparent;" align="center">www.sisconti.com</td>
            </tr>
        </table>
    </page_footer>';

    $documento .= '<page backimgw="100%" backtop="5mm" backbottom="5mm" backleft="5mm" backright="5mm">';
    $documento .= $logo . $cliente . $deta . $totales . $tablePago. $tableLeyenda.$tablepago;
    $documento .= $legend;
    $documento .= '</page>';


    
    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
    $html2pdf->WriteHTML($documento);
    $ruta = DIR_FACTELEC . 'modulos/envio_documentos/upload/pdf/fac_' . $nombre_archivo . '.pdf';
    $html2pdf->Output($ruta, 'F');
    $rutaPdf = $ruta;

    return $documento;
}

function reporte_notaCredito_personalizado($id = '', $nombre_archivo = '', $idSucursal ='', &$rutaPdf = '') {
    global $DSN_Ifx, $DSN;
    include_once DIR_FACTELEC."Include/Librerias/barcode1/vendor/autoload.php";
    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfx2 = new Dbo;
    $oIfx2->DSN = $DSN_Ifx;
    $oIfx2->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();


    $idEmpresa = $_SESSION['U_EMPRESA'];
    //$idSucursal = $_SESSION['U_SUCURSAL'];


    $sql = "select empr_iva_empr, empr_cod_pais,  * from saeempr where empr_cod_empr = $idEmpresa ";
 $empr_cod_pais = round(consulta_string($sql, 'empr_cod_pais', $oIfx, 0));

    // IMPUESTOS POR PAIS
    $sql = "select p.impuesto, p.etiqueta, p.porcentaje from comercial.pais_etiq_imp p where
    p.pais_cod_pais = $empr_cod_pais ";
    unset($array_imp);
    unset($array_porc);
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                    $impuesto      = $oIfx->f('impuesto');
                    $etiqueta     = $oIfx->f('etiqueta');
                    $porcentaje = $oIfx->f('porcentaje');
                    $array_imp[$impuesto] = $etiqueta;
                    $array_porc[$impuesto] = $porcentaje;

                }while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();



    $sql = "SELECT clpv_cod_clpv, clv_con_clpv from saeclpv";
    $arrayTipDoc = array_dato($oCon, $sql, 'clpv_cod_clpv', 'clv_con_clpv');

    $sql = "select empr_web_color, empr_img_rep, empr_cod_pais,empr_cm1_empr, empr_rimp_sn, empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_iva_empr,empr_tel_resp, empr_ac1_empr, empr_ac2_empr, empr_mai_empr, empr_tip_empr
                                            from saeempr where empr_cod_empr = $idEmpresa ";


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_img_rep');
            $tel_empresa = $oIfx->f('empr_tel_resp');
            $empr_mai_empr = $oIfx->f('empr_mai_empr');
            if ($oIfx->f('empr_conta_sn') == 'S')
                $empr_conta_sn = 'SI';
            else
                $empr_conta_sn = 'NO';
            $empr_web_color = $oIfx->f('empr_web_color');
            $empr_num_resu = $oIfx->f('empr_num_resu');
            $empr_iva_empr = $oIfx->f('empr_iva_empr');
            $empr_ac1_empr = $oIfx->f('empr_ac1_empr');
            $empr_ac2_empr = $oIfx->f('empr_ac2_empr');
            $empr_cm1_empr = $oIfx->f('empr_cm1_empr');
            $empr_cod_pais = $oIfx->f('empr_cod_pais');
            $empr_tip_empr = $oIfx->f('empr_tip_empr');
        }
    }
    $oIfx->Free();


    if(empty($empr_web_color)){
        $empr_web_color='black';
    }

    //  AMBIENTE - EMISION
    $sql = "select sucu_tip_ambi, sucu_tip_emis, sucu_telf_secu  from saesucu where sucu_cod_empr = $idEmpresa and sucu_cod_sucu = $idSucursal ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $ambiente_sri = $oIfx->f('sucu_tip_ambi');
            $emision_sri = $oIfx->f('sucu_tip_emis');
            $sucu_telf_secu = $oIfx->f('sucu_telf_secu');
        }
    }
    $oIfx->Free();

    //VALIDACION SUSCURSALES

    $sqls="select count(*) as cont from saesucu";
    $contsucu=consulta_string($sqls,'cont',$oIfx,0);

    if ($ambiente_sri == 1) {
        $ambiente_sri = 'PRUEBAS';
    } elseif ($ambiente_sri == 2) {
        $ambiente_sri = 'PRODUCCION';
    }

    if ($emision_sri == 1) {
        $emision_sri = 'NORMAL';
    } elseif ($emision_sri == 2) {
        $emision_sri = 'POR INDISPONIBLIDAD DEL SISTEMA';
    }

    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;


     //CABECERA DE LA NOTA DE CREDITO
    
    // $path_logo_img = DIR_FACTELEC . 'imagenes/logos/' . $path_img[$count];
    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

    if (file_exists($path_logo_img)) {
        $logo_empresa='<img width="100px;"  src="' . $path_logo_img . '">';
    }
    else{
        $logo_empresa='<div style="color:red;">LOGO NO CARGADO</div>';
    }
    
    ///DATOS DE LA NOTA DE CREDITO


    $sqlFac = "select * from saencre where ncre_cod_ncre = $id and ncre_cod_sucu = $idSucursal and ncre_cod_empr = $idEmpresa ";

    if ($oIfx->Query($sqlFac)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $ncre_nse_ncre = $oIfx->f('ncre_nse_ncre');
                $ncre_num_preimp = $oIfx->f('ncre_num_preimp');
                $ncre_auto_sri = $oIfx->f('ncre_auto_sri');
                $ncre_fech_sri = $oIfx->f('ncre_fech_sri');
                $ncre_nom_cliente = $oIfx->f('ncre_nom_cliente');
                //$ncre_fech_fact = fecha_mysql_func($oIfx->f('ncre_fech_fact'));
                $ncre_fech_fact = $oIfx->f('ncre_fech_fact');
                $ncre_fech_venc = $oIfx->f('ncre_fech_venc');
                $ncre_ruc_clie = $oIfx->f('ncre_ruc_clie');
                $ncre_tlf_cliente = $oIfx->f('ncre_tlf_cliente');
                $ncre_dir_clie = $oIfx->f('ncre_dir_clie');
                $ncre_email_clpv = $oIfx->f('ncre_email_clpv');
                $ncre_con_miva = $oIfx->f('ncre_con_miva');
                $ncre_sin_iva = $oIfx->f('ncre_sin_miva');
                $ncre_cod_fact = $oIfx->f('ncre_cod_fact');
                $ncre_iva = $oIfx->f('ncre_iva');
                $ncre_cm1_ncre = $oIfx->f('ncre_cm1_ncre');
                $ncre_cm2_ncre = $oIfx->f('ncre_cm2_ncre');
                $ncre_clav_sri = $oIfx->f('ncre_clav_sri');
                $ncre_cod_mone = $oIfx->f('ncre_cod_mone');
                $ncre_cod_clpv= $oIfx->f('ncre_cod_clpv');
                $ncre_cod_hash = $oIfx->f('ncre_cod_hash');
                $ncre_fech_docu = $oIfx->f('ncre_fech_docu');
                $ncre_val_tcam = $oIfx->f('ncre_val_tcam');



                $sql = "select mone_des_mone, mone_sgl_mone, mone_smb_mene from saemone where mone_cod_mone =  $ncre_cod_mone;";
                $moneda= consulta_string($sql,'mone_des_mone', $oCon,'');
                $smbmone= consulta_string($sql,'mone_smb_mene', $oCon,'');
                $sigmone= consulta_string($sql,'mone_sgl_mone', $oCon,'');
               
               

                ///VALIDACION MONEDA
                $sqlmon="select pcon_mon_base, pcon_seg_mone from saepcon where pcon_cod_empr=$idEmpresa";
                $pcon_seg_mone= consulta_string($sqlmon,'pcon_seg_mone', $oCon,'');

                $pcon_mon_base=consulta_string($sqlmon,'pcon_mon_base', $oCon,'');

                if($ncre_cod_mone!=$pcon_mon_base){
                $eti_mone=  substr($sigmone,0,2).$smbmone;
                }
                else{
                    $eti_mone= $smbmone;
                }


                $tipo_doc = $arrayTipDoc[$ncre_cod_clpv];
                if (intval($tipo_doc) == 1) {
                    $tipo_docu = '6';
                    $tipo_envio = '01';
                } else if (intval($tipo_doc) == 2) {
                    $tipo_docu = '1';
                    $tipo_envio = '03';
                } else if (intval($tipo_doc) == 3) {
                    $tipo_docu = '7';
                    $tipo_envio = '03';
                } else {
                    $tipo_docu = '0';
                }




                $date = date_create($ncre_fech_venc);
                $ncre_fech_venc = date_format($date,'d/m/Y');

                $date = date_create($ncre_fech_fact);
                $ncre_fech_fact = date_format($date,'d/m/Y');


          

                $logo ='<table border="0"  style=" font-size:13px; width:100%;" cellspacing="0">';
                $logo .= '<tr>';
            
                $logo .= '<td align="left"  width="410">';
                $logo .= '<table  style="margin: 0px;" >';
                $logo .= '<tr>';
                $logo .= '<td align="center">'.$logo_empresa.'</td>';
                $logo .= '<td width="290" style="font-size:14px;"><b>' . $razonSocial . '</b><br>'.$dirMatriz.'<br></td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
                
                $logo .= '<td align="center" width="300">';
                $logo .= '<table  style=" font-size:16px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px; " cellspacing="0">';
            
                $logo .= '<tr>';
                $logo .= '<td width="300" height="35" align="center"><b>R.U.C. N° ' . $ruc_empr . '</b></td>';
                $logo .= '</tr>';
            
                $logo .= '<tr >';
                $logo .= '<td  width="300" height="35" style="background: '.$empr_web_color.'; color:white;" ><b>NOTA DE CRÉDITO ELECTRÓNICA</b></td>';
                $logo .= '</tr>';
                
                $logo .= '<tr>';
                $logo .= '<td width="300" height="35" align="center" ><b>Nro. '.substr($ncre_nse_ncre, 3, 6).'-' . $ncre_num_preimp . '</b></td>';

                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
            
                $logo .= '</tr>';
                $logo.='</table>';


                $logo .='<table border="0" style="font-size: 12px; width: 100%;">';
                $logo .= '<tr>';
            
                $logo .= '<td   width="400">';
                $logo .= '<table  style="margin: 0px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px; width:100%;" >';
                $logo .= '<tr>';
                $logo .= '<td width="400"><b>Facturado a:</b> '.$ncre_nom_cliente.'</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="400"><b>Dirección:</b> '.$ncre_dir_clie .'</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="400"><b>RUC:</b>  '.$ncre_ruc_clie.'</td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
                $logo .= '<td  width="300">';
                $logo .= '<table  style="margin: 0px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px; width:100%;" >';
                $logo .= '<tr>';
                $logo .= '<td width="296"><b>Fecha de emisi&oacute;n:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ncre_fech_fact.'</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="296"><b>Fecha de vencimiento:</b>&nbsp;&nbsp;'. $ncre_fech_venc.'</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="296"><b>Moneda:</b> '.$moneda.'</td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
            
                $logo .= '</tr>';
                $logo.='</table>';

                
                //DATOS DE LA EMPRESA

                if ($ncre_cod_fact == '') {
                    $ncre_cod_fact = 0;
                }

                $sql = "select fact_nse_fact, fact_num_preimp, fact_fech_fact,
						fact_cm2_fact
						from saefact 
						where fact_cod_empr = $idEmpresa and 
						fact_cod_fact = $ncre_cod_fact";
                // var_dump($sql);exit;


                $numero_fac = "";
                if ($oIfx2->Query($sql)) {
                    if ($oIfx2->NumFilas() > 0) {
                        $fact_nse_fact = $oIfx2->f('fact_nse_fact');
                        $fact_num_preimp = $oIfx2->f('fact_num_preimp');
                        $fact_fech_fact = fecha_mysql_func($oIfx2->f('fact_fech_fact'));
                        $fact_cm2_fact = $oIfx2->f('fact_cm2_fact');

                        $nse = substr($fact_nse_fact, 0, 3);
                        $pto = substr($fact_nse_fact, 3, 6);
                        $numero_fac =  $pto . '-' . $fact_num_preimp;
                    } else {
                        if ($ncre_cod_fact == 0) {
                            $sqlNcre = "select ncre_nse_ncre, ncre_cod_aux, ncre_fec_emfa from saencre where 
                                    ncre_cod_ncre = $id and 
                                    ncre_cod_empr = $idEmpresa and 
                                    ncre_cod_sucu = $idSucursal";
                            if ($oIfx2->Query($sqlNcre)) {
                                if ($oIfx2->NumFilas() > 0) {
                                    $fact_nse_fact = $oIfx2->f('ncre_nse_ncre');
                                    $numero_fac = $oIfx2->f('ncre_cod_aux');
                                    //$fact_fech_fact = fecha_sri($oIfx2->f('ncre_fec_emfa'));
                                    //$fact_fech_fact = $oIfx2->f('ncre_fec_emfa');
                                }
                            }
                        }else{
                            $nse = substr($fact_nse_fact, 0, 3);
                            $pto = substr($fact_nse_fact, 3, 6);
                            $numero_fac = $pto . '-' . $fact_num_preimp;
                        }
                    }
                }
                $oIfx2->Free();

                if(!empty($fact_fech_fact)){
                    $date = date_create($fact_fech_fact);
                    $fact_fech_fact = date_format($date,'d/m/Y');
                }

                if(!empty($ncre_fech_docu)){
                    $date = date_create($ncre_fech_docu);
                    $fact_fech_fact = date_format($date,'d/m/Y');
                }
                


                $logo .= '<table  style="font-size: 13px; margin-top: 5px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px; width:100%;" >';
                $logo .= '<tr>';
                $logo .= '<td style=" border-right: '.$empr_web_color.' 0.5px solid;" width="353" align="center"><b>DOCUMENTO ORIGEN</b><br>FAC '.$numero_fac.' '.$fact_fech_fact.'</td>';
                $logo .= '<td width="353" align="center"><b>MOTIVO EMISION</b><br>'.$ncre_cm1_ncre.'</td>';
                $logo .= '</tr>';
                $logo .= '</table>';

                /*$cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 10% "> NUMERO : </td></b>';
                $cliente .= ' <td style="width: 48% ">' .$numero_fac . '</td>';
                $cliente .= ' <b><td style="width: 13% "> FECHA : </td></b>';
                $cliente .= ' <td style="width: 29% ">' . $fact_fech_fact . '</td>';
                $cliente .= ' </tr>';


                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 10% "> MOTIVO : </td></b>';
                $cliente .= ' <td style="width: 48% ">' . $ncre_cm1_ncre . '</td>';
                $cliente .= ' <b><td style="width: 13% "> ORDEN COMPRA : </td></b>';
                $cliente .= ' <td style="width: 29% ">' . $fact_cm2_fact . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' </table>';

                $cliente .= ' <br>';*/
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    //DETALLE DE LA NOTA DE CREDITO

    $sqlDeta = "select * from saedncr where dncr_cod_ncre = $id and dncr_cod_sucu = $idSucursal and dncr_cod_empr = $idEmpresa ";

    $deta .= ' <table style="width: 101%;  font-size: 13px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:10px;" cellspacing="0">';
    $deta .= ' <tr >';
    $deta .= ' <b> <td style="border-top-left-radius: 4px; background: '.$empr_web_color.'; color:white; width: 63%;" height="30" align="center">DESCRIPCIÓN</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 5%;" height="30" align="center">UM</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width:  12%;" height="30" align="center">CANTIDAD</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width:  10%;" height="30" align="center">PRECIO</td> </b>';
    $deta .= ' <b> <td style="border-top-right-radius: 4px; background: '.$empr_web_color.'; color:white; width: 10%;" height="30" align="center">IMPORTE</td> </b>';
    $deta .= ' </tr>';


    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            $ctrl=0;
            $porcIva = '';
            $tot_opgrav=0;
            $tot_opinafe=0;
            $tot_opexo=0;
            do {
                $dncr_cod_prod = $oIfx->f('dncr_cod_prod');
                $dncr_nom_prod = $oIfx->f('dncr_nom_prod');
                $dncr_cant_dfac = $oIfx->f('dncr_cant_dfac');
                $dncr_precio_dfac = $oIfx->f('dncr_precio_dfac');
                $dncr_des1_dfac = $oIfx->f('dncr_des1_dfac');
                $dncr_por_dsg = $oIfx->f('dncr_por_dsg');
                $dncr_mont_total = $oIfx->f('dncr_mont_total');
                $dncr_cod_lote = $oIfx->f('dncr_cod_lote');
                $dncr_num_comp = $oIfx->f('dncr_tip_dncr');
                $dncr_lote_fcad = $oIfx->f('dncr_lote_fcad');
                $dncr_por_iva = $oIfx->f('dncr_por_iva');

                $dncr_obj_iva = $oIfx->f('dncr_obj_iva');
                $dncr_exc_iva = $oIfx->f('dncr_exc_iva');

                $sqln="select dfac_obj_iva, dfac_exc_iva from saedfac where dfac_cod_fact=$ncre_cod_fact and dfac_cod_prod='$dncr_cod_prod'";
                
                $dfac_obj_iva = consulta_string($sqln,'dfac_obj_iva', $oCon,'');
                $dfac_exc_iva = consulta_string($sqln,'dfac_exc_iva', $oCon,'');

                $dncr_cod_unid = $oIfx->f('dncr_cod_unid');
                if(!empty($dncr_cod_unid)){
                $sqlu="select unid_sigl_unid from saeunid where unid_cod_unid=$dncr_cod_unid";
                $unidad= consulta_string($sqlu,'unid_sigl_unid', $oCon,'');
                }
                else{
                    $unidad='';
                }

                if (empty($dncr_num_comp)) {
                    $dncr_num_comp = 0;
                }

              

                if($dncr_por_iva > 0){
                    $porcIva = $dncr_por_iva;
                }

                if (!empty($dncr_lote_fcad)) {
                    //$dncr_lote_fcad = fecha_mysql_func($dncr_lote_fcad);
                    $date = date_create($dncr_lote_fcad);
                    $fact_fech_fact = date_format($date,'d/m/Y');
                }

                if (empty($dncr_nom_prod)) {
                    $sqlProducto = "select prod_nom_prod from saeprod where 
                                        prod_cod_prod = '$dncr_cod_prod' and
                                        prod_cod_empr = $idEmpresa and 
                                        prod_cod_sucu = $idSucursal ";
                    $dncr_nom_prod = consulta_string_func($sqlProducto, 'prod_nom_prod', $oIfx3, '');
                }// fin if

                $descuento = $dncr_des1_dfac + $dncr_por_dsg;
                if ($descuento > 0)
                    $descuento = ($dncr_precio_dfac * $dncr_cant_dfac) - ($dncr_mont_total);
                else
                    $descuento = 0;

               


                if($ncre_cod_mone!=$pcon_mon_base){
                    $dncr_precio_dfac=$dncr_precio_dfac/$ncre_val_tcam;
                    $dncr_mont_total=$dncr_mont_total/$ncre_val_tcam;
                    $descuento=$descuento/$ncre_val_tcam;
                }

                $totalDescuento = $totalDescuento + $descuento;

                if(!empty($ncre_cod_fact)&&$ncre_cod_fact!=0){
                    if($dfac_obj_iva ==0 && $dfac_exc_iva!=1){
                        $tot_opgrav+=$dncr_precio_dfac;
                    }
                    elseif($dfac_obj_iva ==1 && $dfac_exc_iva!=1){
                        $tot_opinafe+=$dncr_precio_dfac;
                      
                    }
                    else{
                        $tot_opexo+=$dncr_precio_dfac;
                    
                    }
                }
                else{

                    if($dncr_obj_iva ==0 && $dncr_exc_iva!=1){
                        $tot_opgrav+=$dncr_precio_dfac;
                    }
                    elseif($dncr_obj_iva ==1 && $dncr_exc_iva!=1){
                        $tot_opinafe+=$dncr_precio_dfac;
                      
                    }
                    else{
                        $tot_opexo+=$dncr_precio_dfac;
                    
                    }

                }
                

              

                $deta .= ' <tr>';
                $deta .= ' <td style="width: 63%;">' . $dncr_nom_prod . '</td>';
                $deta .= ' <td style="width: 5%;" align="center" style="border-left: '.$empr_web_color.' 0.5px solid;">' . $unidad . '</td>';
                $deta .= ' <td style="width: 12%;" align="right"  style="border-left: '.$empr_web_color.' 0.5px solid;">' . number_format($dncr_cant_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td style="width: 10%;" align="right"  style="border-left: '.$empr_web_color.' 0.5px solid;">' . number_format($dncr_precio_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td style="width: 10%;" align="right"  style="border-left: '.$empr_web_color.' 0.5px solid;">' . number_format($dncr_mont_total, 2, '.', ',') . '</td>';
                $deta .= ' </tr>';
                $ctrl++;

            }while ($oIfx->SiguienteRegistro());
                $deta .= ' <tr>';
                $deta .= ' <td style="width: 63%;">' . $ncre_cm2_ncre . '</td>';
                $deta .= ' <td style="width: 5%;" align="center" style="border-left: '.$empr_web_color.' 0.5px solid;"></td>';
                $deta .= ' <td style="width: 12%;" align="right"  style="border-left: '.$empr_web_color.' 0.5px solid;"></td>';
                $deta .= ' <td style="width: 10%;" align="right"  style="border-left: '.$empr_web_color.' 0.5px solid;"></td>';
                $deta .= ' <td style="width: 10%;" align="right"  style="border-left: '.$empr_web_color.' 0.5px solid;"></td>';
                $deta .= ' </tr>';

            for ($i=$ctrl; $i <=180 ; $i++) { 
                # code...
                $deta .= ' <tr>';
                $deta .= ' <td style="width: 63%;"></td>';
                $deta .= ' <td style="width: 5%;" style="border-left: '.$empr_web_color.' 0.5px solid;" align="center"></td>';
                $deta .= ' <td style="width: 12%;" style="border-left: '.$empr_web_color.' 0.5px solid;" align="right"></td>';
                $deta .= ' <td style="width: 10%;" style="border-left: '.$empr_web_color.' 0.5px solid;" align="right"></td>';
                $deta .= ' <td style="width: 10%;" style="border-left: '.$empr_web_color.' 0.5px solid;" align="right"></td>';
                $deta .= ' </tr>';
            }
        }
    }

    $deta .= ' </table>';


    $total_ncre=$ncre_iva + $ncre_con_miva + $ncre_sin_iva - $totalDescuento;
    if($ncre_cod_mone!=$pcon_mon_base){
        $total_ncre=$total_ncre/$ncre_val_tcam;
        $ncre_iva=$ncre_iva/$ncre_val_tcam;
    }

    $total = number_format($total_ncre, 2, '.', '');
    
    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetrasMonePeru($total, $moneda));


    $totales ='<table style="width: 100%;  font-size: 18px; margin-top: 5px; " >';
    $totales .='<tr>
    <td  valign="top" width="390" > 
       
    <table  style="font-size: 12px; margin: 0px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px; width:100%" >
    <tr>
    <td width="390" height="162" >SON: '.$con_letra.'</td>
    </tr>
    </table>
    </td>

    <td valign="top" width="300" align="left" >';

    $totales .= ' <table style=" font-size: 12px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px;width:100%"   align="left">';

    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18" align="right">Total Op. Gravadas '.$eti_mone.'</td> </b>';
    $totales .= ' <td  width="110" align="right">' . number_format($tot_opgrav, 2) . '</td>';
    $totales .= ' </tr>';

   

    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18" align="right">Total Op. Inafectas '.$eti_mone.'</td> </b>';
    $totales .= ' <td width="110" align="right">' . number_format( $tot_opinafe, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';


    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18"align="right">Total Op. Exoneradas '.$eti_mone.'</td> </b>';
    $totales .= ' <td width="110" align="right">' . number_format($tot_opexo, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18"align="right">Total '.$array_imp ['IVA'].' '. number_format($array_porc ['IVA'],2).'% '.$eti_mone.'</td> </b>';
    $totales .= ' <td width="110" align="right">' . number_format($ncre_iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

  
    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18" align="right">Total Descuentos '.$eti_mone.'</td> </b>';
    $totales .= ' <td width="110" align="right">' . number_format($totalDescuento, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18" align="right">Total ISC '.$eti_mone.'</td> </b>';
    $totales .= ' <td width="110" align="right">0.00</td>';
    $totales .= ' </tr>';


    $totales .= ' <tr>';
    $totales .= ' <b> <td width="185" height="18" align="right">Importe Total '.$eti_mone.'</td> </b>';
    $totales .= ' <td width="110" align="right">' . number_format($total_ncre, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    $totales .= ' </table>';


    $totales.='</td>
    </tr>';
    $totales .='</table>';



    $tableLeyenda ='<table border="0"  style="width: 100%;" cellspacing="0">';
    $tableLeyenda .= '<tr>';

    $tableLeyenda .= '<td valign="top" width="500">';
    $tableLeyenda .= '<table style="  font-size: 13px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:5px; width:100%" cellspacing="0">';
    
    $tableLeyenda .= '<tr>';

    //$hash=hash("md5",$fact_auto_sri);
    $tableLeyenda .= '<td width="500" height="88">Representación impresa de la <b>NOTA DE CREDITO ELECTRÓNICA</b><br>
    Consulta tu comprobante en: <a href ="www.sunat.gob.ec" target="_blank"><b>www.sunat.gob.pe</b></a><br>
    Resumen: '.$ncre_cod_hash.'</td>';
    $tableLeyenda .= '</tr>';
    
    $tableLeyenda.='</table>';
    $tableLeyenda .= '</td>';


    $tableLeyenda .= '<td valign="top" width="220">';
    $tableLeyenda .= '<table style="  font-size: 12px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:5px;" cellspacing="0">';

    //CODIGO QR


    
    $barcode = new \Com\Tecnick\Barcode\Barcode();

    $datosqr=$ruc_empr.'|07|'.substr($ncre_nse_ncre, 3, 6).'|'.$ncre_num_preimp.'|'.number_format($ncre_iva, 2, '.', ',').'|'.number_format($total, 2, '.', ',').'|'.$ncre_fech_fact.'|'.$tipo_docu.'|'.$ncre_ruc_clie;

    $bobj = $barcode->getBarcodeObj(
        'QRCODE,H',                     // Tipo de Barcode o Qr
        $datosqr,          // Datos
        -2.5,                             // Width 
        -2.4,                             // Height
        'black',                        // Color del codigo
        array(-2, -2, -2, -2)           // Padding
        )->setBackgroundColor('white'); // Color de fondo

    $imageData = $bobj->getPngData(); // Obtenemos el resultado en formato PNG
        
    $ruta_dir = DIR_FACTELEC . 'modulos/envio_documentos/qr_nota_credito';
        if (!file_exists($ruta_dir)){
            mkdir($ruta_dir,0777,true);
        }

    file_put_contents(DIR_FACTELEC . 'modulos/envio_documentos/qr_nota_credito/NC_'.$id.'.png', $imageData); // Guardamos el resultado
    
    $ruta=DIR_FACTELEC . 'modulos/envio_documentos/qr_nota_credito/NC_'.$id.'.png';

    $tableLeyenda .= '<tr>';
    $tableLeyenda .= '<td width="204" align="center"><img src="'.$ruta.'"></td>';

    $tableLeyenda .= '</tr>';
    $tableLeyenda.='</table>';
    $tableLeyenda .= '</td>';
    $tableLeyenda .= '</tr>';
    
    $tableLeyenda .= '</table>';

    $legend = '<page_footer>
        <table align="center" style="width: 80%">
            <tr>
                <td style="font-size: 12px; color: #6B6565; background-color: transparent;" align="center">Este comprobante electronico ha sido generado a traves de Sisconti S.A. - Facturacion Electronica</td>
            </tr>
			<tr>
                <td style="font-size: 12px; color: #6B6565; background-color: transparent;" align="center">www.sisconti.com</td>
            </tr>
        </table>
    </page_footer>';

    $documento .= '<page backimgw="100%" backtop="5mm" backbottom="5mm" backleft="5mm" backright="5mm">';
    $documento .= $logo . $deta . $totales . $tableLeyenda;
    $documento .= $legend;
    $documento .= '</page>';


    //file_put_contents("C:/prueba/documento.html", $documento);

    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
    $html2pdf->WriteHTML($documento);
    $ruta_dir= DIR_FACTELEC . 'modulos/envio_documentos/upload';
	if (!file_exists($ruta_dir)){
	    mkdir($ruta_dir,0777,true);
	}
    $ruta = DIR_FACTELEC . 'modulos/envio_documentos/upload/pdf/cred_' . $nombre_archivo . '.pdf';
    $html2pdf->Output($ruta, 'F');
    $rutaPdf = $ruta;

    return $documento;
}

?>