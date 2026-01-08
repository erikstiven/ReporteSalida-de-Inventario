<?

include_once('../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
require_once (path(DIR_INCLUDE).'Clases/Contratos.class.php');


$idClpv=$dato = $_REQUEST['clpv'];
$idContrato=$dato = $_REQUEST['contrato'];
$tipoImpresion=$dato = $_REQUEST['tipo'];



if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

global $DSN_Ifx, $DSN;

// conexxion

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();

$oCon = new Dbo;
$oCon->DSN = $DSN;
$oCon->Conectar();

//variables de sesion
$idempresa = $_SESSION['U_EMPRESA'];
$idsucursal = $_SESSION['U_SUCURSAL'];

//DATOS DE LA EMPRESA
$sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_cod_pais,
                empr_num_resu, empr_path_logo, empr_iva_empr , empr_num_dire, empr_fax_empr,
                empr_sitio_web, empr_cm1_empr, empr_cm2_empr , empr_prec_sucu, empr_tel_resp, empr_mai_empr, empr_ema_comp, empr_web_color
                from saeempr where empr_cod_empr = $idempresa ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_path_logo');
            $empr_cod_pais = $oIfx->f('empr_cod_pais');
            $empr_web_color = $oIfx->f('empr_web_color');
        }
    }
    $oIfx->Free();

    if(empty($empr_web_color)){
        $empr_web_color='#8C939B';
    }


 //clases contratos
 $Contratos = new Contratos($oCon, $oIfx, $idempresa, $idsucursal, $idClpv, $idContrato);
 $arrayContrato = $Contratos->consultarContrato();

 //CABECERA DATOS DEL CLIENTE
 if (count($arrayContrato) > 0) {
    foreach ($arrayContrato as $val) {
        $id_sucursal = $val[2];
        $codigo = $val[5];
        $nom_clpv = $val[6];
        $ruc_clpv = $val[7];
        $fecha_contrato = $val[8];
        $fecha_corte = $val[10];
        $fecha_cobro = $val[11];
        $duracion = $val[12];
        $penalidad = $val[13];
        $tarifa = $val[18];
        $fecha_instalacion = $val[21];
        $sobrenombre = $val[23];
        $limite = $val[24];
        $cheque_sn = $val[27];
        $cobro_directo = $val[28];
        $direccion = $val[31];
        $referencia = $val[32];
        $celular = $val[33];
        $email = $val[34];
        $latitud = $val[35];
        $longitud = $val[36];
        $foto = $val[41];
        $estadoNombre = $val[45];
        $estadoClass = $val[46];
        $sector = $val[47];
        $barrio = $val[48];
        $tipoContrato = $val[49];
        $vend_nom_vend = $val[50];
        $cobrador = $val[51];
        $estadoColor = $val[52];
        $tarifa_e = $val[54];
        $descuento_p = $val[55];
        $descuento_v = $val[56];
        $direccion_cobro = $val[62];
        $id_ruta = $val[65];
        $ruta = $val[66];
        $orden_ruta = $val[67];
        $fecha_c_corte = $val[76];
    }
}

$sql = "SELECT clv_con_clpv from saeclpv where clpv_cod_clpv = $idClpv";
                        $clv_con_clpv = consulta_string_func($sql, 'clv_con_clpv', $oCon, '');

                        if(strlen($clv_con_clpv)==0){
                            $clv_con_clpv = 2;
                        }
                        
                        //TIPO DE IDENTIFICACION DEL CLIENTE
                        $sql_sucu = "SELECT identificacion from comercial.tipo_iden_clpv_pais where pais_cod_pais = $empr_cod_pais and id_iden_clpv = '$clv_con_clpv'";
                        if ($oIfx->Query($sql_sucu)) {
                            if ($oIfx->NumFilas() > 0) {
                                do {
                                    $tip_iden_cliente = $oIfx->f('identificacion');
                                } while ($oIfx->SiguienteRegistro());
                            }
                        }
                        $oIfx->Free();


if (empty($cobrador)) {
    $cobrador = '&nbsp;';
}

$ubicacion = '';

