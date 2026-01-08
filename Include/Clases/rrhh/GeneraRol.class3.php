<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class GeneraRol{
	
	var $oConexion; 
	var $oConexion2; 
	
	function Generar($oConexion, $oConexion2,$empleado,$departamento,  $departamento_calculo,$fecha_ingreso,   $empresa, $fecha,  $fecha_salida,  $array_rubros_ingresos,
					$array_rubros_descuentos, $array_rubros_provision,    $array_rubros_liquidacion, $control_amem,  $empl_sal_sala,  $empl_cam_htra, $empl_jor_trab,
					$periodo_ingreso, $forma_calculo,  $array_tnov){
		
		/// fecha rol
	
		$array_fecha = explode('/', $fecha);
		$mes = $array_fecha[0];
		$dia = $array_fecha[1];
		$anio = $array_fecha[2];
	
		$fecha_liquidacion = $mes . '/' . $dia . '/' . $anio;
		$fecha_inicion_mes = $mes . '/1/' . $anio;
		$fecha_liqui = $anio . '-' . $mes . '-' . $dia;
		$hora_liqui = date('H:i:s');
		$fec_liquidacion = $fecha_liqui . ' ' . $hora_liqui;
		$fecha_real = date('Y-m-d');
		$periodo = $anio . $mes;
	
		//ulitmo dia mes
		$ultimo_dia = date("d", (mktime(0, 0, 0, $mes + 1, 1, $anio) - 1));
	
		//fecha con ultimo_dia
		$fecha_novedad = $mes . '/' . $ultimo_dia . '/' . $anio;

		if ($departamento_calculo == 'C') {
			$dias_mes = 30;
		}else{
			$dias_mes=$ultimo_dia;

		}
		if ($periodo == $periodo_ingreso) {
			$dias_falta = abs($dia - $dia_ingreso) + 1;
		} 
		else {
			
			if($ultimo_dia==$dia){
				$dias_falta = $dias_mes;
			}else{
				 $dias_falta = $dia;
			}
		}
		
		if ($forma_calculo == 'H') {
			$dias_falta = $dias_falta * 8;
		}
		if( $dias_falta>'240'){
			$dias_falta='240';
		}
		/// control amem
		$fecha_amem_n=$fecha_liquidacion;
		if(!empty($fecha_salida)){
			$fecha_amem_n=$fecha_salida;
		}
		//// saenove

		$fec_nove=fecha_mysql_func($fecha_amem_n);
		$arra_f_nove=explode('/', $fec_nove);
		$fec_nove= $arra_f_nove[2].'-'.$arra_f_nove[1].'-'.$arra_f_nove[0]. ' 00:00:00';
		$fec_nove2= $arra_f_nove[2].'-'.$arra_f_nove[1].'-'.$arra_f_nove[0]. ' 23:59:59';

		
		$sql="select sum(nove_num_hora) as horas, nove_cod_tnov from saenove
				 where nove_cod_empl='$empleado' and nove_cod_empr='$empresa' and
				 nove_fec_carga='$fecha_amem_n'  group by nove_cod_tnov";
		
		if($oConexion->Query($sql)){
			if($oConexion->NumFilas()>0){
				do{
					$nove_cod_tnov=$oConexion->f('nove_cod_tnov');
					$horas=$oConexion->f('horas');

					if($horas>0){
						$campo=$array_tnov[$nove_cod_tnov];
						if($campo!=''){

							$upt="update saeamem set $campo='$horas' where  amem_cod_empr=$empresa AND amem_cod_empl='$empleado'and amem_fec_amem='$fecha_liquidacion'";
							$oConexion2->QueryT($upt);
						}
					}

				}while($oConexion->SiguienteRegistro());
			}
		}

		if ($control_amem == 0) {
			$sql = "SELECT MAX(amem_fec_amem) as amem_fec_fina FROM saeamem WHERE amem_cod_empr=$empresa AND amem_cod_empl='$empleado' AND DATE_PART('year', amem_fec_amem)='$anio' and month(amem_fec_amem)='$mes'" ;
			$amem_fec_hast = consulta_string($sql, 'amem_fec_fina', $oConexion, 0);
			
			if($amem_fec_hast!=0){
				$array_fecha_amem=explode('/',$amem_fec_hast);
				
				$mes_amem = $array_fecha_amem[0];
				$dia_ultimo = $array_fecha_amem[1];
				$anio_amem = $array_fecha_amem[2];
				if($dia>30){
					$dia=30;
				}
				$dias_falta = abs($dia - $dia_ultimo);
				$dias_falta = $dias_falta * 8;
				$fecha_amem_n=$mes_amem.'/'.$dia_ultimo.'/'.$anio_amem;
			}else{
				$amem_fec_hast=$fecha_liquidacion;
			}
			
			if(!empty($fecha_salida)){
				$array_fecha_amem=explode('/',$fecha_salida);
				
				$mes_amem = $array_fecha_amem[0];
				$dia_ultimo = $array_fecha_amem[1];
				$anio_amem = $array_fecha_amem[2];
				if($dia>30){
					$dia=30;
				}
				//$dias_falta = abs($dia - $dia_ultimo);
				$dias_falta = $dia_ultimo * 8;
				$fecha_amem_n=$mes_amem.'/'.$dia_ultimo.'/'.$anio_amem;
			}
			$insert = "INSERT INTO saeamem (amem_cod_empr, amem_cod_empl, amem_fec_amem, amem_fec_inic , amem_fec_fina , amem_est_amem, amem_cod_estr )
							VALUES ('$empresa','$empleado', '$fecha_liquidacion', '$amem_fec_hast', '$fecha_liquidacion', '0', '$cargo')";
			
			$oConexion->QueryT($insert);
			$horas_menos = 0;
		} else {
			$sql = "SELECT SUM(amem_hor_trab-amem_hor_lice+amem_hor_atra + amem_hor_falt + amem_hor_sein + amem_hor_perm + amem_hor_mate) as horas_menos FROM saeamem WHERE amem_cod_empr=$empresa AND amem_cod_empl='$empleado' AND amem_fec_amem='$fecha_amem_n' AND amem_est_amem = 0";
			echo $sql;exit;
			$dias_falta = consulta_string($sql, 'horas_menos', $oConexion, 0);
			

		}
		
		//// update a saeamem
		if ((empty($empl_cam_htra)) || ($empl_cam_htra == '') || ($empl_cam_htra = 'N')) {
                     
			$update = "UPDATE saeamem SET amem_hor_trab='$dias_falta' WHERE amem_cod_empr='$empresa' AND amem_cod_empl='$empleado' AND  amem_fec_amem='$fecha_liquidacion' AND amem_est_amem = 0";
			$oConexion->QueryT($update);
		}

		// prestamos del empleado
		unset($array_prestamos);
		$sql = "select tpre_cod_rubr, rubr_des_rubr,  sum(cuot_mot_capi) as valor, sum(cuot_mot_inte)as inte
					from saepret, saecuot, saetpre, saerubr
					where pret_cod_pret = cuot_cod_pret
							and pret_cod_tpre = tpre_cod_tpre
							and pret_cod_empr = tpre_cod_empr
							and tpre_cod_rubr = rubr_cod_rubr
							and tpre_cod_empr = rubr_cod_empr
							and pret_cod_empl = '$empleado'
							and pret_cod_empr = '$empresa'
							and cuot_est_cuot = '0'
							and cuot_fec_venc = '$fecha_novedad'
					group by tpre_cod_rubr, rubr_des_rubr";
		
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				do {
					$tpre_cod_rubr = $oConexion->f('tpre_cod_rubr');
					$rubr_des_rubr = $oConexion->f('rubr_des_rubr');
				   
					$valor = $oConexion->f('valor');
					$inte = $oConexion->f('inte');
					$valor = $valor + $inte;
					$array_prestamos[] = array($tpre_cod_rubr, $rubr_des_rubr, $valor);
				} while ($oConexion->SiguienteRegistro());
			}
		}

		//pagos realizados del rol
		unset($array_pago_rol);
		$sql = "  SELECT saepago.pago_cod_rubr,   
						saepago.pago_cod_empl,   
						saepago.pago_cod_empr,   
						saepago.pago_per_pago,   
						saepago.pago_val_pago,   
						saepago.pago_fec_liqu,   
						saepago.pago_ori_gene,   
						saepago.pago_fec_real,   
						saepago.pago_cod_estr  
			   FROM saepago  
		  WHERE ( saepago.pago_cod_empr = $empresa ) AND  
						( saepago.pago_per_pago = '$periodo') AND  
						( saepago.pago_cod_empl = '$empleado') AND  
						( saepago.pago_ori_gene = 'R' ) AND 
						( saepago.pago_cod_estr = '$departamento' ) and 
						( saepago.pago_fec_real = '$fecha_liquidacion')";
	//	echo $sql;exit;
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				do {
					$pago_cod_rubr = $oConexion->f('pago_cod_rubr');
					$pago_val_pago = $oConexion->f('pago_val_pago');
					$pago_cod_estr = $oConexion->f('pago_cod_estr');
					$valor = $oConexion->f('valor');
					$array_pago_rol[] = array($pago_cod_rubr, $pago_val_pago, $pago_cod_estr);
				} while ($oConexion->SiguienteRegistro());
			}
		}


		//datos de la pemp
		unset($array_pemp);
		$sql = "  SELECT saepemp.pemp_cod_empl,   
						saepemp.pemp_cod_empr,   
						saepemp.pemp_cod_pnom,   
						saepemp.pemp_per_pemp,   
						saepemp.pemp_val_mese,   
						saepemp.pemp_val_paga,   
						saepemp.pemp_val_vaca,
						saepemp.pemp_val_liqu,   
						saepemp.pemp_fec_liqu,   
						saepemp.pemp_est_pemp,   
						saepemp.pemp_ori_gene  
			   FROM saepemp  
		  WHERE ( saepemp.pemp_cod_empr = $empresa ) AND  
						( saepemp.pemp_per_pemp = $periodo ) AND  
						( saepemp.pemp_cod_empl = '$empleado' ) AND 
						( saepemp.pemp_cod_estr = '$departamento' )and
						( saepemp.pemp_fec_real = '$fecha_liquidacion')";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				do {
					$pemp_cod_pnom = $oConexion->f('pemp_cod_pnom');
					$pemp_val_mese = $oConexion->f('pemp_val_mese');
					$pemp_val_paga = $oConexion->f('pemp_val_paga');
					$pemp_val_vaca = $oConexion->f('pemp_val_vaca');
					$pemp_val_liqu = $oConexion->f('pemp_val_liqu');
					$pemp_fec_liqu = $oConexion->f('pemp_fec_liqu');
					$pemp_est_pemp = $oConexion->f('pemp_est_pemp');
					$pemp_ori_gene = $oConexion->f('pemp_ori_gene');

					$array_pemp[] = array($pemp_cod_pnom, $pemp_val_mese, $pemp_val_paga, $pemp_val_liqu, $pemp_fec_liqu, $pemp_est_pemp, $pemp_ori_gene);
				} while ($oConexion->SiguienteRegistro());
			}
		}

		//datos de la pemp x pagar
		unset($array_pempx);
		$sql = "    SELECT saepemp.pemp_cod_empl,   
							saepemp.pemp_cod_empr,   
							saepemp.pemp_cod_pnom,   
							saepemp.pemp_per_pemp,   
							saepemp.pemp_val_mese,   
							saepemp.pemp_val_paga,   
							saepemp.pemp_val_vaca,   
							saepemp.pemp_val_liqu,   
							saepemp.pemp_fec_liqu,   
							saepemp.pemp_est_pemp,   
							saepemp.pemp_ori_gene  
					FROM saepemp  
					WHERE ( saepemp.pemp_cod_empr = $empresa ) AND  
							( saepemp.pemp_cod_empl = '$empleado' ) AND  
							( saepemp.pemp_est_pemp <> '2' ) AND 
							( saepemp.pemp_cod_estr = '$departamento' )";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				do {
					$pemp_cod_pnom = $oConexion->f('pemp_cod_pnom');
					$pemp_val_mese = $oConexion->f('pemp_val_mese');
					$pemp_val_paga = $oConexion->f('pemp_val_paga');
					$pemp_val_vaca = $oConexion->f('pemp_val_vaca');
					$pemp_val_liqu = $oConexion->f('pemp_val_liqu');
					$pemp_fec_liqu = $oConexion->f('pemp_fec_liqu');
					$pemp_est_pemp = $oConexion->f('pemp_est_pemp');
					$pemp_ori_gene = $oConexion->f('pemp_ori_gene');

					$array_pempx[] = array($pemp_cod_pnom, $pemp_val_mese, $pemp_val_paga, $pemp_val_liqu, $pemp_fec_liqu, $pemp_est_pemp, $pemp_ori_gene);
				} while ($oConexion->SiguienteRegistro());
			}
		}

   
		if (!empty($array_pemp)) {
			$sql = "DELETE saepemp  WHERE ( saepemp.pemp_cod_empr = $empresa ) AND  
										( saepemp.pemp_per_pemp = $periodo ) AND  
										( saepemp.pemp_cod_empl = '$empleado' ) AND 
										( saepemp.pemp_cod_estr = '$departamento' ) and 
										( saepemp.pemp_fec_real = '$fecha_liquidacion')";
			$oConexion->QueryT($sql);
		}
		$ban = true;
	           
		if ($array_pago_rol!='') {
			$sql = "DELETE saepago  WHERE ( saepago.pago_cod_empr = $empresa ) AND
										( saepago.pago_per_pago = '$periodo') AND
										( saepago.pago_cod_empl = '$empleado') AND
										( saepago.pago_ori_gene = 'R' ) AND
									
										( saepago.pago_fec_real = '$fecha_liquidacion')";
		
			$oConexion->QueryT($sql);
		}



		unset($_SESSION['rrhh_formula']);

		// INGRESOS
	
		if($array_rubros_ingresos!='') {
		//	var_dump($array_rubros_ingresos);
			foreach ($array_rubros_ingresos as $arreglo) {
			
				$rubro = $arreglo[0];
				
				$valor = f_resuelve_formula($rubro, $empleado, $fecha_amem_n, $empresa, $fecha_liquidacion);
				
				if ($valor != 0) {

					$insert = "INSERT INTO saepago(pago_cod_empr,pago_cod_empl, pago_per_pago, pago_cod_rubr, pago_val_pago,  pago_ori_gene, pago_fec_real, pago_cod_estr)
										VALUES($empresa,'$empleado',$periodo, '$rubro',$valor, 'R', '$fecha_liquidacion', '$departamento')";
					$oConexion->QueryT($insert);
				
					$insert_tmp = "INSERT INTO tmp_pago_nomina
										VALUES( '$rubro','$empleado','$empresa',$valor, '$periodo')";
					$oConexion->QueryT($insert_tmp);
				}
				
				
			}
	
		} 
		

		$sql = "SELECT amem_hor_vaca FROM saeamem WHERE amem_cod_empr=$empresa AND amem_cod_empl='$empleado' AND amem_fec_amem='$fecha_novedad' AND amem_est_amem = 0";
		$amem_hor_vaca = consulta_string($sql, 'amem_hor_vaca', $oConexion, 0);
		if($amem_hor_vaca=='0'){
			$sql = "SELECT amem_hor_vaca FROM saeamem WHERE amem_cod_empr=$empresa AND amem_cod_empl='$empleado' AND amem_fec_amem='$fecha_novedad' AND amem_est_amem = 0";
			$amem_hor_vaca = consulta_string($sql, 'amem_hor_vaca', $oConexion, 0);
		}
		
		 // PROVISIONES
		 if($array_rubros_provision){
			foreach ($array_rubros_provision as $arreglo) {
				$rubro = $arreglo[0];    
				$cod_pnom = $arreglo[3];    
			
				$valor = f_resuelve_formula($rubro, $empleado, $fecha_amem_n, $empresa, $fecha_liquidacion);
				
				$horas_rubr=0;
				if (($valor != 0) && ($cod_pnom != 0)) {
					if($rubro=='RVACA'){
						
						$anios =intval(( dias_diferencia($fecha_real , $fecha_ingreso))/365);
					//	echo $anios;exit;
						if($anios>5){
						
							$empl_jor_trab=(($empl_jor_trab)/30)*$anios;
							
						}
						$horas_rubr=(($empl_jor_trab/30)*15)/12;
						
					
					}
					$insert = "INSERT INTO saepemp(pemp_cod_empr, pemp_cod_pnom, pemp_per_pemp, pemp_val_mese,  pemp_ori_gene, pemp_cod_empl,
												pemp_est_pemp,  pemp_cod_estr, pemp_fec_real, pemp_hor_vaca)
										VALUES($empresa, $cod_pnom, '$periodo', '$valor',  'R', '$empleado',
												0,  '$departamento',  '$fecha_liquidacion', '$horas_rubr')";
					$oConexion->QueryT($insert);
					
				}
			}
		}
		  //DESCUENTOS
		if($array_rubros_descuentos!=''){
		
			foreach ($array_rubros_descuentos as $arreglo) {
		  
				$rubro = $arreglo[0];
				$valor = f_resuelve_formula($rubro, $empleado, $fecha_amem_n, $empresa, $fecha_liquidacion);
				
				if ($valor != 0) {
					$insert = "INSERT INTO saepago(pago_cod_empr,pago_cod_empl, pago_per_pago, pago_cod_rubr, pago_val_pago,  pago_ori_gene, pago_fec_real, pago_cod_estr)
									VALUES($empresa,'$empleado',$periodo, '$rubro',$valor, 'R', '$fecha_liquidacion', '$departamento')";
					$oConexion->QueryT($insert);
				
				}
			}
		}
		  
		 ///    PRESTAMOS
		 if (!empty($array_prestamos)) {
			foreach ($array_prestamos as $arreglo) {
				$rubro = $arreglo[0];
				$valor = $arreglo[2];
			   
				$sql = "SELECT count(*) as contador
						FROM saepago
						WHERE 
							pago_cod_empl = '$empleado'
						   and pago_cod_empr = '$empresa'
						   and pago_cod_rubr = '$rubro'
						   and pago_cod_estr ='$departamento'
						   and pago_per_pago='$periodo'
						   and pago_ori_gene='R'";
				$control = consulta_string($sql, 'contador', $oConexion, 0);

				if ($control == 0) {

					$insert = "INSERT INTO saepago(pago_cod_empr,pago_cod_empl, pago_per_pago, pago_cod_rubr, pago_val_pago,  pago_ori_gene, pago_fec_real, pago_cod_estr)
									VALUES($empresa,'$empleado',$periodo, '$rubro',$valor, 'R', '$fecha_liquidacion', '$departamento')";
					$oConexion->QueryT($insert);
				}
				
				
			}
			
			$sql="select * from saepret where pret_cod_empl='$empleado' and pret_cod_empr='$empresa'";
			if($oConexion->Query($sql)){
				if($oConexion->NumFilas()>0){
					do{
						$pret_cod_pret=$oConexion->f('pret_cod_pret');
						$upt="update saecuot set cuot_est_cuot=1 where cuot_cod_pret='$pret_cod_pret' and cuot_fec_venc='$fecha_liquidacion'";
						$oConexion2->QueryT($upt);
					}while($oConexion->SiguienteRegistro());
				}
			}
			
		}
		$update ="UPDATE saeamem SET amem_est_amem=1 WHERE amem_cod_empl='$empleado' AND amem_est_amem=0 AND amem_cod_estr='$cargo'";
		$oConexion->QueryT($update);

		$array="Rol Generado!! Empleado";

		
		return $array;
		
	}
	
	
}
?>