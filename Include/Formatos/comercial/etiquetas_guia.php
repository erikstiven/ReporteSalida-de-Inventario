<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<?

include_once('../../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
if (isset($_REQUEST['guia'])){
    $guia = $_REQUEST['guia'];
}else{
    $guia = '';
}

if (isset($_REQUEST['tipo'])){
    $etiqueta = $_REQUEST['tipo'];
}else{
    $etiqueta = '';
}


if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

global $DSN_Ifx, $DSN;

// conexxion

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();


$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();
$idEmpresa = $_SESSION['U_EMPRESA'];

$sql = "select empr_web_color, empr_cod_pais,empr_cm1_empr, empr_rimp_sn, empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_img_rep, empr_iva_empr,empr_tel_resp, empr_ac1_empr, empr_ac2_empr, empr_mai_empr, empr_tip_empr
                                            from saeempr where empr_cod_empr = $idEmpresa ";


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_path_logo');
            $tel_empresa = $oIfx->f('empr_tel_resp');
            $empr_mai_empr = $oIfx->f('empr_mai_empr');
            if ($oIfx->f('empr_conta_sn') == 'S')
                $empr_conta_sn = 'SI';
            else
                $empr_conta_sn = 'NO';
            $empr_web_color = $oIfx->f('empr_web_color');
            $empr_rimp_sn = $oIfx->f('empr_rimp_sn');
            $empr_num_resu = $oIfx->f('empr_num_resu');
            $empr_iva_empr = $oIfx->f('empr_iva_empr');
            $empr_ac1_empr = $oIfx->f('empr_ac1_empr');
            $empr_ac2_empr = $oIfx->f('empr_ac2_empr');
            $empr_cm1_empr = $oIfx->f('empr_cm1_empr');
            $empr_cod_pais = $oIfx->f('empr_cod_pais');
            $empr_tip_empr = $oIfx->f('empr_tip_empr');
        }
    }
    $oIfx->Free();

    $path_img = explode("/", $empr_path_logo);
    $count = count($path_img) - 1;


    //CABECERA DE LA FACTURA
    
    // $path_logo_img = DIR_FACTELEC . 'imagenes/logos/' . $path_img[$count];
    $path_logo_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];


    if (file_exists($path_logo_img)) {
        $logo_empresa='<img width="250px;"  src="' . $path_logo_img . '">';
        $logo_empresa2='<img width="230px;"  src="' . $path_logo_img . '">';
        $logo_empresa3='<img width="180;"  src="' . $path_logo_img . '">';
    }
    else{
        $logo_empresa='<div style="color:red;">LOGO NO CARGADO</div>';
        $logo_empresa2='<div style="color:red;">LOGO NO CARGADO</div>';
        $logo_empresa3='<div style="color:red;">LOGO NO CARGADO</div>';
    }
    $path_rotulo=DIR_FACTELEC.'modulos/guia_remision/view/rotulo.png';
    $path_rotulo2=DIR_FACTELEC.'modulos/guia_remision/view/rotulo2.png';

    $logo_rotulo='<img width="350px;"  src="' . $path_rotulo . '">';
    $logo_rotulo2='<img width="500px;"  src="' . $path_rotulo2 . '">';
    $logo_rotulo3='<img width="270px;"  src="' . $path_rotulo2 . '">';

    $sql = "select g.guia_cod_guia, g.guia_cod_sucu, g.guia_cod_guia, g.guia_fech_guia, g.guia_num_preimp, g.guia_ruc_clie, 
    g.guia_nom_cliente, g.guia_iva, g.guia_con_miva, g.guia_tot_guia,g.guia_est_guia,
    g.guia_sin_miva, g.guia_email_clpv, g.guia_erro_sri, g.guia_sal_guia, g.guia_ciu_des,g.guia_ciu_ori,
    g.guia_tlf_cliente, g.guia_dir_clie, g.guia_hos_guia, g.guia_hol_guia,
    g.guia_num_plac, g.guia_cm3_guia, g.guia_cm1_guia, g.guia_cod_trta, g.guia_nse_guia, g.guia_cod_clpv,
    g.guia_clav_sri, g.guia_aprob_sri, g.guia_tip_entr
    from saeguia g where g.guia_cod_guia=$guia  ";

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $guia_fech_guia    = $oIfx->f('guia_fech_guia');
            $guia_num_preimp   = $oIfx->f('guia_num_preimp');
            $guia_nom_cliente  = $oIfx->f('guia_nom_cliente');
            $guia_ruc_clie     = $oIfx->f('guia_ruc_clie');
            $guia_est_guia     = $oIfx->f('guia_est_guia');
            $guia_cod_sucu     = $oIfx->f('guia_cod_sucu');
            $guia_clav_sri     = $oIfx->f('guia_clav_sri');
            $guia_email_clpv = $oIfx->f('guia_email_clpv');
            $guia_cm3_guia = $oIfx->f('guia_cm3_guia');
            $guia_cm1_guia = $oIfx->f('guia_cm1_guia');
            $guia_cod_trta = $oIfx->f('guia_cod_trta');
            $guia_sal_guia = $oIfx->f('guia_sal_guia');
            $guia_tlf_cliente = $oIfx->f('guia_tlf_cliente');

            $guia_num_plac = $oIfx->f('guia_num_plac');
            $guia_hos_guia = $oIfx->f('guia_hos_guia');
            $guia_hol_guia = $oIfx->f('guia_hol_guia');
            $guia_dir_clie = trim($oIfx->f('guia_dir_clie'));

            $guia_tip_entr = $oIfx->f('guia_tip_entr');
            
            $guia_ciu_des = $oIfx->f('guia_ciu_des');
            $guia_cod_clpv = $oIfx->f('guia_cod_clpv');
            if(empty($guia_ciu_des)){
                $guia_ciu_des='NULL';
            }

            if(empty($guia_dir_clie)){

                //DIRCCION DE LA SAECLPV

                $sqlcli="select sp_direcciones(saeclpv.clpv_cod_empr,clpv_cod_sucu,saeclpv.clpv_cod_clpv) direccion from saeclpv where clpv_cod_clpv= $guia_cod_clpv";
                $guia_dir_clie = consulta_string($sqlcli,'direccion', $oIfxA,'');
            }   
            
            $sql = "select ciud_cod_ciud, ciud_nom_ciud  from saeciud where ciud_cod_ciud = $guia_ciu_des";
            $ciud_des = consulta_string($sql, 'ciud_nom_ciud', $oIfxA, '');


            $guia_ciu_ori = $oIfx->f('guia_ciu_ori');
            if(empty($guia_ciu_ori)){
                $guia_ciu_ori='NULL';
            }
            $sql = "select ciud_cod_ciud, ciud_nom_ciud  from saeciud where ciud_cod_ciud = $guia_ciu_ori";
            $ciud_ori = consulta_string($sql, 'ciud_nom_ciud', $oIfxA, '');

        }
    }
    $oIfx->Free();

    //TIPO DE ENTREGA

    if($guia_tip_entr==''){
        $sqltip="select tip_entrega_clpv,atencion_ofi_clpv from saeclpv where clpv_cod_clpv=$guia_cod_clpv";
        $tipo = strtoupper(consulta_string($sqltip, 'tip_entrega_clpv', $oIfxA, ''));

        if(empty($tipo)){
            $tipo='DOMICILICIO';
        }
    }
    else{
        $tipo= strtoupper($guia_tip_entr);
    }

   
    //DATOS CLIENTE OFICINA
    $sqltip="select atencion_ofi_clpv from saeclpv where clpv_cod_clpv=$guia_cod_clpv";
    $atencion_oficina = strtoupper(consulta_string($sqltip, 'atencion_ofi_clpv', $oIfxA, ''));

   

    //ETIQUETA 1 TAMAÑO A4 HORIZONTAL

    $tablePDF='';
    if($etiqueta=='1'){
        $tablePDF.='<table width="100%" style="font-size:22px;margin-top:0px;" >';
        $tablePDF.='<tr>';
        $tablePDF.='<td width="500"><b>REMITE:</b><br><br><font style="color:#3c536c;">'.$razonSocial.'<br><br>'.$dirMatriz.'<br><br><b>CEL.'.$tel_empresa.'</b></font></td>';
        $tablePDF.='<td width="550">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$logo_empresa.'</td>';
        $tablePDF.='</tr>';
        $tablePDF.='<tr>';
        $tablePDF.='<td style="font-size:100px;color:red;" align="center" colspan="2" ><b>FRAGIL</b></td>';
        $tablePDF.='</tr>';
        $tablePDF.='<tr>';
        $tablePDF.='<td width="500"></td>';
        $tablePDF.='<td width="550" align="right"><br><b>PARA:</b><font style="color:#081f4b;"><br><br>'.$guia_nom_cliente.'<br><br>'.$guia_dir_clie.'<br><br>'.$ciud_ori.' - '.$ciud_des.'<br><br>CEL. '.$guia_tlf_cliente.'</font>
        <br><br><font style="color:red;font-size:24px;"><b>ENTREGA A '.$tipo.'</b></font><br><font style="color:#081f4b;">'.$atencion_oficina.'</font></td>';
        $tablePDF.='</tr>';
        $tablePDF.='</table>';
        $html2pdf = new HTML2PDF('L', 'A4', 'es');
    }
    if($etiqueta=='2'){
        $tablePDF.='<table width="100%" style="font-size:22px;margin-top:10px;" align="center">';
        $tablePDF.='<tr>';
        $tablePDF.='<td width="1010" align="center">'.$logo_empresa2.'</td>';
        $tablePDF.='</tr>';
        $tablePDF.='<tr>';
        $tablePDF.='<td width="1010" style="font-size:18px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PARA:</td>';
        $tablePDF.='</tr>';
        $tablePDF.='<tr>';
        $tablePDF.='<td width="1010" align="center"><font style="color:#002254;"><b>'.$guia_nom_cliente.'<br><br><br>'.$guia_dir_clie.'</b></font><br><br><font style="color:red;font-size:26px;"><b>ENTREGA A '.$tipo.'</b></font><br><font style="color:#081f4b;">'.$atencion_oficina.'</font></td>';
        $tablePDF.='</tr>';
        $tablePDF.='<tr>';
        $tablePDF.='<td style="font-size:100px;color:red;" width="1010" align="center"><b>FRAGIL</b></td>';
        $tablePDF.='</tr>';

        $tablePDF.='<tr>';
        $tablePDF.='<td width="1010" style="font-size:18px;"><br>REMITE:</td>';
        $tablePDF.='</tr>';

        $tablePDF.='<tr>';
        $tablePDF.='<td width="1010" align="center"><font style="color:#002254;"><b>'.$razonSocial.'</b><br><br>'.$ciud_ori.' - '.$ciud_des.'</font></td>';
        $tablePDF.='</tr>';
        $tablePDF.='</table>';
        $html2pdf = new HTML2PDF('L', 'A4', 'es');
    }

    if($etiqueta=='3'){
        $tablePDF.='<table width="100%" style="font-size:17px;margin-top:10px;" >';
        $tablePDF.='<tr>';
        $tablePDF.='<td width="400" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$logo_empresa3.'</td>';
        $tablePDF.='</tr>';
        $tablePDF.='<tr>';
        $tablePDF.='<td width="400" style="font-size:16px;">PARA:</td>';
        $tablePDF.='</tr>';
        $tablePDF.='<tr>';
        $tablePDF.='<td width="400" align="center"><font style="color:#002254;"><b>'.$guia_nom_cliente.'<br><br><br>'.$guia_dir_clie.'</b></font><br><br><font style="color:red;font-size:17px;"><b>ENTREGA A '.$tipo.'</b></font><br><font style="color:#081f4b;">'.$atencion_oficina.'</font></td>';
        $tablePDF.='</tr>';
        $tablePDF.='<tr>';
        $tablePDF.='<td style="font-size:60px;color:red;" width="400" align="center"><b>FRAGIL</b></td>';
        $tablePDF.='</tr>';

        $tablePDF.='<tr>';
        $tablePDF.='<td width="400" style="font-size:16px;"><br><br>REMITE:</td>';
        $tablePDF.='</tr>';

        $tablePDF.='<tr>';
        $tablePDF.='<td width="400" align="center"><font style="color:#002254;"><b>'.$razonSocial.'</b><br><br>'.$ciud_ori.' - '.$ciud_des.'</font></td>';
        $tablePDF.='</tr>';
        $tablePDF.='</table>';
        $html2pdf = new HTML2PDF('P', 'A4', 'es');
    }
    


 $html2pdf->WriteHTML($tablePDF);
 $html2pdf->Output('etiquetas.pdf', '');
    
?>