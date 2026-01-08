<?php
require_once(DIR_INCLUDE . 'comun.lib.php');
require_once(DIR_INCLUDE . 'Clases/Contratos.class.php');

class Facturacion
{

    var $oIfx;
    var $idEmpresa;
    var $idSucursal;
    var $idClpv;
    var $idContrato;
    var $idUser;
    var $idUserIfx;

    function __construct($oIfx, $idEmpresa, $idSucursal, $idClpv, $idContrato, $idUser, $idUserIfx)
    {

        global $DSN, $DSN_Ifx;
        session_start();

        $oIfx2 = new Dbo;
        $oIfx2->DSN = $DSN_Ifx;
        $oIfx2->Conectar();

        $this->oIfx = $oIfx2;
        $this->idEmpresa = $idEmpresa;
        $this->idSucursal = $idSucursal;
        $this->idClpv = $idClpv;
        $this->idContrato = $idContrato;
        $this->idUser = $idUser;
        $this->idUserIfx = $idUserIfx;
    }

    function parametrosFacturacion()
    {

        $oIfx = $this->oIfx;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idUserIfx = $this->idUserIfx;

        $fecha_servidor = date("Y-m-d");

        $sql = "SELECT para_ite_fact, para_fac_cxc, para_pro_bach, COALESCE(para_sec_usu,'N') as para_sec_usu, para_punt_emi
				from saepara where
				para_cod_empr = $idEmpresa and
				para_cod_sucu = $idSucursal ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $para_ite_fact = $oIfx->f('para_ite_fact');
                $secu_ctrl = $oIfx->f('para_sec_usu');
                $mayo_sn = $oIfx->f('para_pro_bach');
                $tran = $oIfx->f('para_fac_cxc');
                $para_punt_emi = $oIfx->f('para_punt_emi');
            }
        }
        $oIfx->Free();

        $sql_moneda = "SELECT pcon_mon_base from saepcon where pcon_cod_empr = $idEmpresa ";
        $moneda = consulta_string($sql_moneda, 'pcon_mon_base', $oIfx, '');

        $sql_tcambio = "SELECT tcam_fec_tcam, tcam_cod_tcam, tcam_val_tcam  from saetcam where
						tcam_cod_mone = $moneda and
						mone_cod_empr = $idEmpresa and
						tcam_fec_tcam = (SELECT max(tcam_fec_tcam) from saetcam where
						tcam_cod_mone = $moneda and
						tcam_fec_tcam <= '$fecha_servidor' and
						mone_cod_empr = $idEmpresa) ";

        if ($oIfx->Query($sql_tcambio)) {
            if ($oIfx->NumFilas() > 0) {
                $tcambio = $oIfx->f('tcam_cod_tcam');
                $val_tcambio = $oIfx->f('tcam_val_tcam');
            } else {
                $tcambio = 0;
                $val_tcambio = 0;
            }
        }
        $oIfx->Free();

        $sql_formato = "SELECT ftrn_cod_ftrn from saeftrn where
						ftrn_cod_empr = $idEmpresa and
						ftrn_cod_modu = 7 and
						ftrn_des_ftrn = 'FACTURA' ";
        $formato = consulta_string_func($sql_formato, 'ftrn_cod_ftrn', $oIfx, 0);

        $vendedor_logeado = '0';
        if(isset($_SESSION['U_VENDEDOR'])){
            $vendedor_logeado = $_SESSION['U_VENDEDOR'];
        }

        //consulta empleado y vendedor
        $sql2 = "SELECT usua_cod_empl, usua_nom_usua, usua_cod_vend 
				 FROM SAEUSUA 
				 WHERE USUA_COD_USUA = $idUserIfx";
        if ($oIfx->Query($sql2)) {
            if ($oIfx->NumFilas() > 0) {
                $empleado = $oIfx->f('usua_cod_empl');
                $nom_usua = $oIfx->f('usua_nom_usua');
                $vendedor = $oIfx->f('usua_cod_vend');
            }
        }
        $oIfx->Free();

        if(!empty($vendedor_logeado)){
            $vendedor = $vendedor_logeado;
        }

        $array = array(
            $para_ite_fact, $tcambio, $moneda, $tcambio, $val_tcambio, $formato, $secu_ctrl, $mayo_sn, $tran, $empleado,
            $nom_usua, $vendedor, $para_punt_emi
        );


        return $array;
    }

    function secuencialFacturacion($secu_ctrl, $campoPara, $fechaFactura, $para_punt_emi, $tipo_mifa)
    {

        $oIfx = $this->oIfx;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idUserIfx = $this->idUserIfx;

        $opcion_tmp = 0;
        $fecha_servidor = date("Y-m-d");

        if($para_punt_emi == 'S'){

            $opcion_tmp = $tipo_mifa;

            $sql = "SELECT emifa_sec_doc as secuencial, 
                            emifa_num_dig as num_digitos, 
                            emifa_cod_pto as serie, 
                            emifa_auto_emifa as autorizacion,
                            emifa_fec_fin as fecha_final,
                            emifa_cod_estab as establecimiento
                    from saeemifa where
                    emifa_cod_emifa = $tipo_mifa";
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                    $secuencial = $oIfx->f('secuencial');
                    $num_digitos = $oIfx->f('num_digitos');
                    $secuencial_real = secuencial_func(2, '0', $secuencial, $num_digitos);
                    $serie = $oIfx->f('serie');
                    $autorizacion = $oIfx->f('autorizacion');
                    $fecha_aut = $oIfx->f('fecha_final');
                    $establecimiento = $oIfx->f('establecimiento');
                }
            }
            $oIfx->Free();

            $serie = $establecimiento.$serie;
        }else{
            if ($secu_ctrl == 'N') {
                // secuencial normal
                $opcion_tmp = 2;
                $sql = "select COALESCE($campoPara,'N') as para_sec_fac, para_pre_fact,  COALESCE(para_sec_usu,'N') as para_sec_usu
                        from saepara where
                        para_cod_empr = $idEmpresa and
                        para_cod_sucu = $idSucursal ";
                if ($oIfx->Query($sql)) {
                    if ($oIfx->NumFilas() > 0) {
                        $secuencial = $oIfx->f('para_sec_fac');
                        $ceros = $oIfx->f('para_pre_fact');
                        //secuencial real
                        $secuencial_real = secuencial_func(2, '0', $secuencial, $ceros);
                    }
                }
                $oIfx->Free();
    
                if($campoPara == 'para_sec_nven'){
                    $aufa_nse_fact = "aufa_nse_ndeb";
                }else{
                    $aufa_nse_fact = "aufa_nse_fact";
                }
    
                //autorizaciones sri
                $sql_sri = "SELECT $aufa_nse_fact as aufa_nse_fact, aufa_nau_fact, aufa_ffi_fact  
                            FROM saeaufa
                                WHERE aufa_cod_empr = $idEmpresa and
                                aufa_cod_sucu = $idSucursal and
                                aufa_est_fact = 'A' and
                                aufa_ffi_fact >= '$fechaFactura' and
                                aufa_fin_fact <= '$fechaFactura' ";
                if ($oIfx->Query($sql_sri)) {
                    if ($oIfx->NumFilas() > 0) {
                        $autorizacion = $oIfx->f('aufa_nau_fact');
                        $fecha_aut = $oIfx->f('aufa_ffi_fact');
                        $serie = $oIfx->f('aufa_nse_fact');
                    } else {
                        $autorizacion = '0000000000';
                        $fecha_aut = '00/00/0000';
                        $serie = '000000';
                    }
                }
                $oIfx->Free();
            } elseif ($secu_ctrl == 'S') {
                // secuencial por usuario
                if($campoPara == "para_sec_nven"){
                    $opcion_tmp = 1;
                    $sql = "select  usec_cod_usua, usec_nse_fact, usec_nau_fact,
                                    usec_fec_fact,  usec_isec_fact, 
                                    usec_sec_inif,  usec_sec_finf,  usec_pre_fact, usec_isec_ndeb, usec_nau_ndeb, usec_nse_ndeb, usec_fec_ndeb
                                    from saeusec where
                                    usec_cod_empr = $idEmpresa and
                                    usec_cod_sucu = $idSucursal and
                                    usec_cod_usua = $idUserIfx and
                                    usec_est_fact = 'S' ";
                    if ($oIfx->Query($sql)) {
                        if ($oIfx->NumFilas() > 0) {
                            $secuencial = $oIfx->f('usec_isec_ndeb');
                            $ceros = $oIfx->f('usec_pre_fact');
                            $autorizacion = $oIfx->f('usec_nau_ndeb');
                            $fecha_aut = $oIfx->f('usec_fec_ndeb');
                            $serie = $oIfx->f('usec_nse_ndeb');
                            //secuencial real
                            $secuencial_real = secuencial_func(2, '0', $secuencial, $ceros);
                        }
                    }  
                }else{
                    $opcion_tmp = 1;
                    $sql = "select  usec_cod_usua, usec_nse_fact, usec_nau_fact,
                                    usec_fec_fact,  usec_isec_fact, 
                                    usec_sec_inif,  usec_sec_finf,  usec_pre_fact, usec_isec_ndeb, usec_nau_ndeb, usec_nse_ndeb, usec_fec_fact
                                    from saeusec where
                                    usec_cod_empr = $idEmpresa and
                                    usec_cod_sucu = $idSucursal and
                                    usec_cod_usua = $idUserIfx and
                                    usec_est_fact = 'S' ";
                    if ($oIfx->Query($sql)) {
                        if ($oIfx->NumFilas() > 0) {
                            $secuencial = $oIfx->f('usec_isec_fact');
                            $ceros = $oIfx->f('usec_pre_fact');
                            $autorizacion = $oIfx->f('usec_nau_fact');
                            $fecha_aut = $oIfx->f('usec_fec_fact');
                            $serie = $oIfx->f('usec_nse_fact');
                            //secuencial real
                            $secuencial_real = secuencial_func(2, '0', $secuencial, $ceros);
                        }
                    }
                }
                
            }  // fin if
        }
      

        unset($array);
        $array = array($secuencial_real, $autorizacion, $fecha_aut, $serie, $opcion_tmp);

        return $array;
    }

    function controlFacturacion($secuencial, $serie)
    {

        $oIfx = $this->oIfx;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;

        $sql = "select count(*) as control
				from saefact
				where fact_cod_empr = $idEmpresa and
				fact_cod_sucu = $idSucursal and
				fact_num_preimp = '$secuencial' and
				fact_nse_fact = '$serie'";
        $control = consulta_string_func($sql, 'control', $oIfx, 0);

        return $control;
    }

    function controlFacturacion2($secuencial, $serie, $id_fact)
    {

        $oIfx = $this->oIfx;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;

        $sql = "select count(*) as control
				from saefact
				where fact_cod_empr = $idEmpresa and
				fact_cod_sucu = $idSucursal and
				fact_num_preimp = '$secuencial' and
				fact_nse_fact = '$serie' and
                fact_cod_fact not in ($id_fact)";
        $control = consulta_string_func($sql, 'control', $oIfx, 0);

        return $control;
    }

    function cabeceraFacturacion(
        $oIfx,
        $moneda,
        $tcambio,
        $vendedor,
        $empleado,
        $formato,
        $fecha_cotizacion,
        $fecha_vencimiento,
        $idprdo,
        $idejer,
        $secuencial_real,
        $nombre_cliente,
        $telefono,
        $iva_total,
        $con_iva,
        $sin_iva,
        $fecha_servidor,
        $estado_fact,
        $precio,
        $descuento,
        $desc_valor,
        $flete,
        $otros,
        $fact_tot_fact,
        $ruc,
        $direccion,
        $observaciones,
        $fact_cm2_fact,
        $fact_cm3_fact,
        $fact_cm4_fact,
        $dias,
        $sucursal_cliente,
        $tran,
        $serie,
        $autorizacion,
        $fecha_aut,
        $anticipo,
        $subcliente,
        $ice_total,
        $hora_inicio,
        $hora_final,
        $val_tcambio,
        $fact_email_clpv,
        $tipo_comprobante,
        $asto_cod,
        $irbp_total,
        $valorPagoEfeLocal,
        $valorCambioEfeLocal,
        $secuencialOk,
        $codigo,
        $deuda_anterior,
        $fact_con_clv  ='',
        $valorPagoEfeEx=0,
        $valorCambioEfeEx=0,
        $valor_detraccion=0 
    ) {

        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;
        $idUser = $this->idUser;
        $idUserIfx = $this->idUserIfx;
        if(!$valorPagoEfeEx){
            $valorPagoEfeEx = 0;
        }
        if(!$valorCambioEfeEx){
            $valorCambioEfeEx = 0;
        }

        //VALIDACION POSTGRESS
        if(strlen($subcliente)==0){
            $subcliente=0;
        }

        $fecha_pg = "";

        $data_fecha = explode("-",$fecha_cotizacion);

        $dia = $data_fecha[1];
        $mes = $data_fecha[0];
        $anio = $data_fecha[2];

        $fecha_pg = $anio."-".$mes."-".$dia;

        $fecha_pg_v = "";

        $data_fecha_v = explode("-",$fecha_vencimiento);

        $dia_v = $data_fecha_v[1];
        $mes_v = $data_fecha_v[0];
        $anio_v = $data_fecha_v[2];

        $fecha_pg_v = $anio_v."-".$mes_v."-".$dia_v;

        global $DSN;

        $oCon = new Dbo;
        $oCon->DSN = $DSN;
        $oCon->Conectar();

        if($idContrato > 0){
            $sql = "SELECT vendedor from isp.contrato_clpv WHERE id = $idContrato";
            $vendedor_contrato = consulta_string_func($sql, 'vendedor', $oCon, 0);
            
            if(!empty($vendedor_contrato)){
                $vendedor = $vendedor_contrato;
            }
        }

        $sql = "SELECT campo_rete_fact
                from isp.int_parametros_general
                where id_empresa = $idEmpresa";
        $campo_rete_fact = consulta_string_func($sql, 'campo_rete_fact', $oIfx, '');

        $valor_detra = 0;
        if($campo_rete_fact == 'S'){
            if($valor_detraccion != 0 && $valor_detraccion != ''){
                $sql_1 = "SELECT tret_porct FROM saetret WHERE tret_cod = '$valor_detraccion' and tret_cod_empr = $idEmpresa";
                $tret_porct = consulta_string_func($sql_1, 'tret_porct', $oIfx, '');

                $tret_porct_c = $tret_porct / 100;

                $valor_detra = $fact_tot_fact * $tret_porct_c;
            }
        }

        $fecha_servidor = "'".date("Y-m-d H:i:s")."'";
        // $fecha_servidor = 'NOW()';


        $sql = "insert into saefact(fact_cod_aux,    		fact_cod_sucu,     		fact_cod_empr,
									fact_cod_mone,     		fact_cod_tcam,     		fact_cod_vend,
									fact_cod_clpv,     		fact_cod_empl,     		fact_cod_ftrn,
									fact_fech_fact,    		fact_fech_venc,    		fact_cod_usua,
									fact_num_prdo,     		fact_cod_ejer,     		fact_num_preimp,
									fact_nom_cliente,  		fact_tlf_cliente,  		fact_iva,
									fact_con_miva,     		fact_sin_miva,    		fact_fec_servidor, 		
									fact_est_fact,     		fact_prc_fact,			fact_dsg_porc,     		
									fact_dsg_valo,     		fact_fle_fact,			fact_otr_fact,     		
									fact_fin_fact,     		fact_tot_fact,			fact_ruc_clie,     		
									fact_dir_clie,     		fact_cm1_fact,			fact_cm2_fact,     		
									fact_cm3_fact,     		fact_cm4_fact,			fact_dia_fact,
									fact_sucu_clpv,			fact_fon_fact,     		fact_nse_fact,     		
									fact_nau_fact,     		fact_fech_aut,			fact_anti_fact,    		
									fact_cod_ccli,			fact_ice,          		fact_tip_llam,     		
									fact_hor_ini,      		fact_hor_fin,			fact_val_tcam,     		
									fact_usu_fact,     		fact_email_clpv,		fact_tip_vent,    		
									fact_val_irbp,			fact_user_web,			fact_cod_asto,
									fact_cod_contr,			fact_cm8_fac,			fact_cm9_fac,
									fact_awb_fact,			fact_cm7_fac,			fact_cm5_fac ,
									fact_con_clv,           fact_ret2m_fact,        fact_retimd_fact,
                                    fact_cod_detra,         fact_mon_rete_ext)
							values(0,                 		$idSucursal,         	$idEmpresa,
									$moneda,           		$tcambio,         		'$vendedor',
									$idClpv,          		'$empleado',       		$formato,
									'$fecha_pg',            '$fecha_pg_v',            $idUserIfx,
									$idprdo,                $idejer,        		'$secuencial_real',
									'$nombre_cliente',      '$telefono',    		$iva_total,
									$con_iva,               $sin_iva ,				$fecha_servidor,      
									'$estado_fact',         $precio,				$descuento,             
									$desc_valor,            $flete,					$otros,                 
									0,              		$fact_tot_fact,			'$ruc',                 
									'$direccion', 			'$observaciones',		'$fact_cm2_fact',       
									'$fact_cm3_fact',  		'$fact_cm4_fact',		'$dias',
									$sucursal_cliente,		'$tran',                '$serie',
									'$autorizacion', 		'$fecha_aut',			$anticipo,              
									'$subcliente',			'$ice_total',           'E',
									'$hora_inicio',   		'$hora_final',			$val_tcambio,
									$idUserIfx ,     		'$fact_email_clpv',		'$tipo_comprobante',  	
									$irbp_total,			$idUser,				'$asto_cod',
									$idContrato,			'$valorPagoEfeLocal',	'$valorCambioEfeLocal',
									'$secuencialOk',		'$codigo',				'$deuda_anterior' ,
								    '$fact_con_clv',         $valorPagoEfeEx,        $valorCambioEfeEx,
                                    '$valor_detraccion',    $valor_detra)";

                                    //echo $sql;exit;
        $oIfx->QueryT($sql);

        //codigo serial del saefact
        $sql = "select max(fact_cod_fact) as fact_cod_fact 
				from saefact where
				fact_num_preimp = '$secuencial_real' and
				fact_cod_empr = $idEmpresa and
				fact_cod_sucu = $idSucursal and 
				fact_cod_clpv = $idClpv and 
				fact_est_fact = '$estado_fact'";
        $fact_cod_fact = consulta_string_func($sql, 'fact_cod_fact', $oIfx, 0);

        return $fact_cod_fact;
    }

    function detalleFacturacion(
        $oIfx,
        $oCon,
        $idunidad,
        $fact_cod_fact,
        $idproducto,
        $bodega_id,
        $idprdo,
        $idejer,
        $cantidad,
        $valor,
        $subtotal,
        $iva,
        $descuento1,
        $prod_cod_linp,
        $margen_utilidad,
        $ultimo_costo,
        $vendedor,
        $fecha_cotizacion,
        $prod_cod_grpr,
        $prod_cod_cate,
        $prod_cod_marc,
        $prod_ice,
        $dfac_det_dfac,
        $prod_nom_prod,
        $serial_minv,
        $dfac_ord_dfac,
        $dfac_cod_pedf,
        $dfac_cod_ccos,
        $idCuota
    ) {

        $cod_pais = $_SESSION['S_PAIS_API_SRI'];

        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv = $this->idClpv;
        $cero = 0;

        //CONTROL PARA POSTGRESS
        if(strlen($ultimo_costo)==0){
            $ultimo_costo = 0;
        }
        if(strlen($prod_ice)==0){
            $prod_ice = 0;
        }
        if(strlen($dfac_ord_dfac)==0){
            $dfac_ord_dfac = 0;
        }
        if(strlen($dfac_cod_pedf)==0){
            $dfac_cod_pedf = 0;
        }

        $fecha_pg = "";

        $data_fecha = explode("-",$fecha_cotizacion);

        $dia = $data_fecha[1];
        $mes = $data_fecha[0];
        $anio = $data_fecha[2];

        $fecha_pg = $anio."-".$mes."-".$dia;

        if($cod_pais == '51'){
            $dfac_obj_iva = 0;
            $dfac_exc_iva = 0;

            $sql = "SELECT prod_sn_noi, prod_sn_exe from saeprod where prod_cod_prod = '$idproducto' and prod_cod_empr = $idEmpresa";
            $prod_sn_noi = consulta_string_func($sql, 'prod_sn_noi', $oIfx, '');
            $prod_sn_exe = consulta_string_func($sql, 'prod_sn_exe', $oIfx, '');

            if($prod_sn_noi == 'S'){
                $dfac_obj_iva = 1;
            }

            if($prod_sn_exe == 'S'){
                $dfac_exc_iva = 1;
            }            
        }else{
            $prod_no_obj_igv = 'N';
            $prod_exe_igv = 'N';
            $dfac_obj_iva = 0;
            $dfac_exc_iva = 0;
        }

        $sql = "SELECT prbo_cco_prbo from saeprbo where prbo_cod_prod = '$idproducto' and prbo_cod_empr = $idEmpresa and prbo_cod_bode = $bodega_id";
        $dfac_cod_ccos = consulta_string_func($sql, 'prbo_cco_prbo', $oIfx, '');

        $sql = "insert into saedfac(dfac_cod_sucu,    		dfac_cod_empr,    		dfac_cod_unid,
									dfac_cod_fact,    		dfac_cod_prod,    		dfac_cod_bode,
									dfac_num_prdo,    		dfac_cod_ejer,    		dfac_cant_dfac,
									dfac_precio_dfac, 		dfac_mont_total,  		dfac_des1_dfac,
									dfac_des2_dfac,   		dfac_des3_dfac,   		dfac_des4_dfac,
									dfac_tot_des,     		dfac_tot_imp,     		dfac_gui_dfac,
									dfac_cos_uni,     		dfac_por_iva,     		dfac_por_dsg,
									dfac_cod_linp,    		dfac_mar_uti,     		dfac_prod_cost,
									dfac_dev_ncre,    		dfac_sal_ncre,			dfac_cod_vend,    		
									dfac_cod_clpv,    		dfac_fech_emi,			dfac_cod_grpr,    		
									dfac_cod_cate,    		dfac_cod_marc,			dfac_por_ice,     		
									dfac_det_dfac,   		dfac_nom_prod,			dfac_tip_dfac,    		
									dfac_por_irbp,    		dfac_ord_dfac,			dfac_cod_pedf,    		
									dfac_cod_ccos,    		dfac_cod_mes,           dfac_obj_iva,
                                    dfac_exc_iva)
							values($idSucursal,				$idEmpresa, 			$idunidad,
									$fact_cod_fact, 		'$idproducto', 			$bodega_id, 
									$idprdo,				$idejer, 				$cantidad, 
									$valor, 				$subtotal,				$descuento1,
									$cero, 					$cero, 					$cero, 					
									$cero,					$cero, 					$cero, 					
									$cero, 					$iva, 					$cero, 					
									$prod_cod_linp, 		'$margen_utilidad', 	'$ultimo_costo',			
									$cero, 					$cantidad, 				'$vendedor', 				
									$idClpv,				'$fecha_pg', 	        $prod_cod_grpr, 		
									$prod_cod_cate, 		$prod_cod_marc,			'$prod_ice',			
									'$dfac_det_dfac', 		'$prod_nom_prod', 		$serial_minv, 			
									$cero,					'$dfac_ord_dfac', 		'$dfac_cod_pedf', 		
									'$dfac_cod_ccos', 		$idCuota,               $dfac_obj_iva,
                                    $dfac_exc_iva)";
        $oIfx->QueryT($sql);

        //update id factura = cuota
        if($idCuota > 0){
            $sql = "UPDATE isp.contrato_pago SET id_factura = $idCuota, facturado = 'S' WHERE id = $idCuota";
            $oCon->QueryT($sql);
        }

        return 'OK';
    }

    function actualizarSecuencial($oIfx, $opcion, $secuencial_real, $campoPara, $campoUsec, $campoUsec_1)
    {

        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idUserIfx = $this->idUserIfx;

        $sql = "SELECT para_punt_emi from saepara WHERE para_cod_empr = $idEmpresa AND para_cod_sucu = $idSucursal";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $para_punt_emi = $oIfx->f('para_punt_emi');
            }
        }  

        if($para_punt_emi == 'S'){

            $sql = "SELECT emifa_sec_doc from saeemifa WHERE emifa_cod_emifa = $opcion";
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                    $emifa_sec_doc = $oIfx->f('emifa_sec_doc');
                }
            }  

            $secu_mas = $emifa_sec_doc + 1;

            $sql_update_secuencial = "UPDATE saeemifa set emifa_sec_doc = '$secu_mas' where
                                        emifa_cod_empr = $idEmpresa and
                                        emifa_cod_emifa = $opcion";
            $oIfx->QueryT($sql_update_secuencial);
        }else{
            if ($opcion == 1) {
                $secuencial = $secuencial_real + 1 - 1;
                $sql_update_secuencial = "update saeusec set $campoUsec = $secuencial where
                                        usec_cod_empr = $idEmpresa and
                                        usec_cod_sucu = $idSucursal and
                                        usec_cod_usua = $idUserIfx and
                                        $campoUsec_1 = 'S' ";
                $oIfx->QueryT($sql_update_secuencial);
            } elseif ($opcion == 2) {
    
                $sql_update_secuencial = "update saepara set $campoPara = '$secuencial_real' where
                                            para_cod_empr = $idEmpresa and
                                            para_cod_sucu = $idSucursal";
                $oIfx->QueryT($sql_update_secuencial);
            }
        }
    }

    function cuotaFacturacion($oCon, $idPago, $fact_cod_fact, $valorCuota, $deuda_anterior, $descuento = 0)
    {

        $oIfx = $this->oIfx;
        $idEmpresa = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idUser = $this->idUser;
        $idClpv = $this->idClpv;
        $idContrato = $this->idContrato;


        //consulta tipo de comprobante
        $sql = "SELECT fact_tip_vent FROM saefact WHERE fact_cod_fact = $fact_cod_fact";
        $fact_tip_vent = consulta_string_func($sql, 'fact_tip_vent', $oIfx, '');

        if ($fact_tip_vent == '99') {
            $tipo = 'F';
        }else{
            $tipo = 'FC';
        }

        //valor de pago
        $sql = "select valor_pago, (tarifa + tot_add - valor_pago - valor_no_uso) as tarifa 
		  from isp.contrato_pago 
		  where id_contrato = $idContrato and
		  id_clpv = $idClpv and
		  id = $idPago";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $valor_pago = $oCon->f('valor_pago');
                $tarifa = $oCon->f('tarifa');
            }
        }
        $oCon->Free();

        //actualiza tabla de pagos
        $totalPago = round($valor_pago + $valorCuota, 2);



        if($descuento > 0){
            $descuento = $descuento * -1; //pasa a negativo el descuento
        }

        $sqlPago = '';
        $sqlPago = "UPDATE isp.contrato_pago set valor_pago = $totalPago, descuento = $descuento";
        if ($tarifa == $totalPago) {
            $sqlPago .= ", estado_fact = 'GR'";
        }
        $sqlPago .= " where id_contrato = $idContrato and
				  id_clpv = $idClpv and
				  id = $idPago";
        $oCon->QueryT($sqlPago);

        //consulta deuda anterior
        $Contratos = new Contratos($oCon, null, $idEmpresa, $idSucursal, $idClpv, $idContrato);
        $deuda_actual = $Contratos->consultaMontoMesAdeuda();

        //consula monto de pago y fecha de ultimo pago
        $sql = "SELECT monto_pago, fecha_c_pago FROM isp.contrato_clpv WHERE id = $idContrato";
        if($oCon->Query($sql)){
            if($oCon->NumFilas() > 0){
                $monto_pago = $oCon->f('monto_pago');
                $fecha_c_pago = $oCon->f('fecha_c_pago');
            }
        }
        $oCon->Free();

        if(strlen($fecha_c_pago)==0){
            $fecha_c_pago = date("Y-m-d H:i:s");
            // $fecha_c_pago = "NOW()";
        }

        $fecha_servidor_this = "'".date("Y-m-d H:i:s")."'";
        // $fecha_servidor_this = "now()";
        //insert contrato factura
        $sql = "insert into isp.contrato_factura(id_empresa, id_sucursal, id_contrato, id_clpv, id_pago, id_factura,
								valor, deuda_anterior, deuda_actual, estado, user_web, fecha_server, tipo, tipo_tcmp,
								monto_pago, fecha_u_pago)
						values($idEmpresa, $idSucursal, $idContrato, $idClpv, $idPago, $fact_cod_fact,
								'$valorCuota', '$deuda_anterior', '$deuda_actual', 'A', $idUser, $fecha_servidor_this, '$tipo', '$fact_tip_vent',
								'$monto_pago', '$fecha_c_pago')";
        $oCon->QueryT($sql);

        $Contratos->recalcularCuotaValores($idPago);

    }
}
