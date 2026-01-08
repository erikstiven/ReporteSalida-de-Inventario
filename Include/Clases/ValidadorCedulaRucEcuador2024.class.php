<?php
// $ruta_absoluta=dirname(__FILE__);
// include_once($ruta_absoluta.'/../config.inc.php');
class ValidadorCedulaRucEcuador2024
{
    private $oIfx;
    private $oCon;
    private $pais_codigo_inter;
    private $idempresa;
    private $id_usuario;

    private $empr_ws_iden_sn;
    private $empr_ws_iden_url;
    private $empr_ws_iden_renueva;
    private $empr_ws_iden_token;
    private $last_error_message;

    function __construct($oIfx, $oCon, $idempresa,$id_usuario)
    {
        $this->oIfx = $oIfx;
        $this->oCon = $oCon;
        $this->idempresa = $idempresa?$idempresa:0;
        $this->id_usuario = $id_usuario?$id_usuario:0;

        // CREA LA TABLA PARA ALMACENAR LAS PETICIONES DE LAS IDENTIFICAICONES
        $this->init();
        $this->create_table();
    }

    private function init(){
        $sql = "SELECT 
                    empr_cod_pais,
                    pais_cod_inte, 
                    pais_codigo_inter,
                    empr_ws_iden_sn,
                    empr_ws_iden_url,
                    empr_ws_iden_renueva,
                    empr_ws_iden_token
                from saeempr em
                LEFT JOIN saepais pa on em.empr_cod_pais = pa.pais_cod_pais
                WHERE empr_cod_empr = '".$this->idempresa."';";

        if ($this->oCon->Query($sql)) {
            if ($this->oCon->NumFilas() > 0) {
                do {
                    $this->pais_codigo_inter = ($this->oCon->f('pais_codigo_inter'))?($this->oCon->f('pais_codigo_inter')):0;            
                    $this->empr_ws_iden_sn = ($this->oCon->f('empr_ws_iden_sn'))?($this->oCon->f('empr_ws_iden_sn')):0;            
                    $this->empr_ws_iden_url = ($this->oCon->f('empr_ws_iden_url'))?($this->oCon->f('empr_ws_iden_url')):0;            
                    $this->empr_ws_iden_renueva = ($this->oCon->f('empr_ws_iden_renueva'))?($this->oCon->f('empr_ws_iden_renueva')):0;            
                    $this->empr_ws_iden_token = ($this->oCon->f('empr_ws_iden_token'))?($this->oCon->f('empr_ws_iden_token')):0;   

                } while ($this->oCon->SiguienteRegistro());
            }
        }
        $this->oCon->Free();
    }
    /*** Algoritmo de validacion cédula y ruc ***/

    public function validarRUC($ruc) {
        // Verificar que el RUC tenga 13 dígitos
        if (strlen($ruc) !== 13) {
            return false;
        }

        // Obtener los dos primeros dígitos que corresponden a la provincia
        $provincia = substr($ruc, 0, 2);
        if ($provincia < 1 || $provincia > 24) {
            return false;
        }

        // Obtener el tercer dígito que identifica el tipo de entidad
        $tercerDigito = substr($ruc, 2, 1);

        // Validar RUC de persona natural
        if ($tercerDigito >= 0 && $tercerDigito <= 5) {
            return $this->validarCedula(substr($ruc, 0, 10));
        }
        // Validar RUC de entidad privada
        if ($tercerDigito == 9) {
            return $this->validarEntidadPrivada(substr($ruc, 0, 10));
        }
        // Validar RUC de entidad pública
        if ($tercerDigito == 6) {
            return $this->validarEntidadPublica(substr($ruc, 0, 9));
        }

        // Si no cumple ninguna de las validaciones anteriores, es inválido
        return false;
    }

    public function validarCedula($cedula) {
        if (strlen($cedula) !== 10) {
            return false;
        }

        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $suma = 0;

        for ($i = 0; $i < 9; $i++) {
            $producto = $cedula[$i] * $coeficientes[$i];
            if ($producto >= 10) {
                $producto -= 9;
            }
            $suma += $producto;
        }

        $digitoVerificador = (10 - ($suma % 10)) % 10;
        return $digitoVerificador == $cedula[9];
    }

    private function validarEntidadPrivada($ruc) {
        if (strlen($ruc) !== 10) {
            return false;
        }

        $coeficientes = [4, 3, 2, 7, 6, 5, 4, 3, 2];
        $suma = 0;

        for ($i = 0; $i < 9; $i++) {
            $suma += $ruc[$i] * $coeficientes[$i];
        }

        $digitoVerificador = (11 - ($suma % 11)) % 11;
        return $digitoVerificador == $ruc[9];
    }

