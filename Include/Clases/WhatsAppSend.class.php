<?php
//require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class WhatsAppSend
{
    private $oIfx;

    public $empr_cod_empr = '';
    public $empr_nom_empr = '';
    public $empr_whatsapp_sn = 0;
    public $empr_whatsapp_url = '';

    function __construct($oIfx, $idempresa)
    {
        $this->oIfx = $oIfx;

        $sql = "SELECT empr_cod_empr, empr_nom_empr, empr_whatsapp_sn, empr_whatsapp_url
                    FROM saeempr 
                    WHERE empr_cod_empr = $idempresa";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {

                    $this->empr_cod_empr = $oIfx->f('empr_cod_empr');
                    $this->empr_nom_empr = $oIfx->f('empr_nom_empr');
                    $this->empr_whatsapp_sn = $oIfx->f('empr_whatsapp_sn');
                    $this->empr_whatsapp_url = $oIfx->f('empr_whatsapp_url');

                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

    }

    function verificarSessionWhatsApp()
    {
        if($this->empr_whatsapp_sn){

            try {

                $headers = array(
                    "Content-Type:application/json"
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, "$this->empr_whatsapp_url/lead/verificar");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $respuesta = curl_exec($ch);

                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    switch ($http_code) {
                        case 200:
                            $validacion = $data_json['responseExSave'];

                            $result = array(
                                'result' => $validacion
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

        }else{
            throw new Exception('No tiene activo el envio de WhatsApp');
        }

        return $result;
    }

    function enviarMensajeWhatsApp($telefono, $mensaje)
    {
        if($this->empr_whatsapp_sn){

            try {

                $data = array(
                    'phone' => $telefono,
                    'message' => $mensaje
                );

                $headers = array(
                    "Content-Type:application/json"
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, "$this->empr_whatsapp_url/lead/mensaje");
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
                            $validacion = $data_json['responseExSave'];

                            if(isset($validacion['id'])){
                                $validacion = 'mensaje enviado, id:'.$validacion['id'];
                            }else if(isset($validacion['error'])){
                                throw new Exception($validacion['error']);
                            }else{
                                throw new Exception('No se pudo enviar el mensaje, Intente de nuevo');
                            }

                            $result = array(
                                'result' => $validacion
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

        }else{
            throw new Exception('No tiene activo el envio de WhatsApp');
        }

        return $result;
    }

    function enviarAdjuntoWhatsApp($telefono, $nombre_archivo, $archivo)
    {
        if($this->empr_whatsapp_sn){

            try {

                $data = array(
                    'phone' => $telefono,
                    'nombre_archivo' => $nombre_archivo,
                    'archivo' => $archivo
                );

                $headers = array(
                    "Content-Type:application/json"
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, "$this->empr_whatsapp_url/lead/adjunto");
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
                            $validacion = $data_json['responseExSave'];

                            if(isset($validacion['id'])){
                                $validacion = 'mensaje enviado, id:'.$validacion['id'];
                            }else if(isset($validacion['error'])){
                                throw new Exception($validacion['error']);
                            }else{
                                throw new Exception('No se pudo enviar el mensaje, Intente de nuevo');
                            }

                            $result = array(
                                'result' => $validacion
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

        }else{
            throw new Exception('No tiene activo el envio de WhatsApp');
        }

        return $result;
    }

}

?>