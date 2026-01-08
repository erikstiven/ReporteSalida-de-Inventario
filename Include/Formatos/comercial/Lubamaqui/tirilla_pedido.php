<?

include_once('../../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

    global $DSN_Ifx, $DSN;
    session_start();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();

    $idEmpresa      = $_SESSION['U_EMPRESA'];
    $idSucursal     = $_SESSION['U_SUCURSAL'];
    $array_imp      = $_SESSION['U_EMPRESA_IMPUESTO'];
    $etiqueta_iva   = $array_imp ['IVA'];
    $empr_cod_pais  = $_SESSION['U_PAIS_COD'];
    $id = $_GET['codigo'];
     // IMPUESTOS POR PAIS
     $sql = "select p.impuesto, p.etiqueta, p.porcentaje from comercial.pais_etiq_imp p where
     p.pais_cod_pais = $empr_cod_pais and etiqueta='$etiqueta_iva'";
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {
                $impuesto      = $oCon->f('impuesto');
                $etiqueta     = $oCon->f('etiqueta');
                $porcentaje = $oCon->f('porcentaje');
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();


    $sqlMonedaEm = "SELECT pcon_mon_base from saepcon where pcon_cod_empr = $idEmpresa";
    $moneda_emp = consulta_string($sqlMonedaEm, 'pcon_mon_base', $oIfx, 0);

    $sqlPuntEmi = "SELECT para_punt_emi from saepara where para_cod_sucu = $idSucursal";
    $para_punt_emi = consulta_string($sqlPuntEmi, 'para_punt_emi', $oIfx, 0);

    $moneda_emp_ = "SELECT * from saemone where mone_cod_mone = $moneda_emp  ";
    $moneda_emp_local = consulta_string($moneda_emp_, 'mone_sgl_mone', $oIfx, '');
    $moneda_des_mone = consulta_string($moneda_emp_, 'mone_des_mone', $oIfx, '');
    $moneda_emp_local_sm = consulta_string($moneda_emp_, 'mone_smb_mene', $oIfx, '');



    // UNIDAD
    $sql = "select unid_cod_unid, unid_nom_unid from saeunid where unid_cod_empr = $idEmpresa ";
    unset($array_unid);
    $array_unid = array_dato($oIfx, $sql, 'unid_cod_unid', 'unid_nom_unid');

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
            if ($oIfx->f('empr_conta_sn') == 'S')
                $empr_conta_sn = 'SI';
            else
                $empr_conta_sn = 'NO';

            $empr_num_resu = $oIfx->f('empr_num_resu');
            $empr_iva_empr = $oIfx->f('empr_iva_empr');
            $empr_num_dire = $oIfx->f('empr_num_dire');
            $empr_fax_empr = $oIfx->f('empr_fax_empr');
            $empr_sitio_web= $oIfx->f('empr_sitio_web');
            $empr_cm1_empr = $oIfx->f('empr_cm1_empr');
            $empr_cm2_empr = $oIfx->f('empr_cm2_empr');
            $empr_prec_sucu = $oIfx->f('empr_prec_sucu');
            $empr_tel_resp = $oIfx->f('empr_tel_resp');
            $empr_cod_pais = $oIfx->f('empr_cod_pais');
            $empr_mai_empr = $oIfx->f('empr_mai_empr');
            $empr_ema_comp = $oIfx->f('empr_ema_comp');
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

    $sql_sucu = "SELECT etiqueta, porcentaje from comercial.pais_etiq_imp where pais_cod_pais = $empr_cod_pais and impuesto = 'IVA' ";
    if ($oIfx->Query($sql_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $etiqueta_iva = $oIfx->f('etiqueta');
                $porcentaje_iva = $oIfx->f('porcentaje');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    //selecciona sucursales y direcciones
    $sql_sucu = "SELECT identificacion from comercial.tipo_iden_clpv_pais where pais_cod_pais = $empr_cod_pais and id_iden_clpv = 1";
    if ($oIfx->Query($sql_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $tip_ruc_pais = $oIfx->f('identificacion');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();



    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;


    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

    $style_image = 'style="display: block; margin-left: auto; margin-right: auto; width: 50%; min-width: 170px"';
    // $style_image = '';

    if (file_exists($path_logo_img)) {
        $logo_empresa=' <img width="150px;"  src="' . $path_logo_img . '"'.$style_image.'>';
    }
    else{
        $logo_empresa='<div style="color:red;">LOGO NO CARGADO</div>';
    }


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
                $fecha_pedido = date('d-m-Y', strtotime($oIfx->f('pedf_fech_fact')));
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
                $hora_pedido=date('H:i',strtotime($pedf_hor_fin));
				$pedf_hor_impr = $oIfx->f('pedf_hor_impr');
				$pedf_lug_pedf = $oIfx->f('pedf_lug_pedf');
				$pedf_cod_ase = $oIfx->f('pedf_cod_ase');


                $sql = "SELECT clv_con_clpv from saeclpv where clpv_cod_clpv = $id_cliente";
                $clv_con_clpv = consulta_string_func($sql, 'clv_con_clpv', $oCon, '');

                    if(strlen($clv_con_clpv)==0){
                        $clv_con_clpv = 2;
                    }
                  

                     //TIPO DE IDENTIFICACION DEL CLIENTE
                     $sql_sucu = "SELECT identificacion from comercial.tipo_iden_clpv_pais where pais_cod_pais = $empr_cod_pais and id_iden_clpv = '$clv_con_clpv'";
                     if ($oCon->Query($sql_sucu)) {
                         if ($oCon->NumFilas() > 0) {
                             do {
                                 $tip_iden_cliente = $oCon->f('identificacion');
                             } while ($oCon->SiguienteRegistro());
                         }
                     }
                     $oCon->Free();



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
                
                if(!empty($pedf_cod_ase)){
                    $sql_asesor="select concat(usuario_apellido, ' ', usuario_nombre) as user from comercial.usuario where empresa_id=$idEmpresa and usuario_id=$pedf_cod_ase";
                    $asesor = consulta_string($sql_asesor, 'user', $oIfxA, '');
                }


        }
    }
    $oIfx->Free();


        $sql_sucu = "SELECT usuario_nombre, usuario_apellido, usuario_user from comercial.usuario where usuario_id = $id_user";
        if ($oIfx->Query($sql_sucu)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $usuario_nombre = $oIfx->f('usuario_nombre');
                    $usuario_apellido = $oIfx->f('usuario_apellido');
                    $usuario_user = $oIfx->f('usuario_user');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $nombre_cajero = $usuario_user;

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
    dpef_cod_pedf = $id and dpef_cod_sucu = $idSucursal and dpef_cod_empr = $idEmpresa ";


        $deta = ' <table style="width: 100%;" >
                    <tr>
                        <td colspan="6" style="border-top: 1px solid black;" width="200"></td>
                    </tr>';
        $deta .= ' <tr>';
        $deta .= ' <b> <td style="width: 10; font-size: 7px;font-family: Arial;" align="center">CANT.</td> </b>';
        $deta .= ' <b> <td style="width: 35; font-size: 7px;font-family: Arial;" align="center">COD</td> </b>';
        $deta .= ' <b> <td style="width: 50; font-size: 7px;font-family: Arial;" align="center">DESC.</td> </b>';
        $deta .= ' <b> <td style="width: 55; font-size: 7px;font-family: Arial;" align="center">APLICACION</td> </b>';
        $deta .= ' <b> <td style="width: 20; font-size: 7px;fosnt-family: Arial;" align="center">PREC</td> </b>';
        $deta .= ' <b> <td style="width: 20; font-size: 7px;font-family: Arial;" align="center">TOTAL</td> </b>';
        $deta .= ' </tr>
                    <tr>
                        <td colspan="6" style="border-top: 1px solid black;" width="200"></td>
                    </tr>';

      
        $rd = 1;
        if ($oIfx->Query($sqlDeta)) {
            if ($oIfx->NumFilas() > 0) {
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
                    $deta .= ' <td style="width: 10;font-family: Arial; font-size: 7px;" align="center">' . number_format($dfac_cant_dfac, 2, '.', ',') . '</td>';
                    $deta .= ' <td style="width: 35;font-family: Arial; font-size: 7px;">' . $dfac_cod_prod . '</td>';
                    $deta .= ' <td style="width: 50; font-family: Arial; font-size: 5px;" >' . $dfac_nom_prod . '</td>';
                    //$deta .= ' <td style="width: 41;font-family: Arial; font-size: 7px;">' . $dfac_cod_lote . '</td>';
                    $deta .= ' <td style="width: 55;font-family: Arial; font-size: 5px;">' . $aplicacion . '</td>';
                   
                    $deta .= ' <td style="width: 20; font-family: Arial; font-size: 7px;" align="right">' . number_format($dfac_precio_dfac, 2, '.', ',') . '</td>';

                    $deta .= ' <td style="width: 20; font-family: Arial; font-size: 7px;" align="right">' . number_format($dfac_mont_total, 2, '.', ',') . '</td>';
                    $deta .= ' </tr>';


                }while ($oIfx->SiguienteRegistro());
                
            }
        }
        $oIfx->Free();

        $deta .= '<tr>
                        <td colspan="6" style="border-top: 1px solid black;" width="200"></td>
                    </tr> </table>';

    /*if($fact_cod_mone==$pcon_seg_mone)
    {
        $iva = $iva/$fact_val_tcam;
        $fact_tot_fact = $fact_tot_fact/$fact_val_tcam;
    }*/
        $total=$con_iva + $sin_iva + $iva;

        $totales = ' <table style="width: 100%; margin-top: 2px;">';
        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 7px;font-family: Arial;">SUBTOTAL:</td></b>';
        $totales .= ' <td style="width: 60;font-size: 7px;font-family: Arial;" align="right">' . number_format($con_iva+$sin_iva+$dsg_valo, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';

        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 7px;font-family: Arial;">DESCUENTO:</td></b>';
        $totales .= ' <td style="width: 60;font-size: 7px;font-family: Arial;" align="right">' . number_format($dsg_valo, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';


        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 7px;font-family: Arial;">SUBTOTAL SIN IMPUESTOS:</td> </b>';
        $totales .= ' <td style="width: 60;font-size: 7px;font-family: Arial;" align="right">' . number_format( $con_iva+$sin_iva, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';


        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 7px;font-family: Arial;">' . $array_imp ['IVA'] . '  '.$porcentaje.' %:</td> </b>';
        $totales .= ' <td style="width: 60;font-size: 7px;font-family: Arial;" align="right">' . number_format($iva, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';


        $totales .= ' <tr>';
        $totales .= ' <b> <td style="width: 150;font-size: 7px;font-family: Arial;">TOTAL:</td> </b>';
        $totales .= ' <td style="width: 60;font-size: 7px;font-family: Arial;" align="right">' . number_format($total, 2, '.', ',') . '</td>';
        $totales .= ' </tr>';



        if($moneda == "DOLAR"){
            $txt = "ES";
        }else{
            $txt = "S";
        }

        $V = new EnLetras();
        $con_letra = strtoupper($V->ValorEnLetras($total, $moneda));
      
        

        
        $totales .= ' </table><table style="width: 100%;"> <tr>
                            <td style="font-size: 7px;font-family: Arial;" align="left"  width="20"><b>SON: </b></td>
                            <td style="font-size: 7px;font-family: Arial;" align="left"  width="185">'.$con_letra.'</td>
                        </tr>
                        <tr><br>
                            <td colspan="2" style="border-top: 1px solid black;"></td>
                        </tr> </table>';


        $html .= '<div style=" height: 145mm;" >'; //div padre

        $html .= '<div style="margin-left:2px;" >'; //div 2
        
        $html .= '<table align="left">
                    <tr>
                        <td align="center" width="200">'.$logo_empresa.'</td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size: 8px;text-align:center;font-family: Arial;" width="200"><b>' . $razonSocial . '</b></td>
                    </tr>
                    <tr>
                        <td style="font-size: 7px;font-family: Arial; text-align: center;" width="200">'.$sucu_dir_sucu.'</td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size: 7px;font-family: Arial; " width="200"><b>'.$tip_ruc_pais.' N°</b> ' . $ruc_empr . '</td>
                    </tr>
                    <tr><td align="center" style="font-size: 7px; font-family: Arial; " width="200">' . $sucu_nom_sucu . '</td>
                        </tr>
                        
                    </table>';


        $html .= '<table align="left" >
        <tr>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="110"><b>PEDIDO: </b> </td>
                        <td style="font-size: 7px;font-family: Arial;" align="RIGHT" width="90">' . $codigo_op . '</td>
                    </tr>
                    <tr>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="110"><b>FECHA DE EMISIÓN:</b> '.$fecha_pedido.'</td>
                        <td style="font-size: 7px;font-family: Arial;" align="RIGHT" width="90"><b>HORA:</b> '.$hora_pedido.'</td>
                    </tr>
                </table>
                <table align="left" >
                    
                    <tr>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="55"><b>NOMBRE:</b></td>
                        <td style="font-size: 7px;font-family: Arial;" align="left" width="145">'.$nombre_cliente.'</td>
                    </tr>
                    <tr>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="55"><b>'.$tip_iden_cliente.':</b></td>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="145">'.$ruc_cliente.'</td>
                </tr>
                    <tr>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="55"><b>DIRECCION:</b></td>
                    <td style="font-size: 7px;font-family: Arial;" align="left" width="145">'.$direccion.'</td>
                </tr>
                </table> ';

        $html .= $deta;
        $html .= $totales;
        //$html .= $tablePago;

        $html .=    ' <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 7px;font-family: Arial;" align="left" width="130"><b>CAJERO:</b></td>
                            <td style="font-size: 7px;font-family: Arial;"  align="left" width="80">'.$nombre_cajero . '</td>
                        </tr>';

        $html .=    ' <tr>
                            <td style="font-size: 7px;font-family: Arial;" align="center" colspan="2" width="200"><br>AGRADECEMOS SU PREFERENCIA</td>
                        </tr>
                        
                        </table>
                  
                </div> 
                </div>'; //fin div padre


    //arma pdf

    $table = '<page>';
    $table.= $html;
    $table.= '</page>';

    // echo  $table;exit;

    $html2pdf = new HTML2PDF('P', 'B5', 'es', true, 'UTF-8', array(0,0,0,0));
    $html2pdf->WriteHTML($table);
    ob_end_clean();
    $html2pdf->Output('tirilla_pedido.pdf', '');

?>
