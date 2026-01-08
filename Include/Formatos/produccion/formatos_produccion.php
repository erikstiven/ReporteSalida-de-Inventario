<?
set_time_limit(3000);
ini_set('memory_limit', '20000M');

function formato_produccion( $idempresa="", $idproceso="", $num_op='' ){
	global $DSN_Ifx, $DSN;

    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

	$oCon = new Dbo;
    $oCon->DSN = $DSN;
	$oCon->Conectar(); 
    	
    try{
		$sql = "select c.PROCESO from moda.procesos c where c.ID_PROCESO = $idproceso; ";
		$proceso = consulta_string_func($sql, 'proceso', $oCon, 1);

		// COLOR
		$sql = "select c.id_color, c.color from moda.color c where
			c.empr_cod_empr = $idempresa ";
		unset($array_color);
		$array_color  = array_dato($oCon, $sql, 'id_color', 'color');

		// TALLA
		$sql = "select t.id_talla, t.talla, t.sigla from moda.talla t where
			t.empr_cod_empr = $idempresa ";
		unset($array_talla);
		$array_talla  = array_dato($oCon, $sql, 'id_talla', 'sigla');              

		// BODEGA
		$sql = "select   bode_cod_bode, bode_nom_bode  from saebode where
			bode_cod_empr = $idempresa ";
		unset($array_bode);
		$array_bode = array_dato($oIfx, $sql, 'bode_cod_bode', 'bode_nom_bode');

		// BODE LOTE
		$sql = "select b.id_bode_lote, b.bode_cod_bode from moda.bode_lote b where b.empr_cod_empr = $idempresa ";
		unset($array_blote);
		if($oCon->Query($sql)){
			if($oCon->NumFilas()>0){
				do{
					$array_blote [$oCon->f('bode_cod_bode')] = $oCon->f('bode_cod_bode');
				}while($oCon->SiguienteRegistro());
			}
		}

		//UNIDAD
		$sql = "select  unid_cod_unid, unid_sigl_unid from saeunid where unid_cod_empr = $idempresa ";
		unset($array_unid);
		$array_unid = array_dato($oIfx, $sql, 'unid_cod_unid', 'unid_sigl_unid');

		// DATOS CABECERA
		$sql = "select m.id_mov_cab, m.pedf_num_preimp, m.pedf_cod_pedf,
					m.mano_obra, m.cifs, m.otros, m.minutos, m.total_cifs,
					m.total_mp, m.msn
					from moda.mov_cab m where
					m.empr_cod_empr   = $idempresa and
					m.pedf_num_preimp = '$num_op' and
					m.id_proceso      = $idproceso ";
		//                echo $sql;
		if($oCon->Query($sql)){
			if($oCon->NumFilas()>0){
				do{
					$id_cab   = $oCon->f('id_mov_cab');
					$pedf_cod = $oCon->f('pedf_cod_pedf');
					$mano     = $oCon->f('mano_obra');
					$cifs     = $oCon->f('cifs');
					$otros    = $oCon->f('otros');
					$min      = round($oCon->f('minutos'),3);
					$min_tot  = round($oCon->f('total_cifs'),3);
					$msn_prod = $oCon->f('msn');
				}while($oCon->SiguienteRegistro());
			}
		}

		// DETALLE
		$sql = "select c.id_progra, c.id_progra_det, c.pedf_num_preimp, c.pedf_cod_pedf,
					c.dpef_cod_dpef, c.id_talla, c.id_color, c.dpef_ref_clpv, c.cod_refe,
					c.cantidad, c.costo_proc, c.costo_mp_cif, c.costo_unit
					from moda.mov_proc c where
					c.empr_cod_empr = $idempresa and
					c.id_mov_cab    = $id_cab and
					c.id_proceso    = $idproceso ";
		unset($array);
		if($oCon->Query($sql)){
			if($oCon->NumFilas()>0){
				do{
					$id_progra   = $oCon->f('id_progra');
					$id_dprog    = $oCon->f('id_progra_det');
					$talla       = $array_talla[$oCon->f('id_talla')];
					$color       = $array_color[$oCon->f('id_color')];
					$refe_cod    = $oCon->f('cod_refe');
					$refe_clpv   = $oCon->f('dpef_ref_clpv');
					$cant        = $oCon->f('cantidad');
					$array []    = array( $id_progra, $id_dprog, $talla,  $color,  $refe_cod, $refe_clpv, $cant );
				}while($oCon->SiguienteRegistro());
			}
		}

		$tabla_det  =  '';
		$tabla_det .= '<fieldset style="border:#999999 1px solid; padding:2px; text-align:center; width:99%; background-color:#FFFFFF ">';
		$tabla_det .= '<table align="center" border="0" cellpadding="2" cellspacing="1" width="99%" class="footable">';     
		$tabla_det .= '<tr>
							<td class="fecha_letra" align="center" colspan="6">ORDEN PRODUCCION: '.$num_op.' - PRODUCCION PROCESO DE '.$proceso.'</td>
					   </tr>';
		$tabla_det .= '<tr align="center">
							<td align="left" class="fecha_letra" scope="row">MANO OBRA:</td>   
							<td align="right" scope="row" align="left">'.$mano.' x Minuto</td>   
							<td align="left" class="fecha_letra" scope="row">GASTO FABRIC.:</td>   
							<td align="right" scope="row" align="left">'.$cifs.' x Minuto</td> 
							<td align="left" class="fecha_letra" scope="row">OTROS:</td>    
							<td align="right" scope="row" align="left">'.$otros.' x Minuto</td>   
					   </tr>';
		$tabla_det .= '<tr height="20" align="center">     
							<td align="left" class="fecha_letra" scope="row">MINUTOS:</td>  
							<td  align="right" scope="row" align="left">'.$min.'</td>  
							<td align="left" class="fecha_letra" scope="row">TOTAL MINUTOS USD:</td>  
							<td align="right" scope="row" align="left">'.$min_tot.'</td>  
							<td align="left" scope="row" >OBSERVACION:</td>  
							<td align="left" scope="row" align="left">'.$msn_prod.'</td> 
						</tr>';
		// CABECERA PEDIDO
		$sql = "select pedf_nom_cliente, pedf_fech_fact, pedf_fech_venc, pedf_opc_pedf  ,  pedf_depar_clpv 
					from saepedf where
					pedf_cod_empr = $idempresa and
					pedf_cod_pedf = $pedf_cod ";
		if($oIfx->Query($sql)){
			if($oIfx->NumFilas()>0){
				do{
					$clpv_nom  = $oIfx->f('pedf_nom_cliente');
					$fecha_emis = fecha_mysql_func($oIfx->f('pedf_fech_fact'));
					$fecha_venc = fecha_mysql_func($oIfx->f('pedf_fech_venc'));
					$orde_comp  = $oIfx->f('pedf_opc_pedf');                            
				}while($oIfx->SiguienteRegistro());
			}
		}
		$tabla_det .= '<tr>
					<td colspan="6">
						<table align="center" border="0" cellpadding="2" cellspacing="1" width="99%" class="footable">                                       
							<tr>
								<td  align="left">ORDEN:</td>
								<td  align="left">'.$orde_comp.'</td>
								<td  align="left">FECHA EMISION:</td>
								<td  align="left">'.$fecha_emis.'</td>
								<td  align="left">CLIENTE:</td>
								<td  align="left">'.$clpv_nom.'</td>
								<td align="left">ORDEN PRODUCCION:</td>
								<td align="left">'.$num_op.'</td>
							</tr>
							<tr>
								<td align="left">FECHA ENTREGA CD:</td>											
								<td align="left">'.$fecha_venc.'</td>
								<td align="left">DEPTO:</td>
								<td align="left">'.$depar_clpv.'</td>
								<td align="left">REF. PRENDA CLIENTE:</td>
								<td align="left">'.$refe_clpv.'</td>
								<td align="left">REF. PRENDA INTERNA:</td>
								<td align="left">'.$refe_cod.'</td>													                                                        
							</tr>
						</table>
					</td>
				</tr>';
		// CANTIDADDES
		$tabla_det .= '<tr>
					<td colspan="6">
						<table align="center" border="0" cellpadding="2" cellspacing="1" width="60%" class="footable">';
		$tabla_det .= '     <tr align="center">
								<td class="fecha_letra" scope="row" colspan="6">CANTIDAD DE PRENDAS</td>    
							</tr>'; 
		$tabla_det .= '             <tr align="center">
								<td class="fecha_letra" scope="row">N.-</td>   
								<td class="fecha_letra" scope="row">COLOR</td>   
								<td class="fecha_letra" scope="row">TALLA</td>   
								<td class="fecha_letra" scope="row">CANTIDAD</td>  
							</tr>';   
		if(count($array)>0){
		$i = 1;
		foreach ($array as $val){
			$talla = $val[2];
			$color = $val[3];
			$cant  = $val[6]; 
			$tabla_det .='<tr align="left" valign="top">
								<td class="ficha_prod" scope="row" align="right">'.$i.'</td>
								<td class="ficha_prod" scope="row">'.$color.'</td>   
								<td class="ficha_prod" scope="row">'.$talla.'</td>   
								<td class="ficha_prod" scope="row"  align="right">'.$cant.'</td>';
			$tabla_det .= '</tr>';
			$i++;
			}
		}
		$tabla_det .= '         </table>';
		$tabla_det .= '     </td>';
		$tabla_det .= '</tr>';

		// DEVOLUCION MP-INSUMOS
		$tabla_det .= '<tr>
					<td colspan="6">
						<table align="center" border="0" cellpadding="2" cellspacing="1" width="60%" class="footable">';
		$tabla_det .= '             <tr align="center">
								<td class="fecha_letra" scope="row" colspan="6">DEVOLUCION MP-INSUMOS</td>    
							</tr>'; 
		$tabla_det .= '             <tr align="center">
								<td class="fecha_letra" scope="row">N.-</td>   
								<td class="fecha_letra" scope="row">BODEGA</td>  
								<td class="fecha_letra" scope="row">CODIGO</td>   
								<td class="fecha_letra" scope="row">PRODUCTO</td>                                                   
								<td class="fecha_letra" scope="row">UNIDAD</td>  
								<td class="fecha_letra" scope="row">CANTIDAD</td>   
							</tr>';   
		$sql = "select m.prod_cod_prod, m.prod_nom_prod, m.bode_cod_bode, m.unid_cod_unid, m.id_tipo_tela , m.cant_ingr
					from moda.mov_prod m where
					m.empr_cod_empr   = $idempresa and
					m.pedf_num_preimp = '$num_op' and
					m.id_proceso      = $idproceso ";
		$i = 1;
		if($oCon->Query($sql)){
			if($oCon->NumFilas()>0){
				do{
					$prod_cod   = $oCon->f('prod_cod_prod');
					$prod_nom   = $oCon->f('prod_nom_prod');
					$bode_nom   = $array_bode[$oCon->f('bode_cod_bode')];
					$unid_nom   = $array_unid[$oCon->f('unid_cod_unid')];
					$cant_devol = round($oCon->f('cant_ingr'),6);
					$tabla_det .='<tr height="20" align="left" valign="top">
									<td class="ficha_prod" scope="row" align="right">'.$i.'</td>
									<td class="ficha_prod" scope="row">'.$bode_nom.'</td>  
									<td class="ficha_prod" scope="row">'.$prod_cod.'</td>   
									<td class="ficha_prod" scope="row">'.$prod_nom.'</td>                                               
									<td class="ficha_prod" scope="row">'.$unid_nom.'</td>  
									<td class="ficha_prod" scope="row"  align="right">'.$cant_devol.'</td>';
					$tabla_det .= '</tr>';
					$i++;
				}while($oCon->SiguienteRegistro());
			}
		}
		$tabla_det .= '         </table>';
		$tabla_det .= '     </td>';
		$tabla_det .= '</tr>';
		$tabla_det .= '</table></fieldset>';

		
	} catch (Exception $e) {
        $tabla_det .=$e->getMessage();
    }
     	
	return $tabla_det;
	
}


