<?php

// suma horas
function sumaHorasCli($hora_ini, $hora_fin){
    $min1 = MinutosCli($hora_ini);
    $min2 = MinutosCli($hora_fin);
    $min_total = $min1 + $min2;
    $hora = TotalHoras_NormalCli($min_total);
    return $hora;
}


function MinutosCli($hora){
	$horaSplit = explode(":", $hora);
	if( count($horaSplit) < 3 ) {
            $horaSplit[2] = 0;
	}

	// Pasamos los elementos a segundos
	$horaSplit[0] = $horaSplit[0] * 60 * 60;
	$horaSplit[1] = $horaSplit[1] * 60;

	return round(((($horaSplit[0] + $horaSplit[1] + $horaSplit[2]) / 60)),0);
}

function TotalHoras_NormalCli($mins){
        if($mins<0){
            $mins = abs($mins);
            $signo = -1;
        }else{
            $signo = 1;
        }

	$hours = floor($mins / 60);
	$minutes = $mins - ($hours*60);	
	if (!$minutes){
		$minutes = "00";
	}else if ($minutes <= 9){
		$minutes = "0" . $minutes;
	}
        $hours = $hours * $signo;
        if($hours<10){
            $hours = '0'.$hours;
        }
	return ("{$hours}:{$minutes}:00");
}


function sumar_dias_func_clinico($fecha, $ndias)
{
    $nuevafecha = date("Y-m-d", strtotime($fecha.'+ '.$ndias.' days'));
    return ($nuevafecha);
}


function sincroniza_base(){

	global $DSN, $DSN_Ifx;
    session_start();
	$oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();


	$oReturn = new xajaxResponse();
	/*****MODULO CLINICO_FACTURACION*****/
	$sqlgein = "SELECT count(*) as conteo
	FROM INFORMATION_SCHEMA.COLUMNS
	WHERE COLUMN_NAME = 'tpac_cod_pedf' AND TABLE_NAME = 'saetpac' and table_schema='clinico'";
	$ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);

	if ($ctralter == 0) {
		$sqlalter = "alter table clinico.saetpac add tpac_cod_pedf int2";
		$oIfx->QueryT($sqlalter);
	}


	/*****MODULO CLINICO_PROTOCOLO_OPERATORIO*****/

	//CAMPO CODIGOS DE TARIFARIO
    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'prot_cod_tari' AND TABLE_NAME = 'protocolo' AND table_schema='clinico'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table clinico.protocolo add prot_cod_tari varchar(255);";
            $oIfx->QueryT($sqlalter);
        }
    //CAMPO ESTADO

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'prot_estado' AND TABLE_NAME = 'protocolo' AND table_schema='clinico'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table clinico.protocolo add prot_estado varchar(1) default 'S';";
            $oIfx->QueryT($sqlalter);
        }
    //CAMPOS ANULACION

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'prot_usu_anu' AND TABLE_NAME = 'protocolo' AND table_schema='clinico'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table clinico.protocolo add prot_usu_anu int4 ";
            $oIfx->QueryT($sqlalter);
        }

     $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'prot_fec_anu' AND TABLE_NAME = 'protocolo' AND table_schema='clinico'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table clinico.protocolo add prot_fec_anu timestamp ";
            $oIfx->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'prot_moti_anu' AND TABLE_NAME = 'protocolo' AND table_schema='clinico'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table clinico.protocolo add prot_moti_anu varchar(1000)";
                $oIfx->QueryT($sqlalter);
            }

	/*****MODULO CLINICO_FORM_EPICRISIS*****/
    //CAMPO ESTADO

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'epi_estado' AND TABLE_NAME = 'epicrisis' AND table_schema='clinico'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table clinico.epicrisis add epi_estado varchar(1) default 'S';";
            $oIfx->QueryT($sqlalter);
        }
    //CAMPOS ANULACION

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'epi_usu_anu' AND TABLE_NAME = 'epicrisis' AND table_schema='clinico'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table clinico.epicrisis add epi_usu_anu int4 ";
            $oIfx->QueryT($sqlalter);
        }

     $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'epi_fec_anu' AND TABLE_NAME = 'epicrisis' AND table_schema='clinico'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table clinico.epicrisis add epi_fec_anu timestamp ";
            $oIfx->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'epi_moti_anu' AND TABLE_NAME = 'epicrisis' AND table_schema='clinico'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table clinico.epicrisis add epi_moti_anu varchar(1000)";
                $oIfx->QueryT($sqlalter);
            }

	/*****MODULO CLINICO_HOJA_005*****/
    //CAMPO ESTADO

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'not_estado' AND TABLE_NAME = 'nota_enfer' AND table_schema='clinico'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table clinico.nota_enfer add not_estado varchar(1) default 'S';";
            $oIfx->QueryT($sqlalter);
        }
    //CAMPOS ANULACION

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'not_usu_anu' AND TABLE_NAME = 'nota_enfer' AND table_schema='clinico'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table clinico.nota_enfer add not_usu_anu int4 ";
            $oIfx->QueryT($sqlalter);
        }

     $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'not_fec_anu' AND TABLE_NAME = 'nota_enfer' AND table_schema='clinico'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table clinico.nota_enfer add not_fec_anu timestamp ";
            $oIfx->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'not_moti_anu' AND TABLE_NAME = 'nota_enfer' AND table_schema='clinico'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table clinico.nota_enfer add not_moti_anu varchar(1000)";
                $oIfx->QueryT($sqlalter);
            }
/*****MODULO CLINICO_FORM_ANAMNESIS*****/
    //CAMPO ESTADO

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'anam_estado' AND TABLE_NAME = 'anamnesis' AND table_schema='clinico'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table clinico.anamnesis add anam_estado varchar(1) default 'S';";
            $oIfx->QueryT($sqlalter);
        }
    //CAMPOS ANULACION

    $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'anam_usu_anu' AND TABLE_NAME = 'anamnesis' AND table_schema='clinico'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table clinico.anamnesis add anam_usu_anu int4 ";
            $oIfx->QueryT($sqlalter);
        }

     $sqlgein = "SELECT count(*) as conteo
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'anam_fec_anu' AND TABLE_NAME = 'anamnesis' AND table_schema='clinico'";
        $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
        if ($ctralter == 0) {
            $sqlalter = "alter table clinico.anamnesis add anam_fec_anu timestamp ";
            $oIfx->QueryT($sqlalter);
        }

        $sqlgein = "SELECT count(*) as conteo
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME = 'anam_moti_anu' AND TABLE_NAME = 'anamnesis' AND table_schema='clinico'";
            $ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
            if ($ctralter == 0) {
                $sqlalter = "alter table clinico.anamnesis add anam_moti_anu varchar(1000)";
                $oIfx->QueryT($sqlalter);
            }

/*****MODULO CLINICO_PREFACTURA*****/

			//ALTER PROTOCLO OPERATORIO
			$sqlgein = "SELECT count(*) as conteo
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE COLUMN_NAME = 'ped_est_pre' AND TABLE_NAME = 'protocolo' AND table_schema='clinico'";
				$ctralter = consulta_string($sqlgein, 'conteo', $oIfx, 0);
				if ($ctralter == 0) {
					$sqlalter = "alter table clinico.protocolo add ped_est_pre varchar(1) default 'N';";
					$oIfx->QueryT($sqlalter);
				}
			 //ALTER PROCESO DE ANULACION
		
		
			 //SAEPEDF
		
			$sqlgein="SELECT count(*) as conteo
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE COLUMN_NAME = 'pedf_usu_anu' AND TABLE_NAME = 'saepedf'";
			$ctralter=consulta_string($sqlgein,'conteo',$oIfx,0);
			if($ctralter==0){
				$sqlalter="alter table saepedf add pedf_usu_anu int2";
				$oIfx->QueryT($sqlalter);
			}
		
			$sqlgein="SELECT count(*) as conteo
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE COLUMN_NAME = 'pedf_fec_anu' AND TABLE_NAME = 'saepedf'";
			$ctralter=consulta_string($sqlgein,'conteo',$oIfx,0);
		
			if($ctralter==0){
					$sqlalter="alter table saepedf add pedf_fec_anu timestamp";
					$oIfx->QueryT($sqlalter);
				  
			}
		
			//SAEDPEF
			 
			//ANULACION
			$sqlgein="SELECT count(*) as conteo
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE COLUMN_NAME = 'dpef_usu_anu' AND TABLE_NAME = 'saedpef'";
			$ctralter=consulta_string($sqlgein,'conteo',$oIfx,0);
			if($ctralter==0){
				$sqlalter="alter table saedpef add dpef_usu_anu int2";
				$oIfx->QueryT($sqlalter);
			}
		
			$sqlgein="SELECT count(*) as conteo
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE COLUMN_NAME = 'dpef_fec_anu' AND TABLE_NAME = 'saedpef'";
			$ctralter=consulta_string($sqlgein,'conteo',$oIfx,0);
		
			if($ctralter==0){
					$sqlalter="alter table saedpef add dpef_fec_anu timestamp";
					$oIfx->QueryT($sqlalter);
				  
			}
			//actualizacion

			$sqlgein="SELECT count(*) as conteo
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE COLUMN_NAME = 'dpef_usu_edi' AND TABLE_NAME = 'saedpef'";
			$ctralter=consulta_string($sqlgein,'conteo',$oIfx,0);
			if($ctralter==0){
				$sqlalter="alter table saedpef add dpef_usu_edi int2";
				$oIfx->QueryT($sqlalter);
			}
		
			$sqlgein="SELECT count(*) as conteo
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE COLUMN_NAME = 'dpef_fec_edi' AND TABLE_NAME = 'saedpef'";
			$ctralter=consulta_string($sqlgein,'conteo',$oIfx,0);
		
			if($ctralter==0){
					$sqlalter="alter table saedpef add dpef_fec_edi timestamp";
					$oIfx->QueryT($sqlalter);
				  
			}

	return $oReturn;


}

//MODAL DIGANOSTICOS CIE10
function modal_consulta_productos($aForm='',$campo, $camocu){

	global $DSN, $DSN_Ifx;
    session_start();
	$oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();
	
	//variables de sesion
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

	$fil = mb_strtoupper($aForm[''.$campo.''],'utf-8');
    $fil = trim($fil);
    if(!empty($fil)){
		$temp="and (prod_nom_prod like '%$fil%' or prod_cod_prod like '%$fil%')";
    }





	$sHtml .= '<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">LISTADO DE PRODUCTOS</h4>
		</div>
		<div class="modal-body">';

		$sHtml .= ' <table id="tbprod"  class="table table-striped table-condensed table-bordered table-hover" style="width: 100%; margin-top: 20px;" align="center">';
                        $sHtml .= '<thead>';
                        $sHtml .= ' <tr>
                                            <th align="center">Nro.</td>
                                            <th align="center">Codigo</th>
                                            <th align="center">Nombre</th>
                                            <th align="center">Seleccionar</th>              
                                        </tr>';
                        $sHtml .= '</thead>';
                        $sHtml .= '<tbody>';

		  $sql = "select prod_cod_prod, prod_nom_prod
                    from saeprod where
                    prod_cod_empr = $idempresa and
                    prod_cod_sucu = $idsucursal 
                    $temp  
					order by  2 limit 500";

		$i=1;
		if($oCon->Query($sql)){
			if($oCon->NumFilas() > 0){
				do{
					$codigo=$oCon->f('prod_cod_prod');
					$nombre=$oCon->f('prod_nom_prod');
					

					

					$edit = '<div align="center"> <div class="btn btn-success btn-sm" onclick="datos_prod(\''.$nombre.'\',\''.$codigo.'\',\''.$campo.'\',\''.$camocu.'\')"><span class="glyphicon glyphicon-ok"><span></div> </div>';


					$sHtml.='<tr>';
					$sHtml.='<td align="center">'.$i.'</td>';
					$sHtml.='<td align="center">'. $codigo.'</td>';
					$sHtml.='<td>'.$nombre.'</td>';
					$sHtml.='<td align="center">'.$edit.'</td>';
					$sHtml.='</tr>';
					$i++;
				}while($oCon->SiguienteRegistro());          
			}
		}

		$oCon->Free();

		$sHtml .= '</tbody>';
        $sHtml .= '</table>';         
        $sHtml .= '</div>
                                  <div class="modal-footer">
                                     <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                  </div>
                                </div>
                           </div>
                        </div>';

		
	return $sHtml;

}

//FORMULARIO PDF EPICRISIS
//GENERACION DEL REPORTE
function genera_pdf_epicrisis( $paciente,$id_dato, $id_epi){

    global $DSN_Ifx, $DSN;
    session_start();

    $oCon = new Dbo;
    $oCon -> DSN = $DSN;
    $oCon -> Conectar();

    $oConA = new Dbo;
    $oConA -> DSN = $DSN;
    $oConA -> Conectar();

    $oIfx = new Dbo;
    $oIfx -> DSN = $DSN_Ifx;
    $oIfx -> Conectar();

    $oReturn = new xajaxResponse();

    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    
    //VARIABLES REPORTE
	$def='';
	$tran='';


	$sql = "select grpv_cod_med from clinico.grupo_clopv";
		$grpv_cod_med = consulta_string($sql, 'grpv_cod_med', $oCon, '');
		
		//grupo medicos
		$sql = "select clpv_cod_clpv, clpv_nom_clpv
				from saeclpv
				where clpv_cod_empr = $idempresa and
				clpv_clopv_clpv = 'PV' and
				grpv_cod_grpv = '$grpv_cod_med'";
		if($oIfx->Query($sql)){
			if($oIfx->NumFilas() > 0){
				unset($arrayClpv);
				do{
					$arrayClpv[$oIfx->f('clpv_cod_clpv')] = htmlentities($oIfx->f('clpv_nom_clpv')); 
				}while($oIfx->SiguienteRegistro());
			}
		}


		$sql = "select proe_des_proe, clpv_cod_clpv
			from saeproe, saeclpv
			where  proe_cod_proe = clpv_cod_proe and
			clpv_cod_empr = proe_cod_empr";
	if($oIfx->Query($sql)){
		if($oIfx->NumFilas() > 0){
			unset($arrayEspe);
			do{
				$arrayEspe[$oIfx->f('clpv_cod_clpv')] = $oIfx->f('proe_des_proe'); 
			}while($oIfx->SiguienteRegistro());
		}
	}
		

		
	//DATOS DE LA EMPRESA
	$sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_tel_resp
												from saeempr where empr_cod_empr = $idempresa ";
		if ($oIfx->Query($sql)) {
			if ($oIfx->NumFilas() > 0) {
				$razonSocial = trim($oIfx->f('empr_nom_empr'));
				$ruc_empr = $oIfx->f('empr_ruc_empr');
				$dirMatriz = trim($oIfx->f('empr_dir_empr'));
				$empr_path_logo = $oIfx->f('empr_path_logo');
				$empr_tel_resp = $oIfx->f('empr_tel_resp');
				$empr_num_resu = $oIfx->f('empr_num_resu');
			}
		}

	// DATOS DEL PACIENTE INFORMIX
	$sql_pac = "select clpv_sex_clpv,clpv_cod_clpv,clpv_nom_clpv,clpv_secu_hicl from saeclpv where
	clpv_cod_empr = $idempresa and
	clpv_cod_clpv = $paciente ";

	$sexo=consulta_string($sql_pac, 'clpv_sex_clpv', $oIfx, '');
	if($sexo==1){
		$sexo='M';
	}else{
		$sexo='F';
	}

	$historia=consulta_string($sql_pac, 'clpv_secu_hicl', $oIfx, '');

				if(empty($historia)){
					$sqlh="select secu_hist from clinico.datos_clpv where id_dato=$id_dato";
					$historia=consulta_string($sqlh, 'secu_hist', $oCon, '');
				}

	$nombre_pac = htmlentities(consulta_string($sql_pac, 'clpv_nom_clpv', $oIfx, ''));



	
	  //CONSULTA DE POSICIONES PARA ARMAR TABLA DE DIAGNÓSTICOS

	  $sqlpos="select max(posicion) as posicion from clinico.epicrisis_diagnosticos where id_epicrisis=$id_epi";
	  $maxpos=consulta_string($sqlpos, 'posicion', $oCon, '');

	  $htmlcie='';
	  $n5=espacios(57); //ESPACIOS SECCION 3
	  $htmlcie.='<table border="1"  style="width: 100%; margin: 0px;" cellpadding="1" cellspacing="0" >
	  <tr>
	  <th colspan="5" style="font-size:95%;"align="left" width="333.45"><b> 5 DIAGNÓSTICOS INGRESO</b><br>PRE=PRESUNTIVO DEF=DEFINITIVO<br>'.$n5.'CIE&nbsp;&nbsp;PRE&nbsp;&nbsp;DEF</th>
	  <th colspan="5" style="font-size:95%;"align="left" width="333.45"><b> 6 DIAGNÓSTICOS EGRESO</b><br>PRE=PRESUNTIVO DEF=DEFINITIVO<br>'.$n5.'CIE&nbsp;&nbsp;PRE&nbsp;&nbsp;DEF</th>
	  </tr>';
	  if(!empty($maxpos)){
		

			for ($i=1; $i <=$maxpos; $i++) { 
				$htmlcie.='<tr>';

				$sqlcie="select cod_cie, pre_def from clinico.epicrisis_diagnosticos where id_epicrisis=$id_epi and posicion=$i and tipo='I'";
			
				$cod_cie=consulta_string($sqlcie, 'cod_cie', $oCon, '');
				$pre_def=consulta_string($sqlcie, 'pre_def', $oCon, '');
				
				if(!empty($cod_cie)){

					$sqld="select id_cie, codigo_cie, cie
					from clinico.cie10 where id_cie=$cod_cie";
					$cdiag=consulta_string($sqld, 'codigo_cie', $oConA, '');
					$diag=htmlentities(consulta_string($sqld, 'cie', $oConA, ''));

					if($pre_def=='P'){
						$pre='X';
						$def='';
					}
					else{
						$pre='';
						$def='X';

					}

					$htmlcie.='
					<td style="font-size:95%;"align="center" width="30">'.$i.'</td>
					<td style="font-size:95%;"align="left" width="213.45">'.$diag.'</td>
					<td style="font-size:95%;"align="left" width="30">'.$cdiag.'</td>
					<td style="font-size:95%;"align="center" width="30">'.$pre.'</td>
					<td style="font-size:95%;"align="center" width="30">'.$def.'</td>';

				}
				else{
					$htmlcie.='
					<td style="font-size:95%;"align="center" width="30">'.$i.'</td>
					<td style="font-size:95%;"align="left" width="213.45"></td>
					<td style="font-size:95%;"align="left" width="30"></td>
					<td style="font-size:95%;"align="center" width="30"></td>
					<td style="font-size:95%;"align="center" width="30"></td>';

				}

				$sqlcie="select cod_cie, pre_def from clinico.epicrisis_diagnosticos where id_epicrisis=$id_epi and posicion=$i and tipo='A'";
				$cod_cie=consulta_string($sqlcie, 'cod_cie', $oCon, '');
				$pre_def=consulta_string($sqlcie, 'pre_def', $oCon, '');

				if(!empty($cod_cie)){

					$sqld="select id_cie, codigo_cie, cie
					from clinico.cie10 where id_cie=$cod_cie";
					$cdiag=consulta_string($sqld, 'codigo_cie', $oConA, '');
					$diag=htmlentities(consulta_string($sqld, 'cie', $oConA, ''));

					if($pre_def=='P'){
						$pre='X';
						$def='';
					}
					else{
						$pre='';
						$def='X';

					}

					$htmlcie.='
					<td style="font-size:95%;"align="center" width="30">'.$i.'</td>
					<td style="font-size:95%;"align="left" width="213.45">'.$diag.'</td>
					<td style="font-size:95%;"align="left" width="30">'.$cdiag.'</td>
					<td style="font-size:95%;"align="center" width="30">'.$pre.'</td>
					<td style="font-size:95%;"align="center" width="30">'.$def.'</td>';

				}
				else{
					$htmlcie.='
					<td style="font-size:95%;"align="center" width="30">'.$i.'</td>
					<td style="font-size:95%;"align="left" width="213.45"></td>
					<td style="font-size:95%;"align="left" width="30"></td>
					<td style="font-size:95%;"align="center" width="30"></td>
					<td style="font-size:95%;"align="center" width="30"></td>';

				}

				$htmlcie.='</tr>';

			}
	  }
	  else{
		//TABLA VACIA

		$htmlcie.='<tr>
					<td style="font-size:95%;"align="center" width="30">1</td>
					<td style="font-size:95%;"align="left" width="213.45"></td>
					<td style="font-size:95%;"align="left" width="30"></td>
					<td style="font-size:95%;"align="center" width="30"></td>
					<td style="font-size:95%;"align="center" width="30"></td>

					<td style="font-size:95%;"align="center" width="30">1</td>
					<td style="font-size:95%;"align="left" width="213.45"></td>
					<td style="font-size:95%;"align="left" width="30"></td>
					<td style="font-size:95%;"align="center" width="30"></td>
					<td style="font-size:95%;"align="center" width="30"></td>
				</tr>';
				$htmlcie.='<tr>
				<td style="font-size:95%;"align="center" width="30">2</td>
				<td style="font-size:95%;"align="left" width="213.45"></td>
				<td style="font-size:95%;"align="left" width="30"></td>
				<td style="font-size:95%;"align="center" width="30"></td>
				<td style="font-size:95%;"align="center" width="30"></td>

				<td style="font-size:95%;"align="center" width="30">2</td>
				<td style="font-size:95%;"align="left" width="213.45"></td>
				<td style="font-size:95%;"align="left" width="30"></td>
				<td style="font-size:95%;"align="center" width="30"></td>
				<td style="font-size:95%;"align="center" width="30"></td>
			</tr>';

			$htmlcie.='<tr>
					<td style="font-size:95%;"align="center" width="30">3</td>
					<td style="font-size:95%;"align="left" width="213.45"></td>
					<td style="font-size:95%;"align="left" width="30"></td>
					<td style="font-size:95%;"align="center" width="30"></td>
					<td style="font-size:95%;"align="center" width="30"></td>

					<td style="font-size:95%;"align="center" width="30">3</td>
					<td style="font-size:95%;"align="left" width="213.45"></td>
					<td style="font-size:95%;"align="left" width="30"></td>
					<td style="font-size:95%;"align="center" width="30"></td>
					<td style="font-size:95%;"align="center" width="30"></td>
				</tr>';
	  }
	  $htmlcie.='</table>';

	  

	   //CONSULTA DE DATOS PARA ARMAR TABLA DE MEDICOS TRATANTES

	   $sqlmed="select cod_medico,periodo from clinico.epicrisis_medicos where id_epicrisis=$id_epi order by posicion";
	   $htmlmed='';
	   $htmlmed.='<table border="1"  style="width: 100%; margin: 0px;" cellpadding="1" cellspacing="0" >
	   <tr>
	   <th colspan="5" style="font-size:95%;"align="left" width="666.9"><b> 8 MÉDICOS TRATANTES</b></th>
	   </tr>
	   <tr>
		   <td colspan="2" style="font-size:95%;"align="center" width="250"> NOMBRES</td>
		   <td style="font-size:95%;"align="center"> ESPECIALIDAD</td>
		   <td style="font-size:95%;"align="center" width="100"> CÓDIGO</td>
		   <td style="font-size:95%;"align="center" width="183"> PERÍDO DE RESPONSABILIDAD</td>
	   </tr>';
	   if ($oCon->Query($sqlmed)) {
		if ($oCon->NumFilas() > 0) {
			$m=1;
				do{
					$cod_med=$oCon->f('cod_medico');
					$periodo=$oCon->f('periodo');

					$sqlruc="select clpv_ruc_clpv,clpv_nom_clpv from saeclpv where clpv_cod_clpv=$cod_med";
					$clpv_nom_clpv=htmlentities(consulta_string($sqlruc, 'clpv_nom_clpv', $oIfx, ''));
					$ruc_med=consulta_string($sqlruc, 'clpv_ruc_clpv', $oIfx, '');
					$ruc_med=substr($ruc_med,0,10);
					$especialidad=htmlentities($arrayEspe[$cod_med]);

					$htmlmed.='		
					<tr> 
						<td style="font-size:95%;"align="center" width="30">'.$m.'</td>
						<td style="font-size:95%;"align="left" width="220"> '.$clpv_nom_clpv.'</td>
						<td style="font-size:95%;"align="center">'.$especialidad.'</td>
						<td style="font-size:95%;"align="center">'.$ruc_med.'</td>
						<td style="font-size:95%;"align="center">'.$periodo.'</td>
					</tr>';


					$m++;
				}while($oCon->SiguienteRegistro());
			}
			else{
				$htmlmed.='		
					<tr> 
						<td style="font-size:95%;"align="center" width="30">1</td>
						<td style="font-size:95%;"align="left" width="220"> </td>
						<td style="font-size:95%;"align="center"></td>
						<td style="font-size:95%;"align="center"></td>
						<td style="font-size:95%;"align="center"></td>
					</tr>
					<tr> 
						<td style="font-size:95%;"align="center" width="30">2</td>
						<td style="font-size:95%;"align="left" width="220"> </td>
						<td style="font-size:95%;"align="center"></td>
						<td style="font-size:95%;"align="center"></td>
						<td style="font-size:95%;"align="center"></td>
					</tr>
					<tr> 
						<td style="font-size:95%;"align="center" width="30">3</td>
						<td style="font-size:95%;"align="left" width="220"> </td>
						<td style="font-size:95%;"align="center"></td>
						<td style="font-size:95%;"align="center"></td>
						<td style="font-size:95%;"align="center"></td>
					</tr>';
			}
		}
		$htmlmed.='</table>';
		$oCon->Free();

	   



		$sql = "select * from clinico.epicrisis  where
		U_EMPRESA = $idempresa AND
		U_SUCURSAL = $idsucursal AND
		paciente = '$paciente' and id_dato=$id_dato and id_epi=$id_epi";
	
	$id=1;
	if ($oCon->Query($sql)) {
					if ($oCon->NumFilas() > 0) {
							do{
								$fecha_registro=$oCon->f('fecha_registro');
								$defini = $oCon->f('definitiva');
								$transi =$oCon->f('transitoria');
								$leve = $oCon->f('leve');
								$moderada = $oCon->f('moderada');
								$grave = $oCon->f('grave');
								$autorizado = $oCon->f('autorizado');
								if($autorizado==1){
									$autorizado='X';
								}
								$noautorizado = $oCon->f('noautorizado');
								if($noautorizado==2){
									$noautorizado='X';   
								}
								$asintomatico = $oCon->f('asintomatico');
								$defuncionmenos = $oCon->f('defuncionmenos');
								$defuncionmas = $oCon->f('defuncionmas');
								$diastadia = $oCon->f('diastadia');
								$diaincapacidad = $oCon->f('diaincapacidad');

								$resumenclinico = $oCon->f('resumenclinico');
								$resumenevolucion = $oCon->f('resumenevolucion');
								$hallazgo = $oCon->f('hallazgo');
								$resumenx = $oCon->f('resumenx');

								$medicotra=$oCon->f('medicotra');

								if(!empty($medicotra)){
									$sqlruc="select clpv_ruc_clpv from saeclpv where clpv_cod_clpv=$medicotra";
									$ruc_med=consulta_string($sqlruc, 'clpv_ruc_clpv', $oIfx, '');
									$ruc_med=substr($ruc_med,0,10);
								}
								else{
									$ruc_med='';
								}
		
								$condiciones = $oCon->f('condicionegreso');

								if($defini!=''){
									$def='X';
								}
								if($transi!=''){
									$tran='X';
								}
								if($leve!=''){
									$leve='X';
								}
								if($moderada!=''){
									$moderada='X';
								}

								if($grave!=''){
									$grave='X';
								}

								if($asintomatico!=''){
									$asintomatico='X';
								}

								if($defuncionmenos!=''){
									$defuncionmenos='X';
								}

								if($defuncionmas!=''){
									$defuncionmas='X';
								}
								$id++;

							}while($oCon->SiguienteRegistro());
					}
			}
			
	

//VARIABLES REPORTE
$num=1;

$fechamed=date('d-m-Y');
$horamed=date('h:i:s');


    $idempresa = $_SESSION['U_EMPRESA'];
    //DATOS DE LA EMPRESA
    $sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr,empr_num_dire,  
    empr_path_logo, empr_tel_resp,empr_fax_empr,empr_mai_empr,
    empr_ema_repr
    from saeempr where empr_cod_empr = $idempresa ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                    $razonSocial = trim($oIfx->f('empr_nom_empr'));
                    $ruc_empr = $oIfx->f('empr_ruc_empr');
                    $dirMatriz = trim($oIfx->f('empr_dir_empr'));
                    $calles=trim($oIfx->f('empr_num_dire'));
                    $empr_path_logo = $oIfx->f('empr_path_logo');
                    
                    $tel=$oIfx->f('empr_tel_resp');
                    $fax=$oIfx->f('empr_fax_empr');
                    $ema1=$oIfx->f('empr_mai_empr');
                    $ema2=$oIfx->f('empr_ema_repr');       
    }
    }
    $oIfx->Free();
    //ARRAY
    $dirMatriz=strtolower($dirMatriz);
    $dirMatriz=ucwords($dirMatriz);
    $ruta=basename($empr_path_logo);
    ///////LOGO DEL REPORTE ///////////////
    //$arc?img='../../../file/img/'.$imagen_i;
    $arc_img=DIR_FACTELEC."Include/Clases/Formulario/Plugins/reloj/$ruta";

    if (file_exists($arc_img)) {
        $imgData = base64_encode(file_get_contents($arc_img));
        $imagen_base64 = 'data: ' . mime_content_type($arc_img) . ';base64,' . $imgData;


        $logo='<div>
        <img src="'. $imagen_base64 .'" style="
        width:200px;
        object-fit; contain;">
        </div>';

    } else {
        $logo = '';
    }

//$encabezado=encabezado_reportes();
$html = '<table  style="font-size:18px;width: 100%; margin: 0px;" >
        <tr>
        <td rowspan="6" align="left"> '.$logo.' </td>
        <td></td>      
        </tr>
        <tr>
        <td  >'.$dirMatriz.'</td>
        </tr>
        <tr>
            <td >Tel&eacute;fonos: '.$tel.' </td>
        </tr>
        <tr>
            <td >'.$ema1.'</td>
        </tr>
        <tr>
            <td >'.$ema2.'</td>
        </tr>
    </table>  
        <br>
        <table border="1"  style="width: 100%; margin: 0px;">
        <tr>
        <th style="font-size:95%;"align="center" width="180"><b>ESTABLECIMIENTO</b></th>
        <th style="font-size:95%;"align="center" width="245"><b>NOMBRES Y APELLIDOS</b></th>
        <th style="font-size:95%;"align="center" width="55"><b>SEXO(M/F)</b></th>
        <th style="font-size:95%;"align="center" width="60"><b>N° HOJA</b></th>
        <th style="font-size:95%;"align="center"><b>HISTORIA CLÍNICA</b></th>
        </tr>
        <tr>
        <td style="font-size:95%;"align="center">'.$razonSocial.'</td>
        <td style="font-size:95%;"align="center">'.$nombre_pac.'</td>
        <td style="font-size:95%;"align="center">'.$sexo.'</td>
        <td style="font-size:95%;"align="center"></td>
        <td style="font-size:95%;"align="center">'.$historia.'</td>
        </tr>
        </table>

        <br>
		<div style="border:1px solid black;"><b> 1 RESUMEN DEL CUADRO CLÍNICO</b></div>
		<div style="border:1px solid black;text-align:justify">'.$resumenclinico.'</div>
        <br>
		<div style="border:1px solid black;"><b> 2 RESUMEN DE EVOLUCIÓN Y COMPLICACIONES</b></div>
		<div style="border:1px solid black;text-align:justify">'.$resumenevolucion.'</div>
        <br>
		<div style="border:1px solid black;"><b> 3 HALLAZGOS RELEVANTES DE EXÁMENES Y PROCEDIMIENTOS DIAGNÓSTICOS</b></div>
		<div style="border:1px solid black;text-align:justify">'.$hallazgo.'</div>
        <table border="0"  style="width: 100%; margin: 0px;" cellpadding="2" cellspacing="0">
            <tr>
            <td style="font-size:85%;"align="left" ><b>SNS-MPS / HCU-form.006/2008</b></td>
            <td style="font-size:85%;"align="right" ><b> F17A&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;EPICRISIS(1)</b></td>
            </tr>
        </table>';

$fecha_epi=date('d-m-Y',strtotime($fecha_registro));
$hora_epi=date('H:i',strtotime($fecha_registro));

$html.='<br>
	<div style="border:1px solid black;"><b> 4 RESUMEN DE TRATAMIENTO Y PROCEDIMIENTOS TERAPEUTICOS</b></div>
	<div style="border:1px solid black;text-align:justify">'.$resumenx.'</div>
    <br>
	'.$htmlcie.'
	<div style="border:1px solid black;"><b> 7 CONDICIONES DE EGRESO Y PRONÓSTICO</b></div>
	<div style="border:1px solid black;text-align:justify">'.$condiciones.'</div>
     <br>       
	'.$htmlmed.'
    <br>
        <table border="1"  style="width: 100%; margin: 0px;" cellpadding="1" cellspacing="0" >
        <tr>
            <th colspan="12" style="font-size:95%;"align="left" width="666.9"><b> 9 EGRESO</b></th>
        </tr>
        <tr>
            <td style="font-size:80%;"align="right" width="87">ALTA DEFINITIVA</td>
            <td style="font-size:80%;"align="center" width="30">'.$def.'</td>
            <td style="font-size:80%;"align="right" width="80">ASINTOMÁTICO</td>
            <td style="font-size:80%;"align="center" width="30">'.$asintomatico.'</td>
            <td style="font-size:80%;"align="right" width="80">DISCAPACIDAD MODERADA</td>
            <td style="font-size:80%;"align="center" width="30">'.$moderada.'</td>
            <td style="font-size:80%;"align="right" width="80">RETIRO AUTORIZADO</td>
            <td style="font-size:80%;"align="center" width="30">'.$autorizado.'</td>
            <td style="font-size:80%;"align="right" width="80">DEFUNCIÓN MENOS <br> DE 48 HORAS</td>
            <td style="font-size:80%;"align="center" width="30">'.$defuncionmenos.'</td>
            <td style="font-size:80%;"align="right" width="80">DÍAS DE ESTADÍA</td>
            <td style="font-size:80%;"align="center" width="30">'.$diastadia.'</td>
        </tr>
        <tr>
            <td style="font-size:80%;"align="right">ALTA TRANSITORIA</td>
            <td style="font-size:80%;"align="center">'.$tran.'</td>
            <td style="font-size:80%;"align="right">DISCAPACIDAD LEVE</td>
            <td style="font-size:80%;"align="center">'.$leve.'</td>
            <td style="font-size:80%;"align="right">DISCAPACIDAD GRAVE</td>
            <td style="font-size:80%;"align="center">'.$grave.'</td>
            <td style="font-size:80%;"align="right">RETIRO NO AUTORIZADO</td>
            <td style="font-size:80%;"align="center">'.$noautorizado.'</td>
            <td style="font-size:80%;"align="right">DEFUNCIÓN MÁS <br> DE 48 HORAS</td>
            <td style="font-size:80%;"align="center">'.$defuncionmas.'</td>
            <td style="font-size:80%;"align="right">DÍAS DE INCAPACIDAD</td>
            <td style="font-size:80%;"align="center">'.$diaincapacidad.'</td>
        </tr>
        </table>
        <br>
        <table border="1"  style="width: 100%; margin: 0px;" cellpadding="1" cellspacing="0" >
               <tr>
            <td style="font-size:70%;"align="center" width="38"> FECHA</td>
            <td style="font-size:80%;"align="center" width="80">'.$fecha_epi.'</td>
            <td style="font-size:70%;"align="center" width="30"> HORA</td>
            <td style="font-size:80%;"align="center" width="70">'.$hora_epi.'</td>
            <td style="font-size:70%;"align="center" width="72"> NOMBRE DEL PROFESIONAL</td>
            <td style="font-size:70%;"align="center" width="100">'.$arrayClpv[$medicotra].'</td>
            <td style="font-size:70%;"align="center" width="57">'.$ruc_med.'</td>
            <td style="font-size:70%;"align="center" width="35"> FIRMA</td>
            <td style="font-size:70%;"align="center" width="80"></td>
            <td style="font-size:70%;"align="center" width="75"> NÚMERO DE HOJA</td>
            <td style="font-size:70%;"align="center" width="30"></td>
        </tr>
        </table>
        <table border="0"  style="width: 100%; margin: 0px;" cellpadding="2" cellspacing="0">
            <tr>
            <td style="font-size:85%;"align="left" ><b>SNS-MPS / HCU-form.006/2008</b></td>
            <td style="font-size:85%;"align="right" ><b> F17B&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;EPICRISIS(2)</b></td>
            </tr>
        </table>
        </div>';

$dochtml='<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
</head>
<body>
    '.$html.'
</body>
</html>';


                    $documento_html = base64_encode($dochtml);

                    $headers = array(
                        "Content-Type:application/json;charset=utf-8"
                    );

                    $data_html = array(
						"contenido" => $documento_html,
						"opciones" => array(
							"numeracion" => array(
								"habilitado" => true
							)
						));

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_URL, URL_JIREH_DOCUMENTOS."/core/reporte/convertir/html2pdf");
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_html));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $data_pdf = curl_exec($ch);

                    $ruta = DIR_FACTELEC . 'Include/archivos';
                    if (!file_exists($ruta)){
                        mkdir($ruta);
                    }

                    $ruta = DIR_FACTELEC . 'Include/archivos/epicrisis';
                    if (!file_exists($ruta)){
                        mkdir($ruta);
                    }

                    $ruta_pdf = DIR_FACTELEC . 'Include/archivos/epicrisis/'.$id_epi.'_'.$historia.'_epicrisis.pdf';
                    header('Content-Type: application/pdf');
                    file_put_contents($ruta_pdf,$data_pdf);

                    return $oReturn;
  }

  
