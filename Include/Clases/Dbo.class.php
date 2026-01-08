<?php
require_once('DB.php');

/* CONSTANTES DE OPCIONES DE CLASE */
define('CLS_OP_ERROR_DETENER', true); // Detiene la ejecucion del sistema si hay errores
define('CLS_OP_ERROR_VER', true); // Muestra los todos los errores
define('CLS_OP_ERROR_PROGRAMADOR', true); // Muestra los errores
define('CLS_OP_ERROR_TIPOMSG', 'txt'); // Valores => txt, js, html
define('CLS_OP_ERROR_AUDITORIA_LOG', false);
define('CLS_OP_ERROR_AUDITORIA_LOG_EXT', 'err');
define('CLS_OP_ERROR_AUDITORIA_MAIL', false);
define('CLS_OP_ERROR_AUDITORIA_MAIL_PARA', 'danny.rosero@gmail.com');
define('CLS_OP_ERROR_AUDITORIA_MAIL_ASUNTO', 'Error del sistema');
define('CLS_OP_ERROR_AUDITORIA_MAIL_TIPO', 'html');
define('CLS_OP_AUDITORIA_LOG', false);    // Crea un archivo de txt con todas las transacciones realizadas a la base
define('CLS_OP_AUDITORIA_LOG_EXT', 'las');

class Dbo
{
    /* VARIABLES DE MANEJO DE LA BASE DE TADOS */
    var $DSN = '';
    var $Opciones = '';
    var $Db = '';
    var $error = false;
    var $ResultadoQuery = '';
    var $ResTmp = '';
    var $ResTmpOBJ = '';
    var $ResRow = '';
    var $Fila;


    /********************************************
     * @ Dbo
     * + Constructor de la clase Dbo, si se pasaron
     * + parametros al ser creada se conecta a la
     * + base automaticamente de la
     ********************************************/


    function __construct($db_informacion = NULL)
    {
        if (is_null($db_informacion))
            return false;
        else {
            if ($this->GeneraDsn($db_informacion)) {
                $this->ConectarPrivado();
            }
        }
    }

    /********************************************
     * @ GeneraDsn -> (true/false)
     * + Genera la cadena de conexion con los datos
     * + pasados en el constructor de la clase en el
     * + siguiente formato:
     * +
     * + TipoBd://Usuario:Clave@Servidor/BaseDatos
     * +
     ********************************************/


    function GeneraDsn($db_informacion)
    {
        $Ok = false;
        $msg_error = ':: Errores Parametros de Conexion::<br>';
        if (is_array($db_informacion)) {
            if (($db_informacion['TipoBD'] == '')) {
                $msg_error .= '[ TipoBD ] No puede ser cadena vacia <br>';
                $this->error = true;
            }
            if ($db_informacion['Usuario'] == '') {
                $msg_error .= '[ Usuario ] No puede ser cadena vacia <br>';
                $this->error = true;
            }
            if ($db_informacion['Servidor'] == '' && (substr($db_informacion['TipoBD'], 0, 4) != 'odbc')) {
                $msg_error .= '[ Servidor ] No puede ser cadena vacia <br>';
                $this->error = true;
            }
            if ($db_informacion['Base'] == '') {
                $msg_error .= '[ Base ] No puede ser cadena vacia <br>';
                $this->error = true;
            }
            if ($this->error) $this->Error($msg_error);
            else {
                $this->DSN = $db_informacion['TipoBD']
                    . '://' .
                    $db_informacion['Usuario']
                    . ':' .
                    $db_informacion['Clave']
                    . '@' .
                    $db_informacion['Servidor']
                    . '/' .
                    $db_informacion['Base'];
                $Ok = true;
            }

        } else {
            if (is_string($db_informacion))
                if (!($db_informacion == '') || !is_null($db_informacion)) {
                    $this->DSN = $db_informacion;
                    $Ok = true;
                }
        }
        return $Ok;
    }

    /********************************************
     * @ ConectarPrivado -> (true/false)
     * + Realiza la conexion a la base de datos
     * + es llamada desde el constructor o de la
     * + funcion Conectar
     * +
     ********************************************/
    function ConectarPrivado()
    {
        $Ok = false;

        $conexion_php = new DB();
        if ($conexion_php->db_conexion) {
            $this->Db = $conexion_php->conexion();
            $Ok = true;
        } else {
            $Ok = false;
            $this->Error($conexion_php->db_error, true);
        }


//		$this->Db = DB::connect($this->DSN, $this->Opciones);
//		if (DB::isError($this->Db)) {
//			$this->Error($this->Db, true);
//			$Ok = false;
//		} else {
//			$Ok = true;
//			$this->SetFormatoResultado();
//		}
        return $Ok;
    }

