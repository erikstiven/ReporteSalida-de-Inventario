<?

function reporte_factura_export_eng($id = '', $nombre_archivo = '', $idSucursal ='', &$rutaPdf = '') {
    global $DSN_Ifx, $DSN;

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

    //CAMPOS APROBACION
    $sqlgein="SELECT count(*) as conteo
     FROM INFORMATION_SCHEMA.COLUMNS
     WHERE COLUMN_NAME = 'empr_fac_empr' AND TABLE_NAME = 'saeempr'";
    $ctralter=consulta_string($sqlgein,'conteo',$oCon,0);
    if($ctralter==0){
        $sqlalter="alter table saeempr add  empr_fac_empr varchar(1)";
        $oCon->QueryT($sqlalter);
    }


    $sqlgein="SELECT count(*) as conteo
     FROM INFORMATION_SCHEMA.COLUMNS
     WHERE COLUMN_NAME = 'empr_det_fac' AND TABLE_NAME = 'saeempr'";
    $ctralter=consulta_string($sqlgein,'conteo',$oCon,0);
    if($ctralter==0){
        $sqlalter="alter table saeempr add  empr_det_fac text";
        $oCon->QueryT($sqlalter);
    }

    //VALIDAICON DETALLE

    $sqlfa="select para_dfa_para from saepara where para_cod_empr= $idEmpresa and para_cod_sucu=$idSucursal";
    $para_dfa_para=consulta_string($sqlfa,'para_dfa_para',$oCon,'');

    $sqlfa="select empr_fac_empr, empr_det_fac from saeempr where empr_cod_empr=$idEmpresa ";
    $obfact=consulta_string($sqlfa,'empr_fac_empr',$oCon,'');
    $obdetfact=consulta_string($sqlfa,'empr_det_fac',$oCon,'');

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
        }
    }
    $oIfx->Free();

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



