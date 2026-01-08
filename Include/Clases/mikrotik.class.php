<?php

/*
  Mikrotik Class
  Autor: Leobannys Urdaneta - 2020
  Descripcion: Clase para gestión y administración de Mikrotik
  Version 1.20
 */
require_once(path(DIR_INCLUDE) . 'comun.lib.php');
require_once(path(DIR_INCLUDE) . 'Clases/routeros_api.class.php');


class Mk
{

    var $oCon;
    var $id_dispositivo;
    var $idMarca;
    var $software;
    var $idPuerto;
    var $ip;
    var $port;
    var $hostname;
    var $user;
    var $clave;
    var $estado;
    var $cola_simple;
    var $pppoe;
    var $API;
    var $connect_string;
    var $cmd_consulta;
    var $var_consulta;
    var $cmd_registro;
    var $var_registro;
    var $cmd_actualiza;
    var $var_actualiza;
    var $cmd_corte;
    var $cmd_reconexion;
    var $cmd_eliminar;
    var $var_eliminar;

    function __construct($oCon, $id_dispositivo)
    {
        $this->oCon = $oCon;
        $this->id_dispositivo = $id_dispositivo;
        $this->connect();
    }

    private function error($msj)
    {
        throw new Exception($msj);
    }


    public function connect()
    {
        $sql = "SELECT
                int_dispositivos.id_marca,
                int_dispositivos.ip,
                int_dispositivos.hostname,
                int_dispositivos.`user`,
                int_dispositivos.user_password,
                int_dispositivos.software,
                int_dispositivos.port,
                int_dispositivos.estado,
                int_config_mikrotik.pppoe,
                int_config_mikrotik.queue_tree,
                int_config_mikrotik.queue_simple 
            FROM
                int_dispositivos
                INNER JOIN int_config_mikrotik ON int_dispositivos.id = int_config_mikrotik.id_dispositivo 
            WHERE
                int_dispositivos.id = $this->id_dispositivo";
        if ($this->oCon->Query($sql)) {
            if ($this->oCon->NumFilas() > 0) {
                $this->idMarca      = $this->oCon->f('id_marca');
                $this->software     = $this->oCon->f('software');
                $this->ip           = $this->oCon->f('ip');
                $this->port         = $this->oCon->f('port');
                $this->hostname     = $this->oCon->f('hostname');
                $this->user         = $this->oCon->f('user');
                $this->clave        = $this->oCon->f('user_password');
                $this->estado       = $this->oCon->f('estado');

                $this->cola_simple  = ($this->oCon->f('queue_simple') == 'S') ? true : false;
                $this->pppoe        = ($this->oCon->f('pppoe') == 'S') ? true : false;


                // $this->oCon->f('modelo');
                // $this->oCon->f('serial');
                // $this->oCon->f('config_password');
                // $this->oCon->f('latitud');
                // $this->oCon->f('longitud');

                $tipo_conf_tmp = '';
                if ($this->cola_simple) {
                    $tipo_conf_tmp = "  AND a.tipo_conf = 'CS'";
                }

                if ($this->pppoe) {
                    $tipo_conf_tmp = "  AND a.tipo_conf = 'PO'";
                }

                $sql = "SELECT a.metodo, a.comando, b.variables
                        FROM
                            int_olt_comandos a left join int_mikrotik_var b on a.id = b.id_comando
                        WHERE
                            a.id_marca = $this->idMarca
                            $tipo_conf_tmp
                        ORDER BY
                            a.metodo ASC";
                $this->cmd_corte      = "/ip/firewall/address-list/add";
                $this->cmd_reconexion = "/ip/firewall/address-list/remove";

                if ($this->oCon->Query($sql)) {
                    if ($this->oCon->NumFilas() > 0) {
                        do {
                            switch ($this->oCon->f('metodo')) {
                                case 'C':
                                    $this->cmd_consulta  = $this->oCon->f('comando');
                                    $this->var_consulta  = $this->oCon->f('variables');
                                    break;
                                
                                case 'R':
                                    $this->cmd_registro  = $this->oCon->f('comando');
                                    $this->var_registro  = $this->oCon->f('variables');
                                    break;
                                
                                case 'U':
                                    $this->cmd_actualiza = $this->oCon->f('comando');
                                    $this->var_actualiza = $this->oCon->f('variables');
                                    break;

                                case 'D':
                                    $this->cmd_eliminar  = $this->oCon->f('comando');
                                    $this->var_eliminar  = $this->oCon->f('variables');
                                    break;
                                
                                default:
                                    break;
                            }
                        } while ($this->oCon->SiguienteRegistro());
                    
                    }
                }

                $this->API = new RouterosAPI();
                if ($this->software > '6.45.1') {
                    $this->connect_string = $this->API->connect($this->ip, $this->user, $this->clave);
                } else {
                    $this->connect_string = $this->API->connect_old($this->ip, $this->user, $this->clave);
                }
            } else {
                $this->error('Dispositivo no encontrado..');
            }
        }
        $this->oCon->Free();
    }

