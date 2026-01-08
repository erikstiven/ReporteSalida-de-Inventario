<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>
    <? /*     * ***************************************************************** */ ?>

    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen" /><link type="text/css" href="css/style.css" rel="stylesheet"></link>
    <link type="text/css" href="css/style.css" rel="stylesheet"></link>
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <!-- Morris Charts CSS -->
    <link href="css/plugins/morris.css" rel="stylesheet">

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>

    <script>

        
        function genera_formulario() {
            xajax_genera_cabecera_formulario();
        }

        function cerrar_ventana() {
            CloseAjaxWin();
        }

        function cargar_sucursal() {
            xajax_genera_cabecera_formulario('sucursal', xajax.getFormValues("form1"));
        }

        function consultar() {
            if (ProcesarFormulario() == true) {
				xajax_consultar(xajax.getFormValues("form1"));
			}
        }

        function consultarEvent(event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4 - ENTER
                consultar();
            }
        }
		
		function guardar() {
            if (ProcesarFormulario() == true) {
				xajax_guardar(xajax.getFormValues("form1"));
			}
        }
		
        // abrir archivo excel
        function abrir() {
            document.location = "../reporte_facturacion/excel.php";
        }
		
		function reporte_pedido() {
            var tmp = document.getElementById("archivo").value;
            if (tmp.length == 0) {
                alert('Por favor cargue el archivo...!');
            } else {
                xajax_reporteArchivoExcel(xajax.getFormValues("form1"));
            }
        }
		
		function procesar() {
            if (ProcesarFormulario() == true) {
				xajax_procesar(xajax.getFormValues("form1"));
			}
        }
		
		function descargarEjemplo() {
            document.location = "../datos_cash/MaestroDatosCash.xls";
        }
		
		function marcar(source) {
            checkboxes = document.getElementsByTagName('input'); //obtenemos todos los controles del tipo Input
            for (i = 0; i < checkboxes.length; i++) //recoremos todos los controles
            {
                if (checkboxes[i].type == "checkbox") //solo si es un checkbox entramos
                {
                    checkboxes[i].checked = source.checked; //si es un checkbox le damos el valor del checkbox que lo llamó (Marcar/Desmarcar Todos)
                }
            }
            //xajax_suma(xajax.getFormValues("form1"));
        }
		
		function generaProvision(){
			xajax_generaProvision(xajax.getFormValues("form1"));
		}
		
        function eliminar_lista_rubro() {
            var sel = document.getElementById("rubro");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_rubro(x, i, elemento) {
            var lista = document.form1.rubro;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

		
    </script>
    <body>
        <div align="center">
            <form id="form1" name="form1" action="javascript:void(null);">
                <div id="page-wrapper">
                    <div class="container-fluid">
                        <div class="row col-md-12">
                            <div id="divFormularioFacturacion" class="table-responsive"></div>
                        </div>
                        <div class="row col-md-12">
                            <div id="divReporteFacturacion" class="table-responsive"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </body>
    <script> genera_formulario()</script>
    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Morris Charts JavaScript -->
    <script src="js/plugins/morris/raphael.min.js"></script>
    <script src="js/plugins/morris/morris.min.js"></script>
    <script src="js/plugins/morris/morris-data.js"></script>


    <script type="text/javascript" language="javascript" src="js/jsWeb.js"></script>

    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>     			
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>