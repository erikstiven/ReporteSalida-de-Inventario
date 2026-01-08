<?php
require_once(path(DIR_INCLUDE) . 'comun.lib.php');
class inventario_class
{

    var $propiedad_1;
    var $propiedad_2;
    var $propiedad_3;
    var $Conexion;

    // CAEBECERA SAEMINV
    function saeminv($Conexion, $ConMYsql, $idempresa,  $idsucursal, $fecha_mov, $user_ifx, $tipo, $clpv_cod, $ord_op, $num_op, $tran)
    {
        // O B T E N E R     M O N E D A      D E S D E      I N F O R M I X
        $sql    = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa ";
        $moneda = consulta_string_func($sql, 'pcon_mon_base', $Conexion, '', 0);

        // O B T E N E R     T C A M B I O      D E S D E      I N F O R M I X
        $sql = "select tcam_fec_tcam, tcam_cod_tcam, tcam_val_tcam from saetcam where
                                    tcam_cod_mone = $moneda and
                                    mone_cod_empr = $idempresa and
                                    tcam_fec_tcam = (select max(tcam_fec_tcam) from saetcam where
                                                            tcam_cod_mone = $moneda and
                                                            tcam_fec_tcam <= '$fecha_mov' and
                                                            mone_cod_empr = $idempresa) ";
        $tcambio = consulta_string_func($sql, 'tcam_cod_tcam', $Conexion, 0);

        // C O D I G O     D E L     E M P L E A D O     I N F O R M I X
        $sql = "SELECT usua_cod_empl, usua_nom_usua FROM SAEUSUA WHERE USUA_COD_USUA = $user_ifx ";
        if ($Conexion->Query($sql)) {
            if ($Conexion->NumFilas() > 0) {
                $empleado      = $Conexion->f('usua_cod_empl');
                $usua_nom_usua = $Conexion->f('usua_nom_usua');
            } else {
                $empleado =  '';
                $usua_nom_usua = '';
            }
        }

        //  ANIO
        $anio = substr($fecha_mov, 0, 4);
        //            $fecha_ejer='12/31/'.$anio;
        $fecha_ejer = $anio . '-12-31';

        //      EJERCICIO  DE  INFORMIX
        $sql = "select ejer_cod_ejer from saeejer where ejer_fec_finl = '$fecha_ejer' and ejer_cod_empr = $idempresa ";
        $idejer = consulta_string_func($sql, 'ejer_cod_ejer', $Conexion, 1);


        //      MES
        list($a, $idprdo, $d) = explode('-', $fecha_mov);

        //      FECHA  DEL SERVIDOR
        $fecha_servidor = date("Y-m-d");
        $hora =  date("H:i:s");


        // SECUENCIAL EGRESO
        $sql_defi = "SELECT DEFI_COD_MODU, DEFI_TRS_DEFI  , DEFI_TIP_DEFI, DEFI_FOR_DEFI FROM SAEDEFI WHERE
                            DEFI_COD_EMPR = $idempresa AND
                            DEFI_COD_SUCU = $idsucursal and
                            defi_cod_modu = 10 and
                            defi_cod_tran = '$tran' ";
        if ($Conexion->Query($sql_defi)) {
            if ($Conexion->NumFilas() > 0) {
                $secu_minv = $Conexion->f('defi_trs_defi');
                $formato   = $Conexion->f('defi_for_defi');
            } else {
                $secu_minv = '';
                $formato = 0;
            }
        }
        $Conexion->Free();

        $secu_minv = secuencial(2, '0', $secu_minv, 8);

        $sql_maxminv = "select max(minv_num_comp) as maximo from saeminv";
        $minv_num_comp = consulta_string($sql_maxminv, 'maximo', $Conexion, 0) + 1;

        // SAEMINV
        $sql_minv  = "insert into saeminv(minv_num_comp, minv_num_plaz,  minv_num_sec,     minv_cod_tcam,
                                            minv_cod_mone,  minv_cod_empr,      minv_cod_sucu,
                                            minv_cod_tran,  minv_cod_modu,      minv_cod_empl,
                                            minv_cod_ftrn,  minv_fmov,          minv_dege_minv,
                                            minv_cod_usua,  minv_num_prdo,      minv_cod_ejer,
                                            minv_fec_ser,   minv_est_minv,      minv_con_iva,
                                            minv_sin_iva,   minv_dge_valo,      minv_iva_valo,
                                            minv_otr_valo,  minv_fle_minv,      minv_fec_entr,
                                            minv_sno_esta,  minv_usu_minv,      minv_cod_clpv)
                                    values($minv_num_comp, 0,             '$secu_minv',        $tcambio,
                                            $moneda,        $idempresa,         $idsucursal,
                                            '$tran',        10,                '$empleado',
                                            '$formato',     '$fecha_mov',       0,
                                            $user_ifx,      $idprdo,         $idejer,
                                            CURRENT_DATE,        1,                  0,
                                            0,              0,                  0,
                                            0,              0,                  '$fecha_mov',
                                            0,              '$usua_nom_usua',   '$clpv_cod') ";
        $Conexion->QueryT($sql_minv);

