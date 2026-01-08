<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class TipoCambioMoneda
{
    private $oIfx;
    private $oCon;

    public $test_url = "https://v6.exchangerate-api.com";
    public $test_token = "45e4d1d589f72a943d9fa99c";//creado 2024-11-06

    public $nombre_integracion = 'exchangerate';
    public $ambiente = 'pruebas';
    public $pais_codigo_inter = '';
    public $idempresa = '';
    public $idusuario = '';


    public $id_integracion = '';
    public $url_api = '';
    public $request_autorizacion = '';
    public $tipo_api = '';
    public $token = '';

    function __construct($oIfx, $oCon, $idempresa = 0,$idusuario = 0)
    {
        $this->oIfx = $oIfx;
        $this->oCon = $oCon;
        $this->idempresa = $idempresa;


        $this->tabla_integraciones();//crea la tabla si no existe
        $this->obtener_integraciones($idempresa,$this->nombre_integracion,$this->ambiente);// setea los parametros de la integracion


        $sql = "SELECT 
                    pais_codigo_inter 
                FROM saeempr 
                INNER JOIN saepais on empr_cod_pais = pais_cod_pais
                and empr_cod_empr = $idempresa";

        if ($this->oCon->Query($sql)) {
            if ($this->oCon->NumFilas() > 0) {
                do {
                    $this->pais_codigo_inter    = $this->oCon->f('pais_codigo_inter');
                } while ($this->oCon->SiguienteRegistro());
            }
        }
        $this->oCon->Free();
    }

    private function obtener_integraciones($empresa_id=0,$nombre_integracion='',$ambiente = ''){
        $sql = "SELECT 
                    ambiente,
                    url_api,                    
                    request_autorizacion,
                    tipo_api,
                    token
                FROM comercial.integraciones
                where empresa_id = '$empresa_id'
                and nombre_integracion = '$nombre_integracion'
                and ambiente = '$ambiente'
                and estado_sn = 'S' limit 1 ;";
        
        $integracion_data = [];
        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {

                $this->id_integracion         = $this->oIfx->f('id')?$this->oIfx->f('id'):0;
                $this->ambiente               = $this->oIfx->f('ambiente')?$this->oIfx->f('ambiente'):0;
                $this->url_api                = $this->oIfx->f('url_api')?$this->oIfx->f('url_api'):0;
                $this->request_autorizacion   = $this->oIfx->f('request_autorizacion')?$this->oIfx->f('request_autorizacion'):0;
                $this->tipo_api               = $this->oIfx->f('tipo_api')?$this->oIfx->f('tipo_api'):0;
                $this->token                  = $this->oIfx->f('token')?$this->oIfx->f('token'):0;

                $integracion_data[$this->id_integracion] = array(
                    "estado_sn"=>$estado_sn,
                    "id_integracion"=>$id_integracion,
                    "ambiente"=>$ambiente,
                    "url_api"=>$url_api,
                    "request_autorizacion"=>$request_autorizacion,
                    "tipo_api"=>$tipo_api,
                    "token"=>$token
                );

            }else{
                $this->registrar_integracion($empresa_id,'S',$nombre_integracion,$ambiente,$this->test_url,'rest','','','','',$this->test_token,'','','');
                return $this->obtener_integraciones($empresa_id,$nombre_integracion,$ambiente);
            }   
        }
        $this->oIfx->Free();
        return $integracion_data;

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

        }
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

        $ambiente = $ambiente?$ambiente:$ambiente_int;


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
                        token                   = '$token',
                        ambiente                = '$ambiente'
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
                token,
                ambiente
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
                '$token',
                '$ambiente'
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

    function request($endpoint,$method,$body){
        $url_full = $this->url_api.''.$endpoint;
        $api_response = json_decode($this->api_request($url_full,$body,'',$method,[]),true);
        $http_code = intval($api_response['http_code']);
        
        if($http_code >=200 && $http_code <300){
            return $api_response;
        }else{
            $api_response = $api_response['data'];
            $result = $api_response['result'];
            $error_type = $api_response['error-type'];
            $extra_info = $api_response['extra-info'];

            $message = $error_type .''.$extra_info;
            throw new Exception($this->nombre_integracion." - $result - message: $message",1);
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

    public function guardar_tip_cambio_ws(){
        //variables de sesion
        $idempresa = $this->idempresa;
        $idusuario = $this->idusuario;
        $fecha_ini = date("Y-m-d");

        try {


            $pcon_mon_base = 0;
            $pcon_seg_mone = 0;
            $sql = "SELECT pcon_seg_mone, pcon_mon_base FROM saepcon WHERE pcon_cod_empr = $idempresa";
            if ($this->oCon->Query($sql)) {
                if ($this->oCon->NumFilas() > 0) {
                    do {
                        $pcon_mon_base    = $this->oCon->f('pcon_mon_base');
                        $pcon_seg_mone    = $this->oCon->f('pcon_seg_mone');
                    } while ($this->oCon->SiguienteRegistro());
                }
            }
            $this->oCon->Free();

            $tcam_cod_tcam = 0;
            $sql = "SELECT tcam_cod_tcam FROM saetcam WHERE mone_cod_empr = $idempresa AND tcam_fec_tcam = '$fecha_ini' AND tcam_cod_mone = $pcon_seg_mone";
            if ($this->oCon->Query($sql)) {
                if ($this->oCon->NumFilas() > 0) {
                    do {
                        $tcam_cod_tcam    = $this->oCon->f('tcam_cod_tcam');
                    } while ($this->oCon->SiguienteRegistro());
                }
            }
            $this->oCon->Free();


            $sql = "SELECT mone_sgl_mone,mone_cod_mone,mone_des_mone FROM SAEMONE WHERE mone_cod_empr= $idempresa";
            $sgl_mone_array = [];
            $cod_mone_array = [];

            $mone_sgl_mone = '';
            if ($this->oCon->Query($sql)) {
                if ($this->oCon->NumFilas() > 0) {
                    do {
                        $sgl_mone_array[$this->oCon->f('mone_sgl_mone')]    = $this->oCon->f('mone_cod_mone');
                        $cod_mone_array[$this->oCon->f('mone_cod_mone')]    = $this->oCon->f('mone_sgl_mone');

                    } while ($this->oCon->SiguienteRegistro());
                }
            }
            $this->oCon->Free();
            
            $insert_count = 0;

            if ($tcam_cod_tcam == 0) {

                // Obtener el contenido de la URL

                $url_base   =  $this->url_api;
                $token      =  $this->token;

                $url = "$url_base/v6/$token/latest/".$cod_mone_array[$pcon_mon_base];
                $contenido = file_get_contents($url);

                // Verificar si la solicitud fue exitosa
                if ($contenido !== null) {
                    $data = json_decode($contenido);

                    $conversion_rates       = $data->conversion_rates;
                    $time_last_update_unix  = $data->time_last_update_unix;

                    foreach ($conversion_rates as $key => $rate_value) {
                        $valor_venta = 0;
                        $fecha_cambio = $fecha_ini;
                        $valor_compra = 0;
                        if($sgl_mone_array[$key]){


                            if($pcon_mon_base != $sgl_mone_array[$key]){
                                //solo se registraran los tipos de cambio de todas las moneda excepto la moneda principal por empresa 2024-11-06
                            
                                if($this->pais_codigo_inter ==593){
                                    if($pcon_seg_mone == $sgl_mone_array[$key]){
                                        //El tipo de cambio para la moneda secundarioa en Ecuador sera igual a 1 2024-10-18
                                        $rate_value = 1;
                                    }                               

                                }
                                
                                // registra el tipo de cambio de todas las monedas de la saemone
                                $currect_cod_mone = $sgl_mone_array[$key];

                                $valor_venta    = $rate_value;
                                $valor_compra   = $rate_value;
                                $exchange_date  = $time_last_update_unix;

                                $fecha_hora_cambio  = gmdate("Y-m-d H:i:s", $exchange_date);
                                $fecha_cambio       = gmdate("Y-m-d", $exchange_date);

                                //Valida que la fecha sea igual a la de la devuelta por el api
                                if ($fecha_cambio == $fecha_ini) {
                                    $this->oCon->QueryT('BEGIN;');

                                    $sql = "INSERT INTO saetcam(mone_cod_empr, tcam_cod_mone, tcam_val_tcam, tcam_fec_tcam, tcam_valc_tcam) 
                                                        VALUES($idempresa, $currect_cod_mone, $valor_venta, '$fecha_cambio', $valor_compra)";
                                    $this->oCon->QueryT($sql);
                                    $this->oCon->QueryT('COMMIT;');
                                    $insert_count++;
                                }
                            }
                        }
                    }
                }
            }

            $this->oCon->Free();
            return $insert_count;
            
        } catch (Exception $e) {
            // rollback
            print_r($e->getMessage());
            print_r(PHP_EOL);
            $this->oCon->QueryT('ROLLBACK;');
            return 0;
        }
    }
}

?>