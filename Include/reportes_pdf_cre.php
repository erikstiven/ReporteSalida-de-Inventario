<?php
require_once 'html2pdf_v4.03/_tcpdf_5.0.002_old/tcpdf.php';

///PDF SOLICITUD DE COMPRA


function genera_pdf_doc_comp($pedi, $id, $aForm = '')
{

   
    global $DSN_Ifx, $DSN;
    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
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
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    $usuario_web = $_SESSION['U_ID'];

    $sqlest = "select pedi_est_pedi from saepedi 
    where pedi_cod_empr=$idempresa
    and pedi_cod_pedi=$pedi";

    $est = consulta_string_func($sqlest, 'pedi_est_pedi', $oIfxA, '');
///echo 'ESTADO'.$est;exit;
//LOGO DEL REPORTE

    $sql = "select empr_web_color, empr_path_logo from saeempr where empr_cod_empr =  $idempresa ";

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $empr_path_logo = $oIfx->f('empr_path_logo');
            $empr_color = $oIfx->f('empr_web_color');
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

        $empr_logo = '<div>
        <img src="' . $imagen . '" style="
        width:90px;
        object-fit; contain;">
        </div>';
        $x = '0px';
    }




//AUTORIZADOR LOGISTICO
    $sqllog = "select pedi_alog_pedi,pedi_fec_aut from saepedi where pedi_cod_pedi=$pedi";

    $celog = consulta_string_func($sqllog, 'pedi_alog_pedi', $oIfxA, '');

    $array_firma = firma_nomb_empleado($celog);

    foreach ($array_firma as $firma) {

        $logo4 = $firma[0];
        $log = $firma[1];

    }


//FECHA DE AUTORIZACION LOGISTICA


    $fechalog = consulta_string_func($sqllog, 'pedi_fec_aut', $oIfxA, '');

    $fechalog = date("d-m-Y", strtotime($fechalog));


//AUTORIZADOR DEPARTAMENTAL

    $sqldep = "select pedi_adep_pedi from saepedi where pedi_cod_pedi=$pedi";
    $cedep = consulta_string_func($sqldep, 'pedi_adep_pedi', $oIfxA, '');

    $array_firma = firma_nomb_empleado($cedep);

    foreach ($array_firma as $firma) {

        $logo3 = $firma[0];
        $dep = $firma[1];

    }

//FECHA DE AUTORIZACION DEPARTAMENTAL
    $fdep = "select hisped_fadep_hisped from saehisped where hisped_cod_pedi=$pedi";

    $fechadep = consulta_string_func($fdep, 'hisped_fadep_hisped', $oIfxA, '');
    $fechadep = date("d-m-Y", strtotime($fechadep));


//AUTORIZADOR FINANCIERO
    $sqlfin = "select pedi_afin_pedi from saepedi where pedi_cod_pedi=$pedi";
    $cedfin = consulta_string_func($sqlfin, 'pedi_afin_pedi', $oIfx, '');

    $array_firma = firma_nomb_empleado($cedfin);

    foreach ($array_firma as $firma) {

        $logo5 = $firma[0];
        $afin = $firma[1];

    }


//FECHA DE AUTORIZACIÓN FINANCIERA

    $fafin = "select hisped_fafin_hisped from saehisped where hisped_cod_pedi=$pedi";

    $fechaut = consulta_string_func($fafin, 'hisped_fafin_hisped', $oIfxA, '');
    $fecfin = date("d-m-Y", strtotime($fechaut));


//SOLICITANTE

    $sqlsol = "select pedi_cod_empl from saepedi where pedi_cod_pedi=$pedi";
    $ced = consulta_string_func($sqlsol, 'pedi_cod_empl', $oIfxA, '');


    $array_firma = firma_nomb_empleado($ced);

    foreach ($array_firma as $firma) {

        $logo1 = $firma[0];
        $solicitante = $firma[1];

    }


    //CODIGO DE AREEA

    $spedi = "select pedi_carea_pedi from saepedi where pedi_cod_pedi=$pedi";

    $nota_compra = consulta_string_func($spedi, 'pedi_carea_pedi', $oIfx, '');


//FECHA DE DEL PEDIDO


    $sfecha = "select pedi_fec_pedi from saepedi where pedi_cod_pedi=$pedi";
    $fecha_pedido = consulta_string_func($sfecha, 'pedi_fec_pedi', $oIfx, '');
    $fecha_pedido = date("d-m-Y", strtotime($fecha_pedido));


///ARE SOLICITANTE
    $sqla = "select pedi_des_cons, pedi_are_soli from saepedi where pedi_cod_pedi=$pedi";

    $sarea = consulta_string_func($sqla, 'pedi_are_soli', $oIfx, 0);

    $obs_pedido= nl2br(consulta_string_func($sqla, 'pedi_des_cons', $oIfx, ''));


    $sqla="select area_nom_area from saearea where area_cod_area='$sarea'";
    $sarea=consulta_string_func($sqla, 'area_nom_area', $oCon, '');

    //CENTROS DE COSTOS


    $listaproy='';
    $p=1;
    $sqlc="select distinct(dped_cod_ccos) from saedped where dped_cod_pedi=$pedi limit 5";
    if ($oIfx->Query($sqlc)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $codcos=$oIfx->f('dped_cod_ccos');

                ///CONSULTA CENTRO DE COSTO
                $sqlc = "select ccosn_cod_ccosn,  ccosn_nom_ccosn
                from saeccosn where ccosn_cod_ccosn= '$codcos'";

                if ($oIfxB->Query($sqlc)) {
                    if ($oIfxB->NumFilas() > 0) {

                        $ccos = $oIfxB->f('ccosn_nom_ccosn');
                    }
                }

                $oIfxB->Free();



                $listaproy.=$p.'. '.$ccos.' ';


                $p++;

            }while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfxA->Free();


    //DATOS DEL PEDIDO
    $sql = "select dped_cod_dped,    dped_cod_pedi,  dped_cod_prod,
dped_cod_bode,    dped_cod_sucu,  dped_cod_empr,
dped_num_prdo,    dped_cod_ejer,  dped_cod_unid,
dped_can_ped,     dped_can_ent,   dped_can_ped,
dped_prc_dped,    dped_ban_dped,  dped_costo_dped,
dped_tot_dped,    dped_prod_nom,  dped_cod_ccos,
dped_det_dped,dped_pre_dped,dped_aut_tecn from saedped where dped_cod_pedi=$pedi and dped_cod_dped not in(select dped_cod_dped from saedped where dped_est_dped ='1')";

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
                $detalle = $oIfx->f('dped_det_dped');
                $cos = $oIfx->f('dped_cod_ccos');
                $presupuesto = $oIfx->f('dped_pre_dped');

                $citem=$oIfx->f('dped_cod_prod');
                $sqltip="select prod_cod_tpro from saeprod where prod_cod_prod='$citem'";
                $tip=consulta_string($sqltip, 'prod_cod_tpro', $oIfxA, 0);

                if(empty($detalle)&&$tip==2){
                    $detalle=$oIfx->f('dped_prod_nom');
                }

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
            <td style="font-size:80%;"align="center" width="5%">' . $i . '</td>
            <td style="font-size:80%;"align="center" width="10%">' . $cantidad . '</td>
            <td style="font-size:80%;"align="center" width="10%">' . $sigla . '</td>
            <td style="font-size:80%;"align="left"width="30%"> ' . $detalle . '</td>
            <td style="font-size:80%;"align="left" width="30%"> ' . $tec . '</td>';

                //<td style="font-size:80%;"align="center" width="20%">' . $ccos . '</td>
                $des .= '<td style="font-size:80%;"align="right" width="15%">' . $presupuesto . '</td>
        </tr>';
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


    if ($est == 0) {
        $logo2 = '';
        $tec = '';
        $fechatec = '';//tecnica
        $logo3 = '';
        $dep = '';
        $fechadep = '';//gerencia
        $logo5 = '';
        $afin = '';
        $fecfin = '';//financierqa
        $logo4 = '';
        $log = '';
        $fechalog = '';//logistica

    } elseif ($est == 1||$est == 22) {
        $logo3 = '';
        $dep = '';
        $fechadep = '';//gerencia
        $logo5 = '';
        $afin = '';
        $fecfin = '';//financierqa
        $logo4 = '';
        $log = '';
        $fechalog = '';//logistica

    } elseif ($est == 3) {
        $contec = 0;
        $sql = "select dped_raut_tecn from saedped where dped_cod_pedi=$pedi";
        if ($oIfxA->Query($sql)) {
            if ($oIfxA->NumFilas() > 0) {
                do {

                    $raut = $oIfxA->f('dped_raut_tecn');

                    if ($raut == 'SI') {

                        $contec++;
                    }
                } while ($oIfxA->SiguienteRegistro());
            }
        }
        if ($contec == 0) {
            $logo2 = '';
            $tec = '';
            $fechatec = '';//tecnica
        }
    } elseif ($est == 2) {

        $logo3 = '';
        $dep = '';
        $fechadep = '';//gerencia

        $contec = 0;
        $sql = "select dped_raut_tecn from saedped where dped_cod_pedi=$pedi";
        if ($oIfxA->Query($sql)) {
            if ($oIfxA->NumFilas() > 0) {
                do {
                    $raut = $oIfxA->f('dped_raut_tecn');
                    if ($raut == 'SI') {

                        $contec++;
                    }
                } while ($oIfxA->SiguienteRegistro());
            }
        }

        if ($contec == 0) {
            $logo2 = '';
            $tec = '';
            $fechatec = '';//tecnica
        }


    }
    elseif($est == 5||$est == 6||$est == 23){

        $logo2 = '';
        $tec = '';
        $fechatec = '';//tecnica
        $logo3 = '';
        $dep = '';
        $fechadep = '';//gerencia
        $logo5 = '';
        $afin = '';
        $fecfin = '';//financierqa
        $logo4 = '';
        $log = '';
        $fechalog = '';//logistica


    }
    elseif($est == 4){
        $logo3 = '';
        $dep = '';
        $fechadep = '';//gerencia
        $logo5 = '';
        $afin = '';
        $fecfin = '';//financierqa
        $logo4 = '';
        $log = '';
        $fechalog = '';//logistica


    }

    $sqllog = "select pedi_alog_pedi from saepedi where pedi_cod_pedi=$pedi";
    $conlog = consulta_string_func($sqllog, 'pedi_alog_pedi', $oIfxA, '');
    if (empty($conlog)) {

        $logo4 = '';
        $log = '';
        $fechalog = '';//logistica
    }


//AUTORIZADOR TECNICO

    $sqltec = "select pedi_atec_pedi from saepedi where pedi_cod_pedi=$pedi";
    $cedtec = consulta_string_func($sqltec, 'pedi_atec_pedi', $oIfxA, '');

    $ctec='<tr>
	<td style="font-size:80%;" align="center" width="50%">
    
    <table cellspacing="5">
    <tr>
    <td align="center"><strong>Solicitante</strong></td>
    </tr>
    <tr>
    <td align="center">Nombre:' . $solicitante . '</td>
    </tr>
    <tr>
    <td align="center">Fecha: ' . $fecha_pedido . '</td>
    </tr>
    <tr>
    <td align="center">' . $logo1 . '</td>
    </tr>
    <tr>
    <td align="center"><strong> ______________________________ </strong></td>
    </tr>
    <tr>
    <td align="center">FIRMA</td>
    </tr>
    </table>

	
	</td>
	
	<td style="font-size:80%;" align="center" width="50%">

    <table cellspacing="5">
    <tr>
    <td align="center"><strong>Aprobado por : Gerente de area y/o Administrador</strong></td>
    </tr>
    <tr>
    <td align="center">Nombre:' . $dep . '</td>
    </tr>
    <tr>
    <td align="center">Fecha: ' . $fechadep . '</td>
    </tr>
    <tr>
    <td align="center">' . $logo3 . '</td>
    </tr>
    <tr>
    <td align="center"><strong> ______________________________ </strong></td>
    </tr>
    <tr>
    <td align="center">FIRMA</td>
    </tr>
    </table>

	</td>
	</tr>';


    $total_pre = number_format(round(($total_pre), 2), 2, '.', ',');

///DISEÑO DEL REPORTE

    $encabezado = '<table  border="1"  cellpadding="2" >
    <tr>
     <td  align="center">' . $empr_logo . ' </td>';


    $html = '' . $encabezado . '<td style="font-size:80%;"align="center"><br><br><br><b>SOLICITUD DE COMPRA Y / O DESPACHO</b><br> No. ' . $nota_compra . '</td>   
    </tr> 
    <tr>
    <td style="font-size:80%;"align="left"><b>&nbsp;&nbsp;FECHA DE SOLICITUD: </b>' . $fecha_pedido . '</td>
    <td style="font-size:80%;"align="left"><p align="justify"><b>AREA O PROYECTO SOLICITANTE:</b><br>' . $sarea . ' | '. $listaproy.'</p></td>
    </tr>
    <tr>
    <td style="font-size:80%;"align="rigth"  colspan="2"></td>
    </tr>
    </table>
    <table  border="1"  cellpadding="1" >
    <tr>
        <td style="font-size:75%;"align="center" width="5%" style="background-color: '.$empr_color.';" ><font color ="#ffffff"><strong>ID</strong></font></td>
        <td style="font-size:75%;"align="center" width="10%" style="background-color: '.$empr_color.';"><font color ="#ffffff"><strong>Cantidad</strong></font></td>
        <td style="font-size:75%;"align="center" width="10%" style="background-color: '.$empr_color.';"><font color ="#ffffff"><strong>Un de medida</strong></font></td>
        <td style="font-size:75%;"align="center" width="60%" colspan="2" style="background-color: '.$empr_color.';"><font color ="#ffffff"><strong>DETALLE / DESCRIPCIÓN ESPECIFICACIÓN TÉCNICA</strong></font></td>
        <td style="font-size:75%;"align="center" width="15%" style="background-color: '.$empr_color.';"><font color ="#ffffff"><strong>PRESUPUESTO</strong></font></td>
        
    </tr>
     ' . $des . ' ' . $col . '

     <tr>
     <td colspan="5" style="font-size:80%;"align="right"><strong>TOTAL:</strong></td>
     <td style="font-size:80%;"align="right">'.$total_pre.'</td>
      
     </tr>
     ';
    $html .= '
     
    <tr>
    <td align="left" colspan="6"><strong>OBSERVACIONES</strong></td>
    </tr>
    <tr>
    <td align="justify" colspan="6">'.$obs_pedido.'</td>
    </tr>
   </table>

   <table  border="1"  cellpadding="1" >

	' . $ctec . '

    <tr>
    <td style="font-size:80%;" align="center" width="50%">
        <table cellspacing="5">
        <tr>
            <td align="center"><strong>Aprobaci&oacute;n presupuesto GAF Y/O CONTADOR <br>Proyecto Centro de costo</strong></td>
        </tr>
        <tr>
            <td align="center">Nombre:' . $afin . '</td>
        </tr>
        <tr>
            <td align="center">Fecha: ' . $fecfin . '</td>
        </tr>
        <tr>
            <td align="center">' . $logo5 . '</td>
        </tr>
        <tr>
            <td align="center"><strong> ______________________________ </strong></td>
        </tr>
        <tr>
            <td align="center">FIRMA</td>
        </tr>
        </table>    
    </td>

    <td style="font-size:80%;" align="center"  width="50%">
        <table cellspacing="5">
        <tr>
            <td align="center"><strong>Coordinador Nacional de Logistica/ Administrador</strong></td>
        </tr>
        <tr>
            <td align="center">Nombre:' . $log . '</td>
        </tr>
        <tr>
            <td align="center">Fecha: ' . $fechalog . '</td>
        </tr>
        <tr>
            <td align="center">' . $logo4 . '</td>
        </tr>
        <tr>
            <td align="center"><strong> ______________________________ </strong></td>
        </tr>
        <tr>
            <td align="center">FIRMA</td>
        </tr>
        </table>    
     </td>
    </tr>
     ';

    $html .= '</table>';

    if ($id == 1) {

//echo $html;exit;
        $pdf = new TCPDF2('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(10,10, 10, true); 
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('helvetica', 'N', 10);
        $pdf->AddPage();


        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);


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
        return $docu;

    } else {
        return $html;

    }
}

// PDF CUADRO COMPARATIVO

