<? 

function reporte_guia_personalizado($id = '', $nombre_archivo = '', $idSucursal = '', &$rutaPdf = '')
{

global $DSN_Ifx, $DSN;
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();

$oIfx2 = new Dbo;
$oIfx2->DSN = $DSN_Ifx;
$oIfx2->Conectar();

$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();


$idEmpresa = $_SESSION['U_EMPRESA'];
//$idSucursal = $_SESSION['U_SUCURSAL'];


$sql = "select empr_sn_conta, empr_ac2_empr, empr_rimp_sn,empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo
                                        from saeempr where empr_cod_empr = $idEmpresa ";
if ($oIfx->Query($sql)) {
    if ($oIfx->NumFilas() > 0) {
        $razonSocial = trim($oIfx->f('empr_nom_empr'));
        $ruc_empr = $oIfx->f('empr_ruc_empr');
        $dirMatriz = trim($oIfx->f('empr_dir_empr'));
        $empr_path_logo = $oIfx->f('empr_path_logo');
        if ($oIfx->f('empr_conta_sn') == 'S')
            $empr_conta_sn = 'SI';
        else
            $empr_conta_sn = 'NO';

        $empr_num_resu = $oIfx->f('empr_num_resu');
        $empr_ac2_empr = trim($oIfx->f('empr_ac2_empr'));
        $empr_rimp_sn = $oIfx->f('empr_rimp_sn');
        $empr_sn_conta = $oIfx->f('empr_sn_conta');
    }
}
$oIfx->Free();

//  AMBIENTE - EMISION
$sql = "select sucu_tip_ambi, sucu_tip_emis, sucu_dir_sucu  
        from saesucu 
        where sucu_cod_empr = $idEmpresa and 
        sucu_cod_sucu = $idSucursal ";
if ($oIfx->Query($sql)) {
    if ($oIfx->NumFilas() > 0) {
        $ambiente_sri = $oIfx->f('sucu_tip_ambi');
        $emision_sri = $oIfx->f('sucu_tip_emis');
        $sucu_dir = $oIfx->f('sucu_dir_sucu');
    }
}
$oIfx->Free();


//VALIDACION SUSCURSALES

$sqls = "select count(*) as cont from saesucu";
$contsucu = consulta_string($sqls, 'cont', $oIfx, 0);

if ($ambiente_sri == 1) {
    $ambiente_sri = 'PRUEBAS';
} elseif ($ambiente_sri == 2) {
    $ambiente_sri = 'PRODUCCION';
}

if ($emision_sri == 1) {
    $emision_sri = 'NORMAL';
} elseif ($emision_sri == 2) {
    $emision_sri = 'POR INDISPONIBLIDAD DEL SISTEMA';
}



$path_img = explode("/", $empr_path_logo);
$count = count($path_img) - 1;
//$path_logo_img = DIR_FACTELEC . 'imagenes/logos/' . $path_img[$count];
$path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];


$rutaImagen =  $_SERVER['DOCUMENT_ROOT'] . "/" . DIR_INCLUDE . 'Clases/Formulario/plugins/reloj/' . basename($empr_path_logo);

$emprce = "select empr_num_resu,empr_nom_empr from saeempr where empr_cod_empr=$idEmpresa";
$contribuyente = consulta_string($emprce, 'empr_num_resu', $oIfx, '');
$razonSocial = consulta_string($emprce, 'empr_nom_empr', $oIfx, '');
$logo .= '<table style="width: 100%; margin: 0px;">';
$logo .= '<tr>';
$logo .= '<b><td align="center" style="width: 50%; border:1px solid black; border-radius: 5px; margin: 0px;">';
$logo .= '<table align="center" style="margin: 0px;">';
$logo .= '<tr><td style="margin-top: 0px;"><img width="250px;"  src="' . $path_logo_img . '"></td></tr>';
$logo .= '<tr><td>&nbsp;</td></tr>';
$logo .= '<tr><td align="center" style="font-size: 16px; white-space:nowrap;" width="480" >' . $razonSocial . '</td></tr>';

