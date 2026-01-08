<?php
include_once('../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
include_once(path(DIR_INCLUDE) . 'html2pdf_v4.03/_tcpdf_5.0.002_old/tcpdf.php');

global $DSN_Ifx, $DSN;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();

$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();

$oCon = new Dbo;
$oCon->DSN = $DSN;
$oCon->Conectar();

$idEmpresa = $_SESSION['U_EMPRESA'];
$idSucursal = $_SESSION['U_SUCURSAL'];
$tipo = $_GET['tipo'];
$cod_pedf = $_GET['cod_pedf'];
$direccion_envio = $_GET['direccion_envio'];

if (empty($cod_pedf)) {
    $cod_pedf = 0;
}

if (empty($direccion_envio)) {
    $direccion_envio = 0;
}


if (isset($_GET['data_array'])) {
    // Decodificamos el array desde JSON a un array PHP
    $data_array = json_decode($_GET['data_array'], true);
} else {
    $data_array = array();
}

$cod_clpv_add = $_GET['cod_clpv_add'];
if (empty($cod_clpv_add)) {
    $cod_clpv_add = 0;
}


$prclp_cod_clpv = $_GET['prclp_cod_clpv'];
$prclp_fech_ingr = $_GET['prclp_fech_ingr'];

// INDEXACION DE BODEGAS
$sql_saebode = "SELECT 
                    bode_nom_bode,
                    bode_cod_bode
                FROM 
                    saebode
                WHERE 
                    bode_cod_empr = $idEmpresa
                ";
$array_data_bodegas = array();
if ($oIfx->Query($sql_saebode)) {
    if ($oIfx->NumFilas() > 0) {
        do {
            $bode_nom_bode = $oIfx->f('bode_nom_bode');
            $bode_cod_bode = $oIfx->f('bode_cod_bode');
            $array_data_bodegas[$bode_cod_bode] = $bode_nom_bode;
        } while ($oIfx->SiguienteRegistro());
    }
}
$oIfx->Free();


// INDEXACION DE VARIABLES
$sql_config_var_reporte = "SELECT 
                                    varpr_cod_varpr,
                                    varpr_nom_varpr,
                                    varpr_cod_prclp
                                FROM 
                                    saevarpr
                                WHERE 
                                    varpr_cod_empr = $idEmpresa
                                    and varpr_cod_sucu = $idSucursal
                                ";
$array_data_varpr = array();
$array_data_opt = array();
if ($oIfx->Query($sql_config_var_reporte)) {
    if ($oIfx->NumFilas() > 0) {
        do {
            $varpr_cod_varpr = $oIfx->f('varpr_cod_varpr');
            $varpr_nom_varpr = $oIfx->f('varpr_nom_varpr');
            $varpr_cod_prclp = $oIfx->f('varpr_cod_prclp');
            $array_data_varpr[$varpr_cod_varpr] = $varpr_nom_varpr;
            $array_data_opt[$varpr_cod_prclp] = $varpr_nom_varpr;
        } while ($oIfx->SiguienteRegistro());
    }
}
$oIfx->Free();


// INDEXACION DE POR PEDIDO
$sql_config_var_reporte = "SELECT 
                                    cod_producto,
                                    cod_variable,
                                    cod_opcion
                                FROM 
                                    variable_prod_ped
                                WHERE 
                                    cod_pedido = $cod_pedf
                                ";
$array_data_variable_pedido = array();
if ($oIfx->Query($sql_config_var_reporte)) {
    if ($oIfx->NumFilas() > 0) {
        do {
            $cod_producto = $oIfx->f('cod_producto');
            $cod_variable = $oIfx->f('cod_variable');
            $cod_opcion = $oIfx->f('cod_opcion');
            $array_data_variable_pedido[$cod_producto][$cod_variable] = $cod_opcion;
        } while ($oIfx->SiguienteRegistro());
    }
}
$oIfx->Free();



// 
$sql_saepedf = "SELECT 
                                pedf_nom_cliente, 
                                pedf_fech_fact 
                            from 
                                saepedf
                            where 
                                pedf_cod_pedf = $cod_pedf
                                ;";
$nombre_cliente = '';
$fecha_pedido = '';
if ($oIfx->Query($sql_saepedf)) {
    if ($oIfx->NumFilas() > 0) {
        do {
            $nombre_cliente = $oIfx->f('pedf_nom_cliente');
            $fecha_pedido = $oIfx->f('pedf_fech_fact');
        } while ($oIfx->SiguienteRegistro());
    }
}
$oIfx->Free();




//DATOS DE LA EMPRESA
$sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr, 
			empr_conta_sn, empr_num_resu, empr_path_logo
			from saeempr 
			where empr_cod_empr = $idEmpresa ";
if ($oIfx->Query($sql)) {
    if ($oIfx->NumFilas() > 0) {
        $razonSocial = trim($oIfx->f('empr_nom_empr'));
        $ruc_empr = $oIfx->f('empr_ruc_empr');
        $dirMatriz = trim($oIfx->f('empr_dir_empr'));
        $empr_path_logo = $oIfx->f('empr_path_logo');
    }
}
$oIfx->Free();

//LOGO DEL REPORTE
$path_img = explode("/", $empr_path_logo);
$count = count($path_img) - 1;
$arc_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

if (file_exists($arc_img)) {
    $imagen = $arc_img;
} else {
    $imagen = '';
}
$logo = '';
$x = '0px';
if ($imagen != '') {

    $empr_logo = '<div align="center">
            <img src="' . $imagen . '" style="
            width:100px;
            object-fit; contain;">
            </div>';
    $x = '0px';
} else {
    $empr_logo = 'LOGO NO CARGADO';
}


//CONTRATO COMPOSTURA
if ($tipo == 1) {
    //CABECERA


    // INDEXACION DE POR PEDIDO
    /*
    $prclp_cod_clpv = $_GET['prclp_cod_clpv'];
    $prclp_fech_ingr = $_GET['prclp_fech_ingr'];
    */
    $sql_config_var_reporte = "SELECT 
                                    prclp_fech_ingr,
                                    prclp_fech_act,
                                    prclp_com_prclp
                                FROM 
                                    saeprclp
                                WHERE 
                                    prclp_cod_clpv = '$prclp_cod_clpv'
                                    and prclp_fech_ingr = '$prclp_fech_ingr'
                                LIMIT 1
                                ";
    $fecha_inicio_envio = '';
    $fecha_entrega_envio = '';
    $comentario_envio = '';
    if ($oIfx->Query($sql_config_var_reporte)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $fecha_inicio_envio = $oIfx->f('prclp_fech_ingr');
                $fecha_entrega_envio = $oIfx->f('prclp_fech_act');
                $comentario_envio = $oIfx->f('prclp_com_prclp');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();


    $sql_clpv = "SELECT clpv_nom_clpv from saeclpv WHERE clpv_cod_clpv = '$prclp_cod_clpv'";
    $clpv_nom_clpv = consulta_string_func($sql_clpv, 'clpv_nom_clpv', $oIfxA, '');


    $html = '<table style="font-size: 150%;" border="0" width="100%;">';
    $html .= '<tr>';
    $html .= '<td width="45%">' .  $empr_logo . '</td>';
    $html .= '<td width="55%"><b>' .  $razonSocial . '</b><br><font size="8">' . espacios_pro(20) . 'Genuine Panama Hats desde 1939</font></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td></td>';
    $html .= '</tr>';

    $html .= '</table>';

    $html .= '<table style="font-size: 110%;" border="0" width="100%;" cellpadding="1" cellspacing="1"> ';

    $html .= '<tr style="background-color:black" >';
    $html .= '<td align="center" style="font-size: 120%;color:white" colspan="9"><b>Contrato de Compostura</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td colspan="9"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="7%"></td>';
    $html .= '<td bgcolor="#d8d8d8" style="border: black 1px solid ;" width="7%" align="center">Fecha</td>';
    $html .= '<td style="border: black 1px solid ;" width="33%">' . $fecha_inicio_envio . '</td>';
    $html .= '<td width="7%"></td>';
    $html .= '<td bgcolor="#d8d8d8" style="border: black 1px solid ;" width="12%" align="center">Re-Compostura</td>';
    $html .= '<td bgcolor="#d8d8d8" style="border: black 1px solid ;" width="7%"></td>';
    $html .= '<td style="border: black 1px solid ;"width="14%"></td>';
    $html .= '<td width="6%"></td>';
    $html .= '<td width="7%"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td colspan="9"></td>';
    $html .= '</tr>';

    $hora_actual = date('H:i:s');
    $html .= '<tr>';
    $html .= '<td width="7%"></td>';
    $html .= '<td bgcolor="#d8d8d8" style="border: black 1px solid ;" width="7%" align="center">Nombre</td>';
    $html .= '<td style="border: black 1px solid ;" width="33%">' . $clpv_nom_clpv . '</td>';
    $html .= '<td width="7%"></td>';
    $html .= '<td bgcolor="#d8d8d8" style="border: black 1px solid ;" width="12%" align="center">Fecha entrega:</td>';
    $html .= '<td bgcolor="#d8d8d8" style="border: black 1px solid ;" width="7%"></td>';
    $html .= '<td style="border: black 1px solid ;" width="14%">' . date('Y-m-d', strtotime($fecha_entrega_envio)) . '</td>';
    $html .= '<td bgcolor="#d8d8d8" width="6%" align="center">Hora:</td>';
    $html .= '<td style="border: black 1px solid ;" width="7%">' . $hora_actual . '</td>';
    $html .= '</tr>';

    $html .= '</table>';

    //DETALLE
    $html .= '<br><br><table style="font-size: 100%;" border="1" width="100%;" cellpadding="1" cellspacing="0"> ';

    $html .= '<tr style="background-color:#d8d8d8" >';
    $html .= '<td align="center" width="7%">Line</td>';
    $html .= '<td align="center" width="7%">#Orden<br>Produccion</td>';
    $html .= '<td align="center" width="5%">Cant.</td>';
    $html .= '<td align="center" width="5%">Doc.</td>';
    $html .= '<td align="center" width="11%">Tejido</td>';
    $html .= '<td align="center" width="5%">Grado</td>';
    $html .= '<td align="center" width="7%">Entrada</td>';
    $html .= '<td align="center" width="7%">Color</td>';
    $html .= '<td align="center" width="5%">Copa</td>';
    $html .= '<td align="center" width="7%">Ala</td>';
    $html .= '<td align="center" width="7%">Encolado</td>';
    $html .= '<td align="center" width="8%">#Pago<br>Compostura</td>';
    $html .= '<td align="center" colspan="2"width="12%">Entrega Fecha/Cant.</td>';
    $html .= '<td align="center" width="7%">Saldo</td>';
    $html .= '</tr>';

    $sql_config_var_reporte = "SELECT 
                                    *
                                FROM 
                                    saeprclp
                                WHERE 
                                    prclp_cod_clpv = '$prclp_cod_clpv'
                                    and prclp_fech_ingr = '$prclp_fech_ingr'
                                ";
    $fecha_inicio_envio = '';
    $fecha_entrega_envio = '';
    $comentario_envio = '';
    if ($oIfx->Query($sql_config_var_reporte)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $prclp_cod_bode = $oIfx->f('prclp_cod_bode');
                $prclp_cod_prod = $oIfx->f('prclp_cod_prod');
                $prclp_can_prclp = $oIfx->f('prclp_can_prclp');

                $html .= '<tr>';
                $html .= '<td align="center" width="7%">' . $prclp_cod_prod . '</td>';
                $html .= '<td align="center" width="7%"></td>';
                $html .= '<td align="center" width="5%">' . $prclp_can_prclp . '</td>';
                $html .= '<td align="center" width="5%"></td>';
                $html .= '<td align="center" width="11%"></td>';
                $html .= '<td align="center" width="5%"></td>';
                $html .= '<td align="center" width="7%"></td>';
                $html .= '<td align="center" width="7%"></td>';
                $html .= '<td align="center" width="5%"></td>';
                $html .= '<td align="center" width="7%"></td>';
                $html .= '<td align="center" width="7%"></td>';
                $html .= '<td align="center" width="8%"></td>';
                $html .= '<td align="center" width="6%"></td>';
                $html .= '<td align="center" width="6%"></td>';
                $html .= '</tr>';

                //
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $html .= '<tr>';
    $html .= '<td align="center" width="7%"></td>';
    $html .= '<td align="center" width="7%"></td>';
    $html .= '<td align="center" width="5%"></td>';
    $html .= '<td align="center" width="5%"></td>';
    $html .= '<td align="center" width="11%"></td>';
    $html .= '<td align="center" width="5%"></td>';
    $html .= '<td align="center" width="7%"></td>';
    $html .= '<td align="center" width="7%"></td>';
    $html .= '<td align="center" width="5%"></td>';
    $html .= '<td align="center" width="7%"></td>';
    $html .= '<td align="center" width="7%"></td>';
    $html .= '<td align="center" width="8%"></td>';
    $html .= '<td align="center" width="6%"></td>';
    $html .= '<td align="center" width="6%"></td>';
    $html .= '</tr>';

    $html .= '</table>';

    //PIE

    $html .= '<br><br><table style="font-size: 90%;" border="0" width="100%;" cellpadding="1" cellspacing="0"> ';
    $html .= '<tr>';
    $html .= '<td  width="7%"></td>';
    $html .= '<td style="border: black 2px solid ;" width="40%"><b>Nota: ' . $comentario_envio . '</b></td>';
    $html .= '<td  width="53%" align="justify">Declaro recibir sombreros campanas, según lo detallado en el presente documento.<br>Me comprometo a cumplir la fecha de entrega acordada y realizar el trabajo con calidad satisfactoria,<br>según la "Especificación de Compostura" E005PRO y "Tarifas de Producción" F028PRO.</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td  width="100%"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td  width="100%"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td  width="100%"></td>';
    $html .= '</tr>';


    $html .= '<tr>';
    $html .= '<td  align="center" width="10%"></td>';
    $html .= '<td  align="center" width="16%"><b>________________________</b></td>';
    $html .= '<td  align="center" width="16%"></td>';
    $html .= '<td  align="center" width="16%"><b>________________________</b></td>';
    $html .= '<td  align="center" width="16%"></td>';
    $html .= '<td  align="center" width="16%"><b>________________________</b></td>';
    $html .= '<td  align="center" width="10%"></td>';
    $html .= '</tr>';


    $html .= '<tr>';
    $html .= '<td  align="center" width="10%"></td>';
    $html .= '<td  align="center" width="16%">Responsable ' . $razonSocial . '</td>';
    $html .= '<td  align="center" width="16%"></td>';
    $html .= '<td  align="center" width="16%">Re - Conteo</td>';
    $html .= '<td  align="center" width="16%"></td>';
    $html .= '<td  align="center" width="16%">Compositor</td>';
    $html .= '<td  align="center" width="10%"></td>';
    $html .= '</tr>';

    $html .= '</table>';
    $orientacion = 'L';
} //CIERRE IF TIPO 1

//CONTRATO AZOCADO
elseif ($tipo == 2) {
    //CABECERA


    $sql_cantidad_enviada = "SELECT
                                id,
                                codigo_clpv,
                                nombre_clpv,
                                fecha_movimiento,
                                fecha_entrega,
                                comentario
                            FROM comercial.movimiento_azocado
                            WHERE
                                id = $cod_pedf
                            ";

    if ($oIfx->Query($sql_cantidad_enviada)) {
        if ($oIfx->NumFilas() > 0) {
            do {

                $numero_azocado = str_pad($oIfx->f('id'), 9, '0', STR_PAD_LEFT);

                $id_movimiento_azo = $oIfx->f('id');
                $codigo_clpv = $oIfx->f('codigo_clpv');
                $nombre_clpv = $oIfx->f('nombre_clpv');
                $nombre_prod = $oIfx->f('nombre_prod');
                $fecha_movimiento = $oIfx->f('fecha_movimiento');
                $fecha_entrega = $oIfx->f('fecha_entrega');
                $comentario = $oIfx->f('comentario');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $html = '<table style="font-size: 150%;" border="0" width="100%;">';
    $html .= '<tr>';
    $html .= '<td width="20%">' .  $empr_logo . '</td>';
    $html .= '<td width="80%" ><b>' .  $razonSocial . '</b><br><font size="8">Genuine Panama Hats desde 1939</font></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td style="color:red" width="100%" align="right"><b>' . $numero_azocado . '</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td></td>';
    $html .= '</tr>';

    $html .= '</table>';

    $html .= '<table style="font-size: 90%;" border="0" width="100%;" cellpadding="1" cellspacing="0"> ';

    $html .= '<tr  >';
    $html .= '<td style="font-size: 160%; border: black 1px solid;" align="center" colspan="4"><b>Contrato de Azocado</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td colspan="4"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td bgcolor="#d8d8d8" style="font-size: 130%; border: black 1px solid ;" width="20%" align="center">Fecha :</td>';
    $html .= '<td style="font-size: 130%; border: black 1px solid ;" width="30%">' . $fecha_movimiento . '</td>';

    $html .= '<td bgcolor="#d8d8d8" style="font-size: 130%; border: black 1px solid ;" width="20%" align="center">Fecha de Entrega:</td>';
    $html .= '<td style="font-size: 130%; border: black 1px solid ;" width="30%">' . $fecha_entrega . '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td colspan="4"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td bgcolor="#d8d8d8" style="font-size: 130%; border: black 1px solid ;" width="20%" align="center">Azocador</td>';
    $html .= '<td style="font-size: 130%; border: black 1px solid ;" width="80%" >' . $nombre_clpv . '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td style="font-size: 90%; " width="100%" >Declaro haber recibido de Exportadora K. Dorfzaun S.A. para Azocar y entregar a entera satisfacción,
    <br>de acuerdo a las tarifas vigentes, el siguiente sombrero:</td>';
    $html .= '</tr>';

    $html .= '</table>';

    //DETALLE

    $html .= '<br><br><table style="font-size: 100%;" border="1" width="100%;" cellpadding="1" cellspacing="0"> ';

    $html .= '<tr style="background-color:#d8d8d8" >';
    $html .= '<td align="center" width="20%">Cant. (doc)</td>';
    $html .= '<td align="center" width="50%">Descripción sombrero</td>';
    $html .= '<td align="center" width="30%">Notas</td>';
    $html .= '</tr>';

    $sql_cantidad_enviada = "SELECT
                                id,
                                nombre_prod,
                                cantidad,
                                comentario
                            FROM comercial.detalles_azocado
                            WHERE
                                id_movimiento_azocado = $cod_pedf
                            ";

    if ($oIfx->Query($sql_cantidad_enviada)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $id = $oIfx->f('id');
                $nombre_prod = $oIfx->f('nombre_prod');
                $cantidad = $oIfx->f('cantidad');
                $comentario = $oIfx->f('comentario');

                // Calcular el número de docenas
                $docenas = intdiv($cantidad, 12);
                // Calcular el restante en unidades
                $restantes = $cantidad % 12;

                $html .= '<tr>';
                $html .= '<td bgcolor="#fff2cc" align="center" width="20%">' . $docenas . '/' . $restantes . '</td>';
                $html .= '<td align="center" width="50%">' . $nombre_prod . '</td>';
                $html .= '<td align="center" width="30%">' . $comentario . '</td>';
                $html .= '</tr>';
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();



    $html .= '<tr style="background-color:#E4DDDB">';
    $html .= '<td align="center" width="20%"><b>Total: 27/7</b></td>';
    $html .= '<td align="center" width="50%"></td>';
    $html .= '<td align="center" width="30%"></td>';
    $html .= '</tr>';

    $html .= '</table>';

    //FIRMAS

    $html .= '<br><br><table style="font-size: 90%;" border="0" width="100%;" cellpadding="0" cellspacing="0"> ';

    $html .= '<tr>';
    $html .= '<td  width="100%"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td  width="100%"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td  width="100%"></td>';
    $html .= '</tr>';


    $html .= '<tr>';
    $html .= '<td  align="center" width="15%"><hr></td>';
    $html .= '<td  align="center" width="15%"><hr></td>';
    $html .= '<td  align="center" width="70%"><hr></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td  align="center" width="15%">Entrega</td>';
    $html .= '<td  align="center" width="15%">Reconteo</td>';
    $html .= '<td  align="center" width="70%">' . espacios_pro(100) . 'Azocador</td>';
    $html .= '</tr>';

    $html .= '</table>';


    $orientacion = 'P';
}

//ORDEN DE PRODUCCION
elseif ($tipo == 3) {
    //CABECERA

    $html = '<table style="font-size: 150%;" border="0" width="100%;">';
    $html .= '<tr>';
    $html .= '<td width="40%" rowspan="2">' .  $empr_logo . '</td>';
    $html .= '<td style="color:red;" width="40%"></td>';
    $html .= '<td style="color:red;" width="20%"><b>OP23900</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="65%"><b>' .  $razonSocial . '</b><br><font size="8">' . espacios_pro(20) . 'Genuine Panama Hats desde 1939</font></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td></td>';
    $html .= '</tr>';

    $html .= '</table>';

    $html .= '<table style="font-size: 90%;" border="0" width="100%;" cellpadding="1" cellspacing="1"> ';

    $html .= '<tr  >';
    $html .= '<td style="font-size: 150%; border: black 1px solid;" align="center" colspan="5"><b>Orden de Producción</b></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td colspan="5"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td bgcolor="#d8d8d8" style="font-size: 120%; border: black 1px solid ;" width="7%" align="center">Fecha :</td>';
    $html .= '<td style="font-size: 120%; border: black 1px solid ;" width="33%">' . $fecha_pedido . '</td>';
    $html .= '<td width="19%"></td>';
    $html .= '<td bgcolor="#d8d8d8" style="font-size: 120%; border: black 1px solid ;" width="12%" align="center">Encargado:</td>';
    $html .= '<td style="font-size: 120%; border: black 1px solid ;" width="29%"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td colspan="5"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td bgcolor="#d8d8d8" style="font-size: 120%; border: black 1px solid ;" width="7%" align="center">Cliente :</td>';
    $html .= '<td style="font-size: 120%; border: black 1px solid ;" width="33%">' . $nombre_cliente . '</td>';
    $html .= '<td width="19%"></td>';
    $html .= '<td bgcolor="#d8d8d8" style="font-size: 120%; border: black 1px solid ;" width="12%" align="center">Nota:</td>';
    $html .= '<td style="font-size: 120%; border: black 1px solid ;" width="29%"></td>';
    $html .= '</tr>';

    $html .= '</table>';

    //DETALLE
    $html .= '<br><br><table style="font-size: 100%;" border="1" width="100%;" cellpadding="1" cellspacing="0"> ';

    $html .= '<tr style="background-color:#d8d8d8" >';
    $html .= '<td align="center" width="7%">LINE</td>';
    $html .= '<td align="center" width="5%">CANT.</td>';
    $html .= '<td align="center" width="7%">BALANCE</td>';
    $html .= '<td align="center" width="8%">BODEGA</td>';


    $sql_config_var_reporte = "SELECT 
                                    ids_variables
                                FROM 
                                    config_var_reporte
                                WHERE 
                                    empresa = $idEmpresa
                                    and sucursal = $idSucursal
                                    and tipo_reporte = 'PRODUCCION'
                                ";

    $array_ids_variables = array();
    if ($oIfx->Query($sql_config_var_reporte)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $ids_variables = $oIfx->f('ids_variables');
                $array_ids_variables = explode(",", $ids_variables);
                foreach ($array_ids_variables as $key => $id_variable) {
                    $nombre_variable = $array_data_varpr[$id_variable];
                    $html .= '<td align="center" width="8%">' . $nombre_variable . '</td>';
                }
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();




    $html .= '<td align="center" width="13%">NOTAS</td>';
    $html .= '</tr>';




    $sql_saepedf = "SELECT 
                        dpef_cod_dpef, 
                        dpef_cod_prod, 
                        dpef_nom_prod,
                        dpef_cant_dfac,
                        dpef_cod_bode
                    from 
                        saedpef
                    where 
                        dpef_cod_pedf = $cod_pedf
                        ;";
    $nombre_cliente = '';
    $fecha_pedido = '';
    $total_cantidad_pedido = 0;
    if ($oIfx->Query($sql_saepedf)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $dpef_cod_bode = $oIfx->f('dpef_cod_bode');
                $dpef_cod_dpef = $oIfx->f('dpef_cod_dpef');
                $dpef_cod_prod = $oIfx->f('dpef_cod_prod');
                $dpef_cant_dfac = round($oIfx->f('dpef_cant_dfac'), 2);

                $bode_nom_bode = $array_data_bodegas[$dpef_cod_bode];

                $html .= '<tr>';
                $html .= '<td align="center" width="7%">' . $dpef_cod_dpef . '</td>';
                $html .= '<td bgcolor="gray" style="color:white" align="center" width="5%">' . $dpef_cant_dfac . '</td>';
                $html .= '<td align="center" width="7%"></td>';
                $html .= '<td align="center" width="8%">' . $bode_nom_bode . '</td>';

                $total_cantidad_pedido += $dpef_cant_dfac;

                $array_ids_variables = array();
                if ($oIfxA->Query($sql_config_var_reporte)) {
                    if ($oIfxA->NumFilas() > 0) {
                        do {
                            $ids_variables = $oIfxA->f('ids_variables');
                            $array_ids_variables = explode(",", $ids_variables);
                            foreach ($array_ids_variables as $key => $id_variable) {
                                $id_variable_opcion = $array_data_variable_pedido[$dpef_cod_prod][$id_variable];
                                $nombre_opcion = $array_data_opt[$id_variable_opcion];
                                $html .= '<td align="center" width="8%">' . $nombre_opcion . '</td>';
                            }
                        } while ($oIfxA->SiguienteRegistro());
                    }
                }
                $oIfxA->Free();


                $html .= '<td align="center" width="13%"></td>';
                $html .= '</tr>';
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();






    //TOTALES
    $html .= '<tr style="background-color:#E4DDDB">';
    $html .= '<td align="right" width="7%">Total</td>';
    $html .= '<td align="center" width="5%"><b>' . $total_cantidad_pedido . '</b></td>';
    $html .= '<td align="center" width="7%"></td>';
    $html .= '<td align="center" width="8%"></td>';


    foreach ($array_ids_variables as $key => $id_variable) {
        $html .= '<td align="center" width="8%"></td>';
    }

    $html .= '<td align="center" width="13%"></td>';
    $html .= '</tr>';
    $html .= '</table>';

    $html .= '<table style="font-size: 90%;" border="0" width="100%;" cellpadding="1" cellspacing="0"> ';
    $html .= '<tr>';
    $html .= '<td align="right" width="100%" >F006PRO001</td>';
    $html .= '</tr>';
    $html .= '</table>';



    //FIRMAS

    $html .= '<br><br><table style="font-size: 90%;" border="0" width="100%;" cellpadding="1" cellspacing="0"> ';

    $html .= '<tr>';
    $html .= '<td  width="100%"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td  width="100%"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td  width="100%"></td>';
    $html .= '</tr>';


    $html .= '<tr>';
    $html .= '<td  align="center" width="10%"><b>_____________</b></td>';
    $html .= '<td  align="center" width="7%"></td>';
    $html .= '<td  align="center" width="10%"><b>_____________</b></td>';
    $html .= '<td  align="center" width="7%"></td>';
    $html .= '<td  align="center" width="10%"><b>_____________</b></td>';
    $html .= '<td  align="center" width="16%"></td>';

    $html .= '<td  align="center" width="10%"><b>_______________</b></td>';
    $html .= '<td  align="center" width="5%"></td>';
    $html .= '<td  align="center" width="10%"><b>_____________</b></td>';
    $html .= '<td  align="center" width="15%"></td>';
    $html .= '</tr>';


    $html .= '<tr>';
    $html .= '<td  align="center" width="10%">Autorización</td>';
    $html .= '<td  align="center" width="7%"></td>';
    $html .= '<td  align="center" width="10%">Bleach</td>';
    $html .= '<td  align="center" width="7%"></td>';
    $html .= '<td  align="center" width="10%">Teñido</td>';
    $html .= '<td  align="center" width="16%"></td>';

    $html .= '<td  align="center" width="10%">Liberación de Lote</td>';
    $html .= '<td  align="center" width="5%"></td>';
    $html .= '<td  align="center" width="10%">Revisión</td>';
    $html .= '<td  align="center" width="15%"></td>';
    $html .= '</tr>';

    $html .= '</table>';
    $orientacion = 'L';
} //CIERRE IF TIPO 3


//PACKING LIST
elseif ($tipo == 4) {
    //CABECERA

    $html = '<table style="font-size: 110%;" border="0" width="100%;">';
    $html .= '<tr>';
    $html .= '<td width="40%" rowspan="2">' .  $empr_logo . '</td>';
    $html .= '<td style="color:red;" width="40%"></td>';
    $html .= '<td style="color:black;" width="20%">
                <b>PL3068PRO</b><br>
                <b>Packing List</b>
            </td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="65%"><b>' .  $razonSocial . '</b><br><font size="8">' . $dirMatriz . '</font></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td></td>';
    $html .= '</tr>';

    $html .= '</table>';


    // ----------------------------------------------------------
    // Tabla de arriba donde vamos a ver la informacino del packing list
    // ----------------------------------------------------------

    $fecha_hoy = date('Y-m-d');

    // NOMBRE CLIENTE
    $sql_clpv = "SELECT clpv_nom_clpv, clpv_nom_come from saeclpv WHERE clpv_cod_clpv = '$cod_clpv_add'";
    if ($oIfx->Query($sql_clpv)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
                $clpv_nom_come = $oIfx->f('clpv_nom_come');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();


    $sql_add_dire =  '';
    if (!empty($direccion_envio)) {
        $sql_add_dire =  "and dire_cod_dire = $direccion_envio";
    }

    // DIRECCION CLIENTE
    $sql_clpv = "SELECT dire_dir_dire from saedire WHERE dire_cod_clpv = '$cod_clpv_add' $sql_add_dire";
    if ($oIfx->Query($sql_clpv)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $dire_dir_dire = $oIfx->f('dire_dir_dire');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();


    // TELEFONO CLIENTE
    $sql_clpv = "SELECT tlcp_tlf_tlcp from saetlcp WHERE tlcp_cod_clpv = '$cod_clpv_add'";
    if ($oIfx->Query($sql_clpv)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $tlcp_tlf_tlcp = $oIfx->f('tlcp_tlf_tlcp');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();


    // CORREO CLIENTE
    $sql_clpv = "SELECT emai_ema_emai from saeemai WHERE emai_cod_clpv = '$cod_clpv_add'";
    if ($oIfx->Query($sql_clpv)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $emai_ema_emai = $oIfx->f('emai_ema_emai');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();


    $html .= '<table style="font-size: 100%; border: none;" width="100%;"> ';

    $html .= '<tr>';
    $html .= '  <td style="font-size: 80%; border: none;" width="10%">Date:</td>';
    $html .= '  <td style="font-size: 80%; border: none;" width="40%">' . $fecha_hoy . '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '  <td style="font-size: 80%; border: none;" width="10%">For Messrs.:</td>';
    $html .= '  <td style="font-size: 80%; border: none;" width="40%">' . $clpv_nom_clpv . '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '  <td style="font-size: 80%; border: none;" width="10%">Attention Mr./Ms.:</td>';
    $html .= '  <td style="font-size: 80%; border: none;" width="40%">
                    E-Mail : ' . $emai_ema_emai . '<br>
                    ' . $clpv_nom_clpv . '<br>
                    Company name : ' . $clpv_nom_come . '.<br>
                    Contact person: ' . $clpv_nom_clpv . '<br>
                    Address : ' . $dire_dir_dire . '<br>
                    <!--
                    Country : Slovakia<br>
                    -->
                    Tel : ' . $tlcp_tlf_tlcp . '
                </td>';
    $html .= '</tr>';

    /*
    $html .= '<tr>';
    $html .= '  <td style="font-size: 80%; border: none;" width="10%">Bill to:/Ship to:</td>';
    $html .= '  <td style="font-size: 80%; border: none;" width="40%">
                    E-Mail : zdenka.celovska@mayser.com<br>
                    /Company name : MAYSER Slovakia s.r.o.<br>
                    Contact person: Zdenka Čeľovská<br>
                    Taxpayer ID : SK 2020037690<br>
                    Address : Gemerska 564 049 51Brzotin (Roznava) SK<br>
                    Postal code :<br>
                    Country : Slovakia<br>
                    Tel : +421 58 777 44 01<br>
                    E-Mail : zdenka.celovska@mayser.com
                </td>';
    $html .= '</tr>';
    */

    $html .= '<tr>';
    $html .= '  <td style="font-size: 80%; border: none;" width="10%">Invoice number:</td>';
    $html .= '  <td style="font-size: 80%; border: none;" width="40%">001003000003775</td>';
    $html .= '</tr>';

    $html .= '</table>';

    // ----------------------------------------------------------
    // FIN Tabla de arriba donde vamos a ver la informacino del packing list
    // ----------------------------------------------------------


    //DETALLE
    $html .= '<br><br><table style="font-size: 40%;" border="1" width="100%;" cellpadding="1" cellspacing="0"> ';

    $html .= '<tr style="background-color:#d8d8d8" >';
    $html .= '<td align="center">Item</td>';
    $html .= '<td align="center">Line No.</td>';
    $html .= '<td align="center">Packing Num</td>';
    $html .= '<td align="center">Package Type Code</td>';
    $html .= '<td align="center">Package Dimensions (cm)</td>';
    $html .= '<td align="center">Weight (kg)</td>';
    $html .= '<td align="center">REAL Quantity (units)</td>';


    $sql_config_var_reporte = "SELECT 
                                    ids_variables
                                FROM 
                                    config_var_reporte
                                WHERE 
                                    empresa = $idEmpresa
                                    and sucursal = $idSucursal
                                    and tipo_reporte = 'PACKING'
                                ";

    $array_ids_variables = array();
    if ($oIfx->Query($sql_config_var_reporte)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $ids_variables = $oIfx->f('ids_variables');
                $array_ids_variables = explode(",", $ids_variables);
                foreach ($array_ids_variables as $key => $id_variable) {
                    $nombre_variable = $array_data_varpr[$id_variable];
                    if ($nombre_variable != 'IMAGEN') {
                        $html .= '<td align="center">' . $nombre_variable . '</td>';
                    }
                }
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();




    $html .= '<td align="center">Unit price</td>';
    $html .= '<td align="center">Total price</td>';
    $html .= '<td align="center">Note</td>';
    $html .= '</tr>';








    $sql_in_pedidos = '';
    foreach ($data_array as $key_prod => $data_dpef) {
        $cod_dpef = $data_dpef['cod_dpef'];
        $sql_codigo_pedido = "SELECT dpef_cod_pedf from saedpef where dpef_cod_dpef = $cod_dpef";
        $dpef_cod_pedf = consulta_string_func($sql_codigo_pedido, 'dpef_cod_pedf', $oIfx, 0);
        if (!empty($dpef_cod_pedf)) {
            $sql_in_pedidos .= $dpef_cod_pedf . ',';
        }
    }
    $sql_in_pedidos = substr($sql_in_pedidos, 0, -1);
    if (empty($sql_in_pedidos)) {
        $sql_in_pedidos = '9999999999999999999999999';
    }



    // INDEXACION DE POR PEDIDO
    $sql_config_var_pedido = "SELECT 
                                    cod_producto,
                                    cod_variable,
                                    cod_opcion
                                FROM 
                                    variable_prod_ped
                                WHERE 
                                    cod_pedido in (
                                        $sql_in_pedidos 
                                    )
                                ";
    $array_data_variable_pedido = array();
    if ($oIfx->Query($sql_config_var_pedido)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $cod_producto = $oIfx->f('cod_producto');
                $cod_variable = $oIfx->f('cod_variable');
                $cod_opcion = $oIfx->f('cod_opcion');
                $array_data_variable_pedido[$cod_producto][$cod_variable] = $cod_opcion;
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $total_cantidad_pedido = 0;
    $array_cajas_solas = array();
    $array_cajas_solas_cantidad = array();

    $peso_anterior = '';
    $dimensiones_anterior = '';

    $array_numero_cajas = array();

    foreach ($data_array as $key_prod => $data_dpef) {

        $cod_dpef = $data_dpef['cod_dpef'];
        $id_cabecera_pl = $data_dpef['id_cabecera_pl'];
        $id_packing_list = $data_dpef['id_packing_list'];
        $nombre_real_caja = $data_dpef['nombre_real_caja'];
        $tipo_codigo = $data_dpef['tipo_codigo'];
        $dimensiones = $data_dpef['dimensiones'];
        $cantidad_por_caja = $data_dpef['cantidad'];
        $peso_caja = $data_dpef['peso_caja'];


        if (array_key_exists($id_packing_list, $array_cajas_solas)) {
            // echo "La clave $id_packing_list existe en el array.";
        } else {
            $data = array(
                "tipo_codigo" => $tipo_codigo,
                "dimensiones" => $dimensiones,
                "cantidad_por_caja" => $cantidad_por_caja,
                "nombre_real_caja" => $nombre_real_caja
            );
            $array_cajas_solas[$id_cabecera_pl] = $data;
            $array_cajas_solas_cantidad[$id_cabecera_pl] += $cantidad_por_caja;
        }




        $sql_saepedf = "SELECT 
                            dpef_cod_dpef, 
                            dpef_cod_prod, 
                            dpef_nom_prod,
                            dpef_cant_dfac,
                            dpef_cod_bode,
                            dpef_precio_dfac
                        from 
                            saedpef
                        where 
                            dpef_cod_dpef = $cod_dpef
                            ;";
        $nombre_cliente = '';
        $fecha_pedido = '';
        $contador_tabla = 1;


        if ($oIfx->Query($sql_saepedf)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $dpef_cod_bode = $oIfx->f('dpef_cod_bode');
                    $dpef_cod_dpef = $oIfx->f('dpef_cod_dpef');
                    $dpef_cod_prod = $oIfx->f('dpef_cod_prod');
                    $dpef_cant_dfac = round($oIfx->f('dpef_cant_dfac'), 2);
                    $dpef_precio_dfac = number_format($oIfx->f('dpef_precio_dfac'), 2, '.', '');

                    $bode_nom_bode = $array_data_bodegas[$dpef_cod_bode];

                    $html .= '<tr>';
                    $html .= '<td align="center">' . $contador_tabla . '</td>';
                    $html .= '<td align="center">' . $dpef_cod_dpef . '</td>';
                    $html .= '<td align="center">' . $id_packing_list . '</td>';

                    if ($peso_anterior != $peso_caja || $dimensiones_anterior != $dimensiones) {
                        $html .= '<td align="center">' . $tipo_codigo . '</td>';
                        $html .= '<td align="center">' . $dimensiones . '</td>';
                        $html .= '<td align="center">' . $peso_caja . '</td>';
                        $total_cantidad_peso += $peso_caja;
                    } else {
                        $html .= '<td align="center"></td>';
                        $html .= '<td align="center"></td>';
                        $html .= '<td align="center"></td>';
                    }
                    $array_numero_cajas[$tipo_codigo] += 1;


                    $html .= '<td align="center">' . $cantidad_por_caja . '</td>';


                    $array_ids_variables = array();
                    if ($oIfxA->Query($sql_config_var_reporte)) {
                        if ($oIfxA->NumFilas() > 0) {
                            do {
                                $ids_variables = $oIfxA->f('ids_variables');
                                $array_ids_variables = explode(",", $ids_variables);

                                foreach ($array_ids_variables as $key => $id_variable) {
                                    $nombre_variable = $array_data_varpr[$id_variable];
                                    $id_variable_opcion = $array_data_variable_pedido[$dpef_cod_prod][$id_variable];
                                    $nombre_opcion = $array_data_opt[$id_variable_opcion];
                                    if ($nombre_variable != 'IMAGEN') {
                                        $html .= '<td align="center">' . $nombre_opcion . '</td>';
                                    }
                                }
                            } while ($oIfxA->SiguienteRegistro());
                        }
                    }
                    $oIfxA->Free();


                    $cantidad_pecio_ad = number_format($cantidad_por_caja * $dpef_precio_dfac, 2, '.', '');
                    $html .= '<td align="center">' . $dpef_precio_dfac . '</td>';
                    $html .= '<td align="center">' . $cantidad_pecio_ad . '</td>';
                    $html .= '<td align="center"></td>';
                    $html .= '</tr>';

                    $total_cantidad_pedido += $cantidad_por_caja;
                    $total_cdinero_pedido += $cantidad_pecio_ad;

                    $peso_anterior = $peso_caja;
                    $dimensiones_anterior = $dimensiones;

                    $contador_tabla++;
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();


        //
    }






    //TOTALES
    $html .= '<tr style="background-color:#E4DDDB">';
    $html .= '<td align="right">Total</td>';
    $html .= '<td align="center"></td>';
    $html .= '<td align="center"></td>';
    $html .= '<td align="center"></td>';
    $html .= '<td align="center"></td>';
    $html .= '<td align="center">' . $total_cantidad_peso . '</td>';
    $html .= '<td align="center">' . $total_cantidad_pedido . '</td>';


    foreach ($array_ids_variables as $key => $id_variable) {
        if ($key > 0) {
            $nombre_variable = $array_data_varpr[$id_variable];
            if ($nombre_variable != 'IMAGEN') {
                $html .= '<td align="center"></td>';
            }
        }
    }

    $html .= '<td align="center" colspan="2">Ex Works Cuenca USD</td>';
    $html .= '<td align="center">' . $total_cdinero_pedido . '</td>';
    $html .= '<td align="center"></td>';
    $html .= '</tr>';
    $html .= '</table>';



    $total_numero_cajas = 0;
    foreach ($array_numero_cajas as $key => $valor) {
        $total_numero_cajas += $valor;
    }


    $peso_menos_cajas = round($total_cantidad_peso - $total_numero_cajas, 2);


    $html .= '<br>';
    $html .= '<br>';
    $html .= '<br>';
    $html .= '<table style="font-size: 40%;" border="1" width="20%;" cellpadding="1" cellspacing="0">';

    $html .= '<tr>';
    $html .= '  <td align="left">Total (Quantity):</td>';
    $html .= '  <td align="right">' . $total_cantidad_pedido . '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '  <td align="left">Gross weight(kg):</td>';
    $html .= '  <td align="right">' . $total_cantidad_peso . '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '  <td align="left">Net weight(kg):</td>';
    $html .= '  <td align="right">' . $peso_menos_cajas . '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '  <td align="left">Package(s):</td>';
    $html .= '  <td align="right">' . $total_numero_cajas . '</td>';
    $html .= '</tr>';

    $html .= '</table>';


    $html .= '<table style="font-size: 50%;" border="1" width="20%;" cellpadding="1" cellspacing="0">';

    $html .= '<tr style="background-color:#E4DDDB">';
    $html .= '  <td align="left">Package Type Code</td>';
    $html .= '  <td align="right">Package Dimensions (cm)</td>';
    $html .= '  <td align="right">Quantity of packages</td>';
    $html .= '</tr>';


    foreach ($array_cajas_solas as $key47 => $data_cajas) {

        $tipo_codigo = $data_cajas['tipo_codigo'];
        $dimensiones = $data_cajas['dimensiones'];
        $cantidad_por_caja = $data_cajas['cantidad_por_caja'];
        $nombre_real_caja = $data_cajas['nombre_real_caja'];
        $cantidad_por_caja_sumado = $array_cajas_solas_cantidad[$key47];


        $cantidad_cajas = $array_numero_cajas[$tipo_codigo];


        $html .= '<tr>';
        $html .= '  <td align="left">' . $tipo_codigo . '</td>';
        $html .= '  <td align="left">' . $dimensiones . '</td>';
        //$html .= '  <td align="right">' . $cantidad_por_caja_sumado . '</td>';
        $html .= '  <td align="right">' . $cantidad_cajas . '</td>';
        $html .= '</tr>';
    }



    $html .= '</table>';


    $orientacion = 'L';
} //CIERRE IF TIPO 4



$documento = <<<EOD
    $html
EOD;

$pdf = new TCPDF2($orientacion, 'mm', 'A4', true, 'UTF-8', false);



$pdf->setPrintHeader(false);
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(10, 10, 10, true);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// set font
$pdf->SetFont('helvetica', 'N', 10);
// add a page
$pdf->AddPage();
$pdf->writeHTMLCell(0, 0, '', '', $documento, 0, 1, 0, true, '', true);
$pdf->Output($num_ped . '.pdf', 'I');

//ESPACIOS EN BLANCO
function espacios_pro($cant)
{
    $n = "";
    for ($i = 0; $i <= $cant; $i++) {
        $n .= "&nbsp;";
    }
    return $n;
}
