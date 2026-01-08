<?php
include_once('WhatsAppConnection.class.php');

class NotificacionesCompras {
    private $oCon; // Objeto de conexión a la base de datos
    private $oConA; // Objeto de conexión a la base de datos
    private $oReturn; // Objeto para manejar las respuestas
    private $S_PAIS_API_SRI; // Código de país
    private $idempresa; // ID de la empresa
    private $idsucursal; // ID de la sucursal
    private $ruta; // Ruta de archivos adjuntos
    private $cod_apro; // Código del aprobador
    private $secuencial; // Secuencial de la solicitud de compra o Proforma
    private $area; // Área de la solicitud
    private $asunto; // Asunto del correo
    private $correo_sn; // Activación envío de correos
    private $whatsap_sn; // Activación envío de whatsapp
    private $nombre_empresa; // Nombre de la empresa

    // Constructor
    public function __construct($oCon, $oConA, $oReturn, $idempresa, $idsucursal, $cod_apro = 'NULL', $secuencial = '', $area = '', $ruta = '') {
        $this->oCon = $oCon;
        $this->oConA = $oConA;
        $this->oReturn = $oReturn;
        $this->S_PAIS_API_SRI = $_SESSION['S_PAIS_API_SRI']; // Código del país
        $this->idempresa = $idempresa;
        $this->idsucursal = $idsucursal;
        $this->ruta = $ruta;
        $this->cod_apro = $cod_apro;
        $this->secuencial = $secuencial;
        $this->area = $area;
        
        // Cargar configuración de los aprobadores
        $this->cargarConfiguracionAprobadores();

        // Cargar configuración de la empresa
        $this->cargarConfiguracionEmpresa();

    }

    //Cragra datos de la empresa desde la base de datos
    private function cargarConfiguracionEmpresa(){
        $sql = "SELECT empr_cod_empr, empr_nom_empr, empr_whatsapp_sn, empr_whatsapp_url
                    FROM saeempr 
                    WHERE empr_cod_empr = $this->idempresa";
        if ($this->oCon->Query($sql)) {
            if ($this->oCon->NumFilas() > 0) {
                do {

                    $this->nombre_empresa = $this->oCon->f('empr_nom_empr');

                } while ($this->oCon->SiguienteRegistro());
            }
        }
        $this->oCon->Free();
    }

    // Cargar configuración de los aprobadores desde la base de datos
    private function cargarConfiguracionAprobadores() {
        $sql = "SELECT envio_email_sn, envio_whts_sn, nombre FROM comercial.aprobaciones_compras WHERE empresa = $this->idempresa AND id = $this->cod_apro";
        if ($this->oCon->Query($sql)) {
            if ($this->oCon->NumFilas() > 0) {
                do {
                    $this->correo_sn = $this->oCon->f('envio_email_sn');
                    $this->whatsap_sn = $this->oCon->f('envio_whts_sn');
                    $this->asunto = $this->oCon->f('nombre');
                } while ($this->oCon->SiguienteRegistro());
            }
        }
        $this->oCon->Free();
    }

    // Método para enviar mensaje de correo al solicitante
    public function enviarCorreoSolicitante($mail) {
        if ($this->correo_sn == 'S') {
            // Enviar correo al solicitante
            if (!empty($mail)) {
                $mensaje = 'Su solicitud fue generada con éxito';
                $array_sol = array($mail);
                $correoMsj = correo_compras_general($mail, $array_sol, $mensaje, $this->area, $this->ruta, intval($this->secuencial), 'Solicitud de Compra');
                //$this->oReturn->alert($correoMsj);
                $this->oReturn->script("alertSwal('$correoMsj', 'success');");
            } else {
                $this->oReturn->alert('USUARIO NO TIENE REGISTRADO UN CORREO');
            }
        }
    }

