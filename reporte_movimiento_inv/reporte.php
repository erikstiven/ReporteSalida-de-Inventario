<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>
    <? /*     * ***************************************************************** */ ?>

    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/dataTables/dataTables.buttons.min.css" media="screen">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/Ionicons/css/ionicons.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/select2/dist/css/select2.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skinsfolder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/dataTables/dataTables.bootstrap.min.css" media="screen">


    <!--JavaScript-->
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.flash.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.jszip.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.pdfmake.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.vfs_fonts.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.html5.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.print.min.js"></script>

    <!-- Select2 -->
    <script src="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/select2/dist/js/select2.full.min.js"></script>

    <!-- AdminLTE App -->
    <script src="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/js/adminlte.min.js"></script>

    <!--CSS-->
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/treeview/css/bootstrap-treeview.css" media="screen">
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/dataTables/dataTables.bootstrap.min.css">
    <script src="media/js/lenguajeusuario_producto.js"></script>

    <script>
        function genera_formulario() {
            xajax_genera_cabecera_formulario();
        }

        //---------------------------------------------------------
        //FUNCIONES PARA AUTOCOMPLETAR LOS SUPLIDORES
        //---------------------------------------------------------
        
        //caso cuando vienen con suplidor iniciales
        function autocompletar(empresa, event) {
            if (event.keyCode == 115 || event.keyCode == 13) {
                $("#ModalClpv").modal("show");
                xajax_clpv_reporte(xajax.getFormValues("form1"));
            }
        }
        //caso cuando solo da click en consultar
        function autocompletar_btn(empresa) {
            $("#ModalClpv").modal("show");
            xajax_clpv_reporte(xajax.getFormValues("form1"));

        }

        //funcion para seleccionar el suplidor dentro del modal
        /*function datos_clpv(cod, cli, ruc, dir, tel, cel, vend, cont, pre, fpago, tpago, fec, auto, serie, fec_venc, dia, contr, ini, fin, cuenta, correo) {
            document.form1.cliente.value = cod;
            document.form1.cliente_nombre.value = cli;
            document.form1.ruc.value = ruc;
            document.form1.tipo_pago.value = tpago;
            document.form1.forma_pago1.value = fpago;
            //document.form1.auto_prove.value 		= auto;
            //document.form1.fecha_validez.value 	= fec;
            //document.form1.serie_prove.value 		= serie;
            var f1 = fec_venc;
            var f2 = new Date();
            if (f1 > f2) {
                document.form1.fecha_entrega.value = fec_venc;
            }
            if (dia == 0) {
                var fecha_compra = document.getElementById('fecha_pedido').value;
                document.form1.fecha_entrega.value = fecha_compra;
            }
            document.form1.fecha_final.value = fec_venc;
            document.form1.plazo.value = dia;
            document.form1.dias_fp.value = dia;
            document.form1.contri_prove.value = contr;
            document.form1.cuenta_prove.value = cuenta;
            document.form1.dir_prove.value = dir;
            document.form1.tel_prove.value = tel;
            document.form1.correo_prove.value = correo;
            document.form1.producto.focus();
            $("#ModalClpv").modal("hide");
        }*/

        function datos_clpv(cod, cli) {

        // Guardar código del proveedor
        document.form1.cliente.value = cod;

        // Mostrar nombre
        document.form1.cliente_nombre.value = cli;

        // Cerrar el modal
        $("#ModalClpv").modal("hide");
        }

        //---------------------------------------------------------
        //FUNCIONES PARA AUTOCOMPLETAR LOS SUPLIDORES
        //---------------------------------------------------------

        function cargar_sucursal() {
            xajax_genera_cabecera_formulario('sucursal', xajax.getFormValues("form1"));
        }

        function cargar_tran() {
            xajax_genera_cabecera_formulario('tran', xajax.getFormValues("form1"));
        }

        function agregar() {
            if (ProcesarFormulario() == true) {
                xajax_guardar(xajax.getFormValues("form1"));
                limpiar();
            }
        }

        function consultar() {
            var op = 0;
            if (ProcesarFormulario() == true) {
                xajax_consultar(xajax.getFormValues("form1"), op);
            }
        }

        function cargar_modifi(id_det, empresa, sucursal) {
            AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../precierre/modificar.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&id_det=' + id_det + '&empresa=' + empresa + '&sucursal=' + sucursal, 'DetalleShow', 'iframe', 'Modificar', '700', '200', '10', '10', '1', '1');
        }

        function cerrar_ventana() {
            CloseAjaxWin();
        }

        function limpiar() {
            if (document.getElementById("cantidad")) {
                document.getElementById('cantidad').value = 0;
            }
            if (document.getElementById("valor")) {
                document.getElementById('valor').value = 0;
            }
            if (document.getElementById("banco")) {
                document.getElementById('banco').value = '';
            }
            if (document.getElementById("tarjeta")) {
                document.getElementById('tarjeta').value = '';
            }
            if (document.getElementById("numero")) {
                document.getElementById('numero').value = '';
            }
        }

        function aprobar() {
            if (ProcesarFormulario() == true) {
                xajax_aprobar(xajax.getFormValues("form1"));
            }
        }

        // abrir archivo excel
        function abrir() {
            document.location = "../reporte_movimiento_inv/excel.php";
        }

        // abrir archivo excel
        function abrir_detalle_excel() {
            document.location = "../reporte_movimiento_inv/excel_detalle.php";
        }

        function genera_documento(tipo_documento, id, clavAcce) {
            xajax_genera_documento(tipo_documento, id, clavAcce);
        }

        function generar_pdf() {
            if (ProcesarFormulario() == true) {
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=.370, top=255, left=130";
                var pagina = '../../Include/documento_pdf.php?sesionId=<?= session_id() ?>';
                window.open(pagina, "", opciones);
            }
        }

        function autoriza_sri(cod) {
            AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../reporte_facturacion/autoriza.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&id=' + cod, 'DetalleShow', 'iframe', 'Autorizacion Sri', '400', '150', '0', '0', '0', '0');
        }

        function vista_previa_(id, empr, sucu) {
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
            var pagina = '../reporte_movimiento_inv/vista_previa.php?sesionId=<?= session_id() ?>&codigo=' + id + '&empr=' + empr + '&sucu=' + sucu;
            window.open(pagina, "", opciones);
        }


        function vista_previa_totales(id, empr, sucu) {
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
            var pagina = '../reporte_movimiento_inv/vista_previa_totales.php?sesionId=<?= session_id() ?>&codigo=' + id + '&empr=' + empr + '&sucu=' + sucu;
            window.open(pagina, "", opciones);
        }

        function order_fact(op) {
            xajax_consultar(xajax.getFormValues("form1"), op);
        }

        function buscar_pala() {
            find();
        }


        function seleccionaItem(empr, sucu, ejer, mes, asto) {
            $("#miModal2").modal("show");
            $("#divInfo").html('');
            $("#divDirectorio").html('');
            $("#divRetencion").html('');
            $("#divDiario").html('');
            $("#divAdjuntos").html('');
            xajax_verDiarioContable(xajax.getFormValues("form1"), empr, sucu, ejer, mes, asto);
        }

        function vista_previa_diario(idempresa, sucursal, cod_prove, asto_cod, ejer_cod, prdo_cod) {
            xajax_genera_pdf_doc_compras(idempresa, sucursal, asto_cod, ejer_cod, prdo_cod);
        }

        function generar_pdf_compras() {
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=.370, top=255, left=130";
            var pagina = '../../Include/documento_pdf3.php?sesionId=<?= session_id() ?>';
            window.open(pagina, "", opciones);
        }

        function genera_etiquetas(id){
            $("#ModalEtiquetas").modal("show");
            xajax_formulario_etiqueta(id, xajax.getFormValues("form1"));
        }
        function etiquetasPrint(){
            //var op = document.getElementById('etiquetam').value;
            var ancho = document.getElementById('ancho').value;
            var alto = document.getElementById('alto').value;     
    
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=850, height=500, top=180, left=290";
            var pagina = '../inventario_compra/code_.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false'+'&ancho='+ancho+'&alto='+alto;
            window.open(pagina, "", opciones);
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
        }

        function procesar(){
            xajax_enviar_etiquetas(xajax.getFormValues("form1"));   
	    }
    </script>

    <body>
        <div class="container-fluid">
            <form id="form1" name="form1" action="javascript:void(null);">
                <div class="main row">
                    <div class="col-md-12">
                        <div id="DivPresupuesto" class="table-responsive"></div>
                    </div>
                    <div class="col-md-12">
                        <div id="DivReporte" class="table-responsive"></div>
                    </div>
                    <div class="col-md-12">
                        <div id="divFormularioTotal" class="table-responsive"></div>
                    </div>
                </div>


                <div class="modal fade" id="miModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="myModalLabel">DIARIO CONTABLE <span id="divTituloAsto"></span></h4>
                            </div>
                            <div class="modal-body">
                                <div>
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li role="presentation" class="active"><a href="#divInfo" aria-controls="divInfo" role="tab" data-toggle="tab">Informacion</a></li>
                                        <li role="presentation"><a href="#divDirectorio" aria-controls="divDirectorio" role="tab" data-toggle="tab">Directorio</a></li>
                                        <li role="presentation"><a href="#divRetencion" aria-controls="divRetencion" role="tab" data-toggle="tab">Retencion</a></li>
                                        <li role="presentation"><a href="#divDiario" aria-controls="divDiario" role="tab" data-toggle="tab">Diario</a></li>
                                        <li role="presentation"><a href="#divAdjuntos" aria-controls="divAdjuntos" role="tab" data-toggle="tab">Adjuntos</a></li>
                                    </ul>

                                    <!-- Tab panes -->
                                    <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane active" id="divInfo">...</div>
                                        <div role="tabpanel" class="tab-pane" id="divDirectorio">...</div>
                                        <div role="tabpanel" class="tab-pane" id="divRetencion">...</div>
                                        <div role="tabpanel" class="tab-pane" id="divDiario">...</div>
                                        <div role="tabpanel" class="tab-pane" id="divAdjuntos">...</div>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="width: 100%;">
                <!------------------------------------------------------------------->
                <!--DIV DONDE SE INYECTARA LA TABLA INTERNA-->
                <!------------------------------------------------------------------->
                <div class="modal fade" id="ModalClpv" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                <!------------------------------------------------------------------->
                <!--FIN DIV DONDE SE INYECTARA LA TABLA INTERNA-->
                <!------------------------------------------------------------------->

            	<div class="modal fade" id="ModalEtiquetas" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
        	    </div>

            </form>
        </div>
    </body>


    <script>
        genera_formulario()

        function init() {
            var search = '<?= $ruc ?>';
            var table = $('#tbclientes').DataTable({
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

            table.search(search).draw();
        }
    </script>
    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>