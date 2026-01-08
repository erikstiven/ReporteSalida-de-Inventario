<?php
include_once('../../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
//include_once (path(DIR_INCLUDE).'html2pdf_v4.03/_tcpdf_5.0.002_old/tcpdf.php');

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

    $array_imp = $_SESSION['U_EMPRESA_IMPUESTO'];
    $eti_imp=$array_imp['IVA'];

    $empr_cod_pais = $_SESSION['U_PAIS_COD'];
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

//	print_r($id);Exit;
	
	if(empty($idSucursal)){
		$idSucursal = $_SESSION['U_SUCURSAL'];
	}

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
    //LOGO DEL REPORTE
$path_img = explode("/", $empr_path_logo);
$count = count($path_img) - 1;
$arc_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

if (file_exists($arc_img)) {
    $imagen = $arc_img;
} else {
    $imagen = '';
}
$logo = '';
$x = '0px';
if ($imagen != '') {

    $empr_logo = '
        <img src="' . $imagen . '" style="
        width:100px;
        object-fit; contain;">';
    $x = '0px';
}
else{
    $empr_logo='LOGO NO CARGADO';
}
	
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




    $sqlPed = "SELECT * FROM saepedf WHERE
				pedf_cod_pedf = $id and
				pedf_cod_empr = $idEmpresa and
				pedf_cod_sucu = $idSucursal";
//    print_r($sqlPed);
    if ($oIfx->Query($sqlPed)) {
        //echo $oIfx->NumFilas();
        if ($oIfx->NumFilas() > 0) {


                $id_pedido = $oIfx->f('pedf_cod_pedf');
                $codigo_op = $oIfx->f('pedf_num_preimp');
                $id_cliente = $oIfx->f('pedf_cod_clpv');
                $pedf_cod_pedf = $oIfx->f('pedf_cod_pedf');
                $nombre_cliente = $oIfx->f('pedf_nom_cliente');
                $ruc_cliente = $oIfx->f('pedf_ruc_clie');
                $telefono = $oIfx->f('pedf_tlf_cliente');
                $direccion = $oIfx->f('pedf_dir_clie');
                $id_user = $oIfx->f('pedf_user_web');
                $fecha_user = $oIfx->f('pedf_fech_fact');
                $prioridad = $oIfx->f('prioridad');
                $vend_cod_vend = $oIfx->f('pedf_cod_vend');
                $pedf_email_clpv = $oIfx->f('pedf_email_clpv');
                $subtotal = $oIfx->f('pedf_tot_fact');
                $con_iva = $oIfx->f('pedf_con_miva');
                $sin_iva = $oIfx->f('pedf_sin_miva');
                $dsg_valo = $oIfx->f('pedf_dsg_valo');
                $iva = $oIfx->f('pedf_iva');
                $pedf_cm1_pedf = $oIfx->f('pedf_cm1_pedf');
				$estado = $oIfx->f('pedf_est_fact');
				$pedf_cod_ccli = $oIfx->f('pedf_cod_ccli');
				$pedf_hor_fin = $oIfx->f('pedf_hor_fin');
                
				$pedf_hor_impr = $oIfx->f('pedf_hor_impr');
				$pedf_lug_pedf = $oIfx->f('pedf_lug_pedf');
				$pedf_cod_ase = $oIfx->f('pedf_cod_ase');


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



                //selecciona sucursales y direcciones
                /*$sql_sucu = "select sucu_nom_sucu, sucu_dir_sucu from saesucu where sucu_cod_empr = $idEmpresa and sucu_cod_sucu = $idSucursal limit 1";
                $sucu_nom_sucu = consulta_string($sql_sucu, 'sucu_nom_sucu', $oIfx, '');
                $sucu_dir_sucu = consulta_string($sql_sucu, 'sucu_dir_sucu', $oIfx, '');


                /*$logo .= '<table style="font-size: 80%;" >';
                $logo .= '<tr><td align="left"  >' .  $empr_logo . '</td></tr>';
                $logo .= '<tr><td align="center" >' . $razonSocial . '</td></tr>';
                $logo .= '<tr><td align="center" >' . $sucu_nom_sucu . '</td></tr>';
                $logo .= '<tr><td align="center" >' . $sucu_dir_sucu . '</td></tr>';
                $logo .= '<tr><td align="center" style="color: red;">PEDIDO : ' . $codigo_op . '</td></tr>';
                $logo .= '</table><br><br>';*/




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
                }elseif ($estado == 'PR') {
                    $estado = "PERDIDA";
                    $color = 'red';
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
                //echo $sql_vend;
                if ($oIfxA->Query($sql_vend)) {
                    if ($oIfxA->NumFilas() > 0) {
                        $nombre_vendedor = $oIfxA->f('vend_nom_vend');
                    } else {
                        $nombre_vendedor = '';
                    }
                }
                $oIfxA->Free();

                $asesor = '';
                /*if($pedf_cod_ase){
                    // nombre asesor
                    $sql_asesor = "select   ases_ape_ases || ' ' ||ases_nom_ases as asesor  from saeases where ases_cod_ases ='$pedf_cod_ase' ";
                    if ($oIfxA->Query($sql_asesor)) {
                        if ($oIfxA->NumFilas() > 0) {
                            $asesor = $oIfxA->f('asesor');
                        } else {
                            $asesor = '';
                        }
                    }
                    $oIfxA->Free();
                }*/

                if(!empty($pedf_cod_ase)){
                    $sql_asesor="select concat(usuario_apellido, ' ', usuario_nombre) as user from comercial.usuario where empresa_id=$idEmpresa and usuario_id=$pedf_cod_ase";
                    $asesor = consulta_string($sql_asesor, 'user', $oIfxA, '');
                }


                // nombre comercial
                $sql_com = "select clpv_nom_come, clpv_cod_zona from saeclpv where clpv_cod_clpv = '$id_cliente' ";

                if ($oIfxA->Query($sql_com)) {
                    if ($oIfxA->NumFilas() > 0) {
                        $nombre_comercial = trim($oIfxA->f('clpv_nom_come'));
                        $zona_cliente = $oIfxA->f('clpv_cod_zona');

                        if($zona_cliente){
                            $sql_zona = "select zona_nom_zona from saezona where zona_cod_zona = '$zona_cliente';";
                            $zona_cliente = consulta_string($sql_zona, 'zona_nom_zona', $oIfx, '');
                        }

                    } else {
                        $nombre_comercial = '';
                        $zona_cliente = '';
                    }
                }
                $oIfxA->Free();


                $logo ='<table border="0"  style="width: 100%;"  cellspacing="0">';
                $logo .= '<tr>';
            
                $logo .= '<td align="left" width="420">';
                $logo .= '<table  style="margin: 0px;">';
                $logo .= '<tr>';
                $logo .= '<td align="center">'.$empr_logo.'</td>';
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
                $logo .= '<td width="260"  height="35" style="background: '.$empr_web_color.'; color:white;" align="center"><b>PEDIDO</b></td>';
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
            
                $logo .= '<td  width="371" >';
                $logo .= '<table  style="margin: 0px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px; width:100%;" >';
                $logo .= '<tr>';
                $logo .= '<td width="371"><div style="margin-left:3px"><b>Nombre Comercial:</b> '.$nombre_comercial.'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="371"><div style="margin-left:3px"><b>Cliente:</b> '.$nombre_cliente.'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="371"><div style="margin-left:3px"><b>'.$tip_iden_cliente.':</b>  '.$ruc_cliente.'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="371"><div style="margin-left:3px"><b>Telefono:</b> '.$telefono.'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="371"><div style="margin-left:3px"><b>Dirección:</b> '.htmlentities($direccion)  .'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="371"><div style="margin-left:3px"><b>Subcliente:</b> '.$ccli_nom_conta  .'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="371"><div style="margin-left:3px"><b>Observaciones:</b> '.$pedf_cm1_pedf  .'</div></td>';
                $logo .= '</tr>';
                
                $logo.='</table>';
                $logo .= '</td>';
            
                $logo .= '<td  width="315">';
                $logo .= '<table  style="margin: 0px; border: '.$empr_web_color.' 1px solid ; border-radius: 5px; width:100%;" >';

                $logo .= '<tr>';
                $logo .= '<td width="315"><b>Fecha de Pedido:</b> '.$pedf_hor_fin.'</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="315"><b>Email:</b> '.$pedf_email_clpv.'</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="315"><b>Vendedor:</b> ' . $nombre_vendedor . '</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="315"><b>Estado:</b> <font style="color: '.$color.';" ">' . $estado . '</font></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="315"><b>Zona:</b> '.$zona_cliente  .'</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="315"><b>Ciudad:</b> '.$pedf_lug_pedf.'</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="315"><b>Asesor:</b> '.$asesor.'</td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
            
                $logo .= '</tr>';
                $logo.='</table>';


                $cliente .= ' <table style="width: 100%; margin-top:7px;font-size: 80%;" cellspacing="1" cellpadding="1">';
				$cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%;" align="left"><b>NOMBRE COMERCIAL:</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $nombre_comercial . '</td>';
                $cliente .= ' <td style="width: 15%;"><b>FECHA PEDIDO:</b></td>';
                $cliente .= ' <td style="width: 30%;">'.$pedf_hor_fin.'</td>';
                $cliente .= ' </tr>';
				
				$cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%"><b>CLIENTE:</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $nombre_cliente . '</td>';
                $cliente .= ' <td style="width: 15%;"><b>IDENTIFICACION:</b></td>';
                $cliente .= ' <td style="width: 30%;">' . $ruc_cliente . '</td>';
                $cliente .= ' </tr>';

			    $cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%;"><b>TELEFONO</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $telefono . '</td>';
				$cliente .= ' <td style="width: 15%;"><b>EMAIL:</b></td>';
                $cliente .= ' <td style="width: 30%;">' . $pedf_email_clpv . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%;"><b>DIRECCION:</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $direccion . '</td>';
                $cliente .= ' <td style="width: 15%;"><b>VENDEDOR:</b></td>';
                $cliente .= ' <td style="width: 30%;">' . $nombre_vendedor . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%;"><b>SUBCLIENTE:</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $ccli_nom_conta . '</td>';
                $cliente .= ' <td style="width: 15%;"><b>ESTADO:</b></td>';
                $cliente .= ' <td style="width: 30%;color: '.$color.';">' . $estado . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%;"><b>OBSERVACIONES:</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $pedf_cm1_pedf . '</td>';
                $cliente .= ' <td style="width: 15% "><b>CIUDAD:</b></td>';
                $cliente .= ' <td style="width: 30%;">' . $pedf_lug_pedf . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%;"><b>ZONA:</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $zona_cliente . '</td>';
                $cliente .= ' <td style="width: 15%;" ><b>ASESOR:</b></td>';
                $cliente .= ' <td style="width: 30%;">' . $asesor . '</td>';
                $cliente .= ' </tr>';
                $cliente .= ' </table><br><br>';



        }
    }
    $oIfx->Free();

    $sqlDeta = "SELECT
        dpef_cant_dfac,
        dpef_cod_prod,
        dpef_nom_prod,
        dpef_precio_dfac,
        dpef_des1_dfac,
        dpef_des2_dfac,
        dpef_por_dsg,
        dpef_mont_total,
        dpef_det_dpef,
        dpef_cod_bode,
        dpef_cod_lote,
        dpef_cod_unid,
        prod_apli_prod,
        marc_des_marc,
        prbo_dis_prod 
    FROM
        saedpef d
        INNER JOIN saeprbo b ON d.dpef_cod_prod = b.prbo_cod_prod 
        AND d.dpef_cod_empr = b.prbo_cod_empr 
        AND d.dpef_cod_sucu = b.prbo_cod_sucu
        AND d.dpef_cod_bode = b.prbo_cod_bode
        INNER JOIN saeprod P ON d.dpef_cod_prod = P.prod_cod_prod 
        AND d.dpef_cod_empr = P.prod_cod_empr 
        AND d.dpef_cod_sucu = P.prod_cod_sucu
        LEFT JOIN saemarc M ON P.prod_cod_marc = M.marc_cod_marc 
        AND P.prod_cod_empr = M.marc_cod_empr 
    WHERE
    dpef_cod_pedf = $id and dpef_cod_sucu = $idSucursal and dpef_cod_empr = $idEmpresa";

   
    $deta .= ' <table style="width: 99%;  font-size: 13px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:10px;margin-left:2px;" cellspacing=0>';
    $deta .= ' <tr>';
    $deta .= ' <b> <td style="border-top-left-radius: 4px; background: '.$empr_web_color.'; color:white; width: 5%; font-size:13px;" align="center" height="30">ITEM</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 15%; font-size:13px;" align="center" height="30">CODIGO</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 27%; font-size:13px;" align="center" height="30">DESCRIPCIÓN</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 5%; font-size:13px;" align="center" height="30">UM</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 15%; font-size:13px;" align="center" height="30">LOTE</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width:  10%; font-size:13px;" align="center" height="30">CANTIDAD</td> </b>';
    $deta .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width:  11%; font-size:13px;" align="center" height="30">PRECIO</td> </b>';
    $deta .= ' <b> <td style="border-top-right-radius: 4px; background: '.$empr_web_color.'; color:white; width: 12%; font-size:13px;" align="center" height="30">IMPORTE</td> </b>';
    $deta .= ' </tr>';

    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            $i = 1;
            do {
                $dfac_cod_prod = $oIfx->f('dpef_cod_prod');

                $aplicacion = $oIfx->f('prod_apli_prod');
                $marca = $oIfx->f('marc_des_marc');
                $stock = $oIfx->f('prbo_dis_prod');

                $dfac_nom_prod = $oIfx->f('dpef_nom_prod');
                $dfac_cant_dfac = $oIfx->f('dpef_cant_dfac');
                $dfac_precio_dfac = $oIfx->f('dpef_precio_dfac');
                $dfac_des1_dfac = $oIfx->f('dpef_des1_dfac');
                $dfac_des2_dfac = $oIfx->f('dpef_des2_dfac');
                $dfac_por_dsg = $oIfx->f('dpef_por_dsg');
                $dfac_mont_total = $oIfx->f('dpef_mont_total');
                $dpef_det_dpef = $oIfx->f('dpef_det_dpef');
                $dpef_cod_bode = $oIfx->f('dpef_cod_bode');
				$dpef_cod_unid = $oIfx->f('dpef_cod_unid');
                $dpef_cod_lote = $oIfx->f('dpef_cod_lote');
				
	
                $porc_descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;
                $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;
                if ($descuento > 0)
                    $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                else
                    $descuento = 0;

                $totalDescuento = $totalDescuento + $descuento;

                $deta .= ' <tr>';
                $deta .= ' <td style="width: 5%;" align="center">'.$i.'</td>';
                $deta .= ' <td style="width: 15%; border-left: '.$empr_web_color.' 0.7px solid;">'.$dfac_cod_prod.'</td>';
                $deta .= ' <td style="width: 27%; border-left: '.$empr_web_color.' 0.7px solid;">'.$dfac_nom_prod.'</td>';
                $deta .= ' <td align="center" style="width: 5%; border-left: '.$empr_web_color.' 0.7px solid;">' . $arrayUnidad[$dpef_cod_unid] . '</td>';
                $deta .= ' <td style="width: 15%; border-left: '.$empr_web_color.' 0.7px solid;">'.$dpef_cod_lote.'</td>';
                $deta .= ' <td align="right" style="width: 10%; border-left: '.$empr_web_color.' 0.7px solid;">' . number_format($dfac_cant_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td align="right" style="width: 11%; border-left: '.$empr_web_color.' 0.7px solid;">' . number_format($dfac_precio_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td align="right" style="width: 12%; border-left: '.$empr_web_color.' 0.7px solid;">' . number_format($dfac_mont_total, 2, '.', ',') . '</td>';
                $deta .= ' </tr>';
                $i++;
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


   
    $totales .= ' </table>';


    $totales.='</td>
    </tr>';
    $totales .='</table>';

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
		
		$hora_impr = date('Y-m-d H:i:s');
		
		$sqlUpdate = "update saepedf set pedf_est_impr = 'S', pedf_hor_impr = '$hora_impr' where pedf_cod_empr = $idEmpresa and pedf_cod_pedf = $id";
		$oIfx->QueryT($sqlUpdate);
	}


    $documento .= '<page backimgw="95%" backtop="5mm" backbottom="5mm" backleft="5mm" backright="5mm" footer="date;heure;page">';
    $documento .= $logo . $deta . $totales. $tableLeyenda;
    $documento .= '</page>';

    $html2pdf = new HTML2PDF('P', 'A4', 'es');
    $html2pdf->WriteHTML($documento);
    $html2pdf->Output($id.'.pdf');



?>