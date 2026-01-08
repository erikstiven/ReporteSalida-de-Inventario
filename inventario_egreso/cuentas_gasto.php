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
<title>Cuentas Contables</title>
<!--CSS--> 
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="js/jquery/plugins/simpleTree/style.css" />
    <link rel="stylesheet" href="media/css/bootstrap.css">
    <link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="media/font-awesome/css/font-awesome.css">
	<link type="text/css" href="css/style.css" rel="stylesheet"></link>

    <!--Javascript--> 
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script src="media/js/jquery-1.10.2.js"></script>
    <script src="media/js/jquery.dataTables.min.js"></script>
    <script src="media/js/dataTables.bootstrap.min.js"></script>          
    <script src="media/js/bootstrap.js"></script>
    <script type="text/javascript" language="javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>    
    <script src="media/js/lenguajeusuario_producto.js"></script>   

<script>
	function datos( a, txt, id){
                window.opener.document.getElementById(txt).value = a;
                window.opener.cargar_update_cant(id);
		close();
	}
</script>
</head>

<body>

<?
	$oIfx = new Dbo;
	$oIfx -> DSN = $DSN_Ifx;
	$oIfx -> Conectar();
	
	$empresa = $_GET['empresa'];
        $txt     = $_GET['id'];
        list($id_grid, $n) = explode('_', $txt);
        $cod     = $_GET['cuenta'];
	$fecha   = date("m-d-Y");

        if(empty($cod)){
            $sql_tmp = '';
        }elseif(!empty($cod)){
            $numero = str_replace('.', '', $cod);
            $is_numeric = is_numeric($numero);
            if($is_numeric==true){
                $sql_tmp = " and cuen_cod_cuen like '$cod%' ";
            }else{
                $sql_tmp = " and cuen_nom_cuen like UPPER('$cod%') ";
            }  
        }

        // cuenta inicial
        $sql = "select cuen_cod_cuen, cuen_nom_cuen, cuen_nom_ingl from saecuen where
                    cuen_cod_empr = $empresa and
                    cuen_mov_cuen = 1
                    $sql_tmp
                    order by cuen_cod_cuen ";	
?> 
</body>
<div id="contenido">
<?	
	$cont=1;
	echo '<div class="table-responsive">';
	echo '<table class="table table-bordered table-hover" align="center" style="width: 98%;">';
	echo '<tr><td colspan="7" align="center" class="bg-primary">CLIENTES - PROVEEDORES</td></tr>';
	echo '<tr>
				<td align="center" class="bg-primary">Cuenta</td>
				<td align="center" class="bg-primary">Nombre - Espa&ntilde;ol</td>
				<td align="center" class="bg-primary">Nombre - Ingles</td>
		  </tr>';
		  
		  
    if ($oIfx->Query($sql)){
        if( $oIfx->NumFilas() > 0 ){
		do {
			$cod_cuenta=htmlentities($oIfx->f('cuen_cod_cuen'));
			$nom_cuenta=htmlentities($oIfx->f('cuen_nom_cuen'));
                        $nom_cuenta_ingles=htmlentities($oIfx->f('cuen_nom_ingl'));

                        if ($sClass=='off') $sClass='on'; else $sClass='off';
                        echo '<tr height="20" class="'.$sClass.'"
                                    onMouseOver="javascript:this.className=\'link\';"
                                    onMouseOut="javascript:this.className=\''.$sClass.'\';">';
				echo '<td width="100">';		
	?>
    				<a href="#" onclick="datos('<? echo $cod_cuenta;?>', '<? echo $txt;?>', '<? echo $id_grid;?>' ) ">
					               <? echo $cod_cuenta;?></a>
    <?
				echo '</td>';
				echo '<td>'
	?>
    				<a href="#" onclick="datos('<? echo $cod_cuenta;?>', '<? echo $txt;?>', '<? echo $id_grid;?>' ) ">
						     <? echo $nom_cuenta;?></a>
    <?
				echo '</td>';
				echo '<td>';
	
	?>
                                <a href="#" onclick="datos('<? echo $cod_cuenta;?>', '<? echo $txt;?>', '<? echo $id_grid;?>' ) ">
                                                    <? echo $nom_cuenta_ingles;?></a>
	
	<?			echo '</td>';
				
				
        ?>
    <?
			echo '</td>';
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
?>    
</div>
</html>

