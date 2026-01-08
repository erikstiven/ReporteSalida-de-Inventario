<?php

class ValidarIdentificacion
{

    protected $error = '';
    protected $informacion = '';
    protected $url_web_service = '';

    public function __construct($S_URL_API_SRI)
    {
        $this->url_web_service = $S_URL_API_SRI;
    }


    public function validarCedulaEcuador($numero = '')
    {
        $resultado_consulta = false;
        $numero = (string)$numero;
        $this->setError('');
        try {
            $this->validarInicial($numero, '10');
            $curl = curl_init();

            $url_consulta = $this->url_web_service.'ConsultasDatos/ConsultaCedula?Apikey=POUGCTE4RW653JHDXGFMVCNE7I56&Cedula='.$numero;

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url_consulta,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 2,
                CURLOPT_TIMEOUT => 2,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                throw new Exception(curl_error($curl));
            }

            curl_close($curl);

            $resultado = json_decode($response);

            if(is_object($resultado)){

                if($resultado->nombre == '' || $resultado->nombre == NULL){
                    //$resultado->error ="No existe cedula";
                    throw new Exception('IDENTIFICACION INVALIDA');
                }else{
                    $this->setInformacion($resultado);
                }
            }else{
                throw new Exception('NO SE ENCONTRO INFORMACION');
            }

            $resultado_consulta = true;

        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }

        return $resultado_consulta;
    }


    public function validarRucEcuador($numero = '')
    {
        $resultado_consulta = false;
        $numero = (string)$numero;
        $this->setError('');
        try {
            $this->validarInicial($numero, '13');
            //$curl = curl_init();
            //$url_consulta = $this->url_web_service.'ConsultasDatosSri/RucSri?Apikey=POUGCTE4RW653JHDXGFMVCNE7I56&Ruc='.$numero;
            $url_consulta = 'https://srienlinea.sri.gob.ec/sri-catastro-sujeto-servicio-internet/rest/ConsolidadoContribuyente/obtenerPorNumerosRuc?&ruc='.$numero;
            //echo $url_consulta; exit;
            //******Banchito */
            $curl = curl_init($url_consulta);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($curl);
            if (curl_errno($curl)) 
            {
                throw new Exception(curl_error($curl));
            }
            curl_close($curl);
            $resultado = json_decode($response, true);
            if (!empty($resultado)) {
                $resultado_consulta = true;
            } else {
                throw new Exception('NO SE ENCONTRO DATOS');
            }
            //echo $resultado['numeroRuc'];
            //******Banchito */
            /*curl_setopt_array($curl, array(
                CURLOPT_URL => $url_consulta,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 2,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                throw new Exception(curl_error($curl));
            }

            curl_close($curl);

            $resultado = json_decode($response);
            //echo $resultado->numeroRuc; exit;
            if(is_object($resultado)){

                if($resultado->error){
                    throw new Exception('Validador sin conexion: '.$resultado->error);
                }else{
                    $this->setInformacion($resultado);
                }
            }else{
                throw new Exception('NO SE ENCONTRO INFORMACION');
            }
            */
            $resultado_consulta = true;

        } catch (Exception $e) {
            $this->setError($e->getMessage());
            // SE CAMBIÓ ESTA VALIDACION QUE CONSULTA AL SRI EL RUC
            //$resultado_consulta = true;
        }

        return $resultado_consulta;
    }


    public function validarInicial($numero, $caracteres)
    {
        if (empty($numero)) {
            throw new Exception('Valor no puede estar vacio');
        }

        if (!ctype_digit($numero)) {
            throw new Exception('Valor ingresado solo puede tener dígitos');
        }

        if (strlen($numero) != $caracteres) {
            throw new Exception('Valor ingresado debe tener '.$caracteres.' caracteres');
        }
        return true;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($newError)
    {
        $this->error = $newError;
        return $this;
    }

    public function getInformacion()
    {
        return $this->informacion;
    }

    public function setInformacion($informacion)
    {
        $this->informacion = $informacion;
        return $this;
    }
}

?>
