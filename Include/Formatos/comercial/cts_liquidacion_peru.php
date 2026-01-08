<?php

function cts_liquidacion_peru($id_empl = '', $id_empresa = 0, $id_rubr = 'RCTSA', $id_anio = 0, &$rutaPdf = '')
{
    //Definiciones
    global $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();


    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();


    $oIfxC = new Dbo;
    $oIfxC->DSN = $DSN_Ifx;
    $oIfxC->Conectar();
    
    $nombre_archivo = 'CTS_LIQUIDACION_' . $id_anio . '_' . $id_rubr . '_' . $id_empl;
    $sql = "select * from saeempr where empr_cod_empr='$id_empresa'";
    $empr_path_logo = consulta_string($sql, 'empr_path_logo', $oIfx, '');


    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;

    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

    if (file_exists($path_logo_img)) {
        $logo = '<div>
            <img src="' . $path_logo_img . '" style="
            width:100px;
            object-fit; contain;">
            </div>';
    }
    else{
        $logo = '<div>
        <h1 style="color:#FF0000">SIN LOGO</h1>
        </div>';
    }

    


    $empr_repres = consulta_string($sql, 'empr_repres', $oIfx, '');
    $empr_nom = consulta_string($sql, 'empr_nom_empr', $oIfx, '');
    $empr_ruc = consulta_string($sql, 'empr_ruc_empr', $oIfx, '');
    $empr_dir = consulta_string($sql, 'empr_dir_empr', $oIfx, '');
    $empr_ced_repr = consulta_string($sql, 'empr_ced_repr', $oIfx, '');

    $sql = "select * from saeempl where empl_cod_empl='$id_empl'";
    //echo $sql;exit;
    $empl_ape = consulta_string($sql, 'empl_ape_empl', $oIfx, '');
    $empl_nom = consulta_string($sql, 'empl_nom_empl', $oIfx, '');
    $empl_val_sala = consulta_string($sql, 'empl_sal_sala', $oIfx, '');
    $empl_cod_nove = consulta_string($sql, 'empl_cod_nove', $oIfx, '');
    $empl_fir_empl = consulta_string($sql, 'empl_fir_empl', $oIfx, '');
    $empl_cod_banc = consulta_string($sql, 'empl_cod_banc', $oIfx, '');
    $empl_num_ctas = consulta_string($sql, 'empl_num_ctas', $oIfx, '');

    $sql = "SELECT banc_nom_banc FROM saebanc WHERE banc_cod_empr = '{$id_empresa}' AND banc_cod_banc = '{$empl_cod_banc}';";
    $empl_cod_banc = consulta_string($sql, 'banc_nom_banc', $oIfx, '');
    if ($empl_cod_nove == '') {
        $empl_cod_nove = 'V';
    }
    $sql = "SELECT  nove_des_nove FROM saelnove where nove_ini_nove='$empl_cod_nove'";
    $empl_cod_nove = consulta_string($sql, 'nove_des_nove', $oIfx, '');
    //echo $sql;exit;
    /*
    $sql = "select * from saeesem where esem_cod_empr='$id_empresa' and DATE_PART('year',esem_fec_sali)=$id_anio and esem_cod_empl='$id_empl'";
    $esem_fec_sali = consulta_string($sql, 'esem_fec_sali', $oIfx, '');
    //echo $sql;exit;
    $array_f = explode('-', $esem_fec_sali);
    $anio_sali = $array_f[0];
    $mes_sali = $array_f[1];
    $dia_sali = $array_f[2];
    */
    $esem_fec_ingr = consulta_string($sql, 'esem_fec_ingr', $oIfx, '');
    $esem_cod_estr = consulta_string($sql, 'esem_cod_estr', $oIfx, '');
    $sql = "select * from saeestr where estr_cod_estr='$esem_cod_estr' and estr_cod_empr='$id_empresa'";

    $firma_img = '<br><br><br><br><br><br><br>';
    if(!empty($empl_fir_empl)){
        $dir_fir = DIR_FACTELEC . "Include/Clases/Formulario/Plugins/reloj/" . basename($empl_fir_empl);
        if (file_exists($dir_fir)) {
            $firma_img = '
                <img src="' . $dir_fir . '" style="
                width:200px;
                object-fit; contain;">
                ';
        }
    }
    //MARK: Calculo
    //Paso 1: determinar la CTS aplicable:
    $rcts = cts_aplicable($id_anio,$id_rubr, $id_empresa, $oIfx);
    $rcts_est_trun = es_trunca($rcts['rcts_fec_inic'],$rcts['rcts_fec_fina'],$id_empl,$id_empresa,$oIfx);

    if($rcts_est_trun != false){
        $rcts['rcts_fec_fina'] = $rcts_est_trun;
    }

    // Paso 2: determinar los valores variables aplicables

    $rcts_val_hhee = promedio_horas_extras($rcts['rcts_per_inic'], $rcts['rcts_per_fina'], $rcts['rcts_fec_fina'], $id_empl, $id_empresa, $oIfx);
    $rcts_val_comi = promedio_comisiones($rcts['rcts_per_inic'], $rcts['rcts_per_fina'], $rcts['rcts_fec_fina'], $id_empl, $id_empresa, $oIfx);

    // paso 3: determinar el valor de la ultima gratificacion

    $rcts_val_rgrt = valor_ultima_gratificacion(
                                        $rcts['rcts_per_inic'], 
                                        $rcts['rcts_per_fina'], 
                                        $rcts['rcts_cod_rubr'], 
                                        $rcts['rcts_fec_fina'],
                                        $id_empl, 
                                        $id_empresa, 
                                        $oIfx
                                    );
    $rcts_dia_trab = calcular_intervalo_trabajado($rcts['rcts_per_inic'], $rcts['rcts_fec_fina'], $id_empl, $id_empresa, $oIfx);

    $sql = "SELECT pago_val_pago FROM saepago WHERE pago_cod_rubr= 'RASFM' AND pago_cod_empl = '{$id_empl}' and pago_cod_empr = '{$id_empresa}' AND pago_per_pago = '{$rcts['rcts_per_fina']}'";
    $RASFM_val_pago =  consulta_string($sql, 'pago_val_pago', $oIfx, '');

    $remuneracion_html = "";
    $remuneracion_total = 0;
    $remuneracion = [
        "Basico" => $empl_val_sala,
        "Asignación Familiar" => $RASFM_val_pago,
        "Promedio de HHEE" => $rcts_val_hhee,
        "Promedio de Comisiones" => $rcts_val_comi,
        "Sexto de Gratificación" => $rcts_val_rgrt/6
    ];

    foreach ($remuneracion as $key => $value) {
        $remuneracion_total += $value;
        $remuneracion_html .= '
            <tr>
                <td width="144" align="right" style="padding-right: 16px;">-</td>
                <td width="192">'.$key.'</td>
                <td width="128" align="center">&nbsp;</td>
                <td width="32">S/.</td>
                <td align="right">'.(($value > 0) ?  number_format($value, 2, '.', ',') : 0).'</td>
                <td width="32">&nbsp;</td>
                <td align="right">&nbsp;</td>
            </tr>
        ';
    }

    
    $calculo_html = "";
    $calculo_total = calcular_remuneracion_precalculada($remuneracion, $rcts,$id_empresa, $id_empl, $oIfx);
    //$calculo_total = 0;
    
    $calculo = [
        "Por los meses" => [
            "periodo" => ((int)($rcts_dia_trab/30)),
            "valor" => (((int)($rcts_dia_trab/30))*30)*($calculo_total/$rcts_dia_trab)
        ],
        "Por los dias" => [
            "periodo" => $rcts_dia_trab%30,
            "valor" => ($rcts_dia_trab%30)*($calculo_total/$rcts_dia_trab)
        ]
    ];

    foreach ($calculo as $key => $value) {
        //$calculo_total += $value['valor'];
        $calculo_html .= '
            <tr>
                <td width="144" align="right" style="padding-right: 16px;">-</td>
                <td width="192">'.$key.'</td>
                <td width="128" align="center">'. number_format($value['periodo'], 2, '.', ',') .'</td>
                <td width="32">&nbsp;</td>
                <td align="right">&nbsp;</td>
                <td width="32">S/.</td>
                <td align="right">'. number_format($value['valor'], 2, '.', ',') .'</td>
            </tr>
        ';
    }

    $V = new EnLetras();
    $calculo_letras = strtoupper($V->ValorEnLetras($calculo_total, 'SOLES'));

    $mes_nombre = [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre'
    ];
    $mes_nombre = $mes_nombre[date('m')];

    $documento='
        <div style="margin-left: 64px; margin-right: 32px;">
            <style>
                tr {
                    font-size: 14px;
                }
                p {
                    font-size: 16px;
                    line-height: 1.6;
                }
                h1 {
                    font-size: 20px;
                }
                h2 {
                    font-size: 16px;
                }
                
            </style>
            <div>
                '.$logo.'
            </div>
            <br/>
            <h1>LIQUIDACIÓN DEL DEPOSITO SEMESTRAL DE CTS</h1>
            <br/>
            <table cellspacing="4"> 
                <tr>
                    <td colspan="6"><div style="font-weight: bold;font-size: 14px;padding: 8px">1. IDENTIFICACIÓN DE LAS PARTES</div></td>
                </tr>
                <tr>
                    <td colspan="6"><div style="font-weight: bold;font-size: 14px;padding: 8px;padding-left: 64px;">1.1. Datos del empleador:</div></td>
                </tr>
                <tr>
                    <td width="144" align="right" style="padding-right: 16px;">-</td>
                    <td width="256">Nombre o razón social</td>
                    <td width="32" align="center">:</td>
                    <td>'.$empr_nom.'</td>
                </tr>
                <tr>
                    <td width="144" align="right" style="padding-right: 16px;">-</td>
                    <td width="256">RUC</td>
                    <td width="32" align="center">:</td>
                    <td>'.$empr_ruc.'</td>
                </tr>
                <tr>
                    <td width="144" align="right" style="padding-right: 16px;">-</td>
                    <td width="256">Dirección</td>
                    <td width="32" align="center">:</td>
                    <td>'.$empr_dir.'</td>
                </tr>
                <tr>
                    <td width="144" align="right" style="padding-right: 16px;">-</td>
                    <td width="256">Representante legal</td>
                    <td width="32" align="center">:</td>
                    <td>'.$empr_repres.'</td>
                </tr>
                <tr>
                    <td width="144" align="right" style="padding-right: 16px;">-</td>
                    <td width="256">D.N.I</td>
                    <td width="32" align="center">:</td>
                    <td>'.$empr_ced_repr.'</td>
                </tr>
                <tr>
                    <td colspan="6"><div style="font-weight: bold;font-size: 14px;padding: 8px;padding-left: 64px;">1.2. Datos del trabajador</div></td>
                </tr>
                <tr>
                    <td width="144" align="right" style="padding-right: 16px;">-</td>
                    <td width="256">Apellidos y Nombres</td>
                    <td width="32" align="center">:</td>
                    <td>'.$empl_ape.' '.$empl_nom.'</td>
                </tr>
                <tr>
                    <td width="144" align="right" style="padding-right: 16px;">-</td>
                    <td width="256">D.N.I</td>
                    <td width="32" align="center">:</td>
                    <td>'.$id_empl.'</td>
                </tr>
                <tr>
                    <td colspan="6"><div style="font-weight: bold;font-size: 14px;padding: 8px">2. ENTIDAD DEPOSITARIA</div></td>
                </tr>
                <tr>
                    <td width="144" align="right" style="padding-right: 16px;">-</td>
                    <td width="256">Banco</td>
                    <td width="32" align="center">:</td>
                    <td>'.$empl_cod_banc.'</td>
                </tr>
                <tr>
                    <td width="144" align="right" style="padding-right: 16px;">-</td>
                    <td width="256">N° de Cuenta</td>
                    <td width="32" align="center">:</td>
                    <td>'.$empl_num_ctas.'</td>
                </tr>
                <tr>
                    <td colspan="6"><div style="font-weight: bold;font-size: 14px;padding: 8px">3. PERIODO QUE SE LIQUIDA</div></td>
                </tr>
                <tr>
                    <td colspan="6" align="center">Del '. $rcts['rcts_fec_inic']->format('d/m/Y') .' al '. $rcts['rcts_fec_fina']->format('d/m/Y') .'</td>
                </tr>
                <tr>
                    <td colspan="6">La presente constancia del depósito de Compensación por Tiempo de Servicios realizado el '. (new DateTime())->format('d/m/Y') .' por los siguientes montos:</td>
                </tr>
            </table>
            <table cellspacing="4"> 
                <tr>
                    <td colspan="7"><div style="font-weight: bold;font-size: 14px;padding: 8px">4. REMUNERACIÓN COMPUTABLE</div></td>
                </tr>
                '.$remuneracion_html.'
                <tr>
                    <td width="144" align="right" style="padding-right: 16px;">-</td>
                    <td width="192">Total remuneración</td>
                    <td width="128" align="center">&nbsp;</td>
                    <td width="32" style="border-top: 1px solid black;font-weight: bold;">S/.</td>
                    <td align="right" style="border-top: 1px solid black;font-weight: bold;">'.number_format($remuneracion_total, 2, '.', ',').'</td>
                    <td width="32">&nbsp;</td>
                    <td align="right">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="7"><div style="font-weight: bold;font-size: 14px;padding: 8px">5. CÁLCULO</div></td>
                </tr>
                '.$calculo_html.'
                <tr>
                    <td width="144" align="right" style="padding-right: 16px;">-</td>
                    <td width="192">Total Monto</td>
                    <td width="128" align="center">&nbsp;</td>
                    <td width="32">&nbsp;</td>
                    <td align="right">&nbsp;</td>
                    <td width="32" style="border-top: 1px solid black;font-weight: bold;">S/.</td>
                    <td style="border-top: 1px solid black;font-weight: bold;" align="right" >'. number_format($calculo_total, 2, '.', ',') .'</td>
                </tr>
                <tr>
                    <td colspan="7"><div style="font-size: 14px;padding: 8px"><b>MONTO DEPOSITADO,</b> '.$calculo_letras.'</div></td>
                </tr>
            </table>
            
            <p align="justify">Ate, ' . date('d') . ' de '.$mes_nombre.' del '. date('Y') .'</p>
            <br/>
            <table style="width: 100%;" cellspacing="4">
                <tr>
                <td align="center" style="width: 50%;">&nbsp;</td>
                <td align="center" style="width: 50%;height: 100px;">'.$firma_img.'</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><hr/></td>
                </tr>
                <tr>
                    <td align="center">&nbsp;</td>
                    <td align="center">'.$empl_nom.' '.$empl_ape.'</td>
                </tr>
                <tr>
                    <td align="center">&nbsp;</td>
                    <td align="center">DNI N° '.$id_empl.'</td>
                </tr>
            </table>
        </div>
    ';

    
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
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

    $pdf->writeHTMLCell(0, 0, '', '',$documento, 0, 1, 0, true, '', true);

    $ruta = DIR_FACTELEC . 'Include/archivos/' . $nombre_archivo . '.pdf';


    $pdf->Output($ruta, 'F');
    return $documento;
    
}

