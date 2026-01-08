<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<?  ?>
<?  ?>
<? if ($ejecuta) { ?>

    <script>

        function detalles_onu(id_olt, serie){
            var detalles_onu = ``;

            var datos_get = {id_olt, serie};
            var datos_post = {};
            var datos = {
                "endpoint": "DETALLES_ONU",
                "datos_get": datos_get,
                "datos_post": datos_post,
                "tipo_sistema": 1
            };
            enviaDatosBack(datos, function (response) {
                var data = response;
                detalles_onu = `<div class="table-responsive">
                                    <h4 class="text-primary" align="center">Detalles de onu: `+serie+`</h4>
                                        <table border="1" class="table table-bordered table-striped table-condensed" style="width: 100%; margin-bottom: 0px;">
                                                <tr>
                                                    <th align="center" td class="success fecha_letra fecha_letra" colspan="4" >
                                                        <pre style="height: 340px; overflow-y: scroll; background-color:black; color:white;">`+JSON.stringify(data)+`</pre>
                                                    </th>
                                                </tr>
                                        </table>
                                    </div>`;

                document.getElementById('detalles_onu').innerHTML = detalles_onu;
            }, function (error) {
                var data = error;
                document.getElementById('detalles_onu').innerHTML = data.responseText;
            });

            var sHtml = `<div class="modal-dialog modal-lg" role="document" style="width:95%;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="margin-top:0xp;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="thumbnail" id="detalles_onu" align="center">
                                                <h4 class="text-primary">La búsqueda solicitada puede tomar un tiempo en retornar una respuesta, por favor espere. </h4>
                                                <br>
                                                <i class="fas fa-spinner fa-spin fa-5x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>`;
            
            document.getElementById('miModal').innerHTML = sHtml;
            $("#miModal").modal("show");
        }

        function niveles_atenuacion(id_olt, serie, gest_new_snmp, id_marca, id_modelo) {
            if(id_marca == 5){
                var endpoint = 'NIVELES_ONU_TELNET';
            }else{
                var endpoint = gest_new_snmp == 'S' ? 'NIVELES_ONU_SNMP' : 'NIVELES_ONU_TELNET';
            }

            var datos_get = { id_olt, serie };
            var datos_post = {};
            var datos = {
                "endpoint": endpoint,
                "datos_get": datos_get,
                "datos_post": datos_post,
                "tipo_sistema": 1
            };

            var sHtml = `<div class="modal-dialog modal-lg style="width:100%;"">
                            <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                <div class="col-md-12">
                                    <div class="thumbnail">
                                        <h4 class="text-primary" align="center">Niveles de atenuacion de la onu con sn: ${serie}.</h4>
                                        <br>
                                        <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                            <div class="col-md-2">
                                                <div class="alert alert-info alert-dismissible">
                                                <h5>OLT TX</h5>
                                                <div id="val_olt_tx"> <i class="fas fa-spinner fa-spin fa-5x"></i> </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="alert alert-info alert-dismissible">
                                                <h5>OLT RX</h5>
                                                <div id="val_olt_rx"> <i class="fas fa-spinner fa-spin fa-5x"></i> </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="alert alert-danger bg-yellow alert-dismissible">
                                                <h5>ONU TX</h5>
                                                <div id="val_onu_tx"> <i class="fas fa-spinner fa-spin fa-5x"></i> </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="alert alert-danger bg-yellow alert-dismissible">
                                                <h5>ONU RX</h5>
                                                <div id="val_onu_rx"> <i class="fas fa-spinner fa-spin fa-5x"></i> </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="alert alert-success alert-dismissible">
                                                <h5>ATENUACION UP</h5>
                                                <div id="val_att_up"> <i class="fas fa-spinner fa-spin fa-5x"></i> </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="alert alert-success alert-dismissible">
                                                <h5>ATENUACION DOWN</h5>
                                                <div id="val_att_down"> <i class="fas fa-spinner fa-spin fa-5x"></i> </div>
                                                </div>
                                            </div>
                                            </div>
                                            <div class="row">
                                            <div class="col-md-12">
                                                <div id="container1" style="width:100%; height:300px;"></div>
                                            </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                            </div>
                            </div>
                        </div>`;

            document.getElementById('miModal').innerHTML = sHtml;
            $("#miModal").modal("show");

            const chart1 = Highcharts.chart('container1', {
                chart: {
                    type: 'area',
                    events: {
                        load: function () {
                            var series = this.series;
                            let fetchTimeout; // Variable para almacenar el temporizador

                            function fetchData() {
                                if ($("#miModal").is(":visible")) {  // Verificar si el modal sigue visible
                                    enviaDatosBack(datos, function (response) {
                                        var olt_rx = response.olt.rx;
                                        var olt_tx = response.olt.tx;
                                        var onu_rx = response.onu.rx;
                                        var onu_tx = response.onu.tx;

                                        var atenuacion_up = response.atenuacion.up;
                                        var atenuacion_down = response.atenuacion.down;

                                        chart1.series[0].addPoint([olt_rx], true);
                                        chart1.series[1].addPoint([olt_tx], true);
                                        chart1.series[2].addPoint([onu_rx], true);
                                        chart1.series[3].addPoint([onu_tx], true);
                                        chart1.series[4].addPoint([atenuacion_up], true);
                                        chart1.series[5].addPoint([atenuacion_down], true);

                                        $('#val_olt_tx').html(olt_tx);
                                        $('#val_olt_rx').html(olt_rx);
                                        $('#val_onu_tx').html(onu_tx);
                                        $('#val_onu_rx').html(onu_rx);
                                        $('#val_att_up').html(atenuacion_up);
                                        $('#val_att_down').html(atenuacion_down);

                                        // Llamar recursivamente después de 2 segundos
                                        fetchTimeout = setTimeout(fetchData, 2000);
                                    }, function (error) {
                                        console.log("Error: " + error.responseText);
                                        // Continuar con las peticiones incluso si hay error
                                        fetchTimeout = setTimeout(fetchData, 2000);
                                    });
                                }
                            }

                            // Iniciar la primera llamada
                            fetchData();

                            // Limpiar el temporizador cuando se cierre el modal
                            $('#miModal').on('hidden.bs.modal', function () {
                                clearTimeout(fetchTimeout);
                            });
                        }
                    }
                },
                title: {
                    text: serie
                },
                xAxis: {
                    categories: [0]
                },
                yAxis: {
                    title: {
                        text: 'Niveles de atenuacion'
                    }
                },
                series: [
                    {
                        name: 'OLT TX',
                        data: [0]
                    },
                    {
                        name: 'OLT RX',
                        data: [0]
                    },
                    {
                        name: 'ONU RX',
                        data: [0]
                    },
                    {
                        name: 'ONU TX',
                        data: [0]
                    },
                    {
                        name: 'ATENUACION UP',
                        data: [0]
                    },
                    {
                        name: 'ATENUACION DOWN',
                        data: [0]
                    }
                ]
            });
        }

        function show_running(id_olt, serial){

            var detalles_onu = ``;

            var datos_get = {id_olt, serial};
            var datos_post = {};

            var datos = {
                "endpoint": "SHOW_RUNNING",
                "datos_get": datos_get,
                "datos_post": datos_post,
                "tipo_sistema": 1
            };

            enviaDatosBack(datos, function (response) {
                var data = response;
                detalles_onu = `
                                <div class="table-responsive">
                                    <h4 class="text-primary" align="center">Show Running Onu: `+serial+`</h4>
                                        <table border="1" class="table table-bordered table-striped table-condensed" style="width: 100%; margin-bottom: 0px;">
                                                <tr>
                                                    <th align="center" td class="success fecha_letra fecha_letra" colspan="4" >
                                                        <pre style="height: 340px; overflow-y: scroll; background-color:black; color:white;">`+JSON.stringify(data)+`</pre>
                                                    </th>
                                                </tr>
                                        </table>
                                    </div>
                                    <br>`;
                document.getElementById('detalles_onu').innerHTML = detalles_onu;
            }, function (error) {
                var data = error;
                document.getElementById('detalles_onu').innerHTML = data.responseText;
            });

            var sHtml = `<div class="modal-dialog modal-lg" role="document" style="width:95%;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="margin-top:0xp;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="thumbnail" id="detalles_onu" align="center">
                                                <h4 class="text-primary">La búsqueda solicitada puede tomar un tiempo en retornar una respuesta, por favor espere. </h4>
                                                <br>
                                                <i class="fas fa-spinner fa-spin fa-5x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>`;
            
            document.getElementById('miModal').innerHTML = sHtml;
            $("#miModal").modal("show");
                
          
        }

        function caracteristicas_onu(id_olt, serial){

            var detalles_onu = ``;

            var datos_get = {id_olt, serial};
            var datos_post = {};

            var datos = {
                "endpoint": "CARACTERISTICAS_ONU_SNMP",
                "datos_get": datos_get,
                "datos_post": datos_post,
                "tipo_sistema": 1
            };

            enviaDatosBack(datos, function (response) {

                var datos = response[0];

                var valida_reg_1 = datos.fields.onu_Sw_ver_Region1_Status
                var valida_reg_2 = datos.fields.onu_Sw_ver_Region2_Status
                var txt_activo_reg_1 = "(activo: No)";
                if(valida_reg_1 == 1){
                    txt_activo_reg_1 = "(activo: Si)";
                }
                var txt_activo_reg_2 = "(activo: No)";
                if(valida_reg_2 == 1){
                    txt_activo_reg_2 = "(activo: Si)";
                }

                var string_fecha = datos.time;
                var fecha = new Date(string_fecha);

                // Verifica si la fecha es válida
                if (!isNaN(fecha.getTime())) {
                    // Opcional: puedes usar toLocaleDateString para diferentes formatos según la configuración regional
                    var opciones = { 
                        year: 'numeric', 
                        month: '2-digit', 
                        day: '2-digit', 
                        hour: '2-digit', 
                        minute: '2-digit', 
                        second: '2-digit' 
                    };
                    var fechaFormateada = fecha.toLocaleDateString('es-ES', opciones).replace(',', '');
                }

                var detalles_html = `
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <br>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label class="text-primary">
                                        Tipo medición:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.measurement+`
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label class="text-primary">
                                        Fecha de la medición:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+fechaFormateada+`
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12" align="left">
                                    <label class="text-primary">
                                        Etiquetas:
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Dirección del Servidor:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.tags.host+`
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Ubicación SNMP:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.tags.onu_sn_enc+`
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12" align="left">
                                    <label class="text-primary">
                                        Detalles Onu:
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Puerto PON:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.fields.onuprt+`
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Version HW:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.fields.onu_hw_version+`
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Número de serie:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.fields.onu_sn+`
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Versión OMCC:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.fields.onu_Omcc+`
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Modelo:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.fields.onu_Model+`
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12" align="left">
                                    <label class="text-primary">
                                        Características reportadas por la ONU:
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Total puertos ETH:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.fields.onu_Total_Eth_ports+`
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Puertos GbEth:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.fields.onu_GbEth_ports+`
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Puertos FeEth:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.fields.onu_FeEth_ports+`
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Puertos VoIP:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.fields.onu_Voip_ports+`
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Total puertos CATV RF:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.fields.onu_RF_Catv_ports+`
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Total puertos WIFI RF:
                                    </label>
                                </div>
                                <div class="col-md-9" align="left">
                                    `+datos.fields.onu_RF_Wifi_ports+`
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12" align="left">
                                    <label class="text-primary">
                                        Versión de Software:
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Región 1:
                                    </label>
                                </div>
                                <div class="col-md-3" align="left">
                                    `+datos.fields.onu_Sw_ver_Region1+`
                                </div>
                                <div class="col-md-6" align="left">
                                    `+txt_activo_reg_1+`
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3" align="left">
                                    <label>
                                        Región 2:
                                    </label>
                                </div>
                                <div class="col-md-3" align="left">
                                    `+datos.fields.onu_Sw_ver_Region2+`
                                </div>
                                <div class="col-md-6" align="left">
                                    `+txt_activo_reg_2+`
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                $("#detalles_onu").html(detalles_html);
            }, function (error) {
                var data = error;
                document.getElementById('detalles_onu').innerHTML = data.responseText;
            });

            var sHtml = `<div class="modal-dialog modal-lg" role="document" style="width:95%;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="margin-top:0xp;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="thumbnail" id="detalles_onu" align="center">
                                                <h4 class="text-primary">La búsqueda solicitada puede tomar un tiempo en retornar una respuesta, por favor espere. </h4>
                                                <br>
                                                <i class="fas fa-spinner fa-spin fa-5x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>`;

            document.getElementById('miModal').innerHTML = sHtml;
            $("#miModal").modal("show");

        }

        function resync_config(id_olt, serial){
            var datos = {
                "id_olt": id_olt,
                "serial": serial
            };

            $.ajax({
                data: datos,
                url: '../../../modulos/int_ws_listado_onus/datos_onu.php',
                type: 'post',
                success: function(data_bdd) {
                    if (data_bdd.length == 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: "Error al obtener información de la base de datos."
                        });

                        return;
                    }
                    data_bdd = JSON.parse(data_bdd);

                    if (data_bdd.status == 200) {

                        var chasis = data_bdd.datos_onu.chasis;
                        var slot = data_bdd.datos_onu.slot;
                        var puerto = data_bdd.datos_onu.puerto;
                        var id_ont = data_bdd.datos_onu.id_ont;

                        Swal.fire({
                            title: 'Reconfiguración de onu',
                            icon: 'info',
                            text: `Esta seguro de reconfigurar la onu con serial: `+serial+`?.`,
                            customClass: 'swal-wide',
                            confirmButtonText: 'Si, continuar'
                        }).then((result) => {
                            if (result.value) {

                                jsShowWindowLoad();

                                var datos_get = {id_olt, serial, chasis, slot, puerto, id_ont};

                                var datos_post = {};
                                var datos = {
                                    "endpoint": "RECONFIGURACION_ONU",
                                    "datos_get": datos_get,
                                    "datos_post": datos_post,
                                    "tipo_sistema": 1
                                };
                                enviaDatosBack(datos, function (response) {
                                    if(response.status == 200){
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Exito',
                                            text: 'Se ha reconfigurado de forma exitosa la onu con serial: '+serial
                                        });
                                    }else{

                                        var error = response.mensaje;
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: error
                                        });
                                    }


                                    jsRemoveWindowLoad();
                                }, function (error) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops..',
                                        text: 'Error al ejecutar reconfiguracion de onu'
                                    });

                                    jsRemoveWindowLoad();
                                });
                            
                            }
                        })
                    } else {
                        Swal.fire({
                            type: 'error',
                            title: 'Oops...',
                            text: data_bdd.mensaje
                        })
                    }

                    jsRemoveWindowLoad();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    throw "Error bdd";
                    jsRemoveWindowLoad()
                }
            });

           
        }

        function obtener_mac(id_olt, serial, id_caja){
            var datos_get = {id_olt, serial};
            var datos_post = {};

            var detalles_mac = '';

            var datos = {
                "endpoint": "OBTENER_MAC",
                "datos_get": datos_get,
                "datos_post": datos_post,
                "tipo_sistema": 1
            };

            enviaDatosBack(datos, function (response) {
                var mac = response.mac_address;
                detalles_mac = `
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <label for="mac">
                                                        * MAC:
                                                    </label>
                                                    <input type="text" class="form-control" id="mac" value=`+mac+` readonly/>
                                                </div>
                                                <div class="col-md-4">
                                                    <button class="btn btn-success btn-lg btn-block" onclick="guardar_mac(`+id_caja+`)">
                                                        <i class="fa-solid fa-floppy-disk"></i> Guardar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                document.getElementById('detalles_mac').innerHTML = detalles_mac;
            }, function (error) {
                var data = error;
                document.getElementById('detalles_mac').innerHTML = data.responseText;
            });

            var sHtml = `<div class="modal-dialog modal-lg" role="document" style="width:95%;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="margin-top:0xp;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="thumbnail" id="detalles_mac" align="center">
                                                <h4 class="text-primary">Consultando MAC, la búsqueda solicitada puede tomar un tiempo en retornar una respuesta, por favor espere. </h4>
                                                <br>
                                                <i class="fas fa-spinner fa-spin fa-5x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>`;
            
            document.getElementById('miModal').innerHTML = sHtml;
            $("#miModal").modal("show");
        }

        function hacer_ping_router(id_router, ip){
            var detalles_onu = ``;

            var datos_get = {id_router, ip};
            var datos_post = {};

            var datos = {
                "endpoint": "PING_ROUTER",
                "datos_get": datos_get,
                "datos_post": datos_post,
                "tipo_sistema": 2
            };

            enviaDatosBack(datos, function (response) {
                var data = response;
                detalles_onu = `<div class="table-responsive">
                                    <h4 class="text-primary" align="center">Ping: `+ip+`</h4>
                                        <table border="1" class="table table-bordered table-striped table-condensed" style="width: 100%; margin-bottom: 0px;">
                                                <tr>
                                                    <th align="center" td class="success fecha_letra fecha_letra" colspan="4" >
                                                        <pre style="height: 340px; overflow-y: scroll; background-color:black; color:white;">`+data+`</pre>
                                                    </th>
                                                </tr>
                                        </table>
                                    </div>`;

                document.getElementById('detalles_onu').innerHTML = detalles_onu;
            }, function (error) {
                var data = error;
                document.getElementById('detalles_onu').innerHTML = data.responseText;
            });

            var sHtml = `<div class="modal-dialog modal-lg" role="document" style="width:95%;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="margin-top:0xp;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="thumbnail" id="detalles_onu" align="center">
                                                <h4 class="text-primary">Realizando la conexión hacia el equipo, por favor espere. </h4>
                                                <br>
                                                <i class="fas fa-spinner fa-spin fa-5x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>`;
            
            document.getElementById('miModal').innerHTML = sHtml;
            $("#miModal").modal("show");
           
        }

        function ver_adress_list(id_router, ip){
            let id_api = $('#tipoTecnologia').val();
            let estado_api = $('#estadoApi').val();

            if(estado_api == 'A' && id_api == 4){

                var detalles_onu = ``;

                var datos_get = {id_router, ip};
                var datos_post = {};

                var datos = {
                    "endpoint": "VER_ADRESS_LIST_IP",
                    "datos_get": datos_get,
                    "datos_post": datos_post,
                    "tipo_sistema": 2
                };

                enviaDatosBack(datos, function (response) {
                    var data = response;
                    detalles_onu = `<div class="table-responsive">
                                        <h4 class="text-primary" align="center">Adress list: `+ip+`</h4>
                                            <table border="1" class="table table-bordered table-striped table-condensed" style="width: 100%; margin-bottom: 0px;">
                                                    <tr>
                                                        <th align="center" td class="success fecha_letra fecha_letra" colspan="4" >
                                                            <pre style="height: 340px; overflow-y: scroll; background-color:black; color:white;">`+data+`</pre>
                                                        </th>
                                                    </tr>
                                            </table>
                                        </div>`;

                    document.getElementById('detalles_onu').innerHTML = detalles_onu;
                }, function (error) {
                    var data = error;
                    document.getElementById('detalles_onu').innerHTML = data.responseText;
                });

                
                var sHtml = `<div class="modal-dialog modal-lg" role="document" style="width:95%;">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body" style="margin-top:0xp;">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="thumbnail" id="detalles_onu" align="center">
                                                    <h4 class="text-primary">Realizando la conexión hacia el equipo, por favor espere. </h4>
                                                    <br>
                                                    <i class="fas fa-spinner fa-spin fa-5x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>`;
                
                document.getElementById('miModal').innerHTML = sHtml;
                $("#miModal").modal("show");
                
            }else{
                Swal.fire({
                    icon: 'warning',
                    title: 'El api para ver el adress list esta desactivado '
                })
            }
           
        }

        function ver_adress_list_ipv6(id_router, ip){
            let id_api = $('#tipoTecnologia').val();
            let estado_api = $('#estadoApi').val();

            if(estado_api == 'A' && id_api == 4){

                var detalles_onu = ``;

                var datos_get = {};
                var datos_post = {
                    "id_router" : id_router,
                    "direccion_ip" : ip
                };

                var datos = {
                    "endpoint": "VER_ADRESS_LIST_IPV6",
                    "datos_get": datos_get,
                    "datos_post": datos_post,
                    "tipo_sistema": 2
                };

                enviaDatosBack(datos, function (response) {
                    var data = response;
                    detalles_onu = `<div class="table-responsive">
                                        <h4 class="text-primary" align="center">Adress list: `+ip+`</h4>
                                            <table border="1" class="table table-bordered table-striped table-condensed" style="width: 100%; margin-bottom: 0px;">
                                                    <tr>
                                                        <th align="center" td class="success fecha_letra fecha_letra" colspan="4" >
                                                            <pre style="height: 340px; overflow-y: scroll; background-color:black; color:white;">`+data+`</pre>
                                                        </th>
                                                    </tr>
                                            </table>
                                        </div>`;

                    document.getElementById('detalles_onu').innerHTML = detalles_onu;
                }, function (error) {
                    var data = error;
                    document.getElementById('detalles_onu').innerHTML = data.responseText;
                });

                
                var sHtml = `<div class="modal-dialog modal-lg" role="document" style="width:95%;">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body" style="margin-top:0xp;">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="thumbnail" id="detalles_onu" align="center">
                                                    <h4 class="text-primary">Realizando la conexión hacia el equipo, por favor espere. </h4>
                                                    <br>
                                                    <i class="fas fa-spinner fa-spin fa-5x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>`;
                
                document.getElementById('miModal').innerHTML = sHtml;
                $("#miModal").modal("show");
                
            }else{
                Swal.fire({
                    icon: 'warning',
                    title: 'El api para ver el adress list esta desactivado '
                })
            }
           
        }

        function ver_informacion_lease(id_router, ipv4){

            var detalles_onu = ``;

            var datosEnvio = {
                "id_router":parseInt(id_router),
                "direccion_ip":ipv4
            }

            var datos_get = {};
            var datos_post = datosEnvio;

            var datos = {
                "endpoint": "DETALLES_LEASE",
                "datos_get": datos_get,
                "datos_post": datos_post,
                "tipo_sistema": 2
            };

            enviaDatosBack(datos, function (response) {
                
                if('status' in response){

                    if(response.status != 200){
                        var message = response.message;
                        Swal.fire({
                            icon: 'error',
                            title: 'Opps...',
                            text: message
                        });
                        return;
                    }

                    var data = response.data;
                // Extraer las claves del primer elemento para obtener los encabezados
                var headers = Object.keys(data[0]);

                    // Generar HTML para las filas de los encabezados
                    let tableHTML = `<table class="table table-striped table-bordered table-hover table-condensed" style="width:100%" id="tbl_lease">
                                        <thead>
                                            <tr>
                                                <td align="right">
                                                    <h4 class="text-primary">INFORMACIÓN LEASE</h4>
                                                </td>
                                                <td>
                                                    <h4 class="text-primary"><b>`+ipv4+`</b></h4>
                                                </td>
                                            </tr>
                                        <thead>
                                        <tbody>`;

                    // Para cada encabezado, crear una fila con la clave (como encabezado) y sus valores
                    data.forEach(item => {
                        headers.forEach(header => {
                            tableHTML += "<tr>";
                            tableHTML += `<td class="bg-primary" align="right"><h6><b>${header}</b></h6></td>`;
                            tableHTML += `<td><h6><b>${item[header] !== undefined ? item[header] : ""}</b></h6></td>`;
                            tableHTML += "</tr>";
                        });
                    });

                    tableHTML += "</tbody></table>";

                    $("#detalles_onu").html(tableHTML);

                    initTable("tbl_lease");

                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Opps...',
                        text: 'Error al consultar'
                    });
                    return;
                }
                
            }, function (error) {
                var data = error;
                document.getElementById('detalles_onu').innerHTML = data.responseText;
            });

            var sHtml = `<div class="modal-dialog modal-lg" role="document" style="width:95%;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="margin-top:0xp;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="thumbnail table-responsive" id="detalles_onu" align="center">
                                                <h4 class="text-primary">La búsqueda solicitada puede tomar un tiempo en retornar una respuesta, por favor espere. </h4>
                                                <br>
                                                <i class="fas fa-spinner fa-spin fa-5x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>`;

            document.getElementById('miModal').innerHTML = sHtml;
            $("#miModal").modal("show");

        }

        function ver_informacion_binding(id_router, ipv6){

            var detalles_onu = ``;

            var datosEnvio = {
                "id_router":parseInt(id_router),
                "direccion_ip":ipv6
            }

            var datos_get = {};
            var datos_post = datosEnvio;

            var datos = {
                "endpoint": "DETALLES_BINDING",
                "datos_get": datos_get,
                "datos_post": datos_post,
                "tipo_sistema": 2
            };

            enviaDatosBack(datos, function (response) {
                
                if('status' in response){

                    if(response.status != 200){
                        var message = response.message;
                        Swal.fire({
                            icon: 'error',
                            title: 'Opps...',
                            text: message
                        });
                        return;
                    }

                    var data = response.data;
                // Extraer las claves del primer elemento para obtener los encabezados
                var headers = Object.keys(data[0]);

                    // Generar HTML para las filas de los encabezados
                    let tableHTML = `<table class="table table-striped table-bordered table-hover table-condensed" style="width:100%" id="tbl_binding">
                                        <thead>
                                            <tr>
                                                <td align="right">
                                                    <h4 class="text-primary">INFORMACIÓN BINDING</h4>
                                                </td>
                                                <td>
                                                    <h4 class="text-primary"><b>`+ipv6+`</b></h4>
                                                </td>
                                            </tr>
                                        <thead>
                                        <tbody>`;

                    // Para cada encabezado, crear una fila con la clave (como encabezado) y sus valores
                    data.forEach(item => {
                        headers.forEach(header => {
                            tableHTML += "<tr>";
                            tableHTML += `<td class="bg-primary" align="right"><h6><b>${header}</b></h6></td>`;
                            tableHTML += `<td><h6><b>${item[header] !== undefined ? item[header] : ""}</b></h6></td>`;
                            tableHTML += "</tr>";
                        });
                    });

                    tableHTML += "</tbody></table>";

                    $("#detalles_onu").html(tableHTML);

                    initTable('tbl_binding');

                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Opps...',
                        text: 'Error al consultar'
                    });
                    return;
                }
                
            }, function (error) {
                var data = error;
                document.getElementById('detalles_onu').innerHTML = data.responseText;
            });

            var sHtml = `<div class="modal-dialog modal-lg" role="document" style="width:95%;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="margin-top:0xp;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="thumbnail table-responsive" id="detalles_onu" align="center">
                                                <h4 class="text-primary">La búsqueda solicitada puede tomar un tiempo en retornar una respuesta, por favor espere. </h4>
                                                <br>
                                                <i class="fas fa-spinner fa-spin fa-5x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>`;

            document.getElementById('miModal').innerHTML = sHtml;
            $("#miModal").modal("show");
                

        }

        function trafico_onu(id_olt_swr, sn) {
            var datos_get = { id_olt_swr, sn };
            var datos_post = {};
            var datos = {
                "endpoint": 'TRAFICO_ONU_SNMP',
                "datos_get": datos_get,
                "datos_post": datos_post,
                "tipo_sistema": 1
            };

            // Variables para guardar los datos de la iteración anterior
            let datosAnteriores = null;
            let ultimaTasaEntrada = null; // Guardar última tasa entrada
            let ultimaTasaSalida = null; // Guardar última tasa salida
            let fetchTimeout; // Variable para almacenar el temporizador

            var sHtml = `<div class="modal-dialog modal-lg" style="width:95%;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="thumbnail">
                                                <h4 class="text-primary" align="center"><i class="fas fa-spinner fa-spin"></i> Tráfico de la onu con sn: ${sn}.</h4>
                                                <br>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="alert alert-success alert-dismissible">
                                                            <h5>TRAFICO BAJADA</h5>
                                                            <div id="val_entrada"> <i class="fas fa-spinner fa-spin fa-5x"></i> </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="alert alert-danger alert-dismissible">
                                                            <h5>TRAFICO SUBIDA</h5>
                                                            <div id="val_salida"> <i class="fas fa-spinner fa-spin fa-5x"></i> </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div id="container1" style="width:100%; height:300px;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>`;

            $('#miModal').html(sHtml);
            $('#miModal').modal('show');

            const chart1 = Highcharts.chart('container1', {
                chart: {
                    type: 'line',
                    events: {
                        load: function () {
                            let series = this.series;

                            // Función recursiva que enviará los datos al backend y esperará 5 segundos antes de llamar nuevamente
                            function fetchData() {
                                if ($('#miModal').is(":visible")) { // Verificar si el modal está visible
                                    enviaDatosBack(datos, function (response) {
                                        // Si es la primera iteración, simplemente almacenar los datos y no calcular
                                        if (!datosAnteriores) {
                                            datosAnteriores = response; // Guardar los datos de inicio
                                            chart1.series[0].addPoint([0], true);
                                            chart1.series[1].addPoint([0], true);
                                            $('#val_entrada').html('0mbps');
                                            $('#val_salida').html('0mbps');
                                        } else {
                                            // Calcular deltas
                                            let delta_octetos_bajada = response.output_rate - datosAnteriores.output_rate;
                                            let delta_octetos_subida = response.input_rate - datosAnteriores.input_rate;
                                            let delta_uptime = response.up_time - datosAnteriores.up_time;
                                            let velocidad_interfaz = response.if_speed_r;

                                            // Si el delta de uptime y la velocidad de la interfaz son válidos, calcular el tráfico
                                            if (delta_uptime > 0 && velocidad_interfaz > 0) {
                                                let tasa_bits_bajada = (delta_octetos_bajada * 8 * 100) / delta_uptime * velocidad_interfaz;
                                                let tasa_bits_subida = (delta_octetos_subida * 8 * 100) / delta_uptime * velocidad_interfaz;

                                                let tasa_mb_bajada = tasa_bits_bajada / 100000000000000;
                                                let tasa_mb_subida = tasa_bits_subida / 100000000000000;

                                                // Verificar si las tasas son 0 y asignar el valor anterior si es necesario
                                                if (tasa_mb_bajada === 0) {
                                                    tasa_mb_bajada = ultimaTasaEntrada !== null ? ultimaTasaEntrada : 0; // Mantener valor anterior
                                                } else {
                                                    ultimaTasaEntrada = tasa_mb_bajada; // Actualizar valor anterior
                                                }

                                                if (tasa_mb_subida === 0) {
                                                    tasa_mb_subida = ultimaTasaSalida !== null ? ultimaTasaSalida : 0; // Mantener valor anterior
                                                } else {
                                                    ultimaTasaSalida = tasa_mb_subida; // Actualizar valor anterior
                                                }

                                                // Formatear las tasas para mostrar adecuadamente
                                                const [entradaTexto, entradaUnidad] = formatearTasa(tasa_mb_bajada);
                                                const [salidaTexto, salidaUnidad] = formatearTasa(tasa_mb_subida);

                                                // Actualizar el gráfico con los nuevos datos
                                                chart1.series[0].addPoint([tasa_mb_bajada], true);
                                                chart1.series[1].addPoint([tasa_mb_subida], true);

                                                // Actualizar los valores en el HTML
                                                $('#val_entrada').html(entradaTexto + ' ' + entradaUnidad);
                                                $('#val_salida').html(salidaTexto + ' ' + salidaUnidad);
                                            }

                                            // Actualizar datosAnteriores para la siguiente iteración
                                            datosAnteriores = response;
                                        }

                                        // Continuar si el modal sigue visible
                                        fetchTimeout = setTimeout(fetchData, 5000);

                                    }, function (error) {
                                        console.log("Error: " + error.responseText);

                                        // Si hay error, intentar nuevamente después de 5 segundos si el modal está visible
                                        fetchTimeout = setTimeout(fetchData, 5000);
                                    });
                                }
                            }

                            // Iniciar la primera llamada
                            fetchData();
                        }
                    }
                },
                title: {
                    text: sn
                },
                xAxis: {
                    categories: [0]
                },
                yAxis: {
                    title: {
                        text: 'Tráfico'
                    }
                },
                series: [
                    {
                        name: 'Trafico Bajada',
                        data: [],
                        color: 'green'
                    },
                    {
                        name: 'Trafico Subida',
                        data: [],
                        color: 'red'
                    }
                ]
            });

            // Función para formatear la tasa en mbps, kbps o pps
            function formatearTasa(tasa) {
                let unidad = 'mbps';
                let tasaFormateada = tasa;

                if (tasa < 1) {
                    tasaFormateada = tasa * 1_000; // Convertir a Kbps
                    unidad = 'kbps';
                } else if (tasa < 0.001) {
                    tasaFormateada = tasa * 1_000_000; // Convertir a pps (Paquetes por segundo)
                    unidad = 'pps';
                }

                return [tasaFormateada.toFixed(2), unidad]; // Devolver el valor formateado y la unidad
            }

            // Limpiar el temporizador cuando se cierre el modal
            $('#miModal').on('hidden.bs.modal', function () {
                clearTimeout(fetchTimeout);
            });
        }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/series-label.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<?  ?>
<? /* * ***************************************************************** */ ?>