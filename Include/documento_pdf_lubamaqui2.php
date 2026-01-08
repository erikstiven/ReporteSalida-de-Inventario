<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
set_time_limit(3000);
ini_set('memory_limit', '20000M');
require_once 'html2pdf_v4.03/html2pdf.class.php';


$documento = '
              <html>
              <head>
              <style>
              table {
                width: 190mm;
                margin: 5mm;
              }
              td {
                border: 1px solid #e2e2e2;
                padding: 5px; 
                max-width: 25%;
                word-wrap: break-word;
                font-size: 8px;
              }
              img {
                width: 25%;
              }
              b {
                margin: 8px;
              }
              </style>
              </head>
              <body>

              ' . $_SESSION['pdf'] . '
                
              </body>
              </html>
              ';




$html2pdf = new HTML2PDF('P', 'A3', 'es');
$html2pdf->WriteHTML($documento);
$html2pdf->Output('documento.pdf', 'I');
