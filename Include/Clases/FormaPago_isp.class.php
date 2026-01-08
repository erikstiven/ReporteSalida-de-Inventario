<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class FormaPago{
	
	var $oConexion; 
	
	function parametrosFormaPago($oConexion, $idEmpresa, $idSucursal, $formaPago){
		
		$fecha_servidor = date("Y-m-d");
		
		$sql = "select para_fac_cxc
				from saepara where
				para_cod_empr = $idEmpresa and
				para_cod_sucu = $idSucursal ";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				$para_fac_cxc = $oConexion->f('para_fac_cxc');
			}
		}
		$oConexion->Free();
		
		// cuenta de la fp
		$sql = "select fpag_cod_cuen, fpag_cod_clpv, fpag_cot_fpag,
				fpag_det_fpag
				from saefpag where
				fpag_cod_empr = $idEmpresa and
				fpag_cod_sucu = $idSucursal and
				fpag_cod_fpag = '$formaPago'";
		//$oReturn->alert($sql);
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				$fpag_cod_cuen = $oConexion->f('fpag_cod_cuen');
				$fpag_cod_clpv = $oConexion->f('fpag_cod_clpv');
				$fpag_cot_fpag = $oConexion->f('fpag_cot_fpag');
				$fpag_det_fpag = $oConexion->f('fpag_det_fpag');
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
		
		$array = array($moneda, $tcambio, $val_tcambio, $para_fac_cxc, $fpag_cod_cuen, $fpag_cod_clpv, $fpag_cot_fpag, $fpag_det_fpag);

		
		return $array;
	}
	
	function cabeceraFormaPago($oConexion, $id, $idEmpresa, $idSucursal, $idejer, $idprdo, $id_factura, $forma_pago, $cuen_cod_cuen,
							$dias, $porcentaje, $valor, $fecha_inicio, $fecha_fin, $fpag_cot, $ret, $porc_ret, $base, $cheque,
							$clpv_nom, $valor_ext, $num_referencia = '', $base64data = '',  $fecha_tipo_cambio = '', $tipo_cambio = 0, $valor_transformado = 0){
		
		//VALIDACION POSTGRESS
		if(strlen($porc_ret)==0){
			$porc_ret=0;
		}
		if(strlen($cheque)==0){
			$cheque=0;
		}

		$valor = str_replace(",", "", $valor);

		$sql = "SELECT empr_mone_fxfp from saeempr WHERE  empr_cod_empr = $idEmpresa";
        $empr_mone_fxfp = consulta_string_func($sql, 'empr_mone_fxfp', $oConexion, 'N');

		$fpag_cod_mone = 0;
		if($empr_mone_fxfp == 'S'){
			$sql = "SELECT fpag_cod_mone from saefpag WHERE fpag_cod_fpag = $forma_pago";
        	$fpag_cod_mone = consulta_string_func($sql, 'fpag_cod_mone', $oConexion, 0);

			/* $sql = "SELECT t1.tcam_val_tcam
					FROM saetcam t1
					INNER JOIN (
						SELECT tcam_cod_mone, MAX(tcam_fec_tcam) AS max_fec_tcam
						FROM saetcam
						GROUP BY tcam_cod_mone
					) t2
					ON t1.tcam_cod_mone = t2.tcam_cod_mone
					AND t1.tcam_fec_tcam = t2.max_fec_tcam
					AND t2.tcam_cod_mone = $fpag_cod_mone";
        	$tcam_val_tcam = consulta_string_func($sql, 'tcam_val_tcam', $oConexion, 0); */
		}

		if(empty($fecha_tipo_cambio)){
			$fecha_tipo_cambio = date("Y-m-d");
		}

	 	$sql_fxfp = "insert into saefxfp(fxfp_cod_fxfp,    	fxfp_cod_sucu,     fxfp_cod_empr,
										 fxfp_cod_ejer,    		fxfp_num_prdo,     fxfp_cod_fact,
										 fxfp_cod_fpag,    		fxfp_cod_cuen,     fxfp_num_dias,
										 fxfp_poc_fxfp,    		fxfp_val_fxfp,     fxfp_fec_fxfp,
										 fxfp_fec_fin,     		fxfp_cot_fpag,     fxfp_cta_rete,
										 fxfp_por_rete,    		fxfp_bas_rete,     fxfp_num_rete,
										 fxfp_nom_rete,			fxfp_val_ext,      fxfp_num_refe,
										 fxfp_b64_img,			fxfp_cod_mone,     fxfp_val_tcam,
										 fxfp_fec_tcam, 		fxfp_val_mone)
								 values(($id+1),          		$idSucursal,        $idEmpresa,
										$idejer,         		$idprdo,           	$id_factura,
										$forma_pago,     		'$cuen_cod_cuen',  	$dias,
										$porcentaje,    		'$valor',            	'$fecha_inicio',
										'$fecha_fin',    		'$fpag_cot',       	'$ret',
										'$porc_ret',     		'$base',           	'$cheque', 
										'$clpv_nom',			'$valor_ext',       '$num_referencia',
										'$base64data',			$fpag_cod_mone,     $tipo_cambio,
										'$fecha_tipo_cambio', 	$valor_transformado) ";
		$oConexion->QueryT($sql_fxfp);
		
		//codigo serial del saefxfp
		$sql = "select max(fxfp_cod_fxfp) as fxfp_cod_fxfp
				from saefxfp where
				fxfp_cod_fact = $id_factura and
				fxfp_cod_empr = $idEmpresa and
				fxfp_cod_sucu = $idSucursal and
				fxfp_cod_fpag = '$forma_pago'";
		$fxfp_cod_fxfp = consulta_string($sql, 'fxfp_cod_fxfp', $oConexion, 0);
		
		return $fxfp_cod_fxfp;
							
	}
	
	function detalleFormaPago($oConexion, $idEmpresa, $idSucursal, $idFxfp, $idejer, $idprdo, $id_factura, $banco, $girador, $fecha_fin, $cuenta, $cheque, $valor){
		
		$sql_id = "select max(bafp_cod_bafp) as maximo from saebafp ";
		$id_bafp = consulta_string_func($sql_id, 'maximo', $oConexion, 0);
		$valor=str_replace(',','',$valor);
		$fecha_fin = date("Y-m-d");
		$sql_saebafp = "insert into saebafp (bafp_cod_bafp,    	bafp_cod_fxfp,   bafp_cod_sucu,
											 bafp_cod_empr,    	bafp_cod_ejer,   bafp_num_prdo,
											 bafp_cod_fact,    	bapg_nom_banc,   bapg_nom_gira,
											 bapg_fec_venc,    	bapg_num_ctab,   bapg_num_cheq,
											 bapg_val_mont)
									 values (($id_bafp+1),    	$idFxfp,   		$idSucursal,
											$idEmpresa,     	 $idejer,  		$idprdo,
											$id_factura,  		'$banco', 		'$girador',
											'$fecha_fin',    	'$cuenta', 		'$cheque',
											$valor) ";
		$oConexion->QueryT($sql_saebafp);
		
		return 'OK';
					
	}
	
	function estadoCuenta($oConexion, $idEmpresa, $idSucursal, $idejer, $dmcc_cod_modu, $modu_cod_modu, $moneda, $cliente, $tran, $dmcc_num_fac, $fecha_fin, $fecha_inicio, 
							$detalle, $valor, $estado, $cero, $id_factura, $val_tcambio, $dmcc_num_vch='', $dmcc_num_reci='', $dmcc_num_lot='', $dmcc_cod_contr = 0){
		$valor=str_replace(',','',$valor);
		
		$fecha_pg_v = "";

        $data_fecha_v = explode("-",$fecha_fin);

        $dia_v = $data_fecha_v[1];
        $mes_v = $data_fecha_v[0];
        $anio_v = $data_fecha_v[2];

        $fecha_pg_v = $anio_v."-".$mes_v."-".$dia_v;

		$valor_mone_ext = $valor / $val_tcambio;

		$sql_id = "select max(dmcc_cod_dmcc) as maximo from saedmcc ";
		$id_dmcc = consulta_string_func($sql_id, 'maximo', $oConexion, 0);
		$id_dmcc = $id_dmcc + $_SESSION['U_ID'];
		$sql_dmcc = "insert into saedmcc(dmcc_cod_dmcc, 	dmcc_cod_empr,    dmcc_cod_sucu,     dmcc_cod_ejer,
										 dmcc_cod_modu,    modu_cod_modu,     dmcc_cod_mone,
										 clpv_cod_clpv,    dmcc_cod_tran,     dmcc_num_fac,
										 dmcc_fec_ven,     dmcc_fec_emis,     dmcc_det_dmcc,
										 dmcc_mon_ml,      dmcc_mon_ext,      dmcc_est_dmcc,
										 dmcc_deb_ml,      dmcc_cre_ml,       dmcc_cod_fact,
										 dmcc_val_coti,    dmcc_deb_mext,     dmcc_cre_mext,
										 dmcc_mov_sucu,	   dmcc_num_vch, 	  dmcc_num_reci,	dmcc_num_lot, dmcc_cod_contr)
								values ( $id_dmcc,			$idEmpresa,       $idSucursal,       $idejer,
										$dmcc_cod_modu,   $modu_cod_modu,     $moneda,
										$cliente,         '$tran',           '$dmcc_num_fac',
										'$fecha_pg_v',     '$fecha_inicio',   '$detalle',
										'$valor',           '$valor_mone_ext',    '$estado',
										'$valor',           '$cero',              '$id_factura',
										'$val_tcambio',     '$valor_mone_ext',             '$cero',
										$idSucursal, 		'$dmcc_num_vch',  '$dmcc_num_reci', '$dmcc_num_lot', $dmcc_cod_contr) ";
		$oConexion->QueryT($sql_dmcc);
		
		return 'OK';
					
	}
	
}
?>