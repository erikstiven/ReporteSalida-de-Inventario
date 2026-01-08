<?

////PDF DIARIOS, INGRESOS, EGRESOS, MAS LOGO EMPRESA
function formato_diario($idempresa = "", $idsucursal = "", $asto_cod = "", $ejer_cod = "", $prdo_cod = "")
{
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfx2 = new Dbo;
    $oIfx2->DSN = $DSN_Ifx;
    $oIfx2->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $idEmpresa = $_SESSION['U_EMPRESA'];

    $class = new GeneraDetalleAsientoContable();

    $arrayAsto = $class->informacionAsientoContable($oIfx, $idempresa, $idsucursal, $ejer_cod, $prdo_cod, $asto_cod);

    $arrayDiario = $class->diarioAsientoContable($oIfx, $idempresa, $idsucursal, $ejer_cod, $prdo_cod, $asto_cod);

    $arrayDirectorio = $class->directorioAsientoContable($oIfx, $idempresa, $idsucursal, $ejer_cod, $prdo_cod, $asto_cod);

    $arrayRetencion = $class->retencionAsientoContable($oIfx, $idempresa, $idsucursal, $ejer_cod, $prdo_cod, $asto_cod);

    $sql = "SELECT
			empl_cod_empl,
			empl_nom_empl,
			empl_ape_empl
		 FROM saeempl, SAEPCCH
		WHERE
			SAEPCCH.pcch_cod_empl=saeempl.empl_cod_empl AND empl_cod_empr = $idempresa ";
    if ($oIfx->Query($sql)) {
        unset($array_empl);
        if ($oIfx->NumFilas() > 0) {
            do {
                $empleado = $oIfx->f('empl_nom_empl') . ' ' . $oIfx->f('empl_ape_empl');
                $array_empl[$oIfx->f('empl_cod_empl')] = $empleado;
            } while ($oIfx->SiguienteRegistro());
        }
    }

    foreach ($arrayAsto as $val) {
        $asto_cod_asto = $val[0];
        $asto_vat_asto = $val[1];
        $asto_ben_asto = $val[2];
        $asto_fec_asto = $val[3];
        $asto_det_asto = $val[4];
        $asto_cod_modu = $val[5];
        $asto_usu_asto = $val[6];
        $asto_user_web = $val[7];
        $asto_fec_serv = $val[8];
        $asto_cod_tidu = $val[9];
    }

    $sql = "select empr_ruc_empr, empr_dir_empr, empr_nom_empr, empr_path_logo from saeempr where empr_cod_empr = $idempresa ";
    if ($oIfx->Query($sql)) {
        $empr_ruc = $oIfx->f('empr_ruc_empr');
        $empr_dir = $oIfx->f('empr_dir_empr');
        $empr_nom = $oIfx->f('empr_nom_empr');
        $empr_path_logo = $oIfx->f('empr_path_logo');
        $empr_nom .= ' ';
    }

    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;
    $empr_path_logo = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

    if (file_exists($empr_path_logo)) {
        $imagen =  $empr_path_logo;
    } else {
        $imagen = '';
    }
    if ($imagen != '') {

        $empr_logo = '<div>
            <img src="' . $imagen . '" style="
            width: 20%;
            object-fit; contain;">
            </div>';
        $x = '0px';
    } else {

        $empr_logo = '<span><font color="red">SIN LOGO</font></span>';
    }



    $sql = "SELECT sucu_nom_sucu FROM saesucu WHERE sucu_cod_sucu='$idsucursal'";
    if ($oIfx->Query($sql)) {
        $sucu_nom = $oIfx->f('sucu_nom_sucu');
    }
    $sql = "select ciud_nom_ciud from saesucu inner join saeciud on saesucu.sucu_cod_ciud=saeciud.ciud_cod_ciud where saesucu.sucu_cod_sucu='$idsucursal'";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $ciudad = $oIfx->f('ciud_nom_ciud');
        }
    }

    $sql = "select modu_des_modu from saemodu where modu_cod_modu = $asto_cod_modu";
    $modu_des_modu = consulta_string_func($sql, 'modu_des_modu', $oIfx, '');

    //tipo documento
    $sql = "select tidu_des_tidu from saetidu where tidu_cod_tidu = '$asto_cod_tidu'";
    $tidu_des_tidu = consulta_string_func($sql, 'tidu_des_tidu', $oIfx, '');


    //Marco Logico
    $sql = "SELECT fprv_cod_actv from saefprv 
                where 
                    fprv_num_mayo = '$asto_cod_asto' 
                    and fprv_cod_ejer = $ejer_cod
                    and fprv_cod_empr = $idempresa
                    and fprv_cod_sucu = $idsucursal
            ";
    $fprv_cod_actv = consulta_string_func($sql, 'fprv_cod_actv', $oIfx, 0);

    if ($fprv_cod_actv != 0) {
        $sql_detalle_amrco = 'SELECT codigo, nombre FROM proyecto_pymes WHERE id = ' . $fprv_cod_actv;
        $nombre_marco_logico = consulta_string_func($sql_detalle_amrco, 'nombre', $oIfx, '');
        $codigo_marco_logico = consulta_string_func($sql_detalle_amrco, 'codigo', $oIfx, '');
    }




    $oIfx->Free();
    setlocale(LC_ALL, "es_ES@euro", "es_ES", "esp");

    //$asto_fec = strftime("%d de %B de %Y", strtotime($asto_fec_asto));
    $asto_fec = explode('-', $asto_fec_asto);
    $dia = $asto_fec[2];
    $mes = nomb_mes($asto_fec[1]);
    $anio = $asto_fec[0];
    $asto_fec = $dia . ' de ' . $mes . ' de ' . $anio;
    /// minv
    $sql = "select minv_num_comp, minv_num_caja, minv_prec_caja from saeminv where minv_cod_empr='$idempresa' and minv_cod_sucu='$idsucursal' and 
				minv_cod_ejer='$ejer_cod' and minv_num_prdo='$prdo_cod' and minv_tran_minv='$asto_cod'";
    $minv_num_caja = consulta_string_func($sql, 'minv_num_caja', $oIfx, '');
    $minv_prec_caja = str_replace('.0', '', consulta_string_func($sql, 'minv_prec_caja', $oIfx, ''));

    $rutaImagen = $empr_path_logo;

    $html .= '
        <table border = "0" style="width: 100%; margin-left: 50px; margin-top:20px">
            <tr>
                <td style="font-size:18px; text-align: left; margin-left:100px; margin-top:30px">' . $empr_logo . '</td>
            </tr>
        </table>
        <table border="0" style="width: 100%;">
            <tr>
                <td style="width: 100%; font-size:18px; font-weight: bold; text-align: center">' . $empr_nom . '</td>
            </tr>
            <tr>
                <td  style="width: 100%; font-size:14px; text-align: center"><strong>SUCURSAL:' . $sucu_nom . '</strong></td>
            </tr>
            <tr>
                <td  style="width: 100%; font-size:14px; text-align: center"><strong>DIRECCION:' . $empr_dir . '</strong></td>
            </tr>
            <tr>
                <td style="width: 100%; font-size:14px; text-align: center"><strong>RUC:' . $empr_ruc . '</strong><br><br></td>
            </tr>
		</table>
		<table border="0" style="width:90%; margin-left:50px; ">
				<tr>
					<td style="width: 50%;font-size:12px; text-align: left"><strong>Fecha:</strong> ' . $ciudad . ', ' . $asto_fec . '</td>
					<td style="width: 50%; font-size:12px; text-align: left"><strong>Modulo:</strong>' . $modu_des_modu . '</td>
				</tr>
				
				<tr>
					<td  style="width:50%; font-size:12px; text-align: left"><strong>Monto: </strong>$ ' . number_format($asto_vat_asto, 2, '.', ',') . '</td>
					<td style="width: 50%; font-size:12px; text-align: left"><strong>Documento:</strong> ' . $asto_cod_tidu . ' - ' . $tidu_des_tidu . '</td>
				</tr>
				<tr>
					<td  style="width: 50%; font-size:12px; text-align: left"><strong>Beneficiario: </strong> ' . $asto_ben_asto . '</td>
					<td style="width: 50%; font-size:12px; text-align: left"><strong>Comprobante No:</strong>' . $asto_cod_asto . '</td>
				</tr>
				<tr>
					<td colspan="2" style="width: 50%; font-size:12px; text-align: left">
                        <strong>Detalle:</strong> ' . $asto_det_asto . '
                        <br>
                        <strong>Marco Logico: </strong> ' . $codigo_marco_logico . '-' . $nombre_marco_logico . '
                    </td>
				</tr>                
                ';

    if ($minv_prec_caja != '') {
        $html .= '<tr>
					<td  style="width: 50%; font-size:12px; text-align: left"><strong>Responsable Caja: </strong> ' . $array_empl[$minv_prec_caja] . '</td>
					<td style="width: 50%;font-size:14px; text-align: left"><strong>Secuencia No:</strong>' . $minv_num_caja . '</td>

				</tr>';
    }


    $html .= '</table>';


    if (count($arrayDirectorio) > 0) {
        $html .= '
								<table  style="margin-left:50px; width:90%;border:1px solid black; border-radius: 5px; margin-top:20px" align="left">
									<tr>
									    <td colspan="6" style="width:100%; font-size:16px; text-align: center; border-bottom:1px solid "><strong>DIRECTORIO</strong></td>
									</tr>
									<tr>
										<td  style="width:3%;font-size:14px; text-align: center; border-right:1px solid; "><strong>N</strong></td>
										<td  style="width:30%;font-size:14px; text-align: center; border-right:1px solid; "><strong>CLIENTE / PROVEEDOR</strong></td>
										<td  style="width:10%;font-size:14px; text-align: center; border-right:1px solid; "><strong>TRANSACCION</strong></td>
										<td  style="width:20%;font-size:14px; text-align: center; border-right:1px solid; "><strong>FACTURA</strong></td>
										<td  style="width:10%;font-size:14px; text-align: center;  border-right:1px solid; "><strong>DEBITO</strong></td>
										<td  style="width:10%;font-size:14px; text-align: center; "><strong>CREDITO</strong></td>
										
									</tr>';
        foreach ($arrayDirectorio as $val) {
            //directorio

            $dir_cod_dir = $val[0];
            $dir_cod_cli = $val[1];
            $tran_cod_modu = $val[2];
            $dir_cod_tran = $val[3];
            $dir_num_fact = $val[4];
            $dir_detalle = $val[5];
            $dir_fec_venc = $val[6];
            $dir_deb_ml = $val[7];
            $dir_cre_ml = $val[8];
            //clpv
            $clpv_nom_clpv = '';
            if (!empty($dir_cod_cli)) {
                $sql = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $dir_cod_cli";
                $clpv_nom_clpv = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');
            }

            $html .= '<tr>';
            $html .= '<td style="width:3%;font-size:12px; text-align: center; border-right:1px solid; border-top:1px solid ">' . $dir_cod_dir . '</td>';
            $html .= '<td style="width:30%;font-size:12px; text-align: center; border-right:1px solid; border-top:1px solid">' . $clpv_nom_clpv . '</td>';
            $html .= '<td style="width:10%;font-size:12px; text-align: center; border-right:1px solid; border-top:1px solid ">' . $dir_cod_tran . '</td>';
            $html .= '<td style="width:20%;font-size:12px; text-align: center; border-right:1px solid; border-top:1px solid ">' . $dir_num_fact . '</td>';
            $html .= '<td style="width:10%;font-size:12px; text-align: right;  border-right:1px solid; border-top:1px solid" align="right">' . number_format($dir_deb_ml, 2, '.', ',') . '</td>';
            $html .= '<td style="width:10%;font-size:12px; text-align: right; border-top:1px solid" align="right">' . number_format($dir_cre_ml, 2, '.', ',') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
    }

    //retencioncd 
    if (count($arrayRetencion) > 0) {

        $html .= '<table  style="margin-left:50px; width:90%;border:1px solid black; border-radius: 5px; margin-top:20px" align="left">';
        $html .= '<tr>';
        $html .= '<td colspan="7" style="width:100%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>RETENCION</strong></td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid " ><strong>Cliente/Proveedor</strong></td>';
        $html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Factura</strong></td>';
        $html .= '<td style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Retencion</strong></td>';
        $html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Codigo</strong></td>';
        $html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Porcentaje</strong></td>';
        $html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Base Imp.</strong></td>';
        $html .= '<td style="width:10%;font-size:14px; text-align: center; border-bottom:1px solid "><strong>Valor</strong></td>';
        $html .= '</tr>';
        foreach ($arrayRetencion as $val) {
            $ret_cta_ret = $val[0];
            $ret_porc_ret = $val[1];
            $ret_bas_imp = $val[2];
            $ret_valor = $val[3];
            $ret_num_ret = $val[4];
            $ret_detalle = $val[5];
            $ret_num_fact = $val[6];
            $ret_ser_ret = $val[7];
            $ret_cod_clpv = $val[8];
            $ret_fec_ret = $val[9];

            //clpv
            $clpv_nom_clpv = '';
            if (!empty($ret_cod_clpv)) {
                $sql = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $ret_cod_clpv";
                $clpv_nom_clpv = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');
            }

            //fprv
            $printRet = '';
            if ($asto_cod_modu == 4 || $asto_cod_modu == 6) {

                //fecha fprv o minv
                if ($asto_cod_modu == 4) {
                    $sql = "select fprv_fec_emis 
								from saefprv
								where fprv_cod_clpv = $ret_cod_clpv and
								fprv_num_fact = '$ret_num_fact' and
								fprv_cod_asto = '$asto_cod_asto' and
								fprv_cod_ejer = $ejer_cod and
								fprv_cod_empr = $idempresa and
								fprv_cod_sucu = $idsucursal";
                    $fechaEmis = consulta_string_func($sql, 'fprv_fec_emis', $oIfx, '');
                } elseif ($asto_cod_modu == 6) {
                    $sql = "select minv_fmov 
								from saeminv
								where minv_cod_clpv = $ret_cod_clpv and
								minv_fac_prov = '$ret_num_fact' and
								minv_comp_cont = '$asto_cod_asto' and
								minv_cod_ejer = $ejer_cod and
								minv_cod_empr = $idempresa and
								minv_cod_sucu = $idsucursal";
                    $fechaEmis = consulta_string_func($sql, 'minv_fmov', $oIfx, '');
                }

                $printRet = '<div class="btn btn-primary btn-sm" onclick="genera_documento(5, \'' . $campo . '\',\'' . $fprv_clav_sri . '\' ,
																				 \'' . $ret_cod_clpv . '\'  , \'' . $ret_num_fact . '\', \'' . $ejer . '\',
																				 \'' . $asto . '\',  \'' . $fechaEmis . '\', ' . $sucu . ');">
									<span class="glyphicon glyphicon-print"></span>
								</div>';
            }

            $html .= '<tr>';
            $html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" >' . $clpv_nom_clpv . '</td>';
            $html .= '<td style=" width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" >' . $ret_num_fact . '</td>';
            $html .= '<td style="width:20%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $ret_ser_ret . ' - ' . $ret_num_ret . '</td>';
            $html .= '<td style="width:10%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid">' . $ret_cta_ret . '</td>';
            $html .= '<td style="width:10%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid" align="right">' . $ret_porc_ret . '</td>';
            $html .= '<td style="width:10%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid" align="right">' . number_format($ret_bas_imp, 2, '.', ',') . '</td>';
            $html .= '<td style="width:10%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($ret_valor, 2, '.', ',') . '</td>';
            $html .= '</tr>';
        } //fin foreach

        $html .= '</table>';
    }
    ///diario
    if (count($arrayDiario) > 0) {

        $html .= '<table style="margin-left:50px; width:90%; border:1px ; border-radius: 2px; margin-top:20px" align="left">';
        $html .= '<tr>';
        $html .= '<td colspan="6" style="width:100%;font-size:14px; text-align: center; border-bottom:1px;"><strong>DIARIO</strong></td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="width:25%;font-size:14px; text-align: center;  border-bottom:1px; border-right:1px "><strong>Cuenta Contable</strong></td>';
        $html .= '<td style="width:15%;font-size:14px; text-align: center;  border-bottom:1px; border-right:1px "><strong>Centro Costos</strong></td>';
        $html .= '<td style="width:15%;font-size:14px; text-align: center;  border-bottom:1px; border-right:1px "><strong>Centro Actividad</strong></td>';
        $html .= '<td style="width:15%;font-size:14px; text-align: center;  border-bottom:1px; border-right:1px "><strong>Documento</strong></td>';
        $html .= '<td style="width:15%;font-size:14px; text-align: center;  border-bottom:1px; border-right:1px "><strong>Debito</strong></td>';
        $html .= '<td style="width:15%;font-size:14px; text-align: center;  border-bottom:1px; border-right:1px "><strong>Credito</strong></td>';
        $html .= '</tr>';
        $totalDeb = 0;
        $totalCre = 0;
        foreach ($arrayDiario as $val) {
            $dasi_cod_cuen = $val[0];
            $dasi_cod_cact = $val[1];
            $ccos_cod_ccos = $val[2];
            $dasi_dml_dasi = $val[3];
            $dasi_cml_dasi = $val[4];
            $dasi_det_asi = $val[5];
            $dasi_num_depo = $val[6];

            //clpv
            $cuen_nom_cuen = '';
            if (!empty($dasi_cod_cuen)) {
                $sql = "select cuen_nom_cuen from saecuen where cuen_cod_cuen = '$dasi_cod_cuen' and cuen_cod_empr = $idempresa";
                $cuen_nom_cuen = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');
            }

            $ccosn_nom_ccosn = '';
            if (!empty($ccos_cod_ccos)) {
                $sql = "select ccosn_nom_ccosn from saeccosn where ccosn_cod_ccosn = '$ccos_cod_ccos' and ccosn_cod_empr = $idempresa";
                $ccosn_nom_ccosn = consulta_string_func($sql, 'ccosn_nom_ccosn', $oIfx, '');
            }

            $cact_nom_cact = '';
            if (!empty($dasi_cod_cact)) {
                $sql = "select cact_nom_cact from saecact where cact_cod_cact = '$dasi_cod_cact' and cact_cod_empr = $idempresa";
                $cact_nom_cact = consulta_string_func($sql, 'cact_nom_cact', $oIfx, '');
            }

            $html .= '<tr>';
            $html .= '<td style="width:25%;font-size:12px; text-align: left;  border-bottom:1px; border-right:1px ">' . $dasi_cod_cuen . ' - ' . $cuen_nom_cuen . '</td>';
            $html .= '<td style="width:15%;font-size:12px; text-align: left;  border-bottom:1px; border-right:1px ">' . $ccos_cod_ccos . ' - ' . $ccosn_nom_ccosn . '</td>';
            $html .= '<td style="width:15%;font-size:12px; text-align: left;  border-bottom:1px; border-right:1px ">' . $dasi_cod_cact . ' - ' . $cact_nom_cact . '</td>';
            $html .= '<td style="width:15%;font-size:12px; text-align: left;  border-bottom:1px; border-right:1px ">' . $dasi_num_depo . '</td>';
            $html .= '<td style="padding: 8px; width:15%;font-size:12px; text-align: right; border-bottom:1px; border-right:1px; margin-top:20px "><label margin-top:20px>' . number_format($dasi_dml_dasi, 2, '.', ',') . '</label></td>';
            $html .= '<td style="padding: 8px; width:15%;font-size:12px; text-align: right; border-bottom:1px; border-right:1px ">' . number_format($dasi_cml_dasi, 2, '.', ',') . '</td>';
            $html .= '</tr>';

            $totalDeb += $dasi_dml_dasi;
            $totalCre += $dasi_cml_dasi;
        } //fin foreach

        //$usuario = $_SESSION['U_NOMBRECOMPLETO'];


        if (!empty($asto_user_web)) {
            $sql = "select u.USUARIO_USER, concat(u.usuario_nombre,' ',u.usuario_apellido) as apenomb  from comercial.usuario u where u.USUARIO_ID = $asto_user_web ";
            $usuario = consulta_string_func($sql, 'usuario_user', $oCon, '');
            $nombre_usuario = consulta_string_func($sql, 'apenomb', $oCon, '');
        } else {
            $usuario = $asto_usu_asto;
            if (!empty($usuario)) {
                $sql = "select u.USUARIO_USER, concat(u.usuario_nombre,' ',u.usuario_apellido) as apenomb  from comercial.usuario u where u.USUARIO_ID = $asto_usu_asto ";
                $nombre_usuario = consulta_string_func($sql, 'apenomb', $oCon, '');
            }
        }

        $html .= '<tr>';
        $html .= '<td align="right" style="width:60%;font-size:14px; text-align: right;" colspan="4"><strong>TOTAL</strong></td>';
        $html .= '<td align="right" style="padding: 8px; width:8%;font-size:14px; text-align: right;">' . number_format($totalDeb, 2, '.', ',') . '</td>';
        $html .= '<td align="right" style="padding: 8px; width:8%;font-size:14px;">' . number_format($totalCre, 2, '.', ',') . '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $sql = "select minv_num_comp, minv_num_caja, minv_prec_caja from saeminv where minv_cod_empr='$idempresa' and minv_cod_sucu='$idsucursal' and 
				minv_cod_ejer='$ejer_cod' and minv_num_prdo='$prdo_cod' and minv_tran_minv='$asto_cod'";

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $minv_num_comp = $oIfx->f('minv_num_comp');
                    $sql2 = "select * from saeminr where minr_cod_empr='$idEmpresa' and minr_cod_sucu='$idsucursal' and minr_cod_ejer='$ejer_cod' 
						and minr_num_prdo='$prdo_cod' and minr_num_comp='$minv_num_comp'";
                    if ($oIfx2->Query($sql2)) {
                        if ($oIfx2->NumFilas() > 0) {
                            $html .= '<table  style="margin-left:50px; width:90%;border:1px solid black; border-radius: 5px; margin-top:20px" align="left">';
                            $html .= '<tr>';
                            $html .= '<td colspan="7" style="width:100%;font-size:16px; text-align: center; border-bottom:1px solid "><strong>FACTURAS DE REEMBOLSO</strong></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                            $html .= '<td  style="width:3%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid;"><strong>N</strong></td>
									<td  style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid;"><strong>FECHA</strong></td>
									<td  style="width:15%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid;"><strong>IDENTIFICACION</strong></td>
									<td  style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid;"><strong>N. ESTAB.</strong></td>
									<td  style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid;"><strong>PTO. EMISION</strong></td>
									<td  style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid;"><strong>FACTURA</strong></td>
									<td  style="width:10%;font-size:14px; text-align: center; border-bottom:1px solid;"><strong>VALOR</strong></td>';
                            $html .= '</tr>';
                            $i = 1;
                            $gran_t_remm = 0;
                            do {
                                $html .= '<tr>';
                                $html .= '<td  style="width:3%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid;">' . $i . '</td>
									<td  style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid;">' . $oIfx2->f('minr_fec_emis') . '</td>
									<td  style="width:15%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid;">' . $oIfx2->f('minr_ide_prov') . '</td>
									<td  style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid;">' . $oIfx2->f('minr_num_esta') . '</td>
									<td  style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid;">' . $oIfx2->f('minr_pto_emis') . '</td>
									<td  style="width:20%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid;">' . $oIfx2->f('minr_sec_docu') . '</td>
									<td  style="width:10%;font-size:14px; text-align: right;  border-right:1px solid; border-bottom:1px solid;">' . ($oIfx2->f('minr_con_miva') + $oIfx2->f('minr_sin_miva') + $oIfx2->f('minr_iva_valo')) . '</td>';
                                $html .= '</tr>';
                                $i++;
                                $gran_t_remm += ($oIfx2->f('minr_con_miva') + $oIfx2->f('minr_sin_miva') + $oIfx2->f('minr_iva_valo'));
                            } while ($oIfx2->SiguienteRegistro());
                            $html .= '<tr>';
                            $html .= '<td  colspan="6" style="width:3%;font-size:14px; text-align: right; border-right:1px solid;"><strong>TOTAL</strong></td>
									 <td  style="width:10%;font-size:14px; text-align: right;   ">' . $gran_t_remm . '</td>';
                            $html .= '</tr>';
                            $html .= '</table>';
                        }
                    }
                } while ($oIfx->SiguienteRegistro());
            }
        }




        // ------------------------------------------------------------------------------------------
        // TABLA BENEFICIARIOS
        // ------------------------------------------------------------------------------------------

        // Tabla de beneficiarios proceso fedexport tabla: proy_fact_benf
        // beneficiarios
        $sql_beneficiarios = "SELECT id_prove, id_clpv, valor 
                                from proy_fact_benf 
                                where 
                                id_empresa =  $idempresa and 
                                id_sucursal = $idsucursal and
                                asto = '$asto_cod_asto' and
                                ejer = $ejer_cod
                                ";

        $cont_ben = 1;
        $valor_total = 0;
        if ($oIfx->Query($sql_beneficiarios)) {
            if ($oIfx->NumFilas() > 0) {

                $html .= '<table  style="margin-left:50px; width:90%;border:1px solid black; border-radius: 5px; margin-top:20px" align="left">';
                $html .= '<tr>';
                $html .= '<td colspan="7" style="width:100%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>GASTOS BENEFICIARIOS</strong></td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td style="width:10%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid " ><strong>No.</strong></td>';
                $html .= '<td style="width:70%;font-size:14px; text-align: center; border-right:1px solid; border-bottom:1px solid "><strong>Cliente</strong></td>';
                $html .= '<td style="width:20%;font-size:14px; text-align: center; border-bottom:1px solid "><strong>Valor</strong></td>';
                $html .= '</tr>';


                do {
                    $id_prove = $oIfx->f('id_prove');
                    $id_clpv = $oIfx->f('id_clpv');
                    $valor = $oIfx->f('valor');
                    $valor_total = $valor_total + $valor;


                    $sql_nom_ben = "SELECT nom_clpv FROM contrato_clpv where id_clpv = $id_clpv";
                    $nom_clpv = consulta_string_func($sql_nom_ben, 'nom_clpv', $oIfx2, '');


                    $html .= '<tr>';
                    $html .= '<td style="width:10%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" >' . $cont_ben . '</td>';
                    $html .= '<td style=" width:70%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid" >' . $nom_clpv . '</td>';
                    $html .= '<td style="width:20%;font-size:12px; text-align: right;  border-bottom:1px solid" align="right">' . number_format($valor, 2, '.', ',') . '</td>';
                    $html .= '</tr>';

                    $cont_ben++;
                } while ($oIfx->SiguienteRegistro());

                $html .= '<tr>';
                $html .= '<td align="right" style="width:80%;font-size:14px; text-align: right;" colspan="2"><strong>TOTAL</strong></td>';
                $html .= '<td align="right" style="padding: 8px; width:8%;font-size:14px; text-align: right;">' . number_format($valor_total, 2, '.', ',') . '</td>';
                $html .= '</tr>';
                $html .= '</table>';
            }
        }
        $oIfx->Free();


        // ------------------------------------------------------------------------------------------
        // FIN TABLA BENEFICIARIOS
        // ------------------------------------------------------------------------------------------






        $html .= '<table style="width:90%; margin-top: 100px" align="center" >
					<tr>
						<td style="width:40%; font-size:12px; text-align: center;border-top : 2px solid black;">Ingresado por:<br>' . $nombre_usuario . '</td>
						<td style="width:10%;"></td>
						<td style="width:40%;font-size:12px; text-align: center;border-top : 2px solid black;">Aprobado por:</td>
                        <td style="width:10%;"></td>					
					</tr>
			</table>';
    }





    return $html;
}
