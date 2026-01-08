<?

function formato_datafast($factcod,$tipo){
         //Definiciones
    global $DSN_Ifx;
    include_once('../../../../Include/config.inc.php');
    include_once(path(DIR_INCLUDE) . 'html2pdf_v4.03/_tcpdf_5.0.002_old/tcpdf.php');

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();


    $idEmpresa = $_SESSION['U_EMPRESA'];

    //DATOS DE LA EMPRESA

    $sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_tel_resp, empr_cod_ciud,empr_path_logo
                                            from saeempr where empr_cod_empr = $idEmpresa ";


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $tel_empresa = $oIfx->f('empr_tel_resp');
            $empr_cod_ciud = $oIfx->f('empr_cod_ciud');
            $empr_path_logo = $oIfx->f('empr_path_logo');
           
        }
    }
    $oIfx->Free();

    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;
    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

    if (file_exists($path_logo_img)) {
        $logo_empresa='<img width="90px;"  src="'.$path_logo_img . '">';
    }
    else{
        $logo_empresa='<div style="color:red;">LOGO NO CARGADO</div>';
    }


    //CIUDAD
    if(empty($empr_cod_ciud)){
        $empr_cod_ciud='NULL';   
    }
    $sql="select ciud_nom_ciud frOm saeciud where ciud_cod_ciud= $empr_cod_ciud";
    $ciudad=consulta_string($sql,'ciud_nom_ciud',$oIfxA,'');

    $sqlFac="select fact_est_fact from saefact where fact_cod_fact=$factcod";
    if ($oIfx->Query($sqlFac)) {
        if ($oIfx->NumFilas() > 0) {
            $estado=$oIfx->f('fact_est_fact');

        }
    
    }
    $oIfx->Free();

//DATOS FACTURA GENERAL

$sql = "select fact_num_preimp, fact_fech_fact, fact_nse_fact, fact_clav_sri, fact_tip_vent from saefact where 
fact_cod_empr = $idEmpresa  and fact_cod_fact = $factcod";
if ($oIfx->Query($sql)) {
    if ($oIfx->NumFilas() > 0) {
        $fact_num_preimp = $oIfx->f('fact_num_preimp');
        $fecha = $oIfx->f('fact_fech_fact');
        $estab = $oIfx->f('fact_nse_fact');
        $fact_clav_sri = $oIfx->f('fact_clav_sri');
        $fact_tip_vent = $oIfx->f("fact_tip_vent");
    }
}
$oIfx->Free();

$sql = "select tcmp_cod_tcmp, tcmp_des_tcmp from saetcmp where tcmp_cod_tcmp = '$fact_tip_vent';";
$tipo_text_fac= consulta_string($sql,'tcmp_des_tcmp', $oIfx,'FACTURA');

$sqlfpag = "select fxfp_cot_fpag from saefxfp where  fxfp_cod_fact= $factcod";
$fpag = consulta_string($sqlfpag, 'fxfp_cot_fpag', $oIfx, '');

$clave_acceso = $fact_clav_sri;

$table .= '<table cellspacing="0" cellpadding="1" style="margin-left:13px;">
    <tr>
                <th style="font-size: 11px;font-family: Arial;text-align: center; " width="230"><b>'.$tipo_text_fac.' No.- ' . $estab . '-' . $fact_num_preimp . '</b></th>
          </tr>';
$table .= '<tr>
      <th style="font-size: 11px;font-family: Arial;text-align: center; " width="230"><b>CLAVE DE ACCESO </b></th>
</tr>';
$table .= '<tr>
      <th style="font-size: 8px;font-family: Arial;text-align: center; " width="230"><b>' . $clave_acceso . '</b></th>
</tr>';
$table .= '<tr>
      <th style="font-size: 10px;font-family: Arial;text-align: center; " width="230">ESTE DOCUMENTO NO TIENE VALIDEZ TRIBUTARIA</th>
