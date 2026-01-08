<? /********************************************************************/ ?>
<? /* NO MODIFICAR ESTA SECCION*/ ?>
<? include_once('../_Modulo.inc.php');?>
<? include_once(HEADER_MODULO);?>
<? if ($ejecuta) { ?>
<? /********************************************************************/ ?>
<?
	if(isset($_REQUEST['id'])) $cod=$_REQUEST['id'];
		else $cod='';
?>
<script>

        function genera_autorizacion(){           
			xajax_autoriza(xajax.getFormValues("form1"));
        }
		
		function modificar(){           
			xajax_upload(xajax.getFormValues("form1"), <?=$cod?>);
        }

	function cerrar_ventana(){
		CloseAjaxWin();
	}
	

</script>
<!-- Divs contenedores!-->
<div align="center">
    <form id="form1" name="form1" action="javascript:void(null);">
      <table align="center" border="0" cellpadding="2" cellspacing="0" width="100%">
        <tr>
          	<td valign="top" align="center">
                    <div id="divFormularioDetalle"></div>
         	</td>
        </tr>
        </tr>
      </table>
     </form>
</div>
<script>
genera_autorizacion();
</script>
<? /********************************************************************/ ?>
<? /* NO MODIFICAR ESTA SECCION*/ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /********************************************************************/ ?>