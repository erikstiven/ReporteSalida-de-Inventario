<?php
require_once(path(DIR_INCLUDE).'comun.lib.php');

class Contratos{
	
	var $oConexion; 
	
	function consultarContrato($oConexion, $idEmpresa, $idSucursal, $idContrato){
		
		$sql = "select * from contrato_clpv 
				where id_empresa = $idEmpresa and
				id_sucursal = $idSucursal and
				id = $idContrato";
		if ($oConexion->Query($sql)) {
			unset($array);
			if ($oConexion->NumFilas() > 0) {
				$id = $oConexion->f('id');
				$id_empresa = $oConexion->f('id_empresa');
				$id_sucursal = $oConexion->f('id_sucursal');
				$id_clpv = $oConexion->f('id_clpv');
				$id_ciudad = $oConexion->f('id_ciudad');
				$codigo = $oConexion->f('codigo');
				$nom_clpv = $oConexion->f('nom_clpv');
				$ruc_clpv = $oConexion->f('ruc_clpv');
				$fecha_contrato = $oConexion->f('fecha_contrato');
				$fecha_firma = $oConexion->f('fecha_firma');
				$fecha_corte = $oConexion->f('fecha_corte');
				$fecha_cobro = $oConexion->f('fecha_cobro');
				$duracion = $oConexion->f('duracion');
				$penalidad = $oConexion->f('penalidad');
				$estado = $oConexion->f('estado');
				$vendedor = $oConexion->f('vendedor');
				$user_web = $oConexion->f('user_web');
				$fecha_server = $oConexion->f('fecha_server');
				$tarifa = $oConexion->f('tarifa');
				$id_dire = $oConexion->f('id_dire');
			}
		}
		$oConexion->Free();
		
		$array[] = array($id, $id_empresa, $id_sucursal, $id_clpv, $id_ciudad, $codigo, $nom_clpv, $ruc_clpv, $fecha_contrato,
						$fecha_firma, $fecha_corte, $fecha_cobro, $duracion, $penalidad, $estado, $vendedor, $user_web,
						$fecha_server, $tarifa, $id_dire);

		
		return $array;
	}
	