$sqlxml = "select ixml_tit_ixml, ixml_det_ixml from saeixml where ixml_cod_empr=$idEmpresa 
            and ixml_est_deleted ='S' and ixml_sn_pdf='S' order by ixml_ord_ixml";

if ($oIfx->Query($sqlxml)) {
    if ($oIfx->NumFilas() > 0) {
        do {
            $titulo  = $oIfx->f('ixml_tit_ixml');
            $detalle = $oIfx->f('ixml_det_ixml');
            $logo .= '<tr><td align="center" style="font-size: 14px;" width="300">' . $detalle . '</td></tr>';
        } while ($oIfx->SiguienteRegistro());
    }
}
$oIfx->Free();

//$logo .= '<tr><td>&nbsp;</td></tr>';
$logo .= '<tr><td align="center" style="font-size: 16px;">RUC : ' . $ruc_empr . '</td></tr>';
$logo .= '<tr><td>&nbsp;</td></tr>';

//selecciona sucursales y direcciones
$sql_sucu_matriz = "select sucu_dir_sucu from saesucu where sucu_nom_sucu ='MATRIZ'";
$matriz = consulta_string($sql_sucu_matriz, 'sucu_dir_sucu', $oIfx, '');
$sql_sucu = "select sucu_nom_sucu, sucu_dir_sucu from saesucu where sucu_cod_empr = $idEmpresa and sucu_cod_sucu = $idSucursal ";
$emprce = "select empr_num_resu from saeempr where empr_cod_empr=$idEmpresa";
$contribuyente = consulta_string($emprce, 'empr_num_resu', $oIfx, '');
if ($oIfx->Query($sql_sucu)) {
    if ($oIfx->NumFilas() > 0) {
        do {
            $sucu_nom_sucu = $oIfx->f('sucu_nom_sucu');
            $sucu_dir_sucu = $oIfx->f('sucu_dir_sucu');

            //$logo .= '<tr><td align="center" style="font-size: 12px">' . $sucu_nom_sucu . ': ' . htmlentities($sucu_dir_sucu) . '</td></tr>';
            //$logo .= '<tr><td align="center" style="font-size: 13px">SUCURSAL : ' . $sucu_nom_sucu . '</td></tr>';

            $logo .= '<tr><td style="position:top; " width="480"><h4>Direccion Matriz:</h4></td></tr><br>';
            $logo .= '<tr><td width="480">' . htmlentities($dirMatriz) . '</td></tr>';
            if ($contsucu > 1) {
                $logo .= '<tr><td align="center" style="font-size: 13px" width="480">SUCURSAL : ' . $sucu_nom_sucu . '</td></tr>';

                $logo .= '<tr><td width="480"><h4>Direccion Sucursal:</h4>' . htmlentities($sucu_dir_sucu) . '</td></tr>';
            }


            if ($empr_conta_sn == 'SI') {
                $logo .= '<tr><td> Contribuyente Especial:' . $contribuyente . '</td></tr>';
            }
        } while ($oIfx->SiguienteRegistro());
    }
}
$logo .= '<tr><td>&nbsp;</td></tr>';

if (!empty($empr_num_resu)) {
    $logo .= '<tr><td align="center" style="font-size: 12px;"><b>Contribuyente Especial #:</b> ' . $empr_num_resu . '</td></tr>';
}

//$logo .= '<tr><td align="center" style="font-size: 12px;">Contribuyente Especial #:' . $empr_num_resu . '</td></tr>';
if ($empr_conta_sn == 'SI') {
    if ($empr_sn_conta == 'S') {
        $empr_sn_conta = 'SI';
    } else {
        $empr_sn_conta = 'NO';
    }
    $logo .= '<tr><td align="center" style="font-size: 12px;">Obligado a llevar Contabilidad :' . $empr_sn_conta . '</td></tr>';
}
if ($empr_rimp_sn == "S") {
    $logo .= '<tr><td align="center" style="font-size: 12px;"><b>CONTRIBUYENTE R&Eacute;GIMEN RIMPE</b></td></tr>';
}