//determinar la CTS aplicable:
//return: 'RCTSA' = CTS Abril | 'RCTSN' CTS Noviembre
function cts_aplicable($rcts_ani_fina, $rcts_cod_rubr, $empr_cod, $oIfx){
    $sql = "SELECT 
                pnom_cod_rubr,
                pnom_cod_pnom,
                pnom_mes_inic,
                pnom_mes_fina,
                pnom_dia_inic,
                pnom_dia_fina
            FROM saepnom
            WHERE 
                pnom_cod_empr = '{$empr_cod}' AND
                pnom_cod_rubr = '{$rcts_cod_rubr}'
            ;";
    $oIfx->Query($sql);

    $pnom_mes_inic = $oIfx->f("pnom_mes_inic");
    $pnom_mes_fina = $oIfx->f("pnom_mes_fina");
    $pnom_dia_fina = $oIfx->f("pnom_dia_fina");
    $pnom_dia_inic = $oIfx->f("pnom_dia_inic");

    if($pnom_mes_fina < $pnom_mes_inic){
        $rcts_ani_inic = ((int)$rcts_ani_fina) -1;
        $pnom_per_inic = $rcts_ani_inic.str_pad($pnom_mes_inic, 2, '0', STR_PAD_LEFT);
        $pnom_per_fina = $rcts_ani_fina.str_pad($pnom_mes_fina, 2, '0', STR_PAD_LEFT);
    }else{
        $rcts_ani_inic = $rcts_ani_fina;
        $pnom_per_inic = $rcts_ani_inic.str_pad($pnom_mes_inic, 2, '0', STR_PAD_LEFT);
        $pnom_per_fina = $rcts_ani_fina.str_pad($pnom_mes_fina, 2, '0', STR_PAD_LEFT);
    }
    $rcts_fec_fina = new DateTime($rcts_ani_fina.'-'.str_pad($pnom_mes_fina, 2, '0', STR_PAD_LEFT).'-'.str_pad($pnom_dia_fina, 2, '0', STR_PAD_LEFT));
    $rcts_fec_inic = new DateTime($rcts_ani_inic.'-'.str_pad($pnom_mes_inic, 2, '0', STR_PAD_LEFT).'-'.str_pad($pnom_dia_inic, 2, '0', STR_PAD_LEFT));

    $result = [
        'rcts_cod_rubr' => $oIfx->f("pnom_cod_rubr"),
        'rcts_cod_pnom' => $oIfx->f("pnom_cod_pnom"),
        'rcts_per_inic' => $pnom_per_inic,
        'rcts_per_fina' => $pnom_per_fina,
        'rcts_fec_fina' => $rcts_fec_fina,
        'rcts_fec_inic' => $rcts_fec_inic
    ];
    $oIfx->Free();
    return $result;
}

