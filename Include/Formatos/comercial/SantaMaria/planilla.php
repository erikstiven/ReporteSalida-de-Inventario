<?

function formato_planilla( $cliente, $admision, $tipo){
	//Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();
	
	$oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();
	

    //Lectura Sucia
    

    //variables de session
    unset($_SESSION['detalleCuentaPaciente']);
    unset($_SESSION['detalleCuentaPacienteTotal']);
    unset($_SESSION['CORTE_CUENTA_PACIENTE']);
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    $usuario_web= $_SESSION['U_ID'];

    $ids='';
    $sql="select * from clinico.codigos_tarifario where id_empresa= '$idempresa'";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0){		
			unset($arrayCodTari);	
			do {
				$arrayCodTari[$oCon->f('codigo_empresa')] = $oCon->f('codigo_tarifario');
				$ids.="'".$oCon->f('codigo_tarifario')."',";
			}while($oCon->SiguienteRegistro());

        }
    }
    $oCon->Free();

    

    $sql="select concat(usuario_nombre,' ',usuario_apellido) as nombres,empl_cod_empl from comercial.usuario where usuario_id=$usuario_web";
    $nombres=consulta_string($sql,'nombres',$oCon,'');
    $identificacion=consulta_string($sql,'empl_cod_empl',$oCon,'');

    //CONSULTA DEPARTAMENTO

    $sqld="SELECT esem_cod_estr from  saeesem where esem_cod_empl='$identificacion' and esem_cod_empr=$idempresa and esem_est_esem='I'";
    $esem_cod_estr=consulta_string($sqld,'esem_cod_estr',$oIfxA,'');

    $sqlcod="SELECT estr_cod_padr FROM saeestr WHERE estr_cod_estr='$esem_cod_estr' and estr_cod_test='C'";
    $estr_cod_padr=consulta_string($sqlcod,'estr_cod_padr',$oIfxA,'');

    $sqldep="SELECT estr_des_estr FROM saeestr WHERE estr_cod_padr='$estr_cod_padr' and estr_cod_test='D'";
    $departamento=consulta_string($sqldep,'estr_des_estr',$oIfxA,'');
    if(empty($departamento)){
        $departamento='AUDITORIA';
    }


    //BODEGA TARIFARIO

    $sqlpar="select clipar_val_clipar from clinico.clipar where clipar_cod_clipar='BODTAR'";
    $bode_tar= consulta_string($sqlpar, 'clipar_val_clipar', $oCon, 0);


	//grupo medicos
	$sql = "select grpv_cod_med from clinico.grupo_clopv";
	$grpv_cod_med = consulta_string($sql, 'grpv_cod_med', $oCon, '');
	$sql = "select clpv_cod_clpv, clpv_nom_clpv
			from saeclpv
			where clpv_cod_empr = $idempresa and
			clpv_clopv_clpv = 'PV' and
			grpv_cod_grpv = '$grpv_cod_med'";
		//	ECHO $sql;EXIT;
	if($oIfx->Query($sql)){
		if($oIfx->NumFilas() > 0){
			unset($arrayClpv);
			do{
				$arrayClpv[$oIfx->f('clpv_cod_clpv')] = $oIfx->f('clpv_nom_clpv'); 
			}while($oIfx->SiguienteRegistro());
		}
	}
	$oIfx->Free();
	/// lineas productos
	$sql="select linp_cod_linp, linp_des_linp from  saelinp where linp_cod_empr='$idempresa'";
	unset($array_lineas);
	if($oIfx->Query($sql)){
		if($oIfx->NumFilas()>0){
			do{
				$array_lineas[$oIfx->f('linp_cod_linp')]=$oIfx->f('linp_des_linp');
			}while($oIfx->SiguienteRegistro());
		}
	}
	$sql = "select proe_des_proe, clpv_cod_clpv
			from saeproe, saeclpv
			where proe_cod_proe = clpv_cod_proe and
			clpv_cod_empr = proe_cod_empr";
	if($oIfx->Query($sql)){
		if($oIfx->NumFilas() > 0){
			unset($arrayEspe);
			do{
				$arrayEspe[$oIfx->f('clpv_cod_clpv')] = $oIfx->f('proe_des_proe'); 
			}while($oIfx->SiguienteRegistro());
		}
	}

    /// diagnosticos
	$sqlDiag = "select codigo_cie, id_cie from clinico.cie10";
	if($oCon->Query($sqlDiag)){
		if($oCon->NumFilas() > 0){
			unset($arrayDiag);
			do{
				$arrayDiag[$oCon->f('id_cie')] = $oCon->f('codigo_cie'); 
				
			}while($oCon->SiguienteRegistro());
		}
	}

    
    
	$sql="select * from clinico.datos_clpv where id_dato='$admision' and id_empresa='$idempresa'";
	$paciente=consulta_string($sql, 'nom_clpv', $oCon, 0);
	$ruc_clpv=consulta_string($sql, 'ruc_clpv', $oCon, 0);
	$secu_hist=consulta_string($sql, 'secu_hist', $oCon, 0);
	$fecha_admision=consulta_string($sql, 'fecha_admision', $oCon, 0);
    $fecha_admision=date('d/m/Y', strtotime($fecha_admision));
	$fecha_alta=consulta_string($sql, 'fecha_alta', $oCon, '');
    if(!empty($fecha_alta)){
        $fecha_alta=date('d/m/Y', strtotime($fecha_alta));
    }



    $id_cie10   = consulta_string($sql, 'id_cie10', $oCon, '');
    $id_cie10_1 = consulta_string($sql, 'id_cie10_1', $oCon, '');
	$id_cie10_2 = consulta_string($sql, 'id_cie10_2', $oCon, '');
	$id_cie10_3 = consulta_string($sql, 'id_cie10_3', $oCon, '');
    $id_cie10_4 = consulta_string($sql, 'id_cie10_4', $oCon, '');
	$id_cie10_5 = consulta_string($sql, 'id_cie10_5', $oCon, '');


    if($id_cie10!=0 && !empty($id_cie10)){
        $id_cie10=$arrayDiag[$id_cie10];
    }
    $id_cie10_1=consulta_string($sql, 'id_cie10_1', $oCon, '');
    if($id_cie10_1!=0 && !empty($id_cie10_1)){
        $id_cie10_1=$arrayDiag[$id_cie10_1];
    }
    $id_cie10_2=consulta_string($sql, 'id_cie10_2', $oCon, '');
    if($id_cie10_2!=0 && !empty($id_cie10_2)){
        $id_cie10_2=$arrayDiag[$id_cie10_2];
    }

    $id_cie10_3=consulta_string($sql, 'id_cie10_3', $oCon, '');
    if($id_cie10_3!=0 && !empty($id_cie10_3)){
        $id_cie10_3=$arrayDiag[$id_cie10_3];
    }

    $id_cie10_4=consulta_string($sql, 'id_cie10_4', $oCon, '');
    if($id_cie10_4!=0 && !empty($id_cie10_4)){
        $id_cie10_4=$arrayDiag[$id_cie10_4];
    }

    $id_cie10_5=consulta_string($sql, 'id_cie10_5', $oCon, '');
    if($id_cie10_5!=0 && !empty($id_cie10_5)){
        $id_cie10_5=$arrayDiag[$id_cie10_5];
    }

   




	$sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_tel_resp
                                            from saeempr where empr_cod_empr = $idempresa ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_path_logo');
            $empr_tel_resp = $oIfx->f('empr_tel_resp');
            if ($oIfx->f('empr_conta_sn') == 'S')
                $empr_conta_sn = 'SI';
            else
                $empr_conta_sn = 'NO';

            $empr_num_resu = $oIfx->f('empr_num_resu');
        }
    }

    //LOGO DEL REPORTE
    
    $ruta=basename($empr_path_logo);
    ///////LOGO DEL REPORTE ///////////////
    $arc_img=DIR_FACTELEC."Include/Clases/Formulario/Plugins/reloj/$ruta";

    $logo='';

    if(file_exists($arc_img)){
        $imagen=$arc_img;
    }else{
        $imagen='';
    }

    $x='0px';
    if($imagen!=''){

        $logo='
                <img src="'. $imagen .'" style="
                width:190px;
                object-fit; contain;">
                ';
        $x='0px';
    }
    else{
        $logo='';

    }
	//SELECCIONA RESP CUENTA
		$sql = "select ccli_clpv, resp_precio from clinico.datos_clpv where id_dato = $admision and id_clpv = $cliente and id_empresa = $idempresa";
		$ccli_clpv = consulta_string($sql, 'ccli_clpv', $oCon, '');
		$resp_precio = consulta_string($sql, 'resp_precio', $oCon, '');


        $html="<table  cellspacing='0' cellpadding='1' border='0.7' style='width:100%;font-size:12px;'>
						
						
						<tr >
							<td  width='125' ><b>NOMBRE DEL PRESTADOR</b></td>
							<td    width='160' align='center'>".$razonSocial."</td>
                            <td  rowspan='6'   align='center' width='200' align='center'>".$logo."</td>
						</tr>
						<tr>
							<td  width='125' ><b>NOMBRE DEL PACIENTE</b></td>
							<td align='center'  width='360' align='center'>".$paciente."</td>
						</tr>
						<tr>
							<td  width='125' ><b>NUMERO DE CI:</b></td>
							<td  align='center' width='360' align='center'>".$ruc_clpv."</td>
						</tr>
						<tr>
							<td  width='125' ><b>FECHA DE INGRESO:</b></td>
							<td  align='center' width='360' align='center'>".$fecha_admision." </td>
                        </tr>
                        <tr>
							<td  width='125'  ><b>FECHA DE ALTA:</b></td>
							<td  align='center' width='360' align='center'>".$fecha_alta."</td>
						</tr>
                        <tr>
                        <td width='125' ><b>CIE 10:</b></td>
						<td align='center' width='360' align='center'> $id_cie10  $id_cie10_1 $id_cie10_2 $id_cie10_3 $id_cie10_4 $id_cie10_5</td>
                       
                        </tr>
                        </table>";

                        $html.="<table  cellspacing='0' cellpadding='1' border='0.7' style='margin-top:12px; width:100%;font-size:12px;'>
						<tr>
							<td align='center'  width='90'>Fec_periodo_fin</td>
							<td align='center'  width='85'>CODIGO</td>
							<td align='center'  width='195'>DESCRIPCION</td>
                            <td align='center'  width='100'>MEDICO</td>
							<td align='center'  width='60'>Cantidad</td>
							<td align='center'  width='60'>V. Unitario</td>
							<td align='center'  width='67'>V. Total</td>
					
						</tr>";
                        $i=0;
                        $sub_total=0;
                        $ctrl_ped=0;

