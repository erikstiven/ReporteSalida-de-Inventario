var msg = '';
var asciiBack = 8;
var asciiTab = 9;
var asciiSHIFT = 16;
var asciiCTRL = 17;
var asciiALT = 18;
var asciiHome = 36;
var asciiLeftArrow = 37;
var asciiRightArrow = 39;
var asciiMS = 92;
var asciiView = 93;
var asciiF1 = 112;
var asciiF2 = 113;
var asciiF3 = 114;
var asciiF4 = 115;
var asciiF5 = 116;
var asciiF6 = 117;
var asciiF11 = 122;
var asciiF12 = 123;
var asciiF11 = 122;
var asciiAmp = 38;
var asciiAmp1 = 54;

//===================================================================================//


if (document.all) { //ie has to block in the key down
    document.onkeydown = onKeyPress;
} else if (document.layers || document.getElementById) { //NS and mozilla have to block in the key press
    document.onkeypress = onKeyPress;
}


function onKeyPress(evt) {

    var evt = evt ? evt : event;
    window.status = '';
    //get the event object
    var oEvent = (window.event) ? window.event : evt;

    //hmmm in mozilla this is jacked, so i have to record these seperate
    //what key was pressed
    var nKeyCode = oEvent.keyCode ? oEvent.keyCode : oEvent.which ? oEvent.which : void 0;

    var bIsFunctionKey = false;

    //hmmm in mozilla the keycode would contain a function key ONLY IF the charcode IS 0    
    //else key code and charcode read funny, the charcode for 't' 
    //returns 116, which is the same as the ascii for F5
    //SOOO,... to check if a the keycode is truly a function key, 
    //ONLY check when the charcode is null OR 0, IE returns null, mozilla returns 0 
    //alert(nKeyCode);
    if (oEvent.charCode == null || oEvent.charCode == 0) {
        bIsFunctionKey = (nKeyCode >= asciiF2 && nKeyCode <= asciiF12) || (
            nKeyCode == asciiALT
            || nKeyCode == asciiMS
            || nKeyCode == asciiView
            || nKeyCode == asciiHome
            || nKeyCode == asciiBack
        )
    }

    //convert the key to a character, makes for more readable code  
    var sChar = String.fromCharCode(nKeyCode).toUpperCase();

    //get the active tag that has the focus on the page, and its tag type
    var oTarget = (oEvent.target) ? oEvent.target : oEvent.srcElement;
    var sTag = oTarget.tagName.toLowerCase();
    var sTagType = oTarget.getAttribute("type");

    var bAltPressed = (oEvent.altKey) ? oEvent.altKey : oEvent.modifiers & 1 > 0;
    var bShiftPressed = (oEvent.shiftKey) ? oEvent.shiftKey : oEvent.modifiers & 4 > 0;
    var bCtrlPressed = (oEvent.ctrlKey) ? oEvent.ctrlKey : oEvent.modifiers & 2 > 0;
    //var bMetaPressed = (oEvent.metaKey) ? oEvent.metaKey : oEvent.modifiers & 8 > 0;

    var bRet = true; //assume true as that will be the case most times
    //alert (nKeyCode + ' ' + sChar + ' ' + sTag + ' ' + sTagType + ' ' + bShiftPressed + ' ' + bCtrlPressed + ' ' + bAltPressed);

    if (sTagType != null) {
        sTagType = sTagType.toLowerCase();
    }

    //allow these keys inside a text box
    // alert(sChar);
    if (sTag == "textarea" || (sTag == "input" && (sTagType == "text" || sTagType == "password")) && (nKeyCode == asciiBack || nKeyCode == asciiSHIFT || nKeyCode == asciiHome || bShiftPressed || (bCtrlPressed && (nKeyCode == asciiLeftArrow || nKeyCode == asciiRightArrow)))) {
        return true;
    } else if (bAltPressed && (nKeyCode == asciiLeftArrow || nKeyCode == asciiRightArrow)) { // block alt + left or right arrow
        bRet = false;
    } else if (bCtrlPressed && (sChar == 'A' || sChar == 'Q' || sChar == '2' || nKeyCode == 219 || nKeyCode == 220 || sChar == 'C' || sChar == 'V' || sChar == 'X' || sChar == 'F' || sChar == 'B' || sChar == 'E')) { // ALLOW cut, copy and paste, and SELECT ALL
        bRet = true;
    } else if (bShiftPressed && nKeyCode == asciiTab) {//allow shift + tab
        bRet = true;
    } else if (bShiftPressed && (nKeyCode == asciiAmp || nKeyCode == asciiAmp1 || sChar == '6' || sChar == '&')) {//block allow shift + &
        bRet = false;
    } else if (bIsFunctionKey) { // Capture and stop these keys
        bRet = false;
    } else if (bCtrlPressed || bShiftPressed || bAltPressed) { //block ALL other sequences, includes CTRL+O, CTRL+P, CTRL+N, etc....
        bRet = false;
    }

    if (!bRet) {
        try {
            oEvent.returnValue = false;
            oEvent.cancelBubble = true;

            if (document.all) { //IE
                oEvent.keyCode = 0;
            } else { //NS
                oEvent.preventDefault();
                oEvent.stopPropagation();
            }
            window.status = msg;
        } catch (ex) {
            //alert(ex);
        }
    }
    return bRet;
}


