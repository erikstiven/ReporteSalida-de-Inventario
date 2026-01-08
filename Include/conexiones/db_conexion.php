<?php
//***************************
// Informacion de la conexion
//***************************//
define('MOTOR_BD_PO', 'pgsql');

define('HOST_PO', '10.100.46.32');
define('PORT_PO', '54320');
define('USUARIO_PO', 'sisconti');
define('CLAVE_PO', 'JB34na4Ac');
// define('BASE_PO', 'beloro2024');
//define('BASE_PO', 'minesadco2024');
define('BASE_PO', 'logistica2025');

/** CONFIGURACION BDD WEBSERVICE **/
define('MOTOR_BD_PO_ISP', 'pgsql');
define('HOST_PO_ISP', 'servidor');
define('PORT_PO_ISP', '5432');
define('USUARIO_PO_ISP', 'user');
define('CLAVE_PO_ISP', 'contra');
define('BASE_PO_ISP', 'base');

/* Inclusion de librerias y clases */
require_once path(DIR_INCLUDE) . 'Clases/Dbo.class.php';

$file_path = path(DIR_INCLUDE) . 'Clases/DboWs.class.php';

$DSN = MOTOR_BD_PO . '://' . USUARIO_PO . ':' . CLAVE_PO . '@' . HOST_PO . '/' . BASE_PO;
$DSN_API_ISP = MOTOR_BD_PO_ISP . '://' . USUARIO_PO_ISP . ':' . CLAVE_PO_ISP . '@' . HOST_PO_ISP . '/' . BASE_PO_ISP;

//Conexion General
$oCon = new Dbo;
$oCon->DSN = $DSN;
$oConA = new Dbo;
$oConA->DSN = $DSN;
//Conexion Login
$oConL = new Dbo;
$oConL->DSN = $DSN;
//conexiones de base de datos para ajax
$oCnx = new Dbo;
$oCnx->DSN = $DSN;
$oCnxa = new Dbo;
$oCnxa->DSN = $DSN;

$oCon = new Dbo;
$oCon -> DSN = $DSN;
$oCon -> Conectar();

$DSN_Ifx = MOTOR_BD_PO . '://' . USUARIO_PO . ':' . CLAVE_PO . '@' . HOST_PO . '/' . BASE_PO;
$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();
?>
