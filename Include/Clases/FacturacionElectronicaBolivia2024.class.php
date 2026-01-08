<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class FacturacionElectronicaBolivia2024
{
    private $oIfx;
    private $oCon;
    private $oCon1;

    public $empr_cod_empr = '';
    public $empr_ruc_empr = '';
    public $empr_nom_empr = '';
    public $empr_dir_empr = '';
    public $empr_mai_empr = '';
    public $empr_tel_resp = '';
    public $empr_token_api = '';
    public $empr_cod_api_fac = '';
    public $prov_des_prov = '';
    public $ciud_nom_ciud = '';
    public $parr_des_parr = '';
    public $empr_cpo_empr = '';
    public $pcon_mon_base = '';
    public $pcon_seg_mone = '';

    ///FACTURAS
    public $url_ws_recepcionFactura = 'https://prueba.apifacturadorlinea.tecnocloud.com/apifacturadorlinea/public/facturacion_electronica/recepcionFactura';
    public $url_ws_verificarFactura = 'https://prueba.apifacturadorlinea.tecnocloud.com/apifacturadorlinea/public/cliente/verificarFacturaByIdEntidadCliente';
    public $url_ws_recuperarDatosFacturaCuf = 'https://prueba.apifacturadorlinea.tecnocloud.com/apifacturadorlinea/public/cliente/recuperarFacturaCufCliente';
    public $url_ws_anulacionFactura = 'https://prueba.apifacturadorlinea.tecnocloud.com/apifacturadorlinea/public/facturacion_electronica/anulacionFactura';
    public $url_ws_descargarFacturaPdf = 'https://prueba.apifacturadorlinea.tecnocloud.com/apifacturadorlinea/public/facturacion_electronica/descargarFacturaPdf';
   
    //NOTAS DE CREDITO - DEBITO
    public $url_ws_recepcionNotaCreditoDebito = 'https://prueba.apifacturadorlinea.tecnocloud.com/apifacturadorlinea/public/nota_electronica/recepcionNotaCreditoDebito';
    public $url_ws_anulacionNCND = 'https://prueba.apifacturadorlinea.tecnocloud.com/apifacturadorlinea/public/nota_electronica/anulacionNotaFiscalCreditoDebito';
    public $url_ws_recuperarNotaCufCliente = 'https://prueba.apifacturadorlinea.tecnocloud.com/apifacturadorlinea/public/cliente/recuperarNotaCufCliente';
    public $url_ws_descargarNotaCreditoDebitoPdf = 'https://prueba.apifacturadorlinea.tecnocloud.com/apifacturadorlinea/public/nota_electronica/descargarNotaCreditoDebitoPdf';

    function __construct($oIfx, $oCon, $oCon1, $idempresa)
    {
        $this->oIfx = $oIfx;
        $this->oCon = $oCon;
        $this->oCon1 = $oCon1;

        $sql = "SELECT  empr_ruc_empr, empr_nom_empr, empr_dir_empr, empr_mai_empr, 
                    empr_tel_resp, empr_token_api_fac, empr_cod_empr, empr_cod_prov,
                    empr_cod_ciud, empr_cod_parr, empr_cpo_empr, empr_cod_api_fac
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
                    $this->empr_token_api = $oCon->f('empr_token_api_fac');
                    $this->empr_cod_api_fac = $oCon->f('empr_cod_api_fac');
                    $empr_cod_prov = $oCon->f('empr_cod_prov');
                    $empr_cod_ciud = $oCon->f('empr_cod_ciud');
                    $empr_cod_parr = $oCon->f('empr_cod_parr');
                    $this->empr_cpo_empr = $oCon->f('empr_cpo_empr');
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

        if (!empty($empr_cod_parr)) {
            $sql = "SELECT parr_des_parr from saeparr where parr_cod_parr = $empr_cod_parr ";
            $this->parr_des_parr = consulta_string($sql, 'parr_des_parr', $oCon, 0);
        } else {
            $this->parr_des_parr = "";
        }
    }
    /**
     * ANULACION DE FACTURAS 
     */
function AnulacionFactura($fact_auto_sri,$motivo){

    try {
        session_start();

        $headers = array(
            "Content-Type:application/json",
            "X-CodCliente:$this->empr_cod_api_fac",
            "X-Authorization:$this->empr_token_api"
        );

        $data_envio = array(
            "codigoMotivo" => $motivo, 
            "cuf" => $fact_auto_sri
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->url_ws_anulacionFactura);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_envio));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta = curl_exec($ch);


        if (!curl_errno($ch)) {
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $data_json = json_decode($respuesta);
          


            switch ($http_code) {

                case 200:
                    $msj="OK";
                break;

                default:
                $array=$data_json->error;
                $msj='';
                foreach($array as $val){
                    if(!empty($val)){
                        $msj.=$val.' ';  
                    }
                }
            }
        }
        else {
            $errorMessage = curl_error($ch);
            $msj="Hubo un error no se puede conectar al WebService ($errorMessage)";
        }
    
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }

    return $msj;
}
    /**
     * ANULACION DE FACTURAS 
     */
    function AnulacionNotaCreditoDebito($ncre_auto_sri,$motivo){

        try {
            session_start();
    
            $headers = array(
                "Content-Type:application/json",
                "X-CodCliente:$this->empr_cod_api_fac",
                "X-Authorization:$this->empr_token_api"
            );
    
            $data_envio = array(
                "codigoMotivo" => $motivo, 
                "cuf" => $ncre_auto_sri
            );
    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $this->url_ws_anulacionNCND);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_envio));
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $respuesta = curl_exec($ch);
    
    
            if (!curl_errno($ch)) {
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $data_json = json_decode($respuesta);
              
    
    
                switch ($http_code) {
    
                    case 200:
                        $msj="OK";
                    break;
    
                    default:
                    $array=$data_json->error;
                    $msj='';
                    foreach($array as $val){
                        if(!empty($val)){
                            $msj.=$val.' ';  
                        }
                    }
                }
            }
            else {
                $errorMessage = curl_error($ch);
                $msj="Hubo un error no se puede conectar al WebService ($errorMessage)";
            }
        
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    
        return $msj;
    }

    /**
     * FACTURAS  ENVIO API FACTURACION BOLIVIA
     */
    function EnviarFactura($fact_cod_fact)
    {


        try {
            session_start();
            $id_usuario = $_SESSION['U_ID'];
            $idempresa = $_SESSION['U_EMPRESA'];

            //ARRAY UNIDADES - 
            $sql = "select u.unid_cod_unid, f.unid_cod_clas, f.unid_des_unid from saeunid u
            inner join comercial.catalogo_unidades f on
            u.unid_cod_alias = f.id";
            unset($array_unidad);
            unset($array_unidad_desc);
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        if (!empty($this->oIfx->f('unid_cod_clas')) || $this->oIfx->f('unid_cod_clas') != '') {
                            $array_unidad[$this->oIfx->f('unid_cod_unid')] = $this->oIfx->f('unid_cod_clas');
                            $array_unidad_desc[$this->oIfx->f('unid_cod_unid')] = $this->oIfx->f('unid_des_unid');
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();


            //CONTROL FORMATOS PERSONALIZADOS
            $sql = "select ftrn_ubi_web from saeftrn  where ftrn_cod_empr=$idempresa and ftrn_des_ftrn = 'FACTURA' and ftrn_cod_modu=7 and (ftrn_ubi_web is not null or ftrn_ubi_web != '')";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $ubi =  $this->oIfx->f('ftrn_ubi_web');
                    if (!empty($ubi)) {
                        include_once('../../' . $ubi . '');
                    } else {
                        //FORMATO ESTANDAR BOLIVIA
                        include_once('../../Include/Formatos/comercial/factura_bolivia.php');
                    }
                } else {
                    //FORMATO ESTANDAR BOLIVIA
                    include_once('../../Include/Formatos/comercial/factura_bolivia.php');
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
            fac.fact_cm2_fact,
            fac.fact_dsg_valo
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
                        $fact_dsg_valo  = $this->oIfx->f('fact_dsg_valo');

                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);


                        if ($fact_cm4_fact == null) {
                            $fact_cm4_fact = 0;
                        }
                        $fact_tot_fact = $fact_con_miva + $fact_iva + $fact_sin_miva + $fact_val_irbp - $fact_dsg_valo ;




                        //NIT
                        if (intval($tipo_doc) == 1) {
                            $tipo_docu = '5';
                            // $tipo_envio = '01';
                        }
                        //CEDULA 
                        else if (intval($tipo_doc) == 2) {
                            $tipo_docu = '1';
                            //$tipo_envio = '03';
                        }
                        //PASAPORTE 
                        else if (intval($tipo_doc) == 3) {
                            $tipo_docu = '3';
                            //$tipo_envio = '03';
                        }
                        //EXTRANJERIA
                        else if (intval($tipo_doc) == 4) {
                            $tipo_docu = '2';
                            //$tipo_envio = '03';
                        } else {
                            $tipo_docu = '4';
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            if ($this->pcon_seg_mone == $fact_cod_mone) {
                $fact_tot_fact = $fact_tot_fact / $fact_val_tcam;
            }

            $valor_mon_ext = $fact_tot_fact;







            $sql = "SELECT mone_sgl_mone, mone_des_mone, cmone_cod_clas from saemone 
            inner join comercial.catalogo_monedas on
            mone_cod_api= id
            where mone_cod_mone = $fact_cod_mone ";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $codigo_moneda = $this->oIfx->f('cmone_cod_clas');
                        $mone_sgl_mone = $this->oIfx->f('mone_sgl_mone');
                        $mone_des_mone = $this->oIfx->f('mone_des_mone');
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();




            //CODIGO DE SUCURSAL
            $codigoSucursal = 0;
            $sqlsuc = "select sucu_alias_sucu from saesucu where sucu_cod_empr= $idempresa and sucu_cod_sucu=$fact_cod_sucu";
            if ($this->oIfx->Query($sqlsuc)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $codigoSucursal = $this->oIfx->f('sucu_alias_sucu');
                }
            }



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

                //JSON DE LA FACTURA COMPRA-VENTA

                //TIPOS DE DOCUMENTO DE IDENTIDAD

                /*
                {
    "result": true,
    "function": "sincronizarParametricaTipoDocumentoIdentidad",
    "data": [
        {
            "codigoClasificador": 1,
            "descripcion": "CI - CEDULA DE IDENTIDAD"
        },
        {
            "codigoClasificador": 2,
            "descripcion": "CEX - CEDULA DE IDENTIDAD DE EXTRANJERO"
        },
        {
            "codigoClasificador": 5,
            "descripcion": "NIT - NÚMERO DE IDENTIFICACIÓN TRIBUTARIA"
        },
        {
            "codigoClasificador": 3,
            "descripcion": "PAS - PASAPORTE"
        },
        {
            "codigoClasificador": 4,
            "descripcion": "OD - OTRO DOCUMENTO DE IDENTIDAD"
        }
    ],
    "code": 200
}
                */

                //DATOS DE LA FORMA DE PAGO - SE PUEDE TOMAR DEL ALIAS

                $sql = "SELECT
                fx.fxfp_cod_fpag,
                fx.fxfp_val_fxfp,
                fx.fxfp_fec_fin,
                fx.fxfp_cot_fpag,
                mp.mpag_cod_clas
                FROM
                saefxfp fx,saefpag fp
                inner join  comercial.catalogo_metodos_pago mp on
                fp.fpag_cod_api = mp.id
                WHERE 
                fp.fpag_cod_fpag = fx.fxfp_cod_fpag and
                fp.fpag_cod_empr=$idempresa and
                fx.fxfp_cod_fact = $fact_cod_fact ;";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {
                            $mpag_cod_api  = $this->oIfx->f('mpag_cod_clas');

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
                            } else {
                                $tipo = 'Contado';
                            }
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                //DATOS DEL USUARIO

                $sqlus = "select usuario_user from comercial.usuario where usuario_id=$id_usuario";
                if ($this->oIfx->Query($sqlus)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $usuario_user = $this->oIfx->f('usuario_user');
                    }
                }


                //GENERACION DE LEYENDA ALEATORIA

                $sqley = "select * from comercial.catalogo_leyendas_factura";
                $array_leyenda = array();
                if ($this->oIfx->Query($sqley)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        do {

                            $id_leyenda = $this->oIfx->f('id');

                            array_push($array_leyenda, $id_leyenda);
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                $clave_aleatoria = array_rand($array_leyenda, 1);
                if (empty($clave_aleatoria)) {
                    $clave_aleatoria = 0;
                }
                $leyenda_factura = '';
                $sqley = "select ley_des_ley from comercial.catalogo_leyendas_factura where id=$clave_aleatoria";
                if ($this->oIfx->Query($sqley)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $leyenda_factura = $this->oIfx->f('ley_des_ley');
                    }
                }

                //COMENTAR 
                //$fpag_cod_alias=1;



                $data['cabecera'] =  [

                    "nitEmisor" => $this->empr_ruc_empr,

                    "razonSocialEmisor" => $this->empr_nom_empr,

                    "municipio" => $this->prov_des_prov,

                    "telefono" => $this->empr_tel_resp,

                    "codigoSucursal" => $codigoSucursal, //TIPO NUMERICO SE DEBE CONFIGIURAR EN EL CAMPO ALIAS DE LA CONFIGURACION SUCURSAL

                    "direccion" => $this->empr_dir_empr,

                    "codigoPuntoVenta" => "0", //VALIDAR- POR SUCURSAL PUEDE EXISTIR OTRO PUNTO DE VENTA

                    "nombreRazonSocial" => $fact_nom_cliente,

                    "codigoTipoDocumentoIdentidad" => $tipo_docu,

                    "numeroDocumento" => $fact_ruc_clie,

                    "complemento" => "",

                    "codigoCliente" => $fact_cod_clpv,

                    "codigoMetodoPago" => $mpag_cod_api,

                    "numeroTarjeta" => "", ///4PRIMEROSDIGITOS-XXXX-4ULTIMOSDIGITOS

                    "montoTotal" => round($fact_tot_fact, 2, PHP_ROUND_HALF_UP),

                    "montoTotalSujetoIva" => round($fact_tot_fact, 2, PHP_ROUND_HALF_UP), //VARIA EN CASO DE QUE EXISTA UN DESCUENTO ADICIONAL QUE SE RESTA AL MONTO TOTAL

                    "codigoMoneda" => $codigo_moneda,

                    "tipoCambio" => "1",

                    "montoTotalMoneda" => round($valor_mon_ext, 2, PHP_ROUND_HALF_UP), //ESE MONTO SE COLOCARIA EN LA MONEDA DE CAMBIO EJM: DOLAR

                    "montoGiftCard" => "0", //VALIDAR - SIMILAR A UN DESCUENTO GENERAL
                    //round(($fact_dsg_valo + $fact_cm4_fact), 2, PHP_ROUND_HALF_UP)
                    "descuentoAdicional" => "0",

                    "codigoExcepcion" => "0",

                    "cafc" => "",
                    //VALIDAR
                    "leyenda" => $leyenda_factura, //COLOCAR DE FORMA ALEATORIA - GAURDAR DATOS EN LOCAL

                    "usuario" => $usuario_user,

                    "codigoDocumentoSector" => "1", //COMPRA VENTA  - NOTA DE DEBITO O CREDITO 24

                ];

                //var_dump($data);exit;                




                //////////////////////DETALLES SERVICIOS/PRODUCTOS//////////////////////



                $sql = "SELECT dfac_cant_dfac, dfac_cod_prod, dfac_det_dfac, dfac_mont_total, dfac_por_iva, dfac_exc_iva,dfac_cod_lote, dfac_cod_unid, 
                dfac_precio_dfac, dfac_des1_dfac, dfac_des2_dfac, dfac_por_dsg  FROM saedfac WHERE dfac_cod_fact = $fact_cod_fact";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {
                            $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                            $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                            $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                            $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                            $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                            $dfac_cod_lote = $this->oIfx->f('dfac_cod_lote');
                            $dfac_cod_unid = $this->oIfx->f('dfac_cod_unid');
                            $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');

                            $dfac_des1_dfac = $this->oIfx->f('dfac_des1_dfac');
                            $dfac_des2_dfac = $this->oIfx->f('dfac_des2_dfac');
                            $dfac_por_dsg = $this->oIfx->f('dfac_por_dsg');


                            $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                            $porcentaje_descuento=$dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                            if ($descuento > 0){
                                $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                                $descuento =round($descuento, 2, PHP_ROUND_HALF_UP);
                            }
                            else{
                                $descuento = '0';
                            }
                           

                            if (round($dfac_por_iva, 2) > 0) {
                                $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                                $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;
                                $dfac_mont_total = $dfac_mont_total + $valor_iva;
                                $dfac_precio_dfac = $dfac_precio_dfac + ($valor_iva/$dfac_cant_dfac);
                            }

                            /*if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                            }*/

                            
                            //$precio_uni = $dfac_mont_total * $porcentaje_iva;
                            


                            if (empty($dfac_det_dfac)) {
                                $dfac_det_dfac = 'Sin detalle';
                            }

                            //consulta codigo actividad y codigo de prodcuto registrado en el API

                            $sqlproapi = "select lis_cod_act, lis_cod_prod from comercial.catalogo_productos_servicios 
                            where id =(select COALESCE(prod_cod_api,0) as codigo from saeprod where prod_cod_prod='$dfac_cod_prod' and prod_cod_empr=$idempresa)";
                            $codigo_actividad = '';
                            $codigo_prod_api = '';
                            if ($this->oCon->Query($sqlproapi)) {
                                if ($this->oCon->NumFilas() > 0) {

                                    $codigo_actividad = $this->oCon->f('lis_cod_act');;
                                    $codigo_prod_api =  $this->oCon->f('lis_cod_prod');;
                                }
                            }



                            $data['detalle'][$j++] =
                                [
                                    "actividadEconomica" => $codigo_actividad, //VALIDAR

                                    "codigoProductoSin" => $codigo_prod_api, //CODIGO TOMADO DEL CATALOGO DE PRODCUTOS- SERVICIOS

                                    "codigoProducto" => $dfac_cod_prod, //CORRESPONDE AL CODIGO DE PRODUCTO DEL SISTEMA

                                    "descripcion" => $dfac_det_dfac,

                                    "cantidad" => floatval($dfac_cant_dfac),

                                    "unidadMedida" => $array_unidad[$dfac_cod_unid],

                                    "unidadMedidaTexto" => $array_unidad_desc[$dfac_cod_unid],

                                    "precioUnitario" => round($dfac_precio_dfac, 2, PHP_ROUND_HALF_UP),

                                    "montoDescuento" => $descuento, //VALIDAR

                                    "subTotal" => round($dfac_mont_total, 2, PHP_ROUND_HALF_UP),

                                    "numeroSerie" => null,

                                    "numeroImei" => null,
                                ];
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                //var_dump($data);exit;

                /////CORREO DE PRUEBA COMENTAR
                //$fact_email_clpv = 'andres.garcia@sisconti.com';


                /**
                 * EMPIEZA ENVIO
                 */


                ////VALIDACION PARA FACTURAS YA ENVIADAS


                $headers = array(
                    "Content-Type:application/json",
                    "X-CodCliente:$this->empr_cod_api_fac"
                );

                $data_envio = array(

                    "idEntidadCliente" => $fact_cod_fact
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $this->url_ws_verificarFactura);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_envio));
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);



                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);
                    //var_dump($data_json);exit;


                    switch ($http_code) {
                            //FACTURA YA SE ENCUENTRA REGISTRADA EN EL API  
                        case 200:
                            $result_txt = 'Comprobante ya enviado.';
                            $result = array(
                                'div_button' => $div_button,
                                'result_ws' => $result_txt,
                                'result_email' => '',
                            );


                            break;
                            //FACTURA NO ENVIADA AL API SE PROCEDE AL ENVIO DE DATOS
                        case 404:


                            $headers = array(
                                "Content-Type:application/json",
                                "X-CodCliente:$this->empr_cod_api_fac",
                                "X-Authorization:$this->empr_token_api"
                            );

                            //JSON RECEPCION FACTURA FUERA DE LINEA

                            $data_servicio = array(

                                "codigoSucursal" =>  $codigoSucursal,

                                "codigoPuntoVenta" =>  "0",

                                "codigoDocumentoSector" =>  "1", //

                                "tipoFacturaDocumento" =>  1, //VALIDAR  EN CASO DE NOTA DEBITTO - CREDITO TOMARIA EL 2

                                "datosFactura" =>  $data,

                                "email" =>  $fact_email_clpv, //EL DEL CLIENTE FINAL 
                                "idEntidadCliente" => $fact_cod_fact,
                                "tipoGeneracionNroFactura" =>  1, //API DEVUELVE EL NUMERO DE FACTURA - CUIS 
                                "fueraLinea" =>  1, // 1 EN LINEA - 2 FUERA DE LINEA 
                            );

                            //AL AUTORIZAR SE DEBERIA GUARDAR:
                            //CUF 
                            //ESTADO RECPECIONADA
                            //NRO FACTURA QUE OTORGA EL API 
                            //FECHA DE EnviarGuiasRemision

                            //FORMATO
                            //CUF ES EL CODIGO DE AUTOIRZACION

                            ///VALIDACION FACTURAS ENVIADAS
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_URL, $this->url_ws_recepcionFactura);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_servicio));
                            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $respuesta = curl_exec($ch);

                            if (!curl_errno($ch)) {
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $data_json = json_decode($respuesta, true);

                                switch ($http_code) {
                                    case 200:
                                        try {


                                            $this->oIfx->QueryT('BEGIN;');

                                            $cuf =          $data_json['data']['cuf'];
                                            $emision =      $data_json['data']['emision'];
                                            $fechaEmision = $data_json['data']['fechaEmision'];


                                            $nombre_documento = $this->empr_ruc_empr . '-' . $fact_nse_fact . '-' . $fact_num_preimp;


                                            $ruta_pdf = 'upload/pdf/fac_' . $nombre_documento . '.pdf';

                                            //FORMATO PERSONALIZADO - BOLIVIA
                                            reporte_factura_personalizado($fact_cod_fact, $nombre_documento, $fact_cod_sucu, $ruta_pdf);

                                            //GENERAR ARCHIVO XML
                                            $data_cuf = array(
                                                "cuf" =>  $cuf
                                            );

                                            $this->GenerarXml($headers, $nombre_documento, $data_cuf, 1);


                                            $div_button = '<div class="btn-group" role="group" aria-label="...">
                                            <a href="upload/xml/fac_' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                                <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                            </a>
                                            <a href="upload/pdf/fac_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                                <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                            </a>
                                        </div>';

                                            $sql_update = "UPDATE saefact SET fact_aprob_sri = 'S',fact_auto_sri='$cuf', fact_fech_sri='$fechaEmision',  fact_user_sri=$id_usuario,
                                            fact_leye_fact= '$leyenda_factura', fact_tip_emis='$emision' WHERE fact_cod_fact = $fact_cod_fact ;";

                                            $this->oIfx->QueryT($sql_update);
                                            $this->oIfx->QueryT('COMMIT;');

                                            $result_txt = $data_json['data']['estado'];

                                            $result = array(
                                                'div_button' => $div_button,
                                                'result_ws' => $result_txt,
                                                'result_email' => 'Autorizado (El correo fue enviado al cliente)',
                                            );
                                        } catch (Exception $e) {
                                            $this->oIfx->QueryT('ROLLBACK;');
                                            throw new Exception($e->getMessage());
                                        }
                                        break;
                                    case 422:
                                        $error_api = $data_json['error'];
                                        $detalle_error = '';
                                        foreach ($error_api as $info) {
                                            $detalle_error .= $info . ' ';
                                        }
                                        $error_sri = substr($detalle_error, 0, 255);
                                        $this->oIfx->QueryT('BEGIN;');
                                        $sql_update = "UPDATE saefact SET fact_erro_sri = '$error_sri' WHERE fact_cod_fact = $fact_cod_fact";
                                        $this->oIfx->QueryT($sql_update);
                                        $this->oIfx->QueryT('COMMIT;');

                                        throw new Exception($detalle_error);
                                        break;
                                }
                            } else {
                                $errorMessage = curl_error($ch);
                                throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
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

    function GenerarXml($headers, $nombre_documento, $data, $tipo_documento)
    {


        switch ($tipo_documento) {
            //FACTURAS
            case 1:
                $url_xml= $this->url_ws_recuperarDatosFacturaCuf;
                $ruta_xml = 'upload/xml/fac_' . $nombre_documento . '.xml';
                break;
            //NOTAS DE CREDITO
            case 3:
                $url_xml= $this->url_ws_recuperarNotaCufCliente;
                $ruta_xml = 'upload/xml/cred_' . $nombre_documento . '.xml';
                
                break;

        }


        //GENERA XML
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url_xml);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta = curl_exec($ch);


        $data_json = json_decode($respuesta);

        $data_xml = $data_json->data->xml;


        file_put_contents($ruta_xml, $data_xml);

        return true;
    }

    //RECUPERAR PDF FACTURAS - NOTAS DE CREDITO

    function recuperarPdf($nombre_documento, $data, $tipo_documento)
    {


        switch ($tipo_documento) {
            //FACTURAS
            case 1:
                $url_pdf= $this->url_ws_descargarFacturaPdf;               
                break;
            //NOTAS DE CREDITO
            case 3:
                $url_pdf= $this->url_ws_descargarNotaCreditoDebitoPdf;
                break;
        }

        $headers = array(
            "Content-Type:application/json",
            "X-CodCliente:$this->empr_cod_api_fac"
            
        );

        //GENERA XML
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL,  $url_pdf);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta = curl_exec($ch);


        $data_json = json_decode($respuesta);


        $data_pdf = $data_json->data;
        


        //file_put_contents($ruta_xml, $data_xml);

        $respuesta = array(
            "ruta" => $data_pdf,
            "nombre_archivo" => $nombre_documento
        );

        return $respuesta;
    }

