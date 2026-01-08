<?php
// Copyright (c) 2018, Altiria TIC SL
// All rights reserved.
// El uso de este código de ejemplo es solamente para mostrar el uso de la pasarela de envío de SMS de Altiria
// Para un uso personalizado del código, es necesario consultar la API de especificaciones técnicas, donde también podrás encontrar
// más ejemplos de programación en otros lenguajes y otros protocolos (http, REST, web services)
// https://www.altiria.com/api-envio-sms/

// XX, YY y ZZ se corresponden con los valores de identificacion del
// usuario en el sistema.
include('httpPHPAltiria.php');

$altiriaSMS = new AltiriaSMS();
$altiriaSMS->setUrl("http://www.altiria.net/api/http");
$altiriaSMS->setDomainId('demopr');
$altiriaSMS->setLogin('danielcastro');
$altiriaSMS->setPassword('r94ufs3n');

$altiriaSMS->setDebug(true);

//$sDestination = '346xxxxxxxx';
//$sDestination = '593987284494';
$sDestination = array('50241084433','593992035278','593987284494');

//No es posible utilizar el remitente en América pero sí en España y Europa
$response = $altiriaSMS->sendSMS($sDestination, "Prueba de envio a Centro America, Hola Mynor");
//Utilizar esta llamada solo si se cuenta con un remitente autorizado por Altiria
//$response = $altiriaSMS->sendSMS($sDestination, "Mensaje de prueba", "remitente");

if (!$response)
  echo "El envío ha terminado en error";
else
  echo $response;
?>

