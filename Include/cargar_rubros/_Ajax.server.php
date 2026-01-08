<?php
require ("_Ajax.comun.php"); // No modificar esta linea
require_once 'reader/Classes/PHPExcel/IOFactory.php';
require_once 'excelPhp/Excel/reader.php';
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  // S E R V I D O R   A J A X //
  :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
ini_set('memory_limit', '6144M');
set_time_limit(0);
/* * ******************************************* */
/* FCA01 :: GENERA INGRESO TABLA PRESUPUESTO  */
/* * ******************************************* */

function genera_cabecera_formulario($sAccion = 'nuevo', $aForm = '') {
//Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oCon = new Dbo ( );
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo ( );
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario ( );
    $fu->DSN = $DSN;

    $ifu = new Formulario ( );
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse ( );

    // VARIABLES
	unset($_SESSION['ARRAY_SUBIR_DATOS_CASH_CLPV']);
	unset($_SESSION['ARRAY_CLPV_CASH']);
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    $empresa = $aForm['empresa'];
    $departamento = $aForm['departamento'];

    if (empty($empresa)) {
        $empresa = $idempresa;
    }

    switch ($sAccion) {
        case 'nuevo':

            $ifu->AgregarCampoListaSQL('empresa', 'Empresa|left', "select empr_cod_empr, empr_nom_empr from saeempr order by 1", true, 170, 150);
            $ifu->AgregarComandoAlCambiarValor('empresa', 'cargar_sucursal();');

            $ifu->AgregarCampoListaSQL('departamento', 'Departamentos|left', "", false, 170, 150);
			
			$ifu->AgregarCampoListaSQL('rubro', 'Rubros|left', "", true, 170, 150);
			
            $ifu->AgregarCampoTexto('empleado', 'Empleado|left', false, '', 300, 200);
            $ifu->AgregarComandoAlEscribir('empleado', 'consultarEvent(event); form1.empleado.value=form1.empleado.value.toUpperCase();');
			
			$ifu->AgregarCampoArchivo('archivo', 'Archivo|left', false, '', 100, 100, '');
			
			$ifu->AgregarCampoNumerico('si', 'Activo|left', true, '1', 50, 1);
			
			$ifu->AgregarCampoNumerico('no', 'Inactivo|left', true, '0', 50, 1);
			
            $oReturn->script('cargar_sucursal();');

            break;

        case 'sucursal':

            $ifu->AgregarCampoListaSQL('empresa', 'Empresa|left', "select empr_cod_empr, empr_nom_empr from saeempr order by 1", true, 170, 150);
            $ifu->AgregarComandoAlCambiarValor('empresa', 'cargar_sucursal()');

            $ifu->AgregarCampoListaSQL('departamento', 'Departamentos|left', "select estr_cod_estr, estr_des_estr from saeestr where estr_cod_empr = $empresa and estr_cod_test = 'D'", false, 170, 150);
			
			$ifu->AgregarCampoLista('rubro', 'Rubros|left', true, 170, 100);
            $sql = "select rubr_cod_rubr, rubr_des_rubr, rubr_cod_trub from saerubr where rubr_cod_empr = $empresa order by 3,2";
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                    do {
                        $rubr_cod_rubr = $oIfx->f('rubr_cod_rubr');
                        $rubr_des_rubr = $oIfx->f('rubr_des_rubr');
						$rubr_cod_trub = $oIfx->f('rubr_cod_trub');
						$detalle = $rubr_cod_trub.' - '.$rubr_des_rubr . ' - '.$rubr_cod_rubr;
                        $ifu->AgregarOpcionCampoLista('rubro', $detalle, $rubr_cod_rubr);
                    } while ($oIfx->SiguienteRegistro());
                }
            }
            $oIfx->Free();
			

            $ifu->AgregarCampoTexto('empleado', 'Empleado|left', false, '', 300, 200);
            $ifu->AgregarComandoAlEscribir('empleado', 'consultarEvent(event); form1.empleado.value=form1.empleado.value.toUpperCase();');
			
			$ifu->AgregarCampoArchivo('archivo', 'Archivo|left', false, '', 100, 100, '');
			
			$ifu->AgregarCampoNumerico('si', 'Activo|left', true, '1', 50, 1);
			
			$ifu->AgregarCampoNumerico('no', 'Inactivo|left', true, '0', 50, 1);
			
            $ifu->cCampos["empresa"]->xValor = $empresa;

            break;
    }

    $sHtml .= '<table class="table table-striped table-bordered table-condensed" style="width: 70%; margin-bottom: 0px;">';
    $sHtml .= '<tr>
                <td align="left" colspan="4">
					<div class="btn-group">
						<div class="btn btn-primary btn-sm" onclick="genera_formulario();">
							<span class="glyphicon glyphicon-file"></span>
							Nuevo
						</div>
						<div class="btn btn-primary btn-sm" onclick="guardar();">
							<span class="glyphicon glyphicon-floppy-disk"></span>
							Guardar
						</div>
					</div>
                </td>
            </tr>';
    $sHtml .= '<tr>
                    <td colspan="4" align="center" class="bg-primary">EMPLEADOS - RUBROS</td>
                </tr>';
    $sHtml .= '<tr>
					<td>' . $ifu->ObjetoHtmlLBL('empresa') . '</td>
					<td>' . $ifu->ObjetoHtml('empresa') . '</td>
					<td colspan="2">
						<span>
							<label for="filtro7">Todos</label>
							<input type="radio" name="opFiltro" id="filtro7" value="4" checked onclick="generaProvision();"/>
						</span>
						<span>
							<label for="filtro4">Dscto</label>
							<input type="radio" name="opFiltro" id="filtro4" value="1" onclick="generaProvision();" />
						</span>
						<span>
							<label for="filtro5">Ingreso</label>
							<input type="radio" name="opFiltro" id="filtro5" value="2" onclick="generaProvision();" />
						</span>
						 <span>
							<label for="filtro6">Provision</label>
							<input type="radio" name="opFiltro" id="filtro6" value="3" onclick="generaProvision();" />
						</span>
					</td>
				</tr>
				<tr>
					<td>' . $ifu->ObjetoHtmlLBL('departamento') . '</td>
					<td>' . $ifu->ObjetoHtml('departamento') . '</td>
					<td>' . $ifu->ObjetoHtmlLBL('rubro') . '</td>
					<td>' . $ifu->ObjetoHtml('rubro') . '</td>
				</tr>
				<tr>
					<td>' . $ifu->ObjetoHtmlLBL('empleado') . '</td>
					<td colspan="1">' . $ifu->ObjetoHtml('empleado') . '</td>
					<td>Filtro</td>
						<td colspan="3">
							<span>
								<label for="filtro1">Todos</label>
								<input type="radio" name="filtro" id="filtro1" value="1" checked/>
							</span>
							<span>
								<label for="filtro2">Activo</label>
								<input type="radio" name="filtro" id="filtro2" value="2" />
							</span>
							 <span>
								<label for="filtro3">Inactivo</label>
								<input type="radio" name="filtro" id="filtro3" value="3" />
							</span>
						</td>
				</tr>
				<tr>
					<td>'.$ifu->ObjetoHtmlLBL('archivo').'</td>
					<td colspan="3">'.$ifu->ObjetoHtml('archivo').'
						<div class="btn btn-info btn-sm" onclick="reporte_pedido();">
							<span class="glyphicon glyphicon-open"></span>
							Reporte
						</div>
						<div class="btn btn-info btn-sm" onclick="procesar();">
							<span class="glyphicon glyphicon-floppy-saved"></span>
							Procesar
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="4" align="center">
						<div class="btn btn-primary btn-sm" onClick="consultar();">
							<span class="glyphicon glyphicon-search"></span>
							Consultar
						</div>
					</td>
				</tr>';
    $sHtml .='</table>';

    $oReturn->assign("divFormularioFacturacion", "innerHTML", $sHtml);
    $oReturn->assign("divReporteFacturacion", "innerHTML", '');
    $oReturn->assign("empleado", "placeholder", "DIGITE DATOS DEL EMPLEADO PARA BUSCAR ....");

    return $oReturn;
}