function clickIE4() {
    if (event.button == 2) {
        alert(message);
        return false;
    }
}

function clickNS4(e) {
    if (document.layers || document.getElementById && !document.all) {
        if (e.which == 2 || e.which == 3) {
            alert(message);
            return false;
        }
    }
}

if (document.layers) {
    document.captureEvents(Event.MOUSEDOWN);
    document.onmousedown = clickNS4;
} else if (document.all && !document.getElementById) {
    document.onmousedown = clickIE4;
}

//document.oncontextmenu=new Function("window.status = msg;return false")


// funciones para el procesamiento de formularios con Clase
function ValidarCampo(lbl, obj, brequerido, ntipo, scampos, nValMax, nValMin, boostrap = false) {
    var campo_error = 'CampoErrorFormulario';
    var campo_form = 'CampoFormulario';
    if (boostrap) {
        if( ntipo == 1){
            campo_error = 'form-control input-sm error_form_control text-right validarDecimalInput';
            campo_form = 'form-control input-sm text-right validarDecimalInput';
        }else{
            campo_error = 'form-control input-sm error_form_control';
            campo_form = 'form-control input-sm ';
        }

    }else{
        if( ntipo == 1){
            campo_error = 'CampoErrorFormulario validarDecimalInput';
            campo_form = 'CampoFormulario validarDecimalInput';
        }
    }

    tipoObjeto = obj.type;
    if (tipoObjeto.indexOf('select', 0) != -1)
        ntipo = 7;
    if (ntipo > 100) {
        var opcs = obj.length;
        if (obj[opcs - 1].checked) {
            if (ValidarCampo(lbl, document.getElementById('-opc-' + obj[0].id), brequerido, ntipo - 100, ''))
                return '';
            else
                return false;
        } else
            return '';
    }
    if (brequerido == 1 && obj.type != 'radio') {
        if (obj.value == "") {
            obj.className = campo_error;
            try {
                document.getElementById('lbl' + lbl).className = 'Rojo';
                return 'Ingrese un Valor en ' + document.getElementById('lbl' + lbl).innerHTML + '\n';

            } catch (err) {
                iPos = lbl.indexOf('-', 1);
                sAux = lbl.substring(iPos + 1);
                document.getElementById('lbl-ln0-' + sAux).className = 'Rojo';
                return 'Ingrese un Valor en ' + document.getElementById('lbl-ln0-' + sAux).innerHTML + '\n';
            }
        } else {
            obj.className = campo_form;
            try {
                document.getElementById('lbl' + lbl).className = '';
            } catch (err) {
                iPos = lbl.indexOf('-', 1);
                sAux = lbl.substring(iPos + 1);
                document.getElementById('lbl-ln0-' + sAux).className = '';
            }
        }
    }

    switch (ntipo) {
        case 1:
            // VALOR NUMERICO
            //alert("numero");
            if (isNaN(obj.value)) {
                obj.className = campo_error;
                try {
                    document.getElementById('lbl' + lbl).className = 'Rojo';
                    return 'Valor numerico invalido en ' + document.getElementById('lbl' + lbl).innerHTML + '\n';
                } catch (err) {
                    iPos = lbl.indexOf('-', 1);
                    sAux = lbl.substring(iPos + 1);
                    document.getElementById('lbl-ln0-' + sAux).className = 'Rojo';
                    return 'Valor numerico invalido en ' + document.getElementById('lbl-ln0-' + sAux).innerHTML + '\n';
                }
            } else {

                //alert(nValMax +' '+ obj.value);
                if ((nValMax != '' && Number(obj.value) > Number(nValMax)) || (nValMin != '' && Number(obj.value) < Number(nValMin))) {
                    obj.className = campo_error;
                    try {
                        document.getElementById('lbl' + lbl).className = 'Rojo';
                        return 'Valor numerico invalido en ' + document.getElementById('lbl' + lbl).innerHTML + '\n';
                    } catch (err) {
                        iPos = lbl.indexOf('-', 1);
                        sAux = lbl.substring(iPos + 1);
                        document.getElementById('lbl-ln0-' + sAux).className = 'Rojo';
                        return 'Valor numerico invalido en ' + document.getElementById('lbl-ln0-' + sAux).innerHTML + '\n';
                    }
                } else {
                    obj.className = campo_form;
                    try {
                        document.getElementById('lbl' + lbl).className = '';
                    } catch (err) {
                        iPos = lbl.indexOf('-', 1);
                        sAux = lbl.substring(iPos + 1);
                        document.getElementById('lbl-ln0-' + sAux).className = '';
                    }
                }
            }
            break;
        case 2:
            // VALOR FECHA
            if (esFecha(obj.value) == true) {
                try {
                    document.getElementById('lbl' + lbl).className = '';
                } catch (err) {
                    iPos = lbl.indexOf('-', 1);
                    sAux = lbl.substring(iPos + 1);
                    document.getElementById('lbl-ln0-' + sAux).className = '';
                }
                document.getElementById('err' + lbl).innerHTML = '';
            } else {
                try {
                    document.getElementById('lbl' + lbl).className = 'Rojo';
                } catch (err) {
                    iPos = lbl.indexOf('-', 1);
                    sAux = lbl.substring(iPos + 1);
                    document.getElementById('lbl-ln0-' + sAux).className = 'Rojo';
                }

                return 'Fecha invalida';
            }
            break;
        case 4:
            // VALOR EMAIL
            if (obj.value != '') {
                if (!validar_email(obj.value)) {
                    obj.className = campo_error;

                    try {
                        document.getElementById('lbl' + obj.name).className = 'Rojo';
                        return 'Valor invalido en ' + document.getElementById('lbl' + lbl).innerHTML + '\n';
                    } catch (err) {
                        iPos = obj.name.indexOf('-', 1);
                        sAux = obj.name.substring(iPos + 1);
                        document.getElementById('lbl-ln0-' + sAux).className = 'Rojo';
                        return 'Valor invalido en ' + document.getElementById('lbl-ln0-' + sAux).innerHTML + '\n';
                    }
                } else {
                    obj.className = campo_form;
                    try {
                        document.getElementById('lbl' + obj.name).className = '';
                    } catch (err) {
                        iPos = obj.name.indexOf('-', 1);
                        sAux = obj.name.substring(iPos + 1);
                        document.getElementById('lbl-ln0-' + sAux).className = '';
                    }
                }
            } else {
                obj.className = campo_form;
                try {
                    document.getElementById('lbl' + obj.name).className = '';
                } catch (err) {
                    iPos = obj.name.indexOf('-', 1);
                    sAux = obj.name.substring(iPos + 1);
                    document.getElementById('lbl-ln0-' + sAux).className = '';
                }
            }
            break;
        case 6:
            // VALOR RADIO BUTTON
            if (eval('document.forms[0].' + obj.id + '[0].checked') == false && eval('document.forms[0].' + obj.id + '[1].checked') == false) {
                obj.className = campo_error;
                try {
                    document.getElementById('lbl' + lbl).className = 'Rojo';
                } catch (err) {
                    iPos = obj.name.indexOf('-', 1);
                    sAux = obj.name.substring(iPos + 1);
                    document.getElementById('lbl-ln0-' + sAux).className = 'Rojo';
                }
                return 'Selecione una Opcion en ' + document.getElementById('lbl' + lbl).innerHTML + '\n';
            } else {
                obj.className = campo_form;
                try {
                    document.getElementById('lbl' + lbl).className = '';
                } catch (err) {
                    iPos = obj.name.indexOf('-', 1);
                    sAux = obj.name.substring(iPos + 1);
                    document.getElementById('lbl-ln0-' + sAux).className = '';
                }
            }
            break;
        case 7:
            // LISTAS DE SELECCION
            if (obj.value == "") {
                obj.className = campo_error;
                try {
                    document.getElementById('lbl' + lbl).className = 'Rojo';
                    return 'Ingrese un Valor en ' + document.getElementById('lbl' + lbl).innerHTML + '\n';

                } catch (err) {
                    iPos = lbl.indexOf('-', 1);
                    sAux = lbl.substring(iPos + 1);
                    document.getElementById('lbl-ln0-' + sAux).className = 'Rojo';
                    return 'Ingrese un Valor en ' + document.getElementById('lbl-ln0-' + sAux).innerHTML + '\n';
                }
            }
            break;
    }
    return '';
}

