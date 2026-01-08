<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<?

include_once('../../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
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
           
        }
    }
    $oIfx->Free();

    $sqlfac="select g.guia_cod_guia, g.guia_cod_sucu, g.guia_cod_guia, g.guia_fech_guia, g.guia_num_preimp, g.guia_ruc_clie, 
    g.guia_nom_cliente, g.guia_iva, g.guia_con_miva, g.guia_tot_guia,g.guia_est_guia,
    g.guia_sin_miva, g.guia_email_clpv, g.guia_erro_sri , c.clv_con_clpv,
    g.guia_tlf_cliente, g.guia_dir_clie, g.guia_hos_guia, g.guia_hol_guia,
    g.guia_num_plac, g.guia_cm3_guia, g.guia_cod_trta, g.guia_nse_guia, g.guia_cod_clpv,
    g.guia_clav_sri, g.guia_aprob_sri, g.guia_cod_vend
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
                $guia_nom_cliente  = $oIfx->f('guia_nom_cliente');
                $guia_ruc_clie     = $oIfx->f('guia_ruc_clie');
                $guia_est_guia     = $oIfx->f('guia_est_guia');
                $guia_cod_sucu     = $oIfx->f('guia_cod_sucu');
                $guia_clav_sri     = $oIfx->f('guia_clav_sri');
                $guia_email_clpv = $oIfx->f('guia_email_clpv');
                $guia_cm3_guia = $oIfx->f('guia_cm3_guia');
                $guia_cm1_guia = $oIfx->f('guia_cm1_guia');
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
                           

                            $sqlDeta = "select dgui_cod_prod, dgui_cant_dgui, dgui_nom_prod, 
                            dgui_des1_dgui, dgui_des2_dgui, dgui_des3_dgui, dgui_des4_dgui, dgui_por_dsg, dgui_cod_unid, dgui_precio_dgui   from saedgui where dgui_fac_dgui = '$dgui_fac_dgui' and dgui_cod_guia = $idgui
                                            and dgui_cod_empr = $idEmpresa and dgui_cod_sucu = $guia_cod_sucu";
                        } else if ($dgui_fac_dgui == '' && $para_sec_para == 1)
                            $sqlDeta = "select dgui_cod_prod, dgui_cant_dgui, dgui_nom_prod, dgui_cod_lote,
                            dgui_des1_dgui, dgui_des2_dgui, dgui_des3_dgui, dgui_des4_dgui, dgui_por_dsg, dgui_cod_unid, dgui_precio_dgui from saedgui where (dgui_fac_dgui = ''  or  dgui_fac_dgui is null ) and dgui_cod_guia = $idgui
                                            and dgui_cod_empr = $idEmpresa and dgui_cod_sucu = $guia_cod_sucu";
                        else
                            $sqlDeta = "select dgui_cod_prod, dgui_cant_dgui, dgui_nom_prod, dgui_cod_lote,
                            dgui_des1_dgui, dgui_des2_dgui, dgui_des3_dgui, dgui_des4_dgui, dgui_por_dsg, dgui_cod_unid, dgui_precio_dgui from saedgui where dgui_cod_guia = $idgui
                                            and dgui_cod_empr = $idEmpresa and dgui_cod_sucu = $guia_cod_sucu";

        
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
                                    $detalle .= '<td align="center" style="width:7%;">&nbsp;&nbsp;&nbsp;' . $codigoInterno . '</td>';
                                    $detalle .= '<td  align="center" style="width:6%;">&nbsp;&nbsp;' . round($cantidad, 0) . '</td>';
                                    $detalle .= '<td style="width:8%;" align="center" >' . $desc_unid . '</td>';
                                    $detalle .= '<td style="width:42%;">' . $descripcion . '</td>';
                                    $detalle .= '<td style="width:14%;" align="right">' . number_format($precio, 2, '.', ',') . '</td>';
                                    $detalle .= '<td style="width:10%;" align="center">&nbsp;&nbsp;&nbsp;&nbsp;' . number_format($porcentaje_descuento, 2, '.', ',') . '</td>';
                                    $detalle .= '<td style="width:10%;" align="center">' . number_format($cantidad*$precio, 2, '.', ',') . '</td>';
        
                                    $detalle .= '</tr>';
                   
                                    $a = $a + $cantidad;
                                    $num++;
                                    $ctrl++;
                                } while ($oIfx2->SiguienteRegistro());
                                for ($i=$ctrl; $i <=12 ; $i++) { 
                                    # code...
                                    $detalle .= '<tr>';
                                    $detalle .= '<td align="left" style="width:7%;">&nbsp;</td>';
                                    $detalle .= '<td  align="center" style="width:6%;"></td>';
                                    $detalle .= '<td style="width:8%;" align="center" ></td>';
                                    $detalle .= '<td style="width:42%;"></td>';
                                    $detalle .= '<td style="width:14%;" align="right"></td>';
                                    $detalle .= '<td style="width:10%;" align="right"></td>';
                                    $detalle .= '<td style="width:10%;" align="right"></td>';
                                         $detalle .= '</tr>';
                   
                                }
                                $detalle .= '</table>';
                            }
                        }

                        $oIfx2->Free();

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






