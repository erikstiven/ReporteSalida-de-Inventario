<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class Empleados{
	
	var $oConexion; 
	
	function consultarEmpleado($oConexion, $idEmpresa, $sql_condicion){
		
		$sql="select   empl_cam_htra, empl_ape_empl, empl_nom_empl, empl_ape_nomb, empl_cod_estc, empl_dir_empl, empl_mai_empl, empl_cod_sexo, empl_cod_eemp,
                    empl_seg_sala, empl_hor_extr, empl_des_impu, empl_cod_empr, empl_cod_empl, empl_cod_sucu,  empl_cod_tmil,
                    empl_cod_alt,empl_fot_empl, empl_fir_empl, empl_car_iess, empl_cod_iess, empl_cod_pdie,
                    empl_lib_mili,empl_sal_sala, empl_cod_mone, empl_cod_tcon, empl_num_cont, empl_cod_tpag, empl_cod_banc, empl_cod_tcta,
                    empl_num_ctas, empl_cod_inst,  empl_dir_nume, empl_val_eval, empl_num_hora, empl_val_hora, empl_val1_empl,
                    empl_val2_empl, empl_val3_empl, empl_val4_empl, empl_val5_empl, empl_val6_empl, empl_cod_titr, empl_val_adic, empl_cod_banc1,empl_fec_naci,
                    empl_cod_tcta1, empl_num_ctas1, empl_val_quinc, empl_tip_tide, empl_cod_paisp, empl_cod_disc, empl_por_disc, empl_val_exon, empl_cod_ciud, empl_cod_tsan,
                    empl_num_npat, empl_cod_parr, empl_fal_empl, empl_pen_alim, empl_cod_resi, empl_cod_conv, empl_cod_tnom, empl_cod_ssnt,
                    empl_rem_tide, empl_rem_empl, empl_por_reem, empl_anti_empl, empl_sala_neto, empl_por_anti, empl_jor_trab,empl_val_neto, empl_cod_etni, empl_nom_apod
            from saeempl
			where
				 empl_cod_empr='$idEmpresa'  $sql_condicion order by empl_ape_empl";
		
		//echo $sql;
		if ($oConexion->Query($sql)) {
			unset($array);
			if ($oConexion->NumFilas() > 0) {
				do{
							
					$empl_cod_empl = $oConexion->f('empl_cod_empl');
					$empl_ape_empl = $oConexion->f('empl_ape_empl');
					$empl_nom_empl = $oConexion->f('empl_nom_empl');
					$empl_ape_nomb = $oConexion->f('empl_ape_nomb');
					$empl_cod_estc = $oConexion->f('empl_cod_estc');
					$empl_dir_empl = $oConexion->f('empl_dir_empl');
					$empl_mai_empl = $oConexion->f('empl_mai_empl');
					$empl_cod_sexo = $oConexion->f('empl_cod_sexo');
					$empl_cod_eemp = $oConexion->f('empl_cod_eemp');
					$empl_seg_sala = $oConexion->f('empl_seg_sala');
					$empl_hor_extr = $oConexion->f('empl_hor_extr');
					$empl_des_impu = $oConexion->f('empl_des_impu');
					$empl_cod_alt  = $oConexion->f('empl_cod_alt');
					$empl_cod_tsan = $oConexion->f('empl_cod_tsan');
					$empl_fec_naci = $oConexion->f('empl_fec_naci');
					$empl_cod_ciud = $oConexion->f('empl_cod_ciud');
					$empl_fot_empl = $oConexion->f('empl_fot_empl');
					$empl_fir_empl = $oConexion->f('empl_fir_empl');
					$empl_car_iess = $oConexion->f('empl_car_iess');
					$empl_num_npat = $oConexion->f('empl_num_npat');
					$empl_cod_pdie = $oConexion->f('empl_cod_pdie');
					$empl_cod_tmil = $oConexion->f('empl_cod_tmil');
					$empl_lib_mili = $oConexion->f('empl_lib_mili');
					$empl_sal_sala = $oConexion->f('empl_sal_sala');
					$empl_cod_mone = $oConexion->f('empl_cod_mone');
					$empl_cod_tcon = $oConexion->f('empl_cod_tcon');
					$empl_num_cont = $oConexion->f('empl_num_cont');
					$empl_cod_tpag = $oConexion->f('empl_cod_tpag');
					$empl_cod_banc = $oConexion->f('empl_cod_banc');
					$empl_cod_tcta = $oConexion->f('empl_cod_tcta');
					$empl_num_ctas = $oConexion->f('empl_num_ctas');
					$empl_cod_inst = $oConexion->f('empl_cod_inst');
					$empl_dir_nume = $oConexion->f('empl_dir_nume');
					$empl_val_eval = $oConexion->f('empl_val_eval');
					$empl_num_hora = $oConexion->f('empl_num_hora');
					$empl_val_hora = $oConexion->f('empl_val_hora');
					$empl_val1_empl	 = $oConexion->f('empl_val1_empl');
					$empl_val2_empl	 = $oConexion->f('empl_val2_empl');
					$empl_val3_empl	 = $oConexion->f('empl_val3_empl');
					$empl_val4_empl	 = $oConexion->f('empl_val4_empl');
					$empl_val5_empl	 = $oConexion->f('empl_val5_empl');
					$empl_val6_empl	 = $oConexion->f('empl_val6_empl');
					$empl_cod_titr = $oConexion->f('empl_cod_titr');
					$empl_val_adic = $oConexion->f('empl_val_adic');
					$empl_cod_banc1 = $oConexion->f('empl_cod_banc1');
					$empl_cod_tcta1 = $oConexion->f('empl_cod_tcta1');
					$empl_num_ctas1 = $oConexion->f('empl_num_ctas1');
					$empl_val_quinc = $oConexion->f('empl_val_quinc');
					$empl_tip_tide = $oConexion->f('empl_tip_tide');
					$empl_nom_apod = $oConexion->f('empl_nom_apod');
					$empl_cod_paisp = $oConexion->f('empl_cod_paisp');
					$empl_cod_disc = $oConexion->f('empl_cod_disc');
					$empl_por_disc = $oConexion->f('empl_por_disc');
					$empl_val_exon = $oConexion->f('empl_val_exon');
					$empl_rem_tide = $oConexion->f('empl_rem_tide');
					$empl_por_reem = $oConexion->f('empl_por_reem');
					$empl_cod_parr  = $oConexion->f('empl_cod_parr');
					$empl_cod_iess = $oConexion->f('empl_cod_iess');
					$empl_fal_empl  = $oConexion->f('empl_fal_empl');
					$empl_pen_alim  = $oConexion->f('empl_pen_alim');
					$empl_anti_empl  = $oConexion->f('empl_anti_empl');
					$empl_cam_htra  = $oConexion->f('empl_cam_htra');
					$empl_cod_resi  = $oConexion->f('empl_cod_resi');
					$empl_cod_conv  = $oConexion->f('empl_cod_conv');
					$empl_cod_tnom  = $oConexion->f('empl_cod_tnom');
					$empl_cod_ssnt  = $oConexion->f('empl_cod_ssnt');
					$empl_rem_empl  = $oConexion->f('empl_rem_empl');
					$empl_cod_empr  = $oConexion->f('empl_cod_empr');
					$empl_sala_neto  = $oConexion->f('empl_sala_neto');
					$empl_por_anti  = $oConexion->f('empl_por_anti');
					$empl_jor_trab  = $oConexion->f('empl_jor_trab');
					$empl_val_neto  = $oConexion->f('empl_val_neto');
					$empl_cod_etni  = $oConexion->f('empl_cod_etni');
					$empl_cod_sucu  = $oConexion->f('empl_cod_sucu');
				
					$array[$empl_cod_empl]=array(
						"empl_cod_empl"=>$empl_cod_empl,
						"empl_cam_htra"=>$empl_cam_htra,
						"empl_ape_empl"=>$empl_ape_empl,
						"empl_nom_empl"=>$empl_nom_empl,
						"empl_ape_nomb"=>$empl_ape_nomb, 
						"empl_cod_estc"=>$empl_cod_estc, 
						"empl_dir_empl"=>$empl_dir_empl,
						"empl_mai_empl"=>$empl_mai_empl,
						"empl_cod_sexo"=>$empl_cod_sexo,
						"empl_cod_eemp"=>$empl_cod_eemp,
						"empl_seg_sala"=>$empl_seg_sala,
						"empl_hor_extr"=>$empl_hor_extr,
						"empl_des_impu"=>$empl_des_impu,
						"empl_cod_empr"=>$empl_cod_empr, 
						"empl_cod_sucu"=>$empl_cod_sucu, 
						"empl_cod_tmil"=>$empl_cod_tmil,
						"empl_cod_alt"=>$empl_cod_alt,
						"empl_fot_empl"=>$empl_fot_empl,
						"empl_fir_empl"=>$empl_fir_empl,
						"empl_car_iess"=>$empl_car_iess,
						"empl_cod_iess"=>$empl_cod_iess,
						"empl_cod_pdie"=>$empl_cod_pdie,
						"empl_lib_mili"=>$empl_lib_mili, 
						"empl_sal_sala"=>$empl_sal_sala, 
						"empl_cod_mone"=>$empl_cod_mone,
						"empl_cod_tcon"=>$empl_cod_tcon,
						"empl_num_cont"=>$empl_num_cont,
						"empl_cod_tpag"=>$empl_cod_tpag,
						"empl_cod_banc"=>$empl_cod_banc,
						"empl_cod_tcta"=>$empl_cod_tcta,
						"empl_num_ctas"=>$empl_num_ctas,
						"empl_cod_inst"=>$empl_cod_inst, 
						"empl_dir_nume"=>$empl_dir_nume, 
						"empl_val_eval"=>$empl_val_eval,
						"empl_num_hora"=>$empl_num_hora,
						"empl_val_hora"=>$empl_val_hora,
						"empl_val1_empl"=>$empl_val1_empl,
						"empl_val2_empl"=>$empl_val2_empl,
						"empl_val3_empl"=>$empl_val3_empl,
						"empl_val4_empl"=>$empl_val4_empl,
						"empl_val5_empl"=>$empl_val5_empl,
						"empl_val6_empl"=>$empl_val6_empl, 
						"empl_cod_titr"=>$empl_cod_titr, 
						"empl_val_adic"=>$empl_val_adic,
						"empl_cod_banc1"=>$empl_cod_banc1,
						"empl_fec_naci"=>$empl_fec_naci,
						"empl_cod_tcta1"=>$empl_cod_tcta1,
						"empl_num_ctas1"=>$empl_num_ctas1,
						"empl_val_quinc"=>$empl_val_quinc,
						"empl_tip_tide"=>$empl_tip_tide,
						"empl_cod_paisp"=>$empl_cod_paisp, 
						"empl_cod_disc"=>$empl_cod_disc, 
						"empl_val_exon"=>$empl_val_exon,
						"empl_cod_ciud"=>$empl_cod_ciud,
						"empl_cod_tsan"=>$empl_cod_tsan,
						"empl_num_npat"=>$empl_num_npat,
						"empl_cod_parr"=>$empl_cod_parr,
						"empl_fal_empl"=>$empl_fal_empl,
						"empl_pen_alim"=>$empl_pen_alim,
						"empl_cod_resi"=>$empl_cod_resi, 
						"empl_cod_conv"=>$empl_cod_conv, 
						"empl_cod_tnom"=>$empl_cod_tnom,
						"empl_cod_ssnt"=>$empl_cod_ssnt,
						"empl_rem_tide"=>$empl_rem_tide,
						"empl_rem_empl"=>$empl_rem_empl,
						"empl_por_reem"=>$empl_por_reem,
						"empl_anti_empl"=>$empl_anti_empl,
						"empl_sala_neto"=>$empl_sala_neto,
						"empl_por_anti"=>$empl_por_anti, 
						"empl_jor_trab"=>$empl_jor_trab, 
						"empl_val_eval"=>$empl_val_eval,
						"empl_cod_etni"=>$empl_cod_etni,
						"empl_nom_apod"=>$empl_nom_apod,
						
					);	
					
				
				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		return $array;
	}
	
	
}
?>