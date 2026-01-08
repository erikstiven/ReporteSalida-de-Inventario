<?php

class EnvioCorreos
{
    public $empr_cod_empr   = 0;
    public $database        = 0;

    function __construct($database, $idempresa)
    {
        $this->database         = $database;

        $sql = "SELECT empr_cod_empr, empr_token_api
                FROM saeempr 
                WHERE empr_cod_empr = {$idempresa}";
        $result = $database->executeQuery($sql);

        foreach($result as $empresa){
            $this->empr_cod_empr =  $empresa["empr_cod_empr"];
            $this->empr_api_token =  $empresa["empr_token_api"];
        }

        
    }

    function enviarCorreoApi($html, $id_tipo, $correo)
    {
        
        try {

            $id_empresa = $this->empr_cod_empr;
            $empr_api_token = $this->empr_api_token;

            $sqlSmtp = "SELECT server, port, auth, 
                                config_email.user, pass, ssltls, 
                                mail 
                        from comercial.config_email
                        where id_empresa = $id_empresa and
                                id_tipo = $id_tipo";
            $result = $this->database->executeQuery($sqlSmtp);

            foreach($result as $data){
                $host       = $data["server"];
                $port       = $data["port"];
                $smtpauth   = $data["auth"];
                $userid     = $data["user"];
                $password   = $data["pass"];
                $smtpsecure = $data["ssltls"];
                $mailenvio  = $data["mail"];
            }

            if($smtpsecure=='S'||$smtpsecure=='ssl'){
                $smtpsecure='ssl';
            }else{
                $smtpsecure='tls';
            }

            $secure_type=$smtpsecure;

            $headers = array(
                "Content-Type:application/json",
                "Token-Api:$empr_api_token"
            );
        
            $data = array(
                "smtp_server" => $host.":".$port,
                "secure_type" => $secure_type,
                "username" => $userid,
                "password" => $password,
                "from_address" => $mailenvio,
                "to_address" =>  $correo,
                "title" => "Notificacion de sorteo",
                "content" => $html
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_CORREOS."/api/v1/correo/enviar");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15); 
            $respuesta = curl_exec($ch);
            $resultado = json_decode($respuesta,true);

            $mensaje = $resultado["msg"];
            $enviado = $resultado["result"];

            return $enviado;

           
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        

        return $result;
    }

}

?>