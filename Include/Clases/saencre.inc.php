<?php
//header('Content-Type: text/html; charset=utf-8');
//require_once(path(DIR_INCLUDE) . 'comun.lib.php');
//header('Content-Type: text/html; charset=utf-8');

class Saencre_class
{

    var $oCon;
    var $oConA;
    var $oIfx;
    var $idEmpresa;
    var $idSucursal;
    var $idClpv;
    var $idContrato;
    var $fecha_ncre;
    var $mone_cod;
    var $user_ifx;
    var $user_web;
    var $id_ejer;
    var $id_prdo;

    function __construct($oCon, $oConA, $oIfx, $idEmpresa, $idSucursal, $idClpv, $idContrato, $fecha_ncre, $moneda, $user_ifx, $user_web, $id_ejer, $id_prdo)
    {
        $this->oCon = $oCon;
        $this->oConA = $oConA;
        $this->oIfx = $oIfx;
        $this->idEmpresa = $idEmpresa;
        $this->idSucursal = $idSucursal;
        $this->idClpv = $idClpv;
        $this->idContrato = $idContrato;
        $this->fecha_ncre = $fecha_ncre;
        $this->mone_cod = $moneda;
        $this->user_ifx = $user_ifx;
        $this->user_web = $user_web;
        $this->id_ejer = $id_ejer;
        $this->id_prdo = $id_prdo;
    }

    function saencre(
        $ncre_cod_aux,
        $tcambio,
        $vend_cod,
        $empl_cod,
        $ftrn_cod,
        $ncre_num,
        $clpv_nom,
        $clpv_tel,
        $ncre_iva,
        $ncre_con_miva,
        $ncre_sin_miva,
        $estado_fact,
        $ncre_dsg_porc,
        $ncre_dsg_valo,
        $ncre_fle_fact,
        $ncre_otr_fact,
        $ncre_fin_fact,
        $ncre_tot_fact,
        $clpv_ruc,
        $clpv_dir,
        $ncre_motivo,
        $ncre_estab,
        $autorizacion,
        $fecha_aut,
        $id_factura,
        $ncre_ice,
        $val_tcambio,
        $asto_cod,
        $tipo_comprobante,
        $ncre_irbp,
        $ncre_email_clpv,
        $ncre_cm2_ncre,
        $ncre_ncf_num,
        $isp = 0,
        $ncre_fech_docu=''
    ) {


        $oCon = $this->oCon;
        $oConA = $this->oConA;
        $oIfx = $this->oIfx;
        $idempresa = $this->idEmpresa;
        $idsucursal = $this->idSucursal;
        $id_cliente = $this->idClpv;
        $id_Contrato = $this->idContrato;
        $fecha_ncre = $this->fecha_ncre;
        $mone_cod = $this->mone_cod;
        $user_ifx = $this->user_ifx;
        $user_web = $this->user_web;
        $id_ejer = $this->id_ejer;
        $id_prdo = $this->id_prdo;

        $ncre_fech_docu = $ncre_fech_docu != '' ? "'".$ncre_fech_docu."'" : 'NULL';

        $ncre_cm2_ncre = limpiar_string($ncre_cm2_ncre);

        if (empty($vend_cod)) {
            $sql = "select clpv_cod_vend from saeclpv where clpv_cod_empr = $idempresa and clpv_cod_clpv = $id_cliente ";
            $vend_cod = consulta_string_func($sql, 'clpv_cod_vend', $oIfx, '');
        }

        if (empty($ftrn_cod)) {
            $sql_formato = "select ftrn_cod_ftrn from saeftrn where
                                        ftrn_cod_empr = $idempresa and
                                        ftrn_cod_modu = 7 and
                                        ftrn_des_ftrn = 'NOTA_CREDITO' ";
            $ftrn_cod = consulta_string($sql_formato, 'ftrn_cod_ftrn', $oIfx, 0);
        }

        if (!$fecha_aut) {
            $fecha_aut = "null";
        } else {
            $fecha_aut = "'$fecha_aut'";
        }

        if (empty($ncre_dsg_porc)) {
            $ncre_dsg_porc = 0;
        }

        $sqlEncaNC = "insert into saencre(  
                        ncre_cod_aux,       ncre_cod_sucu,      ncre_cod_empr,           ncre_cod_mone,      ncre_cod_tcam,      ncre_cod_ccos,
                        ncre_cod_clpv,      ncre_cod_vend,      ncre_cod_empl,           ncre_cod_cons,      ncre_cod_ftrn,      ncre_fech_fact,
                        ncre_fech_venc,     ncre_cod_usua,      ncre_num_prdo,           ncre_cod_ejer,      ncre_num_preimp,    ncre_nom_cliente,
                        ncre_tlf_cliente,   ncre_iva,           ncre_con_miva,           ncre_sin_miva,      ncre_mon_rete_ext,  ncre_fec_servidor,
                        ncre_est_fact,      ncre_prc_fact,      ncre_dsg_porc,           ncre_dsg_valo,      ncre_fle_fact,      ncre_otr_fact,
                        ncre_fin_fact,      ncre_tot_fact,      ncre_ruc_clie,           ncre_dir_clie,      ncre_dia_ncre,      ncre_cm1_ncre,
                        ncre_cm2_ncre,      ncre_cm3_ncre,      ncre_cm4_ncre,           ncre_sucu_clpv,     ncre_est_stby,      ncre_est_auto,
                        ncre_est_apro,      ncre_nse_ncre,      ncre_nau_ncre,           ncre_fech_aut,      ncre_cod_fact,      ncre_ice,
                        ncre_val_tcam,      ncre_cod_asto,      ncre_cod_ccli,           ncre_tip_vent,      ncre_irbp,          ncre_email_clpv,
                        ncre_user_web,      ncre_cod_contr ,    ncre_ncf_num,            ncre_auto_sri,      ncre_fech_sri,      ncre_user_sri, ncre_clav_sri, ncre_fech_docu) values(        
                        '$ncre_cod_aux',    $idsucursal,        $idempresa,               $mone_cod,          $tcambio,           '',
                        $id_cliente,        '$vend_cod',        '$empl_cod',             null,                  $ftrn_cod,          '$fecha_ncre',
                        '$fecha_ncre',      $user_ifx,          $id_prdo,                 $id_ejer,           '$ncre_num',        '$clpv_nom',
                        '$clpv_tel',        '$ncre_iva',        '$ncre_con_miva',         '$ncre_sin_miva',     0,                   CURRENT_DATE,
                        '$estado_fact',     1,                  '$ncre_dsg_porc',           '$ncre_dsg_valo',     '$ncre_fle_fact',     '$ncre_otr_fact',
                        '$ncre_fin_fact',   '$ncre_tot_fact',     '$clpv_ruc',             '$clpv_dir',         0,                 '$ncre_motivo',
                        '$ncre_cm2_ncre',   '',                 '',                       $idsucursal,      '',                 '',
                        '',                 '$ncre_estab',      '$autorizacion',          $fecha_aut,       $id_factura,        '$ncre_ice',
                        '$val_tcambio',       '$asto_cod',         0,                     '$tipo_comprobante', '$ncre_irbp',         '$ncre_email_clpv',
                        $user_web,          $id_Contrato ,      '$ncre_ncf_num',        '$autorizacion',    $fecha_aut,    $user_web,  '$autorizacion', $ncre_fech_docu) RETURNING ncre_cod_ncre;";

