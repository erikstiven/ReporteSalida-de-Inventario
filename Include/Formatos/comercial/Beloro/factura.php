<?


function reporte_factura_personalizado($id = '', $nombre_archivo = '', $idSucursal = '', &$rutaPdf = '')
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


    $oIfC = new Dbo;
    $oIfC->DSN = $DSN_Ifx;
    $oIfC->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();


    $idEmpresa = $_SESSION['U_EMPRESA'];
    //$idSucursal = $_SESSION['U_SUCURSAL'];

    //CAMPOS APROBACION
    $sqlgein = "SELECT count(*) as conteo
     FROM INFORMATION_SCHEMA.COLUMNS
     WHERE COLUMN_NAME = 'empr_fac_empr' AND TABLE_NAME = 'saeempr'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "alter table saeempr add  empr_fac_empr varchar(1)";
        $oCon->QueryT($sqlalter);
    }


    $sqlgein = "SELECT count(*) as conteo
     FROM INFORMATION_SCHEMA.COLUMNS
     WHERE COLUMN_NAME = 'empr_det_fac' AND TABLE_NAME = 'saeempr'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
    if ($ctralter == 0) {
        $sqlalter = "alter table saeempr add  empr_det_fac text";
        $oCon->QueryT($sqlalter);
    }

     //ARRAY UNIDADES - 
     $sql = "select unid_cod_unid, unid_nom_unid from saeunid 
     where unid_cod_empr = $idEmpresa";
     unset($array_unidad);
     if ($oIfx->Query($sql)) {
         if ($oIfx->NumFilas() > 0) {
             do {
                     $array_unidad[$oIfx->f('unid_cod_unid')] = $oIfx->f('unid_nom_unid');
             } while ($oIfx->SiguienteRegistro());
         }
     }
     $oIfx->Free();


    $sql = "SELECT  empr_sn_conta, empr_cod_pais, empr_cm1_empr, empr_rimp_sn, 
                    empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, 
                    empr_num_resu, empr_path_logo, empr_iva_empr,empr_tel_resp, 
                    empr_ac1_empr, empr_ac2_empr, empr_mai_empr, empr_tip_empr, empr_cm2_empr,
                    empr_fac_empr, empr_det_fac, empr_cta_sn, empr_det_cta,
                    empr_rinf_sn,  empr_det_rinf
                    from saeempr where empr_cod_empr = $idEmpresa ";


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_path_logo');
            $tel_empresa = $oIfx->f('empr_tel_resp');
            $empr_mai_empr = $oIfx->f('empr_mai_empr');
            if ($oIfx->f('empr_conta_sn') == 'S')
                $empr_conta_sn = 'SI';
            else
                $empr_conta_sn = 'NO';
            $empr_rimp_sn = $oIfx->f('empr_rimp_sn');
            $empr_num_resu = $oIfx->f('empr_num_resu');
            $empr_iva_empr = $oIfx->f('empr_iva_empr');
            $empr_ac1_empr = $oIfx->f('empr_ac1_empr');
            $empr_ac2_empr = $oIfx->f('empr_ac2_empr');
            $empr_cm1_empr = $oIfx->f('empr_cm1_empr');
            $empr_cod_pais = $oIfx->f('empr_cod_pais');
            $empr_tip_empr = $oIfx->f('empr_tip_empr');
            $empr_cm2_empr = $oIfx->f('empr_cm2_empr');
            $empr_sn_conta = $oIfx->f('empr_sn_conta');


            $obfact = $oIfx->f('empr_fac_empr');
            $obdetfact = $oIfx->f('empr_det_fac');
            //INFORMACION BANCARIA
            $empr_cta_sn = $oIfx->f('empr_cta_sn');
            $empr_det_cta = $oIfx->f('empr_det_cta');
            //INFORMACION ADICIONAL
            $empr_rinf_sn = $oIfx->f('empr_rinf_sn');
            $empr_det_rinf = $oIfx->f('empr_det_rinf');
        }
    }
    $oIfx->Free();

    //VALIDACION DETALLE- CONVERSION

    $sqlfa = "select para_dfa_para, para_conv_sn from saepara where para_cod_empr= $idEmpresa and para_cod_sucu=$idSucursal";
    $para_dfa_para = consulta_string($sqlfa, 'para_dfa_para', $oCon, '');
    $para_conv_sn = consulta_string($sqlfa, 'para_conv_sn', $oCon, '');


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

    $sqls = "select count(*) as cont from saesucu";
    $contsucu = consulta_string($sqls, 'cont', $oIfx, 0);

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



    // $path_logo_img = DIR_FACTELEC . 'imagenes/logos/' . $path_img[$count];
    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];
    if (file_exists($path_logo_img)) {
        //echo "El fichero $nombre_fichero existe";
    } else {
        //echo "El fichero $nombre_fichero no existe";
        $path_logo_img = "https://turbologo.com/articles/wp-content/uploads/2019/05/no-logo.png";
    }
    //https://turbologo.com/articles/wp-content/uploads/2019/05/no-logo.png


    $logo .= '<table style="width: 100%; margin: 0px;">';
    $logo .= '<tr>';
    $logo .= '<td align="center" style="width: 55%; border:1px solid black; border-radius: 5px; margin: 0px;">';
    $logo .= '<table width: 55%; valign="center" style="margin: 0px;">';
    $logo .= '<tr><td style="margin-top: 0px;"><img width="250px;"  src="' . $path_logo_img . '"></td></tr>';
    $logo .= '<tr><td>&nbsp;</td></tr>';
    $logo .= '<tr><td align="left" style="font-size: 15px;" width="300"><b>Emisor:</b> ' . $razonSocial . '</td></tr>';

    $sqlxml = "select ixml_tit_ixml, ixml_det_ixml from saeixml where ixml_cod_empr=$idEmpresa 
				and ixml_est_deleted ='S' and ixml_sn_pdf='S' order by ixml_ord_ixml";

    if ($oIfx->Query($sqlxml)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $titulo  = $oIfx->f('ixml_tit_ixml');
                $detalle = $oIfx->f('ixml_det_ixml');
                $logo .= '<tr><td align="left" style="font-size: 14px;" width="300"><b>' . $titulo . '</b> ' . $detalle . '</td></tr>';
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();


    //$logo .= '<tr><td>&nbsp;</td></tr>';
    $logo .= '<tr><td align="left" style="font-size: 13px;"><b>RUC:</b> ' . $ruc_empr . '</td></tr>';
    //$logo .= '<tr><td>&nbsp;</td></tr>';
    //$logo .= '<tr><td>&nbsp;</td></tr>';
    $logo .= '<tr><td align="left" style="font-size: 13px;" width="500"><b>Matriz:</b> ' . $dirMatriz . '</td></tr>';
    //$logo .= '<tr><td>&nbsp;</td></tr>';


    //selecciona sucursales y direcciones
    $sql_sucu_matriz = "select sucu_dir_sucu from saesucu where sucu_nom_sucu ='MATRIZ'";
    $matriz = consulta_string($sql_sucu_matriz, 'sucu_dir_sucu', $oIfx, '');
    $sql_sucu = "select sucu_nom_sucu, sucu_dir_sucu from saesucu where sucu_cod_empr = $idEmpresa and sucu_cod_sucu = $idSucursal ";
    $emprce = "select empr_num_resu from saeempr where empr_cod_empr=$idEmpresa";
    $contribuyente = consulta_string($emprce, 'empr_num_resu', $oIfx, '');
    if ($oIfx->Query($sql_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
                $sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');

                //$logo .= '<tr><td align="center" style="font-size: 12px">' . $sucu_nom_sucu . ': ' . htmlentities($sucu_dir_sucu) . '</td></tr>';
                //$logo .= '<tr><td align="center" style="font-size: 13px">SUCURSAL : ' . $sucu_nom_sucu . '</td></tr>';

                ///$logo .= '<tr><td style="position:top; width:50px;"><h4>Direccion Matriz:</h4></td></tr><br>';
                //$logo .= '<tr><td>' . htmlentities($dirMatriz) . '</td></tr>';
                if ($contsucu > 1) {
                    $logo .= '<tr><td align="left" style="font-size: 13px;"><b>Sucursal:</b> ' . $sucu_nom_sucu . '</td></tr>';

                    $logo .= '<tr><td align="left" style="font-size: 13px;" width="500" ><b>Direcci&oacute;n Sucursal:</b>' . $sucu_dir_sucu . '</td></tr>';
                }
            } while ($oIfx->SiguienteRegistro());
        }
    }
    //$logo .= '<tr><td>&nbsp;</td></tr>';

    //DATOS ADICIONALES
    $logo .= '<tr><td align="left" style="font-size: 13px;"><b>Correo:</b> ' .  $empr_mai_empr . '</td></tr>';

    $logo .= '<tr><td align="left" style="font-size: 13px;"><b>Tel&eacute;fono:</b> ' . $sucu_telf_secu . '</td></tr>';


    if ($empr_tip_empr == 'S') {
        $logo .= '<tr><td align="left" style="font-size: 12px;"><b>Contribuyente Especial #:</b> ' . $empr_num_resu . '</td></tr>';
    } //fin if*/
    /*if($empr_conta_sn=='SI'){
        $logo .= '<tr><td align="center" style="font-size: 12px;"><b>Contribuyente Especial #:</b> '.$contribuyente.'</td></tr>';
    }*/

    if (!empty($empr_ac2_empr)) {
        $empr_ac2_empr = '<b>Agente de Retención Resolución Nro:</b> ' . $empr_ac2_empr;
    }
    /*else{
        $empr_ac2_empr ='<b>AGENTE DE RETENCI&Oacute;N:</b> NO';
    }*/


    if ($empr_conta_sn == 'SI') {
        if ($empr_sn_conta == 'S') {
            $empr_sn_conta = 'SI';
        } else {
            $empr_sn_conta = 'NO';
        }
        $logo .= '<tr><td align="left" style="font-size: 12px;"><b>Obligado a llevar Contabilidad :</b> ' . $empr_sn_conta . '</td></tr>';
    }
    //$logo .= '<tr><td>&nbsp;</td></tr>';
    if ($empr_rimp_sn == "S") {
        $logo .= '<tr><td align="left" style="font-size: 12px;"><b>CONTRIBUYENTE R&Eacute;GIMEN RIMPE</b></td></tr>';
    }
    $logo .= '<tr><td align="left" style="font-size: 12px;" width="500">' . $empr_ac2_empr . '</td></tr>';

    if (!empty($empr_cm2_empr)) {
        $logo .= '<tr><td align="left" style="font-size: 12px;" ><b>' . $empr_cm2_empr . '</b></td></tr>';
    }

    $logo .= '<tr><td>&nbsp;</td></tr>';
    $logo .= ' </table>';
    $logo .= '</td>';


    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'json_cab_pedi' AND TABLE_NAME = 'apipedi' AND TABLE_SCHEMA='apicomercial'";
    $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);


    $sqlFac = "select * from saefact where fact_cod_fact = $id;";

    if ($oIfx->Query($sqlFac)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $fact_nse_fact = $oIfx->f('fact_nse_fact');
                $fact_num_preimp = $oIfx->f('fact_num_preimp');
                $fact_auto_sri = $oIfx->f('fact_auto_sri');
                $fact_fech_sri = $oIfx->f('fact_fech_sri');
                $fact_nom_cliente = $oIfx->f('fact_nom_cliente');
                $fact_fech_fact = fecha_mysql_func($oIfx->f('fact_fech_fact'));
                $fact_ruc_clie = $oIfx->f('fact_ruc_clie');
                $fact_tlf_cliente = $oIfx->f('fact_tlf_cliente');
                $fact_dir_clie = htmlentities($oIfx->f('fact_dir_clie'));
                $fact_email_clpv = str_replace(' ', '', $oIfx->f("fact_email_clpv"));
                $fact_con_miva = $oIfx->f('fact_con_miva');
                $fact_sin_miva = $oIfx->f('fact_sin_miva');
                $fact_tot_fact = $oIfx->f('fact_tot_fact');
                $fact_iva = $oIfx->f('fact_iva');
                $fact_ice = $oIfx->f('fact_ice');
                $fact_fina_fact = $oIfx->f('fact_fina_fact');
                $fact_clav_sri  = $oIfx->f('fact_clav_sri');
                $fact_dsg_valo  = $oIfx->f('fact_dsg_valo');
                $fact_cm1_fact  = trim($oIfx->f("fact_cm1_fact"));
                $fact_cm2_fact  = $oIfx->f("fact_cm2_fact");
                $fact_cod_pac  = $oIfx->f("fact_cod_pac");

                $fact_cm4_fact  = $oIfx->f("fact_cm4_fact");
                $orden_compra   = $oIfx->f("fact_opc_fact");
                $fact_cod_clpv  = $oIfx->f("fact_cod_clpv");
                $fact_cod_ccli  = $oIfx->f("fact_cod_ccli");
                $fact_dia_plazo = $oIfx->f("fact_dia_fact");
                $fact_fech_venc = fecha_mysql_func($oIfx->f("fact_fech_venc"));
                $fact_cm3_fact  = $oIfx->f("fact_cm3_fact");
                $fact_cod_contr = $oIfx->f("fact_cod_contr");

                if (strlen($fact_auto_sri) == 0 || $fact_auto_sri == NULL) {
                    $fact_auto_sri = "<strong style='color:red;'>(AUTORIZACION REQUERIDA)</strong>";
                }

                if (strlen($fact_fech_sri) > 0 || $fact_fech_sri != NULL) {
                    $fact_fech_sri = new DateTime($fact_fech_sri);
                    $fact_fech_sri = $fact_fech_sri->format('d-m-Y H:i:s');
                } else {
                    $fact_fech_sri = "<strong style='color:red;'>(AUTORIZACION REQUERIDA)</strong>";
                }


                $id_usuario_w = $oIfx->f("fact_user_web");
                $sql = "select usuario_user, usuario_nombre, usuario_apellido  from comercial.usuario where usuario_id = '$id_usuario_w' ";
                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        $usuario = $codigo = $oCon->f('usuario_nombre') . ' ' . $oCon->f('usuario_apellido');
                    }
                }

                $fact_tip_vent = $oIfx->f("fact_tip_vent");
                $sql = "select tcmp_cod_tcmp, tcmp_des_tcmp from saetcmp where tcmp_cod_tcmp = '$fact_tip_vent';";
                $tipo_text_fac = consulta_string($sql, 'tcmp_des_tcmp', $oCon, 'FACTURA');


                $logo .= '<b><td style="width: 45%; border: 1px solid black; border-radius: 5px;" align="center">';
                $logo .= ' <table align="center" style="font-size: 15px;">';
                $logo .= ' <tr>';
                $logo .= '<td style="border-bottom: 1px inset black;" width="400">' . $tipo_text_fac . '</td>';
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
                $logo .= '<td>AUTORIZACION:</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= '<td>' . $fact_auto_sri . '</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= '<td style="border-top:1">FECHA AUTORIZACION:</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= '<td>' . $fact_fech_sri . '</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= '<td style="border-top:1" >AMBIENTE:</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= ' <td>' . $ambiente_sri . '</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= ' <td style="border-top:1" >EMISION:</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= ' <td>' . $emision_sri . '</td>';
                $logo .= ' </tr>';

                //verifica si existe clave de acceso
                if ($fact_clav_sri != '') {
                    $nombArch = $fact_nse_fact . $fact_num_preimp;
                    //$nombArch = '0112201401179141325300110010010000048101234567818';
                    $rutaCodi = DIR_FACTELEC . 'Include/archivos/' . $nombArch . '.gif';

                    new barCodeGenrator($fact_clav_sri, 1, $rutaCodi, 450, 100, true);

                    $logo .= '<tr>';
                    $logo .= '<td colspan=2 align="center" style="font-size: 12px;">CLAVE DE ACCESO</td>';
                    $logo .= '</tr>';
                    $logo .= '<tr>';
                    $logo .= '<td colspan=2 align="center"> <img width="400px;" src="' . $rutaCodi . '"/></td>';
                    $logo .= '</tr>';
                }

                $logo .= ' </table>';
                $logo .= '</td></b>';
                $logo .= '</tr>';
                $logo .= '</table>';
                $logo .= ' <br>';

                $cliente .= ' <table style="width: 100%; border:1px solid black; border-radius: 5px; padding: 2px; font-size: 15x;">';
                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 10%;">CLIENTE:</td></b>';
                $cliente .= ' <td style="width: 40%; ">' . utf8_decode($fact_nom_cliente) . '</td>';
                $cliente .= ' <b><td style="width: 13%; ">FECHA EMISION:</td></b>';
                $cliente .= ' <td style="width: 37% ">' . $fact_fech_fact . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 10% ">RUC:</td></b>';
                $cliente .= ' <td style="width: 40% ">' . $fact_ruc_clie . '</td>';
                $cliente .= ' <b><td style="width: 13% ">TELEFONO:</td></b>';
                $cliente .= ' <td style="width: 37% ">' . $fact_tlf_cliente . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 10% ">DIRECCION:</td></b>';
                $cliente .= ' <td style="width: 40% ">' . utf8_decode($fact_dir_clie) . '</td>';
                $cliente .= ' <b><td style="width: 13% ">EMAIL:</td></b>';
                $cliente .= ' <td style="width: 37% ">' . $fact_email_clpv . '</td>';
                $cliente .= ' </tr>';

                


                // -------------------------------------------------------------------------
                // campos fedexport solucion temporal borrar
                // -------------------------------------------------------------------------
                if ($ruc_empr == 1790312143001 || $ruc_empr == 1791870158001) {

                    if (!empty($fact_cod_pac)) {
                        if (empty($orden_compra)) {
                            $sql_oc = "SELECT orden_compra from contrato_clpv where id_clpv = $fact_cod_clpv and
                                        id_empresa = $idEmpresa";
                            $orden_compra = consulta_string_func($sql_oc, 'orden_compra', $oIfxA, '');
                        }
                    }


                    $cliente .= ' <tr>';
                    $cliente .= ' <b><td style="width: 10% "></td></b>';
                    $cliente .= ' <td style="width: 48% "></td>';
                    $cliente .= ' <b><td style="width: 13% ">ORDEN DE COMPRA:</td></b>';
                    $cliente .= ' <td style="width: 29% ">' . $orden_compra . '</td>';
                    $cliente .= ' </tr>';
                }
                // -------------------------------------------------------------------------
                // FIn campos fedexport solucion temporar borrar
                // -------------------------------------------------------------------------


                $cliente .= ' </table>';

                $cliente .= ' <br>';
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $oIfx->Free();

    $sqlDeta = "select * from saedfac where dfac_cod_fact = $id and 
                dfac_cod_sucu = $idSucursal and 
                dfac_cod_empr = $idEmpresa  ";


    $deta .= ' <table style="width: 100%; font-size: 12px; border-radius: 5px; border-collapse: collapse;" border="1">';
    $deta .= ' <tr>';
    $deta .= ' <b> <td style="width: 10%;" align="center">Cod. Principal</td> </b>';
    $deta .= ' <b> <td style="width: 10%;" align="center">Cod. Auxiliar</td> </b>';

    $deta .= ' <b> <td style="width:  8%;" align="center">Cant.</td> </b>';
    $deta .= ' <b> <td style="width: 16%;" align="center">Descripción</td> </b>';
    $deta .= ' <b> <td style="width: 6%;" align="center">Detalle Adicional</td> </b>';
    $deta .= ' <b> <td style="width: 12%;" align="center">Detalle Adicional</td> </b>';
    $deta .= ' <b> <td style="width: 12%;" align="center">Detalle Adicional</td> </b>';
    

    $deta .= ' <b> <td style="width:  8%;" align="center">Precio Unitario</td> </b>';
    $deta .= ' <b> <td style="width:  8%;" align="center">Descuento</td> </b>';
    $deta .= ' <b> <td style="width: 10%;" align="center">Precio Total</td> </b>';
    $deta .= ' </tr>';
    $total_cant = 0;

    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            $porcIva = '';
            do {
                $dfac_cod_prod = $oIfx->f('dfac_cod_prod');
                $dfac_cod_lote = $oIfx->f('dfac_cod_lote');
                $dfac_nom_prod = $oIfx->f('dfac_nom_prod');
                $dfac_cant_dfac = $oIfx->f('dfac_cant_dfac');
                $dfac_cod_unid = $oIfx->f('dfac_cod_unid');
                $total_cant += $dfac_cant_dfac;
                $dfac_precio_dfac = $oIfx->f('dfac_precio_dfac');
                $dfac_des1_dfac = $oIfx->f('dfac_des1_dfac');
                $dfac_des2_dfac = $oIfx->f('dfac_des2_dfac');
                $dfac_por_dsg = $oIfx->f('dfac_por_dsg');
                $dfac_mont_total = $oIfx->f('dfac_mont_total');
                $dfac_num_comp = $oIfx->f('dfac_tip_dfac');
                $dfac_por_iva = $oIfx->f('dfac_por_iva');
                $dfac_det_dfac = $oIfx->f('dfac_det_dfac');
                $dfac_cant_conv = $oIfx->f('dfac_cant_conv');
                $dfac_cp2_dfac = $oIfx->f('dfac_cp2_dfac');
                

                if ($dfac_por_iva > 0) {
                    $porcIva = $dfac_por_iva;
                }

                //CAMPO APLICACION - ACTUALMENTE SOLO ES UTILIZADO POR LA EMPRESA LUBAMAQUI
                $sql_prod = "SELECT  prod_cod_alterno from saeprod where prod_cod_prod='$dfac_cod_prod' and prod_cod_empr=$idEmpresa";
                $prod_cod_alt = trim(consulta_string_func($sql_prod, 'prod_cod_alterno', $oIfxA, ''));
                



                $sql = "SELECT empr_menu_sucu from saeempr where empr_cod_empr=$idEmpresa";
                $detalle_sn = consulta_string_func($sql, 'empr_menu_sucu', $oIfxA, '');


                if ($detalle_sn == 'S') {
                    $sql_prod = "SELECT prod_det_prod from saeprod where prod_cod_prod='$dfac_cod_prod'";
                    //echo $sql;exit;
                    $dfac_nom_prod = consulta_string_func($sql_prod, 'prod_det_prod', $oIfxA, '');
                    $dfac_det_dfac = $dfac_nom_prod;
                }

                //echo $dfac_nom_prod;exit;



                //echo $detalle_sn;exit;


                //echo $dfac_nom_prod;exit;

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

                $dfac_det_dfac = str_replace(',', ', ', $dfac_det_dfac);
                $dfac_nom_prod = str_replace(',', ', ', $dfac_nom_prod);


                $deta .= ' <tr>';
                $deta .= ' <td style="width: 10%;" align="center">' . $prod_cod_alt . '</td>';
                $deta .= ' <td style="width: 10%;" align="center">' . $dfac_cod_prod . '</td>';
                $deta .= ' <td style="width: 8%;" align="center">' . number_format($dfac_cant_dfac, 4, '.', ',') . '</td>';
                $deta .= ' <td style="width: 16%;">' . $dfac_nom_prod . '</td>';
                $deta .= ' <td style="width: 6%;" align="center">' . $array_unidad[$dfac_cod_unid] . '</td>';
                $deta .= ' <td style="width: 12%;">' . $dfac_det_dfac . '</td>';
                $deta .= ' <td style="width: 12%;">' . $dfac_cp2_dfac . '</td>';
                
                $total = number_format($dfac_cant_dfac * $dfac_precio_dfac, 4, '.', ',');
                if (empty($dfac_des1_dfac)) {
                    $dfac_des1_dfac = 0;
                }
                $descuento = $total * $dfac_des1_dfac / 100;

                /*$total_con_des=$total-$descuento;

                if($total>=$dfac_mont_total){
                    $total_dfac=$total_con_des;
                }else{
                    $total_dfac=$dfac_mont_total;
                }*/

                if ($para_conv_sn == 'S') {
                    if (round($dfac_cant_conv, 2) > 0) {
                        $dfac_cant_dfac = $dfac_cant_conv;
                        $dfac_precio_dfac = $dfac_mont_total / $dfac_cant_dfac;
                    }
                }



                $deta .= ' <td style="width: 8%;" align="right">' . number_format($dfac_precio_dfac, 4, '.', ',') . '</td>';
                $deta .= ' <td style="width: 8%;" align="right">' . number_format($descuento, 4, '.', ',') . '</td>';
                $deta .= ' <td style="width: 10%;" align="right">' . number_format($dfac_mont_total, 4, '.', ',') . '</td>';
                $deta .= ' </tr>';
            } while ($oIfx->SiguienteRegistro());
            if ($ruc_empr == '0190020304001' || $ruc_empr == '0190314294001') {
                $deta .= ' <tr>';
                $deta .= ' <td  colspan="2" align="right">TOTAL CANT:</td>';
                $deta .= ' <td  align="right">' . number_format($total_cant, 4, '.', ',') . '</td>';
                $deta .= ' <td></td>';
                $deta .= ' <td></td>';
                $deta .= ' <td></td>';
                $deta .= ' </tr>';
            }
        }
    }

    $deta .= ' </table>';


    
    

    $totales .= '<table style="width: 100%;  font-size: 13px; margin-top: 3px;"  align="center">';

    $totales .= '<tr>
    <td  style="width: 65%;" valign="top" >';

    $totales .= ' <table style="width:100%; font-size: 13px; border-radius: 5px; border-collapse: collapse;"  border=1 >';
    $totales .= ' <tr>';
    $totales .= ' <td style="width:100%;" ><b>Información Adicional</b><br>'.$dfac_det_dfac.'<br>' . $fact_cm1_fact . '</td>';
    $totales .= ' </tr>';
    $totales .= ' </table>';

    $totales .= '</td>';


    $totales .= '<td style="width: 35%; align="left" valign="top">';

    $totales .= ' <table style="width: 100%; font-size: 13px; border-radius: 5px; border-collapse: collapse;"  border=1 align="right">';



    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 72%;">SUBTOTAL:</td> </b>';
    $totales .= ' <td style="width: 28%;" align="right">' . number_format($fact_con_miva + $fact_sin_miva + $fact_dsg_valo, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 72%;">DESCUENTO:</td> </b>';
    $totales .= ' <td style="width: 28%;" align="right">' . number_format($fact_dsg_valo, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 72%;">SUBTOTAL SIN IMPUESTOS:</td> </b>';
    $totales .= ' <td style="width: 28%;" align="right">' . number_format($fact_con_miva + $fact_sin_miva - $fact_ice, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 72%;">IVA ' . round($porcIva) . '%:</td> </b>';
    $totales .= ' <td align="right" style="width: 28%;">' . number_format($fact_iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 72%;">ICE:</td> </b>';
    $totales .= ' <td style="width: 28%;" align="right">' . number_format($fact_ice, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 72%;">IRBP:</td> </b>';
    $totales .= ' <td style="width: 28%;" align="right">' . number_format($fact_val_irbp, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 72%;">INTERES:</td> </b>';
    $totales .= ' <td style="width: 28%;" align="right">' . number_format($fact_fina_fact, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 72%;">TOTAL:</td> </b>';
    $fact_tot_fact = $fact_con_miva + $fact_iva + $fact_sin_miva + $fact_val_irbp + $fact_fina_fact;
    $totales .= ' <td style="width: 28%;" align="right">' . number_format($fact_tot_fact, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    $totales .= ' </table>';


    $totales .= '</td>
    </tr>';
    $totales .= '</table>';



    $sqltmp = '';
    if (!empty($fact_cod_contr)) {
        $sqltmp = "id_contrato = $fact_cod_contr AND";
    }

    $total = number_format($fact_con_miva + $fact_sin_miva + $fact_iva + $fact_fle_fact + $fact_otr_fact + $fact_fin_fact - $totalDescuento, 2, '.', '');
    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetras($total, 'dolar'));

    //query forma de pago
    $sqlFPago = "select fp.fpag_cod_fpagop, fx.fxfp_val_fxfp, fx.fxfp_num_dias,
				fpg.fpagop_des_fpagop
				from saefact f, saefxfp fx, saefpag fp, saefpagop fpg
				where 
                f.fact_cod_fact = fx.fxfp_cod_fact and
				fp.fpag_cod_fpag = fx.fxfp_cod_fpag and
                f.fact_cod_empr = fpg.fpagop_cod_empr and
				fp.fpag_cod_fpagop = fpg.fpagop_cod_fpagop and
				f.fact_cod_empr = $idEmpresa and
				f.fact_cod_sucu = $idSucursal and
				f.fact_cod_fact = $id order by fx.fxfp_cod_fxfp";
    if ($oIfx->Query($sqlFPago)) {
        if ($oIfx->NumFilas() > 0) {
            $tablePago .= '<table style="width: 60%; border-collapse: collapse; margin-top: 10px;" border="1">';
            $tablePago .= '<tr>';
            $tablePago .= '<th style="width: 70%;">FORMA PAGO</th>';
            $tablePago .= '<th style="width: 30%;">VALOR</th>';
            $tablePago .= '<th style="width: 30%;">PLAZO</th>';
            $tablePago .= '<th style="width: 30%;">UNIDAD</th>';
            $tablePago .= '</tr>';
            do {
                $fpag_cod_fpagop    = $oIfx->f('fpag_cod_fpagop');
                $fxfp_val_fxfp      = $oIfx->f('fxfp_val_fxfp');
                $fxfp_num_dias      = $oIfx->f('fxfp_num_dias');
                $fpagop_des_fpagop  = $oIfx->f('fpagop_des_fpagop');

                $tablePago .= '<tr>';
                $tablePago .= '<td style="width: 70%;">' . $fpag_cod_fpagop . ' ' . htmlentities($fpagop_des_fpagop) . '</td>';
                $tablePago .= '<td style="width: 30%;" align="right">' . number_format($fxfp_val_fxfp, 2, '.', '') . '</td>';
                $tablePago .= '<td style="width: 30%;" align="right">' . round($fxfp_num_dias) . '</td>';
                $tablePago .= '<td style="width: 30%;" align="right">dias</td>';

                $tablePago .= '</tr>';
            } while ($oIfx->SiguienteRegistro());
            $tablePago .= '</table>';
        }
    }
    $oIfx->Free();

    if (!empty($empr_cm1_empr)) {
        $msjCliente = $empr_cm1_empr;

        $msjCliente = str_replace('&&&correo&&&', $empr_mai_empr, $msjCliente);

        // LEEYNDA
        $tableLeyenda .= '<br /> <br /> <br />';
        $tableLeyenda .= '<table style="width: 98%; border-collapse: collapse; margin-top: 10px;" border="0">';
        $tableLeyenda .= '<tr>';
        $tableLeyenda .= '<td style="width: 98%;" align="center">' . $msjCliente . '</td>';
        $tableLeyenda .= '</tr>';
        $tableLeyenda .= '</table>';
    }

    if ($obfact == 'S') {

        $tableLeyenda .= '<table style="width: 98%; border-collapse: collapse; margin-top: 0px;" border="0">';
        $tableLeyenda .= '<tr>';
        $tableLeyenda .= '<td style="width: 98%;" align="left">' . $obdetfact . '</td>';
        $tableLeyenda .= '</tr>';
        $tableLeyenda .= '</table>';
    }


    if ($empr_cta_sn == 'S') {
        $tableLeyenda .= $empr_det_cta;
    }

    if ($empr_rinf_sn == 'S') {
        $tableLeyenda .= $empr_det_rinf;
    }


    $legend = '<page_footer>
            <div style="text-align:center;color: #6B6565; background-color: transparent;">Este comprobante electronico ha sido generado a traves de Sisconti S.A. - Facturacion Electronica<br>www.sisconti.com.ec</div>
    </page_footer>';

    $documento .= '<page backimgw="70%" backtop="10mm" backbottom="10mm" backleft="20mm" backright="10mm">';
    $documento .= $logo . $cliente . $deta . $totales . $tablePago . $tableLeyenda;
    $documento .= $legend;
    $documento .= '</page>';



    //file_put_contents("C:/prueba/documento.html", $documento);

    $html2pdf = new HTML2PDF('P', 'A3', 'fr');
    $html2pdf->WriteHTML($documento);
    $ruta = DIR_FACTELEC . 'Include/archivos/' . $nombre_archivo . '.pdf';
    $html2pdf->Output($ruta, 'F');
    $rutaPdf = $ruta;

    return $documento;
}