    // Método para enviar correos a los aprobadores
    public function enviarCorreoAprobadores($mensaje) {
        if ($this->correo_sn == 'S') {
            //$mensaje = 'Se ha generado la siguiente solicitud <b>N. ' . $this->secuencial . '</b><br> Requiere su revisión y aprobación';
            $array_sol = $this->obtenerCorreosAprobadores();

            if (count($array_sol) > 0) {
                // Enviar correo
                $correoMsj = correo_compras_general('', $array_sol, $mensaje, $this->area, $this->ruta, intval($this->secuencial), $this->asunto);
                //$this->oReturn->alert($correoMsj);
                $this->oReturn->script("alertSwal('$correoMsj', 'success', { timer: 3000, showConfirmButton: false });");
            }
        }
    }

    // Enviar mensaje de WhatsApp al solicitante
    public function enviarWhatsAppSolicitante($cel_user) {
        if ($this->whatsap_sn == 'S') {
            if (empty($this->S_PAIS_API_SRI)) {
                $this->oReturn->alert("Código de País no configurado, diríjase al módulo de la parte derecha Geografía/Paises");
            } elseif (empty($cel_user)) {
                $this->oReturn->alert("Usuario no tiene configurado un número de teléfono, diríjase al módulo de la parte derecha Sistemas /Usuarios");
            } else {
                $cel_user = $this->formatearNumeroTelefono($cel_user);
                $text_envio = 'Su solicitud fue generada con éxito';

                $whatsapp_send = new WhatsAppConnection($this->oCon, $this->idempresa);
                $result = $whatsapp_send->verificarSessionWhatsApp();
                if (($result['status'] === WhatsAppConnection::VERIFICACION_CONECTADO) && $cel_user) {
                    if ($text_envio) {
                        $media_pdf = mime_content_type($this->ruta);
                        $media = new \CurlFile($this->ruta, $media_pdf);
                        $whatsapp_send->enviarMensajeWhatsApp($cel_user, $text_envio, $media);
                    }
                    $this->oReturn->alert('Mensaje Enviado a: '.$cel_user);
                    //$this->oReturn->script("alertSwal('Mensaje Enviado a: $cel_user', 'success', { timer: 3000, showConfirmButton: false });");
                } else {
                    $this->oReturn->script("alertSwal('Sin Session Activa', 'warning');");
                }
            }
        }
    }

    // Enviar mensaje de WhatsApp a los aprobadores
    public function enviarWhatsAppAprobadores($text_envio) {
        if ($this->whatsap_sn == 'S') {
            //$text_envio = 'Se ha generado la siguiente solicitud *N. ' . $this->secuencial . '*\nRequiere su revisión y aprobación';
            $array_sol = $this->obtenerNumerosAprobadores();

            if (count($array_sol) > 0) {
                foreach ($array_sol as $cel_user) {
                    $cel_user = $this->formatearNumeroTelefono($cel_user);
                    $whatsapp_send = new WhatsAppConnection($this->oCon, $this->idempresa);
                    $result = $whatsapp_send->verificarSessionWhatsApp();

                    if (($result['status'] === WhatsAppConnection::VERIFICACION_CONECTADO) && $cel_user) {
                        if ($text_envio) {
                            if(!empty($this->ruta)){
                                $media_pdf = mime_content_type($this->ruta);
                                $media = new \CurlFile($this->ruta, $media_pdf);
                                $whatsapp_send->enviarMensajeWhatsApp($cel_user, $text_envio, $media);
                            }
                            else{
                                $whatsapp_send->enviarMensajeWhatsApp($cel_user, $text_envio, null);
                            }
                            
                        }
                        $this->oReturn->alert('Mensaje Enviado a: '.$cel_user);
                        //$this->oReturn->script("alertSwal('Mensaje Enviado a: $cel_user', 'success', { timer: 3000, showConfirmButton: false });");
                    } else {
                        $this->oReturn->alert('Sin Session Activa');
                        //$this->oReturn->script("alertSwal('Sin Session Activa', 'warning');");
                    }
                }
            }
        }
    }

