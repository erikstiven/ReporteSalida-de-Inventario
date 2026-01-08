<?

function formatoCorteContrato($id_clpv = 0, $id_contrato = 0, $id = 0)
{
	global $DSN;

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$sql = "SELECT id_proceso from isp.instalacion_clpv where id_contrato = $id_contrato and id_clpv = $id_clpv and id = $id";
	$id_proceso = consulta_string_func($sql, 'id_proceso', $oCon, 0);

	$sql = "SELECT formato from isp.int_tipo_proceso where id = $id_proceso";
	$formato = consulta_string_func($sql, 'formato', $oCon, 0);

	$pdf = $formato($id_clpv, $id_contrato, $id);

	return $pdf;
}

function limitar_string($texto, $limite = 15) {
    // Si la longitud del texto es mayor que el límite
    if (strlen($texto) > $limite) {
        // Recorta el texto a la longitud deseada y añade "..."
        return substr($texto, 0, $limite) . '...';
    } else {
        // Si el texto es más corto que el límite, simplemente lo devuelve
        return $texto;
    }
}

function pdf_cortes($id_clpv = 0, $id_contrato = 0, $id = 0, &$rutaPdf = '')
{
	global $DSN_Ifx, $DSN, $DSN_API_ISP;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oIfxA = new Dbo;
	$oIfxA->DSN = $DSN_Ifx;
	$oIfxA->Conectar();

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oCon2 = new Dbo;
	$oCon2->DSN = $DSN;
	$oCon2->Conectar();

	if (class_exists('DboWs')) {
        $oConIsp = new DboWs; //CONEXION A BASE DEL WEBSERVICE
        $oConIsp->DSN = $DSN_API_ISP;
        $oConIsp->Conectar();
    }

	$idEmpresa = $_SESSION['U_EMPRESA'];
	$idSucursal = $_SESSION['U_SUCURSAL'];

	$id_api     = $_SESSION['id_api'];
    $estado_api = $_SESSION['estado_api'];


	$sql = "SELECT empr_nom_empr, empr_ruc_empr, empr_path_logo, empr_cod_pais, empr_mai_empr, empr_nomcome_empr
			from saeempr where empr_cod_empr = $idEmpresa ";
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			$razonSocial = trim($oIfx->f('empr_nom_empr'));
			$ruc_empr = $oIfx->f('empr_ruc_empr');
			$empr_path_logo = $oIfx->f('empr_path_logo');
			$empr_cod_pais = $oIfx->f('empr_cod_pais');
			$empr_nom_empr = $oIfx->f('empr_nom_empr');
			$empr_mai_empr = $oIfx->f('empr_mai_empr');
			$empr_nomcome_empr = $oIfx->f('empr_nomcome_empr');
		}
	}
	$oIfx->Free();

	if (!empty($empr_nomcome_empr)) {
		$razonSocial = $empr_nomcome_empr;
	}

	unset($arrayDetallaFormato);
	$sql = "SELECT id, material from isp.int_ftrn_material order by 2";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			do {
				$arrayDetallaFormato[] = $oCon->f('material');
			} while ($oCon->SiguienteRegistro());
		}
	}
	$oCon->Free();

	//datos del abono
	$sql = "SELECT id_sucursal, secuencial, fecha, observaciones, id_proceso,
			id_motivo, id_sucursal_ori, user_web, fecha_server as hora,
			fecha_print, direccion, vendedor_orden_servicio, estado
			from isp.instalacion_clpv
			where id_clpv = $id_clpv and
			id_contrato = $id_contrato and
			id = $id and
			id_empresa = $idEmpresa";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$id_sucursal = $oCon->f('id_sucursal');
			$secuencial = $oCon->f('secuencial');
			$fechaTran = $oCon->f('fecha');
			$observaciones = $oCon->f('observaciones');
			$id_proceso = $oCon->f('id_proceso');
			$id_motivo = $oCon->f('id_motivo');
			$id_sucursal_ori = $oCon->f('id_sucursal_ori');
			$user_web = $oCon->f('user_web');
			$hora = $oCon->f('hora');
			$fecha_print = $oCon->f('fecha_print');
			//$direccion_orden = $oCon->f('direccion');
			$vendedor_orden_servicio = $oCon->f('vendedor_orden_servicio');
			$estado_os = $oCon->f('estado'); 
		}
	}
	$oCon->Free();

	$sql = "SELECT material_os, mat_usado_sn
                from isp.int_parametros 
                where 
                id_sucursal = $id_sucursal";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$material_os = $oCon->f('material_os');
			$mat_usado_sn = $oCon->f('mat_usado_sn');
		}
	}
	$oCon->Free();

	if($estado_os != 'TE'){
		$mat_usado_sn = 'N';
	}

	$sql = "SELECT vineta
			from isp.int_contrato_caja
			where id_clpv = $id_clpv and
			id_contrato = $id_contrato AND vineta is not null AND vineta != ''";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$vineta = $oCon->f('vineta');
		}
	}
	$oCon->Free();

	$sql = "SELECT string_agg(ip, ' ') as ip, string_agg(mac, ' ') as mac, string_agg(id_tarjeta, ' ') as id_tarjeta, string_agg(vlan, ' ') as vlan, string_agg(puerto_pon, ' ') as puerto_pon, string_agg(hub, ' ')  as tarjeta_hd, string_agg(interface, ' ') as stb
			from isp.int_contrato_caja
			where id_clpv = $id_clpv and
			id_contrato = $id_contrato and estado not in ('E') and id_tipo_prod = 2 GROUP BY id_contrato";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$ip_c = $oCon->f('ip');
			$mac_c = $oCon->f('mac');
			$id_tarjeta_c = $oCon->f('id_tarjeta');
			$vlan_c = $oCon->f('vlan');
			$puerto_pon_c = $oCon->f('puerto_pon');
			$tarjeta_hd_c = $oCon->f('tarjeta_hd');
			$stb_c = $oCon->f('stb');
		}
	}
	$oCon->Free();

	if($id_api == 4 && $estado_api == 'A'){
		$sql = "SELECT id, nombre FROM olts WHERE habilitado = 't'";
		$array_olts = array_dato($oConIsp, $sql, 'id', 'nombre');
	}else{
		$sql = "SELECT id, serial from isp.int_dispositivos";
		$array_olts = array_dato($oCon, $sql, 'id', 'serial');
	}

	$array_routers = array();
	if($id_api == 4 && $estado_api == 'A'){
		$sql = "SELECT id, nombre FROM routers WHERE habilitado = 't'";
		$array_routers = array_dato($oConIsp, $sql, 'id', 'nombre');
	}

	$sql = "SELECT b.paquete
			FROM isp.int_contrato_caja_pack a INNER JOIN isp.int_paquetes b ON a.id_prod = b.id 
			where a.id_contrato = $id_contrato AND a.estado not in ('E') AND a.activo = 'S' AND b.id_tipo_prod = 2";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$paquete_int = $oCon->f('paquete');
		}
	}
	$oCon->Free();

	$sql = "SELECT fecha
			from isp.instalacion_ejecucion
			where id_clpv = $id_clpv and
			id_contrato = $id_contrato and
			id_instalacion = $id";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$fecha_ejec = $oCon->f('fecha');
		}
	}
	$oCon->Free();

	if (empty($fecha_print)) {

		$fecha_print = date("Y-m-d H:i:s");
		$fecha_print_ok = date("d-m-Y H:i:s");

		$sql = "UPDATE isp.instalacion_clpv SET fecha_print = '$fecha_print' WHERE id = $id";
		$oCon->QueryT($sql);
	} else {
		$fecha_print_f = substr($fecha_print, 0, 10);
		$fecha_print_h = substr($fecha_print, 10, 9);

		$fecha_print_ok = fecha_mysql_dmy($fecha_print_f) . ' ' . $fecha_print_h;
	}

	//datos de la sucursal
	$sql_sucu = "SELECT sucu_nom_sucu, sucu_dir_sucu 
				from saesucu 
				where sucu_cod_empr = $idEmpresa and 
				sucu_cod_sucu = $id_sucursal ";
	if ($oIfx->Query($sql_sucu)) {
		if ($oIfx->NumFilas() > 0) {
			$sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
			$sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');
		}
	}
	$oIfx->Free();

	$sucu_ori = "";
	if ($id_sucursal <> $id_sucursal_ori) {
		//datos de la sucursal
		$sql = "SELECT sucu_nom_sucu from saesucu where sucu_cod_empr = $idEmpresa and sucu_cod_sucu = $id_sucursal_ori ";
		$sucu_ori = ' <span style="color: blue">( ' . consulta_string_func($sql, 'sucu_nom_sucu', $oIfx, '') . ' )</span>';
	}

	//query usuario
	$sql = "SELECT concat(usuario_nombre, ' ', usuario_apellido) as user FROM comercial.usuario WHERE usuario_id = $user_web";
	$user = consulta_string_func($sql, 'user', $oCon, '');

	//nombre proceso
	$sql = "SELECT descripcion from isp.int_tipo_proceso where id = $id_proceso";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$descripcion = $oCon->f('descripcion');
		}
	}
	$oCon->Free();

	//franja
	if (!empty($id_franja)) {
		$sql = "SELECT franja from isp.instalacion_franja where id = $id_franja";
		$franja = consulta_string_func($sql, 'franja', $oCon, '');
	}

	//motivo
	if (!empty($id_motivo)) {
		$sql = "SELECT motivo from isp.int_motivos_canc where id = $id_motivo";
		$motivo = consulta_string_func($sql, 'motivo', $oCon, '');
	}

	//datos del contrato
	$sql = "SELECT poste, sobrenombre,codigo, tarifa, direccion, referencia, id_sector, 
					id_barrio, nombre, apellido, precinto, caja, nom_clpv, 
					telefono, celular, ruc_clpv, longitud, latitud, observaciones, vendedor
			from isp.contrato_clpv
			where id_clpv = $id_clpv and
			id= $id_contrato and
			id_empresa = $idEmpresa";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$codigo = $oCon->f('codigo');
			$tarifa = $oCon->f('tarifa');
			$direccion = $oCon->f('direccion');
			$referencia = $oCon->f('referencia');
			$id_sector = $oCon->f('id_sector');
			$id_barrio = $oCon->f('id_barrio');
			$nombre = $oCon->f('nombre');
			$apellido = $oCon->f('apellido');
			$precinto = $oCon->f('precinto');
			$caja = $oCon->f('caja');
			$nom_clpv = $oCon->f('nom_clpv');
			$telefono = $oCon->f('telefono');
			$celular = $oCon->f('celular');
			$ruc_clpv = $oCon->f('ruc_clpv');
			$longitud = $oCon->f('longitud');
			$latitud = $oCon->f('latitud');
			$observaciones_clpv = $oCon->f('observaciones');
			$cod_vend = $oCon->f('vendedor');
			$poste = $oCon->f('poste');
			$sobrenombre = $oCon->f('sobrenombre');
		}
	}
	$oCon->Free();

	if (!empty($vendedor_orden_servicio)) {
		$cod_vend = $vendedor_orden_servicio;
	}

	$sql="SELECT CONCAT(direccion, ' ', referencia) as direccion from isp.direccion_traslado where id_tarea = $id";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$direccion_new = $oCon->f('direccion');
		}
	}
	$oCon->Free();

	$direccion_actual = $direccion;

	if (!empty($direccion_orden)) {
		$direccion = $direccion_orden;
	}

	$sql = "SELECT vend_nom_vend
		from saevend
		where vend_cod_vend = '$cod_vend'";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$vend_nom_vend = $oCon->f('vend_nom_vend');
		}
	}
	$oCon->Free();

	if ($id_proceso == 1 && strlen($observaciones_clpv) > 0) {
		$observaciones = $observaciones_clpv;
	}

	if (strlen($nom_clpv) == 0) {
		$nom_clpv = $nombre . " " . $apellido;
	}

	if (strlen($telefono) == 0) {
		$telefono = $celular;
	}
	//sector - barrio
	$sector = '&nbsp;';
	if (!empty($id_sector)) {
		$sql = "SELECT sector from comercial.sector_direccion where id = $id_sector";
		$sector = consulta_string_func($sql, 'sector', $oCon, '');
	}

	$barrio = '&nbsp;';
	if (!empty($id_barrio)) {
		$sql = "SELECT barrio from isp.int_barrio where id = $id_barrio";
		$barrio = consulta_string_func($sql, 'barrio', $oCon, '');
	}

	//datos del cliente
	$sql = "SELECT clpv_nom_clpv, clpv_ruc_clpv 
			from saeclpv
			where clpv_cod_clpv = $id_clpv and
			clpv_cod_empr = $idEmpresa";
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			$clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
			$clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
		}
	}
	$oIfx->Free();

	//telefonos
	$telefonos = '';
	$sql = "SELECT tlcp_tlf_tlcp 
			from saetlcp
			where tlcp_cod_empr = $idEmpresa and
			tlcp_cod_clpv = $id_clpv and
			tlcp_cod_contr = $id_contrato";
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			do {
				$telefonos .= $oIfx->f('tlcp_tlf_tlcp') . ' ';
			} while ($oIfx->SiguienteRegistro());
		}
	}
	$oIfx->Free();

	//TIPO IDENTIFICACION EMPRESA
	$tipo_iden = '';
	$sql = "SELECT identificacion 
			from comercial.tipo_iden_clpv_pais
			where pais_cod_pais = $empr_cod_pais and
			id_iden_clpv = 1";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			do {
				$tipo_iden = $oCon->f('identificacion');
			} while ($oCon->SiguienteRegistro());
		}
	}
	$oCon->Free();

	$Contratos = new Contratos($oCon, $oIfx, $idEmpresa, $idSucursal, $id_clpv, $id_contrato);
	$balance = $Contratos->consultaMontoMesAdeuda();

	if ($empr_cod_pais == 23 && $empr_nom_empr == "CABLE NET BROADBAND S DE RL DE CV") {

		$_SESSION['U_NOM_EMPR'] = $empr_nom_empr;

		$generado = $user . ' ' . $sucu_ori;

		$sql = "SELECT frto_txt_frto FROM saefrto WHERE frto_cod_empr = $idEmpresa AND frto_cod_modu = 98 AND frto_des_empr = '$empr_nom_empr' and frto_cod_form = 1";
		if ($oIfx->Query($sql)) {
			if ($oIfx->NumFilas() > 0) {
				$documento = $oIfx->f('frto_txt_frto', false);
			}
		}
		$oIfx->Free();

		$sql = "SELECT frto_txt_frto FROM saefrto WHERE frto_cod_empr = $idEmpresa AND frto_cod_modu = 98 AND frto_des_empr = '$empr_nom_empr' and frto_cod_form = 2";
		if ($oIfx->Query($sql)) {
			if ($oIfx->NumFilas() > 0) {
				$documento_anexo = $oIfx->f('frto_txt_frto', false);
			}
		}
		$oIfx->Free();

		$sql = "SELECT id, CONCAT(siglas, ' - ', nombre) as nombre from isp.int_nap WHERE estado = 'A'";
        unset($array_naps);
        $array_naps = array_dato($oCon, $sql, 'id', 'nombre');

		$path_img = explode("/", $empr_path_logo);
		$count = count($path_img) - 1;

		$path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

		//tecnico asignado
		$sql = "SELECT id_tecnico, nombres from isp.instalacion_tecnico WHERE id_instalacion = $id order by id DESC LIMIT 1";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				do {
					$id_tenico = $oCon->f('id_tecnico');
					$nombres = $oCon->f('nombres');

					$tecnico = $nombres;
				} while ($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();

		if ($longitud == 0) {
			$longitud = "";
		}

		if ($latitud == 0) {
			$latitud = "";
		}

		$id_nap = 0;
		$puerto_nap = 0;

		$sql = "SELECT id_nap, puerto_nap
				from isp.int_contrato_caja
				where id_clpv = $id_clpv and
				id_contrato = $id_contrato AND id_tipo_prod = 2";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				$id_nap = $oCon->f('id_nap');
				$puerto_nap = $oCon->f('puerto_nap');
			}
		}
		$oCon->Free();

		$nombre_nap = '';
        if(isset($array_naps[$id_nap])){
            $nombre_nap         = $array_naps[$id_nap] . ' / Puerto: '.$puerto_nap;
        }

		$array_cajas = array();
		$sql = "SELECT mac, hub as stb, interface as tarjeta_hd, usuario_sub_iptv as cuenta_hd, fecha_vence
				from isp.int_contrato_caja 
				where id_contrato = $id_contrato and id_tipo_prod = 8 and estado not in ('E') ORDER BY id ";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				do {
					$mac 			= $oCon->f('mac');
					$stb 			= $oCon->f('stb');
					$tarjeta_hd 	= $oCon->f('tarjeta_hd');
					$cuenta_hd 		= $oCon->f('cuenta_hd');
					$fecha_vence 	= $oCon->f('fecha_vence');
					$id_nap 		= $oCon->f('id_nap');
					$puerto_nap 	= $oCon->f('puerto_nap');
					
					$datos_indi = array(
						"mac" => $mac,
						"stb" => $stb,
						"tarjeta_hd" => $tarjeta_hd,
						"cuenta_hd" => $cuenta_hd,
						"fecha_vence" => $fecha_vence
					);

					array_push($array_cajas, $datos_indi);
				} while ($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();

		$sHtml_cajas = '';
		if(count($array_cajas) > 0){
			$sHtml_cajas = '<tr>';

			for ($i = 0; $i < count($array_cajas); $i++) {
				
			
				if ($i % 2 == 0 && $i != 0) {
					$sHtml_cajas .= '</tr><tr>'; 
				}

				$num = $i + 1;
				$sHtml_cajas .= '<td style="width: 50%; text-align: left;">
									<table border="1" height="50" style="width: 100%; vertical-align: middle; border-collapse: collapse;">
										<tr>
											<td colspan="2" align="center"><b>Datos caja HD #' . $num . '</b></td>
										</tr>
										<tr>
											<td style="width: 50%;"><b>Mac HD:</b></td>
											<td style="width: 50%;">' . $array_cajas[$i]["mac"] . '</td>
										</tr>
										<tr>
											<td style="width: 50%;"><b>Stb numero real:</b></td>
											<td style="width: 50%;">' . $array_cajas[$i]["stb"] . '</td>
										</tr>
										<tr>
											<td style="width: 50%;"><b>Tarjeta HD:</b></td>
											<td style="width: 50%;">' . $array_cajas[$i]["tarjeta_hd"] . '</td>
										</tr>
										<tr>
											<td style="width: 50%;"><b>Cuenta Sistema:</b></td>
											<td style="width: 50%;">' . $array_cajas[$i]["cuenta_hd"] . '</td>
										</tr>
										<tr>
											<td style="width: 50%;"><b>Vencimiento:</b></td>
											<td style="width: 50%;">' . $array_cajas[$i]["fecha_vence"] . '</td>
										</tr>
										<tr>
											<td style="width: 50%;"><b>Paquete HD:</b></td>
											<td style="width: 50%;"></td>
										</tr>
									</table>
								</td>';
			}

			$sHtml_cajas .= '</tr>';
		}

		$fech_impresion = date("d-m-Y h:m:s");
		$txt_potencia = "";
		$documento = preg_replace("/%nombre_empresa%/", $empr_nom_empr, $documento);
		$documento = preg_replace("/%ruta_logo%/", $path_logo_img, $documento);
		$documento = preg_replace("/%ruc_empresa%/", $ruc_empr, $documento);
		$documento = preg_replace("/%tipo_ruc%/", $tipo_iden, $documento);
		$documento = preg_replace("/%correo_empresa%/", $empr_mai_empr, $documento);
		$documento = preg_replace("/%num_orden%/", $secuencial, $documento);
		$documento = preg_replace("/%nom_cliente%/", $nom_clpv, $documento);
		$documento = preg_replace("/%codigo_contrato%/", $codigo, $documento);
		$documento = preg_replace("/%telefono%/", $telefono, $documento);
		$documento = preg_replace("/%balance%/", $balance, $documento);
		$documento = preg_replace("/%identificacion%/", $ruc_clpv, $documento);
		$documento = preg_replace("/%tarifa%/", $tarifa, $documento);
		$documento = preg_replace("/%direccion%/", $direccion, $documento);
		$documento = preg_replace("/%caja%/", $caja, $documento);
		$documento = preg_replace("/%motivo%/", $motivo, $documento);
		$documento = preg_replace("/%generado%/", $generado, $documento);
		$documento = preg_replace("/%tecnico%/", $tecnico, $documento);
		$documento = preg_replace("/%observaciones%/", $observaciones, $documento);
		$documento = preg_replace("/%iden_tecnico%/", $id_tenico, $documento);
		$documento = preg_replace("/%fecha_impresion%/", $fech_impresion, $documento);
		$documento = preg_replace("/%iden_cliente%/", $ruc_clpv, $documento);
		$documento = preg_replace("/%fecha_orden%/", $fechaTran, $documento);
		$documento = preg_replace("/%fecha_ejecucion%/", $fecha_ejec, $documento);
		$documento = preg_replace("/%tip_orden%/", $descripcion, $documento);
		$documento = preg_replace("/%longitud%/", $longitud, $documento);
		$documento = preg_replace("/%latitud%/", $latitud, $documento);
		$documento = preg_replace("/%nom_vendedor%/", $vend_nom_vend, $documento);
		$documento = preg_replace("/%vineta%/", $vineta, $documento);
		$documento = preg_replace("/%ip_cliente%/", $ip_c, $documento);
		$documento = preg_replace("/%mac_cliente%/", $mac_c, $documento);
		$documento = preg_replace("/%serie_cliente%/", $id_tarjeta_c, $documento);
		$documento = preg_replace("/%vlan_cliente%/", $vlan_c, $documento);
		$documento = preg_replace("/%puerto_pon_cliente%/", $puerto_pon_c, $documento);
		$documento = preg_replace("/%tarjeta_hd_cliente%/", $tarjeta_hd_c, $documento);
		$documento = preg_replace("/%stb_cliente%/", $stb_c, $documento);
		$documento = preg_replace("/%plan_inter_cliente%/", $paquete_int, $documento);
		$documento = preg_replace("/%direccion_nueva%/", $direccion_new, $documento);
		$documento = preg_replace("/%html_cajas%/", $sHtml_cajas, $documento);
		$documento = preg_replace("/%info_nap%/", $nombre_nap, $documento);

		$documento1 = '<page backimgw="70%" backtop="5mm" backbottom="5mm" backleft="5mm" backright="5mm" footer="www.sisconti.com">';
		$documento1 .= $documento;
		$documento1 .= '</page>';

		$documento2 = '<page backimgw="70%" backtop="5mm" backbottom="5mm" backleft="5mm" backright="5mm" footer="www.sisconti.com">';
		$documento2 .= $documento_anexo;
		$documento2 .= '</page>';

		$documentos = $documento1 . $documento2;

		return $documentos;
	} else {
		$sHtmlDire = '';
		//traslado
		if ($id_proceso == 6) {
			//query direcciones antiguas

			$sql = "SELECT dire_dir_dire, dire_refe_dire FROM saedire WHERE dire_cod_contr = $id_contrato AND dire_est_dire = 'I'";
			if ($oIfx->Query($sql)) {
				if ($oIfx->NumFilas() > 0) {
					$dire_dir_dire = $oIfx->f('dire_dir_dire');
					$dire_refe_dire = $oIfx->f('dire_refe_dire');

					$sHtmlDire .= '<tr>';
					$sHtmlDire .= '<td style="font-size: 18px; width: 15%; color: red;"><b>Direccion Antigua:</b></td>';
					$sHtmlDire .= '<td style="font-size: 18px; width: 85%;">' . $dire_dir_dire . ', ' . $dire_refe_dire . '</td>';
					$sHtmlDire .= '</tr>';
				}
			}
			$oIfx->Free();
		}

		$path_img = explode("/", $empr_path_logo);
		$count = count($path_img) - 1;

		$path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

		$logo = '<table align="center" style="width: 100%; margin: 0px;">';
		$logo .= '<tr>';
		$logo .= '<td align="left" style="width: 10%;" rowspan="3"><img width="250px;" height="100px;" src="' . $path_logo_img . '"></td>';
		$logo .= '<td align="center" style="width: 80%; font-size: 20px;">' . $razonSocial . '</td>';
		$logo .= '</tr>';
		$logo .= '<tr><td align="center" style="font-size: 16px;">RUC : ' . $ruc_empr . '</td></tr>';
		$logo .= '<tr><td align="center" style="font-size: 16px;">' . $sucu_nom_sucu . ': ' . htmlentities($sucu_dir_sucu) . '</td></tr>';
		$logo .= '</table>';

		$cabecera = '<table align="left" style="width: 100%; margin-top: 5px;">';
		$cabecera .= '<tr>';
		$cabecera .= '<td colspan="4" style="font-size: 20px;" align="center"><b>' . $descripcion . ': ' . $secuencial . '</b></td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 15%;"><b>Fecha:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 40%;">' . fecha_mysql_dmy($fechaTran) . ' ' . $hora . '</td>';
		$cabecera .= '<td style="font-size: 18px; width: 13%; color: red;"><b>Balance:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 32%; color: red;">' . number_format($balance, 2, '.', ',') . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 15%;"><b>Cliente:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 40%;">' . $nom_clpv . '</td>';
		$cabecera .= '<td style="font-size: 18px; width: 13%;"><b>Identificacion:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 32%;">' . $clpv_ruc_clpv . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 15%;"><b>Contrato:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 40%;">' . $codigo . '</td>';
		$cabecera .= '<td style="font-size: 18px; width: 13%;"><b>Tarifa:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 32%;">' . number_format($tarifa, 2, '.', ',') . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 15%;"><b>Sector:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 40%;">' . $sector . '</td>';
		$cabecera .= '<td style="font-size: 18px; width: 13%;"><b>Barrio:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 32%;">' . $barrio . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 15%;"><b>Telefono:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 40%;">' . $telefono . '</td>';
		$cabecera .= '<td style="font-size: 18px; width: 13%;"><b>Precinto:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 32%;">' . $precinto . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 15%;"><b>Telefonos Adicionales:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 40%;">' . $telefonos . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '</table>';
		$cabecera .= '<table align="left" style="width: 100%; margin-top: 0px;">';
		$cabecera .= $sHtmlDire;
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 15%;"><b>Direccion Actual:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 40%;">' . $direccion . ' ' . $referencia . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 15%;"><b>Direccion Nueva:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 40%;">' . $direccion_new . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 13%;"><b>Poste:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 32%;">' . $poste . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 13%;"><b>Caja:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 32%;">' . $caja . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 15%;"><b>Referencia:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 85%;">' . $referencia . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 15%;"><b>Sobrenombre:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 85%;">' . $sobrenombre . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 15%;"><b>Motivo:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 85%;">' . $motivo . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 15%;"><b>Observaciones:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 85%;">' . $observaciones . '</td>';
		$cabecera .= '</tr>';
		$cabecera .= '<tr>';
		$cabecera .= '<td style="font-size: 18px; width: 15%;"><b>Generado:</b></td>';
		$cabecera .= '<td style="font-size: 18px; width: 85%;">' . $user . ' ' . $sucu_ori . '</td>';
		$cabecera .= '</tr>';

		//tecnico asignado
		$sql = "SELECT id_tecnico, nombres from isp.instalacion_tecnico WHERE id_instalacion = $id";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				do {
					$id_tenico = $oCon->f('id_tecnico');
					$nombres = $oCon->f('nombres');

					$cabecera .= '<tr>
								<td style="font-size: 18px; width: 15%;"><b>Tecnico:</b></td>
								<td style="font-size: 18px; width: 85%;">' . $nombres . '</td>
							</tr>';
				} while ($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();

		// --------------------------------------------------------------------------
		// Adrian47 Metraje asignado a esa orden
		// --------------------------------------------------------------------------

		$sql = "SELECT metraje_metros, metraje_kilometros, nombre_nap, puerto from isp.int_metraje WHERE id_contrato = $id_contrato";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				do {
					$metraje_metros = $oCon->f('metraje_metros');
					$metraje_kilometros = $oCon->f('metraje_kilometros');
					$nombre_nap = $oCon->f('nombre_nap');
					$puerto = $oCon->f('puerto');
					
					$cabecera .= '<tr>
									<td style="font-size: 18px; width: 15%;"><b>Metraje:</b></td>
									<td style="font-size: 18px; width: 85%;">' . $metraje_metros . ' metros; ' . $metraje_kilometros . ' kilometros</td>
								</tr>';

					$cabecera .= '<tr>
									<td style="font-size: 18px; width: 15%;"><b>NAP:</b></td>
									<td style="font-size: 18px; width: 85%;">' . $nombre_nap . '</td>
								</tr>';

					$cabecera .= '<tr>
								<td style="font-size: 18px; width: 15%;"><b>Puerto:</b></td>
								<td style="font-size: 18px; width: 85%;">' . $puerto . '</td>
							</tr>';
				} while ($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();

		// --------------------------------------------------------------------------
		// FIN Adrian47 Metraje asignado a esa orden
		// --------------------------------------------------------------------------



		$cabecera .= '</table>';

		//caja instalacion
		$array_i_c = array();
		$sql = "SELECT id_caja from isp.instalacion_prod WHERE id_instalacion = $id";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				do {
					$array_i_c[$oCon->f('id_caja')] = 'S';
				} while ($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();

		//paquetes caja
		$array_p = array();
		$sql = "SELECT c.id_caja, c.id_prod, p.paquete
				from isp.int_contrato_caja_pack c, isp.int_paquetes p
				WHERE c.id_prod = p.id AND
				c.id_clpv = $id_clpv AND
				c.id_contrato = $id_contrato AND
				c.estado in ('P', 'A', 'C')";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				do {
					$array_p[$oCon->f('id_caja')] .= $oCon->f('paquete');
				} while ($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();

		//estado equipo
		$sql = "SELECT id, estado, color from isp.int_estados_equipo";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				unset($arrayEstado);
				unset($arrayEstadoColor);
				unset($arrayEstadoCallCenter);
				do {
					$arrayEstado[$oCon->f('id')] = $oCon->f('estado');
					$arrayEstadoColor[$oCon->f('id')] = $oCon->f('color');
				} while ($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();

		//equipos // OLD
		/* $sql = "SELECT c.id, c.id_tarjeta, c.id_caja, c.estado, 
				c.ubicacion, c.id_tipo_prod, c.latitud, c.longitud, c.ip
				from isp.int_contrato_caja c
				where 
				c.id_empresa = $idEmpresa and
				c.id_clpv = $id_clpv and
				c.id_contrato = $id_contrato and
				c.estado in ('P', 'A', 'C')
				order by c.id_tipo_prod";
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				$cabecera .= '<table>';
				$cabecera .= '<tr>
								<td style="font-size: 18px;"><b>Equipos</b></td>
							</tr>';
				do {
					$id_c = $oCon->f('id');
					$id_tarjeta = $oCon->f('id_tarjeta');
					$id_caja = $oCon->f('id_caja');
					$estado = $oCon->f('estado');
					$ubicacion = $oCon->f('ubicacion');
					$id_tipo_prod = $oCon->f('id_tipo_prod');
					$latitud = $oCon->f('latitud');
					$longitud = $oCon->f('longitud');
					$ip = $oCon->f('ip');

					$paquetes = htmlentities($array_p[$id_c]);

					$html_e = '<span style="color: ' . $arrayEstadoColor[$estado] . '">[' . $arrayEstado[$estado] . ']</span>';

					$tipo_servicio = 'TV';
					if ($id_tipo_prod == 2) {
						$tipo_servicio = 'INTERNET';
					}
					$cabecera .= '<tr>';
					if (!empty($array_i_c[$id_c])) {
						$cabecera .= '<td style="font-size: 15px; color: blue;"><b>' . $tipo_servicio . ' - ' . $id_tarjeta . ', ' . $id_caja . ', ' . $ubicacion . ', ' . $html_e . ', ' . $paquetes . '</b></td>';
					} else {
						$cabecera .= '<td style="font-size: 15px;">' . $tipo_servicio . ' - ' . $id_tarjeta . ', ' . $ip . ', ' . $id_caja . ', ' . $ubicacion . ', ' . $html_e . ', ' . $paquetes . '</td>';
					}
					$cabecera .= '</tr>';
				} while ($oCon->SiguienteRegistro());
				$cabecera .= '</table>';
			}
		}
		$oCon->Free(); */

		//EQUIPOS TABLA ACTUALIZADA 2024-09-12
		$cabecera .= '<table align="left" border="1" style="width: 98%; margin-top: 10px; border-collapse: collapse;">
							<tr>
								<td colspan="8" align="center" style="font-size: 20px;" style="width: 15%; height: 18px;"><b>REPORTE EQUIPOS</b></td>
							</tr>
							<tr>
								<td style="font-size: 18px;" style="width: 15%; height: 18px;"><b>Tipo</b></td>
								<td style="font-size: 18px;" style="width: 10%; height: 18px;"><b>Estado</b></td>
								<td style="font-size: 18px;" style="width: 15%; height: 18px;"><b>Olt</b></td>
								<td style="font-size: 18px;" style="width: 15%; height: 18px;"><b>Router</b></td>
								<td style="font-size: 18px;" style="width: 15%; height: 18px;"><b>Serial</b></td>
								<td style="font-size: 18px;" style="width: 10%; height: 18px;"><b>Ip</b></td>
								<td style="font-size: 18px;" style="width: 15%; height: 18px;"><b>Mac</b></td>
								<td style="font-size: 18px;" style="width: 5%; height: 18px;"><b>Vlan</b></td>
							</tr>';
				$sql = "SELECT a.id_dispositivo, a.id_router, a.id_tarjeta, a.estado, b.estado as estado_txt, b.color, a.id_tipo_prod, c.nombre as txt_tipo_prod, a.vlan, a.ip, a.mac, a.vlan
						FROM isp.int_contrato_caja a INNER JOIN 
								isp.int_estados_equipo b ON a.estado = b.id INNER JOIN 
								isp.int_tipo_prod c ON a.id_tipo_prod = c.id 
						WHERE a.id_contrato = $id_contrato";
				if ($oCon->Query($sql)) {
					if ($oCon->NumFilas() > 0) {
	
						do {
							$id_dispositivo = $oCon->f('id_dispositivo');
							$id_router 		= $oCon->f('id_router');
							$id_tarjeta 	= $oCon->f('id_tarjeta');
							$estado 		= $oCon->f('estado');
							$id_tipo_prod 	= $oCon->f('id_tipo_prod');
							$vlan 			= $oCon->f('vlan');
							$ip 			= $oCon->f('ip');
							$mac 			= $oCon->f('mac');
							$estado_txt 	= $oCon->f('estado_txt');
							$txt_tipo_prod 	= $oCon->f('txt_tipo_prod');
							$color 			= $oCon->f('color');
							$vlan 			= $oCon->f('vlan');

							$olt = '';
							if(isset($array_olts[$id_dispositivo])){
								$olt = $array_olts[$id_dispositivo];
							}

							$router = '';
							if(isset($array_routers[$id_router])){
								$router = $array_routers[$id_router];
							}

							$cabecera .= '
								<tr>
									<td style="font-size: 18px;" style="width: 15%; height: 12px;">'.limitar_string($txt_tipo_prod, 15).'</td>
									<td style="font-size: 18px;" style="width: 10%; height: 12px; color:'.$color.';">'.limitar_string($estado_txt, 15).'</td>
									<td style="font-size: 18px;" style="width: 15%; height: 12px;">'.limitar_string($olt, 15).'</td>
									<td style="font-size: 18px;" style="width: 15%; height: 12px;">'.limitar_string($router, 15).'</td>
									<td style="font-size: 18px;" style="width: 15%; height: 12px;">'.limitar_string($id_tarjeta, 15).'</td>
									<td style="font-size: 18px;" style="width: 10%; height: 12px;">'.limitar_string($ip, 15).'</td>
									<td style="font-size: 18px;" style="width: 15%; height: 12px;">'.limitar_string($mac, 15).'</td>
									<td style="font-size: 18px;" style="width: 5%; height: 12px;">'.limitar_string($vlan, 15).'</td>
								</tr>';
						} while ($oCon->SiguienteRegistro());
					}
				}
				$oCon->Free();
		
		$cabecera .= '</table>';

		$tableProd = '<table align="left" border="1" style="width: 98%; margin-top: 10px; border-collapse: collapse;">';
		$tableProd .= '<tr>';
		$tableProd .= '<td align="center" style="font-size: 12px;""><b>Material</b></td>';
		$tableProd .= '<td align="center" style="font-size: 12px;""><b>Cantidad</b></td>';
		$tableProd .= '<td align="center" style="font-size: 12px;""><b>Material</b></td>';
		$tableProd .= '<td align="center" style="font-size: 12px;""><b>Cantidad</b></td>';
		$tableProd .= '</tr>';

		//materiales

		if($mat_usado_sn == 'S'){
			$sql = "SELECT count(*) as control_mate
					from isp.instalacion_materiales 
					where 
					id_instalacion = $id";
			if ($oCon->Query($sql)) {
				if ($oCon->NumFilas() > 0) {

					do {
						$control_mate = $oCon->f('control_mate');
					} while ($oCon->SiguienteRegistro());
				}
			}
			$oCon->Free();

			$sql = "SELECT id_prod as cod_prod, id_bodega, cantidad
					from isp.instalacion_materiales 
					where 
					id_instalacion = $id ORDER BY id_prod";
			if ($oCon->Query($sql)) {
				if ($oCon->NumFilas() > 0) {
					$d = 1;
					do {

						$cod_prod = $oCon->f('cod_prod');
						$id_bodega = $oCon->f('id_bodega');
						$cantidad = $oCon->f('cantidad');

						if (!empty($id_bodega)) {
							$sqlProd = "SELECT a.prod_nom_prod FROM saeprod a, saeprbo b WHERE a.prod_cod_prod = b.prbo_cod_prod AND b.prbo_cod_bode = $id_bodega AND a.prod_cod_prod = '$cod_prod' LIMIT 1";
							if ($oCon2->Query($sqlProd)) {
								if ($oCon2->NumFilas() > 0) {
									do {
										$prod_nom_prod = $oCon2->f('prod_nom_prod');

										if (($d % 2) <> 0) {
											$tableProd .= '<tr>';
										}

										$tableProd .= '<td style="font-size: 18px;" style="width: 35%; height: 18px;">' . $prod_nom_prod . '</td>';
										$tableProd .= '<td style="font-size: 18px;" style="width: 15%;">'.$cantidad.'</td>';

										if (($d % 2) == 0) {
											$tableProd .= '</tr>';
										}
										$d++;
									} while ($oCon2->SiguienteRegistro());
								}
							}
							$oCon2->Free();
						}
					} while ($oCon->SiguienteRegistro());
				} else {
					$tableProd .= '<tr >';
					$tableProd .= '<td colspan="4">Sin datos..</td>';
					$tableProd .= '</tr>';
				}
			}
			$oCon->Free();

			if (($control_mate % 2) <> 0) {
				$tableProd .= '</tr>';
			}

			$tableProd .= '</table>';
		}else if ($material_os == 'S' && $mat_usado_sn == 'N') {

			$sql = "SELECT count(*) as control_mate
					from isp.int_orden_materiales 
					where 
					id_sucursal = $id_sucursal";
			if ($oCon->Query($sql)) {
				if ($oCon->NumFilas() > 0) {

					do {
						$control_mate = $oCon->f('control_mate');
					} while ($oCon->SiguienteRegistro());
				}
			}
			$oCon->Free();

			$sql = "SELECT cod_prod, id_bodega
					from isp.int_orden_materiales 
					where 
					id_sucursal = $id_sucursal ORDER BY cod_prod";
			if ($oCon->Query($sql)) {
				if ($oCon->NumFilas() > 0) {
					$d = 1;
					do {

						$cod_prod = $oCon->f('cod_prod');
						$id_bodega = $oCon->f('id_bodega');

						if (!empty($id_bodega)) {
							$sqlProd = "SELECT a.prod_nom_prod FROM saeprod a, saeprbo b WHERE a.prod_cod_prod = b.prbo_cod_prod AND b.prbo_cod_bode = $id_bodega AND a.prod_cod_prod = '$cod_prod' LIMIT 1";
							if ($oCon2->Query($sqlProd)) {
								if ($oCon2->NumFilas() > 0) {
									do {
										$prod_nom_prod = $oCon2->f('prod_nom_prod');

										if (($d % 2) <> 0) {
											$tableProd .= '<tr>';
										}

										$tableProd .= '<td style="font-size: 18px;" style="width: 35%; height: 18px;">' . $prod_nom_prod . '</td>';
										$tableProd .= '<td style="font-size: 18px;" style="width: 15%;"></td>';

										if (($d % 2) == 0) {
											$tableProd .= '</tr>';
										}
										$d++;
									} while ($oCon2->SiguienteRegistro());
								}
							}
							$oCon2->Free();
						}
					} while ($oCon->SiguienteRegistro());
				} else {
					$tableProd .= '<tr >';
					$tableProd .= '<td colspan="4">Sin datos..</td>';
					$tableProd .= '</tr>';
				}
			}
			$oCon->Free();

			if (($control_mate % 2) <> 0) {
				$tableProd .= '</tr>';
			}

			$tableProd .= '</table>';
		} else {
			$d = 1;
			for ($i = 0; $i < count($arrayDetallaFormato); $i++) {

				if (($d % 2) <> 0) {
					$tableProd .= '<tr>';
				}

				$tableProd .= '<td style="font-size: 18px;" style="width: 35%; height: 18px;">' . $arrayDetallaFormato[$i] . '</td>';
				$tableProd .= '<td style="font-size: 18px;" style="width: 15%;"></td>';

				if (($d % 2) == 0) {
					$tableProd .= '</tr>';
				}
				$d++;
			}

			$tableProd .= '</tr>';
			$tableProd .= '</table>';
		}

		$tableObservacion = '<table align="left" style="width: 98%; margin-top: 20px; border-collapse: collapse;">';
		$tableObservacion .= '<tr>';
		$tableObservacion .= '<td style="width: 10%;">Observaciones:</td>';
		$tableObservacion .= '<td style="width: 90%; border-bottom: 2px solid #000"></td>';
		$tableObservacion .= '</tr>';
		$tableObservacion .= '</table>';


		$firma = '<table align="center" style="width: 90%; margin-top: 10px;">';
		$firma .= '<tr>';
		$firma .= '<td colspan="3">Sr(a). Cliente, su firma indica que esta de acuerdo con los datos consignados en esta orden de trabajo... LEALO antes de firmar y
		confirme el funcionamiento de su servicio, si lo requiere utilice el espacio de observaciones</td>';
		$firma .= '</tr>';
		$firma .= '<tr>';
		$firma .= '<td colspan="3">&nbsp;</td>';
		$firma .= '</tr>';
		$firma .= '<tr>';
		$firma .= '<td colspan="3">&nbsp;</td>';
		$firma .= '</tr>';
		$firma .= '<tr>';
		$firma .= '<td colspan="3">&nbsp;</td>';
		$firma .= '</tr>';
		$firma .= '<tr>';
		$firma .= '<td align="left">............................................................</td>';
		$firma .= '<td align="left">&nbsp;&nbsp;</td>';
		$firma .= '<td align="left">............................................................</td>';
		$firma .= '</tr>';
		$firma .= '<tr>';
		$firma .= '<td align="left" style="font-size: 14px;"><b>Firma Tecnico</b></td>';
		$firma .= '<td align="left">&nbsp;&nbsp;</td>';
		$firma .= '<td align="left" style="font-size: 14px;"><b>Firma Cliente</b></td>';
		$firma .= '</tr>';
		$firma .= '<tr>';
		$firma .= '<td align="left" style="font-size: 14px;">C.C.(ID.): ' . $id_tenico . '</td>';
		$firma .= '<td align="left">&nbsp;&nbsp;</td>';
		$firma .= '<td align="left" style="font-size: 14px;">C.C.(ID.): ....................................</td>';
		$firma .= '</tr>';
		$firma .= '<tr></tr>';
		$firma .= '<tr>';
		$firma .= '<td align="left" style="font-size: 14px;">Impreso: ' . $fecha_print_ok . '</td>';
		$firma .= '</tr>';
		$firma .= '</table>';

		$documento = '<page backimgw="70%" backtop="10mm" backbottom="10mm" backleft="20mm" backright="10mm" footer="date;heure;page">';
		$documento .= $logo . $cabecera . $tableProd . $tableObservacion . $firma;
		$documento .= '</page>';

		$html2pdf = new HTML2PDF('P', 'A5', 'es');
		$html2pdf->WriteHTML($documento);
		$ruta = DIR_FACTELEC . 'Include/archivos/' . $id . '.pdf';
		$html2pdf->Output($ruta, 'F');
		$rutaPdf = $ruta;

		return $documento;
	}
}

function formatoAvisoCobro($id_cuota = 0, $empresa = '', $sucursal = '', $msj = '')
{

	include_once('../../Include/codigo_de_barras/barcode.inc.php');
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$idEmpresa = $_SESSION['U_EMPRESA'];
	$idSucursal = $_SESSION['U_SUCURSAL'];

	$idEmpresa = $_SESSION['U_EMPRESA'];
	$idSucursal = $_SESSION['U_SUCURSAL'];

	if (!empty($empresa)) {
		$idEmpresa = $empresa;
	}
	if (!empty($sucursal)) {
		$idSucursal = $sucursal;
	}

	//LECTURA SUCIA


	$sql = "SELECT empr_nom_empr, empr_ruc_empr, empr_path_logo
			from saeempr where empr_cod_empr = $idEmpresa ";
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			$razonSocial = trim($oIfx->f('empr_nom_empr'));
			$ruc_empr = $oIfx->f('empr_ruc_empr');
			$empr_path_logo = $oIfx->f('empr_path_logo');
		}
	}
	$oIfx->Free();


	//datos de la sucursal
	$sql_sucu = "SELECT sucu_nom_sucu, sucu_dir_sucu 
				from saesucu 
				where sucu_cod_empr = $idEmpresa and 
				sucu_cod_sucu = $idSucursal ";
	if ($oIfx->Query($sql_sucu)) {
		if ($oIfx->NumFilas() > 0) {
			$sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
			$sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');
		}
	}
	$oIfx->Free();

	$codigo_sucu = '';
	if (strlen($idSucursal) == 1) {
		$codigo_sucu = '0' . $idSucursal;
	} else {
		$codigo_sucu = $idSucursal;
	}

	$sql = "SELECT id_contrato, numero_oc, detalle_oc, fecha_oc, fecha_po, mes, anio, tarifa, fecha
			from isp.contrato_pago 
			WHERE id = $id_cuota";
	//echo $sql; exit;
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$id_contrato = $oCon->f('id_contrato');
			$numero_oc = $oCon->f('numero_oc');
			$detalle_oc = $oCon->f('detalle_oc');
			$mes = $oCon->f('mes');
			$anio = $oCon->f('anio');
			$tarifa = $oCon->f('tarifa');
			$fecha_oc = fecha_mysql_dmy($oCon->f('fecha_oc'));
			$fecha_po = fecha_mysql_dmy($oCon->f('fecha_po'));
			$fecha_cuota = $oCon->f('fecha');
		}
	}
	$oCon->Free();

	$sql = "SELECT nom_clpv, ruc_clpv, codigo, direccion, id_barrio, id_sector, observaciones, ruta, referencia, direccion_cobro
			from isp.contrato_clpv
			WHERE id = $id_contrato";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$nom_clpv = $oCon->f('nom_clpv');
			$ruc_clpv = $oCon->f('ruc_clpv');
			$codigo = $oCon->f('codigo');
			$direccion = $oCon->f('direccion');
			$id_barrio = $oCon->f('id_barrio');
			$id_sector = $oCon->f('id_sector');
			$observaciones = $oCon->f('observaciones');
			$ruta = $oCon->f('ruta');
			$referencia = $oCon->f('referencia');
			$direccion_cobro = $oCon->f('direccion_cobro');
		}
	}
	$oCon->Free();

	if (!empty($id_barrio) && !empty($id_sector)) {
		$barrio = consulta_string_func("SELECT barrio from isp.int_barrio where id = $id_barrio", "barrio", $oCon, '');
		$sector = consulta_string_func("SELECT sector from comercial.sector_direccion where id = $id_sector", "sector", $oCon, '');
	} else {
		$barrio = '';
		$sector = '';
	}


	$div_s = "";

	// $sql = "SELECT clse_cod_prod, clse_nom_prod, clse_pre_clse
	// 		FROM saeclse 
	// 		WHERE clse_cod_contr = $id_contrato AND
	// 		clse_cobr_sn = 'S'";
	// if ($oIfx->Query($sql)) {			
	//     if ($oIfx->NumFilas() > 0) {
	// 		do{
	// 			$clse_cod_prod = $oIfx->f('clse_cod_prod');
	// 			$clse_nom_prod = $oIfx->f('clse_nom_prod');
	// 			$clse_pre_clse = $oIfx->f('clse_pre_clse');

	// 			$div_s .= '<tr>
	// 							<td style="width: 100mm; font-size: 18px;">'.$clse_nom_prod.'</td>
	// 							<td style="width: 40mm; font-size: 18px;">'.$clse_pre_clse.'</td>
	// 						</tr>';
	// 		}while($oIfx->SiguienteRegistro());
	//     }
	// }
	// $oIfx->Free();
	$fecha_actual =  date("d-m-Y", strtotime($fecha_cuota));
	// $fecha_actual = date('d-m-Y');
	if ($fecha_actual == '30-01-2020' || $fecha_actual == '31-01-2020') {
		$fecha_actual = '02/29/2020';
	} else {
		// $fecha_actual = date("m/d/Y",strtotime($fecha_actual."+1 month"));
		$fecha_actual = date("m/d/Y", strtotime($fecha_actual));
	}

	$fecha_final = explode("/", $fecha_actual);
	$ultimo_dia = date("d", (mktime(0, 0, 0, $fecha_final[0] + 1, 1, $fecha_final[2]) - 1));
	$fecha_actual = "{$fecha_final[2]}-{$fecha_final[0]}-{$ultimo_dia}";
	// echo $fecha_actual; exit;

	$sql_actual = "SELECT id_contrato, fecha, (tarifa - valor_pago) as deuda from isp.contrato_pago where id_contrato = $id_contrato and fecha <= '$fecha_actual' and estado_fact is null and tipo = 'P' order by year(fecha) asc";

	if ($oCon->Query($sql_actual)) {
		if ($oCon->NumFilas() > 0) {
			do {
				setlocale(LC_TIME, 'spanish');
				// $date = date("F", $string);
				// $date = strftime("%A, %d de %B del %Y", strtotime($string));
				$mes_f = strftime("%B", strtotime($oCon->f('fecha')));
				// $mes_f = strtoupper(substr($mes_f, 0, 3));
				$anio_f = date('Y', strtotime($oCon->f('fecha')));
				$div_s .= '<div style="width: 85%; display: table; float: left; margin-bottom: -15px;">
								<span style="font-size: 10px; font-weight: bold; margin-left: 5px;">Mensualidad ' . $mes_f . ' ' . $anio_f . '</span>
							</div>
							<div style="width: 15%; display: table; float: left; margin-bottom: -15px;">
							    <span style="font-size: 9px; text-align: right;">' . $oCon->f('deuda') . '</span> 
							</div>
						   <br>';
				// $div_s .= '<tr>
				// 			<td style="width: 100mm; font-size: 14.5px;"> CARGO FIJO MENSUAL  '.$mes_f.'/'.$anio_f.'</td>
				// 			<td style="width: 40mm; font-size: 14.5px;">'.$oCon->f('deuda').'</td>
				// 		 </tr>';

				$total_a_pagar = $total_a_pagar + $oCon->f('deuda');
				// $array_deudas[$id_contrato][$oCon->f('fecha')] = array($oCon->f('fecha'), $oCon->f('deuda'));
			} while ($oCon->SiguienteRegistro());
		}
	}
	$oCon->Free();

	$fecha_pago = '';
	$valor_pago = 0;

	//query ultimo pago
	$sql = "SELECT id_pago, date(fecha_server) as fecha_pago, sum(valor) as valor_pago from isp.contrato_factura where id_contrato = $id_contrato and estado = 'A' group by 1,2 order by 1 desc";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$id_pago = $oCon->f('id_pago');
			$fecha_pago = fecha_mysql_dmy($oCon->f('fecha_pago'));
			$valor_pago = $oCon->f('valor_pago');
		}
	}
	$oCon->Free();

	//codigo pse
	$codigo_pse = $codigo_sucu . '' . $codigo;
	$rutaCodi = '';
	// if ($codigo_pse != '') {
	// 	$rutaCodi = DIR_FACTELEC . 'include/archivos/' . $codigo_pse . '.gif';
	// 	new barCodeGenrator($codigo_pse, 1, $rutaCodi, 450, 100, true);
	// }

	// $div = '< style="width: 214mm; height: 135mm; margin: 20mm;">';

	// $div .= '<div style="width: 214mm; height: 19mm;"></div>';
	$empr_path_logo = str_replace('\\', '/', $empr_path_logo);;

	$needle = 'htdocs';
	$path = substr($empr_path_logo, strpos($empr_path_logo, $needle) + strlen($needle));

	$empr_path_logo = "../../../..{$path}";

	$div .= '<div style="width: 100%; height: 15mm;">';
	$div .= '<div align="center" style="width: 100%; margin: 0px;">';
	$div .= '<div style="float:left">';
	$div .= '<span align="left" style="" rowspan="3"><img width="40px;" height="25px;" src="' . $empr_path_logo . '"></span>';
	$div .= '</div>';
	$div .= '<div style="float:left">';
	$div .= '<div>';
	// $div .= '<span align="center" style="text-align: center; font-size: 7.5px;">' . $razonSocial . '</span>';
	$div .= '</div>';
	// $div .= '<div><span align="center" style="font-size: 7px;">NIT : ' . $ruc_empr . ' ' . $sucu_nom_sucu . ': ' . htmlentities($sucu_dir_sucu) . '</span></div>';
	$div .= '<div style="text-align: center; margin-left: 50px;"><span align="center" style="font-size: 9px;">' . $sucu_nom_sucu . '</span></div>';
	$div .= '</div>';
	$div .= '</div>';

	$div .= '<div style="width: 100%; display: table; text-align: center;">
				<span style="font-size: 10px; font-weight: bold;">AVISO DE COBRO</span>
			</div>
			<div style="width: 100%; display: table; margin-top: -2px;">
				<span style="font-size: 10px; font-weight: bold;">FECHA IMPRESION: </span>
				<span style="font-size: 9px;">' . $fecha_po . '</span>
			</div>
			<div style="width: 100%; display: table; margin-top: -2px;">
				<span style="font-size: 10px; font-weight: bold;">CODIGO: </span>
				<span style="font-size: 9px;">' . $codigo . '</span>
				<span style="font-size: 10px; font-weight: bold; margin-left: 50px;">RUTA: </span>
				<span style="font-size: 9px;">' . $ruta . '</span>
			</div>
			<div style="width: 100%; display: table; margin-top: -2px;">
				<span style="font-size: 10px; font-weight: bold;">SUSCRIPTOR: </span>
				<span style="font-size: 9px;">' . $nom_clpv . '</span>
			</div>
			<div style="width: 100%; display: table; margin-top: -2px;">
			 	<span style="font-size: 9px;">' . $sector . ' - ' . $barrio . ' - ' . $direccion . '</span>
			</div>
			<div style="width: 100%; display: table; margin-top: -2px;">
			 	<span style="font-size: 9px;">' . $referencia . '</span>
			</div>';

	if (!empty($direccion_cobro)) {
		$div .= '<div style="width: 100%; display: table; margin-top: -2px;">
				<span style="font-size: 10px; font-weight: bold;">DIR COBRO: </span>
				<span style="font-size: 9px;">' . $direccion_cobro . '</span>
   			</div>';
	}

	if (!empty($observaciones)) {
		$div .= '<div style="width: 100%; display: table; margin-top: -2px;">
					<span style="font-size: 10px; font-weight: bold;">OBSERVACION: </span>
					<span style="font-size: 9px;">' . strtoupper($observaciones) . '</span>
				</div>';
		// $div .= '<div style="width: 100%; border: 1px solid black; border-radius: 0 0 3px; padding: 0.6px; margin-top: 5px;">
		// 			<div style="font-size: 8px; margin: 4px;">'.$observaciones.'</div>
		// 		</div>';
	}

	$div .= '<div style="width: 100%; display: table; margin-top: 5px;">
			 	<span style="font-size: 7px;"></span>
			</div>';

	$div .=	'<div style="width: 100%; display: table; margin-top: 5px; border: solid 1px black; border-radius: 3px 3px 0 0; border-bottom: none; padding: 1px;">
					<span style="font-size: 10px; font-weight: bold;">UN ATENTO RECORDATORIO DE COBRO:</span>
				</div>';

	$div .=	'<div style="width: 100%; border: 1px solid black; border-radius: 0 0 3px; padding: 0.2px;">
					' . $div_s . '
					<div style="margin-top: 0px;">
						<span style="font-size: 10px; font-weight: bold; margin-left: 50px;">TOTAL A PAGAR: </span>
					</div>
					<div style="width: 15%; margin-top: -16px; float: right;">
						<span style="font-size: 9px;">' . number_format($total_a_pagar, 2) . '</span>
					</div>
				</div>';

	if (!empty($detalle_oc)) {
		$div .= '<div style="width: 100%; border: 1px solid black; border-radius: 0 0 3px; padding: 0.6px; margin-top: 5px;">
			<div style="font-size: 9px; margin: 4px;">' . $detalle_oc . '</div>
		</div>';
	} else if (!empty($msj)) {
		$div .= '<div style="width: 100%; border: 1px solid black; border-radius: 0 0 3px; padding: 0.6px; margin-top: 5px;">
			<div style="font-size: 9px; margin: 4px;">' . $msj . '</div>
		</div>';
	}

	$div .= '<div style="width: 100%; margin-top: -8px;">';
	$div .= '<span style="font-size: 8px; font-weight: bold;">SU FACTURA LA PUEDE RECOGER EN OFICINAS</span>';
	$div .= '</div>';
	$div .= '</div>';

	// RECUADRO BLANCO
	$div .= ' <div style="display:block; page-break-before:always;"></div>';
	$div .= '<div style="width: 100%; min-height: 70px; border: 1px solid black; padding: 0px; margin-top: 5px;">
			</div>';

	// COPIA CLIENTE
	$div .= ' <div style="display:block; page-break-before:always;"></div>';
	$div .= '<div style="width: 100%; border-bottom: 1px solid; margin-bottom: 3px;"></div>';
	$div .= '<div style="width: 100%; text-align: left; margin-bottom: -3px;"><img src="../../imagenes/scissors.png" width="8px" height="8px"></div>';
	$div .= '<div style="width: 100%; border-bottom: 1px dotted; margin-bottom: 0px;"></div>';
	$div .= '<div style="width: 100%; text-align: right"><span style="font-size: 8px; font-weight: bold;">COPIA RECEPTOR</span></div>';
	$div .= '<div style="width: 100%; border-bottom: 1px solid; margin-bottom: 12px;"></div>';
	$div .= '<div style="width: 100%; height: 15mm;">';
	$div .= '<div  style="width: 100%; margin: 0px;">';
	$div .= '<div style="width: 100%; display: table; margin-top: -2px;">
				<span style="font-size: 10px; font-weight: bold;">CODIGO: </span>
				<span style="font-size: 9px;">' . $codigo . '</span>
				<span style="font-size: 10px; font-weight: bold; margin-left: 50px;">RUTA: </span>
				<span style="font-size: 9px;">' . $ruta . '</span>
			</div>';
	$div .= '<div style="width: 100%; display: table; margin-top: -2px;">
				<span style="font-size: 10px; font-weight: bold;">NOMBRE: </span>
				<span style="font-size: 9px;">' . $nom_clpv . '</span>
			</div>
			<div style="width: 100%; display: table; margin-top: -2px;">
				<span style="font-size: 10px; font-weight: bold;">DIRECCION: </span>
				<span style="font-size: 9px;">' . $sector . ' - ' . $barrio . ' - ' . $direccion . ' ' . $referencia . '</span>
			</div>';

	if (!empty($direccion_cobro)) {
		$div .= '<div style="width: 100%; display: table; margin-top: -2px;">
				<span style="font-size: 10px; font-weight: bold;">DIR COBRO: </span>
				<span style="font-size: 9px;">' . $direccion_cobro . '</span>
			   </div>';
	}
	$div .= '<div style="width: 100%; display: table; margin-top: -2px;">
				<span style="font-size: 10px; font-weight: bold;">TOTAL DEL PAGO: </span>
				<span style="font-size: 9px;">' . number_format($total_a_pagar, 2) . '</span>
			</div>';

	$div .=	'<div style="width: 100%; border: 1px solid black;  padding: 0.2px;">
				' . $div_s . '
				
			</div>';

	$div .= '<div style="width: 100%; min-height: 40px; border: 1px solid black; padding: 0px; margin-top: 5px;">
				<div style="width: 58%; float:left; min-height: 40px; border-right: 1px solid black; padding-right: 2px;">
					<div style="font-size: 9px; font-weight: bold; padding-left: 1.5px;">Observaciones: </div>
				</div>
			</div>';
	// $div .= '<div style="width: 100%; border: 1px solid black; margin-top: 5px;">

	// 		</div>';

	$div .= '</div>';
	$div .= '</div>';
	// COPIA CLIENTE

	// $documento .= '<page backtop="5mm" backbottom="5mm" backleft="5mm" backright="5mm">';
	$documento .= $div;
	// $documento .= '</page>';

	return $documento;
}

