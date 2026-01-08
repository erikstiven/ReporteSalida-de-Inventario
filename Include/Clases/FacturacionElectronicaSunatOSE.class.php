<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');



class FacturacionElectronicaSunatOSE
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
    public $empr_iva_empr = '';
    public $empr_ws_sri_url = '';


    function __construct($oIfx, $oCon, $idempresa)
    {
        $this->oIfx = $oIfx;
        $this->oCon = $oCon;

        $sql = "SELECT  empr_ruc_empr, empr_nom_empr, empr_dir_empr, empr_mai_empr, 
                    empr_tel_resp, empr_token_api, empr_cod_empr, empr_cod_prov,
                    empr_cod_ciud, empr_cod_parr, empr_cpo_empr, empr_iva_empr, empr_ws_sri_url
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
                    $empr_cod_prov = $oCon->f('empr_cod_prov');
                    $empr_cod_ciud = $oCon->f('empr_cod_ciud');
                    $empr_cod_parr = $oCon->f('empr_cod_parr');
                    $this->empr_cpo_empr = $oCon->f('empr_cpo_empr');
                    $this->empr_iva_empr = $oCon->f('empr_iva_empr');
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

        if (!empty($empr_cod_parr)) {
            $sql = "SELECT parr_des_parr from saeparr where parr_cod_parr = $empr_cod_parr ";
            $this->parr_des_parr = consulta_string($sql, 'parr_des_parr', $oCon, 0);
        } else {
            $this->parr_des_parr = "";
        }
    }

/**
 * CONSULTA ESTADO DE LSO DOCUMENTOS ENVIADOS
 */
