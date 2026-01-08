<?php
include_once('../../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
include_once (path(DIR_INCLUDE).'html2pdf_v4.03/_tcpdf_5.0.002_old/tcpdf.php');

	global $DSN_Ifx, $DSN;

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
	$idSucursal = $_GET['sucursal'];
	$id = $_GET['codigo'];

    $array_imp = $_SESSION['U_EMPRESA_IMPUESTO'];
    $eti_imp=$array_imp['IVA'];

    $empr_cod_pais = $_SESSION['U_PAIS_COD'];
    // IMPUESTOS POR PAIS
    $sql = "select p.impuesto, p.etiqueta, p.porcentaje from comercial.pais_etiq_imp p where
    p.pais_cod_pais = $empr_cod_pais and etiqueta='$eti_imp'";
   if ($oCon->Query($sql)) {
       if ($oCon->NumFilas() > 0) {
           do {
               $porcentaje = $oCon->f('porcentaje');
           } while ($oCon->SiguienteRegistro());
       }
   }
   $oCon->Free();

//	print_r($id);Exit;
	
	if(empty($idSucursal)){
		$idSucursal = $_SESSION['U_SUCURSAL'];
	}

    $sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr, 
			empr_conta_sn, empr_num_resu, empr_path_logo
			from saeempr 
			where empr_cod_empr = $idEmpresa ";
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
        }
    }
    $oIfx->Free();

    //LOGO DEL REPORTE
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

    $empr_logo = '<div align="center">
        <img src="' . $imagen . '" style="
        width:100px;
        object-fit; contain;">
        </div>';
    $x = '0px';
}
else{
    $empr_logo='LOGO NO CARGADO';
}
	
	//query bodega
	$sqlBode = "select bode_cod_bode, bode_nom_bode from saebode where bode_cod_empr = $idEmpresa";
	if ($oIfx->Query($sqlBode)) {
        if ($oIfx->NumFilas() > 0) {
			unset($arrayBode);
            do {
                $arrayBode[$oIfx->f('bode_cod_bode')] = $oIfx->f('bode_nom_bode');
            } while ($oIfx->SiguienteRegistro());
        }
    }
	$oIfx->Free();
	
	$sqlUnidad = "select unid_cod_unid, unid_nom_unid, unid_sigl_unid from saeunid where unid_cod_empr = $idEmpresa";
	if ($oIfx->Query($sqlUnidad)) {
        if ($oIfx->NumFilas() > 0) {
			unset($arrayUnidad);
            do {
                $arrayUnidad[$oIfx->f('unid_cod_unid')] = $oIfx->f('unid_sigl_unid');
            } while ($oIfx->SiguienteRegistro());
        }
    }
	$oIfx->Free();




    $sqlPed = "SELECT * FROM saepedf WHERE
				pedf_cod_pedf = $id and
				pedf_cod_empr = $idEmpresa and
				pedf_cod_sucu = $idSucursal";
