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
		function datos(a, b, c, d, e, f, g, h, nom_prod, min, stock, cinventario) {


			// g => lote; h => serie
			if (g == 'S') {
				window.opener.document.getElementById("f1").style.display = "block";
				window.opener.document.getElementById("f2").style.display = "none";
			} else {
				window.opener.document.getElementById("f1").style.display = "none";
			}

			if (h == 'S') {
				window.opener.document.getElementById("f1").style.display = "none";
				window.opener.document.getElementById("f2").style.display = "block";
			} else {
				window.opener.document.getElementById("f2").style.display = "none";
			}

			// g => Lote : h => Serie
			if (g == 'S' && h == 'S') {
				alert('El producto tiene lote y serie a la vez, se le apicará uniamente la serie. Para configurar lote y serie vaya a Configuracion/Inventario/Ficha producto');
			} else {

				window.opener.document.form1.codigo_producto.value = a;
				window.opener.document.form1.producto.value = b;
				window.opener.document.form1.cuenta_inv.value = c;
				window.opener.document.form1.cuenta_iva.value = d;
				window.opener.document.form1.costo.value = e;
				window.opener.document.form1.stock.value = f;
				if (cinventario == 'S') {
					if (parseInt(stock) < parseInt(min)) {
						if (confirm('Te estas quedando sin estock !!. PRODUCTO: ' + nom_prod)) {
							close();
						}
					}
					close();
				} else {
					close();
				}
			}

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


	$cinventario   = $_SESSION['U_CINVENTARIO'];

	$idempresa    = $_GET['empresa'];
	$sucursal     = $_GET['sucursal'];
	$prod_nom     = $_GET['producto'];
	$codigo_nom   = $_GET['codigo'];
	$opcion       = $_GET['opcion'];
	$bodega       = $_GET['bodega'];
	$fecha        = fecha_informix_func($_GET['fecha']);


	if ($opcion == 1) {
		// producto
		$sql_tmp = " and (p.prod_nom_prod like upper('%$prod_nom%') OR p.prod_cod_prod like upper('%$prod_nom%')) ";
	} elseif ($opcion == 2) {
		// codigo
		$sql_tmp = " and ( p.prod_cod_prod like upper('%$codigo_nom%') or
                               p.prod_cod_prod in ( select  prcb_cod_prod  from saeprcb where
														prcb_cod_empr = $idempresa and
														prcb_cod_sucu = $sucursal and
														prcb_cod_barr = '$codigo_nom' )
							  ) ";
	}

	$sql = "select un.unid_nom_unid, tp.tpro_des_tpro, b.bode_nom_bode, pr.prbo_cod_prod, p.prod_nom_prod, pr.prbo_dis_prod, pr.prbo_cta_inv, pr.prbo_cta_ideb,
                        pr.prbo_uco_prod, pr.prbo_iva_porc, prod_lot_sino, prod_ser_prod, prod_cod_barr3
                        from saeprbo pr, saeprod p, saebode b, saetpro tp, saeunid un
                        where pr.prbo_dis_prod > 0 and
                        p.prod_cod_prod     = pr.prbo_cod_prod and
                        pr.prbo_cod_bode     = b.bode_cod_bode and
                        tp.tpro_cod_tpro     = p.prod_cod_tpro and
                        un.unid_cod_unid     = pr.prbo_cod_unid and
                        p.prod_cod_empr     = $idempresa and
                        p.prod_cod_sucu     = $sucursal and
                        pr.prbo_cod_empr    = $idempresa and
                        pr.prbo_cod_bode    = '$bodega'
						AND pr.prbo_cod_sucu = $sucursal
                        $sql_tmp 
						group by 1,2,3,4,5,6,7,8,9,10,11,12,13
						order by  2 limit 100";
	?>
