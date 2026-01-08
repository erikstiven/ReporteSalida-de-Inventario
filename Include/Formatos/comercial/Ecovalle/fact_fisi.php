<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<?

include_once('../../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
	if (isset($_REQUEST['codigo'])){
		$idfact = $_REQUEST['codigo'];
	}else{
		$idfact = 'NULL';
	}
	
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

global $DSN_Ifx, $DSN;

// conexxion

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();


$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();

$idEmpresa      = $_SESSION['U_EMPRESA'];

$dia=date('d');
$mes=date('m');
$anio=date('y');

    $sqlfac="select * from saefact where fact_cod_fact=$idfact";
    if ($oIfx->Query($sqlfac)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $fact_fech_fact     = $oIfx->f('fact_fech_fact');
                $dia_fac=date('d',strtotime($fact_fech_fact));
                $mes_fac=nomb_mes(date('m', strtotime($fact_fech_fact)));
                $anio_fac=date('y',strtotime($fact_fech_fact));
                $fact_nom_cliente = $oIfx->f('fact_nom_cliente');
                $fact_ruc_clie = $oIfx->f('fact_ruc_clie');
                $fact_dir_clie = $oIfx->f('fact_dir_clie');
                $fact_cod_clpv = $oIfx->f('fact_cod_clpv');
                if(empty($fact_dir_clie)){

                    //DIRECCION DE LA SAECLPV
                    $sqlcli="select sp_direcciones(saeclpv.clpv_cod_empr,clpv_cod_sucu,saeclpv.clpv_cod_clpv) direccion from saeclpv where clpv_cod_clpv= $fact_cod_clpv";
                    $fact_dir_clie = consulta_string($sqlcli,'direccion', $oIfxA,'');
                } 

                $fact_cod_mone = $oIfx->f('fact_cod_mone');
                $fact_val_tcam = $oIfx->f('fact_val_tcam');
                $fact_tot_fact      = $oIfx->f('fact_tot_fact');
                $fact_con_miva      = $oIfx->f('fact_con_miva');
                $fact_sin_miva      = $oIfx->f('fact_sin_miva');
                $fact_dsg_valo      = $oIfx->f('fact_dsg_valo');
                $fact_iva           = $oIfx->f('fact_iva');
                    
                    $sql = "select mone_des_mone, mone_sgl_mone, mone_smb_mene from saemone where mone_cod_mone =  $fact_cod_mone;";
                    $moneda= consulta_string($sql,'mone_des_mone', $oIfxA,'');
                   
                

                    ///VALIDACION MONEDA
                $sqlmon="select pcon_mon_base, pcon_seg_mone from saepcon where pcon_cod_empr=$idEmpresa";
                $pcon_seg_mone= consulta_string($sqlmon,'pcon_seg_mone', $oIfxA,'');

                $pcon_mon_base=consulta_string($sqlmon,'pcon_mon_base', $oIfxA,'');



            }while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

//VARIBALES ESPACIOS ENTRE TEXTOS
$esp1=espacios_fact(1);
$esp2=espacios_fact(22);
$esp3=espacios_fact(35);

$esp4=espacios_fact(4);
$esp5=espacios_fact(1);
$esp6=espacios_fact(4);

$esp7=espacios_fact(3);

$esp8=espacios_fact(9);
$esp9=espacios_fact(9);
$esp10=espacios_fact(27);
$esp11=espacios_fact(53);

//CABECERA

$table_fact = '<table style="margin-left:46px;font-size: 10px;width: 90%; "  cellpadding="1" cellspacing="1" border="0">';
$table_fact .='<tr>
                   <td  style="height: 20px;" colspan="2">'.$dia_fac.''.$esp2.''.$mes_fac.''.$esp3.''.$anio_fac.'</td>
               </tr>';
$table_fact .='<tr>
                   <td style="width:68%; height: 15px;">'.$esp4.''.$fact_nom_cliente.'</td>
                   <td style="width:32%; height: 15px;">'.$esp5.''.$fact_ruc_clie.'</td>
               </tr>';
$table_fact .='<tr>
                   <td colspan="2" style="width:100%; height: 14px;">'.$esp6.''.$fact_dir_clie.'</td>
               </tr>';
$table_fact .='</table>';

//DETALLE

    //DETALLE DE LA FACTURA
    
    $sqlDeta = "select * from saedfac where dfac_cod_fact = $idfact";

    $table_fact .= ' <table style="margin-top:27px;margin-left:0px; font-size: 11px;  width: 90%;" cellpadding="1" cellspacing="1" border="0" align="left">';
   

    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            $ctrl=0;
            $porcIva = '';
            $tot_opgrav=0;
            $tot_opinafe=0;
            $tot_opexo=0;
            $totalDescuento =0;
            do {
                $dfac_cod_prod = $oIfx->f('dfac_cod_prod');
                $dfac_cod_lote = $oIfx->f('dfac_cod_lote');
                $dfac_lote_fcad = $oIfx->f('dfac_lote_fcad');
                $dfac_nom_prod = $oIfx->f('dfac_nom_prod');
                $dfac_cant_dfac = $oIfx->f('dfac_cant_dfac');
                $dfac_precio_dfac = $oIfx->f('dfac_precio_dfac');

                

               

                $dfac_des1_dfac = $oIfx->f('dfac_des1_dfac');
                $dfac_des2_dfac = $oIfx->f('dfac_des2_dfac');
                $dfac_por_dsg = $oIfx->f('dfac_por_dsg');
                $dfac_mont_total = $oIfx->f('dfac_mont_total');
                $dfac_num_comp = $oIfx->f('dfac_tip_dfac');
                $dfac_por_iva = $oIfx->f('dfac_por_iva');
                $dfac_det_dfac = $oIfx->f('dfac_det_dfac');

                //CAMPOS NUEVOS GRABADAS Y NO AFECTAS
                $dfac_obj_iva = $oIfx->f('dfac_obj_iva');
                $dfac_exc_iva = $oIfx->f('dfac_exc_iva');
                

                $dfac_cod_unid = $oIfx->f('dfac_cod_unid');
                if(!empty($dfac_cod_unid)){
                $sqlu="select unid_sigl_unid from saeunid where unid_cod_unid=$dfac_cod_unid";
                $unidad= consulta_string($sqlu,'unid_sigl_unid', $oIfxA,'');
                }
                else{
                    $unidad='';
                }

                if ($dfac_por_iva > 0) {
                    $porcIva = $dfac_por_iva;
                }

                $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                $porcentaje_descuento=$dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

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

              

                if($fact_cod_mone==$pcon_seg_mone){
                    $dfac_precio_dfac=$dfac_precio_dfac/$fact_val_tcam;
                    $dfac_mont_total=$dfac_mont_total/$fact_val_tcam;
                    $descuento=$descuento/$fact_val_tcam;
                    $totalDescuento=$totalDescuento/$fact_val_tcam;
                }

                if ($dfac_por_iva > 0) {
                    $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                    $dfac_mont_total = $dfac_mont_total * $porcentaje_iva;
                    $dfac_precio_dfac = $dfac_precio_dfac * $porcentaje_iva;
                }

                

                if($dfac_obj_iva ==0 && $dfac_exc_iva!=1){
                    $tot_opgrav+=$dfac_precio_dfac;
                }
                elseif($dfac_obj_iva ==1 && $dfac_exc_iva!=1){
                    $tot_opinafe+=$dfac_precio_dfac;
                   
                }
                else{
                    $tot_opexo+=$dfac_precio_dfac;
                
                }
			if(!empty($dfac_cod_lote)){
			$detlote='Lt: '.$dfac_cod_lote;
			}
			else{
				$detlote='';
			}


                    $table_fact .= ' <tr>';
                    $table_fact .= ' <td style="width: 8%;" align="center">' . number_format($dfac_cant_dfac, 2, '.', ',') . '</td>';
                    $table_fact .= ' <td style="width: 63%;" align="left">&nbsp;&nbsp;'.$dfac_nom_prod.'  '.$detlote.'</td>';
                    $table_fact .= ' <td style="width: 12%;" align="right">' . number_format($dfac_precio_dfac, 2, '.', ',') . '</td>';
                    $table_fact .= ' <td style="width: 12%;" align="center">&nbsp;&nbsp;&nbsp;&nbsp;' . number_format($dfac_mont_total, 2, '.', ',') . '</td>';
                    $table_fact .= ' </tr>';
                $ctrl++;
            }while ($oIfx->SiguienteRegistro());
                for ($i=$ctrl; $i <=8 ; $i++) { 
                    # code...
                    $table_fact .= ' <tr>';
                    $table_fact .= ' <td style="width: 8%;" align="center">&nbsp;</td>';
                    $table_fact .= ' <td style="width: 63%;"></td>';
                    $table_fact .= ' <td style="width: 12%;" align="right"></td>';
                    $table_fact .= ' <td style="width: 12%;" align="right"></td>';
                    $table_fact .= ' </tr>';
   
                }
        }
    }

    $fact_tot_fact = $fact_con_miva + $fact_iva+$fact_sin_miva + $fact_val_irbp -$fact_dsg_valo;
    $V = new EnLetras();
    
    if($fact_cod_mone==$pcon_seg_mone)
        {
            $fact_tot_fact = $fact_tot_fact/$fact_val_tcam;
            $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, $moneda));
        }
        else{
            $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, $moneda));
        }

    $table_fact .= ' <tr>';
    $table_fact .= ' <td colspan="2" style="width: 67%;height:15px;" ><p style="margin-top:61px;margin-left:31px;">'.$con_letra.'</p></td>';
    $table_fact .= ' <td style="width: 15%;height:15px;" align="right"></td>';
    $table_fact .= ' <td style="width: 18%;height:15px;" align="right"></td>';
    $table_fact .= ' </tr>';

    $table_fact .= ' <tr>';
    $table_fact .= ' <td colspan="2" style="width: 67%;height:15px;" >&nbsp;</td>';
    $table_fact .= ' <td colspan="2" style="width: 23%;height:15px;" align="right"></td>';
    $table_fact .= ' </tr>';

    $table_fact .= ' <tr>';
    $table_fact .= ' <td colspan="2" style="width: 67%;height:15px;" ><p style="margin-top:19px;">&nbsp;</p></td>';
    $table_fact .= ' <td colspan="2" style="width: 23%;height:15px;" align="left">'.$esp11.''.number_format($fact_tot_fact, 2, '.', ',').'</td>';
    $table_fact .= ' </tr>';

    $table_fact .= ' </table>';




   $html.='<page backimgw="10%" backtop="36mm" backbottom="10mm" backleft="0.2mm" backright="4mm">';
   $html.=$table_fact;
   $html.= '</page>';

   //ESPACIOS EN BLANCO
function espacios_fact($cant)
{
    $n="";
    for ($i=0; $i <=$cant ; $i++) {
        $n.="&nbsp;";
    }
    return $n;
}

	//CONFIGURAICON IMPRESORA PREDETERMINADO A4 CALIDAD ALTA

    $html2pdf = new HTML2PDF('P', 'A4', 'es');
    $html2pdf->WriteHTML($html);
    $html2pdf->Output('recibo_template.pdf', '');?>