    /********************************************
     * @ Conectar -> (true/false)
     * + Realiza la conexion a la base de datos
     * + es llamada desde fuera es de acceso publico
     * + se le pasa la informacion de la Base
     * +
     ********************************************/
    function Conectar($db_informacion = NULL)
    {
        if (is_null($db_informacion))
            if ($this->GeneraDsn($this->DSN))
                return $this->ConectarPrivado();
            else
                if ($this->GeneraDsn($db_informacion))
                    return $this->ConectarPrivado();
    }

    /********************************************
     * @ CambiarConexion -> (true/false)
     * + Nos permite cambiar la conexion a una
     * + diferente base de datos con solo enviarle
     * + el DSN correspondiente.
     * +
     ********************************************/
    function CambiarConexion($db_informacion = NULL)
    {
        if (is_null($db_informacion)) {
            $this->Desconectar();
            if ($this->GeneraDsn($this->DSN))
                return $this->ConectarPrivado();
        } else {
            $this->Desconectar();
            if ($this->GeneraDsn($db_informacion))
                return $this->ConectarPrivado();
        }
    }

    /********************************************
     * @ Desconectar -> (true/false)
     * + Raliza la desconexion segura de la Base
     * +
     ********************************************/
    function Desconectar()
    {
        $Ok = false;
//        if (is_object($this->Db))
//            $Ok = $this->Db->close();
        return $Ok;
    }

    /********************************************
     * @ SetFormatoResultado
     * + Setea el formato en el qu queremos que nos
     * + devuelva el resultado del realizar un Query
     * + se le puede pasar 3 opciones
     * + ORDENADO, ASOCIADO (Default), OBJETO
     * +
     ********************************************/
    function SetFormatoResultado($formato = 'ASOCIADO')
    {
//        switch ($formato) {
//            case 'ORDENADO':
//                $formato = DB_FETCHMODE_ORDERED;
//                break;
//            case 'ASOCIADO':
//                $formato = DB_FETCHMODE_ASSOC;
//                break;
//            case 'OBJETO':
//                $formato = DB_FETCHMODE_OBJECT;
//                break;
//            default:
//                $formato = DB_FETCHMODE_ASSOC;
//        }
//
//        $this->Db->setFetchMode($formato);
    }

    /********************************************
     * @ Query -> (true/false)
     * + Recibe el Sql lo prepara y lo ejecuta, se
     * + le puede puede ocupar de dos formas como
     * + se muestra a continuacion:
     * +
     * + $DbCon => Objeto de conexion valido
     * + (1)
     * + $Sql = 'SELECT * FROM USUARIO';
     * + $DbCon->Query($Sql);
     * + (2)
     * + $Sql = 'SELECT *
     * +         FROM USUARIO
     * +         WHERE USERNAME = ? AND PASSWORD = ?';
     * + $Data = array('danny','danny2007')
     * + $DbCon->Query($Sql, $Data);
     * +
     * +
     ********************************************/