//MODAL DIGANOSTICOS CIE10
function modal_consulta_cie10($aForm='',$campo, $camocu){

	global $DSN, $DSN_Ifx;
    session_start();
	$oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();
	
	//variables de sesion
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

	$fil = mb_strtoupper($aForm[''.$campo.''],'utf-8');
    $fil = trim($fil);
    if(!empty($fil)){
		$temp="where (codigo_cie like '%$fil%' or cie like '%$fil%')";
    }

	$sHtml .= '<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">LISTADO DE DIAGN&Oacute;STICOS</h4>
		</div>
		<div class="modal-body">';

		$sHtml .= ' <table id="tbcie"  class="table table-striped table-condensed table-bordered table-hover" style="width: 100%; margin-top: 20px;" align="center">';
                        $sHtml .= '<thead>';
                        $sHtml .= ' <tr>
                                            <th align="center">Nro.</td>
                                            <th align="center">Codigo CIE10</th>
                                            <th align="center">Descripci&oacute;n</th>
                                            <th align="center">Seleccionar</th>              
                                        </tr>';
                        $sHtml .= '</thead>';
                        $sHtml .= '<tbody>';

		$sql="select * from clinico.cie10 $temp order by cie limit 500";
		$i=1;
		if($oCon->Query($sql)){
			if($oCon->NumFilas() > 0){
				do{
					$cie=$oCon->f('codigo_cie');
					$nombre=$oCon->f('cie');
					$idcie=$oCon->f('id_cie');
					$clasificacion=$oCon->f('clasificacion');

					$nombre_cie=$cie.' '.$nombre;
					$nombre_cie = str_replace('"', " ", ($nombre_cie));
                    $nombre_cie = str_replace("'", " ", ($nombre_cie));

					$edit = '<div align="center"> <div class="btn btn-success btn-sm" onclick="datos_cie10(\''.$nombre_cie.'\',\''.$idcie.'\',\''.$campo.'\',\''.$camocu.'\')"><span class="glyphicon glyphicon-ok"><span></div> </div>';


					$sHtml.='<tr>';
					$sHtml.='<td align="center">'.$i.'</td>';
					$sHtml.='<td align="center">'. $cie.'</td>';
					$sHtml.='<td>'.$nombre.'</td>';
					//$sHtml.='<td>'.$clasificacion.'</td>';
					$sHtml.='<td align="center">'.$edit.'</td>';
					$sHtml.='</tr>';
					$i++;
				}while($oCon->SiguienteRegistro());          
			}
		}

		$oCon->Free();

		$sHtml .= '</tbody>';
        $sHtml .= '</table>';         
        $sHtml .= '</div>
                                  <div class="modal-footer">
                                     <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                  </div>
                                </div>
                           </div>
                        </div>';

		
	return $sHtml;

}

//MODAL DIGANOSTICOS CIE10
function modal_consulta_tarifario($aForm='',$campo, $camocu){

	global $DSN, $DSN_Ifx;
    session_start();
	$oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();
	
	//variables de sesion
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

	$fil = mb_strtoupper($aForm[''.$campo.''],'utf-8');
    $fil = trim($fil);
    if(!empty($fil)){
		$temp="and (p.prod_nom_prod like '%$fil%' or pr.prbo_cod_prod like '%$fil%')";
    }


	$sqlpar="select clipar_val_clipar from clinico.clipar where clipar_cod_clipar='BODTAR'";
    $bode_tar= consulta_string($sqlpar, 'clipar_val_clipar', $oCon, 0);



	$sHtml .= '<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">LISTADO DE CODIGOS DEL TARIFARIO NACIONAL</h4>
		</div>
		<div class="modal-body">';

		$sHtml .= ' <table id="tbtar"  class="table table-striped table-condensed table-bordered table-hover" style="width: 100%; margin-top: 20px;" align="center">';
                        $sHtml .= '<thead>';
                        $sHtml .= ' <tr>
                                            <th align="center">Nro.</td>
                                            <th align="center">Codigo</th>
                                            <th align="center">Descripci&oacute;n</th>
                                            <th align="center">Seleccionar</th>              
                                        </tr>';
                        $sHtml .= '</thead>';
                        $sHtml .= '<tbody>';

		  $sql = "select pr.prbo_cod_prod, p.prod_nom_prod, pr.prbo_dis_prod , pr.prbo_pco_prod, prod_cod_tpro,
					p.prod_stock_neg,  p.prod_cod_marc, prod_nom_ext
                    from saeprbo pr, saeprod p where
                    p.prod_cod_prod = pr.prbo_cod_prod and
                    p.prod_cod_empr = $idempresa and
                    p.prod_cod_sucu = $idsucursal and
                    pr.prbo_cod_empr = $idempresa and
                    pr.prbo_cod_bode = '$bode_tar' and
					pr.prbo_est_prod = '1' and prod_cod_tpro='1'
                    $temp  
					order by  2 limit 500";

		$i=1;
		if($oCon->Query($sql)){
			if($oCon->NumFilas() > 0){
				do{
					$codigo=$oCon->f('prbo_cod_prod');
					$nombre=$oCon->f('prod_nom_prod');
					

					$proyectada=substr($nombre,0,50).' (COD: '.$codigo.')';
					$proyectada = str_replace('"', " ", ($proyectada));
                    $proyectada = str_replace("'", " ", ($proyectada));

					$edit = '<div align="center"> <div class="btn btn-success btn-sm" onclick="datos_tari(\''.$proyectada.'\',\''.$codigo.'\',\''.$campo.'\',\''.$camocu.'\')"><span class="glyphicon glyphicon-ok"><span></div> </div>';


					$sHtml.='<tr>';
					$sHtml.='<td align="center">'.$i.'</td>';
					$sHtml.='<td align="center">'. $codigo.'</td>';
					$sHtml.='<td>'.$nombre.'</td>';
					$sHtml.='<td align="center">'.$edit.'</td>';
					$sHtml.='</tr>';
					$i++;
				}while($oCon->SiguienteRegistro());          
			}
		}

		$oCon->Free();

		$sHtml .= '</tbody>';
        $sHtml .= '</table>';         
        $sHtml .= '</div>
                                  <div class="modal-footer">
                                     <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                  </div>
                                </div>
                           </div>
                        </div>';

		
	return $sHtml;

}

//PAGINADOR TRIAJE

function paginador_busqueda_pacientes_triaje($aForm='',$order = 0, $page = 0){

	global $DSN, $DSN_Ifx;
    session_start();
  
    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

	$oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();
	
	$oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();
	
	$oConB = new Dbo;
    $oConB->DSN = $DSN;
    $oConB->Conectar();
    //varibales de sesion
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    $perfil = $_SESSION['U_PERFIL'];
    $user_web = $_SESSION['U_ID'];
	

    $oReturn = new xajaxResponse();

    $tipo_pedido = $aForm['tipo_pedido'];
    
    if($tipo_pedido!=''&& $tipo_pedido!='T'){
            $fil="and d.clpv_emer ='$tipo_pedido'";
    }

    $tmp_pac='';
    $paciente=trim(mb_strtoupper($aForm['nom_pac'],'utf-8'));
    if(!empty($paciente)){
        $tmp_pac="and d.nom_clpv like '%$paciente%'";
    }

    $tmp_id='';
    $identificacion=trim($aForm['id_pac']);
    if(!empty($identificacion)){
        $tmp_id="and d.ruc_clpv like '%$identificacion%'";
    }

   

	$sql = "select grpv_cod_med from clinico.grupo_clopv";
	$grpv_cod_med = consulta_string($sql, 'grpv_cod_med', $oCon, '');
	
	//GRUPO MEDICOS
	$sql = "select clpv_cod_clpv, clpv_nom_clpv
			from saeclpv
			where clpv_cod_empr = $idempresa and
			clpv_clopv_clpv = 'PV' and
			grpv_cod_grpv = '$grpv_cod_med'";
	if($oIfx->Query($sql)){
		if($oIfx->NumFilas() > 0){
			unset($arrayClpv);
			do{
				$arrayClpv[$oIfx->f('clpv_cod_clpv')] = $oIfx->f('clpv_nom_clpv'); 
			}while($oIfx->SiguienteRegistro());
		}
	}
	$oIfx->Free();
    //ESPECIALIDADES
	
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

	//PRIORIDAD

	$sql = "select c.cama_cod_cama, c.cama_nom_cama, h.habi_nom_habi
	from clinico.cama c, clinico.habi h
	where 
	c.cama_cod_habi = h.habi_cod_habi and
	c.cama_cod_empr = h.habi_cod_empr and
	c.cama_cod_sucu = h.habi_cod_sucu and
	c.cama_cod_empr = $idempresa and
	c.cama_cod_sucu = $idsucursal";
	if($oCon->Query($sql)){
		if($oCon->NumFilas() > 0){
			unset($arrayCama);
			do{
				$arrayCama[$oCon->f('cama_cod_cama')] = $oCon->f('habi_nom_habi').'/'.$oCon->f('cama_nom_cama');
			}while($oCon->SiguienteRegistro());
		}
	}
	$oCon->Free();

	//PRIORIDAD

	$sql = "select id, nombre, color from clinico.prioridad_triaje";
	if($oCon->Query($sql)){
		if($oCon->NumFilas() > 0){
			unset($arrayTriaje);
			unset($arrayColorTriaje);
			do{
				$arrayTriaje[$oCon->f('id')] = $oCon->f('nombre');
				$arrayColorTriaje[$oCon->f('id')] = $oCon->f('color');
			}while($oCon->SiguienteRegistro());
		}
	}
	$oCon->Free();
   
  $table_op='';
  $table_op .= '<div  class="row form-group col-xs-12 col-sm-12 col-md-12 col-lg-12">
					
                        <div class="form-group col-xs-12 col-sm-12 col-md-5 col-lg-5">
								<label class="control-label" for="nom_pac">PACIENTE</label>                                
									
										<input type="text" class="form-control input-sm" placeholder="APELLIDOS Y NOMBRES" id="nom_pac" name="nom_pac" onkeyup="borra_idpac();"/>
									                             
							</div>

							<div class="form-group col-xs-12 col-sm-12 col-md-4 col-lg-4">
								<label class="control-label" for="id_pac">IDENTIFICACION</label>                                
									
										<input type="text" class="form-control input-sm" placeholder="NUMERO DE IDENTIFICACION" id="id_pac" name="id_pac" onkeyup="borra_nompac();"/>
									                              
							</div>

							<div class="form-group col-xs-12 col-sm-12 col-md-3 col-lg-3"><br>
							<label class="control-label" >&nbsp;&nbsp;&nbsp;</label>

							<i class="btn btn-success btn-sm" onclick="consultar_pacientes();">
                                        <span class="glyphicon glyphicon glyphicon-search"></span>
                                        Consultar
							</i>
								
									
                        	</div>
						
					</div>';

  $table_op .= '<div  class="row form-group col-xs-12 col-sm-12 col-md-12 col-lg-12">

  <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12">
  <div class="table responsive"><table id="tbtriaje"  class="table table-striped table-bordered table-hover table-condensed" style=" width: 100%; margin-bottom: 0px;" >';

  $table_op.= '<thead>';
  $table_op .= ' <tr>
                  <th align="center">Fecha</th>
                  <th align="center">Admisi&oacute;n</th>
                  <th align="center">Paciente</th>
                  <th align="center">Indentificaci&oacute;n</th>
				  <th align="center">Sexo</th>
				  <th align="center">Edad</th>
                  <th align="center">M&eacute;dico</th>
                  <th align="center">Especialidad</th>
				  <th align="center">Cama</th>
				  <th align="center">Triaje</th>
                  <th align="center">Seleccionar</th>
              
              </tr>';
   $table_op .= '</thead>';
   $table_op .= '<tbody>';
	
	
   $NUM_ITEMS_BY_PAGE = 100;

   $total_products =0;

    $sqltotal="SELECT COUNT(*) AS total FROM
    clinico.datos_clpv d
    where d.id_empresa = $idempresa and 
    
    (alta = 'N' or alta is null) $fil $tmp_pac $tmp_id";

    $total_products=consulta_string($sqltotal,'total',$oCon,0);
    //--------------------------------------------------------------------------------------------------------------------
    // INICIO PAGINADOR 
    //--------------------------------------------------------------------------------------------------------------------
    if (!$page) {
        $start = 0;
        $page = 1;
    } else {
        $start = ($page - 1) * $NUM_ITEMS_BY_PAGE;
    }
    //calculo el total de paginas
    $total_pages = ceil($total_products / $NUM_ITEMS_BY_PAGE);

    $table_op .= '<div class="row">';
    $table_op .= '<div class="col-md-12">';
    $table_op .= '<h5><b> Numero de items: ' . $total_products . '</b></h5>';
    $table_op .= '<h6 style="color: #818181">En cada pagina se muestra ' . $NUM_ITEMS_BY_PAGE . ' item ordenados en formato descendente.</h6>';
    $table_op .= '<h6><b>Mostrando la pagina ' . $page . ' de ' . $total_pages . ' paginas.</b></h6>';
    $table_op .= '</div>';
    $table_op .= '</div>';


    $table_op .=  '<nav>';
    $table_op .=  '<ul class="pagination">';

    if ($total_pages > 1) {
        if ($page != 1) {
            $table_op .=  '<li class="page-item"><a href="javascript:void(0);" class="page-link" onclick="paginador(' . $order . ', ' . ($page - 1) . ')"><span aria-hidden="true">&laquo;</span></a></li>';
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            if ($page == $i) {
                $table_op .=  '<li class="page-item active"><a href="javascript:void(0);" class="page-link" href="#">' . $page . '</a></li>';
            } else {
                $table_op .=  '<li class="page-item"><a href="javascript:void(0);" class="page-link" onclick="paginador(' . $order . ', ' . $i . ')" href="index.php?page=' . $i . '">' . $i . '</a></li>';
            }
        }

        if ($page != $total_pages) {
            $table_op .=  '<li class="page-item"><a href="javascript:void(0);" class="page-link" onclick="paginador(' . $order . ', ' . ($page + 1) . ')"><span aria-hidden="true">&raquo;</span></a></li>';
        }
    }
    $table_op .=  '</ul>';
    $table_op .=  '</nav>';
    //--------------------------------------------------------------------------------------------------------------------
    // FIN PAGINADOR 
    //--------------------------------------------------------------------------------------------------------------------


	$sql="SELECT
	d.id_clpv,
	d.id_dato,
	d.nom_clpv,
	d.ruc_clpv,
	d.fecha_admision,
	d.id_tipo_adm,
	d.alta,
	d.id_med_trat,
	d.fecha_naci,
	d.sexo,
	d.id_cama,
	(select h.id_triaje from clinico.hoja008_6 h
			where d.id_clpv = h.id_clpv and
			d.id_dato = h.id_dato) as triaje
	FROM
	clinico.datos_clpv d
	where d.id_empresa = $idempresa and 
	
	(d.alta = 'N' or d.alta is null) $fil $tmp_pac $tmp_id  order by 5 desc LIMIT $NUM_ITEMS_BY_PAGE OFFSET $start" ;

    if($oCon->Query($sql)){
    	if($oCon->NumFilas() > 0){
    		$sHtmlEstado = '';
    		do{

			    $id_dato=$oCon->f('id_dato');
			    $id_clpv=$oCon->f('id_clpv');
			    $nom_clpv=$oCon->f('nom_clpv');
			    $ruc_clie=$oCon->f('ruc_clpv');
			    $fecha_admision=$oCon->f('fecha_admision');
			    $id_tipo_adm=$oCon->f('id_tipo_adm');
			    $alta=$oCon->f('alta');
                $medtrat=$oCon->f('id_med_trat');

				$edad = calcular_edad($oCon->f('fecha_naci'));
				
				$sexo = $oCon->f('sexo');
				$id_triaje = $oCon->f('triaje');
				$id_cama = $oCon->f('id_cama');

				if($sexo == 1){
					$sexAdm = 'HOM';
				}else{
					$sexAdm = 'MUJ';
				}

			   
				
				  
				  
                //DATOS MEDICO TRATANTE
	            if(!empty($medtrat)){

				  $fecha_hora=$fecha_admision;

                  $medico=$arrayClpv[$medtrat];
                  if(empty($medico)){
                    $medico='<span><font color="red">NO REGISTRADO COMO PROVEEDOR</font></span>';
                    $nommed='SIN ASIGNAR';
                    $especialidad ='<span><font color="red">NA</font></span>';		
                  }
                  else{
                    $nommed=$medico;
                    $especialidad = $arrayEspe[$medtrat];
                  }
				

				}
                else{
                    $medico='<span><font color="red">SIN ASIGNAR</font></span>';
                    $nommed='SIN ASIGNAR';
                    $especialidad ='<span><font color="red">NA</font></span>';	 
                }
				
  
				$img = '<div align="center"> <div class="btn btn-success btn-sm" onclick="datos_clpv('.$id_dato.','.$id_clpv.', \''.$nom_clpv.'\', \''.$ruc_clie.'\', \''.$medtrat.'\', \''.$id_consul_medica.'\', \''.$nommed.'\', \''.$fecha_hora.'\')"><span class="glyphicon glyphicon-ok"><span></div> </div>';


                $table_op.='<tr>';
                $table_op.='<td align="center">'.$fecha_admision.'</td>';
                $table_op.='<td align="center">'. $id_dato.'</td>';
                $table_op.='<td>'.$nom_clpv.'</td>';
                $table_op.='<td align="center">'.$ruc_clie.'</td>';
				$table_op .= '<td align="center">' . $sexAdm . '</td>';
                $table_op .= '<td align="center">' . $edad . '</td>';
                $table_op.='<td>'.$medico.'</td>';
                $table_op.='<td align="center">'.$especialidad.'</td>';
				$table_op .= '<td>' . $arrayCama[$id_cama] . '</td>';
				$table_op .= '<td align="center" style="background-color:'.$arrayColorTriaje[$id_triaje].'"><font color="white"><b>' . $arrayTriaje[$id_triaje] . '</b></font></td>';
                $table_op.='<td align="center">'.$img.'</td>';

			}while($oCon->SiguienteRegistro());
    	}
	}
    $oCon->Free();
    $table_op .= '</tbody>';
    $table_op .= '</table></div></div></div>';

	return $table_op;

}


//PAGINADOR PACEINTES

function paginador_busqueda_pacientes($aForm='',$order = 0, $page = 0){

	global $DSN, $DSN_Ifx;
    session_start();
  
    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

	$oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();
	
	$oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();
	
	$oConB = new Dbo;
    $oConB->DSN = $DSN;
    $oConB->Conectar();
    //varibales de sesion
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    $perfil = $_SESSION['U_PERFIL'];
    $user_web = $_SESSION['U_ID'];
	

    $oReturn = new xajaxResponse();

    $tipo_pedido = $aForm['tipo_pedido'];
    
    if($tipo_pedido!=''){
            $fil="and clpv_emer ='$tipo_pedido'";
    }

    $tmp_pac='';
    $paciente=trim(mb_strtoupper($aForm['nom_pac'],'utf-8'));
    if(!empty($paciente)){
        $tmp_pac="and nom_clpv like '%$paciente%'";
    }

    $tmp_id='';
    $identificacion=trim($aForm['id_pac']);
    if(!empty($identificacion)){
        $tmp_id="and ruc_clpv like '%$identificacion%'";
    }

   

	$sql = "select grpv_cod_med from clinico.grupo_clopv";
	$grpv_cod_med = consulta_string($sql, 'grpv_cod_med', $oCon, '');
	
	//GRUPO MEDICOS
	$sql = "select clpv_cod_clpv, clpv_nom_clpv
			from saeclpv
			where clpv_cod_empr = $idempresa and
			clpv_clopv_clpv = 'PV' and
			grpv_cod_grpv = '$grpv_cod_med'";
	if($oIfx->Query($sql)){
		if($oIfx->NumFilas() > 0){
			unset($arrayClpv);
			do{
				$arrayClpv[$oIfx->f('clpv_cod_clpv')] = $oIfx->f('clpv_nom_clpv'); 
			}while($oIfx->SiguienteRegistro());
		}
	}
	$oIfx->Free();
    //ESPECIALIDADES
	
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
   
  $table_op='';
  $table_op .= '<div  class="row form-group col-xs-12 col-sm-12 col-md-12 col-lg-12">
					
                        <div class="form-group col-xs-12 col-sm-12 col-md-5 col-lg-5">
								<label class="control-label" for="nom_pac">PACIENTE</label>                                
									
										<input type="text" class="form-control input-sm" placeholder="APELLIDOS Y NOMBRES" id="nom_pac" name="nom_pac" onkeyup="borra_idpac();"/>
									                             
							</div>

							<div class="form-group col-xs-12 col-sm-12 col-md-4 col-lg-4">
								<label class="control-label" for="id_pac">IDENTIFICACION</label>                                
									
										<input type="text" class="form-control input-sm" placeholder="NUMERO DE IDENTIFICACION" id="id_pac" name="id_pac" onkeyup="borra_nompac();"/>
									                              
							</div>

							<div class="form-group col-xs-12 col-sm-12 col-md-3 col-lg-3"><br>
							<label class="control-label" >&nbsp;&nbsp;&nbsp;</label>

							<i class="btn btn-success btn-sm" onclick="consultar_pacientes();">
                                        <span class="glyphicon glyphicon glyphicon-search"></span>
                                        Consultar
							</i>
								
									
                        	</div>
						
					</div>';

  $table_op .= '<div  class="row form-group col-xs-12 col-sm-12 col-md-12 col-lg-12">

  <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12">
  <div class="table responsive"><table id="tbpac"  class="table table-striped table-bordered table-hover table-condensed" style=" width: 100%; margin-bottom: 0px;" >';

  $table_op.= '<thead>';
  $table_op .= ' <tr>
                  <th align="center">Fecha</th>
                  <th align="center">Admisi&oacute;n</th>
                  <th align="center">Paciente</th>
                  <th align="center">Indentificaci&oacute;n</th>
                  <th align="center">M&eacute;dico</th>
                  <th align="center">Especialidad</th>
                  <th align="center">Seleccionar</th>
              
              </tr>';
   $table_op .= '</thead>';
   $table_op .= '<tbody>';
	
	
   $NUM_ITEMS_BY_PAGE = 100;

   $total_products =0;

    $sqltotal="SELECT COUNT(*) AS total FROM
    clinico.datos_clpv
    where id_empresa = $idempresa and 
    
    (alta = 'N' or alta is null) $fil $tmp_pac $tmp_id";

    $total_products=consulta_string($sqltotal,'total',$oCon,0);
    //--------------------------------------------------------------------------------------------------------------------
    // INICIO PAGINADOR 
    //--------------------------------------------------------------------------------------------------------------------
    if (!$page) {
        $start = 0;
        $page = 1;
    } else {
        $start = ($page - 1) * $NUM_ITEMS_BY_PAGE;
    }
    //calculo el total de paginas
    $total_pages = ceil($total_products / $NUM_ITEMS_BY_PAGE);

    $table_op .= '<div class="row">';
    $table_op .= '<div class="col-md-12">';
    $table_op .= '<h5><b> Numero de items: ' . $total_products . '</b></h5>';
    $table_op .= '<h6 style="color: #818181">En cada pagina se muestra ' . $NUM_ITEMS_BY_PAGE . ' item ordenados en formato descendente.</h6>';
    $table_op .= '<h6><b>Mostrando la pagina ' . $page . ' de ' . $total_pages . ' paginas.</b></h6>';
    $table_op .= '</div>';
    $table_op .= '</div>';


    $table_op .=  '<nav>';
    $table_op .=  '<ul class="pagination">';

    if ($total_pages > 1) {
        if ($page != 1) {
            $table_op .=  '<li class="page-item"><a href="javascript:void(0);" class="page-link" onclick="paginador(' . $order . ', ' . ($page - 1) . ')"><span aria-hidden="true">&laquo;</span></a></li>';
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            if ($page == $i) {
                $table_op .=  '<li class="page-item active"><a href="javascript:void(0);" class="page-link" href="#">' . $page . '</a></li>';
            } else {
                $table_op .=  '<li class="page-item"><a href="javascript:void(0);" class="page-link" onclick="paginador(' . $order . ', ' . $i . ')" href="index.php?page=' . $i . '">' . $i . '</a></li>';
            }
        }

        if ($page != $total_pages) {
            $table_op .=  '<li class="page-item"><a href="javascript:void(0);" class="page-link" onclick="paginador(' . $order . ', ' . ($page + 1) . ')"><span aria-hidden="true">&raquo;</span></a></li>';
        }
    }
    $table_op .=  '</ul>';
    $table_op .=  '</nav>';
    //--------------------------------------------------------------------------------------------------------------------
    // FIN PAGINADOR 
    //--------------------------------------------------------------------------------------------------------------------


	$sql="SELECT
	datos_clpv.id_clpv,
	datos_clpv.id_dato,
	datos_clpv.nom_clpv,
	datos_clpv.ruc_clpv,
	datos_clpv.fecha_admision,
	datos_clpv.id_tipo_adm,
	datos_clpv.alta,
	datos_clpv.id_med_trat
	FROM
	clinico.datos_clpv
	where id_empresa = $idempresa and 
	
	(alta = 'N' or alta is null) $fil $tmp_pac $tmp_id  LIMIT $NUM_ITEMS_BY_PAGE OFFSET $start" ;

    if($oCon->Query($sql)){
    	if($oCon->NumFilas() > 0){
    		$sHtmlEstado = '';
    		do{

			    $id_dato=$oCon->f('id_dato');
			    $id_clpv=$oCon->f('id_clpv');
			    $nom_clpv=$oCon->f('nom_clpv');
			    $ruc_clie=$oCon->f('ruc_clpv');
			    $fecha_admision=$oCon->f('fecha_admision');
			    $id_tipo_adm=$oCon->f('id_tipo_adm');
			    $alta=$oCon->f('alta');
                $medtrat=$oCon->f('id_med_trat');
			   
				
				  
				  
                //DATOS MEDICO TRATANTE
	            if(!empty($medtrat)){

				  $fecha_hora=$fecha_admision;

                  $medico=$arrayClpv[$medtrat];
                  if(empty($medico)){
                    $medico='<span><font color="red">NO REGISTRADO COMO PROVEEDOR</font></span>';
                    $nommed='SIN ASIGNAR';
                    $especialidad ='<span><font color="red">NA</font></span>';		
                  }
                  else{
                    $nommed=$medico;
                    $especialidad = $arrayEspe[$medtrat];
                  }
				

				}
                else{
                    $medico='<span><font color="red">SIN ASIGNAR</font></span>';
                    $nommed='SIN ASIGNAR';
                    $especialidad ='<span><font color="red">NA</font></span>';	 
                }
				
  
				$img = '<div align="center"> <div class="btn btn-success btn-sm" onclick="datos_clpv('.$id_dato.','.$id_clpv.', \''.$nom_clpv.'\', \''.$ruc_clie.'\', \''.$medtrat.'\', \''.$id_consul_medica.'\', \''.$nommed.'\', \''.$fecha_hora.'\')"><span class="glyphicon glyphicon-ok"><span></div> </div>';


                $table_op.='<tr>';
                $table_op.='<td align="center">'.$fecha_admision.'</td>';
                $table_op.='<td align="center">'. $id_dato.'</td>';
                $table_op.='<td>'.$nom_clpv.'</td>';
                $table_op.='<td align="center">'.$ruc_clie.'</td>';
                $table_op.='<td>'.$medico.'</td>';
                $table_op.='<td align="center">'.$especialidad.'</td>';
                $table_op.='<td align="center">'.$img.'</td>';

			}while($oCon->SiguienteRegistro());
    	}
	}
    $oCon->Free();
    $table_op .= '</tbody>';
    $table_op .= '</table></div></div></div>';

	return $table_op;

}

//FORAMTOS FORMUALRIOS MSP
function generar_pdf_iees($id_f){
	//Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();
	
	$oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

	$oConB = new Dbo;
    $oConB->DSN = $DSN;
    $oConB->Conectar();

	$oConC = new Dbo;
    $oConC->DSN = $DSN;
    $oConC->Conectar();

	$oReturn = new xajaxResponse();
	
    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

	$oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

	$idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];



