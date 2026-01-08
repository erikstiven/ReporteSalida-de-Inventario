<?php
//require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class EmailSend
{
    private $oIfx;
    public $empr_cod_empr = '';
    public $empr_nom_empr = '';
    public $empr_api_toke = '';

    function __construct($oIfx, $idempresa)
    {
        $this->oIfx = $oIfx;

        $sql = "SELECT empr_cod_empr, empr_nom_empr, empr_token_api
                    FROM saeempr 
                    WHERE empr_cod_empr = $idempresa";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $this->empr_cod_empr = $oIfx->f('empr_cod_empr');
                    $this->empr_nom_empr = $oIfx->f('empr_nom_empr');
                    $this->empr_api_toke = $oIfx->f('empr_token_api');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

    }

    function envio_correo_html_send($correo = '', $html_mensaje = '', $title_mensaje = '',  $idTipo = 20, $copia_email = "", $adj=array()) {

        $oIfx = $this->oIfx;
        $idEmpresa = $this->empr_cod_empr;


        $sqlSmtp = "select server, port, auth, 
    config_email.user, pass, ssltls, 
            mail 
            from comercial.config_email
            where id_empresa = $idEmpresa and
            id_tipo = $idTipo";
        if ($oIfx->Query($sqlSmtp)) {
            if ($oIfx->NumFilas() > 0) {
                $host = $oIfx->f('server');
                $port = $oIfx->f('port');
                $smtpauth = $oIfx->f('auth');
                $userid = $oIfx->f('user');
                $smtpsecure = $oIfx->f('ssltls');
                $mailenvio = $oIfx->f('mail');
                $password = $oIfx->f('pass');
            }
        }
        $oIfx->Free();

        if($smtpsecure=='S'||$smtpsecure=='ssl'){
            $smtpsecure='ssl';
        }
        else{
            $smtpsecure='tls';
        }


        $secure_type=$smtpsecure;
        $sHtml = "<div style='width: 900px;'>".$html_mensaje."</div>";

        $headers = array(
            "Content-Type:application/json",
            "Token-Api:$this->empr_api_toke"
        );

        $correocc = array();

        $rescorreo=trim($correo);
        $correos_destino = array();
        $correos_explode = explode(';',$correo);
        foreach ($correos_explode as $correo_exp){
            array_push($correos_destino, trim($correo_exp));
        }

        if($copia_email){
            array_push( $correocc,$copia_email);
        }



        $data = array(
            "smtp_server" => $host.":".$port,
            "secure_type" => $secure_type,
            "username" => $userid,
            "password" => $password,
            "from_address" => $mailenvio,
            "to_address" =>  $correos_destino,
            "to_cc" =>  $correocc,
            "title" => $title_mensaje,
            "content" => $sHtml,
            "attachments" => $adj,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS_CORREOS."/api/v1/correo/enviar");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta = curl_exec($ch);
        $resultado = json_decode($respuesta,true);

        if($resultado){
            $mensaje = $resultado["msg"];
            $enviado = $resultado["result"];

            if($enviado == true){
                return [True, 'Mail Enviado'];
            }else{
                return [False, $mensaje];
            }
        }else{
            return [False, 'No se envio el correo'];
        }

    }

}

?>