    function Query($sql = '', $datos = NULL)
    {

        $Ok = false;
        if (is_null($datos)) {
            if ($sql == '')
                $this->Error('[Sql] La cadena esta vacia no existe operacion para ejecutar en el Query');
            else {
                    //$this->Db->prepare(utf8_encode_jire($sql));
                    $exec_query = $this->Db->query($sql);
                    $error_obj = $this->Db->errorInfo();
                    if(isset($error_obj[2])){
                        $error_sql = $error_obj[2];
                        $this->Error( 'SQL: '. $sql.', '.$error_sql, false);
                    }else{
                        $this->ResTmpOBJ = $exec_query;
                        $this->ResRow = $exec_query->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_LAST);
                        $this->ResTmp = $this->ResRow;
                        $Ok = true;
                        $this->Fila = $this->NumFilas();
                    }
            }
        } else {
            if ($sql == '')
                $this->Error('[Sql] La cadena esta vacia no existe operacion para ejecutar en el Query');
            else {
                $sql_exec = $this->replaceSqlarray($sql, $datos);
                $this->Db->prepare(($sql_exec));
                $exec_query = $this->Db->query(($sql_exec));
                $error_obj = $this->Db->errorInfo();
                if(isset($error_obj[2])){
                    $error_sql = $error_obj[2];
                    $this->Error( 'SQL: '. $sql_exec.', '.$error_sql, false);
                }else{
                    $this->ResTmpOBJ = $exec_query;
                    $this->ResRow = $exec_query->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_LAST);
                    $this->ResTmp = $this->ResRow;
                    $Ok = true;
                    $this->Fila = $this->NumFilas();
                }
            }
        }

        return $Ok;
    }

    function replaceSqlarray($query, $values)
    {
        foreach (array_fill(0, count($values), '?') as $key => $wildcard) {
            $query = substr_replace($query, "'" . $values[$key] . "'", strpos($query, $wildcard), strlen($wildcard));
        }
        return $query;
    }

    /********************************************
     * @ Query -> (true/false)
     * + Recibe el Sql lo prepara y lo ejecuta, se
     * + le puede puede ocupar de dos formas como
     * + se muestra a continuacion:
     * +
     * + $DbCon => Objeto de conexion valido
     * + (1)
     * + $Sql = 'SELECT * FROM USUARIO';
     * + $DbCon->Query($Sql);
     * + (2)
     * + $Sql = 'SELECT *
     * +         FROM USUARIO
     * +         WHERE USERNAME = ? AND PASSWORD = ?';
     * + $Data = array('danny','danny2007')
     * + $DbCon->Query($Sql, $Data);
     * +
     * +
     ********************************************/

    function QueryT($sql = '', $datos = NULL)
    {

        $Ok = false;
        if (is_null($datos)) {
            if (strlen($sql) == 0)
                $this->Error('[Sql] La cadena esta vacia no existe operacion para ejecutar en el Query');
            else {

                //$this->Db->prepare(($sql));
                $exec_query = $this->Db->query(($sql));
                $error_obj = $this->Db->errorInfo();
                if(isset($error_obj[2])){
                    $error_sql = $error_obj[2];
                    $this->Error( 'SQL: '. $sql.', '.$error_sql, false);
                }else{
                    $this->ResTmpOBJ = $exec_query;
                    $this->ResRow = $exec_query->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_LAST);
                    $this->ResTmp = $this->ResRow;
                    $Ok = true;
                    $this->Fila = $this->NumFilas();
                }
            }
        } else {
            if (strlen($sql) == 0)
                $this->Error('[Sql] La cadena esta vacia no existe operacion para ejecutar en el Query');
            else {
                $sql_exec = $this->replaceSqlarray($sql, $datos);
                $this->Db->prepare(($sql_exec));
                $exec_query = $this->Db->query(($sql_exec));
                $error_obj = $this->Db->errorInfo();
                if(isset($error_obj[2])){
                    $error_sql = $error_obj[2];
                    $this->Error(  'SQL: '. $sql_exec.', '.$error_sql, false);
                }else{
                    $this->ResTmpOBJ = $exec_query;
                    $this->ResRow = $exec_query->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_LAST);
                    $this->ResTmp = $this->ResRow;
                    $Ok = true;
                    $this->Fila = $this->NumFilas();
                }
            }
        }

        return $Ok;
    }


    /********************************************
     * @ QuerySacaTodo -> (false/Array)
     * + Recibe el Sql lo prepara y lo ejecuta y
     * + devuelve un arreglo on todo el ResultSet,
     * + se le puede puede ocupar de dos formas como
     * + se muestra a continuacion:
     * +
     * + $DbCon => Objeto de conexion valido
     * + (1)
     * + $Sql = 'SELECT * FROM USUARIO';
     * + $DbCon->QuerySacaTodo($Sql);
     * + (2)
     * + $Sql = 'SELECT *
     * +         FROM USUARIO
     * +         WHERE USERNAME = ? AND PASSWORD = ?';
     * + $Data = array('danny','danny2007')
     * + $DbCon->QuerySacaTodo($Sql, $Data);
     * +
     * +
     ********************************************/
    function QuerySacaTodo($sql = '', $datos = NULL)
    {
        $Ok = false;
        if (is_null($datos)) {
            if ($sql == '')
                $this->Error('[Sql] La cadena esta vacia no existe operacion para ejecutar en el Query');
            else {
                $Ok = $this->Db->getAll($sql);
                if (DB::isError($Ok)) {
                    $this->Error($Ok, true);
                    $Ok = false;
                }
            }
        } else {
            if ($sql == '')
                $this->Error('[Sql] La cadena esta vacia no existe operacion para ejecutar en el Query');
            else {
                $Ok = $this->Db->getAll($sql, $datos);
                if (DB::isError($Ok)) {
                    $this->Error($Ok, true);
                    $Ok = false;
                }
            }
        }
        return $Ok;
    }

    /********************************************
     * @ SiguienteRegistro -> (true/false)
     * + Recorre registro por registro del resultado
     * + obtenido del Query se lo puede utilizar de
     * +`la siguiente manera:
     * +
     * + $Db => Objeto de conexion valido
     * + $Sql => Cadena Sql valida
     * + $Db->Query($Sql);
     * + do{
     * +   $Db->f([Campo]);
     * + }while($Db->SiguienteRegistro());
     * +
     ********************************************/
    function SiguienteRegistro()
    {
        $Ok = false;
        if (is_object($this->Db))
            if ($this->ResTmp != '') {
                $this->Fila -= 1;
                $this->ResRow = $this->ResTmpOBJ->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_PRIOR);
                if ($this->Fila <= 0) {
                    $Ok = false;
                } else {
                    $Ok = true;
                }
            }
        return $Ok;
    }

    /********************************************
     * @ f -> (String)
     * + Devuelve el valor del campo del registro
     * + actual de la consulta actual, se lo usa
     * + en conjunto con las funciones Query y
     * + SiguienteRegistro
     * +
     ********************************************/
    function f($campo = '', $limpiar=true)
    {
        $Ok = '';
        if (is_object($this->Db)) {
            if ($this->ResRow != '' && $limpiar == true) {
                $Ok = (isset($this->ResRow[$campo]) ? limpiar_string($this->ResRow[$campo]) : '');
            }else if($this->ResRow != '' && $limpiar == false){
                $Ok = (isset($this->ResRow[$campo]) ? $this->ResRow[$campo] : '');
            }
        }

        return $Ok;
    }

    /********************************************
     * @ Free -> (true/false)
     * + Libera el resulset en memoria de una consulta
     * +
     ********************************************/
    function Free()
    {
        $Ok = false;
        if (is_object($this->Db))
            if ($this->ResTmp != '')
                $Ok = $this->ResTmpOBJ->closeCursor();
        return $Ok;
    }

    /********************************************
     * @ NumFilas -> (false, Integer)
     * + Retorna el numero de filas obtenidas en una
     * + consulta, no es compatible con todos los
     * + metodos de coneccion y motores de base de
     * + datos.
     * +
     ********************************************/
    function NumFilas()
    {
        $Ok = false;
        if (is_object($this->Db))
            if ($this->ResTmp != '')
                $Ok = (int)$this->ResTmpOBJ->rowCount();
        return $Ok;
    }

    /********************************************
     * @ NumCols -> (false, Integer)
     * + Retorna el numero de columnas obtenidas en
     * + una consulta, no es compatible con todos los
     * + metodos de coneccion y motores de base de
     * + datos.
     * +
     ********************************************/
    function NumCols()
    {
        $Ok = false;
        if (is_object($this->Db))
            if ($this->ResTmp != '')
                $Ok = $this->ResTmp->numCols();
        return $Ok;
    }

    /********************************************
     * @ FilasAfectadas -> (false, Integer)
     * + Retorna el numero de filas afectadas en una
     * + operacion INSERT, UPDATE, DELETE, no es
     * + compatible con todos los metodos de coneccion
     * + y motores de base de datos.
     * +
     ********************************************/
    function FilasAfectadas()
    {
        $Ok = false;
        if (is_object($this->Db))
            $Ok = $this->Db->affectedRows();
        return $Ok;
    }

    /********************************************
     * @ InfoTabla -> (false/array)
     * + Retorna la Informacion de la tabla que se
     * + le solicita, esto lo hace en un arreglo.
     * +
     ********************************************/
    function InfoTabla($tabla = '')
    {
        $Ok = false;
        if (is_object($this->Db) && ($tabla != ''))
            $Ok = $this->Db->tableInfo($tabla);
        return $Ok;
    }

    /********************************************
     * @ Error
     * + Esta funcion despliaga los mensajes de error
     * + sucitados en la clase durante la ejecucion
     * + del sistema. Puede detener el sistema.
     * +
     ********************************************/
