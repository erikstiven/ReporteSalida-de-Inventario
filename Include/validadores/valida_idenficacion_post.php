<?php
include_once('ValidarIdentificacion.php');

session_start();
$S_URL_API_SRI_SN = $_SESSION['S_URL_API_SRI_SN'];
$S_URL_API_SRI = $_SESSION['S_URL_API_SRI'];
$S_PAIS_API_SRI = $_SESSION['S_PAIS_API_SRI'];

if($S_PAIS_API_SRI == '593' && $S_URL_API_SRI_SN == 'S' && $S_URL_API_SRI) {
    $tipo_identifiacion = $_GET['tipo_identificacion'];
    $numero = $_GET['numero'];

    if ($numero != '' && $tipo_identifiacion != '') {
        $validarCedula = new ValidarIdentificacion($S_URL_API_SRI);
        if ($tipo_identifiacion == '02') {
            /** VALIDAR CEDULA */
            $cedula_consulta = $validarCedula->validarCedulaEcuador($numero);

            if ($cedula_consulta) {
                echo json_encode(array(1, "Exito",$validarCedula->getInformacion()));
            } else {
                $mensaje_error = $validarCedula->getError();
                echo json_encode(array(2, $mensaje_error,''));
            }

        } else if ($tipo_identifiacion == '01') {
            /**Validar si el RUC EXISTE*/
            $ruc_consulta = $validarCedula->validarRucEcuador($numero);
            //Esto se cambió
            //$ruc_consulta = true;
            //echo $ruc_consulta; exit;
            if ($ruc_consulta) {
                echo json_encode(array(1, "Exito",$validarCedula->getInformacion()));
            } else {
                $mensaje_error = $validarCedula->getError();
                echo json_encode(array(2, $mensaje_error,''));
            }

        }

    } else {
        echo json_encode(array(0, "Se requiere Tipo de identificacion y numero de identificacion",''));
    }
}else{
    echo json_encode(array(1, "Exito",''));
}