function formatoAvisoCorte($id_clpv, $id_contrato, $id = '', $fecha, $fechaCorte, $empresa = '', $sucursal = '', $msj = '')
{

	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$idEmpresa = $_SESSION['U_EMPRESA'];
	$idSucursal = $_SESSION['U_SUCURSAL'];

	$idEmpresa = $_SESSION['U_EMPRESA'];
	$idSucursal = $_SESSION['U_SUCURSAL'];

	if (!empty($empresa)) {
		$idEmpresa = $empresa;
	}
	if (!empty($sucursal)) {
		$idSucursal = $sucursal;
	}

	$sql = "SELECT nom_clpv, ruc_clpv, codigo, direccion, id_barrio, id_sector, ruta, referencia, direccion_cobro
			from isp.contrato_clpv
			WHERE id = $id_contrato";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$nom_clpv = $oCon->f('nom_clpv');
			$ruc_clpv = $oCon->f('ruc_clpv');
			$codigo = $oCon->f('codigo');
			$direccion = $oCon->f('direccion');
			$id_barrio = $oCon->f('id_barrio');
			$id_sector = $oCon->f('id_sector');
			$ruta = $oCon->f('ruta');
			$referencia = $oCon->f('referencia');
			$direccion_cobro = $oCon->f('direccion_cobro');
		}
	}
	$oCon->Free();


	$fecha_actual =  date("d-m-Y");
	// $fecha_actual = date('d-m-Y');
	if ($fecha_actual == '30-01-2020' || $fecha_actual == '31-01-2020') {
		$fecha_actual = '02/29/2020';
	} else {
		$fecha_actual = date("m/d/Y", strtotime($fecha_actual));
	}

	$fecha_final = explode("/", $fecha_actual);
	$ultimo_dia = date("d", (mktime(0, 0, 0, $fecha_final[0] + 1, 1, $fecha_final[2]) - 1));
	$fecha_actual = "{$fecha_final[2]}-{$fecha_final[0]}-{$ultimo_dia}";
	// echo $fecha_actual; exit;

	$sql_actual = "SELECT sum(tarifa - valor_pago) as deuda from isp.contrato_pago where id_contrato = $id_contrato and fecha <= '$fecha_actual' and estado_fact is null and tipo = 'P' order by year(fecha) asc";
	if ($oCon->Query($sql_actual)) {
		if ($oCon->NumFilas() > 0) {
			$total_a_pagar = $oCon->f('deuda');
		}
	}
	$oCon->Free();

	$sql = "SELECT empr_nom_empr, empr_ruc_empr, empr_path_logo
			from saeempr where empr_cod_empr = $idEmpresa ";
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			$razonSocial = trim($oIfx->f('empr_nom_empr'));
			$ruc_empr = $oIfx->f('empr_ruc_empr');
			$empr_path_logo = $oIfx->f('empr_path_logo');
		}
	}
	$oIfx->Free();


	//datos de la sucursal
	$sql_sucu = "SELECT sucu_nom_sucu, sucu_dir_sucu 
				from saesucu 
				where sucu_cod_empr = $idEmpresa and 
				sucu_cod_sucu = $idSucursal ";
	if ($oIfx->Query($sql_sucu)) {
		if ($oIfx->NumFilas() > 0) {
			$sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
			$sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');
		}
	}
	$oIfx->Free();

	// $empr_path_logo = '/var/www/Colombia/WebApp/Include/Clases/Formulario/Plugins/reloj/logo_sisconti.jpg';
	$empr_path_logo = str_replace('\\', '/', $empr_path_logo);;

	$needle = 'htdocs';
	$path = substr($empr_path_logo, strpos($empr_path_logo, $needle) + strlen($needle));

	$empr_path_logo = "../../../..{$path}";



	if (!empty($id_barrio) && !empty($id_sector)) {
		$barrio = consulta_string_func("SELECT barrio from isp.int_barrio where id = $id_barrio", "barrio", $oCon, '');
		$sector = consulta_string_func("SELECT sector from comercial.sector_direccion where id = $id_sector", "sector", $oCon, '');
	} else {
		$barrio = '';
		$sector = '';
	}

	$div .= '<div style="width: 100%; height: 15mm;">';
	$div .= '<div align="center" style="width: 100%; margin: 0px;">';
	$div .= '<div style="float:left">';
	$div .= '<span align="left" style="" rowspan="3"><img width="40px;" height="25px;" src="' . $empr_path_logo . '"></span>';
	$div .= '</div>';
	$div .= '<div style="float:left">';
	$div .= '<div>';
	// $div .= '<span align="center" style="text-align: center; font-size: 7.5px;">' . $razonSocial . '</span>';
	$div .= '</div>';
	// $div .= '<div><span align="center" style="font-size: 7px;">NIT : ' . $ruc_empr . ' ' . $sucu_nom_sucu . ': ' . htmlentities($sucu_dir_sucu) . '</span></div>';
	$div .= '<div style="text-align: center; margin-left: 50px;"><span align="center" style="font-size: 9px;">' . $sucu_nom_sucu . '</span></div>';
	$div .= '</div>';
	$div .= '</div>';

	// $div .= '<div style="width: 100%; height: 15mm;">';
	// $div .= '<div align="center" style="width: 100%; margin: 0px;">';
	// $div .= '<div style="float:left">';
	// $div .= '<span align="left" style="" rowspan="3"><img width="100px;" height="60px;" src="' . $empr_path_logo . '"></span>';
	// $div .= '</div>';
	// $div .= '<div style="margin-right: 100px;">';
	// $div .= '<div>';
	// $div .= '<span align="center" style="text-align: center; font-size: 13px;">' . $razonSocial . '</span>';
	// $div .= '</div>';
	// $div .= '<div><span align="center" style="font-size: 13px;">RUC : ' . $ruc_empr . '</span></div>';
	// $div .= '<div><span align="center" style="font-size: 13px;">' . $sucu_nom_sucu . ': ' . htmlentities($sucu_dir_sucu) . '</span></div>';
	// $div .= '</div>';
	// $div .= '</div>';

	$div .= '
				<div style="width: 100%; display: table; text-align: center;">
					<span style="font-size: 10px; font-weight: bold;">AVISO DE CORTE SERVICO</span>
				</div>
				<div style="width: 100%; display: table; margin-top: -2px;">
					<span style="font-size: 10px; font-weight: bold;">FECHA IMPRESION: </span>
					<span style="font-size: 9px;">' . $fecha . '</span>
				</div>
				<div style="width: 100%; display: table; margin-top: -2px;">
					<span style="font-size: 10px; font-weight: bold;">CODIGO: </span>
					<span style="font-size: 9px;">' . $codigo . '</span>
					<span style="font-size: 10px; font-weight: bold; margin-left: 50px;">RUTA: </span>
					<span style="font-size: 9px;">' . $ruta . '</span>
				</div>
				<div style="width: 100%; display: table; margin-top: -2px;">
					<span style="font-size: 10px; font-weight: bold;">SUSCRIPTOR: </span>
					<span style="font-size: 9px;">' . $nom_clpv . '</span>
				</div>
				<div style="width: 100%; display: table; margin-top: -2px;">
				 	<span style="font-size: 9px;">' . $sector . ' - ' . $barrio . ' - ' . $direccion . '</span>
				</div>
				<div style="width: 100%; display: table; margin-top: -2px;">
			 		<span style="font-size: 9px;">' . $referencia . '</span>
				</div>';

	if (!empty($direccion_cobro)) {
		$div .= '<div style="width: 100%; display: table; margin-top: -2px;">
				<span style="font-size: 10px; font-weight: bold;">DIR COBRO: </span>
				<span style="font-size: 9px;">' . $direccion_cobro . '</span>
			   </div>';
	}



	$div .= '<div style="width: 100%; display: table; margin-top: 5px;">
			 		<span style="font-size: 7px;"></span>
				</div>
				<div style="width: 100%; display: table; margin-top: -2px;">
					<span style="font-size: 10px; font-weight: bold;">SALDO: </span>
					<span style="font-size: 9px;">' . $total_a_pagar . '</span>
				</div>
				<div style="width: 100%; display: table; margin-top: 5px; border: solid 1px black; border-bottom: default; padding: none; text-align: center;">
					<p style="font-size: 10px; font-weight: bold; text-align: center;">Lamentamos informarle que tiene una orden de suspension para: ' . $fechaCorte . '</p>
					<p style="font-size: 8px; font-weight: bold; text-align: center;"></p>
				</div>';

	$div .= '<div style="width: 100%; display: table; margin-top: 5px; border: solid 1px black; border-bottom: default; padding: none; text-align: center;">
				<span style="font-size: 8px; font-weight: bold; text-align: center;">Recuerde que la visita en carro tiene un recargo adicional de Q. 25.00 a su saldo, y la reconexion un Costo Q. 50.00</span>
			</div>';

	// contenido
	// $div . = '<div style="width: 100%; border: 1px solid black; border-radius: 0 0 3px; padding: 0.6px;">
	// 			<div style="margin-top: 30px;">
	// 				<span style="font-size: 14px; font-weight: bold; margin-left: 100px;">TOTAL A PAGAR: </span>
	// 			</div>
	// 			<div style="width: 40%; margin-top: -16px; float: right;">
	// 				<!-- <span style="font-size: 13.5px;">'.number_format($total_a_pagar, 2).'</span> -->
	// 			</div>
	// 		</div>';
	// contenido

	// if (!empty($detalle_oc)) {
	// 	$div .= '<div style="width: 100%; border: 1px solid black; border-radius: 0 0 3px; padding: 0.6px; margin-top: 5px;">
	// 		<div style="font-size: 13.5px; margin: 4px;">'.$detalle_oc.'</div>
	// 	</div>';
	// }

	$div .= '</div>';


	$documento .= $div;
	return $documento;
}

