<?php
require_once('class/BCGFontFile.php');
require_once('class/BCGColor.php');
require_once('class/BCGDrawing.php');

require_once('class/BCGcode128.barcode.php');
header('Content-Type: image/png');

$colorFront = new BCGColor(0, 0, 0);
$colorBack = new BCGColor(255, 255, 255);

$code = new BCGcode128();
$code->setScale(2);
$code->setThickness(30);
$code->setForegroundColor($colorFront);
$code->setBackgroundColor($colorBack);
$code->parse('APPR16D20230094');

$drawing = new BCGDrawing('', $colorBack);
$drawing->setBarcode($code);

$drawing->draw();
$drawing->finish(BCGDrawing::IMG_FORMAT_PNG, 100, 'cod_barras_ad.png');

