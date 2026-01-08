<?php

function finiquito_liquidacion_peru($id_empresa = 0, $id_anio = 0, $id_mes = 0, $id_empl = '', $id_depar = 0, $id_proyecto = 0, &$rutaPdf = '')
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

    $oReturn = new xajaxResponse();
    $lon = strlen($id_mes);
    if ($lon == 1) {
        $id_mes = '0' . $id_mes;
    }
    //anio - mes
    $perido = $id_anio . $id_mes;
    //ulitmo dia mes
    $ultimo_dia = date("d", (mktime(0, 0, 0, $id_mes + 1, 1, $id_anio) - 1));

    //$fecha_liqui=$id_anio.'-'.$id_mes.'-'.$ultimo_dia.' 00:00:00';
    $nombre_archivo = 'FINIQUITO_LIQUIDACION_CO_' . $id_anio . '_' . $id_mes . '_' . $id_empl;
    $sql = "select * from saeempr where empr_cod_empr='$id_empresa'";

    $ciudad = consulta_string($sql, 'empr_cod_ciud', $oIfx, '');
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
    $empresa = consulta_string($sql, 'empr_nom_empr', $oIfx, '');
    $empr_ruc = consulta_string($sql, 'empr_ruc_empr', $oIfx, '');
    $empr_dir = consulta_string($sql, 'empr_dir_empr', $oIfx, '');
    $empr_ced_repr = consulta_string($sql, 'empr_ced_repr', $oIfx, '');

    $sql = "select * from saeciud where ciud_cod_ciud='$ciudad'";
    $ciudad = consulta_string($sql, 'ciud_nom_ciud', $oIfx, '');
    $sql = "select * from saeempl where empl_cod_empl='$id_empl'";
    //echo $sql;exit;
    $empl_ape = consulta_string($sql, 'empl_ape_empl', $oIfx, '');
    $empl_nom = consulta_string($sql, 'empl_nom_empl', $oIfx, '');
    $empl_cod_pdie = consulta_string($sql, 'empl_cod_pdie', $oIfx, '');
    $empl_val_sala = consulta_string($sql, 'empl_sal_sala', $oIfx, '');
    $empl_cod_nove = consulta_string($sql, 'empl_cod_nove', $oIfx, '');
    $empl_fir_empl = consulta_string($sql, 'empl_fir_empl', $oIfx, '');
    if ($empl_cod_nove == '') {
        $empl_cod_nove = 'V';
    }
    $sql = "SELECT  nove_des_nove FROM saelnove where nove_ini_nove='$empl_cod_nove'";
    $empl_cod_nove = consulta_string($sql, 'nove_des_nove', $oIfx, '');
    //echo $sql;exit;
    $sql = "select * from saeesem where esem_cod_empr='$id_empresa' and DATE_PART('year',esem_fec_sali)=$id_anio and DATE_PART('MONTH',esem_fec_sali)='$id_mes' and esem_cod_empl='$id_empl'";
    $esem_fec_sali = consulta_string($sql, 'esem_fec_sali', $oIfx, '');
    //echo $sql;exit;
    $array_f = explode('-', $esem_fec_sali);
    $anio_sali = $array_f[0];
    $mes_sali = $array_f[1];
    $dia_sali = $array_f[2];
    $esem_fec_ingr = consulta_string($sql, 'esem_fec_ingr', $oIfx, '');
    $esem_cod_estr = consulta_string($sql, 'esem_cod_estr', $oIfx, '');
    $sql = "select * from saeestr where estr_cod_estr='$esem_cod_estr' and estr_cod_empr='$id_empresa'";
    $estr_des_estr = consulta_string($sql, 'estr_des_estr', $oIfx, '');
    $fecha_liqui = $anio_sali . '-' . $mes_sali . '-' . $dia_sali . ' 00:00:00';

    $date1 = new DateTime($esem_fec_ingr);
    $date2 = new DateTime($esem_fec_sali);
    $interval = $date1->diff($date2);

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
    $rcts = cts_aplicable($esem_fec_sali, $id_empresa, $oIfx);
    //Paso 2: determinar la gratificacion aplicable:
    $rgrt = gratificacion_aplicable($esem_fec_sali, $id_empresa, $oIfx);
    
    //Paso 2.1: determinar el periodo de calculo de vacaciones
    $vaca = calcular_periodo_vacaciones(new DateTime($esem_fec_ingr), new DateTime($esem_fec_sali));
    // Paso 3: determinar los valores variables aplicables
    // paso 3.1: determinar el promedio de horas extra
    $rcts_val_hhee = promedio_horas_extras($rcts['rcts_per_inic'], $rcts['rcts_per_fina'], new DateTime($esem_fec_sali), $id_empl, $id_empresa, $oIfx);
    $rgrt_val_hhee = promedio_horas_extras($rgrt['rgrt_per_inic'], $rgrt['rgrt_per_fina'], new DateTime($esem_fec_sali), $id_empl, $id_empresa, $oIfx);
    $vaca_val_hhee = promedio_horas_extras($vaca['vaca_per_inic'], $vaca['vaca_per_fina'], new DateTime($esem_fec_sali), $id_empl, $id_empresa, $oIfx);
    // paso 3.2: detrminar el valor de las comisiones
    $rcts_val_comi = promedio_comisiones($rcts['rcts_per_inic'], $rcts['rcts_per_fina'], new DateTime($esem_fec_sali), $id_empl, $id_empresa, $oIfx);
    $rgrt_val_comi = promedio_comisiones($rgrt['rgrt_per_inic'], $rgrt['rgrt_per_fina'], new DateTime($esem_fec_sali), $id_empl, $id_empresa, $oIfx);
    $vaca_val_comi = promedio_comisiones($vaca['vaca_per_inic'], $vaca['vaca_per_fina'], new DateTime($esem_fec_sali), $id_empl, $id_empresa, $oIfx);
    // paso 4: determinar el pago de gratificacion anterior
    $rcts_val_rgrt = valor_ultima_gratificacion($rcts['rcts_per_inic'], $rcts['rcts_per_fina'], $rcts['rcts_cod_rubr'], new DateTime($esem_fec_sali), $id_empl, $id_empresa, $oIfx);
    // paso 5: determinar el timpo trunco para cada  provision
    $rcts_dia_trun = calcular_intervalo_trunco($rcts['rcts_per_inic'], new DateTime($esem_fec_sali), $id_empl, $id_empresa, $oIfx);
    $rgrt_dia_trun = calcular_intervalo_trunco($rgrt['rgrt_per_inic'], new DateTime($esem_fec_sali), $id_empl, $id_empresa, $oIfx);
    $rgrt_dia_trun['empl_dia_trab'] = (calcular_intervalo_gratificaciones_trunco($rgrt, new DateTime($esem_fec_sali), $id_empl, $id_empresa, $oIfx))*30;


    $vaca_dia_trun = calcular_vacaciones(new DateTime($esem_fec_sali), new DateTime($esem_fec_ingr), $id_empl, $id_empresa, $oIfx);

    $sql = "SELECT pago_val_pago FROM saepago WHERE pago_cod_rubr= 'RASFM' AND pago_cod_empl = '{$id_empl}' and pago_cod_empr = '{$id_empresa}' AND pago_per_pago = '{$perido}'";
    $RASFM_val_pago =  consulta_string($sql, 'pago_val_pago', $oIfx, '');
    $remuneraciones = [
        [
            'concepto' => 'SUELDO',
            'cts' => $empl_val_sala,
            'vacaciones' => $empl_val_sala,
            'gratificacion' => $empl_val_sala
        ],
        [
            'concepto' => 'ASIGNACION FAMILIAR',
            'cts' => $RASFM_val_pago,
            'vacaciones' => $RASFM_val_pago,
            'gratificacion' => $RASFM_val_pago
        ],
        [
            'concepto' => 'PROMEDIO DE COMISIONES',
            'cts' => $rcts_val_comi,
            'vacaciones' => $vaca_val_comi,
            'gratificacion' => $rgrt_val_comi
        ],
        [
            'concepto' => 'PROMEDIO DE HHEE',
            'cts' => $rcts_val_hhee,
            'vacaciones' => $rcts_val_hhee,
            'gratificacion' => $rgrt_val_hhee
        ],
        [
            'concepto' => 'SEXTO GRATIFICACION',
            'cts' => $rcts_val_rgrt/6,
            'vacaciones' => 0,
            'gratificacion' => 0
        ]
    ];

    
    $remuneraciones_html = "";

    $remuneraciones_total = [
        'cts' => 0,
        'vacaciones' => 0,
        'gratificacion' => 0
    ];

    foreach ($remuneraciones as $value) {
        $remuneraciones_total['cts'] += $value['cts'];
        $remuneraciones_total['vacaciones'] += $value['vacaciones'];
        $remuneraciones_total['gratificacion'] += $value['gratificacion'];
        $remuneraciones_html .= '
            <tr>
                <td width="256"><b>' . $value['concepto'] . '</b></td>
                <td width="32" align="center">:</td>
                <td width="208" align="center">' . (($value['cts'] > 0) ? number_format($value['cts'], 2) : ('-')) . '</td>
                <td width="208" align="center">' . (($value['vacaciones'] > 0) ? number_format($value['vacaciones'], 2) : ('-')) . '</td>
                <td width="208" align="center">' . (($value['gratificacion'] > 0) ? number_format($value['gratificacion'], 2) : ('-')) . '</td>
            </tr>
        ';
    }


    // paso 6: determinar el valor de la provision
    $prov_val_trun = [
        'rcts_val_trab' => ($remuneraciones_total['cts']/360) * $rcts_dia_trun['empl_dia_trab'],
        'rcts_val_falt' => -1*(($remuneraciones_total['cts']/360) * $rcts_dia_trun['empl_dia_falt']),
        'rgrt_val_trab' => ($remuneraciones_total['gratificacion']/360) * $rgrt_dia_trun['empl_dia_trab'],
        'rgrt_val_falt' => -1*(($remuneraciones_total['gratificacion']/360) * $rgrt_dia_trun['empl_dia_falt']),
        'rgrt_val_boni' => (($remuneraciones_total['gratificacion']/360) * $rgrt_dia_trun['empl_dia_trab'])*0.09,
        'vaca_val_trun' => (($remuneraciones_total['vacaciones']/30) * $vaca_dia_trun['empl_vac_trun']),
        'vaca_val_pend' => (($remuneraciones_total['vacaciones']/30) * $vaca_dia_trun['empl_vac_pend']),
        'vaca_val_toma' => (($remuneraciones_total['vacaciones']/30) * $vaca_dia_trun['empl_vac_toma'])
    ];
    $rcts_val_tota = $prov_val_trun['rcts_val_trab'] + $prov_val_trun['rcts_val_falt'];
    $rgrt_val_tota = $prov_val_trun['rgrt_val_trab'] + $prov_val_trun['rgrt_val_falt'] + $prov_val_trun['rgrt_val_boni'];
    $vaca_val_tota = $prov_val_trun['vaca_val_trun'] + $prov_val_trun['vaca_val_pend'] + $prov_val_trun['vaca_val_toma'];

    $prov_val_tota = 0;
    foreach ($prov_val_trun as $key => $value) {
        $prov_val_tota += $value;
    }

    $vafp_por_porc = valor_vafp(new DateTime($esem_fec_sali),$empl_cod_pdie,$id_empresa, $oIfx);
    $vafp_val_porc = $vaca_val_tota*($vafp_por_porc*0.01);


    $prov_des_tota = $vafp_val_porc;

    $prov_pag_tota = $prov_val_tota - $prov_des_tota;


    $essa_val_pago = valor_essalud(new DateTime($esem_fec_sali), $id_empl, $id_empresa, $oIfx);
    $peps_por_pago = valor_eps($id_empl, $id_empresa, $oIfx);
    $peps_val_pago = $vaca_val_tota*($peps_por_pago*0.01);
    $empr_val_tota = $essa_val_pago;

    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetras($prov_pag_tota, 'Soles'));

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
                td {
                    font-size: 14px;
                }

                p {
                    font-size: 14px;
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
            <br />
            <h1 align="center">LIQUIDACIÓN DE BENEFICIOS SOCIALES</h1>
            <br />
            <table cellspacing="4">
                <tr>
                    <td width="256"><b>NOMBRE</b></td>
                    <td width="32" align="center">:</td>
                    <td width="256">'.$empl_ape.' '.$empl_nom.'</td>
                </tr>
                <tr>
                    <td width="256"><b>DOC.DE IDENTIDAD</b></td>
                    <td width="32" align="center">:</td>
                    <td colspan="3" width="256">'.$id_empl.'</td>
                </tr>
                <tr>
                    <td width="256"><b>CARGO</b></td>
                    <td width="32" align="center">:</td>
                    <td colspan="3" width="256">'.$estr_des_estr.'</td>
                </tr>
                <tr>
                    <td width="256"><b>FECHA INGRESO</b></td>
                    <td width="32" align="center">:</td>
                    <td colspan="3" width="256">'.$esem_fec_ingr.'</td>
                </tr>
                <tr>
                    <td width="256"><b>FECHA DE CESE</b></td>
                    <td width="32" align="center">:</td>
                    <td colspan="3" width="256">'.$esem_fec_sali.'</td>
                </tr>
                
                <tr>
                    <td width="256"><b>TIEMPO DE SERVICIO</b></td>
                    <td width="32" align="center">:</td>
                    <td colspan="3" width="256">'.
                        $interval->y.' '.(($interval->y > 1) ? 'AÑOS' : 'AÑO').' '.
                        $interval->m.' '.(($interval->m > 1) ? 'MESES' : 'MES').' '.
                        ($interval->d + 1).' '.((($interval->d+1) > 1) ? 'DIAS' : 'DIA').
                    '</td>
                </tr>
                <tr>
                    <td width="256"><b>MOTIVO DE CESE</b></td>
                    <td width="32" align="center">:</td>
                    <td colspan="3" width="256">'.$empl_cod_nove.'</td>
                </tr>
            </table>
            <table cellspacing="4">
                <tr>
                    <td style="border-top: 1px solid black;border-bottom: 1px solid black;" width="256"><b>REMUNERACIONES COMPUTABLES</b></td>
                    <td style="border-top: 1px solid black;border-bottom: 1px solid black;" width="32" align="center">&nbsp;</td>
                    <td style="border-top: 1px solid black;border-bottom: 1px solid black;" width="208" align="center"><b>CTS</b></td>
                    <td style="border-top: 1px solid black;border-bottom: 1px solid black;" width="208" align="center"><b>VACACIONES</b></td>
                    <td style="border-top: 1px solid black;border-bottom: 1px solid black;" width="208" align="center"><b>GRATIFICACION</b></td>
                </tr>
                '.$remuneraciones_html.'
                <tr>
                    <td style="border-top: 1px solid black;border-bottom: 1px solid black;" width="256"><b>SUELDO COMPUTABLE</b></td>
                    <td style="border-top: 1px solid black;border-bottom: 1px solid black;" width="32" align="center">:</td>
                    <td style="border-top: 1px solid black;border-bottom: 1px solid black;" width="208" align="center"><b>' . number_format($remuneraciones_total['cts'], 2) . '</b></td>
                    <td style="border-top: 1px solid black;border-bottom: 1px solid black;" width="208" align="center"><b>' . number_format($remuneraciones_total['vacaciones'], 2) . '</b></td>
                    <td style="border-top: 1px solid black;border-bottom: 1px solid black;" width="208" align="center"><b>' . number_format($remuneraciones_total['gratificacion'], 2) . '</b></td>
                </tr>
            </table>
            <table cellspacing="4">
                <tr>
                    <td width="32" align="center">></td>
                    <td width="256"><b>CTS</b></td>
                    <td width="104" align="right">&nbsp;</td>
                    <td width="64" align="left">&nbsp;</td>
                    <td width="104" align="center">&nbsp;</td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">CTS TRUNCO</td>
                    <td width="104" align="right">'.number_format($rcts_dia_trun['empl_dia_trab'], 2, '.', ',').'</td>
                    <td width="64" align="left">DIAS</td>
                    <td width="104" align="center">'. number_format($prov_val_trun['rcts_val_trab'], 2, '.', ',') .'</td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">(-) DIAS POR FALTA INJUSTIFICADA</td>
                    <td width="104" align="right">'. number_format($rcts_dia_trun['empl_dia_falt'], 2, '.', ',') .'</td>
                    <td width="64" align="left">DIAS</td>
                    <td width="104" align="center">'. number_format($prov_val_trun['rcts_val_falt'], 2, '.', ',') .'</td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">&nbsp;</td>
                    <td width="104" align="right">&nbsp;</td>
                    <td width="64" align="left">&nbsp;</td>
                    <td width="104" align="center">&nbsp;</td>
                    <td width="168" align="center">&nbsp;</td>
                    <td width="64" align="center">&nbsp;</td>
                    <td align="right"><b>'. number_format($rcts_val_tota, 2, '.', ',') .'</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">></td>
                    <td width="256"><b>VACACIONES</b></td>
                    <td width="104" align="right">&nbsp;</td>
                    <td width="64" align="left">&nbsp;</td>
                    <td width="104" align="center">&nbsp;</td>
                    <td width="168" align="center">&nbsp;</td>
                    <td width="64" align="center">&nbsp;</td>
                    <td align="right">&nbsp;</td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">VACACIONES TRUNCAS</td>
                    <td width="104" align="right">'. number_format($vaca_dia_trun['empl_vac_trun'], 2, '.', ',') .'</td>
                    <td width="64" align="left">DIAS</td>
                    <td width="104" align="center">'. number_format($prov_val_trun['vaca_val_trun'], 2, '.', ',') .'</td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">VACACIONES TOMADAS</td>
                    <td width="104" align="right">'. number_format($vaca_dia_trun['empl_vac_toma'], 2, '.', ',') .'</td>
                    <td width="64" align="left">DIAS</td>
                    <td width="104" align="center">'. number_format($prov_val_trun['vaca_val_toma'], 2, '.', ',') .'</td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">VACACIONES PENDIENTES</td>
                    <td width="104" align="right">'. number_format($vaca_dia_trun['empl_vac_pend'], 2, '.', ',') .'</td>
                    <td width="64" align="left">DIAS</td>
                    <td width="104" align="center">'. number_format($prov_val_trun['vaca_val_pend'], 2, '.', ',') .'</td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">&nbsp;</td>
                    <td width="104" align="right">&nbsp;</td>
                    <td width="64" align="left">&nbsp;</td>
                    <td width="104" align="center">&nbsp;</td>
                    <td width="168" align="center">&nbsp;</td>
                    <td width="64" align="center">&nbsp;</td>
                    <td align="right"><b>'. number_format($vaca_val_tota, 2, '.', ',') .'</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">></td>
                    <td width="256"><b>GRATIFICACIONES</b></td>
                    <td width="104" align="right">&nbsp;</td>
                    <td width="64" align="left">&nbsp;</td>
                    <td width="104" align="center">&nbsp;</td>
                    <td width="168" align="center">&nbsp;</td>
                    <td width="64" align="center">&nbsp;</td>
                    <td align="right">&nbsp;</td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">GRATIFICACION TRUNCA</td>
                    <td width="104" align="right">'.number_format($rgrt_dia_trun['empl_dia_trab'], 2, '.', ',').'</td>
                    <td width="64" align="left">DIAS</td>
                    <td width="104" align="center">'. number_format($prov_val_trun['rgrt_val_trab'], 2, '.', ',') .'</td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">(-) DIAS POR FALTA INJUSTIFICADA</td>
                    <td width="104" align="right">'.number_format($rgrt_dia_trun['empl_dia_falt'], 2, '.', ',').'</td>
                    <td width="64" align="left">DIAS</td>
                    <td width="104" align="center">'. number_format($prov_val_trun['rgrt_val_falt'], 2, '.', ',') .'</td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">BONIF. EXTRAORDINARIA 9%</td>
                    <td width="104" align="right">&nbsp;</td>
                    <td width="64" align="left">&nbsp;</td>
                    <td width="104" align="center">'. number_format($prov_val_trun['rgrt_val_boni'], 2, '.', ',') .'</td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">&nbsp;</td>
                    <td width="104" align="right">&nbsp;</td>
                    <td width="64" align="left">&nbsp;</td>
                    <td width="104" align="center">&nbsp;</td>
                    <td width="168" align="center">&nbsp;</td>
                    <td width="64" align="center">&nbsp;</td>
                    <td align="right"><b>'. number_format($rgrt_val_tota, 2, '.', ',') .'</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">&nbsp;</td>
                    <td width="104" align="right">&nbsp;</td>
                    <td width="64" align="left">&nbsp;</td>
                    <td width="104" align="center">&nbsp;</td>
                    <td width="168" align="center"><b>TOTAL INGRESOS</b></td>
                    <td width="64" align="center"><b>S/</b></td>
                    <td align="right"><b>'. number_format($prov_val_tota, 2, '.', ',') .'</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">></td>
                    <td width="256"><b>APORTE AFP INTEGRA</b></td>
                    <td colspan="2" align="right"><b>'.number_format($vafp_por_porc, 2, '.', ',').'%</b></td>
                    <td width="104" align="center">'.number_format($vafp_val_porc, 2, '.', ',').'</td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">&nbsp;</td>
                    <td width="104" align="right">&nbsp;</td>
                    <td width="64" align="left">&nbsp;</td>
                    <td width="104" align="center">&nbsp;</td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>S/</b></td>
                    <td align="right"><b>'.number_format($vafp_val_porc, 2, '.', ',').'</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">&nbsp;</td>
                    <td width="104" align="right">&nbsp;</td>
                    <td width="64" align="left">&nbsp;</td>
                    <td width="104" align="center">&nbsp;</td>
                    <td width="168" align="center"><b>TOTAL DESCUENTOS</b></td>
                    <td width="64" align="center"><b>S/</b></td>
                    <td align="right"><b>'.number_format($prov_des_tota, 2, '.', ',').'</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">&nbsp;</td>
                    <td width="104" align="right">&nbsp;</td>
                    <td width="64" align="left">&nbsp;</td>
                    <td width="104" align="center">&nbsp;</td>
                    <td width="168" align="center"><b>TOTAL A PAGAR</b></td>
                    <td width="64" align="center"><b>S/</b></td>
                    <td align="right"><b>'.number_format($prov_pag_tota, 2, '.', ',').'</b></td>
                </tr>
                <tr>
                    <td colspan="8" align="center" style="border: 1px solid black;"><b>SON: '.$con_letra.'</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">></td>
                    <td width="256"><b>APORTACIONES DEL EMPLEADOR</b></td>
                    <td colspan="2" align="right"><b>&nbsp;</b></td>
                    <td width="104" align="center"><b>&nbsp;</b></td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="32" align="center"></td>
                    <td width="256">ESSALUD</td>
                    <td colspan="2" align="right"><b>&nbsp;</b></td>
                    <td width="104" align="center"><b>'. number_format($essa_val_pago, 2, '.', ',') .'</b></td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="32" align="center"><b>&nbsp;</b></td>
                    <td width="256">CREDITO EPS</td>
                    <td colspan="2" align="right"><b>'. number_format($peps_por_pago, 2, '.', ',') .'%</b></td>
                    <td width="104" align="center"><b>'. number_format($peps_val_pago, 2, '.', ',') .'</b></td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>&nbsp;</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">&nbsp;</td>
                    <td width="104" align="right">&nbsp;</td>
                    <td width="64" align="left">&nbsp;</td>
                    <td width="104" align="center">&nbsp;</td>
                    <td width="168" align="center"><b>&nbsp;</b></td>
                    <td width="64" align="center"><b>&nbsp;</b></td>
                    <td align="right"><b>'. number_format($empr_val_tota, 2, '.', ',') .'</b></td>
                </tr>
                <tr>
                    <td width="32" align="center">&nbsp;</td>
                    <td width="256">&nbsp;</td>
                    <td width="104" align="right">&nbsp;</td>
                    <td width="64" align="left">&nbsp;</td>
                    <td width="104" align="center">&nbsp;</td>
                    <td width="168" align="center"><b>TOTAL APORTES EMPLEADOR</b></td>
                    <td width="64" align="center"><b>S/</b></td>
                    <td align="right"><b>'. number_format($empr_val_tota, 2, '.', ',') .'</b></td>
                </tr>
            </table>
            <p align="justify"><b>DECLARO ESTAR CONFORME CON LA LIQUIDACIÓN DE MIS BENEFICIOS SOCIALES, NO TENIENDO NADA QUE RECLAMAR POSTERIORMENTE, DÁNDOSE POR CONCLUIDO EL VÍNCULO LABORAL DE MUTUO ACUERDO ENTRE AMBAS PARTES.</b></p>
            <p align="justify"><b>Ate, ' . date('d') . ' de '.$mes_nombre.' del '. date('Y') .'</b></p>
            <br/>
            <table style="width: 100%;" cellspacing="4">
                <tr>
                <td align="center" style="width: 50%;">&nbsp;</td>
                <td align="center" style="width: 50%;height: 100px;">'.$firma_img.'</td>
                </tr>
                <tr>
                    <td><hr/></td>
                    <td><hr/></td>
                </tr>
                <tr>
                    <td align="center">EMPRESA</td>
                    <td align="center">RECIBI CONFORME</td>
                </tr>
                <tr>
                    <td align="center">'.$empresa.'</td>
                    <td align="center">'.$empl_nom.' '.$empl_ape.'</td>
                </tr>
                <tr>
                    <td align="center">RUC N° '.$empr_ruc.'</td>
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
function cts_aplicable($fecha, $empr_cod, $oIfx){
    $mes = date('m', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));
    $sql = "SELECT 
                pnom_cod_rubr,
                pnom_cod_pnom,
                pnom_mes_inic,
                pnom_mes_fina
            FROM saepnom
            WHERE 
                pnom_cod_empr = '{$empr_cod}' AND
                pnom_cod_rubr LIKE 'RCTS%' AND
                (pnom_mes_inic <= pnom_mes_fina AND '{$mes}' BETWEEN pnom_mes_inic AND pnom_mes_fina)
                OR
                (pnom_mes_inic > pnom_mes_fina AND ('{$mes}' >= pnom_mes_inic OR '{$mes}' <= pnom_mes_fina));
            ;";
    $oIfx->Query($sql);

    $pnom_mes_inic = $oIfx->f("pnom_mes_inic");
    $pnom_mes_fina = $oIfx->f("pnom_mes_fina");

    if($pnom_mes_fina < $pnom_mes_inic){
        if($mes >= $pnom_mes_inic){
            $pnom_per_inic = $anio.str_pad($pnom_mes_inic, 2, '0', STR_PAD_LEFT);
            $pnom_per_fina = ($anio+1).str_pad($pnom_mes_fina, 2, '0', STR_PAD_LEFT);
        }else{
            $pnom_per_inic = ($anio-1).str_pad($pnom_mes_inic, 2, '0', STR_PAD_LEFT);
            $pnom_per_fina = $anio.str_pad($pnom_mes_fina, 2, '0', STR_PAD_LEFT);
        }
    }else{
        $pnom_per_inic = $anio.str_pad($pnom_mes_inic, 2, '0', STR_PAD_LEFT);
        $pnom_per_fina = $anio.str_pad($pnom_mes_fina, 2, '0', STR_PAD_LEFT);
    }
    
    $result = [
        'rcts_cod_rubr' => $oIfx->f("pnom_cod_rubr"),
        'rcts_cod_pnom' => $oIfx->f("pnom_cod_pnom"),
        'rcts_per_inic' => $pnom_per_inic,
        'rcts_per_fina' => $pnom_per_fina
    ];
    $oIfx->Free();
    return $result;
}