function documentos_leidos($fecha_doc=''){

    session_start();
    $curl = curl_init();

    $array_comprobantes=array();

    if(empty($fecha_doc)) $fecha_doc=$_SESSION['dia_actual'];
    
    $url=$this->empr_ws_sri_url.'/reporte/comprobantes/leidos?fecha_inicio='.$fecha_doc.'&fecha_final='.$fecha_doc.'&tipo_documento=00';

    curl_setopt_array($curl, array(
      CURLOPT_URL =>$url ,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    
    $response = curl_exec($curl);

    $cont_pe=0;//PENDIENTES
    $cont_ag=0;//AUTORIZADOS
    $cont_le=0;//LEIDOS
    $cont_err=0;//ERROR

    if (!curl_errno($curl)) {
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        
        switch ($http_code) {

            case 200:

                $data_json = json_decode($response, true);

                foreach($data_json as $res){

                    $estado=$res['bl_estadoRegistro'];

                    if($estado=='N'){
                        $cont_pe++;
                    }
                    elseif($estado=='L'){
                        $cont_le++;
                    }
                    elseif($estado=='P'){
                        $cont_ag++;
                    }
                    elseif($estado=='E'){
                        $cont_err++;
                    }

                }

                $array_comprobantes=[$cont_pe,$cont_ag,$cont_le,$cont_err];
                

                break;
                case 500:
                    $data_json = json_decode($response, true);
                    throw new Exception($data_json);
                break;
        }
        curl_close($curl);

    
    } //CIERRE IF CURL ERRNO 
    else {
        $errorMessage = curl_error($curl);
        throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
    }



    return $array_comprobantes;
   

}


/**
     * ANULACION NOTAS DE CREDITO
     */
    function AnularNotaCredito($ncre_cod_ncre, $motivo){

        try {
            session_start();
            $id_usuario = $_SESSION['U_ID'];
            $idempresa = $_SESSION['U_EMPRESA'];


    $sql = "SELECT
	        fac.ncre_nse_ncre as fact_nse_fact,
	        fac.ncre_num_preimp as fact_num_preimp,
	        fac.ncre_fech_fact as fact_fech_fact,
            fac.ncre_aprob_sri as fact_aprob_sri
	        FROM
	        saencre fac inner join saeclpv cli on fac.ncre_cod_clpv = cli.clpv_cod_clpv
            WHERE fac.ncre_cod_ncre = $ncre_cod_ncre;";

            $fact_aprob_sri = 'N';
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $fact_nse_fact  = $this->oIfx->f('fact_nse_fact');
                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);
                        $fact_num_preimp = $this->oIfx->f('fact_num_preimp');
                        $fact_fech_fact = $this->oIfx->f('fact_fech_fact');
                        $fact_aprob_sri = $this->oIfx->f('fact_aprob_sri');
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

//EL COMP0RBANTE DEBE ESTAR AUTORIZADO EN LA SUNAT
    if($fact_aprob_sri=='N'){

        throw new Exception("COMPROBANTE NO AUTORIZADO NO SE PUEDE EJECUTAR LA ANULACION");

    }
            
            //CABECERA
    $cNumDoce= $this->empr_ruc_empr; //'20502459261'	--RUC emisor
    $cTipDoce= '6'; //'6'	--Tipo documento emisor
    $cRazSoce= $this->empr_nom_empr; //'EMPRESA DE TELECOMUNICACIONES MULTIMEDIA ALFA SAC'	--Rason social emisor
    $cCorEmi = '-';//'-'	--Correo emisor

    $cNumCor = '001';//'001'	--Numero correlativo por d�a
    $cFecRes = date('Ymd');//'20240109'	--Fecha resumen de anulaci�n
    $cNumRes = 'RA-'.$cFecRes.'-'.$cNumCor;//CONCAT('RA-', @cFecRes, '-',@cNumCor)	--Ejm.: RA-20240109-001

    $cFecComp= $fact_fech_fact; //'20240108'	--Fecha de comprobante

   
    $data['cabecera'] = array(
        "numeroDocumentoEmisor" => $cNumDoce,
        "resumenId" => $cNumRes,
        "tipoDocumentoEmisor" => $cTipDoce,
        "correoEmisor" => $cCorEmi,
        "fechaEmisionComprobante" => $cFecComp,
        "fechaGeneracionResumen" => $cFecRes,
        "inHabilitado" => '1',
        "razonSocialEmisor" => $cRazSoce,
        "resumenTipo" => 'RA',
        "bl_estadoRegistro" => 'N',
    );

    //COMENTAR
    //$cSerNum='FM04-00000114';

        //DETALLE
    $cNumComp= $fact_num_preimp;//'12345678'
    $cNumSer = $fact_nse_fact;//'FM01'
    $cCdgTipc= '01';//'01'	--Tipo documento: 01=FACTURA | 07=NOTA CREDITO

    $data['detalle'] = array(
    "numeroDocumentoEmisor" => $cNumDoce,
    "tipoDocumentoEmisor" => $cTipDoce,
    "resumenId" =>$cNumRes,
    "numeroFila" =>'00001',
    "motivoBaja" =>$motivo,
    "numeroDocumentoBaja" => $cNumComp,
    "serieDocumentoBaja" =>$cNumSer,
    "tipoDocumento" =>$cCdgTipc,
    );

    //echo json_encode($data);exit;


    $headers = array(
        "Content-Type:application/json"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url.'/anulacion');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $respuesta = curl_exec($ch);
    


    if (!curl_errno($ch)) {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        


        switch ($http_code) {

            case 200:

                //$this->oIfx->QueryT('BEGIN;');
                $result_txt = 'OK';

                break;
                case 500:

                    $detalle_error = $respuesta;
                    throw new Exception($detalle_error);
                break;
        }
        curl_close($ch);
    } //CIERRE IF CURL ERRNO 
    else {
        $errorMessage = curl_error($ch);
        throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
    }



        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result_txt;

    }
    /**
     * ANULACION FACTURAS
     */
    function AnularFactura($fact_cod_fact, $motivo){

            try {
                session_start();
                $id_usuario = $_SESSION['U_ID'];
                $idempresa = $_SESSION['U_EMPRESA'];



                $sql = "SELECT
                fac.fact_nse_fact,
                fac.fact_num_preimp,
                fac.fact_fech_fact,
                fac.fact_aprob_sri
                FROM
                saefact fac 
                WHERE fac.fact_cod_fact = $fact_cod_fact;";

                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        do {
                            $fact_nse_fact  = $this->oIfx->f('fact_nse_fact');
                            $fact_nse_fact = substr($fact_nse_fact, 3, 9);
                            $fact_num_preimp = $this->oIfx->f('fact_num_preimp');
                            $fact_fech_fact = $this->oIfx->f('fact_fech_fact');
                            $fact_aprob_sri = $this->oIfx->f('fact_aprob_sri');
                        
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                //EL COMP0RBANTE DEBE ESTAR AUTORIZADO EN LA SUNAT
    if($fact_aprob_sri=='N'){

        throw new Exception("COMPROBANTE NO AUTORIZADO NO SE PUEDE EJECUTAR LA ANULACION");

    }




                //CABECERA
        $cNumDoce= $this->empr_ruc_empr; //'20502459261'	--RUC emisor
        $cTipDoce= '6'; //'6'	--Tipo documento emisor
        $cRazSoce= $this->empr_nom_empr; //'EMPRESA DE TELECOMUNICACIONES MULTIMEDIA ALFA SAC'	--Rason social emisor
        $cCorEmi = '-';//'-'	--Correo emisor

        $cNumCor = '001';//'001'	--Numero correlativo por d�a
        $cFecRes = date('Ymd');//'20240109'	--Fecha resumen de anulaci�n
        $cNumRes = 'RA-'.$cFecRes.'-'.$cNumCor;//CONCAT('RA-', @cFecRes, '-',@cNumCor)	--Ejm.: RA-20240109-001

        $cFecComp= $fact_fech_fact; //'20240108'	--Fecha de comprobante

    
        $data['cabecera'] = array(
            "numeroDocumentoEmisor" => $cNumDoce,
            "resumenId" => $cNumRes,
            "tipoDocumentoEmisor" => $cTipDoce,
            "correoEmisor" => $cCorEmi,
            "fechaEmisionComprobante" => $cFecComp,
            "fechaGeneracionResumen" => $cFecRes,
            "inHabilitado" => '1',
            "razonSocialEmisor" => $cRazSoce,
            "resumenTipo" => 'RA',
            "bl_estadoRegistro" => 'N',
        );

        //COMENTAR
        //$cSerNum='FM04-00000114';

            //DETALLE
        $cNumComp= $fact_num_preimp;//'12345678'
        $cNumSer = $fact_nse_fact;//'FM01'
        $cCdgTipc= '01';//'01'	--Tipo documento: 01=FACTURA | 07=NOTA CREDITO

        $data['detalle'] = array(
        "numeroDocumentoEmisor" => $cNumDoce,
        "tipoDocumentoEmisor" => $cTipDoce,
        "resumenId" =>$cNumRes,
        "numeroFila" =>'00001',
        "motivoBaja" =>$motivo,
        "numeroDocumentoBaja" => $cNumComp,
        "serieDocumentoBaja" =>$cNumSer,
        "tipoDocumento" =>$cCdgTipc,
        );

        //echo json_encode($data);exit;


        $headers = array(
            "Content-Type:application/json"
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url.'/anulacion');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta = curl_exec($ch);
        


        if (!curl_errno($ch)) {
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            


            switch ($http_code) {

                case 200:

                    //$this->oIfx->QueryT('BEGIN;');
                    $result_txt = 'OK';

                    break;
                    case 500:

                        $detalle_error = $respuesta;
                        throw new Exception($detalle_error);
                    break;
            }
        } //CIERRE IF CURL ERRNO 
        else {
            $errorMessage = curl_error($ch);
            throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
        }



            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            return $result_txt;

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
                    } else {
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
            fac.fact_cm2_fact,
            fac.fact_cm7_fac,
            fac.fact_cod_contr,
            fac.fact_user_web
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

                        $cod_contrato =  $this->oIfx->f('fact_cod_contr');
                        if ($cod_contrato == 0 || empty($cod_contrato)) {
                            $cod_contrato = trim($this->oIfx->f('fact_cm7_fac'));
                        }

                        if (empty($cod_contrato)) {
                            $cod_contrato = 'NULL';
                        }

                        $fact_user_web = $this->oIfx->f('fact_user_web');


                        $sql_sucu = "SELECT usuario_nombre, usuario_apellido, usuario_user from comercial.usuario where usuario_id = $fact_user_web";
                        if ($this->oCon->Query($sql_sucu)) {
                            if ($this->oCon->NumFilas() > 0) {
                                do {
                                    $usuario_nombre = $this->oCon->f('usuario_nombre');
                                    $usuario_apellido = $this->oCon->f('usuario_apellido');
                                    $cajero = $usuario_apellido . ' ' . $usuario_nombre;
                                } while ($this->oCon->SiguienteRegistro());
                            }
                        }
                        $this->oCon->Free();



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
                        } else {
                            $tipo_docu = '0';
                        }
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            ///MONEDA

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

            ///CODIGO DE SUCURSAL->VALIDAR CONFIGURACION

            $sql = "SELECT sucu_alias_sucu from saesucu where sucu_cod_sucu = $fact_cod_sucu";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $sucu_alias_sucu = $this->oIfx->f('sucu_alias_sucu');
                       
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

                $serie = $fact_nse_fact . '-' . $fact_num_preimp;
                $valor_venta = $fact_con_miva + $fact_sin_miva;


                /*$data = array(
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
                );*/

                $n_orden_compra = rtrim($fact_cm2_fact);

                /*if (strlen($n_orden_compra) > 0) {
                    $data['compra'] = strval($n_orden_compra);
                }*/

                if ($fact_cod_detra > 0) {
                    /*$data['detraccion'] = [
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
                    ];*/
                }

                ///DATOS DE LA FORMA DE PAGO
                $forma_pago = 0;
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

                            /* if ($tipo == 'EFE') {
                                $tipo = 'Contado';
                            } else if ($tipo == 'CRE') {
                                $forma_pago=1;
                                $tipo = 'Credito';

                                $fxfp_fec_fin = $fact_fech_venc;

                                $data['cuotas'][$j++] = [
                                    "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                    "fechaPago" => $fxfp_fec_fin
                                ];
                            } else {
                                $tipo = 'Contado';
                            }*/

                            /*$data['formaPago'] = [
                                "moneda" => $mone_sgl_mone,
                                "tipo" => $tipo,
                                "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', ''))
                            ];*/
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                //CODIG DEL ABONADO 


                $sqlc="select codigo from isp.contrato_clpv where id = $cod_contrato and ( codigo is not null and codigo !='') ";

                $cod_abonado='';
                if ($this->oIfx->Query($sqlc)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $cod_abonado   = $this->oIfx->f('codigo');
                    }
                }


                /**
                 * EMPIEZA ENVIO
                 */

                // ---------------------------------------------------------------------
                // Creamos el insert que vamos a enviar a la OSE
                // ---------------------------------------------------------------------

                if (empty($fact_email_clpv)) {
                    $fact_email_clpv = '-';
                }
                $pais_emisor = 'PE';


                if ($fact_iva > 0) {
                    $fact_sin_miva = $fact_con_miva;
                }


                // Dividir el número en partes entera y decimal
                $numero_a_texto = strtoupper($this->convertirATexto($fact_tot_fact));

                //REGISTRO DE COMPROBANTES
                $cCorEmi = '-';    //Correo emisor
                $cCorAdq = $fact_email_clpv;    // En caso no tenga registrar con gui�n '-'
                $cNumDoce = $this->empr_ruc_empr;    //RUC emisor
                $cTipDoce = '6';    //Tipo documento emisor
                $cCdgTipc = $tipo_envio; //Tipo comprobante: Factura = '01' | Boleta = '03' | Nota de cr�dito = '07'
                $cRazSoce = $this->empr_nom_empr;    //Rason social emisor
                $cNomCom = $this->empr_nom_empr;    //Nombre comercial emisor
                $cSerNum = $fact_nse_fact . '-' . $fact_num_preimp;     //Serie y N�mero de comprobante 
                $cFecEmi = $fact_fech_fact;        //Fecha emisi�n comprobante: AAAA-MM-DD
                $cUbiEmi = $this->empr_cpo_empr;        //Ubigeo emisor
                $cDirEmi = $this->empr_dir_empr;     //Ubicaci�n emisor
                $cUrbEmi = '-';     //Urbanizaci�n emisor
                $cPrvEmi = $this->ciud_nom_ciud;      //Provincia emisor
                $cDptEmi = $this->prov_des_prov;    //Departamento emisor
                $cDstEmi = $this->parr_des_parr;       // Distrito emisor
                $cPaiEmi = $pais_emisor;        // Pais emisor
                $cNumDoca = $fact_ruc_clie;    //N�mero documento adquiriente 
                $cTipDoca = $tipo_docu;    //Tipo documento adquiriente (1=Documento Nacional de Identidad (DNI) | 4=Carnet de extranjer�a | 6 = Registro Unico de Contributentes (RUC) | 7 = Pasaporte | M�s en catalogo 6)
                $cRazSoca = $fact_nom_cliente;    //
                $cTipMon = $mone_sgl_mone;    //Moneda de comprobante: PEN=Soles | USD=Dolares americanos | Mas en catalogo 2
                $cTotValv = $fact_sin_miva;    //Valor de venta
                $cTotIgv = $this->empr_iva_empr;    //Importe IGV
                $cTotImp = $fact_iva;    //Total impuestos
                $cTotIsc = '0.00';    //ISC
                $cTotVta =  $fact_tot_fact;    //Total venta
                $cSwtCont = '0';    // Comprobante de contingencia: 0=No | 1=Si
                $cTotValv = $fact_sin_miva;    //Total valor venta
                $cTotPrec = $fact_tot_fact;    //Total precio de venta
                $cCdgLey1 = '1000';    //Leyenda 1: 1000, N�mero en letras (Impresi�n)

                /*if ($mone_sgl_mone == 'PEN') {
                    // Si moneda es en SOLES
                    $cTexLey1 = 'SON: ' . $numero_a_texto . ' SOLES';
                } else {
                    // Si moneda es en DOLARES
                    $cTexLey1 = 'SON: ' . $numero_a_texto . ' DOLARES AMERICANOS';
                }*/
                $cTexLey1 = 'SON: ' . $con_letra;

                $cCdgA401 = '9011';    //Etiqueta porcentaje IGV en el PDF'
                $cTexA401 = $this->empr_iva_empr . ' %';    //Porcentaje IGV aplicado e impreso en comprobante (Impresi�n)

                $cTipOpe = '1001';        //Tipo de operacion: Venta Interna, ver catalogo 14
                $cHorEmi = date('H:i:s');    //Hora emisi�n: hh:mm:ss


                ///VALIDAR  CAMPO DE LA SUCURSAL
                 $cCdgEstb = $sucu_alias_sucu;    //C�digo local anexo emisor (Establecimientos) Código asignado por SUNAT para el establecimiento anexo declarado en el RUC.

                //LOCAL			@cCdgEstb	DIRECCON
                //OF. MANYLSA	0012        COOP. MANYLSA LTDA 476 CAL.16 MZ M  L 01, ATE
                //OF. VITARTE	0006        AV. CENTRAL 632, ATE
                //OF. HUAYCAN	0001        AV. JOSE CARLOS MARIATEGUI, UCV 9 L58, HUAYCAN, ATE
                //OF. CHOSICA	0007        JR. TACNA N� 219 INT 2, LURIGANCHO
                //OF. PURUCHUCO	0014        AV. NICOLAS AYLLON 4770 INT 122A, ATE


                if ($tipo == 'EFE') {
                    $tipo_pago = 'EFECTIVO';
                } else if ($tipo == 'CRE') {
                    $tipo_pago = 'CREDITO';
                } else {
                    $tipo_pago = 'CONTADO';
                }

                $cEstReg = 'N';     //Estado de registro: N=Nuevo | A:Agregado | L:Le�do

                if(!empty($cod_abonado)){
                    $cCdgAux100_1 = '9407';     //C�digo auxiliar 100_1: C�digo de abonado en sistema 
                    $cTxtAux100_1 = $cod_abonado;       //Texto auxiliar 100_1: Valor C�digo de abonado en sistema (Impresi�n)
                }
                else{
                    $cCdgAux100_1 = null;     //C�digo auxiliar 100_1: C�digo de abonado en sistema 
                    $cTxtAux100_1 = null;       //Texto auxiliar 100_1: Valor C�digo de abonado en sistema (Impresi�n)
                }

                $cCdgAux100_2 = null;     //C�digo auxiliar 100_2: C�digo de cajero en sistema 
                $cTxtAux100_2 = null;     //Texto auxiliar 100_2: Valor C�digo de cajero en sistema (Impresi�n)
                $cCdgAux100_3 = '8267';     //C�digo auxiliar 100_3: Nombre de cajero en sistema 
                $cTxtAux100_3 = $cajero;        //Texto auxiliar 100_3: Valor Nombre de cajero en sistema (Impresi�n)
                $cCdgAux100_4 = null;         //C�digo auxiliar 100_4: 8265 = C�digo Importe devuelto
                $cTxtAux100_4 = null;            //Texto auxiliar 100_4: Valor Importe devuelto (Impresi�n)
                $cCdgAux100_5 = null;            //C�digo auxiliar 100_5: 8266 = C�digo Importe entregado
                $cTxtAux100_5 = null;            //Texto auxiliar 100_5: Valor Importe entregado (Impresi�n)
                $cCdgAux100_6 = '9015';    //C�digo auxiliar 100_6: C�digo Forma de pago
                $cTxtAux100_6 = $tipo_pago;        //Texto auxiliar 100_6: Valor Forma de pago (Impresi�n) CHEQUE | DEPOSITO | EFECTIVO | TRANSFERENCIA | TARJETA | Sin valor: '-')
                $cCdgAux100_7 = '8561';        //C�digo auxiliar 100_7: C�digo Direcci�n establecimiento
                $cTxtAux100_7 = $this->empr_dir_empr;         //Texto auxiliar 100_7: Valor Direcci�n establecimiento (Impresi�n)

                //NOMBRE		@cCdgEstb	DIRECCON
                //OF. MANYLSA	0012        COOP. MANYLSA LTDA 476 CAL.16 MZ M  L 01, ATE
                //OF. VITARTE	0006        AV. CENTRAL 632, ATE
                //OF. HUAYCAN	0001        AV. JOSE CARLOS MARIATEGUI, UCV 9 L58, HUAYCAN, ATE
                //OF. CHOSICA	0007        JR. TACNA N� 219 INT 2, LURIGANCHO
                //OF. PURUCHUCO	0014        AV. NICOLAS AYLLON 4770 INT 122A, ATE 

                // Si el importe supera los S/700

                if ($fact_cod_detra > 0) {
                    $cCdgAux100_8 = null;    //C�digo auxiliar 100_8: 9045 = Cuenta detracci�n
                    $cTxtAux100_8 = null;    //Texto auxiliar 100_8: Valor Cuenta detracci�n (Impresi�n): Obligaci�n sujeta al sistema de detracciones SPOT N� de Cuenta Bco. de la Naci�n: 00-066-025551.
                    // Si el importe es menor a los S/700
                    $cCdgAux100_8 = null;    //C�digo auxiliar 100_8: 9045 = Cuenta detracci�n
                    $cTxtAux100_8 = null;    //Texto auxiliar 100_8: Valor Cuenta detracci�n (Impresi�n): ''
                    $cCdgAux250_1 = null;    //C�digo auxiliar 250_1: 9618 = C�digo Leyenda fideicomiso
                    $cTxtAux250_1 = null;    //Texto auxiliar 250_1: Valor Leyenda fideicomiso (Impresi�n): 'Los importes contenidos en el presente comprobante de pago han sido cedidos a un patrimonio fideicometido administrado por La Fiduciaria S.A.'
                }
                else{
                    $cCdgAux100_8 = null;    //C�digo auxiliar 100_8: 9045 = Cuenta detracci�n
                    $cTxtAux100_8 = null;    //Texto auxiliar 100_8: Valor Cuenta detracci�n (Impresi�n): Obligaci�n sujeta al sistema de detracciones SPOT N� de Cuenta Bco. de la Naci�n: 00-066-025551.
                    // Si el importe es menor a los S/700
                    $cCdgAux100_8 = null;    //C�digo auxiliar 100_8: 9045 = Cuenta detracci�n
                    $cTxtAux100_8 = null;    //Texto auxiliar 100_8: Valor Cuenta detracci�n (Impresi�n): ''
                    $cCdgAux250_1 = null;    //C�digo auxiliar 250_1: 9618 = C�digo Leyenda fideicomiso
                    $cTxtAux250_1 = null;    //Texto auxiliar 250_1: Valor Leyenda fideicomiso (Impresi�n): 'Los importes contenidos en el presente comprobante de pago han sido cedidos a un patrimonio fideicometido administrado por La Fiduciaria S.A.'
                }
                
                //------------------------------------------------------------------------
                // SOLO PARA REGISTRO DE NOTAS DE CREDITO DE LO CONTRARIO DEJAR EN BLANCO
                //------------------------------------------------------------------------
                /*
                $cCdgTipn = '01';        //C�digo tipo nota de credito. Ver catalogo 9
                $cDesTipn = 'ANULACION DE COMPROBANTE';            //Descripci�n de tipo nota de credito. 
                // COD	DESCRIPCION
                // 01	Anulaci�n de la operaci�n	
                // 09	Disminuci�n en el valor	
                // 10	Otros Conceptos 	
                $cRefCdgt = '03';        //Referencia NC, c�digo tipo de documento afectado. 01=FACTURA | 03=BOLETA
                $cRefSern = 'BM01-12345678';            //Referencia NC, serie y n�mero de documento afectado
                // Si @cCdgTipn = '10' OTROS CONCEPTOS, de lo contrario dejar en blanco
                $cRefTipa = '99';            //Referencia NC, referencia adicional
                $cRefNuma = $cRefSern;        //Referencia NC, n�mero documento referencia adicional
                */
                //------------------------------------------------------------------------
                // REGISTRO DE DATOS DE CABECERA PARA COMPROBANTES
                //------------------------------------------------------------------------


                ///VALIDAR PROCESO DE DETRACCIONES
                
                //COMENTAR 
                //$cSerNum='FM04-00000114';

                $data['cabecera'] = array(
                    "correoEmisor" => $cCorEmi,
                    "correoAdquiriente" => $cCorAdq,
                    "numeroDocumentoEmisor" => $cNumDoce,
                    "tipoDocumentoEmisor" => $cTipDoce,
                    "tipoDocumento" => $cCdgTipc,
                    "razonSocialEmisor" => $cRazSoce,
                    "nombreComercialEmisor" => $cNomCom,
                    "serieNumero" => $cSerNum,
                    "fechaEmision" => $cFecEmi,
                    "ubigeoEmisor" => $cUbiEmi,
                    "direccionEmisor" => $cDirEmi,
                    "urbanizacion" => $cUrbEmi,
                    "provinciaEmisor" => $cPrvEmi,
                    "departamentoEmisor" => $cDptEmi,
                    "distritoEmisor" => $cDstEmi,
                    "paisEmisor" => $cPaiEmi,
                    "numeroDocumentoAdquiriente" => $cNumDoca,
                    "tipoDocumentoAdquiriente" => $cTipDoca,
                    "razonSocialAdquiriente" => $cRazSoca,
                    "tipoMoneda" => $cTipMon,
                    "totalValorVentaNetoOpGravadas" => $cTotValv,
                    "totalIgv" => $cTotIgv,
                    "totalImpuestos" => $cTotImp,
                    "totalIsc" => $cTotIsc,
                    "totalVenta" => $cTotVta,
                    "contingencia" => $cSwtCont,
                    "totalValorVenta" => $cTotValv,
                    "totalPrecioVenta" => $cTotVta,
                    "codigoLeyenda_1" => $cCdgLey1,
                    "textoLeyenda_1" => $cTexLey1,
                    "codigoAuxiliar40_1" => $cCdgA401,
                    "textoAuxiliar40_1" => $cTexA401,
                    "tipoOperacion" => $cTipOpe,
                    "horaEmision" => $cHorEmi,
                    "codigoLocalAnexoEmisor" => $cCdgEstb,
                    "bl_EstadoRegistro" => $cEstReg,
                    "codigoAuxiliar100_1" => $cCdgAux100_1,
                    "textoAuxiliar100_1" => $cTxtAux100_1,
                    "codigoAuxiliar100_2" => $cCdgAux100_2,
                    "textoAuxiliar100_2" => $cTxtAux100_2,
                    "codigoAuxiliar100_3" => $cCdgAux100_3,
                    "textoAuxiliar100_3" => $cTxtAux100_3,
                    "codigoAuxiliar100_4" => $cCdgAux100_4,
                    "textoAuxiliar100_4" => $cTxtAux100_4,
                    "codigoAuxiliar100_5" => $cCdgAux100_5,
                    "textoAuxiliar100_5" => $cTxtAux100_5,
                    "codigoAuxiliar100_6" => $cCdgAux100_6,
                    "textoAuxiliar100_6" => $cTxtAux100_6,
                    "codigoAuxiliar100_7" => $cCdgAux100_7,
                    "textoAuxiliar100_7" => $cTxtAux100_7,
                    "codigoAuxiliar100_8" => $cCdgAux100_8,
                    "textoAuxiliar100_8" => $cTxtAux100_8,
                    "codigoAuxiliar250_1" => $cCdgAux250_1,
                    "textoAuxiliar250_1" => $cTxtAux250_1,
                    "codigoSerieNumeroAfectado" => $cCdgTipn,
                    "motivoDocumento" => $cDesTipn,
                    "tipoDocumentoReferenciaPrincip" => $cRefCdgt,
                    "numeroDocumentoReferenciaPrinc" => $cRefSern,
                    "tipoReferenciaAdicional_1" => $cRefTipa,
                    "numeroDocumentoRefeAdicional_1" => $cRefNuma,
                    "codigo_jireh" =>$fact_cod_fact
                );


                //VALIDAR DE DONDE TOMAR LA INFORMACION

                $sqlDire = "SELECT id_provincia, id_canton,     id_ciudad,    id_parroquia, id_sector,     id_barrio,      
                           id_bloque,    nomb_conjunto, num_conjunto, estrato,      id_conjunto,   departamento,  poste,
                           caja,         id_ruta,      ruta,      orden_ruta,       direccion,     referencia,    latitud, 
                           longitud,     id_calle
                    from isp.contrato_clpv
                    where id = $cod_contrato";
                if ($this->oIfx->Query($sqlDire)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $id_provincia   = $this->oIfx->f('id_provincia');
                        $id_canton      = $this->oIfx->f('id_canton');
                        $id_ciudad      = $this->oIfx->f('id_ciudad');
                        $id_parroquia   = $this->oIfx->f('id_parroquia');
                        $id_sector      = $this->oIfx->f('id_sector');
                        $id_barrio      = $this->oIfx->f('id_barrio');
                        $id_bloque      = $this->oIfx->f('id_bloque');
                        $nomb_conjunto  = $this->oIfx->f('nomb_conjunto');
                        $num_conjunto   = $this->oIfx->f('num_conjunto');
                        $estrato        = $this->oIfx->f('estrato');
                        $id_conjunto    = $this->oIfx->f('id_conjunto');
                        $direccion      =substr(trim($this->oIfx->f('direccion')),0,100);


                        if (!empty($id_provincia)) {
                            $sql = "SELECT prov_des_prov from saeprov where prov_cod_prov = $id_provincia ";

                            if ($this->oCon->Query($sql)) {
                                if ($this->oCon->NumFilas() > 0) {
                                    $departamento     = $this->oCon->f('prov_des_prov');
                                }
                            }
                            $this->oCon->Free();
                        }

                        if (!empty($id_ciudad)) {
                            $sql = "SELECT ciud_cod_ciud, ciud_nom_ciud from saeciud where ciud_cod_ciud = $id_ciudad ";
                            if ($this->oCon->Query($sql)) {
                                if ($this->oCon->NumFilas() > 0) {
                                    $distrito     = substr($this->oCon->f('ciud_nom_ciud'),0,30);
                                }
                            }
                            $this->oCon->Free();
                        }

                        if (!empty($id_canton)) {
                            $sql = "SELECT cant_cod_cant, cant_des_cant from saecant where cant_cod_cant = $id_canton and cant_est_cant = 'A' ";
                            if ($this->oCon->Query($sql)) {
                                if ($this->oCon->NumFilas() > 0) {
                                    $provincia     = $this->oCon->f('cant_des_cant');
                                }
                            }
                            $this->oCon->Free();
                        }

                        if (!empty($id_sector)) {
                            $sql = "SELECT id, sector from comercial.sector_direccion where  id = $id_sector ";

                            if ($this->oCon->Query($sql)) {
                                if ($this->oCon->NumFilas() > 0) {
                                    $urbanizacion     = substr($this->oCon->f('sector'),0,25);
                                }
                            }
                            $this->oCon->Free();
                        }
                    }
                }
                $this->oIfx->Free();

                /*-- REGISTRO DE DATOS ANEXOS PARA COMPROBANTE
                -------------------------------------------------------------------------------
                @cDirAdq= 'DIRECCION_CLIENTE'	--Dirección cliente
                @cUbiAdq= 'CODIGO UBIGEO'	--Código ubigeo dirección cliente
                @cUrbAdq= '-'	--Urbanización dirección cliente
                @cPrvAdq= 'NOMBRE_PROVINCIA'	--Provincia de dirección cliente
                @cDptAdq= 'NOMBRE_DEPARTAMENTO'	--Departamento dirección cliente
                @cDstAdq= 'NOMBRE DISTRITO'		--Distrito dirección cliente
                @cSwtFpn= '0'	--Forma pago negociable: 0=Contado | 1= Credito*/


                //INFORMACION DATOS DIRECCION DEL CLIENTE - 

                $array_anexo = ['direccionAdquiriente', 'ubigeoAdquiriente', 'urbanizacionAdquiriente', 'provinciaAdquiriente', 'departamentoAdquiriente', 'distritoAdquiriente', 'paisAdquiriente', 'formaPagoNegociable'];
                $array_anexo_val = [$direccion, '', $urbanizacion, $provincia, $departamento, $distrito, 'PE', '0'];

                $n = 0;
                foreach ($array_anexo as $val) {

                    if (!empty($array_anexo_val[$n])) {
                        $data['anexo'][$n] = array(
                            "tipoDocumentoEmisor" => $cTipDoce,
                            "numeroDocumentoEmisor" => $cNumDoce,
                            "serieNumero" => $cSerNum,
                            "tipoDocumento" => $cCdgTipc,
                            "clave" => $val,
                            "valor" => $array_anexo_val[$n],
                        );
                        
                    }
                    $n++;
                    
                }

                ///DETALLE DE FACTURA

                $sql = "SELECT dfac_cant_dfac, dfac_precio_dfac, dfac_cod_prod, dfac_det_dfac, dfac_mont_total, dfac_por_iva, dfac_exc_iva FROM saedfac WHERE dfac_cod_fact = $fact_cod_fact";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 1;
                        $num_item = '';
                        do {
                            $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                            $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                            $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                            $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                            $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                            $dfac_exc_iva = $this->oIfx->f('dfac_exc_iva');
                            $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                            }

                            $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                            $precio_uni = $dfac_precio_dfac * $porcentaje_iva;
                            $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;

                            $tipAfeIgv = "01";
                            $tipAfeIgvex = "10";
                            if ($dfac_exc_iva == 1) {
                                $tipAfeIgv = "02";
                                $tipAfeIgvex = "20";
                                //$data['mtoImpVenta'] = floatval($dfac_por_iva);
                                // $data['mtoOperExoneradas'] = floatval($fact_tot_fact);
                                //$data['mtoOperGravadas'] = floatval($dfac_por_iva);
                            }

                            if (empty($dfac_det_dfac)) {
                                $dfac_det_dfac = 'Sin detalle';
                            }

                            if ($j < 10) {
                                $num_item = '0' . $j;
                            }

                            /*$data['details'][$j++] =
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
                                ];*/

                            ///VALIDAR INFORMACIÓN

                            $data['detalle'][$j++] =
                                [
                                    "numeroDocumentoEmisor" => $cNumDoce,
                                    "tipoDocumentoEmisor" => $cTipDoce,
                                    "tipoDocumento" => $cCdgTipc,
                                    "serieNumero" => $cSerNum,
                                    "numeroOrdenItem" => $num_item,
                                    "codigoProducto" => $dfac_cod_prod,
                                    "descripcion" => $dfac_det_dfac,
                                    "cantidad" => floatval($dfac_cant_dfac),
                                    "unidadMedida" => "NIU",
                                    "importeTotalSinImpuesto" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "importeUnitarioSinImpuesto" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                    "importeUnitarioConImpuesto" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "codigoImporteUnitarioConImpues" => $tipAfeIgv,
                                    "montoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "tasaIGV" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                    "importeIgv" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "importeTotalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "codigoRazonExoneracion" => $tipAfeIgvex,
                                ];

                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

               


                $headers = array(
                    "Content-Type:application/json"
                );

               //echo json_encode($data);exit;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url.'/factura');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);
                
                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    


                    switch ($http_code) {

                        case 200:
                                        $this->oIfx->QueryT('BEGIN;');
                            
                                
                                            $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_envio . '-' . $fact_nse_fact . '-' . $fact_num_preimp;

                                            $ruta_xml = 'upload/xml/' . $nombre_documento . '.xml';
                                            $ruta_pdf = 'upload/pdf/fac_' . $nombre_documento . '.pdf';
                
                                            //CREACION FORMATO PERSONALZIADO
                                            //$this->GenerarFacturaBoletaXmlPdfPeru($headers, $nombre_documento, $data);
                
                                            //FORMATO PERSONALIZADO - PERU
                
                                            reporte_factura_personalizado($fact_cod_fact, $nombre_documento, $fact_cod_sucu, $ruta_pdf);
                                            /*
                                            <a href="upload/xml/fac_' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                                    <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                                </a>
                                            */
                
                                            $div_button = '<div class="btn-group" role="group" aria-label="...">
                                                
                                                <a href="upload/pdf/fac_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                                    <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                                </a>
                                            </div>';
                
                                            $sql_update = "UPDATE saefact SET fact_aprob_sri = 'F',fact_cod_hash='$fact_cod_hash', fact_user_sri=$id_usuario WHERE fact_cod_fact = $fact_cod_fact ;";
                
                                            $this->oIfx->QueryT($sql_update);
                                            $this->oIfx->QueryT('COMMIT;');
                
                                            $result_txt='Autorizado:'.$estado->bl_EstadoRegistro;
                                            $result = array(
                                                'div_button' => $div_button,
                                                'result_ws' => $result_txt,
                                                'result_email' => '',
                                            );
                            break;
                            case 500:

                                $detalle_error = $respuesta;
                                
                                $this->oIfx->QueryT('BEGIN;');
                                $error_sri = substr($detalle_error, 0, 255);
                                $sql_update = "UPDATE saefact SET fact_erro_sri = '$error_sri' WHERE fact_cod_fact = $fact_cod_fact";
                                $this->oIfx->QueryT($sql_update);
                                $this->oIfx->QueryT('COMMIT;');

                                throw new Exception($detalle_error);
                            break;
                    }
                } //CIERRE IF CURL ERRNO 
                else {
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

        $nombre = $nombre_documento . ".xml";
        $ruta_xml = $ruta . '/' . $nombre;
        $archivo = fopen($ruta_xml, "w+");
        //fwrite($archivo, utf8_encode($data_xml));
        fwrite($archivo, $data_xml);
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
			fact_fec_emis_aux,
            fact_cod_contr,
            fact_cm7_fac
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
                        if(empty($fact_email_clpv)){
                            $fact_email_clpv='-';
                        }
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

                        $cod_contrato =  $this->oIfx->f('fact_cod_contr');
                        if ($cod_contrato == 0 || empty($cod_contrato)) {
                            $cod_contrato = trim($this->oIfx->f('fact_cm7_fac'));
                        }

                        if (empty($cod_contrato)) {
                            $cod_contrato = 'NULL';
                        }

                        $fact_user_web = $this->oIfx->f('fact_user_web');


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

                 ///MONEDA

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

                $pais_emisor = 'PE';

                                //REGISTRO DE COMPROBANTES
                $cCorEmi = '-';    //Correo emisor
                $cCorAdq = $fact_email_clpv;    // En caso no tenga registrar con gui�n '-'
                $cNumDoce = $this->empr_ruc_empr;    //RUC emisor
                $cTipDoce = '6';    //Tipo documento emisor
                $cCdgTipc = '08'; //Tipo comprobante: Factura = '01' | Boleta = '03' | Nota de cr�dito = '07'
                $cRazSoce = $this->empr_nom_empr;    //Rason social emisor
                $cNomCom = $this->empr_nom_empr;    //Nombre comercial emisor
                $cSerNum = $fact_nse_fact . '-' . $fact_num_preimp;     //Serie y N�mero de comprobante 
                $cFecEmi = $fact_fech_fact;        //Fecha emisi�n comprobante: AAAA-MM-DD
                $cUbiEmi = $this->empr_cpo_empr;        //Ubigeo emisor
                $cDirEmi = $this->empr_dir_empr;     //Ubicaci�n emisor
                $cUrbEmi = '-';     //Urbanizaci�n emisor
                $cPrvEmi = $this->ciud_nom_ciud;      //Provincia emisor
                $cDptEmi = $this->prov_des_prov;    //Departamento emisor
                $cDstEmi = $this->parr_des_parr;       // Distrito emisor
                $cPaiEmi = $pais_emisor;        // Pais emisor
                $cNumDoca = $fact_ruc_clie;    //N�mero documento adquiriente 
                $cTipDoca = $tipo_docu;    //Tipo documento adquiriente (1=Documento Nacional de Identidad (DNI) | 4=Carnet de extranjer�a | 6 = Registro Unico de Contributentes (RUC) | 7 = Pasaporte | M�s en catalogo 6)
                $cRazSoca = $fact_nom_cliente;    //
                $cTipMon = $mone_sgl_mone;    //Moneda de comprobante: PEN=Soles | USD=Dolares americanos | Mas en catalogo 2
                $cTotValv = $fact_sin_miva;    //Valor de venta
                $cTotIgv = $this->empr_iva_empr;    //Importe IGV
                $cTotImp = $fact_iva;    //Total impuestos
                $cTotIsc = '0.00';    //ISC
                $cTotVta =  $fact_tot_fact;    //Total venta
                $cSwtCont = '0';    // Comprobante de contingencia: 0=No | 1=Si
                $cTotValv = $fact_sin_miva;    //Total valor venta
                $cTotPrec = $fact_tot_fact;    //Total precio de venta
                $cCdgLey1 = '1000';    //Leyenda 1: 1000, N�mero en letras (Impresi�n)

                /*if ($mone_sgl_mone == 'PEN') {
                    // Si moneda es en SOLES
                    $cTexLey1 = 'SON: ' . $numero_a_texto . ' SOLES';
                } else {
                    // Si moneda es en DOLARES
                    $cTexLey1 = 'SON: ' . $numero_a_texto . ' DOLARES AMERICANOS';
                }*/
                $cTexLey1 = 'SON: ' . $con_letra;

                $cCdgA401 = '9011';    //Etiqueta porcentaje IGV en el PDF'
                $cTexA401 = $this->empr_iva_empr . '.00 %';    //Porcentaje IGV aplicado e impreso en comprobante (Impresi�n)

                $cTipOpe = '1001';        //Tipo de operacion: Venta Interna, ver catalogo 14
                $cHorEmi = date('H:i:s');    //Hora emisi�n: hh:mm:ss
                $cCdgEstb = '';    //C�digo local anexo emisor (Establecimientos)

                //LOCAL			@cCdgEstb	DIRECCON
                //OF. MANYLSA	0012        COOP. MANYLSA LTDA 476 CAL.16 MZ M  L 01, ATE
                //OF. VITARTE	0006        AV. CENTRAL 632, ATE
                //OF. HUAYCAN	0001        AV. JOSE CARLOS MARIATEGUI, UCV 9 L58, HUAYCAN, ATE
                //OF. CHOSICA	0007        JR. TACNA N� 219 INT 2, LURIGANCHO
                //OF. PURUCHUCO	0014        AV. NICOLAS AYLLON 4770 INT 122A, ATE


                if ($tipo == 'EFE') {
                    $tipo_pago = 'EFECTIVO';
                } else if ($tipo == 'CRE') {
                    $tipo_pago = 'CREDITO';
                } else {
                    $tipo_pago = 'CONTADO';
                }

                $cEstReg = 'N';     //Estado de registro: N=Nuevo | A:Agregado | L:Le�do
                $cCdgAux100_1 = null;     //C�digo auxiliar 100_1: C�digo de abonado en sistema 
                $cTxtAux100_1 = null;       //Texto auxiliar 100_1: Valor C�digo de abonado en sistema (Impresi�n)
                $cCdgAux100_2 = '';     //C�digo auxiliar 100_2: C�digo de cajero en sistema 
                $cTxtAux100_2 = '';     //Texto auxiliar 100_2: Valor C�digo de cajero en sistema (Impresi�n)
                $cCdgAux100_3 = null;     //C�digo auxiliar 100_3: Nombre de cajero en sistema 
                $cTxtAux100_3 = null;        //Texto auxiliar 100_3: Valor Nombre de cajero en sistema (Impresi�n)
                $cCdgAux100_4 = '';         //C�digo auxiliar 100_4: 8265 = C�digo Importe devuelto
                $cTxtAux100_4 = '';            //Texto auxiliar 100_4: Valor Importe devuelto (Impresi�n)
                $cCdgAux100_5 = '';            //C�digo auxiliar 100_5: 8266 = C�digo Importe entregado
                $cTxtAux100_5 = '';            //Texto auxiliar 100_5: Valor Importe entregado (Impresi�n)
                $cCdgAux100_6 = '9015';    //C�digo auxiliar 100_6: C�digo Forma de pago
                $cTxtAux100_6 = $tipo_pago;        //Texto auxiliar 100_6: Valor Forma de pago (Impresi�n) CHEQUE | DEPOSITO | EFECTIVO | TRANSFERENCIA | TARJETA | Sin valor: '-')
                $cCdgAux100_7 = '8561';        //C�digo auxiliar 100_7: C�digo Direcci�n establecimiento
                $cTxtAux100_7 = $this->empr_dir_empr;         //Texto auxiliar 100_7: Valor Direcci�n establecimiento (Impresi�n)

                //NOMBRE		@cCdgEstb	DIRECCON
                //OF. MANYLSA	0012        COOP. MANYLSA LTDA 476 CAL.16 MZ M  L 01, ATE
                //OF. VITARTE	0006        AV. CENTRAL 632, ATE
                //OF. HUAYCAN	0001        AV. JOSE CARLOS MARIATEGUI, UCV 9 L58, HUAYCAN, ATE
                //OF. CHOSICA	0007        JR. TACNA N� 219 INT 2, LURIGANCHO
                //OF. PURUCHUCO	0014        AV. NICOLAS AYLLON 4770 INT 122A, ATE 

                // Si el importe supera los S/700
                $cCdgAux100_8 = '';    //C�digo auxiliar 100_8: 9045 = Cuenta detracci�n
                $cTxtAux100_8 = '';    //Texto auxiliar 100_8: Valor Cuenta detracci�n (Impresi�n): Obligaci�n sujeta al sistema de detracciones SPOT N� de Cuenta Bco. de la Naci�n: 00-066-025551.
                // Si el importe es menor a los S/700
                $cCdgAux100_8 = '';    //C�digo auxiliar 100_8: 9045 = Cuenta detracci�n
                $cTxtAux100_8 = '';    //Texto auxiliar 100_8: Valor Cuenta detracci�n (Impresi�n): ''
                //

                $cCdgAux250_1 = '';    //C�digo auxiliar 250_1: 9618 = C�digo Leyenda fideicomiso
                $cTxtAux250_1 = '';    //Texto auxiliar 250_1: Valor Leyenda fideicomiso (Impresi�n): 'Los importes contenidos en el presente comprobante de pago han sido cedidos a un patrimonio fideicometido administrado por La Fiduciaria S.A.'

                //------------------------------------------------------------------------
                // SOLO PARA REGISTRO DE NOTAS DE CREDITO DE LO CONTRARIO DEJAR EN BLANCO
                //------------------------------------------------------------------------

                $cCdgTipn = null;        //C�digo tipo nota de credito. Ver catalogo 9
                $cDesTipn = null;            //Descripci�n de tipo nota de credito. 
                // COD	DESCRIP1CION
                // 01	Anulaci�n de la operaci�n	
                // 09	Disminuci�n en el valor	
                // 10	Otros Conceptos 	
                $cRefCdgt = $tipo_envio;        //Referencia NC, c�digo tipo de documento afectado. 01=FACTURA | 03=BOLETA
                $cRefSern = $numDocfectado;            //Referencia NC, serie y n�mero de documento afectado
                // Si @cCdgTipn = '10' OTROS CONCEPTOS, de lo contrario dejar en blanco
                $cRefTipa = null;            //Referencia NC, referencia adicional
                $cRefNuma = null;        //Referencia NC, n�mero documento referencia adicional

                //------------------------------------------------------------------------
                // REGISTRO DE DATOS DE CABECERA PARA COMPROBANTES
                //------------------------------------------------------------------------


                ///VALIDAR PROCESO DE DETRACCIONES

                $data['cabecera'] = array(
                    "correoEmisor" => $cCorEmi,
                    "correoAdquiriente" => $cCorAdq,
                    "numeroDocumentoEmisor" => $cNumDoce,
                    "tipoDocumentoEmisor" => $cTipDoce,
                    "tipoDocumento" => $cCdgTipc,
                    "razonSocialEmisor" => $cRazSoce,
                    "nombreComercialEmisor" => $cNomCom,
                    "serieNumero" => $cSerNum,
                    "fechaEmision" => $cFecEmi,
                    "ubigeoEmisor" => $cUbiEmi,
                    "direccionEmisor" => $cDirEmi,
                    "urbanizacion" => $cUrbEmi,
                    "provinciaEmisor" => $cPrvEmi,
                    "departamentoEmisor" => $cDptEmi,
                    "distritoEmisor" => $cDstEmi,
                    "paisEmisor" => $cPaiEmi,
                    "numeroDocumentoAdquiriente" => $cNumDoca,
                    "tipoDocumentoAdquiriente" => $cTipDoca,
                    "razonSocialAdquiriente" => $cRazSoca,
                    "tipoMoneda" => $cTipMon,
                    "totalValorVentaNetoOpGravadas" => $cTotValv,
                    "totalIgv" => $cTotIgv,
                    "totalImpuestos" => $cTotImp,
                    "totalIsc" => $cTotIsc,
                    "totalVenta" => $cTotVta,
                    "contingencia" => $cSwtCont,
                    "totalValorVenta" => $cTotValv,
                    "totalPrecioVenta" => $cTotVta,
                    "codigoLeyenda_1" => $cCdgLey1,
                    "textoLeyenda_1" => $cTexLey1,
                    "codigoAuxiliar40_1" => $cCdgA401,
                    "textoAuxiliar40_1" => $cTexA401,
                    "tipoOperacion" => $cTipOpe,
                    "horaEmision" => $cHorEmi,
                    "codigoLocalAnexoEmisor" => $cCdgEstb,
                    "bl_EstadoRegistro" => $cEstReg,
                    "codigoAuxiliar100_1" => $cCdgAux100_1,
                    "textoAuxiliar100_1" => $cTxtAux100_1,
                    "codigoAuxiliar100_2" => $cCdgAux100_2,
                    "textoAuxiliar100_2" => $cTxtAux100_2,
                    "codigoAuxiliar100_3" => $cCdgAux100_3,
                    "textoAuxiliar100_3" => $cTxtAux100_3,
                    "codigoAuxiliar100_4" => $cCdgAux100_4,
                    "textoAuxiliar100_4" => $cTxtAux100_4,
                    "codigoAuxiliar100_5" => $cCdgAux100_5,
                    "textoAuxiliar100_5" => $cTxtAux100_5,
                    "codigoAuxiliar100_6" => $cCdgAux100_6,
                    "textoAuxiliar100_6" => $cTxtAux100_6,
                    "codigoAuxiliar100_7" => $cCdgAux100_7,
                    "textoAuxiliar100_7" => $cTxtAux100_7,
                    "codigoAuxiliar100_8" => $cCdgAux100_8,
                    "textoAuxiliar100_8" => $cTxtAux100_8,
                    "codigoAuxiliar250_1" => $cCdgAux250_1,
                    "textoAuxiliar250_1" => $cTxtAux250_1,
                    "codigoSerieNumeroAfectado" => $cCdgTipn,
                    "motivoDocumento" => $cDesTipn,
                    "tipoDocumentoReferenciaPrincip" => $cRefCdgt,
                    "numeroDocumentoReferenciaPrinc" => $cRefSern,
                    "tipoReferenciaAdicional_1" => $cRefTipa,
                    "numeroDocumentoRefeAdicional_1" => $cRefNuma,
                    "codigo_jireh" =>$fact_cod_fact
                );

                /*$data = array(
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
                );*/

                $sql = "SELECT dfac_cant_dfac, dfac_cod_prod, dfac_det_dfac, dfac_mont_total, dfac_por_iva, dfac_precio_dfac, dfac_exc_iva  FROM saedfac WHERE dfac_cod_fact = $fact_cod_fact";
                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 0;
                        do {
                            $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                            $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                            $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                            $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                            $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                            $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');
                            $dfac_exc_iva = $this->oIfx->f('dfac_exc_iva');
                            $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                            $precio_uni = $dfac_precio_dfac * $porcentaje_iva;
                            $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;

                            if (empty($dfac_det_dfac)) {
                                $dfac_det_dfac = 'Sin detalle';
                            }

                            if ($j < 10) {
                                $num_item = '0' . $j;
                            }


                            $tipAfeIgv = "01";
                            $tipAfeIgvex = "10";
                            if ($dfac_exc_iva == 1) {
                                $tipAfeIgv = "02";
                                $tipAfeIgvex = "20";
                                //$data['mtoImpVenta'] = floatval($dfac_por_iva);
                                // $data['mtoOperExoneradas'] = floatval($fact_tot_fact);
                                //$data['mtoOperGravadas'] = floatval($dfac_por_iva);
                            }

                            /*$data['details'][$j++] =
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
                                ];*/


                                $data['detalle'][$j++] =
                                [
                                    "numeroDocumentoEmisor" => $cNumDoce,
                                    "tipoDocumentoEmisor" => $cTipDoce,
                                    "tipoDocumento" => $cCdgTipc,
                                    "serieNumero" => $cSerNum,
                                    "numeroOrdenItem" => $num_item,
                                    "codigoProducto" => $dfac_cod_prod,
                                    "descripcion" => $dfac_det_dfac,
                                    "cantidad" => floatval($dfac_cant_dfac),
                                    "unidadMedida" => "NIU",
                                    "importeTotalSinImpuesto" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "importeUnitarioSinImpuesto" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                    "importeUnitarioConImpuesto" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "codigoImporteUnitarioConImpues" => $tipAfeIgv,
                                    "montoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "tasaIGV" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                    "importeIgv" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "importeTotalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "codigoRazonExoneracion" => $tipAfeIgvex,
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
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url.'/factura');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);


                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    //$data_json = json_decode($respuesta, true);
                    switch ($http_code) {
                        case 200:

                                try {

                                   // $this->oIfx->QueryT('BEGIN;');
                                   $result_txt=$respuesta;
                                   $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_envio . '-' . $fact_nse_fact . '-' . $fact_num_preimp;
                                   //$this->GenerarNotaDebitoXmlPdfPeru($headers, $nombre_documento, $data);

                                   $ruta_xml = 'upload/xml/deb_' . $nombre_documento . '.xml';
                                   $ruta_pdf = 'upload/pdf/deb_' . $nombre_documento . '.pdf';

                                   /*<a href="upload/xml/deb_' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                           <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                       </a>*/

                                   $div_button = '<div class="btn-group" role="group" aria-label="...">
                                       
                                       <a href="upload/pdf/deb_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                           <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                       </a>
                                   </div>';


                                   //$sql_update = "UPDATE saefact SET fact_aprob_sri = 'S' WHERE fact_cod_fact = $fact_cod_fact;";
                                   //$this->oIfx->QueryT($sql_update);
                                   //$this->oIfx->QueryT('COMMIT;');

                                
                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => $result_txt,
                                        'result_email' => '',
                                    );


                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }
                         
                            break;
                            case 500:

                                $detalle_error = $respuesta;
                                
                                $this->oIfx->QueryT('BEGIN;');
                                $error_sri = substr($detalle_error, 0, 255);
                                $sql_update = "UPDATE saefact SET fact_erro_sri = '$error_sri' WHERE fact_cod_fact = $fact_cod_fact";
                                $this->oIfx->QueryT($sql_update);
                                $this->oIfx->QueryT('COMMIT;');

                                throw new Exception($detalle_error);
                            break;
                        default:
                            throw new Exception("Error desconocido en el WebService, Consulte con el administrador");
                    }
                }
                else {
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

        $nombre = "deb_" . $nombre_documento . ".xml";
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

            //CONTROL FORMATOS PERSONALIZADOS
            $sql = "select ftrn_ubi_web from saeftrn  where ftrn_cod_empr=$idempresa and ftrn_des_ftrn = 'FACTURA' and ftrn_cod_modu=7 and (ftrn_ubi_web is not null or ftrn_ubi_web != '')";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    $ubi =  $this->oIfx->f('ftrn_ubi_web');
                    if (!empty($ubi)) {
                        include_once('../../' . $ubi . '');
                        $ctrl_formato++;
                    } else {
                        //FORMATO ESTANDAR PERU
                        include_once('../../Include/Formatos/comercial/factura_peru.php');
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
            fac.ncre_cm1_ncre as motivo_ncre,
	        fac.ncre_fech_fact as fact_hor_fin,
	        fac.ncre_cod_sucu as fact_cod_sucu,
            fac.ncre_user_web as fact_user_web,
	        cli.clv_con_clpv,
	        fac.ncre_aprob_sri as fact_aprob_sri,
	        fac.ncre_cod_fact as fact_cod_ndb, 
			fac.ncre_cod_aux as fact_aux_preimp,
			fac.ncre_fech_fact as fact_fec_emis_aux,
            fac.ncre_cod_mone as fact_cod_mone,
            fac.ncre_val_tcam as fact_val_tcam,
            fac.ncre_cod_contr as fact_cod_contr
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
                        if(empty($fact_email_clpv)||$fact_email_clpv=='undefined'){
                            $fact_email_clpv='';
                        }
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

                        $fact_user_web = $this->oIfx->f('fact_user_web');

                        $cod_contrato = $this->oIfx->f('fact_cod_contr');
                        if (empty($cod_contrato)) {
                            $cod_contrato = 'NULL';
                        }

                        $fact_nse_fact = substr($fact_nse_fact, 3, 9);


                        $sql_sucu = "SELECT usuario_nombre, usuario_apellido, usuario_user from comercial.usuario where usuario_id = $fact_user_web";
                        if ($this->oCon->Query($sql_sucu)) {
                            if ($this->oCon->NumFilas() > 0) {
                                do {
                                    $usuario_nombre = $this->oCon->f('usuario_nombre');
                                    $usuario_apellido = $this->oCon->f('usuario_apellido');
                                    $cajero = $usuario_apellido . ' ' . $usuario_nombre;
                                } while ($this->oCon->SiguienteRegistro());
                            }
                        }
                        $this->oCon->Free();



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


            ///CODIGO DE SUCURSAL->VALIDAR CONFIGURACION

            $sql = "SELECT sucu_alias_sucu from saesucu where sucu_cod_sucu = $fact_cod_sucu";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $sucu_alias_sucu = $this->oIfx->f('sucu_alias_sucu');
                       
                    } while ($this->oIfx->SiguienteRegistro());
                }
            }
            $this->oIfx->Free();

            /** CONTROL PARA FACTURA YA ENVIADA */
            if ($fact_aprob_sri == 'N') {
                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, $mone_des_mone));

                //$fact_fech_fact = $fact_fech_fact . "T"  . "12:00:00-00:00";
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
                                $fact_num_preimp_afect = $this->oIfx->f('fact_num_preimp');
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
                //CREAR CAMPO PARA MOTIVOS DE DEVOLUCION
                
                $sql = "SELECT ddev_tip_ddev, ddev_des_ddev from saeddev where ddev_des_ddev = '$motivo_ncre'";
                $motivo_envio = consulta_string($sql, 'ddev_tip_ddev', $this->oIfx, '');
                $motivo_envio_nombre = consulta_string($sql, 'ddev_des_ddev', $this->oIfx, '');

                /*$data = array(
                    "ublVersion" => "2.1",
                    "tipoDoc" => $tipo_doc,
                    "serie" => $fact_nse_fact,
                    "correlativo" => (int) $fact_num_preimp,
                    "fechaEmision" => $fact_fech_fact,

                    "tipDocAfectado" => $tipo_envio,
                    "numDocfectado" => $numDocfectado,
                    "codMotivo" => strval($motivo_envio),
                    "desMotivo" => $motivo_envio_nombre,
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
                );*/

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

                                /*if ($tipo == 'EFE') {
                                    $tipo = 'Contado';
                                } else if ($tipo == 'CRE') {
                                    $tipo = 'Credito';

                                    $fxfp_fec_fin = $fact_fech_venc;

                                    /*$data['cuotas'][$j++] = [
                                        "monto" => floatval(number_format($fxfp_val_fxfp, 2, '.', '')),
                                        "fechaPago" => $fxfp_fec_fin
                                    ];*/
                                /*} else {
                                    $tipo = 'Contado';
                                }*/

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



                //REGISTRO DE COMPROBANTES
                $cCorEmi = '-';    //Correo emisor
                $cCorAdq = $fact_email_clpv;    // En caso no tenga registrar con gui�n '-'
                $cNumDoce = $this->empr_ruc_empr;    //RUC emisor
                $cTipDoce = '6';    //Tipo documento emisor
                $cCdgTipc = '07'; //Tipo comprobante: Factura = '01' | Boleta = '03' | Nota de cr�dito = '07'
                $cRazSoce = $this->empr_nom_empr;    //Rason social emisor
                $cNomCom = $this->empr_nom_empr;    //Nombre comercial emisor
                $cSerNum = $fact_nse_fact . '-' . $fact_num_preimp;     //Serie y N�mero de comprobante 
                $cFecEmi = $fact_fech_fact;        //Fecha emisi�n comprobante: AAAA-MM-DD
                $cUbiEmi = $this->empr_cpo_empr;        //Ubigeo emisor
                $cDirEmi = $this->empr_dir_empr;     //Ubicaci�n emisor
                $cUrbEmi = '-';     //Urbanizaci�n emisor
                $cPrvEmi = $this->ciud_nom_ciud;      //Provincia emisor
                $cDptEmi = $this->prov_des_prov;    //Departamento emisor
                $cDstEmi = $this->parr_des_parr;       // Distrito emisor
                $cPaiEmi = $pais_emisor;        // Pais emisor
                $cNumDoca = $fact_ruc_clie;    //N�mero documento adquiriente 
                $cTipDoca = $tipo_docu;    //Tipo documento adquiriente (1=Documento Nacional de Identidad (DNI) | 4=Carnet de extranjer�a | 6 = Registro Unico de Contributentes (RUC) | 7 = Pasaporte | M�s en catalogo 6)
                $cRazSoca = $fact_nom_cliente;    //
                $cTipMon = $mone_sgl_mone;    //Moneda de comprobante: PEN=Soles | USD=Dolares americanos | Mas en catalogo 2
                $cTotValv = $fact_sin_miva;    //Valor de venta
                $cTotIgv = $this->empr_iva_empr;    //Importe IGV
                $cTotImp = $fact_iva;    //Total impuestos
                $cTotIsc = '0.00';    //ISC
                $cTotVta =  $fact_tot_fact;    //Total venta
                $cSwtCont = '0';    // Comprobante de contingencia: 0=No | 1=Si
                $cTotValv = $fact_sin_miva;    //Total valor venta
                $cTotPrec = $fact_tot_fact;    //Total precio de venta
                $cCdgLey1 = '1000';    //Leyenda 1: 1000, N�mero en letras (Impresi�n)

                /*if ($mone_sgl_mone == 'PEN') {
                    // Si moneda es en SOLES
                    $cTexLey1 = 'SON: ' . $numero_a_texto . ' SOLES';
                } else {
                    // Si moneda es en DOLARES
                    $cTexLey1 = 'SON: ' . $numero_a_texto . ' DOLARES AMERICANOS';
                }*/
                $cTexLey1 = 'SON: ' . $con_letra;

                $cCdgA401 = '9011';    //Etiqueta porcentaje IGV en el PDF'
                $cTexA401 = $this->empr_iva_empr . '.00 %';    //Porcentaje IGV aplicado e impreso en comprobante (Impresi�n)

                $cTipOpe = '1001';        //Tipo de operacion: Venta Interna, ver catalogo 14
                $cHorEmi = date('H:i:s');    //Hora emisi�n: hh:mm:ss

                  ///VALIDAR  CAMPO DE LA SUCURSAL
                  $cCdgEstb = $sucu_alias_sucu;    //C�digo local anexo emisor (Establecimientos) Código asignado por SUNAT para el establecimiento anexo declarado en el RUC.


                //LOCAL			@cCdgEstb	DIRECCON
                //OF. MANYLSA	0012        COOP. MANYLSA LTDA 476 CAL.16 MZ M  L 01, ATE
                //OF. VITARTE	0006        AV. CENTRAL 632, ATE
                //OF. HUAYCAN	0001        AV. JOSE CARLOS MARIATEGUI, UCV 9 L58, HUAYCAN, ATE
                //OF. CHOSICA	0007        JR. TACNA N� 219 INT 2, LURIGANCHO
                //OF. PURUCHUCO	0014        AV. NICOLAS AYLLON 4770 INT 122A, ATE


                if ($tipo == 'EFE') {
                    $tipo_pago = 'EFECTIVO';
                } else if ($tipo == 'CRE') {
                    $tipo_pago = 'CREDITO';
                } else {
                    $tipo_pago = 'CONTADO';
                }

                $cEstReg = 'N';     //Estado de registro: N=Nuevo | A:Agregado | L:Le�do
                $cCdgAux100_1 = null;     //C�digo auxiliar 100_1: C�digo de abonado en sistema 
                $cTxtAux100_1 = null;       //Texto auxiliar 100_1: Valor C�digo de abonado en sistema (Impresi�n)
                $cCdgAux100_2 = '';     //C�digo auxiliar 100_2: C�digo de cajero en sistema 
                $cTxtAux100_2 = '';     //Texto auxiliar 100_2: Valor C�digo de cajero en sistema (Impresi�n)
                $cCdgAux100_3 = '8267';     //C�digo auxiliar 100_3: Nombre de cajero en sistema 
                $cTxtAux100_3 = $cajero;        //Texto auxiliar 100_3: Valor Nombre de cajero en sistema (Impresi�n)
                $cCdgAux100_4 = '';         //C�digo auxiliar 100_4: 8265 = C�digo Importe devuelto
                $cTxtAux100_4 = '';            //Texto auxiliar 100_4: Valor Importe devuelto (Impresi�n)
                $cCdgAux100_5 = '';            //C�digo auxiliar 100_5: 8266 = C�digo Importe entregado
                $cTxtAux100_5 = '';            //Texto auxiliar 100_5: Valor Importe entregado (Impresi�n)
                $cCdgAux100_6 = '9015';    //C�digo auxiliar 100_6: C�digo Forma de pago
                $cTxtAux100_6 = $tipo_pago;        //Texto auxiliar 100_6: Valor Forma de pago (Impresi�n) CHEQUE | DEPOSITO | EFECTIVO | TRANSFERENCIA | TARJETA | Sin valor: '-')
                $cCdgAux100_7 = '8561';        //C�digo auxiliar 100_7: C�digo Direcci�n establecimiento
                $cTxtAux100_7 = $this->empr_dir_empr;         //Texto auxiliar 100_7: Valor Direcci�n establecimiento (Impresi�n)

                //NOMBRE		@cCdgEstb	DIRECCON
                //OF. MANYLSA	0012        COOP. MANYLSA LTDA 476 CAL.16 MZ M  L 01, ATE
                //OF. VITARTE	0006        AV. CENTRAL 632, ATE
                //OF. HUAYCAN	0001        AV. JOSE CARLOS MARIATEGUI, UCV 9 L58, HUAYCAN, ATE
                //OF. CHOSICA	0007        JR. TACNA N� 219 INT 2, LURIGANCHO
                //OF. PURUCHUCO	0014        AV. NICOLAS AYLLON 4770 INT 122A, ATE 

                // Si el importe supera los S/700
                $cCdgAux100_8 = '';    //C�digo auxiliar 100_8: 9045 = Cuenta detracci�n
                $cTxtAux100_8 = '';    //Texto auxiliar 100_8: Valor Cuenta detracci�n (Impresi�n): Obligaci�n sujeta al sistema de detracciones SPOT N� de Cuenta Bco. de la Naci�n: 00-066-025551.
                // Si el importe es menor a los S/700
                $cCdgAux100_8 = '';    //C�digo auxiliar 100_8: 9045 = Cuenta detracci�n
                $cTxtAux100_8 = '';    //Texto auxiliar 100_8: Valor Cuenta detracci�n (Impresi�n): ''
                //

                $cCdgAux250_1 = '';    //C�digo auxiliar 250_1: 9618 = C�digo Leyenda fideicomiso
                $cTxtAux250_1 = '';    //Texto auxiliar 250_1: Valor Leyenda fideicomiso (Impresi�n): 'Los importes contenidos en el presente comprobante de pago han sido cedidos a un patrimonio fideicometido administrado por La Fiduciaria S.A.'

                //------------------------------------------------------------------------
                // SOLO PARA REGISTRO DE NOTAS DE CREDITO DE LO CONTRARIO DEJAR EN BLANCO
                //------------------------------------------------------------------------

                $cCdgTipn = $motivo_envio;        //C�digo tipo nota de credito. Ver catalogo 9
                $cDesTipn = $motivo_envio_nombre;            //Descripci�n de tipo nota de credito. 
                // COD	DESCRIPCION
                // 01	Anulaci�n de la operaci�n	
                // 09	Disminuci�n en el valor	
                // 10	Otros Conceptos 	
                $cRefCdgt = $tipo_envio;        //Referencia NC, c�digo tipo de documento afectado. 01=FACTURA | 03=BOLETA
                $cRefSern = $numDocfectado;            //Referencia NC, serie y n�mero de documento afectado
                // Si @cCdgTipn = '10' OTROS CONCEPTOS, de lo contrario dejar en blanco
                $cRefTipa = null;            //Referencia NC, referencia adicional
                $cRefNuma = null;        //Referencia NC, n�mero documento referencia adicional

                //------------------------------------------------------------------------
                // REGISTRO DE DATOS DE CABECERA PARA COMPROBANTES
                //------------------------------------------------------------------------


                ///VALIDAR PROCESO DE DETRACCIONES

                //COMENTAR 
                //$cSerNum='NM04-00000010';

                $data['cabecera'] = array(
                    "correoEmisor" => $cCorEmi,
                    "correoAdquiriente" => $cCorAdq,
                    "numeroDocumentoEmisor" => $cNumDoce,
                    "tipoDocumentoEmisor" => $cTipDoce,
                    "tipoDocumento" => $cCdgTipc,
                    "razonSocialEmisor" => $cRazSoce,
                    "nombreComercialEmisor" => $cNomCom,
                    "serieNumero" => $cSerNum,
                    "fechaEmision" => $cFecEmi,
                    "ubigeoEmisor" => $cUbiEmi,
                    "direccionEmisor" => $cDirEmi,
                    "urbanizacion" => $cUrbEmi,
                    "provinciaEmisor" => $cPrvEmi,
                    "departamentoEmisor" => $cDptEmi,
                    "distritoEmisor" => $cDstEmi,
                    "paisEmisor" => $cPaiEmi,
                    "numeroDocumentoAdquiriente" => $cNumDoca,
                    "tipoDocumentoAdquiriente" => $cTipDoca,
                    "razonSocialAdquiriente" => $cRazSoca,
                    "tipoMoneda" => $cTipMon,
                    "totalValorVentaNetoOpGravadas" => $cTotValv,
                    "totalIgv" => $cTotIgv,
                    "totalImpuestos" => $cTotImp,
                    "totalIsc" => $cTotIsc,
                    "totalVenta" => $cTotVta,
                    "contingencia" => $cSwtCont,
                    "totalValorVenta" => $cTotValv,
                    "totalPrecioVenta" => $cTotVta,
                    "codigoLeyenda_1" => $cCdgLey1,
                    "textoLeyenda_1" => $cTexLey1,
                    "codigoAuxiliar40_1" => $cCdgA401,
                    "textoAuxiliar40_1" => $cTexA401,
                    "tipoOperacion" => $cTipOpe,
                    "horaEmision" => $cHorEmi,
                    "codigoLocalAnexoEmisor" => $cCdgEstb,
                    "bl_EstadoRegistro" => $cEstReg,
                    "codigoAuxiliar100_1" => $cCdgAux100_1,
                    "textoAuxiliar100_1" => $cTxtAux100_1,
                    "codigoAuxiliar100_2" => $cCdgAux100_2,
                    "textoAuxiliar100_2" => $cTxtAux100_2,
                    "codigoAuxiliar100_3" => $cCdgAux100_3,
                    "textoAuxiliar100_3" => $cTxtAux100_3,
                    "codigoAuxiliar100_4" => $cCdgAux100_4,
                    "textoAuxiliar100_4" => $cTxtAux100_4,
                    "codigoAuxiliar100_5" => $cCdgAux100_5,
                    "textoAuxiliar100_5" => $cTxtAux100_5,
                    "codigoAuxiliar100_6" => $cCdgAux100_6,
                    "textoAuxiliar100_6" => $cTxtAux100_6,
                    "codigoAuxiliar100_7" => $cCdgAux100_7,
                    "textoAuxiliar100_7" => $cTxtAux100_7,
                    "codigoAuxiliar100_8" => $cCdgAux100_8,
                    "textoAuxiliar100_8" => $cTxtAux100_8,
                    "codigoAuxiliar250_1" => $cCdgAux250_1,
                    "textoAuxiliar250_1" => $cTxtAux250_1,
                    "codigoSerieNumeroAfectado" => $cCdgTipn,
                    "motivoDocumento" => $cDesTipn,
                    "tipoDocumentoReferenciaPrincip" => $cRefCdgt,
                    "numeroDocumentoReferenciaPrinc" => $cRefSern,
                    "tipoReferenciaAdicional_1" => $cRefTipa,
                    "numeroDocumentoRefeAdicional_1" => $cRefNuma,
                    "codigo_jireh" =>$ncre_cod_ncre
                );

