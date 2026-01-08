<?php
include_once('../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');


global $DSN_Ifx, $DSN;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();

$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();

$oIfx2 = new Dbo;
$oIfx2->DSN = $DSN_Ifx;
$oIfx2->Conectar();

$oCon = new Dbo;
$oCon->DSN = $DSN;
$oCon->Conectar();





try {

    $idEmpresa = $_SESSION['U_EMPRESA'];


    $empresa =  $_GET['empresa'];
    if(empty($empresa)) $empresa= $idEmpresa;
    $sql = "select empr_web_color, empr_cod_pais,empr_cm1_empr, empr_rimp_sn, empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_img_rep, empr_iva_empr,empr_tel_resp, empr_ac1_empr, empr_ac2_empr, empr_mai_empr, empr_tip_empr
                                            from saeempr where empr_cod_empr = $empresa ";


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
        }
    }
    $oIfx->Free();



    $referido =  $_GET['cod_ref'];

    //NOMBRE

    $sql="select clpv_nom_clpv from saeclpv where clpv_cod_clpv=$referido";
    $nomb_referido=consulta_string($sql,'clpv_nom_clpv',$oIfx,'');



    $array_imp = $_SESSION['U_EMPRESA_IMPUESTO'];
    $etiqueta_iva=$array_imp ['IVA'];
    $empr_cod_pais = $_SESSION['U_PAIS_COD'];

    // IMPUESTOS POR PAIS
 $sql = "select p.impuesto, p.etiqueta, p.porcentaje from comercial.pais_etiq_imp p where
 p.pais_cod_pais = $empr_cod_pais and etiqueta='$etiqueta_iva'";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $porcentaje = $oCon->f('porcentaje');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

    //FILTROS
    $sqlFiltroFecha  = $_GET['fil_fecha'];
    $fecha_inicio =  $_GET['fini'];
    $fecha_fin =  $_GET['ffin'];
    if(!empty($fecha_inicio)&&!empty($fecha_fin)) {
        $sqlFiltroFecha = " and fact_fech_fact between '$fecha_inicio' and '$fecha_fin'";
    }
    $sqlFiltroEstado = $_GET['fil_est'];
    $sqlFiltroUser   = $_GET['fil_user'];
    $sqlFiltroSucu   = $_GET['fil_sucu'];

    if(!empty($sqlFiltroEstado )){

        $arrayEstado=explode(':',$sqlFiltroEstado);

        $sqlFiltroEstado=' and fact_est_fact in(';
        foreach($arrayEstado as $est){
            $sqlFiltroEstado.="'".$est."',";
        }
        $sqlFiltroEstado = trim($sqlFiltroEstado, ',');
        $sqlFiltroEstado.=')';
    }

     $ctrl_reg='N';
     //TOTALES GLOBAL

     $total_sin_iva_m=0;
     $total_con_iva_m=0;
     $total_iva_m=0;
     $total_desc_m=0;
     $total_monto_m=0;
     $total_comision_m=0;

     $sql = "select fact_fech_fact,
     fact_num_preimp, fact_nom_cliente, fact_nse_fact, 
    fact_est_fact, 
     sum(round(fact_tot_fact,4)) as fact_tot_fact,
     sum(round(fact_con_miva,4)) as fact_con_miva,
     sum(round(fact_sin_miva,4)) as fact_sin_miva,
     sum(round(fact_iva,4)) as fact_iva,
     sum(round(fact_ice,4)) as fact_ice,
     sum(round(fact_dsg_valo,4)) as fact_dsg_valo,
     sum(round(fact_fle_fact + fact_otr_fact + fact_fin_fact,4)) as total_otros,
     sum(round(fact_con_miva + fact_sin_miva +  fact_iva  + fact_fle_fact + fact_otr_fact + fact_fin_fact + fact_val_irbp ,4)) as total
     from saefact
     where 
     fact_cod_ref =$referido and  
     fact_cod_empr = $empresa
     $sqlFiltroSucu 
     $sqlFiltroFecha
     $sqlFiltroEstado 
     $sqlFiltroUser
     
     group by 1,2,3,4,5";


     $i=1;
     $total_sin_iva=0;
     $total_con_iva=0;
     $total_iva=0;
     $total_desc=0;
     $total_monto=0;
     $total_comision=0;

      $sHtml = '<font style="font-size:13px;"><b>VENTAS</b></font>';
      $sHtml .= '<table   style="width:99%;font-size:11px;margin-top:10px;" cellspacing="1">
                        <thead>
                        <tr>
                            <th style="border-bottom:0.5px solid; width:3%;">No.</th>
                            <th style="border-bottom:0.5px solid; width:7%;">FECHA</th>
                            <th style="border-bottom:0.5px solid; width:13%;">N° FACTURA</th>
                            <th style="border-bottom:0.5px solid; width:28%;">CLIENTE</th>
                            <th style="border-bottom:0.5px solid; width:13%;">F. PAGO</th>
                            <th style="border-bottom:0.5px solid; width:6%;">SUBT 0%</th>
                            <th style="border-bottom:0.5px solid; width:6%;">SUBT '.$porcentaje.'%</th>
                            <th style="border-bottom:0.5px solid; width:6%;">DESCT</th>
                            <th style="border-bottom:0.5px solid; width:6%;">'.$etiqueta_iva.' '.$porcentaje.'%</th>
                            <th style="border-bottom:0.5px solid; width:7%;">TOTAL</th>
                            <th style="border-bottom:0.5px solid; width:6%;">COMISION</th>
                        </tr>
                        </thead><tbody>';

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $ctrl_reg='S';
            do{

                $fecha= date('d-m-Y', strtotime($oIfx->f('fact_fech_fact')));
                $num_fact= $oIfx->f('fact_nse_fact').' '.$oIfx->f('fact_num_preimp');
                $cliente= $oIfx->f('fact_nom_cliente');
                $con_iva= $oIfx->f('fact_con_miva');
                $sin_iva= $oIfx->f('fact_sin_miva');
                $descuento= $oIfx->f('fact_dsg_valo');
                $iva= $oIfx->f('fact_iva');
                $total = $oIfx->f('total');
                
                //COMISION ESTANDAR MAESTROS 10% DE COMISION 
                $comision= $con_iva*0.10;

                $total_sin_iva+=$sin_iva;
                $total_con_iva+=$con_iva;
                $total_iva+=$iva;
                $total_desc+=$descuento;

                $total_monto+=$total;
                $total_comision+=$comision;

                $bgcolor='';
                if($i%2==0) $bgcolor='bgcolor="#DFDFDF"';

                $sHtml .= '<tr '.$bgcolor.'>';
                $sHtml .= '<td style="width:3%;" >'.$i.'</td>';
                $sHtml .= '<td style="width:7%;">'.$fecha.'</td>';
                $sHtml .= '<td style="width:13%;">'.$num_fact.'</td>';
                $sHtml .= '<td style="width:28%;">'.$cliente.'</td>';
                $sHtml .= '<td style="width:13%;"></td>';
                $sHtml .= '<td style="width:6%;" align="right">' . number_format($sin_iva, 4, '.', '') . '</td>';
                $sHtml .= '<td style="width:6%;" align="right">' . number_format($con_iva, 4, '.', '') . '</td>';
                $sHtml .= '<td style="width:6%;" align="right">' . number_format($descuento, 4, '.', '') . '</td>';
                $sHtml .= '<td style="width:6%;" align="right">' . number_format($iva, 2, '.', '') . '</td>';
                $sHtml .= '<td style="width:7%;" align="right">' . number_format($total, 4, '.', '') . '</td>';
                $sHtml .= '<td style="width:6%;" align="right">' . number_format($comision, 2, '.', '') . '</td>';
                $sHtml .= '</tr>';

                $i++;

            }while ($oIfx->SiguienteRegistro());
            $sHtml .= '</tbody>';

            $total_sin_iva_m+=$total_sin_iva;
            $total_con_iva_m+=$total_con_iva;
            $total_iva_m+=$total_iva;
            $total_desc_m+=$total_desc;
            $total_monto_m+=$total_monto;
            $total_comision_m+=$total_comision;

            $sHtml .= '<tfoot><tr >
                            <td align="right" style="width:63%; color: black; font-size: 11px;" colspan="5" ><b>TOTAL VENTAS:</b></td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round($total_sin_iva, 2), 4) . '</td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round($total_con_iva, 2), 4) . '</td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round($total_desc, 2), 4) . '</td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round($total_iva, 2), 2) . '</td>
                            <td align="right" style="width:7%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round($total_monto, 2), 4) . '</td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round($total_comision, 2), 2) . '</td>
                    </tr></tfoot>';
   

            
        }
        else{
            $sHtml .= '<tr >';
            $sHtml .= '<td style="width:3%;"></td>';
            $sHtml .= '<td style="width:7%;"></td>';
            $sHtml .= '<td style="width:13%;"></td>';
            $sHtml .= '<td style="width:28%;"></td>';
            $sHtml .= '<td style="width:13%;"></td>';
            $sHtml .= '<td style="width:6%;"align="right"></td>';
            $sHtml .= '<td style="width:6%;"align="right"></td>';
            $sHtml .= '<td style="width:6%;"align="right"></td>';
            $sHtml .= '<td style="width:6%;"align="right"></td>';
            $sHtml .= '<td style="width:7%;"align="right"></td>';
            $sHtml .= '<td style="width:6%;"align="right"></td>';
            $sHtml .= '</tr>';
            
            $sHtml .= '</tbody>';
           $sHtml .= '<tfoot><tr >
                            <td align="right" style="width:63%; color: black; font-size: 11px;" colspan="5" ><b>TOTAL VENTAS:</b></td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round(0, 2), 4) . '</td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round(0, 2), 4) . '</td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round(0, 2), 4) . '</td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round(0, 2), 2) . '</td>
                            <td align="right" style="width:7%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round(0, 2), 4) . '</td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round(0, 2), 2) . '</td>
                    </tr></tfoot>';
        }
        
    }
    $oIfx->Free();

    $sHtml .= '</table>';

    //NOTAS DE CREDITO ASOCIADAS


    $sql = "select
    ncre_cod_ncre,

    ncre_cod_fact,
    ncre_fech_fact as fact_fech_fact,
     ncre_num_preimp as fact_num_preimp, 
     ncre_nom_cliente as fact_nom_cliente, 
     ncre_nse_ncre as fact_nse_fact, 
     ncre_est_fact as fact_est_fact, 
     sum(round(ncre_tot_fact,4)) as fact_tot_fact,
     sum(round(ncre_con_miva,4)) as fact_con_miva,
     sum(round(ncre_sin_miva,4)) as fact_sin_miva,
     sum(round(ncre_iva,4)) as fact_iva,
     sum(round(ncre_ice,4)) as fact_ice,
     sum(round(ncre_dsg_valo,4)) as fact_dsg_valo,
     sum(round(ncre_fle_fact + ncre_otr_fact + ncre_fin_fact,4)) as total_otros,
     sum(round(ncre_con_miva + ncre_sin_miva +  ncre_iva  + ncre_fle_fact + ncre_otr_fact + ncre_fin_fact  ,4)) as total
     from saencre
     where 
     ncre_cod_fact in (
        select fact_cod_fact
        from saefact
     where 
     fact_cod_ref =$referido and  
     fact_cod_empr = $empresa
     $sqlFiltroSucu 
     $sqlFiltroFecha
     $sqlFiltroEstado 
     $sqlFiltroUser

    )
    group by 1,2,3,4,5,6,7
    ";

     $i=1;
     $total_sin_iva=0;
     $total_con_iva=0;
     $total_iva=0;
     $total_desc=0;
     $total_monto=0;
     $total_comision=0;

      $sHtml .= '<font style="font-size:13px;"><b>DEVOLUCIONES</b></font>';
      $sHtml .= '<table style="width:99%;font-size:11px;margin-top:20px;" cellspacing="1">
                        <thead>
                        <tr>
                            <th style="border-bottom:0.5px solid; width:3%;">No.</th>
                            <th style="border-bottom:0.5px solid; width:7%;">FECHA</th>
                            <th style="border-bottom:0.5px solid; width:13%;">N° NOTA CREDITO</th>
                            <th style="border-bottom:0.5px solid; width:28%;">CLIENTE</th>
                            <th style="border-bottom:0.5px solid; width:13%;">N° FACTURA</th>
                            <th style="border-bottom:0.5px solid; width:6%;">SUBT 0%</th>
                            <th style="border-bottom:0.5px solid; width:6%;">SUBT '.$porcentaje.'%</th>
                            <th style="border-bottom:0.5px solid; width:6%;">DESCT</th>
                            <th style="border-bottom:0.5px solid; width:6%;">'.$etiqueta_iva.' '.$porcentaje.'%</th>
                            <th style="border-bottom:0.5px solid; width:7%;">TOTAL</th>
                            <th style="border-bottom:0.5px solid; width:6%;">COMISION</th>
                        </tr>
                        </thead><tbody>';

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {

            do{
                $id = $oIfx->f('ncre_cod_ncre');
                $ncre_cod_fact = $oIfx->f('ncre_cod_fact');
                $fecha= date('d-m-Y', strtotime($oIfx->f('fact_fech_fact')));
                $num_fact= $oIfx->f('fact_nse_fact').' '.$oIfx->f('fact_num_preimp');
                $cliente= $oIfx->f('fact_nom_cliente');
                $con_iva= $oIfx->f('fact_con_miva');
                $sin_iva= $oIfx->f('fact_sin_miva');
                $descuento= $oIfx->f('fact_dsg_valo');
                $iva= $oIfx->f('fact_iva');
                $total = $oIfx->f('total');

                
                //COMISION ESTANDAR MAESTROS 10% DE COMISION  AVALIDAR
                $comision= $total*0.10;

                $total_sin_iva+=$sin_iva;
                $total_con_iva+=$con_iva;
                $total_iva+=$iva;
                $total_desc+=$descuento;

                $total_monto+=$total;
                $total_comision+=$comision;



                if ($ncre_cod_fact == '') {
                    $ncre_cod_fact = 0;
                }

                $sql = "select fact_nse_fact, fact_num_preimp, fact_fech_fact,
                        fact_cm2_fact
                        from saefact 
                        where fact_cod_empr = $empresa and 
                        fact_cod_fact = $ncre_cod_fact";
                // var_dump($sql);exit;


                $numero_fac = "";
                if ($oIfx2->Query($sql)) {
                    if ($oIfx2->NumFilas() > 0) {
                        $fact_nse_fact = $oIfx2->f('fact_nse_fact');
                        $fact_num_preimp = $oIfx2->f('fact_num_preimp');
                        $fact_fech_fact = fecha_mysql_func($oIfx2->f('fact_fech_fact'));
                        $fact_cm2_fact = $oIfx2->f('fact_cm2_fact');

                        $nse = substr($fact_nse_fact, 0, 3);
                        $pto = substr($fact_nse_fact, 3, 6);
                        //$numero_fac =  $nse . '-' . $pto . '-' . $fact_num_preimp;
                        $numero_fac =$fact_nse_fact.' '.$fact_num_preimp;
                    } else {
                        if ($ncre_cod_fact == 0) {
                            $sqlNcre = "select ncre_nse_ncre, ncre_cod_aux, ncre_fec_emfa from saencre where 
                                    ncre_cod_ncre = $id and 
                                    ncre_cod_empr = $empresa ";
                            if ($oIfx2->Query($sqlNcre)) {
                                if ($oIfx2->NumFilas() > 0) {
                                    $fact_nse_fact = $oIfx2->f('ncre_nse_ncre');
                                    $numero_fac = $oIfx2->f('ncre_cod_aux');
                                    //$fact_fech_fact = fecha_sri($oIfx2->f('ncre_fec_emfa'));
                                    $fact_fech_fact = $oIfx2->f('ncre_fec_emfa');
                                }
                            }
                        }else{
                            $nse = substr($fact_nse_fact, 0, 3);
                            $pto = substr($fact_nse_fact, 3, 6);
                            //$numero_fac =  $nse . '-' . $pto . '-' . $fact_num_preimp;
                            $numero_fac =$fact_nse_fact.' '.$fact_num_preimp;
                        }
                    }
                }
                $oIfx2->Free();




                $bgcolor='';
                if($i%2==0) $bgcolor='bgcolor="#DFDFDF"';

                $sHtml .= '<tr '.$bgcolor.'>';
                $sHtml .= '<td style="width:3%;">'.$i.'</td>';
                $sHtml .= '<td style="width:7%;">'.$fecha.'</td>';
                $sHtml .= '<td style="width:13%;">'.$num_fact.'</td>';
                $sHtml .= '<td style="width:28%;">'.$cliente.'</td>';
                $sHtml .= '<td style="width:13%;">'.$numero_fac.'</td>';
                $sHtml .= '<td style="width:6%;"align="right">' . number_format($sin_iva, 4, '.', '') . '</td>';
                $sHtml .= '<td style="width:6%;"align="right">' . number_format($con_iva, 4, '.', '') . '</td>';
                $sHtml .= '<td style="width:6%;"align="right">' . number_format($descuento, 4, '.', '') . '</td>';
                $sHtml .= '<td style="width:6%;"align="right">' . number_format($iva, 2, '.', '') . '</td>';
                $sHtml .= '<td style="width:7%;"align="right">' . number_format($total, 4, '.', '') . '</td>';
                $sHtml .= '<td style="width:6%;"align="right">' . number_format($comision, 2, '.', '') . '</td>';
                $sHtml .= '</tr>';

                $i++;

            }while ($oIfx->SiguienteRegistro());

            $total_sin_iva_m-=$total_sin_iva;
            $total_con_iva_m-=$total_con_iva;
            $total_iva_m-=$total_iva;
            $total_desc_m-=$total_desc;
            $total_monto_m-=$total_monto;
            $total_comision_m-=$total_comision;
            $sHtml .= '</tbody>';

            $sHtml .= '<tfoot><tr >
                      
                            <td align="right" style="width:63%; color: black; font-size: 11px;" colspan="5" ><b>TOTAL DEVOLUCIONES:</b></td>
                            <td align="right" style="width:6%;  border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round($total_sin_iva, 2), 4) . '</td>
                            <td align="right" style="width:6%;  border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round($total_con_iva, 2), 4) . '</td>
                            <td align="right" style="width:6%;  border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round($total_desc, 2), 4) . '</td>
                            <td align="right" style="width:6%;  border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round($total_iva, 2), 2) . '</td>
                            <td align="right" style="width:7%;  border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round($total_monto, 2), 4) . '</td>
                            <td align="right" style="width:6%;  border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round($total_comision, 2), 2) . '</td>
                    </tr></tfoot>';
   
        }
        else{
            $sHtml .= '<tr >';
            $sHtml .= '<td style="width:3%;"></td>';
            $sHtml .= '<td style="width:7%;"></td>';
            $sHtml .= '<td style="width:13%;"></td>';
            $sHtml .= '<td style="width:28%;"></td>';
            $sHtml .= '<td style="width:13%;"></td>';
            $sHtml .= '<td style="width:6%;"align="right"></td>';
            $sHtml .= '<td style="width:6%;"align="right"></td>';
            $sHtml .= '<td style="width:6%;"align="right"></td>';
            $sHtml .= '<td style="width:6%;"align="right"></td>';
            $sHtml .= '<td style="width:7%;"align="right"></td>';
            $sHtml .= '<td style="width:6%;"align="right"></td>';
            $sHtml .= '</tr>';
            $sHtml .= '</tbody>';
             $sHtml .= '<tfoot><tr ">
                      
                            <td align="right" style="width:63%; color: black; font-size: 12px;" colspan="5" ><b>TOTAL DEVOLUCIONES:</b></td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round(0, 2), 4) . '</td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round(0, 2), 4) . '</td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round(0, 2), 4) . '</td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round(0, 2), 2) . '</td>
                            <td align="right" style="width:7%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round(0, 2), 4) . '</td>
                            <td align="right" style="width:6%; border-top:0.5px solid;  color: black; font-size: 11px;">' . number_format(round(0, 2), 2) . '</td>
                    </tr></tfoot>';
        }
        
    }
    $oIfx->Free();

    $sHtml .= '</table>';

    //TOTAL MAESTRO

    $sHtml .= '<table style="width:99%;font-size:12px;margin-top:10px;" cellspacing="1">';

    $sHtml .= '<tr >';
    $sHtml .= '<td style="width:3%;"></td>';
    $sHtml .= '<td style="width:7%;"></td>';
    $sHtml .= '<td style="width:13%;"></td>';
    $sHtml .= '<td style="width:28%;"></td>';
    $sHtml .= '<td style="width:13%;"></td>';
    $sHtml .= '<td style="width:6%;"align="right"></td>';
    $sHtml .= '<td style="width:6%;"align="right"></td>';
    $sHtml .= '<td style="width:6%;"align="right"></td>';
    $sHtml .= '<td style="width:6%;"align="right"></td>';
    $sHtml .= '<td style="width:7%;"align="right"></td>';
    $sHtml .= '<td style="width:6%;"align="right"></td>';
    $sHtml .= '</tr>';

    $sHtml .= '<tr >
                            <td align="right" style="width:63%; color: red; font-size: 12px;" colspan="5"><b>TOTAL MAESTRO:</b></td>
                            <td align="right" style="width:6%;  border-top:0.5px solid;  color: black; font-size: 12px;"><b>' . number_format(round($total_sin_iva_m, 2), 4) . '</b></td>
                            <td align="right" style="width:6%;  border-top:0.5px solid;  color: black; font-size: 12px;"><b>' . number_format(round($total_con_iva_m, 2), 4) . '</b></td>
                            <td align="right" style="width:6%;  border-top:0.5px solid;  color: black; font-size: 12px;"><b>' . number_format(round($total_desc_m, 2), 4) . '</b></td>
                            <td align="right" style="width:6%;  border-top:0.5px solid;  color: black; font-size: 12px;"><b>' . number_format(round($total_iva_m, 2), 2) . '</b></td>
                            <td align="right" style="width:7%;  border-top:0.5px solid;  color: black; font-size: 12px;"><b>' . number_format(round($total_monto_m, 2), 4) . '</b></td>
                            <td align="right" style="width:6%;  border-top:0.5px solid;  color: black; font-size: 12px;"><b>' . number_format(round($total_comision_m, 2), 2) . '</b></td>
                    </tr>';
    $sHtml .= '</table>';


    if($ctrl_reg=='N') $sHtml='<div style="text-align:center"><span><b>...Sin datos...</b></span></div>';

    $dia_ini=date('d',strtotime($fecha_inicio));
    $mes_ini=strtoupper(nomb_mes(date('m',strtotime($fecha_inicio))));
    $anio_ini=date('Y',strtotime($fecha_inicio));


    $dia_fin=date('d',strtotime($fecha_fin));
    $mes_fin=strtoupper(nomb_mes(date('m',strtotime($fecha_fin))));
    $anio_fin=date('Y',strtotime($fecha_fin));

    $table='<font style="font-size:15px;"><b>'.$razonSocial.'</b></font><br><hr>';
    $table.='<font style="font-size:15px;"><b>Volumen de ventas y comisiones por Maestro</b></font><br><br>';
    $table.='<font style="font-size:13px;">DESDE: '.$dia_ini.' '.$mes_ini.' '.$anio_ini.'&nbsp;&nbsp;&nbsp;&nbsp;HASTA: '.$dia_fin.' '.$mes_fin.' '.$anio_fin.'</font><br><hr>';
    $table.='<font style="font-size:15px;"><b>NOMBRE:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$nomb_referido.'</b></font><br><br>';
    $table.=$sHtml;

} catch (Exception $e) {

    echo $e->getMessage();

} //fin if try

$html2pdf = new HTML2PDF('L', 'A4', 'es');
$html2pdf->WriteHTML($table);
$html2pdf->Output('comisiones.pdf', '');



 ?>