function ProcesarFormulario(form_id = 0, fomulario_in = 'form1') {

    if (!procesar_control()) return false;


    var smes = '';
    var bval = true;
    var iNe = document.forms[form_id].elements.length - 1;

    for (i = 0; i <= iNe; i++) {

        try {


            if (document.getElementById('val-' + document.forms[form_id].elements[i].id).innerHTML != '') {
                saux = eval(document.getElementById('val-' + document.forms[form_id].elements[i].id).innerHTML);

                if (saux != undefined) {
                    smes += eval(document.getElementById('val-' + document.forms[form_id].elements[i].id).innerHTML);
                    //alert('yeah');
                }
                if (smes != '' && bval == true) {
                    bval = false;
                    foco = document.forms[form_id].elements[i].id;
                }
            }
        } catch (err) {

        }
    }
    if (bval == true) {
        //AutoCompletarIE();
        return true;
    } else {
        //alert('Existen uno o mas campos que no tienen valor o tienen valores inv�idos '+smes);
        alert(smes);
        try {
            if(fomulario_in == 'formpago'){
                document.formpago[foco].focus();
            }else{
                document.form1[foco].focus();
            }

        } catch (err) {
        }
        return false;
    }
}


// enfoque de controles de un formulario
function foco(obj) {
    try {
        if (obj == '') {
            var i = -1;
            do {
                i++;
                //alert(document.forms[0].elements[i].id);
                if (!document.forms[0].elements[i].disabled && document.forms[0].elements[i].type != "button" && document.forms[0].elements[i].type != "hidden") {
                    document.forms[0].elements[i].focus();
                    return;
                }
            } while (i < document.forms[0].elements.length);
        } else {
            if (!document.getElementById(obj).disabled)
                document.getElementById(obj).focus();
        }
    } catch (err) {
    }
}