function formatoAvisoVisita($id_cuota, $id_contrato, $fecha, $empresa = '', $sucursal = '', $msj = '', $anio, $mes)
{

	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$idEmpresa = $_SESSION['U_EMPRESA'];
	$idSucursal = $_SESSION['U_SUCURSAL'];

	if (!empty($empresa)) {
		$idEmpresa = $empresa;
	}
	if (!empty($sucursal)) {
		$idSucursal = $sucursal;
	}

	// $sql = "SELECT id_contrato, numero_oc, detalle_oc, fecha_oc, fecha_po, mes, anio, tarifa, fecha
	// 		from isp.contrato_pago 
	// 		WHERE id = $id_cuota";
	// 		// echo $sql; exit;
	// if ($oCon->Query($sql)) {
	//     if ($oCon->NumFilas() > 0) {
	// 		$fecha_cuota = $oCon->f('fecha');
	//     }
	// }
	// $oCon->Free();

	$sql = "SELECT nom_clpv, ruc_clpv, codigo, direccion, id_barrio, id_sector, ruta, referencia, direccion_cobro, observaciones
			from isp.contrato_clpv
			WHERE id = $id_contrato";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$nom_clpv = $oCon->f('nom_clpv');
			$ruc_clpv = $oCon->f('ruc_clpv');
			$codigo = $oCon->f('codigo');
			$direccion = $oCon->f('direccion');
			$id_barrio = $oCon->f('id_barrio');
			$id_sector = $oCon->f('id_sector');
			$ruta = $oCon->f('ruta');
			$referencia = $oCon->f('referencia');
			$direccion_cobro = $oCon->f('direccion_cobro');
			$observaciones = $oCon->f('observaciones');
		}
	}
	$oCon->Free();

	// $fecha_actual = date('d-m-Y');
	$fecha_actual = date('t-m-Y', strtotime("{$anio}-{$mes}-01"));
	if ($fecha_actual == '30-01-2020' || $fecha_actual == '31-01-2020') {
		$fecha_actual = '02/29/2020';
	} else {
		// $fecha_actual = date("m/d/Y",strtotime($fecha_actual."+1 month"));
		$fecha_actual = date("m/d/Y", strtotime($fecha_actual));
	}

	$fecha_final = explode("/", $fecha_actual);
	$ultimo_dia = date("d", (mktime(0, 0, 0, $fecha_final[0] + 1, 1, $fecha_final[2]) - 1));
	$fecha_actual = "{$fecha_final[2]}-{$fecha_final[0]}-{$ultimo_dia}";
	// echo $fecha_actual; exit;
	$sql_actual = "SELECT id_contrato, fecha, (tarifa - valor_pago) as deuda from isp.contrato_pago where id_contrato = $id_contrato and fecha <= '$fecha_actual' and estado_fact is null and tipo = 'P' order by year(fecha) asc";
	$total_a_pagar = 0;
	if ($oCon->Query($sql_actual)) {
		if ($oCon->NumFilas() > 0) {
			do {
				setlocale(LC_TIME, 'spanish');
				// $date = date("F", $string);
				// $date = strftime("%A, %d de %B del %Y", strtotime($string));
				$mes_f = strftime("%B", strtotime($oCon->f('fecha')));
				// $mes_f = strtoupper(substr($mes_f, 0, 3));
				$anio_f = date('Y', strtotime($oCon->f('fecha')));
				$div_s .= '<div style="width: 85%; display: table; float: left; margin-bottom: -15px;">
								<span style="font-size: 10px; font-weight: bold; margin-left: 10px;">Mensualidad ' . $mes_f . ' ' . $anio_f . '</span>
							</div>
							<div style="width: 15%; display: table; float: left; margin-bottom: -15px;">
							    <span style="font-size: 9px; text-align: right;">' . $oCon->f('deuda') . '</span> 
							</div>
						   <br>';
				// $div_s .= '<tr>
				// 			<td style="width: 100mm; font-size: 14.5px;"> CARGO FIJO MENSUAL  '.$mes_f.'/'.$anio_f.'</td>
				// 			<td style="width: 40mm; font-size: 14.5px;">'.$oCon->f('deuda').'</td>
				// 		 </tr>';

				$total_a_pagar = $total_a_pagar + $oCon->f('deuda');
				// $array_deudas[$id_contrato][$oCon->f('fecha')] = array($oCon->f('fecha'), $oCon->f('deuda'));
			} while ($oCon->SiguienteRegistro());
		}
	}
	$oCon->Free();

	// echo $total_a_pagar; exit;

	$sql = "SELECT empr_nom_empr, empr_ruc_empr, empr_path_logo
			from saeempr where empr_cod_empr = $idEmpresa ";
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			$razonSocial = trim($oIfx->f('empr_nom_empr'));
			$ruc_empr = $oIfx->f('empr_ruc_empr');
			$empr_path_logo = $oIfx->f('empr_path_logo');
		}
	}
	$oIfx->Free();


	//datos de la sucursal
	$sql_sucu = "SELECT sucu_nom_sucu, sucu_dir_sucu 
				from saesucu 
				where sucu_cod_empr = $idEmpresa and 
				sucu_cod_sucu = $idSucursal ";
	if ($oIfx->Query($sql_sucu)) {
		if ($oIfx->NumFilas() > 0) {
			$sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
			$sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');
		}
	}
	$oIfx->Free();

	// $empr_path_logo = '/var/www/Colombia/WebApp/Include/Clases/Formulario/Plugins/reloj/logo_sisconti.jpg';
	$empr_path_logo = str_replace('\\', '/', $empr_path_logo);

	$needle = 'htdocs';
	$path = substr($empr_path_logo, strpos($empr_path_logo, $needle) + strlen($needle));

	$empr_path_logo = "../../../..{$path}";


	if (!empty($id_barrio) && !empty($id_sector)) {
		$barrio = consulta_string_func("SELECT barrio from isp.int_barrio where id = $id_barrio", "barrio", $oCon, '');
		$sector = consulta_string_func("SELECT sector from comercial.sector_direccion where id = $id_sector", "sector", $oCon, '');
	} else {
		$barrio = '';
		$sector = '';
	}

	$div .= '<div style="width: 100%; height: 15mm;">';
	$div .= '<div align="center" style="width: 100%; margin: 0px;">';
	$div .= '<div style="float:left">';
	$div .= '<span align="left" style="" rowspan="3"><img width="40px;" height="25px;" src="' . $empr_path_logo . '"></span>';
	$div .= '</div>';
	$div .= '<div style="float:left">';
	$div .= '<div>';
	// $div .= '<span align="center" style="text-align: center; font-size: 7.5px;">' . $razonSocial . '</span>';
	$div .= '</div>';
	// $div .= '<div><span align="center" style="font-size: 7px;">NIT : ' . $ruc_empr . ' ' . $sucu_nom_sucu . ': ' . htmlentities($sucu_dir_sucu) . '</span></div>';
	$div .= '<div style="text-align: center; margin-left: 50px;"><span align="center" style="font-size: 9px;">' . $sucu_nom_sucu . '</span></div>';
	$div .= '</div>';
	$div .= '</div>';


	// $div .= '<div style="width: 100%; height: 15mm;">';
	// $div .= '<div align="center" style="width: 100%; margin: 0px;">';
	// $div .= '<div style="float:left">';
	// $div .= '<span align="left" style="" rowspan="3"><img width="100px;" height="60px;" src="' . $empr_path_logo . '"></span>';
	// $div .= '</div>';
	// $div .= '<div style="margin-right: 100px;">';
	// $div .= '<div>';
	// $div .= '<span align="center" style="text-align: center; font-size: 13px;">' . $razonSocial . '</span>';
	// $div .= '</div>';
	// $div .= '<div><span align="center" style="font-size: 13px;">RUC : ' . $ruc_empr . '</span></div>';
	// $div .= '<div><span align="center" style="font-size: 13px;">' . $sucu_nom_sucu . ': ' . htmlentities($sucu_dir_sucu) . '</span></div>';
	// $div .= '</div>';
	// $div .= '</div>';

	$div .= '<div style="width: 100%; height: 15mm;">
				<div style="width: 100%; display: table; text-align: center;">
					<span style="font-size: 10px; font-weight: bold;">AVISO DE VISITA</span>
				</div>
				<div style="width: 100%; display: table; margin-top: -2px;">
					<span style="font-size: 10px; font-weight: bold;">FECHA IMPRESION: </span>
					<span style="font-size: 9px;">' . date("d/m/Y") . '</span>
				</div>
				<div style="width: 100%; display: table; margin-top: -2px;">
					<span style="font-size: 10px; font-weight: bold;">CODIGO:</span>
					<span style="font-size: 9px;">' . $codigo . '</span>
					<span style="font-size: 10px; font-weight: bold; margin-left: 50px;">RUTA: </span>
					<span style="font-size: 9px;">' . $ruta . '</span>
				</div>
				<div style="width: 100%; display: table; margin-top: -2px;">
					<span style="font-size: 10px; font-weight: bold;">SUSCRIPTOR: </span>
					<span style="font-size: 9px;">' . $nom_clpv . '</span>
				</div>
				<div style="width: 100%; display: table; margin-top: -2px;">
				 	<span style="font-size: 9px;">' . $sector . ' - ' . $barrio . ' - ' . $direccion . '</span>
				</div>
				<div style="width: 100%; display: table; margin-top: -2px;">
			 		<span style="font-size: 9px;">' . $referencia . '</span>
				</div>';

	if (!empty($direccion_cobro)) {
		$div .= '<div style="width: 100%; display: table; margin-top: -2px;">
				<span style="font-size: 10px; font-weight: bold;">DIR COBRO: </span>
				<span style="font-size: 9px;">' . $direccion_cobro . '</span>
			   </div>';
	}

	if (!empty($observaciones)) {
		$div .= '<div style="width: 100%; display: table; margin-top: -2px;">
					<span style="font-size: 10px; font-weight: bold;">OBSERVACION: </span>
					<span style="font-size: 9px;">' . strtoupper($observaciones) . '</span>
				</div>';
		// $div .= '<div style="width: 100%; border: 1px solid black; border-radius: 0 0 3px; padding: 0.6px; margin-top: 5px;">
		// 			<div style="font-size: 8px; margin: 4px;">'.$observaciones.'</div>
		// 		</div>';
	}



	$div .= '<div style="width: 100%; display: table; margin-top: 12px;">
				 <span style="font-size: 7px;"></span>
			</div>';

	// <div style="width: 100%; border: 1px solid black; border-radius: 0 0 3px; padding: 0.6px;">
	// 				'.$div_s.'

	// 				<div style="margin-top: 30px;">
	// 					<span style="font-size: 13px; font-weight: bold; margin-left: 50px;">TOTAL A PAGAR: </span>
	// 				</div>
	// 				<div style="width: 40%; margin-top: -16px; float: right;">
	// 					<span style="font-size: 13.5px;">'.number_format($total_a_pagar, 2).'</span>
	// 				</div>
	// 			</div>

	// contenido
	$div .= '<div style="width: 100%; border: 1px solid black; padding: 0.6px;">
				<div style="text-align: left;">
					<span style="font-size: 10px; font-weight: bold;">FAVOR DEJAR SU PAGO PARA: ' . date("d/m/Y", strtotime($fecha)) . '</span>
				</div>
				' . $div_s . '
				<div style="margin-top: 0px;">
					<span style="font-size: 10px; font-weight: bold; margin-left: 30px;">TOTAL A PAGAR: </span>
				</div>
				<div style="width: 15%; margin-top: -16px; float: right;">
					<span style="font-size: 9px;">' . number_format($total_a_pagar, 2) . '</span>
				</div>
				<!-- <div style="margin-top: 10px; text-align: center;">
					<span style="font-size: 10px; font-weight: bold;">TOTAL A PAGAR: </span>
					<span style="font-size: 9px;">' . number_format($total_a_pagar, 2) . '</span>
				</div> -->
			</div>';
	// contenido

	if (!empty($msj)) {
		$div .= '<div style="width: 100%; display: table; margin-top: 5px; border: solid 1px black; /*border-radius: 3px 3px 0 0;*/ border-bottom: default; padding: none; text-align: center;">
			<p style="font-size: 9px; font-weight: bold; text-align: center;">' . $msj . '</p>
		</div>';
	} else {
		$div .= '<div style="width: 100%; display: table; margin-top: 5px; border: solid 1px black; /*border-radius: 3px 3px 0 0;*/ border-bottom: default; padding: none; text-align: center;">
			<p style="font-size: 9px; font-weight: bold; text-align: center;">Reciba un cordial saludo querido suscriptor.</p>
		</div>';
	}

	// if (!empty($detalle_oc)) {
	// 	$div .= '<div style="width: 100%; border: 1px solid black; border-radius: 0 0 3px; padding: 0.6px; margin-top: 5px;">
	// 		<div style="font-size: 13.5px; margin: 4px;">'.$detalle_oc.'</div>
	// 	</div>';
	// }

	$div .= '<div style="width: 100%; margin-top: -3px;">
				<span style="font-size: 8px; font-weight: bold;">&Oacute; FAVOR LLAMAR A OFICINA PARA INDICAR FECHA SEGURA DE PAGO </span>
			</div>';
	$div .= '</div>';


	$documento .= $div;
	return $documento;
}