//    print_r($sqlPed);
    if ($oIfx->Query($sqlPed)) {
        //echo $oIfx->NumFilas();
        if ($oIfx->NumFilas() > 0) {


                $id_pedido = $oIfx->f('pedf_cod_pedf');
                $codigo_op = $oIfx->f('pedf_num_preimp');
                $id_cliente = $oIfx->f('pedf_cod_clpv');
                $pedf_cod_pedf = $oIfx->f('pedf_cod_pedf');
                $nombre_cliente = $oIfx->f('pedf_nom_cliente');
                $ruc_cliente = $oIfx->f('pedf_ruc_clie');
                $telefono = $oIfx->f('pedf_tlf_cliente');
                $direccion = $oIfx->f('pedf_dir_clie');
                $id_user = $oIfx->f('pedf_user_web');
                $fecha_user = $oIfx->f('pedf_fech_fact');
                $prioridad = $oIfx->f('prioridad');
                $vend_cod_vend = $oIfx->f('pedf_cod_vend');
                $pedf_email_clpv = $oIfx->f('pedf_email_clpv');
                $subtotal = $oIfx->f('pedf_tot_fact');
                $con_iva = $oIfx->f('pedf_con_miva');
                $sin_iva = $oIfx->f('pedf_sin_miva');
                $dsg_valo = $oIfx->f('pedf_dsg_valo');
                $iva = $oIfx->f('pedf_iva');
                $pedf_cm1_pedf = $oIfx->f('pedf_cm1_pedf');
				$estado = $oIfx->f('pedf_est_fact');
				$pedf_cod_ccli = $oIfx->f('pedf_cod_ccli');
				$pedf_hor_fin = $oIfx->f('pedf_hor_fin');
                
				$pedf_hor_impr = $oIfx->f('pedf_hor_impr');
				$pedf_lug_pedf = $oIfx->f('pedf_lug_pedf');
				$pedf_cod_ase = $oIfx->f('pedf_cod_ase');



                //selecciona sucursales y direcciones
                $sql_sucu = "select sucu_nom_sucu, sucu_dir_sucu from saesucu where sucu_cod_empr = $idEmpresa and sucu_cod_sucu = $idSucursal limit 1";
                $sucu_nom_sucu = consulta_string($sql_sucu, 'sucu_nom_sucu', $oIfx, '');
                $sucu_dir_sucu = consulta_string($sql_sucu, 'sucu_dir_sucu', $oIfx, '');


                $logo .= '<table style="font-size: 80%;" >';
                $logo .= '<tr><td align="left"  >' .  $empr_logo . '</td></tr>';
                $logo .= '<tr><td align="center" >' . $razonSocial . '</td></tr>';
                $logo .= '<tr><td align="center" >' . $sucu_nom_sucu . '</td></tr>';
                $logo .= '<tr><td align="center" >' . $sucu_dir_sucu . '</td></tr>';
                $logo .= '<tr><td align="center" style="color: red;">PEDIDO : ' . $codigo_op . '</td></tr>';
                $logo .= '</table><br><br>';




                if ($estado == 'PE') {
                    $estado = 'PENDIENTE';
                    $color = 'green';
                } elseif ($estado == 'AN') {
                    $estado = 'ANULADO';
                    $color = 'red';
                } elseif ($estado == 'GR') {
                    $estado = "FACTURADO";
                    $color = 'blue';
                } elseif ($estado == 'PA') {
                    $estado = "POR AUTORIZAR";
                    $color = 'orange';
                }elseif ($estado == 'PR') {
                    $estado = "PERDIDA";
                    $color = 'red';
                }
				
				//query subcliente
				if(!empty($pedf_cod_ccli)){
					$sqlCcli = "select ccli_nom_conta from saeccli where ccli_cod_ccli = $pedf_cod_ccli and ccli_cod_clpv = $id_cliente and ccli_cod_empr = $idEmpresa";
					if($oIfxA->Query($sqlCcli)){
						if($oIfxA->NumFilas() > 0){
							do{
								$ccli_nom_conta = $oIfxA->f('ccli_nom_conta');
							}while($oIfxA->SiguienteRegistro());
						}
					}
					$oIfxA->Free();
				}

                // nombre vendedor
                $sql_vend = "select vend_nom_vend from saevend where vend_cod_vend = '$vend_cod_vend' ";
                //echo $sql_vend;
                if ($oIfxA->Query($sql_vend)) {
                    if ($oIfxA->NumFilas() > 0) {
                        $nombre_vendedor = $oIfxA->f('vend_nom_vend');
                    } else {
                        $nombre_vendedor = '';
                    }
                }
                $oIfxA->Free();

                $asesor = '';
                /*if($pedf_cod_ase){
                    // nombre asesor
                    $sql_asesor = "select   ases_ape_ases || ' ' ||ases_nom_ases as asesor  from saeases where ases_cod_ases ='$pedf_cod_ase' ";
                    if ($oIfxA->Query($sql_asesor)) {
                        if ($oIfxA->NumFilas() > 0) {
                            $asesor = $oIfxA->f('asesor');
                        } else {
                            $asesor = '';
                        }
                    }
                    $oIfxA->Free();
                }*/

                if(!empty($pedf_cod_ase)){
                    $sql_asesor="select concat(usuario_apellido, ' ', usuario_nombre) as user from comercial.usuario where empresa_id=$idEmpresa and usuario_id=$pedf_cod_ase";
                    $asesor = consulta_string($sql_asesor, 'user', $oIfxA, '');
                }


                // nombre comercial
                $sql_com = "select clpv_nom_come, clpv_cod_zona from saeclpv where clpv_cod_clpv = '$id_cliente' ";

                if ($oIfxA->Query($sql_com)) {
                    if ($oIfxA->NumFilas() > 0) {
                        $nombre_comercial = trim($oIfxA->f('clpv_nom_come'));
                        $zona_cliente = $oIfxA->f('clpv_cod_zona');

                        if($zona_cliente){
                            $sql_zona = "select zona_nom_zona from saezona where zona_cod_zona = '$zona_cliente';";
                            $zona_cliente = consulta_string($sql_zona, 'zona_nom_zona', $oIfx, '');
                        }

                    } else {
                        $nombre_comercial = '';
                        $zona_cliente = '';
                    }
                }
                $oIfxA->Free();



                $cliente .= ' <table style="width: 100%; margin-top:7px;font-size: 80%;" cellspacing="1" cellpadding="1">';
				$cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%;" align="left"><b>NOMBRE COMERCIAL:</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $nombre_comercial . '</td>';
                $cliente .= ' <td style="width: 15%;"><b>FECHA PEDIDO:</b></td>';
                $cliente .= ' <td style="width: 30%;">'.$pedf_hor_fin.'</td>';
                $cliente .= ' </tr>';
				
				$cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%"><b>CLIENTE:</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $nombre_cliente . '</td>';
                $cliente .= ' <td style="width: 15%;"><b>IDENTIFICACION:</b></td>';
                $cliente .= ' <td style="width: 30%;">' . $ruc_cliente . '</td>';
                $cliente .= ' </tr>';

			    $cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%;"><b>TELEFONO</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $telefono . '</td>';
				$cliente .= ' <td style="width: 15%;"><b>EMAIL:</b></td>';
                $cliente .= ' <td style="width: 30%;">' . $pedf_email_clpv . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%;"><b>DIRECCION:</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $direccion . '</td>';
                $cliente .= ' <td style="width: 15%;"><b>VENDEDOR:</b></td>';
                $cliente .= ' <td style="width: 30%;">' . $nombre_vendedor . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%;"><b>SUBCLIENTE:</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $ccli_nom_conta . '</td>';
                $cliente .= ' <td style="width: 15%;"><b>ESTADO:</b></td>';
                $cliente .= ' <td style="width: 30%;color: '.$color.';">' . $estado . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%;"><b>OBSERVACIONES:</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $pedf_cm1_pedf . '</td>';
                $cliente .= ' <td style="width: 15% "><b>CIUDAD:</b></td>';
                $cliente .= ' <td style="width: 30%;">' . $pedf_lug_pedf . '</td>';
                $cliente .= ' </tr>';

                $cliente .= ' <tr>';
                $cliente .= ' <td style="width: 15%;"><b>ZONA:</b></td>';
                $cliente .= ' <td style="width: 40%;">' . $zona_cliente . '</td>';
                $cliente .= ' <td style="width: 15%;" ><b>ASESOR:</b></td>';
                $cliente .= ' <td style="width: 30%;">' . $asesor . '</td>';
                $cliente .= ' </tr>';
                $cliente .= ' </table><br><br>';



        }
    }
    $oIfx->Free();

    $sqlDeta = "SELECT
        dpef_cant_dfac,
        dpef_cod_prod,
        dpef_nom_prod,
        dpef_precio_dfac,
        dpef_des1_dfac,
        dpef_des2_dfac,
        dpef_por_dsg,
        dpef_mont_total,
        dpef_det_dpef,
        dpef_cod_bode,
        dpef_cod_unid,
        prod_apli_prod,
        marc_des_marc,
        prbo_dis_prod 
    FROM
        saedpef d
        INNER JOIN saeprbo b ON d.dpef_cod_prod = b.prbo_cod_prod 
        AND d.dpef_cod_empr = b.prbo_cod_empr 
        AND d.dpef_cod_sucu = b.prbo_cod_sucu
        AND d.dpef_cod_bode = b.prbo_cod_bode
        INNER JOIN saeprod P ON d.dpef_cod_prod = P.prod_cod_prod 
        AND d.dpef_cod_empr = P.prod_cod_empr 
        AND d.dpef_cod_sucu = P.prod_cod_sucu
        LEFT JOIN saemarc M ON P.prod_cod_marc = M.marc_cod_marc 
        AND P.prod_cod_empr = M.marc_cod_empr 
    WHERE
    dpef_cod_pedf = $id and dpef_cod_sucu = $idSucursal and dpef_cod_empr = $idEmpresa";

    $deta .= ' <table style="font-size: 75%;" border="1"  cellpadding="1" >';
    $deta .= ' <tr>';
	//$deta .= ' <td style="width: 14%;" align="center"><b>BODEGA</b></td>';
    $deta .= ' <td style="width: 20%;" align="center"><b>CODIGO</b></td>';
    $deta .= ' <td style="width: 21%;" align="center"><b>DESCRIPCION</b></td>';
    //$deta .= ' <td style="width: 10%;" align="center"><b>DETALLE</b></td>';
    $deta .= ' <td style="width: 8%;" align="center"><b>MARCA</b></td>';
    $deta .= ' <td style="width: 21%;" align="center"><b>APLICACION</b></td>';
    $deta .= ' <td style="width: 8%;" align="center"><b>STOCK</b></td>';
	//$deta .= ' <td style="width: 7%;" align="center"><b>UNIDAD</b></td>';
    $deta .= ' <td style="width: 6%;" align="center"><b>CANT</b></td>';
    $deta .= ' <td style="width: 8%;" align="center"><b>PREC</b></td>';
    //$deta .= ' <td style="width: 5%;" align="center"><b>DSCT</b></td>';
    $deta .= ' <td style="width: 8%;" align="center"><b>TOTAL</b></td>';
    $deta .= ' </tr>';

    if ($oIfx->Query($sqlDeta)) {
        if ($oIfx->NumFilas() > 0) {
            $i = 1;
            do {
                $dfac_cod_prod = $oIfx->f('dpef_cod_prod');

                $aplicacion = $oIfx->f('prod_apli_prod');
                $marca = $oIfx->f('marc_des_marc');
                $stock = $oIfx->f('prbo_dis_prod');

                $dfac_nom_prod = $oIfx->f('dpef_nom_prod');
                $dfac_cant_dfac = $oIfx->f('dpef_cant_dfac');
                $dfac_precio_dfac = $oIfx->f('dpef_precio_dfac');
                $dfac_des1_dfac = $oIfx->f('dpef_des1_dfac');
                $dfac_des2_dfac = $oIfx->f('dpef_des2_dfac');
                $dfac_por_dsg = $oIfx->f('dpef_por_dsg');
                $dfac_mont_total = $oIfx->f('dpef_mont_total');
                $dpef_det_dpef = $oIfx->f('dpef_det_dpef');
                $dpef_cod_bode = $oIfx->f('dpef_cod_bode');
				$dpef_cod_unid = $oIfx->f('dpef_cod_unid');
				
	
                $porc_descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;
                $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;
                if ($descuento > 0)
                    $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                else
                    $descuento = 0;

                $totalDescuento = $totalDescuento + $descuento;

                $deta .= ' <tr>';
				//$deta .= ' <td style="width: 14%;" align="center">' . $arrayBode[$dpef_cod_bode] . '</td>';
                $deta .= ' <td style="width: 20%;" align="center">' . $dfac_cod_prod . '</td>';
                $deta .= ' <td style="width: 21%;" align="left">' . ($dfac_nom_prod) . '</td>';
                //$deta .= ' <td style="width: 10%;" align="left">' . ($dpef_det_dpef) . '</td>';
                $deta .= ' <td style="width: 8%;" align="left">' . $marca . '</td>';
                $deta .= ' <td style="width: 21%;font-size: 70%;" align="left">' . $aplicacion . '</td>';
                $deta .= ' <td style="width: 8%;" align="center">' . round($stock ,0). '</td>';
				//$deta .= ' <td style="width: 7%;" align="center">' . $arrayUnidad[$dpef_cod_unid] . '</td>';
				$deta .= ' <td style="width: 6%;" align="center">' . round($dfac_cant_dfac, 0) . '</td>';
                $deta .= ' <td style="width: 8%;" align="right">' . number_format($dfac_precio_dfac, 2, '.', ',') . '</td>';
                //$deta .= ' <td style="width: 5%;" align="right">' . round($porc_descuento, 0) . ' %</td>';
                $deta .= ' <td style="width: 8%;" align="right">' . number_format($dfac_mont_total, 2, '.', ',') . '</td>';
                $deta .= ' </tr>';
                $i++;
            }while ($oIfx->SiguienteRegistro());
        }
    }
	$oIfx->Free();
	
    $deta .= ' </table>';

    $totales .= ' <table style="font-size:75%;"  border="0" align="right" cellspacing="0" cellpadding="0">';
    $totales .= ' <tr>';
    $totales .= ' <td style="width: 69.6%;"></td>';
    $totales .= ' <td style="width: 30.4%;">';

    $totales .= ' <table style="width:100%;"  border="1" align="right" cellspacing="0" cellpadding="1">';
    $totales .= ' <tr>';
    $totales .= ' <td style="width: 73.4%;" ><b>SUBTOTAL SIN IMPUESTOS:</b></td>';
    $totales .= ' <td style="width: 26.6%;"   align="right">' . number_format($con_iva + $sin_iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    $totales .= ' <tr>';
    $totales .= ' <td><b>SUBTOTAL  '.$eti_imp.' '.$porcentaje.'%:</b></td>';
    $totales .= ' <td align="right">' . number_format($con_iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    $totales .= ' <tr>';
    $totales .= ' <td><b>SUBTOTAL  '.$eti_imp.' 0%:</b></td>';
    $totales .= ' <td align="right">' . number_format($sin_iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    $totales .= ' <tr>';
    $totales .= ' <td><b>DESCUENTO:</b></td>';
    $totales .= ' <td align="right">' . number_format($dsg_valo, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    $totales .= ' <tr>';
    $totales .= ' <td><b>'.$eti_imp.':</b></td>';
    $totales .= ' <td align="right">' . number_format($iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    $totales .= ' <tr>';
    $totales .= ' <td><b>TOTAL:</b></td>';
    $totales .= ' <td align="right">' . number_format($con_iva + $sin_iva + $iva, 2, '.', ',') . '</td>';
    $totales .= ' </tr>';
    $totales .= ' </table>';

    $totales .= '</td>';
    $totales .= ' </tr>';
    $totales .='</table>';

    

	if(empty($pedf_hor_impr)){
		
		$hora_impr = date('Y-m-d H:i:s');
		
		$sqlUpdate = "update saepedf set pedf_est_impr = 'S', pedf_hor_impr = '$hora_impr' where pedf_cod_empr = $idEmpresa and pedf_cod_pedf = $id";
		$oIfx->QueryT($sqlUpdate);
	}

/*  $documento = '<page backimgw="10%" backtop="5mm" backbottom="5mm" backleft="3mm" backright="3mm" footer="date;heure;page">';
    $documento .= $logo . $cliente . $deta . $totales;
    $documento .=   $cliente . $deta . $totales;
    $documento .= '</page>';

    $num_ped = "pedido_$codigo_op";
    $html2pdf = new HTML2PDF('P', 'A4', 'es');
    $html2pdf->WriteHTML($documento);
    $html2pdf->Output($num_ped.'.pdf');*/
$html=$logo.$cliente.$deta.$totales;
$documento = <<<EOD
    $html
EOD;

    //DISEÑO DEL REPORTE
class MYPDF extends TCPDF2 {

    
    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
    
    $pdf = new TCPDF2('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetMargins(10,10, 10, true); 
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
    $pdf->Output($num_ped.'.pdf','I');

?>