function genera_cuadro_comparativo($cod_pedi, $id, $est, $aForm = '')
{

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
    global $DSN_Ifx, $DSN;;

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

    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];


    //LOGOS DEL REPORTE

    $sql = "select empr_img_rep,empr_web_color from saeempr where empr_cod_empr =  $idempresa ";


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $empr_path_logo = $oIfx->f('empr_img_rep');
            $empr_color = $oIfx->f('empr_web_color');

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

    $sqlf="select hisped_fpreprof_hisped from saehisped where hisped_cod_pedi=$cod_pedi";
    $fecha=date('d-m-Y',strtotime(consulta_string_func($sqlf,'hisped_fpreprof_hisped',$oCon,'')));

    // UNIDAD
    $sql = "select unid_cod_unid, unid_nom_unid from saeunid where unid_cod_empr = $idempresa ";
    unset($array_unid);
    $array_unid = array_dato($oIfx, $sql, 'unid_cod_unid', 'unid_nom_unid');

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



    $table_op .= '<br>';
    $table_op .= '<table   border="1"  cellpadding="1" >';
    $anio=date('Y');
    $table_op .= '<tr><td  align="center" colspan="3">' . $empr_logo . ' </td></tr>
    <tr>    
	<td align="center" style="background-color: '.$empr_color.';" colspan="3"><font color ="#ffffff"><strong>Cuadro Comparativo de Compras</strong></font></td>
    </tr>
    <tr>
    <td style="font-size:80%;" align="left" width="30%" ><font color ="red">Lugar y Fecha:</font> ' . $fecha . '</td>
    <td style="font-size:80%;" align="center" width="20%"><font color ="red">No. CRE-'.$anio.'-'.$cod_pedi.'</font></td>
    <td style="font-size:80%;" align="left" width="50%"><font color ="red">Observaciones</font></td>
    </tr>
    </table>';





    //NUMERO DE PROVEEDORES
    $contp=0;

    //TABLAS DE PROVEEDORES
    $numprove=1;
    $sqlpr="  select count(distinct(invpd_cod_clpv)) as numprove from comercial.inv_proforma i, comercial.inv_proforma_det d
    where i.id_inv_prof=d.id_inv_prof and i.inv_cod_pedi=$cod_pedi";

    $cont_prove=intval(consulta_string_func($sqlpr,'numprove',$oConA,0));




    while($cont_prove%6!=0){
        $cont_prove++;
    }

    $num_tb=$cont_prove/6;



    //ARRAY DE PROVEEDORES
    $arrayprove=array();
    $sqlarray="select invpd_cod_clpv  from comercial.inv_proforma i, comercial.inv_proforma_det d
    where i.id_inv_prof=d.id_inv_prof and i.inv_cod_pedi=$cod_pedi  group by 1 order by 1";
    if ($oConA->Query($sqlarray)) {
        if ($oConA->NumFilas() > 0) {
            do {
                $idclpv=$oConA->f('invpd_cod_clpv');
                array_push($arrayprove, $idclpv);
            }while ($oConA->SiguienteRegistro());
        }
    }



    $strprove='';

    $k=0;
    for ($i=1; $i <=$num_tb ; $i++) {



        for ($j=$k; $j <6*$i ; $j++) {

            if($arrayprove[$j]!=null){
                $aDatos[$i][$j]=$arrayprove[$j];
            }

        }
        $k+=6;

    }


    ///RECORRIDO DE TABLAS DE PROVEEDORES

    $n=0;
    for ($t=1; $t <=$num_tb ; $t++) {

        $k=count($aDatos[$t]);

        $widthcol=70/6;

        $cod_proveedor='';
        for ($j=0; $j <$k ; $j++) {


            $cod_clpv=$aDatos[$t][$n];

            if($k==1){
                $cod_proveedor.="$cod_clpv";
            }
            else{
                $cod_proveedor.="$cod_clpv,";
            }




            $n++;
        }
        if($k>1){
            $cod_proveedor = substr($cod_proveedor,0, strlen($cod_proveedor) - 1);
        }

        if($cod_proveedor!=''){

            $sql = "select i.id_inv_prof, i.invp_num_invp ,
       i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, 
       i.invp_unid_cod, i.invp_cant_real, invp_cant_stock, i.inv_cod_pedi
       from comercial.inv_proforma i where
       i.invp_cod_empr 	= $idempresa and
       i.inv_cod_pedi  	= $cod_pedi order by 1 ; ";

            $i = 1;
            $j = 1;
            $l = 1;
            unset($array);
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    $table_op.='<table   border="1"  cellpadding="1" >';
                    do {

                        $proforma = $oCon->f('invp_num_invp');
                        $unid_cod = $oCon->f('invp_unid_cod');
                        $prod_nom = strtoupper($oCon->f('invp_nom_prod'));
                        $id_inv_prof = $oCon->f('id_inv_prof');
                        $cod_pedi = $oCon->f('inv_cod_pedi');
                        $unid_nom = $array_unid[$unid_cod];

                        $sqlpre="select sum(dped_pre_dped) as pres from saedped where dped_cod_pedi=$cod_pedi and dped_cod_dped not in(select dped_cod_dped from saedped where dped_est_dped ='1')";
                        $pre=consulta_string_func($sqlpre, 'pres', $oIfxA,0);
                        $presupuesto=number_format($pre,2);


                        $sqlccos = "select dped_can_ped,dped_det_dped,dped_cod_ccos from saedped where dped_cod_pedi=$cod_pedi";
                        $cos = consulta_string_func($sqlccos, 'dped_cod_ccos', $oIfxA, '');

                        $sqlproy = "select ccosn_cod_ccosn,  ccosn_nom_ccosn
       from saeccosn where ccosn_cod_ccosn= '$cos'";
                        if ($oIfxA->Query($sqlproy)) {
                            if ($oIfxA->NumFilas() > 0) {

                                $ccos = $oIfxA->f('ccosn_nom_ccosn');

                            }
                        }
                        $oIfxA->Free();

                        $sqlped = "select  p.pedi_cod_pedi, p.pedi_cod_clpv,   p.pedi_fec_pedi,
           p.pedi_fec_entr , p.pedi_res_pedi,  p.pedi_det_pedi,p.pedi_are_soli  from saepedi p  where
           p.pedi_cod_pedi=$cod_pedi and p.pedi_cod_empr=$idempresa";

                        if ($oIfx->Query($sqlped)) {
                            if ($oIfx->NumFilas() > 0) {
                                $fec_pedi = $oIfx->f('pedi_fec_pedi');
                                $table_op .= '<tr >';
                            }
                        }
                        $oIfx->Free();
                        /// Proveedor
                        $table_op .= '<td style="font-size:80%;" align="right" >';
                        $table_op .= '<table border="1"  cellpadding="1"  align="center">';
                        ////PROVEEDOR No.

                        $table_op .= '<tr>';
                        $table_op .= '<td width="30%" style="font-size:80%;" align="left" rowspan="3" ><strong>Presupuesto:</strong> '.$presupuesto.' <br><br>
           Aprobacion sobre incremento del presupuesto
           </td>';

                        if ($est == 1) {
                            $invpdest = "and d.invpd_esta_invpd ='S'";

                        } elseif ($est == 2) {
                            $invpdest = "and d.invpd_esta_invpd ='E'";


                        } elseif ($est == 3) {
                            $invpdest = "and d.invpd_esta_invpd ='A'";

                        } elseif ($est == 4) {
                            $invpdest = "and d.invpd_esta_invpd ='AS'";

                        }

                        else {
                            $invpdest = '';
                        }

                        $sqlpro = "select d.invp_subt_prof,d.invp_iva_prof,d.invp_desc_prof,d.invp_total_prof,d.invpd_adjunto,d.invpd_costo_prod,d.invpd_tent_prof,d.invpd_fpago_prof,d.invpd_vofer_prof,d.invp_ofcom_prof,d.invp_ctzcom_prof,d.invp_sadc_prof,d.invp_exps_prof,d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
               d.invpd_ema_clpv, d.invpd_costo_prod
               from comercial.inv_proforma_det d, comercial.inv_proforma i where
               d.id_inv_prof=i.id_inv_prof and
               d.id_inv_prof = $id_inv_prof and i.inv_cod_pedi=$cod_pedi and d.invpd_cod_clpv in($cod_proveedor) $invpdest 
               order by d.invpd_cod_clpv  ";

                        $x = 1;
                        unset($array_clpv);
                        if ($oConA->Query($sqlpro)) {
                            if ($oConA->NumFilas() > 0) {
                                do {

                                    $ppvpr_cod_clpv = $oConA->f('invpd_cod_clpv');
                                    $correo_clpv = $oConA->f('invpd_ema_clpv');

                                    $table_op .= '	
                               <td width="'.$widthcol.'%" style="font-size:80%;" align="center"   colspan="2"style="background-color: '.$empr_color.';"><font color ="#ffffff"><strong>Proveedor No. ' . $numprove . '</strong></font></td>';

                                    $l++;
                                    $x++;
                                    $numprove++;
                                } while ($oConA->SiguienteRegistro());
                            }
                        }
                        $oConA->Free();

                        $table_op .= '</tr>';
                        //NOMBRE
                        $table_op .= '<tr>';
                        if ($oConA->Query($sqlpro)) {
                            if ($oConA->NumFilas() > 0) {
                                do {
                                    $nprov = $oConA->f('invpd_nom_clpv');
                                    $table_op .= '	
                               <td  width="'.$widthcol.'%" style="font-size:80%;" align="left" colspan="2" ><strong>Nombre:</strong> <br>' . $nprov . '</td>';
                                } while ($oConA->SiguienteRegistro());
                            }
                        }
                        $oConA->Free();

                        $table_op .= '</tr>';
                        //PROFORMA
                        $table_op .= '<tr>';
                        if ($oConA->Query($sqlpro)) {
                            if ($oConA->NumFilas() > 0) {
                                do {
                                    $table_op .= '	
                               <td  width="'.$widthcol.'%" style="font-size:80%;" colspan="2" align="left"><strong>Proforma:</strong> ' . $proforma . '</td>';
                                } while ($oConA->SiguienteRegistro());
                            }
                        }
                        $oConA->Free();

                        $table_op .= '</tr>';

                        //DIRECCION
                        $table_op .= '<tr>';
                        $table_op .= '<td width="5%" style="font-size:80%;" align="center" colspan="2" rowspan="3"><strong>SI</strong></td>';
                        $table_op .= '<td width="21%" style="font-size:80%;" align="center" rowspan="3"><strong>PRY/PRES:</strong><br>'.$ccos.' </td>';
                        $table_op .= '<td width="4%" style="font-size:80%;" align="center" rowspan="3"><strong>NO</strong></td>';
                        if ($oConA->Query($sqlpro)) {
                            if ($oConA->NumFilas() > 0) {
                                do {

                                    $ppvpr_cod_clpv =$oConA->f('invpd_cod_clpv');
                                    $sql="select clpe_dir_clpv from comercial.clpv_pedi where clpe_cod_clpv=$ppvpr_cod_clpv and clpe_cod_pedi=$cod_pedi";
                                    $dir =consulta_string($sql,'clpe_dir_clpv',$oConB,'');
                                    $table_op .= '	
                               <td  width="'.$widthcol.'%" style="font-size:80%;" colspan="2" align="left"><strong>Direccion:</strong><br>'.$dir.'</td>';
                                } while ($oConA->SiguienteRegistro());
                            }
                        }
                        $oConA->Free();

                        $table_op .= '</tr>';
                        //E-MAIL
                        $table_op .= '<tr>';

                        if ($oConA->Query($sqlpro)) {
                            if ($oConA->NumFilas() > 0) {
                                do {
                                    $ppvpr_cod_clpv =$oConA->f('invpd_cod_clpv');
                                    $sql="select clpe_ema_clpv from comercial.clpv_pedi where clpe_cod_clpv=$ppvpr_cod_clpv and clpe_cod_pedi=$cod_pedi";
                                    $correo_clpv =consulta_string($sql,'clpe_ema_clpv',$oConB,'');
                                    $table_op .= '	
                               <td width="'.$widthcol.'%" style="font-size:80%;" colspan="2" align="left"><strong>e-mail:</strong><br>'.strtolower($correo_clpv).'</td>';
                                } while ($oConA->SiguienteRegistro());
                            }
                        }
                        $oConA->Free();

                        $table_op .= '</tr>';
                        //CONTACTO/CELULAR
                        $table_op .= '<tr>';
                        if ($oConA->Query($sqlpro)) {
                            if ($oConA->NumFilas() > 0) {
                                do {

                                    $ppvpr_cod_clpv =$oConA->f('invpd_cod_clpv');
                                    $sql="select clpe_tlf_clpv from comercial.clpv_pedi where clpe_cod_clpv=$ppvpr_cod_clpv and clpe_cod_pedi=$cod_pedi";
                                    $telf =consulta_string($sql,'clpe_tlf_clpv',$oConB,'');
                                    $table_op .= '	
                   <td  width="'.$widthcol.'%" style="font-size:80%;" colspan="2" align="left"><strong>Contacto/ Celular:</strong><br>'.$telf.'</td>';
                                } while ($oConA->SiguienteRegistro());
                            }
                        }
                        $oConA->Free();

                        $table_op .= '</tr>';

                        //DETALLE DE LOS PRODUCTOS
                        $sprof = "select invp_cant_pedi, invp_nom_prod,id_inv_prof,invp_unid_cod,invp_cod_prod from comercial.inv_proforma where invp_num_invp = '$proforma' and inv_cod_pedi=$cod_pedi order by id_inv_prof";

                        $table_op .= '<tr>';
                        $table_op .= '<th width="2%" style="font-size:80%;" align="center"><font color ="'.$empr_color.'"><strong>Id</strong></font></th>';
                        $table_op .= '<th width="3%" style="font-size:80%;" align="center"><font color ="'.$empr_color.'"><strong>Q</strong></font></th>';
                        $table_op .= '<th width="21%"style="font-size:85%;" align="center" ><font color ="'.$empr_color.'"><strong>Descripcion</strong></font></th>';
                        $table_op .= '<th width="4%" style="font-size:80%;" align="center" ><font color ="'.$empr_color.'"><strong>Medida</strong></font></th>';
                        if ($oConB->Query($sqlpro)) {
                            if ($oConB->NumFilas() > 0) {
                                do {
                                    $table_op .= '<th  style="font-size:80%;" align="center"><strong><font color ="'.$empr_color.'">Valor Unitario</strong></font></th>
                                 <th   style="font-size:80%;" align="center"><strong><font color ="'.$empr_color.'">Precio Total</strong></font></th>';
                                } while ($oConB->SiguienteRegistro());
                            }
                        }
                        $oConB->Free();

                        $table_op .= '</tr>';

                        unset($array_prod);
                        $m = 1;
                        if ($oConA->Query($sprof)) {
                            if ($oConA->NumFilas() > 0) {
                                do {
                                    $table_op .= '<tr>';

                                    $cod_prod = $oConA->f('invp_cod_prod');
                                    $idinv = $oConA->f('id_inv_prof');
                                    $prod_nom = $oConA->f('invp_nom_prod');

                                    $sqltip="select prod_cod_tpro from saeprod where prod_cod_prod='$cod_prod'";
                                    $tip=consulta_string($sqltip, 'prod_cod_tpro', $oIfxA, 0);



                                    //UNIDAD
                                    $unid_cod = $oCon->f('invp_unid_cod');
                                    if (empty($unid_cod)) {
                                        $unid_cod = 0;
                                    }

                                    $sqlu = "select unid_sigl_unid from saeunid where unid_cod_unid=$unid_cod";

                                    if ($oIfxA->Query($sqlu)) {
                                        if ($oIfxA->NumFilas() > 0) {
                                            $sigla = $oIfxA->f('unid_sigl_unid');
                                        }
                                    }
                                    $oIfxA->Free();
                                    //CENTRO DE COSTO(PROYECTO)

                                    $sqlccos = "select dped_can_ped,dped_det_dped,dped_cod_ccos from saedped where dped_cod_pedi=$cod_pedi and dped_cod_prod='$cod_prod'";

                                    $cos = consulta_string_func($sqlccos, 'dped_cod_ccos', $oIfxA, '');
                                    $cant = consulta_string_func($sqlccos, 'dped_can_ped', $oIfxA, '');
                                    $deta = consulta_string_func($sqlccos, 'dped_det_dped', $oIfxA, '');

                                    $icant = intval($cant);
                                    $sqlproy = "select ccosn_cod_ccosn,  ccosn_nom_ccosn
                       from saeccosn where ccosn_cod_ccosn= '$cos'";
                                    if ($oIfxA->Query($sqlproy)) {
                                        if ($oIfxA->NumFilas() > 0) {

                                            $ccos = $oIfxA->f('ccosn_nom_ccosn');

                                        }
                                    }
                                    $oIfxA->Free();

                                    $cantidad=$oConA->f('invp_cant_pedi');

                                    $table_op .= '<td style="font-size:80%;" align="center">' . $m . '</td>';
                                    $table_op .= '<td style="font-size:80%;" align="center">' . $cantidad . '</td>';
                                    $table_op .= '<td style="font-size:90%;" align="justify" >' . $prod_nom . '</td>';

                                    $table_op .= '<td style="font-size:80%;" align="center" >' . $sigla . '</td>';

                                    if ($oConB->Query($sqlpro)) {
                                        if ($oConB->NumFilas() > 0) {
                                            do {

                                                $ppvpr_cod_clpv = $oConB->f('invpd_cod_clpv');

                                                $sqprod = "select invpd_costo_prod,invp_ptotal_prof,invp_est_prod,invpd_esta_invpd from comercial.inv_proforma_det where invpd_cod_clpv=$ppvpr_cod_clpv and id_inv_prof= $idinv ";

                                                $var = number_format(consulta_string_func($sqprod, 'invpd_costo_prod', $oCon, 0),2);
                                                $pt = number_format(consulta_string_func($sqprod, 'invp_ptotal_prof', $oCon, 0),2);
                                                $estprod=consulta_string_func($sqprod, 'invp_est_prod', $oCon, '');
                                                $estprove=consulta_string_func($sqprod, 'invpd_esta_invpd', $oCon, '');

                                                $serialprod = '';
                                                $serialprod = $j . '-' . $ppvpr_cod_clpv;

                                                $serialprod = $serialprod . '_vu';

                                                $serialpt = $serialprod . '_pt';


                                                //$pt=$aForm[$serialpt];
                                                //$vu = $aForm[$serialprod];

                                                // $table_op .= '<td style="font-size:80%;" align="center" >' . $prod_nom . '</td>';
                                                $table_op.='<td style="font-size:80%;" align="center">' . $var . '</td>
                               <td style="font-size:80%;" align="right">' . $pt . '</td>';

                                            } while ($oConB->SiguienteRegistro());

                                            $m++;
                                        }
                                    }
                                    $oConB->Free();
                                    $table_op .= '</tr>';

                                    $j++;
                                } while ($oConA->SiguienteRegistro());
                            }
                        }
                        $oConA->Free();

                        ///SUB TOTAL
                        $arraypro = array("Subtotal", "Descuento %", "IVA 12%", "Otros Cargos","Total");
                        $k = 1;
                        unset($array_pro);
                        foreach ($arraypro as $val) {

                            $table_op .= '<tr>';

                            $table_op .= '<td style="font-size:80%;" colspan="4" align="center"></td>';

                            if ($oConB->Query($sqlpro)) {
                                if ($oConB->NumFilas() > 0) {
                                    do {

                                        $clpvcod = $oConB->f('invpd_cod_clpv');

                                        $serialtot = '';
                                        $serialtot = $k . '-' . $clpvcod;

                                        //$table_op .= '<td style="font-size:80%;"></td>';

                                        if ($val == "Subtotal") {
                                            $serialtot = $serialtot . '_st';

                                            //CALCULO DE SUBTOTAL
                                            $sqlsub="select sum(invp_ptotal_prof) as subtotal from  comercial.inv_proforma_det as d,comercial.inv_proforma i where 
                           
                           d.id_inv_prof=i.id_inv_prof and inv_cod_pedi=$cod_pedi  and invpd_cod_clpv=$clpvcod";

                                            $sub =number_format(consulta_string($sqlsub,'subtotal',$oCon,0),2);

                                            //$sub = number_format($oConB->f('invp_subt_prof'),2);

                                            $table_op .= '<td style="font-size:80%;" align="right"><strong>' . $val . '</strong></td>

                       <td style="font-size:80%;" align="right">' . $sub . '</td>';
                                        } elseif ($val == "Descuento %") {
                                            $serialtot = $serialtot . '_des';


                                            //$des = $oConB->f('invp_desc_prof');

                                            //CONSULTA DESCUENTO
                                            $sqldes="select invp_desc_prof from comercial.inv_proforma_det as d,comercial.inv_proforma i where 
                                                       
                           d.id_inv_prof=i.id_inv_prof and inv_cod_pedi=$cod_pedi  and invpd_cod_clpv=$clpvcod and invp_desc_prof >0 limit 1";
                                            $des =consulta_string($sqldes,'invp_desc_prof',$oCon,0);

                                            $table_op .= '<td style="font-size:80%;" align="right">' . $val . '</td>
                       <td style="font-size:80%;" align="right">' . $des . '</td>';
                                        } elseif ($val == "IVA 12%") {
                                            $serialtot = $serialtot . '_iv';


                                            //$iva = $oConB->f('invp_iva_prof');

                                            //CONSULTA DEL IVA

                                            $sqliva="select invp_iva_prof from comercial.inv_proforma_det as d,comercial.inv_proforma i where 
                                                       
                           d.id_inv_prof=i.id_inv_prof and inv_cod_pedi=$cod_pedi  and invpd_cod_clpv=$clpvcod and invp_iva_prof >0 limit 1";

                                            $iva =consulta_string($sqliva,'invp_iva_prof',$oCon,0);

                                            $table_op .= '<td style="font-size:80%;" align="right">' . $val . '</td>
                       <td style="font-size:80%;" align="right">' . $iva . '</td>';
                                        }
                                        elseif($val =="Otros Cargos"){

                                            $sqlcar="select invp_oval_prof from comercial.inv_proforma_det as d,comercial.inv_proforma i where 
                                                       
                           d.id_inv_prof=i.id_inv_prof and inv_cod_pedi=$cod_pedi  and invpd_cod_clpv=$clpvcod  and invp_oval_prof >0 limit 1";
                                            $car =consulta_string($sqlcar,'invp_oval_prof',$oCon,0);


                                            $table_op .='<td style="font-size:80%;" align="right">'.$val.'</td>
                       <td style="font-size:80%;" align="right">' . $car. '</td>';
                                        }
                                        else {
                                            $serialtot = $serialtot . '_tot';

                                            //$total = number_format($oConB->f('invp_total_prof'),2);

                                            $sqltot="select invp_total_prof from comercial.inv_proforma_det as d,comercial.inv_proforma i where 
                                                       
                           d.id_inv_prof=i.id_inv_prof and inv_cod_pedi=$cod_pedi  and invpd_cod_clpv=$clpvcod and invp_total_prof >0 limit 1";
                                            $total =number_format(consulta_string($sqltot,'invp_total_prof',$oCon,0),2);

                                            $table_op .= '<td style="font-size:80%;" align="right"><strong>' . $val . '</strong></td>
                       <td style="font-size:80%;" align="right">' . $total . '</td>';
                                        }

                                    } while ($oConB->SiguienteRegistro());
                                }
                            }
                            $oConB->Free();
                            $table_op .= '</tr>';
                            $k++;
                        }
                        //TERMINOS Y CONDICIONES
                        $table_op .= '<tr><td style="font-size:85%;" colspan="4" align="center" style="background-color: '.$empr_color.';"><font color ="#ffffff">Terminos y Condiciones</font></td>
            
            </tr>';

                        $arrayter = array("Oferta Completa", "Cotizaciones Comprobadas", "Servicios Adicionales", "Experiencia Pasada", "Forma de Pago", "Validez de la oferta", "Plazo de entrega");

                        unset($array_ter);
                        foreach ($arrayter as $val) {

                            $table_op .= '<tr>';

                            $table_op .= '<td style="font-size:85%;" colspan="4" align="center">' . $val . '</td>';

                            if ($oConB->Query($sqlpro)) {
                                if ($oConB->NumFilas() > 0) {
                                    do {

                                        $clpvcod = $oConB->f('invpd_cod_clpv');
                                        $serialter = '';
                                        $serialter = $j . '-' . $clpvcod;
                                        if ($val == "Oferta Completa") {
                                            $serialter = $serialter . '_ofcomp';

                                            //$ocom = $aForm[$serialter];
                                            $ocom = $oConB->f('invp_ofcom_prof');


                                            $iva = $oConB->f('invp_iva_prof');

                                            $table_op .= '
                       <td width="'.$widthcol.'%" style="font-size:80%;" align="center" colspan="3">' . $ocom . '</td>';
                                        } elseif ($val == "Cotizaciones Comprobadas") {
                                            $serialter = $serialter . '_ctz';
                                            //$ctz = $aForm[$serialter];
                                            $ctz = $oConB->f('invp_ctzcom_prof');

                                            $table_op .= '
                       <td width="'.$widthcol.'%" style="font-size:80%;" style="font-size:80%;" align="center" colspan="3">' . $ctz . '</td>';
                                        } elseif ($val == "Servicios Adicionales") {

                                            $serialter = $serialter . '_sad';
                                            //$sad = $aForm[$serialter];
                                            $sad = $oConB->f('invp_sadc_prof');

                                            $table_op .= '
                       <td width="'.$widthcol.'%" style="font-size:80%;" align="center" colspan="3">' . $sad . '</td>';
                                        } elseif ($val == "Experiencia Pasada") {

                                            $serialter = $serialter . '_exp';

                                            //$exp = $aForm[$serialter];
                                            $exp = $oConB->f('invp_exps_prof');
                                            $table_op .= '
                       <td width="'.$widthcol.'%" style="font-size:80%;" align="center" colspan="3">' . $exp . '</td>';
                                        } elseif ($val == "Forma de Pago") {
                                            $serialter = $serialter . '_fpag';

                                            //$fpag = $aForm[$serialter];

                                            $fpag = $oConB->f('invpd_fpago_prof');

                                            $table_op .= '
                       <td width="'.$widthcol.'%" style="font-size:80%;" align="center"colspan="3">' . $fpag . '</td>';
                                        } elseif ($val == "Validez de la oferta") {

                                            $serialter = $serialter . '_vorf';

                                            //$vorf = $aForm[$serialter];
                                            $vorf = $oConB->f('invpd_vofer_prof');


                                            $table_op .= '
                       <td width="'.$widthcol.'%" style="font-size:80%;" align="center" colspan="3">' . $vorf . '</td>';
                                        } elseif ($val == "Plazo de entrega") {


                                            $var = $oConB->f('invpd_tent_prof');

                                            $serialter = $serialter . '_pz';

                                            //$pz = $aForm[$serialter];
                                            $pz = $oConB->f('invpd_tent_prof');

                                            $table_op .= '
                       <td width="'.$widthcol.'%" style="font-size:80%;" align="center" colspan="3">' . $pz . '</td>';
                                        }

                                    } while ($oConB->SiguienteRegistro());
                                }
                            }
                            $oConB->Free();

                            $table_op .= '</tr>';
                            $j++;

                        }
                        $oConA->Free();
                        $table_op .= '</table></td>';
                        $table_op .= '</tr>';
                        $i++;
                    } while ($oCon->SiguienteRegistro());

                }
            }
            $oCon->Free();

            if($t==$num_tb){
                $table_op .= '</table><br><br>';

            }
            else{
                $table_op .= '</table>||||||||||<br><br>';

            }


        }




    }





