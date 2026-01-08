<?php
//require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class SMSSend
{
    private $oIfx;

    public $empr_cod_empr = '';
    public $empr_nom_empr = '';
    public $empr_sms_sn = 1;
    public $empr_sms_url = '';
    public $empr_sms_token = '';
    public $empr_sms_key = '';
    public $empr_sms_tipo = 1;
    public $empr_sms_remitente = 'API SISCONTI';

    function __construct($oIfx, $idempresa)
    {
        $this->oIfx = $oIfx;

        $sql = "SELECT empr_cod_empr, empr_nom_empr, empr_sms_sn, empr_sms_token, empr_sms_url, empr_sms_key, empr_sms_tipo, empr_sms_remitente
                    FROM saeempr 
                    WHERE empr_cod_empr = $idempresa";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {

                    $this->empr_cod_empr = $oIfx->f('empr_cod_empr');
                    $this->empr_nom_empr = $oIfx->f('empr_nom_empr');
                    $this->empr_sms_sn = $oIfx->f('empr_sms_sn');
                    $this->empr_sms_token = $oIfx->f('empr_sms_token');
                    $this->empr_sms_url = $oIfx->f('empr_sms_url');
                    $this->empr_sms_key = $oIfx->f('empr_sms_key');
                    $this->empr_sms_tipo = $oIfx->f('empr_sms_tipo');
                    $this->empr_sms_remitente = $oIfx->f('empr_sms_remitente');

                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

    }

    function enviarMensajeSMS($telefono, $mensaje)
    {

        if($this->empr_sms_sn){
            if($this->empr_sms_tipo == 1){
                $result = $this->envioWsVonage($telefono, $mensaje);
            }else if($this->empr_sms_tipo == 2){
                $result = $this->envioWsInfobip($telefono, $mensaje);
            }else{
                throw new Exception('Tipo SMS no configurado');
            }

        }else{
            throw new Exception('No tiene activo el envio el envio SMS');
        }

        return $result;
    }

    function envioWsVonage($telefono, $mensaje){
        try {

            $data = array(
                'from' => "$this->empr_sms_remitente",
                'text' => "$mensaje",
                'to' => "$telefono",
                'api_key' => "$this->empr_sms_key",
                'api_secret' => "$this->empr_sms_token"
            );

            $headers = array(
                "Content-Type:application/json"
            );


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, "$this->empr_sms_url");
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
                        $mensaje_retorno = '';
                        $validacion = count($data_json['messages']);

                        if($validacion > 0 ){
                            $mensaje_retorno = 'mensaje enviado';
                        }else{
                            throw new Exception('No se pudo enviar el mensaje, Intente de nuevo');
                        }

                        $result = array(
                            'result' => $mensaje_retorno
                        );
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

    function envioWsInfobip($telefono, $mensaje){
        try {

            $data = array(
                'from' => 'API SISCONTI',
                'text' => "$mensaje",
                'to' => "$telefono"
            );


            $headers = array(
                "Content-Type: application/json",
                "Accept: application/json",
                'Authorization: Basic '.$this->empr_sms_key
            );


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, "$this->empr_sms_url/sms/1/text/single");
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
                        $mensaje_retorno = '';
                        $validacion = count($data_json['messages']);

                        if($validacion > 0 ){
                            $mensaje_retorno = 'mensaje enviado';
                        }else{
                            throw new Exception('No se pudo enviar el mensaje, Intente de nuevo');
                        }

                        $result = array(
                            'result' => $mensaje_retorno
                        );
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

}

?>