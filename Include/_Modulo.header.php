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
        //include_once(DIR_INCLUDE . 'Clases/Mail/mail.php');
        include_once('_Ajax.comun.php');
    }
} else {
    $ejecuta = false;
}
?>
<html>
    <head>
	
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		
		<!--CSS--> 

		<link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/css/bootstrap-3.3.7-dist/css/bootstrap.css" media="screen">
		<link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen">
		<link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/js/treeview/css/bootstrap-treeview.css" media="screen">
		<link rel="stylesheet" href="<?=$jirehUri?>Include/css/dataTables/dataTables.bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/js/ventanas/dhtmlwindow.css" rel="stylesheet" media="screen"/>
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/Clases/Formulario/Css/Formulario.css" media="screen"/>
        <link rel="stylesheet" type="text/css" href='<?=$jirehUri?>Include/Clases/Formulario/Calendario/calendario.css' media="screen">
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/css/general.css" media="screen">
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/js/arbol/simpletree.css" media="screen"/>
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/js/lytebox/css/lytebox.css" media="screen"/>
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/js/lightbox/css/lightbox.css" media="screen"/>
        
        <!-- Select2 -->
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/Componentes/bower_components/select2/dist/css/select2.min.css">
        <!--Sweetalert2--> 
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/js/sweetalert2/sweetalert2.min.css">
		<!-- Valid -->
        <link rel="stylesheet" type="text/css" href="<?=$jirehUri?>Include/js/jqueryValidate/jquery.validate.css">
		
        <!--JavaScript--> 
		<script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/js/jquery/jquery-3.3.1.min.js.js"></script>
		<script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/css/bootstrap-3.3.7-dist/js/bootstrap.js"></script>
		<script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
        <script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/js/comun.js"></script>
		<script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/js/process.js"></script>
        <script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/Clases/Formulario/Js/Formulario.js"></script>
        <script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/js/ventanas/dhtmlwindow.js"></script>
        <script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/js/arbol/simpletreemenu.js"></script>
        <script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/Clases/fc/js/FusionCharts.js"></script>
		<script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/Clases/HTML_TreeMenuXL-2.0.2/TreeMenu.js"></script>
		<!-- Select2 -->
        <script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/Componentes/bower_components/select2/dist/js/select2.full.min.js"></script>
		<!--Sweetalert2--> 
        <script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/js/sweetalert2/sweetalert2.min.js"></script>
        <!--Valid--> 
        <script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/js/jqueryValidate/jquery.validates.min.js"></script>
        <script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/js/jqueryValidate/localization/messages_es.min.js"></script>
		
        <title></title>
		
		<?php $xajax->printJavascript(DIR_INCLUDE . 'Clases/xajax'); ?>
		
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

            function validarDocumentoRespaldo18042022(){
                numero = document.getElementById('ruc_cli').value;
                var suma = 0;
                var residuo = 0;
                var pri = false;
                var pub = false;
                var nat = false;
                var numeroProvincias = 25;
                var modulo = 11;
                identificacion = $("#identificacion").val();
                if(identificacion == ''){
                    alert('Debe elegir un tipo de identifación');
                    $("#ruc_cli").val('');
                }
                /* Verifico que el campo no contenga letras */
                var ok=1;
                for (i=0; i<numero.length && ok==1 ; i++){
                    var n = parseInt(numero.charAt(i));
                    if (isNaN(n)) ok=0;
                }
                if (ok==0){
                    alert("No puede ingresar caracteres en el número");
                    $("#ruc_cli").val('');
                    return false;
                }
                if(identificacion == '01' || identificacion == '02'){
                    if (numero.length != 13 && identificacion == '01'){
                        alert('El RUC debe contener 13 caracteres');
                        $("#ruc_cli").val('');
                        return false;
                    }
                    if (numero.length != 10 && identificacion == '02'){
                        alert('La cédula debe contener 10 caracteres');
                        $("#ruc_cli").val('');
                        return false;
                    }
                    /* Los primeros dos digitos corresponden al codigo de la provincia */
                    provincia = numero.substr(0,2);
                    if (provincia < 1 || provincia > numeroProvincias){
                        alert('El código de la provincia (dos primeros dígitos) es inválido');
                        $("#ruc_cli").val('');
                        return false;
                    }
                    /* Aqui almacenamos los digitos de la cedula en variables. */
                    d1  = numero.substr(0,1);
                    d2  = numero.substr(1,1);
                    d3  = numero.substr(2,1);
                    d4  = numero.substr(3,1);
                    d5  = numero.substr(4,1);
                    d6  = numero.substr(5,1);
                    d7  = numero.substr(6,1);
                    d8  = numero.substr(7,1);
                    d9  = numero.substr(8,1);
                    d10 = numero.substr(9,1);
                    /* El tercer digito es: */
                    /* 9 para sociedades privadas y extranjeros   */
                    /* 6 para sociedades publicas */
                    /* menor que 6 (0,1,2,3,4,5) para personas naturales */
                    if (d3==7 || d3==8){
                        alert('El tercer dígito ingresado es inválido');
                        $("#ruc_cli").val('');
                        return false;
                    }
                    /* Solo para personas naturales (modulo 10) */
                    if (d3 < 6){
                        nat = true;
                        p1 = d1 * 2;  if (p1 >= 10) p1 -= 9;
                        p2 = d2 * 1;  if (p2 >= 10) p2 -= 9;
                        p3 = d3 * 2;  if (p3 >= 10) p3 -= 9;
                        p4 = d4 * 1;  if (p4 >= 10) p4 -= 9;
                        p5 = d5 * 2;  if (p5 >= 10) p5 -= 9;
                        p6 = d6 * 1;  if (p6 >= 10) p6 -= 9;
                        p7 = d7 * 2;  if (p7 >= 10) p7 -= 9;
                        p8 = d8 * 1;  if (p8 >= 10) p8 -= 9;
                        p9 = d9 * 2;  if (p9 >= 10) p9 -= 9;
                        modulo = 10;
                    }
                    /* Solo para sociedades publicas (modulo 11) */
                    /* Aqui el digito verficador esta en la posicion 9, en las otras 2 en la pos. 10 */
                    else if(d3 == 6){
                        pub = true;
                        p1 = d1 * 3;
                        p2 = d2 * 2;
                        p3 = d3 * 7;
                        p4 = d4 * 6;
                        p5 = d5 * 5;
                        p6 = d6 * 4;
                        p7 = d7 * 3;
                        p8 = d8 * 2;
                        p9 = 0;
                    }
                    /* Solo para entidades privadas (modulo 11) */
                    else if(d3 == 9) {
                        pri = true;
                        p1 = d1 * 4;
                        p2 = d2 * 3;
                        p3 = d3 * 2;
                        p4 = d4 * 7;
                        p5 = d5 * 6;
                        p6 = d6 * 5;
                        p7 = d7 * 4;
                        p8 = d8 * 3;
                        p9 = d9 * 2;
                    }
                    suma = p1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9;
                    residuo = suma % modulo;
                    /* Si residuo=0, dig.ver.=0, caso contrario 10 - residuo*/
                    digitoVerificador = residuo==0 ? 0: modulo - residuo;
                    /* ahora comparamos el elemento de la posicion 10 con el dig. ver.*/
                    if (pub==true){
                        if (digitoVerificador != d9){
                            alert('El ruc de la empresa del sector público es incorrecto.');
                            $("#ruc_cli").val('');
                            return false;
                        }
                        /* El ruc de las empresas del sector publico terminan con 0001*/
                        if ( numero.substr(9,4) != '0001' ){
                            alert('El ruc de la empresa del sector público debe terminar con 0001');
                            $("#ruc_cli").val('');
                            return false;
                        }
                    }
                    else if(pri == true){
                        if (digitoVerificador != d10){
                            alert('El ruc de la empresa del sector privado es incorrecto.');
                            $("#ruc_cli").val('');
                            return false;
                        }
                        if ( numero.substr(10,3) != '001' ){
                            alert('El ruc de la empresa del sector privado debe terminar con 001');
                            $("#ruc_cli").val('');
                            return false;
                        }
                    }
                    else if(nat == true){
                        if (digitoVerificador != d10){
                            alert('El número de cédula de la persona natural es incorrecto.');
                            $("#ruc_cli").val('');
                            return false;
                        }
                        if (numero.length >10 && numero.substr(10,3) != '001' ){
                            alert('El ruc de la persona natural debe terminar con 001');
                            $("#ruc_cli").val('');
                            return false;
                        }
                    }
                }
            }

            function validarDocumento() {
                var bandera_return = false;
                var variable_url = '<?= $url_javascrip_ori ?>';
                var control_pais = '<?= $pais_codigo_ext ?>';

                console.log('control_pais',control_pais);



                var numero = document.getElementById('ruc_cli').value;
                var tipo_identificacion = $("#identificacion").val();

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
                            if (!validacion_js) {
                                console.log(validarDocumentoEcuReturnWs(tipo_identificacion, numero, 'ruc_cli'));
                                // alertSwal('Numero de Identificacion Incorrecta', 'warning');
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

                        console.log('tipo_identificacion: ',tipo_identificacion);
                        console.log('control_pais: ',control_pais);
                        console.log('tipo_identificacion: ',tipo_identificacion);
                        console.log('control_pais: ',control_pais);

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
    </head>
    <body marginheight="0" marginwidth="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
        <div ID="overDiv" STYLE="position:absolute; visibility:hide; z-index: 1;"> </div>
        <script src="<?=$jirehUri?>Include/js/ove.js"></script>
        <?
        //PERMISOS DE USUARIO
        $oCon->Conectar();
        $permiso = permisos($oCon, $_SESSION['U_PERFIL']);
        $oCon->Desconectar();
        ?>
        <!-- <div id="ProcesandoDiv" class='fondoTransparente' style="display:none">
        <div class='center' align="center">
                <img src="<?= DIR_IMAGENES ?>loaders/time3.gif" border="0"> <!-- <br> -->
        <!-- <h4>Procesando</h4>
</div>
</div>  -->
        <?
        if (isset($_REQUEST['mCod']))
            $mCod = $_REQUEST['mCod'];
        else
            $mCod = '';
        if (isset($_REQUEST['snow']))
            $snow = $_REQUEST['snow'];
        else
            $snow = 'on';
        if (isset($_REQUEST['mVer']))
            $mVer = $_REQUEST['mVer'];
        else
            $mVer = 'true';
        if ($mVer == 'true') {
            ?>
            <!-- INICIO TITULO DEL DOCUMENTO -->
            <div align="left" style="width:100%; border-top: dotted 1px #999999; border-bottom: dotted 1px #999999; background-color:#F3F3F3" >
                <span class="Titulo"><?= cargar_titulo($oCon, $mCod); ?></span>
            </div>
            <!-- <br /> -->

            <!-- FIN TITULO DEL DOCUMENTO -->
<? } ?>
