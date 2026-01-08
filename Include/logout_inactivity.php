<?php
include_once('config.inc.php'); 
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

session_unset();

echo json_encode(session_destroy());
?>