//DATOS DEL SOLICITANTE
    $sql = "select pedi_empl_apro from saepedi where pedi_cod_pedi=$cod_pedi";
    $ced = consulta_string_func($sql, 'pedi_empl_apro', $oIfxA, '');


    $array_firma = firma_nomb_empleado($ced);

    foreach ($array_firma as $firma) {

        $logo = $firma[0];
        $sol = $firma[1];

    }


//DATOS DE EVALUADOR

    $sql = "select hisped_eprof_hisped, hisped_feprof_hisped from saehisped where hisped_cod_pedi=$cod_pedi";


    $cede = consulta_string_func($sql, 'hisped_eprof_hisped', $oIfxA, '');
    $feprof = consulta_string_func($sql, 'hisped_feprof_hisped', $oIfxA, '');
    $feprof=date('d-m-Y',strtotime($feprof));
    $array_firma = firma_nomb_empleado($cede);

    foreach ($array_firma as $firma) {

        $logo2 = $firma[0];
        $eval = $firma[1];

    }

//DATOS DE LA ADJUDICACION

    $sql = "select hisped_adjprof_hisped, hisped_fadjprof_hisped from saehisped where hisped_cod_pedi=$cod_pedi";

    $cedj = consulta_string_func($sql, 'hisped_adjprof_hisped', $oIfxA, '');
    $fadj = consulta_string_func($sql, 'hisped_fadjprof_hisped', $oIfxA, '');
    $fadj=date('d-m-Y',strtotime($fadj));

    $array_firma = firma_nomb_empleado($cedj);

    foreach ($array_firma as $firma) {

        $logo3 = $firma[0];
        $adj = $firma[1];

    }


//DATOS DE LA APROBACION DE LA ADJUDICACION

    $sql = "select hisped_aproadj_hisped, hisped_faproadj_hisped from saehisped where hisped_cod_pedi=$cod_pedi";

    $cedaj = consulta_string_func($sql, 'hisped_aproadj_hisped', $oIfxA, '');
    $faproadj = consulta_string_func($sql, 'hisped_faproadj_hisped', $oIfxA, '');
    $faproadj=date('d-m-Y',strtotime($faproadj));

    $array_firma = firma_nomb_empleado($cedaj);

    foreach ($array_firma as $firma) {

        $logo1 = $firma[0];
        $cadj = $firma[1];

    }

//VALIDACION DE ESTADO
    $sqlest="select pedi_est_pedi from saepedi where pedi_cod_pedi=$cod_pedi";

    $est=intval(consulta_string($sqlest,'pedi_est_pedi', $oIfxA,0));
    if($est==11){
        $fir1=$logo;
        $nom1 = $sol;

        $fir2='';
        $nom2='';
        $feprof='';

        $fir3='';
        $nom3='';
        $fadj='';

        $fir4='';
        $nom4='';
        $faproadj='';
    }
    elseif($est==12){
        $fir1=$logo;
        $nom1 = $sol;

        $fir2=$logo2;
        $nom2=$eval;

        $fir3='';
        $nom3='';
        $fadj='';

        $fir4='';
        $nom4='';
        $faproadj='';

    }
    elseif($est==13){
        $fir1=$logo;
        $nom1 = $sol;

        $fir2=$logo2;
        $nom2=$eval;

        $fir3=$logo3;
        $nom3=$adj;

        $fir4='';
        $nom4='';
        $faproadj='';
    }
    elseif($est>=14){
        $fir1=$logo;
        $nom1 = $sol;

        $fir2=$logo2;
        $nom2=$eval;

        $fir3=$logo3;
        $nom3=$adj;

        $fir4=$logo1;
        $nom4=$cadj;
    }


//////TABLA DE FIRMAS
    $table_op .= '<table   border="1"  cellpadding="1" >
<tr>
<td style="font-size:80%;" align="left" style="background-color: '.$empr_color.';"><font color ="#ffffff">Elaborado por:</font></td>
<td style="font-size:80%;" align="left" style="background-color: '.$empr_color.';"><font color ="#ffffff">Revisado por:</font></td>
<td style="font-size:80%;" align="left" style="background-color: '.$empr_color.';"><font color ="#ffffff">Adjudicado por:</font></td>
<td style="font-size:80%;" align="left" style="background-color: '.$empr_color.';"><font color ="#ffffff">Adjudicación Aprobada por:</font></td>
</tr>
<tr>

<td style="font-size:80%;" align="center" ><br><br><br><strong>' . $fir1 . '<br>____________________<br>' . $nom1 . '</strong><br>'.$fecha.'<br></td>


<td style="font-size:80%;" align="center" ><br><br><br><strong>' . $fir2 . '<br>____________________<br>' . $nom2 . '</strong><br>'.$feprof.'<br></td>

<td style="font-size:80%;" align="center" ><br><br><br><strong>' . $fir3 . '<br>____________________<br>' . $nom3 . '</strong><br>'.$fadj.'<br></td>

<td style="font-size:80%;" align="center" ><br><br><br><strong>' . $fir4 . '<br>____________________<br>' . $nom4 . '</strong><br>'.$faproadj.'<br></td>
</tr>
<tr>

<td style="font-size:80%;" align="center"><strong>Gestor de compras</strong></td>
<td style="font-size:80%;" align="center"><strong>Log&iacute;stica</strong></td>
<td style="font-size:80%;" align="center"><strong>Solicitante</strong></td>
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
        return $docu;

    } else {

        return $table_op;
    }
}
function reporte_orden_compra_cre($minv_serial = '', $id = '')
{

    setlocale(LC_TIME, 'spanish');
//setlocale(LC_TIME, 'es_EC.UTF-8');
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oCnx = new Dbo ();
    $oCnx->DSN = $DSN;
    $oCnx->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];


    $logo='';

    if (!empty($minv_serial)) {
        $sql="select sucu_nom_sucu from saesucu where sucu_cod_sucu=$idsucursal";
        $sucursal=consulta_string($sql,'sucu_nom_sucu',$oIfxA,'');

        $sql = "select empr_img_rep, empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_path_logo
				from saeempr where empr_cod_empr = $idempresa ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $razonSocial = trim($oIfx->f('empr_nom_empr'));
                $ruc_empr = $oIfx->f('empr_ruc_empr');
                $dirMatriz = trim($oIfx->f('empr_dir_empr'));
                $empr_path_logo = $oIfx->f('empr_img_rep');
                if ($oIfx->f('empr_conta_sn') == 'S')
                    $empr_conta_sn = 'SI';
                else
                    $empr_conta_sn = 'NO';

                $empr_num_resu = $oIfx->f('empr_num_resu');
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

            $empr_logo = '<div>
        <img src="' . $imagen . '" style="
        width:200px;
        object-fit; contain;">
        </div>';
            $x = '0px';
        }
        else{
            $empr_logo = '<span><font color="red">SIN LOGO</font></span>';

        }

        $prove='';
