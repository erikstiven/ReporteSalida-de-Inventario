<?

include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
require_once 'html2pdf_v4.03/html2pdf.class.php';

if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

$oCnx = new Dbo ( );
$oCnx->DSN = $DSN;
$oCnx->Conectar();

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();

$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();

$idempresa		= $_GET['empresa'];
$minv_cod		= $_GET['serial'];
$minv_secu   	= $_GET['secu'];



$sql = "select bode_cod_bode, bode_nom_bode from saebode where bode_cod_empr = $idempresa ";
unset($array_bode);
$array_bode = array_dato($oIfx, $sql, 'bode_cod_bode', 'bode_nom_bode');

$sql = "select sucu_cod_sucu, sucu_nom_sucu from saesucu where sucu_cod_empr = $idempresa ";
unset($array_sucu);
$array_sucu = array_dato($oIfx, $sql ,'sucu_cod_sucu','sucu_nom_sucu');

$sql = "select u.USUARIO_ID,  CONCAT(u.USUARIO_NOMBRE , ' ', u.USUARIO_APELLIDO) as nom from usuario u";
unset($array_user);
$array_user = array_dato($oCnx, $sql, 'usuario_id', 'nom');

if ($minv_cod > 0) {
    $div .= '<div style="width: 210mm; height: 100mm; font-family: Arial; font-size: 10px; margin: 0px; padding: 0px;">'; //div padre
    $sql_det = "select minv_num_sec, minv_fmov, minv_hor_minv, minv_user_web,minv_cm1_minv,minv_cod_sucu,
						dmov_cod_dmov, dmov_cod_bode, dmov_cod_prod, dmov_bod_envi,
						dmov_can_dmov, dmov_cod_unid, unid_nom_unid, prod_nom_prod
						from saeminv, saedmov, saeunid , saeprod where 
						prod_cod_sucu = dmov_cod_sucu and
						prod_cod_prod = dmov_cod_prod and
						prod_cod_empr = $idempresa and
						unid_cod_unid = dmov_cod_unid and
						unid_cod_empr = $idempresa and
						minv_num_comp = dmov_num_comp and
						minv_cod_empr = $idempresa and
						minv_num_comp = $minv_cod ";
    if ($oIfx->Query($sql_det)) {
        if ($oIfx->NumFilas() > 0) {
			$fecha	= fecha_mysql_Ymd($oIfx->f('minv_fmov'));
			$hora  	= $oIfx->f('minv_hor_minv');
			$user 	= $array_user[$oIfx->f('minv_user_web')];
			$sucu 	= $array_sucu[$oIfx->f('minv_cod_sucu')];
			$comentario  	= $oIfx->f('minv_cm1_minv');
			
            $div .= '<div style="padding:2px; text-align:center; width:95%;">'; //div cabecera
			// $div .= '<fieldset style="border:#999999 1px solid; padding:2px; text-align:center; width:95%;">';
            $div .= '<table align="center" cellpadding="2" cellspacing="1" width="99%" style="border: #999999  1px solid;">'; //table cabecera
			$div .= '<tr>';			
			$div .= '<th align="center" height="20" style="border:#999999 1px solid ;" colspan="6">';
			$div .= '<table align="center">
						<tr>
							<td style="font-size: 13px;" align="center">N.- INGRESO INVENTARIO:</td>
							<td style="font-size: 13px;" align="center">'.$minv_secu.'</td>
						</tr>
						<tr>
							<td style="font-size: 13px;" align="center">SUCURSAL</td>
							<td style="font-size: 13px;" align="center">'.$sucu.'</td>
						</tr>
					 </table>
					 </th>';
			$div .= '</tr>';
			$div .= '<tr>';			
			$div .= '<th align="center" height="20" style="border:#999999 1px solid ;" colspan="6">';
			$div .= '<table align="left">
						<tr>
							<td style="font-size: 13px;" align="left">FECHA:</td>
							<td style="font-size: 13px;" align="left">'.$fecha.' '.$hora.'</td>
						</tr>
					 </table>
					 </th>';
			$div .= '</tr>';
			$div .= '<tr>';			
			$div .= '<th align="center" height="20" style="border:#999999 1px solid ;" colspan="6">';
			$div .= '<table align="left">
						<tr>
							<td style="font-size: 13px;" align="left">USUARIO:</td>
							<td style="font-size: 13px;" align="left">'.$user.'</td>
						</tr>
						<tr>
							<td style="font-size: 13px;" align="center">Observacion</td>
							<td style="font-size: 13px;" align="center">'.$comentario.'</td>
						</tr>
					 </table>
					 </th>';
			$div .= '</tr>';
			$div .= '<tr>';	
			$div .= '<th align="center" height="10" style="border:#999999 1px solid ;" colspan="6">';
			$div .= '<table align="left">
						<tr>
							<td style="font-size: 13px;" align="left"></td>
							<td style="font-size: 13px;" align="left"></td>
						</tr>
					 </table>
					 </th>';
			$div .= '</tr>';
			
			$div .= '<tr height="25">';
			$div .= '<th style="border:#999999 1px solid ;">N.-</th>';
			$div .= '<th style="border:#999999 1px solid ;">BODEGA</th>';
			$div .= '<th style="border:#999999 1px solid ;">CODIGO PRODUCTO</th>';
			$div .= '<th style="border:#999999 1px solid ;">PRODUCTO</th>';
			$div .= '<th style="border:#999999 1px solid ;">UNIDAD</th>';
			$div .= '<th style="border:#999999 1px solid ;">CANTIDAD</th>';
			$div .= '</tr>';
			
            $i = 1;
            do {
                $bode_orig		= $array_bode[$oIfx->f('dmov_cod_bode')];
                $prod_cod		= $oIfx->f('dmov_cod_prod');
				$prod_nom		= $oIfx->f('prod_nom_prod');
                $bode_envi   	= $array_bode[$oIfx->f('dmov_bod_envi')];
                $unid_nom   	= $oIfx->f('unid_nom_unid');
				$cant       	= $oIfx->f('dmov_can_dmov');
				
				$table_op .='<tr height="20" class="' . $sClass . '"
                                            onMouseOver="javascript:this.className=\'link\';"
                                            onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                $div .= '<tr>';
				$div .= '<td style="font-size: 11px; border:#999999 1px solid ;" align="right">'.$i.'</td>';
				$div .= '<td align="left" style="font-size: 11px; border:#999999 1px solid ;">'.$bode_orig.'</td>';
				$div .= '<td align="left" style="font-size: 11px; border:#999999 1px solid ;">'.$prod_cod.'</td>';
				$div .= '<td align="left" style="font-size: 11px; border:#999999 1px solid ;">'.htmlentities($prod_nom).'</td>';
				$div .= '<td align="left" style="font-size: 11px; border:#999999 1px solid ;">'.$unid_nom.'</td>';
				$div .= '<td align="left" style="font-size: 11px; border:#999999 1px solid ;" align="right">'.$cant.'</td>';
				$div .= '</tr>';
            } while ($oIfx->SiguienteRegistro());
            $div .= '</table>'; //fin table
			//$div .= '</fieldset>';
            $div .= '</div>'; //fin div cabecera			
        }
    }

    $div .= '</div>'; //fin div padre
} else {
    $table = '<div>No exite Transferencia...</div>';
}

//arma pdf
$table.= '<page>';
$table.= $div;
$table.= '</page>';


//echo $table;
$html2pdf = new HTML2PDF('P', 'A4', 'es', 'true', 'UTF-8');
$html2pdf->WriteHTML($table);
$html2pdf->Output('recibo_template.pdf', '');
?>