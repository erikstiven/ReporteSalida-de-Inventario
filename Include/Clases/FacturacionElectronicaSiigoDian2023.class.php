<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class FacturacionElectronicaSiigoDian2023
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

    public $date_token_request = '';
    public $date_token_expires = '';

    public $empr_siigo_partnerid = '';
    public $empr_siigo_sn = '';
    public $empr_siigo_ambiente = '';

    public $empr_siigo_autoenvio = '';
    public $empr_siigo_api_url = '';
    public $empr_siigo_username = '';
    public $empr_siigo_access_token = '';
    public $empr_siigo_autoenvio_mail = '';
    public $nombre_integracion = 'SIIGO';
    public $id_integracion;
    public $token_jwt;
    public $token_type;
    public $ncre_tdoc_siigo_id;
    public $fact_tdoc_siigo_id;

    /* datos exclusivos por empresa TEST */

    // public $siigo_active_ncre_id_FEGC = '28308'; //test id alem
    // public $siigo_active_ncre_id_INS = '28308';//test id_alem

    // public $siigo_active_ncre_FEGC_prefix = 'FEGC';  
    // public $siigo_active_ncre_INS_prefix = 'INS'; 

    // public $siigo_active_fact_id_FEGC = '28306'; //test id alem
    // public $siigo_active_fact_id_INS = '28307';//test id_alem

    // public $siigo_active_fact_FEGC_prefix = 'FEGC';
    // public $siigo_active_fact_INS_prefix = 'INS';

    // public $id_retencion = 19186;// autoretencion 2.2% siigo (global colombia)


    /* datos exclusivos por empresa PROD */


    public $siigo_active_ncre_id_FEGC = '28219';
    public $siigo_active_ncre_id_INS = '28207'; 

    public $siigo_active_ncre_FEGC_prefix = 'FEGC';  
    public $siigo_active_ncre_INS_prefix = 'INS';  


    public $siigo_active_fact_id_FEGC = '28214';
    public $siigo_active_fact_id_INS = '28206';

    public $siigo_active_fact_FEGC_prefix = 'FEGC';
    public $siigo_active_fact_INS_prefix = 'INS';

    public $id_retencion = 18116;// autoretencion 2.2% siigo (global colombia)


    function __construct($oIfx, $oCon, $idempresa)
    {
        $this->oIfx = $oIfx;
        $this->oCon = $oCon;

        $sql = "SELECT  
                    empr_ruc_empr, 
                    empr_nom_empr, 
                    empr_dir_empr, 
                    empr_mai_empr, 
                    empr_tel_resp, 
                    empr_token_api, 
                    empr_cod_empr, 
                    empr_siigo_sn,
                    empr_siigo_autoenvio,
                    empr_siigo_api_url,
                    empr_siigo_username, 
                    empr_siigo_access_token,
                    empr_siigo_partnerid,
                    empr_siigo_autoenvio_mail
                FROM saeempr 
                WHERE empr_cod_empr = $idempresa";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $this->empr_cod_empr                = $oCon->f('empr_cod_empr');
                    $this->empr_ruc_empr                = $oCon->f('empr_ruc_empr');
                    $this->empr_nom_empr                = $oCon->f('empr_nom_empr');
                    $this->empr_dir_empr                = $oCon->f('empr_dir_empr');
                    $this->empr_mai_empr                = $oCon->f('empr_mai_empr');
                    $this->empr_tel_resp                = $oCon->f('empr_tel_resp');
                    $this->empr_token_api               = $oCon->f('empr_token_api');
                    $this->empr_siigo_sn                = trim($oCon->f('empr_siigo_sn'));
                    $this->empr_siigo_ambiente          = trim($oCon->f('empr_siigo_ambiente'));
                    $this->empr_siigo_autoenvio         = trim($oCon->f('empr_siigo_autoenvio'));
                    $this->empr_siigo_api_url           = trim($oCon->f('empr_siigo_api_url'));
                    $this->empr_siigo_username          = trim($oCon->f('empr_siigo_username'));
                    $this->empr_siigo_access_token      = trim($oCon->f('empr_siigo_access_token'));
                    $this->empr_siigo_partnerid         = trim($oCon->f('empr_siigo_partnerid'));
                    $this->empr_siigo_autoenvio_mail    = trim($oCon->f('empr_siigo_autoenvio_mail'));
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $this->tabla_integraciones();//crea la tabla si no existe
        $this->registrar_integracion($this->empr_cod_empr,$this->empr_siigo_sn,$this->nombre_integracion,$this->empr_siigo_ambiente,$this->empr_siigo_api_url,'','','',$this->empr_siigo_username,$this->empr_siigo_access_token,$this->empr_siigo_access_token,'','','');

    }

    /*** FACTURAS ENVIO A LA SIIGO*/
    function EnviarFacturaColombia($fact_cod_fact)
    {
        try {

            $this->fact_tdoc_siigo_id = '';

            $sql = "SELECT
            DISTINCT
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
	        cli.clpv_ruc_clpv,
	        cli.clpv_cod_clpv,
            cli.clpv_siigo_id,
            ccli.siigo_id,
	        fac.fact_aprob_sri,
	        fac.fact_cod_contr,
            fac.fact_nau_fact,
            vend_cod_siigo,
            vend_cod_vend,
            fact_cod_ftrn,
            tcmp_siigo_id, 
            tcmp_siigo_name,
            fact_cm7_fac,
            case 
            when (fact_cod_contr::text is null or trim(fact_cod_contr::text) = '') and fact_cm7_fac is null then 'FCOMERCIAL' 
            when fact_cod_contr = 0  and fact_cm7_fac is not null then 'FISP' 
            when fact_cod_contr = 0  and fact_cm7_fac is  null then 'FISP' 
            when fact_cod_contr > 0  and fact_cm7_fac is not null then 'FISP' 
            when fact_cod_contr > 0  and fact_cm7_fac is  null then 'FISP' 
            ELSE 'FCOMERCIAL'  END fact_com_isp,

            case 
            when trim(SUBSTRING (fact_nse_fact, 4)) = '".$this->siigo_active_fact_FEGC_prefix."' then '".$this->siigo_active_fact_id_FEGC."'
            when trim(SUBSTRING (fact_nse_fact, 4)) = '".$this->siigo_active_fact_INS_prefix."' then '".$this->siigo_active_fact_id_INS."' 
            else '' end as fact_tdoc_siigo_id

            FROM
	        saefact fac 
            LEFT join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
            LEFT join isp.contrato_clpv ccli on fac.fact_cod_clpv = ccli.id_clpv
            LEFT join saevend vend on fac.fact_cod_vend = vend.vend_cod_vend
            LEFT JOIN saetcmp tcmp on tcmp.tcmp_cod_tcmp = fac.fact_tip_vent
            LEFT JOIN saeemifa ON tcmp_cod_tcmp = emifa_tip_emifa AND emifa_est_emifa = 'S'
            WHERE 
            emifa_est_emifa = 'S' AND 
            fac.fact_cod_fact = '$fact_cod_fact';";

            // print_r($sql);exit;
            $fact_cod_sucu = 0;

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

                        $vend_cod_siigo = $this->oIfx->f("vend_cod_siigo");
                        $vend_cod_vend = $this->oIfx->f("vend_cod_vend");            
                        $fact_cod_ftrn = $this->oIfx->f("fact_cod_ftrn"); 
                        $clpv_siigo_id = $this->oIfx->f("clpv_siigo_id");                        
                        $siigo_id       = $this->oIfx->f("siigo_id");                        
                        $clpv_ruc_clpv   = $this->oIfx->f("clpv_ruc_clpv");                        
                        $clpv_cod_clpv   = $this->oIfx->f("clpv_cod_clpv");                        
                        $fact_com_isp   = $this->oIfx->f("fact_com_isp");                        
                        $tcmp_siigo_id   = $this->oIfx->f("tcmp_siigo_id");                        
                        $tcmp_siigo_name   = $this->oIfx->f("tcmp_siigo_name");                        
                        $this->fact_tdoc_siigo_id   = $this->oIfx->f("fact_tdoc_siigo_id");                        
                        

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
                throw new Exception("fact_cod_contr no encontrado", 1);
                
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

            $siigo_api_url          = $this->empr_siigo_api_url;
            $siigo_username         = $this->empr_siigo_username;
            $siigo_access_token     = $this->empr_siigo_access_token;
            $empr_siigo_autoenvio   = $this->empr_siigo_autoenvio;



            if (empty($siigo_api_url)) {
                // throw new Exception("No se encuentra configurado dataico_id");
                throw new Exception("No se encuentra configurado SIIGO API URL");
            }

            if (empty($siigo_username)) {
                // throw new Exception("No se encuentra configurado dataico_id");
                throw new Exception("No se encuentra configurado SIIGO USERNAME");
            }

            if (empty($siigo_access_token)) {
                // throw new Exception("No se encuentra configurado dataico_token");
                throw new Exception("No se encuentra configurado SIIGO token");
            }

            $regimen = 'ORDINARIO';

            $departamento = $depar_nom;
            $ciudad = $ciud_nom;

            // $fact_num_preimp = intval($fact_num_preimp);
            $fact_num_preimp = ltrim($fact_num_preimp, '+0');

            $fact_fech_fact_origin = $fact_fech_fact;

            // $fact_fech_fact_origin = date("Y-m-d");

            // $fact_fech_fact = date("d/m/Y", strtotime($fact_fech_fact));

            $fecha_hora = $fact_fech_fact . ' ' . $fact_hor_ini;

            if (!$fact_email_clpv) {
                // throw new Exception("No se encontro email");
            }

            if (!$fact_tlf_cliente) {
                // throw new Exception("No se encontro el telefono");
            }

            if (!$fact_ruc_clie) {
                throw new Exception("No se encontro la identificacion");
            }

            $ambiente = ambienteEmisionSri($this->oCon, $this->empr_cod_empr, $fact_cod_sucu, 1);
            $tipo_ambiente = "PRUEBAS";
            if ($ambiente == 2) {
                $tipo_ambiente = "PRODUCCION";
            }

            // $description = $fact_cm1_fact?$fact_cm1_fact:"Factura ".$fact_nse_fact.'-'.$fact_num_preimp;

            $sql = "SELECT
                        fx.fxfp_cod_fact,
                        fx.fxfp_cod_fpag,
                        round(fx.fxfp_val_fxfp,2) as fxfp_val_fxfp,
                        fx.fxfp_fec_fin,
                        fxfp_fec_fxfp,
                        fx.fxfp_cot_fpag,
                        fpag_siigo_id,
                        fpag_siigo_name
                    FROM saefxfp fx 
                    left join saefpag on fpag_cod_fpag = fx.fxfp_cod_fpag
                    WHERE fx.fxfp_cod_fact = $fact_cod_fact ;";

            $fact_payment_indi = '';
            $fact_payment_indi_array = array();
            $fxfp_cont = 0;
            $valor_detra_rest = $valor_detra_rest?$valor_detra_rest:0;
            $fact_val_tcam = $fact_val_tcam?$fact_val_tcam:1;

            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $j = 0;
                    do {
                        $fxfp_cod_fpag  = $this->oIfx->f('fxfp_cod_fpag');
                        $fxfp_val_fxfp  = $this->oIfx->f('fxfp_val_fxfp');
                        $fxfp_fec_fin   = $this->oIfx->f('fxfp_fec_fin');
                        $fxfp_fec_fxfp  = $this->oIfx->f('fxfp_fec_fxfp');
                        $tipo           = $this->oIfx->f('fxfp_cot_fpag');
                        $fpag_siigo_id  = $this->oIfx->f('fpag_siigo_id');

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
                        } else {
                            $tipo_fpag = 'CASH';
                            $tipo_2 = 'DEBITO';
                        }

                        array_push(
                            $fact_payment_indi_array,
                            array(
                                "id"=> "$fpag_siigo_id",
                                "value"=> "$fxfp_val_fxfp",
                                "due_date"=> "$fxfp_fec_fin"
                            )
                        );

                        $fxfp_cont++;

                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();


            $array_dfac = array();
            $fact_item_indi = '';
            $fact_item_array = array();
            $dfac_det_dfac_full = '';
            $sql = "SELECT 
                        dfac_precio_dfac,
                        dfac_cant_dfac, 
                        dfac_cod_prod, 
                        dfac_det_dfac, 
                        dfac_mont_total, 
                        dfac_por_iva,
                        (dfac_des1_dfac+dfac_des2_dfac+dfac_des3_dfac+dfac_des4_dfac) as descuento_entero,
                        (((dfac_des1_dfac+dfac_des2_dfac+dfac_des3_dfac+dfac_des4_dfac)/100)) as descuento_porcentaje,
                        (((dfac_cant_dfac*dfac_precio_dfac)*(dfac_des1_dfac+dfac_des2_dfac+dfac_des3_dfac+dfac_des4_dfac)/100)) as descuento_valor,
                        ((dfac_cant_dfac*dfac_precio_dfac)- ((dfac_cant_dfac*dfac_precio_dfac)*(dfac_des1_dfac+dfac_des2_dfac+dfac_des3_dfac+dfac_des4_dfac)/100)) as total  
                    FROM saedfac WHERE dfac_cod_fact = $fact_cod_fact";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $j = 0;
                    do {
                        $dfac_por_iva = '';
                        $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                        $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');

                        $dfac_det_dfac_full .= $j>0?', ':'';
                        $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                        $dfac_det_dfac_full .= $dfac_det_dfac;


                        $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                        $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                        $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');
                        $dfac_por_iva = intval($this->oIfx->f('dfac_por_iva'));

                        $dfac_descuento_entero = ($this->oIfx->f('descuento_entero'));
                        $dfac_descuento_porcentaje = ($this->oIfx->f('descuento_porcentaje'));
                        $dfac_descuento_valor = ($this->oIfx->f('descuento_valor'));
                        $dfac_total = ($this->oIfx->f('total'));

                        $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                        $precio_uni = $dfac_mont_total * $porcentaje_iva;


                        $precio_uni_indi = $dfac_mont_total / $dfac_cant_dfac;


                        $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;

                        if (empty($dfac_det_dfac)) {
                            $dfac_det_dfac = 'Sin detalle';
                        }
                        $id_impuesto = 0;

                        $tax_id = '';
                        array_push(
                            $fact_item_array,
                            array(
                                "code"=> "$dfac_cod_prod",
                                "description"=> "$dfac_det_dfac",
                                "quantity"=> intval($dfac_cant_dfac),
                                "price"=> $precio_uni_indi
                            )
                        );

                        $dfac_cont++;

                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();
            $fact_json = '';

            if(empty($tcmp_siigo_id)){
                throw new Exception("tcmp siigo no definido");
            }
            

            if(empty($vend_cod_siigo)){
                throw new Exception("Usuario/Vendedor No registrado en Siigo ($vend_cod_vend)");
            }

            if(empty($this->fact_tdoc_siigo_id)){
                throw new Exception("id tipo de documento No registrado en Siigo ($fact_nse_fact)");
            }

            
            $fact_value_data            = $this->forzar_tot_fxfp_dfac_mont_tot($fact_payment_indi_array, $fact_item_array,$fact_cod_sucu,$fact_tot_fact);

            // throw new Exception("dede: ".json_encode($temp_convetion,true)."");

            $fact_payment_indi_array    = $fact_value_data['fxfp_array']?$fact_value_data['fxfp_array']:$fact_payment_indi_array;
            $fact_item_array            = $fact_value_data['dfac_array']?$fact_value_data['dfac_array']:$fact_item_array;

            $tot_fxfp_forced   	    = $fact_value_data['tot_fxfp'];
            $tot_dfac_forced   	    = $fact_value_data['tot_dfac'];

            

            // $fact_moneda = '"currency": {
            //     "code": "EUR",
            //     "exchange_rate": 3000
            // }';

            // $fact_descuento = '"global_discounts": [
            //     {
            //         "id": 1,
            //         "percentage": 10
            //     }
            // ]';

            // $fact_cargos_adicionales = '"global_charges": [
            //     {
            //         "id": 2,
            //         "value": 100
            //     }
            // ]';


            $empr_siigo_autoenvio_mail = $this->empr_siigo_autoenvio_mail?$this->empr_siigo_autoenvio_mail:'N';



            $array_data = array(
                "document"=>array(
                    "id"=> $this->fact_tdoc_siigo_id
                ),
                "date"=>"$fact_fech_fact",
                "customer"=>array(
                    "identification"=>"$fact_ruc_clie",
                    "branch_office"=>"0"
                ),
                "seller"=>$vend_cod_siigo,
                "number"=>"$fact_num_preimp",
                "items"=>$fact_item_array,
                "payments"=>$fact_payment_indi_array,
                "prefix"=>"$fact_nse_fact",
                "stamp"=>array(
                    "send"=>$empr_siigo_autoenvio=='S'?true:false
                ),
                "mail"=>array(
                    "send"=>$empr_siigo_autoenvio_mail=='S'?true:false

                ),
                "retentions"=>array(
                    array(
                        "id"=> $this->id_retencion
                    )
                ),
                "observations"=>"$fact_cm1_fact"
            );
        
            

            $json_full = json_encode($array_data);

            // print_r($json_full);exit;
            // throw new Exception("$json_full",1);


            // if ($tot_fxfp_forced != $tot_dfac_forced || $tot_fxfp_forced != $fact_tot_fact || $tot_dfac_forced != $fact_tot_fact){
            //     throw new Exception("Revisar el total del encabezado y el total total de la forma de pago 
            //     <br>total factura:  $fact_tot_fact 
            //     <br>total detalle:  $tot_dfac_forced 
            //     <br>total forma pago:  $tot_fxfp_forced 
            //     <br> <br>sent: $json_full", 1);                
            // }
       
            

            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                /**
                 * EMPIEZA ENVIO
                 */
                // $data = $json_full;
                $body = $json_full;


                $endpoint = '/v1/invoices';
                $method = 'POST';
                $result = $this->request_siigo($endpoint,$method,$body);                
                $http_code = intval($result['http_code']);
                $data = $result['data'];
                $mensaje = $result['mensaje'];
                $respuesta = json_encode($data,true);

                
                

                if($http_code>=200 && $http_code<300){

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

                    $data_json = json_decode($respuesta, true);

                    $data_json = (object)$data_json;


                    // $pdf_url = "https://api.siigo.com/v1/";
                    $status = 'SIIGO_NO_ENVIADO';


                    switch ($http_code) {
                        case 200:

                            $status = 'SIIGO_ACEPTADO';

                            $id_inv_siigo = ($data_json->id);

                            $pdf_url .=$id_inv_siigo;

                            $document_id = ($data_json->document['id']);

                            $prefix = ($data_json->prefix);
                            $number = ($data_json->number);

                            $date = trim($data_json->date);
                            $name = trim($data_json->name);
                            $uid = $id_inv_siigo;

                            $num_factura = $name;

                            $customer_id = ($data_json->customer['id']);
                            $customer_identification = ($data_json->customer['identification']);
                            $customer_branch_office = ($data_json->customer['branch_office']);


                            $seller = $data_json->seller;
                            $total = $data_json->total;
                            $balance = $data_json->balance;

                            $items = $data_json->items;
                            $items_id = $items[0]['id'];
                            $items_quantity = $items[0]['quantity'];
                            $items_price = $items[0]['price'];
                            $items_description = $items[0]['description'];
                            $items_taxes = $items[0]['taxes'];

                            $items_taxes_id = $items_taxes[0]['id'];
                            $items_taxes_name = $items_taxes[0]['name'];
                            $items_taxes_type = $items_taxes[0]['type'];
                            $items_taxes_percentage = $items_taxes[0]['percentage'];

                            $payments_id = $data_json->payments['id'];
                            $payments_name = $data_json->payments['name'];
                            $payments_value = $data_json->payments['value'];

                            $mail_status = $data_json->mail['status'];
                            $mail_observations = $data_json->mail['observations'];
                            $email_status = $mail_observations;

                            $metadata_created = $data_json->metadata['created'];

                            /**
                             * PDF
                             */

                             $ruta_pdf_save = $ruta_pdf;
                             $nombre_archivo = 'fac_'. $fact_cod_fact;

                             $formato = 'pdf'; 
                             $endpoint = '/v1/invoices'.'/'.$id_inv_siigo.'/'.$formato;
                             $this->save_base64_pdf($endpoint,$ruta_pdf_save,$nombre_archivo,$formato);
 
                             /**
                              * XML
                              */
                             
                             $ruta_xml_save = $ruta_xml;
                             $formato = 'xml';
                             $endpoint = '/v1/invoices'.'/'.$id_inv_siigo.'/'.$formato;
                             $this->save_base64_pdf($endpoint,$ruta_xml_save,$nombre_archivo,$formato);



                            $nombre_documento = $nombre_archivo;

                            // $div_button .= '<div class="btn-group" role="group">
                            //             <a href="upload/xml/fac_' . $fact_cod_fact . '.xml" download="' . $nombre_documento . '.xml">
                            //                 <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                            //             </a>';
                            $div_button .= '<a href="upload/pdf/fac_' . $fact_cod_fact . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>';
                            // $div_button .= '</div>';


                            if ($status == 'SIIGO_ACEPTADO') {
                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saefact SET fact_aprob_sri = 'S', fact_auto_sri = '$uid'
                                                   WHERE fact_cod_fact = $fact_cod_fact ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "SIIGO_ACEPTADO $num_factura : $id_inv_siigo",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else if ($status == 'SIIGO_NO_ENVIADO') {

                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saefact SET fact_aprob_sri = 'N', fact_auto_sri = '$uid'
                                                   WHERE fact_cod_fact = $fact_cod_fact ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');

                                    // update siigo
                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saefact SET 
                                                    fact_siigo_id = '$id_inv_siigo', 
                                                    fact_siigo_prefix = '$prefix', 
                                                    fact_siigo_number = '$number',
                                                    fact_siigo_name = '$name',
                                                    fact_siigo_date = '$date',

                                                    fact_siigo_envio = '$body',
                                                    fact_siigo_respuesta = '$respuesta'
                                                   WHERE fact_cod_fact = $fact_cod_fact ;";

                                    // print_r($sql_update);exit;
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');



                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "SIIGO_NO_ENVIADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else {
                                $error_array = trim($data_json->errors->error);
                                $error = 'Error ' . $status . ' ' . $error_array[3] .' '. $contr_ident;
                                throw new Exception($error);
                            }

                            break;
                        case 201:
                            $status = 'SIIGO_ACEPTADO';

                            $id_inv_siigo = ($data_json->id);

                            $pdf_url .=$id_inv_siigo;

                            $document_id = ($data_json->document['id']);

                            $prefix = ($data_json->prefix);
                            $number = ($data_json->number);

                            $date = trim($data_json->date);
                            $name = trim($data_json->name);
                            $uid = $id_inv_siigo;

                            $num_factura = $name;

                            $customer_id = ($data_json->customer['id']);
                            $customer_identification = ($data_json->customer['identification']);
                            $customer_branch_office = ($data_json->customer['branch_office']);


                            $seller = $data_json->seller;
                            $total = $data_json->total;
                            $balance = $data_json->balance;

                            $items = $data_json->items;
                            $items_id = $items[0]['id'];
                            $items_quantity = $items[0]['quantity'];
                            $items_price = $items[0]['price'];
                            $items_description = $items[0]['description'];
                            $items_taxes = $items[0]['taxes'];

                            $items_taxes_id = $items_taxes[0]['id'];
                            $items_taxes_name = $items_taxes[0]['name'];
                            $items_taxes_type = $items_taxes[0]['type'];
                            $items_taxes_percentage = $items_taxes[0]['percentage'];

                            $payments_id = $data_json->payments['id'];
                            $payments_name = $data_json->payments['name'];
                            $payments_value = $data_json->payments['value'];

                            $mail_status = $data_json->mail['status'];
                            $mail_observations = $data_json->mail['observations'];
                            $email_status = $mail_observations;

                            $metadata_created = $data_json->metadata['created'];

                            
                            /**
                             * PDF
                             */

                             $ruta_pdf_save = $ruta_pdf;
                             $nombre_archivo = 'fac_' . $fact_cod_fact;

                             $formato = 'pdf'; 
                            $endpoint = '/v1/invoices'.'/'.$id_inv_siigo.'/'.$formato;

                             $this->save_base64_pdf($endpoint,$ruta_pdf_save,$nombre_archivo,$formato);
 
                             /**
                              * XML
                              */
                             
                             $ruta_xml_save = $ruta_xml;
                             $formato = 'xml';
                            $endpoint = '/v1/invoices'.'/'.$id_inv_siigo.'/'.$formato;

                             $this->save_base64_pdf($endpoint,$ruta_xml_save,$nombre_archivo,$formato);


                            $nombre_documento = $nombre_archivo;

                            // $div_button .= '<div class="btn-group" role="group">
                            //             <a href="upload/xml/fac_' . $fact_cod_fact . '.xml" download="' . $nombre_documento . '.xml">
                            //                 <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                            //             </a>';
                            $div_button .='<a href="upload/pdf/fac_' . $fact_cod_fact . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>';
                            // $div_button .= '</div>';


                            if ($status == 'SIIGO_ACEPTADO') {
                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saefact SET fact_aprob_sri = 'S', fact_auto_sri = '$uid'
                                                   WHERE fact_cod_fact = $fact_cod_fact ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');
                                    // update siigo
                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saefact SET 
                                                    fact_siigo_id = '$id_inv_siigo', 
                                                    fact_siigo_prefix = '$prefix', 
                                                    fact_siigo_number = '$number',
                                                    fact_siigo_name = '$name',
                                                    fact_siigo_date = '$date',

                                                    fact_siigo_envio = '$body',
                                                    fact_siigo_respuesta = '$respuesta'
                                                   WHERE fact_cod_fact = $fact_cod_fact ;";

                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "SIIGO_ACEPTADO $num_factura",
                                        'result_email' => $email_status,
                                        'fact_siigo_id' => $id_inv_siigo,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else if ($status == 'SIIGO_NO_ENVIADO') {

                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saefact SET fact_aprob_sri = 'N', fact_auto_sri = '$uid', fact_cod_hash = '$cufe'
                                                   WHERE fact_cod_fact = $fact_cod_fact ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');

                                    
                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "SIIGO_NO_ENVIADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else {
                                $error_array = trim($data_json->errors->error);
                                $error = 'Error ' . $status . ' ' . $error_array[3] . ' '.$contr_ident;
                                throw new Exception($error);
                            }

                            break;

                        case 400:
                            $Status = $data_json->Status;
                            $error_in = ($data_json->Errors);
                            foreach ($error_in as $item) {
                                $mensaje .= $item['Message'].", <br>";
                                $mensaje .= $item['Code'].", <br>";
                            }
                            throw new Exception($mensaje);
                        break;
                            
                        
                        case 401:
                            $Status = $data_json->Status;
                            $error_in = ($data_json->Errors);
                            foreach ($error_in as $item) {
                                $mensaje .= $item['Message'].", <br>";
                                $mensaje .= $item['Code'].", <br>";
                            }
                            throw new Exception($mensaje);
                        break;
                        case 409:
                            $Status = $data_json->Status;
                            $error_in = ($data_json->Errors);
                            foreach ($error_in as $item) {
                                $mensaje .= $item['Message'].", <br>";
                                $mensaje .= $item['Code'].", <br>";
                            }
                            throw new Exception($mensaje);
                            
                        break;
                        case 500:
                            $Status = $data_json->Status;
                            $error_in = ($data_json->Errors);
                            foreach ($error_in as $item) {
                                $mensaje .= $item['Message'].", <br>";
                                $mensaje .= $item['Code'].", <br>";
                            }
                            throw new Exception($mensaje);
                        break;
                        default:
                            $Status = $data_json->Status;
                            $error_in = ($data_json->Errors);
                            foreach ($error_in as $item) {
                                $mensaje .= $item['Message'].", <br>";
                                $mensaje .= $item['Code'].", <br>";
                            }
                            throw new Exception($mensaje);
                            throw new Exception("Error desconocido en el WebService, Consulte con el administrador $http_code $mensaje");
                    }


                } else {
                    if ($http_code>0){
                        $error_array = $data['Errors'];
                        $count_error = 0; 
                        foreach ($error_array as $key => $error) {
                            $mensaje .= $count_error>0?', '.$error:': '.$error;
                            $count_error++;
                        }

                    }
                    
                    throw new Exception("Hubo un error no se puede conectar al WebService ($mensaje $http_code)");
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
            DISTINCT
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
            fac.ncre_val_tcam as fact_val_tcam,
            tcmp_siigo_id, 
            tcmp_siigo_name,
            case 
            when trim(SUBSTRING (fac.ncre_nse_ncre, 4)) = '".$this->siigo_active_ncre_FEGC_prefix."' then '".$this->siigo_active_ncre_id_FEGC."'
            when trim(SUBSTRING (fac.ncre_nse_ncre, 4)) = '".$this->siigo_active_ncre_INS_prefix."' then '".$this->siigo_active_ncre_id_INS."' 
            else '' end as ncre_tdoc_siigo_id
            FROM
	        saencre fac inner join saeclpv cli on fac.ncre_cod_clpv = cli.clpv_cod_clpv
            LEFT JOIN saetcmp tcmp on tcmp.tcmp_cod_tcmp = fac.ncre_tip_vent

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

                        $tcmp_siigo_id = $this->oIfx->f('tcmp_siigo_id');
                        $tcmp_siigo_name = $this->oIfx->f('tcmp_siigo_name');
                        $this->ncre_tdoc_siigo_id = $this->oIfx->f('ncre_tdoc_siigo_id');

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
                            fac.fact_nse_fact, 
                            fac.fact_num_preimp, 
                            fac.fact_auto_sri,                            
                            fact_siigo_id, 
                            fact_siigo_prefix, 
                            fact_siigo_number,
                            fact_siigo_name,
                            fact_siigo_date
                        FROM
                        saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
                        WHERE fac.fact_cod_fact = $fact_cod_ndb;";
                // print($sql);exit;

                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        do {
                            $fact_num_preimp_afect = (int) $this->oIfx->f('fact_num_preimp');
                            $fact_nse_fact_afect = substr($this->oIfx->f('fact_nse_fact'), 3, 9);
                            $fact_auto_sri = $this->oIfx->f('fact_auto_sri');

                            $fact_siigo_id = $this->oIfx->f('fact_siigo_id');
                            $fact_siigo_prefix = $this->oIfx->f('fact_siigo_prefix');
                            $fact_siigo_number = $this->oIfx->f('fact_siigo_number');
                            $fact_siigo_name = $this->oIfx->f('fact_siigo_name');
                            $fact_siigo_date = $this->oIfx->f('fact_siigo_date');
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                $numDocfectado = "$fact_nse_fact_afect-$fact_num_preimp_afect";
            } else {
                throw new Exception("Sin factura para realizar NC");
            }

            if (empty($fact_siigo_id)){
                throw new Exception("No se puede enviar la nota de credito sin antes enviar la factura");
            }

            $fact_payment_indi = '';
            $fact_payment_indi_array = array();
            $fxfp_cont = 0;

            $fact_tot_fact_control = $fact_tot_fact;
            if ($fact_cod_ndb) {
                $sql = "SELECT
                    fx.fxfp_cod_fpag,
                    round(fx.fxfp_val_fxfp,2) as fxfp_val_fxfp,
                    fx.fxfp_fec_fin,
                    fx.fxfp_cot_fpag,
                    fpag_siigo_id,
                    fpag_siigo_name
                    FROM
                    saefxfp fx 
                    left join saefpag on fpag_cod_fpag = fx.fxfp_cod_fpag
                    WHERE fx.fxfp_cod_fact = $fact_cod_ndb ;";

                
                $valor_detra_rest = $valor_detra_rest?$valor_detra_rest:0;
                $fact_val_tcam = $fact_val_tcam?$fact_val_tcam:1;

                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {
                            $fpag_siigo_id = '';
                            $fxfp_cod_fpag  = $this->oIfx->f('fxfp_cod_fpag');
                            $fxfp_val_fxfp  = $this->oIfx->f('fxfp_val_fxfp');
                            $fxfp_fec_fin   = $this->oIfx->f('fxfp_fec_fin');
                            $tipo           = $this->oIfx->f('fxfp_cot_fpag');
                            $fpag_siigo_id  = $this->oIfx->f('fpag_siigo_id');


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
                            } else {
                                $tipo_fpag = 'CASH';
                                $tipo_2 = 'DEBITO';
                            }

                            if($fact_tot_fact_control>0){
                                $fact_tot_fact_control -= $fxfp_val_fxfp;

                                $fxfp_val_fxfp = ($fact_tot_fact_control-$fxfp_val_fxfp>0)?$fxfp_val_fxfp:($fact_tot_fact_control-$fxfp_val_fxfp)+$fxfp_val_fxfp;

                                array_push(
                                    $fact_payment_indi_array,
                                    array(
                                        "id"=> "$fpag_siigo_id",
                                        "value"=> $fxfp_val_fxfp,
                                        "due_date"=>"$fxfp_fec_fin"
                                    )
                                );
                            }
                            $fxfp_cont++;

                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();
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
            

            $siigo_api_url          = $this->empr_siigo_api_url;
            $siigo_username         = $this->empr_siigo_username;
            $siigo_access_token     = $this->empr_siigo_access_token;
            $empr_siigo_autoenvio   = $this->empr_siigo_autoenvio;


            if (empty($siigo_api_url)) {
                throw new Exception("No se encuentra configurado siigo api url");
            }

            if (empty($siigo_username)) {
                throw new Exception("No se encuentra configurado siigo username");
            }

            if (empty($siigo_access_token)) {
                throw new Exception("No se encuentra configurado siigo access token");
            }

            $regimen = 'ORDINARIO';

            $departamento = $depar_nom;
            $ciudad = $ciud_nom;
            $fact_num_preimp = intval($fact_num_preimp);

            // $fact_fech_fact = date("d/m/Y", strtotime($fact_fech_fact));

            $fecha_hora = $fact_fech_fact;

            if (!$fact_email_clpv) {
                // throw new Exception("No se encontro email");
            }

            if (!$fact_tlf_cliente) {
                // throw new Exception("No se encontro el telefono");
            }

            if (!$fact_ruc_clie) {
                throw new Exception("No se encontro la identificacion");
            }

            $ambiente = ambienteEmisionSri($this->oCon, $this->empr_cod_empr, $fact_cod_sucu, 1);
            $tipo_ambiente = "PRUEBAS";
            if ($ambiente == 2) {
                $tipo_ambiente = "PRODUCCION";
            }

            // tabla notivs ncre
            // select ddev_cod_ddev, ddev_des_ddev,* from saeddev where ddev_cod_empr = 1 order by 2

            // Devolución parcial de los bienes y/o no aceptación parcial del servicio
            // Anulación de factura electrónica
            // Rebaja o descuento parcial o total
            // Ajuste de precio
            // Descuento comercial por pronto pago
            // Descuento comercial por volumen de ventas
            $motivo_id = 0;

            switch($motivo_ncre){
                case "Devolución de parte de los bienes; no aceptación de partes del servicio":
                    $motivo_id = '1';
                    break;
                
                case "Anulación de factura electrónica":
                    $motivo_id = '2';
                    break;

                case "Rebaja o descuento parcial o total":
                    $motivo_id = '3';
                    break;
                    
                case "Ajuste de precio":
                    $motivo_id = '4';
                    break;

                case "Descuento comercial por pronto pago":
                    $motivo_id = '6';
                    break;

                case "Descuento comercial por volumen de venta":
                    $motivo_id = '7';
                    break;    
            }


            $sql = "SELECT 
                    dncr_cant_dfac as dfac_cant_dfac,
                    dncr_cod_prod as dfac_cod_prod,
                    dncr_det_dncr as dfac_det_dfac,
                    dncr_mont_total as dfac_mont_total,
                    dncr_precio_dfac as dfac_precio_dfac,
                    dncr_por_iva as dfac_por_iva,
                    dncr_exc_iva as dfac_exc_iva,
                    ((dncr_des1_dfac+dncr_des2_dfac+dncr_des3_dfac+dncr_des4_dfac)) as descuento_entero,
                    (((dncr_des1_dfac+dncr_des2_dfac+dncr_des3_dfac+dncr_des4_dfac)/100)) as descuento_porcentaje,
                    (((dncr_cant_dfac*dncr_precio_dfac)*(dncr_des1_dfac+dncr_des2_dfac+dncr_des3_dfac+dncr_des4_dfac)/100)) as descuento_valor,
                    ((dncr_cant_dfac*dncr_precio_dfac)- ((dncr_cant_dfac*dncr_precio_dfac)*(dncr_des1_dfac+dncr_des2_dfac+dncr_des3_dfac+dncr_des4_dfac)/100)) as total
                    from saedncr
                    where dncr_cod_ncre = $ncre_cod_ncre;";

            $dncre_indi="";
            $dncre_indi_array=array();
            $contador_dncr = 0;
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

                        $dfac_descuento_entero = $this->oIfx->f('descuento_entero');
                        $dfac_descuento_porcentaje = $this->oIfx->f('descuento_porcentaje');
                        $dfac_descuento_valor = $this->oIfx->f('descuento_valor');
                        $dfac_total = $this->oIfx->f('total');

                        $porcentaje_iva = ($dfac_por_iva / 100) + 1;

                        $precio_uni_indi = $dfac_mont_total / $dfac_cant_dfac;

                        $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;

                        if (empty($dfac_det_dfac)) {
                            $dfac_det_dfac = 'Sin detalle';
                        }

                        if($contador_dncr>0){
                            $dncre_indi .=',';
                        }

                        array_push(
                            $dncre_indi_array,
                            array(
                                "code"=>"".$dfac_cod_prod."",
                                "description"=>"".$this->BBVASpecialCharConvertion($dfac_det_dfac)."",
                                "quantity"=>"".intval($dfac_cant_dfac)."",
                                "price"=>$precio_uni_indi
                            )
                        );
                        $contador_dncr++;
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            
            // $date = date('Y-m-d');

            if(empty($this->ncre_tdoc_siigo_id)){
                throw new Exception("tcmp siigo ncre no configurado", 1);                
            }

            $fact_value_data            = $this->forzar_tot_fxfp_dfac_mont_tot($fact_payment_indi_array, $dncre_indi_array,$fact_cod_sucu,$fact_tot_fact);

            // throw new Exception("dede: ".json_encode($temp_convetion,true)."");

            $fact_payment_indi_array    = $fact_value_data['fxfp_array']?$fact_value_data['fxfp_array']:$fact_payment_indi_array;
            $dncre_indi_array   	    = $fact_value_data['dfac_array']?$fact_value_data['dfac_array']:$dncre_indi_array;

            $tot_fxfp_forced   	    = $fact_value_data['tot_fxfp'];
            $tot_dfac_forced   	    = $fact_value_data['tot_dfac'];

            


            


            $empr_siigo_autoenvio_mail = $this->empr_siigo_autoenvio_mail?$this->empr_siigo_autoenvio_mail:'N';


            $array_data = array(
                "document"=>array(
                    "id"=>$this->ncre_tdoc_siigo_id
                ),
                "retentions"=>array(
                    array(
                        "id"=> $this->id_retencion
                    )
                ),
                "date"=>"$fact_fech_fact",
                "reason"=>$this->BBVASpecialCharConvertion("$motivo_id"),
                "invoice"=>$this->BBVASpecialCharConvertion("$fact_siigo_id"),
                "items"=>$dncre_indi_array,
                "number"=>$this->BBVASpecialCharConvertion("$fact_num_preimp"),
                "stamp"=>array(
                    "send"=>$empr_siigo_autoenvio=='S'?true:false
                ),
                "mail"=>array(
                    "send"=>$empr_siigo_autoenvio_mail=='S'?true:false

                ),
                "payments"=>$fact_payment_indi_array
            );
            

            $data = json_encode($array_data);
            // throw new Exception("$data", 1);                


            // if ($tot_fxfp_forced!=$tot_dfac_forced || $tot_fxfp_forced != $fact_tot_fact || $tot_dfac_forced !=$fact_tot_fact){
            //     throw new Exception("Revisar el total del encabezado y el total de la forma de pago 
            //     <br>total nota de credito:  $fact_tot_fact 
            //     <br>total forma pago:  $tot_fxfp_forced 
            //     <br> <br>sent: $data", 1);                
            // }


            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                /**
                 * EMPIEZA ENVIO
                 */
                // $data = json_encode($data);


                $endpoint = '/v1/credit-notes';
                $method = 'POST';
                $body = $data;
                $result = $this->request_siigo($endpoint,$method,$body);                
                $http_code = intval($result['http_code']);
                $data = $result['data'];
                $mensaje = $result['mensaje'];
                $respuesta = json_encode($data,true);

                
                

                if($http_code>=200 && $http_code<300){

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
                            $status = 'SIIGO_ACEPTADO';

                            $id_inv_siigo = ($data_json->id);

                            $pdf_url .=$id_inv_siigo;

                            $document_id = ($data_json->document['id']);

                            $prefix = ($data_json->prefix);
                            $number = ($data_json->number);

                            $date = trim($data_json->date);
                            $name = trim($data_json->name);
                            $uid = $id_inv_siigo;

                            $num_factura = $name;

                            $customer_id = ($data_json->customer['id']);
                            $customer_identification = ($data_json->customer['identification']);
                            $customer_branch_office = ($data_json->customer['branch_office']);


                            $seller = $data_json->seller;
                            $total = $data_json->total;
                            $balance = $data_json->balance;

                            $items = $data_json->items;
                            $items_id = $items[0]['id'];
                            $items_quantity = $items[0]['quantity'];
                            $items_price = $items[0]['price'];
                            $items_description = $items[0]['description'];
                            $items_taxes = $items[0]['taxes'];

                            $items_taxes_id = $items_taxes[0]['id'];
                            $items_taxes_name = $items_taxes[0]['name'];
                            $items_taxes_type = $items_taxes[0]['type'];
                            $items_taxes_percentage = $items_taxes[0]['percentage'];

                            $payments_id = $data_json->payments['id'];
                            $payments_name = $data_json->payments['name'];
                            $payments_value = $data_json->payments['value'];

                            $mail_status = $data_json->mail['status'];
                            $mail_observations = $data_json->mail['observations'];
                            $email_status = $mail_observations;

                            $metadata_created = $data_json->metadata['created'];

                            /**
                             * PDF
                             */

                            $ruta_pdf_save = $ruta_pdf;
                            $nombre_archivo = 'nc_'. $ncre_cod_ncre;

                            $formato = 'pdf'; 
                            $endpoint = '/v1/credit-notes'.'/'.$id_inv_siigo.'/'.$formato;
                            $this->save_base64_pdf($endpoint,$ruta_pdf_save,$nombre_archivo,$formato);

                            /**
                             * XML
                            */
                            
                            $ruta_xml_save = $ruta_xml;
                            $formato = 'xml';
                            $endpoint = '/v1/credit-notes'.'/'.$id_inv_siigo.'/'.$formato;
                            $this->save_base64_pdf($endpoint,$ruta_xml_save,$nombre_archivo,$formato);

                            $nombre_documento = $nombre_archivo;
                            $email_status = $id_inv_siigo;



                            // $div_button .= '<div class="btn-group" role="group">';

                            // $div_button .= '<a href="upload/xml/fac_' . $ncre_cod_ncre . '.xml" download="' . $nombre_documento . '.xml">
                            //                 <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                            //             </a>';

                            $div_button .='<a href="upload/pdf/fac_' . $ncre_cod_ncre . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>';
                            // $div_button .='</div>';

                            // print($div_button);exit;


                            if ($status == 'SIIGO_ACEPTADO') {
                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'S'
                                                   WHERE ncre_cod_ncre = $ncre_cod_ncre ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');



                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saencre SET 
                                                    ncre_siigo_date = '$date',
                                                    ncre_siigo_number = '$number',
                                                    ncre_siigo_name = '$name',
                                                    ncre_siigo_id = '$id_inv_siigo',                                                    

                                                    ncre_siigo_envio = '$body',
                                                    ncre_siigo_respuesta = '$respuesta'
                                                   WHERE ncre_cod_ncre = $ncre_cod_ncre ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "1) SIIGO_ACEPTADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else if ($status == 'SIIGO_NO_ENVIADO') {

                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'N'
                                                   WHERE ncre_cod_ncre = $ncre_cod_ncre ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "SIIGO_NO_ENVIADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else {
                                $error_array = trim($data_json->errors->error);
                                $error = 'Error ' . $status . ' ' . $error_array[3] . ' ' .$contr_ident;
                                throw new Exception($error);
                            }

                            break;
                        case 201:
                            $status = 'SIIGO_ACEPTADO';
                    

                            $id_inv_siigo = ($data_json->id);
                            $pdf_url .=$id_inv_siigo;
                            $document_id = ($data_json->document['id']);
                            $number = ($data_json->number);//numero(correlativo) de la nota de credito
                            $name = ($data_json->name);//nombre(num_ncre/numfac) de la nota de credito
                            $date = ($data_json->date);
                            $invoice_id = ($data_json->invoice['id']);
                            $invoice_name = ($data_json->invoice['name']);
                            $customer_id = ($data_json->customer['id']);
                            $customer_identification = ($data_json->customer['identification']);
                            $customer_branch_office = ($data_json->customer['branch_office']);

                            $seller = $data_json->seller;
                            $total = $data_json->total;

                            $items = $data_json->items;

                            foreach ($items as $item) {
                                $item_id            = $item['id'];
                                $item_quantity      = $item['quantity'];
                                $item_price         = $item['price'];
                                $item_description   = $item['description'];
                                $item_taxes         = $item['taxes'];
                                // update dncr
                            }
                            $payments = $data_json->payments;


                            foreach ($payments as $payment) {
                                $payment_id         = $payment['id'];
                                $payment_name       = $payment['name'];
                                $payment_value      = $payment['value'];
                                $payment_due_date   = $payment['due_date'];
                                // update fxfp de la ncre
                            }

                            $uid            = $id_inv_siigo;
                            $num_factura    = $name;

                            $metadata_created = $data_json->metadata['created'];

                            /**
                             * PDF
                             */

                             $ruta_pdf_save = $ruta_pdf;
                             $nombre_archivo = 'nc_'. $ncre_cod_ncre;

                             $formato = 'pdf'; 
                             $endpoint = '/v1/credit-notes'.'/'.$id_inv_siigo.'/'.$formato;
                             $this->save_base64_pdf($endpoint,$ruta_pdf_save,$nombr_earchivo,$formato);
 
                             /**
                              * XML
                             */
                             
                             $ruta_xml_save = $ruta_xml;
                             $formato = 'xml';
                             $endpoint = '/v1/credit-notes'.'/'.$id_inv_siigo.'/'.$formato;
                             $this->save_base64_pdf($endpoint,$ruta_xml_save,$nombre_archivo,$formato);
 
                             $nombre_documento = $nombre_archivo;

                            $email_status = $id_inv_siigo;



                            // $div_button .= '<div class="btn-group" role="group">';

                            // $div_button .='<a href="upload/xml/nc_' . $ncre_cod_ncre . '.xml" download="' . $nombre_documento . '.xml">
                            //                 <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                            //             </a>';

                            $div_button .= '<a href="upload/pdf/nc_' . $ncre_cod_ncre . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>';
                            // $div_button .= '</div>';


                            if ($status == 'SIIGO_ACEPTADO') {
                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'S'
                                                   WHERE ncre_cod_ncre = $ncre_cod_ncre ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saencre SET 
                                                    ncre_siigo_date = '$date',
                                                    ncre_siigo_number = '$number',
                                                    ncre_siigo_name = '$name',
                                                    ncre_siigo_id = '$id_inv_siigo',
                                                    
                                                    ncre_siigo_envio = '$body',
                                                    ncre_siigo_respuesta = '$respuesta'
                                                   WHERE ncre_cod_ncre = $ncre_cod_ncre ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "2) SIIGO_ACEPTADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else if ($status == 'SIIGO_NO_ENVIADO') {

                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'N'
                                                   WHERE ncre_cod_ncre = $ncre_cod_ncre ;";
                                    $this->oIfx->QueryT($sql_update);

                                    $this->oIfx->QueryT('COMMIT;');


                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => "SIIGO_NO_ENVIADO $num_factura",
                                        'result_email' => $email_status,
                                    );

                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }

                            } else {
                                $error_array = trim($data_json->errors->error);
                                $error = 'Error ' . $status . ' ' . $error_array[3] . ' '. $contr_ident;
                                throw new Exception($error);
                            }

                            break;
                        case 401:
                            $Status = $data_json->Status;
                            $error_in = ($data_json->Errors);
                            foreach ($error_in as $item) {
                                $mensaje .= $item['Message'].", <br>";
                                $mensaje .= $item['Code'].", <br>";
                            }
                            throw new Exception($mensaje);
                            break;
                        case 500:
                            $Status = $data_json->Status;
                            $error_in = ($data_json->Errors);
                            foreach ($error_in as $item) {
                                $mensaje .= $item['Message'].", <br>";
                                $mensaje .= $item['Code'].", <br>";
                            }
                            throw new Exception($mensaje);
                        default:
                            $Status = $data_json->Status;
                            $error_in = ($data_json->Errors);
                            foreach ($error_in as $item) {
                                $mensaje .= $item['Message'].", <br>";
                                $mensaje .= $item['Code'].", <br>";
                            }
                            throw new Exception("default: ".$http_code.'-'.$D.'-'.$mensaje);
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

    private function forzar_tot_fxfp_dfac_mont_tot($fxfp_array, $dfac_array,$id_sucursal,$fact_tot_fact){


        // $sql = "SELECT para_num_dec from saepara WHERE para_cod_empr = ".$this->empr_cod_empr." and para_cod_sucu = $id_sucursal";
        // $para_num_dec = consulta_string_func($sql, 'para_num_dec', $this->oCon, 6);

        $para_num_dec = 2;//siigo trabaja a 2 decimales

        $fxfp_tot_fxfp_rounded = 0;
        $fxfp_tot_fxfp_normal = 0;
        $fxfp_temp_rounded = array();
        $fxfp_temp = array();

        $fxfp_length = sizeof($fxfp_array);

        foreach ($fxfp_array as $key => $fxfp) {
            $id_temp                =  $fxfp['id'];
            $value_tempt_normal     =  floatval($fxfp['value']);
            $value_tempt_rounded    =  round($value_tempt_normal,$para_num_dec);
            $due_date_temp          =  $fxfp['due_date'];
            $fxfp_tot_fxfp_rounded  += $value_tempt_rounded;
            $fxfp_tot_fxfp_normal   += $value_tempt_normal;

            array_push(
                $fxfp_temp_rounded,
                array(
                    "id"=>$id_temp,
                    "value"=>$value_tempt_rounded,
                    "due_date"=>$due_date_temp
                )
            );
        }


        $diff_fact_fxfp_indi = round(($fact_tot_fact-$fxfp_tot_fxfp_rounded)/$fxfp_length,$para_num_dec);
        $diff_fact_fxfp_indi_control = 0;
        $fxfp_tot_fxfp_rounded = 0;

        foreach ($fxfp_temp_rounded as $key => $fxfp) {
            $id_temp                =  $fxfp['id'];
            $value_tempt_normal     =  floatval($fxfp['value']);
            $due_date_temp          =  $fxfp['due_date'];

            $diff_fact_fxfp_indi_control += $diff_fact_fxfp_indi;

            // echo $diff_fact_fxfp_indi_control.'---#';    

            if($diff_fact_fxfp_indi_control>=0.01){
                $value_tempt_normal +=round($diff_fact_fxfp_indi_control,$para_num_dec);

                $tmp_val = $diff_fact_fxfp_indi-round($diff_fact_fxfp_indi,$para_num_dec);

                if($tmp_val>0){
                    $diff_fact_fxfp_indi_control = $tmp_val;
                }else{
                    $diff_fact_fxfp_indi_control = round($diff_fact_fxfp_indi,$para_num_dec)-$diff_fact_fxfp_indi;
                }

            }
            $fxfp_tot_fxfp_rounded += $value_tempt_normal;
            array_push(
                $fxfp_temp,
                array(
                    "id"=>$id_temp,
                    "value"=>round($value_tempt_normal,$para_num_dec),
                    "due_date"=>$due_date_temp
                )
            );
            $value_tempt_normal += $diff_fact_fxfp_indi_control;
        }

        $dfac_tot_dfac_normal = 0;
        $dfac_tot_dfac_rounded = 0;
        $dfac_cant_dfac = 0;
        $count_values_with_decimals = 0;
        $dfac_temp = array();
        foreach ($dfac_array as $key => $dfac) {
            
            $code_temp              = $dfac['code'];
            $description_temp       = $dfac['description'];
            $quantity_temp          = intval($dfac['quantity']);
            $price_temp             = floatval($dfac['price']);

            $dfac_price             =  round($price_temp,$para_num_dec);
            $dfac_cant_dfac         += $quantity_temp;
            $dfac_tot_dfac_normal   += ($dfac_price*$quantity_temp);
            $dfac_tot_dfac_rounded  += round(($dfac_price*$quantity_temp),$para_num_dec);

            if(($price_temp-intval($price_temp))>0){
                $count_values_with_decimals++;
            }

            // echo "count_values_with_decimals $count_values_with_decimals ; price_temp: $price_temp ; dfac_price $dfac_price---@@@";


            array_push(
                $dfac_temp,
                array(
                    "code"=>"".$code_temp."",
                    "description"=>"$description_temp",
                    "quantity"=>$quantity_temp,
                    "price"=>$dfac_price
                )
            );
        }


        if ($fxfp_tot_fxfp_rounded == $dfac_tot_dfac_rounded && $fxfp_tot_fxfp_rounded == $fxfp_tot_fxfp_normal &&  $dfac_tot_dfac_rounded == $dfac_tot_dfac_normal ){


            if($fxfp_tot_fxfp_rounded<=0 || $dfac_tot_dfac_rounded<=0 ){
                throw new Exception("Error en sincronizar valores, valide proceso fxfp_tot_fxfp_rounded: $fxfp_tot_fxfp_rounded y dfac_tot_dfac_rounded: $dfac_tot_dfac_rounded");
            }
            return array(
                "fxfp_array"=>$fxfp_temp,
                "dfac_array"=>$dfac_temp,                
                "tot_dfac"=>$dfac_tot_dfac_rounded,
                "tot_fxfp"=>$fxfp_tot_fxfp_rounded
            );
        }else{
            $dfac_new = array();

            $diferencia = 0;
            $diferencia_control = floatval(($dfac_tot_dfac_rounded > $fxfp_tot_fxfp_rounded)?($dfac_tot_dfac_rounded-$fxfp_tot_fxfp_rounded):($fxfp_tot_fxfp_rounded-$dfac_tot_dfac_rounded));
            if(($diferencia_control*100)>$dfac_cant_dfac){
                // no puede fallar el redondeo por mas de 0.01 por cada item
                throw new Exception("Revise los valores de la factura (excede el valor para sincronizar montos)");
            }else{

                if($dfac_tot_dfac_rounded > $fxfp_tot_fxfp_rounded){

                    // dfac mayor a la forma de pago (reducir dfac)

                    $diferencia = $dfac_tot_dfac_rounded-$fxfp_tot_fxfp_rounded;
                    $valor_reducir_indi = $diferencia/$dfac_cant_dfac;

                    $valor_reducir_indi = $valor_reducir_indi*$count_values_with_decimals;

                    $valor_reducir_indi_sum = 0;
                    $count_iter = 0;
                    $dfac_tot_dfac_rounded = 0;

                    foreach ($dfac_temp as $key => $dfac) {
                        
                        $code_indi          = $dfac['code'];
                        $description_indi   = $dfac['description'];
                        $quantity_indi      = $dfac['quantity'];
                        $price_indi         = $dfac['price'];

                        $valor_reducir_indi_sum += $valor_reducir_indi*$quantity_indi;

                        $to_reduce = round($valor_reducir_indi_sum,$para_num_dec);
                        $check_deci = $price_indi-intval($price_indi);

                        // echo "reduce dfac check_deci $check_deci to_reduce: $to_reduce count_values_with_decimals $count_values_with_decimals---#";


                        if($to_reduce>=0.01 && $check_deci>=0.01){
                          
                            array_push(
                                $dfac_new,
                                array(
                                    "code"=>"".$code_indi."",
                                    "description"=>"$description_indi",
                                    "quantity"=>intval($quantity_indi),
                                    "price"=>round($price_indi-$to_reduce,$para_num_dec)
                                )
                            );
                            $dfac_tot_dfac_rounded += round($price_indi-$to_reduce,$para_num_dec);

                            $valor_reducir_indi_sum = $valor_reducir_indi_sum-$to_reduce;

                            // echo "reduce dfac reducir $valor_reducir_indi_sum ---#";

                        }else{

                            array_push(
                                $dfac_new,
                                array(
                                    "code"=>"".$code_indi."",
                                    "description"=>"$description_indi",
                                    "quantity"=>intval($quantity_indi),
                                    "price"=>$price_indi
                                )
                            );

                            $dfac_tot_dfac_rounded += round($price_indi);

                            // echo "reduce dfac normal $valor_reducir_indi_sum ---#";


                        }   
                        $count_iter++;  
                        
                        
                    }


                }else{
                    // dfac menor a la forma de pago (aumentar dfac)
                    // throw new Exception("aumentar dfac $fxfp_tot_fxfp_rounded, $dfac_tot_dfac_rounded, $fxfp_tot_fxfp_normal, $dfac_tot_dfac_normal");

                    $diferencia = $fxfp_tot_fxfp_rounded-$dfac_tot_dfac_rounded;
                    $valor_reducir_indi = $diferencia/$dfac_cant_dfac;

                    $valor_reducir_indi = $valor_reducir_indi*$count_values_with_decimals;


                    $valor_reducir_indi_sum = 0;

                    foreach ($dfac_temp as $key => $dfac) {
                        
                        $code_indi          = $dfac['code'];
                        $description_indi   = $dfac['description'];
                        $quantity_indi      = $dfac['quantity'];
                        $price_indi         = $dfac['price'];

                        $valor_reducir_indi_sum += $valor_reducir_indi*$quantity_indi;
                        $to_reduce = round($valor_reducir_indi_sum,$para_num_dec);
                        $check_deci = $price_indi-intval($price_indi);

                        if($to_reduce>=0.01 && $check_deci>=0.01){

                            array_push(
                                $dfac_new,
                                array(
                                    "code"=>"".$code_indi."",
                                    "description"=>"$description_indi",
                                    "quantity"=>"".intval($quantity_indi)."",
                                    "price"=>round($price_indi+$to_reduce,$para_num_dec)
                                )
                            );
                            $valor_reducir_indi_sum = $valor_reducir_indi_sum-$to_reduce;
                        }else{
                            array_push(
                                $dfac_new,
                                array(
                                    "code"=>"".$code_indi."",
                                    "description"=>"$description_indi",
                                    "quantity"=>"".intval($quantity_indi)."",
                                    "price"=>$price_indi
                                )
                            );
                        }                       
                        
                    }
                }

                return array(
                    "fxfp_array"=>$fxfp_temp,
                    "dfac_array"=>$dfac_new,
                    "tot_dfac"=>$dfac_tot_dfac_rounded,
                    "tot_fxfp"=>$fxfp_tot_fxfp_rounded
                );
            }

        }

        
    }

    private function tabla_integraciones(){
        $conteo = 0;
        $sql = "SELECT count(*) as conteo FROM INFORMATION_SCHEMA.COLUMNS WHERE  TABLE_NAME = 'integraciones' and table_schema='comercial'";

        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {
                $conteo = $this->oIfx->f('conteo')?$this->oIfx->f('conteo'):0;
            }
        }
        $this->oIfx->Free();

        if(intval($conteo)==0){

            $sql = "CREATE TABLE comercial.integraciones (
                    id serial,
                    empresa_id int4,
                    estado_sn varchar(2),
                    nombre_integracion varchar(100),
                    fecha_creacion timestamp,
                    fecha_modificacion timestamp,
                    fecha_expiracion timestamp,
                    ambiente text,
                    url_api text,
                    auth_autorizacion text,
                    request_autorizacion text,
                    tipo_api text,
                    usuario text,
                    clave text,
                    token text,
                    token_jwt text,
                    token_expira text,
                    token_tiempo text,
                    parametro_auth1 text,
                    parametro_auth2 text,
                    parametro_auth3 text,
                    parametro_auth4 text,
                    parametro_request1 text,
                    parametro_request2 text,
                    parametro_request3 text,
                    parametro_request4 text,
                    otro_parametro1 text,
                    otro_parametro2 text,
                    otro_parametro3 text
                );";

            $this->oIfx->QueryT('BEGIN;');
            $this->oIfx->QueryT($sql);
            $this->oIfx->QueryT('COMMIT;');
            $this->oIfx->Free();
            
            /*CREACION DE LA TABLA PARA LOS TIPOS DE DOCUMENTO SIIGO */
            $conteo_siigo = 0;
            $sql = "SELECT count(*) as conteo FROM INFORMATION_SCHEMA.COLUMNS WHERE  TABLE_NAME = 'tipo_documento_siigo' and table_schema='comercial'";
    
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $conteo_siigo = $this->oIfx->f('conteo')?$this->oIfx->f('conteo'):0;
                }
            }
            $this->oIfx->Free();
    
            if(intval($conteo_siigo)==0){
                $sql = "CREATE TABLE comercial.tipo_documento_siigo (
                    id serial,
                    empresa_id int4,
                    sucursal_id int4,
                    prefijo_siigo varchar(100),
                    estado_siigo_sn varchar(2),
                    siigo_id varchar(100),
                    siigo_json text,
                    nse varchar(100),
                    para_punt_emi_sn varchar(2),
                    tcmp_cod_tcmp varchar(100),
                    fecha_creacion timestamp
                    fecha_modificacion timestamp
                );";

                $this->oIfx->QueryT('BEGIN;');
                $this->oIfx->QueryT($sql);
                $this->oIfx->QueryT('COMMIT;');
                $this->oIfx->Free();

            }

        }
    }

    function registrar_tipo_documento_siigo($empresa_id, $sucursal_id,$tcmp_cod_tcmp){
        $sql = "SELECT * FROM comercial.tipo_documento_siigo WHERE empresa_id = '$empresa_id' AND sucursal_id = '$sucursal_id' and tcmp_cod_tcmp = '$tcmp_cod_tcmp' ";
    }

    function registrar_integracion($empresa_id,$estado_sn,$nombre_integracion,$ambiente='',$url_api,$tipo_api,$auth_autorizacion='',$request_autorizacion='',$usuario,$clave,$token,$token_jwt,$token_expira,$token_tiempo){

        $sql = "SELECT * FROM comercial.integraciones WHERE empresa_id = '$empresa_id' AND nombre_integracion = '$nombre_integracion'";
        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {
                do {
                    $this->id_integracion       = $this->oIfx->f('id');
                    $empresa_id_int             = $this->oIfx->f('empresa_id');
                    $estado_sn_int              = $this->oIfx->f('estado_sn');
                    $nombre_integracion_int     = $this->oIfx->f('nombre_integracion');
                    $fecha_creacion_int         = $this->oIfx->f('fecha_creacion');
                    $ambiente_int               = $this->oIfx->f('ambiente');
                    $url_api_int                = $this->oIfx->f('url_api');
                    $tipo_api_int               = $this->oIfx->f('tipo_api');
                    $auth_autorizacion_int      = $this->oIfx->f('auth_autorizacion');
                    $request_autorizacion_int   = $this->oIfx->f('request_autorizacion');
                    $usuario_int                = $this->oIfx->f('usuario');
                    $clave_int                  = $this->oIfx->f('clave');
                    $token_int                  = $this->oIfx->f('token');
                    $token_jwt_int              = $this->oIfx->f('token_jwt');
                    $token_expira_int           = $this->oIfx->f('token_expira');
                    $token_tiempo_int           = $this->oIfx->f('token_tiempo');

                } while ($this->oIfx->SiguienteRegistro());
            }
        }
        $this->oIfx->Free();



        if($this->id_integracion > 0){
            // update

            $update_token_jwt       = !empty($token_jwt)?", token_jwt = '$token_jwt'":"";
            $update_token_expira    = !empty($token_expira)?" ,token_expira = '$token_expira'":"";
            $update_token_tiempo    = !empty($token_tiempo)?" ,token_tiempo = '$token_tiempo'":"";
            $update_tipo_api        = !empty($tipo_api)?" ,tipo_api = '$tipo_api'":"";

            $update_estado_sn       = !empty($estado_sn)?" ,estado_sn = '$estado_sn'":"";

            $update_auth_autorizacion = !empty($auth_autorizacion)?" ,auth_autorizacion = '$auth_autorizacion'":"";
            $update_request_autorizacion = !empty($request_autorizacion)?" ,request_autorizacion = '$request_autorizacion'":"";


            $sql = "UPDATE comercial.integraciones SET 
                        fecha_modificacion      = now(),
                        url_api                 = '$url_api',
                        usuario                 = '$usuario',
                        clave                   = '$clave',
                        token                   = '$token'
                        $update_estado_sn
                        $update_tipo_api
                        $update_token_jwt
                        $update_token_expira
                        $update_token_tiempo

                        $update_auth_autorizacion
                        $update_request_autorizacion
                    WHERE 
                        id                      = '".$this->id_integracion."' 
                        AND empresa_id          = '$empresa_id'";           

            $this->oIfx->QueryT('BEGIN;');
            $this->oIfx->QueryT($sql);
            $this->oIfx->QueryT('COMMIT;');
            $this->oIfx->Free();

        }else{
            // INSERT

            $insert_estado_sn_tag = !empty($estado_sn)?", estado_sn":"";
            $insert_tipo_api_tag = !empty($tipo_api)?", tipo_api":"";

            $insert_token_jwt_tag = !empty($token_jwt)?", token_jwt":"";
            $insert_token_expira_tag = !empty($token_expira)?" ,token_expira":"";
            $insert_token_tiempo_tag = !empty($token_tiempo)?" ,token_tiempo":"";

            $insert_auth_autorizacion_tag = !empty($auth_autorizacion)?" ,auth_autorizacion":"";
            $insert_request_autorizacion_tag = !empty($request_autorizacion)?" ,request_autorizacion":"";

            $insert_estado_sn_value = !empty($estado_sn)?",'$estado_sn'":"";
            $insert_tipo_api_value = !empty($tipo_api)?",'$tipo_api'":"";
            $insert_token_jwt_value = !empty($token_jwt)?",'$token_jwt'":"";
            $insert_token_expira_value = !empty($token_expira)?" ,'$token_expira'":"";
            $insert_token_tiempo_value = !empty($token_tiempo)?" ,'$token_tiempo'":"";

            $insert_auth_autorizacion_value = !empty($auth_autorizacion)?" ,'$auth_autorizacion'":"";
            $insert_request_autorizacion_value = !empty($request_autorizacion)?" ,'$request_autorizacion'":"";


            $sql = "INSERT INTO comercial.integraciones 
            (
                empresa_id,
                nombre_integracion,
                fecha_creacion,
                url_api,
                auth_autorizacion,
                request_autorizacion,
                usuario,
                clave,
                token
                $insert_estado_sn_tag
                $insert_tipo_api_tag
                $insert_token_jwt_tag           
                $insert_token_expira_tag           
                $insert_token_tiempo_tag   

                $insert_auth_autorizacion_tag        
                $insert_request_autorizacion_tag        
            ) 
            VALUES 
            (
                '$empresa_id',
                '$nombre_integracion',
                now(),
                    '$url_api',
                '$auth_autorizacion',
                '$request_autorizacion',
                '$usuario',
                '$clave',
                '$token'
                $insert_estado_sn_value
                $insert_tipo_api_value
                $insert_token_jwt_value
                $insert_token_expira_value
                $insert_token_tiempo_value

                $insert_auth_autorizacion_value
                $insert_request_autorizacion_value
            ) RETURNING id";


            $this->oIfx->QueryT('BEGIN;');
            $this->oIfx->QueryT($sql);
            $this->oIfx->QueryT('COMMIT;');
            $this->id_integracion = $this->oIfx->ResRow['id'];
            $this->oIfx->Free();
        }

    }

    private function get_jwt_token(){

        $endpoint = "/auth";
        $method = "POST";
        $token = "";
        $token_type = "";
        $url = $this->empr_siigo_api_url.$endpoint;

        $body = json_encode([
            "username"=> "$this->empr_siigo_username",
            "access_key"=> "$this->empr_siigo_access_token"
        ],true);

        $token_tiempo_int = '';
        $token_expira_int = '';
        $token_jwt_int = '';

        $sql = "SELECT * FROM COMERCIAL.INTEGRACIONES WHERE id = '".$this->id_integracion."'";

        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {
                do {                   
                    $token_jwt_int              = $this->oIfx->f('token_jwt');
                    $token_expira_int           = $this->oIfx->f('token_expira');//fecha maxima antes de expirar
                    $token_tiempo_int           = $this->oIfx->f('token_tiempo');//expira en 
                    $auth_autorizacion_in       = $this->oIfx->f('auth_autorizacion');
                    $request_autorizacion_in    = $this->oIfx->f('request_autorizacion');

                } while ($this->oIfx->SiguienteRegistro());
            }
        }
        $this->oIfx->Free();

        $current_date           = date('Y-m-d H:i:s');

        if(!empty($token_expira_int) && !empty($token_tiempo_int) && !empty($request_autorizacion_in)){
            $token_expira_int       = date('Y-m-d H:i:s', strtotime($token_expira_int));
            $token_fecha_peticion   = date("Y-m-d H:i:s", strtotime($token_expira_int." -$token_tiempo_int seconds"));

        }else{
            // throw new Exception("SIIGO -  error en la autenticacion token jwt o tipo token de acceso no registrado", 1);            
        }
        $proceso = "";

        if($current_date<$token_expira_int){
            $proceso = "actual";
            $http_code = '200';

            $this->date_token_request   = $token_fecha_peticion;
            $this->date_token_expires   = $token_expira_int;
            $this->jwt_token            = $token_jwt_int;
            $this->token_type           = $request_autorizacion_in;
        }else{
            $proceso = "nuevo";


            // actualiza el token jwt
            $header_array = $this->create_request_header($token,$token_type,[]);
            $api_response = $this->api_request($url,$body,$token,$method,$header_array);

            $response_array = json_decode($api_response,true);


            if ($response_array["http_code"]=='200'){
                $http_code      = ($response_array["http_code"]);
                $access_token   = ($response_array["data"]["access_token"]);
                $expires_in     = ($response_array["data"]["expires_in"]);
                $token_type     = ($response_array["data"]["token_type"]);
                $scope          = ($response_array["data"]["scope"]);
                
                $this->date_token_request   = date('Y-m-d H:i:s');
                $this->date_token_expires   = date("Y-m-d H:i:s", strtotime($this->date_token_request." +$expires_in seconds"));
                $this->jwt_token            = $access_token;
                $this->token_type           = $token_type;

                $sql = "UPDATE comercial.integraciones SET 
                            token_jwt = '".$this->jwt_token."',
                            fecha_modificacion = '".$current_date."',
                            token_expira = '".$this->date_token_expires."',
                            token_tiempo = '".$expires_in."',
                            request_autorizacion = '".$this->token_type."'
                        WHERE 
                        id = '".$this->id_integracion."'
                        ";
                $this->oIfx->QueryT('BEGIN;');
                $this->oIfx->QueryT($sql);
                $this->oIfx->QueryT('COMMIT;');
                $this->oIfx->Free();
                            
            }
        }

        $return_data = json_encode(array(
            "http_code"=>$http_code,
            "data"=>array(
                "proceso"=>$proceso,
                "token"=>$this->jwt_token,
                "type"=>$this->token_type
            )
        ),true);

        return $return_data;

    }

    function obtener_usuario_vendedor($identificacion){
        
        $token_response_array = json_decode($this->get_jwt_token(),true);
        $http_code_auth = 0;
        $http_code_auth = intval($token_response_array['http_code']);
        if($http_code_auth >=200 && $http_code_auth <300){
        }else{
            $Status = $token_response_array['Status'];
            $Errors = $token_response_array['Errors'][0]['Message'];
            throw new Exception("SIIGO -  error en la autenticacion http_code: $http_code_auth, message: $Errors", 1);            
        }
               

        $url = "$this->empr_siigo_api_url/v1/users";

        $method = "GET";
        $body = "";
        $token      = $token_response_array['data']['token'];
        $token_type = $token_response_array['data']['type'];

        $header_array = $this->create_request_header($token,$token_type,["Partner-Id: ".$this->empr_siigo_partnerid]);

        $api_response = json_decode($this->api_request($url,$body,$token,$method,$header_array),true);
        $http_code = intval($api_response['http_code']);
        
        if($http_code >=200 && $http_code <300){
            $this->sincronizar_vendedor($identificacion, $api_response);
        }else{
            $Status_ = $api_response['Status'];
            $Errors_ = $api_response['Errors'][0]['Message'];
            throw new Exception("SIIGO -  error en obtener usuarios (vendedor) http_code: $http_code, message: $Errors_ data: ".json_encode($api_response,true), 1);
        }
        $this->obtener_cliente('0');

    }
    function obtener_usuario_vendedor_solo($identificacion){
        
        $token_response_array = json_decode($this->get_jwt_token(),true);
        $http_code_auth = 0;
        $http_code_auth = intval($token_response_array['http_code']);
        if($http_code_auth >=200 && $http_code_auth <300){
        }else{
            $Status = $token_response_array['Status'];
            $Errors = $token_response_array['Errors'][0]['Message'];
            throw new Exception("SIIGO -  error en la autenticacion http_code: $http_code_auth, message: $Errors", 1);            
        }
               

        $url = "$this->empr_siigo_api_url/v1/users";

        $method = "GET";
        $body = "";
        $token      = $token_response_array['data']['token'];
        $token_type = $token_response_array['data']['type'];

        $header_array = $this->create_request_header($token,$token_type,["Partner-Id: ".$this->empr_siigo_partnerid]);

        $api_response = json_decode($this->api_request($url,$body,$token,$method,$header_array),true);
        $http_code = intval($api_response['http_code']);
        
        if($http_code >=200 && $http_code <300){
            
            $this->sincronizar_vendedor($identificacion, $api_response);
        }else{
            $Status_ = $api_response['Status'];
            $Errors_ = $api_response['Errors'][0]['Message'];
            throw new Exception("SIIGO -  error en obtener usuarios (vendedor) http_code: $http_code, message: $Errors_ data: ".json_encode($api_response,true), 1);
        }

    }

    function obtener_cliente($identificacion){
        
        $token_response_array = json_decode($this->get_jwt_token(),true);
        $http_code_auth = 0;
        $http_code_auth = intval($token_response_array['http_code']);
        if($http_code_auth >=200 && $http_code_auth <300){
        }else{
            $Status = $token_response_array['Status'];
            $Errors = $token_response_array['Errors'][0]['Message'];
            throw new Exception("SIIGO -  error en la autenticacion (vendedor2) http_code: $http_code_auth, message: $Errors", 1);            
        }
               

        $url = "$this->empr_siigo_api_url/v1/customers";

        $method = "GET";
        $body = "";
        $token      = $token_response_array['data']['token'];
        $token_type = $token_response_array['data']['type'];

        $header_array = $this->create_request_header($token,$token_type,["Partner-Id: ".$this->empr_siigo_partnerid]);

        $api_response = json_decode($this->api_request($url,$body,$token,$method,$header_array),true);
        $http_code = intval($api_response['http_code']);
        
        if($http_code >=200 && $http_code <300){
            $this->sincronizar_cliente($identificacion, $api_response);
        }else{
            $Status_ = $api_response['Status'];
            $Errors_ = $api_response['Errors'][0]['Message'];
            throw new Exception("SIIGO -  error en obtener usuarios (siigo) http_code: $http_code, message: $Errors_ data: ".json_encode($api_response,true), 1);
        }


    }

    function request_siigo($endpoint,$method,$body){
        
        $token_response_array = json_decode($this->get_jwt_token(),true);
        $http_code_auth = 0;
        $http_code_auth = intval($token_response_array['http_code']);

        // print_r($token_response_array);exit;
        if($http_code_auth <200 || $http_code_auth >=300){
            $Status = $token_response_array['Status'];
            $Errors = $token_response_array['Errors'];
            $error_message = "Error en peticion: ";
            $count = 0;
            foreach ($Errors as $key => $error) {
                if($count>0){
                    $error_message .=", ";
                }
                $error_message .=$error['Message'];
            }            
            throw new Exception("SIIGO -  error en la autenticacion (request siigo) http_code: $http_code_auth, message: $error_message", 1);            
        }
               

        $url = $this->empr_siigo_api_url."$endpoint";

        $token      = $token_response_array['data']['token'];
        $token_type = $token_response_array['data']['type'];

        $header_array = $this->create_request_header($token,$token_type,["Partner-Id: ".$this->empr_siigo_partnerid]);

        $api_response = json_decode($this->api_request($url,$body,$token,$method,$header_array),true);
        $http_code = intval($api_response['http_code']);
        
        if($http_code >=200 && $http_code <300){
            return $api_response;
        }else{
            $api_response = $api_response['data'];
            $Status_ = $api_response['Status'];
            $Errors_ = $api_response['Errors'][0]['Message'];
            $Code = $api_response['Errors'][0]['Code'];
            $Params = $api_response['Errors'][0]['Params'][0];
            $Detail = $api_response['Errors'][0]['Detail'];

            throw new Exception("SIIGO -   message: $Errors_ <br><br> sent: $body",1);
        }

    }

    private function create_request_header($token,$token_type,$others_array){
        $header_array = array(
            'Accept: application/json',
            'Content-Type: application/json',
        );

        if (!empty($token) && !empty($token_type)) {
            array_push($header_array, "Authorization: $token_type $token");
        }

        foreach ($others_array as $key => $value) {
            array_push($header_array, "$value");
        }

        return $header_array;
    }

    private function api_request($url, $body, $token, $method, $header_array) {

        // si el metodo es vacio por defecto resa get
        $array_response = array();
        $method = $method ? $method : "GET";

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $data = <<<DATA
        $body
        DATA;

        if (!empty($body)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $resp = curl_exec($curl);


        if (!curl_errno($curl)) {
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $resp = json_decode($resp, true);
            switch ($http_code) {
                case 200:
                    $array_response = array(
                        "http_code" => "$http_code",
                        "data" => $resp,
                        "mensaje" => 'respuesta exitosa',
                    );
                    break;

                default:
                    $array_response = array(
                        "http_code" => "$http_code",
                        "data" => $resp,
                        "mensaje" => 'respuesta erronea',
                    );
                    $res_json = json_encode($array_response);
                    break;
            }
        } else {

            $errorMessage = curl_error($curl);
            $array_response = array(
                "http_code" => '000',
                "data" => '',
                "mensaje" => $errorMessage,
            );
            $res_json = json_encode($array_response);
        }

        curl_close($curl);

        return json_encode($array_response);
    }

    private function sincronizar_vendedor($identificaicon = '', $api_response=[]){
        $data = $api_response['data']['results'];

        if(!empty($data)){
            foreach ($data as $key => $usuario_siigo_indi) {
                $id_siigo             = $usuario_siigo_indi['id'];
                $username_siigo       = $usuario_siigo_indi['username'];
                $first_name_siigo     = $usuario_siigo_indi['first_name'];
                $last_name_siigo      = $usuario_siigo_indi['last_name'];
                $email_siigo          = $usuario_siigo_indi['email'];
                $active_siigo         = $usuario_siigo_indi['active'];
                $identification_siigo = $usuario_siigo_indi['identification'];
                // print_r($identification_siigo.'-');


                $sql_vend = "UPDATE saevend SET vend_cod_siigo = '$id_siigo' WHERE vend_ruc_vend = '$identification_siigo';";

                $this->oIfx->QueryT('BEGIN;');
                $this->oIfx->QueryT($sql_vend);
                $this->oIfx->QueryT('COMMIT;');
                $this->oIfx->Free();

            }
        }

    } 

    private function sincronizar_cliente($identificaicon = '', $api_response=[]){
        $data = $api_response['data']['results'];
        $aditional_specification1 = "  ";
        // $aditional_specification1 = " and (clpv_siigo_id is null or clpv_siigo_id = '') ";

        $aditional_specification2 = "  ";
        // $aditional_specification2 = " and (siigo_id is null or siigo_id = '') ";

        if(!empty($data)){
            foreach ($data as $key => $usuario_siigo_indi) {
                $id_siigo             = $usuario_siigo_indi['id'];
                $identification_siigo = $usuario_siigo_indi['identification'];


                $sql_saeclpv = "UPDATE saeclpv SET clpv_siigo_id = '$id_siigo' WHERE clpv_ruc_clpv = '$identification_siigo' $aditional_specification1 ;";
                $sql_contrato_clpv = "UPDATE isp.contrato_clpv SET siigo_id = '$id_siigo' WHERE ruc_clpv = '$identification_siigo' $aditional_specification2 ;";
                // print_r($sql_contrato_clpv);exit;

                $this->oIfx->QueryT('BEGIN;');
                $this->oIfx->QueryT($sql_saeclpv);
                $this->oIfx->QueryT($sql_contrato_clpv);
                $this->oIfx->QueryT('COMMIT;');
                $this->oIfx->Free();

            }
        }

    } 

    private function sincronizar_tipo_documento($data,$t_doc='FV'){
        $filter = " and tcmp_apl_fact = 'S' ";
        $aditional_specificaion = "  ";
        // $aditional_specificaion = " and (tcmp_siigo_id is null or tcmp_siigo_id = '' ) ";
        if ($t_doc =='NC'){
            $filter = " and tcmp_apl_ncre = 'S' ";
        }
        foreach ($data as $key => $tipo_doc) {
            $sql = "UPDATE saetcmp SET tcmp_siigo_id = '".$tipo_doc['id']."' WHERE tcmp_siigo_name = '".$tipo_doc['name']."' $aditional_specificaion $filter ;";

            $this->oIfx->QueryT('BEGIN;');
            $this->oIfx->QueryT($sql);
            $this->oIfx->QueryT('COMMIT;');
            $this->oIfx->Free();
        }
    }

    private function obtener_tipo_documento($t_doc = 'FV'){
        $endpoint = "/v1/document-types?type=$t_doc";
        $t_doc_responde = $this->request_siigo($endpoint,'GET','');
        $http_code = intval($t_doc_responde['http_code']);
        $data = $t_doc_responde['data'];
        $mensaje = $t_doc_responde['mensaje'];
        if($http_code>=200 && $http_code<300){

            $this->sincronizar_tipo_documento($data,$t_doc);


        }else{
            if ($http_code>0){
                $error_array = $data['Errors'];
                $count_error = 0; 
                foreach ($error_array as $key => $error) {
                    $mensaje .= $count_error>0?', '.$error:': '.$error;
                    $count_error++;
                }

            }
            
            throw new Exception("$mensaje $http_code", 1);
            
        }
    }

    private function sincronizar_forma_pago($data){
        foreach ($data as $key => $fpag) {
            $sql = "UPDATE saefpag SET fpag_siigo_id = '".$fpag['id']."' where fpag_siigo_name = '".$fpag['name']."'";
            
            $this->oIfx->QueryT('BEGIN;');
            $this->oIfx->QueryT($sql);
            $this->oIfx->QueryT('COMMIT;');
            $this->oIfx->Free();
        }
    }

    private function obtener_forma_pago(){
        $endpoint = "/v1/payment-types?document_type=FV";
        $t_doc_responde = $this->request_siigo($endpoint,'GET','');
        $http_code = intval($t_doc_responde['http_code']);
        $data = $t_doc_responde['data'];
        $mensaje = $t_doc_responde['mensaje'];
        if($http_code>=200 && $http_code<300){

            $this->sincronizar_forma_pago($data);


        }else{
            if ($http_code>0){
                $error_array = $data['Errors'];
                $count_error = 0; 
                foreach ($error_array as $key => $error) {
                    $mensaje .= $count_error>0?', '.$error:': '.$error;
                    $count_error++;
                }

            }
            
            throw new Exception("$mensaje $http_code", 1);
            
        }
    }

    private function obtener_cliente_siigo($identificacion=''){

        $param_query = $identificacion?'?identification='.$identificacion.'&branch_office=0':'';

        $endpoint = '/v1/customers'.$param_query;
        $customer_responde = $this->request_siigo($endpoint,'GET','');
        $http_code = intval($customer_responde['http_code']);
        $data = $customer_responde['data'];
        $mensaje = $customer_responde['mensaje'];
        if($http_code>=200 && $http_code<300){
            return $data['results'];
        }else{
            if ($http_code>0){
                $error_array = $data['Errors'];
                $count_error = 0; 
                foreach ($error_array as $key => $error) {
                    $mensaje .= $count_error>0?', '.$error:': '.$error;
                    $count_error++;
                }

            }            
            return [];
            
        }
    }

    public function consultar_cliente_db($ruc_clpv = '0', $id_clpv = '0'){

        $check_exist = $this->obtener_cliente_siigo($ruc_clpv);

        if(!empty($check_exist)){

            $method = 'PUT';

            $result = array(
                "data"=>array(
                    "results"=>array($check_exist[0])
                )
            );
            $this->sincronizar_cliente($ruc_clpv, $result);

        }else{
            $method = 'POST';            
        
            
            $aditional_specification = " ";
            // $aditional_specification = " and (siigo_id is null or siigo_id = '') ";
            $sql = "SELECT 
                        ticlpv.identificacion as identificacion_clpv,
                        ticlpa.identificacion as identificacion_pais,
                        ticlpa.siigo_id as iden_siigo_id,
                        ticlpa.siigo_id,
                        cl.clpv_siigo_id,
                        case when trim(ticlpv.identificacion) = 'NIT' THEN SUBSTRING(cc.ruc_clpv, 1, 9) else cc.ruc_clpv end as ruc_clpv,
                        
                        cl.clv_con_clpv,
                        * 
                    FROM isp.contrato_clpv cc
                    LEFT JOIN saeclpv cl on cc.id_clpv = cl.clpv_cod_clpv 
                    LEFT JOIN saevend ve on cc.vendedor = ve.vend_cod_vend
                    LEFT JOIN saeempr em on cc.id_empresa = empr_cod_empr	
                    LEFT JOIN saepais pa ON pa.pais_cod_pais = em.empr_cod_pais
                    LEFT JOIN comercial.tipo_iden_clpv ticlpv ON cl.clv_con_clpv = ticlpv.tipo
                    LEFT JOIN comercial.tipo_iden_clpv_pais ticlpa ON (pa.pais_codigo_inter = ticlpa.pais_codigo_inter or pa.pais_cod_pais = ticlpa.pais_cod_pais ) AND ticlpv.id_iden_clpv::text = ticlpa.id_iden_clpv::text
                    where cc.ruc_clpv like '%$ruc_clpv%' and cc.id_clpv = '$id_clpv' $aditional_specification";

            $client_data = [];
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $j = 0;
                    do {
                        $contrato_id            = $this->oIfx->f('id');
                        $id_clpv                = $this->oIfx->f('id_clpv');
                        $id_ciudad              = $this->oIfx->f('id_ciudad');
                        $id_pais                = $this->oIfx->f('id_pais');
                        $id_provincia           = $this->oIfx->f('id_provincia');
                        $id_canton              = $this->oIfx->f('id_canton');
                        $id_parroquia           = $this->oIfx->f('id_parroquia');
                        $apellido               = $this->oIfx->f('apellido');
                        $nombre                 = $this->oIfx->f('nombre');
                        $nom_clpv               = $this->oIfx->f('nom_clpv');
                        $ruc_clpv               = $this->oIfx->f('ruc_clpv');
                        $vendedor               = $this->oIfx->f('vendedor');
                        $direccion              = $this->oIfx->f('direccion');
                        $referencia             = $this->oIfx->f('referencia');
                        $id_barrio              = $this->oIfx->f('id_barrio');
                        $id_sector              = $this->oIfx->f('id_sector');
                        $detalle                = $this->oIfx->f('detalle');
                        $observaciones          = $this->oIfx->f('observaciones');
                        $latitud                = $this->oIfx->f('latitud');
                        $longitud               = $this->oIfx->f('longitud');
                        $telefono               = $this->oIfx->f('telefono');
                        $email                  = $this->oIfx->f('email');
                        $num_conjunto           = $this->oIfx->f('num_conjunto');
                        $vend_cod_siigo         = $this->oIfx->f('vend_cod_siigo');
                        $clv_con_clpv           = $this->oIfx->f('clv_con_clpv');//tipo documento
                        $identificacion_pais    = $this->oIfx->f('identificacion_pais');//tipo documento
                        $pais_codigo_inter      = $this->oIfx->f('pais_codigo_inter');//tipo documento
                        $siigo_id               = $this->oIfx->f('siigo_id');//tipo documento
                        $iden_siigo_id          = $this->oIfx->f('iden_siigo_id');//tipo documento EN SIIGO
                        $clpv_siigo_id          = $this->oIfx->f('clpv_siigo_id');//tipo documento
                        

                        $client_data['id_clpv']         = $id_clpv;
                        $client_data['type']            = 'Customer';
                        $client_data['identificacion']  = $ruc_clpv;
                        $client_data['name']            = $nombre;
                        $client_data['person_tipo']     = trim($nombre)?'Person':'Company';//Person; Company
                        $client_data['last_name']       = $apellido;
                        $client_data['comercial_name']  = $nom_clpv;

                        $client_data['address']         = $direccion;
                        $client_data['address']         = $direccion;
                        $client_data['address']         = $direccion;

                        $client_data['number']          = $telefono;
                        $client_data['email']           = $email;
                        $client_data['comment']         = $observaciones?$observaciones:"registro cliente";
                        $client_data['usuario_siigo']   = $vend_cod_siigo;
                        $client_data['iden_type']       = $clv_con_clpv;
                        $client_data['iden_pais']       = $identificacion_pais;
                        $client_data['cod_inter']       = $pais_codigo_inter;
                        $client_data['siigo_id']        = $siigo_id;
                        $client_data['clpv_siigo_id']   = $clpv_siigo_id;
                        $client_data['iden_siigo_id']   = $iden_siigo_id;


                        $j++;
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();
            $this->guardar_nuevo_cliente($client_data,$method);
        }
        
    }

    private function guardar_nuevo_cliente($client_data = [],$method = 'POST' ){


        $id_clpv            = $client_data['id_clpv']; 
        $type               = $client_data['type']; //Customer
        $person_type        = $client_data['person_tipo']; //Person;Company
        $identificacion     = $client_data['identificacion'];
        $name               =  $client_data['name'];
        $last_name          =  $client_data['last_name'];
        $comercial_name     =  $client_data['comercial_name'];
        $address            =  $client_data['address'];
        $country_code       =  $client_data['country_code']?$client_data['country_code']:'Co';
        $state_code         =  $client_data['state_code']?$client_data['state_code']:'11';
        $city_code          =  $client_data['city_code']?$client_data['city_code']:'11001';
        $postal_code        =  $client_data['postal_code']?$client_data['postal_code']:'11001';
        $number             =  $client_data['number'];
        $number2            =  $client_data['number2']?$client_data['number2']:$client_data['number'];
        $phone_ext          =  $client_data['phone_ext']?$client_data['phone_ext']:'000';
        $email              =  $client_data['email'];
        $phone_country_code =  $client_data['phone_country_code'];
        $comment            =  $client_data['comment']?$client_data['comment']:'NUEVO CLIENTE';
        $usuario_siigo      =  $client_data['usuario_siigo'];
        $cod_inter          =  $client_data['cod_inter'];
        $siigo_id           =  $client_data['siigo_id'];
        $siigo_id           =  $client_data['siigo_id'];
        $iden_pais          =  $client_data['iden_pais'];
        $iden_siigo_id      =  $client_data['iden_siigo_id'];

        if (empty($siigo_id)){
            // NIT
            // CEDULA
            // PASAPORTE
            // C. EXTRANJERIA
            switch($iden_pais){
                case "NIT":
                    $siigo_id = '31';
                    break;
                case "CEDULA":
                    $siigo_id = '13';
                    break;
                case "PASAPORTE":
                    $siigo_id = '41';
                    break;
                case "C. EXTRANJERIA":
                    $siigo_id = '22';
                    break;
                case "T. EXTRANJERIA":
                    $siigo_id = '21';
                    break;
            }
        }

        

        // 32, nit, 13 cedula //en documentacion api siigo
        $branch_office = 0;

        // print_r($client_data);exit;


        $json_array = array(
            "type"=> "".$this->BBVASpecialCharConvertion($type)."",
            "person_type"=> "".$this->BBVASpecialCharConvertion($person_type)."",
            "id_type"=> "".$this->BBVASpecialCharConvertion($siigo_id)."",
            "identification"=> "".$this->BBVASpecialCharConvertion($identificacion)."",
            "name"=>array(
                $person_type=="Person"?"".$this->BBVASpecialCharConvertion($name)."":"".$this->BBVASpecialCharConvertion($comercial_name)."",
                $person_type=="Person"?"".$this->BBVASpecialCharConvertion($last_name)."":""
            ),
            "commercial_name"=> "".$this->BBVASpecialCharConvertion($comercial_name)."",
            "branch_office"=> $this->BBVASpecialCharConvertion($branch_office),
            "active"=>true,
            "vat_responsible"=>true,

            "address"=>array(
                "address"=>"".$this->BBVASpecialCharConvertion($address)."",
                "city"=>array(
                    "country_code"=>"".$this->BBVASpecialCharConvertion($country_code)."",
                    "state_code"=>"".$this->BBVASpecialCharConvertion($state_code)."",
                    "city_code"=>"".$this->BBVASpecialCharConvertion($city_code).""
                ),
                "postal_code"=>"".$this->BBVASpecialCharConvertion($postal_code).""
            ),

            "phones"=> array(
                array(
                    "number"=>"".$this->BBVASpecialCharConvertion($number).""
                )
            ),
            "contacts"=> array(
                array(
                    "first_name"=>$person_type=="Person"?"".$this->BBVASpecialCharConvertion($name)."":"".$this->BBVASpecialCharConvertion(explode(' ',$comercial_name)[0])."",
                    "last_name"=>$person_type=="Person"?"".$this->BBVASpecialCharConvertion($last_name)."":"".$this->BBVASpecialCharConvertion(explode(' ',$comercial_name)[1])."",
                    "email"=>"".($email)."",
                    "phone"=>array(
                        "indicative"=>"".$this->BBVASpecialCharConvertion($cod_inter)."",
                        "number"=>"".$this->BBVASpecialCharConvertion($number2)."",
                        "extension"=>"".$this->BBVASpecialCharConvertion($phone_ext)."",
                    )
                )
            ),
            "comments"=> "".$this->BBVASpecialCharConvertion($comment).""

        );

        if (!empty($usuario_siigo)){
            array_push($json_array,array(
                "related_users"=> array(
                    "seller_id"=>$usuario_siigo,
                    "collector_id"=>$usuario_siigo,
                )
            ));
        }

        $body = json_encode($json_array);
        // print_r($body);exit;


        $endpoint = "/v1/customers";
        $endpoint = $method=='PUT'?$endpoint.'/'.$clpv_siigo_id:$endpoint;
        $t_doc_responde = $this->request_siigo($endpoint,$method,$body);
        $http_code = intval($t_doc_responde['http_code']);
        $data = $t_doc_responde['data'];
        $mensaje = $t_doc_responde['mensaje'];
        if($http_code>=200 && $http_code<300){

            $result = array(
                "data"=>array(
                    "results"=>array($data)
                )
            );
            $this->sincronizar_cliente($identificacion, $result);


        }else{
            if ($http_code>0){
                $error_array = $data['Errors'];
                $count_error = 0; 
                foreach ($error_array as $key => $error) {
                    $mensaje .= $count_error>0?', '.$error:': '.$error;
                    $count_error++;
                }

            }
            
            throw new Exception("$mensaje $http_code", 1);
            
        }
    }

    private function save_base64_pdf($endpoint = '',$ruta='',$nombre_archivo='', $formato = ''){

        try{
        
            $method = 'GET';
            $body = '';
            $result = $this->request_siigo($endpoint,$method,$body);                
            $http_code = intval($result['http_code']);
            $data = $result['data'];
            $mensaje = $result['mensaje'];


            if($http_code>=200 && $http_code<300){        
                $data_file = $data['base64']; 
                // print_r($data['base64']);exit;

                $pdf_decoded = base64_decode ($data_file);
                $extension = '.'. $formato;

                // $pdf = fopen ($ruta.'/'."test".$extension,'w');
                // fwrite ($pdf,$pdf_decoded);
                // fclose ($pdf);

                $ruta_pdf_save = $ruta.'/'.$nombre_archivo.$extension;
                file_put_contents($ruta_pdf_save, $pdf_decoded);
            }
        } catch (Exception $e) {
            // error al guardar pdf siigo
        }

        
    }
    function removeScpecialChat($strin_to_convert){
        try{
            $strin_to_convert       = eliminar_tildes($strin_to_convert);
            $strin_to_convert       = formatear_cadena($strin_to_convert);
            $strin_to_convert       = preg_replace('([^A-Za-z0-9 ])', '', $strin_to_convert);
            
        } catch (Exception $e) {
        }
        return $strin_to_convert;
        
    }

    function BBVASpecialCharConvertion($strin_to_convert)
    {
        $a = ['Ä', 'Á', 'À', 'Ã', 'Â'];
        $e = ['Ë', 'É', 'È', 'Ê'];
        $i = ['Ï', 'Í', 'Ì', 'Î'];
        $o = ['Ö', 'Ó', 'Ò', 'Õ', 'Ô'];
        $u = ['Ü', 'Ú', 'Ù', 'Û'];
        $n = ['±', 'Ñ'];
        $y = ['Ý'];

        $a2 = ['á', 'à', 'ã', 'â', 'ä'];
        $e2 = ['é', 'è', 'ê'];
        $i2 = ['í', 'ì', 'î', 'ï'];
        $o2 = ['ó', 'ò', 'õ', 'ô', 'ö'];
        $u2 = ['ú', 'ù', 'û', 'ü'];
        $n2 = ['ñ','¥'];
        $y2 = ['ÿ', 'ý'];



        $d = ['Ð.'];
        $s1 = ['"', ';', ',', '+'];
        $s2 = ['!', '#', '.', '$', '%', '/', '(', "\\", '¡', '¿', mb_convert_encoding('&#xB4;', 'UTF-8', 'HTML-ENTITIES'), '~', '[', '}', ']', '`', '<', '>', '_', ')', '{', '^', ':', '|', '°', '¬', '=', '?', 'º'];

        $strin_to_convert = str_replace($a, "A", $strin_to_convert);
        $strin_to_convert = str_replace($e, "E", $strin_to_convert);
        $strin_to_convert = str_replace($i, "I", $strin_to_convert);
        $strin_to_convert = str_replace($o, "O", $strin_to_convert);
        $strin_to_convert = str_replace($u, "U", $strin_to_convert);
        $strin_to_convert = str_replace($n, "N", $strin_to_convert);
        $strin_to_convert = str_replace($y, "Y", $strin_to_convert);

        $strin_to_convert = str_replace($a2, "a", $strin_to_convert);
        $strin_to_convert = str_replace($e2, "e", $strin_to_convert);
        $strin_to_convert = str_replace($i2, "i", $strin_to_convert);
        $strin_to_convert = str_replace($o2, "o", $strin_to_convert);
        $strin_to_convert = str_replace($u2, "u", $strin_to_convert);
        $strin_to_convert = str_replace($n2, "n", $strin_to_convert);
        $strin_to_convert = str_replace($y2, "y", $strin_to_convert);

        $strin_to_convert = str_replace($d, "D", $strin_to_convert);
        $strin_to_convert = str_replace($s1, " ", $strin_to_convert);
        $strin_to_convert = str_replace($s2, " ", $strin_to_convert);

        return $this->removeScpecialChat($strin_to_convert);
    }

    function obtener_parametros($empresa_id,$sucursal_id,$nse,$tdoc='FAC',$fecha_doc){

        $fecha_servidor = date("Y-m-d");


        $sql = "select
                    COALESCE(para_sec_usu,'N') as para_sec_usu, 
                    para_pro_bach ,
                    para_fac_cxc, 
                    COALESCE(para_sec_fac::INTEGER,0) as para_sec_fac,
                    para_pre_fact, 
                    para_fac_trans, 
                    para_cod_tarj, 
                    para_punt_emi, 
                    para_ndb_cxc 
                from saepara
                where para_cod_empr = '$empresa_id'
                and para_cod_sucu = '$sucursal_id'";

        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {
                $j = 0;
                do {
                    $para_sec_usu   = $this->oIfx->f('para_sec_usu');
                    $para_pro_bach  = $this->oIfx->f('para_pro_bach');
                    $para_fac_cxc   = $this->oIfx->f('para_fac_cxc');
                    $para_sec_fac   = $this->oIfx->f('para_sec_fac');
                    $para_pre_fact  = $this->oIfx->f('para_pre_fact');

                    $para_fac_trans = $this->oIfx->f('para_fac_trans');
                    $para_cod_tarj  = $this->oIfx->f('para_cod_tarj');
                    $para_punt_emi  = $this->oIfx->f('para_punt_emi');
                    $para_ndb_cxc   = $this->oIfx->f('para_ndb_cxc');

                } while ($this->oIfx->SiguienteRegistro());
            }
        }
        $this->oIfx->Free();

        if($para_punt_emi == 'S'){
            // emifa activado
            $sql = "SELECT 
                        emifa_auto_emifa, 
                        emifa_auto_desde, 
                        emifa_auto_hasta, 
                        emifa_fec_ini, 
                        emifa_fec_fin 
                    from saeemifa 
                    where 
                        emifa_tip_doc='$tdoc' and 
                        emifa_est_emifa='S' and 
                        emifa_cod_pto='$nse' and 
                        emifa_fec_ini>='$fecha_doc' and
                        emifa_fec_fin <='$fecha_doc' ";

        }else{
            // aufa activado

             $sql = "SELECT 
                        aufa_nse_fact,aufa_nau_fact,aufa_ffi_fact FROM saeaufa 
                    WHERE 
                        aufa_cod_empr = $empresa_id and 
                        aufa_cod_sucu = $sucursal_id and 
                        aufa_est_fact = 'A' and 
                        aufa_ffi_fact >= '$fecha_doc' and 
                        aufa_fin_fact <= '$fecha_doc'";

        }

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