<?php

require_once(DIR_INCLUDE . 'comun.lib.php');

class Webservice
{

    var $oCon;

    function __construct($oCon)
    {
        $this->oCon = $oCon;
    }

    function parametrosWS()
    {

        session_start();

        $oCon = $this->oCon;
        $id_empresa     = $_SESSION["U_EMPRESA"];

        $data_parametros = array();

        $sql = "SELECT webservice_protocol, webservice_ip, webservice_port, webservice_token FROM isp.int_datos_webservice WHERE webservice_empresa = $id_empresa";

        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $protocolo  = $oCon->f('webservice_protocol');   
                $ip         = $oCon->f('webservice_ip');
                $puerto     = $oCon->f('webservice_port');
                $token      = $oCon->f('webservice_token');
            }
        }
        $oCon->Free();

        $url = "$protocolo"."://"."$ip".":"."$puerto";

        array_push($data_parametros, ["url" => $url, "token" => $token]);

        return $data_parametros;
    }

    function enviaComando($parametros,$envio_get,$envio_post)
    {

        $oCon = $this->oCon;

        $url_ws     = $parametros[0]["url"];
        $token      = $parametros[0]["token"];
        $id_api     = $parametros[1];
        $comando    = $parametros[2];
        $id_sistema = $parametros[3];

        $sql = "SELECT url, metodo from isp.int_api_url WHERE id_api = $id_api AND id_sistema = $id_sistema AND comando_url = '$comando'";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $url    = $oCon->f('url');
                $metodo = $oCon->f('metodo');
            }
        }
        $oCon->Free();

        if($id_api == 4){
            $headers = array(
                "Content-Type:application/json",
                "Authorization: Bearer ".$token
            );
        }else{
            $headers = array(
                "Content-Type:application/json"
            );
        }
       

        $sUrl = $url_ws . $url;

        if(!empty($envio_get) && $metodo == 'GET'){
            $sUrl .= '/'. $envio_get;
        }

        $envio_post = json_encode($envio_post);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $sUrl); 
        if($metodo == "POST"){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $envio_post);
        }       
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        $respuesta = curl_exec($ch); 
        $err     = curl_error($ch);
        curl_close($ch);

        if ($err){
            $data = curl_error($ch);
        }else{
            $data = json_decode($respuesta,true);
        }

        return $data;
    }

    function guardarLogEncabezado($encabezado_log)
    {
        $oCon = $this->oCon;

        $id_empresa                 = $encabezado_log["id_empresa"];
        $id_sucursal                = $encabezado_log["id_sucursal"];
        $id_usuario                 = $encabezado_log["id_usuario"];
        $tipo_accion                = $encabezado_log["tipo_accion"];
        $tipo_de_cr                 = $encabezado_log["tipo_de_cr"];
        $id_modulo                  = $encabezado_log["id_modulo"];
        $n_abonados_seleccionados   = $encabezado_log["n_abonados_seleccionados"];
        $n_abonados_afectados       = $encabezado_log["n_abonados_afectados"];
        $usa_api                    = $encabezado_log["usa_api"];
        $respuesta_api              = $encabezado_log["respuesta_api"];
        $fecha                      = $encabezado_log["fecha"];
        $fecha_server               = $encabezado_log["fecha_server"];
        $script_api                 = $encabezado_log["script_api"];
        $id_dispositivo             = $encabezado_log["id_dispositivo"];

        $script_api = str_replace("'", "", $script_api);
        $respuesta_api = str_replace("'", "", $respuesta_api);

        $sql = "INSERT INTO isp.int_audi_cr(id_empresa,             id_sucursal,            id_usuario,         tipo_accion,        tipo_de_cr,         id_modulo, 
                                        n_abonados_seleccionados,   n_abonados_afectados,   usa_api,            respuesta_api,      fecha,              fecha_server, 
                                        script_api,                 id_dispositivo)
                                VALUES($id_empresa,                 $id_sucursal,           $id_usuario,        $tipo_accion,       $tipo_de_cr,        $id_modulo,
                                        $n_abonados_seleccionados,  $n_abonados_afectados,  $usa_api,           '$respuesta_api',   '$fecha',           '$fecha_server',
                                        '$script_api',         $id_dispositivo)";
        $oCon->QueryT($sql);

        $sql = "SELECT MAX(id) as id_audi from isp.int_audi_cr";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $id_audi = $oCon->f('id_audi');
            }
        }
        $oCon->Free();

        return $id_audi;
    }

    function guardarLogDetalle($detalle_log)
    {
        $oCon = $this->oCon;

        $id_audi_cr     = $detalle_log["id_audi_cr"];
        $id_contrato    = $detalle_log["id_contrato"];
        $fecha_server   = $detalle_log["fecha_server"];
        $script_api     = $detalle_log["script_api"];
        $respuesta_api  = $detalle_log["respuesta_api"];

        $script_api = str_replace("'", "", $script_api);
        $respuesta_api = str_replace("'", "", $respuesta_api);

        $sql = "INSERT INTO isp.int_audi_cr_det(id_audi_cr,     id_contrato,    fecha_server,       script_api,     respuesta_api) 
                                    VALUES($id_audi_cr,         $id_contrato,   '$fecha_server',    '$script_api',  '$respuesta_api')";
        $oCon->QueryT($sql);

        return "ok";
    }

}