//determinar la Gratificacion aplicable:
//return: 'RGRJU' = Gratificacion junio | 'RGRDI' =Gratificacion Noviembre
function gratificacion_aplicable($fecha, $empr_cod, $oIfx){
    $mes = date('m', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));
    $sql = "SELECT 
                pnom_cod_rubr,
                pnom_cod_pnom,
                pnom_mes_inic,
                pnom_mes_fina
            FROM saepnom
            WHERE 
                pnom_cod_empr = '{$empr_cod}' AND
                pnom_cod_rubr LIKE 'RGR%' AND (
                    (pnom_mes_inic <= pnom_mes_fina AND '{$mes}' BETWEEN pnom_mes_inic AND pnom_mes_fina)
                    OR
                    (pnom_mes_inic > pnom_mes_fina AND ('{$mes}' >= pnom_mes_inic OR '{$mes}' <= pnom_mes_fina))
                )
            ;";

    $oIfx->Query($sql);

    //transformar las vaiables pnom_mes: que son valores int entre 1-2 a pnom_per str en formato aaaamm
    $pnom_mes_inic = $oIfx->f("pnom_mes_inic");
    $pnom_mes_fina = $oIfx->f("pnom_mes_fina");
    if($pnom_mes_fina < $pnom_mes_inic){
        if($mes >= $pnom_mes_inic){
            $pnom_per_inic = $anio.str_pad($pnom_mes_inic, 2, '0', STR_PAD_LEFT);
            $pnom_per_fina = ($anio+1).str_pad($pnom_mes_fina, 2, '0', STR_PAD_LEFT);
        }else{
            $pnom_per_inic = ($anio-1).str_pad($pnom_mes_inic, 2, '0', STR_PAD_LEFT);
            $pnom_per_fina = $anio.str_pad($pnom_mes_fina, 2, '0', STR_PAD_LEFT);
        }
    }else{
        $pnom_per_inic = $anio.str_pad($pnom_mes_inic, 2, '0', STR_PAD_LEFT);
        $pnom_per_fina = $anio.str_pad($pnom_mes_fina, 2, '0', STR_PAD_LEFT);
    }
    $result = [
        'rgrt_cod_rubr' => $oIfx->f("pnom_cod_rubr"),
        'rgrt_cod_pnom' => $oIfx->f("pnom_cod_pnom"),
        'rgrt_per_inic' => $pnom_per_inic,
        'rgrt_per_fina' => $pnom_per_fina
    ];
    $oIfx->Free();
    return $result;
}

