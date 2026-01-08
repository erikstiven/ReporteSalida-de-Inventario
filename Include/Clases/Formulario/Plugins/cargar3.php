<?
include('class.upload.php');

if (isset($_REQUEST['accion'])) $accion = $_REQUEST['accion'];
else $accion = '';
if (isset($_REQUEST['tipo'])) $tipo = $_REQUEST['tipo'];
else $tipo = '';

if ($accion == 'subir') {
	$save_dir = basename($_REQUEST['path'] . "/");

	//$save_dir='../FotosPaciente/';
$archivo='';
	foreach ($_FILES["miarchivo"]['tmp_name'] as $key => $tmp_name) {

       //condicional si el fuchero existe
		if($_FILES["miarchivo"]["name"][$key]) {
			// Nombres de archivos de temporales
			$fecha=date('d-m-Y H:i:s');
		
			$archivonombre = $_FILES["miarchivo"]["name"][$key];

		

			$fuente = $_FILES["miarchivo"]["tmp_name"][$key]; 
			
			
			$carpeta = 'archivos_compras/'; //Declaramos el nombre de la carpeta que guardara los archivos
			if(!file_exists($carpeta)){
				mkdir($carpeta, 0777) or die("Hubo un error al crear el directorio de almacenamiento");	
			}
			$dir=opendir($carpeta);

			$narchivonombre=$fecha."_".$archivonombre;
				$narchivonombre=str_replace(" ","_",$narchivonombre);
				$narchivonombre=str_replace(":","-",$narchivonombre);
			$target_path = $carpeta.'/'.$narchivonombre; //indicamos la ruta de destino de los archivos

			if(!move_uploaded_file($fuente, $target_path)) {	

				echo "Se ha producido un error, por favor revise los archivos e intentelo de nuevo";
				
				} 
				else{

					$ctr=true;
				}
                
				$archivo.=$narchivonombre.":";
			closedir($dir); //Cerramos la conexion con la carpeta destino
		}
		
	}


	if($ctr){ ?>
        <script>
		var t='<?=$tipo?>';
		window.opener.document.form1['<?=$_REQUEST['control']?>'].value='<?=$archivo?>';
        if (t=='img')
		window.opener.document.form1['img<?=$_REQUEST['control']?>'].src='<?='../'.$save_dir."/".$archivo?>';
		window.close();
        </script>
  <? }else{ ?>
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
			background: #85c587
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
		<h1>Cargar múltiples archivos PHP</h1>
		<div class="panel panel-primary">
			<div class="panel-body">
				<form name="MiForm" id="MiForm" method="post" action="cargar.php?accion=subir&control=<?=$_REQUEST['control']?>&path=./../../<?=$_REQUEST['path']?>&tipo=<?=$tipo?>" enctype="multipart/form-data">
					<h4 class="text-center">Cargar Múltiple Archivos</h4>
					<div class="form-group">
						<label class="col-sm-2 control-label">Archivos</label>
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