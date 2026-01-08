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
	
	if(empty($idSucursal)){
		$idSucursal = $_SESSION['U_SUCURSAL'];
	}

    $tipo=$_GET['tipo'];

    $sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr, 
			empr_conta_sn, empr_num_resu, empr_path_logo, empr_mai_empr, empr_iva_empr
			from saeempr 
			where empr_cod_empr = $idEmpresa ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_path_logo');
            $empr_mai_empr = $oIfx->f('empr_mai_empr');
            if ($oIfx->f('empr_conta_sn') == 'S')
                $empr_conta_sn = 'SI';
            else
                $empr_conta_sn = 'NO';

            $empr_num_resu = $oIfx->f('empr_num_resu');
            $empr_iva_empr = round($oIfx->f('empr_iva_empr'));
        }
    }
    $oIfx->Free();

    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;

    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];


    if (file_exists($path_logo_img)) {
        $logo_empresa='<img width="200px;"  src="' . $path_logo_img . '">';
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
    

    $logo .= '<table style="width: 100%; margin: 0px;">';
    $logo .= '<tr>';
    $logo .= '<td rowspan="3" width="100">'.$logo_empresa.'</td>';
    $logo .= '<b><td align="center" style="width: 80%; margin: 0px;">';
    $logo .= '<table align="center" style="margin: 0px;">';
    $logo .= '<tr><td align="center" style="font-size: 20px;">' . $razonSocial . '</td></tr>';
    $logo .= '<tr><td>&nbsp;</td></tr>';
	
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
	
	$sqlUnidad = "select unid_cod_unid, unid_nom_unid from saeunid where unid_cod_empr = $idEmpresa";
	if ($oIfx->Query($sqlUnidad)) {
        if ($oIfx->NumFilas() > 0) {
			unset($arrayUnidad);
            do {
                $arrayUnidad[$oIfx->f('unid_cod_unid')] = $oIfx->f('unid_nom_unid');
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
                $prff_ciud_clie     = $oIfx->f('prff_ciud_clie')?:0;
                //$prff_user_web     = $oIfx->f('prff_user_web')?:0;


                


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

                $sql_ciud="SELECT ciud_nom_ciud from saeciud where ciud_cod_ciud='$prff_ciud_clie'";

                if($oIfxA->Query($sql_ciud)){
                    if($oIfxA->NumFilas() > 0){
                        do{
                            $ciud_nom_ciud_a = $oIfxA->f('ciud_nom_ciud');
                        }while($oIfxA->SiguienteRegistro());
                    }
                }


                $sqlUser = "SELECT concat(usuario_apellido, ' ', usuario_nombre) as nombre from comercial.usuario where usuario_id = $id_user";
                $nombre = consulta_string_func($sqlUser, 'nombre', $oIfxA, '');
				
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
				$sql = "select clpv_nov_clpv from saeclpv where clpv_cod_empr = $idEmpresa and clpv_clopv_clpv = 'CL' and clpv_cod_clpv  = '$id_cliente' ";
				$clpv_nov_clpv = consulta_string_func($sql, 'clpv_nov_clpv', $oIfxA, '');
				
				
                $logo .= '<tr><td>&nbsp;</td></tr>';
                $logo .= '<tr><td align="center" style="font-size: 18px; color: red;">PROFORMA : ' . $codigo_op . '</td></tr>';
                $logo .= ' </table>';
                $logo .= '</td></b>';
                $logo .= '</tr>';
                $logo .= '</table>';

                $cliente .= ' <table style="margin-top:25px; width: 100%; padding: 2px; font-size: 15x;">';
				 $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 10%"></td></b>';
                $cliente .= ' <td style="width: 50% "></td>';
                $cliente .= ' <b><td style="width: 14% "> FECHA PROFORMA: </td></b>';
                $cliente .= ' <td style="width: 26% ">' . $fecha_user . ' '.$pedf_hor_fin.'</td>';
                $cliente .= ' </tr>';
				
				 $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 10%"> CLIENTE: </td></b>';
                $cliente .= ' <td style="width: 50% ">' . htmlentities($nombre_cliente) . '</td>';
                $cliente .= ' <b><td style="width: 14% "> RUC: </td></b>';
                $cliente .= ' <td style="width: 26% ">' . $ruc_cliente . '</td>';
                $cliente .= ' </tr>';
               
			    $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 10% "> TELEFONO: </td></b>';
                $cliente .= ' <td style="width: 50% ">' . $telefono . '</td>';
				$cliente .= ' <b><td style="width: 14% "> EMAIL: </td></b>';
                $cliente .= ' <td style="width: 26% ">' . $prff_emai_prff . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <b><td style="width: 13% "> DIRECCION: </td></b>';
                $cliente .= ' <td colspan="">' . htmlentities($direccion) . '</td>';
                $cliente .= ' <b><td style="width: 13% "> GENERADO POR: </td></b>';
                $cliente .= ' <td colspan="">' . ($nombre) . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
				$cliente .= ' <b><td style="width: 10% "> SUBCLIENTE: </td></b>';
                $cliente .= ' <td style="width: 50% ">' . $ccli_nom_conta . '</td>';
                $cliente .= ' <b><td style="width: 14% "> VENDEDOR: </td></b>';
                $cliente .= ' <td style="width: 26% ">' . $nombre_vendedor . '</td>';
                $cliente .= ' </tr>';
				
				$cliente .= ' <tr>';
				$cliente .= ' <b><td style="width: 10% "> ZONA: </td></b>';
                $cliente .= ' <td style="width: 50%;">' . $clpv_nov_clpv . '</td>';
                $cliente .= ' <b><td style="width: 14% ">CIUDAD:</td></b>';
                $cliente .= ' <td style="width: 26% ">'.$ciud_nom_ciud_a.'</td>';
                $cliente .= ' </tr>';
				
				$cliente .= ' <tr>';
				$cliente .= ' <b><td style="width: 13% "> OBSERVACIONES: </td></b>';
                $cliente .= ' <td colspan="3">' . $pedf_cm1_pedf . '</td>';
                $cliente .= ' </tr>';
				
                $cliente .= ' </table>';

                $cliente .= ' <br>';
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $sqlDeta = "select * from saedpff where dpff_cod_prff = $id_pedido and dpff_cod_sucu = $idSucursal and dpff_cod_empr = $idEmpresa";

    $deta .= ' <table style="margin-top:25px; width: 100%; font-size: 12px; border-radius: 5px; border-collapse: collapse;" border="1">';
    $deta .= ' <tr>';
    $deta .= ' <b> <th class="diagrama" style="width: 5%;" align="center">N°</th> </b>';
    $deta .= ' <b> <th class="diagrama" style="width: 14%;" align="center">CODIGO</th> </b>';
    $deta .= ' <b> <th class="diagrama" style="width: 25%;" align="center">DESCRIPCION</th> </b>';
    $deta .= ' <b> <th class="diagrama" style="width: 14%;" align="center">MARCA</th> </b>';
    $deta .= ' <b> <th class="diagrama" style="width: 16%;" align="center">APLICACION</th> </b>';
	$deta .= ' <b> <th class="diagrama" style="width: 8%;" align="center">CANTIDAD</th> </b>';
	//$deta .= ' <b> <th class="diagrama" style="width: 8%;" align="center">UNI.</th> </b>';
	$deta .= ' <b> <th class="diagrama" style="width: 8%;" align="center">P.UNITARIO</th> </b>';	
	$deta .= ' <b> <th class="diagrama" style="width: 10%;" align="center">TOTAL</th> </b>';
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
				
				if(!empty($dpef_lote_fcad)){
					$dpef_lote_fcad = fecha_mysql_func($dpef_lote_fcad);
				}

                $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;
                if ($descuento > 0)
                    $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                else
                    $descuento = 0;

                $totalDescuento = $totalDescuento + $descuento;

				$sql = "select prbo_dis_prod, prbo_ubic_pasillo ,	prbo_ubic_izqdere ,  prbo_ubic_ubicacion , prbo_ubic_nivel 
								from saeprbo where 
								prbo_cod_empr = $idEmpresa and 
								prbo_cod_sucu = $idSucursal and 
								prbo_cod_prod = '$dfac_cod_prod' and
								prbo_cod_bode = $dpef_cod_bode ";
				$stock = 0;						$prbo_ubic_pasillo = '';				$prbo_ubic_izqdere = '';
				$prbo_ubic_ubicacion = '';		$prbo_ubic_nivel   = '';				$ubicacion = '';
				
				if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        $stock 				= ($oIfxA->f('prbo_dis_prod'));
						$prbo_ubic_pasillo	= ($oIfxA->f('prbo_ubic_pasillo'));
						$prbo_ubic_izqdere 	= ($oIfxA->f('prbo_ubic_izqdere'));
						$prbo_ubic_ubicacion= ($oIfxA->f('prbo_ubic_ubicacion'));
						$prbo_ubic_nivel	= ($oIfxA->f('prbo_ubic_nivel'));
						$ubicacion			= $prbo_ubic_pasillo.' - '.$prbo_ubic_izqdere.' - '.$prbo_ubic_ubicacion.' - '.$prbo_ubic_nivel;
                    }
                }
                $oIfxA->Free();
				
				$sql = "select prod_cod_barra,  prod_cod_marc , marc_cod_marc, marc_des_marc, prod_apli_prod
								from saeprod, saemarc  where
								marc_cod_marc = prod_cod_marc and
								marc_cod_empr = $idEmpresa and
								prod_cod_sucu = $idSucursal and
								prod_cod_empr = $idEmpresa and
								prod_cod_prod = '$dfac_cod_prod' ";
				if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        $marca 			= ($oIfxA->f('marc_des_marc'));
						$prod_cod_barra = ($oIfxA->f('prod_cod_barra'));
                        $aplicacion = $oIfxA->f('prod_apli_prod');
                    }
                }
                $oIfxA->Free();
				
				
				
                $deta .= ' <tr>';
                $deta .= ' <td style="width: 5%;" align="center">' . $i . '</td>';
                $deta .= ' <td style="width: 14%;" align="center">' . $dfac_cod_prod . '</td>';
                $deta .= ' <td style="width: 25%;">' . htmlentities($dfac_nom_prod) . '</td>';
                $deta .= ' <td style="width: 14%;">'.$marca.'</td>';
                $deta .= ' <td style="width: 16%;font-size: 70%;">'.$aplicacion.'</td>';			
				$deta .= ' <td style="width: 8%;" align="right">' . number_format($dfac_cant_dfac, 2, '.', ',') . '</td>';
				//$deta .= ' <td style="width: 8%;" align="center">' . $arrayUnidad[$dpef_cod_unid] . '</td>';					
				$deta .= ' <td style="width: 8%;" align="right">' . number_format($dfac_precio_dfac, 4, '.', ',') . '</td>';				
                $deta .= ' <td style="width: 10%;" align="right">' . number_format($dfac_mont_total, 4, '.', ',') . '</td>';
               // $deta .= ' <td style="width: 10%;" align="right">' .$stock. '</td>';
                $deta .= ' </tr>';
				
                $i++;
				$num_prod += $dfac_cant_dfac;
				
            }while ($oIfx->SiguienteRegistro());
        }
    }
	$oIfx->Free();
	
    $deta .= ' </table>';

    $totales .= ' <table style="width: 100%; font-size: 12px; margin-top: 3px; border-radius: 5px; border-collapse: collapse;"  border=1 align="right">';
    $totales .= ' <tr>';
	$totales .= ' <td style="width: 20%;">NRO. PRODUCTOS: </td>';
	$totales .= ' <b><td style="width: 5%;" align="center">'.$num_prod.'</td></b>';
    $totales .= ' <b> <td>SUBTOTAL SIN IMPUESTOS:</td> </b>';
    $totales .= ' <td style="width: 10%;" align="right">' . number_format($con_iva + $sin_iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';	
	
    $totales .= ' <tr>';
	$totales .= ' <td></td>';
	$totales .= ' <td></td>';
    $totales .= ' <b> <td>SUBTOTAL  IVA '.$empr_iva_empr.'%:</td> </b>';
    $totales .= ' <td align="right">' . number_format($con_iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
	
    $totales .= ' <tr>';
	$totales .= ' <td></td>';
	$totales .= ' <td></td>';
    $totales .= ' <b> <td>SUBTOTAL  IVA 0%:</td> </b>';
    $totales .= ' <td align="right">' . number_format($sin_iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
	
    $totales .= ' <tr>';
	$totales .= ' <td></td>';
	$totales .= ' <td></td>';
    $totales .= ' <b> <td>DESCUENTO:</td> </b>';
    $totales .= ' <td align="right">' . number_format($dsg_valo, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
	
    $totales .= ' <tr>';
	$totales .= ' <td></td>';
	$totales .= ' <td></td>';
    $totales .= ' <b> <td>IVA:</td> </b>';
    $totales .= ' <td align="right">' . number_format($iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
	
    $totales .= ' <tr>';
	$totales .= ' <td></td>';
	$totales .= ' <td></td>';
    $totales .= ' <b> <td>TOTAL GENERAL:</td> </b>';
    $totales .= ' <td align="right">' . number_format($con_iva + $sin_iva + $iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    $totales .= ' </table>';

	
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
    $path_1 = DIR_FACTELEC . 'modulos/proforma/files/BANNER_2.png';


    if (file_exists($path_1)) {
        $banner='<img width="600px;"  src="' . $path_1 . '">';
    }
    
    

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

    	
	if(empty($pedf_hor_impr)){
		
		$hora_impr = date('H:i:s');
	}

    $documento .= '<page backimgw="95%" backtop="5mm" backbottom="5mm" backleft="5mm" backright="5mm" footer="date;heure;page">';
    $documento .= $logo . $cliente . $deta . $totales. $adicional;
    $documento .= '</page>';

    $html2pdf = new HTML2PDF('P', 'A3', 'es');
    $html2pdf->WriteHTML($documento);
    $html2pdf->Output($id.'.pdf');

	
?>