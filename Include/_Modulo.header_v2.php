<?
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
header("Cache-control: private");
header('Content-Type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *');
include_once('config.inc.php');

$jirehUri = $_COOKIE["JIREH_URI"];
$S_PAIS_API_SRI = $_SESSION['S_PAIS_API_SRI'];

$url_javascrip_ori = $jirehUri;
$pais_codigo_ext = $S_PAIS_API_SRI;
?>
<iframe width=170 height=200 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="<?=$jirehUri?>Include/Clases/Formulario/Calendario/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:hidden; z-index:999; position:absolute; top:-500px; left:-500px; border: 1px ridge;"></iframe>
<?
if (isset($_REQUEST['sesionId']))
    $sesionId = $_REQUEST['sesionId'];
else
    $sesionId = '';

if ($sesionId == session_id()) {
    include_once(DIR_SISTEMA . 'login.php');
    $nav = $_SESSION['U_NAVEGADOR'];
    $ver = $_SESSION['U_NVERSION'];
    $logged_in = checkLogin();
    if (!$logged_in) {
        echo '<script>window.top.location.href="../../index.php?nav=' . $nav . '&ver=' . $ver . '"</script>';
    } else {
        $ejecuta = true;
        
    }
} else {
    $ejecuta = false;
}
?>
<html>
    <head>
	
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		
		<!--CSS--> 

        <!--LIBRERIA NODE INICIO-->

        <!--JQuery--> 
		<script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>js/jquery/dist/jquery.min.js"></script>
        
        <!--Sweetalert2--> 
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>js/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
        <script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>js/sweetalert2/dist/sweetalert2.min.js"></script>
        
        <!--Bootstrap--> 
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>js/bootstrap/dist/css/bootstrap.min.css" media="screen">
        <script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>js/bootstrap/dist/js/bootstrap.min.js"></script>

        <!--LIBRERIA NODE INICIO-->
        
        
		<link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/js/treeview/css/bootstrap-treeview.css" media="screen">
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/js/arbol/simpletree.css" media="screen"/>
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/js/lytebox/css/lytebox.css" media="screen"/>
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/js/lightbox/css/lightbox.css" media="screen"/>

        <!-- LIBRERIA PARA ICONOS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/tablasPaginadas/paginacion.js"></script>
        
		<!-- Valid 
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/js/jqueryValidate/jquery.validate.css">
        
        <script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/js/jqueryValidate/jquery.validates.min.js"></script>
        <script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/js/jqueryValidate/localization/messages_es.min.js"></script>      
        --><!--Archivos Maestros--> 

       
        
        
		
        <title></title>
        
        <script type="text/javascript">
            document.onclick = function(e) {
                var t = !e ? self.event.srcElement.name : e.target.name;
                if (t != "popcal")
                gfPop.fHideCal();
        }
            function show_load() {
                xajax.$('ProcesandoDiv').style.display = 'block';
            }
            function hide_load() {
                xajax.$('ProcesandoDiv').style.display = 'none';
            }

            

            function validarDocumento() {
                var bandera_return = false;
                var variable_url = '<?= $url_javascrip_ori ?>';
                var control_pais = '<?= $pais_codigo_ext ?>';

                var numero = document.getElementById('ruc_cli').value;
                console.log($("#identificacion").val());
                console.log($("#identificacion_cli").val());
                var tipo_identificacion = $("#identificacion").val();
                console.log(tipo_identificacion);
                // var tipo_identificacion =$("input[name='identificacion']:checked").val();
                if (tipo_identificacion != '' && numero != '') {
                    if (tipo_identificacion == '' || tipo_identificacion == undefined || tipo_identificacion == null) {
                        alertSwal('Debe elegir un tipo de identificaci&oacute;n', 'warning');
                        $("#ruc_cli").val('');
                    } else if (tipo_identificacion == '01' && control_pais == '593' || tipo_identificacion == '02' && control_pais == '593') {
                        if (numero.length != 13 && tipo_identificacion == '01') {
                            alertSwal('El RUC debe contener 13 caracteres', 'warning');
                            $("#ruc_cli").val('');
                        } else if (numero.length != 10 && tipo_identificacion == '02') {
                            alertSwal('La c&eacute;dula debe contener 10 caracteres', 'warning');
                            $("#ruc_cli").val('');
                        } else {
                            var validacion_js = validarDocumentoEcuReturnBolean(tipo_identificacion, numero);
                            console.log(validacion_js);
                            if (!validacion_js) {
                                console.log(validarDocumentoEcuReturnWs(tipo_identificacion, numero, 'ruc_cli'));
                                alertSwal('Numero de Identificacion Incorrecta', 'warning');
                                // $("#ruc").val('');
                            }
                        }
                    }

                } else {
                    alertSwal('Debe elegir un tipo de identificaci&oacute;n', 'warning');
                    $("#ruc_cli").val('');
                }
            }

            function validarDocumentoEcu() {
                var bandera_return = false;
                var variable_url = '<?= $url_javascrip_ori ?>';
                var control_pais = '<?= $pais_codigo_ext ?>';

                var numero = document.getElementById('ruc').value;
                var tipo_identificacion = $("#identificacion").val();
                // var tipo_identificacion =$("input[name='identificacion']:checked").val();
                if (tipo_identificacion != '' && numero != '') {
                    if (tipo_identificacion == '' || tipo_identificacion == undefined || tipo_identificacion == null) {
                        alertSwal('Debe elegir un tipo de identificaci&oacute;n', 'warning');
                        $("#ruc").val('');
                    } else if (tipo_identificacion == '01' && control_pais == '593' || tipo_identificacion == '02' && control_pais == '593') {
                        if (numero.length != 13 && tipo_identificacion == '01') {
                            alertSwal('El RUC debe contener 13 caracteres', 'warning');
                            $("#ruc").val('');
                        } else if (numero.length != 10 && tipo_identificacion == '02') {
                            alertSwal('La c&eacute;dula debe contener 10 caracteres', 'warning');
                            $("#ruc").val('');
                        } else {
                            var validacion_js = validarDocumentoEcuReturnBolean(tipo_identificacion, numero);
                            if (!validacion_js) {
                                validarDocumentoEcuReturnWs(tipo_identificacion, numero, 'ruc');
                                // alertSwal('Numero de Identificacion Incorrecta', 'warning');
                                // $("#ruc").val('');
                            }
                        }
                    }

                } else {
                    alertSwal('Debe elegir un tipo de identificaci&oacute;n', 'warning');
                    $("#ruc").val('');
                }
            }

            function validarDocumentoEcuReturnBolean(identificacion, cedula) {

                if (identificacion == '01' || identificacion == '02') {

                    //Preguntamos si la cedula consta de 10 digitos
                    if (cedula.length === 10) {

                        //Obtenemos el digito de la region que sonlos dos primeros digitos
                        var digito_region = cedula.substring(0, 2);

                        //Pregunto si la region existe ecuador se divide en 24 regiones
                        if (digito_region >= 1 && digito_region <= 24) {

                            // Extraigo el ultimo digito
                            var ultimo_digito = cedula.substring(9, 10);

                            //Agrupo todos los pares y los sumo
                            var pares = parseInt(cedula.substring(1, 2)) + parseInt(cedula.substring(3, 4)) + parseInt(cedula.substring(5, 6)) + parseInt(cedula.substring(7, 8));

                            //Agrupo los impares, los multiplico por un factor de 2, si la resultante es > que 9 le restamos el 9 a la resultante
                            var numero1 = cedula.substring(0, 1);
                            var numero1 = (numero1 * 2);
                            if (numero1 > 9) {
                                var numero1 = (numero1 - 9);
                            }

                            var numero3 = cedula.substring(2, 3);
                            var numero3 = (numero3 * 2);
                            if (numero3 > 9) {
                                var numero3 = (numero3 - 9);
                            }

                            var numero5 = cedula.substring(4, 5);
                            var numero5 = (numero5 * 2);
                            if (numero5 > 9) {
                                var numero5 = (numero5 - 9);
                            }

                            var numero7 = cedula.substring(6, 7);
                            var numero7 = (numero7 * 2);
                            if (numero7 > 9) {
                                var numero7 = (numero7 - 9);
                            }

                            var numero9 = cedula.substring(8, 9);
                            var numero9 = (numero9 * 2);
                            if (numero9 > 9) {
                                var numero9 = (numero9 - 9);
                            }

                            var impares = numero1 + numero3 + numero5 + numero7 + numero9;

                            //Suma total
                            var suma_total = (pares + impares);

                            //extraemos el primero digito
                            var primer_digito_suma = String(suma_total).substring(0, 1);

                            //Obtenemos la decena inmediata
                            var decena = (parseInt(primer_digito_suma) + 1) * 10;

                            //Obtenemos la resta de la decena inmediata - la suma_total esto nos da el digito validador
                            var digito_validador = decena - suma_total;

                            //Si el digito validador es = a 10 toma el valor de 0
                            if (digito_validador == 10)
                                var digito_validador = 0;

                            //Validamos que el digito validador sea igual al de la cedula
                            if (digito_validador == ultimo_digito) {
                                //setErrorIdentificacion('la cedula:' + cedula + ' es correcta');
                                //setErrorIdentificacion("");
                                return true;
                            } else {
                                //setErrorIdentificacion('la cedula:' + cedula + ' es incorrecta');
                                return false;
                            }

                        } else {
                            // imprimimos en consola si la region no pertenece
                            //setErrorIdentificacion('Esta cedula no pertenece a ninguna region');
                            return false;
                        }
                    } else if (cedula.length === 13) {
                        //Obtenemos el digito de la region que sonlos dos primeros digitos
                        var digito_region = cedula.substring(0, 2);

                        //Pregunto si la region existe ecuador se divide en 24 regiones
                        if (digito_region >= 1 && digito_region <= 24) {

                            // Extraigo el ultimo digito
                            var ultimo_digito = cedula.substring(9, 10);

                            //Agrupo todos los pares y los sumo
                            var pares = parseInt(cedula.substring(1, 2)) + parseInt(cedula.substring(3, 4)) + parseInt(cedula.substring(5, 6)) + parseInt(cedula.substring(7, 8));

                            //Agrupo los impares, los multiplico por un factor de 2, si la resultante es > que 9 le restamos el 9 a la resultante
                            var numero1 = cedula.substring(0, 1);
                            var numero1 = (numero1 * 2);
                            if (numero1 > 9) {
                                var numero1 = (numero1 - 9);
                            }

                            var numero3 = cedula.substring(2, 3);
                            var numero3 = (numero3 * 2);
                            if (numero3 > 9) {
                                var numero3 = (numero3 - 9);
                            }

                            var numero5 = cedula.substring(4, 5);
                            var numero5 = (numero5 * 2);
                            if (numero5 > 9) {
                                var numero5 = (numero5 - 9);
                            }

                            var numero7 = cedula.substring(6, 7);
                            var numero7 = (numero7 * 2);
                            if (numero7 > 9) {
                                var numero7 = (numero7 - 9);
                            }

                            var numero9 = cedula.substring(8, 9);
                            var numero9 = (numero9 * 2);
                            if (numero9 > 9) {
                                var numero9 = (numero9 - 9);
                            }

                            var impares = numero1 + numero3 + numero5 + numero7 + numero9;

                            //Suma total
                            var suma_total = (pares + impares);

                            //extraemos el primero digito
                            var primer_digito_suma = String(suma_total).substring(0, 1);

                            //Obtenemos la decena inmediata
                            var decena = (parseInt(primer_digito_suma) + 1) * 10;

                            //Obtenemos la resta de la decena inmediata - la suma_total esto nos da el digito validador
                            var digito_validador = decena - suma_total;

                            //Si el digito validador es = a 10 toma el valor de 0
                            if (digito_validador == 10)
                                var digito_validador = 0;

                            //Validamos que el digito validador sea igual al de la cedula
                            if (digito_validador == ultimo_digito) {
                                //setErrorIdentificacion('la cedula:' + cedula + ' es correcta');
                                if (cedula.substr(10, 3) === '001') {
                                    return true;
                                } else {
                                    //setErrorIdentificacion("El RUC "+ cedula + " es incorrecto");
                                    return false;
                                }
                            } else {
                                //setErrorIdentificacion('El RUC:' + cedula + ' es incorrecto');
                                return false;
                            }

                        } else {
                            // imprimimos en consola si la region no pertenece
                            //setErrorIdentificacion('Esta cedula no pertenece a ninguna region');
                            return false;
                        }
                    } else {
                        //imprimimos en consola si la cedula tiene mas o menos de 10 digitos
                        //console.log('Esta cedula tiene menos de 10 Digitos');
                        //setErrorIdentificacion('Este RUC tiene menos de 13 digitos');
                        return false;
                    }
                } else {
                    return true;
                }
            }

            function validarDocumentoEcuReturnWs(identificacion, numero, variable = 'ruc') {
                var informacion_return = false;
                var bandera_return = false;
                var campo_ruc = "#" + variable;
                var variable_url = '<?= $url_javascrip_ori ?>';
                $.ajax({
                    url: variable_url + '/Include/validadores/valida_idenficacion_post.php?tipo_identificacion=' + identificacion + '&&numero=' + numero,
                    type: 'POST',
                    dataType: "json",
                    async: true,
                    contentType: "application/json; charset=utf-8",
                    success: function (data) {
                        var bandera = data[0]; //1 => exito, 2 => mensaje validacion, 0 => error
                        var mensaje = data[1]; // mensaje del servicio sri
                        var informacion = data[2]; //informacion que retorna
                        if (bandera == 1) {
                            bandera_return = true;
                            informacion_return = informacion;
                            // console.log(informacion);
                        } else if (bandera == 2) {
                            alertSwal(mensaje, 'warning');
                            $(campo_ruc).val('');
                        } else {
                            alertSwal('Error al tratar de validar identificacion', 'warning');
                            $(campo_ruc).val('');
                        }

                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alertSwal(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });

                return [bandera_return,informacion_return];
            }

            function alertSwal(title = 'Error', type = 'error') {
                Swal.fire({
                    type: type,
                    title: title,
                    showConfirmButton: false,
                    timer: 2000,
                    width: 600,
                });
            }

            //xajax.callback.global.onResponseDelay = show_load;
            //xajax.callback.global.onComplete = hide_load;
        </script>

