


<?

function reporte_factura_personalizado($id = '', $nombre_archivo = '', $idSucursal ='', &$rutaPdf = '') {
    global $DSN_Ifx, $DSN;
    include_once DIR_FACTELEC."Include/Librerias/barcode1/vendor/autoload.php";
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
    $array_imp = $_SESSION['U_EMPRESA_IMPUESTO'];

    //$idSucursal = $_SESSION['U_SUCURSAL'];


    $sql = "select empr_iva_empr, empr_cod_pais, empr_cod_ciud  from saeempr where empr_cod_empr = $idEmpresa ";
    $empr_cod_pais = round(consulta_string($sql, 'empr_cod_pais', $oIfx, 0));
    $empr_cod_ciud = consulta_string($sql, 'empr_cod_ciud', $oIfx, 0);

    //DATOS PAIS - CIUDAD
    $sql = "select pais_des_pais from saepais where pais_cod_pais=$empr_cod_pais";
    $pais= consulta_string($sql,'pais_des_pais', $oCon,'');

    $sql="select ciud_nom_ciud from saeciud where ciud_cod_ciud=$empr_cod_ciud";
    $ciudad= consulta_string($sql,'ciud_nom_ciud', $oCon,'NA');

    $sql = "select empr_cod_prov, empr_web_color, empr_cod_pais,empr_cm1_empr, empr_rimp_sn, empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_img_rep, empr_iva_empr,empr_tel_resp, empr_ac1_empr, empr_ac2_empr, empr_mai_empr, empr_tip_empr
                                            from saeempr where empr_cod_empr = $idEmpresa ";


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_img_rep');
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
            $empr_cod_prov = $oIfx->f('empr_cod_prov');
            if(empty($empr_cod_prov)) $empr_cod_prov=0;
        }
    }
    $oIfx->Free();

    if(empty($empr_web_color)){
        $empr_web_color='black';
    }

    //

    $sql="select prov_des_prov from saeprov where prov_cod_prov=$empr_cod_prov";
    $dep_empr= consulta_string($sql,'prov_des_prov', $oCon,'');


   

    

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


    //CABECERA DE LA FACTURA
    
    // $path_logo_img = DIR_FACTELEC . 'imagenes/logos/' . $path_img[$count];
    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];


    if (file_exists($path_logo_img)) {
        $logo_empresa='<img width="200px;"  src="' . $path_logo_img . '">';
    }
    else{
        $logo_empresa='<div style="color:red;">LOGO NO CARGADO</div>';
    }


   
    ////DATOS DE LA FACTURA

    $sqlFac = "select right(fact_hor_ini,8) as fecha_hor_ini ,* from saefact where fact_cod_fact = $id;";
    if ($oIfx->Query($sqlFac)) {
        if ($oIfx->NumFilas() > 0) {
            do {

                $fact_cod_clpv = $oIfx->f('fact_cod_clpv');
                $fact_nse_fact = $oIfx->f('fact_nse_fact');
                $nse_fact = substr($fact_nse_fact, 3, 9);
                $tipo_pdf=substr($fact_nse_fact, 3, 1);
                

                if($tipo_pdf=='F'){
                    //$titulo='FACTURA ELECTRÓNICA';
                    $titulo='FACTURA';
                    $idcli='RUC:';  
                }
                elseif($tipo_pdf=='B'){
                    //$titulo='BOLETA DE VENTA <br> ELECTRÓNICA';
                    $titulo='BOLETA DE VENTA';
                    $idcli='DNI:';
                }
                $titulo='FACTURA';
                $fact_num_preimp = intval($oIfx->f('fact_num_preimp'));
                $fact_auto_sri = $oIfx->f('fact_auto_sri');
                $fact_aprob_sri = $oIfx->f('fact_aprob_sri');
                $fact_fech_sri = $oIfx->f('fact_fech_sri');
                $fact_fech_fact = $oIfx->f('fact_fech_fact');

                $fecha_corte= date("06/m/Y",strtotime($fact_fech_fact."+ 1 month"));

                $fact_hor_ini = $oIfx->f('fecha_hor_ini');

                $fact_fec_hor= $fact_fech_fact.' '.$fact_hor_ini;
                $fecha_gen=date('d/m/Y H:i',strtotime($fact_fec_hor));

                if(!empty($fact_fech_sri)){
                    $fact_fech_sri = date('d/m/Y',strtotime(substr($fact_fech_sri,0,10))).' '.substr($fact_fech_sri,11,8);
                }
                else{
                    $fact_fech_sri = $fact_fech_fact;
                }
                $fact_leye_fact = $oIfx->f('fact_leye_fact');
                if(empty($fact_leye_fact)){
                    $fact_leye_fact=$leyenda_factura;
                }
                $fact_nom_cliente = $oIfx->f('fact_nom_cliente');
               
                $fact_ruc_clie = $oIfx->f('fact_ruc_clie');
                $fact_tlf_cliente = $oIfx->f('fact_tlf_cliente');
                $fact_dir_clie = htmlentities($oIfx->f('fact_dir_clie'));
                $fact_email_clpv = str_replace(' ', '', $oIfx->f("fact_email_clpv"));
                if(!empty($fact_email_clpv)) $fact_email_clpv=strtoupper($fact_email_clpv);

                $fact_con_miva = $oIfx->f('fact_con_miva');
                $fact_cod_mone = $oIfx->f('fact_cod_mone');
                $fact_cod_hash = $oIfx->f('fact_cod_hash');
                $link_qr = $oIfx->f('fact_cod_hash');

                if(!empty($fact_cod_hash)){
                    $text_1=substr($fact_cod_hash,0,40);
                    $text_2=substr($fact_cod_hash,40,80);
            
                    $fact_cod_hash=$text_1.'<br>'.$text_2;
                }

                $fact_val_tcam = $oIfx->f('fact_val_tcam');
                
                $sql = "select mone_des_mone, mone_sgl_mone, mone_smb_mene from saemone where mone_cod_mone =  $fact_cod_mone;";
                $moneda= consulta_string($sql,'mone_des_mone', $oCon,'');
                $smbmone= consulta_string($sql,'mone_smb_mene', $oCon,'');
                $sigmone= consulta_string($sql,'mone_sgl_mone', $oCon,'');
               
               

                ///VALIDACION MONEDA
                $sqlmon="select pcon_mon_base, pcon_seg_mone from saepcon where pcon_cod_empr=$idEmpresa";
                $pcon_seg_mone= consulta_string($sqlmon,'pcon_seg_mone', $oCon,'');

                $pcon_mon_base=consulta_string($sqlmon,'pcon_mon_base', $oCon,'');

                if($fact_cod_mone==$pcon_seg_mone){
                $eti_mone=  substr($sigmone,0,2).$smbmone;
                }
                else{
                    $eti_mone= $smbmone;
                }

                //ETIQUETA LOCAL
                $sql = "select mone_des_mone, mone_sgl_mone, mone_smb_mene from saemone where mone_cod_mone =  $pcon_mon_base;";
                $sigmoneprin= consulta_string($sql,'mone_smb_mene', $oCon,'');

               
            

                $fact_sin_miva = $oIfx->f('fact_sin_miva');
                $fact_tot_fact = $oIfx->f('fact_tot_fact');
                $fact_iva = $oIfx->f('fact_iva');
               
                $fact_dsg_valo  = $oIfx->f('fact_dsg_valo');
                $fact_cm1_fact  = strtoupper(trim($oIfx->f("fact_cm1_fact")));
                $fact_cm2_fact  = $oIfx->f("fact_cm2_fact");
                $fact_cm4_fact  = $oIfx->f("fact_cm4_fact");
                $orden_compra   = $oIfx->f("fact_opc_fact");
                $fact_cod_clpv  = $oIfx->f("fact_cod_clpv");
                $fact_cod_detra  = $oIfx->f("fact_cod_detra");
                $cod_contrato =  $oIfx->f('fact_cod_contr');
                if($cod_contrato==0||empty($cod_contrato)){
                    $cod_contrato= trim($oIfx->f('fact_cm7_fac'));
                }

                //CONTRATO 

                $id_contrato=$cod_contrato;

                
                if(empty($cod_contrato)){
                    $cod_contrato='NULL';
                }

                

                //CODIGO CID CLIENTES

                $sql_cid="select codigo_cid from  isp.int_contrato_caja_pack where id_clpv=$fact_cod_clpv and id_contrato = $cod_contrato
                and estado in ('A','C','P')";
                if ($oIfxA->Query($sql_cid)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $codigo_cid= $oIfxA->f('codigo_cid').'<br>';
                        }while ($oIfxA->SiguienteRegistro());
                    }

                }
                $oIfxA->Free();



                $fact_cod_ccli  = $oIfx->f("fact_cod_ccli");
                $fact_dia_plazo = $oIfx->f("fact_dia_fact");
                $fact_fech_venc = $oIfx->f("fact_fech_venc");
                
                $fact_fec_hor= $fact_fech_venc.' '.$fact_hor_ini;
                $fecha_venc=date('d/m/Y H:i',strtotime($fact_fec_hor));

                $fact_cm3_fact  = $oIfx->f("fact_cm3_fact");

                $cod_contrato =  $oIfx->f('fact_cod_contr');
                if ($cod_contrato == 0 || empty($cod_contrato)) {
                    $cod_contrato = trim($oIfx->f('fact_cm7_fac'));
                }

                if (empty($cod_contrato)) {
                    $cod_contrato = 'NULL';
                }



            }while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

   /* $sql_oc = "SELECT orden_compra from isp.contrato_clpv where id_clpv = $fact_cod_clpv and
    id_empresa = $idEmpresa";
    $orden_compra = consulta_string_func($sql_oc, 'orden_compra', $oCon , '');*/


    //CODIGO DE SUCURSAL
    $codigoSucursal = 0;
    $sqlsuc = "select sucu_alias_sucu from saesucu where sucu_cod_empr= $idempresa and sucu_cod_sucu=$idSucursal";

    $codigoSucursal= consulta_string($sql,'sucu_alias_sucu', $oCon,'');


    $sql = "SELECT clv_con_clpv, sp_telefonos(saeclpv.clpv_cod_empr,clpv_cod_sucu,saeclpv.clpv_cod_clpv) telefono from saeclpv where clpv_cod_clpv = $fact_cod_clpv";
    $clv_con_clpv = consulta_string_func($sql, 'clv_con_clpv', $oCon, '');
    $telf_clpv = consulta_string_func($sql, 'telefono', $oCon, '');


     //TIPO DE IDENTIFICACION DEL CLIENTE
     //$sql_sucu = "SELECT identificacion from comercial.tipo_iden_clpv_pais where pais_cod_pais = $empr_cod_pais and id_iden_clpv = '$clv_con_clpv'";
      $sql_sucu="SELECT t.id_iden_clpv, t.identificacion, t.tipo, c.identificacion AS iden, c.digitos
								FROM comercial.tipo_iden_clpv t , comercial.tipo_iden_clpv_pais c  WHERE
								t.id_iden_clpv = c.id_iden_clpv AND c.pais_cod_pais =$empr_cod_pais and t.tipo='$clv_con_clpv'";
    
     if ($oIfx->Query($sql_sucu)) {
         if ($oIfx->NumFilas() > 0) {
             do {
                 $tip_iden_cliente = $oIfx->f('identificacion');
             } while ($oIfx->SiguienteRegistro());
         }
     }
     $oIfx->Free();



        //query forma de pago
    $sqlFPago = "select fx.fxfp_cod_fxfp, fx.fxfp_fec_fin, fp.fpag_cod_fpagop, fx.fxfp_val_fxfp, fx.fxfp_num_dias,
				fpg.fpagop_des_fpagop,fp.fpag_des_fpag
				from saefact f, saefxfp fx, saefpag fp, saefpagop fpg
				where 
                f.fact_cod_fact = fx.fxfp_cod_fact and
				fp.fpag_cod_fpag = fx.fxfp_cod_fpag and
                f.fact_cod_empr = fpg.fpagop_cod_empr and
				fp.fpag_cod_fpagop = fpg.fpagop_cod_fpagop and
				f.fact_cod_empr = $idEmpresa and
                fp.fpag_cod_empr=$idEmpresa and 
				f.fact_cod_sucu = $idSucursal and
				f.fact_cod_fact =$id order by 1";
     
    if ($oIfx->Query($sqlFPago)) {
        if ($oIfx->NumFilas() > 0) {
            $numero_dias='';
            do {
                $fpag_cod_fpagop    = $oIfx->f('fpag_cod_fpagop');
                $fxfp_val_fxfp      = $oIfx->f('fxfp_val_fxfp');
                if($fact_cod_mone==$pcon_seg_mone)
                {
                    $fxfp_val_fxfp = $fxfp_val_fxfp/$fact_val_tcam;
                }
                $fxfp_num_dias      = $oIfx->f('fxfp_num_dias');
                if($fxfp_num_dias>0) $numero_dias=$fxfp_num_dias.' DÍAS';
                $fpagop_des_fpagop  = $oIfx->f('fpag_des_fpag');
                $fxfp_cod_fxfp   = $oIfx->f('fxfp_cod_fxfp');
                $fxfp_fec_fin = date('d/m/Y',strtotime($oIfx->f('fxfp_fec_fin')));

             
            } while ($oIfx->SiguienteRegistro());
        }        
    }
    $oIfx->Free();

   
    //CODIGO QR

    $barcode = new \Com\Tecnick\Barcode\Barcode();

    $datosqr='DOCUMENTO NO AUTORIZADO|'.$ruc_empr.'|'.$nse_fact.'|'.$fact_num_preimp.'|'.number_format($fact_tot_fact, 2, '.', ',').'|'.$fact_fech_fact.'|'.$tipo_docu.'|'.$fact_ruc_clie;
    if($fact_aprob_sri=='S'){
        $datosqr='https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$link_qr;

        $bobj = $barcode->getBarcodeObj(
            'QRCODE,H',                     // Tipo de Barcode o Qr
            $datosqr,          // Datos
            -2,                             // Width 
            -2,                             // Height
            'black',                        // Color del codigo
            array(-1, -1, -1, -1)           // Padding
            )->setBackgroundColor('white'); // Color de fondo
    }
    else{
        $bobj = $barcode->getBarcodeObj(
            'QRCODE,H',                     // Tipo de Barcode o Qr
            $datosqr,          // Datos
            -2.9,                             // Width 
            -2.9,                             // Height
            'black',                        // Color del codigo
            array(-1, -1, -1, -1)           // Padding
            )->setBackgroundColor('white'); // Color de fondo
    }

   

    $imageData = $bobj->getPngData(); // Obtenemos el resultado en formato PNG
        
    
    file_put_contents(DIR_FACTELEC . 'modulos/envio_documentos_colombia/qr_facturas/FAC_'.$id.'.png', $imageData); // Guardamos el resultado
    
    $ruta=DIR_FACTELEC . 'modulos/envio_documentos_colombia/qr_facturas/FAC_'.$id.'.png';


    //PUNTO DE EMISION

    $sql="SELECT emifa_auto_emifa, emifa_auto_desde, emifa_auto_hasta, emifa_fec_ini, emifa_fec_fin 
    from saeemifa where emifa_tip_doc='FAC' and emifa_est_emifa='S' and emifa_cod_pto='$nse_fact'";

    $autorizacion = consulta_string_func($sql, 'emifa_auto_emifa', $oCon, '');
    $fec_desde = date('d/m/Y',strtotime(consulta_string_func($sql, 'emifa_fec_ini', $oCon, '')));
    $fec_hasta = date('d/m/Y',strtotime(consulta_string_func($sql, 'emifa_fec_fin', $oCon, '')));
    $sec_ini = consulta_string_func($sql, 'emifa_auto_desde', $oCon, '');
    $sec_fin = consulta_string_func($sql, 'emifa_auto_hasta', $oCon, '');


     //CABECERA FACTURA 
                
     $logo ='<table border="0"  style="font-size:11px; width: 100%;"  cellspacing="1">';
     $logo .= '<tr>';

     $logo .= '<td style="width:45%;" >';
     $logo .='<table border="0"   cellspacing="0">';

     $logo .= '<tr>';
     $logo .= '<td  style="width:100%;" align="left">'.$logo_empresa.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td style="width:100%;" >' .$razonSocial . ' NIT '.$ruc_empr.'</td>';
     $logo .= '</tr>';

     ///LEYENDAS PDF- XML 

     $sqlxml = "select ixml_tit_ixml, ixml_det_ixml from saeixml where ixml_cod_empr=$idEmpresa 
				and ixml_est_deleted ='S' and ixml_sn_pdf='S' order by ixml_ord_ixml";

				if ($oIfx->Query($sqlxml)) {
					if ($oIfx->NumFilas() > 0) {
						do {
							$titulo  = $oIfx->f('ixml_tit_ixml');
							$detalle = $oIfx->f('ixml_det_ixml');
                            $logo .= '<tr>';
                            $logo .= '<td style="width:100%;" >' . $detalle . '</td>';//VALIDAR
                            $logo .= '</tr>';
						} while ($oIfx->SiguienteRegistro());
					}
				}
	$oIfx->Free();

    
     $logo .='</table>';
     $logo .= '</td>';


     $logo .= '<td style="width:55%;" >';

     $logo .='<table border="0"  style="font-size:11px; "  cellspacing="0">';

     $logo .= '<tr>';
     $logo .= '<td  style="font-size:16px; width:70%;" align="left"><b>Factura Electrónica de Venta</b></td>';
     $logo .= '<td style="width:30%;" align="left">'.$nse_fact.'-'.$fact_num_preimp.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  colspan="2" align="left">Representación Gráfica</td>';
     $logo .= '</tr>';
     
     $logo .= '<tr>';
     $logo .= '<td  colspan="2"  align="left">Autorización Numeración de Facturación Electrónica</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td   style="font-size:9px; "  colspan="2" align="left">No. '.$autorizacion.' de '.$fec_desde.' - '.$fec_hasta.' autoriza '.$nse_fact.'-'.$sec_ini.' a '.$nse_fact.'-'.$sec_fin.'</td>';//VALIDAR
     $logo .= '</tr>';

     $logo .='</table>';

     $logo .='<table border="0"  style="font-size:11px;  margin-top:20px;"  cellspacing="0">';

     $logo .= '<tr>';
     $logo .= '<td  style="width:30%;" align="left">Tipo de Operación</td>';
     $logo .= '<td  style="width:40%;" align="right">Estandar</td>';//VALIDAR
     $logo .= '<td  style="width:30%;" rowspan="8" align="left"><img src="'.$ruta.'"></td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:30%;" align="left">Fecha de Generación</td>';
     $logo .= '<td  style="width:40%;" align="right">'.$fecha_gen.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:30%;" align="left">Fecha de Vencimiento</td>';
     $logo .= '<td  style="width:40%;" align="right">'.$fecha_venc.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:30%;" align="left">Fecha de Validación</td>';
     $logo .= '<td  style="width:40%;" align="right">'.$fecha_gen.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:30%;" align="left">Forma de Pago</td>';
     $logo .= '<td  style="width:40%;" align="right">'.$fpagop_des_fpagop.' '.$numero_dias.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:30%;" align="left">Medio de Pago</td>';
     $logo .= '<td  style="width:40%;" align="right"></td>';//VALIDAR
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:30%;" align="left">Moneda</td>';
     $logo .= '<td  style="width:40%;" align="right">'.$sigmone.'</td>';//VALIDAR
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:30%;" align="left">Orden de Compra</td>';
     $logo .= '<td  style="width:40%;" align="right">'.$orden_compra.'</td>';//VALIDAR
     $logo .= '</tr>';
     
     $logo .='</table>';
     
     $logo .= '</td>';

     $logo .= '</tr>';
    
     $logo.='</table>';


     $sqlDire = "SELECT id_provincia, id_canton,     id_ciudad,    id_parroquia, id_sector,     id_barrio,      
                           id_bloque,    nomb_conjunto, num_conjunto, estrato,      id_conjunto,   departamento,  poste,
                           caja,         id_ruta,      ruta,      orden_ruta,       direccion,     referencia,    latitud, 
                           longitud,     id_calle
                    from isp.contrato_clpv
                    where id = $cod_contrato";
                if ($oIfx->Query($sqlDire)) {
                    if ($oIfx->NumFilas() > 0) {
                        $id_provincia   = $oIfx->f('id_provincia');
                        $id_canton      = $oIfx->f('id_canton');
                        $id_ciudad      = $oIfx->f('id_ciudad');
                        $id_parroquia   = $oIfx->f('id_parroquia');
                        $id_sector      = $oIfx->f('id_sector');
                        $id_barrio      = $oIfx->f('id_barrio');
                        $id_bloque      = $oIfx->f('id_bloque');
                        $nomb_conjunto  = $oIfx->f('nomb_conjunto');
                        $num_conjunto   = $oIfx->f('num_conjunto');
                        $estrato        = $oIfx->f('estrato');
                        $id_conjunto    = $oIfx->f('id_conjunto');
                        $direccion      = $oIfx->f('direccion');


                        if (!empty($id_provincia)) {
                            $sql = "SELECT prov_des_prov from saeprov where prov_cod_prov = $id_provincia ";

                            if ($oCon->Query($sql)) {
                                if ($oCon->NumFilas() > 0) {
                                    $departamento     = $oCon->f('prov_des_prov');
                                }
                            }
                            $oCon->Free();
                        }

                        if (!empty($id_ciudad)) {
                            $sql = "SELECT ciud_cod_ciud, ciud_nom_ciud from saeciud where ciud_cod_ciud = $id_ciudad ";
                            if ($oCon->Query($sql)) {
                                if ($oCon->NumFilas() > 0) {
                                    $distrito     = $oCon->f('ciud_nom_ciud');
                                }
                            }
                            $oCon->Free();
                        }

                        if (!empty($id_canton)) {
                            $sql = "SELECT cant_cod_cant, cant_des_cant from saecant where cant_cod_cant = $id_canton and cant_est_cant = 'A' ";
                            if ($oCon->Query($sql)) {
                                if ($oCon->NumFilas() > 0) {
                                    $provincia     = $oCon->f('cant_des_cant');
                                }
                            }
                            $oCon->Free();
                        }

                        if (!empty($id_sector)) {
                            $sql = "SELECT id, sector from comercial.sector_direccion where  id = $id_sector ";

                            if ($oCon->Query($sql)) {
                                if ($oCon->NumFilas() > 0) {
                                    $urbanizacion     = $oCon->f('sector');
                                }
                            }
                            $oCon->Free();
                        }
                    }
                }
                $oIfx->free();




     //DATOS DEL CLIENTE

     $logo .='<table border="0"  style="width: 100%; margin-top:20px;"  cellspacing="0">';

     $logo .= '<tr>';
     $logo .= '<td  colspan="2" style="font-size:20px; border-bottom: '.$empr_web_color.' 1px solid; border-right: '.$empr_web_color.' 1px solid; width:50%;" align="center"><b>DATOS DEL EMISOR</b></td>';
     $logo .= '<td  colspan="2" style="font-size:20px; border-bottom: '.$empr_web_color.' 1px solid; width:50%;" align="center"><b>DATOS DEL CLIENTE</b></td>';//VALIDAR
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >Razón Social</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >'.$razonSocial.'</td>';
     $logo .= '<td  style="width:15%;" >Razón Social</td>';
     $logo .= '<td  style="width:35%;" >'.$fact_nom_cliente.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >NIT</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >'.$ruc_empr.'</td>';
     $logo .= '<td  style="width:15%;" >'.$tip_iden_cliente.'</td>';
     $logo .= '<td  style="width:35%;" >'.$fact_ruc_clie.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >Obligación</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >NO APLICA</td>';//VALIDAR
     $logo .= '<td  style="width:15%;" >Obligación</td>';
     $logo .= '<td  style="width:35%;" >N/A</td>';//VALIDAR
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >Email</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >'.$empr_mai_empr.'</td>';
     $logo .= '<td  style="width:15%;" >Email</td>';
     $logo .= '<td  style="width:35%;" >'.$fact_email_clpv.'</td>';
     $logo .= '</tr>';


     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >Teléfono</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >'.$tel_empresa.'</td>';
     $logo .= '<td  style="width:15%;" >Teléfono</td>';
     $logo .= '<td  style="width:35%;" >'.$telf_clpv.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >Dirección</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >'.$dirMatriz.'</td>';
     $logo .= '<td  style="width:15%;" >Dirección</td>';
     $logo .= '<td  style="width:35%;" >'.$fact_dir_clie.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >Ciudad, Depart.</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >'.$ciudad.', '.$dep_empr.' </td>';
     $logo .= '<td  style="width:15%;" >Ciudad, Depart.</td>';
     $logo .= '<td  style="width:35%;" >'.$provincia .', '.$departamento.'</td>';//VALIDAR
     $logo .= '</tr>';

     $logo.='</table>';


    
    //DETALLE DE LA FACTURA
    
    $sqlDeta = "select * from saedfac where dfac_cod_fact = $id and 
                dfac_cod_sucu = $idSucursal and 
                dfac_cod_empr = $idEmpresa  order by dfac_cod_mes";

    $deta .= ' <table  style="width: 100%;  font-size: 12px;   margin-top:20px;" cellpadding="1" cellspacing="0">';
    $deta .= ' <tr >';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 4%;"  align="left"   height="30">No</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 12%;" align="left"   height="30">REF</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 22%;" align="left"   height="30">DESCRIPCIÓN</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="right"  height="30">CANT</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="center" height="30">U/M</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="center" height="30">PRECIO</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 8%;" align="center" height="30">IMP</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="right"  height="30">SUBTOTAL</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 14%;" align="right"  height="30">TOTAL ITEM</td> </b>';
    $deta .= ' </tr>';

    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            $ctrl=0;
            $i=1;
            $total_item=0;

            $array_mes=array();
            $detalle_servicio='';
            do {
                $dfac_cod_prod = $oIfx->f('dfac_cod_prod');
                $dfac_nom_prod = $oIfx->f('dfac_nom_prod');
                $dfac_cod_lote = $oIfx->f('dfac_cod_lote');

                $dfac_cant_dfac = $oIfx->f('dfac_cant_dfac');
                $dfac_precio_dfac = $oIfx->f('dfac_precio_dfac');
                $dfac_des1_dfac = $oIfx->f('dfac_des1_dfac');
                $dfac_des2_dfac = $oIfx->f('dfac_des2_dfac');
                $dfac_por_dsg = $oIfx->f('dfac_por_dsg');
                $dfac_mont_total = $oIfx->f('dfac_mont_total');
                $dfac_por_iva = $oIfx->f('dfac_por_iva');
                $dfac_cod_mes = $oIfx->f('dfac_cod_mes');

                if(round($dfac_por_iva, 2)>0) $iva=$array_imp ['IVA'].' '.round($dfac_por_iva).'%';
                else
                $iva=$array_imp ['IVA'].' '.'0%';

                $dfac_det_dfac = $oIfx->f('dfac_det_dfac');

                $dfac_cod_unid = $oIfx->f('dfac_cod_unid');
                if(!empty($dfac_cod_unid)){
                $unidad= $array_unidad_desc[$dfac_cod_unid];
                }
                else{
                    $unidad='';
                }

                $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                if ($descuento > 0){
                    $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                    $descuento =round($descuento, 2, PHP_ROUND_HALF_UP);
                }
                else{
                    $descuento = '0';
                }
               

                if (round($dfac_por_iva, 2) > 0) {
                    $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                    $total_item = ($dfac_mont_total * $porcentaje_iva);
                    //$total_item = $dfac_mont_total + $valor_iva;
                    
                }
                else{
                    $total_item = $dfac_mont_total;
                }

                if (empty($dfac_det_dfac)) {
                    $dfac_det_dfac = 'Sin detalle';
                }

              

                /*if($fact_cod_mone==$pcon_seg_mone){
                    $dfac_precio_dfac=$dfac_precio_dfac/$fact_val_tcam;
                    $dfac_mont_total=$dfac_mont_total/$fact_val_tcam;
                    $descuento=$descuento/$fact_val_tcam;
                    $totalDescuento=$totalDescuento/$fact_val_tcam;
                }*/

                //$mes_pago = '';
                if( !empty($dfac_cod_mes) ){
                    $sql = "SELECT c.mes, c.anio FROM  isp.contrato_pago c WHERE
                                        c.id IN ( $dfac_cod_mes  )  ";
                    $mes_pago = '';
                    if ($oCon->Query($sql)) {
                        if ($oCon->NumFilas() > 0) {
                            do {
                                $mes_pago = Mes_func($oCon->f('mes')) .'-'.$oCon->f('anio').':'.$dfac_mont_total;
                                $anio_pago =$oCon->f('anio');
                            } while ($oCon->SiguienteRegistro());
                        }
                    }
                    $oCon->Free();

                    $servicio=detalle_plan($dfac_cod_prod, $dfac_cod_mes, $id_contrato);
                    array_push($array_mes, $mes_pago);
                }

                
                $deta .= ' <tr>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 4%;">'.$i.'</td>';
                $deta .= ' <td style="font-size:10px; border-bottom: '.$empr_web_color.' 1px solid; width: 12%;">'.$dfac_cod_prod.'</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 22%;">'.$dfac_det_dfac.' - '.$dfac_nom_prod.'</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="right" >' . number_format($dfac_cant_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="center">' . $unidad . '</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="center" >'.$eti_mone.'' . number_format($dfac_precio_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 8%;" align="center" >' . $iva. '</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="right" >'.$eti_mone.'' . number_format($dfac_mont_total, 2, '.', ',') . '</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 14%;" align="right" >'.$eti_mone.'' . number_format($total_item, 2, '.', ',') . '</td>';
                $deta .= ' </tr>';
                $ctrl++;
            }while ($oIfx->SiguienteRegistro());
            $detalle_meses='';
            $monto_deuda=0;
            $monto_pagado=0;

                /*for ($i=0; $i < count($array_mes) -1 ; $i++) { 
                    # code...
                    $array_val=explode(':',$array_mes[$i]);
                    $detalle_meses.=$array_val[0].'<br>';
                    $monto_mes_anterior+=$array_val[1];
                }
                for ($i=0; $i < count($array_mes) ; $i++) { 
                    # code...
                    $array_val=explode(':',$array_mes[$i]);
                    $monto_pagado+=$array_val[1];
                } */
               
        }
    }
    $deta .= ' </table>';


    $total_fac = $fact_con_miva + $fact_iva+$fact_sin_miva + $fact_val_irbp -$fact_dsg_valo;

    if($fact_cod_mone==$pcon_seg_mone)
    {
        $fact_iva = $fact_iva/$fact_val_tcam;
        $total_fac = $total_fac/$fact_val_tcam;
        $fact_tot_fact = $fact_tot_fact/$fact_val_tcam;

    }


    
    $totales ='<table style="margin-top:15px; width: 100%;  font-size: 13px;" cellspacing="0">';
    $totales .='<tr>
    <td style="width:56%;">&nbsp;</td>';

    $totales.='<td style="width:44%;">';

    $totales .= ' <table style="font-size: 13px;"   cellspacing="0" >';
    $totales .= ' <tr>';
    $totales .= ' <td style="font-size:14px; border-bottom: '.$empr_web_color.' 1px solid; width: 65%;"  height="18" >Subtotal</td>';
    $totales .= ' <td style="font-size:14px; border-bottom: '.$empr_web_color.' 1px solid; width: 35%;" align="right">'.$eti_mone.'' . number_format($fact_tot_fact, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <td style="font-size:15px; width: 65%;" height="18" ><br>Total a Pagar</td>';
    $totales .= ' <td style="font-size:15px; width: 35%;" align="right"><br>'.$eti_mone.'' . number_format($total_fac, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';


    $totales .= ' </table>';

    $totales.='</td></tr>';
    $totales .='</table>';

    ///FIRMAS


    $firmas ='<table style="margin-top:50px; width: 100%;  font-size: 13px;" cellspacing="0">';
    $firmas .='<tr>';
    $firmas .='<td style="width:12%;">&nbsp;</td>';
    $firmas .='<td style="border-bottom: '.$empr_web_color.' 1px solid; width:34%;"></td>';
    $firmas .='<td style="width:4%;">&nbsp;</td>';
    $firmas .='<td style="border-bottom: '.$empr_web_color.' 1px solid; width:34%;"></td>';
    $firmas .='<td style="width:12%;">&nbsp;</td>';
    $firmas .='</tr>';
    $firmas .='<tr>';
    $firmas .='<td style="width:12%;">&nbsp;</td>';
    $firmas .='<td style="width:33%;" align="center">FIRMA EMISOR</td>';
    $firmas .='<td style="width:6%;">&nbsp;</td>';
    $firmas .='<td style="width:33%;" align="center">FIRMA CLIENTE</td>';
    $firmas .='<td style="width:12%;">&nbsp;</td>';
    $firmas .='</tr>';
    $firmas .='</table>';

    

    $tableLeyenda .='<table border="0"  style="width: 85%;margin-top:30px;" cellspacing="0" >';
    $tableLeyenda .= '<tr>';
    /*CONSULTA CUENTAS CONFIGURADAS POR EMPRESA */ 

    if($tipo_pdf=='B'){
        $doc='BOLETA';
        $tip='BOL';
    }
    elseif($tipo_pdf=='F'){
        $doc='FACTURA';
        $tip='FAC';
    }
    $sql_cont="select count(*) as conteo from saeipdf where ipdf_cod_empr=$idEmpresa and ipdf_tip_ipdf in (select 
    emifa_cod_emifa from saeemifa  where emifa_cod_empr = $idEmpresa
    and emifa_tip_doc = '$tip' and emifa_est_emifa = 'S' 
    and emifa_cod_emifa=ipdf_tip_ipdf) and ipdf_est_deleted ='S'";
    $num_items=consulta_string($sql_cont,'conteo',$oIfx,1);


    $sqlpdf="select * from saeipdf where ipdf_cod_empr=$idEmpresa and ipdf_tip_ipdf in (select 
        emifa_cod_emifa from saeemifa  where emifa_cod_empr = $idEmpresa
        and emifa_tip_doc = '$tip'  and emifa_est_emifa = 'S' 
        and emifa_cod_emifa=ipdf_tip_ipdf) and ipdf_est_deleted ='S'  order by ipdf_ord_ipdf";

        $item=0;
        if ($oIfx->Query($sqlpdf)) {
        if ($oIfx->NumFilas() > 0) {
            do{
            $titulo = $oIfx->f('ipdf_tit_ipdf');
            $detalle = $oIfx->f('ipdf_det_ipdf');


            
            //VALIDACION CONTRATO CLPV 
            /*if(!empty($id_contrato) && $id_contrato!=0 && preg_match("/{deuda}/i",$detalle)){

                $deuda='<br><br>'.$servicio;
                $deuda.='<br>'.$detalle_meses;
                $deuda.='<br>Monto: '.number_format($monto_mes_anterior, 2, '.', ',');
                $deuda.='<br>Pago Realizado: '.number_format($monto_pagado, 2, '.', ',');
                $deuda.='<br>Deuda: '.number_format($monto_pagado-$monto_mes_anterior, 2, '.', ',');
                $detalle = str_replace('{deuda}', $deuda, $detalle);
                
            }*/

            if(!empty($id_contrato) && $id_contrato!=0 && preg_match("/{deuda}/i",$detalle)){

                $html=controlCarteraFac($id_contrato);
                $detalle = str_replace('{deuda}', $html, $detalle);

                $detalle .='<br><br><b>Fecha de Corte: '.$fecha_corte.'</b>';
            }
            
                
            $detalle =str_replace('COD_CLIENTE',$codigo_cid,$detalle);
                
            $formato = $oIfx->f('ipdf_tip_ipdf');
                $width=677/$num_items;
            if($num_items==2){
                $width=688/$num_items;
            }
            
            if((!empty($id_contrato) && $id_contrato!=0 && preg_match("/{deuda}/i",$detalle)) || !preg_match("/{deuda}/i",$detalle)){

                $tableLeyenda .= '<td valign="top" style="width: 20%;">';
                $tableLeyenda .= '<table style="  font-size: 11px;  margin-top:5px;" cellspacing="0">';
                $tableLeyenda .= '<tr>';

                if($item!=0) 
                $tableLeyenda .= '<td style="border-left: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; " height="25" width="'.$width.'" valign="middle">&nbsp;<b>'.$titulo.'</b></td>';
                else
                $tableLeyenda .= '<td style="border-bottom: '.$empr_web_color.' 1px solid; " height="25" width="'.$width.'" valign="middle">&nbsp;<b>'.$titulo.'</b></td>';
            
                
                $tableLeyenda .= '</tr>';    
                $tableLeyenda .= '<tr>';

                
                if($item!=0) 
                $tableLeyenda .= '<td style="border-left: '.$empr_web_color.' 1px solid;" width="'.$width.'" height="80"><div style="margin-left:3px;margin-top:10px;"><b>'.$detalle.'</b></div></td>';
                else
                $tableLeyenda .= '<td width="'.$width.'" height="80"><div style="margin-left:3px;margin-top:10px;"><b>'.$detalle.'</b></div></td>';

                $tableLeyenda .= '</tr>';
                $tableLeyenda.='</table>';
                $tableLeyenda .= '</td>';
            }
           
            $item++;
            
            }while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $tableLeyenda .= '</tr>';
    
    $tableLeyenda .= '</table>';

   


    $legend = '<page_footer>
        <table align="center" style="width: 100%">
            <tr>
                <td style="font-size: 11px; width:60%;" align="center">CUFE:<br>'.$fact_cod_hash.'</td>
                <td style="font-size: 10px; width:40%; color: #6B6565; background-color: transparent;" >Este comprobante electronico ha sido generado a traves de Sisconti S.A. - Facturacion Electronica<br>www.sisconti.com</td>
            </tr>
        </table>
    </page_footer>';

    $documento .= '<page backimgw="100%" backtop="5mm" backbottom="5mm" backleft="2mm" backright="5mm" footer="page">';
    $documento .= $logo . $cliente . $deta . $totales .$firmas. $tableLeyenda;
    $documento .= $legend;
    $documento .= '</page>';


    
    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
    $html2pdf->WriteHTML($documento);

    $ruta_dir= DIR_FACTELEC . 'modulos/envio_documentos_colombia/upload';
	if (!file_exists($ruta_dir)){
					mkdir($ruta_dir,0777,true);
	}
    $ruta = DIR_FACTELEC . 'modulos/envio_documentos_colombia/upload/pdf/fac_' . $nombre_archivo . '.pdf';
    $html2pdf->Output($ruta,'F');
    $rutaPdf = $ruta;

    return $documento;
}

function controlCarteraFac($idContrato)
{
    //Definiciones
    global $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();


   

        //varibales de sesion
        $idempresa = $_SESSION['U_EMPRESA'];
        $cod_pais = $_SESSION['S_PAIS_API_SRI'];

        //QUERY CONTRATO CLPV
        $sql = "SELECT id_clpv, nom_clpv, codigo, ruc_clpv FROM isp.contrato_clpv WHERE id = $idContrato";
        $id_clpv = consulta_string_func($sql, 'id_clpv', $oCon, 0);
        $nom_clpv = consulta_string_func($sql, 'nom_clpv', $oCon, 0);
        $codigo = consulta_string_func($sql, 'codigo', $oCon, 0);
        $ruc_clpv_iden = consulta_string_func($sql, 'ruc_clpv', $oCon, 0);

        $html='';

        $total_valor=0;
        $total_pago=0;
        $total_saldo=0;

        /*$sHtml = ' <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                            <div class="table-responsive" >
                                <table id="tableContratos" class="table table-striped table-bordered table-hover table-condensed" style="align=center">
                                    <thead>
                                        <tr>
                                        <td colspan="15" class="bg-primary"><h5>LISTADO DE FACTURAS</small></h5></td>
                                        </tr>
                                        <tr>
                                            <td class="bg-primary" style="width: 1%;">No</td>
                                            <td class="bg-primary" style="width: 6%;">Fecha emisión</td>
                                            <td class="bg-primary" style="width: 12%;">Cliente</td>
                                            <td class="bg-primary" style="width: 8%;">Movimientos</td>
                                            <td class="bg-primary" style="width: 15%;">Factura</td>
                                            <td class="bg-primary" style="width: 4%;">Moneda Pago</td>
                                            <td class="bg-primary" style="width: 5%;">Vence</td>
                                            <td class="bg-primary" style="width: 7%;">Valor</td>    
                                            <td class="bg-primary" style="width: 7%;">NC</td>  
                                            <td class="bg-primary" style="width: 7%;">Pagos</td>      
                                            <td class="bg-primary" style="width: 7%;">Saldo</td>
                                            <td class="bg-primary" style="width: 7%;">Pagar</td>
                                            <td class="bg-primary" style="width: 7%;">Generar NC</td>
                                            <td class="bg-primary" style="width: 8%;">Impreso</td>
                                        </tr>
                                    </thead>
                                    <tbody>';*/

        //TIPO TRAN
        $sql = "SELECT CONCAT(tran_cod_modu,'-',tran_cod_tran) AS codigo, tran_des_tran from saetran";
        $array_tran     = array_dato($oCon, $sql, 'codigo', 'tran_des_tran');

        //COTIZACION FACTURA
        $sql = "SELECT fact_cod_fact, fact_val_tcam from saefact where fact_cod_clpv = $id_clpv and fact_cod_empr = $idempresa";
        $array_fact     = array_dato($oCon, $sql, 'fact_cod_fact', 'fact_val_tcam');

        //CANCELACIONES
        $sql = "SELECT sum(dmcc_cre_ml) as cancelaciones, dmcc_num_fac FROM saedmcc WHERE dmcc_cod_tran in ('CAN') AND clpv_cod_clpv = $id_clpv GROUP BY 2";
        $array_canc     = array_dato($oCon, $sql, 'dmcc_num_fac', 'cancelaciones');

        //NOTA CREDITO
        $sql = "SELECT sum(dmcc_cre_ml) as nota_credito, dmcc_num_fac FROM saedmcc WHERE dmcc_cod_tran in ('NDC') AND clpv_cod_clpv = $id_clpv GROUP BY 2";
        $array_ndct     = array_dato($oCon, $sql, 'dmcc_num_fac', 'nota_credito');

        //FECHAS
        $sql = "SELECT dmcc_fec_ven, dmcc_fec_emis, dmcc_num_fac, dmcc_cod_fact FROM saedmcc WHERE dmcc_cod_tran like '%FAC%' AND clpv_cod_clpv = $id_clpv";
        $array_fec_fact_v   = array_dato($oCon, $sql, 'dmcc_num_fac', 'dmcc_fec_ven');
        $array_fec_fact     = array_dato($oCon, $sql, 'dmcc_num_fac', 'dmcc_fec_emis');
        $array_id_fact     = array_dato($oCon, $sql, 'dmcc_num_fac', 'dmcc_cod_fact');

        //DETALLES DMCC
        $sql = "SELECT dmcc_num_fac, dmcc_cod_tran, dmcc_deb_ml, dmcc_cre_ml, dmcc_cod_modu FROM saedmcc";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                unset($array_info);
                do {
                    if (!empty($oCon->f('dmcc_num_fac'))) {
                        $array_info[$oCon->f('dmcc_num_fac')][] = array(
                            $oCon->f('dmcc_cod_tran'),
                            $oCon->f('dmcc_deb_ml'),
                            $oCon->f('dmcc_cre_ml'),
                            $oCon->f('dmcc_cod_modu')
                        );
                    }
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $num_bvsc = 0.01;

        // ESTADO DE CUENTA
        $sql_sp = "SELECT SUM( a.dmcc_deb_ml ) AS debito,
                            SUM ( a.dmcc_cre_ml ) AS credito,
                            SUM( a.dmcc_deb_mext ) AS debito_mext,
                            SUM ( a.dmcc_cre_mext ) AS credito_mext,
                            a.dmcc_num_fac,
                            a.clpv_cod_clpv,
                            MAX(a.dmcc_cod_fact) as dmcc_cod_fact,
                            MAX(a.dmcc_cod_mone) as dmcc_cod_mone,
                            MAX(a.dmcc_val_coti) as dmcc_val_coti
                        FROM
                            saedmcc a INNER JOIN saeclpv b ON a.clpv_cod_clpv = b.clpv_cod_clpv
                        WHERE
                            a.dmcc_cod_empr = $idempresa
                            AND a.clpv_cod_clpv = $id_clpv
                            AND b.clpv_ruc_clpv = '$ruc_clpv_iden'
                        GROUP BY
                            5,
                            6
                        ORDER BY
                            clpv_cod_clpv, dmcc_num_fac";
        if ($oCon->Query($sql_sp)) {
            $x = 1;
            if ($oCon->NumFilas() > 0) {
                do {
                    $debito        = $oCon->f('debito');
                    $credito       = $oCon->f('credito');
                    $debito_mext   = $oCon->f('debito_mext');
                    $credito_mext  = $oCon->f('credito_mext');
                    $dmcc_num_fac  = $oCon->f('dmcc_num_fac');

                    $cod_clpv      = $oCon->f('clpv_cod_clpv');
                    $dmcc_cod_mone = $oCon->f('dmcc_cod_mone');
                    $dmcc_val_coti = $oCon->f('dmcc_val_coti');
                    $dmcc_cod_fact = $oCon->f('dmcc_cod_fact');

                    $dmcc_fec_emis = $array_fec_fact[$dmcc_num_fac];
                    $dmcc_fec_venc = $array_fec_fact_v[$dmcc_num_fac];
                    $id_factura    = $array_id_fact[$dmcc_num_fac];

                    $dmcc_val_coti = $array_fact[$dmcc_cod_fact];
                    if (empty($dmcc_val_coti)) {
                        $dmcc_val_coti = $oCon->f('dmcc_val_coti');
                    }

                    //COMENTARIO
                    $dmcc_cod_fac  = $id_factura;

                    $saldo         = $debito - $credito;
                    $saldo_mext    = $debito_mext - $credito_mext;
                    $nom_clie      = $nom_clpv;
                    $id_contrato   = $idContrato;

                    $sql_nombre_moneda = "SELECT * FROM saemone where mone_cod_mone = $dmcc_cod_mone and mone_cod_empr = $idempresa";
                    $nombre_moneda = consulta_string_func($sql_nombre_moneda, 'mone_des_mone', $oConA, 0);

                    $debito         = number_format($debito, 2, '.', '');
                    $saldo          = number_format($saldo, 2, '.', '');

                    $detalles       = '';

                    $tipo_t = 0;
                    $deb_val = 0;
                    $cre_val = 0;
                    $cod_mod = 0;
                    $detalles = 0;

                    /* for ($i = 0; $i < count($array_info); $i++) {
                        if(isset($array_info[$dmcc_num_fac][$i][0])){
                            $tipo_t     = $array_info[$dmcc_num_fac][$i][0];
                        }

                        if(isset($array_info[$dmcc_num_fac][$i][1])){
                            $deb_val     = $array_info[$dmcc_num_fac][$i][1];
                        }

                        if(isset($array_info[$dmcc_num_fac][$i][2])){
                            $cre_val     = $array_info[$dmcc_num_fac][$i][2];
                        }

                        if(isset($array_info[$dmcc_num_fac][$i][3])){
                            $cod_mod     = $array_info[$dmcc_num_fac][$i][3];
                        }

                        $cod_tran   = $cod_mod . "-" . $tipo_t;

                        if(isset($array_tran[$cod_tran])){
                            $tipo_t     = $array_tran[$cod_tran];
                        }

                        if (!empty($tipo_t)) {
                            $detalles .= $tipo_t . " - Deb: " . $deb_val . " - Cre: " . $cre_val . '<br>';
                        }
                    } */

                    if ($id_contrato > 0 && $saldo > 0) {
                        $div_i = '<div align="center"> <input type="checkbox" name="contratos" value="' . $id_contrato . '" id="abonados" /></div>';
                    } else {
                        $div_i = '';
                    }

                    if ($dmcc_cod_fac != 0 && !empty($dmcc_cod_fac)) {

                        $sqlfac = "select fact_cod_sucu, fact_clav_sri from saefact where fact_cod_fact=$dmcc_cod_fac";

                        if ($oConA->Query($sqlfac)) {
                            $x = 1;
                            if ($oConA->NumFilas() > 0) {
                                $fact_clav_sri = $oConA->f("fact_clav_sri");
                                $fact_cod_sucu = $oConA->f("fact_cod_sucu");
                            }
                        }

                        $campo = 0;
                        $impre = '<span class="btn btn-primary btn-block" title="Imprimir Factura" value="Imprimir" 
                            onclick="genera_documento(1, ' . $dmcc_cod_fac . ', \'' . $fact_clav_sri . '\', ' . $campo . ', ' . $campo . ', ' . $campo . ', ' . $campo . ', ' . $campo . ', ' . $fact_cod_sucu . ');">
                                        <i class="glyphicon glyphicon-print"></i>
                                   </span>';
                    } else {
                        $impre = 'Sin factura en el sistema';
                    }

                    if ($saldo > 0) {
                        $clase = '';
                        $btn_pagar = '<button class="btn btn-sm btn-success btn-block" onclick="seleccionarFormaPago(\'' . $saldo . '\', \'' . $saldo_mext . '\', \'' . $dmcc_cod_mone . '\', \'' . $dmcc_val_coti . '\', \'' . $dmcc_num_fac . '\', ' . $id_clpv . ')"><i class="fa-sharp fa-solid fa-sack-dollar fa-2x"></i></button>';
                        if ($id_factura > 0) {
                            $btn_nc = '<button class="btn btn-sm btn-warning btn-block" onclick="seleccionarNcre(\'' . $saldo . '\', \'' . $saldo_mext . '\', \'' . $dmcc_cod_mone . '\', \'' . $dmcc_val_coti . '\', \'' . $dmcc_num_fac . '\', ' . $id_factura . ')"><i class="fa-solid fa-handshake fa-2x"></i></button>';
                        } else {
                            $btn_nc = 'Sin factura en el sistema para nota de credito.';
                        }
                    } else {
                        $clase = 'class="bg-success"';
                        $btn_pagar = '';
                        $btn_nc = '';
                    }

                    $pagos = 0;
                    $ndc = 0;

                    if(isset($array_canc[$dmcc_num_fac])){
                        $pagos  = $array_canc[$dmcc_num_fac];
                    }

                    if(isset($array_ndct[$dmcc_num_fac])){
                        $ndc    = $array_ndct[$dmcc_num_fac];
                    }

                    if (empty($pagos)) {
                        $pagos = 0;
                    }

                    if (empty($ndc)) {
                        $ndc = 0;
                    }

                    $detalles = '<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsefp' . $x . '" aria-expanded="true" aria-controls="collapsefp"
                                    title="Presionar aqui para ver desglose de los detalles" style="font-family: Arial; font-size:12px; color: #337ab7;">
                                    <i class="fa-sharp fa-solid fa-eye"></i> Ver detalles
                                </a><br>
                                <div class="collapse" id="collapsefp' . $x . '">
                                    <div class="row" style="margin-top: 11px;">
                                        <div class="col-md-12 has-error">
                                            ' . $detalles . '
                                        </div>
                                    </div>
                                </div>';

                    if ($saldo >= $num_bvsc) {
                        $total_valor+=$debito;
                        $total_pago+=$pagos;
                        $total_saldo+=$saldo;
                        $mes_emision=date('m', strtotime($dmcc_fec_emis));
                        $anio_emision=date('Y', strtotime($dmcc_fec_emis));

                        $mes_fac=Mes_func($mes_emision);
                        $fecha_emision=$mes_fac.'-'.$anio_emision;

                        $factura=substr($dmcc_num_fac,3,strlen($dmcc_num_fac)-7);
                        $html.='<br>'.$factura.' '.$fecha_emision;
                        /*$sHtml .= ' <tr>
                            <td ' . $clase . '>' . $x++ . '</td>
                            <td ' . $clase . '>' . $dmcc_fec_emis . '</td>
                            <td ' . $clase . '>' . $nom_clie . '</td>
                            <td ' . $clase . '>' . $detalles . '</td>         
                            <td ' . $clase . '>' . $dmcc_num_fac . '</td>
                            <td ' . $clase . '>' . $nombre_moneda . '</td>
                            <td ' . $clase . '>' . $dmcc_fec_venc . '</td>
                            <td ' . $clase . ' align="right">' . $debito . '</td>
                            <td ' . $clase . ' align="right">' . $ndc . '</td>
                            <td ' . $clase . ' align="right">' . $pagos . '</td>
                            <td ' . $clase . ' align="right">' . $saldo . '</td>
                            <td ' . $clase . '>' . $btn_pagar . '</td>
                            <td ' . $clase . '>' . $btn_nc . '</td>
                            <td ' . $clase . '>' . $impre . '</td>
                        </tr>';*/
                    }
                } while ($oCon->SiguienteRegistro());
            } else {
                $sHtml = '<span>Sin Datos...</span>';
            }
        }
        $oCon->Free();


        $html.='<br><br>Monto: '.$total_valor;
        $html.='<br>Pago Realizado: '.$total_pago;
        $html.='<br>Deuda: '.$total_saldo;


       
  
    return $html;

    
}
function detalle_plan($idproducto, $idPago, $idContrato=0){
    global $DSN_Ifx, $DSN;
    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}


    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $idempresa = $_SESSION['U_EMPRESA'];

    $sql = "SELECT COALESCE(t.nombre, p.detalle) as nombre, p.tipo, p.detalle
                                from isp.contrato_pago_pack p 
                                            LEFT JOIN isp.int_paquetes i ON p.id_prod = i.id 
                                            LEFT JOIN isp.int_tipo_prod t ON i.id_tipo_prod = t.id
                                WHERE p.id_contrato = $idContrato AND
                                    p.id_pago = $idPago and P.cod_prod = '$idproducto'";

                        $nombre_pack = consulta_string($sql, 'nombre', $oCon, '');
                        $tipo        = consulta_string($sql, 'tipo', $oCon, '');
                        $detalle     = consulta_string($sql, 'detalle', $oCon, '');

                        $sql = "SELECT detalle
                                from isp.contrato_pago
                                WHERE id = $idPago";
                        $detalle_pago = consulta_string($sql, 'detalle', $oCon, '');

                        $id_tipo_prod_v = 0;
                        if (!empty($nombre_pack)) {
                            if ($tipo == 'A') {
                                $detalle_pack .= ' ' . $detalle;
                            } else {
                                $detalle_pack .= ' ' . $nombre_pack;
                            }
                        }else{
                            $sql = "SELECT b.nombre, a.id_tipo_prod from isp.int_paquetes a LEFT JOIN isp.int_tipo_prod b ON a.id_tipo_prod 
                                    = b.id where prod_cod_prod = '$idproducto' and id_empresa = $idempresa";
                            $nombre_pack = consulta_string($sql, 'nombre', $oCon, '');
                            $id_tipo_prod_v = consulta_string($sql, 'id_tipo_prod', $oCon, '');

                            $detalle_pack .= ' ' . $nombre_pack;
                        }

                        if ($tipo == 'A') {
                            $dfac_det_dfac = " " . $detalle_pack . "  ";
                        } else {
                            if($id_tipo_prod_v == 10){
                                $dfac_det_dfac = $detalle_pack;
                            }else{
                                $dfac_det_dfac = "Servicio de" . $detalle_pack;
                                //$dfac_det_dfac = substr($dfac_det_dfac, 0, strlen($dfac_det_dfac) - 2);
                            } 
                        }

                        if ($dfac_det_dfac == '  -: 1  ') {
                            $dfac_det_dfac = $detalle_pago;
                        }

    return $dfac_det_dfac;
}


function reporte_notaCredito_personalizado($id = '', $nombre_archivo = '', $idSucursal ='', &$rutaPdf = '') {
    global $DSN_Ifx, $DSN;
    include_once DIR_FACTELEC."Include/Librerias/barcode1/vendor/autoload.php";
    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfx2 = new Dbo;
    $oIfx2->DSN = $DSN_Ifx;
    $oIfx2->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();


    $idEmpresa = $_SESSION['U_EMPRESA'];
    $array_imp = $_SESSION['U_EMPRESA_IMPUESTO'];
    //$idSucursal = $_SESSION['U_SUCURSAL'];


    $sql = "select empr_iva_empr, empr_cod_pais, empr_cod_ciud from saeempr where empr_cod_empr = $idEmpresa ";
    $empr_cod_pais = round(consulta_string($sql, 'empr_cod_pais', $oIfx, 0));
    $empr_cod_ciud = consulta_string($sql, 'empr_cod_ciud', $oIfx, 0);

    //DATOS PAIS - CIUDAD
    $sql = "select pais_des_pais from saepais where pais_cod_pais=$empr_cod_pais";
    $pais= consulta_string($sql,'pais_des_pais', $oCon,'');

    $sql="select ciud_nom_ciud from saeciud where ciud_cod_ciud=$empr_cod_ciud";
    $ciudad= consulta_string($sql,'ciud_nom_ciud', $oCon,'NA');



    $sql = "select empr_web_color, empr_img_rep, empr_cod_pais,empr_cm1_empr, empr_rimp_sn, empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_iva_empr,empr_tel_resp, empr_ac1_empr, empr_ac2_empr, empr_mai_empr, empr_tip_empr
                                            from saeempr where empr_cod_empr = $idEmpresa ";


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_img_rep');
            $tel_empresa = $oIfx->f('empr_tel_resp');
            $empr_mai_empr = $oIfx->f('empr_mai_empr');
            if ($oIfx->f('empr_conta_sn') == 'S')
                $empr_conta_sn = 'SI';
            else
                $empr_conta_sn = 'NO';
            $empr_web_color = $oIfx->f('empr_web_color');
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



   



        //query forma de pago
    /*$sqlFPago = "select fx.fxfp_cod_fxfp, fx.fxfp_fec_fin, fp.fpag_cod_fpagop, fx.fxfp_val_fxfp, fx.fxfp_num_dias,
				fpg.fpagop_des_fpagop,fp.fpag_des_fpag
				from saefact f, saefxfp fx, saefpag fp, saefpagop fpg
				where 
                f.fact_cod_fact = fx.fxfp_cod_fact and
				fp.fpag_cod_fpag = fx.fxfp_cod_fpag and
                f.fact_cod_empr = fpg.fpagop_cod_empr and
				fp.fpag_cod_fpagop = fpg.fpagop_cod_fpagop and
				f.fact_cod_empr = $idEmpresa and
                fp.fpag_cod_empr=$idEmpresa and 
				f.fact_cod_sucu = $idSucursal and
				f.fact_cod_fact =$id order by 1";
     
    if ($oIfx->Query($sqlFPago)) {
        if ($oIfx->NumFilas() > 0) {
            $numero_dias='';
            do {
                $fpag_cod_fpagop    = $oIfx->f('fpag_cod_fpagop');
                $fxfp_val_fxfp      = $oIfx->f('fxfp_val_fxfp');
                if($fact_cod_mone==$pcon_seg_mone)
                {
                    $fxfp_val_fxfp = $fxfp_val_fxfp/$fact_val_tcam;
                }
                $fxfp_num_dias      = $oIfx->f('fxfp_num_dias');
                if($fxfp_num_dias>0) $numero_dias=$fxfp_num_dias.' DÍAS';
                $fpagop_des_fpagop  = $oIfx->f('fpag_des_fpag');
                $fxfp_cod_fxfp   = $oIfx->f('fxfp_cod_fxfp');
                $fxfp_fec_fin = date('d/m/Y',strtotime($oIfx->f('fxfp_fec_fin')));

             
            } while ($oIfx->SiguienteRegistro());
        }        
    }
    $oIfx->Free();*/



    

//LOGO EMPRESA
    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

    if (file_exists($path_logo_img)) {
        $logo_empresa='<img width="200px;"  src="' . $path_logo_img . '">';
    }
    else{
        $logo_empresa='<div style="color:red;">LOGO NO CARGADO</div>';
    }
    
    ///DATOS DE LA NOTA DE CREDITO


    $sqlFac = "select * from saencre where ncre_cod_ncre = $id and ncre_cod_sucu = $idSucursal and ncre_cod_empr = $idEmpresa ";

    if ($oIfx->Query($sqlFac)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $ncre_nse_ncre = $oIfx->f('ncre_nse_ncre');
                $ncre_nse_ncre = substr($ncre_nse_ncre, 0, 3);
                $ncre_num_preimp = intval($oIfx->f('ncre_num_preimp'));

                $cod_contrato = $oIfx->f('ncre_cod_contr');
                if (empty($cod_contrato)) {
                     $cod_contrato = 'NULL';
                }


               

                $ncre_auto_sri = $oIfx->f('ncre_auto_sri');
                if(!empty($ncre_auto_sri)){
                    $text_1=substr($ncre_auto_sri,0,20);
                    $text_2=substr($ncre_auto_sri,20,20);
                    $text_3=substr($ncre_auto_sri,40,20);
                    $ncre_auto_sri=$text_1.'<br>'.$text_2.'<br>'.$text_3;
                }

                $ncre_fech_sri = $oIfx->f('ncre_fech_sri');
                $ncre_nom_cliente = $oIfx->f('ncre_nom_cliente');
                $ncre_fech_fact = $oIfx->f('ncre_fech_fact');
                $ncre_aprob_sri = $oIfx->f('ncre_aprob_sri');
                $ncre_tot_fact = $oIfx->f('ncre_tot_fact');

                $fecha_gen=date('d/m/Y H:i',strtotime($ncre_fech_fact));

                if(!empty($ncre_fech_sri)){
                    $ncre_fech_sri = date('d/m/Y',strtotime(substr($ncre_fech_sri,0,10))).' '.substr($ncre_fech_sri,11,8);
                }
                else{
                    $ncre_fech_sri = $ncre_fech_fact;
                }

                $ncre_leye_fact = $oIfx->f('ncre_leye_fact');
                if(empty($ncre_leye_fact)){
                    $ncre_leye_fact=$leyenda_factura;
                }


                $ncre_fech_venc = $oIfx->f('ncre_fech_venc');
                $fecha_venc=date('d/m/Y H:i',strtotime($ncre_fech_venc));

                $ncre_ruc_clie = $oIfx->f('ncre_ruc_clie');
                $ncre_tlf_cliente = $oIfx->f('ncre_tlf_cliente');
                $ncre_dir_clie = $oIfx->f('ncre_dir_clie');
                $ncre_email_clpv = $oIfx->f('ncre_email_clpv');
                $ncre_con_miva = $oIfx->f('ncre_con_miva');
                $ncre_sin_iva = $oIfx->f('ncre_sin_miva');
                $ncre_cod_fact = $oIfx->f('ncre_cod_fact');
                $ncre_iva = $oIfx->f('ncre_iva');
                $ncre_cm1_ncre = $oIfx->f('ncre_cm1_ncre');
                $ncre_cm2_ncre = $oIfx->f('ncre_cm2_ncre');
                $ncre_clav_sri = $oIfx->f('ncre_clav_sri');
                $ncre_cod_mone = $oIfx->f('ncre_cod_mone');
                $ncre_cod_clpv= $oIfx->f('ncre_cod_clpv');
                $ncre_cod_hash = $oIfx->f('ncre_cod_hash');
                $ncre_fech_docu = $oIfx->f('ncre_fech_docu');
                $ncre_val_tcam = $oIfx->f('ncre_val_tcam');



                $sql = "select mone_des_mone, mone_sgl_mone, mone_smb_mene from saemone where mone_cod_mone =  $ncre_cod_mone;";
                $moneda= consulta_string($sql,'mone_des_mone', $oCon,'');
                $smbmone= consulta_string($sql,'mone_smb_mene', $oCon,'');
                $sigmone= consulta_string($sql,'mone_sgl_mone', $oCon,'');
               
               

                ///VALIDACION MONEDA
                $sqlmon="select pcon_mon_base, pcon_seg_mone from saepcon where pcon_cod_empr=$idEmpresa";
                $pcon_seg_mone= consulta_string($sqlmon,'pcon_seg_mone', $oCon,'');

                $pcon_mon_base=consulta_string($sqlmon,'pcon_mon_base', $oCon,'');

                if($ncre_cod_mone==$pcon_seg_mone){
                $eti_mone=  substr($sigmone,0,2).$smbmone;
                }
                else{
                    $eti_mone= $smbmone;
                }


              

                $date = date_create($ncre_fech_venc);
                $ncre_fech_venc = date_format($date,'d/m/Y');

                $date = date_create($ncre_fech_fact);
                $ncre_fech_fact = date_format($date,'d/m/Y');

                //DATOS DE LA EMPRESA

                if ($ncre_cod_fact == '') {
                    $ncre_cod_fact = 0;
                }

                $sql = "select fact_nse_fact, fact_num_preimp, fact_fech_fact,
						fact_cm2_fact, fact_fech_sri,fact_auto_sri
						from saefact 
						where fact_cod_empr = $idEmpresa and 
						fact_cod_fact = $ncre_cod_fact";
                // var_dump($sql);exit;


                $numero_fac = "";
                if ($oIfx2->Query($sql)) {
                    if ($oIfx2->NumFilas() > 0) {
                        $fact_nse_fact = $oIfx2->f('fact_nse_fact');
                        $fact_num_preimp = intval($oIfx2->f('fact_num_preimp'));
                        $fact_fech_fact = $oIfx2->f('fact_fech_fact');
                        $fact_cm2_fact = $oIfx2->f('fact_cm2_fact');

                        $nse = substr($fact_nse_fact, 0, 3);
                        $pto = substr($fact_nse_fact, 3, 6);
                        $numero_fac =  $pto . '-' . $fact_num_preimp;

                        $fact_fech_sri = $oIfx2->f('fact_fech_sri');
                        if(!empty($fact_fech_sri)){
                            $fact_fech_sri = date('d/m/Y',strtotime(substr($fact_fech_sri,0,10))).' '.substr($fact_fech_sri,11,8);
                        }

                        $fact_auto_sri = $oIfx2->f('fact_auto_sri');
                        if(!empty($fact_auto_sri)){
                            $text_1=substr($fact_auto_sri,0,20);
                            $text_2=substr($fact_auto_sri,20,20);
                            $text_3=substr($fact_auto_sri,40,20);
                            $fact_auto_sri=$text_1.'<br>'.$text_2.'<br>'.$text_3;
                        }
                        
                    } else {
                        if ($ncre_cod_fact == 0) {
                            $sqlNcre = "select ncre_nse_ncre, ncre_cod_aux, ncre_fec_emfa from saencre where 
                                    ncre_cod_ncre = $id and 
                                    ncre_cod_empr = $idEmpresa and 
                                    ncre_cod_sucu = $idSucursal";
                            if ($oIfx2->Query($sqlNcre)) {
                                if ($oIfx2->NumFilas() > 0) {
                                    $fact_nse_fact = $oIfx2->f('ncre_nse_ncre');
                                    $numero_fac = $oIfx2->f('ncre_cod_aux');
                                    //$fact_fech_fact = fecha_sri($oIfx2->f('ncre_fec_emfa'));
                                    //$fact_fech_fact = $oIfx2->f('ncre_fec_emfa');
                                }
                            }
                        }else{
                            $nse = substr($fact_nse_fact, 0, 3);
                            $pto = substr($fact_nse_fact, 3, 6);
                            $numero_fac = $pto . '-' . $fact_num_preimp;
                        }
                    }
                }
                $oIfx2->Free();

                if(!empty($fact_fech_fact)){
                    $date = date_create($fact_fech_fact);
                    $fact_fech_fact = date_format($date,'d/m/Y');
                }

                if(!empty($ncre_fech_docu)){
                    $date = date_create($ncre_fech_docu);
                    $fact_fech_fact = date_format($date,'d/m/Y');
                }
                

               
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();


    //CODIGO QR

    $barcode = new \Com\Tecnick\Barcode\Barcode();

    $total_ncre=$ncre_iva + $ncre_con_miva + $ncre_sin_iva - $totalDescuento;

    $datosqr='DOCUMENTO NO AUTORIZADO|'.$ruc_empr.'|'.$ncre_nse_ncre.'|'.$ncre_num_preimp.'|'.number_format($total_ncre, 2, '.', ',').'|'.$ncre_fech_fact.'|'.$ncre_ruc_clie;
    if($ncre_aprob_sri=='S'){
        $datosqr='https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$link_qr;

        $bobj = $barcode->getBarcodeObj(
            'QRCODE,H',                     // Tipo de Barcode o Qr
            $datosqr,          // Datos
            -2,                             // Width 
            -2,                             // Height
            'black',                        // Color del codigo
            array(-1, -1, -1, -1)           // Padding
            )->setBackgroundColor('white'); // Color de fondo
    }
    else{
        $bobj = $barcode->getBarcodeObj(
            'QRCODE,H',                     // Tipo de Barcode o Qr
            $datosqr,          // Datos
            -2.9,                             // Width 
            -2.9,                             // Height
            'black',                        // Color del codigo
            array(-1, -1, -1, -1)           // Padding
            )->setBackgroundColor('white'); // Color de fondo
    }

   

    $imageData = $bobj->getPngData(); // Obtenemos el resultado en formato PNG
        
    
    file_put_contents(DIR_FACTELEC . 'modulos/envio_documentos_colombia/qr_nota_credito/NC_'.$id.'.png', $imageData); // Guardamos el resultado
    
    $ruta=DIR_FACTELEC . 'modulos/envio_documentos_colombia/qr_nota_credito/NC_'.$id.'.png';


    //CABECERA DE LA NOTA DE CREDITO

    $logo ='<table border="0"  style="font-size:11px; width: 100%;"  cellspacing="1">';
    $logo .= '<tr>';

    $logo .= '<td style="width:45%;" >';
    $logo .='<table border="0"   cellspacing="0">';

    $logo .= '<tr>';
    $logo .= '<td  style="width:100%;" align="left">'.$logo_empresa.'</td>';
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td style="width:100%;" >' .$razonSocial . ' NIT '.$ruc_empr.'</td>';
    $logo .= '</tr>';

    ///LEYENDAS PDF- XML 

    $sqlxml = "select ixml_tit_ixml, ixml_det_ixml from saeixml where ixml_cod_empr=$idEmpresa 
               and ixml_est_deleted ='S' and ixml_sn_pdf='S' order by ixml_ord_ixml";

               if ($oIfx->Query($sqlxml)) {
                   if ($oIfx->NumFilas() > 0) {
                       do {
                           $titulo  = $oIfx->f('ixml_tit_ixml');
                           $detalle = $oIfx->f('ixml_det_ixml');
                           $logo .= '<tr>';
                           $logo .= '<td style="width:100%;" >' . $detalle . '</td>';//VALIDAR
                           $logo .= '</tr>';
                       } while ($oIfx->SiguienteRegistro());
                   }
               }
   $oIfx->Free();

   
    $logo .='</table>';
    $logo .= '</td>';


    $logo .= '<td style="width:55%;" >';

    $logo .='<table border="0"  style="font-size:11px; "  cellspacing="0">';

    $logo .= '<tr>';
    $logo .= '<td  style="font-size:16px; width:70%;" align="left"><b>Nota Crédito de la Factura<br> Electrónica de Venta</b></td>';
    $logo .= '<td style="width:30%;" align="left">'.$ncre_nse_ncre.'-'.$ncre_num_preimp.'</td>';
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td  colspan="2" align="left">Representación Gráfica</td>';
    $logo .= '</tr>';
    
    $logo .='</table>';

    $logo .='<table border="0"  style="font-size:11px;  margin-top:15px;"  cellspacing="0">';

    $logo .= '<tr>';
    $logo .= '<td  style="width:33%;" align="left">Tipo de Documento</td>';
    $logo .= '<td  style="width:40%;" align="right">Nota Crédito</td>';//VALIDAR
    $logo .= '<td  style="width:27%;" rowspan="9" align="left"><img src="'.$ruta.'"></td>';
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td  style="width:33%;" align="left">Tipo de Operación</td>';
    $logo .= '<td  style="width:40%;" align="right">Estandar</td>';//VALIDAR
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td  style="width:33%;" align="left">Fecha de Generación</td>';
    $logo .= '<td  style="width:40%;" align="right">'.$fecha_gen.'</td>';
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td  style="width:33%;" align="left">Fecha de Vencimiento</td>';
    $logo .= '<td  style="width:40%;" align="right">'.$fecha_venc.'</td>';
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td  style="width:33%;" align="left">Fecha de Validación</td>';
    $logo .= '<td  style="width:40%;" align="right">'.$fecha_gen.'</td>';
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td  style="width:33%;" align="left">Forma de Pago</td>';
    $logo .= '<td  style="width:40%;" align="right">'.$fpagop_des_fpagop.' '.$numero_dias.'</td>';
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td  style="width:33%;" align="left">Medio de Pago</td>';
    $logo .= '<td  style="width:40%;" align="right"></td>';//VALIDAR
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td  style="width:33%;" align="left">Moneda</td>';
    $logo .= '<td  style="width:40%;" align="right">'.$sigmone.'</td>';//VALIDAR
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td  style="width:33%;" align="left">Factura Asociada</td>';
    $logo .= '<td  style="width:40%;" align="right">'.$numero_fac.'</td>';//VALIDAR
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td  style="width:33%;" align="left">Fecha Generación CUFE</td>';
    $logo .= '<td  style="width:40%;" align="right"></td>';//VALIDAR
    $logo .= '</tr>';

    $logo .= '<tr>';
    $logo .= '<td  style="width:100%;" colspan="3" align="left"><b>CUFE:</b></td>';
    $logo .= '</tr>';
    
    $logo .='</table>';
    
    $logo .= '</td>';

    $logo .= '</tr>';
   
    $logo.='</table>';

    //DATOS EL CLIENTE


    $sql = "SELECT clv_con_clpv, sp_telefonos(saeclpv.clpv_cod_empr,clpv_cod_sucu,saeclpv.clpv_cod_clpv) telefono from saeclpv where clpv_cod_clpv = $ncre_cod_clpv";
    $clv_con_clpv = consulta_string_func($sql, 'clv_con_clpv', $oCon, '');
    $telf_clpv = consulta_string_func($sql, 'telefono', $oCon, '');


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


         $sqlDire = "SELECT id_provincia, id_canton,     id_ciudad,    id_parroquia, id_sector,     id_barrio,      
                           id_bloque,    nomb_conjunto, num_conjunto, estrato,      id_conjunto,   departamento,  poste,
                           caja,         id_ruta,      ruta,      orden_ruta,       direccion,     referencia,    latitud, 
                           longitud,     id_calle
                    from isp.contrato_clpv
                    where id = $cod_contrato";
                if ($oIfx->Query($sqlDire)) {
                    if ($oIfx->NumFilas() > 0) {
                        $id_provincia   = $oIfx->f('id_provincia');
                        $id_canton      = $oIfx->f('id_canton');
                        $id_ciudad      = $oIfx->f('id_ciudad');
                        $id_parroquia   = $oIfx->f('id_parroquia');
                        $id_sector      = $oIfx->f('id_sector');
                        $id_barrio      = $oIfx->f('id_barrio');
                        $id_bloque      = $oIfx->f('id_bloque');
                        $nomb_conjunto  = $oIfx->f('nomb_conjunto');
                        $num_conjunto   = $oIfx->f('num_conjunto');
                        $estrato        = $oIfx->f('estrato');
                        $id_conjunto    = $oIfx->f('id_conjunto');
                        $direccion      = $oIfx->f('direccion');


                        if (!empty($id_provincia)) {
                            $sql = "SELECT prov_des_prov from saeprov where prov_cod_prov = $id_provincia ";

                            if ($oCon->Query($sql)) {
                                if ($oCon->NumFilas() > 0) {
                                    $departamento     = $oCon->f('prov_des_prov');
                                }
                            }
                            $oCon->Free();
                        }

                        if (!empty($id_ciudad)) {
                            $sql = "SELECT ciud_cod_ciud, ciud_nom_ciud from saeciud where ciud_cod_ciud = $id_ciudad ";
                            if ($oCon->Query($sql)) {
                                if ($oCon->NumFilas() > 0) {
                                    $distrito     = $oCon->f('ciud_nom_ciud');
                                }
                            }
                            $oCon->Free();
                        }

                        if (!empty($id_canton)) {
                            $sql = "SELECT cant_cod_cant, cant_des_cant from saecant where cant_cod_cant = $id_canton and cant_est_cant = 'A' ";
                            if ($oCon->Query($sql)) {
                                if ($oCon->NumFilas() > 0) {
                                    $provincia     = $oCon->f('cant_des_cant');
                                }
                            }
                            $oCon->Free();
                        }

                        if (!empty($id_sector)) {
                            $sql = "SELECT id, sector from comercial.sector_direccion where  id = $id_sector ";

                            if ($oCon->Query($sql)) {
                                if ($oCon->NumFilas() > 0) {
                                    $urbanizacion     = $oCon->f('sector');
                                }
                            }
                            $oCon->Free();
                        }
                    }
                }
                $oIfx->free();

                if(empty($ncre_dir_clie)) $ncre_dir_clie=$direccion;




     //DATOS DEL CLIENTE

     $logo .='<table border="0"  style="width: 100%; margin-top:20px;"  cellspacing="0">';

     $logo .= '<tr>';
     $logo .= '<td  colspan="2" style="font-size:20px; border-bottom: '.$empr_web_color.' 1px solid; border-right: '.$empr_web_color.' 1px solid; width:50%;" align="center"><b>DATOS DEL EMISOR</b></td>';
     $logo .= '<td  colspan="2" style="font-size:20px; border-bottom: '.$empr_web_color.' 1px solid; width:50%;" align="center"><b>DATOS DEL CLIENTE</b></td>';//VALIDAR
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >Razón Social</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >'.$razonSocial.'</td>';
     $logo .= '<td  style="width:15%;" >Razón Social</td>';
     $logo .= '<td  style="width:35%;" >'.$ncre_nom_cliente.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >NIT</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >'.$ruc_empr.'</td>';
     $logo .= '<td  style="width:15%;" >'.$tip_iden_cliente.'</td>';
     $logo .= '<td  style="width:35%;" >'.$ncre_ruc_clie.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >Obligación</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >NO APLICA</td>';//VALIDAR
     $logo .= '<td  style="width:15%;" >Obligación</td>';
     $logo .= '<td  style="width:35%;" >IVA</td>';//VALIDAR
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >Email</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >'.$empr_mai_empr.'</td>';
     $logo .= '<td  style="width:15%;" >Email</td>';
     $logo .= '<td  style="width:35%;" >'.$ncre_email_clpv.'</td>';
     $logo .= '</tr>';


     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >Teléfono</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >'.$tel_empresa.'</td>';
     $logo .= '<td  style="width:15%;" >Teléfono</td>';
     $logo .= '<td  style="width:35%;" >'.$telf_clpv.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >Dirección</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >'.$dirMatriz.'</td>';
     $logo .= '<td  style="width:15%;" >Dirección</td>';
     $logo .= '<td  style="width:35%;" >'.$ncre_dir_clie.'</td>';
     $logo .= '</tr>';

     $logo .= '<tr>';
     $logo .= '<td  style="width:15%;" >Ciudad, Depart.</td>';
     $logo .= '<td  style="width:35%; border-right: '.$empr_web_color.' 1px solid;" >'.$ciudad.', '.$dep_empr.' </td>';
     $logo .= '<td  style="width:15%;" >Ciudad, Depart.</td>';
     $logo .= '<td  style="width:35%;" >'.$provincia .', '.$departamento.'</td>';//VALIDAR
     $logo .= '</tr>';

     $logo.='</table>';


    //DETALLE DE LA NOTA DE CREDITO

    $sqlDeta = "select 
    dncr_cant_dfac as dfac_cant_dfac,
    dncr_precio_dfac as dfac_precio_dfac,
    dncr_cod_prod as dfac_cod_prod,
    dncr_det_dncr as dfac_det_dfac,
    dncr_mont_total as dfac_mont_total,
    dncr_por_iva as dfac_por_iva,
    dncr_des1_dfac as dfac_des1_dfac,
    dncr_des2_dfac as dfac_des2_dfac,
    dncr_por_dsg as dfac_por_dsg,
    dncr_cod_dfac,
    dncr_cod_unid
    from saedncr where dncr_cod_ncre = $id and dncr_cod_sucu = $idSucursal and dncr_cod_empr = $idEmpresa ";

    $deta .= ' <table  style="width: 100%;  font-size: 12px;   margin-top:20px;" cellpadding="1" cellspacing="0">';
    $deta .= ' <tr >';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 4%;"  align="left"   height="30">No</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 12%;" align="left"   height="30">REF</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 22%;" align="left"   height="30">DESCRIPCIÓN</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="right"  height="30">CANT</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="center" height="30">U/M</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="center" height="30">PRECIO</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 8%;" align="center" height="30">IMP</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="right"  height="30">SUBTOTAL</td> </b>';
    $deta .= ' <b> <td style=" font-size: 15px; border-top: '.$empr_web_color.' 1px solid; border-bottom: '.$empr_web_color.' 1px solid; width: 14%;" align="right"  height="30">TOTAL ITEM</td> </b>';
    $deta .= ' </tr>';

    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            $i=1;
            $total_item=0;
            do{
                $dfac_cant_dfac = $oIfx->f('dfac_cant_dfac');
                $dfac_cod_prod = $oIfx->f('dfac_cod_prod');
                $dfac_det_dfac = $oIfx->f('dfac_det_dfac');
                $dfac_mont_total = $oIfx->f('dfac_mont_total');
                $dfac_por_iva = $oIfx->f('dfac_por_iva');
                $dncr_cod_dfac = $oIfx->f('dncr_cod_dfac');
                if(empty($dncr_cod_dfac)){
                    $dncr_cod_dfac='NULL';
                }
                $dfac_cod_unid = $oIfx->f('dncr_cod_unid');

                $dfac_precio_dfac = $oIfx->f('dfac_precio_dfac');
                $dfac_des1_dfac = $oIfx->f('dfac_des1_dfac');
                $dfac_des2_dfac = $oIfx->f('dfac_des2_dfac');
                $dfac_por_dsg = $oIfx->f('dfac_por_dsg');

                if(round($dfac_por_iva, 2)>0) $iva=$array_imp ['IVA'].' '.round($dfac_por_iva).'%';
                else
                $iva=$array_imp ['IVA'].' '.'0%';


                
                $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                if ($descuento > 0){
                    $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                    $descuento =round($descuento, 2, PHP_ROUND_HALF_UP);
                }
                else{
                    $descuento = '0';
                }

                if (round($dfac_por_iva, 2) > 0) {
                    $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                    $valor_iva = ($dfac_mont_total * $porcentaje_iva);
                    $total_item = $dfac_mont_total + $valor_iva;
                    
                }
                else{
                    $total_item = $dfac_mont_total;
                }

                if (empty($dfac_det_dfac)) {
                    $dfac_det_dfac = 'Sin detalle';
                }

              

                /*if($fact_cod_mone==$pcon_seg_mone){
                    $dfac_precio_dfac=$dfac_precio_dfac/$fact_val_tcam;
                    $dfac_mont_total=$dfac_mont_total/$fact_val_tcam;
                    $descuento=$descuento/$fact_val_tcam;
                    $totalDescuento=$totalDescuento/$fact_val_tcam;
                }*/

                
                $deta .= ' <tr>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 4%;">'.$i.'</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 12%;">'.$dfac_cod_prod.'</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 22%;">'.$dfac_det_dfac.'</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="right" >' . number_format($dfac_cant_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="center">' . $unidad . '</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="center" >'.$eti_mone.'' . number_format($dfac_precio_dfac, 2, '.', ',') . '</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 8%;" align="center" >' . $iva. '</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 10%;" align="right" >'.$eti_mone.'' . number_format($dfac_mont_total, 2, '.', ',') . '</td>';
                $deta .= ' <td style="border-bottom: '.$empr_web_color.' 1px solid; width: 14%;" align="right" >'.$eti_mone.'' . number_format($total_item, 2, '.', ',') . '</td>';
                $deta .= ' </tr>';

            }while ($oIfx->SiguienteRegistro());
               
        }
    }

    $oIfx->Free();

    $deta .= ' </table>';

    $total_ncre=$ncre_iva + $ncre_con_miva + $ncre_sin_iva - $totalDescuento;

    if($ncre_cod_mone==$pcon_seg_mone){
        $total_ncre=$total_ncre/$ncre_val_tcam;
        $ncre_tot_fact=$ncre_tot_fact/$ncre_val_tcam;
    }

    

    
    $totales ='<table style="margin-top:15px; width: 100%;  font-size: 13px;" cellspacing="0">';
    $totales .='<tr>
    <td style="width:56%;">&nbsp;</td>';

    $totales.='<td style="width:44%;">';

    $totales .= ' <table style="font-size: 13px;"   cellspacing="0" >';
    $totales .= ' <tr>';
    $totales .= ' <td style="font-size:14px; border-bottom: '.$empr_web_color.' 1px solid; width: 65%;"  height="18" >Subtotal</td>';
    $totales .= ' <td style="font-size:14px; border-bottom: '.$empr_web_color.' 1px solid; width: 35%;" align="right">'.$eti_mone.'' . number_format($ncre_tot_fact, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';

    $totales .= ' <tr>';
    $totales .= ' <td style="font-size:15px; width: 65%;" height="18" ><br>Total a Pagar</td>';
    $totales .= ' <td style="font-size:15px; width: 35%;" align="right"><br>'.$eti_mone.'' . number_format($total_ncre, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';


    $totales .= ' </table>';

    $totales.='</td></tr>';
    $totales .='</table>';

    ///FIRMAS


    $firmas ='<table style="margin-top:50px; width: 100%;  font-size: 13px;" cellspacing="0">';
    $firmas .='<tr>';
    $firmas .='<td style="width:12%;">&nbsp;</td>';
    $firmas .='<td style="border-bottom: '.$empr_web_color.' 1px solid; width:34%;"></td>';
    $firmas .='<td style="width:4%;">&nbsp;</td>';
    $firmas .='<td style="border-bottom: '.$empr_web_color.' 1px solid; width:34%;"></td>';
    $firmas .='<td style="width:12%;">&nbsp;</td>';
    $firmas .='</tr>';
    $firmas .='<tr>';
    $firmas .='<td style="width:12%;">&nbsp;</td>';
    $firmas .='<td style="width:33%;" align="center">FIRMA EMISOR</td>';
    $firmas .='<td style="width:6%;">&nbsp;</td>';
    $firmas .='<td style="width:33%;" align="center">FIRMA CLIENTE</td>';
    $firmas .='<td style="width:12%;">&nbsp;</td>';
    $firmas .='</tr>';
    $firmas .='</table>';






    $legend = '<page_footer>
        <table align="center" style="width: 100%">
            <tr>
                <td style="font-size: 11px; width:60%;" align="center">CUDE:<br>'.$fact_cod_hash.'</td>
                <td style="font-size: 10px; width:40%; color: #6B6565; background-color: transparent;" >Este comprobante electronico ha sido generado a traves de Sisconti S.A. - Facturacion Electronica<br>www.sisconti.com</td>
            </tr>
        </table>
    </page_footer>';

    $documento .= '<page backimgw="100%" backtop="5mm" backbottom="5mm" backleft="2mm" backright="5mm" footer="page">';
    $documento .= $logo . $cliente . $deta . $totales .$firmas. $tableLeyenda;
    $documento .= $legend;
    $documento .= '</page>';


    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
    $html2pdf->WriteHTML($documento);
    $ruta_dir= DIR_FACTELEC . 'modulos/envio_documentos_colombia/upload';
	if (!file_exists($ruta_dir)){
	    mkdir($ruta_dir,0777,true);
	}
    $ruta = DIR_FACTELEC . 'modulos/envio_documentos_colombia/upload/pdf/cred_' . $nombre_archivo . '.pdf';
    $html2pdf->Output($ruta, 'F');
    $rutaPdf = $ruta;

    return $documento;
}

?>