// determinar el promedio de horas extras
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

    $rcts_ani_fina = substr($rcts_per_fina, 0, 4);
    
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

function calcular_intervalo_trunco($prov_per_inic, $esem_fec_sali, $empl_cod, $empr_cod, $oIfx){
    $sql = "SELECT
                SUM((empl_jor_trab - (amem_hor_trab + amem_hor_vaca) ) / (empl_jor_trab/30) ) AS empl_dia_falt,
                SUM((amem_hor_trab + amem_hor_vaca) / (empl_jor_trab/30) ) AS empl_dia_trab
            FROM saeamem
            JOIN saeempl ON amem_cod_empr = empl_cod_empr AND amem_cod_empl = empl_cod_empl
            WHERE
                amem_cod_empr = '{$empr_cod}' AND 
                amem_cod_empl = '{$empl_cod}' AND 
                amem_fec_amem BETWEEN TO_DATE(CAST('{$prov_per_inic}' AS VARCHAR), 'YYYYMM') AND CAST('{$esem_fec_sali->format('Y-m-d')}' AS DATE)
            ;";
    
    $oIfx->Query($sql);
    $empl_dia_falt = $oIfx->f("empl_dia_falt");
    $empl_dia_trab = $oIfx->f("empl_dia_trab");
    $oIfx->Free();

    
    $dia_trunco = (int)$esem_fec_sali->format('d');
    return [
        'empl_dia_trab' => $empl_dia_trab, 
        'empl_dia_falt' => $empl_dia_falt - (30-$dia_trunco)
    ];
}
function calcular_intervalo_gratificaciones_trunco($rgrt, $esem_fec_sali, $empl_cod, $empr_cod, $oIfx){
    $rgrt_per_fina = $esem_fec_sali->format('Ym');

    $sql = "SELECT
                COUNT(*) AS empl_mes_grat
            FROM saepemp
            WHERE
                pemp_cod_empl = '{$empl_cod}' AND
                pemp_cod_empr = '{$empr_cod}' AND
                pemp_cod_pnom = '{$rgrt['rgrt_cod_pnom']}' AND
                pemp_per_pemp BETWEEN {$rgrt['rgrt_per_inic']} AND {$rgrt_per_fina}
            ;";
    
    $oIfx->Query($sql);
    $empl_mes_grat = $oIfx->f("empl_mes_grat");
    $oIfx->Free();

    return  $empl_mes_grat;
}

