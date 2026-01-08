<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class FacturacionElectronicaGT{
	
	var $oConexion; 
	var $empr_nom_empr; 
	
	function __construct(){
		//$this->empr_nom_empr = $empr_nom_empr; 		
    } 
	
	function GeneraXmlFactura($oConexion, $id_empresa, $id_sucursal, $fact_cod_fact, $clpv_cod_clpv ){
		// PROVINCIA
		$sql = "select provc_cod_provc, provc_nom_provc from saeprovc ";
		unset($array_provc);
		$array_provc = array_dato($oConexion, $sql, 'provc_cod_provc', 'provc_nom_provc');
	
		// CIUDAD
		$sql = "select ciud_cod_ciud,  ciud_nom_ciud from saeciud ";
		unset($array_ciud);
		$array_ciud = array_dato($oConexion, $sql, 'ciud_cod_ciud', 'ciud_nom_ciud');
	
		// UNIDAD
		$sql = "select unid_cod_unid, unid_sigl_unid from saeunid where unid_cod_empr = $id_empresa ";
		unset($array_unid);
		$array_unid = array_dato($oConexion, $sql, 'unid_cod_unid', 'unid_sigl_unid');
	
	
		//datos de la empresa
		$sql = "select empr_nom_empr, empr_ruc_empr, empr_tip_firma, empr_cpo_empr, 
				empr_dir_empr, empr_conta_sn, empr_num_resu , empr_num_estab , empr_cod_prov, empr_cod_ciud
				from saeempr 
				where empr_cod_empr = $id_empresa ";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				$nombre_empr    = trim($oConexion->f('empr_nom_empr'));
				$ruc_empr       = $oConexion->f('empr_ruc_empr');
				$dir_empr       = trim($oConexion->f('empr_dir_empr'));
				$conta_sn       =  $oConexion->f('empr_conta_sn');
				$empr_num_resu  = $oConexion->f('empr_num_resu');
				$empr_tip_firma = $oConexion->f('empr_tip_firma');
				$empr_num_estab = $oConexion->f('empr_num_estab');
				$empr_cpo_empr  = $oConexion->f('empr_cpo_empr');
				$empr_cod_prov  = $array_provc[$oConexion->f('empr_cod_prov')];
				$empr_cod_ciud  = $array_ciud[$oConexion->f('empr_cod_ciud')];
			}
		}
		$oConexion->Free();
		
		//direccion sucursales
		$sql = "select sucu_cod_sucu, sucu_dir_sucu from saesucu where sucu_cod_empr = $id_empresa";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				unset($arrayDireSucu);
				do{
					$arrayDireSucu[$oConexion->f('sucu_cod_sucu')] = $oConexion->f('sucu_dir_sucu');
				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		if ($conta_sn == 'S') {
			$conta_sn = 'SI';
		} else {
			$conta_sn = 'NO';
		}
		
		$sql = "select f.fact_cod_fact, f.fact_cod_sucu, f.fact_fech_fact, f.fact_num_preimp, f.fact_ruc_clie, f.fact_nom_cliente, f.fact_iva, 
							f.fact_con_miva,   f.fact_sin_miva, f.fact_tot_fact, f.fact_erro_sri, f.fact_email_clpv  , f.fact_erro_sri, 
							f.fact_tlf_cliente,  f.fact_dir_clie, f.fact_nse_fact,  f.fact_cod_clpv ,  f.fact_con_miva, f.fact_sin_miva, 
							f.fact_iva, fact_ice,   f.fact_val_irbp, c.clv_con_clpv , f.fact_cm2_fact,  f.fact_opc_fact, f.fact_clav_sri,
							f.fact_cm1_fact, f.fact_aprob_sri
							from saefact f, saeclpv c where 
							c.clpv_cod_clpv   = f.fact_cod_clpv and
							c.clpv_cod_empr   = $id_empresa and
							c.clpv_clopv_clpv = 'CL' and
							f.fact_elec_sn    = 'S'  and
							f.fact_cod_empr = $id_empresa  and  
							f.fact_fon_fact in ( select para_fac_cxc  from saepara where
													para_cod_empr = $id_empresa	and 
													para_cod_sucu = $id_sucursal)   and
							f.fact_est_fact <> 'AN' and
							f.fact_cod_sucu = $id_sucursal and
							f.fact_cod_clpv = $clpv_cod_clpv and
							f.fact_cod_fact = $fact_cod_fact
							order by f.fact_num_preimp ";		
		unset($array_fact);
		if ($oConexion->Query($sql)) {
            if($oConexion->NumFilas() > 0) {
				do{					
					$fact_cod_fact 	= $oConexion->f("fact_cod_fact");
					$fecha_fact 	= fecha_mysql_func($oConexion->f("fact_fech_fact"));
					$secuencial 	= $oConexion->f("fact_num_preimp");
					$ruc 			= $oConexion->f("fact_ruc_clie");
					$cliente 		= $oConexion->f("fact_nom_cliente");
					$iva 			= $oConexion->f("fact_iva");
					$con_iva 		= $oConexion->f("fact_con_miva");
					$sin_iva 		= $oConexion->f("fact_sin_miva");
					$fact_tot_fact 	= $oConexion->f("fact_tot_fact");
					$correo 		= $oConexion->f("fact_email_clpv");
					$error 			= $oConexion->f("fact_erro_sri");
					$telefono 		= $oConexion->f("fact_tlf_cliente");
					$dire 			= $oConexion->f("fact_dir_clie");
					$fecha 			= $oConexion->f("fact_fech_fact");
					$nse_fact 		= $oConexion->f("fact_nse_fact");
					$cod_clpv 		= $oConexion->f("fact_cod_clpv");
					$fact_ice 		= $oConexion->f("fact_ice");
					$fact_val_irbp 	= $oConexion->f("fact_val_irbp");
					$clv_con_clpv 	= $oConexion->f("clv_con_clpv");
					$cod_almacen 	= $oConexion->f("fact_cm2_fact");
					$orden_compra 	= $oConexion->f("fact_opc_fact");
					$fact_cod_sucu 	= $oConexion->f("fact_cod_sucu");
					$fact_clav_sri 	= $oConexion->f("fact_clav_sri");
					$fact_cm1_fact 	= $oConexion->f("fact_cm1_fact");
					$fact_aprob_sri	= $oConexion->f("fact_aprob_sri");

					$total = $con_iva + $sin_iva + $iva;
					
					$fecha_clave = str_replace("/", "", $fecha);
					$fact_clav_sri = $fecha_clave.$secuencial.$ruc.$fact_cod_fact;;
					
					if ($clv_con_clpv == '01') {                  // RUC
						$tipoIdentificacionComprador = '04';
					} elseif ($clv_con_clpv == '02') {            // CEDULA
						$tipoIdentificacionComprador = '05';
					} elseif ($clv_con_clpv == '03') {            // PASAPORTE
						$tipoIdentificacionComprador = '06';
					} elseif ($clv_con_clpv == '07') {            // CONSUMIDOR FINAL
						$tipoIdentificacionComprador = '07';
					} elseif ($clv_con_clpv == '04') {            // EXTRANJERIA
						$tipoIdentificacionComprador = '08';
					}

					$array_fact [] = array( $fact_cod_fact, $secuencial, $ruc, $cliente, $iva, $con_iva, $sin_iva, $fact_tot_fact, $correo, $telefono,
											$dire, $fecha, $nse_fact, $cod_clpv, $fact_ice, $fact_val_irbp, $tipoIdentificacionComprador,
											$cod_almacen, $orden_compra, $fact_cod_sucu, $fact_cm1_fact, $fact_clav_sri);

				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		$xml = '';
		$nombre = '';
		if( count($array_fact) > 0 ){
			foreach ($array_fact as $val) {
				$id_factura = $val[0];	
				$xml 		= '';
				$xmlDeta 	= '';
				$descuento 	= 0;

				//COMSULTAMOS LOS DATOS Y FORMAMOS LOS XML
				$num8 				= 12345678;
				$fact_nom_cliente 	= $val[3];
				$fact_tlf_cliente 	= $val[9];
				$fact_dir_clie 		= $val[10];
				$fact_ruc_clie 		= $val[2];
				$fact_fech_fact 	= $val[11];
				$fact_num_preimp 	= $val[1];
				$fact_nse_fact 		= $val[12];
				$fact_cod_clpv 		= $val[13];
				$fact_con_miva 		= $val[5];
				$fact_sin_miva 		= $val[6];
				$fact_tot_fact 		= $val[7];
				$fact_iva 			= $val[4];
				$fact_ice 			= $val[14];
				$fact_val_irbp 		= $val[15];
				$fact_email_clpv 	= $val[8];
				$tipoIdentificacionComprador = $val[16];
				$cod_almacen 		= $val[17];
				$orden_compra 		= $val[18];
				$id_sucursal 		= $val[19];
				$fact_cm1_fact 		= $val[20];
				$clave_acceso 		= $val[21];
				$totalDescuento 	= 0;
				
				$baseImponibleIce 	= 0;
				$baseImponibleIceTotal = 0;
				
				//direccion sucursal
				$sql = "";
				
				//genera clave de acceso
				$ambiente = ambienteEmisionSri($oConexion, $id_empresa, $id_sucursal, 1);
				$tip_emis = ambienteEmisionSri($oConexion, $id_empresa, $id_sucursal, 2);
				
				//TIPO DOCUMENTO DEPENDE DE CANTIDAD DE CARACTERES
				$clv_con_clpv = strlen($fact_ruc_clie);
				//$oReturn->alert($clv_con_clpv);		
				if ($clv_con_clpv == 13)
					$tipoIdentificacionComprador = '04';
				else if ($clv_con_clpv == 10)
					$tipoIdentificacionComprador = '05';
				else
					$tipoIdentificacionComprador = '06';

		
				$totalSinImpuestos  = $fact_con_miva + $fact_sin_miva;
				$importeTotal 		= round($totalSinImpuestos + $fact_iva + $fact_ice + $fact_val_irbp,2);

				$estable = substr($fact_nse_fact, 0, 3);
				$serie   = substr($fact_nse_fact, 3, 6);

				$fec_fact = cambioFecha($fact_fech_fact, 'mm/dd/aaaa', 'dd/mm/aaaa');
				
				$sqlDetalle = "select * from saedfac where 
								dfac_cod_fact = $id_factura and 
								dfac_cod_sucu = $id_sucursal and 
								dfac_cod_empr = $id_empresa ";
				$item_num   = 1;
				$monto_iva  = 0;
				if ($oConexion->Query($sqlDetalle)) {
					$xmlDeta.='<dte:Items>';
					do {
						$dfac_cod_prod      = $oConexion->f("dfac_cod_prod");
						$dfac_cant_dfac     = $oConexion->f("dfac_cant_dfac");
						$dfac_precio_dfac   = $oConexion->f("dfac_precio_dfac");
						$dfac_mont_total    = $oConexion->f("dfac_mont_total");
						$dfac_por_iva       = $oConexion->f("dfac_por_iva");
						$dfac_por_irbp      = $oConexion->f("dfac_por_irbp");
						$dfac_des1          = $oConexion->f("dfac_des1_dfac");
						$dfac_des2          = $oConexion->f("dfac_des2_dfac");
						$dfac_des3          = $oConexion->f("dfac_des3_dfac");
						$dfac_des4          = $oConexion->f("dfac_des4_dfac");
						$dfac_des5          = $oConexion->f("dfac_por_dsg");
						$dfac_por_ice       = $oConexion->f("dfac_por_ice");
						$dfac_cod_unid      = $array_unid[$oConexion->f("dfac_cod_unid")];
						
						$descuento = 0;
						$desctoItem = 0;
						if ($dfac_des1 != 0 || $dfac_des2 != 0 || $dfac_des3 != 0 || $dfac_des4 != 0 || $dfac_des5 != 0) {
							$descuento = ( $dfac_cant_dfac * $dfac_precio_dfac ) - $dfac_mont_total;
							$desctoItem = ( $dfac_cant_dfac * $dfac_precio_dfac ) - $dfac_mont_total;
							if ($descuento != 0) {
								$totalDescuento += $descuento;
							}
						}// fin if

						$descuento = number_format($descuento, 2, '.', '');

						//PRODUCTO
						$sqlDescripcionProd = "select prod_nom_prod, prod_cod_barra, prod_sn_noi, prod_sn_exe ,prod_cod_tpro  from saeprod where 
													prod_cod_prod = '$dfac_cod_prod' and 
													prod_cod_empr = $id_empresa and 
													prod_cod_sucu = $id_sucursal ";
						if ($oConexion->Query($sqlDescripcionProd)) {
							if ($oConexion->NumFilas() > 0) {
								$prod_nom_prod 		= $oConexion->f('prod_nom_prod');
								$prod_cod_barra 	= $oConexion->f('prod_cod_barra');
								$prod_sn_noi 		= $oConexion->f('prod_sn_noi');
								$prod_sn_exe 		= $oConexion->f('prod_sn_exe');
								$prod_cod_tpro 		= $oConexion->f('prod_cod_tpro');
							}
						}
						$oConexion->Free();

						if($prod_cod_tpro=='1'){
							$prod_cod_tpro = 'S';
						}else{
							$prod_cod_tpro = 'B';
						}

						if ($dfac_por_iva == 0) {
							$monto_grav = 0;
							$monto_imp  = 0;
						} elseif($dfac_por_iva > 0 ) {
							$dfac_precio_dfac = round((($dfac_precio_dfac * $dfac_por_iva) / 100) + $dfac_precio_dfac,3);
							$monto_grav = 0;
							$monto_grav = $dfac_mont_total;
							$monto_imp  = 0;
							$monto_imp  = round((($monto_grav * $dfac_por_iva) / 100) ,2);
						}

						$monto = 0;
						$monto = ( $dfac_cant_dfac * round($dfac_precio_dfac, 4) ) - $descuento;

						$xmlDeta.= '<dte:Item NumeroLinea="'.$item_num.'" BienOServicio="'.$prod_cod_tpro.'">
										<dte:Cantidad>'.$dfac_cant_dfac.'</dte:Cantidad>
										<dte:UnidadMedida>'.$dfac_cod_unid.'</dte:UnidadMedida>
										<dte:Descripcion>'.$prod_nom_prod.'</dte:Descripcion>
										<dte:PrecioUnitario>'.round($dfac_precio_dfac, 4).'</dte:PrecioUnitario>
										<dte:Precio>'.$dfac_cant_dfac * round($dfac_precio_dfac, 4).'</dte:Precio>
										<dte:Descuento>'.round($descuento, 4).'</dte:Descuento>
										<dte:Impuestos>
											<dte:Impuesto>
												<dte:NombreCorto>IVA</dte:NombreCorto>
												<dte:CodigoUnidadGravable>1</dte:CodigoUnidadGravable>
												<dte:MontoGravable>'.$monto_grav.'</dte:MontoGravable>
												<dte:MontoImpuesto>'.$monto_imp.'</dte:MontoImpuesto>
											</dte:Impuesto>
										</dte:Impuestos>
										<dte:Total>'.$monto.'</dte:Total>
									</dte:Item>';                    
						$item_num ++;
					} while ($oConexion->SiguienteRegistro());
					$xmlDeta.='</dte:Items>';
				}// fin ifx
				$oConexion->Free();
				
				$xmlDeta .='<dte:Totales>
								<dte:TotalImpuestos>
									<dte:TotalImpuesto NombreCorto="IVA" TotalMontoImpuesto="'.$fact_iva.'" />
								</dte:TotalImpuestos>
								<dte:GranTotal>'.$importeTotal.'</dte:GranTotal>
							</dte:Totales>';

				///CABECERA DEL $XML
				unset($fecha_array);
				$fecha_array = explode('/',$fact_fech_fact);
				$m1 = $fecha_array[0];              $y1 = $fecha_array[2];             $d1 = $fecha_array[1];
				$fact_gtq = '';                
				$fact_gtq = date(DATE_ATOM, mktime(date("H"), date("i"), date("s"), $m1, $d1,  $y1 ) );


				$xml .= '<?xml version="1.0" encoding="UTF-8"?>
							<dte:GTDocumento xmlns:dte="http://www.sat.gob.gt/dte/fel/0.1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Version="0.4">
								<dte:SAT ClaseDocumento="dte">
									<dte:DTE ID="DatosCertificados">
										<dte:DatosEmision ID="DatosEmision">
											<dte:DatosGenerales Tipo="FACT" FechaHoraEmision="'.$fact_gtq.'" CodigoMoneda="GTQ" />
											<dte:Emisor NITEmisor="'.$ruc_empr.'" NombreEmisor="'.$nombre_empr.'" CodigoEstablecimiento="'.$empr_num_estab.'" NombreComercial="'.$nombre_empr.'" CorreoEmisor="" AfiliacionIVA="GEN">
												<dte:DireccionEmisor>
													<dte:Direccion>'.$dir_empr.'</dte:Direccion>
													<dte:CodigoPostal>'.$empr_cpo_empr.'</dte:CodigoPostal>
													<dte:Municipio>'.$empr_cod_prov.'</dte:Municipio>
													<dte:Departamento>'.$empr_cod_ciud.'</dte:Departamento>
													<dte:Pais>GT</dte:Pais>
												</dte:DireccionEmisor>
											</dte:Emisor>
											<dte:Receptor IDReceptor="'.$fact_ruc_clie.'" NombreReceptor="'.htmlspecialchars($fact_nom_cliente).'">
													<dte:DireccionReceptor>
														<dte:Direccion>'.htmlspecialchars($fact_dir_clie).'</dte:Direccion>
														<dte:CodigoPostal>00000</dte:CodigoPostal>
														<dte:Municipio>GUATEMALA</dte:Municipio>
														<dte:Departamento>GUATEMALA</dte:Departamento>
														<dte:Pais>GT</dte:Pais>
													</dte:DireccionReceptor>
											</dte:Receptor>
											<dte:Frases>
												<dte:Frase TipoFrase="1" CodigoEscenario="2" />
											</dte:Frases>';
				$xml .= $xmlDeta;	

				$xml .='                </dte:DatosEmision>
									</dte:DTE>
								</dte:SAT>
							</dte:GTDocumento>';

				if($empr_tip_firma == 'N'){					
					// firma Normal
					$serv = "C:/Jireh/";
					$ruta = $serv . "Comprobantes Electronicos";
					
					// CARPETA EMPRESA
					$ruta_gene = $ruta . "/generados";
					if (!file_exists($ruta))
						mkdir($ruta);

					if (!file_exists($ruta_gene))
						mkdir($ruta_gene);

					$nombre = $clave_acceso . ".xml";
					$archivo = fopen($ruta_gene . '/' . $nombre, "w+");

					//fwrite($archivo, $xml);
					fwrite($archivo, utf8_encode($xml));
					fclose($archivo);
					
				}elseif($empr_tip_firma == 'M'){					
					//MultiFirma	
					$nombre = $clave_acceso . ".xml";
					$serv = '';		
					$archivo = '';
					
					$serv = DIR_FACTELEC."modulos/sri_offline/documentoselectronicos/generados";					
					$archivo = fopen($serv . '/' . $nombre, "w+");

					fwrite($archivo, utf8_encode($xml));
					fclose($archivo);
				}				
				
			}// fin foreach
		}

		return $nombre;
	}
	
	function FirmarXmlFactura($oConexion, $id_empresa, $id_sucursal, $fact_cod_fact, $clpv_cod_clpv, $nombre_archivo, $user_sri , $doc ){
		$resp = '';
		try {		
			//tipo firma empresa
			$sqlEmpr = "select empr_tip_firma from saeempr where empr_cod_empr = $id_empresa";
			$empr_tip_firma = consulta_string_func($sqlEmpr, 'empr_tip_firma', $oConexion, 'N');
			
			if($empr_tip_firma == 'N'){			
			}elseif($empr_tip_firma == 'M'){			
				/* FIRMAMOS EL XML */	
				$sqlEmprToke = "select empr_nom_toke, empr_pass_toke, empr_ruc_empr from saeempr where empr_cod_empr = $id_empresa";
				if ($oConexion->Query($sqlEmprToke)) {
					$empr_nom_toke  = $oConexion->f("empr_nom_toke");
					$empr_pass_toke = $oConexion->f("empr_pass_toke");
					$empr_ruc_empr  = $oConexion->f("empr_ruc_empr");
				}
				$oConexion->Free();
				
				$rutaFirm = DIR_FACTELEC."modulos/sri_offline/documentoselectronicos/generados/". $nombre_archivo;     
				$xml = file_get_contents($rutaFirm);
				$data = base64_encode($xml); 
	
				$web_services = 'http://portal.cofidiguatemala.com/webservicefrontfeltest/factwsfront.asmx?WSDL';
				$request = new HTTP_Request2();
				$request->setUrl($web_services);
				$request->setMethod(HTTP_Request2::METHOD_POST);
				$request->setConfig(array('follow_redirects' => TRUE ));
				$request->setHeader(array('Content-Type' => 'text/xml'));
				$request->setBody('<?xml version="1.0" encoding="UTF-8"?>
										<SOAP-ENV:Envelope
											xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
											xmlns:ws="http://www.fact.com.mx/schema/ws">
													<SOAP-ENV:Header />
														<SOAP-ENV:Body>
															<ws:RequestTransaction>                                  
															<ws:Requestor>039E50E0-3097-4DDF-9E28-9CBF79BAB9FF</ws:Requestor>
															<ws:Transaction>SYSTEM_REQUEST</ws:Transaction>
															<ws:Country>GT</ws:Country>
															<ws:Entity>'.$empr_ruc_empr.'</ws:Entity>
															<ws:User>039E50E0-3097-4DDF-9E28-9CBF79BAB9FF</ws:User>
															<ws:UserName>ADMINISTRADOR</ws:UserName>
															<ws:Data1>POST_DOCUMENT_SAT</ws:Data1>
															<ws:Data2>'.$data.'</ws:Data2>
															<ws:Data3></ws:Data3>
															</ws:RequestTransaction>
														</SOAP-ENV:Body>
													</SOAP-ENV:Envelope>');
				try {
					$response = $request->send();
					if ($response->getStatus() == 200) {
						$reporte_xml = '';
						$reporte_xml = $response->getBody();
						$rutaFirm = DIR_FACTELEC."modulos/sri_offline/documentoselectronicos/respuesta";                    
	
						// ruta del xml
						$archivo_xml = fopen($rutaFirm . '/' . $nombre_archivo, "w+");
						fwrite($archivo_xml, $reporte_xml);
						fclose($archivo_xml);
	
						// RESPUESTA DEL SOAP
						$xml= new DomDocument();
						$xml->load(DIR_FACTELEC."modulos/sri_offline/documentoselectronicos/respuesta/".$nombre_archivo);
						$tag= $xml->getElementsByTagName( "Response" );
						foreach( $tag as $val ){
							$batch    = $val->getElementsByTagName("Batch")->item(0)->nodeValue;
							$serial_gt= $val->getElementsByTagName("Serial")->item(0)->nodeValue;
							$docguid  = $val->getElementsByTagName("DocumentGUID")->item(0)->nodeValue;
							$suggeste = $val->getElementsByTagName("SuggestedFileName")->item(0)->nodeValue;
							$result   = $val->getElementsByTagName("Result")->item(0)->nodeValue;
							$tiempo   = $val->getElementsByTagName("TimeStamp")->item(0)->nodeValue;
							$descrip  = $val->getElementsByTagName("Description")->item(0)->nodeValue;
							$hint     = $val->getElementsByTagName("Hint")->item(0)->nodeValue;
							$proces   = $val->getElementsByTagName("Processor")->item(0)->nodeValue;
						}
	
						$tag_resp = $xml->getElementsByTagName( "ResponseData" );
						foreach( $tag_resp as $val ){
							$respdata = $val->getElementsByTagName("ResponseData1")->item(0)->nodeValue;
						}
	
						$respuesta  = '';
						$respuesta .= 'Batch: '. $batch.PHP_EOL;
						$respuesta .= 'Serial: '. $serial_gt.PHP_EOL;
						$respuesta .= 'DocumentGUID: '. $docguid.PHP_EOL;
						$respuesta .= 'SuggestedFileName: '. $suggeste.PHP_EOL;
						$respuesta .= 'Resultado: '. $result.PHP_EOL;
						$respuesta .= 'Tiempo: '. $tiempo.PHP_EOL;
						$respuesta .= 'Description: '. $descrip.PHP_EOL;
						$respuesta .= 'Hint: '. $hint.PHP_EOL;
						$respuesta .= 'Procesado: '. $proces.PHP_EOL;

						if( !empty($batch) ){
							$reporte_xml = '';
							$rutaFirm    = DIR_FACTELEC."modulos/sri_offline/documentoselectronicos/firmados";
							$reporte_xml = base64_decode($respdata);
	
							 // ruta del xml
							$archivo_xml = fopen($rutaFirm . '/' . $nombre_archivo, "w+");
							fwrite($archivo_xml, $reporte_xml);
							fclose($archivo_xml);
							
							/*$oReturn->script("Swal.fire({
													title: '<h3><strong>Autorizacion: $docguid</strong></h3>',
													width: 800,
													type: 'success',   
													timer: 2000   ,
													showConfirmButton: false
													})");*/
							$resp = 'AUTORIZADO';

							$num_fiscal = '';   $serie_fiscal = '';  $auto_fiscal = '';
							$num_fiscal   = $serial_gt;
							$serie_fiscal = substr($docguid, 0,8);
							$auto_fiscal  = $docguid;
	
							//$sqlUpdateComp = updateComprobanteFirmado($clave_acceso, $doc, $auto_fiscal, $tiempo, $sql_tmp, $clpv, $num_fact, $ejer, $asto, $fec_emis, $sucu, 'F', $serie_fiscal, $num_fiscal);
							//$oConexion->QueryT($sqlUpdateComp);
				
							switch ($doc) {
								case '1':
									$sqlUpdateComp = "update saefact set 
														fact_aprob_sri 		= 'S',
														fact_auto_sri  		= '$auto_fiscal',
														fact_user_sri  		= $user_sri,
														fact_fech_sri  		= '$tiempo' ,
														fact_serie_fiscal 	= '$serie_fiscal'  ,
														fact_num_fiscal   	= '$num_fiscal'   , 
														fact_clav_sri  		= '$claveAcceso' where
														fact_cod_empr       = $id_empresa and
														fact_cod_sucu       = $id_sucursal and
														fact_cod_fact       = $fact_cod_fact and
														fact_cod_clpv       = $clpv_cod_clpv";
									$oConexion->QueryT($sqlUpdateComp);

									$_SESSION['pdf'] = reporte_factura($fact_cod_fact, $clave_acceso, $id_sucursal, $rutaPdf);
	
									$sql = "select fact_nom_cliente, fact_email_clpv from saefact where 
													fact_cod_fact = $fact_cod_fact and fact_cod_empr = $id_empresa ";
									if ($oConexion->Query($sql)) {
										$correo   = $oConexion->f("fact_email_clpv");
										$nom_clpv = $oConexion->f("fact_nom_cliente");
									}
									$oConexion->Free();
									
								break;
								case '2':
									$_SESSION['pdf'] = reporte_notaDebito($serial, $clave_acceso, $rutaPdf);
								break;
								case '3':
									$_SESSION['pdf'] = reporte_notaCredito($serial, $clave_acceso, $id_sucursal, $rutaPdf);
									$sqlUpdateComp = "update saencre set 
															ncre_aprob_sri 		= 'S',
															ncre_auto_sri 		= '$auto_fiscal',
															ncre_user_sri 		= $user_sri,
															ncre_fech_sri 		= '$tiempo',
															ncre_serie_fiscal  	= '$serie_fiscal'  ,
															ncre_num_fiscal    	= '$num_fiscal'   , 
															ncre_clav_sri  		= '$claveAcceso' where
															ncre_cod_empr       = $id_empresa and
															ncre_cod_sucu       = $id_sucursal and
															ncre_cod_clpv       = $clpv_cod_clpv and
															ncre_cod_ncre       = $fact_cod_fact ";
									$oConexion->QueryT($sqlUpdateComp);
								break;
								case '4':
									$_SESSION['pdf'] = reporte_guiaRemision($serial, $clave_acceso, $id_sucursal, $rutaPdf);
									break;
								case '5':
									$_SESSION['pdf'] = reporte_retencionGasto($sqlUpdate, $clave_acceso, $rutaPdf, $clpv, $num_fact, $ejer, $asto, $fec_emis, $idSucursal);
									break;
								case '6':
									$_SESSION['pdf'] = reporte_retencionInve($sqlUpdate, $clave_acceso, $rutaPdf, $clpv, $num_fact, $ejer, $asto, $fec_emis, $idSucursal);
									break;
								case '7':
									$_SESSION['pdf'] = reporte_factura_export($serial, $clave_acceso, $rutaPdf);
									break;
								case '8':
									$_SESSION['pdf'] = reporte_factura_flor($serial, $clave_acceso, $rutaPdf);
									break;
								case '9':
									$_SESSION['pdf'] = reporte_factura_flor_export($serial, $clave_acceso, $rutaPdf);
									break;
								case '10':
									$_SESSION['pdf'] = reporte_guiaRemisionFlor($serial, $clave_acceso, $rutaPdf);
									break;
								case '11':
									$_SESSION['pdf'] = reporte_liqu_compras($serial, $clave_acceso, $rutaPdf);
									break;                
							}
				
							if($empr_tip_firma == 'N'){
								$rutaDia = "C:/Jireh/Comprobantes Electronicos/firmados";
							}elseif($empr_tip_firma == 'M'){
								$rutaDia = DIR_FACTELEC."modulos/sri_offline/documentoselectronicos/firmados";
							}
	
							$nombre = $clave_acceso . ".xml";
							$ride = '' . $rutaDia . '/' . $nombre;
							
							$numeDocu = substr($clave_acceso, 24, 15);
							
							$correoMsj = envio_correo_adj($correo, $ride, $rutaPdf, $nom_clpv, $clave_acceso, $numeDocu, $clpv_cod_clpv, 16, $id_sucursal);							
				
						}else{
							//$oReturn->alert($descrip);
							$resp = $descrip;
							/*$oReturn->script("Swal.fire({
													title: '<h3><strong>$descrip</strong></h3>',
													width: 950,
													type: 'error',   
													timer: 5000   ,
													showConfirmButton: false
													})");*/
						}                    
	
					}else {
						$resp = 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .$response->getReasonPhrase();
					}
				}catch(HTTP_Request2_Exception $e) {
					$resp = $e->getMessage();
				}
	
			}
			
		} catch (SoapFault $e) {
			$resp = 'NO HUBO CONECCION CON LA FIRMA';
		}

		return $resp;
	}
	
	
}
?>