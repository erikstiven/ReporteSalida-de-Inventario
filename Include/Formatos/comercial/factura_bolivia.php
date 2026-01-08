


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


    $sql = "select empr_iva_empr, empr_cod_pais, empr_cod_ciud  from saeempr where empr_cod_empr = $idEmpresa ";
    $empr_cod_pais = round(consulta_string($sql, 'empr_cod_pais', $oIfx, 0));
    $empr_cod_ciud = consulta_string($sql, 'empr_cod_ciud', $oIfx, 0);

    //DATOS PAIS - CIUDAD
    $sql = "select pais_des_pais from saepais where pais_cod_pais=$empr_cod_pais";
    $pais= consulta_string($sql,'pais_des_pais', $oCon,'');

    $sql="select ciud_nom_ciud from saeciud where ciud_cod_ciud=$empr_cod_ciud";
    $ciudad= consulta_string($sql,'ciud_nom_ciud', $oCon,'NA');


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


    //ARRAY UNIDADES - 
    $sql = "select u.unid_cod_unid, f.unid_cod_clas, f.unid_des_unid from saeunid u
    inner join comercial.catalogo_unidades f on
    u.unid_cod_alias = f.id";
    unset($array_unidad);
    unset($array_unidad_desc);
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                if (!empty($oIfx->f('unid_cod_clas')) || $oIfx->f('unid_cod_clas') != '') {
                    $array_unidad[$oIfx->f('unid_cod_unid')] = $oIfx->f('unid_cod_clas');
                    $array_unidad_desc[$oIfx->f('unid_cod_unid')] = $oIfx->f('unid_des_unid');
                }
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    //GENERACION DE LEYENDA ALEATORIA

    $sqley = "select * from comercial.catalogo_leyendas_factura";
    $array_leyenda = array();
    if ($oIfx->Query($sqley)) {
        if ($oIfx->NumFilas() > 0) {
            do {

                $id_leyenda = $oIfx->f('id');

                array_push($array_leyenda, $id_leyenda);
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $clave_aleatoria = array_rand($array_leyenda, 1);
    if (empty($clave_aleatoria)) {
        $clave_aleatoria = 0;
    }
    $leyenda_factura = '';
    $sqley = "select ley_des_ley from comercial.catalogo_leyendas_factura where id=$clave_aleatoria";
    if ($oIfx->Query($sqley)) {
        if ($oIfx->NumFilas() > 0) {
            $leyenda_factura = $oIfx->f('ley_des_ley');
        }
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
        $logo_empresa='<img width="200px;"  src="' . $path_logo_img . '">';
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
                    //$titulo='FACTURA ELECTRÓNICA';
                    $titulo='FACTURA';
                    $idcli='RUC:';  
                }
                elseif($tipo_pdf=='B'){
                    //$titulo='BOLETA DE VENTA <br> ELECTRÓNICA';
                    $titulo='BOLETA DE VENTA';
                    $idcli='DNI:';
                }
                $titulo='FACTURA';
                $fact_num_preimp = intval($oIfx->f('fact_num_preimp'));
                $fact_auto_sri = $oIfx->f('fact_auto_sri');
                if(!empty($fact_auto_sri)){
                    $text_1=substr($fact_auto_sri,0,20);
                    $text_2=substr($fact_auto_sri,20,20);
                    $text_3=substr($fact_auto_sri,40,20);
                    $fact_auto_sri=$text_1.'<br>'.$text_2.'<br>'.$text_3;
                }
                $fact_fech_sri = $oIfx->f('fact_fech_sri');
                $fact_fech_fact = fecha_mysql_func($oIfx->f('fact_fech_fact'));
                $fact_fech_fact =date('d/m/Y',strtotime($fact_fech_fact));

                if(!empty($fact_fech_sri)){
                    $fact_fech_sri = date('d/m/Y',strtotime(substr($fact_fech_sri,0,10))).' '.substr($fact_fech_sri,11,8);
                }
                else{
                    $fact_fech_sri = $fact_fech_fact;
                }
                $fact_leye_fact = $oIfx->f('fact_leye_fact');
                if(empty($fact_leye_fact)){
                    $fact_leye_fact=$leyenda_factura;
                }
                $fact_nom_cliente = $oIfx->f('fact_nom_cliente');
               
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

                if($fact_cod_mone==$pcon_seg_mone){
                $eti_mone=  substr($sigmone,0,2).$smbmone;
                }
                else{
                    $eti_mone= $smbmone;
                }

                //ETIQUETA LOCAL
                $sql = "select mone_des_mone, mone_sgl_mone, mone_smb_mene from saemone where mone_cod_mone =  $pcon_mon_base;";
                $sigmoneprin= consulta_string($sql,'mone_smb_mene', $oCon,'');

               
            

                $fact_sin_miva = $oIfx->f('fact_sin_miva');
                $fact_tot_fact = $oIfx->f('fact_tot_fact');
                $fact_iva = $oIfx->f('fact_iva');
               
                $fact_dsg_valo  = $oIfx->f('fact_dsg_valo');
                $fact_cm1_fact  = strtoupper(trim($oIfx->f("fact_cm1_fact")));
                $fact_cm2_fact  = $oIfx->f("fact_cm2_fact");
                $fact_cm4_fact  = $oIfx->f("fact_cm4_fact");
                $orden_compra   = $oIfx->f("fact_opc_fact");
                $fact_cod_clpv  = $oIfx->f("fact_cod_clpv");
                $fact_cod_detra  = $oIfx->f("fact_cod_detra");
                $cod_contrato =  $oIfx->f('fact_cod_contr');
                if($cod_contrato==0||empty($cod_contrato)){
                    $cod_contrato= trim($oIfx->f('fact_cm7_fac'));
                }

                
                if(empty($cod_contrato)){
                    $cod_contrato='NULL';
                }

                

                //CODIGO CID CLIENTES

                $sql_cid="select codigo_cid from  isp.int_contrato_caja_pack where id_clpv=$fact_cod_clpv and id_contrato = $cod_contrato
                and estado in ('A','C','P')";
                if ($oIfxA->Query($sql_cid)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $codigo_cid= $oIfxA->f('codigo_cid').'<br>';
                        }while ($oIfxA->SiguienteRegistro());
                    }

                }
                $oIfxA->Free();



                             

                $tipo_doc = $arrayTipDoc[$fact_cod_clpv];

                 //NIT
                 if (intval($tipo_doc) == 1) {
                    $tipo_docu = '5';
                    // $tipo_envio = '01';
                }
                //CEDULA 
                else if (intval($tipo_doc) == 2) {
                    $tipo_docu = '1';
                    //$tipo_envio = '03';
                }
                //PASAPORTE 
                else if (intval($tipo_doc) == 3) {
                    $tipo_docu = '3';
                    //$tipo_envio = '03';
                }
                //EXTRANJERIA
                else if (intval($tipo_doc) == 4) {
                    $tipo_docu = '2';
                    //$tipo_envio = '03';
                } else {
                    $tipo_docu = '4';
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


               

            }while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();


    //CODIGO DE SUCURSAL
    $codigoSucursal = 0;
    $sqlsuc = "select sucu_alias_sucu from saesucu where sucu_cod_empr= $idempresa and sucu_cod_sucu=$idSucursal";

    $codigoSucursal= consulta_string($sql,'sucu_alias_sucu', $oCon,'');


     //CABECERA FACTURA 
                
     $logo ='<table border="0"  style="font-size:12px; width: 100%;"  cellspacing="1">';
     $logo .= '<tr>';
     $logo .= '<td  width="100%" colspan="3" align="left">'.$logo_empresa.'</td>';
     $logo .= '</tr>';
     
     $logo .= '<tr>';
     $logo .= '<td style="width:60%;" align="center"><b>' .$razonSocial . '</b></td>';
     $logo .= '<td style="width:23%;"><b>NIT</b></td>';
     $logo .= '<td style="width:17%;">' . $ruc_empr . '</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td style="width:60%;" align="center"><b>CASA MATRIZ</b></td>';
     $logo .= '<td style="width:23%;"><b>FACTURA N°</b></td>';
     $logo .= '<td style="width:17%;">' . $fact_num_preimp . '</td>';
     $logo .= '</tr>';
  

     $logo .= '<tr>';
     $logo .= '<td style="width:60%;" align="center"><b>No. Punto de Venta ' .$codigoSucursal . '</b><br>'.$dirMatriz.'<br>Teléfono: '.$fact_tlf_cliente.'<br>'.$ciudad.', '.$pais.'</td>';
     $logo .= '<td style="width:23%;" valign="top"><b>COD. AUTORIZACIÓN</b></td>';
     $logo .= '<td style="width:17%;" valign="top">' . $fact_auto_sri . '</td>';
     $logo .= '</tr>';

     $logo.='</table>';

     $logo.='<div style="margin-top:20px;text-align:center;font-size:14px;width="100px;">';
     $logo.='<b>FACTURA<br></b><font size="10">(Con Derecho a Crédito Fiscal)</font>';
     $logo.='</div>';


     //DATOS DEL CLIENTE
     $logo .='<table border="0"  style="width: 100%; margin-top:20px;"  cellspacing="1">';

     $logo .= '<tr>';
     $logo .= '<td style="width:25%;"><b>Fecha:</b></td>';
     $logo .= '<td style="width:40%;">'.$fact_fech_sri.'</td>';
     $logo .= '<td style="width:15%;" align="right"><b>NIT/CI/CEX:</b></td>';
     $logo .= '<td style="width:20%;">'.$fact_ruc_clie.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td style="width:25%;"><b>Nombre/Razón Social:</b></td>';
     $logo .= '<td style="width:40%;">'.$fact_nom_cliente.'</td>';
     $logo .= '<td style="width:15%;" align="right"><b>Cód. Cliente:</b></td>';
     $logo .= '<td style="width:20%;">'.$fact_cod_clpv.'</td>';
     $logo .= '</tr>';

     $logo.='</table>';




    $titulo=str_replace('<br>',' ',$titulo);
    //DETALLE DE LA FACTURA
    
    $sqlDeta = "select * from saedfac where dfac_cod_fact = $id and 
                dfac_cod_sucu = $idSucursal and 
                dfac_cod_empr = $idEmpresa  ";

    $deta .= ' <table  style="border: #a9a9a9 1px solid; width: 100%;  font-size: 13px;   margin-top:5px;" cellpadding="1" cellspacing="0">';
    $deta .= ' <tr >';
    $deta .= ' <b> <td style="background: #e6e6e6; border-right: #a9a9a9 1px solid; width: 12%;" align="center" height="30">CÓDIGO PRODUCTO/ SERVICIO</td> </b>';
    $deta .= ' <b> <td style="background: #e6e6e6; border-right: #a9a9a9 1px solid; width: 12%;" align="center" height="30">CANTIDAD</td> </b>';
    $deta .= ' <b> <td style="background: #e6e6e6; border-right: #a9a9a9 1px solid; width: 12%;" align="center" height="30">UNIDAD MEDIDA</td> </b>';
    $deta .= ' <b> <td style="background: #e6e6e6; border-right: #a9a9a9 1px solid; width: 28%;" align="center" height="30">DESCRIPCIÓN</td> </b>';
    $deta .= ' <b> <td style="background: #e6e6e6; border-right: #a9a9a9 1px solid; width: 12%;" align="center" height="30">PRECIO<br>UNITARIO</td> </b>';
    $deta .= ' <b> <td style="background: #e6e6e6; border-right: #a9a9a9 1px solid; width: 12%;" align="center" height="30">DESCUENTO</td> </b>';
    $deta .= ' <b> <td style="background: #e6e6e6; width: 12%;" align="center" height="30">SUBTOTAL</td> </b>';
    $deta .= ' </tr>';

    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            $ctrl=0;
            
            do {
                $dfac_cod_prod = $oIfx->f('dfac_cod_prod');
                $dfac_nom_prod = $oIfx->f('dfac_nom_prod');
                $dfac_cod_lote = $oIfx->f('dfac_cod_lote');

                $dfac_cant_dfac = $oIfx->f('dfac_cant_dfac');
                $dfac_precio_dfac = $oIfx->f('dfac_precio_dfac');
                $dfac_des1_dfac = $oIfx->f('dfac_des1_dfac');
                $dfac_des2_dfac = $oIfx->f('dfac_des2_dfac');
                $dfac_por_dsg = $oIfx->f('dfac_por_dsg');
                $dfac_mont_total = $oIfx->f('dfac_mont_total');
                $dfac_por_iva = $oIfx->f('dfac_por_iva');
                $dfac_det_dfac = $oIfx->f('dfac_det_dfac');

                $dfac_cod_unid = $oIfx->f('dfac_cod_unid');
                if(!empty($dfac_cod_unid)){
                $unidad= $array_unidad_desc[$dfac_cod_unid];
                }
                else{
                    $unidad='';
                }

                $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                if ($descuento > 0){
                    $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                    $descuento =round($descuento, 2, PHP_ROUND_HALF_UP);
                }
                else{
                    $descuento = '0';
                }
               

                if (round($dfac_por_iva, 2) > 0) {
                    $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                    $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;
                    $dfac_mont_total = $dfac_mont_total + $valor_iva;
                    $dfac_precio_dfac = $dfac_precio_dfac + ($valor_iva/$dfac_cant_dfac);
                }

                if (empty($dfac_det_dfac)) {
                    $dfac_det_dfac = 'Sin detalle';
                }

              

                /*if($fact_cod_mone==$pcon_seg_mone){
                    $dfac_precio_dfac=$dfac_precio_dfac/$fact_val_tcam;
                    $dfac_mont_total=$dfac_mont_total/$fact_val_tcam;
                    $descuento=$descuento/$fact_val_tcam;
                    $totalDescuento=$totalDescuento/$fact_val_tcam;
                }*/

                
                $deta .= ' <tr>';
                $deta .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;">'.$dfac_cod_prod.'</td>';
                $deta .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($dfac_cant_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;">' . $unidad . '</td>';
                $deta .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 28%;">'.$dfac_det_dfac.'</td>';
                $deta .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($dfac_precio_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($descuento, 2, '.', ',') . '</td>';
                $deta .= ' <td style="border-top: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($dfac_mont_total, 2, '.', ',') . '</td>';
                $deta .= ' </tr>';
                $ctrl++;
            }while ($oIfx->SiguienteRegistro());
               
        }
    }

    $deta .= ' </table>';


    $total = number_format($fact_con_miva + $fact_sin_miva + $fact_iva + $fact_fle_fact + $fact_otr_fact + $fact_fin_fact - $fact_dsg_valo, 2, '.', '');
    $V = new EnLetras();
    if($fact_cod_mone==$pcon_seg_mone)
    {
        $total = $total/$fact_val_tcam;
        $con_letra = strtoupper($V->ValorEnLetrasMonePeru($total, $moneda));
    }
    else{
        $con_letra = strtoupper($V->ValorEnLetrasMonePeru($total, $moneda));
    }
    
   
    //DATOS DE LA FORMA DE PAGO
    $tablepago='';

    $tablepago.='<table  style="width:100%; margin-top:10px" border="0" cellspacing="5" >';
    $tablepago .= '<tr>';

    $tablepago .= '<td style="width:64%;">';

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
                fp.fpag_cod_empr=$idEmpresa and 
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
                if($fact_cod_mone==$pcon_seg_mone)
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




    $fact_tot_fact = $fact_con_miva + $fact_iva+$fact_sin_miva + $fact_val_irbp -$fact_dsg_valo;

    if($fact_cod_mone==$pcon_seg_mone)
    {
        $fact_iva = $fact_iva/$fact_val_tcam;
        $fact_tot_fact = $fact_tot_fact/$fact_val_tcam;

    }


    
    $totales ='<table style="width: 100%;  font-size: 13px; margin-top: 0px;" cellspacing="0">';
    $totales .='<tr>
    <td style="width:63.7%;">&nbsp;</td>';

    $totales.='<td style="width:36.3%;">';

    $totales .= ' <table style="width: 100%; font-size: 13px;"   cellspacing="0" >';
    $totales .= ' <tr>';
    $totales .= ' <td style="border-left: #a9a9a9 1px solid; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 66.66%;" align="right" height="18" >SUBTOTAL '.$eti_mone.'</td>';
    $totales .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 33.34%;" align="right">' . number_format($fact_tot_fact, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <td style="border-left: #a9a9a9 1px solid; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 66.66%;" align="right" height="18" >DESCUENTO '.$eti_mone.'</td>';
    $totales .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 33.34%;" align="right">' . number_format($fact_dsg_valo, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <td style="border-left: #a9a9a9 1px solid; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 66.66%;" align="right" height="18" >TOTAL '.$eti_mone.'</td>';
    $totales .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 33.34%;" align="right">' . number_format($fact_tot_fact, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <td style="border-left: #a9a9a9 1px solid; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 66.66%;" align="right" height="18" >MONTO GIFT CARD '.$eti_mone.'</td>';
    $totales .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 33.34%;" align="right">0.00</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr >';
    $totales .= ' <td style="background: #e6e6e6; border-left: #a9a9a9 1px solid; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 66.66%;" align="right" ><b>MONTO A PAGAR '.$eti_mone.'</b></td>';
    $totales .= ' <td style="background: #e6e6e6;border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 33.34%;" align="right"><b>' . number_format($fact_tot_fact, 2, '.', ',') . '</b></td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <td style="background: #e6e6e6; border-left: #a9a9a9 1px solid; border-bottom: #a9a9a9 1px solid; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 66.66%;" align="right" height="18"><b>IMPORTE BASE CRÉDITO FISICAL:</b></td>';
    $totales .= ' <td style="background: #e6e6e6; border-bottom: #a9a9a9 1px solid; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 33.34%;" align="right"><b>' . number_format($fact_tot_fact, 2, '.', ',') . '</b></td>';
    $totales .= ' </tr>';

    $totales .= ' </table>';

    $totales.='</td></tr>';
    $totales .='</table>';



    $tableLeyenda ='<table border="0"  style="width: 100%;font-size: 13px;margin-top:25px;" cellspacing="0">';
    $tableLeyenda .= '<tr>';

    $tableLeyenda .= '<td valign="top" style="width:80%;">';

    $tableLeyenda .= '<table  style="width:100%;" cellspacing="0" >';
    $tableLeyenda .= '<tr>';
    $tableLeyenda .= '<td style="width:100%;"><b>Son: '.$con_letra.'</b></td>';
    $tableLeyenda .= '</tr>';
    $tableLeyenda .= '<tr>';
    $tableLeyenda .= '<td style="width:100%;" align="center"><br>ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS. EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE
    ACUERDO A LEY</td>';
    $tableLeyenda .= '</tr>';
    $tableLeyenda .= '<tr>';
    $tableLeyenda .= '<td style="font-size: 10px; width:100%;" align="center"><br>'.$fact_leye_fact.'</td>';
    $tableLeyenda .= '</tr>';
    $tableLeyenda .= '<tr>';
    $tableLeyenda .= '<td style="font-size: 10px; width:100%;" align="center"><br>"Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido en una modalidad de facturación en línea”</td>';
    $tableLeyenda .= '</tr>';
    $tableLeyenda.='</table>';
    
    $tableLeyenda .= '</td>';

    $tableLeyenda .= '<td valign="top" style="width:20%;" align="right">';
    
    $tableLeyenda .= '<table  style="width:100%;" cellspacing="0" align="right">';

    //CODIGO QR

    $barcode = new \Com\Tecnick\Barcode\Barcode();

    $datosqr=$ruc_empr.'|'.$nse_fact.'|'.$fact_num_preimp.'|'.number_format($fact_tot_fact, 2, '.', ',').'|'.$fact_fech_fact.'|'.$tipo_docu.'|'.$fact_ruc_clie;

    $bobj = $barcode->getBarcodeObj(
        'QRCODE,H',                     // Tipo de Barcode o Qr
        $datosqr,          // Datos
        -3,                             // Width 
        -3,                             // Height
        'black',                        // Color del codigo
        array(-2, -2, -2, -2)           // Padding
        )->setBackgroundColor('white'); // Color de fondo

    $imageData = $bobj->getPngData(); // Obtenemos el resultado en formato PNG
        

    $ruta_dir = DIR_FACTELEC . 'modulos/envio_documentos_bolivia/qr_facturas';
        if (!file_exists($ruta_dir)){
            mkdir($ruta_dir,0777,true);
        }
        
    file_put_contents(DIR_FACTELEC . 'modulos/envio_documentos_bolivia/qr_facturas/FAC_'.$id.'.png', $imageData); // Guardamos el resultado
    
    $ruta=DIR_FACTELEC . 'modulos/envio_documentos_bolivia/qr_facturas/FAC_'.$id.'.png';


    
    $tableLeyenda .= '<tr>';
    $tableLeyenda .= '<td align="right"><img src="'.$ruta.'"></td>';

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

    $documento .= '<page backimgw="100%" backtop="5mm" backbottom="5mm" backleft="3mm" backright="5mm">';
    $documento .= $logo . $cliente . $deta . $totales . $tablePago. $tableLeyenda;
    $documento .= $legend;
    $documento .= '</page>';


    
    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
    $html2pdf->WriteHTML($documento);

    $ruta_dir= DIR_FACTELEC . 'modulos/envio_documentos_bolivia/upload';
	if (!file_exists($ruta_dir)){
					mkdir($ruta_dir,0777,true);
	}
    $ruta = DIR_FACTELEC . 'modulos/envio_documentos_bolivia/upload/pdf/fac_' . $nombre_archivo . '.pdf';
    $html2pdf->Output($ruta,'F');
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


    $sql = "select empr_iva_empr, empr_cod_pais, empr_cod_ciud from saeempr where empr_cod_empr = $idEmpresa ";
    $empr_cod_pais = round(consulta_string($sql, 'empr_cod_pais', $oIfx, 0));
    $empr_cod_ciud = consulta_string($sql, 'empr_cod_ciud', $oIfx, 0);

    //DATOS PAIS - CIUDAD
    $sql = "select pais_des_pais from saepais where pais_cod_pais=$empr_cod_pais";
    $pais= consulta_string($sql,'pais_des_pais', $oCon,'');

    $sql="select ciud_nom_ciud from saeciud where ciud_cod_ciud=$empr_cod_ciud";
    $ciudad= consulta_string($sql,'ciud_nom_ciud', $oCon,'NA');

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

    //ARRAY UNIDADES - 
    $sql = "select u.unid_cod_unid, f.unid_cod_clas, f.unid_des_unid from saeunid u
    inner join comercial.catalogo_unidades f on
    u.unid_cod_alias = f.id";
    unset($array_unidad);
    unset($array_unidad_desc);
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                if (!empty($oIfx->f('unid_cod_clas')) || $oIfx->f('unid_cod_clas') != '') {
                    $array_unidad[$oIfx->f('unid_cod_unid')] = $oIfx->f('unid_cod_clas');
                    $array_unidad_desc[$oIfx->f('unid_cod_unid')] = $oIfx->f('unid_des_unid');
                }
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    //GENERACION DE LEYENDA ALEATORIA

    $sqley = "select * from comercial.catalogo_leyendas_factura";
    $array_leyenda = array();
    if ($oIfx->Query($sqley)) {
        if ($oIfx->NumFilas() > 0) {
            do {

                $id_leyenda = $oIfx->f('id');

                array_push($array_leyenda, $id_leyenda);
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $clave_aleatoria = array_rand($array_leyenda, 1);
    if (empty($clave_aleatoria)) {
        $clave_aleatoria = 0;
    }
    $leyenda_factura = '';
    $sqley = "select ley_des_ley from comercial.catalogo_leyendas_factura where id=$clave_aleatoria";
    if ($oIfx->Query($sqley)) {
        if ($oIfx->NumFilas() > 0) {
            $leyenda_factura = $oIfx->f('ley_des_ley');
        }
    }
    
    
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
        $logo_empresa='<img width="200px;"  src="' . $path_logo_img . '">';
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
                $ncre_num_preimp = intval($oIfx->f('ncre_num_preimp'));

                $ncre_auto_sri = $oIfx->f('ncre_auto_sri');
                if(!empty($ncre_auto_sri)){
                    $text_1=substr($ncre_auto_sri,0,20);
                    $text_2=substr($ncre_auto_sri,20,20);
                    $text_3=substr($ncre_auto_sri,40,20);
                    $ncre_auto_sri=$text_1.'<br>'.$text_2.'<br>'.$text_3;
                }

                
                $ncre_fech_sri = $oIfx->f('ncre_fech_sri');
                $ncre_nom_cliente = $oIfx->f('ncre_nom_cliente');
                //$ncre_fech_fact = fecha_mysql_func($oIfx->f('ncre_fech_fact'));
                $ncre_fech_fact = $oIfx->f('ncre_fech_fact');
                $ncre_fech_fact =date('d/m/Y',strtotime($ncre_fech_fact));

                if(!empty($ncre_fech_sri)){
                    $ncre_fech_sri = date('d/m/Y',strtotime(substr($ncre_fech_sri,0,10))).' '.substr($ncre_fech_sri,11,8);
                }
                else{
                    $ncre_fech_sri = $ncre_fech_fact;
                }

                $ncre_leye_fact = $oIfx->f('ncre_leye_fact');
                if(empty($ncre_leye_fact)){
                    $ncre_leye_fact=$leyenda_factura;
                }


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

                if($ncre_cod_mone==$pcon_seg_mone){
                $eti_mone=  substr($sigmone,0,2).$smbmone;
                }
                else{
                    $eti_mone= $smbmone;
                }


                $tipo_doc = $arrayTipDoc[$ncre_cod_clpv];
                 //NIT
                 if (intval($tipo_doc) == 1) {
                    $tipo_docu = '5';
                    // $tipo_envio = '01';
                }
                //CEDULA 
                else if (intval($tipo_doc) == 2) {
                    $tipo_docu = '1';
                    //$tipo_envio = '03';
                }
                //PASAPORTE 
                else if (intval($tipo_doc) == 3) {
                    $tipo_docu = '3';
                    //$tipo_envio = '03';
                }
                //EXTRANJERIA
                else if (intval($tipo_doc) == 4) {
                    $tipo_docu = '2';
                    //$tipo_envio = '03';
                } else {
                    $tipo_docu = '4';
                }



                $date = date_create($ncre_fech_venc);
                $ncre_fech_venc = date_format($date,'d/m/Y');

                $date = date_create($ncre_fech_fact);
                $ncre_fech_fact = date_format($date,'d/m/Y');


          

                

                
                //DATOS DE LA EMPRESA

                if ($ncre_cod_fact == '') {
                    $ncre_cod_fact = 0;
                }

                $sql = "select fact_nse_fact, fact_num_preimp, fact_fech_fact,
						fact_cm2_fact, fact_fech_sri,fact_auto_sri
						from saefact 
						where fact_cod_empr = $idEmpresa and 
						fact_cod_fact = $ncre_cod_fact";
                // var_dump($sql);exit;


                $numero_fac = "";
                if ($oIfx2->Query($sql)) {
                    if ($oIfx2->NumFilas() > 0) {
                        $fact_nse_fact = $oIfx2->f('fact_nse_fact');
                        $fact_num_preimp = intval($oIfx2->f('fact_num_preimp'));
                        $fact_fech_fact = $oIfx2->f('fact_fech_fact');
                        $fact_cm2_fact = $oIfx2->f('fact_cm2_fact');

                        $nse = substr($fact_nse_fact, 0, 3);
                        $pto = substr($fact_nse_fact, 3, 6);
                        $numero_fac =  $pto . '-' . $fact_num_preimp;

                        $fact_fech_sri = $oIfx2->f('fact_fech_sri');
                        if(!empty($fact_fech_sri)){
                            $fact_fech_sri = date('d/m/Y',strtotime(substr($fact_fech_sri,0,10))).' '.substr($fact_fech_sri,11,8);
                        }

                        $fact_auto_sri = $oIfx2->f('fact_auto_sri');
                        if(!empty($fact_auto_sri)){
                            $text_1=substr($fact_auto_sri,0,20);
                            $text_2=substr($fact_auto_sri,20,20);
                            $text_3=substr($fact_auto_sri,40,20);
                            $fact_auto_sri=$text_1.'<br>'.$text_2.'<br>'.$text_3;
                        }
                        
                    } /*else {
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
                    }*/
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
                

               
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    //CABECERA DE LA NOTA DE CREDITO
    $logo ='<table border="0"  style="font-size:12px; width: 100%;"  cellspacing="1">';
    $logo .= '<tr>';
    $logo .= '<td  width="100%" colspan="3" align="left">'.$logo_empresa.'</td>';
    $logo .= '</tr>';
    
    $logo .= '<tr>';
    $logo .= '<td style="width:60%;" align="center"><b>' .$razonSocial . '</b></td>';
    $logo .= '<td style="width:23%;"><b>NIT</b></td>';
    $logo .= '<td style="width:17%;">' . $ruc_empr . '</td>';
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td style="width:60%;" align="center"><b>CASA MATRIZ</b></td>';
    $logo .= '<td style="width:23%;"><b>NOTA N°</b></td>';
    $logo .= '<td style="width:17%;">' . $ncre_num_preimp . '</td>';
    $logo .= '</tr>';
 

    $logo .= '<tr>';
    $logo .= '<td style="width:60%;" align="center"><b>No. Punto de Venta ' .$codigoSucursal . '</b><br>'.$dirMatriz.'<br>Teléfono: '.$fact_tlf_cliente.'<br>'.$ciudad.', '.$pais.'</td>';
    $logo .= '<td style="width:23%;" valign="top"><b>COD. AUTORIZACIÓN</b></td>';
    $logo .= '<td style="width:17%;" valign="top">' . $ncre_auto_sri . '</td>';
    $logo .= '</tr>';

    $logo.='</table>';

    $logo.='<div style="margin-top:20px;text-align:center;font-size:14px;width="100px;">';
    $logo.='<b>NOTA CRÉDITO - DEBITO</b>';
    $logo.='</div>';


    //DATOS DEL CLIENTE
    $logo .='<table border="0"  style="width: 100%; margin-top:20px;"  cellspacing="1">';

    $logo .= '<tr>';
    $logo .= '<td style="width:25%;"><b>Fecha:</b></td>';
    $logo .= '<td style="width:35%;">'.$ncre_fech_sri.'</td>';
    $logo .= '<td style="width:20%;" align="right"><b>NIT/CI/CEX:</b></td>';
    $logo .= '<td style="width:20%;">'.$ncre_ruc_clie.'</td>';
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td style="width:25%;"><b>Nombre/Razón Social:</b></td>';
    $logo .= '<td style="width:35%;">'.$ncre_nom_cliente.'</td>';
    $logo .= '<td style="width:20%;" align="right"><b>Cód. Cliente:</b></td>';
    $logo .= '<td style="width:20%;">'.$ncre_cod_clpv.'</td>';
    $logo .= '</tr>';


    $logo .= '<tr>';
    $logo .= '<td style="width:25%;"><b>N° Factura:</b></td>';
    $logo .= '<td style="width:35%;">'.$fact_num_preimp.'</td>';
    $logo .= '<td style="width:20%;" align="right"><b>Fecha Factura:</b></td>';
    $logo .= '<td style="width:20%;">'.$fact_fech_sri.'</td>';
    $logo .= '</tr>';


    $logo .= '<tr>';
    $logo .= '<td style="width:25%;"></td>';
    $logo .= '<td style="width:35%;"></td>';
    $logo .= '<td style="width:20%;" align="right"><b>N° Autorización/CUF:</b></td>';
    $logo .= '<td style="width:20%;">'.$fact_auto_sri.'</td>';
    $logo .= '</tr>';

    $logo.='</table>'; 
    
    

    //DETALLE DE LA NOTA DE CREDITO

    $sqlDeta = "select 
    dncr_cant_dfac as dfac_cant_dfac,
    dncr_precio_dfac as dfac_precio_dfac,
    dncr_cod_prod as dfac_cod_prod,
    dncr_det_dncr as dfac_det_dfac,
    dncr_mont_total as dfac_mont_total,
    dncr_por_iva as dfac_por_iva,
    dncr_des1_dfac as dfac_des1_dfac,
    dncr_des2_dfac as dfac_des2_dfac,
    dncr_por_dsg as dfac_por_dsg,
    dncr_cod_dfac,
    dncr_cod_unid
    from saedncr where dncr_cod_ncre = $id and dncr_cod_sucu = $idSucursal and dncr_cod_empr = $idEmpresa ";

    $cab_deta = ' <table  style=" width: 100%;  font-size: 13px;   margin-top:5px;" border="0" cellpadding="1" cellspacing="0">';
    $cab_deta .= ' <tr >';
    $cab_deta .= ' <b> <td style="background: #e6e6e6; border-top: #a9a9a9 1px solid; border-left: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="center" height="30">CÓDIGO PRODUCTO/ SERVICIO</td> </b>';
    $cab_deta .= ' <b> <td style="background: #e6e6e6; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="center" height="30">CANTIDAD</td> </b>';
    $cab_deta .= ' <b> <td style="background: #e6e6e6; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="center" height="30">UNIDAD MEDIDA</td> </b>';
    $cab_deta .= ' <b> <td style="background: #e6e6e6; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 28%;" align="center" height="30">DESCRIPCIÓN</td> </b>';
    $cab_deta .= ' <b> <td style="background: #e6e6e6; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="center" height="30">PRECIO<br>UNITARIO</td> </b>';
    $cab_deta .= ' <b> <td style="background: #e6e6e6; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="center" height="30">DESCUENTO</td> </b>';
    $cab_deta .= ' <b> <td style="background: #e6e6e6; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="center" height="30">SUBTOTAL</td> </b>';
    $cab_deta .= ' </tr>';

    $deta_nc='';
    $deta_ori='';

    $total_ori='';
    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            do{
                $dfac_cant_dfac = $oIfx->f('dfac_cant_dfac');
                $dfac_cod_prod = $oIfx->f('dfac_cod_prod');
                $dfac_det_dfac = $oIfx->f('dfac_det_dfac');
                $dfac_mont_total = $oIfx->f('dfac_mont_total');
                $dfac_por_iva = $oIfx->f('dfac_por_iva');
                $dncr_cod_dfac = $oIfx->f('dncr_cod_dfac');
                if(empty($dncr_cod_dfac)){
                    $dncr_cod_dfac='NULL';
                }
                $dfac_cod_unid = $oIfx->f('dncr_cod_unid');

                $dfac_precio_dfac = $oIfx->f('dfac_precio_dfac');
                $dfac_des1_dfac = $oIfx->f('dfac_des1_dfac');
                $dfac_des2_dfac = $oIfx->f('dfac_des2_dfac');
                $dfac_por_dsg = $oIfx->f('dfac_por_dsg');
                
                $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                if ($descuento > 0){
                    $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                    $descuento =round($descuento, 2, PHP_ROUND_HALF_UP);
                }
                else{
                    $descuento = '0';
                }

                if (round($dfac_por_iva, 2) > 0) {
                    $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                    $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;
                    $dfac_mont_total = $dfac_mont_total + $valor_iva;
                    $dfac_precio_dfac = $dfac_precio_dfac + ($valor_iva/$dfac_cant_dfac);
                }
                

                /*if ($pcon_seg_mone == $fact_cod_mone) {
                    $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                }*/                                       

                if (empty($dfac_det_dfac)) {
                    $dfac_det_dfac = 'Sin detalle';
                }

                //CONSULTAMOS EL DETALLE DE ITEM DE LA FACTURA ORIGINAL

                $sql = "SELECT dfac_cant_dfac, dfac_cod_prod, dfac_det_dfac, dfac_mont_total, dfac_por_iva, 
                 dfac_cod_lote, dfac_cod_unid, dfac_precio_dfac, dfac_des1_dfac, dfac_des2_dfac, dfac_por_dsg FROM saedfac WHERE dfac_cod_fact = $ncre_cod_fact and dfac_cod_dfact=$dncr_cod_dfac";
                
                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        $j = 0;
                        do {
                            $dfac_cant_dfac_ori = $oCon->f('dfac_cant_dfac');
                            $dfac_cod_prod_ori = $oCon->f('dfac_cod_prod');
                            $dfac_det_dfac_ori = $oCon->f('dfac_det_dfac');
                            $dfac_mont_total_ori = $oCon->f('dfac_mont_total');
                            $dfac_por_iva_ori = $oCon->f('dfac_por_iva');
                            $dfac_cod_lote_ori = $oCon->f('dfac_cod_lote');
                            $dfac_cod_unid_ori = $oCon->f('dfac_cod_unid');

                            $dfac_precio_dfac_ori = $oCon->f('dfac_precio_dfac');
                            $dfac_des1_dfac_ori = $oCon->f('dfac_des1_dfac');
                            $dfac_des2_dfac_ori = $oCon->f('dfac_des2_dfac');
                            $dfac_por_dsg_ori = $oCon->f('dfac_por_dsg');
                            
                            $descuento_ori = $dfac_des1_dfac_ori + $dfac_des2_dfac_ori + $dfac_por_dsg_ori;

                            if ($descuento_ori > 0){
                                $descuento_ori = ($dfac_precio_dfac_ori * $dfac_cant_dfac_ori) - ($dfac_mont_total_ori);
                                $descuento_ori =round($descuento_ori, 2, PHP_ROUND_HALF_UP);
                            }
                            else{
                                $descuento_ori = '0';
                            }

                            if (round($dfac_por_iva_ori, 2) > 0) {
                                $porcentaje_iva = ($dfac_por_iva_ori / 100) + 1;
                                $valor_iva = ($dfac_mont_total_ori * $porcentaje_iva) - $dfac_mont_total_ori;
                                $dfac_mont_total_ori = $dfac_mont_total_ori + $valor_iva;
                                $dfac_precio_dfac_ori = $dfac_precio_dfac_ori + ($valor_iva/$dfac_cant_dfac_ori);
                            }
                            

                            $total_ori+=$dfac_mont_total_ori;

                            /*if ($pcon_seg_mone == $fact_cod_mone) {
                                $dfac_mont_total_ori  = $dfac_mont_total_ori / $fact_val_tcam;
                            }*/


                            if (empty($dfac_det_dfac)) {
                                $dfac_det_dfac = 'Sin detalle';
                            }

                           
                                $deta_ori .= ' <tr>';
                                $deta_ori .= ' <td style="border-left: #a9a9a9 1px solid; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;">'.$dfac_cod_prod_ori.'</td>';
                                $deta_ori .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($dfac_cant_dfac_ori, 2, '.', ',') . '</td>';
                                $deta_ori .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;">' . $array_unidad_desc[$dfac_cod_unid_ori] . '</td>';
                                $deta_ori .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 28%;">'.$dfac_det_dfac_ori.'</td>';
                                $deta_ori .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($dfac_precio_dfac_ori, 2, '.', ',') . '</td>';
                                $deta_ori .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($descuento_ori, 2, '.', ',') . '</td>';
                                $deta_ori .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($dfac_mont_total_ori, 2, '.', ',') . '</td>';
                                $deta_ori .= ' </tr>';
                                
                                
                        } while ($oCon->SiguienteRegistro());
                    }
                }
                $oCon->Free();

                
                $deta_nc .= ' <tr>';
                $deta_nc .= ' <td style="border-left: #a9a9a9 1px solid; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;">'.$dfac_cod_prod.'</td>';
                $deta_nc .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($dfac_cant_dfac, 2, '.', ',') . '</td>';
                $deta_nc .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;">' . $array_unidad_desc[$dfac_cod_unid] . '</td>';
                $deta_nc .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 28%;">'.$dfac_det_dfac.'</td>';
                $deta_nc .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($dfac_precio_dfac, 2, '.', ',') . '</td>';
                $deta_nc .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($descuento, 2, '.', ',') . '</td>';
                $deta_nc .= ' <td style="border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($dfac_mont_total, 2, '.', ',') . '</td>';
                $deta_nc .= ' </tr>';

            }while ($oIfx->SiguienteRegistro());
               
        }
    }

    $oIfx->Free();

    $total_ncre=$ncre_iva + $ncre_con_miva + $ncre_sin_iva - $totalDescuento;
    $monto_efectivo=round(($total_ncre*(13/100)), 2, PHP_ROUND_HALF_UP);

    if($ncre_cod_mone==$pcon_seg_mone){
        $total_ncre=$total_ncre/$ncre_val_tcam;
        $ncre_iva=$ncre_iva/$ncre_val_tcam;
    }

    $deta_ori .= ' <tr>';
    $deta_ori .= ' <td style="border-top: #a9a9a9 1px solid; width: 12%;"></td>';
    $deta_ori .= ' <td style="border-top: #a9a9a9 1px solid; width: 12%;" align="right" ></td>';
    $deta_ori .= ' <td style="border-top: #a9a9a9 1px solid; width: 12%;"></td>';
    $deta_ori .= ' <td style="border-top: #a9a9a9 1px solid; width: 28%;"></td>';
    $deta_ori .= ' <td colspan="2" style="width: 24%;border-bottom: #a9a9a9 1px solid; border-top: #a9a9a9 1px solid; border-left: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid;" align="right" >MONTO TOTAL ORIGINAL '.$eti_mone.'</td>';
    $deta_ori .= ' <td style="border-bottom: #a9a9a9 1px solid; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($total_ori, 2, '.', ',') . '</td>';
    $deta_ori .= ' </tr>';
    $deta_ori .= '</table>';
    
    $deta_nc .= ' <tr>';
    $deta_nc .= ' <td style="border-top: #a9a9a9 1px solid; width: 12%;"></td>';
    $deta_nc .= ' <td style="border-top: #a9a9a9 1px solid; width: 12%;"></td>';
    $deta_nc .= ' <td style="border-top: #a9a9a9 1px solid; width: 12%;"></td>';
    $deta_nc .= ' <td style="border-top: #a9a9a9 1px solid; width: 28%;"></td>';
    $deta_nc .= ' <td colspan="2" style="width: 24%; border-bottom: #a9a9a9 1px solid; border-top: #a9a9a9 1px solid; border-left: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid;" align="right" >MONTO TOTAL DEVUELTO '.$eti_mone.'</td>';
    $deta_nc .= ' <td style="border-bottom: #a9a9a9 1px solid; border-top: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($total_ncre, 2, '.', ',') . '</td>';
    $deta_nc .= ' </tr>';
    $deta_nc .= ' <tr>';
    $deta_nc .= ' <td style="width: 12%;"></td>';
    $deta_nc .= ' <td style="width: 12%;"></td>';
    $deta_nc .= ' <td style="width: 12%;"></td>';
    $deta_nc .= ' <td style="width: 28%;"></td>';
    $deta_nc .= ' <td colspan="2" style="width: 24%; border-bottom: #a9a9a9 1px solid;  border-left: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid;" align="right" >MONTO EFECTIVO DÉBITO - CRÉDITO '.$eti_mone.'</td>';
    $deta_nc .= ' <td style="border-bottom: #a9a9a9 1px solid; border-right: #a9a9a9 1px solid; width: 12%;" align="right" >' . number_format($monto_efectivo, 2, '.', ',') . '</td>';
    $deta_nc .= ' </tr>';
    $deta_nc .= ' </table>';



    

    $total = number_format($total_ncre, 2, '.', '');
    
    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetrasMone($total_ncre, $moneda));


    $tableLeyenda ='<table border="0"  style="width: 100%;font-size: 13px;margin-top:25px;" cellspacing="0">';
    $tableLeyenda .= '<tr>';

    $tableLeyenda .= '<td valign="top" style="width:80%;">';

    $tableLeyenda .= '<table  style="width:100%;" cellspacing="0" >';
    $tableLeyenda .= '<tr>';
    $tableLeyenda .= '<td style="width:100%;"><b>Son: '.$con_letra.'</b></td>';
    $tableLeyenda .= '</tr>';
    $tableLeyenda .= '<tr>';
    $tableLeyenda .= '<td style="width:100%;" align="center"><br>ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS. EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE
    ACUERDO A LEY</td>';
    $tableLeyenda .= '</tr>';
    $tableLeyenda .= '<tr>';
    $tableLeyenda .= '<td style="font-size: 10px; width:100%;" align="center"><br>'.$ncre_leye_fact.'</td>';
    $tableLeyenda .= '</tr>';
    $tableLeyenda .= '<tr>';
    $tableLeyenda .= '<td style="font-size: 10px; width:100%;" align="center"><br>"Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido en una modalidad de facturación en línea”</td>';
    $tableLeyenda .= '</tr>';
    $tableLeyenda.='</table>';
    
    $tableLeyenda .= '</td>';

    $tableLeyenda .= '<td valign="top" style="width:20%;" align="right">';
    
    $tableLeyenda .= '<table  style="width:100%;" cellspacing="0" align="right">';

    //CODIGO QR

    $barcode = new \Com\Tecnick\Barcode\Barcode();
    
    $datosqr=$ruc_empr.substr($ncre_nse_ncre, 3, 6).'|'.$ncre_num_preimp.'|'.number_format($total, 2, '.', ',').'|'.$ncre_fech_fact.'|'.$tipo_docu.'|'.$ncre_ruc_clie;

    $bobj = $barcode->getBarcodeObj(
        'QRCODE,H',                     // Tipo de Barcode o Qr
        $datosqr,          // Datos
        -3,                             // Width 
        -3,                             // Height
        'black',                        // Color del codigo
        array(-2, -2, -2, -2)           // Padding
        )->setBackgroundColor('white'); // Color de fondo

    $imageData = $bobj->getPngData(); // Obtenemos el resultado en formato PNG
        

    $ruta_dir = DIR_FACTELEC . 'modulos/envio_documentos_bolivia/qr_nota_credito';
        if (!file_exists($ruta_dir)){
            mkdir($ruta_dir,0777,true);
        }
        
    file_put_contents(DIR_FACTELEC . 'modulos/envio_documentos_bolivia/qr_nota_credito/NC_'.$id.'.png', $imageData); // Guardamos el resultado
    
    $ruta=DIR_FACTELEC . 'modulos/envio_documentos_bolivia/qr_nota_credito/NC_'.$id.'.png';


    
    $tableLeyenda .= '<tr>';
    $tableLeyenda .= '<td align="right"><img src="'.$ruta.'"></td>';

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
    $documento .= $logo .'<br><b>DATOS FACTURA ORIGINAL</b>'.$cab_deta.$deta_ori.'<br><b>DATOS DE LA DEVOLUCION O RESCISIÓN</b>'.$cab_deta.$deta_nc.$tableLeyenda;
    $documento .= $legend;
    $documento .= '</page>';


    //file_put_contents("C:/prueba/documento.html", $documento);

    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
    $html2pdf->WriteHTML($documento);
    $ruta_dir= DIR_FACTELEC . 'modulos/envio_documentos_bolivia/upload';
	if (!file_exists($ruta_dir)){
	    mkdir($ruta_dir,0777,true);
	}
    $ruta = DIR_FACTELEC . 'modulos/envio_documentos_bolivia/upload/pdf/cred_' . $nombre_archivo . '.pdf';
    $html2pdf->Output($ruta, 'F');
    $rutaPdf = $ruta;

    return $documento;
}

?>