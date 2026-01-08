// ************************************************** //
// OCULTA PATH DE LOS LINS //
var DHlinkStatus = {
  defaultStatus: "Texto por defecto",
  mouseOut: function() {
    window.defaultStatus = DHlinkStatus.defaultStatus;
    return true;
  },
  mouseOver: function(e) {
    var t = window.event ? event.srcElement : this;
    var s = t.title || t.innerText || t.textContent;
    defaultStatus = s || DHlinkStatus.defaultStatus;
    if (e.preventDefault) e.preventDefault();
    return true;
  },
  create: function() {
    var d = DHlinkStatus;
    var l = document.links;
    for (var x = 0; x < l.length; x++) {
      addEvent(l[x], "mouseover", d.mouseOver);
      addEvent(l[x], "mouseout", d.mouseOut);
    }
    defaultStatus = d.defaultStatus;
  }
};

function CloseAjaxWin() {
  ajaxwin.close();
}
function addEvent(obj, ev, funct) {
  if (obj.attachEvent) obj.attachEvent("on" + ev, funct);
  else if (obj.addEventListener) obj.addEventListener(ev, funct, true);
  else obj["on" + ev] = funct;
}
//addEvent(window, 'load', DHlinkStatus.create);

// ************************************************** //

// ************************************************** //
// FUNCIONES DE VENTANAS //
function AjaxWin(path, url, nombre, tipo, titulo, w, h, x, y, r, s) {
  ajaxwin = dhtmlwindow.open(
    path,
    nombre,
    tipo,
    url,
    titulo,
    "width=" +
      w +
      "px,height=" +
      h +
      "px,resize=" +
      r +
      ",scrolling=" +
      s +
      ",left=" +
      x +
      "px,top=" +
      y +
      "px"
  );
  ajaxwin.moveTo("middle", "middle");
}

function VentanaCentrada(pagina, nom, w, h) {
  var opciones =
    "toolbar=no, location=no, directories=no, status=no, menubar=no ,scrollbars=yes, resizable=no, fullscreen=no, modal=no, width=" +
    w +
    ",height=" +
    h +
    ", left=" +
    (screen.availWidth - w) / 2 +
    ", top=" +
    (screen.availHeight - h) / 2;
  var ven = window.open(pagina, nom, opciones);
  ven.focus();
}
function VentanaCompleta(pagina, nom) {
  var opciones =
    "toolbar=no, location=no, directories=no, status=no, menubar=no ,scrollbars=yes, resizable=no, channelmode=1, fullscreen=1, modal=no, width=" +
    screen.availWidth +
    ",height=" +
    screen.availHeight +
    ", left=" +
    (screen.availWidth - w) / 2 +
    ", top=" +
    (screen.availHeight - h) / 2;
  var ven = window.open(pagina, nom, opciones);
  ven.focus();
}

// ************************************************** //

function Maximizar() {
  window.moveTo(0, 0);
  if (document.all) {
    top.window.resizeTo(screen.availWidth, screen.availHeight);
  } else if (document.layers || document.getElementById) {
    if (
      top.window.outerHeight < screen.availHeight ||
      top.window.outerWidth < screen.availWidth
    ) {
      top.window.outerHeight = screen.availHeight;
      top.window.outerWidth = screen.availWidth;
    }
  }
}

function seleccionar(obj) {
  if (
    obj.nodeName.toLowerCase() == "textarea" ||
    (obj.nodeName.toLowerCase() == "input" && obj.type == "text")
  ) {
    obj.select();
    return;
  }
  if (window.getSelection) {
    var sel = window.getSelection();
    var range = document.createRange();
    range.selectNodeContents(obj);
    sel.removeAllRanges();
    sel.addRange(range);
  } else if (document.selection) {
    document.selection.empty();
    var range = document.body.createTextRange();
    range.moveToElementText(obj);
    range.select();
  }
}
function seleccionar_todo() {
  for (i = 0; i < document.DataGrid.elements.length; i++)
    if (document.DataGrid.elements[i].type == "checkbox")
      document.DataGrid.elements[i].checked = 1;
}
function deseleccionar_todo() {
  for (i = 0; i < document.DataGrid.elements.length; i++)
    if (document.DataGrid.elements[i].type == "checkbox")
      document.DataGrid.elements[i].checked = 0;
}

function marca_check(source) {
  checkboxes = document.getElementsByTagName("input"); //obtenemos todos los controles del tipo Input
  for (
    i = 0;
    i < checkboxes.length;
    i++ //recoremos todos los controles
  ) {
    if (checkboxes[i].type == "checkbox") {
      //solo si es un checkbox entramos
      checkboxes[i].checked = source.checked; //si es un checkbox le damos el valor del checkbox que lo llam� (Marcar/Desmarcar Todos)
    }
  }
}

function marca_check_class(source, clase) {
  checkboxes = document.getElementsByClassName(clase); //obtenemos todos los controles del tipo Input
  for (
    i = 0;
    i < checkboxes.length;
    i++ //recoremos todos los controles
  ) {
    if (checkboxes[i].type == "checkbox") {
      //solo si es un checkbox entramos
      checkboxes[i].checked = source.checked; //si es un checkbox le damos el valor del checkbox que lo llam� (Marcar/Desmarcar Todos)
    }
  }
}

function arregloCheck(clase) {
  var array = [];

  var checkboxes = document.getElementsByClassName(clase);
  var j = 0;
  for (var i = 0; i < checkboxes.length; i++) {
    if (checkboxes[i].type == "checkbox") {
      if (checkboxes[i].checked == true) {
        array[j] = checkboxes[i].value;
        j++;
      }
    }
  }

  return array;
}

function getRadioButtonSelectedValue(ctrl) {
  for (i = 0; i < ctrl.length; i++) if (ctrl[i].checked) return ctrl[i].value;
}

function limpiarFormulario() {
  $("#form1").trigger("reset");
}

function stopRKey(evt) {
  var evt = evt ? evt : event ? event : null;
  var node = evt.target ? evt.target : evt.srcElement ? evt.srcElement : null;
  if (evt.keyCode == 13 && node.type == "text") {
    return false;
  }
}

function cargarListaJS(val, data) {
  var element = $(val);

  //eliminar elementos
  $(element).empty();

  //agregar elementos
  $(element).append('<option value="">Seleccione una opcion:</option>');
  for (i = 0; i < data.length; i++) {
    $(element).append(
      '<option value="' + data[i]["id"] + '">' + data[i]["valor"] + "</option>"
    );
  }
}

function removeCSS(id) {
  var item = document.getElementsByTagName("link").item(id);
  item.parentNode.removeChild(item);
}

function NumText(string) {
  let out = '';
  let filtro = 'abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ1234567890'; //Caracteres validos

  for (let i = 0; i < string.length; i++)
      if (filtro.indexOf(string.charAt(i)) != -1)
          out += string.charAt(i);
  return out;
}

function ponerReadOnly(id){
	// Ponemos el atributo de solo lectura
	$("#"+id).attr("readonly","readonly");

	// Ponemos una clase para cambiar el color del texto y mostrar que esta deshabilitado
	$("#"+id).addClass("readOnly");
}

function quitarReadOnly(id){
	// Eliminamos el atributo de solo lectura
	$("#"+id).removeAttr("readonly");
	// Eliminamos la clase que hace que cambie el color
	$("#"+id).removeClass("readOnly");
}

function selectSeleccionarTodo(id){
	$("#" + id + " option").attr('selected', true).parent().trigger('change');
}

function selectEliminarTodo(id){
	$('#' + id + ' option').attr('selected', false).parent().trigger('change');
}