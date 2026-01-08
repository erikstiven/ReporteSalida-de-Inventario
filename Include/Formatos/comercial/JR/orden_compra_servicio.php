<?

function formato_orden_compra($serial){
 //Definiciones
 global $DSN_Ifx;
 include_once('../../../../Include/config.inc.php');
 include_once(path(DIR_INCLUDE) . 'html2pdf_v4.03/_tcpdf_5.0.002_old/tcpdf.php');

 if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

 $oIfx = new Dbo;
 $oIfx->DSN = $DSN_Ifx;
 $oIfx->Conectar();

 $oIfxA = new Dbo;
 $oIfxA->DSN = $DSN_Ifx;
 $oIfxA->Conectar();


 $idEmpresa = $_SESSION['U_EMPRESA'];
 $array_imp = $_SESSION['U_EMPRESA_IMPUESTO'];


  ///VALIDACION MONEDA
  $sqlmon="select pcon_mon_base, pcon_seg_mone from saepcon where pcon_cod_empr=$idEmpresa";
  $pcon_seg_mone= consulta_string($sqlmon,'pcon_seg_mone', $oIfx,'');


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

//INFORMACION DE LA EMPRESA

 $sql = "select empr_cod_pais,empr_cm1_empr, empr_rimp_sn, empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_iva_empr,empr_tel_resp, empr_ac1_empr, empr_ac2_empr, empr_mai_empr, empr_tip_empr
                                            from saeempr where empr_cod_empr = $idEmpresa ";


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_path_logo');
            $tel_empresa = $oIfx->f('empr_tel_resp');
            $empr_mai_empr = $oIfx->f('empr_mai_empr');
            
        }
    }
    $oIfx->Free();


    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;
    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];


    ///INFORMACION ORDEN DE COMPRA

    $sqlm="select minv_user_web, minv_fmov,minv_tip_ord,  minv_num_sec, minv_cod_clpv, minv_cod_mone,minv_cm1_minv,minv_tot_minv, minv_iva_valo, minv_dge_valo,
    (COALESCE(minv_tot_minv,0) - COALESCE(minv_dge_valo,0) + COALESCE(minv_iva_valo,0) + COALESCE(minv_otr_valo,0) - COALESCE(minv_fle_minv,0) + COALESCE(minv_val_ice,0) ) total,
    minv_fpag_prov, minv_cta_prov, minv_val_tcam 
    from saeminv where minv_num_comp=$serial";
    $secuencial=consulta_string($sqlm, 'minv_num_sec', $oIfx, '');
    $clpv_cod=consulta_string($sqlm, 'minv_cod_clpv', $oIfx, '');
    $mone_cod=consulta_string($sqlm, 'minv_cod_mone', $oIfx, '');
    $concepto=consulta_string($sqlm, 'minv_cm1_minv', $oIfx, '');
    $fpago=consulta_string($sqlm, 'minv_fpag_prov', $oIfx, '');
    $total_orden=consulta_string($sqlm, 'total', $oIfx, '');
    $minv_tot_minv = consulta_string($sqlm, 'minv_tot_minv', $oIfx, '');
    $minv_iva_valo = consulta_string($sqlm, 'minv_iva_valo', $oIfx, '');
    $minv_cta_prov = consulta_string($sqlm, 'minv_cta_prov', $oIfx, '');
    $minv_val_tcam = consulta_string($sqlm, 'minv_val_tcam', $oIfx, '');
    $minv_user_web = consulta_string($sqlm, 'minv_user_web', $oIfx, '');

    if(!empty($minv_user_web)){

        $sql="select concat(usuario_nombre, ' ', usuario_apellido) as usuario from comercial.usuario where usuario_id= $minv_user_web";
        $usuario=consulta_string($sql, 'usuario', $oIfx, '');
    }
    else{
        $usuario='';
    }

    $minv_tip_ord = consulta_string($sqlm, 'minv_tip_ord', $oIfx, '');

    if($minv_tip_ord==1){
        $titulo='SERVICIO';
        $eti_detalle='DE SERVICIO';
    }
    else{
        $titulo='COMPRA';
        $eti_detalle='';
    }

    

    if(empty($minv_cta_prov)){
        $minv_cta_prov ='NULL'; 
    }

    $fecha_emision=consulta_string($sqlm, 'minv_fmov', $oIfx, '');
    $fecha_emision=date('d/m/Y', strtotime($fecha_emision));

    $sql="select mone_des_mone, mone_sgl_mone from saemone where mone_cod_mone=$mone_cod";
    $moneda=consulta_string($sql, 'mone_des_mone', $oIfx, '');

    $sigla_moneda=consulta_string($sql, 'mone_sgl_mone', $oIfx, '');

    //FORMA DE PAGO ORDEN
    if(empty($fpago)){
        $fpago='NULL';
    }
    $sqlf="select fpag_des_fpag from saefpag where fpag_cod_fpag=$fpago;";
    $fpago_prov=consulta_string($sqlf, 'fpag_des_fpag', $oIfx, '');

    //NUMERO DE LA ULTIMA FACTURA GENERADA FACTURA
    
    $sqlfac="select fprv_num_fact, fprv_fec_emis from saefprv where fprv_cod_pedi=$serial order by 2 desc limit 1";
    $factura=consulta_string($sqlfac, 'fprv_num_fact', $oIfx, '');



    //INFORMACION DEL CLIENTE


    $sql      = "select clpv_tip_ctab, clpv_num_ctab, clv_con_clpv, clpv_ruc_clpv,
    clpv_cod_ciud, clpv_cod_zona, clpv_cod_banc,clpv_ruc_tran, clpv_nom_clpv
    from saeclpv where clpv_cod_empr = $idEmpresa and
    clpv_cod_clpv = $clpv_cod ";


    //$banc_cod = consulta_string($sql, 'clpv_cod_banc', $oIfx, '');
    
    $num_cta  = consulta_string($sql, 'clpv_num_ctab', $oIfx, '');
    $nom_clpv = consulta_string($sql, 'clpv_nom_clpv', $oIfx, '');
    $ruc_clpv = consulta_string($sql, 'clpv_ruc_clpv', $oIfx, '');
    


    $sql = "select cash_cod_ban, cash_cod_int, cash_ruc_clpv, cash_tip_cuen, cash_num_cuen, cash_cod_int  
							from cuentas_cash where cash_cod_empr = $idEmpresa and cash_cod_clpv=$clpv_cod and id=$minv_cta_prov
							and cash_est_del='N'";
	$banc_cod    = consulta_string_func($sql, 'cash_cod_ban', $oIfx, 0);
	$num_cta    = consulta_string_func($sql, 'cash_num_cuen', $oIfx, '');
	$cash_cod_int    = consulta_string_func($sql, 'cash_cod_int', $oIfx, '');
	$tipo_cta = consulta_string_func($sql, 'cash_tip_cuen', $oIfx, '');
    
    $sql      = "select max(dire_dir_dire) as dire from saedire where dire_cod_empr = $idEmpresa and dire_cod_clpv = $clpv_cod ";
    $dire_clpv= trim(consulta_string($sql, 'dire', $oIfx, ''));

    $sql      = "select max(tlcp_tlf_tlcp) as tel from saetlcp where tlcp_cod_empr = $idEmpresa and tlcp_cod_clpv = $clpv_cod ";
    $tel_clpv = trim(consulta_string($sql, 'tel', $oIfx, 'SN'));

    $sql      = "select max(emai_ema_emai) as correo from saeemai where emai_cod_empr = $idEmpresa and emai_cod_clpv = $clpv_cod ";
    $correo_clpv = trim(consulta_string($sql, 'correo', $oIfx, ''));

    if(empty($banc_cod)){
        $banc_cod='NULL';
    }

    $sql      = "SELECT banc_cod_bsri, banc_nom_banc from saebanc where
                                banc_cod_empr = $idEmpresa and
                                banc_cod_banc = $banc_cod";
    $nom_banco= consulta_string($sql, 'banc_nom_banc', $oIfx, '');




    //LOGO

    $logo ='<table border="0"  style=" font-size:14px; width:100%;" cellspacing="0" >';
    $logo .= '<tr>';
    $logo .= '<td align="left"  width="400"><img width="120px;"  src="' . $path_logo_img . '"></td>';
    
    $logo .= '<td width="302" style="font-size:14px;" align="left">';
    $logo .= '<table  style="border: black 1px solid ; border-radius: 5px; " cellspacing="0" align="right">';
    $logo .= '<tr style="font-size:14px;">';
    $logo .= '<td width="180" height="50" align="center"><b>Fecha de Emisión:</b><br>'.$fecha_emision.'</td>';
    $logo .= '</tr>';
    $logo.='</table>';
    
    $logo.='</td>';

    $logo .= '</tr>';
    $logo .= '</table>';

    //ENCABEZADO
    $logo .= '<table  style="border: black 1px solid ;  " cellspacing=5 >';
    $logo .= '<tr style="font-size:14px;" align="center">';
    $logo .= '<td width="695"><b>ORDEN DE '.$titulo.' '.$secuencial.'</b></td>';
    $logo .= '</tr>';
    $logo .='</table>';

    $logo .= '<table  style="margin-top:10px; border: black 1px solid ; " cellspacing=1 >';
    $logo .= '<tr style="font-size:12px;">';
    $logo .= '<td width="100"><b>COMPRADOR:</b></td>';
    $logo .= '<td width="330">'.$razonSocial.'</td>';
    $logo .= '<td width="80"><b>TELEFONO:</b></td>';
    $logo .= '<td  width="180">'.$tel_empresa.'</td>';
    $logo .= '</tr>';
    $logo .= '<tr style="font-size:12px;">';
    $logo .= '<td  width="100"><b>DIRECCION:</b></td>';
    $logo .= '<td  width="330">'.$dirMatriz.'</td>';
    $logo .= '<td  width="80"><b>EMAIL:</b></td>';
    $logo .= '<td  width="180">'.$empr_mai_empr.'</td>';
    $logo .= '</tr>';
    $logo .= '<tr style="font-size:12px;">';
    $logo .= '<td  width="100"><b>RUC:</b></td>';
    $logo .= '<td  width="330">'.$ruc_empr.'</td>';
    $logo .= '<td  width="80"></td>';
    $logo .= '<td  width="180"></td>';
    $logo .= '</tr>';
    $logo .='</table>';

    $logo .= '<table  style="border: black 1px solid ;" cellspacing=1>';
    $logo .= '<tr style="font-size:12px;">';
    $logo .= '<td  width="705" height="3"></td>';
    $logo .= '</tr>';
    $logo .='</table>';

    $logo .= '<table  style="border: black 1px solid ; " cellspacing=1 >';
    $logo .= '<tr style="font-size:12px;">';
    $logo .= '<td width="100"><b>SEÑORES:</b></td>';
    $logo .= '<td width="330">'.$nom_clpv.'</td>';
    $logo .= '<td width="80"><b>EMAIL:</b></td>';
    $logo .= '<td  width="180">'.$correo_clpv.'</td>';
    $logo .= '</tr>';
    $logo .= '<tr style="font-size:12px;">';
    $logo .= '<td  width="100"><b>DIRECCION:</b></td>';
    $logo .= '<td  width="330">'.$dire_clpv.'</td>';
    $logo .= '<td  width="80"><b>CONTACTO:</b></td>';
    $logo .= '<td  width="180"></td>';
    $logo .= '</tr>';
    $logo .= '<tr style="font-size:12px;">';
    $logo .= '<td  width="100"><b>RUC:</b></td>';
    $logo .= '<td  width="330">'.$ruc_clpv.'</td>';
    $logo .= '<td  width="80"><b>GARANTIA</b></td>';
    $logo .= '<td  width="180"></td>';
    $logo .= '</tr>';
    $logo .= '<tr style="font-size:12px;">';
    $logo .= '<td  width="100"></td>';
    $logo .= '<td  width="330"></td>';
    $logo .= '<td  width="80"><b>TELEFONO:</b></td>';
    $logo .= '<td  width="180">'.$tel_clpv.'</td>';
    $logo .= '</tr>';
    $logo .='</table>';

    ///DETALLE DEL SERVICIO


    $logo .= '<table  style="margin-top:10px; border: black 1px solid ; " cellspacing=1 >';
    $logo .= '<tr style="font-size:12px;">';
    $logo .= '<td width="100"><b>CONCEPTO:</b></td>';
    $logo .= '<td width="330">'.$concepto.'</td>';
    $logo .= '<td width="80"><b>MONEDA:</b></td>';
    $logo .= '<td  width="180">'.$moneda.'</td>';
    $logo .= '</tr>';
    $logo .= '<tr style="font-size:12px;">';
    $logo .= '<td  width="100"><b>FORMA DE PAGO:</b></td>';
    $logo .= '<td  width="330">'.$fpago_prov.'</td>';
    $logo .= '<td  width="80"><b>BANCO:</b></td>';
    $logo .= '<td  width="180">'.$nom_banco.'</td>';
    $logo .= '</tr>';
    $logo .= '<tr style="font-size:12px;">';
    $logo .= '<td  width="100"><b>FACTURA:</b></td>';
    $logo .= '<td  width="330">'.$factura.'</td>';
    $logo .= '<td  width="80"><b>N° CUENTA:</b></td>';
    $logo .= '<td  width="180">'.$num_cta.'</td>';
    $logo .= '</tr>';
    $logo .= '<tr style="font-size:12px;">';
    $logo .= '<td  width="100"></td>';
    $logo .= '<td  width="330"></td>';
    $logo .= '<td  width="80"><b>CCI:</b></td>';
    $logo .= '<td  width="180">'.$cash_cod_int.'</td>';
    $logo .= '</tr>';
    $logo .='</table>';



    $sqlDeta = "select * from saedmov where dmov_num_comp = $serial and 
                dmov_cod_empr = $idEmpresa  ";

    $deta = ' <table style="width: 99.3%;  font-size: 12px; border: black 1px; " cellspacing=0>';
    $deta .= ' <tr>';

    $deta .= ' <b> <td style="width: 5%; font-size:12px; border-bottom: black 1px solid;" align="center" height="20">ITEM</td> </b>';
    $deta .= ' <b> <td style="border-left: black 1px solid; border-bottom: black 1px solid; width: 63%; font-size:12px;" align="center" height="20">DESCRIPCIÓN</td> </b>';
    $deta .= ' <b> <td style="border-left: black 1px solid; border-bottom: black 1px solid; width:  10%; font-size:12px;" align="center" height="20">VALOR '.$eti_detalle.' C/U</td> </b>';
    $deta .= ' <b> <td style="border-left: black 1px solid; border-bottom: black 1px solid; width:  12%; font-size:12px;" align="center" height="20">CANTIDAD</td> </b>';
    $deta .= ' <b> <td style="border-left: black 1px solid; border-bottom: black 1px solid; width: 10%; font-size:12px;" align="center" height="20">PRECIO TOTAL</td> </b>';
    $deta .= ' </tr>';


    $sqlDeta = "select  d.dmov_cod_prod, d.dmov_cod_bode, d.dmov_cod_unid,  
    d.dmov_can_dmov, d.dmov_can_entr, d.dmov_det1_dmov,
    p.prod_nom_prod, d.dmov_cun_dmov, pr.prbo_iva_porc,
    dmov_fmov, dmov_cod_ccos
    from saedmov d , saeprod p, saeprbo pr where
    p.prod_cod_prod = d.dmov_cod_prod and
    p.prod_cod_prod = pr.prbo_cod_prod and
    p.prod_cod_empr = pr.prbo_cod_empr and
    p.prod_cod_sucu = pr.prbo_cod_sucu and
    d.dmov_cod_bode = pr.prbo_cod_bode and
    p.prod_cod_empr = $idEmpresa and
    d.dmov_num_comp = $serial and
    d.dmov_cod_empr = $idEmpresa 
    order by d.dmov_cod_dmov ";


    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            $ctrl=0;
            $i=1;
            do {

                $cantidad= $oIfx->f('dmov_can_dmov');
                $costo = $oIfx->f('dmov_cun_dmov');
                $iva = $oIfx->f('prbo_iva_porc');
                $dmov_cod_ccos = $oIfx->f('dmov_cod_ccos');

                if($minv_tip_ord==1){
                $detalle = $oIfx->f('dmov_det1_dmov');
                }
                else{
                    $detalle = $oIfx->f('prod_nom_prod').' '.$oIfx->f('dmov_det1_dmov'); 
                }

                if (empty($iva)) {
                    $iva = 0;
                }

                // TOTAL
                $total_fac = 0;
                $descuento = 0;
                $descuento_2 = 0;
                $descuento_general = 0;
                $dsc1 = ($costo * $cantidad * $descuento) / 100;
                $dsc2 = ((($costo * $cantidad) - $dsc1) * $descuento_2) / 100;
                if ($descuento_general > 0) {
                    // descto general
                    $dsc3 = ((($costo * $cantidad) - $dsc1 - $dsc2) * $descuento_general) / 100;
                    $total_fact_tmp = ((($costo * $cantidad) - ($dsc1 + $dsc2 + $dsc3)));
                    $tmp = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                } else {
                    // sin descuento general
                    $total_fact_tmp = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                    $tmp = $total_fact_tmp;
                }

                $total_fac = round($total_fact_tmp, 2);

                // total con iva
                if ($iva > 0) {
                    $total_con_iva = round((($total_fac * $iva) / 100), 2) + $total_fac;
                } else {
                    $total_con_iva = $total_fac;
                }

                //VALIDAICON MONEDA INTERNACIONAL
              /*  if($mone_cod==$pcon_seg_mone){
                    $costo=$costo/$minv_val_tcam;
                    $total_fac=$total_fac/$minv_val_tcam;
                }*/


                $deta .= ' <tr>';
                $deta .= ' <td align="center" style="width: 5%; border-bottom: black 1px solid;" >' . $i . '</td>';
                $deta .= ' <td align="left"   style="width: 63%; border-bottom: black 1px solid; border-left: black 1px solid;">' . $detalle . '</td>';
                $deta .= ' <td align="right"  style="width: 10%; border-bottom: black 1px solid; border-left: black 1px solid;">' . number_format($costo, 2, '.', ',') . '</td>';
                $deta .= ' <td align="right"  style="width: 12%; border-bottom: black 1px solid; border-left: black 1px solid;">' . number_format($cantidad, 2, '.', ',') . '</td>';
                $deta .= ' <td align="right"  style="width: 10%; border-bottom: black 1px solid; border-left: black 1px solid;">' . number_format($total_fac, 2, '.', ',') . '</td>';
                $deta .= ' </tr>';



                $ctrl++;

            }while ($oIfx->SiguienteRegistro());
            for ($i=$ctrl; $i <=5 ; $i++) { 
                # code...
                $deta .= ' <tr>';
                $deta .= ' <td align="center"  style="width: 5%; border-bottom: black 1px solid;"  >&nbsp;</td>';
                $deta .= ' <td style="width: 63%; border-bottom: black 1px solid; border-left: black 1px solid;"></td>';
                $deta .= ' <td align="right" style="width: 10%; border-bottom: black 1px solid;border-left: black 1px solid;"></td>';
                $deta .= ' <td align="right" style="width: 12%; border-bottom: black 1px solid;border-left: black 1px solid;"></td>';
                $deta .= ' <td align="right" style="width: 10%; border-bottom: black 1px solid;border-left: black 1px solid;"></td>';
                $deta .= ' </tr>';
            }
        }
    }
    $deta .= ' </table>';

     //VALIDAICON MONEDA INTERNACIONAL
    /*if($mone_cod==$pcon_seg_mone){
    $minv_tot_minv=$minv_tot_minv/$minv_val_tcam;
    $minv_iva_valo=$minv_iva_valo/$minv_val_tcam;
    $total_orden=$total_orden/$minv_val_tcam;
    }*/

    $total = number_format($total_orden, 2, '.', '');
    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetrasMonePeru($total, $moneda));


    $totales ='<table style="margin: 0px; border: black 1px solid ; width: 99.3%;  font-size: 12px;" cellspacing="0"  align="left">';
    $totales .='<tr>
    <td  valign="middle" width="400" ><table  style=" font-size: 12px;"   cellspacing="0" align="left" >
    <tr>
    <td width="480" ><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>SON: '.$con_letra.'</u></b></td>
    </tr>';

  
    
    $totales .='</table>';
    
    $totales.='</td>
    <td valign="top" width="200" >';

    $totales .= '<table style=" font-size: 12px;"   cellspacing="0" align="left" >';

    $totales .= ' <tr>';
    $totales .= ' <b> <td width="100" height="18" align="right">SUB TOTAL '.$sigla_moneda.'</td> </b>';
    $totales .= ' <td  width="115" align="right">' . number_format($minv_tot_minv, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

   

    $totales .= ' <tr>';
    $totales .= ' <b> <td width="100" height="18" align="right">'.$array_imp ['IVA'].' '.$array_porc ['IVA'].'% '.$sigla_moneda.'</td> </b>';
    $totales .= ' <td width="115" align="right">' . number_format( $minv_iva_valo, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';


    $totales .= ' <tr>';
    $totales .= ' <b> <td width="100" height="18" align="right">TOTAL A PAGAR '.$sigla_moneda.'</td> </b>';
    $totales .= ' <td width="115" align="right">' . number_format($total, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' </table>';


    $totales.='</td>
    </tr>';
    $totales .='</table>';

    //NOTA 

    $nota = '<table  style="border: black 1px solid ;  margin-top:5px;" cellspacing=5 >';
    $nota .= '<tr style="font-size:10px;" >';
    $nota .= '<td width="695"><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>NOTA:</u><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1. Factura a Nombre de: '.$razonSocial.' // RUC: '.$ruc_empr.'<br>';
    $nota .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. Anexar orden de compra y guia de remision, debidamente firmada por el jefe de logística<br>';
    $nota .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3. Presentar factura original</b></td>';
    $nota .= '</tr>';
    $nota .='</table><br><div style="font-size:10px;"><b>AUTORIZACIONES:</b></div>';

    //FIRMAS

    $firmas = '<table style="width:100%; margin-top: 65px" align="center" >
        <tr>

            <td style="width:21.25%; font-size:10px; text-align: center;border-top : 2px solid black;"><b>'.$usuario.'<br>LOGISTICA</b></td>
            <td style="width:5%;"></td>
            <td style="width:21.25%; font-size:10px; text-align: center;border-top : 2px solid black;"><b>CONTABILIDAD</b></td>
            <td style="width:5%;"></td>
            <td style="width:21.25%;font-size:10px; text-align: center;border-top : 2px solid black;"><b>REYNA B. CHAVARRIA ORIA<br>GERENTE GENERAL<br>'.$razonSocial.'</b></td>
            <td style="width:5%;"></td>
            <td style="width:21.25%;font-size:10px; text-align: center;border-top : 2px solid black;"><b>TESORERIA</b></td>
        
        </tr>
            </table>';



    $documento .= '<page backimgw="100%" backtop="5mm" backbottom="5mm" backleft="5mm" backright="5mm">';
    $documento .= $logo.$deta.$totales.$nota.$firmas;
    $documento .= '</page>';


    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
    $html2pdf->WriteHTML($documento);
    $ruta = DIR_FACTELEC . 'Include/orden_compra';
        if (!file_exists($ruta)){
            mkdir($ruta,0777,true);
        }

    $ruta = DIR_FACTELEC . 'Include/orden_compra/ORDEN_COMPRA_' . $serial . '.pdf';
    $html2pdf->Output($ruta, 'F');
    $rutaPdf = $ruta;

    return $documento;


}

?>