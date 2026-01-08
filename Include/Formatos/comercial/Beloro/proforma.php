<?

    include_once('../../../../Include/config.inc.php');
    include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
    include_once(path(DIR_INCLUDE) . 'comun.lib.php');  

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
	$idSucursal = $_GET['sucursal'];
	$id = $_GET['codigo'];

	
	if(empty($idSucursal)){
		$idSucursal = $_SESSION['U_SUCURSAL'];
	}

    $tipo=$_GET['tipo'];




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



    $sql = "select pcon_mon_base, pcon_seg_mone from saepcon where pcon_cod_empr = $idEmpresa ";
    $mone_cod = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');



        $sql_mone_principal = "select * from saemone
            where mone_cod_empr = $idEmpresa
            and mone_cod_mone = $mone_cod";

        if ($oIfx->Query($sql_mone_principal)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $moneda_principal = $oIfx->f('mone_des_mone') . ' ' . $oIfx->f('mone_smb_mene');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        


    $logo .= '<table style="width: 100%; margin: 0px;">';
    $logo .= '<tr>';
    $logo .= '<td align="center" style="width: 55%; border:1px solid black; border-radius: 5px; margin: 0px;">';
    $logo .= '<table width: 55%; valign="center" style="margin: 0px;">';
    $logo .= '<tr><td style="margin-top: 0px;"><img width="150px;"  src="' . $path_logo_img . '"></td></tr>';
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


    $logo .= '<tr><td align="left" style="font-size: 13px;"><b>RUC:</b> ' . $ruc_empr . '</td></tr>';
    $logo .= '<tr><td align="left" style="font-size: 13px;" width="500"><b>Matriz:</b> ' . $dirMatriz . '</td></tr>';


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

                if ($contsucu > 1) {
                    $logo .= '<tr><td align="left" style="font-size: 13px;"><b>Sucursal:</b> ' . $sucu_nom_sucu . '</td></tr>';

                    $logo .= '<tr><td align="left" style="font-size: 13px;" width="500" ><b>Direcci&oacute;n Sucursal:</b>' . $sucu_dir_sucu . '</td></tr>';
                }
            } while ($oIfx->SiguienteRegistro());
        }
    }


    //DATOS ADICIONALES
    $logo .= '<tr><td align="left" style="font-size: 13px;"><b>Correo:</b> ' .  $empr_mai_empr . '</td></tr>';

    $logo .= '<tr><td align="left" style="font-size: 13px;"><b>Tel&eacute;fono:</b> ' . $sucu_telf_secu . '</td></tr>';


    if ($empr_tip_empr == 'S') {
        $logo .= '<tr><td align="left" style="font-size: 12px;"><b>Contribuyente Especial #:</b> ' . $empr_num_resu . '</td></tr>';
    } 
    
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




    if($tipo==1){
        $sqlPed = "SELECT * FROM saeprff WHERE
        prff_num_preimp = '$id' and
        prff_cod_empr = $idEmpresa and
        prff_cod_sucu = $idSucursal";
    }
    else{
        
    $sqlPed = "SELECT * FROM saeprff WHERE
    prff_cod_prff = '$id' and
    prff_cod_empr = $idEmpresa and
    prff_cod_sucu = $idSucursal";
    }

    if ($oIfx->Query($sqlPed)) {
        if ($oIfx->NumFilas() > 0) {
            do {

                $id_pedido 			= $oIfx->f('prff_cod_prff');
                $codigo_op 			= $oIfx->f('prff_num_preimp');
                $id_cliente 		= $oIfx->f('prff_cod_clpv');
                $nombre_cliente 	= $oIfx->f('prff_nom_cliente');
                $ruc_cliente 		= $oIfx->f('prff_ruc_clie');
                $fecha_proforma 	= $oIfx->f('prff_fech_fact');
                $telefono 			= $oIfx->f('prff_tlf_cliente');
                $direccion 			= $oIfx->f('prff_dir_clie');
                $prff_emai_prff     = $oIfx->f('prff_emai_prff');
                $prff_cm1_prff 		= $oIfx->f('prff_cm1_prff');
                $con_iva 			= $oIfx->f('prff_con_miva');
                $sin_iva 			= $oIfx->f('prff_sin_miva');
                $dsg_valo			= $oIfx->f('prff_dsg_valo');
                $iva 				= $oIfx->f('prff_iva');
                $pedf_cm3_pedf 		= $oIfx->f('prff_cm3_prff');
                $pedf_cm4_pedf 		= $oIfx->f('prff_cm4_prff');
                $prff_fech_entr 	= $oIfx->f('prff_fech_entr');
                $fecha_entrega      = date('d-m-Y', strtotime($prff_fech_entr));


                $total_proforma = $con_iva+$sin_iva+$iva-$dsg_valo;


                $vend_cod_vend 		= $oIfx->f('prff_cod_vend');
                // nombre vendedor
                $sql_vend = "select vend_nom_vend from saevend where vend_cod_vend = '$vend_cod_vend' ";
                if ($oIfxA->Query($sql_vend)) {
                    if ($oIfxA->NumFilas() > 0) {
                        $nombre_vendedor = htmlentities($oIfxA->f('vend_nom_vend'));
                    } else {
                        $nombre_vendedor = '';
                    }
                }
                $oIfxA->Free();




                

                $logo .= '<b><td style="width: 45%; border: 1px solid black; border-radius: 5px;" align="center">';
                $logo .= ' <table align="center" style="font-size: 15px;">';
                $logo .= ' <tr>';
                $logo .= '<td style="border-bottom: 1px inset black;" width="400">PROFORMA</td>';
                $logo .= ' </tr>';
                $logo .= ' <tr>';
                $logo .= '<td style="font-size: 17px; color: red; border-bottom: 1;">N°: ' . $codigo_op . '</td>';
                $logo .= ' </tr>';
                $logo .= ' </table>';
                $logo .= '</td></b>';
                $logo .= '</tr>';
                $logo .= '</table>';
                $logo .= ' <br>';

                //DATOS DEL CLIENTE

                $cliente .= ' <table style="width: 100%; border:1px solid black; border-radius: 5px; padding: 2px; font-size: 15x;">';
                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 10%;">CLIENTE:</td></b>';
                $cliente .= ' <td style="width: 40%; ">' . $nombre_cliente . '</td>';
                $cliente .= ' <b><td style="width: 13%; ">FECHA EMISION:</td></b>';
                $cliente .= ' <td style="width: 37% ">' . $fecha_proforma . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 10% ">RUC:</td></b>';
                $cliente .= ' <td style="width: 40% ">' . $ruc_cliente . '</td>';
                $cliente .= ' <b><td style="width: 13% ">TELEFONO:</td></b>';
                $cliente .= ' <td style="width: 37% ">' . $fact_tlf_cliente . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 10% ">DIRECCION:</td></b>';
                $cliente .= ' <td style="width: 40% ">' . $direccion . '</td>';
                $cliente .= ' <b><td style="width: 13% ">EMAIL:</td></b>';
                $cliente .= ' <td style="width: 37% ">' . $prff_emai_prff . '</td>';
                $cliente .= ' </tr>';
                $cliente .= ' </table>';

                $cliente .= ' <br>';
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $oIfx->Free();


    $sqlDeta = "select * from saedpff where dpff_cod_prff = $id_pedido and dpff_cod_sucu = $idSucursal and dpff_cod_empr = $idEmpresa";

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

    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            $porcIva = '';
            do {
                $dfac_cod_prod = $oIfx->f('dpff_cod_prod');
                $dfac_nom_prod = $oIfx->f('dpff_nom_prod');
                $dfac_cant_dfac = $oIfx->f('dpff_cant_dfac');
                $dfac_cod_unid = $oIfx->f('dpff_cod_unid');
                $dfac_precio_dfac = $oIfx->f('dpff_precio_dfac');
                $dfac_des1_dfac = $oIfx->f('dpff_des1_dfac');
                $dfac_des2_dfac = $oIfx->f('dpff_des2_dfac');
                $dfac_por_dsg = $oIfx->f('dpff_por_dsg');
                $dfac_mont_total = $oIfx->f('dpff_mont_total');

                $dfac_por_iva = $oIfx->f('dpff_por_iva');
                $dfac_det_dfac = $oIfx->f('dpff_det_dpef');

                //$dfac_cp2_dfac = $oIfx->f('dpff_det_peso');
                
               
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

                $deta .= ' <td style="width: 8%;" align="right">' . number_format($dfac_precio_dfac, 4, '.', ',') . '</td>';
                $deta .= ' <td style="width: 8%;" align="right">' . number_format($descuento, 4, '.', ',') . '</td>';
                $deta .= ' <td style="width: 10%;" align="right">' . number_format($dfac_mont_total, 4, '.', ',') . '</td>';
                $deta .= ' </tr>';

            } while ($oIfx->SiguienteRegistro());
        }
    }

    $deta .= ' </table>';




    $totales .= '<table style="width: 100%;  font-size: 13px; margin-top: 3px;"  align="center">';

    $totales .= '<tr>
    <td  style="width: 65%;" valign="top" >';

    $totales .= ' <table style="width:100%; font-size: 13px; border-radius: 5px; border-collapse: collapse;"  border=1 >';
    $totales .= ' <tr>';
    $totales .= ' <td style="width:100%;" ><b>Información Adicional</b><br>' . $prff_cm1_prff . '</td>';
    $totales .= ' </tr>';
    $totales .= ' </table>';

    $totales .= '</td>';


    $totales .= '<td style="width: 35%; align="left" valign="top">';

    $totales .= ' <table style="width: 100%; font-size: 13px; border-radius: 5px; border-collapse: collapse;"  border=1 align="right">';



    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 72%;">SUBTOTAL:</td> </b>';
    $totales .= ' <td style="width: 28%;" align="right">' . number_format($con_iva + $sin_iva + $dsg_valo, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 72%;">DESCUENTO:</td> </b>';
    $totales .= ' <td style="width: 28%;" align="right">' . number_format($dsg_valo, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 72%;">SUBTOTAL SIN IMPUESTOS:</td> </b>';
    $totales .= ' <td style="width: 28%;" align="right">' . number_format($con_iva + $sin_iva - $fact_ice, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b> <td style="width: 72%;">IVA ' . round($porcIva) . '%:</td> </b>';
    $totales .= ' <td align="right" style="width: 28%;">' . number_format($iva, 2, '.', ',') . '</td>';
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
    $fact_tot_fact = $con_iva + $iva + $sin_iva + $fact_val_irbp + $fact_fina_fact;
    $totales .= ' <td style="width: 28%;" align="right">' . number_format($fact_tot_fact, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    $totales .= ' </table>';
    $totales .= '</td>
    </tr>';
    $totales .= '</table>';

    $adicional = ' <table style="width: 100%; font-size: 12px; border-radius: 5px; border-collapse: collapse; margin-top:15px;" border="1">';
    $adicional .= '<tr>';
    $adicional .= '<td style="width: 15%;" align="center"><b>VALIDEZ</b></td>';
    $adicional .= '<td style="width: 20%;" align="center"><b>TIPO / FORMA DE PAGO</b></td>';
    $adicional .= '<td style="width: 20%;" align="center"><b>FECHA DE ENTREGA</b></td>';
    $adicional .= '<td style="width: 20%;" align="center"><b>MONEDA</b></td>';
    $adicional .= '<td style="width: 25%;" align="center"><b>VENDEDOR</b></td>';
    $adicional .= '</tr>';

    $adicional .= '<tr>';
    $adicional .= '<td style="width: 15%;" align="center">'.$pedf_cm3_pedf.'</td>';
    $adicional .= '<td style="width: 20%;" align="center">'.$pedf_cm4_pedf.'</td>';
    $adicional .= '<td style="width: 20%;" align="center">'.$fecha_entrega.'</td>';
    $adicional .= '<td style="width: 20%;" align="center">'.$moneda_principal.'</td>';
    $adicional .= '<td style="width: 25%;" align="center">' . $nombre_vendedor . '</td>';
    $adicional .= '</tr>';

    $adicional .= '</table>';


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
    $documento .= $logo . $cliente . $deta . $totales . $adicional . $tableLeyenda;
    $documento .= $legend;
    $documento .= '</page>';

    $html2pdf = new HTML2PDF('P', 'A3', 'es');
    $html2pdf->WriteHTML($documento);
    $html2pdf->Output($id.'.pdf');

	
?>