</body>
<div id="contenido">
	<?
	$cont = 1;
	echo '<div style="text-align: center">
		<h4>
			LISTA DE PRODUCTOS
		</h4>
	</div>
	<div style="margin: 10px !important;">';
	echo '<table id="tbproductos" class="table table-condensed table-responsive"><thead>';
	echo '<tr>
				<!-- <th class="fecha_letra">No-</th> -->
				<th class="fecha_letra" align="center">Bodega</th>
				<th class="fecha_letra" align="center">Codigo</th>
				<th class="fecha_letra" align="center">Producto</th>
				<th class="fecha_letra" align="center">Referencia</th> 
				<th class="fecha_letra" align="center">Tipo</th>
				<th class="fecha_letra" align="center">Unidad Medida</th>
				<th class="fecha_letra" align="center">lotes</th>
				<th class="fecha_letra" align="center">Series</th>
				<th class="fecha_letra" align="center">Stock</th> 
		  </tr>
		  </thead>
          <tbody>';

	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			do {
				$codigo     = ($oIfx->f('prbo_cod_prod'));
				$nom_bode     = ($oIfx->f('bode_nom_bode'));
				$tipo_prod     = ($oIfx->f('tpro_des_tpro'));
				$detalle_prod     = ($oIfx->f('prod_det_prod'));
				$nom_prod     = htmlentities($oIfx->f('prod_nom_prod'));
				$stock     = $oIfx->f('prbo_dis_prod');
				$cuenta     = $oIfx->f('prbo_cta_inv');
				$cuenta_iva     = $oIfx->f('prbo_cta_ideb');
				$prbo_uco_prod     = $oIfx->f('prbo_uco_prod');
				$iva     = $oIfx->f('prbo_iva_porc');
				$unidad_prod     = $oIfx->f('unid_nom_unid');
				$lote             = $oIfx->f('prod_lot_sino');
				$serie             = $oIfx->f('prod_ser_prod');
				$min 		= $oIfx->f('prbo_smi_prod');
				$mac             = $oIfx->f('prod_cod_barr3');
				if ($lote == 1 || $lote == 'S') {
					$lote = 'S';
				} else {
					$lote = 'N';
				}

				if ($serie == 1 || $serie == 'S') {
					$serie = 'S';
				} else {
					$serie = 'N';
				}

				// echo '<script languaje="javascript">alert("hola");</script>';

				// ULTIMO COSTO
				//$costo = ultimo_costo_func($idempresa, $sucursal, $codigo, $bodega, $fecha, $oIfxA);
				// COSTO PROMEDIO
				$costo = costo_promedio_func($idempresa, $sucursal, $codigo, $bodega, $fecha, $oIfxA);
				if ($sClass == 'off') $sClass = 'on';
				else $sClass = 'off';
				echo '<tr height="20" class="' . $sClass . '"
                                        onMouseOver="javascript:this.className=\'link\';"
                                        onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
				//echo '<td>' . $cont . '</td>';
				echo '<td>'
	?>
				<a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_prod; ?>', '<? echo $cuenta; ?>', '<? echo $cuenta_iva; ?>', '<? echo $costo; ?>' , '<? echo $stock; ?>','<? echo $cinventario; ?>'  )">
					<? echo $nom_bode; ?></a>
				<?
				echo '</td>';
				echo '<td width="100">';
				?>
				<a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_prod; ?>', '<? echo $cuenta; ?>', '<? echo $cuenta_iva; ?>', '<? echo $costo; ?>' , '<? echo $stock; ?>','<? echo $cinventario; ?>'   )">
					<? echo $codigo; ?></a>
				<?
				echo '</td>';
				echo '<td>'
				?>
				<a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_prod; ?>', '<? echo $cuenta; ?>', '<? echo $cuenta_iva; ?>', '<? echo $costo; ?>' , '<? echo $stock; ?>','<? echo $cinventario; ?>'  )">
					<? echo $nom_prod; ?></a>
				<?
				echo '</td>';
				echo '<td>'
				?>
				<a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_prod; ?>', '<? echo $cuenta; ?>', '<? echo $cuenta_iva; ?>', '<? echo $costo; ?>' , '<? echo $stock; ?>','<? echo $cinventario; ?>'  )">
					<? echo $detalle_prod; ?></a>
				<?
				echo '</td>';
				echo '<td>'
				?>
				<a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_prod; ?>', '<? echo $cuenta; ?>', '<? echo $cuenta_iva; ?>', '<? echo $costo; ?>' , '<? echo $stock; ?>','<? echo $cinventario; ?>'  )">
					<? echo $tipo_prod; ?></a>
				<?
				echo '</td>';
				echo '<td>'
				?>
				<a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_prod; ?>', '<? echo $cuenta; ?>', '<? echo $cuenta_iva; ?>', '<? echo $costo; ?>' , '<? echo $stock; ?>','<? echo $cinventario; ?>'  )">
					<? echo $unidad_prod; ?></a>
				<?
				echo '</td>';
				echo '<td>'
				?>
				<a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_prod; ?>', '<? echo $cuenta; ?>', '<? echo $cuenta_iva; ?>', '<? echo $costo; ?>' , '<? echo $stock; ?>','<? echo $cinventario; ?>'  )">
					<? echo $lote; ?></a>
				<?
				echo '</td>';
				echo '<td>'
				?>
				<a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_prod; ?>', '<? echo $cuenta; ?>', '<? echo $cuenta_iva; ?>', '<? echo $costo; ?>' , '<? echo $stock; ?>','<? echo $cinventario; ?>'  )">
					<? echo $serie; ?></a>
				<?
				echo '</td>';
				echo '<td>';
				?>
				<a href="#" onclick="datos('<? echo $codigo; ?>','<? echo $nom_prod; ?>', '<? echo $cuenta; ?>', '<? echo $cuenta_iva; ?>', '<? echo $costo; ?>' , '<? echo $stock; ?>','<? echo $cinventario; ?>'  )">
					<? echo $stock; ?></a>
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
	?>
	<script>
		init();

		function init() {

			var table = $('#tbproductos').DataTable({
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