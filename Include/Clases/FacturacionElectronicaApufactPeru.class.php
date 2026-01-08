<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class FacturacionElectronicaApufact
{
    private $oIfx;
    private $oCon;

    public $empr_cod_empr = '';
    public $empr_ruc_empr = '';
    public $empr_nom_empr = '';
    public $empr_dir_empr = '';
    public $empr_mai_empr = '';
    public $empr_tel_resp = '';
    public $empr_token_api = '';
    public $pcon_mon_base = '';
    public $pcon_seg_mone = '';

    function __construct($oIfx, $oCon, $idempresa)
    {
        $this->oIfx = $oIfx;
        $this->oCon = $oCon;

        $sql = "SELECT  empr_ruc_empr, empr_nom_empr, empr_dir_empr, empr_mai_empr, 
                    empr_tel_resp, empr_token_api, empr_cod_empr
                    FROM saeempr 
                    WHERE empr_cod_empr = $idempresa";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $this->empr_cod_empr = $oCon->f('empr_cod_empr');
                    $this->empr_ruc_empr = $oCon->f('empr_ruc_empr');
                    $this->empr_nom_empr = $oCon->f('empr_nom_empr');
                    $this->empr_dir_empr = $oCon->f('empr_dir_empr');
                    $this->empr_mai_empr = $oCon->f('empr_mai_empr');
                    $this->empr_tel_resp = $oCon->f('empr_tel_resp');
                    $this->empr_token_api = $oCon->f('empr_token_api');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sql = "SELECT pcon_mon_base, pcon_seg_mone
                    FROM saepcon 
                    WHERE pcon_cod_empr = $idempresa";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $this->pcon_mon_base = $oCon->f('pcon_mon_base');
                    $this->pcon_seg_mone = $oCon->f('pcon_seg_mone');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

    }

    /**
     * FACTURAS Y BOLETAS ENVIO A LA SUNAT
     */
    function EnviarFacturaBoletaPeru($fact_cod_fact)
    {
        try {

            $idempresa = $_SESSION['U_EMPRESA'];

           
            $sql = "SELECT
	        fac.fact_nse_fact,
	        fac.fact_num_preimp,
	        fac.fact_fech_fact,
	        fac.fact_hor_ini,
	        fac.fact_cod_clpv,
	        fac.fact_nom_cliente,
	        fac.fact_ruc_clie,
	        fac.fact_dir_clie,
	        fac.fact_email_clpv,
	        fac.fact_tlf_cliente,
	        fac.fact_iva,
	        fac.fact_con_miva,
	        fac.fact_sin_miva,
	        fac.fact_tot_fact,
	        fac.fact_fech_venc,
	        fac.fact_cm4_fact,
	        fac.fact_cm1_fact,
	        fac.fact_hor_fin,
	        fac.fact_cod_sucu,
	        cli.clv_con_clpv,
	        fac.fact_aprob_sri,
            fac.fact_cod_mone,
            fac.fact_cod_detra,
            fac.fact_val_tcam
            FROM
	        saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
            WHERE fac.fact_cod_fact = $fact_cod_fact;";

            $fact_aprob_sri = 'N';
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $fact_nse_fact = $this->oIfx->f('fact_nse_fact');
                        $fact_num_preimp = $this->oIfx->f('fact_num_preimp');
                        $fact_fech_fact = $this->oIfx->f('fact_fech_fact');
                        $fact_fech_venc = $this->oIfx->f('fact_fech_venc');
                        $fact_hor_ini = $this->oIfx->f('fact_hor_ini');
                        $fact_hor_fin = $this->oIfx->f('fact_hor_fin');
                        $fact_cod_clpv = $this->oIfx->f('fact_cod_clpv');
                        $fact_nom_cliente = $this->oIfx->f('fact_nom_cliente');
                        $fact_ruc_clie = $this->oIfx->f('fact_ruc_clie');
                        $fact_dir_clie = $this->oIfx->f('fact_dir_clie');
                        $fact_email_clpv = $this->oIfx->f('fact_email_clpv');
                        $fact_tlf_cliente = $this->oIfx->f('fact_tlf_cliente');
                        $fact_iva = $this->oIfx->f('fact_iva');
                        $fact_con_miva = $this->oIfx->f('fact_con_miva');
                        $fact_sin_miva = $this->oIfx->f('fact_sin_miva');
                        $fact_tot_fact = $fact_iva + $fact_con_miva + $fact_sin_miva;
                        $fact_cm4_fact = $this->oIfx->f('fact_cm4_fact');
                        $fact_cm1_fact = $this->oIfx->f('fact_cm1_fact');
                        $fact_cod_sucu = $this->oIfx->f('fact_cod_sucu');
                        $tipo_doc = $this->oIfx->f('clv_con_clpv');
                        $fact_aprob_sri = $this->oIfx->f('fact_aprob_sri');
                        $fact_cod_mone = $this->oIfx->f('fact_cod_mone');
                        $fact_cod_detra = $this->oIfx->f('fact_cod_detra');
                        $fact_val_tcam = $this->oIfx->f('fact_val_tcam');
                        $valor_detra = 0;

                         
                        
                        if($fact_cod_detra > 0){
                            $sql_tret = "SELECT tret_porct, tret_cod_banc, tret_cod_imp from saetret where tret_cod = '$fact_cod_detra' and tret_cod_empr = $idempresa";
                            if ($this->oCon->Query($sql_tret)) {
                                if ($this->oCon->NumFilas() > 0) {
                                    do {
                                        $tret_porct     = $this->oCon->f('tret_porct');
                                        $tret_cod_banc  = $this->oCon->f('tret_cod_banc');
                                        $tret_cod_imp   = $this->oCon->f('tret_cod_imp');

                                        $tret_porct_c = $tret_porct / 100;

                                        $valor_detra = $fact_tot_fact * $tret_porct_c;
                                    } while ($this->oCon->SiguienteRegistro());
                                }
                            }
                            $this->oCon->Free();

                            if(empty($tret_cod_imp) || empty($tret_cod_banc) ){
                                throw new Exception("No tiene configurado el codigo de la detraccion o el numero de cuenta.");
                            }

                            $sql_tret = "SELECT ctab_num_ctab from saectab where ctab_cod_ctab = '$tret_cod_banc' and ctab_cod_empr = $idempresa";
                            if ($this->oCon->Query($sql_tret)) {
                                if ($this->oCon->NumFilas() > 0) {
                                    do {
                                        $ctab_num_ctab     = $this->oCon->f('ctab_num_ctab');
                                    } while ($this->oCon->SiguienteRegistro());
                                }
                            }
                            $this->oCon->Free();
                        }


                        ///VALIDACION MONEDA EXTRANJERA

                        if ($this->pcon_mon_base != $fact_cod_mone) {
                            $fact_iva       = $fact_iva / $fact_val_tcam;
                            $fact_con_miva  = $fact_con_miva / $fact_val_tcam;
                            $fact_sin_miva  = $fact_sin_miva / $fact_val_tcam;
                            $fact_tot_fact  = $fact_tot_fact / $fact_val_tcam;
                            $fact_cm4_fact  = $fact_cm4_fact / $fact_val_tcam;
                            if ($fact_cod_detra > 0) {
                                $valor_detra_rest   = $valor_detra / $fact_val_tcam;
                                $valor_detra_rest = round($valor_detra_rest,0,PHP_ROUND_HALF_UP);
                            } else {
                                $valor_detra_rest   = 0;
                            }

                            $fact_iva       = floatval(number_format($fact_iva, 2, '.', ''));
                            $fact_con_miva  = floatval(number_format($fact_con_miva, 2, '.', ''));
                            $fact_sin_miva  = floatval(number_format($fact_sin_miva, 2, '.', ''));
                            $fact_tot_fact  = floatval(number_format($fact_tot_fact, 2, '.', ''));
                            $fact_cm4_fact  = floatval(number_format($fact_cm4_fact, 2, '.', ''));
                        } else {
                            $fact_iva = floatval(number_format($fact_iva, 2, '.', ''));
                            $fact_con_miva = floatval(number_format($fact_con_miva, 2, '.', ''));
                            $fact_sin_miva = floatval(number_format($fact_sin_miva, 2, '.', ''));
                            $fact_tot_fact = floatval(number_format($fact_tot_fact, 2, '.', ''));
                            $fact_cm4_fact = floatval(number_format($fact_cm4_fact, 2, '.', ''));
                            if ($fact_cod_detra > 0) {
                                $valor_detra_rest   = $valor_detra;
                                $valor_detra_rest = round($valor_detra_rest,0,PHP_ROUND_HALF_UP);
                            } else {
                                $valor_detra_rest   = 0;
                            }
                        }


                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);


                        if ($fact_cm4_fact == null) {
                            $fact_cm4_fact = 0;
                        }

                        if (intval($tipo_doc) == 1) {
                            $tipo_docu = '6';
                            $tipo_envio = '01';
                        } else if (intval($tipo_doc) == 2) {
                            $tipo_docu = '1';
                            $tipo_envio = '03';
                        } else if (intval($tipo_doc) == 3) {
                            $tipo_docu = '7';
                            $tipo_envio = '03';
                        } else {
                            $tipo_docu = '0';
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetras($fact_tot_fact, "SOLES"));

                $fact_hor_ini = substr($fact_hor_ini, -8);
                $fact_hor_fin = substr($fact_hor_fin, -8);
                //$fact_fech_fact = $fact_fech_fact . "T" . $fact_hor_ini . "-00:00";
                //$fact_fech_venc = $fact_fech_venc . "T" . $fact_hor_fin . "-00:00";
                $serie = $fact_nse_fact . '-' . $fact_num_preimp;

                if(empty($fact_cod_mone)){
                    $fact_cod_mone = 1;
                }
                //MONEDA
                $sql = "SELECT mone_des_mone from saemone where mone_cod_mone = $fact_cod_mone";
                $mone_des_mone = consulta_string($sql, 'mone_des_mone', $this->oIfx, '');

                $sql = "SELECT sucu_cod_agen from saesucu where sucu_cod_sucu = $fact_cod_sucu";
                $sucu_cod_agen = consulta_string($sql, 'sucu_cod_agen', $this->oIfx, '');

                $data = array(
                    "cliente" => strval($fact_nom_cliente),
                    "NroDocCliente" => strval($fact_ruc_clie),
                    "TipoDocCliente" => strval($tipo_docu),
                    "DirCliente" => strval($fact_dir_clie),
                    "TipoDoc" => strval($tipo_envio),
                    "CodVenta" => $serie,
                    "Serie" => $fact_nse_fact,
                    "Correlativo" => $fact_num_preimp,
                    "FechaEmision" => $fact_fech_fact,
                    "HoraEmision" => $fact_hor_ini,
                    "FechaVencimiento" => $fact_fech_venc,
                    "items" => [],
                    "cuotas" => [],
                    "datosguia" => [],
                    "Moneda" => $mone_des_mone,
                    "FormaPago" => [],
                    "Base" => $fact_con_miva,
                    "Igv" => $fact_iva,
                    "MontoExcento" => $fact_sin_miva,
                    "MontoGratuito" => $fact_sin_miva,
                    "Descuento" => floatval($fact_cm1_fact),
                    "TotalDocumento" => $fact_tot_fact,
                    "FechaReferencia" => null,
                    "HoraReferencia" => null,
                    "TipoReferencia" => null,
                    "DocumentoReferencia" => null,
                    "CodMotivo" => null,
                    "Motivo" => null,
                    "Porcentaje" => [],
                    "NGuia" => 0,
                    "TipoCambio" => 0,
                    "otros" => "DOCUMENTO GENERADO EN JIREH WEB",
                    "Detraccion" => 0,//COLOCAR
                    "PorcDetraccion" => 0,//COLOCAR
                    "MontoDetraccion" => 0,//COLOCAR
                    "RegimenPercepcion" => 0,
                    "TasaPercepcion" => 0,
                    "MontoPercepcion" => 0,
                    "ruc" => $this->empr_ruc_empr,
                    "idSucursal" => intval($sucu_cod_agen), // LO BRINDA APUFACT
                    "placa" => null,
                    "Estado" => 1
                );

                if($fact_cod_detra > 0){

                    $data['Detraccion'] = 1;
                    $data['PorcDetraccion'] = floatval($tret_porct);
                    $data['MontoDetraccion'] = round(floatval($valor_detra),0,PHP_ROUND_HALF_UP);
                }

                $sql = "SELECT
                        fx.fxfp_cod_fpag,
                        fx.fxfp_val_fxfp,
                        fx.fxfp_fec_fin,
                        fx.fxfp_cot_fpag
                        FROM
                        saefxfp fx 
                        WHERE fx.fxfp_cod_fact = $fact_cod_fact ;";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {
                            $fxfp_cod_fpag  = $this->oIfx->f('fxfp_cod_fpag');
                            $fxfp_val_fxfp  = $this->oIfx->f('fxfp_val_fxfp');
                            $fxfp_fec_fin   = $this->oIfx->f('fxfp_fec_fin');
                            $tipo           = $this->oIfx->f('fxfp_cot_fpag');

                            if ($this->pcon_mon_base != $fact_cod_mone) {
                                $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                            }

                            $fxfp_val_fxfp = $fxfp_val_fxfp - $valor_detra_rest;

                            if ($tipo == 'EFE') {
                                $tipo = 'CONTADO';
                            } else if ($tipo == 'CRE') {
                                $tipo = 'CREDITO';

                                $data['cuotas'][$j++] = [
                                    "NroCuota" => intval($j),
                                    "MontoCuota" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                    "FechaCuota" => $fxfp_fec_fin
                                ];
                            } else if ($tipo == 'TAR') {
                                $tipo = 'TARJETA';
                            } else {
                                $tipo = 'CONTADO';
                            }

                            $data['FormaPago'] = $tipo;
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                $sql = "SELECT dfac_cant_dfac, dfac_cod_prod, dfac_det_dfac, dfac_mont_total, dfac_por_iva, dfac_nom_prod  FROM saedfac WHERE dfac_cod_fact = $fact_cod_fact";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {
                            $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                            $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                            $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                            $dfac_nom_prod = $this->oIfx->f('dfac_nom_prod');

                            $detallefac= $dfac_nom_prod.' '.$dfac_det_dfac;

                            $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                            $dfac_por_iva = $this->oIfx->f('dfac_por_iva');


                            if ($this->pcon_mon_base != $fact_cod_mone) {
                                $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                            }


                            $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                            $precio_uni = $dfac_mont_total * $porcentaje_iva;
                            $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;

                            if (empty($detallefac)) {
                                $detallefac = 'Sin detalle';
                            }

                            $data['items'][$j++] =
                                [
                                    "CodigoItem" => $dfac_cod_prod,
                                    "Descripcion" => $detallefac,
                                    "Unidad" => "NIU",
                                    "Cantidad" => floatval($dfac_cant_dfac),
                                    "Precio" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "SubTotal" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "Igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "Descuento" => 0,
                                    "Total" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "Lote" => null,
                                    "FechaVcto" => null
                                ];

                            $data['Porcentaje'] = floatval($dfac_por_iva);
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                $datos_envio = array($data);

                /**
                 * EMPIEZA ENVIO
                 */
                $headers = array(
                    "Content-Type:application/json"
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/InsertDocumento");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos_envio));
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);

                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    switch ($http_code) {
                        case 200:
                            $validacion = $data_json[0]['estatus'];

                            if ($validacion == 1) {
                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $fact_cod_hash = $data_json[0]['documento'];

                                    $sql_update = "UPDATE saefact SET fact_aprob_sri = 'S',fact_cod_hash='$fact_cod_hash' WHERE fact_cod_fact = $fact_cod_fact ;";

                                    $this->oIfx->QueryT($sql_update);
                                    $this->oIfx->QueryT('COMMIT;');

                                    $correoMsj = envio_correo_adj_sunat($fact_email_clpv, $ruta_xml, $ruta_pdf, $fact_nom_cliente, $nombre_documento, $serie, $fact_cod_clpv, $tipo_envio);

                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => $data_json[0]['Message'],
                                        'result_email' => 'Autorizado (' . $correoMsj . ')',
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }


                            } else {
                                $error = "Codigo: " . $data_json['estatus'] . "<br>";
                                $error .= "Error: " . $data_json['error'] . "<br>";
                                throw new Exception($error);
                            }

                            break;
                        default:
                            throw new Exception("Error desconocido en el WebService, Consulte con el administrador");
                    }

                } else {
                    $errorMessage = curl_error($ch);
                    throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
                }
            }else{
                throw new Exception("Documento ya se encuentra Autorizado");
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }

    function GenerarFacturaBoletaXmlPdfPeru($headers, $nombre_documento, $data)
    {
        //GENERA XML
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/invoice/xml?token=" . $this->empr_token_api);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data_xml = curl_exec($ch);

        $ruta_upload = "upload";
        $ruta = "upload/xml";
        $ruta_pdf = "upload/pdf";

        if (!file_exists($ruta_upload)) {
            mkdir($ruta_upload, 0777);
        }

        if (!file_exists($ruta)) {
            mkdir($ruta, 0777);
        }

        if (!file_exists($ruta_pdf)) {
            mkdir($ruta_pdf, 0777);
        }

        $nombre = "fac_".$nombre_documento . ".xml";
        $ruta_xml = $ruta . '/' . $nombre;
        $archivo = fopen($ruta_xml, "w+");
        fwrite($archivo, utf8_encode($data_xml));
        fclose($archivo);

        //GENERA PDF
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/invoice/pdf?token=" . $this->empr_token_api);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data_pdf = curl_exec($ch);

        $ruta_pdf = 'upload/pdf/fac_' . $nombre_documento . '.pdf';
//        header('Content-Type: application/pdf');
        file_put_contents($ruta_pdf, $data_pdf);

        return true;
    }

    /**
     * NOTAS DE DEBITO ENVIO A LA SUNAT
     */

    function EnviarNotaDebitoPeru($fact_cod_fact)
    {
        try {

            $sql = "SELECT
	        fac.fact_nse_fact,
	        fac.fact_num_preimp,
	        fac.fact_fech_fact,
	        fac.fact_hor_ini,
	        fac.fact_cod_clpv,
	        fac.fact_nom_cliente,
	        fac.fact_ruc_clie,
	        fac.fact_dir_clie,
	        fac.fact_email_clpv,
	        fac.fact_tlf_cliente,
	        fac.fact_iva,
	        fac.fact_con_miva,
	        fac.fact_sin_miva,
	        fac.fact_tot_fact,
	        fac.fact_fech_venc,
	        fac.fact_cm4_fact,
	        fac.fact_cm1_fact,
	        fac.fact_hor_fin,
	        fac.fact_cod_sucu,
	        cli.clv_con_clpv,
	        fac.fact_aprob_sri,
	        fact_cod_ndb, 
			fact_aux_preimp,
			fact_fec_emis_aux
            FROM
	        saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
            WHERE fac.fact_cod_fact = $fact_cod_fact;";

            $fact_aprob_sri = 'N';
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $fact_nse_fact = $this->oIfx->f('fact_nse_fact');
                        $fact_num_preimp = $this->oIfx->f('fact_num_preimp');
                        $fact_fech_fact = $this->oIfx->f('fact_fech_fact');
                        $fact_fech_venc = $this->oIfx->f('fact_fech_venc');
                        $fact_hor_ini = $this->oIfx->f('fact_hor_ini');
                        $fact_hor_fin = $this->oIfx->f('fact_hor_fin');
                        $fact_cod_clpv = $this->oIfx->f('fact_cod_clpv');
                        $fact_nom_cliente = $this->oIfx->f('fact_nom_cliente');
                        $fact_ruc_clie = $this->oIfx->f('fact_ruc_clie');
                        $fact_dir_clie = $this->oIfx->f('fact_dir_clie');
                        $fact_email_clpv = $this->oIfx->f('fact_email_clpv');
                        $fact_tlf_cliente = $this->oIfx->f('fact_tlf_cliente');
                        $fact_iva = $this->oIfx->f('fact_iva');
                        $fact_con_miva = $this->oIfx->f('fact_con_miva');
                        $fact_sin_miva = $this->oIfx->f('fact_sin_miva');
                        $fact_tot_fact = $fact_iva + $fact_con_miva + $fact_sin_miva;
                        $fact_cm4_fact = $this->oIfx->f('fact_cm4_fact');
                        $fact_cm1_fact = $this->oIfx->f('fact_cm1_fact');
                        $fact_cod_sucu = $this->oIfx->f('fact_cod_sucu');
                        $tipo_doc = $this->oIfx->f('clv_con_clpv');
                        $fact_aprob_sri = $this->oIfx->f('fact_aprob_sri');

                        $fact_cod_ndb = $this->oIfx->f('fact_cod_ndb');
                        $fact_aux_preimp = $this->oIfx->f('fact_aux_preimp');
                        $fact_fec_emis_aux = $this->oIfx->f('fact_fec_emis_aux');

                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                        $fact_iva = floatval(number_format($fact_iva, 2, '.', ''));
                        $fact_con_miva = floatval(number_format($fact_con_miva, 2, '.', ''));
                        $fact_sin_miva = floatval(number_format($fact_sin_miva, 2, '.', ''));
                        $fact_tot_fact = floatval(number_format($fact_tot_fact, 2, '.', ''));
                        $fact_cm4_fact = floatval(number_format($fact_cm4_fact, 2, '.', ''));

                        if ($fact_cm4_fact == null) {
                            $fact_cm4_fact = 0;
                        }

                        if (intval($tipo_doc) == 1) {
                            $tipo_docu = '6';
                            $tipo_envio = '01';
                        } else if (intval($tipo_doc) == 2) {
                            $tipo_docu = '1';
                            $tipo_envio = '03';
                        } else if (intval($tipo_doc) == 3) {
                            $tipo_docu = '7';
                            $tipo_envio = '03';
                        } else {
                            $tipo_docu = '0';
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetras($fact_tot_fact, "SOLES"));

                $fact_hor_ini = substr($fact_hor_ini, -8);
                $fact_hor_fin = substr($fact_hor_fin, -8);
                $fact_fech_fact = $fact_fech_fact . "T" . $fact_hor_ini . "-00:00";
                $fact_fech_venc = $fact_fech_venc . "T" . $fact_hor_fin . "-00:00";
                $serie = $fact_nse_fact . '-' . $fact_num_preimp;
                /**
                 * EXPLODE FACTURA MODIFICA
                 */

                $exp_fac = explode("-", $fact_aux_preimp);
                $nse_fac = substr($exp_fac[0], 3, 9);
                $num_fac = (int) $exp_fac[1];
                $numDocfectado = "$nse_fac-$num_fac";
                $tipo_doc = "08";

                if(!$fact_cm1_fact){
                    $fact_cm1_fact = "NOTA DE DEBITO AFECTA FACTURA $numDocfectado";
                }

                $data = array(
                    "ublVersion" => "2.1",
                    "tipoDoc" => $tipo_doc,
                    "serie" => $fact_nse_fact,
                    "correlativo" => (int) $fact_num_preimp,
                    "fechaEmision" => $fact_fech_fact,

                    "tipDocAfectado" => "01",
                    "numDocfectado" => $numDocfectado,
                    "codMotivo" => "01",
                    "desMotivo" => $fact_cm1_fact,

                    "client" => [
                        "tipoDoc" => $tipo_docu,
                        "numDoc" => $fact_ruc_clie,
                        "rznSocial" => $fact_nom_cliente,
                        "address" => [
                            "direccion" => $fact_dir_clie
                        ],
                        "email" => $fact_email_clpv,
                        "telephone" => $fact_tlf_cliente,
                    ],
                    "company" => [
                        "ruc" => $this->empr_ruc_empr,
                        "razonSocial" => $this->empr_nom_empr,
                        "nombreComercial" => $this->empr_nom_empr,
                        "address" => [
                            "direccion" => $this->empr_dir_empr
                        ],
                        "email" => $this->empr_mai_empr,
                        "telephone" => $this->empr_tel_resp,
                    ],
                    "tipoMoneda" => "PEN",
                    "mtoOperGravadas" => $fact_con_miva,
                    "mtoOperInafectas" => $fact_sin_miva,
                    "mtoIGV" => $fact_iva,
                    "valorVenta" => $fact_con_miva,
                    "subTotal" => $fact_tot_fact,
                    "mtoImpVenta" => $fact_tot_fact,
                    "details" => [],
                    "observacion" => "DOCUMENTO GENERADO EN JIREH WEB",
                    "totalImpuestos" => $fact_iva,
                    "fecVencimiento" => $fact_fech_venc,
                    "sumDsctoGlobal" => $fact_cm4_fact,
                    "mtoDescuentos" => $fact_cm1_fact,
                    "legends" => [
                        [
                            "code" => "1000",
                            "value" => $con_letra
                        ]
                    ],
                );

                $sql = "SELECT dfac_cant_dfac, dfac_cod_prod, dfac_det_dfac, dfac_mont_total, dfac_por_iva  FROM saedfac WHERE dfac_cod_fact = $fact_cod_fact";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {
                            $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                            $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                            $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                            $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                            $dfac_por_iva = $this->oIfx->f('dfac_por_iva');

                            $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                            $precio_uni = $dfac_mont_total * $porcentaje_iva;
                            $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;

                            if (empty($dfac_det_dfac)) {
                                $dfac_det_dfac = 'Sin detalle';
                            }

                            $data['details'][$j++] =
                                [
                                    "unidad" => "NIU",
                                    "cantidad" => floatval($dfac_cant_dfac),
                                    "codProducto" => $dfac_cod_prod,
                                    "descripcion" => $dfac_det_dfac,
                                    "mtoValorUnitario" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "tipAfeIgv" => "10",
                                    "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                ];
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                /**
                 * EMPIEZA ENVIO
                 */
                $headers = array(
                    "Content-Type:application/json"
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/note/send?token=" . $this->empr_token_api);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);

                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    switch ($http_code) {
                        case 200:
                            $validacion = $data_json['sunatResponse']['success'];

                            if ($validacion) {
                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_envio . '-' . $fact_nse_fact . '-' . $fact_num_preimp;
                                    $this->GenerarNotaDebitoXmlPdfPeru($headers, $nombre_documento, $data);

                                    $ruta_xml = 'upload/xml/deb_' . $nombre_documento . '.xml';
                                    $ruta_pdf = 'upload/pdf/deb_' . $nombre_documento . '.pdf';

                                    $div_button = '<div class="btn-group" role="group" aria-label="...">
                                        <a href="upload/xml/deb_' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                            <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                        </a>
                                        <a href="upload/pdf/deb_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>
                                    </div>';


                                    $sql_update = "UPDATE saefact SET fact_aprob_sri = 'S' WHERE fact_cod_fact = $fact_cod_fact;";
                                    $this->oIfx->QueryT($sql_update);
                                    $this->oIfx->QueryT('COMMIT;');

                                    $correoMsj = envio_correo_adj_sunat($fact_email_clpv, $ruta_xml, $ruta_pdf, $fact_nom_cliente, $nombre_documento, $serie, $fact_cod_clpv, $tipo_envio, 'NOTA DE DEBITO');

                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => $data_json['sunatResponse']['cdrResponse']['description'],
                                        'result_email' => 'Autorizado (' . $correoMsj . ')'
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }


                            } else {
                                $error = "Codigo: " . $data_json['sunatResponse']['error']['code'] . "<br>";
                                $error .= "Error: " . $data_json['sunatResponse']['error']['message'] . "<br>";
                                throw new Exception($error);
                            }

                            break;
                        default:
                            throw new Exception("Error desconocido en el WebService, Consulte con el administrador");
                    }

                } else {
                    $errorMessage = curl_error($ch);
                    throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
                }
            }else{
                throw new Exception("Documento ya se encuentra Autorizado");
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }

    function GenerarNotaDebitoXmlPdfPeru($headers, $nombre_documento, $data)
    {
        //GENERA XML
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/note/xml?token=" . $this->empr_token_api);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data_xml = curl_exec($ch);

        $ruta_upload = "upload";
        $ruta = "upload/xml";
        $ruta_pdf = "upload/pdf";

        if (!file_exists($ruta_upload)) {
            mkdir($ruta_upload, 0777);
        }

        if (!file_exists($ruta)) {
            mkdir($ruta, 0777);
        }

        if (!file_exists($ruta_pdf)) {
            mkdir($ruta_pdf, 0777);
        }

        $nombre = "deb_".$nombre_documento . ".xml";
        $ruta_xml = $ruta . '/' . $nombre;
        $archivo = fopen($ruta_xml, "w+");
        fwrite($archivo, utf8_encode($data_xml));
        fclose($archivo);

        //GENERA PDF
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/note/pdf?token=" . $this->empr_token_api);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data_pdf = curl_exec($ch);

        $ruta_pdf = 'upload/pdf/deb_' . $nombre_documento . '.pdf';
//        header('Content-Type: application/pdf');
        file_put_contents($ruta_pdf, $data_pdf);

        return true;
    }


    /**
     * NOTAS DE CREDITO ENVIO A LA SUNAT
     */

    function EnviarNotaCreditoPeru($ncre_cod_ncre)
    {
        try {

            session_start();
            $id_usuario = $_SESSION['U_ID'];

            $idempresa = $_SESSION['U_EMPRESA'];


            $sql = "SELECT
	        fac.ncre_nse_ncre as fact_nse_fact,
	        fac.ncre_num_preimp as fact_num_preimp,
	        fac.ncre_fech_fact as fact_fech_fact,
	        fac.ncre_fech_fact as fact_hor_ini,
	        fac.ncre_cod_clpv as fact_cod_clpv,
	        fac.ncre_nom_cliente as fact_nom_cliente,
	        
	        fac.ncre_ruc_clie as fact_ruc_clie,
	        fac.ncre_dir_clie as fact_dir_clie,
	        fac.ncre_email_clpv as fact_email_clpv,
	        fac.ncre_tlf_cliente as fact_tlf_cliente,
	        fac.ncre_iva as fact_iva,
	        fac.ncre_con_miva as fact_con_miva,
	        fac.ncre_sin_miva as fact_sin_miva,
	        fac.ncre_tot_fact as fact_tot_fact,
	        fac.ncre_fech_venc as fact_fech_venc,
	        fac.ncre_cm1_ncre as fact_cm4_fact,
	        fac.ncre_cm1_ncre as fact_cm1_fact,
	        fac.ncre_cm1_ncre as motivo_ncre,
	        fac.ncre_fech_fact as fact_hor_fin,
	        fac.ncre_fech_docu as fech_fact,
	        fac.ncre_cod_sucu as fact_cod_sucu,
	        cli.clv_con_clpv,
	        fac.ncre_aprob_sri as fact_aprob_sri,
	        fac.ncre_cod_fact as fact_cod_ndb, 
			fac.ncre_cod_aux as fact_aux_preimp,
			fac.ncre_fech_fact as fact_fec_emis_aux,
            fac.ncre_cod_mone as fact_cod_mone
            FROM
	        saencre fac inner join saeclpv cli on fac.ncre_cod_clpv = cli.clpv_cod_clpv
            WHERE fac.ncre_cod_ncre = $ncre_cod_ncre;";

            $fact_aprob_sri = 'N';
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $fact_nse_fact = $this->oIfx->f('fact_nse_fact');
                        $fact_num_preimp = $this->oIfx->f('fact_num_preimp');
                        $fact_fech_fact = $this->oIfx->f('fact_fech_fact');
                        $fact_fech_venc = $this->oIfx->f('fact_fech_venc');
                        $fact_cod_clpv = $this->oIfx->f('fact_cod_clpv');
                        $fact_nom_cliente = $this->oIfx->f('fact_nom_cliente');
                        $fact_ruc_clie = $this->oIfx->f('fact_ruc_clie');
                        $fact_dir_clie = $this->oIfx->f('fact_dir_clie');
                        $fact_email_clpv = $this->oIfx->f('fact_email_clpv');
                        $fact_tlf_cliente = $this->oIfx->f('fact_tlf_cliente');
                        $fact_iva = $this->oIfx->f('fact_iva');
                        $fact_con_miva = $this->oIfx->f('fact_con_miva');
                        $fact_sin_miva = $this->oIfx->f('fact_sin_miva');
                        $fact_tot_fact = $fact_iva + $fact_con_miva + $fact_sin_miva;
                        $fact_cm4_fact = $this->oIfx->f('fact_cm4_fact');
                        $fact_cm1_fact = $this->oIfx->f('fact_cm1_fact');
                        $fact_cod_sucu = $this->oIfx->f('fact_cod_sucu');
                        $tipo_doc = $this->oIfx->f('clv_con_clpv');
                        $fact_aprob_sri = $this->oIfx->f('fact_aprob_sri');
                        $fact_cod_mone = $this->oIfx->f('fact_cod_mone');
                        $fact_cod_ndb = $this->oIfx->f('fact_cod_ndb');
                        $fact_aux_preimp = $this->oIfx->f('fact_aux_preimp');
                        $fact_fec_emis_aux = $this->oIfx->f('fact_fec_emis_aux');
                        $motivo_ncre = $this->oIfx->f('motivo_ncre');
                        $fech_fact = $this->oIfx->f('fech_fact');

                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                        if ($this->pcon_mon_base != $fact_cod_mone) {
                            $fact_iva           = $fact_iva / $fact_val_tcam;
                            $fact_con_miva      = $fact_con_miva / $fact_val_tcam;
                            $fact_sin_miva      = $fact_sin_miva / $fact_val_tcam;
                            $fact_tot_fact      = $fact_tot_fact / $fact_val_tcam;
                            $fact_cm4_fact      = $fact_cm4_fact / $fact_val_tcam;

                            $fact_iva           = floatval(number_format($fact_iva, 2, '.', ''));
                            $fact_con_miva      = floatval(number_format($fact_con_miva, 2, '.', ''));
                            $fact_sin_miva      = floatval(number_format($fact_sin_miva, 2, '.', ''));
                            $fact_tot_fact      = floatval(number_format($fact_tot_fact, 2, '.', ''));
                            $fact_cm4_fact      = floatval(number_format($fact_cm4_fact, 2, '.', ''));
                        } else {
                            $fact_iva           = floatval(number_format($fact_iva, 2, '.', ''));
                            $fact_con_miva      = floatval(number_format($fact_con_miva, 2, '.', ''));
                            $fact_sin_miva      = floatval(number_format($fact_sin_miva, 2, '.', ''));
                            $fact_tot_fact      = floatval(number_format($fact_tot_fact, 2, '.', ''));
                            $fact_cm4_fact      = floatval(number_format($fact_cm4_fact, 2, '.', ''));
                        }

                        

                        if ($fact_cm4_fact == null) {
                            $fact_cm4_fact = 0;
                        }

                        if (intval($tipo_doc) == 1) {
                            $tipo_docu = '6';
                            $tipo_envio = '01';
                        } else if (intval($tipo_doc) == 2) {
                            $tipo_docu = '1';
                            $tipo_envio = '03';
                        } else if (intval($tipo_doc) == 3) {
                            $tipo_docu = '7';
                            $tipo_envio = '03';
                        } else {
                            $tipo_docu = '0';
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetras($fact_tot_fact, "SOLES"));

                $fact_fech_fact = $fact_fech_fact;
                $fact_fech_venc = $fact_fech_venc;
                $serie = $fact_nse_fact . '-' . $fact_num_preimp;
                /**
                 * EXPLODE FACTURA MODIFICA
                 */

                if($fact_cod_ndb){

                    $sql = "SELECT
	                fac.fact_nse_fact, fac.fact_num_preimp, fac.fact_fech_fact, fac.fact_hor_ini
                    FROM
	                saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
                    WHERE fac.fact_cod_fact = $fact_cod_ndb;";

                    if ($this->oIfx->Query($sql)) {
                        if ($this->oIfx->NumFilas() > 0) {
                            do {
                                $fact_num_preimp_afect = strval($this->oIfx->f('fact_num_preimp'));
                                $fact_nse_fact_afect = substr( $this->oIfx->f('fact_nse_fact'), 3, 9);
                                $fecha_factura = $this->oIfx->f('fact_fech_fact');
                                $fact_hor_ini = $this->oIfx->f('fact_hor_ini');

                            } while ($this->oIfx->SiguienteRegistro());
                        }
                    }
                    $this->oIfx->Free();

                    $numDocfectado = "$fact_nse_fact_afect-$fact_num_preimp_afect";
                }else{
                    if(strlen($fact_aux_preimp)>=11 && $fact_cod_ndb > 0){
                        $exp_fac = explode("-", $fact_aux_preimp);
                        $nse_fac = substr($exp_fac[0], 3, 9);
                        $num_fac = strval($exp_fac[1]);
                        $numDocfectado = "$nse_fac-$num_fac";
                    }else{
                        $numDocfectado = "$fact_aux_preimp";
                    }

                    $fecha_factura = $fech_fact;
                    $fact_hor_ini = date("H:i:s");
                }

                $sql = "SELECT sucu_cod_agen from saesucu where sucu_cod_sucu = $fact_cod_sucu";
                $sucu_cod_agen = consulta_string($sql, 'sucu_cod_agen', $this->oIfx, '');

                $tipo_doc = "07";

                if(!$fact_cm1_fact){
                    $fact_cm1_fact = "NOTA DE CREDITO AFECTA FACTURA $numDocfectado";
                }

                if(empty($fact_cod_mone)){
                    $fact_cod_mone = 1;
                }

                //MONEDA
                $sql = "SELECT mone_des_mone from saemone where mone_cod_mone = $fact_cod_mone";
                $mone_des_mone = consulta_string($sql, 'mone_des_mone', $this->oIfx, '');

                $sql = "SELECT sucu_cod_agen from saesucu where sucu_cod_sucu = $fact_cod_sucu";
                $sucu_cod_agen = consulta_string($sql, 'sucu_cod_agen', $this->oIfx, '');

                if(empty($motivo_ncre)){
                    throw new Exception("Sin motivo configurado en la nota de credito");
                }
                $sql = "SELECT ddev_tip_ddev, ddev_des_ddev from saeddev where ddev_des_ddev = '$motivo_ncre'";
                $motivo_envio = consulta_string($sql, 'ddev_tip_ddev', $this->oIfx, '');
                $motivo_envio_nombre = consulta_string($sql, 'ddev_des_ddev', $this->oIfx, '');
 
                $data = array(
                    "cliente" => strval($fact_nom_cliente),
                    "NroDocCliente" => strval($fact_ruc_clie),
                    "TipoDocCliente" => strval($tipo_docu),
                    "DirCliente" => strval($fact_dir_clie),
                    "TipoDoc" => strval($tipo_doc),
                    "CodVenta" => $serie,
                    "Serie" => $fact_nse_fact,
                    "Correlativo" => $fact_num_preimp,
                    "FechaEmision" => $fact_fech_fact,
                    "HoraEmision" => $fact_hor_ini,
                    "FechaVencimiento" => $fact_fech_venc,
                    "items" => [],
                    "cuotas" => [],
                    "datosguia" => [],
                    "Moneda" => $mone_des_mone,
                    "FormaPago" => [],
                    "Base" => $fact_con_miva,
                    "Igv" => $fact_iva,
                    "MontoExcento" => $fact_sin_miva,
                    "MontoGratuito" => $fact_sin_miva,
                    "Descuento" => floatval($fact_cm1_fact),
                    "TotalDocumento" => $fact_tot_fact,
                    "Porcentaje" => [],
                    "NGuia" => 0,
                    "TipoCambio" => 0,

                    "FechaReferencia" => $fecha_factura,
                    "HoraReferencia" => $fact_hor_ini,
                    "TipoReferencia" => $tipo_envio,
                    "DocumentoReferencia" => $numDocfectado,
                    "CodMotivo" => $motivo_envio,
                    "Motivo" => 'NC',

                    "otros" => "NC GENERADO EN JIREH WEB",
                    "Detraccion" => 0,//COLOCAR
                    "PorcDetraccion" => 0,//COLOCAR
                    "MontoDetraccion" => 0,//COLOCAR
                    "RegimenPercepcion" => 0,
                    "TasaPercepcion" => 0,
                    "MontoPercepcion" => 0,
                    "ruc" => $this->empr_ruc_empr,
                    "idSucursal" => intval($sucu_cod_agen), // LO BRINDA APUFACT
                    "placa" => null,
                    "Estado" => 1
                );

                if($fact_cod_ndb){
                    $sql = "SELECT
                            fx.fxfp_cod_fpag,
                            fx.fxfp_val_fxfp,
                            fx.fxfp_fec_fin,
                            fx.fxfp_cot_fpag
                            FROM
                            saefxfp fx 
                            WHERE fx.fxfp_cod_fact = $fact_cod_ndb ;";
                    if ($this->oIfx->Query($sql)) {
                        if ($this->oIfx->NumFilas() > 0) {
                            $j = 0;
                            do {
                                $fxfp_cod_fpag  = $this->oIfx->f('fxfp_cod_fpag');
                                $fxfp_val_fxfp  = $this->oIfx->f('fxfp_val_fxfp');
                                $fxfp_fec_fin   = $this->oIfx->f('fxfp_fec_fin');
                                $tipo           = $this->oIfx->f('fxfp_cot_fpag');

                                $fxfp_val_fxfp = $fxfp_val_fxfp - $valor_detra;

                                if ($this->pcon_mon_base != $fact_cod_mone) {
                                    $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                                }

                                if ($tipo == 'EFE') {
                                    $tipo = 'CONTADO';
                                } else if ($tipo == 'CRE') {
                                    $tipo = 'CREDITO';

                                    $data['cuotas'][$j++] = [
                                        "NroCuota" => intval($j),
                                        "MontoCuota" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                        "FechaCuota" => $fxfp_fec_fin
                                    ];
                                } else if ($tipo == 'TAR') {
                                    $tipo = 'TARJETA';
                                } else {
                                    $tipo = 'CONTADO';
                                }

                                $data['FormaPago'] = $tipo;
                            } while ($this->oIfx->SiguienteRegistro());
                        }
                    }
                    $this->oIfx->Free();
                }else{
                    $data['FormaPago'] = "CONTADO";
                }

                $sql = "select 
                        dncr_cant_dfac as dfac_cant_dfac,
                        dncr_cod_prod as dfac_cod_prod,
                        dncr_det_dncr as dfac_det_dfac,
                        dncr_mont_total as dfac_mont_total,
                        dncr_por_iva as dfac_por_iva,
                        dncr_nom_prod as dfac_nom_prod
                        from saedncr
                        where dncr_cod_ncre = $ncre_cod_ncre;";

                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {
                            $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                            $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                            $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                            $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                            $dfac_por_iva = $this->oIfx->f('dfac_por_iva');

                            $dfac_nom_prod = $this->oIfx->f('dfac_nom_prod');

                            $detallefac= $dfac_nom_prod.' '.$dfac_det_dfac;

                            if ($this->pcon_mon_base != $fact_cod_mone) {
                                $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                            }


                            $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                            $precio_uni = $dfac_mont_total * $porcentaje_iva;
                            $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;

                            if (empty($detallefac)) {
                                $detallefac = 'Sin detalle';
                            }

                            $data['items'][$j++] =
                                [
                                    "CodigoItem" => $dfac_cod_prod,
                                    "Descripcion" => $detallefac,
                                    "Unidad" => "NIU",
                                    "Cantidad" => floatval($dfac_cant_dfac),
                                    "Precio" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "SubTotal" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "Igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "Descuento" => 0,
                                    "Total" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "Lote" => null,
                                    "FechaVcto" => null
                                ];

                            $data['Porcentaje'] = floatval($dfac_por_iva);
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                $datos_envio = array($data);
                
                /**
                 * EMPIEZA ENVIO
                 */
                $headers = array(
                    "Content-Type:application/json"
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/InsertDocumento");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos_envio));
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);

                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);
                    switch ($http_code) {
                        case 200:
                            $validacion = $data_json[0]['estatus'];

                            if ($validacion == 1) {
                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $fact_cod_hash = $data_json[0]['documento'];

                                    $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'S', ncre_cod_hash='$fact_cod_hash', ncre_user_sri = $id_usuario WHERE ncre_cod_ncre = $ncre_cod_ncre ;";

                                    $this->oIfx->QueryT($sql_update);
                                    $this->oIfx->QueryT('COMMIT;');

                                    $correoMsj = envio_correo_adj_sunat($fact_email_clpv, $ruta_xml, $ruta_pdf, $fact_nom_cliente, $nombre_documento, $serie, $fact_cod_clpv, $tipo_envio);

                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => $data_json[0]['Message'],
                                        'result_email' => 'Autorizado (' . $correoMsj . ')',
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }


                            } else if($validacion == 0){
                                $error = "Codigo: " . $data_json[0]['estatus'] . "<br>";
                                $error .= "Mensaje: " . $data_json[0]['Message'] . "<br>";

                                $error_250 = substr($data_json['Message'], 0, 250);
                                $error_250 = str_replace("'", "", $error_250);

                                $this->oIfx->QueryT('BEGIN;');
                                $sql_update = "UPDATE saencre SET ncre_erro_sri = '$error_250' WHERE ncre_cod_ncre = $ncre_cod_ncre";
                                $this->oIfx->QueryT($sql_update);
                                $this->oIfx->QueryT('COMMIT;');

                                throw new Exception($error);
                            }else {
                                $error = "Codigo: " . $data_json[0]['estatus'] . "<br>";
                                $error .= "Error: " . $data_json[0]['error'] . "<br>";

                                $error_250 = substr($data_json['error'], 0, 250);
                                $error_250 = str_replace("'", "", $error_250);

                                $this->oIfx->QueryT('BEGIN;');
                                $sql_update = "UPDATE saencre SET ncre_erro_sri = '$error_250' WHERE ncre_cod_ncre = $ncre_cod_ncre";
                                $this->oIfx->QueryT($sql_update);
                                $this->oIfx->QueryT('COMMIT;');

                                throw new Exception($error);
                            }

                            break;
                        default:
                            throw new Exception("Error desconocido en el WebService, Consulte con el administrador");
                    }

                } else {
                    $errorMessage = curl_error($ch);
                    throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
                }
            }else{
                throw new Exception("Documento ya se encuentra Autorizado");
            }


        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }

    function GenerarNotaCreditoXmlPdfPeru($headers, $nombre_documento, $data)
    {
        //GENERA XML
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/note/xml?token=" . $this->empr_token_api);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data_xml = curl_exec($ch);

        $ruta_upload = "upload";
        $ruta = "upload/xml";
        $ruta_pdf = "upload/pdf";

        if (!file_exists($ruta_upload)) {
            mkdir($ruta_upload, 0777);
        }

        if (!file_exists($ruta)) {
            mkdir($ruta, 0777);
        }

        if (!file_exists($ruta_pdf)) {
            mkdir($ruta_pdf, 0777);
        }

        $nombre = "cred_".$nombre_documento . ".xml";
        $ruta_xml = $ruta . '/' . $nombre;
        $archivo = fopen($ruta_xml, "w+");
        fwrite($archivo, utf8_encode($data_xml));
        fclose($archivo);

        //GENERA PDF
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/note/pdf?token=" . $this->empr_token_api);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data_pdf = curl_exec($ch);

        $ruta_pdf = 'upload/pdf/cred_' . $nombre_documento . '.pdf';
//        header('Content-Type: application/pdf');
        file_put_contents($ruta_pdf, $data_pdf);

        return true;
    }

    /**
     * GUIAS DE REMISION ENVIO A LA SUNAT
     */

    function EnviarGuiasRemisionPeru($guia_cod_guia)
    {
        try {

            $sql = "SELECT
	        fac.guia_nse_guia as fact_nse_fact,
	        fac.guia_num_preimp as fact_num_preimp,
	        fac.guia_fech_guia as fact_fech_fact,
	        fac.guia_fech_guia as fact_hor_ini,
	        fac.guia_cod_clpv as fact_cod_clpv,
	        fac.guia_nom_cliente as fact_nom_cliente,
	        
	        fac.guia_ruc_clie as fact_ruc_clie,
	        fac.guia_dir_clie as fact_dir_clie,
	        fac.guia_email_clpv as fact_email_clpv,
	        fac.guia_tlf_cliente as fact_tlf_cliente,
	        fac.guia_iva as fact_iva,
	        fac.guia_con_miva as fact_con_miva,
	        fac.guia_sin_miva as fact_sin_miva,
	        fac.guia_tot_guia as fact_tot_fact,
	        fac.guia_fech_venc as fact_fech_venc,
	        fac.guia_cm3_guia as fact_cm4_fact,
	        fac.guia_cm1_guia as fact_cm1_fact,
	        fac.guia_fech_guia as fact_hor_fin,
	        fac.guia_cod_sucu as fact_cod_sucu,
	        cli.clv_con_clpv,
	        fac.guia_aprob_sri as fact_aprob_sri
            FROM
	        saeguia fac inner join saeclpv cli on fac.guia_cod_clpv = cli.clpv_cod_clpv
            WHERE fac.guia_cod_guia = $guia_cod_guia;";

            $fact_aprob_sri = 'N';
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $fact_nse_fact = $this->oIfx->f('fact_nse_fact');
                        $fact_num_preimp = $this->oIfx->f('fact_num_preimp');
                        $fact_fech_fact = $this->oIfx->f('fact_fech_fact');
                        $fact_fech_venc = $this->oIfx->f('fact_fech_venc');
                        $fact_cod_clpv = $this->oIfx->f('fact_cod_clpv');
                        $fact_nom_cliente = $this->oIfx->f('fact_nom_cliente');
                        $fact_ruc_clie = $this->oIfx->f('fact_ruc_clie');
                        $fact_dir_clie = $this->oIfx->f('fact_dir_clie');
                        $fact_email_clpv = $this->oIfx->f('fact_email_clpv');
                        $fact_tlf_cliente = $this->oIfx->f('fact_tlf_cliente');
                        $fact_iva = $this->oIfx->f('fact_iva');
                        $fact_con_miva = $this->oIfx->f('fact_con_miva');
                        $fact_sin_miva = $this->oIfx->f('fact_sin_miva');
                        $fact_tot_fact = $fact_iva + $fact_con_miva + $fact_sin_miva;
                        $fact_cm4_fact = $this->oIfx->f('fact_cm4_fact');
                        $fact_cm1_fact = $this->oIfx->f('fact_cm1_fact');
                        $fact_cod_sucu = $this->oIfx->f('fact_cod_sucu');
                        $tipo_doc = $this->oIfx->f('clv_con_clpv');
                        $fact_aprob_sri = $this->oIfx->f('fact_aprob_sri');

                        $fact_cod_ndb = $this->oIfx->f('fact_cod_ndb');
                        $fact_aux_preimp = $this->oIfx->f('fact_aux_preimp');
                        $fact_fec_emis_aux = $this->oIfx->f('fact_fec_emis_aux');

                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                        $fact_iva = floatval(number_format($fact_iva, 2, '.', ''));
                        $fact_con_miva = floatval(number_format($fact_con_miva, 2, '.', ''));
                        $fact_sin_miva = floatval(number_format($fact_sin_miva, 2, '.', ''));
                        $fact_tot_fact = floatval(number_format($fact_tot_fact, 2, '.', ''));
                        $fact_cm4_fact = floatval(number_format($fact_cm4_fact, 2, '.', ''));

                        if ($fact_cm4_fact == null) {
                            $fact_cm4_fact = 0;
                        }

                        if (intval($tipo_doc) == 1) {
                            $tipo_docu = '6';
                            $tipo_envio = '01';
                        } else if (intval($tipo_doc) == 2) {
                            $tipo_docu = '1';
                            $tipo_envio = '03';
                        } else if (intval($tipo_doc) == 3) {
                            $tipo_docu = '7';
                            $tipo_envio = '03';
                        } else {
                            $tipo_docu = '0';
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetras($fact_tot_fact, "SOLES"));

                $fact_fech_fact = $fact_fech_fact . "T"  . "00:00:00-00:00";
                $fact_fech_venc = $fact_fech_venc . "T" . "00:00:00-00:00";
                $serie = $fact_nse_fact . '-' . $fact_num_preimp;
                /**
                 * EXPLODE FACTURA MODIFICA
                 */

                if($fact_cod_ndb){

                    $sql = "SELECT
	                fac.fact_nse_fact, fac.fact_num_preimp
                    FROM
	                saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
                    WHERE fac.fact_cod_fact = $fact_cod_ndb;";

                    if ($this->oIfx->Query($sql)) {
                        if ($this->oIfx->NumFilas() > 0) {
                            do {
                                $fact_num_preimp_afect = (int) $this->oIfx->f('fact_num_preimp');
                                $fact_nse_fact_afect = substr( $this->oIfx->f('fact_nse_fact'), 3, 9);

                            } while ($this->oIfx->SiguienteRegistro());
                        }
                    }
                    $this->oIfx->Free();

                    $numDocfectado = "$fact_nse_fact_afect-$fact_num_preimp_afect";

                }else{
                    $exp_fac = explode("-", $fact_aux_preimp);
                    $nse_fac = substr($exp_fac[0], 3, 9);
                    $num_fac = (int) $exp_fac[1];
                    $numDocfectado = "$nse_fac-$num_fac";
                }


                $tipo_doc = "07";

                if(!$fact_cm1_fact){
                    $fact_cm1_fact = "NOTA DE CREDITO AFECTA FACTURA $numDocfectado";
                }

                $data = array(
                    "ublVersion" => "2.1",
                    "tipoDoc" => "09",
                    "serie" => $fact_nse_fact,
                    "correlativo" => (int) $fact_num_preimp,
                    "fechaEmision" => $fact_fech_fact,
                    "company" => [
                        "ruc" => $this->empr_ruc_empr,
                        "razonSocial" => $this->empr_nom_empr,
                        "nombreComercial" => $this->empr_nom_empr,
                        "address" => [
                            "direccion" => $this->empr_dir_empr
                        ],
                        "email" => $this->empr_mai_empr,
                        "telephone" => $this->empr_tel_resp,
                    ],
                    "details" => [],
                    "observacion" => "DOCUMENTO GENERADO EN JIREH WEB",
                    "destinatario" => [
                        "tipoDoc" => $tipo_docu,
                        "numDoc" => $fact_ruc_clie,
                        "rznSocial" => $fact_nom_cliente,
                        "address" => [
                            "direccion" => $fact_dir_clie
                        ],
                        "email" => $fact_email_clpv,
                        "telephone" => $fact_tlf_cliente,
                    ],
                    "envio" => [
                        "codTraslado" => "01",
                        "desTraslado" => "VENTA",
                        "modTraslado" => "01",
                        "pesoTotal" => "10",
                        "undPesoTotal" => "KGM",
                        "fecTraslado" => $fact_fech_fact,
                        "transportista" => [
                            "tipoDoc" => "6",
                            "numDoc" => "20000000002",
                            "rznSocial" => "TRANSPORTES S.A.C",
                            "nroMtc" => "0001",
                        ],
                        "llegada" => [
                            "ubigueo" => "150203",
                            "direccion" => "AV. ITALIA 459"
                        ],
                        "partida" => [
                            "ubigueo" => "150203",
                            "direccion" => "AV. ITALIA 459"
                        ],
                    ],
                );

                $sql = "select 
                        dgui_cant_dgui as dfac_cant_dfac,
                        dgui_cod_prod as dfac_cod_prod,
                        dgui_nom_prod as dfac_det_dfac,
                        dgui_mont_total as dfac_mont_total,
                        dgui_por_iva as dfac_por_iva
                        from saedgui
                        where dgui_cod_guia = $guia_cod_guia;";

                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {
                            $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                            $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                            $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                            $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                            $dfac_por_iva = $this->oIfx->f('dfac_por_iva');

                            $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                            $precio_uni = $dfac_mont_total * $porcentaje_iva;
                            $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;

                            if (empty($dfac_det_dfac)) {
                                $dfac_det_dfac = 'Sin detalle';
                            }

                            $data['details'][$j++] =
                                [
                                    "unidad" => "NIU",
                                    "cantidad" => floatval($dfac_cant_dfac),
                                    "codigo" => $dfac_cod_prod,
                                    "descripcion" => $dfac_det_dfac
                                ];
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                /**
                 * EMPIEZA ENVIO
                 */
                $headers = array(
                    "Content-Type:application/json"
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/despatch/send?token=" . $this->empr_token_api);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);

                print_r($respuesta);exit;


                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    print_r($data_json);exit;
                    switch ($http_code) {
                        case 200:
                            $validacion = $data_json['sunatResponse']['success'];

                            if ($validacion) {
                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_envio . '-' . $fact_nse_fact . '-' . $fact_num_preimp;
                                    $this->GenerarNotaCreditoXmlPdfPeru($headers, $nombre_documento, $data);

                                    $ruta_xml = 'upload/xml/cred_' . $nombre_documento . '.xml';
                                    $ruta_pdf = 'upload/pdf/cred_' . $nombre_documento . '.pdf';

                                    $div_button = '<div class="btn-group" role="group" aria-label="...">
                                        <a href="upload/xml/cred_' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                            <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                        </a>
                                        <a href="upload/pdf/cred_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>
                                    </div>';


                                    $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'S' WHERE ncre_cod_ncre = $ncre_cod_ncre;";
                                    $this->oIfx->QueryT($sql_update);
                                    $this->oIfx->QueryT('COMMIT;');

//                                    $correoMsj = envio_correo_adj_sunat($fact_email_clpv, $ruta_xml, $ruta_pdf, $fact_nom_cliente, $nombre_documento, $serie, $fact_cod_clpv, $tipo_envio, $fact_cod_sucu);
                                    $correoMsj = envio_correo_adj_sunat('franklin.caiza@sisconti.com', $ruta_xml, $ruta_pdf, $fact_nom_cliente, $nombre_documento, $serie, $fact_cod_clpv,  $tipo_envio,'GUIA DE REMISION');

                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => $data_json['sunatResponse']['cdrResponse']['description'],
                                        'result_email' => 'Autorizado (' . $correoMsj . ')'
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }


                            } else {
                                $error = "Codigo: " . $data_json['sunatResponse']['error']['code'] . "<br>";
                                $error .= "Error: " . $data_json['sunatResponse']['error']['message'] . "<br>";
                                throw new Exception($error);
                            }

                            break;
                        default:
                            throw new Exception("Error desconocido en el WebService, Consulte con el administrador");
                    }

                } else {
                    $errorMessage = curl_error($ch);
                    throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
                }
            }else{
                throw new Exception("Documento ya se encuentra Autorizado");
            }


        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }

    function GenerarGuiasRemisionXmlPdfPeru($headers, $nombre_documento, $data)
    {
        //GENERA XML
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/despatch/xml?token=" . $this->empr_token_api);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data_xml = curl_exec($ch);

        $ruta_upload = "upload";
        $ruta = "upload/xml";
        $ruta_pdf = "upload/pdf";

        if (!file_exists($ruta_upload)) {
            mkdir($ruta_upload, 0777);
        }

        if (!file_exists($ruta)) {
            mkdir($ruta, 0777);
        }

        if (!file_exists($ruta_pdf)) {
            mkdir($ruta_pdf, 0777);
        }

        $nombre = "cred_".$nombre_documento . ".xml";
        $ruta_xml = $ruta . '/' . $nombre;
        $archivo = fopen($ruta_xml, "w+");
        fwrite($archivo, utf8_encode($data_xml));
        fclose($archivo);

        //GENERA PDF
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/despatch/pdf?token=" . $this->empr_token_api);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data_pdf = curl_exec($ch);

        $ruta_pdf = 'upload/pdf/cred_' . $nombre_documento . '.pdf';
//        header('Content-Type: application/pdf');
        file_put_contents($ruta_pdf, $data_pdf);

        return true;
    }

}

?>