function formato_bodega_prod( $idempresa="", $idproceso="", $num_op='' ){
	global $DSN_Ifx, $DSN;

    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

	$oCon = new Dbo;
    $oCon->DSN = $DSN;
	$oCon->Conectar(); 
    	
    try{
			$sql = "select c.PROCESO from moda.procesos c where c.ID_PROCESO = $idproceso ";
			$proceso = consulta_string_func($sql, 'proceso', $oCon, 1);

			// MSN
			$sql = "select msn from moda.mov_prod m where
						m.empr_cod_empr   = $idempresa and
						m.pedf_num_preimp = '$num_op' and
						m.id_proceso      = $idproceso ";
			$msn = consulta_string_func($sql, 'msn', $oCon, '');

			// COLOR
			$sql = "select c.id_color, c.color from moda.color c where c.empr_cod_empr = $idempresa ";
			unset($array_color);
			$array_color  = array_dato($oCon, $sql, 'id_color', 'color');

			// TALLA
			$sql = "select t.id_talla, t.talla, t.sigla from moda.talla t where	t.empr_cod_empr = $idempresa ";
			unset($array_talla);
			$array_talla  = array_dato($oCon, $sql, 'id_talla', 'sigla');              

			// BODEGA
			$sql = "select   bode_cod_bode, bode_nom_bode  from saebode where	bode_cod_empr = $idempresa ";
			unset($array_bode);
			$array_bode = array_dato($oIfx, $sql, 'bode_cod_bode', 'bode_nom_bode');

			// BODE LOTE
			$sql = "select b.id_bode_lote, b.bode_cod_bode from moda.bode_lote b where b.empr_cod_empr = $idempresa ";
			unset($array_blote);
			if($oCon->Query($sql)){
				if($oCon->NumFilas()>0){
					do{
						$array_blote [$oCon->f('bode_cod_bode')] = $oCon->f('bode_cod_bode');
					}while($oCon->SiguienteRegistro());
				}
			}

			//UNIDAD
			$sql = "select  unid_cod_unid, unid_sigl_unid from saeunid where unid_cod_empr = $idempresa ";
			unset($array_unid);
			$array_unid = array_dato($oIfx, $sql, 'unid_cod_unid', 'unid_sigl_unid');

			// CANTIDADS POR PEDIDO
			$sql = "select  d.dpef_id_progra, d.dpef_progr_det,  
						d.dpef_cod_refe,  d.dpef_id_color, (d.dpef_cant_dfac) as cant, d.dpef_id_talla, d.dpef_nom_prod, 
						d.dpef_cod_prod, d.dpef_cod_dpef, pe.pedf_cod_pedf
						from saeprod p, saedpef  d , saepedf pe where
						pe.pedf_cod_pedf  = d.dpef_cod_pedf and
						pe.pedf_cod_empr  = 1  and
						p.prod_cod_prod   = d.dpef_cod_prod and
						p.prod_cod_sucu   = d.dpef_cod_sucu and
						p.prod_cod_empr   = $idempresa and
						d.dpef_cod_empr   = $idempresa and
						pe.pedf_est_fact = 'PE'  and
						pe.pedf_est_op   = 'S' and
						--d.dpef_est_inv   = 'S' and
						d.dpef_pack_sn   = 'N' and
						d.dpef_conj_sn   = 'N' and
						--COALESCE(d.dpef_id_proceso, 0 ) = $idproceso and
						pe.pedf_num_preimp = '$num_op'  and
							p.prod_id_color is not null
						order by d.dpef_id_progra, d.dpef_progr_det, d.dpef_id_talla ";
			//echo $sql; exit;
			unset($array_det);
			if($oIfx->Query($sql)){
				if($oIfx->NumFilas()>0){
					do{
						$array_det [$oIfx->f('pedf_cod_pedf')][] = array( $oIfx->f('dpef_cod_prod'), $oIfx->f('dpef_nom_prod') ,$oIfx->f('dpef_id_progra'),
																	$oIfx->f('dpef_progr_det'), $oIfx->f('dpef_cod_refe'), $oIfx->f('dpef_id_color'),
																	$oIfx->f('dpef_id_talla'), $oIfx->f('cant'), $oIfx->f('dpef_cod_dpef'),
																	$oIfx->f('pedf_cod_pedf') );
					}while($oIfx->SiguienteRegistro());
				}
			}

			$_SESSION['U_ARRAY_CANT_BBORD'] = $array_det;

			$sql = "select pe.pedf_nom_cliente,  pe.pedf_cod_pedf, pe.pedf_fech_fact, pe.pedf_fech_venc, pe.pedf_opc_pedf  ,
							d.dpef_ref_clpv,  d.dpef_id_progra, d.dpef_progr_det,  
							d.dpef_cod_refe, p.prod_id_color, pe.pedf_depar_clpv , pe.pedf_num_preimp, sum(d.dpef_cant_dfac) as cant
							from saeprod p, saedpef  d , saepedf pe where
							pe.pedf_cod_pedf = d.dpef_cod_pedf and
							pe.pedf_cod_empr = $idempresa and
							p.prod_cod_prod = d.dpef_cod_prod and
							p.prod_cod_sucu = d.dpef_cod_sucu and
							p.prod_cod_empr = $idempresa and
							d.dpef_cod_empr = $idempresa and
							pe.pedf_est_fact = 'PE'  and
							pe.pedf_est_op   = 'S' and
							--d.dpef_est_inv   = 'S' and
							d.dpef_pack_sn   = 'N' and
							d.dpef_conj_sn   = 'N' and
							--COALESCE(d.dpef_id_proceso, 0 ) = $idproceso and
							pe.pedf_num_preimp = '$num_op' and
							p.prod_id_color is not null
							group by pe.pedf_nom_cliente,  pe.pedf_cod_pedf, 
							pe.pedf_fech_fact, pe.pedf_fech_venc, pe.pedf_opc_pedf  , d.dpef_ref_clpv,  
							d.dpef_id_progra, d.dpef_progr_det, d.dpef_cod_refe, p.prod_id_color , pe.pedf_depar_clpv  , pe.pedf_num_preimp
							order by d.dpef_id_progra, d.dpef_progr_det ";
							//echo $sql; exit;
			unset($array);
			//        $oReturn->alert($sql);
			if ($oIfx->Query($sql)){
				if($oIfx->NumFilas()>0){
					do {
						$cliente    = $oIfx->f('pedf_nom_cliente');
						$fecha_emis = fecha_mysql_func($oIfx->f('pedf_fech_fact'));
						$fecha_venc = fecha_mysql_func($oIfx->f('pedf_fech_venc'));
						$orde_comp  = $oIfx->f('pedf_opc_pedf');
						$ref_clpv   = $oIfx->f('dpef_ref_clpv');
						$id_progra  = $oIfx->f('dpef_id_progra');
						$id_dprogr  = $oIfx->f('dpef_progr_det');
						$cod_refe   = $oIfx->f('dpef_cod_refe');
						$cantidad   = $oIfx->f('cant');
						$depar_clpv = $oIfx->f('pedf_depar_clpv');
						$preimp     = $oIfx->f('pedf_num_preimp');
						$color      = $array_color[$oIfx->f('prod_id_color')];
						$pedf_cod   = $oIfx->f('pedf_cod_pedf');
						$id_color   = $oIfx->f('prod_id_color');

						$array []   = array($cliente,   $fecha_emis, $fecha_venc, $orde_comp, $ref_clpv, $id_progra,
											$id_dprogr, $cod_refe,   $cantidad ,  $depar_clpv , $preimp, $color, $pedf_cod, $id_color );
					}while($oIfx->SiguienteRegistro());
				}
			}
			$oIfx->Free();  

			unset($array_pr);
			$x=1;
			$tabla_det = '';
			$tabla_det .='<fieldset style="border:#999999 1px solid; padding:2px; text-align:center; width:98%; background-color:#FFFFFF ">';
			$tabla_det .='<table align="center" border="0" cellpadding="2" cellspacing="1" width="99%" class="footable">';                
			$tmp = 1;
			unset($array_dato);
			unset($array_lote);
			unset($array_refe);
			if(count($array)>0){
			foreach ($array as $val){
				$cliente 	= $val[0];
				$fecha_emis = $val[1];
				$fecha_venc = $val[2];
				$orde_comp  = $val[3];
				$ref_clpv   = $val[4];
				$id_progra  = $val[5];
				$id_dprogr  = $val[6];
				$cod_refe   = $val[7];
				$cantidad   = $val[8];  
				$depar_clpv = $val[9];
				$preimp     = $val[10];
				$color      = $val[11];
				$pedf_cod   = $val[12];
				$id_color   = $val[13];
				$array_pr[$x] = $id_progra;

			if($x>1){
			}else{                
				$tabla_det .= '<tr>
									<td class="fecha_letra" align="center">ORDEN PRODUCCION: '.$preimp.' - DESPACHO DE '.$proceso.'</td>
								</tr>';
				$tabla_det .= '<tr height="20">';
				$tabla_det .= '<td width="100%" width="75%" valign="top" >
								<table width="100%" class="footable">
										<tr>
											<td  align="left">ORDEN:</td>
											<td  align="left">'.$orde_comp.'</td>
											<td  align="left">FECHA EMISION:</td>
											<td  align="left">'.$fecha_emis.'</td>
											<td  align="left">CLIENTE:</td>
											<td  align="left" colspan="2">'.$cliente.'</td>
										</tr>
										<tr>
											<td align="left">FECHA ENTREGA CD:</td>											
											<td align="left">'.$fecha_venc.'</td>
											<td align="left">DEPTO:</td>
											<td align="left">'.$depar_clpv.'</td>
											<td align="left">OBSERVACION:</td>
											<td colspan="2" align="left">'.$msn.'</td>												                                                        
										</tr>
										<tr>
											
											<td align="left" colspan="2">REF. PRENDA CLIENTE:</td>
											<td align="left">'.$ref_clpv.'</td>
											<td align="left" colspan="2">REF. PRENDA INTERNA:</td>
											<td align="left">'.$cod_refe.'</td>	
										</tr>
								</table>
							</td> ';
				$tabla_det .= '</tr>';

				// DETALLES
				$array_refe [] = array($id_progra);

			}

			// LISTA DE INSUMOS E MP
			unset($array_tmp);
			$array_tmp = $array_det[$pedf_cod];
			if(count($array_tmp)>0){
			$tabla_det .= '<tr height="20" align="left">
							<td class="ficha_min" scope="row" colspan="11" width="30%">
								<table class="footable">   
									<tr align="left">  
										<td class="fecha_letra" scope="row" colspan="5">COLOR: '.$color.' '.$cantidad.' UND.</td>  
									</tr>
									<tr align="left">
										<td class="fecha_letra" scope="row">N.-</td>  
										<td class="fecha_letra" scope="row">CODIGO</td> 
										<td class="fecha_letra" scope="row">COLOR</td>   
										<td class="fecha_letra" scope="row">TALLA</td>   
										<td class="fecha_letra" scope="row">CANT</td>   
									</tr>';
			$pp=1;
			foreach ($array_tmp as $row){
				$det_prod = $row[0];
				$det_nom  = $row[1];
				$det_progr= $row[2];
				$det_dprg = $row[3];
				$det_refe = $row[4]; 
				$det_color= $array_color[$row[5]]; 
				$det_talla= $array_talla[$row[6]]; 
				$det_cant = $row[7]; 
				$det_iddet= $row[8]; 
				$det_cab  = $row[9]; 
				
				$tabla_det .='<tr align="left" valign="top">
									<td  scope="row" align="right">'.$pp.'</td>
									<td  scope="row">'.$det_prod.'</td>   
									<td  scope="row">'.$det_color.'</td>   
									<td  scope="row">'.$det_talla.'</td>   
									<td  scope="row" align="right">'.$det_cant.'</td>';
				$tabla_det .= '</tr>';   
				$pp++;
			}// fin for
			$tabla_det .= '</table></td></tr>';
			}// fin if

			
			$tabla_det .= '<tr height="20">';
			$tabla_det .= '<td width="100%" class="fecha_balance" valign="top">
								<table width="100%" class="footable">                                        
									<tr align="left">  
										<td class="fecha_letra" scope="row" colspan="4">MATERIA PRIMA - INSUMOS</td>  
									</tr>';
			$tabla_det .= '<tr align="center">
							<td class="fecha_letra" scope="row"></td>    
							<td class="fecha_letra" scope="row">BODEGA</td>   
							<td class="fecha_letra" scope="row">CODIGO</td>   
							<td class="fecha_letra" scope="row">PRODUCTO</td>   
							<td class="fecha_letra" scope="row">UNIDAD</td> 
							<td class="fecha_letra" scope="row">CANTIDAD REQUERIDA</td>    
							<td class="fecha_letra" scope="row">DESPACHO</td>   
						</tr>';

			// MP - INSUMOS
			$sql = "select m.prod_cod_prod, m.prod_nom_prod, m.cant_rece, m.cant_desp,
								m.bode_cod_bode, m.unid_cod_unid, m.id_tipo_tela 
								from moda.mov_prod m where
								m.empr_cod_empr   = $idempresa and
								m.pedf_num_preimp = '$num_op' and
								m.id_proceso      = $idproceso ";
			$i=1;
			if($oCon->Query($sql)){
			if($oCon->NumFilas()>0){
				do{
					$prod_cod = $oCon->f('prod_cod_prod');
					$prod_nom = $oCon->f('prod_nom_prod');
					$consumo  = round($oCon->f('cant_rece'),6);
					$tipo_tela= $oCon->f('id_tipo_tela');
					$bode_cod = $oCon->f('bode_cod_bode');
					$bode_nom = $array_bode[$oCon->f('bode_cod_bode')];
					$unid_cod = $oCon->f('unid_cod_unid');
					$unid_nom = $array_unid[$unid_cod];
					$cant_desp= round($oCon->f('cant_desp'),6);
					
					$tabla_det .='<tr height="20" align="left" valign="top">
									<td  scope="row" align="right">'.$i.'</td>
									<td  scope="row">'.$bode_nom.'</td>   
									<td  scope="row">'.$prod_cod.'</td>   
									<td  scope="row">'.$prod_nom.'</td>
									<td  scope="row" align="right">'.$unid_nom.'</td>
									<td  scope="row" align="right">'.$consumo.'</td>
									<td  scope="row" align="right">'.$cant_desp.'</td>';
					$tabla_det .= '</tr>';

					$tmp++;
					$i++;
				}while($oCon->SiguienteRegistro());
			}// fin if
			}// fin if           

			$tabla_det .= '</table>
						</td>';
			$tabla_det .= '</tr>';            
			$x++;          
			$rowspan ++;
			}// fin foreach

			}else{
			$tabla_det = 'Sin Datos...';
			}
		
		
	} catch (Exception $e) {
        $tabla_det .=$e->getMessage();
    }
     	
	return $tabla_det;
	
}