// $path_logo_img = DIR_FACTELEC . 'imagenes/logos/' . $path_img[$count];
    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];
    if (file_exists($path_logo_img)) {
        //echo "El fichero $nombre_fichero existe";
    } else {
        //echo "El fichero $nombre_fichero no existe";
        $path_logo_img = "https://turbologo.com/articles/wp-content/uploads/2019/05/no-logo.png";
    }
    
    $font_family = 'Monterchi Serif Trial';
    $logo .= '<table style="width: 100%; margin: 0px;">';
    $logo .= '<tr>';
    $logo .= '<td align="center" style="width: 55%; border:0px solid black; border-radius: 5px; margin: 0px;">';
    $logo .= '<table width: 55%; valign="center" style="margin: 0px;">';
    $logo .= '<tr><td style="margin-top: 0px;"><img width="250px;"  src="' . $path_logo_img . '"></td></tr>';
    $logo .= '<tr><td>&nbsp;</td></tr>';
    $logo .= '<tr><td align="left" style="font-size: 15px;" width="300"><b>' . $razonSocial . '</b></td></tr>';
    //$logo .= '<tr><td>&nbsp;</td></tr>';
    $logo .= '<tr><td align="left" style="font-size: 13px;"><b>RUC:</b> ' . $ruc_empr . '</td></tr>';
    //$logo .= '<tr><td>&nbsp;</td></tr>';
    //$logo .= '<tr><td>&nbsp;</td></tr>';
    $logo .= '<tr><td align="left" style="font-size: 13px;" width="500"><b>Address:</b> ' . $dirMatriz . '</td></tr>';
    //$logo .= '<tr><td>&nbsp;</td></tr>';


    //selecciona sucursales y direcciones
    $sql_sucu_matriz = "select sucu_dir_sucu from saesucu where sucu_nom_sucu ='MATRIZ'";
    $matriz = consulta_string($sql_sucu_matriz, 'sucu_dir_sucu', $oIfx, '');
    $sql_sucu = "select sucu_nom_sucu, sucu_dir_sucu from saesucu where sucu_cod_empr = $idEmpresa and sucu_cod_sucu = $idSucursal ";
    $emprce="select empr_num_resu from saeempr where empr_cod_empr=$idEmpresa";
    $contribuyente= consulta_string($emprce, 'empr_num_resu', $oIfx, '');
    if ($oIfx->Query($sql_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
                $sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');

                //$logo .= '<tr><td align="center" style="font-size: 12px">' . $sucu_nom_sucu . ': ' . htmlentities($sucu_dir_sucu) . '</td></tr>';
                //$logo .= '<tr><td align="center" style="font-size: 13px">SUCURSAL : ' . $sucu_nom_sucu . '</td></tr>';

                ///$logo .= '<tr><td style="position:top; width:50px;"><h4>Direccion Matriz:</h4></td></tr><br>';
                //$logo .= '<tr><td>' . htmlentities($dirMatriz) . '</td></tr>';
                if( $contsucu>1){
                    //$logo .= '<tr><td align="left" style="font-size: 13px;"><b>Branch:</b> ' . $sucu_nom_sucu . '</td></tr>';

                    //$logo .= '<tr><td align="left" style="font-size: 13px;" width="500" ><b>Direcci&oacute;n Sucursal:</b>' . $sucu_dir_sucu.'</td></tr>';
                }





            } while ($oIfx->SiguienteRegistro());
        }
    }
    //$logo .= '<tr><td>&nbsp;</td></tr>';

    //DATOS ADICIONALES
    $logo .= '<tr><td align="left" style="font-size: 13px;"><b>Email:</b> ' .  $empr_mai_empr . '</td></tr>';

    $logo .= '<tr><td align="left" style="font-size: 13px;"><b>Phone Number:</b> ' . $sucu_telf_secu . '</td></tr>';


    if ($empr_tip_empr == 'S') {
       // $logo .= '<tr><td align="left" style="font-size: 12px;"><b>Contribuyente Especial #:</b> ' . $empr_num_resu . '</td></tr>';
    }//fin if*/
    /*if($empr_conta_sn=='SI'){
        $logo .= '<tr><td align="center" style="font-size: 12px;"><b>Contribuyente Especial #:</b> '.$contribuyente.'</td></tr>';
    }*/

    if(!empty($empr_ac2_empr)){
        $empr_ac2_empr = '<b>Withholding Agent #:</b> '.$empr_ac2_empr;
    }
    /*else{
        $empr_ac2_empr ='<b>AGENTE DE RETENCI&Oacute;N:</b> NO';
    }*/


    if($empr_conta_sn=='SI'){
        $logo .= '<tr><td align="left" style="font-size: 12px;"><b>Forced to keep accounts :</b> ' . $empr_conta_sn . '<br><b>Usual exporter of goods</b></td></tr>';
    }
    //$logo .= '<tr><td>&nbsp;</td></tr>';
    if($empr_rimp_sn=="S"){
        $logo .= '<tr><td align="left" style="font-size: 12px;"><b>CONTRIBUYENTE R&Eacute;GIMEN RIMPE</b></td></tr>';
    }
    $logo .= '<tr><td align="left" style="font-size: 12px;">'.$empr_ac2_empr.'</td></tr>';

    $logo .= '<tr><td>&nbsp;</td></tr>';
    $logo .= ' </table>';
    $logo .= '</td>';

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
                $fact_clav_sri  = $oIfx->f('fact_clav_sri');
                $fact_dsg_valo  = $oIfx->f('fact_dsg_valo');
                $fact_cm1_fact  = trim($oIfx->f("fact_cm1_fact"));
                $fact_cm2_fact  = $oIfx->f("fact_cm2_fact");
                $fact_cm4_fact  = $oIfx->f("fact_cm4_fact");
                $orden_compra   = $oIfx->f("fact_opc_fact");
                $fact_cod_clpv  = $oIfx->f("fact_cod_clpv");
                $fact_cod_ccli  = $oIfx->f("fact_cod_ccli");
                $fact_dia_plazo = $oIfx->f("fact_dia_fact");
                $fact_fech_venc = fecha_mysql_func($oIfx->f("fact_fech_venc"));
                $fact_cm3_fact  = $oIfx->f("fact_cm3_fact");
                $fact_cod_contr = $oIfx->f("fact_cod_contr");

                $fact_term_expor = $oIfx->f('fact_term_expor');
                if(empty($fact_term_expor)){
                    $fact_term_expor='NULL';
                }
                $fact_cod_pais = $oIfx->f('fact_cod_pais');
                $fact_cod_ffue = $oIfx->f('fact_cod_ffue');
                if(empty($fact_cod_ffue)){
                    $fact_cod_ffue='NULL';
                }
                $fact_fec_emb = $oIfx->f('fact_fec_emb');
                $fact_prto_emb = $oIfx->f('fact_prto_emb');
                if(empty($fact_prto_emb)){
                    $fact_prto_emb='NULL';
                }
                $fact_prto_dest = $oIfx->f('fact_prto_dest');
                if(empty($fact_prto_dest)){
                    $fact_prto_dest='NULL';
                }
                $fact_fle_fact = $oIfx->f('fact_fle_fact');
                $fact_otr_fact = $oIfx->f('fact_otr_fact');
                $fact_fin_fact = $oIfx->f('fact_fin_fact');

                $id_usuario_w = $oIfx->f("fact_user_web");
                $sql = "select usuario_user, usuario_nombre, usuario_apellido  from comercial.usuario where usuario_id = '$id_usuario_w' ";
                if($oCon->Query($sql)){
                    if($oCon->NumFilas() > 0){
                        $usuario=$codigo = $oCon->f('usuario_nombre').' '.$oCon->f('usuario_apellido');
                    }
                }

                //PAIS
                if ($fact_cod_pais == '') {
                    $fact_cod_pais = 0;
                }
                $sql_pais = "select pais_des_pais from saepais where pais_cod_pais = $fact_cod_pais";
                $pais = consulta_string_func($sql_pais, 'pais_des_pais', $oIfxA, '--');

                //EMBARQUE ORIGEN
                $sql_emb_1 = "select prto_des_prto from saeprto where prto_cod_prto = $fact_prto_emb";
                $prto_1 = consulta_string_func($sql_emb_1, 'prto_des_prto', $oIfxA, '--');

                //EMBARQUE DESTINO
                $sql_emb_2 = "select prto_des_prto from saeprto where prto_cod_prto = $fact_prto_dest";
                $prto_2 = consulta_string_func($sql_emb_2, 'prto_des_prto', $oIfxA, '--');

                // Icoterm
                $sql_tem = "select id, nombre from comercial.incoterm where id = $fact_term_expor;";
                $term = consulta_string_func($sql_tem, 'nombre', $oIfxA, '');

                // DAE
                $sql_fue = "select ffue_fue_ffue    from saeffue where
                                ffue_cod_ffue  = $fact_cod_ffue ";
                $dae = consulta_string_func($sql_fue, 'ffue_fue_ffue', $oIfxA, '');


                $logo .= '<b><td style="width: 45%; border: 0px solid black; border-radius: 5px;" align="right">';
                $logo .= ' <table align="center" style="font-size: 15px;">';
                $logo .= ' <tr>';
                $logo .= '<td style="border-bottom: 0px inset black; font-size: 20px !important">INVOICE</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= '<td>SERIE:</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= '<td>' . substr($fact_nse_fact, 0, 3) . '-' . substr($fact_nse_fact, 3, 6) . ' <label style="color: red">' . $fact_num_preimp . '</label></td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= '<td>AUTORIZATION:</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= '<td>' . $fact_auto_sri . '</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= '<td >AUTORIZATION DATE:</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= '<td>' . $fact_fech_sri . '</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= '<td  >ENVIROMENT:</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= ' <td>' . $ambiente_sri . '</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= ' <td  >ISSUE:</td>';
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
                    $logo .= '<td colspan=2 align="center" style="font-size: 12px;">PASSWORD</td>';
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
                $logo .= ' <hr>';
                $logo .= ' <br>';
                $logo .= ' <br>';

                $cliente .= ' <table style="width: 100%; border:0px solid white; border-radius: 5px; padding: 0px; font-size: 15x;">';
                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 13%;">CLIENT:</td></b>';
                $cliente .= ' <td style="width: 45%; ">' . utf8_decode($fact_nom_cliente) . '</td>';
                $cliente .= ' <b><td style="width: 15%; ">DATE OF ISSUE:</td></b>';
                $cliente .= ' <td style="width: 27% ">' . $fact_fech_fact . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 13% ">RUC:</td></b>';
                $cliente .= ' <td style="width: 45% ">' . $fact_ruc_clie . '</td>';
                $cliente .= ' <b><td style="width: 15% ">PHONE NUMBER:</td></b>';
                $cliente .= ' <td style="width: 27% ">' . $fact_tlf_cliente . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 13% ">ADRESS:</td></b>';
                $cliente .= ' <td style="width: 45% ">' . utf8_decode($fact_dir_clie) . '</td>';
                $cliente .= ' <b><td style="width: 15% ">EMAIL:</td></b>';
                $cliente .= ' <td style="width: 27% ">' . $fact_email_clpv . '</td>';
                $cliente .= ' </tr>';


                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 13% ">COUNTRY OF ORIGIN: </td></b>';
                $cliente .= ' <td style="width: 45% ">ECUADOR</td>';
                $cliente .= ' <b><td style="width: 15% "> PORT OF ORIGIN: </td></b>';
                $cliente .= ' <td style="width: 27% ">' . $prto_1 . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 13% ">SHIPPING DATE: </td></b>';
                $cliente .= ' <td style="width: 45% ">' . $fact_fec_emb . '</td>';
                $cliente .= ' <td style="width: 15% "></td>';
                $cliente .= ' <td style="width: 27% "></td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 13% ">DESTINATION COUNTRY: </td></b>';
                $cliente .= ' <td style="width: 45% ">' . $pais . '</td>';
                $cliente .= ' <b><td style="width: 15% ">PORT OF DESTINATION: </td></b>';
                $cliente .= ' <td style="width: 27% ">' . $prto_2 . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 13% "> INCOTERM: </td></b>';
                $cliente .= ' <td style="width: 45% ">' . $term . '</td>';
                $cliente .= ' <td style="width: 15% "></td>';
                $cliente .= ' <td style="width: 27% "></td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 13% "> DAE: </td></b>';
                $cliente .= ' <td style="width: 45% ">' . $dae . '</td>';
                $cliente .= ' <td style="width: 15% "></td>';
                $cliente .= ' <td style="width: 27% "></td>';
                $cliente .= ' </tr>';


              
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

    $deta .= ' <table style="width: 100%; font-size: 13px; border-radius: 5px; border-collapse: collapse;" border="0">';
    $deta .= ' <tr>';
    $deta .= ' <b> <td style="width: 16%; background-color: black;" align="center"><label style="color: white">CODE</label></td> </b>';
    
    $deta .= ' <b> <td style="width: 15%; background-color: black;" align="center"><label style="color: white">COD. CLIENT</label></td> </b>';
    
    $deta .= ' <b> <td style="width: 35%; background-color: black;" align="center"><label style="color: white">DESCRIPTION</label></td> </b>';
    
    $deta .= ' <b> <td style="width:  8%; background-color: black;" align="center"><label style="color: white">QTY</label></td> </b>';
    $deta .= ' <b> <td style="width:  8%; background-color: black;" align="center"><label style="color: white">P.V.P.</label></td> </b>';
    $deta .= ' <b> <td style="width:  8%; background-color: black;" align="center"><label style="color: white">DSCT</label></td> </b>';
    $deta .= ' <b> <td style="width: 10%; background-color: black;" align="center"><label style="color: white">TOTAL</label></td> </b>';
    $deta .= ' </tr>';

    $total_cant=0;

    

    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            $porcIva = '';
            do {
                $dfac_cod_prod = $oIfx->f('dfac_cod_prod');
                $dfac_nom_prod = $oIfx->f('dfac_nom_prod');
                $dfac_cant_dfac = $oIfx->f('dfac_cant_dfac');
                $total_cant+=$dfac_cant_dfac;
                $dfac_precio_dfac = $oIfx->f('dfac_precio_dfac');
                $dfac_des1_dfac = $oIfx->f('dfac_des1_dfac');
                $dfac_des2_dfac = $oIfx->f('dfac_des2_dfac');
                $dfac_por_dsg = $oIfx->f('dfac_por_dsg');
                $dfac_mont_total = $oIfx->f('dfac_mont_total');
                $dfac_num_comp = $oIfx->f('dfac_tip_dfac');
                $dfac_por_iva = $oIfx->f('dfac_por_iva');
                $dfac_det_dfac = $oIfx->f('dfac_det_dfac');

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

                $dfac_det_dfac=str_replace(',',', ',$dfac_det_dfac);
                $dfac_nom_prod=str_replace(',',', ',$dfac_nom_prod);

                $deta .= ' <tr>';
                $deta .= ' <td style="width: 16%;border-right: 0.5px solid #000">' . $dfac_cod_prod . '</td>';
                $deta .= ' <td style="width: 15%;border-right: 0.5px solid #000">' . $dfac_det_dfac . '</td>';
                $deta .= ' <td style="width: 35%;border-right: 0.5px solid #000">' . $dfac_nom_prod . '</td>';
                $deta .= ' <td style="width: 8%;border-right: 0.5px solid #000; float: right;" align="right">' . number_format($dfac_cant_dfac, 4, '.', ',') . '</td>';
                $deta .= ' <td style="width: 8%;border-right: 0.5px solid #000; float: right;" align="right">' . number_format($dfac_precio_dfac, 4, '.', ',') . '</td>';
                $deta .= ' <td style="width: 8%;border-right: 0.5px solid #000; float: right;" align="right">' . round($dfac_des1_dfac, 0) . '</td>';
                $deta .= ' <td style="width: 10%;" align="right">' . number_format($dfac_mont_total, 4, '.', ',') . '</td>';
                $deta .= ' </tr>';
            
            }while ($oIfx->SiguienteRegistro());
            $deta .= ' <tr>';
            $deta .= ' <td colspan="3" align="right">TOTAL QTY:</td>';
            $deta .= ' <td  align="right">' . number_format($total_cant, 4, '.', ',') . '</td>';
            $deta .= '<td></td>';
            $deta .= '<td></td>';
            $deta .= '<td></td>';
            $deta .= ' </tr>';
        }
    }

    $deta .= ' </table>';

    if(!empty($fact_cm1_fact)){
        $fact_cm1_fact='<b>REMARKS:</b> '.$fact_cm1_fact;
    }

    $empr_cod_pais = $_SESSION['U_PAIS_COD'];

    // IMPUESTOS POR PAIS
    $sql = "select p.impuesto, p.etiqueta, p.porcentaje from comercial.pais_etiq_imp p where
                p.pais_cod_pais = $empr_cod_pais ";
    unset($array_imp);
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {
                $impuesto      = $oCon->f('impuesto');
                $etiqueta     = $oCon->f('etiqueta');
                $porcentaje = $oCon->f('porcentaje');
                $array_imp[$impuesto] = $etiqueta;
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();


    $totales .='<table style="width: 100%;  font-size: 13px; margin-top: 3px;  align="center">';
    $totales .='<tr>
    <td  style="width: 50%; align="left"> </td>
    <td style="width: 50%; align="left">';

    $totales .= ' <table style="width: 195%; font-size: 13px; border-radius: 5px; border-collapse: collapse;"  border=1 align="right">';



    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 25%;">SUBTOTAL:</td> </b>';
    $totales .= ' <td style="width: 10%;" align="right">' . number_format($fact_con_miva+$fact_sin_miva+$fact_dsg_valo, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    /*if($fact_iva>0){
        $fact_con_iva=number_format($fact_con_miva+$fact_sin_miva+$fact_dsg_valo, 2, '.', ',');
    }
    else{
        $fact_con_iva=0.00;
    }

    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 25%;">SUBTOTAL TAX '.$porcentaje.' %:</td> </b>';
    $totales .= ' <td style="width: 10%;" align="right">'.$fact_con_iva . '</td>';
    $totales .= ' </tr>';*/
    
    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 25%;">SUBTOTAL WITHOUT TAX:</td> </b>';
    $totales .= ' <td style="width: 10%;" align="right">' . number_format($fact_con_miva+$fact_sin_miva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    
    $totales .= ' <tr>';
    $totales .= ' <b> <td>DISCOUNT:</td> </b>';
    $totales .= ' <td align="right">' . number_format($fact_dsg_valo, 2, '.', ',') . '</td>';
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
    $totales .= ' <b> <td>INTERNACIONAL FREIGHT:</td> </b>';
    $totales .= ' <td align="right">' . number_format($fact_fle_fact, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td>INTERNATIONAL INSURANCE:</td> </b>';
    $totales .= ' <td align="right">' . number_format($fact_otr_fact, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td>TRANSPORTATION COSTS:</td> </b>';
    $totales .= ' <td align="right">' . number_format($fact_fin_fact, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';


    $totales .= ' <tr>';
    $totales .= ' <b> <td>TAX '.$porcentaje.' %:</td> </b>';
    $totales .= ' <td align="right">' . number_format($fact_iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    $totales .= ' <tr>';
    $totales .= ' <b> <td>TOTAL USD:</td> </b>';
    $fact_tot_fact = $fact_con_miva + $fact_iva+ $fact_sin_miva + $fact_val_irbp + $fact_fle_fact + $fact_otr_fact + $fact_fin_fact;
    $totales .= ' <td align="right">' . number_format($fact_tot_fact, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    $totales .= ' </table>';



    $totales.='</td>
    </tr>';
    $totales .='</table>';



    $sqltmp ='';
    if (!empty($fact_cod_contr)) {
        $sqltmp ="id_contrato = $fact_cod_contr AND";
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
				f.fact_cod_fact = $id";
    if ($oIfx->Query($sqlFPago)) {
        if ($oIfx->NumFilas() > 0) {
            $tablePago .= '<table style="width: 100%; border-collapse: collapse; margin-top: 10px; text-align: center;" border="0">';
            $tablePago .= '<tr>';
            $tablePago .= '<th style="width: 80%; background-color: black"><label style="color: white">WAY TO PAY</label></th>';
            $tablePago .= '<th style="width: 1%;"></th>';
            $tablePago .= '<th style="width: 19%; background-color: black"><label style="color: white">VALUE</label></th>';
            $tablePago .= '</tr>';
            do {
                $fpag_cod_fpagop    = $oIfx->f('fpag_cod_fpagop');
                $fxfp_val_fxfp      = $oIfx->f('fxfp_val_fxfp');
                $fxfp_num_dias      = $oIfx->f('fxfp_num_dias');
                $fpagop_des_fpagop  = $oIfx->f('fpagop_des_fpagop');

                $tablePago .= '<tr>';
                $tablePago .= '<td style="width: 80%;">' . $fpag_cod_fpagop . ' ' . htmlentities($fpagop_des_fpagop) . '</td>';
                $tablePago .= '<td style="width: 1%;" align=""></td>';
                $tablePago .= '<td style="width: 19%;" align="center">' . number_format($fxfp_val_fxfp, 2, '.', '') . '</td>';
                $tablePago .= '</tr>';
            } while ($oIfx->SiguienteRegistro());
            $tablePago .= '</table>';
        }
    }
    $oIfx->Free();

    if(!empty($fact_cm1_fact)){


        $tablePago .= '<br>';
        $tablePago .= '<br>';
        $tablePago .= '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;" border="1">';
        $tablePago .= '<tr>';
        $tablePago .= '<td style="width: 100%;">'.$fact_cm1_fact.'</td>';
        $tablePago .= '</tr>'; 
        $tablePago .= '</table>';
    }

    

    if(!empty($empr_cm1_empr)){
        $msjCliente = $empr_cm1_empr;

        $msjCliente=str_replace('&&&correo&&&',$empr_mai_empr,$msjCliente);

        // LEEYNDA
        $tableLeyenda .= '<br /> <br /> <br />';
        $tableLeyenda .= '<table style="width: 98%; border-collapse: collapse; margin-top: 10px;" border="0">';
        $tableLeyenda .= '<tr>';
        $tableLeyenda .= '<td style="width: 98%;" align="center">'.$msjCliente.'</td>';
        $tableLeyenda .= '</tr>';
        $tableLeyenda .= '</table>';


    }

    if($obfact=='S'){

        $tableLeyenda .= '<table style="width: 98%; border-collapse: collapse; margin-top: 0px;" border="0">';
        $tableLeyenda .= '<tr>';
        $tableLeyenda .= '<td style="width: 98%;" align="left">'.$obdetfact.'</td>';
        $tableLeyenda .= '</tr>';
        $tableLeyenda .= '</table>';

    }

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

    $documento .= '<page backimgw="70%" backtop="10mm" backbottom="10mm" backleft="20mm" backright="10mm">';
    $documento .= $logo . $cliente . $deta . $totales . $tablePago. $tableLeyenda;
    $documento .= $legend;
    $documento .= '</page>';


    //file_put_contents("C:/prueba/documento.html", $documento);

    $html2pdf = new HTML2PDF('P', 'A3', 'fr');
    $html2pdf->WriteHTML($documento);
    $ruta = DIR_FACTELEC . 'Include/archivos/FENG_' . $nombre_archivo . '.pdf';
    $html2pdf->Output($ruta, 'F');
    $rutaPdf = $ruta;

    return $documento;
}

?>