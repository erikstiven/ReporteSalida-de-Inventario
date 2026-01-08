<?
include('class.upload.php');

if (isset($_REQUEST['accion'])) $accion = $_REQUEST['accion'];
else $accion = '';
if (isset($_REQUEST['tipo'])) $tipo = $_REQUEST['tipo'];
else $tipo = '';

if ($accion == 'subir') {
	$save_dir = basename($_REQUEST['path'] . "/");

	//$save_dir='../FotosPaciente/';
	$archivo = '';
	foreach ($_FILES["miarchivo"]['tmp_name'] as $key => $tmp_name) {

		//condicional si el fuchero existe
		if ($_FILES["miarchivo"]["name"][$key]) {
			// Nombres de archivos de temporales
			//$fecha=date('d-m-Y H:i:s');

			$micro_date = microtime();
			$date_array = explode(" ", $micro_date);
			$date = date("Y-m-d H:i:s", $date_array[1]);
			$fecha = $date . " " . $date_array[0];

			$archivonombre = $_FILES["miarchivo"]["name"][$key];
			$fuente = $_FILES["miarchivo"]["tmp_name"][$key];

			//$carpeta = 'archivos_compras/'; //Declaramos el nombre de la carpeta que guardara los archivos
			$carpeta = 'reloj/';
			if (!file_exists($carpeta)) {
				mkdir($carpeta, 0777) or die("Hubo un error al crear el directorio de almacenamiento");
			}
			$dir = opendir($carpeta);
			$target_path = $carpeta . '/' . $archivonombre; //indicamos la ruta de destino de los archivos
			//VAIDACIONES
			if (!move_uploaded_file($fuente, $target_path)) {

				echo "Se ha producido un error, por favor revise los archivos e intentelo de nuevo";
			} else {
				//EXTRAEMOS LA EXTENSION DEL ARCHIVO
				$extension = pathinfo($target_path, PATHINFO_EXTENSION);
				$narchivonombre = "archivo_" . $fecha . "." . $extension;
				$narchivonombre = str_replace(" ", "_", $narchivonombre);
				$narchivonombre = str_replace(":", "-", $narchivonombre);

				$target_new_path = $carpeta . '/' . $narchivonombre;
				rename("$target_path", "$target_new_path");

				$archivo .= $narchivonombre . ":";


				$ctr = true;
			}
			closedir($dir); //Cerramos la conexion con la carpeta destino
		}
	}
	$afirma = explode(":", $archivo);
	if (count($afirma) == 2) {
		$archivo = $afirma[0];
	}

	if ($ctr) { ?>
		<script>
			var t = '<?= $tipo ?>';
			// Verificar si existe un campo específico en el documento del padre
			if (window.opener && window.opener.document.form1['<?= $_REQUEST['control'] ?>']) {
				// El campo existe en el documento del padre
				//console.log('El campo existe en el documento del padre.');
				window.opener.document.form1['<?= $_REQUEST['control'] ?>'].value = '<?= $save_dir . "/" . $archivo ?>';

			}
			if (t == 'img') {
				if (window.opener && window.opener.document.form1['img<?= $_REQUEST['control'] ?>']) {
					// El campo existe en el documento del padre
					//console.log('El campo existe en el documento del padre.');
					window.opener.document.form1['img<?= $_REQUEST['control'] ?>'].src = '<?= '../' . $save_dir . "/" . $archivo ?>';
				}
			}


			// Forma de pago cuando es otro id y name de form1
			if (window.opener && window.opener.document.formpago['<?= $_REQUEST['control'] ?>']) {
				// El campo existe en el documento del padre
				//console.log('El campo existe en el documento del padre.');
				window.opener.document.formpago['<?= $_REQUEST['control'] ?>'].value = '<?= $save_dir . "/" . $archivo ?>';

			}
			if (t == 'img') {
				if (window.opener && window.opener.document.formpago['img<?= $_REQUEST['control'] ?>']) {
					// El campo existe en el documento del padre
					//console.log('El campo existe en el documento del padre.');
					window.opener.document.formpago['img<?= $_REQUEST['control'] ?>'].src = '<?= '../' . $save_dir . "/" . $archivo ?>';
				}
			}
			window.close();
		</script>
	<? } else { ?>
		<script>
			alert("No se pudo subir el archivo al servidor");
		</script>
<? }
}
?>


<!DOCTYPE html>
<html>

<head>
	<meta charset=utf-8>
	<title>Capturar pantalla del sitio web desde URL PHP</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

	<script type="text/javascript">
		window.resizeTo(650, 450);
		window.moveTo(screen.availWidth / 2 - 200, screen.availHeight / 2 - 200);
	</script>
	<style type="text/css">
		* {
			font-family: Segoe, "Segoe UI", "DejaVu Sans", "Trebuchet MS", Verdana, sans-serif
		}

		.main {
			margin: auto;
			border: 1px solid #7C7A7A;
			width: 70%;
			text-align: left;
			padding: 30px;
			background: #337ab7
		}

		input[type=submit] {
			background: #6ca16e;
			width: 100%;
			padding: 5px 15px;
			background: #ccc;
			cursor: pointer;
			font-size: 16px;
		}

		input[type=text] {
			width: 40%;
			padding: 5px 15px;
			height: 25px;
			font-size: 16px;
		}

		.form-control {
			padding: 0px 0px;
		}
	</style>
</head>

<body bgcolor="#bed7c0">
	<br>
	<div class="main">
		<h1>
			<center>
				<font color="#ffffff">Carga de Archivos <br>JirehWeb</font>
			</center>
		</h1>
		<div class="panel panel-primary">
			<div class="panel-body">
				<form name="MiForm" id="MiForm" method="post" action="cargar.php?accion=subir&control=<?= $_REQUEST['control'] ?>&path=./../../<?= $_REQUEST['path'] ?>&tipo=<?= $tipo ?>" enctype="multipart/form-data">

					<div class="form-group">
						<label class="col-sm-2 control-label">Archivos: <font color="red">Tamaño Máximo Permitido 50MB</font></label>
						<div class="col-sm-8">
							<input type="file" class="form-control" id="miarchivo[]" name="miarchivo[]" multiple="">
						</div>
						<button type="submit" class="btn btn-primary">Cargar Multiple</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>

</html>