function formatoPSE($array, $idempresa, $idSucursal, $oCon, $oIfx)
{
	$sql_sucu = "SELECT sucu_nom_sucu, sucu_cod_agen 
                from saesucu 
                where sucu_cod_empr = $idempresa and 
                sucu_cod_sucu = $idSucursal ";
	if ($oIfx->Query($sql_sucu)) {
		if ($oIfx->NumFilas() > 0) {
			$codigo_sucu = $oIfx->f('sucu_cod_agen');
		}
	}
	$oIfx->Free();

	//TIPO IDENTIFIACION
	$sql = "SELECT clpv_cod_clpv, clv_con_clpv from saeclpv";
	$array_clpv_con = array_dato($oIfx, $sql, 'clpv_cod_clpv', 'clv_con_clpv');

	//DATOS CLPV
	$sql = "SELECT id, codigo, ruc_clpv from isp.contrato_clpv";
	$array_cod_clpv = array_dato($oIfx, $sql, 'id', 'codigo');
	$array_ruc_clpv = array_dato($oIfx, $sql, 'id', 'ruc_clpv');
	$sql = "SELECT max (fact_num_preimp) as num_fact from saefact where fact_cod_empr = $idempresa and fact_cod_sucu = $idSucursal";
	$num_fact = consulta_string_func($sql, 'num_fact', $oIfx, 0);
	$sql = "SELECT num_digitos, num_letras, codigo_actual, codigo_automatico,
                dia_corte, dia_cobro, num_digitos
                from isp.int_parametros 
                where id_empresa = $idempresa and
                id_sucursal = $idSucursal";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$codigo_automatico = $oCon->f('codigo_automatico');
			$num_digitos = $oCon->f('num_digitos');
			$num_letras = $oCon->f('num_letras');
			$codigo_actual = $oCon->f('codigo_actual');
			$dia_corte = $oCon->f('dia_corte');
			$dia_cobro = $oCon->f('dia_cobro');
		}
	}
	$oCon->Free();

	$div  = '<table class="table table-striped table-condensed table-bordered table-hover" border="1" style="width: 50%; margin-top: 20px;">
                <tr>
                    <td style="font-size: 14px;">TIPO IDENTIFICACION</td>
                    <td style="font-size: 14px;">NUMERO DE IDENTIFICACION</td>
                    <td style="font-size: 14px;">DETALLE DEUDA</td>
                    <td style="font-size: 14px;">CODIGO</td>
                    <td style="font-size: 14px;">N DE FACTURA</td>
                    <td style="font-size: 14px;">VALOR</td>
                    <td style="font-size: 14px;"></td>
                </tr>';
	for ($i = 0; $i < count($array); $i++) {
		$data_abonados_e = explode("+", $array[$i]);
		$id 		= $data_abonados_e[0];
		$id_clpv    = $data_abonados_e[1];
		$tarifa 	= $data_abonados_e[2];
		$adeuda 	= $data_abonados_e[3];
		$cod_clpv   = $array_cod_clpv[$id];
		$ruc_clpv   = $array_ruc_clpv[$id];
		if ($adeuda > 0) {
			$clv_con_clpv   = $array_clpv_con[$id_clpv];
			if ($clv_con_clpv == '01') {                  // RUC
				$tipoIdentificacionComprador = 'RUT';
			} elseif ($clv_con_clpv == '02') {            // CEDULA
				$tipoIdentificacionComprador = 'CC';
			} elseif ($clv_con_clpv == '03') {            // PASAPORTE
				$tipoIdentificacionComprador = 'PP';
			} elseif ($clv_con_clpv == '07') {            // CONSUMIDOR FINAL
				$tipoIdentificacionComprador = 'CF';
			}
			$div .= '<tr>
                        <td style="font-size: 12px;">' . $tipoIdentificacionComprador . '</td>
                        <td style="font-size: 12px;" align="left">' . $ruc_clpv . '</td>
                        <td style="font-size: 12px;"> CARGO FIJO MENSUAL - ' . $id . '</td>
                        <td style="font-size: 12px;">' . $codigo_sucu . str_pad(($cod_clpv), $num_digitos, "0", STR_PAD_LEFT) . '</td>
                        <td style="font-size: 12px;">' . ($num_fact = $num_fact + 1) . '</td>
                        <td style="font-size: 12px;">' . round($adeuda, 0) . '</td>
                        <td style="font-size: 12px;">0</td>
                    </tr>';
		}
	}
	$div .= '</table>';

	return $div;
}

