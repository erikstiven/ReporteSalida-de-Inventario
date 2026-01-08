<?php

function pago_utilidades_peru($empr_cod_empr, $ejer_cod_ejer, $id_empl, &$rutaPdf = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $db = new Database();
    if ($db->initializeConnections() !== "Todas las conexiones se realizaron con éxito.") {
        throw new Exception("No se pudo conectar a las bases de datos: ");
    }

    $sql = "SELECT DATE_PART('year', ejer_fec_finl) as anio FROM saeejer WHERE ejer_cod_ejer = '{$ejer_cod_ejer}'";
    $anio = $db->executeQuery($sql)[0]['anio'];


    $nombre_archivo = "DISTRIBUCION_DE_UTILIDADES_{$anio}_{$id_empl}";

    $sql = "SELECT 
                empr_path_logo,
                empr_repres,
                empr_nom_empr,
                empr_ruc_empr,
                empr_dir_empr,
                empr_ced_repr
            FROM 
                saeempr 
            WHERE 
                empr_cod_empr='{$empr_cod_empr}'
            ;";
    $tmp = $db->executeQuery($sql)[0];

    $empr_path_logo = $tmp['empr_path_logo'];
    $empr_repres = $tmp['empr_repres'];
    $empr_nom = $tmp['empr_nom_empr'];
    $empr_ruc = $tmp['empr_ruc_empr'];
    $empr_dir = $tmp['empr_dir_empr'];
    $empr_ced_repr = $tmp['empr_ced_repr'];


    $sql = "SELECT 
                empl_ape_empl,
                empl_nom_empl,
                empl_sal_sala,
                empl_fir_empl
            FROM saeempl
            WHERE
                empl_cod_empl='{$id_empl}'
            ;";
    $tmp = $db->executeQuery($sql)[0];
    
    $empl_ape = $tmp['empl_ape_empl'];
    $empl_nom = $tmp['empl_nom_empl'];
    $empl_val_sala = $tmp['empl_sal_sala'];
    $empl_fir_empl = $tmp['empl_fir_empl'];

    $sql = "SELECT pago_val_pago FROM saepago WHERE pago_cod_rubr= 'RASFM' AND pago_cod_empl = '{$id_empl}' and pago_cod_empr = '{$empr_cod_empr}' ORDER BY pago_per_pago DESC";
    $rasfm_val_pago = $db->executeQuery($sql)[0]['pago_val_pago'];



    $sql = "SELECT 
                valor, 
                anio, 
                remuneracion_total_computada, 
                total_dias_trabajados
            FROM comercial.participacion_utilidades
            WHERE 
                empr_cod_empr = '{$empr_cod_empr}' AND
                anio = '{$ejer_cod_ejer}'
            ";

    $tmp = $db->executeQuery($sql)[0];

    $renta_anual = $tmp['valor'];
    $cod_anio = $tmp['anio'];
    $empr_dias = $tmp['total_dias_trabajados'];
    $empr_salario = $tmp['remuneracion_total_computada'];

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

    $monto_a_distribuir = $renta_anual*0.1;

    $sql = "SELECT
                SUM(DATE_PART( 'day', amem_fec_amem ) - ( ( 240 - amem_hor_trab ) / 8 )) AS dias
            FROM
                saeamem
                JOIN saeejer ON ( ejer_cod_ejer = {$cod_anio} ) 
            WHERE
                amem_cod_empr = '{$empr_cod_empr}' AND 
                amem_cod_empl = '{$id_empl}' AND 
                amem_fec_amem BETWEEN ejer_fec_inil AND ejer_fec_finl;";
    $empl_dias = $db->executeQuery($sql)[0]['dias'];

    $sql = "SELECT
                SUM(pago_val_pago) AS valor
            FROM
                saepago
                JOIN saeejer ON ( ejer_cod_ejer = 25 ) 
            WHERE
                pago_cod_rubr IN (SELECT rubr_cod_rubr from saerubr where rubr_cod_trub='I' and rubr_pag_iess=1) AND
                pago_cod_empr = '{$empr_cod_empr}' AND
                pago_cod_empl = '{$id_empl}' AND
                TO_DATE(CAST(pago_per_pago AS VARCHAR), 'YYYYMM') BETWEEN ejer_fec_inil AND ejer_fec_finl;";

    $empl_salario = $db->executeQuery($sql)[0]['valor'];

    $participacion_dias_ratio = round((($monto_a_distribuir/2)/$empr_dias), 3);
    $participacion_salario_ratio = round((($monto_a_distribuir/2)/$empr_salario), 3);

    //empl_cod_eemp
    $participacion_por_dias = $participacion_dias_ratio*$empl_dias;
    $participacion_por_salario = $participacion_salario_ratio*$empl_salario;

    $participacion_total = $participacion_por_dias + $participacion_por_salario;

    $empl_val_remu = $empl_val_sala + (empty($rasfm_val_pago)? 0 : $rasfm_val_pago); 

    $participacion_tope = $participacion_total > ($empl_val_remu * 18);

    $participacion_final = $participacion_tope ? ($empl_val_remu * 18) : $participacion_total;


    
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
            <h1 align="center">CONSTANCIA DE LIQUIDACIÓN DE DISTRIBUCIÓN DE UTILIDADES '.$anio.'</h1>
            <br/>
            <p align="justify"><b>'.$empr_nom.'</b>, con RUC Nº '.$empr_ruc.', domiciliado en '.$empr_dir.',
                siendo representada por '.$empr_repres.', con DNI N° '.$empr_ced_repr.', en su calidad de empleador y en base al cumplimiento de lo
                dispuesto por el Decreto Legislativo N° 892 y el Decreto Supremo N° 009 - 98 -TR, se deja constancia de la determinación, distribución y pago de la
                participación en las utilidades correspondiente al ejercicio '.$anio.' del trabajador: '.$empl_ape.' '.$empl_nom.' con DNI N° '.$id_empl.'</p>
            <br/>
            <br/>
            <h2 align="left">CÁLCULO DEL MONTO DE LA PARTICIPACION EN LAS UTILIDADES</h2>
            <table cellspacing="8"> 
                <tr>
                    <td colspan="6"><div style="font-weight: bold;font-size: 14px;padding: 8px">1. Utilidad por distribuir</div></td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td width="512"> Renta anual de la empresa antes de impuestos:</td>
                    <td>S/</td>
                    <td align="right">'.number_format($renta_anual, 2, '.', ',').'</td>
                    <td style="font-weight: bold;">&nbsp;</td>
                    <td align="right" style="font-weight: bold;">&nbsp;</td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td>Porcentaje a distribuir:</td>
                    <td>&nbsp;</td>
                    <td align="right">10%</td>
                    <td style="font-weight: bold;">&nbsp;</td>
                    <td align="right" style="font-weight: bold;">&nbsp;</td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td>Monto a distribuir:</td>
                    <td>S/</td>
                    <td align="right">'.number_format(($renta_anual*0.1), 2, '.', ',').'</td>
                    <td style="font-weight: bold;">&nbsp;</td>
                    <td align="right" style="font-weight: bold;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="6"><div style="font-weight: bold;font-size: 14px;padding: 8px">2. Cálculo de la participación</div></td>
                </tr>
                <tr>
                    <td colspan="6"><div style="font-weight: bold;font-size: 14px;padding: 8px;padding-left: 64px;">2.1. Según los días laborados</div></td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td width="512">Número total de días laborados durante el ejercicio '.$anio.' por todos los trabajadores de la empresa con derecho a percibir utilidades:</td>
                    <td>&nbsp;</td>
                    <td align="right">'.number_format($empr_dias, 0, '.', ',').'</td>
                    <td style="font-weight: bold;">&nbsp;</td>
                    <td align="right" style="font-weight: bold;">&nbsp;</td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td>Número de días laborados durante el ejercicio '.$anio.' por el trabajador:</td>
                    <td>&nbsp;</td>
                    <td align="right">'.number_format($empl_dias, 0, '.', ',').'</td>
                    <td style="font-weight: bold;">&nbsp;</td>
                    <td align="right" style="font-weight: bold;">&nbsp;</td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td>Participación del trabajador según los días laborados:</td>
                    <td>&nbsp;</td>
                    <td align="right">'. number_format($participacion_dias_ratio, 3, '.', ',') .'</td>
                    <td style="font-weight: bold;">&nbsp;</td>
                    <td align="right" style="font-weight: bold;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="6"><div style="font-weight: bold;font-size: 14px;padding: 8px;padding-left: 64px;">2.2. Según las remuneraciones percibidas</div></td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td width="512">Remuneración computable total pagada durante el ejercicio '.$anio.' a todos los trabajadores:</td>
                    <td>S/</td>
                    <td align="right">'. number_format($empr_salario, 2, '.', ',') .'</td>
                    <td style="font-weight: bold;">&nbsp;</td>
                    <td align="right" style="font-weight: bold;">&nbsp;</td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td>Remuneración computable percibida durante el ejercicio '. $anio .' por el trabajador:</td>
                    <td>S/</td>
                    <td align="right">'. number_format($empl_salario, 2, '.', ',') .'</td>
                    <td style="font-weight: bold;">&nbsp;</td>
                    <td align="right" style="font-weight: bold;">&nbsp;</td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td>Participación del trabajador según las remuneraciones percibidas:</td>
                    <td>S/</td>
                    <td align="right">'. number_format($participacion_salario_ratio, 3, '.', ',') .'</td>
                    <td style="font-weight: bold;">&nbsp;</td>
                    <td align="right" style="font-weight: bold;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="6"><div style="font-weight: bold;font-size: 14px;padding: 8px">3. Monto de la particpación a percibir por el trabajador</div></td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td width="512">Participación según los días laborados:</td>
                    <td>S/</td>
                    <td align="right">'. number_format($participacion_por_dias, 2, '.', ',') .'</td>
                    <td style="font-weight: bold;">&nbsp;</td>
                    <td align="right" style="font-weight: bold;">&nbsp;</td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td>Participación según las remuneraciones percibidas:</td>
                    <td>S/</td>
                    <td align="right">'. number_format($participacion_por_salario, 2, '.', ',') .'</td>
                    <td style="font-weight: bold;">&nbsp;</td>
                    <td align="right" style="font-weight: bold;">&nbsp;</td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td>Total de la participación del trabajador en las utilidades:</td>
                    <td>&nbsp;</td>
                    <td align="right">&nbsp;</td>
                    <td style="font-weight: bold;">S/</td>
                    <td align="right" style="font-weight: bold;">'. number_format($participacion_total, 2, '.', ',') .'</td>
                </tr>
                <tr>
                    <td colspan="6"><div style="font-weight: bold;font-size: 14px;padding: 8px">4. Monto del remanente generado por el trabajador</div></td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td>Tope de 18 remuneraciones del trabajador:</td>
                    <td>'. ($participacion_tope ?? 'S/') .'</td>
                    <td align="right">'. ($participacion_tope ? number_format($participacion_final, 2, '.', ',') : '-') .'</td>
                    <td style="font-weight: bold;">&nbsp;</td>
                    <td align="right" style="font-weight: bold;">&nbsp;</td>
                </tr>
                <tr>
                    <td width="144"><div style="padding-left: 128px;">-</div></td>
                    <td>Remanente destinado al FONDOEMPLEO</td>
                    <td>&nbsp;</td>
                    <td align="right">-</td>
                    <td style="font-weight: bold;">&nbsp;</td>
                    <td align="right" style="font-weight: bold;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="4"><div style="font-weight: bold;font-size: 14px;padding: 8px; text-align: center;">TOTAL A PAGAR POR LA UTILIDAD</div></td>
                    <td style="font-weight: bold; padding: 8px; border-top: 1px solid black;border-bottom: 1px solid black;">S/</td>
                    <td align="right" style="font-weight: bold; padding: 8px; border-top: 1px solid black;border-bottom: 1px solid black;">'. number_format($participacion_final, 2, '.', ',') .'</td>
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
                    <td><hr/></td>
                    <td><hr/></td>
                </tr>
                <tr>
                    <td align="center">'.$empr_nom.'</td>
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

?>