    // Obtener los correos de los aprobadores
    private function obtenerCorreosAprobadores() {
        $array_sol = array();
        $cod_apro = '"' . $this->cod_apro . '"';
        $sql = "SELECT usuario_email FROM comercial.usuario WHERE usuario_activo = 'S' AND aprobaciones_compras::jsonb @> '[$cod_apro]'::jsonb;";

        if ($this->oCon->Query($sql)) {
            if ($this->oCon->NumFilas() > 0) {
                do {
                    $mail = trim($this->oCon->f('usuario_email'));
                    if (!empty($mail)) {
                        array_push($array_sol, $mail);
                    }
                    else{
                        $this->oReturn->alert('USUARIO NO TIENE REGISTRADO UN CORREO'); 
                    }
                } while ($this->oCon->SiguienteRegistro());
            }
        }
        $this->oCon->Free();
        return $array_sol;
    }

    // Obtener los números de teléfono de los aprobadores
    private function obtenerNumerosAprobadores() {
        $array_sol = array();
        $cod_apro = '"' . $this->cod_apro . '"';
        $sql = "SELECT usuario_movil FROM comercial.usuario WHERE usuario_activo = 'S' AND aprobaciones_compras::jsonb @> '[$cod_apro]'::jsonb;";

        if ($this->oCon->Query($sql)) {
            if ($this->oCon->NumFilas() > 0) {
                do {
                    $cel_user = trim($this->oCon->f('usuario_movil'));
                    if (!empty($cel_user)) {
                        array_push($array_sol, $cel_user);
                    }
                    else{
                        $this->oReturn->alert('USUARIO NO TIENE REGISTRADO UN NUMERO'); 
                    }
                } while ($this->oCon->SiguienteRegistro());
            }
        }
        $this->oCon->Free();
        return $array_sol;
    }

    // Formatear el número de teléfono
    private function formatearNumeroTelefono($cel_user) {
        if ($this->S_PAIS_API_SRI == '593') {
            return $this->S_PAIS_API_SRI . substr($cel_user, 1);  // Reemplaza el 0 por 593
        } else {
            return $this->S_PAIS_API_SRI . $cel_user;
        }
    }