// calculo de coordenadas
function getX(menu, ori) {
    var onMac = navigator.platform ? navigator.platform == "MacPPC" : false;
    var onBrowser = navigator.appName;
    var xmenu = document.getElementById(menu);
    if (ori == 'h')
        var x = xmenu.offsetHeight;
    else
        var x = 0;
    var lastoffset = -1;
    //document.getElementById('texto').value = "x\n";
    while (xmenu) {
        //document.getElementById('texto').value+=xmenu.offsetTop.toString()+'-'+xmenu.tagName+'-'+xmenu.topMargin+'\n';
        if (xmenu.offsetTop != 0 && xmenu.tagName != 'TR') {
            if (lastoffset != xmenu.offsetTop) {
                x += xmenu.offsetTop;
                lastoffset = xmenu.offsetTop;
            }
        }
        if (xmenu.topMargin && onMac && onBrowser == "Microsoft Internet Explorer") {
            if (xmenu.topMargin != 0) x += parseInt(xmenu.topMargin);
        }
        if (onMac && onBrowser == "Microsoft Internet Explorer")
            xmenu = xmenu.parentElement;
        else
            xmenu = xmenu.offsetParent;

    }
    return x;
}

function getY(menu, ori) {
    var onMac = navigator.platform ? navigator.platform == "MacPPC" : false;
    var onBrowser = navigator.appName;
    var ymenu = document.getElementById(menu);
    if (ori == 'v')
        var y = ymenu.offsetWidth;
    else
        var y = 0;
    var lastoffset = -1;
    while (ymenu) {
        if (ymenu.offsetLeft != 0 && ymenu.tagName != 'DIV') {
            if (lastoffset != ymenu.offsetLeft) {
                y += ymenu.offsetLeft;
                lastoffset = ymenu.offsetLeft
            }
        }
        if (ymenu.leftMargin && onMac && onBrowser == "Microsoft Internet Explorer") {
            if (ymenu.leftMargin != 0) y += parseInt(ymenu.leftMargin);
        }
        if (onMac && onBrowser == "Microsoft Internet Explorer")
            ymenu = ymenu.parentElement;
        else
            ymenu = ymenu.offsetParent;
    }
    return y;
}

// validaciones de teclado

var va = 0;

function valorant(valor) {
    tmp = valor.value
    if ((tmp > 0) || (tmp <= 0))
        va = valor.value
}

function validarletra(valor) {
    a = valor.value
    if (!((a > 0) || (a <= 0))) {
        alert("Ingrese unicamente digitos de 0 al 9, para separacion de decimales ingrese '.'")
        valor.value = va
    }
}

function ValidarTeclas(min, max, e) {
    var evt = e ? e : event;
    if ((evt.keyCode <= min || evt.keyCode > max)) evt.returnValue = false;
}


function showImage(forma) {
    forma.imagen.src = forma.imagen.value;
}

