<?php

/*
  GPON
  Daniel Castro - VII2019
  Clase para gestión y administración de Gpon
 */

require_once(path(DIR_INCLUDE) . 'comun.lib.php');
require_once(path(DIR_INCLUDE) . 'Clases/Telnet.class.php');

class Gpon extends PHPTelnet{

    var $oCon;
    var $idDispositivo;
    var $idMarca;
    var $idSoftware;
    var $idPuerto;
    var $idOnu;
    var $puerto;
    var $onu;
    var $sn;
    var $ip;
    var $port;
    var $hostname;
    var $user;
    var $clave;

    function __construct($oCon, $idDispositivo) {
        $this->idDispositivo = $idDispositivo;
        $this->oCon = $oCon;
    }

    function getParametros($idPuerto, $idOnu){

    }

    public function getDispositivo(){

        $array = array();

        $sql = "SELECT * FROM isp.int_dispositivos WHERE id = $this->idDispositivo";
        if ($this->oCon->Query($sql)) {
            if ($this->oCon->NumFilas() > 0) {
               $array = array($this->oCon->f('id_marca'), 
                            $this->oCon->f('modelo'),
                            $this->oCon->f('software'),
                            $this->oCon->f('serial'),
                            $this->oCon->f('hostname'),
                            $this->oCon->f('ip'),
                            $this->oCon->f('port'),
                            $this->oCon->f('user'),
                            $this->oCon->f('user_password'),
                            $this->oCon->f('config_password'),
                            $this->oCon->f('estado'),
                            $this->oCon->f('latitud'),
                            $this->oCon->f('longitud')
                        );
            }
        }
        $this->oCon->Free();

        return $array;
    }

    function setPuerto($id){
        $sql = "SELECT puerto FROM isp.int_olt_puertos WHERE id = $id";
        $this->puerto = consulta_string_func($sql, 'puerto', $this->oCon, '');
        return $this->puerto;
    }

    function setOnu($id){
        $array = array();
        
        $sql = "SELECT id_tarjeta, id_caja FROM isp.int_contrato_caja WHERE id = $id";
        $this->sn = consulta_string_func($sql, 'id_tarjeta', $this->oCon, '');
        $this->onu = consulta_string_func($sql, 'id_caja', $this->oCon, '');

        $array[0] = $this->sn;
        $array[1] = $this->onu;
        return $array;
    }

    function setComando($id){

        $sql = "SELECT comando, tipo FROM isp.int_olt_comandos WHERE id = $id";
        if($this->oCon->Query($sql)){
            if($this->oCon->NumFilas() > 0){
                $comando = $this->oCon->f('comando');
                $tipo = $this->oCon->f('tipo');
            }
        }
        $this->oCon->Free();

        if($tipo == 'A'){ // no aplica descomponer variables
            $cmd = $comando;
        }elseif($tipo == 'P'){ // descomponer variables $puerto
            if(!empty($this->puerto)){
                $cmd = str_replace('$puerto', $this->puerto, $comando);
            }
        }elseif($tipo == 'O'){// descomponer varibales $onu
            if(!empty($this->onu) && !empty($this->sn)){
                $cmd = str_replace('$onu', $this->onu, $comando);
            }
        }

        $r = '';
        if(!empty($cmd)){
            $array = self::getDispositivo();
            $ip = $array[5];
            $port = $array[6];
            $hostname = $array[4];
            $user = $array[7];
            $clave = $array[8];
            $Telnet = $this->PHPTelnet($ip, $port, $hostname, $user, $clave);
            if($Telnet){
                $r = '';
                $conexion = $this->Connect();
                if($conexion === 0){
                    $this->DoCommand("terminal length 0", $r);
                    $this->DoCommand($cmd, $r);
                }else{
                    $r = 'Error:: '.$conexion;
                }
            }
            $this->Disconnect();
        }

        return $r;

    }

    function descomponerCmd($cmd){

    }


}