<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class Cti
{

	var $socketCAS;
	var $infoCAS;
	var $subId;
	var $userInfo;
	var $oCon;
	var $oReturn;
	var $accionPrevia;
	var $idLog;
	var $msj;
	var $tipo;
	var $id_peticion;

	function Cti($oCon, $oReturn, $tipo = 0)
	{

		unset($infoCAS);
		$infoCAS['empresa'] = $_SESSION['U_EMPRESA'];
		$infoCAS['sucursal'] = $_SESSION['U_SUCURSAL'];
		$infoCAS['userConected'] = $_SESSION['U_NOMBRECOMPLETO'];

		$sql = "select d.id, d.modelo, d.software, d.serial, d.hostname, d.ip, d.port
				from int_sistemas s, int_marcas_dispositivos m , int_dispositivos d
				where d.estado = 'A' and 
				m.id_sistema = s.id and
				d.id_marca = m.id and
				d.id_empresa = " . $infoCAS['empresa'] . " and
				s.sistema = 'TELECABLE' and
				m.marca =  'COMPUNICATE'";

		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				$infoCAS['id'] = $oCon->f('id');
				$infoCAS['modelo'] = $oCon->f('modelo');
				$infoCAS['version'] = $oCon->f('software');
				$infoCAS['nombre'] = $oCon->f('serial');
				$infoCAS['sms'] = $oCon->f('hostname');
				$infoCAS['ip'] = $oCon->f('ip');
				$infoCAS['port'] = $oCon->f('port');
			}
		}
		$oCon->Free();

		$userInfo = "Comunicando con la tarjeta de red del equipo local";
		$this->userInfo = $userInfo;
		$subId = 1;
		$sql = "INSERT INTO int_log_cti_cas (sub_id,id_empresa, id_sucursal, usuario, ejecucion)
	           VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "' ,'$userInfo')";
		$oCon->QueryT($sql);
		$sql = "SELECT max(id) as id from int_log_cti_cas";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				$idLog = $oCon->f('id');
				$this->idLog = $idLog;
			}
		}
		$oCon->Free();

		$this->socketCAS = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
		$socketCAS = $this->socketCAS;

		if ($socketCAS === false) {
			$userInfo = socket_strerror(socket_last_error($socketCAS));
			$this->userInfo = $userInfo;
			$sql = "UPDATE int_log_cti_cas set estado = 'ER', info = '$userInfo' where sub_id = $subId and id = $idLog";
			$oCon->QueryT($sql);
			$this->excepcionFunc();
		} else {
			$sql = " UPDATE int_log_cti_cas set estado = 'OK' where sub_id = $subId and id = $idLog";
			$oCon->QueryT($sql);
		}

		$this->infoCAS = $infoCAS;
		$this->subId = $subId;
		$this->oCon = $oCon;
		$this->oReturn = $oReturn;
		$this->tipo = $tipo;

		return $oReturn;
	}

	function conexion()
	{

		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

		//register_shutdown_function(array($this,'excepcionFunc'));

		$subId = $this->subId;
		$socketCAS = $this->socketCAS;
		$infoCAS = $this->infoCAS;
		$oCon =	$this->oCon;
		$oReturn = $this->oReturn;

		if ($socketCAS === false || $socketCAS == null) {

			$userInfo = "No se realizó una interaccion previa con la tarjeta de red del equipo local";
			$this->userInfo = $userInfo;
			$$this->excepcionFunc();
		} else {

			//Identificación de la version del CAS
			if ($infoCAS['version'] == "4.0") {
				$version = chr(18);
				//$version = chr(19);
			} else {
				$version = chr(16);
			}

			if ($socketCAS !== false) {

				$subId++;
				$this->subId = $subId;
				$userInfo = "Conexion con el servidor CAS";
				$this->userInfo = $userInfo;
				$sql = "INSERT INTO int_log_cti_cas (sub_id,id_empresa, id_sucursal, usuario, ejecucion)
					   VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "' ,'$userInfo')";
				$oCon->QueryT($sql);
				$sql = "SELECT max(id) as id from int_log_cti_cas";
				if ($oCon->Query($sql)) {
					if ($oCon->NumFilas() > 0) {
						$idLog = $oCon->f('id');
						$this->idLog = $idLog;
					}
				}
				$oCon->Free();
				$infoCAS['id_conexion'] = $idLog;
				$sql = 'UPDATE int_log_cti_cas SET id_conexion = ' . $infoCAS['id_conexion'] . ' where id = ' . $infoCAS['id_conexion'] . '';
				$oCon->QueryT($sql);

				set_error_handler(array($this, 'errorHandler'));
				$connect = socket_connect($socketCAS, $infoCAS['ip'], $infoCAS['port']);
				restore_error_handler();

				if ($connect === true) {

					//Formato del protocolo CTI
					$header = $version; //Version del CAS (1 byte)
					$header .= chr(0) . chr($infoCAS['sms']); //SMS Registrado (2 bytes)
					$transaNo = $this->separarDec($subId, 4);
					$header .= chr($transaNo[0]) . chr($transaNo[1]) . chr($transaNo[2]) . chr($transaNo[3]); //Número de transacción (4 bytes)
					$stmp = getdate();
					$year = $this->separarDec($stmp["year"], 2);
					$header .= chr($year[0]) . chr($year[1]) . chr($stmp["mon"]) . chr($stmp["mday"]) . chr($stmp["hours"]) . chr($stmp["minutes"]) . chr($stmp["seconds"]); //Timestamp (4 bytes)
					$command = 1; //Comando de conexión
					$header .= chr($command); //Comando (1 byte)
					$header .= chr(0) . chr(0); //Longitud del comando(2 bytes)
					$verb = ''; //No tiene verb
					$writing = $header . $verb;

					socket_write($socketCAS, $writing, strlen($writing));
					$reading = socket_read($socketCAS, 1024);

					$readingHex = bin2hex($reading);
					$writingHex = bin2hex($writing);
					//$oReturn->alert($writingHex.'--'.$readingHex);

					$respuesta = substr($readingHex, 28, strlen($readingHex) - 28);

					if ($respuesta == '020000') {

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', comando = '$writingHex' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					} else {

						$inicio = strpos($readingHex, '00ff') + 4;
						$longitud = (int) substr($readingHex, $inicio, 4);
						$respuesta = substr($readingHex, $inicio + 4, $longitud * 2);
						$respuesta = (int) hexdec($respuesta);

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}

					//***VERIFICAR NO DESCONEXION DEL CAS***//
					/**/
				}
			}
		}
		$this->subId = $subId;
		$this->infoCAS = $infoCAS;
		$this->socketCAS = $socketCAS;

		return $oReturn;
	}

	function servicios($idActividad, $tarjetas, $verb = null, $id_peticion = 0)
	{

		//El id de la actividad empieza en uno ascendente segun el orden en la tabla
		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

		$oReturn = $this->oReturn;
		$oCon = $this->oCon;
		$socketCAS = $this->socketCAS;
		$infoCAS = $this->infoCAS;
		$subId = $this->subId;
		$accionPrevia = $this->accionPrevia;
		$this->msj = false;

		if ($accionPrevia === false && !empty($accionPrevia)) {
			$this->excepcionFunc();
		}

		if (empty($socketCAS)) {
			$userInfo = "No se realizo previamente una conexion correcta con el CAS";
			$this->userInfo = $userInfo;
			$this->excepcionFunc();
		}

		//register_shutdown_function(array($this,'excepcionFunc'));

		$sql = "SELECT p.id, t.id_cas from int_perfiles_cti t, int_paquetes p, 
				int_paquetes_perfiles e  
				WHERE e.id_paquete = p.id AND 
				t.id = e.id_perfil AND 
				t.estado = 'A' 
				GROUP BY t.id_cas";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				do {
					$paquetesCti[$oCon->f('id')] = $oCon->f('id_cas');
				} while ($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();

		$sql = "SET @id = ( SELECT MIN(a.id)-1
				FROM int_actividades a, int_dispositivos d
				WHERE a.id_dispositivo = d.id AND a.id_dispositivo = " . $infoCAS['id'] . ");";
		$oCon->QueryT($sql);
		$sql = "SELECT a.id-@id AS id, a.id AS id_tabla, a.actividad, a.info
				FROM int_actividades a, int_dispositivos d
				WHERE a.id_dispositivo = d.id AND a.id_dispositivo = " . $infoCAS['id'] . " AND a.estado = 'A' AND a.id-@id = " . $idActividad . "";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				$idActividad = $oCon->f('id');
				$actividad = $oCon->f('actividad');
				$idTabla = $oCon->f('id_tabla');
				$actividadCti = (int) $oCon->f('info');
			}
		}
		$oCon->Free();

		$sql = "SELECT c.id_comando, c.comando, c.descripcion, c.longitud_bytes, c.observaciones, a.obligatorio  
				FROM int_act_cmd_cti_cas a, int_comandos_cti_cas c, int_actividades t 
				WHERE c.id = a.id_comando AND t.id = a.id_actividad AND c.estado = 'A' AND a.estado = 'A' and t.id = $idTabla";

		$i = 0;
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				do {
					$comandoCti[$i] = $oCon->f('id_comando');
					$nombreComando[$i] = $oCon->f('comando');
					$descripcion[$i] = $oCon->f('descripcion');
					$longitudComando[$i] = $oCon->f('longitud_bytes');
					$infoAdd[$i] = $oCon->f('observaciones');
					$obliga[$i] = $oCon->f('obligatorio');
					$i++;
				} while ($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();

		//Identificación de la version del CAS
		if ($infoCAS['version'] == "4.0") {
			$version = chr(18);
		} else {
			$version = chr(16);
		}

		$sql = '';

		//Formato del protocolo CTI
		$header = $version; //Version del CAS (1 byte)
		$header .= chr(0) . chr($infoCAS['sms']); //SMS Registrado (2 bytes)
		$msj = 3;
		$transaNo = $this->separarDec($msj, 4);
		$header .= chr($transaNo[0]) . chr($transaNo[1]) . chr($transaNo[2]) . chr($transaNo[3]); //Número de transacción (4 bytes)
		$stmp = getdate();
		$year = $this->separarDec($stmp["year"], 2);
		$header .= chr($year[0]) . chr($year[1]) . chr($stmp["mon"]) . chr($stmp["mday"]) . chr($stmp["hours"]) . chr($stmp["minutes"]) . chr($stmp["seconds"]); //Timestamp (7 bytes)
		$header .= chr(1); //Comando (1 byte)
		$header .= chr(0) . chr(0); //Longitud del comando(2 bytes)

		if (!empty($comandoCti)) {
			unset($aux);
			for ($j = 0; $j < count($comandoCti); $j++) {
				$command = (int) $comandoCti[$j];
				$aux = $this->separarDec($command, 2);
				${'commandHex_' . $j} = chr($aux[0]) . chr($aux[1]);
				if ($longitudComando[$j] != 'var') {
					$long = (int) $longitudComando[$j];
					unset($aux);
					$aux = $this->separarDec($long, 2);
					${'longHex_' . $j} = chr($aux[0]) . chr($aux[1]);
				}
			}
		}

		if (!is_array($tarjetas)) {
			$tarjetas = array($tarjetas);
		}

		for ($i = 0; $i <  count($tarjetas); $i++) {

			$tarjeta = (int) $tarjetas[$i];

			$sql = "SELECT c.id from int_contrato_caja c where c.id_tarjeta = '$tarjeta'";
			if ($oCon->Query($sql)) {
				if ($oCon->NumFilas() > 0) {
					$idCaja = $oCon->f('id');
				}
			}
			$oCon->Free();

			if (empty($idCaja)) {
				$idCaja = 'null';
			}

			$subId++;
			$this->subId = $subId;
			$tipo = '';
			if (!empty($verb['tipo'])) {
				if ($verb['tipo'] == 'S') {
					$tipo = 'Suspender tarjeta ';
				} elseif ($verb['tipo'] == 'R') {
					$tipo = 'Reconectar tarjeta ';
				} elseif ($verb['tipo'] == 'U') {
					$tipo = 'Quitar tarjeta ';
				}
			}
			$userInfo = "$actividad : $tipo" . $tarjeta . "";
			$this->userInfo = $userInfo;
			$sql = "INSERT INTO int_log_cti_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, id_equipo, ejecucion)
					VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . " ,$idCaja ,'$userInfo')";
			$oCon->QueryT($sql);
			$sql = "SELECT max(id) as id from int_log_cti_cas";
			if ($oCon->Query($sql)) {
				if ($oCon->NumFilas() > 0) {
					$idLog = $oCon->f('id');
					$this->idLog = $idLog;
				}
			}

			if (!empty($id_peticion)) {
				$id_conexion = $infoCAS['id_conexion'];
				$sql = "UPDATE isp.int_red_peticiones SET id_log = $id_conexion WHERE id = $id_peticion";
				$oCon->QueryT($sql);
			}

			$verbHex = '';
			switch ($idActividad) {
				case 1: //Connection Check
					//$comandos = '';//No tiene verb
					$verbHex = '';
					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);

					$respuesta = substr($reading, 28, strlen($reading) - 28);

					if ($respuesta == '020000') {

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
						if ($verb['fin'] === true) {
							if ($this->tipo === 1) {
								$this->msj = true;
							} else {
								$oReturn->alert('La conexion trabaja correctamente');
							}
						}
					} else {

						$respuesta = $datos['respuesta'];
						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$oCon->Free();

						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->msj = false;
						$this->excepcionFunc();

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					}

					break;
				case 4: //Register Smart Card (V4.0)
					//$comandos = array('4','6','7','11','12','10','68');

					$dataHex_0 = '';
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}

					$dataHex_1 = (string) $verb['idStb'];
					unset($aux);
					$long = strlen(bin2hex($dataHex_1)) / 2;
					if ($long > 20) {
						$userInfo = 'La longitud de la tarjeta excede el limite permitido';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_1 = chr($aux[0]) . chr($aux[1]);

					$dataHex_2 = (string) $verb['regionid'];
					$long = strlen($dataHex_2);
					if ($long > 8) {
						$userInfo = 'Se excedio el limite maximo de longitud del Id de la region';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_2 = chr($aux[0]) . chr($aux[1]);

					$dataHex_3 = (string) $verb['ctrdistrict'];
					$long = strlen($dataHex_3);
					if ($long > 8) {
						$userInfo = 'Se excedio el limite maximo de longitud del Id de la region';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_3 = chr($aux[0]) . chr($aux[1]);

					$dataHex_4 = (int) $verb['ctrcustwd'];
					unset($aux);
					$aux = $this->separarDec($dataHex_4, $longitudComando[4]);
					$dataHex_4 = '';
					for ($j = 0; $j < $longitudComando[4]; $j++) {
						$dataHex_4 = $dataHex_4 . chr($aux[$j]);
					}

					$dataHex_5 = (int) $verb['matchtype'];
					unset($aux);
					$aux = $this->separarDec($dataHex_5, $longitudComando[5]);
					$dataHex_5 = '';
					for ($j = 0; $j < $longitudComando[5]; $j++) {
						$dataHex_5 = $dataHex_5 . chr($aux[$j]);
					}

					$verbHex = '';
					for ($j = 0; $j < (count($comandoCti) - 1); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					//var_dump(bin2hex($verbHex));

					$reading = bin2hex($datos['reading']);
					//$oReturn->alert(bin2hex($datos['writing']).'--'.$reading);
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {
						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {
						$verbHex = bin2hex($verbHex);
						$sql = " UPDATE int_log_cti_cas set estado = 'OK', comando = '$verbHex' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);


						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
					}

					break;
				case 5: //Smart Card Operation: Stop, Unregister, Resume
					//$comandos = array('4','13');

					$estado = $verb['estado'];
					if (empty($estado)) {
						$userInfo = "Al ejecutar la operacion " . $verb['tipo'] . " de la tarjeta no se envio un estado";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}

					$dataHex_0 = '';
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}

					$dataHex_1 = $verb['tipo'];

					$verbHex = '';
					for ($j = 0; $j < count($longitudComando); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					//$verbHex = pack('H*','0004000500000004F6000600083030303930373833000700013100080003000001000900020001000A00010C000B000131000C00020001');
					//$verbHex = pack('H*','0004000500000004F6000D000152');

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					//$oReturn->alert(bin2hex($datos['writing']).'--'.$reading);
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$oCon->Free();
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {
						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);

						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
					}

					break;
				case 6: //Resume Entitle of Smart Card
				case 9: //Purchase Package
				case 10: //Cancel Package
					//$comandos = array('4','16');

					$estado = $verb['estado'];
					$estado = 'A';
					if (empty($estado)) {
						$userInfo = "Se perdio la informacion del estado";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}

					$dataHex_0 = '';
					unset($aux);
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}

					$paquetes = $verb['paquetes'];
					if (!is_array($paquetes)) {
						$paquetes = array($paquetes);
					}

					unset($aux);
					for ($j = 0; $j < count($paquetes); $j++) {
						unset($aux);
						if ($j != 0) {
							${'commandHex_' . ($j + 1)} = ${'commandHex_' . $j};
							${'longHex_' . ($j + 1)} = ${'longHex_' . $j};
						}
						${'dataHex_' . ($j + 1)} = '';
						$paquete = $paquetesCti[$paquetes[$j]];
						$aux = $this->separarDec($paquete, $longitudComando[1]);
						for ($q = 0; $q < $longitudComando[1]; $q++) {
							${'dataHex_' . ($j + 1)} = ${'dataHex_' . ($j + 1)} . chr($aux[$q]);
						}
					}

					$verbHex = '';
					for ($j = 0; $j < (count($longitudComando) + count($paquetes) - 1); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					//$verbHex = pack('H*','0004000500000004F6001000020001');

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					//var_dump(bin2hex($verbHex));

					$reading = bin2hex($datos['reading']);
					//$oReturn->alert(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading'  where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
					}

					break;

				case 7: //Match of Smart Card and STB
					//$comandos = array('4','6','10');

					$dataHex_0 = '';
					unset($aux);
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}

					$dataHex_1 = (string) $verb['idStb'];

					$ok = $dataHex_1;

					unset($aux);
					$long = strlen(bin2hex($dataHex_1)) / 2;
					$ok .= $long;
					if ($long > 20) {
						$userInfo = 'La longitud de la tarjeta excede el limite permitido';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_1 = chr($aux[0]) . chr($aux[1]);

					//$ok .= $aux[0];
					//$ok .= $aux[1];

					$dataHex_2 = (int) $verb['matchtype'];
					unset($aux);
					$aux = $this->separarDec($dataHex_2, $longitudComando[2]);
					$dataHex_2 = '';
					for ($j = 0; $j < $longitudComando[2]; $j++) {
						$dataHex_2 = $dataHex_2 . chr($aux[$j]);
					}

					$verbHex = '';
					for ($j = 0; $j < count($comandoCti); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					//var_dump(bin2hex($verbHex));

					$reading = bin2hex($datos['reading']);
					//$oReturn->alert(bin2hex($datos['writing']).'--'.$reading);
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {

						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading', info = '$ok' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);

						//UNA VEZ QUE SE EMPATA EL TALLER HAY QUE REGISTRAR LA TARJETA EN LA BDD INT_CONTRATO_CAJA
					}

					break;
				case 8: //Send Money
					$comandos = array('4', '14', '20');
					break;
				case 11: //Purchase Program
					$comandos = array('4', '14', '21', '22', '23', '24', '25');
					break;
				case 12: //Cancel Program
					$comandos = array('4', '14', '21', '22', '23');
					break;
				case 13: //Single Message BMail**
					//$comandos = array('4','6','26','28','29','30','31');

					$dataHex_0 = '';
					unset($aux);
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}

					//ACTIVAR SI EL SISTEMA TIENE EL NUMERO DE LOS STBS DE TODAS LAS TARJETAS (cambiar for verbhex)						
					/*
						$dataHex_1 = (string) $verb['stbid'];
						unset($aux);
						$long = strlen(bin2hex($dataHex_1))/2;
						$aux = $this->separarDec($long,2);
						$longHex_1 = chr($aux[0]).chr($aux[1]);
					*/

					//Formato de fecha ingreso yyyy-mm-dd hh:mm:ss
					$inicio = $verb['startdate'];
					if (empty($inicio)) {
						$stmp = getdate();
						$year = $this->separarDec($stmp["year"], 2);
						$dataHex_2 = chr($year[0]) . chr($year[1]) . chr($stmp["mon"]) . chr($stmp["mday"]) . chr($stmp["hours"]) . chr($stmp["minutes"]) . chr($stmp["seconds"]);
					} else {
						$year = (int) substr($inicio, 0, 4);
						$year = $this->separarDec($year, 2);
						$dataHex_2 = chr($year[0]) . chr($year[1]) . chr(substr($inicio, 5, 2)) . chr(substr($inicio, 8, 2)) . chr(substr($inicio, 11, 2)) . chr(substr($inicio, 14, 2) + 1) . chr(substr($inicio, 17, 2));
					}

					$autoriza =  (int) $verb['messday'];
					if ($autoriza < 0) {
						$userInfo = 'El valor de la autorizacion es negativo';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$dataHex_3 = chr($autoriza);

					$dataHex_4 = $verb['txtAsunto'];
					unset($aux);
					$long = strlen($dataHex_4);
					if ($long > 60) {
						$userInfo = 'El texto del titulo sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_4 = chr($aux[0]) . chr($aux[1]);

					$dataHex_5 = $verb['txtMsj'];
					unset($aux);
					$long = strlen($dataHex_5);
					if ($long > 2000) {
						$userInfo = 'El texto del contenido sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_5 = chr($aux[0]) . chr($aux[1]);

					$dataHex_6 = $verb['messfrom'];
					unset($aux);
					$long = strlen($dataHex_6);
					if ($long > 20) {
						$userInfo = 'El texto del remitente sobrepaso el limite del soportado por el CAS';
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_6 = chr($aux[0]) . chr($aux[1]);

					$verbHex = '';
					for ($j = 0; $j < count($comandoCti); $j++) {
						if ($j != 1) { //Retirar si se activa el comando STBid
							$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
						}
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					//$oReturn->alert(bin2hex($datos['writing']).'--'.$reading);
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {
						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}
					break;

				case 14: //Region Message BMail**
					//$comandos = array('7','26','28','29','30','31');

					$dataHex_0 = (string) $verb['regionid'];
					$long = strlen($dataHex_0);
					if ($long > 8) {
						$userInfo = 'Se excedio el limite maximo de longitud del Id de la region';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_0 = chr($aux[0]) . chr($aux[1]);


					//Formato de fecha yyyy-mm-dd hh:mm:ss
					$inicio = $verb['messstart'];
					if (empty($inicio)) {
						$stmp = getdate();
						$year = $this->separarDec($stmp["year"], 2);
						$dataHex_1 = chr($year[0]) . chr($year[1]) . chr($stmp["mon"]) . chr($stmp["mday"]) . chr($stmp["hours"]) . chr($stmp["minutes"]) . chr($stmp["seconds"]);
					} else {
						$year = (int) substr($inicio, 0, 4);
						$year = $this->separarDec($year, 2);
						$dataHex_1 = chr($year[0]) . chr($year[1]) . chr(substr($inicio, 5, 2)) . chr(substr($inicio, 8, 2)) . chr(substr($inicio, 11, 2)) . chr(substr($inicio, 14, 2)) . chr(substr($inicio, 17, 2));
					}

					$autoriza =  (int) $verb['messday'];
					$dataHex_2 = chr($autoriza);

					$dataHex_3 = $verb['messtitle'];
					unset($aux);
					$long = strlen($dataHex_3);
					if ($long > 60) {
						$userInfo = 'El texto del titulo sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_3 = chr($aux[0]) . chr($aux[1]);

					$dataHex_4 = $verb['messcont'];
					unset($aux);
					$long = strlen($dataHex_4);
					if ($long > 2000) {
						$userInfo = 'El texto del contenido sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_4 = chr($aux[0]) . chr($aux[1]);

					$dataHex_5 = $verb['messfrom'];
					unset($aux);
					$long = strlen($dataHex_5) / 2;
					if ($long > 20) {
						$userInfo = 'El texto del remitente sobrepaso el limite del soportado por el CAS';
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_5 = chr($aux[0]) . chr($aux[1]);

					$verbHex = '';
					for ($j = 0; $j < count($comandoCti); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					if ($this->tipo === 1) {
						$this->msj = bin2hex($datos['writing']) . '--' . $reading;
					} else {
						$oReturn->alert(bin2hex($datos['writing']) . '--' . $reading);
					}
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {

						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}
					break;

				case 15: //All Message BMail**
					//$comandos = array('26','28','29','30','31');

					//Formato de fecha yyyy-mm-dd hh:mm:ss
					$inicio = $verb['messstart'];
					if (empty($inicio)) {
						$stmp = getdate();
						$year = $this->separarDec($stmp["year"], 2);
						$dataHex_0 = chr($year[0]) . chr($year[1]) . chr($stmp["mon"]) . chr($stmp["mday"]) . chr($stmp["hours"]) . chr($stmp["minutes"]) . chr($stmp["seconds"]);
					} else {
						$year = (int) substr($inicio, 0, 4);
						$year = $this->separarDec($year, 2);
						$dataHex_0 = chr($year[0]) . chr($year[1]) . chr(substr($inicio, 5, 2)) . chr(substr($inicio, 8, 2)) . chr(substr($inicio, 11, 2)) . chr(substr($inicio, 14, 2)) . chr(substr($inicio, 17, 2));
					}

					$autoriza =  (int) $verb['messday'];
					$dataHex_1 = chr($autoriza);

					$dataHex_2 = $verb['messtitle'];
					unset($aux);
					$long = strlen($dataHex_2);
					if ($long > 60) {
						$userInfo = 'El texto del titulo sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_2 = chr($aux[0]) . chr($aux[1]);

					$dataHex_3 = $verb['messcont'];
					unset($aux);
					$long = strlen($dataHex_3);
					if ($long > 2000) {
						$userInfo = 'El texto del contenido sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_3 = chr($aux[0]) . chr($aux[1]);

					$dataHex_4 = $verb['messfrom'];
					unset($aux);
					$long = strlen($dataHex_4) / 2;
					if ($long > 20) {
						$userInfo = 'El texto del remitente sobrepaso el limite del soportado por el CAS';
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_4 = chr($aux[0]) . chr($aux[1]);

					$verbHex = '';
					for ($j = 0; $j < count($comandoCti); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					//$oReturn->alert(bin2hex($datos['writing']).'--'.$reading);
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {

						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}
					break;

				case 16: //Single Message OSD**
					//$comandos = array('4','6','26','28','27','29','30','31');

					$dataHex_0 = '';
					unset($aux);
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}

					//ACTIVAR SI EL SISTEMA TIENE EL NUMERO DE LOS STBS DE TODAS LAS TARJETAS (cambiar for verbhex)						
					$verb['stbid'] = '620118010007229';
					$dataHex_1 = (string) $verb['stbid'];
					unset($aux);
					$long = strlen(bin2hex($dataHex_1)) / 2;
					$aux = $this->separarDec($long, 2);
					$longHex_1 = chr($aux[0]) . chr($aux[1]);

					//Formato de fecha ingreso yyyy-mm-dd hh:mm:ss
					$inicio = $verb['startdate'];
					$inicio = date("Y-m-d H:i:s");
					if (empty($inicio)) {
						$stmp = getdate();
						$year = $this->separarDec($stmp["year"], 2);
						$dataHex_2 = chr($year[0]) . chr($year[1]) . chr($stmp["mon"]) . chr($stmp["mday"]) . chr($stmp["hours"]) . chr($stmp["minutes"]) . chr($stmp["seconds"]);
					} else {
						$year = (int) substr($inicio, 0, 4);
						$year = $this->separarDec($year, 2);
						$dataHex_2 = chr($year[0]) . chr($year[1]) . chr(substr($inicio, 5, 2)) . chr(substr($inicio, 8, 2)) . chr(substr($inicio, 11, 2)) . chr(substr($inicio, 14, 2) + 1) . chr(substr($inicio, 17, 2));
					}

					$autoriza =  (int) $verb['messday'];
					$autoriza = 11;
					if ($autoriza < 0) {
						$userInfo = 'El valor de la autorizacion es negativo';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$dataHex_3 = chr($autoriza);

					$cuenta = (int) $verb['messaccount'];
					$cuenta = 1;
					$dataHex_4 .= chr($cuenta);

					$dataHex_5 = $verb['txtAsunto'];
					$dataHex_5 = 'Asunto';
					unset($aux);
					$long = strlen($dataHex_5);
					if ($long > 60) {
						$userInfo = 'El texto del titulo sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_5 = chr($aux[0]) . chr($aux[1]);

					$dataHex_6 = $verb['txtMsj'];
					$dataHex_6 = 'Hola Mundo';
					unset($aux);
					$long = strlen($dataHex_6);
					if ($long > 2000) {
						$userInfo = 'El texto del contenido sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_6 = chr($aux[0]) . chr($aux[1]);

					$dataHex_7 = $verb['messfrom'];
					$dataHex_7 = 'DVB';
					unset($aux);
					$long = strlen($dataHex_7);
					if ($long > 20) {
						$userInfo = 'El texto del remitente sobrepaso el limite del soportado por el CAS';
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_7 = chr($aux[0]) . chr($aux[1]);

					$verbHex = '';
					for ($j = 0; $j < count($comandoCti); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					//$oReturn->alert(bin2hex($datos['writing']).'--'.$reading);
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {
						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}
					break;

				case 17: //Region Message OSD**
					//$comandos = array('7','26','28','27','29','30','31');

					$dataHex_0 = (string) $verb['regionid'];
					$long = strlen($dataHex_0);
					if ($long > 8) {
						$userInfo = 'Se excedio el limite maximo de longitud del Id de la region';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_0 = chr($aux[0]) . chr($aux[1]);

					//Formato de fecha yyyy-mm-dd hh:mm:ss
					$inicio = $verb['messstart'];
					if (empty($inicio)) {
						$stmp = getdate();
						$year = $this->separarDec($stmp["year"], 2);
						$dataHex_1 = chr($year[0]) . chr($year[1]) . chr($stmp["mon"]) . chr($stmp["mday"]) . chr($stmp["hours"]) . chr($stmp["minutes"]) . chr($stmp["seconds"]);
					} else {
						$year = (int) substr($inicio, 0, 4);
						$year = $this->separarDec($year, 2);
						$dataHex_1 = chr($year[0]) . chr($year[1]) . chr(substr($inicio, 5, 2)) . chr(substr($inicio, 8, 2)) . chr(substr($inicio, 11, 2)) . chr(substr($inicio, 14, 2)) . chr(substr($inicio, 17, 2));
					}

					$autoriza =  (int) $verb['messday'];
					$dataHex_2 = chr($autoriza);

					$cuenta =  (int) $verb['messaccount'];
					$dataHex_3 = chr($cuenta);

					$dataHex_4 = $verb['messtitle'];
					unset($aux);
					$long = strlen($dataHex_4);
					if ($long > 60) {
						$userInfo = 'El texto del titulo sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_4 = chr($aux[0]) . chr($aux[1]);

					$dataHex_5 = $verb['messcont'];
					unset($aux);
					$long = strlen($dataHex_5);
					if ($long > 2000) {
						$userInfo = 'El texto del contenido sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_5 = chr($aux[0]) . chr($aux[1]);

					$dataHex_6 = $verb['messfrom'];
					unset($aux);
					$long = strlen($dataHex_6) / 2;
					if ($long > 20) {
						$userInfo = 'El texto del remitente sobrepaso el limite del soportado por el CAS';
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_6 = chr($aux[0]) . chr($aux[1]);

					$verbHex = '';
					for ($j = 0; $j < count($comandoCti); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					if ($this->tipo === 1) {
						$this->msj = bin2hex($datos['writing']) . '--' . $reading;
					} else {
						$oReturn->alert(bin2hex($datos['writing']) . '--' . $reading);
					}
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {

						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}
					break;

				case 18: //All Message OSD**
					//$comandos = array('26','28','27','29','30','31');

					//Formato de fecha yyyy-mm-dd hh:mm:ss
					$inicio = $verb['messstart'];
					if (empty($inicio)) {
						$stmp = getdate();
						$year = $this->separarDec($stmp["year"], 2);
						$dataHex_0 = chr($year[0]) . chr($year[1]) . chr($stmp["mon"]) . chr($stmp["mday"]) . chr($stmp["hours"]) . chr($stmp["minutes"]) . chr($stmp["seconds"]);
					} else {
						$year = (int) substr($inicio, 0, 4);
						$year = $this->separarDec($year, 2);
						$dataHex_0 = chr($year[0]) . chr($year[1]) . chr(substr($inicio, 5, 2)) . chr(substr($inicio, 8, 2)) . chr(substr($inicio, 11, 2)) . chr(substr($inicio, 14, 2)) . chr(substr($inicio, 17, 2));
					}

					$autoriza =  (int) $verb['messday'];
					$dataHex_1 = chr($autoriza);

					$cuenta =  (int) $verb['messaccount'];
					$dataHex_2 = chr($cuenta);

					$dataHex_3 = $verb['messtitle'];
					unset($aux);
					$long = strlen($dataHex_3);
					if ($long > 60) {
						$userInfo = 'El texto del titulo sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_3 = chr($aux[0]) . chr($aux[1]);

					$dataHex_4 = $verb['messcont'];
					unset($aux);
					$long = strlen($dataHex_4);
					if ($long > 2000) {
						$userInfo = 'El texto del contenido sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_4 = chr($aux[0]) . chr($aux[1]);

					$dataHex_5 = $verb['messfrom'];
					unset($aux);
					$long = strlen($dataHex_5) / 2;
					if ($long > 20) {
						$userInfo = 'El texto del remitente sobrepaso el limite del soportado por el CAS';
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_5 = chr($aux[0]) . chr($aux[1]);

					$verbHex = '';
					for ($j = 0; $j < count($comandoCti); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					if ($this->tipo === 1) {
						$this->msj = true;
					} else {
						$oReturn->alert(bin2hex($datos['writing']) . '--' . $reading);
					}
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {

						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}
					break;

				case 19: //Slave Smart Card Register
					$comandos = array('33', '34', '4', '35', '36', '37', '38', '39', '40', '41', '42');
					break;
				case 20: //Master and Slave Smart Card Operation
					$comandos = array('33', '34', '4', '35');
					break;
				case 21: //Get PPID Information
					//$comandos = '';
					$verbHex = '';
					//$this->getInfo($activity);

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					if ($this->tipo === 1) {
						$this->msj = bin2hex($datos['writing']) . '--' . $reading;
					} else {
						$oReturn->alert(bin2hex($datos['writing']) . '--' . $reading);
					}
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {

						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading'  where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}
					break;

				case 22: //Get Channel Information
					//$comandos = '14';
					//$this->getInfo($activity);

					$ppid = (int) $verb['proveedorId'];
					unset($aux);
					$aux = $this->separarDec($ppid, 1);
					$dataHex_0 = chr($aux[0]);
					$verbHex = $commandHex_0 . $longHex_0 . $dataHex_0;

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					if ($this->tipo === 1) {
						$this->msj = bin2hex($datos['writing']) . '--' . $reading;
					} else {
						$oReturn->alert(bin2hex($datos['writing']) . '--' . $reading);
					}
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {

						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading'  where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}

					break;
				case 23: //Get Program Event Information
					//$comandos = array('14','18','46');
					//$this->getInfo($activity);

					$ppid = (int) $verb['proveedorId'];
					unset($aux);
					$aux = $this->separarDec($ppid, 1);
					$dataHex_0 = chr($aux[0]);

					$channelId = (int) $verb['channelid'];
					unset($aux);
					$aux = $this->separarDec($channelId, 2);
					$dataHex_1 = chr($aux[0]) . chr($aux[1]);

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					if ($this->tipo === 1) {
						$this->msj = bin2hex($datos['writing']) . '--' . $reading;
					} else {
						$oReturn->alert(bin2hex($datos['writing']) . '--' . $reading);
					}
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {

						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading'  where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}

					break;
				case 24: //Get Program Package Information
					//$comandos = array('14','46');

					//Formato de fecha yyyy-mm-dd hh:mm:ss
					$opdate = $verb['fechaOperacion'];
					if ($opdate == null || $opdate == '') {
						$stmp = getdate();
						$year = $this->separarDec($stmp["year"], 2);
						$dataHex_1 = chr($year[0]) . chr($year[1]) . chr($stmp["mon"]) . chr($stmp["mday"]) . chr($stmp["hours"]) . chr($stmp["minutes"]) . chr($stmp["seconds"]);
					} else {
						$year = (int) substr($opdate, 0, 4);
						$year = $this->separarDec($year, 2);
						$dataHex_1 = chr($year[0]) . chr($year[1]) . chr(substr($opdate, 5, 2)) . chr(substr($opdate, 8, 2)) . chr(substr($opdate, 11, 2)) . chr(substr($opdate, 14, 2)) . chr(substr($opdate, 17, 2));
					}

					$dataHex_0 = '';
					$ppid = (int) $verb['idProveedor'];
					unset($aux);
					$aux = $this->separarDec($ppid, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}

					$verbHex = '';
					for ($j = 0; $j < (count($longitudComando)); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					//$oReturn->alert(bin2hex($datos['writing']).'--'.$reading);
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {
						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading'  where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}
					break;

				case 25: //Get Package In Smart Card
				case 45: //Get UA Information
					$dataHex_0 = '';
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}
					$verbHex = '';
					for ($j = 0; $j < (count($longitudComando)); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}
					$datos = $this->envioComando($actividadCti, $header, $verbHex);
					$reading = bin2hex($datos['reading']);
					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {
						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}

						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->msj = false;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {
						unset($oReturn);
						$oReturn = $this->respuesta($idActividad, $reading);
						$arrayPackCti = $oReturn['PACKAGEId'];
						$sql = "SELECT t.id_cas, r.id 
								FROM int_paquetes r, int_perfiles_cti t, int_paquetes_perfiles f 
								WHERE f.id_paquete = r.id AND 
								f.id_perfil = t.id 
								GROUP BY t.id_cas";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								do {
									$arrayPack[$oCon->f('id_cas')] = $oCon->f('id');
								} while ($oCon->SiguienteRegistro());
							}
						}
						$oCon->Free();
						for ($k = 0; $k < count($arrayPackCti); $k++) {
							$arrayOk[$k] = $arrayPack[$arrayPackCti[$k]];
						}
						$oReturn['PACKAGEId'] = $arrayOk;
						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading'  where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);

						$this->msj = true;
					}
					break;

					//ACTUALMENTE NO USADO
				case 30: //Refresh Smart Card CA Entitlement
					$dataHex_0 = '';
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}
					$verbHex = '';
					for ($j = 0; $j < (count($longitudComando)); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}
					$datos = $this->envioComando($actividadCti, $header, $verbHex);
					$reading = bin2hex($datos['reading']);
					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {
						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$oCon->Free();
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
						$verbHex = bin2hex($verbHex);
						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading', info = '$verbHex'  where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}
					break;
				case 31: //Refresh Smart Card CA Register
					$dataHex_0 = '';
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}
					$verbHex = '';
					for ($j = 0; $j < (count($longitudComando)); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}
					$datos = $this->envioComando($actividadCti, $header, $verbHex);
					$reading = bin2hex($datos['reading']);
					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {
						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$oCon->Free();
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();

						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading'  where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}
					break;

				case 35: //All Package Subscribe 
					break;
				case 36: //All Package Cancel
					$estado = $verb['estado'];
					if (empty($estado)) {
						$userInfo = "Se perdio la informacion del estado";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$dataHex_0 = '';
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}
					$verbHex = '';
					for ($j = 0; $j < (count($longitudComando)); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}
					$datos = $this->envioComando($actividadCti, $header, $verbHex);
					$reading = bin2hex($datos['reading']);
					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {
						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {
						$sql = "UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
					}
					break;

				case 26: //Write CCA Information (Write Region information)***
					$comandos = array('7', '32');
					break;
				case 27: //Write GCA Information*
					$comandos = array('11', '73');
					break;
				case 28: //Delete CCA Information*
					$comandos = '7';
					break;
				case 29: //Delete GCA Inforamtion*
					$comandos = '11';
					break;
				case 32: //Exchange Smart Card
					//$comandos = array('53','54');
					$dataHex_0 = '';
					unset($aux);
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}

					$dataHex_1 = (string) $verb['newcard'];
					unset($aux);
					$aux = $this->separarDec($tarjeta, $longitudComando[1]);
					for ($j = 0; $j < $longitudComando[1]; $j++) {
						$dataHex_1 = $dataHex_0 . chr($aux[$j]);
					}

					$verbHex = '';
					for ($j = 0; $j < count($comandoCti); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);
					$reading = bin2hex($datos['reading']);
					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
					}
					break;

				case 33: //Exchange STB
					//$comandos = array('4','55','56');
					$dataHex_0 = '';
					unset($aux);
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}

					$dataHex_1 = (string) $verb['stbid'];
					unset($aux);
					$long = strlen(bin2hex($dataHex_1)) / 2;
					if ($long > 20) {
						$userInfo = 'La longitud de la tarjeta excede el limite permitido';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_1 = chr($aux[0]) . chr($aux[1]);

					$dataHex_2 = (string) $verb['newstbid'];
					unset($aux);
					$long = strlen(bin2hex($dataHex_2)) / 2;
					if ($long > 20) {
						$userInfo = 'La longitud de la tarjeta excede el limite permitido';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_2 = chr($aux[0]) . chr($aux[1]);;

					$verbHex = '';
					for ($j = 0; $j < count($comandoCti); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
					}
					break;

					break;
				case 34: //Smart Card Other Operation*
					//$comandos = array('4', '6', '7', '11', '12', '10', '68');

					$log = '';

					$dataHex_0 = '';
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}

					$dataHex_1 = (string) $verb['idStb'];
					$log .= 'idStb: ' . $dataHex_1;
					unset($aux);
					$long = strlen(bin2hex($dataHex_1)) / 2;
					if ($long > 20) {
						$userInfo = 'La longitud de la tarjeta excede el limite permitido';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_1 = chr($aux[0]) . chr($aux[1]);


					$dataHex_2 = (string) $verb['regionid'];
					$log .= 'regionid: ' . $dataHex_2;
					$long = strlen($dataHex_2);
					if ($long > 8) {
						$userInfo = 'Se excedio el limite maximo de longitud del Id de la region';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_2 = chr($aux[0]) . chr($aux[1]);

					$dataHex_3 = (string) $verb['ctrdistrict'];
					$log .= 'ctrdistrict: ' . $dataHex_3;
					$long = strlen($dataHex_3);
					if ($long > 8) {
						$userInfo = 'Se excedio el limite maximo de longitud del Id de la region';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_3 = chr($aux[0]) . chr($aux[1]);

					$dataHex_4 = (int) $verb['ctrcustwd'];
					$log .= 'ctrcustwd: ' . $dataHex_4;
					unset($aux);
					$aux = $this->separarDec($dataHex_4, $longitudComando[4]);
					$dataHex_4 = '';
					for ($j = 0; $j < $longitudComando[4]; $j++) {
						$dataHex_4 = $dataHex_4 . chr($aux[$j]);
					}

					$dataHex_5 = (int) $verb['matchtype'];
					$log .= 'matchtype: ' . $dataHex_5;
					unset($aux);
					$aux = $this->separarDec($dataHex_5, $longitudComando[5]);
					$dataHex_5 = '';
					for ($j = 0; $j < $longitudComando[5]; $j++) {
						$dataHex_5 = $dataHex_5 . chr($aux[$j]);
					}

					$verbHex = '';
					for ($j = 0; $j < (count($comandoCti) - 1); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					//$oReturn->alert(bin2hex($datos['writing']).'--'.$reading);
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {
						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
					}

					break;
				case 37: //Approvable Delete Single Message
					//$comandos = array('4','57','6','26','28','27','29','30','31','58');

					var_dump($verb);
					$this->excepcionFunc();
					exit;

					$dataHex_0 = '';
					unset($aux);
					$aux = $this->separarDec($tarjeta, $longitudComando[0]);
					for ($j = 0; $j < $longitudComando[0]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}

					$dataHex_1 = $verb['messtype']; //Solo puede ser U/u

					//ACTIVAR SI EL SISTEMA TIENE EL NUMERO DE LOS STBS DE TODAS LAS TARJETAS (cambiar for verbhex)						
					/*
					$dataHex_2 = (string) $verb['stbid'];
					unset($aux);
					$long = strlen(bin2hex($dataHex_1))/2;
					$aux = $this->separarDec($long,2);
					$longHex_2 = chr($aux[0]).chr($aux[1]);
					*/

					//Formato de fecha yyyy-mm-dd hh:mm:ss
					$inicio = $verb['messstart'];
					if (empty($inicio)) {
						$stmp = getdate();
						$year = $this->separarDec($stmp["year"], 2);
						$dataHex_3 = chr($year[0]) . chr($year[1]) . chr($stmp["mon"]) . chr($stmp["mday"]) . chr($stmp["hours"]) . chr($stmp["minutes"]) . chr($stmp["seconds"]);
					} else {
						$year = (int) substr($inicio, 0, 4);
						$year = $this->separarDec($year, 2);
						$dataHex_3 = chr($year[0]) . chr($year[1]) . chr(substr($inicio, 5, 2)) . chr(substr($inicio, 8, 2)) . chr(substr($inicio, 11, 2)) . chr(substr($inicio, 14, 2)) . chr(substr($inicio, 17, 2));
					}

					$autoriza =  (int) $verb['messday'];
					$dataHex_4 = chr($autoriza);

					$cuenta =  (int) $verb['messaccount'];
					$dataHex_5 = chr($cuenta);

					$dataHex_6 = $verb['messtitle'];
					unset($aux);
					$long = strlen($dataHex_6);
					if ($long > 60) {
						$userInfo = 'El texto del titulo sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_6 = chr($aux[0]) . chr($aux[1]);

					$dataHex_7 = $verb['messcont'];
					unset($aux);
					$long = strlen($dataHex_7);
					if ($long > 2000) {
						$userInfo = 'El texto del contenido sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_7 = chr($aux[0]) . chr($aux[1]);

					$dataHex_8 = $verb['messfrom'];
					unset($aux);
					$long = strlen($dataHex_8) / 2;
					if ($long > 20) {
						$userInfo = 'El texto del remitente sobrepaso el limite del soportado por el CAS';
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_8 = chr($aux[0]) . chr($aux[1]);

					$dataHex_9 = $verb['messageid'];
					unset($aux);
					$aux = $this->separarDec($dataHex_9, $longitudComando[9]);
					$dataHex_9 = '';
					for ($j = 0; $j < $longitudComando[9]; $j++) {
						$dataHex_9 = $dataHex_9 . chr($aux[$j]);
					}

					$verbHex = '';
					for ($j = 0; $j < count($comandoCti); $j++) {
						if ($j = 2) { //Retirar si se activa el comando STBid
							$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
						}
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					if ($this->tipo === 1) {
						$this->msj = bin2hex($datos['writing']) . '--' . $reading;
					} else {
						$oReturn->alert(bin2hex($datos['writing']) . '--' . $reading);
					}
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {

						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
					}
					break;

				case 38: //Approvable Delete Region Message
					//$comandos = array('7','57','26','28','27','29','30','31','58');

					$dataHex_0 = (string) $verb['regionid'];
					$long = strlen($dataHex_0);
					if ($long > 8) {
						$userInfo = 'Se excedio el limite maximo de longitud del Id de la region';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_0 = chr($aux[0]) . chr($aux[1]);

					$dataHex_1 = $verb['messtype']; //Solo puede ser C/c

					//Formato de fecha yyyy-mm-dd hh:mm:ss
					$inicio = $verb['messstart'];
					if (empty($inicio)) {
						$stmp = getdate();
						$year = $this->separarDec($stmp["year"], 2);
						$dataHex_2 = chr($year[0]) . chr($year[1]) . chr($stmp["mon"]) . chr($stmp["mday"]) . chr($stmp["hours"]) . chr($stmp["minutes"]) . chr($stmp["seconds"]);
					} else {
						$year = (int) substr($inicio, 0, 4);
						$year = $this->separarDec($year, 2);
						$dataHex_2 = chr($year[0]) . chr($year[1]) . chr(substr($inicio, 5, 2)) . chr(substr($inicio, 8, 2)) . chr(substr($inicio, 11, 2)) . chr(substr($inicio, 14, 2)) . chr(substr($inicio, 17, 2));
					}

					$autoriza =  (int) $verb['messday'];
					$dataHex_3 = chr($autoriza);

					$cuenta =  (int) $verb['messaccount'];
					$dataHex_4 = chr($cuenta);

					$dataHex_5 = $verb['messtitle'];
					unset($aux);
					$long = strlen($dataHex_5);
					if ($long > 60) {
						$userInfo = 'El texto del titulo sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_5 = chr($aux[0]) . chr($aux[1]);

					$dataHex_6 = $verb['messcont'];
					unset($aux);
					$long = strlen($dataHex_6);
					if ($long > 2000) {
						$userInfo = 'El texto del contenido sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_6 = chr($aux[0]) . chr($aux[1]);

					$dataHex_7 = $verb['messfrom'];
					unset($aux);
					$long = strlen($dataHex_7) / 2;
					if ($long > 20) {
						$userInfo = 'El texto del remitente sobrepaso el limite del soportado por el CAS';
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_7 = chr($aux[0]) . chr($aux[1]);

					$dataHex_8 = $verb['messageid'];
					unset($aux);
					$aux = $this->separarDec($dataHex_8, $longitudComando[9]);
					$dataHex_8 = '';
					for ($j = 0; $j < $longitudComando[9]; $j++) {
						$dataHex_8 = $dataHex_8 . chr($aux[$j]);
					}

					$verbHex = '';
					for ($j = 0; $j < count($comandoCti); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);
					if ($this->tipo === 1) {
						$this->msj = bin2hex($datos['writing']) . '--' . $reading;
					} else {
						$oReturn->alert(bin2hex($datos['writing']) . '--' . $reading);
					}
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {
						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();

						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}
					break;

				case 39: //Approvable Delete All Message
					//$comandos = array('57','26','28','27','29','30','31','58');

					$dataHex_0 = $verb['messtype']; //Solo puede ser G/g

					//Formato de fecha yyyy-mm-dd hh:mm:ss
					$inicio = $verb['messstart'];
					if (empty($inicio)) {
						$stmp = getdate();
						$year = $this->separarDec($stmp["year"], 2);
						$dataHex_1 = chr($year[0]) . chr($year[1]) . chr($stmp["mon"]) . chr($stmp["mday"]) . chr($stmp["hours"]) . chr($stmp["minutes"]) . chr($stmp["seconds"]);
					} else {
						$year = (int) substr($inicio, 0, 4);
						$year = $this->separarDec($year, 2);
						$dataHex_1 = chr($year[0]) . chr($year[1]) . chr(substr($inicio, 5, 2)) . chr(substr($inicio, 8, 2)) . chr(substr($inicio, 11, 2)) . chr(substr($inicio, 14, 2)) . chr(substr($inicio, 17, 2));
					}

					$autoriza =  (int) $verb['messday'];
					$dataHex_2 = chr($autoriza);

					$cuenta =  (int) $verb['messaccount'];
					$dataHex_3 = chr($cuenta);

					$dataHex_4 = $verb['messtitle'];
					unset($aux);
					$long = strlen($dataHex_4);
					if ($long > 60) {
						$userInfo = 'El texto del titulo sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_4 = chr($aux[0]) . chr($aux[1]);

					$dataHex_5 = $verb['messcont'];
					unset($aux);
					$long = strlen($dataHex_5);
					if ($long > 2000) {
						$userInfo = 'El texto del contenido sobrepaso el limite del soportado por el CAS';
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_5 = chr($aux[0]) . chr($aux[1]);

					$dataHex_6 = $verb['messfrom'];
					unset($aux);
					$long = strlen($dataHex_6) / 2;
					if ($long > 20) {
						$userInfo = 'El texto del remitente sobrepaso el limite del soportado por el CAS';
						$this->excepcionFunc();
					}
					$aux = $this->separarDec($long, 2);
					$longHex_6 = chr($aux[0]) . chr($aux[1]);

					$dataHex_7 = $verb['messageid'];
					unset($aux);
					$aux = $this->separarDec($dataHex_7, $longitudComando[9]);
					$dataHex_7 = '';
					for ($j = 0; $j < $longitudComando[9]; $j++) {
						$dataHex_7 = $dataHex_7 . chr($aux[$j]);
					}

					$verbHex = '';
					for ($j = 0; $j < count($comandoCti); $j++) {
						$verbHex = $verbHex . ${'commandHex_' . $j} . ${'longHex_' . $j} . ${'dataHex_' . $j};
					}

					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);

					if ($this->tipo === 1) {
						$this->msj = bin2hex($datos['writing']) . '--' . $reading;
					} else {
						$oReturn->alert(bin2hex($datos['writing']) . '--' . $reading);
					}
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {
						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}

					break;

				case 40: //Delete Message
					//$comandos = '58';

					$dataHex_0 = $verb['messageid'];
					unset($aux);
					$aux = $this->separarDec($dataHex_0, $longitudComando[0]);
					$dataHex_0 = '';
					for ($j = 0; $j < $longitudComando[9]; $j++) {
						$dataHex_0 = $dataHex_0 . chr($aux[$j]);
					}
					$datos = $this->envioComando($actividadCti, $header, $verbHex);

					$reading = bin2hex($datos['reading']);

					if ($this->tipo === 1) {
						$this->msj = bin2hex($datos['writing']) . '--' . $reading;
					} else {
						$oReturn->alert(bin2hex($datos['writing']) . '--' . $reading);
					}
					//var_dump(bin2hex($datos['writing']).'--'.$reading);

					$respuesta = $datos['respuesta'];

					if ($respuesta != 0) {
						$sql = "select comentario from int_respuesta_cti where valor = '$respuesta'";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$respuesta = $oCon->f('comentario');
							}
						}
						$userInfo = "$respuesta";
						$this->userInfo = $userInfo;
						$this->excepcionFunc();
						$this->msj = false;

						//update log peticion
						if (!empty($id_peticion)) {
							$sql = "UPDATE isp.int_red_peticiones set consulta = '$respuesta' where id = $id_peticion";
							$oCon->QueryT($sql);
						}
					} else {
						if ($this->tipo === 1) {
							$this->msj = true;
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}

						$sql = " UPDATE int_log_cti_cas set estado = 'OK', id_equipo = $idCaja, comando = '$reading' where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
					}

					break;

				case 41: //Parent Control Password of Smart Card
					$comandos = array('4', '62', '64', '60', '61');
					break;
				case 42: //Fingerprint Operation of Smart Card
					$comandos = array('4', '65', '66', '67', '60', '61');
					break;
				case 43: //Smart Card & High-Security Chip ID
					$comandos = array('4', '68', '69', '70', '71');
					break;
				case 44: //Get Chip ID Information
					$comandos = '69';
					$this->getInfo($activity);
					break;
				default:
					$comandos = ''; //No tiene comandos
					break;
			}
		}

		if ($this->tipo === 1) {
			return trim($this->msj);
		} else {
			return $oReturn;
		}
	}

	function cierraConexion()
	{

		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

		$subId = $this->subId;
		$socketCAS = $this->socketCAS;
		$infoCAS = $this->infoCAS;
		$oCon =	$this->oCon;
		$oReturn = $this->oReturn;

		$subId++;
		$userInfo = "Cerrar conexion";
		$this->userInfo = $userInfo;
		$sql = "INSERT INTO int_log_cti_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, ejecucion)
			   VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . ",'$userInfo')";
		$oCon->QueryT($sql);
		$sql = "SELECT max(id) as id from int_log_cti_cas";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				$idLog = $oCon->f('id');
			}
		}

		$close = socket_shutdown($socketCAS);

		if ($close === false) {

			$userInfo = "No se pudo enviar el comando correctamente: " . socket_last_error();
			//$userInfo = "No se pudo enviar el comando correctamente: ".socket_strerror(socket_last_error());
			$this->userInfo = $userInfo;
			$sql = "UPDATE int_log_cti_cas set estado = 'ER', info = '$userInfo' where sub_id = $subId and id = $idLog";
			$oCon->QueryT($sql);
			$this->excepcionFunc();
		} else {

			$sql = " UPDATE int_log_cti_cas set estado = 'OK' where sub_id = $subId and id = $idLog";
			$oCon->QueryT($sql);
		}

		socket_close($socketCAS);

		unset($this->conMSS);
		$this->subId = 0;
		$this->accionPrevia = false;

		return $oReturn;
	}

	function separarDec($num, $numVar = null)
	{
		if ($numVar == null) {
			$i = 0;
			do {
				$aux = $num / pow(256, $i);
				$i++;
			} while ($aux >= 1);
			$numVar = $i - 1;
		}
		$aux = $num;
		$num = array('');
		for ($i = 1; $i <= $numVar; $i++) {
			$aux2 = $aux / pow(256, ($numVar - $i));
			if ($aux2 >= 1) {
				$num[$i - 1] = floor($aux2);
				$aux = $aux - (floor($aux2) * pow(256, $numVar - $i));
			} else {
				$num[$i - 1] = 0;
			}
		}
		return ($num); //El resultado es un array con los decimales separados
	}

	function envioComando($actividadCti, $header, $verbHex)
	{

		$socketCAS = $this->socketCAS;
		$subId = $this->subId;

		$stmp = getdate(); //Actualizar fecha y hora
		$year = $this->separarDec($stmp["year"], 2);
		$header = substr_replace($header, chr($year[0]) . chr($year[1]) . chr($stmp["mon"]) . chr($stmp["mday"]) . chr($stmp["hours"]) . chr($stmp["minutes"]) . chr($stmp["seconds"]), 7, 7); //Reemplazo de timestamp
		$transaNo = $this->separarDec($subId, 4);
		$header = substr_replace($header, chr($transaNo[0]) . chr($transaNo[1]) . chr($transaNo[2]) . chr($transaNo[3]), 3, 4); //Reemplazo de No transacción
		$header = substr_replace($header, chr($actividadCti), 14, 1); //Reemplazo de comando
		unset($lCommand);
		$lCommand = bin2hex($verbHex);
		$lCommand = strlen($lCommand) / 2;
		$lCommand = $this->separarDec($lCommand, 2);
		$header = substr_replace($header, chr($lCommand[0]) . chr($lCommand[1]), 15, 2); //Reemplazo de longitud de comando
		$writing = $header . $verbHex;
		socket_write($socketCAS, $writing, strlen($writing));
		$reading = socket_read($socketCAS, 1024);
		//var_dump(bin2hex($writing));
		//$reading = 0;
		$datos['writing'] = $writing;
		$datos['reading'] = $reading;
		$datos['respuesta'] = 256;	//error no hubo respuesta STAT_CAS
		$reading = bin2hex($reading);
		if (strpos($reading, '00ff', 50) != false) {
			$inicio = strpos($reading, '00ff', 50) + 4;
			$longitud = (int) substr($reading, $inicio, 4);
			$respuesta = substr($reading, $inicio + 4, $longitud * 2);
			$respuesta = (int) hexdec($respuesta);
			$datos['respuesta'] = $respuesta;
		}

		return $datos;
	}

	function respuesta($idActividad, $reading)
	{

		$oCon =	$this->oCon;

		$inicio = strpos($reading, '00ff000100') + 10;
		$reading = substr($reading, $inicio, strlen($reading) - $inicio);

		$sql = 'SET @id = (
				SELECT MIN(a.id)
				FROM int_actividades a, int_dispositivos d, int_marcas_dispositivos m
				WHERE m.id = d.id_marca AND a.id_dispositivo = d.id AND m.marca = "COMPUNICATE") -1 ;';
		$oCon->QueryT($sql);

		$sql = "SET @rcomando = (
				SELECT a.info
				FROM int_actividades a, int_dispositivos d, int_marcas_dispositivos m
				WHERE m.id = d.id_marca AND a.id_dispositivo = d.id AND m.marca = 'COMPUNICATE' AND a.id = $idActividad + @id);";
		$oCon->QueryT($sql);

		$sql = "SELECT a.id AS id_tabla, a.actividad
				FROM int_actividades a, int_dispositivos d, int_marcas_dispositivos m
				WHERE a.id_dispositivo = d.id AND d.id_marca = m.id AND m.marca = 'COMPUNICATE' AND a.estado = 'A' 
				AND a.`inout` ='I' AND info = CONCAT('3_',@rcomando)";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				$actividad = $oCon->f('actividad');
				$idTabla = $oCon->f('id_tabla');
			}
		}
		$oCon->Free();

		$sql = "SELECT c.id_comando, c.comando, c.descripcion, c.longitud_bytes, c.observaciones, a.obligatorio  
				FROM int_act_cmd_cti_cas a, int_comandos_cti_cas c, int_actividades t 
				WHERE c.id = a.id_comando AND 
				t.id = a.id_actividad AND 
				c.estado = 'A' AND 
				a.estado = 'A' and 
				t.id = $idTabla";
		$i = 0;
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				do {
					$comandoCti[$i] = (int) $oCon->f('id_comando');
					$nombreComando[$i] = $oCon->f('comando');
					$descripcion[$i] = $oCon->f('descripcion');
					$longitudComando[$i] = $oCon->f('longitud_bytes');
					$infoAdd[$i] = $oCon->f('observaciones');
					$obliga[$i] = $oCon->f('obligatorio');
					$i++;
				} while ($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();
		$pos = 0;
		$j = 0;
		do {
			$comando = hexdec(substr($reading, $pos, 4));
			$pos = $pos + 4;
			for ($i = 0; $i < count($comandoCti); $i++) {
				if ($comandoCti[$i] == $comando) {
					$longitud = (int) substr($reading, $pos, 4);
					$longitud = hexdec($longitud) * 2;
					$pos = $pos + 4;
					if (strpos($infoAdd[$i], 'INT') !== false) {
						$verb['' . $nombreComando[$i] . ''][$j] = hexdec(substr($reading, $pos, $longitud));
					} else {
						$verb['' . $nombreComando[$i] . ''][$j] = pack('H*', substr($reading, $pos, $longitud));
					}
					$pos = $pos + $longitud;
					$i = count($comandoCti);
				}
			}
			$j++;
		} while ($pos < strlen($reading));
		return $verb;
	}

	function errorHandler($errno, $errstr, $file, $line)
	{
		$oReturn = $this->oReturn;
		$userInfo = "Error: [$errno] $errstr in line $line";
		$oReturn->alert($userInfo);
		return $oReturn;
	}

	function excepcionFunc()
	{

		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

		$oReturn = $this->oReturn;
		$accionPrevia = $this->accionPrevia;
		$idLog = $this->idLog;
		$tipo = $this->tipo;

		if ($accionPrevia === true || empty($accionPrevia)) {
			$userInfo = $this->userInfo;
			$oCon = $this->oCon;
			$subId = $this->subId;
			$sql = "UPDATE int_log_cti_cas set estado = 'ER', info = 'Excepcion : $userInfo' where sub_id = $subId and id = $idLog";
			$oCon->QueryT($sql);
			$this->accionPrevia = false;
		}

		if ($tipo === 1) {
			return $userInfo;
		} else {
			throw new Exception('Error: ' . $userInfo);
			return $oReturn;
		}
	}
}
