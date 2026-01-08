<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class FacturacionElectronicaVenezuela2024
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
    public $empr_ws_sri_url = '';
    



    ///RUTA API PRUEBAS
    //https://testapi.serdimpre.com:7245/API';
  
    function __construct($oIfx, $oCon, $oCon1, $idempresa)
    {
        $this->oIfx = $oIfx;
        $this->oCon = $oCon;
        $this->oCon1 = $oCon1;

        $sql = "SELECT  empr_ruc_empr, empr_nom_empr, empr_dir_empr, empr_mai_empr, 
                    empr_tel_resp, empr_token_api_fac, empr_cod_empr, empr_cod_prov,
                    empr_cod_ciud, empr_cod_parr, empr_cpo_empr, empr_cod_api_fac, empr_ws_sri_url
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
            $sql = "SELECT prov_des_prov from saeprov where prov_cod_prov = '$empr_cod_prov'";
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
            $sql = "SELECT parr_des_parr from saeparr where parr_cod_parr = '$empr_cod_parr' ";
            $this->parr_des_parr = consulta_string($sql, 'parr_des_parr', $oCon, 0);
        } else {
            $this->parr_des_parr = "";
        }
    }


/**
* ENVIO DOCUMENTOS API FACTURACION VENEZUELA SEDIMPRE
*/
function EnviarFactura($fact_cod_fact)
{


        try {
            session_start();
            $id_usuario = $_SESSION['U_ID'];
            $idempresa = $_SESSION['U_EMPRESA'];
            $empr_cod_pais = $_SESSION['U_PAIS_COD'];

            // IMPUESTOS POR PAIS
        $sql = "select p.impuesto, p.etiqueta, p.porcentaje from comercial.pais_etiq_imp p where
        p.pais_cod_pais = $empr_cod_pais ";
        unset($array_imp);
        unset($array_porc);
        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {
                do {
                        $impuesto      = $this->oIfx->f('impuesto');
                        $etiqueta     = $this->oIfx->f('etiqueta');
                        $porcentaje_impuesto = $this->oIfx->f('porcentaje');
                        $array_imp[$impuesto] = $etiqueta;
                        $array_porc[$impuesto] = $porcentaje;

                    }while ($this->oIfx->SiguienteRegistro());
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
                        //FORMATO ESTANDAR VENEZUELA
                        include_once('../../Include/Formatos/comercial/factura_venezuela.php');
                    }
                } else {
                    //FORMATO ESTANDAR VENEZUELA
                    include_once('../../Include/Formatos/comercial/factura_venezuela.php');
                }
            }
            $this->oIfx->Free();


            //ARRAY UNIDADES - 
            $sql = "select unid_cod_unid, unid_sigl_unid from saeunid where unid_cod_empr=$idempresa";
            unset($array_unidad);
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                            $array_unidad[$this->oIfx->f('unid_cod_unid')] = $this->oIfx->f('unid_sigl_unid');
                        
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();


            $sql = "SELECT
	        fac.fact_nse_fact,
	        fac.fact_num_preimp,
	        fac.fact_fech_fact,
            right(fac.fact_hor_ini,8) as fact_hor_ini,
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
            fac.fact_otr_fact,
	        cli.clv_con_clpv,
            cli.clpv_cod_ctrb,
            cli.clpv_ctrb_sn,
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
                        $fact_hor_ini = $this->oIfx->f('fact_hor_ini');
                        $fact_fech_venc = $this->oIfx->f('fact_fech_venc');
                        

                        $fechaEmision = $fact_fech_fact.'T'.$fact_hor_ini;
                        $fechaVencimiento = $fact_fech_venc.'T'.$fact_hor_ini;

                       

                        $fact_hor_fin = $this->oIfx->f('fact_hor_fin');
                        $fact_cod_clpv = $this->oIfx->f('fact_cod_clpv');
                        $fact_nom_cliente = $this->oIfx->f('fact_nom_cliente');
                        $fact_ruc_clie = $this->oIfx->f('fact_ruc_clie');
                        $fact_dir_clie = $this->oIfx->f('fact_dir_clie');
                        $fact_email_clpv = $this->oIfx->f('fact_email_clpv');
                        $fact_tlf_cliente = $this->oIfx->f('fact_tlf_cliente');
                        $fact_iva = $this->oIfx->f('fact_iva');
                        $fact_iva = floatval(number_format($fact_iva, 2, '.', ''));
                        $fact_con_miva = $this->oIfx->f('fact_con_miva');
                        $fact_con_miva = floatval(number_format($fact_con_miva, 2, '.', ''));
                        $fact_sin_miva = $this->oIfx->f('fact_sin_miva');
                        $fact_sin_miva = floatval(number_format($fact_sin_miva, 2, '.', ''));
                        $fact_sub_fac=  $this->oIfx->f('fact_tot_fact');
                        $fact_sub_fac = floatval(number_format($fact_sub_fac, 2, '.', ''));

                        $fact_otr_fact=  $this->oIfx->f('fact_otr_fact');
                        $fact_otr_fact = floatval(number_format($fact_otr_fact, 2, '.', ''));

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

                        //VALIDAICON DIRECCION
                        if(empty($fact_dir_clie)){
                            throw new Exception("Direccion no ingresada");
                        }

                        //DATOS COMPRADOR
                        $clpv_cod_ctrb = $this->oIfx->f('clpv_cod_ctrb');
                        if(empty($clpv_cod_ctrb)){
                            throw new Exception("Tipo de Comprador no ingresado configure en la Ficha del cliente");
                        }
                        $clpv_ctrb_sn = $this->oIfx->f('clpv_ctrb_sn');
                        if($clpv_ctrb_sn=='S'){
                            $clpv_ctrb_sn=true;
                        }
                        else{
                            $clpv_ctrb_sn=false;
                        }


                        if ($fact_cm4_fact == null) {
                            $fact_cm4_fact = 0;
                        }

                        $fact_tot_fact = $fact_con_miva + $fact_iva + $fact_sin_miva + $fact_val_irbp - $fact_dsg_valo ;

                        $fact_tot_fact = floatval(number_format($fact_tot_fact, 2, '.', ''));




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

            //if ($this->pcon_seg_mone == $fact_cod_mone) {
              //  $fact_tot_fact = $fact_tot_fact / $fact_val_tcam;
            //}

            $valor_mon_ext = $fact_tot_fact;

            //MONEDA

            $sql="select mone_des_mone,mone_smb_mene from saemone where mone_cod_mone=$fact_cod_mone";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $moneda = $this->oIfx->f('mone_des_mone');
                    $simbolo_moneda = $this->oIfx->f('mone_smb_mene');
                }
            }


            //CODIGO DE SUCURSAL
            $codigoSucursal = 0;
            $sqlsuc = "select sucu_alias_sucu, sucu_nom_sucu, sucu_cod_site, sucu_val_site from saesucu where sucu_cod_empr= $idempresa and sucu_cod_sucu=$fact_cod_sucu";
            if ($this->oIfx->Query($sqlsuc)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $sucu_nom_sucu = $this->oIfx->f('sucu_nom_sucu');
                    $sucu_cod_site = $this->oIfx->f('sucu_cod_site');
                    $sucu_val_site = $this->oIfx->f('sucu_val_site');
                }
            }


            //CODIGO DEL PAIS DEL CLIENTE

            $sqlp="select clpv_cod_pais from saeclpv where clpv_cod_clpv=$fact_cod_clpv";
            if ($this->oIfx->Query($sqlp)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $codigoPaiscliente = $this->oIfx->f('clpv_cod_pais');
                }
            }

        

            //PAIS CLIENTE

            if(empty($codigoPaiscliente)) $codigoPaiscliente='NULL';

            $sqlp="select pais_cod_inte from saepais where pais_cod_pais=$codigoPaiscliente";
            if ($this->oIfx->Query($sqlp)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $Paiscliente = $this->oIfx->f('pais_cod_inte');
                }
            }

            if(empty($Paiscliente)){
                $Paiscliente ='VEF';
            }


            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetrasMone($fact_tot_fact, $moneda));

                //DATOS DE LA FORMA DE PAGO - SE PUEDE TOMAR DEL ALIAS

                $sql = "SELECT
                fx.fxfp_cod_fpag,
                fx.fxfp_val_fxfp,
                fx.fxfp_fec_fin,
                fx.fxfp_cot_fpag,
                fx.fxfp_cod_mone,
                fp.fpag_cod_alias
                FROM
                saefxfp fx,saefpag fp
                WHERE 
                fp.fpag_cod_fpag = fx.fxfp_cod_fpag and
                fp.fpag_cod_empr=$idempresa and
                fx.fxfp_cod_fact = $fact_cod_fact ;";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        $array_fp=array();

                        $base_imponible_igtf=0;
                        do {
                

                            $fxfp_val_fxfp  = $this->oIfx->f('fxfp_val_fxfp');
                            $fpag_cod_alias   = $this->oIfx->f('fpag_cod_alias');
                            $tipo           = $this->oIfx->f('fxfp_cot_fpag');
                            $fxfp_cod_mone  = $this->oIfx->f('fxfp_cod_mone');

                            if ($this->pcon_mon_base != $fxfp_cod_mone) {
                                $base_imponible_igtf+= $fxfp_val_fxfp;
                            }

                            

                            $sql = "SELECT mone_sgl_mone, mone_des_mone from saemone where mone_cod_mone = $fxfp_cod_mone ";
                            if ($this->oCon->Query($sql)) {
                                if ($this->oCon->NumFilas() > 0) {
                                    do {
                                        $mone_sgl_mone = $this->oCon->f('mone_sgl_mone');
                                        $mone_des_mone = $this->oCon->f('mone_des_mone');
                                    } while ($this->oCon->SiguienteRegistro());
                                }
                            }
                            $this->oCon->Free();

                          

                            if ($tipo == 'EFE') {
                                $tipoPago = 1;
                            } else if ($tipo == 'CRE') {
                                $tipoPago = 0;
                            } else {
                                $tipoPago = 1;
                            }


                            $data_fp=array(
                                "forma" => $j,
                                "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                "moneda" => $mone_sgl_mone,
                                "tipoCambio" => 0
                            );
                            array_push($array_fp, $data_fp);


                            $j++;

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

                //ARRAY TELEFONOS


                $sql="SELECT tlcp_tlf_tlcp

                from saetlcp
            
                where tlcp_cod_empr = $idempresa
            
                and tlcp_cod_clpv=$fact_cod_clpv";

                $array_telf=array();

                 if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {

                            $tlcp_tlf_tlcp  = $this->oIfx->f('tlcp_tlf_tlcp');


                            array_push($array_telf, $tlcp_tlf_tlcp);

                            $j++;
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

            //ARRAY CORREOS

                $sql="SELECT DISTINCT(emai_ema_emai) as correo

                from saeemai
            
                where emai_cod_empr = $idempresa
            
                and emai_cod_clpv=$fact_cod_clpv";
                $array_correo=array();

                 if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {

                            $email_clpv  = $this->oIfx->f('correo');

                            array_push($array_correo, $email_clpv);
                            $j++;
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                //JSON CLIENTE

                $data_cli= array(
                    "comprador_Ctrb" =>  $clpv_ctrb_sn,//VALIDAR
                    "doc_NoDomiciliado" =>  false,//VALIDAR
                    "cod_Comprador" =>  $fact_cod_clpv, //validar - Codigo interno del cliente
                    "comprador_Tipo_ID" =>  $clpv_cod_ctrb, //validar - Tipo de persona del cliente posibles valores (J,G,V,E) Juridica, Gubernamental, Venezolana o Extrajera respectivamente
                    "comprador_Num_ID" =>  $fact_ruc_clie,  
                    "razon_Social" =>  $fact_nom_cliente,
                    "direccion" =>  $fact_dir_clie,
                    "pais" => $Paiscliente , 
                    "telefonos" =>$array_telf,
                    "emails" => $array_correo
                );

                

                //ITEMS FACTURA
                $sqli="select count(*) items from saedfac where dfac_cod_fact=$fact_cod_fact";
                if ($this->oIfx->Query($sqli)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $items = $this->oIfx->f('items');
                    }
                }

                
                //////////////////////DETALLES SERVICIOS/PRODUCTOS//////////////////////

                $sql = "SELECT dfac_cant_dfac, dfac_cod_prod, dfac_det_dfac, dfac_mont_total, dfac_por_iva, dfac_exc_iva,dfac_cod_lote, dfac_cod_unid, 
                dfac_precio_dfac, dfac_des1_dfac, dfac_des2_dfac, dfac_por_dsg  FROM saedfac WHERE dfac_cod_fact = $fact_cod_fact";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        $cod_imp=4;
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

                            $prec_Item = $dfac_mont_total;
                            $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                            $porcentaje_descuento=$dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                            if ($descuento > 0){
                                $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                                $descuento =round($descuento, 2, PHP_ROUND_HALF_UP);
                            }
                            else{
                                $descuento = '0';
                            }
                           
                            $valor_iva=0;
                            if (round($dfac_por_iva, 2) > 0) {
                                $cod_imp=1;
                                $porcentaje_impuesto=$dfac_por_iva;

                                $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                                $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;
                                $dfac_mont_total = $dfac_mont_total + $valor_iva;
                            }
                            
                            /*if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                            }*/

                            if (empty($dfac_det_dfac)) {
                                $dfac_det_dfac = 'Sin detalle';
                            }

                            //CONSULTA TIPO DE PRODUCTO

                            $sqlpro="select prod_cod_tpro from saeprod where prod_cod_prod='$dfac_cod_prod' and prod_cod_empr=$idempresa";
                            if ($this->oCon->Query($sqlpro)) {
                                if ($this->oCon->NumFilas() > 0) {

                                    $tipo_item = $this->oCon->f('prod_cod_tpro');
                                  
                                }
                            }

                                    if(round($descuento)==0){
                                        $descuento=null;
                                    }

                                $data_detalle[$j++] =
                                [
                                    "num_Linea" => $j, 

                                    "item_Cod" => $dfac_cod_prod, //CORRESPONDE AL CODIGO DE PRODUCTO DEL SISTEMA

                                    "tipo_Item" => $tipo_item,

                                    "desc_Item" => $dfac_det_dfac,

                                    "cantidad" => intval($dfac_cant_dfac),

                                    "unidad_Medida" => $array_unidad[$dfac_cod_unid],

                                    "prec_Unitario" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),

                                    "prec_Unitario_Desc" => 0,

                                    "bonif_Item" => null,

                                    "descBonif_Item" => null,

                                    "desc_Monto" => $descuento,

                                    "prec_Item" => floatval(number_format($prec_Item, 2, '.', '')),

                                    "cod_Imp" => $cod_imp, //VALIDAR

                                    "tasa_IVA" => floatval(number_format($dfac_por_iva, 2, '.', '')),

                                    "valor_IVA" => floatval(number_format($valor_iva, 2, '.', '')),

                                    "total_Item" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                ];
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                //JSON TOTALES
                $array_imp=array();

                if (round($fact_con_miva,2)>0){
                    $data_imp=array(
                        "cod_Impuesto" =>  1,//VALIDAR                    
                        "alicuotaImp" =>  floatval(number_format($porcentaje_impuesto, 2, '.', '')),
                        "base_Imponible" =>  floatval(number_format($fact_con_miva, 2, '.', '')),
                        "total_Impuesto" =>  floatval(number_format($fact_iva, 2, '.', '')),
    
                    );
                    array_push($array_imp, $data_imp);
                }
                //VALIDACION EXCENTO
                if(round($fact_sin_miva,2)>0){
                    $data_imp=array(
                        "cod_Impuesto" =>  4,//VALIDAR                    
                        "alicuotaImp" =>  floatval(number_format(0, 2, '.', '')),
                        "base_Imponible" =>  floatval(number_format($fact_sin_miva, 2, '.', '')),
                        "total_Impuesto" =>  floatval(number_format(0, 2, '.', '')),
                    );
                    array_push($array_imp, $data_imp);
                }

                 ///VALIDACION PAGO CON DIVISAS(MONEDA EXTRANJERA)
                 if(round($fact_otr_fact,2)>0){
                    $data_imp=array(
                        "cod_Impuesto" =>  3,
                        "Tasa" => $fact_val_tcam,                    
                        "alicuotaImp" =>  floatval(number_format(3, 2, '.', '')),
                        "base_Imponible" =>  floatval(number_format($base_imponible_igtf, 2, '.', '')),
                        "total_Impuesto" =>  floatval(number_format($fact_otr_fact, 2, '.', '')),
                    );
                    array_push($array_imp, $data_imp);
                }
                

                //TOTALES
                $data_tot= array(
                    "items_Doc" =>  $items,
                    "monto_Gravado" =>  floatval(number_format($fact_con_miva, 2, '.', '')),
                    "monto_Exento" =>  floatval(number_format($fact_sin_miva, 2, '.', '')), 
                    "total_IVA" =>  floatval(number_format($fact_iva, 2, '.', '')), 
                    "total_Doc" =>  floatval(number_format($fact_tot_fact, 2, '.', '')), 
                    "total_Letras" =>  $con_letra,
                    "total_Desc_Letras" => null,//EN CASO DE DESCUENTO VALIDAR
                    "impuestos" =>  $array_imp,
                    "pagos" => $array_fp , 
                );


                //INFORMACION ADICIONAL
                $data_info=array();

                if(!empty($sucu_val_site)){
                    $data_info=array(
                        "site" => $sucu_cod_site,
                        "campo" => "",
                        "valor" => $sucu_val_site
                    );
                }
                

                /**
                 * EMPIEZA ENVIO
                 */

                $headers = array(
                    "Content-Type:application/json",
                    "Authorization:Bearer $this->empr_token_api"
                );

                              //JSON EMISION DOCUMENTO

                  $data_doc = array(
                    "tipo_Doc" =>  1,//1-FACTURA, 2- NOTA DE CREDITO, 3- NOTA DE DEBITO
                    "num_Doc" =>  $fact_nse_fact.$fact_num_preimp,
                    "num_Doc_Afec" =>  null, //
                    "fechaEmision" =>  $fechaEmision, //VALIDAR FORMATO FECHA 
                    "fechaVencimiento" =>  $fechaVencimiento, //VALIDAR FORMATO FECHA 
                    "tipoPago" =>  $tipoPago,
                    "serie" =>  $fact_nse_fact,
                    "sucursal" =>$sucu_nom_sucu ,
                    "moneda" => $simbolo_moneda , 
                    "vendedor" =>  $usuario_user,
                    "comprador" =>   $data_cli,
                    "totales" =>   $data_tot,
                    "conceptos" =>   $data_detalle,
                    "addInfo" =>   $data_info,
                );

                //var_dump(json_encode($data_doc));exit;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url.'/EmitirDocumento');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_doc));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $respuesta = curl_exec($ch);



                            if (!curl_errno($ch)) {
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $data_json = json_decode($respuesta, true);
                                switch ($http_code) {
                                    case 200:
                                        try {


                                            $this->oIfx->QueryT('BEGIN;');


                                            $result_txt = $data_json['mensaje'];

                                            $ctrl_num = $data_json['resultado']['nroControl'];

                                            $fechaAsignacion = $data_json['resultado']['fechaAsignacion'];




                                          
                                            //FORMATO PERSONALIZADO - VENEZUELA
                                            //reporte_factura_personalizado($fact_cod_fact, $nombre_documento, $fact_cod_sucu, $ruta_pdf);

                                            //GENERAR ARCHIVO XML

                                           $nombre_documento='fac_'.$fact_cod_fact;
                                           $numDoc=$fact_nse_fact.$fact_num_preimp;

                                           //PDF
                                           $this->GenerarXmlPdf($fact_cod_fact, 1, 1, $ctrl_num, $numDoc);
                                           
                                           //XML
                                           $this->GenerarXmlPdf($fact_cod_fact, 1, 2, $ctrl_num, $numDoc);
                                            

                                            $div_button = '<div class="btn-group" role="group" aria-label="...">
                                            <a href="upload/xml/fac_' . $fact_cod_fact . '.xml" download="' . $nombre_documento . '.xml">
                                                <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                            </a>
                                            <a href="upload/pdf/fac_' . $fact_cod_fact . '.pdf" download="' . $nombre_documento . '.pdf">
                                                <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                            </a>
                                        </div>';

                                           

                                            $sql_update = "UPDATE saefact SET fact_aprob_sri = 'S', fact_fech_sri='$fechaAsignacion',  
                                            fact_user_sri=$id_usuario, fact_cod_hash='$ctrl_num'
                                             WHERE fact_cod_fact = $fact_cod_fact ;";

                                            $this->oIfx->QueryT($sql_update);
                                            $this->oIfx->QueryT('COMMIT;');

                                            

                                            $result = array(
                                                'div_button' => $div_button,
                                                'result_ws' => $result_txt,
                                                'result_email' => 'Autorizado',
                                            );
                                        } catch (Exception $e) {
                                            $this->oIfx->QueryT('ROLLBACK;');
                                            throw new Exception($e->getMessage());
                                        }
                                        break;
                                    case 400:

                                        ///ERRORES
                                        $mensaje = $data_json['mensaje'];
                                        $error_api = $mensaje.' '.$data_json['validaciones'][0]['error'].' '.$data_json['validaciones'][0]['mensaje'];
                                        $error_sri = substr($error_api, 0, 255);

                                        if(empty($error_sri)){
                                            $error_sri = substr($data_json['errors'], 0, 255);
                                            $error_api =$data_json['errors'];
                                        }

                                        $this->oIfx->QueryT('BEGIN;');

                                        $sql_update = "UPDATE saefact SET fact_erro_sri = '$error_sri' WHERE fact_cod_fact = $fact_cod_fact";
                                        $this->oIfx->QueryT($sql_update);
                                        
                                        $this->oIfx->QueryT('COMMIT;');

                                        throw new Exception($error_api);
                                        break;
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

function EnviarNotaDebito($fact_cod_fact)
{


        try {
            session_start();
            $id_usuario = $_SESSION['U_ID'];
            $idempresa = $_SESSION['U_EMPRESA'];
            $empr_cod_pais = $_SESSION['U_PAIS_COD'];

            // IMPUESTOS POR PAIS
        $sql = "select p.impuesto, p.etiqueta, p.porcentaje from comercial.pais_etiq_imp p where
        p.pais_cod_pais = $empr_cod_pais ";
        unset($array_imp);
        unset($array_porc);
        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {
                do {
                        $impuesto      = $this->oIfx->f('impuesto');
                        $etiqueta     = $this->oIfx->f('etiqueta');
                        $porcentaje_impuesto = $this->oIfx->f('porcentaje');
                        $array_imp[$impuesto] = $etiqueta;
                        $array_porc[$impuesto] = $porcentaje;

                    }while ($this->oIfx->SiguienteRegistro());
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
                        //FORMATO ESTANDAR VENEZUELA
                        include_once('../../Include/Formatos/comercial/factura_venezuela.php');
                    }
                } else {
                    //FORMATO ESTANDAR VENEZUELA
                    include_once('../../Include/Formatos/comercial/factura_venezuela.php');
                }
            }
            $this->oIfx->Free();


            //ARRAY UNIDADES - 
            $sql = "select unid_cod_unid, unid_sigl_unid from saeunid where unid_cod_empr=$idempresa";
            unset($array_unidad);
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                            $array_unidad[$this->oIfx->f('unid_cod_unid')] = $this->oIfx->f('unid_sigl_unid');
                        
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();


            $sql = "SELECT
	        fac.fact_nse_fact,
	        fac.fact_num_preimp,
	        fac.fact_fech_fact,
            right(fac.fact_hor_ini,8) as fact_hor_ini,
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
            fac.fact_cod_ndb,
	        cli.clv_con_clpv,
            cli.clpv_cod_ctrb,
            cli.clpv_ctrb_sn,
	        fac.fact_aprob_sri,
            fac.fact_cod_mone,
            fac.fact_val_tcam,
            fac.fact_cod_detra,
            fac.fact_cm2_fact,
            fac.fact_dsg_valo,
            fac.fact_aux_preimp
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
                        $fact_hor_ini = $this->oIfx->f('fact_hor_ini');
                        $fact_fech_venc = $this->oIfx->f('fact_fech_venc');

                        $fechaEmision = $fact_fech_fact.'T'.$fact_hor_ini;
                        $fechaVencimiento = $fact_fech_venc.'T'.$fact_hor_ini;

                       

                        $fact_hor_fin = $this->oIfx->f('fact_hor_fin');
                        $fact_cod_clpv = $this->oIfx->f('fact_cod_clpv');
                        $fact_nom_cliente = $this->oIfx->f('fact_nom_cliente');
                        $fact_ruc_clie = $this->oIfx->f('fact_ruc_clie');
                        $fact_dir_clie = $this->oIfx->f('fact_dir_clie');
                        $fact_email_clpv = $this->oIfx->f('fact_email_clpv');
                        $fact_tlf_cliente = $this->oIfx->f('fact_tlf_cliente');
                        $fact_iva = $this->oIfx->f('fact_iva');
                        $fact_iva = floatval(number_format($fact_iva, 2, '.', ''));
                        $fact_con_miva = $this->oIfx->f('fact_con_miva');
                        $fact_con_miva = floatval(number_format($fact_con_miva, 2, '.', ''));
                        $fact_sin_miva = $this->oIfx->f('fact_sin_miva');
                        $fact_sin_miva = floatval(number_format($fact_sin_miva, 2, '.', ''));
                        $fact_sub_fac=  $this->oIfx->f('fact_tot_fact');
                        $fact_sub_fac = floatval(number_format($fact_sub_fac, 2, '.', ''));

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
                        $fact_aux_preimp = $this->oIfx->f('fact_aux_preimp');
                        $fact_cod_ndb = $this->oIfx->f('fact_cod_ndb');

                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);

                        //DATOS COMPRADOR
                        $clpv_cod_ctrb = $this->oIfx->f('clpv_cod_ctrb');
                        if(empty($clpv_cod_ctrb)){
                            throw new Exception("Tipo de Comprador no ingresado configure en la Ficha del cliente");
                        }
                        $clpv_ctrb_sn = $this->oIfx->f('clpv_ctrb_sn');

                        if($clpv_ctrb_sn=='S'){
                            $clpv_ctrb_sn=true;
                        }
                        else{
                            $clpv_ctrb_sn=false;
                        }


                        if ($fact_cm4_fact == null) {
                            $fact_cm4_fact = 0;
                        }

                        $fact_tot_fact = $fact_con_miva + $fact_iva + $fact_sin_miva + $fact_val_irbp - $fact_dsg_valo ;

                        $fact_tot_fact = floatval(number_format($fact_tot_fact, 2, '.', ''));




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

            $exp_fac = explode("-", $fact_aux_preimp);
            $nse_fac = substr($exp_fac[0], 3, 9);
            $num_fac = $exp_fac[1];
            $numDocfectado = $nse_fac.$num_fac;



            if ($this->pcon_seg_mone == $fact_cod_mone) {
                $fact_tot_fact = $fact_tot_fact / $fact_val_tcam;
            }

            $valor_mon_ext = $fact_tot_fact;

            //MONEDA

            $sql="select mone_des_mone,mone_smb_mene from saemone where mone_cod_mone=$fact_cod_mone";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $moneda = $this->oIfx->f('mone_des_mone');
                    $simbolo_moneda = $this->oIfx->f('mone_smb_mene');
                }
            }


            //CODIGO DE SUCURSAL
            $codigoSucursal = 0;
            $sqlsuc = "select sucu_alias_sucu, sucu_nom_sucu, sucu_cod_site, sucu_val_site from saesucu where sucu_cod_empr= $idempresa and sucu_cod_sucu=$fact_cod_sucu";
            if ($this->oIfx->Query($sqlsuc)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $sucu_nom_sucu = $this->oIfx->f('sucu_nom_sucu');
                    $sucu_cod_site = $this->oIfx->f('sucu_cod_site');
                    $sucu_val_site = $this->oIfx->f('sucu_val_site');
                }
            }


            //CODIGO DEL PAIS DEL CLIENTE

            $sqlp="select clpv_cod_pais from saeclpv where clpv_cod_clpv=$fact_cod_clpv";
            if ($this->oIfx->Query($sqlp)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $codigoPaiscliente = $this->oIfx->f('clpv_cod_pais');
                }
            }

        

            //PAIS CLIENTE

            if(empty($codigoPaiscliente)) $codigoPaiscliente='NULL';

            $sqlp="select pais_cod_inte from saepais where pais_cod_pais=$codigoPaiscliente";
            if ($this->oIfx->Query($sqlp)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $Paiscliente = $this->oIfx->f('pais_cod_inte');
                }
            }

            if(empty($Paiscliente)){
                $Paiscliente ='VEF';
            }


            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetrasMone($fact_tot_fact, $moneda));

                //DATOS DE LA FORMA DE PAGO - SE PUEDE TOMAR DEL ALIAS

                $sql = "SELECT
                fx.fxfp_cod_fpag,
                fx.fxfp_val_fxfp,
                fx.fxfp_fec_fin,
                fx.fxfp_cot_fpag,
                fp.fpag_cod_alias
                FROM
                saefxfp fx,saefpag fp
                WHERE 
                fp.fpag_cod_fpag = fx.fxfp_cod_fpag and
                fp.fpag_cod_empr=$idempresa and
                fx.fxfp_cod_fact = $fact_cod_ndb ;";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {
                

                            $fxfp_val_fxfp  = $this->oIfx->f('fxfp_val_fxfp');
                            $fpag_cod_alias   = $this->oIfx->f('fpag_cod_alias');
                            $tipo           = $this->oIfx->f('fxfp_cot_fpag');

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                            }

                          

                            if ($tipo == 'EFE') {
                                $tipoPago = 1;
                            } else if ($tipo == 'CRE') {
                                $tipoPago = 0;
                            } else {
                                $tipoPago = 1;
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

                //ARRAY TELEFONOS


                $sql="SELECT tlcp_tlf_tlcp

                from saetlcp
            
                where tlcp_cod_empr = $idempresa
            
                and tlcp_cod_clpv=$fact_cod_clpv";

                $array_telf=array();

                 if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {

                            $tlcp_tlf_tlcp  = $this->oIfx->f('tlcp_tlf_tlcp');


                            array_push($array_telf, $tlcp_tlf_tlcp);

                            $j++;
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

            //ARRAY CORREOS

                $sql="SELECT DISTINCT(emai_ema_emai) as correo

                from saeemai
            
                where emai_cod_empr = $idempresa
            
                and emai_cod_clpv=$fact_cod_clpv";
                $array_correo=array();

                 if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {

                            $email_clpv  = $this->oIfx->f('correo');

                            array_push($array_correo, $email_clpv);
                            $j++;
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                //JSON CLIENTE

                $data_cli= array(
                    "comprador_Ctrb" =>  $clpv_ctrb_sn,//VALIDAR
                    "doc_NoDomiciliado" =>  false,
                    "cod_Comprador" =>  $fact_cod_clpv, //validar - Codigo interno del cliente
                    "comprador_Tipo_ID" =>  $clpv_cod_ctrb, 
                    "comprador_Num_ID" =>  $fact_ruc_clie,  
                    "razon_Social" =>  $fact_nom_cliente,
                    "direccion" =>  $fact_dir_clie,
                    "pais" => $Paiscliente , 
                    "telefonos" =>$array_telf,
                    "emails" => $array_correo
                );

                

                //ITEMS FACTURA
                $sqli="select count(*) items from saedfac where dfac_cod_fact=$fact_cod_fact";
                if ($this->oIfx->Query($sqli)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $items = $this->oIfx->f('items');
                    }
                }

                
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

                            $prec_Item = $dfac_mont_total;


                            $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                            $porcentaje_descuento=$dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                            if ($descuento > 0){
                                $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                                $descuento =round($descuento, 2, PHP_ROUND_HALF_UP);
                            }
                            else{
                                $descuento = '0';
                            }
                           
                            $valor_iva=0;
                            if (round($dfac_por_iva, 2) > 0) {
                                $porcentaje_impuesto=$dfac_por_iva;

                                $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                                $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;
                                $dfac_mont_total = $dfac_mont_total + $valor_iva;
                            }

                            /*if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                            }*/

                            if (empty($dfac_det_dfac)) {
                                $dfac_det_dfac = 'Sin detalle';
                            }

                            //CONSULTA TIPO DE PRODUCTO

                            $sqlpro="select prod_cod_tpro from saeprod where prod_cod_prod='$dfac_cod_prod' and prod_cod_empr=$idempresa";
                            if ($this->oCon->Query($sqlpro)) {
                                if ($this->oCon->NumFilas() > 0) {

                                    $tipo_item = $this->oCon->f('prod_cod_tpro');
                                  
                                }
                            }

                                    if(round($descuento)==0){
                                        $descuento=null;
                                    }

                                $data_detalle[$j++] =
                                [
                                    "num_Linea" => $j, 

                                    "item_Cod" => $dfac_cod_prod, //CORRESPONDE AL CODIGO DE PRODUCTO DEL SISTEMA

                                    "tipo_Item" => $tipo_item,

                                    "desc_Item" => $dfac_det_dfac,

                                    "cantidad" => intval($dfac_cant_dfac),

                                    "unidad_Medida" => $array_unidad[$dfac_cod_unid],

                                    "prec_Unitario" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),

                                    "prec_Unitario_Desc" => 0,

                                    "bonif_Item" => null,

                                    "descBonif_Item" => null,

                                    "desc_Monto" => $descuento,

                                    "prec_Item" => floatval(number_format($prec_Item, 2, '.', '')),

                                    "cod_Imp" => 1, //VALIDAR

                                    "tasa_IVA" => floatval(number_format($dfac_por_iva, 2, '.', '')),

                                    "valor_IVA" => floatval(number_format($valor_iva, 2, '.', '')),

                                    "total_Item" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                ];
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                //JSON TOTALES
                //JSON TOTALES
                $array_imp=array();

                if (round($fact_con_miva,2)>0){
                    $data_imp=array(
                        "cod_Impuesto" =>  1,//VALIDAR                    
                        "alicuotaImp" =>  floatval(number_format($porcentaje_impuesto, 2, '.', '')),
                        "base_Imponible" =>  floatval(number_format($fact_con_miva, 2, '.', '')),
                        "total_Impuesto" =>  floatval(number_format($fact_iva, 2, '.', '')),
    
                    );
                    array_push($array_imp, $data_imp);
                }
                //VALIDACION EXCENTO
                if(round($fact_sin_miva,2)>0){
                    $data_imp=array(
                        "cod_Impuesto" =>  4,//VALIDAR                    
                        "alicuotaImp" =>  floatval(number_format(0, 2, '.', '')),
                        "base_Imponible" =>  floatval(number_format($fact_sin_miva, 2, '.', '')),
                        "total_Impuesto" =>  floatval(number_format(0, 2, '.', '')),
                    );
                    array_push($array_imp, $data_imp);
                }

                //TOTALES
                $data_tot= array(
                    "items_Doc" =>  $items,
                    "monto_Gravado" =>  floatval(number_format($fact_con_miva, 2, '.', '')),
                    "monto_Exento" =>  floatval(number_format($fact_sin_miva, 2, '.', '')), 
                    "total_IVA" =>  floatval(number_format($fact_iva, 2, '.', '')), 
                    "total_Doc" =>  floatval(number_format($fact_tot_fact, 2, '.', '')), 
                    "total_Letras" =>  $con_letra,
                    "total_Desc_Letras" => null,//EN CASO DE DESCUENTO VALIDAR
                    "impuestos" =>  $array_imp,
                    "pagos" => $data_pago , 
                );


                //INFORMACION ADICIONAL
                $data_info=array();
                if(!empty($sucu_val_site)){
                    $data_info=array(
                        "site" => $sucu_cod_site,
                        "campo" => "",
                        "valor" => $sucu_val_site
                    );
                }

                /**
                 * EMPIEZA ENVIO
                 */

                $headers = array(
                    "Content-Type:application/json",
                    "Authorization:Bearer $this->empr_token_api"
                );

                              //JSON EMISION DOCUMENTO

                  $data_doc = array(
                    "tipo_Doc" =>  3,//1-FACTURA, 2- NOTA DE CREDITO, 3- NOTA DE DEBITO
                    "num_Doc" =>  $fact_nse_fact.$fact_num_preimp,
                    "num_Doc_Afec" =>  $numDocfectado, //
                    "fechaEmision" =>  $fechaEmision, //VALIDAR FORMATO FECHA 
                    "fechaVencimiento" =>  $fechaVencimiento, //VALIDAR FORMATO FECHA 
                    "tipoPago" =>  $tipoPago,
                    "serie" =>  $fact_nse_fact,
                    "sucursal" =>$sucu_nom_sucu ,
                    "moneda" => $simbolo_moneda , 
                    "vendedor" =>  $usuario_user,
                    "comprador" =>   $data_cli,
                    "totales" =>   $data_tot,
                    "conceptos" =>   $data_detalle,
                    "addInfo" =>   $data_info,
                );


                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url.'/EmitirDocumento');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_doc));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $respuesta = curl_exec($ch);
                


                            if (!curl_errno($ch)) {
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $data_json = json_decode($respuesta, true);

                                switch ($http_code) {
                                    case 200:
                                        try {


                                            $this->oIfx->QueryT('BEGIN;');


                                            $result_txt = $data_json['mensaje'];

                                            $ctrl_num = $data_json['resultado']['nroControl'];

                                            $fechaAsignacion = $data_json['resultado']['fechaAsignacion'];




                                          
                                            //FORMATO PERSONALIZADO - VENEZUELA
                                            //reporte_factura_personalizado($fact_cod_fact, $nombre_documento, $fact_cod_sucu, $ruta_pdf);

                                            //GENERAR ARCHIVO XML

                                           $nombre_documento='deb_'.$fact_cod_fact;
                                           $numDoc=$fact_nse_fact.$fact_num_preimp;

                                           //PDF
                                           $this->GenerarXmlPdf($fact_cod_fact, 2, 1, $ctrl_num, $numDoc);
                                           
                                           //XML
                                           $this->GenerarXmlPdf($fact_cod_fact, 2, 2, $ctrl_num, $numDoc);
                                            

                                            $div_button = '<div class="btn-group" role="group" aria-label="...">
                                            <a href="upload/xml/deb_' . $fact_cod_fact . '.xml" download="' . $nombre_documento . '.xml">
                                                <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                            </a>
                                            <a href="upload/pdf/deb_' . $fact_cod_fact . '.pdf" download="' . $nombre_documento . '.pdf">
                                                <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                            </a>
                                        </div>';

                                           

                                            $sql_update = "UPDATE saefact SET fact_aprob_sri = 'S', fact_fech_sri='$fechaAsignacion',  
                                            fact_user_sri=$id_usuario, fact_cod_hash='$ctrl_num'
                                             WHERE fact_cod_fact = $fact_cod_fact ;";

                                            $this->oIfx->QueryT($sql_update);
                                            $this->oIfx->QueryT('COMMIT;');

                                            

                                            $result = array(
                                                'div_button' => $div_button,
                                                'result_ws' => $result_txt,
                                                'result_email' => 'Autorizado',
                                            );
                                        } catch (Exception $e) {
                                            $this->oIfx->QueryT('ROLLBACK;');
                                            throw new Exception($e->getMessage());
                                        }
                                        break;
                                    case 400:

                                        ///ERRORES
                                        $mensaje = $data_json['mensaje'];
                                        $error_api = $mensaje.' '.$data_json['validaciones'][0]['error'].' '.$data_json['validaciones'][0]['mensaje'];
                                        $error_sri = substr($error_api, 0, 255);

                                        $this->oIfx->QueryT('BEGIN;');

                                        $sql_update = "UPDATE saefact SET fact_erro_sri = '$error_sri' WHERE fact_cod_fact = $fact_cod_fact";
                                        $this->oIfx->QueryT($sql_update);
                                        
                                        $this->oIfx->QueryT('COMMIT;');

                                        throw new Exception($error_api);
                                        break;
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

function EnviarNotaCredito($ncre_cod_ncre)
{
        try {

            session_start();
            $id_usuario = $_SESSION['U_ID'];
            $idempresa = $_SESSION['U_EMPRESA'];
            $empr_cod_pais = $_SESSION['U_PAIS_COD'];

            // IMPUESTOS POR PAIS
        $sql = "select p.impuesto, p.etiqueta, p.porcentaje from comercial.pais_etiq_imp p where
        p.pais_cod_pais = $empr_cod_pais ";
        unset($array_imp);
        unset($array_porc);
        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {
                do {
                        $impuesto      = $this->oIfx->f('impuesto');
                        $etiqueta     = $this->oIfx->f('etiqueta');
                        $porcentaje_impuesto = $this->oIfx->f('porcentaje');
                        $array_imp[$impuesto] = $etiqueta;
                        $array_porc[$impuesto] = $porcentaje;

                    }while ($this->oIfx->SiguienteRegistro());
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
                        //FORMATO ESTANDAR VENEZUELA
                        include_once('../../Include/Formatos/comercial/factura_venezuela.php');
                    }
                } else {
                    //FORMATO ESTANDAR VENEZUELA
                    include_once('../../Include/Formatos/comercial/factura_venezuela.php');
                }
            }
            $this->oIfx->Free();


            //ARRAY UNIDADES - 
            $sql = "select unid_cod_unid, unid_sigl_unid from saeunid where unid_cod_empr=$idempresa";
            unset($array_unidad);
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                            $array_unidad[$this->oIfx->f('unid_cod_unid')] = $this->oIfx->f('unid_sigl_unid');
                        
                    } while ($this->oIfx->SiguienteRegistro());
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
            fac.ncre_dsg_valo as fact_dsg_valo,
	        fac.ncre_fech_venc as fact_fech_venc,
	        fac.ncre_cm1_ncre as fact_cm4_fact,
	        fac.ncre_cm1_ncre as fact_cm1_fact,
            fac.ncre_cm1_ncre as motivo_ncre,
	        fac.ncre_fech_fact as fact_hor_fin,
	        fac.ncre_cod_sucu as fact_cod_sucu,
	        cli.clv_con_clpv,
            cli.clpv_cod_ctrb,
            cli.clpv_ctrb_sn,
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

                        $fechaEmision = $fact_fech_fact.'T'.'12:00:00';
                        $fechaVencimiento = $fact_fech_venc.'T'.'12:00:00';


                        $fact_iva = $this->oIfx->f('fact_iva');
                        $fact_iva = floatval(number_format($fact_iva, 2, '.', ''));
                        $fact_con_miva = $this->oIfx->f('fact_con_miva');
                        $fact_con_miva = floatval(number_format($fact_con_miva, 2, '.', ''));
                        $fact_sin_miva = $this->oIfx->f('fact_sin_miva');
                        $fact_sin_miva = floatval(number_format($fact_sin_miva, 2, '.', ''));
                        $fact_dsg_valo  = $this->oIfx->f('fact_dsg_valo');

                        //TOTAL
                        $fact_tot_fact = $fact_con_miva + $fact_iva + $fact_sin_miva - $fact_dsg_valo ;
                        $fact_tot_fact = floatval(number_format($fact_tot_fact, 2, '.', ''));


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

                        //DATOS COMPRADOR
                        $clpv_cod_ctrb = $this->oIfx->f('clpv_cod_ctrb');
                        if(empty($clpv_cod_ctrb)){
                            throw new Exception("Tipo de Comprador no ingresado configure en la Ficha del cliente");
                        }
                        $clpv_ctrb_sn = $this->oIfx->f('clpv_ctrb_sn');
                        if($clpv_ctrb_sn=='S'){
                            $clpv_ctrb_sn=true;
                        }
                        else{
                            $clpv_ctrb_sn=false;
                        }


                        if ($fact_cm4_fact == null) {
                            $fact_cm4_fact = 0;
                        }


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


            //MONEDA

            $sql="select mone_des_mone,mone_smb_mene from saemone where mone_cod_mone=$fact_cod_mone";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $moneda = $this->oIfx->f('mone_des_mone');
                    $simbolo_moneda = $this->oIfx->f('mone_smb_mene');
                }
            }

            //CODIGO DE SUCURSAL
            $codigoSucursal = 0;
            $sqlsuc = "select sucu_alias_sucu, sucu_nom_sucu, sucu_cod_site, sucu_val_site from saesucu where sucu_cod_empr= $idempresa and sucu_cod_sucu=$fact_cod_sucu";
            if ($this->oIfx->Query($sqlsuc)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $sucu_nom_sucu = $this->oIfx->f('sucu_nom_sucu');
                    $sucu_cod_site = $this->oIfx->f('sucu_cod_site');
                    $sucu_val_site = $this->oIfx->f('sucu_val_site');
                }
            }


            //CODIGO DEL PAIS DEL CLIENTE

            $sqlp="select clpv_cod_pais from saeclpv where clpv_cod_clpv=$fact_cod_clpv";
            if ($this->oIfx->Query($sqlp)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $codigoPaiscliente = $this->oIfx->f('clpv_cod_pais');
                }
            }

            //TELEFONOS

            //$sql="";

            //PAIS CLIENTE

            if(empty($codigoPaiscliente)) $codigoPaiscliente='NULL';

            $sqlp="select pais_cod_inte from saepais where pais_cod_pais=$codigoPaiscliente";
            if ($this->oIfx->Query($sqlp)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $Paiscliente = $this->oIfx->f('pais_cod_inte');
                }
            }

            if(empty($Paiscliente)){
                $Paiscliente ='VEF';
            }


            //DATOS DEL USUARIO

            $sqlus = "select usuario_user from comercial.usuario where usuario_id=$id_usuario";
            if ($this->oIfx->Query($sqlus)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $usuario_user = $this->oIfx->f('usuario_user');
                }
            }



            /** CONTROL PARA NC AUTORIZADA */
            if ($fact_aprob_sri == 'N') {

                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetrasMone($fact_tot_fact, $moneda));


                /**
                 * EXPLODE FACTURA MODIFICA
                 */


                if ($fact_cod_ndb) {

                    $sql = "SELECT
	                fac.fact_nse_fact, fac.fact_num_preimp, fac.fact_auto_sri, fac.fact_tot_fact,
                    fac.fact_iva, fac.fact_con_miva, fac.fact_sin_miva, fac.fact_tot_fact,
                    fac.fact_otr_fact, fac.fact_dsg_valo
                    FROM
	                saefact fac inner join saeclpv cli on fac.fact_cod_clpv = cli.clpv_cod_clpv
                    WHERE fac.fact_cod_fact = $fact_cod_ndb;";

                    if ($this->oIfx->Query($sql)) {
                        if ($this->oIfx->NumFilas() > 0) {
                            do {
                                $fact_num_preimp_afect = $this->oIfx->f('fact_num_preimp');
                                $fact_nse_fact_afect = substr($this->oIfx->f('fact_nse_fact'), 3, 9);
                                $fact_auto_sri = $this->oIfx->f('fact_auto_sri');

                                $fact_dsg_valo  = $this->oIfx->f('fact_dsg_valo');

                                $fact_iva_afec = $this->oIfx->f('fact_iva');
                                $fact_iva_afec = floatval(number_format($fact_iva_afec, 2, '.', ''));
                                
                                $fact_con_miva_afec = $this->oIfx->f('fact_con_miva');
                                $fact_con_miva_afec = floatval(number_format($fact_con_miva_afec, 2, '.', ''));
                                
                                $fact_sin_miva_afec = $this->oIfx->f('fact_sin_miva');
                                $fact_sin_miva_afec = floatval(number_format($fact_sin_miva_afec, 2, '.', ''));
                                
                                $fact_sub_fac_afec=  $this->oIfx->f('fact_tot_fact');
                                $fact_sub_fac_afec = floatval(number_format($fact_sub_fac_afec, 2, '.', ''));

                                $fact_otr_fact_afec=  $this->oIfx->f('fact_otr_fact');
                                $fact_otr_fact_afec = floatval(number_format($fact_otr_fact_afec, 2, '.', ''));



                                $fact_tot_fact_afec = $fact_con_miva_afec + $fact_iva_afec + $fact_sin_miva_afec - $fact_dsg_valo ;
                                $fact_tot_fact_afec = floatval(number_format($fact_tot_fact_afec, 2, '.', ''));

                                
                            } while ($this->oIfx->SiguienteRegistro());
                        }
                    }
                    $this->oIfx->Free();

                    $numDocfectado = $fact_nse_fact_afect.$fact_num_preimp_afect;


                /**VALIDACION IMPUESTO IGTF UNICAMENTE SE APLCIA CUANDO SE HACE LA DEVOLUCION DEL TOTAL DE LA FACTURA */

                if($fact_tot_fact==$fact_tot_fact_afec){
                            //DATOS DE LA FORMA DE PAGO - SE PUEDE TOMAR DEL ALIAS

                        $sql = "SELECT
                        fx.fxfp_cod_fpag,
                        fx.fxfp_val_fxfp,
                        fx.fxfp_fec_fin,
                        fx.fxfp_cot_fpag,
                        fx.fxfp_cod_mone,
                        fp.fpag_cod_alias
                        FROM
                        saefxfp fx,saefpag fp
                        WHERE 
                        fp.fpag_cod_fpag = fx.fxfp_cod_fpag and
                        fp.fpag_cod_empr=$idempresa and
                        fx.fxfp_cod_fact = $fact_cod_ndb ;";
                        if ($this->oIfx->Query($sql)) {
                            if ($this->oIfx->NumFilas() > 0) {
                                $j = 0;
                                $array_fp=array();

                                $base_imponible_igtf=0;
                                do {
                        

                                    $fxfp_val_fxfp  = $this->oIfx->f('fxfp_val_fxfp');
                                    $fpag_cod_alias   = $this->oIfx->f('fpag_cod_alias');
                                    $tipo           = $this->oIfx->f('fxfp_cot_fpag');
                                    $fxfp_cod_mone  = $this->oIfx->f('fxfp_cod_mone');

                                    if ($this->pcon_mon_base != $fxfp_cod_mone) {
                                        $base_imponible_igtf+= $fxfp_val_fxfp;
                                    }

                                    

                                    $sql = "SELECT mone_sgl_mone, mone_des_mone from saemone where mone_cod_mone = $fxfp_cod_mone ";
                                    if ($this->oCon->Query($sql)) {
                                        if ($this->oIfx->NumFilas() > 0) {
                                            do {
                                                $mone_sgl_mone = $this->oCon->f('mone_sgl_mone');
                                                $mone_des_mone = $this->oCon->f('mone_des_mone');
                                            } while ($this->oCon->SiguienteRegistro());
                                        }
                                    }
                                    $this->oCon->Free();

                                

                                    if ($tipo == 'EFE') {
                                        $tipoPago = 1;
                                    } else if ($tipo == 'CRE') {
                                        $tipoPago = 0;
                                    } else {
                                        $tipoPago = 1;
                                    }


                                    $data_fp=array(
                                        "forma" => $j,
                                        "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                        "moneda" => $mone_sgl_mone,
                                        "tipoCambio" => 0
                                    );
                                    array_push($array_fp, $data_fp);


                                    $j++;

                                } while ($this->oIfx->SiguienteRegistro());
                            }
                        }
                        $this->oIfx->Free();
                    }

                    //DATOS DE LA FORMA DE PAGO

                    $sql = "SELECT
                        fx.fxfp_cod_fpag,
                        fx.fxfp_val_fxfp,
                        fx.fxfp_fec_fin,
                        fx.fxfp_cot_fpag,
                        fp.fpag_cod_alias
                        FROM
                        saefxfp fx,saefpag fp
                        WHERE 
                        fp.fpag_cod_fpag = fx.fxfp_cod_fpag and
                        fp.fpag_cod_empr=$idempresa and
                        fx.fxfp_cod_fact = $fact_cod_ndb ;";
                        if ($this->oIfx->Query($sql)) {
                            if ($this->oIfx->NumFilas() > 0) {
                                $j = 0;
                                do {
                        

                                    $fxfp_val_fxfp  = $this->oIfx->f('fxfp_val_fxfp');
                                    $fpag_cod_alias   = $this->oIfx->f('fpag_cod_alias');
                                    $tipo           = $this->oIfx->f('fxfp_cot_fpag');

                                    if ($this->pcon_seg_mone == $fact_cod_mone) {
                                        $fxfp_val_fxfp  = $fxfp_val_fxfp / $fact_val_tcam;
                                    }

                                

                                    if ($tipo == 'EFE') {
                                        $tipoPago = 1;
                                    } else if ($tipo == 'CRE') {
                                        $tipoPago = 0;
                                    } else {
                                        $tipoPago = 1;
                                    }

                                } while ($this->oIfx->SiguienteRegistro());
                            }
                        }
                        $this->oIfx->Free();
                } else {
                    if (strlen($fact_aux_preimp) >= 11 && $fact_cod_ndb > 0) {
                        $exp_fac = explode("-", $fact_aux_preimp);
                        $nse_fac = substr($exp_fac[0], 3, 9);
                        $num_fac = $exp_fac[1];
                        $numDocfectado = $nse_fac.$num_fac;
                    } else {
                        $numDocfectado = $fact_aux_preimp;
                    }

                    $tipoPago = 1;
                }

                //ARRAY TELEFONOS


                $sql="SELECT tlcp_tlf_tlcp

                from saetlcp
            
                where tlcp_cod_empr = $idempresa
            
                and tlcp_cod_clpv=$fact_cod_clpv";

                $array_telf=array();

                 if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {

                            $tlcp_tlf_tlcp  = $this->oIfx->f('tlcp_tlf_tlcp');


                            array_push($array_telf, $tlcp_tlf_tlcp);

                            $j++;
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

            //ARRAY CORREOS

                $sql="SELECT DISTINCT(emai_ema_emai) as correo

                from saeemai
            
                where emai_cod_empr = $idempresa
            
                and emai_cod_clpv=$fact_cod_clpv";
                $array_correo=array();

                 if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {

                            $email_clpv  = $this->oIfx->f('correo');

                            array_push($array_correo, $email_clpv);
                            $j++;
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();


                
                //JSON CLIENTE

                $data_cli= array(
                    "comprador_Ctrb" =>  $clpv_ctrb_sn,//VALIDAR
                    "doc_NoDomiciliado" =>  false,//VALIDAR
                    "cod_Comprador" =>  $fact_cod_clpv, //validar - Codigo interno del cliente
                    "comprador_Tipo_ID" =>  $clpv_cod_ctrb, //validar - Tipo de persona del cliente posibles valores (J,G,V,E) Juridica, Gubernamental, Venezolana o Extrajera respectivamente
                    "comprador_Num_ID" =>  $fact_ruc_clie,  
                    "razon_Social" =>  $fact_nom_cliente,
                    "direccion" =>  $fact_dir_clie,
                    "pais" => $Paiscliente , 
                    "telefonos" =>$array_telf,
                    "emails" => $array_correo
                );

                

                //ITEMS NOTA DE CREDITO

                $sqli="select count(*) items from saedncr where dncr_cod_ncre=$ncre_cod_ncre";
                if ($this->oIfx->Query($sqli)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $items = $this->oIfx->f('items');
                    }
                }

                //////////////////////DETALLES SERVICIOS/PRODUCTOS//////////////////////
                $sql = "select 
                dncr_cant_dfac as dfac_cant_dfac,
                dncr_cod_prod as dfac_cod_prod,
                dncr_det_dncr as dfac_det_dfac,
                dncr_cod_unid as dfac_cod_unid, 
                dncr_mont_total as dfac_mont_total,
                dncr_por_iva as dfac_por_iva,
                dncr_exc_iva as dfac_exc_iva,
                dncr_precio_dfac as dfac_precio_dfac,
                dncr_des1_dfac as dfac_des1_dfac,
                dncr_des2_dfac as dfac_des2_dfac,
                dncr_por_dsg as dfac_por_dsg
                from saedncr
                where dncr_cod_ncre = $ncre_cod_ncre;";
                $j=0;
                    if ($this->oIfx->Query($sql)) {
                        if ($this->oIfx->NumFilas() > 0) {
                      
                            do {
                                $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                                $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                                $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                                $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                                $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                                $dfac_cod_unid = $this->oIfx->f('dfac_cod_unid');
                                $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');
    
                                $dfac_des1_dfac = $this->oIfx->f('dfac_des1_dfac');
                                $dfac_des2_dfac = $this->oIfx->f('dfac_des2_dfac');
                                $dfac_por_dsg = $this->oIfx->f('dfac_por_dsg');

                                $prec_Item = $dfac_mont_total;

    
    
                                $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;
    
                                $porcentaje_descuento=$dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;
    
                                if ($descuento > 0){
                                    $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                                    $descuento =round($descuento, 2, PHP_ROUND_HALF_UP);
                                }
                                else{
                                    $descuento = '0';
                                }
                               
                                $valor_iva=0;
                                if (round($dfac_por_iva, 2) > 0) {
                                    $porcentaje_impuesto=$dfac_por_iva;
    
                                    $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                                    $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;
                                    $dfac_mont_total = $dfac_mont_total + $valor_iva;
                                }
    
                                /*if ($this->pcon_seg_mone == $fact_cod_mone) {
                                    $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                                }*/
    
                                if (empty($dfac_det_dfac)) {
                                    $dfac_det_dfac = 'Sin detalle';
                                }
    
                                //CONSULTA TIPO DE PRODUCTO
    
                                $sqlpro="select prod_cod_tpro from saeprod where prod_cod_prod='$dfac_cod_prod' and prod_cod_empr=$idempresa";
                                if ($this->oCon->Query($sqlpro)) {
                                    if ($this->oCon->NumFilas() > 0) {
    
                                        $tipo_item = $this->oCon->f('prod_cod_tpro');
                                      
                                    }
                                }
    
    
    
                                if(round($descuento)==0){
                                    $descuento=null;
                                }
    
                                    $data_detalle[$j++] =
                                    [
                                        "num_Linea" => $j, 
    
                                        "item_Cod" => $dfac_cod_prod, //CORRESPONDE AL CODIGO DE PRODUCTO DEL SISTEMA
    
                                        "tipo_Item" => $tipo_item,
    
                                        "desc_Item" => $dfac_det_dfac,
    
                                        "cantidad" => intval($dfac_cant_dfac),
    
                                        "unidad_Medida" => $array_unidad[$dfac_cod_unid],
    
                                        "prec_Unitario" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
    
                                        "prec_Unitario_Desc" => 0,
    
                                        "bonif_Item" => null,
    
                                        "descBonif_Item" => null,
    
                                        "desc_Monto" => $descuento,
    
                                        "prec_Item" => floatval(number_format($prec_Item, 2, '.', '')),
    
                                        "cod_Imp" => 1, //VALIDAR
    
                                        "tasa_IVA" => floatval(number_format($dfac_por_iva, 2, '.', '')),
    
                                        "valor_IVA" => floatval(number_format($valor_iva, 2, '.', '')),
    
                                        "total_Item" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    ];
                            }while ($this->oIfx->SiguienteRegistro());
                        }
                    }
                    $this->oIfx->Free();

                //JSON TOTALES
                $array_imp=array();

                if (round($fact_con_miva,2)>0){
                    $data_imp=array(
                        "cod_Impuesto" =>  1,//VALIDAR                    
                        "alicuotaImp" =>  floatval(number_format($porcentaje_impuesto, 2, '.', '')),
                        "base_Imponible" =>  floatval(number_format($fact_con_miva, 2, '.', '')),
                        "total_Impuesto" =>  floatval(number_format($fact_iva, 2, '.', '')),
    
                    );
                    array_push($array_imp, $data_imp);
                }
                //VALIDACION EXCENTO
                if(round($fact_sin_miva,2)>0){
                    $data_imp=array(
                        "cod_Impuesto" =>  4,//VALIDAR                    
                        "alicuotaImp" =>  floatval(number_format(0, 2, '.', '')),
                        "base_Imponible" =>  floatval(number_format($fact_sin_miva, 2, '.', '')),
                        "total_Impuesto" =>  floatval(number_format(0, 2, '.', '')),
                    );
                    array_push($array_imp, $data_imp);
                }

                 /**VALIDACION IMPUESTO IGTF UNICAMENTE SE APLCIA CUANDO SE HACE LA DEVOLUCION DEL TOTAL DE LA FACTURA */

                 if($fact_tot_fact==$fact_tot_fact_afec){
                    ///VALIDACION PAGO CON DIVISAS(MONEDA EXTRANJERA)
                    if(round($fact_otr_fact_afec,2)>0){
                        $data_imp=array(
                            "cod_Impuesto" =>  3,
                            "Tasa" => $fact_val_tcam,                    
                            "alicuotaImp" =>  floatval(number_format(3, 2, '.', '')),
                            "base_Imponible" =>  floatval(number_format($base_imponible_igtf, 2, '.', '')),
                            "total_Impuesto" =>  floatval(number_format($fact_otr_fact_afec, 2, '.', '')),
                        );
                        array_push($array_imp, $data_imp);
                    }

                 }



                //TOTALES
                $data_tot= array(
                    "items_Doc" =>  $items,
                    "monto_Gravado" =>  floatval(number_format($fact_con_miva, 2, '.', '')),
                    "monto_Exento" =>  floatval(number_format($fact_sin_miva, 2, '.', '')), 
                    "total_IVA" =>  floatval(number_format($fact_iva, 2, '.', '')), 
                    "total_Doc" =>  floatval(number_format($fact_tot_fact, 2, '.', '')), 
                    "total_Letras" =>  $con_letra,
                    "total_Desc_Letras" => null,//EN CASO DE DESCUENTO VALIDAR
                    "impuestos" =>  $array_imp,
                    "pagos" => $array_fp , 
                );


                //INFORMACION ADICIONAL
                $data_info=array();
                if(!empty($sucu_val_site)){
                    $data_info=array(
                        "site" => $sucu_cod_site,
                        "campo" => "",
                        "valor" => $sucu_val_site
                    );
                }

                /////CORREO DE PRUEBA COMENTAR
                //$fact_email_clpv = 'andres.garcia@sisconti.com';


                /**
                 * EMPIEZA ENVIO
                 */

                 $headers = array(
                    "Content-Type:application/json",
                    "Authorization:Bearer $this->empr_token_api"
                );

                              //JSON EMISION DOCUMENTO

                  $data_doc = array(
                    "tipo_Doc" =>  2,//1-FACTURA, 2- NOTA DE CREDITO, 3- NOTA DE DEBITO
                    "num_Doc" =>  $fact_nse_fact.$fact_num_preimp,
                    "num_Doc_Afec" =>  $numDocfectado, //
                    "fechaEmision" =>  $fechaEmision, //VALIDAR FORMATO FECHA 
                    "fechaVencimiento" =>  $fechaVencimiento, //VALIDAR FORMATO FECHA 
                    "tipoPago" =>  $tipoPago,
                    "serie" =>  substr($fact_nse_fact, 0, 3),
                    "sucursal" =>$sucu_nom_sucu ,
                    "moneda" => $simbolo_moneda , 
                    "vendedor" =>  $usuario_user,
                    "comprador" =>   $data_cli,
                    "totales" =>   $data_tot,
                    "conceptos" =>   $data_detalle,
                    "addInfo" =>   $data_info,
                );


                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url.'/EmitirDocumento');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_doc));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $respuesta = curl_exec($ch);
                


                            if (!curl_errno($ch)) {
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $data_json = json_decode($respuesta, true);

                                switch ($http_code) {
                                    case 200:
                                        try {


                                            $this->oIfx->QueryT('BEGIN;');

                                            $result_txt = $data_json['mensaje'];

                                            $ctrl_num = $data_json['resultado']['nroControl'];

                                            $fechaAsignacion = $data_json['resultado']['fechaAsignacion'];

                                           

                                            //FORMATO PERSONALIZADO - BOLIVIA
                                            //reporte_factura_personalizado($fact_cod_fact, $nombre_documento, $fact_cod_sucu, $ruta_pdf);


                                           $nombre_documento='cred_'.$ncre_cod_ncre;
                                           $numDoc=$fact_nse_fact.$fact_num_preimp;

                                           //PDF
                                           $this->GenerarXmlPdf($ncre_cod_ncre, 3, 1, $ctrl_num, $numDoc);
                                           
                                           //XML
                                           $this->GenerarXmlPdf($ncre_cod_ncre, 3, 2, $ctrl_num, $numDoc);


                                            $div_button = '<div class="btn-group" role="group" aria-label="...">
                                            <a href="upload/xml/cred_' . $ncre_cod_ncre . '.xml" download="' . $nombre_documento . '.xml">
                                                <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                            </a>
                                            <a href="upload/pdf/cred_' . $ncre_cod_ncre . '.pdf" download="' . $nombre_documento . '.pdf">
                                                <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                            </a>
                                        </div>';

                                           


                                            $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'S', ncre_user_sri='$id_usuario',  
                                            ncre_fech_sri='$fechaAsignacion', ncre_cod_hash='$ctrl_num'
                                             WHERE ncre_cod_ncre = $ncre_cod_ncre ;";

                                            $this->oIfx->QueryT($sql_update);
                                            $this->oIfx->QueryT('COMMIT;');

                                            

                                            $result = array(
                                                'div_button' => $div_button,
                                                'result_ws' => $result_txt,
                                                'result_email' => 'Autorizado',
                                            );
                                        } catch (Exception $e) {
                                            $this->oIfx->QueryT('ROLLBACK;');
                                            throw new Exception($e->getMessage());
                                        }
                                        break;
                                    case 400:

                                        ///ERRORES
                                        $mensaje = $data_json['mensaje'];
                                        $error_api = $mensaje.' '.$data_json['validaciones'][0]['error'].' '.$data_json['validaciones'][0]['mensaje'];
                                        $error_sri = substr($error_api, 0, 255);

                                        $this->oIfx->QueryT('BEGIN;');

                                        $sql_update = "UPDATE saencre SET ncre_erro_sri = '$error_sri' WHERE ncre_cod_ncre = $ncre_cod_ncre";
                                        $this->oIfx->QueryT($sql_update);
                                        
                                        $this->oIfx->QueryT('COMMIT;');

                                        throw new Exception($error_api);
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
/**
* ANULACION DE DOCUMENTOS 
*/
function AnulacionDocumentos($serie,$num_doc, $control,$motivo){

    try {
        session_start();

        $curl = curl_init();

        $fechaHora = new DateTimeImmutable();
        $fechaHoraActual = $fechaHora->format('Y-m-d\TH:i:s.v\Z');
        
        $serie=substr($serie,0,1);

        $data= array(
            "date_Anulacion"=> $fechaHoraActual,
            "id_Documento" => [
                "serie" => $serie,
                "num_Documento" => $num_doc,
                "num_Control" => $control,
                "uuid" => null
            ],
            "motivo_Anulacion" => $motivo
        );


        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->empr_ws_sri_url.'/AnulacionDocumento',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_POSTFIELDS =>json_encode($data),
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Accept: text/plain",
            "Authorization: Bearer $this->empr_token_api"
        ),
        ));

        $respuesta = curl_exec($curl);

        if (!curl_errno($curl)) {
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $data_json = json_decode($respuesta, true);
          
            switch ($http_code) {

                case 200:
                    $msj="OK";
                break;

                default:
                $msj=$data_json['mensaje'];
                break;
            }
        }
        else {
            $errorMessage = curl_error($curl);
            $msj="Hubo un error no se puede conectar al WebService ($errorMessage)";
        }
    
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }

    return $msj;
}
function GenerarXmlPdf($id_documento, $tipo_documento, $formato,$ctrl, $numDoc)
{


 
        switch ($tipo_documento) {
            //FACTURAS
            case 1:
                //PDF
                if($formato==1){
                    $ruta = 'upload/pdf/fac_' . $id_documento . '.pdf';
                }
                //XML
                elseif($formato==2){
                    $ruta = 'upload/xml/fac_' . $id_documento . '.xml';
                }
                
                break;
            //NOTAS DE DEBITO
             case 2:
                //PDF
                if($formato==1){
                    $ruta = 'upload/pdf/deb_' . $id_documento . '.pdf';
                }
                //XML
                elseif($formato==2){
                    $ruta = 'upload/xml/deb_' . $id_documento . '.xml';
                }
                
                break;
            //NOTAS DE CREDITO
            case 3:
                //PDF
                if($formato==1){
                    $ruta = 'upload/pdf/cred_' . $id_documento . '.pdf';
                }
                //XML
                elseif($formato==2){
                    $ruta = 'upload/xml/cred_' . $id_documento . '.xml';
                }
                break;

        }

        $numDoc=substr($numDoc,0,1);

        //GENERA XML-PDF
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->empr_ws_sri_url."/ObtenerDocumento?Serie=$numDoc&CtrNum=$ctrl&Format=$formato",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            "Accept: text/plain",
            "Authorization: Bearer $this->empr_token_api"
        ),
        ));
        $respuesta = curl_exec($curl);


        $data_json = json_decode($respuesta, true);
        $content=$data_json['resultado']['base64String'];
        if(!empty($content)){
            $data=base64_decode($content);
            file_put_contents($ruta, $data);
        }

        curl_close($curl);
        return true;
}

