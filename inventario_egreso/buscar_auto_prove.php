<?
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE).'comun.lib.php');

if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<link rel="stylesheet" type = "text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/general.css">
    <link href="<?=$_COOKIE["JIREH_INCLUDE"]?>Clases/Formulario/Css/Formulario.css" rel="stylesheet" type="text/css"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>AUTORIZACIONES PROVEEDOR</title>
<style type="text/css">
<!--
.Estilo1 {
	font-size: 12px;
	font-family: Georgia, "Times New Roman", Times, serif;
	color: #000000;
}
-->
</style>

<script>
	function datos( serie, auto, fec_venc, ini, fin ){
                window.opener.document.form1.auto_prove.value = auto;
                window.opener.document.form1.fecha_validez.value = fec_venc;
                window.opener.document.form1.serie_prove.value = serie;
                window.opener.document.getElementById("fac_ini").innerHTML = 'FACT. INI: '+ ini;
                window.opener.document.getElementById("fac_fin").innerHTML = 'FACT. FIN: ' + fin;
		close();
	}
</script>
</head>

<body>

<?
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
        
        $oIfx = new Dbo;
	$oIfx -> DSN = $DSN_Ifx;
	$oIfx -> Conectar();

        $oIfxA = new Dbo;
	$oIfxA -> DSN = $DSN_Ifx;
	$oIfxA -> Conectar();
	
	$idempresa = $_GET['empresa'];
	$serie_nom = $_GET['serie'];
        $prove = $_GET['prove'];



//	$codigo_busca = strtr(strtoupper($codigo), "àáâãäåæçèéêëìíîïðñòóôõöøùüú", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÜÚ");
	$sql = "select  coa_fec_vali, coa_aut_usua, coa_seri_docu, coa_fact_ini, coa_fact_fin
                    from saecoa where
                    clpv_cod_empr = $idempresa and
                    clpv_cod_clpv = $prove ";
?> 
</body>
<div id="contenido">
<?	
	$cont=1;
	echo '<table align="center" border="0" cellpadding="2" cellspacing="1" width="98%" style="border:#999999 1px solid">';
	echo '<tr><th colspan="7" align="center" class="titulopedido">LISTA AUTORIZACIONES</th></tr>';
	echo '<tr>
			<th align="left" bgcolor="#EBF0FA" class="titulopedido">ID</th>
			<th align="left" bgcolor="#EBF0FA" class="titulopedido">SERIE</th>
			<th align="left" bgcolor="#EBF0FA" class="titulopedido">AUTORIZACION</th>
                        <th align="left" bgcolor="#EBF0FA" class="titulopedido">FECHA CAD.</th>
                        <th align="left" bgcolor="#EBF0FA" class="titulopedido">FACTURA INI</th>
                        <th align="left" bgcolor="#EBF0FA" class="titulopedido">FACTURA FIN</th>
		  </tr>';

    if ($oIfx->Query($sql)){
        if( $oIfx->NumFilas() > 0 ){
		do {
			$fec_cadu_prove_tmp = fecha_mysql_func2($oIfx->f('coa_fec_vali'));
                        $fec_cadu_prove = $oIfx->f('coa_fec_vali');
                        $auto_prove = $oIfx->f('coa_aut_usua');
                        $serie_prove = $oIfx->f('coa_seri_docu');
                        $ini_prove = $oIfx->f('coa_fact_ini');
                        $fin_prove = $oIfx->f('coa_fact_fin');

                        if ($sClass=='off') $sClass='on'; else $sClass='off';
			echo '<tr height="20" class="'.$sClass.'"
                                        onMouseOver="javascript:this.className=\'link\';"
                                        onMouseOut="javascript:this.className=\''.$sClass.'\';">';
				echo '<td>'.$cont.'</td>';
				echo '<td width="100">';
	?>
    				<a href="#" onclick="datos('<? echo $serie_prove;?>','<? echo $auto_prove;?>', '<? echo $fec_cadu_prove; ?>',
                                                           '<? echo $ini_prove; ?>', '<? echo $fin_prove; ?>' )">
                                <? echo $serie_prove;?></a>
       <?
				echo '</td>';
				echo '<td>'
	?>
    				<a href="#" onclick="datos('<? echo $serie_prove;?>','<? echo $auto_prove;?>', '<? echo $fec_cadu_prove; ?>',
                                                           '<? echo $ini_prove; ?>', '<? echo $fin_prove; ?>' )">
				<? echo $auto_prove;?></a>
    <?
				echo '</td>';
                                echo '<td>';
    ?>
                                <a href="#" onclick="datos('<? echo $serie_prove;?>','<? echo $auto_prove;?>', '<? echo $fec_cadu_prove; ?>',
                                                           '<? echo $ini_prove; ?>', '<? echo $fin_prove; ?>' )">
				<? echo $fec_cadu_prove_tmp;?></a>
    <?                          echo '<td>';
    ?>
                                <a href="#" onclick="datos('<? echo $serie_prove;?>','<? echo $auto_prove;?>', '<? echo $fec_cadu_prove; ?>',
                                                           '<? echo $ini_prove; ?>', '<? echo $fin_prove; ?>' )">
				<? echo $ini_prove;?></a>
    <?
                                echo '</td>';
                                echo '<td>';
    ?>
                                <a href="#" onclick="datos('<? echo $serie_prove;?>','<? echo $auto_prove;?>', '<? echo $fec_cadu_prove; ?>',
                                                           '<? echo $ini_prove; ?>', '<? echo $fin_prove; ?>' )">
				<? echo $fin_prove;?></a>
    <?                          echo '</td>';
    ?>
    <?
			echo '</tr>';
			echo '<tr>'; echo '</tr>'; 		echo '<tr>'; echo '</tr>';
			echo '<tr>'; echo '</tr>'; 		echo '<tr>'; echo '</tr>';
		$cont++;
		}while($oIfx->SiguienteRegistro());
            }else{
                echo '<span class="fecha_letra">Sin Datos....</span>';
            }
	}
	$oIfx->Free();
	echo '<tr><td colspan="3">Se mostraron '.($cont-1).' Registros</td></tr>';
	echo '</table>';
	//echo $cod_producto;
        function fecha_mysql_func2($fecha){
                $fecha_array = explode('/',$fecha);
                $m = $fecha_array[0];
                $y = $fecha_array[2];
                $d = $fecha_array[1];

                return ( $y.'/'.$m.'/'.$d );
        }
?>    
</div>
</html>

