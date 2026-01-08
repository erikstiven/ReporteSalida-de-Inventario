<?php
require_once(DIR_INCLUDE . 'comun.lib.php');
require_once(DIR_INCLUDE . 'Clases/Contratos.class.php');

class Equipos extends Contratos
{

    var $oCon;
    var $oIfx;
    var $idEmpresa;
    var $idSucursal;
    var $idClpv;
    var $idContrato;
    var $idEquipo;
    var $pathLog;

    function __construct($oCon, $oIfx, $idEmpresa, $idSucursal, $idClpv, $idContrato, $idEquipo)
    {

        parent::__construct($oCon, $oIfx, $idEmpresa, $idSucursal, $idClpv, $idContrato);

        $this->oCon = $oCon;
        $this->oIfx = $oIfx;
        $this->idEmpresa = $idEmpresa;
        $this->idSucursal = $idSucursal;
        $this->idClpv = $idClpv;
        $this->idContrato = $idContrato;
        $this->idEquipo = $idEquipo;
    }

    /***************************************************
     * @ consultaEquipo
     * + Consulta data de equipo por contrato / id equipo
     * + Retorna array
     **************************************************/
    function consultaEquipo()
    {

        $oCon = $this->oCon;
        $idEquipo = $this->idEquipo;

        $array = array();

        $sql = "select * from isp.int_contrato_caja where id = $idEquipo";
        if ($oCon->Query($sql)) {

            if ($oCon->NumFilas() > 0) {
                $array[] = array(
                    $oCon->f('id_empresa'), $oCon->f('id_sucursal'), $oCon->f('id_clpv'), $oCon->f('id_contrato'), $oCon->f('id_ubicacion'), $oCon->f('id_dispositivo'),
                    $oCon->f('id_marca'), $oCon->f('id_puerto'), $oCon->f('id_equipo'), $oCon->f('id_tarjeta'), $oCon->f('id_caja'),
                    $oCon->f('estado'), $oCon->f('user_web'), $oCon->f('fecha'), $oCon->f('fecha_corte'), $oCon->f('fecha_reconexion'),
                    $oCon->f('fecha_vence'), $oCon->f('fecha_server'), $oCon->f('id_tipo_prod'), $oCon->f('tipo'), $oCon->f('ubicacion'),
                    $oCon->f('medida'), $oCon->f('id_nap'), $oCon->f('id_contr_grupo'), $oCon->f('prod_cod_caja'),
                    $oCon->f('prod_nom_caja'), $oCon->f('latitud'), $oCon->f('longitud'), $oCon->f('id_user_voip')
                );
            }
        }
        $oCon->Free();

        return $array;
    }

    /***************************************************
     * @ consultaPlan
     * + Consulta data de planes por contrato / id equipo
     * + Retorna array
     **************************************************/
    function consultaPlan()
    {

        $oCon = $this->oCon;
        $idEquipo = $this->idEquipo;
        $idContrato = $this->idContrato;

        $array = array();

        $filtro_e = "";
        if (!empty($idEquipo)) {
            $filtro_e = " AND id_caja = $idEquipo";
        }

        $sql = "SELECT * from isp.int_contrato_caja_pack WHERE id_contrato = $idContrato and estado not in ('E') $filtro_e";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $array[] = array(
                        $oCon->f('id'), $oCon->f('id_clpv'), $oCon->f('id_contrato'), $oCon->f('id_caja'), $oCon->f('id_prod'), $oCon->f('cod_prod'),
                        $oCon->f('precio'), $oCon->f('fecha'), $oCon->f('fecha_corte'), $oCon->f('fecha_reconexion'), $oCon->f('fecha_vence'),
                        $oCon->f('fecha_pago'), $oCon->f('tarifa'), $oCon->f('estado'), $oCon->f('user_web'), $oCon->f('fecha_server')
                    );
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        return $array;
    }