/**
 * ENVIO DOCUMENTOS API FACTURACION VENEZUELA SMART
 */
function EnviarFacturaSmart($fact_cod_fact)
{


        try {
            session_start();
            $id_usuario = $_SESSION['U_ID'];
            $idempresa = $_SESSION['U_EMPRESA'];
            $empr_cod_pais = $_SESSION['U_PAIS_COD'];

            // IMPUESTOS POR PAIS
        $sql = "select p.impuesto, p.etiqueta, p.porcentaje from comercial.pais_etiq_imp p where
        p.pais_cod_pais = $empr_cod_pais ";
        unset($array_imp);
        unset($array_porc);
        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {
                do {
                        $impuesto      = $this->oIfx->f('impuesto');
                        $etiqueta     = $this->oIfx->f('etiqueta');
                        $porcentaje_impuesto = $this->oIfx->f('porcentaje');
                        $array_imp[$impuesto] = $etiqueta;
                        $array_porc[$impuesto] = $porcentaje_impuesto;

                    }while ($this->oIfx->SiguienteRegistro());
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
                        //FORMATO ESTANDAR VENEZUELA
                        include_once('../../Include/Formatos/comercial/factura_venezuela.php');
                    }
                } else {
                    //FORMATO ESTANDAR VENEZUELA
                    include_once('../../Include/Formatos/comercial/factura_venezuela.php');
                }
            }
            $this->oIfx->Free();


            //ARRAY UNIDADES - 
            $sql = "select unid_cod_unid, unid_sigl_unid from saeunid where unid_cod_empr=$idempresa";
            unset($array_unidad);
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                            $array_unidad[$this->oIfx->f('unid_cod_unid')] = $this->oIfx->f('unid_sigl_unid');
                        
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();


            $sql = "SELECT
	        fac.fact_nse_fact,
	        fac.fact_num_preimp,
	        fac.fact_fech_fact,
            right(fac.fact_hor_ini,8) as fact_hor_ini,
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
            cli.clpv_cod_ctrb,
            cli.clpv_ctrb_sn,
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
                        $fact_hor_ini = $this->oIfx->f('fact_hor_ini');
                        $fact_fech_venc = $this->oIfx->f('fact_fech_venc');
                        

                        $fechaEmision = $fact_fech_fact.'T'.$fact_hor_ini;
                        $fechaVencimiento = $fact_fech_venc.'T'.$fact_hor_ini;

                        $fact_hor_fin = $this->oIfx->f('fact_hor_fin');
                        $fact_cod_clpv = $this->oIfx->f('fact_cod_clpv');
                        $fact_nom_cliente = $this->oIfx->f('fact_nom_cliente');
                        $fact_ruc_clie = $this->oIfx->f('fact_ruc_clie');
                        $fact_dir_clie = $this->oIfx->f('fact_dir_clie');
                        $fact_email_clpv = $this->oIfx->f('fact_email_clpv');

                        if(empty($fact_email_clpv)){
                            $sqlm="select max(emai_ema_emai) as email from saeemai where emai_cod_clpv =$fact_cod_clpv";
                            $fact_email_clpv = consulta_string($sqlm, 'email', $this->oCon, '');
                        }

                        $fact_tlf_cliente = $this->oIfx->f('fact_tlf_cliente');

                        
                        if(empty($fact_tlf_cliente)){
                            $sqlm="select max(tlcp_tlf_tlcp) as movil from saetlcp where tlcp_cod_clpv =$fact_cod_clpv";
                            $fact_tlf_cliente = consulta_string($sqlm, 'movil', $this->oCon, '');
                        }


                        $fact_iva = $this->oIfx->f('fact_iva');
                        $fact_iva = floatval(number_format($fact_iva, 2, '.', ''));
                        $fact_con_miva = $this->oIfx->f('fact_con_miva');
                        $fact_con_miva = floatval(number_format($fact_con_miva, 2, '.', ''));
                        $fact_sin_miva = $this->oIfx->f('fact_sin_miva');
                        $fact_sin_miva = floatval(number_format($fact_sin_miva, 2, '.', ''));
                        $fact_sub_fac=  $this->oIfx->f('fact_tot_fact');
                        $fact_sub_fac = floatval(number_format($fact_sub_fac, 2, '.', ''));

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

                        //VALIDAICON DIRECCION
                        if(empty($fact_dir_clie)){
                            throw new Exception("Direccion no ingresada");
                        }

                        //DATOS COMPRADOR
                        $clpv_cod_ctrb = $this->oIfx->f('clpv_cod_ctrb');
                        if(empty($clpv_cod_ctrb)){
                            throw new Exception("Tipo de Comprador no ingresado configure en la Ficha del cliente");
                        }
                        $clpv_ctrb_sn = $this->oIfx->f('clpv_ctrb_sn');
                        if($clpv_ctrb_sn=='S'){
                            $clpv_ctrb_sn=true;
                        }
                        else{
                            $clpv_ctrb_sn=false;
                        }


                        if ($fact_cm4_fact == null) {
                            $fact_cm4_fact = 0;
                        }

                        $fact_tot_fact = $fact_con_miva + $fact_iva + $fact_sin_miva + $fact_val_irbp - $fact_dsg_valo ;

                        $fact_tot_fact = floatval(number_format($fact_tot_fact, 2, '.', ''));




                        //RIF
                        if (intval($tipo_doc) == 1) {
                            $tipo_docu = '3';
                            // $tipo_envio = '01';
                        }
                        //CEDULA 
                        else if (intval($tipo_doc) == 2) {
                            $tipo_docu = '1';
                            //$tipo_envio = '03';
                        }
                        //PASAPORTE 
                        else if (intval($tipo_doc) == 3) {
                            $tipo_docu = '2';
                            //$tipo_envio = '03';
                        }
                        //EXTRANJERIA
                        else if (intval($tipo_doc) == 4) {
                            $tipo_docu = '2';
                            //$tipo_envio = '03';
                        } 
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();
       

            //MONEDA

            $sql="select mone_des_mone,mone_smb_mene from saemone where mone_cod_mone=$fact_cod_mone";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $moneda = $this->oIfx->f('mone_des_mone');
                    $simbolo_moneda = $this->oIfx->f('mone_smb_mene');
                }
            }




            //CODIGO DE SUCURSAL
            $codigoSucursal = 0;
            $sqlsuc = "select sucu_alias_sucu, sucu_nom_sucu, sucu_cod_site, sucu_val_site from saesucu where sucu_cod_empr= $idempresa and sucu_cod_sucu=$fact_cod_sucu";
            if ($this->oIfx->Query($sqlsuc)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $sucu_nom_sucu = $this->oIfx->f('sucu_nom_sucu');
                    $sucu_cod_site = $this->oIfx->f('sucu_cod_site');
                    $sucu_val_site = $this->oIfx->f('sucu_val_site');
                }
            }


            //CODIGO DEL PAIS DEL CLIENTE

            $sqlp="select clpv_cod_pais from saeclpv where clpv_cod_clpv=$fact_cod_clpv";
            if ($this->oIfx->Query($sqlp)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $codigoPaiscliente = $this->oIfx->f('clpv_cod_pais');
                }
            }

        

            //PAIS CLIENTE

            if(empty($codigoPaiscliente)) $codigoPaiscliente='NULL';

            $sqlp="select pais_cod_inte from saepais where pais_cod_pais=$codigoPaiscliente";
            if ($this->oIfx->Query($sqlp)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $Paiscliente = $this->oIfx->f('pais_cod_inte');
                }
            }

            if(empty($Paiscliente)){
                $Paiscliente ='VEF';
            }


            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                

                //DATOS DE LA FORMA DE PAGO - SE PUEDE TOMAR DEL ALIAS

                $sql = "SELECT
                fx.fxfp_cod_fpag,
                fx.fxfp_val_fxfp,
                fx.fxfp_fec_fin,
                fx.fxfp_cot_fpag,
                fx.fxfp_cod_mone,
                fp.fpag_cod_alias
                FROM
                saefxfp fx,saefpag fp
                WHERE 
                fp.fpag_cod_fpag = fx.fxfp_cod_fpag and
                fp.fpag_cod_empr=$idempresa and
                fx.fxfp_cod_fact = $fact_cod_fact ;";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        $array_fp=array();
                        do {

                            $fxfp_val_fxfp  = $this->oIfx->f('fxfp_val_fxfp');
                            $fpag_cod_alias   = $this->oIfx->f('fpag_cod_alias');
                            $tipo           = $this->oIfx->f('fxfp_cot_fpag');
                            $fxfp_cod_mone  = $this->oIfx->f('fxfp_cod_mone');

                            

                            if(empty($fxfp_cod_mone)) $fxfp_cod_mone = 'NULL';

                            $sql = "SELECT mone_sgl_mone, mone_des_mone from saemone where mone_cod_mone = $fxfp_cod_mone ";

                            if ($this->oCon->Query($sql)) {
                                if ($this->oCon->NumFilas() > 0) {
                                    do {
                                        $mone_sgl_mone = $this->oCon->f('mone_sgl_mone');
                                        $mone_des_mone = $this->oCon->f('mone_des_mone');
                                    } while ($this->oCon->SiguienteRegistro());
                                }
                            }
                            $this->oCon->Free();

                          
                           
                            $tipoPago='';
                            if ($tipo == 'EFE') {
                                $tipoPago = 'EFECTIVO';
                            } else if ($tipo == 'CRE') {
                                $tipoPago = 'CREDITO';
                            } else {
                                $tipoPago = 'EFECTIVO';
                            }

                            if ($this->pcon_mon_base != $fxfp_cod_mone) {
                                $tipoPago.= ' '.$mone_des_mone;
                            }

                            $data_fp=array(
                                "forma" => $tipoPago,
                                "valor" => floatval(number_format($fxfp_val_fxfp, 2, '.', ''))
                                );
                            array_push($array_fp, $data_fp);

                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

               

               
                
                
                //////////////////////DETALLES SERVICIOS/PRODUCTOS//////////////////////

                $sql = "SELECT dfac_cant_dfac, dfac_cod_prod, dfac_nom_prod, dfac_det_dfac, dfac_mont_total, dfac_por_iva, dfac_exc_iva,dfac_cod_lote, dfac_cod_unid, 
                dfac_precio_dfac, dfac_des1_dfac, dfac_des2_dfac, dfac_por_dsg  FROM saedfac WHERE dfac_cod_fact = $fact_cod_fact";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        $cod_imp=4;
                        do {
                            $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                            $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                            $dfac_nom_prod = $this->oIfx->f('dfac_nom_prod');
                            $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                            $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                            $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                            $dfac_cod_lote = $this->oIfx->f('dfac_cod_lote');
                            $dfac_cod_unid = $this->oIfx->f('dfac_cod_unid');
                            $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');

                            $dfac_des1_dfac = $this->oIfx->f('dfac_des1_dfac');
                            $dfac_des2_dfac = $this->oIfx->f('dfac_des2_dfac');
                            $dfac_por_dsg = $this->oIfx->f('dfac_por_dsg');

                            $prec_Item = $dfac_mont_total;
                            $descuento = $dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                            $porcentaje_descuento=$dfac_des1_dfac + $dfac_des2_dfac + $dfac_por_dsg;

                            if ($descuento > 0){
                                $descuento = ($dfac_precio_dfac * $dfac_cant_dfac) - ($dfac_mont_total);
                                $descuento =round($descuento, 2, PHP_ROUND_HALF_UP);
                            }
                            else{
                                $descuento = 0;
                            }
                           
                            $valor_iva=0;

                            $prod_excento=true;
                            if (round($dfac_por_iva, 2) > 0) {

                                $prod_excento=false;
                                $cod_imp=1;
                                $porcentaje_impuesto=$dfac_por_iva;

                                $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                                $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;
                               // $dfac_mont_total = $dfac_mont_total + $valor_iva;
                            } 
                            if (empty($dfac_det_dfac)) {
                                $dfac_det_dfac = '';
                            }

                                $data_detalle[$j++] =
                                [
                                    "codigo" => "001",
                                    "descripcion" => $dfac_nom_prod,
                                    "comentario" => $dfac_det_dfac,
                                    "precio" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                    "cantidad" => intval($dfac_cant_dfac),
                                    "tasa" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                    "impuesto" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "descuento" => floatval(number_format($descuento, 2, '.', '')),
                                    "exento" => $prod_excento,
                                    "monto" => floatval(number_format($dfac_mont_total, 2, '.', ''))
                                ];



                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

            
                /**
                 * EMPIEZA ENVIO
                 */

                $headers = array(
                    "Content-Type:application/json",
                    "Authorization:Bearer $this->empr_token_api"
                );


                $data_doc = array(
                    "rif"=> $this->empr_ruc_empr,//J-987654321-0
                    "trackingid"=> null,
                    "nombrecliente"=> $fact_nom_cliente,
                    "rifcedulacliente"=> $fact_ruc_clie,
                    "emailcliente"=> $fact_email_clpv,
                    "telefonocliente"=>$fact_tlf_cliente,
                    "idtipocedulacliente"=> $tipo_docu,
                    "idtipodocumento"=> 1,//(factura=1, débito=2, nota de crédito=3, Guía de despacho=4
                    "direccioncliente"=> $fact_dir_clie,
                    "subtotal"=> $fact_sub_fac,
                    "exento"=> floatval(number_format($fact_sin_miva, 2, '.', '')),
                    "tasag"=> floatval(number_format($porcentaje_impuesto, 2, '.', '')),
                    "baseg"=> floatval(number_format($fact_con_miva, 2, '.', '')),
                    "impuestog"=> floatval(number_format($fact_iva, 2, '.', '')),
                    "tasar"=> 0,
                    "baser"=> 0,
                    "impuestor"=> 0,
                    "tasaa"=> 0.00,
                    "basea"=> 0.00,
                    "impuestoa"=> 0.00,
                    "tasaigtf"=> 3,
                    "baseigtf"=> 0,
                    "impuestoigtf"=> 0,
                    "total"=> floatval(number_format($fact_tot_fact, 2, '.', '')),
                    "sendmail"=> "1",
                    "relacionado"=> "",
                    "sucursal"=> $fact_cod_sucu,
                    "numerointerno"=> $fact_num_preimp,
                    "tasacambio"=> $fact_val_tcam,
                    "cuerpofactura"=>$data_detalle,
                    "formasdepago" => $array_fp
                );


                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url.'/facturacion');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_doc));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $respuesta = curl_exec($ch);

                var_dump($respuesta);exit;

                            if (!curl_errno($ch)) {
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $data_json = json_decode($respuesta, true);
                                switch ($http_code) {
                                    case 200:
                                        try {


                                            $this->oIfx->QueryT('BEGIN;');


                                            $result_txt = $data_json['mensaje'];

                                            $ctrl_num = $data_json['resultado']['nroControl'];

                                            $fechaAsignacion = $data_json['resultado']['fechaAsignacion'];




                                          
                                            //FORMATO PERSONALIZADO - VENEZUELA
                                            //reporte_factura_personalizado($fact_cod_fact, $nombre_documento, $fact_cod_sucu, $ruta_pdf);

                                            //GENERAR ARCHIVO XML

                                           $nombre_documento='fac_'.$fact_cod_fact;
                                           $numDoc=$fact_nse_fact.$fact_num_preimp;

                                           //PDF
                                           $this->GenerarXmlPdf($fact_cod_fact, 1, 1, $ctrl_num, $numDoc);
                                           
                                           //XML
                                           $this->GenerarXmlPdf($fact_cod_fact, 1, 2, $ctrl_num, $numDoc);
                                            

                                            $div_button = '<div class="btn-group" role="group" aria-label="...">
                                            <a href="upload/xml/fac_' . $fact_cod_fact . '.xml" download="' . $nombre_documento . '.xml">
                                                <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                            </a>
                                            <a href="upload/pdf/fac_' . $fact_cod_fact . '.pdf" download="' . $nombre_documento . '.pdf">
                                                <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                            </a>
                                        </div>';

                                           

                                            $sql_update = "UPDATE saefact SET fact_aprob_sri = 'S', fact_fech_sri='$fechaAsignacion',  
                                            fact_user_sri=$id_usuario, fact_cod_hash='$ctrl_num'
                                             WHERE fact_cod_fact = $fact_cod_fact ;";

                                            $this->oIfx->QueryT($sql_update);
                                            $this->oIfx->QueryT('COMMIT;');

                                            

                                            $result = array(
                                                'div_button' => $div_button,
                                                'result_ws' => $result_txt,
                                                'result_email' => 'Autorizado',
                                            );
                                        } catch (Exception $e) {
                                            $this->oIfx->QueryT('ROLLBACK;');
                                            throw new Exception($e->getMessage());
                                        }
                                        break;
                                    case 400:

                                        ///ERRORES
                                        $mensaje = $data_json['mensaje'];
                                        $error_api = $mensaje.' '.$data_json['validaciones'][0]['error'].' '.$data_json['validaciones'][0]['mensaje'];
                                        $error_sri = substr($error_api, 0, 255);

                                        if(empty($error_sri)){
                                            $error_sri = substr($data_json['errors'], 0, 255);
                                            $error_api =$data_json['errors'];
                                        }

                                        $this->oIfx->QueryT('BEGIN;');

                                        $sql_update = "UPDATE saefact SET fact_erro_sri = '$error_sri' WHERE fact_cod_fact = $fact_cod_fact";
                                        $this->oIfx->QueryT($sql_update);
                                        
                                        $this->oIfx->QueryT('COMMIT;');

                                        throw new Exception($error_api);
                                        break;
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


}