function generaProvision($aForm = '') {
//Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();
	
	$oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

	$oReturn = new xajaxResponse();
	
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    $empresa = $aForm['empresa'];
	$opFiltro = $aForm['opFiltro'];
	
	$tmpFiltro = '';
	if($opFiltro == '1'){
		$tmpFiltro = " and rubr_cod_trub = 'D'";
	}elseif($opFiltro == '2'){
		$tmpFiltro = " and rubr_cod_trub = 'I'";
	}elseif($opFiltro == '3'){
		$tmpFiltro = " and rubr_cod_trub = 'P'";
	}elseif($opFiltro == '4'){
		$tmpFiltro = '';
	}
	
	//lectura sucia
    //
	
	$sql = "select rubr_cod_rubr, rubr_des_rubr, rubr_cod_trub from saerubr where rubr_cod_empr = $empresa $tmpFiltro order by 3,2";
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_rubro();');
        if ($oIfx->NumFilas() > 0) {
			$i = 1;
            do {
				$detalle = $oIfx->f('rubr_cod_trub').' - '.$oIfx->f('rubr_des_rubr').' - '.$oIfx->f('rubr_cod_rubr');
                $oReturn->script(('anadir_elemento_rubro(' . $i++ . ',\'' . $oIfx->f('rubr_cod_rubr') . '\', \'' . $detalle . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
	$oIfx->Free();

    return $oReturn;
}

function consultar($aForm = '') {
//Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
	
    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();
	
	$ifu = new Formulario ( );
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    try {

        //variables de sesion
		unset($_SESSION['ARRAY_RUBR_EMPL']);
        $idempresa = $_SESSION['U_EMPRESA'];

        //variables del formulario
        $empresa = $aForm['empresa'];
        $departamento = $aForm['departamento'];
        $rubro = $aForm['rubro'];
		$empleado = $aForm['empleado'];
		$filtro = $aForm['filtro'];
		
        //Lectura Sucia
        //    
		
		/*(select s.esem_cod_estr from saeesem s, saeestr t 
				where s.esem_cod_empl = e.empl_cod_empl and
				s.esem_cod_estr = t.estr_cod_estr and
				s.esem_cod_empr = t.estr_cod_empr and
				e.empl_cod_empr = t.estr_cod_empr and 
				e.empl_cod_empr = s.esem_cod_empr and 
				e.empl_cod_sucu = s.esem_cod_sucu) as depar*/

		$sql = "select estr_cod_estr, estr_des_estr from saeestr where estr_cod_empr = $empresa ";
		if($oIfx->Query($sql)){
			if($oIfx->NumFilas() > 0){
				unset($arrayDepar);
				do{
					$arrayDepar[$oIfx->f('estr_cod_estr')] = $oIfx->f('estr_des_estr');
				}while($oIfx->SiguienteRegistro());
			}
		}
		$oIfx->Free();
		
		$sqlTmp = '';
		if(!empty($empleado)){
			$sqlTmp = " and (e.empl_cod_empl like ('%$empleado%') OR e.empl_ape_nomb like upper('%$empleado%'))";
		}
		
		$sqlTmpDepar = '';
		$dato = '';
		if(!empty($departamento)){
		
			 $sql = "SELECT 	estr_cod_estr FROM saeestr WHERE estr_cod_padr='$departamento'";
			$empl = '';
			if ($oIfx->Query($sql)) {
				if ($oIfx->NumFilas() > 0) {
					do {
						$empl .= "'" . $oIfx->f('estr_cod_estr') . "',";
					} while ($oIfx->SiguienteRegistro());
				}
			}
			$dato = substr($empl, 0, -1);
			$sql = "SELECT  esem_cod_empl FROM saeesem WHERE esem_cod_estr in ($dato) and esem_fec_sali is null";
			$empl = '';
			if ($oIfx->Query($sql)) {
				if ($oIfx->NumFilas() > 0) {
					do {
						$empl .= "'" . $oIfx->f('esem_cod_empl') . "',";
					} while ($oIfx->SiguienteRegistro());
				}
			}
			 $dato = substr($empl, 0, -1);
			 $dato = "and empl_cod_empl in ($dato)";
		}
		
		$sqlTmpDatos = '';
		if($filtro == 1){
			$sqlTmpDatos = " ";
		}elseif($filtro == 2){
			$sqlTmpDatos = " and r.ruem_est_ruem = '1'";
		}elseif($filtro == 3){
			$sqlTmpDatos = " and r.ruem_est_ruem = '0'";
		}
        
        $sql = "select e.empl_cod_empl, empl_ape_nomb, r.ruem_est_ruem, r.ruem_cod_rubr
				from saeempl e, saeruem r, saerubr b
				where e.empl_cod_empl = r.ruem_cod_empl and
				e.empl_cod_empr = r.ruem_cod_empr and
				r.ruem_cod_empr = b.rubr_cod_empr and
				r.ruem_cod_rubr = b.rubr_cod_rubr and
				e.empl_cod_empr = b.rubr_cod_empr and
				e.empl_cod_empr = $empresa and
				e.empl_cod_eemp = 'A' and
				b.rubr_cod_rubr = '$rubro'
				$sqlTmp
				$sqlTmpDatos
				 $dato 
			
				order by 2";
        //$oReturn->alert($sql);
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {

                $sHtml .='<table class="table table-striped table-bordered table-hover table-condensed" style="width: 80%; margin-top: 10px;" align="center">
							<tr>
								<td>No.</td>
								<td>IDENTIFICACION</td>
								<td>EMPLEADO</td>
								<td>CARGO</td>
								<td>RUBRO</td>
								<td align="center"><input type="checkbox" onclick="marcar(this);"></td>
							</tr>';
                $i = 1;
				unset($array);
                do {
					$empl_cod_empl = $oIfx->f('empl_cod_empl');
					$empl_ape_nomb = $oIfx->f('empl_ape_nomb');
					$ruem_est_ruem = $oIfx->f('ruem_est_ruem');
					$ruem_cod_rubr = $oIfx->f('ruem_cod_rubr');
					$depar = $oIfx->f('depar');
					
					$array[] = array($empl_cod_empl, $ruem_cod_rubr);
					
					$tipoChecked = '';
					if($ruem_est_ruem == 1){
						$tipoChecked = 'checked';
					}
					
					$input = '<input type="checkbox" name="check_'.$empl_cod_empl.'_'.$ruem_cod_rubr.'" id="check_'.$empl_cod_empl.'_'.$ruem_cod_rubr.'" value="S" '.$tipoChecked.'/>';
					
                    $sHtml .= '<tr>';
					$sHtml .= '<td>'.$i.'</td>';
					$sHtml .= '<td>'.$empl_cod_empl.'</td>';
					$sHtml .= '<td>'.$empl_ape_nomb.'</td>';
					$sHtml .= '<td>'.$arrayDepar[$depar].'</td>';
					$sHtml .= '<td>'.$ruem_cod_rubr.'</td>';
					$sHtml .= '<td align="center">'.$input.'</td>';
					$sHtml .= '</tr>';
					$i++;
					
                }while ($oIfx->SiguienteRegistro());
                $sHtml .='</table>';
            }else {
                $sHtml = '<span class="fecha_letra">Sin Datos para mostrar...</span>';
            }
        }
        $oIfx->Free();

		$_SESSION['ARRAY_RUBR_EMPL'] = $array;
        $oReturn->assign("divReporteFacturacion", "innerHTML", $sHtml);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }//fin if try
    return $oReturn;
}

function reporteArchivoExcel($aForm = '') {
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

	unset($_SESSION['ARRAY_RUBR_EMPL']);
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    
	//variable del formulario
	$empresa = $aForm['empresa'];
	$sucursal = $aForm['sucursal'];
	
	try {
		//query banco
		$sql = "select empl_cod_empl, empl_ape_nomb 
				from saeempl
				where empl_cod_empr = $empresa";
		if($oIfx->Query($sql)){
			if($oIfx->NumFilas() > 0){
				unset($arrayEmpleado);
				do{
					$arrayEmpleado[$oIfx->f('empl_cod_empl')] = $oIfx->f('empl_ape_nomb');
				}while($oIfx->SiguienteRegistro());
			}
		}
		$oIfx->Free();
		
		
		$archivo = $aForm['archivo'];

		// archivo txt
		$archivo_real = substr($archivo, 3);
		$nombreArchivo = "../../Include/Clases/Formulario/Plugins/reloj/" . $archivo_real;
		
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('CP1251');
		$data->read($nombreArchivo);
		
		unset($arrayA);
		unset($arrayB);
		unset($arrayC);
		
		$x = 1;
		for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) { //lectura vertical		
			for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) { //lectura horizontal
				
				if($j == 1){ //B
					$arrayB[$x] = $data->sheets[0]['cells'][$i][$j];
				}elseif($j == 2){ //C
					$arrayC[$x] = $data->sheets[0]['cells'][$i][$j];
					$x++;
				}
			}//fin for
		}//fin for
		
		//presenta html
		if(count($arrayB) > 0){
			
			$sHtml .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 80%; margin-top: 10px;" align="center">';
			$sHtml .= '<tr>
						   <td class="bg-primary" colspan="5">REPORTE ARCHIVO EXCEL</td>
					   </tr>';
			$sHtml .= '<tr>
						<td>No.</td>
						<td>IDENTIFICACION</td>
						<td>EMPLEADO</td>
						<td>VALOR</td>
						<td align="center">VALIDO</td>
					  </tr>';
			unset($arrayCash);

			for($d = 1; $d <= count($arrayB); $d++){
				$bandVerifica = true;
				
				if(!empty($arrayB[$d])){
					
					$empl_cod_empl = $arrayB[$d];
					$empl_ape_nomb = $arrayEmpleado[$empl_cod_empl];
					$estado = $arrayC[$d];
					
					if(empty($empl_ape_nomb)){
						$colorClpv = 'red'; 
						$bandVerifica = false;
					}
					
					if($bandVerifica == true){
						$valido = 'S';
						$colorValido = 'green'; 
					}else{
						$valido = 'N';
						$colorValido = 'red'; 
					}
					
					if($bandVerifica == true){
						$array[] = array($empl_cod_empl, $estado);
					}
											
					
					$sHtml .= '<tr>';
					$sHtml .= "<td>".$d."</td>";	
					$sHtml .= "<td style='color: ".$colorValido.";'>". $empl_cod_empl ."</td>";
					$sHtml .= "<td style='color: ".$colorValido.";'>". $empl_ape_nomb ."</td>";
					$sHtml .= "<td style='color: ".$colorValido.";'>". $estado ."</td>";
					$sHtml .= "<td align='center' style='color: ".$colorValido.";'>". $valido."</td>";	
					$sHtml .= "</tr>";
				}//fin if
			}//fin for
			$sHtml .= "</table>";
		}
		
		$_SESSION['ARRAY_RUBR_EMPL'] = $array;
			
		$oReturn->assign("divReporteFacturacion", "innerHTML", $sHtml);
		
	} catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }//fin if try
	
    return $oReturn;
}

