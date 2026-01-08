<?

  
if(isset($_REQUEST['accion'])) $accion=$_REQUEST['accion'];
else $accion='';

if($accion=='subir'){
 $save_dir =basename($_REQUEST['path']."/");
 
 $upload_dir=$_FILES['arc']['name'];
 //echo $upload_dir."  ".$_FILES['arc']['tmp_name']." ".$_REQUEST['path']."<br>";
 $ctr=copy($_FILES['arc']['tmp_name'],$_REQUEST['path'].'/'.$upload_dir);

     if($ctr){ ?>
        <script>
		window.opener.document.form1['<?=$_REQUEST['control']?>'].value='<?=$_REQUEST['path'].$upload_dir?>';
        window.opener.document.form1['img<?=$_REQUEST['control']?>'].src='<?='../'.$save_dir."/".$upload_dir?>';
        window.close();
        </script>
  <? }else{ ?>
        <script>
		alert("No se pudo subir la imagen al servidor");
        </script>
  <? }
}
?>
<html>
	<head>
		<title>Subir Archivos</title>
		<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
		<link href="../css/Formulario.css" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="../../../Js/Capas/niftycube.js"></script>
		<!-- Funciones js para el uso de formularios -->
		<script type="text/javascript">
			NiftyLoad=function(){
				Nifty("div#contenedor","big");
				Nifty("div#titulo","small");
			}	
			window.resizeTo(605,245);
			window.moveTo(screen.availWidth/2-200, screen.availHeight/2-200);
			function doValidar(){
			 if(document.form1.arc.value==''){
				alert('No ha seleccionado ningun archivo para subir!');
				document.form1.arc.focus();
				return false;
			 }
			 return true;
			}
		</script>
	</head>
	<body>
		<div align="center">
			<div id="contenedor" align="center">
				<div id="titulo" align="center">Subir Archivo</div>
				<hr />
				<FORM ENCTYPE="multipart/form-data" 
					ACTION="cargar.php?accion=subir&control=<?=$_REQUEST['control']?>&path=../../../../<?=$_REQUEST['path']?>" 
					METHOD="POST" 
					id="form1" 
					name="form1" 
					onSubmit="return doValidar();">
					
					<table width="100%" border="0" align="center" cellpadding="3" cellspacing="0" class="CampoFormulario">
						<TR>
							<TD align="center">
								Pulsa en el boton Examinar y elige el archivo de tu computadora.<br>
								Luego presiona el boton Subir.
							</TD>
						</tr>
						<TR>
							<TD nowrap align="center">
							  <INPUT 
									NAME="arc" 
									type="file" 
									class="BotonFormularioActivo" id="arc" 
									style="width:450px;" 
									size="60">
						   </td>
						</tr>
					</TABLE>
					<hr>
					<table width="50%" border="0" align="center" cellpadding="0" cellspacing="0" class="CampoFormulario">
						<TR>
							<TD align="center">
								<INPUT 
									type="submit" 
									class="BotonFormulario" 
									value="Subir" 
									onMouseOver="javascript:this.className='BotonFormularioActivo';" 
									onMouseOut="javascript:this.className='BotonFormulario';">
								<INPUT 
									type="button" 
									class="BotonFormulario" 
									value="Cancelar" 
									onMouseOver="javascript:this.className='BotonFormularioActivo';" 
									onMouseOut="javascript:this.className='BotonFormulario';" 
									onClick="window.close();">
							</TD>
						</TR>
					</table>
				</FORM>
			</div>
		</div>
	</BODY>
</HTML>