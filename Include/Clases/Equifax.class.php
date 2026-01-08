<?php
require_once(DIR_INCLUDE . 'comun.lib.php');
require_once(DIR_INCLUDE . 'Clases/Contratos.class.php');

class Equifax
{


    public $url_ws = '';
    public $app_id = '';
    public $contraseña = '';

    function __construct()
    {
        //$this->empr_nom_empr = $empr_nom_empr; 		
    }

    /***************************************************
     * @ consultarParametrosEquipo
     * + Consulta parametros de equipo
     * + Retorna HTML
     **************************************************/
    function consultarXMLCovidCompleto($oIfxA, $idempresa, $tipo_doc, $identificacion)
    {

        $sql = "SELECT app_id, app_secret, url_ws
       FROM comercial.equifax_credentials 
       WHERE empresa_id = $idempresa";
        if ($oIfxA->Query($sql)) {
            if ($oIfxA->NumFilas() > 0) {
                do {
                    $this->app_id = $oIfxA->f('app_id');
                    $this->contraseña = $oIfxA->f('app_secret');
                    $this->url_ws = $oIfxA->f('url_ws');
                } while ($oIfxA->SiguienteRegistro());
            }
        }
        $oIfxA->Free();



        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url_ws,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '<?xml version="1.0" encoding="utf-8"?>
                                    <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                                    <soap:Header>
                                        <CabeceraCR xmlns="http://www.creditreport.ec/">
                                        <Usuario>' . $this->app_id . '</Usuario>
                                        <Clave>' . $this->contraseña . '</Clave>
                                        </CabeceraCR>
                                    </soap:Header>
                                    <soap:Body>
                                        <ObtenerReporte360 xmlns="http://www.creditreport.ec/">
                                        <tipoDocumento>' . $tipo_doc . '</tipoDocumento>
                                        <numeroDocumento>' . $identificacion . '</numeroDocumento>
                                        </ObtenerReporte360>
                                    </soap:Body>
                                    </soap:Envelope>',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);


        return $response;
    }

    function consultarXMLCovidCompletoPeru($oIfxA, $idempresa, $tipo_doc, $identificacion, $tipo_persona)
    {
        $sql = "SELECT app_id, app_secret, url_ws
       FROM comercial.equifax_credentials 
       WHERE empresa_id = $idempresa";
        if ($oIfxA->Query($sql)) {
            if ($oIfxA->NumFilas() > 0) {
                do {
                    $this->app_id = $oIfxA->f('app_id');
                    $this->contraseña = $oIfxA->f('app_secret');
                    $this->url_ws = $oIfxA->f('url_ws');
                } while ($oIfxA->SiguienteRegistro());
            }
        }
        $oIfxA->Free();

        $curl = curl_init();
        //var_dump($this->url_ws);exit;

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url_ws,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '<?xml version="1.0" encoding="utf-8"?>
                                        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                                        xmlns:end="http://ws.creditreport.equifax.com.pe/endpoint"
                                        xmlns:doc="http://ws.creditreport.equifax.com.pe/document">
                                        <soapenv:Header>
                                        <wsse:Security soapenv:mustUnderstand="1"
                                        xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
                                        xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurityutility-1.0.xsd">
                                        <wsse:UsernameToken>
                                        <wsse:Username>'.$this->app_id.'</wsse:Username>
                                        <wsse:Password
                                        Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile1.0#PasswordText">' . $this->contraseña . '</wsse:Password>
                                        </wsse:UsernameToken>
                                        </wsse:Security>
                                        </soapenv:Header>
                                        <soapenv:Body>
                                        <end:GetReporteOnline>
                                        <!--Optional:-->
                                        <doc:DatosConsulta>
                                        <TipoPersona>'.$tipo_persona.'</TipoPersona>
                                        <TipoDocumento>'.$tipo_doc.'</TipoDocumento>
                                        <NumeroDocumento>'.$identificacion.'</NumeroDocumento>
                                        <CodigoReporte>882</CodigoReporte>
                                        </doc:DatosConsulta>
                                        </end:GetReporteOnline>
                                        </soapenv:Body>
                                        </soapenv:Envelope>',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);


        return $response;
    }


    function ingresarEquifax(
        $tipo_documento,
        $documento,
        $nombre_sujeto,
        $score_actual,
        $score_6_meses,
        $score_12_meses,
        $actividad_sri,
        $ruc_sri,
        $estado_contribuyente,
        $clase_contribuyente,
        $codigoCiiu,
        $fecha_inicio_act,
        $fecha_fin_act,
        $numero_estab,
        $obligado_cont,
        $nombre_comercial,
        $fecha_score,
        $oCon
    ) {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userWeb = $_SESSION['U_ID'];
        $sucursal = $_SESSION['U_SUCURSAL'];
        $id_empresa = $_SESSION['U_EMPRESA'];


        $fechaServer = date("Y-m-d H:i:s");



        //valida direccion
        //if (empty($id_dire)) {
        //    $id_dire = 'null';
        //}

        $sql = "SELECT COUNT(*) as control, max(id) as id FROM comercial.consumo_equifax WHERE documento = '$documento' GROUP BY fecha_score ORDER BY fecha_score DESC   LIMIT 1;";

        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $control    = $oCon->f('control')?$oCon->f('control'):0;
                    $id         = $oCon->f('id');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        if($control>0){
            // update
            $sql = "UPDATE comercial.consumo_equifax
                    SET 
                    tipo_documento          = '$tipo_documento',
                    documento               = '$documento',
                    nombre_sujeto           = '$nombre_sujeto',
                    score_actual            = $score_actual,
                    score_6_meses           = $score_6_meses,
                    score_12_meses          = $score_12_meses,
                    actividad_sri           = '$actividad_sri',
                    ruc_sri                 = '$ruc_sri',
                    estado_contribuyente    = '$estado_contribuyente',
                    clase_contribuyente     = '$clase_contribuyente',
                    codigo_ciiu              = '$codigoCiiu',
                    fecha_inicio_act        = '$fecha_inicio_act',
                    fecha_fin_act           = '$fecha_fin_act',
                    numero_estab            = '$numero_estab',
                    obligado_cont           = '$obligado_cont',
                    nombre_comercial        = '$nombre_comercial',
                    usuario_reg             = $userWeb,
                    fecha_score             = '$fecha_score',
                    fecha_server             = '$fechaServer',
                    id_empresa              = '$id_empresa'

                    WHERE 
                        id = '$id' and documento = '$documento' ;";

        }else{
            // insert        

            $sql = "INSERT INTO comercial.consumo_equifax(
                                                    tipo_documento,         documento,              nombre_sujeto,
                                                    score_actual,          score_6_meses,          score_12_meses,         actividad_sri,
                                                    ruc_sri,               estado_contribuyente,   clase_contribuyente,    codigo_ciiu,
                                                    fecha_inicio_act,      fecha_fin_act,          numero_estab,           obligado_cont,
                                                    nombre_comercial,      usuario_reg,            fecha_score,            fecha_server,
                                                    id_empresa)

                                        values(     '$tipo_documento',      '$documento',               '$nombre_sujeto',
                                                    $score_actual,          $score_6_meses,             $score_12_meses,            '$actividad_sri',
                                                    '$ruc_sri',             '$estado_contribuyente',    '$clase_contribuyente',     '$codigoCiiu',
                                                    '$fecha_inicio_act',    '$fecha_fin_act',           '$numero_estab',            '$obligado_cont',
                                                    '$nombre_comercial',    $userWeb,                   '$fecha_score',             '$fechaServer',
                                                    '$id_empresa') ";
        }