        $oIfx->QueryT($sqlEncaNC);

        $ncre_cod_ncre = $oIfx->ResRow['ncre_cod_ncre'];

        if (!$ncre_cod_ncre) {
            throw new Exception("No se pudo insertar la nota de credito");
        }

        $ncre_total = 0;
        $ncre_total = round(($ncre_tot_fact - $ncre_dsg_valo + $ncre_iva + $ncre_ice + $ncre_irbp), 2);

        if ($isp) {
            $sql = "SELECT id, fecha, mes, anio, 
                    c.tarifa, c.tot_add, c.valor_pago FROM contrato_pago c WHERE
                    c.id_contrato = $id_Contrato AND
                    c.id_clpv     = $id_cliente
                    HAVING (c.tarifa+c.tot_add) != c.valor_pago
                    ORDER BY fecha; ";
            $id_pago = 0;
            $tarifa = 0;
            $tot_add = 0;
            $valor_pago = 0;
            $total_pago = 0;
            $saldo = 0;
            $i = 0;
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $id_pago = $oCon->f('id');
                        $tarifa = $oCon->f('tarifa');
                        $tot_add = $oCon->f('tot_add');
                        $valor_pago = $oCon->f('valor_pago');
                        $total_pago = round(($tarifa + $tot_add - $valor_pago), 2);
                        $tarifa_tot = $tarifa + $tot_add;

                        $total_pago = $ncre_total - $tarifa_tot;
                        if ($total_pago > 0) {
                            //NCRE ES MAYOR Q LA TARIFA
                            $ncre_total -= $total_pago;
                            $sql = "update contrato_pago set valor_nc = $tarifa_tot where
                                        id_contrato = $id_Contrato AND
                                        id_clpv     = $id_cliente  and
                                        id          = $id_pago ";
                            $oConA->QueryT($sql);

                            $sql = "insert into contrato_factura ( id_empresa  ,        id_sucursal,        id_contrato,        id_clpv,        id_pago,
                                                               id_factura,          valor,              user_web,           fecha_server ,  tipo  )
                                                       values( $idempresa,          $idsucursal,        $id_Contrato,       $id_cliente,    $id_pago,
                                                               $ncre_cod_ncre,      $tarifa_tot,        $user_web,          now(),          'N'  )  ";
                            $oConA->QueryT($sql);
                        } else {
                            // NCRE ES MENOR Q LA TARIFA
                            $sql = "update contrato_pago set valor_nc = $ncre_total where
                                        id_contrato = $id_Contrato AND
                                        id_clpv     = $id_cliente  and
                                        id          = $id_pago ";
                            $oConA->QueryT($sql);

                            $sql = "insert into contrato_factura ( id_empresa  ,        id_sucursal,        id_contrato,        id_clpv,        id_pago,
                                                               id_factura,          valor,              user_web,           fecha_server ,  tipo  )
                                                       values( $idempresa,          $idsucursal,        $id_Contrato,       $id_cliente,    $id_pago,
                                                               $ncre_cod_ncre,      $ncre_total,        $user_web,          now(),          'N'  )  ";
                            $oConA->QueryT($sql);

