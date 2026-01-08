<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class Facturacion{
	
	var $oConexion; 
	
	function __construct(){
		//$this->empr_nom_empr = $empr_nom_empr; 		
    } 
	
	function parametrosFacturacion($oConexion, $idEmpresa, $idSucursal, $idUserIfx){
		
		$fecha_servidor = date("m-d-Y");
		
		$sql_item = "select para_ite_fact from saepara where para_cod_empr = $idEmpresa and para_cod_sucu = $idSucursal";
		$para_ite_fact = consulta_string($sql_item, 'para_ite_fact', $oConexion, 100);
		
		 //consulta tipo comprobante
        $sql = "select tcmp_cod_tcmp from saetcmp where tcmp_ven_tcmp = 'S' and tcmp_apl_fact = 'S'";
        $tcmp_cod_tcmp = consulta_string($sql, 'tcmp_cod_tcmp', $oConexion, 0);
		
		$sql = "select para_ite_fact, para_fac_cxc, para_pro_bach, COALESCE(para_sec_usu,'N') as para_sec_usu
				from saepara where
				para_cod_empr = $idEmpresa and
				para_cod_sucu = $idSucursal ";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				$para_ite_fact = $oConexion->f('para_ite_fact');
				$secu_ctrl = $oConexion->f('para_sec_usu');
				$mayo_sn = $oConexion->f('para_pro_bach');
				$tran = $oConexion->f('para_fac_cxc');
			}
		}
		$oConexion->Free();
		
		$sql_moneda = "select pcon_mon_base from saepcon where pcon_cod_empr = $idEmpresa ";
		$moneda = consulta_string($sql_moneda, 'pcon_mon_base', $oConexion, '');
		
		$sql_tcambio = "select tcam_fec_tcam, tcam_cod_tcam, tcam_val_tcam from saetcam where
						tcam_cod_mone = $moneda and
						mone_cod_empr = $idEmpresa and
						tcam_fec_tcam = (select max(tcam_fec_tcam) from saetcam where
						tcam_cod_mone = $moneda and
						tcam_fec_tcam <= '$fecha_servidor' and
						mone_cod_empr = $idEmpresa) ";
		if ($oConexion->Query($sql_tcambio)) {
			if ($oConexion->NumFilas() > 0) {
				$tcambio = $oConexion->f('tcam_cod_tcam');
				$val_tcambio = $oConexion->f('tcam_val_tcam');
			} else {
				$tcambio = 0;
				$val_tcambio = 0;
			}
		}
		$oConexion->Free();
		
		$sql_formato = "select ftrn_cod_ftrn from saeftrn where
						ftrn_cod_empr = $idEmpresa and
						ftrn_cod_modu = 7 and
						ftrn_des_ftrn = 'FACTURA' ";
		$formato = consulta_string($sql_formato, 'ftrn_cod_ftrn', $oConexion, 0);
		
		//consulta empleado y vendedor
        $sql2 = "SELECT usua_cod_empl, usua_nom_usua, usua_cod_vend 
				 FROM SAEUSUA 
				 WHERE USUA_COD_USUA = $idUserIfx";
        if ($oConexion->Query($sql2)) {
            if ($oConexion->NumFilas() > 0) {
                $empleado = $oConexion->f('usua_cod_empl');
                $nom_usua = $oConexion->f('usua_nom_usua');
                $vendedor = $oConexion->f('usua_cod_vend');
            }
        }
        $oConexion->Free();
		
		
		$array[] = array($para_ite_fact, $tcmp_cod_tcmp, $moneda, $tcambio, $val_tcambio, $formato, $secu_ctrl, $mayo_sn, $tran, $empleado,
						$nom_usua, $vendedor);

		
		return $array;
	}
	
	function secuencialFacturacion($oConexion, $idEmpresa, $idSucursal, $secu_ctrl){
		$opcion_tmp = 0;
		if ($secu_ctrl == 'N') {
			// secuencial normal
			$opcion_tmp = 2;
			$sql = "select COALESCE(para_sec_fac,0) as para_sec_fac, para_pre_fact,  COALESCE(para_sec_usu,'N') as para_sec_usu
					from saepara where
					para_cod_empr = $idEmpresa and
					para_cod_sucu = $idSucursal ";
			if ($oConexion->Query($sql)) {
				if ($oConexion->NumFilas() > 0) {
					$secuencial = $oConexion->f('para_sec_fac');
					$ceros = $oConexion->f('para_pre_fact');
					//secuencial real
					$secuencial_real = secuencial_func(2, '0', $secuencial, $ceros);
				}
			}
			$oConexion->Free();

			// autorizaciones sri
			$sql_sri = "SELECT aufa_nse_fact, aufa_nau_fact, aufa_ffi_fact  
						FROM saeaufa
							WHERE aufa_cod_empr = $idEmpresa and
							aufa_cod_sucu = $idSucursal and
							aufa_est_fact = 'A' and
							aufa_ffi_fact >= '$fecha_servidor' and
							aufa_fin_fact <= '$fecha_servidor' ";
			if ($oConexion->Query($sql_sri)) {
				if ($oConexion->NumFilas() > 0) {
					$autorizacion = $oConexion->f('aufa_nau_fact');
					$fecha_aut = $oConexion->f('aufa_ffi_fact');
					$serie = $oConexion->f('aufa_nse_fact');
				} else {
					$autorizacion = '0000000000';
					$fecha_aut = '00/00/0000';
					$serie = '000000';
				}
			}
			$oConexion->Free();
			
		} elseif ($secu_ctrl == 'S') {
			// secuencial por usuario
			$opcion_tmp = 1;
			$sql = "select  usec_cod_usua, usec_nse_fact, usec_nau_fact,
							usec_fec_fact,  usec_isec_fact,     usec_sec_inif,  usec_sec_finf,  usec_pre_fact
							from saeusec where
							usec_cod_empr = $idEmpresa and
							usec_cod_sucu = $idSucursal and
							usec_cod_usua = $usuario_informix and
							usec_est_fact = 'S' ";
			if ($oConexion->Query($sql)) {
				if ($oConexion->NumFilas() > 0) {
					$secuencial = $oConexion->f('usec_isec_fact');
					$ceros = $oConexion->f('usec_pre_fact');
					$autorizacion = $oConexion->f('usec_nau_fact');
					$fecha_aut = $oConexion->f('usec_fec_fact');
					//secuencial real
					$secuencial_real = secuencial_func(2, '0', $secuencial, $ceros);
				}
			}
		}  // fin if
		
		
		$array[] = array($secuencial_real, $autorizacion, $fecha_aut, $serie, $opcion_tmp);

		
		return $array;
	}
	
	function controlFacturacion($oConexion, $idEmpresa, $idSucursal, $secuencial, $serie){
		
        $sql = "select count(*) as control
				from saefact
				where fact_cod_empr = $idEmpresa and
				fact_cod_sucu = $idSucursal and
				fact_est_fact != 'AN' and
				fact_num_preimp = '$secuencial' and
				fact_nse_fact = '$serie'";
        $control = consulta_string($sql, 'control', $oConexion, 0);
		
		return $control;
	}
	// FACTURA DE EXPORTACION
	function cabeceraFacturacionExp($oConexion, $idEmpresa, $idSucursal, $moneda,
								$tcambio, $vendedor, $cliente, $empleado,
								$formato, $fecha_cotizacion, $fecha_vencimiento, $usuario_informix,
								$idprdo, $idejer, $secuencial_real, $nombre_cliente,
								$telefono, $iva_total, $con_iva, $sin_iva, 
								$fecha_servidor, $estado_fact, $precio, $descuento,
								$desc_valor, $flete, $otros, $fact_tot_fact,
								$ruc, $direccion, $observaciones, $fact_cm2_fact,
								$fact_cm3_fact, $fact_cm4_fact, $dias, $sucursal_cliente,
								$tran, $envio, $transporte, $serie, 
								$autorizacion, $fecha_aut, $anticipo, $subcliente, 
								$ice_total, $hora_inicio, $hora_final, $val_tcambio,
								$usuario_informix, $fact_email_clpv, $tipo_comprobante, $fact_cod_ndb,
								$usuario_web, $asto_cod){
		
		$sql = "insert into saefact(fact_cod_aux,    		fact_cod_sucu,     		fact_cod_empr,
									fact_cod_mone,     		fact_cod_tcam,     		fact_cod_vend,
									fact_cod_clpv,     		fact_cod_empl,     		fact_cod_ftrn,
									fact_fech_fact,    		fact_fech_venc,    		fact_cod_usua,
									fact_num_prdo,     		fact_cod_ejer,     		fact_num_preimp,
									fact_nom_cliente,  		fact_tlf_cliente,  		fact_iva,
									fact_con_miva,     		fact_sin_miva,    		fact_mon_rete_ext,
									fact_fec_servidor, 		fact_est_fact,     		fact_prc_fact,
									fact_dsg_porc,     		fact_dsg_valo,     		fact_fle_fact,
									fact_otr_fact,     		fact_fin_fact,     		fact_tot_fact,
									fact_ruc_clie,     		fact_dir_clie,     		fact_cm1_fact,
									fact_cm2_fact,     		fact_cm3_fact,     		fact_cm4_fact,
									fact_dia_fact,     		fact_pri_fact,     		fact_sucu_clpv,
									fact_fon_fact,     		fact_cod_eden,     		fact_des_tras,
									fact_nse_fact,     		fact_nau_fact,     		fact_fech_aut,
									fact_anti_fact,    		fact_cod_ccli,			fact_ice,          		
									fact_ent_empr,     		fact_est_enem,			fact_tip_llam,     		
									fact_hor_ini,      		fact_hor_fin,			fact_val_tcam,     		
									fact_usu_fact,     		fact_email_clpv,		fact_tip_vent,    		
									fact_cod_ndb,      		fact_val_irbp,			fact_user_web,
									fact_cod_asto)
							values(0,                 		$idSucursal,         	$idEmpresa,
									$moneda,           		$tcambio,         		'$vendedor',
									$cliente,          		'$empleado',       		$formato,
									'$fecha_cotizacion',    '$fecha_vencimiento',   $usuario_informix,
									$idprdo,                $idejer,        		'$secuencial_real',
									'$nombre_cliente',      '$telefono',    		$iva_total,
									$con_iva,               $sin_iva ,      		0,
									'$fecha_servidor',      '$estado_fact',         $precio,
									$descuento,             $desc_valor,            $flete,
									$otros,                 0,              		$fact_tot_fact,
									'$ruc',                 '$direccion', 			'$observaciones',
									'$fact_cm2_fact',       '$fact_cm3_fact',  		'$fact_cm4_fact',
									'$dias',                'N',            		$sucursal_cliente,
									'$tran',                '$envio',       		'$transporte',
									'$serie',               '$autorizacion', 		'$fecha_aut',
									$anticipo,              '$subcliente',			'$ice_total',              	
									'N',             		'P',					'E',                 	
									'$hora_inicio',   		'$hora_final',			$val_tcambio,
									$usuario_informix ,     '$fact_email_clpv',		'$tipo_comprobante',  	
									'$fact_cod_ndb',  		0,						$usuario_web,
									'$asto_cod')";
		$oConexion->QueryT($sql);
		
		//codigo serial del saefact
		$sql = "select fact_cod_fact from saefact where
				fact_num_preimp = '$secuencial_real' and
				fact_cod_empr = $idEmpresa and
				fact_cod_sucu = $idSucursal and 
				fact_cod_clpv = $cliente and 
				fact_est_fact = '$estado_fact'";
		$fact_cod_fact = consulta_string($sql, 'fact_cod_fact', $oConexion, 0);
		
		return $fact_cod_fact;
					
	}

	
	function cabeceraFacturacion($oConexion, $idEmpresa, $idSucursal, $moneda,
								$tcambio, $vendedor, $cliente, $empleado,
								$formato, $fecha_cotizacion, $fecha_vencimiento, $usuario_informix,
								$idprdo, $idejer, $secuencial_real, $nombre_cliente,
								$telefono, $iva_total, $con_iva, $sin_iva, 
								$fecha_servidor, $estado_fact, $precio, $descuento,
								$desc_valor, $flete, $otros, $fact_tot_fact,
								$ruc, $direccion, $observaciones, $fact_cm2_fact,
								$fact_cm3_fact, $fact_cm4_fact, $dias, $sucursal_cliente,
								$tran, $envio, $transporte, $serie, 
								$autorizacion, $fecha_aut, $anticipo, $subcliente, 
								$ice_total, $hora_inicio, $hora_final, $val_tcambio,
								$usuario_informix, $fact_email_clpv, $tipo_comprobante, $fact_cod_ndb,
								$usuario_web, $asto_cod){
		
		$sql = "insert into saefact(fact_cod_aux,    		fact_cod_sucu,     		fact_cod_empr,
									fact_cod_mone,     		fact_cod_tcam,     		fact_cod_vend,
									fact_cod_clpv,     		fact_cod_empl,     		fact_cod_ftrn,
									fact_fech_fact,    		fact_fech_venc,    		fact_cod_usua,
									fact_num_prdo,     		fact_cod_ejer,     		fact_num_preimp,
									fact_nom_cliente,  		fact_tlf_cliente,  		fact_iva,
									fact_con_miva,     		fact_sin_miva,    		fact_mon_rete_ext,
									fact_fec_servidor, 		fact_est_fact,     		fact_prc_fact,
									fact_dsg_porc,     		fact_dsg_valo,     		fact_fle_fact,
									fact_otr_fact,     		fact_fin_fact,     		fact_tot_fact,
									fact_ruc_clie,     		fact_dir_clie,     		fact_cm1_fact,
									fact_cm2_fact,     		fact_cm3_fact,     		fact_cm4_fact,
									fact_dia_fact,     		fact_pri_fact,     		fact_sucu_clpv,
									fact_fon_fact,     		fact_cod_eden,     		fact_des_tras,
									fact_nse_fact,     		fact_nau_fact,     		fact_fech_aut,
									fact_anti_fact,    		fact_cod_ccli,			fact_ice,          		
									fact_ent_empr,     		fact_est_enem,			fact_tip_llam,     		
									fact_hor_ini,      		fact_hor_fin,			fact_val_tcam,     		
									fact_usu_fact,     		fact_email_clpv,		fact_tip_vent,    		
									fact_cod_ndb,      		fact_val_irbp,			fact_user_web,
									fact_cod_asto)
							values(0,                 		$idSucursal,         	$idEmpresa,
									$moneda,           		$tcambio,         		'$vendedor',
									$cliente,          		'$empleado',       		$formato,
									'$fecha_cotizacion',    '$fecha_vencimiento',   $usuario_informix,
									$idprdo,                $idejer,        		'$secuencial_real',
									'$nombre_cliente',      '$telefono',    		$iva_total,
									$con_iva,               $sin_iva ,      		0,
									'$fecha_servidor',      '$estado_fact',         $precio,
									$descuento,             $desc_valor,            $flete,
									$otros,                 0,              		$fact_tot_fact,
									'$ruc',                 '$direccion', 			'$observaciones',
									'$fact_cm2_fact',       '$fact_cm3_fact',  		'$fact_cm4_fact',
									'$dias',                'N',            		$sucursal_cliente,
									'$tran',                '$envio',       		'$transporte',
									'$serie',               '$autorizacion', 		'$fecha_aut',
									$anticipo,              '$subcliente',			'$ice_total',              	
									'N',             		'P',					'E',                 	
									'$hora_inicio',   		'$hora_final',			$val_tcambio,
									$usuario_informix ,     '$fact_email_clpv',		'$tipo_comprobante',  	
									'$fact_cod_ndb',  		0,						$usuario_web,
									'$asto_cod')";
		$oConexion->QueryT($sql);
		
		//codigo serial del saefact
		$sql = "select fact_cod_fact from saefact where
				fact_num_preimp = '$secuencial_real' and
				fact_cod_empr = $idEmpresa and
				fact_cod_sucu = $idSucursal and 
				fact_cod_clpv = $cliente and 
				fact_est_fact = '$estado_fact'";
		$fact_cod_fact = consulta_string($sql, 'fact_cod_fact', $oConexion, 0);
		
		return $fact_cod_fact;
					
	}
	
	function detalleFacturacion($oConexion, $idSucursal, $idEmpresa, $idunidad,
								$fact_cod_fact, $idproducto, $bodega_id, $idprdo,
								$idejer, $cantidad, $valor, $subtotal, $descuento1,
								$cero, $cero, $cero, $cero,
								$cero, $cero, $cero, $iva, 
								$cero, $prod_cod_linp, $margen_utilidad, $ultimo_costo,
								$cero, $cantidad, $vendedor, $cliente,
								$fecha_cotizacion, $prod_cod_grpr, $prod_cod_cate, $prod_cod_marc,
								$prod_ice, $dfac_det_dfac, $prod_nom_prod, $serial_minv, $cero,
								$dfac_ord_dfac, $dfac_cod_pedf, $dfac_cod_ccos, $promo){
		
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
									dfac_cod_ccos,    		dfac_tip_prom)
							values($idSucursal,				$idEmpresa, 			$idunidad,
									$fact_cod_fact, 		'$idproducto', 			$bodega_id, 
									$idprdo,				$idejer, 				$cantidad, 
									$valor, 				$subtotal,				$descuento1,
									$cero, 					$cero, 					$cero, 					
									$cero,					$cero, 					$cero, 					
									$cero, 					$iva, 					$cero, 					
									$prod_cod_linp, 		'$margen_utilidad', 		'$ultimo_costo',			
									$cero, 					$cantidad, 				'$vendedor', 				
									$cliente,				'$fecha_cotizacion', 	$prod_cod_grpr, 		
									$prod_cod_cate, 		$prod_cod_marc,			'$prod_ice',			
									'$dfac_det_dfac', 		'$prod_nom_prod', 		$serial_minv, 			
									$cero,					'$dfac_ord_dfac', 		'$dfac_cod_pedf', 		
									'$dfac_cod_ccos', 		'$promo')";
		$oConexion->QueryT($sql);
		
		return 'OK';
					
	}
	
}
?>