function guardar($aForm = '') {
//Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    //VARIABLES DE SESION
    $array = $_SESSION['ARRAY_RUBR_EMPL'];
	
	 //variables del formulario
	$empresa = $aForm['empresa'];

    //LECTURA SUCIA
    //

    if(count($array) > 0){
		try {
		
            // commit
            $oIfx->QueryT('BEGIN WORK;');
			
			foreach($array as $val){
				$empl_cod_empl = $val[0];
				$ruem_cod_rubr = $val[1];
									
				$check = $aForm['check_'.$empl_cod_empl.'_'.$ruem_cod_rubr];
				
				if(!empty($check)){
					$opCheck = '1';
				}else{
					$opCheck = '0';
				}
				
				$sql = "update saeruem set ruem_est_ruem = '$opCheck' where ruem_cod_rubr = '$ruem_cod_rubr' and ruem_cod_empl = '$empl_cod_empl' and ruem_cod_empr = $empresa";
				//$oReturn->alert($sql);
				$oIfx->QueryT($sql);
					
			}
			$oIfx->QueryT('COMMIT WORK;');
			$oReturn->alert('Procesado Correctamente..');
			$oReturn->script('consultar();');
		} catch (Exception $e) {
            // rollback
            $oIfx->QueryT('ROLLBACK WORK;');
            $oReturn->alert($e->getMessage());
        }
	}else{
		$oReturn->alert('Sin Datos para procesar..');
	}

    return $oReturn;
}