//CONSULTA DE ESPECIALIDADES
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
    $oIfx->Free();

	
	$sql = "select empr_web_color, empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_tel_resp
                                            from saeempr where empr_cod_empr = $idempresa ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $empr_path_logo = $oIfx->f('empr_path_logo');
			$empr_web_color = $oIfx->f('empr_web_color');
            
        }
    }
    $oIfx->Free();

	///DATOS DEL PACIENTE - INSTRUCCION
	$sql="select * from clinico.instruccion";
	if($oCon->Query($sql)){
		if($oCon->Numfilas()>0){
			unset($arreglo_instruccion);
			do{
				$arreglo_instruccion[$oCon->f('id')]=array($oCon->f('instruccion'));
			}while($oCon->SiguienteRegistro());
		}
	}
    $oCon->Free();

    ///SEGURO
	$sql="select * from clinico.tipo_seguro";
	if($oCon->Query($sql)){
		if($oCon->Numfilas()>0){
			unset($arreglo_seguro);
			do{
				$arreglo_seguro[$oCon->f('id')]=array($oCon->f('tipo_seguro'));
			}while($oCon->SiguienteRegistro());
		}
	}
    $oCon->Free();


	///NOMBRE TIPO DE FORMULARIO LADO
	$sql="select * from clinico.tipos_formularios_lados";
	if($oCon->Query($sql)){
		if($oCon->Numfilas()>0){
			unset($arreglo_formularios_lados);
			do{
				$arreglo_formularios_lados[$oCon->f('id')]=array($oCon->f('descripcion'));
			}while($oCon->SiguienteRegistro());
		}
	}
	
    $oCon->Free();
	
	
	$sql = "select grpv_cod_med from clinico.grupo_clopv";
	$grpv_cod_med = consulta_string($sql, 'grpv_cod_med', $oCon, '');
	
	//grupo medicos
	$sql = "select clpv_cod_clpv, clpv_nom_clpv
			from saeclpv
			where clpv_cod_empr = $idempresa and
			clpv_clopv_clpv = 'PV' and
			grpv_cod_grpv = '$grpv_cod_med'";
	if($oIfx->Query($sql)){
		if($oIfx->NumFilas() > 0){
			unset($arrayClpv);
			do{
				$arrayClpv[$oIfx->f('clpv_cod_clpv')] = $oIfx->f('clpv_nom_clpv'); 
			}while($oIfx->SiguienteRegistro());
		}
	}
	$oIfx->Free();

    //FORMATOS FORMULARIOS
    
	$sql="select * from clinico.formulario_iess where id='$id_f'";
    $html='';
	if($oCon->Query($sql)){
		if($oCon->Numfilas()>0){
			do{

				$cod_med=$oCon->f('cod_med');
				$id=$oCon->f('id_tipo_formulario');
				$id_lado=$oCon->f('id_tipo_formulario_lado');
				$ruc_clpv=$oCon->f('ruc_clpv');
				$fecha_registro=substr($oCon->f('fecha_registro'),0,11);
				$hora=substr($oCon->f('fecha_registro'),11,19);
				
				$user_web=$oCon->f('user_web');
				$ruc_pac=$oCon->f('ruc_clpv');


				//CODIGO DE ADMISION Y CODIGO DEL PACIENTE
				$id_dato=$oCon->f('id_dato');
				$id_clpv=$oCon->f('cod_clpv');


				//DATOS CONSULTA MEDICA

				$sql="SELECT
			consulta_medica.id_dato,
			datos_clpv.id_clpv,
			datos_clpv.nom_clpv,
			datos_clpv.ruc_clpv,
			datos_clpv.fecha_admision,
			datos_clpv.fecha_naci,
			datos_clpv.id_tipo_adm,
			datos_clpv.alta,
			consulta_medica.id_consul_medica,
			consulta_medica.clpv_cod_med,
			consulta_medica.fecha_hora
			FROM
			clinico.datos_clpv
			INNER JOIN clinico.consulta_medica ON datos_clpv.id_clpv = consulta_medica.clpv_cod_pac AND datos_clpv.id_dato = consulta_medica.id_dato
			where id_empresa = $idempresa and alta = 'N' and datos_clpv.id_dato=$id_dato " ;

           $fecha_consulta=consulta_string($sql, 'fecha_hora', $oConA, '');
		   $hora_consulta=date("H:i:s",strtotime($fecha_consulta));
		   $fecha_consulta=date('d-m-Y',strtotime($fecha_consulta));

		   $sql="select nom_clpv from clinico.datos_clpv where id_dato=$id_dato and id_empresa = $idempresa";
		   $nom_clpv=consulta_string($sql, 'nom_clpv', $oConA, '');
          
             //DATOS DEL USUARIO

				$sqlu="select concat(usuario_nombre, ' ', usuario_apellido) as nombre from comercial.usuario where usuario_id=$user_web";
				$nombre_usuario=consulta_string($sqlu, 'nombre', $oConB, '');

			//DATOS DEL PACIENTE

				$sql="select * from clinico.datos_clpv where id_dato='$id_dato'";
	
				$M='';
				$F='';
				$SOL='';
				$CAS='';
				$DIV='';
				$VIU='';
				$UL='';
				if($oConA->Query($sql)){
					if($oConA->Numfilas()>0){
						$edad=$oConA->f('edad');
						$sexo=$oConA->f('sexo');
						$estado_civil=$oConA->f('estado_civil');
						$instruccion=$oConA->f('instruccion');
						$tipo_seguro=$oConA->f('tipo_seguro');
						$empresa_trabajo=$oConA->f('empresa_trabajo');
						$direccion=$oConA->f('direccion');
						$telefono=$oConA->f('telefono');

						$clpv_emer=$oConA->f('clpv_emer');

						//TIPO DE ADMISION
						$adm_emer='';
						$adm_cext='';
						$adm_hosp='';
						if($clpv_emer=='E'){
							$adm_emer='X';
						}
						elseif($clpv_emer=='C'){
							$adm_cext='X';
						}elseif($clpv_emer=='A'||$clpv_emer=='U'){
							$adm_hosp='X';
						}
						


						$provincia_resi=$oConA->f('prov_resi');
						$ciudad_resi=$oConA->f('ciud_resi');
						$cod_pais=$oConA->f('id_pais');
						$sqlp="select pais_des_pais from saepais where  pais_cod_pais=$cod_pais";
						$nomb_pais=consulta_string($sqlp, 'pais_des_pais', $oIfx, '');

						$id_naci=$oConA->f('id_nacional');
						
						$secu_hist = $oConA->f('secu_hist');
						$fecha_naci= $oConA->f('fecha_naci');
						if(!empty($fecha_naci)){
							$dia_naci=date('d',strtotime($fecha_naci));
							$mes_naci=date('m',strtotime($fecha_naci));
							$anio_naci=date('Y',strtotime($fecha_naci));
						   }  
						
						if(empty($secu_hist)){
							$sqlh="select clpv_secu_hicl  from saeclpv where clpv_ruc_clpv='$ruc_pac'";
							if($oIfx->Query($sqlh)){
								if($oIfx->NumFilas() > 0){ 
									$secu_hist=$oIfx->f('clpv_secu_hicl');
								}
								else{
									$secu_hist='';
								}
							}
						}
						if($sexo==1){
							$M='X';
						}
						if($sexo==2){
							$F='X';
						}
						if($estado_civil==1){
							$SOL='X';
						}
						if($estado_civil==2){
							$CAS='X';
						}
						if($estado_civil==3){
							$DIV='X';
						}
						if($estado_civil==4){
							$VIU='X';
						}
						if($estado_civil==5){
							$UL='X';
						}
						
					}
				}
				$oConA->Free();

				$sqlfor="select * from clinico.formulario_iess where id_tipo_formulario=$id and id_dato=$id_dato and id=$id_f order by id_tipo_formulario_lado";
				if($oConC->Query($sqlfor)){
					if($oConC->Numfilas()>0){
						do{
							
							$id=$oConC->f('id_tipo_formulario');
							$id_lado=$oConC->f('id_tipo_formulario_lado');
							$id_f=$oConC->f('id');

							///CONSULTA DE DIAGNOSTICOS

					$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and (cod_cie is not null or cod_cie<>'')  and tipo='TABLE'";

					if($oCon->Query($sql)){
						$cie1="";
						$cie_deta1="";
						$tipo_res_c1="";
						$cie2="";
						$cie_deta2="";
						$tipo_res_c2="";
						$cie3="";
						$cie_deta3="";
						$tipo_res_c3="";
						$cie4="";
						$cie_deta4="";
						$tipo_res_c4="";
						$cie5="";
						$cie_deta5="";
						$tipo_res_c5="";
						$cie6="";
						$cie_deta6="";
						$tipo_res_c6="";
						if($oCon->NumFilas()>0){
							$i=1;
							do{
								if($i==1){
									$cie1=$oCon->f('cod_cie');
									$cie_deta1=$oCon->f('respuesta');
									$tipo_res_c1=$oCon->f('tipo_resp_cie');
									$cie_d1='';
									$cie_p1='';
									if($tipo_res_c1=='D'){
										$cie_d1='X';
									}else{
										$cie_p1='X';
									}
								}
								if($i==2){
									$cie2=$oCon->f('cod_cie');
									$cie_deta2=$oCon->f('respuesta');
									$tipo_res_c2=$oCon->f('tipo_resp_cie');
									$cie_d2='';
									$cie_p2='';
									if($tipo_res_c2=='D'){
										$cie_d2='X';
									}else{
										$cie_p2='X';
									}
								}
								if($i==3){
									$cie3=$oCon->f('cod_cie');
									$cie_deta3=$oCon->f('respuesta');
									$tipo_res_c3=$oCon->f('tipo_resp_cie');
									$cie_d3='';
									$cie_p3='';
									if($tipo_res_c3=='D'){
										$cie_d3='X';
									}else{
										$cie_p3='X';
									}
								}
								if($i==4){
									$cie4=$oCon->f('cod_cie');
									$cie_deta4=$oCon->f('respuesta');
									$tipo_res_c4=$oCon->f('tipo_resp_cie');
									$cie_d4='';
									$cie_p4='';
									if($tipo_res_c4=='D'){
										$cie_d4='X';
									}else{
										$cie_p4='X';
									}
								}
								if($i==5){
									$cie5=$oCon->f('cod_cie');
									$cie_deta5=$oCon->f('respuesta');
									$tipo_res_c5=$oCon->f('tipo_resp_cie');
									$cie_d5='';
									$cie_p5='';
									if($tipo_res_c5=='D'){
										$cie_d5='X';
									}else{
										$cie_p5='X';
									}
								}
								if($i==6){
									$cie6=$oCon->f('cod_cie');
									$cie_deta6=$oCon->f('respuesta');
									$tipo_res_c6=$oCon->f('tipo_resp_cie');
									$cie_d6='';
									$cie_p6='';
									if($tipo_res_c6=='D'){
										$cie_d6='X';
									}else{
										$cie_p6='X';
									}
								}
								$i++;
							}while($oCon->SiguienteRegistro());
						}
					}
					$oCon->Free();



				//DISEÑO DE FORMATOS
				switch ($id){
					case 1:
						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and id_input='texto6' and tipo='TEXT'";

						$referencia=consulta_string($sql, 'respuesta', $oCon, '');
						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and id_input='texto7' and tipo='TEXT'";
						
						$establecimiento=consulta_string($sql, 'respuesta', $oCon, '');
						$ban_t=false;
						$html.='<br><br><table  border="1" cellpadding="2" cellspacing="0">
									<tr  style="background-color:'.$empr_web_color.'">
										<td style="font-size:60%;"align="center" width="180"><strong>INSTITUCIÓN DEL SISTEMA</strong></td>
										<td  style="font-size:60%;"align="center" width="160"><strong>UNIDAD OPERATIVA</strong></td>
										<td style="font-size:60%;" align="center" width="60"><strong>COD. UO</strong></td>
										<td colspan="3" style="font-size:60%;" align="center" width="150"><strong>COD. LOCALIZACIÓN</strong></td>
										<td style="font-size:60%;" align="center" width="109.4"><strong>NÚMERO DE HISTORIA CLINICA</strong></td>
									</tr>
									<tr>
										<td  rowspan="2" style="font-size:60%;"align="center">IESS</td>
										<td  rowspan="2" style="font-size:60%;"align="center">'.$razonSocial .'</td>
										<td  rowspan="2"  style="font-size:60%;" align="center">520</td>
										<td style="font-size:40%;"align="center" width="50">PARROQUIA</td>
                						<td style="font-size:40%;"align="center" width="50">CANTÓN</td>
                						<td style="font-size:40%;"align="center" width="50">PROVINCIA</td>
                						<td style="font-size:60%;"align="center"><b>HISTORIA CLÍNICA</b></td> 
									</tr>
									<tr>
										<td style="font-size:40%;"align="center" >13</td>
										<td style="font-size:40%;"align="center">QUITO</td>
										<td style="font-size:40%;"align="center">PICHINCHA</td>
										<td style="font-size:60%;"align="center">'.$secu_hist.'</td>
									</tr>
									</table>
									<table border="1" cellpadding="2" cellspacing="0">
									<tr style="background-color:'.$empr_web_color.'">
									<td style="font-size:50%;"align="center" width="500"> APELLIDO PATERNO&nbsp;&nbsp;&nbsp;APELLIDO MATERNO&nbsp;&nbsp;&nbsp;PRIMER NOMBRE&nbsp;&nbsp;&nbsp;SEGUNDO NOMBRE</td>
									<td style="font-size:50%;"align="center" width="159.4">No CÉDULA DE CIUDADANÍA</td>
									</tr>
									<tr>
									<td style="font-size:50%;"align="center" >'.$nom_clpv.'</td>
									<td style="font-size:50%;"align="center" >'.$ruc_clpv.'</td>
									</tr>
									</table>

									<table border="1" cellpadding="2" cellspacing="0">
									<tr style="background-color:'.$empr_web_color.'">
									<td rowspan="2" style="font-size:50%;"align="center" width="59.4">FECHA DE REFERENCIA</td>
                                    <td rowspan="2" style="font-size:50%;"align="center" width="50">HORA</td>
									<td rowspan="2" style="font-size:50%;"align="center" width="50">EDAD</td>
									<td colspan="2" style="font-size:50%;"align="center"width="50">GENERO</td>
									<td colspan="5"style="font-size:50%;"align="center" width="100">ESTADO CIVIL</td>
									<td rowspan=" 2" style="font-size:40%;"align="center" width="100">INSTRUCCIÓN ÚLTIMO AÑO APROBADO</td>
									<td rowspan=" 2" style="font-size:40%;"align="center" width="125">EMPRESA DONDE TRABAJA</td>
									<td rowspan=" 2" style="font-size:40%;"align="center" width="125">SEGURO DE SALUD</td>  
									</tr>

									<tr>
									<td style="font-size:50%;"align="center">M</td>
									<td style="font-size:50%;"align="center" >F</td>
									<td style="font-size:50%;"align="center" >SOL</td>
									<td style="font-size:50%;"align="center" >CAS</td>
									<td style="font-size:50%;"align="center" >DIV</td>
									<td style="font-size:50%;"align="center" >VIU</td>
									<td style="font-size:50%;"align="center" >U-L</td>
									</tr>
									<tr>
									<td style="font-size:50%;"align="center" >'.$fecha_registro.'</td>
									<td style="font-size:50%;"align="center" >'.$hora.'</td>
									<td style="font-size:50%;"align="center" >'.$edad.'</td>
									<td style="font-size:50%;"align="center" >'.$M.'</td>
									<td style="font-size:50%;"align="center" >'.$F.'</td>
									<td style="font-size:50%;"align="center" >'.$SOL.'</td>
									<td style="font-size:50%;"align="center" >'.$CAS.'</td>
									<td style="font-size:50%;"align="center" >'.$DIV.'</td>
									<td style="font-size:50%;"align="center" >'.$VIU.'</td>
									<td style="font-size:50%;"align="center" >'.$UL.'</td>
									<td style="font-size:50%;"align="center" >'.$arreglo_instruccion[$instruccion][0].'</td>
									<td style="font-size:50%;"align="center" >'.$empresa_trabajo.'</td>
									<td style="font-size:50%;"align="center" >'.$arreglo_seguro[$tipo_seguro][0].'</td>
									</tr>
									</table>
									';

								
						if($id_lado=='2'){
							$text1="SERVICIO QUE CONTRAREFIERE";
						}
						if($id_lado=='1'){
							$text1="SERVICIO QUE REFIERE";
						}
							$html.='
							<table border="1" cellpadding="2" cellspacing="0">
												<tr>
													<td bgcolor="'.$empr_web_color.'"  style="font-size:50%;"align="center" width="100.4">ESTABLECIMIENTO AL QUE SE HACE REFERENCIA</td>
													<td style="font-size:50%;"align="center"width="125">'.$establecimiento.'</td>
													<td bgcolor="'.$empr_web_color.'"  style="font-size:50%;"align="center" width="100">'.$text1.'</td>
													<td style="font-size:50%;"align="center" width="125">'.$referencia.'</td>
													<td bgcolor="'.$empr_web_color.'"  style="font-size:50%;"align="center" width="74.5" ></td>
													<td style="font-size:50%;"align="center" width="30"></td>
													<td  bgcolor="'.$empr_web_color.'" style="font-size:50%;"align="center" width="74.5"></td>
													<td style="font-size:50%;"align="center" width="30"></td>
												</tr>
												
								</table>
								
								';
							$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and id_input<>'texto6' order by id_tipo_formulario_datos";
							 $ban_t=false;

							
							if($oConA->Query($sql)){
								if($oConA->NumFilas()>0){
									do{
										$pregunta=$oConA->f('pregunta');
										$respuesta=$oConA->f('respuesta');
										$tipo=$oConA->f('tipo');
										$id=$oConA->f('id_tipo_formulario_datos');

										if($id!=66){

											if($tipo=='TEXT'||$tipo=='TEXTAREA'||$tipo=='COD'){
												$html.='<br><br><table border="1" cellpadding="2" cellspacing="0">
												<tr>
													<td  bgcolor="'.$empr_web_color.'" width="659.4"><h5>'.$pregunta.'</h5></td>
												</tr>
												<tr>
													<td height="90"  style= width:"659.4" >'.$respuesta.'</td>
												</tr>
											</table>';
											}


										}
										
										if($tipo=='TABLE'&& $ban_t==false){

											$n1=espacios(54);
											$n2=espacios(3);
											$n3=espacios(88);

											
											$html.='
											<br><br><table border="1" cellpadding="2" cellspacing="0">
														<tr>
															<td  colspan="10" bgcolor="'.$empr_web_color.'" style="font-size:80%;"align="left" width="659.4"><b> '.$pregunta.' </b>'.$n1.'CIE'.$n2.'PRE'.$n2.'DEF'.$n3.'CIE'.$n2.'PRE'.$n2.'DEF</td>
															
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="30">1</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">4</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d4.'</td>
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="30">2</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7" >'.$cie_deta2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">5</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7" >'.$cie_deta5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d5.'</td>
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="30">3</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">6</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d6.'</td>
														</tr>
													</table>';
											
											$ban_t=true;
										}
									}while($oConA->SiguienteRegistro());
								}
							}
							
							$html.='<br><br>
							<table   border="1" cellpadding="2" cellspacing="0">
										<tr>
											<td style="font-size:50%;"align="center" width="50">SALA</td>
											<td style="font-size:50%;"align="center" width="60"></td>
											<td style="font-size:50%;"align="center" width="50">CAMA</td>
											<td style="font-size:50%;"align="center" width="60"></td>
											<td style="font-size:50%;"align="center" width="100">PROFESIONAL</td>
											<td style="font-size:50%;"align="center" width="159.4">'.$nombre_usuario.'</td>
											<td style="font-size:50%;"align="center" width="30"></td>
											<td style="font-size:50%;"align="center" width="50">FIRMA</td>
											<td style="font-size:50%;"align="center" width="100"></td>

										</tr>
									</table>
									<table  align="center" style="width:99%;  margin-top:20px;" border=0 cellpadding="0" cellspacing="0" >
										<tr>
											<td align="left" style=" font-size:80%; width:50%;">
												<strong>SNS-MSP / HCU-form.053 / 2008</strong>
											</td>
											<td align="right" style=" font-size:80%; width:50%;">
												<strong>'.$arreglo_formularios_lados[$id_lado][0].'</strong>
											</td>
										</tr>
									</table>';
									$html.='||||||||||';
								
					break;
					case 2:
						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and (tipo='TABLE2' or tipo='MEDICO' or tipo='DATETIME')";
						
						$t2_text4="";
						$t2_text3="";
						$t2_text2="";
						$t2_text1="";
						$t2_text8="";
						$t2_text7="";
						$t2_text6="";
						$t2_text5="";
						if($oConA->Query($sql)){
							if($oConA->NumFilas()>0){
								
								do{
									$id_input=$oConA->f('id_input');
									$tipof=$oConA->f('tipo');
									if($id_input=="t2_text1"){
										$t2_text1=$oConA->f('respuesta');
									}
									if($id_input=="t2_text2"){
										$t2_text2=$oConA->f('respuesta');
									}
									if($id_input=="t2_text3"){
										$t2_text3=$oConA->f('respuesta');
									}
									if($id_input=="t2_text4"){
										$t2_text4=$oConA->f('respuesta');
									}
									if($id_input=="t2_text5"){
										$t2_text5=$oConA->f('respuesta');
									}
									if($id_input=="t2_text6"){
										$t2_text6=$oConA->f('respuesta');
										$normal="";
										$urgente="";
										if($t2_text6=='U'){
												$urgente="X";
										}if($t2_text6=='N'){
												$normal="X";
										}
									}
									if($id_input=="medicoint"){
										$codmed=$oConA->f('respuesta');
										if(!empty($codmed)){
											$sqlc="select clpv_nom_clpv, clpv_ruc_clpv from saeclpv where clpv_cod_clpv=$codmed";
											$t2_text7=consulta_string($sqlc, 'clpv_nom_clpv',$oIfxA,'');
										  }
								
								    }
									if($id_input=="t2_text8"){
										$t2_text8=$oConA->f('respuesta');
									}
									if($id_input=="medicotrad"){
										$codmedt=$oConA->f('respuesta');

										
										if(!empty($codmedt)){
											$sqlc="select clpv_nom_clpv, clpv_ruc_clpv from saeclpv where clpv_cod_clpv=$codmedt";
											$med_tratante=consulta_string($sqlc, 'clpv_nom_clpv',$oIfxA,'');
											$ruc_tratante=substr(consulta_string($sqlc, 'clpv_ruc_clpv',$oIfxA,''),0,10);
										  }
								
			
								    }
									if($tipof=='DATETIME'){
										$fechaf=$oConA->f('respuesta');
										$fecha7=date('Y-m-d',strtotime($fechaf));
										$hora7=date('H:i',strtotime($fechaf));
									}
									
								}while($oConA->SiguienteRegistro());
							}
						}
						$oConA->Free();
						if($sexo==1){
							$sexo='H';
						}
						if($sexo==2){
							$sexo='M';
						}
						/*$html.='<br><br><table  border="1" cellpadding="2" cellspacing="0">
						<tr  style="background-color:'.$empr_web_color.'">
							<td style="font-size:60%;"align="center" width="35%"><strong>ESTABLECIMIENTO SOLICITANTE</strong></td>
							<td style="font-size:50%;"align="center" width="35%"> APELLIDOS y NOMBRES</td>
							<td style="font-size:50%;"align="center" width="10%">SEXO(H-M)</td>
							<td style="font-size:50%;"align="center" width="10%">EDAD</td>
							<td style="font-size:50%;"align="center" width="10%">Nro. HISTORIA CLINICA</td>
						</tr>';

						$html.='<tr>
							<td style="font-size:60%;"align="center" width="35%">'.$razonSocial .'</td>
							<td style="font-size:50%;"align="center" width="35%">'.$nom_clpv.'</td>
							<td style="font-size:50%;"align="center" width="10%">'.$sexo.'</td>
							<td style="font-size:50%;"align="center" width="10%">'.$edad.'</td>
							<td style="font-size:50%;"align="center" width="10%">'.$secu_hist.'</td>
						</tr>';

						
						$html.='</table>';*/
						
						$html.='<table border="1" cellpadding="1" cellspacing="0">
							<thead>
							<tr style="background-color:'.$empr_web_color.'">
									<th style="font-size:90%;"width="100%"><b>A. DATOS DEL ESTABLECIMIENTO Y USUARIO / PACIENTE</b></th>
							</tr>
							<tr>
									<th style="font-size:80%;"align="center" width="22%"><b>INSTITUCIÓN DEL SISTEMA</b></th>
									<th style="font-size:80%;"align="center" width="9%"><b>UNICÓDIGO</b></th>
									<th style="font-size:80%;"align="center" width="23%"><b>ESTABLECIMIENTO DE SALUD</b></th>
									<th style="font-size:80%;"align="center" width="29%"><b>NÚMERO DE HISTORIA CLÍNICA ÚNICA</b></th>
									<th style="font-size:80%;"align="center" width="10%"><b>NÚMERO DE ARCHIVO</b></th>
									<th style="font-size:80%;"align="center" width="7%"><b>No.HOJA</b></th>

							</tr>
							<tr>
									<th style="font-size:80%;"align="center" width="22%"></th>
									<th style="font-size:80%;"align="center" width="9%"></th>                    
									<th style="font-size:80%;"align="center" width="23%">'.$razonSocial.'</th>
									<th style="font-size:80%;"align="center" width="29%">'.$secu_hist.'</th>
									<th style="font-size:80%;"align="center" width="10%"></th>
									<th style="font-size:80%;"align="center" width="7%"></th>
							</tr>
							<tr>
									<th style="font-size:80%;"align="center" width="74%" rowspan="2"><b>APELLIDOS y NOMBRES</b></th>
									<th style="font-size:80%;"align="center" width="7%" rowspan="2"><b>SEXO</b></th>
									<th style="font-size:80%;"align="center" width="7%" rowspan="2"><b>EDAD</b></th>
									<th style="font-size:60%;"align="center" width="12%" colspan="4"><b>CONDICIÓN EDAD</b><br>(MARCAR)</th>
							</tr>
							<tr>
									<th style="font-size:60%;"align="center" width="3%"><b>H</b></th>
									<th style="font-size:60%;"align="center" width="3%"><b>D</b></th>
									<th style="font-size:60%;"align="center" width="3%"><b>M</b></th>
									<th style="font-size:60%;"align="center" width="3%"><b>A</b></th>                                                            
							</tr>
							<tr>
							<th style="font-size:80%;"align="center" width="74%" >'.$nom_clpv.'</th>
									<th style="font-size:80%;"align="center" width="7%" >'.$sexo.'</th>
									<th style="font-size:80%;"align="center" width="7%" >'.$edad.'</th>
									<th style="font-size:60%;"align="center" width="3%"></th>
									<th style="font-size:60%;"align="center" width="3%"></th>
									<th style="font-size:60%;"align="center" width="3%"></th>
									<th style="font-size:60%;"align="center" width="3%"></th>  

							</tr>
							</table>';
							
							$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' order by id_tipo_formulario_datos";
							$ban_t=false;
							$ban_t2=false;
							$ban_t3=false;
							$cont=1;
							if($oConA->Query($sql)){
								if($oConA->NumFilas()>0){
									do{
										$pregunta=$oConA->f('pregunta');
										$respuesta=$oConA->f('respuesta');
										$codform=$oConA->f('id_tipo_formulario_datos');
										$tipo=$oConA->f('tipo');
										$sqlf="select label from clinico.tipos_formulario_datos_lado where id=$codform";
										$titulo=consulta_string($sqlf, 'label', $oConB, '');
										if($tipo=='TEXT'||$tipo=='TEXTAREA'){

											$obligatorio='';
											if(preg_match("/{regobli}/",$pregunta)){
												$n5=espacios(110);
												$pregunta=str_replace('{regobli}','',$pregunta);
												$obligatorio=$n5.'<font size="6">REGISTRAR DE MANERA OBLIGATORIA</font>';
											}
											$html.='<br><br><table   border="1" cellpadding="2" cellspacing="0">
														<tr>
															<td  bgcolor="'.$empr_web_color.'" style="font-size:90%;" width="100%" align="left" ><b>'.$pregunta.'</b>'.$obligatorio.'</td>
														</tr>
														<tr>
															<td height="50"  style="font-size:60%;" width:"100%" align="left">'.$respuesta.'</td>
														</tr>
													</table>';
													$cont++;
													
										}
										if($tipo=='TABLE'&& $ban_t==false){
											$n1=espacios(8);
											$n2=espacios(6);
											$n3=espacios(155);
											$n4=espacios(14);
											$html.='
											<br><br><table border="1" cellpadding="2" cellspacing="0">
														<tr>
															<td  colspan="10" bgcolor="'.$empr_web_color.'" style="font-size:90%;"align="left" width="100%"><b> '.$pregunta.'</b>'.$n4.'<font size="5">PRE=PRESUNTIVO DEF =DEFINITVO'.$n1.'CIE'.$n2.'PRE'.$n2.'DEF'.$n3.'CIE'.$n2.'PRE'.$n2.'DEF</font></td>
															
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="3%">1</td>
															<td height="10"  style="font-size:50%;"align="left" width="36%">'.$cie_deta1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="5%">'.$cie1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_p1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_d1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">4</td>
															<td height="10"  style="font-size:50%;"align="left" width="36%">'.$cie_deta4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="5%">'.$cie4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_p4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_d4.'</td>
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="3%">2</td>
															<td height="10"  style="font-size:50%;"align="left" width="36%" >'.$cie_deta2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="5%">'.$cie2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_p2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_d2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">5</td>
															<td height="10"  style="font-size:50%;"align="left" width="36%" >'.$cie_deta5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="5%">'.$cie5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_p5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_d5.'</td>
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="3%">3</td>
															<td height="10"  style="font-size:50%;"align="left" width="36%">'.$cie_deta3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="5%">'.$cie3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_p3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_d3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">6</td>
															<td height="10"  style="font-size:50%;"align="left" width="36%">'.$cie_deta6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="5%">'.$cie6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_p6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_d6.'</td>
														</tr>
													</table>';
											$ban_t=true;
										}
										if($tipo=='TABLE2'&& $ban_t2==false){
											$html.='<br><br><table  border="1" cellpadding="2" cellspacing="0" >
														<tr>
															<td  colspan="10" bgcolor="'.$empr_web_color.'" style="font-size:90%;" width="100%" align="left" ><b>'.$titulo.'</b></td>
														</tr>


														<tr>
															<td style="font-size:50%;"align="center" width="38%" colspan="6"><b>SERVICIO</b></td>
															<td style="font-size:50%;"align="center" width="26%"><b>ESPECIALIDAD</b></td>
															<td style="font-size:50%;"align="center" width="8%"><b>No. CAMA</b></td>
															<td style="font-size:50%;"align="center" width="10%"><b>No. SALA</b></td>
															<td style="font-size:50%;"align="center" width="18%" colspan="4"><b>URGENTE</b></td>
														</tr>

														<tr>
															<td style="font-size:50%;"align="center" width="9%">EMERGENCIA</td>
															<td style="font-size:50%;"align="center" width="3%">'.$adm_emer.'</td>
															<td style="font-size:50%;"align="center" width="9%">CONSULTA EXTERNA</td>
															<td style="font-size:50%;"align="center" width="3%">'.$adm_cext.'</td>
															<td style="font-size:50%;"align="center" width="11%">HOSPITALIZACIÓN</td>
															<td style="font-size:50%;"align="center" width="3%">'.$adm_hosp.'</td>

															<td style="font-size:50%;"align="center" width="26%">'.$t2_text3.'</td>
															<td style="font-size:50%;"align="center" width="8%">'.$t2_text5.'</td>
															<td style="font-size:50%;"align="center" width="10%" >'.$t2_text4.'</td>

															<td style="font-size:50%;"align="center" width="4%" >SI</td>															
															<td style="font-size:50%;"align="center" width="5%" >'.$urgente.'</td>															
															<td style="font-size:50%;"align="center" width="4%" >NO</td>
															<td style="font-size:50%;"align="center" width="5%" >'.$normal.'</td>																														
														</tr>


														<tr>
															<td style="font-size:50%;"align="center" width="20%"><b>ESPECIALIDAD CONSULTADA</b></td>
															<td style="font-size:50%;" width="80%" >'.$t2_text2.'</td>
														</tr>
																												<tr>
															<td style="font-size:50%;"align="center" width="20%"><b>DESCRIPCIÓN DEL MOTIVO</b></td>
															<td style="font-size:50%;" width="80%" >'.$t2_text8.'</td>
														</tr>
														<tr>
															<td style="font-size:50%;" width="100%"><b>MEDICO INTERCONSULTADO: </b> '.$t2_text7.'</td>
														</tr>

													</table>';
													$ban_t2=true;
										}
										if($tipo=='MEDICO'&& $ban_t3==false){
											$html.='<br><br><table   border="1" cellpadding="2" cellspacing="0">
														<tr>
															<td  colspan="5" bgcolor="'.$empr_web_color.'" style="font-size:90%;" width="100%"; align="left" ><b>'.$titulo.'</b></td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="15%"><b>FECHA</b><br>(aaaa-mm-dd)</td>
															<td style="font-size:60%;"align="center" width="15%"><b>HORA</b></td>
															<td style="font-size:60%;"align="center" width="70%" colspan="2"><b>APELLIDOS Y NOMBRES</b></td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="15%">'.$fecha7.'</td>
															<td style="font-size:60%;"align="center" width="15%">'.$hora7.'</td>
															<td style="font-size:60%;"align="center" width="70%" colspan="2">'.$med_tratante.'</td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="30%" colspan="2"><b>NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</b></td>
															<td style="font-size:60%;"align="center" width="35%"><b>FIRMA</b></td>
															<td style="font-size:60%;"align="center" width="35%"><b>SELLO</b></td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="30%" colspan="2">'.$ruc_tratante.'</td>
															<td style="font-size:60%;"align="center" width="35%"></td>
															<td style="font-size:60%;"align="center" width="35%"></td>
														</tr>

													</table>';


											$ban_t3=true;
										}
									}while($oConA->SiguienteRegistro());
								}
							}
							$oConA->Free();
							$html.='<table   align="center" style="width:99%;  margin-top:20px;" border=0 cellpadding="0" cellspacing="0" >
										<tr>
											<td align="left" style=" font-size:80%; width:50%;">
												<strong>SNS-MSP / HCU-form.007 / 2021</strong>
											</td>
											<td align="right" style=" font-size:80%; width:50%;">
												<strong>'.$arreglo_formularios_lados[$id_lado][0].'</strong>
											</td>
										</tr>
									</table>';
							$html.='||||||||||';		
					break;

					case 3:

						$n2=espacios(150);
						$n3=espacios(30);
						$n4=espacios(7);
						$n5=espacios(35);
						$n6=espacios(52);
                           ///CABECERA 
						$html.='<div>
						<br><br><table border="1" cellpadding="1" cellspacing="0">
							<thead>
							<tr style="background-color:'.$empr_web_color.'">
									<th style="font-size:90%;"width="100%"><b>A. DATOS DEL ESTABLECIMIENTO Y USUARIO / PACIENTE</b></th>
							</tr>
							<tr>
									<th style="font-size:80%;"align="center" width="22%"><b>INSTITUCIÓN DEL SISTEMA</b></th>
									<th style="font-size:80%;"align="center" width="9%"><b>UNICÓDIGO</b></th>
									<th style="font-size:80%;"align="center" width="23%"><b>ESTABLECIMIENTO DE SALUD</b></th>
									<th style="font-size:80%;"align="center" width="29%"><b>NÚMERO DE HISTORIA CLÍNICA ÚNICA</b></th>
									<th style="font-size:80%;"align="center" width="17%"><b>NÚMERO DE ARCHIVO</b></th>
							</tr>
							<tr>
									<th style="font-size:80%;"align="center" width="22%"></th>
									<th style="font-size:80%;"align="center" width="9%"></th>                    
									<th style="font-size:80%;"align="center" width="23%">'.$razonSocial.'</th>
									<th style="font-size:80%;"align="center" width="29%">'.$secu_hist.'</th>
									<th style="font-size:80%;"align="center" width="17%"></th>
							</tr>
							<tr>
									<th style="font-size:80%;"align="center" width="64%" rowspan="2"><b>APELLIDOS y NOMBRES</b></th>
									<th style="font-size:80%;"align="center" width="7%" rowspan="2"><b>SEXO</b></th>
									<th style="font-size:80%;"align="center" width="10%" rowspan="2"><b>FECHA NACIMIENTO</b></th>
									<th style="font-size:80%;"align="center" width="7%" rowspan="2"><b>EDAD</b></th>
									<th style="font-size:60%;"align="center" width="12%" colspan="4"><b>CONDICIÓN EDAD</b><br>(MARCAR)</th>
							</tr>
							<tr>
									<th style="font-size:60%;"align="center" width="3%"><b>H</b></th>
									<th style="font-size:60%;"align="center" width="3%"><b>D</b></th>
									<th style="font-size:60%;"align="center" width="3%"><b>M</b></th>
									<th style="font-size:60%;"align="center" width="3%"><b>A</b></th>                                                            
							</tr>
							<tr>
							<th style="font-size:80%;"align="center" width="64%" >'.$nom_clpv.'</th>
									<th style="font-size:80%;"align="center" width="7%" >'.$sexo.'</th>
									<th style="font-size:80%;"align="center" width="10%" >'.$fecha_naci.'</th>
									<th style="font-size:80%;"align="center" width="7%" >'.$edad.'</th>
									<th style="font-size:60%;"align="center" width="3%"></th>
									<th style="font-size:60%;"align="center" width="3%"></th>
									<th style="font-size:60%;"align="center" width="3%"></th>
									<th style="font-size:60%;"align="center" width="3%"></th>  

							</tr>
							</table>';

						//FORMATO SOLICITUD
						if($id_lado=='5'){

						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and 	tipo='TABLE3'";
						$t3_text7="";
						$t3_text6="";
						$t3_text5="";
						$t3_text4="";
						$t3_text3="";
						$t3_text2="";
						$t3_text1="";
						if($oConA->Query($sql)){
							if($oConA->NumFilas()>0){
								
								do{
									$id_input=$oConA->f('id_input');
									if($id_input=="t3_text6"){
										$t3_text6=$oConA->f('respuesta');
										if($t3_text6=='S'){
											$t3_text6='X';
										}
									}
									if($id_input=="t3_text2"){
										$t3_text2=$oConA->f('respuesta');
									}
									if($id_input=="t3_text3"){
										$t3_text3=$oConA->f('respuesta');
									}
									if($id_input=="t3_text4"){
										$t3_text4=$oConA->f('respuesta');
										if($t3_text4=='S'){
											$t3_text4='X';
										}
									}
									if($id_input=="t3_text5"){
										$t3_text5=$oConA->f('respuesta');
										if($t3_text5=='S'){
											$t3_text5='X';
										}
									}
									if($id_input=="t3_text7"){
										$t3_text7=$oConA->f('respuesta');
										if($t3_text7=='S'){
											$t3_text7='X';
										}
									}
									if($id_input=="t3_text1"){
										$t3_text1=$oConA->f('respuesta');
										$rx="";
										$tomografia="";
										$resonancia="";
										$eco="";
										$procedi="";
										$otros="";
										//$otros_d="";
										if($t3_text1=='T'){
												$tomografia="X";
										}if($t3_text1=='C'){
												$rx="X";
										}
										if($t3_text1=='C'){
												$rx="X";
										}
										if($t3_text1=='R'){
												$resonancia="X";
										}if($t3_text1=='P'){
												$procedi="X";
										}
										if($t3_text1=='O'){
												$otros="X";
										}
									}
									
								}while($oConA->SiguienteRegistro());
							}
						}
						$oConA->Free();
						$html_l='';
						
						if($id_lado=='5'){
							/*$html_l='<table  border="1" cellpadding="2" cellspacing="0" >
																	<tr>
																	<td style="font-size:60%;"align="center" width="100">PUEDE MOVILIZARSE</td>
																	<td style="font-size:60%;"align="center" width="30">'.$t3_text4.'</td>
																	<td style="font-size:60%;"align="center" width="190">PUEDE RETIRARSE VENDAS, APOSITOS O YESOS</td>
																	<td style="font-size:60%;"align="center" width="30">'.$t3_text5.'</td>
																	<td style="font-size:60%;"align="center" width="150">EL MEDICO ESTARÁ PRESENTE EN EL EXAMEN</td>
																	<td style="font-size:60%;"align="center" width="30">'.$t3_text6.'</td>
																	<td style="font-size:60%;"align="center" width="105">TOMA DE RADIOGRAFÍA EN LA CAMA</td>
																	<td style="font-size:60%;"align="center" width="31">'.$t3_text7.'</td>
																	</tr>
															</table>';*/
						}
						else{
							$html_l='';
						}
						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f'";
						if($oConA->Query($sql)){
							if($oConA->NumFilas()>0){
								do{

									$pregunta=$oConA->f('pregunta');
									$respuesta=$oConA->f('respuesta');
									$tipof=$oConA->f('tipo');
									$id_input=$oConA->f('id_input');
									$codform=$oConA->f('id_tipo_formulario_datos');

									

									
									if($id_input=='r_124'){
										if($respuesta==1){
										$urgente='X';
										$rutina='';
										$control='';
										}
										elseif($respuesta==2){
											$urgente='';
										$rutina='X';
										$control='';
										}
										elseif($respuesta==3){
										$urgente='';
										$rutina='';
										$control='X';
										}
									}
									
									
									
									
									if($id_input=="medicotrad"){
										$codmedt=$oConA->f('respuesta');

										
										if(!empty($codmedt)){
											$sqlc="select clpv_nom_clpv, clpv_ruc_clpv from saeclpv where clpv_cod_clpv=$codmedt";
											$med_tratante=consulta_string($sqlc, 'clpv_nom_clpv',$oIfxA,'');
											$ruc_tratante=substr(consulta_string($sqlc, 'clpv_ruc_clpv',$oIfxA,''),0,10);
										  }
								
			
								    }
									if($id_input=="120_fecha"){
										$fechaf=$oConA->f('respuesta');
										$fecha13=date('Y-m-d',strtotime($fechaf));
										$hora13=date('H:i',strtotime($fechaf));
									}
									if($id_input=="125_fecha"){
										$fechaf=$oConA->f('respuesta');
										$fechatoma=date('d-m-Y',strtotime($fechaf));
										
									}

									if($id_input=='texto7'){
										$servicio=$respuesta;
									}
									if($id_input=='texto8'){
										$sala=$respuesta;
									}
									if($id_input=='texto9'){
										$cama=$respuesta;
									}
									 		 

									
								}while($oConA->SiguienteRegistro());
							}
						}

						$oConA->Free();


						$html.='<br><br><table border="1" cellpadding="2" cellspacing="0">
						<tr style="background-color:'.$empr_web_color.'">
							<td style="font-size:90%;"align="left" width="100%"><b>B. SERVICIO Y PRIORIDAD DE ATENCIÓN</b></td>
						</tr>
						
						<tr>
							<td style="font-size:50%;"align="center" width="40%" colspan="6"><b>SERVICIO</b></td>
							<td style="font-size:50%;"align="center" width="20%"><b>ESPECIALIDAD</b></td>
							<td style="font-size:50%;"align="center" width="5%"><b>CAMA</b></td>
							<td style="font-size:50%;"align="center" width="5%"><b>SALA</b></td>
							<td style="font-size:50%;"align="center" width="30%" colspan="6"><b>PRIORIDAD</b></td>
						</tr>
						<tr>
							<td style="font-size:50%;"align="center" width="7%">EMERGENCIA</td>
							<td style="font-size:50%;"align="center" width="3%">'.$adm_emer.'</td>
							<td style="font-size:50%;"align="center" width="12%">CONSULTA EXTERNA</td>
							<td style="font-size:50%;"align="center" width="3%">'.$adm_cext.'</td>
							<td style="font-size:50%;"align="center" width="12%">HOSPITALIZACIÓN</td>
							<td style="font-size:50%;"align="center" width="3%">'.$adm_hosp.'</td>
							
							<td style="font-size:50%;"align="center" width="20%">'.$servicio.'</td>
							<td style="font-size:50%;"align="center" width="5%">'.$cama.'</td>
							<td style="font-size:50%;"align="center" width="5%">'.$sala.'</td>

							<td style="font-size:50%;"align="center" width="7%">URGENTE</td>
							<td style="font-size:50%;"align="center" width="3%">'.$urgente.'</td>
							<td style="font-size:50%;"align="center" width="7%">RUTINA</td>
							<td style="font-size:50%;"align="center" width="3%">'.$rutina.'</td>
							<td style="font-size:50%;"align="center" width="7%">CONTROL</td>
							<td style="font-size:50%;"align="center" width="3%">'.$control.'</td>							
						</tr>
					
						</table>';

						//Fecha de toma
						//'.$fechatoma.'

						$sql="select id from clinico.tipos_formulario_datos_lado where id_tipo_formulario_lado=$id_lado order by orden;";
						if($oIfxA->Query($sql)){
							if($oIfxA->NumFilas()>0){
								do{
						
						
									$id_preg=$oIfxA->f('id');

						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and id_tipo_formulario_datos=$id_preg order by id_tipo_formulario_datos";
						
							$ban_t=false;
							$ban_t2=false;
							$ban_t3=false;
							$ban_t4=false;
							if($oConA->Query($sql)){
								if($oConA->NumFilas()>0){
									do{
										$pregunta=$oConA->f('pregunta');
										$respuesta=$oConA->f('respuesta');
										$id_input=$oConA->f('id_input');
										$tipo=$oConA->f('tipo');

										$texto_adi='';
											if(preg_match("/{regobli}/",$pregunta)){
												$n5=espacios(56);
												$pregunta=str_replace('{regobli}','',$pregunta);
												$texto_adi=$n5.'<font size="6">REGISTRAR DE MANERA OBLIGATORIA EL CUADRO CLÍNICO ACTUAL DEL PACIENTE</font>';
											}

											if(preg_match("/{razsol}/",$pregunta)){
												$n5=espacios(90);
												$pregunta=str_replace('{razsol}','',$pregunta);
												$texto_adi=$n5.'<font size="6">REGISTRAR LAS RAZONES PARA SOLICITAR EL ESTUDIO</font>';
											}
											
										if($tipo=='TEXT'||$tipo=='TEXTAREA'||$tipo=='COD'){
											if($id_input!='texto7'&&$id_input!='texto8'&&$id_input!='texto9'){

												if(preg_match("/MOTIVO DE LA SOLICITUD/",$pregunta)){

													$html.='<br><br><table border="1" cellpadding="2" cellspacing="0">
													<tr>
														<td  bgcolor="'.$empr_web_color.'" style="font-size:90%;" width="100%" align="left"><b>'.$pregunta.'</b>'.$texto_adi.'</td>
													</tr>
													<tr>
														<td style="font-size:60%;" align="center" width="12%"><b>FUM</b><br>(aaaa-mm-dd)</td>
														<td style="font-size:60%;" align="center" width="15%"></td>
														<td style="font-size:60%;" align="center" width="20%"><b>PACIENTE CONTAMINADO</b></td>
														<td style="font-size:60%;" align="center" width="3%">SI</td>
														<td style="font-size:60%;" align="center" width="3%"></td>
														<td style="font-size:60%;" align="center" width="3%">NO</td>
														<td style="font-size:60%;" align="center" width="3%"></td>
														<td style="font-size:60%;" align="center" width="41%"></td>														
													</tr>
													<tr>
														<td  height="50"  style="font-size:60%;" width="100%" align="left">'.$respuesta.'</td>
													</tr>
												</table>';
												}
												else{
													$html.='<br><br><table border="1" cellpadding="2" cellspacing="0">
													<tr>
														<td  bgcolor="'.$empr_web_color.'" style="font-size:90%;" width="100%" align="left"><b>'.$pregunta.'</b>'.$texto_adi.'</td>
													</tr>
													<tr>
														<td  height="50"  style="font-size:60%;" width="100%" align="left">'.$respuesta.'</td>
													</tr>
												</table>';
												}
												
											}
											
										}
										if($tipo=='TABLE'&& $ban_t==false){

											$n1=espacios(8);
											$n2=espacios(6);
											$n3=espacios(155);
											$n4=espacios(14);
											$html.='
											<br><br><table border="1" cellpadding="2" cellspacing="0">
														<tr>
															<td  colspan="10" bgcolor="'.$empr_web_color.'" style="font-size:90%;" align="left" width="100%"><b> '.$pregunta.'</b>'.$n4.'<font size="5">PRE=PRESUNTIVO DEF =DEFINITVO'.$n1.'CIE'.$n2.'PRE'.$n2.'DEF'.$n3.'CIE'.$n2.'PRE'.$n2.'DEF</font></td>
															
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="3%">1</td>
															<td height="10"  style="font-size:50%;"align="left" width="36%">'.$cie_deta1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="5%">'.$cie1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_p1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_d1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">4</td>
															<td height="10"  style="font-size:50%;"align="left" width="36%">'.$cie_deta4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="5%">'.$cie4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_p4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_d4.'</td>
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="3%">2</td>
															<td height="10"  style="font-size:50%;"align="left" width="36%" >'.$cie_deta2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="5%">'.$cie2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_p2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_d2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">5</td>
															<td height="10"  style="font-size:50%;"align="left" width="36%" >'.$cie_deta5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="5%">'.$cie5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_p5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_d5.'</td>
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="3%">3</td>
															<td height="10"  style="font-size:50%;"align="left" width="36%">'.$cie_deta3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="5%">'.$cie3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_p3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_d3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">6</td>
															<td height="10"  style="font-size:50%;"align="left" width="36%">'.$cie_deta6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="5%">'.$cie6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_p6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="3%">'.$cie_d6.'</td>
														</tr>
													</table>';
											$ban_t=true;
										}
										if($tipo=='TABLE3'&& $ban_t2==false){

											//DESCRIPCION $t3_text2
											$html.='<br><br><table border="1" cellpadding="2" cellspacing="0">
														<tr>
															<td  colspan="13" bgcolor="'.$empr_web_color.'"  align="left" ><b>C. ESTUDIO DE IMAGENOLOGÍA SOLICITADO</b></td>
														</tr>
														<tr>
															<td style=" width:9%;  font-size:55%; " align="center">RX<br>CONVENCIONAL</td>
															<td style=" width:2%;  font-size:55%;" align="center">'.$rx.'</td>
															<td style=" width:6%;  font-size:55%; " align="center">RX<br>PORTÁTIL</td>
															<td style=" width:2%;  font-size:55%;" align="center"></td>															
															<td style=" width:8%;  font-size:55%;" align="center">TOMOGRAFIA</td>
															<td style=" width:3%;  font-size:55%;" align="center">'.$tomografia.'</td>
															<td style=" width:8%;  font-size:55%;" align="center">RESONANCIA</td>
															<td style=" width:3%;  font-size:55%;" align="center">'.$resonancia.'</td>
															<td style=" width:7%;  font-size:55%;" align="center">ECOGRAFIA</td>
															<td style=" width:3%;  font-size:55%;" align="center">'.$eco.'</td>
															<td style=" width:8%;  font-size:55%;" align="center">MAMOGRAFÍA</td>
															<td style=" width:3%;  font-size:55%;" align="center"></td>															
															<td style=" width:11%; font-size:55%;" align="center">PROCEDIMIENTOS</td>
															<td style=" width:3%;  font-size:55%;" align="center">'.$procedi.'</td>
															<td style=" width:5%;  font-size:55%;" align="center">OTROS</td>
															<td style=" width:2%;  font-size:55%;" align="center" >'.$otros.'</td>
															<td style=" width:7%;  font-size:55%;" align="center"><b>SEDACIÓN</b></td>
															<td style=" width:3%;  font-size:55%;" align="center">SI</td>
															<td style=" width:2%;  font-size:55%;" align="center"></td>
															<td style=" width:3%;  font-size:55%;" align="center">NO</td>
															<td style=" width:2%;  font-size:55%;" align="center"></td>
														</tr>
														<tr>
															<td colspan="2" style=" font-size:60%;" >DESCRIPCIÓN</td>
															<td colspan="19"style=" font-size:60%;">'.$t3_text3.'</td>
														</tr>
													</table>'.$html_l.'';

													
													$ban_t2=true;
										}
										if($tipo=='MEDICO'&& $ban_t3==false){
											$html.='<br><br><table   border="1" cellpadding="2" cellspacing="0">
														<tr>
															<td  colspan="5" bgcolor="'.$empr_web_color.'" style="font-size:90%;" width="100%"; align="left" ><b>'.$pregunta.'</b></td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="15%"><b>FECHA</b><br>(aaaa-mm-dd)</td>
															<td style="font-size:60%;"align="center" width="15%"><b>HORA</b></td>
															<td style="font-size:60%;"align="center" width="70%" colspan="2"><b>APELLIDOS Y NOMBRES</b></td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="15%">'.$fecha13.'</td>
															<td style="font-size:60%;"align="center" width="15%">'.$hora13.'</td>
															<td style="font-size:60%;"align="center" width="70%" colspan="2">'.$med_tratante.'</td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="30%" colspan="2"><b>NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</b></td>
															<td style="font-size:60%;"align="center" width="35%"><b>FIRMA</b></td>
															<td style="font-size:60%;"align="center" width="35%"><b>SELLO</b></td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="30%" colspan="2">'.$ruc_tratante.'</td>
															<td style="font-size:60%;"align="center" width="35%"></td>
															<td style="font-size:60%;"align="center" width="35%"></td>
														</tr>

													</table>';


											$ban_t3=true;
										}
										if($tipo=='TABLE4'&& $ban_t4==false){

											$html.='<table border="1" cellpadding="2" cellspacing="0">
																			<tr>
																				<td  colspan="6"  bgcolor="'.$empr_web_color.'" style="font-size:100%;"align="left" width="333" ><h5>3 DATOS BASICOS DE ECOGRAFIA OBSTETRICA</h5></td>
																				<td  colspan="8" bgcolor="'.$empr_web_color.'"  style="font-size:100%;"align="left" width="333"   ><h5>4 DATOS BASICOS DE ECOGRAFIA GINECOLOGICA</h5></td>
																			</tr>
																			<tr>
																			<td style="font-size:50%;"align="center" width="60">MEDIDA</td>
																			<td style="font-size:50%;"align="center" width="60">VALOR</td>
																			<td style="font-size:50%;"align="center" width="60">EDAD GEST.</td>
																			<td style="font-size:50%;"align="center" width="60">PESO</td>
																			<td style="font-size:50%;"align="center" width="93" colspan="2">PLACENTA</td>

																			<td style="font-size:50%;"align="center" width="166.5" colspan="4" >UTERO</td>
																			<td style="font-size:50%;"align="center" width="166.5" colspan="4"  >ANEXOS</td>
																			</tr>
																			<tr>
																			<td style="font-size:50%;"align="center" >DIAMETRO<BR>BILATERAL</td>
																			<td style="font-size:50%;"align="center" >'.$t4_text1.'</td>
																			<td style="font-size:50%;"align="center" >'.$t4_text2.'</td>	
																			<td style="font-size:50%;"align="center" >'.$t4_text3.'</td>
																			<td style="font-size:50%;"align="center" >FUNDICA</td>	
																			<td style="font-size:50%;"align="center" >'.$t4_text4.'</td>

																			<td style="font-size:50%;"align="center" width="56.25">ANTEVERSION</td>
																			<td style="font-size:50%;"align="center" width="30">'.$t5_text1.'</td>
																			<td style="font-size:50%;"align="center" width="50.25">FIBROMA</td>
																			<td style="font-size:50%;"align="center" width="30">'.$t5_text2.'</td>

																			<td style="font-size:50%;"align="center" width="53.25">HIDROSALPIX</td>
																			<td style="font-size:50%;"align="center" width="30">'.$t5_text3.'</td>
																			<td style="font-size:50%;"align="center" width="53.25">QUISTE</td>
																			<td style="font-size:50%;"align="center" width="30">'.$t5_text4.'</td>


																			</tr>
																			<tr>
																			<td style="font-size:50%;"align="center" >LONGITUD<BR>FEMUR</td>
																			<td style="font-size:50%;"align="center" >'.$t4_text5.'</td>
																			<td style="font-size:50%;"align="center" >'.$t4_text6.'</td>	
																			<td style="font-size:50%;"align="center" >'.$t4_text7.'</td>
																			<td style="font-size:50%;"align="center" >MARGINAL</td>	
																			<td style="font-size:50%;"align="center" >'.$t4_text8.'</td>
																				
																			<td style="font-size:50%;"align="center" >RETROVERSION</td>
																			<td style="font-size:50%;"align="center" >'.$t5_text5.'</td>
																			<td style="font-size:50%;"align="center" width="50.25" >MIOMA</td>
																			<td style="font-size:50%;"align="center" width="30" >'.$t5_text6.'</td>
																			<td colspan="4" style="font-size:50%;"align="center" width="166.5">CAVIDAD UTERINA</td>
																			</tr>
																			<tr>
																				<td colspan="2" style="font-size:50%;"align="center" width="60">PERIMETRO<BR>ABDOMINAL</td>
																				<td style="font-size:50%;"align="center" >'.$t4_text9.'</td>
																				<td style="font-size:50%;"align="center" >'.$t4_text10.'</td>	
																				<td style="font-size:50%;"align="center" width="60">'.$t4_text11.'</td>
																				<td colspan="2" style="font-size:50%;"align="center" width="46.4">PREVIA</td>	
																				<td style="font-size:50%;"align="center" width="46.6">'.$t4_text12.'</td>

																				<td style="font-size:50%;"align="center" width="56.25">DIU</td>
																				<td style="font-size:50%;"align="center" >'.$t5_text7.'</td>
																				<td style="font-size:50%;"align="center" width="50.25" >AUSENTE</td>
																				<td style="font-size:50%;"align="center" width="30"  >'.$t5_text8.'</td>
																				<td style="font-size:50%;"align="center" width="53.25">VACIA</td>
																				<td style="font-size:50%;"align="center" width="30">'.$t5_text9.'</td>
																				<td style="font-size:50%;"align="center"  width="53.25">OCUPADA</td>
																				<td style="font-size:50%;"align="center" width="30" >'.$t5_text10.'</td>


																			</tr>
																			<tr>
																				
																			<td style="font-size:50%;"align="center" width="53.25">MASCULINO</td>
																			<td style="font-size:50%;"align="center" width="30">'.$masculino_t4.'</td>
																			<td style="font-size:50%;"align="center" width="53.25">FEMENINO</td>
																			<td style="font-size:50%;"align="center" width="30">'.$femenino_t4.'</td>
																			<td style="font-size:50%;"align="center" width="53.25">MULTIPLE</td>
																			<td style="font-size:50%;"align="center" width="30">'.$multiple_t4.'</td>
																			<td style="font-size:50%;"align="center" width="53.25">GRADO DE MADUREZ</td>
																			<td style="font-size:50%;"align="center" width="30">'.$t4_text14.'</td>

																						<td  style="font-size:50%;"align="center"  colspan="2">FONDO DE SACO DOUGLAS</td>
																						<td style="font-size:50%;"align="center"    colspan="6">'.$t5_text11.'</td>
																						</tr>

																					</table>';
													$ban_t4=true;
										}
									}while($oConA->SiguienteRegistro());
								}
							}
							$oConA->Free();

						}while($oIfxA->SiguienteRegistro());
					}
				}
				$oIfxA->Free();


			$html.='<table   align="center" style="width:99%;  margin-top:20px;" border=0 cellpadding="0" cellspacing="0" >
										<tr>
											<td align="left" style=" font-size:80%; width:50%;">
												<strong>SNS-MSP / HCU-form.012A / 2021</strong>
											</td>
											<td align="right" style=" font-size:80%; width:50%;">
												<strong>'.$arreglo_formularios_lados[$id_lado][0].'</strong>
											</td>
										</tr>
									</table>';


						
						}
						//FORMATO INFORME
						if($id_lado=='6'){

							$n1=espacios(5);
							$n2=espacios(10);
							$n3=espacios(40);
							$n4=espacios(30);
							$n5=espacios(7);
							$n6=espacios(35);
							$n7=espacios(52);

							$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and 	tipo='TABLE3'";
						$t3_text7="";
						$t3_text6="";
						$t3_text5="";
						$t3_text4="";
						$t3_text3="";
						$t3_text2="";
						$t3_text1="";
						if($oConA->Query($sql)){
							if($oConA->NumFilas()>0){
								
								do{
									$id_input=$oConA->f('id_input');
									if($id_input=="t3_text6"){
										$t3_text6=$oConA->f('respuesta');
										if($t3_text6=='S'){
											$t3_text6='X';
										}
									}
									if($id_input=="t3_text2"){
										$t3_text2=$oConA->f('respuesta');
									}
									if($id_input=="t3_text3"){
										$t3_text3=$oConA->f('respuesta');
									}
									if($id_input=="t3_text4"){
										$t3_text4=$oConA->f('respuesta');
										if($t3_text4=='S'){
											$t3_text4='X';
										}
									}
									if($id_input=="t3_text5"){
										$t3_text5=$oConA->f('respuesta');
										if($t3_text5=='S'){
											$t3_text5='X';
										}
									}
									if($id_input=="t3_text7"){
										$t3_text7=$oConA->f('respuesta');
										if($t3_text7=='S'){
											$t3_text7='X';
										}
									}
									if($id_input=="t3_text1"){
										$t3_text1=$oConA->f('respuesta');
										$rx="";
										$tomografia="";
										$resonancia="";
										$eco="";
										$procedi="";
										$otros="";
										//$otros_d="";
										if($t3_text1=='T'){
												$tomografia="X";
										}if($t3_text1=='C'){
												$rx="X";
										}
										if($t3_text1=='C'){
												$rx="X";
										}
										if($t3_text1=='R'){
												$resonancia="X";
										}if($t3_text1=='P'){
												$procedi="X";
										}
										if($t3_text1=='O'){
												$otros="X";
										}
									}
									
								}while($oConA->SiguienteRegistro());
							}
						}
						$oConA->Free();

							$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and tipo='TABLE4'";
						
							$t4_text13="";
							$t4_text14="";
							$t4_text11="";
							$t4_text12="";
							$t4_text10="";
							$t4_text9="";
							$t4_text8="";
							$t4_text7="";
							$t4_text6="";
							$t4_text4="";
							$t4_text5="";
							$t4_text3="";
							$t4_text2="";
							$t4_text1="";
							if($oConA->Query($sql)){
								if($oConA->NumFilas()>0){
									
									do{
										$id_input=$oConA->f('id_input');
										if($id_input=="t4_text13"){
											$t4_text13=$oConA->f('respuesta');
											$masculino_t4='';
											$femenino_t4='';
											$multiple_t4='';
											if($t4_text13=='M'){
												$masculino_t4='X';
											}
											if($t4_text13=='F'){
												$femenino_t4='X';
											}
											if($t4_text13=='PL'){
												$multiple_t4='X';
											}
										}
										if($id_input=="t4_text14"){
											$t4_text14=$oConA->f('respuesta');
										}
										if($id_input=="t4_text12"){
											$t4_text12=$oConA->f('respuesta');
											if($t4_text12=='S'){
												$t4_text12='X';
											}
										}
										if($id_input=="t4_text11"){
											$t4_text11=$oConA->f('respuesta');
										}
										if($id_input=="t4_text10"){
											$t4_text10=$oConA->f('respuesta');
										}
										if($id_input=="t4_text9"){
											$t4_text9=$oConA->f('respuesta');
										}
										if($id_input=="t4_text8"){
											$t4_text8=$oConA->f('respuesta');
											if($t4_text8=='S'){
												$t4_text8='X';
											}
										}
										if($id_input=="t4_text7"){
											$t4_text7=$oConA->f('respuesta');
										}
										if($id_input=="t4_text6"){
											$t4_text6=$oConA->f('respuesta');
										}
										if($id_input=="t4_text5"){
											$t4_text5=$oConA->f('respuesta');
										}
										if($id_input=="t4_text4"){
											$t4_text4=$oConA->f('respuesta');
											if($t4_text4=='S'){
												$t4_text4='X';
											}
										}
										if($id_input=="t4_text3"){
											$t4_text3=$oConA->f('respuesta');
										}
										if($id_input=="t4_text2"){
											$t4_text2=$oConA->f('respuesta');
										}
										if($id_input=="t4_text1"){
											$t4_text1=$oConA->f('respuesta');
										}
									}while($oConA->SiguienteRegistro());
								}
							}
							$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and tipo='TABLE5'";
							//echo $sql;exit;
							
							
							$t5_text11="";
							$t5_text12="";
							$t5_text10="";
							$t5_text9="";
							$t5_text8="";
							$t5_text7="";
							$t5_text6="";
							$t5_text4="";
							$t5_text5="";
							$t5_text3="";
							$t5_text2="";
							$t5_text1="";
							if($oConA->Query($sql)){
								if($oConA->NumFilas()>0){
									
									do{
										$id_input=$oConA->f('id_input');
										if($id_input=="t5_text12"){
											$t5_text12=$oConA->f('respuesta');
										}
										if($id_input=="t5_text11"){
											$t5_text11=$oConA->f('respuesta');
											if($t5_text11=='S'){
												$t5_text11='X';
											}
										}
										if($id_input=="t5_text10"){
											$t5_text10=$oConA->f('respuesta');
											if($t5_text10=='S'){
												$t5_text10='X';
											}
										}
										if($id_input=="t5_text9"){
											$t5_text9=$oConA->f('respuesta');
											if($t5_text9=='S'){
												$t5_text9='X';
											}
										}
										if($id_input=="t5_text8"){
											$t5_text8=$oConA->f('respuesta');
											if($t5_text8=='S'){
												$t5_text8='X';
											}
										}
										if($id_input=="t5_text7"){
											$t5_text7=$oConA->f('respuesta');
											if($t5_text7=='S'){
												$t5_text7='X';
											}
										}
										if($id_input=="t5_text6"){
											$t5_text6=$oConA->f('respuesta');
											if($t5_text6=='S'){
												$t5_text6='X';
											}
										}
										if($id_input=="t5_text1"){
											$t5_text1=$oConA->f('respuesta');
											if($t5_text1=='S'){
												$t5_text1='X';
											}
										}
										if($id_input=="t5_text5"){
											$t5_text5=$oConA->f('respuesta');
											if($t5_text5=='S'){
												$t5_text5='X';
											}
										}
										if($id_input=="t5_text4"){
											$t5_text4=$oConA->f('respuesta');
											if($t5_text4=='S'){
												$t5_text4='X';
											}
										}
										if($id_input=="t5_text3"){
											$t5_text3=$oConA->f('respuesta');
											if($t5_text3=='S'){
												$t5_text3='X';
											}
										}
										if($id_input=="t5_text2"){
											$t5_text2=$oConA->f('respuesta');
											if($t5_text2=='S'){
												$t5_text2='X';
											}
										}
										
									}while($oConA->SiguienteRegistro());
								}
							}
							$oConA->Free();


							$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f'";
						if($oConA->Query($sql)){
							if($oConA->NumFilas()>0){
								do{

									$pregunta=$oConA->f('pregunta');
									$respuesta=$oConA->f('respuesta');
									$tipof=$oConA->f('tipo');
									$id_input=$oConA->f('id_input');
									$codform=$oConA->f('id_tipo_formulario_datos');

									

									
									if($id_input=='r_133'){
										if($respuesta==1){
										$urgente='X';
										$rutina='';
										$control='';
										}
										elseif($respuesta==2){
											$urgente='';
										$rutina='X';
										$control='';
										}
										elseif($respuesta==3){
										$urgente='';
										$rutina='';
										$control='X';
										}

									}
									
									
									
									
									if($id_input=="medicotrad"){
										$codmedt=$oConA->f('respuesta');

										
										if(!empty($codmedt)){
											$sqlc="select clpv_nom_clpv, clpv_ruc_clpv from saeclpv where clpv_cod_clpv=$codmedt";
											$med_tratante=consulta_string($sqlc, 'clpv_nom_clpv',$oIfxA,'');
											$ruc_tratante=substr(consulta_string($sqlc, 'clpv_ruc_clpv',$oIfxA,''),0,10);
										  }
								
			
								    }
									if($id_input=="127_fecha"){
										$fechaf=$oConA->f('respuesta');
										$fecha13=date('d-m-Y',strtotime($fechaf));
										$hora13=date('H:i',strtotime($fechaf));
									}
									if($id_input=="134_fecha"){
										$fechaf=$oConA->f('respuesta');
										$fechatoma=date('d-m-Y',strtotime($fechaf));
										
									}

									if($id_input=='texto9'){
										$recibe=$respuesta;
									}
									if($id_input=='texto10'){
										$profesional=$respuesta;
									}

									if($id_input=='texto11'){
										$servicio=$respuesta;
									}
									if($id_input=='texto12'){
										$sala=$respuesta;
									}
									if($id_input=='texto13'){
										$cama=$respuesta;
									}
									 		 

									
								}while($oConA->SiguienteRegistro());
							}
						}

						$oConA->Free();

						$html.='<br><br><table border="1" cellpadding="2" cellspacing="0">
						<tr style="background-color:'.$empr_web_color.'">
							<td style="font-size:90%;"align="left" width="100%"><b>B. DATOS DEL SERVICIO</b></td>
						</tr>
						
						<tr>
							<td style="font-size:50%;"align="center" width="15%">PROFESIONAL QUE REALIZA<br>EL ESTUDIO</td>
							<td style="font-size:50%;"align="center" width="12%">FECHA DE<br>REALIZACIÓN</td>
							<td style="font-size:50%;"align="center" width="20%">PROFESIONAL SOLICITANTE</td>
							<td style="font-size:50%;"align="center" width="40%" colspan="6"><b>SERVICIO</b></td>
							<td style="font-size:50%;"align="center" width="13%"><b>ESPECIALIDAD</b></td>
						</tr>
						<tr>
							<td style="font-size:50%;"align="center" width="15%">'.$recibe.'</td>
							<td style="font-size:50%;"align="center" width="12%">'.$fechatoma.'</td>
							<td style="font-size:50%;"align="center" width="20%">'.$profesional.'</td>

							<td style="font-size:50%;"align="center" width="7%">EMERGENCIA</td>
							<td style="font-size:50%;"align="center" width="3%">'.$adm_emer.'</td>
							<td style="font-size:50%;"align="center" width="12%">CONSULTA EXTERNA</td>
							<td style="font-size:50%;"align="center" width="3%">'.$adm_cext.'</td>
							<td style="font-size:50%;"align="center" width="12%">HOSPITALIZACIÓN</td>
							<td style="font-size:50%;"align="center" width="3%">'.$adm_hosp.'</td>

							<td style="font-size:50%;"align="center" width="13%">'.$servicio.'</td>				
						</tr>
					
						</table>';


						/*$html.='<table border="1" cellpadding="2" cellspacing="0">
						<tr style="background-color:'.$empr_web_color.'">
						<td style="font-size:50%;"align="left" width="659.4">'.$n1.'PERSONA QUE RECIBE'.$n2.'PROFESIONAL SOLICITANTE'.$n3.'SERVICIO'.$n4.'SALA '.$n5.' CAMA '.$n6.' PRIORIDAD '.$n7.' FECHA DE ENTREGA</td>
						</tr>

						<tr>
							<td style="font-size:50%;"align="center" width="14%">'.$recibe.'</td>
							<td style="font-size:50%;"align="center" width="17%">'.$profesional.'</td>
							<td style="font-size:50%;"align="center" width="20%">'.$servicio.'</td>
							<td style="font-size:50%;"align="center" width="5%">'.$sala.'</td>
							<td style="font-size:50%;"align="center" width="5%">'.$cama.'</td>
							<td style="background-color:'.$empr_web_color.'; font-size:50%;"align="center" width="6%">URGENTE</td>
							<td style="font-size:50%;"align="center" width="3%">'.$urgente.'</td>
							<td style="background-color:'.$empr_web_color.'; font-size:50%;"align="center" width="6%">RUTINA</td>
							<td style="font-size:50%;"align="center" width="3%">'.$rutina.'</td>
							<td style="background-color:'.$empr_web_color.'; font-size:50%;"align="center" width="6%">CONTROL</td>
							<td style="font-size:50%;"align="center" width="3%">'.$control.'</td>
							<td style="font-size:50%;"align="center" width="11%">'.$fechatoma.'</td>
						</tr>
						
						</table>';*/


						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' order by id_tipo_formulario_datos";
							$ban_t=false;
							$ban_t2=false;
							$ban_t3=false;
							$ban_t4=false;
							if($oConA->Query($sql)){
								if($oConA->NumFilas()>0){
									do{
										$pregunta=$oConA->f('pregunta');
										$respuesta=$oConA->f('respuesta');
										$id_input=$oConA->f('id_input');
										$tipo=$oConA->f('tipo');
										
										
										if($tipo=='TEXT'||$tipo=='TEXTAREA'||$tipo=='COD'){
											if($id_input!='texto9'&&$id_input!='texto10'&&$id_input!='texto11'&&$id_input!='texto12'&&$id_input!='texto13'){

												$html.='<br><br><table border="1" cellpadding="2" cellspacing="0">
																	<tr>
																		<td  bgcolor="'.$empr_web_color.'" style="font-size:90%;" width="100%" align="left"><b>'.$pregunta.'</b></td>
																	</tr>
																	<tr>
																		<td  height="50"  style="font-size:60%;" style="font-size:90%;" width="100%" align="left">'.$respuesta.'</td>
																	</tr>
																</table>';
												
											
											}
											
										}
										/*if($tipo=='TABLE'&& $ban_t==false){
											$n1=espacios(54);
											$n2=espacios(3);
											$n3=espacios(88);
											$html.='
											<br><br><table border="1" cellpadding="2" cellspacing="0">
														<tr>
															<td  colspan="10" bgcolor="'.$empr_web_color.'" style="font-size:80%;"align="left" width="659.4"><b> '.$pregunta.' </b>'.$n1.'CIE'.$n2.'PRE'.$n2.'DEF'.$n3.'CIE'.$n2.'PRE'.$n2.'DEF</td>
															
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="30">1</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">4</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d4.'</td>
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="30">2</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7" >'.$cie_deta2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">5</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7" >'.$cie_deta5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d5.'</td>
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="30">3</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">6</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d6.'</td>
														</tr>
													</table>';
											$ban_t=true;
										}*/
										if($tipo=='TABLE3'&& $ban_t2==false){
											$html.='<br><br><table border="1" cellpadding="2" cellspacing="0">
														<tr>
															<td  colspan="13" bgcolor="'.$empr_web_color.'"  align="left" ><b>C. ESTUDIO DE IMAGENOLOGÍA SOLICITADO</b></td>
														</tr>
														<tr>
															<td style=" width:9%;  font-size:55%; " align="center">RX<br>CONVENCIONAL</td>
															<td style=" width:2%;  font-size:55%;" align="center">'.$rx.'</td>
															<td style=" width:6%;  font-size:55%; " align="center">RX<br>PORTÁTIL</td>
															<td style=" width:2%;  font-size:55%;" align="center"></td>															
															<td style=" width:8%;  font-size:55%;" align="center">TOMOGRAFIA</td>
															<td style=" width:3%;  font-size:55%;" align="center">'.$tomografia.'</td>
															<td style=" width:8%;  font-size:55%;" align="center">RESONANCIA</td>
															<td style=" width:3%;  font-size:55%;" align="center">'.$resonancia.'</td>
															<td style=" width:7%;  font-size:55%;" align="center">ECOGRAFIA</td>
															<td style=" width:3%;  font-size:55%;" align="center">'.$eco.'</td>
															<td style=" width:8%;  font-size:55%;" align="center">MAMOGRAFÍA</td>
															<td style=" width:3%;  font-size:55%;" align="center"></td>															
															<td style=" width:11%; font-size:55%;" align="center">PROCEDIMIENTOS</td>
															<td style=" width:3%;  font-size:55%;" align="center">'.$procedi.'</td>
															<td style=" width:5%;  font-size:55%;" align="center">OTROS</td>
															<td style=" width:2%;  font-size:55%;" align="center" >'.$otros.'</td>
															<td style=" width:7%;  font-size:55%;" align="center"><b>SEDACIÓN</b></td>
															<td style=" width:3%;  font-size:55%;" align="center">SI</td>
															<td style=" width:2%;  font-size:55%;" align="center"></td>
															<td style=" width:3%;  font-size:55%;" align="center">NO</td>
															<td style=" width:2%;  font-size:55%;" align="center"></td>
														</tr>
														<tr>
															<td colspan="2" style=" font-size:60%;" >DESCRIPCIÓN</td>
															<td colspan="19"style=" font-size:60%;">'.$t3_text3.'</td>
														</tr>
													</table>';

											/*$html.='<br><br><table border="1" cellpadding="2" cellspacing="0">
														<tr>
															<td  colspan="13" bgcolor="'.$empr_web_color.'"  align="left" ><b>'.$pregunta.'</b></td>
														</tr>
														<tr>
															<td style=" width:11%; font-size:60%; " >R-X CONVENCIONAL</td>
															<td style=" width:3%; font-size:60%;" >'.$rx.'</td>
															<td style=" width:10%; font-size:60%;">TOMOGRAFIA</td>
															<td style=" width:3%; font-size:60%;" >'.$tomografia.'</td>
															<td style=" width:9%; font-size:60%;">RESONANCIA</td>
															<td style=" width:3%; font-size:60%;">'.$resonancia.'</td>
															<td style=" width:10%; font-size:60%;">ECOGRAFIA</td>
															<td style=" width:3%; font-size:60%;" >'.$eco.'</td>
															<td style=" width:12%; font-size:60%;">PROCEDIMIENTOS</td>
															<td style=" width:3%; font-size:60%;" >'.$procedi.'</td>
															<td style=" width:10%; font-size:60%;">OTROS</td>
															<td style=" width:3%; font-size:60%;" >'.$otros.'</td>
															<td style=" width:20%; font-size:60%;">'.$t3_text2.'</td>
														</tr>
														<tr>
															<td colspan="2" style=" font-size:60%;" >DESCRIBIR</td>
															<td colspan="11"style=" font-size:60%;">'.$t3_text3.'</td>
														</tr>
													</table>'.$html_l.'';*/

													
													$ban_t2=true;
										}
										if($tipo=='MEDICO'&& $ban_t3==false){
											$html.='<br><br><table   border="1" cellpadding="2" cellspacing="0">
														<tr>
															<td  colspan="5" bgcolor="'.$empr_web_color.'" style="font-size:90%;" width="100%"; align="left" ><b>'.$pregunta.'</b></td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="15%"><b>FECHA</b><br>(aaaa-mm-dd)</td>
															<td style="font-size:60%;"align="center" width="15%"><b>HORA</b></td>
															<td style="font-size:60%;"align="center" width="70%" colspan="2"><b>APELLIDOS Y NOMBRES</b></td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="15%">'.$fecha13.'</td>
															<td style="font-size:60%;"align="center" width="15%">'.$hora13.'</td>
															<td style="font-size:60%;"align="center" width="70%" colspan="2">'.$med_tratante.'</td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="30%" colspan="2"><b>NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</b></td>
															<td style="font-size:60%;"align="center" width="35%"><b>FIRMA</b></td>
															<td style="font-size:60%;"align="center" width="35%"><b>SELLO</b></td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="30%" colspan="2">'.$ruc_tratante.'</td>
															<td style="font-size:60%;"align="center" width="35%"></td>
															<td style="font-size:60%;"align="center" width="35%"></td>
														</tr>

													</table>';


											$ban_t3=true;
										}
										if($tipo=='TABLE4'&& $ban_t4==false){

											/*$html.='<table border="1" cellpadding="2" cellspacing="0">
																			<tr>
																				<td  colspan="6"  bgcolor="'.$empr_web_color.'" style="font-size:100%;"align="left" width="333" ><h5>3 DATOS BASICOS DE ECOGRAFIA OBSTETRICA</h5></td>
																				<td  colspan="8" bgcolor="'.$empr_web_color.'"  style="font-size:100%;"align="left" width="333"   ><h5>4 DATOS BASICOS DE ECOGRAFIA GINECOLOGICA</h5></td>
																			</tr>
																			<tr>
																			<td style="font-size:50%;"align="center" width="60">MEDIDA</td>
																			<td style="font-size:50%;"align="center" width="60">VALOR</td>
																			<td style="font-size:50%;"align="center" width="60">EDAD GEST.</td>
																			<td style="font-size:50%;"align="center" width="60">PESO</td>
																			<td style="font-size:50%;"align="center" width="93" colspan="2">PLACENTA</td>

																			<td style="font-size:50%;"align="center" width="166.5" colspan="4" >UTERO</td>
																			<td style="font-size:50%;"align="center" width="166.5" colspan="4"  >ANEXOS</td>
																			</tr>
																			<tr>
																			<td style="font-size:50%;"align="center" >DIAMETRO<BR>BILATERAL</td>
																			<td style="font-size:50%;"align="center" >'.$t4_text1.'</td>
																			<td style="font-size:50%;"align="center" >'.$t4_text2.'</td>	
																			<td style="font-size:50%;"align="center" >'.$t4_text3.'</td>
																			<td style="font-size:50%;"align="center" >FUNDICA</td>	
																			<td style="font-size:50%;"align="center" >'.$t4_text4.'</td>

																			<td style="font-size:50%;"align="center" width="56.25">ANTEVERSION</td>
																			<td style="font-size:50%;"align="center" width="30">'.$t5_text1.'</td>
																			<td style="font-size:50%;"align="center" width="50.25">FIBROMA</td>
																			<td style="font-size:50%;"align="center" width="30">'.$t5_text2.'</td>

																			<td style="font-size:50%;"align="center" width="53.25">HIDROSALPIX</td>
																			<td style="font-size:50%;"align="center" width="30">'.$t5_text3.'</td>
																			<td style="font-size:50%;"align="center" width="53.25">QUISTE</td>
																			<td style="font-size:50%;"align="center" width="30">'.$t5_text4.'</td>


																			</tr>
																			<tr>
																			<td style="font-size:50%;"align="center" >LONGITUD<BR>FEMUR</td>
																			<td style="font-size:50%;"align="center" >'.$t4_text5.'</td>
																			<td style="font-size:50%;"align="center" >'.$t4_text6.'</td>	
																			<td style="font-size:50%;"align="center" >'.$t4_text7.'</td>
																			<td style="font-size:50%;"align="center" >MARGINAL</td>	
																			<td style="font-size:50%;"align="center" >'.$t4_text8.'</td>
																				
																			<td style="font-size:50%;"align="center" >RETROVERSION</td>
																			<td style="font-size:50%;"align="center" >'.$t5_text5.'</td>
																			<td style="font-size:50%;"align="center" width="50.25" >MIOMA</td>
																			<td style="font-size:50%;"align="center" width="30" >'.$t5_text6.'</td>
																			<td colspan="4" style="font-size:50%;"align="center" width="166.5">CAVIDAD UTERINA</td>
																			</tr>
																			<tr>
																				<td colspan="2" style="font-size:50%;"align="center" width="60">PERIMETRO<BR>ABDOMINAL</td>
																				<td style="font-size:50%;"align="center" >'.$t4_text9.'</td>
																				<td style="font-size:50%;"align="center" >'.$t4_text10.'</td>	
																				<td style="font-size:50%;"align="center" width="60">'.$t4_text11.'</td>
																				<td colspan="2" style="font-size:50%;"align="center" width="46.4">PREVIA</td>	
																				<td style="font-size:50%;"align="center" width="46.6">'.$t4_text12.'</td>

																				<td style="font-size:50%;"align="center" width="56.25">DIU</td>
																				<td style="font-size:50%;"align="center" >'.$t5_text7.'</td>
																				<td style="font-size:50%;"align="center" width="50.25" >AUSENTE</td>
																				<td style="font-size:50%;"align="center" width="30"  >'.$t5_text8.'</td>
																				<td style="font-size:50%;"align="center" width="53.25">VACIA</td>
																				<td style="font-size:50%;"align="center" width="30">'.$t5_text9.'</td>
																				<td style="font-size:50%;"align="center"  width="53.25">OCUPADA</td>
																				<td style="font-size:50%;"align="center" width="30" >'.$t5_text10.'</td>


																			</tr>
																			<tr>
																				
																			<td style="font-size:50%;"align="center" width="53.25">MASCULINO</td>
																			<td style="font-size:50%;"align="center" width="30">'.$masculino_t4.'</td>
																			<td style="font-size:50%;"align="center" width="53.25">FEMENINO</td>
																			<td style="font-size:50%;"align="center" width="30">'.$femenino_t4.'</td>
																			<td style="font-size:50%;"align="center" width="53.25">MULTIPLE</td>
																			<td style="font-size:50%;"align="center" width="30">'.$multiple_t4.'</td>
																			<td style="font-size:50%;"align="center" width="53.25">GRADO DE MADUREZ</td>
																			<td style="font-size:50%;"align="center" width="30">'.$t4_text14.'</td>

																						<td  style="font-size:50%;"align="center"  colspan="2">FONDO DE SACO DOUGLAS</td>
																						<td style="font-size:50%;"align="center"    colspan="6">'.$t5_text11.'</td>
																						</tr>

																					</table>';*/
													$ban_t4=true;
										}
									}while($oConA->SiguienteRegistro());
								}
							}

							$oConA->Free();
							$html.='<p align="justify" style="font-size:9px;">La aproximación diagnóstica emitida en el presente informe, constituye tan solo una prueba complementaria al diagnóstico clínico definitivo, motivo por el cual se recomienda correlacionar con antecedentes clínicos/quirúrgicos, datos clínicos, exámenes de laboratorio complementarios, así como seguimiento imagenológico del paciente.<p>
									<table   align="center" style="width:99%;" border=0 cellpadding="0" cellspacing="0" >
										<tr>
											<td align="left" style=" font-size:80%; width:50%;">
												<strong>SNS-MSP / HCU-form.012B / 2021</strong>
											</td>
											<td align="right" style=" font-size:80%; width:50%;">
												<strong>'.$arreglo_formularios_lados[$id_lado][0].'</strong>
											</td>
										</tr>
									</table>';


						}
					
						$html.='||||||||||';		
					break;
					case 4:
						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and tipo='TABLE6'";
						
						$t6_text5="";
						$t6_text4="";
						$t6_text3="";
						$t6_text2="";
						$t6_text1="";
						if($oConA->Query($sql)){
							if($oConA->NumFilas()>0){
								
								do{
									$id_input=$oConA->f('id_input');
									if($id_input=="t6_text1"){
										$t6_text1=$oConA->f('respuesta');
									}
									if($id_input=="t6_text2"){
										$t6_text2=$oConA->f('respuesta');
									}
									if($id_input=="t6_text3"){
										$t6_text3=$oConA->f('respuesta');
									}
									if($id_input=="t6_text4"){
										$t6_text4=$oConA->f('respuesta');
										$ur="";
										$ru="";
										$co="";
										
										if($t6_text4=='U'){
												$ur="X";
										}if($t6_text4=='C'){
												$co="X";
										}
										if($t6_text4=='R'){
												$ru="X";
										}
										
									}
									if($id_input=="t6_text5"){
										$t6_text5=$oConA->f('respuesta');
										
									}
									
									
									
								}while($oConA->SiguienteRegistro());
							}
						}
						
						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and tipo='TABLE7'";
						$t7_text1="";     
						$t7_text2="";
						$t7_text3="";
						$t7_text4="";
						$t7_text5="";
						$t7_text6="";
						$t7_text7="";
						$t7_text8="";
						$t7_text9="";
						$t7_text10="";
						$t7_text11="";     
						$t7_text12="";
						$t7_text13="";
						$t7_text14="";
						$t7_text15="";
						$t7_text16="";
						$t7_text17="";
						$t7_text18="";
						$t7_text19="";
						$t7_text20="";
						$t7_text21="";     
						$t7_text22="";
						$t7_text23="";
						$t7_text24="";
						$t7_text25="";
						$t7_text26="";
						$t7_text27="";
						$t7_text28="";
						$t7_text29="";
						$t7_text30="";
						$t7_text31="";     
						$t7_text32="";
						$t7_text33="";
						$t7_text34="";
						$t7_text35="";
						$t7_text36="";
						$t7_text37="";
						$t7_text38="";
						$t7_text39="";
						$t7_text40="";
						$t7_text41="";     
						$t7_text42="";
						$t7_text43="";
						$t7_text44="";
						$t7_text45="";
						$t7_text46="";
						$t7_text47="";
						$t7_text48="";
						$t7_text49="";
						$t7_text50="";
						$t7_text51="";     
						$t7_text52="";
						$t7_text53="";
						$t7_text54="";
						$t7_text55="";
						$t7_text56="";
						if($oConA->Query($sql)){
							
							if($oConA->NumFilas()>0){
								
								do{
									$id_input=$oConA->f('id_input');
									
									if($id_input=="t7_text1"){

										
										$t7_text1=$oConA->f('respuesta');
										if($t7_text1=='S'){
											$t7_text1='X';
										}
									}
									if($id_input=="t7_text2"){
										$t7_text2=$oConA->f('respuesta');
										if($t7_text2=='S'){
											$t7_text2='X';
										}
									}
									if($id_input=="t7_text3"){
										$t7_text3=$oConA->f('respuesta');
										if($t7_text3=='S'){
											$t7_text3='X';
										}
									}
									if($id_input=="t7_text4"){
										$t7_text4=$oConA->f('respuesta');
										if($t7_text4=='S'){
											$t7_text4='X';
										}
									}
									if($id_input=="t7_text5"){
										$t7_text5=$oConA->f('respuesta');
										if($t7_text5=='S'){
											$t7_text5='X';
										}
									}
									if($id_input=="t7_text6"){
										$t7_text6=$oConA->f('respuesta');
										if($t7_text6=='S'){
											$t7_text6='X';
										}
									}
									if($id_input=="t7_text7"){
										$t7_text7=$oConA->f('respuesta');
										if($t7_text7=='S'){
											$t7_text7='X';
										}
									}
									if($id_input=="t7_text8"){
										$t7_text8=$oConA->f('respuesta');
										if($t7_text8=='S'){
											$t7_text8='X';
										}
									}
									if($id_input=="t7_text9"){
										$t7_text9=$oConA->f('respuesta');
										if($t7_text9=='S'){
											$t7_text9='X';
										}
									}
									if($id_input=="t7_text10"){
										$t7_text10=$oConA->f('respuesta');
										if($t7_text10=='S'){
											$t7_text10='X';
										}
									}
									if($id_input=="t7_text11"){
										$t7_text11=$oConA->f('respuesta');
										if($t7_text11=='S'){
											$t7_text11='X';
										}
									}
									if($id_input=="t7_text12"){
										$t7_text12=$oConA->f('respuesta');
										if($t7_text12=='S'){
											$t7_text12='X';
										}
									}
									if($id_input=="t7_text13"){
										$t7_text13=$oConA->f('respuesta');
										if($t7_text13=='S'){
											$t7_text13='X';
										}
									}
									if($id_input=="t7_text14"){
										$t7_text14=$oConA->f('respuesta');
										if($t7_text14=='S'){
											$t7_text14='X';
										}
									}
									if($id_input=="t7_text15"){
										$t7_text15=$oConA->f('respuesta');
										if($t7_text15=='S'){
											$t7_text15='X';
										}
									}
									if($id_input=="t7_text16"){
										$t7_text16=$oConA->f('respuesta');
										if($t7_text16=='S'){
											$t7_text16='X';
										}
									}
									if($id_input=="t7_text17"){
										$t7_text17=$oConA->f('respuesta');
										if($t7_text17=='S'){
											$t7_text17='X';
										}
									}
									if($id_input=="t7_text18"){
										$t7_text18=$oConA->f('respuesta');
										if($t7_text18=='S'){
											$t7_text18='X';
										}
									}
									if($id_input=="t7_text19"){
										$t7_text19=$oConA->f('respuesta');
										if($t7_text19=='S'){
											$t7_text19='X';
										}
									}
									if($id_input=="t7_text20"){
										$t7_text20=$oConA->f('respuesta');
										if($t7_text20=='S'){
											$t7_text20='X';
										}
									}
									if($id_input=="t7_text21"){
										$t7_text21=$oConA->f('respuesta');
										if($t7_text21=='S'){
											$t7_text21='X';
										}
									}
									if($id_input=="t7_text22"){
										$t7_text22=$oConA->f('respuesta');
										if($t7_text22=='S'){
											$t7_text22='X';
										}
									}
									if($id_input=="t7_text23"){
										$t7_text23=$oConA->f('respuesta');
										if($t7_text23=='S'){
											$t7_text23='X';
										}
									}
									if($id_input=="t7_text24"){
										$t7_text24=$oConA->f('respuesta');
										if($t7_text24=='S'){
											$t7_text24='X';
										}
									}
									if($id_input=="t7_text25"){
										$t7_text25=$oConA->f('respuesta');
										if($t7_text25=='S'){
											$t7_text25='X';
										}
									}
									if($id_input=="t7_text26"){
										$t7_text26=$oConA->f('respuesta');
										if($t7_text26=='S'){
											$t7_text26='X';
										}
									}
									if($id_input=="t7_text27"){
										$t7_text27=$oConA->f('respuesta');
										if($t7_text27=='S'){
											$t7_text27='X';
										}
									}
									if($id_input=="t7_text28"){
										$t7_text28=$oConA->f('respuesta');
										if($t7_text28=='S'){
											$t7_text28='X';
										}
									}
									if($id_input=="t7_text29"){
										$t7_text29=$oConA->f('respuesta');
										if($t7_text29=='S'){
											$t7_text29='X';
										}
									}
									if($id_input=="t7_text30"){
										$t7_text30=$oConA->f('respuesta');
										if($t7_text30=='S'){
											$t7_text30='X';
										}
									}
									if($id_input=="t7_text31"){
										$t7_text31=$oConA->f('respuesta');
										if($t7_text31=='S'){
											$t7_text31='X';
										}
									}
									if($id_input=="t7_text32"){
										$t7_text32=$oConA->f('respuesta');
										if($t7_text32=='S'){
											$t7_text32='X';
										}
									}
									if($id_input=="t7_text33"){
										$t7_text33=$oConA->f('respuesta');
										if($t7_text33=='S'){
											$t7_text33='X';
										}
									}
									if($id_input=="t7_text34"){
										$t7_text34=$oConA->f('respuesta');
										if($t7_text34=='S'){
											$t7_text34='X';
										}
									}
									if($id_input=="t7_text35"){
										$t7_text35=$oConA->f('respuesta');
										if($t7_text35=='S'){
											$t7_text35='X';
										}
									}
									if($id_input=="t7_text36"){
										$t7_text36=$oConA->f('respuesta');
										if($t7_text36=='S'){
											$t7_text36='X';
										}
									}
									if($id_input=="t7_text37"){
										$t7_text37=$oConA->f('respuesta');
										if($t7_text37=='S'){
											$t7_text37='X';
										}
									}
									if($id_input=="t7_text38"){
										$t7_text38=$oConA->f('respuesta');
										if($t7_text38=='S'){
											$t7_text38='X';
										}
									}
									if($id_input=="t7_text39"){
										$t7_text39=$oConA->f('respuesta');
										if($t7_text39=='S'){
											$t7_text39='X';
										}
									}
									if($id_input=="t7_text40"){
										$t7_text40=$oConA->f('respuesta');
										if($t7_text40=='S'){
											$t7_text40='X';
										}
									}
									if($id_input=="t7_text41"){
										$t7_text41=$oConA->f('respuesta');
										if($t7_text41=='S'){
											$t7_text41='X';
										}
									}
									if($id_input=="t7_text42"){
										$t7_text42=$oConA->f('respuesta');
										if($t7_text42=='S'){
											$t7_text42='X';
										}
									}
									if($id_input=="t7_text43"){
										$t7_text43=$oConA->f('respuesta');
										if($t7_text43=='S'){
											$t7_text43='X';
										}
									}
									if($id_input=="t7_text44"){
										$t7_text44=$oConA->f('respuesta');
										if($t7_text44=='S'){
											$t7_text44='X';
										}
									}
									if($id_input=="t7_text45"){
										$t7_text45=$oConA->f('respuesta');
										if($t7_text45=='S'){
											$t7_text45='X';
										}
									}
									if($id_input=="t7_text46"){
										$t7_text46=$oConA->f('respuesta');
										if($t7_text46=='S'){
											$t7_text46='X';
										}
									}
									if($id_input=="t7_text47"){
										$t7_text47=$oConA->f('respuesta');
										if($t7_text47=='S'){
											$t7_text47='X';
										}
									}
									if($id_input=="t7_text48"){
										$t7_text48=$oConA->f('respuesta');
										if($t7_text48=='S'){
											$t7_text48='X';
										}
									}
									if($id_input=="t7_text49"){
										$t7_text49=$oConA->f('respuesta');
										if($t7_text49=='S'){
											$t7_text49='X';
										}
									}
									if($id_input=="t7_text50"){
										$t7_text50=$oConA->f('respuesta');
										if($t7_text50=='S'){
											$t7_text50='X';
										}
									}
									if($id_input=="t7_text51"){
										$t7_text51=$oConA->f('respuesta');
										if($t7_text51=='S'){
											$t7_text51='X';
										}
									}
									if($id_input=="t7_text52"){
										$t7_text52=$oConA->f('respuesta');
										if($t7_text52=='S'){
											$t7_text52='X';
										}
									}
									if($id_input=="t7_text53"){
										$t7_text53=$oConA->f('respuesta');
										if($t7_text53=='S'){
											$t7_text53='X';
										}
									}
									if($id_input=="t7_text54"){
										$t7_text54=$oConA->f('respuesta');
										if($t7_text54=='S'){
											$t7_text54='X';
										}
									}
									if($id_input=="t7_text55"){
										$t7_text55=$oConA->f('respuesta');
										if($t7_text55=='S'){
											$t7_text55='X';
										}
									}
									if($id_input=="t7_text56"){
										$t7_text56=$oConA->f('respuesta');
										if($t7_text56=='S'){
											$t7_text56='X';
										}
									}
								}while($oConA->SiguienteRegistro());
							}
						}
						if($sexo==1){
							$sexo='M';
						}
						if($sexo==2){
							$sexo='F';
						}
						$html_l='';
						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f'";
						if($oConA->Query($sql)){
							if($oConA->NumFilas()>0){
								do{

									$pregunta=$oConA->f('pregunta');
									$respuesta=$oConA->f('respuesta');
									$tipof=$oConA->f('tipo');
									$id_input=$oConA->f('id_input');
									$codform=$oConA->f('id_tipo_formulario_datos');

									
									if($id_input=="medicotrad"){
										$codmedt=$oConA->f('respuesta');

										
										if(!empty($codmedt)){
											$sqlc="select clpv_nom_clpv, clpv_ruc_clpv from saeclpv where clpv_cod_clpv=$codmedt";
											$med_tratante=consulta_string($sqlc, 'clpv_nom_clpv',$oIfxA,'');
											$ruc_tratante=substr(consulta_string($sqlc, 'clpv_ruc_clpv',$oIfxA,''),0,10);
										  }
								
			
								    }
									if($id_input=="136_fecha"){
										$fechaf=$oConA->f('respuesta');
										$fecha13=date('d-m-Y',strtotime($fechaf));
										$hora13=date('H:i',strtotime($fechaf));
									}
									
									
								}while($oConA->SiguienteRegistro());
							}
						}

						$oConA->Free();
						
						///CABECERA 
						$html.='<div>
						<br><br><table border="1" cellpadding="1" cellspacing="0">
							<thead>
							<tr style="background-color:'.$empr_web_color.'">
									<th style="font-size:90%;"width="100%"><b>A. DATOS DEL ESTABLECIMIENTO Y USUARIO / PACIENTE</b></th>
							</tr>
							<tr>
									<th style="font-size:80%;"align="center" width="22%"><b>INSTITUCIÓN DEL SISTEMA</b></th>
									<th style="font-size:80%;"align="center" width="9%"><b>UNICÓDIGO</b></th>
									<th style="font-size:80%;"align="center" width="23%"><b>ESTABLECIMIENTO DE SALUD</b></th>
									<th style="font-size:80%;"align="center" width="29%"><b>NÚMERO DE HISTORIA CLÍNICA ÚNICA</b></th>
									<th style="font-size:80%;"align="center" width="17%"><b>NÚMERO DE ARCHIVO</b></th>
							</tr>
							<tr>
									<th style="font-size:80%;"align="center" width="22%"></th>
									<th style="font-size:80%;"align="center" width="9%"></th>                    
									<th style="font-size:80%;"align="center" width="23%">'.$razonSocial.'</th>
									<th style="font-size:80%;"align="center" width="29%">'.$secu_hist.'</th>
									<th style="font-size:80%;"align="center" width="17%"></th>
							</tr>
							<tr>
									<th style="font-size:80%;"align="center" width="64%" rowspan="2"><b>APELLIDOS y NOMBRES</b></th>
									<th style="font-size:80%;"align="center" width="7%" rowspan="2"><b>SEXO</b></th>
									<th style="font-size:80%;"align="center" width="10%" rowspan="2"><b>FECHA NACIMIENTO</b></th>
									<th style="font-size:80%;"align="center" width="7%" rowspan="2"><b>EDAD</b></th>
									<th style="font-size:60%;"align="center" width="12%" colspan="4"><b>CONDICIÓN EDAD</b><br>(MARCAR)</th>
							</tr>
							<tr>
									<th style="font-size:60%;"align="center" width="3%"><b>H</b></th>
									<th style="font-size:60%;"align="center" width="3%"><b>D</b></th>
									<th style="font-size:60%;"align="center" width="3%"><b>M</b></th>
									<th style="font-size:60%;"align="center" width="3%"><b>A</b></th>                                                            
							</tr>
							<tr>
							<th style="font-size:80%;"align="center" width="64%" >'.$nom_clpv.'</th>
									<th style="font-size:80%;"align="center" width="7%" >'.$sexo.'</th>
									<th style="font-size:80%;"align="center" width="10%" >'.$fecha_naci.'</th>
									<th style="font-size:80%;"align="center" width="7%" >'.$edad.'</th>
									<th style="font-size:60%;"align="center" width="3%"></th>
									<th style="font-size:60%;"align="center" width="3%"></th>
									<th style="font-size:60%;"align="center" width="3%"></th>
									<th style="font-size:60%;"align="center" width="3%"></th>  

							</tr>
							</table>';

							
							$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' order by id_tipo_formulario_datos";
							$ban_t6=false;
							$ban_t7=false;
							$ban_t3=false;
							if($oConA->Query($sql)){
								if($oConA->NumFilas()>0){
									do{
										$pregunta=$oConA->f('pregunta');
										$respuesta=$oConA->f('respuesta');
										$tipo=$oConA->f('tipo');
										
										if($tipo=='TABLE6' && $ban_t6==false){
											/*$n2=espacios(150);
											$n3=espacios(30);
											$n4=espacios(7);
											$n5=espacios(35);
											$n6=espacios(52);
											
											$html.='<br><br><table border="1" cellpadding="2" cellspacing="0">
											<tr style="background-color:'.$empr_web_color.'">
											<td style="font-size:50%;"align="left" width="659.4">'.$n2.' SERVICIO'.$n3.'SALA '.$n4.' CAMA 
											'.$n5.' PRIORIDAD '.$n6.' FECHA DE TOMA</td>
											</tr>

											<tr>
											<td style="font-size:50%;"align="center" width="14%"></td>
											<td style="font-size:50%;"align="center" width="17%"></td>
											<td style="font-size:50%;"align="center" width="20%">'.$t6_text1.'</td>
											<td style="font-size:50%;"align="center" width="5%">'.$t6_text2.'</td>
											<td style="font-size:50%;"align="center" width="5%">'.$t6_text3.'</td>
											<td style="background-color:'.$empr_web_color.'; font-size:50%;"align="center" width="6%">URGENTE</td>
											<td style="font-size:50%;"align="center" width="3%">'.$ur.'</td>
											<td style="background-color:'.$empr_web_color.'; font-size:50%;"align="center" width="6%">RUTINA</td>
											<td style="font-size:50%;"align="center" width="3%">'.$ru.'</td>
											<td style="background-color:'.$empr_web_color.'; font-size:50%;"align="center" width="6%">CONTROL</td>
											<td style="font-size:50%;"align="center" width="3%">'.$co.'</td>
											<td style="font-size:50%;"align="center" width="11%">'.$t6_text5.'</td>
											

											</tr>
											
											</table><br><br>';*/

											$html.='<br><br><table border="1" cellpadding="2" cellspacing="0">
											<tr style="background-color:'.$empr_web_color.'">
												<td style="font-size:90%;"align="left" width="100%"><b>B. SERVICIO Y PRIORIDAD DE ATENCIÓN</b></td>
											</tr>

											<tr>
												<td style="font-size:50%;"align="center" width="31%" colspan="2"><b>DIAGNÓSTICO</b></td>	
												<td style="font-size:50%;"align="center" width="7%"><b>CIE</b></td>
												<td style="font-size:50%;"align="center" width="8%" rowspan="3"><b>SERVICIO</b></td>
												<td style="font-size:50%;"align="center" width="11%">EMERGENCIA</td>
												<td style="font-size:50%;"align="center" width="3%">'.$adm_emer.'</td>
												<td style="font-size:50%;"align="center" width="8%"><b>ESPECIALIDAD</b></td>
												<td style="font-size:50%;"align="center" width="20%">'.$t6_text1.'</td>
												<td style="font-size:50%;"align="center" width="12%"><b>PRIORIDAD</b></td>
											</tr>
											
											<tr>
												<td style="font-size:50%;" align="center" width="2%" ><b>1.</b></td>
												<td style="font-size:50%;" width="29%"></td>
												<td style="font-size:50%;"align="center" width="7%"></td>
												<td style="font-size:50%;"align="center" width="11%">CONSULTA EXTERNA</td>
												<td style="font-size:50%;"align="center" width="3%" >'.$adm_cext.'</td>
												<td style="font-size:50%;"align="center" width="8%"><b>SALA</b></td>
												<td style="font-size:50%;"align="center" width="20%">'.$t6_text2.'</td>
												<td style="font-size:50%;"align="center" width="6%">URGENTE</td>
												<td style="font-size:50%;"align="center" width="6%">'.$ur.'</td>
											</tr>

											<tr>
												<td style="font-size:50%;" align="center" width="2%" ><b>2.</b></td>
												<td style="font-size:50%;" width="29%"></td>
												<td style="font-size:50%;"align="center" width="7%"></td>
												<td style="font-size:50%;"align="center" width="11%">HOSPITALIZACION</td>
												<td style="font-size:50%;"align="center" width="3%" >'.$adm_hosp.'</td>
												<td style="font-size:50%;"align="center" width="8%"><b>CAMA</b></td>
												<td style="font-size:50%;"align="center" width="20%">'.$t6_text3.'</td>
												<td style="font-size:50%;"align="center" width="6%">RUTINA</td>
												<td style="font-size:50%;"align="center" width="6%">'.$ru.'</td>
											</tr>

											<tr>
												<td style="font-size:50%;" width="100%" ><b>TRATAMIENTO TERAPEUTICO (ESPECIFIQUE NOMBRE Y TIEMPO DE ADMINISTRACIÓN):</b></td>
											</tr>
											<tr>
												<td style="font-size:50%;" width="100%" ></td>
											</tr>
											</table><br><br>';
											

										
												
											$ban_t6=true;
										}
									if($tipo=='TABLE7'&& $ban_t7==false){

										$html.='<table border="1" cellpadding="1" cellspacing="0">

													<tr style="background-color:'.$empr_web_color.'">
														<td style="font-size:90%;"align="left" width="100%" ><b>C. LISTADO DE EXÁMENES</b></td>
													</tr>
													<tr>
														<td style="font-size:90%;"align="left" width="34%">
															<table border="1" cellpadding="1" cellspacing="0">
															<tr>
															<td style="font-size:90%;"align="left" width="100%">PRUEBA COLUMNA</td>
															</tr>
														</td>
														<td style="font-size:90%;"align="left" width="66%"></td>
														</table>
													</tr>


													
												</table>';

												
									
											/*$html.='<table border="1" cellpadding="1" cellspacing="0">

																		<tr style="background-color:'.$empr_web_color.'">
																			<td colspan="4" style="font-size:85%;"align="center" width="219.8"><b>1 HEMATOLOGIA</b></td>
																			<td colspan="2" style="font-size:85%;"align="center" width="219.8"><b>2 UROANALISIS</b></td>
																			<td colspan="2" style="font-size:85%;"align="center" width="219.8"><b>3 COPROLOGICO</b></td>
																		</tr>
																		<tr>
																		<td style="font-size:60%;"align="center" width="84.9">BIOMETRIA HEMATICA</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text1.'</td>
																			<td style="font-size:60%;"align="center" width="84.9">INDICES HEMATICOS</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text2.'</td>

																			<td style="font-size:60%;"align="center" width="194.8">ELEMENTAL Y MICROSCOPICO</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text14.'</td>

																			<td style="font-size:60%;"align="center" width="194.8">COPROPARASITARIO</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text17.'</td>


																		</tr>
																		<tr>
																			<td style="font-size:60%;"align="center">PLAQUETAS</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text27.'</td>
																			<td style="font-size:60%;"align="center" >TIEMPO DE PROTROMBINA(TP)</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text3.'</td>

																			<td style="font-size:60%;"align="center">GOTA FRESCA</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text15.'</td>

																			<td style="font-size:60%;"align="center">COPRO SERIADO</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text18.'</td>

																		</tr>
																		<tr>
																			<td style="font-size:60%;"align="center">GRUPO SANGUINEO</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text4.'</td>
																			<td style="font-size:60%;"align="center" >T.PROTROMBINA PARCIAL(TTP)</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text5.'</td>

																			<td style="font-size:60%;"align="center">PRUEBA DE EMBARAZO</td>
																			<td style="font-size:60%;"align="center"width="25">'.$t7_text16.'</td>

																			<td style="font-size:60%;"align="center">SANGRE OCULTA</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text19.'</td>

																		</tr>
																		<tr>
																			<td style="font-size:60%;"align="center">RETICULOCITOS</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text6.'</td>
																			<td style="font-size:60%;"align="center">DREPANOCITOS</td>
																			<td style="font-size:60%;"align="center" width="25"> '.$t7_text7.'</td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>

																			<td style="font-size:60%;"align="center">INVESTIGACION DE POLIMORFOS</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text20.'</td>
																		</tr>
																		<tr>
																			<td style="font-size:60%;"align="center">HEMATOZOARIO</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text8.'</td>
																			<td style="font-size:60%;"align="center">COOMBS DIRECTO</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text9.'</td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>

																			<td style="font-size:60%;"align="center">INVESTIGACION DE ROTAVIRUS </td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text21.'</td>
																		</tr>
																		<tr>
																			<td style="font-size:60%;"align="center">CELULA L.E</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text10.'</td>
																			<td style="font-size:60%;"align="center">COOMBS INDIRECTO</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text11.'</td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																		</tr>
																		<tr>
																			<td style="font-size:60%;"align="center">TIEMPO DE COAGULACION</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text12.'</td>
																			<td style="font-size:60%;"align="center">TIEMPO DE SANGRIA</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text13.'</td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																		</tr>
																	</table>';
																	$html.='<br><br><table border="1" cellpadding="1" cellspacing="0">
																	<tr style="background-color:'.$empr_web_color.'">
																			<td colspan="4" style="font-size:85%;"align="center" width="219.8"><b>4 QUIMICA SANGUINEA</b></td>
																			<td colspan="2" style="font-size:85%;"align="center" width="219.8"><b>5 SEROLOGIA</b></td>
																			<td  colspan="4" style="font-size:85%;"align="center" width="219.8"><b>6 BACTERIOLOGIA</b></td>
																		</tr>
																		<tr>
																			<td style="font-size:60%;"align="center" width="84.9">GLUCOSA EN AYUNA</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text22.'</td>
																			<td style="font-size:60%;"align="center" width="84.9">TRANSAMINASA PIRUVICA(ALT)</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text23.'</td>

																			<td style="font-size:60%;"align="center" width="194.8">VDRL</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text43.'</td>

																			<td style="font-size:60%;"align="center" width="84.9">GRAM</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text47.'</td>

																			<td style="font-size:60%;"align="center" width="84.9">FRESCO</td>
																			<td style="font-size:60%;"align="center" width="25">'.$t7_text48.'</td>
																		</tr>
																		<tr>
																			<td style="font-size:60%;"align="center">GLUCOSA POST PRANDIAL 2 HORAS</td>
																			<td style="font-size:60%;"align="center">'.$t7_text24.'</td>
																			<td style="font-size:60%;"align="center">TRANSAMINASA OXALACETICA(AST)</td>
																			<td style="font-size:60%;"align="center">'.$t7_text25.'</td>

																			<td style="font-size:60%;"align="center">LATEX</td>
																			<td style="font-size:60%;"align="center">'.$t7_text44.'</td>

																			<td style="font-size:60%;"align="center">ZIEHL</td>
																			<td style="font-size:60%;"align="center">'.$t7_text49.'</td>
																		
																			<td style="font-size:60%;"align="center">CULTIVO-ANTIBIOGRAMA</td>
																			<td style="font-size:60%;"align="center">'.$t7_text50.'</td>

																		</tr>
																		<tr>
																			<td style="font-size:60%;"align="center">UREA</td>
																			<td style="font-size:60%;"align="center">'.$t7_text26.'</td>
																			<td style="font-size:60%;"align="center">FOSFATASA ALCALINA</td>
																			<td style="font-size:60%;"align="center">'.$t7_text28.'</td>

																			<td style="font-size:60%;"align="center">AGRUTINACIONES FEBRILES</td>
																			<td style="font-size:60%;"align="center">'.$t7_text45.'</td>

																			<td style="font-size:60%;"align="center">HONGOS</td>
																			<td style="font-size:60%;"align="center">'.$t7_text51.'</td>
																		
																			<td colspan="2" style="font-size:60%;"align="left">&nbsp;&nbsp;&nbsp;MUESTRA DE:</td>
																																					</tr>
																		<tr>
																			<td style="font-size:60%;"align="center">CREATININA</td>
																			<td style="font-size:60%;"align="center">'.$t7_text29.'</td>
																			<td style="font-size:60%;"align="center">FOSFATASA ACIDA</td>
																			<td style="font-size:60%;"align="center">'.$t7_text30.'</td>

																			<td style="font-size:60%;"align="center">ASTO</td>
																			<td style="font-size:60%;"align="center">'.$t7_text46.'</td>

																			<td colspan="2" style="font-size:60%;"align="center"></td>
																			<td colspan="2" style="font-size:60%;"align="center">'.$t7_text52.'</td>
																		</tr>
																		<tr>

																			<td style="font-size:60%;"align="center">BILIRUBINA TOTAL</td>
																			<td style="font-size:60%;"align="center">'.$t7_text31.'</td>
																			<td style="font-size:60%;"align="center">COLESTEROL TOTAL</td>
																			<td style="font-size:60%;"align="center">'.$t7_text32.'</td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																			
																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																		</tr>
																		<tr>
																			<td style="font-size:60%;"align="center">BILIRUBINA DIRECTA</td>
																			<td style="font-size:60%;"align="center">'.$t7_text33.'</td>
																			<td style="font-size:60%;"align="center">COLESTEROL HDL</td>
																			<td style="font-size:60%;"align="center">'.$t7_text34.'</td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																			
																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																		</tr>
																		<tr>
																			<td style="font-size:60%;"align="center">ACIDO URICO</td>
																			<td style="font-size:60%;"align="center">'.$t7_text35.'</td>
																			<td style="font-size:60%;"align="center">COLESTEROL LDL</td>
																			<td style="font-size:60%;"align="center">'.$t7_text36.'</td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																			
																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																		</tr>

																		<tr>
																			<td style="font-size:60%;"align="center">PROTEINA TOTAL </td>
																			<td style="font-size:60%;"align="center">'.$t7_text37.'</td>
																			<td style="font-size:60%;"align="center">TRIGLICERIDOS</td>
																			<td style="font-size:60%;"align="center">'.$t7_text38.'</td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																			
																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																		</tr>
																		<tr>
																			<td style="font-size:60%;"align="center">ALBUMINA</td>
																			<td style="font-size:60%;"align="center">'.$t7_text39.'</td>
																			<td style="font-size:60%;"align="center">HIERRO SERICO </td>
																			<td style="font-size:60%;"align="center">'.$t7_text40.'</td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																			
																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																		</tr>
																		<tr>
																			<td style="font-size:60%;"align="center">GLOBULINA</td>
																			<td style="font-size:60%;"align="center">'.$t7_text41.'</td>
																			<td style="font-size:60%;"align="center">AMILASA </td>
																			<td style="font-size:60%;"align="center">'.$t7_text42.'</td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																			
																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>

																			<td style="font-size:60%;"align="center"></td>
																			<td style="font-size:60%;"align="center"></td>
																		</tr>
																	</table>
																	<br><br>
																	<table border="1" cellpadding="1" cellspacing="0" >
																		<tr style="background-color:'.$empr_web_color.'">
																			<td  style="font-size:85%;"align="left" width="659.4"><b>7 OTROS</b></td>
																		
																		</tr>
																		<tr>
																			<td  style="font-size:60%;"align="left">&nbsp;&nbsp;&nbsp;'.$t7_text53.'</td>
																		</tr>
																		<tr>
																			<td  style="font-size:60%;"align="left">&nbsp;&nbsp;&nbsp;'.$t7_text54.'</td>
																		</tr>
																		<tr>
																			<td  style="font-size:60%;"align="left">&nbsp;&nbsp;&nbsp;'.$t7_text55.'</td>
																		</tr>
																		<tr>
																			<td  style="font-size:60%;"align="left">&nbsp;&nbsp;&nbsp;'.$t7_text56.'</td>
																		</tr>
																	</table>
																	</div>';*/
										$ban_t7=true;
									}
									if($tipo=='MEDICO'&& $ban_t3==false){
										$html.='<br><br><table   border="1" cellpadding="2" cellspacing="0">
													<tr>
														<td  colspan="5" bgcolor="'.$empr_web_color.'" style="font-size:90%;" width="100%"; align="left" ><b>'.$pregunta.'</b></td>
													</tr>
													<tr>
														<td style="font-size:60%;"align="center" width="15%"><b>FECHA</b><br>(aaaa-mm-dd)</td>
														<td style="font-size:60%;"align="center" width="15%"><b>HORA</b></td>
														<td style="font-size:60%;"align="center" width="70%" colspan="2"><b>APELLIDOS Y NOMBRES</b></td>
													</tr>
													<tr>
														<td style="font-size:60%;"align="center" width="15%">'.$fecha13.'</td>
														<td style="font-size:60%;"align="center" width="15%">'.$hora13.'</td>
														<td style="font-size:60%;"align="center" width="70%" colspan="2">'.$med_tratante.'</td>
													</tr>
													<tr>
														<td style="font-size:60%;"align="center" width="30%" colspan="2"><b>NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</b></td>
														<td style="font-size:60%;"align="center" width="35%"><b>FIRMA</b></td>
														<td style="font-size:60%;"align="center" width="35%"><b>SELLO</b></td>
													</tr>
													<tr>
														<td style="font-size:60%;"align="center" width="30%" colspan="2">'.$ruc_tratante.'</td>
														<td style="font-size:60%;"align="center" width="35%"></td>
														<td style="font-size:60%;"align="center" width="35%"></td>
													</tr>

												</table>';


										$ban_t3=true;
									}
										
									}while($oConA->SiguienteRegistro());
									
								}
							}

							$html.='
						<table   align="center" style="width:99%;  margin-top:20px;" border=0 cellpadding="0" cellspacing="0" >
							<tr>
								<td align="left" style=" font-size:80%; width:50%;">
									<strong>SNS-MSP / HCU-form.010A / 2021</strong>
								</td>
								<td align="right" style=" font-size:80%; width:50%;">
									<strong>'.$arreglo_formularios_lados[$id_lado][0].'</strong>
								</td>
							</tr>
						</table>';

						$html.='||||||||||';
							
					break;
					case 5:
						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' ";
						//echo $sql;exit;
						$contacto="";
						$telefono="";
						$correo="";
						$servicio="";
						$idmedico="";
						$idrepre="";
						$nomrepre="";
						$diagcie='';
						$i=1;					
						if($oCon->Query($sql)){
							if($oCon->NumFilas()>0){
								do{
									
									$id_input=$oCon->f('id_input');
									$tipo=$oCon->f('tipo');
									if($tipo=='TABLE'){
										$codcie=$oCon->f('cod_cie');
										$rescie=$oCon->f('respuesta');
										$diagcie.='<b>'.$i.')</b> '.$rescie.'-'.$codcie.' ';
										$i++;
									}
									if($id_input=="texto1"){
										$contacto=$oCon->f('respuesta');
									}
								
									if($id_input=="texto2"){
										$telefono=$oCon->f('respuesta');
									}
									if($id_input=="texto3"){
										$correo=$oCon->f('respuesta');
									}
									if($id_input=="texto6"){
										$servicio=$oCon->f('respuesta');
									}
									if($id_input=="texto7"){
										$codval=$oCon->f('respuesta');
									}
									if($id_input=="texto8"){
										$observaciones=$oCon->f('respuesta');
									}
									if($id_input=="texto9"){
										$idmedico=$oCon->f('respuesta');
									}
									if($id_input=="texto11"){
										$idrepre=$oCon->f('respuesta');
									}
									if($id_input=="texto12"){
										$nomrepre=$oCon->f('respuesta');
									}
									if($id_input=="texto13"){
										$parentesco=$oCon->f('respuesta');
									}
									
									if($id_input=="148_fecha"){
										$fechpres=$oCon->f('respuesta');
										$mespres=date('m',strtotime($fechpres));
										$mespres=Mes_func($mespres);
										$aniopres=date('Y',strtotime($fechpres));
									}
									if($id_input=="151_fecha"){
										$fechrep=$oCon->f('respuesta');
										$diarep=date('d',strtotime($fechrep));
										$mesrep=date('m',strtotime($fechrep));
										$mesrep=Mes_func(intval($mesrep));
										$aniorep=date('Y',strtotime($fechrep));
										
									}

								}while($oCon->SiguienteRegistro());
							}
						}
						$oCon->Free();
                        //VALIDACION REPRESENTANTE 
						$rnom_clpv=$nom_clpv;
						if(empty($idrepre)){
							$idrepre='';
							$nomrepre='...........................................';
							$rnom_clpv='';
							$parentesco='.......................................';
						}


						$ban_t=false;
						$dia=substr($fecha_registro,8,10);
						$mes=substr($fecha_registro,5,2);
						 
						
						
						

						$sql="SELECT fecha_hora, clpv_cod_med from clinico.consulta_medica where 
						id_dato=$id_dato and empr_cod_empr = $idempresa";

						$fecha_consul=consulta_string($sql,'fecha_hora',$oConA,'');

						$clpv_cod_med=consulta_string($sql,'clpv_cod_med',$oConA,0);
						$mesp=substr($fecha_consul,5,2);
						$aniop=substr($fecha_consul,0,4);

						$especialidad = $arrayEspe[$clpv_cod_med];

						$sqlpac="select secu_hist from clinico.datos_clpv where id_dato=$id_dato";
 
		
						$hist_cli=consulta_string($sqlpac,'secu_hist',$oConA,'');
                        
					
						if(empty($hist_cli)){
							$hist_cli=$ruc_clpv;
						}

						$html.=' 
						<td style="font-size:90%;"  align="center" width="336.5"><br><br><br><br><br><br><b> ACTA DE ENTREGA - RECEPCIÓN DE SERVICIOS</b></td>  </tr>
            <tr>
            <td style="font-size:90%;"align="left" width="190"><b> Prestador:</b></td>
            <td style="font-size:95%;" align="center" width="476">'.$razonSocial.'</td>
            </tr>
    
     </table>
						<table    cellpadding="1" border="1">
 
						<tr>
						<td style="font-size:90%;"align="left" width="190"><b> Persona de Contacto:</b></td>
						<td colspan="3" style="font-size:95%;"align="center" width="476">'.$contacto.'</td>
						</tr>
						<tr>
						<td style="font-size:90%;"align="left"><b> Teléfono:</b></td>
						<td style="font-size:95%;"align="center">'.$telefono.'</td>
						<td style="font-size:90%;"align="center" width="50"><b> E-mail:</b></td>
						<td style="font-size:95%;"align="center" width="267">'.$correo.'</td>
						</tr>
				</table>
				
				<table    cellpadding="1" border="1">
				 
						<tr>
						   <td style="font-size:90%;"align="left" width="190"><b> Mes y año de Prestación:</b></td>
						   <td style="font-size:95%;"align="left" width="476">'. $mespres.' '.$aniopres.'</td>
						</tr>
						<tr>
						   <td style="font-size:90%;"align="left"><b> Diagnóstico+Código CIE 10:</b></td>
						   <td style="font-size:95%;"align="left">'.$diagcie.'</td>
						</tr>
						<tr>
						   <td style="font-size:90%;"align="left"><b> Servicio Entregado</b></td>
						   <td style="font-size:95%;"align="left">'.$servicio.'</td>
						</tr>
						<tr>
						   <td style="font-size:90%;"align="left" width="190"><b> Nro. de Código de Validación:</b></td>
						   <td style="font-size:95%;"align="left" width="476">'.$codval.'</td>
						</tr>
						<tr>
						<td style="font-size:90%;"align="left"><b> Nro. de Historia Clínica:</b></td>
						<td style="font-size:95%;"align="left">'.$hist_cli.'</td>
						</tr>
						<tr>
						   <td colspan="2" style="font-size:100%;"align="left"></td>
						</tr>
						<tr style="background-color:'.$empr_web_color.'">
						<td style="font-size:90%;"align="left"><b> N° Cédula de Identidad:</b></td>
						<td style="font-size:90%;"align="center"><b>NOMBRE DEL PCTE.       (Apellidos y Nombres)</b></td>
						</tr>
						<tr>
						<td style="font-size:95%;"align="left">'.$ruc_clpv.'</td>
						<td style="font-size:95%;"align="center">'.$nom_clpv.'</td>
						</tr>
				
						<tr>
						   <td colspan="2" style="font-size:75%;"align="left">
						   <br><br>
						   <b>OBSERVACIONES:</b>....'.$observaciones.'....................................................
						
						   <p align="center">
							<b><u>Acuse entrega del servicio</u></b>
							
							<ul>
							<li>Como prestador de la RPIS, conozco el cumplimiento obligatorio del TPSNS y sus procedimientos 
							que están regulados en la Normativa Legal vigente.
							</li>
							<li>Además tengo conocimiento el acápite que refiere a la Coordinación de pagos y tarifas que indica textualmente:</li>
							</ul></p>
						   </td>
						</tr>
				
						<tr>
						   <td colspan="2" style="font-size:75%;"align="left">
						   
						   <b><i>"En caso de procedimientos observados que no fueron justificados y produzcan débitos definitivos, la 
						   unidad de salud no podrá requerir por <br> ningún motivo el pago al paciente o familiares de los valores objetados".
						   </i></b>
						   Por lo que me comprometo a entregar la documentación según la norma. 
						   <br>
						   </td>
						</tr>
				
						<tr>
						<td colspan="2" style="font-size:75%;"align="left" height="100">
						<br><br>
						<br> <b> ______________________________
						<br>  Firma y sello del Médico
						<br>  N° de Documento de indetidad:</b>'.$idmedico.'
						<p align="center">
						<b><u>Acuse recepción del servicio</u></b>
						</p>
						</td>
						</tr>
						
						<tr>
						<td colspan="2" style="font-size:75%;"align="left" height="20">
						<br> Con la firma de éste documento el paciente y/o representante ratifican que el servicio brindado fue recibido a entera satisfacción, sin efectuar pago alguno.
						</td>
						</tr>
						<tr>
						<td colspan="2" style="font-size:75%;"align="left" height="20">
						<br><br> Quito, a los '.$diarep.' días del mes de '.$mesrep.' del año '.$aniorep.'
						<br><br>
						<br> <b> ______________________________
						<br>  Firma o huella del paciente
						<br>  No. CI. Paciente:</b> '.$ruc_clpv.'
						<br>  Observaciones: Yo, '.$nomrepre.', en mi calidad de '.$parentesco.' y representante del paciente '.$rnom_clpv.' , certifico que recibió el servicio de........'.$servicio.'........
						<br><br>
						<br> <b>_______________________________
						<br>  (Firma del representante o acompañante)
						<br>  (No. CI.)</b> '.$idrepre.'
						<br>
						<p style="text-align: justify;"><b><i>EN MI CALIDAD DE PRESTADOR DE SERVICIOS CERTIFICO QUE LAS FIRMAS CONSTANTES EN EL PRESENTE DOCUMENTO, CORRESPONDEN A LA FIRMA DEL PACIENTE O SU REPRESENTANTE DE SER EL CASO, MISMA QUE FUE RECEPTADA EN ESTE CENTRO DE ATENCIÓN, POR LO TANTO ME RESPONZABILIZO POR EL CONTENIDO DE DICHO CERTIFICADO, ASUMIENDO TODA LA RESPONSABILIDAD TANTO ADMINISTRATIVA, CIVIL O PENAL POR VERACIDAD DE LA INFORMACIÓN ENTREGADA.</i></b></p>
						<br><br><br>
						 <p align="center"> 
						 <b>  _______________________________________
						 <br>       Firma y sello del prestador</b>
						 </p>
						</td>
						</tr>     
				 </table>       
				
						</div>
				
						';
													
					break;
					case 6:
						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and 	tipo='TABLE8'";
						$t8_text11="";
						$t8_text10="";
						$t8_text9="";
						$t8_text8="";
						$t8_text7="";
						$t8_text6="";
						$t8_text5="";
						$t8_text4="";
						$t8_text3="";
						$t8_text2="";
						$t8_text1="";
						if($oConA->Query($sql)){
							if($oConA->NumFilas()>0){
								
								do{
									$id_input=$oConA->f('id_input');
									if($id_input=="t8_text1"){
										$t8_text1=$oConA->f('respuesta');
										if($t8_text1=='S'){
											$t8_text1='X';
										}
									}
									
									if($id_input=="t8_text2"){
										$t8_text2=$oConA->f('respuesta');
										if($t8_text2=='S'){
											$t8_text2='X';
										}
									}
									if($id_input=="t8_text3"){
										$t8_text3=$oConA->f('respuesta');
										if($t8_text3=='S'){
											$t8_text3='X';
										}
									}
									if($id_input=="t8_text4"){
										$t8_text4=$oConA->f('respuesta');
										if($t8_text4=='S'){
											$t8_text4='X';
										}
									}
									if($id_input=="t8_text5"){
										$t8_text5=$oConA->f('respuesta');
										if($t8_text5=='S'){
											$t8_text5='X';
										}
									}
									if($id_input=="t8_text6"){
										$t8_text6=$oConA->f('respuesta');
										if($t8_text6=='S'){
											$t8_text6='X';
										}
									}
									
									if($id_input=="t8_text7"){
										$t8_text7=$oConA->f('respuesta');
										if($t8_text7=='S'){
											$t8_text7='X';
										}
									}
									if($id_input=="t8_text8"){
										$t8_text8=$oConA->f('respuesta');
										if($t8_text8=='S'){
											$t8_text8='X';
										}
									}
									if($id_input=="t8_text9"){
										$t8_text9=$oConA->f('respuesta');
										if($t8_text9=='S'){
											$t8_text9='X';
										}
									}
									if($id_input=="t8_text10"){
										$t8_text10=$oConA->f('respuesta');
										if($t8_text10=='S'){
											$t8_text10='X';
										}
									}
									if($id_input=="t8_text11"){
										$t8_text11=$oConA->f('respuesta');
										
									}
								}while($oConA->SiguienteRegistro());
							}
						}
						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and tipo='TABLE9'";
						//echo $sql;exit;
						
						
						$t9_text11="";
						$t9_text10="";
						$t9_text9="";
						$t9_text8="";
						$t9_text7="";
						$t9_text6="";
						$t9_text5="";
						$t9_text4="";
						$t9_text5="";
						$t9_text3="";
						$t9_text2="";
						$t9_text1="";
						if($oConA->Query($sql)){
							if($oConA->NumFilas()>0){
								
								do{
									$id_input=$oConA->f('id_input');
									if($id_input=="t9_text1"){
										$t9_text1=$oConA->f('respuesta');
										if($t9_text1=='S'){
											$t9_text1='X';
										}
									}
									
									if($id_input=="t9_text2"){
										$t9_text2=$oConA->f('respuesta');
										if($t9_text2=='S'){
											$t9_text2='X';
										}
									}
									if($id_input=="t9_text3"){
										$t9_text3=$oConA->f('respuesta');
										if($t9_text3=='S'){
											$t9_text3='X';
										}
									}
									if($id_input=="t9_text4"){
										$t9_text4=$oConA->f('respuesta');
										if($t9_text4=='S'){
											$t9_text4='X';
										}
									}
									if($id_input=="t9_text5"){
										$t9_text5=$oConA->f('respuesta');
										if($t9_text5=='S'){
											$t9_text5='X';
										}
									}
									if($id_input=="t9_text6"){
										$t9_text6=$oConA->f('respuesta');
										if($t9_text6=='S'){
											$t9_text6='X';
										}
									}
									
									if($id_input=="t9_text7"){
										$t9_text7=$oConA->f('respuesta');
										if($t9_text7=='S'){
											$t9_text7='X';
										}
									}
									if($id_input=="t9_text8"){
										$t9_text8=$oConA->f('respuesta');
										if($t9_text8=='S'){
											$t9_text8='X';
										}
									}
									if($id_input=="t9_text9"){
										$t9_text9=$oConA->f('respuesta');
										if($t9_text9=='S'){
											$t9_text9='X';
										}
									}
									if($id_input=="t9_text10"){
										$t9_text10=$oConA->f('respuesta');
										if($t9_text10=='S'){
											$t9_text10='X';
										}
									}
									if($id_input=="t9_text11"){
										$t8_text11=$oConA->f('respuesta');
										
									}
								}while($oConA->SiguienteRegistro());
							}
						}
						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and tipo='TABLE10'";
						$t10_text1="";     
						$t10_text2="";
						$t10_text3="";
						$t10_text4="";
						$t10_text5="";
						$t10_text6="";
						$t10_text7="";
						$t10_text8="";
						$t10_text9="";
						$t10_text10="";
						$t10_text11="";     
						$t10_text12="";
						$t10_text13="";
						$t10_text14="";
						$t10_text15="";
						$t10_text16="";
						$t10_text17="";
						$t10_text18="";
						$t10_text19="";
						$t10_text20="";
						$t10_text21="";     
						$t10_text22="";
						$t10_text23="";
						$t10_text24="";
						$t10_text25="";
						$t10_text26="";
						$t10_text27="";
						$t10_text28="";
						$t10_text29="";
						$t10_text30="";
						$t10_text31="";
						$t10_text32="";
					
						if($oConA->Query($sql)){
							if($oConA->NumFilas()>0){
								
								do{
									$id_input=$oConA->f('id_input');
									if($id_input=="t10_text1"){
										$t10_text1=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text2"){
										$t10_text2=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text3"){
										$t10_text3=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text4"){
										$t10_text4=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text5"){
										$t10_text5=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text6"){
										$t10_text6=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text7"){
										$t10_text7=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text8"){
										$t10_text8=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text9"){
										$t10_text9=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text10"){
										$t10_text10=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text11"){
										$t10_text11=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text12"){
										$t10_text12=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text13"){
										$t10_text13=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text14"){
										$t10_text14=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text15"){
										$t10_text15=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text16"){
										$t10_text16=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text17"){
										$t10_text17=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text18"){
										$t10_text18=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text19"){
										$t10_text19=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text20"){
										$t10_text20=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text21"){
										$t10_text21=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text22"){
										$t10_text22=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text23"){
										$t10_text23=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text24"){
										$t10_text24=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text25"){
										$t10_text25=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text26"){
										$t10_text26=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text27"){
										$t10_text27=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text28"){
										$t10_text28=$oConA->f('respuesta');
										
									}
									if($id_input=="t10_text29"){
										$t10_text29=$oConA->f('respuesta');
										
									}if($id_input=="t10_text30"){
										$t10_text30=$oConA->f('respuesta');
										
									}if($id_input=="t10_text31"){
										$t10_text31=$oConA->f('respuesta');
										
									}if($id_input=="t10_text32"){
										$t10_text32=$oConA->f('respuesta');
										
									}
								}while($oConA->SiguienteRegistro());
							}
						}
						
						///
						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and tipo='TABLE11'";
						//echo $sql;exit;
						
						
					
						$t11_text7="";
					
						$t11_text6="";
						$t11_text6_p="";
						$t11_text6_d="";
						$t11_text4="";
						$t11_text4_p="";
						$t11_text4_d="";
						$t11_text5="";
						$t11_text5_d="";
						$t11_text5_p="";
						$t11_text3="";
						$t11_text3_p="";
						$t11_text3_d="";
						$t11_text2="";
						$t11_text2_p="";
						$t11_text2_d="";
						$t11_text1="";
						$t11_text1_p="";
						$t11_text1_d="";
						if($oConA->Query($sql)){
							if($oConA->NumFilas()>0){
								
								do{
									$id_input=$oConA->f('id_input');
									if($id_input=="radio11_1"){
										$t11_text1=$oConA->f('respuesta');
										if($t11_text1=='D'){
											$t11_text1_d='X';
										}else{
											$t11_text1_p='X';
										}
									}
									
									if($id_input=="radio11_2"){
										$t11_text2=$oConA->f('respuesta');
										if($t11_text2=='D'){
											$t11_text2_d='X';
										}else{
											$t11_text2_p='X';
										}
									}
									if($id_input=="radio11_3"){
										$t11_text3=$oConA->f('respuesta');
										if($t11_text3=='D'){
											$t11_text3_d='X';
										}else{
											$t11_text3_p='X';
										}
									}
									if($id_input=="radio11_4"){
										$t11_text4=$oConA->f('respuesta');
										if($t11_text4=='D'){
											$t11_text4_d='X';
										}else{
											$t11_text4_p='X';
										}
									}
									if($id_input=="radio11_5"){
										$t11_text5=$oConA->f('respuesta');
										if($t11_text5=='D'){
											$t11_text5_d='X';
										}else{
											$t11_text5_p='X';
										}
									}
									if($id_input=="radio11_6"){
										$t11_text6=$oConA->f('respuesta');
										if($t11_text6=='D'){
											$t11_text6_d='X';
										}else{
											$t11_text6_p='X';
										}
									}
									
									if($id_input=="radio11_7"){
										$t11_text7=$oConA->f('respuesta');
										
									}
									
								}while($oConA->SiguienteRegistro());
							}
						}
						if($sexo==1){
							$sexo='M';
						}
						if($sexo==2){
							$sexo='F';
						}
						
						$html_l='';
						$html.='
								<table align="center" style="width:100%; " border="1" cellpadding="1" cellspacing="0">
									<tr>
									<th style="font-weight: bold; text-align: left;" >A. DATOS DEL ESTABLECIMIENTO Y USARIO / PACIENTE</th>
									</tr>
									<tr>
									<th style="font-size: 80%; padding: 10px; text-align: center; width: 22%;"><b>INSTITUCIÓN DEL SISTEMA</b></th>
									<th style="font-size: 80%; padding: 10px; text-align: center; width: 9%;"><b>UNICÓDIGO</b></th>
									<th style="font-size: 80%; padding: 10px; text-align: center; width: 23%;"><b>ESTABLECIMIENTO DE SALUD</b></th>
									<th style="font-size: 80%; padding: 10px; text-align: center; width: 29%;"><b>NÚMERO DE HISTORIA CLÍNICA ÚNICA</b></th>
									<th style="font-size: 80%; padding: 10px; text-align: center; width: 10%;"><b>NÚMERO DE ARCHIVO</b></th>
									<th style="font-size: 80%; padding: 10px; text-align: center; width: 7%;"><b>No. HOJA</b></th>
									</tr>
	
									<tr>
									<th style="font-size: 80%; height: 30px;" align="center" width="22%;"></th>
									<th style="font-size: 80%; height: 30px;" width="9%;"></th>
									<th style="font-size: 80%; height: 30px;" width="23%;">'.$razonSocial .'</th>
									<th style="font-size: 80%; height: 30px;" width="29%;">'.$secu_hist.'</th>
									<th style="font-size: 80%; height: 30px;" width="10%;"></th>
									<th style="font-size: 80%; height: 30px;" width="7%;"></th>
									</tr>

									<tr>
									<th style="font-weight: bold; font-size: 80%; padding: 10px; text-align: center; width: 86%;">NOMBRES Y APELLIDOS COMPLETOS</th>
									<th style="font-weight: bold; font-size: 80%; padding: 10px; text-align: center; width: 7%;">SEXO</th>
									<th style="font-size: 80%; padding: 10px; text-align: center; width: 7%;"><b>EDAD</b><br>(Años)</th>
									</tr>

									<tr>
									<th style="font-size: 80%; height: 30px; text-align: center; width: 86%;">'.$nom_clpv.'</th>
									<th style="font-size: 80%; height: 30px; text-align: center; width: 7%;">'.$sexo.' </th>
									<th style="font-size: 80%; height: 30px; text-align: center; width: 7%;">'.$edad.'</th>
									</tr>
									
								</table>';
							
							$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' order by id_tipo_formulario_datos";
							
							$ban_t=false;
							$ban_t3=false;
							$ban_t10=false;
							$ban_t11=false;
							$ban_t9=false;
							$ban_t8=false;
							$ban_t12=false;
							if($oConA->Query($sql)){
								if($oConA->NumFilas()>0){
									do{
										$pregunta=$oConA->f('pregunta');
										$respuesta=$oConA->f('respuesta');
										$tipo=$oConA->f('tipo');
										if($tipo=='TEXT'||$tipo=='TEXTAREA'||$tipo=='COD'){
											$html.='<table   align="center" style="width:100%;" border="1" cellpadding="0" cellspacing="0" >
														<tr>
															<td style=" background-color:'.$empr_web_color.' width:100%;" ><h5>'.$pregunta.'</h5></td>
														</tr>
														<tr>
															<td height="80"  style=" width:100%;" valign="top">'.$respuesta.'</td>
														</tr>
													</table>';
										}
										//bgcolor="#BDBDBD"
										if($tipo=='TABLE'&& $ban_t==false){
											$html.='<table   align="center" style="width:100%;" border="1" cellpadding="0" cellspacing="0" >
														<tr>
															<td  colspan="2"  style=" width:20%;" ><h5>'.$pregunta.'</h5></td>
															<td  style=" background-color:'.$empr_web_color.' width:10%;" >CIE</td>
															<td  style=" background-color:'.$empr_web_color.' width:5%;" >PRE</td>
															<td  style=" background-color:'.$empr_web_color.' width:5%;" >DEF</td>
															<td  style=" background-color:'.$empr_web_color.' width:5%;" ></td>
															<td  style=" background-color:'.$empr_web_color.' width:20%;" ></td>
															<td  style=" background-color:'.$empr_web_color.' width:10%;" >CIE</td>
															<td  style=" background-color:'.$empr_web_color.' width:5%;" >PRE</td>
															<td  style=" background-color:'.$empr_web_color.' width:5%;" >DEF</td>
														</tr>
														<tr>
															<td height="10"  style=" width:5%;" valign="top">1</td>
															<td height="10"  style=" width:25%;" valign="top">'.$cie_deta1.'</td>
															<td height="10"  style=" width:10%;" valign="top">'.$cie1.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie_p1.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie_d1.'</td>
															<td height="10"  style=" width:5%;" valign="top">4</td>
															<td height="10"  style=" width:25%;" valign="top">'.$cie_deta4.'</td>
															<td height="10"  style=" width:10%;" valign="top">'.$cie4.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie_p4.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie_d4.'</td>
														</tr>
														<tr>
															<td height="10"  style=" width:5%;" valign="top">2</td>
															<td height="10"  style=" width:25%;" valign="top">'.$cie_deta2.'</td>
															<td height="10"  style=" width:10%;" valign="top">'.$cie2.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie_p2.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie_d2.'</td>
															<td height="10"  style=" width:5%;" valign="top">5</td>
															<td height="10"  style=" width:25%;" valign="top">'.$cie_deta5.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie5.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie_p5.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie_d5.'</td>
														</tr>
														<tr>
															<td height="10"  style=" width:5%;" valign="top">3</td>
															<td height="10"  style=" width:25%;" valign="top">'.$cie_deta3.'</td>
															<td height="10"  style=" width:10%;" valign="top">'.$cie3.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie_p3.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie_d3.'</td>
															<td height="10"  style=" width:5%;" valign="top">6</td>
															<td height="10"  style=" width:25%;" valign="top">'.$cie_deta6.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie6.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie_p6.'</td>
															<td height="10"  style=" width:5%;" valign="top">'.$cie_d6.'</td>
														</tr>
													</table>';
											$ban_t=true;
										}
										
										if($tipo=='TABLE8'&& $ban_t8==false){
											/*$html.='
																<table  align="center" style="width:100%; margin-top:20px; margin-left:10px" border="1" cellpadding="0" cellspacing="0" >
																			<tr>
																				<td   style=" background-color:'.$empr_web_color.' width:100%;" ><h5>3 ANTECEDENTES FAMILIARES</h5></td>
																			</tr>
																			<tr>
																				<td align="center" style=" width:7%; font-size:12px;">1. CARDIOPATIA</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t8_text1.'</td>
																				<td align="center" style=" width:7%; font-size:12px;">2. DIABETES</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t8_text2.'</td>
																				<td align="center" style=" width:7%; font-size:12px;">3. ENF. C. VASCULAR</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t8_text3.'</td>
																				<td align="center" style=" width:7%; font-size:12px;">4. HIPER TENSION</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t8_text4.'</td>
																				<td align="center" style=" width:7%; font-size:12px;">5. CANCER</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t8_text5.'</td>
																				<td align="center" style=" width:9%; font-size:12px;">6. TUBERCULOSIS</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t8_text6.'</td>
																				<td align="center" style=" width:7%; font-size:12px;">7. ENE. MENTAL</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t8_text7.'</td>
																				<td align="center" style=" width:7%; font-size:12px;">8. ENF. INFECCIOSA</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t8_text8.'</td>
																				<td align="center" style=" width:7%; font-size:12px;">9. MAL FORMACION</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t8_text9.'</td>
																				<td align="center" style=" width:5%; font-size:12px;">10. OTRO</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t8_text10.'</td>
																			</tr>
																			<tr>
																				<td   style=" width:100%;">'.$t8_text11.'</td>
																				
																			</tr>
																				
																</table>
															
													';*/
											$n1=espacios(35);

											$html.='<table align="center" style="width:100%;" border="1" cellpadding="0" cellspacing="0" >
														<tr>
														<td align="left"><b>C. ANTECEDENTES PATOLÓGICOS PERSONALES</b>'.$n1.'<font size="5">DATOS CLÍNICO - QUIRÚRGICOS, OBSTÉTRICOS, ALÉRGICOS RELEVANTES</font></td>
														</tr>
														<tr>
															<td style="width: 9%; font-size: 50%; text-align: left;"><b>1.</b> CARDIOPATÍA</td>
															<td style="width: 3%; font-size: 50%; text-align: center;"></td>
															<td style="width: 8%; font-size: 50%; text-align: left;"><b>2.</b>HIPERTENSIÓN</td>
																<td style="width: 3%; font-size: 50%; text-align: center;"></td>
															<td style="width: 7%; font-size: 50%; text-align: left;"><b>3.</b> ENF. C.VASCULAR</td>
															<td style="width: 3%; font-size: 50%; text-align: center;"></td>
															<td style="width: 7%; font-size: 50%; text-align: center;"><b>4.</b> ENDÓCRINO METABÓLICO </td>
															<td style="width: 3%; font-size: 50%; text-align: center;"></td>
															<td style="width: 5%; font-size: 50%; text-align: center;"><b>5.</b> CÁNCER</td>
															<td style="width: 3%; font-size: 50%; text-align: center;"></td>
															<td style="width: 8%; font-size: 49%; text-align: center;"><b>6.</b><br> TUBERCULOSIS</td>
															<td style="width: 3%; font-size: 50%; text-align: center;"></td>
															<td style="width: 7%; font-size: 50%; text-align: center;"><b>7.</b> ENF.MENTAL</td>
															<td style="width: 3%; font-size: 50%; text-align: center;"></td>
															<td style="width: 7%; font-size: 50%; text-align: center;"><b>8.</b> ENF.<br>INFECCIOSA</td>
															<td style="width: 3%; font-size: 50%; text-align: center;"></td>
															<td style="width: 7%; font-size: 50%; text-align: center;"><b>9.</b> MAL FORMACIÓN</td>
															<td style="width: 3%; font-size: 50%; text-align: center;"></td>
															<td style="width: 4%; font-size: 50%; text-align: center;"><b>10.</b> OTRO</td>
															<td style="width: 3%; font-size: 50%; text-align: center;"></td>
														
														</tr>
														<tr>
															<td style="width: 100%; height: 20px; font-size: 50%; text-align: center;"></td>
														</tr>
														<tr>
															<td style="width: 100%; height: 20px; font-size: 50%; text-align: center;"></td>
														</tr>
														<tr>
															<td style="width: 100%; height: 20px; font-size: 50%; text-align: center;"></td>
														</tr>
														<tr>
															<td style="width: 100%; height: 20px; font-size: 50%; text-align: center;"></td>
														</tr>
															
													
													</table>';

													$html.='<table align="center" style="width:100%;" border="1" cellpadding="0" cellspacing="0" >
													<tr>
													<td align="left"><b>D. ANTECEDENTES PATOLÓGICOS FAMILIARES</b></td>
													</tr>
													<tr>
														<td style="width: 9%; font-size: 50%; text-align: left;"><b>1.</b> CARDIOPATÍA</td>
														<td style="width: 3%; font-size: 50%; text-align: center;"></td>
														<td style="width: 8%; font-size: 50%; text-align: left;"><b>2.</b>HIPERTENSIÓN</td>
															<td style="width: 3%; font-size: 50%; text-align: center;"></td>
														<td style="width: 7%; font-size: 50%; text-align: left;"><b>3.</b> ENF. C.VASCULAR</td>
														<td style="width: 3%; font-size: 50%; text-align: center;"></td>
														<td style="width: 7%; font-size: 50%; text-align: center;"><b>4.</b> ENDÓCRINO METABÓLICO </td>
														<td style="width: 3%; font-size: 50%; text-align: center;"></td>
														<td style="width: 5%; font-size: 50%; text-align: center;"><b>5.</b> CÁNCER</td>
														<td style="width: 3%; font-size: 50%; text-align: center;"></td>
														<td style="width: 8%; font-size: 49%; text-align: center;"><b>6.</b><br> TUBERCULOSIS</td>
														<td style="width: 3%; font-size: 50%; text-align: center;"></td>
														<td style="width: 7%; font-size: 50%; text-align: center;"><b>7.</b> ENF.MENTAL</td>
														<td style="width: 3%; font-size: 50%; text-align: center;"></td>
														<td style="width: 7%; font-size: 50%; text-align: center;"><b>8.</b> ENF.<br>INFECCIOSA</td>
														<td style="width: 3%; font-size: 50%; text-align: center;"></td>
														<td style="width: 7%; font-size: 50%; text-align: center;"><b>9.</b> MAL FORMACIÓN</td>
														<td style="width: 3%; font-size: 50%; text-align: center;"></td>
														<td style="width: 4%; font-size: 50%; text-align: center;"><b>10.</b> OTRO</td>
														<td style="width: 3%; font-size: 50%; text-align: center;"></td>
													
													</tr>
													<tr>
														<td style="width: 100%; height: 20px; font-size: 50%; text-align: center;"></td>
													</tr>
													<tr>
														<td style="width: 100%; height: 20px; font-size: 50%; text-align: center;"></td>
													</tr>
													<tr>
														<td style="width: 100%; height: 20px; font-size: 50%; text-align: center;"></td>
													</tr>
													<tr>
														<td style="width: 100%; height: 20px; font-size: 50%; text-align: center;"></td>
													</tr>
														
												
												</table>';
													$ban_t8=true;
										}
										if($tipo=='TABLE9'&& $ban_t9==false){
											/*$html.='
																<table  align="center" style="width:100%; margin-top:20px; margin-left:10px" border="1" cellpadding="0" cellspacing="0" >
																			<tr>
																				<td  tyle="background-color:'.$empr_web_color.' width:100%;" ><h5>5 REVISION ACTUAL DE ORGANOS Y SISTEMAS</h5></td>
																			</tr>
																			<tr>
																				<td>CP: CON EVIDENCIA DE PATOLOGIA <br> SP: SIN EVIDENCIA DE PATOLOGIA</td>
																				<td>CP</td>
																				<td>SP</td>
																				<td></td>
																				<td>CP</td>
																				<td>SP</td>
																				<td></td>
																				<td>CP</td>
																				<td>SP</td>
																				<td></td>
																				<td>CP</td>
																				<td>SP</td>
																				<td></td>
																				<td>CP</td>
																				<td>SP</td>
																			</tr>
																			<tr>
																				<td align="center" style=" width:10%; font-size:12px;">1. ORGANOS DE LOS SENTIDOS</td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:10%; font-size:12px;">3. CARDIO VASCULAR</td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:10%; font-size:12px;">5. GENITAL</td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:10%; font-size:12px;">7. MUSCULOS ESQUELETICO</td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:10%; font-size:12px;">9. HEMO LINFATICO</td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				
																			</tr>
																			<tr>
																				<td align="center" style=" width:10%; font-size:12px;">2. RESPIRATORIO</td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:10%; font-size:12px;">4. DIGESTIVO</td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:10%; font-size:12px;">6. URINARIO</td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:10%; font-size:12px;">8. ENDOCRINO</td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:10%; font-size:12px;">10. NERVIOSO</td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				<td align="center" style=" width:2%; font-size:12px;"></td>
																				
																			</tr>
																			<tr>
																				<td  style=" width:100%;"></td>
																				
																			</tr>
																				
																</table>
															
													';*/
													$n2=espacios(53);


													$html.='
																		<table  align="center" style="width:100%; margin-top:20px; margin-left:10px" border="1" cellpadding="0" cellspacing="0" >
																					<tr>
																	
																						<td align="left" style="background-color:'.$empr_web_color.' width:100%;"><b>G. REVISIÓN ACTUAL DE ÓRGANOS Y SISTEMAS</b>'.$n2.'<font size="5">MARCAR "X" CUANDO PRESENTE PATOLOGÍA Y DESCRIBA</font></td>
																					</tr>
																					<tr>
																						<td style="width: 3%; font-size: 65%; text-align: center; font-weight: bold;">1</td>
																						<td  style="width: 10%; font-size: 65%; text-align: center;">PIEL - ANEXOS</td>
																						<td style="width: 5%; font-size: 65%; text-align: center; font-weight: bold;"></td>
																						<td style="width: 3%; font-size: 65%; text-align: center; font-weight: bold;">3</td>
																						<td  style="width: 10%; font-size: 65%; text-align: center;">RESPIRATORIO</td>
																						<td style="width: 5%; font-size: 65%; text-align: center; font-weight: bold;"></td>
																						<td style="width: 3%; font-size: 65%; text-align: center; font-weight: bold;">5</td>
																						<td  style="width: 10%; font-size: 65%; text-align: center;">DIGESTIVO</td>
																						<td style="width: 5%; font-size: 65%; text-align: center; font-weight: bold;"></td>
																						<td style="width: 3%; font-size: 65%; text-align: center; font-weight: bold;">7</td>
																						<td  style="width: 17%; font-size: 65%; text-align: center;">MÚSCULO - ESQUELÉTICO</td>
																						<td style="width: 5%; font-size: 65%; text-align: center; font-weight: bold;"></td>
																						<td style="width: 3%; font-size: 65%; text-align: center; font-weight: bold;">9</td>
																						<td  style="width: 13%; font-size: 65%; text-align: center;">HEMO - LINFÁTICO</td>
																						<td style="width: 5%; font-size: 65%; text-align: center; font-weight: bold;"></td>
																						
																				
																					</tr>
																					<tr>
																						<td style="width: 3%; font-size: 65%; text-align: center; font-weight: bold;">2</td>
																						<td  style="width: 10%; font-size: 65%; text-align: center;">ORGANOS DE LOS SENTIDOS</td>
																						<td style="width: 5%; font-size: 65%; text-align: center; font-weight: bold;"></td>
																						<td style="width: 3%; font-size: 65%; text-align: center; font-weight: bold;">4</td>
																						<td  style="width: 10%; font-size: 65%; text-align: center;">CARDIO - VASCULAR</td>
																						<td style="width: 5%; font-size: 65%; text-align: center; font-weight: bold;"></td>
																						<td style="width: 3%; font-size: 65%; text-align: center; font-weight: bold;">6</td>
																						<td  style="width: 10%; font-size: 65%; text-align: center;">GENITO - URINARIO</td>
																						<td style="width: 5%; font-size: 65%; text-align: center; font-weight: bold;"></td>
																						<td style="width: 3%; font-size: 65%; text-align: center; font-weight: bold;">8</td>
																						<td  style="width: 17%; font-size: 65%; text-align: center;">ENDOCRINO</td>
																						<td style="width: 5%; font-size: 65%; text-align: center; font-weight: bold;"></td>
																						<td style="width: 3%; font-size: 65%; text-align: center; font-weight: bold;">10</td>
																						<td  style="width: 13%; font-size: 65%; text-align: center;">NERVIOSO</td>
																						<td style="width: 5%; font-size: 65%; text-align: center; font-weight: bold;"></td>
																						
																					</tr>
																					<tr>
																						<td style="width: 100%; height: 20px; font-size: 50%; text-align: center;"></td>
																						
																					</tr>
																					<tr>
																						<td style="width: 100%; height: 20px; font-size: 50%; text-align: center;"></td>
																					</tr>
																					<tr>
																					<td style="width: 100%; height: 20px; font-size: 50%; text-align: center;"></td>
																					</tr>
																						
																		</table>
																	
															';
		
													$ban_t9=true;
										}
										if($tipo=='TABLE10'&& $ban_t10==false){
											$html.='
											<table  align="center" style="width:100%; margin-top:20px; margin-left:10px" border="1" cellpadding="0" cellspacing="0" >
														<tr>
															<td  style=" background-color:'.$empr_web_color.' width:100%;" align="left" ><h5>F. CONSTANTES VITALES Y ANTROPOMETRÍA</h5></td>
														</tr>
														<tr>
															<td  style="width: 6%; font-size: 65%; text-align: center;">FECHA</td>
															<td  style="width: 5%; font-size: 65%; text-align: center;">HORA</td>
															<td  style="width: 10%; font-size: 65%; text-align: center;">Temperatura (°C)</td>
															<td  style="width: 6%; font-size: 65%; text-align: center;">Pulso / min</td>
															<td  style="width: 10%; font-size: 65%; text-align: center;">Presión Arterial (mmHg)</td>	
															<td  style="width: 10%; font-size: 65%; text-align: center;">Frecuencia Respiratoria/min</td>
															<td  style="width: 6%; font-size: 65%; text-align: center;">Peso (Kg)</td>
															<td  style="width: 6%; font-size: 65%; text-align: center;">Talla (cm)</td>
															<td  style="width: 6%; font-size: 65%; text-align: center;">IMC<br> (Kg / m 2)</td>
															<td  style="width: 10%; font-size: 65%; text-align: center;">Perímetro <br>Abdominal (cm)</td>
															<td  style="width: 8%; font-size: 65%; text-align: center;">Hemoglobina<br> capilar (g/dl)</td>
															<td  style="width: 9%; font-size: 65%; text-align: center;">Glucosa <br>capilar (mg/ dl)</td>
															<td  style="width: 8%; font-size: 65%; text-align: center;">Pulsioximetría<br> (%)</td>
															
															
														</tr>
														<tr>
														<td style="height: 20px; width: 6%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 5%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 10%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 6%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 10%; font-size: 65%; text-align: center;"></td>	
														<td style="height: 20px; width: 10%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 6%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 6%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 6%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 10%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 8%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 9%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 8%; font-size: 65%; text-align: center;"></td>
													</tr>
															<tr>
														<td style="height: 20px; width: 6%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 5%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 10%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 6%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 10%; font-size: 65%; text-align: center;"></td>	
														<td style="height: 20px; width: 10%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 6%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 6%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 6%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 10%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 8%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 9%; font-size: 65%; text-align: center;"></td>
														<td style="height: 20px; width: 8%; font-size: 65%; text-align: center;"></td>
													</tr>
													
													
														
															
											</table>
										
								';
											/*$html.='
																<table  align="center" style="width:100%; margin-top:20px; margin-left:10px" border="1" cellpadding="0" cellspacing="0" >
																			<tr>
																				<td  style=" background-color:'.$empr_web_color.' width:100%;" ><h5>6 SIGNOS VITALES Y ANTROPOMETRIA</h5></td>
																			</tr>
																			<tr>
																				<td colspan="2" align="center" style=" width:20%; font-size:12px;">FECHA DE MEDIACION</td>
																				<td colspan="2" align="center" style=" width:20%; font-size:12px;">'.$t10_text1.'</td>
																				<td colspan="2" align="center" style=" width:20%; font-size:12px;">'.$t10_text2.'</td>
																				<td colspan="2" align="center" style=" width:20%; font-size:12px;">'.$t10_text3.'</td>
																				<td colspan="2" align="center" style=" width:20%; font-size:12px;">'.$t10_text4.'</td>
																				
																			</tr>
																			<tr>
																				<td colspan="2" align="center" style=" width:20%; font-size:12px;">TEMPERATURA C</td>
																				<td colspan="2" align="center" style=" width:20%; font-size:12px;">'.$t10_text5.'</td>
																				<td colspan="2" align="center" style=" width:20%; font-size:12px;">'.$t10_text6.'</td>
																				<td colspan="2" align="center" style=" width:20%; font-size:12px;">'.$t10_text7.'</td>
																				<td colspan="2" align="center" style=" width:20%; font-size:12px;">'.$t10_text8.'</td>
																				
																			</tr>
																			<tr>
																				<td colspan="2" align="center" style=" width:20%; font-size:12px;">PRESION ARTERIAL C</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text9.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text10.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text11.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text12.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text13.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text14.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text15.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text16.'</td>
																				
																			</tr>
																			<tr>
																				<td align="center" style=" width:10%; font-size:12px;">PULSO/min</td>
																				<td align="center" style=" width:10%; font-size:12px;">FRECUENCIA RESPIRATORIO</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text17.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text18.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text19.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text20.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text21.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text22.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text23.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text24.'</td>
																				
																			</tr>
																			<tr>
																				<td align="center" style=" width:10%; font-size:12px;">PESO/kg</td>
																				<td align="center" style=" width:10%; font-size:12px;">TALLA/cm </td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text25.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text26.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text27.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text28.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text29.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text30.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text31.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">'.$t10_text32.'</td>
																				
																			</tr>
																			
																				
																</table>
															
													';*/
													$ban_t10=true;
										}
										if($tipo=='TABLE11'&& $ban_t11==false){
											$html.='
																<table  align="center" style="width:100%; margin-top:20px; margin-left:10px" border="1" cellpadding="0" cellspacing="0" >
																			<tr>
																				<td    style=" background-color:'.$empr_web_color.' width:100%;" ><h5>7 EXAMEN FISICO REGIONAL</h5></td>
																			</tr>
																			<tr>
																				<td>CP: CON EVIDENCIA DE PATOLOGIA <br> SP: SIN EVIDENCIA DE PATOLOGIA</td>
																				<td>CP</td>
																				<td>SP</td>
																				<td></td>
																				<td>CP</td>
																				<td>SP</td>
																				<td></td>
																				<td>CP</td>
																				<td>SP</td>
																				<td></td>
																				<td>CP</td>
																				<td>SP</td>
																				<td></td>
																				<td>CP</td>
																				<td>SP</td>
																				<td></td>
																				<td>CP</td>
																				<td>SP</td>
																			</tr>
																			<tr>
																				<td align="center" style=" width:10%; font-size:12px;">1. CABEZA</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t11_text1_d.'</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t11_text1_p.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">2. CUELLO</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t11_text2_d.'</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t11_text2_p.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">3. TORAX</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t11_text3_d.'</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t11_text3_p.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">4. ABDOMEN</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t11_text4_d.'</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t11_text4_p.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">5. PELVIS</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t11_text5_d.'</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t11_text5_p.'</td>
																				<td align="center" style=" width:10%; font-size:12px;">6. EXTREMIDADES</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t11_text6_d.'</td>
																				<td align="center" style=" width:2%; font-size:12px;">'.$t11_text6_p.'</td>
																			</tr>
																			<tr>
																				<td   style=" width:100%;">'.$t11_text7.'</td>
																				
																			</tr>
																				
																</table>
															
													';
													$ban_t11=true;
										}
										if($tipo=='TABLE12'&& $ban_t12==false){
											$html.='
									<table  border="1" cellpadding="0" cellspacing="0">
											<tr>
															<td  bgcolor="'.$empr_web_color.'" style="font-size:90%;" width="100%"; align="left" ><b>'.$pregunta.'</b></td>
														</tr>
										<tr>
											<td >
												EJEMPLO
											</td>
										</tr>
									</table>';
											$ban_t12=true;
										}
										if($tipo=='MEDICO'&& $ban_t3==false){
											$html.='<br><br><table   border="1" cellpadding="2" cellspacing="0">
														<tr>
															<td  colspan="5" bgcolor="'.$empr_web_color.'" style="font-size:90%;" width="100%"; align="left" ><b>'.$pregunta.'</b></td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="15%"><b>FECHA</b><br>(aaaa-mm-dd)</td>
															<td style="font-size:60%;"align="center" width="15%"><b>HORA</b></td>
															<td style="font-size:60%;"align="center" width="70%" colspan="2"><b>APELLIDOS Y NOMBRES</b></td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="15%"></td>
															<td style="font-size:60%;"align="center" width="15%"></td>
															<td style="font-size:60%;"align="center" width="70%" colspan="2">'.$nombre_usuario.'</td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="30%" colspan="2"><b>NÚMERO DE DOCUMENTO DE IDENTIFICACIÓN</b></td>
															<td style="font-size:60%;"align="center" width="35%"><b>FIRMA</b></td>
															<td style="font-size:60%;"align="center" width="35%"><b>SELLO</b></td>
														</tr>
														<tr>
															<td style="font-size:60%;"align="center" width="30%" colspan="2"></td>
															<td style="font-size:60%;"align="center" width="35%"></td>
															<td style="font-size:60%;"align="center" width="35%"></td>
														</tr>

													</table>';


											$ban_t3=true;
										}
									}while($oConA->SiguienteRegistro());
								}
							}
							//$pie_form=$arreglo_formularios_lados[$id_lado][0];
							$html.='
									<table   border="0" cellpadding="0" cellspacing="0" >
										<tr>
											<td width="50%">
												<b>SNS-MSP / HCU-form.002 / 2007</b>
											</td>
											<td align="right" width="50%" >
												<b>CONSULTA EXTERNA - ANAMNESIS Y EXAMEN FÍSICO</b>
											</td>
										</tr>
									</table>';
									$html.='||||||||||';
					break;
					///FOMULARIO 053 NUEVO
					case 7:
						if($id_lado==11){
							//DATOS DEl FORMUALRIOS
							$htmltext='';   

							//VARIABLES DIGANOSTICOS
							$d=1;
							$cabeceradiag='';
							$countdiag=0;
							$cod_msp=0;
							$array_check=array();
							$htmlcheck='';

							$sql="select count(*) as cont from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and tipo='TABLE'";
							$countdiag=consulta_string($sql,'cont',$oConA,0);
							
							$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' order by id_tipo_formulario_datos";

								if($oConA->Query($sql)){
									if($oConA->NumFilas()>0){
										do{
											$pref='';
											$def='';
											$pregunta=$oConA->f('pregunta');
											$respuesta=$oConA->f('respuesta');
											$tipo=$oConA->f('tipo');
											$cod_cie=$oConA->f('cod_cie');
											$res_cie=$oConA->f('tipo_resp_cie');
											$id_input=$oConA->f('id_input');

											if($id_input=="medicotrad"){
												$codmedt=$oConA->f('respuesta');
		
												
												if(!empty($codmedt)){
													$sqlc="select clpv_nom_clpv, clpv_ruc_clpv from saeclpv where clpv_cod_clpv=$codmedt";
													$med_tratante=consulta_string($sqlc, 'clpv_nom_clpv',$oIfxA,'');
													$ruc_tratante=substr(consulta_string($sqlc, 'clpv_ruc_clpv',$oIfxA,''),0,10);
												  }
										
					
											}
											if($id_input=="138_fecha"){
												$fechaf=$oConA->f('respuesta');
												$diaref=date('d',strtotime($fechaf));
												$mesref=date('m',strtotime($fechaf));
												$anioref=date('Y',strtotime($fechaf));
										
											}
											if($id_input=='texto6'){
												$instref=$respuesta;
											}
											if($id_input=='texto7'){
												$servref=$respuesta;
											}
											if($id_input=='texto8'){
												$esperef=$respuesta;
											}



											if($res_cie=='P'){
												$pref='X';
												$def='';
											}
											elseif($res_cie=='D'){
												$pref='';
												$def='X';
											}
											

											if($tipo=='RADIO'){
												if($respuesta==1){
												$referencia='X';
												$derivacion='';
												$tiporef='I';
												}
												elseif($respuesta==2){
													$referencia='';
													$derivacion='X';
													$tiporef='II';
												}

											}
											if($tipo=='CHECK'){
												array_push($array_check,substr($id_input,2,2));

											}
											if($tipo=='TABLE'){
											
											if($d==1){
												$htmltext.='
										<tr>
											<td>
														<table align="center" style="width:100%; " border="1" >
															<tr>
																<td width="83%" align="left" ><strong>'.$pregunta.'</strong></td>
																<td width="7%" align="center" style="font-size:85%;"><strong>CIE 10</strong></td>
																<td width="5%" align="center" style="font-size:85%;"><strong>PRE</strong></td>
																<td width="5%" align="center" style="font-size:85%;"><strong>DEF</strong></td>
															</tr>
															<tr>
															<td width="3%" align="center" style="font-size:85%;">'.$d.'</td>
															<td width="80%" align="justify" style="font-size:85%;">'.$respuesta.'</td>
															<td width="7%" align="center" style="font-size:85%;">'.$cod_cie.'</td>
															<td width="5%" align="center" style="font-size:85%;">'.$pref.'</td>
															<td width="5%" align="center" style="font-size:85%;">'.$def.'</td>
															</tr>';

											}	
											
											elseif($d==$countdiag){
												$htmltext.='<tr>
												<td width="3%" align="center" style="font-size:85%;">'.$d.'</td>
												<td width="80%" align="justify" style="font-size:85%;">'.$respuesta.'</td>
												<td width="7%" align="center" style="font-size:85%;">'.$cod_cie.'</td>
												<td width="5%" align="center" style="font-size:85%;">'.$pref.'</td>
												<td width="5%" align="center" style="font-size:85%;">'.$def.'</td>
												</tr></table></td>
												</tr>';
											}
											else{
												$htmltext.='<tr>
												<td width="3%" align="center" style="font-size:85%;">'.$d.'</td>
												<td width="80%" align="justify" style="font-size:85%;">'.$respuesta.'</td>
												<td width="7%" align="center" style="font-size:85%;">'.$cod_cie.'</td>
												<td width="5%" align="center" style="font-size:85%;">'.$pref.'</td>
												<td width="5%" align="center" style="font-size:85%;">'.$def.'</td>
												</tr>';
											}
											if($countdiag==1){
												$htmltext.='</table></td>
												</tr>';
											}

											$d++;
											}

											if($tipo=='TEXT'||$tipo=='TEXTAREA'){
												 if($id_input!='texto6'&&$id_input!='texto7'&&$id_input!='texto8'){
													$htmltext.='
													<tr>
													<td><strong>'.$pregunta.'</strong></td>
													</tr>
													<tr>
														<td>
															<table align="center" style="width:100%; " border="1" >
																<tr>
																	<td align="justify" style="font-size:85%;">'.$respuesta.'</td>
																</tr>
																
															</table>
														</td>
													</tr>';
												 }
												
											}
											

										}while($oConA->SiguienteRegistro());
									}
								}
							$oConA->Free();	

							if(count($array_check>0)){

								$htmlcheck='';

								$sqlc="
								select distinct(id_tipo_formulario_datos) as formlado,tipo from clinico.formulario_iess_detalle where id_formulario_iess=$id_f";
								if($oConA->Query($sqlc)){
									if($oConA->NumFilas()>0){
										do{
											$idform=$oConA->f('formlado');
											$tipo=$oConA->f('tipo');

											if($tipo=='CHECK'){
												$sqlt="select label from clinico.tipos_formulario_datos_lado where id= $idform";
												$titulo=consulta_string($sqlt,'label',$oConB,'');
												$htmlcheck.='
												<tr>
												<td colspan="6"><strong>'.$titulo.'</strong></td>
												</tr>
												<tr>
												<td><table align="center" style="width:100%; " border="0" >
												';

												//CONTEO ITEMS
												$sqlcont="select count(*) as conteo from clinico.tipos_formulario_datos_lado_opciones where id_formulario_datos_lado=$idform";
												$cont5=consulta_string($sqlcont,'conteo',$oConB,1);

												//CONTEO DE ITEMS
													while($cont5%2!=0){
														$cont5++;
													}
												
													$num_tb5=$cont5/2;


												//ARRAY DE ITEMS
												$arrayitem5=array();
												$sql = "select * from clinico.tipos_formulario_datos_lado_opciones where id_formulario_datos_lado=$idform";
												if ($oConB->Query($sql)) {
													
													if ($oConB->NumFilas() > 0) {
														do {
															$idradio = $oConB->f('id_radio');
															array_push($arrayitem5, $idradio);
														}while ($oConB->SiguienteRegistro());
													}
												}
												$oConB->Free();



												$k=0;
											for ($i=1; $i <=$num_tb5 ; $i++) { 
												
												for ($j=$k; $j <2*$i ; $j++) { 

													if($arrayitem5[$j]!=null){
														$aDatos5[$i][$j]=$arrayitem5[$j];
													}
													else{
														$aDatos5[$i][$j]=0;
													}
													
												} 
												$k+=2;
													
											}


											$k=0;
									
											for ($i=1; $i <=$num_tb5; $i++) { 
												$htmlcheck .= '<tr>';
												
												for ($j=$k; $j <2*$i ; $j++) { 

													
													$idradio=$aDatos5[$i][$j];

												

														$sqlo="select id_radio, label from clinico.tipos_formulario_datos_lado_opciones where id_formulario_datos_lado=$idform and id_radio='$idradio'";
												
														if($oConB->Query($sqlo)){
															if($oConB->NumFilas()>0){
																do{
																	$label=$oConB->f('label');
																	$radio=$oConB->f('id_radio');
																	$sqlres="select respuesta from clinico.formulario_iess_detalle where id_formulario_iess=$id_f and substring(id_input from 3 for 2)='$idradio'";
																	$rescheck=consulta_string($sqlres,'respuesta',$oConB,'');

																	

																	$htmlcheck.='<td align="left" style="font-size:85%;" width="42%">'.$label.'</td>
																				<td align="right" style="font-size:85%;" width="3%">'.$radio.'</td>';
																				if($rescheck=='S'){
																					$htmlcheck.='<td align="center" style="border:1px solid black; border-radius: 5px; font-size:85%;" width="5%">X</td>';
																				}
																				else{
																					$htmlcheck.='<td align="center" style="font-size:85%;" width="5%"></td>';
																				}

																}while($oConB->SiguienteRegistro());
															}
															else{
																$htmlcheck.='<td align="left" style="font-size:85%;" width="42%"></td>
																				<td align="right" style="font-size:85%;" width="3%"></td>
																				<td align="center" style="font-size:85%;" width="5%"></td>';
															}
														}
														$oConB->Free();

											}
											$k+=2;
											$htmlcheck .= '</tr>';
										
										}

										$htmlcheck .= '</table></td></tr>';
												
											//echo '<table border="1">'.$htmlcheck.'</table>';exit;
											}
												
																					
										}while($oConA->SiguienteRegistro());
									}
								}
								$oConA->Free();

								

							}

                      
							$html.='<br><br>
							<table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
								<tr>
								<td bgcolor="black" align="center" style="width:100%;"><strong><font color="white">FORMULARIO DE REFERENCIA, DERIVACIÓN CONTRAREFERENCIA Y REFERENCIA INVERSA</font></strong></td>
								</tr>
							</table>
							<table align="center" style="width:100%; " border="0" cellpadding="0" cellspacing="0">
							<tr>
							<td align="left"><strong>I. DATOS DEL USUARIO/USUARIA</strong></td>
							</tr>
							</table>
							<table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">	
								<tr bgcolor="'.$empr_web_color.'">
									
									<td  align="center" style="width:50%; font-size:90%;">NOMBRES Y APELLIDOS</td>
									<td  align="center" colspan="3" style="width:40%; font-size:90%;">Fecha de nacimiento:</td>
									<td  align="center" style="width:10%; font-size:90%;">Sexo:</td>
								</tr>
								<tr>
									<td align="center" style="width:50%; font-size:90%;">'.$nom_clpv.'</td>
									<td align="center" style="font-size:90%;">'.$dia_naci.'</td>
									<td align="center" style="font-size:90%;">'.$mes_naci.'</td>
									<td align="center" style="font-size:90%;">'.$anio_naci.'</td>
									<td colspan="2" align="center" style="width:10%;">'.$sexo.' </td>
								</tr>							
							</table>';
							$html.='<table align="center" style="width:100%; " border="0" cellpadding="0" cellspacing="0">
							<tr>
							<td style="width:50%;"></td>
							<td align="center" style="width:50%; font-size:90%;"><table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
							<tr>
							<td align="center" style="width:26.66%;">día</td><td align="center" style="width:26.66%;">mes</td><td align="center" style="width:26.66%;">año</td><td align="center" style="width:10%;">1=M</td><td align="center" style="width:10%;">2=F</td>
							</tr>
							</table></td>
							</tr>
							</table>';
							$html.='<table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
							<tr bgcolor="'.$empr_web_color.'">
								<td align="center" style="width:15%; font-size:85%;">Nacionalidad:</td>
								<td align="center" style="width:10%; font-size:85%;">País:</td>
								<td align="center" style="width:15%; font-size:85%;">Cedula o Pasaporte:</td>
								<td align="center" colspan="3" style="width:22%; font-size:85%;">Lugar de residencia actual:</td>
								<td align="center" style="width:26%; font-size:85%;">Dirección domiciliaria:</td>
								<td align="center" style="width:12%; font-size:85%;">No. Telefónico:</td>
							</tr>
							<tr>
								<td align="center" style="width:15%; font-size:85%;">'.$id_naci.'</td>
								<td align="center" style="width:10%; font-size:85%;">'.$nomb_pais.'</td>
								<td align="center" style="width:15%; font-size:85%;">'.$ruc_clpv.'</td>
								<td align="center" style="font-size:85%;">'.$provincia_resi.'</td>
								<td align="center" style="font-size:85%;">'.$ciudad_resi.'</td>
								<td align="center" style="font-size:85%;"></td>
								<td align="center" style="width:26%; font-size:85%;">'.$direccion.'</td>
								<td align="center" style="width:12%; font-size:85%;">'.$telefono.'</td>
							</tr>
							</table>';
							$html.='<table align="center" style="width:100%; " border="0" cellpadding="0" cellspacing="0">
							<tr>
							<td style="width:15%;">
							<table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
								<tr>
									<td align="center" style="width:50%; font-size:85%;">1=Ecu</td>
									<td align="center" style="width:50%; font-size:85%;">2=Ext</td>
								</tr>
							</table>
							</td>

							<td style="width:25%;"></td>

							<td style="width:22%;">
								<table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
									<tr>
										<td align="center" style="width:33.33%; font-size:85%;">Prov.</td>
										<td align="center" style="width:33.33%; font-size:85%;">Cantón</td>
										<td align="center" style="width:33.33%; font-size:85%;">Parroquia</td>
									</tr>
								</table>
							</td>

							<td style="width:35%;"></td>

							</tr>
							</table><br><br>';

							$html.='<table align="center" style="width:100%; " border="0" cellpadding="0" cellspacing="0">	
							<tr>
								<td align="center" style="width:20%; font-size:85%;"><strong>II. REFERENCIA:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1</strong></td>
								
								<td align="center" style="width:3%; font-size:85%;"><table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
										<tr>
											<td align="center" style="font-size:85%;">'.$referencia.'</td>
										</tr>
									</table>
								</td>
								<td align="center" style="width:20%; font-size:85%;"><strong>DERIVACIÓN:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2</strong></td>
								<td align="center" style="width:3%; font-size:85%;"><table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
									<tr>
										<td align="center" style=" font-size:85%;">'.$derivacion.'</td>
									</tr>
								</table>
							</td>
							</tr>							
							</table>';

							$html.='<table style="width: 100%; border:1px solid black; border-radius: 5px; padding: 2px; font-size:100%;" cellpadding="3" cellspacing="3">
							<tr>
							<td><strong>1. Datos Institucionales</strong></td>
							</tr>
							<tr>
							<td>
								<table align="center" style="width:100%; " border="1" >
									<tr bgcolor="'.$empr_web_color.'">
										<td align="center" style="font-size:85%;">Entidad del sistema</td>
										<td align="center" style="font-size:85%;">Hist. Clínica</td>
										<td align="center" style="font-size:85%;">Establecimiento de Salud</td>
										<td align="center" style="font-size:85%;">Tipo</td>
										<td align="center" style="font-size:85%;">Distrito/Área</td>
									</tr>
									<tr>
										<td align="center" style="font-size:85%;"></td>
										<td align="center" style="font-size:85%;">'.$secu_hist.'</td>
										<td align="center" style="font-size:85%;">'.$razonSocial.'</td>
										<td align="center" style="font-size:85%;">'.$tiporef.'</td>
										<td align="center" style="font-size:85%;"></td>
									</tr>
								</table>
							</td>
							</tr>
							';

							$html.='
							<tr>
							<td><strong>'.espacios(60).'Refiere o deriva a :</strong></td>
							</tr>
							<tr>
							<td><table align="center" style="width:100%;" border="1" >
									<tr>
										<td align="center" style="font-size:80%; width:20.75%;"></td>
										<td align="center" style="font-size:80%; width:20.75%;">'.$instref.'</td>
										<td align="center" style="font-size:80%; width:20.75%;">'.$servref.'</td>
										<td align="center" style="font-size:80%; width:20.75%;">'.$esperef.'</td>
										<td align="center" style="font-size:80%; width:5%;">'.$diaref.'</td>
										<td align="center" style="font-size:80%; width:5%;">'.$mesref.'</td>
										<td align="center" style="font-size:80%; width:7%;">'.$anioref.'</td>
									</tr>
									<tr bgcolor="'.$empr_web_color.'"><td align="center" style="font-size:85%; width:20.75%;">Entidad del sistema</td>
										<td align="center" style="font-size:85%; width:20.75%;">Establecimiento de Salud</td>
										<td align="center" style="font-size:85%; width:20.75%;">Servicio</td>
										<td align="center" style="font-size:85%; width:20.75%;">Especialidad</td>
										<td align="center" style="font-size:85%; width:5%;">día</td>
										<td align="center" style="font-size:85%; width:5%;">mes</td>
										<td align="center" style="font-size:85%; width:7%;">año</td>
									</tr>
								</table></td>
							</tr>';

							$html.='';

							$html.=$htmlcheck;
							$html.=$htmltext;

							$html.='<tr>
									<td>
										<table align="center" style="width:100%; " border="0" >
											<tr>
												<td width="15%" align="left" style="font-size:85%;">Nombre de profesional:</td>
												<td width="30%" align="justify" style="font-size:85%;"><u><strong>'.$med_tratante.'</strong></u></td>
												<td width="12%" align="center" style="font-size:85%;">Código MSP:</td>
												<td width="18%">
													<table align="center" style="width:100%; " border="1" >
													<tr>
														<td align="center" style="font-size:85%;">'.$ruc_tratante.'</td>
													</tr>
													</table>
												</td>
												<td width="7%" align="right" style="font-size:85%;">Firma:</td>
												<td width="18%" align="center" style="font-size:85%;">_________________</td>
											</tr>							
										</table>
									</td>
								</tr>';

							$html.='</table>';
					

						}
						if($id_lado==12){

							//DATOS DEl FORMUALRIOS
							$htmltext='';   

							//VARIABLES DIGANOSTICOS
							$d=1;
							$cabeceradiag='';
							$countdiag=0;
							$cod_msp=0;
							$array_check=array();
							$htmlcheck='';

							$sql="select count(*) as cont from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' and tipo='TABLE'";
							$countdiag=consulta_string($sql,'cont',$oConA,0);
							$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' order by id_tipo_formulario_datos";

								if($oConA->Query($sql)){
									if($oConA->NumFilas()>0){
										do{
											$pref='';
											$def='';
											$pregunta=$oConA->f('pregunta');
											$respuesta=$oConA->f('respuesta');
											$tipo=$oConA->f('tipo');
											$cod_cie=$oConA->f('cod_cie');
											$res_cie=$oConA->f('tipo_resp_cie');
											$id_input=$oConA->f('id_input');


											if($id_input=="medicotrad"){
												$codmedt=$oConA->f('respuesta');
		
												
												if(!empty($codmedt)){
													$sqlc="select clpv_nom_clpv, clpv_ruc_clpv from saeclpv where clpv_cod_clpv=$codmedt";
													$med_tratante=consulta_string($sqlc, 'clpv_nom_clpv',$oIfxA,'');
													$ruc_tratante=substr(consulta_string($sqlc, 'clpv_ruc_clpv',$oIfxA,''),0,10);
												  }
										
					
											}
											if($id_input=="145_fecha"){
												$fechaf=$oConA->f('respuesta');
												$diaref=date('d',strtotime($fechaf));
												$mesref=date('m',strtotime($fechaf));
												$anioref=date('Y',strtotime($fechaf));
										
											}
											if($id_input=='texto7'){
												$instref=$respuesta;
											}
											if($id_input=='texto8'){
												$servref=$respuesta;
											}
											if($id_input=='texto9'){
												$esperef=$respuesta;
											}
											
											if($res_cie=='P'){
												$pref='X';
												$def='';
											}
											elseif($res_cie=='D'){
												$pref='';
												$def='X';
											}
											

											if($tipo=='RADIO'){
												if($respuesta==1){
												$referencia='X';
												$derivacion='';
												$tiporef='I';
												}
												elseif($respuesta==2){
													$referencia='';
													$derivacion='X';
													$tiporef='II';
												}

											}
											if($tipo=='CHECK'){
												array_push($array_check,substr($id_input,2,2));

											}
											if($tipo=='TABLE'){
											
											if($d==1){
												$htmltext.='
										<tr>
											<td>
														<table align="center" style="width:100%; " border="1" >
															<tr>
																<td width="83%" align="left" ><strong>'.$pregunta.'</strong></td>
																<td width="7%" align="center" style="font-size:85%;"><strong>CIE 10</strong></td>
																<td width="5%" align="center" style="font-size:85%;"><strong>PRE</strong></td>
																<td width="5%" align="center" style="font-size:85%;"><strong>DEF</strong></td>
															</tr>
															<tr>
															<td width="3%" align="center" style="font-size:85%;">'.$d.'</td>
															<td width="80%" align="justify" style="font-size:85%;">'.$respuesta.'</td>
															<td width="7%" align="center" style="font-size:85%;">'.$cod_cie.'</td>
															<td width="5%" align="center" style="font-size:85%;">'.$pref.'</td>
															<td width="5%" align="center" style="font-size:85%;">'.$def.'</td>
															</tr>';

											}	
											
											elseif($d==$countdiag){
												$htmltext.='<tr>
												<td width="3%" align="center" style="font-size:85%;">'.$d.'</td>
												<td width="80%" align="justify" style="font-size:85%;">'.$respuesta.'</td>
												<td width="7%" align="center" style="font-size:85%;">'.$cod_cie.'</td>
												<td width="5%" align="center" style="font-size:85%;">'.$pref.'</td>
												<td width="5%" align="center" style="font-size:85%;">'.$def.'</td>
												</tr></table></td>
												</tr>';
											}
											else{
												$htmltext.='<tr>
												<td width="3%" align="center" style="font-size:85%;">'.$d.'</td>
												<td width="80%" align="justify" style="font-size:85%;">'.$respuesta.'</td>
												<td width="7%" align="center" style="font-size:85%;">'.$cod_cie.'</td>
												<td width="5%" align="center" style="font-size:85%;">'.$pref.'</td>
												<td width="5%" align="center" style="font-size:85%;">'.$def.'</td>
												</tr>';
											}
											if($countdiag==1){
												$htmltext.='</table></td>
												</tr>';
											}
											$d++;
											}

											if($tipo=='TEXT'||$tipo=='TEXTAREA'){
												if($id_input!='texto7'&&$id_input!='texto8'&&$id_input!='texto9'){
													$htmltext.='
													<tr>
													<td><strong>'.$pregunta.'</strong></td>
													</tr>
													<tr>
														<td>
															<table align="center" style="width:100%; " border="1" >
																<tr>
																	<td align="justify" style="font-size:85%;">'.$respuesta.'</td>
																</tr>
																
															</table>
														</td>
													</tr>';
												}
												
											}
											if($tipo=='COD'){
												$cod_msp=$respuesta;
											}

										}while($oConA->SiguienteRegistro());
									}
								}
							$oConA->Free();	



							$html.='<br><br>
							<table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
								<tr>
								<td bgcolor="black" align="center" style="width:100%;"><strong><font color="white">FORMULARIO DE REFERENCIA, DERIVACIÓN CONTRAREFERENCIA Y REFERENCIA INVERSA</font></strong></td>
								</tr>
							</table>
							<table align="center" style="width:100%; " border="0" cellpadding="0" cellspacing="0">
							<tr>
							<td align="left"><strong>I. DATOS DEL USUARIO/USUARIA</strong></td>
							</tr>
							</table>
							<table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">	
								<tr bgcolor="'.$empr_web_color.'">
									
									<td  align="center" style="width:50%; font-size:90%;">NOMBRES Y APELLIDOS</td>
									<td  align="center" colspan="3" style="width:40%; font-size:90%;">Fecha de nacimiento:</td>
									<td  align="center" style="width:10%; font-size:90%;">Sexo:</td>
								</tr>
								<tr>
									<td align="center" style="width:50%; font-size:90%;">'.$nom_clpv.'</td>
									<td align="center" style="font-size:90%;">'.$dia_naci.'</td>
									<td align="center" style="font-size:90%;">'.$mes_naci.'</td>
									<td align="center" style="font-size:90%;">'.$anio_naci.'</td>
									<td colspan="2" align="center" style="width:10%;">'.$sexo.' </td>
								</tr>							
							</table>';
							$html.='<table align="center" style="width:100%; " border="0" cellpadding="0" cellspacing="0">
							<tr>
							<td style="width:50%;"></td>
							<td align="center" style="width:50%; font-size:90%;"><table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
							<tr>
							<td align="center" style="width:26.66%;">día</td><td align="center" style="width:26.66%;">mes</td><td align="center" style="width:26.66%;">año</td><td align="center" style="width:10%;">1=M</td><td align="center" style="width:10%;">2=F</td>
							</tr>
							</table></td>
							</tr>
							</table>';
							$html.='<table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
							<tr bgcolor="'.$empr_web_color.'">
								<td align="center" style="width:15%; font-size:85%;">Nacionalidad:</td>
								<td align="center" style="width:10%; font-size:85%;">País:</td>
								<td align="center" style="width:15%; font-size:85%;">Cedula o Pasaporte:</td>
								<td align="center" colspan="3" style="width:22%; font-size:85%;">Lugar de residencia actual:</td>
								<td align="center" style="width:26%; font-size:85%;">Dirección domiciliaria:</td>
								<td align="center" style="width:12%; font-size:85%;">No. Telefónico:</td>
							</tr>
							<tr>
								<td align="center" style="width:15%; font-size:85%;">'.$id_naci.'</td>
								<td align="center" style="width:10%; font-size:85%;">'.$nomb_pais.'</td>
								<td align="center" style="width:15%; font-size:85%;">'.$ruc_clpv.'</td>
								<td align="center" style="font-size:85%;">'.$provincia_resi.'</td>
								<td align="center" style="font-size:85%;">'.$ciudad_resi.'</td>
								<td align="center" style="font-size:85%;"></td>
								<td align="center" style="width:26%; font-size:85%;">'.$direccion.'</td>
								<td align="center" style="width:12%; font-size:85%;">'.$telefono.'</td>
							</tr>
							</table>';
							$html.='<table align="center" style="width:100%; " border="0" cellpadding="0" cellspacing="0">
							<tr>
							<td style="width:15%;">
							<table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
								<tr>
									<td align="center" style="width:50%; font-size:85%;">1=Ecu</td>
									<td align="center" style="width:50%; font-size:85%;">2=Ext</td>
								</tr>
							</table>
							</td>

							<td style="width:25%;"></td>

							<td style="width:22%;">
								<table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
									<tr>
										<td align="center" style="width:33.33%; font-size:85%;">Prov.</td>
										<td align="center" style="width:33.33%; font-size:85%;">Cantón</td>
										<td align="center" style="width:33.33%; font-size:85%;">Parroquia</td>
									</tr>
								</table>
							</td>

							<td style="width:35%;"></td>

							</tr>
							</table><br><br>';

							$html.='<table align="center" style="width:100%; " border="0" cellpadding="0" cellspacing="0">	
							<tr>
								<td align="left" style="width:27%; font-size:85%;"><strong>III. CONTRAREFERENCIA:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3</strong></td>
								
								<td align="center" style="width:3%; font-size:85%;"><table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
										<tr>
											<td align="center" style="font-size:85%;">'.$referencia.'</td>
										</tr>
									</table>
								</td>
								<td align="left" style="width:27%; font-size:85%;"> <strong>REFERENCIA INVERSA:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4</strong></td>
								<td align="center" style="width:3%; font-size:85%;"><table align="center" style="width:100%; " border="1" cellpadding="0" cellspacing="0">
									<tr>
										<td align="center" style=" font-size:85%;">'.$derivacion.'</td>
									</tr>
								</table>
							</td>
							</tr>							
							</table>';

							$html.='<table style="width: 100%; border:1px solid black; border-radius: 5px; padding: 2px; font-size:100%;" cellpadding="3" cellspacing="3">
							<tr>
							<td><strong>1. Datos Institucionales</strong></td>
							</tr>
							<tr>
							<td>
								<table align="center" style="width:100%; " border="1" >
									<tr bgcolor="'.$empr_web_color.'">
										<td align="center" style="font-size:85%;">Entidad del sistema</td>
										<td align="center" style="font-size:85%;">Hist. Clínica</td>
										<td align="center" style="font-size:85%;">Establecimiento de Salud</td>
										<td align="center" style="font-size:85%;">Tipo</td>
										<td align="center" style="font-size:85%;">Servicio</td>
										<td align="center" style="font-size:85%;">Especialidad</td>
									</tr>
									<tr>
										<td align="center" style="font-size:85%;"></td>
										<td align="center" style="font-size:85%;">'.$secu_hist.'</td>
										<td align="center" style="font-size:85%;">'.$razonSocial.'</td>
										<td align="center" style="font-size:85%;">'.$tiporef.'</td>
										<td align="center" style="font-size:85%;">'.$servref.'</td>
										<td align="center" style="font-size:85%;">'.$esperef.'</td>
									</tr>
								</table>
							</td>
							</tr>
							';

							$html.='
							<tr>
							<td><strong>'.espacios(60).'Contrarefiere o Referencia inversa a :</strong></td>
							</tr>
							<tr>
							<td><table align="center" style="width:100%;" border="1" >
									<tr>
										<td align="center" style="font-size:85%; width:20.75%;"></td>
										<td align="center" style="font-size:85%; width:20.75%;">'.$instref.'</td>
										<td align="center" style="font-size:85%; width:20.75%;">'.$tiporef.'</td>
										<td align="center" style="font-size:85%; width:20.75%;"></td>
										<td align="center" style="font-size:85%; width:5%;">'.$diaref.'</td>
										<td align="center" style="font-size:85%; width:5%;">'.$mesref.'</td>
										<td align="center" style="font-size:85%; width:7%;">'.$anioref.'</td>
									</tr>
									<tr bgcolor="'.$empr_web_color.'"><td align="center" style="font-size:85%; width:20.75%;">Entidad del sistema</td>
										<td align="center" style="font-size:85%; width:20.75%;">Establecimiento de Salud</td>
										<td align="center" style="font-size:85%; width:20.75%;">Tipo</td>
										<td align="center" style="font-size:85%; width:20.75%;">Distrito/Área</td>
										<td align="center" style="font-size:85%; width:5%;">día</td>
										<td align="center" style="font-size:85%; width:5%;">mes</td>
										<td align="center" style="font-size:85%; width:7%;">año</td>
									</tr>
								</table></td>
							</tr>';

							$html.='';

							$html.=$htmlcheck;
							$html.=$htmltext;

							$html.='<tr>
									<td>
										<table align="center" style="width:100%; " border="0" >
											<tr>
												<td width="15%" align="left" style="font-size:85%;">Nombre de profesional:</td>
												<td width="30%" align="justify" style="font-size:85%;"><u><strong>'.$med_tratante.'</strong></u></td>
												<td width="12%" align="center" style="font-size:85%;">Código MSP:</td>
												<td width="18%">
													<table align="center" style="width:100%; " border="1" >
													<tr>
														<td align="center" style="font-size:85%;">'.$ruc_tratante.'</td>
													</tr>
													</table>
												</td>
												<td width="7%" align="right" style="font-size:85%;">Firma:</td>
												<td width="18%" align="center" style="font-size:85%;">_________________</td>
											</tr>							
										</table>
									</td>
								</tr>';

							$html.='</table>';

						}//CIERRE IF 12

						$html.='||||||||||';
					break;	

					case 8:
						if($sexo==1){
							$sexo='M';
						}
						if($sexo==2){
							$sexo='F';
						}
										
						$html.='<div>
						<br><br>
						<table border="1" cellpadding="1" cellspacing="0">
						<tr  style="background-color:'.$empr_web_color.'"> 
							<th style="font-size:60%;"align="center" width="180" ><b>INSTITUCIÓN DEL SISTEMA</b></th>
							<th style="font-size:60%;"align="center" width="160"><b>UNIDAD OPERATIVA</b></th>
							<th style="font-size:60%;" align="center" width="60"><b>COD. UO</b></th>	
							<th colspan="3" style="font-size:60%;" align="center" width="150"><b>COD. LOCALIZACIÓN</b></th>
							<th  rowspan="2" style="font-size:60%;" align="center" width="109.4"><b>NÚMERO DE HISTORIA CLÍNICA</b></th>
						</tr>
						<tr> 
							<td rowspan="2" style="font-size:60%;"align="center">IESS</td>
							<td rowspan="2" style="font-size:60%;"align="center" >'.$razonSocial.'</td>
							<td rowspan="2"  style="font-size:60%;" align="center">520</td>
							<td style="background-color:'.$empr_web_color.'; font-size:40%;"align="center" width="50">PARROQUIA</td>
							<td style="background-color:'.$empr_web_color.'; font-size:40%;"align="center" width="50">CANTÓN</td>
							<td style="background-color:'.$empr_web_color.'; font-size:40%;"align="center" width="50">PROVINCIA</td>
						</tr>
						<tr>
							<td style="font-size:40%;"align="center" >13</td>
							<td style="font-size:40%;"align="center">QUITO</td>
							<td style="font-size:40%;"align="center">PICHINCHA</td>
							<td style="font-size:60%;"align="center">'.$secu_hist.'</td>
						</tr>
						</table>
						<table border="1" cellpadding="2" cellspacing="0">
						<tr style="background-color:'.$empr_web_color.'">
						<td style="font-size:50%;"align="center" width="450"> APELLIDO PATERNO&nbsp;&nbsp;&nbsp;APELLIDO MATERNO&nbsp;&nbsp;&nbsp;PRIMER NOMBRE&nbsp;&nbsp;&nbsp;SEGUNDO NOMBRE</td>
						<td style="font-size:50%;"align="center" width="50">EDAD</td>
						<td style="font-size:50%;"align="center" width="159.4">No CÉDULA DE CIUDADANÍA</td>
						</tr>
						<tr>
						<td style="font-size:50%;"align="center" >'.$nom_clpv.'</td>
						<td style="font-size:50%;"align="center" >'.$edad.'</td>
						<td style="font-size:50%;"align="center" >'.$ruc_clpv.'</td>
						</tr>
						</table>';

						if($id_lado==13){
							$n2=espacios(150);
							$n3=espacios(30);
							$n4=espacios(7);
							$n5=espacios(35);
							$n6=espacios(52);
						
							$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f'";
						if($oConA->Query($sql)){
							if($oConA->NumFilas()>0){
								do{

									$pregunta=$oConA->f('pregunta');
									$respuesta=$oConA->f('respuesta');
									$tipof=$oConA->f('tipo');
									$id_input=$oConA->f('id_input');
									$codform=$oConA->f('id_tipo_formulario_datos');

									if($id_input=='r_86'){
										if($respuesta==1){
										$histopatologia='X';
										$citologia='';
										}
										elseif($respuesta==2){
											$histopatologia='';
											$citologia='X';
										}

									}

									
									if($id_input=='r_110'){
										if($respuesta==1){
										$urgente='X';
										$rutina='';
										$control='';
										}
										elseif($respuesta==2){
											$urgente='';
										$rutina='X';
										$control='';
										}
										elseif($respuesta==3){
										$urgente='';
										$rutina='';
										$control='X';
										}

									}
									
									
									if($id_input=='texto3'){
										$descripcion=$respuesta;
									}
									
									if($id_input=='texto1'){

										$sqlf="select label from clinico.tipos_formulario_datos_lado where id=$codform";
										$titest=consulta_string($sqlf, 'label', $oConB, '');
										$detestudio=$respuesta;
									}
									if($id_input=="medicotrad"){
										$codmedt=$oConA->f('respuesta');

										
										if(!empty($codmedt)){
											$sqlc="select clpv_nom_clpv, clpv_ruc_clpv from saeclpv where clpv_cod_clpv=$codmedt";
											$med_tratante=consulta_string($sqlc, 'clpv_nom_clpv',$oIfxA,'');
											$ruc_tratante=substr(consulta_string($sqlc, 'clpv_ruc_clpv',$oIfxA,''),0,10);
										  }
								
			
								    }
									if($id_input=="94_fecha"){
										$fechaf=$oConA->f('respuesta');
										$fecha13=date('d-m-Y',strtotime($fechaf));
										$hora13=date('H:i',strtotime($fechaf));
									}
									if($id_input=="111_fecha"){
										$fechaf=$oConA->f('respuesta');
										$fechatoma=date('d-m-Y',strtotime($fechaf));
										
									}

									if($id_input=='texto11'){
										$servicio=$respuesta;
									}
									if($id_input=='texto12'){
										$sala=$respuesta;
									}
									if($id_input=='texto13'){
										$cama=$respuesta;
									}
									 		 

									
								}while($oConA->SiguienteRegistro());
							}
						}

						$oConA->Free();

						$html.='<table border="1" cellpadding="2" cellspacing="0">
						<tr style="background-color:'.$empr_web_color.'">
						<td style="font-size:50%;"align="left" width="659.4">'.$n2.' SERVICIO'.$n3.'SALA '.$n4.' CAMA 
						'.$n5.' PRIORIDAD '.$n6.' FECHA DE TOMA</td>
						</tr>

						<tr>
						<td style="font-size:50%;"align="center" width="14%"></td>
						<td style="font-size:50%;"align="center" width="17%"></td>
						<td style="font-size:50%;"align="center" width="20%">'.$servicio.'</td>
						<td style="font-size:50%;"align="center" width="5%">'.$sala.'</td>
						<td style="font-size:50%;"align="center" width="5%">'.$cama.'</td>
						<td style="background-color:'.$empr_web_color.'; font-size:50%;"align="center" width="6%">URGENTE</td>
						<td style="font-size:50%;"align="center" width="3%">'.$urgente.'</td>
						<td style="background-color:'.$empr_web_color.'; font-size:50%;"align="center" width="6%">RUTINA</td>
						<td style="font-size:50%;"align="center" width="3%">'.$rutina.'</td>
						<td style="background-color:'.$empr_web_color.'; font-size:50%;"align="center" width="6%">CONTROL</td>
						<td style="font-size:50%;"align="center" width="3%">'.$control.'</td>
						<td style="font-size:50%;"align="center" width="11%">'.$fechatoma.'</td>
						

						</tr>
						
						</table>';

						$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' order by id_tipo_formulario_datos";
							$ban_t=false;
							$ban_t2=false;
							$cont=1;
							if($oConA->Query($sql)){
								if($oConA->NumFilas()>0){
									do{
										$pregunta=$oConA->f('pregunta');
										$respuesta=$oConA->f('respuesta');
										$codform=$oConA->f('id_tipo_formulario_datos');
										$id_input=$oConA->f('id_input');
										$tipo=$oConA->f('tipo');
										$sqlf="select label from clinico.tipos_formulario_datos_lado where id=$codform";
										$titulo=consulta_string($sqlf, 'label', $oConB, '');
										if($id_input=='r_86'){
											$html.='<br><br><table   border="1" cellpadding="2" cellspacing="0">
														<tr bgcolor="'.$empr_web_color.'">
															<td colspan="6"  align="left" ><h5>'.$titest.'</h5></td>
														</tr>
														<tr>
															<td style="font-size:60%;" width="12%" align="center">HISTOPATOLOGIA</td>
															<td style="font-size:60%;" width="5%" align="center">'.$histopatologia.'</td>
															<td style="font-size:60%;" width="12%" align="center">CITOLOGIA</td>
															<td style="font-size:60%;" width="5%" align="center">'.$citologia.'</td>
															<td style="font-size:60%;" width="12%" align="center">DESCRIPCION</td>
															<td style="font-size:60%;" width="54%" align="left"> '.$descripcion.'</td>

														</tr>
														<tr>
														<td colspan="6"  style="font-size:60%;" align="left" >'.$detestudio.'</td>
														</tr>
													</table>';
										}
										
										if($tipo=='TEXT'||$tipo=='TEXTAREA'){

											if($id_input!='texto1' && $id_input!='texto3' && $id_input!='texto11'&& $id_input!='texto12'&& $id_input!='texto13'){
												$html.='<br><br><table   border="1" cellpadding="2" cellspacing="0">
												<tr>
													<td  bgcolor="'.$empr_web_color.'" style= width:"659.4" align="left" ><h5>'.$pregunta.'</h5></td>
												</tr>
												<tr>
													<td height="50"  style="font-size:60%;" width:"659.4" align="left">'.$respuesta.'</td>
												</tr>
											</table>';
											}
											
													$cont++;
													
										}
										if($tipo=='TABLE'&& $ban_t==false){
											$n1=espacios(54);
											$n2=espacios(3);
											$n3=espacios(88);
											$html.='
											<br><br><table border="1" cellpadding="2" cellspacing="0">
														<tr>
															<td  colspan="10" bgcolor="'.$empr_web_color.'" style="font-size:80%;"align="left" width="659.4"><b> '.$pregunta.' </b>'.$n1.'CIE'.$n2.'PRE'.$n2.'DEF'.$n3.'CIE'.$n2.'PRE'.$n2.'DEF</td>
															
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="30">1</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d1.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">4</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p4.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d4.'</td>
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="30">2</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7" >'.$cie_deta2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d2.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">5</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7" >'.$cie_deta5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p5.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d5.'</td>
														</tr>
														<tr>
															<td height="10"  style="font-size:50%;"align="center" width="30">3</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d3.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">6</td>
															<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p6.'</td>
															<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d6.'</td>
														</tr>
													</table>';
											$ban_t=true;
										}
									
									}while($oConA->SiguienteRegistro());
								}
							}
							$oConA->Free();
							$html.='<table   border="1" cellpadding="2" cellspacing="0">
										<tr>
											<td style="font-size:60%;"align="center" width="5%">FECHA</td>
											<td style="font-size:60%;"align="center" width="10%">'.$fecha13.'</td>
											<td style="font-size:60%;"align="center" width="5%">HORA</td>
											<td style="font-size:60%;"align="center" width="10%">'.$hora13.'</td>
											<td style="font-size:60%;"align="center" width="10%">PROFESIONAL</td>
											<td style="font-size:60%;"align="center" width="24%">'.$med_tratante.'</td>
											<td style="font-size:60%;"align="center" width="10%">'.$ruc_tratante.'</td>
											<td style="font-size:60%;"align="center" width="10%">FIRMA</td>
											<td style="font-size:60%;"align="center" width="16%"></td>

										</tr>
									</table>
									<table   align="center" style="width:99%;  margin-top:20px;" border=0 cellpadding="0" cellspacing="0" >
										<tr>
											<td align="left" style=" font-size:80%; width:50%;">
												<strong>SNS-MSP / HCU-form.013A / 2008</strong>
											</td>
											<td align="right" style="font-size:80%;  width:50%;">
												<strong>'.$arreglo_formularios_lados[$id_lado][0].'</strong>
											</td>
										</tr>
									</table>';
						
						}
						if($id_lado==14){
                            $n1=espacios(5);
							$n2=espacios(10);
							$n3=espacios(40);
							$n4=espacios(30);
							$n5=espacios(7);
							$n6=espacios(35);
							$n7=espacios(50);

							$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f'";
							if($oConA->Query($sql)){
								if($oConA->NumFilas()>0){
									do{
	
										$pregunta=$oConA->f('pregunta');
										$respuesta=$oConA->f('respuesta');
										$tipof=$oConA->f('tipo');
										$id_input=$oConA->f('id_input');
										$codform=$oConA->f('id_tipo_formulario_datos');
	
										if($id_input=='r_97'){
											if($respuesta==1){
											$histopatologia='X';
											$citologia='';
											}
											elseif($respuesta==2){
												$histopatologia='';
												$citologia='X';
											}
	
										}
										if($id_input=='r_117'){
											if($respuesta==1){
											$urgente='X';
											$rutina='';
											$control='';
											}
											elseif($respuesta==2){
												$urgente='';
											$rutina='X';
											$control='';
											}
											elseif($respuesta==3){
											$urgente='';
											$rutina='';
											$control='X';
											}
	
										}
									
										if($id_input=='texto1'){
											$detallemacro=$respuesta;
										}
										if($id_input=='texto2'){
											$numpieza=$respuesta;
										}
										if($id_input=='texto3'){
											$numinfo=$respuesta;
										}
	
										if($id_input=='texto4'){
											$descrimacro=$respuesta;
										}
										if($id_input=='texto5'){
											$sqlf="select label from clinico.tipos_formulario_datos_lado where id=$codform";
										$titmic=consulta_string($sqlf, 'label', $oConB, '');
											$detallemicro=$respuesta;
										}
	
										if($id_input=='texto7'){
											$descrimicro=$respuesta;
										}
										if($id_input=="medicotrad"){
											$codmedt=$oConA->f('respuesta');
	
											
											if(!empty($codmedt)){
												$sqlc="select clpv_nom_clpv, clpv_ruc_clpv from saeclpv where clpv_cod_clpv=$codmedt";
												$med_tratante=consulta_string($sqlc, 'clpv_nom_clpv',$oIfxA,'');
												$ruc_tratante=substr(consulta_string($sqlc, 'clpv_ruc_clpv',$oIfxA,''),0,10);
											  }
									
				
										}
										if($id_input=="102_fecha"){
											$fechaf=$oConA->f('respuesta');
											$fecha13=date('d-m-Y',strtotime($fechaf));
											$hora13=date('H:i',strtotime($fechaf));
										}
										if($id_input=="118_fecha"){
											$fechaf=$oConA->f('respuesta');
											$fechatoma=date('d-m-Y',strtotime($fechaf));
											
										}

										if($id_input=='texto12'){
											$recibe=$respuesta;
										}
										if($id_input=='texto13'){
											$profesional=$respuesta;
										}

										if($id_input=='texto14'){
											$servicio=$respuesta;
										}
										if($id_input=='texto15'){
											$sala=$respuesta;
										}
										if($id_input=='texto16'){
											$cama=$respuesta;
										}
										
												  
	
										
									}while($oConA->SiguienteRegistro());
								}
							}
	
							$oConA->Free();

							$html.='<table border="1" cellpadding="2" cellspacing="0">
						<tr style="background-color:'.$empr_web_color.'">
						<td style="font-size:50%;"align="left" width="659.4">'.$n1.'PERSONA QUE REFIERE'.$n2.'PROFESIONAL SOLICITANTE'.$n3.'SERVICIO'.$n4.'SALA '.$n5.' CAMA '.$n6.' PRIORIDAD '.$n7.' FECHA DE ENTREGA</td>
						</tr>

						<tr>
						<td style="font-size:50%;"align="center" width="14%">'.$recibe.'</td>
						<td style="font-size:50%;"align="center" width="17%">'.$profesional.'</td>
						<td style="font-size:50%;"align="center" width="20%">'.$servicio.'</td>
						<td style="font-size:50%;"align="center" width="5%">'.$sala.'</td>
						<td style="font-size:50%;"align="center" width="5%">'.$cama.'</td>
						<td style="background-color:'.$empr_web_color.'; font-size:50%;"align="center" width="6%">URGENTE</td>
						<td style="font-size:50%;"align="center" width="3%">'.$urgente.'</td>
						<td style="background-color:'.$empr_web_color.'; font-size:50%;"align="center" width="6%">RUTINA</td>
						<td style="font-size:50%;"align="center" width="3%">'.$rutina.'</td>
						<td style="background-color:'.$empr_web_color.'; font-size:50%;"align="center" width="6%">CONTROL</td>
						<td style="font-size:50%;"align="center" width="3%">'.$control.'</td>
						<td style="font-size:50%;"align="center" width="11%">'.$fechatoma.'</td>
						

						</tr>
						
						</table>';
	
							$sql="select * from clinico.formulario_iess_detalle where id_formulario_iess='$id_f' order by id_tipo_formulario_datos";
								$ban_t=false;
								$ban_t2=false;
								$cont=1;
								if($oConA->Query($sql)){
									if($oConA->NumFilas()>0){
										do{
											$pregunta=$oConA->f('pregunta');
											$respuesta=$oConA->f('respuesta');
											$codform=$oConA->f('id_tipo_formulario_datos');
											$id_input=$oConA->f('id_input');
											$tipo=$oConA->f('tipo');
											$sqlf="select label from clinico.tipos_formulario_datos_lado where id=$codform";
											$titulo=consulta_string($sqlf, 'label', $oConB, '');
											if($id_input=='texto1'){
												$html.='<br><br><table   border="1" cellpadding="2" cellspacing="0">
															<tr bgcolor="'.$empr_web_color.'">
																<td colspan="6"  align="left" ><h5>'.$pregunta.'</h5></td>
															</tr>
															<tr>
																<td style="font-size:60%;" width="12%" align="center">NUMERO DE LA PIEZA</td>
																<td style="font-size:60%;" width="5%" align="center">'.$numpieza.'</td>
																<td style="font-size:60%;" width="12%" align="center">NUMERO DEL INFORME</td>
																<td style="font-size:60%;" width="5%" align="center">'.$numinfo.'</td>
																<td style="font-size:60%;" width="12%" align="center">DESCRIPCION</td>
																<td style="font-size:60%;" width="54%" align="left"> '.$descrimacro.'</td>
	
															</tr>
															<tr>
															<td colspan="6" style="font-size:60%;" align="left">'.$detallemacro.'</td>
															</tr>
														</table>';
											}
											if($id_input=='r_97'){
												$html.='<br><br><table   border="1" cellpadding="2" cellspacing="0">
															<tr bgcolor="'.$empr_web_color.'">
																<td colspan="6"  align="left" ><h5>'.$titmic.'</h5></td>
															</tr>
															<tr>
																<td style="font-size:60%;" width="12%" align="center">HISTOPATOLOGIA</td>
																<td style="font-size:60%;" width="5%" align="center">'.$histopatologia.'</td>
																<td style="font-size:60%;" width="12%" align="center">CITOLOGIA</td>
																<td style="font-size:60%;" width="5%" align="center">'.$citotologia.'</td>
																<td style="font-size:60%;" width="12%" align="center">DESCRIPCION</td>
																<td style="font-size:60%;" width="54%" align="left"> '.$descrimicro.'</td>
	
															</tr>
															<tr>
															<td colspan="6" style="font-size:60%;" align="left">'.$detallemicro.'</td>
															</tr>
														</table>';
											}
											if($tipo=='TEXT'||$tipo=='TEXTAREA'){
	
												if($id_input!='texto1'&&$id_input!='texto2'&&$id_input!='texto3'&&$id_input!='texto4'&&$id_input!='texto5'&&$id_input!='texto7'&&$id_input!='texto12'&&$id_input!='texto13'&&$id_input!='texto14'&&$id_input!='texto15'&&$id_input!='texto16'){
													$html.='<br><br><table   border="1" cellpadding="2" cellspacing="0">
													<tr>
														<td  bgcolor="'.$empr_web_color.'" style= width:"659.4" align="left" ><h5>'.$pregunta.'</h5></td>
													</tr>
													<tr>
														<td height="50"  style="font-size:60%;" width:"659.4" align="left">'.$respuesta.'</td>
													</tr>
												</table>';
												}
												
														$cont++;
														
											}
											if($tipo=='TABLE'&& $ban_t==false){
												$n1=espacios(54);
												$n2=espacios(3);
												$n3=espacios(88);
												$html.='
												<br><br><table border="1" cellpadding="2" cellspacing="0">
															<tr>
																<td  colspan="10" bgcolor="'.$empr_web_color.'" style="font-size:80%;"align="left" width="659.4"><b> '.$pregunta.' </b>'.$n1.'CIE'.$n2.'PRE'.$n2.'DEF'.$n3.'CIE'.$n2.'PRE'.$n2.'DEF</td>
																
															</tr>
															<tr>
																<td height="10"  style="font-size:50%;"align="center" width="30">1</td>
																<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta1.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie1.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p1.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d1.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">4</td>
																<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta4.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie4.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p4.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d4.'</td>
															</tr>
															<tr>
																<td height="10"  style="font-size:50%;"align="center" width="30">2</td>
																<td height="10"  style="font-size:50%;"align="left" width="209.7" >'.$cie_deta2.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie2.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p2.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d2.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">5</td>
																<td height="10"  style="font-size:50%;"align="left" width="209.7" >'.$cie_deta5.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie5.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p5.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d5.'</td>
															</tr>
															<tr>
																<td height="10"  style="font-size:50%;"align="center" width="30">3</td>
																<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta3.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie3.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p3.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d3.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">6</td>
																<td height="10"  style="font-size:50%;"align="left" width="209.7">'.$cie_deta6.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie6.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_p6.'</td>
																<td height="10"  style="font-size:50%;"align="center" width="30">'.$cie_d6.'</td>
															</tr>
														</table>';
												$ban_t=true;
											}
										
										}while($oConA->SiguienteRegistro());
									}
								}
								$oConA->Free();
								$html.='<table   border="1" cellpadding="2" cellspacing="0">
											<tr>
												<td style="font-size:60%;"align="center" width="5%">FECHA</td>
												<td style="font-size:60%;"align="center" width="10%">'.$fecha13.'</td>
												<td style="font-size:60%;"align="center" width="5%">HORA</td>
												<td style="font-size:60%;"align="center" width="10%">'.$hora13.'</td>
												<td style="font-size:60%;"align="center" width="10%">PROFESIONAL</td>
												<td style="font-size:60%;"align="center" width="24%">'.$med_tratante.'</td>
												<td style="font-size:60%;"align="center" width="10%">'.$ruc_tratante.'</td>
												<td style="font-size:60%;"align="center" width="10%">FIRMA</td>
												<td style="font-size:60%;"align="center" width="16%"></td>
	
											</tr>
										</table>
										<table   align="center" style="width:99%;  margin-top:20px;" border=0 cellpadding="0" cellspacing="0" >
											<tr>
												<td align="left" style="font-size:80%; width:50%;">
													<strong>SNS-MSP / HCU-form.013B / 2008</strong>
												</td>
												<td align="right" style="font-size:80%;  width:50%;">
													<strong>'.$arreglo_formularios_lados[$id_lado][0].'</strong>
												</td>
											</tr>
										</table>';
						
						}

						$html.='||||||||||';
					break;
				}//CIERRE SWITCH

					}while($oConC->SiguienteRegistro());
				}
			}
			$oConC->Free();
			}while($oCon->SiguienteRegistro());
		}
	}
	$oCon->Free();

	
	return $html;
}

