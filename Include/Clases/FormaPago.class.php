<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class FormaPago{
	
	var $oConexion; 
	
	function __construct(){
		//$this->empr_nom_empr = $empr_nom_empr; 		
    } 
	
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
		
		$array[] = array($moneda, $tcambio, $val_tcambio, $para_fac_cxc, $fpag_cod_cuen, $fpag_cod_clpv, $fpag_cot_fpag, $fpag_det_fpag);

		
		return $array;
	}
	
	function formaPago($oConexion, $id, $idEmpresa, $idSucursal, $idejer, $idprdo, $id_factura, $forma_pago, $cuen_cod_cuen,
							$dias, $porcentaje, $valor, $fecha_inicio, $fecha_fin, $fpag_cot, $ret, $porc_ret, $base, $cheque,
							$clpv_nom){

	    if(!$base){
            $base = 0;
        }
	    if(!$porc_ret){
            $porc_ret = 0;
        }
		
		 $sql_fxfp = "insert into saefxfp(fxfp_cod_fxfp,    	fxfp_cod_sucu,     fxfp_cod_empr,
										 fxfp_cod_ejer,    		fxfp_num_prdo,     fxfp_cod_fact,
										 fxfp_cod_fpag,    		fxfp_cod_cuen,     fxfp_num_dias,
										 fxfp_poc_fxfp,    		fxfp_val_fxfp,     fxfp_fec_fxfp,
										 fxfp_fec_fin,     		fxfp_cot_fpag,     fxfp_cta_rete,
										 fxfp_por_rete,    		fxfp_bas_rete,     fxfp_num_rete,
										 fxfp_nom_rete )
								 values(($id+1),          		$idSucursal,        $idEmpresa,
										$idejer,         		$idprdo,           	$id_factura,
										$forma_pago,     		'$cuen_cod_cuen',  	$dias,
										$porcentaje,    		$valor,            	'$fecha_inicio',
										'$fecha_fin',    		'$fpag_cot',       	'$ret',
										$porc_ret,     		$base,           	'$cheque', 
										'$clpv_nom') ";
		$oConexion->QueryT($sql_fxfp);
		
		/*//codigo serial del saefact
		$sql = "select fact_cod_fact from saefact where
				fact_num_preimp = '$secuencial_real' and
				fact_cod_empr = $idEmpresa and
				fact_cod_sucu = $idSucursal and 
				fact_cod_clpv = $cliente and 
				fact_est_fact = '$estado_fact'";
		$fact_cod_fact = consulta_string($sql, 'fact_cod_fact', $oConexion, 0);*/
		
		return 1;
							
	}
	
	function detalleFormaPago($oConexion, $id, $idEmpresa, $idSucursal, $idFxfp, $idejer, $idprdo, $id_factura, $banco, $girador, $fecha_fin, $cuenta, $cheque, $valor){
		
		$sql_id = "select max(bafp_cod_bafp) as maximo from saebafp ";
		$id_bafp = consulta_string_func($sql_id, 'maximo', $oConexion, 0);
		$valor=str_replace(',','',$valor);
		$valor=str_replace("'","",$valor);
		$sql_saebafp = "insert into saebafp (bafp_cod_bafp,    	bafp_cod_fxfp,   bafp_cod_sucu,
											 bafp_cod_empr,    	bafp_cod_ejer,   bafp_num_prdo,
											 bafp_cod_fact,    	bapg_nom_banc,   bapg_nom_gira,
											 bapg_fec_venc,    	bapg_num_ctab,   bapg_num_cheq,
											 bapg_val_mont)
									 values (($id_bafp+1),    	($id+1),   		$idSucursal,
											$idEmpresa,     	 $idejer,  		$idprdo,
											$id_factura,  		'$banco', 		'$girador',
											'$fecha_fin',    	'$cuenta', 		'$cheque',
											$valor ) ";
		$oConexion->QueryT($sql_saebafp);
		
		return 'OK';
					
	}
	
	function estadoCuenta($oConexion, $idEmpresa, $idSucursal, $idejer, $dmcc_cod_modu, $modu_cod_modu, $moneda, $cliente, $tran, $dmcc_num_fac, $fecha_fin, $fecha_inicio, 
							$detalle, $valor, $estado, $cero, $id_factura, $val_tcambio){
								
		$sql_dmcc = "insert into saedmcc(dmcc_cod_empr,    dmcc_cod_sucu,     dmcc_cod_ejer,
										 dmcc_cod_modu,    modu_cod_modu,     dmcc_cod_mone,
										 clpv_cod_clpv,    dmcc_cod_tran,     dmcc_num_fac,
										 dmcc_fec_ven,     dmcc_fec_emis,     dmcc_det_dmcc,
										 dmcc_mon_ml,      dmcc_mon_ext,      dmcc_est_dmcc,
										 dmcc_deb_ml,      dmcc_cre_ml,       dmcc_cod_fact,
										 dmcc_val_coti,    dmcc_deb_mext,     dmcc_cre_mext,
										 dmcc_mov_sucu )
								values ( $idEmpresa,       $idSucursal,       $idejer,
										$dmcc_cod_modu,   $modu_cod_modu,     $moneda,
										$cliente,         '$tran',           '$dmcc_num_fac',
										'$fecha_fin',     '$fecha_inicio',   '$detalle',
										$valor,           $valor,            '$estado',
										$valor,           $cero,              $id_factura,
										$val_tcambio,     $valor,             $cero,
										$idSucursal  ) ";
		$oConexion->QueryT($sql_dmcc);
		
		return 'OK';
					
	}
	
}
?>
