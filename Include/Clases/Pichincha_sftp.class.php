<?php
// require_once(path(DIR_INCLUDE).'comun.lib.php');
// require_once(path(DIR_INCLUDE).'comun.lib.php');

class PichinchaSftp{
	
	private $oConexion1; 
	private $oConexion2; 

    function __construct($oConexion1,$oConexion2) {
        //Los objetos de conexion deben ser de tipo Transaccional
        $this->oConexion1 = $oConexion1;
        $this->oConexion2 = $oConexion2;

    }

    public function check_sftp_conexion($host_ip_or_name, $port){
        try{
            if (!function_exists("ssh2_connect")) {
                throw new Exception("function ssh2_connect doesn't exist");
            }        
            $sftp_conn = ssh2_connect($host_ip_or_name, $port,array('hostkey'=>'ssh-rsa'));

            return array(
                array(
                    "type"=>"conexion",
                    "message"=>$sftp_conn?"Conexion exitosa":"Conexion fallida",
                    "data"=>$sftp_conn,
                    "estado"=>$sftp_conn?"success":"error"
                )                
            );
        } catch (Exception $e) {
            return array(
                array(
                    "type"=>"error",
                    "mensaje"=>$e->getMessage(),                                     
                    "data"=>$e,
                    "estado"=>'error'
                )
            );
        }

    }

    public function check_sftp_auth($host_ip_or_name, $port,$user_name,$ppk_file_full_dir){

        $ppk_only_dir = "../../../Include/Clases/Formulario/Plugins/reloj/archivo_2024-04-17_18-54-59_0.83987800";
        // $ppk_file_full_dir = "../../../Include/Clases/Formulario/Plugins/reloj/archivo_2024-04-17_18-54-59_0.83987800.ppk";


        $archivo    = fopen($ppk_file_full_dir, "r");
        $datos      = file($ppk_file_full_dir); 
        $NumFilas   = count($datos);
        $primera_linea_ppk = ($datos[0]);
        
        // var_dump($host_ip_or_name);
        // var_dump($port);
        // var_dump($user_name);
        // var_dump($datos[0]);
        fclose($archivo);


        try{
            if (!function_exists("ssh2_connect")) {
                throw new Exception("function ssh2_connect doesn't exist");
            }       
            // $sftp_conn = ssh2_connect($host_ip_or_name, $port);
            $sftp_conn = ssh2_connect($host_ip_or_name, $port,array('hostkey'=>'ssh-rsa'));
            // $auth = ssh2_auth_pubkey_file($sftp_conn,$user_name,$ppk_file_full_dir); 
            
            $auth = ssh2_auth_pubkey_file($connection, $user_name,$ppk_file_full_dir);
            
            
            return array(
                array(
                    "type"=>"conexion",
                    "message"=>$sftp_conn?"Conexion exitosa ".$primera_linea_ppk:"Conexion falida".$primera_linea_ppk,                    
                    "data"=>$sftp_conn,
                    "estado"=>$sftp_conn?'success':'error'
                ),
                array(
                    "type"=>"autenticacion",
                    "message"=>$auth?"Autenticacion exitosa ".$primera_linea_ppk:"Autenticacion fallida ".$primera_linea_ppk,                    
                    "data"=>$auth,
                    "estado"=>$auth?'success':'error'
                )
            );


        } catch (Exception $e) {
            return array(
                array(
                    "type"=>"error",
                    "mensaje"=>$e->getMessage(),                                     
                    "data"=>$e,
                    "estado"=>'error'
                )
            );
        }

    }