function AutoCompletarIE() {
    if (navigator.appName == "Microsoft Internet Explorer")
        window.external.AutoCompleteSaveForm(form1);
}


var daysInMonth = makeArray(12);
daysInMonth[1] = 31;
daysInMonth[2] = 29;
daysInMonth[3] = 31;
daysInMonth[4] = 30;
daysInMonth[5] = 31;
daysInMonth[6] = 30;
daysInMonth[7] = 31;
daysInMonth[8] = 31;
daysInMonth[9] = 30;
daysInMonth[10] = 31;
daysInMonth[11] = 30;
daysInMonth[12] = 31;

function makeArray(n) {
    for (var i = 1; i <= n; i++) {
        this[i] = 0;
    }
    return this;
}

function daysInFebruary(whichYear) {
    return (whichYear % 4 == 0 && (!(whichYear % 100 == 0) || (whichYear % 400 == 0)) ? 29 : 28);
}

function esFecha(fecha) {
    aa = fecha.substring(0, 4);
    mm = fecha.substring(5, 7);
    dd = fecha.substring(8, 10);
    //alert ("ESTA AQUI "+fecha);
    if (isDate(mm, dd, aa) == true) {
        return true;
    } else {
        return false;
    }
}

function isDate(mm, dd, yyyy) {
    if (mm != "" && !(mm > 0 && mm < 13)) {
        return false;
    }
    if (dd != "" && !(dd > 0 && dd < 32)) {
        return false;
    }
    if ((dd != "" && mm != "") && dd > daysInMonth[mm]) {
        return false;
    }
    if (yyyy != "" && !(yyyy > 1889 && yyyy < 2101)) {
        return false;
    }
    if ((mm == "2" || mm == "02" && dd != "" && yyyy != "") && dd > daysInFebruary(yyyy)) {
        return false;
    }
    return true;
}

function doValidarFechaAnterior(dt, fec) {  //Valida fechas en un rango  dt mayor, fec menor
    cont = dt.length;
    aa = dt.substring(0, 4);
    mm = dt.substring(5, 7);
    dd = dt.substring(8, 10);
    a1 = fec.substring(0, 4);
    m1 = fec.substring(5, 7);
    d1 = fec.substring(8, 10);
    a2 = parseFloat(aa);
    a12 = parseFloat(a1);
    m2 = parseFloat(mm);
    m12 = parseFloat(m1);
    d2 = parseFloat(dd);
    d12 = parseFloat(d1);

    if (cont != 10) {
        alert("Fecha Invalida!");
        return false;
    } else {
        if (isDate(mm, dd, aa) == false) {
            alert("Fecha Invalida!");
            return false;
        } else {
            if (a2 < a12) {
                alert("Fecha Invalida!");
                return false;
            } else {
                if (a2 == a12) {
                    if (m2 < m12) {
                        alert("Fecha Invalida! ");
                        return false;
                    } else {
                        if (m2 == m12) {
                            if (d2 <= d12) {
                                alert("Fecha Invalida!");
                                return false;
                            }
                        }
                    }
                }
            }
        }
    }
    return true;
}

//  funciones para buscar en los comboBox
var digitos = 10 //cantidad de digitos buscados
var puntero = 0
var buffer = new Array(digitos) //declaraci� del array Buffer
var cadena = ""

function buscar_op(obj, objfoco) {
    var keynum
    var keychar
    var numcheck
    if (window.event) // IE
    {
        keynum = e.keyCode
    } else if (e.which) // netscape/Firefox/opera
    {
        keynum = e.which
    }
    keychar = String.fromCharCode(keynum)

    var letra = keynum;
    numcontador = 0;
    if (puntero >= digitos) {
        cadena = "";
        puntero = 0;
    }
    //si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto...
    if (letra == 13) {
        borrar_buffer();
        if (objfoco != 0) objfoco.focus(); //evita foco a otro objeto si objfoco=0
    }
    //sino busco la cadena tipeada dentro del combo...
    else {
        buffer[puntero] = letra;
        //guardo en la posicion puntero la letra tipeada
        cadena = cadena + buffer[puntero]; //armo una cadena con los datos que van ingresando al array
        puntero++;

        //barro todas las opciones que contiene el combo y las comparo la cadena...
        for (var opcombo = 0; opcombo < obj.length; opcombo++) {
            if (obj[opcombo].text.substr(0, puntero).toLowerCase() == cadena.toLowerCase()) {
                obj.selectedIndex = opcombo;
                opcombo = obj.length;
            } else {
                numcontador++;
            }
        }
        if (numcontador == obj.length) {
            borrar_buffer();
        }
    }
    event.returnValue = false; //invalida la acci� de pulsado de tecla para evitar busqueda del primer caracter
}