//	function Error($msg = '', $sistema = false, $titulo = 'Error Base de Datos'){
//		$tituloDebug = 'Error Base de Datos - Programador';
//		if (CLS_OP_ERROR_VER){
//			if ($sistema){
//				$this->alerta($msg->getMessage() .'  Code: '. $msg->getCode(), '.:: '.$titulo.' ::.', 0,CLS_OP_ERROR_TIPOMSG);
//				if (CLS_OP_ERROR_PROGRAMADOR)
//					$this->alerta($msg->getDebugInfo(), '.:: '.$tituloDebug.' ::.', 0, CLS_OP_ERROR_TIPOMSG);
//				}else{
//					$this->alerta($msg, '.:: '.$titulo.' ::.', 0,CLS_OP_ERROR_TIPOMSG);
//				}
//		}
//		if(CLS_OP_ERROR_DETENER){
//			$this->alerta('.::  SISTEMA DETENIDO ::.', '.:: Alerta ::.', 2, CLS_OP_ERROR_TIPOMSG);
//			exit;
//		}
//	}


    function Error($msg = '', $sistema = false, $titulo = 'Error Base de Datos')
    {
        $tituloDebug = 'Error Base de Datos - Programador';
        $mensaje_final_error = '';
        $mensaje_final_debug = '';
        $codigo = '';

        if (CLS_OP_ERROR_VER) {
            $mensaje_final_error .= $msg;
        }

        if ($mensaje_final_error != '') {
            error_log($mensaje_final_error, 0);
        }

        if (CLS_OP_ERROR_DETENER) {
            //$this->alerta('.::  SISTEMA DETENIDO ::.', '.:: Alerta ::.', 2, CLS_OP_ERROR_TIPOMSG);
            //exit;
            throw new Exception($mensaje_final_error);
        }
    }


    /********************************************
     * @ Alerta
     * + Muestra en pantalla mensajes de:
     * + Error => 0
     * + Ok => 1
     * + Advertencia => 2
     * + Interrogacion => 3
     * + Informacion => 4
     * + en formatos txt, html o js de acuerdo a como
     * + se la setee, trabaja en conjunto con la
     * = funcion error.
     * +
     ********************************************/
    function Alerta($mensaje = 'Funcion Alerta([Mensaje], [Titulo], [Opcion])', $tituloMsg = 'No se pasaron parametros', $tipo = 2, $formato = 'hmtl', $bReturn = false)
    {
        $path = "imagenes/iconos/";
        switch ($tipo) {
            case 0:
                $icono = $path . "ico_error.png";
                $titulo = " ";
                $color = "CC0000";
                $colorTitulo = "FFFFFF";
                $colorMsg = "CC0000";
                break;
            case 1:
                $icono = $path . "ico_ok.png";
                $titulo = " ";
                $color = "009900";
                $colorTitulo = "FFFFFF";
                $colorMsg = "009900";
                break;
            case 2:
                $icono = $path . "ico_advertencia.png";
                $titulo = " ";
                $color = "FFCC33";
                $colorTitulo = "FFFFFF";
                $colorMsg = "FF9900";
                break;
            case 3:
                $icono = $path . "ico_atencion.png";
                $titulo = " ";
                $color = "FF9900";
                $colorTitulo = "FFFFFF";
                $colorMsg = "FF9900";
                break;
            case 4:
                $icono = $path . "ico_info.png";
                $titulo = " ";
                $color = "006699";
                $colorTitulo = "FFFFFF";
                $colorMsg = "006699";
                break;
        }
        $titulo .= $tituloMsg;
        switch ($formato) {
            case 'html':
                $alerta = '<br>
				<table width="50%" border="1" align="center" cellpadding="2" cellspacing="0" bordercolor="#' . $color . '">
				  <tr>
					<td width="375" bgcolor="#' . $color . '" align="left">
					 <img src="' . $icono . '" align="absmiddle">
					 <font font size="2" color="#' . $colorTitulo . '"> ' . $titulo . '</font>
					</td>
				  </tr>
				  <tr>
					<td><div align="center"><font size="2" color="#' . $colorMsg . '">' . $mensaje . '</font></div></td>
				  </tr>
				</table><br>';
                break;
            case 'txt':
                $alerta = '
					<br>
					<div align="center">
						<font size="2" color="#' . $colorMsg . '">' . $titulo . '<br>' . $mensaje . '</font>
					</div>
					<br>';
                break;
            case 'js':
                $alerta = '<script>alert("' . $titulo . '  ' . $mensaje . '")</script>';
                break;
        }
        if ($bReturn) return $alerta;
        else echo($alerta);
    }

}
?>