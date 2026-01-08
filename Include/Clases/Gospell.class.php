<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class Gospell
{

	var $conMSS;
	var $conMSS_Read;
	var $infoCAS;
	var $subId;
	var $userInfo;
	var $oCon;
	var $oConTr;
	var $oReturn;
	var $accionPrevia;
	var $idLog;
	var $msj;
	var $tipo;

	function Gospell($oCon, $oReturn, $tipo = 0)
	{

		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

		unset($infoCAS);

		$infoCAS['empresa'] = $_SESSION['U_EMPRESA'];
		$infoCAS['sucursal'] = $_SESSION['U_SUCURSAL'];
		$infoCAS['userConected'] = $_SESSION['U_NOMBRECOMPLETO'];

		$sql = "select d.id, d.modelo, d.software, d.serial, d.hostname, d.ip, d.port, d.user, d.user_password
				from int_sistemas s, int_marcas_dispositivos m , int_dispositivos d
				where d.estado = 'A' and 
				m.id_sistema = s.id and
				d.id_marca = m.id and
				d.id_empresa = " . $infoCAS['empresa'] . " and
				s.sistema = 'TELECABLE' and
				m.marca =  'GOSPELL'";

		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				$infoCAS['id'] = $oCon->f('id');
				$infoCAS['modelo'] = $oCon->f('modelo');
				$infoCAS['version'] = $oCon->f('software');
				$infoCAS['dbase'] = $oCon->f('hostname');
				$infoCAS['ip'] = $oCon->f('ip');
				$infoCAS['port'] = $oCon->f('port');
				$infoCAS['user'] = $oCon->f('user');
				$infoCAS['password'] = $oCon->f('user_password');
			}
		}
		$oCon->Free();

		$this->infoCAS = $infoCAS;
		$this->oReturn = $oReturn;
		$this->oCon = $oCon;
		$this->tipo = $tipo;

		return $oReturn;
	}

	function conexion()
	{

		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

		$infoCAS = $this->infoCAS;
		$oReturn = $this->oReturn;
		$oCon = $this->oCon;

		//Nueva conexion MySql para manejar transaccionalidad
		/*$oConTr = mysqli_init();
		if (!$oConTr) {
			$userInfo =  "Error al inicializar MySQL";
			$this->userInfo = $userInfo;
			$this->excepcionFunc();
		} /*elseif(!$oConTr->options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 0')){
			$userInfo =  "Error al configurar conexion MySQL";
			$this->userInfo = $userInfo;
			$this->excepcionFunc();
		} */ /*elseif (!$oConTr->real_connect('127.0.0.1', 'root', 'satlantico19', 'atlantico')) {
			$userInfo = 'No se pudo conectar a mysql (' . mysqli_connect_errno() . ') ' . mysqli_connect_error();
			$this->userInfo = $userInfo;
			$this->excepcionFunc();
		} elseif (!$oConTr->autocommit(false)) {
			$userInfo = 'No se pudo generar transaccionalidad en la base de mysql';
			$this->userInfo = $userInfo;
			$this->excepcionFunc();
		}
		$this->oConTr = $oConTr;*/

		/* //Ejemplo Consulta
		$resultado = $oConTr->query("SELECT * from int_actividades where id = 64");
		if($resultado !== false){
			$numFilas = $resultado->num_rows;
			if($numFilas > 0){
				$i = 0;
				do{ 
					$resultado->data_seek($i);
					$filas[$i] = $resultado->fetch_assoc();
					$actividad[$i] = $filas[$i]['actividad'];
					$i++;
				} while ($i <= $numFilas);
			}
		}
		*/


		//Log de la conexion 

		$subId = 1;
		$this->subId = $subId;
		$userInfo = "Conectando al servidor GOSPELL";
		$this->userInfo = $userInfo;
		$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, ejecucion)
	           VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "' ,'$userInfo')";
		$oCon->QueryT($sql);
		$sql = "SELECT max(id) as id from int_log_gospell_cas";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				$idLog = $oCon->f('id');
				$this->idLog = $idLog;
			}
		}
		$oCon->Free();
		$infoCAS['id_conexion'] = $idLog;

		$sql = 'UPDATE int_log_gospell_cas SET id_conexion = ' . $infoCAS['id_conexion'] . ' where id = ' . $infoCAS['id_conexion'] . '';
		$oCon->QueryT($sql);

		$conn = odbc_pconnect("DRIVER=SQL Server;SERVER=" . $infoCAS['ip'] . ";UID=" . $infoCAS['user'] . ";PWD=" . $infoCAS['password'] . "; DATABASE=" . $infoCAS['dbase'] . "; Address=" . $infoCAS['user'] . "," . $infoCAS['port'] . "", "" . $infoCAS['user'] . "", "" . $infoCAS['password'] . "");
		if ($conn !== false) {
			$autocomm = odbc_autocommit($conn, false);
			$this->conMSS = $conn;
			if (!$autocomm) {
				$this->conMSS = $conn;
				$userInfo = "No se pudo generar una transaccion en la base $base";
				$this->userInfo = $userInfo;
				$this->excepcionFunc();
			}
			$base = 'CAS';
			$connRead = odbc_pconnect("DRIVER=SQL Server;SERVER=" . $infoCAS['ip'] . ";UID=" . $infoCAS['user'] . ";PWD=" . $infoCAS['password'] . "; DATABASE=" . $base . "; Address=" . $infoCAS['user'] . "," . $infoCAS['port'] . "", "" . $infoCAS['user'] . "", "" . $infoCAS['password'] . "");
			$this->conMSS_Read = $connRead;
			if ($connRead !== false) {
				$sql = "UPDATE int_log_gospell_cas set estado = 'OK' where sub_id = $subId and id = $idLog";
				$oCon->QueryT($sql);
			} else {
				$userInfo = "No se pudo conectar a la base $base de lectura correctamente";
				$this->userInfo = $userInfo;
				$this->excepcionFunc();
			}
		} else {
			$userInfo = "No se pudo conectar a la base " . $infoCAS['dbase'] . " correctamente";
			$this->userInfo = $userInfo;
			$this->excepcionFunc();
		}
		$this->infoCAS = $infoCAS;

		return $oReturn;
	}

	function servicios($idActividad, $tarjetas, $verb = null, $id_peticion = 0)
	{


		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

		$oReturn = $this->oReturn;
		$oCon = $this->oCon;
		$conn = $this->conMSS;
		$connRead = $this->conMSS_Read;
		$infoCAS = $this->infoCAS;
		$subId = $this->subId;
		$accionPrevia = $this->accionPrevia;

		if ($accionPrevia === false && !empty($accionPrevia)) {
			$this->excepcionFunc();
		}

		try {
			$sql = "SELECT p.id, t.id_cas from int_perfiles_gospell t, int_paquetes p, int_paquetes_perfiles e  WHERE e.id_paquete = p.id AND t.id = e.id_perfil AND t.estado = 'A' GROUP BY t.id_cas";
			if ($oCon->Query($sql)) {
				if ($oCon->NumFilas() > 0) {
					do {
						$paquetesGospell[$oCon->f('id')] = $oCon->f('id_cas');
						$paquetesSistema[$oCon->f('id_cas')] = $oCon->f('id');
					} while ($oCon->SiguienteRegistro());
				}
			}
			$oCon->Free();

			if (empty($connRead) || empty($conn)) {
				$userInfo = "No se realizo previamente una conexion correcta con el CAS";
				$this->userInfo = $userInfo;
				$this->excepcionFunc();
			}

			$sql = "SET @id = ( SELECT MIN(a.id)-1
					FROM int_actividades a, int_dispositivos d
					WHERE a.id_dispositivo = d.id AND a.id_dispositivo = " . $infoCAS['id'] . ");";
			$oCon->QueryT($sql);
			$sql = "SELECT a.id-@id AS id, a.id AS id_tabla, a.actividad, a.info
					FROM int_actividades a, int_dispositivos d
					WHERE a.id_dispositivo = d.id AND 
					a.id_dispositivo = " . $infoCAS['id'] . " AND 
					a.estado = 'A' AND 
					a.id-@id = " . $idActividad . "";
			if ($oCon->Query($sql)) {
				if ($oCon->NumFilas() > 0) {
					$idActividad = $oCon->f('id');
					$actividad = $oCon->f('actividad');
					$idTabla = $oCon->f('id_tabla');
					$base = (int) $oCon->f('info');
				}
			}
			$oCon->Free();

			$sql = "select `query` from int_comandos_gospell c where id_actividad = $idTabla and estado = 'A'";
			if ($oCon->Query($sql)) {
				if ($oCon->NumFilas() > 0) {
					$query = $oCon->f('query');
				}
			}
			$oCon->Free();

			if (!is_array($tarjetas)) {
				$tarjetas = array($tarjetas);
			}

			for ($i = 0; $i < count($tarjetas); $i++) {

				$verb['tarjeta'] = $tarjeta = (string) $tarjetas[$i];
				$tar = (string) $tarjetas[$i];

				if(!empty($id_peticion)){
					$sql = "SELECT id_equipo FROM isp.int_red_peticiones WHERE id = $id_peticion";
					$id_equipo = consulta_string_func($sql, 'id_equipo', $oCon, 0);

					$sql = "SELECT c.id, c.id_clpv, c.id_contrato 
							from int_contrato_caja c 
							where c.id = $id_equipo";
					if ($oCon->Query($sql)) {
						if ($oCon->NumFilas() > 0) {
							$idCaja = $oCon->f('id');
							$idClpv = $oCon->f('id_clpv');
							$idContrato = $oCon->f('id_contrato');
						}
					}
					$oCon->Free();
				}else{
					$sql = "SELECT c.id, c.id_clpv, c.id_contrato 
							from int_contrato_caja c 
							where c.id_tarjeta = '$tarjeta'";
					if ($oCon->Query($sql)) {
						if ($oCon->NumFilas() > 0) {
							$idCaja = $oCon->f('id');
							$idClpv = $oCon->f('id_clpv');
							$idContrato = $oCon->f('id_contrato');
						}
					}
					$oCon->Free();
				}

				if (empty($idCaja)) {
					$idCaja = 'null';
				}

				$subId++;
				$this->subId = $subId;
				$userInfo = "$actividad : " . $tarjeta . "";
				$this->userInfo = $userInfo;

				$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, id_equipo, ejecucion)
						VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . " ,$idCaja ,'$userInfo')";
				$oCon->QueryT($sql);

				$sql = "SELECT max(id) as id from int_log_gospell_cas";
				if ($oCon->Query($sql)) {
					if ($oCon->NumFilas() > 0) {
						$idLog = $oCon->f('id');
						$this->idLog = $idLog;
					}
				}
				$oCon->Free();

				if (!empty($id_peticion)) {
					$id_conexion = $infoCAS['id_conexion'];
					$sql = "UPDATE isp.int_red_peticiones SET id_log = $id_conexion WHERE id = $id_peticion";
					$oCon->QueryT($sql);
				}

				//var_dump($idActividad);
				switch ($idActividad) {

					case 1: //Asignar tarjeta
						$ZoneNum  = 65536;

						$idContrato = $verb['idContrato'];

						//query contrato
						$sql = "SELECT codigo, nom_clpv, id_sucursal, abonado 
								FROM contrato_clpv WHERE id = $idContrato";
						if ($oCon->Query($sql)) {
							if ($oCon->NumFilas() > 0) {
								$AccountNum = $oCon->f('abonado');
								$FullName = $oCon->f('nom_clpv');
								$ZipCode = $oCon->f('id_sucursal');
								$DocumentNO = $oCon->f('codigo');
							}
						}
						$oCon->Free();

						$sql = "SELECT ConverterID FROM dbo.SubscriberInfo WHERE AccountNum = '" . $AccountNum . "' AND DocumentNO = '" . $DocumentNO . "'";
						$consulta = odbc_exec($conn, $sql);
						if ($consulta === false) {
							$userInfo = "No se pudo ejecutar la consulta correctamente: " . odbc_errormsg($conn);
							$this->userInfo = $userInfo;
							$this->excepcionFunc();
						} else {
							if (odbc_num_rows($consulta) == 0) {
								$sql = "INSERT INTO dbo.SubscriberInfo (AccountNum, FullName, ZipCode, ZoneNum, DocumentNO, CreateDate) VALUES ('$AccountNum', '$FullName', '$ZipCode', $ZoneNum, '$DocumentNO' , GETDATE())";
								if (odbc_exec($conn, $sql)) {
									if (odbc_num_rows($consulta) <> 1) {
										$userInfo = "No se pudo crear el suscriptor correctamente: " . odbc_errormsg($conn);
										$this->userInfo = $userInfo;
										$this->excepcionFunc();
									} else {
										$sql = "SELECT max(ConverterID) as ConverterID from dbo.SubscriberInfo";
										if (odbc_exec($conn, $sql)) {
											if (odbc_fetch_row($consulta)) {
												$ConverterID = odbc_result($consulta, 'ConverterID');
											} else {
												$userInfo = "Error (MSSServer): No se pudo obtener la fila solicitada: " . odbc_errormsg($conn);
												$this->userInfo = $userInfo;
												$this->excepcionFunc();
											}
										} else {
											$userInfo = "Error (MSSServer): No se pudo ejecutar la consulta: " . odbc_errormsg($conn);
											$this->userInfo = $userInfo;
											$this->excepcionFunc();
										}
									}
								} else {
									$userInfo = "Error (MSSServer): No se pudo crear el suscriptor correctamente: " . odbc_errormsg($conn);
									$this->userInfo = $userInfo;
									$this->excepcionFunc();
								}
							} else {
								if (odbc_fetch_row($consulta)) {
									$ConverterID = odbc_result($consulta, 'ConverterID');
								} else {
									$userInfo = "Error (MSSServer): No se pudo obtener una fila de la consulta: " . odbc_errormsg($conn);
									$this->userInfo = $userInfo;
									$this->excepcionFunc();
								}
							}
							if ($ConverterID === false || empty($ConverterID)) {
								$userInfo = "No se pudo obtener el id del suscriptor correctamente";
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							} else {
								$verb['ConverterID'] = $ConverterID;
							}
						}

						$idUbicacion = $verb['idUbicacionEquipo'];
						$ubicacion = strtoupper($verb['ubicacionEquipo']);
						$idMarca = (int) $verb['idMarcaEquipo'];

						$consulta = $this->envioComando($query, $verb);

						if ($consulta === false) {
							$userInfo = "No se pudo enviar el comando correctamente: " . odbc_errormsg($conn);
							$this->userInfo = $userInfo;
							$this->excepcionFunc();
						} else {
							$consulta = odbc_num_rows($consulta);
							if ($consulta <> 1) {
								if ($consulta === false) {
									$userInfo = "Se envió el comando correctamente pero no se modifico en la interfaz de GOSPELL (" . odbc_errormsg($conn) . ")";
								} else {
									$userInfo = "El comando cambio en el CAS mas valores que los necesarios. Se revertira el proceso";
								}
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							} else {

								//colocar codigo exitoso
							}
						}

						//$bddMysql = $oConTr->commit();
						$bddMsserver = odbc_commit($conn);
						if ($bddMsserver) {
							$sql = " UPDATE int_log_gospell_cas set estado = 'OK', id_equipo = $idCaja  where sub_id = $subId and id = $idLog";
							$oCon->QueryT($sql);
							if ($verb['fin'] === true) {
								if ($this->tipo === 1) {
									$this->msj = 'El comando se ejecuto correctamente';
								} else {
									$oReturn->alert('El comando se ejecuto correctamente');
								}
							}
						} else {
							$userInfo = "No se pudo terminar la transaccion del CAS. Se cambio en el Sistema pero no en el CAS";
							$this->userInfo = $userInfo;
							$this->excepcionFunc();
						}
						break;

					case 2: //Eliminar tarjeta

						$consulta = $this->envioComando($query, $verb);

						if ($consulta === false) {
							$userInfo = "No se pudo enviar el comando correctamente: " . odbc_errormsg($conn);
							$this->userInfo = $userInfo;
							$this->excepcionFunc();
						} else {
							$consulta = odbc_num_rows($consulta);
							if ($consulta <> 1) {
								if ($consulta === false) {
									$userInfo = "Se envió el comando correctamente pero no se modifico en la interfaz de GOSPELL (" . odbc_errormsg($conn) . ")";
								} else {
									$userInfo = "El comando cambio en el CAS mas valores que los necesarios. Se revertira el proceso";
								}
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							} else {
							
							}
						}

						//$bddMysql = $oConTr->commit();
						$bddMsserver = odbc_commit($conn);
						if ($bddMsserver) {
							$sql = " UPDATE int_log_gospell_cas set estado = 'OK', id_equipo = $idCaja  where sub_id = $subId and id = $idLog";
							$oCon->QueryT($sql);
							if ($verb['fin'] === true) {
								$oReturn->alert('El comando se ejecuto correctamente');
							}
						} else {

							$userInfo = "No se pudo terminar la transaccion del CAS. Se cambio en el Sistema pero no en el CAS";
							$this->userInfo = $userInfo;
							$this->excepcionFunc();
						}
						break;

					case 4: //Reconectar tarjeta
					case 3: //Suspender tarjeta

						$consulta = $this->envioComando($query, $verb);

						if ($consulta === false) {
							$userInfo = "No se pudo enviar el comando correctamente: " . odbc_errormsg($conn);
							$this->userInfo = $userInfo;
							$this->excepcionFunc();
						} else {
							$consulta = odbc_num_rows($consulta);
							if ($consulta <> 1) {
								if ($consulta === false) {
									$userInfo = "Se envió el comando correctamente pero no se modifico en la interfaz de GOSPELL (" . odbc_errormsg($conn) . ")";
								} elseif ($consulta == 0) {
									$userInfo = "Ya no existe la tarjeta en el CAS";
								} else {
									$userInfo = "El comando cambio en el CAS mas valores que los necesarios. Se revertira el proceso";
								}
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							} else {
							}
						}

						//$bddMysql = $oConTr->commit();
						$bddMsserver = odbc_commit($conn);
						if ($bddMsserver) {
							$sql = " UPDATE int_log_gospell_cas set estado = 'OK', id_equipo = $idCaja  where sub_id = $subId and id = $idLog";
							$oCon->QueryT($sql);
							if ($verb['fin'] === true) {
								if ($this->tipo === 1) {
									$this->msj = 'El comando se ejecuto correctamente';
								} else {
									$msj = 'El comando se ejecuto correctamente';
								}
							}
						} else {
							$userInfo = "No se pudo terminar la transaccion del CAS. Se cambio en el Sistema pero no en el CAS";
							$this->userInfo = $userInfo;
							$this->excepcionFunc();
						}
						break;

					case 5: //Cancelar paquete

						if ($idActividad == 5) {
							$paquetes = $verb['paquetes'];
							if (!is_array($paquetes)) {
								$paquetes = $verb['paquetes'] = array($paquetes);
							}
						}
						$verb['receiptid'] = '' . date("Y") . date("m") . date("d") . date("H") . date("i") . date("s") . '';
						$verb['InnerID'] = substr($tarjeta, 8, strlen($tarjeta) - 8);
						$userInfoErr = "Error en un paquete";

						//fecha de desconexion de paquetes
						$verb['startdate'] = date("Y-m-").'01 '.date("H:i:s").'.000';
						$verb['enddate'] = date("Y-m-").'01 '.date("H:i:s").'.000';

						for ($j = 0; $j < count($paquetes); $j++) {
							$verb['productid'] = $paquetesGospell[$paquetes[$j]];

							$userInfo = "$actividad : " . $tarjeta . " (" . $verb['productid'] . ")";
							$this->userInfo = $userInfo;
							$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, id_equipo, ejecucion)
									VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . " ,$idCaja ,'$userInfo')";
							$oCon->QueryT($sql);
							$sql = "SELECT max(id) as id from int_log_gospell_cas";
							if ($oCon->Query($sql)) {
								if ($oCon->NumFilas() > 0) {
									$idLog_1 = $oCon->f('id');
								}
							}
							$oCon->Free();

							$sql = "DELETE FROM AuthTab_2020 WHERE cardid = '" . $tarjeta . "' AND productid = ".$verb['productid']."";
							$sql_g = odbc_exec($conn, $sql);

							/*$sql = "select ProductEndTime from AccreditLog a where a.CardID = '" . $verb['InnerID'] . "' and 
									a.ProductStartTime <= CURRENT_DATE_TIMESTAMP and a.ProductStartTime != ProductEndTime and
									a.ProductNumber = " . $verb['productid'] . " and a.ProductEndTime >= CURRENT_DATE_TIMESTAMP AND
									a.ProductEndTime not in (select t.ProductStartTime from AccreditLog t where t.ProductStartTime = t.ProductEndTime AND
									t.AccreditLogId > a.AccreditLogId and t.CardID = a.CardID and t.ProductNumber = a.ProductNumber);";

							$queryR = odbc_exec($connRead, $sql);
							$filasAfectadas = odbc_num_rows($queryR);
							if ($filasAfectadas  == 1) {
								if (odbc_fetch_row($queryR)) {
									$enddate = odbc_result($queryR, 'ProductEndTime');
									if ($enddate === false) {
										$userInfo = "(MSSServer) Error al mostrar el resultado de la consulta";
										$sql = "UPDATE int_log_gospell_cas set estado = 'ER', info = 'Exception : $userInfo' where sub_id = $subId and id = $idLog_1";
										$oCon->QueryT($sql);
										$this->userInfo = $userInfoErr;
										$this->excepcionFunc();
									} else {
										if (empty($enddate)) {
											$userInfo = "El paquete ya se encontaba inactivo";
											$sql = "UPDATE int_log_gospell_cas set estado = 'ER', info = 'Exception : $userInfo' where sub_id = $subId and id = $idLog_1";
											$oCon->QueryT($sql);
											$this->userInfo = $userInfoErr;
											$this->excepcionFunc();
										} else {
											$verb['enddate'] = $enddate;
										}
									}
								} else {
									$userInfo  = "(MSSServer) No se pudo capturar la fila de la consulta";
									$sql = "UPDATE int_log_gospell_cas set estado = 'ER', info = 'Exception : $userInfo' where sub_id = $subId and id = $idLog_1";
									$oCon->QueryT($sql);
									$this->userInfo = $userInfoErr;
									$this->excepcionFunc();
								}
							} else {
								if ($filasAfectadas === false) {
									$userInfo  = "Error: " . odbc_errormsg($connRead) . ". No se pudo encontrar registro del paquete en el sistema del CAS ";
								} else {
									$userInfo = "Lineas encontradas: $filasAfectadas. Se encontro mas de un registro del paquete en el CAS";
								}
								$sql = "UPDATE int_log_gospell_cas set estado = 'ER', info = 'Exception : $userInfo' where sub_id = $subId and id = $idLog_1";
								$oCon->QueryT($sql);
								$this->userInfo = $userInfoErr;
								$this->excepcionFunc();
							}*/
							//var_dump($query);
							//var_dump($verb);
							$consulta = $this->envioComando($query, $verb);

							if ($consulta === false) {
								$userInfo = "No se pudo enviar el comando correctamente: " . odbc_errormsg($conn);
								$sql = "UPDATE int_log_gospell_cas set estado = 'ER', info = 'Exception : $userInfo' where sub_id = $subId and id = $idLog_1";
								$oCon->QueryT($sql);
								$this->userInfo = $userInfoErr;
								$this->excepcionFunc();
							} else {
								$rows = odbc_num_rows($consulta);
								$estado = $verb['estado'];
								if ($rows <> 1) {
									$userInfo = "No se pudo enviar el comando correctamente: " . odbc_errormsg($conn);
									$sql = "UPDATE int_log_gospell_cas set estado = 'ER', info = 'Exception : $userInfo' where sub_id = $subId and id = $idLog_1";
									$oCon->QueryT($sql);
									$this->userInfo = $userInfoErr;
									$this->excepcionFunc();
								} else {
								}
							}
						}

						//$bddMysql = $oConTr->commit();
						$bddMsserver = odbc_commit($conn);
						if ($bddMsserver) {
							$sql = " UPDATE int_log_gospell_cas set estado = 'OK', id_equipo = $idCaja  where sub_id = $subId and id = $idLog";
							$oCon->QueryT($sql);
							if ($verb['fin'] === true) {
								if ($this->tipo === 1) {
									$this->msj = 'Actividad ejecutada Exitosamente';
								} else {
									$oReturn->alert('El comando se ejecuto correctamente');
								}
							}
						} else {
							$userInfo = "No se pudo terminar la transaccion del CAS. Se cambio en el Sistema pero no en el CAS";
							$this->userInfo = $userInfo;
							$this->excepcionFunc();
						}
						break;

					case 6: //Añadir paquete

						$paquetes = $verb['paquetes'];
						unset($verb['paquetes']);
						if (!is_array($paquetes)) {
							$paquetes = $verb['paquetes'] = array($paquetes);
						}

						$precios = $verb['precios'];
						unset($verb['precios']);
						if (!is_array($precios)) {
							$precios = array($precios);
						}

						unset($startdates);
						if (!is_array($verb['startdate'])) {
							for ($j = 0; $j < count($paquetes); $j++) {
								$startdates[$j] = $verb['startdate'];
							}
						} else {
							for ($j = 0; $j < count($paquetes); $j++) {
								$startdates[$j] = $verb['startdate'][$j];
							}
						}
						unset($verb['startdate']);

						unset($enddates);
						if (!is_array($verb['enddate'])) {
							for ($j = 0; $j < count($paquetes); $j++) {
								$enddates[$j] = $verb['enddate'];
							}
						} else {
							for ($j = 0; $j < count($paquetes); $j++) {
								$enddates[$j] = $verb['enddate'][$j];
							}
						}

						unset($verb['enddate']);

						$verb['receiptid'] = '' . date("Y") . date("m") . date("d") . date("H") . date("i") . date("s") . '';
						$verb['InnerID'] = substr($tarjeta, 8, strlen($tarjeta) - 8);

						//consulta si equipo esta sincronizado a formato actual
						$sql = "SELECT id_cmd, id_equipo FROM isp.int_red_peticiones WHERE id = $id_peticion";
						$id_cmd = consulta_string_func($sql, 'id_cmd', $oCon, 0);

						if($id_cmd == 2 || $id_cmd == 3){
							$id_equipo = consulta_string_func($sql, 'id_equipo', $oCon, 0);

							$sql = "SELECT sin_gospell FROM int_contrato_caja WHERE id = $id_equipo";
							$sin_gospell = consulta_string_func($sql, 'sin_gospell', $oCon, 'S');

							if($sin_gospell == 'N'){
								$sql = "DELETE FROM AuthTab_2020 WHERE cardid = '" . $tarjeta . "'";
								$sql_g = odbc_exec($conn, $sql);
								if($sql_g){
									$sql = "UPDATE int_contrato_caja SET sin_gospell = 'S' WHERE id = $id_equipo";
									$oCon->QueryT($sql);
								}
							}
						}

						for ($j = 0; $j < count($paquetes); $j++) {
							$verb['startdate'] = $startdate = $startdates[$j] . '.000';
							$verb['enddate'] = $enddate =  $enddates[$j] . '.000';
							$verb['productid'] = $paquetesGospell[$paquetes[$j]];

							$userInfo = "$actividad : " . $tarjeta . " (" . $verb['productid'] . ")";
							$this->userInfo = $userInfo;
							$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, id_equipo, ejecucion)
									VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . " ,$idCaja ,'$userInfo')";
							$oCon->QueryT($sql);
							$sql = "SELECT timediff('$startdate',NOW()) as posf, max(id) as id from int_log_gospell_cas";
							if ($oCon->Query($sql)) {
								if ($oCon->NumFilas() > 0) {
									$idLog_1 = $oCon->f('id');
									$posf = $oCon->f('posf');
								}
							}
							$oCon->Free();

							/*
							//AQUI HAY QUE ANALIZAR POSF PARA ENVIAR EL COMANDO A LAS PETICIONES AUTOMATICAS
							//Hago una verificacion si existe algun paquete activo o por activar para el paquete en el cas
							$sql = "select DATEDIFF(second, CURRENT_DATE_TIMESTAMP, ProductStartTime) as diferStart, ProductStartTime, ProductEndTime 
									from AccreditLog a where a.CardID = '" . $verb['InnerID'] . "' and a.ProductStartTime != ProductEndTime and a.ProductNumber = " . $verb['productid'] . " and 
									a.ProductEndTime >= CURRENT_DATE_TIMESTAMP AND a.ProductEndTime not in (select t.ProductStartTime from AccreditLog t
									where t.ProductStartTime = t.ProductEndTime AND t.AccreditLogId > a.AccreditLogId and t.CardID = a.CardID and t.ProductNumber = a.ProductNumber);";
							$queryR = odbc_exec($connRead, $sql);
							
							$filasAfectadas = odbc_num_rows($queryR);
							$userInfo = '';
							$filasAfectadas = 0;
							if ($filasAfectadas <> 0) {
								$q = 1;
								$activo<s = 0;
								$posfechados = 0;
								while (odbc_fetch_row($queryR)) {
									$startdateP[$q] = odbc_result($queryR, 'ProductStartTime');
									$enddateP[$q] = odbc_result($queryR, 'ProductEndTime');
									$diferStart[$q] = odbc_result($queryR, 'diferStart');
									if ($diferStart[$q] < 0) {
										$userInfo .= 'Se encontro un paquete activado desde ' . $startdateP[$q] . ' hasta  ' . $enddateP[$q] . ' para el paquete ' . $verb['productid'] . '' . PHP_EOL;
										$activos++;
									} else {
										$userInfo .= 'Alerta: Se encontro una activacion posfechada desde ' . $startdateP[$q] . ' hasta  ' . $enddateP[$q] . ' para el paquete ' . $verb['productid'] . '' . PHP_EOL;
										$posfechados++;
									}
									$q++;
								}
								if ($activos <> 0) {
									$this->userInfo = $userInfo;
									$this->excepcionFunc();
								} elseif ($posfechados <> 0) {
									//$oReturn->alert($userInfo);
								}
							}*/

							$consulta = $this->envioComando($query, $verb);
							
							//$consulta = false;
							if ($consulta === false) {
								$userInfo = "No se pudo enviar el comando correctamente: " . odbc_errormsg($conn);
								$sql = "UPDATE int_log_gospell_cas set estado = 'ER', info = 'Exception : $userInfo' where sub_id = $subId and id = $idLog_1";
								$oCon->QueryT($sql);
								$this->userInfo = $userInfoErr;
								$this->excepcionFunc();
							} else {
								$rows = odbc_num_rows($consulta);
								$estado = $verb['estado'];
								$precio = $precios[$j];
								if ($rows <> 1) {
									$userInfo = "No se pudo enviar el comando correctamente: " . odbc_errormsg($conn);
									$sql = "UPDATE int_log_gospell_cas set estado = 'ER', info = 'Exception : $userInfo' where sub_id = $subId and id = $idLog_1";
									$oCon->QueryT($sql);
									$this->userInfo = $userInfoErr;
									$this->excepcionFunc();
								} else {
								}
							}
						}

						$bddMsserver = odbc_commit($conn);
						if ($bddMsserver) {
							$sql = " UPDATE int_log_gospell_cas set estado = 'OK', id_equipo = $idCaja  where sub_id = $subId and id = $idLog";
							$oCon->QueryT($sql);
							if ($verb['fin'] === true) {
								if ($this->tipo === 1) {
									$this->msj = 'Actividad ejecutada Exitosamente';
								} else {
									$oReturn->alert('El comando se ejecuto correctamente');
								}
							}
						} else {
							$userInfo = "No se pudo terminar la transaccion del CAS. Se cambio en el Sistema pero no en el CAS";
							$this->userInfo = $userInfo;
							$this->excepcionFunc();
						}
						break;

					case 7: //Cancelar todos los paquetes
						$estado = $verb['estado'];
						$verb['receiptid'] = '' . date("Y") . date("m") . date("d") . date("H") . date("i") . date("s") . '';
						$verb['InnerID'] = substr($tarjeta, 8, strlen($tarjeta) - 8);
						/*unset($paquetes);
						$verb['productid'] = '';
						$paquetes = array_values($paquetesGospell);
						for ($j = 0; $j < count($paquetesGospell); $j++) {
							$paquete = $paquetes[$j];
							
							$sql = "select ProductNumber, ProductEndTime from AccreditLog a where a.CardID = '" . $verb['InnerID'] . "' and 
									a.ProductStartTime <= CURRENT_DATE_TIMESTAMP and a.ProductStartTime != ProductEndTime and
									a.ProductNumber = " . $paquete . " and a.ProductEndTime >= CURRENT_DATE_TIMESTAMP AND
									a.ProductEndTime not in (select t.ProductStartTime from AccreditLog t where t.ProductStartTime = t.ProductEndTime AND
									t.AccreditLogId > a.AccreditLogId and t.CardID = a.CardID and t.ProductNumber = a.ProductNumber);";
							$queryR = odbc_exec($connRead, $sql);
							if ($queryR === false) {
								$userInfo = "Error (MSSServer) en la consulta: " . odbc_errormsg($connRead);
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							} else {
								$filasAfectadas = odbc_num_rows($queryR);
								if ($filasAfectadas > 0) {
									//if ($filasAfectadas  > 1) {
										//$oReturn->alert('Se eliminara mas de una autorizacion para el paquete ' . $paquete . '.');
									//}
									while (odbc_fetch_row($queryR)) {
										$verb['productid'] = odbc_result($queryR, 'ProductNumber');
										$verb['enddate'] = odbc_result($queryR, 'ProductEndTime');
										$consulta = $this->envioComando($query, $verb);
										if ($consulta === false) {
											$userInfo = "No se pudo enviar el comando correctamente: " . odbc_errormsg($connRead);
											$this->userInfo = $userInfo;
											$this->excepcionFunc();
										} else {
											$rows = odbc_num_rows($consulta);
											if ($rows <> 1) {
												$userInfo = "No se pudo enviar el comando correctamente: " . odbc_errormsg($connRead);
												$this->userInfo = $userInfo;
												$this->excepcionFunc();
											} else {
											}
										}
									}
								}
							}
						}*/
					
						/*$consulta = $this->envioComando($query, $verb);		
						echo $query;
						var_dump($verb);
						var_dump($consulta);
						if ($consulta === false) {
							$userInfo = "No se pudo enviar el comando correctamente: " . odbc_errormsg($conn);
							$this->userInfo = $userInfo;
							$this->excepcionFunc();
						} else {
							$consulta = odbc_num_rows($consulta);
							if ($consulta <> 1) {
								if ($consulta === false) {
									$userInfo = "Se envió el comando correctamente pero no se modifico en la interfaz de GOSPELL (" . odbc_errormsg($conn) . ")";
								} else {
									$userInfo = "El comando cambio en el CAS mas valores que los necesarios. Se revertira el proceso";
								}
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							} else {
							
							}
						}*/

						$sql = "DELETE FROM AuthTab_2020 WHERE cardid = '" . $tarjeta . "'";
						$sql_g = odbc_exec($conn, $sql);

						$bddMsserver = odbc_commit($conn);
						if ($bddMsserver) {
							$sql = " UPDATE int_log_gospell_cas set estado = 'OK', id_equipo = $idCaja  where sub_id = $subId and id = $idLog";
							$oCon->QueryT($sql);
							if ($verb['fin'] === true) {
								if ($this->tipo === 1) {
									$this->msj = 'Actividad ejecutada Exitosamente';
								} else {
									$oReturn->alert('El comando se ejecuto correctamente');
								}
							}
						} else {
							$userInfo = "No se pudo terminar la transaccion del CAS. Se cambio en el Sistema pero no en el CAS";
							$this->userInfo = $userInfo;
							$this->excepcionFunc();
						}
						break;

					case 8: //Consultar paquetes tarjeta

						$verb['InnerID'] = substr($tarjeta, 8, strlen($tarjeta) - 8);
						$q = 0;
						unset($paquetes);
						$paquetes = array_values($paquetesGospell);
						for ($j = 0; $j < count($paquetesGospell); $j++) {
							$paquete = $paquetes[$j];
							$sql = "select ProductNumber, ProductEndTime from AccreditLog a where a.CardID = '" . $verb['InnerID'] . "' and 
									a.ProductStartTime <= CURRENT_DATE_TIMESTAMP and a.ProductStartTime != ProductEndTime and
									a.ProductNumber = " . $paquete . " and a.ProductEndTime >= CURRENT_DATE_TIMESTAMP AND
									a.ProductEndTime not in (select t.ProductStartTime from AccreditLog t where t.ProductStartTime = t.ProductEndTime AND
									t.AccreditLogId > a.AccreditLogId and t.CardID = a.CardID and t.ProductNumber = a.ProductNumber);";
							$queryR = odbc_exec($connRead, $sql);
							if ($queryR === false) {
								$userInfo = "Error (MSSServer) No se pudo realizar la cosulta";
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							} else {
								$filasAfectadas = odbc_num_rows($queryR);
								if (odbc_fetch_row($queryR)) {
									$paquetesL[$q] = $paquetesSistema[odbc_result($queryR, 'ProductNumber')];
									$q++;
								}
							}
						}
						unset($oReturn);
						$oReturn['PACKAGEId'] = $paquetesL;
						$sql = " UPDATE int_log_gospell_cas set estado = 'OK', id_equipo = $idCaja  where sub_id = $subId and id = $idLog";
						$oCon->QueryT($sql);
						break;

					case 10: //Mensaje BMAIL normal
					case 9: //Mensaje OSD normal

						$verb['enddate'] .= '.000';
						$InnerID = (int) substr($tarjeta, 8, strlen($tarjeta) - 8);
						$verb['Condition'] = "cardno=:$InnerID";

						$consulta = $this->envioComando($query, $verb);

						if ($consulta === false) {
							$userInfo = "No se pudo enviar el comando correctamente: " . odbc_error();
							$this->userInfo = $userInfo;
							$this->excepcionFunc();
						} else {
							$rows = odbc_num_rows($consulta);
							if ($rows <> 1) {
								if ($rows > 1) {
									$userInfo = "Se envio el comando mas de una vez. Se revertira el proceso";
								} else {
									$userInfo = "No se envio el comando correctamente ";
								}
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							}
						}
						if (odbc_commit($conn)) {
							$sql = " UPDATE int_log_gospell_cas set estado = 'OK', id_equipo = $idCaja  where sub_id = $subId and id = $idLog";
							$oCon->QueryT($sql);
							if ($verb['fin'] === true) {
								if ($this->tipo === 1) {
									$this->msj = 'Actividad ejecutada Exitosamente';
								} else {
									$oReturn->alert('El comando se ejecuto correctamente');
								}
							}
						} else {
							$userInfo = "No se pudo enviar la transaccion";
							$this->userInfo = $userInfo;
							$this->excepcionFunc();
						}
						break;

					case 18: //Reemplazar tarjeta

						for ($i = 0; $i < count($tarjetas); $i++) {

							$sql = "SELECT c.id from int_contrato_caja c where c.id_tarjeta = '$tarjeta'";
							if ($oCon->Query($sql)) {
								if ($oCon->NumFilas() > 0) {
									$idContrato = $oCon->f('id');
								}
							}
							$oCon->Free();

							$subId++;
							$this->subId = $subId;
							$userInfo = "$actividad : " . $tarjeta . "";
							$this->userInfo = $userInfo;
							$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, id_equipo, ejecucion)
									VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . " ,$idContrato ,'$userInfo')";
							$oCon->QueryT($sql);

							for ($j = 0; $j < count($querys); $j++) {
								//Asignacion de variables
								$query = odbc_exec($conn, $querys[$i]);
							}
						}
						break;

					case 19: //Añadir suscriptor

						for ($i = 0; $i < count($tarjetas); $i++) {

							$tarjeta = $tarjetas[$i];

							$sql = "SELECT c.id from int_contrato_caja c where c.id_tarjeta = '$tarjeta'";
							if ($oCon->Query($sql)) {
								if ($oCon->NumFilas() > 0) {
									$idContrato = $oCon->f('id');
								}
							}
							$oCon->Free();

							$subId++;
							$this->subId = $subId;
							$userInfo = "$actividad : " . $tarjeta . "";
							$this->userInfo = $userInfo;
							$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, id_equipo, ejecucion)
									VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . " ,$idContrato ,'$userInfo')";
							$oCon->QueryT($sql);

							if ($tarjeta == null) {

								$userInfo = "Tarjeta no encontrada";
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							}

							$sql = "select c.id, c.id_sucursal, c.abonado, c.codigo, c.nom_clpv  
									from contrato_clpv c, int_contrato_caja d
									where d.id_contrato = c.id and
									id_tarjeta = '" . $tarjeta . "'";

							if ($oCon->Query($sql)) {
								if ($oCon->NumFilas($sql) > 0) {
									$AccountNum = $oCon->f('codigo'); //str
									$FullName = $oCon->f('nom_clpv'); //str
									$ZipCode = $oCon->f('id_sucursal'); //str
									$ZoneNum = 65536; //int
									$DocumentNO = $oCon->f('abonado'); //str
								}
							}
							$oCon->Free();

							$cambio = '';
							$cambioS = '';
							$inicio = 0;
							$aux = $querys[0];

							$inicio = 0;
							while (strpos($querys[0], '$', $inicio) !== false) {
								$inicio = strpos($querys[0], '$', $inicio);
								$final = strpos($querys[0], '_', $inicio) + 1;
								$cambio = substr($querys[0], $inicio + 1, $final - $inicio - 2);
								//var_dump($cambio);							
								$cambio = ${$cambio};
								$querys[0] = str_replace(substr($querys[0], $inicio, $final - $inicio), $cambio, $querys[0]);
							}

							$query = odbc_exec($conn, $querys[0]);

							$querys[0] = $aux;

							if ($query === false) {

								$userInfo = "No se pudo enviar el comando correctamente: " . odbc_error();
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							} else {

								$query = odbc_num_rows($query);

								if ($query >= 1) {

									$sql = " set @id = (SELECT max(id) from int_log_gospell_cas)";
									$oCon->QueryT($sql);
									$sql = " UPDATE int_log_gospell_cas set estado = 'OK', id_equipo = $idContrato  where sub_id = $subId and id = @id";
									$oCon->QueryT($sql);
								} else {
									$userInfo = "Error";
									$this->userInfo = $userInfo;
									$this->excepcionFunc();
								}
							}
						}

						if ($this->tipo === 1) {
							$this->msj = 'Actividad ejecutada Exitosamente';
						} else {
							$oReturn->alert('El comando se ejecuto correctamente');
						}
						break;

					case 20: //Eliminar suscriptor

						for ($i = 0; $i < count($tarjetas); $i++) {

							$sql = "SELECT c.id from int_contrato_caja c where c.id_tarjeta = '$tarjeta'";
							if ($oCon->Query($sql)) {
								if ($oCon->NumFilas() > 0) {
									$idContrato = $oCon->f('id');
								}
							}
							$oCon->Free();

							$subId++;
							$this->subId = $subId;
							$userInfo = "$actividad : " . $tarjeta . "";
							$this->userInfo = $userInfo;
							$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, id_equipo, ejecucion)
									VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . " ,$idContrato ,'$userInfo')";
							$oCon->QueryT($sql);

							for ($j = 0; $j < count($querys); $j++) {
								//Asignacion de variables
								$query = odbc_exec($conn, $querys[$i]);
							}
						}
						break;

					case 21: //Emparejar tarjeta suscriptor

						for ($i = 0; $i < count($tarjetas); $i++) {

							$sql = "SELECT c.id from int_contrato_caja c where c.id_tarjeta = '$tarjeta'";
							if ($oCon->Query($sql)) {
								if ($oCon->NumFilas() > 0) {
									$idContrato = $oCon->f('id');
								}
							}
							$oCon->Free();

							$subId++;
							$this->subId = $subId;
							$userInfo = "$actividad : " . $tarjeta . "";
							$this->userInfo = $userInfo;
							$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, id_equipo, ejecucion)
									VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . " ,$idContrato ,'$userInfo')";
							$oCon->QueryT($sql);

							for ($j = 0; $j < count($querys); $j++) {
								//Asignacion de variables
								$query = odbc_exec($conn, $querys[$i]);
							}
						}
						break;

					case 22: //Eliminar paquete

						$tarjeta = '';
						$paquetes = $verb['productid'];
						$year = date("Y");
						$receiptid = '' . date("Y") . date("m") . date("d") . date("H") . date("i") . date("s") . '';


						for ($i = 0; $i < count($tarjetas); $i++) {

							$tarjeta = $cardid = $tarjetas[$i];
							$InnerID = (int) substr($cardid, 8, strlen($cardid) - 8);


							for ($j = 0; $j < count($paquetes); $j++) {

								$productid = $paquetes[$j];

								$sql = "SELECT p.fecha_vence, c.id , p.id AS paquete
										FROM int_contrato_caja_pack p, int_contrato_caja c, int_paquetes_perfiles a, int_perfiles_gospell g, int_paquetes u
										WHERE p.id_caja = c.id AND p.cod_prod = u.prod_cod_prod AND a.id_paquete = u.id AND c.id_dispositivo = a.id_dispositivo AND a.id_perfil = g.id AND g.id_cas = $productid AND c.id_tarjeta = '$tarjeta'";
								if ($oCon->Query($sql)) {
									if ($oCon->NumFilas() > 0) {
										$enddate = $oCon->f('fecha_vence');
										$idContrato = $oCon->f('id');
										$idPaquete = $oCon->f('paquete');
									}
								}
								$oCon->Free();

								$subId++;
								$this->subId = $subId;
								$userInfo = "$actividad ($productid) : " . $tarjeta . "";
								$this->userInfo = $userInfo;
								$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, id_equipo, ejecucion)
										VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . " ,$idContrato ,'$userInfo')";
								$oCon->QueryT($sql);

								$cambio = '';
								$cambioS = '';
								$inicio = 0;
								$aux = $querys[0];

								while (strpos($querys[0], '$', $inicio) !== false) {
									$inicio = strpos($querys[0], '$', $inicio);
									$final = strpos($querys[0], '_', $inicio) + 1;
									$cambio = substr($querys[0], $inicio + 1, $final - $inicio - 2);
									$cambioS = ${$cambio};
									$querys[0] = str_replace(substr($querys[0], $inicio, $final - $inicio), $cambioS, $querys[0]);
								}

								//var_dump($querys[0]);
								$query = odbc_exec($conn, $querys[0]);
								$querys[0] = $aux;

								if ($query === false) {

									$userInfo = "No se pudo enviar el comando correctamente: " . odbc_error();
									$this->userInfo = $userInfo;
									$this->excepcionFunc();
								} else {

									$rows = odbc_num_rows($query);

									if ($rows >= 1) {

										$sql = "set @id = (SELECT max(id) from int_log_gospell_cas)";
										$oCon->QueryT($sql);
										$sql = "UPDATE int_log_gospell_cas set estado = 'OK', id_equipo = $idContrato  where sub_id = $subId and id = @id";
										$oCon->QueryT($sql);
										$sql = "DELETE FROM int_contrato_caja_pack WHERE id = $idPaquete";
										$oCon->QueryT($sql);
									} else {

										$userInfo = "No se pudo enviar el comando correctamente: " . odbc_error();
										$this->userInfo = $userInfo;
										$this->excepcionFunc();
									}
								}
							}
						}
						if ($this->tipo === 1) {
							$this->msj = 'Actividad ejecutada Exitosamente';
						} else {
							$oReturn->alert('Actividad ejecutada Exitosamente');
						}
						break;

					case 12: //Cambiar zona

						for ($i = 0; $i < count($tarjetas); $i++) {

							$sql = "SELECT c.id from int_contrato_caja c where c.id_tarjeta = '$tarjeta'";
							if ($oCon->Query($sql)) {
								if ($oCon->NumFilas() > 0) {
									$idContrato = $oCon->f('id');
								}
							}
							$oCon->Free();

							$subId++;
							$this->subId = $subId;
							$userInfo = "$actividad : " . $tarjeta . "";
							$this->userInfo = $userInfo;
							$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, id_equipo, ejecucion)
									VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . " ,$idContrato ,'$userInfo')";
							$oCon->QueryT($sql);

							for ($j = 0; $j < count($querys); $j++) {
								//Asignacion de variables
								$query = odbc_exec($conn, $querys[$i]);
							}
						}
						$oReturn->alert('Actividad ejecutada $this->excepcionFunc()osamente');
						break;

					case 14: //Enviar correo

						for ($i = 0; $i < count($tarjetas); $i++) {

							$Content = $verb['content'];
							$tarjeta = $tarjetas[$i];
							$Title = $verb['asunto'];
							$MType = "Message";

							if ($Title == null) {
								$oReturn->alert('Por favor agregar Asunto');
								$this->excepcionFunc();
							}

							$endInterval = $verb['endInterval']; // valores: yy (anio), qq(trimestre), mm(mes) ,dayofDATE_PART('year', dia del anio), day(dia), ww(semana), dw(dia de la semana), hh(hora), mi(minutos),ss(segundos)
							$eIvalue = $verb['eIvalue'];
							$InnerID = (int) substr($tarjeta, 8, strlen($tarjeta) - 8);
							$Condition = "cardno=:$InnerID";

							$sql = "SELECT c.id from int_contrato_caja c where c.id_tarjeta = '$tarjeta'";
							if ($oCon->Query($sql)) {
								if ($oCon->NumFilas() > 0) {
									$idContrato = $oCon->f('id');
								}
							}
							$oCon->Free();

							$subId++;
							$this->subId = $subId;
							$userInfo = "$actividad : " . $tarjeta . " . Mensaje: $Content";
							$this->userInfo = $userInfo;
							$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, id_equipo, ejecucion)
									VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . " ,$idContrato ,'$userInfo')";
							$oCon->QueryT($sql);

							if ($tarjeta == null) {

								$userInfo = "Tarjeta no encontrada";
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							}

							$cambio = '';
							$cambioS = '';
							$inicio = 0;
							$aux = $querys[0];

							while (strpos($querys[0], '$', $inicio) !== false) {
								$inicio = strpos($querys[0], '$', $inicio);
								$final = strpos($querys[0], '_', $inicio) + 1;
								$cambio = substr($querys[0], $inicio + 1, $final - $inicio - 2);
								$cambioS = ${$cambio};
								$querys[0] = str_replace(substr($querys[0], $inicio, $final - $inicio), $cambioS, $querys[0]);
							}

							//var_dump($querys[0]);
							$query = odbc_exec($conn, $querys[0]);
							$querys[0] = $aux;

							if ($query === false) {

								$userInfo = "No se pudo enviar el comando correctamente: " . odbc_error();
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							} else {

								$sql = " set @id = (SELECT max(id) from int_log_gospell_cas)";
								$oCon->QueryT($sql);
								$sql = " UPDATE int_log_gospell_cas set estado = 'OK', id_equipo = $idContrato  where sub_id = $subId and id = @id";
								$oCon->QueryT($sql);
							}
						}
						if ($this->tipo === 1) {
							$this->msj = 'Actividad ejecutada Exitosamente';
						} else {
							$oReturn->alert('Actividad ejecutada Exitosamente');
						}
						break;

					case 15: //Crear pago por evento

						for ($i = 0; $i < count($tarjetas); $i++) {

							$sql = "SELECT c.id from int_contrato_caja c where c.id_tarjeta = '$tarjeta'";
							if ($oCon->Query($sql)) {
								if ($oCon->NumFilas() > 0) {
									$idContrato = $oCon->f('id');
								}
							}
							$oCon->Free();

							$subId++;
							$this->subId = $subId;
							$userInfo = "$actividad : " . $tarjeta . "";
							$this->userInfo = $userInfo;
							$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, id_equipo, ejecucion)
									VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . " ,$idContrato ,'$userInfo')";
							$oCon->QueryT($sql);

							for ($j = 0; $j < count($querys); $j++) {
								//Asignacion de variables
								$query = odbc_exec($conn, $querys[$i]);
							}
						}
						if ($this->tipo === 1) {
							$this->msj = 'Actividad ejecutada Exitosamente';
						} else {
							$oReturn->alert('Actividad ejecutada Exitosamente');
						}
						break;

						//case 16: //Reconectar tarjeta
					case 17: //Reconectar tarjeta

						for ($i = 0; $i < count($tarjetas); $i++) {

							$tarjeta = $tarjetas[$i];

							$sql = "SELECT c.id from int_contrato_caja c where c.id_tarjeta = '$tarjeta'";
							if ($oCon->Query($sql)) {
								if ($oCon->NumFilas() > 0) {
									$idContrato = $oCon->f('id');
								}
							}
							$oCon->Free();

							$subId++;
							$this->subId = $subId;
							$userInfo = "$actividad : " . $tarjeta . "";
							$this->userInfo = $userInfo;
							$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, id_equipo, ejecucion)
									VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . " ,$idContrato ,'$userInfo')";
							$oCon->QueryT($sql);

							if ($tarjeta == null) {

								$userInfo = "Tarjeta no encontrada";
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							}

							$cambio = '';
							$cambioS = '';
							$inicio = 0;
							$aux = $querys[0];

							while (strpos($querys[0], '$', $inicio) !== false) {
								$inicio = strpos($querys[0], '$', $inicio);
								$final = strpos($querys[0], '_', $inicio) + 1;
								$cambio = substr($querys[0], $inicio + 1, $final - $inicio - 2);
								$cambioS = ${$cambio};
								$querys[0] = str_replace(substr($querys[0], $inicio, $final - $inicio), $cambioS, $querys[0]);
							}

							//var_dump($querys[0]);
							$query = odbc_exec($conn, $querys[0]);
							$querys[0] = $aux;

							if ($query === false) {

								$userInfo = "No se pudo enviar el comando correctamente: " . odbc_error();
								$this->userInfo = $userInfo;
								$this->excepcionFunc();
							} else {

								$sql = " set @id = (SELECT max(id) from int_log_gospell_cas)";
								$oCon->QueryT($sql);
								$sql = " UPDATE int_log_gospell_cas set estado = 'OK', id_equipo = $idContrato  where sub_id = $subId and id = @id";
								$oCon->QueryT($sql);
								$sql = "update int_contrato_caja set estado = 'A' where id_tarjeta = '$tarjeta'";
								$oCon->QueryT($sql);
							}
						}
						//$oReturn->alert('Actividad ejecutada $this->excepcionFunc()osamente');
						if ($this->tipo === 1) {
							$this->msj = 'Actividad ejecutada Exitosamente';
						} else {
							$oReturn->alert('Actividad ejecutada Exitosamente');
						}
						break;
				}
			}
		} catch (Exception $e) {
			$userInfo = $e->getMessage();
			$this->userInfo = $userInfo;
			$this->excepcionFunc();
		}

		if ($this->tipo === 1) {
			return $this->msj;
		} else {
			return $oReturn;
		}
	}

	function cierraConexion()
	{

		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

		$conn = $this->conMSS;
		$connRead = $this->conMSS_Read;
		$infoCAS = $this->infoCAS;
		$subId = $this->subId;
		$oCon = $this->oCon;
		$oConTr = $this->oConTr;
		$oReturn = $this->oReturn;

		//$oConTr->close();
		odbc_close_all();

		$subId++;
		$userInfo = "Cerrar conexion";
		$this->userInfo = $userInfo;
		$sql = "INSERT INTO int_log_gospell_cas (sub_id,id_empresa, id_sucursal, usuario, id_conexion, ejecucion, estado)
	           VALUES ($subId ," . $infoCAS['empresa'] . " ," . $infoCAS['sucursal'] . " ,'" . $infoCAS['userConected'] . "', " . $infoCAS['id_conexion'] . ",'$userInfo','OK')";
		$oCon->QueryT($sql);

		unset($this->conMSS);
		unset($this->oConTr);
		$this->subId = 0;

		return $oReturn;
	}

	function envioComando($query, $verb)
	{

		$conn = $this->conMSS;
		$consulta = $query;
		$cambio = '';
		$cambioS = '';
		$inicio = 0;
		while (strpos($consulta, '$', $inicio) !== false) {
			$inicio = strpos($consulta, '$', $inicio);
			$final = strpos($consulta, '_', $inicio) + 1;
			$cambio = substr($consulta, $inicio + 1, $final - $inicio - 2);
			$cambioS = $verb["$cambio"];
			$consulta = str_replace(substr($consulta, $inicio, $final - $inicio), $cambioS, $consulta);
		}
		$consulta = odbc_exec($conn, $consulta);
		$this->conMSS = $conn;

		return $consulta;
	}

	function excepcionFunc()
	{

		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

		$oReturn = $this->oReturn;
		$accionPrevia = $this->accionPrevia;
		$idLog = $this->idLog;
		$conn = $this->conMSS;
		$tipo = $this->tipo;

		if ($accionPrevia === true || empty($accionPrevia)) {
			$userInfo = $this->userInfo;
			$oCon = $this->oCon;
			$subId = $this->subId;
			if ($subId != 1) {
				set_error_handler(array($this, 'errorHandler'));
				$rollb = odbc_rollback($conn);
				restore_error_handler();
				if (!$rollb) {
					$userInfo = $userInfo . '. NO SE PUDO HACER ROLLBACK A LA BASE SQL SERVER';
				}
			}
			$sql = "UPDATE int_log_gospell_cas set estado = 'ER', info = 'Exception : $userInfo' where sub_id = $subId and id = $idLog";
			$oCon->QueryT($sql);
			$this->accionPrevia = false;
		}
		throw new Exception($userInfo);

		if ($tipo === 1) {
			return $userInfo;
		} else {
			return $oReturn;
		}
	}

	function errorHandler($errno, $errstr, $file, $line)
	{
		$oReturn = $this->oReturn;
		$userInfo = "Error: [$errno] $errstr in line $line";
		$oReturn->alert($userInfo);
		return $oReturn;
	}
}
