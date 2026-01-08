<?
set_time_limit(3000);
ini_set('memory_limit', '20000M');
require_once 'dompdf/dompdf_config.inc.php';

function genera_pdf_esta_cta_int2( $idempresa='', $clpv_cod='', $idcontrato='', $idpago='' ){
	global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $idsucursal = $_SESSION['U_SUCURSAL'];
	
    
    
    //clases contratos
    $Contratos = new Contratos($oCon, $oIfx, $idempresa, $idsucursal, $clpv_cod, $idcontrato);
	
	$arrayContrato = $Contratos->consultarContrato( );
    $telefono      = $Contratos->consultaTelefonos();
    $correo        = $Contratos->consultaEmail();
    $valorDeuda    = $Contratos->consultaMontoMesAdeuda();
    $valorCuotas   = $Contratos->consultaMesesAdeuda();
	
	$sql = "select prod_cod_prod, prod_nom_prod 
				from saeprod
				where prod_cod_empr = $idempresa and
				prod_cod_sucu       = $idsucursal and
				prod_cod_cabl is not null";
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			unset($arrayProd);
			do {
				$arrayProd[$oIfx->f('prod_cod_prod')] = $oIfx->f('prod_nom_prod');
			} while ($oIfx->SiguienteRegistro());
		}
	}
	$oIfx->Free();
	
	//var_dump($arrayRetencion);exit;
	foreach($arrayContrato as $val){
                $id                 = $val[0];                     $id_empresa         = $val[1];                   $id_sucursal        = $val[2]; 
                $id_clpv            = $val[3];                     $id_ciudad          = $val[4];                   $codigo             = $val[5];  
                $nom_clpv           = $val[6];                     $ruc_clpv           = $val[7];                   $fecha_contrato     = $val[8]; 
                $fecha_firma        = $val[9];                     $fecha_corte        = $val[10];                  $fecha_cobro        = $val[11]; 
                $duracion           = $val[12];                    $penalidad          = $val[13];                  $estado             = $val[14]; 
                $vendedor           = $val[15];                    $user_web           = $val[16];                  $fecha_server       = $val[17]; 
                $tarifa             = $val[18];                    $id_dire            = $val[19];                  $saldo_mora         = $val[20]; 
                $fecha_instalacion  = $val[21];                    $detalle            = $val[22];                  $sobrenombre        = $val[23]; 
                $limite             = $val[24];                    $cobrador           = $val[25];                  $tipo_contrato      = $val[26]; 
                $cheque_sn          = $val[27];                    $cobro_directo      = $val[28];                  $id_sector          = $val[29]; 
                $id_barrio          = $val[30];                    $direccion          = $val[31];                  $referencia         = $val[32]; 
                $telef              = $val[33];                    $email              = $val[34];                  $latitud            = $val[35]; 
                $longitud           = $val[36];                    $abonado            = $val[37];                  $estado_tmp         = $val[38]; 
                $nombre             = $val[39];                    $apellido           = $val[40];                  $foto               = $val[41]; 
                $observaciones      = $val[42];                    $tipo_duracion      = $val[43];                  $fecha_c_vence      = $val[44]; 
                $estadoNombre       = $val[45];                    $estadoClass        = $val[46];                  $sector             = $val[47];  
                $barrio             = $val[48];                    $tipoContrato       = $val[49];                  $vend_nom_vend      = $val[50]; 
                $nom_cobrador       = $val[51];                    $estadoColor        = $val[52];                  $tipo_ncf           = $val[53]; 
                $tarifa_e           = $val[54];                    $descuento_p        = $val[55];                  $descuento_v        = $val[56]; 
	}// fin
	
	$sql = "select empr_ruc_empr, empr_dir_empr, empr_nom_empr, empr_path_logo  from saeempr where empr_cod_empr = $idempresa ";
	if($oIfx->Query($sql)){
		$empr_ruc       = $oIfx->f('empr_ruc_empr');
		$empr_dir       = $oIfx->f('empr_dir_empr');
		$empr_nom       = $oIfx->f('empr_nom_empr');
		$empr_path_logo = $oIfx->f('empr_path_logo');
		$empr_nom.=' ';
    }
    
	$sql="SELECT sucu_nom_sucu, sucu_telf_secu, sucu_email_secu FROM saesucu WHERE sucu_cod_sucu='$idsucursal'";
	if($oIfx->Query($sql)){
		$sucu_nom   = $oIfx->f('sucu_nom_sucu');
		$sucu_telf  = $oIfx->f('sucu_telf_secu');
		$sucu_email = $oIfx->f('sucu_email_secu');		
	}
	
	$oIfx->Free();
    setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
    

    // EQUIPOS
    $sql = "select c.id, c.id_dispositivo, m.id_sistema, c.id_equipo, c.id_tarjeta, c.id_caja, c.estado, 
                    c.nombre, c.fecha_corte, c.fecha_reconexion, c.fecha, c.id_ubicacion, c.ubicacion, c.id_tipo_prod,
                    d.modelo, m.marca, d.serial  
                    from int_contrato_caja c, int_marcas_dispositivos m, int_dispositivos d    
                    where c.id_dispositivo = d.id and    
                    d.id_marca      = m.id  and    
                    c.id_empresa    = $idempresa and    
                    c.id_clpv       = $clpv_cod  and        
                    c.id_contrato   = $idcontrato ";
    $div_eq  = '';
    $div_eq .= '<table border="0">'; //table cabecera
    $div_eq .= '<tr>';
    $div_eq .= '<th class="service" align="center" style="text-align:center;">
					<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
						Tipo
					</span>
				</th>';
    $div_eq .= '<th class="service" style="text-align:center;">
					<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
						Ubicacion
					</span>					
				</th>';
    $div_eq .= '<th class="desc" style="text-align:center;">
					<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
						Tarjeta Modem
					</span>
				</th>';
    $div_eq .= '</tr>';

    $i=1;
    /*if($oCon->Query($sql)){
        if ($oCon->NumFilas() > 0) {
            do{
                $marca        = $oCon->f('marca');
                $modelo       = $oCon->f('modelo');
                $id_ubicacion = $oCon->f('id_ubicacion');
                $serial       = $oCon->f('serial');
                $ubicacion    = $oCon->f('ubicacion');
                $id_tipo_prod = $oCon->f('id_tipo_prod');
                $id_tarjeta   = $oCon->f('id_tarjeta');                

                $clase = '';
				if(($i%2)==0){
					$clase = 'F5F5F5';
				}else{
					$clase = 'FFFFFF';
				}

                $sql = "SELECT t.nombre FROM int_tipo_prod t WHERE t.id = '$id_tipo_prod'  ";
                $tipo_prod = consulta_string_func($sql, 'nombre', $oConA, '-');

                $div_eq .= '<tr>';
                $div_eq .= '<td style="text-align:left; height: 25px">
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									'.$tipo_prod.'
								</span>
							</td>';
                $div_eq .= '<td style="text-align:left;">
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									'.$ubicacion.'
								</span>
							</td>';
                $div_eq .= '<td style="text-align:right;">
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . $id_tarjeta. '
								</span>
							</td>';
                $div_eq .= '</tr>';

                $i++;
            } while ($oCon->SiguienteRegistro());
        }        
    }
	*/
    $div_eq .= '</table>';

    // PLANES	
	$Equipos = new Equipos($oCon, $oIfx, $idempresa, $idsucursal, $clpv_cod, $idcontrato, null);
	$array_planes  = $Equipos->consultarPlanesCuotaEquipo($idpago);
	sort($array_planes, SORT_STRING);
	if (count($array_planes) > 0) {
		$sHtml_0  = '<table>';
		$sHtml_0 .= '<tr>';
		$sHtml_0 .= '<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Equipo
							</span>							
					 </th>';
		$sHtml_0 .= '<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Plan
							</span>							
					 </th>';
		$sHtml_0 .= '<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Dias Uso
							</span>							
					 </th>';
		$sHtml_0 .= '<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Dias No Uso
							</span>							
					 </th>';
		$sHtml_0 .= '<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Tarifa
							</span>							
					 </th>';
		$sHtml_0 .= '<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								A Pagar
							</span>							
					 </th>';
		$sHtml_0 .= '</tr>';
		
		$tot_t = 0;		$tot_u = 0;		$tot_n = 0;
		$tot_p = 0;		$tot_s = 0;
		$fact_tot = 0;	$fact_iva = 0;	$fact_ice = 0; 	$fact_irbp = 0;
		foreach ($array_planes as $val) {
			$equipo 	= $val[0];
			$plan 		= $val[1];
			$dias_uso   = $val[2];
			$dias_nuso 	= $val[3];
			$tarifa  	= $val[4];
			$valor_diario= $val[5];
			$apagar		= $val[6];
			$estado		= $val[7];
			$estado_eq	= $val[8];
			$prod_cod	= $val[9];
			
			if( $estado_eq == 'A' || $estado_eq == 'C' ){
				$sql = "select prbo_iva_sino, prbo_iva_porc,
								prbo_ice_sino, prbo_ice_porc,
								prbo_irbp_sino, prbo_val_irbp
								from saeprbo where
								prbo_cod_prod = '$prod_cod' and
								prbo_cod_empr = $idempresa and
								prbo_cod_sucu = $idsucursal ";
				$prbo_iva_sino = ''; $prbo_iva_porc = 0;	$prbo_ice_sino = ''; $prbo_ice_porc = 0; $prbo_irbp_sino=''; $prbo_val_irbp = 0;
				$impuesto_val  = 0;
				if($oIfx->Query($sql)){
					$prbo_iva_sino   = $oIfx->f('prbo_iva_sino');					$prbo_iva_porc   = $oIfx->f('prbo_iva_porc');
					$prbo_ice_sino   = $oIfx->f('prbo_ice_sino');					$prbo_ice_porc   = $oIfx->f('prbo_ice_porc');
					$prbo_irbp_sino  = $oIfx->f('prbo_irbp_sino');					$prbo_val_irbp   = $oIfx->f('prbo_val_irbp');
				}

				if( $prbo_iva_sino=='S' ){		$impuesto_val += $prbo_iva_porc;			}
				if( $prbo_ice_sino=='S' ){		$impuesto_val += $prbo_ice_porc;			}
				if( $prbo_irbp_sino=='S'){		$impuesto_val += $prbo_val_irbp;			}

				$subt1 		= 0;
				$imp_val	= '1.'.$impuesto_val;
				$subt1 		= $apagar/$imp_val;
				$fact_tot  += $subt1;

				if($prbo_iva_porc>0){				$fact_iva  += round( (( $subt1*$prbo_iva_porc) / 100),2);				}
				if($prbo_ice_porc>0){				$fact_ice  += round( (( $subt1*$prbo_ice_porc) / 100),2);				}
				if($prbo_val_irbp>0){				$fact_irbp += round( (( $subt1*$prbo_val_irbp) / 100),2);				}

				$sHtml_0 .= '<tr>';
				$sHtml_0 .= '<td style="text-align:left; height: 25px">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' .$equipo. '
								</span>								
							 </td>';
				$sHtml_0 .= '<td style="text-align:left; height: 25px">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' .$plan. '
								</span>								
							 </td>';
				$sHtml_0 .= '<td style="text-align:right; height: 25px">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">								
									'. number_format($dias_uso, 2, '.', '').'
								</span>								
							 </td>';
				$sHtml_0 .= '<td style="text-align:right; height: 25px">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">								
									'. number_format($dias_nuso, 2, '.', '').'
								</span>								
							 </td>';
				$sHtml_0 .= '<td style="text-align:right; height: 25px">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">								
									'. number_format($tarifa, 2, '.', ',').'
								</span>								
							 </td>';
				$sHtml_0 .= '<td style="text-align:right; height: 25px">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">								
									'. number_format($apagar, 2, '.', ',').'
								</span>								
							 </td>';
				$sHtml_0 .= '</tr>';

				$tot_t += $tarifa;
				$tot_s += $apagar;
			}
		}
		$sHtml_0 .= '<tr>';
		$sHtml_0 .= '<td></td>';
		$sHtml_0 .= '<td></td>';
		$sHtml_0 .= '<td></td>';
		$sHtml_0 .= '<td>
						<span style="right: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">								
							<strong>Total:</strong>
						</span>	
					 </td>';
		$sHtml_0 .= '<td style="text-align:right; height: 35px">
							<span style="right: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">								
								<strong>'. number_format($tot_t, 2, '.', ',').'</strong>
							</span>								
					 </td>';
		$sHtml_0 .= '<td style="text-align:right; height: 35px">
							<span style="right: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">								
								<strong>'. number_format($tot_s, 2, '.', ',').'</strong>
							</span>								
					 </td>';
		$sHtml_0 .= '</tr>';
		$sHtml_0 .= '</table>';
	}

	// BALANCE ANTERIOR
	$sql = "SELECT c.secuencial FROM contrato_pago c WHERE 
				c.id_contrato = $idcontrato AND 
				c.id_clpv     = $clpv_cod AND
				c.id          = $idpago ";
	$secu_bal = consulta_string_func($sql, 'secuencial', $oCon, '0');
	
	$sql = "SELECT sum(c.tarifa  +  c.tot_add) AS tot, id  FROM contrato_pago c WHERE
				c.id_contrato = $idcontrato AND 
				c.id_clpv     = $clpv_cod AND
				c.secuencial  = ($secu_bal-1) ";
	$bal_anterior = consulta_string_func($sql, 'tot', $oCon, '0');
	$id_pago_ant  = consulta_string_func($sql, 'id', $oCon, '0');
		
	$sql = "SELECT sum(c.valor) as valor, date(c.fecha_server) AS fecha
				FROM contrato_factura c WHERE
				c.id_empresa  = $idempresa AND
				c.id_contrato = $idcontrato AND
				c.id_clpv     = $clpv_cod AND
				c.estado      = 'A' and
				c.id_pago     = $id_pago_ant ";
	$pago_ant = consulta_string_func($sql, 'valor', $oCon, '0');
	$fec_pant = consulta_string_func($sql, 'fecha', $oCon, '');
	if( !empty($fec_pant) ){
		$fec_pant = fecha_mysql_dmy($fec_pant).', Gracias';
	}
	
	// DESCUENTO TARIFA
	$sql = "SELECT c.descuento_v FROM contrato_descuentos c WHERE
				c.id_empresa 	= $idempresa AND
				c.id_contrato 	= $idcontrato AND
				c.estado 		= 'A' and
				c.id_clpv       = $clpv_cod ";
	$desc_tar = consulta_string_func($sql, 'descuento_v', $oCon, '0');
	
    // CUOTA
    $sHtml_1  = '';
    $sHtml_1 .= '<table border="0">'; //table cabecera
    $sql = "select * from contrato_pago
                where id_clpv = $clpv_cod and
                id_contrato   = $idcontrato and
                id            = $idpago ";
    //$oReturn->alert($sql);
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            $sHtml_1 .= '<tr>'; 
            $sHtml_1 .= '<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Tarifa 
							</span>
					     </th>';
			$sHtml_1 .= '<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Adicional
							</span>
					     </th>';
			$sHtml_1 .= '<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Descuento
							</span>
					     </th>';
            $sHtml_1 .= '<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Valor Uso
							</span>
						 </th>';
            $sHtml_1 .= '<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Valor No Uso
							</span>
						 </th>';
            $sHtml_1 .= '<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Pagos
							</span>
					     </th>';
            $sHtml_1 .= '<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Saldo
							</span>
						 </th>';
            $sHtml_1 .= '</tr>';
            do {
                $idDet          = $oCon->f('id');
				$fecha 			= $oCon->f('fecha');
                $mes 			= $oCon->f('mes');
                $anio 			= $oCon->f('anio');
                $secuencial 	= $oCon->f('secuencial');
                $estado 		= $oCon->f('estado');
                $estado_fact 	= $oCon->f('estado_fact');
                $tarifa 		= $oCon->f('tarifa');
                $valor_pago 	= $oCon->f('valor_pago');
                $valor_uso 		= $oCon->f('valor_uso');
                $valor_no_uso  	= $oCon->f('valor_no_uso');
				$tot_add    	= $oCon->f('tot_add');

                //pago
                $sHtmlFactura = '';
                $sql = "select fact_cod_fact, fact_num_preimp, fact_fech_fact, fact_clav_sri
                        from saefact f, saedfac d
                        where fact_cod_empr = dfac_cod_empr and
                        fact_cod_sucu = dfac_cod_sucu and
                        fact_cod_clpv = dfac_cod_clpv and
                        fact_cod_fact = dfac_cod_fact and
                        fact_cod_empr = $idempresa and
                        fact_cod_clpv = $clpv_cod and
                        dfac_cod_mes = $idDet and
                        fact_cod_contr = $idcontrato and
                        fact_est_fact != 'AN'";
                /*if ($oIfx->Query($sql)) {
                    if ($oIfx->NumFilas() > 0) {
                        $sHtmlFactura = '';
                        do {
                            $fact_cod_fact      = $oIfx->f('fact_cod_fact');
                            $fact_num_preimp    = $oIfx->f('fact_num_preimp');
                            $fact_fech_fact     = $oIfx->f('fact_fech_fact');
                            $fact_clav_sri      = $oIfx->f('fact_clav_sri');
                            $sHtmlFactura      .= $fact_num_preimp . ', ';
                        } while ($oIfx->SiguienteRegistro());
                    }
                }
                $oIfx->Free();
				*/
				
                $clase = '';
				if(($i%2)==0){
					$clase = 'F5F5F5';
				}else{
					$clase = 'FFFFFF';
                }
						
				if ($valor_uso == 0) {
					$valor_uso = $tarifa;
				}
				
                $sHtml_1 .= '<tr>';
                $sHtml_1 .= '<td style="text-align:center; height:25px">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . number_format($tarifa, 2, '.', ',') . '
								</span>
							 </td>';
				$sHtml_1 .= '<td style="text-align:center;">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . number_format($tot_add, 2, '.', ',') . '
								</span>
							 </td>';
				$sHtml_1 .= '<td style="text-align:center;">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . number_format($desc_tar, 2, '.', ',') . '
								</span>
							 </td>';
                $sHtml_1 .= '<td style="text-align:center;">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . number_format($valor_uso, 2, '.', ',') . '
								</span>
							 </td>';
                $sHtml_1 .= '<td style="text-align:center;">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . number_format($valor_no_uso, 2, '.', ',') . '
								</span>
							 </td>';
                $sHtml_1 .= '<td style="text-align:center;">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . number_format($valor_pago, 2, '.', '') . '
								</span>
							 </td>';
                $sHtml_1 .= '<td style="text-align:center;">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . number_format($tarifa + $tot_add - $valor_pago - $valor_no_uso, 2, '.', ',') . '
								</span>
							 </td>';
                $sHtml_1 .= '</tr>';
                
                $i++;
            } while ($oCon->SiguienteRegistro());
				$sHtml_1 .= '</table>';
        }
    }
    $oCon->Free();
    

	$html  ='<!DOCTYPE html>
					<html lang="en">
					  <head>
						<meta charset="utf-8">
						<title>ESTADO DE CUENTA '.$nom_clpv.'</title>
						<link rel="stylesheet" href="style.css" media="all" />
					  </head>
					  <body>
							<table border="0">
									<tr>
										<td style="text-align:center;width: 50%;">
											<div id="project">
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 15.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black"><strong>'.$empr_nom.'</strong></span>
												</div>
												<br>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
															<strong>SUCURSAL:</strong>
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$sucu_nom.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
															<strong>DIRECCION:</strong>
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$empr_dir.'
													</span>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
															<strong>RNC:</strong>
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$empr_ruc.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
															<strong>TELEFONO:</strong>
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$sucu_telf.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
															<strong>EMAIL:</strong>
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$sucu_email.'
													</span>
												</div>
											</div>
										</td>
										<td style="text-align:center;width: 50%;">
											<img src="' . $empr_path_logo . '" width="310" height="110">
										</td>
									</tr>									
									<tr>
										<td colspan="2" style="height:auto;">
											<br>
											<h1 style="height:30px">
												<span style="left: 434.167px; top: 139.372px; font-size: 16.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black;">
														Informacion General
												</span>
											</h1>
										</td>
									</tr>
									<tr>
										<td style="text-align:center;width: 50%;">
											<div id="project">
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Cliente:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$nom_clpv.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Direccion:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$direccion.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Codigo:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$codigo.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Telefono:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$telefono.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															N.- Cuotas:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'. number_format($valorCuotas, 2, '.', ',').'
													</span>
												</div>
											</div>
										</td>
										<td style="text-align:center;width: 50%;">
											<div id="project">
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Identificacion:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$ruc_clpv.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Barrio:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$barrio.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Fecha Contrato:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.fecha_mysql_dmy($fecha_contrato).'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Email:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$correo.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Balance Actual:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														' . number_format($tarifa + $tot_add - $valor_pago - $valor_no_uso - $desc_tar, 2, '.', ',') . '
													</span>
												</div>
											</div>
										</td>
									</tr>	
							</table>';
	
	$html .='<table border="0">
				<tr>
					<td colspan="2" style="height:auto;">
						<br>
						<h1 style="height:30px">
							<span style="left: 434.167px; top: 139.372px; font-size: 16.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black;">
									Factura Mes Anterior (RD$)
							</span>
						</h1>
					</td>
					<td colspan="2" style="height:auto;">
						<br>
						<h1 style="height:30px">
							<span style="left: 434.167px; top: 139.372px; font-size: 16.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black;">
									Factura del Mes (RD$)
							</span>
						</h1>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="height:auto;" valign="top">
						<table border="0" align="center">
								<tr>
									<td style="text-align:left; height:25px">
										<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
											Balance Anterior:
										</span>
									</td>
									<td style="text-align:right; height:25px">
										<span style="right: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
											'.number_format($bal_anterior, 2, '.', ',').'
										</span>
									</td>
								</tr>
								<tr>
									<td style="text-align:left; height:25px">
										<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
											Pago Recibido, '.$fec_pant.'
										</span>
									</td>
									<td style="height:25px">
										<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
											- '.number_format($pago_ant, 2, '.', ',').'
										</span>
									</td>
								</tr>
								<tr>
									<td style="text-align:left; height:25px">
										<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
												<strong>Balance Mes Anterior:</strong>
										</span>
									</td>
									<td>
										<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
											<strong>$ '.number_format(( $bal_anterior - $pago_ant ), 2, '.', ',').'</strong>
										</span>
									</td>
								</tr>
								<tr>
									<td style="text-align:left; height:25px"></td>
									<td></td>
								</tr>
						</table>

					</td>
					<td colspan="2" style="height:auto;" valign="top">
						<table border="0" align="center">
							<tr>
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										Subtotal:
									</span>
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										'.number_format($fact_tot, 2, '.', ',').'
									</span>
								</td>
							</tr>
							<tr>									
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										ITBIS - 18%
									</span>
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										'.number_format($fact_iva, 2, '.', ',').'
									</span>							
								</td>
							</tr>
							<tr>
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										CDT - 2%
									</span>							
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										'.number_format($fact_ice, 2, '.', ',').'
									</span>
								</td>
							</tr>
							<tr>
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										ISC - 10%
									</span>
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										'.number_format($fact_irbp, 2, '.', ',').'
									</span>
								</td>
							</tr>
							<tr>
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										<strong>Total del Mes:</strong>
									</span>
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										<strong>$ '.number_format(( $fact_tot + $fact_iva + $fact_ice + $fact_irbp ), 2, '.', ',').'</strong>
									</span>
								</td>
							</tr>
							<tr>
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										<strong>Fecha de Pago:</strong>
									</span>
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										<strong>'.Mes_func($mes).'  '.$fecha_cobro.' , '.$anio.'</strong>
									</span>
								</td>
							</tr>
							<tr>
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										<strong>Total a Pagar:</strong>
									</span>
								</td>';

	$total_pagar = 0;
	if( ($bal_anterior - $pago_ant) > 0 ){
		$total_pagar = number_format(($bal_anterior - $pago_ant) + ( $fact_tot + $fact_iva + $fact_ice + $fact_irbp ), 2, '.', ',');
	}
	$html .='
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										<strong>$ '.$total_pagar.'</strong>
									</span>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			 </table>';
	/*$html .='<table border="0">
				<tr>
					<td style="text-align:center; height:auto;">
						<div id="project">
							<div>

								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										Balance Anterior:
								</span>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									'.number_format($bal_anterior, 2, '.', ',').'
								</span>
							</div>
							<div>
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										Pago Recibido, '.$fec_pant.':
								</span>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									- '.number_format($pago_ant, 2, '.', ',').'
								</span>
							</div>
							<div>
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										<strong>Balance Mes Anterior:</strong>
								</span>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									<strong>$ '.number_format(( $bal_anterior - $pago_ant ), 2, '.', ',').'</strong>
								</span>
							</div>
						</div>
					</td>
				</tr>	
			 </table>';
	$html .='<table>
				<tr>
					<td colspan="2" style="height:auto;">
						<br>
						<h1 style="height:30px">
							<span style="left: 434.167px; top: 139.372px; font-size: 16.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;">
									Factura del Mes (RD$)
							</span>
						</h1>
					</td>
				</tr>	
			</table>';*/
	/*$html .='<table border="0">
				<tr>
					<td style="text-align:center;width: 70%;">
						<div id="project">
							<div>
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										Subtotal:
								</span>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									'.number_format($fact_tot, 2, '.', ',').'
								</span>
							</div>
							<div>
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										ITBIS - 18%
								</span>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									'.number_format($fact_iva, 2, '.', ',').'
								</span>
							</div>
							<div>
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										CDT - 2%
								</span>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									'.number_format($fact_ice, 2, '.', ',').'
								</span>
							</div>
							<div>
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										ISC - 10%
								</span>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									'.number_format($fact_irbp, 2, '.', ',').'
								</span>
							</div>							
							<div>
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										<strong>Tolta del Mes:</strong>
								</span>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									<strong>$ '.number_format(( $fact_tot + $fact_iva + $fact_ice + $fact_irbp ), 2, '.', ',').'</strong>
								</span>
							</div>
						</div>
					</td>
				</tr>	
			</table>';	  
	*/	  
	$html .='<table>
				<tr>
					<td colspan="2" style="height:auto;">
						<br>
						<h1 style="height:30px">
							<span style="left: 434.167px; top: 139.372px; font-size: 16.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black;">
									Mensualidad ' . strtoupper(Mes_func($mes)) . ' / ' . $anio . '
							</span>
						</h1>
					</td>
				</tr>	
			</table>';
	$html .= $sHtml_1;  	
	$html .='<table>
				<tr>
					<td colspan="2" style="height:auto;">
						<br>
						<h1 style="height:30px">
							<span style="left: 434.167px; top: 139.372px; font-size: 16.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black;">
									Planes
							</span>
						</h1>
					</td>
				</tr>	
			</table>';
	$html .= $sHtml_0;                
    $html .='</table>';
	$html .='</body>
			 </html>';
	return $html;
	
}



