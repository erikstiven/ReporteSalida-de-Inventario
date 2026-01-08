<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class OzmapAPI
{

    var $oConexion1;
    var $oConexion2;

    function __construct($oConexion1, $oConexion2)
    {
        $this->oConexion1 = $oConexion1;
        $this->oConexion2 = $oConexion2;
    }

    function source_information($idempresa)
    {
        // informacion de la api de Ozmap
        $oConexion1 = $this->oConexion1;
        $empr_ozmap_sn = '';
        $empr_ozmap_url = '';
        $empr_ozmap_pass = '';
        $empr_ozmap_user = '';
        $empr_ozmap_api_token = '';

        $sql_empr = "SELECT
                        empr_ozmap_sn,
                        empr_ozmap_url, 
                        empr_ozmap_pass, 
                        empr_ozmap_user, 
                        empr_ozmap_api_token 
                    FROM saeempr 
                    WHERE empr_cod_empr = $idempresa";


        if ($oConexion1->Query($sql_empr)) {
            if ($oConexion1->NumFilas() > 0) {
                do {

                    $empr_ozmap_sn          = trim($oConexion1->f('empr_ozmap_sn'));
                    $empr_ozmap_url         = trim($oConexion1->f('empr_ozmap_url'));
                    $empr_ozmap_pass        = trim($oConexion1->f('empr_ozmap_pass'));
                    $empr_ozmap_user        = trim($oConexion1->f('empr_ozmap_user'));
                    $empr_ozmap_api_token   = trim($oConexion1->f('empr_ozmap_api_token'));
                } while ($oConexion1->SiguienteRegistro());
            }
        }

        $oConexion1->Free();

        if ($empr_ozmap_sn == 'S' && !empty($empr_ozmap_url) && !empty($empr_ozmap_api_token)) {
            $info = array(
                "base_url" => "$empr_ozmap_url",
                "username" => "$empr_ozmap_user",
                "password" => "$empr_ozmap_pass",
                "main_tag" => "/api/v2",
                "endpoints" => array(
                    "boxes" => array(
                        "path" => "/boxes",
                        "descripcion" => "Endpoint para obtener las cajas",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"clients" => array(
                        "path" => "/clients",
                        "descripcion" => "Endpoint para obtener los clientes",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"users" => array(
                        "path" => "/users",
                        "descripcion" => "Endpoint para obtener los usuarios",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"points" => array(
                        "path" => "/points",
                        "descripcion" => "Endpoint para obtener los puntos",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"cables" => array(
                        "path" => "/cables",
                        "descripcion" => "Endpoint para obtener los cables",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"poles" => array(
                        "path" => "/poles",
                        "descripcion" => "Endpoint para obtener los postes",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"olts" => array(
                        "path" => "/olts",
                        "descripcion" => "Endpoint para obtener las OLTs",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"splitters" => array(
                        "path" => "/splitters",
                        "descripcion" => "Endpoint para obtener los divisores",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"switches" => array(
                        "path" => "/switches",
                        "descripcion" => "Endpoint para obtener los direccionadores/enrutadores",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"prospects" => array(
                        "path" => "/prospects",
                        "descripcion" => "Endpoint para obtener los clientes potenciales",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"connectors" => array(
                        "path" => "/connectors",
                        "descripcion" => "Endpoint para obtener los conectores",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"fusions" => array(
                        "path" => "/fusions",
                        "descripcion" => "Endpoint para obtener las fusiones",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"regions" => array(
                        "path" => "/regions",
                        "descripcion" => "Endpoint para obtener las regiones",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"pendencies" => array(
                        "path" => "/pendencies",
                        "descripcion" => "Endpoint para obtener los pendientes",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    ),"projects" => array(
                        "path" => "/projects",
                        "descripcion" => "Endpoint para obtener los proyectos",
                        "method" => "GET",
                        "param" => true,
                        "body" => false,
                        "token" => "$empr_ozmap_api_token",
                        "token_required" => true,
                        "version" => "1"

                    )
                )
            );
        }
        return $info;
    }

    function generar_json_registro_guia($data)    {
        return json_encode($data,true);
    }

    function api_request($url, $body, $token, $method)
    {

        // si el metodo es vacio por defecto resa get
        $array_response = array();
        $method = $method ? $method : "GET";

        $curl = curl_init();

        $header_array = array(
            'Accept: application/json',
            'Content-Type: application/json',
        );

        if (!empty($token)) {
            array_push($header_array, "Authorization: Bearer $token");
        }

        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
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
                    // throw new Exception("Error desconocido en el WebService Laarcourier, Consulte con el administrador (error $res_json)");
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

            // throw new Exception("Hubo un error no se puede conectar al WebService Laarcourier($res_json)");

        }

        curl_close($curl);

        return json_encode($array_response);
    }

    function consulta_api_laarcourier($endpoint_target, $json, $idEmpresa, $guia_laar_guia, $filtro_cod_ciud)
    {

        $oConexion1 = $this->oConexion1;

        $end_authenticate = 'authenticate';

        $api_info   = $this->source_information($idEmpresa);
        // echo($idEmpresa);exit;
        // var_dump($api_info);exit;
        $url_base   = $api_info['base_url'];
        $port       = $api_info['port'];
        $username   = $api_info['username'];
        $password   = $api_info['password'];

        $endpoint_path_auth     = $api_info['endpoints'][$end_authenticate]['path'];
        $endpoint_method_auth   = $api_info['endpoints'][$end_authenticate]['method'];
        $endpoint_param_auth    = $api_info['endpoints'][$end_authenticate]['param'];
        $endpoint_body_auth     = $api_info['endpoints'][$end_authenticate]['body'];
        $endpoint_token_auth    = $api_info['endpoints'][$end_authenticate]['token'];
        // $endpoint_token_required_auth   = $api_info['endpoints'][$end_authenticate]['token_required'];

        // var_dump($api_info);exit;

        $endpoint_path      = $api_info['endpoints'][$endpoint_target]['path'];
        $endpoint_method    = $api_info['endpoints'][$endpoint_target]['method'];
        $endpoint_param     = $api_info['endpoints'][$endpoint_target]['param'];
        $endpoint_body      = $api_info['endpoints'][$endpoint_target]['body'];
        $endpoint_token     = $api_info['endpoints'][$endpoint_target]['token'];
        $endpoint_token_required    = $api_info['endpoints'][$endpoint_target]['token_required'];

        // $api_info_json   = json_encode($api_info);
        // $api_info   = json_decode($api_info);

        $url_auth = $url_base . $endpoint_path_auth;
        $url = $url_base . $endpoint_path;

        $body_auth = json_encode(array("username" => $username, "password" => $password));
        $token_auth = "";

        $body = "";
        $token = "";

        $response_aut_inicial = json_decode($this->api_request($url_auth, $body_auth, $token_auth, $endpoint_method_auth), true);
        $response_auth = $response_aut_inicial['http_code'] == '200' ? $response_aut_inicial['data'] : "";
        // var_dump($response_auth['nombre']);exit;

        $token          = $endpoint_token_required ? $response_auth['token'] : "";
        $nombre         = $response_auth['nombre'];
        $ruc            = $response_auth['ruc'];
        $codigoUsuario  = $response_auth['codigoUsuario'];
        $codigoSucursal = $response_auth['codigoSucursal'];

        // $end = ['productos','ciudades','guias_contado','guias_v2','anular_guia'];

        $response = '';




        switch ($endpoint_target) {
            case 'authenticate':
                $response = json_decode($this->api_request($url, $body, $token, $endpoint_method), true);
                $response = $response['http_code'] == '200' ? json_encode($response['data']) : "";
                // echo $response;exit;
                break;

            case 'productos':
                $response   = json_decode($this->api_request($url, $body, $token, $endpoint_method), true);
                // echo $response;exit;

                $response = $response['http_code'] == '200' ? json_encode($response['data']) : "";

                $response = str_replace(".0,", ',', $response);

                $producto_data  = json_decode($response, true);

                $array_prod     = array();
                // var_dump($producto_data);exit;

                for ($i = 0; $i < sizeof($producto_data) - 1; $i++) {

                    $codigo             = $producto_data[$i]['codigo'];
                    $nombre             = $producto_data[$i]['nombre'];

                    $producto_data[$i]['codigo'] = $codigo;
                    $producto_data[$i]['nombre'] = $nombre;
                    array_push($array_prod, $producto_data[$i]);
                }

                $response   = json_encode($array_prod, true, JSON_UNESCAPED_SLASHES);


                break;

            case 'ciudades':

                $response   = json_decode($this->api_request($url, $body, $token, $endpoint_method), true);

                $response = $response['http_code'] == '200' ? json_encode($response['data']) : "";

                $response = str_replace(".0,", ',', $response);


                $ciudad_data    = json_decode($response, true, JSON_UNESCAPED_SLASHES);
                // echo( $url);
                // echo( $body);
                // echo( $token);
                // echo( $endpoint_target);exit;


                $array_ciud = array();

                    $last_cod_prov = 0;
                    $last_cod_ciud = 0;
                    
                    for ($i=0; $i < sizeof($ciudad_data)-1; $i++) { 
                        $codigo             = $ciudad_data[$i]['codigo'];
                        // $nombre             = $ciudad_data[$i]['nombre'];//nombre ciudad
                        // $nombre             = explode(' (',$ciudad_data[$i]['nombre']);//nombre ciudad
                        $nombre             = $this->BBVASpecialCharConvertion($ciudad_data[$i]['nombre']);//nombre ciudad
                        $trayecto           = $ciudad_data[$i]['trayecto'];
                        $provincia          = $ciudad_data[$i]['provincia'];//nombre provincia
                        $provincia          = $this->BBVASpecialCharConvertion($ciudad_data[$i]['provincia']);//nombre provincia
                        $codigoProvincia    = $ciudad_data[$i]['codigoProvincia'];
                        $codigor            = $ciudad_data[$i]['codigor'];

                        

                    $ciud_cod_ciud = '';
                    $prov_cod_prov = '';
                    $pais_cod_pais = '';





                    $filtro = "";
                    if (!empty($filtro_cod_ciud)) {
                        $filtro = " and ciud_cod_ciud in ('$filtro_cod_ciud')";
                    }

                    $sql = "SELECT 
                                    ciud_cod_ciud, 
                                    ciud_cod_pais,
                                    ciud_nom_ciud as ciud_nom_ciud,
                                    prov_cod_prov,
                                    prov_des_prov,
                                    pais_cod_pais,
                                    pais_des_pais, 
                                    pais_des_naci,
                                    pais_cod_inte, 
                                    pais_codigo_inter 
                                from saeciud ciud
                                INNER JOIN saeprov prov on ciud.ciud_cod_prov = prov.prov_cod_prov
                                INNER JOIN saepais pais on pais.pais_cod_pais = ciud_cod_pais
                                WHERE 
                                upper(ciud_nom_ciud) like upper('$nombre%') 
                                and upper(prov_des_prov) like upper('$provincia%') 
                                $filtro
                                limit 1";
                    if($nombre =='KM 41 QUEVEDO'){
                        // print_r($ciudad_data[$i]);exit;
                    }
                    // $sql = "SELECT 
                    //         ciud_cod_ciud, 
                    //         ciud_cod_pais,
                    //         ciud_nom_ciud as ciud_nom_ciud,
                    //         prov_cod_prov,
                    //         prov_des_prov,
                    //         pais_cod_pais,
                    //         pais_des_pais, 
                    //         pais_des_naci,
                    //         pais_cod_inte, 
                    //         pais_codigo_inter 
                    //     from saeciud ciud
                    //     INNER JOIN saeprov prov on ciud.ciud_cod_prov = prov.prov_cod_prov
                    //     INNER JOIN saepais pais on pais.pais_cod_pais = ciud_cod_pais
                    //     WHERE 
                    //     upper(ciud_nom_ciud) like upper('$nombre%') 
                    //     and upper(prov_des_prov) like upper('%$provincia%') 
                    //     $filtro
                    //     limit 1";

                    // if($nombre =='LA TRONCAL'){
                    //     $ciud_cod_ciud = '521';
                    // }


                    if ($oConexion1->Query($sql)) {
                        if ($oConexion1->NumFilas() > 0) {
                            do {

                                $ciud_cod_ciud      = $oConexion1->f('ciud_cod_ciud');
                                $ciud_cod_pais      = $oConexion1->f('ciud_cod_pais');
                                $ciud_nom_ciud      = $oConexion1->f('ciud_nom_ciud');
                                $prov_cod_prov      = $oConexion1->f('prov_cod_prov');
                                $prov_des_prov      = $oConexion1->f('prov_des_prov');
                                $pais_cod_pais      = $oConexion1->f('pais_cod_pais');
                                $pais_des_pais      = $oConexion1->f('pais_des_pais');
                                $pais_des_naci      = $oConexion1->f('pais_des_naci');
                                $pais_cod_inte      = $oConexion1->f('pais_cod_inte');
                                $pais_codigo_inter  = $oConexion1->f('pais_codigo_inter');


                                $ciudad_data[$i]['ciud_cod_ciud'] = $ciud_cod_ciud;
                                $ciudad_data[$i]['prov_cod_prov'] = $prov_cod_prov;
                                $ciudad_data[$i]['pais_cod_pais'] = $pais_cod_pais;
                            } while ($oConexion1->SiguienteRegistro());
                        } else {
                            // // solo para ecuador
                            // $ciud_cod_pais = '1';
                            // $prov_array = $this->get_prov($provincia);
                            // $tmp_cod_prov = $prov_array['prov_cod_prov'];

                            // if($last_cod_prov!=0){
                            //     $last_cod_prov = $last_cod_prov+1;
                            // }
                            // $last_cod_prov = $prov_array['prov_cod_prov'];
                            // $last_cod_ciud = 0;

                            // $ciud_array = $this->get_ciud($nombre,$last_cod_prov,$ciud_cod_pais);

                            // $ciudad_data[$i]['ciud_cod_ciud'] = $ciud_array['ciud_cod_ciud'];
                            // $ciudad_data[$i]['prov_cod_prov'] = $ciud_array['ciud_cod_prov'];
                            // $ciudad_data[$i]['pais_cod_pais'] = $ciud_cod_pais;
                            continue;
                        }
                    }

                    // if($nombre =='LA TRONCAL'){
                    //     $ciudad_data[$i]['ciud_cod_ciud'] = '521';
                    // }

                    $oConexion1->Free();


                    array_push($array_ciud, $ciudad_data[$i]);
                    // $array_ciud["$ciud_nom_ciud"]=$ciudad_data[$i];
                }

                // $ciud_array_full = array();
                // krsort($array_ciud);
                // foreach($array_ciud as $k => $value) {
                //     array_push($ciud_array_full,$value);
                // }

                $response   = json_encode($array_ciud, true, JSON_UNESCAPED_SLASHES);

                break;

            case 'registro_guia':
                // registra la guia en laarcourier
                if ($endpoint_body) {
                    // echo $json.'00000000000 ';

                    $response = json_decode($this->api_request($url, $json, $token, $endpoint_method), true);
                    $response = $response['http_code'] == '200' ? json_encode($response['data']) : "";


                    // echo $response;exit;

                }

                break;

            case 'consulta_guia':
                // proceso para consultar una guia a partir de un codigo
                $response = json_decode($this->api_request($url . $guia_laar_guia, $body, $token, $endpoint_method), true);
                $response = $response['http_code'] == '200' ? json_encode($response['data']) : "";

                break;

            case 'anular_guia':
                $response = json_decode($this->api_request($url . $guia_laar_guia, $body, $token, $endpoint_method), true);
                $http_code = $response['http_code'];
                $data = $response['data'];
                $response = $response['http_code'] == '200' ? json_encode($response['data']) : "";
                if ($response['http_code'] == '200') {
                    $guia_laar_data['guia_laar_est'] = 'ANULADO';
                } else {
                    $guia_laar_data['guia_laar_est'] = 'ERROR ANULACION ' . $http_code . ', ' . ($data['Message']);
                }
                $guia_laar_data['guia_laar_guia'] = $guia_laar_guia;
                $guia_laar_data['guia_cod_empr'] = $idEmpresa;
                $this->update_anular_laarcourier_saeguia($guia_laar_data);


                break;

            case 'anular_guia_v2':
                // $response = json_decode($this->api_request($url,$body,$token,$endpoint_method),true);
                // $response = $response['http_code']=='200'?json_encode($response['data']):"";

                break;
        }




        return $response;
    }

    function update_laarcourier_saeguia($guia_laar_data)
    {
        // var_dump($guia_laar_data);exit;
        $oConexion1 = $this->oConexion1;

        $mensaje  = '{"Actualizacion":';

        $guia_laar_guia = $guia_laar_data['guia_laar_guia'];
        $guia_laar_url = $guia_laar_data['guia_laar_url'];
        $guia_laar_zpl = $guia_laar_data['guia_laar_zpl'];
        $guia_cod_guia = $guia_laar_data['guia_cod_guia'];
        $guia_cod_empr = $guia_laar_data['guia_cod_empr'];
        $guia_est_guia = '';

        if (!empty($guia_laar_guia) && !empty($guia_laar_url)) {
            $guia_est_guia = "REGISTRADO";
        } else {
            $guia_est_guia = "ERROR";
        }

        try {
            $sql_guia = "update saeguia set
                        guia_laar_guia = '$guia_laar_guia',
                        guia_laar_url = '$guia_laar_url',
                        guia_laar_zpl = '$guia_laar_zpl',
                        guia_laar_est = '$guia_est_guia'
                    where
                    guia_cod_empr = '$guia_cod_empr'
                    and guia_cod_guia = '$guia_cod_guia'";

            // echo $sql_guia;exit;
            $oConexion1->QueryT($sql_guia);

            $mensaje = array(
                "estado" => $guia_est_guia == 'REGISTRADO' ? true : false,
                "mensaje" => "$guia_est_guia",
                "descripcion" => "Actualizacion completa"
            );
        } catch (Exception $e) {
            $mensaje = array(
                "estado" => false,
                "mensaje" => "$guia_est_guia" . ',' . $e->getMessage(),
                "descripcion" => $e->getMessage()
            );
        }

        return $mensaje;
    }


    function update_anular_laarcourier_saeguia($guia_laar_data)
    {
        // var_dump($guia_laar_data);exit;
        $oConexion1 = $this->oConexion1;

        $mensaje  = array();

        $guia_laar_guia = $guia_laar_data['guia_laar_guia'];
        $guia_laar_est = $guia_laar_data['guia_laar_est'];
        $guia_cod_empr = $guia_laar_data['guia_cod_empr'];

        try {
            $sql_guia = "update saeguia set
                        guia_laar_est = '$guia_laar_est'
                    where
                    guia_cod_empr = '$guia_cod_empr'
                    and guia_laar_guia = '$guia_laar_guia'
                    ";

            $oConexion1->QueryT($sql_guia);


            $mensaje = array(
                "estado" => $guia_laar_est == 'ANULADO' ? true : false,
                "mensaje" => "$guia_laar_est",
                "descripcion" => "Actualizacion completa"
            );
        } catch (Exception $e) {
            $mensaje = array(
                "estado" => false,
                "mensaje" => "$guia_laar_est" . ',' . $e->getMessage(),
                "descripcion" => $e->getMessage()
            );
        }

        return $mensaje;
    }

    function obtener_ciudad($idEmpresa, $ciud_cod_ciud)
    {
        $ciudad_data =  json_decode($this->consulta_api_laarcourier('ciudades', '', $idEmpresa, '', $ciud_cod_ciud), true); //ciudades
        // $codigo = $ciudad_data[0]['codigo'];
        return  $ciudad_data;
    }

    function validate_information($data)
    {
        $complete = "Complete ";
        $descripcion_error = '';

        // de origen
        if (!$data['identificacionO']) {
            $descripcion_error .= $descripcion_error ? ", identificacionO" : $complete . "identificacionO";
        }
        if (!$data['ciudadO']) {
            $descripcion_error .= $descripcion_error ? ", ciudadO" : $complete . "ciudadO";
        }
        if (!$data['nombreO']) {
            $descripcion_error .= $descripcion_error ? ", nombreO" : $complete . "nombreO";
        }
        if (!$data['direccionO']) {
            $descripcion_error .= $descripcion_error ? ", direccionO" : $complete . "direccionO";
        }
        if (!$data['referenciaO']) {
            $descripcion_error .= $descripcion_error ? ", referenciaO" : $complete . "referenciaO";
        }
        if (!$data['numeroCasaO']) {
            $descripcion_error .= $descripcion_error ? ", numeroCasaO" : $complete . "numeroCasaO";
        }
        if (!$data['postalO']) {
            $descripcion_error .= $descripcion_error ? ", postalO" : $complete . "postalO";
        }
        if (!$data['telefonoO']) {
            $descripcion_error .= $descripcion_error ? ", telefonoO" : $complete . "telefonoO";
        }
        if (!$data['celularO']) {
            $descripcion_error .= $descripcion_error ? ", celularO" : $complete . "celularO";
        }
        // de destino
        if (!$data['identificacionD']) {
            $descripcion_error .= $descripcion_error ? ", identificacionD" : $complete . "identificacionD";
        }
        if (!$data['ciudadD']) {
            $descripcion_error .= $descripcion_error ? ", ciudadD" : $complete . "ciudadD";
        }
        if (!$data['direccionD']) {
            $descripcion_error .= $descripcion_error ? ", direccionD" : $complete . "direccionD";
        }
        if (!$data['referenciaD']) {
            $descripcion_error .= $descripcion_error ? ", referenciaD" : $complete . "referenciaD";
        }
        if (!$data['telefonoD']) {
            $descripcion_error .= $descripcion_error ? ", telefonoD" : $complete . "telefonoD";
        }
        if (!$data['celularD']) {
            $descripcion_error .= $descripcion_error ? ", celularD" : $complete . "celularD";
        }
        // generales
        // if (!$data['numeroGuia']){
        //     $descripcion_error .= $descripcion_error?", numeroGuia":$complete."numeroGuia";
        // }
        if (!$data['tipoServicio']) {
            $descripcion_error .= $descripcion_error ? ", tipoServicio" : $complete . "tipoServicio";
        }
        if (!$data['noPiezas']) {
            $descripcion_error .= $descripcion_error ? ", noPiezas" : $complete . "noPiezas";
        }
        if (!$data['peso']) {
            $descripcion_error .= $descripcion_error ? ", peso" : $complete . "peso";
        }
        // if (!$data['valorDeclarado']){
        //     $descripcion_error .= $descripcion_error?", valorDeclarado":$complete."valorDeclarado";
        // }
        if (!$data['contiene']) {
            $descripcion_error .= $descripcion_error ? ", contiene" : $complete . "contiene";
        }
        // if (!$data['cod']){
        //     $descripcion_error .= $descripcion_error?", cod":$complete."cod";
        // }
        // if (!$data['costoflete']){
        //     $descripcion_error .= $descripcion_error?", costoflete":$complete."costoflete";
        // }
        // if (!$data['costoproducto']){
        //     $descripcion_error .= $descripcion_error?", costoproducto":$complete."costoproducto";
        // }
        // if (!$data['tipocobro']){
        //     $descripcion_error .= $descripcion_error?", tipocobro":$complete."tipocobro";
        // }
        if (!$data['fechaPedido']) {
            $descripcion_error .= $descripcion_error ? ", fechaPedido" : $complete . "fechaPedido";
        }
        // if (!$data['Campo1']){
        //     $descripcion_error .= "Complete extras Campo1";
        // }
        // if (!$data['Campo2']){
        //     $descripcion_error .= "Complete extras Campo2";
        // }
        // if (!$data['Campo3']){
        //     $descripcion_error .= "Complete extras Campo3";
        // }


        if (!empty($descripcion_error)) {
            return array(
                "valido" => false,
                "mensaje" => "$descripcion_error"
            );
        } else {
            return array(
                "valido" => true,
                "mensaje" => "datos completos"
            );
        }
    }

    function anular_laar_guia($guia_laar_guia, $idEmpresa, $ciud_cod_ciud)
    {
        $anulacion_data =  json_decode($this->consulta_api_laarcourier('anular_guia', '', $idEmpresa, $guia_laar_guia, ''), true); //anulacion
        $codigo_guia    = $anulacion_data['guia'];
        $menssage_guia  = $anulacion_data['menssage'];
        $array_response = array(
            "mensaje" => $codigo_guia . ' fue ' . $menssage_guia
        );
        return   json_encode($array_response, true, JSON_UNESCAPED_SLASHES);
    }

    // private function add_new_ciud($ciud_cod_ciud,$new_ciud_nom_ciud, $prov_cod_prov,$ciud_cod_pais){
    //     $sql_insert_saeciud = "insert into saeciud 
    //                             (
    //                                 ciud_cod_ciud,
    //                                 ciud_cod_pais,
    //                                 ciud_nom_ciud,
    //                                 ciud_cod_prov
    //                                 ) 
    //                             values
    //                             ($ciud_cod_ciud,$ciud_cod_pais,'$new_ciud_nom_ciud',$prov_cod_prov)";

    //     $oConexion1 = $this->oConexion1;
    //     $oConexion1->QueryT($sql_insert_saeciud);         
    // }

    // private function add_new_prov($new_nom_prov,$prov_cod_prov){
    //     $sql_insert_saeprov = "insert into saeprov 
    //                             (
    //                                 prov_cod_prov,
    //                                 prov_des_prov
    //                                 ) 
    //                             values
    //                             ($prov_cod_prov,'$new_nom_prov')";

    //     $oConexion1 = $this->oConexion1;
    //     $oConexion1->QueryT($sql_insert_saeprov); 

    // }

    // private function get_prov($prov_des_prov){
    //     $sql_select_saeprov = "select prov_cod_prov from saeprov where prov_des_prov = '$prov_des_prov'";
    //     $prov_cod_prov = 0;
    //     $oConexion1 = $this->oConexion1;
    //     if ($oConexion1->Query($sql_select_saeprov)) {
    //         if ($oConexion1->NumFilas() > 0) {
    //             do {

    //                 $prov_cod_prov      = intval($oConexion1->f('prov_cod_prov'));
    //             } while ($oConexion1->SiguienteRegistro());
    //         }
    //     }
    //     $oConexion1->Free();

    //     if($prov_cod_prov==0){
    //         $sql_max = "select max(prov_cod_prov) as max_cod_prov from saeprov";
    //         $max_cod_prov = 0;
    //         if ($oConexion1->Query($sql_max)) {
    //             if ($oConexion1->NumFilas() > 0) {
    //                 do {

    //                     $prov_cod_prov      = intval($oConexion1->f('max_cod_prov'))+1;
    //                 } while ($oConexion1->SiguienteRegistro());
    //             }
    //         }

    //         $this->add_new_prov($prov_des_prov,$prov_cod_prov);
    //         $estado = 's';
    //     }else{
    //         $estado = 'n';
    //     }
    //     $oConexion1->Free();

    //     return array(
    //         "prov_cod_prov" => $prov_cod_prov,
    //         "prov_des_prov" => $prov_des_prov,
    //         "nuevo_sn" => $estado
    //     );

    // }

    // private function get_ciud($ciud_nom_ciud, $prov_cod_prov,$ciud_cod_pais){
    //     $sql_select_saeciud = "select ciud_cod_ciud from saeciud where ciud_nom_ciud = '$ciud_nom_ciud' and ciud_cod_prov = '$prov_cod_prov' and ciud_cod_pais = '$ciud_cod_pais'";
    //     $ciud_cod_ciud = 0;
    //     $oConexion1 = $this->oConexion1;
    //     if ($oConexion1->Query($sql_select_saeciud)) {
    //         if ($oConexion1->NumFilas() > 0) {
    //             do {

    //                 $ciud_cod_ciud      = intval($oConexion1->f('ciud_cod_ciud'));

    //             } while ($oConexion1->SiguienteRegistro());
    //         }
    //     }
    //     $oConexion1->Free();

    //     if($ciud_cod_ciud==0){
    //         $sql_max = "select max(ciud_cod_ciud) as max_cod_ciud from saeciud";
    //         $max_cod_ciud = 0;
    //         if ($oConexion1->Query($sql_max)) {
    //             if ($oConexion1->NumFilas() > 0) {
    //                 do {

    //                     $ciud_cod_ciud      = intval($oConexion1->f('max_cod_ciud'))+1;
    //                 } while ($oConexion1->SiguienteRegistro());
    //             }
    //         }

    //         $this->add_new_ciud($ciud_cod_ciud,$ciud_nom_ciud, $prov_cod_prov,$ciud_cod_pais);
    //         $estado = 's';
    //     }else{
    //         $estado = 'n';
    //     }
    //     $oConexion1->Free();

    //     return array(
    //         "ciud_cod_ciud" => $prov_cod_prov,
    //         "ciud_nom_ciud" => $ciud_nom_ciud,
    //         "ciud_cod_prov" => $ciud_cod_prov,
    //         "ciud_cod_pais" => $ciud_cod_pais,
    //         "nuevo_sn" =>"$estado"
    //     );

    // }

    // private function removeScpecialChat($strin_to_convert){
    //     $strin_to_convert       = eliminar_tildes($strin_to_convert);
    //     $strin_to_convert       = formatear_cadena($strin_to_convert);
    //     $strin_to_convert       = preg_replace('([^A-Za-z0-9 ])', '', $strin_to_convert);
    //     return $strin_to_convert;
    // }
    private function BBVASpecialCharConvertion($strin_to_convert)
    {
        $a = ['Ä', 'Á', 'À', 'Ã', 'Â'];
        $e = ['Ë', 'É', 'È', 'Ê'];
        $i = ['Ï', 'Í', 'Ì', 'Î'];
        $o = ['Ö', 'Ó', 'Ò', 'Õ', 'Ô'];
        $u = ['Ü', 'Ú', 'Ù', 'Û'];
        $n = ['±', 'Ñ'];
        $y = ['Ý'];

        $a2 = ['á', 'à', 'ã', 'â', 'ä'];
        $e2 = ['é', 'è', 'ê'];
        $i2 = ['í', 'ì', 'î', 'ï'];
        $o2 = ['ó', 'ò', 'õ', 'ô', 'ö'];
        $u2 = ['ú', 'ù', 'û', 'ü'];
        $n2 = ['ñ', '¥'];
        $y2 = ['ÿ', 'ý'];

        $d = ['Ð.'];
        $s1 = ['"', ';', ',', '+'];
        $s2 = ['!', '#', '.', '$', '%', '/', '(', "\\", '¡', '¿', mb_convert_encoding('&#xB4;', 'UTF-8', 'HTML-ENTITIES'), '~', '[', '}', ']', '`', '<', '>', '_', ')', '{', '^', ':', '|', '°', '¬', '=', '?', 'º'];

        $strin_to_convert = str_replace($a, "A", $strin_to_convert);
        $strin_to_convert = str_replace($e, "E", $strin_to_convert);
        $strin_to_convert = str_replace($i, "I", $strin_to_convert);
        $strin_to_convert = str_replace($o, "O", $strin_to_convert);
        $strin_to_convert = str_replace($u, "U", $strin_to_convert);
        $strin_to_convert = str_replace($n, "N", $strin_to_convert);
        $strin_to_convert = str_replace($y, "Y", $strin_to_convert);

        $strin_to_convert = str_replace($a2, "a", $strin_to_convert);
        $strin_to_convert = str_replace($e2, "e", $strin_to_convert);
        $strin_to_convert = str_replace($i2, "i", $strin_to_convert);
        $strin_to_convert = str_replace($o2, "o", $strin_to_convert);
        $strin_to_convert = str_replace($u2, "u", $strin_to_convert);
        $strin_to_convert = str_replace($n2, "n", $strin_to_convert);
        $strin_to_convert = str_replace($y2, "y", $strin_to_convert);

        $strin_to_convert = str_replace($d, "D", $strin_to_convert);
        $strin_to_convert = str_replace($s1, " ", $strin_to_convert);
        $strin_to_convert = str_replace($s2, " ", $strin_to_convert);

        // return removeScpecialChat($strin_to_convert);
        return $strin_to_convert;
    }
}