	function consultarTablaPagosContrato($oConexion, $idContrato, $idClpv){
		
		$sql = "select * from contrato_pago
				where id_contrato = $idContrato and
				id_clpv = $idClpv";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				unset($array);
				do{
					$id = $oConexion->f('id');
					$id_contrato = $oConexion->f('id_contrato');
					$id_clpv = $oConexion->f('id_clpv');
					$id_pago = $oConexion->f('id_pago');
					$fecha = $oConexion->f('fecha');
					$mes = $oConexion->f('mes');
					$anio = $oConexion->f('anio');
					$secuencial = $oConexion->f('secuencial');
					$estado = $oConexion->f('estado');
					$estado_fact = $oConexion->f('estado_fact');
					$id_factura = $oConexion->f('id_factura');
					
					$array[] = array($id, $id_pago, $fecha, $mes, $anio, $secuencial, $estado, $estado_fact, $id_factura);
						
				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		return $array;
	}
	
	function consultaCuotasFacturadas($oConexionI, $idEmpresa, $idContrato, $idClpv, $anio, $rango){
		
		//lectura sucia
		
		$sqlNcre = "select ncre_cod_clpv, ncre_cod_fact, count(*) as contador
					from saencre, saedncr, saeprod
					where
					ncre_cod_ncre = dncr_cod_ncre and
					ncre_cod_empr = dncr_cod_empr and
					ncre_cod_sucu = dncr_cod_sucu and
					dncr_cod_prod = prod_cod_prod and
					dncr_cod_empr = prod_cod_empr and
					dncr_cod_sucu = prod_cod_sucu and
					prod_cod_linp = 4 and
					ncre_est_fact != 'AN' and
					ncre_cod_empr = $idEmpresa and
					year(ncre_fech_fact) = $anio and
					ncre_cod_clpv = $idClpv and
					dncr_cod_prod not in ('103', '402')
					group by 1,2";
		//$oReturn->alert($sqlNcre);
		if($oConexionI->Query($sqlNcre)){
			if($oConexionI->NumFilas() > 0){
				unset($arrayControlMesNotaCredito);
				do{
					$arrayControlMesNotaCredito[$oConexionI->f('ncre_cod_clpv')][$oConexionI->f('ncre_cod_fact')] = $oConexionI->f('contador');
				}while($oConexionI->SiguienteRegistro());
			}
		}
		$oConexionI->Free();
		
		//numero de facturas
		$sqlFact = "select fact_cod_clpv, fact_cod_fact
					from saefact, saedfac
					where fact_cod_empr = dfac_cod_empr and
					fact_cod_sucu = dfac_cod_sucu and
					fact_cod_fact = dfac_cod_fact and
					fact_est_fact != 'AN' and
					fact_cod_empr = $idEmpresa and
					year(fact_fech_fact) = $anio and
					dfac_cod_linp = 4 and
					dfac_cod_prod not in ('103', '402') and
					fact_cod_clpv = $idClpv and
					fact_cod_contr = $idContrato
					group by 1,2
					order by 2";
		//$oReturn->alert($sqlFact);
		if($oConexionI->Query($sqlFact)){
			if($oConexionI->NumFilas() > 0){
				unset($arrayControlFacturacion);
				unset($arrayIFacturacion);
				unset($arrayIClpv);
				$totalMesAdeuda = 0;
				do{
					$fact_cod_clpv = $oConexionI->f('fact_cod_clpv');
					$fact_cod_fact = $oConexionI->f('fact_cod_fact');
				
					$notasCredito = 0;
					$notasCredito = $arrayControlMesNotaCredito[$fact_cod_clpv][$fact_cod_fact];
					
					if(empty($notasCredito)){
						$notasCredito = 0;
					}
					
					if($notasCredito == 0){
						$totalMesAdeuda++;
					}elseif($notasCredito > 0){
						$totalMesAdeuda--;
					}
						
				}while($oConexionI->SiguienteRegistro());
			}
		}
		$oConexionI->Free();
		
		return $totalMesAdeuda;
		
	}
	
	function consultaMontoMesAdeuda($oConexion, $oConexionI, $idContrato, $idClpv, $fechaCorte){
		
		//lectura sucia
		
		$sql = "select count(*) as cuotas 
				from contrato_pago 
				where id_contrato = $idContrato and
				id_clpv = $idClpv and
				(estado_fact is null OR estado_fact != 'GR') and
				fecha < '$fechaCorte'";
		$cuotas = consulta_string_func($sql, 'cuotas', $oConexion, 0);
		
		$valor = 0;
		$valor_1 = 0;
		$totalDeuda = 0;
		$totalDeudaVal = 0;
		if($cuotas > 0){
			$sql = "select sum(clse_pre_clse) as valor, sum(clse_tot_add) as valor_1
					from saeclse
					where clse_cod_clpv = $idClpv and
					clse_cod_contr = $idContrato and
					clse_cobr_sn = 'S'";
			if ($oConexionI->Query($sql)) {
				if ($oConexionI->NumFilas() > 0) {
					$valor = $oConexionI->f('valor');
					$valor_1 = $oConexionI->f('valor_1');
				}
			}
			$oConexionI->Free();
			
			$totalDeudaVal = round($valor + $valor_1, 2);
			$totalDeuda = round($totalDeudaVal * $cuotas, 2);
		}
		
		return $totalDeuda;
		
	}
	
	function consultaTelefonos($oConexion, $idContrato, $idClpv){
		
		$telefono = '';
		
		$sql = "select tlcp_tip_ticp, tlcp_tlf_tlcp
				from saetlcp
				where tlcp_cod_clpv = $idClpv and
				tlcp_cod_contr = $idContrato";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				do{
					$telefono .= $oConexion->f('tlcp_tip_ticp').' - '.$oConexion->f('tlcp_tlf_tlcp').' ';
				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		return $telefono;
		
	}
	
	function consultaEmail($oConexion, $idContrato, $idClpv){
		
		$email = '';
		
		$sql = "select emai_ema_emai
				from saeemai
				where emai_cod_clpv = $idClpv and
				emai_cod_contr = $idContrato";
		if ($oConexion->Query($sql)) {
			if ($oConexion->NumFilas() > 0) {
				do{
					$email .= $oConexion->f('emai_ema_emai').' ';
				}while($oConexion->SiguienteRegistro());
			}
		}
		$oConexion->Free();
		
		return $email;
		
	}
	
	function sumaTotalesServicio($oConexion, $oConexionI, $idContrato, $idClpv){
		
		$sql = "select sum(clse_pre_clse) as valor
				from saeclse 
				where clse_cod_contr = $idContrato and
				clse_cod_clpv = $idClpv and
				clse_cobr_sn = 'S'";
		$valor = round(consulta_string_func($sql, 'valor', $oConexionI, 0),2);
		
		//update precio
		$sqlUpdate = "update contrato_clpv set tarifa = '$valor' where id = $idContrato and id_clpv = $idClpv";
		$oConexion->QueryT($sqlUpdate);
		
		return 'OK';
		
	}
	
	function detalleContrato($oCon, $oConA, $oIfx, $id, $id_clpv){

		session_start();

		//variables de sesion
		$idempresa = $_SESSION['U_EMPRESA'];
		$idsucursal = $_SESSION['U_SUCURSAL'];
		

		//lectura sucia
		
		//cliente 
		$sql = "select clpv_ruc_clpv, clpv_nom_clpv 
				from saeclpv 
				where clpv_cod_empr = $idempresa and 
				clpv_cod_clpv = $id_clpv";
		if($oIfx->Query($sql)){
			if($oIfx->NumFilas() > 0){
				$clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
				$clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
			}
		}
		$oIfx->Free();
		
		//datos del contrato
		$sql = "select codigo, id_dire, tarifa 
				from contrato_clpv 
				where id = $id and 
				id_clpv = $id_clpv and 
				id_empresa = $idempresa";
		if($oCon->Query($sql)){
			if($oCon->NumFilas() > 0){
				$codigo = $oCon->f('codigo');
				$id_dire = $oCon->f('id_dire');
				$tarifa = $oCon->f('tarifa');
			}
		}
		$oCon->Free();
		
		if(empty($id_dire)){
			$id_dire = 0;
		}
		
		//direccion
		$sql = "select dire_dir_dire, dire_cod_sect, dire_barr_dire, dire_refe_dire 
				from saedire 
				where dire_cod_empr = $idempresa and 
				dire_cod_clpv = $id_clpv and 
				dire_cod_dire = $id_dire";
		if($oIfx->Query($sql)){
			if($oIfx->NumFilas() > 0){
				$dire_dir_dire = $oIfx->f('dire_dir_dire');
				$dire_cod_sect = $oIfx->f('dire_cod_sect');
				$dire_barr_dire = $oIfx->f('dire_barr_dire');
				$dire_refe_dire = $oIfx->f('dire_refe_dire');
			}
		}
		$oIfx->Free();
		
		//sector
		if(!empty($dire_cod_sect)){
			$sql = "select sector from sector_direccion where id = $dire_cod_sect";
			$sector = consulta_string_func($sql, 'sector', $oCon, 0);
		}
		
		//barrio
		if(!empty($dire_barr_dire)){
			$sql = "select barrio from int_barrio where id = $dire_cod_sect";
			$barrio = consulta_string_func($sql, 'barrio', $oCon, 0);
		}
		
		$sHtml .= '<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title" id="myModalLabel">Contrato Cliente</h4>
						</div>
						<div class="modal-body">';
		
		$sHtml .= '<table class="table table-condensed table-striped" style="width: 100%; margin: 0px;">';
		
		//deuda
		$fechaHoy = date("Y-m-d");
		$classContratos = new Contratos();
		$valor = $classContratos->consultaMontoMesAdeuda($oCon, $oIfx, $id, $id_clpv, $fechaHoy);
		$contratoTelefonos = $classContratos->consultaTelefonos($oIfx, $id, $id_clpv);
		$contratoEmail = $classContratos->consultaEmail($oIfx, $id, $id_clpv);
		
		$sHtml .= '<tr>';
		$sHtml .= '<td colspan="3"></td>'; 
		$sHtml .= '<td colspan="1" align="right"><span class="fecha_letra">Adeuda:</span> <a href="#" style="font-size: 20px; color: red;">'.number_format($valor, 2, '.', '').'</td></td>';
		$sHtml .= '</tr>'; 
		
		$sHtml .= '<tr>';
		$sHtml .= '<td class="fecha_letra">Contrato:</td>'; 		
		$sHtml .= '<td>'.$codigo.'</td>';
		$sHtml .= '<td class="fecha_letra">Tarifa:</td>'; 		
		$sHtml .= '<td align="left"><a href="#">'.number_format($tarifa, 2, '.', ',').'</a></td>';
		$sHtml .= '</tr>'; 
		$sHtml .= '<tr>';
		$sHtml .= '<td class="fecha_letra">Identificacion:</td>'; 		
		$sHtml .= '<td>'.$clpv_ruc_clpv.'</td>';
		$sHtml .= '<td class="fecha_letra">Nombre:</td>'; 		
		$sHtml .= '<td>'.$clpv_nom_clpv.'</td>';
		$sHtml .= '</tr>'; 
		$sHtml .= '<tr>';
		$sHtml .= '<td class="fecha_letra">Sector:</td>'; 		
		$sHtml .= '<td>'.$sector.'</td>';
		$sHtml .= '<td class="fecha_letra">Barrio:</td>'; 		
		$sHtml .= '<td>'.$barrio.'</td>';
		$sHtml .= '</tr>';
		$sHtml .= '<tr>';
		$sHtml .= '<td class="fecha_letra">Direccion:</td>'; 		
		$sHtml .= '<td colspan="3">'.$dire_dir_dire.'</td>';
		$sHtml .= '</tr>';
		$sHtml .= '<tr>';
		$sHtml .= '<td class="fecha_letra">Referencia:</td>'; 		
		$sHtml .= '<td colspan="3">'.$dire_refe_dire.'</td>';
		$sHtml .= '</tr>';
		$sHtml .= '<tr>';
		$sHtml .= '<td class="fecha_letra">Telefono:</td>'; 		
		$sHtml .= '<td colspan="3">'.$contratoTelefonos.'</td>';
		$sHtml .= '</tr>';
		$sHtml .= '<tr>';
		$sHtml .= '<td class="fecha_letra">E-mail:</td>'; 		
		$sHtml .= '<td colspan="3">'.$contratoEmail.'</td>';
		$sHtml .= '</tr>';
 
		//servicios
		$sql = "select clse_cod_prod, clse_nom_prod, clse_cobr_sn,
				clse_pre_clse, clse_cant_add, clse_tot_add
				from saeclse
				where clse_cod_clpv = $id_clpv and
				clse_cod_empr = $idempresa and
				clse_cod_contr = $id";
		if($oIfx->Query($sql)){
			if($oIfx->NumFilas() > 0){
				$sHtml .= '<tr>';
				$sHtml .= '<td colspan="4">'; 
				$sHtml .= '<table class="table table-condensed table-striped table-bordered" style="width: 100%; margin:0px;">';
				$sHtml .= '<tr>';
				$sHtml .= '<td>SERVICIO</td>';
				$sHtml .= '<td>PRECIO</td>';
				$sHtml .= '<td>COBRA</td>';
				$sHtml .= '<td>CANT AAD</td>';
				$sHtml .= '<td>VAL ADD</td>';
				$sHtml .= '</tr>';
				$totalPrecio = 0;
				do{
					$clse_cod_prod = $oIfx->f('clse_cod_prod');
					$clse_nom_prod = $oIfx->f('clse_nom_prod');
					$clse_pre_clse = $oIfx->f('clse_pre_clse');
					$clse_cobr_sn = $oIfx->f('clse_cobr_sn');
					$clse_cant_add = $oIfx->f('clse_cant_add');
					$clse_tot_add = $oIfx->f('clse_tot_add');
					
					if($clse_cobr_sn == 'S'){
						$colorProd = 'bg-success';
					}elseif($clse_cobr_sn == 'N'){
						$colorProd = 'bg-danger';
					}
					
					$sHtml .= '<tr>';
					$sHtml .= '<td>'.$clse_nom_prod.'</td>';
					$sHtml .= '<td align="right">'.$clse_pre_clse.'</td>';
					$sHtml .= '<td align="center" class="'.$colorProd.'">'.$clse_cobr_sn.'</td>';
					$sHtml .= '<td align="right">'.$clse_cant_add.'</td>';
					$sHtml .= '<td align="right">'.$clse_tot_add.'</td>';
					$sHtml .= '</tr>';
					
					$totalPrecio += round($clse_pre_clse + $clse_tot_add, 2);
				}while($oIfx->SiguienteRegistro());
				$sHtml .= '</table>'; 
				$sHtml .= '</td>'; 
				$sHtml .= '</tr>'; 
			}
		}
		$oIfx->Free();
		
		$sHtml .= '</table>'; 
		
							
		$sHtml .= '<table class="table table-condensed table-striped table-bordered table-hover" style="width: 100%; margin: 0px;">';
		$sHtml .= '<tr>';
		$sHtml .= '<td colspan="10" class="bg-primary">ESTADO DE CUENTA</td>';
		$sHtml .= '</tr>'; 

		//query contrato
		$sql = "select id, fecha, secuencial, estado, dias,
				estado_fact, id_factura, estado
				from contrato_pago
				where id_clpv = $id_clpv and
				id_contrato = $id";
		//$oReturn->alert($sql);
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				$sHtml .= '<tr>';
				$sHtml .= '<td>No.</td>';
				$sHtml .= '<td>FECHA MENSUALIDAD</td>';
				$sHtml .= '<td>DIAS MES</td>';
				$sHtml .= '<td>CORTE</td>';
				$sHtml .= '<td>DIAS USO</td>';
				$sHtml .= '<td>VALOR</td>';
				$sHtml .= '<td>COBRO</td>';
				$sHtml .= '<td>FACTURA</td>';
				$sHtml .= '<td>SALDO</td>';
				$sHtml .= '</tr>';
				do{
					$idDet = $oCon->f('id');
					$fechaPago = $oCon->f('fecha');
					$secuencial = $oCon->f('secuencial');
					$estado = $oCon->f('estado');
					$dias = $oCon->f('dias');
					$estado_fact = $oCon->f('estado_fact');
					$id_factura = $oCon->f('id_factura');
					$estado = $oCon->f('estado');

					$eventoFact = '';
					$totalFactura = 0;
					if($estado_fact != 'GR'){
						$imgFact = 'PE';
					}elseif($estado_fact == 'GR'){
						
						$sqlFact = "select sum(fact_tot_fact + fact_iva + fact_ice - fact_dsg_valo) as total
									from saefact 
									where fact_cod_empr = $idempresa and
									fact_cod_contr = $id and
									fact_cod_fact = $id_factura and
									fact_est_fact != 'AN'";
						$totalFactura = consulta_string_func($sqlFact, 'total', $oIfx, 0);
						
						if($totalFactura > 0){
							$eventoFact = 'onclick="verFacturas('.$id_clpv.', '.$id.');"';
						}
					}

					$eventoCobro = '';
					$totalCobro = 0;
					if($estado != 'GR'){
						$sql = "select sum(valor) as valor
								from cobros_clpv
								where id_empresa = $idempresa and
								id_clpv = $id_clpv and
								id_contrato = $id and
								estado != 'AN' and
								id_fpago = $idDet";
						$totalCobro = consulta_string_func($sql, 'valor', $oConA, 0);
						
						if($totalCobro > 0){
							$eventoCobro = 'onclick="verPago('.$idDet.');"';
						}
					}
					
					$saldoMes = round($totalPrecio - $totalFactura, 2);
					
					$sHtml .= '<tr>';
					$sHtml .= '<td>'.$secuencial.'</td>';
					$sHtml .= '<td>'.($fechaPago).'</td>';
					$sHtml .= '<td align="right">'.$dias.'</td>';
					$sHtml .= '<td align="center"></td>';
					$sHtml .= '<td align="center"></td>';
					$sHtml .= '<td align="right">'.number_format($totalPrecio,2,'.',',').'</td>';
					$sHtml .= '<td align="right"><a href="#" '.$eventoCobro.'>'.number_format($totalCobro,2,'.',',').'</a></td>';
					$sHtml .= '<td align="right"><a href="#" '.$eventoFact.'>'.number_format($totalFactura,2,'.',',').'</a></td>';
					$sHtml .= '<td align="right">'.number_format($saldoMes,2,'.',',').'</td>';
					$sHtml .= '</tr>';
				}while($oCon->SiguienteRegistro());
				
			} 
		}
		$oCon->Free();
		$sHtml .= '</table>';