function es_trunca($prov_fec_inic, $prov_fec_fina, $empl_cod, $empr_cod, $oIfx){
    $sql = "SELECT 
                (esem_fec_sali BETWEEN '{$prov_fec_inic->format('Y-m-d')}' AND '{$prov_fec_fina->format('Y-m-d')}') AS prov_est_trun,
                esem_fec_sali
            FROM saeesem 
            WHERE 
                esem_cod_empr='{$empr_cod}' AND 
                esem_cod_empl='{$empl_cod}'
            ;";
    $oIfx->Query($sql);
    $prov_est_trun = $oIfx->f("prov_est_trun");
    $esem_fec_sali = $oIfx->f("esem_fec_sali");
    $oIfx->Free();
    if($prov_est_trun == 1){
        return new DateTime($esem_fec_sali);
    }else{
        return false;
    }
}

function promedio_horas_extras($prov_per_inic, $prov_per_fina, $esem_fec_sali, $empl_cod, $empr_cod, $oIfx){
    $prov_fec_inic = DateTime::createFromFormat('Ymd', $prov_per_inic.'01');
    $interval_meses = ($esem_fec_sali->format('m') + 1) - $prov_fec_inic->format('m');

    $prov_per_fina = $esem_fec_sali->format('Ym');

    $base = 0;
    
    $sql = "SELECT count(*) FROM saepago WHERE pago_cod_empl = '$empl_cod' AND pago_cod_empr = $empr_cod AND pago_cod_rubr = 'RHECI' AND pago_ori_gene = 'R' AND pago_per_pago BETWEEN '$prov_per_inic' AND '$prov_per_fina'";
    $oIfx->Query($sql);
    $rheci_count = $oIfx->f("count");
    $oIfx->Free();

    if ($rheci_count >= 3){
        $sql = "SELECT sum(pago_val_pago) FROM saepago WHERE pago_cod_empl = '$empl_cod' AND pago_cod_empr = $empr_cod AND pago_cod_rubr = 'RHECI' AND pago_ori_gene = 'R' AND pago_per_pago BETWEEN '$prov_per_inic' AND '$prov_per_fina'";
        $oIfx->Query($sql);
        $rheci_sum = $oIfx->f("sum");
        $oIfx->Free();
        $base += $rheci_sum;
    }

    $sql = "SELECT count(*) FROM saepago WHERE pago_cod_empl = '$empl_cod' AND pago_cod_empr = $empr_cod AND pago_cod_rubr = 'RHEVC' AND pago_ori_gene = 'R' AND pago_per_pago BETWEEN '$prov_per_inic' AND '$prov_per_fina'";
    $oIfx->Query($sql);
    $rhevc_count = $oIfx->f("count");
    $oIfx->Free();

    if ($rhevc_count >= 3){
        $sql = "SELECT sum(pago_val_pago) FROM saepago WHERE pago_cod_empl = '$empl_cod' AND pago_cod_empr = $empr_cod AND pago_cod_rubr = 'RHEVC' AND pago_ori_gene = 'R' AND pago_per_pago BETWEEN '$prov_per_inic' AND '$prov_per_fina'";
        $oIfx->Query($sql);
        $rhevc_sum = $oIfx->f("sum");
        $oIfx->Free();
        $base += $rhevc_sum;
    }

    $sql = "SELECT count(*) FROM saepago WHERE pago_cod_empl = '$empl_cod' AND pago_cod_empr = $empr_cod AND pago_cod_rubr = 'RHECC' AND pago_ori_gene = 'R' AND pago_per_pago BETWEEN '$prov_per_inic' AND '$prov_per_fina'";
    $oIfx->Query($sql);
    $rhecc_count = $oIfx->f("count");
    $oIfx->Free();

    if ($rhecc_count >= 3){
        $sql = "SELECT sum(pago_val_pago) FROM saepago WHERE pago_cod_empl = '$empl_cod' AND pago_cod_empr = $empr_cod AND pago_cod_rubr = 'RHECC' AND pago_ori_gene = 'R' AND pago_per_pago BETWEEN '$prov_per_inic' AND '$prov_per_fina'";
        $oIfx->Query($sql);
        $rhecc_sum = $oIfx->f("sum");
        $oIfx->Free();
        $base += $rhecc_sum;
    }

    $promedio = $base / $interval_meses;
    if($promedio !== NAN ){
        return $promedio;
    }else{
        return 0;
    }
}

