<?

include_once('../../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

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
	$idSucursal = $_GET['sucursal'];
	$id = $_GET['codigo'];
    $empr_cod_pais = $_SESSION['U_PAIS_COD'];
	
	if(empty($idSucursal)){
		$idSucursal = $_SESSION['U_SUCURSAL'];
	}

    $tipo=$_GET['tipo'];

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

    $sql = "select empr_web_color, empr_cod_pais,empr_cm1_empr, empr_rimp_sn, empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, 
    empr_img_rep, empr_iva_empr,empr_tel_resp, empr_ac1_empr, empr_ac2_empr, empr_mai_empr, empr_tip_empr
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

    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;

    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];


    if (file_exists($path_logo_img)) {
        $logo_empresa='<img width="100px;"  src="' . $path_logo_img . '">';
    }
    else{
        $logo_empresa='<div style="color:red;">LOGO NO CARGADO</div>';
    }

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
    

    /*$logo .= '<table style="width: 100%; margin: 0px;">';
    $logo .= '<tr>';
    $logo .= '<td rowspan="3" width="100">'.$logo_empresa.'</td>';
    $logo .= '<b><td align="center" style="width: 80%; margin: 0px;">';
    $logo .= '<table align="center" style="margin: 0px;">';
    $logo .= '<tr><td align="center" style="font-size: 20px;">' . $razonSocial . '</td></tr>';
    $logo .= '<tr><td>&nbsp;</td></tr>';*/
	
	//query bodega
	$sqlBode = "select bode_cod_bode, bode_nom_bode from saebode where bode_cod_empr = $idEmpresa";
	if ($oIfx->Query($sqlBode)) {
        if ($oIfx->NumFilas() > 0) {
			unset($arrayBode);
            do {
                $arrayBode[$oIfx->f('bode_cod_bode')] = $oIfx->f('bode_nom_bode');
            } while ($oIfx->SiguienteRegistro());
        }
    }
	$oIfx->Free();
	
	$sqlUnidad = "select unid_cod_unid, unid_nom_unid, unid_sigl_unid from saeunid where unid_cod_empr = $idEmpresa";
	if ($oIfx->Query($sqlUnidad)) {
        if ($oIfx->NumFilas() > 0) {
			unset($arrayUnidad);
            do {
                $arrayUnidad[$oIfx->f('unid_cod_unid')] = $oIfx->f('unid_sigl_unid');
            } while ($oIfx->SiguienteRegistro());
        }
    }
	$oIfx->Free();
	
    //selecciona sucursales y direcciones
    $sql_sucu = "select sucu_nom_sucu, sucu_dir_sucu from saesucu where sucu_cod_empr = $idEmpresa and sucu_cod_sucu = $idSucursal";
    if ($oIfx->Query($sql_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
                $sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');

                $logo .= '<tr><td align="center" style="font-size: 13px">' . $sucu_nom_sucu . ': ' . htmlentities($sucu_dir_sucu) . '</td></tr>';
            } while ($oIfx->SiguienteRegistro());
        }
    }
	$oIfx->Free();

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


                $sql = "SELECT clv_con_clpv from saeclpv where clpv_cod_clpv = $id_cliente";
                $clv_con_clpv = consulta_string_func($sql, 'clv_con_clpv', $oCon, '');

                if(strlen($clv_con_clpv)==0){
                    $clv_con_clpv = 2;
                }
              

                 //TIPO DE IDENTIFICACION DEL CLIENTE
                 $sql_sucu = "SELECT identificacion from comercial.tipo_iden_clpv_pais where pais_cod_pais = $empr_cod_pais and id_iden_clpv = '$clv_con_clpv'";
                 if ($oIfxA->Query($sql_sucu)) {
                     if ($oIfxA->NumFilas() > 0) {
                         do {
                             $tip_iden_cliente = $oIfxA->f('identificacion');
                         } while ($oIfxA->SiguienteRegistro());
                     }
                 }
                 $oIfxA->Free();


                $pedf_cod_pedf 		= $oIfx->f('prff_cod_prff');
                $nombre_cliente 	= $oIfx->f('prff_nom_cliente');
                $ruc_cliente 		= $oIfx->f('prff_ruc_clie');
                $telefono 			= $oIfx->f('prff_tlf_cliente');
                $direccion 			= $oIfx->f('prff_dir_clie');
                $id_user 			= $oIfx->f('prff_user_web');
                $fecha_user 		= date('d-m-Y',strtotime($oIfx->f('prff_fech_fact')));
                $prioridad 			= $oIfx->f('prioridad');
                $vend_cod_vend 		= $oIfx->f('prff_cod_vend');
                $pedf_email_clpv 	= $oIfx->f('prff_email_clpv');
                $subtotal 			= $oIfx->f('prff_tot_fact');
                $con_iva 			= $oIfx->f('prff_con_miva');
                $sin_iva 			= $oIfx->f('prff_sin_miva');
                $dsg_valo			= $oIfx->f('prff_dsg_valo');
                $iva 				= $oIfx->f('prff_iva');
                $pedf_cm1_pedf 		= $oIfx->f('prff_cm1_prff');
                $pedf_cm3_pedf 		= $oIfx->f('prff_cm3_prff');
                $pedf_cm4_pedf 		= $oIfx->f('prff_cm4_prff');
				$estado 			= $oIfx->f('prff_est_fact');
				$pedf_cod_ccli 		= $oIfx->f('prff_cod_ccli');
				$pedf_hor_fin 		= $oIfx->f('prff_hor_fin');
				$pedf_hor_impr 		= $oIfx->f('prff_hor_impr');
                $prff_fech_entr 	= $oIfx->f('prff_fech_entr');
                $prff_emai_prff     = $oIfx->f('prff_emai_prff');
                $prff_des_tras     = $oIfx->f('prff_des_tras');

                $sql="SELECT trta_nom_trta
                    FROM saetrta WHERE trta_cod_empr = $idEmpresa and trta_cid_trta='$prff_des_tras'";

                $transporte= consulta_string($sql,'trta_nom_trta', $oIfxA,'');
                

                $fecha_entrega      = date('d-m-Y', strtotime($prff_fech_entr));
				
				if ($estado == 'PE') {
                    $estado = 'PENDIENTE';
                    $color = 'green';
                } elseif ($estado == 'AN') {
                    $estado = 'ANULADO';
                    $color = 'red';
                } elseif ($estado == 'GR') {
                    $estado = "FACTURADO";
                    $color = 'blue';
                } elseif ($estado == 'PA') {
                    $estado = "POR AUTORIZAR";
                    $color = 'orange';
                }
				
				//query subcliente
				if(!empty($pedf_cod_ccli)){
					$sqlCcli = "select ccli_nom_conta from saeccli where ccli_cod_ccli = $pedf_cod_ccli and ccli_cod_clpv = $id_cliente and ccli_cod_empr = $idEmpresa";
					if($oIfxA->Query($sqlCcli)){
						if($oIfxA->NumFilas() > 0){
							do{
								$ccli_nom_conta = $oIfxA->f('ccli_nom_conta');
							}while($oIfxA->SiguienteRegistro());
						}
					}
					$oIfxA->Free();
				}

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

				// NOVEDADES
				$sql = "select clpv_cod_zona from saeclpv where clpv_cod_empr = $idEmpresa and clpv_clopv_clpv = 'CL' and clpv_cod_clpv  = '$id_cliente' ";
				$clpv_cod_zona = consulta_string_func($sql, 'clpv_cod_zona', $oIfxA, '');
                $zona='';
                if(!empty($clpv_cod_zona)){
                    $sql = "select zona_nom_zona from saezona where zona_cod_empr = $idEmpresa and zona_cod_zona=$clpv_cod_zona";
				    $zona = consulta_string_func($sql, 'zona_nom_zona', $oIfxA, '');
                }


                $logo ='<table border="0"  style="width: 100%;"  cellspacing="0">';
                $logo .= '<tr>';
            
                $logo .= '<td align="left" width="420">';
                $logo .= '<table  style="margin: 0px;">';
                $logo .= '<tr>';
                $logo .= '<td align="center">'.$logo_empresa.'</td>';
                $logo .= '<td width="335" style="font-size:13px;"><div style="margin-left:10px"><b>' . $razonSocial . '</b><br>'.$dirMatriz.'<br>
                <b>Telf:</b>'.$tel_empresa.'<br><b>Celular:</b> '.$sucu_telf_secu.'<br><b>Web:</b> www.ecovalle.pe </div></td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
                
                $logo .= '<td align="left" width="260">';
                $logo .= '<table  style="border: '.$empr_web_color.' 1px solid ; border-radius: 5px; " cellspacing=0>';
            
                $logo .= '<tr style="font-size:16px;">';
                $logo .= '<td width="260" height="35" align="center"><b>R.U.C. N° ' . $ruc_empr . '</b></td>';
                $logo .= '</tr>';
            
                $logo .= '<tr style="font-size:16px;">';
                $logo .= '<td width="260"  height="35" style="background: '.$empr_web_color.'; color:white;" align="center"><b>PROFORMA</b></td>';
                $logo .= '</tr>';
                
                $logo .= '<tr style="font-size:16px;">';
                $logo .= '<td width="260" height="35" align="center" ><b>Nro. ' . $codigo_op . '</b></td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
            
                $logo .= '</tr>';
                $logo.='</table>';


                $logo .='<table border="0" style="width: 80%; margin-top:4px;font-size:12px;">';
                $logo .= '<tr>';
            
                $logo .= '<td  width="361" >';
                $logo .= '<table  style="margin: 0px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px; width:100%;" >';
                $logo .= '<tr>';
                $logo .= '<td width="361"><div style="margin-left:3px"><b>Cliente:</b> '.$nombre_cliente.'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="361"><div style="margin-left:3px"><b>'.$tip_iden_cliente.':</b>  '.$ruc_cliente.'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="361"><div style="margin-left:3px"><b>Telefono:</b> '.$telefono.'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="361"><div style="margin-left:3px"><b>Dirección:</b> '.htmlentities($direccion)  .'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="361"><div style="margin-left:3px"><b>Subcliente:</b> '.$ccli_nom_conta  .'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="361"><div style="margin-left:3px"><b>Observaciones:</b> '.$pedf_cm1_pedf  .'</div></td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
            
                $logo .= '<td  width="325">';
                $logo .= '<table  style="margin: 0px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px; width:100%;" >';

                $logo .= '<tr>';
                $logo .= '<td width="325"><b>Fecha de Proforma:</b> ' . $fecha_user . ' '.$pedf_hor_fin.'</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="325"><b>Vendedor:</b> ' . $nombre_vendedor . '</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="325"><div style="margin-left:3px"><b>Email:</b> '.$prff_emai_prff.'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="325"><div style="margin-left:3px"><b>Zona:</b> '.$zona.'</div></td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
            
                $logo .= '</tr>';
                $logo.='</table>';



                
				
				
                            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $sqlDeta = "select * from saedpff where dpff_cod_prff = $id_pedido and dpff_cod_sucu = $idSucursal and dpff_cod_empr = $idEmpresa";

    $deta .= ' <table style="width: 99%;  font-size: 13px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:10px;margin-left:2px;" cellspacing=0>';
    $deta .= ' <tr>';
    $deta .= ' <b> <td style="border-top-left-radius: 4px; background: '.$empr_web_color.'; color:white; width: 5%; font-size:13px;" align="center" height="30">ITEM</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 15%; font-size:13px;" align="center" height="30">CODIGO</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 42%; font-size:13px;" align="center" height="30">DESCRIPCIÓN</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width:  10%; font-size:13px;" align="center" height="30">CANTIDAD</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 5%; font-size:13px;" align="center" height="30">UM</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width:  11%; font-size:13px;" align="center" height="30">PRECIO</td> </b>';
    $deta .= ' <b> <td style="border-top-right-radius: 4px; background: '.$empr_web_color.'; color:white; width: 12%; font-size:13px;" align="center" height="30">IMPORTE</td> </b>';
    $deta .= ' </tr>';
    

	$num_prod = 0;
    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            $i = 1;
            do {
                $dfac_cod_prod 		= $oIfx->f('dpff_cod_prod');
                $dfac_nom_prod 		= $oIfx->f('dpff_nom_prod');
                $dfac_cant_dfac 	= $oIfx->f('dpff_cant_dfac');
                $dfac_precio_dfac 	= $oIfx->f('dpff_precio_dfac');
                $dfac_des1_dfac 	= $oIfx->f('dpff_des1_dfac');
                $dfac_des2_dfac 	= $oIfx->f('dpff_des2_dfac');
                $dfac_por_dsg 		= $oIfx->f('dpff_por_dsg');
                $dfac_mont_total 	= $oIfx->f('dpff_mont_total');
                $dpef_det_dpef 		= $oIfx->f('dpff_det_dpef');
                $dpef_cod_bode 		= $oIfx->f('dpff_cod_bode');
				$dpef_cod_lote 		= $oIfx->f('dpff_cod_lote');
				$dpef_lote_fcad 	= $oIfx->f('dpff_lote_fcad');
				$dpef_cod_unid 		= $oIfx->f('dpff_cod_unid');
                $dpff_por_iva 		= $oIfx->f('dpff_por_iva');
				
				if(!empty($dpef_lote_fcad)){
					$dpef_lote_fcad = fecha_mysql_func($dpef_lote_fcad);
				}

                $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;
                if ($descuento > 0)
                    $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                else
                    $descuento = 0;

                $totalDescuento = $totalDescuento + $descuento;

                if ($dpff_por_iva > 0) {
                    $porcentaje_iva = ($dpff_por_iva / 100) + 1;
                    $dfac_mont_total = $dfac_mont_total * $porcentaje_iva;
                    $dfac_precio_dfac = $dfac_precio_dfac * $porcentaje_iva;
                }
			
                
                $deta .= ' <tr>';
                $deta .= ' <td style="width: 5%;" align="center">'.$i.'</td>';
                $deta .= ' <td style="width: 15%; border-left: '.$empr_web_color.' 0.7px solid;">'.$dfac_cod_prod.'</td>';
                $deta .= ' <td style="width: 42%; border-left: '.$empr_web_color.' 0.7px solid;">'.$dfac_nom_prod.'</td>';
                $deta .= ' <td align="right" style="width: 10%; border-left: '.$empr_web_color.' 0.7px solid;">' . number_format($dfac_cant_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td align="center" style="width: 5%; border-left: '.$empr_web_color.' 0.7px solid;">' . $arrayUnidad[$dpef_cod_unid] . '</td>';
                $deta .= ' <td align="right" style="width: 11%; border-left: '.$empr_web_color.' 0.7px solid;">' . number_format($dfac_precio_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td align="right" style="width: 12%; border-left: '.$empr_web_color.' 0.7px solid;">' . number_format($dfac_mont_total, 2, '.', ',') . '</td>';
                $deta .= ' </tr>';


                $i++;
				$num_prod += $dfac_cant_dfac;
				
            }while ($oIfx->SiguienteRegistro());
        }
    }
	$oIfx->Free();
	
    $deta .= ' </table>';


    $totales ='<table style="width: 99%;  font-size: 11px; margin-top: 5px;" cellspacing="0"  align="left">';
    $totales .='<tr>
    <td  valign="top" width="432" ></td>';


    $totales.='<td valign="top" width="250" >';

    $totales .= ' <table style=" font-size: 13px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px;"   cellspacing="0" align="left">';

    $totales .= ' <tr>';
    $totales .= ' <b><td style="width: 185; border-bottom: '.$empr_web_color.' 0.7px solid;" align="right">SUBTOTAL SIN IMPUESTOS:</td> </b>';
    $totales .= ' <td    style="width: 80; border-bottom: '.$empr_web_color.' 0.7px solid; border-left: '.$empr_web_color.' 0.7px solid;" align="right">' . number_format($$con_iva + $sin_iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b><td style="width: 185; border-bottom: '.$empr_web_color.' 0.7px solid;" align="right">SUBTOTAL '.$array_imp ['IVA'].' '. number_format($array_porc ['IVA'],2).'%:</td> </b>';
    $totales .= ' <td    style="width: 80; border-bottom: '.$empr_web_color.' 0.7px solid; border-left: '.$empr_web_color.' 0.7px solid;" align="right">' . number_format($con_iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b><td style="width: 185; border-bottom: '.$empr_web_color.' 0.7px solid;" align="right">SUBTOTAL '.$array_imp ['IVA'].' 0%:</td> </b>';
    $totales .= ' <td    style="width: 80; border-bottom: '.$empr_web_color.' 0.7px solid; border-left: '.$empr_web_color.' 0.7px solid;" align="right">' . number_format($sin_iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b><td style="width: 185; border-bottom: '.$empr_web_color.' 0.7px solid;" align="right">DESCUENTO:</td> </b>';
    $totales .= ' <td    style="width: 80; border-bottom: '.$empr_web_color.' 0.7px solid; border-left: '.$empr_web_color.' 0.7px solid;" align="right">' . number_format($dsg_valo, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';


    $totales .= ' <tr>';
    $totales .= ' <b><td style="width: 185; border-bottom: '.$empr_web_color.' 0.7px solid;" align="right">'.$array_imp ['IVA'].':</td> </b>';
    $totales .= ' <td    style="width: 80; border-bottom: '.$empr_web_color.' 0.7px solid; border-left: '.$empr_web_color.' 0.7px solid;" align="right">' . number_format($iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b><td style="width: 185; border-bottom: '.$empr_web_color.' 0.7px solid; " align="right">TOTAL GENERAL:</td> </b>';
    $totales .= ' <td    style="width: 80; border-bottom: '.$empr_web_color.' 0.7px solid; border-left: '.$empr_web_color.' 0.7px solid;" align="right">' . number_format($con_iva + $sin_iva + $iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <b><td style="width: 185;" align="right">N° PRODUCTOS:</td> </b>';
    $totales .= ' <td    style="width: 80; border-left: '.$empr_web_color.' 0.7px solid;" align="right">' . $num_prod . '</td>';
    $totales .= ' </tr>';

   
    $totales .= ' </table>';


    $totales.='</td>
    </tr>';
    $totales .='</table>';


	
    $totales .= ' <table style="width: 100%; font-size: 12px; margin-top: 30px; border-radius: 5px; border-collapse: collapse;"  border=0 align="right">';
    $totales .= ' <tr>';
	$totales .= ' <td style="width: 33%;" align="center">--------------------------------------</td>';
	$totales .= ' <td style="width: 33%;" align="center">--------------------------------------</td>';
	$totales .= ' <td style="width: 33%;" align="center">--------------------------------------</td>';
    $totales .= ' </tr>';

	$totales .= ' <tr>';
	$totales .= ' <td style="width: 33%;" align="center">PREP. POR</td>';
	$totales .= ' <td style="width: 33%;" align="center">CONTROL</td>';
	$totales .= ' <td style="width: 33%;" align="center">AUTORIZADO POR</td>';
    $totales .= ' </tr>';
	
	$totales .= ' </table>';

    //DATOS ADICIONALES
    
    $adicional = ' <table style="width: 99%;  font-size: 13px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:10px;margin-left:2px;" cellspacing=0>';
    $adicional .= ' <tr>';
    $adicional .= ' <b> <td style="border-top-left-radius: 4px; background: '.$empr_web_color.'; color:white; width: 15%; font-size:13px;" align="center" height="30">VALIDEZ</td> </b>';
    $adicional .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 15%; font-size:13px;" align="center" height="30">TIPO / FORMA DE PAGO</td> </b>';
    $adicional .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 15%; font-size:13px;" align="center" height="30">FECHA DE ENTREGA</td> </b>';
    $adicional .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 15%; font-size:13px;" align="center" height="30">MONEDA</td> </b>';
    $adicional .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 15%; font-size:13px;" align="center" height="30">TRANSPORTE</td> </b>';
    $adicional .= ' <b> <td style="border-top-right-radius: 4px; background: '.$empr_web_color.'; color:white; width: 25%; font-size:13px;" align="center" height="30">VENDEDOR</td> </b>';
    $adicional .= ' </tr>';


    $adicional .= ' <tr>';
    $adicional .= ' <td style="width: 15%;" align="center">'.$pedf_cm3_pedf.'</td>';
    $adicional .= ' <td style="width: 15%; border-left: '.$empr_web_color.' 0.7px solid;" align="center">'.$pedf_cm4_pedf.'</td>';
    $adicional .= ' <td style="width: 15%; border-left: '.$empr_web_color.' 0.7px solid;" align="center">'.$fecha_entrega.'</td>';
    $adicional .= ' <td align="center" style="width: 15%; border-left: '.$empr_web_color.' 0.7px solid;">'.$moneda_principal.'</td>';
    $adicional .= ' <td align="center" style="width: 15%; border-left: '.$empr_web_color.' 0.7px solid;">' . $transporte . '</td>';
    $adicional .= ' <td align="center" style="width: 25%; border-left: '.$empr_web_color.' 0.7px solid;">' . $nombre_vendedor . '</td>';

    
    $adicional .= ' </tr>';
    $adicional .= '</table>';


    ///LEYENDAS FACTURA
    $tableLeyenda ='<table border="0"  style="width: 85%;" cellspacing="2">';
    $tableLeyenda .= '<tr>';

 $sql_cont="select count(*) as conteo from saeipdf where ipdf_cod_empr=$idEmpresa and ipdf_tip_ipdf in (select 
    emifa_cod_emifa from saeemifa  where emifa_cod_empr = $idEmpresa
    and emifa_tip_doc = 'FAC' and emifa_est_emifa = 'S' 
    and emifa_cod_emifa=ipdf_tip_ipdf) and ipdf_est_deleted ='S'";
    $num_items=consulta_string($sql_cont,'conteo',$oIfx,1);


    $sqlpdf="select * from saeipdf where ipdf_cod_empr=$idEmpresa and ipdf_tip_ipdf in (select 
        emifa_cod_emifa from saeemifa  where emifa_cod_empr = $idEmpresa
        and emifa_tip_doc = 'FAC'  and emifa_est_emifa = 'S' 
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

    	
	if(empty($pedf_hor_impr)){
		
		$hora_impr = date('H:i:s');
	}

    $documento .= '<page backimgw="95%" backtop="5mm" backbottom="5mm" backleft="5mm" backright="5mm" footer="date;heure;page">';
    $documento .= $logo . $deta . $totales. $adicional.$tableLeyenda;
    $documento .= '</page>';

    $html2pdf = new HTML2PDF('P', 'A4', 'es');
    $html2pdf->WriteHTML($documento);
    $html2pdf->Output($id.'.pdf');

	
?>