</tr>';
$table .= "</table>";
// datos factura
$sql = "SELECT F.FACT_COD_FACT, F.FACT_NOM_CLIENTE,f.fact_cod_usua,
            F.FACT_FECH_FACT,  F.FACT_RUC_CLIE,
            F.FACT_DIR_CLIE, F.FACT_TLF_CLIENTE,
            F.FACT_IVA, F.FACT_TOT_FACT,
            (F.FACT_TOT_FACT + F.FACT_IVA) AS TOTAL,
            D.DFAC_COD_PROD,   
			D.DFAC_CANT_DFAC,
            D.DFAC_PRECIO_DFAC, D.DFAC_MONT_TOTAL,
			f.fact_dsg_valo, D.DFAC_POR_IVA,F.fact_con_miva,F.fact_sin_miva,
			F.fact_iva,F.fact_ice, F.fact_val_irbp, D.dfac_nom_prod,D.dfac_por_dsg, D.dfac_des1_dfac
            FROM SAEFACT F, SAEDFAC D  WHERE
            F.FACT_COD_FACT = D.DFAC_COD_FACT AND
            F.FACT_COD_EMPR = $idEmpresa AND
            D.DFAC_COD_EMPR = $idEmpresa AND
            F.FACT_COD_FACT = '$factcod'";