function adj_paciente($cod,$id){

    //Definiciones
  global $DSN_Ifx, $DSN;
  if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
  
  $oIfx = new Dbo;
  $oIfx->DSN = $DSN_Ifx;
  $oIfx->Conectar();  
  
  $oCon = new Dbo;
  $oCon->DSN = $DSN;
  $oCon->Conectar();
  
  
  
  
  $sHtml .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 90%; margin-bottom: 0px;" align="center">';
      $sHtml .= '<tr>
      <td class="info" align="center">No.</td>
      <td class="info" align="center">Titulo</td>
      <td class="info" align="center">Adjuntos</td></tr>';
  
  
      ////CARGA DE ADJUNTOS////
      $k=1;
      $sql="select ruta,titulo from clinico.adjuntos_paciente where id_clpv=$cod and id_dato=$id";
  
      if ($oCon->Query($sql)) {
          if ($oCon->NumFilas() > 0) {
              do {
  
                  $ruta=$oCon->f('ruta');
                  $titulo=$oCon->f('titulo');
  
                  $sHtml .='<tr>';
                  $sHtml .='<td align="center">'.$k.'</td>';
                  $sHtml .='<td align="center">'.$titulo.'</td>';
  
  
                 $arc_img="../../Include/Clases/Formulario/Plugins/reloj/$ruta";
   
                 $archivos="../../Include/Clases/Formulario/Plugins/reloj/$ruta";
                 
                  if(file_exists($arc_img)){
                      $imagen=$arc_img;
                  }else{
                      $imagen='';
                  }
                  $logo='';
                  $x='0px';
                  if(preg_match("/jpg|png|PNG|jpeg|gif/",$ruta)){
  
                      $logo='<div>
                              <img src="'. $imagen .'" style="
                              width:200px;
                              object-fit; contain;">
                              </div>';
                      $x='0px';
  
                      $sHtml .= '<td align="center"><a href="'. $archivos .'" target="_blank" >'.$logo.'</a></td>';
                  }
                  else{
  
                      $logo='<div>
                      <a href="'. $archivos .'" target="_blank" >'.$ruta.'</a>
                      </div>';
  
                      $sHtml .= '<td align="center"> '.$logo.'</td>';
                      
                  }
                  $sHtml .='</tr>';
                  $k++;
              } while ($oCon->SiguienteRegistro());
              $sHtml .='</table>';
          }
          else{
              $sHtml ='<div align="center"><span><font color="red"><stong>SIN ADJUNTOS</strong></font></span></div>';
          }
      }				
  
      $modal  ='<div id="mostrarmodal" class="modal fade" role="dialog">
                  <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                          <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                              <h4 class="modal-title">ADJUNTOS - PACIENTE</h4>
                          </div>
                          <div class="modal-body">
                          <div class="table-responsive">';                  
      $modal .= $sHtml;                
      $modal .='</div></div>
                          <div class="modal-footer">
                              <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                          </div>
                      </div>
                  </div>
               </div>';    
      
      
      return $modal;
  
  }
  function encabezado_reportes(){
        //Definiciones
        global $DSN_Ifx, $DSN;
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
        
        $oIfx = new Dbo;
        $oIfx->DSN = $DSN_Ifx;
        $oIfx->Conectar();
        $idempresa = $_SESSION['U_EMPRESA'];
        //DATOS DE LA EMPRESA
        $sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr,empr_num_dire,  
        empr_path_logo, empr_tel_resp,empr_fax_empr,empr_mai_empr,
        empr_ema_repr
        from saeempr where empr_cod_empr = $idempresa ";
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                        $razonSocial = trim($oIfx->f('empr_nom_empr'));
                        $ruc_empr = $oIfx->f('empr_ruc_empr');
                        $dirMatriz = trim($oIfx->f('empr_dir_empr'));
                        $calles=trim($oIfx->f('empr_num_dire'));
                        $empr_path_logo = $oIfx->f('empr_path_logo');
                        
                        $tel=$oIfx->f('empr_tel_resp');
                        $fax=$oIfx->f('empr_fax_empr');
                        $ema1=$oIfx->f('empr_mai_empr');
                        $ema2=$oIfx->f('empr_ema_repr');       
        }
        }
        $oIfx->Free();
        //ARRAY
        $dirMatriz=strtolower($dirMatriz);
        $dirMatriz=ucwords($dirMatriz);
        $ruta=basename($empr_path_logo);
        ///////LOGO DEL REPORTE ///////////////
        //$arc?img='../../../file/img/'.$imagen_i;
        $arc_img=DIR_FACTELEC."Include/Clases/Formulario/Plugins/reloj/$ruta";
        
        $logo='';
        
        if(file_exists($arc_img)){
            $imagen=$arc_img;
        }else{
            $imagen='';
        }
        
        $x='0px';
        if($imagen!=''){
        
            $logo='<div>
                    <img src="'. $imagen .'" style="
                    width:150px;
                    object-fit; contain;">
                    </div>';
            $x='0px';
        }
        else{
            $logo='';
        
        }
        
        
        
        $html=<<<EOD
        <table    cellpadding="1" border="0">
                <tr>
                <td rowspan="5" align="left"> $logo </td>
                <td></td>      
                </tr>
                <tr>
                <td style="font-size:80%;"align="rigth"  >$dirMatriz</td>
                </tr>
                <tr>
                    <td style="font-size:80%;"align="rigth">Tel&eacute;fonos: $tel </td>
                </tr>
                <tr>
                    <td style="font-size:80%;"align="rigth" >$ema1</td>
                </tr>
                <tr>
                    <td style="font-size:80%;"align="rigth" height=200>$ema2</td>
                </tr>
        </table>  
        EOD;
        
        
        return $html;
  
  }
  
  function emprcolor_logo  ($empr,$suc){
  
   //Definiciones
   global $DSN_Ifx, $DSN;
   if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
  
   $oIfx = new Dbo;
   $oIfx->DSN = $DSN_Ifx;
   $oIfx->Conectar();
  
   $sql = "select empr_web_color, empr_path_logo from saeempr where empr_cod_empr =  $empr";
  if ($oIfx->Query($sql)) {
      if ($oIfx->NumFilas() > 0) {
          $empr_path_logo = $oIfx->f('empr_path_logo');
          $empr_color = $oIfx->f('empr_web_color');
      }
  }
  $oIfx->Free();
  
   $array_empr [] = array($empr_path_logo, $empr_color);
  
   return $array_empr;
  
  
  }
  
  ///// resultados de laboratorio

