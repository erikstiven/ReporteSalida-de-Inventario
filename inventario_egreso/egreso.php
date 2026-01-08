<?

/********************************************************************/ ?>
<? /* NO MODIFICAR ESTA SECCION*/ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>
    <? /********************************************************************/ ?>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <!-- ejecuta la funci�n mostrar una vez que se carga la p�gina  -->
    <script language="javascript">
        window.onload = function() {
            cambiarPestanna('pestanas', 'pestana3');
        }
    </script>

    <!--CSS-->
    <link rel="stylesheet" href="media/css/bootstrap.css">
    <link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="media/css/style.css">
    <link rel="stylesheet" href="media/font-awesome/css/font-awesome.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/select2/dist/css/select2.min.css">

    <!--Javascript-->
    <script src="media/js/jquery-1.10.2.js"></script>
    <script src="media/js/jquery.dataTables.min.js"></script>
    <script src="media/js/dataTables.keyTable.min.js"></script>
    <script src="media/js/dataTables.bootstrap.min.js"></script>
    <script src="media/js/bootstrap.js"></script>
    <!-- Select2 -->
    <script src="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/select2/dist/js/select2.full.min.js"></script>


    <!-- FUNCIONES PARA MANEJO DE PESTA�AS  -->

    <script type="text/javascript">
        function cambiarPestanna(pestannas, pestanna) {
            // Obtiene los elementos con los identificadores pasados.
            pestanna = document.getElementById(pestanna.id);
            //alert(pestanna);
            listaPestannas = document.getElementById(pestannas.id);

            // Obtiene las divisiones que tienen el contenido de las pesta�as.
            cpestanna = document.getElementById('c' + pestanna.id);
            tpestanna = document.getElementById('t' + pestanna.id);
            listacPestannas = document.getElementById('contenido' + pestannas.id);

            i = 0;
            // Recorre la lista ocultando todas las pesta�as y restaurando el fondo
            // y el padding de las pesta�as.

            while (typeof listacPestannas.getElementsByTagName('div')[i] != 'undefined') {
                $(document).ready(function() {
                    if (listacPestannas.getElementsByTagName('div')[i].id == "cpestana1" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "cpestana2" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "tpestana1" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "tpestana2" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "cpestana3" ||
                        listacPestannas.getElementsByTagName('div')[i].id == "tpestana3") {
                        $(listacPestannas.getElementsByTagName('div')[i]).css('display', 'none');
                    }
                });
                i += 1;
            }

            i = 0;
            while (typeof listaPestannas.getElementsByTagName('li')[i] != 'undefined') {
                $(document).ready(function() {
                    $(listaPestannas.getElementsByTagName('li')[i]).css('background', '');
                    $(listaPestannas.getElementsByTagName('li')[i]).css('padding-bottom', '');
                });
                i += 1;
            }

            $(document).ready(function() {
                // Muestra el contenido de la pesta�a pasada como parametro a la funcion,
                // cambia el color de la pesta�a y aumenta el padding para que tape el
                // borde superior del contenido que esta justo debajo y se vea de este
                // modo que esta seleccionada.
                //alert("recupera");
                $(cpestanna).css('display', '');
                $(tpestanna).css('display', '');
                $(pestanna).css('background', '#3783FE');
                $(pestanna).css('padding-bottom', '2px');
            });
            // var prueba = document.getElementById('divMateriaPrima');
            // alert(prueba);
            //alert("d");
        }
    </script>

    <!-- ESTILO PARA MANEJO DE PESTA�AS-->
    <style type="text/css">
        /*PARA CREACION DE PESTA?AS*/
        .contenedor {
            width: 96%;
            margin: auto;
            background-color: #EBEBEB;
            color: bisque;
            padding: 10px 15px 10px 25px;
            border-radius: 10px;
            box-shadow: 0 10px 10px 0px rgba(0, 0, 0, 0.8);
        }

        .contenedorConsulta {
            width: 300px;
            margin: auto;
            background-color: #EBEBEB;
            color: bisque;
            padding: 5px 15px 25px 25px;
            border-radius: 10px;
            /*box-shadow: 0 10px 10px 0px rgba(0, 0, 0, 0.8);*/
        }

        #pestanas {
            background-color: #EBEBEB;
            float: top;
            font-size: 3ex;
            font-weight: bold;
        }

        #pestanas ul {
            margin-left: -40px;
        }

        #pestanas li {
            list-style-type: none;
            float: left;
            text-align: center;
            margin: 0px 2px -2px -0px;
            background: #A6C4E1;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
            border: 2px #808080;
            border-bottom: dimgray;
            padding: 0px 20px 0px 20px;
        }

        #pestanas a:link {
            text-decoration: none;
            color: white;
        }

        #contenidopestanas {
            clear: both;
            background: #D3D3D3;
            padding: 10px 0px 10px 10px;
            border-radius: 5px;
            border-top-left-radius: 0px;
            border: 2px #808080;
        }

        /*FIN DE CREACION DE PESTA?AS*/
    </style>

    <script>
        function genera_formulario() {
            xajax_genera_formulario_pedido();
        }

        function cargar_sucursal() {
            xajax_genera_formulario_pedido('sucursal', xajax.getFormValues("form1"));
        }

        function cargar_transaccion() {
            xajax_genera_formulario_pedido('tran', xajax.getFormValues("form1"));
        }

        function autocompletar(empresa, event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                var cliente_nom = document.getElementById('cliente_nombre').value;
                var empresa = document.getElementById('empresa').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=830, height=380, top=255, left=130";
                var pagina = '../inventario_egreso/buscar_cliente.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cliente=' + cliente_nom + '&empresa=' + empresa;
                window.open(pagina, "", opciones);
            }
        }

        function cargar_secuencial() {
            var sucursal = document.getElementById("sucursal").value;
            xajax_genera_formulario_pedido(sucursal, 'nuevo', xajax.getFormValues("form1"));
        }

        function guardar_pedido(id_op) {
            if (ProcesarFormulario() == true) {
                var ctrl = document.getElementById("ctrl").value;
                if (ctrl == 1) {
                    document.getElementById("ctrl").value = 2;
                    xajax_guarda_pedido(id_op, xajax.getFormValues("form1"));
                } else {
                    var codigo = document.getElementById("nota_compra").value;
                    var cont = codigo.length;
                    if (cont > 0) {
                        alert('!!!!....Error el Egreso ya esta Ingresado....!!!!!...');
                    } else {
                        alert('Por favor espere Guardando Informacion...');
                    } // fin if
                }
            }
        }



        function cancelar_pedido() {
            confirmar = confirm("�Deseas Guardar los cambios?");
            if (confirmar) {
                guardar_pedido();
            } else {
                genera_formulario();
            }
        }

        function totales() {
            // IMPRIME EL TOTAL DEL PEDIDO
            // descuento general
            if (!document.getElementById("descuento_general")) {
                var desc = 0;
            } else {
                var desc = document.getElementById("descuento_general").value;
            }
            // flete
            if (!document.getElementById("flete")) {
                var flete = 0;
            } else {
                var flete = document.getElementById("flete").value;
                if (flete == '') {
                    flete = 0;
                }
            }
            // otros
            if (!document.getElementById("otros")) {
                var otro = 0;
            } else {
                var otro = document.getElementById("otros").value;
                if (otro == '') {
                    otro = 0;
                }
            }
            //anticipo
            if (!document.getElementById("anticipo")) {
                var anticipo = 0;
            } else {
                var anticipo = document.getElementById("anticipo").value;
                if (anticipo == '') {
                    anticipo = 0;
                }
            }
            xajax_total_grid(desc, flete, otro, anticipo, xajax.getFormValues("form1"));
        }

        function cargar_descuento(desc, fac, iva) {
            // descuento
            var a = document.getElementById("descuento_general").value;
            if (a == '') {
                a = 0;
                document.getElementById("descuento_general").value = a;
            }

            if (desc < a) {
                alert('El valor maximo de descuento para este usuario es de ' + desc + ' %');
                a = desc;
                document.getElementById("descuento_general").value = desc;
            }
            xajax_agrega_modifica_grid_update(a, xajax.getFormValues("form1"));
        }


        function cerrar_ventana() {
            CloseAjaxWin();
        }

        function focus_ruc() {
            var ruc = document.getElementById("ruc");
            ruc.focus();
            var value = ruc.value;
            ruc.value = "";
            ruc.value = value;
        }

        function tecla_ruc(event) {
            // F4 115
            // ENTER 13
            if (event.keyCode == 13) {
                var sucursal = document.getElementById("sucursal").value;
                xajax_genera_formulario_pedido(sucursal, 'cargar_ruc', xajax.getFormValues("form1"));
            }
        }


        function cargar_tran() {
            xajax_cargar_tran(xajax.getFormValues("form1"));
        }

        function autocompletar_producto(empresa, event, op) {

            var prod_nom = document.getElementById('producto').value;
            var cod_nom = document.getElementById('codigo_producto').value;
            var sucu = document.getElementById('sucursal').value;
            var bodega = document.getElementById('bodega').value;
            var fecha = document.getElementById('fecha_pedido').value;
            var empresa = document.getElementById('empresa').value;


            const check_filtros_adicionales = document.getElementById("check_filtros_adicionales");
            if (check_filtros_adicionales.checked) {
                var linea = document.getElementById('linea').value;

                if (bodega == '' || linea == '') {
                    alert('Debe seleccionar Bodega y al menos una linea');
                } else {
                    xajax_abrir_modal_prod_filtro(xajax.getFormValues("form1"));
                }
            } else {
                if (event.keyCode == 115 || event.keyCode == 13) { // F4

                    if (bodega == '') {
                        alert('Debe seleccionar Bodega');
                    } else {
                        var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=830, height=380, top=255, left=130";
                        var pagina = '../inventario_egreso/buscar_prod.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&producto=' + prod_nom + '&codigo=' + encodeURIComponent(cod_nom) + '&opcion=' + op + '&sucursal=' + sucu + '&bodega=' + bodega + '&fecha=' + fecha + '&empresa=' + empresa + '&linea=' + linea + '&grupo=' + grupo + '&categoria=' + cate + '&marca=' + marca;
                        window.open(pagina, "", opciones);
                    }

                }
            }


        }

        function cargar_producto() {
            var producto = document.getElementById('producto').value;
            var cantidad = document.getElementById('cantidad').value;
            if (producto == '' || cantidad <= 0) {
                alert('Debe seleccionar producto y cantidad');
            } else {
                var control_lote = document.getElementById("f1").style.display;
                var control_serie = document.getElementById("f2").style.display;
                if (control_lote == 'block') {
                    var lote = document.getElementById('loteProd').value;
                    var fela = document.getElementById('fElaLoteProd').value;
                    var fcad = document.getElementById('fCadLoteProd').value;
                    if (lote == '' || fela == '' || fcad == '') {
                        alert('Debes seleccionar Lote, Fecha Elaboracion, Fecha Caducidad');
                    } else {
                        xajax_agrega_modifica_grid(0, 0, '', xajax.getFormValues("form1"));

                    }
                } else if (control_serie == 'block') {
                    var lote = document.getElementById('serieProd').value;
                    if (lote == '') {
                        alert('Debes seleccionar Serie');
                    } else {
                        xajax_agrega_modifica_grid(0, 0, '', xajax.getFormValues("form1"));
                    }
                } else {
                    xajax_agrega_modifica_grid(0, 0, '', xajax.getFormValues("form1"));

                }
            }
        }




        function cargar_update_cant(id) {
            var a = document.getElementById(id + "_cantidad").value;
            xajax_actualiza_grid(id, xajax.getFormValues("form1"));
        }

        function cargar_update_grid(id, producto, cantidad, costo_prod, iva, desc1, desc2, bode, cuenta1, cuenta2, cuenta3, cuenta4, detalle, lote, fecha_elaboracion, fecha_caducidad) {
            var desc = document.getElementById("descuento_general").value;
            xajax_agrega_modifica_grid(1, desc, producto, xajax.getFormValues("form1"), id, cantidad, costo_prod, iva, desc1, desc2, bode, cuenta1, cuenta2, cuenta3, cuenta4, detalle, lote, fecha_elaboracion, fecha_caducidad);
        }

        function limpiar_prod() {
            foco('producto');
            document.getElementById("producto").value = '';
            document.getElementById("cantidad").value = 1;
            document.getElementById("codigo_producto").value = '';
            document.getElementById("costo").value = 0;
            document.getElementById("iva").value = 0;
            document.getElementById("cuenta_inv").value = '';
            document.getElementById("cuenta_iva").value = '';
            document.getElementById("detalle").value = '';
            document.getElementById("stock").value = '0';
            document.getElementById("loteProd").value = '';
            document.getElementById("serieProd").value = '';
            document.getElementById("fCadLoteProd").value = '';
            document.getElementById("fElaLoteProd").value = '';
            document.getElementById("f1").style.display = "none";
            document.getElementById("f2").style.display = "none";

        }

        function foco(idElemento) {
            document.getElementById(idElemento).focus();
        }


        function consultar() {
            // COMERCIAL
            //jsShowWindowLoad();

            var sucursal = document.getElementById('sucursal').value;

            if (sucursal != '') {
                xajax_cargar_ord_compra_respaldo(xajax.getFormValues("form1"));
            } else {
                alert('Seleccione la sucursal');
            }
        }


        // carga imagen a servidor
        function upload_image(id) { //Funcion encargada de enviar el archivo via AJAX
            $(".upload-msg").text('Cargando...');
            var inputFileImage = document.getElementById(id);
            var file = inputFileImage.files[0];
            var data = new FormData();
            data.append(id, file);

            $.ajax({
                url: "upload.php?id=" + id, // Url to which the request is send
                type: "POST", // Type of request to be send, called as method
                data: data, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                contentType: false, // The content type used when sending data to the server.
                cache: false, // To unable request pages to be cached
                processData: false, // To send DOMDocument or non processed data file it is set to false
                success: function(data) // A function to be called if request succeeds
                {
                    $(".upload-msg").html(data);
                    window.setTimeout(function() {
                        $(".alert-dismissible").fadeTo(500, 0).slideUp(500, function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            });
        }

        // busqueda de autorizacion proveedor
        function auto_proveedor(empresa, event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                var serie_prove = document.getElementById('serie_prove').value;
                var prove = document.getElementById('cliente').value;
                var empresa = document.getElementById('empresa').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=830, height=380, top=255, left=130";
                var pagina = '../inventario_egreso/buscar_auto_prove.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&serie=' + serie_prove + '&prove=' + prove + '&empresa=' + empresa;
                window.open(pagina, "", opciones);
            }
        }

        function num_digito(op, id) {
            xajax_num_digito(op, id, xajax.getFormValues("form1"));
        }

        function control_tran() {
            xajax_control_tran(xajax.getFormValues("form1"));
        }

        function habilitar_form() {
            $("#factura").removeAttr("disabled");
            $("#cliente_nombre").removeAttr("disabled");
            $("#ruc").removeAttr("disabled");
        }

        function deshabilitar_form() {
            $("#factura").attr("disabled", "");
            $("#cliente_nombre").attr("disabled", "");
            $("#ruc").attr("disabled", "");
        }

        function autocompletar_factura(empresa, event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                var factura = document.getElementById('factura').value;
                var sucursal = document.getElementById('sucursal').value;
                var cliente = document.getElementById('cliente').value;
                var empresa = document.getElementById('empresa').value;
                AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../inventario_egreso/lista_factura.php?sesionId=<?= session_id() ?>&mOp=false&mVer=false&factura=' + factura + '&sucursal=' + sucursal + '&cliente=' + cliente + '&empresa=' + empresa, 'DetalleShow', 'iframe', 'Factura', '550', '300', '10', '10', '1', '1');
            }
        }

        function cargar_grid() {
            // descuento
            var a = document.getElementById("descuento_general").value;
            xajax_cargar_grid(a, xajax.getFormValues("form1"));
        }

        function cta_gasto_22(id, event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                var cod = document.getElementById(id).value;
                var empresa = document.getElementById('empresa').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../inventario_egreso/cuentas_gasto.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cuenta=' + cod + '&id=' + id + '&empresa=' + empresa;
                window.open(pagina, "", opciones);
            }
        }

        function centro_costo_22(id, event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                var cod = document.getElementById(id).value;
                var empresa = document.getElementById('empresa').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../inventario_egreso/centro_costo.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cuenta=' + cod + '&id=' + id + '&empresa=' + empresa;
                window.open(pagina, "", opciones);
            }
        }


        function vista_previa() {
            var empr = document.getElementById('empresa').value;
            var serial = document.getElementById('serial').value;
            var secu = document.getElementById('nota_compra').value;
            if (serial != '') {
                xajax_genera_pdf_doc(xajax.getFormValues("form1"));
            } else {
                alert('Por favor ingrese el Movimiento para generar vista previa');
            }
        }


        function elimina_detalle(id) {
            xajax_elimina_detalle(id, xajax.getFormValues("form1"));
        }



        function validaTeclaLote(event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                var id = null;
                var bode = document.getElementById('bodega').value;
                var prod = document.getElementById('codigo_producto').value;
                var sucu = document.getElementById('sucursal').value;
                var lote = document.getElementById('loteProd').value;

                if (bode != '' && prod != '') {
                    var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=800, height=320, top=255, left=250";
                    var pagina = '../inventario_egreso/busca_lotes.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&id=' + id + '&bode=' + bode + '&prod=' + encodeURIComponent(prod) + '&sucu=' + sucu + '&lote_prod=' + encodeURIComponent(lote);
                    window.open(pagina, "", opciones);
                } else {
                    alert('Seleccione Bodega y Producto para continuar...!');
                }
            } else {
                // document.getElementById('loteProd').value = '';
            }
        }

        function generar_pdf() {
            if (ProcesarFormulario() == true) {
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=.370, top=255, left=130";
                var pagina = '../../Include/documento_pdf3.php?sesionId=<?= session_id() ?>';
                window.open(pagina, "", opciones);
            }
        }

        function modal_cargar_archivo() {
            xajax_modal_cargar_archivo(xajax.getFormValues("form1"));
        }

        function abre_modal() {
            $("#mostrarmodal").modal("show");
        }

        function cerrar_modal() {
            $("#mostrarmodal").modal("hide");
        }

        function ocultar_procesar() {
            document.getElementById('div_procesar').style.display = "none";
        }

        function mostrar_procesar() {
            document.getElementById('div_procesar').style.display = "block";
        }

        function processar_archivo() {
            xajax_cargar_ord_compra(xajax.getFormValues("form1"));
        }

        function cargar_filtros_adicionales() {
            const check_filtros_adicionales = document.getElementById("check_filtros_adicionales");
            if (check_filtros_adicionales.checked) {
                //resultado.textContent = "El checkbox está seleccionado.";
                document.getElementById('div_select_linp').style.display = "block";
                document.getElementById('div_select_grpr').style.display = "block";
                document.getElementById('div_select_cate').style.display = "block";
                document.getElementById('div_select_marc').style.display = "block";
            } else {
                //resultado.textContent = "El checkbox no está seleccionado.";
                document.getElementById('div_select_linp').style.display = "none";
                document.getElementById('div_select_grpr').style.display = "none";
                document.getElementById('div_select_cate').style.display = "none";
                document.getElementById('div_select_marc').style.display = "none";
            }
        }

        function cargar_arbol(cod) {
            xajax_cargar_arbol(xajax.getFormValues("form1"), cod);
        }

        function borrar_lista(form) {
            document.getElementById(form).options.length = 0;
        }

        function anadir_elemento(x, i, elemento, componente) {
            var lista = document.getElementById(componente);
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function abre_modal_prod_filtro() {
            $("#mostrarModalProdFiltro").modal("show");
        }

        function cierra_modal_prod_filtro() {
            $("#mostrarModalProdFiltro").modal("hide");
        }


        function control_stock_filtro(cod_prod, stock) {
            var cantidad = document.getElementById('cantidad_prod_' + cod_prod).value;

            stock = parseFloat(stock);
            cantidad = parseFloat(cantidad);

            if (cantidad > stock) {
                alerts('Cantidad sobrepasa el stock !!', 'error')
                document.getElementById('cantidad_prod_' + cod_prod).value = stock
            }
        }

        function procesar_informacion_filtro() {
            xajax_procesar_informacion_filtro(xajax.getFormValues("form1"));

        }

        function alerts(mensaje, tipo) {
            if (tipo == 'success') {
                Swal.fire({
                    type: tipo,
                    title: mensaje,
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 2000,
                    width: '600',
                })
            } else {

                Swal.fire({
                    type: tipo,
                    title: mensaje,
                    showCancelButton: false,
                    showConfirmButton: true,
                    width: '600',

                })
            }

        }

        function datos(a, b, c, d, e, f, g, h, nom_prod, min, stock, cinventario) {
            // g => lote; h => serie
            if (g == 'S') {
                document.getElementById("f1").style.display = "block";
                document.getElementById("f2").style.display = "none";
            } else {
                document.getElementById("f1").style.display = "none";
            }

            if (h == 'S') {
                document.getElementById("f1").style.display = "none";
                document.getElementById("f2").style.display = "block";
            } else {
                document.getElementById("f2").style.display = "none";
            }


            // g => Lote : h => Serie
            if (g == 'S' && h == 'S') {
                alert('El producto tiene lote y serie a la vez, se le apicará uniamente la serie. Para configurar lote y serie vaya a Configuracion/Inventario/Ficha producto');
            } else {

                document.getElementById("codigo_producto").value = a;
                document.getElementById("producto").value = b;
                document.getElementById("cuenta_inv").value = c;
                document.getElementById("cuenta_iva").value = d;
                document.getElementById("costo").value = e;
                document.getElementById("stock").value = f;
                if (cinventario == 'S') {
                    if (parseInt(stock) < parseInt(min)) {
                        alert('Te estas quedando sin estock !!. PRODUCTO: ' + nom_prod);
                    }
                }
            }
            cierra_modal_prod_filtro();
        }

        function abrir_modal_distribucion(cont) {
            xajax_abrir_modal_distribucion(cont, xajax.getFormValues("form1"));
        }

        function abre_modal_prod_distribucion() {
            $("#mostrarModalProdDistribucion").modal("show");
        }

        function cierra_modal_prod_distribucion() {
            $("#mostrarModalProdDistribucion").modal("hide");
        }

        function validar_cuenta_contable(cod_prod) {
            xajax_validar_cuenta_contable(cod_prod, xajax.getFormValues("form1"));
        }

        function validar_centro_costos(cod_prod) {
            xajax_validar_centro_costos(cod_prod, xajax.getFormValues("form1"));
        }

        function generaSelect2() {
            $('.select2').select2();
        }

        function calcular_valor_porcentaje(ccosn_cod_ccosn, cont_data_grid, tipo) {
            xajax_calcular_valor_porcentaje(ccosn_cod_ccosn, cont_data_grid, tipo, xajax.getFormValues("form1"));
        }

        function calcular_totales_distri(ccosn_cod_ccosn, cont_data_grid, tipo) {
            xajax_calcular_totales_distri(ccosn_cod_ccosn, cont_data_grid, tipo, xajax.getFormValues("form1"));
        }

        function agregar_distribucion(cont_data_grid) {
            xajax_agregar_distribucion(cont_data_grid, xajax.getFormValues("form1"));

        }
    </script>

    <!--DIBUJA FORMULARIO FILTRO-->

    <body onload='javascript:cambiarPestanna(pestanas, pestana1);'>
        <div align="center">
            <form id="form1" name="form1" action="javascript:void(null);">

                <div class="col-md-12 col-sm-12 col-xs-12">
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="divCompraMenu" style="margin-top: 5px !important;">
                            <div id="divModalProductoFiltro" class="table-responsive"></div>
                            <div id="divModalProdDistribucion" class="table-responsive"></div>
                        </div>
                    </div>
                </div>


                <div class="main row">
                    <div class="col-md-12">
                        <div id="divFormularioTotal" class="table-responsive"></div>
                    </div>
                </div>

                <table align="center" border="0" cellpadding="2" cellspacing="0" width="100%">
                    <tr>
                        <td valign="top" align="center">
                            <div id="divFormularioCabecera"></div>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" align="center">
                            <div id="divFormularioDetalle"></div>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" align="right" width="60%">
                            <div id="divTotal" style="width: 60%;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" align="center">
                            <div id="divFormularioDetalle2"></div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div id=" divGrid"></div>
    </body>
    <script>
        genera_formulario(); /*genera_detalle();genera_form_detalle();*/
    </script>
    <? /********************************************************************/ ?>
    <? /* NO MODIFICAR ESTA SECCION*/ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /********************************************************************/ ?>