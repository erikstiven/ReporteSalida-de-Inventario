<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class generaXMLComprobanteElectronico{
	
	var $oConexion; 
	var $empr_nom_empr; 
	var $empr_ruc_empr; 
	var $empr_dir_empr; 
	var $empr_conta_sn; 
	var $empr_num_resu; 
	var $sucu_tip_ambi; 
	var $sucu_tip_emis; 
	var $sucu_tip_toke; 
	var $claveAcceso; 
	
	function __construct($oConexion, $empresa, $sucursal){
       
	   $sqlSucu = "select  sucu_tip_ambi, sucu_tip_emis, sucu_tip_toke 
					from saesucu s
					where sucu_cod_sucu = $sucursal";
		if ($oConexion->Query($sqlSucu)) {
			if ($oConexion->NumFilas() > 0) {
				$sucu_tip_ambi = $oConexion->f('sucu_tip_ambi');
				$sucu_tip_emis = $oConexion->f('sucu_tip_emis');
				$sucu_tip_toke = $oConexion->f('sucu_tip_toke');
			}
		}
		$oConexion->Free();

		$sqlEmpr = "select empr_nom_empr, empr_ruc_empr, empr_dir_empr, 
					empr_conta_sn, empr_num_resu 
					from saeempr 
					where  empr_cod_empr = $empresa ";
		if ($oConexion->Query($sqlEmpr)) {
			if ($oConexion->NumFilas() > 0) {
				$empr_nom_empr = trim($oConexion->f('empr_nom_empr'));
				$empr_ruc_empr = $oConexion->f('empr_ruc_empr');
				$empr_dir_empr = trim($oConexion->f('empr_dir_empr'));
				$empr_conta_sn = $oConexion->f('empr_conta_sn');
				$empr_num_resu = $oConexion->f('empr_num_resu');
				
				if ($empr_conta_sn == 'S') {
					$empr_conta_sn = 'SI';
				} else {
					$empr_conta_sn = 'NO';
				}
			}
		}
		$oConexion->Free();
		
		$this->empr_nom_empr = $empr_nom_empr; 
		$this->empr_ruc_empr = $empr_ruc_empr; 
		$this->empr_dir_empr = $empr_dir_empr;
		$this->empr_conta_sn = $empr_conta_sn; 
		$this->empr_num_resu = $empr_num_resu;
		$this->sucu_tip_ambi = $sucu_tip_ambi;
		$this->sucu_tip_emis = $sucu_tip_emis;	 
		$this->sucu_tip_toke = $sucu_tip_toke;			
    } 
	
	
	
	
	function generaXMLFacturaVenta($oConexion, $oConexion_, $empresa, $sucursal, $idComprobante){
		
		$empr_nom_empr = $this->empr_nom_empr; 
		$empr_ruc_empr = $this->empr_ruc_empr; 
		$empr_dir_empr = $this->empr_dir_empr;
		$empr_conta_sn = $this->empr_conta_sn; 
		$empr_num_resu = $this->empr_num_resu;
		$sucu_tip_ambi = $this->sucu_tip_ambi;
		$sucu_tip_emis = $this->sucu_tip_emis;
	
		if ($empr_conta_sn == 'S') {
			$empr_conta_sn = 'SI';
		} else {
			$empr_conta_sn = 'NO';
		}
		
		$sqlFactVent = "select f.fact_cod_fact, f.fact_fech_fact, f.fact_num_preimp, f.fact_ruc_clie, 
						f.fact_nom_cliente, f.fact_iva, f.fact_con_miva, f.fact_sin_miva, 
						f.fact_tot_fact, f.fact_erro_sri, f.fact_email_clpv, f.fact_erro_sri, 
                        f.fact_tlf_cliente, f.fact_dir_clie, f.fact_nse_fact, f.fact_cod_clpv , 
						f.fact_con_miva, f.fact_sin_miva, f.fact_iva, fact_ice, f.fact_dsct_soli, 
						f.fact_val_irbp, c.clv_con_clpv, f.fact_cm2_fact,  f.fact_opc_fact 
                        from saefact f, saeclpv c where 
                        c.clpv_cod_clpv = f.fact_cod_clpv and
                        c.clpv_cod_empr = $empresa and
                        c.clpv_clopv_clpv = 'CL' and
                        f.fact_cod_empr = $empresa  and 
                        f.fact_cod_sucu = $sucursal and 
						f.fact_cod_fact = $idComprobante and
                        f.fact_aprob_sri = 'N' and 
                        f.fact_fon_fact in (select para_fac_cxc  from saepara where
											para_cod_empr = $empresa and
											para_cod_sucu = $sucursal) and
                        f.fact_est_fact <> 'AN'";
		
		if ($oConexion->Query($sqlFactVent)) {
			if ($oConexion->NumFilas() > 0) {
				do {
					$fact_cod_fact = $oConexion->f("fact_cod_fact");
					$fact_fech_fact = $oConexion->f("fact_fech_fact");
					$fact_num_preimp = $oConexion->f("fact_num_preimp");
					$fact_ruc_clie = $oConexion->f("fact_ruc_clie");
					$fact_nom_cliente = $oConexion->f("fact_nom_cliente");
					$fact_iva = $oConexion->f("fact_iva");
					$fact_con_miva = $oConexion->f("fact_con_miva");
					$fact_sin_miva = $oConexion->f("fact_sin_miva");
					$fact_tot_fact = $oConexion->f("fact_tot_fact");
					$fact_dsct_soli = $oConexion->f("fact_dsct_soli");
					$fact_email_clpv = $oConexion->f("fact_email_clpv");
					$fact_erro_sri = $oConexion->f("fact_erro_sri");
					$fact_tlf_cliente = $oConexion->f("fact_tlf_cliente");
					$fact_dir_clie = $oConexion->f("fact_dir_clie");
					$fact_nse_fact = $oConexion->f("fact_nse_fact");
					$fact_cod_clpv = $oConexion->f("fact_cod_clpv");
					$fact_ice = $oConexion->f("fact_ice");
					$fact_val_irbp = $oConexion->f("fact_val_irbp");
					$clv_con_clpv = $oConexion->f("clv_con_clpv");
					$cod_almacen = $oConexion->f("fact_cm2_fact");
					$orden_compra = $oConexion->f("fact_opc_fact");
				}while ($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		
		$clv_con_clpv = strlen($fact_ruc_clie);
		if ($clv_con_clpv == 13)
			$tipoIdentificacionComprador = '04';
		else if ($clv_con_clpv == 10)
			$tipoIdentificacionComprador = '05';
		else
			$tipoIdentificacionComprador = '06';

		$totalSinImpuestos = $fact_con_miva + $fact_sin_miva;
		$importeTotal = $totalSinImpuestos + $fact_iva + $fact_ice + $fact_val_irbp;

		$estable = substr($fact_nse_fact, 0, 3);
		$serie = substr($fact_nse_fact, 3, 6);

		$fec_fact = cambioFecha($fact_fech_fact, 'mm/dd/aaaa', 'dd/mm/aaaa');
		
		$clave_acceso = generaClaveAccesoSri('01', $empr_ruc_empr, $sucu_tip_ambi, $sucu_tip_emis, $fact_fech_fact, $fact_nse_fact, $fact_num_preimp);
		$this->claveAcceso = $clave_acceso;	

		$sqlDetalle = "select * from saedfac where 
						dfac_cod_fact = $idComprobante and 
						dfac_cod_sucu = $sucursal and 
						dfac_cod_empr = $empresa ";
		if ($oConexion->Query($sqlDetalle)) {
			if($oConexion->NumFilas() > 0){
				$xmlDeta.='<detalles>';
				$bandera = 3;
				$baseImponibleIRBP = 0;
				$totalDescuento = 0;
				do {
					$dfac_cod_prod = $oConexion->f("dfac_cod_prod");
					$dfac_cant_dfac = $oConexion->f("dfac_cant_dfac");
					$dfac_precio_dfac = $oConexion->f("dfac_precio_dfac");
					$dfac_mont_total = $oConexion->f("dfac_mont_total");
					$dfac_por_iva = $oConexion->f("dfac_por_iva");
					$dfac_por_irbp = $oConexion->f("dfac_por_irbp");
					$dfac_des1 = $oConexion->f("dfac_des1_dfac");
					$dfac_des2 = $oConexion->f("dfac_des2_dfac");
					$dfac_des3 = $oConexion->f("dfac_des3_dfac");
					$dfac_des4 = $oConexion->f("dfac_des4_dfac");
					$dfac_des5 = $oConexion->f("dfac_por_dsg");

					$descuento = 0;
					if ($dfac_des1 != 0 || $dfac_des2 != 0 || $dfac_des3 != 0 || $dfac_des4 != 0 || $dfac_des5 != 0) {
						$descuento = ( $dfac_cant_dfac * $dfac_precio_dfac ) - $dfac_mont_total;
						if ($descuento != 0) {
							$totalDescuento += $descuento;
						}
					}// fin if

					$descuento = number_format($descuento, 2, '.', '');
					
					//query Producto
					$sqlDescripcionProd = "select prod_nom_prod, prod_cod_barra from saeprod where 
											prod_cod_prod = '$dfac_cod_prod' and 
											prod_cod_empr = $empresa and 
											prod_cod_sucu = $sucursal ";
					if ($oConexion_->Query($sqlDescripcionProd)) {
						if ($oConexion_->NumFilas() > 0) {
							$prod_nom_prod = $oConexion_->f('prod_nom_prod');
							$prod_cod_barra = $oConexion_->f('prod_cod_barra');
						}
					}
					$oConexion_->Free();

					if (empty($prod_cod_barra)) {
						$prod_cod_barra = $dfac_cod_prod;
					}

					$xmlDeta.='<detalle>';
					$xmlDeta.="<codigoPrincipal>$dfac_cod_prod</codigoPrincipal>";
					$xmlDeta.="<codigoAuxiliar>$prod_cod_barra</codigoAuxiliar>";
					$xmlDeta.="<descripcion>$prod_nom_prod</descripcion>";
					$xmlDeta.="<cantidad>$dfac_cant_dfac</cantidad>";
					$xmlDeta.="<precioUnitario>" . round($dfac_precio_dfac, 4) . "</precioUnitario>";
					$xmlDeta.="<descuento>" . round($descuento, 4) . "</descuento>";
					$xmlDeta.="<precioTotalSinImpuesto>" . round($dfac_mont_total, 4) . "</precioTotalSinImpuesto>";
					$xmlDeta.='<impuestos>';
					
					if ($dfac_por_iva == 0) {
						$codigoPorcentaje = 0;
						$valor = 0.00;
						$tarifa = 0.00;
					} elseif($dfac_por_iva == 12) {
						$codigoPorcentaje = 2;
						$valor = round((($dfac_mont_total * $dfac_por_iva) / 100), 2);
						$tarifa = 12.00;
						$bandera = 2;
					} elseif($dfac_por_iva == 14) {
						$codigoPorcentaje = 3;
						$valor = round((($dfac_mont_total * $dfac_por_iva) / 100), 2);
						$tarifa = 14.00;
						$bandera = 3;
					}

					$xmlDeta.='<impuesto>';
					$xmlDeta.='<codigo>2</codigo>';
					$xmlDeta.="<codigoPorcentaje>$codigoPorcentaje</codigoPorcentaje>";
					$xmlDeta.="<tarifa>$tarifa</tarifa>";
					$xmlDeta.="<baseImponible>" . round($dfac_mont_total, 2) . "</baseImponible>";
					$xmlDeta.="<valor>" . round($valor, 2) . "</valor>";
					$xmlDeta.='</impuesto>';

					$unid_caja = 1;
					if ($dfac_por_irbp > 0) {
						$sql_unid = "select COALESCE(prod_uni_caja,1) as prod_uni_caja  from saeprod where
																	prod_cod_empr = $id_empresa and
																	prod_cod_sucu = $id_sucursal and
																	prod_cod_prod = '$dfac_cod_prod' ";
						$unid_caja = consulta_string_func($sql_unid, 'prod_uni_caja', $oIfx, 1);

						$xmlDeta.='<impuesto>';
						$xmlDeta.='<codigo>5</codigo>';
						$xmlDeta.="<codigoPorcentaje>5001</codigoPorcentaje>";
						$xmlDeta.="<tarifa>0.00</tarifa>";
						$xmlDeta.="<baseImponible>" . round($dfac_mont_total, 2) . "</baseImponible>";
						$xmlDeta.="<valor>" . number_format($dfac_por_irbp * $dfac_cant_dfac * $unid_caja, 2, '.', '') . "</valor>";
						$xmlDeta.='</impuesto>';
						$baseImponibleIRBP += $dfac_mont_total;
					}            

					$xmlDeta.='</impuestos>';
					$xmlDeta.='</detalle>';
				} while ($oConexion->SiguienteRegistro());
				$xmlDeta.='</detalles>';
			}
		}

		//genera cabecera XML
		$xml .= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
					<factura id="comprobante" version="1.1.0">
						<infoTributaria>
							<ambiente>' . $sucu_tip_ambi . '</ambiente>
							<tipoEmision>' . $sucu_tip_emis . '</tipoEmision>
							<razonSocial>' . $empr_nom_empr . '</razonSocial>
							<nombreComercial>' . $empr_nom_empr . '</nombreComercial>
							<ruc>' . $empr_ruc_empr . '</ruc>
							<claveAcceso>' . $clave_acceso . '</claveAcceso>
							<codDoc>01</codDoc>
							<estab>' . $estable . '</estab>
							<ptoEmi>' . $serie . '</ptoEmi>
							<secuencial>' . $fact_num_preimp . '</secuencial>
							<dirMatriz>' . $empr_dir_empr . '</dirMatriz>
						</infoTributaria>
						<infoFactura>
							<fechaEmision>' . $fec_fact . '</fechaEmision>';
		if ($empr_num_resu != '')
			$xml .= '<contribuyenteEspecial>' . $empr_num_resu . '</contribuyenteEspecial>';

		$xml .= '<obligadoContabilidad>' . $empr_conta_sn . '</obligadoContabilidad>
					<tipoIdentificacionComprador>' . $tipoIdentificacionComprador . '</tipoIdentificacionComprador>
					<razonSocialComprador>' . htmlspecialchars($fact_nom_cliente) . '</razonSocialComprador>
					<identificacionComprador>' . $fact_ruc_clie . '</identificacionComprador>
					<totalSinImpuestos>' . round($totalSinImpuestos, 2) . '</totalSinImpuestos>
					<totalDescuento>' . round($totalDescuento, 2) . '</totalDescuento>';
		$xml .= "<totalConImpuestos>";
		if ($fact_con_miva != '') {
			$xml .= '<totalImpuesto>
						<codigo>2</codigo>
						<codigoPorcentaje>'.$bandera.'</codigoPorcentaje>
						<baseImponible>' . round($fact_con_miva, 2) . '</baseImponible>
						<valor>' . round($fact_iva, 2) . '</valor>
					</totalImpuesto>';
		}
		if ($fact_sin_miva != '') {
			$xml .= '<totalImpuesto>
						<codigo>2</codigo>
						<codigoPorcentaje>0</codigoPorcentaje>
						<baseImponible>' . round($fact_sin_miva, 2) . '</baseImponible>
						<valor>0.00</valor>
					</totalImpuesto>';
		}
		if ($fact_val_irbp > 0) {
			$xml .= '<totalImpuesto>
						<codigo>5</codigo>
						<codigoPorcentaje>5001</codigoPorcentaje>
						<baseImponible>' . $baseImponibleIRBP . '</baseImponible>
						<valor>' . round($fact_val_irbp, 2) . '</valor>
					</totalImpuesto>';
		}
		$xml .= "</totalConImpuestos> ";

		if($fact_dsct_soli > 0){
			$xml .= '<compensaciones>
						<compensacion>
							<codigo>1</codigo>
							<tarifa>2</tarifa>
							<valor>' . round($fact_dsct_soli, 2) . '</valor>
						</compensacion>
				   </compensaciones>';
		}
				
		$xml .='<propina>0.00</propina>
						<importeTotal>' . round($importeTotal, 2) . '</importeTotal>
						<moneda>DOLAR</moneda>';
						
		//query forma de pago
		$sqlFPago = "select fp.fpag_cod_fpagop, fx.fxfp_val_fxfp, fx.fxfp_num_dias
					from saefact f, saefxfp fx, saefpag fp
					where f.fact_cod_fact = fx.fxfp_cod_fact and
					fp.fpag_cod_fpag = fx.fxfp_cod_fpag and
					f.fact_cod_empr = $empresa and
					f.fact_cod_sucu = $sucursal and
					f.fact_cod_fact = $idComprobante";
		if($oConexion->Query($sqlFPago)){
			if($oConexion->NumFilas() > 0){
				$xml .= '<pagos>';
				do{
					$fpag_cod_fpagop = $oConexion->f('fpag_cod_fpagop');
					$fxfp_val_fxfp = $oConexion->f('fxfp_val_fxfp');
					$fxfp_num_dias = $oConexion->f('fxfp_num_dias');
					
					$xml .= '<pago>
								<formaPago>'.$fpag_cod_fpagop.'</formaPago>
								<total>' . round($fxfp_val_fxfp, 2) . '</total>
								<plazo>'.$fxfp_num_dias.'</plazo> 
								<unidadTiempo>dias</unidadTiempo> 
							</pago>';
				}while($oConexion->SiguienteRegistro());
				$xml .= '</pagos>';
			}
		}
		$oConexion->Free();
				
		$xml .='</infoFactura>';		
		$xml .= $xmlDeta;

		$aDataInfoAdic['Direccion'] = $fact_dir_clie;
		$aDataInfoAdic['Telefono'] = $fact_tlf_cliente;
		$aDataInfoAdic['Email'] = $fact_email_clpv;
		if (!empty($cod_almacen)) {
			$aDataInfoAdic['codigoAlmacen'] = $cod_almacen;
		}

		if (!empty($orden_compra)) {
			$aDataInfoAdic['ordenCompra'] = $orden_compra;
		}

		$etiqueta = array_keys($aDataInfoAdic);
		if (count($etiqueta) > 0) {
			$xml .= '<infoAdicional>';
			foreach ($etiqueta as $nom) {
				if ($aDataInfoAdic[$nom] != '')
					$xml.= "<campoAdicional nombre=\"$nom\">$aDataInfoAdic[$nom]</campoAdicional>";
			}
			$xml .= '</infoAdicional>';
		}

		$xml .='</factura>';
		
		/*eliminaArchivos(path(DIR_INCLUDE) . '/archivos/');
		eliminaArchivos("C:/Jireh/comprobantes electronicos/generados/");
		eliminaArchivos("C:/Jireh/Comprobantes Electronicos/firmados/");*/

		//genera directo xml
		$serv = "C:/Jireh/";
		$ruta = $serv . "Comprobantes Electronicos";
		$ruta_gene = $ruta . "/generados";
		if (!file_exists($ruta))
			mkdir($ruta);

		if (!file_exists($ruta_gene))
			mkdir($ruta_gene);

		$nombre = $clave_acceso . ".xml";
		$archivo = fopen($ruta_gene . '/' . $nombre, "w+");

		fwrite($archivo, utf8_encode($xml));
		fclose($archivo);
		
		return $nombre;
	}
	
	
	
	
	function firmarComprobanteElectronico($tipoDocumento, $archivoFirma, $id_docu) {

		$empr_ruc_empr = $this->empr_ruc_empr;
		$sucu_tip_ambi = $this->sucu_tip_ambi;
		$sucu_tip_emis = $this->sucu_tip_emis;
		$sucu_tip_toke = $this->sucu_tip_toke;
		$tiempoEspera = 3;
		
		$pathpdf = "C:/Jireh/comprobantes electronicos/generados";

		//SETEAMOS EL WEB SERVICE PARA FIRMAR LOS COMPROBANTES
		$clientOptions = array(
			"useMTOM" => FALSE,
			'trace' => 1,
			'stream_context' => stream_context_create(array('http' => array('protocol_version' => 1.0)))
		);

		try {
			$wsdlFirma = new SoapClient("http://localhost:8080/WebServFirma/firmaComprobante?WSDL", $clientOptions);


			$serv = "C:/Jireh/";
			$ruta = $serv . "Comprobantes Electronicos";
			$pathFirmados = $ruta . "/firmados";

			if (!file_exists($ruta)) {
				mkdir($ruta);
			}

			if (!file_exists($pathFirmados)) {
				mkdir($pathFirmados);
			}

			$pathArchivo = "C:/Jireh/comprobantes electronicos/generados/" . $archivoFirma;

			$password = null;

			$aFirma = array("ruc" => $empr_ruc_empr, "tipoAmbiente" => $sucu_tip_ambi, "tiempoEspera" => $tiempoEspera,
				"token" => $sucu_tip_toke, "pathArchivo" => $pathArchivo, "pathFirmados" => $pathFirmados,
				"password" => $password);

			$respFirm = $wsdlFirma->FirmarDocumento($aFirma);

			$respFirm = strtoupper($respFirm->return);

			if ($respFirm == null) {
				$msj = "OK".$respFirm;
			} else {
				$msj = "El archivo fue guardado pero no fue firmado : " . $respFirm;
			}
		} catch (SoapFault $e) {
			$msj = "NO HAY CONECCION CON LA FIRMA";
		}
		
		return $msj;
	}
	
	
	
	
	function validaComprobanteElectronico($archivoFirma) {
				
		$sucu_tip_ambi = $this->sucu_tip_ambi;
		$tiempoEspera = 3;

		$pathpdf = "C:/Jireh/comprobantes electronicos/generados";

		$clientOptions = array(
			"useMTOM" => FALSE,
			'trace' => 1,
			'stream_context' => stream_context_create(array('http' => array('protocol_version' => 1.0)))
		);


		//HACEMOS LA VALIDACION DEL COMPROBANTE SUBIENDO EL ARCHIVO XML YA FIRMADO
		try {

			if ($sucu_tip_ambi == 1) {
				$wsdlValiComp = new SoapClient("https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantes?wsdl", $clientOptions);
			} else {
				$wsdlValiComp = new SoapClient("https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantes?wsdl", $clientOptions);
			}

			$rutaFirm = "C:/Jireh/Comprobantes Electronicos/firmados/" . $archivoFirma;
			$xml = file_get_contents($rutaFirm);

			$aArchivo = array("xml" => $xml);

			$valiComp = new stdClass();
			$valiComp = $wsdlValiComp->validarComprobante($aArchivo);

			$RespuestaRecepcionComprobante = $valiComp->RespuestaRecepcionComprobante;
			$estado = $RespuestaRecepcionComprobante->estado;
	
			if ($estado == 'RECIBIDA') {
				$msj = 'OK';
			} else {
				$comprobantes = $RespuestaRecepcionComprobante->comprobantes;
				$comprobante = $comprobantes->comprobante;
				$mensajes = $comprobante->mensajes;
				$mensaje = $mensajes->mensaje;
				$informacionAdicional = strtoupper($mensaje->informacionAdicional);

				$msj = $informacionAdicional;
			}
		} catch (SoapFault $e) {
			$msj = 'NO HAY CONECCION AL SRI (VALIDAR) ';
		}

		return $msj;
	}


	
	
	function autorizaComprobanteElectronico($idDocumento, $correo) {
		
		$sucu_tip_ambi = $this->sucu_tip_ambi;
		$claveAcceso = $this->claveAcceso; 

		$pathpdf = "C:/Jireh/comprobantes electronicos/generados";

		try {
			$clientOptions = array(
				"useMTOM" => FALSE,
				'trace' => 1,
				'stream_context' => stream_context_create(array('http' => array('protocol_version' => 1.0)))
			);

			if ($sucu_tip_ambi == 1) {
				$wsdlAutoComp = new SoapClient("https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantes?wsdl", $clientOptions);
			} else {
				$wsdlAutoComp = new SoapClient("https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantes?wsdl", $clientOptions);
			}

			//RECUPERA LA AUTORIZACION DEL COMPROBANTE
			$aClave = array("claveAccesoComprobante" => $claveAcceso);

			$autoComp = new stdClass();
			$autoComp = $wsdlAutoComp->autorizacionComprobante($aClave);

			$RespuestaAutorizacionComprobante = $autoComp->RespuestaAutorizacionComprobante;
			$claveAccesoConsultada = $RespuestaAutorizacionComprobante->claveAccesoConsultada;
			$autorizaciones = $RespuestaAutorizacionComprobante->autorizaciones;
			$autorizacion = $autorizaciones->autorizacion;

			if (count($autorizacion) > 1) {
				$estado = $autorizacion[0]->estado;
				$numeroAutorizacion = $autorizacion[0]->numeroAutorizacion;
				$fechaAutorizacion = $autorizacion[0]->fechaAutorizacion;
				$ambiente = $autorizacion[0]->ambiente;
				$comprobante = $autorizacion[0]->comprobante;
				$mensajes = $autorizacion[0]->mensajes;
				$mensaje = $mensajes->mensaje;
			} else {
				$estado = $autorizacion->estado;
				$numeroAutorizacion = $autorizacion->numeroAutorizacion;
				$fechaAutorizacion = $autorizacion->fechaAutorizacion;
				$ambiente = $autorizacion->ambiente;
				$comprobante = $autorizacion->comprobante;
				$mensajes = $autorizacion->mensajes;
				$mensaje = $mensajes->mensaje;
			}


			if ($estado == 'AUTORIZADO') {
				$msj = "OK";
				
				updateComprobanteSRI($claveAcceso, $numeroAutorizacion, $fechaAutorizacion, $idDocumento);

				$dia = substr($claveAccesoConsultada, 0, 2);
				$mes = substr($claveAccesoConsultada, 2, 2);
				$an = substr($claveAccesoConsultada, 4, 4);

				//CREO LOS DIRECTORIOS DE LOS RIDES
				$serv = "C:/Jireh";
				$rutaRide = $serv . "/RIDE";
				$rutaComp = $rutaRide . "/FACTURAS VENTAS";
				$rutaAo = $rutaComp . "/" . $an;
				$rutaMes = $rutaAo . "/" . $mes;
				$rutaDia = $rutaMes . "/" . $dia;

				if (!file_exists($rutaRide)) {
					mkdir($rutaRide);
				}

				if (!file_exists($rutaComp)) {
					mkdir($rutaComp);
				}

				if (!file_exists($rutaAo)) {
					mkdir($rutaAo);
				}

				if (!file_exists($rutaMes)) {
					mkdir($rutaMes);
				}

				if (!file_exists($rutaDia)) {
					mkdir($rutaDia);
				}

				$numero = substr($claveAccesoConsultada, 24, 15);
				$nombre = "Fact_" . $numero . "_" . "$dia-$mes-$an" . ".xml";

				//FORMO EL RIDE
				$ride .= '<?xml version="1.0" encoding="UTF-8"?>';
				$ride .= '<autorizacion>';
				$ride .="<estado>$estado</estado>";
				$ride .="<numeroAutorizacion>$numeroAutorizacion</numeroAutorizacion>";
				$ride .="<fechaAutorizacion>$fechaAutorizacion</fechaAutorizacion>";
				$ride .="<ambiente>$ambiente</ambiente>";
				$ride .="<comprobante><![CDATA[$comprobante]]></comprobante>";
				$ride .='</autorizacion>';

				// ruta del xml
				$archivo_xml = fopen($rutaDia . '/' . $nombre, "w+");
				fwrite($archivo_xml, $ride);
				fclose($archivo_xml);

				$ride = '' . $rutaDia . '/' . $nombre;
				
				$_SESSION['pdf'] = reporte_factura($idDocumento, $claveAcceso, $rutaPdf);

				envio_correo_adj($correo, $ride, $rutaPdf);
			} else {
				$msj = "COMPROBANTE GUARDADO, FIRMADO, ENVIADO, PERO NO AUTORIZADO";
			}
		} catch (SoapFault $e) {
			$msj = "NO HAY CONECCION AL SRI (AUTORIZAR)";
		}

		return $msj;
	}

}
?>