    private function validarEntidadPublica($ruc) {
        if (strlen($ruc) !== 9) {
            return false;
        }

        $coeficientes = [3, 2, 7, 6, 5, 4, 3, 2];
        $suma = 0;

        for ($i = 0; $i < 8; $i++) {
            $suma += $ruc[$i] * $coeficientes[$i];
        }

        $digitoVerificador = (11 - ($suma % 11)) % 11;
        return $digitoVerificador == $ruc[8];
    }


    private function source_info(){
        // definir las constantes en el archivo de configuracion
        return array(
            "ws_externo"=>array(
                "validacion_identificacion"=>array(
                    "link"=>"".$this->empr_ws_iden_url."",
                    "token"=>"".$this->empr_ws_iden_token."",
                    "cedula"=>array(
                        "endpoint"=>"/api/ConsultasDatos/ConsulaCedulaV2",
                        "method"=>"GET",
                        "sent_param"=>array(
                            "p1"=>"Cedula",
                            "p2"=>"Apikey",
                        ),
                        "return_param"=>array(
                            "p1"=>"cedula",
                            "p2"=>"nombre",
                            "p3"=>"lugarDomicilio",
                        ),
                    ),
                    "ruc"=>array(
                        "endpoint"=>"/api/ConsultasDatosSri/ConsulaArtesanoCalificado",
                        "method"=>"GET",
                        "sent_param"=>array(
                            "p1"=>"Ruc",
                            "p2"=>"Apikey",
                        ),
                        "return_param"=>array(
                            "p1"=>"ruc",
                            "p2"=>"razonSocial",
                        ),
                    ),

                )
            )
        );
    }

    function valida_identificacion_ws_ecuador($identificacion,$tipo_identificaion){
        $mensaje = '';
        $fulldata = [];

        try{
            $source_info_base   = $this->source_info();
            $source_info        = $source_info_base['ws_externo']['validacion_identificacion'];
            $url_base           = $source_info['link'];
            $token              = $source_info['token'];

            $status = false;

            switch($tipo_identificaion){
                case "01":
                    //RUC
                    $tipo = 'ruc';
                    $target_config  = $source_info["$tipo"];

                    $endpoint       = $target_config["endpoint"];
                    $method         = $target_config["method"];
                    $sent_param     = $target_config["sent_param"];
                    $return_param   = $target_config["return_param"];

                    $sp1            = $sent_param["p1"];
                    $sp2            = $sent_param["p2"];

                    $rp1            = $return_param["p1"];
                    $rp2            = $return_param["p2"];

                    break;
                case "02":
                    //CEDULA
                    $tipo = 'cedula';
                    $target_config   = $source_info["$tipo"];

                    $endpoint       = $target_config["endpoint"];
                    $method         = $target_config["method"];
                    $sent_param     = $target_config["sent_param"];
                    $return_param   = $target_config["return_param"];
                    //SEND
                    $sp1   = $sent_param["p1"];
                    $sp2   = $sent_param["p2"];
                    //RECIVE
                    $rp1   = $return_param["p1"];
                    $rp2   = $return_param["p2"];
                    $rp3   = $return_param["p3"];

                    

                    break;
            }

            $source = "";

            $db_rsult                       = $this->select_request_result($identificacion,$return_param);
            $actualizable                   = $db_rsult['actualizable'];
            $ws_response_array              = $db_rsult['ws_response'];
            $id_peticiones_identificacion   = $db_rsult['id_peticiones_identificacion'];

            $p1_db = $ws_response_array["$rp1"];
            $p2_db = $ws_response_array["$rp2"];
            $p3_db = $ws_response_array["$rp3"];

            
            if (!empty($ws_response_array) && !empty($p1_db) && !empty($p2_db) && $actualizable =='N' ){

                $source = "DB";
                $p1 = $p1_db;
                $p2 = $p2_db;
                $p3 = $p3_db;
                $status = true;

                $this->save_request_log($id_peticiones_identificacion);
            }else{
                $source = "WS";

                $params_array = array(
                    "$sp1" => "$identificacion",
                    "$sp2" => "$token"
                );
                $param_query = http_build_query($params_array);


                $full_url = $url_base.$endpoint."?".$param_query;

                $header_array       = $this->create_request_header('','',[]);
                $api_response       = $this->api_request($full_url,'','',$method,$header_array);

                $api_response_array = json_decode($api_response,true);
                $http_code          = $api_response_array['http_code'];
                $mensaje            = $api_response_array['mensaje'];
                $data               = $api_response_array['data'];
                $fulldata           = $data;

                if(intval($http_code)>=200 && intval($http_code)<300){
                    // rango de codigo http correcto
                    $p1 = $data["$rp1"];
                    $p2 = $data["$rp2"];
                    $p3 = $data["$rp3"];
                    if (!empty($p1) && !empty($p2)){
                        $status = true;
                    }
                }
                $this->save_request_result($identificacion,$data,$process,$full_url,$tipo_identificaion,$id_peticiones_identificacion);

            }
        } catch (Exception $e) {
            $mensaje = $e->getMessage();
            $this->$last_error_message = $mensaje;
        }


        return array('status'=>$status,'data'=>array('identificacion'=>$p1,'nombres'=>$p2,'direccion'=>$p3),"full_data"=>$fulldata,"source"=>"$source","mensaje"=>$mensaje);

    }
    function getError(){
        return "Se a producido un error ".$this->$last_error_message;
    }

