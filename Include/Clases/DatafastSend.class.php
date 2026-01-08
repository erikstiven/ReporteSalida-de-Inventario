<?php

class DatafastSend
{
    private $oIfx;

    public $empr_cod_empr = '';
    public $empr_nom_empr = '';
    public $empr_datafast_sn = 0;
    public $empr_datafast_url = '';
    public $empr_datafast_token = '';

    function __construct($oIfx, $idempresa)
    {
        $this->oIfx = $oIfx;

        $sql = "SELECT empr_cod_empr, empr_nom_empr, empr_datafast_sn, empr_datafast_url, empr_datafast_token
                    FROM saeempr 
                    WHERE empr_cod_empr = $idempresa";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {

                    $this->empr_cod_empr = $oIfx->f('empr_cod_empr');
                    $this->empr_nom_empr = $oIfx->f('empr_nom_empr');
                    $this->empr_datafast_sn = $oIfx->f('empr_datafast_sn');
                    $this->empr_datafast_url = $oIfx->f('empr_datafast_url');
                    $this->empr_datafast_token = $oIfx->f('empr_datafast_token');

                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

    }

    function configuracionInicialEquipo($codigo_asignado, $empresa_id, $sucursal_id)
    {
        if ($this->empr_datafast_sn) {

            try {

                $headers = array(
                    "Content-Type:application/json",
                    "token_api:$this->empr_datafast_token",
                );

                $url_envio = "$this->empr_datafast_url/api/datafast/config_inicial?codigo_asignado=$codigo_asignado&empresa_id=$empresa_id&sucursal_id=$sucursal_id";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $url_envio);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                // CURLOPT_SSL_VERIFYHOST PARA PRUEBAS EN LOCALHOST
                // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                $respuesta = curl_exec($ch);

                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);
                    switch ($http_code) {
                        case 200:
                            $code = $data_json['code'];
                            $message = $data_json['message'];
                            $responseData = $data_json['responseData'];

                            if ($code != 1) {
                                throw new Exception($responseData);
                            }

                            $result = array(
                                'result' => $data_json
                            );
                            break;

                        case 400:
                            $responseData = $data_json['responseData'];
                            throw new Exception($responseData);
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

        } else {
            throw new Exception('No tiene activo el envio de Datafast');
        }

        return $result;
    }

    function configuracionLoteEquipo($codigo_asignado, $empresa_id, $sucursal_id)
    {
        if ($this->empr_datafast_sn) {

            try {

                $headers = array(
                    "Content-Type:application/json",
                    "token_api:$this->empr_datafast_token",
                    'Content-Length: 0',
                );
                $url_envio = "$this->empr_datafast_url/api/datafast/procesar_control_lote?codigo_asignado=$codigo_asignado&empresa_id=$empresa_id&sucursal_id=$sucursal_id";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $url_envio);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                // CURLOPT_SSL_VERIFYHOST PARA PRUEBAS EN LOCALHOST
                // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                $respuesta = curl_exec($ch);


                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);
                    
                    switch ($http_code) {
                        case 200:
                            $code = $data_json['code'];
                            $message = $data_json['message'];
                            $responseData = $data_json['responseData'];

                            if ($code != 1) {
                                throw new Exception($responseData);
                            }

                            $result = array(
                                'result' => $data_json
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

        } else {
            throw new Exception('No tiene activo el envio de Datafast');
        }

        return $result;
    }

    function procesoPagoEquipo($codigo_asignado, $empresa_id, $sucursal_id, $data_array)
    {
        if ($this->empr_datafast_sn) {

            try {

                if (count($data_array) != 9) {
                    throw new Exception("Error al recibir datos");
                }

                $headers = array(
                    "Content-Type:application/json",
                    "token_api:$this->empr_datafast_token",
                );
                $url_envio = "$this->empr_datafast_url/api/datafast/procesar_pago?codigo_asignado=$codigo_asignado&empresa_id=$empresa_id&sucursal_id=$sucursal_id";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $url_envio);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                // CURLOPT_SSL_VERIFYHOST PARA PRUEBAS EN LOCALHOST
                // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_array));
                $respuesta = curl_exec($ch);


                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    switch ($http_code) {
                        case 200:
                            $code = $data_json['code'];
                            $message = $data_json['message'];
                            $responseData = $data_json['responseData'];

                            if ($code != 1) {
                                throw new Exception($responseData);
                            }

                            $result = array(
                                'result' => $data_json
                            );

                            break;
                        case 400:
                            $code = $data_json['code'];
                            $message = $data_json['message'];
                            $responseData = $data_json['responseData'];
                            throw new Exception($responseData);
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

        } else {
            throw new Exception('No tiene activo el envio de Datafast');
        }

        return $result;
    }

    function procesoReversoEquipo($codigo_asignado, $empresa_id, $sucursal_id, $data_array)
    {
        if ($this->empr_datafast_sn) {

            try {

                if (count($data_array) != 10) {
                    throw new Exception("Error al recibir datos");
                }

                $headers = array(
                    "Content-Type:application/json",
                    "token_api:$this->empr_datafast_token",
                );
                $url_envio = "$this->empr_datafast_url/api/datafast/reverso_pago?codigo_asignado=$codigo_asignado&empresa_id=$empresa_id&sucursal_id=$sucursal_id";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $url_envio);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                // CURLOPT_SSL_VERIFYHOST PARA PRUEBAS EN LOCALHOST
                // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_array));
                $respuesta = curl_exec($ch);


                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    switch ($http_code) {
                        case 200:
                            $code = $data_json['code'];
                            $message = $data_json['message'];
                            $responseData = $data_json['responseData'];

                            if ($code != 1) {
                                throw new Exception($responseData);
                            }

                            $result = array(
                                'result' => $data_json
                            );

                            break;
                        case 400:
                            $code = $data_json['code'];
                            $message = $data_json['message'];
                            $responseData = $data_json['responseData'];
                            throw new Exception($responseData);
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

        } else {
            throw new Exception('No tiene activo el envio de Datafast');
        }

        return $result;
    }

    function procesoAnulacionEquipo($codigo_asignado, $empresa_id, $sucursal_id, $data_array)
    {
        if ($this->empr_datafast_sn) {

            try {

                if (count($data_array) != 5) {
                    throw new Exception("Error al recibir datos");
                }

                $headers = array(
                    "Content-Type:application/json",
                    "token_api:$this->empr_datafast_token",
                );
                $url_envio = "$this->empr_datafast_url/api/datafast/anulacion_pago?codigo_asignado=$codigo_asignado&empresa_id=$empresa_id&sucursal_id=$sucursal_id";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $url_envio);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                // CURLOPT_SSL_VERIFYHOST PARA PRUEBAS EN LOCALHOST
                // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_array));
                $respuesta = curl_exec($ch);


                if (!curl_errno($ch)) {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    switch ($http_code) {
                        case 200:
                            $code = $data_json['code'];
                            $message = $data_json['message'];
                            $responseData = $data_json['responseData'];

                            if ($code != 1) {
                                throw new Exception($responseData);
                            }

                            $result = array(
                                'result' => $data_json
                            );

                            break;
                        case 400:
                            $code = $data_json['code'];
                            $message = $data_json['message'];
                            $responseData = $data_json['responseData'];
                            throw new Exception($responseData);
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

        } else {
            throw new Exception('No tiene activo el envio de Datafast');
        }

        return $result;
    }


}

?>