    /**
     * Datos del dispositivo
     *
     * @return array                  Muestra la informacion del dispositivo
     */
    public function getDispositivo()
    {
        return array(
            $this->idMarca,
            $this->software,
            $this->ip,
            $this->port,
            $this->hostname,
            $this->user,
            $this->clave,
            $this->estado,
            $this->cola_simple,
            $this->pppoe,
            $this->cmd_consulta,
            $this->cmd_registro,
            $this->cmd_actualiza,
            $this->cmd_corte,
            $this->cmd_reconexion,
            $this->cmd_eliminar
        );
    }

    /**
     * Consulta al mikrotik
     *
     * @param string      $searchIp   La ip del cliente a consultar (dejar vacio para retornar todo)
     *
     * @return array                  Muestra la informacion de la consulta
     */
    public function consulta($searchIp = '')
    {
        if ($this->estado == 'A') {
            if ($this->connect_string) {
                try {
                    $vars = explode(',', $this->var_consulta);
                    $params = '';
                    if (!empty($searchIp)) {
                        $searchIp = ($this->cola_simple) ? $searchIp . "/32" : $searchIp;
                        $datos = array($searchIp);
                        foreach ($vars as $posicion => $valor) {
                            $params["?" . trim($valor)] = $datos[$posicion];
                        }
                    }

                    $READ = $this->API->comm($this->cmd_consulta, $params);
                    $ARRAY = $this->API->parseResponse($READ);

                    if (count($ARRAY) > 0) {
                        return $ARRAY;
                    } else {
                        $this->error('No hay Datos...');
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->API->disconnect();
            } else {
                $this->error('No fue posible conectar al Mikrotik..');
            }
        } else {
            $this->error('El dispositivo no se encuentra activo..');
        }
    }

    /**
     * Registro de cliente
     *
     * @param string      $nombre       nombre del cliente a registrar
     * @param string      $ip           ip del cliente a registrar
     * @param string      $num_ident    numero de identificacion del cliente
     * @param string      $max_limit    velocidad de carga y descargar (1M/1M, siendo carga/descarga) 
     *
     * @return array                   Retorna id del cliente en el mikrotik
     */
    public function registro($nombre, $ip, $num_ident, $max_limit)
    {
        if ($this->estado == 'A') {
            if ($this->connect_string) {
                try {
                    $vars = explode(',', $this->var_registro);
                    $params = '';
                    if ($this->cola_simple) {
                        $datos = array($nombre, $ip, "{$num_ident}-{$nombre}", $max_limit);
                    } else if ($this->pppoe) {
                        $datos = array($nombre, $ip, md5($num_ident . $nombre), $this->getProfile($max_limit), 'pppoe', "{$num_ident}-{$nombre}");
                    }
                    foreach ($vars as $posicion => $valor) {
                        $params[trim($valor)] = $datos[$posicion];
                    }
                    $add = $this->API->comm($this->cmd_registro, $params);

                    $registro = $this->API->parseResponse($add);

                    if (!empty($registro) > 0) {
                        return array('.id' => $registro);
                    } else {
                        $this->error('no se realizo el registro');
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->API->disconnect();
            } else {
                $this->error('No fue posible conectar al Mikrotik..');
            }
        } else {
            $this->error('El dispositivo no se encuentra activo..');
        }
    }


    /**
     * actualizacion velocidad de cliente
     *
     * @param string      $id_mikrotik      id del cliente en el mikrotik
     * @param string      $max_limit        velocidad de carga y descarga a actualizar (1M/1M, siendo carga/descarga) 
     *
     * @return array                        Retorna id del cliente en el mikrotik
     */
    public function actualizar($id_mikrotik, $max_limit)
    {
        if ($this->estado == 'A') {
            if ($this->connect_string) {
                try {
                    $vars = explode(',', $this->var_actualiza);
                    $params = '';
                    if ($this->cola_simple) {
                        $datos = array($id_mikrotik, $max_limit);
                    } else if ($this->pppoe) {
                        $datos = array($id_mikrotik, $this->getProfile($max_limit));
                    }
                    foreach ($vars as $posicion => $valor) {
                        $params[trim($valor)] = $datos[$posicion];
                        // if ($posicion == $valor) {
                        //     $params[trim($valor)] = $id_mikrotik;
                        // } else {
                        //     $params[trim($valor)] = $max_limit;
                        // }
                    }
                    $update = $this->API->comm($this->cmd_actualiza, $params);
                    $actualizacion = $this->API->parseResponse($update);
                    return array('.id' => $id_mikrotik);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->API->disconnect();
            } else {
                $this->error('No fue posible conectar al Mikrotik..');
            }
        } else {
            $this->error('El dispositivo no se encuentra activo..');
        }
    }


    /**
     * eliminar cliente
     *
     * @param string      $id_mikrotik      id del cliente en el mikrotik
     *
     * @return array                        Retorna mensaje de eliminacion
     */
    public function eliminar($id_mikrotik)
    {
        if ($this->estado == 'A') {
            if ($this->connect_string) {
                try {
                    $vars = explode(',', $this->var_eliminar);
                    $params = '';
                    $datos = array($id_mikrotik);
                    foreach ($vars as $posicion => $valor) {
                        $params[trim($valor)] = $datos[$posicion];
                    }
                    $delete = $this->API->comm($this->cmd_eliminar, $params);
                    $eliminar = $this->API->parseResponse($delete);
                    return array("msg" => "deleted successfully");
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->API->disconnect();
            } else {
                $this->error('No fue posible conectar al Mikrotik..');
            }
        } else {
            $this->error('El dispositivo no se encuentra activo..');
        }
    }

    /**
     * corte de cliente
     *
     * @param string      $nombre       nombre del cliente a cortar
     * @param string      $ip           ip del cliente a cortar
     * @param string      $grupo        grupo de corte
     *
     * @return array                    Retorna id del registro de corte en el mikrotik
     */
    function corte($nombre, $ip, $grupo)
    {

        if ($this->estado == 'A') {
            $params = '';
            if ($this->connect_string) {
                try {
                    $corte = $this->API->comm("/ip/firewall/address-list/add", array(
                        'list'             => $grupo,
                        'address'          => $ip,
                        'comment'          => $nombre,
                    ));

                    $id_corte = $this->API->parseResponse($corte);
                    return array('.id' => $id_corte);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->API->disconnect();
            } else {
                $this->error('No fue posible conectar al Mikrotik..');
            }
        } else {
            $this->error('El dispositivo no se encuentra activo..');
        }
    }


    /**
     * reconexion de cliente
     *
     * @param string      $ip           ip del cliente a cortar
     * @param string      $grupo        grupo de corte
     *
     * @return array                    Retorna la ip del cliente reconectado
     */
    function reconexion($ip, $grupo)
    {

        if ($this->estado == 'A') {
            if ($this->connect_string) {
                try {
                    $reconexion = $this->API->comm("/ip/firewall/address-list/print", array(
                        '?list'      => $grupo,
                        '?address'   => $ip
                    ));
                    $address_list = $this->API->parseResponse($reconexion);

                    if (count($address_list) > 0) {
                        $this->API->comm("/ip/firewall/address-list/remove", array(
                            '.id'  => $address_list[0]['.id']
                        ));

                        return array('ip' => $ip);
                    } else {
                        $this->error('Cliente no encontrado, no ha sido posible ejecutar la reconexion');
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->API->disconnect();
            } else {
                $this->error('No fue posible conectar al Mikrotik..');
            }
        } else {
            $this->error('El dispositivo no se encuentra activo..');
        }
    }

    /**
     * obtener perfil
     *
     * @param string      $rate_limit       velocidad del perfil a consultar
     *
     * @return string                       Retorna el nombre de perfil consultado
     */
    private function getProfile($rate_limit)
    {
        if ($this->estado == 'A') {
            if ($this->connect_string) {
                try {
                    $profile = $this->API->comm("/ppp/profile/print", array(
                        "?rate-limit" => $rate_limit
                    ));
                    $profile_data = $this->API->parseResponse($profile);

                    if (count($profile_data) > 0) {
                        return $profile_data[0]['name'];
                    } else {
                        $this->error('Perfil no encontrado...');
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->API->disconnect();
            } else {
                $this->error('No fue posible conectar al Mikrotik..');
            }
        } else {
            $this->error('El dispositivo no se encuentra activo..');
        }
    }
}