if (!empty($empr_ac2_empr)) {
    $logo .= '<tr><td align="center" style="font-size: 12px;">Agente de Retención Resolución No. ' . $empr_ac2_empr . '</td></tr>';
}
$logo .= ' </table>';
$logo .= '</td></b>';

$sqlFac = "select * from saeguia where guia_cod_guia= $id and guia_cod_sucu = $idSucursal and guia_cod_empr = $idEmpresa ";
if ($oIfx->Query($sqlFac)) {
    if ($oIfx->NumFilas() > 0) {
        do {
            $guia_nse_guia = $oIfx->f('guia_nse_guia');
            $guia_num_preimp = $oIfx->f('guia_num_preimp');
            $guia_auto_sri = $oIfx->f('guia_auto_sri');
            $guia_fech_sri = $oIfx->f('guia_fech_sri');
            $guia_nom_cliente = $oIfx->f('guia_nom_cliente');
            $guia_fech_guia = fecha_mysql_func($oIfx->f('guia_fech_guia'));
            $guia_ruc_clie = $oIfx->f('guia_ruc_clie');
            $guia_tlf_cliente = $oIfx->f('guia_tlf_cliente');
            $guia_dir_clie = $oIfx->f('guia_dir_clie');
            $guia_email_clpv = $oIfx->f('guia_email_clpv');
            //$ncre_cod_fact = $oIfx->f('ncre_cod_fact');
            $guia_cm3_guia = $oIfx->f('guia_cm3_guia');
            $guia_cod_trta = $oIfx->f('guia_cod_trta');
            $guia_num_plac = $oIfx->f('guia_num_plac');
            $guia_hos_guia = $oIfx->f('guia_hos_guia');
            $guia_hol_guia = $oIfx->f('guia_hol_guia');
            $guia_clav_sri = $oIfx->f('guia_clav_sri');
            $guia_sal_guia = $oIfx->f('guia_sal_guia');
            $guia_cod_ccli = $oIfx->f('guia_cod_ccli');
            $guia_cod_clpv = $oIfx->f('guia_cod_clpv');
            $guia_cm1_guia = $oIfx->f('guia_cm1_guia');
            $guia_inf_adi = $oIfx->f('guia_inf_adi');

            $guia_nse_fact = $oIfx->f('guia_nse_fact');
            $guia_num_fact = $oIfx->f('guia_num_fact');
            $guia_fech_fact = $oIfx->f('guia_fech_fact');


            $guia_ciu_ori = $oIfx->f('guia_ciu_ori');
            if(empty($guia_ciu_ori)) $guia_ciu_ori='NULL';


            $guia_ciu_des = $oIfx->f('guia_ciu_des');

            $guia_cod_dest = $oIfx->f('guia_cod_dest');
            $guia_doc_adua = $oIfx->f('guia_doc_adua');

            // ciudades
            $sql = "select ciud_cod_ciud, ciud_nom_ciud, ciud_cod_pais, ciud_cod_prov  from saeciud where ciud_cod_ciud = $guia_ciu_ori";
            $ciud_ori = consulta_string($sql, 'ciud_nom_ciud', $oIfx2, '');
            $pais_ori = consulta_string($sql, 'ciud_cod_pais', $oIfx2, 0);
            $prov_ori = consulta_string($sql, 'ciud_cod_prov', $oIfx2, 0);

            //provincia origen
            $sql="select prov_des_prov from saeprov where prov_cod_prov=$prov_ori";
            $provincia_origen = consulta_string($sql, 'prov_des_prov', $oIfx2, '');

            //pais origen
            $sql="select pais_des_pais from saepais where pais_cod_pais=$pais_ori";
            $pais_origen = consulta_string($sql, 'pais_des_pais', $oIfx2, '');

            $sql = "select ciud_cod_ciud, ciud_nom_ciud  from saeciud where ciud_cod_ciud = '$guia_ciu_des' ";
            $ciud_des = consulta_string($sql, 'ciud_nom_ciud', $oIfx2, '');


            $logo .= '<b><td style="width: 49%; border: 1px solid black; border-radius: 5px;" align="center">';
            $logo .= ' <table align="center" style="font-size: 15px;">';
            $logo .= ' <tr>';
            $logo .= '<td style="border-bottom: 1px inset black;">GUIA DE REMISION</td>';
            $logo .= ' </tr>';
            $logo .= ' <tr>';
            $logo .= '<td>SERIE:</td>';
            $logo .= ' </tr>';
            $logo .= ' <tr>';
            $logo .= '<td>' . substr($guia_nse_guia, 0, 3) . '-' . substr($guia_nse_guia, 3, 6) . '</td>';
            $logo .= ' </tr>';
            $logo .= ' <tr>';
            $logo .= '<td style="font-size: 17px; color: red; border-bottom: 1;">' . $guia_num_preimp . '</td>';
            $logo .= ' </tr>';
            $logo .= ' <tr>';
            $logo .= '<td>AUTORIZACION:</td>';
            $logo .= ' </tr>';
            $logo .= ' <tr>';
            $logo .= '<td>' . $guia_auto_sri . '</td>';
            $logo .= ' </tr>';
            $logo .= ' <tr>';
            $logo .= '<td style="border-top: 1;">FECHA AUTORIZACION:</td>';
            $logo .= ' </tr>';
            $logo .= ' <tr>';
            $logo .= '<td>' . $guia_fech_sri . '</td>';
            $logo .= ' </tr>';
            $logo .= ' <tr>';
            $logo .= '<td style="border-top:1">AMBIENTE: </td>';
            $logo .= ' </tr>';
            $logo .= ' <tr>';
            $logo .= ' <td>' . $ambiente_sri . '</td>';
            $logo .= ' </tr>';
            $logo .= ' <tr>';
            $logo .= ' <td style="border-top:1">EMISION: </td>';
            $logo .= ' </tr>';
            $logo .= ' <tr>';
            $logo .= '<td>' . $emision_sri . '</td>';
            $logo .= ' </tr>';

            if ($guia_clav_sri != '') {
                $nombArch = $guia_nse_guia . $guia_num_preimp;
                $rutaCodi = DIR_FACTELEC . 'Include/archivos/' . $nombArch . '.gif';

                new barCodeGenrator($guia_clav_sri, 1, $rutaCodi, 450, 100, true);

                $logo .= '<tr>';
                $logo .= '<td colspan=2 align="right" style="font-size: 12px;">CLAVE DE ACCESO:</td>';
                $logo .= '</tr>';
                $logo .= '<tr>';
                $logo .= '<td colspan=2 align="right"> <img width="350px;" src="' . $rutaCodi . '"/></td>';
                $logo .= '</tr>';
            }

            $logo .= ' </table>';
            $logo .= '</td></b>';
            $logo .= '</tr>';
            $logo .= '</table>';
            $logo .= ' <br>';


            //DATOS DEL TRANSPORTISTA
            if (!empty($guia_cod_trta)) {
                $sqlTran = "select * from saetrta where trta_cod_trta = $guia_cod_trta and trta_cod_empr = $idEmpresa ";
            } else {
                $sqlTran = "select * from saetrta where trta_cid_trta = '$guia_sal_guia' and trta_cod_empr = $idEmpresa ";
            }

            $transportista .= ' <table style="width: 100%; border:1px solid black; border-radius: 5px; padding: 2px; font-size: 15x;">';


            if ($oIfx2->Query($sqlTran)) {
                if ($oIfx2->NumFilas() > 0) {
                    do {
                        //$oReturn->alert('si');
                        $trta_nom_trta = $oIfx2->f("trta_nom_trta");
                        $trta_cid_trta = $oIfx2->f("trta_cid_trta");

                        $transportista .= ' <tr>';
                        $transportista .= ' <td style="width: 30% ">Identificación (Transportista)</td>';
                        $transportista .= ' <td style="width: 70%" colspan="3">' . $trta_cid_trta . '</td>';
                        $transportista .= ' </tr>';

                        $transportista .= ' <tr>';
                        $transportista .= ' <td style="width: 30%">Razón Social / Nombres y Apellidos:</td>';
                        $transportista .= ' <td style="width: 70%" colspan="3">' . $trta_nom_trta . '</td>';
                        $transportista .= ' </tr>';

                        $transportista .= ' <tr>';
                        $transportista .= ' <td style="width: 30% "> Placa:</td>';
                        $transportista .= ' <td style="width: 70% " colspan="3">' . $guia_num_plac . '</td>';
                        $transportista .= ' </tr>';

                        $transportista .= ' <tr>';
                        $transportista .= ' <td style="width: 30% ">Punto de Partida:</td>';
                        $transportista .= ' <td style="width: 70% " colspan="3">' . $ciud_ori . '-'.$provincia_origen.'-'.$pais_origen.'</td>';
                        $transportista .= ' </tr>';

                        $transportista .= ' <tr>';
                        $transportista .= ' <td style="width: 30% "> Fecha Inicio Transporte:</td>';
                        $transportista .= ' <td style="width: 20% ">' . fecha_mysql_func($guia_hos_guia) . '</td>';
                        $transportista .= ' <td style="width: 30% "> Fecha fin Transporte:</td>';
                        $transportista .= ' <td style="width: 20% ">' . fecha_mysql_func($guia_hol_guia) . '</td>';
                        $transportista .= ' </tr>';
                    } while ($oIfx2->SiguienteRegistro());
                }
            }

            $transportista .= ' </table>';
            $transportista .= ' <br>';
        } while ($oIfx->SiguienteRegistro());
    }
}
$oIfx->Free();