//deuda
$valor = $Contratos->consultaMontoMesAdeuda();
$meses = $Contratos->consultaMesesAdeuda();
$contratoTelefonos = $Contratos->consultaTelefonos();
$contratoEmail = $Contratos->consultaEmail();

if ($latitud <> 0 && $longitud <> 0) {
    $ubicacion = '<span class="btn btn-success " onclick="verMapa(' . $idContrato . ', ' . $idClpv . ');"> <i class="glyphicon glyphicon-globe"></i> </span><span class="text-primary" style="cursor: pointer; font-size: 11px;" onclick="verMapa(' . $idContrato . ', ' . $idClpv . ');"> ' . $latitud . ', ' . $longitud . '</span>';
}

//fotos
$sHtmlFoto = '';
if (!empty($foto)) {
    $sHtmlFoto = '';
}

//tarifa especial
$sHtmlTarifa = '';
if ($tarifa_e == 'S') {

    if ($descuento_v > 0) {
        $tarifa -= $descuento_v;
    }

    $sHtmlTarifa = '<a class="btn btn-xs bg-maroon" href="# return 0" style="font-size: 15px;">TARIFA ESPECIAL: ' . number_format($descuento_v, 2, '.', ',') . ' (' . $descuento_p . ' %)</a>';
}


//nombre sucursal
$sql = "SELECT sucu_nom_sucu FROM saesucu WHERE sucu_cod_sucu = $id_sucursal";
$sucu_nom_sucu = consulta_string_func($sql, 'sucu_nom_sucu', $oIfx, '');

$sql = "SELECT foto, identificador, id_tipo_cont_serv,observaciones,vendedor FROM isp.contrato_clpv WHERE id = $idContrato";
$foto = consulta_string_func($sql, 'foto', $oIfx, '');
$identificador = consulta_string_func($sql, 'identificador', $oIfx, '');
$id_tipo_cont_serv = consulta_string_func($sql, 'id_tipo_cont_serv', $oIfx, 0);
$observaciones_contr = consulta_string_func($sql, 'observaciones', $oIfx, 0);

$cod_vend = consulta_string_func($sql, 'vendedor', $oIfx, 0);

$sql = "SELECT id_nap FROM isp.int_contrato_caja WHERE id_contrato = $idContrato AND estado not in ('E') AND id_nap is not null";
$id_nap = consulta_string_func($sql, 'id_nap', $oIfx, 0);

if(!empty($id_nap) && $id_nap > 0){
    $sql = "SELECT concat(siglas, ' / ', nombre) as nap FROM isp.int_nap where id = $id_nap";
    $nap = consulta_string_func($sql, 'nap', $oIfx, '');
}

//  $vendedor_contr = '';
if (!empty($cod_vend)) {
    $sql = "SELECT vend_nom_vend from saevend where vend_cod_vend = '$cod_vend'";
    $vendedor_contr = consulta_string_func($sql, 'vend_nom_vend', $oIfx, '');
}


if ($id_tipo_cont_serv > 0) {
    $sql = "SELECT nombre FROM isp.int_tipo_servicio WHERE id = $id_tipo_cont_serv";
    $nombre_tip_cliente = consulta_string_func($sql, 'nombre', $oIfx, '');
}

if (empty($foto)) {
    $foto = "/images/abonado_ejemplo.png";
}

if (empty($identificador)) {
    $identificador = "/images/sin_firma.png";
}