function formato_bodega_prod_status( $idempresa="", $idproceso="", $num_op='' ){
	global $DSN_Ifx, $DSN;

    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

	$oCon = new Dbo;
    $oCon->DSN = $DSN;
	$oCon->Conectar(); 
    	
    try{
			$sql = "select c.PROCESO from moda.procesos c where c.ID_PROCESO = $idproceso ";
			$proceso = consulta_string_func($sql, 'proceso', $oCon, 1);

			// MSN
			$sql = "select msn from moda.mov_prod m where
						m.empr_cod_empr   = $idempresa and
						m.pedf_num_preimp = '$num_op' and
						m.id_proceso      = $idproceso ";
			$msn = consulta_string_func($sql, 'msn', $oCon, '');

			// COLOR
			$sql = "select c.id_color, c.color from moda.color c where c.empr_cod_empr = $idempresa ";
			unset($array_color);
			$array_color  = array_dato($oCon, $sql, 'id_color', 'color');

			// TALLA
			$sql = "select t.id_talla, t.talla, t.sigla from moda.talla t where	t.empr_cod_empr = $idempresa ";
			unset($array_talla);
			$array_talla  = array_dato($oCon, $sql, 'id_talla', 'sigla');              

			// BODEGA
			$sql = "select   bode_cod_bode, bode_nom_bode  from saebode where	bode_cod_empr = $idempresa ";
			unset($array_bode);
			$array_bode = array_dato($oIfx, $sql, 'bode_cod_bode', 'bode_nom_bode');

			// BODE LOTE
			$sql = "select b.id_bode_lote, b.bode_cod_bode from moda.bode_lote b where b.empr_cod_empr = $idempresa ";
			unset($array_blote);
			if($oCon->Query($sql)){
				if($oCon->NumFilas()>0){
					do{
						$array_blote [$oCon->f('bode_cod_bode')] = $oCon->f('bode_cod_bode');
					}while($oCon->SiguienteRegistro());
				}
			}

			//UNIDAD
			$sql = "select  unid_cod_unid, unid_sigl_unid from saeunid where unid_cod_empr = $idempresa ";
			unset($array_unid);
			$array_unid = array_dato($oIfx, $sql, 'unid_cod_unid', 'unid_sigl_unid');

			// CANTIDADS POR PEDIDO
			$sql = "select  d.dpef_id_progra, d.dpef_progr_det,  
						d.dpef_cod_refe,  d.dpef_id_color, (d.dpef_cant_dfac) as cant, d.dpef_id_talla, d.dpef_nom_prod, 
						d.dpef_cod_prod, d.dpef_cod_dpef, pe.pedf_cod_pedf
						from saeprod p, saedpef  d , saepedf pe where
						pe.pedf_cod_pedf  = d.dpef_cod_pedf and
						pe.pedf_cod_empr  = $idempresa  and
						p.prod_cod_prod   = d.dpef_cod_prod and
						p.prod_cod_sucu   = d.dpef_cod_sucu and
						p.prod_cod_empr   = $idempresa and
						d.dpef_cod_empr   = $idempresa and
						pe.pedf_est_op   = 'S' and
						d.dpef_pack_sn   = 'N' and
						d.dpef_conj_sn   = 'N' and
						pe.pedf_num_preimp = '$num_op' and
							p.prod_id_color is not null
						order by d.dpef_id_progra, d.dpef_progr_det, d.dpef_id_talla ";        
			unset($array_det);
			if($oIfx->Query($sql)){
				if($oIfx->NumFilas()>0){
					do{
						$array_det [$oIfx->f('pedf_cod_pedf')][] = array( $oIfx->f('dpef_cod_prod'), $oIfx->f('dpef_nom_prod') ,$oIfx->f('dpef_id_progra'),
																	$oIfx->f('dpef_progr_det'), $oIfx->f('dpef_cod_refe'), $oIfx->f('dpef_id_color'),
																	$oIfx->f('dpef_id_talla'), $oIfx->f('cant'), $oIfx->f('dpef_cod_dpef'),
																	$oIfx->f('pedf_cod_pedf') );
					}while($oIfx->SiguienteRegistro());
				}
			}

			$_SESSION['U_ARRAY_CANT_BBORD'] = $array_det;

			$sql = "select pe.pedf_nom_cliente,  pe.pedf_cod_pedf, pe.pedf_fech_fact, pe.pedf_fech_venc, pe.pedf_opc_pedf  ,
							d.dpef_ref_clpv,  d.dpef_id_progra, d.dpef_progr_det,  
							d.dpef_cod_refe, p.prod_id_color, pe.pedf_depar_clpv , pe.pedf_num_preimp, sum(d.dpef_cant_dfac) as cant
							from saeprod p, saedpef  d , saepedf pe where
							pe.pedf_cod_pedf = d.dpef_cod_pedf and
							pe.pedf_cod_empr = $idempresa and
							p.prod_cod_prod = d.dpef_cod_prod and
							p.prod_cod_sucu = d.dpef_cod_sucu and
							p.prod_cod_empr = $idempresa and
							d.dpef_cod_empr = $idempresa and
							pe.pedf_est_op   = 'S' and
							d.dpef_pack_sn   = 'N' and
							d.dpef_conj_sn   = 'N' and
							pe.pedf_num_preimp = '$num_op' and
							p.prod_id_color is not null
							group by pe.pedf_nom_cliente,  pe.pedf_cod_pedf, 
							pe.pedf_fech_fact, pe.pedf_fech_venc, pe.pedf_opc_pedf  , d.dpef_ref_clpv,  
							d.dpef_id_progra, d.dpef_progr_det, d.dpef_cod_refe, p.prod_id_color , pe.pedf_depar_clpv  , pe.pedf_num_preimp
							order by d.dpef_id_progra, d.dpef_progr_det ";     
			unset($array);
			//        $oReturn->alert($sql);
			if ($oIfx->Query($sql)){
				if($oIfx->NumFilas()>0){
					do {
						$cliente    = $oIfx->f('pedf_nom_cliente');
						$fecha_emis = fecha_mysql_func($oIfx->f('pedf_fech_fact'));
						$fecha_venc = fecha_mysql_func($oIfx->f('pedf_fech_venc'));
						$orde_comp  = $oIfx->f('pedf_opc_pedf');
						$ref_clpv   = $oIfx->f('dpef_ref_clpv');
						$id_progra  = $oIfx->f('dpef_id_progra');
						$id_dprogr  = $oIfx->f('dpef_progr_det');
						$cod_refe   = $oIfx->f('dpef_cod_refe');
						$cantidad   = $oIfx->f('cant');
						$depar_clpv = $oIfx->f('pedf_depar_clpv');
						$preimp     = $oIfx->f('pedf_num_preimp');
						$color      = $array_color[$oIfx->f('prod_id_color')];
						$pedf_cod   = $oIfx->f('pedf_cod_pedf');
						$id_color   = $oIfx->f('prod_id_color');

						$array []   = array($cliente,   $fecha_emis, $fecha_venc, $orde_comp, $ref_clpv, $id_progra,
											$id_dprogr, $cod_refe,   $cantidad ,  $depar_clpv , $preimp, $color, $pedf_cod, $id_color );
					}while($oIfx->SiguienteRegistro());
				}
			}
			$oIfx->Free();  

			unset($array_pr);
			$x=1;
			$tabla_det = '';
			$tabla_det .='<fieldset style="border:#999999 1px solid; padding:2px; text-align:center; width:98%; background-color:#FFFFFF ">';
			$tabla_det .='<table align="center" border="0" cellpadding="2" cellspacing="1" width="99%" class="footable">';                
			$tmp = 1;
			unset($array_dato);
			unset($array_lote);
			unset($array_refe);
			if(count($array)>0){
			foreach ($array as $val){
				$cliente 	= $val[0];
				$fecha_emis = $val[1];
				$fecha_venc = $val[2];
				$orde_comp  = $val[3];
				$ref_clpv   = $val[4];
				$id_progra  = $val[5];
				$id_dprogr  = $val[6];
				$cod_refe   = $val[7];
				$cantidad   = $val[8];  
				$depar_clpv = $val[9];
				$preimp     = $val[10];
				$color      = $val[11];
				$pedf_cod   = $val[12];
				$id_color   = $val[13];
				$array_pr[$x] = $id_progra;

			if($x>1){
			}else{                
				$tabla_det .= '<tr>
									<td class="fecha_letra" align="center">ORDEN PRODUCCION: '.$preimp.' - DESPACHO DE '.$proceso.'</td>
								</tr>';
				$tabla_det .= '<tr height="20">';
				$tabla_det .= '<td width="100%" width="75%" valign="top" >
								<table width="100%" class="footable">
										<tr>
											<td  align="left">ORDEN:</td>
											<td  align="left">'.$orde_comp.'</td>
											<td  align="left">FECHA EMISION:</td>
											<td  align="left">'.$fecha_emis.'</td>
											<td  align="left">CLIENTE:</td>
											<td  align="left" colspan="2">'.$cliente.'</td>
										</tr>
										<tr>
											<td align="left">FECHA ENTREGA CD:</td>											
											<td align="left">'.$fecha_venc.'</td>
											<td align="left">DEPTO:</td>
											<td align="left">'.$depar_clpv.'</td>
											<td align="left">OBSERVACION:</td>
											<td colspan="2" align="left">'.$msn.'</td>												                                                        
										</tr>
										<tr>
											
											<td align="left" colspan="2">REF. PRENDA CLIENTE:</td>
											<td align="left">'.$ref_clpv.'</td>
											<td align="left" colspan="2">REF. PRENDA INTERNA:</td>
											<td align="left">'.$cod_refe.'</td>	
										</tr>
								</table>
							</td> ';
				$tabla_det .= '</tr>';

				// DETALLES
				$array_refe [] = array($id_progra);

			}

			// LISTA DE INSUMOS E MP
			unset($array_tmp);
			$array_tmp = $array_det[$pedf_cod];
			if(count($array_tmp)>0){
			$tabla_det .= '<tr height="20" align="left">
							<td class="ficha_min" scope="row" colspan="11" width="30%">
								<table class="footable">   
									<tr align="left">  
										<td class="fecha_letra" scope="row" colspan="5">COLOR: '.$color.' '.$cantidad.' UND.</td>  
									</tr>
									<tr align="left">
										<td class="fecha_letra" scope="row">N.-</td>  
										<td class="fecha_letra" scope="row">CODIGO</td> 
										<td class="fecha_letra" scope="row">COLOR</td>   
										<td class="fecha_letra" scope="row">TALLA</td>   
										<td class="fecha_letra" scope="row">CANT</td>   
									</tr>';
			$pp=1;
			foreach ($array_tmp as $row){
				$det_prod = $row[0];
				$det_nom  = $row[1];
				$det_progr= $row[2];
				$det_dprg = $row[3];
				$det_refe = $row[4]; 
				$det_color= $array_color[$row[5]]; 
				$det_talla= $array_talla[$row[6]]; 
				$det_cant = $row[7]; 
				$det_iddet= $row[8]; 
				$det_cab  = $row[9]; 
				
				$tabla_det .='<tr align="left" valign="top">
									<td  scope="row" align="right">'.$pp.'</td>
									<td  scope="row">'.$det_prod.'</td>   
									<td  scope="row">'.$det_color.'</td>   
									<td  scope="row">'.$det_talla.'</td>   
									<td  scope="row" align="right">'.$det_cant.'</td>';
				$tabla_det .= '</tr>';   
				$pp++;
			}// fin for
			$tabla_det .= '</table></td></tr>';
			}// fin if

			
			$tabla_det .= '<tr height="20">';
			$tabla_det .= '<td width="100%" class="fecha_balance" valign="top">
								<table width="100%" class="footable">                                        
									<tr align="left">  
										<td class="fecha_letra" scope="row" colspan="4">MATERIA PRIMA - INSUMOS</td>  
									</tr>';
			$tabla_det .= '<tr align="center">
							<td class="fecha_letra" scope="row"></td>    
							<td class="fecha_letra" scope="row">BODEGA</td>   
							<td class="fecha_letra" scope="row">CODIGO</td>   
							<td class="fecha_letra" scope="row">PRODUCTO</td>   
							<td class="fecha_letra" scope="row">UNIDAD</td> 
							<td class="fecha_letra" scope="row">CANTIDAD REQUERIDA</td>    
							<td class="fecha_letra" scope="row">DESPACHO</td>   
						</tr>';

			// MP - INSUMOS
			$sql = "select m.prod_cod_prod, m.prod_nom_prod, m.cant_rece, m.cant_desp,
								m.bode_cod_bode, m.unid_cod_unid, m.id_tipo_tela 
								from moda.mov_prod m where
								m.empr_cod_empr   = $idempresa and
								m.pedf_num_preimp = '$num_op' and
								m.id_proceso      = $idproceso ";
			$i=1;
			if($oCon->Query($sql)){
				if($oCon->NumFilas()>0){
					do{
							$prod_cod = $oCon->f('prod_cod_prod');
							$prod_nom = $oCon->f('prod_nom_prod');
							$consumo  = round($oCon->f('cant_rece'),6);
							$tipo_tela= $oCon->f('id_tipo_tela');
							$bode_cod = $oCon->f('bode_cod_bode');
							$bode_nom = $array_bode[$oCon->f('bode_cod_bode')];
							$unid_cod = $oCon->f('unid_cod_unid');
							$unid_nom = $array_unid[$unid_cod];
							$cant_desp= round($oCon->f('cant_desp'),6);
							
							$tabla_det .='<tr height="20" align="left" valign="top">
											<td  scope="row" align="right">'.$i.'</td>
											<td  scope="row">'.$bode_nom.'</td>   
											<td  scope="row">'.$prod_cod.'</td>   
											<td  scope="row">'.$prod_nom.'</td>
											<td  scope="row" align="right">'.$unid_nom.'</td>
											<td  scope="row" align="right">'.$consumo.'</td>
											<td  scope="row" align="right">'.$cant_desp.'</td>';
							$tabla_det .= '</tr>';

							$tmp++;
							$i++;
					}while($oCon->SiguienteRegistro());
				}// fin if
			}// fin if           

			$tabla_det .= '</table>
						</td>';
			$tabla_det .= '</tr>';            
			$x++;          
			$rowspan ++;
			}// fin foreach

			}else{
				$tabla_det = 'Sin Datos...';
			}
		
		
	} catch (Exception $e) {
        $tabla_det .=$e->getMessage();
    }
     	
	return $tabla_det;
	
}


?>