function promedio_comisiones($prov_per_inic, $prov_per_fina, $esem_fec_sali, $empl_cod, $empr_cod, $oIfx){
    $prov_fec_inic = DateTime::createFromFormat('Ymd', $prov_per_inic.'01');
    $interval_meses = ($esem_fec_sali->format('m') + 1) - $prov_fec_inic->format('m');
    

    $prov_per_fina = $esem_fec_sali->format('Ym');

    $base = 0;

    $sql = "SELECT count(*) FROM saepago WHERE pago_cod_empl = '$empl_cod' AND pago_cod_empr = $empr_cod AND pago_cod_rubr = 'RCOMI' AND pago_ori_gene = 'R' AND pago_per_pago BETWEEN '$prov_per_inic' AND '$prov_per_fina'";
    $oIfx->Query($sql);
    $rcomi_count = $oIfx->f("count");
    $oIfx->Free();
    if ($rcomi_count >= 3){
        $sql = "SELECT sum(pago_val_pago) FROM saepago WHERE pago_cod_empl = '$empl_cod' AND pago_cod_empr = $empr_cod AND pago_cod_rubr = 'RCOMI' AND pago_ori_gene = 'R' AND pago_per_pago BETWEEN '$prov_per_inic' AND '$prov_per_fina'";
        $oIfx->Query($sql);
        $rcomi_sum = $oIfx->f("sum");
        $oIfx->Free();
        $base += $rcomi_sum;
    }
    

    $promedio = $base / $interval_meses;
    if($promedio !== NAN ){
        return $promedio;
    }else{
        return 0;
    }
}