    /***************************************************
     * @ consultarParametrosEquipo
     * + Consulta parametros de equipo
     * + Retorna HTML
     **************************************************/
    function consultarParametrosEquipo($op)
    {

        $oIfx = $this->oIfx;
        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;
        $idEquipo = $this->idEquipo;

        $arrayContrato = $this->consultarContrato();
        if (count($arrayContrato) > 0) {
            foreach ($arrayContrato as $val) {
                $codigo = $val[5];
                $nom_clpv = $val[6];
            }
        }

        $sql = "SELECT codigo_servicio_sn FROM isp.int_parametros_general WHERE id_empresa = $idEmpresa";
        $codigo_servicio_sn = consulta_string_func($sql, 'codigo_servicio_sn', $oCon, '');

        $modal_i = '<div class="modal-dialog modal-lg" role="document" style="width:85%;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h5 class="modal-title" id="myModalLabel">' . $codigo . ' - ' . utf8_decode($nom_clpv) . '</h5>
                        </div>
                        <div class="modal-body" style="margin-top:0xp;">';

        //informacion caja
        $sql = "SELECT id_ubicacion, id_dispositivo, id_marca, id_equipo, id_tarjeta,
                id_caja, estado, user_web, fecha, fecha_corte, fecha_reconexion, fecha_vence
                FROM isp.int_contrato_caja
                WHERE id = $idEquipo";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $id_ubicacion = $oCon->f('id_ubicacion');
                $id_dispositivo = $oCon->f('id_dispositivo');
                $id_marca = $oCon->f('id_marca');
                $id_equipo = $oCon->f('id_equipo');
                $id_tarjeta = $oCon->f('id_tarjeta');
                $id_caja = $oCon->f('id_caja');
                $estado = $oCon->f('estado');
                $user_web = $oCon->f('user_web');
                $fecha = $oCon->f('fecha');
                $fecha_corte = $oCon->f('fecha_corte');
                $fecha_reconexion = $oCon->f('fecha_reconexion');
                $fecha_vence = $oCon->f('fecha_vence');
            }
        }
        $oCon->Free();

        $sql = "select estado, color from isp.int_estados_equipo where id = '$estado'";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $estadoCaja = $oCon->f('estado');
                $colorCaja = $oCon->f('color');
            }
        }
        $oCon->Free();

        //dispositivo
        if (!empty($id_dispositivo)) {
            $sql = "SELECT serial FROM isp.int_dispositivos WHERE id = $id_dispositivo";
            $serial = consulta_string_func($sql, 'serial', $oCon, '');
        }

        //marca
        if (!empty($id_marca)) {
            $sql = "SELECT modelo FROM isp.int_modelos WHERE id = $id_marca";
            $marca = consulta_string_func($sql, 'modelo', $oCon, '');
        }

        //ubicacion
        if (!empty($id_ubicacion)) {
            $sql = "SELECT ubicacion FROM isp.int_ubicaciones WHERE id = $id_ubicacion";
            $ubicacion = consulta_string_func($sql, 'ubicacion', $oCon, '');
        }

        $sHtml = '<h4 class="text-primary">Parametros Equipo <small style="color: ' . $colorCaja . '"><strong>[ ' . $estadoCaja . ' ]</strong></small></h4>';
        $sHtml .= '<div class="row">&nbsp;</div>';
        $sHtml .= '<div class="row">';
        $sHtml .= '<label class="col-md-1">Registro:</label>';
        $sHtml .= '<div class="col-md-2">' . $fecha . '</div>';
        $sHtml .= '<label class="col-md-1">Corte:</label>';
        $sHtml .= '<div class="col-md-2">' . $fecha_corte . '</div>';
        $sHtml .= '<label class="col-md-1">Reconexi&oacute;n:</label>';
        $sHtml .= '<div class="col-md-2">' . $fecha_reconexion . '</div>';
        $sHtml .= '<label class="col-md-1">Vencimiento:</label>';
        $sHtml .= '<div class="col-md-2">' . $fecha_vence . '</div>';
        $sHtml .= '</div>';

        $sHtml .= '<br>';

        $sHtml .= '<div class="row">';
        $sHtml .= '<label class="col-md-1">Sistema:</label>';
        $sHtml .= '<div class="col-md-2">' . $serial . '</div>';
        $sHtml .= '<label class="col-md-1">Marca:</label>';
        $sHtml .= '<div class="col-md-2">' . $marca . '</div>';
        $sHtml .= '<label class="col-md-1">Ubicaci&oacute;n</label>';
        $sHtml .= '<div class="col-md-2">' . $ubicacion . '</div>';
        $sHtml .= '</div>';

        $sHtml .= '<br>';

        $sHtml .= '<div class="row">';
        $sHtml .= '<label class="col-md-1" for="equipoTarjeta">Tarjeta:</label>';
        $sHtml .= '<div class="col-md-5">
                            <input type="text" id="equipoTarjeta" name="equipoTarjeta"  class="form-control input-sm" value="' . $id_tarjeta . '" readonly> 
                            <input type="hidden" name="getSincroniza" id="getSincroniza" value="0">
                    </div>';
        $sHtml .= '<label class="col-md-1" for="equipoCaja">Caja:</label>';
        $sHtml .= '<div class="col-md-5">
                            <input type="text" id="equipoCaja" name="equipoCaja" class="form-control input-sm" value="' . $id_caja . '" readonly> 
                    </div>';
        $sHtml .= '</div>';

        $sHtml .= '<h4 class="text-primary">Planes Equipo</h4>';

        $sHtml .= '<div class="table-responsive">';

        $sql_pack = "";
        $columna_cid = '';
        if($codigo_servicio_sn == 'S'){
            $sql_pack = ", c.codigo_cid ";
            $columna_cid = '<td class="text-sys text-info">CID</td>';
        }

        $sql = "SELECT c.id_prod, c.cod_prod, c.fecha, c.fecha_corte, c.fecha_reconexion, c.fecha_vence,
                c.fecha_pago, c.tarifa, c.estado, c.precio $sql_pack
                FROM isp.int_contrato_caja_pack c
                WHERE
                id_clpv = $idClpv AND
                id_contrato = $idContrato AND
                id_caja = $idEquipo";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $sHtml .= '<table class="table table-condensed table-bordered table-striped table-hover" style="width: 99%;" align="center">';
                $sHtml .= '<theader>';
                $sHtml .= '<tr>';
                $sHtml .= '<td class="text-sys text-info">Plan</td>';
                $sHtml .= $columna_cid;
                $sHtml .= '<td class="text-sys text-info">Registro</td>';
                $sHtml .= '<td class="text-sys text-info">Corte</td>';
                $sHtml .= '<td class="text-sys text-info">Reconexi&oacute;n</td>';
                $sHtml .= '<td class="text-sys text-info">Vence</td>';
                $sHtml .= '<td class="text-sys text-info">Estado</td>';
                $sHtml .= '<td class="text-sys text-info">Tarifa</td>';
                $sHtml .= '</tr>';
                $sHtml .= '</theader>';
                $sHtml .= '<tbody>';

                $totalPaquetes = 0;
                do {
                    $cod_prod = $oCon->f('cod_prod');
                    $fecha = $oCon->f('fecha');
                    $fecha_corte = $oCon->f('fecha_corte');
                    $fecha_reconexion = $oCon->f('fecha_reconexion');
                    $fecha_vence = $oCon->f('fecha_vence');
                    $fecha_pago = $oCon->f('fecha_pago');
                    $tarifa = $oCon->f('tarifa');
                    $precio = $oCon->f('precio');
                    $estado = $oCon->f('estado');
                    $codigo_cid = $oCon->f('codigo_cid');

                    $sql = "select prod_nom_prod from saeprod where prod_cod_prod = '$cod_prod' and prod_cod_empr = $idEmpresa";
                    $prod_nom_prod = consulta_string_func($sql, 'prod_nom_prod', $oIfx, '');

                    $codigo_cid_tbl = '';
                    if($codigo_servicio_sn == 'S'){
                        $codigo_cid_tbl = '<td class="text-sys">' . $codigo_cid . '</td>';
                    }

                    $sHtml .= '<tr>';
                    $sHtml .= '<td class="text-sys">' . $prod_nom_prod . '</td>';
                    $sHtml .= $codigo_cid_tbl;
                    $sHtml .= '<td class="text-sys">' . $fecha . '</td>';
                    $sHtml .= '<td class="text-sys">' . $fecha_corte . '</td>';
                    $sHtml .= '<td class="text-sys">' . $fecha_reconexion . '</td>';
                    $sHtml .= '<td class="text-sys">' . $fecha_vence . '</td>';
                    $sHtml .= '<td class="text-sys">' . $estado . '</td>';
                    $sHtml .= '<td class="text-sys" align="right">' . $precio . '</td>';
                    $sHtml .= '</tr>';

                    $totalPaquetes += $precio;
                } while ($oCon->SiguienteRegistro());
                $sHtml .= '<tr>';
                if($codigo_servicio_sn == 'S'){
                    $sHtml .= '<td colspan="7" class="bg-danger text-sys"></td>';
                }else{
                    $sHtml .= '<td colspan="6" class="bg-danger text-sys"></td>';
                }
                
                $sHtml .= '<td align="right" class="bg-danger text-sys">' . $totalPaquetes . '</td>';
                $sHtml .= '<td class="bg-danger text-sys"></td>';
                $sHtml .= '</tr>';
                $sHtml .= '</tbody>';
                $sHtml .= '</table>';
            }
        }
        $oCon->Free();

        $modal_f .= '</div>';
        $modal_f .= '</div>
                        <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                        </div>
                    </div>';

        $sHtmlOk = '';
        if ($op == 0) {
            $sHtmlOk .= $modal_i . $sHtml . $modal_f;
        } elseif ($op == 1) {
            $sHtmlOk .= $sHtml;
        }

        return $sHtmlOk;
    }

    /***************************************************
     * @ consultarPlanesEquipo
     * + Consulta planes de equipo
     * + Retorna HTML
     **************************************************/
    function consultarPlanesEquipo()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

        $oIfx = $this->oIfx;
        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;
        $idEquipo = $this->idEquipo;

        $sHtml = '<h4 class="text-primary">Planes Equipo</h4>';

        $sHtml .= '<div class="table-responsive">';

        //AND id_caja = $idEquipo
        $sql = "SELECT c.id_prod, c.cod_prod, c.fecha, c.fecha_corte, c.fecha_reconexion, c.fecha_vence, 
                c.fecha_pago, c.tarifa, c.estado, c.precio, d.egress, d.ingress
                FROM isp.int_contrato_caja_pack c, isp.int_paquetes d
                WHERE
                id_clpv = $idClpv AND
                id_contrato = $idContrato AND
				id_caja = $idEquipo AND
				c.cod_prod = d.prod_cod_prod";

        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $sHtml .= '<table class="table table-condensed table-bordered table-striped table-hover" style="width: 99%;" align="center">';
                $sHtml .= '<theader>';
                $sHtml .= '<tr>';
                $sHtml .= '<td class="text-sys text-info">Plan</td>';
                $sHtml .= '<td class="text-sys text-info">Registro</td>';
                $sHtml .= '<td class="text-sys text-info">Corte</td>';
                $sHtml .= '<td class="text-sys text-info">Reconexi&oacute;n</td>';
                $sHtml .= '<td class="text-sys text-info">Vence</td>';
                $sHtml .= '<td class="text-sys text-info">Tarifa</td>';
                $sHtml .= '<td class="text-sys text-info">Estado</td>';
                $sHtml .= '</tr>';
                $sHtml .= '</theader>';
                $sHtml .= '<tbody>';

                $totalPaquetes = 0;
                do {
                    $cod_prod = $oCon->f('cod_prod');
                    $fecha = $oCon->f('fecha');
                    $fecha_corte = $oCon->f('fecha_corte');
                    $fecha_reconexion = $oCon->f('fecha_reconexion');
                    $fecha_vence = $oCon->f('fecha_vence');
                    $fecha_pago = $oCon->f('fecha_pago');
                    $tarifa = $oCon->f('tarifa');
                    $precio = $oCon->f('precio');
                    $estado = $oCon->f('estado');
                    $_SESSION["vel_subida_pack"] = $oCon->f('egress');
                    $_SESSION["vel_bajada_pack"] = $oCon->f('ingress');

                    $sql = "select prod_nom_prod from saeprod where prod_cod_prod = '$cod_prod' and prod_cod_empr = $idEmpresa";
                    $prod_nom_prod = consulta_string_func($sql, 'prod_nom_prod', $oIfx, '');

                    $fechaOk = '';
                    if (!empty($fecha)) {
                        $fechaOk = fecha_mysql_dmy(substr($fecha, 0, 10)) . ' ' . substr($fecha, 10, 10);
                    }

                    $fechaCorteOk = '';
                    if (!empty($fechaCorte)) {
                        $fechaCorteOk = fecha_mysql_dmy(substr($fechaCorte, 0, 10)) . ' ' . substr($fechaCorte, 10, 10);
                    }

                    $fechaReconexionOk = '';
                    if (!empty($fechaReconexion)) {
                        $fechaReconexionOk = fecha_mysql_dmy(substr($fechaReconexion, 0, 10)) . ' ' . substr($fechaReconexion, 10, 10);
                    }

                    $fecha_venceOk = '';
                    if (!empty($fecha_vence)) {
                        $fecha_venceOk = fecha_mysql_dmy(substr($fecha_vence, 0, 10)) . ' ' . substr($fecha_vence, 10, 10);
                    }

                    $sHtml .= '<tr>';
                    $sHtml .= '<td class="text-sys">' . $prod_nom_prod . '</td>';
                    $sHtml .= '<td class="text-sys">' . $fechaOk . '</td>';
                    $sHtml .= '<td class="text-sys">' . $fechaCorteOk . '</td>';
                    $sHtml .= '<td class="text-sys">' . $fechaReconexionOk . '</td>';
                    $sHtml .= '<td class="text-sys">' . $fecha_venceOk . '</td>';
                    $sHtml .= '<td class="text-sys" align="right">' . $precio . '</td>';
                    $sHtml .= '<td class="text-sys">' . $estado . '</td>';
                    $sHtml .= '</tr>';

                    $totalPaquetes += $precio;
                } while ($oCon->SiguienteRegistro());
                $sHtml .= '<tr>';
                $sHtml .= '<td colspan="5" class="bg-danger text-sys"></td>';
                $sHtml .= '<td align="right" class="bg-danger text-sys">' . $totalPaquetes . '</td>';
                $sHtml .= '<td class="bg-danger text-sys"></td>';
                $sHtml .= '</tr>';
                $sHtml .= '</tbody>';
                $sHtml .= '</table>';
            }
        }
        $oCon->Free();

        $sHtml .= '</div>';

        return $sHtml;
    }

    /***************************************************
     * @ consultarPlanesCuotaEquipo
     * + Consulta planes de equipo relacionados con cuota
     * + Retorna array
     **************************************************/
    function consultarPlanesCuotaEquipo($idPago)
    {

        $oIfx = $this->oIfx;
        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;
        $idEquipo = $this->idEquipo;

        $array = array();

        $sql = "select id, paquete FROM isp.int_paquetes";
        $array_p = array_dato($oCon, $sql, 'id', 'paquete');

        //AND id_caja = $idEquipo
        $sql = "select p.id, p.fecha, p.estado, p.estado_fact, p.dias, p.valor_pago, p.valor_dia,
				p.tarifa, p.dias_uso, p.valor_uso, p.valor_no_uso, p.cod_prod, p.dias_no_uso,
				c.id_caja, c.id_tarjeta, p.id_prod
				FROM isp.contrato_pago_pack p, isp.int_contrato_caja c
				where p.id_clpv = c.id_clpv and
				p.id_contrato = c.id_contrato and
				p.id_empresa = c.id_empresa and
				p.id_caja = c.id and
				c.id_empresa = $idEmpresa and
				p.id_clpv = $idClpv and
				p.id_contrato = $idContrato and
				p.id_pago = $idPago and
				p.estado != 'AN'";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $cod_prod = $oCon->f('cod_prod');
                    $id_prod = $oCon->f('id_prod');
                    $fecha = $oCon->f('fecha');
                    $fecha_corte = $oCon->f('fecha_corte');
                    $fecha_reconexion = $oCon->f('fecha_reconexion');
                    $fecha_vence = $oCon->f('fecha_vence');
                    $fecha_pago = $oCon->f('fecha_pago');
                    $tarifa = $oCon->f('tarifa');
                    $estado_fact = $oCon->f('estado_fact');
                    $dias_uso = $oCon->f('dias_uso');
                    $dias_no_uso = $oCon->f('dias_no_uso');
                    $valor_dia = $oCon->f('valor_dia');
                    $valor_no_uso = $oCon->f('valor_no_uso');
                    $id_tarjeta = $oCon->f('id_tarjeta');

                    $pagar = ($tarifa - $valor_no_uso);

                    $prod_nom_prod = $array_p[$id_prod];

                    $estado = $estado_fact;
                    if (empty($estado_fact)) {
                        $estado = 'PE';
                    }

                    $array[] = array($id_tarjeta, $prod_nom_prod, $dias_uso, $dias_no_uso, $tarifa, $valor_dia, $pagar, $estado, $oCon->f('estado'), $cod_prod);
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        return $array;
    }

    /***************************************************
     * @ registraCajaContrato
     * + Ingresa equipo / caja
     * + Retorna id equipo
     **************************************************/
    function registraCajaContrato($array)
    {

        $id = 0;

        if (count($array) > 0) {

            $idUser = $_SESSION['U_ID'];

            $oCon = $this->oCon;
            $idEmpresa = $this->idEmpresa;
            $idSucursal = $this->idSucursal;
            $idClpv = $this->idClpv;
            $idContrato = $this->idContrato;

            $fechaServer = date("Y-m-d H:i:s");

            unset($array_null);
            $array_null[0] = 'S';
            $array_null[1] = 'S';
            $array_null[2] = 'S';
            $array_null[3] = 'S';
            $array_null[4] = 'S';
            $array_null[21] = 'S';

            $sql = "INSERT INTO isp.int_contrato_caja(id_empresa, id_sucursal, id_clpv, id_contrato, 
                                                id_ubicacion, 
                                                id_dispositivo,
                                                id_marca, 
                                                id_puerto, 
                                                id_equipo, 
                                                id_tarjeta, 
                                                id_caja, 
                                                onu, 
                                                nombre, 
                                                estado, 
                                                user_web, 
                                                fecha, 
                                                fecha_vence, 
                                                ubicacion, 
                                                id_tipo_prod, 
                                                tipo, 
                                                latitud, 
                                                longitud, 
                                                id_inst_desp_cab, 
                                                id_inst_desp_det, 
                                                medida, id_nap, 
                                                id_insta_clpv, 
                                                TYPE, 
                                                PROFILE, 
                                                vlan)";
            $sql .= " values($idEmpresa, $idSucursal, $idClpv, $idContrato, ";


            for ($i = 0; $i < count($array); $i++) {
                if (isset($array_null[$i])) {
                    if (empty($array[$i])) {
                        $sql .= " null, ";
                    } else {
                        $sql .= "'" . $array[$i] . "',";
                    }
                } else {
                    $sql .= "'" . $array[$i] . "',";
                }
            }

            $sql = substr($sql, 0, strlen($sql) - 1);

            $sql .= ')';

            $oCon->QueryT($sql);

            //id caja
            $sql = "select max(id) as id 
                    from isp.int_contrato_caja 
                    where id_empresa = $idEmpresa and 
                    id_clpv = $idClpv and 
                    id_contrato = $idContrato";
            $id = consulta_string_func($sql, 'id', $oCon, 0);

            $this->idEquipo = $id;

            $this->registraAuditoriaContratos(3, $idUser, '');
        }
        return $id;
    }

    /***************************************************
     * @ registraCajaContratoPaquetes
     * + Ingresa planes en equipo
     **************************************************/
    function registraCajaContratoPaquetes($id, $arrayOk, $idUser)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

        $oCon = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $idempresa = $_SESSION["U_EMPRESA"];

        $fechaServer = date("Y-m-d H:i:s");

        //AGREGADO PARA CID DE GLOBAL PERU
        $sql = "SELECT codigo_servicio_sn, num_digitos, secuencial_actual FROM isp.int_parametros_general WHERE id_empresa = $idempresa";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $codigo_servicio_sn = $oCon->f('codigo_servicio_sn');
                $num_digitos = $oCon->f('num_digitos');
                $secuencial_actual = $oCon->f('secuencial_actual');
            }
        }
        $oCon->Free();

        if($codigo_servicio_sn == 'S'){
            $sql = "SELECT id_tipo_cont_serv FROM isp.contrato_clpv WHERE id = $idContrato AND id_clpv = $idClpv";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    $id_tipo_cont_serv = $oCon->f('id_tipo_cont_serv');
                }
            }
            $oCon->Free();

            //ARRAY TIPOS
            $sql = "SELECT id, codigo
                    FROM isp.int_tipo_servicio";
            $array_tipo_serv  = array_dato($oCon, $sql, 'id', 'codigo');
        }


        //consulta fecha de equipo
        $sql = "SELECT fecha, fecha_vence FROM isp.int_contrato_caja WHERE id = $id";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $fecha = $oCon->f('fecha');
                $fecha_vence = $oCon->f('fecha_vence');
            }
        }
        $oCon->Free();

        if(strlen($fecha_vence)==0){
            $fecha_vence = $fechaServer;
        }

        if (count($arrayOk) > 0) {
            foreach ($arrayOk as $val) {
                $codProd = $val[0];
                $precio = $val[1];
                $tipo = $val[2];

                if (empty($tipo)) {
                    $tipo = 'P';
                }

                $monedaExt = "N";

                if(isset($_SESSION['moneda_ext_'.$codProd])){
                    $monedaExt = $_SESSION['moneda_ext_'.$codProd];
                }

                if($codigo_servicio_sn == 'S'){
                    //id prod
                    $sql = "SELECT id, id_tipo_serv FROM isp.int_paquetes WHERE prod_cod_prod = '$codProd' AND id_empresa = $idempresa";
                    $id_prod = consulta_string_func($sql, 'id', $oCon, 0);
                    $id_tipo_serv = consulta_string_func($sql, 'id_tipo_serv', $oCon, 0);

                    $codigo_servicio = $array_tipo_serv[$id_tipo_serv];
                    $codigo_tip_contr = $array_tipo_serv[$id_tipo_cont_serv];

                    $codigo_dig = $secuencial_actual + 1;
                    $codigo_dig = str_pad($codigo_dig, $num_digitos, "0", STR_PAD_LEFT);
                    $codigo_cid_fn = $codigo_tip_contr.$codigo_servicio.$codigo_dig;

                    $sql = "INSERT INTO isp.int_contrato_caja_pack (id_clpv, id_contrato, id_caja, id_prod, cod_prod, precio, estado, fecha, fecha_vence, tipo, user_web, fecha_server, moneda_extranjera, codigo_cid)
                                                        values($idClpv, $idContrato, $id, $id_prod, '$codProd', '$precio', 'P', '$fecha', '$fecha_vence', '$tipo', $idUser, '$fechaServer', '$monedaExt', '$codigo_cid_fn')";
                    $oCon->QueryT($sql);

                    $secu_update = $secuencial_actual + 1;
                    $sql = "UPDATE isp.int_parametros_general SET secuencial_actual = $secu_update WHERE id_empresa = $idempresa";
                    $oCon->QueryT($sql);
                    
                }else{
                    //id prod
                    $sql = "SELECT id FROM isp.int_paquetes WHERE prod_cod_prod = '$codProd' AND id_empresa = $idempresa";
                    $id_prod = consulta_string_func($sql, 'id', $oCon, 0);

                    $sql = "INSERT INTO isp.int_contrato_caja_pack (id_clpv, id_contrato, id_caja, id_prod, cod_prod, precio, estado, fecha, fecha_vence, tipo, user_web, fecha_server, moneda_extranjera)
                                                        values($idClpv, $idContrato, $id, $id_prod, '$codProd', '$precio', 'P', '$fecha', '$fecha_vence', '$tipo', $idUser, '$fechaServer', '$monedaExt')";
                    $oCon->QueryT($sql);
                }
                

                unset($_SESSION['moneda_ext_'.$codProd]);
            }
        }

        $this->registraAuditoriaContratos(4, $idUser, '');

        return 'ok';
    }

    /***************************************************
     * @ registraServiciosContratos
     * + Ingresa planes de equipo en BDD IFX saeclse
     **************************************************/
    function registraServiciosContratos()
    {

        $oIfx = $this->oIfx;
        $oCon = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        //consulta paquetes 
        $sql = "SELECT c.tipo, p.cod_prod, SUM(p.precio) as precio, COUNT(p.id) as numero
                FROM isp.int_contrato_caja c, isp.int_contrato_caja_pack p
                WHERE c.id_contrato = p.id_contrato AND
                c.id_clpv = p.id_clpv AND
                p.id_caja = c.id AND
                c.id_clpv = $idClpv AND
                c.id_contrato = $idContrato AND
                p.estado IN ('P')
                group by 1,2";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                unset($arrayProd);
                do {
                    $arrayProd[] = array($oCon->f('tipo'), $oCon->f('cod_prod'), $oCon->f('precio'), $oCon->f('numero'));
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();


        if (count($arrayProd) > 0) {

            //lectura sucia
            //

            foreach ($arrayProd as $val) {
                $tipo = $val[0];
                $cod_prod = $val[1];
                $precio = $val[2];
                $numero = $val[3];

                //control servicios
                $sql = "select count(*) as control from saeclse where clse_cod_clpv = $idClpv and clse_cod_contr = $idContrato and clse_cod_prod = '$cod_prod' and clse_tip_clse = '$tipo'";
                $control = consulta_string_func($sql, 'control', $oIfx, 0);

                if ($control == 0) { // inserta
                    //datos del contrato
                    $sql = "select id_empresa, id_sucursal
                            from isp.contrato_clpv
                            where id_clpv = $idClpv and
                            id = $idContrato";
                    if ($oCon->Query($sql)) {
                        if ($oCon->NumFilas() > 0) {
                            $id_empresa = $oCon->f('id_empresa');
                            $id_sucursal = $oCon->f('id_sucursal');
                        }
                    }
                    $oCon->Free();

                    $tipoPrecio = 1;
                    $idBodega = 2;

                    //nombre prod
                    $sql = "select prod_nom_prod from saeprod where prod_cod_empr = $id_empresa and prod_cod_sucu = $id_sucursal and prod_cod_prod = '$cod_prod'";
                    $prod_nom_prod = consulta_string_func($sql, 'prod_nom_prod', $oIfx, '');

                    $sql = "insert into saeclse(clse_cod_empr, clse_cod_sucu, clse_cod_bode,
                                                clse_cod_prod, clse_nom_prod, clse_cod_nomp, clse_cco_clse,
                                                clse_pre_clse, clse_cant_add, clse_pre_add, clse_tot_add,
                                                clse_cod_clpv, clse_cod_contr, clse_tip_cobr, clse_cobr_sn,
                                                clse_prod_inst, clse_est_clse, clse_cant_clse, clse_tip_clse)
                                        values($id_empresa, $id_sucursal, $idBodega,
                                                '$cod_prod', '$prod_nom_prod', '$tipoPrecio', '',
                                                '$precio', 0, 0, 0,
                                                $idClpv, $idContrato, 1, 'S',
                                                '', 'PE', '$numero', '$tipo')";
                    $oIfx->QueryT($sql);
                } else { //modifica
                    $sql = "update saeclse set clse_pre_clse = '$precio',
                                                clse_cant_clse = '$numero'
                                                where clse_cod_clpv = $idClpv and
                                                clse_cod_contr = $idContrato and
                                                clse_cod_prod = '$cod_prod'";
                    $oIfx->QueryT($sql);
                }
            } //fin foreach

            $this->sumaTotalesServicio($oCon, $oIfx, $idContrato, $idClpv);
        } //fin if
    }

    /***************************************************
     * @ actualizaCajaContrato
     * + Actualiza numero de cajas en contratos
     **************************************************/
    function actualizaCajaContrato()
    {

        $oCon = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $sql = "select count(*) as control, tipo, id_tipo_prod
                from isp.int_contrato_caja
                where id_contrato = $idClpv and
                id_clpv = $idContrato and
                estado not in ('I')
                group by 2,3";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                unset($arrayCaja);
                do {
                    $arrayCaja[$oCon->f('id_tipo_prod')][$oCon->f('tipo')] = $oCon->f('control');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $cantidad_c = $arrayCaja[1]['P'];
        $adicional_c = $arrayCaja[1]['A'];

        $cantidad_m = $arrayCaja[2]['P'];
        $adicional_m = $arrayCaja[2]['A'];

        //CONTROL PARA POSTGRESS
        if(strlen($cantidad_c)==0){
            $cantidad_c = 0;
        }

        if(strlen($adicional_c)==0){
            $adicional_c = 0;
        }

        if(strlen($cantidad_m)==0){
            $cantidad_m = 0;
        }
        if(strlen($adicional_m)==0){
            $adicional_m = 0;
        }

        $sql = "update isp.contrato_clpv set num_caja = '$cantidad_c', num_caja_a = '$adicional_c',
                                    num_modem = '$cantidad_m', num_modem_a = '$adicional_m'
                                    where id = $idContrato and
                                    id_clpv = $idClpv";
        $oCon->QueryT($sql);

        return 'ok';
    }

    /***************************************************
     * @ suscripcionContrato
     * + Registra cantidades y precios de suscripcion
     **************************************************/
    function suscripcionContrato($id_pago, $equipo, $tipo, $cantidad, $precio, $total, $detalle)
    {

        $oCon = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;

        if (empty($id_pago)) {
            $id_pago = 'null';
        }

        //consulta paquetes 
        $sql = "INSERT INTO isp.int_suscribir (id_empresa, id_sucursal, id_clpv, id_contrato, id_pago, 
                                        equipo, tipo, cantidad, precio, total, detalle, estado)
                                        VALUES($idEmpresa, $idSucursal, $idClpv, $idContrato, $id_pago,
                                        '$equipo', '$tipo', '$cantidad', '$precio', '$total', '$detalle', 'PE')";
        $oCon->QueryT($sql);

        //consulta monto de suscripcion
        $sql = "SELECT suscripcion from isp.contrato_clpv WHERE id = $idContrato";
        $suscripcion = consulta_string_func($sql, 'suscripcion', $oCon, 0);

        $total += $suscripcion;

        //actualiza monto de suscripcion
        $sql = "update isp.contrato_clpv SET suscripcion = $total WHERE id = $idContrato";
        $oCon->QueryT($sql);

    }

    /***************************************************
     * @ reporteEquiposAsignados
     * + Consulta equipos asignados a Cliente
     * + Retorna HTML
     **************************************************/
    function reporteEquiposAsignados($idBodega)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

        $oIfx = $this->oIfx;
        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;

        $sHtml = '';

        $sql = "select empl_cod_empl, empl_ape_nomb
                from saeempl
                where empl_cod_empr = $idEmpresa";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                unset($arrayEmpleado);
                do {
                    $arrayEmpleado[$oIfx->f('empl_cod_empl')] = $oIfx->f('empl_ape_nomb');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $sql = "select usuario_id, concat(usuario_nombre, ' ', usuario_apellido) as user
				from usuario
				where empresa_id = $idEmpresa";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                unset($arrayUser);
                do {
                    $arrayUser[$oCon->f('usuario_id')] = $oCon->f('user');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        //nombre bodega
        $sql = "select bode_nom_bode from saebode where bode_cod_bode = $idBodega";
        $bode_nom_bode = consulta_string_func($sql, 'bode_nom_bode', $oIfx, '');


        $sHtml .= '<div class="alert alert-warning alert-dismissible" role="alert">
					<strong>' . $bode_nom_bode . '</strong>
				</div>';

        $sHtml .= '<table class="table table-bordered table-striped table-condensed table-hover" style="width: 99%; margin-top: 10px;" align="center">';
        $sHtml .= '<tr>
                    <td align="center" colspan="7" class="bg-primary">LISTA DE EQUIPOS</td>
                </tr>';
        //query clpv
        $sql = "select d.id_inst_amarre, d.id_inst_desp_cab, d.minv_num_comp,
				d.minv_num_secu, d.minv_fmov, d.prod_cod_caja, d.costo_caja, d.serie_caja,
				d.prod_cod_tarj, d.costo_tarj, d.serie_tarj, d.estado, c.usuario_id, c.fecha_hora,
				d.id_equipo, c.id_tecnico
                from isp.int_inst_desp_amarre d, isp.int_inst_despacho_cab c
                where
				c.empr_cod_empr = d.empr_cod_empr and
				c.minv_num_comp = d.minv_num_comp and
				c.id_inst_desp_cab = d.id_inst_desp_cab and
				c.empr_cod_empr = $idEmpresa and
                c.sucu_cod_sucu = $idSucursal";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $sHtml .= '<tr>';
                $sHtml .= '<td>ID Equipo</td>';
                $sHtml .= '<td>Tarjeta</td>';
                $sHtml .= '<td>Caja</td>';
                $sHtml .= '<td>Entrega</td>';
                $sHtml .= '<td>Asigna</td>';
                $sHtml .= '<td>Fecha</td>';
                $sHtml .= '<td></td>';
                $sHtml .= '</tr>';
                do {
                    $id_inst_amarre = $oCon->f('id_inst_amarre');
                    $id_inst_desp_cab = $oCon->f('id_inst_desp_cab');
                    $minv_num_comp = $oCon->f('minv_num_comp');
                    $minv_fmov = $oCon->f('minv_fmov');
                    $prod_cod_caja = $oCon->f('prod_cod_caja');
                    $costo_caja = $oCon->f('costo_caja');
                    $serie_caja = $oCon->f('serie_caja');
                    $prod_cod_tarj = $oCon->f('prod_cod_tarj');
                    $costo_tarj = $oCon->f('costo_tarj');
                    $serie_tarj = $oCon->f('serie_tarj');
                    $estado = $oCon->f('estado');
                    $usuario_id = $oCon->f('usuario_id');
                    $fecha_hora = $oCon->f('fecha_hora');
                    $id_equipo = $oCon->f('id_equipo');
                    $id_tecnico = $oCon->f('id_tecnico');

                    $sHtml .= '<tr>';
                    $sHtml .= '<td align="left">' . $id_equipo . '</td>';
                    $sHtml .= '<td align="left">' . $serie_tarj . '</td>';
                    $sHtml .= '<td align="left">' . $serie_caja . '</td>';
                    $sHtml .= '<td align="left">' . $arrayUser[$usuario_id] . '</td>';
                    $sHtml .= '<td align="left">' . $arrayEmpleado[$id_tecnico] . '</td>';
                    $sHtml .= '<td align="left">' . fecha_mysql_dmy($minv_fmov) . '</td>';
                    $sHtml .= '<td align="left">
									<div class="btn btn-success btn-sm" onclick="seleccionaEquipo(' . $id_inst_amarre . ');">
										<span class="glyphicon glyphicon-ok"></span>
									</div>
								</td>';
                    $sHtml .= '</tr>';
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sHtml .= '</table>';

        return $sHtml;
    }

    /***************************************************
     * @ registraEquipo
     * + Actualiza equipo e inserta tabla de cambios
     **************************************************/
    function registraEquipo(
        $idSistemaEquipo,
        $idMarcaEquipo,
        $idNAP,
        $idMedida,
        $idTarjetaEquipo,
        $idStb,
        $idUbicacionEquipo,
        $ubicacionEquipo,
        $fechaEquipo,
        $latitudId,
        $longitudId,
        $id_inst_desp_cab,
        $id_inst_desp_det,
        $inst_clpv_id,
        $cambio_eq,
        $idTarjetaEquipo_c,
        $idStb_c,
        $id_contr_grupo,
        $id_user_voip,
        $ip,
        $id_dispositivo_2,
        $nap,
        $senal,
        $protocolo,
        $pppoe_sn,
        $pppoe_user,
        $pppoe_clave,
        $pppoe_perfil,
        $tecnologia,
        $tipo_onu = 0,  
        $puerto_pon = 0,
        $secuencial_onu = 0,
        $vlan = 0,
        $vel_subida = 0,
        $vel_bajada = 0,
        $comentario = 0,
        $tipo_ip = 0,
        $tipo_modo = 0,
        $mascara = 0,
        $gateway = 0,
        $lista_mk = 0,
        $incluye_catv = 0,
        $vineta = 0,
        $ppoe_user = 0,
        $ppoe_pass = 0,
        $ppoe_vlan = 0,

        //29-12-2021
        $id_olt_sm = 0,
        $board = 0,
        $tipoPon = 0,
        $onuMode = 0,
        $zona_onu = 0,
        $vel_subida_s = 0,
        $vel_bajada_s = 0,
        $odb_onu = 0,
        $nombre_onu = 0,
        $direccion_comentario_onu = 0,
        $estado = ''
        )
        {
        session_start();

        $oCon = $this->oCon;
        $idContrato = $this->idContrato;
        $idEquipo = $this->idEquipo;
        $idempresa = $this->idEmpresa;
        $idsucursal = $this->idSucursal;
        $id_clpv = $this->idClpv;

        //variables de session
        $idUser = $_SESSION['U_ID'];

        $hora = date("H:i:s");

        $fechaCompleta = $fechaEquipo . ' ' . $hora;

        $ok = false;

        $sql = "SELECT fecha_c_vence FROM isp.contrato_clpv WHERE id = $idContrato";
        $fecha_c_vence = consulta_string_func($sql, 'fecha_c_vence', $oCon, '');

        if (empty($id_user_voip)) {
            $id_user_voip = 'null';
        }

        if(empty($idMarcaEquipo)){
            $idMarcaEquipo = 1;
        }

        if(strlen($longitudId)==0){
            $longitudId = 0;
        }
        
        if(strlen($latitudId)==0){
            $latitudId = 0;
        }
        $sql_e = "UPDATE isp.int_contrato_caja SET 
                            id_ubicacion    = '$idUbicacionEquipo',
							id_dispositivo 	= '$idSistemaEquipo',
							id_marca 		= '$idMarcaEquipo',
							id_equipo 		= '0',
							id_tarjeta 		= '$idTarjetaEquipo',
							id_caja 		= '$idStb',
							ubicacion 		= '$ubicacionEquipo', 
							medida 			= '$idMedida',
							fecha 			= '$fechaCompleta',
							fecha_vence 	= '$fecha_c_vence',
							latitud 		= '$latitudId',
							longitud 		= '$longitudId'  ,
                            id_user_voip    = $id_user_voip,";
        if (!empty($idNAP)) {
            $sql_e .= "id_nap = '$idNAP', ";
        }

        if (!empty($estado)) {
            $sql_e .= " estado = '$estado', ";
        }

        if (!empty($tipo_onu)) {
            $sql_e .= "type = '$tipo_onu', ";
        }

        if (!empty($puerto_pon)) {
            $sql_e .= "puerto_pon = '$puerto_pon', ";
        }

        if (!empty($secuencial_onu)) {
            $sql_e .= "puerto_secuencial = '$secuencial_onu', ";
        }

        if (!empty($vlan)) {
            $sql_e .= "vlan = '$vlan', ";
        }

        if (!empty($vineta)) {
            $sql_e .= "vineta = '$vineta', ";
        }

        if (!empty($vel_subida)) {
            $sql_e .= "vel_subida  = '$vel_subida', ";
        }else{
            $sql_e .= "vel_subida  = '$lista_mk', ";
        }

        if (!empty($vel_bajada)) {
            $sql_e .= "vel_bajada = '$vel_bajada', ";
        }else{
            $sql_e .= "vel_bajada = '$lista_mk', ";
        }

        if (!empty($comentario)) {
            $sql_e .= "comentario = '$comentario', ";
        }

        if(strlen($tipo_ip)==0){
            $tipo_ip = 0;
        }
        //19/07/2022 --- ADICION PARA GUARDAR EL TIPO DE LA IP
        $sql_e .= "tipo_ip = '$tipo_ip', ";
        
        //19/07/2022 --- ADICION PARA GUARDAR EL MODO DE LA ONU
        if (!empty($tipo_modo)) {
            $sql_e .= "modo = '$tipo_modo', ";
        }
        
        $sql_e .= "ip = '$ip', ";

        $sql_e .= "mascara = '$mascara', ";

        $sql_e .= "gatway = '$gateway', ";

        $sql_e .= "usa_catv = '$incluye_catv', ";

        if (!empty($ppoe_user)) {
            $sql_e .= "ppoe_user = '$ppoe_user', ";
        }

        if (!empty($ppoe_pass)) {
            $sql_e .= "ppoe_password = '$ppoe_pass', ";
        }

        if (!empty($ppoe_vlan)) {
            $sql_e .= "ppoe_perfil_vlan = '$ppoe_vlan', ";
        }

        //29/12/2021 ---------- SEMTEC
        if (!empty($id_olt_sm)) {
            $sql_e .= "id_olt_sm = '$id_olt_sm', ";
        }

        if (!empty($board)) {
            $sql_e .= "board_sm = '$board', ";
        }

        if (!empty($tipoPon)) {
            $sql_e .= "tipo_pon_sm = '$tipoPon', ";
        }

        if (!empty($onuMode)) {
            $sql_e .= "onu_mode_sm = '$onuMode', ";
        }

        if (!empty($zona_onu)) {
            $zona_onu = explode(" /-/ ",$zona_onu);
            $zona_onu = $zona_onu[1];
            $sql_e .= "zona_onu_sm = '$zona_onu', ";
        }

        if (!empty($vel_subida_s)) {
            $sql_e .= "vel_subida = '$vel_subida_s', ";
        }

        if (!empty($vel_bajada_s)) {
            $sql_e .= "vel_bajada = '$vel_bajada_s', ";
        }

        if (!empty($odb_onu)) {
            $sql_e .= "odb_onu_sm = '$odb_onu', ";
        }

        if (!empty($nombre_onu)) {
            $sql_e .= "nombre_onu_sm = '$nombre_onu', ";
        }

        if (!empty($direccion_comentario_onu)) {
            $sql_e .= "comentario = '$direccion_comentario_onu', ";
        }

        $sql_e .= "			id_insta_clpv   = $inst_clpv_id ,
							cambio_sn       = '$cambio_eq'  
							WHERE id 		= $idEquipo AND 
							id_contrato 	= $idContrato";

                            //ECHO $sql_e;exit;
        //$sql_c = "UPDATE isp.contrato_clpv SET latitud = '$latitudId', longitud = '$longitudId' WHERE id = $idContrato";

        if ($oCon->QueryT($sql_e)) {
            $ok = true;
        }

        
        //  CAMBIOS DE Equipos
        if ($cambio_eq == 'S') {
            $sql = "SELECT c.id_contr_cajac FROM isp.int_contrato_caja_cambio c WHERE
						c.id_empresa 	= $idempresa AND
						c.id_sucursal 	= $idsucursal AND
						c.id_clpv  		= $id_clpv AND
						c.id_contrato 	= $idContrato AND
						c.id_insta_clpv = $inst_clpv_id AND
						c.id_contr_caja = $idEquipo ";
            $id_cambio = consulta_string_func($sql, 'id_contr_cajac', $oCon, '0');
            if ($id_cambio == 0) {
                // INSERTAR
                $sql = "insert into isp.int_contrato_caja_cambio ( id_empresa,		id_sucursal,		id_clpv,		id_contrato,		id_insta_clpv,
															   id_contr_caja,   id_equipo,			id_tarjeta,		id_caja,			id_tarjeta_cambio,
															   id_caja_cambio,  longitud,			latitud,		user_web,			fecha_server    )
													   values( $idempresa,		$idsucursal,		$id_clpv,		$idContrato,		$inst_clpv_id,
															   $idEquipo,       '' ,				'$idTarjetaEquipo', '$idStb',       '$idTarjetaEquipo_c' ,
															   '$idStb_c',      '$longitudId' ,     '$latitudId',   $idUser,            now()
													   )		";
            } else {
                // ACTUALIZAR
                $sql = "UPDATE isp.int_contrato_caja_cambio set  
										id_tarjeta			= '$idTarjetaEquipo' ,		
										id_caja				= '$idStb' ,
										id_tarjeta_cambio	= '$idTarjetaEquipo_c' ,
										id_caja_cambio		= '$idStb_c' ,  
										longitud			= '$longitudId' ,	
										latitud				= '$latitudId',
										user_modi			= $idUser ,
										fecha_modi			= now()  	
                        WHERE
										id_empresa 			= $idempresa AND
										id_sucursal 		= $idsucursal AND
										id_clpv  			= $id_clpv AND
										id_contrato 		= $idContrato AND
										id_insta_clpv 		= $inst_clpv_id AND
										id_contr_caja 		= $idEquipo and
										id_contr_cajac		= $id_cambio ";
            }

            $oCon->QueryT($sql);
        }

        $this->registraAuditoriaContratos(8, $idUser, 'EQUIPO: ' . $idTarjetaEquipo);

        return $ok;
    }

    /***************************************************
     * @ registraCuotaPaqueteCaja
     * + Consulta planes y registra cuotas
     **************************************************/
    function registraCuotaPaqueteCaja($duracion, $op_dias = '', $op_renovacion = 'N', $fecha = '')
    {

        //variables de session
        $idUser = $_SESSION['U_ID'];

        //variables
        $oCon = $this->oCon;
        $idContrato = $this->idContrato;
        $idempresa = $this->idEmpresa;
        $idsucursal = $this->idSucursal;
        $mesI = date("m");
        $anioI = date("Y");

        //genera log de la transaccion
        $this->pathLog = path(DIR_INCLUDE) . 'Logs/Cable_Operador/TablaPagos/' . date("dmY") . '.txt';

        //consulta planes de los equipo<s
        $array = $this->consultaPlan();

        $this->crearLog("*** Creacion Tabla Pagos ***");
        $this->crearLog("Count Array: " . count($array));

        if (count($array) > 0) {

            //verifica fecha
            if ($op_renovacion == 'S') {

                $this->crearLog("Ingresa en proceso de renovacion = $op_renovacion");

                //query fecha de ultima cuota
                $sql = "SELECT MAX(fecha) AS fecha 
				        FROM isp.contrato_pago
                        WHERE id_contrato = $idContrato";
                $fecha_ok = consulta_string_func($sql, 'fecha', $oCon, date("Y/m/d"));

                $this->crearLog("Maxima Fecha de cuota = $fecha_ok");

                //query estado del contrato
                $sql = "SELECT estado, fecha_instalacion, id_clpv from isp.contrato_clpv WHERE id = $idContrato";
                $estado = consulta_string_func($sql, 'estado', $oCon, '');
                $idClpv = consulta_string_func($sql, 'id_clpv', $oCon, '');

                $this->crearLog("Estado de contrato = $estado");

                if ($estado == 'PI') {

                    $fecha_instalacion = consulta_string_func($sql, 'fecha_instalacion', $oCon, '');

                    if (empty($fecha_instalacion) || $fecha_instalacion == '0000-00-00') {
                        $fecha_ok = date("Y/m/d");
                    } else {
                        $fecha_ok = $fecha_instalacion;
                    }

                    $this->crearLog("Fecha instalacion = $fecha_instalacion");
                    $this->crearLog("Fecha instalacion ok = $fecha_ok");

                    $op_dias = 'S'; //calcula dias de consumo
                    $this->crearLog("Cambia parametro de dias_consumidos estado contrato PI = $op_dias");

                    //actualiza estado del contrato
                    $sql = "update isp.contrato_clpv SET estado = 'AP' WHERE id = $idContrato";
                    $oCon->QueryT($sql);

                    $this->crearLog("Actualiza estado de contrato = $sql");
                }
            } else {
                if (!empty($fecha)) {
                    $fecha_ok = $fecha;
                    $anioI = substr($fecha_ok, 0, 4);
                    $mesI = substr($fecha_ok, 5, 2);
                } else {
                    $fecha_ok = date("Y/m/d");
                }
            }

            $this->crearLog("Referencia duracion = $duracion, dias_consumidos = $op_dias");

            $mesI = substr($fecha_ok, 5, 2);
            $anioI = substr($fecha_ok, 0, 4);

            $this->crearLog("Mes = $mesI");
            $this->crearLog("Año = $anioI");

            //consulta op tabla de pagos por estado equipo
            $sql = "SELECT id, op_pago FROM isp.int_estados_equipo";
            $array_e_e = array_dato($oCon, $sql, 'id', 'op_pago');

            //parametros
            $sql = "SELECT dias_consumidos, usa_promo_sn
                    from isp.int_parametros 
                    where id_empresa = $idempresa and 
                    id_sucursal = $idsucursal";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    $dias_consumidos = $oCon->f('dias_consumidos');
                    $usa_promo_sn = $oCon->f('usa_promo_sn');
                }
            }
            $oCon->Free();

            if (!empty($op_dias)) {
                $dias_consumidos = $op_dias;
            }

            if (empty($duracion)) {
                //duracion contrato
                $sql = "SELECT duracion from isp.contrato_clpv WHERE id = $idContrato";
                $duracion = consulta_string_func($sql, 'duracion', $oCon, 12);
            }

            $info_actual_promo = "";
            if($usa_promo_sn == 'S'){
                $sql = "SELECT info_actual_promo
                        from isp.contrato_clpv 
                        where id = $idContrato";
                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        do {
                            $info_actual_promo      = $oCon->f('info_actual_promo', false);
                            $info_actual_promo      = json_decode($info_actual_promo);                     
                        } while ($oCon->SiguienteRegistro());
                    }
                }
                $oCon->Free();

            }

            //query tarifa contrato
            $sql = "SELECT tarifa from isp.contrato_clpv WHERE id = $idContrato";
            $tarifa = consulta_string_func($sql, 'tarifa', $oCon, 0);

            //consulta descuentos
            $sql = "SELECT descuento_v FROM isp.contrato_descuentos WHERE id_contrato = $idContrato AND estado = 'A'";
            $descuento_v = round(consulta_string_func($sql, 'descuento_v', $oCon, 0), 2);

            if ($descuento_v > 0) {
                $descuento_v = $descuento_v * -1;
            } elseif ($descuento_v < 0) {
                $tarifa += abs($descuento_v);
                $descuento_v = 0;
            }

            $this->crearLog("Variables duracion = $duracion, dias_consumidos = $op_dias");

            if (empty($duracion)) {
                $duracion = 12;

                $this->crearLog("duracion no definida, se actualiza duracion a $duracion meses");

                $sql = "update isp.contrato_clpv set duracion = $duracion WHERE id = $idContrato";

                $oCon->QueryT($sql);
            }

            //quer maximo secuencial tabla de pagos
            $sql = "SELECT max(secuencial) as maximo FROM isp.contrato_pago WHERE id_contrato = $idContrato";
            $j = consulta_string_func($sql, 'maximo', $oCon, 1);

            $this->crearLog("Max Secuencial Cuotas = $j : $sql");

            if ($duracion > 20) {
                $duracion = 20;
            }

            unset($arrayFecha);

            for ($i = 1; $i <= $duracion; $i++) {

                /**
                 * Consultar Cuota Descuento
                 */
                $d_cuotas = 0;
                $d_descuento = 0;
                $d_descuento_p = 0;

                if($op_renovacion != 'S'){
                    $sql = "SELECT cuotas, descuento, descuento_p, incluye_pc, dias_consumo
                            FROM isp.int_contrato_descuento
                            WHERE id_contrato = $idContrato 
                            AND cuotas = $i
                            AND estado = 'A' AND datos_cuotas is null";
                    if ($oCon->Query($sql)) {
                        if ($oCon->NumFilas() > 0) {
                            $d_cuotas = $oCon->f('cuotas');
                            $d_descuento = $oCon->f('descuento');
                            $d_descuento_p = $oCon->f('descuento_p');
                            $d_incluye_pc = $oCon->f('incluye_pc');
                            $dias_consumidos_descu = $oCon->f('dias_consumo');
                        }
                    }
                    $oCon->Free();

                    $sql = "SELECT datos_cuotas, dias_consumo
                            FROM isp.int_contrato_descuento
                            WHERE id_contrato = $idContrato  AND datos_cuotas is not null";
                    if ($oCon->Query($sql)) {
                        if ($oCon->NumFilas() > 0) {
                            $datos_cuotas = $oCon->f('datos_cuotas', false);
                            $datos_cuotas = json_decode($datos_cuotas);
                            $dias_consumidos_descu = $oCon->f('dias_consumo');
                        }
                    }
                    $oCon->Free();
                }

                if (!empty($dias_consumidos_descu)) {
                    $dias_consumidos = $dias_consumidos_descu;
                }

                $diasDiferencia = 0;
                $valor_dia = 0;
                $valor_uso = 0;
                $valor_no_uso = 0;

                $txt_log = "";
                if ($i == 1) {

                    $diaMes = $this->ultimoDiaMes($fecha_ok);
                    $nuevaFecha = $anioI . '/' . $mesI . '/' . $diaMes;
                    if ($dias_consumidos == 'S') {
                        $diaUso = diferenciaDias($fecha_ok, $nuevaFecha);
                        //$diaUso++;
                        $ultimoDiaMes = $this->ultimoDiaMes($nuevaFecha);
                        $diasDiferencia = $ultimoDiaMes - $diaUso;
                        $valor_dia = round($tarifa / $ultimoDiaMes, 6);
                        $valor_uso = round($valor_dia * $diaUso, 6);
                        $valor_no_uso = round($valor_dia * $diasDiferencia, 6);
                    } else {
                        $ultimoDiaMes = $this->ultimoDiaMes($nuevaFecha);
                        $diaUso = $this->ultimoDiaMes($nuevaFecha);
                    }

                    $arrayFecha[$i] = $nuevaFecha;

                    if($info_actual_promo != '' && count($info_actual_promo) > 0 ){
                        $dia_proporcional = $info_actual_promo->dia_proporcional;
                        $dia_actual = date("d");
                        if($dia_actual >= $dia_proporcional && $dia_proporcional > 0){
                            $valor_uso = 0;
                            $valor_no_uso = round($tarifa, 6);
                        }
                    }
                } else {
                    $anio = substr($arrayFecha[$i - 1], 0, 4);
                    $mes = substr($arrayFecha[$i - 1], 5, 2) + 1;
                    $nuevaFecha = data_last_month_day($mes, $anio);
                    $arrayFecha[$i] = $nuevaFecha;
                    $ultimoDiaMes = $this->ultimoDiaMes($nuevaFecha);
                    $diaUso = $this->ultimoDiaMes($nuevaFecha);
                }

                $txt_log .= PHP_EOL . "tarifa = $tarifa";
                $txt_log .= PHP_EOL . "diaMes = $diaMes";
                $txt_log .= PHP_EOL . "nuevaFecha = $nuevaFecha";
                $txt_log .= PHP_EOL . "fecha_ok = $fecha_ok";
                $txt_log .= PHP_EOL . "diaUso = $diaUso";
                $txt_log .= PHP_EOL . "ultimoDiaMes = $ultimoDiaMes";
                $txt_log .= PHP_EOL . "diasDiferencia = $diasDiferencia";
                $txt_log .= PHP_EOL . "valor_dia = $valor_dia";
                $txt_log .= PHP_EOL . "valor_uso = $valor_uso";
                $txt_log .= PHP_EOL . "valor_no_uso = $valor_no_uso";
                $txt_log .= PHP_EOL . "anio = $anio";
                $txt_log .= PHP_EOL . "mes = $mes";

                $this->crearLog("Parametros calculo : $txt_log");

                $mesOk = substr($nuevaFecha, 5, 2);
                $anioOk = substr($nuevaFecha, 0, 4);

                $this->crearLog("nuevaFecha = $nuevaFecha");
                $this->crearLog("mesOk = $mesOk");
                $this->crearLog("anioOk = $anioOk");

                $sql = "SELECT count(*) as control FROM isp.contrato_pago WHERE id_contrato = $idContrato AND EXTRACT(MONTH FROM fecha) = '$mesOk' AND anio = '$anioOk' AND tipo = 'P'";
                $control = consulta_string_func($sql, 'control', $oCon, 0);

                $this->crearLog("count contrato_pago = $control : $sql");

                if ($control == 0) {

                    $idCuota = $this->registraTablaPago($nuevaFecha, $j, 'PE', $mesOk, $anioOk, $tarifa, $valor_dia, $valor_uso, $valor_no_uso, $ultimoDiaMes, $diaUso, $diasDiferencia, 'P', '', $descuento_v);

                    $this->crearLog("idCuota = $idCuota");

                    if($dias_consumidos_descu == "S" && $i == 1 && $d_descuento_p > 0){
                        $d_tarifa = $tarifa - $valor_no_uso;
                        $dd_descuento = round(($d_tarifa * $d_descuento_p) / 100, 0);
                        $dd_descuento = $dd_descuento * -1;
                    }else{
                        $dd_descuento = $d_descuento * -1;
                    }
                    
                    $sql = "UPDATE isp.contrato_pago SET descuento = $dd_descuento WHERE id = $idCuota";
                    $oCon->QueryT($sql);

                    $txt_log = "";
                    $valor_dia_p = 0;
                    $valor_uso_p = 0;
                    $valor_no_uso_p = 0;
                    $descuento_fin = 0;
                    //ingreso de cuotas
                    foreach ($array as $val) {
                        $id                         = $val[0];
                        $idEquipo                   = $val[3];
                        $id_prod                    = $val[4];
                        $cod_prod                   = $val[5];
                        $precio                     = $val[6];
                        $estado                     = $val[13];
                        $dd_descuento_individual    = 0;

                        if ($i == 1 && $dias_consumidos == 'S') {
                            $valor_dia_p    = round($precio / $ultimoDiaMes, 6);
                            $valor_uso_p    = round($valor_dia_p * $diaUso, 6);
                            $valor_no_uso_p = round($valor_dia_p * $diasDiferencia, 6);
                        }

                        if($info_actual_promo != '' && count($info_actual_promo) > 0 && $i == 1){
                            $dia_proporcional = $info_actual_promo->dia_proporcional;
                            $dia_actual = date("d");
                            if($dia_actual >= $dia_proporcional && $dia_proporcional > 0){
                                $valor_uso_p = 0;
                                $valor_no_uso_p = round($tarifa, 6);
                            }
                        }

                        $recalcular_sn = 'N';
                        if($estado != 'A'){
                            if($estado != 'P'){
                                $valor_uso_p    = 0;
                                $valor_no_uso_p = $precio;
                            }
                        }

                        if ($d_descuento_p > 0) {
                            $d_precio_individual = $precio - $valor_no_uso_p;
                            $dd_descuento_individual = round(($d_precio_individual * $d_descuento_p) / 100, 0);
                            $dd_descuento_individual = $dd_descuento_individual * -1;
                        }
                        
                        if(count($datos_cuotas) > 0){
                            foreach($datos_cuotas as $dato_cuota){
                                if($dato_cuota->Cuota == $i){
                                    $planes = $dato_cuota->Planes;

                                    if(count($planes) > 0){
                                        foreach($planes as $dato_plan){
                                            if($id_prod == $dato_plan->IdPlan){
                                                $dd_descuento_individual = $precio - $dato_plan->PrecioFin;
                                                $descuento_fin          += $dd_descuento_individual;
                                                $dd_descuento_individual = $dd_descuento_individual * -1;
        
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        } 

                        $txt_log .= "id = $id";
                        $txt_log .= "idEquipo = $idEquipo";
                        $txt_log .= "id_prod = $id_prod";
                        $txt_log .= "cod_prod = $cod_prod";
                        $txt_log .= "precio = $precio";
                        $txt_log .= "ultimoDiaMes = $ultimoDiaMes";
                        $txt_log .= "diaUso = $diaUso";
                        $txt_log .= "diasDiferencia = $diasDiferencia";
                        $txt_log .= "i = $i";
                        $txt_log .= "valor_dia_p = $valor_dia_p";
                        $txt_log .= "valor_uso_p = $valor_uso_p";
                        $txt_log .= "valor_no_uso_p = $valor_no_uso_p";

                        $this->crearLog("Calculo Equipo = $txt_log");
                        $this->crearLog("idCuota = $idCuota");

                        $this->crearLog("Op Pago, Crea contrato_pago_pack = " . $array_e_e[$val[13]]);

                        if ($array_e_e[$val[13]] == 'S') {
                            $this->registraTablaPagoPack($idEquipo, $id, $idCuota, $id_prod, $cod_prod, $nuevaFecha, $precio, $valor_dia_p, $valor_uso_p, $valor_no_uso_p, $ultimoDiaMes, $diaUso, 'A','',$recalcular_sn,'M',$dd_descuento_individual);
                        }

                        $oCon->QueryT($sql);
                    } //fin foreach

                    if(count($datos_cuotas) > 0){
                        foreach($datos_cuotas as $dato_cuota){

                            if($dato_cuota->Cuota == $i){
                                $planes = $dato_cuota->Planes;

                                if(count($planes) > 0){
                                    foreach($planes as $dato_plan){
                                        $nom_plan = $dato_plan->Plan;
                                        $IdPlan = $dato_plan->IdPlan;
                                        $CodPlan = $dato_plan->CodPlan;
                                        $Precio = $dato_plan->Precio;

                                        if ($i == 1 && $dias_consumidos == 'S') {
                                            $valor_dia_p    = round($Precio / $ultimoDiaMes, 6);
                                            $valor_uso_p    = round($valor_dia_p * $diaUso, 6);
                                            $valor_no_uso_p = round($valor_dia_p * $diasDiferencia, 6);
                                        }

                                        if (strpos($nom_plan, "SERVICIO EXTRA") !== false) {
                                            $dd_descuento_individual = $Precio - $dato_plan->PrecioFin;
                                            $descuento_fin          += $dd_descuento_individual;
                                            $dd_descuento_individual = $dd_descuento_individual * -1;

                                            $this->registraTablaPagoPack('', '', $idCuota, $IdPlan, $CodPlan, $nuevaFecha, $Precio, $valor_dia_p, $valor_uso_p, $valor_no_uso_p, $ultimoDiaMes, $diaUso, 'A','',$recalcular_sn,'M',$dd_descuento_individual);

                                            $sql = "UPDATE isp.contrato_pago 
                                                    SET tarifa = tarifa + $Precio, valor_uso = valor_uso + $valor_uso_p, valor_no_uso = valor_no_uso + $valor_no_uso_p 
                                                    WHERE id = $idCuota";
                                            $oCon->QueryT($sql);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $descuento_fin = $descuento_fin * -1;

                    if($descuento_fin != 0){
                        $sql = "UPDATE isp.contrato_pago SET descuento = $descuento_fin WHERE id = $idCuota";
                        $oCon->QueryT($sql);
                    }

                    $j++;
                } //fin if control
            } //fin for duracion

            $sql = "SELECT id, fecha, tarifa, planes, descuento_p, descuento_v, fecha_vence, indefinido, cortesia, observaciones, max_cajas, max_modem, estado 
                    FROM isp.contrato_descuentos WHERE id_contrato = $idContrato AND estado = 'A'";
            $idDescuento = consulta_string_func($sql, 'id', $oCon, 0);
            $fecha = consulta_string_func($sql, 'fecha', $oCon, 0);
            $tarifa = consulta_string_func($sql, 'tarifa', $oCon, 0);
            $precio = consulta_string_func($sql, 'planes', $oCon, 0);
            $descuento_p = consulta_string_func($sql, 'descuento_p', $oCon, 0);
            $descuento_v = consulta_string_func($sql, 'descuento_v', $oCon, 0);
            $fecha_vence = consulta_string_func($sql, 'fecha_vence', $oCon, 0);
            $indefinido = consulta_string_func($sql, 'indefinido', $oCon, 0);
            $cortesia = consulta_string_func($sql, 'cortesia', $oCon, 0);
            $observaciones = consulta_string_func($sql, 'observaciones', $oCon, 0);
            $max_cajas = consulta_string_func($sql, 'max_cajas', $oCon, 0);
            $max_modem = consulta_string_func($sql, 'max_modem', $oCon, 0);
            $estado_d = consulta_string_func($sql, 'estado', $oCon, 0);

            if($idDescuento > 0){
                //APLICA DESCUENTO AL RENOVAR CUOTAS
                $Contratos = new Contratos($oCon, 0, $idempresa, null, $idClpv, $idContrato);
                $Contratos->modificaDescuento($idDescuento, $fecha, $tarifa, $precio, $descuento_p, $descuento_v, $fecha_vence, $indefinido, $cortesia, $observaciones, $max_cajas, $max_modem, $estado_d);
            }
                    
            $this->registraAuditoriaContratos(11, $idUser, '');

            return 'ok';
        } //fin if control array
    }

    function registraCuotaPaqueteCajaImportacion($duracion, $op_dias = '', $op_renovacion = 'N', $fecha = '', $estado_contrato)
    {

        if($estado_contrato == 'CO' || $estado_contrato == 'CA'){
            $estado_cuota = 'CO';
        }else{
            $estado_cuota = 'PE';
        }
        //variables de session
        $idUser = $_SESSION['U_ID'];

        //variables
        $oCon = $this->oCon;
        $idContrato = $this->idContrato;
        $idempresa = $this->idEmpresa;
        $idsucursal = $this->idSucursal;
        $mesI = date("m");
        $anioI = date("Y");

        //genera log de la transaccion
        $this->pathLog = path(DIR_INCLUDE) . 'Logs/Cable_Operador/TablaPagos/' . date("dmY") . '.txt';

        //consulta planes de los equipo<s
        $array = $this->consultaPlan();

        $this->crearLog("*** Creacion Tabla Pagos ***");
        $this->crearLog("Count Array: " . count($array));

        if (count($array) > 0) {

            //verifica fecha
            if ($op_renovacion == 'S') {

                $this->crearLog("Ingresa en proceso de renovacion = $op_renovacion");

                //query fecha de ultima cuota
                $sql = "SELECT MAX(fecha) AS fecha 
				        FROM isp.contrato_pago
                        WHERE id_contrato = $idContrato";
                $fecha_ok = consulta_string_func($sql, 'fecha', $oCon, date("Y/m/d"));

                $this->crearLog("Maxima Fecha de cuota = $fecha_ok");

                //query estado del contrato
                $sql = "SELECT estado, fecha_instalacion from isp.contrato_clpv WHERE id = $idContrato";
                $estado = consulta_string_func($sql, 'estado', $oCon, '');

                $this->crearLog("Estado de contrato = $estado");

                if ($estado == 'PI') {

                    $fecha_instalacion = consulta_string_func($sql, 'fecha_instalacion', $oCon, '');

                    if (empty($fecha_instalacion) || $fecha_instalacion == '0000-00-00') {
                        $fecha_ok = date("Y/m/d");
                    } else {
                        $fecha_ok = $fecha_instalacion;
                    }

                    $this->crearLog("Fecha instalacion = $fecha_instalacion");
                    $this->crearLog("Fecha instalacion ok = $fecha_ok");

                    $op_dias = 'S'; //calcula dias de consumo
                    $this->crearLog("Cambia parametro de dias_consumidos estado contrato PI = $op_dias");

                    //actualiza estado del contrato
                    $sql = "update isp.contrato_clpv SET estado = 'AP' WHERE id = $idContrato";
                    $oCon->QueryT($sql);

                    $this->crearLog("Actualiza estado de contrato = $sql");
                }
            } else {
                if (!empty($fecha)) {
                    $fecha_ok = $fecha;
                    $anioI = substr($fecha_ok, 0, 4);
                    $mesI = substr($fecha_ok, 5, 2);
                } else {
                    $fecha_ok = date("Y/m/d");
                }
            }

            $this->crearLog("Referencia duracion = $duracion, dias_consumidos = $op_dias");

            $mesI = substr($fecha_ok, 5, 2);
            $anioI = substr($fecha_ok, 0, 4);

            $this->crearLog("Mes = $mesI");
            $this->crearLog("Año = $anioI");

            //consulta op tabla de pagos por estado equipo
            $sql = "SELECT id, op_pago FROM isp.int_estados_equipo";
            $array_e_e = array_dato($oCon, $sql, 'id', 'op_pago');

            //parametros
            $sql = "select dias_consumidos
                    from isp.int_parametros 
                    where id_empresa = $idempresa and 
                    id_sucursal = $idsucursal";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    $dias_consumidos = $oCon->f('dias_consumidos');
                }
            }
            $oCon->Free();

            if (!empty($op_dias)) {
                $dias_consumidos = $op_dias;
            }

            if (empty($duracion)) {
                //duracion contrato
                $sql = "SELECT duracion from isp.contrato_clpv WHERE id = $idContrato";
                $duracion = consulta_string_func($sql, 'duracion', $oCon, 12);
            }

            //query tarifa contrato
            $sql = "SELECT tarifa from isp.contrato_clpv WHERE id = $idContrato";
            $tarifa = consulta_string_func($sql, 'tarifa', $oCon, 0);

            //consulta descuentos
            $sql = "SELECT descuento_v FROM isp.contrato_descuentos WHERE id_contrato = $idContrato AND estado = 'A'";
            $descuento_v = round(consulta_string_func($sql, 'descuento_v', $oCon, 0), 2);

            if ($descuento_v > 0) {
                $descuento_v = $descuento_v * -1;
            } elseif ($descuento_v < 0) {
                $tarifa += abs($descuento_v);
                $descuento_v = 0;
            }

            $this->crearLog("Variables duracion = $duracion, dias_consumidos = $op_dias");

            if (empty($duracion)) {
                $duracion = 12;

                $this->crearLog("duracion no definida, se actualiza duracion a $duracion meses");

                $sql = "update isp.contrato_clpv set duracion = $duracion WHERE id = $idContrato";

                $oCon->QueryT($sql);
            }

            //quer maximo secuencial tabla de pagos
            $sql = "SELECT max(secuencial) as maximo FROM isp.contrato_pago WHERE id_contrato = $idContrato";
            $j = consulta_string_func($sql, 'maximo', $oCon, 1);

            $this->crearLog("Max Secuencial Cuotas = $j : $sql");

            if ($duracion > 20) {
                $duracion = 20;
            }

            unset($arrayFecha);

            for ($i = 1; $i <= $duracion; $i++) {

                /**
                 * Consultar Cuota Descuento
                 */
                $d_cuotas = 0;
                $d_descuento = 0;
                $d_descuento_p = 0;

                $sql = "SELECT cuotas, descuento, descuento_p, incluye_pc,dias_consumo
					FROM isp.int_contrato_descuento
					WHERE id_contrato = $idContrato 
					  AND cuotas = $i
					  AND estado = 'A';";

                //echo $sql;exit;

                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        $d_cuotas = $oCon->f('cuotas');
                        $d_descuento = $oCon->f('descuento');
                        $d_descuento_p = $oCon->f('descuento_p');
                        $d_incluye_pc = $oCon->f('incluye_pc');
                        $dias_consumidos_descu = $oCon->f('dias_consumo');
                    }
                }
                $oCon->Free();

                if (!empty($dias_consumidos_descu)) {
                    $dias_consumidos = $dias_consumidos_descu;
                }

                $diasDiferencia = 0;
                $valor_dia = 0;
                $valor_uso = 0;
                $valor_no_uso = 0;

                $txt_log = "";
                if ($i == 1) {

                    $diaMes = $this->ultimoDiaMes($fecha_ok);
                    $nuevaFecha = $anioI . '/' . $mesI . '/' . $diaMes;
                    if ($dias_consumidos == 'S') {
                        $diaUso = diferenciaDias($fecha_ok, $nuevaFecha);
                        $diaUso++;
                        $ultimoDiaMes = $this->ultimoDiaMes($nuevaFecha);
                        $diasDiferencia = $ultimoDiaMes - $diaUso;
                        $valor_dia = round($tarifa / $ultimoDiaMes, 6);
                        $valor_uso = round($valor_dia * $diaUso, 6);
                        $valor_no_uso = round($valor_dia * $diasDiferencia, 6);
                    } else {
                        $ultimoDiaMes = $this->ultimoDiaMes($nuevaFecha);
                        $diaUso = $this->ultimoDiaMes($nuevaFecha);
                    }

                    $arrayFecha[$i] = $nuevaFecha;
                } else {
                    $anio = substr($arrayFecha[$i - 1], 0, 4);
                    $mes = substr($arrayFecha[$i - 1], 5, 2) + 1;
                    $nuevaFecha = data_last_month_day($mes, $anio);
                    $arrayFecha[$i] = $nuevaFecha;
                    $ultimoDiaMes = $this->ultimoDiaMes($nuevaFecha);
                    $diaUso = $this->ultimoDiaMes($nuevaFecha);
                }

                $txt_log .= PHP_EOL . "tarifa = $tarifa";
                $txt_log .= PHP_EOL . "diaMes = $diaMes";
                $txt_log .= PHP_EOL . "nuevaFecha = $nuevaFecha";
                $txt_log .= PHP_EOL . "fecha_ok = $fecha_ok";
                $txt_log .= PHP_EOL . "diaUso = $diaUso";
                $txt_log .= PHP_EOL . "ultimoDiaMes = $ultimoDiaMes";
                $txt_log .= PHP_EOL . "diasDiferencia = $diasDiferencia";
                $txt_log .= PHP_EOL . "valor_dia = $valor_dia";
                $txt_log .= PHP_EOL . "valor_uso = $valor_uso";
                $txt_log .= PHP_EOL . "valor_no_uso = $valor_no_uso";
                $txt_log .= PHP_EOL . "anio = $anio";
                $txt_log .= PHP_EOL . "mes = $mes";

                $this->crearLog("Parametros calculo : $txt_log");

                $mesOk = substr($nuevaFecha, 5, 2);
                $anioOk = substr($nuevaFecha, 0, 4);

                $this->crearLog("nuevaFecha = $nuevaFecha");
                $this->crearLog("mesOk = $mesOk");
                $this->crearLog("anioOk = $anioOk");

                $sql = "SELECT count(*) as control FROM isp.contrato_pago WHERE id_contrato = $idContrato AND EXTRACT(MONTH FROM fecha) = '$mesOk' AND anio = '$anioOk' AND tipo = 'P'";
                $control = consulta_string_func($sql, 'control', $oCon, 0);

                $this->crearLog("count contrato_pago = $control : $sql");

                if ($control == 0 && $estado_contrato != 'PE') {

                    $idCuota = $this->registraTablaPago($nuevaFecha, $j, $estado_cuota, $mesOk, $anioOk, $tarifa, $valor_dia, $valor_uso, $valor_no_uso, $ultimoDiaMes, $diaUso, $diasDiferencia, 'P', '', $descuento_v);

                    $this->crearLog("idCuota = $idCuota");

                    //if ($d_descuento <> 0) {
                        if($dias_consumidos_descu == "S" && $i == 1){
                            $d_tarifa = $tarifa - $valor_no_uso;
                            $dd_descuento = round(($d_tarifa * $d_descuento_p) / 100, 0);
                            $dd_descuento = $dd_descuento * -1;
                        }else{
                            $dd_descuento = $d_descuento * -1;
                        }
                        
                        $sql = "update isp.contrato_pago SET descuento = $dd_descuento WHERE id = $idCuota";
                        $oCon->QueryT($sql);

                    //}

                    $txt_log = "";
                    $valor_dia_p = 0;
                    $valor_uso_p = 0;
                    $valor_no_uso_p = 0;
                    //ingreso de cuotas
                    foreach ($array as $val) {
                        $id = $val[0];
                        $idEquipo = $val[3];
                        $id_prod = $val[4];
                        $cod_prod = $val[5];
                        $precio = $val[6];
                        $dd_descuento_individual=0;

                        if ($i == 1) {
                            if ($dias_consumidos == 'S') {
                                $valor_dia_p = round($precio / $ultimoDiaMes, 6);
                                $valor_uso_p = round($valor_dia_p * $diaUso, 6);
                                $valor_no_uso_p = round($valor_dia_p * $diasDiferencia, 6);
                            }
                        }

                        //if ($d_descuento <> 0) {
                            $d_precio_individual = $precio - $valor_no_uso_p;
                            $dd_descuento_individual = round(($d_precio_individual * $d_descuento_p) / 100, 0);
                            $dd_descuento_individual = $dd_descuento_individual * -1;
                        //}

                        //prisp.int_r('PRECIO:'.$d_precio_individual);
                        //prisp.int_r('Descuento :'.$dd_descuento_individual);exit;

                        $txt_log .= "id = $id";
                        $txt_log .= "idEquipo = $idEquipo";
                        $txt_log .= "id_prod = $id_prod";
                        $txt_log .= "cod_prod = $cod_prod";
                        $txt_log .= "precio = $precio";
                        $txt_log .= "ultimoDiaMes = $ultimoDiaMes";
                        $txt_log .= "diaUso = $diaUso";
                        $txt_log .= "diasDiferencia = $diasDiferencia";
                        $txt_log .= "i = $i";
                        $txt_log .= "valor_dia_p = $valor_dia_p";
                        $txt_log .= "valor_uso_p = $valor_uso_p";
                        $txt_log .= "valor_no_uso_p = $valor_no_uso_p";

                        $this->crearLog("Calculo Equipo = $txt_log");
                        $this->crearLog("idCuota = $idCuota");

                        $this->crearLog("Op Pago, Crea contrato_pago_pack = " . $array_e_e[$val[13]]);

                        if ($array_e_e[$val[13]] == 'S') {
                            $this->registraTablaPagoPack($idEquipo, $id, $idCuota, $id_prod, $cod_prod, $nuevaFecha, $precio, $valor_dia_p, $valor_uso_p, $valor_no_uso_p, $ultimoDiaMes, $diaUso, 'A','','S','M',$dd_descuento_individual);
                        }

                        $oCon->QueryT($sql);
                    } //fin foreach

                    $j++;
                } //fin if control
            } //fin for duracion
            $this->registraAuditoriaContratos(11, $idUser, '');
            return 'ok';
        } //fin if control array
    }

    /***************************************************
     * @ registraTablaPago
     * + Inserta tabla de pagos
     **************************************************/
    function registraTablaPago($nuevaFecha, $j, $estado, $mesOk, $anioOk, $tarifa, $valor_dia, $valor_uso, $valor_no_uso, $ultimoDiaMes, $diaUso, $diasDiferencia, $tipo = 'P', $detalle = '', $descuento = 0)
    {

        //variables
        $oCon = $this->oCon;
        $idContrato = $this->idContrato;
        $idClpv = $this->idClpv;
        $cero = 0;

        $sql = "insert into isp.contrato_pago (id_contrato, id_clpv, fecha, secuencial, estado, mes, anio, tarifa, 
                                            can_add, pre_add, tot_add, valor_pago, valor_dia, valor_uso, valor_no_uso,
                                            dias, dias_uso, dias_no_uso, tipo, detalle, descuento)
                                    values($idContrato, $idClpv, '$nuevaFecha', $j, '$estado', '$mesOk', '$anioOk', '$tarifa',
                                            $cero, $cero, $cero, $cero, '$valor_dia', '$valor_uso', '$valor_no_uso',
                                            $ultimoDiaMes, $diaUso, $diasDiferencia, '$tipo', '$detalle', '$descuento')";
        $oCon->QueryT($sql);

        $sql = "select max(id) as id FROM isp.contrato_pago where id_contrato = $idContrato and id_clpv = $idClpv and fecha = '$nuevaFecha'";
        $idCuota = consulta_string_func($sql, 'id', $oCon, 0);

        return $idCuota;
    }



    function registraTablaPagoPack($idEquipo, $id, $idCuota, $id_prod, $cod_prod, $nuevaFecha, $precio, $valor_dia_p, $valor_uso_p, $valor_no_uso_p, $ultimoDiaMes, $diaUso, $estado, $detalle = '', $recalcula_pack = 'N', $tipo = 'M',$descuento_individual= 0)
    {

        $idUser = $_SESSION['U_ID'];

        //variables
        $oCon = $this->oCon;
        $idContrato = $this->idContrato;
        $idempresa = $this->idEmpresa;
        $idsucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $cero = 0;

        if (empty($diaUso)) {
            $diaUso = $ultimoDiaMes;
        }

        if (empty($idEquipo)) {
            $idEquipo = 'null';
        }

        if (empty($id)) {
            $id = 'null';
        }

        if (empty($id_prod)) {
            $id_prod = 'null';
        }

        if (empty($precio)) {
            $precio = 0;
        }

        if (empty($descuento_individual)) {
            $descuento_individual = 0;
        }

        $sql = "INSERT into isp.contrato_pago_pack(id_empresa, id_sucursal, id_clpv, id_contrato,
                                            id_caja, id_pack, id_pago, id_prod, cod_prod, fecha, 
                                            tarifa, valor_pago, valor_dia, valor_uso, valor_no_uso,
                                            dias, dias_uso, estado, detalle, user_web, fecha_server,
                                            tipo,descuento,valor_nc)
                                        values($idempresa, $idsucursal, $idClpv, $idContrato,
                                            $idEquipo, $id, $idCuota, $id_prod, '$cod_prod', '$nuevaFecha',
                                            $precio, $cero, $valor_dia_p, $valor_uso_p, $valor_no_uso_p,
                                            $ultimoDiaMes, $diaUso, '$estado', '$detalle', $idUser, now(),
                                            '$tipo','$descuento_individual',0)";

        $oCon->QueryT($sql);

        if ($recalcula_pack == 'S') {
            //update contrato pago
            $sql = "update isp.contrato_pago SET tarifa = (tarifa + $precio),
                    valor_no_uso = (valor_no_uso + $valor_no_uso_p),
                    valor_uso = (tarifa + valor_uso - valor_no_uso)
                    WHERE id_contrato = $idContrato AND
                    id_clpv = $idClpv AND
                    id = $idCuota";
            $oCon->QueryT($sql);
        }


        return 'ok';
    }


    /***************************************************
     * @ registrarSenalEquipo
     * + Registra atenuacion rx olt - onu GPON
     **************************************************/
    function registrarSenalEquipo($fecha, $rx_olt, $rx_onu)
    {

        $oCon = $this->oCon;
        $idContrato = $this->idContrato;
        $idEquipo = $this->idEquipo;

        //variables de session
        $idUser = $_SESSION['U_ID'];

        //consulta paquetes 
        $sql = "INSERT INTO isp.int_contrato_caja_senal (id_contrato, id_caja, fecha, rx_olt, rx_onu, user_web)
                                VALUES($idContrato, $idEquipo, '$fecha', '$rx_olt', '$rx_onu', $idUser)";
        $oCon->QueryT($sql);
    }

    /***************************************************
     * @ ultimoDiaMes
     * + Funcion interna para calcular ultimo dia del mes
     **************************************************/
    function ultimoDiaMes($fecha)
    {

        $last_day = date('t', strtotime($fecha));

        return $last_day;
    }

    /***************************************************
     * @ crearLog
     * + Funcion privada para creacion del txt de log
     **************************************************/
    private function crearLog($log)
    {

        $pathLog = $this->pathLog;
        $idContrato = $this->idContrato;

        $a = fopen($pathLog, 'a');

        fwrite($a, "[" . date("d/m/Y H:i:s") . "][$idContrato]: $log\r\n");
        fclose($a);
    }
}