function calcular_periodo_vacaciones($esem_fec_ingr, $esem_fec_sali){
    $empl_int_trab = $esem_fec_ingr->diff($esem_fec_sali);

    $vaca_fec_finl = $esem_fec_sali;
    $vaca_fec_inic = (clone $esem_fec_ingr)->add(new DateInterval('P'.$empl_int_trab->y.'Y'));

    return [
        'vaca_fec_inic' => $vaca_fec_inic,
        'vaca_fec_fina' => $vaca_fec_finl,
        'vaca_per_fina' => $vaca_fec_finl->format('Ym'),
        'vaca_per_inic' => $vaca_fec_inic->format('Ym')
    ];
}

function calcular_vacaciones(DateTime $esem_fec_sali, DateTime $esem_fec_ingr, $empl_cod, $empr_cod, $oIfx){
    $empl_int_trab = $esem_fec_ingr->diff($esem_fec_sali);
    if ($empl_int_trab->m < 1){
        return [
            'empl_vac_toma' => 0,
            'empl_vac_pend' => 0,
            'empl_vac_trun' => 0,
            'vaca_dia_anio' => 0
        ];
    }
    $sql = "SELECT 
                CAST ( prrh_val_prrh AS INTEGER ) 
            FROM saeprrh 
            WHERE 
                prrh_cod_empr = '{$empr_cod}' AND 
                prrh_cod_prrh = 'PVNDA' 
            ;";

    $oIfx->Query($sql);
    $vaca_dia_anio = $oIfx->f("prrh_val_prrh");
    $oIfx->Free();
    
    $empl_vac_trun = calcular_vacaciones_truncas($esem_fec_sali, $esem_fec_ingr, $empl_cod, $empr_cod, $oIfx);
    
    $sql = "SELECT 
                CAST ( prrh_val_prrh AS INTEGER ) 
            FROM saeprrh 
            WHERE 
                prrh_cod_empr = '{$empr_cod}' AND 
                prrh_cod_prrh = 'PAADI' 
            ;";

    $oIfx->Query($sql);
    $vaca_dia_paadi = $oIfx->f("prrh_val_prrh");
    $oIfx->Free();

    if ($empl_int_trab->y < $vaca_dia_paadi){
        return [
            'empl_vac_toma' => 0,
            'empl_vac_pend' => 0,
            'empl_vac_trun' => $empl_vac_trun,
            'vaca_dia_anio' => $vaca_dia_anio
        ];
    } 

    $empl_vac_pend = calcular_vacaciones_pendientes($esem_fec_sali, $esem_fec_ingr, $empl_cod, $empr_cod, $oIfx);
    $empl_vac_toma = calcular_vacaciones_tomadas($esem_fec_sali, $esem_fec_ingr, $empl_cod, $empr_cod, $oIfx);
    return [
        'empl_vac_toma' => ($empl_vac_toma ?? 0)*-1 ,
        'empl_vac_pend' => ($empl_vac_pend ?? 0)*1,
        'empl_vac_trun' => $empl_vac_trun ?? 0,
        'vaca_dia_anio' => $vaca_dia_anio ?? 0
    ];
}