function valor_ultima_gratificacion($rcts_per_inic, $rcts_per_fina, $rcts_cod_rubr, $esem_fec_sali, $empl_cod, $empr_cod, $oIfx){
    $rcts_ani_fina = substr($rcts_per_fina, 0, 4);
    $sql = "SELECT
                pnom_cod_rubr,
                pnom_cod_pnom,
                pnom_mes_inic,
                pnom_mes_fina
            FROM saepnom
            WHERE
                pnom_cod_empr = '{$empr_cod}' AND
                pnom_cod_rubr LIKE 'RGR%'
            ;";

    $oIfx->Query($sql);
    $rcts_per_fina = ($rcts_per_fina <= $esem_fec_sali->format('Ym')) ? $rcts_per_fina : $esem_fec_sali->format('Ym');
    $rcts_mes_fina = substr($rcts_per_fina,-2);
    //$rcts_mes_fina = 5;
    
    $record = 100;
    $rgrt_cod_pnom = null;
    $rgrt_cod_rubr = null;
    $rgrt_mes_fina = null;
    $rgrt_mes_inic = null;
    $rgrt_ani_fina = null;
    $rgrt_ani_inic = null;
    do{
        $_rgrt_cod_pnom = $oIfx->f("pnom_cod_pnom");
        $_rgrt_mes_fina = $oIfx->f("pnom_mes_fina");
        $_rgrt_cod_rubr = $oIfx->f("pnom_cod_rubr");
        if ($rcts_mes_fina < $_rgrt_mes_fina){
            $_record = ($rcts_mes_fina + 12) - $_rgrt_mes_fina;
            if ($_record < $record){
                $record = $_record;
                $rgrt_cod_pnom = $_rgrt_cod_pnom;
                $rgrt_cod_rubr = $_rgrt_cod_rubr;
                $rgrt_mes_fina = str_pad($_rgrt_mes_fina, 2, '0', STR_PAD_LEFT);
                $rgrt_mes_inic = str_pad($oIfx->f("pnom_mes_inic"), 2, '0', STR_PAD_LEFT);
                $rgrt_ani_fina = $rcts_ani_fina - 1;
                $rgrt_ani_inic = ($rgrt_mes_inic < $rgrt_mes_fina) ? $rgrt_ani_fina : $rgrt_ani_fina -1;
            }
        }else{
            $_record = $rcts_mes_fina - $_rgrt_mes_fina;
            if ($_record < $record){
                $record = $_record;
                $rgrt_cod_pnom = $_rgrt_cod_pnom;
                $rgrt_cod_rubr = $_rgrt_cod_rubr;
                $rgrt_mes_fina = str_pad($_rgrt_mes_fina, 2, '0', STR_PAD_LEFT);
                $rgrt_mes_inic = str_pad($oIfx->f("pnom_mes_inic"), 2, '0', STR_PAD_LEFT);
                $rgrt_ani_fina = $rcts_ani_fina;
                $rgrt_ani_inic = ($rgrt_mes_inic < $rgrt_mes_fina) ? $rgrt_ani_fina : $rgrt_ani_fina -1;
            }
        }

    }while($oIfx->SiguienteRegistro());

    $oIfx->Free();

    $sql = "SELECT
                SUM(pemp_val_mese) as rgrt_val_ulti
            FROM saepemp
            WHERE
                pemp_cod_pnom = '{$rgrt_cod_pnom}' AND
                pemp_cod_empl = '{$empl_cod}' AND
                pemp_per_pemp BETWEEN '{$rgrt_ani_inic}{$rgrt_mes_inic}' AND '{$rgrt_ani_fina}{$rgrt_mes_fina}'
            ;";
    $oIfx->Query($sql);
    $rgrt_val_ulti = $oIfx->f("rgrt_val_ulti");
    $oIfx->Free();

    return $rgrt_val_ulti;
}

