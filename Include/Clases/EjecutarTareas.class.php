<?php

/*
  + Class Tareas
  + Daniel Castro - IX2019
  + Clase para ejecucion de Tareas
  + Cambio de estado de tareas, peticiones
  + Calculo de dias_consumidos de servicio
  + Calculo de montos de facturacion
  + Log de transaccion en txt
 */

require_once(path(DIR_INCLUDE) . 'comun.lib.php');
require_once(path(DIR_INCLUDE) . 'Clases/Equipos.class.php');

class EjecutarTarea
{


    var $oCon;
    var $idContrato;
    var $idClpv;
    var $idTarea;
    var $idEquipo;
    var $pathLog;
    var $nameLog;

    /***************************************************
	 @ ejecutarTarea
	 + Ejecucion de tarea
     **************************************************/
    function ejecucionTarea($oCon, $idContrato, $idTarea, $idPeticion, $idEquipo, $idPlan, $fecha, $fin_cmd = 'S')
    {

        $this->oCon = $oCon;
        $this->idContrato = $idContrato;
        $this->idTarea = $idTarea;
        $this->idEquipo = $idEquipo;

        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

        //variables de sesion
        $idEmpresa = $_SESSION['U_EMPRESA'];

        try {

            //genera log de la transaccion
            $this->pathLog = path(DIR_INCLUDE) . 'Logs/Cable_Operador/Ejecuciones/' . date("dmY") . '.txt';

            //control si fecha esta vacia
            if (empty($fecha)) {
                $fecha = date("Y-m-d");
            }

            //variables
            $mes = substr($fecha, 5, 2);
            $anio = substr($fecha, 0, 4);
            $fechaInicioMes = $anio . '/' . $mes . '/01';
            $fechaEjecucion = str_replace("-", "/", $fecha); //fecha corte yyyy/mm/dd
            $ultimoDiaMes = ultimoDiaMesFunc($fecha);
            $fechaFinMes = $anio . '/' . $mes . '/' . $ultimoDiaMes;

            //array campo fecha
            $array_c_f['I'] = 'fecha';
            $array_c_f['C'] = 'fecha_corte';
            $array_c_f['D'] = 'fecha_corte';
            $array_c_f['R'] = 'fecha_reconexion';
            $array_c_f['V'] = 'fecha_vence';

            //array campo tipo
            $array_t_f['C'] = 'corte';
            $array_t_f['D'] = 'corte';
            $array_t_f['R'] = 'reconexion';
            $array_t_f['V'] = 'corte';

            //array opcion pago
            $array_p_f['C'] = 'S';
            $array_p_f['D'] = 'S';
            $array_p_f['R'] = 'N';
            $array_p_f['V'] = 'N';

            //id clpv
            $sql = "SELECT id_clpv, id_sucursal, tarifa FROM isp.contrato_clpv WHERE id = $idContrato";
            if($oCon->Query($sql)){
                if($oCon->NumFilas() > 0){
                    $idClpv = $oCon->f('id_clpv');
                    $idSucursal = $oCon->f('id_sucursal');
                    $tarifa = $oCon->f('tarifa');
                }
            }
            $oCon->Free();

            $log_txt_proceso .= PHP_EOL . "tarifa: $tarifa";


            //atributo id clpv
            $this->idClpv = $idClpv;

            //id proceso
            $sql = "SELECT id_proceso, reconexion_pago, estado, user_web FROM isp.instalacion_clpv WHERE id = $idTarea";
            if($oCon->Query($sql)){
                if($oCon->NumFilas() > 0){
                    $idProceso = $oCon->f('id_proceso');
                    $reconexion_pago = $oCon->f('reconexion_pago');
                    $estado_orden = $oCon->f('estado');
                    $userWeb = $oCon->f('user_web');
                }
            }
            $oCon->Free();

            $log_txt_proceso .= PHP_EOL . "reconexion_pago: $reconexion_pago";

            //control usuario
            if(empty($userWeb)){
                $userWeb = $_SESSION['U_ID'];
            }

            $this->crearLog($idContrato, $idTarea, $idEquipo, "Estado orden servicio: $estado_orden");

            if($estado_orden != 'AN'){

                 // commit
                $oCon->QueryT('BEGIN;');
 
                if ($idProceso == 4) {
                    $idPlan = '';
                }

                $log_txt_proceso = '';
                //parametros del proceso
                $sql = "SELECT op_ejecucion, op_equipo, op_plan, op_corte, etiqueta, 
                        op_pago, tipo, dias_consumidos, op_tarifa, op_elimina, descripcion,
                        op_recalculo, campo_fecha_c
                        FROM isp.int_tipo_proceso 
                        WHERE id = $idProceso";
                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        $op_ejecucion = $oCon->f('op_ejecucion');
                        $op_equipo = $oCon->f('op_equipo');
                        $op_plan = $oCon->f('op_plan');
                        $op_corte = $oCon->f('op_corte');
                        $etiqueta = $oCon->f('etiqueta');
                        $op_pago = $oCon->f('op_pago');
                        $tipo = $oCon->f('tipo');
                        $dias_consumidos = $oCon->f('dias_consumidos');
                        $op_tarifa = $oCon->f('op_tarifa');
                        $op_elimina = $oCon->f('op_elimina');
                        $descripcion = $oCon->f('descripcion');
                        $op_recalculo = $oCon->f('op_recalculo');
                        $campo_fecha_c = $oCon->f('campo_fecha_c');

                        $log_txt_proceso .= PHP_EOL . "op_ejecucion: $op_ejecucion";
                        $log_txt_proceso .= PHP_EOL . "op_equipo: $op_equipo";
                        $log_txt_proceso .= PHP_EOL . "op_plan: $op_plan";
                        $log_txt_proceso .= PHP_EOL . "op_corte: $op_corte";
                        $log_txt_proceso .= PHP_EOL . "etiqueta: $etiqueta";
                        $log_txt_proceso .= PHP_EOL . "op_pago: $op_pago";
                        $log_txt_proceso .= PHP_EOL . "tipo: $tipo";
                        $log_txt_proceso .= PHP_EOL . "dias_consumidos: $dias_consumidos";
                        $log_txt_proceso .= PHP_EOL . "op_tarifa: $op_tarifa";
                        $log_txt_proceso .= PHP_EOL . "op_elimina: $op_elimina";
                        $log_txt_proceso .= PHP_EOL . "descripcion: $descripcion";
                        $log_txt_proceso .= PHP_EOL . "op_recalculo: $op_recalculo";
                        $log_txt_proceso .= PHP_EOL . "campo_fecha_c: $campo_fecha_c";
                    }
                }
                $oCon->Free();

                if($cambio_proceso_reconexion == 'S'){
                    $op_ejecucion = 'AP';
                    $log_txt_proceso .= PHP_EOL . "**op_ejecucion**: $op_ejecucion";
                }

                //variable tipo de fecha
                $campo_f = $array_c_f[$etiqueta];
                $tipo_f = $array_t_f[$etiqueta];
                $op_f = $array_p_f[$etiqueta];

                $log_txt_proceso .= PHP_EOL . "campo_f: $campo_f";
                $log_txt_proceso .= PHP_EOL . "tipo_f: $tipo_f";
                $log_txt_proceso .= PHP_EOL . "op_f: $op_f";

                $this->crearLog($idContrato, $idTarea, $idEquipo, "*** INICIO Orden: $descripcion: $log_txt_proceso ***");

                //actualiza estado equipo
                if (!empty($op_equipo)) {

                    $sql = "UPDATE isp.int_contrato_caja SET estado = '$op_equipo', $campo_f = now() WHERE id = $idEquipo";
                    //$oCon->QueryT($sql);

                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Actualiza Equipo: $op_equipo " . $sql);
                }

                //actualiza estado contrato
                if (!empty($op_ejecucion)) {

                    //consulta si contrato es cortesia
                    $sql = "SELECT COUNT(*) as control FROM isp.contrato_descuentos WHERE id_contrato = $idContrato AND estado = 'A' AND cortesia = 'S'";
                    $control_cortesia = consulta_string_func($sql, 'control', $oCon, 0);

                    if ($control_cortesia > 0) {
                        //actualiza contrato cortesia
                        $sql = "UPDATE isp.contrato_clpv SET estado = 'CR' WHERE id = $idContrato";
                        $oCon->QueryT($sql);
                    } else {
                        //actualiza contrato segun parametro
                        $sql = "UPDATE isp.contrato_clpv SET estado = '$op_ejecucion' WHERE id = $idContrato";
                        $oCon->QueryT($sql);
                    }

                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Actualiza estado contrato: $idContrato : $op_ejecucion " . $sql);
                }

                $this->crearLog($idContrato, $idTarea, $idEquipo, "Fin Comando $fin_cmd");

                /***************************************************
                + En caso de ser instalacion proceso tabla pagos
                **************************************************/
                if ($idProceso == 1) {

                    if ($fin_cmd == 'S') {

                        //en caso de no existir la tabla de pagos genera las cuotas
                        $Equipos = new Equipos($oCon, null, $idEmpresa, $idSucursal, $idClpv, $idContrato, null);
                        $Equipos->registraCuotaPaqueteCaja(0);

                        $this->crearLog($idContrato, $idTarea, $idEquipo, "Crea Tabla Pagos");
                    }
                } else {

                    //insert ejecucion de tarea
                    $sql = "insert into isp.instalacion_ejecucion(id_empresa, id_sucursal, id_clpv, id_contrato, id_instalacion, fecha, 
                                                        observacion_cliente, observacion_tecnico, user_web, fecha_server)
                                                values($idEmpresa, $idSucursal, $idClpv, $idContrato, $idTarea, '$fecha', 
                                                        '', 'Proceso de Ejecucion Automatica', $userWeb, now())";
                    $oCon->QueryT($sql);

                    //id ejecucion
                    $sql = "select max(id) as id 
                        from isp.instalacion_ejecucion 
                        where id_empresa = $idEmpresa and 
                        id_clpv = $idClpv and
                        id_contrato = $idContrato and
                        id_instalacion = $idTarea";
                    $idEjecucion = consulta_string_func($sql, 'id', $oCon, 0);

                    $this->crearLog($idContrato, $idTarea, $idEquipo, "ID Ejecucion $idEjecucion $sql");

                    //en caso de necesitar afectar la mensualidad 
                    if ($op_pago == 'S') {

                        $this->crearLog($idContrato, $idTarea, $idEquipo, "Modifica Tabla Pagos");

                        //variables para calculo de dias_consumidos y montos de pago
                        $array_plan = array();
                        $array_pago = array();
                        $array_calculos = array();
                        $diasDiferencia = 0;
                        $valor_diario = 0;
                        $valor_uso = 0;
                        $valor_no_uso = 0;
                        $diaUsoOk = 0;
                        $diasDiferenciaOk = 0;

                        //id cuota
                        $sql = "SELECT id 
                            FROM isp.contrato_pago 
                            WHERE id_contrato = $idContrato AND 
                            id_clpv = $idClpv AND 
                            month(fecha) = $mes AND 
                            DATE_PART('year', fecha) = $anio AND
                            estado_fact is null AND 
                            tipo = 'P' AND 
                            estado = 'PE'";
                        $idCuota = consulta_string_func($sql, 'id', $oCon, 0);

                        if (empty($idCuota)) {
                            //id cuota
                            $sql = "SELECT min(id) as id
                                FROM isp.contrato_pago 
                                WHERE id_contrato = $idContrato AND 
                                id_clpv = $idClpv AND
                                estado_fact is null AND
                                tipo = 'P' AND
                                estado = 'PE'";
                            $idCuota = consulta_string_func($sql, 'id', $oCon, 0);
                        }

                        $this->crearLog($idContrato, $idTarea, $idEquipo, "Query Cuota: $sql = $idCuota");

                        //valida existencia de cuota
                        if (empty($idCuota)) {
                            //en caso de no existir la tabla de pagos, genera las cuotas
                            $Equipos = new Equipos($oCon, null, $idEmpresa, $idSucursal, $idClpv, $idContrato, null);
                            $Equipos->registraCuotaPaqueteCaja(0, 'N', 'N', $fecha);

                            $this->crearLog($idContrato, $idTarea, $idEquipo, "Crea Tabla Pagos, no se encontro ID CUOTA");

                            //id cuota
                            $sql = "SELECT id 
                                    FROM isp.contrato_pago 
                                    WHERE id_contrato = $idContrato AND 
                                    id_clpv = $idClpv AND 
                                    month(fecha) = $mes AND 
                                    DATE_PART('year', fecha) = $anio AND 
                                    estado_fact is null AND
                                    tipo = 'P' AND
                                    estado = 'PE'";
                            $idCuota = consulta_string_func($sql, 'id', $oCon, 0);

                            $this->crearLog($idContrato, $idTarea, $idEquipo, "Query Cuota: $sql = $idCuota");
                        }

                        //ejecuta proceso en caso de existir cuota
                        if ($idCuota > 0) {

                            //informacion de la mensualidad
                            $sql = "select fecha, dias, dias_uso
                                from isp.contrato_pago 
                                where id_contrato = $idContrato and 
                                id = $idCuota";
                            if ($oCon->Query($sql)) {
                                if ($oCon->NumFilas() > 0) {
                                    $fecha_cuota = $oCon->f('fecha');
                                    $dias_cuota = $oCon->f('dias');
                                    $dias_uso = $oCon->f('dias_uso');
                                }
                            }
                            $oCon->Free();

                            $this->crearLog($idContrato, $idTarea, $idEquipo, "Modifica Cuota: $idCuota");

                            //variable para clasificacion de planes
                            $filtro_plan = " and id_pack in ( ";

                            $validPlan = false;
                            if (!empty($idPlan)) { //plan unico
                                
                                $sql = "SELECT id 
                                        FROM isp.int_contrato_caja_pack 
                                        WHERE id_caja = $idEquipo AND 
                                        id_prod in ($idPlan)";
                                $this->crearLog($idContrato, $idTarea, $idEquipo, "plan unico: $sql");
                                if ($oCon->Query($sql)) {
                                    if ($oCon->NumFilas() > 0) {
                                        $validPlan = true;
                                        do {
                                            
                                            $filtro_plan .= $oCon->f('id') . ', ';

                                            $array_plan[] = $oCon->f('id');

                                            $this->crearLog($idContrato, $idTarea, $idEquipo, "Id Pack: " . $oCon->f('id'));
                                        } while ($oCon->SiguienteRegistro());
                                        $filtro_plan = substr($filtro_plan, 0, strlen($filtro_plan) - 2);
                                    }
                                }
                                $oCon->Free();
                            } else { // todos los planes

                                $sql = "SELECT id 
                                        FROM isp.int_contrato_caja_pack 
                                        WHERE id_caja = $idEquipo";
                                $this->crearLog($idContrato, $idTarea, $idEquipo, "todos los planes: $sql");
                                if ($oCon->Query($sql)) {
                                    if ($oCon->NumFilas() > 0) {
                                        $validPlan = true;
                                        do {

                                            $filtro_plan .= $oCon->f('id') . ', ';

                                            $array_plan[] = $oCon->f('id');

                                            $this->crearLog($idContrato, $idTarea, $idEquipo, "Id Pack: " . $oCon->f('id'));
                                        } while ($oCon->SiguienteRegistro());
                                        $filtro_plan = substr($filtro_plan, 0, strlen($filtro_plan) - 2);
                                    }
                                }
                                $oCon->Free();
                            }
                            $filtro_plan .= " )";

                            $this->crearLog($idContrato, $idTarea, $idEquipo, "Modifica Planes: $filtro_plan $sql");

                            if ($validPlan) {

                                //query tabla pagos por plan
                                $sql = "SELECT id, id_pago, dias_uso, dias_no_uso, tarifa, valor_no_uso, valor_dia, id_pack
                                        FROM isp.contrato_pago_pack 
                                        WHERE id_contrato = $idContrato AND
                                        id_clpv = $idClpv AND
                                        id_caja = $idEquipo AND
                                        id_pago = $idCuota AND
                                        month(fecha) = '$mes' AND
                                        DATE_PART('year', fecha) = '$anio' AND
                                        estado != 'AN'
                                        $filtro_plan";
                                $this->crearLog($idContrato, $idTarea, $idEquipo, "Query isp.contrato_pago_pack: $sql");
                                if ($oCon->Query($sql)) {
                                    if ($oCon->NumFilas() > 0) {
                                        do {
                                            $array_pago[] = array(
                                                $oCon->f('id'),
                                                $oCon->f('valor_dia'),
                                                $oCon->f('dias_uso'),
                                                $oCon->f('dias_no_uso'),
                                                $oCon->f('valor_no_uso'),
                                                $oCon->f('tarifa'),
                                                $oCon->f('id_pack')
                                            );
                                            $this->crearLog($idContrato, $idTarea, $idEquipo, "Query tabla pagos plan: " . $oCon->f('id'));
                                        } while ($oCon->SiguienteRegistro());
                                    }
                                }
                                $oCon->Free();

                                //actualiza estado cuota
                                if (!empty($op_ejecucion)) {
                                    //actualiza cuotas futuras, congela deuda

                                    //modifica estado de aprobado a su equivalente en tabla de pagos PE
                                    if ($op_ejecucion == 'AP') {
                                        $op_ejecucion = 'PE';
                                    }

                                    $sql = "UPDATE isp.contrato_pago 
                                    SET estado = '$op_ejecucion'
                                    WHERE  id_contrato = $idContrato AND
                                    id_clpv = $idClpv AND
                                    id > $idCuota AND
                                    estado_fact is null AND
                                    estado != 'AN' AND
									tipo = 'P'";
                                    $oCon->QueryT($sql);

                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Actualiza estado de tabla de pagos, equivalente a contrato: $idContrato : $op_ejecucion $sql");
                                }

                                //query cargo de servicios adicionales por proceso de corte
                                if($idProceso == 2){
									
									$sql = "SELECT count(*) as control 
											FROM isp.contrato_pago 
											WHERE id_contrato = $idContrato AND 
											fecha = '$fecha' AND 
											estado = 'PE' AND
											tipo = 'A' AND 
											detalle = 'CARGO POR RECONEXION'";
									$controlReco = consulta_string_func($sql, 'control', $oCon, 0);
									
									if($controlReco == 0){
										
										unset($array_c_s);
										$sql = "SELECT id_prod, cod_prod, precio 
												FROM isp.int_tipo_proceso_serv
												WHERE id_empresa = $idEmpresa AND
												id_sucursal = $idSucursal AND
												id_proceso = $idProceso AND
												estado = 'A'";
										if ($oCon->Query($sql)) {
											if ($oCon->NumFilas() > 0) {
												$tot_add = 0;
												do {
													$array_c_s[] = array($oCon->f('id_prod'), $oCon->f('cod_prod'), $oCon->f('precio'));
													$tot_add += $oCon->f('precio');
												} while ($oCon->SiguienteRegistro());
											}
										}
										$oCon->Free();

										//array cagos adicionales
										if (count($array_c_s) > 0) {
										

											//ingresa cuota independiente
											$sql = "INSERT INTO isp.contrato_pago (id_contrato, id_clpv, fecha, secuencial, estado, mes, anio, tarifa, 
																			can_add, pre_add, tot_add, valor_pago, valor_dia, valor_uso, valor_no_uso,
																			dias, dias_uso, dias_no_uso, tipo, detalle)
																			values($idContrato, $idClpv, '$fecha', 0, 'PE', '$mes', '$anio', $tot_add,
																			0, 0, 0, 0, 0, 0, 0,
																			1, 1, 0, 'A', 'CARGO POR RECONEXION')";
											$oCon->QueryT($sql);

											$this->crearLog($idContrato, $idTarea, $idEquipo, "Ingreso cargo adicional: $sql");

											//query maximo id cuota
											$sql = "SELECT max(id) as id FROM isp.contrato_pago WHERE id_contrato = $idContrato AND tipo = 'A' AND estado = 'PE'";
											$idCuotaReco = consulta_string_func($sql, 'id', $oCon, 0);

											$this->crearLog($idContrato, $idTarea, $idEquipo, "Id cuota reconexion = $idCuotaReco");

											foreach ($array_c_s as $val_s) {
												$id_prod_s = $val_s[0];
												$cod_prod_s = $val_s[1];
												$precio_s = $val_s[2];

												//query control
												$sql = "SELECT count(*) as control 
														FROM isp.contrato_pago_pack 
														WHERE id_contrato = $idContrato AND
														id_pago = $idCuotaReco AND
														id_prod = $id_prod_s AND
														cod_prod = '$cod_prod_s'";
												$control_s = consulta_string_func($sql, 'control', $oCon, 0);

												//control para evitar duplicar cargo adicional
												if ($control_s == 0) {
													//registra servicio por proceso
													$sql = "INSERT INTO isp.contrato_pago_pack(id_empresa, id_sucursal, id_clpv, id_contrato,
																				id_caja, id_pack, id_pago, id_prod, cod_prod, fecha, 
																				tarifa, valor_pago, valor_dia, valor_uso, valor_no_uso,
																				tipo, dias, dias_uso, estado, user_web, fecha_server)
																		values($idEmpresa, $idSucursal, $idClpv, $idContrato,
																				null, null, $idCuotaReco, $id_prod_s, '$cod_prod_s', '$fecha',
																				$precio_s, 0, 0, $precio_s, 0,
																				'A', 1, 1, 'A', $userWeb, now())";
													$oCon->QueryT($sql);

													$this->crearLog($idContrato, $idTarea, $idEquipo, "Registra cargo adicional: $cod_prod_s : $precio_s $sql");
												}
											} //fin foreach
										} //fin if count array servicio adicional
									} //fin if count control corte
                                }

                                //proceso para eliminacion de planes, cuotas
                                if ($op_elimina == 'S') {

                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Proceso Eliminacion: $op_elimina");

                                    if ($tipo == 'C') { //contrato: elimina tabla de pagos, tabla de pagos pack

                                        //delete tabla de pagos
                                        $sql = "DELETE FROM isp.contrato_pago_pack 
                                        WHERE id_contrato = $idContrato AND
                                        id_clpv = $idClpv AND
                                        id_pago > $idCuota AND
                                        estado_fact is null AND
                                        estado = 'A' AND
                                        tipo = 'M' AND
                                        id_pago not in (SELECT f.id_pago FROM isp.contrato_factura f
                                        WHERE id_pago = f.id_pago AND
                                        id_contrato = f.id_contrato)";
                                        $oCon->QueryT($sql);

                                        //delete tabla de pagos pack
                                        $sql = "DELETE FROM isp.contrato_pago
                                        WHERE id_contrato = $idContrato AND
                                        id_clpv = $idClpv AND
                                        id > $idCuota AND
                                        estado_fact is null AND
                                        estado = 'PE' AND
                                        tipo = 'P' AND
                                        id not in (SELECT f.id_pago FROM isp.contrato_factura f
                                        WHERE id = f.id_pago AND
                                        id_contrato = f.id_contrato)";
                                        $oCon->QueryT($sql);

                                        $this->crearLog($idContrato, $idTarea, $idEquipo, "Elimina Tabla de Pagos $sql");
                                    } elseif ($tipo == 'E') { //contrato: elimina de tabla pagos pack y valida tabla pagos

                                        //delete tabla de pagos
                                        $sql = "DELETE FROM isp.contrato_pago_pack 
                                        WHERE id_contrato = $idContrato AND
                                        id_clpv = $idClpv AND
                                        id_pago > $idCuota AND
                                        id_caja = $idEquipo AND
                                        estado_fact is null AND
                                        estado = 'A' AND
                                        tipo = 'M' AND
                                        id_pago not in (SELECT f.id_pago FROM isp.contrato_factura f
                                        WHERE id_pago = f.id_pago AND
                                        id_contrato = f.id_contrato)
                                        $filtro_plan";
                                        $oCon->QueryT($sql);

                                        $this->crearLog($idContrato, $idTarea, $idEquipo, "Elimina Tabla de Pagos Paquetes $sql");

                                        //control de cuotas pack
                                        $sql = "SELECT count(*) as control 
                                        FROM isp.contrato_pago_pack 
                                        WHERE id_contrato = $idContrato AND 
                                        id_pago > $idCuota AND 
                                        estado_fact is null AND
                                        estado = 'A' AND
                                        tipo = 'M' AND
                                        id_pago not in (SELECT f.id_pago FROM isp.contrato_factura f
                                        WHERE id_pago = f.id_pago AND
                                        id_contrato = f.id_contrato)";
                                        $control = consulta_string_func($sql, 'control', $oCon, 0);

                                        if ($control == 0) {
                                            //delete tabla de pagos
                                            $sql = "DELETE FROM isp.contrato_pago
                                            WHERE id_contrato = $idContrato AND
                                            id_clpv = $idClpv AND
                                            id > $idCuota AND
                                            estado_fact is null AND
                                            estado = 'PE' AND
                                            tipo = 'P' AND
                                            id not in (SELECT f.id_pago FROM isp.contrato_factura f
                                            WHERE id = f.id_pago AND
                                            id_contrato = f.id_contrato)";
                                            $oCon->QueryT($sql);

                                            $this->crearLog($idContrato, $idTarea, $idEquipo, "Elimina Tabla de Pagos $sql");
                                        }
                                    } //fin elimina table de pagos, paquetes
                                } //fin op elimina paquetes

                                //proceso de verificacion de existencia de tabla de pagos para reconexiones e instalaciones
                                if ($etiqueta == 'R' || $etiqueta == 'I') {

                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Proceso de reconexion: $etiqueta");

                                    $Equipos = new Equipos($oCon, null, $idEmpresa, $idSucursal, $idClpv, $idContrato, null);

                                    //control existencia de la tabla de pagos
                                    $sql = "SELECT count(*) as control
                                            FROM isp.contrato_pago
                                            WHERE id_contrato = $idContrato AND
                                            id_clpv = $idClpv AND
                                            estado_fact is null AND
                                            estado = 'PE' AND
                                            tipo = 'P' AND
                                            id > $idCuota";
                                    $control_pago = consulta_string_func($sql, 'control', $oCon, 0);

                                    if ($control_pago == 0) { //en caso de que tabla de pagos no existe, la genera

                                        //en caso de no existir la tabla de pagos genera las cuotas
                                        $Equipos->registraCuotaPaqueteCaja(0, 'N');

                                        $this->crearLog($idContrato, $idTarea, $idEquipo, "Registra Nueva Tabla de Pagos");

                                        /* proceso para verificacion e ingreso de paquete en cuota en caso de no existir */

                                        //recorre array de planes
                                        for ($i = 0; $i < count($array_plan); $i++) {

                                            $id_p = $array_plan[$i];

                                            $sql = "SELECT id 
                                            FROM isp.contrato_pago_pack
                                            WHERE id_contrato = $idContrato AND
                                            id_clpv = $idClpv AND
                                            id_pago = $idCuota AND
                                            id_caja = $idEquipo AND
                                            id_pack = $id_p AND
                                            estado = 'A' AND
                                            tipo = 'M' AND
                                            estado_fact is null";
                                            $idCuotaPack = consulta_string_func($sql, 'id', $oCon, 0);

                                            $dias_uso_pack = 0;
                                            $valor_dia_pack = 0;

                                            //query datos de la cuota del plan
                                            $sql = "SELECT dias_uso, valor_dia
                                            FROM isp.contrato_pago_pack
                                            WHERE id = $idCuotaPack";
                                            if ($oCon->Query($sql)) {
                                                if ($oCon->NumFilas() > 0) {
                                                    $dias_uso_pack = $oCon->f('dias_uso');
                                                    $valor_dia_pack = $oCon->f('valor_dia');
                                                }
                                            }
                                            $oCon->Free();

                                            //query datos de los planes del equipo
                                            $sql = "SELECT id_prod, cod_prod, precio
                                            FROM isp.int_contrato_caja_pack
                                            WHERE id = $id_p";
                                            if ($oCon->Query($sql)) {
                                                if ($oCon->NumFilas() > 0) {
                                                    $id_prod = $oCon->f('id_prod');
                                                    $cod_prod = $oCon->f('cod_prod');
                                                    $precio = $oCon->f('precio');
                                                }
                                            }
                                            $oCon->Free();

                                            if ($idCuotaPack > 0) { // en caso de existir plan en cuota, realiza calculo de dias

                                                $valor_uso_ok = $this->calculoDiasCuotaPlan($etiqueta, $dias_consumidos, $fecha, $fechaFinMes, $fechaEjecucion, $ultimoDiaMes, $precio, $idCuota, $idCuotaPack, $dias_uso_pack, $valor_dia_pack);

                                                $this->crearLog($idContrato, $idTarea, $idEquipo, "Actualiza Tabla de Pagos Plan : $idCuotaPack : $id_p con Paquetes: $cod_prod - $precio valor: $valor_uso_ok");
                                            } else { //de no existir plan en cuota lo registra

                                                if ($dias_consumidos == 'S') {
                                                    $diaUsoPack = diferenciaDias($fechaEjecucion, $fechaFinMes);
                                                    $diaUsoPack += $dias_uso_pack;
                                                    $diasDiferencia = $ultimoDiaMes - $diaUsoPack;
                                                    $valor_dia_pack = round($precio / $ultimoDiaMes, 6);
                                                    $valor_uso_pack = round($valor_dia_pack * $diaUsoPack, 6);
                                                    $valor_no_uso_pack = round($valor_dia_pack * $diasDiferencia, 6);
                                                } else {
                                                    $diaUsoPack = ultimoDiaMesFunc($fecha);
                                                    $diasDiferencia = 0;
                                                    $valor_dia_pack = 0;
                                                    $valor_uso_pack = 0;
                                                    $valor_no_uso_pack = 0;
                                                }

                                                $Equipos->registraTablaPagoPack($idEquipo, $id_p, $idCuota, $id_prod, $cod_prod, $fecha_cuota, $precio, $valor_dia_pack, $valor_uso_pack, $valor_no_uso_pack, $dias_cuota, $diaUsoPack, 'A');

                                                $this->crearLog($idContrato, $idTarea, $idEquipo, "No Existe en Tabla Pagos Registra Tabla de Pagos Plan : $id_p con Paquetes: $cod_prod - $precio, valor uso: $valor_uso_pack, dias: $dias_uso_pack + $diaUsoPack");
                                            } //else plan en cuota no exite
                                        } //fin for

                                    } else { //en caso de que tabla de pagos exite, verificamos que los planes esten registrados

                                        //query cuotas sin facturar
                                        $sql = "SELECT id, fecha, dias 
                                                FROM isp.contrato_pago
                                                WHERE id_contrato = $idContrato AND
                                                id_clpv = $idClpv AND
                                                estado_fact is null AND
                                                tipo = 'P' AND
                                                estado != 'CA'
                                        ORDER BY secuencial";
                                        if ($oCon->Query($sql)) {
                                            if ($oCon->NumFilas() > 0) {
                                                do {
                                                    $array_new[] = array($oCon->f('id'), $oCon->f('fecha'), $oCon->f('dias'));
                                                } while ($oCon->SiguienteRegistro());
                                            }
                                        }
                                        $oCon->Free();

                                        if (count($array_new) > 0) {
                                            foreach ($array_new as $val_new) {
                                                $idCuotaNew = $val_new[0];
                                                $nuevaFecha = $val_new[1];
                                                $diasNew = $val_new[2];

                                                //recorre array de planes
                                                for ($i = 0; $i < count($array_plan); $i++) {

                                                    $id_p = $array_plan[$i];

                                                    $sql = "SELECT id 
                                                            FROM isp.contrato_pago_pack
                                                            WHERE id_contrato = $idContrato AND
                                                            id_clpv = $idClpv AND
                                                            id_pago = $idCuotaNew AND
                                                            id_caja = $idEquipo AND
                                                            id_pack = $id_p AND
                                                            tipo = 'M' AND
                                                            estado_fact is null";
                                                    $idCuotaPack = consulta_string_func($sql, 'id', $oCon, 0);

                                                    $dias_uso_pack = 0;
                                                    $valor_dia_pack = 0;

                                                    //query datos de la cuota del plan
                                                    $sql = "SELECT dias_uso, valor_dia
                                                    FROM isp.contrato_pago_pack
                                                    WHERE id = $idCuotaPack";
                                                    if ($oCon->Query($sql)) {
                                                        if ($oCon->NumFilas() > 0) {
                                                            $dias_uso_pack = $oCon->f('dias_uso');
                                                            $valor_dia_pack = $oCon->f('valor_dia');
                                                        }
                                                    }
                                                    $oCon->Free();

                                                    //query datos de los planes del equipo
                                                    $sql = "SELECT p.id_prod, p.cod_prod, p.precio
                                                    FROM isp.int_contrato_caja_pack p
                                                    WHERE p.id = $id_p AND
                                                    p.activo = 'S' AND
                                                    p.estado IN (SELECT e.id
                                                    FROM isp.int_estados_equipo e 
                                                    where e.id = p.estado and
                                                    e.op_pago = 'S')";
                                                    if ($oCon->Query($sql)) {
                                                        if ($oCon->NumFilas() > 0) {
                                                            $id_prod = $oCon->f('id_prod');
                                                            $cod_prod = $oCon->f('cod_prod');
                                                            $precio = $oCon->f('precio');
                                                        }
                                                    }
                                                    $oCon->Free();

                                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Query planes del equipo $sql : id_prod: $id_prod - $precio");

                                                    if ($idCuotaPack > 0) { //existe plan en cuota
                                                        if ($idCuota == $idCuotaNew) {
                                                            $this->calculoDiasCuotaPlan($etiqueta, $dias_consumidos, $fecha, $fechaFinMes, $fechaEjecucion, $ultimoDiaMes, $precio, $idCuota, $idCuotaPack, $dias_uso_pack, $valor_dia_pack);
                                                            $this->crearLog($idContrato, $idTarea, $idEquipo, "Actualiza Tabla de Pagos Plan : $id_p con Paquetes: $cod_prod - $precio");
                                                        }
                                                    } else { //no existe plan en cuota

                                                        $diaUsoPack = 0;
                                                        $diasDiferencia = 0;
                                                        $valor_uso_pack = 0;
                                                        $valor_no_uso_pack = 0;
                                                        $recalcula_pack = 'N'; //variable que activa recalculo de cuota pago
                                                        if ($idCuota == $idCuotaNew) {
                                                            $recalcula_pack = 'S';
                                                            if ($dias_consumidos == 'S') {
                                                                $diaUsoPack = diferenciaDias($fechaEjecucion, $fechaFinMes);
                                                                $diaUsoPack += $dias_uso_pack;
                                                                $diasDiferencia = $ultimoDiaMes - $diaUsoPack;
                                                                $valor_dia_pack = round($precio / $ultimoDiaMes, 6);
                                                                $valor_uso_pack = round($valor_dia_pack * $diaUsoPack, 6);
                                                                $valor_no_uso_pack = round($valor_dia_pack * $diasDiferencia, 6);
                                                            } else {
                                                                $diaUsoPack = ultimoDiaMesFunc($nuevaFecha);
                                                            }
                                                        } else {
                                                            $diaUsoPack = ultimoDiaMesFunc($nuevaFecha);
                                                        }

                                                        $Equipos->registraTablaPagoPack($idEquipo, $id_p, $idCuotaNew, $id_prod, $cod_prod, $nuevaFecha, $precio, $valor_dia_pack, $valor_uso_pack, $valor_no_uso_pack, $diasNew, $diaUsoPack, 'A', '', $recalcula_pack);

                                                        $this->crearLog($idContrato, $idTarea, $idEquipo, "No Existe en Tabla Pagos Registra Tabla de Pagos Plan, recalcula $recalcula_pack : $id_p con Paquetes: $cod_prod - $precio, valor uso: $valor_uso_pack, dias: $dias_uso_pack + $diaUsoPack");
                                                    } //else crea plan en cuotas
                                                } //fin for planes
                                            } // fin foreach
                                        } //fin if count
                                    } //else tabla de pagos

                                    //consulta tarifa de contrato
                                    if($tarifa == 0){
                                        $Contratos = new Contratos($oCon, null, $idEmpresa, $idSucursal, $idClpv, $idContrato);
                                        $Contratos->cambioTarifaContrato('N');
                                        $this->crearLog($idContrato, $idTarea, $idEquipo, "Tarifa 0, entra a proceso de recalculo de tarifa");
                                    }

                                } else {

                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Proceso de $etiqueta");
                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Contador array_pago = " . count($array_pago));

                                    if (count($array_pago) > 0) {
                                        foreach ($array_pago as $val) {

                                            $valor_uso_ok = $this->calculoDiasCuotaPlan($etiqueta, $dias_consumidos, $fecha, $fechaInicioMes, $fechaEjecucion, $ultimoDiaMes, $val[5], $idCuota, $val[0], $val[2], $val[1]);

                                            $this->crearLog($idContrato, $idTarea, $idEquipo, "Actualiza valores de la cuota valor uso:  $valor_uso_ok ");
                                        } //fin foreach
                                    } //fin count
                                } //else proceso diferente de reconexion

                                //cambio de estado planes del equipo
                                for ($i = 0; $i < count($array_plan); $i++) {
                                    $id_pack = $array_plan[$i];

                                    if (!empty($op_plan)) {

                                        $sql = "UPDATE isp.int_contrato_caja_pack SET estado = '$op_plan' WHERE id = $id_pack and estado not in ('E')";
                                        //$oCon->QueryT($sql);

                                        $this->crearLog($idContrato, $idTarea, $idEquipo, "Actualiza estado plan: $sql");
                                    }
                                } //fin for

                                if ($op_tarifa == 'N') {
                                    //actualiza tarifa en caso de ser necesario
                                    $Contratos = new Contratos($oCon, null, $idEmpresa, $idSucursal, $idClpv, $idContrato);
                                    $newTarifa = $Contratos->cambioTarifaContrato('S', $idCuota);

                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Actualiza Tarifa : $newTarifa");
                                } //fin op tarifa

                                //actualizar estado tarea
                                $sql = "SELECT count(*) as control FROM isp.int_contrato_caja WHERE id_contrato = $idContrato AND estado = 'P'";
                                $control_ins = consulta_string_func($sql, 'control', $oCon, 0);

                                if($control_ins == 0){
                                    $sql = "UPDATE isp.instalacion_clpv SET estado = 'TE' WHERE id = $idTarea";
                                    $oCon->QueryT($sql);
                                }

                                $this->crearLog($idContrato, $idTarea, $idEquipo, "Actualiza Ejecucion : $sql");

                                if($op_recalculo == 'S'){
                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Actualiza tarifa de tabla de pagos : $op_recalculo");
                                    $Contratos = new Contratos($oCon, null, $idEmpresa, $idSucursal, $idClpv, $idContrato);
                                    $Contratos->cambioTarifaContrato('S');
                                }

                                //activa contrato en caso de pago de reconexion
                                if($reconexion_pago == 'S'){
                                    $sql = "UPDATE isp.contrato_clpv SET estado = 'AP' WHERE id = $idContrato";
                                    $oCon->QueryT($sql);

                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Activa contrato por pago de reconexion : $sql");

                                    //actualiza estado de las cuotas 
                                    $sql = "UPDATE isp.contrato_pago 
                                            SET estado = 'PE'
                                            WHERE  id_contrato = $idContrato AND
                                            id_clpv = $idClpv AND
                                            id > $idCuota AND
                                            estado_fact is null AND
                                            estado != 'AN'";
                                    $oCon->QueryT($sql);

                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Actualiza estado de tabla de pagos, equivalente a contrato: $idContrato : $op_ejecucion $sql");
                                }

                                //verifica tarifa de contrato, en caso de ser cero modifica la mensualidad del contrato
                                if($tarifa == 0){
                                    unset($Contratos);
                                    $Contratos = new Contratos($oCon, null, $idEmpresa, $idSucursal, $idClpv, $idContrato);
                                    $Contratos->cambioTarifaContrato('N');
                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Tarifa 0, entra a proceso de recalculo de tarifa");
                                }

                                //verifica cambio de tarifa
                                $sql = "SELECT tarifa FROM isp.contrato_clpv WHERE id = $idContrato";
                                $tarifa_new = consulta_string_func($sql, 'tarifa', $oCon, 0);

                                if($tarifa <> $tarifa_new){
                                    unset($Contratos);
                                    $Contratos = new Contratos($oCon, null, $idEmpresa, $idSucursal, $idClpv, $idContrato);
                                    $Contratos->registroTarifaContrato($idTarea, $idPeticion, $userWeb, $tarifa, $tarifa_new);
                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Tarifa difiere de nueva tarifa, entra a proceso de recalculo de tarifa");
                                }

                                //actualiza fechas en contrato
                                if(!empty($campo_fecha_c)){
                                    $sql = "UPDATE isp.contrato_clpv SET $campo_fecha_c = '$fecha' WHERE id = $idContrato";
                                    $oCon->QueryT($sql);

                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Actualiza Fecha Contrato ".$sql);
                                }

                                //control suspension de equipo principal
                                if($etiqueta == 'D' && $op_equipo == 'D' && $tipo == 'D'){

                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Entra en validacion de suspension de equipo ");
                                    
                                    $sql = "SELECT c.tipo, c.id_tipo_prod, p.precio
                                            FROM isp.int_contrato_caja c, isp.int_contrato_caja_pack p
                                            WHERE c.id = p.id_caja AND
                                            c.id_contrato = p.id_contrato AND
                                            c.id_contrato = $idContrato AND
                                            c.id = $idEquipo";
                                    if($oCon->Query($sql)){
                                        if($oCon->NumFilas() > 0){
                                            $caja_tipo = $oCon->f('tipo');
                                            $caja_id_tipo_prod = $oCon->f('id_tipo_prod');
                                            $caja_precio = $oCon->f('precio');
                                        }
                                    }
                                    $oCon->Free();

                                    $this->crearLog($idContrato, $idTarea, $idEquipo, "Suspension de equipo, tipo, precio: $caja_tipo - $caja_id_tipo_prod - $caja_precio ".$sql);

                                    //control television
                                    if($caja_id_tipo_prod == 1){
                                        if($caja_tipo == 'P'){
                                            $this->crearLog($idContrato, $idTarea, $idEquipo, "Caja Principal");

                                            $sql = "SELECT min(p.id) AS id
                                                    FROM isp.int_contrato_caja c, isp.int_contrato_caja_pack p
                                                    WHERE c.id = p.id_caja AND
                                                    c.id_contrato = p.id_contrato AND
                                                    c.id_contrato = $idContrato AND
                                                    c.estado = 'A' AND
                                                    p.id_prod = 1 AND
                                                    c.id_tipo_prod = 1";
                                            $id_min = consulta_string_func($sql, 'id', $oCon, 0);

                                            $this->crearLog($idContrato, $idTarea, $idEquipo, "Id paquete update = $id_min ".$sql);

                                            if($id_min > 0){
                                                $sql = "UPDATE isp.int_contrato_caja_pack SET precio_tmp = precio, precio = $caja_precio, tipo = 'P' WHERE id = $id_min";
                                                $oCon->QueryT($sql);

                                                $this->crearLog($idContrato, $idTarea, $idEquipo, "Update paquete suspension ".$sql);

                                                unset($Contratos);
                                                $Contratos = new Contratos($oCon, null, $idEmpresa, $idSucursal, $idClpv, $idContrato);
                                                $Contratos->recalcularTarifaCuotas('S', $idCuota);
                                            }
                                            
                                        }//fin if tipo caja
                                    }//fin if tipo prod
                                }// fin if control suspension
                            } else {
                                $this->crearLog($idContrato, $idTarea, $idEquipo, "No se puede ejecutar tarea porque no existen planes disponibles..!");
                                return 0;
                                $oCon->QueryT('ROLLBACK;');
                                exit;
                            }
                        } else {
                            $this->crearLog($idContrato, $idTarea, $idEquipo, "No existe Cuota para esta fecha now($fecha)");
                        }
                    } //fin op pago

                } //fin if tabla pagos
                $oCon->QueryT('COMMIT;');
            }

            return true;
        } catch (Exception $e) {
            $oCon->QueryT('ROLLBACK;');
            $this->crearLog($idContrato, $idTarea, $idEquipo, "Error: " . $e->getMessage());
        }
    }

    private function calculoDiasCuotaPlan($etiqueta, $dias_consumidos, $fecha, $fechaInicioMes, $fechaEjecucion, $ultimoDiaMes, $tarifa, $idCuota, $idCuotaPack, $dias_uso, $valor_diario)
    {
        $oCon = $this->oCon;
        $idContrato = $this->idContrato;
        $idClpv = $this->idClpv;
        $idTarea = $this->idTarea;
        $idEquipo = $this->idEquipo;

        $tipo_f = "";
        $campo_f = "";
       if($etiqueta == 'C'){
            $tipo_f = "corte";
            $campo_f = "fecha_corte";
        }elseif($etiqueta == 'R'){
            $tipo_f = "reconexion";
            $campo_f = "fecha_reconexion";
        }

        $log_txt_calculo = '';

        $diaUso = 0;
        if ($dias_consumidos == 'S') {
            if ($etiqueta == 'I' || $etiqueta == 'R') {
                $diaUso = diferenciaDias($fechaEjecucion, $fechaInicioMes);
                if($dias_uso <>  $ultimoDiaMes){
                    $log_txt_calculo .= PHP_EOL . "dias_uso <> ultimoDiaMes: $tipo_f";
                    $diaUso += $dias_uso;
                }

                if($diaUso > $ultimoDiaMes){
                    $log_txt_calculo .= PHP_EOL . "dias de uso es mayor a mes actual: $diaUso = $ultimoDiaMes";
                    $diaUso = $ultimoDiaMes;
                }

                $diasDiferencia = $ultimoDiaMes - $diaUso;
                $valor_diario = round($tarifa / $ultimoDiaMes, 6);
                $valor_uso = round($valor_diario * $diaUso, 6);
                $valor_no_uso = round($valor_diario * $diasDiferencia, 6);
            } else {

                $diaUso = diferenciaDias($fechaInicioMes, $fechaEjecucion);
                $diaUso++;

                if($diaUso > $ultimoDiaMes){
                    $log_txt_calculo .= PHP_EOL . "dias de uso es mayor a mes actual: $diaUso = $ultimoDiaMes";
                    $diaUso = $ultimoDiaMes;
                }

                $diasDiferencia = $ultimoDiaMes - $diaUso;
                $valor_diario = round($tarifa / $ultimoDiaMes, 6);
                $valor_uso = round($valor_diario * $diaUso, 6);
                $valor_no_uso = round($valor_diario * $diasDiferencia, 6);
            }
        } else {
            $diaUso = ultimoDiaMesFunc($fechaEjecucion);
            $diasDiferencia = 0;
            $valor_diario = 0;
            $valor_uso = 0;
            $valor_no_uso = 0;
        }

        $log_txt_calculo .= PHP_EOL . "tipo_f: $tipo_f";
        $log_txt_calculo .= PHP_EOL . "campo_f: $campo_f";
        $log_txt_calculo .= PHP_EOL . "ultimoDiaMes: $ultimoDiaMes";
        $log_txt_calculo .= PHP_EOL . "fechaEjecucion: $fechaEjecucion";
        $log_txt_calculo .= PHP_EOL . "fechaInicioMes: $fechaInicioMes";
        $log_txt_calculo .= PHP_EOL . "diaUso: $diaUso";
        $log_txt_calculo .= PHP_EOL . "dia_uso: $dias_uso";
        $log_txt_calculo .= PHP_EOL . "diasDiferencia: $diasDiferencia";
        $log_txt_calculo .= PHP_EOL . "valor_diario: $valor_diario";
        $log_txt_calculo .= PHP_EOL . "valor_uso: $valor_uso";
        $log_txt_calculo .= PHP_EOL . "valor_no_uso: $valor_no_uso";

        $this->crearLog($idContrato, $idTarea, $idEquipo, "Calculo: $log_txt_calculo");

        //update contrato pago
        $sql_pack = "update isp.contrato_pago_pack set dias_uso = $diaUso,
                    dias_no_uso = $diasDiferencia,
                    valor_dia = $valor_diario, 
                    valor_uso = $valor_uso,
                    valor_no_uso = $valor_no_uso";
        if(!empty($tipo_f)){
            $sql_pack .= ", $tipo_f = 'S', $campo_f = '$fecha'";
        }
        $sql_pack .= " where id_contrato = $idContrato and
                    id_clpv = $idClpv and
                    id = $idCuotaPack";
        $oCon->QueryT($sql_pack);

        $this->crearLog($idContrato, $idTarea, $idEquipo, "Calculo, actualiza isp.contrato_pago_pack: $sql_pack");

        //actualiza campos tabla pagos
        if(!empty($tipo_f)){
            $sql_pago = "UPDATE isp.contrato_pago 
                        SET 
                        $tipo_f = 'S',
                        $campo_f = '$fecha'
                        WHERE id = $idCuota";
            $oCon->QueryT($sql_pago);

            $this->crearLog($idContrato, $idTarea, $idEquipo, "Calculo, actualiza tabla pagos: $sql_pago");
        }
        
        //query valor no uso pack
        $sql = "SELECT SUM(valor_no_uso) as val_no_uso,
                SUM(valor_uso) as val_uso
                FROM isp.contrato_pago_pack 
                WHERE id_pago = $idCuota AND
                estado = 'A' AND
                tipo = 'M'";
        if($oCon->Query($sql)){
            if($oCon->NumFilas() > 0){
                $val_no_uso = $oCon->f('val_no_uso');
                $val_uso = $oCon->f('val_uso');
            }
        }
        $oCon->Free();

        $this->crearLog($idContrato, $idTarea, $idEquipo, "Calculo, $sql : val_no_uso = $val_no_uso val_uso $val_uso");
        
        if(empty($val_no_uso)){
            $val_no_uso = 0;
        }

        if(empty($val_uso)){
            $val_uso = 0;
        }

        //query valor no uso cuota
        //$sql = "select valor_no_uso from isp.contrato_pago where id = $idCuota";
        //$saldo_no_uso = consulta_string_func($sql, 'valor_no_uso', $oCon, 0);

        $this->crearLog($idContrato, $idTarea, $idEquipo, "Calculo, valor no uso cuota: $sql = $saldo_no_uso");

        if ($etiqueta == 'R') {
            $valor_no_uso_ok = $saldo_no_uso + $saldo;
        } else {
            $valor_no_uso_ok = $saldo_no_uso + $saldo;
        }

        //update contrato pago
        $sql = "UPDATE isp.contrato_pago SET valor_no_uso = $val_no_uso, 
                valor_uso = $val_uso
				WHERE id_contrato = $idContrato AND
				id_clpv = $idClpv AND
				id = $idCuota";
        $oCon->QueryT($sql);

        $this->crearLog($idContrato, $idTarea, $idEquipo, "Calculo, actualiza cuota: $sql valor no uso = $valor_no_uso_ok, valor uso = $valor_uso");

        return $valor_uso;
    }

    private function crearLog($idContrato, $idTarea, $idEquipo, $log)
    {

        $pathLog = $this->pathLog;

        $a = fopen($pathLog, 'a');

        fwrite($a, "[" . date("d/m/Y H:i:s") . "][$idContrato][$idTarea][$idEquipo]: $log\r\n");
        fclose($a);
    }
}
