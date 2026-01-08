<?php
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
set_time_limit(3000);
ini_set('memory_limit', '20000M');
require_once 'dompdf/dompdf_config.inc.php';

$documento = $_SESSION['pdf'];

//require_once 'alumnos.php';
$dompdf = new DOMPDF();
//$dompdf->set_paper("A4", "portrait");
$dompdf->load_html( utf8_decode( $documento) );
$dompdf->render();
$dompdf->stream("mi_archivo.pdf");        

?>