		$sHtml .= '</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
						</div>
					</div>
				</div>';
						
		return $sHtml;
	}
	
	function contratosParaCorteServicio($oCon, $oConA, $oIfx, $idEmpresa, $idSucursal, $idBarrio, $fechaCorte, $codigo, $cliente){

		session_start();

		//variables de sesion
		$idempresa = $_SESSION['U_EMPRESA'];
		$idsucursal = $_SESSION['U_SUCURSAL'];
		

		//lectura sucia
		
		$sHtml .= '<table class="table table-striped table-condensed table-bordered table-hover" style="width: 100%; margin-top: 20px;">';
		$sHtml .= '<tr>';
		$sHtml .= '<td colspan="7" class="bg-primary">REPORTE DE CONTRATOS</td>';
		$sHtml .= '</tr>';
        $sHtml .= '<tr>';
        $sHtml .= '<td>Contrato</td>';
        $sHtml .= '<td>Cliente</td>';
		$sHtml .= '<td>Direccion</td>';
		$sHtml .= '<td>Tarifa</td>';
		$sHtml .= '<td>Adeuda</td>';
		$sHtml .= '<td>Detalle</td>';
        $sHtml .= '<td align="center"><input type="checkbox" onclick="marcar(this);"></td>';
        $sHtml .= '</tr>';
		
		$sqlTmpBarrio = " ";
		if(!empty($idBarrio)){
			$sqlTmpBarrio = "and c.id_barrio = $idBarrio";
		}
		
		$sqlTmpCodigo = " ";
		if(!empty($codigo)){
			$sqlTmpCodigo = "and c.codigo = '$codigo'";
		}
		
		$sqlTmpClpv = " ";
		if(!empty($cliente)){
			$sqlTmpClpv = "and (c.nom_clpv like upper('%$cliente%') OR c.ruc_clpv like ('%$cliente%'))";
		}
		
		$sql = "select c.id, c.codigo, c.id_clpv, c.nom_clpv, c.direccion, c.tarifa
				from contrato_clpv c, contrato_pago p
				where c.id = p.id_contrato and
				c.id_clpv = p.id_clpv and
				p.estado_fact is null and
				day(p.fecha) > c.fecha_corte and
				p.fecha < '$fechaCorte' and
				--c.estado = 'AP' and
				c.id_empresa = $idEmpresa and
				c.id_sucursal = $idSucursal 
				$sqlTmpBarrio
				$sqlTmpCodigo
				$sqlTmpClpv
				group by 1,2,3,4,5,6
				limit 5";
		if($oCon->Query($sql)){
			if($oCon->NumFilas() > 0){
			  unset($array);
			  $totalAdeuda = 0;
				do{
					$id = $oCon->f('id');
                    $id_clpv = $oCon->f('id_clpv');
                    $clpv_ruc_clpv = $oCon->f('ruc_clpv');
                    $clpv_nom_clpv = utf8_encode($oCon->f('nom_clpv'));
                    $codigo = $oCon->f('codigo');
					$tarifa = $oCon->f('tarifa');
					$direccion = $oCon->f('direccion');
					$referencia = $oCon->f('referencia');
					$id_sector = $oCon->f('id_sector');
										
					$valorDeuda = $this->consultaMontoMesAdeuda($oConA, $oIfx, $id, $id_clpv, $fechaCorte);

                    $array[] = array($id_clpv, $id);

                    $sHtml .= '<tr>';
                    $sHtml .= '<td>'.$codigo.'</td>';
                    $sHtml .= '<td>'.$clpv_nom_clpv.'</td>';
					$sHtml .= '<td>'.$direccion.'</td>';
					$sHtml .= '<td align="right">'.number_format($tarifa, 2,'.',',').'</td>';
					$sHtml .= '<td align="right">'.number_format($valorDeuda, 2,'.',',').'</td>';
					$sHtml .= '<td align="center">
                                <div class="btn btn-info btn-sm" onclick="estadoCuentaContrato('.$id.', '.$id_clpv.');">
									<span class="glyphicon glyphicon-list-alt"></span>
								</div>
                            </td>';
                    $sHtml .= '<td align="center">
                                <input type="checkbox" name="check_'.$id.'" value="S"/>
                            </td>';
                    $sHtml .= '</tr>';
					$totalAdeuda += $valorDeuda;
				}while($oCon->SiguienteRegistro());
				$sHtml .= '<tr>';
				$sHtml .= '<td class="bg-danger fecha_letra" colspan="4" align="right">Total:</td>'; 		
				$sHtml .= '<td align="right" class="bg-danger fecha_letra">'.number_format($totalAdeuda, 2,'.',',').'</td>';	
				$sHtml .= '<td class="bg-danger fecha_letra" colspan="2"></td>'; 				
				$sHtml .= '</tr>'; 
			}
		}
		$oCon->Free();
		
		$_SESSION['ARRAY_CHECK_CORTE_CLPV'] = $array;
		
		$sHtml .= '</table>'; 
		
							
		return $sHtml;
	}
	
	function contratosConCorteServicio($oCon, $oConA, $oIfx, $idEmpresa, $idSucursal, $idBarrio, $fechaCorte){

		session_start();

		//lectura sucia
		
		$sHtml .= '<table class="table table-striped table-condensed table-bordered table-hover" style="width: 100%; margin-top: 20px;">';
		$sHtml .= '<tr>';
		$sHtml .= '<td colspan="9" class="bg-primary">REPORTE DE CONTRATOS</td>';
		$sHtml .= '</tr>';
        $sHtml .= '<tr>';
        $sHtml .= '<td>Contrato</td>';
        $sHtml .= '<td>Cliente</td>';
		$sHtml .= '<td>Tarifa</td>';
		$sHtml .= '<td>Adeuda</td>';
		$sHtml .= '<td>Detalle</td>';
        $sHtml .= '<td align="center"><input type="checkbox" onclick="marcar(this);"></td>';
        $sHtml .= '</tr>';
		
		$sqlTmpBarrio = "";
		if(!empty($idBarrio)){
			$sqlTmpBarrio = " and c.id_barrio = $idBarrio";
		}
		
		$sql = "select c.id, c.codigo, c.id_clpv, c.nom_clpv, c.direccion, c.tarifa
				from contrato_clpv c, contrato_pago p
				where c.id = p.id_contrato and
				c.id_clpv = p.id_clpv and
				p.estado_fact is null and
				c.estado = 'CO' and
				c.id_empresa = $idEmpresa and
				c.id_sucursal = $idSucursal
				$sqlTmpBarrio
				group by 1,2,3,4,5,6";
		if($oCon->Query($sql)){
			if($oCon->NumFilas() > 0){
			  unset($array);
			  $totalAdeuda = 0;
				do{
					$id = $oCon->f('id');
                    $id_clpv = $oCon->f('id_clpv');
                    $clpv_ruc_clpv = $oCon->f('ruc_clpv');
                    $clpv_nom_clpv = utf8_encode($oCon->f('nom_clpv'));
                    $codigo = $oCon->f('codigo');
					$tarifa = $oCon->f('tarifa');
					$direccion = $oCon->f('direccion');
					$referencia = $oCon->f('referencia');
					$id_sector = $oCon->f('id_sector');
					$secuencial = $oCon->f('secuencial');
					$fecha = $oCon->f('fecha');
										
					$valorDeuda = $this->consultaMontoMesAdeuda($oConA, $oIfx, $id, $id_clpv, $fechaCorte);

                    $array[] = array($id_clpv, $id);

                    $sHtml .= '<tr>';
                    $sHtml .= '<td>'.$codigo.'</td>';
                    $sHtml .= '<td>'.$clpv_nom_clpv.'</td>';
					$sHtml .= '<td align="right">'.number_format($tarifa, 2,'.',',').'</td>';
					$sHtml .= '<td align="right">'.number_format($valorDeuda, 2,'.',',').'</td>';
					$sHtml .= '<td align="center">
                                <div class="btn btn-warning btn-sm" onclick="estadoCuentaContrato('.$id.', '.$id_clpv.');">
									<span class="glyphicon glyphicon-list-alt"></span>
								</div>
                            </td>';
                    $sHtml .= '<td align="center">
                                <input type="checkbox" name="check_'.$id.'" value="S"/>
                            </td>';
                    $sHtml .= '</tr>';
					$totalAdeuda += $valorDeuda;
				}while($oCon->SiguienteRegistro());
				$sHtml .= '<tr>';
				$sHtml .= '<td class="bg-danger fecha_letra" colspan="3" align="right">Total:</td>'; 		
				$sHtml .= '<td align="right" class="bg-danger fecha_letra">'.number_format($totalAdeuda, 2,'.',',').'</td>';	
				$sHtml .= '<td class="bg-danger fecha_letra" colspan="2"></td>'; 				
				$sHtml .= '</tr>'; 
			}
		}
		$oCon->Free();
		
		$_SESSION['ARRAY_CHECK_CORTE_CLPV'] = $array;
		
		$sHtml .= '</table>'; 
		
							
		return $sHtml;
	}
	
	function reporteAbonosCobros($oCon, $oConA, $oIfx, $idempresa, $idsucursal, $id_clpv, $id_contrato, $id){
		session_start();

		$sHtml = '';
		
		//lectura sucia
        
        //cliente 
        $sql = "select clpv_ruc_clpv, clpv_nom_clpv from saeclpv where clpv_cod_empr = $idempresa and clpv_cod_clpv = $id_clpv";
        if($oIfx->Query($sql)){
            if($oIfx->NumFilas() > 0){
                $clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
                $clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
            }
        }
        $oIfx->Free();
		
		//datos contrato
		$sql = "select codigo from contrato_clpv where id_empresa = $idempresa and id_sucursal = $idsucursal and id_clpv = $id_clpv and id = $id_contrato";
		if($oCon->Query($sql)){
            if($oCon->NumFilas() > 0){
                $codigo = $oCon->f('codigo');
            }
        }
        $oCon->Free();
        
        $sHtml .= '<div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h5 class="modal-title" id="myModalLabel">
								'.$clpv_ruc_clpv.' '.$clpv_nom_clpv.' 
								<small>'.$codigo.'</small>
							</h5>
                        </div>
                        <div class="modal-body">';
        
        $sHtml .='<table class="table table-bordered table-striped table-condensed table-hover" style="width: 99%; margin-top: 10px;" align="center">';
        $sHtml .='<tr>
                    <td align="center" colspan="7" class="bg-primary">COMPROBANTE DE PAGO</td>
                </tr>';
                
        //query clpv
        $sql = "select id, id_pago, mes, anio, fecha, valor, cuenta, cheque, 
                banco, girador, fecha_server, user_web, secuencial, tipo
                from cobros_clpv 
                where
                id_empresa = $idempresa and
                id_sucursal = $idsucursal and
                id_clpv = $id_clpv and
                id_contrato = $id_contrato and
                id_fpago = $id";
        if($oCon->Query($sql)){
            if($oCon->NumFilas() > 0){
				$sHtml .= '<tr>';
				$sHtml .= '<td>Tipo</td>';
				$sHtml .= '<td>Fecha</td>';
				$sHtml .= '<td>Secuencial</td>';
				$sHtml .= '<td>Forma Pago</td>';
				$sHtml .= '<td>Valor</td>';
				$sHtml .= '<td>Usuario</td>';
				$sHtml .= '<td>Imprimir</td>';
				$sHtml .= '</tr>';
				$totalAbono = 0;
                do{
					$idDetalle = $oCon->f('id');
                    $idPago = $oCon->f('id_pago');
                    $mes = $oCon->f('mes');
                    $anio = $oCon->f('anio');
                    $fecha = $oCon->f('fecha');
                    $valor = $oCon->f('valor');
                    $cuenta = $oCon->f('cuenta');
                    $cheque = $oCon->f('cheque');
                    $banco = $oCon->f('banco');
                    $girador = $oCon->f('girador');
                    $fecha_server = $oCon->f('fecha_server');
                    $user_web = $oCon->f('user_web');
					$secuencial = $oCon->f('secuencial');
					$tipo = $oCon->f('tipo');

                    //formade pago
                    $sql = "select fpag_des_fpag from saefpag where fpag_cod_fpag = '$idPago' and fpag_cod_sucu = $idsucursal and
                            fpag_cod_empr = $idempresa";
                    $fpag_des_fpag = consulta_string_func($sql, 'fpag_des_fpag', $oIfx, '');

                    //usuario
                    $sql = "select concat(usuario_nombre, ' ', usuario_apellido) as user from usuario where usuario_id = $user_web";
                    $user = consulta_string_func($sql, 'user', $oConA, '');
                    
                    $sHtml .= '<tr>';
					$sHtml .= '<td align="left">'.$tipo.'</td>';
                    $sHtml .= '<td align="left">'.($fecha).'</td>';
					$sHtml .= '<td align="left">'.$secuencial.'</td>';
                    $sHtml .= '<td align="left">'.$fpag_des_fpag.'</td>';
                    $sHtml .= '<td align="right">'.number_format($valor,2,'.',',').'</td>';
                    $sHtml .= '<td align="left">'.$user.'</td>';
					$sHtml .= '<td align="center">
									<div class="btn btn-primary btn-sm" onclick="vistaPrevia_1('.$idDetalle.');">
                                        <span class="glyphicon glyphicon-print"></span>
                                    </div>
								</td>';
                    $sHtml .= '</tr>';
					
				$totalAbono += $valor;
                }while($oCon->SiguienteRegistro());
				$sHtml .= '<tr>';
				$sHtml .= '<td class="bg-danger fecha_letra" align="right" colspan="4">Total:</td>';
				$sHtml .= '<td class="bg-danger fecha_letra" align="right">'.number_format($totalAbono,2,'.',',').'</td>';
				$sHtml .= '<td class="bg-danger fecha_letra" align="right" colspan="2"></td>';
				$sHtml .= '</tr>';
            }
        }
        $oCon->Free();
        
        $sHtml .= '</table>';
        
        $sHtml .= '</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>';
				
		return $sHtml;
	}
	
	function reporteDetalleContratos($oCon, $oIfx, $oIfxA, $empresa, $id, $id_clpv, $fecha_inicio, $fecha_fin, $impuestoSN){
		
		$sHtml = "";
		$anio = date("Y");
		$fechaIni = ($fecha_inicio);
		$fechaFin = ($fecha_fin);
		
		//lectura sucia
		
		$sql = "select sucu_cod_sucu, sucu_sigl_sucu from saesucu where sucu_cod_empr = $empresa";
		if($oIfx->Query($sql)){
			if($oIfx->NumFilas() > 0){
				unset($arraySucursal);
				do{
					$arraySucursal[$oIfx->f('sucu_cod_sucu')] = $oIfx->f('sucu_sigl_sucu');
				}while($oIfx->SiguienteRegistro());
			}
		}
		$oIfx->Free();
		
		$sHtml .='<table class="table table-condensed table-striped" border="1" style="width:100%; margin-top: 10px; border-collapse: collapse;" align="center">';
		$sHtml .='<tr>
					<td colspan="9" class="bg-success">FACTURACION</td>
				</tr>
				<tr>
					<td style="width: 7%;" align="center">Sucursal</td>
					<td style="width: 8%;" align="center">Fecha</td>
					<td style="width: 8%;" align="center">Serie</td>
					<td style="width: 8%;" align="center">Factura</td>
					<td style="width: 37%;" align="center">Detalle</td>
					<td style="width: 8%;" align="center">Venta</td>
					<td style="width: 8%;" align="center">Nota Credito</td>
					<td style="width: 8%;" align="center">Cobros</td>
					<td style="width: 8%;" align="center">Saldo</td>
				</tr>';
		
		$sqlFiltroClpv = '';	
		$sqlFiltroClpv .= " fact_cod_clpv in ($id_clpv ";
		
		//consulta contratos relacionado
		$sql = "select id_clpv
				from contrato_rela
				where id_contrato = $id and
				estado = 'A'";
		if($oCon->Query($sql)){
			if($oCon->NumFilas() > 0){
				do{
					$id_clpv_rela = $oCon->f('id_clpv');
					
					$sqlFiltroClpv .= ','.$id_clpv_rela; 
				}while($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();
		
		$sqlFiltroClpv .= ')'; 	
		
		//array facturas
		$sql = "select fact_cod_fact, fact_cod_sucu, fact_cod_clpv, fact_nse_fact, fact_num_preimp, dfac_det_dfac, fact_fech_fact,
				coalesce ((sum(( coalesce (fact_tot_fact,0) - fact_dsg_valo + fact_fle_fact + fact_otr_fact + fact_fin_fact))),0) as venta,
				sum(coalesce (fact_iva,0)) as iva
				from saefact, saedfac
				where 
				fact_cod_empr = dfac_cod_empr and
				fact_cod_sucu = dfac_cod_sucu and
				fact_cod_clpv = dfac_cod_clpv and
				fact_cod_fact = dfac_cod_fact and
				fact_cod_empr = $empresa and
				fact_est_fact != 'AN' and
				dfac_cod_grpr in (18, 19, 20, 21, 25, 28) and
				fact_fech_fact between '$fecha_inicio' and '$fecha_fin' and
				$sqlFiltroClpv
				group by 1,2,3,4,5,6,7
				order by 7";
		//echo $sql;
		if ($oIfx->Query($sql)) {
			if ($oIfx->NumFilas() > 0) {
				$totalFactura = 0;
				$totalNotaCredito = 0;
				$totalCobros = 0;
				$totalSaldo = 0;
				unset($arrayFacturas);
				unset($arrayIvaFactura);
				do {
					$fact_cod_sucu = $oIfx->f('fact_cod_sucu');
					$fact_nse_fact = $oIfx->f('fact_nse_fact');
					$fact_cod_fact = $oIfx->f('fact_cod_fact');
					$fact_cod_clpv = $oIfx->f('fact_cod_clpv');
					$fact_num_preimp = $oIfx->f('fact_num_preimp');
					$dfac_det_dfac = $oIfx->f('dfac_det_dfac');
					$fact_fech_fact  = $oIfx->f('fact_fech_fact');
					$venta = $oIfx->f('venta');
					$iva = $oIfx->f('iva');
					
					$arrayIvaFactura[$fact_nse_fact][$fact_num_preimp] = $iva;
					
					$arrayFacturas[] = array($fact_cod_fact, $fact_cod_clpv, $fact_num_preimp, $fact_nse_fact);
					
					$tmpNcre = '';
					if($impuestoSN == 'S'){
						$venta += $iva;
						$tmpNcre = " + coalesce (ncre_iva,0)";
					}
					
					//totas de credito
					$sqlNcre = "select 
								round((coalesce ((sum( coalesce (ncre_tot_fact,0) $tmpNcre - coalesce (ncre_dsg_valo,0) + coalesce (ncre_fle_fact,0) + coalesce (ncre_otr_fact ,0) + coalesce (ncre_fin_fact,0))),0)),2) as venta 
								from saencre
								where ncre_cod_empr = $empresa and
								ncre_cod_fact = $fact_cod_fact and
								ncre_cod_clpv = $fact_cod_clpv and
								ncre_est_fact != 'AN'";
					$valorNotaCredito = consulta_string_func($sqlNcre, 'venta', $oIfxA, 0);
					
					//cancelacion
					$sqlDmcc = "select (sum(saedmcc.dmcc_cre_ml) ) as saldo  
								from saedmcc
								where dmcc_cod_empr = $empresa and
								clpv_cod_clpv = $fact_cod_clpv and
								dmcc_cod_tran in ('CAN', 'CAN2', 'CAN3', 'CAN4') and
								dmcc_num_fac like ('%$fact_num_preimp%')";
					$valorCobros = consulta_string_func($sqlDmcc, 'saldo', $oIfxA, 0);
										
					//cobros
					$sqlDmcc = "select (sum(saedmcc.dmcc_cre_ml) ) as saldo  
								from saedmcc
								where dmcc_cod_empr = $empresa and
								clpv_cod_clpv = $fact_cod_clpv and
								dmcc_cod_tran in ('RIV', 'RIV2', 'RIV3', 'RIV4') and
								dmcc_num_fac like ('%$fact_num_preimp%')";
					$valorRetencion = consulta_string_func($sqlDmcc, 'saldo', $oIfxA, 0);
					
					if($valorCobros > 0){
						$valorCobros += $valorRetencion;
					}
					
					if(empty($impuestoSN)){
						if($valorCobros > 0){
							$valorCobros = round($valorCobros / 1.12,2);
						}
					}
					
					$saldoFact = abs(round($venta - $valorNotaCredito - $valorCobros,2));
					
					$sHtml .='<tr>';
					$sHtml .='<td style="width: 7%;">'.$arraySucursal[$fact_cod_sucu].'</td>';
					$sHtml .='<td style="width: 8%;">'.fecha_mysql_func($fact_fech_fact).'</td>';
					$sHtml .='<td style="width: 8%;">'.$fact_nse_fact.'</td>';
					$sHtml .='<td style="width: 8%;"><a href="#" onclick="genera_documento(1, '.$fact_cod_fact.', '.$fact_cod_sucu.')">'.$fact_num_preimp.'</a></td>';
					$sHtml .='<td style="width: 37%;">'.$dfac_det_dfac.'</td>';
					$sHtml .='<td style="width: 8%;" align="right">'.number_format($venta,2,'.',',').'</td>';
					$sHtml .='<td style="width: 8%;" align="right">'.number_format($valorNotaCredito,2,'.',',').'</td>';
					$sHtml .='<td style="width: 8%;" align="right">'.number_format($valorCobros,2,'.',',').'</td>';
					$sHtml .='<td style="width: 8%;" align="right">'.number_format($saldoFact,2,'.',',').'</td>';
					$sHtml .='</tr>';
					
					$totalFactura += $venta;
					$totalNotaCredito += $valorNotaCredito;
					$totalCobros += $valorCobros;
					$totalSaldo += $saldoFact;
				} while ($oIfx->SiguienteRegistro());
				
				$sHtml .='<tr>';
				$sHtml .='<td colspan="5" class="bg-danger fecha_grande" align="right">TOTAL:</td>';
				$sHtml .='<td align="right" class="bg-danger fecha_grande">'.number_format($totalFactura,2,'.',',').'</td>';
				$sHtml .='<td align="right" class="bg-danger fecha_grande">'.number_format($totalNotaCredito,2,'.',',').'</td>';
				$sHtml .='<td align="right" class="bg-danger fecha_grande">'.number_format($totalCobros,2,'.',',').'</td>';
				$sHtml .='<td align="right" class="bg-danger fecha_grande">'.number_format($totalSaldo,2,'.',',').'</td>';
				$sHtml .='</tr>';
			}
		}
		$oIfx->Free();
		
		$sHtml .= '</table>';
		
		if(count($arrayFacturas) > 0){
			$sHtml .='<table class="table table-condensed table-striped" border="1" style="width:100%; margin-top: 10px; border-collapse: collapse;" align="center">';
			$sHtml .='<tr>
						<td colspan="8" class="bg-success">NOTAS DE CREDITO</td>
					</tr>
					<tr>
						<td style="width: 7%;" align="center">Sucursal</td>
						<td style="width: 8%;" align="center">Fecha</td>
						<td style="width: 8%;" align="center">Serie</td>
						<td style="width: 8%;" align="center">Nota Credito</td>
						<td style="width: 47%;" align="center">Observaciones</td>
						<td style="width: 14%;" align="center">Factura Aplica</td>
						<td style="width: 8%;" align="center">Valor</td>
					</tr>';
					
			$totalNC = 0;
			foreach($arrayFacturas as $val){
				$fact_cod_fact = $val[0];
				$fact_cod_clpv = $val[1];
				$fact_num_preimp = $val[2];
				$fact_nse_fact = $val[3];
				
				$tmpNcre = '';
				if($impuestoSN == 'S'){
					$venta += $iva;
					$tmpNcre = " + coalesce (ncre_iva,0)";
				}
					
				//totas de credito
				$sqlNcre = "select ncre_cod_ncre, ncre_cod_sucu, ncre_nse_ncre, ncre_num_preimp, ncre_fech_fact,
							ncre_cm1_ncre,
							round((coalesce ((sum( coalesce (ncre_tot_fact,0) $tmpNcre - coalesce (ncre_dsg_valo,0) + coalesce (ncre_fle_fact,0) + coalesce (ncre_otr_fact ,0) + coalesce (ncre_fin_fact,0))),0)),2) as venta 
							from saencre
							where ncre_cod_empr = $empresa and
							ncre_cod_fact = $fact_cod_fact and
							ncre_cod_clpv = $fact_cod_clpv and
							ncre_est_fact != 'AN'
							group by 1,2,3,4,5,6";
				if($oIfx->Query($sqlNcre)){
					if($oIfx->NumFilas() > 0){
						do{
							$ncre_cod_ncre = $oIfx->f('ncre_cod_ncre');
							$ncre_cod_sucu = $oIfx->f('ncre_cod_sucu');
							$ncre_nse_ncre = $oIfx->f('ncre_nse_ncre');
							$ncre_num_preimp = $oIfx->f('ncre_num_preimp');
							$ncre_fech_fact = $oIfx->f('ncre_fech_fact');
							$ncre_cm1_ncre = $oIfx->f('ncre_cm1_ncre');
							$valorNC = $oIfx->f('venta');
							
							$sHtml .='<tr>';
							$sHtml .='<td style="width: 7%;">'.$arraySucursal[$ncre_cod_sucu].'</td>';
							$sHtml .='<td style="width: 8%;">'.fecha_mysql_func($ncre_fech_fact).'</td>';
							$sHtml .='<td style="width: 8%;">'.$ncre_nse_ncre.'</td>';
							$sHtml .='<td style="width: 8%;"><a href="#" onclick="genera_documento(2, '.$ncre_cod_ncre.', '.$ncre_cod_sucu.')">'.$ncre_num_preimp.'</a></td>';
							$sHtml .='<td style="width: 47%;">'.$ncre_cm1_ncre.'</td>';
							$sHtml .='<td style="width: 14%;">'.$fact_nse_fact.' '.$fact_num_preimp.'</td>';
							$sHtml .='<td style="width: 8%;" align="right">'.number_format($valorNC,2,'.',',').'</td>';
							$sHtml .='</tr>';
							
							$totalNC += $valorNC;
						}while($oIfx->SiguienteRegistro());
					}
				}
				$oIfx->Free();
			}
			$sHtml .='<tr>';
			$sHtml .='<td colspan="6" class="bg-danger fecha_grande" align="right">TOTAL:</td>';
			$sHtml .='<td align="right" class="bg-danger fecha_grande">'.number_format($totalNC,2,'.',',').'</td>';
			$sHtml .='</tr>';
			$sHtml .='</table>';
		}
		
		
		//cobros
		if(count($arrayFacturas) > 0){
			$sHtml .='<table class="table table-condensed table-striped" border="1" style="width:100%; margin-top: 10px; border-collapse: collapse;" align="center">';
			$sHtml .='<tr>
						<td colspan="8" class="bg-success">COBROS</td>
					</tr>';
			$sHtml .='<tr>';
			$sHtml .='<td style="width: 7%;" align="center">Sucursal</td>';
			$sHtml .='<td style="width: 8%;" align="center">Fecha</td>';
			$sHtml .='<td style="width: 8%;" align="center">Transaccion</td>';
			$sHtml .='<td style="width: 10%;" align="center">Diario</td>';
			$sHtml .='<td style="width: 10%;" align="center">Factura</td>';
			$sHtml .='<td style="width: 41%;" align="center">Detalle</td>';
			$sHtml .='<td style="width: 8%;" align="center">Retenciones</td>';
			$sHtml .='<td style="width: 8%;" align="center">Valor</td>';
			$sHtml .='</tr>';		
					
			$totalCB = 0;
			$totalRT = 0;
			foreach($arrayFacturas as $val){
				$fact_cod_fact = $val[0];
				$fact_cod_clpv = $val[1];
				$fact_num_preimp = $val[2];
				$fact_nse_fact = $val[3];
				
				$ivaFactura = $arrayIvaFactura[$fact_nse_fact][$fact_num_preimp];
				
				$sqlDmcc = "select dmcc_cod_sucu, dmcc_cod_asto, dmcc_cod_tran, dmcc_det_dmcc, dmcc_num_fac, 
							dmcc_fec_emis, dmcc_cod_ejer, month(dmcc_fec_emis) as mescobro, dmcc_cod_fact,
							(sum(saedmcc.dmcc_cre_ml) ) as saldo  
							from saedmcc
							where dmcc_cod_empr = $empresa and
							clpv_cod_clpv = $fact_cod_clpv and
							dmcc_cod_tran not in ('NDC', 'NDC2', 'NDC3', 'NDC4', 'RIV') and
							dmcc_num_fac like ('%$fact_num_preimp%') and
							dmcc_cre_ml > 0
							group by 1,2,3,4,5,6,7,8,9";
				//$oReturn->alert($sqlDmcc);
				if($oIfx->Query($sqlDmcc)){
					if($oIfx->NumFilas() > 0){
						do{
							$dmcc_cod_sucu = $oIfx->f('dmcc_cod_sucu');
							$dmcc_cod_asto = $oIfx->f('dmcc_cod_asto');
							$dmcc_cod_tran = $oIfx->f('dmcc_cod_tran');
							$dmcc_det_dmcc = $oIfx->f('dmcc_det_dmcc');
							$dmcc_num_fac = $oIfx->f('dmcc_num_fac');
							$dmcc_cod_ejer = $oIfx->f('dmcc_cod_ejer');
							$dmcc_fec_emis = $oIfx->f('dmcc_fec_emis');
							$mescobro = $oIfx->f('mescobro');
							$saldo = $oIfx->f('saldo');
							$dmcc_cod_fact = $oIfx->f('dmcc_cod_fact');
							
							//cobros
							$sqlDmcc = "select (sum(saedmcc.dmcc_cre_ml) ) as saldo  
										from saedmcc
										where dmcc_cod_empr = $empresa and
										clpv_cod_clpv = $fact_cod_clpv and
										dmcc_cod_tran in ('RIV', 'RIV2', 'RIV3', 'RIV4') and
										dmcc_num_fac like ('%$fact_num_preimp%')";
							$valorRetencion = consulta_string_func($sqlDmcc, 'saldo', $oIfxA, 0);
							
							if($saldo > 0){
								$saldo += $valorRetencion;
							}
							
							if($impuestoSN != 'S'){
								if($ivaFactura > 0){
									$saldo = round($saldo / 1.12, 2);
								}
							}
							
							$sHtml .='<tr>';
							$sHtml .='<td style="width: 7%;">'.$arraySucursal[$dmcc_cod_sucu].'</td>';
							$sHtml .='<td style="width: 8%;">'.fecha_mysql_func($dmcc_fec_emis).'</td>';
							$sHtml .='<td style="width: 8%;">'.$dmcc_cod_tran.'</td>';
							$sHtml .='<td style="width: 10%;"><a href="#" onclick="seleccionaItem('.$empresa.', '.$dmcc_cod_sucu.', '.$dmcc_cod_ejer.', '.$mescobro.' ,\'' . $dmcc_cod_asto . '\')">'.$dmcc_cod_asto.'</a></td>';
							$sHtml .='<td style="width: 10%;">'.$dmcc_num_fac.'</td>';
							$sHtml .='<td style="width: 41%;">'.$dmcc_det_dmcc.'</td>';
							$sHtml .='<td style="width: 8%;" align="right">'.number_format($valorRetencion,2,'.',',').'</td>';
							$sHtml .='<td style="width: 8%;" align="right">'.number_format($saldo,2,'.',',').'</td>';
							$sHtml .='</tr>';
							
							$totalRT += $valorRetencion;
							$totalCB += $saldo;
						}while($oIfx->SiguienteRegistro());
					}
				}
				$oIfx->Free();
			}
			$sHtml .='<tr>';
			$sHtml .='<td colspan="6" class="bg-danger fecha_grande" align="right">TOTAL:</td>';
			$sHtml .='<td align="right" class="bg-danger fecha_grande">'.number_format($totalRT,2,'.',',').'</td>';
			$sHtml .='<td align="right" class="bg-danger fecha_grande">'.number_format($totalCB,2,'.',',').'</td>';
			$sHtml .='</tr>';
			$sHtml .='</table>';
		}
		
		//gastos
		$sHtml .='<table class="table table-condensed table-striped" border="1" style="width:100%; margin-top: 10px; border-collapse: collapse;" align="center">';
		$sHtml .='<tr>
						<td colspan="8" class="bg-success">GASTOS</td>
					</tr>';
		$sHtml .='<tr>';
		$sHtml .='<td style="width: 7%;" align="center">Sucursal</td>';
		$sHtml .='<td style="width: 8%;" align="center">Fecha</td>';
		$sHtml .='<td style="width: 8%;" align="center">Factura</td>';
		$sHtml .='<td style="width: 10%;" align="center">Diario</td>';
		$sHtml .='<td style="width: 18%;" align="center">Proveedor</td>';
		$sHtml .='<td style="width: 33%;" align="center">Detalle</td>';
		$sHtml .='<td style="width: 8%;" align="center">Valor</td>';
		$sHtml .='<td style="width: 8%;" align="center">Pago</td>';
		$sHtml .='</tr>';
				
		$sqlGastos = "select id, id_sucursal, factura, fecha, valor, id_clpv, id_prove, asto, ejer, prdo
						from proy_fact_benf
						where id_empresa = $empresa and
						id_contrato = $id and
						(select c.id_clpv
						from contrato_clpv c
						where c.id = id_contrato and
						c.id_empresa = id_empresa) = $id_clpv and
						fecha between '$fechaIni' and '$fechaFin' and
						estado = '0'";
		if($oCon->Query($sqlGastos)){
			if($oCon->NumFilas() > 0){
				$totalValor = 0;
				$totalValorPago = 0;
				do{
					$detalleFecha = $oCon->f('fecha');
					$detalleFactura = $oCon->f('factura');
					$detalleValor = $oCon->f('valor');
					$detalleProve = $oCon->f('id_prove');
					$detalleAsto = $oCon->f('asto');
					$detalleEjer = $oCon->f('ejer');
					$detallePrdo = $oCon->f('prdo');
					$id_sucursal = $oCon->f('id_sucursal');
					
					$sql = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $detalleProve";
					$clpv_nom_clpv = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');
					
					//detalle 
					$sqlDet = "select fprv_det_fprv 
								from saefprv
								where fprv_num_fact = '$detalleFactura' and
								fprv_cod_clpv = $detalleProve and
								fprv_cod_asto = '$detalleAsto' and
								fprv_cod_empr = $empresa";
					$detalleFprv = consulta_string_func($sqlDet, 'fprv_det_fprv', $oIfxA, 0);	
				
					//cobros
					$sqlDmcp = "select count(*) as control
								from saedmcp
								where dmcp_cod_empr = $empresa and
								clpv_cod_clpv = $detalleProve and
								dmcp_cod_tran = 'CAN' and
								dmcp_num_fac like ('%$detalleFactura%')";
					$controlPago = consulta_string_func($sqlDmcp, 'control', $oIfxA, 0);

					$valorPago = 0;
					if($controlPago > 0){
						$valorPago = $detalleValor;
					}
					
					$sHtml .='<tr>';
					$sHtml .='<td style="width: 7%;">'.$arraySucursal[$id_sucursal].'</td>';
					$sHtml .='<td style="width: 8%;">'.($detalleFecha).'</td>';
					$sHtml .='<td style="width: 8%;">'.$detalleFactura.'</td>';
					$sHtml .='<td style="width: 10%;"><a href="#" onclick="seleccionaItem('.$empresa.', '.$id_sucursal.', '.$detalleEjer.', '.$detallePrdo.' ,\'' . $detalleAsto . '\')">'.$detalleAsto.'</a></td>';
					$sHtml .='<td style="width: 18%;">'.$clpv_nom_clpv.'</td>';
					$sHtml .='<td style="width: 33%;">'.$detalleFprv.'</td>';
					$sHtml .='<td style="width: 8%;" align="right">'.number_format($detalleValor,2,'.',',').'</td>';
					$sHtml .='<td style="width: 8%;" align="right">'.number_format($valorPago,2,'.',',').'</td>';
					$sHtml .='</tr>';
					
					$totalValor += $detalleValor;
					$totalValorPago += $valorPago;
					
				} while ($oCon->SiguienteRegistro());
				$sHtml .= '<tr>';
				$sHtml .= '<td class="bg-danger fecha_grande" align="right" colspan="6">TOTALES:</td>';
				$sHtml .= '<td class="bg-danger fecha_grande" align="right">'.number_format($totalValor,2,'.',',').'</td>';
				$sHtml .= '<td class="bg-danger fecha_grande" align="right">'.number_format($totalValorPago,2,'.',',').'</td>';
				$sHtml .='</tr>';
			}
		}
		$oCon->Free();
		$sHtml .='</table>';
		
		//capacitaciones
		$sHtml .='<table class="table table-condensed table-striped" border="1" style="width:100%; margin-top: 10px; border-collapse: collapse;" align="center">';
		$sHtml .='<tr>
						<td colspan="8" class="bg-success">CAPACITACIONES</td>
					</tr>';
		$sHtml .='<tr>';
		$sHtml .='<td style="width: 10%;" align="center">No.Horas</td>';
		$sHtml .='<td style="width: 50%;" align="center">Capacitacion</td>';
		$sHtml .='<td style="width: 10%;" align="center">No. Personas</td>';
		$sHtml .='<td style="width: 10%;" align="center">Confinanciado</td>';
		$sHtml .='<td style="width: 10%;" align="center">Contrapartida</td>';
		$sHtml .='<td style="width: 10%;" align="center">Valor</td>';
		$sHtml .='</tr>';
		
		$sql = "SELECT id, fecha, ruc, horas, detalle, personas, valor, cofinanciado, contrapartida
				FROM proy_capacitacion
				WHERE id_empresa = $empresa AND
				id_clpv = $id_clpv and
				id_contrato = $id and
				estado = 'A'";
		if($oCon->Query($sql)){
			if($oCon->NumFilas() > 0){
				$totalHoras = 0;
				$totalPersonas = 0;
				$totalValor = 0;
				$totalCofinanciado = 0;
				$totalContrapartida = 0;
				do{
					$id = $oCon->f('id');
					$fecha = $oCon->f('fecha');
					$horas = $oCon->f('horas');
					$detalle = $oCon->f('detalle');
					$personas = $oCon->f('personas');
					$valor = $oCon->f('valor');
					$cofinanciado = $oCon->f('cofinanciado');
					$contrapartida = $oCon->f('contrapartida');
					
					$sHtml .='<tr>';
					$sHtml .='<td style="width: 10%;" align="right">'.$horas.'</td>';
					$sHtml .='<td style="width: 50%;">'.$detalle.'</td>';
					$sHtml .='<td style="width: 10%;" align="right">'.$personas.'</td>';
					$sHtml .='<td style="width: 10%;" align="right">'.number_format($cofinanciado,2,'.',',').'</td>';
					$sHtml .='<td style="width: 10%;" align="right">'.number_format($contrapartida,2,'.',',').'</td>';
					$sHtml .='<td style="width: 10%;" align="right">'.number_format($valor,2,'.',',').'</td>';
					$sHtml .='</tr>';
					
					$totalHoras += $horas;
					$totalPersonas += $personas;
					$totalValor += $valor;
					$totalCofinanciado += $cofinanciado;
					$totalContrapartida += $contrapartida;
					
				} while ($oCon->SiguienteRegistro());
				$sHtml .= '<tr>';
				$sHtml .= '<td class="bg-danger fecha_grande" align="right">'.$totalHoras.'</td>';
				$sHtml .= '<td class="bg-danger fecha_grande"></td>';
				$sHtml .= '<td class="bg-danger fecha_grande" align="right">'.$totalPersonas.'</td>';
				$sHtml .= '<td class="bg-danger fecha_grande" align="right">'.number_format($totalCofinanciado,2,'.',',').'</td>';
				$sHtml .= '<td class="bg-danger fecha_grande" align="right">'.number_format($totalContrapartida,2,'.',',').'</td>';
				$sHtml .= '<td class="bg-danger fecha_grande" align="right">'.number_format($totalValor,2,'.',',').'</td>';
				$sHtml .='</tr>';
			}
		}
		$oCon->Free();
		$sHtml .='</table>';
		
		return $sHtml;
	}
}
?>