                            break 1;
                        }

                        $i++;
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();
        }

        return $ncre_cod_ncre;
    }


    function saedncr(
        $dncr_cod_dncr,
        $prod_cod,
        $bode_cod,
        $unid_cod,
        $ncre_cod,
        $cant,
        $precio,
        $monto,
        $desc1,
        $iva_porc,
        $dsg_porc,
        $detalle,
        $lote,
        $ccos_cod,
        $ice_porc,
        $vend_cod,
        $prod_nom,
        $irbp_porc,
        $fecha_cad,
        $dncr_cod_mes,
        $dncr_cod_dfac='',
        $dncr_obj_iva='',
        $dncr_exc_iva=''
    ) {
        $oIfx = $this->oIfx;
        $idempresa = $this->idEmpresa;
        $idsucursal = $this->idSucursal;
        $id_cliente = $this->idClpv;
        $id_Contrato = $this->idContrato;
        $fecha_ncre = $this->fecha_ncre;
        $mone_cod = $this->mone_cod;
        $user_ifx = $this->user_ifx;
        $user_web = $this->user_web;
        $id_ejer = $this->id_ejer;
        $id_prdo = $this->id_prdo;

        if (!$desc1) {
            $desc1 = 0;
        }

        $cod_pais = $_SESSION['S_PAIS_API_SRI'];

        if ($cod_pais == '51') {
            $dfac_obj_iva = 0;
            $dfac_exc_iva = 0;

            $sql = "SELECT prod_sn_noi, prod_sn_exe from saeprod where prod_cod_prod = '$prod_cod'";
            $prod_sn_noi = consulta_string_func($sql, 'prod_sn_noi', $oIfx, '');
            $prod_sn_exe = consulta_string_func($sql, 'prod_sn_exe', $oIfx, '');

            if ($prod_sn_noi == 'S') {
                $dfac_obj_iva = 1;
            }

            if ($prod_sn_exe == 'S') {
                $dfac_exc_iva = 1;
            }

            if(!empty($dncr_obj_iva)){
                $dfac_obj_iva=$dncr_obj_iva;
            }
            if(!empty($dncr_exc_iva)){
                $dfac_exc_iva=$dncr_exc_iva;
            }
        } else {
            $prod_no_obj_igv = 'N';
            $prod_exe_igv = 'N';
            $dfac_obj_iva = 0;
            $dfac_exc_iva = 0;
        }


        if(empty($dncr_cod_dfac)){
            $dncr_cod_dfac='NULL';
        }
        $sqlDetaNC = "insert into saedncr(      
                            dncr_cod_dncr,       dncr_cod_sucu,      dncr_cod_empr,          dncr_cod_prod,      dncr_cod_bode,      dncr_cod_unid,
                            dncr_cod_ncre,       dncr_num_prdo,      dncr_cod_ejer,          dncr_cant_dfac,     dncr_precio_dfac,   dncr_mont_total,
                            dncr_mont_tot_ext,   dncr_des1_dfac,     dncr_des2_dfac,         dncr_des3_dfac,     dncr_por_iva,       dncr_por_dsg,
                            dncr_cos_uni,        dncr_des4_dfac,     dncr_tot_des,           dncr_tot_imp,       dncr_tot_lin_loc,   dncr_pro_pedf,
                            dncr_tot_lin_ext,    dncr_tip_dncr,      dncr_det_dncr,          dncr_cod_empa,      dncr_can_empa,      dncr_cod_lote,
                            dncr_lot_clpv,       dncr_orc_clpv,      dncr_cod_tdev,          dncr_num_caja,      dncr_can_auxi,      dncr_cod_ccos,
                            dncr_prod_serv,      dncr_inc_imp,       dncr_por_ice,           dncr_cod_linp,      dncr_cod_vend,      dncr_cod_clpv,
                            dncr_fech_emi,       dncr_cod_part,      dncr_alt_dncr,          dncr_anc_dncr,      dncr_m2_dncr,       dncr_nom_prod,
                            dncr_cant_m2,        dncr_por_irbp,      dncr_cod_ccli,          dncr_nom_subc,      dncr_num_ser1,      dncr_num_ser2,
                            dncr_num_ser3,       dncr_num_ser4,		 dncr_lote_fcad,         dncr_cod_mes,       dncr_obj_iva,       dncr_exc_iva,
                            dncr_cod_dfac       )   values(
                            $dncr_cod_dncr,      $idsucursal,         $idempresa,             '$prod_cod',        $bode_cod,          $unid_cod,
                            $ncre_cod,           $id_prdo,            $id_ejer,                $cant,             $precio,            $monto,
                            0,                   $desc1,              0,                       0,                 $iva_porc,          $dsg_porc,
                            0,                   0,                   0,                       0,                 0,                 0,
                            0,                  0,                  '$detalle',               null,                 0,                 '$lote',
                            '',                 '',                  '',                       '',                 0,                 '$ccos_cod',
                            '',                 'N',                 '$ice_porc',              0,                 '$vend_cod',        $id_cliente,
                            '$fecha_ncre',      0,                 0,                       0,                 0,                 '$prod_nom',
                            0,                 '$irbp_porc',        0,                       '',                 '',                 '',
                            '',                 '',					'$fecha_cad',             '$dncr_cod_mes',    $dfac_obj_iva,      $dfac_exc_iva,
                            $dncr_cod_dfac )";

        $oIfx->QueryT($sqlDetaNC);

        $sql_update_prbo = "update saeprbo set prbo_fec_udev = '$fecha_ncre' where
                                                                                prbo_cod_empr = $idempresa and
                                                                                prbo_cod_sucu = $idsucursal and
                                                                                prbo_cod_bode = $bode_cod and 
                                                                                prbo_cod_prod = '$prod_cod'";
        $oIfx->QueryT($sql_update_prbo);
    }


    function saeminv($tcambio, $empl_cod, $ncre_num, $usua_nom, $ncre_cm1, $val_tcambio, $anio, $mes)
    {
        $oIfx = $this->oIfx;
        $idempresa = $this->idEmpresa;
        $idsucursal = $this->idSucursal;
        $id_cliente = $this->idClpv;
        $id_Contrato = $this->idContrato;
        $fecha_ncre = $this->fecha_ncre;
        $mone_cod = $this->mone_cod;
        $user_ifx = $this->user_ifx;
        $user_web = $this->user_web;
        $id_ejer = $this->id_ejer;
        $id_prdo = $this->id_prdo;
        $hora = date("Y-m-d H:i:s");

        //TIPO TRANSACCION
        $sql_para = "SELECT PARA_NCR_TRANS FROM SAEPARA WHERE
                        PARA_COD_EMPR = $idempresa AND
                        PARA_COD_SUCU = $idsucursal ";
        $para_ncr_trans = consulta_string_func($sql_para, 'para_ncr_trans', $oIfx, '');

        //SECUENCIAL SAEDEFI
        $sql_defi = "SELECT DEFI_TRS_DEFI, defi_for_defi  FROM SAEDEFI WHERE
                            DEFI_COD_EMPR = $idempresa AND
                            DEFI_COD_SUCU = $idsucursal and
                            defi_cod_modu = 10 and
                            defi_tip_defi = '0' and
                            defi_cod_tran = '$para_ncr_trans' ";
        $secu_minv = consulta_string_func($sql_defi, 'defi_trs_defi', $oIfx, 0);
        $formato = consulta_string($sql_defi, 'defi_for_defi', $oIfx, 0);

        $secu_minv_real = secuencial_pedido(2, '0', $secu_minv, 8);


        $sql_ultimo_id = "select max(minv_num_comp) as minv_num_comp from saeminv";
        $ultimo_id = consulta_string($sql_ultimo_id, 'minv_num_comp', $oIfx, '');

        $ultimo_id = $ultimo_id + 1;

        $sql_minv = "insert into saeminv
                          ( minv_num_comp, minv_num_plaz,   minv_num_sec,       minv_cod_tcam,		  minv_cod_mone,          minv_cod_empr,      
                            minv_cod_sucu,   minv_cod_tran,      minv_cod_modu,       minv_cod_empl,		  minv_cod_ftrn,  
                            minv_fmov,       minv_dege_minv,     minv_cod_usua,       minv_num_prdo,          minv_cod_ejer,
                            minv_fec_entr,   minv_fec_ser,   	 minv_est_minv,       minv_tot_minv,          minv_fac_prov,
                            minv_con_iva,    minv_sin_iva,       minv_dge_valo,       minv_iva_valo,		  minv_otr_valo,  
                            minv_fle_minv,   minv_sucu_clpv,	 minv_usu_minv ,      minv_cm1_minv,          minv_fec_regc,      
                            minv_ani_minv,   minv_mes_minv,      minv_user_web,       minv_cod_clpv,          minv_val_tcam ,     
                            minv_hor_minv )  values( '$ultimo_id',
                            0,              '$secu_minv_real',  $tcambio,             $mone_cod,              $idempresa,
                            $idsucursal,    '$para_ncr_trans',  10,                   '$empl_cod',            $formato,
                            '$fecha_ncre',  0,                  $user_ifx,            $id_prdo,               $id_ejer,
                            '$fecha_ncre',   CURRENT_DATE,            '1',                  0,                      '$ncre_num',
                            0,              0,                  0,                    0,                      0,
                            0,              $idsucursal,        '$usua_nom',          '$ncre_cm1',            '$fecha_ncre',
                            $anio,          $mes,               $user_web,            $id_cliente,            $val_tcambio, 
                            '$hora'    
                            ) ";
        $oIfx->QueryT($sql_minv);

        //UPDATE AL SECUENCIAL SAEDEFI
        $sql_update = "UPDATE SAEDEFI SET DEFI_TRS_DEFI = '$secu_minv_real' WHERE
                            DEFI_COD_EMPR = $idempresa AND
                            DEFI_COD_SUCU = $idsucursal and
                            defi_cod_modu = 10 and
                            defi_tip_defi = '0' and
                            defi_cod_tran = '$para_ncr_trans' ";
        $oIfx->QueryT($sql_update);

        //SERIAL DEL SAEDMIV
        $serial_minv = 0;
        $sql_serial = "select minv_num_comp from saeminv where
                            minv_fac_prov = '$ncre_num' and
                            minv_cod_empr = $idempresa and
                            minv_cod_sucu = $idsucursal and
                            minv_cod_tran= '$para_ncr_trans' and
                            minv_num_sec = '$secu_minv_real' ";
        $serial_minv = consulta_string_func($sql_serial, 'minv_num_comp', $oIfx, 0);

        return $serial_minv;
    }


    function saedmov(
        $minv_cod,
        $dmov_cod,
        $tran_cod,
        $ncre_num,
        $prod_cod,
        $bode_cod,
        $cuenta_inv,
        $unid_cod,
        $cant,
        $costo,
        $lote,
        $fecha_ela,
        $fecha_cad,
        $ccos_cod
    ) {
        $oIfx = $this->oIfx;
        $idempresa = $this->idEmpresa;
        $idsucursal = $this->idSucursal;
        $id_cliente = $this->idClpv;
        $id_Contrato = $this->idContrato;
        $fecha_ncre = $this->fecha_ncre;
        $mone_cod = $this->mone_cod;
        $user_ifx = $this->user_ifx;
        $user_web = $this->user_web;
        $id_ejer = $this->id_ejer;
        $id_prdo = $this->id_prdo;
        $hora = date("Y-m-d H:i:s");

        if (!$fecha_cad) {
            $fecha_cad = 'null';
        } else {
            $fecha_cad = "'$fecha_cad'";
        }

        if (!$fecha_ela) {
            $fecha_ela = 'null';
        } else {
            $fecha_ela = "'$fecha_ela'";
        }

        $sql_dmov = "insert into saedmov(
                            dmov_cod_dmov,      dmov_cod_prod,      dmov_cod_sucu,           dmov_cod_empr,   	dmov_cod_bode,     	dmov_cod_cuen,
                            dmov_cod_unid,   	dmov_cod_ejer,     	dmov_num_comp,           dmov_num_prdo,   	dmov_can_dmov,     	dmov_can_entr,
                            dmov_cun_dmov,   	dmov_cto_dmov,     	dmov_con_dmov,           dmov_pun_dmov,   	dmov_pto_dmov,     	dmov_ds1_dmov,
                            dmov_ds2_dmov,   	dmov_ds3_dmov,     	dmov_ds4_dmov,           dmov_des_tota,   	dmov_imp_dmov,     	dmov_est_dmov,
                            dmov_iva_dmov,   	dmov_ban_lote,     	dmov_cod_tran,           dmov_fac_prov,   	dmov_cod_clpv,     	dmov_fmov,
                            dmov_cod_lote,		dmov_cad_lote,		dmov_ela_lote,           dmov_cod_ccos,     dmov_hor_crea  ) values(
                            $dmov_cod,          '$prod_cod',        $idsucursal,             $idempresa,        $bode_cod,          '$cuenta_inv',
                            $unid_cod,           $id_ejer,          $minv_cod,               $id_prdo,          $cant,              0,
                            $costo,             ($cant*$costo),     0,                       $costo,            ($cant*$costo),     0,
                            0,                  0,                  0,                       0,                 0,                  0,
                            0,                  'N',               '$tran_cod',              '$ncre_num',       $id_cliente,         '$fecha_ncre',
                            '$lote',            $fecha_cad,        $fecha_ela,             '$ccos_cod' ,      '$hora' ) ";
        $oIfx->QueryT($sql_dmov);

        // ID DEL SAECOST
        $sql_id_cost = "select max(cost_cod_cost) as maximo from saecost where
                            cost_cod_prod = '$prod_cod' and
                            cost_cod_empr = $idempresa ";
        $cost_cod_cost = consulta_string_func($sql_id_cost, 'maximo', $oIfx, 0);

        // sctok bodega
        $sql = "select prbo_dis_prod, prbo_uco_prod from saeprbo where
                        prbo_cod_empr = $idempresa and
                        prbo_cod_sucu = $idsucursal and
                        prbo_cod_bode = $bode_cod and
                        prbo_cod_prod = '$prod_cod' ";
        $stock = consulta_string_func($sql, 'prbo_dis_prod', $oIfx, 0);

        // INGRESO SAECOST
        $sql_cost = "insert into saecost(
                            cost_cod_cost,        cost_cod_prod,      cost_num_comp,               cost_cod_dmov,        cost_cod_bode,      cost_cod_sucu,
                            cost_cod_empr,        cost_num_prdo,      cost_cod_ejer,               cost_fec_cost,        cost_can_cost,      cost_val_unit,
                            cost_est_cost,        cost_tip_cost )
                    values(($cost_cod_cost+1),    '$prod_cod',        $minv_cod,                   $dmov_cod,            $bode_cod,           $idsucursal,
                            $idempresa,           $id_prdo,           $id_ejer,                   '$fecha_ncre',        ($stock + $cant),     $costo,
                            1,                    'I' ) ";
        $oIfx->QueryT($sql_cost);

        $sql = "update saeprbo set prbo_dis_prod = ($stock+$cant) where
                                prbo_cod_empr = $idempresa and
                                prbo_cod_sucu = $idsucursal and
                                prbo_cod_prod = '$prod_cod' and
                                prbo_cod_bode = $bode_cod ";
        $oIfx->QueryT($sql);
    }


    function saedmcc(
        $dmcc_cod_modu,
        $modu_cod_modu,
        $dmcc_cod_tran,
        $dmcc_num_fac,
        $dmcc_det_dmcc,
        $gran_total,
        $estado_fact,
        $ncre_cod,
        $val_tcambio,
        $asto_cod
    ) {
        $oIfx = $this->oIfx;
        $idempresa = $this->idEmpresa;
        $idsucursal = $this->idSucursal;
        $id_cliente = $this->idClpv;
        $id_Contrato = $this->idContrato;
        $fecha_ncre = $this->fecha_ncre;
        $mone_cod = $this->mone_cod;
        $user_ifx = $this->user_ifx;
        $user_web = $this->user_web;
        $id_ejer = $this->id_ejer;
        $id_prdo = $this->id_prdo;
        $cero = 0;
        $gran_total_ext = $gran_total / $val_tcambio;

        if(is_nan($gran_total_ext)){
            $gran_total_ext = 0;
        }

        if(empty($val_tcambio)){
            $val_tcambio = 0;
        }
        
        $sql_id_dmcc = "select max(dmcc_cod_dmcc) as maximo from saedmcc ";
        $id_dmcc = consulta_string($sql_id_dmcc, 'maximo', $oIfx, 0);
        $id_dmcc = $id_dmcc + 1;

        $sql_dmcc = "insert into saedmcc(dmcc_cod_dmcc, dmcc_cod_empr,    dmcc_cod_sucu,     dmcc_cod_ejer,
                                                    dmcc_cod_modu,    modu_cod_modu,     dmcc_cod_mone,
                                                    clpv_cod_clpv,    dmcc_cod_tran,     dmcc_num_fac,
                                                    dmcc_fec_ven,     dmcc_fec_emis,     dmcc_det_dmcc,
                                                    dmcc_mon_ml,      dmcc_mon_ext,      dmcc_est_dmcc,
                                                    dmcc_deb_ml,      dmcc_cre_ml,       dmcc_cod_fact,
                                                    dmcc_val_coti,    dmcc_deb_mext,     dmcc_cre_mext,
                                                    dmcc_mov_sucu,    dmcc_cod_vend,     dmcc_cod_asto )
                                            values ($id_dmcc, $idempresa,       $idsucursal,          $id_ejer,
                                                     $dmcc_cod_modu,   $modu_cod_modu,     $mone_cod,
                                                     $id_cliente,      '$dmcc_cod_tran', '$dmcc_num_fac',
                                                     '$fecha_ncre',    '$fecha_ncre',  '$dmcc_det_dmcc',
                                                     '$gran_total',     '$gran_total_ext',     '$estado_fact',
                                                     $cero,            '$gran_total',      $ncre_cod,
                                                     $val_tcambio,     '$cero',    '$gran_total_ext',
                                                     $idsucursal,     '$user_ifx',     '$asto_cod' ); ";
        $oIfx->QueryT($sql_dmcc);
    }

    // TIDU
    function secu_asto()
    {
        $oIfx = $this->oIfx;
        $idempresa = $this->idEmpresa;
        $idsucursal = $this->idSucursal;
        $id_cliente = $this->idClpv;
        $id_Contrato = $this->idContrato;
        $fecha_ncre = $this->fecha_ncre;
        $mone_cod = $this->mone_cod;
        $user_ifx = $this->user_ifx;
        $user_web = $this->user_web;
        $id_ejer = $this->id_ejer;
        $id_prdo = $this->id_prdo;
        $hora = date("Y-m-d H:i:s");

        // ASIENTO CONTABLE
        // TIDU
        $sql = "select  para_tidu_fact, para_cfv_para , para_otr_para 
                    from saepara where
                    para_cod_empr = $idempresa  and
                    para_cod_sucu = $idsucursal ";
        if ($oIfx->Query($sql)) {
            $tidu = $oIfx->f('para_tidu_fact');
            $cta_fle = $oIfx->f('para_cfv_para');
            $cta_otro = $oIfx->f('para_otr_para');
        }

        // CUENTA DESCUENTO
        $sql = "select  bode_cta_desc  from saebode b, saesubo s where 
                    b.bode_cod_bode = s.subo_cod_bode and
                    s.subo_cod_empr = $idempresa  and
                    s.subo_cod_sucu = $idsucursal and
                    bode_cod_empr   = $idempresa group by 1";
        $cta_desc = consulta_string_func($sql, 'bode_cta_desc', $oIfx, '');

        // SECUENCIAL DEL ASIENTO
        $sql = "select  secu_dia_comp, secu_asi_comp from saesecu where
                    secu_cod_empr = $idempresa and
                    secu_cod_sucu = $idsucursal and
                    secu_cod_tidu = '$tidu' and
                    secu_cod_modu = 7 and
                    secu_cod_ejer = $id_ejer and
                    secu_num_prdo = $id_prdo ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $secu_dia = $oIfx->f('secu_dia_comp');
                $secu_asto = $oIfx->f('secu_asi_comp');
            }
        }
        $oIfx->Free();

        $secu_dia_tmp = substr($secu_dia, 5);
        $secu_asto_tmp = substr($secu_asto, 5);
        $ini_secu_dia = substr($secu_dia, 0, 5);
        $ini_secu_asto = substr($secu_asto, 0, 5);

        $secu_dia = $ini_secu_dia . secuencial(2, '', $secu_dia_tmp, 8);
        $secu_asto = $ini_secu_asto . secuencial(2, '', $secu_asto_tmp, 8);

        // UPDATE SECUENCIA SAESECU
        $sql = "update saesecu set secu_dia_comp = '$secu_dia', 
                            secu_asi_comp = '$secu_asto' where
                            secu_cod_empr = $idempresa and
                            secu_cod_sucu = $idsucursal and
                            secu_cod_tidu = '$tidu' and
                            secu_cod_modu = 7 and
                            secu_cod_ejer = $id_ejer and
                            secu_num_prdo = $id_prdo ";
        $oIfx->QueryT($sql);
        unset($array);
        $array[] = array($secu_asto, $secu_dia, $tidu, $cta_fle, $cta_otro, $cta_desc);
        return $array;
    }


    function asiento_asto($secu_asto, $tran, $clpv_nom, $total_vta, $detalle_asto, $secu_dia, $tidu, $usua_nom, $coti)
    {
        $oIfx = $this->oIfx;
        $idempresa = $this->idEmpresa;
        $idsucursal = $this->idSucursal;
        $id_cliente = $this->idClpv;
        $id_Contrato = $this->idContrato;
        $fecha_ncre = $this->fecha_ncre;
        $mone_cod = $this->mone_cod;
        $user_ifx = $this->user_ifx;
        $user_web = $this->user_web;
        $id_ejer = $this->id_ejer;
        $id_prdo = $this->id_prdo;
        $hora = date("Y-m-d H:i:s");
        // SAEASTO
        $sql = "select ftrn_cod_ftrn from saeftrn where ftrn_cod_modu = 7 and ftrn_cod_empr = $idempresa";
        $formato = consulta_string_func($sql, 'ftrn_cod_ftrn', $oIfx, '');

        $sql = "insert into saeasto (  
                            asto_cod_asto,       asto_cod_empr,      asto_cod_sucu,      asto_cod_ejer,    asto_num_prdo,       asto_cod_mone,      asto_cod_usua,      asto_cod_modu,
                            asto_cod_tdoc,       asto_ben_asto,      asto_vat_asto,      asto_fec_asto,    asto_det_asto,       asto_est_asto,      asto_num_mayo,      asto_fec_emis,
                            asto_tipo_mov,       asto_cot_asto,      asto_for_impr,      asto_cod_tidu,
                            asto_usu_asto,       asto_fec_serv,      asto_user_web,      asto_fec_fina  )
                    values( '$secu_asto',        $idempresa,         $idsucursal,        $id_ejer,         $id_prdo,            $mone_cod,          $user_ifx,          7,
                            '$tran',            '$clpv_nom',         $total_vta,         '$fecha_ncre',   '$detalle_asto',      'PE',               '$secu_dia',        '$fecha_ncre',
                            'DI',                $coti,                  $formato,           '$tidu',         '$usua_nom',            CURRENT_DATE,            $user_web,          '$fecha_ncre'   )";
        $oIfx->QueryT($sql);
        return 'OK';
    }

    function dasi($cuenta, $ccos, $debml, $crml, $debme, $crme, $tip_camb, $det_dasi, $tran, $secu_asto)
    {
        $oIfx = $this->oIfx;
        $idempresa = $this->idEmpresa;
        $idsucursal = $this->idSucursal;
        $id_cliente = $this->idClpv;
        $id_Contrato = $this->idContrato;
        $fecha_ncre = $this->fecha_ncre;
        $mone_cod = $this->mone_cod;
        $user_ifx = $this->user_ifx;
        $user_web = $this->user_web;
        $id_ejer = $this->id_ejer;
        $id_prdo = $this->id_prdo;

        $sql = "select  cuen_nom_cuen  from saecuen where
                        cuen_cod_empr = $idempresa and
                        cuen_cod_cuen = '$cuenta' ";
        $cuen_prod_nom = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');

        $sql = "insert into saedasi (
                            asto_cod_asto,        asto_cod_empr,      asto_cod_sucu,       dasi_num_prdo,       asto_cod_ejer,       dasi_cod_cuen,      ccos_cod_ccos,       dasi_dml_dasi,      
                            dasi_cml_dasi,       dasi_dme_dasi,       dasi_cme_dasi,       dasi_tip_camb,       dasi_det_asi,        dasi_nom_ctac,      dasi_cod_clie,       dasi_cod_tran,      
                            dasi_user_web )
                    values( '$secu_asto',         $idempresa,         $idsucursal,         $id_prdo,            $id_ejer,            '$cuenta',          '$ccos',             $debml,      
                            $crml,                $debme,             $crme,               $tip_camb ,         '$det_dasi' ,         '$cuen_prod_nom',    $id_cliente,        '$tran',       
                            $user_web  ); ";
        $oIfx->QueryT($sql);
        return 'OK';
    }
}