function procesar($aForm = '') {
//Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    //VARIABLES DE SESION
    $array = $_SESSION['ARRAY_RUBR_EMPL'];
	
	//varibales del formulario
	$empresa = $aForm['empresa'];
	$departamento = $aForm['departamento'];
	$rubro = $aForm['rubro'];

	
    //LECTURA SUCIA
    //

    if(count($array) > 0){
		try {
		
            // commit
            $oIfx->QueryT('BEGIN WORK;');
			
			//update estado 
			$sql = "update saeruem set ruem_est_ruem = '0' where ruem_cod_rubr = '$rubro' and ruem_cod_empr = $empresa";
			$oIfx->QueryT($sql);
			
			foreach($array as $val){
				$empl_cod_empl = $val[0];
				$opcion = $val[1];
				
				$sql = "select count(*) as control from saeruem where ruem_cod_rubr = '$rubro' and ruem_cod_empl = '$empl_cod_empl' and ruem_cod_empr = $empresa";
				$control = consulta_string($sql, 'control', $oIfx, 0);
				
				if($control == 0){
					$sql = "insert into saeruem (ruem_cod_rubr, ruem_cod_empl, ruem_cod_empr, ruem_est_ruem) 
											values('$rubro', '$empl_cod_empl', '$empresa', '$opcion')";
					$oIfx->QueryT($sql);
				}else{
					$sql = "update saeruem set ruem_est_ruem = '$opcion' where ruem_cod_rubr = '$rubro' and ruem_cod_empl = '$empl_cod_empl' and ruem_cod_empr = $empresa";
					$oIfx->QueryT($sql);
				}
				
			}
			$oIfx->QueryT('COMMIT WORK;');
			$oReturn->alert('Procesado Correctamente..');
			//$oReturn->script('consultar();');
		} catch (Exception $e) {
            // rollback
            $oIfx->QueryT('ROLLBACK WORK;');
            $oReturn->alert($e->getMessage());
        }
	}else{
		$oReturn->alert('Sin Datos para procesar..');
	}

    return $oReturn;
}

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
?>