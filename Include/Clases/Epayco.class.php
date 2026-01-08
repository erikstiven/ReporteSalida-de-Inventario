<?php
// require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class EpaycoAPI
{

    var $oConexion1;
    var $oConexion2;

    private $nombre_integracion = 'EPAYCO';
    private $estado_sn_integracion = 'S';
    private $ambiente_integracion = 'TEST';
    private $login_mail_sn = 'N'; // N: por ids; S: por mail y password del mail

    private $source_information = array();
    private $current_jwt_token = '';

    function __construct($oConexion1, $oConexion2)
    {
        $this->oConexion1 = $oConexion1;
        $this->oConexion2 = $oConexion2;

        $sql = "SELECT 
                    id,
                    estado_sn,
                    url_api,
                    usuario,
                    clave,
                    auth_autorizacion,
                    request_autorizacion,
                    otro_parametro1,
                    otro_parametro2,
                    otro_parametro3

                FROM COMERCIAL.INTEGRACIONES 
                WHERE 
                    nombre_integracion = '".$this->nombre_integracion."' AND 
                    estado_sn = '".$this->estado_sn_integracion."' and 
                    ambiente = '".$this->ambiente_integracion."'";
    
        if ($this->oConexion1->Query($sql)) {
            if ($this->oConexion1->NumFilas() > 0) {
                do {                   
                    $id_integracion             = $this->oConexion1->f('id');
                    $estado_sn                  = $this->oConexion1->f('estado_sn');
                    $url_api                    = $this->oConexion1->f('url_api');
                    $usuario                    = $this->oConexion1->f('usuario');
                    $clave                      = $this->oConexion1->f('clave');
                    $token_type                 = $this->oConexion1->f('auth_autorizacion');//basic
                    $auth_autorizacion_in       = $this->oConexion1->f('auth_autorizacion');//basic
                    $request_autorizacion_in    = $this->oConexion1->f('request_autorizacion');//bearer
                    $otro_parametro1            = $this->oConexion1->f('otro_parametro1');//otro_parametro1
                    $otro_parametro2            = $this->oConexion1->f('otro_parametro2');//otro_parametro2
                    $otro_parametro3            = $this->oConexion1->f('otro_parametro3');//otro_parametro3
    
                } while ($this->oConexion1->SiguienteRegistro());
            }
        }
        $this->oConexion1->Free();

        if ($estado_sn == 'S' && !empty($url_api) && !empty($usuario) && !empty($clave) && !empty($this->login_mail_sn)) {
            
            
            $login_type = $this->login_mail_sn=='S'?'/mail':'';
            
            $this->source_information = array(
                "id_integracion"    => "$id_integracion",
                "base_url"          => "$url_api",
                "username"          => "$usuario",
                "password"          => "$clave",
                "version"           => "001",
                "test_url"          => "https://apify.epayco.co",
                "endpoints" => array(
                    "login" => array(
                        "path"              => "/login$login_type",
                        "descripcion"       => "Endpoint para generar el token jwt para las peticiones",
                        "method"            => "POST",
                        "sent_info_way"     => '',
                        "auth_type"         => "Basic",
                        "token_required"    => false,                        
                        "otro_parametro1"    => $otro_parametro1,
                        "otro_parametro2"    => $otro_parametro2,
                        "otro_parametro3"    => $otro_parametro3

                    ),
                    "create_pay_link" => array(
                        "path"              => "/collection/link/create",
                        "descripcion"       => "Endpoint para crear un link de pago",
                        "method"            => "POST",
                        "sent_info_way"     => 'body',
                        "auth_type"         => "Bearer",
                        "token_required"    => true,
                        "otro_parametro1"    => $otro_parametro1,
                        "otro_parametro2"    => $otro_parametro2,
                        "otro_parametro3"    => $otro_parametro3

                    ),
                    "update_pay_link" => array(
                        "path"              => "/collection/link/update",
                        "descripcion"       => "Endpoint para modificar el enlace de pago genrado",
                        "method"            => "POST",
                        "sent_info_way"     => 'body',
                        "auth_type"         => "Bearer",
                        "token_required"    => true,                        
                        "otro_parametro1"    => $otro_parametro1,
                        "otro_parametro2"    => $otro_parametro2,
                        "otro_parametro3"    => $otro_parametro3

                    ),
                    "list_pay_link" => array(
                        "path"              => "/collection/link",
                        "descripcion"       => "Endpoint para enlistar los enlaces de pago",
                        "method"            => "GET",
                        "sent_info_way"     => 'body',
                        "auth_type"         => "Bearer",
                        "token_required"    => true,                        
                        "otro_parametro1"    => $otro_parametro1,
                        "otro_parametro2"    => $otro_parametro2,
                        "otro_parametro3"    => $otro_parametro3

                    )


                )
            );
        }
    }


    private function create_general_request($request_data){

        $target         = $request_data['target'];
        $token          = $request_data['token'];
        $data           = $request_data['data'];


        $id_integracion = $this->source_information['id_integracion'];
        $url_api        = $this->source_information['base_url'];
        $username       = $this->source_information['username'];
        $password       = $this->source_information['password'];
        $endpoint_data  = $this->source_information['endpoints'][$target];

        $endpoint       = $endpoint_data['path'];
        $descripcion    = $endpoint_data['descripcion'];
        $method         = $endpoint_data['method'];
        $sent_info_way  = $endpoint_data['sent_info_way'];
        $auth_type      = $endpoint_data['auth_type'];
        $token_required = $endpoint_data['token_required'];

        $url            = $url_api.$endpoint;
        
        // tipo de autenticacion header
        switch ($auth_type) {
            case 'Basic':
                $token_array = array('username'=>$username,"password"=>$password);
                break;
            
            case 'Bearer':
                $token_array = array('token'=>$token);
                break;
        }

        switch ($sent_info_way) {
            case 'body':
                // validar si data es diferente de vacia
                $data = $data;
                break;
            
            case 'param':
                // generar un query param
                $data = '';
                break;
        }

        $header_array   = $this->create_request_header($token_array,$auth_type,[]);
        $response_data  = $this->api_request($url,$data,$method,$header_array);

        $response_data  = json_decode($response_data,true);        
        $response_data['id_integracion'] = $id_integracion;

        switch ($target) {
            case 'login':
                # actualizar los datos de login
                $this->update_login_data($response_data);
                break;
        }

        return $response_data;

    }
    
    private function update_login_data($login_data){
        $proceso = 'update_login_data';
    
        $current_date               = date('Y-m-d H:i:s');
        $request_autorizacion_in    = 'Bearer';

        $login_data['id_integracion'] = ($login_data['id_integracion'])?($login_data['id_integracion']):0;

        if ($login_data["http_code"]=='200'){
            $http_code      = ($login_data["http_code"]);
            $token          = ($login_data["data"]["token"]);

            $sql = "UPDATE comercial.integraciones SET 
                        token = '".$token."',
                        fecha_modificacion = '".$current_date."',
                        request_autorizacion = '$request_autorizacion_in'
                    WHERE 
                    id = '".$login_data['id_integracion']."'
                    ";

            $this->oConexion1->QueryT('BEGIN;');
            $this->oConexion1->QueryT($sql);
            $this->oConexion1->QueryT('COMMIT;');
            $this->oConexion1->Free();
                        
        }
        
    
        $return_data = json_encode(array(
            "http_code"=>$http_code,
            "data"=>array(
                "proceso"=>$proceso,
                "token"=>$token,
                "type"=>$request_autorizacion_in
            )
        ),true);
    
        return $return_data;
    
    }
    
    private function api_request($url, $body, $method, $header_array) {
    
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
    
    private function create_request_header($crecential_array,$token_type,$others_array){
        $header_array = array(
            'Accept: application/json',
            'Content-Type: application/json',
        );
    
        if (!empty($crecential_array) && !empty($token_type)) {
    
            switch ($token_type) {
                case 'Basic':
    
                    $credenctial = base64_encode($crecential_array['username'].":".$crecential_array['password']);
                    array_push($header_array, "Authorization: $token_type $credenctial");
                    break;
                
    
                case 'Bearer':
                    array_push($header_array, "Authorization: $token_type ".$crecential_array['token']."");
    
                    break;
            }
        }
    
        foreach ($others_array as $key => $value) {
            array_push($header_array, "$value");
        }
    
        return $header_array;
    }

    public function epayco_request($target,$data){

        $request_response = [];
        if(!empty($target)){
            $login_request_data = array(
                "data"=>"",
                "target"=>"login",
                "token"=>""
            );

            $login_data = $this->create_general_request($login_request_data);

            $http_code      = intval($login_data['http_code']);
            $response_data  = $login_data['data'];

            if($http_code>=200 && $http_code <300){
                $request_data = array(
                    "data"      => $data,
                    "target"    => $target,
                    "token"     => $response_data['token']
                );

                $request_response = $this->create_general_request($request_data);
            }
        }
        return $request_response;
    }

    public function get_source_information(){
        return $this->source_information;
    }



}