//VALIDAR DE DONDE TOMAR LA INFORMACION

                $sqlDire = "SELECT id_provincia, id_canton,     id_ciudad,    id_parroquia, id_sector,     id_barrio,      
                           id_bloque,    nomb_conjunto, num_conjunto, estrato,      id_conjunto,   departamento,  poste,
                           caja,         id_ruta,      ruta,      orden_ruta,       direccion,     referencia,    latitud, 
                           longitud,     id_calle
                    from isp.contrato_clpv
                    where id = $cod_contrato";
                if ($this->oIfx->Query($sqlDire)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $id_provincia   = $this->oIfx->f('id_provincia');
                        $id_canton      = $this->oIfx->f('id_canton');
                        $id_ciudad      = $this->oIfx->f('id_ciudad');
                        $id_parroquia   = $this->oIfx->f('id_parroquia');
                        $id_sector      = $this->oIfx->f('id_sector');
                        $id_barrio      = $this->oIfx->f('id_barrio');
                        $id_bloque      = $this->oIfx->f('id_bloque');
                        $nomb_conjunto  = $this->oIfx->f('nomb_conjunto');
                        $num_conjunto   = $this->oIfx->f('num_conjunto');
                        $estrato        = $this->oIfx->f('estrato');
                        $id_conjunto    = $this->oIfx->f('id_conjunto');
                        $direccion      =substr(trim($this->oIfx->f('direccion')),0,100);


                        if (!empty($id_provincia)) {
                            $sql = "SELECT prov_des_prov from saeprov where prov_cod_prov = $id_provincia ";

                            if ($this->oCon->Query($sql)) {
                                if ($this->oCon->NumFilas() > 0) {
                                    $departamento     = $this->oCon->f('prov_des_prov');
                                }
                            }
                            $this->oCon->Free();
                        }

                        if (!empty($id_ciudad)) {
                            $sql = "SELECT ciud_cod_ciud, ciud_nom_ciud from saeciud where ciud_cod_ciud = $id_ciudad ";
                            if ($this->oCon->Query($sql)) {
                                if ($this->oCon->NumFilas() > 0) {
                                    $distrito     = substr($this->oCon->f('ciud_nom_ciud'),0,30);
                                }
                            }
                            $this->oCon->Free();
                        }

                        if (!empty($id_canton)) {
                            $sql = "SELECT cant_cod_cant, cant_des_cant from saecant where cant_cod_cant = $id_canton and cant_est_cant = 'A' ";
                            if ($this->oCon->Query($sql)) {
                                if ($this->oCon->NumFilas() > 0) {
                                    $provincia     = $this->oCon->f('cant_des_cant');
                                }
                            }
                            $this->oCon->Free();
                        }

                        if (!empty($id_sector)) {
                            $sql = "SELECT id, sector from comercial.sector_direccion where  id = $id_sector ";

                            if ($this->oCon->Query($sql)) {
                                if ($this->oCon->NumFilas() > 0) {
                                    $urbanizacion     = substr($this->oCon->f('sector'),0,25);
                                }
                            }
                            $this->oCon->Free();
                        }
                    }
                }
                $this->oIfx->Free();

                //INFORMACION DATOS DIREECCION DEL CLIENTE

                $array_anexo = ['direccionAdquiriente', 'ubigeoAdquiriente', 'urbanizacionAdquiriente', 'provinciaAdquiriente', 'departamentoAdquiriente', 'distritoAdquiriente', 'paisAdquiriente', 'formaPagoNegociable'];
                $array_anexo_val = [$direccion, '', $urbanizacion, $provincia, $departamento, $distrito, 'PE', '0'];

                $n = 0;
                foreach ($array_anexo as $val) {

                    if (!empty($array_anexo_val[$n])) {
                        $data['anexo'][$n] = array(
                            "tipoDocumentoEmisor" => $cTipDoce,
                            "numeroDocumentoEmisor" => $cNumDoce,
                            "serieNumero" => $cSerNum,
                            "tipoDocumento" => $cCdgTipc,
                            "clave" => $val,
                            "valor" => $array_anexo_val[$n],
                        );
                       
                    }
                    $n++;
                }


                $sql = "select 
                        dncr_cant_dfac as dfac_cant_dfac,
                        dncr_cod_prod as dfac_cod_prod,
                        dncr_det_dncr as dfac_det_dfac,
                        dncr_mont_total as dfac_mont_total,
                        dncr_por_iva as dfac_por_iva,
                        dncr_exc_iva as dfac_exc_iva,
                        dncr_precio_dfac as dfac_precio_dfac
                        from saedncr
                        where dncr_cod_ncre = $ncre_cod_ncre;";

                if ($this->oIfx->Query($sql)) {
                    if ($this->oIfx->NumFilas() > 0) {
                        $j = 1;
                        do {
                            $dfac_cant_dfac = $this->oIfx->f('dfac_cant_dfac');
                            $dfac_cod_prod = $this->oIfx->f('dfac_cod_prod');
                            $dfac_det_dfac = $this->oIfx->f('dfac_det_dfac');
                            $dfac_mont_total = $this->oIfx->f('dfac_mont_total');
                            $dfac_por_iva = $this->oIfx->f('dfac_por_iva');
                            $dfac_exc_iva = $this->oIfx->f('dfac_exc_iva');
                            $dfac_precio_dfac = $this->oIfx->f('dfac_precio_dfac');

                            if ($this->pcon_seg_mone == $fact_cod_mone) {
                                $dfac_mont_total  = $dfac_mont_total / $fact_val_tcam;
                            }

                            $porcentaje_iva = ($dfac_por_iva / 100) + 1;
                            $precio_uni = $dfac_precio_dfac * $porcentaje_iva;
                            $valor_iva = ($dfac_mont_total * $porcentaje_iva) - $dfac_mont_total;



                            $tipAfeIgv = "01";
                            $tipAfeIgvex = "10";
                            if ($dfac_exc_iva == 1) {
                                $tipAfeIgv = "02";
                                $tipAfeIgvex = "20";
                                //$data['mtoImpVenta'] = floatval($dfac_por_iva);
                                // $data['mtoOperExoneradas'] = floatval($fact_tot_fact);
                                //$data['mtoOperGravadas'] = floatval($dfac_por_iva);
                            }

                            if (empty($dfac_det_dfac)) {
                                $dfac_det_dfac = 'Sin detalle';
                            }

                            if ($j < 10) {
                                $num_item = '0' . $j;
                            }

                            $data['detalle'][$j++] =
                                [
                                    "numeroDocumentoEmisor" => $cNumDoce,
                                    "tipoDocumentoEmisor" => $cTipDoce,
                                    "tipoDocumento" => $cCdgTipc,
                                    "serieNumero" => $cSerNum,
                                    "numeroOrdenItem" => $num_item,
                                    "codigoProducto" => $dfac_cod_prod,
                                    "descripcion" => $dfac_det_dfac,
                                    "cantidad" => floatval($dfac_cant_dfac),
                                    "unidadMedida" => "NIU",
                                    "importeTotalSinImpuesto" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "importeUnitarioSinImpuesto" => floatval(number_format($dfac_precio_dfac, 2, '.', '')),
                                    "importeUnitarioConImpuesto" => floatval(number_format($precio_uni, 2, '.', '')),
                                    "codigoImporteUnitarioConImpues" => $tipAfeIgv,
                                    "montoBaseIgv" => floatval(number_format($dfac_mont_total, 2, '.', '')),
                                    "tasaIGV" => floatval(number_format($dfac_por_iva, 2, '.', '')),
                                    "importeIgv" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "importeTotalImpuestos" => floatval(number_format($valor_iva, 2, '.', '')),
                                    "codigoRazonExoneracion" => $tipAfeIgvex,
                                ];


                            /*$data['details'][$j++] =
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
                                ];*/
                             
                        } while ($this->oIfx->SiguienteRegistro());
                    }
                }
                $this->oIfx->Free();

                //echo json_encode($data);exit;

                /**
                 * EMPIEZA ENVIO
                 */
                $headers = array(
                    "Content-Type:application/json"
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $this->empr_ws_sri_url.'/factura');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);


                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    //$data_json = json_decode($respuesta, true);
                    switch ($http_code) {
                        case 200:

                                try {

                                    $this->oIfx->QueryT('BEGIN;');

                                    $result_txt = $respuesta;
                                    $nombre_documento = $this->empr_ruc_empr . '-' . $tipo_doc . '-' . $fact_nse_fact . '-' . $fact_num_preimp;

                                    $ruta_xml = 'upload/xml/' . $nombre_documento . '.xml';
                                    $ruta_pdf = 'upload/pdf/cred_' . $nombre_documento . '.pdf';


                                    //CREACION FORMATO PERSONALZADO
                                    //$this->GenerarNotaCreditoXmlPdfPeru($headers, $nombre_documento, $data);
                                    //FORMATO PERSONALIZADO
                                    reporte_notaCredito_personalizado($ncre_cod_ncre, $nombre_documento, $fact_cod_sucu, $ruta_pdf);

                                    /* <a href="upload/xml/' . $nombre_documento . '.xml" download="' . $nombre_documento . '.xml">
                                            <button type="button" class="btn btn-primary btn-sm">XML<i class="fa-solid fa-download"></i></button>
                                        </a>*/
                                    $div_button = '<div class="btn-group" role="group" aria-label="...">
                                       
                                        <a href="upload/pdf/cred_' . $nombre_documento . '.pdf" download="' . $nombre_documento . '.pdf">
                                            <button type="button" class="btn btn-danger btn-sm">PDF<i class="fa-solid fa-download"></i></button>
                                        </a>
                                    </div>';

                                    
                                    $sql_update = "UPDATE saencre SET ncre_aprob_sri = 'F', ncre_user_sri = $id_usuario WHERE ncre_cod_ncre = $ncre_cod_ncre;";

                                    $this->oIfx->QueryT($sql_update);
                                    $this->oIfx->QueryT('COMMIT;');

                                
                                    $result = array(
                                        'div_button' => $div_button,
                                        'result_ws' => $result_txt,
                                        'result_email' => '',
                                    );


                                } catch (Exception $e) {
                                    $this->oIfx->QueryT('ROLLBACK;');
                                    throw new Exception($e->getMessage());
                                }
                         
                            break;
                            case 500:

                                $detalle_error = $respuesta;
                                
                                $this->oIfx->QueryT('BEGIN;');
                                $error_sri = substr($detalle_error, 0, 255);
                                $sql_update = "UPDATE saencre SET ncre_erro_sri = '$error_sri' WHERE ncre_cod_ncre = $ncre_cod_ncre";
                                $this->oIfx->QueryT($sql_update);
                                $this->oIfx->QueryT('COMMIT;');

                                throw new Exception($detalle_error);
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

        $nombre = $nombre_documento . ".xml";
        $ruta_xml = $ruta . '/' . $nombre;
        $archivo = fopen($ruta_xml, "w+");
        //fwrite($archivo, utf8_encode($data_xml));
        fwrite($archivo, $data_xml);
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
                $con_letra = strtoupper($V->ValorEnLetrasMonePeru($fact_tot_fact, "SOLES"));

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
                    $exp_fac = explode("-", $fact_aux_preimp);
                    $nse_fac = substr($exp_fac[0], 3, 9);
                    $num_fac = (int) $exp_fac[1];
                    $numDocfectado = "$nse_fac-$num_fac";
                }


                $tipo_doc = "07";

                if (!$fact_cm1_fact) {
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

                print_r($respuesta);
                exit;


                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    print_r($data_json);
                    exit;
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
                                    $correoMsj = envio_correo_adj_sunat('franklin.caiza@sisconti.com', $ruta_xml, $ruta_pdf, $fact_nom_cliente, $nombre_documento, $serie, $fact_cod_clpv,  $tipo_envio, 'GUIA DE REMISION');

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

        $nombre = $nombre_documento . ".xml";
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
            curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/invoice/xml?token=" . $this->empr_token_api);
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
            }

            $sql = "select 
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
            curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_PERU . "/note/xml?token=" . $this->empr_token_api);
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



    function unidad($num)
    {
        switch ($num) {
            case 1:
                return 'uno';
            case 2:
                return 'dos';
            case 3:
                return 'tres';
            case 4:
                return 'cuatro';
            case 5:
                return 'cinco';
            case 6:
                return 'seis';
            case 7:
                return 'siete';
            case 8:
                return 'ocho';
            case 9:
                return 'nueve';
        }
        return '';
    }

    function decena($num)
    {
        if ($num >= 90) {
            return 'noventa ' . $this->unidad($num - 90);
        } elseif ($num >= 80) {
            return 'ochenta ' . $this->unidad($num - 80);
        } elseif ($num >= 70) {
            return 'setenta ' . $this->unidad($num - 70);
        } elseif ($num >= 60) {
            return 'sesenta ' . $this->unidad($num - 60);
        } elseif ($num >= 50) {
            return 'cincuenta ' . $this->unidad($num - 50);
        } elseif ($num >= 40) {
            return 'cuarenta ' . $this->unidad($num - 40);
        } elseif ($num >= 30) {
            return 'treinta ' . $this->unidad($num - 30);
        } elseif ($num >= 20) {
            return 'veinte ' . $this->unidad($num - 20);
        } else {
            return $this->unidad($num);
        }
    }

    function centena($num)
    {
        if ($num >= 100) {
            if ($num % 100 == 0) {
                return $this->unidad($num / 100) . ' cien';
            } else {
                return $this->unidad(floor($num / 100)) . ' ciento ' . $this->decena($num % 100);
            }
        } else {
            return $this->decena($num);
        }
    }

    function convertirATexto($numero)
    {
        $numero = str_replace(',', '', $numero);
        $num = (float)$numero;

        $decimal = '';
        if (strpos($numero, '.') !== false) {
            list($num, $decimal) = explode('.', $numero, 2);
        }

        if ($num < 0) {
            return 'menos ' . $this->convertirATexto(abs($num));
        }

        $parteEntera = '';
        $parteDecimal = '';

        if ($num == 0) {
            $parteEntera = 'cero';
        } elseif ($num < 1) {
            $parteEntera = 'menos ' . $this->centena(abs($num));
        } else {
            $parteEntera = $this->centena($num);
        }

        //if (!empty($decimal)) {
        $decimal = ltrim($decimal, '0');

        if (empty($decimal)) {
            $decimal = '00';
        }

        $parteDecimal = " con $decimal/100";
        //}

        return $parteEntera . $parteDecimal;
    }
}
