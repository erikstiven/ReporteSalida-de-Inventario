<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class FacturacionElectronicaDian2023
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
    public $empr_dataico_id = '';
    public $empr_dataico_token = '';

    function __construct($oIfx, $oCon, $idempresa)
    {
        $this->oIfx = $oIfx;
        $this->oCon = $oCon;

        $sql = "SELECT  empr_ruc_empr, empr_nom_empr, empr_dir_empr, empr_mai_empr, 
                    empr_tel_resp, empr_token_api, empr_cod_empr, empr_dataico_id, empr_dataico_token
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
                    $this->empr_dataico_id = trim($oCon->f('empr_dataico_id'));
                    $this->empr_dataico_token = trim($oCon->f('empr_dataico_token'));
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

    }

    /*** FACTURAS ENVIO A LA DIAN*/
    function EnviarFacturaColombia($fact_cod_fact)
    {
        try {

            $sql = "SELECT
	        fac.fact_cod_fact,
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
	        fac.fact_cod_contr,
            fac.fact_nau_fact,
            case 
                when (fact_cod_contr::text is null or trim(fact_cod_contr::text) = '') and fact_cm7_fac is null then 'FCOMERCIAL' 
                when fact_cod_contr = 0  and fact_cm7_fac is not null then 'FISP' 
                when fact_cod_contr = 0  and fact_cm7_fac is  null then 'FISP' 
                when fact_cod_contr > 0  and fact_cm7_fac is not null then 'FISP' 
                when fact_cod_contr > 0  and fact_cm7_fac is  null then 'FISP' 
                ELSE 'FCOMERCIAL'  END fact_com_isp
            FROM
	        saefact fac 
            inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
            WHERE fac.fact_cod_fact = $fact_cod_fact;";

            $fact_aprob_sri = 'N';
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $fact_cod_fact = $this->oIfx->f('fact_cod_fact');
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
                        $fact_cod_contr = $this->oIfx->f("fact_cod_contr");
                        $fact_nau_fact = $this->oIfx->f("fact_nau_fact");

                        $fact_com_isp   = $this->oIfx->f("fact_com_isp");                        


                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                        $fact_iva = floatval(number_format($fact_iva, 2, '.', ''));
                        $fact_con_miva = floatval(number_format($fact_con_miva, 2, '.', ''));
                        $fact_sin_miva = floatval(number_format($fact_sin_miva, 2, '.', ''));
                        $fact_tot_fact = floatval(number_format($fact_tot_fact, 2, '.', ''));
                        $fact_cm4_fact = floatval(number_format($fact_cm4_fact, 2, '.', ''));

                        if ($fact_cm4_fact == null) {
                            $fact_cm4_fact = 0;
                        }

                        $fact_ruc_clie_nit = $this->nit_colombia_9d($fact_ruc_clie,$fact_cod_clpv );
                        $fact_ruc_clie = $fact_ruc_clie_nit?$fact_ruc_clie_nit:$fact_ruc_clie;

                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            if (empty($fact_cod_contr) && $fact_com_isp == 'FISP'){
                throw new Exception("codigo de contrato en la factura no encontrado", 1);
                
            }

            $sql = "select sucu_cod_ciud, sucu_pref_num, sucu_resol_num from saesucu where sucu_cod_empr = $this->empr_cod_empr and sucu_cod_sucu = $fact_cod_sucu ";
            $ciud_cod = consulta_string_func($sql, 'sucu_cod_ciud', $this->oCon, 0);
            $prefijo = consulta_string_func($sql, 'sucu_pref_num', $this->oCon, '');
            $resolucion = consulta_string_func($sql, 'sucu_resol_num', $this->oCon, '');

            $sql = "select ciud_cod_prov, ciud_nom_ciud from saeciud where ciud_cod_ciud = $ciud_cod ";
            $ciud_cod_provc = consulta_string_func($sql, 'ciud_cod_prov', $this->oCon, 0);
            $ciud_nom = consulta_string_func($sql, 'ciud_nom_ciud', $this->oCon, '');

            $sql = "select prov_cod_prov, prov_des_prov from saeprov where prov_cod_prov = $ciud_cod_provc; ";
            $depar_nom = consulta_string_func($sql, 'prov_des_prov', $this->oCon, '');


            if ( $fact_com_isp == 'FISP'){
                $sql = "SELECT c.id, c.nombre, c.apellido, c.codigo FROM isp.contrato_clpv c WHERE C.id = $fact_cod_contr;";
                $num_contrato = consulta_string_func($sql, 'codigo', $this->oCon, '');
                $nombre = consulta_string_func($sql, 'nombre', $this->oCon, '.');
                $apellido = consulta_string_func($sql, 'apellido', $this->oCon, '.');

                $contr_ident = ' - IdContrato: ' . $num_contrato;
            }else{
                $sql = "SELECT c.clpv_cod_clpv as id, c.clpv_nom_clpv as nombre, c.clpv_ape_clpv as apellido, c.clpv_ruc_clpv as codigo FROM saeclpv c WHERE c.clpv_cod_clpv = $fact_cod_clpv;";
                $num_contrato = consulta_string_func($sql, 'codigo', $this->oCon, '');
                $nombre = consulta_string_func($sql, 'nombre', $this->oCon, '.');
                $apellido = consulta_string_func($sql, 'apellido', $this->oCon, '.');

                $contr_ident = ' - identificacion: ' . $num_contrato;

            }

            if (!$fact_email_clpv) {
                $sql = "select emai_cod_emai, emai_ema_emai from saeemai where emai_cod_clpv = $fact_cod_clpv;";
                $fact_email_clpv = consulta_string_func($sql, 'emai_ema_emai', $this->oCon, '');
            }

            if (!$fact_tlf_cliente) {
                $sql = "select tlcp_cod_tlcp, tlcp_tlf_tlcp from saetlcp where tlcp_cod_clpv = $fact_cod_clpv;";
                $fact_tlf_cliente = consulta_string_func($sql, 'tlcp_tlf_tlcp', $this->oCon, '');
            }


            $tipo_ruc = '';
            if ($tipo_doc == '01') {                  // RUC
                $tipoIdentificacionComprador = '04';
                $tipo_ruc = 'PERSONA_JURIDICA';
            } elseif ($tipo_doc == '02') {            // CEDULA
                $tipoIdentificacionComprador = '05';
                $tipo_ruc = 'PERSONA_NATURAL';
            } elseif ($tipo_doc == '03') {            // PASAPORTE
                $tipoIdentificacionComprador = '06';
                $tipo_ruc = 'PERSONA_NATURAL';
            } elseif ($tipo_doc == '04') {            // EXTRANJERIA
                $tipoIdentificacionComprador = '08';
                $tipo_ruc = 'PERSONA_JURIDICA';
            }elseif ($tipo_doc == '07') {            // CONSUMIDOR FINAL
                $tipoIdentificacionComprador = '07';
                $tipo_ruc = 'PERSONA_NATURAL';
            }

            if (empty($tipo_ruc)) {
                $tipo_ruc = 'PERSONA_NATURAL';
            }

            $dataico_id = $this->empr_dataico_id;
            $token = $this->empr_dataico_token;

            if (empty($dataico_id)) {
                throw new Exception("No se encuentra configurado dataico_id");
            }

            if (empty($token)) {
                throw new Exception("No se encuentra configurado dataico_token");
            }

            $regimen = 'ORDINARIO';

            $departamento = $depar_nom;
            $ciudad = $ciud_nom;
            $fact_num_preimp = intval($fact_num_preimp);
            $fact_fech_fact = date("d/m/Y", strtotime($fact_fech_fact));

            $fecha_hora = $fact_fech_fact . ' ' . $fact_hor_ini;

            if (!$fact_email_clpv) {
                throw new Exception("No se encontro email");
            }

            if (!$fact_tlf_cliente) {
                throw new Exception("No se encontro el telefono");
            }

            if (!$fact_ruc_clie) {
                throw new Exception("No se encontro la identificacion");
            }

            $ambiente = ambienteEmisionSri($this->oCon, $this->empr_cod_empr, $fact_cod_sucu, 1);
            $tipo_ambiente = "PRUEBAS";
            if ($ambiente == 2) {
                $tipo_ambiente = "PRODUCCION";
            }

            $data = [
                "actions" => [
                    "send_dian" => true,
                    "send_email" => true,
                ],
                "invoice" => [
                    "env" => "$tipo_ambiente",
                    "dataico_account_id" => "$dataico_id",
                    "issue_date" => "$fact_fech_fact",
                    "payment_date" => "$fecha_hora",
                    "order_reference" => "$num_contrato",
                    "number" => "$fact_num_preimp",
                    "invoice_type_code" => "FACTURA_VENTA",
                    "payment_means" => "CASH",
                    "payment_means_type" => "DEBITO",
                    "notes" => ["string"],
                    "numbering" => [
                        "prefix" => "$fact_nse_fact",
                        "resolution_number" => "$fact_nau_fact",
                    ],
                    "customer" => [
                        "email" => "$fact_email_clpv",
                        "phone" => "$fact_tlf_cliente",
                        "party_identification" => "$fact_ruc_clie",
                        "party_type" => $tipo_ruc,
                        "tax_level_code" => "COMUN",
                        "regimen" => "$regimen",
                        "department" => "$departamento",
                        "city" => "$ciudad",
                        "address_line" => "$fact_dir_clie",
                        "country_code" => "CO",
                        "company_name" => "$fact_nom_cliente",
                        "first_name" => "$nombre",
                        "family_name" => "$apellido",
                    ],
                ]
            ];

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
                        if ($this->pcon_seg_mone == $fact_cod_mone) {
                            $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                        }
                        $fxfp_val_fxfp = $fxfp_val_fxfp - $valor_detra_rest;

                        if ($tipo == 'EFE') {
                            $tipo_fpag = 'CASH';
                            $tipo_2 = 'DEBITO';
                        } else if ($tipo == 'CRE') {
                            $tipo_fpag = 'CREDITO';
                            $tipo_2 = 'CREDIT_ACH';
                            $fxfp_fec_fin = $fact_fech_venc;

                            $data["invoice"]["issue_date"] = $fact_fech_fact;
                            $data["invoice"]["payment_date"] = $fxfp_fec_fin;

                           /*  $data['invoice'] = [
                                "issue_date" => $fact_fech_fact,
                                "payment_date" => $fxfp_fec_fin
                            ]; */
                        } else {
                            $tipo_fpag = 'CASH';
                            $tipo_2 = 'DEBITO';
                        }

                        $data["invoice"]["payment_means"] = $tipo_2;
                        $data["invoice"]["payment_means_type"] = $tipo_fpag;
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            $sql = "SELECT 
                        dfac_cant_dfac, 
                        dfac_cod_prod, 
                        dfac_det_dfac, 
                        dfac_mont_total, 
                        dfac_precio_dfac, 
                        dfac_por_iva,
                        (dfac_des1_dfac+dfac_des2_dfac+dfac_des3_dfac+dfac_des4_dfac) as dfac_des_dfac  
                    FROM saedfac 
                    WHERE dfac_cod_fact = $fact_cod_fact";

            $bandera_descuento = 0;
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $j = 0;
                    do {
                        $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                        $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                        $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                        $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                        $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');
                        $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                        $dfac_des_dfac = $this->oIfx->f('dfac_des_dfac');

                        $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                        $precio_uni = $dfac_mont_total * $porcentaje_iva;
                        $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;

                        $valor_descuento = ($dfac_precio_dfac) * ($dfac_des_dfac/100);

                        if (empty($dfac_det_dfac)) {
                            $dfac_det_dfac = 'Sin detalle';
                        }
                        $data_indi=[];
                        $data_indi =
                            [
                                "sku" => $dfac_cod_prod,
                                "quantity" => floatval($dfac_cant_dfac),
                                "description" => $dfac_det_dfac,
                                "price" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "taxes" => [
                                    [
                                        "tax_category" => "IVA",
                                        "tax_rate" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                    ]
                                ],
                            ];
                            if($dfac_des_dfac>0){
                                $bandera_descuento++;
                                // $data_indi['original_price'] = floatval(number_format($dfac_precio_dfac, 2, '.', ''));
                                $data_indi['discount_rate'] = floatval(number_format($dfac_des_dfac, 2, '.', ''));
                               
                            }
                            $data['invoice']['items'][$j++] = $data_indi;
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();


            if($bandera_descuento>0){
                $fact_cm1_fact = empty($fact_cm1_fact)?"DESCUENTO A LA FACTURA":$fact_cm1_fact;
                $data['invoice']['charges'] = [
                    [
                        "reason"=>strtoupper($fact_cm1_fact),
                        "base_amount"=>floatval(number_format($fact_cm4_fact, 2, '.', '')),
                        "discount"=>true
                    ]

                ];
            }


            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                /**
                 * EMPIEZA ENVIO
                 */
                $data = json_encode($data);

                $headers = array(
                    "Content-Type:application/json",
                    "auth-token:$token"
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, "https://api.dataico.com/dataico_api/v2/invoices");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);

                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $ruta = "upload";
                    $ruta_respuesta = "upload/respuesta";
                    $ruta_xml = "upload/xml";
                    $ruta_pdf = "upload/pdf";

                    if (!file_exists($ruta)) {
                        mkdir($ruta, 0777);
                    }

                    if (!file_exists($ruta_respuesta)) {
                        mkdir($ruta_respuesta, 0777);
                    }

                    if (!file_exists($ruta_xml)) {
                        mkdir($ruta_xml, 0777);
                    }

                    if (!file_exists($ruta_pdf)) {
                        mkdir($ruta_pdf, 0777);
                    }

                    /**
                     * JSON
                     */
                    $nombre = "fac_" . $fact_cod_fact . ".json";
                    $ruta_json_save = $ruta_respuesta . '/' . $nombre;
                    $archivo = fopen($ruta_json_save, "w+");
                    fwrite($archivo, $respuesta);
                    fclose($archivo);

                    /** @var  $data_json */
                    $data_json = json_decode($respuesta, true);
                    $data_json = (object)$data_json;

                    switch ($http_code) {
                        case 200:
                            $status = trim($data_json->dian_status);
                            $num_factura = trim($data_json->number);
                            $cufe = trim($data_json->cufe);
                            $uid = trim($data_json->uuid);
                            $qrcod = trim($data_json->qrcode);
                            $prefijo = trim($data_json->numbering->prefix);
                            $xml_url = trim($data_json->xml_url);
                            $pdf_url = trim($data_json->pdf_url);
                            $email_status = trim($data_json->email_status);

                            /**
                             * PDF
                             */
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_URL, $pdf_url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $data_pdf = curl_exec($ch);
                            $ruta_pdf_save = $ruta_pdf . '/fac_' . $fact_cod_fact . '.pdf';
                            file_put_contents($ruta_pdf_save, $data_pdf);

                            /**
                             * XML
                             */
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_URL, $xml_url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $data_xml = curl_exec($ch);
                            $ruta_xml_save = $ruta_xml . '/fac_' . $fact_cod_fact . '.xml';
                            file_put_contents($ruta_xml_save, $data_xml);


                            $nombre_documento = "fac_$fact_cod_fact";

                            $div_button = '<div class="btn-group" role="group">
                                        <a href="upload/xml/fac_' . $fact_cod_fact . '.xml" download="' . $nombre_documento . '.xml">
                                            <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                        </a>
                                        <a href="upload/pdf/fac_' . $fact_cod_fact . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>
                                    </div>';


                            if ($status == 'DIAN_ACEPTADO') {
                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saefact SET fact_aprob_sri = 'S', fact_auto_sri = '$uid'
                                                   WHERE fact_cod_fact = $fact_cod_fact ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "DIAN_ACEPTADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else if ($status == 'DIAN_NO_ENVIADO') {

                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saefact SET fact_aprob_sri = 'N', fact_auto_sri = '$uid'
                                                   WHERE fact_cod_fact = $fact_cod_fact ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "DIAN_NO_ENVIADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else {
                                $error_array = trim($data_json->errors->error);
                                $error = 'Error ' . $status . ' ' . $error_array[3] . ' - IdContrato: ' . $num_contrato;
                                throw new Exception($error);
                            }

                            break;
                        case 201:
                            $status = trim($data_json->dian_status);
                            $num_factura = trim($data_json->number);
                            $cufe = trim($data_json->cufe);
                            $uid = trim($data_json->uuid);
                            $qrcod = trim($data_json->qrcode);
                            $prefijo = trim($data_json->numbering->prefix);
                            $xml_url = trim($data_json->xml_url);
                            $pdf_url = trim($data_json->pdf_url);
                            $email_status = trim($data_json->email_status);

                            /**
                             * PDF
                             */
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_URL, $pdf_url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $data_pdf = curl_exec($ch);
                            $ruta_pdf_save = $ruta_pdf . '/fac_' . $fact_cod_fact . '.pdf';
                            file_put_contents($ruta_pdf_save, $data_pdf);

                            /**
                             * XML
                             */
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_URL, $xml_url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $data_xml = curl_exec($ch);
                            $ruta_xml_save = $ruta_xml . '/fac_' . $fact_cod_fact . '.xml';
                            file_put_contents($ruta_xml_save, $data_xml);


                            $nombre_documento = "fac_$fact_cod_fact";

                            $div_button = '<div class="btn-group" role="group">
                                        <a href="upload/xml/fac_' . $fact_cod_fact . '.xml" download="' . $nombre_documento . '.xml">
                                            <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                        </a>
                                        <a href="upload/pdf/fac_' . $fact_cod_fact . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>
                                    </div>';


                            if ($status == 'DIAN_ACEPTADO') {
                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saefact SET fact_aprob_sri = 'S', fact_auto_sri = '$uid'
                                                   WHERE fact_cod_fact = $fact_cod_fact ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "DIAN_ACEPTADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else if ($status == 'DIAN_NO_ENVIADO') {

                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saefact SET fact_aprob_sri = 'N', fact_auto_sri = '$uid', fact_cod_hash = '$cufe'
                                                   WHERE fact_cod_fact = $fact_cod_fact ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "DIAN_NO_ENVIADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else {
                                $error_array = trim($data_json->errors->error);
                                $error = 'Error ' . $status . ' ' . $error_array[3] . ' - IdContrato: ' . $num_contrato;
                                throw new Exception($error);
                            }

                            break;
                        case 401:
                            $error_in = $data_json->errors;
                            if ($error_in) {

                                $mensaje = "No se pudo enviar la factura";
                                if (isset($error_in[0]['error'])) {
                                    $mensaje = $error_in[0]['error'] . ":<br>";
                                    if (isset($error_in[0]['path'])) {
                                        foreach ($error_in[0]['path'] as $item) {
                                            $mensaje .= "$item, <br>";
                                        }

                                    }
                                }
                                throw new Exception($mensaje);
                            }
                            break;
                        case 500:
                            $error_in = $data_json->errors;
                            if ($error_in) {

                                $mensaje = "No se pudo enviar la factura";
                                if (isset($error_in[0]['error'])) {
                                    $mensaje = $error_in[0]['error'] . ":<br>";
                                    if (isset($error_in[0]['path'])) {
                                        foreach ($error_in[0]['path'] as $item) {
                                            $mensaje .= "$item, <br>";
                                        }

                                    }
                                }
                                throw new Exception($mensaje);
                            }
                            break;
                        default:
                            throw new Exception("Error desconocido en el WebService, Consulte con el administrador");
                    }

                } else {
                    $errorMessage = curl_error($ch);
                    throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
                }

            } else {
                throw new Exception("Documento ya se encuentra Autorizado");
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }

    function EnviarNCColombia($ncre_cod_ncre)
    {
        try {

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
	        fac.ncre_cod_sucu as fact_cod_sucu,
	        cli.clv_con_clpv,
	        fac.ncre_aprob_sri as fact_aprob_sri,
	        fac.ncre_cod_fact as fact_cod_ndb, 
			fac.ncre_cod_aux as fact_aux_preimp,
			fac.ncre_fech_fact as fact_fec_emis_aux,
            fac.ncre_cod_mone as fact_cod_mone,
            fac.ncre_val_tcam as fact_val_tcam
            FROM
	        saencre fac inner join saeclpv cli on fac.ncre_cod_clpv = cli.clpv_cod_clpv
            WHERE fac.ncre_cod_ncre = $ncre_cod_ncre;";

            $fact_aprob_sri = 'N';
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $fact_cod_fact = $this->oIfx->f('fact_cod_fact');
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
                        $fact_cod_contr = $this->oIfx->f("fact_cod_contr");
                        $fact_nau_fact = $this->oIfx->f("fact_nau_fact");
                        $fact_cod_ndb = $this->oIfx->f("fact_cod_ndb");
                        $motivo_ncre = $this->oIfx->f('motivo_ncre');

                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                        $fact_iva = floatval(number_format($fact_iva, 2, '.', ''));
                        $fact_con_miva = floatval(number_format($fact_con_miva, 2, '.', ''));
                        $fact_sin_miva = floatval(number_format($fact_sin_miva, 2, '.', ''));
                        $fact_tot_fact = floatval(number_format($fact_tot_fact, 2, '.', ''));
                        $fact_cm4_fact = floatval(number_format($fact_cm4_fact, 2, '.', ''));

                        if ($fact_cm4_fact == null) {
                            $fact_cm4_fact = 0;
                        }

                        $fact_ruc_clie_nit = $this->nit_colombia_9d($fact_ruc_clie,$fact_cod_clpv );
                        $fact_ruc_clie = $fact_ruc_clie_nit?$fact_ruc_clie_nit:$fact_ruc_clie;


                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            if (empty($motivo_ncre)) {
                throw new Exception("Sin motivo configurado en la nota de credito");
            }

            if ($fact_cod_ndb) {

                $sql = "SELECT
                fac.fact_nse_fact, fac.fact_num_preimp, fac.fact_auto_sri
                FROM
                saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
                WHERE fac.fact_cod_fact = $fact_cod_ndb;";

                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        do {
                            $fact_num_preimp_afect = (int) $this->oIfx->f('fact_num_preimp');
                            $fact_nse_fact_afect = substr($this->oIfx->f('fact_nse_fact'), 3, 9);
                            $fact_auto_sri = $this->oIfx->f('fact_auto_sri');
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                $numDocfectado = "$fact_nse_fact_afect-$fact_num_preimp_afect";
            } else {
                throw new Exception("Sin factura para realizar NC");
            }

            $sql = "select sucu_cod_ciud, sucu_pref_num, sucu_resol_num from saesucu where sucu_cod_empr = $this->empr_cod_empr and sucu_cod_sucu = $fact_cod_sucu ";
            $ciud_cod = consulta_string_func($sql, 'sucu_cod_ciud', $this->oCon, 0);
            $prefijo = consulta_string_func($sql, 'sucu_pref_num', $this->oCon, '');
            $resolucion = consulta_string_func($sql, 'sucu_resol_num', $this->oCon, '');

            $sql = "select ciud_cod_prov, ciud_nom_ciud from saeciud where ciud_cod_ciud = $ciud_cod ";
            $ciud_cod_provc = consulta_string_func($sql, 'ciud_cod_prov', $this->oCon, 0);
            $ciud_nom = consulta_string_func($sql, 'ciud_nom_ciud', $this->oCon, '');

            $sql = "select prov_cod_prov, prov_des_prov from saeprov where prov_cod_prov = $ciud_cod_provc; ";
            $depar_nom = consulta_string_func($sql, 'prov_des_prov', $this->oCon, '');

            if (!$fact_email_clpv) {
                $sql = "select emai_cod_emai, emai_ema_emai from saeemai where emai_cod_clpv = $fact_cod_clpv;";
                $fact_email_clpv = consulta_string_func($sql, 'emai_ema_emai', $this->oCon, '');
            }

            if (!$fact_tlf_cliente) {
                $sql = "select tlcp_cod_tlcp, tlcp_tlf_tlcp from saetlcp where tlcp_cod_clpv = $fact_cod_clpv;";
                $fact_tlf_cliente = consulta_string_func($sql, 'tlcp_tlf_tlcp', $this->oCon, '');
            }


            $tipo_ruc = '';
            if ($tipo_doc == '01') {                  // RUC
                $tipoIdentificacionComprador = '04';
                $tipo_ruc = 'PERSONA_JURIDICA';
            } elseif ($tipo_doc == '02') {            // CEDULA
                $tipoIdentificacionComprador = '05';
                $tipo_ruc = 'PERSONA_NATURAL';
            } elseif ($tipo_doc == '03') {            // PASAPORTE
                $tipoIdentificacionComprador = '06';
                $tipo_ruc = 'PERSONA_NATURAL';
            } elseif ($tipo_doc == '07') {            // CONSUMIDOR FINAL
                $tipoIdentificacionComprador = '07';
                $tipo_ruc = 'PERSONA_NATURAL';
            } elseif ($tipo_doc == '04') {            // EXTRANJERIA
                $tipoIdentificacionComprador = '08';
                $tipo_ruc = 'PERSONA_JURIDICA';
            }

            if (empty($tipo_ruc)) {
                $tipo_ruc = 'PERSONA_NATURAL';
            }

            $dataico_id = $this->empr_dataico_id;
            $token = $this->empr_dataico_token;

            if (empty($dataico_id)) {
                throw new Exception("No se encuentra configurado dataico_id");
            }

            if (empty($token)) {
                throw new Exception("No se encuentra configurado dataico_token");
            }

            $regimen = 'ORDINARIO';

            $departamento = $depar_nom;
            $ciudad = $ciud_nom;
            $fact_num_preimp = intval($fact_num_preimp);
            $fact_fech_fact = date("d/m/Y", strtotime($fact_fech_fact));

            $fecha_hora = $fact_fech_fact;

            if (!$fact_email_clpv) {
                throw new Exception("No se encontro email");
            }

            if (!$fact_tlf_cliente) {
                throw new Exception("No se encontro el telefono");
            }

            if (!$fact_ruc_clie) {
                throw new Exception("No se encontro la identificacion");
            }

            $ambiente = ambienteEmisionSri($this->oCon, $this->empr_cod_empr, $fact_cod_sucu, 1);
            $tipo_ambiente = "PRUEBAS";
            if ($ambiente == 2) {
                $tipo_ambiente = "PRODUCCION";
            }

            $data = [
                "actions" => [
                    "send_dian" => true,
                    "send_email" => true,
                ],
                "credit_note" => [
                    "env" => "$tipo_ambiente",
                    "dataico_account_id" => "$dataico_id",
                    "invoice_id" => "$fact_auto_sri",
                    "issue_date" => "$fact_fech_fact",
                    "payment_date" => "$fecha_hora",
                    "reason" => "$motivo_ncre",
                    "number" => "$fact_num_preimp",
                    "numbering" => [
                        "prefix" => "$fact_nse_fact",
                        "flexible" => false,
                    ],
                ]
            ];

            $sql = "SELECT 
                    dncr_cant_dfac as dfac_cant_dfac,
                    dncr_cod_prod as dfac_cod_prod,
                    dncr_det_dncr as dfac_det_dfac,
                    dncr_mont_total as dfac_mont_total,
                    dncr_por_iva as dfac_por_iva,
                    dncr_exc_iva as dfac_exc_iva
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

                        $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                        $precio_uni = $dfac_mont_total * $porcentaje_iva;
                        $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;

                        if (empty($dfac_det_dfac)) {
                            $dfac_det_dfac = 'Sin detalle';
                        }

                        $data['credit_note']['items'][$j++] =
                            [
                                "sku" => $dfac_cod_prod,
                                "quantity" => floatval($dfac_cant_dfac),
                                "description" => $dfac_det_dfac,
                                "price" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "taxes" => [
                                    [
                                        "tax_category" => "IVA",
                                        "tax_rate" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                    ]
                                ],
                            ];
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                /**
                 * EMPIEZA ENVIO
                 */
                $data = json_encode($data);

                $headers = array(
                    "Content-Type:application/json",
                    "auth-token:$token"
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, "https://api.dataico.com/direct/dataico_api/v2/credit_notes");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);

                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $ruta = "upload";
                    $ruta_respuesta = "upload/respuesta";
                    $ruta_xml = "upload/xml";
                    $ruta_pdf = "upload/pdf";

                    if (!file_exists($ruta)) {
                        mkdir($ruta, 0777);
                    }

                    if (!file_exists($ruta_respuesta)) {
                        mkdir($ruta_respuesta, 0777);
                    }

                    if (!file_exists($ruta_xml)) {
                        mkdir($ruta_xml, 0777);
                    }

                    if (!file_exists($ruta_pdf)) {
                        mkdir($ruta_pdf, 0777);
                    }

                    /**
                     * JSON
                     */
                    $nombre = "nc_" . $fact_cod_fact . ".json";
                    $ruta_json_save = $ruta_respuesta . '/' . $nombre;
                    $archivo = fopen($ruta_json_save, "w+");
                    fwrite($archivo, $respuesta);
                    fclose($archivo);

                    /** @var  $data_json */
                    $data_json = json_decode($respuesta, true);
                    $data_json = (object)$data_json;

                    switch ($http_code) {
                        case 200:
                            $status = trim($data_json->dian_status);
                            $num_factura = trim($data_json->number);
                            $cufe = trim($data_json->cufe);
                            $uid = trim($data_json->uuid);
                            $qrcod = trim($data_json->qrcode);
                            $prefijo = trim($data_json->numbering->prefix);
                            $xml_url = trim($data_json->xml_url);
                            $pdf_url = trim($data_json->pdf_url);
                            $email_status = trim($data_json->email_status);

                            /**
                             * PDF
                             */
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_URL, $pdf_url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $data_pdf = curl_exec($ch);
                            $ruta_pdf_save = $ruta_pdf . '/nc_' . $fact_cod_fact . '.pdf';
                            file_put_contents($ruta_pdf_save, $data_pdf);

                            /**
                             * XML
                             */
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_URL, $xml_url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $data_xml = curl_exec($ch);
                            $ruta_xml_save = $ruta_xml . '/nc_' . $ncre_cod_ncre . '.xml';
                            file_put_contents($ruta_xml_save, $data_xml);


                            $nombre_documento = "nc_$ncre_cod_ncre";

                            $div_button = '<div class="btn-group" role="group">
                                        <a href="upload/xml/fac_' . $ncre_cod_ncre . '.xml" download="' . $nombre_documento . '.xml">
                                            <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                        </a>
                                        <a href="upload/pdf/fac_' . $ncre_cod_ncre . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>
                                    </div>';


                            if ($status == 'DIAN_ACEPTADO') {
                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'S'
                                                   WHERE ncre_cod_ncre = $ncre_cod_ncre ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "DIAN_ACEPTADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else if ($status == 'DIAN_NO_ENVIADO') {

                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'N'
                                                   WHERE ncre_cod_ncre = $ncre_cod_ncre ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "DIAN_NO_ENVIADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else {
                                $error_array = trim($data_json->errors->error);
                                $error = 'Error ' . $status . ' ' . $error_array[3] . ' - IdContrato: ' . $num_contrato;
                                throw new Exception($error);
                            }

                            break;
                        case 201:
                            $status = trim($data_json->dian_status);
                            $num_factura = trim($data_json->number);
                            $cufe = trim($data_json->cufe);
                            $uid = trim($data_json->uuid);
                            $qrcod = trim($data_json->qrcode);
                            $prefijo = trim($data_json->numbering->prefix);
                            $xml_url = trim($data_json->xml_url);
                            $pdf_url = trim($data_json->pdf_url);
                            $email_status = trim($data_json->email_status);

                            /**
                             * PDF
                             */
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_URL, $pdf_url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $data_pdf = curl_exec($ch);
                            $ruta_pdf_save = $ruta_pdf . '/nc_' . $ncre_cod_ncre . '.pdf';
                            file_put_contents($ruta_pdf_save, $data_pdf);

                            /**
                             * XML
                             */
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_URL, $xml_url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $data_xml = curl_exec($ch);
                            $ruta_xml_save = $ruta_xml . '/nc_' . $ncre_cod_ncre . '.xml';
                            file_put_contents($ruta_xml_save, $data_xml);


                            $nombre_documento = "nc_$ncre_cod_ncre";

                            $div_button = '<div class="btn-group" role="group">
                                        <a href="upload/xml/nc_' . $ncre_cod_ncre . '.xml" download="' . $nombre_documento . '.xml">
                                            <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                        </a>
                                        <a href="upload/pdf/nc_' . $ncre_cod_ncre . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>
                                    </div>';


                            if ($status == 'DIAN_ACEPTADO') {
                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'S'
                                                   WHERE ncre_cod_ncre = $ncre_cod_ncre ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "DIAN_ACEPTADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else if ($status == 'DIAN_NO_ENVIADO') {

                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'N'
                                                   WHERE ncre_cod_ncre = $ncre_cod_ncre ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "DIAN_NO_ENVIADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else {
                                $error_array = trim($data_json->errors->error);
                                $error = 'Error ' . $status . ' ' . $error_array[3] . ' - IdContrato: ' . $num_contrato;
                                throw new Exception($error);
                            }

                            break;
                        case 401:
                            $error_in = $data_json->errors;
                            if ($error_in) {

                                $mensaje = "No se pudo enviar la factura";
                                if (isset($error_in[0]['error'])) {
                                    $mensaje = $error_in[0]['error'] . ":<br>";
                                    if (isset($error_in[0]['path'])) {
                                        foreach ($error_in[0]['path'] as $item) {
                                            $mensaje .= "$item, <br>";
                                        }

                                    }
                                }
                                throw new Exception($mensaje);
                            }
                            break;
                        case 500:
                            $error_in = $data_json->errors;
                            if ($error_in) {

                                $mensaje = "No se pudo enviar la factura";
                                if (isset($error_in[0]['error'])) {
                                    $mensaje = $error_in[0]['error'] . ":<br>";
                                    if (isset($error_in[0]['path'])) {
                                        foreach ($error_in[0]['path'] as $item) {
                                            $mensaje .= "$item, <br>";
                                        }

                                    }
                                }
                                throw new Exception($mensaje);
                            }
                            break;
                        default:
                            throw new Exception("Error desconocido en el WebService, Consulte con el administrador");
                    }

                } else {
                    $errorMessage = curl_error($ch);
                    throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
                }

            } else {
                throw new Exception("Documento ya se encuentra Autorizado");
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }

    function nit_colombia_9d($identificaion='',$id_clpv=0){
        $result_nit = $identificaion;
        $sql = "SELECT
                    cl.clpv_ruc_clpv,
                    cc.ruc_clpv
                FROM
                    isp.contrato_clpv cc
                    LEFT JOIN saeclpv cl ON cc.id_clpv = cl.clpv_cod_clpv
                    LEFT JOIN saevend ve ON cc.vendedor = ve.vend_cod_vend
                    LEFT JOIN saeempr em ON cc.id_empresa = empr_cod_empr
                    LEFT JOIN saepais pa ON pa.pais_cod_pais = em.empr_cod_pais
                    LEFT JOIN comercial.tipo_iden_clpv ticlpv ON cl.clv_con_clpv = ticlpv.tipo
                    LEFT JOIN comercial.tipo_iden_clpv_pais ticlpa ON ( pa.pais_codigo_inter = ticlpa.pais_codigo_inter OR pa.pais_cod_pais = ticlpa.pais_cod_pais ) 
                    AND ticlpv.id_iden_clpv :: TEXT = ticlpa.id_iden_clpv :: TEXT 
                    WHERE
                    (ticlpv.identificacion like '%NIT%' or ticlpa.identificacion like '%NIT%')
                    and pais_cod_inte = 'CO'
                    and cc.ruc_clpv LIKE'%$identificaion%' 
                    and cc.id_clpv = '$id_clpv'
                    limit 1;
                    ";
        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {
                do {
                    $clpv_ruc_clpv  = substr($this->oIfx->f('clpv_ruc_clpv'),0,9);
                    $ruc_clpv       = substr($this->oIfx->f('ruc_clpv'),0,9);
                    $result_nit     = $ruc_clpv;

                } while ($this->oIfx->SiguienteRegistro());
            }
        }
        $this->oIfx->Free();

        return $result_nit;
    }

}

?>