<?php
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE).'comun.lib.php');
require_once 'html2pdf_v4.03/html2pdf.class.php';
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
// funcion
function fecha_mysql($fecha){
        $fecha_array = explode('/',$fecha);
        $m = $fecha_array[0];
        $y = $fecha_array[2];
        $d = $fecha_array[1];
        return ( $d.'/'.$m.'/'.$y );
}

// conexxion
$oIfx = new Dbo;
$oIfx -> DSN = $DSN_Ifx;
$oIfx -> Conectar();

// datos
$idempresa =  $_SESSION['U_EMPRESA'];
$sucursal =  $_SESSION['U_SUCURSAL'];
$xml = $_SESSION['U_FACT_XML'];
$estab = $_GET['estab'];
$fact_num_preimp = $_GET['codigo'];
$correo = $_GET['correo'];

$table .='<page><fieldset style="border:#999999 1px solid; padding:2px; text-align:center; width:60%;">';
$table .= "<table  align='center' cellpadding='0 cellspacing='2' width='150%' border='0'>";
$table .='<tr>
                <th colspan="4" style="font:Arial;font-family:Arial;font-size:10px">FACTURA No.- '.$estab.'-'.$fact_num_preimp.'</th>
          </tr>';
$table .='<tr>
                <th style="font:Arial;font-family:Arial;font-size:10px">CANT</th>
                <th style="font:Arial;font-family:Arial;font-size:10px">DESCRIPCION</th>
                <th style="font:Arial;font-family:Arial;font-size:10px">C/UNIT</th>
                <th style="font:Arial;font-family:Arial;font-size:10px">TOTAL</th>
          </tr>';
// datos factura
$sql = "SELECT F.FACT_COD_FACT, F.FACT_NOM_CLIENTE,
            F.FACT_FECH_FACT,  F.FACT_RUC_CLIE,
            F.FACT_DIR_CLIE, F.FACT_TLF_CLIENTE,
            F.FACT_IVA, F.FACT_TOT_FACT,
            (F.FACT_TOT_FACT + F.FACT_IVA) AS TOTAL,
            D.DFAC_COD_PROD, P.PROD_NOM_PROD ,  D.DFAC_CANT_DFAC,
            D.DFAC_PRECIO_DFAC, D.DFAC_MONT_TOTAL
            FROM SAEFACT F, SAEDFAC D , SAEPROD P WHERE
            P.PROD_COD_PROD = D.DFAC_COD_PROD AND
            F.FACT_COD_FACT = D.DFAC_COD_FACT AND
            P.PROD_COD_EMPR = $idempresa AND
            P.PROD_COD_SUCU = $sucursal AND
            F.FACT_COD_EMPR = $idempresa AND
            F.FACT_COD_SUCU = $sucursal AND
            D.DFAC_COD_EMPR = $idempresa AND
            D.DFAC_COD_SUCU = $sucursal AND
            F.FACT_NUM_PREIMP = '$fact_num_preimp' ";
unset($array_prod);
$i = 0;
if($oIfx->Query($sql)){
   if($oIfx->NumFilas()>0){
       do{
           $fact_cod_fact = $oIfx->f('fact_cod_fact');
           $fecha = fecha_mysql($oIfx->f('fact_fech_fact'));
           $iva = $oIfx->f('fact_iva');
           $subtotal = $oIfx->f('fact_tot_fact');
           $total = $oIfx->f('total');
           $producto = $oIfx->f('prod_nom_prod');
           $cantidad = $oIfx->f('dfac_cant_dfac');
           $precio = $oIfx->f('dfac_precio_dfac');
           $monto = $oIfx->f('dfac_mont_total');

           $table .='<tr height="20">';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:right">'.$cantidad.'</td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:left">'.$producto.'</td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:right">'.$precio.'</td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:right">'.$monto.'</td>';
           $table .='</tr>';

           $i++;
       }while($oIfx->SiguienteRegistro());
           $table .='<tr height="20">';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px"></td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px"></td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:left">SUBTOTAL USD</td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:right">'.$subtotal.'</td>';
           $table .='</tr>';

           // SUBTOTAL
           $table .='<tr height="20">';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px"></td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:left">SUBTOTAL</td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:right">'.$subtotal.'</td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px"></td>';
           $table .='</tr>';
           // IVA
           $table .='<tr height="20">';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px"></td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:left">IVA 12%</td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:right">'.$iva.'</td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px"></td>';
           $table .='</tr>';
           // TOTAL           
           $table .='<tr height="20">';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px"></td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:left">TOTAL</td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:right">'.($subtotal + $iva).'</td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px"></td>';
           $table .='</tr>';
           // FECHA
           $table .='<tr height="20">';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:left">FECHA</td>';
           $table .='<td style="font:Arial;font-family:Arial;font-size:10px;text-align:left" colspan="2">'.$fecha.'</td>';
           $table .='</tr>';
   }
}
$oIfx->Free();
$table .= "</table>";
$table .= "</fieldset></page>";
$html2pdf = new HTML2PDF('P','A4','fr');
$html2pdf->WriteHTML($table);
$ruta = 'recibo_template'.$fact_num_preimp.'.pdf';
$html2pdf->Output($ruta,'F');

envio_correo_adj_func($correo, 'SISTEMAS CONTABLES INTEGRADOS FLORES NARVAEZ', 'FACTURA'.$fact_num_preimp, $ruta, $xml);

?>