///DATOS DE PROVEEDOR



        $sqlFac = "SELECT distinct( minv_num_comp),   minv_fmov,      clpv_nom_clpv,   
					minv_num_sec, minv_cod_clpv, minv_est_minv, minv_cm3_minv,
					minv_cm1_minv,
					minv_tot_minv,
                    minv_otr_valo,
                    minv_sin_iva,
					minv_iva_valo,
					minv_dge_valo,
					minv_cm6_minv,
                    clpv_ruc_clpv,
                    dmov_cod_pedi,
					(COALESCE(minv_tot_minv,0) - COALESCE(minv_dge_valo,0) + COALESCE(minv_iva_valo,0) + COALESCE(minv_otr_valo,0) - COALESCE(minv_fle_minv,0) + COALESCE(minv_val_ice,0) ) total
					FROM saeminv,    saeclpv,    saedmov   WHERE 
					minv_cod_clpv =  clpv_cod_clpv  and  
					minv_num_comp = dmov_num_comp and  
					minv_cod_tran in  ( select defi_cod_tran from saedefi Where 
					defi_tip_defi  = '4' and 
					defi_cod_empr  = $idempresa and 
					defi_cod_modu  = 10)  AND  
					minv_cod_empr = $idempresa  AND  
					minv_num_comp = $minv_serial AND
					clpv_cod_empr = $idempresa AND
					(( minv_cer_sn is null) or ( minv_cer_sn = 'N' ) or ( minv_cer_sn = 'S' ) )";

        if ($oIfx->Query($sqlFac)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $minv_num_comp = $oIfx->f('minv_num_comp');
                    $minv_est_minv = $oIfx->f('minv_est_minv');
                    $clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
                    $minv_num_sec = $oIfx->f('minv_num_sec');
                    $minv_cod_clpv = $oIfx->f('minv_cod_clpv');

          
                    $minv_fmov = $oIfx->f('minv_fmov');


                    $anio=date('Y',strtotime($minv_fmov));
                    $dia=date('d',strtotime($minv_fmov));
                    $mes=nomb_mes(date('m',strtotime($minv_fmov)));


                    $total = $oIfx->f('total');
                    $minv_cm3_minv = $oIfx->f('minv_cm3_minv');
                    $minv_cm1_minv = $oIfx->f('minv_cm1_minv');
                    $minv_tot_minv = $oIfx->f('minv_tot_minv');
                    $minv_otr_valo = $oIfx->f('minv_otr_valo');
                    $minv_sin_iva = $oIfx->f('minv_sin_iva');
                    $minv_iva_valo = $oIfx->f('minv_iva_valo');
                    $minv_dge_valo = $oIfx->f('minv_dge_valo');
                    $minv_cm6_minv = $oIfx->f('minv_cm6_minv');
                    $mov_cod_pedi  = $oIfx->f('dmov_cod_pedi');
                    $clpv_ruc_clpv= $oIfx->f('clpv_ruc_clpv');
                    $sqlsol="select pedi_carea_pedi from saepedi where pedi_cod_pedi=$mov_cod_pedi";
                    $codsol=consulta_string($sqlsol,'pedi_carea_pedi',$oIfxA,'');

                    $sqlprof="select invp_num_invp from comercial.inv_proforma where inv_cod_pedi=$mov_cod_pedi";
                    $proforma=consulta_string($sqlprof,'invp_num_invp',$oIfxA,'');

                    $sqltlf="select tlcp_tip_ticp, tlcp_tlf_tlcp from saetlcp where tlcp_cod_clpv=$minv_cod_clpv";
                    $tlftip=consulta_string($sqltlf,'tlcp_tip_ticp',$oIfxA,'');



                    if($tlftip=='F'){
                        $fax=consulta_string($sqltlf,'tlcp_tlf_tlcp',$oIfxA,'');
                    }
                    else{

                        $telefono=consulta_string($sqltlf,'tlcp_tlf_tlcp',$oIfxA,'');
                    }


                    // direccion
                    $sql = "select min( dire_dir_dire ) as dire  from saedire where
							dire_cod_empr = $idempresa and
							dire_cod_clpv = $minv_cod_clpv ";
                    $direccion = acento_func(consulta_string_func($sql, 'dire', $oIfxA, ''));

                    // telefono
                    /* $sql = "select min( tlcp_tlf_tlcp ) as telf  from saetlcp where
                             tlcp_cod_empr = $idempresa and
                             tlcp_cod_clpv = $minv_cod_clpv ";
                     $telefono = acento_func(consulta_string_func($sql, 'telf', $oIfxA, ''));
                     */

                    $cabecera='<table border="0" width="100%">
        <tr><td width="100%" align="left">'. $empr_logo.'</td></tr>
        <tr><td width="100%" align="center"><strong>ORDEN DE COMPRA BIENES</strong></td></tr>
        <tr><td width="100%" align="right">R.U.C. '.$clpv_ruc_clpv.'</td></tr>
        </table>';

                    $prove.='<table border="0" width="100%">';
                    $prove.='<tr>';

                    $prove.='<td width="47%">';
                    $prove.='<table  border="1">';
                    $prove.='<tr>';
                    $prove.='<td width="32%"><strong> Lugar y Fecha :</strong></td>';
                    $prove.='<td width="68%"> Quito D.M., '.$dia.' de '.$mes.' de '.$anio.'  </td>';
                    $prove.='</tr>';
                    $prove.='</table>';
                    $prove.='</td>';

                    $prove.='<td width="6%">';
                    $prove.='</td>';

                    $prove.='<td width="47%">';
                    $prove.='<table cellpading="1" border="1">';
                    $prove.='<tr>';
                    $prove.='<td align="center"><strong>No.</strong> '.$minv_num_sec.'</td>';
                    $prove.='</tr>';
                    $prove.='</table>';
                    $prove.='</td>';

                    $prove.='</tr>';
                    $prove.='</table>';

                    /////Datos del Proveedor

                    $prove.='<table border="0" width="100%">';
                    $prove.='<tr>';

                    $prove.='<td width="47%">';
                    $prove.='<table cellpadding="1" border="1">';

                    $prove.='<tr>';
                    $prove.='<td width="32%"><strong> Proveedor :</strong></td>';
                    $prove.='<td width="68%"><p align="justify">'.$clpv_nom_clpv.' </p></td>';
                    $prove.='</tr>';


                    $prove.='<tr>';
                    $prove.='<td width="32%"><strong> Direcci&oacute;n :</strong></td>';
                    $prove.='<td width="68%"><p align="justify"> '.$direccion.'</p></td>';
                    $prove.='</tr>';

                    $prove.='<tr>';
                    $prove.='<td width="32%"><strong> Proforma No :</strong></td>';
                    $prove.='<td width="68%"> '.$proforma.'</td>';
                    $prove.='</tr>';

                    $prove.='</table>';
                    $prove.='</td>';

                    $prove.='<td width="6%">';
                    $prove.='</td>';

                    $prove.='<td width="47%">';
                    $prove.='<table cellpadding="1" border="1">';
                    $prove.='<tr>';
                    $prove.='<td align="left"><strong> No. R.U.C. o C.C. : 
                <br>&nbsp;Tel&eacute;fono :
                </strong>
                 </td>
                 
                 <td>
                 '.$clpv_ruc_clpv.'<br>&nbsp;&nbsp;'.$telefono.'
                 </td>';
                    $prove.='</tr>';

                    $prove.='<tr>';
                    $prove.='<td> <strong>No. de referencia :</strong></td>
                 <td>
                 '.$codsol.'</td>';
                    $prove.='</tr>';

                    $prove.='</table>';
                    $prove.='</td>';

                    $prove.='</tr>';
                    $prove.='</table>';



                    $cliente .= ' <table style="width: 100%; border:1px solid black; border-radius: 5px; padding: 2px; ">';
                    $cliente .= ' <tr>';
                    $cliente .= ' <b><td style="width: 13%"> PROVEEDOR: </td></b>';
                    $cliente .= ' <td style="width: 50% ">' . ($clpv_nom_clpv) . '</td>';
                    $cliente .= ' <b><td style="width: 15% "> FECHA EMISION: </td></b>';
                    $minv_fmov = str_replace("/", "", $minv_fmov);
                    $cliente .= ' <td style="width: 22% ">' . $minv_fmov . '</td>';
                    $cliente .= ' </tr>';

                    $cliente .= ' <tr>';
                    $cliente .= ' <td style="width: 13% "> <strong>DIRECCION:</strong> </td>';
                    $cliente .= ' <td style="width: 50% ">' . ($direccion) . '</td>';
                    $cliente .= ' <td style="width: 15% "><strong> TELEFONO: </strong></td>';
                    $cliente .= ' <td style="width: 22% ">' . $telefono . '</td>';
                    $cliente .= ' </tr>';

                    $cliente .= ' <tr>';
                    $cliente .= ' <td style="width: 13% "><strong> ORDEN DE COMPRA: </strong></td>';
                    $cliente .= ' <td style="width: 50% ">' . $minv_num_sec . '</td>';
                    $cliente .= '<td style="width: 15% "><strong> TRANSPORTE: </strong></td>';
                    $cliente .= ' <td style="width: 22% ">' . $minv_cm3_minv . '</td>';
                    $cliente .= ' </tr>';

                    $cliente .= ' <tr>';
                    $cliente .= ' <td style="width: 13% "><strong> OBSERVACIONES: </strong></td>';
                    $cliente .= ' <td colspan="3">' . $minv_cm1_minv . '</td>';
                    $cliente .= ' </tr>';
                    if ($minv_cm6_minv != '') {

                        $cliente .= ' <tr>';
                        $cliente .= ' <td style="width: 13% "><strong> OBSERVACIONES2: </strong></td>';
                        $cliente .= ' <td colspan="3">' . $minv_cm6_minv . '</td>';
                        $cliente .= ' </tr>';
                    }
                    $cliente .= ' </table>';

                    $cliente .= '<br>';
                    $cliente .= '<br>';

                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        /*$sqlDeta = "select  d.dmov_cod_prod, d.dmov_cod_bode, d.dmov_cod_unid,
					(d.dmov_can_dmov - d.dmov_can_entr) as cantidad,
					p.prod_nom_prod, d.dmov_cun_dmov, pr.prbo_iva_porc,
					dmov_fmov, dmov_cod_ccos,dmov_cod_dped,dmov_cod_pedi
					from saedmov d , saeprod p, saeprbo pr where
					p.prod_cod_prod = d.dmov_cod_prod and
					p.prod_cod_prod = pr.prbo_cod_prod and
					p.prod_cod_empr = pr.prbo_cod_empr and
					p.prod_cod_sucu = pr.prbo_cod_sucu and
					d.dmov_cod_bode = pr.prbo_cod_bode and
					p.prod_cod_empr = $idempresa and
					d.dmov_num_comp = $minv_serial and
					d.dmov_cod_empr = $idempresa
					order by d.dmov_cod_dmov ";*/
        $sqlDeta ="select  d.dmov_cod_dmov, d.dmov_cod_prod, d.dmov_cod_unid,  
        (d.dmov_can_dmov - d.dmov_can_entr) as cantidad, 
        d.dmov_cun_dmov, 
        dmov_fmov, dmov_cod_ccos,dmov_cod_dped,dmov_cod_pedi,d.dmov_can_dmov
        from saedmov d where
        d.dmov_num_comp = $minv_serial and
        d.dmov_cod_empr = $idempresa
        group by 1,2,3,4,5,6,7,8,9,10
        order by 1";

        $deta.='<br><br><table border="1" cellpadding="1">';
        $deta .= ' <tr>';
        $deta .= ' <td style="width: 7%; font-size:80%;" align="center"><strong>ITEM</strong></td>';
        $deta .= ' <td style="width: 12%; font-size:80%;" align="center"><strong>CANTIDAD</strong></td> ';
        $deta .= ' <td style="width: 16%; font-size:80%;" align="center"><strong>PRESENTACION</strong></td> ';
        $deta .= ' <td style="width: 10%; font-size:80%;" align="center"><strong>CODIGO</strong></td> ';
        $deta .= ' <td style="width: 35%; font-size:80%;" align="justify"><strong>DESCRIPCION</strong></td> ';
        $deta .= ' <td style="width: 10%; font-size:80%;" align="center"><strong>VALOR UNITARIO</strong></td> ';
        $deta .= ' <td style="width: 10%; font-size:80%;" align="center"><strong>VALOR TOTAL</strong></td>';
        $deta .= ' </tr>';

        $i=1;
        if ($oIfx->Query($sqlDeta)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $prod_cod = $oIfx->f('dmov_cod_prod');
                    $codped=$oIfx->f('dmov_cod_dped');
                    $codpedi=$oIfx->f('dmov_cod_pedi');

                    if(!empty($codpedi)&&!empty($codped)){
                        $sql="select dped_det_dped from saedped where dped_cod_dped=$codped and dped_cod_pedi='$codpedi'";
                        $detalle=consulta_string($sql, 'dped_det_dped', $oCnx, 0);
                    }

                    $sqltip="select prod_cod_tpro from saeprod where prod_cod_prod='$prod_cod'";
                    $tip=consulta_string($sqltip, 'prod_cod_tpro', $oCnx, 0);

                    if($tip==1){
                        if(!empty($detalle)){
                            $prod_nom_prod=trim($detalle);
                        }
                        else{

                            $sqlprod="select prod_nom_prod from saeprod where prod_cod_prod='$prod_cod'";
                            $prod_nom_prod = trim(consulta_string($sqlprod, 'prod_nom_prod', $oCnx, ''));
                        }

                    }
                    else{
                        $sqlprod="select prod_nom_prod from saeprod where prod_cod_prod='$prod_cod'";
                        $prod_nom_prod = trim(consulta_string($sqlprod, 'prod_nom_prod', $oCnx, ''));
                    }


                    //$bode_cod = $oIfx->f('dmov_cod_bode');
                    $unid_cod = $oIfx->f('dmov_cod_unid');
                    $cantidad = $oIfx->f('dmov_can_dmov');

                    $costo = $oIfx->f('dmov_cun_dmov');

                    $cta_inv = $oIfx->f('prbo_cta_inv');
                    $cta_iva = $oIfx->f('prbo_cta_ideb');
                    $iva = $oIfx->f('prbo_iva_porc');
                    $dmov_fmov = $oIfx->f('dmov_fmov');
                    $dmov_cod_ccos = $oIfx->f('dmov_cod_ccos');
                    if(empty($unid_cod)){
                        $unid_cod=0;
                    }
                    $sqlun="select unid_nom_unid from saeunid where unid_cod_unid=$unid_cod";
                    $unidad=consulta_string( $sqlun,'unid_nom_unid',$oIfxA,'');

                    $ccosn_nom_ccosn = "";
                    if (!empty($dmov_cod_ccos)) {
                        $sql = "select ccosn_nom_ccosn from saeccosn where ccosn_cod_empr = $idempresa and ccosn_cod_ccosn = '$dmov_cod_ccos'";
                        $ccosn_nom_ccosn = consulta_string_func($sql, 'ccosn_nom_ccosn', $oIfxA, '');
                    }

                    if (empty($iva)) {
                        $iva = 0;
                    }

                    // TOTAL
                    $total_fac = 0;
                    $descuento = 0;
                    $descuento_2 = 0;
                    $descuento_general = 0;
                    $dsc1 = ($costo * $cantidad * $descuento) / 100;
                    $dsc2 = ((($costo * $cantidad) - $dsc1) * $descuento_2) / 100;
                    if ($descuento_general > 0) {
                        // descto general
                        $dsc3 = ((($costo * $cantidad) - $dsc1 - $dsc2) * $descuento_general) / 100;
                        $total_fact_tmp = ((($costo * $cantidad) - ($dsc1 + $dsc2 + $dsc3)));
                        $tmp = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                    } else {
                        // sin descuento general
                        $total_fact_tmp = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
                        $tmp = $total_fact_tmp;
                    }

                    $total_fac = round($total_fact_tmp, 2);

                    // total con iva
                    if ($iva > 0) {
                        $total_con_iva = round((($total_fac * $iva) / 100), 2) + $total_fac;
                    } else {
                        $total_con_iva = $total_fac;
                    }

                    $total_fac=$cantidad*$costo;


                    $deta .= ' <tr>';
                    $deta .= ' <td style="width: 7%; font-size:80%;" align="center">' . $i . '</td>';
                    $deta .= ' <td style="width: 12%; font-size:80%;" align="right">' . $cantidad . '</td>';
                    $deta .= ' <td style="width: 16%; font-size:80%;" align="center">' . $unidad . '</td>';
                    $deta .= ' <td style="width: 10%; font-size:80%;" align="left">' .  $prod_cod . '</td>';
                    $deta .= ' <td style="width: 35%; font-size:80%;" align="justify">'.$prod_nom_prod.'</td>';
                    $deta .= ' <td style="width: 10%; font-size:80%;" align="right">' . number_format($costo, 4, '.', ',') . '</td>';
                    $deta .= ' <td style="width: 10%; font-size:80%;" align="right">' . number_format($total_fac, 2, '.', ',') . '</td>';
                    $deta .= ' </tr>';


                    $i++;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $deta .= ' </table>';


        //DATOS DE LA PROFORMA
        if(empty($mov_cod_pedi)){

            $mov_cod_pedi=0;
        }

        if(empty($minv_cod_clpv)){
            $minv_cod_clpv=0;
        }
        $sqld="select d.invpd_esta_invpd , d.invp_subt_prof,d.invp_iva_prof,d.invp_desc_prof,d.invp_total_prof,d.invpd_adjunto,d.invpd_costo_prod,d.invpd_tent_prof,d.invpd_fpago_prof,d.invpd_vofer_prof,d.invp_ofcom_prof,d.invp_ctzcom_prof,d.invp_sadc_prof,d.invp_exps_prof,d.id_inv_dprof, d.invpd_cod_clpv, d.invpd_nom_clpv,
								d.invpd_ema_clpv, d.invpd_costo_prod
								from comercial.inv_proforma_det d, comercial.inv_proforma i where
                                d.id_inv_prof=i.id_inv_prof  and i.inv_cod_pedi=$mov_cod_pedi and d.invpd_esta_invpd = 'AS' and d.invpd_cod_clpv=$minv_cod_clpv and d.invpd_tent_prof !='' and d.invpd_fpago_prof!=''";

        $forma_pago=consulta_string($sqld,'invpd_fpago_prof',$oIfxA,'');
        $tentrega=consulta_string($sqld,'invpd_tent_prof',$oIfxA,'');

        $sqlg="select pedi_lug_entr from saepedi where pedi_cod_pedi=$mov_cod_pedi and pedi_cod_empr=$idempresa";
        $lugar_entrega=consulta_string($sqlg,'pedi_lug_entr',$oIfx,'');




        $profdet='';

        $profdet.='<br><br><table border="0">';
        $profdet.='<tr>';

        $profdet.='<td width="60%">';
        $profdet.='<table cellpadding="1" border="1">';

        $profdet.='<tr>';
        $profdet.='<td colspan="2" align="center"><strong>Condiciones de Compra</strong></td>';
        $profdet.='</tr>';

        $profdet.='<tr>';
        $profdet.='<td width="40%"> Transporte a cargo de :</td>';
        $profdet.='<td width="60%"> PROVEEDOR</td>';
        $profdet.='</tr>';

        $profdet.='<tr>';
        $profdet.='<td width="40%"> Lugar de entrega :</td>';
        $profdet.='<td width="60%"> '.$lugar_entrega.'</td>';
        $profdet.='</tr>';

        $profdet.='<tr>';
        $profdet.='<td width="40%"> Forma de pago :</td>';
        $profdet.='<td width="60%"> '.$forma_pago.'</td>';
        $profdet.='</tr>';

        $profdet.='<tr>';
        $profdet.='<td width="40%"> Plazo de entrega :</td>';
        $profdet.='<td width="60%"> '.$tentrega.' DIAS LABORABLES</td>';
        $profdet.='</tr>';
        $profdet.='</table>';
        $profdet.='</td>';



        $profdet.='<td width="7%">';
        $profdet.='</td>';

        $profdet.='<td width="33%">';
        $profdet.='<table  border="1">';

        $profdet.='<tr>';

        //$iva=($minv_iva_valo*$minv_sin_iva)/100;

        //$total=$minv_sin_iva+$iva;

        // $iva=$minv_tot_minv-$minv_sin_iva;


        $iva=$minv_iva_valo;
        $minv_sin_iva=$minv_tot_minv;
        $minv_tot_minv+=$iva;



        if($minv_otr_valo>0){

            $otros_valores='<tr>
            <td>Otros Cargos</td><td align="right">' . number_format($minv_otr_valo, 2, '.', ',') . '</td>
            </tr>';
        }
        else{
            $otros_valores='';
        }

        $minv_tot_minv+=$minv_otr_valo;


        $profdet.='<td>
        <table cellpadding="1" border="0">
        <tr>
        <td>Base</td><td align="rigth">'.number_format($minv_sin_iva,2,'.',',').'</td>
        </tr>
        <tr>
        <td>SUBTotal</td><td align="right">' . number_format($minv_sin_iva, 2, '.', ',') . '</td>
        </tr>
        <tr>
        <td>Neto I.V.A.12%</td><td align="right">' . number_format($minv_sin_iva, 2, '.', ',') . '</td>
        </tr>
        <tr>
        <td>AfectoIVA</td><td align="right">' . number_format($minv_sin_iva, 2, '.', ',') . '</td>
        </tr>
        <tr>
        <td>I.V.A. 12%-14</td><td align="right">' . number_format($iva, 2, '.', ',') . '</td>
        </tr>
        '.$otros_valores.'
        <tr>
        <td>Total</td><td align="right">' . number_format( $minv_tot_minv, 2, '.', ',') . '</td>
        </tr>
        </table>
        </td>';
        $profdet.='</tr>';


        $profdet.='</table>';
        $profdet.='</td>';

        $profdet.='</tr>';
        $profdet.='</table>';

        ///CANTIDAD EN LETRAS
        $formatter = new EnLetras();

        $valtotal=$formatter->ValorEnLetras($minv_tot_minv,'d&oacute;lares');

        $cletras='';
        $cletras.='<br><br><table width="90%" align="center">';
        $cletras.='<tr>';

        $cletras.='<td width="10%">';
        $cletras.='<table cellpadding="1">';
        $cletras.='<tr>';
        $cletras.='<td align="center"><strong>Son :</strong></td>';
        $cletras.='</tr>';
        $cletras.='</table>';
        $cletras.='</td>';

        $cletras.='<td width="90%">';
        $cletras.='<table border="1" cellpadding="1">';
        $cletras.='<tr>';
        $cletras.='<td align="left"> '.$valtotal.'</td>';
        $cletras.='</tr>';
        $cletras.='</table>';
        $cletras.='</td>';

        $cletras.='</tr>';
        $cletras.='</table>';

        ///FIRMAS DE AUTORIZACION

        //GESTOR DE COMPRAS
        if(empty($mov_cod_pedi)){
            $mov_cod_pedi=0;
        }
        $sqlg="select pedi_empl_apro,pedi_are_soli,pedi_carea_pedi from saepedi where pedi_cod_pedi=$mov_cod_pedi and pedi_cod_empr=$idempresa";
        $gest=consulta_string($sqlg,'pedi_empl_apro',$oIfx,'');
        $areasoli=consulta_string($sqlg,'pedi_are_soli',$oIfx,'');
        $codcarea=consulta_string($sqlg,'pedi_carea_pedi',$oIfx,'');

        $sql="select mparea_cod_empl from saemparea where  mparea_f4aplog_mparea='SI' and mparea_cod_empr=$idempresa ";

        $coor=consulta_string($sql,'mparea_cod_empl',$oIfx,'');

        $firmalog=firma_nomb_empleado($coor);

        foreach ($firmalog as $firma) {

            $firmalog = $firma[0];

        }

        $firmagest=firma_nomb_empleado($gest);

        foreach ($firmagest as $firma) {

            $firmagest = $firma[0];

        }
        $firmas='';
        $firmas.='<br><br><table cellpadding="1" border ="1">';
        $firmas.='<tr>';
        $firmas.='<td align="center"><strong>Elaborado por :</strong></td>';
        $firmas.='<td align="center"><strong>Revisado y Autorizado por :</strong></td>';
        $firmas.='</tr>';
        $firmas.='<tr>';
        $firmas.='<td align="center">'.$firmagest.'Departamento de Compras</td>';
        $firmas.='<td align="center"> '.$firmalog.'Coordinaci&oacute;n Nacional de Log&iacute;stica</td>';
        $firmas.='</tr>';
        $firmas.='</table>';

        $area.='<br><br><table >';
        $area.='
     <tr>

     <td width="47%">
     <table cellpadding="1" border="1">
     
     <tr>
     <td colspan="2"  align="center"><strong>&Aacute;rea / Departamento solicitante (Centro de Costo)</strong></td>
     </tr>
     
     <tr>
     <td colspan="2"  align="center"><p align="center">'. $areasoli.'</p></td>
     </tr>
     </table>

     <table>
     <tr>
     <td></td>
     </tr>
     </table>

     <table cellpadding="1" border ="1">
     <tr>
     <td colspan="2" align="center"><strong>Aceptaci&oacute;n del Proveedor</strong></td>
     </tr>
     <tr>
     <td width="30%"> Fecha :</td>
     <td width="70%"> </td>
     </tr>

     <tr>
     <td width="30%"> Nombre :</td>
     <td width="70%"> </td>
     </tr>

     <tr>
     <td width="30%"> Firma y Sello :</td>
     <td width="70%"> </td>
     </tr>

     </table>

     
     </td>

     <td width="6%">
     </td>

     <td width="47%">
     <table cellpadding="1" border="1">
     <tr>
     <td colspan="2" align="center"><strong>Recepci&oacute;n de Bodega</strong></td>
     </tr>
     <tr>
     <td width="30%"> Fecha :</td>
     <td width="70%"></td>
     </tr>
     <tr>
     <td width="30%"> Ingreso No :</td>
     <td width="70%"></td>
     </tr>
     <tr>
     <td colspan="2" align="center"><br><br><br> Firma y Sello</td>
     </tr>
     <tr>
     <td colspan="2" > Observaciones :</td>
     </tr>
     </table>
     </td>


     </tr>';
        $area.='</table>';

        $nota='';
        $nota.='<br><br>
     <table cellpadding="4" border="1">
     <tr>
     <td  style="font-size:75%;" align="center"><strong>Nota :</strong></td>
     </tr>
     <tr>
     <td style="font-size:75%;" ><p  align="justify">PARA TRAMITAR EL PAGO DE LOS ARTICULOS ADQUIRIDOS POR CRUZ ROJA ECUATORIANA, ES NECESARIO QUE EL PROVEEDOR
      PRESENTE AL MOMENTO DE LA ENTREGA DE LA MERCADERIA, LA FACTURA ORIGINAL CON TODOS LOS DATOS DEL COMPRADOR 
      CORRECTOS; DICHA FACTURA DEBERA IR ACOMPA&Ntilde;ADA DE LA PRESENTE ORDEN DE COMPRA, DEBIDAMENTE LEGALIZADA.</p>
     
     </td>
     </tr>
     </table>';

        /*$totales .= ' <table style="width: 100%; font-size: 13px; margin-top: 3px; border-radius: 5px; border-collapse: collapse;"  border=1 align="right">';
		$totales .= ' <tr>';
		$totales .= ' <b> <td style="width: 10%;">SUBTOTAL:</td> </b>';
		$totales .= ' <td align="right" style="width: 10%;">' . number_format($minv_tot_minv, 2, '.', ',') . '</td>';
		$totales .= ' </tr>';
		$totales .= ' <tr>';
		$totales .= ' <b> <td style="width: 10%;">DESCUENTO:</td> </b>';
		$totales .= ' <td align="right" style="width: 10%;">' . number_format($minv_dge_valo, 2, '.', ',') . '</td>';
		$totales .= ' </tr>';
		$totales .= ' <tr>';
		$totales .= ' <b> <td style="width: 10%;">IVA:</td> </b>';
		$totales .= ' <td align="right" style="width: 10%;">' . number_format($minv_iva_valo, 2, '.', ',') . '</td>';
		$totales .= ' </tr>';
		$totales .= ' <tr>';
		$totales .= ' <b> <td style="width: 10%;">TOTAL:</td> </b>';
		$totales .= ' <td align="right" style="width: 10%;">' . number_format($total, 2, '.', ',') . '</td>';
		$totales .= ' </tr>';
		$totales .= ' </table>';*/

        //$documento .= '<page backimgw="70%" backtop="10mm" backbottom="10mm" backleft="20mm" backright="10mm">';
        //$documento .= $logo . $cliente . $deta . $totales;

        $documento .=$cabecera.$prove.$deta.$profdet.$cletras.$firmas.$area.$nota;


        // echo $documento;exit;


        //$documento .= $logo ;
        // $documento .= $legend;
        // $documento .= '</page>';

        if($id==1){
$html=<<<EOD
    $documento
EOD;

            $pdf = new TCPDF2('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->setPrintHeader(false);
            //$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            // $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetMargins(10,5, 10, true);
            //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            // set auto page breaks
            //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            // set image scale factor
            //$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            // set font
            $pdf->SetFont('helvetica', 'N', 10);
            // add a page
            $pdf->AddPage();

            $pdf->writeHTMLCell(0, 0, '', '',$html, 0, 1, 0, true, '', true);

            $nombre='ordencompra'.$minv_cod_clpv.$codcarea;
            $ruta = DIR_FACTELEC . 'Include/archivos';
            if (!file_exists($ruta)){
                mkdir($ruta);
            }
            $ruta = DIR_FACTELEC . 'Include/archivos/orden_compras';
            if (!file_exists($ruta)){
                mkdir($ruta);
            }
            $ruta = DIR_FACTELEC . 'Include/archivos/orden_compras/' . $nombre . '.pdf';
            $pdf->Output($ruta, 'F');
            $rutaPdf = $ruta;
            return $rutaPdf;

        }
        else{
            $html=<<<EOD
            $documento
EOD;
            return $html;
        }


    }

}

//////SOLICITUD DE COMPRA
function genera_pdf_doc_pago($pedi,$id, $aForm = '')
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
    //$idempresa = $aForm['empresa'];
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $aForm['sucursal'];
    $usuario_web = $_SESSION['U_ID'];

    $sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_iva_empr
    from saeempr where empr_cod_empr = $idempresa ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_path_logo');


            if ($oIfx->f('empr_conta_sn') == 'S')
                $empr_conta_sn = 'SI';
            else
                $empr_conta_sn = 'NO';

            $empr_num_resu = $oIfx->f('empr_num_resu');
            $empr_iva_empr = $oIfx->f('empr_iva_empr');
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

        $logo = '<div>
            <img src="' . $imagen . '" style="
            width:100px;
            object-fit; contain;">
            </div>';
        $x = '0px';
    }
    else{
        $logo='<span><font color="red">SIN LOGO</font></span>';
    }



    $html.='<table  border="1"  cellpadding="1" >
    <tr>
    <td  align="center">'.$logo.' </td>';



    $sqlusua = "select empl_cod_empl from comercial.usuario where usuario_id=$usuario_web";

    $cedusua = consulta_string_func($sqlusua, 'empl_cod_empl', $oCon, 0);

    $sqlres = "select empl_ape_empl,empl_nom_empl  from saeempl where empl_cod_empl='$cedusua'";

    $ape = consulta_string_func($sqlres, 'empl_ape_empl', $oIfx, '');
    $nom = consulta_string_func($sqlres, 'empl_nom_empl', $oIfx, '');
    $responsable = $nom . ' ' . $ape;


    $spedi = "select pgs_tip_pago, pgs_ope_pgs,pgs_tot_pgs, pgs_tipo_pgs,pgs_carea_pgs, pgs_ger_soli, pgs_cod_ccos,pgs_moti_pgs from saepgs where pgs_cod_pgs=$pedi";
    $cpedi = consulta_string_func($spedi, 'pgs_carea_pgs', $oIfxA, '');
    $area = consulta_string_func($spedi, 'pgs_ger_soli', $oIfxA, '');
    $tipo = consulta_string_func($spedi, 'pgs_tip_pago', $oIfxA, '');
    $motivo = consulta_string_func($spedi, 'pgs_moti_pgs', $oIfxA, '');
    $operacion = consulta_string_func($spedi, 'pgs_ope_pgs', $oIfxA, '');

    $ccos = consulta_string_func($spedi, 'pgs_cod_ccos', $oIfxA, '');
    $scos = "select ccosn_nom_ccosn from saeccosn where ccosn_cod_ccosn='$ccos'";
    $ncosn = consulta_string_func($scos, 'ccosn_nom_ccosn', $oIfxA, '');


    $total = number_format(consulta_string_func($spedi, 'pgs_tot_pgs', $oIfxA, ''),2);


    //CONSULTA DE LOS CAMPOS

    $sql = "select dpgs_det_dpgs, dpgs_fdes_dpgs, dpgs_fhas_dpgs, dpgs_hosp_dpgs,
      dpgs_ben_dpgs,dpgs_ali_dpgs,dpgs_ter_dpgs, dpgs_ext_dpgs, dpgs_vadi_dpgs,
      dpgs_vapr_dpgs, dpgs_dest_dpgs,dpgs_fval_dpgs, dpgs_nfac_dpgs, dpgs_pruc_dpgs,
      dpgs_npro_dpgs, dpgs_ncom_dpgs, dpgs_vpag_dpgs, dpgs_dcri_dpgs, dpgs_ant_dpgs,
      dpgs_pmes_dpgs, dpgs_mont_dpgs, dpgs_tip_tc, dpgs_cod_ccos
      from saedpgs where dpgs_cod_pgs=$pedi";

    $sqlpag="select nombre from comercial.cre_tipo_solicitud where id=$tipo";
    $ntipo=consulta_string_func($sqlpag,'nombre',$oCon,'');

    //FORMATOS REPORTES SOLICITUDES

    //VIATICOS

    if ($tipo == 1) {


        $shosp = "select sum(dpgs_hosp_dpgs) as shosp from saedpgs where dpgs_cod_pgs=$pedi";
        $thosp = consulta_string_func($shosp, 'shosp', $oIfxA, 0);
        $thosp=number_format($thosp,2);

        $sali = "select sum(dpgs_ali_dpgs) as sali from saedpgs where dpgs_cod_pgs=$pedi";
        $tali = consulta_string_func($sali, 'sali', $oIfxA, 0);
        $tali=number_format($tali,2);

        $ster = "select sum(dpgs_ter_dpgs) as ster from saedpgs where dpgs_cod_pgs=$pedi";
        $tter = consulta_string_func($ster, 'ster', $oIfxA, 0);
        $tter=number_format($tter,2);

        $sext = "select sum(dpgs_ext_dpgs) as sext from saedpgs where dpgs_cod_pgs=$pedi";
        $text = consulta_string_func($sext, 'sext', $oIfxA, 0);
        $text=number_format($text,2);

        $svapr = "select sum(dpgs_vapr_dpgs) as svapr from saedpgs where dpgs_cod_pgs=$pedi";
        $tvapr = consulta_string_func($svapr, 'svapr', $oIfxA, 0);
        $tvpar=number_format($tvpar,2);


        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $via .= '<table  border="1" cellpadding="1">';

                $via .= '<tr style="background-color: #e02126;">
        <td align="center" colspan="9"><font color ="#ffffff"><strong>' . $ntipo . '</strong></font></td>
        </tr>';

                do {


                    $beneficiario = $oIfx->f('dpgs_ben_dpgs');
                    $dpgs_cod_ccos = $oIfx->f('dpgs_cod_ccos');

                    $scos_det = "select ccosn_nom_ccosn from saeccosn where ccosn_cod_ccosn='$dpgs_cod_ccos'";
                    $ncosn = consulta_string_func($scos_det, 'ccosn_nom_ccosn', $oIfxA, '');

                    $sqlres = "select empl_ape_empl,empl_nom_empl  from saeempl where empl_cod_empl='$beneficiario'";

                    $ape = consulta_string_func($sqlres, 'empl_ape_empl', $oIfxA, '');
                    $nom = consulta_string_func($sqlres, 'empl_nom_empl', $oIfxA, '');
                    $nombre = $nom . ' ' . $ape;

                    if(empty($nom)){

                        $sqlprove="select clpv_nom_clpv from saeclpv where clpv_ruc_clpv='$beneficiario'";
                        $nombre = consulta_string_func($sqlprove, 'clpv_nom_clpv', $oIfxA, '');

                        if(empty($nombre)){
                            $nombre='BENEFICIARIO NO ENCONTRADO';
                        }

                    }



                    $fdes = $oIfx->f('dpgs_fdes_dpgs');
                    $afdes=explode('-',$fdes);
                    $fdes=$afdes[2].'-'.$afdes[1].'-'.$afdes[0];
                    $fhasta = $oIfx->f('dpgs_fhas_dpgs');
                    $afhasta=explode('-',$fhasta);
                    $fhasta=$afhasta[2].'-'.$afhasta[1].'-'.$afhasta[0];

                    $dias = dias_pasados($fdes, $fhasta);

                    $hosp = $oIfx->f('dpgs_hosp_dpgs');
                    $hosp=number_format($hosp,2);
                    $ali = $oIfx->f('dpgs_ali_dpgs');
                    $ali=number_format($ali,2);
                    $ter = $oIfx->f('dpgs_ter_dpgs');
                    $ter=number_format($ter,2);
                    $ext = $oIfx->f('dpgs_ext_dpgs');
                    $ext=number_format($ext,2);
                    $vapro = $oIfx->f('dpgs_vapr_dpgs');
                    $vapro=number_format($vapro,2);
                    $vadi = $oIfx->f('dpgs_vadi_dpgs');
                    $vadi=number_format($vadi,2);
                    $det = $oIfx->f('dpgs_det_dpgs');

                    $des = $oIfx->f('dpgs_dest_dpgs');

                    $sql = "select provz_cod_zona from saeprovz where provz_nom_provc='$des'";
                    $zona = consulta_string_func($sql, 'provz_cod_zona', $oIfxA, '');



                    $via .= '<tr>
        <td  align="left" colspan="5"><font color ="red"><strong>Beneficiario:</strong></font>&nbsp;&nbsp;&nbsp;' . $nombre . '</td>
        <td align="left" colspan="4" ><font color ="red"><strong>No. C&eacute;dula de Ciudadan&iacute;a del Empleado:</strong></font>&nbsp;&nbsp;&nbsp;' . $beneficiario . '</td>
        </tr>';
                    $via .= '<tr>
        <td  align="rigth" colspan="3"><font color ="red"><strong>Destino:&nbsp;&nbsp;</strong></font></td>
        <td  align="center" colspan="2">' . $des . '</td>
        <td  align="left" colspan="4"><font color ="red"><strong>Motivo:</strong></font>&nbsp;&nbsp;&nbsp;' . $motivo . '</td>
        </tr>';


                    $via .= '<tr>
        <td  colspan="3" align="rigth"><font color ="red"><strong>Centro de Costos:&nbsp;&nbsp;</strong></font></td>
        <td  colspan="6" align="center">' . $ncosn . '</td>
        </tr>';
                    $via .= '<tr>
        <td rowspan="2" align="center"><font color ="red"><strong>Zona</strong></font></td>
        <td  colspan="2" align="center"><font color ="red"><strong>Fecha</strong></font></td>
        <td rowspan="2" align="center"><font color ="red"><strong>D&iacute;as</strong></font></td>
        <td colspan="4"></td>
        <td rowspan="2" align="center"><font color ="red"><strong>Valor<br>Aprobado</strong></font></td>
        </tr>';

                    $via .= '<tr>
        <td align="center"><font color ="red"><strong>Desde</strong></font></td>
        <td align="center"><font color ="red"><strong>Hasta</strong></font></td>
        <td align="center"><font color ="red"><strong>Hospedaje</strong></font></td>
        <td align="center"><font color ="red"><strong>Alimentaci&oacute;n</strong></font></td>
        <td align="center"><font color ="red"><strong>Terrestre</strong></font></td>
        <td align="center"><font color ="red"><strong>Exterior</strong></font></td>
        </tr>';
                    $via .= '<tr>
        <td  align="center">' . $zona . '</td>
        <td  align="center">' . $fdes . '</td>
        <td  align="center">' . $fhasta . '</td>
        <td  align="center">' . $dias . '</td>
        <td  align="center">' . $hosp . '</td>
        <td  align="center">' . $ali . '</td>
        <td  align="center">' . $ter . '</td>
        <td  align="center">' . $ext . '</td>
        <td  align="center">' . ($vapro - $vadi) . '</td>
        </tr>';
                    $via .= '<tr>
        <td  align="center" colspan="2"><strong>Valores Adicionales</strong></td>
        <td  align="center" colspan="6">' . $det . '</td>
        <td  align="center">' . $vadi . '</td>
        </tr>';

                } while ($oIfx->SiguienteRegistro());
                $via .= '<tr>
      <td  align="center"></td>
      <td  align="center"></td>
      <td  align="center"></td>
      <td  align="center"></td>
      <td  align="center"></td>
      <td  align="center"></td>
      <td  align="center"></td>
      <td  align="center"></td>
      <td  align="center"></td>
      </tr>';
                $via .= '<tr>
      <td  align="center"><strong>Total</strong></td>
      <td  align="center"></td>
      <td  align="center"></td>
      <td  align="center"></td>
      <td  align="center">' . $thosp . '</td>
      <td  align="center">' . $tali . '</td>
      <td  align="center">' . $tter . '</td>
      <td  align="center">' . $text . '</td>
      <td  align="center">' . ($tvapr - $vadi) . '</td>
        </tr>';

                $via .= '<tr>
      <td  align="center" colspan="7"></td>
      <td  align="center"><strong>Total</strong></td>
      <td align="center">' . $total . '</td>
      </tr>';
                $via .= '</table>';


            }
        }

    }//CIERRE IF VIATICOS
    else {
        $via = '';
    }

    //FONDOS

    if ($tipo == 2 ) {

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $fon .= '<table  border="1" cellpadding="1">';

                $fon .= '<tr style="background-color: #e02126;">
                <td align="center" colspan="9"><font color ="#ffffff"><strong>' . $ntipo . '</strong></font></td>
                </tr>';
                do {

                    $beneficiario = $oIfx->f('dpgs_ben_dpgs');
                    $valor = $oIfx->f('dpgs_fval_dpgs');
                    $valor=number_format($valor,2);

                    $sqlres = "select empl_ape_empl,empl_nom_empl  from saeempl where empl_cod_empl='$beneficiario'";

                    $ape = consulta_string_func($sqlres, 'empl_ape_empl', $oIfxA, '');
                    $nom = consulta_string_func($sqlres, 'empl_nom_empl', $oIfxA, '');
                    $nombre = $nom . ' ' . $ape;

                    if(empty($nom)){

                        $sqlprove="select clpv_nom_clpv from saeclpv where clpv_ruc_clpv='$beneficiario'";
                        $nombre = consulta_string_func($sqlprove, 'clpv_nom_clpv', $oIfxA, '');

                        if(empty($nombre)){
                            $nombre='BENEFICIARIO NO ENCONTRADO';
                        }

                    }



                    $fon .= '<tr>
        <td  align="left" colspan="5"><font color ="red"><strong>Beneficiario:</strong></font>&nbsp;&nbsp;&nbsp;' . $nombre . '</td>
        <td align="left" colspan="4" ><font color ="red"><strong>No. C&eacute;dula de Ciudadan&iacute;a del Empleado:</strong></font>&nbsp;&nbsp;&nbsp;' . $beneficiario . '</td>
        </tr>';
                    $fon .= '<tr>
        <td  align="rigth" colspan="3"><font color ="red"><strong>Valor:&nbsp;&nbsp;</strong></font></td>
        <td  align="center" colspan="2">' . $valor . '</td>
        <td  align="left" colspan="4"><font color ="red"><strong>Motivo:</strong></font>&nbsp;&nbsp;&nbsp;' . $motivo . '</td>
        </tr>';


                    $fon .= '<tr>
        <td  colspan="3" align="rigth"><font color ="red"><strong>Centro de Costos:&nbsp;&nbsp;</strong></font></td>
        <td  colspan="6" align="center">' . $ncosn . '</td>
        </tr>';

                } while ($oIfx->SiguienteRegistro());
                $fon .= '</table>';
            }
        }

    }//CIERRE IF FONDOS
    else {
        $fon = '';
    }


    //REEMBOLSOS

    if ($tipo == 3) {

        $svpag = "select sum(dpgs_vpag_dpgs) as svpag from saedpgs where dpgs_cod_pgs=$pedi";

        $tvpag = consulta_string_func($svpag, 'svpag', $oIfxA, 0);
        $tvpag=number_format($tvpag,2);

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $nfact = $oIfx->f('dpgs_nfac_dpgs');
                $beneficiario = $oIfx->f('dpgs_ben_dpgs');
                $valor = $oIfx->f('dpgs_fval_dpgs');

                $sqlres = "select empl_ape_empl,empl_nom_empl  from saeempl where empl_cod_empl='$beneficiario'";

                $ape = consulta_string_func($sqlres, 'empl_ape_empl', $oIfxA, '');
                $nom = consulta_string_func($sqlres, 'empl_nom_empl', $oIfxA, '');
                $nombre = $nom . ' ' . $ape;
                if(empty($nom)){

                    $sqlprove="select clpv_nom_clpv from saeclpv where clpv_ruc_clpv='$beneficiario'";
                    $nombre = consulta_string_func($sqlprove, 'clpv_nom_clpv', $oIfxA, '');

                    if(empty($nombre)){
                        $nombre='BENEFICIARIO NO ENCONTRADO';
                    }

                }

                $rem .= '<table  border="1" cellpadding="1">';
                $rem .= '<tr style="background-color: #e02126;">
        <td align="center" colspan="4"><font color ="#ffffff"><strong>' . $ntipo . '</strong></font></td>
        </tr>';

                $rem .= '<tr>
        <td  align="left" colspan="2"><font color ="red"><strong>Beneficiario:</strong></font>&nbsp;&nbsp;&nbsp;' . $nombre . '</td>
        <td align="left" colspan="2" ><font color ="red"><strong>No. C&eacute;dula de Ciudadan&iacute;a del Empleado:</strong></font>&nbsp;&nbsp;&nbsp;' . $beneficiario . '</td>
        </tr>';
                $rem .= '<tr>
        <td  align="center" ><font color ="red"><strong>Seleccione la operaci&oacute;n</strong></font></td>
        <td  align="center">' . $operacion . '</td>
        <td  align="left" colspan="2"><font color ="red"><strong>Motivo:</strong></font>&nbsp;&nbsp;&nbsp;' . $motivo . '</td>
        </tr>';

                $rem .= '<tr>
        <td  align="rigth"><font color ="red"><strong>Centro de Costos:&nbsp;&nbsp;</strong></font></td>
        <td  align="center">' . $ncosn . '</td>
        <td  align="rigth"><font color ="red"><strong>Factura:&nbsp;&nbsp;</strong></font></td>
        <td  align="rigth">' . $nfact . '</td>
        </tr>';

                $rem .= '<tr>
        <td align="center" ><font color ="red"><strong>RUC</strong></font></td>
        <td align="center" ><font color ="red"><strong>Nombre<br>Proveedor</strong></font></td>
        <td align="center" ><font color ="red"><strong>No. Comprobante</strong></font></td>
        <td align="center" ><font color ="red"><strong>Valor Pagado</strong></font></td>      
        </tr>';

                do {

                    $ruc = $oIfx->f('dpgs_pruc_dpgs');
                    $npro = $oIfx->f('dpgs_npro_dpgs');
                    $ncom = $oIfx->f('dpgs_ncom_dpgs');
                    $vpag = $oIfx->f('dpgs_vpag_dpgs');
                    $vpag=number_format($vpag,2);




                    $rem .= '<tr>
            <td align="center" >' . $ruc . '</td>
            <td align="center" >' . $npro . '</td>
            <td align="center" >' . $ncom . '</td>
            <td align="center" >' . $vpag . '</td>      
            </tr>';

                } while ($oIfx->SiguienteRegistro());
                $rem .= '<tr>
          <td align="center" ><strong>Total</strong></td>
          <td align="center" ></td>
          <td align="center" ></td>
          <td align="center" >' . $tvpag . '</td>      
          </tr>';
                $rem .= '</table>';
            }
        }
    }//CIERRE IF REEMBOLSOS
    else {

        $rem = '';

    }

    //FACTURAS

    if ($tipo == 4) {

        $svpag = "select sum(dpgs_vpag_dpgs) as svpag from saedpgs where dpgs_cod_pgs=$pedi";
        $tvpag = consulta_string_func($svpag, 'svpag', $oIfxA, 0);

        $sql_cod_empl = "select pgs_cod_empl from saepgs where pgs_cod_pgs=$pedi";
        $pgs_cod_empl = consulta_string_func($sql_cod_empl, 'pgs_cod_empl', $oIfxA, '');

        $sql_empl = "select empl_ape_nomb from saeempl where empl_cod_empl='$pgs_cod_empl'";
        $empl_ape_nomb = consulta_string_func($sql_empl, 'empl_ape_nomb', $oIfxA, '');

        $tvpag=number_format($tvpag,2);
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $nfact = $oIfx->f('dpgs_nfac_dpgs');
                $descripcion = $oIfx->f('dpgs_dcri_dpgs');

                $pfac .= '<table  border="1" cellpadding="1">';
                $pfac .= '<tr style="background-color: #e02126;">
        <td align="center" colspan="4"><font color ="#ffffff"><strong>' . $ntipo . '</strong></font></td>
        </tr>';

                $pfac .= '<tr>
        <td  align="left" colspan="2"><font color ="red"><strong>Responsable:</strong></font>&nbsp;&nbsp;&nbsp;' . $empl_ape_nomb . '</td>
        <td align="left" colspan="2" ><font color ="red"><strong>No. C&eacute;dula de Ciudadan&iacute;a del Empleado:</strong></font>&nbsp;&nbsp;&nbsp;' . $pgs_cod_empl . '</td>
        </tr>';

                $pfac .= '<tr>
        <td  width="15%" align="rigth"><font color ="red"><strong>Centro de Costos:&nbsp;&nbsp;</strong></font></td>
        <td  width="20%" align="center">' . $ncosn . '</td>
        <td  width="15%" align="left"><font color ="red"><strong>Descripci&oacute;n</strong></font></td>
        <td  width="50%" align="left">' . $descripcion . '</td>
        </tr>';

                $pfac .= '<tr>
        <td align="center" ><font color ="red"><strong>RUC</strong></font></td>
        <td align="center" ><font color ="red"><strong>Nombre<br>Proveedor</strong></font></td>
        <td align="center" ><font color ="red"><strong>No. Comprobante</strong></font></td>
        <td align="center" ><font color ="red"><strong>Valor Pagado</strong></font></td>      
        </tr>';

                do {

                    $ruc = $oIfx->f('dpgs_pruc_dpgs');
                    $npro = $oIfx->f('dpgs_npro_dpgs');
                    $ncom = $oIfx->f('dpgs_ncom_dpgs');
                    $vpag = $oIfx->f('dpgs_vpag_dpgs');
                    $vpag=number_format($vpag,2);

                    $beneficiario = $oIfx->f('dpgs_ben_dpgs');
                    $valor = $oIfx->f('dpgs_fval_dpgs');
                    $valor=number_format($valor,2);

                    $sqlres = "select empl_ape_empl,empl_nom_empl  from saeempl where empl_cod_empl='$beneficiario'";

                    $ape = consulta_string_func($sqlres, 'empl_ape_empl', $oIfxA, '');
                    $nom = consulta_string_func($sqlres, 'empl_nom_empl', $oIfxA, '');
                    $nombre = $nom . ' ' . $ape;

                    if(empty($nom)){

                        $sqlprove="select clpv_nom_clpv from saeclpv where clpv_ruc_clpv='$beneficiario'";
                        $nombre = consulta_string_func($sqlprove, 'clpv_nom_clpv', $oIfxA, '');

                        if(empty($nombre)){
                            $nombre='BENEFICIARIO NO ENCONTRADO';
                        }

                    }


                    $pfac .= '<tr>
            <td align="center" >' . $ruc . '</td>
            <td align="center" >' . $npro . '</td>
            <td align="center" >' . $ncom . '</td>
            <td align="center" >' . $vpag . '</td>      
            </tr>';

                } while ($oIfx->SiguienteRegistro());
                $pfac .= '<tr>
          <td align="center" ><strong>Total</strong></td>
          <td align="center" ></td>
          <td align="center" ></td>
          <td align="center" >' . $tvpag . '</td>      
          </tr>';
                $pfac .= '</table>';
            }
        }
    }//CIERRE PAGO FACTURAS
    else {

        $pfac = '';

    }

    //NOMINA

    if ($tipo == 5) {
        $svpag = "select sum(dpgs_mont_dpgs) as monto from saedpgs where dpgs_cod_pgs=$pedi";
        $tmonto = consulta_string_func($svpag, 'monto', $oIfxA, 0);
        $tmonto=number_format($tmonto,2);

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $ant = $oIfx->f('dpgs_ant_dpgs');


                $descripcion = $oIfx->f('dpgs_dcri_dpgs');

                $pnom .= '<table  border="1" cellpadding="1">';
                $pnom .= '<tr style="background-color: #e02126;">
        <td align="center" colspan="4"><font color ="#ffffff"><strong>' . $ntipo . '</strong></font></td>
        </tr>';

                $pnom .= '<tr>
        <td   width="50%" align="left" colspan="2"><font color ="red"><strong>Responsable:</strong></font>&nbsp;&nbsp;&nbsp;' . $responsable . '</td>
        <td  width="50%" align="left"><font color ="red"><strong>Descripci&oacute;n:&nbsp;&nbsp;&nbsp;</strong></font>' . $descripcion . '</td>
        </tr>';

                $pnom .= '<tr>
        
        <td  width="70%" align="left"></td>
        <td  width="30%" align="left"><font color ="red"><strong>&nbsp;&nbsp;Anticipo:&nbsp;&nbsp;</strong></font>' . $ant . '</td>

        </tr>';


                do {

                    $beneficiario = $oIfx->f('dpgs_ben_dpgs');
                    $sqlres = "select empl_ape_empl,empl_nom_empl  from saeempl where empl_cod_empl='$beneficiario'";
                    $ape = consulta_string_func($sqlres, 'empl_ape_empl', $oIfxA, '');
                    $nom = consulta_string_func($sqlres, 'empl_nom_empl', $oIfxA, '');
                    $nombre = $nom . ' ' . $ape;
                    if(empty($nom)){

                        $sqlprove="select clpv_nom_clpv from saeclpv where clpv_ruc_clpv='$beneficiario'";
                        $nombre = consulta_string_func($sqlprove, 'clpv_nom_clpv', $oIfxA, '');

                        if(empty($nombre)){
                            $nombre='BENEFICIARIO NO ENCONTRADO';
                        }

                    }


                    $mes = $oIfx->f('dpgs_pmes_dpgs');
                    $monto = $oIfx->f('dpgs_mont_dpgs');
                    $monto=number_format($monto,2);


                    if ($ant == 'SI') {


                        $pnom .= '<tr>
                <td  align="left" width="60%"><font color ="red"><strong>Beneficiario:</strong></font>&nbsp;&nbsp;&nbsp;' . $nombre . '</td>
                    <td align="left" width="40%"><font color ="red"><strong>No. C&eacute;dula de Ciudadan&iacute;a del Empleado:</strong></font>&nbsp;&nbsp;&nbsp;' . $beneficiario . '</td>
                    </tr>';

                        $pnom .= '<tr>
                <td width="30%" align="center"><font color ="red"><strong>Plazo(meses)</strong></font></td>
                <td width="20%" align="center" >' . $mes . '</td>
                <td width="30%" align="center"><font color ="red"><strong>Monto</strong></font></td>
                <td width="20%" align="center" >' . $monto . '</td>
                </tr>';

                    } else {
                        $pnom .= '<tr>
                <td  align="center" width="50%"><font color ="red"><strong>Valor:</strong></font></td>
                    <td align="center" width="50%">' . $monto . '</td>
                    </tr>';
                    }


                } while ($oIfx->SiguienteRegistro());

                if ($ant == 'SI') {
                    $pnom .= '<tr>
        <td align="center" ><strong>Total</strong></td>
        <td></td>
        <td></td>
        <td align="center" >' . $total . '</td>      
        </tr>';
                } else {
                    $pnom .= '<tr>
        <td align="center" ><strong>Total</strong></td>
        <td align="center" >' . $total . '</td>      
        </tr>';

                }
                $pnom .= '</table>';
            }
        }


    }//CIERRE PAGO NOMINA
    else {

        $pnom = '';

    }

    //ANTICIPO PROVEEDORES -ANTICIPO JUNTAS-NOTAS DE CREDITO

    if ($tipo == 7 ||$tipo== 9||$tipo== 11) {

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $antpro .= '<table  border="1" cellpadding="1">';

                $antpro .= '<tr style="background-color: #e02126;">
                <td align="center" colspan="9"><font color ="#ffffff"><strong>' . $ntipo . '</strong></font></td>
                </tr>';
                do {

                    $beneficiario = $oIfx->f('dpgs_pruc_dpgs');
                    $valor = $oIfx->f('dpgs_vpag_dpgs');
                    $valor=number_format($valor,2);

                    $sqlprove="select clpv_nom_clpv from saeclpv where clpv_ruc_clpv='$beneficiario'";


                    $nombre = consulta_string_func($sqlprove, 'clpv_nom_clpv', $oIfxA, '');



                    $antpro .= '<tr>
        <td  align="left" colspan="5"><font color ="red"><strong>Beneficiario:</strong></font>&nbsp;&nbsp;&nbsp;' . $nombre . '</td>
        <td align="left" colspan="4" ><font color ="red"><strong>No. C&eacute;dula de Ciudadan&iacute;a del Empleado:</strong></font>&nbsp;&nbsp;&nbsp;' . $beneficiario . '</td>
        </tr>';
                    $antpro .= '<tr>
        <td  align="rigth" colspan="3"><font color ="red"><strong>Valor:&nbsp;&nbsp;</strong></font></td>
        <td  align="center" colspan="2">' . $valor . '</td>
        <td  align="left" colspan="4"><font color ="red"><strong>Motivo:</strong></font>&nbsp;&nbsp;&nbsp;' . $motivo . '</td>
        </tr>';


                    $antpro .= '<tr>
        <td  colspan="3" align="rigth"><font color ="red"><strong>Centro de Costos:&nbsp;&nbsp;</strong></font></td>
        <td  colspan="6" align="center">' . $ncosn . '</td>
        </tr>';

                } while ($oIfx->SiguienteRegistro());
                $antpro .= '</table>';
            }
        }

    }//CIERRE IF FONDOS
    else {
        $antpro = '';
    }

    //OTROS -OTROS TC

    if ($tipo == 8 || $tipo == 10) {

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $otros .= '<table  border="1" cellpadding="1">';

                $otros .= '<tr style="background-color: #e02126;">
                <td align="center" colspan="9"><font color ="#ffffff"><strong>' . $ntipo . '</strong></font></td>
                </tr>';
                do {

                    $valor = $oIfx->f('dpgs_vpag_dpgs');
                    $valor=number_format($valor,2);


                    $otros .= '<tr>
        <td  align="rigth" colspan="3"><font color ="red"><strong>Valor:&nbsp;&nbsp;</strong></font></td>
        <td  align="center" colspan="2">' . $valor . '</td>
        <td  align="left" colspan="4"><font color ="red"><strong>Motivo:</strong></font>&nbsp;&nbsp;&nbsp;' . $motivo . '</td>
        </tr>';


                    $otros .= '<tr>
        <td  colspan="3" align="rigth"><font color ="red"><strong>Centro de Costos:&nbsp;&nbsp;</strong></font></td>
        <td  colspan="6" align="center">' . $ncosn . '</td>
        </tr>';
                    $tiptc=intval($oIfx->f('dpgs_tip_tc'));
                    if($tiptc>0){
                        if($tiptc==1){
                            $nombtc='PASAJE AEREO';
                        }
                        elseif($tiptc==2){
                            $nombtc='OTROS TC';

                        }

                        $otros .= '<tr>
                    <td  colspan="3" align="rigth"><font color ="red"><strong>Tipo TC:&nbsp;&nbsp;</strong></font></td>
                    <td  colspan="6" align="center">' . $nombtc . '</td>
                    </tr>';

                    }

                } while ($oIfx->SiguienteRegistro());
                $otros .= '</table>';
            }
        }

    }//CIERRE IF FONDOS
    else {
        $otros = '';
    }








    if (!empty($pedi)) {

        //SOLICITANTE
        $sqlsol = "select pgs_cod_empl,pgs_fec_pgs from saepgs  where pgs_cod_pgs=$pedi";
        $csol = consulta_string_func($sqlsol, 'pgs_cod_empl', $oIfxA, '');


        $array_firma = firma_nomb_empleado($csol);

        foreach ($array_firma as $firma) {

            $logo1 = $firma[0];
            $soli = $firma[1];

        }

        //AUTORIZADOR GERENCIAL

        $sqlger = "select pgs_ager_pgs,pgs_fec_ger from saepgs  where pgs_cod_pgs=$pedi";
        $cger = consulta_string_func($sqlger, 'pgs_ager_pgs', $oIfxA, '');
        $fger = consulta_string_func($sqlger, 'pgs_fec_ger', $oIfxA, '');
        $fger= date('d-m-Y',strtotime($fger));
        $array_firma = firma_nomb_empleado($cger);
        foreach ($array_firma as $firma) {
            $logo2 = $firma[0];
            $eval = $firma[1];
        }

        // Post Aprobacion
        $sqlPostGer = "select pgs_ager_post,pgs_fec_postger from saepgs  where pgs_cod_pgs=$pedi";
        $cgerpost = consulta_string_func($sqlPostGer, 'pgs_ager_post', $oIfxA, '');
        $fgerpost = consulta_string_func($sqlPostGer, 'pgs_fec_postger', $oIfxA, '');
        $fgerpost = date('d-m-Y',strtotime($fgerpost));
        $array_firma = firma_nomb_empleado($cgerpost);
        foreach ($array_firma as $firma) {

            $logo2post = $firma[0];
            $evalpost = $firma[1];

        }

        //AUTORIZADOR FINANCIERO

        $sqlfin = "select pgs_afin_pgs,pgs_fec_fin from saepgs  where pgs_cod_pgs=$pedi";
        $cfin = consulta_string_func($sqlfin, 'pgs_afin_pgs', $oIfxA, '');
        $ffin = consulta_string_func($sqlfin, 'pgs_fec_fin', $oIfxA, '');
        $ffin= date('d-m-Y',strtotime($ffin));
        $array_firma = firma_nomb_empleado($cfin);
        foreach ($array_firma as $firma) {
            $logo3 = $firma[0];
            $adj = $firma[1];
        }


        // Post Aprobacion
        $sqlPostfin = "select pgs_afin_post,pgs_fec_postfin from saepgs  where pgs_cod_pgs=$pedi";
        $cfinpost = consulta_string_func($sqlPostfin, 'pgs_afin_post', $oIfxA, '');
        $ffinpost = consulta_string_func($sqlPostfin, 'pgs_fec_postfin', $oIfxA, '');
        $ffinpost= date('d-m-Y',strtotime($ffinpost));
        $array_firma = firma_nomb_empleado($cfinpost);
        foreach ($array_firma as $firma) {
            $logo3post = $firma[0];
            $adjpost = $firma[1];
        }


        //AUTORIZADOR GAF

        $sqlgaf = "select pgs_agaf_pgs,pgs_fec_gaf from saepgs  where pgs_cod_pgs=$pedi";
        $cgaf = consulta_string_func($sqlgaf, 'pgs_agaf_pgs', $oIfxA, '');
        $fgaf = consulta_string_func($sqlgaf, 'pgs_fec_gaf', $oIfxA, '');
        $fgaf= date('d-m-Y',strtotime($fgaf));
        $array_firma = firma_nomb_empleado($cgaf);
        foreach ($array_firma as $firma) {
            $logo4 = $firma[0];
            $gaf = $firma[1];
        }


        // Post Aprobacion
        $sqlPostgaf = "select pgs_agaf_post,pgs_fec_postgaf from saepgs  where pgs_cod_pgs=$pedi";
        $cgafpost = consulta_string_func($sqlPostgaf, 'pgs_agaf_post', $oIfxA, '');
        $fgafpost = consulta_string_func($sqlPostgaf, 'pgs_fec_postgaf', $oIfxA, '');
        $fgafpost= date('d-m-Y',strtotime($fgafpost));
        $array_firma = firma_nomb_empleado($cgafpost);
        foreach ($array_firma as $firma) {
            $logo4post = $firma[0];
            $gafpost = $firma[1];
        }

        //VALIDACION DE ESTADOS FIRMAS

        $sql="select pgs_est_pgs, pgs_est_post, pgs_est_soli, pgs_est_post_soli from saepgs where pgs_cod_pgs=$pedi";
        $est=consulta_string_func($sql, 'pgs_est_pgs', $oIfxA,0);
        $estpost=consulta_string_func($sql, 'pgs_est_post', $oIfxA,0);
        $pgs_est_soli=consulta_string_func($sql, 'pgs_est_soli', $oIfxA,0);
        $pgs_est_post_soli=consulta_string_func($sql, 'pgs_est_post_soli', $oIfxA,0);

        if($est==0){
            //FINANCIERA
            $logo3='';
            $adj='';
            $ffin='';
            //GERENCIA
            $logo2='';
            $eval='';
            $fger='';
            //GAF
            $logo4='';
            $gaf='';
            $fgaf='';

        }
        elseif($est==1){
            //GERENCIA
            $logo2='';
            $eval='';
            $fger='';
            //GAF
            $logo4='';
            $gaf='';
            $fgaf='';

        }
        elseif($est==2){
            //GAF
            $logo4='';
            $gaf='';
            $fgaf='';

        }

        // Validacion de firmas post aprobacion
        if($estpost==0){
            //FINANCIERA
            $logo3post='';
            $adjpost='';
            $ffinpost='';
            //GERENCIA
            $logo2post='';
            $evalpost='';
            $fgerpost='';
            //GAF
            $logo4post='';
            $gafpost='';
            $fgafpost='';

        }
        elseif($estpost==1){
            //GERENCIA
            $logo2post='';
            $evalpost='';
            $fgerpost='';
            //GAF
            $logo4post='';
            $gafpost='';
            $fgafpost='';

        }
        elseif($estpost==2){
            //GAF
            $logo4post='';
            $gafpost='';
            $fgafpost='';

        }


        //FECHA DE AUTORIZACION
        $fsol = consulta_string_func($sqlsol, 'pgs_fec_pgs', $oIfxA, '');

        $fsol = date("d-m-Y", strtotime($fsol));

        $html .= '<td style="font-size:80%;"align="center"><br><br><br><b>SOLICITUD DE PAGO Y / O DESPACHO</b><br> No. ' . $cpedi . '</td>   
    </tr> 
    <tr>
    <td style="font-size:80%;"align="left"><b>&nbsp;&nbsp;FECHA DE SOLICITUD: </b>' . $fsol . '</td>
    <td style="font-size:80%;"align="center"><b>&nbsp;&nbsp;AREA O PROYECTO SOLICITANTE:&nbsp;&nbsp;</b>' . $area . '</td>
    </tr>
    <tr>
    <td style="font-size:80%;"align="rigth"></td>
    </tr>
    </table>';

        //VARIABLES DE LAS TABLAS POR TIPO DE PAGO
        $html .= '' . $via . ' ' . $fon . ' ' . $rem . '' . $pfac . ' ' . $pnom . ' '.$antpro.' '.$otros.'';

        $html .= '
            <table  border="1"  cellpadding="1" >

            <tr>
                <td style="font-size:80%;" align="left" style="background-color: #e02126;"><font color ="#ffffff">Elaborado por:</font></td>
                <td style="font-size:80%;" align="left" style="background-color: #e02126;"><font color ="#ffffff">Presupuestado por:</font></td>
                <td style="font-size:80%;" align="left" style="background-color: #e02126;"><font color ="#ffffff">Aprobado por:</font></td>
                <td style="font-size:80%;" align="left" style="background-color: #e02126;"><font color ="#ffffff">Autorizado Aprobada por:</font></td>
            </tr>';

        $html .= '
            <tr>
                <td style="font-size:80%;" align="center" ><br><br><br><strong>' . $logo1 . '<br>____________________<br>' . $soli . '</strong><br>Fecha: '.$fsol.'<br><br></td>
                <td style="font-size:80%;" align="center" ><br><br><br><strong>' . $logo3 . '<br>____________________<br>' . $adj . '</strong><br>Fecha: '.$ffin.'<br><br></td>
                <td style="font-size:80%;" align="center" ><br><br><br><strong>' . $logo2 . '<br>____________________<br>' . $eval . '</strong><br>Fecha: '.$fger.'<br><br></td>
                <td style="font-size:80%;" align="center" ><br><br><br><strong>' . $logo4 . '<br>____________________<br>' . $gaf . '</strong><br>Fecha: '.$fgaf.'<br><br></td>
            </tr>

            <tr>
                <td style="font-size:80%;" align="center"><font color ="red"><strong>Solicitado Coordinador </strong></font></td>
                <td style="font-size:80%;" align="center"><font color ="red"><strong>Aprobaci&oacute;n Presupuestaria</strong></font></td>
                <td style="font-size:80%;" align="center"><font color ="red"><strong>Aprobaci&oacute;n Gerencia</strong></font></td>
                <td style="font-size:80%;" align="center"><font color ="red"><strong>Autorizaci&oacute;n GAF</strong></font></td>
            </tr>';

        // Verificamos que solamente cuando la post aprobacion de inicie, aparesca el otro cuadro de firmas
        if($pgs_est_post_soli > 0){
            $html .= '
                        <tr>
                            <td style="font-size:80%;" align="center" ><br><br><br><strong>' . $logo1 . '<br>____________________<br>' . $soli . '</strong><br>Fecha: '.$fsol.'<br><br></td>
                            <td style="font-size:80%;" align="center" ><br><br><br><strong>' . $logo3post . '<br>____________________<br>' . $adjpost . '</strong><br>Fecha: '.$ffinpost.'<br><br></td>
                            <td style="font-size:80%;" align="center" ><br><br><br><strong>' . $logo2post . '<br>____________________<br>' . $evalpost . '</strong><br>Fecha: '.$fgerpost.'<br><br></td>
                            <td style="font-size:80%;" align="center" ><br><br><br><strong>' . $logo4post . '<br>____________________<br>' . $gafpost . '</strong><br>Fecha: '.$fgafpost.'<br><br></td>
                        </tr>

                        <tr>
                            <td style="font-size:80%;" align="center"><font color ="red"><strong>Solicitado Coordinador </strong></font></td>
                            <td style="font-size:80%;" align="center"><font color ="red"><strong>Aprobaci&oacute;n Post Presupuestaria</strong></font></td>
                            <td style="font-size:80%;" align="center"><font color ="red"><strong>Aprobaci&oacute;n Post Gerencia</strong></font></td>
                            <td style="font-size:80%;" align="center"><font color ="red"><strong>Autorizaci&oacute;n Post GAF</strong></font></td>
                        </tr>';
        }


        $html .= '</table>';



        if($id==1){
            return $html;

        }
        else{



            $pdf = new TCPDF2('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->setPrintHeader(false);
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetMargins(10,5, 10, true);
            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            // set font
            $pdf->SetFont('helvetica', 'N', 10);
            // add a page
            $pdf->AddPage();
            $pdf->writeHTMLCell(0, 0, '', '',$html, 0, 1, 0, true, '', true);


            $docu = 'solicitud_pago' . $pedi . '.pdf';
            $ruta = DIR_FACTELEC . 'Include/archivos';
            if (!file_exists($ruta)){
                mkdir($ruta);
            }
            $ruta = DIR_FACTELEC . 'Include/archivos/solicitudes_pagos';
            if (!file_exists($ruta)){
                mkdir($ruta);
            }

            $ruta=  DIR_FACTELEC . 'Include/archivos/solicitudes_pagos/'.$docu;
            $pdf->Output($ruta, 'F');
            return $ruta;

        }

    } else {
        $oReturn->alert('Seleccione un pedido..');


    }




}