function formatoSG($array, $idempresa, $idSucursal, $oCon, $oIfx)
{
	$sql_sucu = "SELECT sucu_nom_sucu, sucu_cod_agen 
                from saesucu 
                where sucu_cod_empr = $idempresa and 
                sucu_cod_sucu = $idSucursal ";
	if ($oIfx->Query($sql_sucu)) {
		if ($oIfx->NumFilas() > 0) {
			$codigo_sucu = $oIfx->f('sucu_cod_agen');
		}
	}
	$oIfx->Free();

	//TIPO IDENTIFIACION
	$sql = "SELECT clpv_cod_clpv, clv_con_clpv from saeclpv";
	$array_clpv_con = array_dato($oIfx, $sql, 'clpv_cod_clpv', 'clv_con_clpv');

	//DATOS CLPV
	$sql = "SELECT id, nom_clpv, ruc_clpv from isp.contrato_clpv";
	$array_nom_clpv = array_dato($oIfx, $sql, 'id', 'nom_clpv');
	$array_ruc_clpv = array_dato($oIfx, $sql, 'id', 'ruc_clpv');

	$sql = "SELECT max (fact_num_preimp) as num_fact from saefact where fact_cod_empr = $idempresa and fact_cod_sucu = $idSucursal";
	$num_fact = consulta_string_func($sql, 'num_fact', $oIfx, 0);
	$sql = "SELECT num_digitos, num_letras, codigo_actual, codigo_automatico,
                dia_corte, dia_cobro, num_digitos
                from isp.int_parametros 
                where id_empresa = $idempresa and
                id_sucursal = $idSucursal";
	if ($oCon->Query($sql)) {
		if ($oCon->NumFilas() > 0) {
			$codigo_automatico = $oCon->f('codigo_automatico');
			$num_digitos = $oCon->f('num_digitos');
			$num_letras = $oCon->f('num_letras');
			$codigo_actual = $oCon->f('codigo_actual');
			$dia_corte = $oCon->f('dia_corte');
			$dia_cobro = $oCon->f('dia_cobro');
		}
	}
	$oCon->Free();

	$fecha_actual = date('Y-m-d');

	//ULTIMO DIA DEL MES
	$u_dia_mes = date("t/m/Y", strtotime($fecha_actual));

	$fecha_actual_p = date("dmYHis");
	$primera_linea = "";
	$primera_linea .= "01,1,5658,5658," . $fecha_actual_p;

	for ($i = 0; $i < count($array); $i++) {
		$data_abonados_e = explode("+", $array[$i]);
		$id 		= $data_abonados_e[0];
		$id_clpv    = $data_abonados_e[1];
		$tarifa 	= $data_abonados_e[2];
		$adeuda 	= $data_abonados_e[3];
		$adeuda 	= round($adeuda, 0);
		$nom_clpv   = $array_nom_clpv[$id];
		$ruc_clpv   = $array_ruc_clpv[$id];
		if ($adeuda > 0) {
			$clv_con_clpv   = $array_clpv_con[$id_clpv];
			if ($clv_con_clpv == '01') {                  // RUC
				$tipoIdentificacionComprador = 'RUT';
			} elseif ($clv_con_clpv == '02') {            // CEDULA
				$tipoIdentificacionComprador = 'CC';
			} elseif ($clv_con_clpv == '03') {            // PASAPORTE
				$tipoIdentificacionComprador = 'PP';
			} elseif ($clv_con_clpv == '07') {            // CONSUMIDOR FINAL
				$tipoIdentificacionComprador = 'CF';
			}
			$data_abonados .= "\n02," . $tipoIdentificacionComprador . "," . $ruc_clpv . "," . $ruc_clpv . "," . $adeuda . "," . $u_dia_mes . "," . $nom_clpv;

			$num_abonados++;
			$tot_deuda += $adeuda;
		}
	}

	$tot_deuda 	= round($tot_deuda, 0);

	$ultima_linea = "";
	$ultima_linea .= "\n09," . $num_abonados . "," . $tot_deuda;
	$data_archivo = "";
	$data_archivo .= $primera_linea;
	$data_archivo .= $data_abonados;
	$data_archivo .= $ultima_linea;

	return $data_archivo;
}

