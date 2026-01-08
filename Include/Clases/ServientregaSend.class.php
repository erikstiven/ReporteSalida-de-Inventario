<?php
//require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class ServientregaSend
{
    private $oIfx;

    public $empr_cod_empr = '';
    public $empr_nom_empr = '';
    public $empr_servi_sn = 0;
    public $empr_servi_url = '';
    public $empr_servi_user = '';
    public $empr_servi_pass = '';

    public $empr_dir_empr = '';
    public $empr_ruc_empr = '';
    public $empr_mai_empr = '';
    public $empr_tel_resp = '';

    function __construct($oIfx, $idempresa)
    {
        $this->oIfx = $oIfx;

        $sql = "SELECT empr_cod_empr, empr_nom_empr, empr_servi_sn, empr_servi_url, empr_servi_user, empr_servi_pass, 
                    empr_dir_empr, empr_ruc_empr, empr_mai_empr, empr_tel_resp
                    FROM saeempr 
                    WHERE empr_cod_empr = $idempresa";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {

                    $this->empr_cod_empr = $oIfx->f('empr_cod_empr');
                    $this->empr_nom_empr = $oIfx->f('empr_nom_empr');
                    $this->empr_servi_sn = $oIfx->f('empr_servi_sn');
                    $this->empr_servi_url = $oIfx->f('empr_servi_url');
                    $this->empr_servi_user = $oIfx->f('empr_servi_user');
                    $this->empr_servi_pass = $oIfx->f('empr_servi_pass');

                    $this->empr_dir_empr = $oIfx->f('empr_dir_empr');
                    $this->empr_ruc_empr = $oIfx->f('empr_ruc_empr');
                    $this->empr_mai_empr = $oIfx->f('empr_mai_empr');
                    $this->empr_tel_resp = $oIfx->f('empr_tel_resp');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    }

    function ejecutarCiudades()
    {
        if ($this->empr_servi_sn) {

            try {

                $headers = array(
                    "Content-Type:application/json"
                );

                $ch = curl_init();
                $url = $this->empr_servi_url . '/api/ciudades/' . curl_escape($ch, "['$this->empr_servi_user', '$this->empr_servi_pass']");

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                $respuesta = curl_exec($ch);
                if (!curl_errno($ch)) {

                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);


                    switch ($http_code) {
                        case 200:

                            $count_update = 0;
                            foreach ($data_json as $item) {
                                $count_update++;
                                $id_ciudad = $item['id'];
                                $nombre_ciudad = $item['nombre'];


                                $sql = "select count(*) as contador from comercial.ciudades_servientrega where id_ciudad = '$id_ciudad';";
                                $existe = consulta_string_func($sql, 'contador', $this->oIfx, 0);

                                if ($existe) {

                                    $sql = "update comercial.ciudades_servientrega set 
                                                    nombre = '$nombre_ciudad' where
                                                    id_ciudad = '$id_ciudad' ";
                                    $this->oIfx->QueryT($sql);
                                } else {

                                    $sql = "insert into comercial.ciudades_servientrega(nombre, id_ciudad)
                                                            values('$nombre_ciudad', '$id_ciudad') ";
                                    $this->oIfx->QueryT($sql);
                                }
                            }

                            $result = array(
                                'result' => $count_update
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
            throw new Exception('No tiene activo el envio de Servientrega');
        }

        return $result;
    }

    function ejecutarEnvioWeb($data)
    {
        if ($this->empr_servi_sn) {

            try {

                $headers = array(
                    "Content-Type:application/json"
                );

                $ch = curl_init();
                $url = $this->empr_servi_url . '/api/guiawebs/';

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                $respuesta = curl_exec($ch);
                if (!curl_errno($ch)) {

                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);

                    switch ($http_code) {
                        case 201:

                            $result = array(
                                'data_id' => $data_json['id'],
                                'data_mensaje' => $data_json['msj'],
                                'data_save' => json_encode($data_json),
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
            throw new Exception('No tiene activo el envio de Servientrega');
        }

        return $result;
    }

    function ejecutarAnulacion($guia_servi_codigo)
    {
        if ($this->empr_servi_sn) {

            try {

                $headers = array(
                    "Content-Type:application/json"
                );

                $ch = curl_init();
                $url = $this->empr_servi_url . '/api/guiawebs/' . curl_escape($ch, "['$guia_servi_codigo','$this->empr_servi_user', '$this->empr_servi_pass']");

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                $respuesta = curl_exec($ch);
                if (!curl_errno($ch)) {

                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);


                    switch ($http_code) {
                        case 201:

                            $pos = strpos($data_json['msj'], 'ANULADA CORRECTAMENTE');
                            $pos2 = strpos($data_json['msj'], 'YA SE ENCUENTRA ANULADA');
                            if ($pos == true || $pos2 == true) {
                                $result = array(
                                    'data_id' => $data_json['id'],
                                    'data_mensaje' => $data_json['msj'],
                                    'data_save' => json_encode($data_json),
                                );
                            } else {
                                throw new Exception($data_json['msj']);
                            }

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
            throw new Exception('No tiene activo el envio de Servientrega');
        }

        return $result;
    }

    function impresionGuiaA4($guia_servi_codigo)
    {
        if ($this->empr_servi_sn) {

            try {

                $headers = array(
                    "Content-Type:application/json"
                );

                $ch = curl_init();
                $empr_servi_url = str_replace(':5052', ':5001', $this->empr_servi_url);

                $url = $empr_servi_url . '/api/GuiasWeb/' . curl_escape($ch, "['$guia_servi_codigo','$this->empr_servi_user', '$this->empr_servi_pass', '1']");

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                $respuesta = curl_exec($ch);

                if (!curl_errno($ch)) {

                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $data_json = json_decode($respuesta, true);


                    switch ($http_code) {
                        case 201:

                            $result = array(
                                'data_guia' => $data_json['guia'],
                                'data_mensaje' => $data_json['mensaje'],
                                'data_archivo' => json_encode($data_json['archivoEncriptado']),
                                'data_completa' => json_encode($data_json),
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
            throw new Exception('No tiene activo el envio de Servientrega');
        }

        return $result;
    }
}