//CABECERA

$table_fact = '<table style="margin-left:5px; font-size: 10px;width: 100%; "  cellpadding="1" cellspacing="1" border="0" >';
$table_fact .='<tr>
                   <td  style="height: 18px;" colspan="2">'.$esp1.''.$dia_gui.''.$esp2.''.$mes_gui.''.$esp3.''.$anio_gui.''.$esp12.''.$dia.''.$esp2.''.$mes.''.$esp2.''.$anio.'</td>
               </tr>';
$table_fact .='<tr>
                    <td  style="height: 26px;" colspan="2">&nbsp;</td>
                </tr>';
$table_fact .='<tr>
                <td  style="width:50%;height: 26px;" >'.$esp4.''.$dirMatriz.'</td>
                <td  style="width:50%;height: 26px;" ></td>
            </tr>';
$table_fact .='<tr>
                   <td style="width:50%; "></td>
                   <td style="width:50%; ">'.$esp5.''.$guia_nom_cliente.'</td>
               </tr>';
$table_fact .='<tr>
                   <td style="width:50%;"><div style="margin-top:34px;width:365px;">'.$esp4.''.$guia_dir_clie.'</div></td>
                   <td style="width:50%;"><div style="margin-top:16px;width:365px;">'.$esp6.''.$guia_dir_clie.'</div>
					  <div style="margin-top:5px;">'.$esp7.''.$guia_ruc_clie.'</div></td>
               </tr>';               
   
$table_fact .='</table>';

$table_fact .=$detalle;

$table_fact .= '<table  style="font-size: 10px;width: 90%; margin-top:61px;"  cellpadding="1" cellspacing="1" border="0">';
$table_fact .='<tr>
                   <td  style="width:70%; height: 7px;" colspan="3"></td>
                   <td  style="width:30%; height: 7px;" align="center" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . number_format($guia_tot_guia, 2, '.', ',') . '</td>
               </tr>';
$table_fact .='<tr>
                   <td  style="width:30%; height: 16px;" >'.$esp9.''.$placa.'</td>
                   <td  style="width:30%; height: 16px;" ><div style="width:300px;">'.$esp10.''.$nombre_transpor.'</div></td>
                   <td  style="width:10%; height: 16px;" ></td>
                   <td  style="width:30%; height: 16px;" ></td>
               </tr>';
$table_fact .='<tr>
                   <td  style="width:30%; height: 16px;" >&nbsp;</td>
                   <td  style="width:30%; height: 16px;" ></td>
                   <td  style="width:10%; height: 16px;" ></td>
                   <td  style="width:30%; height: 16px;" ></td>
               </tr>';               
$table_fact .='<tr>
                   <td  style="width:30%; height: 10px;" >&nbsp;</td>
                   <td  style="width:30%; height: 10px;" ></td>
                   <td  style="width:10%; height: 10px;" ></td>
                   <td  style="width:30%; height: 10px;" ></td>
               </tr>';
$table_fact .='<tr>
                   <td  style="width:30%; height: 16px;" ></td>
                   <td  style="width:30%; height: 16px;" >'.$esp8.''.$id_transpor.'</td>
                   <td  style="width:10%; height: 16px;" ></td>
                   <td  style="width:30%; height: 16px;" ></td>
               </tr>';        
$table_fact .='</table>';




   $html.='<page backimgw="10%" backtop="37mm" backbottom="10mm" backleft="0.2mm" backright="4mm">';
   $html.=$table_fact;
   $html.= '</page>';

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
    $html2pdf->WriteHTML($html);
    $html2pdf->Output('recibo_template.pdf', '');?>