function formatoAvisoPse($array, $idTipoAviso, $idempresa, $idSucursal, $oCon, $oIfx, $obs, $fecha)
{
	$sql = "SELECT empr_nom_empr, empr_ruc_empr, empr_path_logo, empr_cod_pais, empr_mai_empr
			from saeempr where empr_cod_empr = $idempresa ";
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			$razonSocial = trim($oIfx->f('empr_nom_empr'));
			$ruc_empr = $oIfx->f('empr_ruc_empr');
			$empr_path_logo = $oIfx->f('empr_path_logo');
			$empr_cod_pais = $oIfx->f('empr_cod_pais');
			$empr_nom_empr = $oIfx->f('empr_nom_empr');
			$empr_mai_empr = $oIfx->f('empr_mai_empr');
		}
	}
	$oIfx->Free();

	$sql = "SELECT id, nom_clpv, ruc_clpv, direccion, fecha_c_pago, monto_pago, codigo from isp.contrato_clpv";
	$array_nom_clpv = array_dato($oIfx, $sql, 'id', 'nom_clpv');
	$array_ruc_clpv = array_dato($oIfx, $sql, 'id', 'ruc_clpv');
	$array_dir_clpv = array_dato($oIfx, $sql, 'id', 'direccion');
	$array_fec_c_pago_clpv = array_dato($oIfx, $sql, 'id', 'fecha_c_pago');
	$array_monto_pago = array_dato($oIfx, $sql, 'id', 'monto_pago');
	$array_codigo = array_dato($oIfx, $sql, 'id', 'codigo');

	$sql = "SELECT id, mes, anio from isp.contrato_pago";
	$array_cuota_mes = array_dato($oIfx, $sql, 'id', 'mes');
	$array_cuota_anio = array_dato($oIfx, $sql, 'id', 'anio');

	$sql = "SELECT codigo, mes from comercial.mes";
	$array_meses = array_dato($oIfx, $sql, 'codigo', 'mes');

	$sql = "select sucu_cod_sucu, sucu_cod_agen from saesucu;";
	$array_cod_agen = array_dato($oIfx, $sql, 'sucu_cod_sucu', 'sucu_cod_agen');

	$path_img = explode("/", $empr_path_logo);
	$count = count($path_img) - 1;

	$path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

	for ($i = 0; $i < count($array); $i++) {
		$formato = '';

		$sql = "SELECT formato FROM isp.int_aviso_cobro_formato WHERE id = $idTipoAviso";
		if ($oIfx->Query($sql)) {
			if ($oIfx->NumFilas() > 0) {
				$formato = $oIfx->f('formato', false);
			}
		}
		$oIfx->Free();

		$data_abonados_e = explode("+", $array[$i]);

		$id 		= $data_abonados_e[0];
		$id_clpv    = $data_abonados_e[1];
		$tarifa 	= $data_abonados_e[2];
		$adeuda 	= $data_abonados_e[3];
		$det_meses	= $data_abonados_e[4];

		$nom_clpv   = $array_nom_clpv[$id];
		$ruc_clpv   = $array_ruc_clpv[$id];
		$direccion  = $array_dir_clpv[$id];

		$adeuda     = number_format($adeuda, 2);

		if ($adeuda > 0) {
			$fecha_u_pago   = $array_fec_c_pago_clpv[$id];
			$valor_u_pago 	= $array_monto_pago[$id];
			$codigo_c		= $array_codigo[$id];

			$cod_agen_sucu    = $array_cod_agen[$idSucursal];
			$generado_codigo = $cod_agen_sucu . '' . $codigo_c;


			$fecha_actual 	= date('Y-m-d');
			$fecha_ym		= date('Y-m');
			$mes_actual		= date('m');

			$mes_actual     = str_pad($mes_actual, 4, "0", STR_PAD_LEFT);

			$nom_clpv = trim($nom_clpv);
			$nom_clpv = html_entity_decode($nom_clpv);

			if (strlen($fecha_u_pago) > 0) {
				$fecha_u_pago = $fecha_u_pago . " Valor: " . $valor_u_pago;
			}

			$formato = preg_replace("/%ruta_logo%/", $path_logo_img, $formato);
			$formato = preg_replace("/%nom_cliente%/", $nom_clpv, $formato);
			$formato = preg_replace("/%direccion%/", $direccion, $formato);
			$formato = preg_replace("/%ultimo_pago%/", $fecha_u_pago, $formato);
			$formato = preg_replace("/%fecha%/", $fecha_actual, $formato);
			$formato = preg_replace("/%codigo_mes%/", $mes_actual, $formato);
			$formato = preg_replace("/%codigo_pse%/", $generado_codigo, $formato);

			$data_meses = explode("%", $det_meses);

			$data_concepto_valor = '';

			for ($j = 0; $j < count($data_meses); $j++) {
				$data_cod = explode("=", $data_meses[$j]);

				$id_mes = $data_cod[0];
				$deuda_mes = $data_cod[1];

				$mes_num = $array_cuota_mes[$id_mes];
				$anio_num = $array_cuota_anio[$id_mes];

				$nombre_mes = $array_meses[$mes_num];

				$det_mes = $nombre_mes . "/" . $anio_num;

				if (strlen($det_mes) == 1) {
					$det_mes = '';
				}

				$data_concepto_valor .= '<tr>
											<td style="width: 50%;">' . $det_mes . '</td>
											<td style="width: 50%;">' . $deuda_mes . '</td>
										</tr>';
			}

			$observaciones_d = 'SR. USUARIO PUEDES REALIZAR TU PAGO INGRESANDO AL LINK CON EL CODIGO PSE QUE APARECE EN TU FACTURA';

			$formato = preg_replace("/%detalles_concepto%/", $data_concepto_valor, $formato);
			$formato = preg_replace("/%observaciones%/", $obs, $formato);
			$formato = preg_replace("/%total_deuda%/", $adeuda, $formato);
			$formato = preg_replace("/%codigo%/", $codigo_c, $formato);
			$formato = preg_replace("/%fecha_mes_anio%/", $fecha, $formato);

			$documento1 .= '<STYLE>
								H1.SaltoDePagina
								{
									PAGE-BREAK-AFTER: always
								}
							</STYLE>';
			$documento1 .= $formato;
			$documento1 .= '<H1 class=SaltoDePagina> </H1>';
		}
	}



	return $documento1;
}
