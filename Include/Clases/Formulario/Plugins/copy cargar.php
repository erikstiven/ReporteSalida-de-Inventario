<?
include('class.upload.php');
  
if(isset($_REQUEST['accion'])) $accion=$_REQUEST['accion'];
else $accion='';
if(isset($_REQUEST['tipo'])) $tipo=$_REQUEST['tipo'];
else $tipo='';

if($accion=='subir'){
 $save_dir =basename($_REQUEST['path']."/");
 
 //$save_dir='../FotosPaciente/';
 
 
 $handle = new upload($_FILES['archivo']);
  if ($handle->uploaded) {
      //$handle->file_new_name_body   = 'image_resized';
      $handle->image_resize = false;
      
	  //$handle->process($_REQUEST['path']);
	  
	 $handle->process('reloj/');
	  
      if ($handle->processed) {
                $nombreArchivo = $handle->file_dst_name;
                $xml           = $handle->file_dst_name_ext;
                if($xml=='xml.txt'){
                    $nombreArchivo = substr($nombreArchivo, 0, -4);
                }
		$handle->clean();
		$ctr=true;
      } else {
         echo  '<script>alert("'.$handle->error.'");</script>';
      }
  }

     if($ctr){ ?>
        <script>
		var t='<?=$tipo?>';
		window.opener.document.form1['<?=$_REQUEST['control']?>'].value='<?=$save_dir."/".$nombreArchivo?>';
        if (t=='img')
		window.opener.document.form1['img<?=$_REQUEST['control']?>'].src='<?='../'.$save_dir."/".$nombreArchivo?>';
		window.close();
        </script>
  <? }else{ ?>
        <script>
		alert("No se pudo subir el archivo al servidor");
        </script>
  <? }
}
?>
<html>
	<head>
		<title>Subir Archivos</title>
		<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
		<link href="../Css/Formulario.css" rel="stylesheet" type="text/css">
		<!-- Funciones js para el uso de formularios -->
		<script type="text/javascript">
			window.resizeTo(655,285);
			window.moveTo(screen.availWidth/2-200, screen.availHeight/2-200);
			function doValidar(){
			 if(document.form1.archivo.value==''){
				alert('No ha seleccionado ningun archivo para subir!');
				document.form1.archivo.focus();
				return false;
			 }
			 return true;
			}
		</script>
	</head>
	<body>
		<div align="center">
			<div id="contenedor" align="center">
				<div align="center" class="celdaLabel" id="titulo">Subir Archivo</div>
                <FORM ENCTYPE="multipart/form-data" 
					ACTION="cargar.php?accion=subir&control=<?=$_REQUEST['control']?>&path=./../../<?=$_REQUEST['path']?>&tipo=<?=$tipo?>" 
					METHOD="POST" 
					id="form1" 
					name="form1" 
					onSubmit="return doValidar();">
					
					<table width="100%" border="0" align="center" cellpadding="3" cellspacing="0" class="CampoFormulario">
						<TR>
							<TD align="center" class="celdaLabel">
							  Pulsa en el boton Examinar y elige el archivo de tu computadora.<br>
								Luego presiona el boton Subir.							</TD>
					  </tr>
						<TR>
						   <TD nowrap align="center"><input 
									name="archivo" 
									type="file" 
									class="BotonFormularioActivo" 
									id="archivo" 
									style="width:450px;" 
									size="60"></td>
						</tr>
					</TABLE>
					<table width="50%" border="0" align="center" cellpadding="0" cellspacing="0" class="CampoFormulario">
						<TR>
							<TD align="center">
								<INPUT 
									type="submit" 
									class="BotonFormulario" 
									value="Subir" 
									onMouseOver="javascript:this.className='BotonFormularioActivo';" 
									onMouseOut="javascript:this.className='BotonFormulario';">
								&nbsp;&nbsp;
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