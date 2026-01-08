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

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();

    $idEmpresa      = $_SESSION['U_EMPRESA'];
    $idSucursal     = $_SESSION['U_SUCURSAL'];
    $mone_sigla     = $_SESSION['U_MONE_SIGLA'];
    $valida_pais    = $_SESSION['S_PAIS_API_SRI'];

    $sqlMonedaEm = "SELECT pcon_mon_base from saepcon where pcon_cod_empr = $idEmpresa";
    $moneda_emp = consulta_string($sqlMonedaEm, 'pcon_mon_base', $oIfx, 0);

    $sqlPuntEmi = "SELECT para_punt_emi from saepara where para_cod_sucu = $idSucursal";
    $para_punt_emi = consulta_string($sqlPuntEmi, 'para_punt_emi', $oIfx, 0);

    $moneda_emp_ = "SELECT * from saemone where mone_cod_mone = $moneda_emp  ";
    $moneda_emp_local = consulta_string($moneda_emp_, 'mone_sgl_mone', $oIfx, '');
    $moneda_des_mone = consulta_string($moneda_emp_, 'mone_des_mone', $oIfx, '');
    $moneda_emp_local_sm = consulta_string($moneda_emp_, 'mone_smb_mene', $oIfx, '');

    $sqlMonedaEm = "SELECT aufa_nau_fact, aufa_fac_inic, aufa_fac_fina, aufa_nse_fact, aufa_ffi_fact  from saeaufa where aufa_cod_empr = $idEmpresa AND aufa_cod_sucu = $idSucursal";
    $aufa_nau_fact = consulta_string($sqlMonedaEm, 'aufa_nau_fact', $oIfx, 0);
    $aufa_fac_inic = consulta_string($sqlMonedaEm, 'aufa_fac_inic', $oIfx, 0);
    $aufa_fac_fina = consulta_string($sqlMonedaEm, 'aufa_fac_fina', $oIfx, 0);
    $aufa_nse_fact = consulta_string($sqlMonedaEm, 'aufa_nse_fact', $oIfx, 0);
    $aufa_ffi_fact = consulta_string($sqlMonedaEm, 'aufa_ffi_fact', $oIfx, 0);
    // UNIDAD
    $sql = "select unid_cod_unid, unid_nom_unid from saeunid where unid_cod_empr = $idEmpresa ";
    unset($array_unid);
    $array_unid = array_dato($oIfx, $sql, 'unid_cod_unid', 'unid_nom_unid');

    $sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_cod_pais,
                empr_num_resu, empr_path_logo, empr_iva_empr , empr_num_dire, empr_fax_empr,
                empr_sitio_web, empr_cm1_empr, empr_cm2_empr , empr_prec_sucu, empr_tel_resp, empr_mai_empr, empr_ema_comp
                from saeempr where empr_cod_empr = $idEmpresa ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_path_logo');
            if ($oIfx->f('empr_conta_sn') == 'S')
                $empr_conta_sn = 'SI';
            else
                $empr_conta_sn = 'NO';

            $empr_num_resu = $oIfx->f('empr_num_resu');
            $empr_iva_empr = $oIfx->f('empr_iva_empr');
            $empr_num_dire = $oIfx->f('empr_num_dire');
            $empr_fax_empr = $oIfx->f('empr_fax_empr');
            $empr_sitio_web= $oIfx->f('empr_sitio_web');
            $empr_cm1_empr = $oIfx->f('empr_cm1_empr');
            $empr_cm2_empr = $oIfx->f('empr_cm2_empr');
            $empr_prec_sucu = $oIfx->f('empr_prec_sucu');
            $empr_tel_resp = $oIfx->f('empr_tel_resp');
            $empr_cod_pais = $oIfx->f('empr_cod_pais');
            $empr_mai_empr = $oIfx->f('empr_mai_empr');
            $empr_ema_comp = $oIfx->f('empr_ema_comp');
        }
    }
    $oIfx->Free();

    //selecciona sucursales y direcciones
    $sql_sucu = "select sucu_nom_sucu, sucu_dir_sucu from saesucu where sucu_cod_empr = $idEmpresa and sucu_cod_sucu = $idSucursal ";
    if ($oIfx->Query($sql_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
                $sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $sql_sucu = "SELECT etiqueta, porcentaje from comercial.pais_etiq_imp where pais_cod_pais = $empr_cod_pais and impuesto = 'IVA' ";
    if ($oIfx->Query($sql_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $etiqueta_iva = $oIfx->f('etiqueta');
                $porcentaje_iva = $oIfx->f('porcentaje');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    //selecciona sucursales y direcciones
    $sql_sucu = "SELECT identificacion from comercial.tipo_iden_clpv_pais where pais_cod_pais = $empr_cod_pais and id_iden_clpv = 1";
    if ($oIfx->Query($sql_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $tip_ruc_pais = $oIfx->f('identificacion');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $sql = "select fact_cod_fact, fact_tip_vent, fact_cm4_fact,fact_cm1_fact from saefact where
                    fact_cod_empr = $idEmpresa and
                    fact_cod_fact = $id and 
                    fact_cod_clpv = $cliente";
    $contador = consulta_string($sql, 'fact_cod_fact', $oIfx, 0);
    $comprobante = consulta_string($sql, 'fact_tip_vent', $oIfx, 0);
    $descuento_temp = consulta_string($sql, 'fact_cm4_fact', $oIfx, 0);
    $observacion = consulta_string($sql, 'fact_cm1_fact', $oIfx, '');


    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;


    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

    if (file_exists($path_logo_img)) {
        $logo_empresa=' <img width="170px;"  src="' . $path_logo_img . '">';
    }
    else{
        $logo_empresa='<div style="color:red;">LOGO NO CARGADO</div>';
    }



    if ($contador > 0) {
        // TOTAL FACTURA
        $sqlFPago = "select sum(fx.fxfp_val_fxfp) as total
                            from saefact f, saefxfp fx, saefpag fp
                            where f.fact_cod_fact = fx.fxfp_cod_fact and
                            fp.fpag_cod_fpag = fx.fxfp_cod_fpag and
                            f.fact_cod_empr = $idEmpresa and
                            f.fact_cod_sucu = $idSucursal and
                            f.fact_cod_fact = $id ";
        $tot_fxfp = consulta_string($sqlFPago, 'total', $oIfx, 0);

        //print_r($sqlFPago);exit;

        $sqlFac = "select * from saefact where fact_cod_fact = $id  ";

        if ($oIfx->Query($sqlFac)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $fact_nse_fact      = $oIfx->f('fact_nse_fact');
                    $fact_num_preimp    = $oIfx->f('fact_num_preimp');
                    $fact_auto_sri      = $oIfx->f('fact_auto_sri');
                    $fact_fech_sri      = $oIfx->f('fact_fech_sri');
                    $fact_nom_cliente   = $oIfx->f('fact_nom_cliente');
                    $fact_fech_fact     = fecha_mysql_func($oIfx->f('fact_fech_fact'));
                    $fact_ruc_clie      = $oIfx->f('fact_ruc_clie');
                    $fact_tlf_cliente   = $oIfx->f('fact_tlf_cliente');
                    $fact_dir_clie      = htmlentities($oIfx->f('fact_dir_clie'));
                    $fact_email_clpv    = str_replace(' ', '', $oIfx->f("fact_email_clpv"));
                    $fact_con_miva      = $oIfx->f('fact_con_miva');
                    $fact_sin_miva      = $oIfx->f('fact_sin_miva');
                    $fact_tot_fact      = $oIfx->f('fact_tot_fact');
                    $fact_iva           = $oIfx->f('fact_iva');
                    $fact_ice           = $oIfx->f('fact_ice');
                    $fact_val_irbp      = $oIfx->f('fact_val_irbp');
                    $fact_clav_sri      = $oIfx->f('fact_clav_sri');
                    $fact_dsg_valo      = $oIfx->f('fact_dsg_valo');
                    $fact_cm2_fact      = $oIfx->f("fact_cm2_fact");
                    $fact_cm4_fact      = $oIfx->f("fact_cm4_fact");
                    $fact_cod_clpv      = $oIfx->f("fact_cod_clpv");
                    $fact_dia_plazo     = $oIfx->f("fact_dia_fact");
                    $fact_tip_vent      = $oIfx->f("fact_tip_vent");
                    $fact_fech_venc     = fecha_mysql_func($oIfx->f("fact_fech_venc"));
                    $fact_cod_contr     = $oIfx->f("fact_cod_contr");
                    $fact_user_web      = $oIfx->f("fact_user_web");
                    $fact_nau_fact      = $oIfx->f("fact_nau_fact");
                    $fact_serie_fiscal  = $oIfx->f("fact_serie_fiscal");
                    $fact_num_fiscal    = $oIfx->f("fact_num_fiscal");
                    $fact_hor_fin       = $oIfx->f("fact_hor_fin");
                    $fact_cod_mone      = $oIfx->f("fact_cod_mone");
                    $fact_cm7_fac       = $oIfx->f("fact_cm7_fac");

                    $sql = "select tcmp_des_tcmp from saetcmp where tcmp_cod_tcmp = '$fact_tip_vent'";
                    $tcmp_des_tcmp = 'BOLETA ELECTRONICA';//;consulta_string_func($sql, 'tcmp_des_tcmp', $oIfxA, '');

                    if($fact_dia_plazo==0){
                        $sql            = "select fxfp_num_dias, fxfp_fec_fin from saefxfp where fxfp_cod_fact = $id and fxfp_cod_empr = $idEmpresa and fxfp_cod_sucu = $idSucursal ";
                        $fact_dia_plazo = consulta_string_func($sql, 'fxfp_num_dias', $oIfxA, 0);
                        $fact_fech_venc = fecha_mysql_func(consulta_string_func($sql, 'fxfp_fec_fin',  $oIfxA, ''));

                    }

                    $fact_dir_clie = $fact_dir_clie;

                    $logo .= '<b><td style="width: 40%; border: 1px solid black; border-radius: 5px;" align="center">';
                    $logo .= ' <table align="center" style="font-size: 15px;">';
                    $logo .= ' <tr>';
                    $logo .= '<td style="border-bottom: 1px inset black;">'.$tcmp_des_tcmp.'</td>';
                    $logo .= ' </tr>';
                    $logo .= ' <tr>';
                    $logo .= '<td>SERIE:</td>';
                    $logo .= ' </tr>';
                    $logo .= ' <tr>';
                    $logo .= '<td>' . substr($fact_nse_fact, 0, 3) . '-' . substr($fact_nse_fact, 3, 6) . '</td>';
                    $logo .= ' </tr>';
                    $logo .= ' <tr>';
                    $logo .= '<td style="font-size: 17px; color: red; border-bottom: 1;">' . $fact_num_preimp . '</td>';
                    $logo .= ' </tr>';
                    $logo .= ' <tr>';
                    $logo .= '<td>FECHA :</td>';
                    $logo .= ' </tr>';
                    $logo .= ' <tr>';
                    $logo .= '<td style="border-bottom:1">' . $fact_fech_fact . '</td>';
                    $logo .= ' </tr>';

                    $logo .= ' </table>';
                    $logo .= '</td></b>';
                    $logo .= '</tr>';
                    $logo .= '</table>';
                    $logo .= ' <br>';

                    if(!empty($fact_cod_contr)){
                        $sql = "SELECT id_sector, id_barrio, ruta, id_clpv, codigo FROM isp.contrato_clpv WHERE id = $fact_cod_contr";
                        if($oCon->Query($sql)){
                            if($oCon->NumFilas() > 0){
                                $id_sector  = $oCon->f('id_sector');
                                $id_barrio  = $oCon->f('id_barrio');
                                $ruta       = $oCon->f('ruta');
                                $id_clpv    = $oCon->f('id_clpv');
                                $codigo_clpv    = $oCon->f('codigo');
                            }
                        }
                        $oCon->Free();

                        $sql = "SELECT clv_con_clpv from saeclpv where clpv_cod_clpv = $id_clpv";
                        $clv_con_clpv = consulta_string_func($sql, 'clv_con_clpv', $oCon, '');

                        if(strlen($clv_con_clpv)==0){
                            $clv_con_clpv = 2;
                        }
                        
                        //TIPO DE IDENTIFICACION DEL CLIENTE
                        $sql_sucu = "SELECT identificacion from comercial.tipo_iden_clpv_pais where pais_cod_pais = $empr_cod_pais and id_iden_clpv = '$clv_con_clpv'";
                        if ($oIfx->Query($sql_sucu)) {
                            if ($oIfx->NumFilas() > 0) {
                                do {
                                    $tip_iden_cliente = $oIfx->f('identificacion');
                                } while ($oIfx->SiguienteRegistro());
                            }
                        }
                        $oIfx->Free();


                        if(!empty($id_sector)){ //sector
                            $sql = "SELECT sector FROM comercial.sector_direccion WHERE id = $id_sector";
                            $sector = consulta_string_func($sql, 'sector', $oCon, '');
                        }

                        if(!empty($id_barrio)){ //sector
                            $sql = "SELECT barrio FROM isp.int_barrio WHERE id = $id_barrio";
                            $barrio = consulta_string_func($sql, 'barrio', $oCon, '');
                        }

                    }

                    $cliente = ' <table style="width: 100%; border:1px solid black; border-radius: 5px; padding: 2px; font-size: 15x;">
                                    <tr>
                                    <b><td style="width: 10%;">CLIENTE:</td></b>
                                    <td style="width: 48%; ">' . htmlentities($fact_nom_cliente) . '</td>
                                    <b><td style="width: 13%; ">FECHA EMISION:</td></b>
                                    <td style="width: 29% ">' . $fact_fech_fact . '</td>
                                    </tr>
                                    <tr>
                                    <b><td style="width: 10% ">NIT.:</td></b>
                                    <td style="width: 48% ">' . $fact_ruc_clie . '</td>
                                    <b><td style="width: 13% ">TELEFONO:</td></b>
                                    <td style="width: 29% ">' . $fact_tlf_cliente . '</td>
                                    </tr>
                                    <tr>
                                    <b><td style="width: 10% ">DIRECCION:</td></b>
                                    <td style="width: 48% ">' . htmlentities($fact_dir_clie) . '</td>
                                    <b><td style="width: 13% ">EMAIL:</td></b>
                                    <td style="width: 29% ">' . $fact_email_clpv . '</td>
                                    </tr>
                                    <tr>
                                    <b><td style="width: 10% ">SECTOR:</td></b>
                                    <td style="width: 48% ">' . htmlentities($sector) . ' '.htmlentities($barrio).'</td>
                                    <b><td style="width: 13% ">CONTRATO:</td></b>
                                    <td style="width: 29% ">' . $fact_email_clpv . '</td>
                                    </tr>
                                </table>
                    <br>';
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $sql_sucu = "SELECT usuario_nombre, usuario_apellido from comercial.usuario where usuario_id = $fact_user_web";
        if ($oIfx->Query($sql_sucu)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $usuario_nombre = $oIfx->f('usuario_nombre');
                    $usuario_apellido = $oIfx->f('usuario_apellido');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $nombre_cajero = $usuario_nombre." ".$usuario_apellido;

        $sqlDeta = "SELECT * from saedfac where dfac_cod_fact = $id and 
                        dfac_cod_empr = $idEmpresa 
                        order by dfac_cod_mes, dfac_precio_dfac desc ";

        $sqlDeta1 = "SELECT sum(dfac_precio_dfac) as dfac_precio_dfac, 
                            sum(dfac_mont_total) as dfac_mont_total,
                            dfac_det_dfac,  dfac_cod_mes , dfac_por_iva from saedfac where
                            dfac_cod_fact = $id and 
                            dfac_cod_empr = $idEmpresa
                            group by dfac_det_dfac,  dfac_cod_mes , dfac_por_iva order by dfac_cod_mes ";
        $tot_det = 0;
        if ($oIfx->Query($sqlDeta)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $dfac_precio_dfac   = $oIfx->f('dfac_precio_dfac');
                    $dfac_por_iva       = $oIfx->f('dfac_por_iva');

                    if ($dfac_por_iva > 0) {
                        $porcIva = $dfac_por_iva;
                    }

                    if($dfac_por_iva>0){
                        $dfac_precio_dfac = round(($dfac_precio_dfac + ($dfac_precio_dfac * $dfac_por_iva/100 )),0);
                    }

                    $tot_det += round($dfac_precio_dfac,0);
                }while ($oIfx->SiguienteRegistro());
            }
        }

        $dif = round(($tot_fxfp - $tot_det),2);

        $deta = ' <table style="width: 100%;">
                    <tr>
                        <td colspan="3" style="border-top: 1px solid black;"  width="200"></td>
                    </tr>';
        $deta .= ' <tr>';
        $deta .= ' <b> <td style="width: 20; font-size: 9px;font-family: Arial;" align="center">CANT</td> </b>';
        $deta .= ' <b> <td style="width: 130; font-size: 9px;font-family: Arial;" align="center">DESCRIPCION</td> </b>';
        $deta .= ' <b> <td style="width: 50; font-size: 9px;font-family: Arial;" align="center">IMPORTE</td> </b>';
        $deta .= ' </tr>
                    <tr>
                        <td colspan="3" style="border-top: 1px solid black;"></td>
                    </tr>';

        $desc_monto = 0;
        $monto_precio = 0;
        $rd = 1;
        if ($oIfx->Query($sqlDeta)) {
            if ($oIfx->NumFilas() > 0) {
                $porcIva = '';
                $totalDescuento = 0;
                do {
                    $dfac_cod_prod      = $oIfx->f('dfac_cod_prod');
                    $dfac_nom_prod      = $oIfx->f('dfac_nom_prod');
                    $dfac_cant_dfac     = $oIfx->f('dfac_cant_dfac');
                    $dfac_precio_dfac   = $oIfx->f('dfac_precio_dfac');
                    $dfac_des1_dfac     = $oIfx->f('dfac_des1_dfac');
                    $dfac_des2_dfac     = $oIfx->f('dfac_des2_dfac');
                    $dfac_por_dsg       = $oIfx->f('dfac_por_dsg');
                    $dfac_mont_total    = $oIfx->f('dfac_mont_total');
                    $dfac_lote          = $oIfx->f('dfac_cod_lote');
                    $dfac_num_comp      = $oIfx->f('dfac_tip_dfac');
                    $dfac_por_iva       = $oIfx->f('dfac_por_iva');
                    $dfac_lote_fcad     = $oIfx->f('dfac_lote_fcad');
                    $dfac_cod_unid      = $array_unid[$oIfx->f('dfac_cod_unid')];
                    $dfac_cod_mes       = $oIfx->f('dfac_cod_mes');
                    $dfac_det_dfac      = $oIfx->f('dfac_det_dfac');

                    if ($dfac_por_iva > 0) {
                        $porcIva = $dfac_por_iva;
                    }

                    $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;
                    if ($descuento > 0)
                        $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                    else
                        $descuento = 0;

                    $totalDescuento = $totalDescuento + $descuento;

                    if($dfac_por_iva>0){
                        $dfac_precio_dfac = round(($dfac_precio_dfac + ($dfac_precio_dfac * $dfac_por_iva/100 )),2);
                    }


                    $mes_pago = '';
                    if( !empty($dfac_cod_mes) ){
                        $sql = "SELECT c.mes, c.anio FROM  isp.contrato_pago c WHERE
                                            c.id IN ( $dfac_cod_mes  )  ";
                        $mes_pago = 'MENSUAL: ';
                        if ($oCon->Query($sql)) {
                            if ($oCon->NumFilas() > 0) {
                                do {
                                    $mes_pago = Mes_func($oCon->f('mes')) .'/'.$oCon->f('anio');
                                } while ($oCon->SiguienteRegistro());
                            }
                        }
                        $oCon->Free();
                    }

                    $l =  strlen($dfac_det_dfac);
                    if(empty($dfac_det_dfac) || $l>100){
                        //PRODUCTO
                        $sqlDescripcionProd = "select prod_nom_prod, prod_cod_barra, prod_sn_noi, prod_sn_exe ,prod_cod_tpro  from saeprod where 
                                                    prod_cod_prod = '$dfac_cod_prod' and 
                                                    prod_cod_empr = $idEmpresa and 
                                                    prod_cod_sucu = $idSucursal ";
                        $prod_nom_prod = consulta_string_func($sqlDescripcionProd, 'prod_nom_prod', $oIfxA, 0);
                        $dfac_det_dfac = $prod_nom_prod. ' '.$mes_pago ;
                    }

                    $deta .= ' <tr>';
                    $deta .= ' <td style="width: 20;  font-family: Arial; font-size: 9px;" align="right">' . round($dfac_cant_dfac) . '</td>';
                    $deta .= ' <td style="width: 130; font-family: Arial; font-size: 9px;">' . $dfac_det_dfac . '</td>';
                    $deta .= ' <td style="width: 50;  font-family: Arial; font-size: 9px;" align="right">' . number_format($dfac_mont_total, 2, '.', ',') . '</td>';
                    $deta .= ' </tr>';

                    $monto_precio += round($dfac_precio_dfac*$dfac_cant_dfac,2);
                    $rd++;
                }while ($oIfx->SiguienteRegistro());
            }
        }

        $deta .= '<tr>
                        <td colspan="3" style="border-top: 1px solid black;"></td>
                    </tr> </table>';

        $totales = ' <table style="width: 120%; ">';

        if($valida_pais == '504'){
            $totales .= ' <tr>';
            $totales .= ' <b> <td style="font-size: 9px;font-family: Arial;">Descuentos o Rebajas <br> otorgados: <b>'.$moneda_emp_local_sm.'.</b></td> </b>';
            $totales .= ' <td style="font-size: 9px;font-family: Arial;" align="right">' . number_format($descuento_temp, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';

            $totales .= ' <tr>';
            $totales .= ' <b> <td style="font-size: 9px;font-family: Arial;">Importe exonerado: <b></b></td> </b>';
            $totales .= ' <td style="font-size: 9px;font-family: Arial;" align="right">' . number_format(0, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';

            $totales .= ' <tr>';
            $totales .= ' <b> <td style="font-size: 9px;font-family: Arial;">Importe exento: <b></b></td> </b>';
            $totales .= ' <td style="font-size: 9px;font-family: Arial;" align="right">' . number_format(0, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';

            $totales .= ' <tr>';
            $totales .= ' <b> <td style="font-size: 9px;font-family: Arial;">Importe gravado' . round($porcIva) . '%: '.$moneda_emp_local_sm.'.<b></b></td> </b>';
            $totales .= ' <td style="font-size: 9px;font-family: Arial;" align="right">' . number_format($fact_tot_fact - $fact_ice - $fact_iva, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';

            $totales .= ' <tr>';
            $totales .= ' <b> <td style="font-size: 9px;font-family: Arial;">Importe gravado 18%: '.$moneda_emp_local_sm.'.<b></b></td> </b>';
            $totales .= ' <td style="font-size: 9px;font-family: Arial;" align="right">' . number_format(0, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';
        }else{
            $totales .= ' <tr>';
            $totales .= ' <b> <td style="width: 150;font-size: 9px;font-family: Arial;">SUBTOTAL '.$moneda_emp_local_sm.' ' . round($porcIva) . '%: <b></b></td> </b>';
            $totales .= ' <td style="width: 50;font-size: 9px;font-family: Arial;" align="right">' . number_format($fact_tot_fact - $fact_ice - $fact_iva, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';

            $totales .= ' <tr>';
            $totales .= ' <b> <td style="width: 150;font-size: 9px;font-family: Arial;">SUBTOTAL '.$moneda_emp_local_sm.' 0%: <b></b></td> </b>';
            $totales .= ' <td style="width: 50;font-size: 9px;font-family: Arial;" align="right">' . number_format($fact_sin_miva, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';
        }

        if($valida_pais != '504'){
            $totales .= ' <tr>';
            $totales .= ' <b> <td style="width: 150;font-size: 9px;font-family: Arial;">DESCUENTO <b>'.$moneda_emp_local_sm.':</b></td> </b>';
            $totales .= ' <td style="width: 50;font-size: 9px;font-family: Arial;" align="right">' . number_format($descuento_temp, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';
        }
        
        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 9px;font-family: Arial;">'.$etiqueta_iva.' '.$porcentaje_iva.'%:</td> </b>';
        $totales .= ' <td style="width: 50;font-size: 9px;font-family: Arial;" align="right">' . number_format($fact_iva, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';

        if($valida_pais == '504'){
            $totales .= ' <tr>';
            $totales .= ' <b> <td style="font-size: 9px;font-family: Arial;">'.$etiqueta_iva.' 18%:</td> </b>';
            $totales .= ' <td style="font-size: 9px;font-family: Arial;" align="right">' . number_format(0, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';
        }

        if($empr_cod_pais == 1){
            $totales .= ' <tr>';
            $totales .= ' <b> <td style="width: 150;font-size: 9px;font-family: Arial;">ICE 15%:</td> </b>';
            $totales .= ' <td style="width: 50;font-size: 9px;font-family: Arial;" align="right">' . number_format($fact_ice, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';
        }

        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 9px;font-family: Arial;">TOTAL: <b>'.$moneda_emp_local_sm.'.</b></td> </b>';
        $totales .= ' <td style="width: 50;font-size: 9px;font-family: Arial;" align="right">' . number_format($fact_tot_fact, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';


        if($moneda_des_mone == "DOLAR"){
            $txt = "ES";
        }else{
            $txt = "S";
        }

        $V = new EnLetras();
        $con_letra = strtoupper($V->ValorEnLetrasMone($fact_tot_fact, $moneda_des_mone));
        
        $totales .= '</table>
        <table style="width: 100%;"> 
            <tr>
                <td style="font-size: 9px;font-family: Arial;" align="left"  width="20"><b>SON: </b></td>
                <td style="font-size: 9px;font-family: Arial;" align="left"  width="185">'.$con_letra.'</td>
            </tr>
            <tr>
            <br><td colspan="3" style="border-top: 1px solid black;"></td>
            </tr>
        </table>';

        $total = number_format($fact_con_miva + $fact_sin_miva + $fact_iva + $fact_ice + $fact_val_irbp + $fact_fle_fact + $fact_otr_fact + $fact_fin_fact - $totalDescuento, 2, '.', '');

        if($valida_pais == '504'){
            $totales .= ' <table style="width: 100%;" style="margin-left:12px;">';
            if($para_punt_emi == 'S'){

                $cod_estab_pto_emi = substr($fact_nse_fact, 0, 3);
                $cod_pto_emi = substr($fact_nse_fact, -6);
                
                if(empty($fact_tip_vent)){
                    $fact_tip_vent = 18;
                }

                $sqlPuntEmi = "SELECT emifa_auto_desde, emifa_auto_hasta, emifa_fec_fin, emifa_num_dig
                                from saeemifa 
                                where emifa_cod_estab = '$cod_estab_pto_emi' and
                                    emifa_cod_pto = '$cod_pto_emi' and emifa_tip_emifa = '$fact_tip_vent'";
                $emifa_auto_desde   = consulta_string($sqlPuntEmi, 'emifa_auto_desde', $oCon, 0);
                $emifa_auto_hasta   = consulta_string($sqlPuntEmi, 'emifa_auto_hasta', $oCon, 0);
                $emifa_fec_fin      = consulta_string($sqlPuntEmi, 'emifa_fec_fin', $oCon, 0);
                $emifa_num_dig      = consulta_string($sqlPuntEmi, 'emifa_num_dig', $oCon, 0);

                $emifa_auto_desde   = str_pad($emifa_auto_desde, $emifa_num_dig, "0", STR_PAD_LEFT);
                $rango_ini          = $cod_estab_pto_emi.'-'.$cod_pto_emi.'-'.$emifa_auto_desde;

                $emifa_auto_hasta   = str_pad($emifa_auto_hasta, $emifa_num_dig, "0", STR_PAD_LEFT);
                $rango_fin          = $cod_estab_pto_emi.'-'.$cod_pto_emi.'-'.$emifa_auto_hasta;
                

                $totales.='<tr>
                            <td style="font-size: 8px;font-family: Arial;" align="center"><b>CAI</b> - '.$fact_nau_fact.'</td> 
                            </tr>
                            <tr>
                            <b> <td style="font-size: 8px;font-family: Arial;" align="center">Rango Autorizado</td> </b>
                            </tr>
                            <tr>
                            <td style="font-size: 8px;font-family: Arial;" align="center">Del '.$rango_ini.' <br> A la '.$rango_fin.' </td> 
                            </tr>
                            <tr>
                            <td style="font-size: 8px;font-family: Arial;" align="center"><b>Fecha Limite de Emision:</b> '.$emifa_fec_fin.'</td> 
                            </tr>';
            }else{
                $totales.='<tr>
                            <td style="font-size: 8px;font-family: Arial;" align="center"><b>CAI</b> - '.$aufa_nau_fact.'</td> 
                            </tr>
                            <tr>
                            <b> <td style="font-size: 8px;font-family: Arial;" align="center">Rango Autorizado</td> </b>
                            </tr>
                            <tr>
                            <td style="font-size: 8px;font-family: Arial;" align="center">Del '.$aufa_nse_fact.'-'.$aufa_fac_inic.' <br> A la '.$aufa_nse_fact.'-'.$aufa_fac_fina.' </td> 
                            </tr>
                            <tr>
                            <td style="font-size: 8px;font-family: Arial;" align="center"><b>Fecha Limite de Emision:</b> '.$aufa_ffi_fact.'</td> 
                            </tr>';
            }
    
            $totales.='<tr>
                            <td colspan="3" style="border-top: 1px solid black;"></td>
                        </tr>
                        </table>';
            $totales .= ' <table style="width: 100%;" style="margin-left:12px;">
                        <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left">* No. Orden de Compra Exenta:</td> 
                        </tr>
                        <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left">* No. Cons. Del Registro de Exonerados: </td> 
                        </tr>
                        <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left">* No. De Registro SAG:</td> 
                        </tr>
                        <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left"></td> 
                        </tr>
                        <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="center">Original: Cliente, <br> Copia: Obligado Tributario Emisor.</td> 
                        </tr>
                        <tr>
                            <td colspan="3" style="border-top: 1px solid black;"></td>
                        </tr>
                        </table>';
        }
        
        //query forma de pago

        $sqlFPago = "SELECT fx.fxfp_val_fxfp,   fx.fxfp_num_dias,   fp.fpag_des_fpag,   f.fact_val_tcam,    fx.fxfp_val_ext,    fm.mone_sgl_mone, f.fact_cm5_fac
                        FROM saefact f, saefxfp fx, saefpag fp, saemone fm
                        WHERE f.fact_cod_fact = fx.fxfp_cod_fact AND 
                                fp.fpag_cod_fpag = fx.fxfp_cod_fpag AND 
                                fm.mone_cod_mone = fp.fpag_cod_mone AND 
                                --f.fact_cod_empr = $idEmpresa AND 
                                --f.fact_cod_sucu = $idSucursal AND 
                                f.fact_cod_fact = $id ";

        if ($oIfx->Query($sqlFPago)) {
            if ($oIfx->NumFilas() > 0) {
                $tablePago = '<table style="width: 100%;">';
                $tablePago .= '<tr >';
                $tablePago .= '<b><td style="width: 130; padding: 0px;font-family: Arial; font-size: 9px;">FORMA PAGO</td></b>';
                $tablePago .= '<b><td style="width: 80; padding: 0px;font-family: Arial; font-size: 9px;">VALOR</td></b>';
                $tablePago .= '</tr>';
                do {
                    $fpag_des_fpag     = $oIfx->f('fpag_des_fpag');
                    $fxfp_val_fxfp      = $oIfx->f('fxfp_val_fxfp');
                    $fxfp_val_ext      = $oIfx->f('fxfp_val_ext');
                    $mone_sgl_mone      = $oIfx->f('mone_sgl_mone');
                    $fxfp_num_dias      = $oIfx->f('fxfp_num_dias');
                    $fpagop_des_fpagop  = $oIfx->f('fpagop_des_fpagop');
                    $fxfp_val_ext = number_format($fxfp_val_ext,2);
                    $fact_cm5_fac = number_format($fact_cm5_fac,2);

                    if ($mone_sgl_mone == 'USD') {
                        $tablePago .= '<tr>';
                        $tablePago .= '<td style="width: 130; font-family: Arial; font-size: 9px;">' . htmlentities($fpag_des_fpag) . '<small><b>('.$fxfp_val_ext.')</b></small></td>';
                        $tablePago .= '<td style="width: 80; font-family: Arial; font-size: 9px;">'. number_format($fxfp_val_fxfp, 2, '.', '') . '</td>';
                        $tablePago .= '</tr>';
                    }else{
                        $tablePago .= '<tr>';
                        $tablePago .= '<td style="width: 130; font-family: Arial; font-size: 9px;">' . htmlentities($fpag_des_fpag) . '</td>';
                        $tablePago .= '<td style="width: 80;  font-family: Arial; font-size: 9px;">' . number_format($fxfp_val_fxfp, 2, '.', '') . '</td>';
                        $tablePago .= '</tr>';
                    }
                } while ($oIfx->SiguienteRegistro());

                $tablePago .= '</table>';
            }
        }
        $oIfx->Free();

        $Contratos = new Contratos($oCon, $oIfx, $idempresa, $fact_cod_sucu, $fact_cod_clpv, $fact_cod_contr);
        $deuda = $Contratos->consultaMontoMesAdeuda();

        $html .= '<div style="width: 80mm; margin-top: 2px;" >'; //div padre

        $html .= '<div style="width: 80mm; margin-left:30px;" >'; //div 2
        
        $html .= '<table align="left">
                    <tr>
                        <td style="margin-top: 0px;" align="center" width="200">
                        '.$logo_empresa.'
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size: 9px; font-family: Arial;" width="200">' . $razonSocial . '</td>
                    </tr>

                    <tr>
                        <td align="center" style="font-size: 9px;font-family: Arial; " width="200">
                            <b>'.$tip_ruc_pais.' N°:</b> ' . $ruc_empr . '
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size: 9px; font-family: Arial;" width="200">
                            ' . $empr_num_dire . '
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 9px;font-family: Arial; text-align: center;" width="200">
                            '.$dirMatriz.'
                        </td>
                    </tr>';
            if($valida_pais != '504'){
                $html.='<tr><td align="center" style="font-size: 9px; font-family: Arial; " width="200"><b>SUCURSAL:</b> ' . $sucu_nom_sucu . '</td>
                        </tr>';
            }
                    if(strlen($empr_tel_resp)>0){
                        $html.='<tr>
                                    <td align="center" style="font-size: 9px; font-family: Arial; " width="200">
                                        <b>TELEFONOS:</b> ' . $empr_tel_resp . '
                                    </td>
                                </tr>';
                    }

                    if(strlen($empr_mai_empr)>0){
                        $html.='<tr>
                                    <td align="center" style="font-size: 9px; font-family: Arial; " width="200">
                                        <b>EMAIL:</b><br>' . $empr_mai_empr . '
                                    </td>
                                </tr>';
                    }

                    if($valida_pais == '504'){
                        $html.='<tr><td align="center" style="font-size: 9px; font-family: Arial; " width="200">
                                    <b>WHATSAPP:</b> ' . $empr_fax_empr . '
                                    </td>
                                </tr>
                                <tr><td align="center" style="font-size: 9px; font-family: Arial; " width="200">
                                    <b>FACEBOOK:</b> ' . $empr_ema_comp . '
                                    </td>
                                </tr>
                                ';
                    }

            $cadena_buscada   = 'TK';
            $posicion_coincidencia = strpos($fact_nse_fact, $cadena_buscada);

            if (!empty($posicion_coincidencia)) {
                $titulo="TICKET - ELECTRONICO";                
            }else{
                $titulo="BOLETA - ELECTRONICA";
                }
        
                    
            /*$html.='<tr>
                        <td style="font-size: 9px; font-family: Arial;" colspan="2"  align="center" width="200"><b>'.$titulo.'</b></td>
                    </tr>';*/
                    if($para_punt_emi == 'S' && $valida_pais == '504'){
                        $num_serie = $fact_nse_fact . ' - ' . $fact_num_preimp;
                        $num_serie = preg_replace('/(\d{3})(\d{3})-(\d{2}) - (\d+)/', '$1-$2-$3-$4', $num_serie);

                        $html.='<tr>
                                    <td style="font-size: 12px; font-family: Arial; width:40mm;" align="center" colspan="2" width="200">' . $num_serie . '</td>
                                </tr>';
                    }/*else{
                        $html.='<tr>
                                    <td style="font-size: 12px; font-family: Arial; width:40mm;" align="center" colspan="2" width="200">' . $fact_nse_fact . ' - ' . $fact_num_preimp . '</td>
                                </tr>';
                    }
                
            $html.='<tr>
                        <td style="font-size: 9px; font-family: Arial;" align="center" colspan="2" width="200">
                            <b>Fecha y Hora de Emisión: </b>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="center" colspan="2" width="200">
                            ' . $fact_fech_fact . ' - '.$fact_hor_fin.'
                        </td>
                    </tr>
                </table>';*/
                $html.='</table>';

        $pos      = strripos($fact_nom_cliente, ',');
        if( $pos > 0 ){
            list($a1,$b1,$c1,$d1,$e1,$f1,$g1, $h1, $i1, $j1, $k1, $l1, $m1, $n1 ) = explode(',', $fact_nom_cliente);
            list($a111, $b111, $c111, $d111) = explode(' ', $fact_nom_cliente);
            if( !empty($d111) ){
                list($a1,$b1,$c1,$d1,$e1,$f1, $g1, $h1, $i1, $j1, $k1, $l1, $l1, $m1, $n1) = explode(' ', $fact_nom_cliente);
            }

        }else{
            list($a1,$b1,$c1,$d1,$e1,$f1,$g1, $h1, $i1, $j1, $k1, $l1, $m1, $n1) = explode(' ', $fact_nom_cliente);
        }


        $fact_fech_fact=date('d-m-Y',strtotime($fact_fech_fact));

        $html .= '
        
         <table align="left" >
                    <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="120"><b>'.$titulo.'</b></td>
                        <td style="font-size: 9px;font-family: Arial;" align="right" width="90">' . $fact_nse_fact . ' - ' . $fact_num_preimp . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="120"><b>FECHA DE EMISION:</b> '.$fact_fech_fact.'</td>
                        <td style="font-size: 9px;font-family: Arial;" align="right" width="90"><b>HORA:</b> '.$fact_hor_fin.'</td>
                    </tr>
                </table>

                <table align="left" >
                    <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="60"><b>CONTRATO:</b></td>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="145">'.$codigo_clpv.'</td>
                    </tr>
                    <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="60"><b>CLIENTE:</b></td>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="145">'.$fact_nom_cliente.'</td>
                    </tr>
                    
                    <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="60"><b>'.$tip_iden_cliente.':</b></td>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="145">'.$fact_ruc_clie.'</td>
                    </tr>
                    <tr>
                    <td style="font-size: 9px;font-family: Arial;" align="left" width="60"><b>DIRECCION:</b></td>
                    <td style="font-size: 9px;font-family: Arial;" align="left" width="145">'.$fact_dir_clie.'</td>
                    </tr>
                    <tr>
                    <td style="font-size: 9px;font-family: Arial;" align="left" width="60"><b>MONEDA:</b></td>
                    <td style="font-size: 9px;font-family: Arial;" align="left" width="145">'.$moneda_des_mone.'</td>
                    </tr>
                    <tr>
                    <td style="font-size: 9px;font-family: Arial;" align="left" width="60"><b>OBSERVACION:</b></td>
                    <td style="font-size: 9px;font-family: Arial;" align="left" width="145">'.$observacion.'</td>
                    </tr>
                </table>';

        $html .= $deta;
        $html .= $totales;
        $html .= $tablePago;

        $html .=    '<table align="left">
                        <tr>
                            <td style="font-size: 9px;font-family: Arial;" width="130"  align="left">
                            <b>SALDO:</b>
                            </td>
                            <td style="font-size: 9px;" width="80" >' . number_format($deuda, 2, '.', ',') . '</td>
                        </tr>';

                        if($valida_pais == '504'){
                        $html .= '<tr>
                                    <td style="font-size: 10px;font-family: Arial;" align="center" colspan="2">
                                        <b>CAJA EMPRESARIAL BANCO OCCIDENTE <br> CON CODIGO DE CLIENTE</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px;font-family: Arial;" align="center" colspan="2">
                                        <b>PARA DEPOSITO ENVIAR BAUCHER DE <br> PAGO POR WHATSAPP - <br>'.$empr_fax_empr.' <br> OCCIDENTE: # 11-201-013934-6</b>
                                    </td>
                                </tr>';
                        }
        $html .=    '<tr>
                            <td style="font-size: 9px;font-family: Arial;" align="center" colspan="2">
                                <b>https://www.sisconti.com</b>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 9px;font-family: Arial;" align="center" colspan="2">
                                '.$empr_cm2_empr.'
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 9px;font-family: Arial;" align="center" colspan="2">
                                GRACIAS POR SU PAGO!
                            </td>
                        </tr>
                        </table>
                </div> 
                </div>'; //fin div padre


    } else {
        $table = '<div>No existe Factura...</div>';
    }

    //arma pdf

    $table = '<page>';
    $table.= $html;
    $table.= '</page>';

    $html2pdf = new HTML2PDF('P', 'B5', 'es', true, 'UTF-8', array(0,0,0,0));
    $html2pdf->WriteHTML($table);
    $html2pdf->Output('recibo_template.pdf', '');
    $ruta = DIR_FACTELEC . 'Include/archivos/' . $id . '.pdf';
    $html2pdf->Output($ruta, 'F');

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

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();

    $idEmpresa      = $_SESSION['U_EMPRESA'];
    $idSucursal     = $_SESSION['U_SUCURSAL'];
    $mone_sigla     = $_SESSION['U_MONE_SIGLA'];
    $valida_pais    = $_SESSION['S_PAIS_API_SRI'];

    $sqlMonedaEm = "SELECT pcon_mon_base from saepcon where pcon_cod_empr = $idEmpresa";
    $moneda_emp = consulta_string($sqlMonedaEm, 'pcon_mon_base', $oIfx, 0);

    $sqlPuntEmi = "SELECT para_punt_emi from saepara where para_cod_sucu = $idSucursal";
    $para_punt_emi = consulta_string($sqlPuntEmi, 'para_punt_emi', $oIfx, 0);

    $moneda_emp_ = "SELECT * from saemone where mone_cod_mone = $moneda_emp  ";
    $moneda_emp_local = consulta_string($moneda_emp_, 'mone_sgl_mone', $oIfx, '');
    $moneda_des_mone = consulta_string($moneda_emp_, 'mone_des_mone', $oIfx, '');
    $moneda_emp_local_sm = consulta_string($moneda_emp_, 'mone_smb_mene', $oIfx, '');

    $sqlMonedaEm = "SELECT aufa_nau_fact, aufa_fac_inic, aufa_fac_fina, aufa_nse_fact, aufa_ffi_fact  from saeaufa where aufa_cod_empr = $idEmpresa AND aufa_cod_sucu = $idSucursal";
    $aufa_nau_fact = consulta_string($sqlMonedaEm, 'aufa_nau_fact', $oIfx, 0);
    $aufa_fac_inic = consulta_string($sqlMonedaEm, 'aufa_fac_inic', $oIfx, 0);
    $aufa_fac_fina = consulta_string($sqlMonedaEm, 'aufa_fac_fina', $oIfx, 0);
    $aufa_nse_fact = consulta_string($sqlMonedaEm, 'aufa_nse_fact', $oIfx, 0);
    $aufa_ffi_fact = consulta_string($sqlMonedaEm, 'aufa_ffi_fact', $oIfx, 0);
    // UNIDAD
    $sql = "select unid_cod_unid, unid_nom_unid from saeunid where unid_cod_empr = $idEmpresa ";
    unset($array_unid);
    $array_unid = array_dato($oIfx, $sql, 'unid_cod_unid', 'unid_nom_unid');

    $sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_cod_pais,
                empr_num_resu, empr_path_logo, empr_iva_empr , empr_num_dire, empr_fax_empr,
                empr_sitio_web, empr_cm1_empr, empr_cm2_empr , empr_prec_sucu, empr_tel_resp, empr_mai_empr, empr_ema_comp
                from saeempr where empr_cod_empr = $idEmpresa ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_path_logo');
            if ($oIfx->f('empr_conta_sn') == 'S')
                $empr_conta_sn = 'SI';
            else
                $empr_conta_sn = 'NO';

            $empr_num_resu = $oIfx->f('empr_num_resu');
            $empr_iva_empr = $oIfx->f('empr_iva_empr');
            $empr_num_dire = $oIfx->f('empr_num_dire');
            $empr_fax_empr = $oIfx->f('empr_fax_empr');
            $empr_sitio_web= $oIfx->f('empr_sitio_web');
            $empr_cm1_empr = $oIfx->f('empr_cm1_empr');
            $empr_cm2_empr = $oIfx->f('empr_cm2_empr');
            $empr_prec_sucu = $oIfx->f('empr_prec_sucu');
            $empr_tel_resp = $oIfx->f('empr_tel_resp');
            $empr_cod_pais = $oIfx->f('empr_cod_pais');
            $empr_mai_empr = $oIfx->f('empr_mai_empr');
            $empr_ema_comp = $oIfx->f('empr_ema_comp');
        }
    }
    $oIfx->Free();

    //selecciona sucursales y direcciones
    $sql_sucu = "select sucu_nom_sucu, sucu_dir_sucu from saesucu where sucu_cod_empr = $idEmpresa and sucu_cod_sucu = $idSucursal ";
    if ($oIfx->Query($sql_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
                $sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $sql_sucu = "SELECT etiqueta, porcentaje from comercial.pais_etiq_imp where pais_cod_pais = $empr_cod_pais and impuesto = 'IVA' ";
    if ($oIfx->Query($sql_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $etiqueta_iva = $oIfx->f('etiqueta');
                $porcentaje_iva = $oIfx->f('porcentaje');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    //selecciona sucursales y direcciones
    $sql_sucu = "SELECT identificacion from comercial.tipo_iden_clpv_pais where pais_cod_pais = $empr_cod_pais and id_iden_clpv = 1";
    if ($oIfx->Query($sql_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $tip_ruc_pais = $oIfx->f('identificacion');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $sql = "select fact_cod_fact, fact_tip_vent, fact_cm4_fact,fact_cm1_fact from saefact where
                    fact_cod_empr = $idEmpresa and
                    fact_cod_fact = $id and 
                    fact_cod_clpv = $cliente";
    $contador = consulta_string($sql, 'fact_cod_fact', $oIfx, 0);
    $comprobante = consulta_string($sql, 'fact_tip_vent', $oIfx, 0);
    $descuento_temp = consulta_string($sql, 'fact_cm4_fact', $oIfx, 0);
    $observacion = consulta_string($sql, 'fact_cm1_fact', $oIfx, '');


    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;


    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

    if (file_exists($path_logo_img)) {
        $logo_empresa=' <img width="170px;"  src="' . $path_logo_img . '">';
    }
    else{
        $logo_empresa='<div style="color:red;">LOGO NO CARGADO</div>';
    }



    if ($contador > 0) {
        // TOTAL FACTURA
        $sqlFPago = "select sum(fx.fxfp_val_fxfp) as total
                            from saefact f, saefxfp fx, saefpag fp
                            where f.fact_cod_fact = fx.fxfp_cod_fact and
                            fp.fpag_cod_fpag = fx.fxfp_cod_fpag and
                            f.fact_cod_empr = $idEmpresa and
                            f.fact_cod_sucu = $idSucursal and
                            f.fact_cod_fact = $id ";
        $tot_fxfp = consulta_string($sqlFPago, 'total', $oIfx, 0);

        //print_r($sqlFPago);exit;

        $sqlFac = "select * from saefact where fact_cod_fact = $id  ";

        if ($oIfx->Query($sqlFac)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $fact_nse_fact      = $oIfx->f('fact_nse_fact');
                    $fact_num_preimp    = $oIfx->f('fact_num_preimp');
                    $fact_auto_sri      = $oIfx->f('fact_auto_sri');
                    $fact_fech_sri      = $oIfx->f('fact_fech_sri');
                    $fact_nom_cliente   = $oIfx->f('fact_nom_cliente');
                    $fact_fech_fact     = fecha_mysql_func($oIfx->f('fact_fech_fact'));
                    $fact_ruc_clie      = $oIfx->f('fact_ruc_clie');
                    $fact_tlf_cliente   = $oIfx->f('fact_tlf_cliente');
                    $fact_dir_clie      = htmlentities($oIfx->f('fact_dir_clie'));
                    $fact_email_clpv    = str_replace(' ', '', $oIfx->f("fact_email_clpv"));
                    $fact_con_miva      = $oIfx->f('fact_con_miva');
                    $fact_sin_miva      = $oIfx->f('fact_sin_miva');
                    $fact_tot_fact      = $oIfx->f('fact_tot_fact');
                    $fact_iva           = $oIfx->f('fact_iva');
                    $fact_ice           = $oIfx->f('fact_ice');
                    $fact_val_irbp      = $oIfx->f('fact_val_irbp');
                    $fact_clav_sri      = $oIfx->f('fact_clav_sri');
                    $fact_dsg_valo      = $oIfx->f('fact_dsg_valo');
                    $fact_cm2_fact      = $oIfx->f("fact_cm2_fact");
                    $fact_cm4_fact      = $oIfx->f("fact_cm4_fact");
                    $fact_cod_clpv      = $oIfx->f("fact_cod_clpv");
                    $fact_dia_plazo     = $oIfx->f("fact_dia_fact");
                    $fact_tip_vent      = $oIfx->f("fact_tip_vent");
                    $fact_fech_venc     = fecha_mysql_func($oIfx->f("fact_fech_venc"));
                    $fact_cod_contr     = $oIfx->f("fact_cod_contr");
                    $fact_user_web      = $oIfx->f("fact_user_web");
                    $fact_nau_fact      = $oIfx->f("fact_nau_fact");
                    $fact_serie_fiscal  = $oIfx->f("fact_serie_fiscal");
                    $fact_num_fiscal    = $oIfx->f("fact_num_fiscal");
                    $fact_hor_fin       = $oIfx->f("fact_hor_fin");
                    $fact_cod_mone      = $oIfx->f("fact_cod_mone");
                    $fact_cm7_fac       = $oIfx->f("fact_cm7_fac");

                    $sql = "select tcmp_des_tcmp from saetcmp where tcmp_cod_tcmp = '$fact_tip_vent'";
                    $tcmp_des_tcmp = 'BOLETA ELECTRONICA';//;consulta_string_func($sql, 'tcmp_des_tcmp', $oIfxA, '');

                    if($fact_dia_plazo==0){
                        $sql            = "select fxfp_num_dias, fxfp_fec_fin from saefxfp where fxfp_cod_fact = $id and fxfp_cod_empr = $idEmpresa and fxfp_cod_sucu = $idSucursal ";
                        $fact_dia_plazo = consulta_string_func($sql, 'fxfp_num_dias', $oIfxA, 0);
                        $fact_fech_venc = fecha_mysql_func(consulta_string_func($sql, 'fxfp_fec_fin',  $oIfxA, ''));

                    }

                    $fact_dir_clie = $fact_dir_clie;

                    $logo .= '<b><td style="width: 40%; border: 1px solid black; border-radius: 5px;" align="center">';
                    $logo .= ' <table align="center" style="font-size: 15px;">';
                    $logo .= ' <tr>';
                    $logo .= '<td style="border-bottom: 1px inset black;">'.$tcmp_des_tcmp.'</td>';
                    $logo .= ' </tr>';
                    $logo .= ' <tr>';
                    $logo .= '<td>SERIE:</td>';
                    $logo .= ' </tr>';
                    $logo .= ' <tr>';
                    $logo .= '<td>' . substr($fact_nse_fact, 0, 3) . '-' . substr($fact_nse_fact, 3, 6) . '</td>';
                    $logo .= ' </tr>';
                    $logo .= ' <tr>';
                    $logo .= '<td style="font-size: 17px; color: red; border-bottom: 1;">' . $fact_num_preimp . '</td>';
                    $logo .= ' </tr>';
                    $logo .= ' <tr>';
                    $logo .= '<td>FECHA :</td>';
                    $logo .= ' </tr>';
                    $logo .= ' <tr>';
                    $logo .= '<td style="border-bottom:1">' . $fact_fech_fact . '</td>';
                    $logo .= ' </tr>';

                    $logo .= ' </table>';
                    $logo .= '</td></b>';
                    $logo .= '</tr>';
                    $logo .= '</table>';
                    $logo .= ' <br>';

                    if(!empty($fact_cod_contr)){
                        $sql = "SELECT id_sector, id_barrio, ruta, id_clpv, codigo FROM isp.contrato_clpv WHERE id = $fact_cod_contr";
                        if($oCon->Query($sql)){
                            if($oCon->NumFilas() > 0){
                                $id_sector  = $oCon->f('id_sector');
                                $id_barrio  = $oCon->f('id_barrio');
                                $ruta       = $oCon->f('ruta');
                                $id_clpv    = $oCon->f('id_clpv');
                                $codigo_clpv    = $oCon->f('codigo');
                            }
                        }
                        $oCon->Free();

                        $sql = "SELECT clv_con_clpv from saeclpv where clpv_cod_clpv = $id_clpv";
                        $clv_con_clpv = consulta_string_func($sql, 'clv_con_clpv', $oCon, '');

                        if(strlen($clv_con_clpv)==0){
                            $clv_con_clpv = 2;
                        }
                        
                        //TIPO DE IDENTIFICACION DEL CLIENTE
                        $sql_sucu = "SELECT identificacion from comercial.tipo_iden_clpv_pais where pais_cod_pais = $empr_cod_pais and id_iden_clpv = '$clv_con_clpv'";
                        if ($oIfx->Query($sql_sucu)) {
                            if ($oIfx->NumFilas() > 0) {
                                do {
                                    $tip_iden_cliente = $oIfx->f('identificacion');
                                } while ($oIfx->SiguienteRegistro());
                            }
                        }
                        $oIfx->Free();


                        if(!empty($id_sector)){ //sector
                            $sql = "SELECT sector FROM comercial.sector_direccion WHERE id = $id_sector";
                            $sector = consulta_string_func($sql, 'sector', $oCon, '');
                        }

                        if(!empty($id_barrio)){ //sector
                            $sql = "SELECT barrio FROM isp.int_barrio WHERE id = $id_barrio";
                            $barrio = consulta_string_func($sql, 'barrio', $oCon, '');
                        }

                    }

                    $cliente = ' <table style="width: 100%; border:1px solid black; border-radius: 5px; padding: 2px; font-size: 15x;">
                                    <tr>
                                    <b><td style="width: 10%;">CLIENTE:</td></b>
                                    <td style="width: 48%; ">' . htmlentities($fact_nom_cliente) . '</td>
                                    <b><td style="width: 13%; ">FECHA EMISION:</td></b>
                                    <td style="width: 29% ">' . $fact_fech_fact . '</td>
                                    </tr>
                                    <tr>
                                    <b><td style="width: 10% ">NIT.:</td></b>
                                    <td style="width: 48% ">' . $fact_ruc_clie . '</td>
                                    <b><td style="width: 13% ">TELEFONO:</td></b>
                                    <td style="width: 29% ">' . $fact_tlf_cliente . '</td>
                                    </tr>
                                    <tr>
                                    <b><td style="width: 10% ">DIRECCION:</td></b>
                                    <td style="width: 48% ">' . htmlentities($fact_dir_clie) . '</td>
                                    <b><td style="width: 13% ">EMAIL:</td></b>
                                    <td style="width: 29% ">' . $fact_email_clpv . '</td>
                                    </tr>
                                    <tr>
                                    <b><td style="width: 10% ">SECTOR:</td></b>
                                    <td style="width: 48% ">' . htmlentities($sector) . ' '.htmlentities($barrio).'</td>
                                    <b><td style="width: 13% ">CONTRATO:</td></b>
                                    <td style="width: 29% ">' . $fact_email_clpv . '</td>
                                    </tr>
                                </table>
                    <br>';
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $sql_sucu = "SELECT usuario_nombre, usuario_apellido from comercial.usuario where usuario_id = $fact_user_web";
        if ($oIfx->Query($sql_sucu)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $usuario_nombre = $oIfx->f('usuario_nombre');
                    $usuario_apellido = $oIfx->f('usuario_apellido');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $nombre_cajero = $usuario_nombre." ".$usuario_apellido;

        $sqlDeta = "SELECT * from saedfac where dfac_cod_fact = $id and 
                        dfac_cod_empr = $idEmpresa 
                        order by dfac_cod_mes, dfac_precio_dfac desc ";

        $sqlDeta1 = "SELECT sum(dfac_precio_dfac) as dfac_precio_dfac, 
                            sum(dfac_mont_total) as dfac_mont_total,
                            dfac_det_dfac,  dfac_cod_mes , dfac_por_iva from saedfac where
                            dfac_cod_fact = $id and 
                            dfac_cod_empr = $idEmpresa
                            group by dfac_det_dfac,  dfac_cod_mes , dfac_por_iva order by dfac_cod_mes ";
        $tot_det = 0;
        if ($oIfx->Query($sqlDeta)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $dfac_precio_dfac   = $oIfx->f('dfac_precio_dfac');
                    $dfac_por_iva       = $oIfx->f('dfac_por_iva');

                    if ($dfac_por_iva > 0) {
                        $porcIva = $dfac_por_iva;
                    }

                    if($dfac_por_iva>0){
                        $dfac_precio_dfac = round(($dfac_precio_dfac + ($dfac_precio_dfac * $dfac_por_iva/100 )),0);
                    }

                    $tot_det += round($dfac_precio_dfac,0);
                }while ($oIfx->SiguienteRegistro());
            }
        }

        $dif = round(($tot_fxfp - $tot_det),2);

        $deta = ' <table style="width: 100%;">
                    <tr>
                        <td colspan="3" style="border-top: 1px solid black;"  width="200"></td>
                    </tr>';
        $deta .= ' <tr>';
        $deta .= ' <b> <td style="width: 20; font-size: 9px;font-family: Arial;" align="center">CANT</td> </b>';
        $deta .= ' <b> <td style="width: 130; font-size: 9px;font-family: Arial;" align="center">DESCRIPCION</td> </b>';
        $deta .= ' <b> <td style="width: 50; font-size: 9px;font-family: Arial;" align="center">IMPORTE</td> </b>';
        $deta .= ' </tr>
                    <tr>
                        <td colspan="3" style="border-top: 1px solid black;"></td>
                    </tr>';

        $desc_monto = 0;
        $monto_precio = 0;
        $rd = 1;
        if ($oIfx->Query($sqlDeta)) {
            if ($oIfx->NumFilas() > 0) {
                $porcIva = '';
                $totalDescuento = 0;
                do {
                    $dfac_cod_prod      = $oIfx->f('dfac_cod_prod');
                    $dfac_nom_prod      = $oIfx->f('dfac_nom_prod');
                    $dfac_cant_dfac     = $oIfx->f('dfac_cant_dfac');
                    $dfac_precio_dfac   = $oIfx->f('dfac_precio_dfac');
                    $dfac_des1_dfac     = $oIfx->f('dfac_des1_dfac');
                    $dfac_des2_dfac     = $oIfx->f('dfac_des2_dfac');
                    $dfac_por_dsg       = $oIfx->f('dfac_por_dsg');
                    $dfac_mont_total    = $oIfx->f('dfac_mont_total');
                    $dfac_lote          = $oIfx->f('dfac_cod_lote');
                    $dfac_num_comp      = $oIfx->f('dfac_tip_dfac');
                    $dfac_por_iva       = $oIfx->f('dfac_por_iva');
                    $dfac_lote_fcad     = $oIfx->f('dfac_lote_fcad');
                    $dfac_cod_unid      = $array_unid[$oIfx->f('dfac_cod_unid')];
                    $dfac_cod_mes       = $oIfx->f('dfac_cod_mes');
                    $dfac_det_dfac      = $oIfx->f('dfac_det_dfac');

                    if ($dfac_por_iva > 0) {
                        $porcIva = $dfac_por_iva;
                    }

                    $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;
                    if ($descuento > 0)
                        $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                    else
                        $descuento = 0;

                    $totalDescuento = $totalDescuento + $descuento;

                    if($dfac_por_iva>0){
                        $dfac_precio_dfac = round(($dfac_precio_dfac + ($dfac_precio_dfac * $dfac_por_iva/100 )),2);
                    }


                    $mes_pago = '';
                    if( !empty($dfac_cod_mes) ){
                        $sql = "SELECT c.mes, c.anio FROM  isp.contrato_pago c WHERE
                                            c.id IN ( $dfac_cod_mes  )  ";
                        $mes_pago = 'MENSUAL: ';
                        if ($oCon->Query($sql)) {
                            if ($oCon->NumFilas() > 0) {
                                do {
                                    $mes_pago = Mes_func($oCon->f('mes')) .'/'.$oCon->f('anio');
                                } while ($oCon->SiguienteRegistro());
                            }
                        }
                        $oCon->Free();
                    }

                    $l =  strlen($dfac_det_dfac);
                    if(empty($dfac_det_dfac) || $l>100){
                        //PRODUCTO
                        $sqlDescripcionProd = "select prod_nom_prod, prod_cod_barra, prod_sn_noi, prod_sn_exe ,prod_cod_tpro  from saeprod where 
                                                    prod_cod_prod = '$dfac_cod_prod' and 
                                                    prod_cod_empr = $idEmpresa and 
                                                    prod_cod_sucu = $idSucursal ";
                        $prod_nom_prod = consulta_string_func($sqlDescripcionProd, 'prod_nom_prod', $oIfxA, 0);
                        $dfac_det_dfac = $prod_nom_prod. ' '.$mes_pago ;
                    }

                    $deta .= ' <tr>';
                    $deta .= ' <td style="width: 20;  font-family: Arial; font-size: 9px;" align="right">' . round($dfac_cant_dfac) . '</td>';
                    $deta .= ' <td style="width: 130; font-family: Arial; font-size: 9px;">' . $dfac_det_dfac . '</td>';
                    $deta .= ' <td style="width: 50;  font-family: Arial; font-size: 9px;" align="right">' . number_format($dfac_mont_total, 2, '.', ',') . '</td>';
                    $deta .= ' </tr>';

                    $monto_precio += round($dfac_precio_dfac*$dfac_cant_dfac,2);
                    $rd++;
                }while ($oIfx->SiguienteRegistro());
            }
        }

        $deta .= '<tr>
                        <td colspan="3" style="border-top: 1px solid black;"></td>
                    </tr> </table>';

        $totales = ' <table style="width: 120%; ">';

        if($valida_pais == '504'){
            $totales .= ' <tr>';
            $totales .= ' <b> <td style="font-size: 9px;font-family: Arial;">Descuentos o Rebajas <br> otorgados: <b>'.$moneda_emp_local_sm.'.</b></td> </b>';
            $totales .= ' <td style="font-size: 9px;font-family: Arial;" align="right">' . number_format($descuento_temp, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';

            $totales .= ' <tr>';
            $totales .= ' <b> <td style="font-size: 9px;font-family: Arial;">Importe exonerado: <b></b></td> </b>';
            $totales .= ' <td style="font-size: 9px;font-family: Arial;" align="right">' . number_format(0, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';

            $totales .= ' <tr>';
            $totales .= ' <b> <td style="font-size: 9px;font-family: Arial;">Importe exento: <b></b></td> </b>';
            $totales .= ' <td style="font-size: 9px;font-family: Arial;" align="right">' . number_format(0, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';

            $totales .= ' <tr>';
            $totales .= ' <b> <td style="font-size: 9px;font-family: Arial;">Importe gravado' . round($porcIva) . '%: '.$moneda_emp_local_sm.'.<b></b></td> </b>';
            $totales .= ' <td style="font-size: 9px;font-family: Arial;" align="right">' . number_format($fact_tot_fact - $fact_ice - $fact_iva, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';

            $totales .= ' <tr>';
            $totales .= ' <b> <td style="font-size: 9px;font-family: Arial;">Importe gravado 18%: '.$moneda_emp_local_sm.'.<b></b></td> </b>';
            $totales .= ' <td style="font-size: 9px;font-family: Arial;" align="right">' . number_format(0, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';
        }else{
            $totales .= ' <tr>';
            $totales .= ' <b> <td style="width: 150;font-size: 9px;font-family: Arial;">SUBTOTAL '.$moneda_emp_local_sm.' ' . round($porcIva) . '%: <b></b></td> </b>';
            $totales .= ' <td style="width: 50;font-size: 9px;font-family: Arial;" align="right">' . number_format($fact_tot_fact - $fact_ice - $fact_iva, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';

            $totales .= ' <tr>';
            $totales .= ' <b> <td style="width: 150;font-size: 9px;font-family: Arial;">SUBTOTAL '.$moneda_emp_local_sm.' 0%: <b></b></td> </b>';
            $totales .= ' <td style="width: 50;font-size: 9px;font-family: Arial;" align="right">' . number_format($fact_sin_miva, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';
        }

        if($valida_pais != '504'){
            $totales .= ' <tr>';
            $totales .= ' <b> <td style="width: 150;font-size: 9px;font-family: Arial;">DESCUENTO <b>'.$moneda_emp_local_sm.':</b></td> </b>';
            $totales .= ' <td style="width: 50;font-size: 9px;font-family: Arial;" align="right">' . number_format($descuento_temp, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';
        }
        
        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 9px;font-family: Arial;">'.$etiqueta_iva.' '.$porcentaje_iva.'%:</td> </b>';
        $totales .= ' <td style="width: 50;font-size: 9px;font-family: Arial;" align="right">' . number_format($fact_iva, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';

        if($valida_pais == '504'){
            $totales .= ' <tr>';
            $totales .= ' <b> <td style="font-size: 9px;font-family: Arial;">'.$etiqueta_iva.' 18%:</td> </b>';
            $totales .= ' <td style="font-size: 9px;font-family: Arial;" align="right">' . number_format(0, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';
        }

        if($empr_cod_pais == 1){
            $totales .= ' <tr>';
            $totales .= ' <b> <td style="width: 150;font-size: 9px;font-family: Arial;">ICE 15%:</td> </b>';
            $totales .= ' <td style="width: 50;font-size: 9px;font-family: Arial;" align="right">' . number_format($fact_ice, 2, '.', ',') . '</td>';
            $totales .= ' </tr>';
        }

        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 9px;font-family: Arial;">TOTAL: <b>'.$moneda_emp_local_sm.'.</b></td> </b>';
        $totales .= ' <td style="width: 50;font-size: 9px;font-family: Arial;" align="right">' . number_format($fact_tot_fact, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';


        if($moneda_des_mone == "DOLAR"){
            $txt = "ES";
        }else{
            $txt = "S";
        }

        $V = new EnLetras();
        $con_letra = strtoupper($V->ValorEnLetrasMone($fact_tot_fact, $moneda_des_mone));
        
        $totales .= '</table>
        <table style="width: 100%;"> 
            <tr>
                <td style="font-size: 9px;font-family: Arial;" align="left"  width="20"><b>SON: </b></td>
                <td style="font-size: 9px;font-family: Arial;" align="left"  width="185">'.$con_letra.'</td>
            </tr>
            <tr>
            <br><td colspan="3" style="border-top: 1px solid black;"></td>
            </tr>
        </table>';

        $total = number_format($fact_con_miva + $fact_sin_miva + $fact_iva + $fact_ice + $fact_val_irbp + $fact_fle_fact + $fact_otr_fact + $fact_fin_fact - $totalDescuento, 2, '.', '');

        if($valida_pais == '504'){
            $totales .= ' <table style="width: 100%;" style="margin-left:12px;">';
            if($para_punt_emi == 'S'){

                $cod_estab_pto_emi = substr($fact_nse_fact, 0, 3);
                $cod_pto_emi = substr($fact_nse_fact, -6);
                
                if(empty($fact_tip_vent)){
                    $fact_tip_vent = 18;
                }

                $sqlPuntEmi = "SELECT emifa_auto_desde, emifa_auto_hasta, emifa_fec_fin, emifa_num_dig
                                from saeemifa 
                                where emifa_cod_estab = '$cod_estab_pto_emi' and
                                    emifa_cod_pto = '$cod_pto_emi' and emifa_tip_emifa = '$fact_tip_vent'";
                $emifa_auto_desde   = consulta_string($sqlPuntEmi, 'emifa_auto_desde', $oCon, 0);
                $emifa_auto_hasta   = consulta_string($sqlPuntEmi, 'emifa_auto_hasta', $oCon, 0);
                $emifa_fec_fin      = consulta_string($sqlPuntEmi, 'emifa_fec_fin', $oCon, 0);
                $emifa_num_dig      = consulta_string($sqlPuntEmi, 'emifa_num_dig', $oCon, 0);

                $emifa_auto_desde   = str_pad($emifa_auto_desde, $emifa_num_dig, "0", STR_PAD_LEFT);
                $rango_ini          = $cod_estab_pto_emi.'-'.$cod_pto_emi.'-'.$emifa_auto_desde;

                $emifa_auto_hasta   = str_pad($emifa_auto_hasta, $emifa_num_dig, "0", STR_PAD_LEFT);
                $rango_fin          = $cod_estab_pto_emi.'-'.$cod_pto_emi.'-'.$emifa_auto_hasta;
                

                $totales.='<tr>
                            <td style="font-size: 8px;font-family: Arial;" align="center"><b>CAI</b> - '.$fact_nau_fact.'</td> 
                            </tr>
                            <tr>
                            <b> <td style="font-size: 8px;font-family: Arial;" align="center">Rango Autorizado</td> </b>
                            </tr>
                            <tr>
                            <td style="font-size: 8px;font-family: Arial;" align="center">Del '.$rango_ini.' <br> A la '.$rango_fin.' </td> 
                            </tr>
                            <tr>
                            <td style="font-size: 8px;font-family: Arial;" align="center"><b>Fecha Limite de Emision:</b> '.$emifa_fec_fin.'</td> 
                            </tr>';
            }else{
                $totales.='<tr>
                            <td style="font-size: 8px;font-family: Arial;" align="center"><b>CAI</b> - '.$aufa_nau_fact.'</td> 
                            </tr>
                            <tr>
                            <b> <td style="font-size: 8px;font-family: Arial;" align="center">Rango Autorizado</td> </b>
                            </tr>
                            <tr>
                            <td style="font-size: 8px;font-family: Arial;" align="center">Del '.$aufa_nse_fact.'-'.$aufa_fac_inic.' <br> A la '.$aufa_nse_fact.'-'.$aufa_fac_fina.' </td> 
                            </tr>
                            <tr>
                            <td style="font-size: 8px;font-family: Arial;" align="center"><b>Fecha Limite de Emision:</b> '.$aufa_ffi_fact.'</td> 
                            </tr>';
            }
    
            $totales.='<tr>
                            <td colspan="3" style="border-top: 1px solid black;"></td>
                        </tr>
                        </table>';
            $totales .= ' <table style="width: 100%;" style="margin-left:12px;">
                        <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left">* No. Orden de Compra Exenta:</td> 
                        </tr>
                        <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left">* No. Cons. Del Registro de Exonerados: </td> 
                        </tr>
                        <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left">* No. De Registro SAG:</td> 
                        </tr>
                        <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left"></td> 
                        </tr>
                        <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="center">Original: Cliente, <br> Copia: Obligado Tributario Emisor.</td> 
                        </tr>
                        <tr>
                            <td colspan="3" style="border-top: 1px solid black;"></td>
                        </tr>
                        </table>';
        }
        
        //query forma de pago

        $sqlFPago = "SELECT fx.fxfp_val_fxfp,   fx.fxfp_num_dias,   fp.fpag_des_fpag,   f.fact_val_tcam,    fx.fxfp_val_ext,    fm.mone_sgl_mone, f.fact_cm5_fac
                        FROM saefact f, saefxfp fx, saefpag fp, saemone fm
                        WHERE f.fact_cod_fact = fx.fxfp_cod_fact AND 
                                fp.fpag_cod_fpag = fx.fxfp_cod_fpag AND 
                                fm.mone_cod_mone = fp.fpag_cod_mone AND 
                                --f.fact_cod_empr = $idEmpresa AND 
                                --f.fact_cod_sucu = $idSucursal AND 
                                f.fact_cod_fact = $id ";

        if ($oIfx->Query($sqlFPago)) {
            if ($oIfx->NumFilas() > 0) {
                $tablePago = '<table style="width: 100%;">';
                $tablePago .= '<tr >';
                $tablePago .= '<b><td style="width: 130; padding: 0px;font-family: Arial; font-size: 9px;">FORMA PAGO</td></b>';
                $tablePago .= '<b><td style="width: 80; padding: 0px;font-family: Arial; font-size: 9px;">VALOR</td></b>';
                $tablePago .= '</tr>';
                do {
                    $fpag_des_fpag     = $oIfx->f('fpag_des_fpag');
                    $fxfp_val_fxfp      = $oIfx->f('fxfp_val_fxfp');
                    $fxfp_val_ext      = $oIfx->f('fxfp_val_ext');
                    $mone_sgl_mone      = $oIfx->f('mone_sgl_mone');
                    $fxfp_num_dias      = $oIfx->f('fxfp_num_dias');
                    $fpagop_des_fpagop  = $oIfx->f('fpagop_des_fpagop');
                    $fxfp_val_ext = number_format($fxfp_val_ext,2);
                    $fact_cm5_fac = number_format($fact_cm5_fac,2);

                    if ($mone_sgl_mone == 'USD') {
                        $tablePago .= '<tr>';
                        $tablePago .= '<td style="width: 130; font-family: Arial; font-size: 9px;">' . htmlentities($fpag_des_fpag) . '<small><b>('.$fxfp_val_ext.')</b></small></td>';
                        $tablePago .= '<td style="width: 80; font-family: Arial; font-size: 9px;">'. number_format($fxfp_val_fxfp, 2, '.', '') . '</td>';
                        $tablePago .= '</tr>';
                    }else{
                        $tablePago .= '<tr>';
                        $tablePago .= '<td style="width: 130; font-family: Arial; font-size: 9px;">' . htmlentities($fpag_des_fpag) . '</td>';
                        $tablePago .= '<td style="width: 80;  font-family: Arial; font-size: 9px;">' . number_format($fxfp_val_fxfp, 2, '.', '') . '</td>';
                        $tablePago .= '</tr>';
                    }
                } while ($oIfx->SiguienteRegistro());

                $tablePago .= '</table>';
            }
        }
        $oIfx->Free();

        $Contratos = new Contratos($oCon, $oIfx, $idempresa, $fact_cod_sucu, $fact_cod_clpv, $fact_cod_contr);
        $deuda = $Contratos->consultaMontoMesAdeuda();

        $html .= '<div style="width: 80mm; margin-top: 2px;" >'; //div padre

        $html .= '<div style="width: 80mm; margin-left:30px;" >'; //div 2
        
        $html .= '<table align="left">
                    <tr>
                        <td style="margin-top: 0px;" align="center" width="200">
                        '.$logo_empresa.'
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size: 9px; font-family: Arial;" width="200">' . $razonSocial . '</td>
                    </tr>

                    <tr>
                        <td align="center" style="font-size: 9px;font-family: Arial; " width="200">
                            <b>'.$tip_ruc_pais.' N°:</b> ' . $ruc_empr . '
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size: 9px; font-family: Arial;" width="200">
                            ' . $empr_num_dire . '
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 9px;font-family: Arial; text-align: center;" width="200">
                            '.$dirMatriz.'
                        </td>
                    </tr>';
            if($valida_pais != '504'){
                $html.='<tr><td align="center" style="font-size: 9px; font-family: Arial; " width="200"><b>SUCURSAL:</b> ' . $sucu_nom_sucu . '</td>
                        </tr>';
            }
                    if(strlen($empr_tel_resp)>0){
                        $html.='<tr>
                                    <td align="center" style="font-size: 9px; font-family: Arial; " width="200">
                                        <b>TELEFONOS:</b> ' . $empr_tel_resp . '
                                    </td>
                                </tr>';
                    }

                    if(strlen($empr_mai_empr)>0){
                        $html.='<tr>
                                    <td align="center" style="font-size: 9px; font-family: Arial; " width="200">
                                        <b>EMAIL:</b><br>' . $empr_mai_empr . '
                                    </td>
                                </tr>';
                    }

                    if($valida_pais == '504'){
                        $html.='<tr><td align="center" style="font-size: 9px; font-family: Arial; " width="200">
                                    <b>WHATSAPP:</b> ' . $empr_fax_empr . '
                                    </td>
                                </tr>
                                <tr><td align="center" style="font-size: 9px; font-family: Arial; " width="200">
                                    <b>FACEBOOK:</b> ' . $empr_ema_comp . '
                                    </td>
                                </tr>
                                ';
                    }
                    //echo $fact_nse_fact;exit;
                    $cadena_buscada   = 'B';
                    $posicion_coincidencia = strpos($fact_nse_fact, $cadena_buscada);
        
                // echo $posicion_coincidencia;exit;
        
                    if (!empty($posicion_coincidencia)) {
                        $titulo="BOLETA - ELECTRONICA";
                    }else{
                        $titulo="FACTURA - ELECTRONICA";
                        }
        
                    
            /*$html.='<tr>
                        <td style="font-size: 9px; font-family: Arial;" colspan="2"  align="center" width="200"><b>'.$titulo.'</b></td>
                    </tr>';*/
                    if($para_punt_emi == 'S' && $valida_pais == '504'){
                        $num_serie = $fact_nse_fact . ' - ' . $fact_num_preimp;
                        $num_serie = preg_replace('/(\d{3})(\d{3})-(\d{2}) - (\d+)/', '$1-$2-$3-$4', $num_serie);

                        $html.='<tr>
                                    <td style="font-size: 12px; font-family: Arial; width:40mm;" align="center" colspan="2" width="200">' . $num_serie . '</td>
                                </tr>';
                    }/*else{
                        $html.='<tr>
                                    <td style="font-size: 12px; font-family: Arial; width:40mm;" align="center" colspan="2" width="200">' . $fact_nse_fact . ' - ' . $fact_num_preimp . '</td>
                                </tr>';
                    }
                
            $html.='<tr>
                        <td style="font-size: 9px; font-family: Arial;" align="center" colspan="2" width="200">
                            <b>Fecha y Hora de Emisión: </b>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="center" colspan="2" width="200">
                            ' . $fact_fech_fact . ' - '.$fact_hor_fin.'
                        </td>
                    </tr>
                </table>';*/
                $html.='</table>';

        $pos      = strripos($fact_nom_cliente, ',');
        if( $pos > 0 ){
            list($a1,$b1,$c1,$d1,$e1,$f1,$g1, $h1, $i1, $j1, $k1, $l1, $m1, $n1 ) = explode(',', $fact_nom_cliente);
            list($a111, $b111, $c111, $d111) = explode(' ', $fact_nom_cliente);
            if( !empty($d111) ){
                list($a1,$b1,$c1,$d1,$e1,$f1, $g1, $h1, $i1, $j1, $k1, $l1, $l1, $m1, $n1) = explode(' ', $fact_nom_cliente);
            }

        }else{
            list($a1,$b1,$c1,$d1,$e1,$f1,$g1, $h1, $i1, $j1, $k1, $l1, $m1, $n1) = explode(' ', $fact_nom_cliente);
        }


        $fact_fech_fact=date('d-m-Y',strtotime($fact_fech_fact));

        $html .= '
        
         <table align="left" >
                    <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="120"><b>'.$titulo.'</b></td>
                        <td style="font-size: 9px;font-family: Arial;" align="right" width="90">' . $fact_nse_fact . ' - ' . $fact_num_preimp . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="120"><b>FECHA DE EMISION:</b> '.$fact_fech_fact.'</td>
                        <td style="font-size: 9px;font-family: Arial;" align="right" width="90"><b>HORA:</b> '.$fact_hor_fin.'</td>
                    </tr>
                </table>

                <table align="left" >
                    <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="60"><b>CONTRATO:</b></td>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="145">'.$codigo_clpv.'</td>
                    </tr>
                    <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="60"><b>CLIENTE:</b></td>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="145">'.$fact_nom_cliente.'</td>
                    </tr>
                    
                    <tr>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="60"><b>'.$tip_iden_cliente.':</b></td>
                        <td style="font-size: 9px;font-family: Arial;" align="left" width="145">'.$fact_ruc_clie.'</td>
                    </tr>
                    <tr>
                    <td style="font-size: 9px;font-family: Arial;" align="left" width="60"><b>DIRECCION:</b></td>
                    <td style="font-size: 9px;font-family: Arial;" align="left" width="145">'.$fact_dir_clie.'</td>
                    </tr>
                    <tr>
                    <td style="font-size: 9px;font-family: Arial;" align="left" width="60"><b>MONEDA:</b></td>
                    <td style="font-size: 9px;font-family: Arial;" align="left" width="145">'.$moneda_des_mone.'</td>
                    </tr>
                    <tr>
                    <td style="font-size: 9px;font-family: Arial;" align="left" width="60"><b>OBSERVACION:</b></td>
                    <td style="font-size: 9px;font-family: Arial;" align="left" width="145">'.$observacion.'</td>
                    </tr>
                </table>';

        $html .= $deta;
        $html .= $totales;
        $html .= $tablePago;

        $html .=    '<table align="left">
                        <tr>
                            <td style="font-size: 9px;font-family: Arial;" width="130"  align="left">
                            <b>SALDO:</b>
                            </td>
                            <td style="font-size: 9px;" width="80" >' . number_format($deuda, 2, '.', ',') . '</td>
                        </tr>';

                        if($valida_pais == '504'){
                        $html .= '<tr>
                                    <td style="font-size: 10px;font-family: Arial;" align="center" colspan="2">
                                        <b>CAJA EMPRESARIAL BANCO OCCIDENTE <br> CON CODIGO DE CLIENTE</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px;font-family: Arial;" align="center" colspan="2">
                                        <b>PARA DEPOSITO ENVIAR BAUCHER DE <br> PAGO POR WHATSAPP - <br>'.$empr_fax_empr.' <br> OCCIDENTE: # 11-201-013934-6</b>
                                    </td>
                                </tr>';
                        }
        $html .=    '<tr>
                            <td style="font-size: 9px;font-family: Arial;" align="center" colspan="2">
                                <b>https://www.sisconti.com</b>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 9px;font-family: Arial;" align="center" colspan="2">
                                '.$empr_cm2_empr.'
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 9px;font-family: Arial;" align="center" colspan="2">
                                GRACIAS POR SU PAGO!
                            </td>
                        </tr>
                        </table>
                </div> 
                </div>'; //fin div padre


    } else {
        $table = '<div>No existe Factura...</div>';
    }

    //arma pdf

    $table = '<page>';
    $table.= $html;
    $table.= '</page>';

    $html2pdf = new HTML2PDF('P', 'B5', 'es', true, 'UTF-8', array(0,0,0,0));
    $html2pdf->WriteHTML($table);
    $html2pdf->Output('recibo_template.pdf', '');
    $ruta = DIR_FACTELEC . 'Include/archivos/' . $id . '.pdf';
    $html2pdf->Output($ruta, 'F');

    return $ruta;

}


?>