function reporte_factura_rd( $fact_cod_fact='', $idsucursal='', $clave='' ){
	global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $idempresa = $_SESSION['U_EMPRESA'];
	
      
	
	$sql = "select fact_cod_contr , fact_cod_clpv, fact_fech_fact, fact_tip_vent,
					fact_tot_fact, fact_iva, fact_dsg_valo, fact_fle_fact, fact_otr_fact , fact_val_irbp, fact_ice,
					fact_num_preimp
					from saefact where
					fact_cod_empr = $idempresa and
					fact_cod_sucu = $idsucursal and
					fact_cod_fact = $fact_cod_fact ";
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
				$idcontrato = $oIfx->f('fact_cod_contr');
				$clpv_cod   = $oIfx->f('fact_cod_clpv');
				$fact_fech  = fecha_mysql_func($oIfx->f('fact_fech_fact'));
				$fact_tip   = $oIfx->f('fact_tip_vent');
				$fact_tot   = $oIfx->f('fact_tot_fact');
				$fact_iva   = $oIfx->f('fact_iva');
				$fact_dsg   = $oIfx->f('fact_dsg_valo');
				$fact_fle   = $oIfx->f('fact_fle_fact');
				$fact_otr   = $oIfx->f('fact_otr_fact');
				$fact_irbp  = $oIfx->f('fact_val_irbp');
				$fact_ice   = $oIfx->f('fact_ice');
				$fact_num   = $oIfx->f('fact_num_preimp');
		}
	}
	$oIfx->Free();

	$sql = "select tcmp_cod_tcmp, tcmp_des_tcmp from saetcmp where tcmp_cod_tcmp = '$fact_tip' ";
	$tcmp_des_tcmp = consulta_string_func($sql, 'tcmp_des_tcmp', $oIfx, 0);

	// DETALLE FACTURA
	$sHtml_1 = '<table>
					<tr>
						<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Codigo 
							</span>
 						</th>
						<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Plan
							</span>
						</th>
						<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Cantidad
							</span>
						</th>
						<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Precio
							</span>
						</th>
						<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Impuesto
							</span>
						</th>
						<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Total
							</span>
						</th>
					</tr>';

	$sql = "select dfac_cod_prod, dfac_nom_prod, dfac_cant_dfac, 
				dfac_precio_dfac, dfac_mont_total, dfac_por_iva
				from saedfac where dfac_cod_empr = $idempresa and dfac_cod_fact= $fact_cod_fact ";
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			do{
				$dfac_cod_prod   = $oIfx->f('dfac_cod_prod');
				$dfac_nom_prod   = $oIfx->f('dfac_nom_prod');
				$dfac_cant_dfac  = $oIfx->f('dfac_cant_dfac');
				$dfac_precio_dfac= $oIfx->f('dfac_precio_dfac');
				$dfac_mont_total = $oIfx->f('dfac_mont_total');
				$dfac_por_iva    = $oIfx->f('dfac_por_iva');

				$sHtml_1 .= '<tr>';
                $sHtml_1 .= '<td style="text-align:left; height:25px">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . $dfac_cod_prod . '
								</span>
							 </td>';
				$sHtml_1 .= '<td style="text-align:left;">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . $dfac_nom_prod . '
								</span>
							 </td>';
				$sHtml_1 .= '<td style="text-align:right;">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . number_format($dfac_cant_dfac, 2, '.', ',') . '
								</span>
							 </td>';
                $sHtml_1 .= '<td style="text-align:right;">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . number_format($dfac_precio_dfac, 2, '.', ',') . '
								</span>
							 </td>';
                $sHtml_1 .= '<td style="text-align:right;">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . number_format($dfac_por_iva, 2, '.', ',') . '
								</span>
							 </td>';
				$sHtml_1 .= '<td style="text-align:right;">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . number_format($dfac_mont_total, 2, '.', ',') . '
								</span>
							</td>';
				$sHtml_1 .= '</tr>';
			}while($oIfx->SiguienteRegistro());
		}
	}
	$oIfx->Free();

	$sHtml_1 .= '</table>';


	// FORMA DE PAGO
	
	$sHtml_2 = '<table style="width:60%">
					<tr>
						<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Codigo 
							</span>
 						</th>
						<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Forma Pago
							</span>
						</th>
						<th class="service" align="center" style="text-align:center;">
							<span style="text-align:center; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px; ">
								Valor
							</span>
						</th>
					</tr>';
	$sql = "select fxfp_val_fxfp, fxfp_cod_fpag, fxfp_cot_fpag , fpag_des_fpag
					from saefxfp , saefpag where
					fpag_cod_fpag = fxfp_cod_fpag and
					fpag_cod_empr = $idempresa and
					fxfp_cod_empr = $idempresa and
					fxfp_cod_fact = $fact_cod_fact ";
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			do{
				$fxfp_val_fxfp   = $oIfx->f('fxfp_val_fxfp');
				$fxfp_cod_fpag   = $oIfx->f('fxfp_cod_fpag');
				$fpag_des_fpag   = $oIfx->f('fpag_des_fpag');
				$fxfp_cot_fpag   = $oIfx->f('fxfp_cot_fpag');
				
				$sHtml_2 .= '<tr>';
                $sHtml_2 .= '<td style="text-align:left; height:25px">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . $fxfp_cot_fpag . '
								</span>
							 </td>';
				$sHtml_2 .= '<td style="text-align:left;">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . $fpag_des_fpag . '
								</span>
							 </td>';
				$sHtml_2 .= '<td style="text-align:right;">
								<span style="left: 434.167px; top: 139.372px; font-size: 10.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
									' . number_format($fxfp_val_fxfp, 2, '.', ',') . '
								</span>
							</td>';
				$sHtml_2 .= '</tr>';
			}while($oIfx->SiguienteRegistro());
		}
	}
	$oIfx->Free();

	$sHtml_2 .= '</table>';

	//clases contratos
    $Contratos = new Contratos($oCon, $oIfx, $idempresa, $idsucursal, $clpv_cod, $idcontrato);
	
	$arrayContrato = $Contratos->consultarContrato( );
    $telefono      = $Contratos->consultaTelefonos();
	$correo        = $Contratos->consultaEmail();
	
	foreach($arrayContrato as $val){
			$id                 = $val[0];                     $id_empresa         = $val[1];                   $id_sucursal        = $val[2]; 
			$id_clpv            = $val[3];                     $id_ciudad          = $val[4];                   $codigo             = $val[5];  
			$nom_clpv           = $val[6];                     $ruc_clpv           = $val[7];                   $fecha_contrato     = $val[8]; 
			$fecha_firma        = $val[9];                     $fecha_corte        = $val[10];                  $fecha_cobro        = $val[11]; 
			$duracion           = $val[12];                    $penalidad          = $val[13];                  $estado             = $val[14]; 
			$vendedor           = $val[15];                    $user_web           = $val[16];                  $fecha_server       = $val[17]; 
			$tarifa             = $val[18];                    $id_dire            = $val[19];                  $saldo_mora         = $val[20]; 
			$fecha_instalacion  = $val[21];                    $detalle            = $val[22];                  $sobrenombre        = $val[23]; 
			$limite             = $val[24];                    $cobrador           = $val[25];                  $tipo_contrato      = $val[26]; 
			$cheque_sn          = $val[27];                    $cobro_directo      = $val[28];                  $id_sector          = $val[29]; 
			$id_barrio          = $val[30];                    $direccion          = $val[31];                  $referencia         = $val[32]; 
			$telef              = $val[33];                    $email              = $val[34];                  $latitud            = $val[35]; 
			$longitud           = $val[36];                    $abonado            = $val[37];                  $estado_tmp         = $val[38]; 
			$nombre             = $val[39];                    $apellido           = $val[40];                  $foto               = $val[41]; 
			$observaciones      = $val[42];                    $tipo_duracion      = $val[43];                  $fecha_c_vence      = $val[44]; 
			$estadoNombre       = $val[45];                    $estadoClass        = $val[46];                  $sector             = $val[47];  
			$barrio             = $val[48];                    $tipoContrato       = $val[49];                  $vend_nom_vend      = $val[50]; 
			$nom_cobrador       = $val[51];                    $estadoColor        = $val[52];                  $tipo_ncf           = $val[53]; 
			$tarifa_e           = $val[54];                    $descuento_p        = $val[55];                  $descuento_v        = $val[56]; 
	}// fin

	$sql = "select empr_ruc_empr, empr_dir_empr, empr_nom_empr, empr_path_logo  from saeempr where empr_cod_empr = $idempresa ";
	if($oIfx->Query($sql)){
		$empr_ruc       = $oIfx->f('empr_ruc_empr');
		$empr_dir       = $oIfx->f('empr_dir_empr');
		$empr_nom       = $oIfx->f('empr_nom_empr');
		$empr_path_logo = $oIfx->f('empr_path_logo');
		$empr_nom.=' ';
    }
    
	$sql="SELECT sucu_nom_sucu, sucu_telf_secu, sucu_email_secu FROM saesucu WHERE sucu_cod_sucu='$idsucursal'";
	if($oIfx->Query($sql)){
		$sucu_nom   = $oIfx->f('sucu_nom_sucu');
		$sucu_telf  = $oIfx->f('sucu_telf_secu');
		$sucu_email = $oIfx->f('sucu_email_secu');		
	}
	
	$oIfx->Free();
    setlocale(LC_ALL,"es_ES@euro","es_ES","esp");

	$html  ='<!DOCTYPE html>
					<html lang="en">
					  <head>
						<meta charset="utf-8">
						<title>FACTURA '.$nom_clpv.'</title>
						<link rel="stylesheet" href="style.css" media="all" />
					  </head>
					  <body>
							<table border="0">
									<tr>
										<td style="text-align:center;width: 50%;">
											<div id="project">
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 15.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black"><strong>'.$empr_nom.'</strong></span>
												</div>
												<br>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
															<strong>SUCURSAL:</strong>
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$sucu_nom.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
															<strong>DIRECCION:</strong>
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$empr_dir.'
													</span>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
															<strong>RNC:</strong>
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$empr_ruc.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
															<strong>TELEFONO:</strong>
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$sucu_telf.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
															<strong>EMAIL:</strong>
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$sucu_email.'
													</span>
												</div>
											</div>
										</td>
										<td style="text-align:center;width: 50%;">
											<img src="' . $empr_path_logo . '" width="310" height="110">
										</td>
									</tr>									
									<tr>
										<td colspan="2" style="height:auto;">
											<br>
											<h1 style="height:30px">
												<span style="left: 434.167px; top: 139.372px; font-size: 16.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black;">
														Informacion General
												</span>
											</h1>
										</td>
									</tr>
									<tr>
										<td style="text-align:center;width: 50%;">
											<div id="project">
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Cliente:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$nom_clpv.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Direccion:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$direccion.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Codigo:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$codigo.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Telefono:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$telefono.'
													</span>
												</div>												
											</div>
										</td>
										<td style="text-align:center;width: 50%;">
											<div id="project">
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Identificacion:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$ruc_clpv.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Barrio:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$barrio.'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Fecha Contrato:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.fecha_mysql_dmy($fecha_contrato).'
													</span>
												</div>
												<div>
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
															Email:
													</span>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
														'.$correo.'
													</span>
												</div>
											</div>
										</td>
									</tr>	
							</table>';
	

	$html .='<table border="0">
				<tr>
					<td colspan="2" style="height:auto;">
						<br>
						<h1 style="height:30px">
							<span style="left: 434.167px; top: 139.372px; font-size: 16.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black;">
									Factura RD$: '.$tcmp_des_tcmp.'
							</span>
						</h1>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="height:auto;" valign="top">
						<table border="0" align="center" >
							<tr>
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										Factura:
									</span>
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										'.$fact_num.'
									</span>
								</td>
							</tr>
							<tr>
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										Fecha Factura:
									</span>
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										'.$fact_fech.'
									</span>
								</td>
							</tr>
							<tr>
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										Subtotal:
									</span>
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										'.number_format($fact_tot, 2, '.', ',').'
									</span>
								</td>
							</tr>
							<tr>
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										Descuento $:
									</span>
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										'.number_format($fact_dsg, 2, '.', ',').'
									</span>
								</td>
							</tr>
							<tr>									
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										ITBIS - 18%
									</span>
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										'.number_format($fact_iva, 2, '.', ',').'
									</span>							
								</td>
							</tr>
							<tr>
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										CDT - 2%
									</span>							
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										'.number_format($fact_ice, 2, '.', ',').'
									</span>
								</td>
							</tr>
							<tr>
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										ISC - 10%
									</span>
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										'.number_format($fact_irbp, 2, '.', ',').'
									</span>
								</td>
							</tr>
							<tr>
								<td style="text-align:left; height:25px">
									<span style="left: 134.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: #5D6975;; height: 20px">
										<strong>Total Factura:</strong>
									</span>
								</td>
								<td>
									<span style="left: 434.167px; top: 139.372px; font-size: 11.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black; height: 20px">
										<strong>$ '.number_format(( $fact_tot + $fact_iva + $fact_ice + $fact_irbp  - $fact_dsg ), 2, '.', ',').'</strong>
									</span>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			 </table>';
	
	$html .='<table>
				<tr>
					<td colspan="2" style="height:auto;">
						<br>
						<h1 style="height:30px">
							<span style="left: 434.167px; top: 139.372px; font-size: 16.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black;">
									Detalle Factura
							</span>
						</h1>
					</td>
				</tr>	
			</table>';
	$html .= $sHtml_1; 
	$html .='<table>
				<tr>
					<td colspan="2" style="height:auto;">
						<br>
						<h1 style="height:30px">
							<span style="left: 434.167px; top: 139.372px; font-size: 16.0833px; font-family: sans-serif; transform: scaleX(1.00283); color: black;">
									Forma de Pago
							</span>
						</h1>
					</td>
				</tr>	
			</table>';
	$html .= $sHtml_2; 
    $html .='</table>';
	$html .='</body>
			 </html>';

	return $html;
	
}

?>