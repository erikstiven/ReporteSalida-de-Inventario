<?php
/* ARCHIVO COMUN PARA LA EJECUCION DEL SERVIDOR AJAX DEL MODULO */

/***************************************************/
/* NO MODIFICAR */
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
include_once(path(DIR_INCLUDE) . 'Clases/Formulario/Formulario.class.php');
require_once(path(DIR_INCLUDE) . 'Clases/xajax/xajax_core/xajax.inc.php');
require_once(path(DIR_INCLUDE) . 'Clases/GeneraDetalleInventario.class.php');


/***************************************************/
/* INSTANCIA DEL SERVIDOR AJAX DEL MODULO*/
$xajax = new xajax('_Ajax.server.php');
$xajax->setCharEncoding('ISO-8859-1');
/***************************************************/
//	FUNCIONES PUBLICAS DEL SERVIDOR AJAX DEL MODULO 
//	Aqui registrar todas las funciones publicas del servidor ajax
//	Ejemplo,
//	$xajax->registerFunction("Nombre de la Funcion");
/***************************************************/
//	Fuciones de lista de pedido
$xajax->registerFunction("genera_formulario_pedido");
$xajax->registerFunction("agrega_modifica_grid");
$xajax->registerFunction("agrega_modifica_grid_update");
$xajax->registerFunction("total_grid_update");
$xajax->registerFunction("total_grid");
$xajax->registerFunction("mostrar_grid");
$xajax->registerFunction("mostrar_grid_ret");
$xajax->registerFunction("cancelar_pedido");
$xajax->registerFunction("elimina_detalle");
$xajax->registerFunction("actualiza_grid");
$xajax->registerFunction("guarda_pedido");
$xajax->registerFunction("cargar_grid");


// F U N C I O N E S     P A R A     E L     
// S E C U E N C I A L     D E L      P E D I D O
$xajax->registerFunction("secuencial_pedido");
$xajax->registerFunction("cero_mas");

// CLIENTE NUEVO
$xajax->registerFunction("genera_formulario_cliente");
$xajax->registerFunction("guardar_cliente");
$xajax->registerFunction("control_tran");

// LISTA DE FACTURA
$xajax->registerFunction("lista_factura");
$xajax->registerFunction("cargar_factura");

$xajax->registerFunction("genera_pdf_doc");

$xajax->registerFunction("cargar_ord_compra");
$xajax->registerFunction("cargar_ord_compra_respaldo");
$xajax->registerFunction("modal_cargar_archivo");

$xajax->registerFunction("cargar_arbol");
$xajax->registerFunction("abrir_modal_prod_filtro");
$xajax->registerFunction("procesar_informacion_filtro");

$xajax->registerFunction("abrir_modal_distribucion");
$xajax->registerFunction("validar_cuenta_contable");
$xajax->registerFunction("validar_centro_costos");

$xajax->registerFunction("calcular_valor_porcentaje");
$xajax->registerFunction("calcular_totales_distri");
$xajax->registerFunction("agregar_distribucion");

/***************************************************/