    // Método para enviar correos a los proveedores
    public function enviarCorreoProveedores($mail_solicitante, $clpv_cod) {
        if ($this->correo_sn == 'S') {

            //VALIDACION MODULO PRECIOS DE PROFORMA
            $fil_prov='';
            $fil_est='';
            if(!empty($clpv_cod)){//MODULO PRECIOS DE PROFORMA
                $fil_prov="and d.invpd_cod_clpv=$clpv_cod";
                //VALIDACION ORDEN DE COMPRA
                $sqlord="SELECT COUNT(*)as cont from comercial.inv_proforma i, comercial.inv_proforma_det d where
                i.id_inv_prof   	= d.id_inv_prof and
                i.invp_cod_empr 	= $this->idempresa and
                i.invp_cod_sucu 	= $this->idsucursal and
                i.invp_num_invp 	= '$this->secuencial' 
                and d.invpd_cod_clpv 	= $clpv_cod and
                d.invpd_esta_invpd ='S' and d.invpd_num_secu is not null";
                $ctrl_ord = consulta_string($sqlord, 'cont', $this->oCon, 0);

                if($ctrl_ord != 0){
                    $fil_est="and d.invpd_esta_invpd ='S'";
                }
            }

            // UNIDAD
        $sql = "select unid_cod_unid, unid_nom_unid from saeunid where unid_cod_empr = $this->idempresa";
        unset($array_unid);
        $array_unid = array_dato($this->oCon, $sql, 'unid_cod_unid', 'unid_nom_unid');


				$sql = "SELECT d.invpd_cod_clpv, d.invpd_nom_clpv, d.invpd_ema_clpv
							from comercial.inv_proforma i, comercial.inv_proforma_det d where
							i.id_inv_prof   	= d.id_inv_prof and
							i.invp_cod_empr 	= $this->idempresa and
							i.invp_cod_sucu 	= $this->idsucursal and
							i.invp_num_invp 	= '$this->secuencial'
                            $fil_prov 
							group by 1,2,3	order by 1 ";
				if ($this->oCon->Query($sql)) {
					if ($this->oCon->NumFilas() > 0) {
						do {
							$clpv_cod 	= $this->oCon->f('invpd_cod_clpv');
							$clpv_nom 	= $this->oCon->f('invpd_nom_clpv');
							$clpv_correo= $this->oCon->f('invpd_ema_clpv');
							
							$tabla = '';
							$tabla .= '<table border="1" class="table table-striped table-bordered table-hover table-condensed" style="width: 90%; margin-bottom: 0px;" align="center" >';
							$tabla .= '<tr>';
							$tabla .= '<td>N.-</td>';
							$tabla .= '<td>Codigo</td>';
							$tabla .= '<td>Producto</td>';
							$tabla .= '<td>Detalle</td>';
							$tabla .= '<td>Unidad</td>';
							$tabla .= '<td>Cantidad</td>';
							$tabla .= '</tr>';
							
							$sql = "SELECT d.invpd_cod_clpv, d.invpd_nom_clpv, d.invpd_ema_clpv,
							i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, i.invp_unid_cod,
							d.invpd_cun_dmov, d.invpd_cant_dmov,  i.id_inv_prof ,  d.id_inv_dprof,
							i.invp_cant_real, invp_det_prod, d.invpd_num_secu
							from comercial.inv_proforma i, comercial.inv_proforma_det d where
							i.id_inv_prof   	= d.id_inv_prof and
							i.invp_cod_empr 	= $this->idempresa and
							i.invp_cod_sucu 	= $this->idsucursal aND
							i.invp_num_invp 	= '$this->secuencial' and
							d.invpd_cod_clpv 	= $clpv_cod $fil_est";

							$x = 1;
							$total_minv = 0;
							
							if ($this->oConA->Query($sql)) {
								if ($this->oConA->NumFilas() > 0) {
									do {
										$bode_cod 	= $this->oConA->f('invp_cod_bode');
										$prod_cod 	= $this->oConA->f('invp_cod_prod');
										$prod_nom   = $this->oConA->f('invp_nom_prod');
										$unid_cod   = $this->oConA->f('invp_unid_cod');
										$costo      = $this->oConA->f('invpd_cun_dmov');
										$cantidad   = $this->oConA->f('invp_cant_real');
										$invp_det_prod   = $this->oConA->f('invp_det_prod');
                                        $secu_minv   = $this->oConA->f('invpd_num_secu');
										
										$tabla .= '<tr>';
										$tabla .= '<td>'.$x.'</td>';
										$tabla .= '<td>'.$prod_cod.'</td>';
										$tabla .= '<td>'.$prod_nom.'</td>';
										$tabla .= '<td>'.$invp_det_prod.'</td>';
										$tabla .= '<td>'.$array_unid[$unid_cod].'</td>';
										$tabla .= '<td>'.$cantidad.'</td>';
										$tabla .= '</tr>';
							
										$x++;
										
									} while ($this->oConA->SiguienteRegistro());
									$tabla .= '</table>';
								}
							}// fin
                            $this->oConA->Free();				
							
							
							// VALIDACION DE CORREOS

							// ENVIO DE CORREOS
							if(!empty($clpv_correo)){

								$array_prove=array();

                                //VALIDAICON ORDEN DE COMPRA
                                if(!empty($secu_minv)){
                                    $asunto='Cotizacion de Compra';
                                    $mensaje="Estimado Proveedor,<br><br><br>
                                    El motivo de la presente es para solicitarle  de la manera mas cordial la siguiente 
                                    Orden de Compra Nro: $secu_minv<br>$tabla<br><br><br>
                                    Recibe un cordial saludo,<br><b>Departamento de Compras</b><br><b>$this->nombre_empresa</b>";
                                }
                                else{
                                    $asunto='Orden de Compra';
                                    $mensaje="Estimado Proveedor,<br><br><br>
                                    El motivo de la presente es para solicitarle  de la manera mas cordial la siguiente 
                                    Cotizacion de Compra Nro: $this->secuencial <br>$tabla<br><br><br>
                                    Recibe un cordial saludo,<br><b>Departamento de Compras</b><br><b>$this->nombre_empresa</b>";
                                }
								
								
                                array_push($array_prove,$clpv_correo);
								$correoMsj = correo_compras_general($mail_solicitante, $array_prove, $mensaje, '', $this->ruta, intval($this->secuencial),$asunto);
								$this->oReturn->alert($correoMsj);
                                //$this->oReturn->script("alertSwal('$correoMsj', 'success');");
							}
							else{
								$this->oReturn->alert('PROVEEDOR NO TIENE REGISTRADO UN CORREO'); 
							}

							
						} while ($this->oCon->SiguienteRegistro());
					}
				}// fin
                $this->oCon->Free();
            
        }
    }
    // Método para enviar correos a los proveedores
    public function enviarWhatsAppProveedores($clpv_cod) {
        if ($this->whatsap_sn == 'S') {

            //VALIDACION MODULO PRECIOS DE PROFORMA
            $fil_prov='';
            $fil_est='';
            if(!empty($clpv_cod)){//MODULO PRECIOS DE PROFORMA
                $fil_prov="and d.invpd_cod_clpv=$clpv_cod";
                //VALIDACION ORDEN DE COMPRA
                $sqlord="SELECT COUNT(*)as cont from comercial.inv_proforma i, comercial.inv_proforma_det d where
                i.id_inv_prof   	= d.id_inv_prof and
                i.invp_cod_empr 	= $this->idempresa and
                i.invp_cod_sucu 	= $this->idsucursal and
                i.invp_num_invp 	= '$this->secuencial' 
                and d.invpd_cod_clpv 	= $clpv_cod and
                d.invpd_esta_invpd ='S' and d.invpd_num_secu is not null";
                $ctrl_ord = consulta_string($sqlord, 'cont', $this->oCon, 0);

                if($ctrl_ord != 0){
                    $fil_est="and d.invpd_esta_invpd ='S'";
                }
            }

            // UNIDAD
        $sql = "select unid_cod_unid, unid_nom_unid from saeunid where unid_cod_empr = $this->idempresa";
        unset($array_unid);
        $array_unid = array_dato($this->oCon, $sql, 'unid_cod_unid', 'unid_nom_unid');


				$sql = "SELECT d.invpd_cod_clpv, d.invpd_nom_clpv, d.invpd_ema_clpv, d.invpd_movil_clpv
							from comercial.inv_proforma i, comercial.inv_proforma_det d where
							i.id_inv_prof   	= d.id_inv_prof and
							i.invp_cod_empr 	= $this->idempresa and
							i.invp_cod_sucu 	= $this->idsucursal and
							i.invp_num_invp 	= '$this->secuencial'
                            $fil_prov 
							group by 1,2,3,4	order by 1 ";
				if ($this->oCon->Query($sql)) {
					if ($this->oCon->NumFilas() > 0) {
						do {
							$clpv_cod 	= $this->oCon->f('invpd_cod_clpv');
							$clpv_nom 	= $this->oCon->f('invpd_nom_clpv');
							$clpv_correo= $this->oCon->f('invpd_ema_clpv');
                            $cel_prove= $this->oCon->f('invpd_movil_clpv');
							
							$tabla = '';
							
							$sql = "SELECT d.invpd_cod_clpv, d.invpd_nom_clpv, d.invpd_ema_clpv,
							i.invp_cod_bode, i.invp_cod_prod, i.invp_nom_prod, i.invp_unid_cod,
							d.invpd_cun_dmov, d.invpd_cant_dmov,  i.id_inv_prof ,  d.id_inv_dprof,
							i.invp_cant_real, invp_det_prod, d.invpd_num_secu
							from comercial.inv_proforma i, comercial.inv_proforma_det d where
							i.id_inv_prof   	= d.id_inv_prof and
							i.invp_cod_empr 	= $this->idempresa and
							i.invp_cod_sucu 	= $this->idsucursal and
							i.invp_num_invp 	= '$this->secuencial' and
							d.invpd_cod_clpv 	= $clpv_cod $fil_est";
							$x = 1;
							$total_minv = 0;
							
							if ($this->oConA->Query($sql)) {
								if ($this->oConA->NumFilas() > 0) {
									do {
										$bode_cod 	= $this->oConA->f('invp_cod_bode');
										$prod_cod 	= $this->oConA->f('invp_cod_prod');
										$prod_nom   = $this->oConA->f('invp_nom_prod');
										$unid_cod   = $this->oConA->f('invp_unid_cod');
										$costo      = $this->oConA->f('invpd_cun_dmov');
										$cantidad   = $this->oConA->f('invp_cant_real');
										$invp_det_prod   = $this->oConA->f('invp_det_prod');
                                        $secu_minv   = $this->oConA->f('invpd_num_secu');

                                        $tabla .= "\n\n*Nro:* ".$x;
                                        $tabla .= "\n*Codigo:* ".$prod_cod;
                                        $tabla .= "\n*Producto:* ".$prod_nom;
                                        $tabla .= "\n*Detalle:* ".$invp_det_prod;
                                        $tabla .= "\n*Unidad:* ".$array_unid[$unid_cod];
                                        $tabla .= "\n*Cantidad:* ".$cantidad;

										$x++;
									} while ($this->oConA->SiguienteRegistro());
								}
							}// fin
                            $this->oConA->Free();				
							
							
							// VALIDACION NUMERO DE CELULAR
							if(!empty($cel_prove)){
                                
                                 //VALIDAICON ORDEN DE COMPRA
                                 if(!empty($secu_minv)){
                                    $text_envio="Estimado Proveedor,\n\n\nEl motivo de la presente es para solicitarle  de la manera mas cordial la siguiente Orden de Compra Nro: $secu_minv\n$tabla\n\nRecibe un cordial saludo,\n*Departamento de Compras*\n*$this->nombre_empresa*";
                                 }
                                 else{
    								$text_envio="Estimado Proveedor,\n\n\nEl motivo de la presente es para solicitarle  de la manera mas cordial la siguiente Cotizacion de Compra Nro: $this->secuencial\n$tabla\n\nRecibe un cordial saludo,\n*Departamento de Compras*\n*$this->nombre_empresa*";
                                 }

                                $cel_prove = $this->formatearNumeroTelefono($cel_prove);
                                $whatsapp_send = new WhatsAppConnection($this->oConA, $this->idempresa);
                                $result = $whatsapp_send->verificarSessionWhatsApp();

                                if (($result['status'] === WhatsAppConnection::VERIFICACION_CONECTADO) && $cel_prove) {
                                    if ($text_envio) {

                                        if(!empty($this->ruta)){
                                            $media_pdf = mime_content_type($this->ruta);
                                            $media = new \CurlFile($this->ruta, $media_pdf);
                                            $whatsapp_send->enviarMensajeWhatsApp($cel_prove, $text_envio, $media);
                                        }
                                        else{
                                            $whatsapp_send->enviarMensajeWhatsApp($cel_prove, $text_envio, null);
                                        }
                                    }
                                    $this->oReturn->alert('Mensaje Enviado a: '.$cel_prove);
                                    //$this->oReturn->script("alertSwal('Mensaje Enviado a: $cel_prove', 'success');");
                                } else {
                                    $this->oReturn->alert('Sin Session Activa');
                                    //$this->oReturn->script("alertSwal('Sin Session Activa', 'warning');");
                                }
							}
							else{
								$this->oReturn->alert('PROVEEDOR NO TIENE REGISTRADO UN NUMERO DE CELULAR'); 
							}

							
						} while ($this->oCon->SiguienteRegistro());
					}
				}// fin
                $this->oCon->Free();
            
        }
    }


}//Cierre Clase NotificacionesCompras

?>