        //UPDATE AL SECUENCIAL SAEDEFI
        $sql_update = "UPDATE SAEDEFI SET DEFI_TRS_DEFI = '$secu_minv' WHERE
                                DEFI_COD_EMPR = $idempresa AND
                                DEFI_COD_SUCU = $idsucursal and
                                defi_cod_modu = 10 and
                                defi_cod_tran = '$tran' ";
        $Conexion->QueryT($sql_update);

        //SERIAL DEL SAEDMIV
        $serial_minv = 0;
        $sql_serial = "select minv_num_comp from saeminv where
                                minv_num_sec = '$secu_minv' and
                                minv_cod_empr = $idempresa and
                                minv_cod_sucu = $idsucursal and
                                minv_cod_tran = '$tran' and
                                minv_cod_modu = 10 and
                                minv_cod_ejer = $idejer  ";
        $serial_minv = consulta_string_func($sql_serial, 'minv_num_comp', $Conexion, 0);

        unset($array);
        $array[] = array($serial_minv, $idejer, $idprdo, $tran, $hora, $secu_minv);
        return $array;
    }


    function saedmov(
        $Conexion,
        $idempresa,
        $idsucursal,
        $fecha_mov,
        $hora,
        $idejer,
        $idprdo,
        $serial_minv,
        $tran,
        $x,
        $bode_cod,
        $prod_cod,
        $unid_cod,
        $cantidad,
        $costo,
        $tipo,
        $bode_dest,
        $lote,
        $fec_ela,
        $fec_cad
    ) {

        $hora = date('Y-m-d H:i:s');
        if (empty($fec_ela)) {
            $fec_ela = 'NULL';
        } else {
            $fec_ela = "'" . $fec_ela . "'";
        }
        if (empty($fec_cad)) {
            $fec_cad = 'NULL';
        } else {
            $fec_cad = "'" . $fec_cad . "'";
        }

        $sql = "insert into saedmov ( dmov_cod_dmov,   dmov_cod_prod,   dmov_cod_sucu,
                                        dmov_cod_empr,   dmov_cod_bode,     dmov_cod_unid,
                                        dmov_cod_ejer,   dmov_num_comp,     dmov_num_prdo,
                                        dmov_can_dmov,   dmov_can_entr,     dmov_cun_dmov,
                                        dmov_cto_dmov,   dmov_pun_dmov,     dmov_pto_dmov,
                                        dmov_ds1_dmov,   dmov_ds2_dmov,     dmov_ds3_dmov,
                                        dmov_ds4_dmov,   dmov_des_tota,     dmov_imp_dmov,
                                        dmov_est_dmov,   dmov_iva_dmov,     dmov_iva_porc,
                                        dmov_dis_dmov,   dmov_ice_dmov,     dmov_hor_crea,
                                        dmov_cod_tran,   dmov_fmov,         dmov_pto1_dmov,
                                        dmov_cod_lote,   dmov_ela_lote,     dmov_cad_lote   
                                        ) 
                                values(($x),           '$prod_cod',         $idsucursal,
                                        $idempresa,       $bode_cod,        '$unid_cod',
                                        $idejer,          $serial_minv,     $idprdo,
                                        ( $cantidad ),    $cantidad,        $costo,
                                        ( $cantidad*$costo),   $costo,      0,
                                        0,                0,                0,
                                        0,                0,                0,
                                        '1',              0,                0,
                                        'N',              0,                '$hora',
                                        '$tran' ,         '$fecha_mov',     0,
                                        '$lote',          $fec_ela,       $fec_cad  ) ";
        $Conexion->QueryT($sql);

        //producto / servicio
        $sql = "select prod_cod_tpro 
					from saeprod 
					where prod_cod_empr = $idempresa and
					prod_cod_sucu = $idsucursal and
					prod_cod_prod = '$prod_cod'";
        $prod_cod_tpro = consulta_string_func($sql, 'prod_cod_tpro', $Conexion, 0);

        if ($prod_cod_tpro == 2) {

            // sctok bodega
            $sql = "select prbo_dis_prod, prbo_uco_prod from saeprbo where
							prbo_cod_empr = $idempresa and
							prbo_cod_sucu = $idsucursal and
							prbo_cod_bode = $bode_cod and
							prbo_cod_prod = '$prod_cod' ";
            $stock = consulta_string_func($sql, 'prbo_dis_prod', $Conexion, 0);

            // actualiza stock en bodega materia prima - insumos
            $stock_real = 0;
            $tmp        = 0;
            if ($tipo == 'I') {
                // INGRESO
                $stock_real = $stock + $cantidad;
                if($stock_real==0){
                    $stock_real=1;
                }
            } elseif ($tipo == 'E') {
                // EGRESO
                $stock_real = $stock - $cantidad;
            } elseif ($tipo == 'T') {
                // TRANSFERENCIA
                // EGRESO
                $stock_real = $stock - $cantidad;
                $tipo       = 'E';
                $tmp        = 1;
            }
            

            $sql = "update saeprbo set prbo_dis_prod = $stock_real,  prbo_uco_prod = $costo where
								prbo_cod_empr = $idempresa and
								prbo_cod_sucu = $idsucursal and
								prbo_cod_bode = $bode_cod and
								prbo_cod_prod = '$prod_cod' ";

                                //echo $sql;exit;
            $Conexion->QueryT($sql);


            /*
            // SAECOST
            // ID DEL SAECOST EGRESO
            $sql_id_cost = "select max(cost_cod_cost) as maximo from saecost where
									cost_cod_prod = '$prod_cod' and
									cost_cod_empr = $idempresa ";
            $cost_cod_cost = consulta_string_func($sql_id_cost, 'maximo', $Conexion, 0);

            // INGRESO SAECOST
            $sql_cost = "insert into saecost(cost_cod_cost,       cost_cod_prod,      cost_num_comp,
												cost_cod_dmov,        cost_cod_bode,      cost_cod_sucu,
												cost_cod_empr,        cost_num_prdo,      cost_cod_ejer,
												cost_fec_cost,        cost_can_cost,      cost_val_unit,
												cost_est_cost,        cost_tip_cost )
										values(($cost_cod_cost+1),    '$prod_cod',        $serial_minv,
												 ($x),                 $bode_cod,         $idsucursal,
												 $idempresa,          $idprdo,            $idejer,
												 CURRENT_DATE,             $stock_real,        $costo,
												 1,                   '$tipo' ) ";
            $Conexion->QueryT($sql_cost);
            */

            // TRANSFERENCIA INGRESO
            if ($tmp == 1) {
                $tipo = 'I';
                // sctok bodega DESTINO
                $sql = "select prbo_dis_prod, prbo_uco_prod from saeprbo where
								prbo_cod_empr = $idempresa and
								prbo_cod_sucu = $idsucursal and
								prbo_cod_bode = $bode_dest and
								prbo_cod_prod = '$prod_cod' ";
                $stock      = consulta_string_func($sql, 'prbo_dis_prod', $Conexion, 0);
                $stock_real = $stock + $cantidad;

                $sql = "update saeprbo set prbo_dis_prod = $stock_real,  prbo_uco_prod = $costo where
								prbo_cod_empr = $idempresa and
								prbo_cod_sucu = $idsucursal and
								prbo_cod_bode = $bode_dest and
								prbo_cod_prod = '$prod_cod' ";
                $Conexion->QueryT($sql);

                /*
                // INGRESO SAECOST
                $sql_cost = "insert into saecost(cost_cod_cost,       cost_cod_prod,      cost_num_comp,
													cost_cod_dmov,        cost_cod_bode,      cost_cod_sucu,
													cost_cod_empr,        cost_num_prdo,      cost_cod_ejer,
													cost_fec_cost,        cost_can_cost,      cost_val_unit,
													cost_est_cost,        cost_tip_cost )
											values(($cost_cod_cost+2),    '$prod_cod',        $serial_minv,
													 ($x),                 $bode_dest,         $idsucursal,
													 $idempresa,          $idprdo,            $idejer,
													 CURRENT_DATE,             $stock_real,        $costo,
													 1,                   '$tipo' ) ";
                $Conexion->QueryT($sql_cost);
                */
            }
        }

        return 'OK';
    }
}
