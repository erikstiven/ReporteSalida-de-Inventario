<?
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/general.css">
    <link href="<?= $_COOKIE["JIREH_INCLUDE"] ?>Clases/Formulario/Css/Formulario.css" rel="stylesheet" type="text/css" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>LISTA DE DESCRIPCION</title>
    <style type="text/css">
        <!--
        .Estilo1 {
            font-size: 12px;
            font-family: Georgia, "Times New Roman", Times, serif;
            color: #000000;
        }
        -->
    </style>

    <!--CSS-->
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="js/jquery/plugins/simpleTree/style.css" />
    <link rel="stylesheet" href="media/css/bootstrap.css">
    <link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="media/font-awesome/css/font-awesome.css">
    <link type="text/css" href="css/style.css" rel="stylesheet">
    </link>

    <!--Javascript-->
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script src="media/js/jquery-1.10.2.js"></script>
    <script src="media/js/jquery.dataTables.min.js"></script>
    <script src="media/js/dataTables.bootstrap.min.js"></script>
    <script src="media/js/bootstrap.js"></script>
    <script type="text/javascript" language="javascript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
    <script src="media/js/lenguajeusuario_producto.js"></script>


    <script>
        function datos(cod, cli, ruc, dir, tel, cel, vend, cont, pre, fpago, tpago, fec, auto, serie, fec_venc, dia, contr, ini, fin, cuenta) {
            window.opener.document.form1.cliente.value = cod;
            window.opener.document.form1.cliente_nombre.value = cli;
            window.opener.document.form1.ruc.value = ruc;
            window.opener.document.form1.tipo_pago.value = tpago;
            window.opener.document.form1.forma_pago1.value = fpago;
            window.opener.document.form1.auto_prove.value = auto;
            // window.opener.document.form1.fecha_validez.value = fec;
            window.opener.document.form1.serie_prove.value = serie;
            window.opener.document.form1.fecha_entrega.value = fec_venc;
            window.opener.document.form1.plazo.value = dia;
            window.opener.document.form1.contri_prove.value = contr;
            window.opener.document.form1.cuenta_prove.value = cuenta;
            window.opener.document.form1.dir_prove.value = dir;
            window.opener.document.form1.tel_prove.value = tel;
            window.opener.document.getElementById("fac_ini").innerHTML = 'FACT. INI: ' + ini;
            window.opener.document.getElementById("fac_fin").innerHTML = 'FACT. FIN: ' + fin;

            // DATOS SRI
            /* window.opener.document.form1.cliente_nombre_tloc.value = cli;
             window.opener.document.form1.ruc_tloc.value = ruc;
             window.opener.document.form1.serie_modi.value = serie;
             window.opener.document.form1.auto_modi.value = auto;
             window.opener.document.form1.fecha_cad_modi.value = fec;*/

            close();
        }
    </script>
</head>

