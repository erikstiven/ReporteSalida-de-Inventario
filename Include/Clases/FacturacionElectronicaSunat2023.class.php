<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class FacturacionElectronicaSunat2023
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
    public $prov_des_prov = '';
    public $ciud_nom_ciud = '';
    public $parr_des_parr = '';
    public $empr_cpo_empr = '';
    public $pcon_mon_base = '';
    public $pcon_seg_mone = '';
    public $empr_ws_sri_url = '';
    public $empr_nomcome_empr = '';
    public $cant_des_cant = '';

    function __construct($oIfx, $oCon, $idempresa)
    {
        $this->oIfx = $oIfx;
        $this->oCon = $oCon;

        $sql = "SELECT  empr_ruc_empr, empr_nom_empr, empr_dir_empr, empr_mai_empr, 
                    empr_tel_resp, empr_token_api, empr_cod_empr, empr_cod_prov,
                    empr_cod_ciud, empr_cod_parr, empr_cpo_empr, empr_ws_sri_url,empr_nomcome_empr,empr_cod_cant
                    FROM saeempr 
                    WHERE empr_cod_empr = $idempresa";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $this->empr_cod_empr = $oCon->f('empr_cod_empr');
                    $this->empr_ruc_empr = $oCon->f('empr_ruc_empr');
                    $this->empr_nom_empr = $oCon->f('empr_nom_empr');
                    $this->empr_nom_empr = $oCon->f('empr_nom_empr');
                    $this->empr_dir_empr = $oCon->f('empr_dir_empr');
                    $this->empr_mai_empr = $oCon->f('empr_mai_empr');
                    $this->empr_tel_resp = $oCon->f('empr_tel_resp');
                    $this->empr_token_api = $oCon->f('empr_token_api');
                    $this->empr_nomcome_empr = $oCon->f('empr_nomcome_empr');
                    $empr_cod_prov = $oCon->f('empr_cod_prov');
                    $empr_cod_ciud = $oCon->f('empr_cod_ciud');
                    $empr_cod_parr = $oCon->f('empr_cod_parr');
                    $empr_cod_cant = $oCon->f('empr_cod_cant');
                    $this->empr_cpo_empr = $oCon->f('empr_cpo_empr');
                    $this->empr_ws_sri_url = $oCon->f('empr_ws_sri_url');
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

        if (!empty($empr_cod_prov)) {
            $sql = "SELECT prov_des_prov from saeprov where prov_cod_prov = $empr_cod_prov ";
            $this->prov_des_prov = consulta_string($sql, 'prov_des_prov', $oCon, 0);
        } else {
            $this->prov_des_prov = "";
        }

        if (!empty($empr_cod_ciud)) {
            $sql = "SELECT ciud_nom_ciud from saeciud where ciud_cod_ciud = $empr_cod_ciud ";
            $this->ciud_nom_ciud = consulta_string($sql, 'ciud_nom_ciud', $oCon, 0);
        } else {
            $this->ciud_nom_ciud = "";
        }

        if (!empty($empr_cod_cant)) {
            $sql = "SELECT cant_des_cant from saecant where cant_cod_cant = $empr_cod_cant ";
            $this->cant_des_cant = consulta_string($sql, 'cant_des_cant', $oCon, 0);
        } else {
            $this->cant_des_cant = "";
        }

        if (!empty($empr_cod_parr)) {
            $sql = "SELECT parr_des_parr from saeparr where parr_cod_parr = $empr_cod_parr ";
            $this->parr_des_parr = consulta_string($sql, 'parr_des_parr', $oCon, 0);
        } else {
            $this->parr_des_parr = "";
        }
    }

    /**
     * FACTURAS Y BOLETAS ENVIO A LA SUNAT
     */
    function EnviarFacturaBoletaPeru($fact_cod_fact)
    {
        try {
            session_start();
            $id_usuario = $_SESSION['U_ID'];
            $idempresa = $_SESSION['U_EMPRESA'];

            //CONTROL FORMATOS PERSONALIZADOS
            $sql = "select ftrn_ubi_web from saeftrn  where ftrn_cod_empr=$idempresa and ftrn_des_ftrn = 'FACTURA' and ftrn_cod_modu=7 and (ftrn_ubi_web is not null or ftrn_ubi_web != '')";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $ubi =  $this->oIfx->f('ftrn_ubi_web');
                    if (!empty($ubi)) {
                        include_once('../../' . $ubi . '');
                        
                    }
                    else{
                        //FORMATO ESTANDAR PERU
                        include_once('../../Include/Formatos/comercial/factura_peru.php');
                    }
                }else{
                    include_once('../../Include/Formatos/comercial/factura_peru.php');
                }
            }
            $this->oIfx->Free();

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
            fac.fact_val_tcam,
            fac.fact_cod_detra,
            fac.fact_cm2_fact
            FROM
	        saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
            WHERE fac.fact_cod_fact = $fact_cod_fact;";

            $fact_aprob_sri = 'N';
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $fact_nse_fact  = $this->oIfx->f('fact_nse_fact');
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
                        $fact_val_tcam = $this->oIfx->f('fact_val_tcam');
                        $fact_cod_detra = $this->oIfx->f('fact_cod_detra');
                        $fact_cm2_fact = $this->oIfx->f('fact_cm2_fact');
                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                        if ($fact_cod_detra > 0) {
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

                            if (empty($tret_cod_imp) || empty($tret_cod_banc)) {
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

                        if ($this->pcon_seg_mone == $fact_cod_mone) {
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
                        } else if (intval($tipo_doc) == 5) {
                            $tipo_docu = '4';
                            $tipo_envio = '03';
                        } else if (intval($tipo_doc) == 4) {
                            $tipo_docu = '0';
                            $tipo_envio = '03';
                        } else {
                            $tipo_docu = '03';
                        }

                       /*  echo intval($tipo_doc).PHP_EOL;
                        echo $tipo_docu;exit; */
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            $sql = "SELECT mone_sgl_mone, mone_des_mone from saemone where mone_cod_mone = $fact_cod_mone ";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $mone_sgl_mone = $this->oIfx->f('mone_sgl_mone');
                        $mone_des_mone = $this->oIfx->f('mone_des_mone');
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, $mone_des_mone));

                $fact_hor_ini = substr($fact_hor_ini, -8);
                $fact_hor_fin = substr($fact_hor_fin, -8);
                $fact_fech_fact = $fact_fech_fact . "T"  . "12:00:00-00:00";
                $fact_fech_venc = $fact_fech_venc . "T" . "12:00:00-00:00";
                $serie = $fact_nse_fact . '-' . $fact_num_preimp;
                $valor_venta = $fact_con_miva + $fact_sin_miva;
                $data = array(
                    "ublVersion" => "2.1",
                    "tipoOperacion" => "0101",
                    "tipoDoc" => $tipo_envio,
                    "serie" => $fact_nse_fact,
                    "correlativo" => (int) $fact_num_preimp,
                    "fechaEmision" => $fact_fech_fact,
                    "formaPago" => [],
                    "cuotas" => [],
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
                            "direccion" => $this->empr_dir_empr,
                            "ubigueo" => $this->empr_cpo_empr,
                            "departamento" => $this->prov_des_prov, // SAEPROV
                            "provincia" => $this->ciud_nom_ciud, // SAECANT O SAECIUD
                            "distrito" => $this->parr_des_parr // SAEPARR
                        ],
                        "email" => $this->empr_mai_empr,
                        "telephone" => $this->empr_tel_resp,
                    ],
                    "tipoMoneda" => $mone_sgl_mone,
                    "sumOtrosCargos" => 0,
                    "mtoOperGravadas" => $fact_con_miva,
                    "mtoOperInafectas" => 0,
                    "mtoOperExoneradas" => 0,
                    "mtoOperGratuitas" => 0,
                    "mtoIGV" => $fact_iva,
                    "mtoIGVGratuitas" => 0,
                    "valorVenta" => $valor_venta,
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

                $n_orden_compra = rtrim($fact_cm2_fact);

                if (strlen($n_orden_compra) > 0) {
                    $data['compra'] = strval($n_orden_compra);
                }

                if ($fact_cod_detra > 0) {

                    $valor_detra = round($valor_detra,0,PHP_ROUND_HALF_UP);

                    $data['detraccion'] = [
                        "percent" => floatval(number_format($tret_porct, 4, '.', '')),
                        "mount" => floatval(number_format($valor_detra, 0, '.', '')) ,
                        "ctaBanco" => strval($ctab_num_ctab),
                        "codBienDetraccion" =>  strval($tret_cod_imp),
                        "codMedioPago" => "001"
                    ];

                    $data['tipoOperacion'] = "1001";

                    $data['legends'][1] = [
                        "code" => "2006",
                        "value" => "OPERACION SUJETA A DETRACCION"
                    ];
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

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                            }

                            $fxfp_val_fxfp = $fxfp_val_fxfp - $valor_detra_rest;

                            if ($tipo == 'EFE') {
                                $tipo = 'Contado';
                            } else if ($tipo == 'CRE') {
                                $tipo = 'Credito';

                                $fxfp_fec_fin = $fact_fech_venc;

                                $data['cuotas'][$j++] = [
                                    "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                    "fechaPago" => $fxfp_fec_fin
                                ];
                            } else {
                                $tipo = 'Contado';
                            }

                            $data['formaPago'] = [
                                "moneda" => $mone_sgl_mone,
                                "tipo" => $tipo,
                                "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', ''))
                            ];
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                $sql = "SELECT dfac_cant_dfac, dfac_cod_prod, dfac_det_dfac, dfac_mont_total, dfac_por_iva, dfac_exc_iva, dfac_precio_dfac, dfac_obj_iva, dfac_des1_dfac FROM saedfac WHERE dfac_cod_fact = $fact_cod_fact";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        $igv_gratuitas=0;
                        $tot_op_gratuitas=0;
                        do {
                            $dfac_des1_dfac=0;
                            $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                            $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                            $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                            $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                            $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                            $dfac_exc_iva = $this->oIfx->f('dfac_exc_iva');
                            $dfac_obj_iva = $this->oIfx->f('dfac_obj_iva');
                            $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');
                            $dfac_des1_dfac = $this->oIfx->f('dfac_des1_dfac');

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                            }

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $dfac_precio_dfac  = $dfac_precio_dfac / $fact_val_tcam;
                            }

                            $valor_unitario=$dfac_precio_dfac;
                            if($dfac_des1_dfac > 0){
                                $por_descuento = $dfac_des1_dfac / 100;
                                $valor_descuento = $valor_unitario * $por_descuento;
                                $valor_unitario = $valor_unitario - $valor_descuento;
                            }

                            $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                            $precio_uni = $valor_unitario * $porcentaje_iva;
                            $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;

                            $tipAfeIgv = "10";
                            if ($dfac_exc_iva == 1) {
                                $tipAfeIgv = "20";
                                //$data['mtoImpVenta'] = floatval($dfac_por_iva);
                                $data['mtoOperExoneradas'] = floatval($fact_tot_fact);
                                $data['mtoOperGravadas'] = floatval($dfac_por_iva);
                            }
                            
                            ///OPERACIONES GRATUITAS
                            if ($dfac_obj_iva == 1) {
                                $igv_gratuitas+=$valor_iva;
                                $tipAfeIgv = "15";
                                //$data['tipoOperacion'] = "2106";
                                $tot_op_gratuitas+=$dfac_mont_total;
                                $precio_uni =0;
                                $valor_unitario=0;
                                $data['legends'][1] = [
                                    "code" => "1002",
                                    "value" => "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE"
                                ];
                            }
                            
                            if (empty($dfac_det_dfac)) {
                                $dfac_det_dfac = 'Sin detalle';
                            }
                            //OPERACIONES GRATUITAS
                            if ($dfac_obj_iva == 1) {
                                $data['details'][$j++] =
                                [
                                    "unidad" => "NIU",
                                    "cantidad" => floatval($dfac_cant_dfac),
                                    "codProducto" => $dfac_cod_prod,
                                    "descripcion" => $dfac_det_dfac,
                                    "mtoValorUnitario" => floatval(number_format($valor_unitario, 2, '.', '')),
                                    "mtoValorGratuito" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                    "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "tipAfeIgv" => $tipAfeIgv,
                                    "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                ];

                            }
                            else{
                                $data['details'][$j++] =
                                [
                                    "unidad" => "NIU",
                                    "cantidad" => floatval($dfac_cant_dfac),
                                    "codProducto" => $dfac_cod_prod,
                                    "descripcion" => $dfac_det_dfac,
                                    "mtoValorUnitario" => floatval(number_format($valor_unitario, 2, '.', '')),
                                    "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "tipAfeIgv" => $tipAfeIgv,
                                    "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                ];    
                            }

                            
                        } while ($this->oIfx->SiguienteRegistro());

                        //VALIDACION OPERACIONES GRATUITAS
                        if(floatval($tot_op_gratuitas)>0){
                            $data['mtoOperGratuitas'] = floatval(number_format($tot_op_gratuitas, 2, '.', ''));
                            $data['mtoIGVGratuitas'] = floatval(number_format($igv_gratuitas, 2, '.', ''));
                        }

                    }
                }
                $this->oIfx->Free();

                $data_json_envio = str_replace("&", "&amp;", json_encode($data));

                /**
                 * EMPIEZA ENVIO
                 */
                $headers = array(
                    "Content-Type:application/json"
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/invoice/send?token=" . $this->empr_token_api);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json_envio);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);

                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    switch ($http_code) {
                        case 200:
                            $validacion = $data_json['sunatResponse']['success'];

                            if ($validacion == true || $data_json['sunatResponse']['error']['code'] == '1033') {
                                try {

                                    if (strpos($data_json['sunatResponse']['cdrResponse']['description'], 'error') !== false) {
                                        throw new Exception($data_json['sunatResponse']['cdrResponse']['description']);
                                    }

                                    $this->oIfx->QueryT('BEGIN;');

                                    $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_envio . '-' . $fact_nse_fact . '-' . $fact_num_preimp;

                                    $ruta_xml = 'upload/xml/' . $nombre_documento . '.xml';
                                    $ruta_pdf = 'upload/pdf/fac_' . $nombre_documento . '.pdf';

                                    //CREACION FORMATO PERSONALZIADO
                                    $this->GenerarFacturaBoletaXmlPdfPeru($headers, $nombre_documento, $data);

                                    //FORMATO PERSONALIZADO - PERU

                                        reporte_factura_personalizado($fact_cod_fact, $nombre_documento, $fact_cod_sucu, $ruta_pdf);
                                    

                                    $div_button = '<div class="btn-group" role="group" aria-label="...">
                                        <a href="upload/xml/fac_' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                            <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                        </a>
                                        <a href="upload/pdf/fac_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>
                                    </div>';

                                    $fact_cod_hash = '';
                                    $ruta_xmlhash = 'modulos/envio_documentos/upload/xml/' . $nombre_documento . '.xml';
                                    $hash = DIR_FACTELEC . $ruta_xmlhash;
                                    $xml = new SimpleXMLElement(file_get_contents($hash));
                                    $fact_cod_hash = utf8_decode(current($xml->xpath("//ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestValue")));


                                    $values_crd_zip = $data_json['sunatResponse']['cdrZip'];

                                    $base64Data = $values_crd_zip; // Reemplaza esto con tu cadena Base64

                                    // Decodificar la cadena Base64
                                    $binaryData = base64_decode($base64Data);

                                    if ($binaryData !== false) {
                                        // Ruta donde deseas guardar el archivo
                                        $filePath = 'upload/cdr/R-' . $nombre_documento . '.zip'; // Cambia la ruta y el nombre del archivo según tus necesidades

                                        // Guarda los datos decodificados en un archivo
                                        $file = fopen($filePath, 'wb');
                                        fwrite($file, $binaryData);
                                        fclose($file);
                                    }

                                    $sql_update = "UPDATE saefact SET fact_aprob_sri = 'S',fact_cod_hash='$fact_cod_hash', fact_user_sri=$id_usuario WHERE fact_cod_fact = $fact_cod_fact ;";

                                    $this->oIfx->QueryT($sql_update);
                                    $this->oIfx->QueryT('COMMIT;');

                                    $correoMsj = envio_correo_adj_sunat($fact_email_clpv, $ruta_xml, $ruta_pdf, $fact_nom_cliente, $nombre_documento, $serie, $fact_cod_clpv, $tipo_envio);

                                    if ($data_json['sunatResponse']['error']['code'] == '1033') {
                                        $result_txt = 'Comprobante ya enviado.';
                                    } else {
                                        $result_txt = $data_json['sunatResponse']['cdrResponse']['description'];
                                    }

                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => $result_txt,
                                        'result_email' => 'Autorizado (' . $correoMsj . ')',
                                    );
                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }
                            } else {
                                $error = "Codigo: " . $data_json['sunatResponse']['error']['code'] . "<br>";
                                $error .= "Error: " . $data_json['sunatResponse']['error']['message'] . "<br>";

                                $error_250 = substr($data_json['sunatResponse']['error']['message'], 0, 250);
                                $error_250 = str_replace("'", "", $error_250);

                                $this->oIfx->QueryT('BEGIN;');
                                $sql_update = "UPDATE saefact SET fact_erro_sri = '$error_250' WHERE fact_cod_fact = $fact_cod_fact";
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
            } else {
                throw new Exception("Documento ya se encuentra Autorizado");
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }

    /**
     * FACTURAS Y BOLETAS ENVIO A LA SUNAT
     */
    function EnviarResumenesBoletas($array_fact, $fecha_emision)
    {

    
        try {
            session_start();
            $id_usuario = $_SESSION['U_ID'];

            $fecha_comprobante=$fecha_emision;
            /** JSON RESUMENES */
            /**VALIDACION NUMERO DE CORRELATIVO POR DIA*/

            $sql="SELECT MAX(correlativo::INTEGER) as ultimo from resumenes_boletas where empresa=$this->empr_cod_empr and (substring( fecha_emision from 1 for 10))='$fecha_emision'";
            $num_corr=intval(consulta_string($sql,'ultimo',$this->oIfx,0))+1;
            $num_corr     = cero_mas_func('0', 3 - strlen($num_corr)).$num_corr;
            

            // Crear un objeto DateTime con la zona horaria actual
            $date = new DateTime('now', new DateTimeZone('America/Bogota')); // Ajusta la zona horaria si es necesario

            // Formatear la fecha y hora en el formato deseado
            $fecha_generacion = $date->format('Y-m-d\TH:i:sP');
            
            $fecha_emision = $fecha_emision. "T"  . "12:00:00-00:00";


                $data = array(
                        "correlativo" => $num_corr,
                        "fecGeneracion" => $fecha_generacion,
                        "fecResumen" => $fecha_emision,
                        "moneda" => null,
                        "company" => [
                          "ruc" => $this->empr_ruc_empr,
                          "razonSocial" => $this->empr_nom_empr,
                          "nombreComercial" =>  $this->empr_nom_empr,
                          "address" => [
                            "ubigueo" => $this->empr_cpo_empr,
                            "codigoPais" => "PE",
                            "departamento" => $this->prov_des_prov,
                            "provincia" => $this->ciud_nom_ciud,
                            "distrito" => $this->parr_des_parr,
                            "urbanizacion" => "-",
                            "direccion" => $this->empr_dir_empr
                            ]
                        ],
                       "details" => []
                );
            //DETALLE DE LOS COMPROBANTES
            $j = 0;
            $num_comprobantes=0;

            foreach($array_fact as $fact_cod_fact){


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
                fac.fact_val_tcam,
                fac.fact_cod_detra,
                fac.fact_cm2_fact
                FROM
                saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
                WHERE fac.fact_cod_fact = $fact_cod_fact;";

                $fact_aprob_sri = 'N';
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                            $fact_nse_fact  = $this->oIfx->f('fact_nse_fact');
                            $fact_num_preimp = $this->oIfx->f('fact_num_preimp');
                            $fact_cod_clpv = $this->oIfx->f('fact_cod_clpv');
                            $fact_nom_cliente = $this->oIfx->f('fact_nom_cliente');
                            $fact_ruc_clie = $this->oIfx->f('fact_ruc_clie');
                            $fact_email_clpv = $this->oIfx->f('fact_email_clpv');
                            $fact_iva = $this->oIfx->f('fact_iva');
                            $fact_con_miva = $this->oIfx->f('fact_con_miva');
                            $fact_sin_miva = $this->oIfx->f('fact_sin_miva');
                            $fact_tot_fact = $fact_iva + $fact_con_miva + $fact_sin_miva;
                            $fact_cm4_fact = $this->oIfx->f('fact_cm4_fact');
                            $fact_cod_sucu = $this->oIfx->f('fact_cod_sucu');
                            $tipo_doc = $this->oIfx->f('clv_con_clpv');
                            $fact_aprob_sri = $this->oIfx->f('fact_aprob_sri');
                            $fact_cod_mone = $this->oIfx->f('fact_cod_mone');
                            $fact_val_tcam = $this->oIfx->f('fact_val_tcam');
                            $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $fact_iva       = $fact_iva / $fact_val_tcam;
                                $fact_con_miva  = $fact_con_miva / $fact_val_tcam;
                                $fact_sin_miva  = $fact_sin_miva / $fact_val_tcam;
                                $fact_tot_fact  = $fact_tot_fact / $fact_val_tcam;
                                $fact_cm4_fact  = $fact_cm4_fact / $fact_val_tcam;
                               

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
                                
                            }

                            //tipo de documento y envio
                            if (intval($tipo_doc) == 1) {
                                $tipo_docu = '6';
                                $tipo_envio = '01';
                            } else if (intval($tipo_doc) == 2) {
                                $tipo_docu = '1';
                                $tipo_envio = '03';
                            } else if (intval($tipo_doc) == 3) {
                                $tipo_docu = '7';
                                $tipo_envio = '03';
                            } else if (intval($tipo_doc) == 5) {
                                $tipo_docu = '4';
                                $tipo_envio = '03';
                            } else if (intval($tipo_doc) == 4) {
                                $tipo_docu = '0';
                                $tipo_envio = '03';
                            } else {
                                $tipo_docu = '03';
                            
                    
                        }
                    }
                }
                $this->oIfx->Free();

                $sql = "SELECT mone_sgl_mone, mone_des_mone from saemone where mone_cod_mone = $fact_cod_mone ";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        do {
                            $mone_sgl_mone = $this->oIfx->f('mone_sgl_mone');
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                $serie = $fact_nse_fact . '-' . (int) $fact_num_preimp;

            
                $data['moneda'] = $mone_sgl_mone;
                    $data['details'][$j++] =
                                    [
                                        "tipoDoc" => $tipo_envio,
                                        "serieNro" => $serie,
                                        "clienteTipo" => $tipo_docu,
                                        "clienteNro" => $fact_ruc_clie,
                                        "estado" => "1",
                                        "total" => $fact_tot_fact,
                                        "mtoOperGravadas" => $fact_con_miva,
                                        "mtoIGV" => $fact_iva
                                    ];
                $num_comprobantes++;
            }//CIERRE FOREACH


                $data_json_envio = str_replace("&", "&amp;", json_encode($data));

                /**
                 * EMPIEZA ENVIO
                 */
                $headers = array(
                    "Content-Type:application/json"
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/summary/send?token=" . $this->empr_token_api);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json_envio);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);

                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    switch ($http_code) {
                        case 200:
                            $validacion = $data_json['sunatResponse']['success'];

                            if ($validacion == true) {
                                try {

                                    $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_envio . '-' . $num_corr . '-' . $fecha_comprobante;

                                    //GENERACION XML PDF RESUMENES
                                    $this->GenerarResumenBoletaXmlPdfPeru($headers, $nombre_documento, $data);

                                    $div_button = '<div class="btn-group" role="group" aria-label="...">
                                        <a href="upload/xml/rc_' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                            <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                        </a>
                                        <a href="upload/pdf/rc_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>
                                    </div>';


                                    $this->oIfx->QueryT('BEGIN;');

                                    //INGRESO REGISTRO TABLA RESUMENES
                                    $fecha_server=date('Y-m-d H:i:s');
                                    $sql="INSERT INTO resumenes_boletas (empresa, correlativo, fecha_generacion, fecha_emision,fecha_server, usuario_id, numero_comprobantes)
                                    VALUES ($this->empr_cod_empr, '$num_corr','$fecha_generacion', '$fecha_emision','$fecha_server', $id_usuario, $num_comprobantes) RETURNING id";
                                    $this->oIfx->QueryT($sql);
                                    $id_resumen = $this->oIfx->ResRow['id'];

                                    foreach($array_fact as $fact_cod_fact){
                                    
                                        $sql_update = "UPDATE saefact SET fact_aprob_sri = 'S', fact_user_sri=$id_usuario, fact_cod_rc=$id_resumen WHERE fact_cod_fact = $fact_cod_fact ;";

                                        $this->oIfx->QueryT($sql_update);
                                    }
                                    $this->oIfx->QueryT('COMMIT;');

                                    //$correoMsj = envio_correo_adj_sunat($fact_email_clpv, $ruta_xml, $ruta_pdf, $fact_nom_cliente, $nombre_documento, $serie, $fact_cod_clpv, $tipo_envio);

                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => '',
                                        'result_email' => 'Resumen Enviado Correctamente',
                                    );
                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }
                            } else {
                                $error = "Codigo: " . $data_json['sunatResponse']['error']['code'] . "<br>";
                                $error .= "Error: " . $data_json['sunatResponse']['error']['message'] . "<br>";

                                $error_250 = substr($data_json['sunatResponse']['error']['message'], 0, 250);
                                $error_250 = str_replace("'", "", $error_250);

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
            
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }

    function GenerarResumenBoletaXmlPdfPeru($headers, $nombre_documento, $data)
    {
        //GENERA XML
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/summary/xml?token=" . $this->empr_token_api);
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

        $nombre = $nombre_documento . ".xml";
        $ruta_xml = $ruta . '/rc_' . $nombre;
        $archivo = fopen($ruta_xml, "w+");
        //fwrite($archivo, utf8_encode($data_xml));
        fwrite($archivo, $data_xml);
        fclose($archivo);

        //GENERA PDF
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/summary/pdf?token=" . $this->empr_token_api);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data_pdf = curl_exec($ch);

        $ruta_pdf = 'upload/pdf/rc_' . $nombre_documento . '.pdf';
        //        header('Content-Type: application/pdf');
        file_put_contents($ruta_pdf, $data_pdf);

        return true;
    }

    function GenerarFacturaBoletaXmlPdfPeru($headers, $nombre_documento, $data)
    {
        //GENERA XML
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/invoice/xml?token=" . $this->empr_token_api);
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

        $nombre = $nombre_documento . ".xml";
        $ruta_xml = $ruta . '/' . $nombre;
        $archivo = fopen($ruta_xml, "w+");
        //fwrite($archivo, utf8_encode($data_xml));
        fwrite($archivo, $data_xml);
        fclose($archivo);

        //GENERA PDF
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/invoice/pdf?token=" . $this->empr_token_api);
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


    function EnviarFacturaBoletaPeruOCE($fact_cod_fact)
    {
        try {
            session_start();
            $id_usuario = $_SESSION['U_ID'];
            $idempresa = $_SESSION['U_EMPRESA'];

            //CONTROL FORMATOS PERSONALIZADOS
            $sql = "select ftrn_ubi_web from saeftrn  where ftrn_cod_empr=$idempresa and ftrn_des_ftrn = 'FACTURA' and ftrn_cod_modu=7 and (ftrn_ubi_web is not null or ftrn_ubi_web != '')";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $ubi =  $this->oIfx->f('ftrn_ubi_web');
                    if (!empty($ubi)) {
                        include_once('../../' . $ubi . '');
                       
                    }
                    else{
                        //FORMATO ESTANDAR PERU
                        include_once('../../Include/Formatos/comercial/factura_peru.php');
                    }
                }
            }
            $this->oIfx->Free();

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
            fac.fact_val_tcam,
            fac.fact_cod_detra,
            fac.fact_cm2_fact
            FROM
	        saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
            WHERE fac.fact_cod_fact = $fact_cod_fact;";

            $fact_aprob_sri = 'N';
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $fact_nse_fact  = $this->oIfx->f('fact_nse_fact');
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
                        $fact_val_tcam = $this->oIfx->f('fact_val_tcam');
                        $fact_cod_detra = $this->oIfx->f('fact_cod_detra');
                        $fact_cm2_fact = $this->oIfx->f('fact_cm2_fact');
                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                        if ($fact_cod_detra > 0) {
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

                            if (empty($tret_cod_imp) || empty($tret_cod_banc)) {
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

                        if ($this->pcon_seg_mone == $fact_cod_mone) {
                            $fact_iva       = $fact_iva / $fact_val_tcam;
                            $fact_con_miva  = $fact_con_miva / $fact_val_tcam;
                            $fact_sin_miva  = $fact_sin_miva / $fact_val_tcam;
                            $fact_tot_fact  = $fact_tot_fact / $fact_val_tcam;
                            $fact_cm4_fact  = $fact_cm4_fact / $fact_val_tcam;
                            if ($fact_cod_detra > 0) {
                                $valor_detra_rest   = $valor_detra / $fact_val_tcam;
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
                            } else {
                                $valor_detra_rest   = 0;
                            }
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
                        } else if (intval($tipo_doc) == 5) {
                            $tipo_docu = '4';
                            $tipo_envio = '03';
                        } else if (intval($tipo_doc) == 4) {
                            $tipo_docu = '0';
                            $tipo_envio = '03';
                        } else {
                            $tipo_docu = '0';
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            $sql = "SELECT mone_sgl_mone, mone_des_mone from saemone where mone_cod_mone = $fact_cod_mone ";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $mone_sgl_mone = $this->oIfx->f('mone_sgl_mone');
                        $mone_des_mone = $this->oIfx->f('mone_des_mone');
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, $mone_des_mone));

                $fact_hor_ini = substr($fact_hor_ini, -8);
                $fact_hor_fin = substr($fact_hor_fin, -8);
                $fact_fech_fact = $fact_fech_fact . "T"  . "12:00:00-00:00";
                $fact_fech_venc = $fact_fech_venc . "T" . "12:00:00-00:00";
                $serie = $fact_nse_fact . '-' . $fact_num_preimp;
                $valor_venta = $fact_con_miva + $fact_sin_miva;
                $data = array(
                    "ublVersion" => "2.1",
                    "tipoOperacion" => "0101",
                    "tipoDoc" => $tipo_envio,
                    "serie" => $fact_nse_fact,
                    "correlativo" => (int) $fact_num_preimp,
                    "fechaEmision" => $fact_fech_fact,
                    "formaPago" => [],
                    "cuotas" => [],
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
                            "direccion" => $this->empr_dir_empr,
                            "ubigueo" => $this->empr_cpo_empr,
                            "departamento" => $this->prov_des_prov, // SAEPROV
                            "provincia" => $this->ciud_nom_ciud, // SAECANT O SAECIUD
                            "distrito" => $this->parr_des_parr // SAEPARR
                        ],
                        "email" => $this->empr_mai_empr,
                        "telephone" => $this->empr_tel_resp,
                    ],
                    "tipoMoneda" => $mone_sgl_mone,
                    "sumOtrosCargos" => 0,
                    "mtoOperGravadas" => $fact_con_miva,
                    "mtoOperInafectas" => 0,
                    "mtoOperExoneradas" => 0,
                    "mtoIGV" => $fact_iva,
                    "valorVenta" => $valor_venta,
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

                $n_orden_compra = rtrim($fact_cm2_fact);

                if (strlen($n_orden_compra) > 0) {
                    $data['compra'] = strval($n_orden_compra);
                }

                if ($fact_cod_detra > 0) {
                    $data['detraccion'] = [
                        "percent" => floatval(number_format($tret_porct, 4, '.', '')),
                        "mount" => floatval(number_format($valor_detra, 2, '.', '')),
                        "ctaBanco" => strval($ctab_num_ctab),
                        "codBienDetraccion" =>  strval($tret_cod_imp),
                        "codMedioPago" => "001"
                    ];

                    $data['tipoOperacion'] = "1001";

                    $data['legends'][1] = [
                        "code" => "2006",
                        "value" => "OPERACION SUJETA A DETRACCION"
                    ];
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

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                            }

                            $fxfp_val_fxfp = $fxfp_val_fxfp - $valor_detra_rest;

                            if ($tipo == 'EFE') {
                                $tipo = 'Contado';
                            } else if ($tipo == 'CRE') {
                                $tipo = 'Credito';

                                $fxfp_fec_fin = $fact_fech_venc;

                                $data['cuotas'][$j++] = [
                                    "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                    "fechaPago" => $fxfp_fec_fin
                                ];
                            } else {
                                $tipo = 'Contado';
                            }

                            $data['formaPago'] = [
                                "moneda" => $mone_sgl_mone,
                                "tipo" => $tipo,
                                "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', ''))
                            ];
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                $sql = "SELECT dfac_cant_dfac, dfac_cod_prod, dfac_det_dfac, dfac_mont_total, dfac_por_iva, dfac_exc_iva FROM saedfac WHERE dfac_cod_fact = $fact_cod_fact";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {
                            $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                            $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                            $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                            $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                            $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                            $dfac_exc_iva = $this->oIfx->f('dfac_exc_iva');

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                            }

                            $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                            $precio_uni = $dfac_mont_total * $porcentaje_iva;
                            $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;

                            $tipAfeIgv = "10";
                            if ($dfac_exc_iva == 1) {
                                $tipAfeIgv = "20";
                                //$data['mtoImpVenta'] = floatval($dfac_por_iva);
                                $data['mtoOperExoneradas'] = floatval($fact_tot_fact);
                                $data['mtoOperGravadas'] = floatval($dfac_por_iva);
                            }

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
                                    "tipAfeIgv" => $tipAfeIgv,
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
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/invoice/send?token=" . $this->empr_token_api);
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

                            if ($validacion == true || $data_json['sunatResponse']['error']['code'] == '1033') {
                                try {

                                    if (strpos($data_json['sunatResponse']['cdrResponse']['description'], 'error') !== false) {
                                        throw new Exception($data_json['sunatResponse']['cdrResponse']['description']);
                                    }

                                    $this->oIfx->QueryT('BEGIN;');

                                    $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_envio . '-' . $fact_nse_fact . '-' . $fact_num_preimp;

                                    $ruta_xml = 'upload/xml/' . $nombre_documento . '.xml';
                                    $ruta_pdf = 'upload/pdf/fac_' . $nombre_documento . '.pdf';

                                    //CREACION FORMATO PERSONALZIADO
                                    $this->GenerarFacturaBoletaXmlPdfPeru($headers, $nombre_documento, $data);

                                    //FORMATO PERSONALIZADO
                                        reporte_factura_personalizado($fact_cod_fact, $nombre_documento, $fact_cod_sucu, $ruta_pdf);
                                    

                                    $div_button = '<div class="btn-group" role="group" aria-label="...">
                                        <a href="upload/xml/fac_' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                            <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                        </a>
                                        <a href="upload/pdf/fac_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>
                                    </div>';

                                    $fact_cod_hash = '';
                                    $ruta_xmlhash = 'modulos/envio_documentos/upload/xml/' . $nombre_documento . '.xml';
                                    $hash = DIR_FACTELEC . $ruta_xmlhash;
                                    $xml = new SimpleXMLElement(file_get_contents($hash));
                                    $fact_cod_hash = utf8_decode(current($xml->xpath("//ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestValue")));


                                    $values_crd_zip = $data_json['sunatResponse']['cdrZip'];

                                    $base64Data = $values_crd_zip; // Reemplaza esto con tu cadena Base64

                                    // Decodificar la cadena Base64
                                    $binaryData = base64_decode($base64Data);

                                    if ($binaryData !== false) {
                                        // Ruta donde deseas guardar el archivo
                                        $filePath = 'upload/cdr/R-' . $nombre_documento . '.zip'; // Cambia la ruta y el nombre del archivo según tus necesidades

                                        // Guarda los datos decodificados en un archivo
                                        $file = fopen($filePath, 'wb');
                                        fwrite($file, $binaryData);
                                        fclose($file);
                                    }

                                    $sql_update = "UPDATE saefact SET fact_aprob_sri = 'S',fact_cod_hash='$fact_cod_hash', fact_user_sri=$id_usuario WHERE fact_cod_fact = $fact_cod_fact ;";

                                    $this->oIfx->QueryT($sql_update);
                                    $this->oIfx->QueryT('COMMIT;');

                                    $correoMsj = envio_correo_adj_sunat($fact_email_clpv, $ruta_xml, $ruta_pdf, $fact_nom_cliente, $nombre_documento, $serie, $fact_cod_clpv, $tipo_envio);

                                    if ($data_json['sunatResponse']['error']['code'] == '1033') {
                                        $result_txt = 'Comprobante ya enviado.';
                                    } else {
                                        $result_txt = $data_json['sunatResponse']['cdrResponse']['description'];
                                    }

                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => $result_txt,
                                        'result_email' => 'Autorizado (' . $correoMsj . ')',
                                    );
                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }
                            } else {
                                $error = "Codigo: " . $data_json['sunatResponse']['error']['code'] . "<br>";
                                $error .= "Error: " . $data_json['sunatResponse']['error']['message'] . "<br>";

                                $error_250 = substr($data_json['sunatResponse']['error']['message'], 0, 250);
                                $error_250 = str_replace("'", "", $error_250);

                                $this->oIfx->QueryT('BEGIN;');
                                $sql_update = "UPDATE saefact SET fact_erro_sri = '$error_250' WHERE fact_cod_fact = $fact_cod_fact";
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
            } else {
                throw new Exception("Documento ya se encuentra Autorizado");
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
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
                        } else if (intval($tipo_doc) == 5) {
                            $tipo_docu = '4';
                            $tipo_envio = '03';
                        } else if (intval($tipo_doc) == 4) {
                            $tipo_docu = '0';
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
                $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, "SOLES"));

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

                if (!$fact_cm1_fact) {
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
                    "cuotas" => [],
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
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/note/send?token=" . $this->empr_token_api);
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

                                $error_250 = substr($data_json['sunatResponse']['error']['message'], 0, 250);
                                $error_250 = str_replace("'", "", $error_250);

                                $this->oIfx->QueryT('BEGIN;');
                                $sql_update = "UPDATE saefact SET fact_erro_sri = '$error_250' WHERE fact_cod_fact = $fact_cod_fact";
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
            } else {
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
        curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/note/xml?token=" . $this->empr_token_api);
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

        $nombre = "deb_" . $nombre_documento . ".xml";
        $ruta_xml = $ruta . '/' . $nombre;
        $archivo = fopen($ruta_xml, "w+");
        fwrite($archivo, utf8_encode($data_xml));
        fclose($archivo);

        //GENERA PDF
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/note/pdf?token=" . $this->empr_token_api);
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

            //CONTROL FORMATOS PERSONALIZADOS
            $sql = "select ftrn_ubi_web from saeftrn  where ftrn_cod_empr=$idempresa and ftrn_des_ftrn = 'FACTURA' and ftrn_cod_modu=7 and (ftrn_ubi_web is not null or ftrn_ubi_web != '')";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $ubi =  $this->oIfx->f('ftrn_ubi_web');
                    if (!empty($ubi)) {
                        include_once('../../' . $ubi . '');
                        $ctrl_formato++;
                    }
                    else{
                        //FORMATO ESTANDAR PERU
                        include_once('../../Include/Formatos/comercial/factura_peru.php');
                    }
                }else{
                    include_once('../../Include/Formatos/comercial/factura_peru.php');
                }
            }
            $this->oIfx->Free();

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
                        $fact_cod_mone = $this->oIfx->f('fact_cod_mone');
                        $fact_val_tcam = $this->oIfx->f('fact_val_tcam');
                        $motivo_ncre = $this->oIfx->f('motivo_ncre');

                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                        if ($this->pcon_seg_mone == $fact_cod_mone) {
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
                        } else if (intval($tipo_doc) == 5) {
                            $tipo_docu = '4';
                            $tipo_envio = '03';
                        } else if (intval($tipo_doc) == 4) {
                            $tipo_docu = '0';
                            $tipo_envio = '03';
                        } else {
                            $tipo_docu = '0';
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();


            $sql = "SELECT mone_sgl_mone, mone_des_mone from saemone where mone_cod_mone = $fact_cod_mone ";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $mone_sgl_mone = $this->oIfx->f('mone_sgl_mone');
                        $mone_des_mone = $this->oIfx->f('mone_des_mone');
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();


            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, $mone_des_mone));

                $fact_fech_fact = $fact_fech_fact . "T"  . "12:00:00-00:00";
                $fact_fech_venc = $fact_fech_venc . "T" . "12:00:00-00:00";
                $serie = $fact_nse_fact . '-' . $fact_num_preimp;
                /**
                 * EXPLODE FACTURA MODIFICA
                 */

                if ($fact_cod_ndb) {

                    $sql = "SELECT
	                fac.fact_nse_fact, fac.fact_num_preimp
                    FROM
	                saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
                    WHERE fac.fact_cod_fact = $fact_cod_ndb;";

                    if ($this->oIfx->Query($sql)) {
                        if ($this->oIfx->NumFilas() > 0) {
                            do {
                                $fact_num_preimp_afect = (int) $this->oIfx->f('fact_num_preimp');
                                $fact_nse_fact_afect = substr($this->oIfx->f('fact_nse_fact'), 3, 9);
                            } while ($this->oIfx->SiguienteRegistro());
                        }
                    }
                    $this->oIfx->Free();

                    $numDocfectado = "$fact_nse_fact_afect-$fact_num_preimp_afect";
                } else {
                    if (strlen($fact_aux_preimp) >= 11 && $fact_cod_ndb > 0) {
                        $exp_fac = explode("-", $fact_aux_preimp);
                        $nse_fac = substr($exp_fac[0], 3, 9);
                        $num_fac = (int) $exp_fac[1];
                        $numDocfectado = "$nse_fac-$num_fac";
                    } else {
                        $numDocfectado = $fact_aux_preimp;
                    }
                }

                $tipo_doc = "07";

                if (!$fact_cm1_fact) {
                    $fact_cm1_fact = "NOTA DE CREDITO AFECTA FACTURA $numDocfectado";
                }

                $valor_venta = $fact_con_miva + $fact_sin_miva;

                if (empty($motivo_ncre)) {
                    throw new Exception("Sin motivo configurado en la nota de credito");
                }
                $sql = "SELECT ddev_tip_ddev, ddev_des_ddev from saeddev where ddev_des_ddev = '$motivo_ncre'";
                $motivo_envio = consulta_string($sql, 'ddev_tip_ddev', $this->oIfx, '');
                $motivo_envio_nombre = consulta_string($sql, 'ddev_des_ddev', $this->oIfx, '');

                $ctrl_cuotas='N';
                if ($fact_cod_ndb) {
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

                                if ($this->pcon_seg_mone == $fact_cod_mone) {
                                    $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                                }

                                $fxfp_val_fxfp = $fxfp_val_fxfp - $valor_detra_rest;

                                if ($tipo == 'EFE') {
                                    $tipo = 'Contado';
                                } else if ($tipo == 'CRE') {
                                    $tipo = 'Credito';

                                    $fxfp_fec_fin = $fact_fech_venc;
                                    $ctrl_cuotas='S';
                                } else {
                                    $tipo = 'Contado';
                                }
                                
                               
                            } while ($this->oIfx->SiguienteRegistro());
                        }
                    }
                    $this->oIfx->Free();
                }

                $data = array(
                    "ublVersion" => "2.1",
                    "tipoDoc" => $tipo_doc,
                    "serie" => $fact_nse_fact,
                    "correlativo" => (int) $fact_num_preimp,
                    "fechaEmision" => $fact_fech_fact,

                    "tipDocAfectado" => $tipo_envio,
                    "numDocfectado" => $numDocfectado,
                    "codMotivo" => strval($motivo_envio),
                    "desMotivo" => $motivo_envio_nombre,
                    //"cuotas" => [],
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
                            "direccion" => $this->empr_dir_empr,
                            "ubigueo" => $this->empr_cpo_empr,
                            "departamento" => $this->prov_des_prov, // SAEPROV
                            "provincia" => $this->ciud_nom_ciud, // SAECANT O SAECIUD
                            "distrito" => $this->parr_des_parr // SAEPARR
                        ],
                        "email" => $this->empr_mai_empr,
                        "telephone" => $this->empr_tel_resp,
                    ],
                    "tipoMoneda" => $mone_sgl_mone,
                    "sumOtrosCargos" => 0,
                    "mtoOperGravadas" => $fact_con_miva,
                    "mtoOperInafectas" => 0,
                    "mtoOperExoneradas" => 0,
                    "mtoOperGratuitas" => 0,
                    "mtoIGV" => $fact_iva,
                    "mtoIGVGratuitas" => 0,
                    "valorVenta" => $valor_venta,
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



                if($ctrl_cuotas=='S'){
                    $data["cuotas"]=[];
                }

                
                if ($fact_cod_ndb) {
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

                                if ($this->pcon_seg_mone == $fact_cod_mone) {
                                    $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                                }

                                $fxfp_val_fxfp = $fxfp_val_fxfp - $valor_detra_rest;

                                if ($tipo == 'EFE') {
                                    $tipo = 'Contado';
                                } else if ($tipo == 'CRE') {
                                    $tipo = 'Credito';

                                    $fxfp_fec_fin = $fact_fech_venc;

                                    $data['cuotas'][$j++] = [
                                        "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                        "fechaPago" => $fxfp_fec_fin
                                    ];

                                    $data['formaPago'] = [
                                        "moneda" => $mone_sgl_mone,
                                        "tipo" => $tipo,
                                        "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', ''))
                                    ];
                                } else {
                                    $tipo = 'Contado';
                                }
                                
                                
                            } while ($this->oIfx->SiguienteRegistro());
                        }
                    }
                    $this->oIfx->Free();
                }

                //var_dump($data);exit;


                $sql = "select 
                        dncr_cant_dfac as dfac_cant_dfac,
                        dncr_cod_prod as dfac_cod_prod,
                        dncr_det_dncr as dfac_det_dfac,
                        dncr_mont_total as dfac_mont_total,
                        dncr_por_iva as dfac_por_iva,
                        dncr_exc_iva as dfac_exc_iva,
                        dncr_obj_iva as dfac_obj_iva,
                        dncr_des1_dfac as dncr_des1_dfac,
                        dncr_precio_dfac as  dfac_precio_dfac
                        from saedncr
                        where dncr_cod_ncre = $ncre_cod_ncre;";

                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        $igv_gratuitas=0;
                        $tot_op_gratuitas=0;
                        do {
                            $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                            $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                            $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                            $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                            $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                            $dfac_exc_iva = $this->oIfx->f('dfac_exc_iva');
                            $dfac_obj_iva = $this->oIfx->f('dfac_obj_iva');
                            $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');
                            $dncr_des1_dfac = $this->oIfx->f('dncr_des1_dfac');

                            //VALIDAICON OPERACIONES GRATUITAS
                            if(round($dncr_des1_dfac,2)>0 && $dfac_obj_iva == 1){
                                $dfac_mont_total  = round(($dfac_cant_dfac*$dfac_precio_dfac),2);
                            }

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                            }

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $dfac_precio_dfac  = $dfac_precio_dfac / $fact_val_tcam;
                            }

                            $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                            $precio_uni = $dfac_precio_dfac * $porcentaje_iva;
                            $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;
                            

                            $tipAfeIgv = "10";
                            if ($dfac_exc_iva == 1) {
                                $tipAfeIgv = "20";
                                //$data['mtoImpVenta'] = floatval($dfac_por_iva);
                                $data['mtoOperExoneradas'] = floatval($fact_tot_fact);
                                $data['mtoOperGravadas'] = floatval($dfac_por_iva);
                            }
                            $valor_unitario=$dfac_precio_dfac;
                            ///OPERACIONES GRATUITAS
                            if ($dfac_obj_iva == 1) {
                                $igv_gratuitas+=$valor_iva;
                                $tipAfeIgv = "15";
                                //$data['tipoOperacion'] = "2106";
                                $tot_op_gratuitas+=$dfac_mont_total;
                                $precio_uni =0;
                                $valor_unitario=0;
                                $data['legends'][1] = [
                                    "code" => "1002",
                                    "value" => "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE"
                                ];
                            }

                            if (empty($dfac_det_dfac)) {
                                $dfac_det_dfac = 'Sin detalle';
                            }

                            //OPERACIONES GRATUITAS
                            if ($dfac_obj_iva == 1) {
                                $data['details'][$j++] =
                                [
                                    "unidad" => "NIU",
                                    "cantidad" => floatval($dfac_cant_dfac),
                                    "codProducto" => $dfac_cod_prod,
                                    "descripcion" => $dfac_det_dfac,
                                    "mtoValorUnitario" => floatval(number_format($valor_unitario, 2, '.', '')),
                                    "mtoValorGratuito" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                    "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "tipAfeIgv" => $tipAfeIgv,
                                    "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                ];

                            }
                            else{
                                $data['details'][$j++] =
                                [
                                    "unidad" => "NIU",
                                    "cantidad" => floatval($dfac_cant_dfac),
                                    "codProducto" => $dfac_cod_prod,
                                    "descripcion" => $dfac_det_dfac,
                                    "mtoValorUnitario" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                    "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "tipAfeIgv" => $tipAfeIgv,
                                    "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                ];    
                            }

                        
                        } while ($this->oIfx->SiguienteRegistro());
                        //VALIDACION OPERACIONES GRATUITAS
                        if(floatval($tot_op_gratuitas)>0){
                            $data['mtoOperGratuitas'] = floatval(number_format($tot_op_gratuitas, 2, '.', ''));
                            $data['mtoIGVGratuitas'] = floatval(number_format($igv_gratuitas, 2, '.', ''));
                        }
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
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/note/send?token=" . $this->empr_token_api);
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

                            if ($validacion == true || $data_json['sunatResponse']['error']['code'] == '1033') {
                                try {

                                    if (strpos($data_json['sunatResponse']['cdrResponse']['description'], 'error') !== false) {
                                        throw new Exception($data_json['sunatResponse']['cdrResponse']['description']);
                                    }

                                    $this->oIfx->QueryT('BEGIN;');

                                    $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_doc . '-' . $fact_nse_fact . '-' . $fact_num_preimp;

                                    $ruta_xml = 'upload/xml/' . $nombre_documento . '.xml';
                                    $ruta_pdf = 'upload/pdf/cred_' . $nombre_documento . '.pdf';


                                    //CREACION FORMATO PERSONALZADO
                                    $this->GenerarNotaCreditoXmlPdfPeru($headers, $nombre_documento, $data);
                                    //FORMATO PERSONALIZADO
                                        reporte_notaCredito_personalizado($ncre_cod_ncre, $nombre_documento, $fact_cod_sucu, $ruta_pdf);
                                    

                                    $div_button = '<div class="btn-group" role="group" aria-label="...">
                                        <a href="upload/xml/' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                            <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                        </a>
                                        <a href="upload/pdf/cred_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>
                                    </div>';

                                    $ncre_cod_hash = '';
                                    $ruta_xmlhash = 'modulos/envio_documentos/upload/xml/' . $nombre_documento . '.xml';
                                    $hash = DIR_FACTELEC . $ruta_xmlhash;
                                    $xml = new SimpleXMLElement(file_get_contents($hash));
                                    $ncre_cod_hash = utf8_decode(current($xml->xpath("//ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestValue")));

                                    $values_crd_zip = $data_json['sunatResponse']['cdrZip'];

                                    $base64Data = $values_crd_zip; // Reemplaza esto con tu cadena Base64

                                    // Decodificar la cadena Base64
                                    $binaryData = base64_decode($base64Data);

                                    if ($binaryData !== false) {
                                        // Ruta donde deseas guardar el archivo
                                        $filePath = 'upload/cdr/R-' . $nombre_documento . '.zip'; // Cambia la ruta y el nombre del archivo según tus necesidades

                                        // Guarda los datos decodificados en un archivo
                                        $file = fopen($filePath, 'wb');
                                        fwrite($file, $binaryData);
                                        fclose($file);
                                    }

                                    $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'S', ncre_cod_hash='$ncre_cod_hash', ncre_user_sri = $id_usuario WHERE ncre_cod_ncre = $ncre_cod_ncre;";

                                    $this->oIfx->QueryT($sql_update);
                                    $this->oIfx->QueryT('COMMIT;');

                                    $correoMsj = envio_correo_adj_sunat($fact_email_clpv, $ruta_xml, $ruta_pdf, $fact_nom_cliente, $nombre_documento, $serie, $fact_cod_clpv, $tipo_envio, 'NOTA DE CREDITO');

                                    if ($data_json['sunatResponse']['error']['code'] == '1033') {
                                        $result_txt = 'Comprobante ya enviado.';
                                    } else {
                                        $result_txt = $data_json['sunatResponse']['cdrResponse']['description'];
                                    }

                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => $result_txt,
                                        'result_email' => 'Autorizado (' . $correoMsj . ')'
                                    );
                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }
                            } else {
                                $error = "Codigo: " . $data_json['sunatResponse']['error']['code'] . "<br>";
                                $error .= "Error: " . $data_json['sunatResponse']['error']['message'] . "<br>";

                                $error_250 = substr($data_json['sunatResponse']['error']['message'], 0, 250);
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
            } else {
                throw new Exception("Documento ya se encuentra Autorizado");
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }
    /**
     * RESUMEN NOTAS DE CREDITO ATADAS A BOLETAS - ENVIO A LA SUNAT
     */

     function EnviarResumenesBoletasNotaCredito($ncre_cod_ncre)
     {
         try {
 
             session_start();
             $id_usuario = $_SESSION['U_ID'];
 
             $idempresa = $_SESSION['U_EMPRESA'];
 
             //CONTROL FORMATOS PERSONALIZADOS
             $sql = "select ftrn_ubi_web from saeftrn  where ftrn_cod_empr=$idempresa and ftrn_des_ftrn = 'FACTURA' and ftrn_cod_modu=7 and (ftrn_ubi_web is not null or ftrn_ubi_web != '')";
             if ($this->oIfx->Query($sql)) {
                 if ($this->oIfx->NumFilas() > 0) {
                     $ubi =  $this->oIfx->f('ftrn_ubi_web');
                     if (!empty($ubi)) {
                         include_once('../../' . $ubi . '');
                         $ctrl_formato++;
                     }
                     else{
                         //FORMATO ESTANDAR PERU
                         include_once('../../Include/Formatos/comercial/factura_peru.php');
                     }
                 }else{
                     include_once('../../Include/Formatos/comercial/factura_peru.php');
                 }
             }
             $this->oIfx->Free();
 
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
                         $fact_cod_mone = $this->oIfx->f('fact_cod_mone');
                         $fact_val_tcam = $this->oIfx->f('fact_val_tcam');
                         $motivo_ncre = $this->oIfx->f('motivo_ncre');
 
                         $fact_nse_fact = substr($fact_nse_fact, 3, 9);
 
                         if ($this->pcon_seg_mone == $fact_cod_mone) {
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
                         } else if (intval($tipo_doc) == 5) {
                             $tipo_docu = '4';
                             $tipo_envio = '03';
                         } else if (intval($tipo_doc) == 4) {
                             $tipo_docu = '0';
                             $tipo_envio = '03';
                         } else {
                             $tipo_docu = '0';
                         }
                     } while ($this->oIfx->SiguienteRegistro());
                 }
             }
             $this->oIfx->Free();
 
 
             $sql = "SELECT mone_sgl_mone, mone_des_mone from saemone where mone_cod_mone = $fact_cod_mone ";
             if ($this->oIfx->Query($sql)) {
                 if ($this->oIfx->NumFilas() > 0) {
                     do {
                         $mone_sgl_mone = $this->oIfx->f('mone_sgl_mone');
                         $mone_des_mone = $this->oIfx->f('mone_des_mone');
                     } while ($this->oIfx->SiguienteRegistro());
                 }
             }
             $this->oIfx->Free();
 
 
             /** CONTROL PARA FACTURA YA ENVIADA */
             if ($fact_aprob_sri == 'N') {
                 $V = new EnLetras();
                 $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, $mone_des_mone));
 
                 $fact_fech_fact = $fact_fech_fact . "T"  . "12:00:00-00:00";
                 $fact_fech_venc = $fact_fech_venc . "T" . "12:00:00-00:00";
                 $serie = $fact_nse_fact . '-' . $fact_num_preimp;
                 /**
                  * EXPLODE FACTURA MODIFICA
                  */
 
                 if ($fact_cod_ndb) {
 
                     $sql = "SELECT
                     fac.fact_nse_fact, fac.fact_num_preimp
                     FROM
                     saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
                     WHERE fac.fact_cod_fact = $fact_cod_ndb;";
 
                     if ($this->oIfx->Query($sql)) {
                         if ($this->oIfx->NumFilas() > 0) {
                             do {
                                 $fact_num_preimp_afect = (int) $this->oIfx->f('fact_num_preimp');
                                 $fact_nse_fact_afect = substr($this->oIfx->f('fact_nse_fact'), 3, 9);
                             } while ($this->oIfx->SiguienteRegistro());
                         }
                     }
                     $this->oIfx->Free();
 
                     $numDocfectado = "$fact_nse_fact_afect-$fact_num_preimp_afect";
                 } else {
                     if (strlen($fact_aux_preimp) >= 11 && $fact_cod_ndb > 0) {
                         $exp_fac = explode("-", $fact_aux_preimp);
                         $nse_fac = substr($exp_fac[0], 3, 9);
                         $num_fac = (int) $exp_fac[1];
                         $numDocfectado = "$nse_fac-$num_fac";
                     } else {
                         $numDocfectado = $fact_aux_preimp;
                     }
                 }
 
                 $tipo_doc = "07";
 
                 if (!$fact_cm1_fact) {
                     $fact_cm1_fact = "NOTA DE CREDITO AFECTA FACTURA $numDocfectado";
                 }
 
                 $valor_venta = $fact_con_miva + $fact_sin_miva;
 
                 if (empty($motivo_ncre)) {
                     throw new Exception("Sin motivo configurado en la nota de credito");
                 }
                 $sql = "SELECT ddev_tip_ddev, ddev_des_ddev from saeddev where ddev_des_ddev = '$motivo_ncre'";
                 $motivo_envio = consulta_string($sql, 'ddev_tip_ddev', $this->oIfx, '');
                 $motivo_envio_nombre = consulta_string($sql, 'ddev_des_ddev', $this->oIfx, '');
 
                 $ctrl_cuotas='N';
                 if ($fact_cod_ndb) {
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
 
                                 if ($this->pcon_seg_mone == $fact_cod_mone) {
                                     $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                                 }
 
                                 $fxfp_val_fxfp = $fxfp_val_fxfp - $valor_detra_rest;
 
                                 if ($tipo == 'EFE') {
                                     $tipo = 'Contado';
                                 } else if ($tipo == 'CRE') {
                                     $tipo = 'Credito';
 
                                     $fxfp_fec_fin = $fact_fech_venc;
                                     $ctrl_cuotas='S';
                                 } else {
                                     $tipo = 'Contado';
                                 }
                                 
                                
                             } while ($this->oIfx->SiguienteRegistro());
                         }
                     }
                     $this->oIfx->Free();
                 }
 
                 $data = array(
                     "ublVersion" => "2.1",
                     "tipoDoc" => $tipo_doc,
                     "serie" => $fact_nse_fact,
                     "correlativo" => (int) $fact_num_preimp,
                     "fechaEmision" => $fact_fech_fact,
 
                     "tipDocAfectado" => $tipo_envio,
                     "numDocfectado" => $numDocfectado,
                     "codMotivo" => strval($motivo_envio),
                     "desMotivo" => $motivo_envio_nombre,
                     //"cuotas" => [],
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
                             "direccion" => $this->empr_dir_empr,
                             "ubigueo" => $this->empr_cpo_empr,
                             "departamento" => $this->prov_des_prov, // SAEPROV
                             "provincia" => $this->ciud_nom_ciud, // SAECANT O SAECIUD
                             "distrito" => $this->parr_des_parr // SAEPARR
                         ],
                         "email" => $this->empr_mai_empr,
                         "telephone" => $this->empr_tel_resp,
                     ],
                     "tipoMoneda" => $mone_sgl_mone,
                     "sumOtrosCargos" => 0,
                     "mtoOperGravadas" => $fact_con_miva,
                     "mtoOperInafectas" => 0,
                     "mtoOperExoneradas" => 0,
                     "mtoOperGratuitas" => 0,
                     "mtoIGV" => $fact_iva,
                     "mtoIGVGratuitas" => 0,
                     "valorVenta" => $valor_venta,
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
 
 
 
                 if($ctrl_cuotas=='S'){
                     $data["cuotas"]=[];
                 }
 
                 
                 if ($fact_cod_ndb) {
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
 
                                 if ($this->pcon_seg_mone == $fact_cod_mone) {
                                     $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                                 }
 
                                 $fxfp_val_fxfp = $fxfp_val_fxfp - $valor_detra_rest;
 
                                 if ($tipo == 'EFE') {
                                     $tipo = 'Contado';
                                 } else if ($tipo == 'CRE') {
                                     $tipo = 'Credito';
 
                                     $fxfp_fec_fin = $fact_fech_venc;
 
                                     $data['cuotas'][$j++] = [
                                         "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                         "fechaPago" => $fxfp_fec_fin
                                     ];
 
                                     $data['formaPago'] = [
                                         "moneda" => $mone_sgl_mone,
                                         "tipo" => $tipo,
                                         "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', ''))
                                     ];
                                 } else {
                                     $tipo = 'Contado';
                                 }
                                 
                                 
                             } while ($this->oIfx->SiguienteRegistro());
                         }
                     }
                     $this->oIfx->Free();
                 }
 
                 //var_dump($data);exit;
 
 
                 $sql = "select 
                         dncr_cant_dfac as dfac_cant_dfac,
                         dncr_cod_prod as dfac_cod_prod,
                         dncr_det_dncr as dfac_det_dfac,
                         dncr_mont_total as dfac_mont_total,
                         dncr_por_iva as dfac_por_iva,
                         dncr_exc_iva as dfac_exc_iva,
                         dncr_obj_iva as dfac_obj_iva,
                         dncr_des1_dfac as dncr_des1_dfac,
                         dncr_precio_dfac as  dfac_precio_dfac
                         from saedncr
                         where dncr_cod_ncre = $ncre_cod_ncre;";
 
                 if ($this->oIfx->Query($sql)) {
                     if ($this->oIfx->NumFilas() > 0) {
                         $j = 0;
                         $igv_gratuitas=0;
                         $tot_op_gratuitas=0;
                         do {
                             $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                             $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                             $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                             $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                             $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                             $dfac_exc_iva = $this->oIfx->f('dfac_exc_iva');
                             $dfac_obj_iva = $this->oIfx->f('dfac_obj_iva');
                             $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');
                             $dncr_des1_dfac = $this->oIfx->f('dncr_des1_dfac');
 
                             //VALIDAICON OPERACIONES GRATUITAS
                             if(round($dncr_des1_dfac,2)>0 && $dfac_obj_iva == 1){
                                 $dfac_mont_total  = round(($dfac_cant_dfac*$dfac_precio_dfac),2);
                             }
 
                             if ($this->pcon_seg_mone == $fact_cod_mone) {
                                 $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                             }
 
                             if ($this->pcon_seg_mone == $fact_cod_mone) {
                                 $dfac_precio_dfac  = $dfac_precio_dfac / $fact_val_tcam;
                             }
 
                             $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                             $precio_uni = $dfac_precio_dfac * $porcentaje_iva;
                             $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;
                             
 
                             $tipAfeIgv = "10";
                             if ($dfac_exc_iva == 1) {
                                 $tipAfeIgv = "20";
                                 //$data['mtoImpVenta'] = floatval($dfac_por_iva);
                                 $data['mtoOperExoneradas'] = floatval($fact_tot_fact);
                                 $data['mtoOperGravadas'] = floatval($dfac_por_iva);
                             }
                             $valor_unitario=$dfac_precio_dfac;
                             ///OPERACIONES GRATUITAS
                             if ($dfac_obj_iva == 1) {
                                 $igv_gratuitas+=$valor_iva;
                                 $tipAfeIgv = "15";
                                 //$data['tipoOperacion'] = "2106";
                                 $tot_op_gratuitas+=$dfac_mont_total;
                                 $precio_uni =0;
                                 $valor_unitario=0;
                                 $data['legends'][1] = [
                                     "code" => "1002",
                                     "value" => "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE"
                                 ];
                             }
 
                             if (empty($dfac_det_dfac)) {
                                 $dfac_det_dfac = 'Sin detalle';
                             }
 
                             //OPERACIONES GRATUITAS
                             if ($dfac_obj_iva == 1) {
                                 $data['details'][$j++] =
                                 [
                                     "unidad" => "NIU",
                                     "cantidad" => floatval($dfac_cant_dfac),
                                     "codProducto" => $dfac_cod_prod,
                                     "descripcion" => $dfac_det_dfac,
                                     "mtoValorUnitario" => floatval(number_format($valor_unitario, 2, '.', '')),
                                     "mtoValorGratuito" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                     "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                     "tipAfeIgv" => $tipAfeIgv,
                                     "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                     "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                     "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                     "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                     "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                 ];
 
                             }
                             else{
                                 $data['details'][$j++] =
                                 [
                                     "unidad" => "NIU",
                                     "cantidad" => floatval($dfac_cant_dfac),
                                     "codProducto" => $dfac_cod_prod,
                                     "descripcion" => $dfac_det_dfac,
                                     "mtoValorUnitario" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                     "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                     "tipAfeIgv" => $tipAfeIgv,
                                     "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                     "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                     "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                     "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                     "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                 ];    
                             }
 
                         
                         } while ($this->oIfx->SiguienteRegistro());
                         //VALIDACION OPERACIONES GRATUITAS
                         if(floatval($tot_op_gratuitas)>0){
                             $data['mtoOperGratuitas'] = floatval(number_format($tot_op_gratuitas, 2, '.', ''));
                             $data['mtoIGVGratuitas'] = floatval(number_format($igv_gratuitas, 2, '.', ''));
                         }
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
                 curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/note/send?token=" . $this->empr_token_api);
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
 
                             if ($validacion == true || $data_json['sunatResponse']['error']['code'] == '1033') {
                                 try {
 
                                     if (strpos($data_json['sunatResponse']['cdrResponse']['description'], 'error') !== false) {
                                         throw new Exception($data_json['sunatResponse']['cdrResponse']['description']);
                                     }
 
                                     $this->oIfx->QueryT('BEGIN;');
 
                                     $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_doc . '-' . $fact_nse_fact . '-' . $fact_num_preimp;
 
                                     $ruta_xml = 'upload/xml/' . $nombre_documento . '.xml';
                                     $ruta_pdf = 'upload/pdf/cred_' . $nombre_documento . '.pdf';
 
 
                                     //CREACION FORMATO PERSONALZADO
                                     $this->GenerarNotaCreditoXmlPdfPeru($headers, $nombre_documento, $data);
                                     //FORMATO PERSONALIZADO
                                         reporte_notaCredito_personalizado($ncre_cod_ncre, $nombre_documento, $fact_cod_sucu, $ruta_pdf);
                                     
 
                                     $div_button = '<div class="btn-group" role="group" aria-label="...">
                                         <a href="upload/xml/' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                             <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                         </a>
                                         <a href="upload/pdf/cred_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                             <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                         </a>
                                     </div>';
 
                                     $ncre_cod_hash = '';
                                     $ruta_xmlhash = 'modulos/envio_documentos/upload/xml/' . $nombre_documento . '.xml';
                                     $hash = DIR_FACTELEC . $ruta_xmlhash;
                                     $xml = new SimpleXMLElement(file_get_contents($hash));
                                     $ncre_cod_hash = utf8_decode(current($xml->xpath("//ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestValue")));
 
                                     $values_crd_zip = $data_json['sunatResponse']['cdrZip'];
 
                                     $base64Data = $values_crd_zip; // Reemplaza esto con tu cadena Base64
 
                                     // Decodificar la cadena Base64
                                     $binaryData = base64_decode($base64Data);
 
                                     if ($binaryData !== false) {
                                         // Ruta donde deseas guardar el archivo
                                         $filePath = 'upload/cdr/R-' . $nombre_documento . '.zip'; // Cambia la ruta y el nombre del archivo según tus necesidades
 
                                         // Guarda los datos decodificados en un archivo
                                         $file = fopen($filePath, 'wb');
                                         fwrite($file, $binaryData);
                                         fclose($file);
                                     }
 
                                     $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'S', ncre_cod_hash='$ncre_cod_hash', ncre_user_sri = $id_usuario WHERE ncre_cod_ncre = $ncre_cod_ncre;";
 
                                     $this->oIfx->QueryT($sql_update);
                                     $this->oIfx->QueryT('COMMIT;');
 
                                     $correoMsj = envio_correo_adj_sunat($fact_email_clpv, $ruta_xml, $ruta_pdf, $fact_nom_cliente, $nombre_documento, $serie, $fact_cod_clpv, $tipo_envio, 'NOTA DE CREDITO');
 
                                     if ($data_json['sunatResponse']['error']['code'] == '1033') {
                                         $result_txt = 'Comprobante ya enviado.';
                                     } else {
                                         $result_txt = $data_json['sunatResponse']['cdrResponse']['description'];
                                     }
 
                                     $result = array(
                                         'div_button' => $div_button,
                                         'result_ws' => $result_txt,
                                         'result_email' => 'Autorizado (' . $correoMsj . ')'
                                     );
                                 } catch (Exception $e) {
                                     $this->oIfx->QueryT('ROLLBACK;');
                                     throw new Exception($e->getMessage());
                                 }
                             } else {
                                 $error = "Codigo: " . $data_json['sunatResponse']['error']['code'] . "<br>";
                                 $error .= "Error: " . $data_json['sunatResponse']['error']['message'] . "<br>";
 
                                 $error_250 = substr($data_json['sunatResponse']['error']['message'], 0, 250);
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
             } else {
                 throw new Exception("Documento ya se encuentra Autorizado");
             }
         } catch (Exception $e) {
             throw new Exception($e->getMessage());
         }
 
         return $result;
     }
    /**
     * NOATS DE CREDITO COMERCIAL ENVIO A LA SUNAT
     */
    function EnviarNotaCreditoPeruComercial($ncre_cod_ncre)
    {
        try {

            session_start();
            $id_usuario = $_SESSION['U_ID'];

            $idempresa = $_SESSION['U_EMPRESA'];

            //CONTROL FORMATOS PERSONALIZADOS
            $sql = "select ftrn_ubi_web from saeftrn  where ftrn_cod_empr=$idempresa and ftrn_des_ftrn = 'FACTURA' and ftrn_cod_modu=7 and (ftrn_ubi_web is not null or ftrn_ubi_web != '')";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $ubi =  $this->oIfx->f('ftrn_ubi_web');
                    if (!empty($ubi)) {
                        include_once('../../' . $ubi . '');
                        $ctrl_formato++;
                    }
                    else{
                        //FORMATO ESTANDAR PERU
                        include_once('../../Include/Formatos/comercial/factura_peru.php');
                    }
                }else{
                    include_once('../../Include/Formatos/comercial/factura_peru.php');
                }
            }
            $this->oIfx->Free();

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
                        $fact_cod_mone = $this->oIfx->f('fact_cod_mone');
                        $fact_val_tcam = $this->oIfx->f('fact_val_tcam');
                        $motivo_ncre = $this->oIfx->f('motivo_ncre');

                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                        if ($this->pcon_seg_mone == $fact_cod_mone) {
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
                        } else if (intval($tipo_doc) == 5) {
                            $tipo_docu = '4';
                            $tipo_envio = '03';
                        } else if (intval($tipo_doc) == 4) {
                            $tipo_docu = '0';
                            $tipo_envio = '03';
                        } else {
                            $tipo_docu = '0';
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();


            $sql = "SELECT mone_sgl_mone, mone_des_mone from saemone where mone_cod_mone = $fact_cod_mone ";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $mone_sgl_mone = $this->oIfx->f('mone_sgl_mone');
                        $mone_des_mone = $this->oIfx->f('mone_des_mone');
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();


            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, $mone_des_mone));

                $fact_fech_fact = $fact_fech_fact . "T"  . "12:00:00-00:00";
                $fact_fech_venc = $fact_fech_venc . "T" . "12:00:00-00:00";
                $serie = $fact_nse_fact . '-' . $fact_num_preimp;
                /**
                 * EXPLODE FACTURA MODIFICA
                 */

                if ($fact_cod_ndb) {

                    $sql = "SELECT
	                fac.fact_nse_fact, fac.fact_num_preimp
                    FROM
	                saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
                    WHERE fac.fact_cod_fact = $fact_cod_ndb;";

                    if ($this->oIfx->Query($sql)) {
                        if ($this->oIfx->NumFilas() > 0) {
                            do {
                                $fact_num_preimp_afect = (int) $this->oIfx->f('fact_num_preimp');
                                $fact_nse_fact_afect = substr($this->oIfx->f('fact_nse_fact'), 3, 9);
                            } while ($this->oIfx->SiguienteRegistro());
                        }
                    }
                    $this->oIfx->Free();

                    $numDocfectado = "$fact_nse_fact_afect-$fact_num_preimp_afect";
                } else {
                    if (strlen($fact_aux_preimp) >= 11 && $fact_cod_ndb > 0) {
                        $exp_fac = explode("-", $fact_aux_preimp);
                        $nse_fac = substr($exp_fac[0], 3, 9);
                        $num_fac = (int) $exp_fac[1];
                        $numDocfectado = "$nse_fac-$num_fac";
                    } else {
                        $numDocfectado = $fact_aux_preimp;
                    }
                }

                $tipo_doc = "07";

                if (!$fact_cm1_fact) {
                    $fact_cm1_fact = "NOTA DE CREDITO AFECTA FACTURA $numDocfectado";
                }

                $valor_venta = $fact_con_miva + $fact_sin_miva;

                if (empty($motivo_ncre)) {
                    throw new Exception("Sin motivo configurado en la nota de credito");
                }
                $sql = "SELECT ddev_tip_ddev, ddev_des_ddev from saeddev where ddev_des_ddev = '$motivo_ncre'";
                $motivo_envio = consulta_string($sql, 'ddev_tip_ddev', $this->oIfx, '');
                $motivo_envio_nombre = consulta_string($sql, 'ddev_des_ddev', $this->oIfx, '');

                $data = array(
                    "ublVersion" => "2.1",
                    "tipoDoc" => $tipo_doc,
                    "serie" => $fact_nse_fact,
                    "correlativo" => (int) $fact_num_preimp,
                    "fechaEmision" => $fact_fech_fact,

                    "tipDocAfectado" => $tipo_envio,
                    "numDocfectado" => $numDocfectado,
                    "codMotivo" => strval($motivo_envio),
                    "desMotivo" => $motivo_envio_nombre,
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
                            "direccion" => $this->empr_dir_empr,
                            "ubigueo" => $this->empr_cpo_empr,
                            "departamento" => $this->prov_des_prov, // SAEPROV
                            "provincia" => $this->ciud_nom_ciud, // SAECANT O SAECIUD
                            "distrito" => $this->parr_des_parr // SAEPARR
                        ],
                        "email" => $this->empr_mai_empr,
                        "telephone" => $this->empr_tel_resp,
                    ],
                    "tipoMoneda" => $mone_sgl_mone,
                    "sumOtrosCargos" => 0,
                    "mtoOperGravadas" => $fact_con_miva,
                    "mtoOperInafectas" => 0,
                    "mtoOperExoneradas" => 0,
                    "mtoOperGratuitas" => 0,
                    "mtoIGV" => $fact_iva,
                    "mtoIGVGratuitas" => 0,
                    "valorVenta" => $valor_venta,
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

                if ($fact_cod_ndb) {
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

                                if ($this->pcon_seg_mone == $fact_cod_mone) {
                                    $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                                }

                                $fxfp_val_fxfp = $fxfp_val_fxfp - $valor_detra_rest;

                                if ($tipo == 'EFE') {
                                    $tipo = 'Contado';
                                } else if ($tipo == 'CRE') {
                                    $tipo = 'Credito';

                                    $fxfp_fec_fin = $fact_fech_venc;

                                    /*$data['cuotas'][$j++] = [
                                        "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                        "fechaPago" => $fxfp_fec_fin
                                    ];*/
                                } else {
                                    $tipo = 'Contado';
                                }
                                
                                /*$data['formaPago'] = [
                                    "moneda" => $mone_sgl_mone,
                                    "tipo" => $tipo,
                                    "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', ''))
                                ];*/
                            } while ($this->oIfx->SiguienteRegistro());
                        }
                    }
                    $this->oIfx->Free();
                }

                $sql = "select 
                        dncr_cant_dfac as dfac_cant_dfac,
                        dncr_cod_prod as dfac_cod_prod,
                        dncr_det_dncr as dfac_det_dfac,
                        dncr_mont_total as dfac_mont_total,
                        dncr_por_iva as dfac_por_iva,
                        dncr_exc_iva as dfac_exc_iva,
                        dncr_obj_iva as dfac_obj_iva,
                        dncr_des1_dfac as dncr_des1_dfac,
                        dncr_precio_dfac as  dfac_precio_dfac
                        from saedncr
                        where dncr_cod_ncre = $ncre_cod_ncre;";

                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        $igv_gratuitas=0;
                        $tot_op_gratuitas=0;
                        do {
                            $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                            $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                            $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                            $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                            $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                            $dfac_exc_iva = $this->oIfx->f('dfac_exc_iva');
                            $dfac_obj_iva = $this->oIfx->f('dfac_obj_iva');
                            $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');
                            $dncr_des1_dfac = $this->oIfx->f('dncr_des1_dfac');

                            //VALIDAICON OPERACIONES GRATUITAS
                            if(round($dncr_des1_dfac,2)>0 && $dfac_obj_iva == 1){
                                $dfac_mont_total  = round(($dfac_cant_dfac*$dfac_precio_dfac),2);
                            }

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                            }

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $dfac_precio_dfac  = $dfac_precio_dfac / $fact_val_tcam;
                            }

                            $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                            $precio_uni = $dfac_precio_dfac * $porcentaje_iva;
                            $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;
                            

                            $tipAfeIgv = "10";
                            if ($dfac_exc_iva == 1) {
                                $tipAfeIgv = "20";
                                //$data['mtoImpVenta'] = floatval($dfac_por_iva);
                                $data['mtoOperExoneradas'] = floatval($fact_tot_fact);
                                $data['mtoOperGravadas'] = floatval($dfac_por_iva);
                            }
                            $valor_unitario=$dfac_precio_dfac;
                            ///OPERACIONES GRATUITAS
                            if ($dfac_obj_iva == 1) {
                                $igv_gratuitas+=$valor_iva;
                                $tipAfeIgv = "15";
                                //$data['tipoOperacion'] = "2106";
                                $tot_op_gratuitas+=$dfac_mont_total;
                                $precio_uni =0;
                                $valor_unitario=0;
                                $data['legends'][1] = [
                                    "code" => "1002",
                                    "value" => "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE"
                                ];
                            }

                            if (empty($dfac_det_dfac)) {
                                $dfac_det_dfac = 'Sin detalle';
                            }

                            //OPERACIONES GRATUITAS
                            if ($dfac_obj_iva == 1) {
                                $data['details'][$j++] =
                                [
                                    "unidad" => "NIU",
                                    "cantidad" => floatval($dfac_cant_dfac),
                                    "codProducto" => $dfac_cod_prod,
                                    "descripcion" => $dfac_det_dfac,
                                    "mtoValorUnitario" => floatval(number_format($valor_unitario, 2, '.', '')),
                                    "mtoValorGratuito" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                    "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "tipAfeIgv" => $tipAfeIgv,
                                    "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                ];

                            }
                            else{
                                $data['details'][$j++] =
                                [
                                    "unidad" => "NIU",
                                    "cantidad" => floatval($dfac_cant_dfac),
                                    "codProducto" => $dfac_cod_prod,
                                    "descripcion" => $dfac_det_dfac,
                                    "mtoValorUnitario" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                    "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "tipAfeIgv" => $tipAfeIgv,
                                    "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                ];    
                            }

                        
                        } while ($this->oIfx->SiguienteRegistro());
                        //VALIDACION OPERACIONES GRATUITAS
                        if(floatval($tot_op_gratuitas)>0){
                            $data['mtoOperGratuitas'] = floatval(number_format($tot_op_gratuitas, 2, '.', ''));
                            $data['mtoIGVGratuitas'] = floatval(number_format($igv_gratuitas, 2, '.', ''));
                        }
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
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/note/send?token=" . $this->empr_token_api);
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

                            if ($validacion == true || $data_json['sunatResponse']['error']['code'] == '1033') {
                                try {

                                    if (strpos($data_json['sunatResponse']['cdrResponse']['description'], 'error') !== false) {
                                        throw new Exception($data_json['sunatResponse']['cdrResponse']['description']);
                                    }

                                    $this->oIfx->QueryT('BEGIN;');

                                    $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_doc . '-' . $fact_nse_fact . '-' . $fact_num_preimp;

                                    $ruta_xml = 'upload/xml/' . $nombre_documento . '.xml';
                                    $ruta_pdf = 'upload/pdf/cred_' . $nombre_documento . '.pdf';


                                    //CREACION FORMATO PERSONALZADO
                                    $this->GenerarNotaCreditoXmlPdfPeru($headers, $nombre_documento, $data);
                                    //FORMATO PERSONALIZADO
                                        reporte_notaCredito_personalizado($ncre_cod_ncre, $nombre_documento, $fact_cod_sucu, $ruta_pdf);
                                    

                                    $div_button = '<div class="btn-group" role="group" aria-label="...">
                                        <a href="upload/xml/' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                            <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                        </a>
                                        <a href="upload/pdf/cred_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>
                                    </div>';

                                    $ncre_cod_hash = '';
                                    $ruta_xmlhash = 'modulos/envio_documentos/upload/xml/' . $nombre_documento . '.xml';
                                    $hash = DIR_FACTELEC . $ruta_xmlhash;
                                    $xml = new SimpleXMLElement(file_get_contents($hash));
                                    $ncre_cod_hash = utf8_decode(current($xml->xpath("//ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestValue")));

                                    $values_crd_zip = $data_json['sunatResponse']['cdrZip'];

                                    $base64Data = $values_crd_zip; // Reemplaza esto con tu cadena Base64

                                    // Decodificar la cadena Base64
                                    $binaryData = base64_decode($base64Data);

                                    if ($binaryData !== false) {
                                        // Ruta donde deseas guardar el archivo
                                        $filePath = 'upload/cdr/R-' . $nombre_documento . '.zip'; // Cambia la ruta y el nombre del archivo según tus necesidades

                                        // Guarda los datos decodificados en un archivo
                                        $file = fopen($filePath, 'wb');
                                        fwrite($file, $binaryData);
                                        fclose($file);
                                    }

                                    $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'S', ncre_cod_hash='$ncre_cod_hash', ncre_user_sri = $id_usuario WHERE ncre_cod_ncre = $ncre_cod_ncre;";

                                    $this->oIfx->QueryT($sql_update);
                                    $this->oIfx->QueryT('COMMIT;');

                                    $correoMsj = envio_correo_adj_sunat($fact_email_clpv, $ruta_xml, $ruta_pdf, $fact_nom_cliente, $nombre_documento, $serie, $fact_cod_clpv, $tipo_envio, 'NOTA DE CREDITO');

                                    if ($data_json['sunatResponse']['error']['code'] == '1033') {
                                        $result_txt = 'Comprobante ya enviado.';
                                    } else {
                                        $result_txt = $data_json['sunatResponse']['cdrResponse']['description'];
                                    }

                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => $result_txt,
                                        'result_email' => 'Autorizado (' . $correoMsj . ')'
                                    );
                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }
                            } else {
                                $error = "Codigo: " . $data_json['sunatResponse']['error']['code'] . "<br>";
                                $error .= "Error: " . $data_json['sunatResponse']['error']['message'] . "<br>";

                                $error_250 = substr($data_json['sunatResponse']['error']['message'], 0, 250);
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
            } else {
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
        curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/note/xml?token=" . $this->empr_token_api);
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

        $nombre = $nombre_documento . ".xml";
        $ruta_xml = $ruta . '/' . $nombre;
        $archivo = fopen($ruta_xml, "w+");
        //fwrite($archivo, utf8_encode($data_xml));
        fwrite($archivo, $data_xml);
        fclose($archivo);

        //GENERA PDF
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/note/pdf?token=" . $this->empr_token_api);
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


            $sql = "select ftrn_ubi_web from saeftrn where ftrn_cod_modu=7 and ftrn_des_ftrn='GUIA REMISION' and ftrn_cod_empr=$this->empr_cod_empr";
            $ubigui = consulta_string($sql, 'ftrn_ubi_web', $this->oIfx, '');

            if (!empty($ubigui)) {

            //CONTROL FORMATOS PERSONALIZADOS
            $sql = "select ftrn_ubi_web from saeftrn  where ftrn_cod_empr=$this->empr_cod_empr and ftrn_des_ftrn = 'FACTURA' and ftrn_cod_modu=7 and (ftrn_ubi_web is not null or ftrn_ubi_web != '')";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $ubi =  $this->oIfx->f('ftrn_ubi_web');
                    if (!empty($ubi)) {
                        include_once('../../' . $ubi . '');
                        
                    }
                    else{
                        //FORMATO ESTANDAR PERU
                        include_once('../../Include/Formatos/comercial/factura_peru.php');
                    }
                }else{
                    include_once('../../Include/Formatos/comercial/factura_peru.php');
                }
            }
            $this->oIfx->Free();

            }


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
            fac.guia_peso_bruto as peso_bruto,
            fac.guia_cod_trta as guia_cod_trta,
            fac.guia_sal_guia as guia_sal_guia,
            fac.guia_num_plac as guia_num_plac,
            fac.guia_num_tick as guia_num_tick,
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
                        $fact_dir_clie = trim($this->oIfx->f('fact_dir_clie'));
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
                        $peso_bruto = $this->oIfx->f('peso_bruto');

                        $fact_cod_ndb = $this->oIfx->f('fact_cod_ndb');
                        $fact_aux_preimp = $this->oIfx->f('fact_aux_preimp');
                        $fact_fec_emis_aux = $this->oIfx->f('fact_fec_emis_aux');
                        $guia_cod_trta = $this->oIfx->f('guia_cod_trta');
                        $guia_sal_guia = $this->oIfx->f('guia_sal_guia');
                        $guia_num_plac = trim($this->oIfx->f('guia_num_plac'));
                        $guia_num_tick = trim($this->oIfx->f('guia_num_tick'));

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
                        } else if (intval($tipo_doc) == 5) {
                            $tipo_docu = '4';
                            $tipo_envio = '03';
                        } else if (intval($tipo_doc) == 4) {
                            $tipo_docu = '0';
                            $tipo_envio = '03';
                        } else {
                            $tipo_docu = '0';
                        }

                        //DIRECCION DE PARTIDA POR SUCURSAL

                        $sqls="select sucu_dir_sucu, sucu_telf_secu, sucu_ubi_geo from saesucu where sucu_cod_sucu=$fact_cod_sucu";
                        $guia_dir_sucu   =consulta_string($sqls,'sucu_dir_sucu', $this->oCon,'');
                        $sucu_ubigeo   =consulta_string($sqls,'sucu_ubi_geo', $this->oCon,'');

                        if(strlen($sucu_ubigeo)!=6){
                            throw new Exception("El codigo ubigeo de la Sucursal no se encuentra configurado correctamente ");
                        }


                           //DATOS DEL TRANSPORTISTA
                        if (!empty($guia_cod_trta)) {
                            $sqlTran = "select * from saetrta where trta_cod_trta = $guia_cod_trta and trta_cod_empr =$this->empr_cod_empr";
                        } else {
                            $sqlTran = "select * from saetrta where trta_cid_trta = '$guia_sal_guia' and trta_cod_empr = $this->empr_cod_empr";
                        }


                        if ($this->oCon->Query($sqlTran)) {
                            if ($this->oCon->NumFilas() > 0) {
                                do {
                                    //$oReturn->alert('si');
                                    $trta_nom_trta = $this->oCon->f("trta_nom_trta");
                                    $trta_cid_trta = $this->oCon->f("trta_cid_trta");
                                    $trta_tip_iden = $this->oCon->f("trta_tip_iden");
                                    $trta_plc_cami = $this->oCon->f("trta_plc_cami");
                                    $trta_cod_cate = $this->oCon->f("trta_cod_cate");

                                    //DATOS DEL TRANSPORTISTA
                                    $trta_ch_iden = trim($this->oCon->f("trta_ch_iden"));
                                    $trta_cid_ch = trim($this->oCon->f("trta_cid_ch"));
                                    $trta_nom_ch = trim($this->oCon->f("trta_nom_ch"));
                                    $trta_ape_ch = trim($this->oCon->f("trta_ape_ch"));
                                    $trta_num_lic = trim($this->oCon->f("trta_num_lic"));

                                    $trta_num_mtc = trim($this->oCon->f("trta_num_mtc"));

                                    if(empty($guia_num_plac)){
                                        $guia_num_plac=$trta_plc_cami;
                                    }
                                    if($trta_cod_cate==1){
                                        $trta_tip_iden=$trta_ch_iden;
                                    }


                                    if (intval($trta_tip_iden) == 1) {
                                        $tipo_docu_transp = '6';

                                    } else if (intval($trta_tip_iden) == 2) {
                                        $tipo_docu_transp = '1';

                                    } else if (intval($trta_tip_iden) == 3) {
                                        $tipo_docu_transp = '7';

                                    } else if (intval($trta_tip_iden) == 5) {
                                        $tipo_docu_transp = '4';

                                    } else if (intval($trta_tip_iden) == 4) {
                                        $tipo_docu_transp = '0';

                                    } else {
                                        $tipo_docu_transp = '0';
                                    }


                                } while ($this->oCon->SiguienteRegistro());
                            }
                        }

                        $this->oCon->Free();


                        

                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            //VALIDAICION UBIGEO DIRECCIONES CLIENTE
            $ubigeo_destino='';
            if(!empty($fact_dir_clie)){

            $sqlg="SELECT CONCAT(trim(prov_cod_char), trim(cant_cod_char), trim(parr_cod_char)) as ubigeo
            from saedire 
            inner join saeprov on prov_cod_prov = dire_cod_prov
            inner join saecant on cant_cod_cant = dire_cod_cant
            inner join saeparr on parr_cod_parr = dire_cod_parr
            where trim(dire_dir_dire) like '$fact_dir_clie%' and dire_cod_clpv=$fact_cod_clpv and dire_cod_empr=$this->empr_cod_empr";

            $ubigeo_destino=consulta_string($sqlg,'ubigeo', $this->oCon,'');

            }

            if(strlen($ubigeo_destino)!=6){
                throw new Exception("El codigo ubigeo de la direccion del cliente no se encuentra configurado correctamente verifique la Ficha");
            }


            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, "SOLES"));

                $fact_fech_fact = $fact_fech_fact . "T"  . "12:00:00-00:00";
                $fact_fech_venc = $fact_fech_venc . "T" . "12:00:00-00:00";
                $serie = $fact_nse_fact . '-' . $fact_num_preimp;

            //JSON GUIAS DE REMISION


            //PRIVADO
            if($trta_cod_cate==1){

                if(empty($trta_num_lic)){
                     throw new Exception("Transportista sin número de licencia");
                }
                $data = array(
                    "version" => "2022",
                    "tipoDoc" => "09",
                    "serie" => $fact_nse_fact,
                    "correlativo" => (int) $fact_num_preimp,
                    "fechaEmision" => $fact_fech_fact,
                    "company" => [
                        "ruc" => $this->empr_ruc_empr,
                        "razonSocial" => $this->empr_nom_empr,
                        "nombreComercial" => $this->empr_nomcome_empr
                        
                    ],
                    "details" => [],
                    "observacion" => "DOCUMENTO GENERADO EN JIREH WEB",
                    "destinatario" => [
                        "tipoDoc" => $tipo_docu,
                        "numDoc" => $fact_ruc_clie,
                        "rznSocial" => $fact_nom_cliente
                    ],
                    "envio" => [
                        "codTraslado" => "01",
                        "desTraslado" => "VENTA",
                        "modTraslado" => "02",
                        "pesoTotal" => $peso_bruto,
                        "undPesoTotal" => "KGM",
                        "fecTraslado" => $fact_fech_fact,
                        "vehiculo"=> [
                                    "placa"=> $guia_num_plac
                                    
                            ],
                                "choferes"=> [
                                    [
                                        "tipo"=> "Principal",
                                        "tipoDoc"=> $tipo_docu_transp,
                                        "nroDoc"=> $trta_cid_ch,
                                        "licencia"=> $trta_num_lic,
                                        "nombres"=> $trta_nom_ch,
                                        "apellidos"=> $trta_ape_ch
                                    ]
                                ],
                        "llegada" => [
                            "ubigueo" => $ubigeo_destino,
                            "direccion" => $fact_dir_clie
                        ],
                        "partida" => [
                            "ubigueo" => $sucu_ubigeo,
                            "direccion" => $guia_dir_sucu
                        ],
                    ],
                );
            } 
            //PUBLICO
            else if($trta_cod_cate==2){

                $data = array(
                    "version" => "2022",
                    "tipoDoc" => "09",
                    "serie" => $fact_nse_fact,
                    "correlativo" => (int) $fact_num_preimp,
                    "fechaEmision" => $fact_fech_fact,
                    "company" => [
                        "ruc" => $this->empr_ruc_empr,
                        "razonSocial" => $this->empr_nom_empr,
                        "nombreComercial" => $this->empr_nomcome_empr
                    ],
                    "details" => [],
                    "observacion" => "DOCUMENTO GENERADO EN JIREH WEB",
                    "destinatario" => [
                        "tipoDoc" => $tipo_docu,
                        "numDoc" => $fact_ruc_clie,
                        "rznSocial" => $fact_nom_cliente
                    ],
                    "envio" => [
                        "codTraslado" => "01",
                        "desTraslado" => "VENTA",
                        "modTraslado" => "01",
                        "pesoTotal" => $peso_bruto,
                        "undPesoTotal" => "KGM",
                        "fecTraslado" => $fact_fech_fact,
                        "transportista" => [
                            "tipoDoc" => $tipo_docu_transp,
                            "numDoc" => $trta_cid_trta,
                            "rznSocial" => $trta_nom_trta,
                            "nroMtc"=> $trta_num_mtc,
                            "placa"=> $guia_num_plac
                        ],
                        "llegada" => [
                            "ubigueo" => $ubigeo_destino,
                            "direccion" => $fact_dir_clie
                        ],
                        "partida" => [
                            "ubigueo" => $sucu_ubigeo,
                            "direccion" => $guia_dir_sucu
                        ],
                    ],
                );
            }


                //var_dump(json_encode($data));exit;

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
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/despatch/send?token=" . $this->empr_token_api);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);

                //print_r($respuesta);exit;


                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    //print_r($data_json);
                    //exit;
                    switch ($http_code) {
                        case 200:
                            $validacion = $data_json['sunatResponse']['success'];
                            $ticket     = $data_json['sunatResponse']['ticket'];
                            

                            if ($validacion == true || $data_json['sunatResponse']['error']['code'] == '1033') {
                                try {

                                    sleep(5);
                                    if(!empty($ticket) && empty($guia_num_tick)){
                                        $sql_update = "UPDATE saeguia SET guia_num_tick = '$ticket' WHERE guia_cod_guia = $guia_cod_guia;";
                                        $this->oIfx->QueryT($sql_update);
                                    }
                                    //VALIDAICON CUANDO YA EXISTE UN TICKET GENERADO
                                    if(!empty($guia_num_tick)){
                                        $ticket=$guia_num_tick;
                                    }

                                    $this->oIfx->QueryT('BEGIN;');


                                    //VALIDAR ACEPTACION Y OBTENER HASH 


                                    $gui_cod_hash = '';
                                    $curl = curl_init();

                                    curl_setopt_array($curl, array(
                                      CURLOPT_URL => $this->empr_ws_sri_url.'/despatch/status?ticket='.$ticket.'&ruc='.$this->empr_ruc_empr.'&token='. $this->empr_token_api,
                                      CURLOPT_RETURNTRANSFER => true,
                                      CURLOPT_ENCODING => '',
                                      CURLOPT_MAXREDIRS => 10,
                                      CURLOPT_TIMEOUT => 0,
                                      CURLOPT_FOLLOWLOCATION => true,
                                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                      CURLOPT_CUSTOMREQUEST => 'GET',
                                      CURLOPT_HTTPHEADER => array(
                                        'Content-Type: application/json'
                                      ),
                                    ));
                                    
                                    $response = curl_exec($curl);

                                    if (!curl_errno($curl)) {
                                        $http_codeg = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                                        $data_jsong = json_decode($response, true);

                    
                                        switch ($http_codeg) {
                                            case 200:
                                                $gui_cod_hash = $data_jsong['cdrResponse']['reference'];
                                                $codigo=$data_jsong['code'];


                                                if ($data_jsong['error']['code'] == '1033') {
                                                   
                                                    $sql_update = "UPDATE saeguia SET guia_erro_sri = 'El comprobante fue registrado previamente con otros datos' WHERE guia_cod_guia = $guia_cod_guia;";
                                                    $this->oIfx->QueryT($sql_update);
                                                    
                                                    $this->oIfx->QueryT('COMMIT;');
                                                    throw new Exception('El comprobante fue registrado previamente con otros datos');
                                                } elseif(strpos($data_jsong['cdrResponse']['description'], 'error') !== false) {

                                                    $erro=substr($data_jsong['cdrResponse']['description'],0,255);
                                                    
                                                    $sql_update = "UPDATE saeguia SET guia_erro_sri = '$erro' WHERE guia_cod_guia = $guia_cod_guia;";
                                                    $this->oIfx->QueryT($sql_update);
                                                    
                                                    $this->oIfx->QueryT('COMMIT;');
                                                    throw new Exception($data_jsong['cdrResponse']['description']);
                                                }
                                                else{
                                                    $result_txt = $data_jsong['cdrResponse']['description'];
                                                }
                                            break;
                                            default:
                                            throw new Exception("Error desconocido en el WebService, Consulte con el administrador");
                                        }

                                    }


                                    if($codigo==0){

                                        $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_envio . '-' . $fact_nse_fact . '-' . $fact_num_preimp;
                                        //$this->GenerarNotaCreditoXmlPdfPeru($headers, $nombre_documento, $data);
    
                                        $this->GenerarGuiasRemisionXmlPdfPeru($headers, $nombre_documento, $data);
    
                                        //FORMATO PERSONALIZADO - PERU
                                        $sql_update = "UPDATE saeguia SET guia_aprob_sri = 'S', guia_cod_hash='$gui_cod_hash' WHERE guia_cod_guia = $guia_cod_guia;";
                                        $this->oIfx->QueryT($sql_update);
                                        $this->oIfx->QueryT('COMMIT;');

    
                                      
    
                                        $ruta_xml = 'upload/xml/' . $nombre_documento . '.xml';
                                        $ruta_pdf = 'upload/pdf/gui_' . $nombre_documento . '.pdf';

                                        reporte_guia_personalizado($guia_cod_guia, $nombre_documento, $fact_cod_sucu, $ruta_pdf);
    
                                        $div_button = '<div class="btn-group" role="group" aria-label="...">
                                            <a href="upload/xml/gui_' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                                <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                            </a>
                                            <a href="upload/pdf/gui_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                                <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                            </a>
                                        </div>';
    
    
    
                                       
    
                                        $correoMsj = envio_correo_adj_sunat($fact_email_clpv, $ruta_xml, $ruta_pdf, $fact_nom_cliente, $nombre_documento, $serie, $fact_cod_clpv, $tipo_envio);
                                            $result = array(
                                                'div_button' => $div_button,
                                                'result_ws' => $result_txt,
                                                'result_email' => 'Autorizado (' . $correoMsj.$ticket . ')'
                                            );
                                        }
                                        else{
                                            $erro=substr($result_txt,0,255);
                                            $sql_update = "UPDATE saeguia SET guia_erro_sri = '$erro' WHERE guia_cod_guia = $guia_cod_guia;";
                                            $this->oIfx->QueryT($sql_update);
                                            $this->oIfx->QueryT('COMMIT;');

                                            $result = array(
                                                'div_button' => $div_button,
                                                'result_ws' => $result_txt,
                                                'result_email' => 'NO AUTORIZADO'
                                            );
                                        }
                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }
                            } else {
                                $error = "Codigo: " . $data_json['sunatResponse']['error']['code'] . "<br>";
                                $error .= "Error: " . $data_json['sunatResponse']['error']['message'] . "<br>";
                                $erro=substr($error,0,255);
                                try {

                                    $this->oIfx->QueryT('BEGIN;');
                                    $sql_update = "UPDATE saeguia SET guia_erro_sri = '$erro' WHERE guia_cod_guia = $guia_cod_guia;";
                                    $this->oIfx->QueryT($sql_update);
                                    $this->oIfx->QueryT('COMMIT;');

                                }
                                catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

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
            } else {
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
        curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/despatch/xml?token=" . $this->empr_token_api);
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

        $nombre = $nombre_documento . ".xml";
        $ruta_xml = $ruta . '/' . $nombre;
        $archivo = fopen($ruta_xml, "w+");
        fwrite($archivo, utf8_encode($data_xml));
        fclose($archivo);

        //GENERA PDF
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/despatch/pdf?token=" . $this->empr_token_api);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data_pdf = curl_exec($ch);

        $ruta_pdf = 'upload/pdf/gui_' . $nombre_documento . '.pdf';
        //        header('Content-Type: application/pdf');
        file_put_contents($ruta_pdf, $data_pdf);

        return true;
    }

    function GenerarXml($fact_cod_fact)
    {
        try {
            session_start();
            $id_usuario = $_SESSION['U_ID'];
            $idempresa = $_SESSION['U_EMPRESA'];

            //CONTROL FORMATOS PERSONALIZADOS
            $ctrl_formato = 0;
            $sql = "select ftrn_ubi_web from saeftrn  where ftrn_cod_empr=$idempresa and ftrn_des_ftrn = 'FACTURA' and ftrn_cod_modu=7 and (ftrn_ubi_web is not null or ftrn_ubi_web != '')";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $ubi =  $this->oIfx->f('ftrn_ubi_web');
                    if (!empty($ubi)) {
                        include_once('../../' . $ubi . '');
                        $ctrl_formato++;
                    }
                }
            }
            $this->oIfx->Free();

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
            fac.fact_val_tcam,
            fac.fact_cod_detra,
            fac.fact_cm2_fact
            FROM
	        saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
            WHERE fac.fact_cod_fact = $fact_cod_fact;";

            $fact_aprob_sri = 'N';
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $fact_nse_fact  = $this->oIfx->f('fact_nse_fact');
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
                        $fact_val_tcam = $this->oIfx->f('fact_val_tcam');
                        $fact_cod_detra = $this->oIfx->f('fact_cod_detra');
                        $fact_cm2_fact = $this->oIfx->f('fact_cm2_fact');
                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                        if ($fact_cod_detra > 0) {
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

                            if (empty($tret_cod_imp) || empty($tret_cod_banc)) {
                                throw new Exception("No tiene configurado el codigo de la detraccion o el numero de cuenta.");
                            }

                            $sql_tret = "SELECT ctab_num_ctab from saectab where ctab_cod_ctab = $tret_cod_banc and ctab_cod_empr = $idempresa";
                            if ($this->oCon->Query($sql_tret)) {
                                if ($this->oCon->NumFilas() > 0) {
                                    do {
                                        $ctab_num_ctab     = $this->oCon->f('ctab_num_ctab');
                                    } while ($this->oCon->SiguienteRegistro());
                                }
                            }
                            $this->oCon->Free();
                        }

                        if ($this->pcon_seg_mone == $fact_cod_mone) {
                            $fact_iva           = $fact_iva / $fact_val_tcam;
                            $fact_con_miva      = $fact_con_miva / $fact_val_tcam;
                            $fact_sin_miva      = $fact_sin_miva / $fact_val_tcam;
                            $fact_tot_fact      = $fact_tot_fact / $fact_val_tcam;
                            $fact_cm4_fact      = $fact_cm4_fact / $fact_val_tcam;
                            if ($fact_cod_detra > 0) {
                                $valor_detra_rest   = $valor_detra / $fact_val_tcam;
                                $valor_detra_rest=round($valor_detra_rest,0,PHP_ROUND_HALF_UP);
                            } else {
                                $valor_detra_rest   = 0;
                            }

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
                            if ($fact_cod_detra > 0) {
                                $valor_detra_rest   = $valor_detra;
                                $valor_detra_rest=round($valor_detra_rest,0,PHP_ROUND_HALF_UP);
                            } else {
                                $valor_detra_rest   = 0;
                            }
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
                        } else if (intval($tipo_doc) == 5) {
                            $tipo_docu = '4';
                            $tipo_envio = '03';
                        } else if (intval($tipo_doc) == 4) {
                            $tipo_docu = '0';
                            $tipo_envio = '03';
                        } else {
                            $tipo_docu = '0';
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            $sql = "SELECT mone_sgl_mone, mone_des_mone from saemone where mone_cod_mone = $fact_cod_mone ";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $mone_sgl_mone = $this->oIfx->f('mone_sgl_mone');
                        $mone_des_mone = $this->oIfx->f('mone_des_mone');
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            $V = new EnLetras();
            $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, $mone_des_mone));

            $fact_hor_ini = substr($fact_hor_ini, -8);
            $fact_hor_fin = substr($fact_hor_fin, -8);
            $fact_fech_fact = $fact_fech_fact . "T"  . "12:00:00-00:00";
            $fact_fech_venc = $fact_fech_venc . "T" . "12:00:00-00:00";
            $serie = $fact_nse_fact . '-' . $fact_num_preimp;
            $valor_venta = $fact_con_miva + $fact_sin_miva;
            $data = array(
                "ublVersion" => "2.1",
                "tipoOperacion" => "0101",
                "tipoDoc" => $tipo_envio,
                "serie" => $fact_nse_fact,
                "correlativo" => (int) $fact_num_preimp,
                "fechaEmision" => $fact_fech_fact,
                "formaPago" => [],
                "cuotas" => [],
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
                        "direccion" => $this->empr_dir_empr,
                        "ubigueo" => $this->empr_cpo_empr,
                        "departamento" => $this->prov_des_prov, // SAEPROV
                        "provincia" => $this->ciud_nom_ciud, // SAECANT O SAECIUD
                        "distrito" => $this->parr_des_parr // SAEPARR
                    ],
                    "email" => $this->empr_mai_empr,
                    "telephone" => $this->empr_tel_resp,
                ],
                "tipoMoneda" => $mone_sgl_mone,
                "sumOtrosCargos" => 0,
                "mtoOperGravadas" => $fact_con_miva,
                "mtoOperInafectas" => 0,
                "mtoOperExoneradas" => 0,
                "mtoOperGratuitas" => 0,
                "mtoIGV" => $fact_iva,
                "mtoIGVGratuitas" => 0,
                "valorVenta" => $valor_venta,
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

            $n_orden_compra = rtrim($fact_cm2_fact);

            if (strlen($n_orden_compra) > 0) {
                $data['compra'] = strval($n_orden_compra);
            }

            if ($fact_cod_detra > 0) {

                $valor_detra=round($valor_detra,0,PHP_ROUND_HALF_UP);
                $data['detraccion'] = [
                    "percent" => floatval(number_format($tret_porct, 4, '.', '')),
                    "mount" => floatval(number_format($valor_detra, 0, '.', '')),
                    "ctaBanco" => strval($ctab_num_ctab),
                    "codBienDetraccion" =>  strval($tret_cod_imp),
                    "codMedioPago" => "001"
                ];

                $data['tipoOperacion'] = "1001";

                $data['legends'][1] = [
                    "code" => "2006",
                    "value" => "OPERACION SUJETA A DETRACCION"
                ];
            }

            $sql = "SELECT
	                fx.fxfp_cod_fpag,
	                fx.fxfp_val_fxfp,
	                fx.fxfp_fec_fin,
                    fx.fxfp_cot_fpag
                    FROM
	                saefxfp fx
                    WHERE fx.fxfp_cod_fact = $fact_cod_fact;";
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
                            $tipo = 'Contado';
                        } else if ($tipo == 'CRE') {
                            $tipo = 'Credito';

                            $fxfp_fec_fin = $fact_fech_venc;

                            $data['cuotas'][$j++] = [
                                "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                "fechaPago" => $fxfp_fec_fin
                            ];
                        } else {
                            $tipo = 'Contado';
                        }

                        $data['formaPago'] = [
                            "moneda" => $mone_sgl_mone,
                            "tipo" => $tipo,
                            "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', ''))
                        ];
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            $sql = "SELECT dfac_cant_dfac, dfac_cod_prod, dfac_det_dfac, dfac_mont_total, dfac_por_iva, dfac_exc_iva, dfac_precio_dfac,dfac_obj_iva,dfac_des1_dfac FROM saedfac WHERE dfac_cod_fact = $fact_cod_fact";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $j = 0;
                    $igv_gratuitas=0;
                    $tot_op_gratuitas=0;
                    do {
                        $dfac_des1_dfac=0;
                        $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                        $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                        $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                        $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                        $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                        $dfac_exc_iva = $this->oIfx->f('dfac_exc_iva');
                        $dfac_obj_iva = $this->oIfx->f('dfac_obj_iva');
                        $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');
                        $dfac_des1_dfac = $this->oIfx->f('dfac_des1_dfac');

                        if ($this->pcon_seg_mone == $fact_cod_mone) {
                            $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                        }

                        if ($this->pcon_seg_mone == $fact_cod_mone) {
                            $dfac_precio_dfac  = $dfac_precio_dfac / $fact_val_tcam;
                        }

                        $valor_unitario=$dfac_precio_dfac;
                        if($dfac_des1_dfac > 0){
                            $por_descuento = $dfac_des1_dfac / 100;
                            $valor_descuento = $valor_unitario * $por_descuento;
                            $valor_unitario = $valor_unitario - $valor_descuento;
                        }

                        $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                        $precio_uni = $valor_unitario * $porcentaje_iva;
                        $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;

                        $tipAfeIgv = "10";
                        if ($dfac_exc_iva == 1) {
                            $tipAfeIgv = "20";
                            //$data['mtoImpVenta'] = floatval($dfac_por_iva);
                            $data['mtoOperExoneradas'] = floatval($fact_tot_fact);
                            $data['mtoOperGravadas'] = floatval($dfac_por_iva);
                        }
                        
                        ///OPERACIONES GRATUITAS
                        if ($dfac_obj_iva == 1) {
                            $igv_gratuitas+=$valor_iva;
                            $tipAfeIgv = "15";
                            //$data['tipoOperacion'] = "2106";
                            $tot_op_gratuitas+=$dfac_mont_total;
                            $precio_uni =0;
                            $valor_unitario=0;
                            $data['legends'][1] = [
                                "code" => "1002",
                                "value" => "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE"
                            ];
                        }

                        if (empty($dfac_det_dfac)) {
                            $dfac_det_dfac = 'Sin detalle';
                        }

                        //OPERACIONES GRATUITAS
                        if ($dfac_obj_iva == 1) {
                            $data['details'][$j++] =
                            [
                                "unidad" => "NIU",
                                "cantidad" => floatval($dfac_cant_dfac),
                                "codProducto" => $dfac_cod_prod,
                                "descripcion" => $dfac_det_dfac,
                                "mtoValorUnitario" => floatval(number_format($valor_unitario, 2, '.', '')),
                                "mtoValorGratuito" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                "tipAfeIgv" => $tipAfeIgv,
                                "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                            ];

                        }
                        else{
                            $data['details'][$j++] =
                            [
                                "unidad" => "NIU",
                                "cantidad" => floatval($dfac_cant_dfac),
                                "codProducto" => $dfac_cod_prod,
                                "descripcion" => $dfac_det_dfac,
                                "mtoValorUnitario" => floatval(number_format($valor_unitario, 2, '.', '')),
                                "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                "tipAfeIgv" => $tipAfeIgv,
                                "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                            ];    
                        }

                        
                    } while ($this->oIfx->SiguienteRegistro());
                    //VALIDACION OPERACIONES GRATUITAS
                    if(floatval($tot_op_gratuitas)>0){
                        $data['mtoOperGratuitas'] = floatval(number_format($tot_op_gratuitas, 2, '.', ''));
                        $data['mtoIGVGratuitas'] = floatval(number_format($igv_gratuitas, 2, '.', ''));
                    }
                }
            }
            $this->oIfx->Free();

            $data_json_envio = str_replace("&", "&amp;", json_encode($data));

            /**
             * EMPIEZA ENVIO
             */
            $headers = array(
                "Content-Type:application/json"
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/invoice/xml?token=" . $this->empr_token_api);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json_envio);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $respuesta = curl_exec($ch);


            $ruta_upload = "upload";
            $ruta = "upload/xml";

            if (!file_exists($ruta_upload)) {
                mkdir($ruta_upload, 0777);
            }

            if (!file_exists($ruta)) {
                mkdir($ruta, 0777);
            }

            $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_envio . '-' . $fact_nse_fact . '-' . $fact_num_preimp;

            $nombre = $nombre_documento . ".xml";
            $ruta_xml = $ruta . '/' . $nombre;
            $archivo = fopen($ruta_xml, "w+");
            //fwrite($archivo, utf8_encode($respuesta));
            fwrite($archivo, $respuesta);
            fclose($archivo);

            $respuesta = array(
                "ruta" => $ruta_xml,
                "nombre_archivo" => $nombre
            );

            return $respuesta;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }

    function GenerarXmlNc($ncre_cod_ncre)
    {
        try {

            session_start();
            $id_usuario = $_SESSION['U_ID'];

            $idempresa = $_SESSION['U_EMPRESA'];

            //CONTROL FORMATOS PERSONALIZADOS
            $ctrl_formato = 0;
            $sql = "select ftrn_ubi_web from saeftrn  where ftrn_cod_empr=$idempresa and ftrn_des_ftrn = 'FACTURA' and ftrn_cod_modu=7 and (ftrn_ubi_web is not null or ftrn_ubi_web != '')";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $ubi =  $this->oIfx->f('ftrn_ubi_web');
                    if (!empty($ubi)) {
                        include_once('../../' . $ubi . '');
                        $ctrl_formato++;
                    }
                }
            }
            $this->oIfx->Free();

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
	        fac.ncre_fech_fact as fact_hor_fin,
	        fac.ncre_cod_sucu as fact_cod_sucu,
	        cli.clv_con_clpv,
	        fac.ncre_aprob_sri as fact_aprob_sri,
	        fac.ncre_cod_fact as fact_cod_ndb, 
			fac.ncre_cod_aux as fact_aux_preimp,
			fac.ncre_fech_fact as fact_fec_emis_aux,
            fac.ncre_cod_mone as fact_cod_mone,
            fac.ncre_val_tcam as fact_val_tcam,
            fac.ncre_cm1_ncre as motivo_ncre
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
                        $fact_cod_mone = $this->oIfx->f('fact_cod_mone');
                        $fact_val_tcam = $this->oIfx->f('fact_val_tcam');
                        $motivo_ncre = $this->oIfx->f('motivo_ncre');

                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                        if ($this->pcon_seg_mone == $fact_cod_mone) {
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
                        } else if (intval($tipo_doc) == 5) {
                            $tipo_docu = '4';
                            $tipo_envio = '03';
                        } else if (intval($tipo_doc) == 4) {
                            $tipo_docu = '0';
                            $tipo_envio = '03';
                        } else {
                            $tipo_docu = '0';
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();


            $sql = "SELECT mone_sgl_mone, mone_des_mone from saemone where mone_cod_mone = $fact_cod_mone ";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $mone_sgl_mone = $this->oIfx->f('mone_sgl_mone');
                        $mone_des_mone = $this->oIfx->f('mone_des_mone');
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            $V = new EnLetras();
            $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, $mone_des_mone));

            $fact_fech_fact = $fact_fech_fact . "T"  . "12:00:00-00:00";
            $fact_fech_venc = $fact_fech_venc . "T" . "12:00:00-00:00";
            $serie = $fact_nse_fact . '-' . $fact_num_preimp;
            /**
             * EXPLODE FACTURA MODIFICA
             */

            if ($fact_cod_ndb) {

                $sql = "SELECT
	                fac.fact_nse_fact, fac.fact_num_preimp
                    FROM
	                saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
                    WHERE fac.fact_cod_fact = $fact_cod_ndb;";

                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        do {
                            $fact_num_preimp_afect = (int) $this->oIfx->f('fact_num_preimp');
                            $fact_nse_fact_afect = substr($this->oIfx->f('fact_nse_fact'), 3, 9);
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                $numDocfectado = "$fact_nse_fact_afect-$fact_num_preimp_afect";
            } else {
                if (strlen($fact_aux_preimp) >= 11 && $fact_cod_ndb > 0) {
                    $exp_fac = explode("-", $fact_aux_preimp);
                    $nse_fac = substr($exp_fac[0], 3, 9);
                    $num_fac = (int) $exp_fac[1];
                    $numDocfectado = "$nse_fac-$num_fac";
                } else {
                    $numDocfectado = $fact_aux_preimp;
                }
            }

            $tipo_doc = "07";

            if (!$fact_cm1_fact) {
                $fact_cm1_fact = "NOTA DE CREDITO AFECTA FACTURA $numDocfectado";
            }

            $valor_venta = $fact_con_miva + $fact_sin_miva;

            if (empty($motivo_ncre)) {
                throw new Exception("Sin motivo configurado en la nota de credito");
            }
            $sql = "SELECT ddev_tip_ddev, ddev_des_ddev from saeddev where ddev_des_ddev = '$motivo_ncre'";
            $motivo_envio = consulta_string($sql, 'ddev_tip_ddev', $this->oIfx, '');
            $motivo_envio_nombre = consulta_string($sql, 'ddev_des_ddev', $this->oIfx, '');
            
            $ctrl_cuotas='N';

            if ($fact_cod_ndb) {
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

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                            }

                            $fxfp_val_fxfp = $fxfp_val_fxfp - $valor_detra_rest;

                            if ($tipo == 'EFE') {
                                $tipo = 'Contado';
                            } else if ($tipo == 'CRE') {
                                $tipo = 'Credito';

                                $fxfp_fec_fin = $fact_fech_venc;
                                $ctrl_cuotas='S';
                            } else {
                                $tipo = 'Contado';
                            }
                            
                            
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();
            }


            $data = array(
                "ublVersion" => "2.1",
                "tipoDoc" => $tipo_doc,
                "serie" => $fact_nse_fact,
                "correlativo" => (int) $fact_num_preimp,
                "fechaEmision" => $fact_fech_fact,

                "tipDocAfectado" => $tipo_envio,
                "numDocfectado" => $numDocfectado,
                "codMotivo" => strval($motivo_envio),
                "desMotivo" => $fact_cm1_fact,
                //"cuotas" => [],
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
                        "direccion" => $this->empr_dir_empr,
                        "ubigueo" => $this->empr_cpo_empr,
                        "departamento" => $this->prov_des_prov, // SAEPROV
                        "provincia" => $this->ciud_nom_ciud, // SAECANT O SAECIUD
                        "distrito" => $this->parr_des_parr // SAEPARR
                    ],
                    "email" => $this->empr_mai_empr,
                    "telephone" => $this->empr_tel_resp,
                ],
                "tipoMoneda" => $mone_sgl_mone,
                "sumOtrosCargos" => 0,
                "mtoOperGravadas" => $fact_con_miva,
                "mtoOperInafectas" => 0,
                "mtoOperExoneradas" => 0,
                "mtoOperGratuitas" => 0,
                "mtoIGV" => $fact_iva,
                "mtoIGVGratuitas" => 0,
                "mtoIGV" => $fact_iva,
                "valorVenta" => $valor_venta,
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

            if($ctrl_cuotas=='S'){
                $data["cuotas"]=[];
            }

            if ($fact_cod_ndb) {
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

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                            }

                            $fxfp_val_fxfp = $fxfp_val_fxfp - $valor_detra_rest;

                            if ($tipo == 'EFE') {
                                $tipo = 'Contado';
                            } else if ($tipo == 'CRE') {
                                $tipo = 'Credito';

                                $fxfp_fec_fin = $fact_fech_venc;

                                $data['cuotas'][$j++] = [
                                    "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                    "fechaPago" => $fxfp_fec_fin
                                ];

                                $data['formaPago'] = [
                                    "moneda" => $mone_sgl_mone,
                                    "tipo" => $tipo,
                                    "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', ''))
                                ];
                            } else {
                                $tipo = 'Contado';
                            }
                            
                            
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();
            }

            $sql = "select 
                        dncr_cant_dfac as dfac_cant_dfac,
                        dncr_cod_prod as dfac_cod_prod,
                        dncr_det_dncr as dfac_det_dfac,
                        dncr_mont_total as dfac_mont_total,
                        dncr_por_iva as dfac_por_iva,
                        dncr_exc_iva as dfac_exc_iva,
                        dncr_obj_iva as dfac_obj_iva,
                        dncr_precio_dfac as  dfac_precio_dfac
                        from saedncr
                        where dncr_cod_ncre = $ncre_cod_ncre;";

            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $j = 0;
                    $igv_gratuitas=0;
                    $tot_op_gratuitas=0;
                    do {
                        $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                        $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                        $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                        $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                        $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                        $dfac_exc_iva = $this->oIfx->f('dfac_exc_iva');
                        $dfac_obj_iva = $this->oIfx->f('dfac_obj_iva');
                        $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');

                        if ($this->pcon_seg_mone == $fact_cod_mone) {
                            $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                        }

                        if ($this->pcon_seg_mone == $fact_cod_mone) {
                            $dfac_precio_dfac  = $dfac_precio_dfac / $fact_val_tcam;
                        }

                        $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                        $precio_uni = $dfac_precio_dfac * $porcentaje_iva;
                        $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;
                        

                        $tipAfeIgv = "10";
                        if ($dfac_exc_iva == 1) {
                            $tipAfeIgv = "20";
                            //$data['mtoImpVenta'] = floatval($dfac_por_iva);
                            $data['mtoOperExoneradas'] = floatval($fact_tot_fact);
                            $data['mtoOperGravadas'] = floatval($dfac_por_iva);
                        }
                        $valor_unitario=$dfac_precio_dfac;
                            ///OPERACIONES GRATUITAS
                            if ($dfac_obj_iva == 1) {
                                $igv_gratuitas+=$valor_iva;
                                $tipAfeIgv = "15";
                                //$data['tipoOperacion'] = "2106";
                                $tot_op_gratuitas+=$dfac_mont_total;
                                $precio_uni =0;
                                $valor_unitario=0;
                                $data['legends'][1] = [
                                    "code" => "1002",
                                    "value" => "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE"
                                ];
                            }

                        if (empty($dfac_det_dfac)) {
                            $dfac_det_dfac = 'Sin detalle';
                        }

                        //OPERACIONES GRATUITAS
                        if ($dfac_obj_iva == 1) {
                            $data['details'][$j++] =
                            [
                                "unidad" => "NIU",
                                "cantidad" => floatval($dfac_cant_dfac),
                                "codProducto" => $dfac_cod_prod,
                                "descripcion" => $dfac_det_dfac,
                                "mtoValorUnitario" => floatval(number_format($valor_unitario, 2, '.', '')),
                                "mtoValorGratuito" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                "tipAfeIgv" => $tipAfeIgv,
                                "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                            ];

                        }
                        else{
                            $data['details'][$j++] =
                            [
                                "unidad" => "NIU",
                                "cantidad" => floatval($dfac_cant_dfac),
                                "codProducto" => $dfac_cod_prod,
                                "descripcion" => $dfac_det_dfac,
                                "mtoValorUnitario" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                "tipAfeIgv" => $tipAfeIgv,
                                "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                            ];    
                        }

                        
                    } while ($this->oIfx->SiguienteRegistro());
                    //VALIDACION OPERACIONES GRATUITAS
                    if(floatval($tot_op_gratuitas)>0){
                        $data['mtoOperGratuitas'] = floatval(number_format($tot_op_gratuitas, 2, '.', ''));
                        $data['mtoIGVGratuitas'] = floatval(number_format($igv_gratuitas, 2, '.', ''));
                    }
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
            curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/note/xml?token=" . $this->empr_token_api);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $respuesta = curl_exec($ch);

            $ruta_upload = "upload";
            $ruta = "upload/xml";

            if (!file_exists($ruta_upload)) {
                mkdir($ruta_upload, 0777);
            }

            if (!file_exists($ruta)) {
                mkdir($ruta, 0777);
            }

            $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_doc . '-' . $fact_nse_fact . '-' . $fact_num_preimp;

            $nombre = $nombre_documento . ".xml";
            $ruta_xml = $ruta . '/' . $nombre;
            $archivo = fopen($ruta_xml, "w+");
            //fwrite($archivo, utf8_encode($respuesta));
            fwrite($archivo, $respuesta);
            fclose($archivo);

            $respuesta = array(
                "ruta" => $ruta_xml,
                "nombre_archivo" => $nombre
            );

            return $respuesta;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }

    function GenerarXmlNcComercial($ncre_cod_ncre)
    {
        try {

            session_start();
            $id_usuario = $_SESSION['U_ID'];

            $idempresa = $_SESSION['U_EMPRESA'];

            //CONTROL FORMATOS PERSONALIZADOS
            $ctrl_formato = 0;
            $sql = "select ftrn_ubi_web from saeftrn  where ftrn_cod_empr=$idempresa and ftrn_des_ftrn = 'FACTURA' and ftrn_cod_modu=7 and (ftrn_ubi_web is not null or ftrn_ubi_web != '')";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $ubi =  $this->oIfx->f('ftrn_ubi_web');
                    if (!empty($ubi)) {
                        include_once('../../' . $ubi . '');
                        $ctrl_formato++;
                    }
                }
            }
            $this->oIfx->Free();

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
	        fac.ncre_fech_fact as fact_hor_fin,
	        fac.ncre_cod_sucu as fact_cod_sucu,
	        cli.clv_con_clpv,
	        fac.ncre_aprob_sri as fact_aprob_sri,
	        fac.ncre_cod_fact as fact_cod_ndb, 
			fac.ncre_cod_aux as fact_aux_preimp,
			fac.ncre_fech_fact as fact_fec_emis_aux,
            fac.ncre_cod_mone as fact_cod_mone,
            fac.ncre_val_tcam as fact_val_tcam,
            fac.ncre_cm1_ncre as motivo_ncre
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
                        $fact_cod_mone = $this->oIfx->f('fact_cod_mone');
                        $fact_val_tcam = $this->oIfx->f('fact_val_tcam');
                        $motivo_ncre = $this->oIfx->f('motivo_ncre');

                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                        if ($this->pcon_seg_mone == $fact_cod_mone) {
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
                        } else if (intval($tipo_doc) == 5) {
                            $tipo_docu = '4';
                            $tipo_envio = '03';
                        } else if (intval($tipo_doc) == 4) {
                            $tipo_docu = '0';
                            $tipo_envio = '03';
                        } else {
                            $tipo_docu = '0';
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();


            $sql = "SELECT mone_sgl_mone, mone_des_mone from saemone where mone_cod_mone = $fact_cod_mone ";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $mone_sgl_mone = $this->oIfx->f('mone_sgl_mone');
                        $mone_des_mone = $this->oIfx->f('mone_des_mone');
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            $V = new EnLetras();
            $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, $mone_des_mone));

            $fact_fech_fact = $fact_fech_fact . "T"  . "12:00:00-00:00";
            $fact_fech_venc = $fact_fech_venc . "T" . "12:00:00-00:00";
            $serie = $fact_nse_fact . '-' . $fact_num_preimp;
            /**
             * EXPLODE FACTURA MODIFICA
             */

            if ($fact_cod_ndb) {

                $sql = "SELECT
	                fac.fact_nse_fact, fac.fact_num_preimp
                    FROM
	                saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
                    WHERE fac.fact_cod_fact = $fact_cod_ndb;";

                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        do {
                            $fact_num_preimp_afect = (int) $this->oIfx->f('fact_num_preimp');
                            $fact_nse_fact_afect = substr($this->oIfx->f('fact_nse_fact'), 3, 9);
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                $numDocfectado = "$fact_nse_fact_afect-$fact_num_preimp_afect";
            } else {
                if (strlen($fact_aux_preimp) >= 11 && $fact_cod_ndb > 0) {
                    $exp_fac = explode("-", $fact_aux_preimp);
                    $nse_fac = substr($exp_fac[0], 3, 9);
                    $num_fac = (int) $exp_fac[1];
                    $numDocfectado = "$nse_fac-$num_fac";
                } else {
                    $numDocfectado = $fact_aux_preimp;
                }
            }

            $tipo_doc = "07";

            if (!$fact_cm1_fact) {
                $fact_cm1_fact = "NOTA DE CREDITO AFECTA FACTURA $numDocfectado";
            }

            $valor_venta = $fact_con_miva + $fact_sin_miva;

            if (empty($motivo_ncre)) {
                throw new Exception("Sin motivo configurado en la nota de credito");
            }
            $sql = "SELECT ddev_tip_ddev, ddev_des_ddev from saeddev where ddev_des_ddev = '$motivo_ncre'";
            $motivo_envio = consulta_string($sql, 'ddev_tip_ddev', $this->oIfx, '');
            $motivo_envio_nombre = consulta_string($sql, 'ddev_des_ddev', $this->oIfx, '');

            $data = array(
                "ublVersion" => "2.1",
                "tipoDoc" => $tipo_doc,
                "serie" => $fact_nse_fact,
                "correlativo" => (int) $fact_num_preimp,
                "fechaEmision" => $fact_fech_fact,

                "tipDocAfectado" => $tipo_envio,
                "numDocfectado" => $numDocfectado,
                "codMotivo" => strval($motivo_envio),
                "desMotivo" => $fact_cm1_fact,
                //"cuotas" => [],
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
                        "direccion" => $this->empr_dir_empr,
                        "ubigueo" => $this->empr_cpo_empr,
                        "departamento" => $this->prov_des_prov, // SAEPROV
                        "provincia" => $this->ciud_nom_ciud, // SAECANT O SAECIUD
                        "distrito" => $this->parr_des_parr // SAEPARR
                    ],
                    "email" => $this->empr_mai_empr,
                    "telephone" => $this->empr_tel_resp,
                ],
                "tipoMoneda" => $mone_sgl_mone,
                "sumOtrosCargos" => 0,
                "mtoOperGravadas" => $fact_con_miva,
                "mtoOperInafectas" => 0,
                "mtoOperExoneradas" => 0,
                "mtoOperGratuitas" => 0,
                "mtoIGV" => $fact_iva,
                "mtoIGVGratuitas" => 0,
                "mtoIGV" => $fact_iva,
                "valorVenta" => $valor_venta,
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

            if ($fact_cod_ndb) {
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

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                            }

                            $fxfp_val_fxfp = $fxfp_val_fxfp - $valor_detra_rest;

                            if ($tipo == 'EFE') {
                                $tipo = 'Contado';
                            } else if ($tipo == 'CRE') {
                                $tipo = 'Credito';

                                $fxfp_fec_fin = $fact_fech_venc;

                                /*$data['cuotas'][$j++] = [
                                    "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                    "fechaPago" => $fxfp_fec_fin
                                ];*/
                            } else {
                                $tipo = 'Contado';
                            }
                            
                            /*$data['formaPago'] = [
                                "moneda" => $mone_sgl_mone,
                                "tipo" => $tipo,
                                "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', ''))
                            ];*/
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();
            }

            $sql = "select 
                        dncr_cant_dfac as dfac_cant_dfac,
                        dncr_cod_prod as dfac_cod_prod,
                        dncr_det_dncr as dfac_det_dfac,
                        dncr_mont_total as dfac_mont_total,
                        dncr_por_iva as dfac_por_iva,
                        dncr_exc_iva as dfac_exc_iva,
                        dncr_obj_iva as dfac_obj_iva,
                        dncr_precio_dfac as  dfac_precio_dfac
                        from saedncr
                        where dncr_cod_ncre = $ncre_cod_ncre;";

            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $j = 0;
                    $igv_gratuitas=0;
                    $tot_op_gratuitas=0;
                    do {
                        $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                        $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                        $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                        $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                        $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                        $dfac_exc_iva = $this->oIfx->f('dfac_exc_iva');
                        $dfac_obj_iva = $this->oIfx->f('dfac_obj_iva');
                        $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');

                        if ($this->pcon_seg_mone == $fact_cod_mone) {
                            $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                        }

                        if ($this->pcon_seg_mone == $fact_cod_mone) {
                            $dfac_precio_dfac  = $dfac_precio_dfac / $fact_val_tcam;
                        }

                        $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                        $precio_uni = $dfac_precio_dfac * $porcentaje_iva;
                        $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;
                        

                        $tipAfeIgv = "10";
                        if ($dfac_exc_iva == 1) {
                            $tipAfeIgv = "20";
                            //$data['mtoImpVenta'] = floatval($dfac_por_iva);
                            $data['mtoOperExoneradas'] = floatval($fact_tot_fact);
                            $data['mtoOperGravadas'] = floatval($dfac_por_iva);
                        }
                        $valor_unitario=$dfac_precio_dfac;
                            ///OPERACIONES GRATUITAS
                            if ($dfac_obj_iva == 1) {
                                $igv_gratuitas+=$valor_iva;
                                $tipAfeIgv = "15";
                                //$data['tipoOperacion'] = "2106";
                                $tot_op_gratuitas+=$dfac_mont_total;
                                $precio_uni =0;
                                $valor_unitario=0;
                                $data['legends'][1] = [
                                    "code" => "1002",
                                    "value" => "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE"
                                ];
                            }

                        if (empty($dfac_det_dfac)) {
                            $dfac_det_dfac = 'Sin detalle';
                        }

                        //OPERACIONES GRATUITAS
                        if ($dfac_obj_iva == 1) {
                            $data['details'][$j++] =
                            [
                                "unidad" => "NIU",
                                "cantidad" => floatval($dfac_cant_dfac),
                                "codProducto" => $dfac_cod_prod,
                                "descripcion" => $dfac_det_dfac,
                                "mtoValorUnitario" => floatval(number_format($valor_unitario, 2, '.', '')),
                                "mtoValorGratuito" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                "tipAfeIgv" => $tipAfeIgv,
                                "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                            ];

                        }
                        else{
                            $data['details'][$j++] =
                            [
                                "unidad" => "NIU",
                                "cantidad" => floatval($dfac_cant_dfac),
                                "codProducto" => $dfac_cod_prod,
                                "descripcion" => $dfac_det_dfac,
                                "mtoValorUnitario" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                "igv" => floatval(number_format($valor_iva, 2, '.', '')),
                                "tipAfeIgv" => $tipAfeIgv,
                                "totalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                "mtoPrecioUnitario" => floatval(number_format($precio_uni, 2, '.', '')),
                                "mtoValorVenta" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "mtoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                "porcentajeIgv" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                            ];    
                        }

                        
                    } while ($this->oIfx->SiguienteRegistro());
                    //VALIDACION OPERACIONES GRATUITAS
                    if(floatval($tot_op_gratuitas)>0){
                        $data['mtoOperGratuitas'] = floatval(number_format($tot_op_gratuitas, 2, '.', ''));
                        $data['mtoIGVGratuitas'] = floatval(number_format($igv_gratuitas, 2, '.', ''));
                    }
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
            curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url . "/note/xml?token=" . $this->empr_token_api);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $respuesta = curl_exec($ch);

            $ruta_upload = "upload";
            $ruta = "upload/xml";

            if (!file_exists($ruta_upload)) {
                mkdir($ruta_upload, 0777);
            }

            if (!file_exists($ruta)) {
                mkdir($ruta, 0777);
            }

            $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_doc . '-' . $fact_nse_fact . '-' . $fact_num_preimp;

            $nombre = $nombre_documento . ".xml";
            $ruta_xml = $ruta . '/' . $nombre;
            $archivo = fopen($ruta_xml, "w+");
            //fwrite($archivo, utf8_encode($respuesta));
            fwrite($archivo, $respuesta);
            fclose($archivo);

            $respuesta = array(
                "ruta" => $ruta_xml,
                "nombre_archivo" => $nombre
            );

            return $respuesta;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }
}