    private function conexion_sftp($user_name,$host_ip_or_name,$port,$ppk_file_full_dir, $file_full_dir_to_sent,$file_full_name_to_sent){
        try{
            if (!function_exists("ssh2_connect")) {
                // die("function ssh2_connect doesn't exist");
                throw new Exception("function ssh2_connect doesn't exist");
            }
            
        
            $sftp_conn = ssh2_connect($host_ip_or_name, $port,array('hostkey'=>'ssh-rsa'));
            // if(empty($sftp_conn)){
            //     throw new Exception("Error de conexion con el servidor $host_ip_or_name");
            // }

            $auth = ssh2_auth_pubkey_file($sftp_conn,$user_name,$ppk_file_full_dir);

            // if(empty($auth)){
            //     throw new Exception("Error de autenticacion");
            // }

            $result = ssh2_scp_send($sftp_conn, $file_full_dir_to_sent, $file_full_name_to_sent,0644);
            ssh2_disconnect($sftp_conn);

            // if(empty($result)){
            //     throw new Exception("Respuesta vacia");
            // }
    
            return array(
                array(
                    "type"=>"conexion",
                    "message"=>$sftp_conn?"Conexion exitosa":"Conexion fallida",
                    "data"=>$sftp_conn,
                    "estado"=>$sftp_conn?'success':'error'
                ),array(
                    "type"=>"autenticacion",
                    "message"=>$auth?"Autenticacion exitosa":"Autenticacion fallida",
                    "data"=>$auth,
                    "estado"=>$auth?'success':'error'
                ),
                array(
                    "type"=>"transferencia",
                    "message"=>$result?"archivo $file_full_name_to_sent fue transferido por sftp a $host_ip_or_name ":"Error de trasferencia",
                    "data"=>$result,
                    "estado"=>$result?'success':'error'
                )
            );
        
        } catch (Exception $e) {
            return array(
                "type"=>"error",
                "mensaje"=>"Error de transferencia del archivo $file_full_name_to_sent por sftp a $host_ip_or_name: ".$e->getMessage(),
                "estado"=>$e,
                "data"=>$e
            );
        }

    }


    private function get_sftp_credentials($idempresa){

        $oConexion1 = $this->oConexion1;

        $sql_empr = "select 
                        empr_bpi_sftp_user,
                        empr_bpi_sftp_ip,
                        empr_bpi_sftp_port,
                        empr_bpi_sftp_ppk_f_dir 
                    from saeempr 
                    where 
                        empr_cod_empr = $idempresa";


        $empr_bpi_sftp_user         = '';
        $empr_bpi_sftp_ip           = '';
        $empr_bpi_sftp_port         = '';
        $empr_bpi_sftp_ppk_f_dir    = '';

        if ($oConexion1->Query($sql_empr)) {
            if ($oConexion1->NumFilas() > 0) {
                do {

                    $empr_bpi_sftp_user         = $oConexion1->f('empr_bpi_sftp_user');
                    $empr_bpi_sftp_ip           = $oConexion1->f('empr_bpi_sftp_ip');
                    $empr_bpi_sftp_port         = $oConexion1->f('empr_bpi_sftp_port');
                    $empr_bpi_sftp_ppk_f_dir    = $oConexion1->f('empr_bpi_sftp_ppk_f_dir');


                } while ($oConexion1->SiguienteRegistro());
            }
        }

        $oConexion1->Free();
        return array(
            "empr_bpi_sftp_user"        => $empr_bpi_sftp_user,
            "empr_bpi_sftp_ip"          => $empr_bpi_sftp_ip,
            "empr_bpi_sftp_port"        => $empr_bpi_sftp_port,
            "empr_bpi_sftp_ppk_f_dir"   => $empr_bpi_sftp_ppk_f_dir,
        );
    }

    public function sent_by_sftp($file_full_dir,$file_name,$idempresa){
        $credentiasl_arr = $this->get_sftp_credentials($idempresa);

        $empr_bpi_sftp_user         = $credentiasl_arr['empr_bpi_sftp_user'];
        $empr_bpi_sftp_ip           = $credentiasl_arr['empr_bpi_sftp_ip'];
        $empr_bpi_sftp_port         = $credentiasl_arr['empr_bpi_sftp_port'];
        $empr_bpi_sftp_ppk_f_dir    = $credentiasl_arr['empr_bpi_sftp_ppk_f_dir'];

        $result = $this->conexion_sftp($empr_bpi_sftp_user,$empr_bpi_sftp_ip,$empr_bpi_sftp_port,$empr_bpi_sftp_ppk_f_dir,$file_full_dir,$file_name);

        return $result;

    }  

}

?>