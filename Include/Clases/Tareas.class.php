<?php

require_once(DIR_INCLUDE . 'comun.lib.php');

class Tareas
{

    var $oConexion;

    var $oCon;
    var $oIfx;
    var $idEmpresa;
    var $idSucursal;
    var $idClpv;
    var $idContrato;
    var $idTarea;
    var $idEjecucion;
    var $idEquipo;
    var $idProceso;

    function __construct($oCon, $oIfx, $idEmpresa, $idSucursal, $idClpv, $idContrato, $idTarea, $idEjecucion, $idEquipo)
    {

        if (empty($idTarea)) {
            $idTarea = 'null';
        }

        if (empty($idEjecucion)) {
            $idEjecucion = 'null';
        }

        $this->oCon = $oCon;
        $this->oIfx = $oIfx;
        $this->idEmpresa = $idEmpresa;
        $this->idSucursal = $idSucursal;
        $this->idClpv = $idClpv;
        $this->idContrato = $idContrato;
        $this->idTarea = $idTarea;
        $this->idEjecucion = $idEjecucion;
        $this->idEquipo = $idEquipo;
        $this->idProceso = null;
    }

    function parametrosTarea($opTarea)
    {

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;

        //parametros
        $sql = "select fecha_cuota, aprueba_instalacion, cobro_fecha, 
				dia_cobro, suma_dias, dia_corte, activa_desactiva, 
				cuota_aprobacion, dias_consumidos
				from  isp.int_parametros 
				where id_empresa = $idEmpresa and 
				id_sucursal = $idSucursal";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $fecha_cuota = $oCon->f('fecha_cuota');
                $aprueba_instalacion = $oCon->f('aprueba_instalacion');
                $cobro_fecha = $oCon->f('cobro_fecha');
                $dia_cobro = $oCon->f('dia_cobro');
                $suma_dias = $oCon->f('suma_dias');
                $dia_corte = $oCon->f('dia_corte');
                $activa_desactiva = $oCon->f('activa_desactiva');
                $cuota_aprobacion = $oCon->f('cuota_aprobacion');
                $dias_consumidos = $oCon->f('dias_consumidos');
            }
        }
        $oCon->Free();
    }

    function ingresarTarea($idProceso, $fecha, $observaciones, $id_dire, $franja, $motivo, $userWeb, $solicita = '', $adjunto = '', $observacion_cliente = '', $id_prioridad = '', $tecnico_sn = 'N', $reconexion_pago = 'N', $id_equipo = 'N')
    {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userWeb = $_SESSION['U_ID'];
        $sucursal = $_SESSION['U_SUCURSAL'];

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;


        $fechaServer = date("Y-m-d H:i:s");

        //valida franja
        if (empty($franja)) {
            $franja = 'null';
        }

        //valida motivo
        if (empty($motivo)) {
            $motivo = 'null';
        }

        //valida prioridad
        if (empty($id_prioridad)) {
            $id_prioridad = 'null';
        }

        //consulta estado del contrato
        $sql = "SELECT estado, id_sucursal, id_dire, direccion FROM  isp.contrato_clpv WHERE id = $idContrato";
        if ($oCon->QUery($sql)) {
            if ($oCon->NumFilas() > 0) {
                $estado = $oCon->f('estado');
                $id_sucursal = $oCon->f('id_sucursal');
                $id_dire = $oCon->f('id_dire');
                $direccion_actual = $oCon->f('direccion');
            }
        }
        $oCon->Free();

        //valida direccion
        if (empty($id_dire)) {
            $id_dire = 'null';
        }

        //secuencial
        $sql = "select max(secuencial) as secuencial 
                from isp.instalacion_clpv
                where id_empresa = $idEmpresa and
                id_sucursal = $id_sucursal and 
                id_proceso = $idProceso";
        $secuencial = consulta_string_func($sql, 'secuencial', $oCon, 0);

        $secuencial_real = secuencial(2, '0', $secuencial, 9);

        //ingresa tarea
        $sql = "insert into isp.instalacion_clpv(id_empresa, id_sucursal, id_sucursal_ori, id_proceso, secuencial, id_clpv, id_contrato, fecha, 
                user_web, fecha_server, observaciones, id_direccion, id_franja, id_motivo, id_prioridad, solicita, adjunto, observacion_cliente,
                estado_contrato, int_tecnico_sn, reconexion_pago, direccion,id_equipo_retiro)
                values($idEmpresa, $id_sucursal, $sucursal, $idProceso, '$secuencial_real', $idClpv, $idContrato, '$fecha', 
                $userWeb, '$fechaServer', '$observaciones', $id_dire, $franja, $motivo, $id_prioridad, '$solicita', '$adjunto', '$observacion_cliente',
                '$estado', '$tecnico_sn', '$reconexion_pago', '$direccion_actual','$id_equipo') ";
        //echo $sql;exit;
        $oCon->QueryT($sql);

        //selecciona id de la tarea
        $sqlId = "select max(id) as id 
                from isp.instalacion_clpv 
                where id_empresa = $idEmpresa and 
                id_sucursal = $id_sucursal and 
                id_contrato = $idContrato and 
                id_clpv = $idClpv";
        $id = consulta_string_func($sqlId, 'id', $oCon, 0);

        $this->idTarea = $id;
        $this->idProceso = $idProceso;

        return $id;
    }


    function ingresarTarea2($idProceso, $fecha, $observaciones, $id_dire, $franja, $motivo, $userWeb, $solicita = '', $adjunto = '', $observacion_cliente = '', $id_prioridad = '', $tecnico_sn = 'N', $reconexion_pago = 'N', $id_equipo)
    {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userWeb = $_SESSION['U_ID'];
        $sucursal = $_SESSION['U_SUCURSAL'];

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;


        $sqlgein = "SELECT count(*) as conteo
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE COLUMN_NAME = 'id_equipo_retiro' AND TABLE_NAME = 'instalacion_clpv' AND table_schema='isp'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oCon, 0);
        if ($ctralter == 0) {
            $sqlalter = "ALTER table isp.instalacion_clpv add id_equipo_retiro int;";
            $oCon->QueryT($sqlalter);
        }



        if (empty($id_equipo)) {
            $id_equipo = 0;
        }


        $fechaServer = date("Y-m-d H:i:s");

        //valida franja
        if (empty($franja)) {
            $franja = 'null';
        }

        //valida motivo
        if (empty($motivo)) {
            $motivo = 'null';
        }

        //valida prioridad
        if (empty($id_prioridad)) {
            $id_prioridad = 'null';
        }

        //consulta estado del contrato
        $sql = "SELECT estado, id_sucursal, id_dire, direccion FROM  isp.contrato_clpv WHERE id = $idContrato";
        if ($oCon->QUery($sql)) {
            if ($oCon->NumFilas() > 0) {
                $estado = $oCon->f('estado');
                $id_sucursal = $oCon->f('id_sucursal');
                $id_dire = $oCon->f('id_dire');
                $direccion_actual = $oCon->f('direccion');
            }
        }
        $oCon->Free();

        //valida direccion
        if (empty($id_dire)) {
            $id_dire = 'null';
        }

        //secuencial
        $sql = "select max(secuencial) as secuencial 
                from isp.instalacion_clpv
                where id_empresa = $idEmpresa and
                id_sucursal = $id_sucursal and 
                id_proceso = $idProceso";
        $secuencial = consulta_string_func($sql, 'secuencial', $oCon, 0);

        $secuencial_real = secuencial(2, '0', $secuencial, 9);

        //ingresa tarea
        $sql = "insert into isp.instalacion_clpv(id_empresa, id_sucursal, id_sucursal_ori, id_proceso, secuencial, id_clpv, id_contrato, fecha, 
                user_web, fecha_server, observaciones, id_direccion, id_franja, id_motivo, id_prioridad, solicita, adjunto, observacion_cliente,
                estado_contrato, int_tecnico_sn, reconexion_pago, direccion, id_equipo_retiro)
                values($idEmpresa, $id_sucursal, $sucursal, $idProceso, '$secuencial_real', $idClpv, $idContrato, '$fecha', 
                $userWeb, '$fechaServer', '$observaciones', $id_dire, $franja, $motivo, $id_prioridad, '$solicita', '$adjunto', '$observacion_cliente',
                '$estado', '$tecnico_sn', '$reconexion_pago', '$direccion_actual', $id_equipo) ";
        $oCon->QueryT($sql);

        //selecciona id de la tarea
        $sqlId = "select max(id) as id 
                from isp.instalacion_clpv 
                where id_empresa = $idEmpresa and 
                id_sucursal = $id_sucursal and 
                id_contrato = $idContrato and 
                id_clpv = $idClpv";
        $id = consulta_string_func($sqlId, 'id', $oCon, 0);

        $this->idTarea = $id;
        $this->idProceso = $idProceso;

        return $id;
    }

    function ingresarServiciosTarea($id_prod, $id_caja, $cambio_sn = 'N')
    {

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;
        $id = $this->idTarea;

        if (empty($id_caja)) {
            $id_caja = 'null';
        }

        if (empty($id_prod)) {
            $id_prod = 'null';
        }

        //ingresa servicios de la tarea
        $sql = "insert into isp.instalacion_prod(id_empresa, id_sucursal, id_clpv, id_contrato, id_instalacion, id_prod, id_caja, cambio_sn)
                                    values($idEmpresa, $idSucursal, $idClpv, $idContrato, $id, $id_prod, $id_caja, '$cambio_sn')";
        $oCon->QueryT($sql);


        return 'ok';
    }

    function ingresarTecnicoTarea($tipo, $recurso, $nombre, $cargo, $descripcion, $observaciones, $userWeb)
    {

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $id = $this->idTarea;

        $sql = "select fecha, id_franja, id_proceso from isp.instalacion_clpv where id = $id";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $fecha = $oCon->f('fecha');
                $id_franja = $oCon->f('id_franja');
                $id_proceso = $oCon->f('id_proceso');
            }
        }
        $oCon->Free();

        //tipo
        $sql = "select descripcion, color from  isp.int_tipo_proceso where id = $id_proceso";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $descripcion = $oCon->f('descripcion');
                $color = $oCon->f('color');
            }
        }
        $oCon->Free();

        //horas franjas
        $sqlFranja = "select hora_inicio, hora_fin from isp.instalacion_franja where id = '$id_franja'";
        if ($oCon->Query($sqlFranja)) {
            if ($oCon->NumFilas() > 0) {
                $hora_inicio = $oCon->f('hora_inicio');
                $hora_fin = $oCon->f('hora_fin');
            }
        }
        $oCon->Free();

        //inicio - fin
        $StartTime = $fecha . ' ' . $hora_inicio;
        $EndTime = $fecha . ' ' . $hora_fin;

        $sql = "insert into isp.instalacion_tecnico(id_instalacion, tipo, id_tecnico, nombres, id_cargo, id_tipo_proceso, id_prod, user_web, fecha_server)
                                        values($id, '$tipo', '$recurso', '$nombre', '$cargo', $id_proceso, '', $userWeb, now())";
        $oCon->QueryT($sql);

        $sql = "insert into comercial.jqcalendar(Id_empresa, Id_personal, Id_cuenta, Empresa, Actividad, Objetivo, StartTime, EndTime, Color)
                                    values($idEmpresa, '$recurso', $id, '$nombre', '$descripcion', '$observaciones', '$StartTime', '$EndTime', '$color')";
        $oCon->QueryT($sql);

        return 'ok';
    }

    function reporteEjecucionTarea($idEjecucion = null)
    {

        $oCon = $this->oCon;
        $oIfx = $this->oIfx;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;
        $idTarea = $this->idTarea;

        $filtro_id = "";
        if (!empty($idEjecucion)) {
            $filtro_id = " and id = $idEjecucion";
        }

        unset($array);
        //datos ejecucion de tarea
        $sql = "select id, fecha, observacion_cliente, observacion_tecnico, detalle_log, user_web, fecha_server
							from isp.instalacion_ejecucion
							where id_empresa = $idEmpresa and
							id_instalacion = $idTarea and
							id_contrato = $idContrato
							$filtro_id";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $array[] = array(
                        $oCon->f('id'),
                        $oCon->f('fecha'),
                        $oCon->f('observacion_cliente'),
                        $oCon->f('observacion_tecnico'),
                        $oCon->f('detalle_log'),
                        $oCon->f('user_web'),
                        $oCon->f('fecha_server')
                    );
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        // SERVICIOS
        $sql = "SELECT p.id, p.id_dispositivo, p.id_tarjeta, p.id_tipo_prod
                    from isp.instalacion_prod c  ,   isp.int_contrato_caja p WHERE 
                    p.id = c.id_caja and
                    c.id_instalacion = $idTarea ";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $sHtml_ser .= '<h5 class="text-warning">Servicios  OS</h5>';
                $sHtml_ser .= '<table class="table table-condensed table-bordered table-striped table-hover">';
                $sHtml_ser .= '<td>No.</td>';
                $sHtml_ser .= '<td>Tipo</td>';
                $sHtml_ser .= '<td>Tarjeta</td>';
                $sHtml_ser .= '</tr>';
                $i = 1;
                do {
                    $id_tarjeta = $oCon->f('id_tarjeta');
                    $id_tipo_prod = $oCon->f('id_tipo_prod');

                    if ($id_tipo_prod == 1) {
                        $id_tipo_prod = 'TV';
                    } elseif ($id_tipo_prod == 2) {
                        $id_tipo_prod = 'INTERNET';
                    }

                    $sHtml_ser .= '<tr>';
                    $sHtml_ser .= '<td>' . $i . '</td>';
                    $sHtml_ser .= '<td>' . $id_tipo_prod . '</td>';
                    $sHtml_ser .= '<td>' . $id_tarjeta . '</td>';
                    $sHtml_ser .= '</tr>';

                    $i++;
                } while ($oCon->SiguienteRegistro());
                $sHtml_ser .= '</table>';
            }
        }
        $oCon->Free();

        $sHtml = '<ul class="nav nav-tabs" role="tablist">';
        $sHtml .= '<li role="presentation" class="active">';
        $sHtml .= '<a href="#divInfo" aria-controls="divInfo" role="tab" data-toggle="tab">Informaci&oacute;n</a>';
        $sHtml .= '</li>';
        $sHtml .= '<li role="presentation">';
        $sHtml .= '<a href="#divLog" aria-controls="divLog" role="tab" data-toggle="tab">Log Sistema</a>';
        $sHtml .= '</li>';
        $sHtml .= '</ul>';
        $sHtml .= '<div class="tab-content">';
        $sHtml .= '<div role="tabpanel" class="tab-pane active" id="divInfo">';
        $sHtml .= $sHtml_ser;

        $sHtmlLog = '';
        if (count($array) > 0) {
            foreach ($array as $val) {
                $id = $val[0];
                $fecha = $val[1];
                $observacion_cliente = $val[2];
                $observacion_tecnico = $val[3];
                $detalle_log = $val[4];
                $user_web = $val[5];
                $fecha_server = $val[6];

                $sHtmlLog .= '<h4 class="text-primary">
                                        Ejecuci&oacute;n: ' . $id . ', ' . fecha_mysql_dmy($fecha) . '
                              </h4><pre>' . $detalle_log . '</pre>';

                $sql = "select concat(usuario_nombre, ' ', usuario_apellido) as user from comercial.usuario where usuario_id = $user_web";
                $user = consulta_string_func($sql, 'user', $oCon, '');


                $sHtml .= '<h4 class="text-primary">Ejecuci&oacute;n: ' . $id . ', ' . fecha_mysql_dmy($fecha) . '</h4>';

                //tabla datos generales
                $sHtml .= '<div class="col-md-12">
                                <div class="form-group col-md-6">
                                    <label>Usuario: </label>
                                    <p>' . $user . '<p>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Fecha Registro: </label>
                                    <p>' . $fecha_server . '<p>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Observaci&oacute;n Cliente: </label>
                                    <p>' . $observacion_cliente . '<p>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Observaci&oacute;n Tecnico: </label>
                                    <p>' . $observacion_tecnico . '<p>
                                </div>
                            </div>';


                //tabla materiales
                $sql_instalacion_clpv = "SELECT 
                                                auditoria_ejec
                                            from 
                                                isp.instalacion_clpv
                                            where 
                                                id = $idTarea";
                $auditoria_ejec = consulta_string_func($sql_instalacion_clpv, 'auditoria_ejec', $oIfx, 'N');
                if ($auditoria_ejec == 'S') {

                    $sql = "SELECT b.prod_nom_prod, c.dmov_cod_lote as serie, b.id_bodega, b.id_prod, b.observaciones as observacion, b.cantidad,
                                            b.estado, b.facturable as facturado, b.fecha_server, b.tipo_mov, b.minv_num_comp
                                    from isp.instalacion_clpv a INNER JOIN isp.instalacion_materiales b ON a.id = b.id_instalacion
                                                                INNER JOIN saedmov c ON b.minv_num_comp = c.dmov_num_comp
                                    WHERE a.id_contrato = $idContrato and b.id_prod = c.dmov_cod_prod
                                    and b.id_instalacion = $idTarea and b.id_ejecucion = $id
                    
                            UNION ALL
                    
                            SELECT b.prod_nom_prod, c.dmov_cod_lote as serie, b.id_bodega, b.prod_cod_prod, '' as observacion, b.cantidad,
                                            '' as estado, b.facturable as facturado, b.fecha_server, b.tipo_mov, b.minv_num_comp
                                    from isp.instalacion_clpv a INNER JOIN isp.instalacion_materiales_audi b ON a.id = b.id_instalacion
                                                                INNER JOIN saedmov c ON b.minv_num_comp = c.dmov_num_comp
                                    WHERE a.id_contrato = $idContrato and b.prod_cod_prod = c.dmov_cod_prod
                                    and b.id_instalacion = $idTarea and b.id_ejecucion = $id
                                    and b.cantidad > 0
                                    and b.tipo_mov = 'E'
                                    ";
                } else {
                    $sql = "SELECT b.prod_nom_prod, c.dmov_cod_lote as serie, b.id_bodega, b.id_prod, b.observaciones as observacion, b.cantidad,
                                            b.estado, b.facturable as facturado, b.fecha_server, b.tipo_mov, b.minv_num_comp
                                    from isp.instalacion_clpv a INNER JOIN isp.instalacion_materiales b ON a.id = b.id_instalacion
                                                                INNER JOIN saedmov c ON b.minv_num_comp = c.dmov_num_comp
                                    WHERE a.id_contrato = $idContrato and b.id_prod = c.dmov_cod_prod
                                    and b.id_instalacion = $idTarea and b.id_ejecucion = $id";
                }



                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        $sHtml .= '<h5 class="text-warning">Materiales Utilizados</h5>';
                        $sHtml .= '<table class="table table-condensed table-bordered table-striped table-hover">';
                        $sHtml .= '<td>No.</td>';
                        $sHtml .= '<td>Item</td>';
                        $sHtml .= '<td>Descripcion</td>';
                        $sHtml .= '<td>Cantidad</td>';
                        $sHtml .= '<td>Facturable</td>';
                        $sHtml .= '<td>Tipo movimiento</td>';
                        $sHtml .= '<td>Fecha Registro</td>';
                        $sHtml .= '<td>Serie</td>';
                        $sHtml .= '</tr>';
                        $i = 1;
                        do {
                            $id                 = $oCon->f('id');
                            $id_prod            = $oCon->f('id_prod');
                            $cantidad           = $oCon->f('cantidad');
                            $estado             = $oCon->f('estado');
                            $fecha_server       = $oCon->f('fecha_server');
                            $user_web           = $oCon->f('user_web');
                            $facturado          = $oCon->f('facturado');
                            $serie              = $oCon->f('serie');
                            $prod_nom_prod      = $oCon->f('prod_nom_prod');
                            $tipo_mov           = $oCon->f('tipo_mov');
                            $minv_num_comp      = $oCon->f('minv_num_comp');

                            $mostrar_registro = 'S';
                            if ($minv_num_comp > 0) {
                                $sql_verifica_anulados = "SELECT minv_est_minv from saeminv where minv_num_comp = $minv_num_comp";
                                $minv_est_minv = consulta_string_func($sql_verifica_anulados, 'minv_est_minv', $oIfx, '0');
                                if ($minv_est_minv != 1 && $minv_est_minv != 'M' && $minv_est_minv != '1') {
                                    $mostrar_registro = 'N';
                                }
                            }

                            if ($mostrar_registro == 'S') {
                                $tipo_mov_txt = 'INGRESO';
                                if ($tipo_mov == 'I') {
                                    $tipo_mov_txt = 'INGRESO';
                                } else if ($tipo_mov == 'E') {
                                    $tipo_mov_txt = 'EGRESO';
                                }

                                $sHtml .= '<tr>';
                                $sHtml .= '<td>' . $i . '</td>';
                                $sHtml .= '<td>' . $id_prod . '</td>';
                                $sHtml .= '<td>' . $prod_nom_prod . '</td>';
                                $sHtml .= '<td>' . $cantidad . '</td>';
                                $sHtml .= '<td>' . $facturado . '</td>';
                                $sHtml .= '<td>' . $tipo_mov_txt . '</td>';
                                $sHtml .= '<td>' . $fecha_server . '</td>';
                                $sHtml .= '<td>' . $serie . '</td>';
                                $sHtml .= '</tr>';

                                $i++;
                            }
                        } while ($oCon->SiguienteRegistro());
                        $sHtml .= '</table>';
                    }
                }
                $oCon->Free();

                //tabla adjuntos

            } //fin foreach

        } //fin if count

        $sHtml .= '</div>';

        //pestana Log
        $sHtml .= '<div role="tabpanel" class="tab-pane" id="divLog">';
        $sHtml .= $sHtmlLog;
        $sHtml .= '</div>';
        $sHtml .= '</div>';

        return $sHtml;
    }

    function ejecutarTarea($fecha, $d_c, $d_t, $cod_inc = 0, $cod_solu = 0)
    {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        //variables de sesion
        $userWeb = $_SESSION['U_ID'];

        $fecha_server = date("Y-m-d H:i:s");

        $oCon         = $this->oCon;
        $idEmpresa     = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv     = $this->idClpv;
        $idContrato = $this->idContrato;
        $idTarea     = $this->idTarea;
        $idEquipo     = $this->idEquipo;
        $idProceso     = $this->idProceso;

        //ingresa ejecucion
        $sqlEje = "insert into isp.instalacion_ejecucion(id_empresa, 			id_sucursal, 		id_clpv, 			id_contrato,
													 id_instalacion, 		user_web, 			fecha_server, 		fecha,
													 observacion_cliente, 	observacion_tecnico,     id_incidencia_arc,  id_solu_arc)
											  values($idEmpresa, 			$idSucursal, 		$idClpv, 			$idContrato,
													 $idTarea, 				$userWeb, 			'$fecha_server', 	'$fecha',
													'$d_c', 				'$d_t',             $cod_inc,             $cod_solu )";
        $oCon->QueryT($sqlEje);

        //id ejecucion
        $sqlMax = "select max(id) as id 
					from isp.instalacion_ejecucion 
					where id_empresa = $idEmpresa and 
					id_clpv = $idClpv and
					id_contrato = $idContrato";
        $idEjecucion = consulta_string_func($sqlMax, 'id', $oCon, 0);

        $this->idEjecucion = $idEjecucion;
        return $idEjecucion;
    }

    function ejecutarTareaEquipo($fecha, $d_c, $d_t, $idPlan, $idequipo_tmp)
    {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        //variables de sesion
        $userWeb = $_SESSION['U_ID'];

        $fecha_server = date("Y-m-d H:i:s");

        $oCon         = $this->oCon;
        $idEmpresa     = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv     = $this->idClpv;
        $idContrato = $this->idContrato;
        $idTarea     = $this->idTarea;
        $idEquipo     = $this->idEquipo;
        $idProceso     = $this->idProceso;

        if (empty($idEquipo) || $idEquipo == 0) {
            $idEquipo = $idequipo_tmp;
        }
        //parametros del proceso
        $sql = "select op_ejecucion, op_corte, etiqueta, op_pago
                from  isp.int_tipo_proceso
                where id = $idProceso";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $op_ejecucion     = $oCon->f('op_ejecucion');
                $op_corte         = $oCon->f('op_corte');
                $etiqueta         = $oCon->f('etiqueta');
                $op_pago         = $oCon->f('op_pago');
            }
        }
        $oCon->Free();

        $controlTablaPagos = true;
        if ($op_pago == 'S') {
            $mesCorte = substr($fecha, 5, 2);
            $anioCorte = substr($fecha, 0, 4);

            $sql = "select id from  isp.int_contrato_caja_pack where id_caja = $idEquipo and id_contrato = $idContrato and id_prod = $idPlan";
            $idPack = consulta_string_func($sql, 'id', $oCon, 0);

            //id pago
            $sql = "select id, id_pago, dias_uso, dias_no_uso, tarifa, valor_no_uso, valor_dia
                    FROM  isp.contrato_pago_pack 
                    where id_contrato = $idContrato and 
                    id_clpv = $idClpv and
					id_caja = $idEquipo and
					id_pack = $idPack and
                    estado != 'AN' and
                    month(fecha) = '$mesCorte' and
                    year(fecha) = '$anioCorte'";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    $idPagoPack = $oCon->f('id');
                    $idPago = $oCon->f('id_pago');
                    $valor_diario = $oCon->f('valor_dia');
                    $dias_uso = $oCon->f('dias_uso');
                    $dias_no_uso = $oCon->f('dias_no_uso');
                    $valor_no_uso = $oCon->f('valor_no_uso');
                    $tarifa = $oCon->f('tarifa');
                }
            }
            $oCon->Free();

            if (empty($idPagoPack) && empty($idPago)) {
                $controlTablaPagos = false;
            }
        }

        if ($controlTablaPagos == true) {

            //parametros generales
            $sql = "select fecha_cuota, aprueba_instalacion, estado_aprueba, cuota_aprobacion,
					dias_consumidos
					from  isp.int_parametros 
					where id_empresa = $idEmpresa and 
					id_sucursal = $idSucursal";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $fecha_cuota = $oCon->f('fecha_cuota');
                        $aprueba_instalacion = $oCon->f('aprueba_instalacion');
                        $estado_aprueba = $oCon->f('estado_aprueba');
                        $cuota_aprobacion = $oCon->f('cuota_aprobacion');
                        $dias_consumidos = $oCon->f('dias_consumidos');
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();

            if (!empty($op_ejecucion)) {

                $diasDiferencia = 0;
                $valor_diario = 0;
                $valor_uso = 0;
                $valor_no_uso = 0;
                $diaUsoOk = 0;
                $diasDiferenciaOk = 0;

                if ($etiqueta == 'D') { //desconexion

                    //fecha corte yyyy/mm/dd
                    $fechaProdOk = str_replace("-", "/", $fecha);
                    $fecha_ok = $anioCorte . '/' . $mesCorte . '/01';

                    $ultimoDiaMes = ultimoDiaMesFunc($fecha);

                    if ($dias_consumidos == 'S') {
                        $diaUso = diferenciaDias($fecha_ok, $fechaProdOk);
                        $diasDiferencia = $ultimoDiaMes - $diaUso;
                        $valor_diario = round($tarifa / $ultimoDiaMes, 6);
                        $valor_uso = round($valor_diario * $diaUso, 6);
                        $valor_no_uso = round($valor_diario * $diasDiferencia, 6);
                    } else {
                        $diaUso = ultimoDiaMesFunc($fecha);
                    }

                    //update contrato pago
                    $sql = "update contrato_pago_pack set dias_uso = $diaUso,
							dias_no_uso = $diasDiferencia,
							valor_dia = $valor_diario, 
							valor_uso = $valor_uso,
							valor_no_uso = $valor_no_uso,
							corte = 'S',
							fecha_corte = '$fecha'
							where id_contrato = $idContrato and
							id_clpv = $idClpv and
							id = $idPagoPack";
                    $oCon->QueryT($sql);

                    //valor no uso
                    $sql_s = "select sum(valor_no_uso) as saldo FROM  isp.contrato_pago_pack where id = $idPagoPack";
                    $saldo = consulta_string_func($sql_s, 'saldo', $oCon, 0);

                    if ($saldo > 0) {

                        //valor no uso
                        $sql = "select valor_no_uso FROM  isp.contrato_pago where id = $idPago";
                        $saldo_no_uso = consulta_string_func($sql, 'valor_no_uso', $oCon, 0);

                        //update contrato pago
                        $sql = "update contrato_pago set 
								valor_no_uso = ($saldo_no_uso + $saldo)
								where id_contrato = $idContrato and
								id_clpv = $idClpv and
								id = $idPago";
                        $oCon->QueryT($sql);
                    }
                }
            }
        }
    }


    /***************************************************
	 @ ingresarPeticion
	 + Inserta en BDD la peticion de proceso automatico
	 + Retorna el id de la peticion
     **************************************************/
    public function ingresarPeticion($tipo, $id_cmd, $idUser, $prioridad, $fecha, $estado, $titulo, $detalle, $planes, $peticion, $fecha_inicio, $fecha_fin, $consulta = '', $id = 0, $fin_cmd = 'S', $ejecutar_sn = 'S', $inactivo_sn = 'N')
    {

        $oCon = $this->oCon;
        $idContrato = $this->idContrato;
        $idTarea = $this->idTarea;
        $idEquipo = $this->idEquipo;

        if (empty($idEquipo)) {
            $idEquipo = $id;
        }

        //consulta id dispositivo
        $sql = "SELECT id_dispositivo, id_tarjeta, id_caja, id_perfil, estado, ip 
                from  isp.int_contrato_caja 
                WHERE id = '$idEquipo'";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $id_dispositivo = $oCon->f('id_dispositivo');
                $id_tarjeta = $oCon->f('id_tarjeta');
                $id_caja = $oCon->f('id_caja');
                $id_perfil = $oCon->f('id_perfil');
                $estado_e = $oCon->f('estado');
                $ip = $oCon->f('ip');
            }
        }
        $oCon->Free();

        if (empty($id_perfil)) {
            $id_perfil = 'null';
        }

        $sql = "INSERT INTO isp.int_red_peticiones (id_dispositivo, id_contrato, id_equipo, id_instalacion, serial, 
                                                tipo, id_cmd, id_usuario, id_prioridad, fecha, estado, onu,
                                                titulo, detalle, planes, peticion, fecha_inicio, 
                                                fecha_fin, fecha_server, id_perfil, consulta, fin_cmd, 
                                                estado_equipo, ejecutar_sn, inactivo_sn, ip)
                                        values($id_dispositivo, $idContrato, $idEquipo, $idTarea, '$id_tarjeta', 
                                                '$tipo', $id_cmd, $idUser, $prioridad, '$fecha', '$estado', '$id_caja',
                                                '$titulo', '$detalle', '$planes', '$peticion', '$fecha_inicio', 
                                                '$fecha_fin', now(), $id_perfil, '$consulta', '$fin_cmd', 
                                                '$estado_e', '$ejecutar_sn', '$inactivo_sn', '$ip')";
        $oCon->QueryT($sql);

        //id peticion
        $sql = "SELECT max(id) as id from  isp.int_red_peticiones WHERE id_dispositivo = $id_dispositivo AND id_contrato = $idContrato";
        $id = consulta_string_func($sql, 'id', $oCon, 0);

        return $id;
    }

    /***************************************************
	 @ consultaPeticion
	 + Consulta respuesta de la peticion solicitada
	 + Retorna array(error, consulta, respuesta)
     **************************************************/
    function consultaPeticion($id)
    {
        $oCon = $this->oCon;
        $idContrato = $this->idContrato;

        $array = array();
        $error = '';
        $consulta = '';
        $respuesta = '';

        $sql = "SELECT error, consulta, respuesta from  isp.int_red_peticiones WHERE id_contrato = $idContrato and id = $id";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $error = $oCon->f('error');
                $consulta = $oCon->f('consulta');
                $respuesta = $oCon->f('respuesta');
            }
        }
        $oCon->Free();

        $array[0] = $error;
        $array[1] = $consulta;
        $array[2] = $respuesta;

        return $array;
    }

    /***************************************************
	 @ verificaEstadoProcesoAutomatico
	 + Vertifica true/false del proceso automatico
	 + De no estar activo, ejecuta .bat
     **************************************************/
    function verificaEstadoProcesoAutomatico($dispositivo)
    {
        $oCon = $this->oCon;

        //consulta dispositivo
        $sql = "SELECT pa_estado, cron, on_off from  isp.int_dispositivos WHERE id = $dispositivo";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $pa_estado = $oCon->f('pa_estado');
                $cron = $oCon->f('cron');
                $on_off = $oCon->f('on_off');
            }
        }
        $oCon->Free();

        if ($on_off == 1) {
            if ($pa_estado == 'I') {
                $cmd = "C:/Jireh/task_manager/$cron";
                pclose(popen("start /B " . $cmd, "r"));
            }
        }
    }

    /***************************************************
	 @ respuestaGpon
	 + Da tratamiento a la respuesta de la peticion
	 + Retorna array
     **************************************************/
    function respuestaPeticion($consulta)
    {
        $array = explode(';', $consulta);

        $array_ok = array();
        for ($i = 0; $i < count($array); $i++) {
            unset($array_r);
            $array_r = explode("\r\n", $array[$i]);
            for ($j = 3; $j < count($array_r) - 1; $j++) {
                unset($array_c);
                $array_r[$j] = preg_replace('/\s+/', ' ', $array_r[$j]); // Elimina multiples espacios en blanco
                $array_c = explode(" ", $array_r[$j]);
                for ($d = 0; $d < count($array_c); $d++) {
                    $array_ok[] = $array_c[$d];
                }
            }
        }

        return $array_ok;
    }
}
