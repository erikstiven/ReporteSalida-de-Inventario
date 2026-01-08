<?php
require_once(DIR_INCLUDE . 'comun.lib.php');

class Facturacion_multicontrato
{

    var $oCon;
    var $id_empresa;
    var $id_sucursal;
    var $id_clpv;
    var $id_user;
    var $id_user_ifx;

    function __construct($oCon, $id_empresa, $id_sucursal, $id_clpv, $id_user, $id_user_ifx)
    {
        $this->oCon = $oCon;
        $this->id_empresa = $id_empresa;
        $this->id_sucursal = $id_sucursal;
        $this->id_clpv = $id_clpv;
        $this->id_user = $id_user;
        $this->id_user_ifx = $id_user_ifx;
    }

    function obtener_id_contratos($array_id_contrato)
    {
        for($c=0;$c<count($array_id_contrato);$c++){
            $data_contrato  = $array_id_contrato[$c];
            $data_contrato  = explode(",",$data_contrato);
            $id_contrato    = $data_contrato[0];
            $id_contratos   .= $id_contrato."-";
        }

        $datos_retorno = substr($id_contratos, 0, strlen($id_contratos) - 1);

        return $datos_retorno;
    }

    function parametros_secuenciales($datos)
    {
        $oCon       = $this->oCon;

        $tipo_emifa = $datos["tipo_emifa"];

        $sql = "SELECT emifa_tip_emifa FROM saeemifa where emifa_cod_emifa = $tipo_emifa";
        $tipo_comprobante = consulta_string($sql, 'emifa_tip_emifa', $oCon, 0);

        $sql = "SELECT emifa_sec_doc as secuencial, 
                        emifa_num_dig as num_digitos, 
                        emifa_cod_pto as serie, 
                        emifa_auto_emifa as autorizacion,
                        emifa_fec_fin as fecha_final,
                        emifa_cod_estab as establecimiento
                from saeemifa where
                emifa_cod_emifa = $tipo_emifa";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $secuencial         = $oCon->f('secuencial');
                $num_digitos        = $oCon->f('num_digitos');
                $secuencial_real    = secuencial_func(2, '0', $secuencial, $num_digitos);
                $serie              = $oCon->f('serie');
                $autorizacion       = $oCon->f('autorizacion');
                $fecha_aut          = $oCon->f('fecha_final');
                $establecimiento    = $oCon->f('establecimiento');
            }
        }
        $oCon->Free();
        
        $serie = $establecimiento.$serie;

        $datos_retorno = array(
            "tipo_comprobante"  => $tipo_comprobante,
            "secuencial"        => $secuencial,
            "num_digitos"       => $num_digitos,
            "secuencial_real"   => $secuencial_real,
            "serie"             => $serie,
            "autorizacion"      => $autorizacion,
            "fecha_aut"         => $fecha_aut,
            "establecimiento"   => $establecimiento
        );

        return $datos_retorno;
    }

    function parametros_cliente()
    {
        $oCon       = $this->oCon;
        $id_clpv    = $this->id_clpv;

        $sql = "SELECT clpv_ruc_clpv, clpv_cod_vend, clpv_pre_ven, clv_con_clpv, clpv_nom_clpv, 
                        clpv_cod_sucu, clpv_cod_cuen, clpv_pro_pago FROM saeclpv where clpv_cod_clpv = $id_clpv";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $identificacion     = $oCon->f('clpv_ruc_clpv');
                $clpv_cod_vend      = $oCon->f('clpv_cod_vend');
                $clpv_pre_ven       = $oCon->f('clpv_pre_ven');
                $clv_con_clpv       = $oCon->f('clv_con_clpv');
                $clpv_nom_clpv      = $oCon->f('clpv_nom_clpv');
                $sucursal_cliente   = $oCon->f('clpv_cod_sucu');
                $clpv_cuen          = $oCon->f('clpv_cod_cuen');
                $dias               = $oCon->f('clpv_pro_pago');
            }
        }
        $oCon->Free();

        $nombre_cliente = eliminar_tildes($clpv_nom_clpv);
        $nombre_cliente = formatear_cadena($nombre_cliente);
        //$nombre_cliente = utf8_decode($nombre_cliente);

        $sql = "SELECT direccion FROM isp.contrato_clpv where id_clpv = $id_clpv";
        $direccion = consulta_string($sql, 'direccion', $oCon, 0);

        $datos_retorno = array(
            "identificacion"    => $identificacion,
            "clpv_cod_vend"     => $clpv_cod_vend,
            "clpv_pre_ven"      => $clpv_pre_ven,
            "clv_con_clpv"      => $clv_con_clpv,
            "nombre_cliente"    => $nombre_cliente,
            "sucursal_cliente"  => $sucursal_cliente,
            "clpv_cuen"         => $clpv_cuen,
            "dias"              => $dias,
            "direccion"         => $direccion
        );

        return $datos_retorno;
    }

    function parametros_factura($datos)
    {

        $oCon           = $this->oCon;
        $id_empresa     = $this->id_empresa;
        $id_sucursal    = $this->id_sucursal;
        $id_user        = $this->id_user;

        $fecha_ejer     = $datos["fecha_ejer"];
        $fecha_accion   = $datos["fecha_accion"];

        $sql = "SELECT ejer_cod_ejer FROM saeejer where ejer_fec_finl = '$fecha_ejer' and ejer_cod_empr = $id_empresa";
        $idejer     = consulta_string($sql, 'ejer_cod_ejer', $oCon, 1);

        $sql_moneda = "SELECT pcon_mon_base from saepcon where pcon_cod_empr = $id_empresa ";
        $moneda     = consulta_string($sql_moneda, 'pcon_mon_base', $oCon, '');

        $sql_vendedor = "SELECT empl_cod_empl, CONCAT(usuario_apellido, ' ', usuario_nombre) as nombre_usuario, vend_cod_vend 
                        FROM comercial.usuario 
                        WHERE usuario_id = $id_user";
        $empl_cod_empl  = consulta_string($sql_vendedor, 'empl_cod_empl', $oCon, '');
        $nombre_usuario = consulta_string($sql_vendedor, 'nombre_usuario', $oCon, '');
        $vend_cod_vend  = consulta_string($sql_vendedor, 'vend_cod_vend', $oCon, '');
        
         $sql = "SELECT para_ite_fact, para_fac_cxc, para_pro_bach, COALESCE(para_sec_usu,'N') as para_sec_usu, para_punt_emi
				from saepara where
				para_cod_empr = $id_empresa and
				para_cod_sucu = $id_sucursal ";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $para_ite_fact  = $oCon->f('para_ite_fact');
                $secu_ctrl      = $oCon->f('para_sec_usu');
                $mayo_sn        = $oCon->f('para_pro_bach');
                $tran           = $oCon->f('para_fac_cxc');
                $para_punt_emi  = $oCon->f('para_punt_emi');
            }
        }
        $oCon->Free();

        $sql_formato = "SELECT ftrn_cod_ftrn from saeftrn where
						ftrn_cod_empr = $id_empresa and
						ftrn_cod_modu = 7 and
						ftrn_des_ftrn = 'FACTURA' ";
        $formato = consulta_string_func($sql_formato, 'ftrn_cod_ftrn', $oCon, 0);

        $sql_tcambio = "SELECT tcam_fec_tcam, tcam_cod_tcam, tcam_val_tcam  from saetcam where
						tcam_cod_mone = $moneda and
						mone_cod_empr = $id_empresa and
						tcam_fec_tcam = (SELECT max(tcam_fec_tcam) from saetcam where
                                            tcam_cod_mone = $moneda and
                                            tcam_fec_tcam <= '$fecha_accion' and
                                            mone_cod_empr = $id_empresa) ";
        if ($oCon->Query($sql_tcambio)) {
            if ($oCon->NumFilas() > 0) {
                $tcambio = $oCon->f('tcam_cod_tcam');
                $val_tcambio = $oCon->f('tcam_val_tcam');
            } else {
                $tcambio = 0;
                $val_tcambio = 0;
            }
        }
        $oCon->Free();

        $datos_retorno = array(
            "idejer"        => $idejer,
            "moneda"        => $moneda,
            "empl_cod_empl" => $empl_cod_empl,
            "nombre_usuario"=> $nombre_usuario,
            "vend_cod_vend" => $vend_cod_vend,
            "secu_ctrl"     => $secu_ctrl,
            "mayo_sn"       => $mayo_sn,
            "tran"          => $tran,
            "para_punt_emi" => $para_punt_emi,
            "formato"       => $formato,
            "tcambio"       => $tcambio,
            "val_tcambio"   => $val_tcambio
        );

        return $datos_retorno;
    }

    function control_factura($datos)
    {
        $oCon           = $this->oCon;
        $id_empresa     = $this->id_empresa;
        $id_sucursal    = $this->id_sucursal;

        $secuencial     = $datos["secuencial"];
        $serie          = $datos["serie"];

        $sql = "SELECT count(*) as control
				from saefact
				where fact_cod_empr = $id_empresa and
				fact_cod_sucu = $id_sucursal and
				fact_num_preimp = '$secuencial' and
				fact_nse_fact = '$serie'";
        $control = consulta_string_func($sql, 'control', $oCon, 0);

        return $control;
    }

    function control_factura_2($datos)
    {
        $oCon           = $this->oCon;
        $id_empresa     = $this->id_empresa;
        $id_sucursal    = $this->id_sucursal;

        $secuencial     = $datos["secuencial"];
        $serie          = $datos["serie"];
        $id_fact        = $datos["id_fact"];

        $sql = "SELECT fact_cod_fact as control
				from saefact
				where fact_cod_empr = $id_empresa and
				fact_cod_sucu = $id_sucursal and
				fact_num_preimp = '$secuencial' and
				fact_nse_fact = '$serie' and 
                fact_cod_fact not in ($id_fact)";
        $control = consulta_string_func($sql, 'control', $oCon, 0);

        return $control;
    }

    function cabecera_factura($datos)
    {

        $oCon           = $this->oCon;
        $id_empresa     = $this->id_empresa;
        $id_sucursal    = $this->id_sucursal;
        $id_clpv        = $this->id_clpv;
        $id_user        = $this->id_user;
        $id_user_ifx    = $this->id_user_ifx;

        $moneda                 = $datos["moneda"];
        $tcambio                = $datos["tcambio"];
        $vend_cod_vend          = $datos["vend_cod_vend"];
        $empl_cod_empl          = $datos["empl_cod_empl"];
        $fecha                  = $datos["fecha"];
        $idprdo                 = $datos["idprdo"];
        $idejer                 = $datos["idejer"];
        $formato                = $datos["formato"];
        $secuencial_real        = $datos["secuencial_real"];
        $nombre_cliente         = $datos["nombre_cliente"];
        $celular_clpv           = $datos["celular_clpv"];
        $iva_tot                = $datos["iva_tot"];
        $con_iva                = $datos["con_iva"];
        $sin_iva                = $datos["sin_iva"];
        $fecha_server           = $datos["fecha_server"];
        $estado_fact            = $datos["estado_fact"];
        $precio                 = $datos["precio"];
        $descuento_porcentaje   = $datos["descuento_porcentaje"];
        $descuento              = $datos["descuento"];
        $total_tot              = $datos["total_tot"];
        $identificacion         = $datos["identificacion"];
        $direccion              = $datos["direccion"];
        $observacion            = $datos["observacion"];
        $nombre_orden_compra    = $datos["nombre_orden_compra"];
        $valor_orden_compra     = $datos["valor_orden_compra"];
        $valor_detraccion       = $datos["valor_detraccion"];
        $dias                   = $datos["dias"];
        $sucursal_cliente       = $datos["sucursal_cliente"];
        $tran                   = $datos["tran"];
        $serie                  = $datos["serie"];
        $autorizacion           = $datos["autorizacion"];
        $fecha_aut              = $datos["fecha_aut"];
        $ice_tot                = $datos["ice_tot"];
        $hora_inicio            = $datos["hora_inicio"];
        $hora_final             = $datos["hora_final"];
        $val_tcambio            = $datos["val_tcambio"];
        $email_clpv             = $datos["email_clpv"];
        $tipo_comprobante       = $datos["tipo_comprobante"];
        $asto_cod               = $datos["asto_cod"];
        $valor_efe              = $datos["valor_efe"];
        $valor_entregar         = $datos["valor_entregar"];
        $id_contratos           = $datos["id_contratos"];
        $clv_con_clpv           = $datos["clv_con_clpv"];
        $fact_fech_venc         = $datos["fact_fech_venc"];

        $sql = "SELECT campo_rete_fact
                from isp.int_parametros_general
                where id_empresa = $id_empresa";
        $campo_rete_fact = consulta_string_func($sql, 'campo_rete_fact', $oCon, '');

        $valor_detra = 0;
        if($campo_rete_fact == 'S'){
            if($valor_detraccion != 0 && $valor_detraccion != ''){
                $sql_1 = "SELECT tret_porct FROM saetret WHERE tret_cod = '$valor_detraccion' and tret_cod_empr = $id_empresa";
                $tret_porct = consulta_string_func($sql_1, 'tret_porct', $oCon, '');

                $tret_porct_c = $tret_porct / 100;

                $valor_detra = $total_tot * $tret_porct_c;
            }
        }

        $sql = "INSERT INTO saefact(fact_cod_aux,    		fact_cod_sucu,     		fact_cod_empr,
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
                                    fact_ret2m_fact,        fact_cod_detra,  		
									fact_cm3_fact,     		fact_cm4_fact,			fact_dia_fact,
									fact_sucu_clpv,			fact_fon_fact,     		fact_nse_fact,     		
									fact_nau_fact,     		fact_fech_aut,			fact_anti_fact,    		
									fact_cod_ccli,			fact_ice,          		fact_tip_llam,     		
									fact_hor_ini,      		fact_hor_fin,			fact_val_tcam,     		
									fact_usu_fact,     		fact_email_clpv,		fact_tip_vent,    		
									fact_val_irbp,			fact_user_web,			fact_cod_asto,
									fact_cod_contr,			fact_cm8_fac,			fact_cm9_fac,
									fact_awb_fact,			fact_cm7_fac,			fact_cm5_fac ,
									fact_con_clv,           fact_retimd_fact,       fact_mon_rete_ext )
							values(0,                 		$id_sucursal,         	$id_empresa,
									$moneda,           		$tcambio,         		'$vend_cod_vend',
									$id_clpv,          		'$empl_cod_empl',       $formato,
									'$fecha',               '$fact_fech_venc',      $id_user_ifx,
									$idprdo,                $idejer,        		'$secuencial_real',
									'$nombre_cliente',      '$celular_clpv',        $iva_tot,
									$con_iva,               $sin_iva ,				'$fecha_server',      
									'$estado_fact',         $precio,				$descuento_porcentaje,             
									$descuento,             0,					    0,                 
									0,              		$total_tot,			    '$identificacion',                 
									'$direccion', 			'$observacion',		    '$nombre_orden_compra',    
                                    $valor_orden_compra,    '$valor_detraccion',
									'',  		            '',		                '$dias',
									$sucursal_cliente,		'$tran',                '$serie',
									'$autorizacion', 		'$fecha_aut',			0,              
									0,			            '$ice_tot',             'E',
									'$hora_inicio',   		'$hora_final',			$val_tcambio,
									$id_user_ifx ,     		'$email_clpv',		    '$tipo_comprobante',  	
									0,			            $id_user,				'$asto_cod',
									0,			            '$valor_efe',	        '$valor_entregar',
									'',		                '$id_contratos',		'' ,
								    '$clv_con_clpv',         $valor_entregar,       $valor_detra)";
        $oCon->QueryT($sql);

        $sql = "SELECT max(fact_cod_fact) as fact_cod_fact 
				FROM saefact 
                WHERE fact_num_preimp = '$secuencial_real' and
				      fact_cod_empr = $id_empresa and
				      fact_cod_sucu = $id_sucursal and 
				      fact_cod_clpv = $id_clpv and 
				      fact_est_fact = '$estado_fact'";
        $fact_cod_fact = consulta_string_func($sql, 'fact_cod_fact', $oCon, 0);

        return $fact_cod_fact;
    }

    function detalle_factura($datos)
    {

        $oCon           = $this->oCon;
        $id_empresa     = $this->id_empresa;
        $id_sucursal    = $this->id_sucursal;
        $id_clpv        = $this->id_clpv;
        $id_user        = $this->id_user;
        $id_user_ifx    = $this->id_user_ifx;

        $codigo_plan    = $datos["codigo_plan"];
        $id_cuota_d     = $datos["id_cuota_d"];
        $id_bodega      = $datos["id_bodega"];
        $descripcion    = $datos["descripcion"];
        $val_descuento  = $datos["val_descuento"];
        $nombre_plan    = $datos["nombre_plan"];
        $tarifa         = $datos["tarifa"];
        $iva_porc_d     = $datos["iva_porc_d"];
        $ice_porc_d     = $datos["ice_porc_d"];
        $subtotal_d     = $datos["subtotal_d"];
        $valor_iva_d    = $datos["valor_iva_d"];
        $valor_ice_d    = $datos["valor_ice_d"];
        $total_d        = $datos["total_d"];
        $fact_cod_fact  = $datos["fact_cod_fact"];
        $idprdo         = $datos["idprdo"];
        $idejer         = $datos["idejer"];
        $vend_cod_vend  = $datos["vend_cod_vend"];
        $fecha          = $datos["fecha"];
        $val_tcam       = $datos["val_tcam"];
        $bandera_mone   = $datos["bandera_mone"];
        $cero           = 0;

        $sql_unid       = "SELECT prbo_cod_unid from saeprbo where prbo_cod_prod = '$codigo_plan' and prbo_cod_empr = $id_empresa";
        $id_unidad      = consulta_string($sql_unid, 'prbo_cod_unid', $oCon, ''); 
        
        $sql_linp       = "SELECT prod_cod_linp, prod_cod_grpr, prod_cod_cate, prod_cod_marc from saeprod where prod_cod_prod = '$codigo_plan' and prod_cod_empr = $id_empresa";
        $id_linp        = consulta_string($sql_linp, 'prod_cod_linp', $oCon, ''); 
        $id_grpr        = consulta_string($sql_linp, 'prod_cod_grpr', $oCon, ''); 
        $id_cate        = consulta_string($sql_linp, 'prod_cod_cate', $oCon, ''); 
        $id_marc        = consulta_string($sql_linp, 'prod_cod_marc', $oCon, ''); 
        
        $por_descuento  = ($val_descuento * 100) / $tarifa;
        $cero           = 0;

        $dfac_mont_total = $subtotal_d - $val_descuento;

        $cod_pais = $_SESSION['S_PAIS_API_SRI'];

        if($cod_pais == '51'){
            $dfac_obj_iva = 0;
            $dfac_exc_iva = 0;

            $sql = "SELECT prod_sn_noi, prod_sn_exe from saeprod where prod_cod_prod = '$codigo_plan' and prod_cod_empr = $id_empresa";
            $prod_sn_noi = consulta_string_func($sql, 'prod_sn_noi', $oCon, '');
            $prod_sn_exe = consulta_string_func($sql, 'prod_sn_exe', $oCon, '');

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

        $sql = "SELECT prbo_cco_prbo from saeprbo where prbo_cod_prod = '$codigo_plan' and prbo_cod_empr = $id_empresa and prbo_cod_bode = $id_bodega";
        $dfac_cod_ccos = consulta_string_func($sql, 'prbo_cco_prbo', $oCon, '');

        $sql = "INSERT INTO saedfac(dfac_cod_sucu,    		dfac_cod_empr,    		dfac_cod_unid,
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
                                    dfac_cod_ccos,    		dfac_cod_mes,           dfac_cp1_dfac,
                                    dfac_obj_iva,           dfac_exc_iva)
                            VALUES($id_sucursal,		    $id_empresa, 			$id_unidad,
                                    $fact_cod_fact, 		'$codigo_plan', 	    $id_bodega, 
                                    $idprdo,				$idejer, 				1, 
                                    $subtotal_d, 		    $dfac_mont_total,		$por_descuento,
                                    $cero, 					$cero, 					$cero, 					
                                    $cero,					$cero, 					$cero, 					
                                    $cero, 					$iva_porc_d, 			$cero, 					
                                    $id_linp, 		        $cero, 	                $cero,			
                                    $cero, 					1, 				        '$vend_cod_vend', 				
                                    $id_clpv,				'$fecha', 	            $id_grpr, 		
                                    $id_cate, 		        $id_marc,			    $ice_porc_d,			
                                    '$descripcion', 		'$nombre_plan', 		$cero, 			
                                    $cero,					'$cero', 		        '$cero', 		
                                    '$dfac_cod_ccos', 		$cero,                  '$id_cuota_d',
                                    $dfac_obj_iva,          $dfac_exc_iva)";
        $oCon->QueryT($sql);

        $val_pago_isp = $subtotal_d+$valor_iva_d+$valor_ice_d;
        if($bandera_mone == 1){
            $val_pago_isp = $val_pago_isp / $val_tcam;
            $val_descuento = $val_descuento / $val_tcam;
        }

        $sql = "UPDATE isp.contrato_pago_pack 
                SET valor_pago = valor_pago + ($val_pago_isp), descuento = descuento + (-$val_descuento) 
                WHERE id = $id_cuota_d";
        $oCon->QueryT($sql);

        $sql = "SELECT id_pago, id_empresa, id_sucursal, id_contrato FROM isp.contrato_pago_pack WHERE id = $id_cuota_d AND id_clpv = $id_clpv";
        $id_pago = consulta_string_func($sql, 'id_pago', $oCon, 0);

        $sql = "UPDATE isp.contrato_pago SET
                valor_pago=
                    (
                    SELECT SUM(valor_pago) 
                    from isp.contrato_pago_pack 
                    WHERE id_clpv = $id_clpv AND id = $id_cuota_d
                    ),
                descuento=
                    (
                    SELECT SUM(descuento) 
                    from isp.contrato_pago_pack 
                    WHERE id_clpv = $id_clpv AND id = $id_cuota_d
                    ),
                estado_fact = 'GR',
                id_factura = $fact_cod_fact,
                facturado = 'S'
                WHERE id_clpv=$id_clpv AND id = $id_pago";
        $oCon->QueryT($sql);

        return true;
    }

    function obtener_id_contrato_pago($cuotas)
    {

        $oCon           = $this->oCon;
        $id_empresa     = $this->id_empresa;
        $id_sucursal    = $this->id_sucursal;
        $id_clpv        = $this->id_clpv;
        $id_user        = $this->id_user;
        $id_user_ifx    = $this->id_user_ifx;

        $respuesta      = [];
        for($d=0;$d<count($cuotas);$d++){
            $id_cuota_d = $cuotas[$d]["id_cuota_d"];
            
            $sql = "SELECT id_pago FROM isp.contrato_pago_pack WHERE id = $id_cuota_d AND id_clpv = $id_clpv";
            $id_pago = consulta_string_func($sql, 'id_pago', $oCon, 0);

            $sql = "SELECT SUM(valor_pago) as pagado, SUM(descuento) as descuento, id_contrato, id_factura FROM isp.contrato_pago WHERE id = $id_pago GROUP BY 3,4";
            $pagado     = consulta_string_func($sql, 'pagado', $oCon, 0);
            $descuento  = consulta_string_func($sql, 'descuento', $oCon, 0);
            $id_contrato  = consulta_string_func($sql, 'id_contrato', $oCon, 0);
            $id_factura  = consulta_string_func($sql, 'id_factura', $oCon, 0);

            $array_datos_indi = array(
                "id_pago" => $id_pago,
                "pagado" => $pagado,
                "descuento" => $descuento,
                "id_contrato" => $id_contrato,
                "id_factura" => $id_factura
            );

            $respuesta[$d] = $array_datos_indi;
        }
       
        return $respuesta;
    }

    function contrato_factura($datos)
    {

        $oCon           = $this->oCon;
        $id_empresa     = $this->id_empresa;
        $id_sucursal    = $this->id_sucursal;
        $id_clpv        = $this->id_clpv;
        $id_user        = $this->id_user;
        $id_user_ifx    = $this->id_user_ifx;

        $fecha_server   = date("Y-m-d");

        for($d=0;$d<count($datos);$d++){
            
            $id_pago        = $datos[$d]["id_pago"];
            $pagado         = $datos[$d]["pagado"];
            $descuento      = $datos[$d]["descuento"];
            $id_contrato    = $datos[$d]["id_contrato"];
            $id_factura     = $datos[$d]["id_factura"];

            $sql = "INSERT INTO isp.contrato_factura (id_empresa,   id_sucursal,    id_contrato,    id_clpv,        id_pago, 
                                                        id_factura, valor,          deuda_anterior, deuda_actual,   estado, 
                                                        user_web,   fecha_server,   tipo,           monto_pago)
                                                VALUES($id_empresa, $id_sucursal,   $id_contrato,   $id_clpv,       $id_pago,
                                                        $id_factura,$pagado,        0,              0,              'A',
                                                        $id_user,   '$fecha_server','FC',           $pagado)";
            $oCon->QueryT($sql);

        }
       
        return true;
    }

    function actualizar_secuencial($id_emifa)
    {

        $oCon           = $this->oCon;
        $id_empresa     = $this->id_empresa;
        $id_sucursal    = $this->id_sucursal;

        $sql = "SELECT emifa_sec_doc from saeemifa WHERE emifa_cod_emifa = $id_emifa";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $emifa_sec_doc = $oCon->f('emifa_sec_doc');
            }
        }  

        $secu_mas = $emifa_sec_doc + 1;

        $sql_update_secuencial = "UPDATE saeemifa set emifa_sec_doc = '$secu_mas' where
                                    emifa_cod_empr = $id_empresa and
                                    emifa_cod_sucu = $id_sucursal and
                                    emifa_cod_emifa = $id_emifa";
        $oCon->QueryT($sql_update_secuencial);

        return true;
    }

    function ingreso_forma_pago($datos)
    {

        $oCon               = $this->oCon;
        $id_empresa         = $this->id_empresa;
        $id_sucursal        = $this->id_sucursal;
        $id_clpv            = $this->id_clpv;
        $id_user            = $this->id_user;
        $id_user_ifx        = $this->id_user_ifx;

        $fecha              = $datos["fecha"];
        $idprdo             = $datos["idprdo"];
        $idejer             = $datos["idejer"];
        $fact_cod_fact      = $datos["fact_cod_fact"];
        $nombre_cliente     = $datos["nombre_cliente"];
        $forma_pago         = $datos["forma_pago"];
        $valores_fp         = $datos["valores_fp"];
        $cuotas_det_info    = $datos["cuotas_det_info"];
        $moneda_fact        = $datos["moneda_fact"];
        $val_tcam           = $datos["val_tcam"];
        $fecha_servidor     = date("Y-m-d");

        $sql = "SELECT fpag_cot_fpag, fpag_cod_cuen FROM saefpag WHERE fpag_cod_fpag = $forma_pago AND fpag_cod_empr = $id_empresa";
        $fpag_cot_fpag = consulta_string_func($sql, 'fpag_cot_fpag', $oCon, 0);
        $fpag_cod_cuen = consulta_string_func($sql, 'fpag_cod_cuen', $oCon, 0);

        $sql = "SELECT clpv_nom_clpv FROM saeclpv WHERE clpv_cod_clpv = $id_clpv";
        $clpv_nom_clpv = consulta_string_func($sql, 'clpv_nom_clpv', $oCon, 0);

        if($fpag_cot_fpag == 'TAR' || $fpag_cot_fpag == 'DEP' || $fpag_cot_fpag == 'CHE'){

            $total_fp = $valores_fp["valor_1"];
            $cod_banc = $valores_fp["valor_2"];
            $num_docu = $valores_fp["valor_3"];
            $nom_depo = $valores_fp["valor_4"];
            $fecha_fp = $valores_fp["valor_5"];

            $sql_id = "SELECT fxfp_num_rete from saefxfp WHERE fxfp_num_rete = '$num_docu' AND fxfp_val_fxfp > 0";
		    $control_docu = consulta_string_func($sql_id, 'fxfp_num_rete', $oCon, 0);

            if($control_docu > 0){
                throw new Exception('Número de documento '.$num_docu.' ya registrado, no se puede continuar.');
            }

            $total_fp = str_replace(",", "", $total_fp);

            $sql = "INSERT INTO saefxfp(fxfp_cod_fxfp,    		fxfp_cod_sucu,     		fxfp_cod_empr,
                                        fxfp_cod_ejer,     		fxfp_num_prdo,     		fxfp_cod_fact,
                                        fxfp_cod_fpag,          fxfp_num_dias,          fxfp_poc_fxfp,          
                                        fxfp_val_fxfp,          fxfp_fec_fxfp,          fxfp_fec_fin, 
                                        fxfp_cot_fpag,          fxfp_num_rete,          fxfp_nom_rete,
                                        fxfp_val_ext,           fxfp_pre_cierre  
                                        )
                                values(1,                       $id_sucursal,           $id_empresa,
                                        '$idejer',              '$idprdo',              $fact_cod_fact,
                                        $forma_pago,            0,                      100,
                                        $total_fp,              '$fecha_fp',            '$fecha_fp',
                                        '$fpag_cot_fpag',       '$num_docu',            '$nom_depo',
                                        $total_fp,              'N'
                                        )";
            $oCon->QueryT($sql);

            $sql_id = "SELECT max(bafp_cod_bafp) as maximo from saebafp ";
		    $id_bafp = consulta_string_func($sql_id, 'maximo', $oCon, 0);
            $id_bafp = $id_bafp + 1;

            $nom_depo = substr($nom_depo,0,40);

            $sql = "INSERT INTO saebafp(bafp_cod_bafp,    		bafp_cod_fxfp,     		bafp_cod_sucu,
                                        bafp_cod_empr,          bafp_cod_ejer,     		bafp_num_prdo,     		
                                        bafp_cod_fact,          bapg_nom_banc,          bapg_nom_gira,          
                                        bapg_fec_venc,          bapg_num_ctab,          bapg_num_cheq,          
                                        bapg_val_mont
                                        )
                                values($id_bafp,                1,                      $id_sucursal,
                                        $id_empresa,            '$idejer',              '$idprdo',
                                        $fact_cod_fact,         '$cod_banc',            '$nom_depo',
                                        '$fecha',               '$fecha_fp',            '$num_docu',
                                        $total_fp
                                        )";
            $oCon->QueryT($sql);
        }else if($fpag_cot_fpag == 'EFE'){

            if($fpag_cod_cuen == 0){
                throw new Exception('Sin cuenta configurada en la forma de pago, no se puede continuar.');
            }

            $total_fp = $valores_fp["valor_1"];

            $total_fp = str_replace(",", "", $total_fp);

            $sql = "INSERT INTO saefxfp(fxfp_cod_fxfp,    		fxfp_cod_sucu,     		fxfp_cod_empr,
                                        fxfp_cod_ejer,     		fxfp_num_prdo,     		fxfp_cod_fact,
                                        fxfp_cod_fpag,          fxfp_num_dias,          fxfp_poc_fxfp,          
                                        fxfp_val_fxfp,          fxfp_fec_fxfp,          fxfp_fec_fin, 
                                        fxfp_cot_fpag,          fxfp_val_ext,           fxfp_pre_cierre,        
                                        fxfp_cod_cuen,          fxfp_num_rete,          fxfp_nom_rete
                                        )
                                values(1,                       $id_sucursal,           $id_empresa,
                                        '$idejer',              '$idprdo',              $fact_cod_fact,
                                        $forma_pago,            0,                      100,
                                        $total_fp,              '$fecha',               '$fecha',
                                        '$fpag_cot_fpag',       $total_fp,              'N',                    
                                        '$fpag_cod_cuen',       '0',                    '$clpv_nom_clpv'
                                        )";
            $oCon->QueryT($sql);
        }else if($fpag_cot_fpag == 'CRE'){

            $total_fp = $valores_fp["valor_1"];

            $total_fp = str_replace(",", "", $total_fp);
            
            $sql = "SELECT fact_nse_fact, fact_num_preimp from saefact where fact_cod_fact = $fact_cod_fact ";
            $serie = consulta_string($sql, 'fact_nse_fact', $oCon, 0);
            $secuencial_real = consulta_string($sql, 'fact_num_preimp', $oCon, 0);

            $sql_moneda = "SELECT pcon_mon_base from saepcon where pcon_cod_empr = $id_empresa ";
            $moneda = consulta_string($sql_moneda, 'pcon_mon_base', $oCon, '');

            $sql_tcambio = "SELECT tcam_fec_tcam, tcam_cod_tcam, tcam_val_tcam  from saetcam where
                            tcam_cod_mone = $moneda and
                            mone_cod_empr = $id_empresa and
                            tcam_fec_tcam = (SELECT max(tcam_fec_tcam) from saetcam where
                            tcam_cod_mone = $moneda and
                            tcam_fec_tcam <= '$fecha_servidor' and
                            mone_cod_empr = $id_empresa) ";
            $tcambio = consulta_string($sql_tcambio, 'tcam_cod_tcam', $oCon, 0);
            $val_tcambio = consulta_string($sql_tcambio, 'tcam_val_tcam', $oCon, 0);

            $sql_id = "SELECT para_fac_cxc
                        from saepara where
                        para_cod_empr = $id_empresa and
                        para_cod_sucu = $id_sucursal";
		    $tran = consulta_string_func($sql_id, 'para_fac_cxc', $oCon, 0);

            $sql = "SELECT tran_des_tran
                    from saetran Where
                        tran_cod_tran = '$tran' and
                        tran_cod_empr = $id_empresa and
                        tran_cod_modu = 3 ";
            $det = consulta_string($sql, 'dfac_cod_prod', $oCon, 'FACTURACION CLIENTES');            

            if(count($cuotas_det_info) == 0){
                throw new Exception('Sin cuotas en credito, no se puede continuar.');
            }

            $num_dias       = $datos["num_dias"];
            $num_cuotas     = $datos["num_cuotas"];

            for($i=0;$i<count($cuotas_det_info);$i++){

                $numero     = $cuotas_det_info[$i][0];
                $fech_ini   = $cuotas_det_info[$i][1];
                $fech_fin   = $cuotas_det_info[$i][2];
                $valor      = $cuotas_det_info[$i][3];
                $saldo      = $cuotas_det_info[$i][4];

                $valor      = str_replace(",", "", $valor);
                $por_cuotas = ($valor * 100)/$total_fp;

                $sql = "INSERT INTO saefxfp(fxfp_cod_fxfp,    		fxfp_cod_sucu,     		fxfp_cod_empr,
                                            fxfp_cod_ejer,     		fxfp_num_prdo,     		fxfp_cod_fact,
                                            fxfp_cod_fpag,          fxfp_num_dias,          fxfp_poc_fxfp,          
                                            fxfp_val_fxfp,          fxfp_fec_fxfp,          fxfp_fec_fin, 
                                            fxfp_cot_fpag,          fxfp_val_ext,           fxfp_pre_cierre,        
                                            fxfp_cod_cuen,          fxfp_num_rete,          fxfp_nom_rete
                                            )
                                    values($i+1,                       $id_sucursal,           $id_empresa,
                                            '$idejer',              '$idprdo',              $fact_cod_fact,
                                            $forma_pago,            $num_dias,              $por_cuotas,
                                            $valor,                 '$fech_ini',            '$fech_fin',
                                            '$fpag_cot_fpag',       $total_fp,              'N',                    
                                            '$fpag_cod_cuen',       '0',                    '$clpv_nom_clpv'
                                            )";
                $oCon->QueryT($sql);

                //INGRESO EN LA DMCC
                $detalle_dmcc = "Facturacion " . $det . " Facturacion";
                $dmcc_num_fac = $serie . '-' . $secuencial_real . '-' . str_pad($i, 3, "0", STR_PAD_LEFT);;

                $sql_id = "SELECT max(dmcc_cod_dmcc) as maximo from saedmcc ";
                $id_dmcc = consulta_string_func($sql_id, 'maximo', $oCon, 0);
                $id_dmcc = $id_dmcc + $_SESSION['U_ID'];

                $valor_ext = $valor / $val_tcam;

                $sql_dmcc = "INSERT INTO saedmcc(dmcc_cod_dmcc,     dmcc_cod_empr,      dmcc_cod_sucu,     dmcc_cod_ejer,
                                                dmcc_cod_modu,      modu_cod_modu,      dmcc_cod_mone,
                                                clpv_cod_clpv,      dmcc_cod_tran,      dmcc_num_fac,
                                                dmcc_fec_ven,       dmcc_fec_emis,      dmcc_det_dmcc,
                                                dmcc_mon_ml,        dmcc_mon_ext,       dmcc_est_dmcc,
                                                dmcc_deb_ml,        dmcc_cre_ml,        dmcc_cod_fact,
                                                dmcc_val_coti,      dmcc_deb_mext,      dmcc_cre_mext,
                                                dmcc_mov_sucu )
                                        values ($id_dmcc,	        $id_empresa,        $id_sucursal,       $idejer,
                                                3,                  7,                  $moneda_fact,
                                                $id_clpv,           '$tran',            '$dmcc_num_fac',
                                                '$fech_fin',        '$fech_ini',        '$detalle_dmcc',
                                                '$valor',           '$valor_ext',       'PE',
                                                '$valor',           '0',                '$fact_cod_fact',
                                                '$val_tcam',     '$valor_ext',       '0',
                                                $id_sucursal)";
                $oCon->QueryT($sql_dmcc);

            }
            
        }        

        return $resp;
    }

   
}