////SEPARACION DE ITEMS POR CATEGORIA

            //TARIFARIO NACIONAL
            $sql = "select	linp_cod_linp, p.pedf_cod_pedf,	d.dpef_cod_prod,	d.dpef_nom_prod,	d.dpef_cant_dfac, d.dpef_bode_ori,
							d.dpef_precio_dfac,	d.dpef_cod_bode, 	d.dpef_cod_unid,	d.dpef_por_iva,
							d.dpef_por_ice,		d.dpef_des1_dfac,	d.dpef_des2_dfac,	d.dpef_por_dsg,
							d.dpef_cod_linp,	d.dpef_est_dpef,	d.dpef_cant_ori,
							d.dpef_cant_dev,	p.pedf_user_web,	d.dpef_pre_fact,	p.pedf_fech_fact,
							p.pedf_hor_ini,		p.pedf_num_preimp,	p.pedf_est_fact, 
							d.dpef_cant_pre,	d.dpef_precio_pre,	d.dpef_cod_dpef,
							d.dpef_mont_total,  d.dpef_des1_pre,    d.dpef_tot_pre, d.dpef_cod_med, linp_des_linp
							from saepedf p, saedpef d, saelinp, saeprod pr where
							linp_cod_empr = d.dpef_cod_empr and
							linp_cod_linp = d.dpef_cod_linp and
							p.pedf_cod_pedf = d.dpef_cod_pedf and
                            d.dpef_cod_prod = pr.prod_cod_prod and
                            pr.prod_cod_empr= $idempresa and 
							p.pedf_cod_empr = $idempresa and
							p.pedf_cod_sucu = $idsucursal and
							p.pedf_cod_clpv = $cliente and 
							p.pedf_cod_admi = $admision and
							p.pedf_est_fact != 'GR' and
							p.pedf_est_fact != 'AN' and
							d.dpef_est_dpef != 'A' and
                            d.dpef_pre_fact='S' and 
							d.dpef_cant_dfac > 0 
                            and d.dpef_cod_linp =5
							order by p.pedf_fech_fact ASC ";

        if($oIfx->Query($sql)){
            if($oIfx->NumFilas()>0){
                
                $i=0;
                do{
                    $dpef_cod_prod=$oIfx->f('dpef_cod_prod');
                    $codig_t=$arrayCodTari[$dpef_cod_prod];

                    

                    $dpef_nom_prod =$oIfx->f('dpef_nom_prod');
                    $dpef_cant_dfac =$oIfx->f('dpef_cant_dfac');
                    $dpef_precio_dfac =$oIfx->f('dpef_precio_dfac');
                    $dpef_cod_linp =$oIfx->f('dpef_cod_linp');
                    $dpef_mont_total =$oIfx->f('dpef_mont_total');
                    $dpef_cod_med =$oIfx->f('dpef_cod_med');
                    $pedf_fech_fact =$oIfx->f('pedf_fech_fact');
                    $dpef_por_iva =$oIfx->f('dpef_por_iva');
                    $dpef_pre_fact = $oIfx->f('dpef_pre_fact');
                    $dpef_bode_ori = $oIfx->f('dpef_bode_ori');
    
                    if($dpef_bode_ori!=$bode_tar){
                        $dpef_cod_prod='';
                    }

                    if(!empty($codig_t) &&empty($dpef_cod_prod)){
                        $dpef_cod_prod= $codig_t;
                    }
    
                    if ($dpef_pre_fact == 'S') {
                            $cant = $oIfx->f('dpef_cant_pre');
                            $precio = round($oIfx->f('dpef_precio_pre'), 4);
                            $descuento = $oIfx->f('dpef_des1_pre');
    
                            if ($descuento > 0) {
                                $total_pedf = round($oIfx->f('dpef_tot_pre'), 4);
                            } else {
                                $total_pedf = $cant * $precio;
                                $descuento = 0.0;
                            }
    
                            
                    } else {
                        $cant = $oIfx->f('dpef_cant_dfac');
                        $precio = round($oIfx->f('dpef_precio_dfac'), 2);
                        $descuento = $oIfx->f('dpef_des1_dfac');
                        
                        if(empty($precio)){
                            $sqlPrecio = "select ppr_pre_raun from saeppr where ppr_cod_prod = '$dpef_cod_prod' and ppr_cod_nomp = $resp_precio";
                            $precio = round(consulta_string($sqlPrecio, 'ppr_pre_raun', $oIfxA, 0), 4);
                        }
    
                        if ($descuento > 0) {
                            $total_pedf = round($oIfx->f('dpef_mont_total'), 4);
                        } else {
                            $total_pedf = $cant * $precio;
                        }
    
                        
                    }
                    //monto de iva
                    if ($dpef_por_iva > 0) {
                        $empr_iva_empr = $dpef_por_iva;
                    }
                    if ($dpef_por_iva > 0) {
                        $totalConIva += $total_pedf;
                    } else {
                        $totalSinIva += $total_pedf;
                    }
                    
        
                    $html.='<tr>
                                <td align="center"  width="90">'.$pedf_fech_fact.'</td>	
                                <td align="center"  width="85">'.$dpef_cod_prod.'</td>	
                                <td align="justify" width="195">'.$dpef_nom_prod.'</td>
                                <td align="justify" width="100">'.$arrayClpv[$dpef_cod_med].'</td>					
                                <td align="center" width="60">'.number_format($cant,2 , ',', '.').'</td>								
                                <td align="right" width="60">'.number_format($precio,4 , ',', '.').'</td>	
                                <td align="right" width="67">'.number_format($total_pedf,4 , ',', '.').'</td>	
                                
                            </tr>';
                    $linp_ant=$dpef_cod_linp;
                    $sub_total+=$total_pedf;
                    $i++;
                    $ctrl_ped++;
                }while($oIfx->SiguienteRegistro());
               
            }
            
        }
        $oIfx->Free();

        //SERVICIOS

               $sql = "select	linp_cod_linp, p.pedf_cod_pedf,	d.dpef_cod_prod,	d.dpef_nom_prod,	d.dpef_cant_dfac, d.dpef_bode_ori,
							d.dpef_precio_dfac,	d.dpef_cod_bode, 	d.dpef_cod_unid,	d.dpef_por_iva,
							d.dpef_por_ice,		d.dpef_des1_dfac,	d.dpef_des2_dfac,	d.dpef_por_dsg,
							d.dpef_cod_linp,	d.dpef_est_dpef,	d.dpef_cant_ori,
							d.dpef_cant_dev,	p.pedf_user_web,	d.dpef_pre_fact,	p.pedf_fech_fact,
							p.pedf_hor_ini,		p.pedf_num_preimp,	p.pedf_est_fact, 
							d.dpef_cant_pre,	d.dpef_precio_pre,	d.dpef_cod_dpef,
							d.dpef_mont_total,  d.dpef_des1_pre,    d.dpef_tot_pre, d.dpef_cod_med, linp_des_linp
							from saepedf p, saedpef d, saelinp, saeprod pr where
							linp_cod_empr = d.dpef_cod_empr and
							linp_cod_linp = d.dpef_cod_linp and
							p.pedf_cod_pedf = d.dpef_cod_pedf and
                            d.dpef_cod_prod = pr.prod_cod_prod and
                            pr.prod_cod_empr= $idempresa and 
							p.pedf_cod_empr = $idempresa and
							p.pedf_cod_sucu = $idsucursal and
							p.pedf_cod_clpv = $cliente and 
							p.pedf_cod_admi = $admision and
							p.pedf_est_fact != 'GR' and
							p.pedf_est_fact != 'AN' and
							d.dpef_est_dpef != 'A' and
                            d.dpef_pre_fact='S' and 
							d.dpef_cant_dfac > 0 
                            and d.dpef_cod_linp in (7, 14)
							order by p.pedf_fech_fact ASC ";

        if($oIfx->Query($sql)){
            if($oIfx->NumFilas()>0){                
                $i=0;
                do{
                    $dpef_cod_prod=$oIfx->f('dpef_cod_prod');
                    $codig_t=$arrayCodTari[$dpef_cod_prod];

                    

                    $dpef_nom_prod =$oIfx->f('dpef_nom_prod');
                    $dpef_cant_dfac =$oIfx->f('dpef_cant_dfac');
                    $dpef_precio_dfac =$oIfx->f('dpef_precio_dfac');
                    $dpef_cod_linp =$oIfx->f('dpef_cod_linp');
                    $dpef_mont_total =$oIfx->f('dpef_mont_total');
                    $dpef_cod_med =$oIfx->f('dpef_cod_med');
                    $pedf_fech_fact =$oIfx->f('pedf_fech_fact');
                    $dpef_por_iva =$oIfx->f('dpef_por_iva');
                    $dpef_pre_fact = $oIfx->f('dpef_pre_fact');
                    $dpef_bode_ori = $oIfx->f('dpef_bode_ori');
    
                    if($dpef_bode_ori!=$bode_tar){
                        $dpef_cod_prod='';
                    }

                    if(!empty($codig_t) &&empty($dpef_cod_prod)){
                        $dpef_cod_prod= $codig_t;
                    }
    
                    if ($dpef_pre_fact == 'S') {
                            $cant = $oIfx->f('dpef_cant_pre');
                            $precio = round($oIfx->f('dpef_precio_pre'), 4);
                            $descuento = $oIfx->f('dpef_des1_pre');
    
                            if ($descuento > 0) {
                                $total_pedf = round($oIfx->f('dpef_tot_pre'), 4);
                            } else {
                                $total_pedf = $cant * $precio;
                                $descuento = 0.0;
                            }
    
                            
                    } else {
                        $cant = $oIfx->f('dpef_cant_dfac');
                        $precio = round($oIfx->f('dpef_precio_dfac'), 2);
                        $descuento = $oIfx->f('dpef_des1_dfac');
                        
                        if(empty($precio)){
                            $sqlPrecio = "select ppr_pre_raun from saeppr where ppr_cod_prod = '$dpef_cod_prod' and ppr_cod_nomp = $resp_precio";
                            $precio = round(consulta_string($sqlPrecio, 'ppr_pre_raun', $oIfxA, 0), 4);
                        }
    
                        if ($descuento > 0) {
                            $total_pedf = round($oIfx->f('dpef_mont_total'), 4);
                        } else {
                            $total_pedf = $cant * $precio;
                        }
    
                        
                    }
                    //monto de iva
                    if ($dpef_por_iva > 0) {
                        $empr_iva_empr = $dpef_por_iva;
                    }
                    if ($dpef_por_iva > 0) {
                        $totalConIva += $total_pedf;
                    } else {
                        $totalSinIva += $total_pedf;
                    }
                    
                    
                    $html.='<tr>
                                <td align="center"  width="90">'.$pedf_fech_fact.'</td>	
                                <td align="center"  width="85">'.$dpef_cod_prod.'</td>	
                                <td align="justify" width="195">'.$dpef_nom_prod.'</td>
                                <td align="justify" width="100">'.$arrayClpv[$dpef_cod_med].'</td>					
                                <td align="center" width="60">'.number_format($cant,2 , ',', '.').'</td>								
                                <td align="right" width="60">'.number_format($precio,4 , ',', '.').'</td>	
                                <td align="right" width="67">'.number_format($total_pedf,4 , ',', '.').'</td>	
                                
                            </tr>';
                    $linp_ant=$dpef_cod_linp;
                    $sub_total+=$total_pedf;
                    $i++;
                    $ctrl_ped++;
                }while($oIfx->SiguienteRegistro());
                
            }
            
        }
        $oIfx->Free();

        //IMAGEN

               $sql = "select	linp_cod_linp, p.pedf_cod_pedf,	d.dpef_cod_prod,	d.dpef_nom_prod,	d.dpef_cant_dfac, d.dpef_bode_ori,
							d.dpef_precio_dfac,	d.dpef_cod_bode, 	d.dpef_cod_unid,	d.dpef_por_iva,
							d.dpef_por_ice,		d.dpef_des1_dfac,	d.dpef_des2_dfac,	d.dpef_por_dsg,
							d.dpef_cod_linp,	d.dpef_est_dpef,	d.dpef_cant_ori,
							d.dpef_cant_dev,	p.pedf_user_web,	d.dpef_pre_fact,	p.pedf_fech_fact,
							p.pedf_hor_ini,		p.pedf_num_preimp,	p.pedf_est_fact, 
							d.dpef_cant_pre,	d.dpef_precio_pre,	d.dpef_cod_dpef,
							d.dpef_mont_total,  d.dpef_des1_pre,    d.dpef_tot_pre, d.dpef_cod_med, linp_des_linp
							from saepedf p, saedpef d, saelinp, saeprod pr where
							linp_cod_empr = d.dpef_cod_empr and
							linp_cod_linp = d.dpef_cod_linp and
							p.pedf_cod_pedf = d.dpef_cod_pedf and
                            d.dpef_cod_prod = pr.prod_cod_prod and
                            pr.prod_cod_empr= $idempresa and 
							p.pedf_cod_empr = $idempresa and
							p.pedf_cod_sucu = $idsucursal and
							p.pedf_cod_clpv = $cliente and 
							p.pedf_cod_admi = $admision and
							p.pedf_est_fact != 'GR' and
							p.pedf_est_fact != 'AN' and
							d.dpef_est_dpef != 'A' and
                            d.dpef_pre_fact='S' and 
							d.dpef_cant_dfac > 0 
                            and d.dpef_cod_linp in (3, 10)
							order by p.pedf_fech_fact ASC ";

        if($oIfx->Query($sql)){
            if($oIfx->NumFilas()>0){                
                $i=0;
                do{
                    $dpef_cod_prod=$oIfx->f('dpef_cod_prod');
                    $codig_t=$arrayCodTari[$dpef_cod_prod];

                    

                    $dpef_nom_prod =$oIfx->f('dpef_nom_prod');
                    $dpef_cant_dfac =$oIfx->f('dpef_cant_dfac');
                    $dpef_precio_dfac =$oIfx->f('dpef_precio_dfac');
                    $dpef_cod_linp =$oIfx->f('dpef_cod_linp');
                    $dpef_mont_total =$oIfx->f('dpef_mont_total');
                    $dpef_cod_med =$oIfx->f('dpef_cod_med');
                    $pedf_fech_fact =$oIfx->f('pedf_fech_fact');
                    $dpef_por_iva =$oIfx->f('dpef_por_iva');
                    $dpef_pre_fact = $oIfx->f('dpef_pre_fact');
                    $dpef_bode_ori = $oIfx->f('dpef_bode_ori');
    
                    if($dpef_bode_ori!=$bode_tar){
                        $dpef_cod_prod='';
                    }

                    if(!empty($codig_t) &&empty($dpef_cod_prod)){
                        $dpef_cod_prod= $codig_t;
                    }
    
                    if ($dpef_pre_fact == 'S') {
                            $cant = $oIfx->f('dpef_cant_pre');
                            $precio = round($oIfx->f('dpef_precio_pre'), 4);
                            $descuento = $oIfx->f('dpef_des1_pre');
    
                            if ($descuento > 0) {
                                $total_pedf = round($oIfx->f('dpef_tot_pre'), 4);
                            } else {
                                $total_pedf = $cant * $precio;
                                $descuento = 0.0;
                            }
    
                            
                    } else {
                        $cant = $oIfx->f('dpef_cant_dfac');
                        $precio = round($oIfx->f('dpef_precio_dfac'), 2);
                        $descuento = $oIfx->f('dpef_des1_dfac');
                        
                        if(empty($precio)){
                            $sqlPrecio = "select ppr_pre_raun from saeppr where ppr_cod_prod = '$dpef_cod_prod' and ppr_cod_nomp = $resp_precio";
                            $precio = round(consulta_string($sqlPrecio, 'ppr_pre_raun', $oIfxA, 0), 4);
                        }
    
                        if ($descuento > 0) {
                            $total_pedf = round($oIfx->f('dpef_mont_total'), 4);
                        } else {
                            $total_pedf = $cant * $precio;
                        }
    
                        
                    }
                    //monto de iva
                    if ($dpef_por_iva > 0) {
                        $empr_iva_empr = $dpef_por_iva;
                    }
                    if ($dpef_por_iva > 0) {
                        $totalConIva += $total_pedf;
                    } else {
                        $totalSinIva += $total_pedf;
                    }
                    
                    
                    $html.='<tr>
                                <td align="center"  width="90">'.$pedf_fech_fact.'</td>	
                                <td align="center"  width="85">'.$dpef_cod_prod.'</td>	
                                <td align="justify" width="195">'.$dpef_nom_prod.'</td>
                                <td align="justify" width="100">'.$arrayClpv[$dpef_cod_med].'</td>					
                                <td align="center" width="60">'.number_format($cant,2 , ',', '.').'</td>								
                                <td align="right" width="60">'.number_format($precio,4 , ',', '.').'</td>	
                                <td align="right" width="67">'.number_format($total_pedf,4 , ',', '.').'</td>	
                                
                            </tr>';
                    $linp_ant=$dpef_cod_linp;
                    $sub_total+=$total_pedf;
                    $i++;
                    $ctrl_ped++;
                }while($oIfx->SiguienteRegistro());
                
            }
            
        }
        $oIfx->Free(); 

        //MEDICINA

               $sql = "select	linp_cod_linp, p.pedf_cod_pedf,	d.dpef_cod_prod,	d.dpef_nom_prod,	d.dpef_cant_dfac, d.dpef_bode_ori,
							d.dpef_precio_dfac,	d.dpef_cod_bode, 	d.dpef_cod_unid,	d.dpef_por_iva,
							d.dpef_por_ice,		d.dpef_des1_dfac,	d.dpef_des2_dfac,	d.dpef_por_dsg,
							d.dpef_cod_linp,	d.dpef_est_dpef,	d.dpef_cant_ori,
							d.dpef_cant_dev,	p.pedf_user_web,	d.dpef_pre_fact,	p.pedf_fech_fact,
							p.pedf_hor_ini,		p.pedf_num_preimp,	p.pedf_est_fact, 
							d.dpef_cant_pre,	d.dpef_precio_pre,	d.dpef_cod_dpef,
							d.dpef_mont_total,  d.dpef_des1_pre,    d.dpef_tot_pre, d.dpef_cod_med, linp_des_linp
							from saepedf p, saedpef d, saelinp, saeprod pr where
							linp_cod_empr = d.dpef_cod_empr and
							linp_cod_linp = d.dpef_cod_linp and
							p.pedf_cod_pedf = d.dpef_cod_pedf and
                            d.dpef_cod_prod = pr.prod_cod_prod and
                            pr.prod_cod_empr= $idempresa and 
							p.pedf_cod_empr = $idempresa and
							p.pedf_cod_sucu = $idsucursal and
							p.pedf_cod_clpv = $cliente and 
							p.pedf_cod_admi = $admision and
							p.pedf_est_fact != 'GR' and
							p.pedf_est_fact != 'AN' and
							d.dpef_est_dpef != 'A' and
                            d.dpef_pre_fact='S' and 
							d.dpef_cant_dfac > 0 
                            and d.dpef_cod_linp in (1, 12)
							order by p.pedf_fech_fact ASC ";

        if($oIfx->Query($sql)){
            if($oIfx->NumFilas()>0){                
                $i=0;
                do{
                    $dpef_cod_prod=$oIfx->f('dpef_cod_prod');
                    $codig_t=$arrayCodTari[$dpef_cod_prod];

                    

                    $dpef_nom_prod =$oIfx->f('dpef_nom_prod');
                    $dpef_cant_dfac =$oIfx->f('dpef_cant_dfac');
                    $dpef_precio_dfac =$oIfx->f('dpef_precio_dfac');
                    $dpef_cod_linp =$oIfx->f('dpef_cod_linp');
                    $dpef_mont_total =$oIfx->f('dpef_mont_total');
                    $dpef_cod_med =$oIfx->f('dpef_cod_med');
                    $pedf_fech_fact =$oIfx->f('pedf_fech_fact');
                    $dpef_por_iva =$oIfx->f('dpef_por_iva');
                    $dpef_pre_fact = $oIfx->f('dpef_pre_fact');
                    $dpef_bode_ori = $oIfx->f('dpef_bode_ori');
    
                    if($dpef_bode_ori!=$bode_tar){
                        $dpef_cod_prod='';
                    }

                    if(!empty($codig_t) &&empty($dpef_cod_prod)){
                        $dpef_cod_prod= $codig_t;
                    }
    
                    if ($dpef_pre_fact == 'S') {
                            $cant = $oIfx->f('dpef_cant_pre');
                            $precio = round($oIfx->f('dpef_precio_pre'), 4);
                            $descuento = $oIfx->f('dpef_des1_pre');
    
                            if ($descuento > 0) {
                                $total_pedf = round($oIfx->f('dpef_tot_pre'), 4);
                            } else {
                                $total_pedf = $cant * $precio;
                                $descuento = 0.0;
                            }
    
                            
                    } else {
                        $cant = $oIfx->f('dpef_cant_dfac');
                        $precio = round($oIfx->f('dpef_precio_dfac'), 2);
                        $descuento = $oIfx->f('dpef_des1_dfac');
                        
                        if(empty($precio)){
                            $sqlPrecio = "select ppr_pre_raun from saeppr where ppr_cod_prod = '$dpef_cod_prod' and ppr_cod_nomp = $resp_precio";
                            $precio = round(consulta_string($sqlPrecio, 'ppr_pre_raun', $oIfxA, 0), 4);
                        }
    
                        if ($descuento > 0) {
                            $total_pedf = round($oIfx->f('dpef_mont_total'), 4);
                        } else {
                            $total_pedf = $cant * $precio;
                        }
    
                        
                    }
                    //monto de iva
                    if ($dpef_por_iva > 0) {
                        $empr_iva_empr = $dpef_por_iva;
                    }
                    if ($dpef_por_iva > 0) {
                        $totalConIva += $total_pedf;
                    } else {
                        $totalSinIva += $total_pedf;
                    }
                    
                    
                    $html.='<tr>
                                <td align="center"  width="90">'.$pedf_fech_fact.'</td>	
                                <td align="center"  width="85">'.$dpef_cod_prod.'</td>	
                                <td align="justify" width="195">'.$dpef_nom_prod.'</td>
                                <td align="justify" width="100">'.$arrayClpv[$dpef_cod_med].'</td>					
                                <td align="center" width="60">'.number_format($cant,2 , ',', '.').'</td>								
                                <td align="right" width="60">'.number_format($precio,4 , ',', '.').'</td>	
                                <td align="right" width="67">'.number_format($total_pedf,4 , ',', '.').'</td>	
                                
                            </tr>';
                    $linp_ant=$dpef_cod_linp;
                    $sub_total+=$total_pedf;
                    $i++;
                    $ctrl_ped++;
                }while($oIfx->SiguienteRegistro());
                
            }
            
        }
        $oIfx->Free();

        //INSUMOS

               $sql = "select	linp_cod_linp, p.pedf_cod_pedf,	d.dpef_cod_prod,	d.dpef_nom_prod,	d.dpef_cant_dfac, d.dpef_bode_ori,
							d.dpef_precio_dfac,	d.dpef_cod_bode, 	d.dpef_cod_unid,	d.dpef_por_iva,
							d.dpef_por_ice,		d.dpef_des1_dfac,	d.dpef_des2_dfac,	d.dpef_por_dsg,
							d.dpef_cod_linp,	d.dpef_est_dpef,	d.dpef_cant_ori,
							d.dpef_cant_dev,	p.pedf_user_web,	d.dpef_pre_fact,	p.pedf_fech_fact,
							p.pedf_hor_ini,		p.pedf_num_preimp,	p.pedf_est_fact, 
							d.dpef_cant_pre,	d.dpef_precio_pre,	d.dpef_cod_dpef,
							d.dpef_mont_total,  d.dpef_des1_pre,    d.dpef_tot_pre, d.dpef_cod_med, linp_des_linp
							from saepedf p, saedpef d, saelinp, saeprod pr where
							linp_cod_empr = d.dpef_cod_empr and
							linp_cod_linp = d.dpef_cod_linp and
							p.pedf_cod_pedf = d.dpef_cod_pedf and
                            d.dpef_cod_prod = pr.prod_cod_prod and
                            pr.prod_cod_empr= $idempresa and 
							p.pedf_cod_empr = $idempresa and
							p.pedf_cod_sucu = $idsucursal and
							p.pedf_cod_clpv = $cliente and 
							p.pedf_cod_admi = $admision and
							p.pedf_est_fact != 'GR' and
							p.pedf_est_fact != 'AN' and
							d.dpef_est_dpef != 'A' and
                            d.dpef_pre_fact='S' and 
							d.dpef_cant_dfac > 0 
                            and d.dpef_cod_linp in (2, 11)
							order by p.pedf_fech_fact ASC ";

        if($oIfx->Query($sql)){
            if($oIfx->NumFilas()>0){                
                $i=0;
                do{
                    $dpef_cod_prod=$oIfx->f('dpef_cod_prod');
                    $codig_t=$arrayCodTari[$dpef_cod_prod];

                    

                    $dpef_nom_prod =$oIfx->f('dpef_nom_prod');
                    $dpef_cant_dfac =$oIfx->f('dpef_cant_dfac');
                    $dpef_precio_dfac =$oIfx->f('dpef_precio_dfac');
                    $dpef_cod_linp =$oIfx->f('dpef_cod_linp');
                    $dpef_mont_total =$oIfx->f('dpef_mont_total');
                    $dpef_cod_med =$oIfx->f('dpef_cod_med');
                    $pedf_fech_fact =$oIfx->f('pedf_fech_fact');
                    $dpef_por_iva =$oIfx->f('dpef_por_iva');
                    $dpef_pre_fact = $oIfx->f('dpef_pre_fact');
                    $dpef_bode_ori = $oIfx->f('dpef_bode_ori');
    
                    if($dpef_bode_ori!=$bode_tar){
                        $dpef_cod_prod='';
                    }

                    if(!empty($codig_t) &&empty($dpef_cod_prod)){
                        $dpef_cod_prod= $codig_t;
                    }
    
                    if ($dpef_pre_fact == 'S') {
                            $cant = $oIfx->f('dpef_cant_pre');
                            $precio = round($oIfx->f('dpef_precio_pre'), 4);
                            $descuento = $oIfx->f('dpef_des1_pre');
    
                            if ($descuento > 0) {
                                $total_pedf = round($oIfx->f('dpef_tot_pre'), 4);
                            } else {
                                $total_pedf = $cant * $precio;
                                $descuento = 0.0;
                            }
    
                            
                    } else {
                        $cant = $oIfx->f('dpef_cant_dfac');
                        $precio = round($oIfx->f('dpef_precio_dfac'), 2);
                        $descuento = $oIfx->f('dpef_des1_dfac');
                        
                        if(empty($precio)){
                            $sqlPrecio = "select ppr_pre_raun from saeppr where ppr_cod_prod = '$dpef_cod_prod' and ppr_cod_nomp = $resp_precio";
                            $precio = round(consulta_string($sqlPrecio, 'ppr_pre_raun', $oIfxA, 0), 4);
                        }
    
                        if ($descuento > 0) {
                            $total_pedf = round($oIfx->f('dpef_mont_total'), 4);
                        } else {
                            $total_pedf = $cant * $precio;
                        }
    
                        
                    }
                    //monto de iva
                    if ($dpef_por_iva > 0) {
                        $empr_iva_empr = $dpef_por_iva;
                    }
                    if ($dpef_por_iva > 0) {
                        $totalConIva += $total_pedf;
                    } else {
                        $totalSinIva += $total_pedf;
                    }
                    
                    
                    $html.='<tr>
                                <td align="center"  width="90">'.$pedf_fech_fact.'</td>	
                                <td align="center"  width="85">'.$dpef_cod_prod.'</td>	
                                <td align="justify" width="195">'.$dpef_nom_prod.'</td>
                                <td align="justify" width="100">'.$arrayClpv[$dpef_cod_med].'</td>					
                                <td align="center" width="60">'.number_format($cant,2 , ',', '.').'</td>								
                                <td align="right" width="60">'.number_format($precio,4 , ',', '.').'</td>	
                                <td align="right" width="67">'.number_format($total_pedf,4 , ',', '.').'</td>	
                                
                            </tr>';
                    $linp_ant=$dpef_cod_linp;
                    $sub_total+=$total_pedf;
                    $i++;
                    $ctrl_ped++;
                }while($oIfx->SiguienteRegistro());
                
            }
            
        }
        $oIfx->Free();
        //SERVICIOS INSTITUCIONALES

               $sql = "select	linp_cod_linp, p.pedf_cod_pedf,	d.dpef_cod_prod,	d.dpef_nom_prod,	d.dpef_cant_dfac, d.dpef_bode_ori,
							d.dpef_precio_dfac,	d.dpef_cod_bode, 	d.dpef_cod_unid,	d.dpef_por_iva,
							d.dpef_por_ice,		d.dpef_des1_dfac,	d.dpef_des2_dfac,	d.dpef_por_dsg,
							d.dpef_cod_linp,	d.dpef_est_dpef,	d.dpef_cant_ori,
							d.dpef_cant_dev,	p.pedf_user_web,	d.dpef_pre_fact,	p.pedf_fech_fact,
							p.pedf_hor_ini,		p.pedf_num_preimp,	p.pedf_est_fact, 
							d.dpef_cant_pre,	d.dpef_precio_pre,	d.dpef_cod_dpef,
							d.dpef_mont_total,  d.dpef_des1_pre,    d.dpef_tot_pre, d.dpef_cod_med, linp_des_linp
							from saepedf p, saedpef d, saelinp, saeprod pr where
							linp_cod_empr = d.dpef_cod_empr and
							linp_cod_linp = d.dpef_cod_linp and
							p.pedf_cod_pedf = d.dpef_cod_pedf and
                            d.dpef_cod_prod = pr.prod_cod_prod and
                            pr.prod_cod_empr= $idempresa and 
							p.pedf_cod_empr = $idempresa and
							p.pedf_cod_sucu = $idsucursal and
							p.pedf_cod_clpv = $cliente and 
							p.pedf_cod_admi = $admision and
							p.pedf_est_fact != 'GR' and
							p.pedf_est_fact != 'AN' and
							d.dpef_est_dpef != 'A' and
                            d.dpef_pre_fact='S' and 
							d.dpef_cant_dfac > 0 
                            and d.dpef_cod_linp in (4, 13)
							order by p.pedf_fech_fact ASC ";

        if($oIfx->Query($sql)){
            if($oIfx->NumFilas()>0){                
                $i=0;
                do{
                    $dpef_cod_prod=$oIfx->f('dpef_cod_prod');
                    $codig_t=$arrayCodTari[$dpef_cod_prod];

                    

                    $dpef_nom_prod =$oIfx->f('dpef_nom_prod');
                    $dpef_cant_dfac =$oIfx->f('dpef_cant_dfac');
                    $dpef_precio_dfac =$oIfx->f('dpef_precio_dfac');
                    $dpef_cod_linp =$oIfx->f('dpef_cod_linp');
                    $dpef_mont_total =$oIfx->f('dpef_mont_total');
                    $dpef_cod_med =$oIfx->f('dpef_cod_med');
                    $pedf_fech_fact =$oIfx->f('pedf_fech_fact');
                    $dpef_por_iva =$oIfx->f('dpef_por_iva');
                    $dpef_pre_fact = $oIfx->f('dpef_pre_fact');
                    $dpef_bode_ori = $oIfx->f('dpef_bode_ori');
    
                    if($dpef_bode_ori!=$bode_tar){
                        $dpef_cod_prod='';
                    }

                    if(!empty($codig_t) &&empty($dpef_cod_prod)){
                        $dpef_cod_prod= $codig_t;
                    }
    
                    if ($dpef_pre_fact == 'S') {
                            $cant = $oIfx->f('dpef_cant_pre');
                            $precio = round($oIfx->f('dpef_precio_pre'), 4);
                            $descuento = $oIfx->f('dpef_des1_pre');
    
                            if ($descuento > 0) {
                                $total_pedf = round($oIfx->f('dpef_tot_pre'), 4);
                            } else {
                                $total_pedf = $cant * $precio;
                                $descuento = 0.0;
                            }
    
                            
                    } else {
                        $cant = $oIfx->f('dpef_cant_dfac');
                        $precio = round($oIfx->f('dpef_precio_dfac'), 2);
                        $descuento = $oIfx->f('dpef_des1_dfac');
                        
                        if(empty($precio)){
                            $sqlPrecio = "select ppr_pre_raun from saeppr where ppr_cod_prod = '$dpef_cod_prod' and ppr_cod_nomp = $resp_precio";
                            $precio = round(consulta_string($sqlPrecio, 'ppr_pre_raun', $oIfxA, 0), 4);
                        }
    
                        if ($descuento > 0) {
                            $total_pedf = round($oIfx->f('dpef_mont_total'), 4);
                        } else {
                            $total_pedf = $cant * $precio;
                        }
    
                        
                    }
                    //monto de iva
                    if ($dpef_por_iva > 0) {
                        $empr_iva_empr = $dpef_por_iva;
                    }
                    if ($dpef_por_iva > 0) {
                        $totalConIva += $total_pedf;
                    } else {
                        $totalSinIva += $total_pedf;
                    }
                    
                    
                    $html.='<tr>
                                <td align="center"  width="90">'.$pedf_fech_fact.'</td>	
                                <td align="center"  width="85">'.$dpef_cod_prod.'</td>	
                                <td align="justify" width="195">'.$dpef_nom_prod.'</td>
                                <td align="justify" width="100">'.$arrayClpv[$dpef_cod_med].'</td>					
                                <td align="center" width="60">'.number_format($cant,2 , ',', '.').'</td>								
                                <td align="right" width="60">'.number_format($precio,4 , ',', '.').'</td>	
                                <td align="right" width="67">'.number_format($total_pedf,4 , ',', '.').'</td>	
                                
                            </tr>';
                    $linp_ant=$dpef_cod_linp;
                    $sub_total+=$total_pedf;
                    $i++;
                    $ctrl_ped++;
                }while($oIfx->SiguienteRegistro());
                
            }
            
        }
        //VALIDACION PREFACTURA GENERADA
        if($ctrl_ped!=0){

            //calculo del iva 
            $montolva = (($totalConIva * $empr_iva_empr) / 100);
            $html.="</table>";
            $html.="<table  cellspacing='0' cellpadding='1' style='margin-top:12px; border:1px  solid black; width:100%;font-size:12px;'>  
            <tr>
            <td align='left'  width='400'><b>NOMBRE:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>".$nombres."</u>
            <br><b>NO. IDENTIFICACION:</b>&nbsp;<u>".$identificacion."</u>
            <br><b>AREA:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>$departamento</u>                  
            </td>
            
            <td width='300'><table cellspacing='0' cellpadding='1' border='0' > 
            <tr>
            <td style='border:1px  solid black;' align='center'  width='220'><b>SUBTOTAL</b></td>
            <td style='border:1px  solid black;' align='right'  width='65'>".number_format($sub_total,4 , ',', '.')."</td>
            </tr>
            <tr>
            <td style='border:1px  solid black;' align='center'  width='220'><b>VALOR IVA</b></td>
            <td style='border:1px  solid black;' align='right'  width='65'>".number_format(round($montolva, 4), 4, ',', '.')."</td>
            </tr>
            <tr>
            <td align='center'  width='220'><b>TOTAL VALOR SOLICITADO</b></td>
            <td style='border:1px  solid black;' align='right'  width='65'>". number_format(round($totalConIva+$totalSinIva + $montolva, 4), 4, ',', '.')."</td>
            </tr>
            </table>
            
            
            
            
            </td>
            </tr>";
            
            $html.="</table>
            <table style='width:100%; margin-top: 65px' align='center' >
    <tr>

        <td style='width:30%; font-size:12px; text-align: center;border-top : 2px solid black;'>Revisado por:<br>" . $nombres . "</td>
        <td style='width:5%;'></td>
        <td style='width:30%; font-size:12px; text-align: center;border-top : 2px solid black;'></td>
        <td style='width:5%;'></td>

    </tr>
        </table>";
        }
        else{
            $html='<font color="red" size="12px">PREFACTURA NO GENERADA</font>';
        }
	
	

    $table='<page backimgw="100%" backtop="5mm" backbottom="7mm" backleft="7mm" backright="5mm">';
    $table.=$html;
    $table.='</page>';

    

    if($tipo==1){
		return $table;
       
	}
	if($tipo==2){
        $html2pdf = new HTML2PDF('P', 'A4', 'es', true, 'UTF-8');
        $html2pdf->WriteHTML($table);
        $html2pdf->Output('planilla.pdf', '');
        $ruta = DIR_FACTELEC . 'modulos/clinico_prefactura/planillas/planilla_' . $admision . '.pdf';
        $html2pdf->Output($ruta, 'F');

		return $ruta;
	}
    

}



?>