function borrar_buffer() {
    //inicializa la cadena buscada
    cadena = "";
    puntero = 0;
}


function buscar_sig(objfoco, e) {
    var keychar
    var numcheck
    var keynum = window.Event ? e.which : e.keyCode;
    if (keynum == 13) {
        objfoco.focus(); //evita foco a otro objeto si objfoco=0
    }
}

/*Funci� para validar el RUC
recibe como par�etro el objeto que contiene la informacion*/
function fnvalruc(r) {
    try {
        if (document.getElementById('vcascte01').checked == true) {
            var ctrval = true
        } else {
            var ctrval = false
        }
    } catch (err) {
        var ctrval = true
    }
    if ((ctrval == true) && (r.value != "") && (r.value != "9999999999")) {
        str_ruc = r.value	//recuperamos el valor del campo
        cont = str_ruc.length		//sacamos la longitud
        pro = str_ruc.substring(0, 2)	//sacamos los 2 primero digitos para verificar la provincia
        pro1 = parseFloat(pro)
        cod = str_ruc.substring(2, 3)	//sacamos el tercer digito para verificar el tipo de institucion
        ver = str_ruc.substring(0, 9)	//sacamos los primeros 9 digitos que necesitamos para el calculo del digito verificador
        dig = str_ruc.substring(9, 10)	//sacamos el decimo digito que es el de verificaci�
        dig1 = parseInt(dig)
        di = str_ruc.substring(8, 9)	//sacamos el noveno digito que es el de verificaci� en unos casos
        dig2 = parseInt(di)

        a = ver.substring(0, 1)	//sacamos el primer d�ito
        b = ver.substring(1, 2)	//sacamos el segundo d�ito
        c = ver.substring(2, 3)	//sacamos el tercer d�ito
        d = ver.substring(3, 4)	//sacamos el cuarto d�ito
        e = ver.substring(4, 5)	//sacamos el quinto d�ito
        f = ver.substring(5, 6)	//sacamos el sexto d�ito
        g = ver.substring(6, 7)	//sacamos el septimo d�ito
        h = ver.substring(7, 8)	//sacamos el octavo d�ito
        i = ver.substring(8, 9)	//sacamos el noveno d�ito
        /*Tranformamos todos los digitos a enteros*/
        a1 = parseInt(a)
        b1 = parseInt(b)
        c1 = parseInt(c)
        d1 = parseInt(d)
        e1 = parseInt(e)
        f1 = parseInt(f)
        g1 = parseInt(g)
        h1 = parseInt(h)
        i1 = parseInt(i)

        if (cont < 10) {  //Validamos que la logitud de la cadena sea de 10 d�itos  //1
            alert("Ruc o Cedula Invalida, debe ser minimo de 10 digitos")
            r.value = "";
            r.focus()
        } else { //1
            if ((pro1 <= 0) || (pro1 >= 23)) {  //Validamos que los 2 primeros digitos sean entre 01 y 23 que corresponden a las provincias
                alert("Numero de Ruc o Cedula Invalida, codigo de provincia inexistente")
                r.value = ""
                r.focus()
            } else { //2
                if ((cod == "7") || (cod == "8")) {   //Verificamos que el tercer digito sea diferente de 7 u 8 ya que no puede tomar estos valores
                    alert("Ruc o Cedula Invalida")
                    r.value = ""
                    r.focus()
                } else {//3
                    if (cod == "9") {  //Si el tercer digito es 9 aplicamos el algoritmo de empresas privadas
                        s = 0
                        s = a1 * 4 + b1 * 3 + c1 * 2 + d1 * 7 + e1 * 6 + f1 * 5 + g1 * 4 + h1 * 3 + i1 * 2
                        s = s % 11
                        if (s != 0) {
                            s = 11 - s
                        }
                        if (dig1 != s) {
                            alert("Ruc o Cedula Invalida, no coincide digito verificador")
                            r.value = ""
                            r.focus()
                        }
                    } else {//4
                        if (cod == "6") {  //Si el tercer digito es 6 aplicamos el algoritmo de empresas pblicas
                            s = 0
                            s = a1 * 3 + b1 * 2 + c1 * 7 + d1 * 6 + e1 * 5 + f1 * 4 + g1 * 3 + h1 * 2
                            s = s % 11
                            if (s != 0) {
                                s = 11 - s
                            }
                            if (dig2 != s) {
                                alert("Ruc o Cedula Invalida, no coincide digito verificador")
                                r.value = ""
                                r.focus()
                            }
                        } else { //5 // para los dem� digitos se usa el algoritmo de personas naturales
                            s = 0
                            for (i = 0; i < 9; i++) {
                                s2 = ver.substring(i, i + 1)
                                s1 = parseInt(s2)
                                if ((i % 2) == 0) {
                                    s1 = s1 * 2
                                    if (s1 >= 10) {
                                        z = s1 / 10
                                        z = parseInt(z)
                                        y = s1 % 10
                                        s1 = z + y
                                    }
                                }
                                s = s + s1
                            }
                            s = s - (parseInt((s / 10)) * 10)
                            if (s != 0) {
                                s = 10 - s
                            }
                            if (dig1 != s) {
                                alert("Ruc o Cedula Invalida, no coincide digito verificador")
                                r.value = ""
                                r.focus()
                            }
                        }//5
                    }//4
                }//3
            }//2
        }//1
    }
}