    private function create_table(){
        $ctralterpresu = 0;
        $ctralter1 = 0;
        $ctralter1 = 0;

        $sql = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE  TABLE_NAME = 'peticiones_identificacion' and table_schema='comercial'";

        if ($this->oCon->Query($sql)) {
            if ($this->oCon->NumFilas() > 0) {
                do {
                    $ctralterpresu = $this->oCon->f('conteo')?$this->oCon->f('conteo'):0;            
                } while ($this->oCon->SiguienteRegistro());
            }
        }
        $this->oCon->Free();

        if ($ctralterpresu == 0) {
            $sql = "CREATE TABLE comercial.peticiones_identificacion (
                        id serial,
                        id_empresa int4,
                        id_usuario int4,
                        identificacion varchar(150),
                        ws_response JSONB, 
                        ws_request text, 
                        request_date timestamp, 
                        pais_codigo_inter varchar(150), 
                        tipo_identificacion varchar(150),
                        contador_peticion int4,
                        fecha_registro timestamp
                    );";

            $this->oCon->QueryT($sql);
            $this->oCon->Free();

            $sql = "ALTER TABLE comercial.peticiones_identificacion ADD CONSTRAINT pk_id_peticiones_identificacion PRIMARY KEY (id);";
            $this->oCon->QueryT($sql);
            $this->oCon->Free();
        }else{
            $ctralter1 = 0;
            $sql = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE 
                    COLUMN_NAME = 'id_empresa' 
                    AND TABLE_NAME = 'peticiones_identificacion' 
                    and table_schema = 'comercial'";
            
            if ($this->oCon->Query($sql)) {
                if ($this->oCon->NumFilas() > 0) {
                    do {
                        $ctralter1 = $this->oCon->f('conteo')?$this->oCon->f('conteo'):0;            
                    } while ($this->oCon->SiguienteRegistro());
                }
            }
            $this->oCon->Free();

            if ($ctralter1 == 0) {
                $sql = "ALTER TABLE comercial.peticiones_identificacion ADD id_empresa int4;";
                $this->oCon->QueryT($sql);
                $this->oCon->Free();
            }
            /* 000000000000000000000000000000000000000000000000000000000000000000000000000 */
            $ctralter1 = 0;

            $sql = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE 
                    COLUMN_NAME = 'id_usuario' 
                    AND TABLE_NAME = 'peticiones_identificacion' 
                    and table_schema = 'comercial'";
            
            if ($this->oCon->Query($sql)) {
                if ($this->oCon->NumFilas() > 0) {
                    do {
                        $ctralter1 = $this->oCon->f('conteo')?$this->oCon->f('conteo'):0;            
                    } while ($this->oCon->SiguienteRegistro());
                }
            }
            $this->oCon->Free();

            if ($ctralter1 == 0) {
                $sql = "ALTER TABLE comercial.peticiones_identificacion ADD id_usuario int4;";
                $this->oCon->QueryT($sql);
                $this->oCon->Free();
            }
            /* 000000000000000000000000000000000000000000000000000000000000000000000000000 */
            $ctralter2 = 0;

            $sql = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE 
                    COLUMN_NAME = 'fecha_registro' 
                    AND TABLE_NAME = 'peticiones_identificacion' 
                    and table_schema = 'comercial'";

            if ($this->oCon->Query($sql)) {
                if ($this->oCon->NumFilas() > 0) {
                    do {
                        $ctralter2 = $this->oCon->f('conteo')?$this->oCon->f('conteo'):0;            
                    } while ($this->oCon->SiguienteRegistro());
                }
            }
            $this->oCon->Free();

            if ($ctralter2 == 0) {
                $sql = "ALTER TABLE comercial.peticiones_identificacion ADD fecha_registro timestamp;";
                $this->oCon->QueryT($sql);
                $this->oCon->Free();
            }

            /* 000000000000000000000000000000000000000000000000000000000000000000000000000 */
            $ctralter2 = 0;
            $sql = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE 
                    COLUMN_NAME = 'contador_peticion' 
                    AND TABLE_NAME = 'peticiones_identificacion' 
                    and table_schema = 'comercial'";

            if ($this->oCon->Query($sql)) {
                if ($this->oCon->NumFilas() > 0) {
                    do {
                        $ctralter2 = $this->oCon->f('conteo')?$this->oCon->f('conteo'):0;            
                    } while ($this->oCon->SiguienteRegistro());
                }
            }
            $this->oCon->Free();

            if ($ctralter2 == 0) {
                $sql = "ALTER TABLE comercial.peticiones_identificacion ADD contador_peticion int4;";
                $this->oCon->QueryT($sql);
                $this->oCon->Free();
            }
        }