unset($array_prod);
$i = 0;
if ($oIfx->Query($sql)) {
    if ($oIfx->NumFilas() > 0) {
        $fact_nom_cliente = $oIfx->f('fact_nom_cliente');
        $fact_ruc_clie = $oIfx->f('fact_ruc_clie');
        $fact_dir_clie = $oIfx->f('fact_dir_clie');
        $fact_tlf_cliente = $oIfx->f('fact_tlf_cliente');

        $fact_cod_usua = $oIfx->f('fact_cod_usua');

        $fact_con_miva = $oIfx->f('fact_con_miva');
        $fact_sin_miva = $oIfx->f('fact_sin_miva');
        $fact_iva = $oIfx->f('fact_iva');
        $fact_ice = $oIfx->f('fact_ice');
        $fact_val_irbp = $oIfx->f("fact_val_irbp");

        $sqlUS = "select USUARIO_USER from comercial.usuario where USUA_COD_USUA = $fact_cod_usua";
        // $user_nom_web = consulta_string($sql, 'USUARIO_USER', $oCnx, '');
        $user_nom_web = consulta_string($sqlUS, 'usuario_user', $oIfxA, '');
        // var_dump($sqlUS);

        $fecha = ($oIfx->f('fact_fech_fact'));
        //$descts=$oIfx->f('fact_dsct_soli');

        $table .= '<table cellspacing="0" cellpadding="1" style="margin-left:13px;margin-top:5px;">';
        $table .= '<tr>';
        $table .= '<td style="font-size: 10px;font-family: Arial;text-align: left; " width="230"><b>CLIENTE: </b>' . $fact_nom_cliente . '</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<td style="font-size: 10px;font-family: Arial;text-align: left; " width="230"><b>IDENTIF:</b> ' . $fact_ruc_clie . '</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<td style="font-size: 10px;font-family: Arial;text-align: left; " width="230"><b>DIRECC:</b> ' . substr($fact_dir_clie, 0, 45) . '</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<td style="font-size: 10px;font-family: Arial;text-align: left; " width="230"><b>TLF:</b> ' . $fact_tlf_cliente . '</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<td style="font-size: 10px;font-family: Arial;text-align: left; " width="230"><b>FECHA:</b> ' . $fecha . '</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<td style="font-size: 10px;font-family: Arial;text-align: left; " width="230"><b>VEND:</b>' . $user_nom_web . '</td>';
        $table .= '</tr>';

        $table .= "</table>";
        $table .= '<table cellspacing="0" cellpadding="1" style="margin-left:13px;margin-top:5px;">';
        $table .= '<tr>
                <th style="font:Arial;font-family:Arial;font-size:10px; width:30px;">CANT</th>
                <th style="font:Arial;font-family:Arial;font-size:10px; width:90px;">DESCRIPCION</th>
                <th style="font:Arial;font-family:Arial;font-size:10px; width:33px;">C/UNIT</th>
                <th style="font:Arial;font-family:Arial;font-size:10px; width:33px;">DES%</th>
                <th style="font:Arial;font-family:Arial;font-size:10px; width:33px;">TOTAL</th>
			</tr>';
        do {
            $fact_cod_fact = $oIfx->f('fact_cod_fact');
            $iva = $oIfx->f('fact_iva');
            $subtotal = $oIfx->f('fact_tot_fact');
            $total = $oIfx->f('total');
            //$producto = $oIfx->f('prod_nom_prod');
            $cantidad = $oIfx->f('dfac_cant_dfac');
            $precio = $oIfx->f('dfac_precio_dfac');
            $monto = $oIfx->f('dfac_mont_total');
            $fact_dsg_valo = $oIfx->f('fact_dsg_valo');
            $iva_msn = $oIfx->f('dfac_por_iva');

            $dfac_cod_prod = $oIfx->f('dfac_cod_prod');
            $dfac_nom_prod = $oIfx->f('dfac_nom_prod');

            $dfac_por_dsg = $oIfx->f('dfac_por_dsg');
            $dfac_des1_dfac = $oIfx->f('dfac_des1_dfac');
            if ($dfac_por_dsg > 0) {
                $descuento1 = ($precio * $cantidad * $dfac_des1_dfac) / 100;
                $descuento2 = 0;
                $descuento3 = 0;

                $monto = number_format((($precio * $cantidad) - ($descuento1 + $descuento2 + $descuento3)), 2, '.', '');

            }


            $table .= '<tr >';
            $table .= '<td style="font:Arial;font-family:Arial;font-size:10px;text-align:left; width:30px;">' . number_format($cantidad, 2) . '</td>';
            $table .= '<td style="font:Arial;font-family:Arial;font-size:10px;text-align:left; width:90px;">' . $dfac_cod_prod . '</td>';
            $table .= '<td style="font:Arial;font-family:Arial;font-size:10px;text-align:center; width:33px;">' . number_format($precio, 2, '.', ',') . '</td>';
            $table .= '<td style="font:Arial;font-family:Arial;font-size:10px;text-align:center; width:33px;">' . number_format($dfac_des1_dfac, 2, '.', ',') . '</td>';
            $table .= '<td style="font:Arial;font-family:Arial;font-size:10px;text-align:center; width:33px;">' . number_format($monto, 2, '.', ',') . '</td>';
            $table .= '</tr>';

            $i++;
        } while ($oIfx->SiguienteRegistro());
        $table .= "</table>";

        $table .= '<table cellspacing="0" cellpadding="1" style="margin-left:13px;margin-top:5px;">';
        $table .= '<tr>
            <td colspan="2" style="border-top: 1px solid black;" width="230"></td>
        </tr>
        <tr >';
        $table .= '<td style="font:Arial;font-family:Arial;font-size:11px;text-align:left" width="150"><b>SUBTOTAL USD</b></td>';
        $table .= '<td style="font:Arial;font-family:Arial;font-size:11px;text-align:right" width="80">' . number_format($fact_con_miva + $fact_sin_miva + $fact_dsg_valo, 2, '.', ',') . '</td>';
        $table .= '</tr>';

        // DSCTO
        $table .= '<tr >';
        $table .= '<td style="font:Arial;font-family:Arial;font-size:11px;text-align:left" width="150"><b>DSCTO</b></td>';
        $table .= '<td style="font:Arial;font-family:Arial;font-size:11px;text-align:right" width="80">' . number_format($fact_dsg_valo, 2, '.', ',') . '</td>';
        $table .= '</tr>';


        // IVA
        $table .= '<tr>';
        $table .= '<td style="font:Arial;font-family:Arial;font-size:11px;text-align:left" width="150"><b>IVA ' . round($iva_msn) . '%</b></td>';
        $table .= '<td style="font:Arial;font-family:Arial;font-size:11px;text-align:right" width="80">' . number_format($iva, 2, '.', ',') . '</td>';
        $table .= '</tr>';
        // TOTAL
        $table .= '<tr >';
        $table .= '<td style="font:Arial;font-family:Arial;font-size:11px;text-align:left"width="150" ><b>TOTAL</b></td>';
        $table .= '<td style="font:Arial;font-family:Arial;font-size:11px;text-align:right" width="80">' . number_format($fact_con_miva + $fact_sin_miva + $fact_iva + $fact_ice + $fact_val_irbp, 2, '.', ',') . '</td>';
        $table .= '</tr>';

        if($fpag){
            $table .= '<tr>';
            $table .= '<td style="font:Arial;font-family:Arial;font-size:11px;text-align:left" width="150"><b>F PAGO</b></td>';
            $table .= '<td style="font:Arial;font-family:Arial;font-size:11px;text-align:right" width="80">' . $fpag . '</td>';
            $table .= '</tr>';
        }

        $table .= "</table>";
    }
}
$oIfx->Free();
   

    //DATOS FORMA DE PAGO TABLA SAEFXFP

    $ctrl_debito=0;
    $documento='';

    $sqlfx="select fxfp_dws_tran, fxfp_dws_caja,fxfp_dws_send, fxfp_dws_result, fxfp_dws_tran from saefxfp where fxfp_cod_fact=$factcod 
    and fxfp_cod_fpag in (select fpag_cod_fpag from saefpag where fpag_cot_fpag='TAR' and fpag_des_fpag='DATAFAST' and fpag_cod_empr=$idEmpresa)";

    if ($oIfx->Query($sqlfx)) {
        if ($oIfx->NumFilas() > 0) {

            do{

            $tran_caja=$oIfx->f('fxfp_dws_tran');

            if(empty($tran_caja)){
                $tran_caja='NULL';
            }

            //DATOS ENVIADOS
            $data_env=json_decode($oIfx->f('fxfp_dws_send',false));
            $fecha_hora=$data_env->fecha_hora;

            //VAORES
            $iva=$data_env->iva;
            $base0=$data_env->base0;
            $baseImp=$data_env->baseImp;
            $total=$data_env->total;
            $cuotas=$data_env->cuotas;
            $tipo_transaccion=$data_env->tipo_transaccion;
            $tipo_credito=$data_env->tipo_credito;

            

            //RESPUESTA

            $data_res=json_decode($oIfx->f('fxfp_dws_result',false));
            $cliente=$data_res->tarjetaHabiente;
            $lote=$data_res->lote;
            $referencia=$data_res->referencia;
            $numeroTarjeta=$data_res->numeroTajeta;
            $autorizacion=$data_res->autorizacion;
            $nombre_tarjeta=$data_res->filler1;
            $cod_aid=$data_res->aid;
            $tag=$data_res->aplicacionEMV;

            $modoLectura=$data_res->modoLectura;
            if($modoLectura=='06'){
                $modoLectura='CTLS';
            }
            elseif($modoLectura=='05'){
                $modoLectura='FALL BANDA';
            }
            elseif($modoLectura=='04'){
                $modoLectura='FALL MANUAL';
            }
            elseif($modoLectura=='03'){
                $modoLectura='CHIP';
            }
            elseif($modoLectura=='01'){
                $modoLectura='TOKEN';
            }

            $publicidad=$data_res->publicidad;
            if(!empty($publicidad)){
                $detalle_publicidad='***'.$data_res->publicidad.'***';
            }
            

            
            $nombreAdquirente=$data_res->nombreAdquirente;
            $interes=$data_res->interes;
            if(empty($interes)){
                $interes=0;
            }

             //VALIDAICON DEBITO CORRIENTE

             if($tipo_transaccion=='01'&&$tipo_credito=='00'){
                $ctrl_debito++;
            }

            $fecha_dat=date('d',strtotime($fecha_hora)).'/'.strtoupper(substr(nomb_mes(date('m',strtotime($fecha_hora))),0,3)).'/'.date('Y',strtotime($fecha_hora));
            $hora_dat=date('H:i',strtotime($fecha_hora));


            $sql_caj="select 
            caja.mid,
            caja.tid
            from datafast.transacciones_caja tran inner join datafast.configuracion_caja caja on caja.id = tran.caja_id
            where tran.id =$tran_caja";
    
            $mid=consulta_string($sql_caj,'mid',$oIfxA,'');
            $tid=consulta_string($sql_caj,'tid',$oIfxA,'');
            //VALIDACION PAGO CREDITO DIFERIDO
            if($cuotas!=0){
                $con_sin='';
                if($interes!=0){
                   $con_sin='<b>DIF CON INTERES</b>';
                }
                else{
                    $con_sin='<b>DIF SIN INTERES</b>';
                }
                
                
                $cuotas=intval($cuotas);
                
                $diferido='<tr>
                <td style="font-size: 11px;font-family: Arial; text-align: left;" width="150">'.$con_sin.'</td>
                <td style="font-size: 11px;font-family: Arial; text-align: right;" width="80">MESES: '.$cuotas.'</td>
                </tr>';
            }
            else{
                $diferido='';   
            }
    
            //VALIDACION INTERES
            if($interes!=0){
    
                $tam=strlen($interes);
    
                $parte_entera=intval(substr($interes,0,($tam-2)));
                $parte_decimal=intval(substr($interes,($tam-2),$tam));
                $interes=$parte_entera.'.'.$parte_decimal;
                
                $td_interes='<tr>
                <td style="font-size: 10px;font-family: Arial; text-align: left;" width="145"><b>INTERES</b></td>
                <td style="font-size: 10px;font-family: Arial; text-align: left;" width="5"><b>:US$</b></td>
                <td style="font-size: 10px;font-family: Arial; text-align: right;" width="75"><b>$ '.number_format($interes,2).'</b></td>
                </tr>
                <tr>
                <td style="font-size: 10px;font-family: Arial; text-align: left;" width="145">GRAN TOTAL</td>
                <td style="font-size: 10px;font-family: Arial; text-align: left;" width="5">:US$</td>
                <td style="font-size: 10px;font-family: Arial; text-align: right;" width="75">$ '.number_format(($total+$interes),2).'</td>
                </tr>';
    
            }
            else{
                $td_interes='';
            }
    
                //FORMATO
        $cabecera = '<table cellspacing="0" cellpadding="1" style="margin-left:12px;">
        <tr>
                        <td align="left" width="230">
                        '.$logo_empresa.'
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 11px;font-family: Arial;text-align: center; " width="230"><br><b>' . htmlentities($razonSocial) . '</b></td>
                    </tr>
                    <tr>
                        <td  style="font-size: 11px;font-family: Arial;text-align: center;" width="230"><b>RUC:' . $ruc_empr . '</b></td>
                    </tr>
                    <tr>
                        <td style="font-size: 11px;font-family: Arial; text-align: center;" width="230">DIRECCION '.$dirMatriz.'</td>
                    </tr>
                    <tr>
                        <td style="font-size: 11px;font-family: Arial; text-align: center;" width="230">Teléfono '.$tel_empresa.'</td>
                    </tr>
                    <tr>
                        <td style="font-size: 11px;font-family: Arial; text-align: center;" width="230">'.$ciudad.'</td>
                    </tr>';
        $lectura='<tr>
                    <td style="font-size: 11px;font-family: Arial; text-align: center;" width="230">'.$mid.' - '.$tid.' -'.$modoLectura.'</td>
                    </tr>';
        $adquiriente='<tr>
                    <td style="font-size: 11px;font-family: Arial; text-align: center;" width="230"><br><b>'.$nombre_tarjeta.'</b><br></td>
                    </tr>';
        $fin_cabecera ='</table>';
    
        $datos_tar='<table cellspacing="0" cellpadding="1" style="margin-top:10px;margin-left:12px;" >
                    <tr>
                    <td style="font-size: 11px;font-family: Arial; text-align: left;" width="150">TARJETA:'.$numeroTarjeta.'</td>
                    <td style="font-size: 11px;font-family: Arial; text-align: right;" width="80">V:XX/XX</td>
                    </tr>
                    <tr>
                    <td style="font-size: 11px;font-family: Arial; text-align: left;" width="150">LOTE#: '.$lote.'</td>
                    <td style="font-size: 11px;font-family: Arial; text-align: right;" width="80">REF: '.$referencia.'</td>
                    </tr>';
    
        $adquiriente_tar='<tr>
                    <td style="font-size: 11px;font-family: Arial; text-align: left;" width="150">ADQUIRIENTE:</td>
                    <td style="font-size: 11px;font-family: Arial; text-align: right;" width="80">'.$nombreAdquirente.'</td>
                    </tr>';
        $fecha='<tr>
                    <td style="font-size: 11px;font-family: Arial; text-align: left;" width="150">FECHA: '.$fecha_dat.'</td>
                    <td style="font-size: 11px;font-family: Arial; text-align: right;" width="80">HORA: '.$hora_dat.'</td>
                    </tr>
                    '.$diferido.'
                    ';
            $aprobacion='<tr>
                    <td style="font-size: 11px;font-family: Arial; text-align: center;" colspan="2"><br>APROBACION#&nbsp;&nbsp;&nbsp;'.$autorizacion.'</td>
                    </tr>';
            $fin_datos_tar='</table>';
    
            $detalle='<table cellspacing="0" cellpadding="1" style="margin-top:10px;margin-left:12px;">
                    <tr>
                    <td style="font-size: 10px;font-family: Arial; text-align: left;" width="145">BASE CONSUMO TARIFA 12</td>
                    <td style="font-size: 10px;font-family: Arial; text-align: left;" width="5">:US$</td>
                    <td style="font-size: 10px;font-family: Arial; text-align: right;" width="75">$ '.number_format($baseImp,2).'</td>
                    </tr>
                    <tr>
                    <td style="font-size: 10px;font-family: Arial; text-align: left;" width="145">BASE CONSUMO TARIFA 0</td>
                    <td style="font-size: 10px;font-family: Arial; text-align: left;" width="5">:US$</td>
                    <td style="font-size: 10px;font-family: Arial; text-align: right;" width="75">$ '.number_format($base0,2).'</td>
                    </tr>
                    <tr>
                    <td style="font-size: 10px;font-family: Arial; text-align: left;" width="145">SUBTOTAL CONSUMOS</td>
                    <td style="font-size: 10px;font-family: Arial; text-align: left;" width="5">:US$</td>
                    <td style="font-size: 10px;font-family: Arial; text-align: right;" width="75">$ '.number_format(($baseImp+$base0),2).'</td>
                    </tr>
                    <tr>
                    <td style="font-size: 10px;font-family: Arial; text-align: left;" width="145">IVA</td>
                    <td style="font-size: 10px;font-family: Arial; text-align: left;" width="5">:US$</td>
                    <td style="font-size: 10px;font-family: Arial; text-align: right;" width="75">$ '.number_format($iva,2).'</td>
                    </tr>
                    <tr>
                    <td style="font-size: 10px;font-family: Arial; text-align: left;" width="145">VR. TOTAL</td>
                    <td style="font-size: 10px;font-family: Arial; text-align: left;" width="5">:US$</td>
                    <td style="font-size: 10px;font-family: Arial; text-align: right;" width="75"><b>$ '.number_format($total,2).'</b></td>
                    </tr>
                    '.$td_interes.'
                    </table>';

                //VALIDACION TEXTO VENTA CORRIENTE DEBITO
                //if($ctrl_debito>0){
                    $declaracion='<p align="justify">DEBO Y PAGARE AL  EMISOR  INCONDICIONALMENTE    Y   SIN    PROTESTO  EL   TOTAL   DE  ESTE    PAGARE
                    MAS LOS INTERESES Y CARGOS POR SERVICIO, EN CASO DE MORA PAGARE LA TASA MAXIMA AUTORIZADA PARA EL EMISOR.
                    <br>DECLARO QUE EL PRODUCTO DE LA TRANSACCION NO SERA UTILIZADO EN ACTIVIDADES DE LAVADO DE ACTIVOS,
                    FINANCIAMIENTO DEL TERRORISMO Y OTROS DELITOS</p>';

                    /*$declaracion='<div style="font-size: 11px;font-family: Arial; width:230; text-align: justify;margin-top:20px;">
                    <span><font style="color:red">DECLARO QUE EL PRODUCTO  DE  LA TRANSACCION NO  SERA  UTILIZADO  EN  ACTIVIDADES  DE  LAVADO
                    DE ACTIVOS, FINANCIAMIENTO DEL TERRORISMO Y OTROS DELITOS.</font></span>
                    </div>';*/
                //}
               /* else{
                   
                }*/
                $firma='<br>
                    <div style="font-size: 11px;font-family: Arial; width:230; text-align: center;margin-left:12px;">
                    <span><font style="color:red">CAP  ELEC   DATAFAST</font></span>
                    </div>
                
                    <div style="font-size: 11px;font-family: Arial; width:230; text-align: left;margin-left:12px;">  
                    '.$declaracion.'
                    <p align="justify" >
                    NOMBRE&nbsp;&nbsp;&nbsp;: '.$cliente.'
                    <br>x_____________________________________<br><br>
                    EL ESTABLECIMIENTO VERIFICA QUE LA FIRMA DEL CLIENTE ES AUTENTICA<br><br>
                    C.I.:___________________________________<br>               
                    <div style="margin-top:3px;">TELEFONO____________________________</div>
                    <div style="margin-top:20px;">'.$tag.'<br>AID : '.$cod_aid.'</div>
                    </p>
                    <p  align="center">
                    ORIGINAL<br><br>
                    '.$detalle_publicidad.'
                    </p>
                    </div>';
    
                    $nombre='<div style="margin-top:15px;font-size: 11px;font-family: Arial; width:230;margin-left:12px;">
                    NOMBRE&nbsp;&nbsp;&nbsp;: '.$cliente.'
                    </div>';

                //VALIDACION VR FASTCLUB Y VR PACIFICARD
                if($publicidad=='016'){
                
                    $firma='<br>
                    <div style="font-size: 11px;font-family: Arial; width:230; text-align: center;margin-left:12px;">
                    <span><font style="color:red">CAP  ELEC   DATAFAST</font></span>
                    </div>
                
                    <div style="font-size: 11px;font-family: Arial; width:230; text-align: left;margin-left:12px;">  
                    <p  align="center">
                    <b>*VOUCHER SIN FIRMA*</b><br><b>*LO HACEMOS POR TI*</b><br>
                    ORIGINAL
                    </p>
                    </div>';
                    $nombre='';               
                }
                if($publicidad=='025'){
           
                    $firma='<br>
                    <div style="font-size: 11px;font-family: Arial; width:230; text-align: center;margin-left:12px;">
                    <span><font style="color:red">CAP  ELEC   DATAFAST</font></span>
                    </div>
                
                    <div style="font-size: 11px;font-family: Arial; width:230; text-align: left;margin-left:12px;">  
                    <p  align="center">
                    <b>***PACIFICARD***</b><br><b>*NO&nbsp;&nbsp;&nbsp;REQUIERE&nbsp;&nbsp;&nbsp;FIRMA*</b><br>
                    ORIGINAL
                    </p>
                    </div>';             
                }
    
                
                    //ORIGINAL
                $original='<div style="width: 230; margin: 7px; text-align: left;" >';
                //VALIDACION FACTURAS ANULADAS
                if($estado=='AN'){

                    $anulacion='<tr>
                    <td style="font-size: 11px;font-family: Arial; text-align: center;" colspan="2"><br><b>ANULACION</b></td>
                    </tr>';
                    $detalle='<table cellspacing="0" cellpadding="1" style="margin-top:10px;margin-left:12px;">
                    <tr>
                    <td style="font-size: 10px;font-family: Arial; text-align: left;" width="145">VR. TOTAL</td>
                    <td style="font-size: 10px;font-family: Arial; text-align: left;" width="5">:US$</td>
                    <td style="font-size: 10px;font-family: Arial; text-align: right;" width="75"><b>-$ '.number_format($total,2).'</b></td>
                    </tr>
                    </table>';

                    $firma='<br> <div style="font-size: 11px;font-family: Arial; width:230; text-align: left;margin-left:12px;">  
                    <div style="margin-top:20px;">'.$tag.'<br>AID : '.$cod_aid.'</div>
                    <p  align="center"><b>ORIGINAL</b></p></div>';

                }
                else{
                    $anulacion='';
                }

                $original.=$cabecera.$lectura.$adquiriente.$fin_cabecera.$datos_tar.$adquiriente_tar.$fecha.$anulacion.$aprobacion.$fin_datos_tar.$detalle.$firma;

                $original.='</div>';
                
                //CLIENTE
                $cliente='<div style="width: 230; margin: 7px; text-align: left;" >';
                if($publicidad=='016'||$publicidad=='025'){
                    $detalle_publicidad='';
                }
                if($estado=='AN'){

                    $anulacion='ANULACION<br>';
                    $firma='<br> <div style="font-size: 11px;font-family: Arial; width:230; text-align: left;margin-left:12px;">  
                    <p  align="center"><b>CLIENTE</b></p></div>';
                    $cliente.=$cabecera.$adquiriente.$fin_cabecera.$datos_tar.$fecha.$fin_datos_tar.'<div style="font-size: 11px;font-family: Arial; width:255; text-align: center; margin-top:10px"><b>'.$anulacion.'DATAFAST</b></div>'.$detalle.$firma;

                }
                else{

                    
                    $anulacion='';
                    $cliente.=$cabecera.$adquiriente.$fin_cabecera.$datos_tar.$fecha.$fin_datos_tar.'<div style="font-size: 11px;font-family: Arial; width:255; text-align: center; margin-top:10px"><b>'.$anulacion.'DATAFAST</b></div>'.$detalle;
                    $cliente.=$nombre.'<div style="font-size: 11px;font-family: Arial; width:230; text-align: center;margin-top:10px;margin-left:12px;">
                    <span><font style="color:red">CLIENTE</font></span><br><br>'.$detalle_publicidad.'
                </div>
                ';
                }
                $cliente.='</div>';
                
                //VALIDACION TIPO DE IMPRESION
                
                if($tipo==1){
                    $documento.=$original;    
                }
                elseif($tipo==2){
                    $documento.=$cliente;
                }
                else{
                    $documento=$cabecera.$fin_cabecera.$table.$original;
                }
                
        
        }while ($oIfx->SiguienteRegistro());
      }
      else{
        $documento='<div style="text-align:center;color:red"><span>FORMA DE PAGO NO CORRESPONDE A DATAFAST, VERIFIQUE LA CONFIGURACION</span></div>';
      }
    
    }
    $oIfx->Free();


    return $documento;

}


?>