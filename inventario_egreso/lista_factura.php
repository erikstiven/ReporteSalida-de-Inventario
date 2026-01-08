<? /********************************************************************/ ?>
<? /* NO MODIFICAR ESTA SECCION*/ ?>
<? include_once('../_Modulo.inc.php');?>
<? include_once(HEADER_MODULO);?>
<? if ($ejecuta) { ?>
<? /********************************************************************/ ?>
<?
	if(isset($_REQUEST['mOp'])) $mOp=$_REQUEST['mOp'];
		else $mOp='';
	if(isset($_REQUEST['Id'])) $Id=$_REQUEST['Id'];
		else $Id='-1';
	if(isset($_REQUEST['factura'])) $factura=$_REQUEST['factura'];
		else $factura = '';
        if(isset($_REQUEST['sucursal'])) $sucursal=$_REQUEST['sucursal'];
		else $sucursal='';
        if(isset($_REQUEST['cliente'])) $cliente=$_REQUEST['cliente'];
		else $cliente = '';
        if(isset($_REQUEST['empresa'])) $empresa=$_REQUEST['empresa'];
		else $empresa='';
//        echo $factura;
?>


	
<script>
	
	function genera_formulario(){
		xajax_lista_factura( '<?=$factura?>', <?=$sucursal?>, <?=$cliente?>, <?=$empresa?> );
	}

    function cerrar_ventana(){
		CloseAjaxWin();
	}

    function cargar_factura(){
		parent.xajax_cargar_factura( '<?=$factura?>',  <?=$sucursal?>, xajax.getFormValues("form1"), <?=$empresa?> );
	}        

</script>

<!--DIBUJA FORMULARIO FILTRO-->
<div align="center">
    <form id="form1" name="form1" action="javascript:void(null);">
      <table align="center" border="0" cellpadding="2" cellspacing="0" width="100%">
        <tr>
          	<td valign="top" align="center">
                    <div id="divFormularioCabecera"></div>
         	</td>
        </tr>
        <tr>
        	<td>
        		<div align="center" id="Utilidades"></div>
        	</td>
        </tr>
      </table>
     </form>
</div>
<div id="divGrid" ></div>
<script>genera_formulario();/*genera_detalle();genera_form_detalle();*/</script>
<? /********************************************************************/ ?>
<? /* NO MODIFICAR ESTA SECCION*/ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /********************************************************************/ ?>