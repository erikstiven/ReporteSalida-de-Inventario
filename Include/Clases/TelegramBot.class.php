<?php

include_once(dirname(__FILE__) . '/IntegracionesComerciales.class.php');


class TelegramBot
{
    private $oIfx;
    private $oCon;

    public $test_token = "7933735849:AAHNST2KSZi3-5D0d-_JM9fqKTVg8u-dq_M";

    public $nombre_integracion = 'Telegram_bot';
    public $ambiente = 'produccion';
    public $pais_codigo_inter = '';
    public $idempresa = '';
    public $idusuario = '';


    public $id_integracion = '';
    public $url_api = '';
    public $request_autorizacion = '';
    public $token = '';
    private $descripcion = 'Integracion para enviar los mensajes a los canales de telegram';
    private $estado = 'S';

    private $botToken = '7933735849:AAHNST2KSZi3-5D0d-_JM9fqKTVg8u-dq_M';
    // private $channelId = ['-1002357373062','-4236382640']; // You can also use the numeric ID
    private $channelId = ['-1002357373062']; // You can also use the numeric ID
    private $endpoint = "/sendMessage";
    private $url_bot = "https://api.telegram.org/bot";
    private $tipo_api = 'Rest';
    private $integracion;

    

    function __construct($oIfx, $oCon, $idempresa = 0,$idusuario = 0)
    {
        $this->oIfx = $oIfx;
        $this->oCon = $oCon;
        $this->idempresa = $idempresa;

        $this->integracion = new IntegracionComercial($oIfx, $oCon, $idempresa,$idusuario);


        $telegram_integracion = $this->integracion->obtener_integracion($idempresa,$this->nombre_integracion,$this->ambiente,'*');
        
        if(empty($telegram_integracion)){

            $inyection_sql = array("update"=>
                                        array(
                                            array(
                                            "column"=>"parametro_request1",
                                            "data"=>$this->endpoint
                                        )
                                    ),

                                    "insert"=> 
                                        array(
                                            array(
                                            "column"=>"parametro_request1",
                                            "data"=>$this->endpoint
                                        )
                                    )
                                );
            $url_api = $this->url_bot;

            $process_result = $this->integracion->registrar_integracion(
                                            0,
                                            $idempresa,
                                            $this->estado,
                                            $this->nombre_integracion,
                                            $this->descripcion,
                                            'pruebas',
                                            $url_api,
                                            $this->tipo_api,
                                            '',
                                            '',
                                            $usuario,
                                            '',
                                            $this->botToken,
                                            'N',
                                            '',
                                            '',
                                            '',
                                            $inyection_sql

                                    );

        }

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

    private function api_request_telegram($url_bot, $channelId, $message) {
    
        $data = http_build_query(array(
            'chat_id' => $channelId,
            'text' => $message,
        ));   

        $url_bot = $url_bot.($data?'?'.$data:'');
        $ch = curl_init($url_bot);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        return json_decode($response, true);
    }

    public function send_message_to_telegram($message,$channelId = []){
        $channelId_array = empty($channelId)?$this->channelId:$channelId; // You can also use the numeric ID
        $column_target = "estado_sn,id,descripcion,nombre_integracion,ambiente,url_api,request_autorizacion,tipo_api,token,parametro_request1";

        $telegram_integracion = $this->integracion->obtener_integracion($this->idempresa,$this->nombre_integracion,$this->ambiente,$column_target);
        if(empty($telegram_integracion)){
            $telegram_integracion = $this->integracion->obtener_integracion($this->idempresa,$this->nombre_integracion,'pruebas',$column_target);
        }
        $send_response = [];
        foreach ($telegram_integracion as $key => $telegram_indi) {

            $id_integracion_telegram        = $telegram_indi['id'];
            $ambiente_telegram              = $telegram_indi['ambiente'];
            $parametro_request1_telegram    = $telegram_indi['parametro_request1']; // endpoint telegram 
            $token_telegram                 = $telegram_indi['token']; 
            $url_api_telegram               = $telegram_indi['url_api'];          

            $telegram_integracion_config = $this->integracion->obtener_integracion_config($id_integracion_telegram,0,$this->idempresa,'*');

            if(empty($telegram_integracion_config) && $ambiente_telegram == 'pruebas'){
                foreach ($this->channelId as $key => $channel_id_indi) {

                    $this->integracion->registrar_integracion_config(
                        $id_integracion_telegram,
                        $this->idempresa,
                        0,
                        "channel_id",
                        "$channel_id_indi",
                        ""
                    );


                }
                $telegram_integracion_config = $this->integracion->obtener_integracion_config($id_integracion_telegram,0,$this->idempresa,'*');
            }


            foreach ($telegram_integracion_config as $key => $integration_config_indi) {
                $apiUrl = $url_api_telegram.$token_telegram.$parametro_request1_telegram;
                $clave = $integration_config_indi['clave'];
                if($clave=='channel_id' && !empty($apiUrl) && !empty($clave)){
                    $valor = $integration_config_indi['valor'];
                    array_push($send_response, $this->api_request_telegram($apiUrl, $valor, $message));
                }
                
            }
        }

        return $send_response;
        
    }

}

?>