//RECUPERAR XML FACTURAS - NOTAS DE CREDITO

function recuperarXml($nombre_documento, $data, $tipo_documento)
{


    switch ($tipo_documento) {
        //FACTURAS
        case 1:
            $url_xml= $this->url_ws_recuperarDatosFacturaCuf;
            $ruta_xml = 'upload/xml/fac_' . $nombre_documento . '.xml';
            break;
        //NOTAS DE CREDITO
        case 3:
            $url_xml= $this->url_ws_recuperarNotaCufCliente;
            $ruta_xml = 'upload/xml/cred_' . $nombre_documento . '.xml';
            break;
    }

    $headers = array(
        "Content-Type:application/json",
        "X-CodCliente:$this->empr_cod_api_fac"
        
    );

    //GENERA XML
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_URL,  $url_xml);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $respuesta = curl_exec($ch);


    $data_json = json_decode($respuesta);

    $data_xml = $data_json->data->xml;
    


    file_put_contents($ruta_xml, $data_xml);

    $respuesta = array(
        "ruta" => $ruta_xml,
        "nombre_archivo" => $nombre_documento
    );

    return $respuesta;
}



    /**
     * NOTAS DE CREDITO ENVIO AL API FACTURACION
     */

    function EnviarNotaCredito($ncre_cod_ncre)
    {
        try {

            session_start();
            $id_usuario = $_SESSION['U_ID'];

            $idempresa = $_SESSION['U_EMPRESA'];


            //ARRAY UNIDADES - 
            $sql = "select u.unid_cod_unid, f.unid_cod_clas, f.unid_des_unid from saeunid u
            inner join comercial.catalogo_unidades f on
            u.unid_cod_alias = f.id";
            unset($array_unidad);
            unset($array_unidad_desc);
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        if (!empty($this->oIfx->f('unid_cod_clas')) || $this->oIfx->f('unid_cod_clas') != '') {
                            $array_unidad[$this->oIfx->f('unid_cod_unid')] = $this->oIfx->f('unid_cod_clas');
                            $array_unidad_desc[$this->oIfx->f('unid_cod_unid')] = $this->oIfx->f('unid_des_unid');
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();



            //CONTROL FORMATOS PERSONALIZADOS
            $sql = "select ftrn_ubi_web from saeftrn  where ftrn_cod_empr=$idempresa and ftrn_des_ftrn = 'FACTURA' and ftrn_cod_modu=7 and (ftrn_ubi_web is not null or ftrn_ubi_web != '')";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $ubi =  $this->oIfx->f('ftrn_ubi_web');
                    if (!empty($ubi)) {
                        include_once('../../' . $ubi . '');
                        $ctrl_formato++;
                    } else {
                        //FORMATO ESTANDAR BOLIVA
                        include_once('../../Include/Formatos/comercial/factura_bolivia.php');
                    }
                } else {
                    //FORMATO ESTANDAR BOLIVIA
                    include_once('../../Include/Formatos/comercial/factura_bolivia.php');
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


                        if ($fact_cm4_fact == null) {
                            $fact_cm4_fact = 0;
                        }
                        $fact_tot_fact = $fact_con_miva + $fact_iva + $fact_sin_miva + $fact_val_irbp - $fact_dsg_valo;




                        //NIT
                        if (intval($tipo_doc) == 1) {
                            $tipo_docu = '5';
                            // $tipo_envio = '01';
                        }
                        //CEDULA 
                        else if (intval($tipo_doc) == 2) {
                            $tipo_docu = '1';
                            //$tipo_envio = '03';
                        }
                        //PASAPORTE 
                        else if (intval($tipo_doc) == 3) {
                            $tipo_docu = '3';
                            //$tipo_envio = '03';
                        }
                        //EXTRANJERIA
                        else if (intval($tipo_doc) == 4) {
                            $tipo_docu = '2';
                            //$tipo_envio = '03';
                        } else {
                            $tipo_docu = '4';
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            if ($this->pcon_seg_mone == $fact_cod_mone) {
                $fact_tot_fact = $fact_tot_fact / $fact_val_tcam;
            }

            $valor_mon_ext = $fact_tot_fact;


            $sql = "SELECT mone_sgl_mone, mone_des_mone, cmone_cod_clas from saemone 
            inner join comercial.catalogo_monedas on
            mone_cod_api= id
            where mone_cod_mone = $fact_cod_mone ";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $codigo_moneda = $this->oIfx->f('cmone_cod_clas');
                        $mone_sgl_mone = $this->oIfx->f('mone_sgl_mone');
                        $mone_des_mone = $this->oIfx->f('mone_des_mone');
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            //CODIGO DE SUCURSAL
            $codigoSucursal = 0;
            $sqlsuc = "select sucu_alias_sucu from saesucu where sucu_cod_empr= $idempresa and sucu_cod_sucu=$fact_cod_sucu";
            if ($this->oIfx->Query($sqlsuc)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $codigoSucursal = $this->oIfx->f('sucu_alias_sucu');
                }
            }



            ///////////////////////////////////////////////////////
            //DATOS DEL USUARIO

            $sqlus = "select usuario_user from comercial.usuario where usuario_id=$id_usuario";
            if ($this->oIfx->Query($sqlus)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $usuario_user = $this->oIfx->f('usuario_user');
                }
            }


            //GENERACION DE LEYENDA ALEATORIA

            $sqley = "select * from comercial.catalogo_leyendas_factura";
            $array_leyenda = array();
            if ($this->oIfx->Query($sqley)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {

                        $id_leyenda = $this->oIfx->f('id');

                        array_push($array_leyenda, $id_leyenda);
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            $clave_aleatoria = array_rand($array_leyenda, 1);
            if (empty($clave_aleatoria)) {
                $clave_aleatoria = 0;
            }
            $leyenda_factura = '';
            $sqley = "select ley_des_ley from comercial.catalogo_leyendas_factura where id=$clave_aleatoria";
            if ($this->oIfx->Query($sqley)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $leyenda_factura = $this->oIfx->f('ley_des_ley');
                }
            }



            /////////////////////////////////////////////////


            /** CONTROL PARA NC AUTORIZADA */
            if ($fact_aprob_sri == 'N') {


                /**
                 * EXPLODE FACTURA MODIFICA
                 */

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
                    if (strlen($fact_aux_preimp) >= 11 && $fact_cod_ndb > 0) {
                        $exp_fac = explode("-", $fact_aux_preimp);
                        $nse_fac = substr($exp_fac[0], 3, 9);
                        $num_fac = (int) $exp_fac[1];
                        $numDocfectado = "$nse_fac-$num_fac";
                    } else {
                        $numDocfectado = $fact_aux_preimp;
                    }
                }

                ////////////CONSULTAMOS LOS DATOS DE LA FACTURA AUTORIZADA\\\\\\\




                $headers = array(
                    "Content-Type:application/json",
                    "X-CodCliente:$this->empr_cod_api_fac",
                    "X-Authorization:$this->empr_token_api"
                );

                $data_envio = array(

                    "cuf" => $fact_auto_sri
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $this->url_ws_recuperarDatosFacturaCuf);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_envio));
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);



                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta);


                    switch ($http_code) {

                        case 200:

                            $fact_num_fact = $data_json->data->nroFactura;
                            $fact_fec_emis = $data_json->data->fechaEmision;
                            $fact_mon_total = $data_json->data->montoTotal;

                            ///MONTO TOTAL ORIGINAL
                            $monto_total_ori=0;
                            $sql = "select 
                            dncr_cant_dfac as dfac_cant_dfac,
                            dncr_cod_prod as dfac_cod_prod,
                            dncr_det_dncr as dfac_det_dfac,
                            dncr_mont_total as dfac_mont_total,
                            dncr_por_iva as dfac_por_iva,
                            dncr_exc_iva as dfac_exc_iva,
                            dncr_cod_dfac,
                            dncr_cod_unid
                            from saedncr
                            where dncr_cod_ncre = $ncre_cod_ncre;";
    
                                if ($this->oIfx->Query($sql)) {
                                    if ($this->oIfx->NumFilas() > 0) {
                                  
                                        do {

                                            $dncr_cod_dfac = $this->oIfx->f('dncr_cod_dfac');

                                            $sql = "SELECT dfac_cant_dfac, dfac_cod_prod, dfac_det_dfac, dfac_mont_total, dfac_por_iva, 
                                            dfac_cod_lote, dfac_cod_unid,dfac_precio_dfac, dfac_des1_dfac, dfac_des2_dfac, dfac_por_dsg FROM saedfac WHERE dfac_cod_fact = $fact_cod_ndb and dfac_cod_dfact=$dncr_cod_dfac";
                                                        if ($this->oCon->Query($sql)) {
                                                            if ($this->oCon->NumFilas() > 0) {
                                                                    $dfac_cant_dfac_ori = $this->oCon->f('dfac_cant_dfac');
                                                                    $dfac_cod_prod_ori = $this->oCon->f('dfac_cod_prod');
                                                                    $dfac_det_dfac_ori = $this->oCon->f('dfac_det_dfac');
                                                                    $dfac_mont_total_ori = $this->oCon->f('dfac_mont_total');
                                                                    $dfac_por_iva_ori = $this->oCon->f('dfac_por_iva');
                                                                    $dfac_cod_lote_ori = $this->oCon->f('dfac_cod_lote');
                                                                    $dfac_cod_unid_ori = $this->oCon->f('dfac_cod_unid');

                                                                    $dfac_precio_dfac_ori = $this->oCon->f('dfac_precio_dfac');
                                                                    $dfac_des1_dfac_ori = $this->oCon->f('dfac_des1_dfac');
                                                                    $dfac_des2_dfac_ori = $this->oCon->f('dfac_des2_dfac');
                                                                    $dfac_por_dsg_ori = $this->oCon->f('dfac_por_dsg');

                                                                    $descuento_ori = $dfac_des1_dfac_ori + $dfac_des2_dfac_ori + $dfac_por_dsg_ori;

                                                                    if ($descuento_ori > 0){
                                                                        $descuento_ori = ($dfac_precio_dfac_ori * $dfac_cant_dfac_ori) - ($dfac_mont_total_ori);
                                                                        $descuento_ori =round($descuento, 2, PHP_ROUND_HALF_UP);
                                                                    }
                                                                    else{
                                                                        $descuento_ori = '0';
                                                                    }

                                                                    if (round($dfac_por_iva_ori, 2) > 0) {
                                                                        $porcentaje_iva = ($dfac_por_iva_ori / 100) + 1;
                                                                        $valor_iva = ($dfac_mont_total_ori * $porcentaje_iva) - $dfac_mont_total_ori;
                                                                        $dfac_mont_total_ori = $dfac_mont_total_ori + $valor_iva;
                                                                        $dfac_precio_dfac_ori = $dfac_precio_dfac_ori + ($valor_iva/$dfac_cant_dfac_ori);
                                                                    }

                
                                                                    
                
                                                                    /*if ($this->pcon_seg_mone == $fact_cod_mone) {
                                                                        $dfac_mont_total_ori  = $dfac_mont_total_ori / $fact_val_tcam;
                                                                    }*/
                
                                                                   
                                                                    $monto_total_ori+=$dfac_mont_total_ori;
                
                                                                   
                
                                                            }
                                                        }


                                        }while ($this->oIfx->SiguienteRegistro());
                                    }
                                }
                                $this->oIfx->Free();

                           
                                       



                            $data['cabecera'] =  [

                                "nitEmisor" => $this->empr_ruc_empr,

                                "razonSocialEmisor" => $this->empr_nom_empr,

                                "municipio" => $this->prov_des_prov,

                                "telefono" => $this->empr_tel_resp,

                                "codigoSucursal" => $codigoSucursal, //TIPO NUMERICO SE DEBE CONFIGIURAR EN EL CAMPO ALIAS DE LA CONFIGURACION SUCURSAL

                                "direccion" => $this->empr_dir_empr,

                                "codigoPuntoVenta" => "0", //VALIDAR- POR SUCURSAL PUEDE EXISTIR OTRO PUNTO DE VENTA

                                "nombreRazonSocial" => $fact_nom_cliente,

                                "codigoTipoDocumentoIdentidad" => $tipo_docu,

                                "numeroDocumento" => $fact_ruc_clie,

                                "complemento" => "",

                                "codigoCliente" => $fact_cod_clpv,

                                "numeroFactura" => $fact_num_fact,

                                "numeroAutorizacionCuf" => $fact_auto_sri,

                                "fechaEmisionFactura" => $fact_fec_emis,

                                "montoTotalOriginal" => round($monto_total_ori,2,PHP_ROUND_HALF_UP),

                                "montoTotalDevuelto" => round($fact_tot_fact, 2, PHP_ROUND_HALF_UP),

                                "montoDescuentoCreditoDebito" => "0",

                                "montoEfectivoCreditoDebito" => round(($fact_tot_fact*(13/100)), 2, PHP_ROUND_HALF_UP),

                                "codigoExcepcion" => "0",


                                "leyenda" => $leyenda_factura, //COLOCAR DE FORMA ALEATORIA 

                                "usuario" => $usuario_user,

                                "codigoDocumentoSector" => "24", //COMPRA VENTA  - NOTA DE DEBITO O CREDITO 24

                            ];



                            $sql = "select 
                        dncr_cant_dfac as dfac_cant_dfac,
                        dncr_precio_dfac as dfac_precio_dfac,
                        dncr_cod_prod as dfac_cod_prod,
                        dncr_det_dncr as dfac_det_dfac,
                        dncr_mont_total as dfac_mont_total,
                        dncr_por_iva as dfac_por_iva,
                        dncr_des1_dfac as dfac_des1_dfac,
                        dncr_des2_dfac as dfac_des2_dfac,
                        dncr_por_dsg as dfac_por_dsg,
                        dncr_cod_dfac,
                        dncr_cod_unid
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
                                        $dncr_cod_dfac = $this->oIfx->f('dncr_cod_dfac');
                                        $dfac_cod_unid = $this->oIfx->f('dncr_cod_unid');

                                        $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');
                                        $dfac_des1_dfac = $this->oIfx->f('dfac_des1_dfac');
                                        $dfac_des2_dfac = $this->oIfx->f('dfac_des2_dfac');
                                        $dfac_por_dsg = $this->oIfx->f('dfac_por_dsg');
                                        
                                        $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                                        if ($descuento > 0){
                                            $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                                            $descuento =round($descuento, 2, PHP_ROUND_HALF_UP);
                                        }
                                        else{
                                            $descuento = '0';
                                        }

                                        if (round($dfac_por_iva, 2) > 0) {
                                            $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                                            $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;
                                            $dfac_mont_total = $dfac_mont_total + $valor_iva;
                                            $dfac_precio_dfac = $dfac_precio_dfac + ($valor_iva/$dfac_cant_dfac);
                                        }
                                        

                                        /*if ($this->pcon_seg_mone == $fact_cod_mone) {
                                            $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                                        }*/                                       

                                        if (empty($dfac_det_dfac)) {
                                            $dfac_det_dfac = 'Sin detalle';
                                        }

                                        //CONSULTAMOS EL DETALLE DE ITEM DE LA FACTURA ORIGINAL

                                        $sql = "SELECT dfac_cant_dfac, dfac_cod_prod, dfac_det_dfac, dfac_mont_total, dfac_por_iva, 
                                         dfac_cod_lote, dfac_cod_unid, dfac_precio_dfac, dfac_des1_dfac, dfac_des2_dfac, dfac_por_dsg FROM saedfac WHERE dfac_cod_fact = $fact_cod_ndb and dfac_cod_dfact=$dncr_cod_dfac";
                                        
                                        if ($this->oCon->Query($sql)) {
                                            if ($this->oCon->NumFilas() > 0) {
                                                $j = 0;
                                                do {
                                                    $dfac_cant_dfac_ori = $this->oCon->f('dfac_cant_dfac');
                                                    $dfac_cod_prod_ori = $this->oCon->f('dfac_cod_prod');
                                                    $dfac_det_dfac_ori = $this->oCon->f('dfac_det_dfac');
                                                    $dfac_mont_total_ori = $this->oCon->f('dfac_mont_total');
                                                    $dfac_por_iva_ori = $this->oCon->f('dfac_por_iva');
                                                    $dfac_cod_lote_ori = $this->oCon->f('dfac_cod_lote');
                                                    $dfac_cod_unid_ori = $this->oCon->f('dfac_cod_unid');

                                                    $dfac_precio_dfac_ori = $this->oCon->f('dfac_precio_dfac');
                                                    $dfac_des1_dfac_ori = $this->oCon->f('dfac_des1_dfac');
                                                    $dfac_des2_dfac_ori = $this->oCon->f('dfac_des2_dfac');
                                                    $dfac_por_dsg_ori = $this->oCon->f('dfac_por_dsg');
                                                    
                                                    $descuento_ori = $dfac_des1_dfac_ori + $dfac_des2_dfac_ori + $dfac_por_dsg_ori;
            
                                                    if ($descuento_ori > 0){
                                                        $descuento_ori = ($dfac_precio_dfac_ori * $dfac_cant_dfac_ori) - ($dfac_mont_total_ori);
                                                        $descuento_ori =round($descuento_ori, 2, PHP_ROUND_HALF_UP);
                                                    }
                                                    else{
                                                        $descuento_ori = '0';
                                                    }
            
                                                    if (round($dfac_por_iva_ori, 2) > 0) {
                                                        $porcentaje_iva = ($dfac_por_iva_ori / 100) + 1;
                                                        $valor_iva = ($dfac_mont_total_ori * $porcentaje_iva) - $dfac_mont_total_ori;
                                                        $dfac_mont_total_ori = $dfac_mont_total_ori + $valor_iva;
                                                        $dfac_precio_dfac_ori = $dfac_precio_dfac_ori + ($valor_iva/$dfac_cant_dfac_ori);
                                                    }
                                                    

                                                   

                                                    /*if ($this->pcon_seg_mone == $fact_cod_mone) {
                                                        $dfac_mont_total_ori  = $dfac_mont_total_ori / $fact_val_tcam;
                                                    }*/

                                                   


                                                    if (empty($dfac_det_dfac_ori)) {
                                                        $dfac_det_dfac_ori = 'Sin detalle';
                                                    }

                                                    //consulta codigo actividad y codigo de prodcuto registrado en el API

                                                    $sqlproapi = "select lis_cod_act, lis_cod_prod from comercial.catalogo_productos_servicios 
                                        where id =(select COALESCE(prod_cod_api,0) as codigo from saeprod where prod_cod_prod='$dfac_cod_prod_ori' and prod_cod_empr=$idempresa)";
                                                    $codigo_actividad = '';
                                                    $codigo_prod_api = '';
                                                    if ($this->oCon1->Query($sqlproapi)) {
                                                        if ($this->oCon1->NumFilas() > 0) {

                                                            $codigo_actividad = $this->oCon1->f('lis_cod_act');;
                                                            $codigo_prod_api =  $this->oCon1->f('lis_cod_prod');;
                                                        }
                                                    }


                                                    $data['detalle'][$j++] =
                                                        [
                                                            "actividadEconomica" => $codigo_actividad, //VALIDAR

                                                            "codigoProductoSin" => $codigo_prod_api, //CODIGO TOMADO DEL CATALOGO DE PRODCUTOS- SERVICIOS

                                                            "codigoProducto" => $dfac_cod_prod_ori, //CORRESPONDE AL CODIGO DE PRODUCTO DEL SISTEMA

                                                            "descripcion" => $dfac_det_dfac_ori,

                                                            "cantidad" => floatval($dfac_cant_dfac_ori),

                                                            "unidadMedida" => $array_unidad[$dfac_cod_unid_ori],

                                                            "unidadMedidaTexto" => $array_unidad_desc[$dfac_cod_unid_ori],
                                                            //round($dfac_mont_total, 2, PHP_ROUND_HALF_UP);

                                                            "precioUnitario" => round($dfac_precio_dfac_ori, 2, PHP_ROUND_HALF_UP),

                                                            "montoDescuento" => $descuento_ori, //VALIDAR

                                                            "subTotal" => round($dfac_mont_total_ori, 2, PHP_ROUND_HALF_UP),

                                                            "codigoDetalleTransaccion" => "1"
                                                        ];
                                                } while ($this->oCon->SiguienteRegistro());
                                            }
                                        }
                                        $this->oCon->Free();


                                        $data['detalle'][$j++] =
                                            [
                                                "actividadEconomica" => $codigo_actividad, 

                                                "codigoProductoSin" => $codigo_prod_api, //CODIGO TOMADO DEL CATALOGO DE PRODUCTOS- SERVICIOS

                                                "codigoProducto" => $dfac_cod_prod, //CORRESPONDE AL CODIGO DE PRODUCTO DEL SISTEMA

                                                "descripcion" => $dfac_det_dfac,

                                                "cantidad" => floatval($dfac_cant_dfac),

                                                "unidadMedida" => $array_unidad[$dfac_cod_unid],

                                                "unidadMedidaTexto" => $array_unidad_desc[$dfac_cod_unid],

                                                "precioUnitario" => round($dfac_precio_dfac, 2, PHP_ROUND_HALF_UP),

                                                "montoDescuento" => $descuento, 

                                                "subTotal" => round($dfac_mont_total, 2, PHP_ROUND_HALF_UP),

                                                "codigoDetalleTransaccion" => "2"
                                            ];
                                    } while ($this->oIfx->SiguienteRegistro());
                                }
                            }
                            $this->oIfx->Free();


                           //var_dump($data);exit;

                            $headers = array(
                                "Content-Type:application/json",
                                "X-CodCliente:$this->empr_cod_api_fac",
                                "X-Authorization:$this->empr_token_api"
                            );

                            //JSON RECEPCION NOTA DE CREDITO

                            //$fact_email_clpv='andres.garcia@sisconti.com';

                            $data_servicio = array(

                                "codigoSucursal" =>  $codigoSucursal,

                                "codigoPuntoVenta" =>  "0",

                                "codigoDocumentoSector" =>  "24", //

                                "tipoFacturaDocumento" =>  3, // 1 FACTURAS CON CREDITO FISCAL 2 FACTURA SIN CREDITO FISCAL 3  NOTAS DE DEBITO - CREDITO 

                                "datosFactura" =>  $data,

                                "email" =>  $fact_email_clpv, //EL DEL CLIENTE FINAL 

                                "tipoGeneracionNroNota" =>  1 //API DEVUELVE EL NUMERO DE FACTURA - CUIS 

                            );

                            //var_dump(json_encode($data_servicio));exit;

                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_URL, $this->url_ws_recepcionNotaCreditoDebito);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_servicio));
                            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $respuesta = curl_exec($ch);

                            if (!curl_errno($ch)) {
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $data_json = json_decode($respuesta, true);

                                switch ($http_code) {
                                    case 200:
                                        try {


                                            $this->oIfx->QueryT('BEGIN;');

                                            $cuf          = $data_json['data']['cuf'];
                                            $emision      = $data_json['data']['emision'];
                                            $fechaEmision = $data_json['data']['fechaEmision'];


                                            $nombre_documento = $this->empr_ruc_empr . '-' . $fact_nse_fact . '-' . $fact_num_preimp;


                                            $ruta_pdf = 'upload/pdf/cred_' . $nombre_documento . '.pdf';

                                            //FORMATO PERSONALIZADO - BOLIVIA

                                            reporte_notaCredito_personalizado($ncre_cod_ncre, $nombre_documento, $fact_cod_sucu, $ruta_pdf);

                                            //GENERAR ARCHIVO XML
                                            $data_cuf = array(
                                                "cuf" =>  $cuf
                                            );

                                            $this->GenerarXml($headers, $nombre_documento, $data_cuf, 3);


                                            

                                            $div_button = '<div class="btn-group" role="group" aria-label="...">
                                            <a href="upload/xml/cred_' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                                <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                            </a>
                                            <a href="upload/pdf/cred_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                                <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                            </a>
                                        </div>';

                                            $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'S', ncre_auto_sri='$cuf', ncre_user_sri = $id_usuario,
                                            ncre_leye_ncre='$leyenda_factura', ncre_fech_sri='$fechaEmision', ncre_tip_emis='$emision'
                                             WHERE ncre_cod_ncre = $ncre_cod_ncre;";

                                            $this->oIfx->QueryT($sql_update);
                                            $this->oIfx->QueryT('COMMIT;');

                                            $result_txt = $data_json['data']['estado'];

                                            $result = array(
                                                'div_button' => $div_button,
                                                'result_ws' => $result_txt,
                                                'result_email' => 'Autorizado (El correo fue enviado al cliente)',
                                            );
                                        } catch (Exception $e) {
                                            $this->oIfx->QueryT('ROLLBACK;');
                                            throw new Exception($e->getMessage());
                                        }
                                        break;
                                    case 422:
                                        $error_api = $data_json['error'];
                                        $detalle_error = '';
                                        foreach ($error_api as $info) {
                                            $detalle_error .= $info . ' ';
                                        }
                                        $error_sri = substr($detalle_error, 0, 255);
                                        $this->oIfx->QueryT('BEGIN;');
                                        $sql_update = "UPDATE saencre SET ncre_erro_sri = '$error_sri' WHERE ncre_cod_ncre = $ncre_cod_ncre";
                                        $this->oIfx->QueryT($sql_update);
                                        $this->oIfx->QueryT('COMMIT;');

                                        throw new Exception($detalle_error);
                                        break;
                                }
                            } else {
                                $errorMessage = curl_error($ch);
                                throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
                            }

                            


                            break;

                        case 404:

                            $result_txt = 'Factura no Autorizada';
                            $result = array(
                                'div_button' => $div_button,
                                'result_ws' => $result_txt,
                                'result_email' => '',
                            );

                            break;
                    }
                } else {
                    $errorMessage = curl_error($ch);
                    throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
                }


            } else {
                throw new Exception("El Documento ya fue  Autorizado");
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }

   


  
}
