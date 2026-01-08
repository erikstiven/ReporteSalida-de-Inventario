<?php
class Controlador{
    public function __construct(){
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
    }

    public static function api(){
        try{
            $action = $_GET['action'];
            $content_type = $_SERVER['CONTENT_TYPE'] ?? null;

           
            if(empty($action)){
                throw new Exception("No se enconto el parametro action");
            }
            $content = [];
            if(!empty($content_type)){
                if($content_type == "application/json"){
                    $content = json_decode(file_get_contents("php://input"),true) ?? [];
                }elseif(strpos($content_type, "multipart/form-data") !== false){
                    $content = $_FILES ?? [];
                }
            }
       
            $controlador = new static();
       
            if (!method_exists($controlador, $action)) {
                throw new Exception("No se encontro un metodo valido");
            }

            $response = $response = [
                'status' => 'error',
                'message' => '',
                'data' => []
            ];
            try {
                $response = call_user_func([$controlador,$action],$content);
                echo $response;
            } catch (\Throwable $e) {
                $response['status'] = 'error';
                $response['message'] = $e->getMessage();
                throw new Exception(json_encode($response));
            } 
            
        }catch(\Throwable $e){
            echo $e->getMessage();
            exit;
        }
    }
}
?>