function reporte_adjudicacion($codpedi,$cod_clpv,$minv_serial){


  //Definiciones
  global $DSN, $DSN_Ifx;
  if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}


  $oIfx = new Dbo;
  $oIfx->DSN = $DSN_Ifx;
  $oIfx->Conectar();

  $oIfxA = new Dbo();
  $oIfxA->DSN = $DSN_Ifx;
  $oIfxA->Conectar();

  $oIfxB = new Dbo();
  $oIfxB->DSN = $DSN_Ifx;
  $oIfxB->Conectar();

  $oCon = new Dbo;
  $oCon->DSN = $DSN;
  $oCon->Conectar();

  $oCnx = new Dbo;
  $oCnx->DSN = $DSN;
  $oCnx->Conectar();


  $idempresa 	= $_SESSION['U_EMPRESA'];
  $idsucursal = $_SESSION['U_SUCURSAL'];


  //LOGOS DEL REPORTE

  $sql = "select empr_img_rep,empr_web_color from saeempr where empr_cod_empr =  $idempresa ";


  if ($oIfx->Query($sql)) {
      if ($oIfx->NumFilas() > 0) {
          $empr_path_logo = $oIfx->f('empr_img_rep');
          $empr_color = $oIfx->f('empr_web_color');

      }
  }
  $oIfx->Free();


  $arc_img = DIR_FACTELEC . "Include/Clases/Formulario/Plugins/reloj/" . basename($empr_path_logo);


  if (file_exists($arc_img)) {
      $imagen = $arc_img;
  } else {
      $imagen = '';
  }
  $logo = '';
  $x = '0px';
  if ($imagen != '') {

      $logo = '<div>
      <img src="' . $imagen . '" style="
      width:200px;
      object-fit; contain;">
      </div>';
      $x = '0px';
  }


  $formatter = new EnLetras();

  //FECHA ORDEN DE COMPRA

  $sqlf="select invp_user_aprob , invp_fmov_minv from comercial.inv_proforma where inv_cod_pedi=$codpedi";
  $fechaord=consulta_string_func($sqlf, 'invp_fmov_minv', $oCon, '');
  $usuario_web=consulta_string_func($sqlf, 'invp_user_aprob', $oCon, '');

  $afecha=explode('-',$fechaord);

  $dia=$afecha[2];
  $mes=nomb_mes($afecha[1]);
  $anio=$afecha[0];

  //$usuario_web = $_SESSION['U_ID'];

  $sqlusua="select empl_cod_empl from comercial.usuario where usuario_id=$usuario_web";

  $cedusua=consulta_string_func($sqlusua, 'empl_cod_empl', $oCon, 0);

  //FIRMA DEL GESTOR

  $array_firma = firma_nomb_empleado($cedusua);

  foreach ($array_firma as $firma) {

      $firma_gest = $firma[0];
      $responsa=$firma[1];
  }

