<?php
/* ARCHIVO COMUN PARA LA EJECUCION DEL SERVIDOR AJAX DEL MODULO */
/***************************************************/
/* NO MODIFICAR */
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE).'comun.lib.php');
include_once(path(DIR_INCLUDE).'Clases/Formulario/Formulario.class.php');
require_once (path(DIR_INCLUDE).'Clases/xajax/xajax_core/xajax.inc.php');

require_once (path(DIR_INCLUDE).'Clases/GeneraDetalleAsientoContable.class.php');
require_once (path(DIR_INCLUDE).'Clases/GeneraDetalleInventario.class.php');

include_once(path(DIR_INCLUDE).'comun.lib.rd.php');

/***************************************************/
/* INSTANCIA DEL SERVIDOR AJAX DEL MODULO*/
$xajax = new xajax('_Ajax.server.php');
$xajax->setCharEncoding(SISTEMA_CHARSET);
$xajax->configure('decodeUTF8Input',true);
/***************************************************/
//    FUNCIONES PUBLICAS DEL SERVIDOR AJAX DEL MODULO 
//    Aqui registrar todas las funciones publicas del servidor ajax
//    Ejemplo,
//    $xajax->registerFunction("Nombre de la Funcion");
/***************************************************/
$xajax->registerFunction("genera_cabecera_formulario");
$xajax->registerFunction("upload");
$xajax->registerFunction("consultar");
$xajax->registerFunction("modificar");
$xajax->registerFunction("eliminar");
$xajax->registerFunction("autoriza");
$xajax->registerFunction("genera_documento");
$xajax->registerFunction("genera_formulario_modifica_detalle");
$xajax->registerFunction("verDiarioContable");
$xajax->registerFunction("genera_pdf_doc_compras");
$xajax->registerFunction("enviar_etiquetas");
$xajax->registerFunction("formulario_etiqueta");

//----------------------------------------------------------
//REGISTRO DE FUNCIONES PARA LOS SUPLIDORES
//----------------------------------------------------------

$xajax->registerFunction("clpv_reporte");

//----------------------------------------------------------
//FIN REGISTRO DE FUNCIONES PARA LOS SUPLIDORES
//----------------------------------------------------------


?>