        $ctralterpresu_log = 0;
        $sql = "SELECT count(*) as conteo
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE  TABLE_NAME = 'peticiones_identificacion_log' and table_schema='comercial'";

        if ($this->oCon->Query($sql)) {
            if ($this->oCon->NumFilas() > 0) {
                do {
                    $ctralterpresu_log = $this->oCon->f('conteo')?$this->oCon->f('conteo'):0;            
                } while ($this->oCon->SiguienteRegistro());
            }
        }
        $this->oCon->Free();

        if ($ctralterpresu_log == 0) {
            $sql = "CREATE TABLE comercial.peticiones_identificacion_log (
                        id serial,
                        id_empresa int4,
                        id_peticiones_identificacion int4,
                        id_usuario int4,
                        fecha_registro timestamp
                    );";

            $this->oCon->QueryT($sql);
            $this->oCon->Free();

            $sql = "ALTER TABLE comercial.peticiones_identificacion_log ADD CONSTRAINT pk_id_peticiones_identificacion_log PRIMARY KEY (id);";
            $this->oCon->QueryT($sql);
            $this->oCon->Free();
        }


    }

    private function save_request_result($identificacion,$data,$process,$full_url,$tipo_identificacion, $id_peticiones_identificacion){
        /* guarda la consultas consultas de las identificaiones
            para evitar futuras peticiones al ws y evitar cargos adicionales innecesarios
        */
        switch ($tipo_identificacion) {
            case '01':
                $tipo = "ruc";
                break;
            case '02':
                $tipo = "cedula";
                break;
            default:
                $tipo = "nothing";
                break;            
        }

        switch ($variable) {
            case 'value':
                # code...
                break;
            
            default:
                # code...
                break;
        }

        $source_info_base   = $this->source_info();
        $source_info        = $source_info_base['ws_externo']['validacion_identificacion'];
        $target_config      = $source_info["$tipo"];

        $sent_param         = $target_config["sent_param"];
        $return_param       = $target_config["return_param"];

        $rp1   = $return_param["p1"];
        $rp2   = $return_param["p2"];
        $rp3   = $return_param["p3"];


        $db_rsult                       = $this->select_request_result($identificacion,$return_param);
        $actualizable                   = $db_rsult['actualizable'];
        $ws_response                    = $db_rsult['ws_response'];
        $id_peticiones_identificacion   = $db_rsult['id_peticiones_identificacion'];
        $p1                             = $ws_response["$rp1"];
        $p2                             = $ws_response["$rp2"];
        $p3                             = $ws_response["$rp3"];

        if(!empty($p1) && !empty($p1) && $actualizable=='S'){
            // actualizar
            $fecha_servidor_this =  "'".date("Y-m-d H:i:s")."'";

            $sql = "UPDATE comercial.peticiones_identificacion  
            SET 
                ws_response = '".json_encode($data)."',
                request_date = $fecha_servidor_this,
                contador_peticion = (contador_peticion + 1)
            WHERE
                identificacion = '".$identificacion."'
                AND id_empresa = '".$this->idempresa."'
                RETURNING id;";

        }else{

            $fecha_servidor_this = "'".date("Y-m-d H:i:s")."'";

            $sql = "INSERT INTO 
                        comercial.peticiones_identificacion 
                        (
                            identificacion,
                            id_empresa,
                            ws_response, 
                            ws_request, 
                            request_date, 
                            pais_codigo_inter, 
                            tipo_identificacion,
                            contador_peticion,
                            id_usuario 
                        ) 
                    VALUES (
                        '".$identificacion."',
                        '".$this->idempresa."',
                        '".json_encode($data)."',
                        '$full_url',
                        $fecha_servidor_this,
                        '".$this->pais_codigo_inter."',
                        '".$tipo_identificacion."',
                        '1',
                        '".$this->id_usuario."'
                        ) RETURNING id;";
        }

        $peticiones_identificacion = 0;
        if ($this->oCon->Query($sql)) {
            if ($this->oCon->NumFilas() > 0) {
                do {
                    $peticiones_identificacion =  $this->oCon->f('id');
                }while ($this->oCon->SiguienteRegistro());
            }
        }
        $this->oCon->Free();

        $peticiones_identificacion = empty($peticiones_identificacion)?$id_peticiones_identificacion:$peticiones_identificacion;
        $this->save_request_log($peticiones_identificacion);


    }

    private function save_request_log($id_peticiones_identificacion){
        $fecha_servidor_this =  date("Y-m-d H:i:s");

        $sql = "INSERT INTO comercial.peticiones_identificacion_log (
            id_empresa,
            id_peticiones_identificacion,
            id_usuario,
            fecha_registro
            ) values (
                '".$this->idempresa."',
                '".$id_peticiones_identificacion."',
                '".$this->id_usuario."',
                '".$fecha_servidor_this."'
            );";

        $this->oCon->QueryT($sql);
        $this->oCon->Free();
    }

    private function select_request_result($identificacion,$return_param){
        /* consultas de las identificaiones
            de previas peticiones al ws 
        */
        $ws_response = '';
        $rango_actualizacion = $this->empr_ws_iden_renueva?$this->empr_ws_iden_renueva:30;//en dias


        if (!empty($return_param['p1'])){
            $statement_p1 = " identificacion as ".$return_param["p1"].", ".PHP_EOL;
        }

        if (!empty($return_param['p2'])){
            $statement_p2 = " ws_response->>'".$return_param["p2"]."' as ".$return_param["p2"].",".PHP_EOL;
        }

        if (!empty($return_param['p3'])){
            $statement_p3 = " ws_response->>'".$return_param["p3"]."' as ".$return_param["p3"].", ".PHP_EOL;
        }

        $fecha_servidor_this =  "'".date("Y-m-d H:i:s")."'";
        $sql = "SELECT
                    id,
                    $statement_p1
                    $statement_p2
                    $statement_p3
                    ws_request, 
                    request_date, 
                    pais_codigo_inter, 
                    tipo_identificacion,
                    case when (request_date is not null ) then (
                        CASE		
                                WHEN request_date :: DATE IS NULL THEN 'S' ELSE (case
                                WHEN (( $fecha_servidor_this :: DATE - request_date :: DATE )::INTEGER) > $rango_actualizacion THEN	'S' else 'N' end)
                            END ) else 'S'
                    end AS actualizable
                FROM comercial.peticiones_identificacion 
                WHERE 
                    identificacion = '$identificacion'
                    
                ORDER BY request_date desc
                limit 1;";

        $array_result = array();
        if ($this->oCon->Query($sql)) {
            if ($this->oCon->NumFilas() > 0) {
                do {
                    $id_peticiones_identificacion   = $this->oCon->f('id');

                    $p1                     = $this->oCon->f($return_param["p1"]);
                    $ws_response            = $this->oCon->f('ws_response');
                    $p2                     = $this->oCon->f($return_param["p2"]);
                    $p3                     = $this->oCon->f($return_param["p3"]);
                    $ws_request             = $this->oCon->f('ws_request');
                    $request_date           = $this->oCon->f('request_date');
                    $pais_codigo_inter      = $this->oCon->f('pais_codigo_inter');
                    $tipo_identificacion    = $this->oCon->f('tipo_identificacion');
                    $actualizable           = $this->oCon->f('actualizable');
            
                } while ($this->oCon->SiguienteRegistro());
            }
        }
        $this->oCon->Free();

        $result_params = [];

        if (!empty($return_param['p1'])){
            $result_params['p1'] = $p1;
        }

        if (!empty($return_param['p2'])){
            $result_params['p2'] = $p2;
        }

        if (!empty($return_param['p3'])){
            $result_params['p3'] = $p3;
        }


        return array(
            "actualizable"=>$actualizable,
            "ws_response"=>$result_params,
            "id_peticiones_identificacion"=>$id_peticiones_identificacion?$id_peticiones_identificacion:0
        );
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
            
            $resp = json_decode($resp,true);
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
}
?>