function calcular_vacaciones_truncas(DateTime $esem_fec_sali, DateTime $esem_fec_ingr, $empl_cod, $empr_cod, $oIfx){
    // ejer_fec_inic = esem_fec_ingr + (esem_fec_ingr->diff(esem_fec_sali)->y) year
    $empl_int_trab = $esem_fec_ingr->diff($esem_fec_sali);
    if ($empl_int_trab->y < 1){
        $ejer_fec_inic = $esem_fec_ingr;
    } else {
        $ejer_fec_inic = (clone $esem_fec_ingr)->add(new DateInterval('P'.$empl_int_trab->y.'Y'));
    }

    $sql = "SELECT 
                CAST ( prrh_val_prrh AS INTEGER ) 
            FROM saeprrh 
            WHERE 
                prrh_cod_empr = '{$empr_cod}' AND 
                prrh_cod_prrh = 'PVNDA' 
            ;";

    $oIfx->Query($sql);
    $vaca_dia_anio = $oIfx->f("prrh_val_prrh");
    $oIfx->Free();

    $empl_dia_trab = calcular_intervalo_trunco($ejer_fec_inic->format('Ym'), $esem_fec_sali, $empl_cod, $empr_cod, $oIfx)['empl_dia_trab'];
    $empl_vac_trun = ($vaca_dia_anio / 360) * $empl_dia_trab;
    return $empl_vac_trun;
}

