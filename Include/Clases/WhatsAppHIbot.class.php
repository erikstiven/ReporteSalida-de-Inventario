<?php
//require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class WhatsAppHibot
{
    private $oIfx;

    public $empr_whatsapp_sn = 1;
    public $empr_whatsapp_url = 1;
    public $duracion= 23;
    public $ultima_generacion= null;
    public $app_id= null;
    public $app_secret= null;
    public $token_actual= null;
    public $id_empresa= null;

    function __construct($oIfx, $url_api, $duracion, $ultima_generacion, $app_id, $app_secret, $token_actual, $id_empresa)
    {
        $this->oIfx = $oIfx;
        $this->empr_whatsapp_url = $url_api;
        $this->duracion = $duracion;
        $this->ultima_generacion = $ultima_generacion;

        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $this->token_actual = $token_actual;
        $this->id_empresa = $id_empresa;

    }

    function verificarSessionWhatsApp()
    {
        if($this->empr_whatsapp_sn){

            try {

                $fechaServer = date("Y-m-d H:i:s");
                $fechaActual = strtotime ($fechaServer);
                $fechaUltima = strtotime ( "+$this->duracion hour" , strtotime($this->ultima_generacion));

                if($fechaActual >= $fechaUltima || !$this->token_actual){
                    echo "GENERAR NUEVO TOKEN \n";

                    $data = array(
                        'appId' => $this->app_id,
                        'appSecret' => $this->app_secret
                    );

                    $headers = array(
                        "Content-Type:application/json"
                    );

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_URL, "$this->empr_whatsapp_url/login");
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    $respuesta = curl_exec($ch);

                    if (!curl_errno($ch)) {
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $data_json = json_decode($respuesta, true);

                        switch ($http_code) {
                            case 200:
                                $token_nuevo = $data_json['token'];
                                $result = $token_nuevo;

                                $sql_update = "UPDATE comercial.hibot_token 
                                               SET token = '$token_nuevo', ultima_generacion = '$fechaServer'
                                               WHERE empresa_id = $this->id_empresa;";
                                $this->oIfx->QueryT($sql_update);

                                break;
                            default:
                                throw new Exception("Error desconocido en el WebService, Consulte con el administrador");
                        }

                    } else {
                        $errorMessage = curl_error($ch);
                        throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
                    }


                }else{
                    echo "RETORNAR TOKEN SESSION \n";
                    $result = $this->token_actual;
                }

            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

        }else{
            throw new Exception('No tiene activo el envio de WhatsApp');
        }

        return $result;
    }

    function validate_url($url) {
        $path = parse_url($url, PHP_URL_PATH);
        $encoded_path = array_map('urlencode', explode('/', $path));
        $url = str_replace($path, implode('/', $encoded_path), $url);
    
        return filter_var($url, FILTER_VALIDATE_URL) ? true : false;
    }
    

    function enviarMensajeWhatsApp($channelId, $recipient, $template, $params, $result_token)
    {
        if($this->empr_whatsapp_sn){

            try {

                $data = array(
                    'channelId' => $channelId,
                    'recipient' => $recipient,
                    'content' => '',
                    'language' => 'es',
                    'template' => $template,
                    'params' => explode("|",$params),
                );


                $headers = array(
                    "Content-Type:application/json",
                    "Authorization: Bearer $result_token",
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, "$this->empr_whatsapp_url/messages");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $respuesta = curl_exec($ch);


                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);


                    switch ($http_code) {
                        case 200:

                            if(isset($data_json[0]['id'])){
                                $validacion = $data_json[0]['id'];
                            }else{
                                throw new Exception('No se pudo enviar el mensaje, Intente de nuevo');
                            }

                            $result = $validacion;

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

        }else{
            throw new Exception('No tiene activo el envio de WhatsApp');
        }

        return $result;
    }

    function enviarMensajeWhatsApp_img($channelId, $recipient, $template, $params, $result_token,$url_img_ruta)
    {
        if($this->empr_whatsapp_sn){

            try {

                $data = array(
                    'channelId' => $channelId,
                    'recipient' => $recipient,
                    'content' => '',
                    'language' => 'es',
                    'template' => $template,
                    'params' => explode("|",$params),
                );
                if(!empty($url_img_ruta)){

                    $data['mediaType'] = 'INTERACTIVE';
                    $data['headerType'] = "IMAGE";
                    $data['media'] = $url_img_ruta;
                }

                $headers = array(
                    "Content-Type:application/json",
                    "Authorization: Bearer $result_token",
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, "$this->empr_whatsapp_url/messages");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $respuesta = curl_exec($ch);


                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);


                    switch ($http_code) {
                        case 200:

                            if(isset($data_json[0]['id'])){
                                $validacion = $data_json[0]['id'];
                            }else{
                                throw new Exception('No se pudo enviar el mensaje, Intente de nuevo');
                            }

                            $result = $validacion;

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

        }else{
            throw new Exception('No tiene activo el envio de WhatsApp');
        }

        return $result;
    }

    function obtener_estado_mensaje_por_canal($array_channelId, $url_estado_mensaje)
    {
        if($this->empr_whatsapp_sn){

            try {

                $data = array(
                    'channels' => $array_channelId
                );

                // {"channels":["6499b26f8fe5182dd88e024d"]}


                $headers = array(
                    "Content-Type:application/json",
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, "$url_estado_mensaje");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $respuesta = curl_exec($ch);


                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    switch ($http_code) {
                        case 200:

                            if(isset($data_json['data'])){
                                $validacion = $data_json['data'];
                            }else{
                                $validacion = [];
                            }

                            $result = $validacion;
                            // echo json_encode($result);

                            break;
                        case 429:

                            if(isset($data_json['error'])){
                                $validacion = ' '.$data_json['error'];
                                throw new Exception($validacion);
                            }else{
                                $validacion = [];
                            }

                            $result = $validacion;

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

        }else{
            throw new Exception('No tiene activo el envio de WhatsApp');
        }

        return $result;
    }

}

?>