/*
    //selecciona sucursales y direcciones
    $sql_sucu = "select sucu_nom_sucu, sucu_dir_sucu from saesucu where sucu_cod_empr = $idEmpresa and sucu_cod_sucu = $idSucursal ";
        if ($oIfx->Query($sql_sucu)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
                    $sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');
                } while ($oIfx->SiguienteRegistro());
            }
        }
    $oIfx->Free();

    $sql_sucu = "SELECT identificacion from comercial.tipo_iden_clpv_pais where pais_cod_pais = $empr_cod_pais and id_iden_clpv = 1";
    if ($oIfx->Query($sql_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $tip_ruc_pais = $oIfx->f('identificacion');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();*/

    //VALIDAICON LOGO

    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;


    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

    if (file_exists($path_logo_img)) {
        $logo_empresa=' <img width="150px;"  src="' . $path_logo_img . '">';
    }
    else{
        $logo_empresa='<div style="color:red;">LOGO NO CARGADO</div>';
    }

    if($tipoImpresion!=0){
        $logo_empresa='';
    }

   if(!empty($fecha_c_corte)){
    $fecha_c_corte=fecha_mysql_dmy($fecha_c_corte);
   }
   else{
    $fecha_c_corte='';
   }


   $html ='<table border="0"  style="font-family:Helvetica; width: 100%;"  cellspacing="0">';
   $html .='<tr>';
   $html .='<td style="font-size: 16px;width:25%" align="left" >'.$logo_empresa.'</td>';
   $html .='<td style="font-size: 18px;width:75%;" align="center"><b>ESTADO DE CUENTA<br><br><font style="font-size:11px;">'.$razonSocial.'<br>R.U.C : '.$ruc_empr.'</font></b></td>';
   $html .='</tr>';
   $html .='</table>';


   $html .='<table   style="margin-top:10px; font-family:Helvetica; font-size:12px; width: 100%;"  cellspacing="2" cellpadding="1">';

   $html .='<tr>';
   $html .='<td colspan="4" style="font-size:13px;height:20px"><b><font color="'.$empr_web_color.'"><b>Datos Personales:</b></font></b></td>';
   $html .='</tr>';

   $html .='<tr>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:15%; border:1px solid '.$empr_web_color.'; " ><b>Cliente:</b></td>';
   $html .='<td style="width:35%; border:1px solid '.$empr_web_color.';" >' . $nom_clpv . '</td>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:15%; border:1px solid '.$empr_web_color.';" ><b>'.$tip_iden_cliente.':</b></td>';
   $html .='<td style="width:35%; border:1px solid '.$empr_web_color.';" >' . $ruc_clpv . '</td>';   
   $html .='</tr>';

   $html .='<tr>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:15%; border:1px solid '.$empr_web_color.';" ><b>Dirección:</b></td>';
   $html .='<td  style="width:35%; border:1px solid '.$empr_web_color.';" >' . $direccion . ' '.$referencia.' '.$sector.' '.$barrio.'</td>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:15%; border:1px solid '.$empr_web_color.';" ><b>Tipo Cliente:</b></td>';
   $html .='<td style="width:35%; border:1px solid '.$empr_web_color.';" >' . $nombre_tip_cliente . '</td>';   
   $html .='</tr>';

   $html .='<tr>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:15%; border:1px solid '.$empr_web_color.';" ><b>Teléfono:</b></td>';
   $html .='<td style="width:35%; border:1px solid '.$empr_web_color.';" >' . $contratoTelefonos . '</td>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:15%; border:1px solid '.$empr_web_color.';" ><b>Email:</b></td>';
   $html .='<td style="width:35%; border:1px solid '.$empr_web_color.'; " >' . $contratoEmail . ' </td>';
   $html .='</tr>';
  
   $html .='</table>';


   $html .='<table   style="font-family:Helvetica; font-size:12px; width: 100%;"  cellspacing="2" cellpadding="1">';

   $html .='<tr>';
   $html .='<td colspan="4" style="font-size:13px;height:20px"><b><font color="'.$empr_web_color.'"><b>Datos del Contrato:</b></font></b></td>';
   $html .='</tr>';

   $html .='<tr>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.';" ><b>Nro. Contrato:</b></td>';
   $html .='<td style="width:30%; border:1px solid '.$empr_web_color.';" >' . $codigo . ' </td>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.'; " ><b>Estado:</b></td>';
   $html .='<td style="width:30%; border:1px solid '.$empr_web_color.';color:'.$estadoColor.'" > <b>' . $estadoNombre . '</b></td>';
   $html .='</tr>';

   $html .='<tr>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.';" ><b>Tipo Contrato:</b></td>';
   $html .='<td style="width:30%; border:1px solid '.$empr_web_color.'; " >' . $tipoContrato . ' </td>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.';" ><b>Fecha Contrato:</b></td>';
   $html .='<td style="width:30%; border:1px solid '.$empr_web_color.';" >' . fecha_mysql_dmy($fecha_contrato) . '</td>';
   $html .='</tr>';

   $html .='<tr>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.';" ><b>Fecha Instalación:</b></td>';
   $html .='<td style="width:30%; border:1px solid '.$empr_web_color.';" >' . fecha_mysql_dmy($fecha_instalacion) . '</td>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.';" ><b>Fecha U.Corte:</b></td>';
   $html .='<td style="width:30%; border:1px solid '.$empr_web_color.'; " >' . $fecha_c_corte . ' </td>';
   $html .='</tr>';

   $html .='<tr>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.';" ><b>Día Cobro:</b></td>';
   $html .='<td style="width:30%; border:1px solid '.$empr_web_color.';" >' . $fecha_cobro . '</td>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.';" ><b>Día Corte:</b></td>';
   $html .='<td style="width:30%; border:1px solid '.$empr_web_color.'; " >' . $fecha_corte . ' </td>';
   $html .='</tr>';

   $html .='<tr>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.';" ><b>Duración:</b></td>';
   $html .='<td style="width:30%; border:1px solid '.$empr_web_color.';" >' . $duracion . '</td>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.';" ><b>Observación:</b></td>';
   $html .='<td style="width:30%; border:1px solid '.$empr_web_color.'; " >' . $observaciones_contr . ' </td>';
   $html .='</tr>';

   $html .='</table>';

   $html .='<table   style="font-family:Helvetica; font-size:12px; width: 100%;"  cellspacing="2" cellpadding="1">';

   $html .='<tr>';
   $html .='<td colspan="4" style="font-size:13px;height:20px"><b><font color="'.$empr_web_color.'"><b>Detalle Pagos:</b></font></b></td>';
   $html .='</tr>';

   $html .='<tr>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.';" ><b>Tarifa:</b></td>';
   $html .='<td style="width:30%; border:1px solid '.$empr_web_color.';" align="right">' . number_format($tarifa, 2, '.', ',') . ' </td>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.'; " ><b>Balance:</b></td>';
   $html .='<td style="width:30%; border:1px solid '.$empr_web_color.';" align="right" > ' . number_format($valor, 2, '.', ',') . '</td>';
   $html .='</tr>';

   $html .='<tr>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.';" ><b>Meses Mora:</b></td>';
   $html .='<td style="color: red;width:30%; border:1px solid '.$empr_web_color.'; " align="right"><b>' . $meses . '</b></td>';
   $html .='<td style="background:'.$empr_web_color.' ; color:white; width:20%; border:1px solid '.$empr_web_color.';" ><b>Ultimo Pago:</b></td>';
   $html .='<td style="width:30%; border:1px solid '.$empr_web_color.';" align="right">' . number_format($valor, 2, '.', ',') . '</td>';
   $html .='</tr>';

   $html .='</table>';


   ///DATOS DE LOS SERVICIOS CONTRATADOS

   $sHtml = '<table style="margin-top:5px; font-family:Helvetica; font-size:12px; width: 100%;"  cellspacing="2" cellpadding="1">
                        ';
        $sql = "SELECT c.nombre, a.id_tarjeta, a.ip, f.estado, f.color, COALESCE(i.sistema, 'N/A') as sistema, string_agg(d.paquete || '#$' || e.nombre || '#$' || b.cod_prod || '#$' || b.precio || '#$' || b.estado || '#$' || g.estado || '#$' || g.color || '#$' || COALESCE(h.nombre, 'N/A') || '#$' || COALESCE(b.codigo_cid, 'N/A') , '%&') as datos_caja_pack
                FROM isp.int_contrato_caja a 
                            INNER JOIN isp.int_contrato_caja_pack b ON a.id = b.id_caja
                            INNER JOIN isp.int_tipo_prod c ON a.id_tipo_prod = c.id
                            INNER JOIN isp.int_paquetes d ON b.id_prod = d.id
                            INNER JOIN isp.int_tipo_prod e ON d.id_tipo_prod = e.id
                            INNER JOIN isp.int_estados_equipo f ON a.estado = f.id
                            INNER JOIN isp.int_estados_equipo g ON b.estado = g.id
                            LEFT JOIN isp.int_tipo_servicio h ON d.id_tipo_serv = h.id
                                                        LEFT JOIN isp.int_sistemas i ON a.id_equipo = i.id
                WHERE a.id_contrato = $idContrato AND a.estado not in ('E') AND b.estado not in ('E') GROUP BY 1,2,3,4,5,6";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $nombre             = $oCon->f('nombre');
                    $id_tarjeta         = $oCon->f('id_tarjeta');
                    $ip                 = $oCon->f('ip');
                    $estado             = $oCon->f('estado');
                    $color              = $oCon->f('color');
                    if(empty($color)){
                        $color='black';
                    }
                    $datos_caja_pack    = $oCon->f('datos_caja_pack');
                    $sistema            = $oCon->f('sistema');
                    //$estado_lbl         = '<span class="label bg-' . $color . '" style="font-size:12px">' . $estado . '</span>';
                    //<td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;" >Ip</td>
                    /*$sHtml .= '<tr>
                                    <td  style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;width:38%;" colspan="2"><b>Tipo</b></td>
                                    <td  style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white; width:40%;" colspan="2"><b>Serial</b></td>
                                    <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white; width:10%;" ><b>Sistema</b></td>
                                    
                                    <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;width:12%;" ><b>Estado</b></td>
                                </tr>
                                <tr>
                                    <td style="width:38%; border:1px solid '.$empr_web_color.';" colspan="2">' . $nombre . '</td>
                                    <td style="width:40%; border:1px solid '.$empr_web_color.';" colspan="2">' . $id_tarjeta . '</td>
                                    <td style="width:10%; border:1px solid '.$empr_web_color.';">' . $sistema . '</td>
                                    <td style="width:12%; border:1px solid '.$empr_web_color.'; color:'.$color.';"><b>' . $estado . '</b></td>
                                </tr>*/
                                $sHtml .= '<tr>
                                    <td colspan="6" style="background:'.$empr_web_color.';font-size:13px;height:20px; color:white;width:100%"  align="center"><b>Planes</b></td>
                                </tr>
                                <tr>
                                    <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white; width:28%;" ><b>Plan</b></td>
                                    <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white; width:10%;" ><b>Tipo</b></td>
                                    <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white; width:28%;" ><b>T. Servicio</b></td>
                                    <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white; width:12%;" ><b>Codigo</b></td>
                                    <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white; width:10%;" ><b>Precio</b></td>
                                    <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white; width:12%;" ><b>Estado</b></td>
                                </tr>';

                    $datos_caja_pack    = explode("%&", $datos_caja_pack);

                    if (count($datos_caja_pack) > 0) {
                        for ($c = 0; $c < count($datos_caja_pack); $c++) {
                            $caja_pack_indi = explode("#$", $datos_caja_pack[$c]);

                            $plan       = trim($caja_pack_indi[0]);
                            $t_plan     = trim($caja_pack_indi[1]);
                            $c_plan     = $caja_pack_indi[2];
                            $p_plan     = $caja_pack_indi[3];
                            $e_plan     = $caja_pack_indi[4];
                            $e_txt_plan = $caja_pack_indi[5];
                            $e_col_plan = $caja_pack_indi[6];
                            $tipo_ser   = trim($caja_pack_indi[7]);
                            $cid        = $caja_pack_indi[8];
                            //$estado_lbl_i         = '<span class="label bg-' . $e_col_plan . '" style="font-size:12px">' . $e_txt_plan . '</span>';

                            $sHtml .= '<tr>
                                            <td style="width:28%; border:1px solid '.$empr_web_color.';">' . $plan . '</td>
                                            <td style="width:10%; border:1px solid '.$empr_web_color.';">' . $t_plan . '</td>
                                            <td style="width:28%; border:1px solid '.$empr_web_color.';">' . $tipo_ser . '</td>
                                            <td style="width:12%; border:1px solid '.$empr_web_color.';">' . $c_plan . '</td>
                                            <td style="width:10%; border:1px solid '.$empr_web_color.';" align="right">' . $p_plan . '</td>
                                            <td style="width:12%; border:1px solid '.$empr_web_color.';" >' . $e_txt_plan . '</td>
                                        </tr>';
                        }
                    } 
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sHtml .= '</table>';

        $html.=$sHtml;

    //DATOS FACTURAS

    //query contrato
    $array_p = array();
    $sql = "select id, fecha, secuencial, estado, dias,
            estado_fact, id_factura, estado, mes, anio,
            tarifa, abono, valor_pago, can_add, pre_add,
            tot_add, valor_uso, valor_no_uso, dias_uso,
            dias_no_uso, descuento
            from isp.contrato_pago
            where id_clpv = $idClpv and
            id_contrato = $idContrato order by fecha";
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            do {
                $array_p[] = array(
                    $oCon->f('id'), $oCon->f('fecha'), $oCon->f('secuencial'), $oCon->f('estado'), $oCon->f('dias'),
                    $oCon->f('estado_fact'), $oCon->f('id_factura'), $oCon->f('estado'), $oCon->f('mes'), $oCon->f('tarifa'),
                    $oCon->f('abono'), $oCon->f('valor_pago'), $oCon->f('anio'), $oCon->f('can_add'), $oCon->f('pre_add'),
                    $oCon->f('tot_add'), $oCon->f('valor_no_uso'), $oCon->f('dias_uso'), $oCon->f('dias_no_uso'), $oCon->f('descuento')
                );
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();

    if (count($array_p) > 0) {

        $sHtml_facturas .= '<table style="margin-top:5px; font-family:Helvetica; font-size:12px; width: 100%;"  cellspacing="2" cellpadding="1">
                                 
                                <thead>
                                <tr>
                                    <td colspan="11"  style="background:'.$empr_web_color.';font-size:13px;height:20px; color:white;width:100%;" align="center"><b>DETALLE FACTURACION</b></td>
                                </tr>
                                    <tr>
                                        <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;width:10%;" ><b>FECHA</b></td>
                                        <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;width:10%;" ><b>PERIODO</b></td>
                                        <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;width:13%;" ><b>FACTURA</b></td>  
                                        <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;width:14%;" ><b>ESTADO</b></td>
                                        <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;width:5%;" ><b>DIAS</b></td>
                                        <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;width:5%;" ><b>DIAS USO</b></td>
                                        <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;width:9%;" ><b>TARIFA</b></td>
                                        <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;width:9%;" ><b>VALOR USO</b></td>
                                        <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;width:9%;" ><b>PAGOS</b></td>
                                        <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;width:7%;" ><b>DSCTO</b></td>
                                        <td style="background:'.$empr_web_color.';font-size:12px;height:20px; color:white;width:9%;" ><b>SALDO</b></td>
                                        
                                    </tr>
                                </thead>
                                <tbody>';

        foreach ($array_p as $val) {
            $idDet = $val[0];
            $fechaPago = $val[1];
            $secuencial = $val[2];
            $estado = $val[3];
            $dias = $val[4];
            $estado_fact = $val[5];
            $id_factura = $val[6];
            $estado = $val[7];
            $mes = $val[8];
            $tarifa = $val[9];
            $abono = $val[10];
            $valor_pago = $val[11];
            $anio = $val[12];
            $can_add = $val[13];
            $pre_add = $val[14];
            $tot_add = $val[15];
            $valor_no_uso = $val[16];
            $dias_uso = $val[17];
            $dias_no_uso = $val[18];
            $descuento = $val[19];

            $tarifaOk = $tarifa + $tot_add;
            $valorReal = $tarifaOk - $valor_no_uso;

            $eventoFact = '';
            $totalFactura = 0;
            $classEstado = 'info';
            $estadopago = "PENDIENTE";
            if ($estado_fact != 'GR') {
                if ($mesHoy > $mes && $anio == $anio_actual) {
                    $classEstado = 'danger';
                    $estadopago = "EN MORA";
                } else {
                    $classEstado = 'info';
                    $estadopago = "PENDIENTE";
                }
            } elseif ($estado_fact == 'GR') {
                $classEstado = 'success';
                $estadopago = "FACTURADO";
            }

            $totalCobros = $abono + $valor_pago;

            $saldoMes = round($valorReal - $totalCobros + $descuento, 2);

            //pago
            $sHtmlFactura = '';
            $fact_num_preimp='';
            $sql = "select fact_cod_fact, fact_num_preimp, fact_fech_fact, fact_clav_sri
                    from saefact f, saedfac d
                    where fact_cod_empr = dfac_cod_empr and
                    fact_cod_sucu = dfac_cod_sucu and
                    fact_cod_clpv = dfac_cod_clpv and
                    fact_cod_fact = dfac_cod_fact and
                    fact_cod_empr = $idempresa and
                    fact_cod_clpv = $idClpv and
                    dfac_cod_mes = $idDet and
                    fact_cod_contr = $idContrato and
                    fact_est_fact != 'AN'
                    group by 1,2,3,4";
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                    $sHtmlFactura = '';
                    do {
                        $fact_cod_fact = $oIfx->f('fact_cod_fact');
                        $fact_num_preimp = $oIfx->f('fact_num_preimp');
                        $fact_fech_fact = $oIfx->f('fact_fech_fact');
                        $fact_clav_sri = $oIfx->f('fact_clav_sri');

                        //$sHtmlFactura .= '<a href="#" title="' . $fact_fech_fact . '" onclick="javascript:genera_documento(' . $fact_cod_fact . ',\'' . $fact_clav_sri . '\')">' . $fact_num_preimp . '</a>, ';
                    } while ($oIfx->SiguienteRegistro());
                }
            }
            $oIfx->Free();
            if(!empty($fechaPago)){
                $fechaPago=date('d', strtotime($fechaPago)).'-'.substr(strtolower(Mes_func($mes)),0,3);
                
            }
            $sHtml_facturas .= '<tr>
                                    <td style="width:10%; border:1px solid '.$empr_web_color.';" align="center">' . $fechaPago . '</td>
                                    <td style="width:10%; border:1px solid '.$empr_web_color.';" align="center">' . $anio . '</td>
                                    <td style="width:13%; border:1px solid '.$empr_web_color.';" align="center">' . $fact_num_preimp . '</td>
                                    <td style="width:14%; border:1px solid '.$empr_web_color.';" align="center">' . $estadopago . '</td>
                                    <td style="width:5%; border:1px solid '.$empr_web_color.';" align="center">' . $dias . '</td>
                                    <td style="width:5%; border:1px solid '.$empr_web_color.';" align="center">' . $dias_uso . '</td>
                                    <td style="width:9%; border:1px solid '.$empr_web_color.';" align="right">' . number_format($tarifaOk, 2, '.', ',') . '</td>
                                    <td style="width:9%; border:1px solid '.$empr_web_color.';" align="right">' . number_format($valorReal, 2, '.', ',') . '</td>
                                    <td style="width:9%; border:1px solid '.$empr_web_color.';" align="right">' . number_format($totalCobros, 2, '.', ',') . '</td>
                                    <td style="width:7%; border:1px solid '.$empr_web_color.';" align="right"> ' . number_format($descuento, 2, '.', ',') . '</td>
                                    <td style="width:9%; border:1px solid '.$empr_web_color.';" align="right">' . number_format($saldoMes, 2, '.', ',') . '</td>
                                    
                                </tr>';
        }
    }
    $sHtml_facturas .= '</tbody>
                    </table>';

    if($tipoImpresion==0){

        $table = '<page backimgw="10%" backtop="5mm" backbottom="5mm" backleft="3mm" backright="3mm" footer="date;heure;page" >';
    $table.= $html;
    $table.= $sHtml_facturas;
    $table.= '</page>';

    $html2pdf = new HTML2PDF('P', 'A4', 'es', true, 'UTF-8', array(0,0,0,0));
    $html2pdf->WriteHTML($table);
    ob_end_clean();
    $html2pdf->Output('recibo_template.pdf', '');

    }
    else{

        //GENERACION ARCHIVO EXCEL

    $dochtml='<!DOCTYPE html>
    <html>
    <head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <style>
        .verticalText {
        writing-mode: vertical-lr;
        transform: rotate(180deg);
        }
    </style>
    </head>
    <body>
        '.$html.'
        '.$sHtml_facturas.'
    </body>
    </html>';
 
     header("Pragma: public");
     header("Expires: 0");
     $filename = "ESTADO_CUENTA_$ruc_clpv"."_$nom_clpv.xls";
     header("Content-type: application/x-msdownload");
     header("Content-Disposition: attachment; filename=$filename");
     header("Pragma: no-cache");
     header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
 
     echo $dochtml;

    }


    

?>



    
    