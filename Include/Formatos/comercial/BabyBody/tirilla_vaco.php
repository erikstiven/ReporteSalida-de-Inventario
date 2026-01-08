<?

function formato_tirilla($id, $tipo)
{
    global $DSN_Ifx, $DSN;
    include_once DIR_FACTELEC . "Include/Librerias/barcode1/vendor/autoload.php";
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
    $array_imp = $_SESSION['U_EMPRESA_IMPUESTO'];
    $etiqueta_iva = $array_imp['IVA'];
    $empr_cod_pais = $_SESSION['U_PAIS_COD'];
    // IMPUESTOS POR PAIS
    $sql = "select p.impuesto, p.etiqueta, p.porcentaje from comercial.pais_etiq_imp p where
     p.pais_cod_pais = $empr_cod_pais and etiqueta='$etiqueta_iva'";
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {
                $impuesto      = $oCon->f('impuesto');
                $etiqueta     = $oCon->f('etiqueta');
                $porcentaje = $oCon->f('porcentaje');
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();


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
            $empr_sitio_web = $oIfx->f('empr_sitio_web');
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

    $sql = "select fact_cod_fact,fact_cod_clpv, fact_tip_vent, fact_cm4_fact,fact_cm1_fact from saefact where
                    fact_cod_empr = $idEmpresa and
                    fact_cod_fact = $id 
                    ";
    $contador = consulta_string($sql, 'fact_cod_fact', $oIfx, 0);
    $cliente = consulta_string($sql, 'fact_cod_clpv', $oIfx, 0);
    $descuento_temp = consulta_string($sql, 'fact_cm4_fact', $oIfx, 0);
    $observacion = consulta_string($sql, 'fact_cm1_fact', $oIfx, '');


    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;


    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

    if (file_exists($path_logo_img)) {
        $logo_empresa = ' <img width="170px;"  src="' . $path_logo_img . '">';
    } else {
        $logo_empresa = '<div style="color:red;">LOGO NO CARGADO</div>';
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
                    $nse_fact = substr($fact_nse_fact, 4, 9);

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
                    $fact_cm1_fact      = trim($oIfx->f("fact_cm1_fact"));
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
                    $fact_fin_fact       = $oIfx->f("fact_fin_fact");


                    $fact_cm7_fac       = $oIfx->f("fact_cm7_fac");

                    $fact_cod_mone = $oIfx->f('fact_cod_mone');
                    $fact_val_tcam = $oIfx->f('fact_val_tcam');

                    $sql = "select mone_des_mone, mone_sgl_mone, mone_smb_mene from saemone where mone_cod_mone =  $fact_cod_mone;";
                    $moneda = consulta_string($sql, 'mone_des_mone', $oCon, '');
                    $smbmone = consulta_string($sql, 'mone_smb_mene', $oCon, '');
                    $sigmone = consulta_string($sql, 'mone_sgl_mone', $oCon, '');


                    ///VALIDACION MONEDA
                    $sqlmon = "select pcon_mon_base, pcon_seg_mone from saepcon where pcon_cod_empr=$idEmpresa";
                    $pcon_seg_mone = consulta_string($sqlmon, 'pcon_seg_mone', $oCon, '');

                    //ETIQUETA MONEDA
                    if ($sigmone == 'USD') {
                        $eti_mone =  $sigmone . $smbmone;
                    } else {
                        $eti_mone =  $sigmone . '/';
                    }

                    $sql = "select tcmp_des_tcmp from saetcmp where tcmp_cod_tcmp = '$fact_tip_vent'";
                    $tcmp_des_tcmp = 'BOLETA ELECTRONICA'; //;consulta_string_func($sql, 'tcmp_des_tcmp', $oIfxA, '');

                    if ($fact_dia_plazo == 0) {
                        $sql            = "select fxfp_num_dias, fxfp_fec_fin from saefxfp where fxfp_cod_fact = $id and fxfp_cod_empr = $idEmpresa and fxfp_cod_sucu = $idSucursal ";
                        $fact_dia_plazo = consulta_string_func($sql, 'fxfp_num_dias', $oIfxA, 0);
                        $fact_fech_venc = fecha_mysql_func(consulta_string_func($sql, 'fxfp_fec_fin',  $oIfxA, ''));
                    }

                    $fact_dir_clie = $fact_dir_clie;

                    $logo .= '<b><td style="width: 40%; border: 1px solid black; border-radius: 5px;" align="center">';
                    $logo .= ' <table align="center" style="font-size: 15px;">';
                    $logo .= ' <tr>';
                    $logo .= '<td style="border-bottom: 1px inset black;">' . $tcmp_des_tcmp . '</td>';
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

                    $sql = "SELECT clv_con_clpv from saeclpv where clpv_cod_clpv = $cliente";
                    $clv_con_clpv = consulta_string_func($sql, 'clv_con_clpv', $oCon, '');

                    if (strlen($clv_con_clpv) == 0) {
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
                    //CONSUMIDOR FINAL
                    if ($clv_con_clpv == '07') {
                        $tip_iden_cliente = 'ID';
                    }
                    if (!empty($fact_cod_contr)) {
                        $sql = "SELECT id_sector, id_barrio, ruta, id_clpv, codigo FROM isp.contrato_clpv WHERE id = $fact_cod_contr";
                        if ($oCon->Query($sql)) {
                            if ($oCon->NumFilas() > 0) {
                                $id_sector  = $oCon->f('id_sector');
                                $id_barrio  = $oCon->f('id_barrio');
                                $ruta       = $oCon->f('ruta');
                                $id_clpv    = $oCon->f('id_clpv');
                                $codigo_clpv    = $oCon->f('codigo');
                            }
                        }
                        $oCon->Free();





                        if (!empty($id_sector)) { //sector
                            $sql = "SELECT sector FROM comercial.sector_direccion WHERE id = $id_sector";
                            $sector = consulta_string_func($sql, 'sector', $oCon, '');
                        }

                        if (!empty($id_barrio)) { //sector
                            $sql = "SELECT barrio FROM isp.int_barrio WHERE id = $id_barrio";
                            $barrio = consulta_string_func($sql, 'barrio', $oCon, '');
                        }
                    }
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $sql_sucu = "SELECT usuario_nombre, usuario_apellido, usuario_user from comercial.usuario where usuario_id = $fact_user_web";
        if ($oIfx->Query($sql_sucu)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $usuario_nombre = $oIfx->f('usuario_nombre');
                    $usuario_apellido = $oIfx->f('usuario_apellido');
                    $usuario_user = $oIfx->f('usuario_user');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $nombre_cajero = $usuario_user;

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

                    if ($dfac_por_iva > 0) {
                        $dfac_precio_dfac = round(($dfac_precio_dfac + ($dfac_precio_dfac * $dfac_por_iva / 100)), 0);
                    }

                    $tot_det += round($dfac_precio_dfac, 0);
                } while ($oIfx->SiguienteRegistro());
            }
        }

        $dif = round(($tot_fxfp - $tot_det), 2);

        $deta = ' <table style="width: 100%;" >
                    <tr>
                        <td colspan="7" style="border-top: 1px solid black;" width="200"></td>
                    </tr>';
        $deta .= ' <tr>';
        $deta .= ' <b> <td style="width: 10; font-size: 7px;font-family: Arial;" align="center">CANT.</td> </b>';
        $deta .= ' <b> <td style="width: 40; font-size: 7px;font-family: Arial;" align="center">COD</td> </b>';
        // $deta .= ' <b> <td style="width: 41; font-size: 7px;font-family: Arial;" align="center">LOTE</td> </b>';
        $deta .= ' <b> <td style="width: 45; font-size: 7px;font-family: Arial;" align="center">DETALLE</td> </b>';
        $deta .= ' <b> <td style="width: 20; font-size: 7px;fosnt-family: Arial;" align="center">C/UNIT</td> </b>';
        $deta .= ' <b> <td style="width: 20; font-size: 7px;font-family: Arial;" align="center">DESC.</td> </b>';
        $deta .= ' <b> <td style="width: 20; font-size: 7px;font-family: Arial;" align="center">TOTAL</td> </b>';
        $deta .= ' </tr>
                    <tr>
                        <td colspan="7" style="border-top: 1px solid black;" width="200"></td>
                    </tr>';

        $desc_monto = 0;
        $monto_precio = 0;
        $rd = 1;

        $sql_color = "SELECT estado, color from comercial.color_inv";
        unset($nombre_color);
        $nombre_color = array_dato($oIfx, $sql_color, 'estado', 'color');

        if ($oIfx->Query($sqlDeta)) {
            if ($oIfx->NumFilas() > 0) {
                $porcIva = '';
                $totalDescuento = 0;
                $tot_opgrav = 0;
                $tot_opinafe = 0;
                $tot_opexo = 0;
                do {

                    $dfac_cod_prod = $oIfx->f('dfac_cod_prod');
                    $dfac_cod_lote = $oIfx->f('dfac_cod_lote');
                    $dfac_nom_prod = $oIfx->f('dfac_nom_prod');
                    $dfac_cant_dfac = $oIfx->f('dfac_cant_dfac');
                    $total_cant += $dfac_cant_dfac;
                    $dfac_precio_dfac = $oIfx->f('dfac_precio_dfac');
                    $dfac_des1_dfac = $oIfx->f('dfac_des1_dfac');
                    $dfac_des2_dfac = $oIfx->f('dfac_des2_dfac');
                    $dfac_por_dsg = $oIfx->f('dfac_por_dsg');
                    $dfac_mont_total = $oIfx->f('dfac_mont_total');
                    $dfac_num_comp = $oIfx->f('dfac_tip_dfac');
                    $dfac_por_iva = $oIfx->f('dfac_por_iva');
                    $dfac_det_dfac = $oIfx->f('dfac_det_dfac');

                    $sql = "SELECT empr_menu_sucu from saeempr where empr_cod_empr=$idEmpresa";
                    $detalle_sn = consulta_string_func($sql, 'empr_menu_sucu', $oIfxA, '');


                    // if($detalle_sn=='S'){

                    // list($serie, $num_documento, $indefinido) = explode("-", $dfac_cod_prod);

                    $array_codigo_prod = explode("-", $dfac_cod_prod);

                    $codigo_color = end($array_codigo_prod);


                    // var_dump($codigo_color);exit;

                    $sql_prod = "SELECT SUBSTRING(prod_cod_prod, 1, 3)as cod_prod, prod_det_prod from saeprod where prod_cod_prod='$dfac_cod_prod'";
                    //echo $sql;exit;
                    $dfac_nom_prod = consulta_string_func($sql_prod, 'prod_det_prod', $oIfxA, '');
                    $cod_prod = consulta_string_func($sql_prod, 'cod_prod', $oIfxA, '');
                    $dfac_det_dfac = $dfac_nom_prod;
                    $dfac_cod_prod = $cod_prod;

                    //  }

                    $nombre_completo = $dfac_nom_prod . ' - ' . $nombre_color[$codigo_color];

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


                    $deta .= ' <tr>';
                    $deta .= ' <td style="width: 10;font-family: Arial; font-size: 7px;">' . number_format($dfac_cant_dfac, 2, '.', ',') . '</td>';
                    $deta .= ' <td style="width: 40;font-family: Arial; font-size: 7px;" align="center">' . $dfac_cod_prod . '</td>';
                    //$deta .= ' <td style="width: 41;font-family: Arial; font-size: 7px;">' . $dfac_cod_lote . '</td>';
                    $deta .= ' <td style="width: 45;font-family: Arial; font-size: 5px;">' . $nombre_completo . '</td>';

                    $deta .= ' <td style="width: 20; font-family: Arial; font-size: 7px;" align="right">' . number_format($dfac_precio_dfac, 2, '.', ',') . '</td>';
                    $deta .= ' <td style="width: 20; font-family: Arial; font-size: 7px;" align="right">' . number_format($dfac_des1_dfac, 2, '.', ',') . '</td>';
                    $deta .= ' <td style="width: 20; font-family: Arial; font-size: 7px;" align="right">' . number_format($dfac_mont_total, 2, '.', ',') . '</td>';
                    $deta .= ' </tr>';

                    $monto_precio += round($dfac_precio_dfac * $dfac_cant_dfac, 2);
                    $rd++;
                } while ($oIfx->SiguienteRegistro());
            }
        }

        $deta .= '<tr>
                        <td colspan="7" style="border-top: 1px solid black;" width="200"></td>
                    </tr> </table>';

        if ($fact_cod_mone == $pcon_seg_mone) {
            $fact_iva = $fact_iva / $fact_val_tcam;
            $fact_tot_fact = $fact_tot_fact / $fact_val_tcam;
        }

        $totales = ' <table style="width: 100%; margin-top: 2px;">';


        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 7px;font-family: Arial;">SUBTOTAL SIN IMPUESTOS:</td> </b>';
        $totales .= ' <td style="width: 50;font-size: 7px;font-family: Arial;" align="right">' . number_format($fact_con_miva + $fact_sin_miva, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';

        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 7px;font-family: Arial;">DESCUENTO:</td></b>';
        $totales .= ' <td style="width: 50;font-size: 7px;font-family: Arial;" align="right">' . number_format($fact_dsg_valo, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';



        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 7px;font-family: Arial;">' . $array_imp['IVA'] . '  ' . $porcentaje . ' %:</td> </b>';
        $totales .= ' <td style="width: 50;font-size: 7px;font-family: Arial;" align="right">' . number_format($fact_iva, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';

        $idEmpresa      = $_SESSION['U_EMPRESA'];
        $idSucursal     = $_SESSION['U_SUCURSAL'];
        //consulta parametros para ventana de productos, stock, disponible
        $sql = "SELECT stock_general, aut_pedido, cant_guia, 
        focus_factura, porc_servicio 
        from comercial.config_menu_pv 
        where id_empresa = $idEmpresa and
        id_sucursal = $idSucursal";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $stock_general = $oCon->f('stock_general'); //visualiza stock de las sucursales
                    $aut_pedido = $oCon->f('aut_pedido'); //campo para calcular disponibilidad el pedido
                    $cant_guia = $oCon->f('cant_guia'); //campo para calcular disponibilidad el pedido
                    $focus_factura = $oCon->f('focus_factura'); //campo para focus en el input
                    $porc_servicio = $oCon->f('porc_servicio'); //porcentaje de servicio
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();


        $calculo_porcentaje_servicio = ($fact_con_miva + $fact_iva) * $porc_servicio / 100;

        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 7px;font-family: Arial;">Servicios  ' . $porc_servicio . ' %:</td> </b>';
        $totales .= ' <td style="width: 50;font-size: 7px;font-family: Arial;" align="right">' . number_format($calculo_porcentaje_servicio, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';


        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 7px;font-family: Arial;">Otros Valores:</td> </b>';
        $totales .= ' <td style="width: 50;font-size: 7px;font-family: Arial;" align="right">' . number_format($fact_fin_fact, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';


        


        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 7px;font-family: Arial;">TOTAL:</td> </b>';
        $totales .= ' <td style="width: 50;font-size: 7px;font-family: Arial;" align="right">' . number_format($fact_con_miva+$fact_iva+$calculo_porcentaje_servicio+$fact_fin_fact, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';



        if ($moneda == "DOLAR") {
            $txt = "ES";
        } else {
            $txt = "S";
        }

        $V = new EnLetras();

        if ($fact_cod_mone == $pcon_seg_mone) {

            $con_letra = strtoupper($V->ValorEnLetras($fact_tot_fact, $moneda));
        } else {
            $con_letra = strtoupper($V->ValorEnLetras($fact_tot_fact, $moneda));
        }



        $totales .= ' </table><table style="width: 100%;"> <tr>
                            <td style="font-size: 7px;font-family: Arial;" align="left"  width="20"><b>SON: </b></td>
                            <td style="font-size: 7px;font-family: Arial;" align="left"  width="185">' . $con_letra . '</td>
                        </tr>
                        <tr><br>
                            <td colspan="2" style="border-top: 1px solid black;"></td>
                        </tr> </table>';



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
                $tablePago .= '<b><td style="width:130; padding: 0px;font-family: Arial; font-size: 7px;">FORMA PAGO</td></b>';
                $tablePago .= '<b><td style="width: 80; padding: 0px;font-family: Arial; font-size: 7px;">VALOR</td></b>';
                $tablePago .= '</tr>';
                do {
                    $fpag_des_fpag     = $oIfx->f('fpag_des_fpag');
                    $fxfp_val_fxfp      = $oIfx->f('fxfp_val_fxfp');
                    if ($fact_cod_mone == $pcon_seg_mone) {
                        $fxfp_val_fxfp = $fxfp_val_fxfp / $fact_val_tcam;
                    }
                    $fxfp_val_ext      = $oIfx->f('fxfp_val_ext');
                    $mone_sgl_mone      = $oIfx->f('mone_sgl_mone');
                    $fxfp_num_dias      = $oIfx->f('fxfp_num_dias');
                    $fpagop_des_fpagop  = $oIfx->f('fpagop_des_fpagop');
                    $fxfp_val_ext = number_format($fxfp_val_ext, 2);
                    $fact_cm5_fac = number_format($fact_cm5_fac, 2);

                    if ($mone_sgl_mone == 'USD') {
                        $tablePago .= '<tr>';
                        $tablePago .= '<td style="width: 130; font-family: Arial; font-size: 7px;">' . htmlentities($fpag_des_fpag) . '</td>';
                        $tablePago .= '<td style="width: 80; font-family: Arial; font-size: 7px;">' . number_format($fxfp_val_fxfp, 2, '.', '') . '</td>';
                        $tablePago .= '</tr>';
                    } else {
                        $tablePago .= '<tr>';
                        $tablePago .= '<td style="width: 130; font-family: Arial; font-size: 7px;">' . htmlentities($fpag_des_fpag) . '<small><b>(' . $fxfp_val_ext . ')</b></small></td>';
                        $tablePago .= '<td style="width: 80; font-family: Arial; font-size: 7px;">' . number_format($fxfp_val_fxfp, 2, '.', '') . '</td>';
                        $tablePago .= '</tr>';
                    }
                } while ($oIfx->SiguienteRegistro());

                $tablePago .= '</table>';
            }
        }
        $oIfx->Free();


        $html .= '<div style=" height: 145mm;" >'; //div padre

        $html .= '<div style="margin-left:2px;" >'; //div 2

        $html .= '<table align="left">
        <tr>
                        <td align="center"  width="200">' . $logo_empresa . '</td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size: 8px;text-align:center;font-family: Arial;" width="200"><b>' . $razonSocial . '</b></td>
                    </tr>
                    <tr>
                        <td style="font-size: 7px;font-family: Arial; text-align: center;" width="200">' . $sucu_dir_sucu . '</td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size: 7px;font-family: Arial; " width="200"><b>' . $tip_ruc_pais . ' N°</b> ' . $ruc_empr . '</td>
                    </tr>
                    <tr><td align="center" style="font-size: 7px; font-family: Arial; " width="200">' . $sucu_nom_sucu . '</td>
                        </tr>';


        //echo $fact_nse_fact;exit;
        $cadena_buscada   = 'B';
        $posicion_coincidencia = strpos($fact_nse_fact, $cadena_buscada);

        // echo $posicion_coincidencia;exit;

        if (!empty($posicion_coincidencia)) {
            $titulo = "BOLETA - ELECTRONICA";
            $tipo_envio = 'BOLETA';
        } else {
            $titulo = "FACTURA - ELECTRONICA";
            $tipo_envio = 'FACTURA';
        }

        if ($fact_tip_vent == 99) {
            $titulo = "RECIBO DE VENTA";
        }

        //CODIGO QR

        /* $barcode = new \Com\Tecnick\Barcode\Barcode();

    $datosqr=$ruc_empr.'|'.$tipo_envio.'|'.$nse_fact.'|'.$fact_num_preimp.'|'.number_format($fact_iva, 2, '.', ',').'|'.number_format($fact_tot_fact, 2, '.', ',').'|'.$fact_fech_fact.'|';

    $bobj = $barcode->getBarcodeObj(
        'QRCODE,H',                     // Tipo de Barcode o Qr
        $datosqr,          // Datos
        -2,                             // Width
        -2,                             // Height
        'black',                        // Color del codigo
        array(-2, -2, -2, -2)           // Padding
        )->setBackgroundColor('white'); // Color de fondo

    $imageData = $bobj->getPngData(); // Obtenemos el resultado en formato PNG

     $ruta_dir = DIR_FACTELEC . 'modulos/facturacion_boostrap/qr_facturas';
        if (!file_exists($ruta_dir)){
            mkdir($ruta_dir,0777,true);
        }
*/
        //file_put_contents(DIR_FACTELEC .'modulos/facturacion_boostrap/qr_facturas/REC_'.$id.'.png', $imageData); // Guardamos el resultado

        $ruta = DIR_FACTELEC . 'modulos/facturacion_boostrap/qr_facturas/REC_' . $id . '.png';


        $html .= '</table>';

        $pos      = strripos($fact_nom_cliente, ',');
        if ($pos > 0) {
            list($a1, $b1, $c1, $d1, $e1, $f1, $g1, $h1, $i1, $j1, $k1, $l1, $m1, $n1) = explode(',', $fact_nom_cliente);
            list($a111, $b111, $c111, $d111) = explode(' ', $fact_nom_cliente);
            if (!empty($d111)) {
                list($a1, $b1, $c1, $d1, $e1, $f1, $g1, $h1, $i1, $j1, $k1, $l1, $l1, $m1, $n1) = explode(' ', $fact_nom_cliente);
            }
        } else {
            list($a1, $b1, $c1, $d1, $e1, $f1, $g1, $h1, $i1, $j1, $k1, $l1, $m1, $n1) = explode(' ', $fact_nom_cliente);
        }
        $fact_fech_fact = date('d-m-Y', strtotime($fact_fech_fact));
        $html .= '<table align="left" >
        <tr>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="110"><b><b>' . $titulo . '</b></b></td>
                        <td style="font-size: 7px;font-family: Arial;" align="RIGHT" width="90">' . $fact_nse_fact . ' - ' . $fact_num_preimp . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="110"><b>FECHA DE EMISIÓN:</b> ' . $fact_fech_fact . '</td>
                        <td style="font-size: 7px;font-family: Arial;" align="RIGHT" width="90"><b>HORA:</b> ' . $fact_hor_fin . '</td>
                    </tr>
                </table>
                <table align="left" >
                    
                    <tr>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="55"><b>NOMBRE:</b></td>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="145">' . $fact_nom_cliente . '</td>
                    </tr>
                    <tr>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="55"><b>' . $tip_iden_cliente . ':</b></td>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="145">' . $fact_ruc_clie . '</td>
                </tr>
                    <tr>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="55"><b>DIRECCION:</b></td>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="145">' . $fact_dir_clie . '</td>
                </tr>
                </table> ';

        $html .= $deta;
        $html .= $totales;
        $html .= $tablePago;

        $html .=    '
                        <table style="width: 100%;">
                        
                        <tr>
                            <td style="font-size: 7px;font-family: Arial;" align="left" width="130"><b>CAJERO:</b></td>
                            <td style="font-size: 7px;font-family: Arial;"  align="left" width="80">' . $nombre_cajero . '</td>
                        </tr>';


        $html .=    '
                        <tr>
                            <td style="font-size: 7px;font-family: Arial;" align="center" colspan="2" width="200"><br>AGRADECEMOS SU PREFERENCIA</td>
                        </tr>
                        
                        <tr>
                            <td style="font-size: 7px;font-family: Arial;" align="center" colspan="2" width="200"><br><b>NOTA: </b>Una vez generada la factura no se acepta <b>CAMBIOS</b> ni <b>DEVOLUCIONES</b></td>
                        </tr>
                        ';

        /*if($fact_tip_vent!=99){
                            $html .=    '<tr>
                            <td style="font-size: 7px;font-family: Arial;" align="center" colspan="2" width="200" ><img src="'.$ruta.'"></td>
                            </tr>
                            <tr>
                                <td style="font-size: 7px;font-family: Arial;" align="center" colspan="2" width="200">Representación impresa de la factura de venta electrónica
                                </td>
                            </tr>';
                        }*/

        $html .=    '   </table>
                  
                </div> 
                </div>'; //fin div padre


    } else {
        $table = '<div>No existe Factura...</div>';
    }

    //arma pdf

    $table = '<page>';
    $table .= $html;
    $table .= '</page>';


    return $table;
}