function calcular_vacaciones_pendientes($esem_fec_sali, $esem_fec_ingr, $empl_cod, $empr_cod, $oIfx){
    $empl_int_trab = $esem_fec_ingr->diff($esem_fec_sali);
    $ejer_fec_fina = (clone $esem_fec_ingr)->add(new DateInterval('P'.$empl_int_trab->y.'Y'));
    $sql = "SELECT
	            (
	                ((
                        SELECT 
                            CAST ( prrh_val_prrh AS INTEGER ) 
                        FROM saeprrh 
                        WHERE 
                            prrh_cod_empr = '{$empr_cod}' AND 
                            prrh_cod_prrh = 'PVNDA' 
                    ) * '{$empl_int_trab->y}') - ( 
                        SELECT 
                            SUM ( amem_hor_vaca / (empl_jor_trab/30) ) AS empl_dia_vaca 
                        FROM saeamem 
                        JOIN saeempl ON amem_cod_empr = empl_cod_empr AND amem_cod_empl = empl_cod_empl
                        WHERE 
                            amem_cod_empl = '{$empl_cod}' AND
                            amem_fec_amem <= CAST('{$ejer_fec_fina->format('Y-m-d')}' AS DATE)
                    ) 
	            ) AS empl_vac_pend
            ;";
    $oIfx->Query($sql);
    $empl_vac_pend = $oIfx->f("empl_vac_pend");
    $oIfx->Free();

    return $empl_vac_pend;
}
function calcular_vacaciones_tomadas($esem_fec_sali, $esem_fec_ingr, $empl_cod, $empr_cod, $oIfx){
    $empl_int_trab = $esem_fec_ingr->diff($esem_fec_sali);
    $ejer_fec_fina = (clone $esem_fec_ingr)->add(new DateInterval('P'.$empl_int_trab->y.'Y'));
    $sql = "SELECT 
                SUM ( amem_hor_vaca / (empl_jor_trab/30) ) AS empl_dia_vaca 
            FROM saeamem 
            JOIN saeempl ON amem_cod_empr = empl_cod_empr AND amem_cod_empl = empl_cod_empl
            WHERE 
                amem_cod_empl = '{$empl_cod}' AND
                amem_fec_amem >= CAST('{$ejer_fec_fina->format('Y-m-d')}' AS DATE)
            ;";

    $oIfx->Query($sql);
    $empl_vac_toma = $oIfx->f("empl_dia_vaca");
    $oIfx->Free();

    return $empl_vac_toma;
}

