<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class GeneraRol{

    var $oConexion;
    var $oConexion2;

    function Generar($oConexion, $oConexion2,$empleado,$departamento,  $departamento_calculo,$fecha_ingreso,   $empresa, $fecha,  $fecha_salida,  $array_rubros_ingresos,
                     $array_rubros_descuentos, $array_rubros_provision,    $array_rubros_liquidacion, $control_amem,  $empl_sal_sala,  $empl_cam_htra, $empl_jor_trab,
                     $periodo_ingreso, $forma_calculo, $array_prestamos, $array_tnov,$amem_cod_estr, $estr_cod_ppag){

        $cargo=$amem_cod_estr;


        //MENSUAL
        if($estr_cod_ppag==1){
            $jornada_horas_diarias=$empl_jor_trab/30;
        }

        //QUINCENAL
        if($estr_cod_ppag==2){
            $jornada_horas_diarias=$empl_jor_trab/30/2;
        }

        //SEMANAL
        if($estr_cod_ppag==3){
            $jornada_horas_diarias=$empl_jor_trab/30/4;
        }

//    DIARIO
        if($estr_cod_ppag==4){
            $jornada_horas_diarias=$empl_jor_trab/30/30;
        }


        //var_dump($jornada_horas_diarias);exit;
        /// fecha rol
        //var_dump($fecha);exit;
        $array_fecha = explode('-', $fecha);
        $anio = $array_fecha[0];
        $mes = $array_fecha[1];
        $dia = $array_fecha[2];

        $fecha_liquidacion = $anio .'-'. $mes . '-' . $dia;
        $fecha_inicion_mes = $anio .'-'. $mes .'-01';
        $fecha_liqui = $anio . '-' . $mes . '-' . $dia;
        $hora_liqui = date('H:i:s');
        $fec_liquidacion = $fecha_liqui . ' ' . $hora_liqui;
        $fecha_real = date('Y-m-d');
        $periodo = $anio . $mes;

        //ulitmo dia mes
        $ultimo_dia = date("d", (mktime(0, 0, 0, $mes + 1, 1, $anio) - 1));

        //fecha con ultimo_dia
        $amem_fec_amem = $fecha ;

        if ($departamento_calculo == 'C') {
            $dias_mes = 30;
        }else{
            $dias_mes=$ultimo_dia;

        }
        //echo $periodo.'----'. $periodo_ingreso;exit;
        $dias_falta=0;
        $horas_falta=0;
        if ($periodo == $periodo_ingreso) {
            $fecha_ingreso1 = explode('-', $fecha_ingreso);
            //echo  $fecha_ingreso;exit;
            $dia_ingreso = $fecha_ingreso1[2];
            if($dia>30){
                $dia=30;
            }elseif($mes==02){
                $dia=30;
            }
            	//echo $dia.'--'.$dia_ingreso;exit;
            $dias_falta = abs($dia_ingreso-1) ;
        }else{
			$total_dias_menos=0;
			//Si tiene fecha de salida calculamos los dias a restar
            if(!empty($fecha_salida)){

                $dias_restar=explode('-', $fecha_salida);
                $dias_restar=30-$dias_restar[2];
                //var_dump($fecha_liquidacion); var_dump($fecha_salida); var_dump($dias_restar);var_dump($dialq);
                if($dias_restar>0){$dias_falta=$dias_restar;}
            }
		}

        //var_dump($dia_ingreso);   var_dump($forma_calculo);        var_dump($horas_falta);        var_dump($empl_jor_trab);        var_dump($dias_mes);		var_dump($dias_falta);  var_dump($jornada_horas_diarias);exit;
        if ($forma_calculo == 'H') {
            $horas_falta = $empl_jor_trab-(($dias_mes-$dias_falta)*$jornada_horas_diarias);
            if ($horas_falta<0)$horas_falta=0;
        }
        //echo 'dias_falta: '.$dias_falta;exit;


        if( $horas_falta>$empl_jor_trab){
            $horas_falta=$empl_jor_trab;
        }
        //var_dump($horas_falta);exit;
        //echo 'dias_falta: '.$dias_falta;
        /// control amem
        $fecha_amem_n=$amem_fec_amem;

        if(!empty($fecha_salida)){
            $fecha_amem_n=$fecha_salida;

        }
        //// saenove

        $sql="SELECT * FROM saenove WHERE nove_cod_empl = '$empleado' and nove_cod_empr='$empresa' and (nove_fec_desde BETWEEN '".$anio."-".$mes."-01' and '$fecha_amem_n' OR nove_fec_hasta BETWEEN '".$anio."-".$mes."-01' and '$fecha_amem_n') order by nove_cod_tnov";
        //var_dump($sql);exit;



        //var_dump($sql);
        if ($oConexion->Query($sql)) {
            if ($oConexion->NumFilas() > 0) {

                $contador_novedades=0;
                $tnove=Array();
                $jornada=$empl_jor_trab;
                $tipo_nove_ant='';
                $total_ant=0;
                do{
                    $nove_cod_tnov=$oConexion->f('nove_cod_tnov');
                    $sql = "select tnov_cam_amem from saetnov where tnov_cod_tnov = '".$nove_cod_tnov."'";
                    $tipo_nove = consulta_string($sql, 'tnov_cam_amem', $oConexion2, '');

                    $start = new DateTime($oConexion->f('nove_fec_desde'));
                    $end = new DateTime($oConexion->f('nove_fec_hasta'));
                    $nove_conti_sn=$oConexion->f('nove_conti_sn');

                    $fecha_desde=$oConexion->f('nove_fec_desde');
                    $fecha_hasta=$fecha_amem_n;
                    $num_horas=$oConexion->f('nove_num_hora');


                    //MESES TRANSCURRIDOS.
                    //$meses=meses_transcurridos($fecha_desde,$fecha_hasta);
                    $fecha1=$fecha_desde;
                    $mes1 = date("m", strtotime("$fecha1"));
                    $ano1=  date("Y", strtotime("$fecha1"));
                    $fecha2=$fecha_hasta;
                    $mes2 = date("m", strtotime("$fecha2"));
                    $ano2=  date("Y", strtotime("$fecha2"));
                    $limite=$mes2.'/'.$ano2;
                    $nuevo=0;
                    $meses=0;

                    if ($ano1.$mes1<$ano2.$mes2) {
                        while ($nuevo != $limite) {
                            $meses++;
                            $nuevo = date("m/Y", mktime(0, 0, 0, $mes1 + $meses, 1, $ano1));
                        }
                    }
                    //FIN MESES TRANSCURRIDOS



                    //var_dump($oConexion->f('nove_fec_desde') .' <-> '. $oConexion->f('nove_fec_hasta'));
                    if ($nove_conti_sn=='N') {
                        $dias_asume_empresa = 3;
                    }else{
                        $dias_asume_empresa = 0;
                    }
                    //var_dump($dias_proy);
                    //var_dump("dias_asume_empresa: ".$dias_asume_empresa);
                    //var_dump($fecha_desde);
                    $dias_asume=0;
                    $dias1=0;
                    //var_dump('total meses: '.$meses);

                    for ($i=0; $i <= $meses ; $i++) {
                        //var_dump('-------------------------');
                        //var_dump('MES: '.$i);
                        //var_dump($start);
                        //var_dump('mes '.$i.' de '.$meses);
                        $diaultimo= $start->format('t');
                        if ($diaultimo>30)$diaultimo=30;

                        $fecha_ultimo_dia= new DateTime($start->format("Y-m-".$diaultimo));
                        //var_dump($fecha_ultimo_dia);exit;
                        if($i == 0){
                            $dias = round(($end->format('U') - $start->format('U')) / (60*60*24))+1;
                            $ultimo_dia = round(($fecha_ultimo_dia->format('U') - $start->format('U')) / (60*60*24))+1;

                            //var_dump($dias);var_dump($fecha_ultimo_dia);var_dump($start);var_dump('ultimo_dia: '.$ultimo_dia);
                            if ($dias<=$ultimo_dia){
                                $fecha_ultimo_dia=$end;
                            }

                            $dias1 = round(($fecha_ultimo_dia->format('U') - $start->format('U')) / (60*60*24))+1;
                            $dias_asume = $dias1;
                        }else{
                            if ($i<$meses){
                                $dias_asume=30;
                            }
                            if ($i == $meses) {
                                $dias = intval($end->format('d'));
                                if ($dias>30)$dias=30;
                                $dias_asume = $dias;
                            }
                            $dias1=$dias_asume_empresa-$dias1;
                            $dias_asume_empresa=$dias1;
                        }

                        //var_dump('total dias: '.$dias);
                        //var_dump('dias asumidos en este mes: '.$dias1);
                        //var_dump('dias asume: '.$dias_asume);
                        //var_dump('dias asume empresa mes actual: '.$dias_asume_empresa);


                        if ($dias_asume_empresa<0)$dias_asume_empresa=0;
                        $horas1 = ($dias_asume-$dias_asume_empresa) * ($jornada/30);

                        if ($horas1<0){
                            $total=0;
                        }else {
                            $total = $horas1;
                        }

                        //consultar si existe linea saeamem
                        $amem_fec_inic=$start->format('Y-m-01');
                        $amem_fec_fina=$start->format('Y-m-t');
                        //var_dump($fecha_hasta);var_dump($amem_fec_inic);var_dump($amem_fec_fina);

                        if($fecha_hasta>=$amem_fec_inic && $fecha_hasta<=$amem_fec_fina) {
                            $sql = "SELECT count(*) as contador  from saeamem where amem_cod_empr='$empresa' and amem_cod_empl = '$empleado' and amem_fec_amem = '$amem_fec_fina'";
                            //echo $sql;
                            $contador = consulta_string($sql, 'contador', $oConexion2, '0');
                            //var_dump($amem_fec_inic.'-'.$amem_fec_fina);


                            //var_dump($tipo_nove);var_dump($tipo_nove_ant);
                            //var_dump($total);var_dump($amem_fec_inic);
                            if ($tipo_nove==$tipo_nove_ant)$total=$total_ant+$total;
                            if ($total>$jornada){
                                $total=$jornada;
                            }
                            if ($tipo_nove!='') {
                                if ($contador != '0') {
                                    $sql = "update saeamem set $tipo_nove = $total , amem_fec_inic='$amem_fec_inic' , amem_fec_fina='$amem_fec_fina' where amem_cod_empr='$empresa' and amem_cod_empl = '$empleado' and amem_fec_amem='$amem_fec_fina'";
                                } else {
                                    $sql = "insert into saeamem (amem_cod_empr,amem_cod_empl,amem_fec_amem,$tipo_nove,amem_fec_inic,amem_fec_fina) values ('$empresa','$empleado', '$amem_fec_fina', '$total','$amem_fec_inic','$amem_fec_fina')";
                                }
                            }
                            $tipo_nove_ant=$tipo_nove;
                            $total_ant=$total;
                            //$num_horas=$num_horas-$total;
                            //var_dump($sql);
                            $oConexion2->QueryT($sql);
                        }


                        $start->modify( 'first day of +1 month' );
                    }

                }while ($oConexion->SiguienteRegistro());
            }
        }

        $sql = "SELECT sum(COALESCE(amem_hor_atra,0)+COALESCE(amem_hor_falt,0)+COALESCE(amem_hor_perm,0)+COALESCE(amem_hor_mate,0)+COALESCE(amem_hor_vaca,0)+COALESCE(amem_hor_efer,0)+COALESCE(amem_hor_lice,0)) as valor from saeamem where amem_cod_empr='$empresa' and amem_cod_empl = '$empleado' and amem_fec_amem = '$fecha'";
        //echo $sql;
        $horas_falta2 = consulta_string($sql, 'valor', $oConexion2, 0);

        $sql = "SELECT count(*) as contador  from saeamem where amem_cod_empr='$empresa' and amem_cod_empl = '$empleado' and amem_fec_amem = '$fecha'";
        //echo $sql;
        $contador = consulta_string($sql, 'contador', $oConexion2, 0);
        //var_dump($amem_fec_inic.'-'.$amem_fec_fina);
        if (intval($contador) == 0) {
            $sql = "insert into saeamem (amem_cod_empr,amem_cod_empl,amem_fec_amem,amem_fec_inic,amem_fec_fina) values ('$empresa','$empleado', '$fecha','$fecha_inicion_mes','$fecha')";
            $oConexion->QueryT($sql);
        }


        /*$horas_falta=abs($horas_falta);
        if($horas_falta==NULL){$horas_falta=$empl_jor_trab;}elseif($horas_falta>$empl_jor_trab){$horas_falta=$empl_jor_trab;}elseif($horas_falta==$empl_jor_trab){$horas_falta=0;}elseif($horas_falta<$empl_jor_trab){$horas_falta=$horas_falta;}*/
        //var_dump($empl_jor_trab);var_dump($horas_falta);var_dump($horas_falta2);exit;
        $horas_falta=$empl_jor_trab-$horas_falta-$horas_falta2;
        if ($horas_falta<0)$horas_falta=0;
        $update = "UPDATE saeamem SET amem_hor_trab='$horas_falta' WHERE amem_cod_empr='$empresa' AND amem_cod_empl='$empleado' AND  amem_fec_amem='$amem_fec_amem'";
        $oConexion->QueryT($update);

        //var_dump($update);

        //pagos realizados del rol
        unset($array_pago_rol);


        //datos de la pemp
        unset($array_pemp);


        //datos de la pemp x pagar
        unset($array_pempx);


        $sql = "DELETE from saepemp  WHERE  pemp_cod_empr = $empresa  AND  
									 pemp_per_pemp = $periodo  AND  
									 pemp_cod_empl = '$empleado'  AND 
									
									 pemp_fec_real = '$amem_fec_amem'";
        $oConexion->QueryT($sql);

        $ban = true;


        $sql = "DELETE from saepago  WHERE ( saepago.pago_cod_empr = $empresa ) AND
									( saepago.pago_per_pago = '$periodo') AND
									( saepago.pago_cod_empl = '$empleado') AND
									( saepago.pago_ori_gene = 'R' ) AND
									
									( saepago.pago_fec_real = '$amem_fec_amem')";

        $oConexion->QueryT($sql);

        /*$sql="select  pret_cod_pret from saepret where pret_cod_empl='$empleado' and pret_cod_empr='$empresa'";

        if($oConexion->Query($sql)){
            if($oConexion->NumFilas()>0){
                do{
                    $pret_cod_pret=$oConexion->f('pret_cod_pret');
                    $upt="update saecuot set cuot_est_cuot=0 where cuot_cod_pret='$pret_cod_pret' and cuot_fec_venc='$amem_fec_amem'";
                    $oConexion2->QueryT($upt);
                }while($oConexion->SiguienteRegistro());
            }
        }*/


        unset($_SESSION['rrhh_formula']);

        // INGRESOS
        //var_dump($array_rubros_ingresos);exit;
        if($array_rubros_ingresos!='') {
            foreach ($array_rubros_ingresos as $arreglo) {

                $rubro = $arreglo[0];
                //var_dump($arreglo);
                $valor = f_resuelve_formula($rubro, $empleado, $fecha_amem_n, $empresa, $amem_fec_amem, $departamento_calculo);
                //echo 'valor ingreso:----> '.$valor;var_dump($rubro);
                //if ($rubro=='RHECI'){var_dump($rubro); echo 'valor:----> '.$valor;}

                if ($valor != 0) {
                    $valor=round(str_replace(',','.', $valor),2);

                    $insert = "INSERT INTO saepago(pago_cod_empr,pago_cod_empl, pago_per_pago, pago_cod_rubr, pago_val_pago,  pago_ori_gene, pago_fec_real, pago_cod_estr)
										VALUES($empresa,'$empleado',$periodo, '$rubro',$valor, 'R', '$amem_fec_amem', '$departamento')";
                    $oConexion->QueryT($insert);
                    //if ($rubro=='RHECC'){var_dump($insert); exit;}

                    $insert_tmp = "INSERT INTO tmp_pago_nomina
										VALUES( '$rubro','$empleado','$empresa',$valor, '$periodo')";
                    $oConexion->QueryT($insert_tmp);

                }
            }
        }


        // PROVISIONES
        // var_dump($array_rubros_provision);exit;
        if($array_rubros_provision){
            foreach ($array_rubros_provision as $arreglo) {
                $rubro = $arreglo[0];
                $cod_pnom = $arreglo[3];

                $valor = f_resuelve_formula($rubro, $empleado, $fecha_amem_n, $empresa, $amem_fec_amem, $departamento_calculo);
                //echo 'valor provision:----> '.$valor;var_dump($rubro);
                //echo $cod_pnom;exit;
                $horas_rubr=0;
                //var_dump($valor);                var_dump($cod_pnom);                var_dump($rubro);

                if (($valor != '0') && ($cod_pnom != '0')) {
                    if($rubro=='RVACA'){
                        $sql="select prrh_val_prrh from saeprrh where prrh_cod_empr='$empresa' and prrh_cod_prrh='PVANI'";
                        $pvani = consulta_string($sql, 'prrh_val_prrh', $oConexion, 0);
                        $sql="select prrh_val_prrh from saeprrh where prrh_cod_empr='$empresa' and prrh_cod_prrh='PVNDA'";
                        $pvnda = consulta_string($sql, 'prrh_val_prrh', $oConexion, 0);
                        $sql="select prrh_val_prrh from saeprrh where prrh_cod_empr='$empresa' and prrh_cod_prrh='PVDMA'";
                        $pvdma = consulta_string($sql, 'prrh_val_prrh', $oConexion, 0);

                        $sql = "SELECT MAX(esem_fec_ingr) as fec FROM saeesem WHERE esem_cod_empl='$empleado' AND esem_cod_empr=$empresa and esem_est_esem='I'";
                        //echo $sql;exit;
                        $fecha_ingreso = consulta_string($sql, 'fec', $oConexion, date('Y-m-t'));

                        $anios =intval(( dias_diferencia($fecha_real , $fecha_ingreso))/365);
                        if($anios>$pvani){
                            //CALCULO DE VACACIONES NORMATIVA EC//
                            if ($anios>20)$anios=20;
                            //var_dump('valor '.$valor); var_dump('pvnda '.$pvnda); var_dump('anios '.$anios); var_dump('pvani '.$pvani);
                            $vacaciones=$valor+(($valor/$pvnda)*($anios-$pvani));
                            $valor=number_format($vacaciones,2);
                             //var_dump('valor '.$valor);                            var_dump('anios: '.$anios);                            var_dump('vaciones: '.$vacaciones);exit;
                            $empl_jor_trab=(($empl_jor_trab)/$pvdma)*$anios;
                        }
                        $horas_rubr=(($empl_jor_trab/$pvdma)*$pvnda)/12;
                    }


                        $sql = "select pnom_mes_inic, pnom_mes_fina from saepnom where pnom_cod_empr='$empresa' and pnom_cod_rubr='$rubro'";
                        $pnom_mes_inic = consulta_string($sql, 'pnom_mes_inic', $oConexion, 0);
                        $pnom_mes_fina = consulta_string($sql, 'pnom_mes_fina', $oConexion, 0);

                    if ($pnom_mes_inic!=0 && $pnom_mes_fina!=0) {
                        //VALIDIACION PARA LAS PROVISIONES QUE TIENEN CONFIGURADOS LAS FECHAS DE INICIO Y FIN DE CALCULO
                        $array_fecha = explode('-', $fecha);
                        $anio = $array_fecha[0];
                        if ($pnom_mes_inic<10)$pnom_mes_inic='0'.$pnom_mes_inic;
                        if ($pnom_mes_fina<10)$pnom_mes_fina='0'.$pnom_mes_fina;
                        $pnom_mes_inic=intval($anio.$pnom_mes_inic);
                        $pnom_mes_fina=intval($anio.$pnom_mes_fina);
                        if ($pnom_mes_fina<$pnom_mes_inic)$pnom_mes_fina=$pnom_mes_fina+100;
                        $periodopnom=intval($periodo);
                        if ($periodopnom>=$pnom_mes_inic && $periodopnom<=$pnom_mes_fina){}else{$periodopnom=$periodopnom+100;}
                        if ($periodopnom>=$pnom_mes_inic && $periodopnom<=$pnom_mes_fina){}else{$valor=0;}
                        /*var_dump($rubro);
                        var_dump($pnom_mes_inic);
                        var_dump($pnom_mes_fina);
                        var_dump($periodo);
                        var_dump($periodopnom);
                        var_dump($valor);*/
                    }

                    if($cod_pnom!='' && $valor > 0){
                        $valor=str_replace(',','', $valor);
                        $insert = "INSERT INTO saepemp(pemp_cod_empr, pemp_cod_pnom, pemp_per_pemp, pemp_val_mese,  pemp_ori_gene, pemp_cod_empl,
												pemp_est_pemp,  pemp_cod_estr, pemp_fec_real, pemp_hor_vaca)
                                    VALUES($empresa, $cod_pnom, '$periodo', '$valor',  'R', '$empleado',
                                            0,  '$departamento',  '$amem_fec_amem', '$horas_rubr')";
                        $oConexion->QueryT($insert);
                    }
                }
            }
        }
        //DESCUENTOS
        if($array_rubros_descuentos!=''){
            foreach ($array_rubros_descuentos as $arreglo) {
                $rubro = $arreglo[0];
                $valor = f_resuelve_formula($rubro, $empleado, $fecha_amem_n, $empresa, $amem_fec_amem, $departamento_calculo);
                //echo 'valor descuento:----> '.$valor;var_dump($rubro);
                //echo 'valor: '.$valor;
                if ($valor != 0) {
                    $valor=str_replace(',','.', $valor);
                    $insert = "INSERT INTO saepago(pago_cod_empr,pago_cod_empl, pago_per_pago, pago_cod_rubr, pago_val_pago,  pago_ori_gene, pago_fec_real, pago_cod_estr)
									VALUES($empresa,'$empleado',$periodo, '$rubro',$valor, 'R', '$amem_fec_amem', '$departamento')";
                    $oConexion->QueryT($insert);
                }
            }
        }
        ///    PRESTAMOS
        if (!empty($array_prestamos[$empleado])) {
            $sql="select * from saepret where pret_cod_empl='$empleado' and pret_cod_empr='$empresa'";
            if($oConexion->Query($sql)){
                if($oConexion->NumFilas()>0){
                    do{
                        $pret_cod_pret=$oConexion->f('pret_cod_pret');
                        $upt="update saecuot set cuot_est_cuot=1 where cuot_cod_pret='$pret_cod_pret' and cuot_fec_venc='$amem_fec_amem'";
                        $oConexion2->QueryT($upt);
                    }while($oConexion->SiguienteRegistro());
                }
            }
        }
        $update ="UPDATE saeamem SET amem_est_amem='1' WHERE amem_cod_empl='$empleado' AND amem_est_amem='0' AND amem_cod_estr='$cargo'";
        //echo $update;
        $oConexion->QueryT($update);
        $array="Rol Generado!! Empleado";
        return $array;
    }

    function impuesto_renta_ec($oIfx,$empresa,$anio,$deduccion_por_discapacidad,$deduccion_tercera_edad_sn,$base_imponible,$total_gastos_personales,$cagas_familiares_sn,$cargas_familiares,$canasta_familiar){
        $array_deducciones=$this->cambiarGradoDiscapacidad($deduccion_por_discapacidad,$deduccion_tercera_edad_sn,$base_imponible);
        //var_dump($deduccion_por_discapacidad);
        $resultadoGastosPersonalesDeducibles=$this->valorGatosPersonalesDeducibles($cagas_familiares_sn,$cargas_familiares,$canasta_familiar);
        $valorRebajaGastosPersonales=$this->valorRebajaGastosPersonales($resultadoGastosPersonalesDeducibles,$total_gastos_personales);

        $deducciones=$array_deducciones[1];
        //var_dump('$base_imponible '.$base_imponible); var_dump('$valorRebajaGastosPersonales '.$valorRebajaGastosPersonales); var_dump('$deducciones '.$deducciones);
        $valor_impuesto_reforma=$this->calculoImpuestos($oIfx,$empresa,$anio,$base_imponible,$valorRebajaGastosPersonales,$deducciones);
        return $valor_impuesto_reforma;
    }

    function cambiarGradoDiscapacidad($deduccion_por_discapacidad,$deduccion_tercera_edad_sn,$base_imponible) {
        $txtTipo = $deduccion_por_discapacidad;
        $valor = 0;
        if ($txtTipo == 0) {
            $valor = 0.00;
        } else if ($txtTipo == 1) {
            $valor = 14066.40;
        } else if ($txtTipo == 2) {
            $valor = 16410.80;
        } else if ($txtTipo == 3) {
            $valor = 18755.20;
        }else if ($txtTipo == 4) {
            $valor = 23444.00;
        }
        $resultadoGradoDiscapacidad =  $valor;
        $resultadoDeduccionTerceraEdad=$this->cambiarDeduccionTerceraEdad($deduccion_tercera_edad_sn);
        $valor_mayor_deduccion=$this->valorMayorDeduccionTerceraEdad($resultadoGradoDiscapacidad,$resultadoDeduccionTerceraEdad,$base_imponible);
        //var_dump('resultadoDeduccionTerceraEdad '.$resultadoDeduccionTerceraEdad);
        //var_dump('valor_mayor_deduccion '.$valor_mayor_deduccion);
        $array_deducciones=Array($resultadoDeduccionTerceraEdad,$valor_mayor_deduccion);

        return $array_deducciones;

    }

    function valorMayorDeduccionTerceraEdad($resultadoGradoDiscapacidad,$resultadoDeduccionTerceraEdad,$base_imponible) {
        $txtDeduccionDiscapacidad=$resultadoGradoDiscapacidad;
        $txtDeduccionTerceraEdad=$resultadoDeduccionTerceraEdad;
        //var_dump('txtDeduccionDiscapacidad '.$txtDeduccionDiscapacidad); var_dump('txtDeduccionTerceraEdad '.$txtDeduccionTerceraEdad);
        $valor_mayor_deduccion=0;
        if($txtDeduccionDiscapacidad > $txtDeduccionTerceraEdad){
            $valor_mayor_deduccion=$txtDeduccionDiscapacidad;
        }else{
            $valor_mayor_deduccion=$txtDeduccionTerceraEdad;
        }

        $resultadoIngresoDeducciones=$this->valorIngresosDeducciones($base_imponible,$valor_mayor_deduccion);
        //var_dump('resultadoIngresoDeducciones '.$resultadoIngresoDeducciones);
        return $resultadoIngresoDeducciones;
    }

    function cambiarDeduccionTerceraEdad($deduccion_tercera_edad_sn) {
        $txtTipoDeducion = $deduccion_tercera_edad_sn;
        $valorDeducion = 0;
        if ($txtTipoDeducion == 'N') {
            $valorDeducion = 0.00;
        } else if ($txtTipoDeducion == 'S') {
            $valorDeducion = 11902.00;
        }
        $resultadoDeduccionTerceraEdad =  $valorDeducion;
        //$this->valorMayorDeduccionTerceraEdad($resultadoGradoDiscapacidad,$resultadoDeduccionTerceraEdad,$base_imponible);
        return $resultadoDeduccionTerceraEdad;
    }

    function valorIngresosDeducciones($base_imponible,$valor_mayor_deduccion){
        $txtTotalIngresoNeto=$base_imponible;
        $txtTotalDeduccionesTerceraEdad=$valor_mayor_deduccion;
        $resultadoIngresoDeducciones =  $txtTotalIngresoNeto - $txtTotalDeduccionesTerceraEdad;
        //var_dump('resultadoIngresoDeducciones '.$resultadoIngresoDeducciones);
        return $resultadoIngresoDeducciones;
    }

    function valorGatosPersonalesDeducibles($cagas_familiares_sn,$cargas_familiares,$canasta_familiar){
        $txtSiNoCargaFamiliarSelecionada=$cagas_familiares_sn;
        $txtCargaFamiliar=$cargas_familiares;


        $siNo = 2;
        $resultadoGastosPersonalesDeducibles = 0;

        if ($txtSiNoCargaFamiliarSelecionada=='S'){
            $siNo = 1;
            $resultadoGastosPersonalesDeducibles = $canasta_familiar*20;
        }else if ($txtCargaFamiliar == 0) {
            $resultadoGastosPersonalesDeducibles = $canasta_familiar*7;
        }else if ($txtCargaFamiliar == 1) {
            $resultadoGastosPersonalesDeducibles = $canasta_familiar*9;
        }else if ($txtCargaFamiliar == 2) {
            $resultadoGastosPersonalesDeducibles = $canasta_familiar*11;
        }else if ($txtCargaFamiliar == 3) {
            $resultadoGastosPersonalesDeducibles = $canasta_familiar*14;
        }else if ($txtCargaFamiliar == 4) {
            $resultadoGastosPersonalesDeducibles = $canasta_familiar*17;
        }else if ($txtCargaFamiliar >= 5) {
            $resultadoGastosPersonalesDeducibles = $canasta_familiar*20;
        }

        return $resultadoGastosPersonalesDeducibles;
    }


    function valorRebajaGastosPersonales($resultadoGastosPersonalesDeducibles,$frem_tot_ingrb){
        $txtTotalGastosPersonales=$frem_tot_ingrb;
        $txtMaxGastosPersonalesDeducibles=$resultadoGastosPersonalesDeducibles;

        $valortxtTotalGastosPersonales = ($txtTotalGastosPersonales);
        $valortxtMaxGastosPersonalesDeducibles = ($txtMaxGastosPersonalesDeducibles);
        $rebaja_gastos_personales = 0;
        $valorMenor = min($valortxtTotalGastosPersonales, $valortxtMaxGastosPersonalesDeducibles);

        $rebaja_gastos_personales = ($valorMenor*0.18);
        return $rebaja_gastos_personales;
        //txtRebajaGastosPersonales.maskMoney('mask');
    }

    function calculoImpuestos($oIfx,$empresa,$anio,$base_imponible,$valorRebajaGastosPersonales,$deducciones){

        $sql="SELECT
            iren_cod_iren,
            iren_val_inic,
            iren_val_fina,
            iren_val_iren,
            iren_por_iren
        FROM
             saeiren
        WHERE
            iren_cod_empr='$empresa' and 
            iren_val_inic<='$base_imponible' and iren_val_fina >='$base_imponible' and iren_anio_iren='$anio'";
        //echo $sql;
        $iren_val_inic = consulta_string($sql, 'iren_val_inic', $oIfx, 0);
        $iren_val_fina = consulta_string($sql, 'iren_val_fina', $oIfx, 0);
        $iren_por_iren = consulta_string($sql, 'iren_por_iren', $oIfx, 0);
        $iren_val_iren = consulta_string($sql, 'iren_val_iren', $oIfx, 0);

        $valor_impuesto_renta = ($iren_val_iren + ($base_imponible - $iren_val_inic) * ($iren_por_iren/100));
        $resultadoImpuestoPagarReforma = ($valor_impuesto_renta - $valorRebajaGastosPersonales);

        //var_dump('iren_val_iren '.$iren_val_iren); var_dump('base_imponible '.$base_imponible);
        //var_dump('iren_val_inic '.$iren_val_inic); var_dump('iren_por_iren '.$iren_por_iren);
        //var_dump('valor_impuesto_renta '.$valor_impuesto_renta); var_dump('valorRebajaGastosPersonales '.$valorRebajaGastosPersonales);
        //var_dump('resultadoImpuestoPagarReforma '.$resultadoImpuestoPagarReforma);exit;


        return $resultadoImpuestoPagarReforma;
        //document.getElementById('valor_mensual_actual').value=resultadoImpuestoPagarReforma/12;
    }

}
?>