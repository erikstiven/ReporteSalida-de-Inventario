<?php

class WhatsAppConnection
{
    const VERIFICACION_CONECTADO = "CONNECTED";
    const VERIFICACION_DESCONECTADO = "DISCONNECTED";
    const VERIFICACION_INVALIDO = "INVALIDO";
    const VERIFICACION_PENDING = "PENDING";
    const VERIFICACION_OPENING = "OPENING";
    const VERIFICACION_QR = "qrcode";
    const RESULT_ACCESS_DENIED = "ACCESS_DENIED";
    const RESULT_SUCCESS = "SUCCESS";

    private $oIfx;
    public $empr_whatsapp_sn = 0;
    public $empr_whatsapp_url = '';
    public $empr_whatsapp_token = '';
    public $empr_cod_inter = "";

    function __construct($oIfx, $idempresa)
    {
        $this->oIfx = $oIfx;

        $sql = "SELECT empr_cod_empr, empr_nom_empr, empr_whatsapp_sn, empr_whatsapp_url, empr_whatsapp_token, empr_cod_pais
                    FROM saeempr 
                    WHERE empr_cod_empr = $idempresa";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $this->empr_whatsapp_sn = $oIfx->f('empr_whatsapp_sn');
                $this->empr_whatsapp_url = $oIfx->f('empr_whatsapp_url');
                $this->empr_whatsapp_token = $oIfx->f('empr_whatsapp_token');
                $empr_cod_pais = $oIfx->f('empr_cod_pais');
            }
        }
        $oIfx->Free();

        $sql = "SELECT pais_codigo_inter FROM saepais WHERE pais_cod_pais = $empr_cod_pais;";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $this->empr_cod_inter = preg_replace('/\D/', '', $oIfx->f('pais_codigo_inter'));
            }
        }
    }


    private function restaurar_qr(){
        $token = $this->empr_whatsapp_token;
        $url_base = $this->empr_whatsapp_url;

        $headers = array(
            "Authorization: Bearer $token",
            "Origin: http://10.1.10.13:8087/"
        );
        $url = $url_base.'api/whatsappsession/';
        $cha = curl_init($url);
        curl_setopt($cha, CURLOPT_URL, $url);
        curl_setopt($cha, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($cha, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($cha, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cha, CURLOPT_TIMEOUT, 10);
        $respuesta = curl_exec($cha);
        if (!curl_errno($cha)) {
            $http_code = curl_getinfo($cha, CURLINFO_HTTP_CODE);
            $data_json = json_decode($respuesta, true);

            switch ($http_code) {
                case 200:
                    return null;
                default:
                    $data_json = implode($data_json);
                    throw new Exception("Error desconocido al restaurar el codigo qr. Consulte con el administrador: \n\tCodigo: $http_code \n\tMensaje: $data_json");
            }

        } else {
            $errorMessage = curl_error($cha);
            throw new Exception("Error no se puede conectar al WebService ($errorMessage)");
        }
    }
    function verificarSessionWhatsApp($es_primer_intento = true)
    {
        if($this->empr_whatsapp_sn){

            try {
                $token = $this->empr_whatsapp_token;
                $url_base = $this->empr_whatsapp_url;

                $headers = array(
                    "Authorization: Bearer $token",
                    "Origin: http://10.1.10.13:8087/"
                );
                
                $url = $url_base.'api/whatsapp/';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                $respuesta = curl_exec($ch);
                

                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);
                    //var_dump($data_json);
                    switch ($http_code) {
                        case 200:
                            if ($data_json['status'] === WhatsAppConnection::VERIFICACION_CONECTADO){
                                $result = [ 
                                    'status' => WhatsAppConnection::VERIFICACION_CONECTADO,
                                ];
                            } elseif ($data_json['status'] === WhatsAppConnection::VERIFICACION_QR){
                                $result = [
                                    'status' => WhatsAppConnection::VERIFICACION_DESCONECTADO,
                                    'qrcode' => $data_json['qrcode']
                                ];
                            } elseif ($data_json['status'] === WhatsAppConnection::VERIFICACION_DESCONECTADO){
                                if($es_primer_intento){
                                    $this->restaurar_qr();
                                    $result = $this->verificarSessionWhatsApp(false);
                                }else{
                                    throw new Exception("Error desconocido en el WebService, Consulte con el administrador: \n\tCodigo: $http_code \n\tStatus: ".$data_json['status']);
                                }
                            } elseif($data_json['status'] === WhatsAppConnection::VERIFICACION_OPENING){
                                if($es_primer_intento){
                                    $this->restaurarSessionWhatsApp();
                                    $result = $this->verificarSessionWhatsApp(false);
                                }else{
                                    throw new Exception("Error desconocido en el WebService, Consulte con el administrador: \n\tCodigo: $http_code \n\tStatus: ".$data_json['status']);
                                }
                            }else{
                                $result = [ 
                                    'status' => WhatsAppConnection::VERIFICACION_INVALIDO,
                                    'result' => $data_json['status'],
                                ];
                            }
                                
                            break;
                        case 401:
                            $result = [
                                'status' => WhatsAppConnection::RESULT_ACCESS_DENIED
                            ];
                            break;
                        default:
                            $data_json = implode($data_json);
                            throw new Exception("Error desconocido en el WebService, Consulte con el administrador: \n\tCodigo: $http_code \n\tMensaje: $data_json");
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
    function enviarMensajeWhatsApp($numero,$mensaje,$media = null)
    {
        if($this->empr_whatsapp_sn){

            try {
                $token = $this->empr_whatsapp_token;
                $url_base = $this->empr_whatsapp_url;

                $headers = array(
                    "Authorization: Bearer $token",
                    "Origin: http://10.1.10.13:8087/", 
                );

                // a exepcion de Norway and Greenland ningun numero + country code tiene menos de 11 digitos
                $numero = preg_replace('/\D/', '', $numero);
                if(strlen($numero) < 11 ){
                    //elimina los '0' al inicio de la cadena
                    $numero = preg_replace('/^0+/','', $numero);
                    $numero = $this->empr_cod_inter.$numero;
                }

                $data = array(
                    'number' => $numero,
                    'body' => $mensaje
                );

                if($media !== null){
                    $data['medias'] = $media;
                    $headers[] = "Content-Type:multipart/form-data";
                }else{
                    $data = json_encode($data);
                    $headers[] = "Content-Type:application/json";
                }
                
                $url = $url_base.'api/messages/send/';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST,1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                $respuesta = curl_exec($ch);
                

                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);
                    
                    switch ($http_code) {
                        case 200:
                            $result = [
                                'result' => $respuesta,
                                'status' => WhatsAppConnection::RESULT_SUCCESS,
                                'message_id' => $data_json['message_id']
                            ];
                            break;
                        case 401:
                            $result = [
                                'result' => $respuesta,
                                'status' => WhatsAppConnection::RESULT_ACCESS_DENIED
                            ];
                            break;
                        default:
                            //$data_json = implode($data_json);
                            throw new Exception("Error desconocido en el WebService, Consulte con el administrador: \n\tCodigo: $http_code \n\tMensaje: $respuesta");
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
    private function restaurarSessionWhatsApp(){
        $token = $this->empr_whatsapp_token;
        $url = $this->empr_whatsapp_url.'api/whatsapp/';

        $headers = array(
            "Authorization: Bearer $token",
            "Origin: http://10.1.10.13:8087/"
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $respuesta = curl_exec($ch);
        if (!curl_errno($ch)) {
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $data_json = json_decode($respuesta, true);

            switch ($http_code) {
                case 200:
                    return null;
                default:
                    $data_json = implode($data_json);
                    throw new Exception("Error desconocido al restaurar el WebService, Consulte con el administrador: \n\tCodigo: $http_code \n\tMensaje: $data_json");
            }

        } else {
            $errorMessage = curl_error($ch);
            throw new Exception("Hubo un error no se puede conectar al WebService ($errorMessage)");
        }
    }
    function desconectarSessionWhatsApp()
    {
        if($this->empr_whatsapp_sn){

            try {
                $token = $this->empr_whatsapp_token;
                $url = $this->empr_whatsapp_url.'api/whatsappsession/';

                $headers = array(
                    "Authorization: Bearer $token",
                    "Origin: http://10.1.10.13:8087/"
                );

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                $respuesta = curl_exec($ch);
                

                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    switch ($http_code) {
                        case 200:
                            $this->restaurarSessionWhatsApp();
                            $result = [ 
                                'status' => true,
                                'mensaje'=> $data_json['message'],
                            ];
                            break;
                        case 401:
                            $result = [
                                'status' => WhatsAppConnection::RESULT_ACCESS_DENIED
                            ];
                            break;
                        default:
                            $data_json = implode($data_json);
                            throw new Exception("Error desconocido en el WebService, Consulte con el administrador: \n\tCodigo: $http_code \n\tMensaje: $data_json");
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

    function obtenerEstadoMensajes($ids = [])
    {
        if($this->empr_whatsapp_sn){
            try {
                $token = $this->empr_whatsapp_token;
                $url = $this->empr_whatsapp_url.'api/messages/status/';

                $headers = [
                    "Authorization: Bearer $token",
                    "Origin: http://10.1.10.13:8087/",
                    "Content-Type:application/json"
                ];
                $data = [
                    "ids" => $ids
                ];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                //curl_setopt($ch, CURLOPT_POST,1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                $respuesta = curl_exec($ch);
                

                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    switch ($http_code) {
                        case 200:
                            $result = [ 
                                'status' => WhatsAppConnection::RESULT_SUCCESS,
                                'result'=> $data_json['messages']
                            ];
                            break;
                        case 401:
                            $result = [
                                'status' => WhatsAppConnection::RESULT_ACCESS_DENIED,
                                'result' => []
                            ];
                            break;
                        default:
                            $data_json = implode($data_json);
                            throw new Exception("[obtenerEstadoMensajes] Error desconocido en el WebService, Consulte con el administrador: \n\tCodigo: $http_code \n\tMensaje: $data_json");
                    }

                } else {
                    $errorMessage = curl_error($ch);
                    throw new Exception("[obtenerEstadoMensajes] Hubo un error no se puede conectar al WebService ($errorMessage)");
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