        //echo $sql;exit;
        $oCon->QueryT($sql);

        return true;
    }


    function ingresarEquifaxPeru(
        $id_score_consulta,
        $fecha_consulta,
        $tipo_documento,
        $numeroDocumento,
        $direccion,
        $nombres_completos,
        $periodo_actual_score,
        $riesgo_actual_score,
        $periodo_anterior_score,
        $riesgo_anterior_score,
        $periodo_12_score,
        $riesgo_12_score,
        $tarjeta_credito,
        $linea_credito,
        $credito_hipotecario,
        $BuenPagadorDeServicios,
        $esta_infocorp,
        $gasto_mensual,
        $tiene_auto,
        $titulo_modulo,
        $puntaje_score,
        $nivel_riesgo,
        $conclusion_puntaje_riesgo,
        $oCon
    ) {

        if(empty($gasto_mensual)){
            $gasto_mensual=0;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userWeb = $_SESSION['U_ID'];
        $sucursal = $_SESSION['U_SUCURSAL'];
        $id_empresa = $_SESSION['U_EMPRESA'];
        
        $sql = "INSERT INTO comercial.consumo_equifax_peru
                (       id,                         fecha_consulta,         tipo_docu,              numero_documento,       direccion,
                         nombres_completos,         periodo_actual_score,   riesgo_actual_score,    periodo_anterior_score,
                         riesgo_anterior_score,     periodo_12_score,       riesog_12_score,        tarjeta_credito,
                         linea_de_credito,          credito_hipo,           buen_pagador_serv,      esta_en_infocorp,
                         gasto_mensual,             tiene_auto,             titulo_modulo,          puntaje_score,
                         nivel_riesgo,              conclusion,             id_empresa,             id_usuario)

             values(  $id_score_consulta,           '$fecha_consulta',           '$tipo_documento',      '$numeroDocumento',     '$direccion',
                       '$nombres_completos',        '$periodo_actual_score','$riesgo_actual_score', '$periodo_anterior_score',
                       '$riesgo_anterior_score',    '$periodo_12_score',    '$riesgo_12_score',     '$tarjeta_credito',
                       '$linea_credito',            '$credito_hipotecario', '$BuenPagadorDeServicios',    '$esta_infocorp',
                       '$gasto_mensual',             '$tiene_auto',          '$titulo_modulo',        '$puntaje_score',
                       '$nivel_riesgo',             '$conclusion_puntaje_riesgo',       $id_empresa,        $userWeb) ";
        //echo $sql;exit;
        $oCon->QueryT($sql);




        return true;
    }

    function consultarJSONCovidCompleto($oIfxA, $idempresa, $tipo_doc, $identificacion)
    {

        $sql = "SELECT app_id, app_secret, url_ws
       FROM comercial.equifax_credentials 
       WHERE empresa_id = $idempresa";
        if ($oIfxA->Query($sql)) {
            if ($oIfxA->NumFilas() > 0) {
                do {
                    $this->app_id = $oIfxA->f('app_id');
                    $this->contraseña = $oIfxA->f('app_secret');
                    $this->url_ws = $oIfxA->f('url_ws');
                } while ($oIfxA->SiguienteRegistro());
            }
        }
        $oIfxA->Free();

        $credentials  = array(
            "Username"=>$this->app_id,
            "Password"=>$this->contraseña
        );

        // $credentials  = array(
        //     "Username"=>"WSSERVITELCO",
        //     "Password"=>"Cisco2022*"
        // );
        
        $body_aut = json_encode($credentials,JSON_UNESCAPED_UNICODE);

        switch ($tipo_doc) {
            case 1: //cedula
                $tipo_doc   = 'C';
                $endpoint   = '/api/Rest360';
                break;

            case 4: //pasaporte
                $tipo_doc   = 'P';              
                $endpoint   = '/api/Rest360/IndexPymes';

                break;

            case 6: //ruc
                $tipo_doc   = 'R';                
                $endpoint   = '/api/Rest360/IndexPymes';
                break;

            case 'C': //cedula
                $endpoint   = '/api/Rest360';
                break;

            case 'P': //pasaporte        
                $endpoint   = '/api/Rest360/IndexPymes';

                break;

            case 'R': //ruc            
                $endpoint   = '/api/Rest360/IndexPymes';
                break;

        }

        $base_equifax_url   = $this->url_ws;
        // $base_equifax_url   = 'https://www.equifax.com.ec/Rest360Covid';

        $auth_ep            = '/api/login/authenticate';

        $url_aut = $base_equifax_url.$auth_ep;
        $url_cre = $base_equifax_url.$endpoint;

        $auth_data  = json_decode($this->api_request($url_aut, $body_aut, '', 'POST'),true, 512, JSON_UNESCAPED_UNICODE);

        $http_code  = $auth_data['http_code'];
        $data       = $auth_data['data'];
        $mensaje    = $auth_data['mensaje'];

        if($http_code =='200'){
            $token  = $data;

            $credentials_search  = array(
                "identificacion"=>
                array(
                    array(
                        "TipoDocumento"=>$tipo_doc,
                        "NumeroDocumento"=>$identificacion
                    )
                )        
            );

            $body_cre = json_encode($credentials_search,JSON_UNESCAPED_UNICODE);

            
            $result_cre = json_decode($this->api_request($url_cre, $body_cre, $token, 'POST'),true, 512, JSON_UNESCAPED_UNICODE);
        }else{
            $auth_data['mensaje'] .= $data['MensajeError']?': '.$data['MensajeError']:"";

            $result_cre = $auth_data;

        }

        return $result_cre;
    }

    function api_request($url, $body, $token, $method)
    {

        $array_response = array();

        // si el metodo es vacio por defecto setea a get
        $method = $method ? $method : "GET";

        $curl = curl_init();

        $header_array = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'charset=utf-8'
        );

        if (!empty($token)) {
            array_push($header_array, "Authorization: Bearer $token");
        }

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
            $resp = json_decode($resp, true, 512, JSON_UNESCAPED_UNICODE);
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

                    $res_json = json_encode($array_response,JSON_UNESCAPED_UNICODE);
                    break;
            }
        } else {

            $errorMessage = curl_error($curl);
            $array_response = array(
                "http_code" => '000',
                "data" => [],
                "mensaje" => $errorMessage,
            );
            $res_json = json_encode($array_response,JSON_UNESCAPED_UNICODE);
        }

        curl_close($curl);

        return json_encode($array_response,JSON_UNESCAPED_UNICODE);
    }


    // public function equifax_fake_respose(){
    //     $jso_data = '{
    //         "REPORTE": [
    //             {
    //                 "C\u00f3digo de Consulta": "307850704"
    //             }
    //         ],
    //         "IdentificacionConsultada": [
    //             {
    //                 "NombreSujeto": "FLORES CARDOSO EDGAR JOSSUE",
    //                 "TipoDocumentoDobleInfo": "C",
    //                 "NumeroDocumentoDobleInfo": "1728991462"
    //             }
    //         ],
    //         "Historico Score 360": [
    //             {
    //                 "Retroactivo": "Actual",
    //                 "Score": 958,
    //                 "Gmin": 869,
    //                 "Gmax": 999,
    //                 "Ymin": 415,
    //                 "Ymax": 868,
    //                 "Rmin": 1,
    //                 "Rmax": 414
    //             },
    //             {
    //                 "Retroactivo": "-6 Meses",
    //                 "Score": 923,
    //                 "Gmin": 869,
    //                 "Gmax": 999,
    //                 "Ymin": 415,
    //                 "Ymax": 868,
    //                 "Rmin": 1,
    //                 "Rmax": 414
    //             },
    //             {
    //                 "Retroactivo": "-12 Meses",
    //                 "Score": 919,
    //                 "Gmin": 869,
    //                 "Gmax": 999,
    //                 "Ymin": 415,
    //                 "Ymax": 868,
    //                 "Rmin": 1,
    //                 "Rmax": 414
    //             }
    //         ],
    //         "Analisis de Saldos por Vencer 360": [
    //             {
    //                 "PeriodoOperacion": "Actual",
    //                 "CantidadOperaciones": 2,
    //                 "SaldosXVencer": 283.19
    //             },
    //             {
    //                 "PeriodoOperacion": "1 a 3 meses",
    //                 "CantidadOperaciones": 2,
    //                 "SaldosXVencer": 409.77
    //             },
    //             {
    //                 "PeriodoOperacion": "3 a 6 meses",
    //                 "CantidadOperaciones": 2,
    //                 "SaldosXVencer": 492.78
    //             },
    //             {
    //                 "PeriodoOperacion": "6 a 12 meses",
    //                 "CantidadOperaciones": 1,
    //                 "SaldosXVencer": 551.48
    //             },
    //             {
    //                 "PeriodoOperacion": "+12 meses",
    //                 "CantidadOperaciones": 1,
    //                 "SaldosXVencer": 699.58
    //             }
    //         ],
    //         "Resumen Protestos y Morosidades 360": [
    //             {
    //                 "Morosidades": 0,
    //                 "Protestos": null,
    //                 "MontoTotalMorosidades": 0.0,
    //                 "TotalNumeroOperaciones": 0
    //             }
    //         ],
    //         "Historico Acreedores 360": [
    //             {
    //                 "Retroactivo": "Actual",
    //                 "Acredores": 2
    //             },
    //             {
    //                 "Retroactivo": "-6 Meses",
    //                 "Acredores": 2
    //             },
    //             {
    //                 "Retroactivo": "-12 Meses",
    //                 "Acredores": 1
    //             }
    //         ],
    //         "Historico Cuota Estimada 360": [
    //             {
    //                 "Orden": 3,
    //                 "Retroactivo": "Actual",
    //                 "CuotaMensual": 249.0
    //             },
    //             {
    //                 "Orden": 2,
    //                 "Retroactivo": "-6 Meses",
    //                 "CuotaMensual": 247.06
    //             },
    //             {
    //                 "Orden": 1,
    //                 "Retroactivo": "-12 Meses",
    //                 "CuotaMensual": 106.53
    //             },
    //             {
    //                 "Orden": 4,
    //                 "Retroactivo": "+6 Meses",
    //                 "CuotaMensual": 243.02
    //             },
    //             {
    //                 "Orden": 5,
    //                 "Retroactivo": "+12 Meses",
    //                 "CuotaMensual": 243.02
    //             }
    //         ],
    //         "Historico Endeudamiento Comercial 360": [
    //             {
    //                 "Retroactivo": "Actual",
    //                 "SaldoTotal": 0.0
    //             },
    //             {
    //                 "Retroactivo": "-6 meses",
    //                 "SaldoTotal": 0.0
    //             },
    //             {
    //                 "Retroactivo": "-12 meses",
    //                 "SaldoTotal": 0.0
    //             }
    //         ],
    //         "Historico Endeudamiento Financiero 360": [
    //             {
    //                 "Retroactivo": "Actual",
    //                 "SaldoTotal": 2436.8
    //             },
    //             {
    //                 "Retroactivo": "-6 meses",
    //                 "SaldoTotal": 3039.89
    //             },
    //             {
    //                 "Retroactivo": "-12 meses",
    //                 "SaldoTotal": 2681.99
    //             }
    //         ],
    //         "Historico Vencidos Comercial 360": [
    //             {
    //                 "Retroactivo": "Actual",
    //                 "SaldoVencido": 0.0
    //             },
    //             {
    //                 "Retroactivo": "-6 meses",
    //                 "SaldoVencido": 0.0
    //             },
    //             {
    //                 "Retroactivo": "-12 meses",
    //                 "SaldoVencido": 0.0
    //             }
    //         ],
    //         "Historico Vencidos Financiero 360": [
    //             {
    //                 "Retroactivo": "Actual",
    //                 "SaldoVencido": 0.0
    //             },
    //             {
    //                 "Retroactivo": "-6 meses",
    //                 "SaldoVencido": 0.0
    //             },
    //             {
    //                 "Retroactivo": "-12 meses",
    //                 "SaldoVencido": 0.0
    //             }
    //         ],
    //         "Informacion SRI 360": [
    //             {
    //                 "Nombre": "",
    //                 "Actividad": "ACTIVIDADES DE LIMPIABOTAS (BETUNEROS), PORTEADORES DE MALETAS, PERSONAS ENCARGADAS DE ESTACIONAR VEH\u00cdCULOS, ETC\u00c9TERA.",
    //                 "RUC": "1728991462001",
    //                 "Direccion": null,
    //                 "estadoContribuyente": "SUSPENDIDO",
    //                 "claseContribuyente": "RIMPE",
    //                 "codigoCiiu": "S960907",
    //                 "fechaInicioActividades": "2021-10-27T00:00:00",
    //                 "fechaSuspensionDefinitiva": "2022-08-20T00:00:00",
    //                 "numeroEstablecimiento": 1,
    //                 "obligado": "N",
    //                 "nombreFantasiaComercial": null
    //             }
    //         ],
    //         "Datos Resumen Informe 360": [
    //             {
    //                 "MontoTotalOpeImpagos": 0.0,
    //                 "NumTotalOpeImpagos": 0,
    //                 "Tipo": "OpeImpagos"
    //             },
    //             {
    //                 "MontoTotalOpeImpagos": 0.0,
    //                 "NumTotalOpeImpagos": 0,
    //                 "Tipo": "DemandaJudicial"
    //             },
    //             {
    //                 "MontoTotalOpeImpagos": 0.0,
    //                 "NumTotalOpeImpagos": 0,
    //                 "Tipo": "CarteraCastigada"
    //             }
    //         ],
    //         "PersonasInhabilitadas": [],
    //         "Mantiene historial crediticio desde": [
    //             {
    //                 "Titulo": "Mantiene historial Crediticio desde: ",
    //                 "PrimeraFecha": "2022-05-31T00:00:00"
    //             }
    //         ],
    //         "Identificador perfil riesgo directo desde 20051": [
    //             {
    //                 "Indicador": "Mayor plazo vencido",
    //                 "Valor": null,
    //                 "Fecha": null
    //             },
    //             {
    //                 "Indicador": "Mayor valor vencido",
    //                 "Valor": null,
    //                 "Fecha": null
    //             },
    //             {
    //                 "Indicador": "Endeudamiento promedio",
    //                 "Valor": "1912,32",
    //                 "Fecha": null
    //             }
    //         ],
    //         "Identificador perfil riesgo directo 6 meses2": [
    //             {
    //                 "Indicador": "Mayor plazo vencido",
    //                 "Valor": null,
    //                 "Fecha": null
    //             },
    //             {
    //                 "Indicador": "Mayor valor vencido",
    //                 "Valor": null,
    //                 "Fecha": null
    //             },
    //             {
    //                 "Indicador": "Endeudamiento promedio",
    //                 "Valor": "2543,91",
    //                 "Fecha": null
    //             }
    //         ],
    //         "Entidades que han consultado": [],
    //         "Recursivo Detalle distribucion endeudamiento Educativo 3600": [
    //             {
    //                 "FechaCorte": "2024-03-31T00:00:00",
    //                 "FechaCorteParam": "2024-03-31T00:00:00",
    //                 "CodigoInstitucionParam": 0,
    //                 "Institucion": "Sistema Financiero Regulado SB",
    //                 "TipoCreditoParam": "0",
    //                 "TipoCredito": "",
    //                 "SaldoDeuda": 2436.8,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "Titular": 1757.83,
    //                 "Garante": 0.0,
    //                 "Codeudor": 0.0,
    //                 "TarjetaCredito": 678.97,
    //                 "AcuerdoConcordatorio": " ",
    //                 "Detalle": "   ",
    //                 "ResaltadaInv": "N",
    //                 "Opcion": "CONS",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2024-03-31T00:00:00",
    //                 "FechaCorteParam": "2024-03-31T00:00:00",
    //                 "CodigoInstitucionParam": 1028,
    //                 "Institucion": "PACIFICO",
    //                 "TipoCreditoParam": "N",
    //                 "TipoCredito": "Consumo",
    //                 "SaldoDeuda": 1757.83,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "Titular": 1757.83,
    //                 "Garante": 0.0,
    //                 "Codeudor": 0.0,
    //                 "TarjetaCredito": 0.0,
    //                 "AcuerdoConcordatorio": null,
    //                 "Detalle": "Ver",
    //                 "ResaltadaInv": "N",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": null,
    //                 "FechaCorteParam": null,
    //                 "CodigoInstitucionParam": null,
    //                 "Institucion": "Total PACIFICO",
    //                 "TipoCreditoParam": " ",
    //                 "TipoCredito": "",
    //                 "SaldoDeuda": 1757.83,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "Titular": 1757.83,
    //                 "Garante": 0.0,
    //                 "Codeudor": 0.0,
    //                 "TarjetaCredito": 0.0,
    //                 "AcuerdoConcordatorio": " ",
    //                 "Detalle": "   ",
    //                 "ResaltadaInv": "S",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2024-03-31T00:00:00",
    //                 "FechaCorteParam": "2024-03-31T00:00:00",
    //                 "CodigoInstitucionParam": 1029,
    //                 "Institucion": "PICHINCHA",
    //                 "TipoCreditoParam": "N",
    //                 "TipoCredito": "Consumo",
    //                 "SaldoDeuda": 678.97,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "Titular": 0.0,
    //                 "Garante": 0.0,
    //                 "Codeudor": 0.0,
    //                 "TarjetaCredito": 678.97,
    //                 "AcuerdoConcordatorio": null,
    //                 "Detalle": "Ver",
    //                 "ResaltadaInv": "N",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": null,
    //                 "FechaCorteParam": null,
    //                 "CodigoInstitucionParam": null,
    //                 "Institucion": "Total PICHINCHA",
    //                 "TipoCreditoParam": " ",
    //                 "TipoCredito": "",
    //                 "SaldoDeuda": 678.97,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "Titular": 0.0,
    //                 "Garante": 0.0,
    //                 "Codeudor": 0.0,
    //                 "TarjetaCredito": 678.97,
    //                 "AcuerdoConcordatorio": " ",
    //                 "Detalle": "   ",
    //                 "ResaltadaInv": "S",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": null,
    //                 "FechaCorteParam": null,
    //                 "CodigoInstitucionParam": null,
    //                 "Institucion": "Total Deuda USD",
    //                 "TipoCreditoParam": " ",
    //                 "TipoCredito": "",
    //                 "SaldoDeuda": 2436.8,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "Titular": 1757.83,
    //                 "Garante": 0.0,
    //                 "Codeudor": 0.0,
    //                 "TarjetaCredito": 678.97,
    //                 "AcuerdoConcordatorio": " ",
    //                 "Detalle": "   ",
    //                 "ResaltadaInv": "S",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             }
    //         ],
    //         "Recursivo Composicion estructura de vencimiento 3600": [
    //             {
    //                 "FechaCorte": "2024-03-31T00:00:00",
    //                 "Institucion": "Sistema Financiero Regulado SB",
    //                 "PorVencer": 2436.8,
    //                 "Vencido": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "SaldoDeuda": 2436.8,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "CodigoInstitucionParam": 1028,
    //                 "AcuerdoConcordatorio": "",
    //                 "InstitucionParam": "SB",
    //                 "Opcion": "CONS",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2024-03-31T00:00:00",
    //                 "Institucion": "PACIFICO                                                    ",
    //                 "PorVencer": 1757.83,
    //                 "Vencido": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "SaldoDeuda": 1757.83,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "CodigoInstitucionParam": 1028,
    //                 "AcuerdoConcordatorio": "",
    //                 "InstitucionParam": "PACIFICO                                                    ",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2024-03-31T00:00:00",
    //                 "Institucion": "PICHINCHA                                                   ",
    //                 "PorVencer": 678.97,
    //                 "Vencido": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "SaldoDeuda": 678.97,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "CodigoInstitucionParam": 1029,
    //                 "AcuerdoConcordatorio": "",
    //                 "InstitucionParam": "PICHINCHA                                                   ",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2024-02-29T00:00:00",
    //                 "Institucion": "CONECEL",
    //                 "PorVencer": 0.0,
    //                 "Vencido": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "CodigoInstitucionParam": 1076,
    //                 "AcuerdoConcordatorio": "",
    //                 "InstitucionParam": "CONECEL",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2024-02-01T00:00:00",
    //                 "Institucion": "Sector Comercial (SICOM)",
    //                 "PorVencer": 0.0,
    //                 "Vencido": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "CodigoInstitucionParam": 1076,
    //                 "AcuerdoConcordatorio": "",
    //                 "InstitucionParam": "SICOM",
    //                 "Opcion": "CONS",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "1900-01-01T00:00:00",
    //                 "Institucion": "TOTAL:",
    //                 "PorVencer": 2436.8,
    //                 "Vencido": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "SaldoDeuda": 2436.8,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "CodigoInstitucionParam": 0,
    //                 "AcuerdoConcordatorio": "",
    //                 "InstitucionParam": "",
    //                 "Opcion": "CONS",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "1900-01-01T00:00:00",
    //                 "Institucion": "TOTAL:",
    //                 "PorVencer": 2436.8,
    //                 "Vencido": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "SaldoDeuda": 2436.8,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "CodigoInstitucionParam": 0,
    //                 "AcuerdoConcordatorio": "",
    //                 "InstitucionParam": "",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "1900-01-01T00:00:00",
    //                 "Institucion": "TOTAL:",
    //                 "PorVencer": 0.0,
    //                 "Vencido": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "CodigoInstitucionParam": 0,
    //                 "AcuerdoConcordatorio": "",
    //                 "InstitucionParam": "",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             }
    //         ],
    //         "Recursivo deuda historica 3601": [
    //             {
    //                 "FechaCorte": "2024-03-31T00:00:00",
    //                 "FechaCorteParam": "2024-03-31T00:00:00",
    //                 "PorVencer": 2436.8,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2436.8,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2024-03-01T00:00:00",
    //                 "FechaCorteParam": "2024-03-01T00:00:00",
    //                 "PorVencer": 2436.8,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2436.8,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2024-02-29T00:00:00",
    //                 "FechaCorteParam": "2024-02-29T00:00:00",
    //                 "PorVencer": 2282.37,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2282.37,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2024-02-29T00:00:00",
    //                 "FechaCorteParam": "2024-02-29T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2024-02-01T00:00:00",
    //                 "FechaCorteParam": "2024-02-01T00:00:00",
    //                 "PorVencer": 2282.37,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2282.37,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2024-01-31T00:00:00",
    //                 "FechaCorteParam": "2024-01-31T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2024-01-31T00:00:00",
    //                 "FechaCorteParam": "2024-01-31T00:00:00",
    //                 "PorVencer": 2379.28,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2379.28,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2024-01-01T00:00:00",
    //                 "FechaCorteParam": "2024-01-01T00:00:00",
    //                 "PorVencer": 2379.28,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2379.28,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2023-12-31T00:00:00",
    //                 "FechaCorteParam": "2023-12-31T00:00:00",
    //                 "PorVencer": 2632.76,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2632.76,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-12-31T00:00:00",
    //                 "FechaCorteParam": "2023-12-31T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-12-01T00:00:00",
    //                 "FechaCorteParam": "2023-12-01T00:00:00",
    //                 "PorVencer": 2632.76,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2632.76,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2023-11-30T00:00:00",
    //                 "FechaCorteParam": "2023-11-30T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-11-30T00:00:00",
    //                 "FechaCorteParam": "2023-11-30T00:00:00",
    //                 "PorVencer": 2720.43,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2720.43,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-11-01T00:00:00",
    //                 "FechaCorteParam": "2023-11-01T00:00:00",
    //                 "PorVencer": 2720.43,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2720.43,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2023-10-31T00:00:00",
    //                 "FechaCorteParam": "2023-10-31T00:00:00",
    //                 "PorVencer": 2811.8,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2811.8,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-10-31T00:00:00",
    //                 "FechaCorteParam": "2023-10-31T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-10-01T00:00:00",
    //                 "FechaCorteParam": "2023-10-01T00:00:00",
    //                 "PorVencer": 2811.8,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2811.8,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2023-09-30T00:00:00",
    //                 "FechaCorteParam": "2023-09-30T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-09-30T00:00:00",
    //                 "FechaCorteParam": "2023-09-30T00:00:00",
    //                 "PorVencer": 3039.89,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3039.89,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-09-01T00:00:00",
    //                 "FechaCorteParam": "2023-09-01T00:00:00",
    //                 "PorVencer": 3039.89,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3039.89,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2023-08-31T00:00:00",
    //                 "FechaCorteParam": "2023-08-31T00:00:00",
    //                 "PorVencer": 3246.99,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3246.99,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-08-31T00:00:00",
    //                 "FechaCorteParam": "2023-08-31T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-08-01T00:00:00",
    //                 "FechaCorteParam": "2023-08-01T00:00:00",
    //                 "PorVencer": 3246.99,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3246.99,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2023-07-31T00:00:00",
    //                 "FechaCorteParam": "2023-07-31T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-07-31T00:00:00",
    //                 "FechaCorteParam": "2023-07-31T00:00:00",
    //                 "PorVencer": 3263.11,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3263.11,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-07-01T00:00:00",
    //                 "FechaCorteParam": "2023-07-01T00:00:00",
    //                 "PorVencer": 3263.11,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3263.11,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2023-06-30T00:00:00",
    //                 "FechaCorteParam": "2023-06-30T00:00:00",
    //                 "PorVencer": 3405.49,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3405.49,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-06-30T00:00:00",
    //                 "FechaCorteParam": "2023-06-30T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-06-01T00:00:00",
    //                 "FechaCorteParam": "2023-06-01T00:00:00",
    //                 "PorVencer": 3405.49,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3405.49,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2023-05-31T00:00:00",
    //                 "FechaCorteParam": "2023-05-31T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-05-31T00:00:00",
    //                 "FechaCorteParam": "2023-05-31T00:00:00",
    //                 "PorVencer": 3596.89,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3596.89,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-05-01T00:00:00",
    //                 "FechaCorteParam": "2023-05-01T00:00:00",
    //                 "PorVencer": 3596.89,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3596.89,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2023-04-30T00:00:00",
    //                 "FechaCorteParam": "2023-04-30T00:00:00",
    //                 "PorVencer": 2608.31,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2608.31,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-04-30T00:00:00",
    //                 "FechaCorteParam": "2023-04-30T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-04-01T00:00:00",
    //                 "FechaCorteParam": "2023-04-01T00:00:00",
    //                 "PorVencer": 2608.31,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2608.31,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2023-03-31T00:00:00",
    //                 "FechaCorteParam": "2023-03-31T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-03-31T00:00:00",
    //                 "FechaCorteParam": "2023-03-31T00:00:00",
    //                 "PorVencer": 2681.99,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2681.99,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-03-01T00:00:00",
    //                 "FechaCorteParam": "2023-03-01T00:00:00",
    //                 "PorVencer": 2681.99,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2681.99,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2023-02-28T00:00:00",
    //                 "FechaCorteParam": "2023-02-28T00:00:00",
    //                 "PorVencer": 2771.61,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2771.61,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-02-28T00:00:00",
    //                 "FechaCorteParam": "2023-02-28T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-02-01T00:00:00",
    //                 "FechaCorteParam": "2023-02-01T00:00:00",
    //                 "PorVencer": 2771.61,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2771.61,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2023-01-31T00:00:00",
    //                 "FechaCorteParam": "2023-01-31T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-01-31T00:00:00",
    //                 "FechaCorteParam": "2023-01-31T00:00:00",
    //                 "PorVencer": 2933.95,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2933.95,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-01-01T00:00:00",
    //                 "FechaCorteParam": "2023-01-01T00:00:00",
    //                 "PorVencer": 2933.95,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 2933.95,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2022-12-31T00:00:00",
    //                 "FechaCorteParam": "2022-12-31T00:00:00",
    //                 "PorVencer": 3001.46,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3001.46,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-12-31T00:00:00",
    //                 "FechaCorteParam": "2022-12-31T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2022-12-01T00:00:00",
    //                 "FechaCorteParam": "2022-12-01T00:00:00",
    //                 "PorVencer": 3001.46,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3001.46,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2022-11-30T00:00:00",
    //                 "FechaCorteParam": "2022-11-30T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2022-11-30T00:00:00",
    //                 "FechaCorteParam": "2022-11-30T00:00:00",
    //                 "PorVencer": 3084.01,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3084.01,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-11-01T00:00:00",
    //                 "FechaCorteParam": "2022-11-01T00:00:00",
    //                 "PorVencer": 3084.01,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3084.01,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2022-10-31T00:00:00",
    //                 "FechaCorteParam": "2022-10-31T00:00:00",
    //                 "PorVencer": 3143.82,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3143.82,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-10-31T00:00:00",
    //                 "FechaCorteParam": "2022-10-31T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2022-10-01T00:00:00",
    //                 "FechaCorteParam": "2022-10-01T00:00:00",
    //                 "PorVencer": 3143.82,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3143.82,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2022-09-30T00:00:00",
    //                 "FechaCorteParam": "2022-09-30T00:00:00",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 0.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2022-09-30T00:00:00",
    //                 "FechaCorteParam": "2022-09-30T00:00:00",
    //                 "PorVencer": 3215.19,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3215.19,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-09-01T00:00:00",
    //                 "FechaCorteParam": "2022-09-01T00:00:00",
    //                 "PorVencer": 3215.19,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3215.19,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2022-08-31T00:00:00",
    //                 "FechaCorteParam": "2022-08-31T00:00:00",
    //                 "PorVencer": 3294.92,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3294.92,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-08-31T00:00:00",
    //                 "FechaCorteParam": "2022-08-31T00:00:00",
    //                 "PorVencer": 22.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 22.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2022-08-01T00:00:00",
    //                 "FechaCorteParam": "2022-08-01T00:00:00",
    //                 "PorVencer": 3316.92,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3316.92,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2022-07-31T00:00:00",
    //                 "FechaCorteParam": "2022-07-31T00:00:00",
    //                 "PorVencer": 22.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 22.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2022-07-31T00:00:00",
    //                 "FechaCorteParam": "2022-07-31T00:00:00",
    //                 "PorVencer": 3362.2,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3362.2,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-07-01T00:00:00",
    //                 "FechaCorteParam": "2022-07-01T00:00:00",
    //                 "PorVencer": 3384.2,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3384.2,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB - SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2022-06-30T00:00:00",
    //                 "FechaCorteParam": "2022-06-30T00:00:00",
    //                 "PorVencer": 3430.19,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3430.19,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-06-01T00:00:00",
    //                 "FechaCorteParam": "2022-06-01T00:00:00",
    //                 "PorVencer": 3430.19,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3430.19,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2022-05-31T00:00:00",
    //                 "FechaCorteParam": "2022-05-31T00:00:00",
    //                 "PorVencer": 3500.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3500.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SB",
    //                 "OpcionParam": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-05-01T00:00:00",
    //                 "FechaCorteParam": "2022-05-01T00:00:00",
    //                 "PorVencer": 3500.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 3500.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SB",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2021-10-31T00:00:00",
    //                 "FechaCorteParam": "2021-10-31T00:00:00",
    //                 "PorVencer": 22.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 22.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2021-10-01T00:00:00",
    //                 "FechaCorteParam": "2021-10-01T00:00:00",
    //                 "PorVencer": 22.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 22.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SICOM",
    //                 "OpcionParam": "CONS"
    //             },
    //             {
    //                 "FechaCorte": "2021-06-30T00:00:00",
    //                 "FechaCorteParam": "2021-06-30T00:00:00",
    //                 "PorVencer": 22.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 22.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": "SICOM",
    //                 "OpcionParam": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2021-06-01T00:00:00",
    //                 "FechaCorteParam": "2021-06-01T00:00:00",
    //                 "PorVencer": 22.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido0a1": 0.0,
    //                 "Vencido1a2": 0.0,
    //                 "Vencido2a3": 0.0,
    //                 "Vencido3a6": 0.0,
    //                 "Vencido6a9": 0.0,
    //                 "Vencido9a12": 0.0,
    //                 "Vencido12a24": 0.0,
    //                 "Vencido24": 0.0,
    //                 "Vencido36": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0,
    //                 "SaldoDeuda": 22.0,
    //                 "tipoDeudaParam": "D",
    //                 "Opcion": " SICOM",
    //                 "OpcionParam": "CONS"
    //             }
    //         ],
    //         "Creditos otorgados 12 ultimos meses Educativo 3600": [],
    //         "Tarjetas de credito anuladas por mal manejo": [],
    //         "Ultimas 10 operaciones canceladas": [],
    //         "Recursivo Garantias personales codeudores operaciones vigentes": [],
    //         "Vinculaciones a Instituciones Financieras": [],
    //         "Analisis detalle del vencido": [],
    //         "Analisis saldos por vencer sistema financiero": [
    //             {
    //                 "FechaCorte": "2024-03-31T00:00:00",
    //                 "Institucion": "PACIFICO",
    //                 "CodigoInstitucionInv": 1028.0,
    //                 "TotalPorVencer": 1757.83,
    //                 "PorVencer0a1": 80.16,
    //                 "PorVencer1a3": 167.35,
    //                 "PorVencer3a6": 259.26,
    //                 "PorVencer6a12": 551.48,
    //                 "PorVencer12": 699.58
    //             },
    //             {
    //                 "FechaCorte": "2024-03-31T00:00:00",
    //                 "Institucion": "PICHINCHA",
    //                 "CodigoInstitucionInv": 1029.0,
    //                 "TotalPorVencer": 678.97,
    //                 "PorVencer0a1": 203.03,
    //                 "PorVencer1a3": 242.42,
    //                 "PorVencer3a6": 233.52,
    //                 "PorVencer6a12": 0.0,
    //                 "PorVencer12": 0.0
    //             },
    //             {
    //                 "FechaCorte": null,
    //                 "Institucion": null,
    //                 "CodigoInstitucionInv": null,
    //                 "TotalPorVencer": 2436.8,
    //                 "PorVencer0a1": 283.19,
    //                 "PorVencer1a3": 409.77,
    //                 "PorVencer3a6": 492.78,
    //                 "PorVencer6a12": 551.48,
    //                 "PorVencer12": 699.58
    //             }
    //         ],
    //         "WSO Graficar Evolucion Deuda 3 Sistemas": [
    //             {
    //                 "FechaCorte": "2021-04-30T00:00:00",
    //                 "Total": 22.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2021-06-30T00:00:00",
    //                 "Total": 22.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2021-10-31T00:00:00",
    //                 "Total": 22.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2022-05-31T00:00:00",
    //                 "Total": 3500.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-06-30T00:00:00",
    //                 "Total": 3430.19,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-07-31T00:00:00",
    //                 "Total": 3362.2,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-07-31T00:00:00",
    //                 "Total": 22.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2022-08-31T00:00:00",
    //                 "Total": 22.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2022-08-31T00:00:00",
    //                 "Total": 3294.92,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-09-30T00:00:00",
    //                 "Total": 3215.19,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-09-30T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2022-10-31T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2022-10-31T00:00:00",
    //                 "Total": 3143.82,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-11-30T00:00:00",
    //                 "Total": 3084.01,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2022-11-30T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2022-12-31T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2022-12-31T00:00:00",
    //                 "Total": 3001.46,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-01-31T00:00:00",
    //                 "Total": 2933.95,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-01-31T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-02-28T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-02-28T00:00:00",
    //                 "Total": 2771.61,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-03-31T00:00:00",
    //                 "Total": 2681.99,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-03-31T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-04-30T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-04-30T00:00:00",
    //                 "Total": 2608.31,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-05-31T00:00:00",
    //                 "Total": 3596.89,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-05-31T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-06-30T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-06-30T00:00:00",
    //                 "Total": 3405.49,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-07-31T00:00:00",
    //                 "Total": 3263.11,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-07-31T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-08-31T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-08-31T00:00:00",
    //                 "Total": 3246.99,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-09-30T00:00:00",
    //                 "Total": 3039.89,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-09-30T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-10-31T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-10-31T00:00:00",
    //                 "Total": 2811.8,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-11-30T00:00:00",
    //                 "Total": 2720.43,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2023-11-30T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-12-31T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2023-12-31T00:00:00",
    //                 "Total": 2632.76,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2024-01-31T00:00:00",
    //                 "Total": 2379.28,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2024-01-31T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2024-02-29T00:00:00",
    //                 "Total": 0.0,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SICOM"
    //             },
    //             {
    //                 "FechaCorte": "2024-02-29T00:00:00",
    //                 "Total": 2282.37,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             },
    //             {
    //                 "FechaCorte": "2024-03-31T00:00:00",
    //                 "Total": 2436.8,
    //                 "Vencidos": 0.0,
    //                 "Opcion": "SB"
    //             }
    //         ],
    //         "Sujeto Al Dia Infocom": [
    //             {
    //                 "Institucion": "CONECEL - telf: 042515555",
    //                 "FechaCorte": "29/02/2024",
    //                 "Mensaje": "CLIENTE AL DIA EN SUS PAGOS"
    //             }
    //         ],
    //         "Score V4_V10": [
    //             {
    //                 "Score": 958,
    //                 "TotalAcum": 80,
    //                 "TasaDeMalosAcum": 4.9,
    //                 "ScoreMin": 946.0,
    //                 "ScoreMax": 958.0,
    //                 "FechaInicial": "2021-04-30T00:00:00",
    //                 "FechaFinal": "2024-03-31T00:00:00"
    //             }
    //         ],
    //         "Valor deuda total en los 3 segmentos SIN IESS 360": [
    //             {
    //                 "Titulo": "Sistema Financiero Regulado SB",
    //                 "TituloWSInv": "SBS",
    //                 "PorVencer": 2436.8,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido": 0.0,
    //                 "Total": 2436.8,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0
    //             },
    //             {
    //                 "Titulo": "Entidades Reguladas SEPS",
    //                 "TituloWSInv": "Entidades Reguladas SEPS",
    //                 "PorVencer": 0.0,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido": 0.0,
    //                 "Total": 0.0,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0
    //             },
    //             {
    //                 "Titulo": null,
    //                 "TituloWSInv": null,
    //                 "PorVencer": 2436.8,
    //                 "NoDevengaInt": 0.0,
    //                 "Vencido": 0.0,
    //                 "Total": 2436.8,
    //                 "DemandaJudicial": 0.0,
    //                 "CarteraCastigada": 0.0
    //             }
    //         ],
    //         "Cuota estimada Mensual Web": [
    //             {
    //                 "Pago": 249.0,
    //                 "NumeroCreditosComercial": 0,
    //                 "TotalVencido": 0.0,
    //                 "TotalDemanda": 0.0,
    //                 "TotalCartera": 0.0,
    //                 "NumeroCreditosIece": 0,
    //                 "NumeroOperacionesExcluidas": 0
    //             }
    //         ],
    //         "Garantias personales codeudores operaciones no vigentes": [],
    //         "Indicador COVID 360 V2": [
    //             {
    //                 "IndicadorCovid": 6,
    //                 "Ingreso": 599.0,
    //                 "CuotaFinanciera": 249.0
    //             }
    //         ],
    //         "DetalleDeudaActualReportadaSBS360": [
    //             {
    //                 "Institucion": "PACIFICO",
    //                 "Fecha de Corte": "31/03/2024",
    //                 "Tipo Riesgo": "TITULAR                                                     ",
    //                 "Tipo Credito": "Consumo",
    //                 "Cupo / Monto Original": 3500.0,
    //                 "Fecha de Apertura": "18/05/2022",
    //                 "Fecha de Vencimiento": "29/10/2025",
    //                 "Calif. Propia": null,
    //                 "Total Vencer": 1757.83,
    //                 "NDI": 0.0,
    //                 "Total Vencido": 0.0,
    //                 "Dem. Jud.": 0.0,
    //                 "Cart. Cast.": 0.0,
    //                 "Saldo Deuda": 1757.83,
    //                 "Cuota Mensual": 105.56
    //             },
    //             {
    //                 "Institucion": "PICHINCHA",
    //                 "Fecha de Corte": "31/03/2024",
    //                 "Tipo Riesgo": "TITULAR                                                     ",
    //                 "Tipo Credito": "Tarjeta",
    //                 "Cupo / Monto Original": 1000.0,
    //                 "Fecha de Apertura": "01/05/2023",
    //                 "Fecha de Vencimiento": "01/05/2026",
    //                 "Calif. Propia": null,
    //                 "Total Vencer": 678.97,
    //                 "NDI": 0.0,
    //                 "Total Vencido": 0.0,
    //                 "Dem. Jud.": 0.0,
    //                 "Cart. Cast.": 0.0,
    //                 "Saldo Deuda": 678.97,
    //                 "Cuota Mensual": 143.44
    //             }
    //         ],
    //         "Evolucion Historica y Dist Endeudamiento SICOM": [
    //             {
    //                 "FechaCorte": "Agosto 2022",
    //                 "NOM_INSTITUCION": "CONECEL",
    //                 "TIPO_DEUDOR": "TITULAR",
    //                 "VAL_X_VENCER": 22.0,
    //                 "VAL_VENCIDO": 0.0,
    //                 "VAL_NDI": 0.0,
    //                 "VAL_DEM_JUDICIAL": 0.0,
    //                 "VAL_CART_CASTIGADA": 0.0,
    //                 "NUM_DIAS_VENCIDO_ACTUALIZADO": 0
    //             },
    //             {
    //                 "FechaCorte": "Julio 2022",
    //                 "NOM_INSTITUCION": "CONECEL",
    //                 "TIPO_DEUDOR": "TITULAR",
    //                 "VAL_X_VENCER": 22.0,
    //                 "VAL_VENCIDO": 0.0,
    //                 "VAL_NDI": 0.0,
    //                 "VAL_DEM_JUDICIAL": 0.0,
    //                 "VAL_CART_CASTIGADA": 0.0,
    //                 "NUM_DIAS_VENCIDO_ACTUALIZADO": 0
    //             },
    //             {
    //                 "FechaCorte": "Octubre 2021",
    //                 "NOM_INSTITUCION": "CONECEL",
    //                 "TIPO_DEUDOR": "TITULAR",
    //                 "VAL_X_VENCER": 22.0,
    //                 "VAL_VENCIDO": 0.0,
    //                 "VAL_NDI": 0.0,
    //                 "VAL_DEM_JUDICIAL": 0.0,
    //                 "VAL_CART_CASTIGADA": 0.0,
    //                 "NUM_DIAS_VENCIDO_ACTUALIZADO": 0
    //             },
    //             {
    //                 "FechaCorte": "Junio 2021",
    //                 "NOM_INSTITUCION": "CONECEL",
    //                 "TIPO_DEUDOR": "TITULAR",
    //                 "VAL_X_VENCER": 22.0,
    //                 "VAL_VENCIDO": 0.0,
    //                 "VAL_NDI": 0.0,
    //                 "VAL_DEM_JUDICIAL": 0.0,
    //                 "VAL_CART_CASTIGADA": 0.0,
    //                 "NUM_DIAS_VENCIDO_ACTUALIZADO": 0
    //             }
    //         ],
    //         "Factores que influyen ScoreV4": [
    //             {
    //                 "Segmento": "A",
    //                 "CupoTarjetaCredito": 1000.0,
    //                 "TiempoDesdePrimerCredito": 20,
    //                 "UtilizacionTC": 0.48,
    //                 "DeudaTotal": 0.0,
    //                 "MaximoMontoOtrogado": 3500.0,
    //                 "DiadeAtraso": 0,
    //                 "RatioDeudaReciente": 0.5,
    //                 "TiempoSinOperaciones": 1,
    //                 "RatioDeudaVencida": 0.0,
    //                 "PresenciaConsultas": 0,
    //                 "PresenciaMora": 0
    //             }
    //         ],
    //         "Califica Detalle de tarjetas 360": [
    //             {
    //                 "Institucion": "PICHINCHA",
    //                 "Emisor": "VISA",
    //                 "Antiguedad": 10,
    //                 "Cupo": 1000.0,
    //                 "SaldoActual": 678.97,
    //                 "SaldoPromedioUltimos6Meses": 586.82,
    //                 "PorcentajeUsoTarjeta": 58.68,
    //                 "PorcentajeRelacionDeudaTCDeudaTotal": 27.86,
    //                 "NumeroTarjetaInv": "qqiqqqeeesgwilp6"
    //             }
    //         ]
    //     }';

    //     $resp = json_decode($jso_data, true, 512, JSON_UNESCAPED_UNICODE);
        
    //     return array(
    //         "http_code" => "200",
    //         "data" => $resp,
    //         "mensaje" => 'respuesta exitosa',
    //     );
    // }
}



