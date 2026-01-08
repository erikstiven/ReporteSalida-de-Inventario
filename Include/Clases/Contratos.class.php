<?php

require_once(DIR_INCLUDE . 'comun.lib.php');
require_once(DIR_INCLUDE . 'Clases/Equipos.class.php');
require_once(DIR_INCLUDE . 'Clases/Isp/Webservice.class.php');

date_default_timezone_set(ZONA_HORARIA);

class Contratos
{

    var $oCon;
    var $oIfx;
    var $idEmpresa;
    var $idSucursal;
    var $idClpv;
    var $idContrato;

    function __construct($oCon, $oIfx, $idEmpresa, $idSucursal, $idClpv, $idContrato)
    {

        if (strlen($idContrato)>0) {
            $sql = "select id_sucursal from isp.contrato_clpv where id = $idContrato";
            $idSucursal = consulta_string_func($sql, 'id_sucursal', $oCon, 'id_sucursal');

            if (empty($idClpv)) {
                $sql = "select id_clpv from isp.contrato_clpv where id = $idContrato";
                $idClpv = consulta_string_func($sql, 'id_clpv', $oCon, 0);
            }
        } else {
            $idClpv = '';
            $idContrato = '';
        }

        $this->oCon = $oCon;
        $this->oIfx = $oIfx;
        $this->idEmpresa = $idEmpresa;
        $this->idSucursal = $idSucursal;
        $this->idClpv = $idClpv;
        $this->idContrato = $idContrato;
    }

    function consultarContrato()
    {

        $oIfx = $this->oIfx;
        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $array = array();

        $sql = "select * from isp.contrato_clpv 
                where id_empresa = $idEmpresa and
                id = $idContrato and
                id_clpv = $idClpv";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $id = $oCon->f('id');
                $id_empresa = $oCon->f('id_empresa');
                $id_sucursal = $oCon->f('id_sucursal');
                $id_clpv = $oCon->f('id_clpv');
                $id_ciudad = $oCon->f('id_ciudad');
                $codigo = $oCon->f('codigo');
                $nom_clpv = $oCon->f('nom_clpv');
                $ruc_clpv = $oCon->f('ruc_clpv');
                $fecha_contrato = $oCon->f('fecha_contrato');
                $fecha_firma = $oCon->f('fecha_firma');
                $fecha_corte = $oCon->f('fecha_corte');
                $fecha_cobro = $oCon->f('fecha_cobro');
                $duracion = $oCon->f('duracion');
                $penalidad = $oCon->f('penalidad');
                $estado = $oCon->f('estado');
                $vendedor = $oCon->f('vendedor');
                $user_web = $oCon->f('user_web');
                $fecha_server = $oCon->f('fecha_server');
                $tarifa = $oCon->f('tarifa');
                $id_dire = $oCon->f('id_dire');
                $saldo_mora = $oCon->f('deuda_1');
                $fecha_instalacion = $oCon->f('fecha_instalacion');
                $detalle = $oCon->f('detalle');
                $sobrenombre = $oCon->f('sobrenombre');
                $limite = $oCon->f('limite');
                $cobrador = $oCon->f('cobrador');
                $tipo_contrato = $oCon->f('tipo_contrato');
                $cheque_sn = $oCon->f('cheque_sn');
                $cobro_directo = $oCon->f('cobro_directo');
                $id_sector = $oCon->f('id_sector');
                $id_barrio = $oCon->f('id_barrio');
                $direccion = $oCon->f('direccion');
                $referencia = $oCon->f('referencia');
                $telefono = $oCon->f('telefono');
                $email = $oCon->f('email');
                $latitud = $oCon->f('latitud');
                $longitud = $oCon->f('longitud');
                $abonado = $oCon->f('abonado');
                $estado_tmp = $oCon->f('estado_tmp');
                $nombre = $oCon->f('nombre');
                $apellido = $oCon->f('apellido');
                $foto = $oCon->f('foto');
                $observaciones = $oCon->f('observaciones');
                $tipo_duracion = $oCon->f('tipo_duracion');
                $fecha_c_vence = $oCon->f('fecha_c_vence');
                $tipo_ncf = $oCon->f('tipo_ncf');
                $tipo_factura = $oCon->f('tipo_factura');
                $tarifa_e = $oCon->f('tarifa_e');
                $descuento_p = $oCon->f('descuento_p');
                $descuento_v = $oCon->f('descuento_v');
                $email_factura = $oCon->f('email_factura');
                $suscripcion = $oCon->f('suscripcion');
                $id_cont = $oCon->f('id_cont');
                $id_clie = $oCon->f('id_clie');
                $id_ncf  = $oCon->f('ncf');
                $abono   = $oCon->f('abono');
                $direccion_cobro   = $oCon->f('direccion_cobro');
                $ruletera   = $oCon->f('ruletera');
                $id_tipo_cobro   = $oCon->f('id_tipo_cobro');
                $id_ruta   = $oCon->f('id_ruta');
                $ruta   = $oCon->f('ruta');
                $orden_ruta   = $oCon->f('orden_ruta');
                $id_ruletera = $oCon->f('id_ruletera');
                $direccion_fiscal = $oCon->f('direccion_fiscal');
                $cantidad_tv = $oCon->f('cantidad_tv');
                $clpv_whatsapp = $oCon->f('clpv_whatsapp');
                $moneda_id = $oCon->f('moneda_id');
                $forma_pago = $oCon->f('forma_pago');
                $tipo_contrato_de_cobro = $oCon->f('tipo_contrato_de_cobro');
                $fecha_c_corte = $oCon->f('fecha_c_corte');
            }
        }
        $oCon->Free();

        $this->idSucursal = $id_sucursal;

        if ($id > 0) {

            //estado
            $sql = "select estado, class, color from isp.estado_contrato where id = '$estado'";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    $estadoNombre = $oCon->f('estado');
                    $estadoClass = $oCon->f('class');
                    $estadoColor = $oCon->f('color');
                }
            }
            $oCon->Free();

            $tipoContrato = '';
            if (!empty($tipo_contrato)) {
                $sql = "select tipo from isp.int_tipo_contrato where id = '$tipo_contrato'";
                $tipoContrato = consulta_string_func($sql, 'tipo', $oCon, '');
            }

            $vend_nom_vend = '';
            if (!empty($vendedor)) {
                $sql = "select vend_nom_vend from saevend where vend_cod_vend = '$vendedor'";
                $vend_nom_vend = consulta_string_func($sql, 'vend_nom_vend', $oIfx, '');
            }

            $nom_cobrador = '';
            if (!empty($cobrador)) {
                $sql = "select (cobr_nom_cobr || ' ' || cobr_ape_cobr) as cobrador from saecobr where cobr_cod_cobr = '$cobrador'";
                $nom_cobrador = consulta_string_func($sql, 'cobrador', $oIfx, '');
            }

            //sector
            $sector = '';
            if (!empty($id_sector)) {
                $sql = "select sector from comercial.sector_direccion where id = $id_sector";
                $sector = consulta_string_func($sql, 'sector', $oCon, 0);
            }

            //barrio
            $barrio = '';
            if (!empty($id_barrio)) {
                $sql = "select barrio from isp.int_barrio where id = $id_barrio";
                $barrio = consulta_string_func($sql, 'barrio', $oCon, 0);
            }

            $sql = "SELECT sum(precio) as tarifa FROM isp.int_contrato_caja_pack WHERE id_contrato = $id AND estado not in ('E')";
            $tarifa = consulta_string_func($sql, 'tarifa', $oCon, 0);

            $array[] = array(
                $id,                        $id_empresa,            $id_sucursal,       $id_clpv,           $id_ciudad,
                $codigo,                    $nom_clpv,              $ruc_clpv,          $fecha_contrato,    $fecha_firma,
                $fecha_corte,               $fecha_cobro,           $duracion,          $penalidad,         $estado,
                $vendedor,                  $user_web,              $fecha_server,      $tarifa,            $id_dire,
                $saldo_mora,                $fecha_instalacion,     $detalle,           $sobrenombre,       $limite,
                $cobrador,                  $tipo_contrato,         $cheque_sn,         $cobro_directo,     $id_sector,
                $id_barrio,                 $direccion,             $referencia,        $telefono,          $email,
                $latitud,                   $longitud,              $abonado,           $estado_tmp,        $nombre,
                $apellido,                  $foto,                  $observaciones,     $tipo_duracion,     $fecha_c_vence,
                $estadoNombre,              $estadoClass,           $sector,            $barrio,            $tipoContrato,
                $vend_nom_vend,             $nom_cobrador,          $estadoColor,       $tipo_ncf,          $tarifa_e,
                $descuento_p,               $descuento_v,           $email_factura,     $suscripcion,       $id_cont,
                $id_clie,                   $abono,                 $direccion_cobro,   $ruletera,          $id_tipo_cobro,
                $id_ruta,                   $ruta,                  $orden_ruta,        $id_ruletera,       $tipo_factura,
                $direccion_fiscal,          $cantidad_tv,           $clpv_whatsapp,     $moneda_id,         $forma_pago,
                $tipo_contrato_de_cobro,    $fecha_c_corte
            );
        }

        return $array;
    }

    function consultarTablaPagosContrato()
    {

        $oConexion = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $sql = "select * from isp.contrato_pago
                where id_contrato = $idContrato and
                id_clpv = $idClpv
                order by secuencial";
        if ($oConexion->Query($sql)) {
            if ($oConexion->NumFilas() > 0) {
                unset($array);
                do {
                    $id = $oConexion->f('id');
                    $id_contrato = $oConexion->f('id_contrato');
                    $id_clpv = $oConexion->f('id_clpv');
                    $id_pago = $oConexion->f('id_pago');
                    $fecha = $oConexion->f('fecha');
                    $mes = $oConexion->f('mes');
                    $anio = $oConexion->f('anio');
                    $secuencial = $oConexion->f('secuencial');
                    $estado = $oConexion->f('estado');
                    $estado_fact = $oConexion->f('estado_fact');
                    $id_factura = $oConexion->f('id_factura');
                    $tarifa = $oConexion->f('tarifa');
                    $valor_pago = $oConexion->f('valor_pago');
                    $tipo = $oConexion->f('tipo');

                    $array[] = array($id, $id_pago, $fecha, $mes, $anio, $secuencial, $estado, $estado_fact, $id_factura, $tarifa, $valor_pago, $tipo);
                } while ($oConexion->SiguienteRegistro());
            }
        }
        $oConexion->Free();

        return $array;
    }


    function consultaMesesAdeuda()
    {

        $oCon = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $oCon = $this->oCon;
        $idContrato = $this->idContrato;

        $id_sucursal_sesion = $_SESSION["U_SUCURSAL"];

        //estado al contrato
        $sql = "SELECT estado, tipo_contrato_de_cobro from isp.contrato_clpv WHERE id = $idContrato";
        $estado = consulta_string_func($sql, 'estado', $oCon, '');
        $tipo_contrato_de_cobro = consulta_string_func($sql, 'tipo_contrato_de_cobro', $oCon, '');

        /* $indicador = "";
        $fecha = "";
        if ($estado == 'AP') {
            if ($tipo_contrato_de_cobro == "POSTPAGO") {
                $indicador = "<";
            } else {
                $indicador = "<=";
            }
            $fecha = date("Y-m-d");
        } else {
            if ($tipo_contrato_de_cobro == "POSTPAGO") {
                $indicador = "<";
            } else {
                $indicador = "<=";
            }
        } */

        $fecha = date("Y-m-d");
        $ultimoDiaMes = date('t', strtotime($fecha));
        $dia = date("d");
        $mes = date("m");
        $anio = date("Y");
        $fechaComparaPre = $anio . '/' . $mes . '/' . $ultimoDiaMes;
        $fechaComparaPos = $anio . '/' . $mes . '/01';

        $sql = "SELECT para_aprox_num     
                from saepara
                WHERE para_cod_sucu = $id_sucursal_sesion";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $para_aprox_num          = $oCon->f('para_aprox_num');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        if($para_aprox_num > 0){
            if($para_aprox_num == 10){
                $num_min = 100;
            }else if($para_aprox_num == 1){
                $num_min = 1;
            }else{
                $num_min = 0;
            }
        }else{
            $num_min = 0;
        }

        /* $sql = "select count(*) as cuotas
                from isp.contrato_pago  p, isp.contrato_clpv c, isp.estado_contrato e
                WHERE c.id = p.id_contrato and
				c.id_clpv = p.id_clpv and 
				c.estado = e.id and
				p.id_clpv = $idClpv and 
                p.id_contrato = $idContrato and
                p.fecha $indicador LAST_DAY(CURRENT_DATE) and
                (p.tarifa + p.tot_add - p.valor_pago - p.valor_no_uso) <> $num_min and
                p.estado = 'PE' and
				e.aplica_cartera = 'S'";
        $cuotas = consulta_string_func($sql, 'cuotas', $oCon, 0); */

        $sql="SELECT a.id, sum(p.tarifa + p.tot_add - p.valor_pago - p.valor_no_uso + p.descuento + p.valor_nc) as saldo, count(p.id) as num_cuotas
                FROM isp.contrato_clpv a INNER JOIN isp.contrato_pago p ON a.id = p.id_contrato 
                WHERE CASE a.tipo_contrato_de_cobro
                    WHEN 'POSTPAGO' 
                            THEN 
                            p.fecha < '$fechaComparaPos'
                    ELSE
                    p.fecha <= '$fechaComparaPre'
                    END 
                            AND (p.tarifa + p.tot_add - p.valor_pago - p.valor_no_uso + p.descuento + p.valor_nc) > $num_min AND p.estado = 'PE' AND a.id = $idContrato GROUP BY a.id";

        $cuotas = consulta_string_func($sql, 'num_cuotas', $oCon, 0);

        return $cuotas;
    }

    function consultaMontoMesAdeuda()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oCon = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $id_sucursal_sesion = $_SESSION['U_SUCURSAL'];

        $sql = "SELECT para_aprox_num     
                from saepara
                WHERE para_cod_sucu = $id_sucursal_sesion";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $para_aprox_num          = $oCon->f('para_aprox_num');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        if ($para_aprox_num > 0) {
            if ($para_aprox_num == 10) {
                $num_min = 100;
            } else if ($para_aprox_num == 1) {
                $num_min = 1;
            } else {
                $num_min = 0;
            }
        } else {
            $num_min = 0;
        }

        $fecha = date("Y-m-d");
        $ultimoDiaMes = date('t', strtotime($fecha));
        $dia = date("d");
        $mes = date("m");
        $anio = date("Y");
        $fechaComparaPre = $anio . '/' . $mes . '/' . $ultimoDiaMes;
        $fechaComparaPos = $anio . '/' . $mes . '/01';

        $sql = "SELECT para_aprox_num     
                from saepara
                WHERE para_cod_sucu = $id_sucursal_sesion";
        $para_aprox_num = consulta_string_func($sql, 'para_aprox_num', $oCon, 0);

        $sql="SELECT a.id, sum(p.tarifa + p.tot_add - p.valor_pago - p.valor_no_uso + p.descuento + p.valor_nc) as saldo, count(p.id) as num_cuotas
                FROM isp.contrato_clpv a INNER JOIN isp.contrato_pago p ON a.id = p.id_contrato 
                WHERE CASE a.tipo_contrato_de_cobro
                    WHEN 'POSTPAGO' 
                            THEN 
                            p.fecha < '$fechaComparaPos'
                    ELSE
                    p.fecha <= '$fechaComparaPre'
                    END 
                            AND (p.tarifa + p.tot_add - p.valor_pago - p.valor_no_uso + p.descuento + p.valor_nc) > $num_min AND p.estado = 'PE' AND a.id = $idContrato GROUP BY a.id";

        $totalDeuda = consulta_string_func($sql, 'saldo', $oCon, 0);

        $totalDeuda = aproxima_digitos($para_aprox_num, $totalDeuda);

        return $totalDeuda;
    }

    function consultaMontoAdeudaCredito()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oCon = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $id_sucursal_sesion = $_SESSION['U_SUCURSAL'];

        $sql = "SELECT para_aprox_num     
                from saepara
                WHERE para_cod_sucu = $id_sucursal_sesion";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $para_aprox_num          = $oCon->f('para_aprox_num');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        if ($para_aprox_num > 0) {
            if ($para_aprox_num == 10) {
                $num_min = 100;
            } else if ($para_aprox_num == 1) {
                $num_min = 1;
            } else {
                $num_min = 0;
            }
        } else {
            $num_min = 0;
        }

        $sql = "SELECT tipo_contrato_de_cobro
                from isp.contrato_clpv
                WHERE id = $idContrato";
        $tipo_contrato_de_cobro = consulta_string_func($sql, 'tipo_contrato_de_cobro', $oCon, 0);

        $fecha = date("Y-m-d");
        $ultimoDiaMes = date('t', strtotime($fecha));
        $dia = date("d");
        $mes = date("m");
        $anio = date("Y");

        if($tipo_contrato_de_cobro == 'PREPAGO'){
            $fechaCompara = $anio . '/' . $mes . '/' . $ultimoDiaMes;
        }else{
            $fechaCompara = $anio . '/' . $mes . '/01';
        }
        
        

        $sql = "SELECT para_aprox_num     
                from saepara
                WHERE para_cod_sucu = $id_sucursal_sesion";
        $para_aprox_num = consulta_string_func($sql, 'para_aprox_num', $oCon, 0);

        //DEUDAS CREDITO

        $sql = "SELECT
                        id_clpv,
                        saldo,
                        fact_cod_contr,
                        dmcc_num_fac
                    FROM (
                        SELECT
                            (SUM(a.dmcc_deb_ml) - SUM(a.dmcc_cre_ml)) AS saldo,
                            MAX(a.dmcc_cod_fact) AS id_factura,
                                    MAX(a.clpv_cod_clpv) AS id_clpv,
                            a.dmcc_num_fac,
                            a.clpv_cod_clpv
                        FROM
                            saedmcc a
                        WHERE
                            a.dmcc_fec_emis <= '$fechaCompara' and 
                            a.clpv_cod_clpv = $idClpv 
                        GROUP BY
                            a.dmcc_num_fac,
                            a.clpv_cod_clpv
                    ) AS subconsulta INNER JOIN saefact ON id_factura = fact_cod_fact
                    WHERE
                        saldo > 0 and id_factura > 0 and fact_cod_contr = $idContrato
                    ORDER BY
                        dmcc_num_fac";
        unset($array_ini);
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $id_clpv        = $oCon->f('id_clpv');
                    $saldo          = $oCon->f('saldo');
                    $fact_cod_contr = $oCon->f('fact_cod_contr');
                    $dmcc_num_fac   = $oCon->f('dmcc_num_fac');
                    $array_indi = array(
                        'id_clpv' => $id_clpv,
                        'saldo' => $saldo,
                        'fact_cod_contr' => $fact_cod_contr,
                        'dmcc_num_fac' => $dmcc_num_fac
                    );
                    $array_ini[] = $array_indi;
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        // Array para almacenar el resultado final
        $datos_procesados = array();
        foreach ($array_ini as $resultado) {
            $fact_cod_contr = $resultado['fact_cod_contr'];
            $saldo = $resultado['saldo'];
            if (isset($datos_procesados[$fact_cod_contr])) {
                // Si ya existe el fact_cod_contr en el array, sumar el saldo
                $datos_procesados[$fact_cod_contr]['saldo'] += $saldo;
                // Contar las veces que se repite el fact_cod_contr (número de cuotas)
                $datos_procesados[$fact_cod_contr]['num_cuotas'] += 1;
            } else {
                // Si no existe, agregar el fact_cod_contr al array con su saldo inicial y número de cuotas
                $datos_procesados[$fact_cod_contr] = array(
                    'saldo' => $saldo,
                    'num_cuotas' => 1,
                );
            }
        }

        $totalDeuda = $datos_procesados[$idContrato]['saldo'];

        $totalDeuda = aproxima_digitos($para_aprox_num, $totalDeuda);

        return $totalDeuda;
    }

    function consultaTelefonos()
    {

        $oIfx = $this->oIfx;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        //lectura sucia
        //

        $telefono = '';

        $sql = "select tlcp_tip_ticp, tlcp_tlf_tlcp
                from saetlcp
                where tlcp_cod_clpv = $idClpv and
                tlcp_cod_contr = $idContrato";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $telefono .= $oIfx->f('tlcp_tip_ticp') . ' - ' . $oIfx->f('tlcp_tlf_tlcp') . ' ';
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        return $telefono;
    }

    function consultaEmail()
    {

        $oIfx = $this->oIfx;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        //lectura sucia
        //

        $email = '';

        $sql = "select emai_ema_emai
                from saeemai
                where emai_cod_clpv = $idClpv and
                emai_cod_contr = $idContrato";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $email .= $oIfx->f('emai_ema_emai') . ' ';
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        return $email;
    }

    function consultaServicios()
    {

        $oIfx = $this->oIfx;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        //lectura sucia
        //

        unset($arrayServicios);
        $sql = "select clse_cod_clse, clse_cod_bode, clse_cod_prod, clse_pre_clse, clse_cobr_sn
                from saeclse
                where clse_cod_clpv = $idClpv and
                clse_cod_contr = $idContrato";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $clse_cod_clse = $oIfx->f('clse_cod_clse');
                    $clse_cod_prod = $oIfx->f('clse_cod_prod');
                    $clse_cod_bode = $oIfx->f('clse_cod_bode');
                    $clse_pre_clse = $oIfx->f('clse_pre_clse');
                    $clse_cobr_sn = $oIfx->f('clse_cobr_sn');

                    $arrayServicios[] = array($clse_cod_clse, $clse_cod_prod, $clse_cod_bode, $clse_pre_clse, $clse_cobr_sn);
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        return $arrayServicios;
    }

    function sumaTotalesServicio()
    {

        $oCon = $this->oCon;
        $oIfx = $this->oIfx;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        //lectura sucia
        //

        //tv adicional
        $sql = "select sum(clse_cant_add) as cantidad,
                sum(clse_pre_add) as precio,
                sum(clse_tot_add) as total
                from saeclse 
                where clse_cod_contr = $idContrato and
                clse_cod_clpv = $idClpv and
                clse_cobr_sn = 'S'";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $cantidad = $oIfx->f('cantidad');
                $precio = $oIfx->f('precio');
                $total = $oIfx->f('total');
            }
        }
        $oIfx->Free();

        if (empty($cantidad)) {
            $cantidad = 0;
        }

        if (empty($precio)) {
            $precio = 0;
        }

        if (empty($total)) {
            $total = 0;
        }

        $sql = "select sum(clse_pre_clse) as valor
                from saeclse 
                where clse_cod_contr = $idContrato and
                clse_cod_clpv = $idClpv and
                clse_cobr_sn = 'S'";
        $valor = round(consulta_string_func($sql, 'valor', $oIfx, 0), 2);
        $valorOk = $valor + $total;

        //UPDATE precio
        $sqlUPDATE = "UPDATE isp.contrato_clpv set tarifa = '$valorOk' where id = $idContrato and id_clpv = $idClpv";
        $oCon->QueryT($sqlUPDATE);

        /*$sql = "select count(*) as control
                from saeclse
                where clse_cod_contr = $idContrato and
                clse_cod_clpv = $idClpv";
        $control = consulta_string_func($sql, 'control', $oIfx, 0);

        $opCombo = 'N';
        if ($control > 1) {
            $opCombo = 'S';
        }

        $sql = "UPDATE isp.contrato_pago set tarifa = '$valor',
                can_add = '$cantidad',
                pre_add = '$precio',
                tot_add = '$total',
                combo_sn = '$opCombo'
                where id_clpv = $idClpv and
                id_contrato = $idContrato and
                valor_pago = 0 and
                estado_fact is null and
                tipo = 'P' and
                corte = 'N'";
        $oCon->QueryT($sql);*/

        return 'OK';
    }

    function registraAuditoriaContratos($idProceso, $userWeb, $msj)
    {

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $fecha = date("Y-m-d");
        $fechaServer = date("Y-m-d H:i:s");

        $sql = "insert into isp.int_auditoria(id_empresa, id_sucursal, id_proceso, id_clpv, id_contrato, user_web, fecha, fecha_server, observaciones)
                                    values($idEmpresa, $idSucursal, $idProceso, $idClpv, $idContrato, $userWeb, '$fecha', '$fechaServer', '$msj')";
        $oCon->QueryT($sql);
        return 'OK';
    }

    function htmlEncabezadoContrato()
    {

        global $DSN, $DSN_Ifx;
        $oCon2 = new Dbo();
        $oCon2->DSN = $DSN;
        $oCon2->Conectar();

        $oCon = $this->oCon;
        $oIfx = $this->oIfx;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $arrayContrato = $this->consultarContrato();

        if (count($arrayContrato) > 0) {
            foreach ($arrayContrato as $val) {
                $id_sucursal = $val[2];
                $codigo = $val[5];
                $nom_clpv = $val[6];
                $ruc_clpv = $val[7];
                $fecha_contrato = $val[8];
                $fecha_corte = $val[10];
                $fecha_cobro = $val[11];
                $duracion = $val[12];
                $penalidad = $val[13];
                $tarifa = $val[18];
                $fecha_instalacion = $val[21];
                $sobrenombre = $val[23];
                $limite = $val[24];
                $cheque_sn = $val[27];
                $cobro_directo = $val[28];
                $direccion = $val[31];
                $referencia = $val[32];
                $celular = $val[33];
                $email = $val[34];
                $latitud = $val[35];
                $longitud = $val[36];
                $foto = $val[41];
                $estadoNombre = $val[45];
                $estadoClass = $val[46];
                $sector = $val[47];
                $barrio = $val[48];
                $tipoContrato = $val[49];
                $vend_nom_vend = $val[50];
                $cobrador = $val[51];
                $estadoColor = $val[52];
                $tarifa_e = $val[54];
                $descuento_p = $val[55];
                $descuento_v = $val[56];
                $direccion_cobro = $val[62];
                $id_ruta = $val[65];
                $ruta = $val[66];
                $orden_ruta = $val[67];
                $fecha_c_corte = $val[76];
            }
        }

        if (empty($cobrador)) {
            $cobrador = '&nbsp;';
        }

        $ubicacion = '';

        //deuda
        $valor = $this->consultaMontoMesAdeuda();
        $balance_credito = $this->consultaMontoAdeudaCredito();
        $meses = $this->consultaMesesAdeuda();
        $contratoTelefonos = $this->consultaTelefonos();
        $contratoEmail = $this->consultaEmail();

        if ($latitud <> 0 && $longitud <> 0) {
            $ubicacion = '<span class="btn btn-success " onclick="verMapa(' . $idContrato . ', ' . $idClpv . ');"> <i class="glyphicon glyphicon-globe"></i> </span><span class="text-primary" style="cursor: pointer; font-size: 11px;" onclick="verMapa(' . $idContrato . ', ' . $idClpv . ');"> ' . $latitud . ', ' . $longitud . '</span>';
        }

        //fotos
        $sHtmlFoto = '';
        if (!empty($foto)) {
            $sHtmlFoto = '';
        }

        //tarifa especial
        $sHtmlTarifa = '';
        if ($tarifa_e == 'S') {

            if ($descuento_v > 0) {
                $tarifa -= $descuento_v;
            }

            $sHtmlTarifa = '<a class="btn btn-xs bg-maroon" href="# return 0" style="font-size: 15px;">TARIFA ESPECIAL: ' . number_format($descuento_v, 2, '.', ',') . ' (' . $descuento_p . ' %)</a>';
        }


        //nombre sucursal
        $sql = "SELECT sucu_nom_sucu FROM saesucu WHERE sucu_cod_sucu = $id_sucursal";
        $sucu_nom_sucu = consulta_string_func($sql, 'sucu_nom_sucu', $oIfx, '');

        $sql = "SELECT foto, identificador, id_tipo_cont_serv ,observaciones, vendedor, info_actual_promo FROM isp.contrato_clpv WHERE id = $idContrato";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $foto                   = $oCon->f('foto');
                    $identificador          = $oCon->f('identificador');
                    $id_tipo_cont_serv      = $oCon->f('id_tipo_cont_serv');
                    $observaciones_contr    = $oCon->f('observaciones');
                    $info_actual_promo      = $oCon->f('info_actual_promo', false);
                    $info_actual_promo      = json_decode($info_actual_promo);

                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sHtmlPromo = "";
        if(count($info_actual_promo) > 0){
            $sHtmlPromo .= '<div class="alert alert-success" role="alert" style="font-size:15px">Contrato tiene promoción: <b>'.$info_actual_promo->nombre.'</b> <button class="btn btn-info btn-xs" onclick="detalles_promo_2('.$idContrato.')"><i class="fa-solid fa-eye"></i> Detalles</button></div>';
        }

        $cod_vend = consulta_string_func($sql, 'vendedor', $oIfx, 0);

        $sql = "SELECT id_nap FROM isp.int_contrato_caja WHERE id_contrato = $idContrato AND estado not in ('E') AND id_nap is not null";
        $id_nap = consulta_string_func($sql, 'id_nap', $oIfx, 0);

        if(!empty($id_nap) && $id_nap > 0){
            $sql = "SELECT concat(siglas, ' / ', nombre) as nap FROM isp.int_nap where id = $id_nap";
            $nap = consulta_string_func($sql, 'nap', $oIfx, '');
        }

      //  $vendedor_contr = '';
        if (!empty($cod_vend)) {
            $sql = "SELECT vend_nom_vend from saevend where vend_cod_vend = '$cod_vend'";
            $vendedor_contr = consulta_string_func($sql, 'vend_nom_vend', $oIfx, '');
        }

    
        if ($id_tipo_cont_serv > 0) {
            $sql = "SELECT nombre FROM isp.int_tipo_servicio WHERE id = $id_tipo_cont_serv";
            $nombre_tip_cliente = consulta_string_func($sql, 'nombre', $oIfx, '');
        }

        if (empty($foto)) {
            $foto = "/images/abonado_ejemplo.png";
        }

        if (empty($identificador)) {
            $identificador = "/images/sin_firma.png";
        }

        $sql = "SELECT id, descripcion from isp.int_tipo_proceso";
        $array_p = array_dato($oCon, $sql, 'id', 'descripcion');

        $sHtmlI = '';
        //ultimas 5 ordenes
        $sql = "SELECT id, id_proceso, fecha, estado, observaciones, user_web, to_char(fecha_server, 'HH12:MI:SS') as hora
                from isp.instalacion_clpv
                WHERE id_contrato = $idContrato and
                estado != 'AN'
                order by id desc
                limit 3";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $id_i = $oCon->f('id');
                    $id_proceso = $oCon->f('id_proceso');
                    $fecha = $oCon->f('fecha');
                    $hora = $oCon->f('hora');
                    $estado = $oCon->f('estado');
                    $observaciones = $oCon->f('observaciones');
                    $user_web = $oCon->f('user_web');

                    $sHtmlI .= '<a href="# return 0;" onclick="vistaPrevia_1(' . $id_i . ', ' . $idClpv . ', ' . $idContrato . ');" data-toggle="tooltip" data-placement="left" title="' . $observaciones . '">
                                    <p class="text-primary" style="text-align: left;">' . $array_p[$id_proceso] . ' </p>
                                    <p style="text-align: right;"><i class="fa fa-clock-o"></i> ' . fecha_mysql_dmy($fecha) . ' ' . $hora . '</p>
                                </a>';
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $dire_cobro = "";
        if (!empty($direccion_cobro)) {
            $dire_cobro = "<p style='font-size: 17px; font-weight: bold;' class='text-danger'>Cobro: $direccion_cobro</p>";
        }

        $ruta_n = '';
        if (!empty($id_ruta)) {
            $sql = "SELECT concat(codigo, ' ', nombre) as ruta from isp.int_rutas WHERE id = $id_ruta";
            $ruta_n = consulta_string_func($sql, 'ruta', $oCon, '');
        }

        $ruta = $_COOKIE["JIREH_URI"] . "modulos/";
        $ruta_img = $ruta . 'int_clientes/' . $foto . '';

        $sql = "SELECT imagen from contrato_firmas where id_contrato = $idContrato";
        $imagen_firma       = consulta_string($sql, 'imagen', $oCon, '');

        if (!empty($imagen_firma)) {
            $sHtmlFirma     = '<img width="200px;" height="100px;" src="data:image/png;base64,' . $imagen_firma . '">';
        } else {
            if (!empty($identificador)) {
                $ruta_firma = $ruta . 'int_clientes/' . $identificador . '';

                $sHtmlFirma = '<img class="margin" src="' . $ruta_firma . '" alt="Foto" style="width: 100px; height: 100px; float: center;" />';
            }
        }


        $sHtml_adj .= '<br><table class="table table-condensed table-striped table-bordered table-hover" style="width: 100%;">
                    <tr>
                    <td colspan="4" class="bg-green"><h5>ADJUNTOS <small style="color:white">Reporte Informacion</small></h5></td>
                    </tr>
                    <tr>
                    <td class="bg-green">No.</td>
                    <td class="bg-green">Titulo</td>
                    <td class="bg-green">Adjunto</td>
                    </tr>';

        $sql = "SELECT id, titulo, ruta
				from comercial.adjuntos_clpv
				where id_clpv = $idClpv and
				id_contrato = $idContrato and estado not in ('E')";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $i = 1;
                do {
                    $titulo = $oCon->f('titulo');
                    $ruta = $oCon->f('ruta');

                    $optionTipoAdj = '';

                    if (is_numeric($titulo)) {
                        $sql = "SELECT tipo
                                from isp.int_adjuntos_tipo
                                WHERE estado = 'A' and id = '$titulo'";
                        if ($oCon2->Query($sql)) {
                            if ($oCon2->NumFilas() > 0) {
                                do {
                                    $optionTipoAdj .= $oCon2->f('tipo');
                                } while ($oCon2->SiguienteRegistro());
                            }
                        }
                        $oCon2->Free();

                        $sHtml_adj .= '<tr>';
                        $sHtml_adj .= '<td>' . $i++ . '</td>';
                        $sHtml_adj .= '<td>' . $optionTipoAdj . '</td>';
                        $sHtml_adj .= '<td><a href="#" onclick="dowloand(\'' . $ruta . '\')">' . $ruta . '</a></td>';
                        $sHtml_adj .= '</tr>';
                    }
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sHtml_adj .= '</table>';

        $sHtml_0 = '<div class="col-md-9">
                        <ul class="timeline">
                            <li class="time-label">
                                <span class="bg-' . $estadoColor . '" style="font-size: 15px;">' . $codigo . '  ' . $estadoNombre . '</span>
                                '.$sHtmlPromo.'
                            </li>
                            <li>
                                <i class="fa fa-user bg-blue"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fa fa-clock-o"></i> ' . fecha_mysql_dmy($fecha_contrato) . '</span>
                                    <h2 class="timeline-header">
                                        <a href="# return 0;" style="font-size: 18px;">' . $nom_clpv . ' <br> ' . $nombre_tip_cliente . '   </a> 
                                    </h2>
                                    <div class="timeline-body col-md-12">
                                        <div class="col-md-12" align="right">
                                            ' . $sHtmlTarifa . '
                                        </div>
                                        <div class="col-md-8">
                                            <p class="fecha_letra" style="font-size: 12px;">Datos Personales</p>
                                            <p style="font-size: 17px;">' . $ruc_clpv . '</p>
                                            <p style="font-size: 17px;">' . $sobrenombre . '</p>
                                            <p class="fecha_letra" style="font-size: 12px;">Direcci&oacute;n</p>
                                            <p style="font-size: 17px;">' . $direccion . '</p>
                                            <p style="font-size: 17px;">' . $referencia . '</p>
                                            <p style="font-size: 17px;">' . $sector . ' ' . $barrio . '</p>
                                            ' . $ubicacion . '
											' . $dire_cobro . '
											<p class="fecha_letra" style="font-size: 12px;">Ruta:</p>
											<p style="font-size: 17px; font-weight: bold;" class="text-success">' . $ruta_n . ' | ' . $ruta . '</p>
                                            <p class="fecha_letra" style="font-size: 12px;">NAP:</p>
                                            <p style="font-size: 17px;">' . $nap . '</p>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-condensed" style="width: 100%;">
                                                    <tr>
                                                        <td class="fecha_letra bg-primary" style="font-size: 14px;" colspan="2">Cuotas:</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fecha_letra" style="font-size: 12px;">Tarifa:</td>
                                                        <td style="font-size: 17px; color: red; font-weight: bold;" align="right"><a href="#" onclick="jsVerHistorialTarifa(' . $idContrato . ')">' . number_format($tarifa, 2, '.', ',') . '</a></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fecha_letra" style="font-size: 12px;">Balance:</td>
                                                        <td style="font-size: 17px; color: red; font-weight: bold;" align="right">' . number_format($valor, 2, '.', ',') . '</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fecha_letra" style="font-size: 12px;">Meses Mora:</td>
                                                        <td style="font-size: 17px; color: red; font-weight: bold;" align="right">' . $meses . '</td>
                                                    </tr>
                                                    <!-- <tr>
                                                        <td class="fecha_letra" style="font-size: 12px;">Ultimo Pago:</td>
                                                        <td style="font-size: 17px; color: red; font-weight: bold;" align="right">' . number_format($valor, 2, '.', ',') . '</td>
                                                    </tr> -->
                                                    <tr>
                                                        <td class="fecha_letra" style="font-size: 12px;">Fecha Pago:</td>
                                                        <td style="font-size: 17px; color: red; font-weight: bold;" align="right"></td>
                                                    </tr>
                                                </table>
                                                <table class="table table-striped table-condensed" style="width: 100%;">
                                                    <tr>
                                                        <td class="fecha_letra bg-primary" style="font-size: 14px;" colspan="2">Crédito:</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fecha_letra" style="font-size: 12px;">Balance:</td>
                                                        <td style="font-size: 17px; color: red; font-weight: bold;" align="right">' . number_format($balance_credito, 2, '.', ',') . '</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-footer col-md-12">
                                        <div class="col-md-12">
                                            <div class="col-md-4 form-group">
                                                <label class="fecha_letra" style="font-size: 12px;">Fecha Contrato:</label>
                                                <p style="font-size: 17px;">' . fecha_mysql_dmy($fecha_contrato) . '</p>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label class="fecha_letra" style="font-size: 12px;">Fecha Instalaci&oacute;n:</label>
                                                <p style="font-size: 17px;">' . fecha_mysql_dmy($fecha_instalacion) . '</p>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label class="fecha_letra" style="font-size: 12px;">Fecha U.Corte:</label>
                                                <p style="font-size: 17px;">' . fecha_mysql_dmy($fecha_c_corte) . '</p>
                                            </div>
                                        </div>
                                         <div class="col-md-12">
                                            <div class="col-md-4 form-group">
                                                <label class="fecha_letra" style="font-size: 12px;">Dia Cobro:</label>
                                                <p style="font-size: 17px;">' . $fecha_cobro . '</p>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label class="fecha_letra" style="font-size: 12px;">Dia Corte:</label>
                                                <p style="font-size: 17px;">' . $fecha_corte . '</p>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label class="fecha_letra" style="font-size: 12px;">Duracion:</label>
                                                <p style="font-size: 17px;">' . $duracion . '</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="col-md-12 form-group">
                                                <label class="fecha_letra" style="font-size: 12px;">Observación:</label>
                                                <p style="font-size: 17px;">' . $observaciones_contr . '</p>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="col-md-12 form-group">
                                                <label class="fecha_letra" style="font-size: 12px;">Vendedor:</label>
                                                <p style="font-size: 17px;">' . $vendedor_contr . '</p>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
											<a class="btn btn-xs bg-orange" href="# return 0" style="font-size: 15px;">' . $sucu_nom_sucu . '</a>
                                            <a class="btn btn-xs bg-teal" href="# return 0" style="font-size: 15px;">' . $tipoContrato . '</a>                                        
                                        </div>
                                        <br>
                                        <br>
                                        <div class="col-md-12">
                                            <div class="col-md-12 form-group table-responsive">
                                                ' . $sHtml_adj . '
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <i class="fa fa-envelope bg-aqua"></i>
                                <div class="timeline-item">
                                    <span class="time"></span>
                                    <h3 class="timeline-header no-border">
                                        <p>
                                            <a href="mailto: ' . $contratoEmail . '" style="font-size: 17px;">' . $contratoEmail . '</a>
                                        </p>
                                        <p>
                                            <a href="# return 0" style="font-size: 17px;">' . $contratoTelefonos . '</a>
                                        </p>
                                    </h3>
                                </div>
                            </li>
                            <!-- END timeline item -->
                            <li>
                                <i class="fa fa-clock-o bg-gray"></i>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="col-md-3">
                        <ul class="timeline">
                            <li class="time-label">
                                <span class="bg-' . $estadoColor . '" style="font-size: 15px;">' . $idContrato . '</span>
                            </li>
                            <li>
                                <i class="fa fa-camera bg-purple"></i>
                                <div class="timeline-item">
                                    <span class="time"></span>
                                    <h3 class="timeline-header"><a href="# return 0;">Documentos</a></h3>
                                    <div class="timeline-body">
                                        <div align="center">
                                            ' . $sHtmlFoto . '
                                            <img class="margin" src="' . $ruta_img . '" alt="Foto" style="width: 150px; height: 100px; align:center" />
                                        </div>
                                        <div align="center">
                                            ' . $sHtmlFirma . '
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <i class="fa fa-wrench bg-red"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header"><a href="# return 0;" onclick="ordenesTrabajo();">Ordenes Servicio</a></h3>
                                    <div class="timeline-body">
                                        <div class="">
                                        ' . $sHtmlI . '
                                        </div>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <h3 class="timeline-header"><a href="# return 0;" onclick="seleccionarTipoContrato();"><i class="fa-solid fa-print"></i> Impresión contrato</a></h3>
                                    <div class="timeline-body">
                                    </div>
                                </div>
                            </li> 
                            <!-- END timeline item -->
                            <li>
                                <i class="fa fa-clock-o"></i>
                            </li>
                        </ul>
                    </div>';

        return $sHtml_0;
    }

    function htmlEncabezadoContratoModi()
    {

        $oCon = $this->oCon;
        $oIfx = $this->oIfx;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $arrayContrato = $this->consultarContrato();

        if (count($arrayContrato) > 0) {
            foreach ($arrayContrato as $val) {
                $id_sucursal = $val[2];
                $codigo = $val[5];
                $nom_clpv = $val[6];
                $ruc_clpv = $val[7];
                $fecha_contrato = $val[8];
                $fecha_corte = $val[10];
                $fecha_cobro = $val[11];
                $duracion = $val[12];
                $penalidad = $val[13];
                $tarifa = $val[18];
                $fecha_instalacion = $val[21];
                $sobrenombre = $val[23];
                $limite = $val[24];
                $cheque_sn = $val[27];
                $cobro_directo = $val[28];
                $direccion = $val[31];
                $referencia = $val[32];
                $celular = $val[33];
                $email = $val[34];
                $latitud = $val[35];
                $longitud = $val[36];
                $foto = $val[41];
                $estadoNombre = $val[45];
                $estadoClass = $val[46];
                $sector = $val[47];
                $barrio = $val[48];
                $tipoContrato = $val[49];
                $vend_nom_vend = $val[50];
                $cobrador = $val[51];
                $estadoColor = $val[52];
                $tarifa_e = $val[54];
                $descuento_p = $val[55];
                $descuento_v = $val[56];
                $direccion_cobro = $val[62];
                $id_ruta = $val[65];
                $ruta = $val[66];
                $orden_ruta = $val[67];
            }
        }

        if (empty($cobrador)) {
            $cobrador = '&nbsp;';
        }

        $ubicacion = '';

        //deuda
        $valor = $this->consultaMontoMesAdeuda();
        $meses = $this->consultaMesesAdeuda();
        $contratoTelefonos = $this->consultaTelefonos();
        $contratoEmail = $this->consultaEmail();

        if ($latitud <> 0 && $longitud <> 0) {
            $ubicacion = '<span class="btn btn-success " onclick="verMapa(' . $idContrato . ', ' . $idClpv . ');"> <i class="glyphicon glyphicon-globe"></i> </span><span class="text-primary" style="cursor: pointer; font-size: 11px;" onclick="verMapa(' . $idContrato . ', ' . $idClpv . ');"> ' . $latitud . ', ' . $longitud . '</span>';
        }

        //fotos
        $sHtmlFoto = '';
        if (!empty($foto)) {
            $sHtmlFoto = '';
        }

        //tarifa especial
        $sHtmlTarifa = '';
        if ($tarifa_e == 'S') {

            if ($descuento_v > 0) {
                $tarifa -= $descuento_v;
            }

            $sHtmlTarifa = '<a class="btn btn-xs bg-maroon" href="# return 0" style="font-size: 15px;">TARIFA ESPECIAL: ' . number_format($descuento_v, 2, '.', ',') . ' (' . $descuento_p . ' %)</a>';
        }

        //nombre sucursal
        $sql = "SELECT sucu_nom_sucu FROM saesucu WHERE sucu_cod_sucu = $id_sucursal";
        $sucu_nom_sucu = consulta_string_func($sql, 'sucu_nom_sucu', $oIfx, '');

        $sql = "SELECT id, descripcion from isp.int_tipo_proceso";
        $array_p = array_dato($oCon, $sql, 'id', 'descripcion');

        $sHtmlI = '';
        //ultimas 5 ordenes
        $sql = "SELECT id, id_proceso, fecha, estado, observaciones, user_web, to_char(fecha_server, 'HH12:MI:SS') as hora
                from isp.instalacion_clpv
                WHERE id_contrato = $idContrato and
                estado != 'AN'
                order by id desc
                limit 3";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $id_i = $oCon->f('id');
                    $id_proceso = $oCon->f('id_proceso');
                    $fecha = $oCon->f('fecha');
                    $hora = $oCon->f('hora');
                    $estado = $oCon->f('estado');
                    $observaciones = $oCon->f('observaciones');
                    $user_web = $oCon->f('user_web');

                    $sHtmlI .= '<a href="# return 0;" onclick="vistaPrevia_1(' . $id_i . ', ' . $idClpv . ', ' . $idContrato . ');" data-toggle="tooltip" data-placement="left" title="' . $observaciones . '">
                                    <p class="text-primary" style="text-align: left;">' . $array_p[$id_proceso] . ' </p>
                                    <p style="text-align: right;"><i class="fa fa-clock-o"></i> ' . fecha_mysql_dmy($fecha) . ' ' . $hora . '</p>
                                </a>';
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $dire_cobro = "";
        if (!empty($direccion_cobro)) {
            $dire_cobro = "<p style='font-size: 17px; font-weight: bold;' class='text-danger'>Cobro: $direccion_cobro</p>";
        }

        $ruta_n = '';
        if (!empty($id_ruta)) {
            $sql = "SELECT concat(codigo, ' ', nombre) as ruta from isp.int_rutas WHERE id = $id_ruta";
            $ruta_n = consulta_string_func($sql, 'ruta', $oCon, '');
        }

        $sHtml_0 = '<div class="col-md-12">
        
                        <ul class="timeline">
                            
                            <li class="time-label">
                                <span class="bg-' . $estadoColor . '" style="font-size: 15px;">
                                    <button class="btn btn-sm btn-warning" onclick="volver()">
                                     <i class="fa-solid fa-turn-down-left"></i> Volver
                                    </button>' . $codigo . '  ' . $estadoNombre . ' - ' . $ruc_clpv . '</span>
                            </li>
                            <li>
                                <i class="fa fa-user bg-blue"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fa fa-clock-o"></i> ' . fecha_mysql_dmy($fecha_contrato) . '</span>
                                    <h2 class="timeline-header">
                                        <a href="# return 0;" style="font-size: 18px;">' . $nom_clpv . '</a> 
                                    </h2>
                                    <div class="timeline-body col-md-12">
                                        <div class="col-md-12" align="right">
                                            ' . $sHtmlTarifa . '
                                        </div>
                                    </div>
                                    <div class="timeline-footer col-md-12">
                                        <div class="col-md-12">
                                            <div class="col-md-2 form-group">
                                                <label class="fecha_letra" style="font-size: 11px;">Fecha Contrato:</label>
                                                <p style="font-size: 14px;">' . fecha_mysql_dmy($fecha_contrato) . '</p>
                                            </div>
                                            <div class="col-md-2 form-group">
                                                <label class="fecha_letra" style="font-size: 11px;">Fecha Instalaci&oacute;n:</label>
                                                <p style="font-size: 14px;">' . fecha_mysql_dmy($fecha_instalacion) . '</p>
                                            </div>
                                            <div class="col-md-2 form-group" align="right">
                                                <label class="fecha_letra" style="font-size: 11px;">Dia Cobro:</label>
                                                <p style="font-size: 14px;">' . $fecha_cobro . '</p>
                                            </div>
                                            <div class="col-md-2 form-group" align="right">
                                                <label class="fecha_letra" style="font-size: 11px;">Dia Corte:</label>
                                                <p style="font-size: 14px;">' . $fecha_corte . '</p>
                                            </div>
                                            <div class="col-md-2 form-group" align="right">
                                                <label class="fecha_letra" style="font-size: 11px;">Duracion:</label>
                                                <p style="font-size: 14px;">' . $duracion . '</p>
                                            </div>
                                            <div class="col-md-2 form-group" align="right">
                                                <label class="fecha_letra" style="font-size: 11px;">Tarifa:</label>
                                                <p style="font-size: 14px;">' . number_format($tarifa, 2, '.', ',') . '</p>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
											<a class="btn btn-xs bg-orange" href="# return 0" style="font-size: 12px;">SUCURSAL: ' . $sucu_nom_sucu . '</a>
                                            <a class="btn btn-xs bg-teal" href="# return 0" style="font-size: 12px;">' . $tipoContrato . '</a>                                           
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <i class="fa fa-envelope bg-aqua"></i>
                                <div class="timeline-item">
                                    <span class="time"></span>
                                    <h3 class="timeline-header no-border">
                                        <p>
                                            <a href="mailto: ' . $contratoEmail . '" style="font-size: 17px;">' . $contratoEmail . '</a>
                                        </p>
                                        <p>
                                            <a href="# return 0" style="font-size: 17px;">' . $contratoTelefonos . '</a>
                                        </p>
                                    </h3>
                                </div>
                            </li>
                            <!-- END timeline item -->
                            <li>
                                <i class="fa fa-clock-o bg-gray"></i>
                            </li>
                        </ul>
                    </div>';

        return $sHtml_0;
    }

    function htmlServiciosContrato()
    {

        $oCon = $this->oCon;
        $oIfx = $this->oIfx;
        $idEmpresa = $this->idEmpresa;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $sHtml = '<table id="table_pagos_procesar" class="table table-striped table-bordered table-hover table-condensed" style="width: 100%;" align="center">
                    <thead>
                        <tr>
                            <td colspan="7" class="bg-blue" style="font-size:16px" align="center">LISTADO EQUIPOS Y PLANES</td>
                        </tr>
                    </thead>
                    <tbody>';
        $sql = "SELECT c.nombre, a.id_tarjeta, a.ip, f.estado, f.color, COALESCE(i.sistema, 'N/A') as sistema, string_agg(d.paquete || '#$' || e.nombre || '#$' || b.cod_prod || '#$' || b.precio || '#$' || b.estado || '#$' || g.estado || '#$' || g.color || '#$' || COALESCE(h.nombre, 'N/A') || '#$' || COALESCE(b.codigo_cid, 'N/A') , '%&') as datos_caja_pack
                FROM isp.int_contrato_caja a 
                            INNER JOIN isp.int_contrato_caja_pack b ON a.id = b.id_caja
                            INNER JOIN isp.int_tipo_prod c ON a.id_tipo_prod = c.id
                            INNER JOIN isp.int_paquetes d ON b.id_prod = d.id
                            INNER JOIN isp.int_tipo_prod e ON d.id_tipo_prod = e.id
                            INNER JOIN isp.int_estados_equipo f ON a.estado = f.id
                            INNER JOIN isp.int_estados_equipo g ON b.estado = g.id
                            LEFT JOIN isp.int_tipo_servicio h ON d.id_tipo_serv = h.id
                                                        LEFT JOIN isp.int_sistemas i ON a.id_equipo = i.id
                WHERE a.id_contrato = $idContrato AND a.estado not in ('E') AND b.estado not in ('E') GROUP BY 1,2,3,4,5,6";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $nombre             = $oCon->f('nombre');
                    $id_tarjeta         = $oCon->f('id_tarjeta');
                    $ip                 = $oCon->f('ip');
                    $estado             = $oCon->f('estado');
                    $color              = $oCon->f('color');
                    $datos_caja_pack    = $oCon->f('datos_caja_pack');
                    $sistema            = $oCon->f('sistema');
                    $estado_lbl         = '<span class="label bg-' . $color . '" style="font-size:12px">' . $estado . '</span>';
                    $sHtml .= '<tr>
                                    <td class="bg-blue" style="font-size:12px" colspan="2">Tipo</td>
                                    <td class="bg-blue" style="font-size:12px" colspan="2">Serial</td>
                                    <td class="bg-blue" style="font-size:12px">Sistema</td>
                                    <td class="bg-blue" style="font-size:12px">Ip</td>
                                    <td class="bg-blue" style="font-size:12px">Estado</td>
                                </tr>
                                <tr>
                                    <td style="font-size:12px" colspan="2">' . $nombre . '</td>
                                    <td style="font-size:12px" colspan="2">' . $id_tarjeta . '</td>
                                    <td style="font-size:12px">' . $sistema . '</td>
                                    <td style="font-size:12px">' . $ip . '</td>
                                    <td style="font-size:12px">' . $estado_lbl . '</td>
                                </tr>
                                <tr>
                                    <td style="font-size:16px" class="bg-blue" colspan="7" align="center">Planes</td>
                                </tr>
                                <tr>
                                    <td style="font-size:12px" class="bg-blue">Plan</td>
                                    <td style="font-size:12px" class="bg-blue">Tipo</td>
                                    <td style="font-size:12px" class="bg-blue">T. Servicio</td>
                                    <td style="font-size:12px" class="bg-blue">Codigo</td>
                                    <td style="font-size:12px" class="bg-blue">CID</td>
                                    <td style="font-size:12px" class="bg-blue">Precio</td>
                                    <td style="font-size:12px" class="bg-blue">Estado</td>
                                </tr>';

                    $datos_caja_pack    = explode("%&", $datos_caja_pack);

                    if (count($datos_caja_pack) > 0) {
                        for ($c = 0; $c < count($datos_caja_pack); $c++) {
                            $caja_pack_indi = explode("#$", $datos_caja_pack[$c]);

                            $plan       = $caja_pack_indi[0];
                            $t_plan     = $caja_pack_indi[1];
                            $c_plan     = $caja_pack_indi[2];
                            $p_plan     = $caja_pack_indi[3];
                            $e_plan     = $caja_pack_indi[4];
                            $e_txt_plan = $caja_pack_indi[5];
                            $e_col_plan = $caja_pack_indi[6];
                            $tipo_ser   = $caja_pack_indi[7];
                            $cid        = $caja_pack_indi[8];
                            $estado_lbl_i         = '<span class="label bg-' . $e_col_plan . '" style="font-size:12px">' . $e_txt_plan . '</span>';

                            $sHtml .= '<tr>
                                            <td style="font-size:12px">' . $plan . '</td>
                                            <td style="font-size:12px">' . $t_plan . '</td>
                                            <td style="font-size:12px">' . $tipo_ser . '</td>
                                            <td style="font-size:12px">' . $c_plan . '</td>
                                            <td style="font-size:12px">' . $cid . '</td>
                                            <td style="font-size:12px">' . $p_plan . '</td>
                                            <td style="font-size:12px">' . $estado_lbl_i . '</td>
                                        </tr>';
                        }
                    } else {
                        $sHtml .= '<tr>
                                        <td style="font-size:12px" colspan="5">Sin datos...</td>
                                    </tr>';
                    }
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sHtml .= '</tbody></table>';


        return $sHtml;
    }

    function detalleContrato($op)
    {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oCon = $this->oCon;
        $oIfx = $this->oIfx;
        $idEmpresa = $this->idEmpresa;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;
        $mesHoy = date("m");
        $anio_actual = date("Y");

        $sHtml_encabezado   = $this->htmlEncabezadoContrato();
        $sHtml_servicios    = $this->htmlServiciosContrato();
        //query contrato
        $array_p = array();
        $sql = "SELECT id, fecha, secuencial, estado, dias,
                estado_fact, id_factura, estado, mes, anio,
                tarifa, abono, valor_pago, can_add, pre_add,
                tot_add, valor_uso, valor_no_uso, dias_uso,
                dias_no_uso, descuento, tipo, detalle
                from isp.contrato_pago
                where id_clpv = $idClpv and
                id_contrato = $idContrato order by tipo, fecha";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $array_p[] = array(
                        $oCon->f('id'), $oCon->f('fecha'), $oCon->f('secuencial'), $oCon->f('estado'), $oCon->f('dias'),
                        $oCon->f('estado_fact'), $oCon->f('id_factura'), $oCon->f('estado'), $oCon->f('mes'), $oCon->f('tarifa'),
                        $oCon->f('abono'), $oCon->f('valor_pago'), $oCon->f('anio'), $oCon->f('can_add'), $oCon->f('pre_add'),
                        $oCon->f('tot_add'), $oCon->f('valor_no_uso'), $oCon->f('dias_uso'), $oCon->f('dias_no_uso'), $oCon->f('descuento'), $oCon->f('tipo'), $oCon->f('detalle')
                    );
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sHtml_facturas = '';

        if (count($array_p) > 0) {

            $sHtml_facturas .= '<div class="table" id="divFormularioCobros" style="height: 264px; overflow-y: scroll;"><div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%;" align="center">
                                    <thead>
                                        <tr>
                                            <td class="bg-blue" style="font-size:12px">No.</td>
                                            <td class="bg-blue" style="font-size:12px">PERIODO</td>
                                            <td class="bg-blue" style="font-size:12px">FECHA</td>
                                            <td class="bg-blue" style="font-size:12px">ESTADO</td>
                                            <td class="bg-blue" style="font-size:12px">DIAS</td>
                                            <td class="bg-blue" style="font-size:12px">DIAS USO</td>
                                            <td class="bg-blue" style="font-size:12px">TARIFA</td>
                                            <td class="bg-blue" style="font-size:12px">VALOR USO</td>
                                            <td class="bg-blue" style="font-size:12px">PAGOS</td>
                                            <td class="bg-blue" style="font-size:12px">DSCTO</td>
                                            <td class="bg-blue" style="font-size:12px">SALDO</td>
                                            <td class="bg-blue" style="font-size:12px">FACTURA</td>
                                            <td class="bg-blue" style="font-size:12px">IMPRESION</td>
                                            <td class="bg-blue" style="font-size:12px">CORREO</td>
                                        </tr>
                                    </thead>
                                    <tbody>';

            foreach ($array_p as $val) {
                $idDet = $val[0];
                $fechaPago = $val[1];
                $secuencial = $val[2];
                $estado = $val[3];
                $dias = $val[4];
                $estado_fact = $val[5];
                $id_factura = $val[6];
                $estado = $val[7];
                $mes = $val[8];
                $tarifa = $val[9];
                $abono = $val[10];
                $valor_pago = $val[11];
                $anio = $val[12];
                $can_add = $val[13];
                $pre_add = $val[14];
                $tot_add = $val[15];
                $valor_no_uso = $val[16];
                $dias_uso = $val[17];
                $dias_no_uso = $val[18];
                $descuento = $val[19];
                $tipo = $val[20];
                $detalle = $val[21];

                $tarifaOk = $tarifa + $tot_add;
                $valorReal = $tarifaOk - $valor_no_uso;

                $eventoFact = '';
                $totalFactura = 0;
                $classEstado = 'info';
                $estadopago = "PENDIENTE";
                if ($estado_fact != 'GR') {
                    if ($mesHoy > $mes && $anio == $anio_actual) {
                        $classEstado = 'danger';
                        $estadopago = "EN MORA";
                    } else {
                        $classEstado = 'info';
                        $estadopago = "PENDIENTE";
                    }
                } elseif ($estado_fact == 'GR') {
                    $classEstado = 'success';
                    $estadopago = "FACTURADO";
                }

                $totalCobros = $abono + $valor_pago;

                $saldoMes = round($valorReal - $totalCobros + $descuento, 2);

                //pago
                $sHtmlFactura = '';
                $sql = "select fact_cod_fact, fact_num_preimp, fact_fech_fact, fact_clav_sri
                        from saefact f, saedfac d
                        where fact_cod_empr = dfac_cod_empr and
                        fact_cod_sucu = dfac_cod_sucu and
                        fact_cod_clpv = dfac_cod_clpv and
                        fact_cod_fact = dfac_cod_fact and
                        fact_cod_empr = $idEmpresa and
                        fact_cod_clpv = $idClpv and
                        dfac_cod_mes = $idDet and
                        fact_cod_contr = $idContrato and
                        fact_est_fact != 'AN'
                        group by 1,2,3,4";
                if ($oIfx->Query($sql)) {
                    if ($oIfx->NumFilas() > 0) {
                        $sHtmlFactura = '';
                        do {
                            $fact_cod_fact = $oIfx->f('fact_cod_fact');
                            $fact_num_preimp = $oIfx->f('fact_num_preimp');
                            $fact_fech_fact = $oIfx->f('fact_fech_fact');
                            $fact_clav_sri = $oIfx->f('fact_clav_sri');

                            $sHtmlFactura .= '<a href="#" title="' . $fact_fech_fact . '" onclick="javascript:genera_documento(' . $fact_cod_fact . ',\'' . $fact_clav_sri . '\')">' . $fact_num_preimp . '</a>, ';
                        } while ($oIfx->SiguienteRegistro());
                    }
                }
                $oIfx->Free();

                $periodo = strtoupper(Mes_func($mes)) . ' / ' . $anio;
                if($tipo == 'A'){
                    $periodo = $detalle;
                }

                $sHtml_facturas .= '<tr>
                                        <td>' . $secuencial . '</td>
                                        <td>' . $periodo . '</td>
                                        <td>' . fecha_mysql_dmy($fechaPago) . '</td>
                                        <td align="center" class="' . $classEstado . '">' . $estado_fact . '</td>
                                        <td align="right">' . $dias . '</td>
                                        <td align="right">' . $dias_uso . '</td>
                                        <td align="right">' . number_format($tarifaOk, 2, '.', ',') . '</td>
                                        <td align="right">' . number_format($valorReal, 2, '.', ',') . '</td>
                                        <td align="right"><a href="#">' . number_format($totalCobros, 2, '.', ',') . '</a></td>
                                        <td align="right"><a href="#">' . number_format($descuento, 2, '.', ',') . '</a></td>
                                        <td align="right">' . number_format($saldoMes, 2, '.', ',') . '</td>
                                        <td align="left">' . $sHtmlFactura . '</td>
                                        <td align="right">
                                            <div class="btn btn-warning btn-sm"
                                                onclick="javascript:genera_pdf_esta_cta(' . $idEmpresa . ', \'' . $idClpv . '\', \'' . $idContrato . '\', \'' . $idDet . '\' );">
                                                <span class="glyphicon glyphicon-print"></span>                                           
                                            </div>	
                                    </td>
                                    <td align="right">
                                            <div class="btn btn-success btn-sm"
                                                onclick="javascript:genera_correo_int(' . $idEmpresa . ', \'' . $idClpv . '\', \'' . $idContrato . '\', \'' . $idDet . '\' );">
                                                <span class="glyphicon glyphicon-envelope"></span>                                           
                                            </div>	
                                    </td>
                                    </tr>';
            }
        }
        $sHtml_facturas .= '</tbody>
                        </table> 
                    </div></div>';

        $sHtml = ' <div class="modal-dialog modal-lg" role="document" style="width:98%;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="myModalLabel">
                                    Contrato Cliente
                                    <div class="btn btn-primary btn" onclick="cxc_det(' . $idClpv . ', ' . $idContrato . ' );">
                                        <span class=" glyphicon glyphicon-list"></span>
                                        Detallado
                                    </div>    
                                    <div class="btn btn-danger btn" onclick="estado_cuenta_pdf(' . $idClpv . ', ' . $idContrato . ' ,0);">
                                        <span class=" glyphicon glyphicon-print"></span>
                                        Imprimir
                                    </div>
                                    
                                    <div class="btn btn-success btn" onclick="estado_cuenta_pdf(' . $idClpv . ', ' . $idContrato . ',1 );">
                                        <span class=" glyphicon glyphicon-download"></span>
                                        Excel
                                    </div>
                                </h4>
                            </div>
                            <div class="modal-body" style="margin-top:0xp;">';

        $sHtml .= $sHtml_encabezado;
        $sHtml .= $sHtml_servicios;
        $sHtml .= $sHtml_facturas;

        $sHtml .= ' </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>';




        return $sHtml;
    }

    function registraNotasContratos($userWeb, $prioridad, $titulo, $msj, $adjunto, $usuarios, $fecha)
    {

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $fechaServer = date("Y-m-d H:i:s");

        $sql = "insert into isp.int_notas(id_empresa, id_sucursal, id_clpv, id_contrato, prioridad, 
                                    titulo, nota, adjunto, estado, fecha, user_web, fecha_server)
                            values($idEmpresa, $idSucursal, $idClpv, $idContrato, '$prioridad',
                                    '$titulo', '$msj', '$adjunto', 'A', '$fecha', $userWeb, '$fechaServer')";
        $oCon->QueryT($sql);

        if (count($usuarios) > 0) {
            $sql = "select max(id) as maximo from isp.int_notas
					where id_empresa = $idEmpresa and
					id_sucursal = $idSucursal and
					id_clpv = $idClpv and
					id_contrato = $idContrato";
            $idmaximo = consulta_string_func($sql, 'maximo', $oCon, 0);

            for ($i = 0; $i < count($usuarios); $i++) {
                $user_recibe = $usuarios[$i];
                $sql = "insert into isp.int_notas_user(id_nota, user_envia, user_recibe) values($idmaximo, $userWeb, $user_recibe)";
                $oCon->QueryT($sql);
            }
        }

        return 'OK';
    }

    function reporteNotasContratos()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $sHtml = '';

        $sql = "select usuario_id, concat(usuario_nombre, ' ', usuario_apellido) as user
                from comercial.usuario
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

        //contrato
        $sql = "select codigo, nom_clpv
                from isp.contrato_clpv 
                where id_empresa = $idEmpresa and
                id_clpv = $idClpv and
                id = $idContrato";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $codigo = $oCon->f('codigo');
                    $nom_clpv = $oCon->f('nom_clpv');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sHtml .= '<div class="alert alert-warning alert-dismissible" role="alert">
					<strong>' . $codigo . ', </strong>' . $nom_clpv . '
				</div>';

        $sHtml .= '<table class="table table-bordered table-striped table-condensed table-hover" style="width: 99%; margin-top: 10px;" align="center">';
        //query clpv
        $sql = "select id, titulo, nota, estado, fecha, user_web, fecha_server
                from isp.int_notas 
                where
                id_empresa = $idEmpresa and
                id_sucursal = $idSucursal and
                id_clpv = $idClpv and
                id_contrato = $idContrato";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $sHtml .= '<tr>';
                $sHtml .= '<td>Fecha</td>';
                $sHtml .= '<td>Titulo</td>';
                $sHtml .= '<td>Nota</td>';
                $sHtml .= '<td>User</td>';
                $sHtml .= '</tr>';
                do {
                    $id = $oCon->f('id');
                    $titulo = $oCon->f('titulo');
                    $nota = $oCon->f('nota');
                    $estado = $oCon->f('estado');
                    $fecha = $oCon->f('fecha');
                    $user_web = $oCon->f('user_web');
                    $fecha_server = $oCon->f('fecha_server');

                    $sHtml .= '<tr>';
                    $sHtml .= '<td align="left">' . fecha_mysql_dmy($fecha) . '</td>';
                    $sHtml .= '<td align="left">' . $titulo . '</td>';
                    $sHtml .= '<td align="left">' . $nota . '</td>';
                    $sHtml .= '<td align="left">' . $arrayUser[$user_web] . '</td>';
                    $sHtml .= '</tr>';
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sHtml .= '</table>';

        return $sHtml;
    }

    function registraDireccionContrato($dire_dir_dire, $tipo_direccion, $tipo_casa, $sectorDire, $barrioDire, $callePrincipal, $calleSecundaria, $numeroDire, $edificioDire, $referenciaDire, $antiguedadDire, $latitud, $longuitud)
    {

        $oIfx = $this->oIfx;
        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $usuario_web = $_SESSION['U_ID'];
        $fechaServer = date("Y-m-d H:i:s");

        $sqlSucursal = "select clpv_cod_sucu from saeclpv where clpv_cod_clpv = $idClpv";
        $sucursal = consulta_string_func($sqlSucursal, 'clpv_cod_sucu', $oIfx, 0);

        //inserta direccion
        $sqlDire = "insert into saedire(dire_cod_empr, dire_cod_sucu, dire_cod_clpv, dire_dir_dire,
                                        dire_cod_tipo, dire_cod_vivi, dire_cod_sect,
                                        dire_barr_dire, dire_call1_dire, dire_call2_dire,
                                        dire_nume_dire, dire_edif_dire, dire_refe_dire,
                                        dire_anti_dire, dire_cod_contr, dire_lat_dire,
                                        dire_lon_dire)
                                        values($idEmpresa, $sucursal, $idClpv, '$dire_dir_dire',
                                        $tipo_direccion, $tipo_casa, $sectorDire,
                                        '$barrioDire', '$callePrincipal', '$calleSecundaria',
                                        '$numeroDire', '$edificioDire', '$referenciaDire',
                                        $antiguedadDire, $idContrato, '$latitud',
                                        '$longuitud')";
        $oIfx->QueryT($sqlDire);

        //control inserta datos
        $sqlCtrl = "insert into isp.control_clpv(id_empresa, id_clpv, tipo, opcion, user_web, fecha_server) 
                    values($idEmpresa, $idClpv, 'D', 1, $usuario_web, '$fechaServer')";
        $oCon->QueryT($sqlCtrl);

        //direccion
        $sql = "select dire_cod_dire from saedire where dire_cod_empr = $idEmpresa and dire_cod_clpv = $idClpv and dire_cod_contr = $idContrato";
        $idDireccion = consulta_string_func($sql, 'dire_cod_dire', $oIfx, 0);

        //UPDATE contrato
        $sql = "UPDATE isp.contrato_clpv set id_dire = $idDireccion,
                                        id_sector = '$sectorDire',
                                        id_barrio = '$barrioDire',
                                        direccion = '$dire_dir_dire',
                                        referencia = '$referenciaDire',
                                        latitud = '$latitud',
                                        longitud = '$longuitud'
                                        where id_empresa = $idEmpresa and
                                        id = $idContrato and
                                        id_clpv = $idClpv";
        $oCon->QueryT($sql);

        $this->registraAuditoriaContratos(5, $usuario_web, $dire_dir_dire);

        return 'OK';
    }

    function registraHistorialBusqueda($idModulo, $codigo, $abonado, $nom_clpv, $apodo, $userWeb)
    {

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $fecha = date("Y-m-d");
        $fechaServer = date("Y-m-d H:i:s");

        $sql = "insert into isp.int_tmp_buscar(id_empresa, id_sucursal, id_modulo, id_clpv, id_contrato, codigo, abonado, nom_clpv, apodo, fecha, id_user, fecha_server)
                                    values($idEmpresa, $idSucursal, $idModulo, $idClpv, $idContrato, '$codigo', '$abonado', '$nom_clpv', '$apodo', '$fecha', $userWeb, '$fechaServer')";
        $oCon->QueryT($sql);

        return 'OK';
    }

    function registraReferencia($referenciaNombre, $referenciaIden, $referenciaParent, $referenciaTelefono, $userWeb)
    {

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $fechaServer = date("Y-m-d H:i:s");

        if (empty($referenciaParent)) {
            $referenciaParent = 'null';
        }

        $sql = "insert into isp.contrato_referencia(id_empresa, id_sucursal, id_clpv, id_contrato, nombre, ruc,
                                                id_parent, telefono, estado, user_web, fecha_server)
                                        values($idEmpresa, $idSucursal, $idClpv, $idContrato, '$referenciaNombre', '$referenciaIden',
                                                $referenciaParent, '$referenciaTelefono', 'A', $userWeb, '$fechaServer')";
        $oCon->QueryT($sql);

        $this->registraAuditoriaContratos(6, $userWeb, $referenciaNombre);

        return 'OK';
    }

    function modificaReferencia($idReferencia, $referenciaNombre, $referenciaIden, $referenciaParent, $referenciaTelefono)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oCon = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        //variables de session
        $idUser = $_SESSION['U_ID'];

        if (empty($referenciaParent)) {
            $referenciaParent = 'null';
        }

        $sql = "UPDATE isp.contrato_referencia set nombre = '$referenciaNombre', 
                                                ruc = '$referenciaIden',
                                                id_parent = $referenciaParent, 
                                                telefono = '$referenciaTelefono'
                                                where id = $idReferencia and
                                                id_clpv = $idClpv and
                                                id_contrato = $idContrato";
        $oCon->QueryT($sql);

        $this->registraAuditoriaContratos(7, $idUser, $referenciaNombre);

        return 'OK';
    }

    function reporteFacturasCuotas($id)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oIfx = $this->oIfx;
        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        global $DSN_Ifx, $DSN;

        $oIfx2 = new Dbo;
        $oIfx2->DSN = $DSN_Ifx;
        $oIfx2->Conectar();

        $idsucursal = $_SESSION['U_SUCURSAL'];
        $id_usuario = $_SESSION['U_ID'];

        //VERIFICAR SI ES FACTURA PREIMPRESA
        $sql = "SELECT sucu_fac_elec from saesucu WHERE sucu_cod_sucu = $idsucursal";
        $oCon->Query($sql);
        $sucu_fact_elec = $oCon->f('sucu_fac_elec');
        $oCon->Free();

        $sql = "SELECT reimpresion_sn from comercial.usuario WHERE usuario_id = $id_usuario";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $reimpresion_sn = $oCon->f('reimpresion_sn');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sql = "SELECT schema_name as validacion_esquema
                FROM information_schema.schemata
                WHERE schema_name = 'portal_isp';";
        $validacion_esquema = consulta_string_func($sql, 'validacion_esquema', $oCon, 0);

        $sql = "select usuario_id, concat(usuario_nombre, ' ', usuario_apellido) as user from comercial.usuario";
        $array_u = array_dato($oCon, $sql, 'usuario_id', 'user');

        $sHtml = '<div class="table-responsive">';
        $sHtml .= '<table class="table table-bordered table-striped table-condensed table-hover" style="width: 100%; margin-top: 10px;" align="center">';
        $sHtml .= '<tr>';
        $sHtml .= '<td style="width: 5%;">Tipo</td>';
        $sHtml .= '<td style="width: 10%;">Fecha Pago</td>';
        $sHtml .= '<td style="width: 10%;">Fecha</td>';
        $sHtml .= '<td style="width: 15%;">Serie</td>';
        $sHtml .= '<td style="width: 15%;">Preimpreso</td>';
        $sHtml .= '<td style="width: 10%;">Estado</td>';
        $sHtml .= '<td style="width: 15%;">Valor</td>';
        $sHtml .= '<td style="width: 15%;">Documento</td>';
        $sHtml .= '<td style="width: 20%;">Usuario</td>';
        $sHtml .= '<td style="width: 20%;">Forma Pago</td>';
        $sHtml .= '<td style="width: 15%;">Descuento Detalle</td>';
        $sHtml .= '<td style="width: 10%;">Descuento Valor</td>';
        $sHtml .= '<td style="width: 5%;">Imprimir</td>';
        $sHtml .= '</tr>';

        //query clpv
        $sql = "SELECT id_factura, user_web
                from isp.contrato_factura
                WHERE id_pago = $id AND
                id_empresa = $idEmpresa AND
                id_clpv = $idClpv AND
                id_contrato = $idContrato AND
                tipo in ('F', 'FC')
                GROUP BY 1,2";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $totalAbono = 0;
                do {
                    $id_factura = $oCon->f('id_factura');
                    $user_web = $oCon->f('user_web');

                    //consultar factura
                    $sql = "select fact_num_preimp, fact_fech_fact, fact_tip_vent, fact_user_web, fact_cod_sucu, fact_clav_sri, fact_nse_fact,
                            fact_est_fact, fact_cod_asto, fact_cod_ejer, fact_num_prdo, fact_cod_fact,fact_cm4_fact, fact_cm1_fact,
                            (COALESCE(fact_con_miva,0) + COALESCE(fact_sin_miva,0) + fact_iva + fact_val_irbp - fact_dsg_valo + fact_fle_fact + fact_otr_fact + fact_fin_fact) as venta
                            from saefact
                            where fact_cod_empr = $idEmpresa and
                            fact_cod_fact = '$id_factura' and
                            fact_cod_clpv = $idClpv";
                    if ($oIfx->Query($sql)) {
                        if ($oIfx->NumFilas() > 0) {
                            $fact_num_preimp = $oIfx->f('fact_num_preimp');
                            $fact_fech_fact = $oIfx->f('fact_fech_fact');
                            $fact_tip_vent = $oIfx->f('fact_tip_vent');
                            $fact_user_web = $oIfx->f('fact_user_web');
                            $fact_cod_sucu = $oIfx->f('fact_cod_sucu');
                            $fact_clav_sri = $oIfx->f('fact_clav_sri');
                            $fact_est_fact = $oIfx->f('fact_est_fact');
                            $fact_cod_asto = $oIfx->f('fact_cod_asto');
                            $fact_cod_ejer = $oIfx->f('fact_cod_ejer');
                            $fact_num_prdo = $oIfx->f('fact_num_prdo');
                            $fact_nse_fact = $oIfx->f('fact_nse_fact');
                            $fact_cod_fact = $oIfx->f('fact_cod_fact');
                            $venta         = $oIfx->f('venta');
                            $fact_cm1_fact = $oIfx->f('fact_cm1_fact');
                            $fact_cm4_fact = $oIfx->f('fact_cm4_fact');
                        }
                    }
                    $oIfx->Free();

                    $sHtmlFormaPago = '';

                    $sql = "select fxfp_cod_fpag, fxfp_val_fxfp, fpag_des_fpag, fxfp_num_rete
                            from saefxfp, saefpag
                            where fxfp_cod_empr = fpag_cod_empr and
                            fpag_cod_fpag = fxfp_cod_fpag and
                            fxfp_cod_empr = $idEmpresa and
                            fxfp_cod_fact = '$id_factura' ";
                    if ($oIfx->Query($sql)) {
                        if ($oIfx->NumFilas() > 0) {
                            do {
                                $fxfp_val_fxfp = $oIfx->f('fxfp_val_fxfp');
                                $fpag_des_fpag = $oIfx->f('fpag_des_fpag');
                                $fxfp_num_rete = $oIfx->f('fxfp_num_rete');

                                $sHtmlFormaPago .= '<p>' . $fpag_des_fpag . ' - ' . number_format($fxfp_val_fxfp, 2, '.', ',') . '</p>';
                            } while ($oIfx->SiguienteRegistro());
                        }
                    }
                    $oIfx->Free();

                    if(strlen($validacion_esquema) == 10){
                        $sql_portal = "SELECT created_at FROM portal_isp.depositos_contratos WHERE referencia_deposito = '$fxfp_num_rete' AND contrato_id = $idContrato";
                        $fecha_pago = consulta_string_func($sql_portal, 'created_at', $oIfx, 0);
                        if($fecha_pago != 0){
                            $fecha_pago = date("Y-m-d", strtotime($fecha_pago));
                        }else{
                            $sql_portal = "SELECT bapg_num_ctab FROM saebafp WHERE bafp_cod_fact = $id_factura";
                            $fecha_pago = consulta_string_func($sql_portal, 'bapg_num_ctab', $oIfx, 0);

                            if($fecha_pago != 0){
                                $fecha_pago = date("Y-m-d", strtotime($fecha_pago));
                            }
                        }
                    }

                    $fact_clav_sri = generaClaveAccesoSri($oIfx2, '01', $idEmpresa, $fact_cod_sucu, $fact_fech_fact, $fact_nse_fact, $fact_num_preimp);

                    $divAsto = $fact_est_fact;
                    if ($fact_est_fact == 'MY') {
                        $divAsto = '<a href="#" title="Presione Aqui para mirar Diario Contable" onclick="verDiarioContable(' . $idEmpresa . ', ' . $fact_cod_sucu . ', ' . $fact_cod_ejer . ', ' . $fact_num_prdo . ', \'' . $fact_cod_asto . '\');">' . $fact_est_fact . '</a>';
                    }

                    $sHtml .= '<tr>';
                    $sHtml .= '<td align="left" style="width: 5%;">' . $fact_tip_vent . '</td>';
                    $sHtml .= '<td align="left" style="width: 10%;">' . $fecha_pago . '</td>';
                    $sHtml .= '<td align="left" style="width: 10%;">' . $fact_fech_fact . '</td>';
                    $sHtml .= '<td align="left" style="width: 15%;">' . $fact_nse_fact . '</td>';
                    $sHtml .= '<td align="left" style="width: 15%;">' . $fact_num_preimp . '</td>';
                    $sHtml .= '<td align="left" style="width: 10%;">' . $divAsto . '</td>';
                    $sHtml .= '<td align="right" style="width: 15%;">' . number_format($venta, 2, '.', '') . '</td>';
                    $sHtml .= '<td align="left" style="width: 20%;">' . $fxfp_num_rete . '</td>';
                    $sHtml .= '<td align="left" style="width: 20%;">' . $array_u[$fact_user_web] . '</td>';
                    $sHtml .= '<td align="left" style="width: 20%;">' . $sHtmlFormaPago . '</td>';
                    $sHtml .= '<td align="left" style="width: 20%;">' . $fact_cm1_fact . '</td>';
                    $sHtml .= '<td align="left" style="width: 10%;">' . number_format($fact_cm4_fact, 2, '.', ',') . '</td>';
                    if ($fact_est_fact != 'AN' && $reimpresion_sn == 'S') {
                        if ($sucu_fact_elec == "S") {
                            $sHtml .= '<td align="center" style="width: 5%;">
                                            <div class="btn btn-primary btn-sm" onclick="reporte_factura_pdf(' . $id_factura . ')">
                                                <span class="glyphicon glyphicon-print"></span>
                                            </div>';
                            if ($fact_tip_vent == 18) {
                                $campo = 0;
                                $sHtml .= ' <div class="btn btn-success btn-sm" onclick="genera_documento(1, ' . $fact_cod_fact . ', \'' . $fact_clav_sri . '\', ' . $campo . ', ' . $campo . ', ' . $campo . ', ' . $campo . ', ' . $campo . ', ' . $fact_cod_sucu . ');">
                                                <span class="glyphicon glyphicon-print"></span>
                                            </div>';
                            }
                        } else {
                            $sHtml .= '<td align="center" style="width: 5%;">
                                            <div class="btn btn-primary btn-sm" onclick="factura_preimpresa(' . $id_factura . ')">
                                                <span class="glyphicon glyphicon-print"></span>
                                            </div>';
                        }

                        $sHtml .= '     </td>';
                        $sHtml .= '</tr>';
                    } else {
                        $sHtml .= '<td> </td></tr>';
                    }


                    $totalAbono += $venta;
                } while ($oCon->SiguienteRegistro());
                $sHtml .= '<tr>';
                $sHtml .= '<td class="bg-danger fecha_letra" align="right" colspan="6">Total:</td>';
                $sHtml .= '<td class="bg-danger fecha_letra" align="right">' . number_format($totalAbono, 2, '.', '') . '</td>';
                $sHtml .= '<td class="bg-danger fecha_letra" align="right" colspan="6"></td>';
                $sHtml .= '</tr>';
            }
        }
        $oCon->Free();

        $sHtml .= '</table>';
        $sHtml .= '</div>';

        return $sHtml;
    }

    function registraDescuento($fecha, $tarifa, $planes, $descuento_p, $descuento_v, $fecha_vence, $indefinido, $cortesia, $observaciones, $max_cajas, $max_modem, $estado)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        //variables de session
        $idUser = $_SESSION['U_ID'];

        $fecha_server = date("Y-m-d H:i:s");
        $id = null;

        if (empty($indefinido)) {
            $indefinido = 'N';
        }

        $tarifa_sn = 'S';
        if (empty($estado)) {
            $estado = 'I';
            $tarifa_sn = 'N';
        }

        $sql = "insert into isp.contrato_descuentos (id_empresa, id_sucursal, id_contrato, id_clpv, 
												fecha, tarifa, planes, descuento_p, descuento_v, indefinido,
												cortesia, observaciones, estado, max_cajas, max_modem, userWeb, fecha_server)
											VALUES($idEmpresa, $idSucursal, $idContrato, $idClpv,
												'$fecha', $tarifa, $planes, $descuento_p, $descuento_v, '$indefinido',
												'$cortesia', '$observaciones', '$estado', $max_cajas, $max_modem, $idUser, '$fecha_server')";
        if ($oCon->QueryT($sql)) {

            $sql = "select max(id) as id 
					from isp.contrato_descuentos
					where id_empresa = $idEmpresa and
					id_sucursal = $idSucursal and
					id_clpv = $idClpv and
					id_contrato = $idContrato";
            $id = consulta_string_func($sql, 'id', $oCon, 0);

            $sql_c = "UPDATE isp.contrato_clpv set tarifa_e = '$tarifa_sn'";
            if ($descuento_p >= 100) {
                $sql_c .= " , estado = 'CR'";
                $oCon->QueryT($sql);
            }
            $sql_c .= " where id = $idContrato and id_clpv = $idClpv";
            $oCon->QueryT($sql_c);

            $observaciones = 'DSCTO: ' . $descuento_p . ' % / $ ' . $descuento_v;

            $this->registraAuditoriaContratos(12, $idUser, $observaciones);
        };

        return $id;
    }

    function modificaDescuento($id, $fecha, $tarifa, $planes, $descuento_p, $descuento_v, $fecha_vence, $indefinido, $cortesia, $observaciones, $max_cajas, $max_modem, $estado)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        //variables de session
        $idUser = $_SESSION['U_ID'];

        $tarifa_sn = 'S';
        if (empty($estado)) {
            $estado = 'I';
            $tarifa_sn = 'N';
        }

        //query cuotas
        $filtro_cuota = "";
        $filtro_cuota_pack = "";
        if (!empty($fecha)) {
            $array_fecha = explode("-", $fecha);
            $anio = $array_fecha[0];
            $mes = $array_fecha[1];

            $sql = "SELECT id 
                    from isp.contrato_pago 
                    WHERE id_contrato = $idContrato AND 
                    EXTRACT (YEAR FROM fecha) = $anio AND
                    EXTRACT (MONTH FROM fecha) = $mes AND
                    tipo = 'P'";
            $idCuotaPago = consulta_string_func($sql, 'id', $oCon, null);

            if (!empty($idCuotaPago)) {
                $filtro_cuota = " AND id >= $idCuotaPago";
                $filtro_cuota_pack = " AND id_pago >= $idCuotaPago";
            }
        }

        $sql = "UPDATE isp.contrato_descuentos set fecha = '$fecha', 
											tarifa = $tarifa, 
											planes = $planes, 
											descuento_p = $descuento_p, 
											descuento_v = $descuento_v, 
											indefinido = '$indefinido',
											cortesia = '$cortesia', 
											observaciones = '$observaciones', 
											max_cajas = $max_cajas,
											max_modem = $max_modem,
                                            estado = '$estado'
											where id = $id";
        $oCon->QueryT($sql);

        //id descuento
        $sql = "select max(id) as id 
					from isp.contrato_descuentos
					where id_empresa = $idEmpresa and
					id_sucursal = $idSucursal and
					id_clpv = $idClpv and
					id_contrato = $idContrato";
        //$id = consulta_string_func($sql, 'id', $oCon, 0);

        $sql_c = "UPDATE isp.contrato_clpv SET tarifa_e = '$tarifa_sn', descuento_p = '$descuento_p', descuento_v = '$descuento_v'";
        if ($descuento_p >= 100) {
            $sql_c .= ", estado = 'CR'";
        } else {

            //query exitencia de tabla de pagos
            $sql = "SELECT COUNT(*) as control from isp.contrato_pago WHERE id_contrato = $idContrato AND fecha >= NOW();";
            $control = consulta_string_func($sql, 'control', $oCon, 0);

            if ($control == 0) {
                //en caso de no existir la tabla de pagos genera las cuotas
                $Equipos = new Equipos($oCon, null, $idEmpresa, $idSucursal, $idClpv, $idContrato, null);
                $Equipos->registraCuotaPaqueteCaja(0);
            }

            //control estado de contrato
            $sql = "SELECT estado from isp.contrato_clpv WHERE id = $idContrato";
            $estado = consulta_string_func($sql, 'estado', $oCon, '');

            if ($estado == 'CR') {
                $sql_c .= ", estado = 'AP'";
            }
        }
        $sql_c .= " WHERE id = $idContrato and id_clpv = $idClpv";
        $oCon->QueryT($sql_c);

        //afecta mensualidades
        if ($descuento_v < 0) {

            //query config prod tarifa especial
            $sql = "SELECT prod_cod_tarifa FROM tran_inv_muest WHERE empr_cod_empr = $idEmpresa AND sucu_cod_sucu = $idSucursal";
            $prod_cod_tarifa = consulta_string_func($sql, 'prod_cod_tarifa', $oCon, '');

            if (!empty($prod_cod_tarifa)) {
                $sql = "SELECT id from isp.int_paquetes WHERE prod_cod_prod = '$prod_cod_tarifa'";
                $id_prod = consulta_string_func($sql, 'id', $oCon, '');
            }

            $array_cuotas = $this->consultarTablaPagosContrato($idCuotaPago);

            if (count($array_cuotas) > 0) {

                $Equipos = new Equipos($oCon, null, $idEmpresa, $idSucursal, $idClpv, $idContrato, null);

                foreach ($array_cuotas as $val) {
                    $idCuota = $val[0];
                    $fechaCuota = $val[2];
                    $estado = $val[6];
                    $estado_fact = $val[7];
                    $valor_pago = $val[10];
                    $tipo = $val[11];

                    //descuento ok
                    $descuento_ok = abs($descuento_v);

                    if ($estado == 'PE' && $tipo == 'P') {
                        if ($valor_pago == 0) {

                            $sql = "SELECT id from isp.contrato_pago_pack WHERE id_contrato = $idContrato AND id_pago = $idCuota AND id_prod = $id_prod";
                            $idPack = consulta_string_func($sql, 'id', $oCon, 0);

                            if ($idPack > 0) {

                                $sql = "UPDATE isp.contrato_pago_pack SET tarifa = '$descuento_ok' WHERE id = $idPack";
                                $oCon->QueryT($sql);

                                $sql = "UPDATE isp.contrato_pago c SET c.tarifa = (SELECT SUM(p.tarifa)
                                        from isp.contrato_pago_pack p
                                        WHERE c.id_contrato = p.id_contrato AND
                                        c.id_clpv = p.id_clpv AND
                                        p.id_pago = c.id AND
                                        p.estado = 'A' and
                                        p.estado_fact is null)
                                        WHERE c.id_contrato = $idContrato AND
                                        c.id_clpv = $idClpv AND
                                        c.id = $idCuota";
                                $oCon->QueryT($sql);
                            } else {
                                $Equipos->registraTablaPagoPack(null, null, $idCuota, $id_prod, $prod_cod_tarifa, $fechaCuota, $descuento_ok, 0, 0, 0, 1, 0, 'A', '', 'S', 'A');
                            }
                        } //if empty estado fact
                    } //if estado PE tipo P
                } //fin foreach
            } //if count cuotas

            $sqlUPDATEPago = "UPDATE isp.contrato_pago SET 
                                descuento = 0
                                WHERE id_clpv = $idClpv AND
                                id_contrato = $idContrato AND
                                estado = 'PE' AND
                                tipo = 'P' AND
                                (estado_fact is null OR estado_fact != 'GR')
                                $filtro_cuota";
            $oCon->QueryT($sqlUPDATEPago);
        } else { //descuento mayor a cero

            $this->recalcularTarifaCuotas('S', $idCuotaPago);
        }

        $observaciones = 'DSCTO: ' . $descuento_p . '% / ' . $descuento_v . '$';

        $this->registraAuditoriaContratos(13, $idUser, $observaciones);


        return $id;
    }

    function consultarDescuento()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $array = array();

        $sql = "select * from isp.contrato_descuentos where id_empresa = $idEmpresa and id_contrato = $idContrato";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {

                $array[] = array(
                    $oCon->f('id'), $oCon->f('fecha'), $oCon->f('tarifa'), $oCon->f('planes'), $oCon->f('descuento_p'), $oCon->f('descuento_v'),
                    $oCon->f('fecha_vence'), $oCon->f('indefinido'), $oCon->f('cortesia'), $oCon->f('observaciones'), $oCon->f('estado'),
                    $oCon->f('max_cajas'), $oCon->f('max_modem'), $oCon->f('userWeb'), $oCon->f('fecha_server')
                );
            }
        }
        $oCon->Free();

        return $array;
    }

    function calculaPlanesCuotaRespaldo($idPago)
    {

        $oCon = $this->oCon;
        $oCon1 = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        global $DSN, $DSN_Ifx;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oConA = new Dbo();
        $oConA->DSN = $DSN;
        $oConA->Conectar();

        $array = array();

        unset($array);

        //consulta planes relacionados con equipos
        $sql = "SELECT p.cod_prod, p.id_prod, p.id,
                count(*) AS cantidad,
				sum(p.valor_pago) AS valor_pago, 
				sum(p.valor_nc) AS valor_nc, 
				sum(p.descuento) AS descuento, 
				sum(p.tarifa) AS tarifa,
				sum(p.valor_uso) AS valor_uso, 
				sum(p.valor_no_uso) AS valor_no_uso,
				sum(p.tarifa - p.valor_no_uso - p.valor_pago + p.valor_nc + p.descuento) as saldo
				from isp.contrato_pago_pack p, isp.int_contrato_caja_pack i
				WHERE 
                p.id_contrato = i.id_contrato AND
                p.id_caja = i.id_caja AND
                p.id_pack = i.id AND
				p.id_empresa = $idEmpresa AND 
				p.id_clpv = $idClpv AND 
				p.id_contrato = $idContrato AND 
				p.id_pago = $idPago AND 
                p.tarifa > 0 AND
                i.estado = (SELECT e.id
                from isp.int_estados_equipo e 
                where e.id = i.estado and
                e.vigente = 'S')
                GROUP BY p.tarifa ORDER BY p.tarifa desc";

        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $cod_prod = $oCon->f('cod_prod');
                    $id_prod = $oCon->f('id_prod');

                    $valor_nc = $oCon->f('valor_nc');
                    $descuento = $oCon->f('descuento');
                    $valor_pago = $oCon->f('valor_pago');
                    $tarifa = $oCon->f('tarifa');
                    $valor_uso = $oCon->f('valor_uso');
                    $valor_no_uso = $oCon->f('valor_no_uso');
                    $saldo = $oCon->f('saldo');
                    $cantidad = $oCon->f('cantidad');

                    $array[] = array($cod_prod, $valor_pago, $tarifa, $valor_uso, $valor_no_uso, $saldo, $id_prod, $cantidad, $valor_nc, $descuento);
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();


        //consulta cagos adicionales
        $sql = "SELECT p.cod_prod, p.id_prod, p.id,
                count(*) AS cantidad,
				sum(p.valor_pago) AS valor_pago, 
				sum(p.valor_nc) AS valor_nc, 
       			sum(p.descuento) AS descuento, 
				sum(p.tarifa) AS tarifa,
				sum(p.valor_uso) AS valor_uso, 
				sum(p.valor_no_uso) AS valor_no_uso,
				sum(p.tarifa - p.valor_no_uso - p.valor_pago + p.valor_nc + p.descuento) as saldo
				from isp.contrato_pago_pack p
				WHERE 
				p.id_empresa = $idEmpresa AND 
				p.id_clpv = $idClpv AND 
				p.id_contrato = $idContrato AND 
				p.id_pago = $idPago AND 
				p.estado = 'A' AND
                p.tipo = 'A' AND
                p.tarifa > 0 AND
                p.id_caja is null
                GROUP BY p.tarifa ORDER BY p.tarifa desc";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $cod_prod = $oCon->f('cod_prod');
                    $id_prod = $oCon->f('id_prod');
                    $valor_pago = $oCon->f('valor_pago');
                    $valor_nc = $oCon->f('valor_nc');
                    $descuento = $oCon->f('descuento');
                    $tarifa = $oCon->f('tarifa');
                    $valor_uso = $oCon->f('valor_uso');
                    $valor_no_uso = $oCon->f('valor_no_uso');
                    $saldo = $oCon->f('saldo');
                    $cantidad = $oCon->f('cantidad');

                    $array[] = array($cod_prod, $valor_pago, $tarifa, $valor_uso, $valor_no_uso, $saldo, $id_prod, $cantidad, $valor_nc, $descuento);
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        return $array;
    }

    function calculaPlanesCuota($idPago)
    {

        $oCon = $this->oCon;
        $oCon1 = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        global $DSN, $DSN_Ifx;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oConA = new Dbo();
        $oConA->DSN = $DSN;
        $oConA->Conectar();

        $array = array();

        unset($array);

        //consulta planes relacionados con equipos
        /* $sql = "SELECT p.cod_prod, p.id_prod, p.id, i.estado
				from isp.contrato_pago_pack p, isp.int_contrato_caja_pack i
				WHERE 
                p.id_contrato = i.id_contrato AND
                p.id_caja = i.id_caja AND
                p.id_pack = i.id AND
				p.id_empresa = $idEmpresa AND 
				p.id_clpv = $idClpv AND 
				p.id_contrato = $idContrato AND 
				p.id_pago = $idPago AND 
                p.tarifa > 0 AND
                i.estado = (SELECT e.id
                from isp.int_estados_equipo e 
                where e.id = i.estado ) ORDER BY p.tarifa desc"; */
        $sql = "SELECT 
                    p.cod_prod, 
                    p.id_prod, 
                    p.id, 
                    i.estado
                FROM 
                    isp.contrato_pago_pack p
                LEFT JOIN 
                    isp.int_contrato_caja_pack i ON p.id_contrato = i.id_contrato 
                                                AND p.id_caja = i.id_caja 
                                                AND p.id_pack = i.id
                WHERE 
                    p.id_empresa = $idEmpresa 
                    AND p.id_clpv = $idClpv 
                    AND p.id_contrato = $idContrato 
                    AND p.id_pago = $idPago
                    AND p.tipo != 'A'
                ORDER BY 
                    p.tarifa DESC";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $cod_prod       = $oCon->f('cod_prod');
                    $id_prod        = $oCon->f('id_prod');
                    $id             = $oCon->f('id');
                    $estado_plan    = $oCon->f('estado');

                    $sqlCantidad = "SELECT COUNT(id) as cantidad from isp.contrato_pago_pack WHERE id = $id";
                    $oConA->Query($sqlCantidad);
                    $cantidad = $oConA->f('cantidad');
                    $oConA->Free();

                    $sqlSaldos =  "SELECT sum(p.valor_pago) AS valor_pago, 
                                            sum(p.valor_nc) AS valor_nc, 
                                            sum(p.descuento) AS descuento, 
                                            sum(p.tarifa) AS tarifa,
                                            sum(p.valor_uso) AS valor_uso, 
                                            sum(p.valor_no_uso) AS valor_no_uso,
                                            sum(p.tarifa - p.valor_no_uso - p.valor_pago + p.valor_nc + p.descuento) as saldo
                                    from isp.contrato_pago_pack p WHERE p.id = $id";
                    $oConA->Query($sqlSaldos);
                    $valor_pago     = $oConA->f('valor_pago');
                    $valor_nc       = $oConA->f('valor_nc');
                    $descuento      = $oConA->f('descuento');
                    $tarifa         = $oConA->f('tarifa');
                    $valor_uso      = $oConA->f('valor_uso');
                    $valor_no_uso   = $oConA->f('valor_no_uso');
                    $saldo          = $oConA->f('saldo');
                    $oConA->Free();

                    $array[] = array($cod_prod, $valor_pago, $tarifa, $valor_uso, $valor_no_uso, $saldo, $id_prod, $cantidad, $valor_nc, $descuento, $estado_plan, $id, '', 'P');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        //consulta cagos adicionales
        $sql = "SELECT p.cod_prod, p.id_prod, p.id, p.estado, p.detalle, p.tipo
				from isp.contrato_pago_pack p
				WHERE 
				p.id_empresa = $idEmpresa AND 
				p.id_clpv = $idClpv AND 
				p.id_contrato = $idContrato AND 
				p.id_pago = $idPago AND 
                p.tipo = 'A' 
                ORDER BY p.tarifa desc";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $cod_prod       = $oCon->f('cod_prod');
                    $id_prod        = $oCon->f('id_prod');
                    $id             = $oCon->f('id');
                    $estado_plan    = $oCon->f('estado');
                    $detalle        = $oCon->f('detalle');
                    $tipo           = $oCon->f('tipo');

                    $sqlCantidad = "SELECT COUNT(id) as cantidad from isp.contrato_pago_pack WHERE id = $id";
                    $oConA->Query($sqlCantidad);
                    $cantidad = $oConA->f('cantidad');
                    $oConA->Free();

                    $sqlSaldos =  "SELECT sum(p.valor_pago) AS valor_pago, 
                                            sum(p.valor_nc) AS valor_nc, 
                                            sum(p.descuento) AS descuento, 
                                            sum(p.tarifa) AS tarifa,
                                            sum(p.valor_uso) AS valor_uso, 
                                            sum(p.valor_no_uso) AS valor_no_uso,
                                            sum(p.tarifa - p.valor_no_uso - p.valor_pago + p.valor_nc + p.descuento) as saldo
                                    from isp.contrato_pago_pack p WHERE p.id = $id and p.valor_no_uso < p.tarifa ";
                    $oConA->Query($sqlSaldos);
                    $valor_pago     = $oConA->f('valor_pago');
                    $valor_nc       = $oConA->f('valor_nc');
                    $descuento      = $oConA->f('descuento');
                    $tarifa         = $oConA->f('tarifa');
                    $valor_uso      = $oConA->f('valor_uso');
                    $valor_no_uso   = $oConA->f('valor_no_uso');
                    $saldo          = $oConA->f('saldo');
                    $oConA->Free();


                    $array[] = array($cod_prod, $valor_pago, $tarifa, $valor_uso, $valor_no_uso, $saldo, $id_prod, $cantidad, $valor_nc, $descuento, $estado_plan, $id, $detalle, $tipo);
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        return $array;
    }

    function calculaPlanesCuotaImpuestos($idPago, $op, $descuento, $abono_total = 0)
    {

        $oIfx = $this->oIfx;
        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        global $DSN, $DSN_Ifx;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oConA = new Dbo();
        $oConA->DSN = $DSN;
        $oConA->Conectar();

        $oCon1 = new Dbo;
        $oCon1->DSN = $DSN;
        $oCon1->Conectar();

        $oCon2 = new Dbo;
        $oCon2->DSN = $DSN;
        $oCon2->Conectar();

        $idsucursalsesion = $_SESSION['U_SUCURSAL'];

        $array_t = array();
        $array_d = array();
        $array_i = array();
        $array_c = array();
        $array_r = array();

        //lectura sucia
        //

        //CONTROLES PARA EL CALCULO EN DIA 
        $sql = "SELECT  EXTRACT(MONTH FROM p.fecha) as mes, EXTRACT(YEAR FROM p.fecha) as anio, c.tipo_contrato_de_cobro, c.fecha_c_corte
                from isp.contrato_pago_pack p, isp.contrato_clpv c
                WHERE 
                p.id_contrato = c.id AND
                p.id_clpv = c.id_clpv AND
                p.id_empresa = $idEmpresa AND 
                p.id_clpv = $idClpv AND 
                p.id_contrato = $idContrato AND 
                p.id_pago = $idPago AND 
                p.estado != 'AN'";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $mes            = $oCon->f('mes');
                    $anio           = $oCon->f('anio');
                    $tipo_contrato  = $oCon->f('tipo_contrato_de_cobro');
                    $fecha_c_corte  = $oCon->f('fecha_c_corte');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        //CONTROL PARA DIAS DE CONSUMO EN LINEA
        $sql = "SELECT id
                from isp.int_contrato_caja_pack
                where id_clpv = $idClpv and
                id_contrato = $idContrato and    
                estado = 'C' and activo = 'S'";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $control_eq_c = $oCon->f('id');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $soporte_mantenimiento_sn   = 'N';
        $rubros_activos_sn          = 'N';
        $sql = "SELECT soporte_mantenimiento_sn, rubros_activos_sn FROM isp.int_parametros where id_sucursal = $idsucursalsesion";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $soporte_mantenimiento_sn   = $oCon->f('soporte_mantenimiento_sn');
                    $rubros_activos_sn          = $oCon->f('rubros_activos_sn');
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $valor_sm = 0;
        if($soporte_mantenimiento_sn == 'S'){
            $sql = "SELECT valor FROM isp.int_config_inst where id_empresa = $idEmpresa and id_sucursal = $idsucursalsesion AND equipo = '10'";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $valor_sm = $oCon->f('valor');
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();
        }

        $array_rubros = array();
        if($rubros_activos_sn == 'S'){
            $sql = "SELECT id_tipo_prod_aplica, id_plan, cod_plan, valor_plan, tipo_por_val FROM isp.int_rubros_fact where id_sucursal = $idsucursalsesion AND estado = 'A'";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $id_tipo_prod_aplica    = $oCon->f('id_tipo_prod_aplica');
                        $id_plan                = $oCon->f('id_plan');
                        $cod_plan               = $oCon->f('cod_plan');
                        $valor_plan             = $oCon->f('valor_plan');
                        $tipo_por_val           = $oCon->f('tipo_por_val');

                        $datos_indi = array(
                            "id_tipo_prod_aplica" => $id_tipo_prod_aplica,
                            "id_plan" => $id_plan,
                            "cod_plan" => $cod_plan,
                            "valor_plan" => $valor_plan,
                            "tipo_por_val" => $tipo_por_val
                        );

                        array_push($array_rubros, $datos_indi);
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();
        }

        $mesActual      = date("m");
        $anioActual     = date("Y");

        $sql = "select p.prod_cod_prod, pr.prbo_iva_porc, pr.prbo_ice_porc, pr.prbo_val_irbp
				from saeprod p, saeprbo pr where
				p.prod_cod_prod = pr.prbo_cod_prod and
				p.prod_cod_empr = pr.prbo_cod_empr and
				p.prod_cod_sucu = pr.prbo_cod_sucu and
				p.prod_cod_empr = $idEmpresa and
				p.prod_cod_sucu = $idSucursal and
				pr.prbo_cod_bode = 2
				group by 1,2,3,4";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $array_i[$oIfx->f('prod_cod_prod')] = $oIfx->f('prbo_iva_porc');
                    $array_c[$oIfx->f('prod_cod_prod')] = $oIfx->f('prbo_ice_porc');
                    $array_r[$oIfx->f('prod_cod_prod')] = $oIfx->f('prbo_val_irbp');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $total_i = 0;
        $con_imp = 0;
        $sin_imp = 0;
        $total = 0;

        $array_pago_pack = [];
        $sql = "SELECT id, COUNT(id) as cantidad, sum(tarifa - valor_no_uso - valor_pago + valor_nc + descuento) as saldo, tarifa from isp.contrato_pago_pack WHERE id_contrato = $idContrato GROUP BY id";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $array_pago_pack[$oCon->f('id')] = [$oCon->f('cantidad'),$oCon->f('saldo'),$oCon->f('tarifa')];
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();


        $sql = "SELECT p.id, p.cod_prod, p.id_prod,p.descuento
				from isp.contrato_pago_pack p
				WHERE 
				p.id_empresa = $idEmpresa AND 
				p.id_clpv = $idClpv AND 
				p.id_contrato = $idContrato AND 
				p.id_pago = $idPago AND 
				p.estado != 'AN' AND
                p.tarifa > 0 ORDER BY p.tarifa desc";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $iva            = $array_i[$oCon->f('cod_prod')];
                    $ice            = $array_c[$oCon->f('cod_prod')];
                    $irbp           = $array_r[$oCon->f('cod_prod')];
                    $id             = $oCon->f('id');
                    $codigo_prod    = $oCon->f('cod_prod');

                    $cantidad = 0;
                    $saldo = 0;
                    $tarifa_sm = 0;
                    if(isset($array_pago_pack[$id])){
                        $cantidad   = $array_pago_pack[$id][0];
                        $saldo      = $array_pago_pack[$id][1];
                        $tarifa_sm  = $array_pago_pack[$id][2];
                    }

                    $saldoCuotaTot_ = $saldo;                    

                    $saldo = $saldoCuotaTot_;
                    $saldo_ini = $saldoCuotaTot_;

                    if(!empty($codigo_prod)){
                        $id_tipo_prod_s = 0;
                        $sql = "SELECT id_tipo_prod FROM isp.int_paquetes where prod_cod_prod = '$codigo_prod'";
                        if ($oCon2->Query($sql)) {
                            if ($oCon2->NumFilas() > 0) {
                                do {
                                    $id_tipo_prod_s = $oCon2->f('id_tipo_prod');
                                } while ($oCon2->SiguienteRegistro());
                            }
                        }
                        $oCon2->Free();

                        //PROCESO PARA SOPORTE E INSTALACION
                        if($soporte_mantenimiento_sn == 'S' && $saldo >= $valor_sm && $id_tipo_prod_s == 2){
                            $saldo = $saldo - $valor_sm;
                        }else if($soporte_mantenimiento_sn == 'S' && $saldo < $valor_sm && $id_tipo_prod_s == 2){
                            /* $sqlSaldos =  "SELECT tarifa from isp.contrato_pago_pack p WHERE p.id = $id";
                            $oConA->Query($sqlSaldos);
                            $tarifa_sm = $oConA->f('tarifa');
                            $oConA->Free(); */

                            $porc_1 = (($saldo * 100) / $tarifa_sm) / 100;
                            $valor_sm_1 = $valor_sm * $porc_1;

                            if($valor_sm_1 > $saldo){
                                $saldo = $valor_sm_1 - $saldo;
                            }else{
                                $saldo = $saldo - $valor_sm_1;
                            }
                        }

                        if(count($array_rubros) > 0 && $rubros_activos_sn == 'S' && $id_tipo_prod_s > 0){
                            //PROCESO PARA EL INGRESO DE RUBROS
                            $array_rubros_fn = array();
                            //------Filtrar el array para obtener solo los elementos que coinciden con el valor de $id_tipo_prod_s ---//
                            $array_rubros_fn = array_filter($array_rubros, function($item) use ($id_tipo_prod_s) {
                                return $item["id_tipo_prod_aplica"] === $id_tipo_prod_s;
                            });
    
                            $array_rubros_fn = array_values($array_rubros_fn);

                            if(count($array_rubros_fn) > 0){
                                foreach ($array_rubros_fn as $array_rubro){
                                    $id_tipo_prod_aplica_rubr    = $array_rubro["id_tipo_prod_aplica"];
                                    $id_plan_rubr                = $array_rubro["id_plan"];
                                    $cod_plan_rubr               = $array_rubro["cod_plan"];
                                    $valor_plan_rubr             = $array_rubro["valor_plan"];
                                    $tipo_por_val_rubr           = $array_rubro["tipo_por_val"];
                                   
                                    if($tipo_por_val_rubr == 1 && $abono_total > $valor_plan_rubr){
                                        $saldo = $saldo - $valor_plan_rubr;
                                    }else if($tipo_por_val_rubr == 2){
                                        $valor_plan_rubr_p = $valor_plan_rubr / 100; // TRANSFORMA EL PORCENTAJE: 30 A 0.30
                                        $valor_rbr = $saldo_ini * $valor_plan_rubr_p; // OBTENE EL VALOR DE ACUERDO AL PORCENTAJE

                                        if($saldo > $valor_rbr){
                                            $saldo = $saldo - $valor_rbr;
                                        }else{
                                            $saldo = $valor_rbr - $saldo;
                                        }
                                        
                                    }
                                   
                                }
                            }
                        }
                    }

                    $id_prod = $oCon->f('id_prod');
                    $descuento = $oCon->f('descuento');

                    if ($iva > 0) {
                        $total_i += $iva;
                    }

                    if ($ice > 0) {
                        $total_i += $ice;
                    }

                    if ($irbp > 0) {
                        $total_i += $irbp;
                    }

                    if ($total_i > 0) {
                        $con_imp += $saldo;
                        $con_iva = $saldo;
                    } else {
                        $sin_imp += $saldo;
                        $sin_iva = $saldo;
                    }

                    $total += $saldo;

                    $array_d[] = array($oCon->f('cod_prod'), $cantidad, $saldo, $con_iva, $sin_iva, $id, $id_prod, $descuento);

                    if($soporte_mantenimiento_sn == 'S' && $saldo_ini >= $valor_sm && $id_tipo_prod_s == 2){
                        $sql = "SELECT cod_prod, id_prod FROM isp.int_config_inst where id_empresa = $idEmpresa and id_sucursal = $idsucursalsesion AND equipo = '10'";
                        if ($oCon2->Query($sql)) {
                            if ($oCon2->NumFilas() > 0) {
                                do {
                                    $codigo_prod     = $oCon2->f('cod_prod');
            
                                    $saldo = $valor_sm;
                                    
                                    $cantidad = 1;
            
                                    $saldoCuotaTot_ = $saldo;
            
                                    $id_prod = $oCon2->f('id_prod');
                                    //$descuento = 0;
            
                                    if ($total_i > 0) {
                                        $con_imp += $saldo;
                                        $con_iva = $saldo;
                                    } else {
                                        $sin_imp += $saldo;
                                        $sin_iva = $saldo;
                                    }
            
                                    $total += $saldo;
            
                                    $array_d[] = array($oCon2->f('cod_prod'), $cantidad, $saldo, $con_iva, $sin_iva, $id, $id_prod, $descuento);
                                } while ($oCon2->SiguienteRegistro());
                            }
                        }
                        $oCon2->Free();
                    }else if($soporte_mantenimiento_sn == 'S' && $saldo < $valor_sm && $id_tipo_prod_s == 2){
                        /* $sqlSaldos =  "SELECT tarifa from isp.contrato_pago_pack p WHERE p.id = $id";
                        $oConA->Query($sqlSaldos);
                        $tarifa_sm = $oConA->f('tarifa');
                        $oConA->Free(); */

                        $sql = "SELECT cod_prod, id_prod FROM isp.int_config_inst where id_empresa = $idEmpresa and id_sucursal = $idsucursalsesion AND equipo = '10'";
                        if ($oCon2->Query($sql)) {
                            if ($oCon2->NumFilas() > 0) {
                                do {
                                    $codigo_prod     = $oCon2->f('cod_prod');
            
                                    $porc_1 = (($saldo_ini * 100) / $tarifa_sm) / 100;
                                    $valor_sm_1 = $valor_sm * $porc_1;

                                    $saldo = $valor_sm_1;
                                    
                                    $cantidad = 1;
            
                                    $saldoCuotaTot_ = $saldo;
            
                                    $id_prod = $oCon2->f('id_prod');
                                    //$descuento = 0;
            
                                    if ($total_i > 0) {
                                        $con_imp += $saldo;
                                        $con_iva = $saldo;
                                    } else {
                                        $sin_imp += $saldo;
                                        $sin_iva = $saldo;
                                    }
            
                                    $total += $saldo;
            
                                    $array_d[] = array($oCon2->f('cod_prod'), $cantidad, $saldo, $con_iva, $sin_iva, $id, $id_prod, $descuento);
                                } while ($oCon2->SiguienteRegistro());
                            }
                        }
                        $oCon2->Free();

                    }

                    if($rubros_activos_sn == 'S' && $id_tipo_prod_s > 0){
                        //PROCESO PARA EL INGRESO DE RUBROS
                        if(count($array_rubros_fn) > 0){
                            foreach ($array_rubros_fn as $array_rubro){
                                $id_tipo_prod_aplica_rubr    = $array_rubro["id_tipo_prod_aplica"];
                                $id_plan_rubr                = $array_rubro["id_plan"];
                                $cod_plan_rubr               = $array_rubro["cod_plan"];
                                $valor_plan_rubr             = $array_rubro["valor_plan"];
                                $tipo_por_val_rubr           = $array_rubro["tipo_por_val"];
                               
                                if($tipo_por_val_rubr == 1 && $saldo_ini > $valor_plan_rubr){
                                   
                                    $saldo = $valor_plan_rubr;
                                    
                                    $cantidad = 1;
            
                                    $saldoCuotaTot_ = $saldo;
            
                                    if ($total_i > 0) {
                                        $con_imp += $saldo;
                                        $con_iva = $saldo;
                                    } else {
                                        $sin_imp += $saldo;
                                        $sin_iva = $saldo;
                                    }
            
                                    $total += $saldo;
            
                                    $array_d[] = array($cod_plan_rubr, $cantidad, $saldo, $con_iva, $sin_iva, $id, $id_plan_rubr, $descuento);
                                }else if($tipo_por_val_rubr == 2){
                                    $valor_plan_rubr_p = $valor_plan_rubr / 100; // TRANSFORMA EL PORCENTAJE: 30 A 0.30
                                    $valor_rbr = $saldo_ini * $valor_plan_rubr_p; // OBTENE EL VALOR DE ACUERDO AL PORCENTAJE
                                    
                                    $saldo = $valor_rbr;

                                    $cantidad = 1;
            
                                    $saldoCuotaTot_ = $saldo;
            
                                    if ($total_i > 0) {
                                        $con_imp += $saldo;
                                        $con_iva = $saldo;
                                    } else {
                                        $sin_imp += $saldo;
                                        $sin_iva = $saldo;
                                    }
            
                                    $total += $saldo;
            
                                    $array_d[] = array($cod_plan_rubr, $cantidad, $saldo, $con_iva, $sin_iva, $id, $id_plan_rubr, $descuento);
                                }
                            }
                        }
                    }
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();
        
        if ($op == 1) {
            return $array_d;
        } else {

            $descuento_v = 0;
            $con_iva = 0;
            $sin_iva = 0;
            $iva = 0;
            $ice = 0;
            $irbp = 0;

            if ($descuento > 0) {
                $val_d = ($total *  $descuento) / 100;
                $total -= $val_d;
                $descuento_v += $val_d;
            }

            if ($con_imp > 0) {
                $con_iva = ($con_imp / 1.30);
            }

            if ($con_iva > 0) {
                $iva = ($con_iva * 18) / 100;
            }

            if ($con_iva > 0) {
                $ice = ($con_iva * 10) / 100;
            }

            if ($con_iva > 0) {
                $irbp = ($con_iva * 2) / 100;
            }

            $total = $con_iva + $sin_iva + $iva + $ice + $irbp;

            $array_t = array($descuento, $descuento_v, $con_iva, $sin_iva, $iva, $ice, $irbp, $total);

            return $array_t;
        }
    }

    /***************************************************
    @ calculaPorcentajeCuotaImpuestos
    + Consulta Tabla Pagos Pack
    + Prorratea impuestos y genera valores
     **************************************************/
    function calculaPorcentajeCuotaImpuestos($idPago, $idBodega)
    {

        $oIfx = $this->oIfx;
        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $array = array();
        $array_i = array();
        $array_c = array();
        $array_r = array();

        //lectura sucia
        //

        $sql = "select p.prod_cod_prod, pr.prbo_iva_porc, pr.prbo_ice_porc, pr.prbo_val_irbp
				from saeprod p, saeprbo pr where
				p.prod_cod_prod = pr.prbo_cod_prod and
				p.prod_cod_empr = pr.prbo_cod_empr and
				p.prod_cod_sucu = pr.prbo_cod_sucu and
				p.prod_cod_empr = $idEmpresa and
				p.prod_cod_sucu = $idSucursal and
				pr.prbo_cod_bode = $idBodega
				group by 1,2,3,4";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $array_i[$oIfx->f('prod_cod_prod')] = $oIfx->f('prbo_iva_porc');
                    $array_c[$oIfx->f('prod_cod_prod')] = $oIfx->f('prbo_ice_porc');
                    $array_r[$oIfx->f('prod_cod_prod')] = $oIfx->f('prbo_val_irbp');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $con_imp = 0;
        $sin_imp = 0;
        $total = 0;

        $sql = "SELECT p.cod_prod, p.id,
                count(*) as cantidad, 
                sum(p.tarifa - p.valor_no_uso - p.valor_pago + p.valor_nc + p.descuento) as saldo
				from isp.contrato_pago_pack p
				WHERE 
				p.id_empresa = $idEmpresa AND 
				p.id_clpv = $idClpv AND 
				p.id_contrato = $idContrato AND 
				p.id_pago = $idPago AND 
				p.estado != 'AN'
                GROUP BY p.tarifa  ORDER BY p.tarifa desc";
        $tot_iva = 0;
        $tot_ice = 0;
        $tot_irbp = 0;
        $porc_iva = 0;
        $porc_ice = 0;
        $porc_irbp = 0;
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $iva = $array_i[$oCon->f('cod_prod')];
                    $ice = $array_c[$oCon->f('cod_prod')];
                    $irbp = $array_r[$oCon->f('cod_prod')];
                    $saldo = $oCon->f('saldo');

                    $valid_i = false;

                    if ($iva > 0) {
                        $valid_i = true;
                        $tot_iva += $saldo;
                        $porc_iva = $iva;
                    }

                    if ($ice > 0) {
                        $valid_i = true;
                        $tot_ice += $saldo;
                        $porc_ice = $ice;
                    }

                    if ($irbp > 0) {
                        $valid_i = true;
                        $tot_irbp += $saldo;
                        $porc_irbp = $irbp;
                    }

                    if ($valid_i) {
                        $con_imp += $saldo;
                    } else {
                        $sin_imp += $saldo;
                    }

                    $total += $saldo;
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        //calcula porcentajes de impuestos
        $porcentaje_con_imp = 0;
        $porcentaje_sin_imp = 0;

        if ($con_imp > 0) {
            $porcentaje_con_imp = ($con_imp / $total) * 100;
        }

        if ($sin_imp > 0) {
            $porcentaje_sin_imp = ($sin_imp / $total) * 100;
        }

        $array = array($porcentaje_con_imp, $porcentaje_sin_imp,   $tot_iva, $tot_ice, $tot_irbp, $porc_iva, $porc_ice, $porc_irbp);

        return $array;
    }

    //FUNCION PARA DEVOLVER EL TOTAL DE LOS IMPUESTOS SEGUN CADA SERVICIO
    function calculaPorcentajeCuotaImpuestosEquipos($idPago, $idBodega, $oCon2, $saldo_tmp = 0, $val_pago = 0)
    {
        global $DSN, $DSN_Ifx;

        $oIfx = $this->oIfx;
        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $oCon1 = new Dbo;
        $oCon1->DSN = $DSN;
        $oCon1->Conectar();

        $idsucursalsesion   = $_SESSION['U_SUCURSAL'];
        $id_pais            = $_SESSION['U_PAIS_COD'];

        $array = array();
        $array_i = array();
        $array_c = array();
        $array_r = array();

        //bodega de facturacion
        $sql = "SELECT para_bod_fact FROM saepara WHERE para_cod_empr = $idEmpresa AND para_cod_sucu = $idsucursalsesion";
        $bodega_id_sesion = consulta_string_func($sql, 'para_bod_fact', $oIfx, 0);

        $sql = "SELECT id_sucursal from isp.contrato_clpv WHERE id = $idContrato";
        $idSucursalCliente = consulta_string_func($sql, 'id_sucursal', $oCon, 0);

        $sql = "SELECT pais_imp_metodo from saepais WHERE pais_cod_pais = $id_pais";
        $pais_imp_metodo = consulta_string_func($sql, 'pais_imp_metodo', $oCon, 0);

        $sql = "SELECT p.prod_cod_prod, pr.prbo_iva_porc, pr.prbo_ice_porc, pr.prbo_val_irbp
				from saeprod p, saeprbo pr where
				p.prod_cod_prod = pr.prbo_cod_prod and
				p.prod_cod_empr = pr.prbo_cod_empr and
				p.prod_cod_sucu = pr.prbo_cod_sucu and
				p.prod_cod_empr = $idEmpresa and
				p.prod_cod_sucu = $idSucursalCliente and
				pr.prbo_cod_bode = $idBodega
				group by 1,2,3,4";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $array_i[$oIfx->f('prod_cod_prod')] = $oIfx->f('prbo_iva_porc');
                    $array_c[$oIfx->f('prod_cod_prod')] = $oIfx->f('prbo_ice_porc');
                    $array_r[$oIfx->f('prod_cod_prod')] = $oIfx->f('prbo_val_irbp');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        //VALIDAR SI HAY DISTRICION
        $total_dis_txt = 'val_dis_total_' . $idPago;

        $total_dis = 0;
        if (isset($_SESSION[$total_dis_txt]) && $_SESSION[$total_dis_txt] > 0) {
            $total_dis = $_SESSION[$total_dis_txt];

            $abono_total = $val_pago;
            $array_d = $this->calculaPlanesCuotaImpuestos($idPago, 1, 0);

            if ($total_dis == $abono_total) {
                $con_imp = 0;
                $sin_imp = 0;
                $total = 0;
                $total_ice = 0;
                $total_iva = 0;
                $total_sin_impuesto_fin = 0;
                $total_impuesto_fin = 0;

                for ($j = 0; $j < count($array_d); $j++) {
                    $idproducto = $array_d[$j][0];

                    $sqlControlBode = "SELECT COUNT(*) as control_bode
                                        from saeprbo where
                                        prbo_cod_empr = $idEmpresa and
                                        prbo_cod_sucu = $idsucursalsesion and
                                        prbo_cod_bode = $bodega_id_sesion and
                                        prbo_cod_prod = '$idproducto'";
                    $oIfx->Query($sqlControlBode);
                    $control_bode = $oIfx->f('control_bode');
                    $oIfx->Free();

                    if ($control_bode == 0) {
                        $ingreso_prod = $this->copiaProdBode($idproducto, $idEmpresa, $idsucursalsesion, $bodega_id_sesion, $idSucursalCliente, $idBodega);
                    }

                    $coddde_temp = 'val_dis_' . $idPago . '_' . $idproducto;
                    if (isset($_SESSION[$coddde_temp]) && $_SESSION[$coddde_temp] != 0) {

                        $valor_pago_ser = $_SESSION[$coddde_temp];

                        $iva = $array_i[$idproducto];
                        $iva_calculo = ($iva / 100) + 1;

                        $ice = $array_c[$idproducto];
                        $ice_calculo = ($ice / 100) + 1;

                        $irbp = $array_r[$idproducto];
                        $irbp_calculo = ($irbp / 100) + 1;

                        $saldo = $valor_pago_ser;
                        if($pais_imp_metodo > 0 && $iva_calculo > 0){
                            $iva_calculo = $iva_calculo - 1;
                            $sin_iva_producto = $saldo * $iva_calculo;
                            $sin_iva_producto = $saldo - $sin_iva_producto;
                            $iva_calculo = $iva_calculo + 1;
                        }else{
                            $sin_iva_producto = $saldo / $iva_calculo;
                        }

                        //$sin_iva_producto = $saldo / $iva_calculo;
                        
                        $sin_ice_producto = $sin_iva_producto / $ice_calculo;

                        $valor_ice_producto = $sin_iva_producto - $sin_ice_producto;
                        $valor_iva_producto = $saldo - $sin_iva_producto;

                        $total_impuestos = $valor_iva_producto + $valor_ice_producto;

                        $tot_ice_prod = $tot_ice_prod + $valor_ice_producto;
                        $tot_iva_prod = $tot_iva_prod + $valor_iva_producto;
                        $tot_sin_impp = $tot_sin_impp + $sin_ice_producto;
                        $tot_imp_prod = $tot_imp_prod + $total_impuestos;
                    }
                }

                $total = $total_dis;
                $total_ice += $tot_ice_prod;
                $total_iva += $tot_iva_prod;
                $total_sin_impuesto_fin += $tot_sin_impp;
                $total_impuesto_fin += $tot_imp_prod;

                if ($iva_calculo > 0 || $ice_calculo > 0 || $irbp_calculo > 0) {
                    $con_imp += $total_dis;
                } else {
                    $sin_imp += $total_dis;
                }
            }
        } else {

            $array_saldos = [];
            $sql2 = "SELECT id, sum(p.tarifa - p.valor_no_uso - p.valor_pago + p.valor_nc + p.descuento) as saldo 
                    from isp.contrato_pago_pack p 
                    WHERE valor_no_uso < tarifa AND id_contrato = $idContrato GROUP BY 1";
            if ($oCon->Query($sql2)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $array_saldos[$oCon->f('id')] = $oCon->f('saldo');
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();

            $array_valida_bode = [];
            $sql = "SELECT prbo_cod_prod, COUNT(*) as control_bode
                    from saeprbo where
                    prbo_cod_empr = $idEmpresa and
                    prbo_cod_sucu = $idsucursalsesion and
                    prbo_cod_bode = $bodega_id_sesion
                    GROUP BY 1";
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                    do {
                        $array_valida_bode[$oIfx->f('prbo_cod_prod')] = $oIfx->f('control_bode');
                    } while ($oIfx->SiguienteRegistro());
                }
            }
            $oIfx->Free();

            //CONTROLES PARA EL CALCULO EN DIA 
            $sql = "SELECT  EXTRACT(MONTH FROM p.fecha) as mes, EXTRACT(YEAR FROM p.fecha) as anio, c.tipo_contrato_de_cobro, c.fecha_c_corte
                    from isp.contrato_pago_pack p, isp.contrato_clpv c
                    WHERE 
                    p.id_contrato = c.id AND
                    p.id_clpv = c.id_clpv AND
                    p.id_empresa = $idEmpresa AND 
                    p.id_clpv = $idClpv AND 
                    p.id_contrato = $idContrato AND 
                    p.id_pago = $idPago AND 
                    p.estado != 'AN'";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $mes            = $oCon->f('mes');
                        $anio           = $oCon->f('anio');
                        $tipo_contrato  = $oCon->f('tipo_contrato_de_cobro');
                        $fecha_c_corte  = $oCon->f('fecha_c_corte');
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();

            //CONTROL PARA DIAS DE CONSUMO EN LINEA
            $sql = "SELECT id
                    from isp.int_contrato_caja_pack
                    where id_clpv = $idClpv and
                    id_contrato = $idContrato and    
                    estado = 'C' and activo = 'S'";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $control_eq_c = $oCon->f('id');
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();

            $mesActual      = date("m");
            $anioActual     = date("Y");

            //EN CASO DE NO CUMPLIR PARA EL CALCULO EN LINEA SE TOMA LOS VALORES GUARDADOS
            $sql = "SELECT SUM(p.tarifa - p.valor_no_uso - p.valor_pago + p.valor_nc + p.descuento) as saldo_total
                    from isp.contrato_pago_pack p
                    WHERE 
                    p.id_empresa = $idEmpresa AND 
                    p.id_clpv = $idClpv AND 
                    p.id_contrato = $idContrato AND 
                    p.id_pago = $idPago AND 
                    p.estado != 'AN'";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $saldo_total = $oCon->f('saldo_total');
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();

            //CALCULOS DE LA CUOTA ACTUAL EN LINEA  
            $sql = "SELECT  calculo_linea   
                    from isp.int_parametros
                    WHERE id_sucursal = $idsucursalsesion";
            if ($oCon2->Query($sql)) {
                if ($oCon2->NumFilas() > 0) {
                    do {
                        $calculo_linea          = $oCon2->f('calculo_linea');
                    } while ($oCon2->SiguienteRegistro());
                }
            }
            $oCon2->Free();

            if ($calculo_linea == "S") {
                if ($mesActual == $mes && $anioActual == $anio && $control_eq_c > 0 && $fecha_c_corte != "0000-00-00" && $fecha_c_corte != "") {
                    $saldoCuota = 0;

                    $sql = "SELECT a.precio, a.fecha_corte, b.valor_pago, b.descuento, b.valor_nc, a.estado
                            from isp.int_contrato_caja_pack a, isp.contrato_pago_pack b
                            where b.id_pack = a.id and 
                            a.cod_prod = b.cod_prod and
                            b.fecha = LAST_DAY(CURRENT_DATE) AND
                            a.id_clpv = $idClpv and
                            a.id_contrato = $idContrato and    
                            a.activo = 'S'";
                    if ($oCon1->Query($sql)) {
                        if ($oCon1->NumFilas() > 0) {
                            do {

                                $precio_pack        = $oCon1->f('precio');
                                $fecha_c_corte_pack = $oCon1->f('fecha_corte');
                                $valor_pago_pack    = $oCon1->f('valor_pago');
                                $descuento_pack     = $oCon1->f('descuento');
                                $valor_nc_pack      = $oCon1->f('valor_nc');
                                $estado_pack        = $oCon1->f('estado');

                                if ($estado_pack == "C") {
                                    $sql = "SELECT  dias_consumidos,                    calculo_linea,                      dias_calculo_linea,         tipo_calculo_linea, 
                                                   extract(day from LAST_DAY(CURRENT_DATE) as ultimo_dia, MONTH(NOW()) as mes_actual,        extract(day from NOW()) as dia_actual,   MONTH('$fecha_c_corte_pack') as mes_u_corte,         
                                                   extract(day from '$fecha_c_corte_pack') as dia_u_corte      
                                            from isp.int_parametros
                                            WHERE id_sucursal = $idsucursalsesion";
                                    if ($oCon2->Query($sql)) {
                                        if ($oCon2->NumFilas() > 0) {
                                            do {
                                                $dias_consumidos        = $oCon2->f('dias_consumidos');
                                                $calculo_linea          = $oCon2->f('calculo_linea');
                                                $dias_calculo_linea     = $oCon2->f('dias_calculo_linea');
                                                $tipo_calculo_linea     = $oCon2->f('tipo_calculo_linea');
                                                $dia_actual             = $oCon2->f('dia_actual');
                                                $mes_actual             = $oCon2->f('mes_actual');
                                                $dia_u_corte            = $oCon2->f('dia_u_corte');
                                                $mes_u_corte            = $oCon2->f('mes_u_corte');
                                                $dia_u_mes              = $oCon2->f('ultimo_dia');

                                                if ($dias_consumidos == "S" && $calculo_linea == "S" && $mes_u_corte == $mes_actual) {
                                                    if ($tipo_calculo_linea == "S") {
                                                        //TIPO CALCULO LINEA = "S" : 
                                                        //SE CALCULA LA CUOTA COMPLETA HASTA CUMPLIR LOS DIAS CONFIGURADOS DESDE LA ULTIMA FECHA DE CORTE
                                                        $totalMs = $precio_pack + $tot_add - $valor_pago_pack + $descuento_pack + $valor_nc_pack;

                                                        $control_dias = $dia_actual - $dia_u_corte;

                                                        if ($control_dias <= $dias_calculo_linea) {
                                                            $saldoCuota = $totalMs;
                                                        } else {
                                                            $dias_no_consumo = $dia_actual - $dia_u_corte;
                                                            $dias_consumo    = $dia_u_mes - $dias_no_consumo;
                                                            $saldoCuota      = ($precio_pack / $dia_u_mes) * $dias_consumo;
                                                            $saldoCuota      = $saldoCuota + $tot_add - $valor_pago_pack + $descuento_pack + $valor_nc_pack;
                                                        }
                                                    } else {
                                                        //TIPO CALCULO LINEA = "N" : 
                                                        //SE CALCULA LOS DIAS DE USO HASTA CUMPLIR LOS DIAS CONFIGURADOS DESDE LA ULTIMA FECHA DE CORTE
                                                        $totalMs = $precio_pack + $tot_add - $valor_pago_pack + $descuento_pack + $valor_nc_pack;

                                                        $control_dias = $dia_actual - $dia_u_corte;

                                                        if ($control_dias > $dias_calculo_linea) {
                                                            $saldoCuota = $totalMs;
                                                        } else {
                                                            $dias_no_consumo = $dia_actual - $dia_u_corte;
                                                            $dias_consumo    = $dia_u_mes - $dias_no_consumo;
                                                            $saldoCuota      = ($precio_pack / $dia_u_mes) * $dias_consumo;
                                                            $saldoCuota      = $saldoCuota + $tot_add - $valor_pago_pack + $descuento_pack + $valor_nc_pack;
                                                        }
                                                    }
                                                }
                                            } while ($oCon2->SiguienteRegistro());
                                        }
                                    }
                                    $oCon2->Free();
                                } else {
                                    $saldoCuota = $precio_pack;
                                }

                                $saldoCuotaTot += $saldoCuota;
                            } while ($oCon1->SiguienteRegistro());
                        }
                    }
                    $oCon1->Free();

                    $saldo_total =  $saldoCuotaTot;
                }
            }

            if ($saldo_total > 0) {
                $procentaje_pago = ($val_pago * 100) / $saldo_total;
            } else {
                $saldo_total = 1;
                $procentaje_pago = ($val_pago * 100) / $saldo_total;
            }

            $con_imp = 0;
            $sin_imp = 0;
            $total = 0;
            $total_ice = 0;
            $total_iva = 0;
            $total_sin_impuesto_fin = 0;
            $total_impuesto_fin = 0;

            $i = 1;
            $iva_calculo = 0;
            $ice_calculo = 0;
            $irbp_calculo = 0;

            $sql = "SELECT p.cod_prod, p.id
                    from isp.contrato_pago_pack p
                    WHERE 
                    p.id_empresa = $idEmpresa AND 
                    p.id_clpv = $idClpv AND 
                    p.id_contrato = $idContrato AND 
                    p.id_pago = $idPago AND 
                    p.estado != 'AN' AND valor_no_uso < tarifa";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {

                        $codigo_prod    = $oCon->f('cod_prod');
                        $id             = $oCon->f('id');

                        $control_bode = 0;
                        if(isset($array_valida_bode[$codigo_prod])){
                            $control_bode = $array_valida_bode[$codigo_prod];
                        }

                        /*$sqlControlBode = "SELECT COUNT(*) as control_bode
                                            from saeprbo where
                                            prbo_cod_empr = $idEmpresa and
                                            prbo_cod_sucu = $idsucursalsesion and
                                            prbo_cod_bode = $bodega_id_sesion and
                                            prbo_cod_prod = '$codigo_prod'";

                                            echo $sqlControlBode;exit;
                        $oIfx->Query($sqlControlBode);
                        $control_bode = $oIfx->f('control_bode');
                        $oIfx->Free(); */

                        if ($control_bode == 0) {
                            $ingreso_prod = $this->copiaProdBode($codigo_prod, $idEmpresa, $idsucursalsesion, $bodega_id_sesion, $idSucursalCliente, $idBodega);
                        }

                        $iva = $array_i[$oCon->f('cod_prod')];
                        $iva_calculo = ($iva / 100) + 1;
                       
                        $ice = $array_c[$oCon->f('cod_prod')];
                        $ice_calculo = ($ice / 100) + 1;
                        
                        $irbp = $array_r[$oCon->f('cod_prod')];
                        $irbp_calculo = ($irbp / 100) + 1;

                        if ($saldo_tmp) {
                            $saldo = $saldo_tmp;
                        } else {

                            if ($calculo_linea == "S") {
                                if ($mesActual == $mes && $anioActual == $anio && $control_eq_c > 0 && $fecha_c_corte != "0000-00-00" && $fecha_c_corte != "") {
                                    $saldoTot = 0;

                                    $sql = "SELECT a.precio, a.fecha_corte, b.valor_pago, b.descuento, b.valor_nc, a.estado
                                            from isp.int_contrato_caja_pack a, isp.contrato_pago_pack b
                                            where b.id_pack = a.id and 
                                            a.cod_prod = b.cod_prod and
                                            b.fecha = LAST_DAY(CURRENT_DATE) AND
                                            a.id_clpv = $idClpv and
                                            a.id_contrato = $idContrato and    
                                            a.activo = 'S' and
                                            a.cod_prod = '$codigo_prod' and
                                            b.id = $id";
                                    if ($oCon1->Query($sql)) {
                                        if ($oCon1->NumFilas() > 0) {
                                            do {
                                                $precio_pack        = $oCon1->f('precio');
                                                $fecha_c_corte_pack = $oCon1->f('fecha_corte');
                                                $valor_pago_pack    = $oCon1->f('valor_pago');
                                                $descuento_pack     = $oCon1->f('descuento');
                                                $valor_nc_pack      = $oCon1->f('valor_nc');
                                                $estado_pack        = $oCon1->f('estado');

                                                if ($estado_pack == "C") {
                                                    $sql = "SELECT  dias_consumidos,                    calculo_linea,                      dias_calculo_linea,         tipo_calculo_linea, 
                                                                   extract(day from LAST_DAY(CURRENT_DATE) as ultimo_dia, MONTH(NOW()) as mes_actual,        extract(day from NOW()) as dia_actual,   MONTH('$fecha_c_corte_pack') as mes_u_corte,         
                                                                   extract(day from '$fecha_c_corte_pack') as dia_u_corte      
                                                            from isp.int_parametros
                                                            WHERE id_sucursal = $idsucursalsesion";
                                                    if ($oCon2->Query($sql)) {
                                                        if ($oCon2->NumFilas() > 0) {
                                                            do {
                                                                $dias_consumidos        = $oCon2->f('dias_consumidos');
                                                                $calculo_linea          = $oCon2->f('calculo_linea');
                                                                $dias_calculo_linea     = $oCon2->f('dias_calculo_linea');
                                                                $tipo_calculo_linea     = $oCon2->f('tipo_calculo_linea');
                                                                $dia_actual             = $oCon2->f('dia_actual');
                                                                $mes_actual             = $oCon2->f('mes_actual');
                                                                $dia_u_corte            = $oCon2->f('dia_u_corte');
                                                                $mes_u_corte            = $oCon2->f('mes_u_corte');
                                                                $dia_u_mes              = $oCon2->f('ultimo_dia');

                                                                if ($dias_consumidos == "S" && $calculo_linea == "S" && $mes_u_corte == $mes_actual) {
                                                                    if ($tipo_calculo_linea == "S") {
                                                                        //TIPO CALCULO LINEA = "S" : 
                                                                        //SE CALCULA LA CUOTA COMPLETA HASTA CUMPLIR LOS DIAS CONFIGURADOS DESDE LA ULTIMA FECHA DE CORTE
                                                                        $totalMs = $precio_pack + $tot_add - $valor_pago_pack + $descuento_pack + $valor_nc_pack;

                                                                        $control_dias = $dia_actual - $dia_u_corte;

                                                                        if ($control_dias <= $dias_calculo_linea) {
                                                                            $saldoCuota = $totalMs;
                                                                        } else {
                                                                            $dias_no_consumo = $dia_actual - $dia_u_corte;
                                                                            $dias_consumo    = $dia_u_mes - $dias_no_consumo;
                                                                            $saldoCuota      = ($precio_pack / $dia_u_mes) * $dias_consumo;
                                                                            $saldoCuota      = $saldoCuota + $tot_add - $valor_pago_pack + $descuento_pack + $valor_nc_pack;
                                                                        }
                                                                    } else {
                                                                        //TIPO CALCULO LINEA = "N" : 
                                                                        //SE CALCULA LOS DIAS DE USO HASTA CUMPLIR LOS DIAS CONFIGURADOS DESDE LA ULTIMA FECHA DE CORTE
                                                                        $totalMs = $precio_pack + $tot_add - $valor_pago_pack + $descuento_pack + $valor_nc_pack;

                                                                        $control_dias = $dia_actual - $dia_u_corte;

                                                                        if ($control_dias > $dias_calculo_linea) {
                                                                            $saldoCuota = $totalMs;
                                                                        } else {
                                                                            $dias_no_consumo = $dia_actual - $dia_u_corte;
                                                                            $dias_consumo    = $dia_u_mes - $dias_no_consumo;
                                                                            $saldoCuota      = ($precio_pack / $dia_u_mes) * $dias_consumo;
                                                                            $saldoCuota      = $saldoCuota + $tot_add - $valor_pago_pack + $descuento_pack + $valor_nc_pack;
                                                                        }
                                                                    }
                                                                }
                                                            } while ($oCon2->SiguienteRegistro());
                                                        }
                                                    }
                                                    $oCon2->Free();
                                                } else {
                                                    $saldoCuota = $precio_pack;
                                                }

                                                $saldo = $saldoCuota;
                                            } while ($oCon1->SiguienteRegistro());

                                            $saldoTot += $saldo;
                                        }
                                    }
                                    $oCon1->Free();

                                    $saldoCuotaTot_ = $saldoTot;
                                } else {
                                    $sql2 = "SELECT sum(p.tarifa - p.valor_no_uso - p.valor_pago + p.valor_nc + p.descuento) as saldo from isp.contrato_pago_pack p WHERE id = $id";
                                    if ($oCon2->Query($sql2)) {
                                        if ($oCon2->NumFilas() > 0) {
                                            do {
                                                $saldoCuotaTot_ = $oCon2->f('saldo');
                                            } while ($oCon2->SiguienteRegistro());
                                        }
                                    }
                                    $oCon2->Free();
                                }
                            } else {
                                $saldoCuotaTot_ = 0;
                                if(isset($array_saldos[$id])){
                                    $saldoCuotaTot_ = $array_saldos[$id];
                                }
                                /* $sql2 = "SELECT sum(p.tarifa - p.valor_no_uso - p.valor_pago + p.valor_nc + p.descuento) as saldo from isp.contrato_pago_pack p WHERE id = $id AND valor_no_uso < tarifa";
                                if ($oCon2->Query($sql2)) {
                                    if ($oCon2->NumFilas() > 0) {
                                        do {
                                            $saldoCuotaTot_ = $oCon2->f('saldo');

                                            echo $saldoCuotaTot_;exit;
                                        } while ($oCon2->SiguienteRegistro());
                                    }
                                }
                                $oCon2->Free(); */
                            }
                        }

                       
                        $saldo = ($procentaje_pago * $saldoCuotaTot_) / 100;

                        if($pais_imp_metodo > 0 && $iva_calculo > 0){
                            $iva_calculo = $iva_calculo - 1;
                            $sin_iva_producto = $saldo * $iva_calculo;
                            $sin_iva_producto = $saldo - $sin_iva_producto;
                            $iva_calculo = $iva_calculo + 1;
                        }else{
                            $sin_iva_producto = $saldo / $iva_calculo;
                        }
                        //$sin_iva_producto = $saldo / $iva_calculo;
                        $sin_ice_producto = $sin_iva_producto / $ice_calculo;
                        $valor_ice_producto = $sin_iva_producto - $sin_ice_producto;
                        $valor_iva_producto = $saldo - $sin_iva_producto;
                        $total_impuestos = $valor_iva_producto + $valor_ice_producto;
                        $total += $saldo;
                        $total_ice += $valor_ice_producto;
                        $total_iva += $valor_iva_producto;
                        $total_sin_impuesto_fin += $sin_ice_producto;
                        $total_impuesto_fin += $total_impuestos;
                        
                        if ($iva_calculo > 1 || $ice_calculo > 1 || $irbp_calculo > 1) {
                            $con_imp += $saldo;
                        } else {
                            $sin_imp += $saldo;
                        }
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();
        }

        //calcula porcentajes de impuestos
        $porcentaje_con_imp = 0;
        $porcentaje_sin_imp = 0;

        if ($con_imp > 0) {
            $porcentaje_con_imp = ($con_imp / $total) * 100;
        }

        if ($sin_imp > 0) {
            $porcentaje_sin_imp = ($sin_imp / $total) * 100;
        }

        $array = array($porcentaje_con_imp, $porcentaje_sin_imp, $total, $total_ice, $total_iva, $total_impuesto_fin, $total_sin_impuesto_fin);

        return $array;
    }

    /***************************************************
    @ recalcular el detalle de la cuota
    + Toma el valor de la isp.int_contrato_caja_pack
     **************************************************/
    function recalcularCuotaDetalle($id_cuota = 0)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oCon = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $idUsuario = $_SESSION['U_ID'];
        $detalle = "R. CUOTA";

        //SELECCIONAR LA TARIFA EL VALOR USO Y EL NO USO DE LA isp.contrato_pago
        $sql = "SELECT tarifa, valor_uso, valor_no_uso, fecha, dias, dias_uso, dias_no_uso, estado, tipo, tot_add
                from isp.contrato_pago
                WHERE id_contrato = $idContrato AND 
                id = $id_cuota LIMIT 1";
        $oCon->Query($sql);
        $tarifa = $oCon->f('tarifa');
        $valor_uso = $oCon->f('valor_uso');
        $valor_no_uso = $oCon->f('valor_no_uso');
        $fecha = $oCon->f('fecha');
        $dias = $oCon->f('dias');
        $dias_uso = $oCon->f('dias_uso');
        $dias_no_uso = $oCon->f('dias_no_uso');
        $estado = $oCon->f('estado');
        $tipo = $oCon->f('tipo');
        $tot_add = $oCon->f('tot_add');
        $oCon->Free();

        if ($tipo == "P") {
            $tipo = "M";
        }

        if ($estado == "PE") {
            $estado = "A";
        } else if ($estado == "CO") {
            $estado = "C";
        } else {
            $estado = "A";
        }

        if ($tarifa == 0) {
            $tarifa = $tot_add;
        }
        //SELECCIONAR LOS DATOS DEL PLAN QUE TIENE EN EL CONTRATO
        $sql = "SELECT id, id_caja, id_prod, cod_prod
                from isp.int_contrato_caja_pack
                WHERE id_contrato = $idContrato AND precio > 0 AND estado not in ('E') ORDER BY id DESC LIMIT 1";
        $oCon->Query($sql);
        $id_caja_pack = $oCon->f('id');
        $id_caja = $oCon->f('id_caja');
        $id_prod = $oCon->f('id_prod');
        $cod_prod = $oCon->f('cod_prod');
        $oCon->Free();

        //SELECCIONAR LOS DATOS DE EMPRESA Y SUCURSAL DEL CONTRATO
        $sql = "SELECT id_empresa, id_sucursal
                from isp.contrato_clpv
                WHERE id = $idContrato";
        $oCon->Query($sql);
        $id_empresa = $oCon->f('id_empresa');
        $id_sucursal = $oCon->f('id_sucursal');
        $oCon->Free();

        //INSERT EN LA CONTRATO PAGO PACK
        $sql = "insert into isp.contrato_pago_pack(id_empresa,      id_sucursal,    id_clpv,        id_contrato,        id_caja,        id_pack,        id_pago,
                                               cod_prod,        fecha,          tarifa,         dias,               dias_uso,       dias_no_uso,    valor_uso,
                                               valor_no_uso,    valor_dia,      valor_pago,     estado,             user_web,       fecha_server,   tipo,
                                               detalle,         id_prod,        valor_nc,       descuento)
                                        VALUES($id_empresa,     $id_sucursal,   $idClpv,        $idContrato,        $id_caja,       $id_caja_pack,  $id_cuota,
                                               '$cod_prod',     '$fecha',       $tarifa,        $dias,              $dias_uso,      $dias_no_uso,   $valor_uso,
                                               $valor_no_uso,   0,              0,              '$estado',          $idUsuario,     NOW(),          '$tipo',
                                               '$detalle',      $id_prod,       0,              0)";
        $oCon->QueryT($sql);

        $respuesta = true;

        return $respuesta;
    }


    /***************************************************
    @ cambioTarifaContrato
    + Tabla Pagos
    + Actualiza tarifa, mensualidades planes
     **************************************************/
    function cambioTarifaContrato($opCheck, $idCuota = 0)
    {

        $oCon = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        //suma planes
        $sql = "SELECT SUM(p.precio) AS valor
                from isp.int_contrato_caja c, isp.int_contrato_caja_pack p
                WHERE c.id_contrato = p.id_contrato AND
				c.id_clpv = p.id_clpv AND
				c.id = p.id_caja AND
				p.id_clpv = $idClpv AND
                p.id_contrato = $idContrato AND
                p.activo = 'S' AND
				c.estado IN (SELECT e.id
                        from isp.int_estados_equipo e 
                        where e.id = p.estado and
                        e.op_pago = 'S') AND
                p.estado IN (SELECT e.id
                        from isp.int_estados_equipo e 
                        where e.id = p.estado and
                        e.op_pago = 'S')";
        $valor = round(consulta_string_func($sql, 'valor', $oCon, 0), 2);

        //consulta descuentos
        $sql = "SELECT descuento_v from isp.contrato_descuentos WHERE id_contrato = $idContrato AND estado = 'A'";
        $descuento_v = round(consulta_string_func($sql, 'descuento_v', $oCon, 0), 2);

        if ($descuento_v > 0) {
            $descuento_v = $descuento_v * -1;
        } elseif ($descuento_v < 0) {
            $valor += abs($descuento_v);
        }

        //UPDATE precio
        $sqlUPDATE = "UPDATE isp.contrato_clpv set tarifa = '$valor' where id = $idContrato and id_clpv = $idClpv";
        $oCon->QueryT($sqlUPDATE);

        if ($opCheck == 'S') {

            $filtro_pago = "";
            if ($idCuota > 0) {
                $filtro_pago = " AND id > $idCuota";
            }

            $sqlUPDATEPago = "UPDATE isp.contrato_pago set tarifa = $valor,
                                descuento = $descuento_v
                                where id_clpv = $idClpv AND
                                id_contrato = $idContrato AND
                                estado = 'PE' AND
                                tipo = 'P' AND
                                (estado_fact is null OR estado_fact != 'GR')
                                $filtro_pago";
            $oCon->QueryT($sqlUPDATEPago);
        }

        return $valor;
    }

    /***************************************************
    @ agregarCargoAdicional
    + Agrega cargo adicional a tabla de pagos
    + Actualiza tarifa de cargos adicionales en cuota
     **************************************************/
    function agregarCargoAdicional($idCuota, $userWeb, $codProd, $detalle, $cantidad, $precio)
    {

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $detalle = strtoupper($detalle);

        $tot_add = $cantidad * $precio;

        //query codigo producto
        $sql = "SELECT id from isp.int_paquetes WHERE prod_cod_prod = '$codProd'";
        $idProd = consulta_string_func($sql, 'id', $oCon, 0);

        //fecha mensualidad
        $sql = "SELECT fecha, dias from isp.contrato_pago WHERE id = $idCuota";
        $fecha = consulta_string_func($sql, 'fecha', $oCon, date("Y-m-d"));
        $dias = consulta_string_func($sql, 'dias', $oCon, 0);

        $sql = "insert into isp.contrato_pago_pack(id_empresa, id_sucursal, id_clpv, id_contrato,
                                            id_caja, id_pack, id_pago, id_prod, cod_prod, fecha, 
                                            tarifa, valor_pago, valor_dia, valor_uso, valor_no_uso,
                                            tipo, dias, dias_uso, estado, user_web, fecha_server,
                                            detalle)
                                    values($idEmpresa, $idSucursal, $idClpv, $idContrato,
                                            null, null, $idCuota, $idProd, '$codProd', '$fecha',
                                            $tot_add, 0, 0, $tot_add, 0,
                                            'A', $dias, $dias, 'A', $userWeb, now(),
                                            '$detalle')";
        $oCon->QueryT($sql);

        $sql = "UPDATE isp.contrato_pago SET tot_add = (tot_add + $tot_add)
                WHERE id_contrato = $idContrato AND
                id_clpv = $idClpv AND
                id = $idCuota";
        $oCon->QueryT($sql);
    }

    /***************************************************
    @ recalcularTarifaCuotas
    + Actualiza Tarifa de Contrato
    + Actualiza Tarifa de las cuotas
     **************************************************/
    function recalcularTarifaCuotas_RESPALDO($aplicaPlanCuotas, $id_cuota = 0, $op_cuota = 'N')
    {

        $oCon = $this->oCon;
        $oCon1 = $this->oCon;
        $oCon2 = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $tmp_cuota = "";
        $tmp_valor_pago = "";

        if ($id_cuota == 0) {
            $anio_cuota = date("Y");
            $mes_cuota = date("m") - 1;
            $dia_cuota = 1;
            $fecha_temp = $anio_cuota . '-' . $mes_cuota . '-' . $dia_cuota;

            $sql = "SELECT id 
                    from isp.contrato_pago 
                    WHERE id_contrato = $idContrato AND 
                    fecha >= '$fecha_temp' AND
                    tipo = 'P' ";
            $id_cuota = consulta_string_func($sql, 'id', $oCon, 0);

            $tmp_valor_pago = ' AND c.valor_pago = 0';
        }
        //filtro de cuota

        if ($id_cuota > 0) {
            if ($op_cuota == 'S') {
                $tmp_cuota = " AND c.id = $id_cuota";
                $tmp_valor_pago = '';
            } else {
                $tmp_cuota = " AND c.id >= $id_cuota";
            }
        }

        $sql = "SELECT cuota_fija from isp.contrato_descuentos WHERE id_contrato = $idContrato AND estado = 'A'";
        $cuota_fija = consulta_string_func($sql, 'cuota_fija', $oCon, 'N');


        if ($cuota_fija == 'S') {

            $sql = "SELECT tarifa from isp.contrato_descuentos WHERE id_contrato = $idContrato";
            $tarifa_cuota_fija = consulta_string_func($sql, 'tarifa', $oCon, 0);

            $sql = "UPDATE isp.contrato_pago c
                    SET tarifa_tmp = tarifa, 
                    descuento_tmp = descuento, 
                    valor_uso_tmp = valor_uso,
                    valor_no_uso_tmp = valor_no_uso,
                    tarifa = $tarifa_cuota_fija, 
                    descuento = 0,
                    valor_uso_tmp = 0,
                    valor_no_uso_tmp = 0
                    WHERE
                    id_contrato = $idContrato AND
                    id_clpv = $idClpv AND
                    tipo = 'P'
                    $tmp_valor_pago
                    $tmp_cuota";
            $oCon->QueryT($sql);
        } else {

            //suma planes
            $sql = "SELECT SUM(p.precio) AS valor
                    from isp.int_contrato_caja c, isp.int_contrato_caja_pack p
                    WHERE c.id_contrato = p.id_contrato AND
                    c.id_clpv = p.id_clpv AND
                    c.id = p.id_caja AND
                    p.id_clpv = $idClpv AND
                    p.id_contrato = $idContrato AND
                    p.activo = 'S' AND
                    c.estado IN (SELECT e.id
                            from isp.int_estados_equipo e 
                            where e.id = p.estado and
                            e.op_pago = 'S') AND
                    p.estado IN (SELECT e.id
                            from isp.int_estados_equipo e 
                            where e.id = p.estado and
                            e.op_pago = 'S')";
            $valor = round(consulta_string_func($sql, 'valor', $oCon, 0), 2);

            //consulta descuentos
            $sql = "SELECT descuento_v from isp.contrato_descuentos WHERE id_contrato = $idContrato AND estado = 'A'";
            $descuento_v = round(consulta_string_func($sql, 'descuento_v', $oCon, 0), 2);

            if ($descuento_v > 0) {
                $descuento_v = $descuento_v;
            } elseif ($descuento_v < 0) {
                $valor += abs($descuento_v);
                $descuento_v = 0;
            }


            //UPDATE precio
            $sqlUPDATE = "UPDATE isp.contrato_clpv set tarifa = '$valor' where id = $idContrato and id_clpv = $idClpv";
            $oCon->QueryT($sqlUPDATE);

            //proceso para actualizacion de cuotas en funcion de la suma global de los planes - recorre todas las cuotas
            if ($aplicaPlanCuotas == 'S') {

                //query suma de planes para calculo de cuotas
                $sql = "SELECT c.id as idc, p.id as pid, SUM(p.precio) AS valor
                        from isp.int_contrato_caja c, isp.int_contrato_caja_pack p
                        WHERE c.id_contrato = p.id_contrato AND
                        c.id_clpv = p.id_clpv AND
                        c.id = p.id_caja AND
                        p.id_clpv = $idClpv AND
                        p.id_contrato = $idContrato AND
                        p.activo = 'S' AND
                        c.estado IN (SELECT e.id
                                from isp.int_estados_equipo e 
                                where e.id = p.estado and
                                e.op_pago = 'S') AND
                        p.estado IN (SELECT e.id
                                from isp.int_estados_equipo e 
                                where e.id = p.estado and
                                e.op_pago = 'S')
                            group by 1,2";
                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        unset($array_t);
                        do {
                            $array_t[$oCon->f('idc')][$oCon->f('pid')] = $oCon->f('valor');
                        } while ($oCon->SiguienteRegistro());
                    }
                }
                $oCon->Free();

                //query detalle de cuotas
                $sql = "SELECT p.id, p.id_caja, p.id_pack,c.id as id_cont_pago
                        from isp.contrato_pago c, isp.contrato_pago_pack p
                        WHERE c.id = p.id_pago AND
                        c.id_contrato = p.id_contrato AND
                        p.id_contrato = $idContrato AND 
                        p.estado = 'A'  AND
                        c.tipo not in ('B', 'C', 'F', 'N') AND
                        p.id_caja is not null
                        $tmp_valor_pago
                        $tmp_cuota
                        GROUP BY 1,2,3";


                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        unset($array_m);
                        do {
                            $array_m[] = array($oCon->f('id'), $oCon->f('id_caja'), $oCon->f('id_pack'), $oCon->f('id_cont_pago'));
                        } while ($oCon->SiguienteRegistro());
                    }
                }
                $oCon->Free();


                //actualiza tarifa detalle de cuotas
                if (count($array_m) > 0) {
                    foreach ($array_m as $val_m) {
                        $id_pack = $val_m[0];
                        $id_pack_caja = $val_m[1];
                        $id_pack_pack = $val_m[2];
                        $id_cont_pago = $val_m[3];

                        if (!empty($id_pack_caja) && !empty($id_pack_pack)) {
                            $tarifa_pack = $array_t[$id_pack_caja][$id_pack_pack];

                            if (!empty($tarifa_pack)) {
                                if ($descuento_v > 0) {
                                    $descuento_v = $descuento_v * -1;
                                    $sql = "UPDATE isp.contrato_pago_pack SET tarifa_tmp = tarifa, tarifa = '$tarifa_pack', descuento = '$descuento_v' WHERE id = $id_pack AND id_contrato = $idContrato";
                                } else {
                                    $sql = "UPDATE isp.contrato_pago_pack SET tarifa_tmp = tarifa, tarifa = '$tarifa_pack' WHERE id = $id_pack AND id_contrato = $idContrato";
                                }
                                $oCon->QueryT($sql);
                            }
                        }
                        $this->recalcularCuotaValores($id_cont_pago);
                    }
                }
            } //fin aplica cuotas
        } //fin cuota fija

        return $valor;
    }


    function recalcularTarifaCuotas($aplicaPlanCuotas, $id_cuota = 0, $op_cuota = 'N')
    {

        global $DSN_Ifx, $DSN;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oCon = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        //filtro de cuota
        $tmp_cuota = "";
        $tmp_valor_pago = " AND c.valor_pago = 0";
        if ($id_cuota > 0) {
            if ($op_cuota == 'S') {
                $tmp_cuota = " AND c.id = $id_cuota";
                $tmp_valor_pago = '';
            } else {
                $tmp_cuota = " AND c.id >= $id_cuota";
            }
        }

        $sql = "SELECT cuota_fija from isp.contrato_descuentos WHERE id_contrato = $idContrato AND estado = 'A'";
        $cuota_fija = consulta_string_func($sql, 'cuota_fija', $oCon, 'N');

        if ($cuota_fija == 'S') {

            $sql = "SELECT tarifa from isp.contrato_descuentos WHERE id_contrato = $idContrato";
            $tarifa_cuota_fija = consulta_string_func($sql, 'tarifa', $oCon, 0);

            $sql = "UPDATE isp.contrato_pago c
                    SET tarifa_tmp = tarifa, 
                    descuento_tmp = descuento, 
                    valor_uso_tmp = valor_uso,
                    valor_no_uso_tmp = valor_no_uso,
                    tarifa = $tarifa_cuota_fija, 
                    descuento = 0,
                    valor_uso_tmp = 0,
                    valor_no_uso_tmp = 0
                    WHERE
                    id_contrato = $idContrato AND
                    id_clpv = $idClpv 
                    $tmp_valor_pago
                    $tmp_cuota";
            $oCon->QueryT($sql);
        } else {

            //suma planes
            $sql = "SELECT SUM(p.precio) AS valor
                    from isp.int_contrato_caja c, isp.int_contrato_caja_pack p
                    WHERE c.id_contrato = p.id_contrato AND
                    c.id_clpv = p.id_clpv AND
                    c.id = p.id_caja AND
                    p.id_clpv = $idClpv AND
                    p.id_contrato = $idContrato AND
                    p.activo = 'S' AND
                    c.estado IN (SELECT e.id
                            from isp.int_estados_equipo e 
                            where e.id = c.estado and
                            e.op_pago = 'S') AND
                    p.estado IN (SELECT e.id
                            from isp.int_estados_equipo e 
                            where e.id = p.estado and
                            e.op_pago = 'S')";
            $valor = round(consulta_string_func($sql, 'valor', $oCon, 0), 2);



            //consulta descuentos
            $sql = "SELECT descuento_v, descuento_p from isp.contrato_descuentos WHERE id_contrato = $idContrato AND estado = 'A'";
            $descuento_v = round(consulta_string_func($sql, 'descuento_v', $oCon, 0), 2);
            $descuento_p = round(consulta_string_func($sql, 'descuento_p', $oCon, 0), 2);

            if ($descuento_v < 0) {
                $valor += abs($descuento_v);
                $descuento_v = 0;
            }

            //UPDATE precio
            $sqlUPDATE = "UPDATE isp.contrato_clpv set tarifa = '$valor' where id = $idContrato and id_clpv = $idClpv";
            $oCon->QueryT($sqlUPDATE);


            //proceso para actualizacion de cuotas en funcion de la suma global de los planes - recorre todas las cuotas
            if ($aplicaPlanCuotas == 'S') {

                //query suma de planes para calculo de cuotas
                $sql = "SELECT c.id as idc, p.id as pid, SUM(p.precio) AS valor
                        from isp.int_contrato_caja c, isp.int_contrato_caja_pack p
                        WHERE c.id_contrato = p.id_contrato AND
                        c.id_clpv = p.id_clpv AND
                        c.id = p.id_caja AND
                        p.id_clpv = $idClpv AND
                        p.id_contrato = $idContrato AND
                        p.activo = 'S' AND
                        c.estado IN (SELECT e.id
                                from isp.int_estados_equipo e 
                                where e.id = c.estado and
                                e.op_pago = 'S') AND
                        p.estado IN (SELECT e.id
                                from isp.int_estados_equipo e 
                                where e.id = p.estado and
                                e.op_pago = 'S')
                            group by 1,2";


                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        unset($array_t);
                        do {
                            $array_t[$oCon->f('idc')][$oCon->f('pid')] = $oCon->f('valor');
                        } while ($oCon->SiguienteRegistro());
                    }
                }
                $oCon->Free();

                //query detalle de cuotas
                $sql = "SELECT p.id, p.id_caja, p.id_pack, c.id as id_pago
                        from isp.contrato_pago c, isp.contrato_pago_pack p
                        WHERE c.id = p.id_pago AND
                        c.id_contrato = p.id_contrato AND
                        p.id_contrato = $idContrato AND 
                        p.estado = 'A'
                        $tmp_valor_pago
                        $tmp_cuota
                        GROUP BY 1,2,3,4";

                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        unset($array_m);
                        do {
                            $array_m[] = array($oCon->f('id'), $oCon->f('id_caja'), $oCon->f('id_pack'), $oCon->f('id_pago'));
                        } while ($oCon->SiguienteRegistro());
                    }
                }
                $oCon->Free();

                //echo "<pre>"." <br>";
                //echo "Descuento a aplicar: ".$descuento_v." <br>";

                //actualiza tarifa detalle de cuotas
                if (count($array_m) > 0) {
                    foreach ($array_m as $val_m) {
                        $id_pack = $val_m[0];
                        $id_pack_caja = $val_m[1];
                        $id_pack_pack = $val_m[2];
                        $id_pago = $val_m[3];

                        //consulta descuentos
                        $sql = "SELECT tarifa FROM isp.contrato_pago_pack where id = $id_pack";
                        $tarifa_pack = consulta_string_func($sql, 'tarifa', $oCon, 0);

                        $descuento_v = ($descuento_p * $tarifa_pack) / 100;

                        if (!empty($id_pack_caja) && !empty($id_pack_pack)) {
                            $tarifa_pack = $array_t[$id_pack_caja][$id_pack_pack];


                            if (!empty($tarifa_pack)) {
                                if ($descuento_v > 0) {
                                    $descuento_temp = $descuento_v * -1;
                                    $sql_2 = "UPDATE isp.contrato_pago_pack SET tarifa = '$tarifa_pack', descuento = $descuento_temp WHERE id = $id_pack;";
                                } else {
                                    $sql_2 = "UPDATE isp.contrato_pago_pack SET tarifa = '$tarifa_pack' WHERE id = $id_pack;";
                                }
                                //echo $sql_2." <br>";
                                $oCon->QueryT($sql_2);
                            }
                        }
                        $this->recalcularCuotaValores($id_pago);
                    }
                }
                //exit;
            } //fin aplica cuotas
        } //fin cuota fija

        return $valor;
    }

    /***************************************************
    @ recalcularCuotasEquipos
    + Actualiza Tarifa de Contrato
    + Actualiza Tarifa de las cuotas en funcion de equipos
     **************************************************/
    function recalcularCuotasEquipos($aplicaPlanCuotas, $id_cuota = 0, $op_cuota = 'N')
    {

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        //filtro de cuota
        $tmp_cuota = "";
        $tmp_cuota_pack = "";
        $tmp_fecha = " AND a.fecha >= date(now())";
        if ($id_cuota > 0) {
            if ($op_cuota == 'S') {
                $tmp_cuota = " AND a.id = $id_cuota";
                $tmp_cuota_pack = " AND a.id_pago = $id_cuota";
                $tmp_fecha = "";
            } else {
                $tmp_cuota = " AND id >= $id_cuota";
                $tmp_cuota_pack = " AND a.id_pago >= $id_cuota";
            }
        }

        //proceso para actualizacion de cuotas en funcion de la suma global de los planes - recorre todas las cuotas
        if ($aplicaPlanCuotas == 'S') {

            //delete detalle de cuotas
            $sql = "DELETE from isp.contrato_pago_pack a
                    WHERE a.id_contrato = $idContrato AND
                    a.tipo = 'M' AND 
                    a.valor_no_uso = 0 AND
                    a.valor_pago = 0 AND
                    a.id_pago not in (SELECT c.id
                    from isp.contrato_pago c
                    WHERE id_pago = c.id AND
                    a.id_contrato = c.id_contrato AND
                    c.valor_pago = 0  AND
                    c.tipo not in ('B', 'C', 'F', 'N'))
                    $tmp_fecha
                    $tmp_cuota_pack";
            $oCon->QueryT($sql);

            //query suma de planes para calculo de cuotas
            $sql = "SELECT c.id as idc, p.id as pid, p.id_prod, p.cod_prod,
                    date(p.fecha) as fecha, SUM(p.precio) AS valor
                    from isp.int_contrato_caja c, isp.int_contrato_caja_pack p
                    WHERE c.id_contrato = p.id_contrato AND
                    c.id_clpv = p.id_clpv AND
                    c.id = p.id_caja AND
                    p.id_clpv = $idClpv AND
                    p.id_contrato = $idContrato AND
                    p.activo = 'S' AND
                    c.estado IN (SELECT e.id
                            from isp.int_estados_equipo e 
                            where e.id = c.estado and
                            e.op_pago = 'S') AND
                    p.estado IN (SELECT e.id
                            from isp.int_estados_equipo e 
                            where e.id = p.estado and
                            e.op_pago = 'S')
                        group by 1,2,3,4,5";

            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    unset($array_t);
                    do {
                        $array_t[] = array($oCon->f('idc'), $oCon->f('pid'), $oCon->f('id_prod'), $oCon->f('cod_prod'), $oCon->f('valor'), $oCon->f('fecha'));
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();

            //query id cuotas
            $sql = "SELECT id, fecha, dias
                    from isp.contrato_pago
                    WHERE id_contrato = $idContrato AND
                    estado = 'PE' AND
                    tipo = 'P' AND 
                    valor_pago = 0 AND
                    fecha >= date(now())
                    $tmp_cuota";

            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    unset($array_c);
                    do {
                        $array_c[] = array($oCon->f('id'), $oCon->f('fecha'), $oCon->f('dias'));
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();

            //recorre cuotas y actualiza valores
            if (count($array_c) > 0) {

                $Equipos = new Equipos($oCon, null, $idEmpresa, $idSucursal, $idClpv, $idContrato, null);

                foreach ($array_c as $val) {
                    $idCuotaVal = $val[0];
                    $fechaCuotaVal = $val[1];
                    $diasCuotaVal = $val[2];

                    if (count($array_t) > 0) {
                        foreach ($array_t as $val_t) {
                            $idc = $val_t[0];
                            $pid = $val_t[1];
                            $id_prod = $val_t[2];
                            $cod_prod = $val_t[3];
                            $valor = $val_t[4];
                            $fecha_pack = $val_t[5];

                            $sql = "SELECT id
                                    from isp.contrato_pago_pack 
                                    WHERE id_contrato = $idContrato AND 
                                    id_pago = $idCuotaVal AND 
                                    id_pack = $pid AND 
                                    id_caja = $idc AND
                                    fecha >= '$fecha_pack'";
                            $control = consulta_string_func($sql, 'id', $oCon, 0);

                            if ($control == 0) {
                                $Equipos->registraTablaPagoPack($idc, $pid, $idCuotaVal, $id_prod, $cod_prod, $fechaCuotaVal, $valor, 0, 0, 0, $diasCuotaVal, $diasCuotaVal, 'A');
                            }
                        }
                    }
                    $this->recalcularTarifaCuotas('S', $idCuotaVal);
                } //foreach cuotas

                if ($id_cuota > 0) {
                    $this->recalcularTarifaCuotas('S', $id_cuota);
                }
            } //fin count cuotas
        }

        return $valor;
    }

    /***************************************************
    @ recalcularAbonos
    + Actualiza Tarifa de Contrato
    + Actualiza Tarifa de las cuotas en funcion de equipos
     **************************************************/
    function recalcularAbonos()
    {

        $oCon = $this->oCon;
        $oIfx = $this->oIfx;
        $idContrato = $this->idContrato;

        //LECTURA SUCIA
        //

        $sql = "SELECT balance from isp.contrato_clpv WHERE id = $idContrato";
        $balance = consulta_string_func($sql, 'balance', $oCon, 0);

        $sql = "select sum(fxfp_val_fxfp) as valor_pago
                from saefact, saefxfp
                where fxfp_cod_fact = fact_cod_fact and
                fxfp_cod_empr = fact_cod_empr and
                fxfp_cod_sucu = fact_cod_sucu and
                fact_est_fact != 'AN' AND
                fact_cod_contr = $idContrato;";
        $valor_pago = consulta_string_func($sql, 'valor_pago', $oIfx, 0);

        if ($balance < 0) {
            $valor_pago += abs($balance);
        }

        $sql = "SELECT p.id, SUM(p.tarifa + p.tot_add - p.valor_no_uso + p.descuento + p.valor_nc) AS valor
                from isp.contrato_pago p
                WHERE
                p.id_contrato = $idContrato AND
                p.estado != 'AN' and
                p.tipo not in ('B', 'C', 'F', 'N')
                GROUP BY 1
                ORDER BY p.fecha";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                unset($array);
                do {
                    $array[] = array($oCon->f('id'), $oCon->f('valor'));
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        if (count($array) > 0) {
            $suma_pago = 0;
            $total_abono = 0;
            $resta_pago = $valor_pago;
            $string_id = "";
            foreach ($array as $val) {
                $id = $val[0];
                $valor = $val[1];

                $suma_pago += $valor;

                $valor_ok = 0;
                if ($suma_pago <= $valor_pago) {
                    $valor_ok = $valor;
                    $resta_pago -= $valor;
                } else {
                    $valor_ok = $valor_pago - $suma_pago;
                }

                if ($valor_ok < 0) {
                    $sql = "UPDATE isp.contrato_pago SET valor_pago_tmp = valor_pago, valor_pago = 0 WHERE id = $id";
                } else {
                    $sql = "UPDATE isp.contrato_pago SET valor_pago_tmp = valor_pago, valor_pago = $valor_ok WHERE id = $id";

                    $string_id .= $id . ",";
                }

                $oCon->QueryT($sql);
            }

            $filtro_id = "";
            if (!empty($string_id)) {
                $string_id = substr($string_id, 0, strlen($string_id) - 1);
                $filtro_id = " AND id not in ($string_id)";
            }

            //echo $resta_pago;

            if ($resta_pago <> 0) {
                $sql = "UPDATE isp.contrato_clpv SET abono_tmp = abono, abono = 0 WHERE id = $idContrato";
                $oCon->QueryT($sql);

                $sql = "SELECT p.id, SUM(p.tarifa + p.tot_add - p.valor_no_uso + p.descuento + p.valor_nc) AS valor
                        from isp.contrato_pago p
                        WHERE
                        p.id_contrato = $idContrato AND
                        p.estado != 'AN' and
                        p.tipo not in ('B', 'C', 'F', 'N')
                        $filtro_id
                        GROUP BY 1
                        ORDER BY p.fecha";
                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        unset($array_p);
                        do {
                            $array_p[] = array($oCon->f('id'), $oCon->f('valor'));
                        } while ($oCon->SiguienteRegistro());
                    }
                }
                $oCon->Free();

                if (count($array_p) > 0) {
                    $resta_pago = abs($resta_pago);
                    $resta = abs($resta_pago);
                    $iCtrl = 1;
                    $valor_ok = 0;
                    foreach ($array_p as $val) {
                        $id = $val[0];
                        $valor = $val[1];

                        $resta -= $valor;
                        if ($resta > 0) {
                            $sql = "UPDATE isp.contrato_pago SET valor_pago_tmp = valor_pago, valor_pago = $valor, abono_pago = $valor 
                                        WHERE id = $id";
                            $oCon->QueryT($sql);
                        } else {
                            if ($iCtrl == 1) {
                                $valor_ok = $valor - abs($resta);
                                $sql = "UPDATE isp.contrato_pago SET valor_pago_tmp = valor_pago, valor_pago = $valor_ok, abono_pago =  $valor_ok 
                                        WHERE id = $id";
                                $oCon->QueryT($sql);
                            } else {
                                $sql = "UPDATE isp.contrato_pago SET valor_pago_tmp = valor_pago, valor_pago = 0, abono_pago =  0 
                                        WHERE id = $id";
                                $oCon->QueryT($sql);
                            }
                            $iCtrl++;
                        }
                    } //fin foreach
                } //fin if conut
            } else {
                $sql = "UPDATE isp.contrato_clpv SET abono_tmp = abono, abono = 0 WHERE id = $idContrato";
                $oCon->QueryT($sql);
            }
        } //fin if count


        return true;
    }

    /***************************************************
    @ agregarCargoRecurrente
    + Agrega cargo recurrente a tabla de pagos
    + Actualiza tarifa de cargos adicionales en cuota
     **************************************************/
    function agregarCargoRecurrente($cod_prod, $cantidad, $precio, $detalle, $userWeb)
    {

        $oCon = $this->oCon;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $detalle = strtoupper($detalle);

        $total = $cantidad * $precio;

        //query codigo producto
        $sql = "SELECT id from isp.int_paquetes WHERE prod_cod_prod = '$cod_prod'";
        $id_prod = consulta_string_func($sql, 'id', $oCon, 'null');

        $sql = "insert into isp.contrato_cargos(id_clpv, id_contrato, id_prod, cod_prod, cantidad, precio, total, fecha, user_web, fecha_server)
                                   values($idClpv, $idContrato, $id_prod, '$cod_prod', $cantidad, $precio, $total, now(), $userWeb, now())";
        $oCon->QueryT($sql);
    }

    /***************************************************
    @ registroTarifaContrato
    + Actualiza Tarifa de Contrato
     **************************************************/
    function registroTarifaContrato($id_instalacion, $id_peticion, $idUser, $tarifa, $tarifa_new)
    {

        $oCon = $this->oCon;
        $idContrato = $this->idContrato;

        //inactiva tarifas anteriores
        $sql = "UPDATE isp.contrato_tarifa SET estado = 'I' WHERE id_contrato = $idContrato";
        $oCon->QueryT($sql);

        //busca tarifa actual
        $sql = "SELECT max(id) as id from isp.contrato_tarifa WHERE id_contrato = $idContrato AND fecha_fin IS null";
        $id = consulta_string_func($sql, 'id', $oCon, 0);

        if ($id > 0) {
            $sql = "UPDATE isp.contrato_tarifa SET fecha_fin = now() WHERE id = $id AND id_contrato = $idContrato";
            $oCon->QueryT($sql);
        }

        if (empty($id_instalacion)) {
            $id_instalacion = 'null';
        }

        if (empty($id_peticion)) {
            $id_peticion = 'null';
        }

        $sql = "insert into isp.contrato_tarifa(id_contrato, id_instalacion, id_peticion, fecha_inicio, fecha_fin, tarifa_antes, tarifa_actual, estado, user_web, fecha_server)
                            VALUES($idContrato, $id_instalacion, $id_peticion, now(), null, '$tarifa', '$tarifa_new', 'A', $idUser, now())";
        $oCon->QueryT($sql);
    }

    function contratosParaCorteServicio($oCon, $oConA, $oIfx, $idEmpresa, $idSucursal, $idBarrio, $fechaCorte, $codigo, $cliente, $mes_deuda, $valor_deuda)
    {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        //variables de sesion
        unset($_SESSION['ARRAY_CHECK_CORTE_CLPV']);
        $idempresa     = $_SESSION['U_EMPRESA'];
        $idsucursal = $_SESSION['U_SUCURSAL'];

        // echo $fechaCorte; exit;

        $mesOK = substr($fechaCorte, 5, 2);
        $anioOK = substr($fechaCorte, 0, 4);


        //lectura sucia
        //

        $sHtml .= '<table class="table table-striped table-condensed table-bordered table-hover" style="width: 100%; margin-top: 20px;">';
        $sHtml .= '<tr>';
        $sHtml .= '<td colspan="8" class="bg-primary">REPORTE DE CONTRATOS</td>';
        $sHtml .= '</tr>';
        $sHtml .= '<tr>';
        $sHtml .= '<td>Contrato</td>';
        $sHtml .= '<td>Cliente</td>';
        $sHtml .= '<td>Direccion</td>';
        $sHtml .= '<td>Tarifa</td>';
        $sHtml .= '<td>Meses Adeuda</td>';
        $sHtml .= '<td>Valor Adeuda</td>';
        $sHtml .= '<td>Detalle</td>';
        $sHtml .= '<td align="center"><input type="checkbox" onclick="marcar(this);"></td>';
        $sHtml .= '</tr>';

        $sqlTmpBarrio = " ";
        if (!empty($idBarrio)) {
            $sqlTmpBarrio = "and c.id_barrio = $idBarrio";
        }

        $sqlTmpCodigo = " ";
        if (!empty($codigo)) {
            $sqlTmpCodigo = "and c.codigo = '$codigo'";
        }

        $sqlTmpClpv = " ";
        if (!empty($cliente)) {
            $sqlTmpClpv = "and (c.nom_clpv like upper('%$cliente%') OR c.ruc_clpv like ('%$cliente%'))";
        }

        $sqlTmpHavingDeuda = '';
        if (!empty($mes_deuda) || !empty($valor_deuda)) {
            if (!empty($mes_deuda) && !empty($valor_deuda)) {
                $sqlTmpHavingDeuda = "HAVING valor_deuda >= $valor_deuda and mes_deuda = $mes_deuda";
            } else {
                if (!empty($mes_deuda)) {
                    $sqlTmpHavingDeuda = "HAVING mes_deuda = $mes_deuda";
                } else if (!empty($valor_deuda)) {
                    $sqlTmpHavingDeuda = "HAVING valor_deuda >= $valor_deuda";
                }
            }
        }

        $fecha_ok = date("Y-m-t", strtotime($fechaCorte));

        $sql = "select c.id, c.codigo, c.id_clpv, c.nom_clpv, c.direccion, c.tarifa,
				(select count(*) as cuotas
				from isp.contrato_pago 
				where id_clpv = c.id_clpv and 
				id_contrato = c.id and
				fecha <= '$fecha_ok' and
				(tarifa - valor_pago) <> 0 and
				estado_fact is null and tipo = 'P') as mes_deuda,
				(select sum(tarifa - valor_pago) as tarifa
				from isp.contrato_pago 
				where id_clpv =  c.id_clpv and 
				id_contrato = c.id and
				fecha <= '$fecha_ok'
				and estado_fact is null and tipo = 'P') as valor_deuda
				from isp.contrato_clpv c, isp.contrato_pago p
				where c.id = p.id_contrato and
				c.estado = 'AP' and
				c.id_clpv = p.id_clpv and
				p.estado_fact is null and
				month(p.fecha) <= $mesOK and
				year(p.fecha) = $anioOK and
				c.id_empresa = $idEmpresa and
				c.id_sucursal = $idSucursal and
				(p.tarifa - p.valor_pago) <> 0
				$sqlTmpBarrio
				$sqlTmpCodigo
				$sqlTmpClpv
				group by 1,2,3,4,5,6
				$sqlTmpHavingDeuda";
        // echo $sql; exit;
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                unset($array);
                $totalAdeuda = 0;
                do {
                    $id = $oCon->f('id');
                    $id_clpv = $oCon->f('id_clpv');
                    $clpv_ruc_clpv = $oCon->f('ruc_clpv');
                    $clpv_nom_clpv = utf8_encode($oCon->f('nom_clpv'));
                    $codigo = $oCon->f('codigo');
                    $tarifa = $oCon->f('tarifa');
                    $direccion = $oCon->f('direccion');
                    $referencia = $oCon->f('referencia');
                    $id_sector = $oCon->f('id_sector');
                    $valorDeuda = $oCon->f('valor_deuda');
                    $mesesDeuda = $oCon->f('mes_deuda');

                    // $valorDeuda = $this->consultaMontoMesAdeuda($oConA, $oIfx, $id, $id_clpv, $fechaCorte);
                    // $mesesDeuda = $this->consultaMesesAdeuda($oConA, $oIfx, $id, $id_clpv, $fechaCorte);


                    $array[] = array($id_clpv, $id);
                    $sHtml .= '<tr>';
                    $sHtml .= '<td>' . $codigo . '</td>';
                    $sHtml .= '<td>' . $clpv_nom_clpv . '</td>';
                    $sHtml .= '<td>' . $direccion . '</td>';
                    $sHtml .= '<td align="right">' . number_format($tarifa, 2, '.', ',') . '</td>';
                    $sHtml .= '<td align="right">' . $mesesDeuda . '</td>';
                    $sHtml .= '<td align="right">' . number_format($valorDeuda, 2, '.', ',') . '</td>';
                    $sHtml .= '<td align="center">
                                <div class="btn btn-info btn-sm" onclick="estadoCuentaContrato(' . $id . ', ' . $id_clpv . ');">
									<span class="glyphicon glyphicon-list-alt"></span>
								</div>
                            </td>';
                    $sHtml .= '<td align="center">
                                <input type="checkbox" name="check_' . $id . '" value="S"/>
                            </td>';
                    $sHtml .= '</tr>';
                    $totalAdeuda += $valorDeuda;
                } while ($oCon->SiguienteRegistro());
                $sHtml .= '<tr>';
                $sHtml .= '<td class="bg-danger fecha_letra" colspan="5" align="right">Total:</td>';
                $sHtml .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalAdeuda, 2, '.', ',') . '</td>';
                $sHtml .= '<td class="bg-danger fecha_letra" colspan="2"></td>';
                $sHtml .= '</tr>';
            }
        }
        $oCon->Free();

        $_SESSION['ARRAY_CHECK_CORTE_CLPV'] = $array;

        $sHtml .= '</table>';


        return $sHtml;
    }

    /***************************************************
    @ estadoCuentaContrato
    + Consulta
     **************************************************/
    function estadoCuentaContrato($op_return = 0, $fecha_inicio = '', $fecha_fin = '')
    {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oCon = $this->oCon;
        $oIfx = $this->oIfx;
        $idEmpresa = $this->idEmpresa;
        $idContrato = $this->idContrato;

        $array_cuotas = array();

        //tipo pago
        $sql = "SELECT id, tipo, credito from isp.contrato_pago_tipo";
        $array_t = array_dato($oCon, $sql, 'id', 'tipo');
        $array_c = array_dato($oCon, $sql, 'id', 'credito');

        $sql = "SELECT usuario_id, lower(usuario_user) as user from comercial.usuario";
        $array_u = array_dato($oCon, $sql, 'usuario_id', 'user');

        $array_etiqueta['C'] = 'Corte';
        $array_etiqueta['D'] = 'Suspension';
        $array_etiqueta['R'] = 'Reconexion';

        //detalle
        $sql = "SELECT c.id_pago, p.paquete
                from isp.int_paquetes p, isp.contrato_pago_pack c
                WHERE p.id = c.id_prod AND
                c.id_contrato = $idContrato AND
                c.estado = 'A' AND
                c.tipo = 'A'";
        $array_detalle = array_dato($oCon, $sql, 'id_pago', 'paquete');

        $sql = "select estado, tarifa
                from isp.contrato_clpv 
                where id_clpv = id_clpv and 
                id = $idContrato";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $estado = $oCon->f('estado');
                $tarifaActual = $oCon->f('tarifa');
            }
        }
        $oCon->Free();

        $balanceActual = $this->consultaMontoMesAdeuda();

        $sHtml = '<table class="table table-striped table-condensed table-hover table-bordered" style="width: 99%; margin-top: 2px;" align="center">';
        $sHtml .= '<tr>';
        $sHtml .= '<td colspan="3"></td>';
        $sHtml .= '<td colspan="2" class="text-danger" align="right"><strong>Tarifa: ' . number_format($tarifaActual, 2, '.', ',') . '</strong></td>';
        $sHtml .= '<td colspan="2" class="text-danger" align="right"><strong>Balance: ' . number_format($balanceActual, 2, '.', ',') . '</strong></td>';
        $sHtml .= '</tr>';
        $sHtml .= '<tr>';
        $sHtml .= '<td class="info">Tipo</td>';
        $sHtml .= '<td class="info">Fecha</td>';
        $sHtml .= '<td class="info">Detalle</td>';
        $sHtml .= '<td class="info">Usuario</td>';
        $sHtml .= '<td class="info">Debito</td>';
        $sHtml .= '<td class="info">Credito</td>';
        $sHtml .= '<td class="info">Saldo</td>';
        $sHtml .= '</tr>';

        if (!empty($fecha_inicio) && !empty($fecha_fin)) {
            $anio = date("Y");
            $mes = date("m");
            $dia = ultimoDiaMesFunc($anio . '-' . $mes . '-01');

            $filtro = '<';
            if ($dia == date("d")) {
                $filtro = '<=';
            }

            $filtro_fecha = " AND fecha >= '$fecha_inicio' AND fecha $filtro '$fecha_fin'";
        } else {
            //filtro estado contrato
            $sql = "SELECT aplica_posfecha from isp.estado_contrato WHERE id = '$estado'";
            $aplica_posfecha = consulta_string_func($sql, 'aplica_posfecha', $oCon, 'N');

            $filtro = '';
            if ($aplica_posfecha == 'S') {

                $anio = date("Y");
                $mes = date("m") + 1;

                if ($mes > 12) {
                    $mes = 1;
                    $anio = date("Y") + 1;
                }

                $dia = ultimoDiaMesFunc($anio . '-' . $mes . '-01');
                $fecha_filtro = $anio . "-" . $mes . "-" . $dia;
                $filtro = '<';
            } else {
                $dia = ultimoDiaMesFunc(date("Y-m-d"));
                $fecha_filtro = date("Y") . "-" . date("m") . "-" . $dia;

                $filtro = '<';
                if ($dia == date("d")) {
                    $filtro = '<=';
                }
            }

            $filtro_fecha = " AND fecha $filtro '$fecha_filtro'";
        }

        $sql = "SELECT id, fecha, secuencial, estado, mes, anio,
                estado_fact, id_pago, id_factura, dias,
                abono, valor_pago, tarifa, tipo, detalle, dias_uso,
                can_add, pre_add, tot_add, valor_uso, valor_no_uso,
                valor_nc, descuento, user_web
                from isp.contrato_pago
                WHERE
                id_contrato = $idContrato AND
                estado = 'PE'
                $filtro_fecha
                order by fecha, id";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                unset($array);
                do {
                    $array[] = array(
                        $oCon->f('id'),
                        $oCon->f('fecha'),
                        $oCon->f('secuencial'),
                        $oCon->f('estado'),
                        $oCon->f('id_pago'),
                        $oCon->f('estado_fact'),
                        $oCon->f('id_factura'),
                        $oCon->f('abono'),
                        $oCon->f('valor_pago'),
                        $oCon->f('tarifa'),
                        $oCon->f('tipo'),
                        $oCon->f('detalle'),
                        $oCon->f('tot_add'),
                        $oCon->f('dias_uso'),
                        $oCon->f('valor_uso'),
                        $oCon->f('valor_no_uso'),
                        $oCon->f('valor_nc'),
                        $oCon->f('descuento'),
                        $oCon->f('user_web'),
                        substr($oCon->f('fecha'), 5, 2),
                        substr($oCon->f('fecha'), 0, 4)
                    );
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        if (count($array) > 0) {
            $totalDebito = 0;
            $totalCredito = 0;
            $totalSaldo = 0;
            unset($array_saldo);
            unset($array_debito);
            unset($array_cuotas_id);
            $valor_saldo = 0;
            $sum_credito = 0;
            $i = 0;
            foreach ($array as $val) {
                $id = $val[0];
                $fechaPago = $val[1];
                $secuencial = $val[2];
                $estado = $val[3];
                $id_pago = $val[4];
                $estado_fact = $val[5];
                $id_factura = $val[6];
                $abono = $val[7];
                $valor_pago = $val[8];
                $tarifa = $val[9];
                $tipo = $val[10];
                $detalle = $val[11];
                $tot_add = $val[12];
                $dias_uso = $val[13];
                $valor_uso = $val[14];
                $valor_no_uso = $val[15];
                $valor_nc = $val[16];
                $descuento = $val[17];
                $user_web = $val[18];
                $mes = $val[19];
                $anio = $val[20];

                $colorClass = '';
                if ($tipo == 'P') {
                    if (!empty($id_factura)) {
                        $colorClass = 'success';
                    } else {
                        $colorClass = 'danger';
                    }

                    //busca detalle
                    if (empty($detalle)) {
                        $c_etiqueta = '';
                        $c_dias_uso = '';
                        $c_dias_no_uso = '';
                        $c_valor_uso = '';
                        $c_valor_no_uso = '';
                        $c_valor_diario = '';
                        $sql = "SELECT etiqueta, dias_uso, dias_no_uso, sum(valor_uso) AS valor_uso, sum(valor_no_uso) AS valor_no_uso
                                from isp.contrato_pago_calculo
                                WHERE id_contrato = $idContrato AND
                                id_pago = $id
                                GROUP BY 1,2,3;";
                        if ($oCon->Query($sql)) {
                            if ($oCon->NumFilas() > 0) {
                                unset($array);
                                do {
                                    $c_etiqueta = $oCon->f('etiqueta');
                                    $c_dias_uso = $oCon->f('dias_uso');
                                    $c_dias_no_uso = $oCon->f('dias_no_uso');
                                    $c_valor_uso = $oCon->f('valor_uso');
                                    $c_valor_no_uso = $oCon->f('valor_no_uso');
                                    $c_valor_diario = $oCon->f('valor_diario');

                                    $detalle .= PHP_EOL . $array_etiqueta[$c_etiqueta] . ' dias: ' . $c_dias_uso . ', valor: ' . $c_valor_uso;
                                } while ($oCon->SiguienteRegistro());
                            } else {
                                $detalle = 'Mensualidad ' . Mes_func($mes) . ' - ' . $anio;
                            }
                        }
                        $oCon->Free();
                    }
                } elseif ($tipo == 'A') {
                    //detalle
                    if (empty($detalle)) {
                        $detalle = $array_detalle[$id];
                    }
                }

                $valor_credito = 0;
                $valor_debito = 0;

                $tipo_c = $array_c[$tipo];

                if ($tipo_c == 'S') {
                    $valor_credito = $tarifa;
                    $valor_debito = 0;
                } else {
                    $valor_credito = 0;
                    $valor_debito = $tarifa + $tot_add - $valor_no_uso + $valor_nc + $descuento;
                    $array_debito[] = array($id, $valor_debito);
                }

                $saldo = $valor_debito + $valor_credito;
                $array_saldo[$i] = $valor_debito + $valor_credito;

                $valor_saldo += $saldo;

                if ($valor_debito > 0 &&  $valor_saldo > 0) {
                    $array_cuotas[] = array($id, $valor_debito, $valor_saldo, $detalle);
                }

                if ($valor_saldo <= 0) {
                    unset($array_cuotas);
                }

                $sHtml .= '<tr ondblclick="detallePaquetes(' . $id . ')" style="cursor: pointer;">';
                $sHtml .= '<td class="' . $colorClass . '">' . $array_t[$tipo] . '</td>';
                $sHtml .= '<td class="' . $colorClass . '">' . fecha_mysql_dmy($fechaPago) . '</td>';
                $sHtml .= '<td class="' . $colorClass . '" >' . $detalle . '</td>';
                $sHtml .= '<td class="' . $colorClass . '" >' . $array_u[$user_web] . '</td>';
                $sHtml .= '<td class="' . $colorClass . '" align="right" title="' . $tarifa . ' + ' . $tot_add . ' - ' . $valor_no_uso . ' + ' . $valor_nc . ' + ' . $descuento . '">' . number_format($valor_debito, 2, '.', ',') . '</td>';
                $sHtml .= '<td class="' . $colorClass . '" align="right">' . number_format(abs($valor_credito), 2, '.', ',') . '</td>';
                $sHtml .= '<td class="' . $colorClass . '" align="right">' . number_format($valor_saldo, 2, '.', ',') . '</td>';
                $sHtml .= '</tr>';

                $totalDebito += $valor_debito;
                $totalCredito += $valor_credito;
                $totalSaldo += $saldo;
                $i++;
            }
            $sHtml .= '<tr>';
            $sHtml .= '<td class="danger text-danger text-right" colspan="4"><strong>TOTAL:</strong></td>';
            $sHtml .= '<td class="danger text-danger text-right"><strong>' . number_format($totalDebito, 2, '.', ',') . '</strong></td>';
            $sHtml .= '<td class="danger text-danger" align="right"><strong>' . number_format(abs($totalCredito), 2, '.', ',') . '</strong></td>';
            $sHtml .= '<td class="danger text-danger" align="right"><strong>' . number_format($totalSaldo, 2, '.', ',') . '</strong></td>';
            $sHtml .= '</tr>';
        }

        $sHtml .= '</table>';

        //recorre valor de cuotas y descuenta pagos
        $sumaCredito = abs($totalCredito);
        if (count($array_debito) > 0) {
            $i = 0;
            foreach ($array_debito as $val_debito) {
                $deb_id = $val_debito[0];
                $deb_val = $val_debito[1];

                $deb_saldo = $sumaCredito - $deb_val;

                $sumaCredito -= $deb_val;

                if ($deb_saldo >= 0) {
                    $array_cuotas_id[$deb_id] = 0;
                } else {
                    $deb_resta = $deb_saldo + $deb_val;
                    if ($deb_resta >= 0) {
                        $array_cuotas_id[$deb_id] = abs($deb_saldo);
                    } else {
                        $array_cuotas_id[$deb_id] = $deb_val;
                    }
                }
            }
            $i++;
        }

        //var_dump($array_cuotas);
        //var_dump($array_cuotas_id);

        if ($op_return == 0) {
            return $sHtml;
        } elseif ($op_return == 1) {
            return array($array_cuotas, $array_cuotas_id);
        }
    }


    /***************************************************
    @ recalcularCuotaValores
    + Suma detalle y actualiza cabecera
     **************************************************/
    function recalcularCuotaValores($id_cuota = 0)
    {

        $oCon = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;


        //query valores detalle de cuotas
        $sql = "SELECT id_pago, tipo, SUM(tarifa) as tarifa, SUM(valor_uso) as valor_uso, SUM(valor_no_uso) as valor_no_uso, SUM(valor_pago) as valor_pago, SUM(valor_nc) as valor_nc, SUM(descuento) as descuento
                from isp.contrato_pago_pack
                WHERE id_contrato = $idContrato AND 
                id_pago = $id_cuota
                GROUP BY 1, 2";

        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                unset($array_p);
                do {
                    $array_p[$oCon->f('id_pago')][$oCon->f('tipo')] = array($oCon->f('tarifa'), $oCon->f('valor_uso'), $oCon->f('valor_no_uso'), $oCon->f('valor_pago'), $oCon->f('valor_nc'), $oCon->f('descuento'));
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        //query id cuotas
        $sql = "SELECT c.id, c.fecha
                from isp.contrato_pago c
                WHERE c.id_contrato = $idContrato AND
                c.id = $id_cuota";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                unset($array_c);
                do {
                    $array_c[] = array($oCon->f('id'));
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();


        //recorre cuotas y actualiza valores
        if (count($array_c) > 0) {
            foreach ($array_c as $val) {

                $idCuotaVal = $val[0];

                //actualiza valor es campos temporales
                $sql = "UPDATE isp.contrato_pago SET tarifa_tmp = tarifa,
                        valor_uso_tmp = valor_uso,
                        valor_no_uso_tmp = valor_no_uso,
                        descuento_tmp = descuento
                        WHERE id = $idCuotaVal AND
                        id_contrato = $idContrato";
                $oCon->QueryT($sql);

                $tarifa_pack = $array_p[$idCuotaVal]['M'][0];
                $valor_uso_pack = $array_p[$idCuotaVal]['M'][1] + $array_p[$idCuotaVal]['A'][1];
                $valor_no_uso_pack = $array_p[$idCuotaVal]['M'][2] + $array_p[$idCuotaVal]['A'][2];
                $valor_pago_pack = $array_p[$idCuotaVal]['M'][3] + $array_p[$idCuotaVal]['A'][3];
                $valor_nc_pack = $array_p[$idCuotaVal]['M'][4] + $array_p[$idCuotaVal]['A'][4];
                $valor_descuento_pack = $array_p[$idCuotaVal]['M'][5] + $array_p[$idCuotaVal]['A'][5];
                $adicional_pack = $array_p[$idCuotaVal]['A'][0];

                if (empty($tarifa_pack)) {
                    $tarifa_pack = 0;
                }

                if (empty($valor_uso_pack)) {
                    $valor_uso_pack = 0;
                }

                if (empty($valor_no_uso_pack)) {
                    $valor_no_uso_pack = 0;
                }

                if (empty($valor_pago_pack)) {
                    $valor_pago_pack = 0;
                }

                if (empty($valor_nc_pack)) {
                    $valor_nc_pack = 0;
                }

                if (empty($valor_descuento_pack)) {
                    $valor_descuento_pack = 0;
                }

                if (empty($adicional_pack)) {
                    $adicional_pack = 0;
                }


                $saldo = $tarifa_pack + $adicional_pack + $valor_nc_pack + $valor_descuento_pack - $valor_pago_pack - $valor_no_uso_pack;

                if ($saldo > 0) {
                    $sql = "UPDATE isp.contrato_pago SET tarifa = '$tarifa_pack',
                        tot_add = '$adicional_pack',
                        valor_uso = '$valor_uso_pack',
                        valor_no_uso = '$valor_no_uso_pack',
                        valor_pago = '$valor_pago_pack',
                        valor_nc = '$valor_nc_pack',
                        descuento = '$valor_descuento_pack',
                        estado_fact = null,
                        facturado = 'N'
                        WHERE id = $idCuotaVal AND
                        id_contrato = $idContrato";
                } else {
                    $sql = "UPDATE isp.contrato_pago SET tarifa = '$tarifa_pack',
                        tot_add = '$adicional_pack',
                        valor_uso = '$valor_uso_pack',
                        valor_no_uso = '$valor_no_uso_pack',
                        valor_pago = '$valor_pago_pack',
                        valor_nc = '$valor_nc_pack',
                        descuento = '$valor_descuento_pack'
                        WHERE id = $idCuotaVal AND
                        id_contrato = $idContrato";
                }

                $oCon->QueryT($sql);
            } //foreach cuotas
        } //fin counto cuotas

        return $id_cuota;
    }

    function copiaProdBode($codigo_prod, $idEmpresa, $idsucursalsesion, $bodega_id_sesion, $idSucursalCliente, $idBodega)
    {
        global $DSN_Ifx, $DSN;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oConP = new Dbo;
        $oConP->DSN = $DSN;
        $oConP->Conectar();

        $oIfxP = new Dbo;
        $oIfxP->DSN = $DSN_Ifx;
        $oIfxP->Conectar();

        $user_web = $_SESSION['U_ID'];

        $sqlControlBode = "SELECT COUNT(*) as control_prod 
                            FROM saeprod 
                            where
                                prod_cod_empr = $idEmpresa and
                                prod_cod_sucu = $idsucursalsesion and
                                prod_cod_prod = '$codigo_prod'";
        $oIfxP->Query($sqlControlBode);
        $control_prod = $oIfxP->f('control_prod');
        $oIfxP->Free();

        if ($control_prod == 0) {
            $sql = "SELECT prod_cod_prod, prod_cod_empr, prod_nom_prod, prod_cod_colr, prod_cod_marc ,
                            prod_cod_tpro, prod_cod_medi, prod_cod_sucu, prod_cod_linp, prod_cod_grpr ,
                            prod_cod_cate, prod_imp_prod, prod_tip_pro, prod_cod_barra, prod_alt_prov ,
                            prod_alt_clie, prod_des_prod, prod_nom_ext, prod_det_prod, prod_lot_sino ,
                            prod_ser_prod, prod_cod_aran, prod_fob_prod, prod_sn_noi, prod_sn_exe ,
                            prod_dsc_prod, prod_uni_caja, prod_stock_neg, prod_pro_prod, prod_cod_gtalla ,
                            prod_cod_talla, prod_cod_gcolor, prod_cod_color, prod_nom_refe, prod_nom_cole ,
                            prod_nom_garan, prod_unid_ped, prod_num_anch, prod_cod_clpv ,icolo_cod_icolo ,
                            prod_int_sistema, prod_int_disp , prod_int_mdisp, prod_val_maxc , prod_val_minc  ,
                            prod_user_web , prod_fec_server,prod_cod_cabl , prod_voip_sn  
                    FROM saeprod
                    WHERE prod_cod_prod = '$codigo_prod' AND 
                    prod_cod_empr = $idEmpresa LIMIT 1";
            if ($oIfxP->Query($sql)) {
                if ($oIfxP->NumFilas() > 0) {
                    do {
                        $prod_cod_prod = $oIfxP->f('prod_cod_prod');
                        $prod_cod_empr = $oIfxP->f('prod_cod_empr');
                        $prod_nom_prod = $oIfxP->f('prod_nom_prod');
                        $prod_cod_colr = $oIfxP->f('prod_cod_colr');
                        $prod_cod_marc = $oIfxP->f('prod_cod_marc');
                        $prod_cod_tpro = $oIfxP->f('prod_cod_tpro');
                        $prod_cod_medi = $oIfxP->f('prod_cod_medi');
                        $prod_cod_sucu = $oIfxP->f('prod_cod_sucu');
                        $prod_cod_linp = $oIfxP->f('prod_cod_linp');
                        $prod_cod_grpr = $oIfxP->f('prod_cod_grpr');
                        $prod_cod_cate = $oIfxP->f('prod_cod_cate');
                        $prod_imp_prod = $oIfxP->f('prod_imp_prod');
                        $prod_tip_pro   = $oIfxP->f('prod_tip_pro');
                        $prod_cod_barra = $oIfxP->f('prod_cod_barra');
                        $prod_alt_prov  = $oIfxP->f('prod_alt_prov');
                        $prod_alt_clie  = $oIfxP->f('prod_alt_clie');
                        $prod_des_prod  = $oIfxP->f('prod_des_prod');
                        $prod_nom_ext   = $oIfxP->f('prod_nom_ext');
                        $prod_det_prod  = $oIfxP->f('prod_det_prod');
                        $prod_lot_sino  = $oIfxP->f('prod_lot_sino');
                        $prod_ser_prod  = $oIfxP->f('prod_ser_prod');
                        $prod_cod_aran  = $oIfxP->f('prod_cod_aran');
                        $prod_fob_prod  = $oIfxP->f('prod_fob_prod');
                        $prod_sn_noi    = $oIfxP->f('prod_sn_noi');
                        $prod_sn_exe    = $oIfxP->f('prod_sn_exe');
                        $prod_dsc_prod  = $oIfxP->f('prod_dsc_prod');
                        $prod_uni_caja  = $oIfxP->f('prod_uni_caja');
                        $prod_stock_neg = $oIfxP->f('prod_stock_neg');
                        $prod_pro_prod  = $oIfxP->f('prod_pro_prod');
                        $prod_cod_gtalla    = $oIfxP->f('prod_cod_gtalla');
                        $prod_cod_talla     = $oIfxP->f('prod_cod_talla');
                        $prod_cod_gcolor    = $oIfxP->f('prod_cod_gcolor');
                        $prod_cod_color     = $oIfxP->f('prod_cod_color');
                        $prod_nom_refe      = $oIfxP->f('prod_nom_refe');
                        $prod_nom_cole      = $oIfxP->f('prod_nom_cole');
                        $prod_nom_garan     = $oIfxP->f('prod_nom_garan');
                        $prod_unid_ped      = $oIfxP->f('prod_unid_ped');
                        $prod_num_anch      = $oIfxP->f('prod_num_anch');
                        $prod_cod_clpv      = $oIfxP->f('prod_cod_clpv');
                        $icolo_cod_icolo    = $oIfxP->f('icolo_cod_icolo');
                        $prod_int_sistema   = $oIfxP->f('prod_int_sistema');
                        $prod_int_disp      = $oIfxP->f('prod_int_disp');
                        $prod_int_mdisp     = $oIfxP->f('prod_int_mdisp');
                        $prod_val_maxc      = $oIfxP->f('prod_val_maxc');
                        $prod_val_minc      = $oIfxP->f('prod_val_minc');
                        $prod_user_web      = $oIfxP->f('prod_user_web');
                        $prod_fec_server    = $oIfxP->f('prod_fec_server');
                        $prod_cod_cabl      = $oIfxP->f('prod_cod_cabl');
                        $prod_voip_sn       = $oIfxP->f('prod_voip_sn');
                    } while ($oIfxP->SiguienteRegistro());
                }
            }
            $oIfxP->Free();

            if (strlen($prod_cod_colr) == 0) {
                $prod_cod_colr = 0;
            }
            if (strlen($prod_pro_prod) == 0) {
                $prod_pro_prod = 0;
            }
            if (strlen($prod_cod_gtalla) == 0) {
                $prod_cod_gtalla = 0;
            }
            if (strlen($prod_cod_talla) == 0) {
                $prod_cod_talla = 0;
            }
            if (strlen($prod_cod_gcolor) == 0) {
                $prod_cod_gcolor = 0;
            }
            if (strlen($prod_cod_color) == 0) {
                $prod_cod_color = 0;
            }
            if (strlen($prod_unid_ped) == 0) {
                $prod_unid_ped = 0;
            }
            if (strlen($prod_int_sistema) == 0) {
                $prod_int_sistema = 0;
            }
            if (strlen($prod_int_disp) == 0) {
                $prod_int_disp = 0;
            }
            if (strlen($prod_int_mdisp) == 0) {
                $prod_int_mdisp = 0;
            }
            if (strlen($prod_cod_cabl) == 0) {
                $prod_cod_cabl = 0;
            }
            if (strlen($prod_num_anch) == 0) {
                $prod_num_anch = 0;
            }
            if (strlen($prod_cod_clpv) == 0) {
                $prod_cod_clpv = 0;
            }
            if (strlen($icolo_cod_icolo) == 0) {
                $icolo_cod_icolo = 0;
            }
            if (strlen($prod_val_maxc) == 0) {
                $prod_val_maxc = 0;
            }
            if (strlen($prod_val_minc) == 0) {
                $prod_val_minc = 0;
            }
            if (strlen($prod_voip_sn) == 0) {
                $prod_voip_sn = 0;
            }

            //INSERT SAEPROD
            $sqlSaeprod = "INSERT INTO saeprod (prod_cod_prod,             prod_cod_empr,                        prod_nom_prod,
                                        prod_fin_prod,              prod_cod_colr,                        prod_cod_marc,
                                        prod_cod_tpro,              prod_cod_medi,                        prod_cod_sucu,
                                        prod_cod_linp,              prod_cod_grpr,                        prod_cod_cate,
                                        prod_imp_prod,              prod_tip_pro,
                                        prod_cod_barra,             prod_alt_prov,                        prod_alt_clie,
                                        prod_des_prod,              prod_nom_ext,                         prod_det_prod,
                                        prod_lot_sino,              prod_ser_prod,                        prod_cod_aran,
                                        prod_fob_prod,              prod_sn_noi,                          prod_sn_exe,
                                        prod_dsc_prod,              prod_uni_caja,                        prod_stock_neg,
                                        prod_pro_prod,              prod_cod_gtalla,                      prod_cod_talla,

                                        prod_cod_gcolor,            prod_cod_color,                       prod_nom_refe,
                                        prod_nom_cole,              prod_nom_garan,						  prod_unid_ped ,
                                        prod_img_inv	,           prod_num_anch,						  prod_cod_clpv,
                                        icolo_cod_icolo	,           prod_int_sistema,					  prod_int_disp,
                                        prod_int_mdisp	,           prod_val_maxc ,						  prod_val_minc ,												
                                        prod_user_web,				prod_fec_server  ,					  prod_cod_cabl,
                                        prod_voip_sn
                                        )                                                             
                                values( '$codigo_prod',            $idEmpresa,                          '$prod_nom_prod',
                                        1,                          '$prod_cod_colr',                    '$prod_cod_marc',
                                        '$prod_cod_tpro',           '$prod_cod_medi',                    '$idsucursalsesion',
                                        '$prod_cod_linp',           '$prod_cod_grpr',                    '$prod_cod_cate',
                                        0,                  		'$prod_tip_pro',                                      
                                        '$prod_cod_barra' ,         '$prod_alt_prov',                    '$prod_alt_clie',
                                        '$prod_des_prod',           '$prod_nom_ext',                     '$prod_det_prod',
                                        '$prod_lot_sino',           '$prod_ser_prod',                    '$prod_cod_aran',
                                        '$prod_fob_prod',           '$prod_sn_noi',                      '$prod_sn_exe',
                                        '$prod_dsc_prod',           '$prod_uni_caja',                    '$prod_stock_neg',
                                        '$prod_pro_prod',           '$prod_cod_gtalla',                  '$prod_cod_talla',
                                        '$prod_cod_gcolor',         '$prod_cod_color',                  '$prod_nom_refe',
                                        '$prod_nom_cole',           '$prod_nom_garan',				    '$prod_unid_ped' ,
                                        ''	,			            '$prod_num_anch'	,				'$prod_cod_clpv',
                                        '$icolo_cod_icolo'	,	    '$prod_int_sistema',				'$prod_int_disp',
                                        '$prod_int_mdisp' ,		    '$prod_val_maxc',		            '$prod_val_minc'	,
                                        $user_web,					CURRENT_DATE,						'$prod_cod_cabl',
                                        '$prod_voip_sn');";

            $oIfxP->QueryT($sqlSaeprod);
        }

        //SELECT PRBO
        $sql = "SELECT *
                FROM saeprbo
                WHERE prbo_cod_prod = '$codigo_prod' AND 
                prbo_cod_empr = $idEmpresa";
        if ($oIfxP->Query($sql)) {
            if ($oIfxP->NumFilas() > 0) {
                do {
                    $prbo_cod_prod  = $oIfxP->f('prbo_cod_prod');
                    $prbo_can_req   = $oIfxP->f('prbo_can_req');
                    $prbo_cod_bode  = $oIfxP->f('prbo_cod_bode');
                    $prbo_cta_inv   = $oIfxP->f('prbo_cta_inv');
                    $prbo_cta_cven  = $oIfxP->f('prbo_cta_cven');
                    $prbo_cta_vent  = $oIfxP->f('prbo_cta_vent');
                    $prbo_cta_desc  = $oIfxP->f('prbo_cta_desc');
                    $prbo_cta_devo  = $oIfxP->f('prbo_cta_devo');
                    $prbo_cta_ideb  = $oIfxP->f('prbo_cta_ideb');
                    $prbo_cta_icre  = $oIfxP->f('prbo_cta_icre');
                    $prbo_cod_unid  = $oIfxP->f('prbo_cod_unid');
                    $prbo_cod_empr  = $oIfxP->f('prbo_cod_empr');
                    $prbo_cod_sucu  = $oIfxP->f('prbo_cod_sucu');
                    $prbo_est_prod  = $oIfxP->f('prbo_est_prod');
                    $prbo_iva_sino  = $oIfxP->f('prbo_iva_sino');
                    $prbo_iva_porc  = $oIfxP->f('prbo_iva_porc');
                    $prbo_cos_prod  = $oIfxP->f('prbo_cos_prod');
                    $prbo_ice_sino  = $oIfxP->f('prbo_ice_sino');
                    $prbo_ice_porc  = $oIfxP->f('prbo_ice_porc');
                    $prbo_irbp_sino = $oIfxP->f('prbo_irbp_sino');
                    $prbo_val_irbp  = $oIfxP->f('prbo_val_irbp');
                    $prbo_cre_prod  = $oIfxP->f('prbo_cre_prod');
                    $prbo_sma_prod  = $oIfxP->f('prbo_sma_prod');
                    $prbo_smi_prod  = $oIfxP->f('prbo_smi_prod');
                } while ($oIfxP->SiguienteRegistro());
            }
        }
        $oIfxP->Free();

        $sql = "SELECT max(id) as id from isp.int_paquetes WHERE id_empresa = $idEmpresa AND prod_cod_prod = '$codigo_prod'";
        $idPaquete = consulta_string_func($sql, 'id', $oConP, 0);

        if (strlen($prbo_ice_porc) == 0) {
            $prbo_ice_porc = 0;
        }

        //INSERT SAEPRBO
        $sqlSaeprbo = "INSERT INTO saeprbo (   prbo_cod_prod,          prbo_can_req,           prbo_cod_bode,          prbo_cta_inv ,      									 
                                        prbo_cta_cven,          prbo_cta_vent,          prbo_cta_desc,          prbo_cta_devo,      
                                        prbo_cta_ideb,          prbo_cta_icre,          prbo_cod_unid,          prbo_cod_empr,  
                                        prbo_cod_sucu,          prbo_est_prod,          prbo_iva_sino,          prbo_iva_porc, 
                                        prbo_cos_prod,          prbo_ice_sino,          prbo_ice_porc,          prbo_irbp_sino,    
                                        prbo_val_irbp,	        prbo_cre_prod,		    prbo_sma_prod,		    prbo_smi_prod )
                        VALUES(         '$codigo_prod',         0,                      $bodega_id_sesion,      '$prbo_cta_inv',    
                                        '$prbo_cta_cven',       '$prbo_cta_vent',       '$prbo_cta_desc',       '$prbo_cta_devo',
                                        '$prbo_cta_ideb',       '$prbo_cta_icre',       $prbo_cod_unid,         $idEmpresa,         
                                        $idsucursalsesion,      'A',                    '$prbo_iva_sino',       '$prbo_iva_porc' ,         
                                        '$prbo_cos_prod',       '$prbo_ice_sino',       $prbo_ice_porc,         '$prbo_irbp_sino',   
                                        0,        '0',       '0',       '0' 
                                ); ";
        $oIfxP->QueryT($sqlSaeprbo);

        //INSERT isp.int_PAQUETES_SUCURSAL
        if ($idPaquete > 0) {
            $sql = "insert into isp.int_paquetes_sucursal (id_empresa, id_sucursal, id_paquete, estado) values($idEmpresa, $idsucursalsesion, $idPaquete, 'A')";
            $oConP->QueryT($sql);
        }

        //INSERT PRECIOS
        $sqlSaeppr = "INSERT INTO saeppr (  ppr_cod_ppr,	        ppr_cod_prod, 	    ppr_cod_bode,	    ppr_cod_empr,
                                            ppr_cod_sucu,	        ppr_pre_raun,	    ppr_cod_nomp,       ppr_imp_ppr ) 
                            values (        1,			            '$codigo_prod',     $bodega_id_sesion,  $idEmpresa,
                                            $idsucursalsesion,	    0,	                1,		            'S' )";
        $oIfxP->QueryT($sqlSaeppr);


        return true;
    }

    //*****************************   FUNCIONES PARA CORTES - RECONEXIONES - CAMBIOS DE PLAN  //PG 04-07-2022  SISCONTI// *******************************//

    //------ ACCIONES POR SERVICIOS ------ //

    /*FUNCION PARA CORTAR EQUIPO - INVOCA A LA FUNCION PARA GENERAR LA OS*/
    function cortarEquipo($datos)
    {

        $oCon = $this->oCon;

        $id_empresa = $datos['id_empresa'];
        $id_usuario = $datos['usuario'];
        $id_contrato = $datos['id_contrato'];
        $id_clpv = $datos['id_clpv'];
        $id_sucursal = $datos['id_sucursal'];
        $id_caja_pack = $datos['id_caja_pack'];
        $serial = $datos['serial'];
        $fecha_completa = $datos['fecha_considerar'];
        $fecha_considerar = $datos['fecha_considerar'];
        $fecha_considerar = explode("-", $fecha_considerar);
        $dia = $fecha_considerar[2];
        $mes = $fecha_considerar[1];
        $anio = $fecha_considerar[0];
        $comentario_os = $datos['comentario_os'];
        $ejecuta = $datos['ejecuta'];
        $genera_orden = $datos['genera_orden'];
        $estado = "C";
        $corte = "S";
        $reconexion = "N";

        $resp = false;

        //OBTENER EL PARAMETRO DE DIAS CONSUMIDOS
        $sql = "SELECT dias_consumidos, ejecuta_cr_sn from isp.int_parametros WHERE id_empresa=$id_empresa AND id_sucursal=$id_sucursal";
        $oCon->Query($sql);
        $dias_consumidos    = $oCon->f('dias_consumidos');
        $ejecuta_cr_sn      = $oCon->f('ejecuta_cr_sn');
        $oCon->Free();

        if ($ejecuta_cr_sn == 'S') {
            $ejecuta = 'S';
        }

        if ($genera_orden == 'N') {
            $ejecuta = 'N';
        }
        //OBTENER EL CODIGO DEL PRODUCTO 
        $sql = "SELECT cod_prod, id_caja
                from isp.int_contrato_caja_pack
                WHERE id_contrato=$id_contrato AND id = $id_caja_pack";
        $oCon->Query($sql);
        $cod_prod = $oCon->f('cod_prod');
        $id_caja = $oCon->f('id_caja');
        $oCon->Free();

        if ($ejecuta_cr_sn == 'S' || $ejecuta == 'S') {
            if ($dias_consumidos == 'S') {

                $sql = "UPDATE isp.int_contrato_caja_pack SET estado='C', fecha_corte='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja_pack' AND activo = 'S' AND estado not in ('E')";
                $oCon->QueryT($sql);

                #MODIFICA CUOTAS SIN CAMBIO DE PLAN
                $sql = "UPDATE isp.contrato_pago_pack SET 
                        estado='$estado',
                        fecha_server=NOW(),
                        dias_uso=$dia,
                        dias_no_uso=DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa'),
                        valor_uso=((tarifa/dias)*$dia),
                        valor_no_uso=((tarifa/dias)*DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')),
                        valor_dia=(tarifa/dias),
                        corte='$corte',
                        reconexion='$reconexion',
                        fecha_corte='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv 
                        AND cod_prod='$cod_prod' AND estado = 'A' AND 
                        fecha_cambio_plan IS NULL AND
                        fecha=LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);

                #MODIFICA CUOTAS CON CAMBIO DE PLAN
                $sql = "UPDATE isp.contrato_pago_pack SET 
                        estado='$estado',
                        fecha_server=NOW(),
                        dias_uso=(dias_uso-DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')),
                        dias_no_uso=(DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')+dias_no_uso),
                        valor_uso=((tarifa/dias)*(dias_uso-DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa'))),
                        valor_no_uso=((tarifa/dias)*(DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')+dias_no_uso)),
                        valor_dia=(tarifa/dias),
                        corte='$corte',
                        reconexion='$reconexion',
                        fecha_corte='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv 
                        AND cod_prod='$cod_prod' AND estado = 'A' AND 
                        fecha_cambio_plan IS NOT NULL AND
                        fecha=LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);

                #MODIFICA SIGUIENTES CUOTAS
                $sql = "UPDATE isp.contrato_pago_pack 
                        SET 
                        estado='$estado',
                        fecha_server=NOW(),
                        corte='$corte',
                        reconexion='$reconexion',
                        dias_uso=0,
                        dias_no_uso=extract(day from LAST_DAY('$fecha_completa')),
                        valor_uso=0,
                        valor_no_uso=tarifa,
                        fecha_corte='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND fecha>LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);

                //Actualiza la cuota actual
                $sql = "UPDATE isp.contrato_pago SET
                        valor_uso=
                            (
                            SELECT SUM(valor_uso) 
                            from isp.contrato_pago_pack 
                            WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                            ),
                        valor_no_uso=
                            (
                            SELECT SUM(valor_no_uso) 
                            from isp.contrato_pago_pack 
                            WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                            )
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha=LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);

                //Actualiza las siguientes cuotas
                $sql = "UPDATE isp.contrato_pago SET dias_uso=0,dias_no_uso=0,
                        valor_uso=(
                            SELECT SUM(precio) 
                            from isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A' AND activo = 'S' 
                        ),
                        valor_no_uso=(
                            SELECT SUM(precio) 
                            from isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='C' AND activo = 'S' 
                        )
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);

                //Contar cuantos servicios activos tiene el contrato para cortarlo
                $sql = "SELECT COUNT(id) AS id 
                        from isp.int_contrato_caja_pack
                        WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado='A' AND activo = 'S'";
                $oCon->Query($sql);
                $servicio_activos = $oCon->f('id');
                $oCon->Free();

                $sql = "UPDATE isp.contrato_clpv SET fecha_c_corte='$fecha_completa'
                        WHERE id=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);

                if ($servicio_activos == 0) {
                    $sql = "UPDATE isp.contrato_clpv SET estado='CO',fecha_server=NOW(),fecha_c_corte='$fecha_completa'
                            WHERE id=$id_contrato AND id_clpv=$id_clpv";
                    $oCon->QueryT($sql);

                    $sql = "UPDATE isp.contrato_pago 
                            SET estado = 'CO', valor_uso=0, valor_no_uso=0
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY('$fecha_completa')";
                    $oCon->QueryT($sql);
                }

                //Contar cuantos servicios activos tiene el equipo para cortarlo
                $sql = "SELECT COUNT(id) AS id 
                        from isp.int_contrato_caja_pack
                        WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado='A' AND activo = 'S' and id_caja = $id_caja";
                $oCon->Query($sql);
                $servicio_activos_indi = $oCon->f('id');
                $oCon->Free();

                if ($servicio_activos_indi == 0) {
                    $sql = "UPDATE isp.int_contrato_caja SET estado='$estado',fecha_corte='$fecha_completa',fecha_server=NOW() 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja'";
                    $oCon->QueryT($sql);
                }

                if ($ejecuta_cr_sn == 'S' && $ejecuta == 'S') {
                    $resp = $this->generarOsCorteEquipo($datos);
                } else {
                    $resp = true;
                }
            } else {

                $sql = "UPDATE isp.int_contrato_caja_pack SET estado='C' 
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja_pack' AND activo = 'S' AND estado not in ('E')";
                $oCon->QueryT($sql);

                //ACTUALIZA isp.contrato_pago_pack
                #MODIFICA CUOTAS SIN CAMBIO DE PLAN
                $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                        dias_uso=0,dias_no_uso=0,
                        valor_uso=0,valor_no_uso=0,
                        valor_dia=(tarifa/dias),corte='$corte',reconexion='$reconexion',
                        fecha_corte='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv 
                        AND cod_prod='$cod_prod' AND estado='A' AND 
                        fecha_cambio_plan IS NULL AND
                        fecha=LAST_DAY('$fecha_completa') AND
                        valor_pago = 0";
                $oCon->QueryT($sql);

                #MODIFICA CUOTAS CON CAMBIO DE PLAN
                $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                        corte='$corte',reconexion='$reconexion',
                        fecha_corte='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv 
                        AND cod_prod='$cod_prod' AND estado='A' AND 
                        fecha_cambio_plan IS NOT NULL AND
                        fecha=LAST_DAY('$fecha_completa') AND
                        valor_pago = 0";
                $oCon->QueryT($sql);

                //MODIFICA LAS SIGUIENTES CUOTAS
                $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                        corte='$corte',
                        reconexion='$reconexion',
                        dias_uso=0,
                        dias_no_uso=extract(day from LAST_DAY('$fecha_completa')),
                        valor_uso=0,
                        valor_no_uso=tarifa,
                        fecha_corte='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND fecha>LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);

                //ACTUALIZA isp.contrato_pago
                //Actualiza la cuota actual
                $sql = "UPDATE isp.contrato_pago SET
                        valor_uso=
                            (
                            SELECT SUM(valor_uso) 
                            from isp.contrato_pago_pack 
                            WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                            ),
                        valor_no_uso=
                            (
                            SELECT SUM(valor_no_uso) 
                            from isp.contrato_pago_pack 
                            WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                            )
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha=LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);

                //Actualiza la cuota de los siguientes meses
                $sql = "UPDATE isp.contrato_pago 
                        SET  dias_uso=0,dias_no_uso=0,
                        valor_uso=(
                            SELECT SUM(precio) 
                            from isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A' AND activo = 'S' 
                        ),
                        valor_no_uso=(
                            SELECT SUM(precio) 
                            from isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='C' AND activo = 'S' 
                        )
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);

                //Contar cuantos servicios activos tiene el contrato para cortarlo
                $sql = "SELECT COUNT(id) AS id 
                        from isp.int_contrato_caja_pack
                        WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado='A' AND activo = 'S'";
                $oCon->Query($sql);
                $servicio_activos = $oCon->f('id');
                $oCon->Free();

                $sql = "UPDATE isp.contrato_clpv SET fecha_c_corte='$fecha_completa'
                        WHERE id=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);

                if ($servicio_activos == 0) {
                    $sql = "UPDATE isp.contrato_clpv SET estado='CO',fecha_server=NOW(),fecha_c_corte='$fecha_completa'
                            WHERE id=$id_contrato AND id_clpv=$id_clpv";
                    $oCon->QueryT($sql);

                    $sql = "UPDATE isp.contrato_pago 
                            SET estado = 'CO', valor_uso=0, valor_no_uso=0
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY('$fecha_completa')";
                    $oCon->QueryT($sql);
                }

                //Contar cuantos servicios activos tiene el equipo para cortarlo
                $sql = "SELECT COUNT(id) AS id 
                        from isp.int_contrato_caja_pack
                        WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado='A' AND activo = 'S' and id_caja = $id_caja";
                $oCon->Query($sql);
                $servicio_activos_indi = $oCon->f('id');
                $oCon->Free();

                if ($servicio_activos_indi == 0) {
                    $sql = "UPDATE isp.int_contrato_caja SET estado='$estado',fecha_corte='$fecha_completa',fecha_server=NOW() 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja'";
                    $oCon->QueryT($sql);
                }

                if ($ejecuta_cr_sn == 'S' && $ejecuta == 'S') {
                    $resp = $this->generarOsCorteEquipo($datos);
                } else {
                    $resp = true;
                }
            }
        } else {
            $sql = "UPDATE isp.int_contrato_caja_pack SET estado='PP' 
                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja_pack' AND activo = 'S' AND estado not in ('E')";
            $oCon->QueryT($sql);

            //Contar cuantos servicios activos tiene el equipo para cortarlo
            $sql = "SELECT COUNT(id) AS id 
                    from isp.int_contrato_caja_pack
                    WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado='A' AND activo = 'S' and id_caja = $id_caja";
            $oCon->Query($sql);
            $servicio_activos_indi = $oCon->f('id');
            $oCon->Free();

            if ($servicio_activos_indi == 0) {
                $sql = "UPDATE isp.int_contrato_caja SET estado='PP',fecha_corte='$fecha_completa',fecha_server=NOW() 
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja'";
                $oCon->QueryT($sql);
            }

            $sql = "SELECT COUNT(id) AS id 
                    from isp.int_contrato_caja_pack
                    WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado='A' AND activo = 'S'";
            $oCon->Query($sql);
            $servicio_activos = $oCon->f('id');
            $oCon->Free();

            if ($servicio_activos == 0) {
                $sql = "UPDATE isp.contrato_clpv SET estado='PC',fecha_server=NOW(),fecha_c_corte='$fecha_completa'
                        WHERE id=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);
            }

            $resp = $this->generarOsCorteEquipo($datos);
        }

        return $resp;
    }
    /* FUNCION PARA GENERAR LA OS DE CORTE DE EQUIPO */
    function generarOsCorteEquipo($datos)
    {

        global $DSN, $DSN_Ifx;

        $oCon2 = new Dbo;
        $oCon2->DSN = $DSN;
        $oCon2->Conectar();

        $oCon = $this->oCon;

        $id_empresa = $datos['id_empresa'];
        $id_usuario = $datos['usuario'];
        $id_contrato = $datos['id_contrato'];
        $id_clpv = $datos['id_clpv'];
        $id_sucursal = $datos['id_sucursal'];
        $id_caja_pack = $datos['id_caja_pack'];
        $serial = $datos['serial'];
        $detalle = $datos['comentario_os'];
        $id_motivo = $datos['id_motivo'];

        $respuesta = false;

        $sql = "SELECT id_caja
                from isp.int_contrato_caja_pack
                WHERE id_contrato=$id_contrato AND id = $id_caja_pack";
        $oCon->Query($sql);
        $id_caja = $oCon->f('id_caja');
        $oCon->Free();

        $sql = "SELECT ejecuta_cr_sn from isp.int_parametros WHERE id_empresa=$id_empresa AND id_sucursal=$id_sucursal";
        $oCon->Query($sql);
        $ejecuta_cr_sn      = $oCon->f('ejecuta_cr_sn');
        $oCon->Free();

        #LA DESCRIPCION SE PUEDE VISUALIZAR EN LA TABLA isp.int_tipo_proceso de MYSQL
        $descripcion = "CORTE";
        $sql = "SELECT id from isp.int_tipo_proceso WHERE descripcion LIKE '%$descripcion%' LIMIT 1";
        $oCon->Query($sql);
        $tipo_servicio = $oCon->f('id');
        $oCon->Free();

        if ($tipo_servicio != null) {

            if (strlen($id_motivo) == 0) {
                #EL MOTIVO SE PUEDE VISUALIZAR EN LA TABLA isp.int_motivos_canc de MYSQL
                $sql = "SELECT id from isp.int_motivos_canc WHERE id_proceso = $tipo_servicio LIMIT 1";
                $oCon->Query($sql);
                $resultado = $oCon->f('id');
                $oCon->Free();

                if ($resultado != null) {
                    $motivo = $resultado = $oCon->f('id');
                } else {
                    $motivo = "CORTE DE SERVICIO POR MORA";
                    $sql = "insert into isp.int_motivos_canc(id_proceso,motivo) 
                            VALUES ($tipo_servicio,'$motivo')";
                    $oCon->QueryT($sql);

                    $sql = "SELECT id from isp.int_motivos_canc WHERE id_proceso = $tipo_servicio AND motivo = '$motivo' LIMIT 1";
                    $oCon->Query($sql);
                    $motivo = $oCon->f('id');
                    $oCon->Free();
                }
            } else {
                $motivo = $id_motivo;
            }

            if ($motivo != null) {

                #SABER SI EL CONTRATO CUENTA CON TELEVISION ANALOGA PARA DEFINIR EL TIPO DE ESTADO
                $sql = "SELECT id_tipo_prod 
                        from isp.int_contrato_caja
                        WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND id = $id_caja";
                $oCon->Query($sql);
                $id_tipo_prod_c = $oCon->f('id_tipo_prod');
                $oCon->Free();

                #CONTROLAR EL ESTADO DEPENDIENDO DEL TIPO DE SERVICIO

                if ($ejecuta_cr_sn == 'S') {
                    if ($id_tipo_prod_c == 1) {
                        $estado = "PE";
                    } else {
                        $estado = "TE";
                    }
                } else {
                    $estado = "PE";
                }

                //CONTROL DE ORDEN DE TRABAJO
                /* $sql = "SELECT COUNT(id) as id from isp.instalacion_clpv WHERE
                                        id_clpv    = $id_clpv AND
                                        id_contrato= $id_contrato AND
                                        estado     = 'PE' and
                                        id_proceso = $tipo_servicio AND
                                        id_franja = $id_tipo_prod_c";
                    $oCon->Query($sql);
                    $ordenes_pendientes = $oCon->f('id');
                    $oCon->Free();

                    if ($ordenes_pendientes == 0){
                        
                    } */

                //DIRECCION DEL CONTRATO
                $sql = "SELECT id_dire, id_sucursal 
                                    from isp.contrato_clpv 
                                    WHERE id = $id_contrato AND 
                                    id_clpv = $id_clpv";

                $oCon->Query($sql);
                $id_dire = $oCon->f('id_dire');
                $id_sucu = $oCon->f('id_sucursal');
                $oCon->Free();

                if ($id_dire == null) {
                    $id_dire = 1;
                }

                #OBTENER EL TIPO DEL PROCESO
                #sql = """
                #SELECT descripcion, etiqueta, color 
                #                from isp.int_tipo_proceso 
                #                WHERE id = {tipo_servicio}
                #""".format(tipo_servicio=tipo_servicio)

                #resultado = self.mysql.query(sql)
                #descripcion_ts = resultado[0]['descripcion']
                #etiqueta_ts = resultado[1]['etiqueta']
                #color_ts = resultado[2]['color']

                #OBTENER EL SECUENCUAL
                $sql = "SELECT CAST (max(secuencial) AS INTEGER)  + 1 as secuencial 
                            from isp.instalacion_clpv
                            WHERE id_proceso = $tipo_servicio";

                $oCon->Query($sql);
                $secuencial = $oCon->f('secuencial');
                $oCon->Free();

                if ($secuencial == null) {
                    $secuencial = 1;
                };

                $secuencial_fin = str_pad($secuencial, 9, 0, STR_PAD_LEFT);
                $fecha_actual = date('Y-m-d');
                $fechaServer = date("Y-m-d H:i:s");

                #INGRESAR LA ORDEN DE SERVICIO
                $sql = "INSERT into isp.instalacion_clpv(id_empresa, id_sucursal, id_sucursal_ori, id_proceso, 
                                                secuencial, id_clpv, id_contrato, fecha, 
                                                user_web, fecha_server, observaciones, id_direccion, 
                                                id_franja, id_motivo, id_prioridad, solicita, adjunto, 
                                                observacion_cliente,estado_contrato, reconexion_pago, 
                                                fecha_inicio, fecha_fin, estado)
                            VALUES($id_empresa, $id_sucursal, $id_sucu, $tipo_servicio, '$secuencial_fin', 
                            $id_clpv, $id_contrato, '$fecha_actual',$id_usuario, '$fechaServer', '$detalle', $id_dire, 
                            $id_tipo_prod_c, $motivo, 1, '', '', '','AP', 'N', '$fechaServer', '$fechaServer', '$estado')";
                $oCon->QueryT($sql);

                #OBTENER EL ULTIMO ID DE LA TABLA instalacion clpv PARA INSERTARLO EN isp.instalacion_prod
                $sql = "SELECT max(id) AS id 
                            from isp.instalacion_clpv
                            WHERE 
                            id_proceso = $tipo_servicio AND 
                            id_clpv = $id_clpv AND id_contrato = $id_contrato";

                $oCon->Query($sql);
                $id_instalacion = $oCon->f('id');
                $oCon->Free();

                //INGRESA AUDI PARA POSTERIOR ANULACION
                $sql_audi = "SELECT estado
                            from isp.contrato_clpv
                            WHERE id=$id_contrato";
                $oCon2->Query($sql_audi);
                $estado_contrato_a = $oCon2->f('estado');
                $oCon2->Free();

                $sql_audi = "SELECT estado
                            from isp.int_contrato_caja
                            WHERE id_contrato=$id_contrato AND id = $id_caja";
                $oCon2->Query($sql_audi);
                $estado_equipo_a = $oCon2->f('estado');
                $oCon2->Free();

                $sql_audi = "SELECT estado
                            from isp.int_contrato_caja_pack
                            WHERE id_contrato=$id_contrato AND id = $id_caja_pack";
                $oCon2->Query($sql_audi);
                $estado_plan_a = $oCon2->f('estado');
                $oCon2->Free();

                $sql = "INSERT INTO isp.int_red_peticiones(id_dispositivo,      id_contrato,            id_equipo,          id_instalacion, 
                                                                id_usuario,         estado,                 estado_equipo,      fecha_server,
                                                                planes,             id_pa)
                                                        VALUES(1,                   $id_contrato,           $id_caja,           $id_instalacion,    
                                                                $id_usuario,        '$estado_contrato_a',   '$estado_equipo_a', CURRENT_DATE,
                                                                '$estado_plan_a',   $id_caja_pack)";
                $oCon->QueryT($sql);

                if ($estado == 'TE') {
                    #INSERTAR ORDEN DE SERVICIO EN LA TABLA isp.instalacion_ejecucion
                    $sql = "insert into isp.instalacion_ejecucion(id_empresa, id_sucursal, id_clpv, id_contrato, 
                                                    id_instalacion, fecha, estado, user_web, fecha_server, 
                                                    observacion_tecnico)
                                VALUES($id_empresa, $id_sucursal, $id_clpv, $id_contrato, '$id_instalacion', 
                                '$fecha_actual', 'A', $id_usuario, '$fechaServer', 'Corte de servicios automatica')";
                    $oCon->QueryT($sql);
                }

                $sql = "insert into isp.instalacion_prod(id_empresa, id_sucursal, id_clpv, id_contrato, 
                                                    id_instalacion, id_caja, estado, cambio_sn, 
                                                    fecha_ejecucion)
                            VALUES($id_empresa, $id_sucursal, $id_clpv, $id_contrato, '$id_instalacion', 
                            $id_caja, '$estado', 'N', '$fecha_actual')";
                $oCon->QueryT($sql);

                $respuesta = true;
            }
        }

        return $respuesta;
    }
    /*FUNCION PARA RECONECTAR EQUIPO - INVOCA A LA FUNCION PARA GENERAR LA OS*/
    function reconectarEquipo($datos, $op = 0)
    {

        $oCon = $this->oCon;

        $id_empresa = $datos['id_empresa'];
        $id_usuario = $datos['usuario'];
        $id_contrato = $datos['id_contrato'];
        $id_clpv = $datos['id_clpv'];
        $id_sucursal = $datos['id_sucursal'];
        $serial = $datos['serial'];
        $id_caja_pack = $datos['id_caja_pack'];
        $fecha_completa = $datos['fecha_considerar'];
        $fecha_considerar = $datos['fecha_considerar'];
        $fecha_considerar = explode("-", $fecha_considerar);
        $dia = $fecha_considerar[2];
        $mes = $fecha_considerar[1];
        $anio = $fecha_considerar[0];
        $comentario_os = $datos['comentario_os'];
        $ejecuta = $datos['ejecuta'];
        $genera_orden = $datos['genera_orden'];
        $estado = "A";
        $corte = "N";
        $reconexion = "S";

        $resp = false;

        //OBTENER EL PARAMETRO DE DIAS CONSUMIDOS
        $sql = "SELECT dias_consumidos, calculo_linea, dias_calculo_linea, ejecuta_cr_sn from isp.int_parametros WHERE id_empresa=$id_empresa AND id_sucursal=$id_sucursal";
        $oCon->Query($sql);
        $dias_consumidos    = $oCon->f('dias_consumidos');
        $calculo_linea      = $oCon->f('calculo_linea');
        $dias_calculo_linea = $oCon->f('dias_calculo_linea');
        $ejecuta_cr_sn      = $oCon->f('ejecuta_cr_sn');
        $oCon->Free();

        if ($ejecuta_cr_sn == 'S') {
            $ejecuta = 'S';
        }

        if ($genera_orden == 'N') {
            $ejecuta = 'N';
        }

        //OBTENER EL CODIGO DEL PRODUCTO 
        $sql = "SELECT cod_prod, id_caja
                from isp.int_contrato_caja_pack
                WHERE id_contrato=$id_contrato AND id = $id_caja_pack";
        $oCon->Query($sql);
        $cod_prod               = $oCon->f('cod_prod');
        $id_caja                = $oCon->f('id_caja');
        $oCon->Free();
        $valida_dias = true;

        if ($ejecuta_cr_sn == 'S' || $ejecuta == 'S') {
            if ($calculo_linea == "S") {

                //CONTROL PARA VALIDAR CON FECHA INGRESADA
                if (!empty($fecha_completa)) {
                    //OBTENER EL ULTIMO DIA DE CORTE
                    $sql = "SELECT extract(day from fecha_corte) AS dia_u_corte, month(fecha_corte) AS mes_u_corte 
                            from isp.int_contrato_caja_pack
                            WHERE cod_prod = '$cod_prod' and activo = 'S' and estado != 'I'";
                    $oCon->Query($sql);
                    $dia_u_corte    = $oCon->f('dia_u_corte');
                    $dia_actual     = $dia;
                    $mes_actual     = $mes;
                    $mes_u_corte    = $oCon->f('mes_u_corte');
                    $oCon->Free();
                } else {
                    //OBTENER EL ULTIMO DIA DE CORTE
                    $sql = "SELECT extract(day from fecha_corte) AS dia_u_corte, extract(day from NOW()) AS dia_actual, month(NOW()) AS mes_actual, month(fecha_corte) AS mes_u_corte 
                            from isp.int_contrato_caja_pack
                            WHERE cod_prod = '$cod_prod' and activo = 'S' and estado != 'I'";
                    $oCon->Query($sql);
                    $dia_u_corte    = $oCon->f('dia_u_corte');
                    $dia_actual     = $oCon->f('dia_actual');
                    $mes_actual     = $oCon->f('mes_actual');
                    $mes_u_corte    = $oCon->f('mes_u_corte');
                    $oCon->Free();
                }

                if ($mes_actual == $mes_u_corte) {
                    $control = $dia_actual - $dia_u_corte;

                    if ($control <= $dias_calculo_linea) {
                        $valida_dias = false;
                    }
                }
            }

            if ($dias_consumidos == 'S' && $valida_dias) {
                //ACTUALIZAR ESTADO DE EQUIPO
                $sql = "UPDATE isp.int_contrato_caja SET estado='$estado',fecha_reconexion='$fecha_completa',fecha_server=NOW() 
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja'";
                $oCon->QueryT($sql);

                $sql = "UPDATE isp.int_contrato_caja_pack SET estado='A',fecha_reconexion='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id = $id_caja_pack AND estado not in ('E')";
                $oCon->QueryT($sql);

                if ($op == 0) {
                    //VALIDACION CUANDO SE RECONECTA DESPES DE MESES AL CORTE

                    $mes_fecha_rec = substr($fecha_completa, 5, 2);
                    $mes_fecha_rec  = intval($mes_fecha_rec);

                    $sql = "SELECT fecha_corte
                            from isp.contrato_pago_pack
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND
                                fecha=LAST_DAY('$fecha_completa') AND
                                estado != 'I'";
                    $oCon->Query($sql);
                    $fecha_corte_valida     = $oCon->f('fecha_corte');
                    $oCon->Free();

                    //VALIDAR SI LA FECHA DE CORTE ESTA LLENA PARA HACER LOS CALCULOS
                    if (strlen($fecha_corte_valida) > 0) {

                        $mes_u_corte    = date("m", strtotime($fecha_corte_valida));
                        $mes_reconexion = date("m", strtotime($fecha_completa));

                        if ($mes_u_corte == $mes_reconexion) {
                            $sql = "UPDATE isp.contrato_pago_pack SET 
                                    estado='$estado',
                                    fecha_server=NOW(),
                                    dias_uso=((extract(DAY from fecha_corte)+DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')) - extract(DAY from fecha_cambio_plan)),
                                    dias_no_uso=((extract(DAY from TIMESTAMP '$fecha_completa')-extract(DAY from fecha_corte))+extract(DAY from fecha_cambio_plan)),
                                    valor_uso=((tarifa/dias)*((extract(DAY from fecha_corte)+DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa'))-extract(DAY from fecha_cambio_plan))),
                                    valor_no_uso=((tarifa/dias)*((extract(DAY from TIMESTAMP '$fecha_completa')-extract(DAY from fecha_corte))+extract(DAY from fecha_cambio_plan))),
                                    valor_dia=(tarifa/dias),corte='$corte',reconexion='$reconexion'
                                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND
                                    fecha_cambio_plan IS NOT NULL AND 
                                    fecha=LAST_DAY('$fecha_completa') AND
                                    estado != 'I'";
                            $oCon->QueryT($sql);

                            $sql = "UPDATE isp.contrato_pago_pack SET 
                                    estado='$estado',
                                    fecha_server=NOW(),
                                    dias_uso= (extract(DAY from fecha_corte)+DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')),
                                    dias_no_uso=(extract(DAY from TIMESTAMP '$fecha_completa')-extract(DAY from fecha_corte)),
                                    valor_uso=((tarifa/dias)*(extract(DAY from fecha_corte)+DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa'))),
                                    valor_no_uso=((tarifa/dias)*(extract(DAY from TIMESTAMP '$fecha_completa')-extract(DAY from fecha_corte))),
                                    valor_dia=(tarifa/dias),
                                    corte='$corte',
                                    reconexion='$reconexion'
                                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND
                                    fecha_cambio_plan IS NULL AND 
                                    fecha=LAST_DAY('$fecha_completa') AND
                                    estado != 'I'";
                            $oCon->QueryT($sql);
                        } else {
                            $sql = "UPDATE isp.contrato_pago_pack SET 
                                    estado='$estado',
                                    fecha_server=NOW(),
                                    dias_uso=DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')-extract(DAY from fecha_cambio_plan),
                                    dias_no_uso=(extract(DAY from TIMESTAMP '$fecha_completa')+extract(DAY from fecha_cambio_plan)),
                                    valor_uso=((tarifa/dias)*DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')-extract(DAY from fecha_cambio_plan)),
                                    valor_no_uso=((tarifa/dias)*(extract(DAY from TIMESTAMP  '$fecha_completa')+extract(DAY from fecha_cambio_plan))),
                                    valor_dia=(tarifa/dias),
                                    corte='$corte',
                                    reconexion='$reconexion'
                                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND
                                    fecha_cambio_plan IS NOT NULL AND 
                                    fecha=LAST_DAY('$fecha_completa') AND
                                    estado != 'I'";
                            $oCon->QueryT($sql);

                            $sql = "UPDATE isp.contrato_pago_pack SET 
                                    estado='$estado',
                                    fecha_server=NOW(),
                                    dias_uso=DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa'),
                                    dias_no_uso=extract(day from TIMESTAMP '$fecha_completa'),
                                    valor_uso=((tarifa/dias)*DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')),
                                    valor_no_uso=((tarifa/dias)*extract(day from TIMESTAMP '$fecha_completa')),
                                    valor_dia=(tarifa/dias),corte='$corte',reconexion='$reconexion'
                                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND
                                    fecha_cambio_plan IS NULL AND 
                                    fecha=LAST_DAY('$fecha_completa') AND
                                    estado != 'I' AND valor_pago = 0";
                            $oCon->QueryT($sql);
                        }
                    } else {
                        $sql = "UPDATE isp.contrato_pago_pack SET 
                                estado='$estado',
                                fecha_server=NOW(),
                                dias_uso=DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa'),
                                dias_no_uso=EXTRACT(day from TIMESTAMP '$fecha_completa'),
                                valor_uso=((tarifa/dias)*DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')),
                                valor_no_uso=((tarifa/dias)*EXTRACT(day from TIMESTAMP '$fecha_completa')),
                                valor_dia=(tarifa/dias),
                                corte='$corte',
                                reconexion='$reconexion'
                                WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND
                                fecha_cambio_plan IS NULL AND 
                                fecha=LAST_DAY('$fecha_completa') AND
                                estado != 'I' AND valor_pago = 0";
                        $oCon->QueryT($sql);
                    }
                } else if ($op == 2) {
                    $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                            dias_uso=0,
                            dias_no_uso=0,
                            valor_uso=tarifa,
                            valor_no_uso=0,
                            valor_dia=(tarifa/dias),
                            corte='$corte',
                            reconexion='$reconexion'
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND
                            fecha_cambio_plan IS NOT NULL AND 
                            fecha=LAST_DAY('$fecha_completa') AND
                            estado != 'I' AND valor_pago = 0";
                    $oCon->QueryT($sql);

                    $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                            dias_uso=0,
                            dias_no_uso=0,
                            valor_uso=tarifa,
                            valor_no_uso=0,
                            valor_dia=(tarifa/dias),corte='$corte',reconexion='$reconexion'
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND
                            fecha_cambio_plan IS NULL AND 
                            fecha=LAST_DAY('$fecha_completa') AND
                            estado != 'I' AND valor_pago = 0";
                    $oCon->QueryT($sql);
                }

                //RECONEXION DE LAS SIGUEINTES CUOTAS
                $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),dias_uso=0,
                        dias_no_uso=0,valor_uso=0,valor_no_uso=0,valor_dia=0,corte='$corte',reconexion='$reconexion'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND fecha>LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);

                //ACTUALIZA isp.contrato_pago
                if ($op != 1) {
                    //Actualiza la cuota actual
                    $sql = "UPDATE isp.contrato_pago SET
                            estado = 'PE',
                            valor_uso=
                                (
                                SELECT SUM(valor_uso) 
                                from isp.contrato_pago_pack 
                                WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                                ),
                            valor_no_uso=
                                (
                                SELECT SUM(valor_no_uso) 
                                from isp.contrato_pago_pack 
                                WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                                )
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha=LAST_DAY('$fecha_completa')";
                    $oCon->QueryT($sql);
                }


                $sqlControl = "SELECT SUM(precio) as control_cero
                                from isp.int_contrato_caja_pack 
                                WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND estado in ('C', 'D')";
                $oCon->Query($sqlControl);
                $control_cero    = $oCon->f('control_cero');
                $oCon->Free();

                if ($control_cero == NULL) {
                    $valor_no_uso = 0;
                } else {
                    $valor_no_uso = $control_cero;
                }

                //Actualiza las siguientes cuotas
                $sql = "UPDATE isp.contrato_pago SET dias_uso=0,dias_no_uso=0,estado='PE',
                        valor_uso=0,
                        valor_no_uso=0
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);

                //Verificar si el contrato está cortado
                $sql = "SELECT estado 
                        from isp.contrato_clpv
                        WHERE id = $id_contrato AND id_clpv = $id_clpv";
                $oCon->Query($sql);
                $estado_contrato = $oCon->f('estado');
                $oCon->Free();

                if ($estado_contrato == 'CO' || $estado_contrato == 'PR') {
                    $sql = "UPDATE isp.contrato_clpv SET estado='AP',fecha_server=NOW(),fecha_c_reconexion='$fecha_completa'
                            WHERE id=$id_contrato AND id_clpv=$id_clpv";
                    $oCon->QueryT($sql);
                }

                if ($ejecuta_cr_sn == 'S' && $ejecuta == 'S') {
                    $resp = $this->generarOsReconexionEquipo($datos);
                } else {
                    $resp = true;
                }
            } else {
                //ACTUALIZAR ESTADO DE EQUIPO
                $sql = "UPDATE isp.int_contrato_caja SET estado='$estado',fecha_reconexion='$fecha_completa',fecha_server=NOW() 
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja'";
                $oCon->QueryT($sql);

                $sql = "UPDATE isp.int_contrato_caja_pack SET estado='A',fecha_reconexion='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id = $id_caja_pack AND estado not in ('E')";
                $oCon->QueryT($sql);

                //ACTUALIZA isp.contrato_pago_pack
                #RECONEXION DE CUOTAS SIN CAMBIO DE PLAN
                $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                        dias_no_uso=0,valor_uso=0,valor_no_uso=0,
                        valor_dia=(tarifa/dias),corte='$corte',reconexion='$reconexion'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND
                        fecha_cambio_plan IS NULL AND 
                        fecha=LAST_DAY('$fecha_completa') AND valor_pago = 0";
                $oCon->QueryT($sql);

                #RECONEXION DE CUOTAS CON CAMBIO DE PLAN
                $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                        dias_no_uso=0,valor_uso=0,valor_no_uso=0,
                        valor_dia=(tarifa/dias),corte='$corte',reconexion='$reconexion'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND
                        fecha_cambio_plan IS NOT NULL AND 
                        fecha=LAST_DAY('$fecha_completa') AND valor_pago = 0";
                $oCon->QueryT($sql);

                //RECONEXION DE LAS SIGUEINTES CUOTAS
                $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),dias_uso=0,
                        dias_no_uso=0,valor_uso=0,valor_no_uso=0,valor_dia=0,corte='$corte',reconexion='$reconexion'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND fecha>LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);

                //ACTUALIZA isp.contrato_pago
                //Actualiza la cuota actual
                $sql = "UPDATE isp.contrato_pago SET
                        estado = 'PE',
                        valor_uso=
                            (
                            SELECT SUM(valor_uso) 
                            from isp.contrato_pago_pack 
                            WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                            ),
                        valor_no_uso=
                            (
                            SELECT SUM(valor_no_uso) 
                            from isp.contrato_pago_pack 
                            WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                            )
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha=LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);

                //Actualiza las siguientes cuotas
                $sql = "UPDATE isp.contrato_pago SET dias_uso=0,dias_no_uso=0,estado='PE',
                            valor_uso=0,
                            valor_no_uso=0
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);

                //Verificar si el contrato está cortado
                $sql = "SELECT estado 
                        from isp.contrato_clpv
                        WHERE id = $id_contrato AND id_clpv = $id_clpv";
                $oCon->Query($sql);
                $estado_contrato = $oCon->f('estado');
                $oCon->Free();

                if ($estado_contrato == 'CO' || $estado_contrato == 'PR') {
                    $sql = "UPDATE isp.contrato_clpv SET estado='AP',fecha_server=NOW(),fecha_c_reconexion='$fecha_completa'
                            WHERE id=$id_contrato AND id_clpv=$id_clpv";
                    $oCon->QueryT($sql);
                }

                if ($ejecuta_cr_sn == 'S' && $ejecuta == 'S') {
                    $resp = $this->generarOsReconexionEquipo($datos);
                } else {
                    $resp = true;
                }
            }
        } else {
            $sql = "UPDATE isp.int_contrato_caja_pack SET estado='PP' 
                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja_pack' AND activo = 'S' AND estado not in ('E')";
            $oCon->QueryT($sql);

            //Contar cuantos servicios activos tiene el equipo para cortarlo
            $sql = "SELECT COUNT(id) AS id 
                    from isp.int_contrato_caja_pack
                    WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado='A' AND activo = 'S' and id_caja = $id_caja";
            $oCon->Query($sql);
            $servicio_activos_indi = $oCon->f('id');
            $oCon->Free();

            if ($servicio_activos_indi == 0) {
                $sql = "UPDATE isp.int_contrato_caja SET estado='PP',fecha_corte='$fecha_completa',fecha_server=NOW() 
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja'";
                $oCon->QueryT($sql);
            }

            $sql = "SELECT COUNT(id) AS id 
                    from isp.int_contrato_caja_pack
                    WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado='A' AND activo = 'S'";
            $oCon->Query($sql);
            $servicio_activos = $oCon->f('id');
            $oCon->Free();

            if ($servicio_activos == 0) {
                $sql = "UPDATE isp.contrato_clpv SET estado='PR',fecha_server=NOW(),fecha_c_corte='$fecha_completa'
                        WHERE id=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);
            }

            $resp = $this->generarOsReconexionEquipo($datos);
        }


        return $resp;
    }
    /* FUNCION PARA GENERAR LA OS DE RECONEXION DE EQUIPO */
    function generarOsReconexionEquipo($datos)
    {

        global $DSN, $DSN_Ifx;

        $oCon2 = new Dbo;
        $oCon2->DSN = $DSN;
        $oCon2->Conectar();

        $oCon = $this->oCon;

        $id_empresa = $datos['id_empresa'];
        $id_usuario = $datos['usuario'];
        $id_contrato = $datos['id_contrato'];
        $id_clpv = $datos['id_clpv'];
        $id_sucursal = $datos['id_sucursal'];
        $id_caja_pack = $datos['id_caja_pack'];
        $serial = $datos['serial'];
        $detalle = $datos['comentario_os'];
        $id_motivo = $datos['id_motivo'];

        $resp = false;

        $sql = "SELECT id_caja
                from isp.int_contrato_caja_pack
                WHERE id_contrato=$id_contrato AND id = $id_caja_pack";
        $oCon->Query($sql);
        $id_caja = $oCon->f('id_caja');
        $oCon->Free();

        $sql = "SELECT ejecuta_cr_sn from isp.int_parametros WHERE id_empresa=$id_empresa AND id_sucursal=$id_sucursal";
        $oCon->Query($sql);
        $ejecuta_cr_sn      = $oCon->f('ejecuta_cr_sn');
        $oCon->Free();

        #LA DESCRIPCION SE PUEDE VISUALIZAR EN LA TABLA isp.int_tipo_proceso de MYSQL
        $descripcion = "RECONEXION";
        $sql = "SELECT id from isp.int_tipo_proceso WHERE descripcion LIKE '%$descripcion%' LIMIT 1";
        $oCon->Query($sql);
        $tipo_servicio = $oCon->f('id');
        $oCon->Free();

        if ($tipo_servicio != null) {

            if (strlen($id_motivo) == 0) {
                #EL MOTIVO SE PUEDE VISUALIZAR EN LA TABLA isp.int_motivos_canc de MYSQL
                $sql = "SELECT id from isp.int_motivos_canc WHERE id_proceso = $tipo_servicio LIMIT 1";
                $oCon->Query($sql);
                $resultado = $oCon->f('id');
                $oCon->Free();

                if ($resultado != null) {
                    $motivo = $resultado = $oCon->f('id');
                } else {
                    $motivo = "RECONEXION SERVICIO";
                    $sql = "insert into isp.int_motivos_canc(id_proceso,motivo) 
                            VALUES ($tipo_servicio,'$motivo')";
                    $oCon->QueryT($sql);

                    $sql = "SELECT id from isp.int_motivos_canc WHERE id_proceso = $tipo_servicio AND motivo = '$motivo' LIMIT 1";
                    $oCon->Query($sql);
                    $motivo = $oCon->f('id');
                    $oCon->Free();
                }
            } else {
                $motivo = $id_motivo;
            }


            if ($motivo != null) {

                #SABER SI EL CONTRATO CUENTA CON TELEVISION ANALOGA PARA DEFINIR EL TIPO DE ESTADO
                $sql = "SELECT id_tipo_prod 
                        from isp.int_contrato_caja
                        WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND id = $id_caja";
                $oCon->Query($sql);
                $id_tipo_prod_c = $oCon->f('id_tipo_prod');
                $oCon->Free();

                #CONTROLAR EL ESTADO DEPENDIENDO DEL TIPO DE SERVICIO

                if ($ejecuta_cr_sn == 'S') {
                    if ($id_tipo_prod_c == 1) {
                        $estado = "PE";
                    } else {
                        $estado = "TE";
                    }
                } else {
                    $estado = "PE";
                }

                //CONTROL DE ORDEN DE TRABAJO
                /* $sql = "SELECT COUNT(id) as id from isp.instalacion_clpv WHERE
                                    id_clpv    = $id_clpv AND
                                    id_contrato= $id_contrato AND
                                    estado     = 'PE' and
                                    id_proceso = $tipo_servicio AND
                                    id_franja = $id_tipo_prod_c";
                $oCon->Query($sql);
                $ordenes_pendientes = $oCon->f('id');
                $oCon->Free();

                if ($ordenes_pendientes == 0){
                    
                } */

                #OBTENER LA DIRECCION DEL CONTRATO
                $sql = "SELECT id_dire, id_sucursal 
                                from isp.contrato_clpv 
                                WHERE id = $id_contrato AND 
                                id_clpv = $id_clpv";

                $oCon->Query($sql);
                $id_dire = $oCon->f('id_dire');
                $id_sucu = $oCon->f('id_sucursal');
                $oCon->Free();

                if ($id_dire == null) {
                    $id_dire = 1;
                }
                #OBTENER EL TIPO DEL PROCESO
                #sql = """
                #SELECT descripcion, etiqueta, color 
                #                from isp.int_tipo_proceso 
                #                WHERE id = {tipo_servicio}
                #""".format(tipo_servicio=tipo_servicio)

                #resultado = self.mysql.query(sql)
                #descripcion_ts = resultado[0]['descripcion']
                #etiqueta_ts = resultado[1]['etiqueta']
                #color_ts = resultado[2]['color']

                #OBTENER EL SECUENCUAL
                $sql = "SELECT CAST (max(secuencial) AS INTEGER)  + 1 as secuencial 
                        from isp.instalacion_clpv
                        WHERE id_proceso = $tipo_servicio";

                $oCon->Query($sql);
                $secuencial = $oCon->f('secuencial');
                $oCon->Free();

                if ($secuencial == null) {
                    $secuencial = 1;
                };

                $secuencial_fin = str_pad($secuencial, 9, 0, STR_PAD_LEFT);
                $fecha_actual = date('Y-m-d');
                $fechaServer = date("Y-m-d H:i:s");

                #INGRESAR LA ORDEN DE SERVICIO
                $sql = "insert into isp.instalacion_clpv(id_empresa, id_sucursal, id_sucursal_ori, id_proceso, 
                                            secuencial, id_clpv, id_contrato, fecha, 
                                            user_web, fecha_server, observaciones, id_direccion, 
                                            id_franja, id_motivo, id_prioridad, solicita, adjunto, 
                                            observacion_cliente,estado_contrato, reconexion_pago, 
                                            fecha_inicio, fecha_fin, estado)
                        VALUES($id_empresa, $id_sucursal, $id_sucu, $tipo_servicio, '$secuencial_fin', 
                        $id_clpv, $id_contrato, '$fecha_actual',$id_usuario, '$fechaServer', '$detalle', $id_dire, 
                        $id_tipo_prod_c, $motivo, 1, '', '', '','AP', 'N', '$fechaServer', '$fechaServer', '$estado')";
                $oCon->QueryT($sql);

                #OBTENER EL ULTIMO ID DE LA TABLA instalacion clpv PARA INSERTARLO EN isp.instalacion_prod
                $sql = "SELECT max(id) AS id 
                        from isp.instalacion_clpv
                        WHERE 
                        id_proceso = $tipo_servicio AND 
                        id_clpv = $id_clpv AND id_contrato = $id_contrato";

                $oCon->Query($sql);
                $id_instalacion = $oCon->f('id');
                $oCon->Free();

                //INGRESA AUDI PARA POSTERIOR ANULACION
                $sql_audi = "SELECT estado
                        from isp.contrato_clpv
                        WHERE id=$id_contrato";
                $oCon2->Query($sql_audi);
                $estado_contrato_a = $oCon2->f('estado');
                $oCon2->Free();

                $sql_audi = "SELECT estado
                        from isp.int_contrato_caja
                        WHERE id_contrato=$id_contrato AND id = $id_caja";
                $oCon2->Query($sql_audi);
                $estado_equipo_a = $oCon2->f('estado');
                $oCon2->Free();

                $sql_audi = "SELECT estado
                        from isp.int_contrato_caja_pack
                        WHERE id_contrato=$id_contrato AND id = $id_caja_pack";
                $oCon2->Query($sql_audi);
                $estado_plan_a = $oCon2->f('estado');
                $oCon2->Free();

                $sql = "INSERT INTO isp.int_red_peticiones(id_dispositivo,      id_contrato,            id_equipo,          id_instalacion, 
                                                            id_usuario,         estado,                 estado_equipo,      fecha_server,
                                                            planes,             id_pa)
                                                    VALUES(1,                   $id_contrato,           $id_caja,           $id_instalacion,    
                                                            $id_usuario,        '$estado_contrato_a',   '$estado_equipo_a', CURRENT_DATE,
                                                            '$estado_plan_a',   $id_caja_pack)";
                $oCon->QueryT($sql);

                if ($estado == 'TE') {
                    #INSERTAR ORDEN DE SERVICIO EN LA TABLA isp.instalacion_ejecucion
                    $sql = "insert into isp.instalacion_ejecucion(id_empresa, id_sucursal, id_clpv, id_contrato, 
                                                id_instalacion, fecha, estado, user_web, fecha_server, 
                                                observacion_tecnico)
                            VALUES($id_empresa, $id_sucursal, $id_clpv, $id_contrato, '$id_instalacion', 
                            '$fecha_actual', 'A', $id_usuario, '$fechaServer', 'Reconexion de servicios automatica')";
                    $oCon->QueryT($sql);
                }

                $sql = "insert into isp.instalacion_prod(id_empresa, id_sucursal, id_clpv, id_contrato, 
                                                id_instalacion, id_caja, estado, cambio_sn, 
                                                fecha_ejecucion)
                        VALUES($id_empresa, $id_sucursal, $id_clpv, $id_contrato, '$id_instalacion', 
                        $id_caja, '$estado', 'N', '$fecha_actual')";
                $oCon->QueryT($sql);

                $respuesta = true;
            }
        }

        return $respuesta;
    }
    /*FUNCION PARA SUSPENDER EL EQUIPO */
    function suspenderEquipo($datos)
    {

        $oCon = $this->oCon;

        $id_empresa = $datos['id_empresa'];
        $id_usuario = $datos['usuario'];
        $id_contrato = $datos['id_contrato'];
        $id_clpv = $datos['id_clpv'];
        $id_sucursal = $datos['id_sucursal'];
        $id_caja_pack = $datos['id_caja_pack'];
        $serial = $datos['serial'];
        $fecha_completa = $datos['fecha_considerar'];
        $fecha_considerar = $datos['fecha_considerar'];
        $fecha_considerar = explode("-", $fecha_considerar);
        $dia = $fecha_considerar[2];
        $mes = $fecha_considerar[1];
        $anio = $fecha_considerar[0];
        $comentario_os = $datos['comentario_os'];
        $estado = "E";
        $corte = "S";
        $reconexion = "N";

        $resp = false;

        //OBTENER EL PARAMETRO DE DIAS CONSUMIDOS
        $sql = "SELECT dias_consumidos FROM isp.int_parametros WHERE id_empresa=$id_empresa AND id_sucursal=$id_sucursal";
        $oCon->Query($sql);
        $dias_consumidos = $oCon->f('dias_consumidos');
        $oCon->Free();

        //OBTENER EL CODIGO DEL PRODUCTO 
        $sql = "SELECT cod_prod, id_caja
                FROM isp.int_contrato_caja_pack
                WHERE id_contrato=$id_contrato AND id = $id_caja_pack";
        $oCon->Query($sql);
        $cod_prod = $oCon->f('cod_prod');
        $id_caja = $oCon->f('id_caja');
        $oCon->Free();

        if ($dias_consumidos == 'S') {

            $sql = "UPDATE isp.int_contrato_caja_pack SET estado='E' ,fecha_corte='$fecha_completa'
                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id_caja='$id_caja' AND activo = 'S'";
            $oCon->QueryT($sql);

            //ACTUALIZA isp.contrato_pago_PACK
            #MODIFICA CUOTAS SIN CAMBIO DE PLAN
            $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                    dias_uso=$dia,
                    dias_no_uso=DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa'),
                    valor_uso=((tarifa/dias)*dias_uso),valor_no_uso=((tarifa/dias)*dias_no_uso),
                    valor_dia=(tarifa/dias),corte='$corte',reconexion='$reconexion',
                    fecha_corte='$fecha_completa'
                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv 
                    AND cod_prod='$cod_prod' AND estado IN ('A','C') AND 
                    fecha_cambio_plan IS NULL AND
                    fecha=LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            #MODIFICA CUOTAS CON CAMBIO DE PLAN
            $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                    dias_uso=(dias_uso-DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')),
                    dias_no_uso=(DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')+dias_no_uso),
                    valor_uso=((tarifa/dias)*dias_uso),valor_no_uso=((tarifa/dias)*dias_no_uso),
                    valor_dia=(tarifa/dias),corte='$corte',reconexion='$reconexion',
                    fecha_corte='$fecha_completa'
                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv 
                    AND cod_prod='$cod_prod' AND estado IN ('A','C') AND 
                    fecha_cambio_plan IS NOT NULL AND
                    fecha=LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            //ACTUALIZA isp.contrato_pago
            //Actualiza la cuota actual
            $sql = "UPDATE isp.contrato_pago SET
                    valor_uso=
                        (
                        SELECT SUM(valor_uso) 
                        FROM isp.contrato_pago_pack 
                        WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                        ),
                    valor_no_uso=
                        (
                        SELECT SUM(valor_no_uso) 
                        FROM isp.contrato_pago_pack 
                        WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                        )
                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha=LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            //Contar cuantos servicios activos tiene el contrato para cortarlo
            $sql = "SELECT COUNT(id) AS id 
                    FROM isp.int_contrato_caja_pack
                    WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado in ('A','C') AND activo = 'S'";
            $oCon->Query($sql);
            $servicio_activos = $oCon->f('id');
            $oCon->Free();

            $sql = "UPDATE isp.contrato_clpv SET fecha_c_corte='$fecha_completa'
                    WHERE id=$id_contrato AND id_clpv=$id_clpv";
            $oCon->QueryT($sql);

            if ($servicio_activos == 0) {
                $sql = "UPDATE isp.contrato_clpv SET estado='SP',fecha_server=NOW(),fecha_c_corte='$fecha_completa'
                        WHERE id=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);

                $sql = "UPDATE isp.contrato_pago SET estado='SP'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv and fecha > LAST_DAY(CURRENT_DATE)";
                $oCon->QueryT($sql);
            }

            //Contar cuantos servicios activos tiene el equipo para cortarlo
            $sql = "SELECT COUNT(id) AS id 
                    FROM isp.int_contrato_caja_pack
                    WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado in ('A','C') AND activo = 'S' and id_caja = $id_caja";
            $oCon->Query($sql);
            $servicio_activos_indi = $oCon->f('id');
            $oCon->Free();

            if ($servicio_activos_indi == 0) {
                $sql = "UPDATE isp.int_contrato_caja SET estado='$estado',fecha_corte='$fecha_completa',fecha_server=NOW() 
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja'";
                $oCon->QueryT($sql);
            }
            $resp = $this->generarOsSuspencionEquipo($datos);
        } else {

            $sql = "UPDATE isp.int_contrato_caja_pack SET estado='E' 
                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id_caja='$id_caja' AND activo = 'S'";
            $oCon->QueryT($sql);

            //ACTUALIZA isp.contrato_pago_PACK
            #MODIFICA CUOTAS SIN CAMBIO DE PLAN
            $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                    dias_uso=0,dias_no_uso=0,
                    valor_uso=0,valor_no_uso=0,
                    valor_dia=(tarifa/dias),corte='$corte',reconexion='$reconexion',
                    fecha_corte='$fecha_completa'
                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv 
                    AND cod_prod='$cod_prod' AND estado='A' AND 
                    fecha_cambio_plan IS NULL AND
                    fecha=LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            #MODIFICA CUOTAS CON CAMBIO DE PLAN
            $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                    corte='$corte',reconexion='$reconexion',
                    fecha_corte='$fecha_completa'
                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv 
                    AND cod_prod='$cod_prod' AND estado='A' AND 
                    fecha_cambio_plan IS NOT NULL AND
                    fecha=LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            //ACTUALIZA isp.contrato_pago
            //Actualiza la cuota actual
            $sql = "UPDATE isp.contrato_pago SET
                    valor_uso=
                        (
                        SELECT SUM(valor_uso) 
                        FROM isp.contrato_pago_pack 
                        WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                        ),
                    valor_no_uso=
                        (
                        SELECT SUM(valor_no_uso) 
                        FROM isp.contrato_pago_pack 
                        WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                        )
                    WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha=LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            //Contar cuantos servicios activos tiene el contrato para cortarlo
            $sql = "SELECT COUNT(id) AS id 
                    FROM isp.int_contrato_caja_pack
                    WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado='A' AND activo = 'S'";
            $oCon->Query($sql);
            $servicio_activos = $oCon->f('id');
            $oCon->Free();

            $sql = "UPDATE isp.contrato_clpv SET fecha_c_corte='$fecha_completa'
                    WHERE id=$id_contrato AND id_clpv=$id_clpv";
            $oCon->QueryT($sql);

            if ($servicio_activos == 0) {
                $sql = "UPDATE isp.contrato_clpv SET estado='SP',fecha_server=NOW(),fecha_c_corte='$fecha_completa'
                        WHERE id=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);

                $sql = "UPDATE isp.contrato_pago SET estado='SP'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv and fecha > LAST_DAY(CURRENT_DATE)";
                $oCon->QueryT($sql);
            }

            //Contar cuantos servicios activos tiene el equipo para cortarlo
            $sql = "SELECT COUNT(id) AS id 
                    FROM isp.int_contrato_caja_pack
                    WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado IN ('A','C') AND activo = 'S' and id_caja = $id_caja";
            $oCon->Query($sql);
            $servicio_activos_indi = $oCon->f('id');
            $oCon->Free();

            if ($servicio_activos_indi == 0) {
                $sql = "UPDATE isp.int_contrato_caja SET estado='$estado',fecha_corte='$fecha_completa',fecha_server=NOW() 
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja'";
                $oCon->QueryT($sql);
            }
            $resp = $this->generarOsSuspencionEquipo($datos);
        }

        return $resp;
    }
    /* FUNCION PARA GENERAR LA OS DE CORTE DE EQUIPO */
    function generarOsSuspencionEquipo($datos)
    {

        $oCon = $this->oCon;

        $id_empresa = $datos['id_empresa'];
        $id_usuario = $datos['usuario'];
        $id_contrato = $datos['id_contrato'];
        $id_clpv = $datos['id_clpv'];
        $id_sucursal = $datos['id_sucursal'];
        $id_caja_pack = $datos['id_caja_pack'];
        $serial = $datos['serial'];
        $detalle = $datos['comentario_os'];
        $id_motivo = $datos['id_motivo'];

        $respuesta = false;

        $sql = "SELECT id_caja
                FROM isp.int_contrato_caja_pack
                WHERE id_contrato=$id_contrato AND id = $id_caja_pack";
        $oCon->Query($sql);
        $id_caja = $oCon->f('id_caja');
        $oCon->Free();

        #LA DESCRIPCION SE PUEDE VISUALIZAR EN LA TABLA isp.int_tipo_proceso de MYSQL
        $descripcion = "SUSPENSION CONTRATO";
        $sql = "SELECT id FROM isp.int_tipo_proceso WHERE descripcion = '$descripcion' LIMIT 1";
        $oCon->Query($sql);
        $tipo_servicio = $oCon->f('id');
        $oCon->Free();

        if ($tipo_servicio != null) {

            if (strlen($id_motivo) == 0) {
                #EL MOTIVO SE PUEDE VISUALIZAR EN LA TABLA isp.int_motivos_canc de MYSQL
                $sql = "SELECT id FROM isp.int_motivos_canc WHERE id_proceso = $tipo_servicio LIMIT 1";
                $oCon->Query($sql);
                $resultado = $oCon->f('id');
                $oCon->Free();

                if ($resultado != null) {
                    $motivo = $resultado = $oCon->f('id');
                } else {
                    $motivo = "SUSPENCION DE CONTRATO";
                    $sql = "INSERT INTO isp.int_motivos_canc(id_proceso,motivo) 
                            VALUES ($tipo_servicio,'$motivo')";
                    $oCon->QueryT($sql);

                    $sql = "SELECT id FROM isp.int_motivos_canc WHERE id_proceso = $tipo_servicio AND motivo = '$motivo' LIMIT 1";
                    $oCon->Query($sql);
                    $motivo = $oCon->f('id');
                    $oCon->Free();
                }
            } else {
                $motivo = $id_motivo;
            }


            if ($motivo != null) {

                #SABER SI EL CONTRATO CUENTA CON TELEVISION ANALOGA PARA DEFINIR EL TIPO DE ESTADO
                $sql = "SELECT id_tipo_prod 
                        FROM isp.int_contrato_caja
                        WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND id = $id_caja";
                $oCon->Query($sql);
                $id_tipo_prod_c = $oCon->f('id_tipo_prod');
                $oCon->Free();

                #CONTROLAR EL ESTADO DEPENDIENDO DEL TIPO DE SERVICIO

                if ($id_tipo_prod_c == 1) {
                    $estado = "PE";
                } else {
                    $estado = "TE";
                }

                //DIRECCION DEL CONTRATO
                $sql = "SELECT id_dire, id_sucursal 
                                    FROM isp.contrato_clpv 
                                    WHERE id = $id_contrato AND 
                                    id_clpv = $id_clpv";

                $oCon->Query($sql);
                $id_dire = $oCon->f('id_dire');
                $id_sucu = $oCon->f('id_sucursal');
                $oCon->Free();

                if ($id_dire == null) {
                    $id_dire = 1;
                }

                #OBTENER EL SECUENCUAL
                $sql = "SELECT CAST (max(secuencial) AS INTEGER)  + 1 as secuencial
                            FROM isp.instalacion_clpv
                            WHERE id_proceso = $tipo_servicio";

                $oCon->Query($sql);
                $secuencial = $oCon->f('secuencial');
                $oCon->Free();

                if ($secuencial == null) {
                    $secuencial = 1;
                };

                $secuencial_fin = str_pad($secuencial, 9, 0, STR_PAD_LEFT);
                $fecha_actual = date('Y-m-d');
                $fechaServer = date("Y-m-d H:i:s");

                #INGRESAR LA ORDEN DE SERVICIO
                $sql = "INSERT INTO isp.instalacion_clpv(id_empresa, id_sucursal, id_sucursal_ori, id_proceso, 
                                                secuencial, id_clpv, id_contrato, fecha, 
                                                user_web, fecha_server, observaciones, id_direccion, 
                                                id_franja, id_motivo, id_prioridad, solicita, adjunto, 
                                                observacion_cliente,estado_contrato, reconexion_pago, 
                                                fecha_inicio, fecha_fin, estado)
                            VALUES($id_empresa, $id_sucursal, $id_sucu, $tipo_servicio, '$secuencial_fin', 
                            $id_clpv, $id_contrato, '$fecha_actual',$id_usuario, '$fechaServer', '$detalle', $id_dire, 
                            $id_tipo_prod_c, $motivo, 1, '', '', '','AP', 'N', '$fechaServer', '$fechaServer', '$estado')";
                $oCon->QueryT($sql);

                #OBTENER EL ULTIMO ID DE LA TABLA instalacion clpv PARA INSERTARLO EN isp.instalacion_prod
                $sql = "SELECT max(id) AS id 
                            FROM isp.instalacion_clpv
                            WHERE 
                            id_proceso = $tipo_servicio AND 
                            id_clpv = $id_clpv AND id_contrato = $id_contrato";

                $oCon->Query($sql);
                $id_instalacion = $oCon->f('id');
                $oCon->Free();

                if ($estado == 'TE') {
                    #INSERTAR ORDEN DE SERVICIO EN LA TABLA isp.instalacion_ejecucion
                    $sql = "INSERT INTO isp.instalacion_ejecucion(id_empresa, id_sucursal, id_clpv, id_contrato, 
                                                    id_instalacion, fecha, estado, user_web, fecha_server, 
                                                    observacion_tecnico)
                                VALUES($id_empresa, $id_sucursal, $id_clpv, $id_contrato, '$id_instalacion', 
                                '$fecha_actual', 'A', $id_usuario, '$fechaServer', 'Corte de servicios automatica')";
                    $oCon->QueryT($sql);
                }

                $sql = "INSERT INTO isp.instalacion_prod(id_empresa, id_sucursal, id_clpv, id_contrato, 
                                                    id_instalacion, id_caja, estado, cambio_sn, 
                                                    fecha_ejecucion)
                            VALUES($id_empresa, $id_sucursal, $id_clpv, $id_contrato, '$id_instalacion', 
                            $id_caja, '$estado', 'N', '$fecha_actual')";
                $oCon->QueryT($sql);

                $respuesta = true;
            }
        }

        return $respuesta;
    }

    //FUNCION PARA RETIRO DE EQUIPO
    function retirarEquipo($datos)
    {

        $oCon = $this->oCon;

        $id_empresa = $datos['id_empresa'];
        $id_usuario = $datos['usuario'];
        $id_contrato = $datos['id_contrato'];
        $id_clpv = $datos['id_clpv'];
        $id_sucursal = $datos['id_sucursal'];
        $id_caja_pack = $datos['id_caja_pack'];
        $serial = $datos['serial'];
        $fecha_completa = $datos['fecha_considerar'];
        $fecha_considerar = $datos['fecha_considerar'];
        $fecha_considerar = explode("-", $fecha_considerar);
        $dia = $fecha_considerar[2];
        $mes = $fecha_considerar[1];
        $anio = $fecha_considerar[0];
        $comentario_os = $datos['comentario_os'];
        $ejecuta = $datos['ejecuta'];
        $genera_orden = $datos['genera_orden'];
        $estado = "C";
        $corte = "S";
        $reconexion = "N";
        $fecha_server = date("Y-m-d H:i:s");

        $resp = false;

        //OBTENER EL PARAMETRO DE DIAS CONSUMIDOS
        $sql = "SELECT dias_consumidos, ejecuta_cr_sn from isp.int_parametros WHERE id_empresa=$id_empresa AND id_sucursal=$id_sucursal";
        $oCon->Query($sql);
        $dias_consumidos    = $oCon->f('dias_consumidos');
        $ejecuta_cr_sn      = $oCon->f('ejecuta_cr_sn');
        $oCon->Free();

        if ($ejecuta_cr_sn == 'S') {
            $ejecuta = 'S';
        }

        if ($genera_orden == 'N') {
            $ejecuta = 'N';
        }
        //OBTENER EL CODIGO DEL PRODUCTO 
        $sql = "SELECT cod_prod, id_caja
                from isp.int_contrato_caja_pack
                WHERE id_contrato=$id_contrato AND id = $id_caja_pack";
        $oCon->Query($sql);
        $cod_prod = $oCon->f('cod_prod');
        $id_caja = $oCon->f('id_caja');
        $oCon->Free();

        if ($dias_consumidos == 'S') {

            $sql = "UPDATE isp.int_contrato_caja_pack SET estado='E', fecha_corte='$fecha_completa', fecha_vence = '$fecha_server'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja_pack' AND activo = 'S' AND estado not in ('E')";
            $oCon->QueryT($sql);

            #MODIFICA CUOTAS SIN CAMBIO DE PLAN
            $sql = "UPDATE isp.contrato_pago_pack SET 
                        estado='$estado',
                        fecha_server=NOW(),
                        dias_uso=$dia,
                        dias_no_uso=DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa'),
                        valor_uso=((tarifa/dias)*$dia),
                        valor_no_uso=((tarifa/dias)*DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')),
                        valor_dia=(tarifa/dias),
                        corte='$corte',
                        reconexion='$reconexion',
                        fecha_corte='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv 
                        AND cod_prod='$cod_prod' AND estado = 'A' AND 
                        fecha_cambio_plan IS NULL AND
                        fecha=LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            #MODIFICA CUOTAS CON CAMBIO DE PLAN
            $sql = "UPDATE isp.contrato_pago_pack SET 
                        estado='$estado',
                        fecha_server=NOW(),
                        dias_uso=(dias_uso-DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')),
                        dias_no_uso=(DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')+dias_no_uso),
                        valor_uso=((tarifa/dias)*(dias_uso-DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa'))),
                        valor_no_uso=((tarifa/dias)*(DATEDIFF(LAST_DAY('$fecha_completa'),'$fecha_completa')+dias_no_uso)),
                        valor_dia=(tarifa/dias),
                        corte='$corte',
                        reconexion='$reconexion',
                        fecha_corte='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv 
                        AND cod_prod='$cod_prod' AND estado = 'A' AND 
                        fecha_cambio_plan IS NOT NULL AND
                        fecha=LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            #MODIFICA SIGUIENTES CUOTAS
            $sql = "UPDATE isp.contrato_pago_pack 
                        SET 
                        estado='$estado',
                        fecha_server=NOW(),
                        corte='$corte',
                        reconexion='$reconexion',
                        dias_uso=0,
                        dias_no_uso=extract(day from LAST_DAY('$fecha_completa')),
                        valor_uso=0,
                        valor_no_uso=tarifa,
                        fecha_corte='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND fecha>LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            //Actualiza la cuota actual
            $sql = "UPDATE isp.contrato_pago SET
                        valor_uso=
                            (
                            SELECT SUM(valor_uso) 
                            from isp.contrato_pago_pack 
                            WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                            ),
                        valor_no_uso=
                            (
                            SELECT SUM(valor_no_uso) 
                            from isp.contrato_pago_pack 
                            WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                            )
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha=LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            //Actualiza las siguientes cuotas
            $sql = "UPDATE isp.contrato_pago SET dias_uso=0,dias_no_uso=0,
                        valor_uso=(
                            SELECT SUM(precio) 
                            from isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A' AND activo = 'S' 
                        ),
                        valor_no_uso=(
                            SELECT SUM(precio) 
                            from isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='C' AND activo = 'S' 
                        )
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            //Contar cuantos servicios activos tiene el contrato para cortarlo
            $sql = "SELECT COUNT(id) AS id 
                        from isp.int_contrato_caja_pack
                        WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado in ('A','C') AND activo = 'S'";
            $oCon->Query($sql);
            $servicio_activos = $oCon->f('id');
            $oCon->Free();

            if ($servicio_activos == 0) {

                $sql = "UPDATE isp.contrato_clpv SET fecha_c_corte='$fecha_completa'
                            WHERE id=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);

                $sql = "UPDATE isp.contrato_clpv SET estado='CA',fecha_server='$fecha_server',fecha_c_corte='$fecha_completa'
                            WHERE id=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);

                $sql = "UPDATE isp.contrato_pago 
                            SET estado = 'CO', valor_uso=0, valor_no_uso=0
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);
            }

            //Contar cuantos servicios activos tiene el equipo para cortarlo
            $sql = "SELECT COUNT(id) AS id 
                        from isp.int_contrato_caja_pack
                        WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado in ('A','C') AND activo = 'S' and id_caja = $id_caja";
            $oCon->Query($sql);
            $servicio_activos_indi = $oCon->f('id');
            $oCon->Free();

            if ($servicio_activos_indi == 0) {
                $sql = "UPDATE isp.int_contrato_caja SET estado='E',fecha_corte='$fecha_completa',fecha_server='$fecha_server' 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja'";
                $oCon->QueryT($sql);

                $sql = "UPDATE isp.int_contrato_caja 
                            SET id_tarjeta          = NULL, 
                                vlan                = NULL, 
                                distancia           = NULL, 
                                ip                  = NULL, 
                                mac                 = NULL, 
                                type                = NULL,
                                state_onu           = NULL, 
                                puerto_secuencial   = NULL, 
                                vel_subida          = NULL, 
                                vel_bajada          = NULL, 
                                comentario          = NULL, 
                                puerto_pon          = NULL,
                                ubicacion           = NULL, 
                                hub                 = NULL, 
                                interface           = NULL,
                                id_tmp              = '0'
                    WHERE id_contrato=$id_contrato AND id = $id_caja";
                $oCon->QueryT($sql);

                $sql = "SELECT duracion,penalidad FROM isp.contrato_clpv WHERE id = $id_contrato";
                $valor_penalidad = consulta_string_func($sql, 'penalidad', $oCon, 0);
                $duracion = consulta_string_func($sql, 'duracion', $oCon, 0);

                $sql = "SELECT COUNT(id) as cuotas_pagadas FROM isp.contrato_pago WHERE id_contrato = $id_contrato AND valor_pago >= tarifa AND tipo_tmp = 'P'";
                $cuotas_pagadas = consulta_string_func($sql, 'cuotas_pagadas', $oCon, 0);

                if ($cuotas_pagadas >= $duracion) {
                    $valor_penalidad = 0;
                }

                if ($valor_penalidad > 0) {
                    $sql = "SELECT id_prod, cod_prod from isp.int_contrato_caja_pack WHERE id = $id_caja_pack";
                    $idProd = consulta_string_func($sql, 'id', $oCon, 0);
                    $cod_prod = consulta_string_func($sql, 'cod_prod', $oCon, 0);

                    $Equipos = new Equipos($oCon, null, $id_empresa, $id_sucursal, $id_clpv, $id_contrato, null);
                    $idCargoCuota = $Equipos->registraTablaPago(date("Y-m-d"), 0, "PE", date("m"), date("Y"), $valor_penalidad, 0, 0, 0, 1, 1, 0, 'A', "PENALIDAD");
                    $Equipos->registraTablaPagoPack(null, null, $idCargoCuota, $idProd, $cod_prod, date("Y-m-d"), $valor_penalidad, 0, 0, 0, 1, 1, 'A', "PENALIDAD", 'N', 'A');
                }
            }

            $resp = true;
        } else {

            $sql = "UPDATE isp.int_contrato_caja_pack SET estado='E' 
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja_pack' AND activo = 'S' AND estado not in ('E')";
            $oCon->QueryT($sql);

            //ACTUALIZA isp.contrato_pago_pack
            #MODIFICA CUOTAS SIN CAMBIO DE PLAN
            $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                        dias_uso=0,dias_no_uso=0,
                        valor_uso=0,valor_no_uso=0,
                        valor_dia=(tarifa/dias),corte='$corte',reconexion='$reconexion',
                        fecha_corte='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv 
                        AND cod_prod='$cod_prod' AND estado='A' AND 
                        fecha_cambio_plan IS NULL AND
                        fecha=LAST_DAY('$fecha_completa') AND
                        valor_pago = 0";
            $oCon->QueryT($sql);

            #MODIFICA CUOTAS CON CAMBIO DE PLAN
            $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                        corte='$corte',reconexion='$reconexion',
                        fecha_corte='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv 
                        AND cod_prod='$cod_prod' AND estado='A' AND 
                        fecha_cambio_plan IS NOT NULL AND
                        fecha=LAST_DAY('$fecha_completa') AND
                        valor_pago = 0";
            $oCon->QueryT($sql);

            //MODIFICA LAS SIGUIENTES CUOTAS
            $sql = "UPDATE isp.contrato_pago_pack SET estado='$estado',fecha_server=NOW(),
                        corte='$corte',
                        reconexion='$reconexion',
                        dias_uso=0,
                        dias_no_uso=extract(day from LAST_DAY('$fecha_completa')),
                        valor_uso=0,
                        valor_no_uso=tarifa,
                        fecha_corte='$fecha_completa'
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND cod_prod='$cod_prod' AND fecha>LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            //ACTUALIZA isp.contrato_pago
            //Actualiza la cuota actual
            $sql = "UPDATE isp.contrato_pago SET
                        valor_uso=
                            (
                            SELECT SUM(valor_uso) 
                            from isp.contrato_pago_pack 
                            WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                            ),
                        valor_no_uso=
                            (
                            SELECT SUM(valor_no_uso) 
                            from isp.contrato_pago_pack 
                            WHERE id_clpv = $id_clpv AND id_contrato = $id_contrato AND fecha=LAST_DAY('$fecha_completa') 
                            )
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha=LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            //Actualiza la cuota de los siguientes meses
            $sql = "UPDATE isp.contrato_pago 
                        SET  dias_uso=0,dias_no_uso=0,
                        valor_uso=(
                            SELECT SUM(precio) 
                            from isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A' AND activo = 'S' 
                        ),
                        valor_no_uso=(
                            SELECT SUM(precio) 
                            from isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='C' AND activo = 'S' 
                        )
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY('$fecha_completa')";
            $oCon->QueryT($sql);

            //Contar cuantos servicios activos tiene el contrato para cortarlo
            $sql = "SELECT COUNT(id) AS id 
                        from isp.int_contrato_caja_pack
                        WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado IN ('A','C') AND activo = 'S'";
            $oCon->Query($sql);
            $servicio_activos = $oCon->f('id');
            $oCon->Free();

            if ($servicio_activos == 0) {
                $sql = "UPDATE isp.contrato_clpv SET fecha_c_corte='$fecha_completa'
                            WHERE id=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);

                $sql = "UPDATE isp.contrato_clpv SET estado='CA',fecha_server=NOW(),fecha_c_corte='$fecha_completa'
                            WHERE id=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);

                $sql = "UPDATE isp.contrato_pago 
                            SET estado = 'CO', valor_uso=0, valor_no_uso=0
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY('$fecha_completa')";
                $oCon->QueryT($sql);
            }

            //Contar cuantos servicios activos tiene el equipo para cortarlo
            $sql = "SELECT COUNT(id) AS id 
                        from isp.int_contrato_caja_pack
                        WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND estado IN ('A','C') AND activo = 'S' and id_caja = $id_caja";
            $oCon->Query($sql);
            $servicio_activos_indi = $oCon->f('id');
            $oCon->Free();

            if ($servicio_activos_indi == 0) {
                $sql = "UPDATE isp.int_contrato_caja SET estado='E',fecha_corte='$fecha_completa',fecha_server='$fecha_server'
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND id='$id_caja'";
                $oCon->QueryT($sql);

                $sql = "UPDATE isp.int_contrato_caja 
                            SET id_tarjeta          = NULL, 
                                vlan                = NULL, 
                                distancia           = NULL, 
                                ip                  = NULL, 
                                mac                 = NULL, 
                                type                = NULL,
                                state_onu           = NULL, 
                                puerto_secuencial   = NULL, 
                                vel_subida          = NULL, 
                                vel_bajada          = NULL, 
                                comentario          = NULL, 
                                puerto_pon          = NULL,
                                ubicacion           = NULL, 
                                hub                 = NULL, 
                                interface           = NULL,
                                id_tmp              = '0'
                    WHERE id_contrato=$id_contrato AND id = $id_caja";
                $oCon->QueryT($sql);

                $sql = "SELECT duracion,penalidad FROM isp.contrato_clpv WHERE id = $id_contrato";
                $valor_penalidad = consulta_string_func($sql, 'penalidad', $oCon, 0);
                $duracion = consulta_string_func($sql, 'duracion', $oCon, 0);

                $sql = "SELECT COUNT(id) as cuotas_pagadas FROM isp.contrato_pago WHERE id_contrato = $id_contrato AND valor_pago >= tarifa AND tipo_tmp = 'P'";
                $cuotas_pagadas = consulta_string_func($sql, 'cuotas_pagadas', $oCon, 0);

                if ($cuotas_pagadas >= $duracion) {
                    $valor_penalidad = 0;
                }

                if ($valor_penalidad > 0) {
                    $sql = "SELECT id_prod, cod_prod from isp.int_contrato_caja_pack WHERE id = $id_caja_pack";
                    $idProd = consulta_string_func($sql, 'id', $oCon, 0);
                    $cod_prod = consulta_string_func($sql, 'cod_prod', $oCon, 0);

                    $Equipos = new Equipos($oCon, null, $id_empresa, $id_sucursal, $id_clpv, $id_contrato, null);
                    $idCargoCuota = $Equipos->registraTablaPago(date("Y-m-d"), 0, "PE", date("m"), date("Y"), $valor_penalidad, 0, 0, 0, 1, 1, 0, 'A', "PENALIDAD");
                    $Equipos->registraTablaPagoPack(null, null, $idCargoCuota, $idProd, $cod_prod, date("Y-m-d"), $valor_penalidad, 0, 0, 0, 1, 1, 'A', "PENALIDAD", 'N', 'A');
                }
            }

            $resp = true;
        }
        return $resp;
    }

    // RECONEXION DEL CONTRATO DESDE FACTURACION
    function reconectarEquipoFact($fecha_rec, $tipo_contrato_de_cobro, $audi_sn = 0)
    {
        global $DSN_Ifx, $DSN;

        session_start();

        $oCon1 = new Dbo;
        $oCon1->DSN = $DSN;
        $oCon1->Conectar();

        $oCon = $this->oCon;

        $idContrato = $this->idContrato;
        $codigoCliente = $this->idClpv;
        $idUser = $_SESSION['U_ID'];
        $id_empresa = $_SESSION['U_EMPRESA'];
        $id_sucursal = $_SESSION['U_SUCURSAL'];
        $id_api = $_SESSION['id_api'];
        $estado_api = $_SESSION['estado_api'];
        $url_api = $_SESSION['url'];

        $op = 0;

        if ($estado_api == "A") {
            $usa_api = 1;
        } else {
            $usa_api = 2;
        }

        $sql = "SELECT a.id, b.id_empresa, b.id_sucursal, b.id_tarjeta, b.ip, b.puerto_pon, b.vlan, c.id_tipo_prod, b.id_dispositivo
                from isp.int_contrato_caja_pack a, isp.int_contrato_caja b, isp.int_paquetes c
                where a.id_caja = b.id and
                a.id_prod = c.id AND 
                a.id_contrato = b.id_contrato and
                a.id_clpv = b.id_clpv and
                a.id_clpv = $codigoCliente and
                a.id_contrato = $idContrato and    
                a.estado in ('C', 'D') and 
                a.activo in ('S','N') and
                a.estado not in ('E')";
        if ($oCon1->Query($sql)) {
            if ($oCon1->NumFilas() > 0) {
                do {
                    $id_caja_pack   = $oCon1->f('id');
                    $id_empresa_r   = $oCon1->f('id_empresa');
                    $id_sucursal_r  = $oCon1->f('id_sucursal');
                    $id_tarjeta_r   = $oCon1->f('id_tarjeta');
                    $ip_r           = $oCon1->f('ip');
                    $puerto_pon_r   = $oCon1->f('puerto_pon');
                    $vlan_r         = $oCon1->f('vlan');
                    $id_tipo_prod   = $oCon1->f('id_tipo_prod');
                    $id_olt         = $oCon1->f('id_dispositivo');
                    $comentario_os  = "RECONEXION DESDE FACTURACION - " . $fecha_rec;

                    if (!empty($id_olt)) {

                        $sql = "SELECT id_sistema, list_reconexion FROM isp.int_dispositivos WHERE id = $id_olt";
                        if ($oCon->Query($sql)) {
                            if ($oCon->NumFilas() > 0) {
                                $id_sistema          = $oCon->f('id_sistema');
                                $list_reconexion     = $oCon->f('list_reconexion');
                            }
                        }
                        $oCon->Free();

                        if(!empty($id_sistema)){
                            $sql = "SELECT sistema FROM isp.int_sistemas WHERE id = $id_sistema ";
                            if ($oCon->Query($sql)) {
                                if ($oCon->NumFilas() > 0) {
                                    $sistema          = $oCon->f('sistema');
                                }
                            }
                            $oCon->Free();
                        }
                        
                        if ($sistema == "GPON") {
                            $n_identificativo = $id_tarjeta_r;
                            $vlan = $vlan_r;
                        } else if ($sistema == "EPON") {
                            $n_identificativo = $ip_r;
                        }

                        $array_rec = array(
                            "id_empresa"  => $id_empresa_r,
                            "usuario"  => $idUser,
                            "id_contrato"  => $idContrato,
                            "id_clpv"  => $codigoCliente,
                            "id_sucursal"  => $id_sucursal_r,
                            "serial"  => $n_identificativo,
                            "id_caja_pack"  => $id_caja_pack,
                            "fecha_considerar"  => $fecha_rec,
                            "comentario_os"  => $comentario_os,
                            "vlan" => $vlan
                        );
                        if ($estado_api == "A" && $id_tipo_prod != 1 && $id_api == 3) {

                            $sql = "SELECT url
                                    from isp.int_api_url 
                                    where id_api = $id_api and comando_url = 'RECONEXION_EQUIPO' AND id_sistema = $id_sistema";
                            if ($oCon->Query($sql)) {
                                if ($oCon->NumFilas() > 0) {
                                    $url_rec_eq = $oCon->f('url');
                                }
                            }
                            $oCon->Free();

                            $sql = "SELECT url
                                    from isp.int_api_url 
                                    where id_api = $id_api and comando_url = 'ACTIVACION_CATV' AND id_sistema = $id_sistema";
                            if ($oCon->Query($sql)) {
                                if ($oCon->NumFilas() > 0) {
                                    $url_rec_catv = $oCon->f('url');
                                }
                            }
                            $oCon->Free();

                            $sUrl = $url_api . $url_rec_eq;
                            $sUrlCatv = $url_api . $url_rec_catv;

                            //VALIDA EL TIPO DE SISTEMA
                            if ($sistema == "GPON") { //GPON
                                if ($id_tipo_prod == 2) {
                                    if (intval($vlan) > 0) {
                                        $serial = $n_identificativo;
                                        $headers = array(
                                            "Content-Type:application/json"
                                        );
                                        $data = array(
                                            "sn" => $serial,
                                            "vlan" => intval($vlan)
                                        );

                                        $finalResult = "";
                                        $finalResult .= '{"id_olt":' . $id_olt . ', "onu":[';
                                        $finalResult .= json_encode($data) . ']}';

                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                        curl_setopt($ch, CURLOPT_URL, $sUrl);
                                        curl_setopt($ch, CURLOPT_POST, true);
                                        curl_setopt($ch, CURLOPT_POSTFIELDS, $finalResult);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                        $respuesta = curl_exec($ch);
                                        $respuesta = json_decode($respuesta, true);

                                        if ($audi_sn == 1) {
                                            $resp = "reconexion_servicio";
                                            $respuesta1 = $respuesta[$resp];

                                            if ($respuesta1) {
                                                $respuesta1 = "Accion sin errores";
                                            } else {
                                                $respuesta1 = "Accion presenta errores en sn";
                                            }

                                            $error = "";

                                            if (count($respuesta["errores"]) > 0) {
                                                for ($i = 0; $i < count($respuesta["errores"]); $i++) {
                                                    $error .= $respuesta["errores"][$i]["error"];
                                                }
                                            } else {
                                                $error = "Ninguno";
                                            }

                                            $respuesta = $respuesta1 . ". Errores: " . $error;

                                            $sql_cabecera = "INSERT INTO isp.int_audi_cr(   id_empresa,     id_sucursal,                id_usuario,         tipo_accion,            fecha,
                                                                                        tipo_de_cr,     n_abonados_seleccionados,   n_abonados_afectados,   respuesta_api,      fecha_server,
                                                                                        usa_api,        id_modulo,                  script_api,         id_dispositivo)
                                                                            VALUES(     $id_empresa,    $id_sucursal,               $idUser,           2,                       '$fecha_rec',
                                                                                        4,              1,                          1,                      '$respuesta',         NOW(),
                                                                                        $usa_api,       1,                          '$finalResult',     $id_olt)";
                                            $oCon->QueryT($sql_cabecera);

                                            $sql = "SELECT MAX(id) as id_audi FROM isp.int_audi_cr";
                                            $oCon->Query($sql);
                                            $id_audi_cr = $oCon->f('id_audi');
                                            $oCon->Free();

                                            if (strlen($respuesta_api) == 0) {
                                                $respuesta_api = "Sin respuesta";
                                            }
                                            $sqlDetalle = "INSERT INTO isp.int_audi_cr_det(id_audi_cr, id_contrato, respuesta_api, fecha_server) VALUES ($id_audi_cr, $idContrato, '$respuesta', NOW())";
                                            $oCon->QueryT($sqlDetalle);
                                        }

                                        if ($respuesta) {
                                            $resp = $this->reconectarEquipo($array_rec, $op);
                                            $msn = $respuesta;
                                        } else {
                                            $msn = "Error de reconexion";
                                        }
                                    } else {
                                        $msn = "Equipo sin reconexion fisica, no cuenta con vlan";
                                    }
                                } else if ($id_tipo_prod == 7) {
                                    $serial = $n_identificativo;

                                    $headers = array(
                                        "Content-Type:application/json"
                                    );

                                    $dataCatv = array(
                                        "sn" => $serial
                                    );

                                    $finalResultCatv = "";
                                    $finalResultCatv .= '{"id_olt":' . $id_olt . ', "onu":[';
                                    $finalResultCatv .= json_encode($dataCatv) . ']}';

                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                    curl_setopt($ch, CURLOPT_URL, $sUrlCatv);
                                    curl_setopt($ch, CURLOPT_POST, true);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $finalResultCatv);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    $respuestaCatv = curl_exec($ch);
                                    $respuestaCatv = json_decode($respuestaCatv, true);

                                    if ($audi_sn == 1) {
                                        if (strlen($respuestaCatv) == 0) {
                                            $msn = "RECONEXION CATV SIN ERRORES";
                                        } else if ($respuestaCatv == false) {
                                            $msn = "RECONEXION CATV ERROR EN SINTAXIS";
                                        } else {
                                            $msn = "RECONEXION CATV ERROR EN SINTAXIS";
                                        }

                                        $respuesta = $msn;

                                        $sql_cabecera = "INSERT INTO isp.int_audi_cr(   id_empresa,     id_sucursal,                id_usuario,         tipo_accion,            fecha,
                                                                                    tipo_de_cr,     n_abonados_seleccionados,   n_abonados_afectados,   respuesta_api,      fecha_server,
                                                                                    usa_api,        id_modulo,                  script_api,         id_dispositivo)
                                                                        VALUES(     $id_empresa,    $id_sucursal,               $idUser,           2,                       '$fecha_rec',
                                                                                    4,              1,                          1,                      '$respuesta',         NOW(),
                                                                                    $usa_api,       1,                          '$finalResult',     $id_olt)";
                                        $oCon->QueryT($sql_cabecera);

                                        $sql = "SELECT MAX(id) as id_audi FROM isp.int_audi_cr";
                                        $oCon->Query($sql);
                                        $id_audi_cr = $oCon->f('id_audi');
                                        $oCon->Free();

                                        if (strlen($respuesta_api) == 0) {
                                            $respuesta_api = "Sin respuesta";
                                        }
                                        $sqlDetalle = "INSERT INTO isp.int_audi_cr_det(id_audi_cr, id_contrato, respuesta_api, fecha_server) VALUES ($id_audi_cr, $idContrato, '$respuesta', NOW())";
                                        $oCon->QueryT($sqlDetalle);
                                    }

                                    if (strlen($respuestaCatv) == 0) {
                                        $resp = $this->reconectarEquipo($array_rec, $op);
                                        $msn = $respuesta;
                                    } else if ($respuestaCatv == false) {
                                        $msn = "RECONEXION CATV ERROR EN SINTAXIS";
                                    } else {
                                        $msn = "RECONEXION CATV ERROR EN SINTAXIS";
                                    }
                                } else {
                                    $resp = $this->reconectarEquipo($array_rec, $op);
                                    $msn = "Equipo sin reconexion fisica, no esta acitva api, es tipo tv";
                                }
                            } else if ($sistema == "EPON") { //EPON
                                if (filter_var($n_identificativo, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {

                                    $sql = "SELECT nom_clpv, nombre, apellido FROM isp.contrato_clpv WHERE id = $idContrato";
                                    if ($oCon->Query($sql)) {
                                        if ($oCon->NumFilas() > 0) {
                                            $nom_clpv   = $oCon->f('nom_clpv');
                                            $nombre     = $oCon->f('nombre');
                                            $apellido   = $oCon->f('apellido');
                                        }
                                    }
                                    $oCon->Free();

                                    if (strlen($nom_clpv) == 0) {
                                        $nom_clpv = $nombre . "_" . $apellido;
                                    }

                                    $nom_clpv   = utf8_encode($nom_clpv);
                                    $nom_clpv   = preg_replace("/\s+/", "_", $nom_clpv);

                                    $ip = $n_identificativo;

                                    $headers = array(
                                        "Content-Type:application/json"
                                    );

                                    $data = array(
                                        "direccion_ip" => $ip,
                                        "lista" => $list_reconexion,
                                        "comentario" => $nom_clpv
                                    );

                                    $finalResult = "";
                                    $finalResult .= '{"id_mk":' . $id_olt . ', "host":[';
                                    $finalResult .= json_encode($data) . ']}';

                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                    curl_setopt($ch, CURLOPT_URL, $sUrl);
                                    curl_setopt($ch, CURLOPT_POST, true);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $finalResult);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    $respuesta = curl_exec($ch);
                                    $respuesta = json_decode($respuesta, true);

                                    if ($audi_sn == 1) {
                                        $resp = "reconexion_servicio";
                                        $respuesta1 = $respuesta[$resp];

                                        if ($respuesta1) {
                                            $respuesta1 = "Accion sin errores";
                                        } else {
                                            $respuesta1 = "Accion presenta errores en sn";
                                        }

                                        $error = "";

                                        if (count($respuesta["errores"]) > 0) {
                                            for ($i = 0; $i < count($respuesta["errores"]); $i++) {
                                                $error .= $respuesta["errores"][$i]["error"];
                                            }
                                        } else {
                                            $error = "Ninguno";
                                        }

                                        $respuesta = $respuesta1 . ". Errores: " . $error;

                                        $sql_cabecera = "INSERT INTO isp.int_audi_cr(   id_empresa,     id_sucursal,                id_usuario,         tipo_accion,            fecha,
                                                                                    tipo_de_cr,     n_abonados_seleccionados,   n_abonados_afectados,   respuesta_api,      fecha_server,
                                                                                    usa_api,        id_modulo,                  script_api,         id_dispositivo)
                                                                        VALUES(     $id_empresa,    $id_sucursal,               $idUser,           2,                       '$fecha_rec',
                                                                                    4,              1,                          1,                      '$respuesta',         NOW(),
                                                                                    $usa_api,       1,                          '$finalResult',     $id_olt)";
                                        $oCon->QueryT($sql_cabecera);

                                        $sql = "SELECT MAX(id) as id_audi FROM isp.int_audi_cr";
                                        $oCon->Query($sql);
                                        $id_audi_cr = $oCon->f('id_audi');
                                        $oCon->Free();

                                        if (strlen($respuesta_api) == 0) {
                                            $respuesta_api = "Sin respuesta";
                                        }
                                        $sqlDetalle = "INSERT INTO isp.int_audi_cr_det(id_audi_cr, id_contrato, respuesta_api, fecha_server) VALUES ($id_audi_cr, $idContrato, '$respuesta', NOW())";
                                        $oCon->QueryT($sqlDetalle);
                                    }

                                    if ($respuesta) {
                                        $resp = $this->reconectarEquipo($array_rec, $op);
                                        $msn = $respuesta;
                                    } else {
                                        $resp = $this->reconectarEquipo($array_rec, $op);
                                        $msn = $respuesta;
                                    }
                                } else {
                                    $resp = $this->reconectarEquipo($array_rec, $op);
                                    $msn = "Equipo sin reconexion fisica, ip invalida";
                                }
                            }
                        } else if ($estado_api == "A" && $id_tipo_prod != 1 && $id_api == 4 && !empty($id_tarjeta_r)) {

                            //NUEVO WS
                            $Webservice = new Webservice($oCon);
                            $parametros = $Webservice->parametrosWS();

                            $serial = $id_tarjeta_r;
                            $op_inter = false;
                            $op_catv = false;

                            if ($id_tipo_prod == 2) {
                                $op_inter = true;
                            } else if ($id_tipo_prod == 7) {
                                $op_catv = true;
                            }

                            $datosOnu = array(
                                "sn" => $serial,
                                "vlan" => intval($vlan_r)
                            );

                            $datosEnvio = array(
                                "id_olt" => intval($id_olt),
                                "onu" => $datosOnu,
                                "internet" => $op_inter,
                                "catv" => $op_catv
                            );

                            $tipo_comando   = "RECONEXION_EQUIPO";
                            $tipo_sistema   = 1;
                            $envio_get      = "";
                            $envio_post     = $datosEnvio; //ENVIAR SIEMPRE EN ARRAY LA CLASE WEBSERVICE TRANSFORMA A JSON
                            array_push($parametros, $id_api, $tipo_comando, $tipo_sistema);

                            $respuesta_accion  = $Webservice->enviaComando($parametros, $envio_get, $envio_post);

                            $respuesta = $respuesta_accion["reconectado"];

                            if ($respuesta) {
                                $resp = $this->reconectarEquipo($array_rec, $op);
                                $msn = $resp;
                            } else {
                                $msn = "Error de reconexion";
                            }

                            $finalResult = json_encode($datosEnvio);
                            $respuesta = json_encode($respuesta_accion);

                            $sql_cabecera = "INSERT INTO isp.int_audi_cr(   id_empresa,     id_sucursal,                id_usuario,         tipo_accion,            fecha,
                                                                        tipo_de_cr,     n_abonados_seleccionados,   n_abonados_afectados,   respuesta_api,      fecha_server,
                                                                        usa_api,        id_modulo,                  script_api,         id_dispositivo)
                                                            VALUES(     $id_empresa,    $id_sucursal,               $idUser,           2,                       '$fecha_rec',
                                                                        4,              1,                          1,                      '$respuesta',         NOW(),
                                                                        $usa_api,       1,                          '$finalResult',     $id_olt)";
                            $oCon->QueryT($sql_cabecera);

                            $sql = "SELECT MAX(id) as id_audi FROM isp.int_audi_cr";
                            $oCon->Query($sql);
                            $id_audi_cr = $oCon->f('id_audi');
                            $oCon->Free();

                            $sqlDetalle = "INSERT INTO isp.int_audi_cr_det(id_audi_cr, id_contrato, respuesta_api, fecha_server, script_api) VALUES ($id_audi_cr, $idContrato, '$respuesta', NOW(), '$finalResult')";
                            $oCon->QueryT($sqlDetalle);
                        } else {
                            $resp = $this->reconectarEquipo($array_rec, $op);
                            $msn = "";
                        }
                    } else {

                        $array_rec = array(
                            "id_empresa"  => $id_empresa_r,
                            "usuario"  => $idUser,
                            "id_contrato"  => $idContrato,
                            "id_clpv"  => $codigoCliente,
                            "id_sucursal"  => $id_sucursal_r,
                            "serial"  => "",
                            "id_caja_pack"  => $id_caja_pack,
                            "fecha_considerar"  => $fecha_rec,
                            "comentario_os"  => $comentario_os,
                            "vlan" => $vlan
                        );
                        
                        $resp = $this->reconectarEquipo($array_rec, $op);
                        $msn = "";
                    }
                } while ($oCon1->SiguienteRegistro());
            } else {
                $msn = "Contrato sin equipos para reconectar";
            }
        }
        $oCon1->Free();

        $result = $msn;

        return $result;
    }
    // CORTE DE SERVICIO DESDDE CORTE EN LOTES
    function cortarServicioLotes($contratos_corte, $tipo_corte, $id_audi_cr, $numero_abonados_ini, $numero_abonados, $respuesta, $usa_api, $finalResult, $id_dispositivo)
    {
        global $DSN_Ifx, $DSN;

        session_start();

        $oCon1 = new Dbo;
        $oCon1->DSN = $DSN;
        $oCon1->Conectar();

        $oCon = $this->oCon;

        $id_empresa     = $_SESSION['U_EMPRESA'];
        $id_sucursal    = $_SESSION['U_SUCURSAL'];
        $idUser = $_SESSION['U_ID'];

        $fecha_corte = date('Y-m-d');

        $contratos_corte = json_decode($contratos_corte, true);

        try {

            if ($tipo_corte == 0) {
                $filtroTipo = "";
            } else {
                $filtroTipo = " and c.id_tipo_prod = $tipo_corte";
            }
            $sql_cabecera = "INSERT INTO isp.int_audi_cr(   id_empresa,     id_sucursal,                id_usuario,         tipo_accion,            fecha,
                                                        tipo_de_cr,     n_abonados_seleccionados,   n_abonados_afectados,   respuesta_api,      fecha_server,
                                                        usa_api,        script_api,                 id_dispositivo,     id_modulo)
                                            VALUES(     $id_empresa,    $id_sucursal,               $idUser,           1,                       '$fecha_corte',
                                                        $tipo_corte,    $numero_abonados_ini,       $numero_abonados,       '$respuesta',         NOW(),
                                                        $usa_api,       '$finalResult',             $id_dispositivo,    3)";
            $oCon->QueryT($sql_cabecera);

            $sql = "SELECT MAX(id) as id_audi FROM isp.int_audi_cr";
            $oCon->Query($sql);
            $id_audi_cr = $oCon->f('id_audi');
            $oCon->Free();

            for ($i = 0; $i < count($contratos_corte); $i++) {

                $idContrato = $contratos_corte[$i];

                $sql = "SELECT a.id, b.id_empresa, b.id_sucursal, b.id_tarjeta, b.ip, b.puerto_pon, b.id_tipo_prod, b.id_dispositivo, b.id_clpv
                        from isp.int_contrato_caja_pack a, isp.int_contrato_caja b, isp.int_paquetes c
                        where 
                        a.id_prod = c.id AND
                        a.id_caja = b.id AND
                        a.id_contrato = b.id_contrato AND
                        a.id_clpv = b.id_clpv AND
                        a.id_contrato = $idContrato AND    
                        a.estado = 'A'
                        $filtroTipo";
                if ($oCon1->Query($sql)) {
                    if ($oCon1->NumFilas() > 0) {
                        do {

                            $id_caja_pack   = $oCon1->f('id');
                            $id_empresa_r   = $oCon1->f('id_empresa');
                            $id_sucursal_r  = $oCon1->f('id_sucursal');
                            $id_tarjeta_r   = $oCon1->f('id_tarjeta');
                            $ip_r           = $oCon1->f('ip');
                            $puerto_pon_r   = $oCon1->f('puerto_pon');
                            $id_tipo_prod   = $oCon1->f('id_tipo_prod');
                            $id_olt         = $oCon1->f('id_dispositivo');
                            $id_clpv        = $oCon1->f('id_clpv');
                            $comentario_os  = "CORTE DE SERVICIO EN LOTES - " . $fecha_corte;

                            if ($id_tipo_prod == 2) {
                                if ($id_api == 1 || $id_api == 2) {
                                    $n_identificativo = $id_tarjeta_r;
                                } else if ($id_api == 3) {
                                    if (strlen($puerto_pon_r) == 0) {
                                        $n_identificativo = $ip_r;
                                    } else {
                                        $n_identificativo = $puerto_pon_r;
                                    }
                                }
                            } else {
                                $n_identificativo = 0;
                            }

                            $array_rec = array(
                                "id_empresa"  => $id_empresa_r,
                                "usuario"  => $idUser,
                                "id_contrato"  => $idContrato,
                                "id_clpv"  => $id_clpv,
                                "id_sucursal"  => $id_sucursal_r,
                                "serial"  => $n_identificativo,
                                "id_caja_pack"  => $id_caja_pack,
                                "fecha_considerar"  => $fecha_corte,
                                "comentario_os"  => $comentario_os
                            );

                            $this->cortarEquipo($array_rec);
                        } while ($oCon1->SiguienteRegistro());
                    }
                }
                $oCon1->Free();

                $sqlDetalle = "INSERT INTO isp.int_audi_cr_det(id_audi_cr, id_contrato, fecha_server) VALUES ($id_audi_cr, $idContrato, NOW())";
                $oCon->QueryT($sqlDetalle);
            }

            return "ok";
        } catch (Exception $e) {
            $oCon->QueryT('ROLLBACK;');
        }
        return $result;
    }

    function reconectarServicioLotes($contratos_corte, $tipo_corte, $id_audi_cr, $numero_abonados_ini, $numero_abonados, $respuesta, $usa_api, $finalResult, $id_dispositivo)
    {
        global $DSN_Ifx, $DSN;

        session_start();

        $oCon1 = new Dbo;
        $oCon1->DSN = $DSN;
        $oCon1->Conectar();

        $oCon = $this->oCon;

        $id_empresa     = $_SESSION['U_EMPRESA'];
        $id_sucursal    = $_SESSION['U_SUCURSAL'];
        $idUser = $_SESSION['U_ID'];

        $fecha_corte = date('Y-m-d');

        $contratos_corte = json_decode($contratos_corte, true);

        try {

            if ($tipo_corte == 0) {
                $filtroTipo = "";
            } else {
                $filtroTipo = " and c.id_tipo_prod = $tipo_corte";
            }

            $sql_cabecera = "INSERT INTO isp.int_audi_cr(   id_empresa,     id_sucursal,                id_usuario,         tipo_accion,            fecha,
                                                        tipo_de_cr,     n_abonados_seleccionados,   n_abonados_afectados,   respuesta_api,      fecha_server,
                                                        usa_api,        script_api,                 id_dispositivo,     id_modulo)
                                            VALUES(     $id_empresa,    $id_sucursal,               $idUser,           2,                       '$fecha_corte',
                                                        $tipo_corte,    $numero_abonados_ini,       $numero_abonados,       '$respuesta',         NOW(),
                                                        $usa_api,       '$finalResult',             $id_dispositivo,    3)";
            $oCon->QueryT($sql_cabecera);

            $sql = "SELECT MAX(id) as id_audi FROM isp.int_audi_cr";
            $oCon->Query($sql);
            $id_audi_cr = $oCon->f('id_audi');
            $oCon->Free();

            for ($i = 0; $i < count($contratos_corte); $i++) {

                $idContrato = $contratos_corte[$i];

                $sql = "SELECT a.id, b.id_empresa, b.id_sucursal, b.id_tarjeta, b.ip, b.puerto_pon, b.id_tipo_prod, b.id_dispositivo, b.id_clpv, b.vlan
                        from isp.int_contrato_caja_pack a, isp.int_contrato_caja b, isp.int_paquetes c
                        where 
                        a.id_prod = c.id AND
                        a.id_caja = b.id and
                        a.id_contrato = b.id_contrato and
                        a.id_clpv = b.id_clpv and
                        a.id_contrato = $idContrato and    
                        a.estado in ('C','D')
                        $filtroTipo";
                if ($oCon1->Query($sql)) {
                    if ($oCon1->NumFilas() > 0) {
                        do {

                            $id_caja_pack   = $oCon1->f('id');
                            $id_empresa_r   = $oCon1->f('id_empresa');
                            $id_sucursal_r  = $oCon1->f('id_sucursal');
                            $id_tarjeta_r   = $oCon1->f('id_tarjeta');
                            $ip_r           = $oCon1->f('ip');
                            $puerto_pon_r   = $oCon1->f('puerto_pon');
                            $id_tipo_prod   = $oCon1->f('id_tipo_prod');
                            $id_olt         = $oCon1->f('id_dispositivo');
                            $id_clpv        = $oCon1->f('id_clpv');
                            $vlan           = $oCon1->f('vlan');
                            $comentario_os  = "RECONEXION DE SERVICIO EN LOTES - " . $fecha_corte;

                            if ($id_tipo_prod == 2) {
                                if ($id_api == 1 || $id_api == 2) {
                                    $n_identificativo = $id_tarjeta_r;
                                } else if ($id_api == 3) {
                                    if (strlen($puerto_pon_r) == 0) {
                                        $n_identificativo = $ip_r;
                                    } else {
                                        $n_identificativo = $puerto_pon_r;
                                    }
                                }
                            } else {
                                $n_identificativo = 0;
                            }

                            $array_rec = array(
                                "id_empresa"  => $id_empresa_r,
                                "usuario"  => $idUser,
                                "id_contrato"  => $idContrato,
                                "id_clpv"  => $id_clpv,
                                "id_sucursal"  => $id_sucursal_r,
                                "serial"  => $n_identificativo,
                                "id_caja_pack"  => $id_caja_pack,
                                "fecha_considerar"  => $fecha_corte,
                                "comentario_os"  => $comentario_os,
                                "vlan" => $vlan
                            );

                            $this->reconectarEquipo($array_rec);
                        } while ($oCon1->SiguienteRegistro());
                    }
                }
                $oCon1->Free();

                $sqlDetalle = "INSERT INTO isp.int_audi_cr_det(id_audi_cr, id_contrato, fecha_server) VALUES ($id_audi_cr, $idContrato, NOW())";
                $oCon->QueryT($sqlDetalle);
            }

            return "ok";
        } catch (Exception $e) {
            $oCon->QueryT('ROLLBACK;');
        }
        return $result;
    }

    function auditoriaReconexionFactLotes($array_contratos_rec, $array_contratos_msn)
    {

        $oCon = $this->oCon;

        $id_empresa     = $_SESSION['U_EMPRESA'];
        $id_sucursal    = $_SESSION['U_SUCURSAL'];
        $idUser         = $_SESSION['U_ID'];
        $estado_api     = $_SESSION['estado_api'];

        $fecha_reconexion = date('Y-m-d');

        $contratos_reconexion = $array_contratos_rec;

        $numero_abonados_ini    = count($array_contratos_rec);
        $numero_abonados        = $numero_abonados_ini;

        if ($estado_api == "A") {
            $usa_api = 1;
        } else {
            $usa_api = 2;
        }

        $errores = "";
        $respuesta = "";
        for ($i = 0; $i < count($contratos_reconexion); $i++) {
            $idContrato = $contratos_reconexion[$i];
            $respuesta_api1 = $array_contratos_msn[$i][$idContrato]["reconexion_servicio"];
            $data_error = $array_contratos_msn[$i][$idContrato]["errores"];
            if (count($data_error) > 0) {
                for ($i = 0; $i < count($data_error); $i++) {
                    $errores .= $data_error[$i]["error"];
                }
            } else {
                $errores = "Ninguno";
            }

            if ($respuesta_api1) {
                $respuesta_api1 = "Accion sin errores";
            } else {
                $respuesta_api1 = "Accion presenta errores en sn";
            }

            $respuesta .= $respuesta_api1 . ". Errores: " . $errores;
        }

        $respuestas_fn = "";
        $respuestas_fn = "Respuestas api: " . $respuesta;

        $sql_cabecera = "INSERT INTO isp.int_audi_cr(   id_empresa,     id_sucursal,                id_usuario,         tipo_accion,            fecha,
                                                    tipo_de_cr,     n_abonados_seleccionados,   n_abonados_afectados,   respuesta_api,      fecha_server,
                                                    usa_api,        id_modulo)
                                        VALUES(     $id_empresa,    $id_sucursal,               $idUser,           2,                       '$fecha_reconexion',
                                                    4,              $numero_abonados_ini,       $numero_abonados,       '$respuestas_fn',         NOW(),
                                                    $usa_api,       4)";
        $oCon->QueryT($sql_cabecera);

        $sql = "SELECT MAX(id) as id_audi FROM isp.int_audi_cr";
        $oCon->Query($sql);
        $id_audi_cr = $oCon->f('id_audi');
        $oCon->Free();

        for ($i = 0; $i < count($contratos_reconexion); $i++) {

            $idContrato = $contratos_reconexion[$i];
            $respuesta_api = $array_contratos_msn[$i][$idContrato]["reconexion_servicio"];

            if ($respuesta_api) {
                $respuesta_api = "Accion sin errores";
            } else {
                $respuesta_api = "Accion presenta errores en sn";
            }
            $sqlDetalle = "INSERT INTO isp.int_audi_cr_det(id_audi_cr, id_contrato, respuesta_api, fecha_server) VALUES ($id_audi_cr, $idContrato, '$respuesta_api', NOW())";

            $oCon->QueryT($sqlDetalle);
        }

        return "ok";


        return $result;
    }

    //*******FUNCIONES PARA CAMBIO DE PLAN */
    function cambiarPlan($datos)
    {

        $oCon = $this->oCon;

        $id_empresa = $datos['id_empresa'];
        $id_sucursal = $datos['id_sucursal'];
        $id_usuario = $datos['id_usuario'];
        $id_caja = $datos['id_caja'];
        $id_caja_pack = $datos['id_caja_pack'];
        $id_nuevo_plan = $datos['id_nuevo_plan'];
        $id_contrato = $datos['id_contrato'];
        $id_clpv = $datos['id_clpv'];
        $precio_nuevo_plan = $datos['precio_nuevo_plan'];
        $velocidad_subida = $datos['val_subida'];
        $velocidad_bajada = $datos['val_bajada'];

        $dias_consumo = $datos['dias_consumo'];
        $cuota_actual_sn = $datos['mensualidad_ini'];
        $orden_servicio_sn = $datos['genera_os'];
        $comentario_os = $datos['observacionesOs'];

        $data_nuevo_plan = explode("$&", $id_nuevo_plan);

        $id_nuevo_plan = $data_nuevo_plan[1];
        $cod_nuevo_plan = $data_nuevo_plan[0];
        $resp = false;

        $sql = "SELECT estado FROM isp.int_contrato_caja WHERE id = $id_caja";
        $oCon->Query($sql);
        $estado_eq = $oCon->f('estado');
        $oCon->Free();

        $sql = "SELECT id_caja FROM isp.int_contrato_caja_pack WHERE id = $id_caja_pack";
        $oCon->Query($sql);
        $id_caja = $oCon->f('id_caja');
        $oCon->Free();
        $sql = "SELECT prod_cod_prod FROM isp.int_paquetes WHERE id = $id_nuevo_plan";
        $oCon->Query($sql);
        $prod_cod_prod = $oCon->f('prod_cod_prod');
        $oCon->Free();
        //INACTIVA EL PLAN ACTUAL
        $sql = "UPDATE isp.int_contrato_caja_pack 
                SET estado = 'E', activo = 'N'
                WHERE id=$id_caja_pack AND id_clpv=$id_clpv AND id_contrato=$id_contrato";
        $oCon->QueryT($sql);
        //ACTUALIZA VELOCIDAD SI LA HAY
        if(!empty($velocidad_subida)){
            $sql = "UPDATE isp.int_contrato_caja
                    SET vel_subida = '$velocidad_subida', vel_bajada = '$velocidad_bajada'
                    WHERE id=$id_caja AND id_clpv=$id_clpv AND id_contrato=$id_contrato";
            $oCon->QueryT($sql);
        }
        //INSERTA EL NUEVO PLAN
        $sql = "INSERT INTO isp.int_contrato_caja_pack(id_clpv,          id_contrato,           id_caja,       id_prod,
                cod_prod,         precio,                fecha,         estado,
                activo,           user_web,              fecha_server,  tipo,
                indefinido,       precio_tmp) 
            VALUES($id_clpv,         $id_contrato,          $id_caja,      $id_nuevo_plan,
                '$prod_cod_prod', $precio_nuevo_plan,    NOW(),         '$estado_eq',
                'S',               $id_usuario,           NOW(),         'P',
                'S',               $precio_nuevo_plan)";
        $oCon->QueryT($sql);
        if ($dias_consumo == 1) {
            //SE SELECCIONA EL ULTIMO INGRESO DE LA isp.int_CONTRATO_CAJA
            $sql = "SELECT id
                    FROM isp.int_contrato_caja_pack
                    WHERE id_clpv=$id_clpv AND id_contrato=$id_contrato ORDER BY id DESC LIMIT 1";
            $oCon->Query($sql);
            $id_contrato_caja_pack_new = $oCon->f('id');
            $oCon->Free();
            //SE SELECCIONA LA SUCURSAL Y EL ID DEL PAGO
            $sql = "SELECT id_pago, id_sucursal
                    FROM isp.contrato_pago_pack
                    WHERE id_clpv=$id_clpv AND id_contrato=$id_contrato AND fecha=LAST_DAY(CURRENT_DATE) LIMIT 1";
            $oCon->Query($sql);
            $id_sucursal = $oCon->f('id_sucursal');
            $id_pago = $oCon->f('id_pago');
            $oCon->Free();

            if (empty($id_sucursal)) {
                $id_sucursal = $_SESSION["U_SUCURSAL"];
            }

            //INSERTA EL NUEVO DETALLE
            $sql = "INSERT INTO isp.contrato_pago_pack (id_empresa,         id_sucursal,            id_clpv,             id_contrato,           id_caja,     id_pack,
                                                    id_pago,            id_prod,                cod_prod,            fecha,                 tipo,        tarifa,      
                                                    dias,       
                                                    dias_uso,           
                                                    dias_no_uso,        
                                                    valor_uso,      
                                                    valor_no_uso,       
                                                    valor_dia,   
                                                    estado,             user_web,               fecha_server,        tarifa_tmp,            tarifa_tmp_1,       
                                                    detalle,            
                                                    fecha_cambio_plan
                                                    )
                                            VALUES ($id_empresa,        $id_sucursal,           $id_clpv,            $id_contrato,          $id_caja,    $id_contrato_caja_pack_new,
                                                    $id_pago,           $id_nuevo_plan,         '$prod_cod_prod',   LAST_DAY(CURRENT_DATE),       'M',         $precio_nuevo_plan, 
                                                    EXTRACT(DAY FROM LAST_DAY(CURRENT_DATE)),
                                                    DATEDIFF(LAST_DAY(CURRENT_DATE),CURRENT_DATE),
                                                    EXTRACT(DAY FROM CURRENT_DATE),        
                                                    (($precio_nuevo_plan/EXTRACT(DAY FROM LAST_DAY(CURRENT_DATE)))*((EXTRACT(DAY FROM LAST_DAY(CURRENT_DATE))-EXTRACT(DAY FROM NOW())))),
                                                    (($precio_nuevo_plan/EXTRACT(DAY FROM LAST_DAY(CURRENT_DATE)))*(EXTRACT(DAY FROM NOW()))), 
                                                    ($precio_nuevo_plan/EXTRACT(DAY FROM LAST_DAY(CURRENT_DATE))),
                                                    'A',                '$id_usuario',          NOW(),               $precio_nuevo_plan,    $precio_nuevo_plan,
                                                    'Detalle insertado por cambio de plan', 
                                                    NOW()
                                    )";

            $oCon->QueryT($sql);
            if ($cuota_actual_sn == 1) {
                //ACTUALIZA LOS VALORES DE LA CUOTA ACTUAL
                $sql = "UPDATE isp.contrato_pago_pack 
                        SET dias_uso=EXTRACT(DAY FROM CURRENT_DATE),
                            dias_no_uso=DATEDIFF(LAST_DAY(CURRENT_DATE),CURRENT_DATE),
                            valor_uso=((tarifa*EXTRACT(DAY FROM CURRENT_DATE))/dias),
                            valor_no_uso=((tarifa/dias)*(dias-EXTRACT(DAY FROM NOW()))),
                            detalle='Detalle modificado por cambio de plan', estado='I'
                        WHERE id_pack=$id_caja_pack AND 
                            fecha=LAST_DAY(CURRENT_DATE) AND 
                            valor_pago = 0";
                $oCon->QueryT($sql);
            }
            //ACTUALIZA LOS VALORES DE LAS SIGUIENTES CUOTAS
            $sql = "UPDATE isp.contrato_pago_pack 
                    SET id_prod=$id_nuevo_plan,
                        cod_prod='$prod_cod_prod',
                        id_pack=$id_contrato_caja_pack_new,
                        tarifa=$precio_nuevo_plan, 
                        tarifa_tmp=$precio_nuevo_plan
                    WHERE id_pack=$id_caja_pack AND 
                        fecha>LAST_DAY(CURRENT_DATE)";
            $oCon->QueryT($sql);
            if ($cuota_actual_sn == 1) {
                $sql = "UPDATE isp.contrato_pago 
                        SET 
                            tarifa=
                            (
                                SELECT SUM(tarifa)
                                FROM isp.contrato_pago_pack 
                                WHERE id_clpv=$id_clpv AND id_contrato=$id_contrato AND fecha=LAST_DAY(CURRENT_DATE)
                            ),
                            valor_uso=
                            (
                                SELECT SUM(valor_uso)
                                FROM isp.contrato_pago_pack 
                                WHERE id_clpv=$id_clpv AND id_contrato=$id_contrato AND fecha=LAST_DAY(CURRENT_DATE)
                            ),
                            valor_no_uso=
                            (
                                SELECT SUM(valor_no_uso)
                                FROM isp.contrato_pago_pack 
                                WHERE id_clpv=$id_clpv AND id_contrato=$id_contrato AND fecha=LAST_DAY(CURRENT_DATE)
                            ),
                            valor_uso_tmp=
                            (
                                SELECT SUM(valor_uso)
                                FROM isp.contrato_pago_pack 
                                WHERE id_clpv=$id_clpv AND id_contrato=$id_contrato AND fecha=LAST_DAY(CURRENT_DATE)
                            ),
                            valor_no_uso_tmp=
                            (
                                SELECT SUM(valor_no_uso)
                                FROM isp.contrato_pago_pack 
                                WHERE id_clpv=$id_clpv AND id_contrato=$id_contrato AND fecha=LAST_DAY(CURRENT_DATE)
                            ),
                            detalle='Cuota modificada por cambio de plan'
                        WHERE 
                            id_contrato=$id_contrato AND 
                            id_clpv=$id_clpv AND 
                            fecha=LAST_DAY(CURRENT_DATE) AND tipo != 'A'";
                $oCon->QueryT($sql);
            }
            $sql = "UPDATE isp.contrato_pago 
                    SET 
                        tarifa=
                        (
                            SELECT SUM(precio) 
                            FROM isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A'
                        ),
                        tarifa_tmp=
                        (
                            SELECT SUM(precio) 
                            FROM isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A'
                        )
                    WHERE 
                        id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY(CURRENT_DATE)";
            $oCon->QueryT($sql);
            $sql = "UPDATE isp.contrato_clpv
                    SET 
                        tarifa=
                        (
                            SELECT SUM(precio) 
                            FROM isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A'
                        ),
                        tarifa_tmp=
                        (
                            SELECT SUM(precio) 
                            FROM isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A'
                        ),
                        fecha_u_cambio_plan = NOW()
                    WHERE 
                        id=$id_contrato AND id_clpv=$id_clpv";
            $oCon->QueryT($sql);
            //CONTROL PARA TARIFA ESPECIAL
            $sql = "SELECT tarifa_e
                    FROM isp.contrato_clpv 
                    WHERE id_clpv=$id_clpv AND id =$id_contrato";
            $oCon->Query($sql);
            $tarifa_e = $oCon->f('tarifa_e');
            $oCon->Free();
            if ($tarifa_e == "S") {
                $sql = "SELECT descuento_p
                        FROM isp.contrato_descuentos 
                        WHERE id_clpv=$id_clpv AND id_contrato =$id_contrato";
                $oCon->Query($sql);
                $descuento_p = $oCon->f('descuento_p');
                $oCon->Free();
                $sql = "SELECT SUM(precio) AS valor_planes
                        FROM isp.int_contrato_caja_pack 
                        WHERE id_clpv=$id_clpv AND id_contrato =$id_contrato AND estado = 'A' AND activo = 'S'";

                $oCon->Query($sql);
                $valor_planes = $oCon->f('valor_planes');
                $oCon->Free();
                $valor_descuento = ($valor_planes * $descuento_p) / 100;
                $valor_tarifa = $valor_planes - $valor_descuento;
                $sql = "UPDATE isp.contrato_descuentos
                        SET tarifa=$valor_tarifa, planes=$valor_planes, descuento_v=$valor_descuento, userWeb=$id_usuario, fecha_server=NOW()
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);
                $sql = "UPDATE isp.contrato_clpv
                        SET descuento_v=$valor_descuento,monto_pago=$valor_tarifa
                        WHERE id=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);
                $sql = "UPDATE isp.contrato_pago
                        SET descuento=-$valor_descuento
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY(CURRENT_DATE)";
                $oCon->QueryT($sql);
            }
        } else {

            //SE SELECCIONA EL ULTIMO INGRESO DE LA isp.int_CONTRATO_CAJA
            $sql = "SELECT id
                    FROM isp.int_contrato_caja_pack
                    WHERE id_clpv=$id_clpv AND id_contrato=$id_contrato ORDER BY id DESC LIMIT 1";
            $oCon->Query($sql);
            $id_contrato_caja_pack_new = $oCon->f('id');
            $oCon->Free();
            if ($cuota_actual_sn == 1) {
                //ACTUALIZA LOS VALORES DE LAS SIGUIENTES CUOTAS CON LA ACTUAL
                $sql = "UPDATE isp.contrato_pago_pack 
                        SET id_prod=$id_nuevo_plan,
                            cod_prod='$prod_cod_prod',
                            id_pack=$id_contrato_caja_pack_new,
                            tarifa=$precio_nuevo_plan, 
                            tarifa_tmp=$precio_nuevo_plan
                        WHERE id_pack=$id_caja_pack AND 
                            fecha>=LAST_DAY(CURRENT_DATE)";
                $oCon->QueryT($sql);
                $sql = "UPDATE isp.contrato_pago 
                        SET 
                            tarifa=
                            (
                                SELECT SUM(precio) 
                                FROM isp.int_contrato_caja_pack 
                                WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A'
                            ),
                            tarifa_tmp=
                            (
                                SELECT SUM(precio) 
                                FROM isp.int_contrato_caja_pack 
                                WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A'
                            )
                        WHERE 
                            id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>=LAST_DAY(CURRENT_DATE)";
                $oCon->QueryT($sql);
            } else {
                //ACTUALIZA LOS VALORES DE LAS SIGUIENTES CUOTAS SIN LA ACTUAL
                $sql = "UPDATE isp.contrato_pago_pack 
                        SET id_prod=$id_nuevo_plan,
                            cod_prod='$prod_cod_prod',
                            id_pack=$id_contrato_caja_pack_new,
                            tarifa=$precio_nuevo_plan, 
                            tarifa_tmp=$precio_nuevo_plan
                        WHERE id_pack=$id_caja_pack AND 
                            fecha>LAST_DAY(CURRENT_DATE)";
                $oCon->QueryT($sql);

                $sql = "UPDATE isp.contrato_pago 
                        SET 
                            tarifa=
                            (
                                SELECT SUM(precio) 
                                FROM isp.int_contrato_caja_pack 
                                WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A'
                            ),
                            tarifa_tmp=
                            (
                                SELECT SUM(precio) 
                                FROM isp.int_contrato_caja_pack 
                                WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A'
                            )
                        WHERE 
                            id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY(CURRENT_DATE)";
                $oCon->QueryT($sql);
            }

            $sql = "UPDATE isp.contrato_clpv
                    SET 
                        tarifa=
                        (
                            SELECT SUM(precio) 
                            FROM isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A'
                        ),
                        tarifa_tmp=
                        (
                            SELECT SUM(precio) 
                            FROM isp.int_contrato_caja_pack 
                            WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND estado='A'
                        ),
                        fecha_u_cambio_plan = NOW()
                    WHERE 
                        id=$id_contrato AND id_clpv=$id_clpv";
            $oCon->QueryT($sql);
            //CONTROL PARA TARIFA ESPECIAL
            $sql = "SELECT tarifa_e
                    FROM isp.contrato_clpv 
                    WHERE id_clpv=$id_clpv AND id =$id_contrato";
            $oCon->Query($sql);
            $tarifa_e = $oCon->f('tarifa_e');
            $oCon->Free();

            if ($tarifa_e == "S") {
                $sql = "SELECT descuento_p
                        FROM isp.contrato_descuentos 
                        WHERE id_clpv=$id_clpv AND id_contrato =$id_contrato";
                $oCon->Query($sql);
                $descuento_p = $oCon->f('descuento_p');
                $oCon->Free();
                $sql = "SELECT SUM(precio) AS valor_planes
                        FROM isp.int_contrato_caja_pack 
                        WHERE id_clpv=$id_clpv AND id_contrato =$id_contrato AND estado = 'A' AND activo = 'S'";

                $oCon->Query($sql);
                $valor_planes = $oCon->f('valor_planes');
                $oCon->Free();
                $valor_descuento = ($valor_planes * $descuento_p) / 100;
                $valor_tarifa = $valor_planes - $valor_descuento;
                $sql = "UPDATE isp.contrato_descuentos
                        SET tarifa=$valor_tarifa, planes=$valor_planes, descuento_v=$valor_descuento, userWeb=$id_usuario, fecha_server=NOW()
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);
                $sql = "UPDATE isp.contrato_clpv
                        SET descuento_v=$valor_descuento,monto_pago=$valor_tarifa
                        WHERE id=$id_contrato AND id_clpv=$id_clpv";
                $oCon->QueryT($sql);
                $sql = "UPDATE isp.contrato_pago
                        SET descuento=-$valor_descuento
                        WHERE id_contrato=$id_contrato AND id_clpv=$id_clpv AND fecha>LAST_DAY(CURRENT_DATE)";
                $oCon->QueryT($sql);
            }
        }

        if ($orden_servicio_sn == 2) {
            $resp = true;
        } else {
            $resp = $this->generarOsCambioPlan($datos);
        }

        return $resp;
    }

    function generarOsCambioPlan($datos)
    {

        $oCon = $this->oCon;

        $id_empresa = $datos['id_empresa'];
        $id_sucursal = $datos['id_sucursal'];
        $id_usuario = $datos['id_usuario'];
        $id_caja = $datos['id_caja'];
        $id_caja_pack = $datos['id_caja_pack'];
        $id_nuevo_plan = $datos['id_nuevo_plan'];
        $id_contrato = $datos['id_contrato'];
        $id_clpv = $datos['id_clpv'];
        $precio_nuevo_plan = $datos['precio_nuevo_plan'];
        $comentario_os = $datos['observacionesOs'];
        $cambio_plan_sn = $datos['cambio_plan_sn'];
        $tipo_orden_cp = $datos['tipo_orden_cp'];
        $motivo_orden_cp = $datos['motivo_orden_cp'];

        $data_nuevo_plan = explode("-", $id_nuevo_plan);

        $id_nuevo_plan = $data_nuevo_plan[1];
        $cod_nuevo_plan = $data_nuevo_plan[0];

        if (isset($tipo_orden_cp) && $tipo_orden_cp > 0) {
            $tipo_servicio = $tipo_orden_cp;
        } else {
            #LA DESCRIPCION SE PUEDE VISUALIZAR EN LA TABLA isp.int_tipo_proceso de MYSQL
            $descripcion = "AGREGAR PAQUETES";
            $sql = "SELECT id from isp.int_tipo_proceso WHERE descripcion = '$descripcion' LIMIT 1";
            $oCon->Query($sql);
            $tipo_servicio = $oCon->f('id');
            $oCon->Free();
        }

        if ($tipo_servicio != null) {

            if (isset($motivo_orden_cp) && $motivo_orden_cp > 0) {
                $motivo = $motivo_orden_cp;
            } else {

                $sql = "SELECT id from isp.int_motivos_canc WHERE id_proceso = $tipo_servicio LIMIT 1";
                $oCon->Query($sql);
                $resultado = $oCon->f('id');
                $oCon->Free();
                if ($resultado != null) {
                    $motivo = $resultado = $oCon->f('id');
                } else {
                    $motivo = "CAMBIO DE PAQUETE";
                    $sql = "insert into isp.int_motivos_canc(id_proceso,motivo) 
                            VALUES ($tipo_servicio,'$motivo')";
                    $oCon->QueryT($sql);
                    $sql = "SELECT id from isp.int_motivos_canc WHERE id_proceso = $tipo_servicio AND motivo = '$motivo' LIMIT 1";
                    $oCon->Query($sql);
                    $motivo = $oCon->f('id');
                    $oCon->Free();
                }
            }

            if ($motivo != null) {

                #SABER SI EL CONTRATO CUENTA CON TELEVISION ANALOGA PARA DEFINIR EL TIPO DE ESTADO
                $sql = "SELECT id_tipo_prod 
                        from isp.int_contrato_caja
                        WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND id = $id_caja";
                $oCon->Query($sql);
                $id_tipo_prod_c = $oCon->f('id_tipo_prod');
                $oCon->Free();

                #CONTROLAR EL ESTADO DEPENDIENDO DEL TIPO DE SERVICIO
                if (isset($cambio_plan_sn)) {
                    if ($cambio_plan_sn == 1) {
                        if ($id_tipo_prod_c == 1) {
                            $estado = "PE";
                            $detalle = "Cambio de plan pendiente automatico";
                        } else {
                            $estado = "TE";
                            $detalle = "Cambio de plan ejecutado automaticamente";
                        }
                    } else if ($cambio_plan_sn == 2) {
                        $estado = "TE";
                        $detalle = "Cambio de precio ejecutado automaticamente";
                    }
                } else {
                    if ($id_tipo_prod_c == 1) {
                        $estado = "PE";
                        $detalle = "Cambio de plan pendiente automatico";
                    } else {
                        $estado = "TE";
                        $detalle = "Cambio de plan ejecutado automaticamente";
                    }
                }

                $detalle = $comentario_os;
                //CONTROL DE ORDEN DE TRABAJO
                $sql = "SELECT COUNT(id) as id from isp.instalacion_clpv WHERE
                                    id_clpv    = $id_clpv AND
                                    id_contrato= $id_contrato AND
                                    estado     = 'PE' and
                                    id_proceso = $tipo_servicio AND
                                    id_franja = $id_tipo_prod_c";
                $oCon->Query($sql);
                $ordenes_pendientes = $oCon->f('id');
                $oCon->Free();
                if ($ordenes_pendientes == 0) {
                    #OBTENER LA DIRECCION DEL CONTRATO
                    $sql = "SELECT id_dire, id_sucursal 
                                    from isp.contrato_clpv 
                                    WHERE id = $id_contrato AND 
                                    id_clpv = $id_clpv";
                    $oCon->Query($sql);
                    $id_dire = $oCon->f('id_dire');
                    $id_sucu = $oCon->f('id_sucursal');
                    $oCon->Free();

                    if ($id_dire == null) {
                        $id_dire = 1;
                    }
                    #OBTENER EL SECUENCUAL
                    $sql = "SELECT CAST (max(secuencial) AS INTEGER)  + 1 as secuencial 
                            from isp.instalacion_clpv
                            WHERE id_proceso = $tipo_servicio";
                    $oCon->Query($sql);
                    $secuencial = $oCon->f('secuencial');
                    $oCon->Free();

                    if ($secuencial == null) {
                        $secuencial = 1;
                    };

                    $fechaServer = date("Y-m-d H:i:s");

                    $secuencial_fin = str_pad($secuencial, 9, 0, STR_PAD_LEFT);
                    $fecha_actual = date('Y-m-d');
                    #INGRESAR LA ORDEN DE SERVICIO
                    $sql = "insert into isp.instalacion_clpv(id_empresa, id_sucursal, id_sucursal_ori, id_proceso, 
                                                secuencial, id_clpv, id_contrato, fecha, 
                                                user_web, fecha_server, observaciones, id_direccion, 
                                                id_franja, id_motivo, id_prioridad, solicita, adjunto, 
                                                observacion_cliente,estado_contrato, reconexion_pago, 
                                                fecha_inicio, fecha_fin, estado)
                            VALUES($id_empresa, $id_sucursal, $id_sucu, $tipo_servicio, '$secuencial_fin', 
                            $id_clpv, $id_contrato, '$fecha_actual',$id_usuario, '$fechaServer', '$detalle', $id_dire, 
                            $id_tipo_prod_c, $motivo, 1, '', '', '','AP', 'N', '$fechaServer', '$fechaServer', '$estado')";
                    $oCon->QueryT($sql);
                    #OBTENER EL ULTIMO ID DE LA TABLA instalacion clpv PARA INSERTARLO EN isp.instalacion_prod
                    $sql = "SELECT max(id) AS id 
                            from isp.instalacion_clpv
                            WHERE 
                            id_proceso = $tipo_servicio AND 
                            id_clpv = $id_clpv AND id_contrato = $id_contrato";
                    $oCon->Query($sql);
                    $id_instalacion = $oCon->f('id');
                    $oCon->Free();
                    if ($estado == 'TE') {
                        #INSERTAR ORDEN DE SERVICIO EN LA TABLA isp.instalacion_ejecucion
                        $sql = "insert into isp.instalacion_ejecucion(id_empresa, id_sucursal, id_clpv, id_contrato, 
                                                    id_instalacion, fecha, estado, user_web, fecha_server, 
                                                    observacion_tecnico)
                                VALUES($id_empresa, $id_sucursal, $id_clpv, $id_contrato, '$id_instalacion', 
                                '$fecha_actual', 'A', $id_usuario, '$fechaServer', 'Cambio de plan automatico')";
                        $oCon->QueryT($sql);
                    }
                    $sql = "insert into isp.instalacion_prod(id_empresa, id_sucursal, id_clpv, id_contrato, 
                                                    id_instalacion, id_caja, estado, cambio_sn, 
                                                    fecha_ejecucion)
                            VALUES($id_empresa, $id_sucursal, $id_clpv, $id_contrato, '$id_instalacion', 
                            $id_caja, '$estado', 'N', '$fecha_actual')";
                    $oCon->QueryT($sql);
                }
            }
        }

        $resp = true;

        return $resp;
    }

    function cortarServicioAutomatico($contratos_corte, $tipo_corte, $id_audi_cr, $numero_abonados_ini, $numero_abonados, $respuesta, $usa_api, $finalResult, $id_dispositivo)
    {
        global $DSN_Ifx, $DSN;

        session_start();

        $oCon1 = new Dbo;
        $oCon1->DSN = $DSN;
        $oCon1->Conectar();

        $oCon = $this->oCon;

        $id_empresa     = 1;
        $id_sucursal    = 1;
        $idUser         = 1;

        $fecha_corte = date('Y-m-d');

        $contratos_corte = json_decode($contratos_corte, true);

        try {

            if ($tipo_corte == 0) {
                $filtroTipo = "";
            } else {
                $filtroTipo = " and c.id_tipo_prod = $tipo_corte";
            }
            $sql_cabecera = "INSERT INTO isp.int_audi_cr(   id_empresa,     id_sucursal,                id_usuario,         tipo_accion,            fecha,
                                                        tipo_de_cr,     n_abonados_seleccionados,   n_abonados_afectados,   respuesta_api,      fecha_server,
                                                        usa_api,        script_api,                 id_dispositivo,     id_modulo)
                                            VALUES(     $id_empresa,    $id_sucursal,               $idUser,           1,                       '$fecha_corte',
                                                        $tipo_corte,    $numero_abonados_ini,       $numero_abonados,       '$respuesta',         NOW(),
                                                        $usa_api,       '$finalResult',             $id_dispositivo,    10)";
            $oCon->QueryT($sql_cabecera);

            $sql = "SELECT MAX(id) as id_audi FROM isp.int_audi_cr";
            $oCon->Query($sql);
            $id_audi_cr = $oCon->f('id_audi');
            $oCon->Free();

            for ($i = 0; $i < count($contratos_corte); $i++) {

                $idContrato = $contratos_corte[$i];

                $sql = "SELECT a.id, b.id_empresa, b.id_sucursal, b.id_tarjeta, b.ip, b.puerto_pon, b.id_tipo_prod, b.id_dispositivo, b.id_clpv
                        from isp.int_contrato_caja_pack a, isp.int_contrato_caja b, isp.int_paquetes c
                        where 
                        a.id_prod = c.id AND
                        a.id_caja = b.id AND
                        a.id_contrato = b.id_contrato AND
                        a.id_clpv = b.id_clpv AND
                        a.id_contrato = $idContrato AND    
                        a.estado = 'A'
                        $filtroTipo";
                if ($oCon1->Query($sql)) {
                    if ($oCon1->NumFilas() > 0) {
                        do {

                            $id_caja_pack   = $oCon1->f('id');
                            $id_empresa_r   = $oCon1->f('id_empresa');
                            $id_sucursal_r  = $oCon1->f('id_sucursal');
                            $id_tarjeta_r   = $oCon1->f('id_tarjeta');
                            $ip_r           = $oCon1->f('ip');
                            $puerto_pon_r   = $oCon1->f('puerto_pon');
                            $id_tipo_prod   = $oCon1->f('id_tipo_prod');
                            $id_olt         = $oCon1->f('id_dispositivo');
                            $id_clpv        = $oCon1->f('id_clpv');
                            $comentario_os  = "CORTE DE SERVICIO AUTOMATICO - " . $fecha_corte;

                            if ($id_tipo_prod == 2) {
                                if ($id_api == 1 || $id_api == 2) {
                                    $n_identificativo = $id_tarjeta_r;
                                } else if ($id_api == 3) {
                                    if (strlen($puerto_pon_r) == 0) {
                                        $n_identificativo = $ip_r;
                                    } else {
                                        $n_identificativo = $puerto_pon_r;
                                    }
                                }
                            } else {
                                $n_identificativo = 0;
                            }

                            $array_rec = array(
                                "id_empresa"  => $id_empresa_r,
                                "usuario"  => $idUser,
                                "id_contrato"  => $idContrato,
                                "id_clpv"  => $id_clpv,
                                "id_sucursal"  => $id_sucursal_r,
                                "serial"  => $n_identificativo,
                                "id_caja_pack"  => $id_caja_pack,
                                "fecha_considerar"  => $fecha_corte,
                                "comentario_os"  => $comentario_os
                            );

                            $this->cortarEquipo($array_rec);
                        } while ($oCon1->SiguienteRegistro());
                    }
                }
                $oCon1->Free();

                $sqlDetalle = "INSERT INTO isp.int_audi_cr_det(id_audi_cr, id_contrato, fecha_server) VALUES ($id_audi_cr, $idContrato, NOW())";
                $oCon->QueryT($sqlDetalle);

            }

            return "ok";
        } catch (Exception $e) {
            $oCon->QueryT('ROLLBACK;');
        }
        return $result;
    }

    function generaOsSincVel($datos)
    {

        global $DSN, $DSN_Ifx;

        $oCon2 = new Dbo;
        $oCon2->DSN = $DSN;
        $oCon2->Conectar();

        $oCon = $this->oCon;

        $id_caja = $datos['id_caja'];
        $id_usuario = $_SESSION["U_ID"];
        $id_empresa = $_SESSION["U_EMPRESA"];
        $id_sucursal = $_SESSION["U_SUCURSAL"];
        $detalle    = 'SINCRONIZACION DE VELOCIDAD';
        $respuesta = false;

        $sql = "SELECT id_contrato, id_clpv
                from isp.int_contrato_caja
                WHERE id = $id_caja";
        $oCon->Query($sql);
        $id_contrato    = $oCon->f('id_contrato');
        $id_clpv        = $oCon->f('id_clpv');
        $oCon->Free();

        #LA DESCRIPCION SE PUEDE VISUALIZAR EN LA TABLA isp.int_tipo_proceso de MYSQL
        $descripcion = "OTROS";
        $sql = "SELECT id from isp.int_tipo_proceso WHERE descripcion = '$descripcion' LIMIT 1";
        $oCon->Query($sql);
        $tipo_servicio = $oCon->f('id');
        $oCon->Free();

        if ($tipo_servicio != null) {

            $nom_motivo = "SINCRONIZACION DE VELOCIDAD";

            $sql = "SELECT id from isp.int_motivos_canc WHERE id_proceso = $tipo_servicio AND motivo = '$nom_motivo' LIMIT 1";
            $oCon->Query($sql);
            $resultado = $oCon->f('id');
            $oCon->Free();

            if ($resultado != null) {
                $motivo = $resultado;
            } else {
                $sql = "insert into isp.int_motivos_canc(id_proceso,motivo) 
                        VALUES ($tipo_servicio,'$nom_motivo')";
                $oCon->QueryT($sql);
                $sql = "SELECT id from isp.int_motivos_canc WHERE id_proceso = $tipo_servicio AND motivo = '$nom_motivo' LIMIT 1";
                $oCon->Query($sql);
                $motivo = $oCon->f('id');
                $oCon->Free();
            }

            if ($motivo != null) {

                #SABER SI EL CONTRATO CUENTA CON TELEVISION ANALOGA PARA DEFINIR EL TIPO DE ESTADO
                $sql = "SELECT id_tipo_prod 
                        from isp.int_contrato_caja
                        WHERE id_contrato = $id_contrato AND id_clpv = $id_clpv AND id = $id_caja";
                $oCon->Query($sql);
                $id_tipo_prod_c = $oCon->f('id_tipo_prod');
                $oCon->Free();

                $estado = "TE";


                //DIRECCION DEL CONTRATO
                $sql = "SELECT id_dire, id_sucursal 
                                    from isp.contrato_clpv 
                                    WHERE id = $id_contrato AND 
                                    id_clpv = $id_clpv";

                $oCon->Query($sql);
                $id_dire = $oCon->f('id_dire');
                $id_sucu = $oCon->f('id_sucursal');
                $oCon->Free();

                if ($id_dire == null) {
                    $id_dire = 1;
                }

                #OBTENER EL SECUENCUAL
                $sql = "SELECT CAST (max(secuencial) AS INTEGER)  + 1 as secuencial 
                            from isp.instalacion_clpv
                            WHERE id_proceso = $tipo_servicio";

                $oCon->Query($sql);
                $secuencial = $oCon->f('secuencial');
                $oCon->Free();

                if ($secuencial == null) {
                    $secuencial = 1;
                };

                $secuencial_fin = str_pad($secuencial, 9, 0, STR_PAD_LEFT);
                $fecha_actual = date('Y-m-d');

                $fechaServer = date("Y-m-d H:i:s");

                #INGRESAR LA ORDEN DE SERVICIO
                $sql = "insert into isp.instalacion_clpv(id_empresa, id_sucursal, id_sucursal_ori, id_proceso, 
                                                secuencial, id_clpv, id_contrato, fecha, 
                                                user_web, fecha_server, observaciones, id_direccion, 
                                                id_franja, id_motivo, id_prioridad, solicita, adjunto, 
                                                observacion_cliente,estado_contrato, reconexion_pago, 
                                                fecha_inicio, fecha_fin, estado)
                            VALUES($id_empresa, $id_sucursal, $id_sucu, $tipo_servicio, '$secuencial_fin', 
                            $id_clpv, $id_contrato, '$fecha_actual',$id_usuario, '$fechaServer', '$detalle', $id_dire, 
                            $id_tipo_prod_c, $motivo, 1, '', '', '','AP', 'N', '$fechaServer', '$fechaServer', '$estado')";
                $oCon->QueryT($sql);

                #OBTENER EL ULTIMO ID DE LA TABLA instalacion clpv PARA INSERTARLO EN isp.instalacion_prod
                $sql = "SELECT max(id) AS id 
                            from isp.instalacion_clpv
                            WHERE 
                            id_proceso = $tipo_servicio AND 
                            id_clpv = $id_clpv AND id_contrato = $id_contrato";

                $oCon->Query($sql);
                $id_instalacion = $oCon->f('id');
                $oCon->Free();

                if ($estado == 'TE') {
                    #INSERTAR ORDEN DE SERVICIO EN LA TABLA isp.instalacion_ejecucion
                    $sql = "insert into isp.instalacion_ejecucion(id_empresa, id_sucursal, id_clpv, id_contrato, 
                                                    id_instalacion, fecha, estado, user_web, fecha_server, 
                                                    observacion_tecnico)
                                VALUES($id_empresa, $id_sucursal, $id_clpv, $id_contrato, '$id_instalacion', 
                                '$fecha_actual', 'A', $id_usuario, NOW(), 'Corte de servicios automatica')";
                    $oCon->QueryT($sql);
                }

                $sql = "insert into isp.instalacion_prod(id_empresa, id_sucursal, id_clpv, id_contrato, 
                                                    id_instalacion, id_caja, estado, cambio_sn, 
                                                    fecha_ejecucion)
                            VALUES($id_empresa, $id_sucursal, $id_clpv, $id_contrato, '$id_instalacion', 
                            $id_caja, '$estado', 'N', '$fecha_actual')";
                $oCon->QueryT($sql);

                $respuesta = true;
            }
        }

        return $respuesta;
    }






    function reporteDetalleContratos($oCon, $oIfx, $oIfxA, $empresa, $id, $id_clpv, $fecha_inicio, $fecha_fin, $impuestoSN)
    {
        $sHtml = "";
        $anio = date("Y");
        $fechaIni = $fecha_inicio;
        $fechaFin = $fecha_fin;

        //lectura sucia
        // $oIfx->QueryT('set isolation to dirty read;');

        $sql = "select sucu_cod_sucu, sucu_sigl_sucu from saesucu where sucu_cod_empr = $empresa";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                unset($arraySucursal);
                do {
                    $arraySucursal[$oIfx->f('sucu_cod_sucu')] = $oIfx->f('sucu_sigl_sucu');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        $sHtml .= '<table class="table table-condensed table-striped" border="1" style="width:100%; margin-top: 10px; border-collapse: collapse;" align="center">';
        $sHtml .= '<tr>
                <td colspan="9" class="bg-success">FACTURACION</td>
            </tr>
            <tr>
                <td style="width: 7%;" align="center">Sucursal</td>
                <td style="width: 8%;" align="center">Fecha</td>
                <td style="width: 8%;" align="center">Serie</td>
                <td style="width: 8%;" align="center">Factura</td>
                <td style="width: 37%;" align="center">Detalle</td>
                <td style="width: 8%;" align="center">Venta</td>
                <td style="width: 8%;" align="center">Nota Credito</td>
                <td style="width: 8%;" align="center">Cobros</td>
                <td style="width: 8%;" align="center">Saldo</td>
            </tr>';

        $sqlFiltroClpv = '';
        $sqlFiltroClpv .= " fact_cod_clpv in ($id_clpv ";

        //consulta contratos relacionado
        $sql = "select id_clpv
            from contrato_rela
            where id_contrato = $id and
            estado = 'A'";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $id_clpv_rela = $oCon->f('id_clpv');

                    $sqlFiltroClpv .= ',' . $id_clpv_rela;
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sqlFiltroClpv .= ')';

        //array facturas
        $sql = "SELECT fact_cod_fact, fact_cod_sucu, fact_cod_clpv, fact_nse_fact, fact_num_preimp, dfac_det_dfac, fact_fech_fact,
                    COALESCE((sum(( COALESCE(fact_tot_fact,0) - fact_dsg_valo + fact_fle_fact + fact_otr_fact + fact_fin_fact))),0) as venta,
                    sum(COALESCE(fact_iva,0)) as iva
                    from saefact_fede, saedfac_fede
                    where 
                    fact_cod_empr = dfac_cod_empr and
                    fact_cod_sucu = dfac_cod_sucu and
                    fact_cod_clpv = dfac_cod_clpv and
                    fact_cod_fact = dfac_cod_fact and
                    fact_cod_empr = $empresa and
                    fact_est_fact != 'AN' and
                    dfac_cod_grpr in (18, 19, 20, 21, 25, 28) and
                    fact_fech_fact between '$fecha_inicio' and '$fecha_fin' and
                    $sqlFiltroClpv
                    group by 1,2,3,4,5,6,7


                UNION ALL
                
                
                select fact_cod_fact, fact_cod_sucu, fact_cod_clpv, fact_nse_fact, fact_num_preimp, dfac_det_dfac, fact_fech_fact,
                    COALESCE((sum(( COALESCE(fact_tot_fact,0) - fact_dsg_valo + fact_fle_fact + fact_otr_fact + fact_fin_fact))),0) as venta,
                    sum(COALESCE(fact_iva,0)) as iva
                    from saefact, saedfac
                    where 
                    fact_cod_empr = dfac_cod_empr and
                    fact_cod_sucu = dfac_cod_sucu and
                    fact_cod_clpv = dfac_cod_clpv and
                    fact_cod_fact = dfac_cod_fact and
                    fact_cod_empr = $empresa and
                    fact_est_fact != 'AN' and
                    dfac_cod_grpr in (18, 19, 20, 21, 25, 28) and
                    fact_fech_fact between '$fecha_inicio' and '$fecha_fin' and
                    $sqlFiltroClpv
                    group by 1,2,3,4,5,6,7
                    order by 7";
        //echo $sql;exit;
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $totalFactura = 0;
                $totalNotaCredito = 0;
                $totalCobros = 0;
                $totalSaldo = 0;
                unset($arrayFacturas);
                unset($arrayIvaFactura);
                do {
                    $fact_cod_sucu = $oIfx->f('fact_cod_sucu');
                    $fact_nse_fact = $oIfx->f('fact_nse_fact');
                    $fact_cod_fact = $oIfx->f('fact_cod_fact');
                    $fact_cod_clpv = $oIfx->f('fact_cod_clpv');
                    $fact_num_preimp = $oIfx->f('fact_num_preimp');
                    $dfac_det_dfac = $oIfx->f('dfac_det_dfac');
                    $fact_fech_fact  = $oIfx->f('fact_fech_fact');
                    $venta = $oIfx->f('venta');
                    $iva = $oIfx->f('iva');

                    $arrayIvaFactura[$fact_nse_fact][$fact_num_preimp] = $iva;

                    $arrayFacturas[] = array($fact_cod_fact, $fact_cod_clpv, $fact_num_preimp, $fact_nse_fact);

                    $tmpNcre = '';
                    if ($impuestoSN == 'S') {
                        $venta += $iva;
                        $tmpNcre = " + COALESCE(ncre_iva,0)";
                    }

                    //totas de credito
                    $sqlNcre = "select 
                            round((COALESCE((sum( COALESCE(ncre_tot_fact,0) $tmpNcre - COALESCE(ncre_dsg_valo,0) + COALESCE(ncre_fle_fact,0) + COALESCE(ncre_otr_fact ,0) + COALESCE(ncre_fin_fact,0))),0)),2) as venta 
                            from saencre
                            where ncre_cod_empr = $empresa and
                            ncre_cod_fact = $fact_cod_fact and
                            ncre_cod_clpv = $fact_cod_clpv and
                            ncre_est_fact != 'AN'";
                    $valorNotaCredito = consulta_string_func($sqlNcre, 'venta', $oIfxA, 0);

                    $sqlNcre2 = "select 
                            round((COALESCE((sum( COALESCE(ncre_tot_fact,0) $tmpNcre - COALESCE(ncre_dsg_valo,0) + COALESCE(ncre_fle_fact,0) + COALESCE(ncre_otr_fact ,0) + COALESCE(ncre_fin_fact,0))),0)),2) as venta 
                            from saencre_fede
                            where ncre_cod_empr = $empresa and
                            ncre_cod_fact = $fact_cod_fact and
                            ncre_cod_clpv = $fact_cod_clpv and
                            ncre_est_fact != 'AN'";
                    $valorNotaCredito2 = consulta_string_func($sqlNcre2, 'venta', $oIfxA, 0);

                    $valorNotaCredito = $valorNotaCredito + $valorNotaCredito2;



                    //cancelacion
                    $sqlDmcc = "select (sum(saedmcc.dmcc_cre_ml) ) as saldo  
                            from saedmcc
                            where dmcc_cod_empr = $empresa and
                            clpv_cod_clpv = $fact_cod_clpv and
                            -- dmcc_cod_tran in ('CAN', 'CAN2', 'CAN3', 'CAN4') and
                            dmcc_cod_tran not in ('NDC', 'NDC2', 'NDC3', 'NDC4', 'RIV') and
                            dmcc_num_fac like ('%$fact_num_preimp%')";
                    $valorCobros = consulta_string_func($sqlDmcc, 'saldo', $oIfxA, 0);

                    $sqlDmcc2 = "select (sum(saedmcc_fede.dmcc_cre_ml) ) as saldo  
                            from saedmcc_fede
                            where dmcc_cod_empr = $empresa and
                            clpv_cod_clpv = $fact_cod_clpv and
                            -- dmcc_cod_tran in ('CAN', 'CAN2', 'CAN3', 'CAN4') and
                            dmcc_cod_tran not in ('NDC', 'NDC2', 'NDC3', 'NDC4', 'RIV') and
                            dmcc_num_fac like ('%$fact_num_preimp%')";
                    $valorCobros2 = consulta_string_func($sqlDmcc2, 'saldo', $oIfxA, 0);

                    $valorCobros =  $valorCobros + $valorCobros2;

                    //cobros
                    $sqlDmcc = "select (sum(saedmcc.dmcc_cre_ml) ) as saldo  
                            from saedmcc
                            where dmcc_cod_empr = $empresa and
                            clpv_cod_clpv = $fact_cod_clpv and
                            dmcc_cod_tran in ('RIV', 'RIV2', 'RIV3', 'RIV4') and
                            dmcc_num_fac like ('%$fact_num_preimp%')";
                    $valorRetencion = consulta_string_func($sqlDmcc, 'saldo', $oIfxA, 0);

                    //cobros
                    $sqlDmcc2 = "select (sum(saedmcc_fede.dmcc_cre_ml) ) as saldo  
                            from saedmcc_fede
                            where dmcc_cod_empr = $empresa and
                            clpv_cod_clpv = $fact_cod_clpv and
                            dmcc_cod_tran in ('RIV', 'RIV2', 'RIV3', 'RIV4') and
                            dmcc_num_fac like ('%$fact_num_preimp%')";
                    $valorRetencion2 = consulta_string_func($sqlDmcc2, 'saldo', $oIfxA, 0);

                    $valorRetencion =  $valorRetencion + $valorRetencion2;

                    if ($valorCobros > 0) {
                        $valorCobros += $valorRetencion;
                    }

                    if (empty($impuestoSN)) {
                        if ($valorCobros > 0) {
                            $valorCobros = round($valorCobros / 1.12, 2);
                        }
                    }

                    $saldoFact = abs(round($venta - $valorNotaCredito - $valorCobros, 2));

                    $sHtml .= '<tr>';
                    $sHtml .= '<td style="width: 7%;">' . $arraySucursal[$fact_cod_sucu] . '</td>';
                    $sHtml .= '<td style="width: 8%;">' . $fact_fech_fact . '</td>';
                    $sHtml .= '<td style="width: 8%;">' . $fact_nse_fact . '</td>';
                    $sHtml .= '<td style="width: 8%;"><a href="#" onclick="genera_documento(1, ' . $fact_cod_fact . ', ' . $fact_cod_sucu . ')">' . $fact_num_preimp . '</a></td>';
                    $sHtml .= '<td style="width: 37%;">' . $dfac_det_dfac . '</td>';
                    $sHtml .= '<td style="width: 8%;" align="right">' . number_format($venta, 2, '.', ',') . '</td>';
                    $sHtml .= '<td style="width: 8%;" align="right">' . number_format($valorNotaCredito, 2, '.', ',') . '</td>';
                    $sHtml .= '<td style="width: 8%;" align="right">' . number_format($valorCobros, 2, '.', ',') . '</td>';
                    $sHtml .= '<td style="width: 8%;" align="right">' . number_format($saldoFact, 2, '.', ',') . '</td>';
                    $sHtml .= '</tr>';

                    $totalFactura += $venta;
                    $totalNotaCredito += $valorNotaCredito;
                    $totalCobros += $valorCobros;
                    $totalSaldo += $saldoFact;
                } while ($oIfx->SiguienteRegistro());

                $sHtml .= '<tr>';
                $sHtml .= '<td colspan="5" class="bg-danger fecha_grande" align="right">TOTAL:</td>';
                $sHtml .= '<td align="right" class="bg-danger fecha_grande">' . number_format($totalFactura, 2, '.', ',') . '</td>';
                $sHtml .= '<td align="right" class="bg-danger fecha_grande">' . number_format($totalNotaCredito, 2, '.', ',') . '</td>';
                $sHtml .= '<td align="right" class="bg-danger fecha_grande">' . number_format($totalCobros, 2, '.', ',') . '</td>';
                $sHtml .= '<td align="right" class="bg-danger fecha_grande">' . number_format($totalSaldo, 2, '.', ',') . '</td>';
                $sHtml .= '</tr>';
            }
        }
        $oIfx->Free();

        $sHtml .= '</table>';

        if (count($arrayFacturas) > 0) {
            $sHtml .= '<table class="table table-condensed table-striped" border="1" style="width:100%; margin-top: 10px; border-collapse: collapse;" align="center">';
            $sHtml .= '<tr>
                    <td colspan="8" class="bg-success">NOTAS DE CREDITO</td>
                </tr>
                <tr>
                    <td style="width: 7%;" align="center">Sucursal</td>
                    <td style="width: 8%;" align="center">Fecha</td>
                    <td style="width: 8%;" align="center">Serie</td>
                    <td style="width: 8%;" align="center">Nota Credito</td>
                    <td style="width: 47%;" align="center">Observaciones</td>
                    <td style="width: 14%;" align="center">Factura Aplica</td>
                    <td style="width: 8%;" align="center">Valor</td>
                </tr>';

            $totalNC = 0;
            foreach ($arrayFacturas as $val) {
                $fact_cod_fact = $val[0];
                $fact_cod_clpv = $val[1];
                $fact_num_preimp = $val[2];
                $fact_nse_fact = $val[3];

                $tmpNcre = '';
                if ($impuestoSN == 'S') {
                    $venta += $iva;
                    $tmpNcre = " + COALESCE(ncre_iva,0)";
                }

                //totas de credito
                $sqlNcre = "SELECT ncre_cod_ncre, ncre_cod_sucu, ncre_nse_ncre, ncre_num_preimp, ncre_fech_fact,
                                    ncre_cm1_ncre,
                                    round((COALESCE((sum( COALESCE(ncre_tot_fact,0) $tmpNcre - COALESCE(ncre_dsg_valo,0) + COALESCE(ncre_fle_fact,0) + COALESCE(ncre_otr_fact ,0) + COALESCE(ncre_fin_fact,0))),0)),2) as venta 
                                    from saencre_fede
                                    where ncre_cod_empr = $empresa and
                                    ncre_cod_fact = $fact_cod_fact and
                                    ncre_cod_clpv = $fact_cod_clpv and
                                    ncre_est_fact != 'AN'
                                    group by 1,2,3,4,5,6

                            UNION ALL
                            
                            select ncre_cod_ncre, ncre_cod_sucu, ncre_nse_ncre, ncre_num_preimp, ncre_fech_fact,
                                    ncre_cm1_ncre,
                                    round((COALESCE((sum( COALESCE(ncre_tot_fact,0) $tmpNcre - COALESCE(ncre_dsg_valo,0) + COALESCE(ncre_fle_fact,0) + COALESCE(ncre_otr_fact ,0) + COALESCE(ncre_fin_fact,0))),0)),2) as venta 
                                    from saencre
                                    where ncre_cod_empr = $empresa and
                                    ncre_cod_fact = $fact_cod_fact and
                                    ncre_cod_clpv = $fact_cod_clpv and
                                    ncre_est_fact != 'AN'
                                    group by 1,2,3,4,5,6";

                if ($oIfx->Query($sqlNcre)) {
                    if ($oIfx->NumFilas() > 0) {
                        do {
                            $ncre_cod_ncre = $oIfx->f('ncre_cod_ncre');
                            $ncre_cod_sucu = $oIfx->f('ncre_cod_sucu');
                            $ncre_nse_ncre = $oIfx->f('ncre_nse_ncre');
                            $ncre_num_preimp = $oIfx->f('ncre_num_preimp');
                            $ncre_fech_fact = $oIfx->f('ncre_fech_fact');
                            $ncre_cm1_ncre = $oIfx->f('ncre_cm1_ncre');
                            $valorNC = $oIfx->f('venta');

                            $sHtml .= '<tr>';
                            $sHtml .= '<td style="width: 7%;">' . $arraySucursal[$ncre_cod_sucu] . '</td>';
                            $sHtml .= '<td style="width: 8%;">' . fecha_mysql_func($ncre_fech_fact) . '</td>';
                            $sHtml .= '<td style="width: 8%;">' . $ncre_nse_ncre . '</td>';
                            $sHtml .= '<td style="width: 8%;"><a href="#" onclick="genera_documento(2, ' . $ncre_cod_ncre . ', ' . $ncre_cod_sucu . ')">' . $ncre_num_preimp . '</a></td>';
                            $sHtml .= '<td style="width: 47%;">' . $ncre_cm1_ncre . '</td>';
                            $sHtml .= '<td style="width: 14%;">' . $fact_nse_fact . ' ' . $fact_num_preimp . '</td>';
                            $sHtml .= '<td style="width: 8%;" align="right">' . number_format($valorNC, 2, '.', ',') . '</td>';
                            $sHtml .= '</tr>';

                            $totalNC += $valorNC;
                        } while ($oIfx->SiguienteRegistro());
                    }
                }
                $oIfx->Free();
            }
            $sHtml .= '<tr>';
            $sHtml .= '<td colspan="6" class="bg-danger fecha_grande" align="right">TOTAL:</td>';
            $sHtml .= '<td align="right" class="bg-danger fecha_grande">' . number_format($totalNC, 2, '.', ',') . '</td>';
            $sHtml .= '</tr>';
            $sHtml .= '</table>';
        }


        //cobros
        if (count($arrayFacturas) > 0) {
            $sHtml .= '<table class="table table-condensed table-striped" border="1" style="width:100%; margin-top: 10px; border-collapse: collapse;" align="center">';
            $sHtml .= '<tr>
                    <td colspan="8" class="bg-success">COBROS</td>
                </tr>';
            $sHtml .= '<tr>';
            $sHtml .= '<td style="width: 7%;" align="center">Sucursal</td>';
            $sHtml .= '<td style="width: 8%;" align="center">Fecha</td>';
            $sHtml .= '<td style="width: 8%;" align="center">Transaccion</td>';
            $sHtml .= '<td style="width: 10%;" align="center">Diario</td>';
            $sHtml .= '<td style="width: 10%;" align="center">Factura</td>';
            $sHtml .= '<td style="width: 41%;" align="center">Detalle</td>';
            $sHtml .= '<td style="width: 8%;" align="center">Retenciones</td>';
            $sHtml .= '<td style="width: 8%;" align="center">Valor</td>';
            $sHtml .= '</tr>';

            $totalCB = 0;
            $totalRT = 0;
            foreach ($arrayFacturas as $val) {
                $fact_cod_fact = $val[0];
                $fact_cod_clpv = $val[1];
                $fact_num_preimp = $val[2];
                $fact_nse_fact = $val[3];

                $ivaFactura = $arrayIvaFactura[$fact_nse_fact][$fact_num_preimp];

                $sqlDmcc = "SELECT dmcc_cod_sucu, dmcc_cod_asto, dmcc_cod_tran, dmcc_det_dmcc, dmcc_num_fac, 
                                    dmcc_fec_emis, dmcc_cod_ejer, date_part('month',dmcc_fec_emis) as mescobro, dmcc_cod_fact,
                                    (sum(saedmcc_fede.dmcc_cre_ml) ) as saldo  
                                    from saedmcc_fede
                                    where dmcc_cod_empr = $empresa and
                                    clpv_cod_clpv = $fact_cod_clpv and
                                    dmcc_cod_tran not in ('NDC', 'NDC2', 'NDC3', 'NDC4', 'RIV') and
                                    dmcc_num_fac like ('%$fact_num_preimp%') and
                                    dmcc_cre_ml > 0
                                    group by 1,2,3,4,5,6,7,8,9
                                
                            UNION ALL
                
                            SELECT dmcc_cod_sucu, dmcc_cod_asto, dmcc_cod_tran, dmcc_det_dmcc, dmcc_num_fac, 
                                                dmcc_fec_emis, dmcc_cod_ejer, date_part('month',dmcc_fec_emis) as mescobro, dmcc_cod_fact,
                                                (sum(saedmcc.dmcc_cre_ml) ) as saldo  
                                                from saedmcc
                                                where dmcc_cod_empr = $empresa and
                                                clpv_cod_clpv = $fact_cod_clpv and
                                                dmcc_cod_tran not in ('NDC', 'NDC2', 'NDC3', 'NDC4', 'RIV') and
                                                dmcc_num_fac like ('%$fact_num_preimp%') and
                                                dmcc_cre_ml > 0
                                                group by 1,2,3,4,5,6,7,8,9";
                //$oReturn->alert($sqlDmcc);
                if ($oIfx->Query($sqlDmcc)) {
                    if ($oIfx->NumFilas() > 0) {
                        do {
                            $dmcc_cod_sucu = $oIfx->f('dmcc_cod_sucu');
                            $dmcc_cod_asto = $oIfx->f('dmcc_cod_asto');
                            $dmcc_cod_tran = $oIfx->f('dmcc_cod_tran');
                            $dmcc_det_dmcc = $oIfx->f('dmcc_det_dmcc');
                            $dmcc_num_fac = $oIfx->f('dmcc_num_fac');
                            $dmcc_cod_ejer = $oIfx->f('dmcc_cod_ejer');
                            $dmcc_fec_emis = $oIfx->f('dmcc_fec_emis');
                            $mescobro = $oIfx->f('mescobro');
                            $saldo = $oIfx->f('saldo');
                            $dmcc_cod_fact = $oIfx->f('dmcc_cod_fact');

                            //cobros
                            $sqlDmcc = "SELECT (sum(saedmcc_fede.dmcc_cre_ml) ) as saldo  
                                                from saedmcc_fede
                                                where dmcc_cod_empr = $empresa and
                                                clpv_cod_clpv = $fact_cod_clpv and
                                                dmcc_cod_tran in ('RIV', 'RIV2', 'RIV3', 'RIV4') and
                                                dmcc_num_fac like ('%$fact_num_preimp%')
                                        
                                        union all 

                                        select (sum(saedmcc.dmcc_cre_ml) ) as saldo  
                                                from saedmcc
                                                where dmcc_cod_empr = $empresa and
                                                clpv_cod_clpv = $fact_cod_clpv and
                                                dmcc_cod_tran in ('RIV', 'RIV2', 'RIV3', 'RIV4') and
                                                dmcc_num_fac like ('%$fact_num_preimp%')";


                            $valorRetencion = consulta_string_func($sqlDmcc, 'saldo', $oIfxA, 0);

                            if ($saldo > 0) {
                                $saldo += $valorRetencion;
                            }

                            if ($impuestoSN != 'S') {
                                if ($ivaFactura > 0) {
                                    $saldo = round($saldo / 1.12, 2);
                                }
                            }

                            $sHtml .= '<tr>';
                            $sHtml .= '<td style="width: 7%;">' . $arraySucursal[$dmcc_cod_sucu] . '</td>';
                            $sHtml .= '<td style="width: 8%;">' . fecha_mysql_func($dmcc_fec_emis) . '</td>';
                            $sHtml .= '<td style="width: 8%;">' . $dmcc_cod_tran . '</td>';
                            $sHtml .= '<td style="width: 10%;"><a href="#" onclick="seleccionaItem(' . $empresa . ', ' . $dmcc_cod_sucu . ', ' . $dmcc_cod_ejer . ', ' . $mescobro . ' ,\'' . $dmcc_cod_asto . '\')">' . $dmcc_cod_asto . '</a></td>';
                            $sHtml .= '<td style="width: 10%;">' . $dmcc_num_fac . '</td>';
                            $sHtml .= '<td style="width: 41%;">' . $dmcc_det_dmcc . '</td>';
                            $sHtml .= '<td style="width: 8%;" align="right">' . number_format($valorRetencion, 2, '.', ',') . '</td>';
                            $sHtml .= '<td style="width: 8%;" align="right">' . number_format($saldo, 2, '.', ',') . '</td>';
                            $sHtml .= '</tr>';

                            $totalRT += $valorRetencion;
                            $totalCB += $saldo;
                        } while ($oIfx->SiguienteRegistro());
                    }
                }
                $oIfx->Free();
            }
            $sHtml .= '<tr>';
            $sHtml .= '<td colspan="6" class="bg-danger fecha_grande" align="right">TOTAL:</td>';
            $sHtml .= '<td align="right" class="bg-danger fecha_grande">' . number_format($totalRT, 2, '.', ',') . '</td>';
            $sHtml .= '<td align="right" class="bg-danger fecha_grande">' . number_format($totalCB, 2, '.', ',') . '</td>';
            $sHtml .= '</tr>';
            $sHtml .= '</table>';
        }

        //gastos
        $sHtml .= '<table class="table table-condensed table-striped" border="1" style="width:100%; margin-top: 10px; border-collapse: collapse;" align="center">';
        $sHtml .= '<tr>
                    <td colspan="8" class="bg-success">GASTOS</td>
                </tr>';
        $sHtml .= '<tr>';
        $sHtml .= '<td style="width: 7%;" align="center">Sucursal</td>';
        $sHtml .= '<td style="width: 8%;" align="center">Fecha</td>';
        $sHtml .= '<td style="width: 8%;" align="center">Factura</td>';
        $sHtml .= '<td style="width: 10%;" align="center">Diario</td>';
        $sHtml .= '<td style="width: 18%;" align="center">Proveedor</td>';
        $sHtml .= '<td style="width: 33%;" align="center">Detalle</td>';
        $sHtml .= '<td style="width: 8%;" align="center">Valor</td>';
        $sHtml .= '<td style="width: 8%;" align="center">Pago</td>';
        $sHtml .= '</tr>';

        $sqlGastos = "SELECT id, id_sucursal, factura, fecha, valor, id_clpv, id_prove, asto, ejer, prdo
                    from proy_fact_benf
                        inner join saeasto on
                            asto_cod_asto = asto
                            and id_sucursal = asto_cod_sucu
                            and asto_cod_ejer = ejer
                            and asto_num_prdo = prdo

                    where id_empresa = $empresa and
                    id_contrato = $id and
                    asto_est_asto != 'AN' and
                    (select c.id_clpv
                    from contrato_clpv c
                    where c.id = id_contrato and
                    c.id_empresa = id_empresa) = $id_clpv and
                    fecha between '$fechaIni' and '$fechaFin'";

        //echo $sqlGastos;exit;

        if ($oCon->Query($sqlGastos)) {
            if ($oCon->NumFilas() > 0) {
                $totalValor = 0;
                $totalValorPago = 0;
                do {
                    $detalleFecha = $oCon->f('fecha');
                    $detalleFactura = $oCon->f('factura');
                    $detalleValor = $oCon->f('valor');
                    $detalleProve = $oCon->f('id_prove');
                    $detalleAsto = $oCon->f('asto');
                    $detalleEjer = $oCon->f('ejer');
                    $detallePrdo = $oCon->f('prdo');
                    $id_sucursal = $oCon->f('id_sucursal');

                    $sql = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $detalleProve";
                    $clpv_nom_clpv = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');

                    //detalle 
                    $sqlDet = "select fprv_det_fprv 
                            from saefprv
                            where fprv_num_fact = '$detalleFactura' and
                            fprv_cod_clpv = $detalleProve and
                            fprv_cod_asto = '$detalleAsto' and
                            fprv_cod_empr = $empresa";
                    $detalleFprv = consulta_string_func($sqlDet, 'fprv_det_fprv', $oIfxA, 0);

                    //cobros
                    $sqlDmcp = "select count(*) as control
                            from saedmcp
                            where dmcp_cod_empr = $empresa and
                            clpv_cod_clpv = $detalleProve and
                            dmcp_cod_tran = 'CAN' and
                            dmcp_num_fac like ('%$detalleFactura%')";
                    $controlPago = consulta_string_func($sqlDmcp, 'control', $oIfxA, 0);

                    $sqlDmcp2 = "select count(*) as control
                            from saedmcp_fede
                            where dmcp_cod_empr = $empresa and
                            clpv_cod_clpv = $detalleProve and
                            dmcp_cod_tran like 'CAN%' and
                            dmcp_num_fac like ('%$detalleFactura%')";
                    $controlPago2 = consulta_string_func($sqlDmcp2, 'control', $oIfxA, 0);

                    $controlPago = $controlPago + $controlPago2;

                    $valorPago = 0;
                    if ($controlPago > 0) {
                        $valorPago = $detalleValor;
                    }

                    $sHtml .= '<tr>';
                    $sHtml .= '<td style="width: 7%;">' . $arraySucursal[$id_sucursal] . '</td>';
                    $sHtml .= '<td style="width: 8%;">' . fecha_mysql_dmy($detalleFecha) . '</td>';
                    $sHtml .= '<td style="width: 8%;">' . $detalleFactura . '</td>';
                    $sHtml .= '<td style="width: 10%;"><a href="#" onclick="seleccionaItem(' . $empresa . ', ' . $id_sucursal . ', ' . $detalleEjer . ', ' . $detallePrdo . ' ,\'' . $detalleAsto . '\')">' . $detalleAsto . '</a></td>';
                    $sHtml .= '<td style="width: 18%;">' . $clpv_nom_clpv . '</td>';
                    $sHtml .= '<td style="width: 33%;">' . $detalleFprv . '</td>';
                    $sHtml .= '<td style="width: 8%;" align="right">' . number_format($detalleValor, 2, '.', ',') . '</td>';
                    $sHtml .= '<td style="width: 8%;" align="right">' . number_format($valorPago, 2, '.', ',') . '</td>';
                    $sHtml .= '</tr>';

                    $totalValor += $detalleValor;
                    $totalValorPago += $valorPago;
                } while ($oCon->SiguienteRegistro());
                $sHtml .= '<tr>';
                $sHtml .= '<td class="bg-danger fecha_grande" align="right" colspan="6">TOTALES:</td>';
                $sHtml .= '<td class="bg-danger fecha_grande" align="right">' . number_format($totalValor, 2, '.', ',') . '</td>';
                $sHtml .= '<td class="bg-danger fecha_grande" align="right">' . number_format($totalValorPago, 2, '.', ',') . '</td>';
                $sHtml .= '</tr>';
            }
        }
        $oCon->Free();
        $sHtml .= '</table>';

        //capacitaciones
        $sHtml .= '<table class="table table-condensed table-striped" border="1" style="width:100%; margin-top: 10px; border-collapse: collapse;" align="center">';
        $sHtml .= '<tr>
                    <td colspan="8" class="bg-success">CAPACITACIONES</td>
                </tr>';
        $sHtml .= '<tr>';
        $sHtml .= '<td style="width: 10%;" align="center">No.Horas</td>';
        $sHtml .= '<td style="width: 50%;" align="center">Capacitacion</td>';
        $sHtml .= '<td style="width: 10%;" align="center">No. Personas</td>';
        $sHtml .= '<td style="width: 10%;" align="center">Confinanciado</td>';
        $sHtml .= '<td style="width: 10%;" align="center">Contrapartida</td>';
        $sHtml .= '<td style="width: 10%;" align="center">Valor</td>';
        $sHtml .= '</tr>';

        $sql = "SELECT id, fecha, ruc, horas, detalle, personas, valor, cofinanciado, contrapartida
            FROM proy_capacitacion
            WHERE id_empresa = $empresa AND
            id_clpv = $id_clpv and
            id_contrato = $id and
            estado = 'A'";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $totalHoras = 0;
                $totalPersonas = 0;
                $totalValor = 0;
                $totalCofinanciado = 0;
                $totalContrapartida = 0;
                do {
                    $id = $oCon->f('id');
                    $fecha = $oCon->f('fecha');
                    $horas = $oCon->f('horas');
                    $detalle = $oCon->f('detalle');
                    $personas = $oCon->f('personas');
                    $valor = $oCon->f('valor');
                    $cofinanciado = $oCon->f('cofinanciado');
                    $contrapartida = $oCon->f('contrapartida');

                    $sHtml .= '<tr>';
                    $sHtml .= '<td style="width: 10%;" align="right">' . $horas . '</td>';
                    $sHtml .= '<td style="width: 50%;">' . $detalle . '</td>';
                    $sHtml .= '<td style="width: 10%;" align="right">' . $personas . '</td>';
                    $sHtml .= '<td style="width: 10%;" align="right">' . number_format($cofinanciado, 2, '.', ',') . '</td>';
                    $sHtml .= '<td style="width: 10%;" align="right">' . number_format($contrapartida, 2, '.', ',') . '</td>';
                    $sHtml .= '<td style="width: 10%;" align="right">' . number_format($valor, 2, '.', ',') . '</td>';
                    $sHtml .= '</tr>';

                    $totalHoras += $horas;
                    $totalPersonas += $personas;
                    $totalValor += $valor;
                    $totalCofinanciado += $cofinanciado;
                    $totalContrapartida += $contrapartida;
                } while ($oCon->SiguienteRegistro());
                $sHtml .= '<tr>';
                $sHtml .= '<td class="bg-danger fecha_grande" align="right">' . $totalHoras . '</td>';
                $sHtml .= '<td class="bg-danger fecha_grande"></td>';
                $sHtml .= '<td class="bg-danger fecha_grande" align="right">' . $totalPersonas . '</td>';
                $sHtml .= '<td class="bg-danger fecha_grande" align="right">' . number_format($totalCofinanciado, 2, '.', ',') . '</td>';
                $sHtml .= '<td class="bg-danger fecha_grande" align="right">' . number_format($totalContrapartida, 2, '.', ',') . '</td>';
                $sHtml .= '<td class="bg-danger fecha_grande" align="right">' . number_format($totalValor, 2, '.', ',') . '</td>';
                $sHtml .= '</tr>';
            }
        }
        $oCon->Free();
        $sHtml .= '</table>';

        return $sHtml;
    }

    function consultaSaldoDmcc()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $oCon = $this->oCon;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;

        $id_sucursal_sesion = $_SESSION['U_SUCURSAL'];
        $id_empresa = $_SESSION['U_EMPRESA'];

        $fecha = date('Y-m-d');
        $ultimoDiaMes = date('t', strtotime($fecha));
        $dia = date("d");
        $mes = date("m");
        $anio = date("Y");
        $fechaComparaPre = $anio . '-' . $mes . '-' . $ultimoDiaMes;

        $saldo_dmcc_tot = 0;
        //DATOS DEL CONTRATO
        $sql = "SELECT saldo
                FROM (
                    SELECT
                        (SUM(a.dmcc_deb_ml) - SUM(a.dmcc_cre_ml)) AS saldo,
                        MAX(a.dmcc_cod_fact) AS id_factura,
                        MAX(a.clpv_cod_clpv) AS id_clpv,
                        a.dmcc_num_fac,
                        a.clpv_cod_clpv,
                    MIN(a.dmcc_fec_emis) as fecha_emision
                    FROM
                        saedmcc a
                    WHERE
                        a.dmcc_cod_empr = $id_empresa AND
                        a.dmcc_fec_emis <= '$fechaComparaPre'
                    GROUP BY
                        a.dmcc_num_fac,
                        a.clpv_cod_clpv
                ) AS subconsulta 
                INNER JOIN saefact b ON subconsulta.id_factura = b.fact_cod_fact 
                INNER JOIN saedfac c ON b.fact_cod_fact = c.dfac_cod_fact
                WHERE
                    saldo > 0 AND id_factura > 0 AND fact_cod_contr > 0 AND fact_cod_contr = $idContrato
                GROUP BY
                    id_clpv, fact_cod_contr, saldo
                ORDER BY
                    fact_cod_contr";
         if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $saldo_dmcc = $oCon->f('saldo');

                    $saldo_dmcc_tot += $saldo_dmcc;
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sql = "SELECT para_aprox_num     
                from saepara
                WHERE para_cod_sucu = $id_sucursal_sesion";
        $para_aprox_num = consulta_string_func($sql, 'para_aprox_num', $oCon, 0);

        $totalDeuda = aproxima_digitos($para_aprox_num, $saldo_dmcc_tot);

        return $totalDeuda;
    }
}