function calcular_intervalo_trabajado($prov_per_inic, $esem_fec_sali, $empl_cod, $empr_cod, $oIfx){
    $sql = "SELECT
                SUM(amem_hor_trab / 8 ) AS empl_dia_trab
            FROM
                saeamem
            WHERE
                amem_cod_empr = '{$empr_cod}' AND 
                amem_cod_empl = '{$empl_cod}' AND 
                amem_fec_amem BETWEEN TO_DATE(CAST('{$prov_per_inic}' AS VARCHAR), 'YYYYMM') AND CAST('{$esem_fec_sali->format('Y-m-d')}' AS DATE)
            ;";
    $oIfx->Query($sql);
    $empl_dia_trab = $oIfx->f("empl_dia_trab");
    $oIfx->Free();

    return  $empl_dia_trab;
}

function calcular_remuneracion_precalculada($remuneracion,$rcts,$empr_cod,$cod_empl,$oIfx){
    $sql = "SELECT
                SUM(pemp_val_paga) as pemp_val_paga
            FROM saepemp
            WHERE
                pemp_cod_empl = '{$cod_empl}' AND
                pemp_cod_empr = '{$empr_cod}' AND
                pemp_cod_pnom = '{$rcts["rcts_cod_pnom"]}' AND
                pemp_per_pemp BETWEEN '{$rcts["rcts_per_inic"]}' AND '{$rcts["rcts_per_fina"]}'
            ;";
    $oIfx->Query($sql);
    $pemp_val_paga = $oIfx->f("pemp_val_paga");
    $oIfx->Free();


    return (float)(
        $pemp_val_paga+
        $remuneracion['Promedio de HHEE']+
        $remuneracion['Promedio de Comisiones']+
        $remuneracion['Sexto de Gratificación']
    );
}