//DATOS DEL PROVEDOR

  $sql="select clpv_nom_clpv from saeclpv where clpv_cod_clpv= $cod_clpv ";

  if ($oIfxB->Query($sql)) {
      if ($oIfxB->NumFilas() > 0) {

          $clpv_nom= $oIfxB->f('clpv_nom_clpv');

      }
  }

//EXTRAER EL DETALLE
  $detalle .= '<table border="1"cellpadding="1">';
  $detalle .='<tr>
                    <th width="5%" style=align="center"><strong>ID</strong></th>
          <th width="12%" style=align="center"><strong>CANTIDAD</strong></th>
          <th width="24%" style=align="center"><strong>NOMBRE</strong></th>
          <th width="24%"style=align="center"><strong>DETALLE</strong></th>
          <th width="10%"style=align="center"><strong>UNIDAD MEDIDA</strong></th>  
          <th width="15%" style=align="center"><strong>V.UNITARIO</strong></th>
          <th width="10%"style=align="center"><strong>V.TOTAL</strong></th>
          
              </tr>';

//DETALLE DE LOS PRODUCTOS
  $sqlDeta ="select  d.dmov_cod_dmov, d.dmov_cod_prod, d.dmov_cod_unid,  
      (d.dmov_can_dmov - d.dmov_can_entr) as cantidad, 
      d.dmov_cun_dmov, 
      dmov_fmov, dmov_cod_ccos,dmov_cod_dped,dmov_cod_pedi,d.dmov_can_dmov
      from saedmov d where
      d.dmov_num_comp = $minv_serial and
      d.dmov_cod_empr = $idempresa
      group by 1,2,3,4,5,6,7,8,9,10
      order by 1";
