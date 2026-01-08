<?
function genera_pdf_doc_comp($pedi, $id, $idempresa, $idsucursal)
{

    session_start();
    global $DSN_Ifx, $DSN;

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


    unset($_SESSION['pdf']);
    $oReturn = new xajaxResponse();

    $usuario_web = $_SESSION['U_ID'];

    try{


    $sqlest = "select pedi_est_pedi from saepedi 
    where pedi_cod_empr=$idempresa and pedi_cod_sucu=$idsucursal
    and pedi_cod_pedi='$pedi'";

    $est = consulta_string_func($sqlest, 'pedi_est_pedi', $oIfxA, '');

//LOGO DEL REPORTE
    $sql = "select empr_web_color, empr_path_logo, empr_nom_empr from saeempr where empr_cod_empr =  $idempresa ";

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $empr_path_logo = $oIfx->f('empr_path_logo');
            $empr_color = $oIfx->f('empr_web_color');
            $razonSocial = $oIfx->f('empr_nom_empr');
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
 
    if ($imagen != '') {

        $empr_logo = '<img src="' . $imagen . '" width="100px" />';
        
    }
    else{
       $empr_logo = '<div style="color:red">LOGO CARGADO</div>'; 
    }
//SOLICITANTE
    $sqlsol = "select pedi_cod_empl, pedi_res_pedi from saepedi where pedi_cod_pedi='$pedi' and pedi_cod_empr=$idempresa and pedi_cod_sucu=$idsucursal";
    $ced = consulta_string_func($sqlsol, 'pedi_cod_empl', $oIfxA, '');
    $responsable = consulta_string_func($sqlsol, 'pedi_res_pedi', $oIfxA, '');
    
//FECHA DE DEL PEDIDO
    $sfecha = "select pedi_fec_pedi, pedi_uso_pedi, pedi_cod_sucu from saepedi where pedi_cod_pedi='$pedi' and pedi_cod_empr=$idempresa and pedi_cod_sucu=$idsucursal";
    $fecha_pedido = consulta_string_func($sfecha, 'pedi_fec_pedi', $oIfx, '');
    $smotivo = consulta_string_func($sfecha, 'pedi_uso_pedi', $oIfx, 0);
    $fecha_pedido = date("Y.m.d", strtotime($fecha_pedido));
    $sucursal = consulta_string_func($sfecha, 'pedi_cod_sucu', $oIfx, 0);
    $spedi = "select sucu_nom_sucu from saesucu where sucu_cod_sucu=$sucursal";
    $nombre_sucursal = consulta_string_func($spedi, 'sucu_nom_sucu', $oIfx, '');



///ARE SOLICITANTE
    $sqla = "select pedi_des_cons, pedi_are_soli from saepedi where pedi_cod_pedi='$pedi' and pedi_cod_empr=$idempresa and pedi_cod_sucu=$idsucursal";
    $sarea = consulta_string_func($sqla, 'pedi_are_soli', $oIfx, 0);
    $obs_pedido= nl2br(consulta_string_func($sqla, 'pedi_des_cons', $oIfx, ''));

    //DETALLE DEL PEDIDO
    $sql = "SELECT  dped_cod_dped,    dped_cod_pedi,  dped_cod_prod,
                    dped_cod_bode,    dped_cod_sucu,  dped_cod_empr,
                    dped_num_prdo,    dped_cod_ejer,  dped_cod_unid,
                    dped_can_ped,     dped_can_ent,   dped_can_ped,
                    dped_prc_dped,    dped_ban_dped,  dped_costo_dped,
                    dped_tot_dped,    dped_prod_nom,  dped_cod_ccos,
                    dped_det_dped,dped_pre_dped, prbo_dis_prod  
                    from saedped
                    INNER JOIN saeprbo b ON dped_cod_prod = b.prbo_cod_prod 
                    AND dped_cod_empr = b.prbo_cod_empr 
                    AND dped_cod_sucu = b.prbo_cod_sucu
                    AND dped_cod_bode = b.prbo_cod_bode 
                    where dped_cod_pedi='$pedi' and dped_cod_empr= $idempresa and dped_cod_sucu = $idsucursal
                     and dped_cod_dped not in(select dped_cod_dped from saedped where dped_est_dped ='1')";

    $des = '';
    $i = 1;
    $cont = 0;
    $total_pre=0;
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $cantidad = $oIfx->f('dped_can_ped');
                $cantidad = round($cantidad, 2);
                $unidad = $oIfx->f('dped_cod_unid');
                $detaprod = $oIfx->f('dped_det_dped');
                $cos = $oIfx->f('dped_cod_ccos');
                $presupuesto = $oIfx->f('dped_pre_dped');
                $stock = $oIfx->f('prbo_dis_prod');

                $citem=$oIfx->f('dped_cod_prod');

                $sqltip="select prod_cod_tpro from saeprod where prod_cod_prod='$citem' and prod_cod_empr=$idempresa";
                $tip=consulta_string($sqltip, 'prod_cod_tpro', $oIfxA, 0);

                /*if(empty($detalle)&&$tip==2){
                    $detalle=$oIfx->f('dped_prod_nom');
                }*/
               
                $nom_prod=$oIfx->f('dped_prod_nom');
                $detalle=$nom_prod.' '.$detaprod;
                $total_pre+=$presupuesto;
                $presupuesto = number_format($presupuesto, 2);
                $tec = $oIfx->f('dped_aut_tecn');

                if (empty($tec)) {
                    $tec = '';
                }
                if (empty($presupuesto)) {
                    $presupuesto = '0';
                }
                if (empty($unidad)) {
                    $unidad = 0;
                }

                $sqlu = "select unid_sigl_unid from saeunid where unid_cod_unid=$unidad";
                if ($oIfxA->Query($sqlu)) {
                    if ($oIfxA->NumFilas() > 0) {
                        $sigla = $oIfxA->f('unid_sigl_unid');
                    }
                }

                $oIfxA->Free();

                ///CONSULTA CENTRO DE COSTO
                $sqlc = "select ccosn_cod_ccosn,  ccosn_nom_ccosn
            from saeccosn where ccosn_cod_ccosn= '$cos'";

                if ($oIfxB->Query($sqlc)) {
                    if ($oIfxB->NumFilas() > 0) {

                        $ccos = $oIfxB->f('ccosn_nom_ccosn');
                    }
                }

                $oIfxB->Free();
                $des .= ' <tr >
                            <td style="font-size:90%;"align="center" width="4%"  border-top:"1px;">' . $i . '</td>
                            <td style="font-size:90%;"align="center" width="20%">' . $citem . '</td>
                            <td style="font-size:90%;"align="left" width="40%">' . $nom_prod . '</td>
                            <td style="font-size:90%;"align="center" width="7%"> ' . $sigla . '</td>
                            <td style="font-size:90%;"align="center" width="7%"> ' . $cantidad . '</td>
                            <td style="font-size:90%;"align="left" width="22%"> ' . $detaprod . '</td>';
                $des .= '</tr>';
                $i++;
                $cont++;
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    for ($i = $cont + 1; $i <= 5; $i++) {

        $col .= '<tr>
        <td style="font-size:80%;"align="center" width="5%">' . $i . '</td>
        <td style="font-size:80%;"align="center" width="10%"></td>
        <td style="font-size:80%;"align="center" width="10%"></td>
        <td style="font-size:80%;"align="left" width="30%"></td>
        <td style="font-size:80%;"align="left" width="30%"></td>
        <td style="font-size:80%;"align="center" width="15%"></td>
    </tr>';
    }

//VLAIDACION DE FIRMAS
   
    $total_pre = number_format(round(($total_pre), 2), 2, '.', ',');

///DISEÑO DEL REPORTE

$html = <<<EOD
<table  border="0"  cellpadding="2" >
    <tr>
     <td  align="left" width="20%">$empr_logo</td>
     <td width="60%" style="font-size:150%;"align="center"><b>$razonSocial </b> <br> <br> <strong> NOTA DE PEDIDO # : $pedi  </strong> </td>
     <td style="font-size:80%;"align="right" width="20%"><b>&nbsp;&nbsp;Fecha: </b>$fecha_pedido </td>
    </tr> 
    <!-- <tr>
        <td style="font-size:150%;"align="center" width="100%"><b> NOTA DE PEDIDO # : </b> $pedi </td>
    </tr>
    <tr>
        <td style="font-size:80%;"align="right" width="100%"><b>&nbsp;&nbsp;Fecha: </b>$fecha_pedido </td>
    </tr> -->
    <tr>
    <td style="font-size:80%;"align="rigth"  colspan="2"></td>
    </tr>
</table>

<table border="0" style="width: 80%; margin-left: 100px;">
    <tr>
        <td style="width: 90%; font-size:10px; text-align: left"><strong>SOLICITADO POR: </strong> $obs_pedido </td>
    </tr>    
    <tr>
        <td style="width: 90%; font-size:10px; text-align: left"><strong>ELABORADO POR: </strong> $responsable </td>  
    </tr>
    <tr>
        <td style="width: 90%; font-size:10px; text-align: left"><strong>MOTIVO PEDIDO: </strong> $smotivo </td>
    </tr>
    <tr>
        <td style="width: 90%; font-size:10px; text-align: left"><strong>AREA SOLICITA: </strong> $sarea </td>
    </tr>
    <tr>
        <td style="width: 90%; font-size:10px; text-align: left"><strong>DESCRIPCIÓN: </strong></td>
    </tr>
    <tr>
        <td style="width: 90%; font-size:10px; text-align: left"> </td>
    </tr>
</table>

<table  border="0"  cellpadding="0">
    <tr>
       
        <td style = "font-size:90%;" align="center" width="4%;" border="1px;"> <strong>N°</strong></td>
        <td style = "font-size:90%;" align="center" width="20%;" border="1px;"> <strong>CODIGO</strong></td>
        <td style = "font-size:90%;" align="center" width="40%;" border="1px;"> <strong>PRODUCTO</strong></td>
        <td style = "font-size:90%;" align="center" width="7%;" border="1px;"> <strong>UNID.</strong></td>
        <td style = "font-size:90%;" align="center" width="7%;" border="1px;"> <strong>CANT.</strong></td>
        <td style = "font-size:90%;" align="center" width="22%;" border="1px;"> <strong>USO</strong></td> 

        <!--
        <td style = "width=4%; font-size:10px; align:center; border_bottom:2px"> <strong>Nro.</strong></td>
        <td style = "width=20%; font-size:10px; align:center; border_bottom:2px"> <strong>CODIGO</strong></td>
        <td style = "width=40%; font-size:10px; align:center; border_bottom:2px"> <strong>PRODUCTO</strong></td>
        <td style = "width=10%; font-size:10px; align:center; border_bottom:2px"> <strong>Unid.</strong></td>
        <td style = "width=10%; font-size:10px; align:center; border_bottom:2px"> <strong>Cant.</strong></td>
        <td style = "width=16%; font-size:10px; align:center; border_bottom:2px"> <strong>Uso</strong></td>       -->  
    </tr>
     $des
    <tr>
    <td style="font-size:80%;" align="center" width="20%">
        <table cellspacing="8">
            <tr>
                <td align="center">$logo5</td>
            </tr> 
            <!-- <tr>
                <td align="center">Fecha: $fecha_pedido</td>
            </tr> -->
            <tr>
                <td align="center"><strong> ________________ </strong></td>
            </tr>
            <tr>
            <td align="center"><strong>Elaborado por</strong></td>
            </tr>
            <tr>
                <td align="center">$responsable</td>
            </tr>
        </table>    
    </td>
    <td style="font-size:80%;" align="center" width="20%">
        <table cellspacing="8">
            <tr>
                <td align="center">$logo5</td>
            </tr>
            <tr>
                <td align="center"><strong> ________________ </strong></td>
            </tr>
            <tr>
                <td align="center"><strong>Gerente de Producción</strong></td>
            </tr>
            <tr>
                <td align="center">SILVA MARCOS ING.</td>
            </tr>
        </table>    
    </td>
    <td style="font-size:80%;" align="center" width="20%">
        <table cellspacing="8">
            <tr>
                <td align="center">$logo5</td>
            </tr>
            <tr>
                <td align="center"><strong> _______________ </strong></td>
            </tr>
            <tr>
                <td align="center"><strong>Gerente Administrativo</strong></td>
            </tr>
            <tr>
                <td align="center">JIMENEZ QUEZADA VICTOR ING.</td>
            </tr>
        </table>    
    </td>

    <td style="font-size:80%;" align="center"  width="20%"> 
        <table cellspacing="8">
            <tr>
                <td align="center">$logo5</td>
            </tr>
            <tr>
                <td align="center"><strong> ________________ </strong></td>
            </tr>
            <tr>
                <td align="center"><strong>Gerente de Producción</strong></td>
            </tr>
            <tr>
                <td align="center"></td>
            </tr>
        </table>    
     </td>
     <td style="font-size:80%;" align="center"  width="20%">
     <table cellspacing="8">
         <tr>
             <td align="center">$logo5</td>
         </tr>
         <tr>
             <td align="center"><strong> _______________ </strong></td>
         </tr>
         <tr>
             <td align="center"><strong>Gerente Administrativo</strong></td>
         </tr>
         <tr>
            <td align="center">AB. JACKSON MACHUCA QUIÑONEZ</td>
        </tr>         
     </table>    
    </td>
    </tr>
</table>
EOD;

} catch (Exception $e) {
  
    echo $e->getMessage();
  
}
    if ($id == 1) {

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10,10, 10, true); 
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set image scale factor
        //$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        // set font
        $pdf->SetFont('helvetica', 'N', 10);
        // add a page
        $pdf->AddPage();
  
        $pdf->writeHTMLCell(0, 0, '', '',$html, 0, 1, 0, true, '', true); 

        $fecha = date('d-m-Y H:i:s');
        $docu = 'solicitud_compra' . $pedi . '.pdf';

        $ruta = DIR_FACTELEC . 'Include/archivos';
        if (!file_exists($ruta)){
            mkdir($ruta);
        }

        $ruta = DIR_FACTELEC . 'Include/archivos/solicitudes_compras';
        if (!file_exists($ruta)){
            mkdir($ruta);
        }
        $ruta = DIR_FACTELEC . 'Include/archivos/solicitudes_compras/' . $docu;

        $pdf->Output($ruta, 'F');
        return $ruta;
    } else {
        return $html;
    }
}

?>