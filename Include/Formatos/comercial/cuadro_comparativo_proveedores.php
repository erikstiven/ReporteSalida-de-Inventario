<?php

function genera_cuadro_comparativo($cod_pedi,$idempresa, $idsucursal, $id, $est)
{

    session_start();
    global $DSN_Ifx, $DSN;
    include_once('../../../../Include/config.inc.php');
    include_once(path(DIR_INCLUDE) . 'html2pdf_v4.03/_tcpdf_5.0.002_old/tcpdf.php');

    $oIfxA = new Dbo();
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfxB = new Dbo();
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oConB = new Dbo;
    $oConB->DSN = $DSN;
    $oConB->Conectar();


    unset($_SESSION['pdf']);

    $fu = new Formulario;
    $fu->DSN = $DSN;
    $oReturn = new xajaxResponse();

   //echo $cod_pedi;exit;

    //LOGOS DEL REPORTE

    $sql = "select empr_iva_empr, empr_img_rep,empr_web_color from saeempr where empr_cod_empr =  $idempresa ";


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $empr_path_logo = $oIfx->f('empr_img_rep');
            $empr_color = $oIfx->f('empr_web_color');
            $empr_iva_empr = round($oIfx->f('empr_iva_empr'));

        }
    }
    $oIfx->Free();

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

        $empr_logo = '<br><div>
        <img src="' . $imagen . '" style="
        width:250px;
        object-fit; contain;">
        </div>';
        $x = '0px';
    }




    //$fecha = date('d/m/Y');

    //$sqlf="select hisped_fpreprof_hisped from saehisped where hisped_cod_pedi=$cod_pedi";
    //$fecha=date('d-m-Y',strtotime(consulta_string_func($sqlf,'hisped_fpreprof_hisped',$oCon,'')));

    // UNIDAD
    $sql = "select unid_cod_unid, unid_sigl_unid from saeunid where unid_cod_empr = $idempresa ";
    unset($array_unid);
    $array_unid = array_dato($oIfx, $sql, 'unid_cod_unid', 'unid_sigl_unid');

    //CABECERA DEL REPORTE

    $pdf = new TCPDF2('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetMargins(10, 10, 10, true);
    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    // set font
    $pdf->SetFont('helvetica', 'N', 10);
    // add a page
    $pdf->AddPage();

    $sqlpr = "SELECT i.id_inv_prof, i.invp_num_invp ,
				i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, 
				i.invp_unid_cod, i.invp_cant_real, invp_cant_stock, invp_det_prod
				from comercial.inv_proforma i where
				i.invp_cod_empr 	= $idempresa and
				i.invp_cod_sucu 	= $idsucursal and
				i.inv_cod_pedi  	= '$cod_pedi'
				order by i.id_inv_prof; ";
    $num_proforma=consulta_string_func($sqlpr,'invp_num_invp',$oConA,0);


    $table_op .= '<br>';
    $table_op .= '<table   border="1"  cellpadding="1" >';
    $anio=date('Y');
    $table_op .= '<tr><td  align="center" colspan="3">' . $empr_logo . ' </td></tr>
    <tr>    
	<td align="center" style="background-color: '.$empr_color.';" colspan="3"><font color ="#ffffff"><strong>Cuadro Comparativo de Compras</strong></font></td>
    </tr>
    <tr>
    <td style="font-size:80%;" align="left" width="30%" ><strong>Lugar y Fecha:</strong> ' . $fecha . '</td>
    <td style="font-size:80%;" align="center" width="20%"><strong>No. '.$anio.'-'.$num_proforma.'</strong></td>
    <td style="font-size:80%;" align="left" width="50%"><strong>Observaciones</strong></td>
    </tr>
    </table>';

    $table_op .= '<table   border="1"  cellpadding="1" >';
    $table_op .= '<tr>';
    $table_op .= '<th width="2%" style="font-size:80%;" align="center"><font color ="'.$empr_color.'"><strong>Id</strong></font></th>';
    $table_op .= '<th width="3%" style="font-size:80%;" align="center"><font color ="'.$empr_color.'"><strong>Cant.</strong></font></th>';
    $table_op .= '<th width="33%"style="font-size:85%;" align="center" ><font color ="'.$empr_color.'"><strong>Descripcion</strong></font></th>';
    $table_op .= '<th width="4%" style="font-size:80%;" align="center" ><font color ="'.$empr_color.'"><strong>Medida</strong></font></th>';
    $table_op .= '<th width="38%" style="font-size:80%;" align="center" ><font color ="'.$empr_color.'"><strong>Proveedores</strong></font></th>';
    $table_op .= '<th  width="10%" style="font-size:80%;" align="center"><strong><font color ="'.$empr_color.'">Valor Unitario</strong></font></th>';
    $table_op .= '<th  width="10%"  style="font-size:80%;" align="center"><strong><font color ="'.$empr_color.'">Precio Total</strong></font></th>';
    $table_op .= '</tr>';

    ////DATOS DE LOS PROVEEDORES

    $sql = "SELECT i.id_inv_prof, i.invp_num_invp ,
				i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, 
				i.invp_unid_cod, i.invp_cant_real, invp_cant_stock, invp_det_prod
				from comercial.inv_proforma i where
				i.invp_cod_empr 	= $idempresa and
				i.invp_cod_sucu 	= $idsucursal and
				i.inv_cod_pedi  	= '$cod_pedi'
				order by i.id_inv_prof; ";

$i = 1;
$tot_compra=0;
if($oCon->Query( $sql )){
    if($oCon->NumFilas()>0){
       do{
               $prod_cod       = $oCon->f('invp_cod_prod');
               $bode_cod       = $oCon->f('invp_cod_bode');
               $unid_cod       = $oCon->f('invp_unid_cod');
               $prbo_dis       = $oCon->f('invp_cant_stock');
               $pedido         = $oCon->f('invp_cant_real');
               $prod_nom       = $oCon->f('invp_nom_prod');
               $id_inv_prof    = $oCon->f('id_inv_prof');
               $invp_det_prod  = $oCon->f('invp_det_prod');
               $secu_prof  = $oCon->f('invp_num_invp');
               $bode_nom       = $array_bode[$bode_cod];
               $unid_nom       = $array_unid[$unid_cod];

               ///CONTEO NÚMERO DE PROVEEDORES

                $sqlpro="select count(*) as contprove
								from comercial.inv_proforma_det d where
								d.id_inv_prof = $id_inv_prof and
								d.invpd_esta_invpd = 'S'";
                $cont_prove=consulta_string($sqlpro,'contprove',$oConA,0);

                $numfila=0;
                if(round($cont_prove)>0) $numfila=$cont_prove;
                $color_fila='';
                if($i%2==0){
                    $color_fila='#c1c1c1';
                }
               $table_op .= '<tr >';
               $table_op .='<td style="background-color: '.$color_fila.';" align="center"  rowspan="'.$numfila.'">'.$i.'</td>';
               $table_op .='<td style="background-color: '.$color_fila.';" align="center"  rowspan="'.$numfila.'">'.$pedido.'</td>';
               $table_op .='<td style="background-color: '.$color_fila.';" align="left"    rowspan="'.$numfila.'">'.$prod_nom.'<br>'.$invp_det_prod.'</td>';
               $table_op .='<td style="background-color: '.$color_fila.';" align="center"  rowspan="'.$numfila.'">'.$unid_nom.'</td>';

        $sql = "select d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
								d.invpd_ema_clpv, d.invpd_costo_prod, invpd_cun_dmov, invpd_cant_dmov
								from comercial.inv_proforma_det d where
								d.id_inv_prof = $id_inv_prof and
								d.invpd_esta_invpd = 'S'
								order by d.invpd_costo_prod  ";
					//$oReturn->alert($sql);
					unset($array_clpv);
					$x = 1;
					if ($oConA->Query($sql)) {
						if ($oConA->NumFilas() > 0) {
							do{
								$ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
								$ppvpr_pre_pac 	= $oConA->f('invpd_cun_dmov');
								$clpv_nom_clpv 	= $oConA->f('invpd_nom_clpv');
								$correo_clpv    = $oConA->f('invpd_ema_clpv');
								$id_inv_dprof   = $oConA->f('id_inv_dprof');
								$cantidad       = $oConA->f('invpd_cant_dmov');
                                $costo          = $oConA->f('invpd_costo_prod');
                                $total_prod=round(($cantidad*$ppvpr_pre_pac),2);
                                $tot_compra+=$total_prod;
                                if($x==1){
                                    $table_op .='<td style="background-color: '.$color_fila.';" align="left">'.$clpv_nom_clpv.'</td>';
                                    $table_op .='<td style="background-color: '.$color_fila.';" align="right">'.$ppvpr_pre_pac.'</td>';
                                    $table_op .='<td style="background-color: '.$color_fila.';" align="right">'.$total_prod.'</td>';
                                    $table_op .='</tr>';
                                }
                                else{
                                    $table_op .='<tr>';
                                    $table_op .='<td style="background-color: '.$color_fila.';" align="left">'.$clpv_nom_clpv.'</td>';
                                    $table_op .='<td style="background-color: '.$color_fila.';" align="right">'.$ppvpr_pre_pac.'</td>';
                                    $table_op .='<td style="background-color: '.$color_fila.';" align="right">'.$total_prod.'</td>';
                                    $table_op .='</tr>';
                                }
								
		
														
								$x++;
								
								
							}while($oConA->SiguienteRegistro());
						}
					}
					$oConA->Free();
                    $i++;

					$tot_pedi += $pedido;
					$tot_stock+= $prbo_dis;
					
            }while($oCon->SiguienteRegistro());   
            
            $table_op .='<tr>';
                                    $table_op .='<td align="right" colspan="6"><strong>Total:</strong></td>';
                                    $table_op .='<td align="right">'.$tot_compra.'</td>';
                                    $table_op .='</tr>';
         }
     }
     $oCon->Free();

     $table_op .='</table>';

    //DATOS DE LA APROBACION DE LA PROFORMA

    $gestor='';
    $logistica='';
    $gerencia='';

    $sql="SELECT usuario, tipo_aprobacion from comercial.aprobaciones_solicitud_compra c 
	inner join comercial.aprobaciones_compras a
	on c.id_aprobacion=a.id
	and c.empresa=a.empresa
	where 
	c.id_solicitud='$cod_pedi' and 
    c.empresa=$idempresa and
    c.sucursal=$idsucursal and
    a.tipo_aprobacion <> 'COMPRAS'";

    if($oCon->Query( $sql )){
        if($oCon->NumFilas()>0){
           do{

            $user_apro = $oCon->f('usuario');
            $tipo_apro = $oCon->f('tipo_aprobacion');

            $sqlape="select concat(usuario_apellido, ' ', usuario_nombre) as nombres from comercial.usuario where usuario_id=$user_apro";
            $usuario_aprobador=consulta_string($sqlape,'nombres',$oConA,'');

            if($tipo_apro=='PROFPREC'){
                $gestor=$usuario_aprobador;
            }
            elseif($tipo_apro=='PROFAUT'){
                $logistica=$usuario_aprobador;
            }
            elseif($tipo_apro=='PROFOCO'){
                $gerencia=$usuario_aprobador;
            }

           }while($oCon->SiguienteRegistro());        
        }
    }
    $oCon->Free();
   

//////TABLA DE FIRMAS
    $table_op .= '<table   border="1"  cellpadding="1" >
<tr>
<td style="font-size:80%;" align="left" style="background-color: '.$empr_color.';"><font color ="#ffffff">Elaborado por:</font></td>
<td style="font-size:80%;" align="left" style="background-color: '.$empr_color.';"><font color ="#ffffff">Autorizado por:</font></td>
<td style="font-size:80%;" align="left" style="background-color: '.$empr_color.';"><font color ="#ffffff">Orden de Compra generada por:</font></td>
</tr>
<tr>

<td style="font-size:80%;" align="center" ><br><br><br><strong>' . $fir1 . '<br>____________________<br>' . $gestor . '</strong><br>'.$fecha.'<br></td>


<td style="font-size:80%;" align="center" ><br><br><br><strong>' . $fir2 . '<br>____________________<br>' . $logistica . '</strong><br>'.$feprof.'<br></td>

<td style="font-size:80%;" align="center" ><br><br><br><strong>' . $fir3 . '<br>____________________<br>' . $gerencia . '</strong><br>'.$fadj.'<br></td>

</tr>
<tr>

<td style="font-size:80%;" align="center"><strong>Gestor de compras</strong></td>
<td style="font-size:80%;" align="center"><strong>Log&iacute;stica</strong></td>
<td style="font-size:80%;" align="center"><strong>Gerencia</strong></td>
</tr>
</table>';


    if ($id == 1) {



        $pdf->writeHTMLCell(0, 0, '', '', $table_op, 0, 1, 0, true, '', true);

        //$fecha = date('d-m-Y H:i:s');

        $docu = 'cuadro_comparativo' . $cod_pedi . '.pdf';

        $ruta = DIR_FACTELEC . 'Include/archivos';
        if (!file_exists($ruta)){
            mkdir($ruta);
        }
        $ruta = DIR_FACTELEC . 'Include/archivos/comparativo_compras';
        if (!file_exists($ruta)){
            mkdir($ruta);
        }

        $ruta = DIR_FACTELEC . 'Include/archivos/comparativo_compras/' . $docu;


        $pdf->Output($ruta, 'F');
        return $ruta;

    } else {

        return $table_op;
    }
}

?>