function valor_vafp($esem_fec_sali, $empl_cod_pdie, $empr_cod, $oIfx){
    $sql = "SELECT 
                (afp_prim_seg+afp_fond_pen+afp_comi_flmi) as value 
            FROM saeafp
            WHERE
                afp_cod_empr = '{$empr_cod}' AND
                afp_cod_pdie = '{$empl_cod_pdie}' AND
                afp_per_afp <= '{$esem_fec_sali->format('Ym')}'
            ORDER BY afp_per_afp DESC
            ;";
    $oIfx->Query($sql);
    $vafp_val = $oIfx->f("value");
    $oIfx->Free();
    if ($vafp_val){
        return $vafp_val;
    }else{
        return 0;
    }
}

function valor_essalud($esem_fec_sali, $empl_cod_, $empr_cod, $oIfx){
    $sql = "SELECT 
                * 
            FROM saepemp 
            WHERE 
                pemp_cod_empl = '{$empl_cod_}' AND
                pemp_cod_empr = '{$empr_cod}' AND
                pemp_cod_pnom = (SELECT pnom_cod_pnom FROM saepnom WHERE pnom_cod_rubr ='RESAL') AND 
                pemp_per_pemp <= '{$esem_fec_sali->format('Ym')}'
            ORDER BY pemp_per_pemp DESC
            ;";
    $oIfx->Query($sql);
    $pemp_val = $oIfx->f("pemp_val_mese");
    $oIfx->Free();
    if ($pemp_val){
        return $pemp_val;
    }else{
        return 0;
    }
}

function valor_eps($empl_cod, $empr_cod, $oIfx){
    $sql = "SELECT 
                eps_val_eps
            FROM saeeps
            WHERE
                eps_cod_eps = (SELECT empl_cod_eps FROM saeempl WHERE empl_cod_empl = '{$empl_cod}' AND empl_cod_empr = '{$empr_cod}') AND
                eps_cod_empr = '{$empr_cod}'
            ;";
    $oIfx->Query($sql);
    $eps_per_eps = $oIfx->f("eps_val_eps");
    $oIfx->Free();

    if ($eps_per_eps){
        return $eps_per_eps;
    }else{
        return 0;
    }
}