function redondear(valor, decimales) {
    if (valor != 0) {
        divisor = Math.pow(10, decimales);
        retorno = Math.round(Number(valor) * divisor) / divisor;
    } else {
        retorno = 0;
    }
    return retorno;
}

function reemplazar(entry) {
    out = "/"; // reemplazar el /
    add = "-"; // por el -
    temp = "" + entry;
    while (temp.indexOf(out) > -1) {
        pos = temp.indexOf(out);
        temp = "" + (temp.substring(0, pos) + add +
            temp.substring((pos + out.length), temp.length));
    }
    entry = temp;
    return entry;
}

function reemplazarPor(entry, out, add) {
    //out = "/"; // reemplazar el /
    //add = "-"; // por el -
    temp = "" + entry;
    while (temp.indexOf(out) > -1) {
        pos = temp.indexOf(out);
        temp = "" + (temp.substring(0, pos) + add +
            temp.substring((pos + out.length), temp.length));
    }
    entry = temp;
    return entry;
}


function letras(c, d, u) {
    var centenas, decenas, decom
    var lc = ""
    var ld = ""
    var lu = ""
    centenas = eval(c);
    decenas = eval(d);
    decom = eval(u);
    switch (centenas) {
        case 0:
            lc = "";
            break;
        case 1: {
            if (decenas == 0 && decom == 0)
                lc = "CIEN"
            else
                lc = "CIENTO ";
        }
            break;
        case 2:
            lc = "DOSCIENTOS ";
            break;
        case 3:
            lc = "TRESCIENTOS ";
            break;
        case 4:
            lc = "CUATROCIENTOS ";
            break;
        case 5:
            lc = "QUINIENTOS ";
            break;
        case 6:
            lc = "SEISCIENTOS ";
            break;
        case 7:
            lc = "SETECIENTOS ";
            break;
        case 8:
            lc = "OCHOCIENTOS ";
            break;
        case 9:
            lc = "NOVECIENTOS ";
            break;
    }
    switch (decenas) {
        case 0:
            ld = "";
            break;
        case 1: {
            switch (decom) {
                case 0:
                    ld = "DIEZ";
                    break;
                case 1:
                    ld = "ONCE";
                    break;
                case 2:
                    ld = "DOCE";
                    break;
                case 3:
                    ld = "TRECE";
                    break;
                case 4:
                    ld = "CATORCE";
                    break;
                case 5:
                    ld = "QUINCE";
                    break;
                case 6:
                    ld = "DIECISIES";
                    break;
                case 7:
                    ld = "DIECISIETE";
                    break;
                case 8:
                    ld = "DIECIOCHO";
                    break;
                case 9:
                    ld = "DIECINUEVE";
                    break;
            }
        }
            break;
        case 2:
            ld = "VEINTE";
            break;
        case 3:
            ld = "TREINTA";
            break;
        case 4:
            ld = "CUARENTA";
            break;
        case 5:
            ld = "CINCUENTA";
            break;
        case 6:
            ld = "SESENTA";
            break;
        case 7:
            ld = "SETENTA";
            break;
        case 8:
            ld = "OCHENTA";
            break;
        case 9:
            ld = "NOVENTA";
            break;
    }
    switch (decom) {
        case 0:
            lu = "";
            break;
        case 1:
            lu = "UN";
            break;
        case 2:
            lu = "DOS";
            break;
        case 3:
            lu = "TRES";
            break;
        case 4:
            lu = "CUATRO";
            break;
        case 5:
            lu = "CINCO";
            break;
        case 6:
            lu = "SEIS";
            break;
        case 7:
            lu = "SIETE";
            break;
        case 8:
            lu = "OCHO";
            break;
        case 9:
            lu = "NUEVE";
            break;
    }

    if (decenas == 1) {
        return lc + ld;
    }
    if (decenas == 0 || decom == 0) {
        return lc + " " + ld + lu;
    } else {
        if (decenas == 2) {
            ld = "VEINTI";
            return lc + ld + lu;
        } else {
            return lc + ld + " Y " + lu
        }
    }
}