if (!empty($guia_cod_ccli)) {
    $sqlCcli = "select ccli_cod_ccli, ccli_nom_conta, ccli_dire_ccli
                from saeccli
                where ccli_cod_clpv = $guia_cod_clpv and
                ccli_cod_ccli = $guia_cod_ccli and
                ccli_cod_empr = $idEmpresa";
    if ($oIfx->Query($sqlCcli)) {
        if ($oIfx->NumFilas() > 0) {
            $ccli_nom_conta = $oIfx->f('ccli_nom_conta');
            $ccli_dire_ccli = $oIfx->f('ccli_dire_ccli');
        }
    }
    $oIfx->Free();
}

$sqlOpc = "select pedf_opc_pedf from saepedf where
           pedf_num_preimp = (select guia_ped_guia from saeguia where guia_cod_guia = $id
           and guia_cod_empr = $idEmpresa and guia_cod_sucu = $idSucursal)
           and pedf_cod_empr = $idEmpresa and pedf_cod_sucu = $idSucursal and
           pedf_cod_clpv = $guia_cod_clpv";
$pedf_opc_pedf = consulta_string($sqlOpc, 'pedf_opc_pedf', $oIfx, '');

//SI L;A CONFIGURACION ES GUIA-FACTURA O PEDIDO-GUIA
$para_sec_para = consulta_string("select para_sec_para from saepara where para_cod_empr = $idEmpresa
                                            and para_cod_sucu =$idSucursal", "para_sec_para", $oIfx2, '');



$ctrl_dest='N';
$destinatario = '';

$destinatario .= ' <table style="width: 100%; border:1px solid black; border-radius: 5px; padding: 2px; font-size: 15x;">';




    $sqlDetaGuia = "select dgui_fac_dgui from saedgui where dgui_cod_guia = $id and dgui_cod_empr = $idEmpresa
                    and dgui_cod_sucu = $idSucursal group by dgui_fac_dgui";
    if ($oIfx->Query($sqlDetaGuia)) {
        if ($oIfx->NumFilas() > 0) {


            do {


                $dgui_fac_dgui = $oIfx->f("dgui_fac_dgui");



                if ($dgui_fac_dgui != '' && $para_sec_para == 1) {
                    $sqlFact = "select fact_nse_fact, fact_num_preimp, fact_auto_sri, fact_fech_fact from saefact where
                                    fact_cod_fact = $dgui_fac_dgui and fact_cod_empr = $idEmpresa and fact_cod_sucu = $idSucursal";

                    if ($oIfx2->Query($sqlFact)) {
                        if ($oIfx2->NumFilas() > 0) {
                            $ctrl_dest='S';
                            do {
                                $serie = substr($oIfx2->f("fact_nse_fact"), 0, 3);
                                $ptoEmi = substr($oIfx2->f("fact_nse_fact"), 3, 3);
                                $fact_num_preimp = $oIfx2->f("fact_num_preimp");
                                $fact_auto_sri = $oIfx2->f("fact_auto_sri");
                                $fact_fech_fact = $oIfx2->f("fact_fech_fact");

                                $numDocSustento = $serie . '-' . $ptoEmi . '-' . $fact_num_preimp;


                                $destinatario .= ' <tr>';
                                $destinatario .= ' <td style="width: 30%;">Comprobante de Venta:</td>';
                                $destinatario .= ' <td style="width: 50%;" >' . $numDocSustento . '</td>';
                                $destinatario .= ' <td style="width: 20%;" >Fecha de Emisión&nbsp;&nbsp;' . $fact_fech_fact . '</td>';
                                $destinatario .= ' </tr>';

                                $destinatario .= ' <tr>';
                                $destinatario .= ' <td style="width: 30%;">Número de Autorización:</td>';
                                $destinatario .= ' <td style="width: 70%;" colspan="2">' . $fact_auto_sri . '</td>';
                                $destinatario .= ' </tr>';

                                $destinatario .= ' <tr>';
                                $destinatario .= ' <td style="width: 30%;">&nbsp;</td>';
                                $destinatario .= ' <td style="width: 70%;" colspan="2"></td>';
                                $destinatario .= ' </tr>';
                            } while ($oIfx2->SiguienteRegistro());
                        }
                        else{
                            $ctrl_dest='S';
                            $destinatario .= ' <tr>';
                            $destinatario .= ' <td style="width: 30%;">Comprobante de Venta:</td>';
                            $destinatario .= ' <td style="width: 50%;" >'.$guia_nse_fact.'-'.$guia_num_fact.'</td>';
                            $destinatario .= ' <td style="width: 20%;" >Fecha de Emisión&nbsp;&nbsp;' . $guia_fech_fact . '</td>';
                            $destinatario .= ' </tr>';

                            $destinatario .= ' <tr>';
                            $destinatario .= ' <td style="width: 30%;">Número de Autorización:</td>';
                            $destinatario .= ' <td style="width: 70%;" colspan="2"></td>';
                            $destinatario .= ' </tr>';

                            $destinatario .= ' <tr>';
                            $destinatario .= ' <td style="width: 30%;">&nbsp;</td>';
                            $destinatario .= ' <td style="width: 70%;" colspan="2"></td>';
                            $destinatario .= ' </tr>';

                        }
                    }
                    $oIfx2->Free();


                    $sqlDeta = "select dgui_cod_prod, dgui_cant_dgui, dgui_nom_prod, dgui_cod_lote from saedgui where dgui_fac_dgui = '$dgui_fac_dgui' and dgui_cod_guia = $id
                                    and dgui_cod_empr = $idEmpresa and dgui_cod_sucu = $idSucursal";
                } else if ($dgui_fac_dgui == '' && $para_sec_para == 1)
                    $sqlDeta = "select dgui_cod_prod, dgui_cant_dgui, dgui_nom_prod, dgui_cod_lote from saedgui where (dgui_fac_dgui = ''  or  dgui_fac_dgui is null ) and dgui_cod_guia = $id
                                    and dgui_cod_empr = $idEmpresa and dgui_cod_sucu = $idSucursal";
                else
                    $sqlDeta = "select dgui_cod_prod, dgui_cant_dgui, dgui_nom_prod, dgui_cod_lote from saedgui where dgui_cod_guia = $id
                                    and dgui_cod_empr = $idEmpresa and dgui_cod_sucu = $idSucursal";

                // $destinatario .= ' </table>';
                // $destinatario .= ' <br>';
                
                if($ctrl_dest=='N'){
                            $destinatario .= ' <tr>';
                            $destinatario .= ' <td style="width: 30%;">Comprobante de Venta:</td>';
                            $destinatario .= ' <td style="width: 50%;" >'.$guia_nse_fact.'-'.$guia_num_fact.'</td>';
                            $destinatario .= ' <td style="width: 20%;" >Fecha de Emisión&nbsp;&nbsp;' . $guia_fech_fact . '</td>';
                            $destinatario .= ' </tr>';

                            $destinatario .= ' <tr>';
                            $destinatario .= ' <td style="width: 30%;">Número de Autorización:</td>';
                            $destinatario .= ' <td style="width: 70%;" colspan="2"></td>';
                            $destinatario .= ' </tr>';

                            $destinatario .= ' <tr>';
                            $destinatario .= ' <td style="width: 30%;">&nbsp;</td>';
                            $destinatario .= ' <td style="width: 70%;" colspan="2"></td>';
                            $destinatario .= ' </tr>';
                }
                if ($oIfx2->Query($sqlDeta)) {
                    if ($oIfx2->NumFilas() > 0) {
                        $detalle = '';

                        $detalle .= ' <table style="width: 100%; font-size: 12px; border-radius: 5px; border-collapse: collapse;margin-top:15px;" border="1">';
                        $detalle .= ' <tr>';
                        $detalle .= ' <td style="width: 10%;  border: 1px inset black;" align="center">Cantidad</td>';
                        $detalle .= ' <td style="width: 30%;  border: 1px inset black;" align="center">Descripcion</td>';
                        $detalle .= ' <td style="width: 30%;  border: 1px inset black;" align="center">Código principal</td>';
                        $detalle .= ' <td style="width: 30%;  border: 1px inset black;" align="center">Código Auxiliar</td>';
                        $detalle .= ' </tr>';

                        $a = 0;
                        do {
                            $codigoInterno = $oIfx2->f("dgui_cod_prod");

                            $sql_prod = "SELECT  prod_cod_alterno from saeprod where prod_cod_prod='$codigoInterno' and prod_cod_empr=$idEmpresa";
                            $prod_cod_alt = trim(consulta_string_func($sql_prod, 'prod_cod_alterno', $oIfxA, ''));

                            // $sql="SELECT tipo_merc, cantidad from agricola.despacho_prod";

                            $cantidad = $oIfx2->f("dgui_cant_dgui");
                            $descripcion = $oIfx2->f("dgui_nom_prod");
                            $lote = $oIfx2->f("dgui_cod_lote");

                            $detalle .= ' <tr>';
                            $detalle .= ' <td style="width: 10%; border: 1px inset black;" >' . round($cantidad, 0) . '</td>';
                            $detalle .= ' <td style="width: 30%; border: 1px inset black;">' . $descripcion . '</td>';
                            $detalle .= ' <td style="width: 30%; border: 1px inset black;">' . $codigoInterno . '</td>';
                            $detalle .= ' <td style="width: 30%; border: 1px inset black;">' . $prod_cod_alt . '</td>';


                            $detalle .= ' </tr>';

                            $a = $a + $cantidad;
                        } while ($oIfx2->SiguienteRegistro());
                        $detalle .= ' </table>';
                    }
                }


                /*$detalle .= ' <tr>';
                $detalle .= ' <td style="width: 20%;"></td>';
                $detalle .= ' <td style="width: 20%;"></td>';
                $detalle .= ' <td style="width: 50%;" align="right"></td>';
                $detalle .= ' <b><td style="width: 10%;" align="right">' . number_format($a, 2, '.', ',') . '</td></b>';
                $detalle .= ' </tr>';
                $detalle .= ' </table>';*/

                $oIfx2->Free();


                /* $detalle .= ' </td>';
                $detalle .= ' </tr>';
                $detalle .= ' </table>'; */
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $destinatario .= ' <tr>';
    $destinatario .= ' <td style="width: 30%;">Motivo Traslado:</td>';
    $destinatario .= ' <td style="width: 70%;" colspan="2" >' . $guia_cm3_guia . '</td>';
    $destinatario .= ' </tr>';
    
    $destinatario .= ' <tr>';
    $destinatario .= ' <td style="width: 30%;">Identificación (Destinatario)</td>';
    $destinatario .= ' <td style="width: 70%;" colspan="2" >' . $guia_ruc_clie . '</td>';
    $destinatario .= ' </tr>';
    
    
    $destinatario .= ' <tr>';
    $destinatario .= ' <td style="width: 30%;">Razón Social / Nombres y Apellidos:</td>';
    $destinatario .= ' <td style="width: 70%;" colspan="2" >' .$guia_nom_cliente . '</td>';
    $destinatario .= ' </tr>';
    
    $destinatario .= ' <tr>';
    $destinatario .= ' <td style="width: 30%;">Documento Aduanero</td>';
    $destinatario .= ' <td style="width: 70%;" colspan="2" >'.$guia_doc_adua.'</td>';
    $destinatario .= ' </tr>';
    
    $destinatario .= ' <tr>';
    $destinatario .= ' <td style="width: 30%;">Código Establecimiento Destino</td>';
    $destinatario .= ' <td style="width: 70%;" colspan="2" >'.$guia_cod_dest.'</td>';
    $destinatario .= ' </tr>';
    
    $destinatario .= ' <tr>';
    $destinatario .= ' <td style="width: 30%;">Ruta:</td>';
    $destinatario .= ' <td style="width: 70%;" colspan="2" >' . $ciud_des . ' ' . $guia_dir_clie . '</td>';
    $destinatario .= ' </tr>';
    
    
    
    
    $destinatario .= ' </table>';

    //INFROMACION ADICIONAL
    $infoadi='';
    if(!empty($guia_inf_adi)){

        $infoadi .= ' <table style="width: 60%; font-size: 12px; border:1px solid black; margin-top:15px;">';
        $infoadi .= ' <tr>';
        $infoadi .= ' <td style="width: 100%;">Información Adicional<br><br>' . $guia_inf_adi . '</td>';
        $infoadi .= ' </tr>';
        $infoadi .= ' </table>';
    }

$documentos .= $destinatario . $detalle. $infoadi;



$oIfx->Free();

$total = number_format($fact_con_miva + $fact_sin_miva + $fact_iva + $fact_fle_fact + $fact_otr_fact + $fact_fin_fact - $totalDescuento, 2, '.', '');
$V = new EnLetras();
$con_letra = strtoupper($V->ValorEnLetras($total, 'dolar'));

/* $totales .= '<table style="width: 100%;">';
  $totales .= '<tr>';
  $totales .= '<td>TOTAL :  </td>';
  $totales .= '<td>' . $con_letra . '</td>';
  $totales .= '</tr>';
  $totales .= '</tr>';
  $totales .= '</table>'; */

$legend = '<page_footer>
    <table align="center" style="width: 80%">
        <tr>
            <td style="font-size: 12px; color: #6B6565; background-color: transparent;" align="center">Este comprobante electronico ha sido generado a traves de Sisconti S.A. - Facturacion Electronica</td>
        </tr>
        <tr>
            <td style="font-size: 12px; color: #6B6565; background-color: transparent;" align="center">www.sisconti.com.ec</td>
        </tr>
    </table>
</page_footer>';

$documento .= '<page backimgw="70%" backtop="10mm" backbottom="10mm" backleft="20mm" backright="10mm">';
$documento .= $logo . $transportista . $documentos;
$documento .= $legend;
$documento .= '</page>';

$html2pdf = new HTML2PDF('P', 'A3', 'fr');
$html2pdf->WriteHTML($documento);
$ruta = DIR_FACTELEC . 'Include/archivos/' . $nombre_archivo . '.pdf';
$html2pdf->Output($ruta, 'F');

$rutaPdf = $ruta;

    return $documento;

}

?>