$i=1;
  if ($oIfx->Query($sqlDeta)) {
      if ($oIfx->NumFilas() > 0) {
          do {
              $prod_cod = $oIfx->f('dmov_cod_prod');
              $codped=$oIfx->f('dmov_cod_dped');
              $codpedi=$oIfx->f('dmov_cod_pedi');

              if(!empty($codpedi)&&!empty($codped)){
                  $sql="select dped_det_dped from saedped where dped_cod_dped=$codped and dped_cod_pedi='$codpedi'";
                  $deta=consulta_string($sql, 'dped_det_dped', $oCnx, 0);
              }

              $sqltip="select prod_cod_tpro from saeprod where prod_cod_prod='$prod_cod'";
              $tip=consulta_string($sqltip, 'prod_cod_tpro', $oCnx, 0);


              $sqlprod="select prod_nom_prod from saeprod where prod_cod_prod='$prod_cod'";
              $prod_nom_prod = trim(consulta_string($sqlprod, 'prod_nom_prod', $oCnx, ''));



              //$bode_cod = $oIfx->f('dmov_cod_bode');
              $unid_cod = $oIfx->f('dmov_cod_unid');
              $cantidad = intval($oIfx->f('dmov_can_dmov'));

              $costo = $oIfx->f('dmov_cun_dmov');

              $cta_inv = $oIfx->f('prbo_cta_inv');
              $cta_iva = $oIfx->f('prbo_cta_ideb');
              $iva = $oIfx->f('prbo_iva_porc');

              if(empty($unid_cod)){
                  $unid_cod=0;
              }
              $sqlun="select unid_nom_unid from saeunid where unid_cod_unid=$unid_cod";
              $unidad=consulta_string( $sqlun,'unid_nom_unid',$oIfxA,'');



              $total_fac=$cantidad*$costo;

              $detalle.='<tr>
    <td width="5%" style=align="center">' . $i . '</td>
    <td  width="12%" style=align="center">' . $cantidad . '</td>
    <td width="24%" style=align="center">' . $prod_nom_prod . '</td>
    <td width="24%" style=align="center">' . $deta . '</td>
    <td width="10%" style=align="center">' . $unidad . '</td>
    <td width="15%" style=align="right">' . number_format($costo, 4, '.', ',') . '</td>
    <td width="10%" style=align="right">' . number_format($total_fac, 2, '.', ',') . '</td>
    </tr>';


              $i++;
          } while ($oIfx->SiguienteRegistro());
      }
  }
  $oIfx->Free();

  $sqlt="select minv_iva_valo, minv_tot_minv from saeminv,saedmov where minv_num_comp=dmov_num_comp and dmov_cod_pedi='$codpedi' and minv_num_comp=$minv_serial";
  $stotal=consulta_string( $sqlt,'minv_tot_minv',$oIfxA,0);
  $iva=consulta_string( $sqlt,'minv_iva_valo',$oIfxA,0);
  $total=$stotal+$iva;

  $detalle.='<tr><td colspan="6" style=align="right">SUBTOTAL</td><td style=align="right">' . number_format(round(($stotal), 2), 2, '.', ',') . '</td></tr>';
  $detalle.='<tr><td colspan="6" style=align="right">IVA 12%</td><td style=align="right">' . number_format(round(($iva), 2), 2, '.', ',') . '</td></tr>';
  $detalle.='<tr><td colspan="6" style=align="right">VALOR TOTAL</td><td style=align="right">' . number_format(round(($total), 2), 2, '.', ',') . '</td></tr>';

  $valtotal=$formatter->ValorEnLetras($total,'d&oacute;lares');
//PLAZOS DE ENTREGA

  $sqldet="select  d.invpd_fpago_prof,d.invpd_tent_prof from comercial.inv_proforma_det as d,comercial.inv_proforma i where d.invpd_cod_clpv=$cod_clpv and 
d.id_inv_prof=i.id_inv_prof and inv_cod_pedi=$codpedi  order by d.id_inv_prof";
  if ($oIfxA->Query($sqldet)) {
      if ($oIfxA->NumFilas() > 0) {
          do{
              $fpag=$oIfxA->f('invpd_fpago_prof');
              if(!empty($fpag)){
                  $fpago=$fpag;
              }
              $plazo=$oIfxA->f('invpd_tent_prof');
              if(!empty($plazo)){
                  $plazoen=$plazo;
              }
          }while($oIfxA->SiguienteRegistro());
      }
  }
//ETIQUETAS DEL REPORTE DE ADJUDICACION

  $sqlrep="select pedi_ord_ref,pedi_ord_eti from saepedi where pedi_cod_pedi=$codpedi";
  $referencia=consulta_string_func($sqlrep, 'pedi_ord_ref', $oCon, '');
  $etiqueta=consulta_string_func($sqlrep, 'pedi_ord_eti', $oCon, '');
//CORREO DEL USUARIO

  $sqlcorreo = "select usuario_email from comercial.usuario where empl_cod_empl='$cedusua'";

  $correo = consulta_string_func($sqlcorreo, 'usuario_email', $oCon, '');

  $detalle.='</table>';

  $responsa=strtolower($responsa);
  $responsa=ucwords($responsa);

  $html=<<<EOD
<div>$logo</div>
<br>
<p align ="center">
NOTIFICACIÓN DE ADJUDICACIÓN No. $codpedi
</p>
<br><br><br>
<p align ="rigth">
Quito, $dia de $mes del $anio 
</p>

<br>$etiqueta<br>$clpv_nom<br>Presente.<br><br><br><b>REFERENCIA:</b>&nbsp;&nbsp;&nbsp;$referencia<br><br><br>
De mi consideración:<br><br><br> Me permito comunicarle, que CRUZ ROJA ECUATORIANA con RUC 1791241746001, de acuerdo al análisis y evaluación de su Proforma, ha resuelto adjudicarle, lo siguiente: <br><br>
$detalle
<br><br><br>
Precio adjudicado:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$valtotal<br>
Forma de Pago:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$fpago<br>
Tiempo de Entrega:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$plazoen<br>
<br><br><br>
Las facturas electrónicas emitidas a Cruz Roja Ecuatoriana, deben ser enviadas a la siguiente dirección de correo electrónico: facturacioncompras@cruzroja.org.ec<br>
Las facturas físicas emitidas a Cruz Roja Ecuatoriana, deben ser entregadas en la dirección: Antonio Elizalde E4-31 y Av. Gran Colombia


<br><br>Atentamente,<br>
$firma_gest<br>

<b>$responsa <br>Departamento de Compras<br>CRUZ ROJA ECUATORIANA-QUITO.</b>
<br><br>
Confirmación Recepción:

EOD;

  $pdf = new TCPDF2('P', 'mm', 'A4', true, 'UTF-8', false);
  $pdf->setPrintHeader(false);
  $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
  $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
  //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
  $pdf->SetMargins(10,5, 10, true);
  $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
  // set auto page breaks
  $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
  // set image scale factor
  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
  // set font
  $pdf->SetFont('helvetica', 'N', 10);
  // add a page
  $pdf->AddPage();

  $pdf->writeHTMLCell(0, 0, '', '',$html, 0, 1, 0, true, '', true);


  $sql="select pedi_carea_pedi from saepedi where pedi_cod_pedi=$codpedi";
  $carea=consulta_string($sql,'pedi_carea_pedi',$oIfxA,'');

  $docu='adjudicacion'.$cod_clpv.$carea.'.pdf';


  $ruta=  DIR_FACTELEC . 'Include/archivos/notificacion_adjudicacion/'.$docu;


  $pdf->Output($ruta, 'F');
  return $ruta;


}