<body>

    <?
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $idempresa =  $_GET['empresa'];
    $cliente_nom = $_GET['cliente'];

    //  LECTURA SUCIA
    //        

    //	$codigo_busca = strtr(strtoupper($codigo), "àáâãäåæçèéêëìíîïðñòóôõöøùüú", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÜÚ");



    // ---------------------------------------------------------------------------------------------------------
    // CONTROL CLPV POR USUARIO, SUCURSALES
    // ---------------------------------------------------------------------------------------------------------
    $id_usuario_comercial = $_SESSION['U_ID'];
    $bloqueo_sucu_sn = 'N';
    $sucursales_usuario = '';
    $sql_data_usuario_sucu = "SELECT bloqueo_sucu_sn, sucursales_usuario from comercial.usuario where usuario_id = $id_usuario_comercial";
    if ($oIfx->Query($sql_data_usuario_sucu)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $bloqueo_sucu_sn = $oIfx->f('bloqueo_sucu_sn');
                $sucursales_usuario = $oIfx->f('sucursales_usuario');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $sql_adicional_sucu = "";
    $oIfx->Free();
    if ($bloqueo_sucu_sn == 'S') {
        if (!empty($sucursales_usuario)) {
            $sql_adicional_sucu = ' and clpv_cod_sucu in (' . $sucursales_usuario . ')';
        }
    }
    // ---------------------------------------------------------------------------------------------------------
    // FIN CONTROL CLPV POR USUARIO, SUCURSALES
    // ---------------------------------------------------------------------------------------------------------


    $sql = "select clpv_cod_clpv, clpv_nom_clpv,  clpv_ruc_clpv, clpv_est_clpv,
            clpv_cod_fpagop, clpv_cod_tpago, clpv_pro_pago, clpv_etu_clpv, clpv_cod_cuen,
            ( SELECT min(DIRE_DIR_DIRE) FROM SAEDIRE WHERE
                    DIRE_COD_CLPV  = clpv_cod_clpv ) as direccion,
            ( SELECT min(TLCP_TLF_TLCP)  FROM SAETLCP WHERE
                    TLCP_COD_CLPV = clpv_cod_clpv AND
                    TLCP_TIP_TICP = 'T' ) as telefono,
            ( SELECT min(TLCP_TLF_TLCP) FROM SAETLCP WHERE
                    TLCP_COD_CLPV = clpv_cod_clpv AND
                    TLCP_TIP_TICP = 'C' ) as celular,
            clpv_cod_vend, clpv_cot_clpv, clpv_pre_ven from saeclpv where
            clpv_cod_empr = $idempresa and
            clpv_clopv_clpv = 'PV' 
            $sql_adicional_sucu
            and
            clpv_nom_clpv like upper('%$cliente_nom%')  order by 2 limit 50";


    ?>
</body>
<div id="contenido">
    <?
    $cont = 1;
    echo '<div style="text-align: center">
        <h4>
            LISTA DE PROVEEDORES
        </h4>
    </div>
    <div style="margin: 10px !important;">';
    echo '<table id="tbclientes" class="table table-condensed table-responsive"><thead>';
    echo '<tr>
			<th>ID</th>
			<th>CODIGO ITEM</th>
			<th>CLIENTE</th>
			<th>SUBCLIENTE</th>
			<th>VENDEDOR</th>
			<th>IDENTIFICACION</th>
			<th>CONTRIBUYENTE ESPECIAL</th>
			<th>ESTADO</th>
		  </tr>
          </thead>
          <tbody>
          ';

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $codigo = ($oIfx->f('clpv_cod_clpv'));
                $nom_cliente = htmlentities($oIfx->f('clpv_nom_clpv'));
                $ruc = ($oIfx->f('clpv_ruc_clpv'));
                $dire = htmlentities($oIfx->f('direccion'));
                $telefono = $oIfx->f('telefono');
                $celular = $oIfx->f('celular');
                $vendedor = $oIfx->f('clpv_cod_vend');
                $contacto = $oIfx->f('clpv_cot_clpv');
                $precio = round($oIfx->f('clpv_pre_ven'), 0);

                $fpago = $oIfx->f('clpv_cod_fpagop');
                $tpago = $oIfx->f('clpv_cod_tpago');
                $prove_dia = $oIfx->f('clpv_pro_pago');
                $clpv_etu_clpv = $oIfx->f('clpv_etu_clpv');
                $clpv_cod_cuen = $oIfx->f('clpv_cod_cuen');
                $clpv_clopv_clpv = $oIfx->f('clpv_clopv_clpv');
                $clpv_est_clpv = $oIfx->f('clpv_est_clpv');


                if ($clpv_etu_clpv == 1) {
                    $clpv_etu_clpv = 'S';
                } else {
                    $clpv_etu_clpv = 'N';
                }


                if ($clpv_est_clpv == 'A') {
                    $estado = 'ACTIVO';
                } elseif ($clpv_est_clpv == 'P') {
                    $estado = 'PENDIENTE';
                } elseif ($clpv_est_clpv == 'S') {
                    $estado = 'SUSPENDIDO';
                } else {
                    $estado = '--';
                }


                /**
                 * Consulta Subcliente
                 */
                $sql_sub = "select count(*) as total from saeccli WHERE ccli_cod_clpv = '$codigo' limit 1;";
                $sub_cliente = consulta_string_func($sql_sub, 'total', $oIfxA, 0);
                $sub_cliente_sn = ($sub_cliente > 0) ? 'SI' : 'NO';


                /**
                 * Consulta Vendedor
                 */
                $sql_vent = "select vend_cod_vend, vend_nom_vend from saevend where vend_cod_empr = $idempresa and vend_cod_vend = '$codigo'";
                $vendedor_info = consulta_string_func($sql_vent, 'vend_nom_vend', $oIfxA, '');


                if (empty($prove_dia)) {
                    $prove_dia = 0;
                }

                // FECHA DE VENCIMIENTO
                $fecha_venc = (sumar_dias_func(date("Y-m-d"), $prove_dia)); //  Y/m/d

                // AUTORIZACION PROVE
                $sql = "select  max(coa_fec_vali) as coa_fec_vali, coa_aut_usua, coa_seri_docu, coa_fact_ini, coa_fact_fin
                                    from saecoa where
                                    clpv_cod_empr = $idempresa and
                                    clpv_cod_clpv = $codigo group by coa_fec_vali,2,3,4,5 ";
                $fec_cadu_prove = '';
                $auto_prove = '';
                $serie_prove = '';
                $ini_prove = '';
                $fin_prove = '';
                if ($oIfxA->Query($sql)) {
                    if ($oIfxA->NumFilas() > 0) {
                        $fec_cadu_prove = fecha_mysql_func2($oIfxA->f('coa_fec_vali'));
                        $auto_prove = $oIfxA->f('coa_aut_usua');
                        $serie_prove = $oIfxA->f('coa_seri_docu');
                        $ini_prove = $oIfxA->f('coa_fact_ini');
                        $fin_prove = $oIfxA->f('coa_fact_fin');
                    }
                }
                $oIfxA->Free();

                if ($sClass == 'off') $sClass = 'on';
                else $sClass = 'off';
                echo '<tr height="20" class="' . $sClass . '"
                                        onMouseOver="javascript:this.className=\'link\';"
                                        onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                echo '<td>' . $cont . '</td>';
                echo '<td width="100">';
    ?>
                <a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_cliente; ?>', '<? echo $ruc ?>', '<? echo $dire ?>', '<? echo $telefono ?>',
                                                           '<? echo $celular ?>', '<? echo $vendedor ?>', '<? echo $contacto ?>','<? echo $precio ?>','<? echo $fpago ?>',
                                                           '<? echo $tpago ?>',  '<? echo $fec_cadu_prove ?>', '<? echo $auto_prove ?>','<? echo $serie_prove ?>',
                                                           '<? echo $fecha_venc ?>', '<? echo $prove_dia ?>',  '<? echo $clpv_etu_clpv ?>',
                                                           '<? echo $ini_prove ?>', '<? echo $fin_prove ?>', '<? echo $clpv_cod_cuen ?>' )">
                    <? echo $codigo; ?></a>
                <?
                echo '</td>';
                echo '<td>'
                ?>
                <a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_cliente; ?>', '<? echo $ruc ?>', '<? echo $dire ?>', '<? echo $telefono ?>',
                                                           '<? echo $celular ?>', '<? echo $vendedor ?>', '<? echo $contacto ?>','<? echo $precio ?>','<? echo $fpago ?>',
                                                           '<? echo $tpago ?>',  '<? echo $fec_cadu_prove ?>', '<? echo $auto_prove ?>','<? echo $serie_prove ?>',
                                                           '<? echo $fecha_venc ?>', '<? echo $prove_dia ?>',  '<? echo $clpv_etu_clpv ?>',
                                                           '<? echo $ini_prove ?>', '<? echo $fin_prove ?>', '<? echo $clpv_cod_cuen ?>' )">
                    <? echo $nom_cliente; ?></a>
                <?
                echo '</td>';
                echo '<td>'
                ?>
                <a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_cliente; ?>', '<? echo $ruc ?>', '<? echo $dire ?>', '<? echo $telefono ?>',
                                                           '<? echo $celular ?>', '<? echo $vendedor ?>', '<? echo $contacto ?>','<? echo $precio ?>','<? echo $fpago ?>',
                                                           '<? echo $tpago ?>',  '<? echo $fec_cadu_prove ?>', '<? echo $auto_prove ?>','<? echo $serie_prove ?>',
                                                           '<? echo $fecha_venc ?>', '<? echo $prove_dia ?>',  '<? echo $clpv_etu_clpv ?>',
                                                           '<? echo $ini_prove ?>', '<? echo $fin_prove ?>', '<? echo $clpv_cod_cuen ?>' )">
                    <? echo $sub_cliente_sn; ?></a>
                <?
                echo '</td>';
                echo '<td>'
                ?>
                <a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_cliente; ?>', '<? echo $ruc ?>', '<? echo $dire ?>', '<? echo $telefono ?>',
                                                           '<? echo $celular ?>', '<? echo $vendedor ?>', '<? echo $contacto ?>','<? echo $precio ?>','<? echo $fpago ?>',
                                                           '<? echo $tpago ?>',  '<? echo $fec_cadu_prove ?>', '<? echo $auto_prove ?>','<? echo $serie_prove ?>',
                                                           '<? echo $fecha_venc ?>', '<? echo $prove_dia ?>',  '<? echo $clpv_etu_clpv ?>',
                                                           '<? echo $ini_prove ?>', '<? echo $fin_prove ?>', '<? echo $clpv_cod_cuen ?>' )">
                    <? echo $vendedor_info; ?></a>
                <?
                echo '</td>';
                echo '<td>';
                ?>
                <a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_cliente; ?>', '<? echo $ruc ?>', '<? echo $dire ?>', '<? echo $telefono ?>',
                                                           '<? echo $celular ?>', '<? echo $vendedor ?>', '<? echo $contacto ?>','<? echo $precio ?>','<? echo $fpago ?>',
                                                           '<? echo $tpago ?>',  '<? echo $fec_cadu_prove ?>', '<? echo $auto_prove ?>','<? echo $serie_prove ?>',
                                                           '<? echo $fecha_venc ?>', '<? echo $prove_dia ?>',  '<? echo $clpv_etu_clpv ?>',
                                                           '<? echo $ini_prove ?>', '<? echo $fin_prove ?>', '<? echo $clpv_cod_cuen ?>' )">
                    <? echo $ruc; ?></a>
                <? echo '<td>';
                ?>
                <a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_cliente; ?>', '<? echo $ruc ?>', '<? echo $dire ?>', '<? echo $telefono ?>',
                                                           '<? echo $celular ?>', '<? echo $vendedor ?>', '<? echo $contacto ?>','<? echo $precio ?>','<? echo $fpago ?>',
                                                           '<? echo $tpago ?>',  '<? echo $fec_cadu_prove ?>', '<? echo $auto_prove ?>','<? echo $serie_prove ?>',
                                                           '<? echo $fecha_venc ?>', '<? echo $prove_dia ?>',  '<? echo $clpv_etu_clpv ?>',
                                                           '<? echo $ini_prove ?>', '<? echo $fin_prove ?>', '<? echo $clpv_cod_cuen ?>' )">
                    <? echo $clpv_etu_clpv; ?></a>
                <?
                echo '</td>';
                ?>
                <?
                echo '<td>';
                ?>
                <a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_cliente; ?>', '<? echo $ruc ?>', '<? echo $dire ?>', '<? echo $telefono ?>',
                                                           '<? echo $celular ?>', '<? echo $vendedor ?>', '<? echo $contacto ?>','<? echo $precio ?>','<? echo $fpago ?>',
                                                           '<? echo $tpago ?>',  '<? echo $fec_cadu_prove ?>', '<? echo $auto_prove ?>','<? echo $serie_prove ?>',
                                                           '<? echo $fecha_venc ?>', '<? echo $prove_dia ?>',  '<? echo $clpv_etu_clpv ?>',
                                                           '<? echo $ini_prove ?>', '<? echo $fin_prove ?>', '<? echo $clpv_cod_cuen ?>' )">
                    <? echo $estado; ?></a>
                <?
                echo '</td>';
                ?>
    <?
                echo '</tr>';
                $cont++;
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();
    echo '</tbody></table></div>';
    //echo $cod_producto;
    function fecha_mysql_func2($fecha)
    {
        $fecha_array = explode('/', $fecha);
        $m = $fecha_array[0];
        $y = $fecha_array[2];
        $d = $fecha_array[1];

        return ($y . '/' . $m . '/' . $d);
    }
    ?>

    <script>
        init();

        function init() {

            var table = $('#tbclientes').DataTable({
                dom: 'Bfrtip',
                processing: "<i class='fa fa-spinner fa-spin' style='font-size:24px; color: #34495e;'></i>",
                "language": {
                    "search": "<i class='fa fa-search'></i>",
                    "searchPlaceholder": "Buscar",
                    'paginate': {
                        'previous': 'Anterior',
                        'next': 'Siguiente'
                    },
                    "zeroRecords": "No se encontro datos",
                    "info": "Mostrando _START_ a _END_ de  _TOTAL_ Total",
                    "infoEmpty": "",
                    "infoFiltered": "(Mostrando _MAX_ Registros Totales)",
                },
                "paging": true,
                "ordering": true,
                "info": true,
            });

            table.search().draw();
        }
    </script>
</div>

</html>