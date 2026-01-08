<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<?

include_once('../../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
include_once DIR_FACTELEC."Include/Librerias/barcode1/vendor/autoload.php";

	if (isset($_REQUEST['codigo'])){
		$idgui = $_REQUEST['codigo'];
	}else{
		$idgui = 'NULL';
	}
	
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

global $DSN_Ifx, $DSN;

// conexxion

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();


$oIfx2 = new Dbo;
$oIfx2->DSN = $DSN_Ifx;
$oIfx2->Conectar();


$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();

$idEmpresa      = $_SESSION['U_EMPRESA'];

$dia=date('d');
$mes=date('m');
$anio=date('y');

//array unidad
		$sql = "select unid_cod_unid, unid_nom_unid,unid_sigl_unid 
				from saeunid";
		if ($oIfx->Query($sql)) {
			if ($oIfx->NumFilas() > 0) {
				unset($arrayUnidad);
				do {
					$arrayUnidad[$oIfx->f('unid_cod_unid')] = $oIfx->f('unid_sigl_unid');
				} while ($oIfx->SiguienteRegistro());
			}
		}
		$oIfx->Free();

    $sql = "select empr_web_color, empr_cod_pais,empr_cm1_empr, empr_rimp_sn, empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_img_rep, empr_iva_empr,empr_tel_resp, empr_ac1_empr, empr_ac2_empr, empr_mai_empr, empr_tip_empr
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

    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;


    //CABECERA DE LA FACTURA
    
    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];


    if (file_exists($path_logo_img)) {
        $logo_empresa='<img width="100px;"  src="' . $path_logo_img . '">';
    }
    else{
        $logo_empresa='<div style="color:red;">LOGO NO CARGADO</div>';
    }

    $sqlfac="select g.guia_cod_guia, g.guia_cod_sucu, g.guia_cod_guia, g.guia_fech_guia, g.guia_num_preimp, g.guia_ruc_clie, 
    g.guia_nom_cliente, g.guia_iva, g.guia_con_miva, g.guia_tot_guia,g.guia_est_guia,
    g.guia_sin_miva, g.guia_email_clpv, g.guia_erro_sri , c.clv_con_clpv,
    g.guia_tlf_cliente, g.guia_dir_clie, g.guia_hos_guia, g.guia_hol_guia,
    g.guia_num_plac, g.guia_cm3_guia,g.guia_cm1_guia,  g.guia_cod_trta, g.guia_nse_guia, g.guia_cod_clpv,
    g.guia_clav_sri, g.guia_aprob_sri, g.guia_cod_vend, g.guia_ciu_des,g.guia_tip_entr, g.guia_cod_hash
    from saeguia g, saeclpv c where 
    c.clpv_cod_clpv = g.guia_cod_clpv and
    c.clpv_cod_empr = $idEmpresa and
    c.clpv_clopv_clpv = 'CL' and
    g.guia_cod_empr = $idEmpresa
    and g.guia_cod_guia=$idgui  
    order by g.guia_num_preimp ";
    if ($oIfx->Query($sqlfac)) {
        if ($oIfx->NumFilas() > 0) {
            $detalle='<table style="margin-top:39px; font-size: 10px; width: 91%;" cellpadding="1" cellspacing="1" border="0" align="left" >';
            $ctrl=0;
            do {

                $guia_fech_guia    = $oIfx->f('guia_fech_guia');

                $dia_gui=date('d',strtotime($guia_fech_guia));
                $mes_gui=date('m',strtotime($guia_fech_guia));
                $anio_gui=date('y',strtotime($guia_fech_guia));
                
                $guia_num_preimp   = $oIfx->f('guia_num_preimp');
                $guia_cod_hash   = $oIfx->f('guia_cod_hash');
                $guia_nom_cliente  = $oIfx->f('guia_nom_cliente');
                $guia_ruc_clie     = $oIfx->f('guia_ruc_clie');
                $guia_est_guia     = $oIfx->f('guia_est_guia');
                $guia_cod_sucu     = $oIfx->f('guia_cod_sucu');

                $sqls="select sucu_dir_sucu, sucu_telf_secu from saesucu where sucu_cod_sucu=$guia_cod_sucu";
                $guia_dir_sucu   =consulta_string($sqls,'sucu_dir_sucu', $oIfxA,'');
                $guia_telf_sucu   =consulta_string($sqls,'sucu_telf_secu', $oIfxA,'');

                $guia_clav_sri     = $oIfx->f('guia_clav_sri');
                $guia_email_clpv = $oIfx->f('guia_email_clpv');
                $guia_cm3_guia = $oIfx->f('guia_cm3_guia');
                $guia_cm1_guia = $oIfx->f('guia_cm1_guia');
                $guia_nse_guia = $oIfx->f('guia_nse_guia');
                $guia_cod_trta = $oIfx->f('guia_cod_trta');
                $guia_sal_guia = $oIfx->f('guia_sal_guia');
                $guia_tlf_cliente = $oIfx->f('guia_tlf_cliente');
    
                $guia_num_plac = $oIfx->f('guia_num_plac');
                $guia_hos_guia = $oIfx->f('guia_hos_guia');
                $guia_hol_guia = $oIfx->f('guia_hol_guia');
                $guia_dir_clie = trim($oIfx->f('guia_dir_clie'));
                $guia_tot_guia = $oIfx->f('guia_tot_guia');
                
                $guia_ciu_des = $oIfx->f('guia_ciu_des');
                $guia_cod_clpv = $oIfx->f('guia_cod_clpv');
                if(empty($guia_ciu_des)){
                    $guia_ciu_des='NULL';
                }
                $guia_cod_trta = $oIfx->f('guia_cod_trta');
    
                if(empty($guia_dir_clie)){
    
                    //DIRCCION DE LA SAECLPV
    
                    $sqlcli="select sp_direcciones(saeclpv.clpv_cod_empr,clpv_cod_sucu,saeclpv.clpv_cod_clpv) direccion from saeclpv where clpv_cod_clpv= $guia_cod_clpv";
                    $guia_dir_clie = consulta_string($sqlcli,'direccion', $oIfxA,'');
                }   
                
                $sql = "select ciud_cod_ciud, ciud_nom_ciud  from saeciud where ciud_cod_ciud = $guia_ciu_des";
                $ciud_des = consulta_string($sql, 'ciud_nom_ciud', $oIfxA, '');
    
    
                $guia_ciu_ori = $oIfx->f('guia_ciu_ori');
                if(empty($guia_ciu_ori)){
                    $guia_ciu_ori='NULL';
                }
                $sql = "select ciud_cod_ciud, ciud_nom_ciud  from saeciud where ciud_cod_ciud = $guia_ciu_ori";
                $ciud_ori = consulta_string($sql, 'ciud_nom_ciud', $oIfxA, '');



                //DATOS DEL TRANSPORTISTA
                if (!empty($guia_cod_trta)) {
                    $sqlTran = "select * from saetrta where trta_cod_trta = $guia_cod_trta and trta_cod_empr = $idEmpresa ";
                } else {
                    $sqlTran = "select * from saetrta where trta_cid_trta = '$guia_sal_guia' and trta_cod_empr = $idEmpresa ";
                }


                if ($oIfx2->Query($sqlTran)) {
                    if ($oIfx2->NumFilas() > 0) {
                        do {
                            //$oReturn->alert('si');
                            $trta_nom_trta = $oIfx2->f("trta_nom_trta");
                            $trta_cid_trta = $oIfx2->f("trta_cid_trta");
                            $trta_num_mtc = trim($oIfx2->f("trta_num_mtc"));


                        } while ($oIfx2->SiguienteRegistro());
                    }
                }

                $oIfx2->Free();

                $sqltip="select resp_flete_clpv,atencion_ofi_clpv,cond_vent_clpv from saeclpv where clpv_cod_clpv=$guia_cod_clpv";
                $resp_flete = consulta_string($sqltip, 'resp_flete_clpv', $oIfxA, '');
                $atencion_ofi = consulta_string($sqltip, 'atencion_ofi_clpv', $oIfxA, '');
                $cond_vent_clpv = consulta_string($sqltip, 'cond_vent_clpv', $oIfxA, '');


                $guia_tip_entr = $oIfx->f('guia_tip_entr');
                //TIPO DE ENTREGA

                    if($guia_tip_entr==''){
                        $sqltip="select tip_entrega_clpv from saeclpv where clpv_cod_clpv=$guia_cod_clpv";
                        $tipo_entrega = strtoupper(consulta_string($sqltip, 'tip_entrega_clpv', $oIfxA, ''));

                        if(empty($tipo_entrega)){
                            $tipo_entrega='DOMICILICIO';
                        }
                    }
                    else{
                        $tipo_entrega= strtoupper($guia_tip_entr);
                    }

                    if($tipo_entrega=='OFICINA'){
                        $info_transpor='<br>';
                    }
                




                $logo ='<table border="0"  style="width: 100%;"  cellspacing="0">';
                $logo .= '<tr>';
            
                $logo .= '<td align="left" width="420">';
                $logo .= '<table  style="margin: 0px;">';
                $logo .= '<tr>';
                $logo .= '<td align="center">'.$logo_empresa.'</td>';
                $logo .= '<td width="335" style="font-size:15px;"><div style="margin-left:10px"><b>' . $razonSocial . '</b><br>'.$dirMatriz.'<br>
                <b>Telf:</b>'.$tel_empresa.'<br><b>Celular:</b> '.$guia_telf_sucu.'<br><b>Web:</b> www.ecovalle.pe </div></td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
                
                $logo .= '<td align="left" width="260">';
                $logo .= '<table  style="border: '.$empr_web_color.' 1px solid ; border-radius: 5px; " cellspacing=0>';
            
                $logo .= '<tr style="font-size:16px;">';
                $logo .= '<td width="260" height="35" align="center"><b>R.U.C. N° ' . $ruc_empr . '</b></td>';
                $logo .= '</tr>';
            
                $logo .= '<tr style="font-size:16px;">';
                $logo .= '<td width="260"  height="35" style="background: '.$empr_web_color.'; color:white;" align="center"><b>GUIA DE REMISIÓN ELECTRÓNICA REMITENTE</b></td>';
                $logo .= '</tr>';
                
                $logo .= '<tr style="font-size:16px;">';
                $serie=substr($guia_nse_guia, 3, 4);

                $logo .= '<td width="260" height="35" align="center" ><b>'.$serie.'-' . $guia_num_preimp.'</b></td>';
                //$logo .= '<td align="center" width=220>Nro. '.$nse_fact.'-0000000000000012</td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
            
                $logo .= '</tr>';
                $logo.='</table>';


                $logo .='<table  style="border: '.$empr_web_color.' 1px solid ; border-radius: 5px; width: 80%; margin-top:4px;font-size:12px;">';
                $logo .= '<tr>';
            
                $logo .= '<td  width="340" >';
                $logo .= '<table  style="margin: 0px; width:100%;" >';
                $logo .= '<tr>';
                $logo .= '<td width="340"><div style="margin-left:3px"><b>Fecha de Inicio:</b> '.$guia_hos_guia.'</div></td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="340"><div style="margin-left:3px"><b>Direccion de Partida:</b> '.$guia_dir_sucu .'</div></td>';
                $logo .= '</tr>';
                $logo.='</table>';
                $logo .= '</td>';
            
            
                $logo .= '<td  width="341">';
                $logo .= '<table  style="margin: 0px;width:100%;" >';
                $logo .= '<tr>';
                $logo .= '<td width="341"><b>Fecha de Fin:</b> '.$guia_hol_guia.'</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td width="341"><b>Direccion de llegada:</b> '. $guia_dir_clie.'</td>';
                $logo .= '</tr>';
                
                $logo.='</table>';
                $logo .= '</td>';
            
                $logo .= '</tr>';
                $logo.='</table>';


              





                //SI L;A CONFIGURACION ES GUIA-FACTURA O PEDIDO-GUIA
            $para_sec_para = consulta_string("select para_sec_para from saepara where para_cod_empr = $idEmpresa
            and para_cod_sucu =$guia_cod_sucu", "para_sec_para", $oIfxA, '');

            $sqlDetaGuia = "select dgui_fac_dgui from saedgui where dgui_cod_guia = $idgui and dgui_cod_empr = $idEmpresa
            and dgui_cod_sucu = $guia_cod_sucu group by dgui_fac_dgui";

            if ($oIfxA->Query($sqlDetaGuia)) {
                if ($oIfxA->NumFilas() > 0) {
        
                  
                    do {
                     
        
                        $dgui_fac_dgui = $oIfxA->f("dgui_fac_dgui");
        
                        if ($dgui_fac_dgui != '' && $para_sec_para == 1) {
                            $sqlFact = "select fact_nse_fact, fact_num_preimp, fact_auto_sri, fact_fech_fact from saefact where
                                            fact_cod_fact = $dgui_fac_dgui and fact_cod_empr = $idEmpresa and fact_cod_sucu = $guia_cod_sucu";
        
                            if ($oIfx2->Query($sqlFact)) {
                                if ($oIfx2->NumFilas() > 0) {
                                    do {
                                        $serie = substr($oIfx2->f("fact_nse_fact"), 0, 3);
                                        $ptoEmi = substr($oIfx2->f("fact_nse_fact"), 3, 3);
                                        $fact_num_preimp = $oIfx2->f("fact_num_preimp");
                                        $fact_auto_sri = $oIfx2->f("fact_auto_sri");
                                        $fact_fech_fact = $oIfx2->f("fact_fech_fact");

                                        $fact_ruc_clie = $oIfx->f('fact_ruc_clie');

                                        $tipo_pdf=substr($oIfx2->f("fact_nse_fact"), 3, 1);
                                        $tipodoc='BOLETA';
                                        if($tipo_pdf=='F'){
                                            $tipodoc='FACTURA';
                                        }
                                        elseif($tipo_pdf=='B'){
                                            $tipodoc='BOLETA';
                                        }
                                        else{
                                            $tipodoc='FACTURA';
                                        
                                        }
        
                                        $numDocSustento = $fact_num_preimp;
        
                                        
    
        
                                    } while ($oIfx2->SiguienteRegistro());
                                }
                            }
                            $oIfx2->Free();
        
                        
                        }             
        
                        if ($dgui_fac_dgui != '' && $para_sec_para == 1) {
                           

                            $sqlDeta = "select dgui_cod_prod, dgui_cant_dgui, dgui_nom_prod, 
                            dgui_des1_dgui, dgui_des2_dgui, dgui_des3_dgui, dgui_des4_dgui, dgui_por_dsg, dgui_cod_unid, dgui_precio_dgui, dgui_cod_lote   from saedgui where dgui_fac_dgui = '$dgui_fac_dgui' and dgui_cod_guia = $idgui
                                            and dgui_cod_empr = $idEmpresa and dgui_cod_sucu = $guia_cod_sucu";
                        } else if ($dgui_fac_dgui == '' && $para_sec_para == 1)
                            $sqlDeta = "select dgui_cod_prod, dgui_cant_dgui, dgui_nom_prod, dgui_cod_lote,
                            dgui_des1_dgui, dgui_des2_dgui, dgui_des3_dgui, dgui_des4_dgui, dgui_por_dsg, dgui_cod_unid, dgui_precio_dgui from saedgui where (dgui_fac_dgui = ''  or  dgui_fac_dgui is null ) and dgui_cod_guia = $idgui
                                            and dgui_cod_empr = $idEmpresa and dgui_cod_sucu = $guia_cod_sucu";
                        else
                            $sqlDeta = "select dgui_cod_prod, dgui_cant_dgui, dgui_nom_prod, dgui_cod_lote,
                            dgui_des1_dgui, dgui_des2_dgui, dgui_des3_dgui, dgui_des4_dgui, dgui_por_dsg, dgui_cod_unid, dgui_precio_dgui from saedgui where dgui_cod_guia = $idgui
                                            and dgui_cod_empr = $idEmpresa and dgui_cod_sucu = $guia_cod_sucu";

                        $detalle = ' <table style="width: 99%;  font-size: 13px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:10px;margin-left:2px;" cellspacing=0>';
                        $detalle .= ' <tr>';
                        $detalle .= ' <b> <td style="border-top-left-radius: 4px; background: '.$empr_web_color.'; color:white; width: 20%; font-size:13px;" align="center" height="30">CODIGO</td> </b>';
                        $detalle .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 15%; font-size:13px;" align="center" height="30">UNIDAD</td> </b>';
                        $detalle .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width:  15%; font-size:13px;" align="center" height="30">LOTE</td> </b>';
                        $detalle .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width:  40%; font-size:13px;" align="center" height="30">DESCRIPCION</td> </b>';
                        $detalle .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width:  10%;" height="30" align="center">CANTIDAD</td> </b>';
                        //$detalle .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width:  10%;" height="30" align="center">DSCTO %</td> </b>';
                       // $detalle .= ' <b> <td style="border-top-right-radius: 4px; background: '.$empr_web_color.'; color:white; width: 10%; font-size:13px;" align="center" height="30">IMPORTE</td> </b>';
                        $detalle .= ' </tr>';                        

        
                        if ($oIfx2->Query($sqlDeta)) {
                            if ($oIfx2->NumFilas() > 0) {
                                
                                $a = 0;
                                do {
                                    $codigoInterno = $codigoAdicional = $oIfx2->f("dgui_cod_prod");
        
                                   // $sql="SELECT tipo_merc, cantidad from agricola.despacho_prod";
        
                                    $cantidad = $oIfx2->f("dgui_cant_dgui");
                                    $descripcion = $oIfx2->f("dgui_nom_prod");
                                    $lote = $oIfx2->f("dgui_cod_lote");
                                    $unidad = $oIfx2->f("dgui_cod_unid");
                                    $desc_unid=$arrayUnidad[$unidad];
                                    $precio = $oIfx2->f("dgui_precio_dgui");

                                    $dgui_des1_dgui = $oIfx2->f("dgui_des1_dgui");
                                    $dgui_des2_dgui = $oIfx2->f("dgui_des2_dgui");
                                    $dgui_des3_dgui = $oIfx2->f("dgui_des3_dgui");
                                    $dgui_des4_dgui = $oIfx2->f("dgui_des4_dgui");
                                    $dgui_por_dsg = $oIfx2->f("dgui_por_dsg");
                                    if(empty($dgui_por_dsg)){
                                        $dgui_por_dsg=0;
                                    }


                                    $porcentaje_descuento=$dgui_des1_dgui + $dgui_des2_dgui + $dgui_des3_dgui  + $dgui_por_dsg;

                                

                                    $detalle .= '<tr>';
                                    $detalle .= '<td align="center" style="width:20%;">' . $codigoInterno . '</td>';
                                    $detalle .= '<td style="width:15%;" align="center" >' . $desc_unid . '</td>';
                                    $detalle .= '<td style="width:15%;" align="center" >' . $lote . '</td>';
                                    $detalle .= '<td style="width:40%;">' . $descripcion . '</td>';
                                    $detalle .= '<td  align="center" style="width:10%;">' . round($cantidad, 0) . '</td>';
                                    //$detalle .= '<td style="width:14%;" align="right">' . number_format($precio, 2, '.', ',') . '</td>';
                                    //$detalle .= '<td style="width:10%;" align="center">' . number_format($porcentaje_descuento, 2, '.', ',') . '</td>';
                                    //$detalle .= '<td style="width:10%;" align="center">' . number_format($cantidad*$precio, 2, '.', ',') . '</td>';
        
                                    $detalle .= '</tr>';
                   
                                    $a = $a + $cantidad;
                                    $num++;
                                    $ctrl++;
                                } while ($oIfx2->SiguienteRegistro());

                                    $detalle .= '<tr>';
                                    $detalle .= '<td align="center" style="border-top: '.$empr_web_color.' 1px solid; 1px solid; ;width:20%;"></td>';
                                    $detalle .= '<td style="border-top: '.$empr_web_color.' 1px solid; 1px solid;width:15%;" align="center" ></td>';
                                    $detalle .= '<td style="border-top: '.$empr_web_color.' 1px solid; 1px solid;width:15%;" align="center" ></td>';
                                    $detalle .= '<td style="border-top: '.$empr_web_color.' 1px solid;border-right: '.$empr_web_color.' 1px solid;width:40%;"></td>';
                                    $detalle .= '<td  align="center" style="border-top: '.$empr_web_color.' 1px solid; width:10%;">' . round($a, 0) . '</td>';
                                    //$detalle .= '<td style="width:14%;" align="right">' . number_format($precio, 2, '.', ',') . '</td>';
                                    //$detalle .= '<td style="width:10%;" align="center">' . number_format($porcentaje_descuento, 2, '.', ',') . '</td>';
                                    //$detalle .= '<td style="width:10%;" align="center">' . number_format($cantidad*$precio, 2, '.', ',') . '</td>';
        
                                    $detalle .= '</tr>';
                                
                                $detalle .= '</table>';
                            }
                        }

                        $oIfx2->Free();


                          //DATOS DE TRANSPORTE

                $transportista = ' <table style="width: 99%;  font-size: 13px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:10px;margin-left:2px;" cellspacing=0;>';
                $transportista .= ' <tr>';
                $transportista .= ' <b> <td style="border-right: white 1px solid; border-top-left-radius: 4px; background: '.$empr_web_color.'; color:white; width: 70%; font-size:13px;" align="left" height="30">DATOS DEL TRANSPORTE:</td> </b>';
                $transportista .= ' <b> <td style="background: '.$empr_web_color.'; color:white; width: 30%; font-size:13px;" align="left" height="30">COMPROBANTE DE PAGO:</td> </b>';
                $transportista .= ' </tr>';

                $transportista .= ' <tr>';

                $transportista .= '<td  style=" width: 70%; border-right: '.$empr_web_color.' 1px solid; font-size:13px;" align="left">';
                $transportista .= '<table  style="width: 100%;margin: 0px;"  >';
                $transportista .= '<tr>';
                $transportista .= '<td width="50%"><b>Nombre:</b> '.$trta_nom_trta.'</td>';
                $transportista .= '<td width="50%"><b>Placa:</b> '.$guia_num_plac.'</td>';
                $transportista .= '</tr>';
                $transportista .= '<tr>';
                $transportista .= '<td width="50%"><b>R.U.C:</b> '.$trta_cid_trta.'</td>';
                $transportista .= '<td width="50%"><b>N° Registro MTC:</b> '.$trta_num_mtc.' </td>';
                $transportista .= '</tr>';
                $transportista.='</table>';
                $transportista .= '</td>';

                $transportista .= '<td  style=" width: 30%; font-size:13px;" align="left">';
                $transportista .= '<table  style="width: 100%;margin: 0px;" >';
                $transportista .= '<tr>';
                $transportista .= '<td width="30%"><b>Tipo:</b></td>';
                $transportista .= '<td width="70%">'.$tipodoc.'</td>';
                $transportista .= '</tr>';
                $transportista .= '<tr>';
                $transportista .= '<td width="30%"><b>Nro:</b></td>';
                $transportista .= '<td width="70%">'.$numDocSustento.'</td>';
                $transportista .= '</tr>';
                $transportista.='</table>';
                $transportista .= '</td>';

                $transportista .= ' </tr>';


                $transportista .= ' </table>';


                //DATOS DE DESTINATARIO


                $destinatario = ' <table style="width: 99%;  font-size: 13px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:10px;margin-left:2px;" cellspacing=0>';
                $destinatario .= ' <tr>';
                $destinatario .= ' <b> <td colspan="4" style="border-top-left-radius: 4px; background: '.$empr_web_color.'; color:white; width: 100%; font-size:13px;" align="left" height="30">DATOS DEL DESTINATARIO:</td> </b>';
                $destinatario .= ' </tr>';

                $destinatario .= ' <tr>';
                $destinatario .= '<td style="width: 20%;"><b>NOMBRE</b></td>';
                $destinatario .= '<td style="width: 30%;">'.$guia_nom_cliente.'</td>';
                $destinatario .= '<td style="width: 20%;"><b>DIRECCION</b></td>';
                $destinatario .= '<td style="width: 30%;">'.$guia_dir_clie.'</td>';
                $destinatario .= ' </tr>';

                $destinatario .= ' <tr>';
                $destinatario .= '<td style="width: 20%;"><b>DNI/RUC:</b></td>';
                $destinatario .= '<td style="width: 30%;">'.$guia_ruc_clie.'</td>';
                $destinatario .= '<td style="width: 20%;"><b>MOTIVO:</b></td>';
                $destinatario .= '<td style="width: 30%;">'.$guia_cm3_guia.'</td>';
                $destinatario .= ' </tr>';
                $destinatario .= ' <tr>';
                $destinatario .= '<td style="width: 20%;"><b>CONDICION DE VENTA:</b></td>';
                $destinatario .= '<td style="width: 30%;">'.$cond_vent_clpv.'</td>';
                $destinatario .= '<td style="width: 20%;"></td>';
                $destinatario .= '<td style="width: 30%;"></td>';
                $destinatario .= ' </tr>';

                $destinatario .= ' <tr>';
                $destinatario .= '<td style="width: 20%;"><b>CORREO:</b></td>';
                $destinatario .= '<td style="width: 30%;">'.$guia_email_clpv.'</td>';
                $destinatario .= '<td style="width: 20%;"><b>TELEFONO:</b></td>';
                $destinatario .= '<td style="width: 30%;">'.$guia_tlf_cliente.'</td>';
                $destinatario .= ' </tr>';

                $destinatario .= ' </table>';

                //DATOS DE ENTREGA

                $transportista .= ' <table style="width: 99%;  font-size: 13px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:10px;margin-left:2px;" cellspacing=0;>';
                $transportista .= ' <tr>';
                $transportista .= ' <b> <td colspan="2" style="border-top-left-radius: 4px; background: '.$empr_web_color.'; color:white; width: 99%; font-size:13px;" align="left" height="30">DATOS DE ENTREGA:</td> </b>';
                $transportista .= ' </tr>';

                $transportista .= ' <tr>';

                $transportista .= '<td  style=" width: 50%; border-right: '.$empr_web_color.' 1px solid; font-size:13px;" align="left">';
                $transportista .= '<b>Tipo de Envio:</b> '.$tipo_entrega.'';
                $transportista .= '</td>';

                $transportista .= '<td  style=" width: 50%;  font-size:13px;" align="left">';
                $transportista .= '<b>Atención a:</b> '.$atencion_ofi.'';
                $transportista .= '</td>';

                $transportista .= '</tr>';


                $transportista .= ' <tr>';

                $transportista .= '<td  style=" width: 50%; border-right: '.$empr_web_color.' 1px solid; font-size:13px;" align="left">';
                $transportista .= '<b>Responsable de Flete:</b> '.$resp_flete.'';
                $transportista .= '</td>';

                $transportista .= '<td  style=" width: 50%;  font-size:13px;" align="left">';
                $transportista .= '';
                $transportista .= '</td>';

                $transportista .= '</tr>';
                $transportista.='</table>';


                    } while ($oIfxA->SiguienteRegistro());
                }
            }
            $oIfxA->Free();

                    


                

            }while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

//VARIBALES ESPACIOS ENTRE TEXTOS
$esp1=espacios_gui(18);
$esp2=espacios_gui(8);
$esp3=espacios_gui(8);

$esp4=espacios_gui(19);
$esp5=espacios_gui(19);
$esp6=espacios_gui(20);
$esp7=espacios_gui(66);

$esp8=espacios_gui(31);
$esp9=espacios_gui(30);
$esp10=espacios_gui(34);
$esp11=espacios_gui(12);

$esp12=espacios_gui(50);

 //DATOS DEL TRANSPORTISTA
 if (!empty($guia_cod_trta)) {
    $sqlTran = "select * from saetrta where trta_cod_trta = $guia_cod_trta and trta_cod_empr = $idEmpresa ";
} else {
    $sqlTran = "select * from saetrta where trta_cid_trta = '$guia_sal_guia' and trta_cod_empr = $idEmpresa ";
}


if ($oIfxA->Query($sqlTran)) {
    if ($oIfxA->NumFilas() > 0) {
        do {
            //$oReturn->alert('si');
            $nombre_transpor = $oIfxA->f("trta_nom_trta");
            $id_transpor = $oIfxA->f("trta_cid_trta");
            $id_trta = $oIfxA->f("trta_cod_trta");

        } while ($oIfxA->SiguienteRegistro());
    }
}
$oIfxA->Free();

if(empty($id_trta)){
    $id_trta='NULL';
}
// camion placa
$sql = "select  cami_num_plac,  cami_des_cami from saecami where
cami_cod_empr = $idEmpresa and
cami_cod_trta = $id_trta";
if ($oIfx->Query($sql)) {
    if ($oIfx->NumFilas() > 0) {
        do {
            $placa = $oIfx->f('cami_num_plac');

        } while ($oIfx->SiguienteRegistro());
    }
}
$oIfx->Free();



$tableLeyenda ='<table border="0"  style="width: 100%;font-size: 11px;" cellspacing="0">';
$tableLeyenda .= '<tr>';

$tableLeyenda .= '<td valign="top" width="500">';
$tableLeyenda .= '<table  height="500" style="font-size: 13px;   margin-top:10px;" cellspacing="0" >';

$tableLeyenda .= '<tr>';

//PRUEBA HASH 
/*$nombre_documento =$ruc_empr . '-' . $tipo_envio . '-' . $fact_nse_fact . '-' . $fact_num_preimp;
$ruta_xml = 'modulos/envio_documentos_comercial/upload/xml/fac_' . $nombre_documento . '.xml';
$hash= DIR_FACTELEC .$ruta_xml;
$xml = new SimpleXMLElement( file_get_contents($hash) );

$fact_cod_hash = utf8_decode(current($xml->xpath("//ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestValue")));

$sql="UPDATE saefact SET fact_cod_hash='$fact_cod_hash' WHERE fact_cod_fact = $id;";
$oIfx->QueryT($sql);*/

$tableLeyenda .= '<td width="500" height="88"></td>';
$tableLeyenda .= '</tr>';




$tableLeyenda.='</table>';
$tableLeyenda .= '</td>';


$tableLeyenda .= '<td valign="top" width="0.5"></td>
<td valign="top" width="192">';
$tableLeyenda .= '<table style="  font-size: 11px; border: '.$empr_web_color.' 1px; border-radius: 5px;  margin-top:10px;" cellspacing="0" >';

//CODIGO QR

$barcode = new \Com\Tecnick\Barcode\Barcode();

if(empty($guia_cod_hash)){
    $guia_cod_hash='La Guia '.$serie.'-'. $guia_num_preimp.' no se encuentra autorizada';
}

$datosqr=$guia_cod_hash;

$bobj = $barcode->getBarcodeObj(
    'QRCODE,H',                     // Tipo de Barcode o Qr
    $datosqr,          // Datos
    -2.5,                             // Width 
    -2.5,                             // Height
    'black',                        // Color del codigo
    array(-2, -2, -2, -2)           // Padding
    )->setBackgroundColor('white'); // Color de fondo

$imageData = $bobj->getPngData(); // Obtenemos el resultado en formato PNG
    

$ruta_dir = DIR_FACTELEC . 'modulos/envio_documentos_comercial/qr_guias';
    if (!file_exists($ruta_dir)){
        mkdir($ruta_dir,0777,true);
    }
    
file_put_contents(DIR_FACTELEC . 'modulos/envio_documentos_comercial/qr_guias/GUIA_'.$idgui.'.png', $imageData); // Guardamos el resultado

$ruta=DIR_FACTELEC . 'modulos/envio_documentos_comercial/qr_guias/GUIA_'.$idgui.'.png';



$tableLeyenda .= '<tr>';
$tableLeyenda .= '<td width="192" align="center"><img src="'.$ruta.'"></td>';

$tableLeyenda .= '</tr>';
$tableLeyenda.='</table>';
$tableLeyenda .= '</td>';

$tableLeyenda .= '</tr>';
$tableLeyenda .= '</table>';




$documento .= '<page backimgw="100%" backtop="5mm" backbottom="5mm" backleft="5mm" backright="5mm">';
//$documento .= $logo . $cliente . $deta . $totales . $tablePago. $tableLeyenda.$tablepago;
$documento .= $logo.$destinatario.$detalle.$tableLeyenda.'<br><br>'.$transportista.'<br><b>Observaciones:</b>'.$guia_cm1_guia;
//$documento .= $legend;
$documento .= '</page>';
   //ESPACIOS EN BLANCO
function espacios_gui($cant)
{
    $n="";
    for ($i=0; $i <=$cant ; $i++) {
        $n.="&nbsp;";
    }
    return $n;
}

	
    $html2pdf = new HTML2PDF('P', 'A4', 'es');
    $html2pdf->WriteHTML($documento);
    $html2pdf->Output('recibo_template.pdf', '');?>