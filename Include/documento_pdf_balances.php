<?php
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
set_time_limit(3000);
ini_set('memory_limit', '20000M');
require_once 'html2pdf_v4.03/html2pdf.class.php';

$documento = $_SESSION['pdf'];
try{

$html2pdf = new HTML2PDF('P','A4','es',true,'UTF-8',10);
$html2pdf->WriteHTML($documento);
$html2pdf->Output('documento.pdf','I');
}
catch(HTML2PDF_exception $e){    
    echo $e;
    exit;

}


?>