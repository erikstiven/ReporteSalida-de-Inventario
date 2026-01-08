<?

include_once('../../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');


$idEmpresa=$dato = $_REQUEST['empresa'];
$idSucursal=$dato = $_REQUEST['sucursal'];
$ejer=$_REQUEST['ejer'];
$mes=$_REQUEST['mes'];
$asto=$_REQUEST['asto'];
$fac=$_REQUEST['fac'];
$valor=$_REQUEST['valor'];


if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

global $DSN_Ifx, $DSN;

// conexxion

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();


$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();


//DATOS DE LA EMPRESA
$sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_cod_pais,
                empr_num_resu, empr_path_logo, empr_iva_empr , empr_num_dire, empr_fax_empr,
                empr_sitio_web, empr_cm1_empr, empr_cm2_empr , empr_prec_sucu, empr_tel_resp, empr_mai_empr, empr_ema_comp
                from saeempr where empr_cod_empr = $idEmpresa ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_path_logo');
            $empr_cod_pais = $oIfx->f('empr_cod_pais');
        }
    }
    $oIfx->Free();

    //selecciona sucursales y direcciones
    $sql_sucu = "select sucu_nom_sucu, sucu_dir_sucu from saesucu where sucu_cod_empr = $idEmpresa and sucu_cod_sucu = $idSucursal ";
        if ($oIfx->Query($sql_sucu)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
                    $sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');
                } while ($oIfx->SiguienteRegistro());
            }
        }
    $oIfx->Free();

    $sql_sucu = "SELECT identificacion from comercial.tipo_iden_clpv_pais where pais_cod_pais = $empr_cod_pais and id_iden_clpv = 1";
    if ($oIfx->Query($sql_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $tip_ruc_pais = $oIfx->f('identificacion');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    //VALIDAICON LOGO

    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;


    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

    if (file_exists($path_logo_img)) {
        $logo_empresa=' <img width="170px;"  src="' . $path_logo_img . '">';
    }
    else{
        $logo_empresa='<div style="color:red;">LOGO NO CARGADO</div>';
    }


    ///VALIDACION MONEDA
    $sqlmon="select pcon_mon_base, pcon_seg_mone from saepcon where pcon_cod_empr=$idEmpresa";
    $pcon_seg_mone= consulta_string($sqlmon,'pcon_seg_mone', $oIfxA,'');

   

    //DATOS DE LA CANCELACION


    $sql = "  SELECT
                saedmcc.dmcc_mon_ext,
				 saedmcc.dmcc_fec_emis,
				 saedmcc.dmcc_cod_tran,
				 saedmcc.dmcc_fec_ven,
				 saedmcc.dmcc_cod_asto,
				 saedmcc.dmcc_num_fac,
				 saedmcc.dmcc_cod_ejer,
                 saedmcc.dmcc_num_reci,
				 DATE_PART('month', saedmcc.dmcc_fec_emis) as mes,
				 c.clpv_nom_clpv,
                 c.clpv_cod_clpv, 
                 c.clpv_ruc_clpv, 
				 saedmcc.dmcc_det_dmcc,
				 saedmcc.dmcc_deb_ml,
				 saedmcc.dmcc_cre_ml,
				 sucu_nom_sucu,
				 sucu_cod_sucu,
				 saedmcc.dmcc_cod_vend,
				 saedmcc.dmcc_cod_cobr,
				 saedmcc.dmcc_cod_empr,
				 saedmcc.dmcc_cod_mone

			FROM saeclpv c,
				 saedmcc,
				 saesucu
		   WHERE ( saesucu.sucu_cod_sucu = saedmcc.dmcc_cod_sucu ) and
				 ( c.clpv_cod_clpv = saedmcc.clpv_cod_clpv ) and
				 ( c.clpv_cod_empr = saedmcc.dmcc_cod_empr )  and
				 c.clpv_cod_empr = $idEmpresa and
                 saedmcc.dmcc_cod_sucu=$idSucursal and
                 saedmcc.dmcc_cod_asto='$asto' and
                 saedmcc.dmcc_cod_ejer =$ejer and
                 saedmcc.dmcc_num_fac='$fac' and
                 saedmcc.dmcc_cod_tran like 'CAN%' and
                 DATE_PART('month', saedmcc.dmcc_fec_emis)='$mes'";

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {

            $clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
            $dmcc_fec_emis = date('d-m-Y',strtotime($oIfx->f('dmcc_fec_emis')));
            $id_clpv = $oIfx->f('clpv_cod_clpv');
            $ruc_clpv = $oIfx->f('clpv_ruc_clpv');
            $dmcc_cod_mone = $oIfx->f('dmcc_cod_mone');
            $dmcc_cod_empr = $oIfx->f('dmcc_cod_empr');
            $dmcc_det_dmcc = $oIfx->f('dmcc_det_dmcc');
            $dmcc_num_reci = $oIfx->f('dmcc_num_reci');
            if($dmcc_num_reci==0){
                $dmcc_num_reci ='';
            }

            $dmcc_mon_ext = $oIfx->f('dmcc_mon_ext');
            if(empty($dmcc_mon_ext)){
                $dmcc_mon_ext=0;
            }


            $dmcc_mon_ext = abs($oIfx->f('dmcc_mon_ext'));

             //DATOS DE LA FACTURA
            $array_fac=explode('-',trim($fac));
            $ser_fac=$array_fac[0];
            $num_fac=$array_fac[1];

            $cadena_buscada   = 'B';
            $posicion_coincidencia = strpos($ser_fac, $cadena_buscada);
            if (!empty($posicion_coincidencia)) {
                $titulo="BOLETA";
                
            }else{
                $titulo="FACTURA";
            }
            
            $sqlf="select dfac_cod_mes, dfac_nom_prod, dfac_det_dfac, fact_cod_mone from saedfac, saefact 
            where dfac_cod_fact=fact_cod_fact and dfac_cod_empr=fact_cod_empr and dfac_cod_empr=$idEmpresa
            and fact_num_preimp='$num_fac' and fact_nse_fact='$ser_fac' and fact_cod_clpv=$id_clpv";
            if ($oIfxA->Query($sqlf)) {
                if ($oIfxA->NumFilas() > 0) {
                    do{
                        $servicio=$oIfxA->f('dfac_nom_prod');
                        $dfac_cod_mes       = $oIfxA->f('dfac_cod_mes');
                        $dfac_det_dfac =    $oIfxA->f('dfac_det_dfac');
                        $fact_cod_mone =    $oIfxA->f('fact_cod_mone');

                        



                        $mes_pago = '';
                    if( !empty($dfac_cod_mes) ){
                        $sql = "SELECT c.mes, c.anio FROM  isp.contrato_pago c WHERE
                                            c.id IN ( $dfac_cod_mes  )  ";
                        $mes_pago = 'MENSUAL: ';
                        if ($oCon->Query($sql)) {
                            if ($oCon->NumFilas() > 0) {
                                do {
                                    $mes_pago = Mes_func($oCon->f('mes')) .'/'.$oCon->f('anio');
                                    $anio_pago =$oCon->f('anio');

                                } while ($oCon->SiguienteRegistro());
                            }
                        }
                        $oCon->Free();
                    }
                        $detalle_pago= $servicio.'<br>'.$dfac_det_dfac;


                    }while ($oIfx->SiguienteRegistro());
                }
                else{
                    $fact_cod_mone ='';
                }
            }
            $oIfxA->Free();

            if($fact_cod_mone==$pcon_seg_mone){
                
                $total_valor=number_format($dmcc_mon_ext, 2, '.', '').' (<font style="font-size:6px;">'.number_format($valor, 2, '.', '').'</font>)';
                $valor=$dmcc_mon_ext;
            }
            else{
                $total_valor=$valor;
            }

            if(empty($fact_cod_mone)){
                $fact_cod_mone=$dmcc_cod_mone;
            }
            if(empty($fact_cod_mone)){
                $fact_cod_mone='NULL';
            }




            $sql_mone="SELECT mone_des_mone from saemone where mone_cod_mone=$fact_cod_mone and mone_cod_empr='$idEmpresa'";
			$moneda = consulta_string_func($sql_mone, 'mone_des_mone', $oIfxA, '');


            $sql = "SELECT clv_con_clpv from saeclpv where clpv_cod_clpv = $id_clpv";
                        $clv_con_clpv = consulta_string_func($sql, 'clv_con_clpv', $oIfxA, '');

                        if(strlen($clv_con_clpv)==0){
                            $clv_con_clpv = 2;
                        }
                        
                        //TIPO DE IDENTIFICACION DEL CLIENTE
                        $sql_sucu = "SELECT identificacion from comercial.tipo_iden_clpv_pais where pais_cod_pais = $empr_cod_pais and id_iden_clpv = '$clv_con_clpv'";
                        if ($oIfxA->Query($sql_sucu)) {
                            if ($oIfxA->NumFilas() > 0) {
                                do {
                                    $tip_iden_cliente = $oIfxA->f('identificacion');
                                } while ($oIfxA->SiguienteRegistro());
                            }
                        }
                        $oIfxA->Free();

            //DATOS DE LA FORMA DE PAGO

            $sql_dpag="SELECT dpag_cod_fpag,dpag_num_cheq,dpag_cod_user, dpag_nom_banc, dpag_con_fila from saedpag where dpag_cod_empr='$idEmpresa' 
			and dpag_cod_sucu='$idSucursal' and dpag_cod_asto='$asto' and asto_num_prdo=$mes and dpag_cod_ejer=$ejer";
			if ($oIfxA->Query($sql_dpag)) {
				if ($oIfxA->NumFilas() > 0) {
					$i = 1;
					do {
		
						$dpag_cod_fpag = $oIfxA->f('dpag_cod_fpag');
						$dpag_num_cheq = $oIfxA->f('dpag_num_cheq');
						$dpag_cod_user = $oIfxA->f('dpag_cod_user');
                        $dpag_nom_banc = $oIfxA->f('dpag_nom_banc');
                        $dpag_con_fila = $oIfxA->f('dpag_con_fila');
                        $num_comp    = cero_mas_func('0', 9 - strlen($dpag_con_fila)).$dpag_con_fila;
				  		$i++;
					} while ($oIfxA->SiguienteRegistro());
				}
			}
            $oIfxA->Free();
            if(empty($dpag_cod_user)){
                $dpag_cod_user='NULL';
            }

            $sqlusua = "SELECT usuario_nombre, usuario_apellido, usuario_user from comercial.usuario where usuario_id = $dpag_cod_user";
            $nombre_usuario=consulta_string($sqlusua,'usuario_user',$oIfxA,'');

            if(empty($dpag_cod_fpag)){
                $dpag_cod_fpag='NULL';
            }
            $sqlf="select fpag_des_fpag from saefpag where fpag_cod_fpag=$dpag_cod_fpag and fpag_cod_empr=$idEmpresa";
            $forma_pago=consulta_string($sqlf,'fpag_des_fpag',$oIfxA,'');

            //DATOS DE LA CUENTA

            $sql = "select ctab_cod_cuen, ctab_num_ctab, banc_nom_banc, ctab_tip_ctab
                                                            from saectab, saebanc
                                                            where ctab_cod_banc = banc_cod_banc and
                                                            ctab_cod_empr = banc_cod_empr and
                                                            ctab_cod_empr = $idEmpresa and ctab_cod_cuen='$dpag_nom_banc'";
                                if ($oIfxA->Query($sql)) {
                                    if ($oIfxA->NumFilas() > 0) {
                                        do {
                                            $ctab_cod_cuen = $oIfxA->f('ctab_cod_cuen');
                                            $ctab_num_ctab = $oIfxA->f('ctab_num_ctab');
                                            $banc_nom_banc = $oIfxA->f('banc_nom_banc');
                                            $ctab_tip_ctab = $oIfxA->f('ctab_tip_ctab');

                                            $cuenta_banco = '';
                                            if ($ctab_tip_ctab == 'A') {
                                                $cuenta_banco = 'AHORROS';
                                            } elseif ($ctab_tip_ctab == 'C') {
                                                $cuenta_banco = 'CORRIENTE';
                                            }

                                            
                                        } while ($oIfx->SiguienteRegistro());
                                    }
                                }
                                $oIfxA->Free();

        }
    }

    //VALOR EN LETRAS
    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetrasMonePeru($valor, $moneda));

    
        
        $html = '<div style="height: 145mm;" >'; //div padre

        $html .= '<div style="margin-left:10px;" >'; //div 2
        
        $html .= '<table align="left">
                    <tr>
                        <td  align="center" width="200">
                        '.$logo_empresa.'
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size: 8px;text-align:center;font-family: Arial;" width="200"><b>' . $razonSocial . '</b></td>
                    </tr>
                    <tr>
                        <td style="font-size: 7px;font-family: Arial; text-align: center;" width="200">'.$sucu_nom_sucu.'</td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size: 7px;font-family: Arial; " width="200"><b>'.$tip_ruc_pais.' N°</b> ' . $ruc_empr . '</td>
                    </tr>
                    <tr><td align="center" style="font-size: 7px; font-family: Arial; " width="200">' . $sucu_dir_sucu . '</td>
                        </tr>';
        $html.='</table>';

        $html .= '
        <table align="left" >
                    <tr>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="100"><b>COMPROBANTE N°</b></td>
                        <td style="font-size: 7px;font-family: Arial;" align="RIGHT" width="90">'.$dmcc_num_reci. ' </td>
                    </tr>
                    <tr>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="100"><b>'.$titulo.' N°</b></td>
                        <td style="font-size: 7px;font-family: Arial;" align="RIGHT" width="90">' . $fac . ' </td>
                    </tr>
                    <tr>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="110" colspan=""><b>FECHA DE PAGO:</b></td>
                        <td style="font-size: 7px;font-family: Arial;" align="RIGHT" width="90">' . $dmcc_fec_emis . ' </td>
                    </tr>
                    <tr>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="110" colspan=""><b>FORMA DE PAGO:</b></td>
                    <td style="font-size: 7px;font-family: Arial;" align="RIGHT" width="90">' . $forma_pago . ' </td>
                    </tr>';
        //VALIDAICON BANCO
        if(!empty($banc_nom_banc)){
            $html.='
            <tr>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="110" colspan=""><b>BANCO:</b></td>
                    <td style="font-size: 7px;font-family: Arial;" align="RIGHT" width="90">' . $banc_nom_banc . ' </td>
            </tr>
            
            <tr>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="110" colspan=""><b>N° DOCUMENTO:</b></td>
                    <td style="font-size: 7px;font-family: Arial;" align="RIGHT" width="90">' . $dpag_num_cheq . ' </td>
            </tr>';
        }
        $html.='
        <tr>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="110" colspan=""><b>CONCEPTO:</b></td>
                    <td style="font-size: 7px;font-family: Arial;" align="RIGHT" width="90">' . $detalle_pago . ' </td>
                    </tr>
        <tr>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="110"><b>VALOR:</b></td>
                        <td style="font-size: 7px;font-family: Arial;" align="RIGHT" width="90">'.$total_valor.'</td>
                    </tr>
                    <tr>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="110"><b>MONEDA:</b></td>
                        <td style="font-size: 7px;font-family: Arial;" align="RIGHT" width="90">'.$moneda.'</td>
                    </tr>
                    <tr>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="110"><b>SON:</b></td>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="90">'.$con_letra.'</td>
                    </tr>
                </table>
                <table align="left" style="margin-top:10px;">
                    <tr>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="110"><b>CLIENTE:</b></td>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="100">'.$clpv_nom_clpv.'</td>
                    </tr>
                    <tr>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="110"><b>'.$tip_iden_cliente.':</b></td>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="100">'.$ruc_clpv.'</td>
                    </tr>
                    <tr>
                            <td style="font-size: 7px;font-family: Arial;" align="left" width="110"><b>CAJERO:</b></td>
                            <td style="font-size: 7px;font-family: Arial;"  align="left" width="100">'.$nombre_usuario . '</td>
                        </tr>
                    <tr>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="110"><b>OBSERVACIONES:</b></td>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="100">'.$dmcc_det_dmcc.'</td>
                </tr>
                </table>
                <table style="width: 100%; margin-top:15px;">
                <tr>
                            <td style="font-size: 7px;font-family: Arial;" align="center"  width="200"><br>AGRADECEMOS SU PREFERENCIA</td>
                        </tr>
                        </table>
                </div> 
                </div> ';


    $table = '<page>';
    $table.= $html;
    $table.= '</page>';

    $html2pdf = new HTML2PDF('P', 'B5', 'es', true, 'UTF-8', array(0,0,0,0));
    $html2pdf->WriteHTML($table);
    ob_end_clean();
    $html2pdf->Output('recibo_template.pdf', '');

?>



    
    