<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class GeneradorCash{
	
	private $oConexion1; 
	private $oConexion2; 

    function __construct($oConexion1,$oConexion2) {
        //Los objetos de conexion deben ser de tipo Transaccional
        $this->oConexion1 = $oConexion1;
        $this->oConexion2 = $oConexion2;

    }



    // // genera cash para el banco del pacifico
    // private function cash_banco_pacifico($datos_arr,$separador=''){
    //     $row_record = '';
    //     foreach ($datos_arr as $key => $value) {
    //         $row_record .= $this->cash_banco_pacifico_indi($value,$separador);
    //     }
    //     return $row_record;

    // }


    public function cash_sftp_pichincha_indi($datos_arr_indi,$separador=''){

        $id_sobre                   = $datos_arr_indi['id_sobre'];
        $id_item                    = $datos_arr_indi['id_item'];  
        $referencia_sobre           = $datos_arr_indi['referencia_sobre'];//nombre_archivo_cargado
        $pais                       = $datos_arr_indi['pais'];//pais de la cuenta
        $banco                      = $datos_arr_indi['banco'];//banco de la cuenta
        $forma_pago                 = $datos_arr_indi['forma_pago'];
        $pais_banco_cuenta          = $datos_arr_indi['pais_banco_cuenta'];
        $contrapartida              = $datos_arr_indi['contrapartida'];
        $referencia                 = $datos_arr_indi['referencia'];
        $valor_porcc                = $datos_arr_indi['valor_porcc'];
        $valor                      = $datos_arr_indi['valor'];
        $moneda                     = $datos_arr_indi['moneda'];
        $fecha_proceso              = $datos_arr_indi['fecha_proceso'];
        $mensaje                    = $datos_arr_indi['mensaje'];
        $referencia_adicional       = $datos_arr_indi['referencia_adicional'];
        $numero_documento           = $datos_arr_indi['numero_documento'];
        $tipo_pago                  = $datos_arr_indi['tipo_pago'];
        $numero_cuenta              = $datos_arr_indi['numero_cuenta'];
        $no_documento               = $datos_arr_indi['no_documento'];
        $estado_impresion           = $datos_arr_indi['estado_impresion'];
        $secuencial_cobro           = $datos_arr_indi['secuencial_cobro'];
        $numero_comprobante         = $datos_arr_indi['numero_comprobante'];

        $array_data = array(
            "1"     => array("val" => $id_sobre,                "len" => 8,     "type" => "a"),
            "2"     => array("val" => $id_item,                 "len" => 9,     "type" => "a"),
            "3"     => array("val" => $referencia_sobre,        "len" => 50,    "type" => "a"),
            "4"     => array("val" => $pais,                    "len" => 2,     "type" => "a"),
            "5"     => array("val" => $banco,                   "len" => 4,     "type" => "a"),
            "6"     => array("val" => $forma_pago,              "len" => 3,     "type" => "a"),
            "7"     => array("val" => $pais_banco_cuenta,       "len" => 33,    "type" => "a"),
            "8"     => array("val" => $contrapartida,           "len" => 20,    "type" => "a"),
            "9"     => array("val" => $referencia,              "len" => 300,   "type" => "a"),
            "10"    => array("val" => $valor_porcc,             "len" => 13,    "type" => "a"),
            "11"    => array("val" => $valor,                   "len" => 13,    "type" => "a"),
            "12"    => array("val" => $moneda,                  "len" => 3,     "type" => "a"),
            "13"    => array("val" => $fecha_proceso,           "len" => 10,    "type" => "a"),
            "15"    => array("val" => $mensaje,                 "len" => 80,    "type" => "a"),
            "16"    => array("val" => $referencia_adicional,    "len" => 100,   "type" => "a"),
            "17"    => array("val" => $numero_documento,        "len" => 11,    "type" => "a"),
            "18"    => array("val" => $tipo_pago,               "len" => 3,     "type" => "a"),
            "19"    => array("val" => $numero_cuenta,           "len" => 16,    "type" => "a"),
            "20"    => array("val" => $no_documento,            "len" => 11,    "type" => "a"),
            "21"    => array("val" => $estado_impresion,        "len" => 9,     "type" => "a"),
            "22"    => array("val" => $secuencial_cobro,        "len" => 10,    "type" => "a"),
            "23"    => array("val" => $numero_comprobante,      "len" => 11,    "type" => "a"),
        );
        
        $identificador  = '';// enviar 'bbva', para eliminar caracteres especiales en base a la especificacion de bbva
        $temp_record    = $this->ITB_tbl_body($array_data, $identificador,$separador)[1] . PHP_EOL;

        return $temp_record;
    }

    // funcion privada para el banco del pacifico
    public function cash_banco_pacifico_indi_cu($datos_arr_indi,$separador=''){
        // var_dump($datos_arr_indi);exit;
        $tipo_proceso           = $datos_arr_indi['tipo_proceso'];
        $codigo_banco           = $datos_arr_indi['codigo_banco'];  
        $tipo_cuenta            = $datos_arr_indi['tipo_cuenta'];
        $num_cuenta             = $datos_arr_indi['num_cuenta'];
        $deuda_total            = $datos_arr_indi['deuda_total'];
        $codigo_contrato        = $datos_arr_indi['codigo_contrato'];
        $tipo_identificaicon    = $datos_arr_indi['tipo_identificaicon'];
        $identificacion         = $datos_arr_indi['identificacion'];
        $nombre_cliente         = $datos_arr_indi['nombre_cliente'];
        $telefono               = $datos_arr_indi['telefono'];
        $separador_monto        = $datos_arr_indi['separador_monto'];
        $identificador_deuda    = $datos_arr_indi['identificador_deuda'];
        $subtotal               = $datos_arr_indi['subtotal'];        
        $monto_iva              = $datos_arr_indi['monto_iva'];        
        $tipo_servicio          = $datos_arr_indi['tipo_servicio'];


        // $TYPE_VAR = $tip_cuenta?'n':'a';

        $array_data = array(
            "1"     => array("val" => $tipo_proceso,        "len" => 2,     "type" => "a"),
            "2"     => array("val" => $codigo_banco,        "len" => 2,     "type" => "a"),
            "3"     => array("val" => $tipo_cuenta,         "len" => 2,     "type" => "n"),
            "4"     => array("val" => $num_cuenta,          "len" => 0,     "type" => "n"),
            "5"     => array("val" => $deuda_total,         "len" => 0,     "type" => "n",  "deci" => 2,    "deli" =>$separador_monto),                              
            "6"     => array("val" => $codigo_contrato,     "len" => 0,     "type" => "n"), 
            "7"     => array("val" => $tipo_identificaicon, "len" => 1,     "type" => "a"),
            "8"     => array("val" => $identificacion,      "len" => 14,    "type" => "a", "alig"   => "de"),//alieacion a la izquierda: iz; alineacion a la derecha: de (solo alfanumericos)
            "9"     => array("val" => $nombre_cliente,      "len" => 30,    "type" => "a", "sc"     => "s"), //sc (elimincacion de caracteres especiales (s/n))
            "10"    => array("val" => $telefono,            "len" => 10,    "type" => "n"),
            // "11"    => array("val" => $separador_monto,  "len" => 1,     "type" => "a"),
            "11"    => array("val" => $identificador_deuda, "len" => 0,     "type" => "a", "sc"     => "s"),
            "12"    => array("val" => $subtotal,            "len" => 0,     "type" => "n",  "deci" => 2,    "deli" =>$separador_monto),
            "13"    => array("val" => $monto_iva,           "len" => 0,     "type" => "n",  "deci" => 2,    "deli" =>$separador_monto),
            "14"    => array("val" => $tipo_servicio,       "len" => 1,     "type" => "a")
        );
        
        // var_dump($array_data);exit;
        // echo json_encode($array_data,true);exit;

        $identificador  = '';// enviar 'bbva', para eliminar caracteres especiales en base a la especificacion de bbva
        $temp_record    = $this->ITB_tbl_body($array_data, $identificador,$separador)[1] . PHP_EOL;

        return $temp_record;

    }

    // funcion privada para el banco del pacifico
    public function cash_banco_pacifico_indi($datos_arr_indi,$separador=''){
        // var_dump($datos_arr_indi);exit;
        // var_dump($datos_arr_indi['localidad']);exit;
        //tercero = cliente
        $localidad          = $datos_arr_indi['localidad'];//La localidad- Guayaquil 1/Quito 5
        $transacción        = $datos_arr_indi['transacción'];// estatico 'OCP'
        
        
        /*  'codigo_servicio (obligarorio)'
        *   OC= Orden de Cobro (Débito a Cuenta); 
        *   ZG= Recaudación con Información (Recaudación a través de Canales con ingreso de información) 
        *   SC= Recaudación de Colegios (Recaudación a través de Canales con o sin información) */
        $cod_servicio       = $datos_arr_indi['codigo_servicio'];

        /*  'tipo_cuenta (obligarorio)'
        *   00: Cuenta Corriente 
        *   10: Cuenta de Ahorros 
        *   Aplica para débitos a cuenta, en caso de recaudaciones en canales electrónicos (Ventanilla, Intermático, etc.) 
        *   'dejar este campo en blanco' */
        $tip_cuenta         = $datos_arr_indi['tipo_cuenta'];

        /*  'numero_cuenta (obligarorio)'
        *   Número de cuenta para Banco del Pacifico. Aplica para débitos a
        *   cuenta, en caso de recaudaciones en canales electrónicos (Ventanilla,
        *   Intermático, etc.) dejar este campo en blanco. */
        $num_cuenta         = $datos_arr_indi['numero_cuenta'];

        /*  'valor (obligarorio)'
        *   Valor total movimiento: 13 enteros, 2 decimales (sin separación de punto o coma). */
        $valor              = $datos_arr_indi['valor'];

        /*  'codigo_tercero (obligarorio)'
        *   Código del cliente, número de contrato, etc. */
        $cod_tercero        = $datos_arr_indi['codigo_tercero'];

        /*  'ref_est_cuenta (obligarorio)'
        *   Referencia Corta para el estado de Cuenta del tercero (Cliente al que se le realiza el débito). */
        $ref_est_cuenta     = $datos_arr_indi['ref_est_cuenta'];

        /*  'forma_pago (obligatorio)'
        *   Ejemplo :
        *   CU : Débitos a Cuentas
        *   RE: Recaudaciones a través de Canales Electrónicos. */
        $forma_pago         = $datos_arr_indi['forma_pago'];

        /*  'moneda_movi (obligarorio)'
        *   USD : Dólares */
        $mone_mov           = $datos_arr_indi['moneda_movi'];

        /*  'nom_tercero (opcional)'
        *   Nombre del tercero (cliente a quien se le debita o quien realiza el pago a través de los canales) */
        $nom_tercero        = $datos_arr_indi['nom_tercero'];

        /*  'loc_reti_cheque (-----)'
        *   No aplica, dejar espacio en blanco */
        $loc_reti_cheque    = $datos_arr_indi['loc_reti_cheque'];

        /*  'age_reti_cheque (-----)'
        *   No aplica, dejar espacio en blanco */
        $age_reti_cheque    = $datos_arr_indi['age_reti_cheque'];

        /*  'tipo_nuc_tercero (obligatorio)'
        *   Tipo de NUC del Tercero (cliente).
        *   C-> Cédula
        *   R ->RUC
        *   P ->Pasaporte */
        $tipo_nuc_tercero   = $datos_arr_indi['tipo_nuc_tercero'];

        /*  'num_uni_tercero (obligatorio)'
        *   Identificación del Tercero (NUC. Del cliente) */
        $num_uni_tercero    = $datos_arr_indi['num_uni_tercero'];

        /*  'telefono_clie (opcional)'
        *   Teléfono del tercero (cliente) */
        $telefono_clie      = $datos_arr_indi['telefono_clie'];

        $TYPE_VAR = $tip_cuenta?'n':'a';

        $array_data = array(
            "1"     => array("val" => $localidad,           "len" => 1,     "type" => "a"),
            "2"     => array("val" => $transacción,         "len" => 3,     "type" => "a"),
            "3"     => array("val" => $cod_servicio,        "len" => 2,     "type" => "a"),
            "4"     => array("val" => $tip_cuenta,          "len" => 2,     "type" => $TYPE_VAR),
            "5"     => array("val" => $num_cuenta,          "len" => 8,     "type" => $TYPE_VAR),                              
            "6"     => array("val" => $valor,               "len" => 15,    "type" => "n",  "deci" => 2,    "deli" =>''), 
            "7"     => array("val" => $cod_tercero,         "len" => 15,    "type" => "a"),
            "8"     => array("val" => $ref_est_cuenta,      "len" => 20,    "type" => "a"),
            "9"     => array("val" => $forma_pago,          "len" => 2,     "type" => "a"),
            "10"    => array("val" => $mone_mov,            "len" => 3,     "type" => "a"),
            "11"    => array("val" => $nom_tercero,         "len" => 30,    "type" => "a"),
            "12"    => array("val" => $loc_reti_cheque,     "len" => 2,     "type" => "a"),
            "13"    => array("val" => $age_reti_cheque,     "len" => 2,     "type" => "a"),
            "14"    => array("val" => $tipo_nuc_tercero,    "len" => 1,     "type" => "a"),
            "15"    => array("val" => $num_uni_tercero,     "len" => 14,    "type" => "a"),
            "16"    => array("val" => $telefono_clie,       "len" => 10,    "type" => "a")
        );
        
        // var_dump($array_data);exit;
        // echo json_encode($array_data,true);exit;

        $identificador  = '';// enviar 'bbva', para eliminar caracteres especiales en base a la especificacion de bbva
        $temp_record    = $this->ITB_tbl_body($array_data, $identificador,$separador)[1] . PHP_EOL;

        return $temp_record;

    }



    /*********************************************************************************/

    private function ITB_tbl_body($array, $identifier,$separador='')
    {
        $contador = 0;

        $tb_body1 = '';
        $tb_data = '';
        $decim_tmp = '0';
        foreach ($array as $key => $value) {

            $v      = $value['val'];
            $l      = intval($value['len']);
            $t      = $value['type'];
            $d      = $value['deci'];
            $dl     = $value['deli'];
            $alig   = $value['alig']?$value['alig']:'iz';
            $sc     = $value['sc']?$value['sc']:'n';
            $sc     = $identifier=='bbva'?'s':$sc; //eliminar special_char (s/n)

            if ($t == 'n') {                

                $v_tmp = explode('.', $v);
                if (empty($d)) {
                    $v = intval($v_tmp[0]);
                } else {
                    $decim_tmp = $v_tmp[1];
                    $decimSize = mb_strlen($decim_tmp);
                    if ($decimSize > $d) {
                        $decim_tmp = substr($v_tmp[1], 0, $d);
                    } else {
                        $decim_tmp = $decim_tmp . $this->setDataLenght("", ($d - $decimSize), "n");
                    }
                    $v = intval($v_tmp[0]) . $dl . $decim_tmp;

                }
            }
            if ($t == 'a') {
                if ($l > 0) {
                    $v = mb_strlen($v) > $l ? substr($v, 0, $l) : $v;
                }

                $v = $sc == 's' ? $this->BBVASpecialCharConvertion($v) : $v;
            }
            $result = $l <= 0 ? $v : $this->setDataLenght($v, $l, $t,$alig);
            $tb_body1 .= '<td style="width: 4.5%;" name="' . $key . '">' . str_replace(" ", "&nbsp;", $result,) . '</td>';
        
            if($contador != 0){
                $tb_data .= $separador;
            }
            $tb_data .= $result;

            $contador++;

        }

        return array($tb_body1, $tb_data);
    }

    private function setDataLenght($string, $len, $is_str,$alig='iz')
    {
        $strlen     = mb_strlen($string);
        $miss_char  = $len - $strlen;
        return $is_str == "a" ? ($alig=='iz'?$string . str_repeat(" ", $miss_char):str_repeat(" ", $miss_char).$string): str_repeat("0", $miss_char) . $string;
    }
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
        $n2 = ['ñ','¥'];
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

    private function source_information($idempresa){
        // informacion de la api de laarcouier
        $info = $idempresa;
        $oConexion1 = $this->oConexion1;
        $empr_laar_sn = '';
        $empr_laar_url = '';
        $empr_laar_user = '';
        $empr_laar_pass = '';

        $sql_empr = "select 
                        empr_laar_sn,
                        empr_laar_url,
                        empr_laar_user,
                        empr_laar_pass 
                    from saeempr 
                    where 
                        empr_cod_empr = $idempresa";


        if ($oConexion1->Query($sql_empr)) {
            if ($oConexion1->NumFilas() > 0) {
                do {

                    $empr_laar_sn   = $oConexion1->f('empr_laar_sn');
                    $empr_laar_url  = $oConexion1->f('empr_laar_url');
                    $empr_laar_user = $oConexion1->f('empr_laar_user');
                    $empr_laar_pass = $oConexion1->f('empr_laar_pass');


                } while ($oConexion1->SiguienteRegistro());
            }
        }

        $oConexion1->Free();

        return $info;
    }


}

?>