function genera_pdf_doc_veh($cod, $id){


  if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
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


  unset($_SESSION['pdf']);
  $oReturn = new xajaxResponse();
  $idempresa = $_SESSION['U_EMPRESA'];
  $idsucursal = $_SESSION['U_SUCURSAL'];
  $usuario_web = $_SESSION['U_ID'];


  //LOGO DEL REPORTE

  $sql = "select empr_web_color, empr_path_logo,empr_img_rep from saeempr where empr_cod_empr =  $idempresa ";


  if ($oIfx->Query($sql)) {
      if ($oIfx->NumFilas() > 0) {
          $empr_path_logo = $oIfx->f('empr_img_rep');
          $empr_color = $oIfx->f('empr_web_color');


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

      $empr_logo = '<div>
      <img src="' . $imagen . '" style="
      width:330px;
      object-fit; contain;">
      </div>';
      $x = '0px';
  }
//VARIABLES PARA EL REPORTE
  $full='';
  $medio='';
  $vacio='';

  $bueno='';
  $regular='';
  $malo='';

////CONSULTA DATOS DE LA ORDEN DE TRABAJO

  $sqlrmnt=" select rmnt_cod_rmnt, rmnt_sol_rmnt, rmnt_nom_sol,  
rmnt_fec_rmnt,rmnt_raut_rmnt,rmnt_apr_rmnt,  rmnt_desc_rmnt,  rmnt_nove_rmnt,rmnt_cod_veh,
rmnt_niv_comb,rmnt_limp_rmnt,rmnt_km_rmnt,rmnt_kma_rmnt,rmnt_cin_rmnt,rmnt_art_rmnt from saermnt

where rmnt_ord_rmnt='$cod' and rmnt_cod_sucu=$idsucursal and  rmnt_cod_empr=$idempresa";

  if($oIfx->Query($sqlrmnt)){
      if($oIfx->NumFilas() > 0){

          $rmntcod=$oIfx->f('rmnt_cod_rmnt');
          $solicitante=$oIfx->f('rmnt_nom_sol');
          $cedsol=$oIfx->f('rmnt_sol_rmnt');
          $fecha=$oIfx->f('rmnt_fec_rmnt');
          $afecha=explode('-',$fecha);
          $fecha=$afecha[2].'-'.$afecha[1].'-'.$afecha[0];
          $desc=$oIfx->f('rmnt_desc_rmnt');
          $nove=$oIfx->f('rmnt_nove_rmnt');
          $niv=$oIfx->f('rmnt_niv_comb');
          //DATOS DE LA AUTORIZACION
          $autorizador=$oIfx->f('rmnt_raut_rmnt');
          $aprobador=$oIfx->f('rmnt_apr_rmnt');
          if($niv=='FULL'){
              $full='X';
          }
          elseif($niv=='MEDIO'){
              $medio='X';
          }
          elseif($niv=='VACIO'){
              $vacio='X';

          }
          $limp=$oIfx->f('rmnt_limp_rmnt');

          if($limp=='B'){
              $bueno='X';
          }
          elseif($limp=='R'){
              $regular='X';
          }
          elseif($limp=='M'){
              $malo='X';

          }
          $km=$oIfx->f('rmnt_km_rmnt');
          $kmant=$oIfx->f('rmnt_kma_rmnt');
          $codint=$oIfx->f('rmnt_cin_rmnt');
          $aurt=$oIfx->f('rmnt_art_rmnt');

          $codveh=$oIfx->f('rmnt_cod_veh');
          //DATOS DEL VEHICULO

          $sqlveh="select vehm_plac_vehm, vehm_marc_vehm, vehm_mode_vehm, vehm_col_vehm, vehm_ncha_vehm, 
     vehm_nmot_vehm, vehm_afac_vehm  from saevehm where vehm_cod_vehm=$codveh";
          if($oIfxA->Query($sqlveh)){
              if($oIfxA->NumFilas() > 0){

                  $placa=$oIfxA->f('vehm_plac_vehm');
                  $marca=$oIfxA->f('vehm_marc_vehm');
                  $modelo=$oIfxA->f('vehm_mode_vehm');
                  $color=$oIfxA->f('vehm_col_vehm');
                  $chasis=$oIfxA->f('vehm_ncha_vehm');
                  $motor=$oIfxA->f('vehm_nmot_vehm');
                  $afab=$oIfxA->f('vehm_afac_vehm');
              }
          }


      }
  }

  $html = '<table  cellpadding="1" ><tr><td  align="center">' . $empr_logo . ' </td></tr>';
  $html.='<tr><td align="center"><h2>REVISION VEHICULAR</h2></td></tr></table><br><br>';

  $html.='<table  border="1" cellpadding="1" >
<tr> 
<td style="font-size:80%;"align="center" ><strong>Placa</strong></td>
<td style="font-size:80%;"align="center" >'.$placa.'</td>
<td style="font-size:80%;"align="center" ><strong>Kilometraje actual</strong></td>
<td style="font-size:80%;"align="center" >'.$km.'</td>
<td style="font-size:80%;"align="center" ><strong>A&ntilde;o Ultima <br>Revisi&oacute;n T&eacute;cnica</strong></td>
<td style="font-size:80%;"align="center" >'.$aurt.'</td>
</tr>';
  $html.='<tr> 
<td style="font-size:80%;"align="center" ><strong>Motor</strong></td>
<td style="font-size:80%;"align="center" >'.$motor.'</td>
<td style="font-size:80%;"align="center" ><strong>Chasis</strong></td>
<td style="font-size:80%;"align="center" >'.$chasis.'</td>
<td style="font-size:80%;"align="center" ><strong>Tipo</strong></td>
<td style="font-size:80%;"align="center" ></td>
</tr>';
  $html.='<tr> 
<td style="font-size:80%;"align="center" ><strong>Marca</strong></td>
<td style="font-size:80%;"align="center" >'.$marca.'</td>
<td style="font-size:80%;"align="center" ><strong>Modelo</strong></td>
<td style="font-size:80%;"align="center" >'.$modelo.'</td>
<td style="font-size:80%;"align="center" ><strong>Color</strong></td>
<td style="font-size:80%;"align="center" >'.$color.'</td>
</tr>';
  $html.='<tr> 
<td style="font-size:80%;"align="center" ><strong>A&ntilde;o de Fabricaci&oacute;n</strong></td>
<td style="font-size:80%;"align="center" >'.$afab.'</td>
<td style="font-size:80%;"align="center" ><strong>Modelo</strong></td>
<td style="font-size:80%;"align="center" ></td>
<td style="font-size:80%;"align="center" ><strong>Clase</strong></td>
<td style="font-size:80%;"align="center" ></td>
</tr>';
  $html.='<tr> 
<td style="font-size:80%;"align="center" ><strong>Fecha de Constataci&oacute;n</strong></td>
<td style="font-size:80%;"align="center" >'.$fecha.'</td>
<td style="font-size:80%;"align="center" ><strong>Kilometraje de Cambio Anterior de Aceite</strong></td>
<td style="font-size:80%;"align="center" >'.$kmant.'</td>
<td style="font-size:80%;"align="center" ><strong>C&oacute;digo Interno</strong></td>
<td style="font-size:80%;"align="center" ></td>
</tr></table>';

/////NIVEL DE COMBUSTIBLE - UNIDAD LIMPIA

  $niv='<table border ="1" cellpadding="1" ><tr>
<td style="font-size:80%;"align="center" rowspan="2" width="61%"><strong>NIVEL DE COMBUSTIBLE</strong></td>
<td style="font-size:80%;"align="center" width="13%">FULL</td>
<td style="font-size:80%;"align="center" width="13%">MEDIO</td>
<td style="font-size:80%;"align="center" width="13%">VACIO</td>
</tr>';

  $niv.='<tr>
<td style="font-size:80%;"align="center" >'.$full.'</td>
<td style="font-size:80%;"align="center" >'.$medio.'</td>
<td style="font-size:80%;"align="center" >'.$vacio.'</td>
</tr></table>';


  $limp='<table border ="1" cellpadding="1" ><tr>
<td style="font-size:80%;"align="center" rowspan="2" width="70%"><strong>SE ENCUENTRA LIMPIA LA UNIDAD</strong></td>
<td style="font-size:80%;"align="center" width="10%">B</td>
<td style="font-size:80%;"align="center" width="10%">R</td>
<td style="font-size:80%;"align="center" width="10%">M</td>
</tr>';

  $limp.='<tr>
<td style="font-size:80%;"align="center" >'.$bueno.'</td>
<td style="font-size:80%;"align="center" >'.$regular.'</td>
<td style="font-size:80%;"align="center" >'.$malo.'</td>
</tr></table>';


  $html.='<br><br><br><table cellpadding="1" >
<tr> 
<td width="45%">'.$niv.'</td>
<td width="10%"></td>
<td width="45%">'.$limp.'</td>
</tr></table>';

///DETALLE DE LA REVISIÓN

  $desc1='<table border ="1" cellpadding="1">
<tr >
<td style="font-size:80%; background-color: '.$empr_color.';" align="center" width="65%" ><font color ="#ffffff"><strong>DESCRIPCI&Oacute;N</strong></font></td>
<td style="font-size:80%; background-color: '.$empr_color.';"align="center" width="7%" ><font color ="#ffffff"><strong>SI</strong></font></td>
<td style="font-size:80%; background-color: '.$empr_color.';"align="center" width="8%" ><font color ="#ffffff"><strong>NO</strong></font></td>
<td style="font-size:80%; background-color: '.$empr_color.';"align="center" width="20%" ><font color ="#ffffff"><strong>ESTADO</strong></font></td>
</tr>';
  $desc2='<table border ="1" cellpadding="1">
<tr>
<td style="font-size:80%; background-color: '.$empr_color.';" align="center" width="65%" ><font color ="#ffffff"><strong>DESCRIPCI&Oacute;N</strong></font></td>
<td style="font-size:80%; background-color: '.$empr_color.';"align="center" width="7%" ><font color ="#ffffff"><strong>SI</strong></font></td>
<td style="font-size:80%; background-color: '.$empr_color.';"align="center" width="8%" ><font color ="#ffffff"><strong>NO</strong></font></td>
<td style="font-size:80%; background-color: '.$empr_color.';"align="center" width="20%" ><font color ="#ffffff"><strong>ESTADO</strong></font></td>
</tr>';
  $desc3='<table border ="1" cellpadding="1">
<tr>
<td style="font-size:80%; background-color: '.$empr_color.';" align="center" width="65%" ><font color ="#ffffff"><strong>DESCRIPCI&Oacute;N</strong></font></td>
<td style="font-size:80%; background-color: '.$empr_color.';"align="center" width="7%" ><font color ="#ffffff"><strong>SI</strong></font></td>
<td style="font-size:80%; background-color: '.$empr_color.';"align="center" width="8%" ><font color ="#ffffff"><strong>NO</strong></font></td>
<td style="font-size:80%; background-color: '.$empr_color.';"align="center" width="20%" ><font color ="#ffffff"><strong>ESTADO</strong></font></td>
</tr>';


  $sqlcont="select count(*) as conteo from saerveh";

  $cont=consulta_string($sqlcont,'conteo',$oIfx,0);

  while(( $cont % 3 ) != 0){

      $cont++;
  }

  $contnew=$cont;

  $n1=$contnew/3;
  $n2=2*$n1;
  $n3=3*$n1;


  $sqlrveh="select rveh_cod_rveh, rveh_desc_rveh from saerveh order by rveh_cod_rveh asc  ";
  if($oIfxA->Query($sqlrveh)){
      if($oIfxA->NumFilas() > 0){

          do{
              $codrevh=$oIfxA->f('rveh_cod_rveh');

              $descripcion=$oIfxA->f('rveh_desc_rveh');

              $sqldet="select drmn_cod_rveh,drmn_est_drmn from saedrmn where drmn_cod_rmnt=$rmntcod and drmn_cod_rveh= $codrevh ";

              if($oIfx->Query($sqldet)){

                  if($oIfx->NumFilas() > 0){

                      $codrveh=$oIfx->f('drmn_cod_rveh');
                      $estado=$oIfx->f('drmn_est_drmn');

                      if($codrevh<=$n1){

                          $desc1.='<tr>
                              <td style="font-size:60%;"align="left" width="65%">'.$descripcion.'</td>
                              <td style="font-size:60%;"align="center" width="7%"><strong>X</strong></td>
                              <td style="font-size:60%;"align="center" width="8%"><strong></strong></td>
                              <td style="font-size:60%;"align="center" width="20%"><strong>'.$estado.'</strong></td>
                              </tr>';
                      }
                      elseif($codrevh>$n1&&$codrevh<=$n2){

                          $desc2.='<tr>
                                  <td style="font-size:60%;"align="left" width="65%">'.$descripcion.'</td>
                                  <td style="font-size:60%;"align="center" width="7%"><strong>X</strong></td>
                                  <td style="font-size:60%;"align="center" width="8%"><strong></strong></td>
                                  <td style="font-size:60%;"align="center" width="20%"><strong>'.$estado.'</strong></td>
                                  </tr>';



                      }
                      elseif($codrevh>$n2&&$codrevh<=$n3){

                          $desc3.='<tr>
                                  <td style="font-size:60%;"align="left" width="65%">'.$descripcion.'</td>
                                  <td style="font-size:60%;"align="center" width="7%"><strong>X</strong></td>
                                  <td style="font-size:60%;"align="center" width="8%"><strong></strong></td>
                                  <td style="font-size:60%;"align="center" width="20%"><strong>'.$estado.'</strong></td>
                                  </tr>';



                      }
                  }
                  else{
                      if($codrevh<=$n1){

                          $desc1.='<tr>
                              <td style="font-size:60%;"align="left" width="65%">'.$descripcion.'</td>
                              <td style="font-size:60%;"align="center" width="7%"><strong></strong></td>
                              <td style="font-size:60%;"align="center" width="8%"><strong>X</strong></td>
                              <td style="font-size:60%;"align="center" width="20%"><strong></strong></td>
                              </tr>';
                      }
                      elseif($codrevh>$n1&&$codrevh<=$n2){

                          $desc2.='<tr>
                                  <td style="font-size:60%;"align="left" width="65%">'.$descripcion.'</td>
                                  <td style="font-size:60%;"align="center" width="7%"><strong></strong></td>
                                  <td style="font-size:60%;"align="center" width="8%"><strong>X</strong></td>
                                  <td style="font-size:60%;"align="center" width="20%"><strong></strong></td>
                                  </tr>';



                      }
                      elseif($codrevh>$n2&&$codrevh<=$n3){

                          $desc3.='<tr>
                                  <td style="font-size:60%;"align="left" width="65%">'.$descripcion.'</td>
                                  <td style="font-size:60%;"align="center" width="7%"><strong></strong></td>
                                  <td style="font-size:60%;"align="center" width="8%"><strong>X</strong></td>
                                  <td style="font-size:60%;"align="center" width="20%"><strong></strong></td>
                                  </tr>';
                      }
                  }

              }

          }while($oIfxA->SiguienteRegistro());
      }
  }


  $desc1.='</table>';
  $desc2.='</table>';
  $desc3.='</table>';
  $html.='<br><br><br>
<table cellpadding="1" >
<tr> 
<td width="33.3%">'.$desc1.'</td>
<td width="33.3%">'.$desc2.'</td>
<td width="33.3%">'.$desc3.'</td>
</tr></table>
';

//DESCRIPCIONES Y NOVEDADES

  $html.='<br><br><table border ="1" cellpadding="1">
<tr>
<td style="font-size:80%; background-color: '.$empr_color.';" align="left" width="100%" ><font color ="#ffffff"><strong>DESCRIPCION GENERAL DEL DAÑO O DESPERFECTO MECANICO</strong></font></td>
</tr>';
  $html.='<tr>
<td style="font-size:70%;" align="left">'.$desc.'</td>
</tr>';
  $html.='<tr>
<td style="font-size:80%; background-color: '.$empr_color.';" align="left" width="100%" ><font color ="#ffffff"><strong>NOVEDADES ENCONTRADAS EN LA UNIDAD</strong></font></td>
</tr>';
  $html.='<tr>
<td style="font-size:70%;" align="left">'.$nove.'</td>
</tr></table>';

///FIRMAS DE AUTORIZACION

  $firmasol=firma_nomb_empleado($cedsol);

  foreach ($firmasol as $firma) {

      $firsoli = $firma[0];

  }
//AUTORIZADOR

  if(!empty($autorizador)){

      $firmaut=firma_nomb_empleado($autorizador);

      foreach ($firmaut as $firma) {

          $firaut = $firma[0];

          $autorizador=$firma[1];

      }


  }
  if(!empty($aprobador)){

      $firapro=firma_nomb_empleado($aprobador);

      foreach ($firapro as $firma) {

          $firaut = $firma[0];

          $aprobador=$firma[1];

      }


  }

  $html.='<br><br><br><br><br><br><br><br><br><br>
<table cellpadding="1" cellspacing="2">
<tr>
<td style="font-size:70%;" align="center"><strong>SOLICITADO POR:</strong></td>
<td style="font-size:70%;" align="center"><strong>REVISADO Y AUTORIZADO POR:</strong></td>
<td style="font-size:70%;" align="center"><strong>APROBADO POR:</strong></td>
</tr>

<tr>
<td style="font-size:70%;" align="center">'.$firsoli.'<br>____________________________</td>
<td style="font-size:70%;" align="center">'.$firaut.'<br>____________________________</td>
<td style="font-size:70%;" align="center">'.$firapro.'<br>____________________________</td>
</tr>

<tr>
<td style="font-size:70%;" align="center">'.$solicitante.'<br><br><strong>TRANSPORTE Y SERVICIOS GENERALES</strong></td>
<td style="font-size:70%;" align="center">'.$autorizador.'<br><br><strong>TRANSPORTE Y SERVICIOS GENERALES</strong></td>
<td style="font-size:70%;" align="center">'.$aprobador.'<br><br><strong>COORDINADORA NACIONAL DE LOGISTICA</strong></td>
</tr>

</table>';



  if ($id == 1) {


      $pdf = new TCPDF2('P', 'mm', 'A4', true, 'UTF-8', false);
      $pdf->setPrintHeader(false);
      $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
      //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
      $pdf->SetMargins(10, 5, 10, true);
      // set auto page breaks
      $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
      // set image scale factor
      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
      // set font
      $pdf->SetFont('helvetica', 'N', 10);
      // add a page
      $pdf->AddPage();
      $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

      $fecha = date('d-m-Y H:i:s');
      $docu = $fecha . "_" . $nota_compra . '.pdf';
      $docu = str_replace(" ", "_", $docu);
      $docu = str_replace(":", "-", $docu);

      $ruta = DIR_FACTELEC . 'Include/archivos/solicitudes_revision_vehicular/' . $docu;
      $pdf->Output($ruta, 'F');
      return $docu;


  } else {
      return $html;

  }


}


?>

