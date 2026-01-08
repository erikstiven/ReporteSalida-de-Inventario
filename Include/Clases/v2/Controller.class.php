<?php

class Controller{

    public function __construct(){
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    }
 

    public static function api(){
        try{
            $action = $_GET['action'];// del url
           
            if(empty($action)){
                throw new Exception("No se enconto el parametro action");
            }
            $content = json_decode(file_get_contents("php://input"),true);
       
            $controlador = new static();
       
            if (!method_exists($controlador, $action)) {
                throw new Exception("No se encontro un metodo valido");
            }
            $result = call_user_func([$controlador,$action],$content);
            echo $result;
       
        }catch(Exception $e){
            echo $e;
            exit;
        }
    }
}