function getNumberLiteral(n) {
    var m0, cm, dm, um, cmi, dmi, umi, ce, de, un, hlp, decimal;

    if (isNaN(n)) {
        alert("La Cantidad debe ser un valor Numerico.");
        return null
    }
    m0 = parseInt(n / 1000000000000);
    rm0 = n % 1000000000000;
    m1 = parseInt(rm0 / 100000000000);
    rm1 = rm0 % 100000000000;
    m2 = parseInt(rm1 / 10000000000);
    rm2 = rm1 % 10000000000;
    m3 = parseInt(rm2 / 1000000000);
    rm3 = rm2 % 1000000000;
    cm = parseInt(rm3 / 100000000);
    r1 = rm3 % 100000000;
    dm = parseInt(r1 / 10000000);
    r2 = r1 % 10000000;
    um = parseInt(r2 / 1000000);
    r3 = r2 % 1000000;
    cmi = parseInt(r3 / 100000);
    r4 = r3 % 100000;
    dmi = parseInt(r4 / 10000);
    r5 = r4 % 10000;
    umi = parseInt(r5 / 1000);
    r6 = r5 % 1000;
    ce = parseInt(r6 / 100);
    r7 = r6 % 100;
    de = parseInt(r7 / 10);
    r8 = r7 % 10;
    un = parseInt(r8 / 1);
    decimal = redondear(r8 % 1 * 100, 0);
    //999123456789
    if (n < 1000000000000 && n >= 1000000000) {
        tmp = n.toString();
        s = tmp.length;
        tmp1 = tmp.slice(0, s - 9)
        tmp2 = tmp.slice(s - 9, s);

        tmpn1 = getNumberLiteral(tmp1);
        tmpn2 = getNumberLiteral(tmp2);

        if (tmpn1.indexOf("UN") >= 0)
            pred = " BILLON "
        else
            pred = " BILLONES "
        return tmpn1 + pred + tmpn2 + " CON " + decimal + "/100";
    }

    if (n < 10000000000 && n >= 1000000) {
        mldata = letras(cm, dm, um);
        hlp = mldata.replace("UN", "*");
        if (hlp.indexOf("*") < 0 || hlp.indexOf("*") > 3) {
            mldata = mldata.replace("UNO", "UN");
            mldata += " MILLONES ";
        } else {
            mldata = "UN MILLON ";
        }
        mdata = letras(cmi, dmi, umi);
        cdata = letras(ce, de, un);
        if (mdata != "	") {
            if (n == 1000000) {
                mdata = mdata.replace("UNO", "UN") + "DE";
            } else {
                mdata = mdata.replace("UNO", "UN") + " MIL ";
            }
        }

        return (mldata + mdata + cdata + " CON " + decimal + "/100");
    }
    if (n < 1000000 && n >= 1000) {
        mdata = letras(cmi, dmi, umi);
        cdata = letras(ce, de, un);
        hlp = mdata.replace("UN", "*");
        if (hlp.indexOf("*") < 0 || hlp.indexOf("*") > 3) {
            mdata = mdata.replace("UNO", "UN");
            return (mdata + " MIL " + cdata + " CON " + decimal + "/100");
        } else
            return ("MIL " + cdata + " CON " + decimal + "/100");
    }
    if (n < 1000 && n >= 1) {
        return (letras(ce, de, un) + " CON " + decimal + "/100");
    }
    if (n == 0) {
        return " CERO";
    }
    return "NO DISPONIBLE"
}


// nueva funcion 

function buscar_sig_a(objfoco, e) {

    var keynum = window.Event ? e.which : e.keyCode;
    var keychar
    var numcheck
    if (keynum == 13) {
        procesar_accion();
    }
}

//opcional si no definen no da error
function procesar_accion() {
    ;
}

// opcional se utiliza para verificar algun control antes de procesar el formulario
// se retorna false si hay algun error en el control deseado
function procesar_control() {
    return true;
}

function ventanaCarga(pagina, nom, w, h) {

    var opciones = ('toolbar=no, location=no, directories=no, status=no, menubar=no ,scrollbars=yes, resizable=no, fullscreen=no, modal=no, width=' + w + ',height=' + h + ', left=' + (screen.availWidth - w) / 2 + ', top=' + (screen.availHeight - h) / 2);
    var ven = window.open(pagina, nom, opciones);
    ven.focus();
}
