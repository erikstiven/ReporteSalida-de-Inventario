<?
// Copyright (c) 2018, Altiria TIC SL
// All rights reserved.
// El uso de este código de ejemplo es solamente para mostrar el uso de la pasarela de envío de SMS de Altiria
// Para un uso personalizado del código, es necesario consultar la API de especificaciones técnicas, donde también podrás encontrar
// más ejemplos de programación en otros lenguajes y otros protocolos (http, REST, web services)
// https://www.altiria.com/api-envio-sms/

// sDestination: lista de numeros de telefono separados por comas.
// Cada numero debe comenzar por el prefijo internacional de pais.
// sMessage: hasta 160 caracteres
// debug: Si es true muestra por pantalla la respuesta completa del servidor
// sSenderId: no es posible utilizar el remitente en América pero sí en España y Europa
// XX, YY y ZZ se corresponden con los valores de identificacion del
// usuario en el sistema.
function AltiriaSMS($sDestination, $sMessage, $debug, $sSenderId){

 $sData ='cmd=sendsms&';
 $sData .='domainId=demopr&';
 $sData .='login=danielcastro&';
 $sData .='passwd=r94ufs3n&';
 //No es posible utilizar el remitente en América pero sí en España y Europa
 $sData .='senderId='.$sSenderId.'&';
 $sData .='dest='.str_replace(',','&dest=',$sDestination).'&';
 $sData .='msg='.urlencode(utf8_encode(substr($sMessage,0,160)));

 //Tiempo máximo de espera para conectar con el servidor = 5 seg
 $timeOut = 5; 
 $fp = fsockopen('www.altiria.net', 80, $errno, $errstr, $timeOut);
 if (!$fp) {
  //Error de conexion o tiempo maximo de conexion rebasado
  $output = "ERROR de conexion: $errno - $errstr
\n";
  $output .= "Compruebe que ha configurado correctamente la direccion/url ";
  $output .= "suministrada por altiria
";
  return $output;
 } else {
  $buf = "POST /api/http HTTP/1.0\r\n";
  $buf .= "Host: www.altiria.net\r\n";
  $buf .= "Content-type: application/x-www-form-urlencoded; charset=UTF-8\r\n";
  $buf .= "Content-length: ".strlen($sData)."\r\n";
  $buf .= "\r\n";
  $buf .= $sData;
  fputs($fp, $buf);
  $buf = "";

  //Tiempo máximo de espera de respuesta del servidor = 60 seg
  $responseTimeOut = 60;
  stream_set_timeout($fp,$responseTimeOut);
  stream_set_blocking ($fp, true);
  if (!feof($fp)){
   if (($buf=fgets($fp,128))===false){
    // TimeOut?
    $info = stream_get_meta_data($fp);
    if ($info['timed_out']){
     $output = 'ERROR Tiempo de respuesta agotado';
     return $output;
    } else {
     $output = 'ERROR de respuesta';
     return $output;
    }
   } else{
    while(!feof($fp)){
     $buf.=fgets($fp,128);
    }
   }
  } else {
   $output = 'ERROR de respuesta';
   return $output;
  }

  fclose($fp);
  
  //Si la llamada se hace con debug, se muestra la respuesta completa del servidor
  if ($debug){
   print "Respuesta del servidor: 
".$buf."
";
  }
  
  //Se comprueba que se ha conectado realmente con el servidor
  //y que se obtenga un codigo HTTP OK 200 
  if (strpos($buf,"HTTP/1.1 200 OK") === false){
   $output = "ERROR. Codigo error HTTP: ".substr($buf,9,3)."
\n";
   $output .= "Compruebe que ha configurado correctamente la direccion/url ";
   $output .= "suministrada por Altiria
";
   return $output;
  }
  //Se comprueba la respuesta de Altiria
  if (strstr($buf,"ERROR")){
   $output = $buf."
\n";
   $output .= " Ha ocurrido algun error. Compruebe la especificacion
";
   return $output;
  } else {
   $output = $buf."
\n";
   $output .= " Exito
";
   return $output; 
  }     
 }
}

//No es posible utilizar el remitente en América pero sí en España y Europa
$resp= AltiriaSMS("593987284494", "Prueba de envio a Centro America, Hola Mynor", false, "");
//Utilizar esta llamada solo si se cuenta con un remitente autorizado por Altiria
//$resp= AltiriaSMS("346xxxxxxxx,346yyyyyyyy", "Mensaje de prueba", false, "remitente");
echo $resp;
?>