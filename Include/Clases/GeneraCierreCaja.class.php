<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class GeneraCierreCaja{

    var $oConexion;
    var $empr_nom_empr;

    function __construct(){
        //$this->empr_nom_empr = $empr_nom_empr;
    }

    function generaCierreCab($oConexion, $idempresa, $sucursal, $usuario, $fecha ){
        $sql = "SELECT c.id_cierre_caja, c.usuario_id, c.sucu_cod_sucu, c.fecha, c.hora, 
					c.estado, c.val_gasto, c.val_cierre_rd, c.usuario_modi, c.fecha_modi 
					from comercial.cierre_caja c where
					c.empr_cod_empr = $idempresa and
					c.sucu_cod_sucu = $sucursal and
					c.usuario_id    = $usuario  and
					c.fecha         = '$fecha' ";
        if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
                unset($array);
                do{
                    $usuario_id 	= $oConexion->f('usuario_id');
                    $sucu_cod_sucu 	= $oConexion->f('sucu_cod_sucu');
                    $fecha       	= $oConexion->f('fecha');
                    $hora        	= $oConexion->f('hora');
                    $estado     	= $oConexion->f('estado');
                    $val_gasto    	= $oConexion->f('val_gasto');
                    $val_cierre_rd  = $oConexion->f('val_cierre_rd');
                    $usuario_modi   = $oConexion->f('usuario_modi');
                    $fecha_modi     = $oConexion->f('fecha_modi');
                    $id_cierre_caja = $oConexion->f('id_cierre_caja');

                    $array[] = array($usuario_id, $sucu_cod_sucu, $fecha, $hora, $estado, $val_gasto, $val_cierre_rd, $usuario_modi,
                        $fecha_modi, $id_cierre_caja );
                }while($oConexion->SiguienteRegistro());
            }
        }
        $oConexion->Free();

        return $array;
    }

    function generaCierreGasto($oConexion, $idempresa, $sucursal, $usuario, $id_cierre ){
        $sql = "select id_gasto_pre, gasto, valor, factura, detalle, id_gasto 
					from comercial.cierre_gasto 
					where id_empresa    = $idempresa and 
					id_sucursal         = $sucursal and
					user_web            = $usuario and
					id_precierre        = $id_cierre  ";
        if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
                unset($array);
                do{
                    $id_gasto_pre = $oConexion->f('id_gasto_pre');
                    $gasto 		  = $oConexion->f('gasto');
                    $valor 		  = $oConexion->f('valor');
                    $factura      = $oConexion->f('factura');
                    $detalle      = $oConexion->f('detalle');
                    $id_gasto     = $oConexion->f('id_gasto');

                    $array[] = array($id_gasto_pre, $gasto, $valor, $factura,  $detalle, $id_gasto );
                }while($oConexion->SiguienteRegistro());
            }
        }
        $oConexion->Free();

        return $array;
    }

    function generaCierreBilleteUSD($oConexion, $idempresa, $sucursal, $usuario, $fecha ){
        $sql = "select d.id_det_caja,  d.forma_pago, d.cantidad, d.valor, d.total, d.tipo, c.estado
						from comercial.cierre_caja c , comercial.det_cierre_caja d where
						c.id_cierre_caja    = d.id_cierre_caja and
						c.empr_cod_empr     = $idempresa and
						c.sucu_cod_sucu     = $sucursal and
						c.usuario_id        = $usuario and
						c.fecha             = '$fecha' and
						d.empr_cod_empr     = $idempresa and
						d.sucu_cod_sucu     = $sucursal and
						d.forma_pago        = 'EFE' and
						d.tipo              = 'B' and
						d.tipo_mone         = 'ML' ";
        if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
                unset($array);
                do{
                    $id_det_caja = $oConexion->f('id_det_caja');
                    $forma_pago  = $oConexion->f('forma_pago');
                    $cantidad 	 = $oConexion->f('cantidad');
                    $valor       = $oConexion->f('valor');
                    $total       = $oConexion->f('total');
                    $tipo        = $oConexion->f('tipo');
                    $estado      = $oConexion->f('estado');

                    $array[] = array($id_det_caja, $forma_pago, $cantidad, $valor,  $total, $tipo , $estado );
                }while($oConexion->SiguienteRegistro());
            }
        }
        $oConexion->Free();

        return $array;
    }


    function generaCierreMonedaUSD($oConexion, $idempresa, $sucursal, $usuario, $fecha ){
        $sql = "select d.id_det_caja,  d.forma_pago, d.cantidad, d.valor, d.total, d.tipo, c.estado
						from comercial.cierre_caja c , comercial.det_cierre_caja d where
						c.id_cierre_caja    = d.id_cierre_caja and
						c.empr_cod_empr     = $idempresa and
						c.sucu_cod_sucu     = $sucursal and
						c.usuario_id        = $usuario and
						c.fecha             = '$fecha' and
						d.empr_cod_empr     = $idempresa and
						d.sucu_cod_sucu     = $sucursal and
						d.forma_pago        = 'EFE' and
						d.tipo              = 'M' and
						tipo_mone           = 'ML'  ";
        if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
                unset($array);
                do{
                    $id_det_caja = $oConexion->f('id_det_caja');
                    $forma_pago  = $oConexion->f('forma_pago');
                    $cantidad 	 = $oConexion->f('cantidad');
                    $valor       = $oConexion->f('valor');
                    $total       = $oConexion->f('total');
                    $tipo        = $oConexion->f('tipo');
                    $estado      = $oConexion->f('estado');

                    $array[] = array($id_det_caja, $forma_pago, $cantidad, $valor,  $total, $tipo , $estado );
                }while($oConexion->SiguienteRegistro());
            }
        }
        $oConexion->Free();

        return $array;
    }


    function generaCierreBilleteRD($oConexion, $idempresa, $sucursal, $usuario, $fecha ){
        $sql = "select d.id_det_caja,  d.forma_pago, d.cantidad, d.valor, d.total, d.tipo, c.estado
						from comercial.cierre_caja c , comercial.det_cierre_caja d where
						c.id_cierre_caja    = d.id_cierre_caja and
						c.empr_cod_empr     = $idempresa and
						c.sucu_cod_sucu     = $sucursal and
						c.usuario_id        = $usuario and
						c.fecha             = '$fecha' and
						d.empr_cod_empr     = $idempresa and
						d.sucu_cod_sucu     = $sucursal and
						d.forma_pago        = 'EFE' and
						d.tipo              = 'B' and
						d.tipo_mone         = 'ME' ";
        if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
                unset($array);
                do{
                    $id_det_caja = $oConexion->f('id_det_caja');
                    $forma_pago  = $oConexion->f('forma_pago');
                    $cantidad 	 = $oConexion->f('cantidad');
                    $valor       = $oConexion->f('valor');
                    $total       = $oConexion->f('total');
                    $tipo        = $oConexion->f('tipo');
                    $estado      = $oConexion->f('estado');

                    $array[] = array($id_det_caja, $forma_pago, $cantidad, $valor,  $total, $tipo , $estado );
                }while($oConexion->SiguienteRegistro());
            }
        }
        $oConexion->Free();

        return $array;
    }


    function generaCierreMonedaRD($oConexion, $idempresa, $sucursal, $usuario, $fecha ){
        $sql = "select d.id_det_caja,  d.forma_pago, d.cantidad, d.valor, d.total, d.tipo, c.estado
						from comercial.cierre_caja c , comercial.det_cierre_caja d where
						c.id_cierre_caja    = d.id_cierre_caja and
						c.empr_cod_empr     = $idempresa and
						c.sucu_cod_sucu     = $sucursal and
						c.usuario_id        = $usuario and
						c.fecha             = '$fecha' and
						d.empr_cod_empr     = $idempresa and
						d.sucu_cod_sucu     = $sucursal and
						d.forma_pago        = 'EFE' and
						d.tipo              = 'M' and
						tipo_mone           = 'ME'  ";
        if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
                unset($array);
                do{
                    $id_det_caja = $oConexion->f('id_det_caja');
                    $forma_pago  = $oConexion->f('forma_pago');
                    $cantidad 	 = $oConexion->f('cantidad');
                    $valor       = $oConexion->f('valor');
                    $total       = $oConexion->f('total');
                    $tipo        = $oConexion->f('tipo');
                    $estado      = $oConexion->f('estado');

                    $array[] = array($id_det_caja, $forma_pago, $cantidad, $valor,  $total, $tipo , $estado );
                }while($oConexion->SiguienteRegistro());
            }
        }
        $oConexion->Free();

        return $array;
    }


    function generaCierreFormaPago($oConexion, $idempresa, $sucursal, $usuario, $fecha_ifx, $tipo ){
        $sql = "select f.fact_cod_fact, f.fact_num_preimp, f.fact_nse_fact,
							fp.fpag_des_fpag, fx.fxfp_val_fxfp, fx.fxfp_num_rete
						    from saefact f, saefxfp fx, saefpag fp
							where f.fact_cod_fact = fx.fxfp_cod_fact and
							fp.fpag_cod_fpag      = fx.fxfp_cod_fpag and
							f.fact_cod_empr       = fx.fxfp_cod_empr and
							f.fact_cod_sucu       = fx.fxfp_cod_sucu and
							f.fact_cod_empr       = $idempresa and
							fx.fxfp_cod_empr      = $idempresa and
							f.fact_cod_sucu       = $sucursal and
							fx.fxfp_cod_sucu      = $sucursal and
							f.fact_fech_fact      = '$fecha_ifx' and
							f.fact_user_web       = $usuario and
							fp.fpag_cot_fpag      = '$tipo' order by fpag_des_fpag ";
        if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
                unset($array);
                do{
                    $fact_mum_preimp = $oConexion->f('fact_num_preimp');
                    $fact_nse_fact   = $oConexion->f('fact_nse_fact');
                    $fpag_des_fpag   = $oConexion->f('fpag_des_fpag');
                    $fxfp_val_fxfp   = $oConexion->f('fxfp_val_fxfp');
                    $fact_cod_fact   = $oConexion->f('fact_cod_fact');
                    $fxfp_num_rete   = $oConexion->f('fxfp_num_rete');

                    $array[] = array($fact_mum_preimp, $fact_nse_fact, $fpag_des_fpag, $fxfp_val_fxfp,  $fact_cod_fact, $fxfp_num_rete );
                }while($oConexion->SiguienteRegistro());
            }
        }
        $oConexion->Free();

        return $array;
    }


    function generaCierreTarjeta($oConexion, $idempresa, $sucursal, $usuario, $fecha_ifx ){
        $sql = "select f.fact_cod_fact, f.fact_num_preimp, f.fact_nse_fact,
							fp.fpag_des_fpag, fx.fxfp_val_fxfp, fx.fxfp_num_rete
						    from saefact f, saefxfp fx, saefpag fp
							where f.fact_cod_fact = fx.fxfp_cod_fact and
							fp.fpag_cod_fpag      = fx.fxfp_cod_fpag and
							f.fact_cod_empr       = fx.fxfp_cod_empr and
							f.fact_cod_sucu       = fx.fxfp_cod_sucu and
							f.fact_cod_empr       = $idempresa and
							fx.fxfp_cod_empr      = $idempresa and
							f.fact_cod_sucu       = $sucursal and
							fx.fxfp_cod_sucu      = $sucursal and
							f.fact_fech_fact      = '$fecha_ifx' and
							f.fact_user_web       = $usuario and
							fp.fpag_cot_fpag in ('TAR' ) order by fpag_des_fpag ";
        if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
                unset($array);
                do{
                    $fact_mum_preimp = $oConexion->f('fact_num_preimp');
                    $fact_nse_fact   = $oConexion->f('fact_nse_fact');
                    $fpag_des_fpag   = $oConexion->f('fpag_des_fpag');
                    $fxfp_val_fxfp   = $oConexion->f('fxfp_val_fxfp');
                    $fact_cod_fact   = $oConexion->f('fact_cod_fact');
                    $fxfp_num_rete   = $oConexion->f('fxfp_num_rete');

                    $array[] = array($fact_mum_preimp, $fact_nse_fact, $fpag_des_fpag, $fxfp_val_fxfp,  $fact_cod_fact, $fxfp_num_rete );
                }while($oConexion->SiguienteRegistro());
            }
        }
        $oConexion->Free();

        return $array;
    }


    function generaCierreTransferencia($oConexion, $idempresa, $sucursal, $usuario, $fecha_ifx ){
        $sql = "select f.fact_cod_fact, f.fact_num_preimp, f.fact_nse_fact,
							fp.fpag_des_fpag, fx.fxfp_val_fxfp, fx.fxfp_num_rete
						    from saefact f, saefxfp fx, saefpag fp
							where f.fact_cod_fact = fx.fxfp_cod_fact and
							fp.fpag_cod_fpag      = fx.fxfp_cod_fpag and
							f.fact_cod_empr       = fx.fxfp_cod_empr and
							f.fact_cod_sucu       = fx.fxfp_cod_sucu and
							f.fact_cod_empr       = $idempresa and
							fx.fxfp_cod_empr      = $idempresa and
							f.fact_cod_sucu       = $sucursal and
							fx.fxfp_cod_sucu      = $sucursal and
							f.fact_fech_fact      = '$fecha_ifx' and
							f.fact_user_web       = $usuario and
							fp.fpag_cot_fpag in ('DEP' ) order by fpag_des_fpag ";
        if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
                unset($array);
                do{
                    $fact_mum_preimp = $oConexion->f('fact_num_preimp');
                    $fact_nse_fact   = $oConexion->f('fact_nse_fact');
                    $fpag_des_fpag   = $oConexion->f('fpag_des_fpag');
                    $fxfp_val_fxfp   = $oConexion->f('fxfp_val_fxfp');
                    $fact_cod_fact   = $oConexion->f('fact_cod_fact');
                    $fxfp_num_rete   = $oConexion->f('fxfp_num_rete');

                    $array[] = array($fact_mum_preimp, $fact_nse_fact, $fpag_des_fpag, $fxfp_val_fxfp,  $fact_cod_fact, $fxfp_num_rete );
                }while($oConexion->SiguienteRegistro());
            }
        }
        $oConexion->Free();

        return $array;
    }


    function generaCierreCredito($oConexion, $idempresa, $sucursal, $usuario, $fecha_ifx ){
        $sql = "select f.fact_cod_fact, f.fact_num_preimp, f.fact_nse_fact,
							fp.fpag_des_fpag, fx.fxfp_val_fxfp, fx.fxfp_num_rete
						    from saefact f, saefxfp fx, saefpag fp
							where f.fact_cod_fact = fx.fxfp_cod_fact and
							fp.fpag_cod_fpag      = fx.fxfp_cod_fpag and
							f.fact_cod_empr       = fx.fxfp_cod_empr and
							f.fact_cod_sucu       = fx.fxfp_cod_sucu and
							f.fact_cod_empr       = $idempresa and
							fx.fxfp_cod_empr      = $idempresa and
							f.fact_cod_sucu       = $sucursal and
							fx.fxfp_cod_sucu      = $sucursal and
							f.fact_fech_fact      = '$fecha_ifx' and
							f.fact_user_web       = $usuario and
							fp.fpag_cot_fpag in ('CRE' ) order by fpag_des_fpag ";
        if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
                unset($array);
                do{
                    $fact_mum_preimp = $oConexion->f('fact_num_preimp');
                    $fact_nse_fact   = $oConexion->f('fact_nse_fact');
                    $fpag_des_fpag   = $oConexion->f('fpag_des_fpag');
                    $fxfp_val_fxfp   = $oConexion->f('fxfp_val_fxfp');
                    $fact_cod_fact   = $oConexion->f('fact_cod_fact');
                    $fxfp_num_rete   = $oConexion->f('fxfp_num_rete');

                    $array[] = array($fact_mum_preimp, $fact_nse_fact, $fpag_des_fpag, $fxfp_val_fxfp,  $fact_cod_fact, $fxfp_num_rete );
                }while($oConexion->SiguienteRegistro());
            }
        }
        $oConexion->Free();

        return $array;
    }

    function generaCierreRetencion($oConexion, $idempresa, $sucursal, $usuario, $fecha_ifx ){
        $sql = "select f.fact_cod_fact, f.fact_num_preimp, f.fact_nse_fact,
							fp.fpag_des_fpag, fx.fxfp_val_fxfp, fx.fxfp_num_rete
						    from saefact f, saefxfp fx, saefpag fp
							where f.fact_cod_fact = fx.fxfp_cod_fact and
							fp.fpag_cod_fpag      = fx.fxfp_cod_fpag and
							f.fact_cod_empr       = fx.fxfp_cod_empr and
							f.fact_cod_sucu       = fx.fxfp_cod_sucu and
							f.fact_cod_empr       = $idempresa and
							fx.fxfp_cod_empr      = $idempresa and
							f.fact_cod_sucu       = $sucursal and
							fx.fxfp_cod_sucu      = $sucursal and
							f.fact_fech_fact      = '$fecha_ifx' and
							f.fact_user_web       = $usuario and
							fp.fpag_cot_fpag in ('RET' ) order by fpag_des_fpag ";
        if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
                unset($array);
                do{
                    $fact_mum_preimp = $oConexion->f('fact_num_preimp');
                    $fact_nse_fact   = $oConexion->f('fact_nse_fact');
                    $fpag_des_fpag   = $oConexion->f('fpag_des_fpag');
                    $fxfp_val_fxfp   = $oConexion->f('fxfp_val_fxfp');
                    $fact_cod_fact   = $oConexion->f('fact_cod_fact');
                    $fxfp_num_rete   = $oConexion->f('fxfp_num_rete');

                    $array[] = array($fact_mum_preimp, $fact_nse_fact, $fpag_des_fpag, $fxfp_val_fxfp,  $fact_cod_fact, $fxfp_num_rete );
                }while($oConexion->SiguienteRegistro());
            }
        }
        $oConexion->Free();

        return $array;
    }
}
?>