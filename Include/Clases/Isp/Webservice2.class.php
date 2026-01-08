<?php

include_once('../../config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE).'comun.lib.php');

// Definiciones
global $DSN;

session_start();

if (class_exists('DboWs')) {
    $oConIsp = new DboWs; //CONEXION A BASE DEL WEBSERVICE
    $oConIsp->DSN = $DSN_API_ISP;
    $oConIsp->Conectar();
}

$oCon = new Dbo;
$oCon->DSN = $DSN;
$oCon->Conectar();

try {
    
    $id_empresa     = $_SESSION["U_EMPRESA"];
    $id_sucursal    = $_SESSION["U_SUCURSAL"];

    session_write_close(); //ESTA FUNCION INPIDE QUE SE BLOQUEEN LOS DEMAS PROCESOS PHP

    $endpoint       = $_POST["endpoint"];
    $timeout        = isset($_POST["timeout"]) ? intval($_POST["timeout"]) : 30;
    $datos_post     = $_POST["datos_post"];
    $datos_get      = $_POST["datos_get"];
    $tipo_sistema   = $_POST["tipo_sistema"];

    if(empty($tipo_sistema)){
        $tipo_sistema = 1;
    }    

    $sql   = "SELECT id_api FROM isp.int_parametros WHERE id_empresa = $id_empresa AND id_sucursal = $id_sucursal";
    $id_api = consulta_string_func($sql, 'id_api', $oCon, '');

    $sql   = "SELECT webservice_protocol, webservice_ip, webservice_port, webservice_token FROM isp.int_datos_webservice WHERE webservice_empresa = $id_empresa";
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {
                $protocolo  = $oCon->f('webservice_protocol');
                $ip         = $oCon->f('webservice_ip');
                $puerto     = $oCon->f('webservice_port');
                $token      = $oCon->f('webservice_token');
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();

    $url_ws = "$protocolo" . "://" . "$ip" . ":" . "$puerto";

    $sql = "SELECT url, metodo from isp.int_api_url WHERE id_api = $id_api AND comando_url = '$endpoint' AND id_sistema = $tipo_sistema";
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            $url    = $oCon->f('url');
            $metodo = $oCon->f('metodo');
        }
    }
    $oCon->Free();

    $headers = array(
        "Content-Type:application/json",
        "Authorization: Bearer " . $token
    );

    $sUrl = $url_ws . $url;

    if ($datos_get != NULL && $metodo == 'GET') {

        if (is_array($datos_get)) {
            // Verificar si alguno de los elementos contiene una barra inclinada
            $hayBarra = false;
            foreach ($datos_get as $dato) {
                if (strpos($dato, '/') !== false) {
                    $hayBarra = true;
                    break;
                }
            }
        
            // Realizar el implode solo si no hay barras
            if (!$hayBarra) {
                $datos_get = implode("/", $datos_get);
            }
        } 

        $sUrl .= '/' . $datos_get;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_URL, $sUrl);
    if ($datos_post != NULL && $metodo == "POST") {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos_post));
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 

    // Manejar excepciones cURL
    $respuesta = curl_exec($ch);

    // Verificar si hubo un error de cURL
    if ($respuesta === false) {
        $error_code = curl_errno($ch);
        $error_msn = "Error en la ejecución de cURL: " . curl_error($ch);

        $error_msn = str_replace("'", "", $error_msn);

        $valor = '';
        $sql = "SELECT valor FROM catalogo_errores where clave = '$error_msn'";
        if ($oConIsp->Query($sql)) {
            if ($oConIsp->NumFilas() > 0) {
                do {
                    $valor     = $oConIsp->f('valor');
                } while ($oConIsp->SiguienteRegistro());
            }
        }
        $oConIsp->Free();

        if (!empty($valor)) { 
            $error_msn = $valor;
        }

        throw new Exception($error_msn);
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Verificar el código de respuesta HTTP
    if ($http_code >= 400) {
        $error = "Error HTTP: $http_code $respuesta";

        $error = str_replace("'", "", $error);

        $valor = '';
        $sql = "SELECT valor FROM catalogo_errores where clave = '$error'";
        if ($oConIsp->Query($sql)) {
            if ($oConIsp->NumFilas() > 0) {
                do {
                    $valor     = $oConIsp->f('valor');
                } while ($oConIsp->SiguienteRegistro());
            }
        }
        $oConIsp->Free();

        if (!empty($valor)) { 
            $error = $valor;
        }

        throw new Exception($error);
    }

    curl_close($ch);

    echo $respuesta;
} catch (Exception $e) {
    // Devolver el código de error
    http_response_code($e->getCode());
    echo $e->getMessage();
}
?>