function reporte_resultados_laboratorio_cli($cod_fact, $id_clpv, $id_l)
{
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();


    $idempresa = $_SESSION['U_EMPRESA'];

    $sql = "select empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_tel_resp
                                            from saeempr where empr_cod_empr = $idempresa ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {

            $empr_path_logo = $oIfx->f('empr_path_logo');

        }
    }
    $oIfx->Free();
    $sql = "SELECT
				lab_toma_detalle.id,
				lab_toma_detalle.cod_prod,
				lab_toma_detalle.nom_prod
			FROM
				clinico.lab_toma_muest
			INNER JOIN clinico.lab_toma_detalle ON lab_toma_muest.id = lab_toma_detalle.id_lab_toma_mues
			WHERE
				estado = 'RC' and cod_fact='$cod_fact' and id_clpv='$id_clpv' and lab_toma_muest.id='$id_l'";
    //	echo $sql;exit;
    if ($oCon->Query($sql)) {
        if ($oCon->NumFilas() > 0) {
            unset($array_detalle_id);
            do {
                $id = $oCon->f('id');
                $cod_prod = $oCon->f('cod_prod');
                $nom_prod = $oCon->f('nom_prod');
                $array_detalle_id[] = array($id, $cod_prod, $nom_prod);
            } while ($oCon->SiguienteRegistro());
        }
    }
    foreach ($array_detalle_id as $arreglo) {
        $id = $arreglo[0];
        $cod_prod = $arreglo[1];
        $nom_prod = $arreglo[2];
        $sql2 = "SELECT
							lab_toma_muest.nom_clpv,
							lab_toma_muest.ruc_clpv,
							lab_toma_detalle.nom_prod,
							lab_toma_detalle.cod_prod,
							DATE_FORMAT(lab_toma_detalle.fecha_registro, '%d-%m-%Y') as fecha_registro,
							lab_toma_muest.fecha_nacimineto,
							lab_toma_muest.sexo
							
						FROM
							clinico.lab_toma_muest
						INNER JOIN clinico.lab_toma_detalle ON lab_toma_muest.id = lab_toma_detalle.id_lab_toma_mues
						WHERE lab_toma_detalle.id='$id'";

        $paciente = consulta_string($sql2, 'nom_clpv', $oCon, '');
        $ruc_clpv = consulta_string($sql2, 'ruc_clpv', $oCon, '');
        $nom_prod = consulta_string($sql2, 'nom_prod', $oCon, '');
        $fecha_registro = consulta_string($sql2, 'fecha_registro', $oCon, '');
        $fecha_nacimineto = consulta_string($sql2, 'fecha_nacimineto', $oCon, '');

        $sexo = consulta_string($sql2, 'sexo', $oCon, '');
        $fecha = time() - strtotime($fecha_nacimineto);
        $edad = floor((($fecha / 3600) / 24) / 360);
        if ($sexo == 'M') {
            $sexo = 'MASCULINO';
        } else {
            $sexo = 'FEMENINO';
        }
        $sql = "SELECT
					lab_servicio_detalle.descripcion,
					lab_toma_reultado.resultado,
					lab_toma_reultado.referencia,
					lab_toma_reultado.fecha_registro
				FROM
					clinico.lab_servicios
				INNER JOIN clinico.lab_servicio_detalle ON lab_servicios.id = lab_servicio_detalle.id_lab_servicio
				INNER JOIN clinico.lab_toma_reultado ON lab_servicio_detalle.id = lab_toma_reultado.id_servicio_detalle
				WHERE
					lab_toma_reultado.id_lab_toma_detalle='$id'";
        //$oReturn->alert	($sql);
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $documento = '<page backtop="60mm" backbottom="30mm" backleft="10mm" backright="20mm" style="width:100%; ">
										<page_header style="width:100%; font-size:20px; ">
											<table style="width:100%; margin: 0px; " border=0>
												<tr>
													<td style="width:25%;"><img src="' . $empr_path_logo . '" width="300" /></td>
													
													<td style="width:25%;">Edificio - Hospital Lenin Mosquera<br>
														Flores 912 y Manabi (Plaza Teatro)<br>
														Telefonos: 2951225 / 2953572<br>
														Servivio al Cliente: 0988422423<br>
														administracion@hospitaleninmosquera.org<br>
														www.hospitaleninmosquera.org</td>
												</tr>
												<tr>
													<td colspan="2" style="width:100%;" align="center"><h2  style="color:#0c5e3a" >SERVICIO DE LABORATORIO CLINICO</h2></td>
												</tr>
											</table>
										</page_header>
										<page_footer>
											<table style="width: 100%; margin: 0px; border-top:3px;  font-size: 20px;" >
												
												<tr>
													<td style="width: 100%; ">&nbsp;&nbsp;&nbsp;&nbsp;Responsable:  </td>
												</tr>
												
											</table>
										</page_footer>';

                $html_pdf = '<table style="width: 96%;" border=0>
									<tr>
										<td colspan="2" style="width: 50%;"><strong>PACIENTE:</strong>' . $paciente . '</td>
										<td style="width: 25%;"><strong>IDENTIFICACION:</strong>' . $ruc_clpv . '</td>
										<td style="width: 25%;"  align="right"><strong>FECHA:</strong>' . $fecha_registro . '</td>
									</tr>
									<tr>
										<td style="width: 10%;"><strong>EDAD:</strong>' . $edad . '</td>
										<td style="width: 25%;"><strong>SEXO:</strong>' . $sexo . '</td>
										<td style="width: 25%;"></td>
										<td style="width: 25%;"></td>
									</tr>
									<tr>
										<td colspan="4"  align="center" style="width: 100%;"><h3><strong>' . $nom_prod . '</strong></h3></td>
										
									</tr>
								</table>
								<table style="width: 96%; border:2px solid black; border-radius: 5px 5px 5px 5px" cellspacing="0" cellpadding="0">
									<tr>
										<td style="width: 33%;border-bottom: 1px inset black;border-right:1px; text-align:center"><strong>DESCRIPCION</strong></td>
										<td style="width: 33%;border-bottom: 1px inset black;border-right:1px; text-align:center"><strong>RESULTADO</strong></td>
										<td style="width: 33%;border-bottom: 1px inset black;border-right:1px; text-align:center"><strong>REFERENCIA</strong></td>
									</tr>';

                do {
                    $decripcion = $oCon->f('descripcion');
                    $resultado = $oCon->f('resultado');
                    $referencia = $oCon->f('referencia');
                    $html_pdf .= '<tr>
									<td style="width: 33%;border-bottom: 1px inset black; border-right:1px">' . $decripcion . '</td>
									<td style="width: 33%;border-bottom: 1px inset black; border-right:1px"><pre>' . $resultado . '</pre></td>
									<td style="width: 33%;border-bottom: 1px inset black; border-right:1px"><pre>' . $referencia . '</pre></td>
								</tr>';
                } while ($oCon->SiguienteRegistro());
                $html_pdf .= '</table>';
                $pdf .= $documento;
                $pdf .= $html_pdf;
                $pdf .= '</page>';
            }
        }

    }
    $nombre_archivo = 'RESULTADO_LABORATORIO_' . $cod_fact . '_' . $id_clpv . '_' . $id_l;
    $html2pdf = new HTML2PDF('P', 'A3', 'fr');
    $html2pdf->WriteHTML($pdf);
    $ruta = DIR_FACTELEC . 'Include/archivos/' . $nombre_archivo . '.pdf';
    $html2pdf->Output($ruta, 'F');
    $rutaPdf = $ruta;


    return $pdf;
}

?>