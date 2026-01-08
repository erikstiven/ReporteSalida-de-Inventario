<?

function rol_individual_formato($id_empresa = 0, $id_anio = 0, $id_mes = 0, $id_empl = '', $id_depar = 0, $id_proyecto = 0, $tipo_formato = 0)
{
    //Definiciones
    global $DSN_Ifx;
    include_once('../../../../Include/config.inc.php');
    include_once(path(DIR_INCLUDE) . 'html2pdf_v4.03/_tcpdf_5.0.002_old/tcpdf.php');

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();


    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();


    $oIfxC = new Dbo;
    $oIfxC->DSN = $DSN_Ifx;
    $oIfxC->Conectar();

    $oIfxD = new Dbo;
    $oIfxD->DSN = $DSN_Ifx;
    $oIfxD->Conectar();

    $oReturn = new xajaxResponse();

    $fecha_ini_per = $id_anio . '-' . $id_mes . '-' . '01';
    $fecha_fin_per = date('Y-m-t', strtotime($fecha_ini_per));


    //anio - mes
    $opAnio = 0;
    if ($id_anio == 2017) {
        $opAnio = 1;
    }

    //PARAMETROS FORMATO 

    $sqlp = "select prrh_val_prrh from saeprrh where prrh_cod_prrh='ROTIP' and prrh_cod_empr=$id_empresa";
    $tipo_formato_copia = consulta_string($sqlp, 'prrh_val_prrh', $oIfx, 0);


    $sqlp = "select prrh_val_prrh from saeprrh where prrh_cod_prrh='COTIP' and prrh_cod_empr=$id_empresa";
    $tipo_formato_correo = consulta_string($sqlp, 'prrh_val_prrh', $oIfx, 0);

    $sqlp = "select prrh_val_prrh from saeprrh where  prrh_cod_prrh='JFRRH' and prrh_cod_empr=$id_empresa";
    $jefe_rrh = consulta_string($sqlp, 'prrh_val_prrh', $oIfx, 0);
    if (empty($jefe_rrh)) {
        $jefe_rrh = '<font color="red">NO CONFIGURADO<br> HAGA EL INGRESO EN EL MODULO:<br>TALENTO HUMANO->PARAMETROS</font>';
    }

    $sqlp = "select prrh_val_prrh from saeprrh where  prrh_cod_prrh='CARRH' and prrh_cod_empr=$id_empresa";
    $cargo_rrh = consulta_string($sqlp, 'prrh_val_prrh', $oIfx, 0);
    if (empty($cargo_rrh)) {
        $cargo_rrh = 'Gerente Recursos Humanos';
    }

    $sqlp = "select prrh_val_prrh from saeprrh where  prrh_cod_prrh='UFRRH' and prrh_cod_empr=$id_empresa";
    $cod_user = consulta_string($sqlp, 'prrh_val_prrh', $oIfx, 0);
    $logofirma = '';
    if (!empty($cod_user)) {
        $sqlf = "select concat(usuario_nombre,' ',usuario_apellido) as apenomb, firma_empl from comercial.usuario where usuario_id=$cod_user";
        $firma = consulta_string_func($sqlf, 'firma_empl', $oIfx, '');

        $fir = substr($firma, 3);
        if (!empty($fir)) {
            $arc_img = DIR_FACTELEC . "Include/Clases/Formulario/Plugins/reloj/$fir";
            if (file_exists($arc_img)) {

                if (preg_match("/jpg|png|PNG|jpeg|gif/", $fir)) {
                    $logofirma = '<img src="' . $arc_img . '" style="width:75px;">';
                } else {
                    $logofirma = '<strong><font color="red">FORMATO DE IMAGEN NO VALIDA</font> <br> <font color="blue">LAS EXTENSIONES DE ARCHIVO PERMITIDAS SON:</font> .jpg, .jpeg, .png, .gif</strong><br>';
                }
            }
        }
    }



    //DATOS DE LA EMPRESA

    $sql = "select empr_cod_pais,empr_cm1_empr, empr_rimp_sn, empr_nom_empr, empr_ruc_empr , empr_dir_empr, empr_conta_sn, empr_num_resu, empr_path_logo, empr_iva_empr,empr_tel_resp, empr_ac1_empr, empr_ac2_empr, empr_mai_empr, empr_tip_empr
                                            from saeempr where empr_cod_empr = $id_empresa ";


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $razonSocial = trim($oIfx->f('empr_nom_empr'));
            $ruc_empr = $oIfx->f('empr_ruc_empr');
            $dirMatriz = trim($oIfx->f('empr_dir_empr'));
            $empr_path_logo = $oIfx->f('empr_path_logo');
            $tel_empresa = $oIfx->f('empr_tel_resp');
            $empr_mai_empr = $oIfx->f('empr_mai_empr');
        }
    }
    $oIfx->Free();

    //ESTADO DE LOS EMPELADOS

    unset($array_estado);



    $sql = "SELECT   empl_cod_empl, empl_ape_nomb, empl_cod_eemp,empl_cod_pdie, empl_car_iess  FROM saeempl
    where empl_cod_empr=$id_empresa;";

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {

            do {
                $identificacion = $oIfx->f('empl_cod_empl');
                $nombre = ($oIfx->f('empl_ape_nomb'));
                $cargo = $array_cargo[$identificacion];
                $estado = $oIfx->f('empl_cod_eemp');



                if ($estado == 'A') {
                    $estado = 'ACTIVO';
                    $div = '<div class=\"btn-info text-center\">' . $estado . '</div>' . $centro_costo;
                }
                if ($estado == 'L') {
                    $estado = 'LIQUIDADO';
                    $div = '<div class=\"btn-warning text-center\" >' . $estado . '</div>' . $centro_costo;
                }

                if ($estado == 'I') {
                    $estado = 'INACTIVO';
                    $div = '<div class=\"btn-danger text-center\" >' . $estado . '</div>';
                }

                $array_estado[$identificacion] = $estado;
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $oIfx->Free();

    //ONP EMPLEADOS

    unset($array_onp);


    $sql = "select empl_cod_empl, pdie_des_pdie from saeempl, saepdie where pdie_cod_pdie=empl_cod_pdie and empl_cod_empr=$id_empresa; ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $sHtmlEstado = '';
            do {

                $identificacion = $oIfx->f('empl_cod_empl');
                $onp = ($oIfx->f('pdie_des_pdie'));
                $array_onp[$identificacion] = $onp;
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $oIfx->Free();



    $nombre_archivo = 'RECIBO_PAGO_' . $id_anio . '_' . $id_mes . '_' . $id_empl;


    $sql = "select pago_fec_real, pago_cod_estr
            from saepago 
            where pago_cod_empl='$id_empl' and 
                pago_cod_empr='$id_empresa' and
                SUBSTR(cast (pago_per_pago as text), 0, 5) = '$id_anio' and
                SUBSTR(cast (pago_per_pago as text), 5, 2) = '$id_mes'  and pago_ori_gene='R'
                
                group by 1,2";

    $ban_anti = false;
    if ($oIfxD->query($sql)) {
        if ($oIfxD->NumFilas() > 0) {
            do {
                $table_op = "";
                $legend = "";
                $pago_fec_real = $oIfxD->f('pago_fec_real');
                $id_depar = $oIfxD->f('pago_cod_estr');
                $sql_depar = '';
                if (!empty($id_proyecto)) {
                    if (!empty($id_depar)) {
                        $sql_depar = " and pago_cod_empl in (  select  esem_cod_empl from saeestr, saeesem where
                                                        estr_cod_estr = esem_cod_estr and
                                                        estr_cod_empr = $id_empresa and
                                                        estr_cod_padr  in ( '$id_depar' )
                                                        group by 1 ) ";
                    } else {
                        $sql_depar = " and pago_cod_empl in (  select  esem_cod_empl from saeestr, saeesem where
                                                        estr_cod_estr = esem_cod_estr and
                                                        estr_cod_empr = $id_empresa and
                                                        estr_cod_padr  in ( select estr_cod_estr
                                                                                from saeestr where
                                                                                estr_cod_empr = $id_empresa and
                                                                                estr_cod_gpro = '$id_proyecto'  )
                                                        group by 1 ) ";
                    }
                }

                $sql_empl = '';
                $sql_empl = " and pago_cod_empl = '$id_empl' ";

                $sql_prov = '';
                $sql_prov = " and pe.pemp_cod_empl= '$id_empl'";


                //DATOS DE LA EMPRESA

                $sql = "select empr_nom_empr, empr_path_logo, empr_img_rep from saeempr where empr_cod_empr = $id_empresa ";

                $empr_nom = consulta_string_func($sql, 'empr_nom_empr', $oIfxA, '');
                $empr_path_logo = consulta_string_func($sql, 'empr_img_rep', $oIfxA, '');
                $path_img = explode("/", $empr_path_logo);
                $count = count($path_img) - 1;
                $arc_img = DIR_FACTELEC . 'Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];
                //echo $arc_img;exit;
                if (file_exists($arc_img)) {
                    $imagen = $arc_img;
                } else {
                    $imagen = '';
                }

                $logo = '';
                $x = '0px';
                if ($imagen != '') {

                    $empr_logo = '<div align="left">
                        <img src="' . $imagen . '" style="
                        width:200px;">
                        </div>';
                    $x = '0px';
                } else {
                    $empr_logo = '<span><font color="red">SIN LOGO</font></span>';
                }




                //rubro sueldo
                $sql = "select rubr_cod_rubr from saerubr where rubr_tsu_rubr = '1'";

                $rubroSueldo = consulta_string_func($sql, 'rubr_cod_rubr', $oIfxA, '');

                //$oReturn->alert($sql);
                unset($tot_ingr);
                unset($array_table_ingr);
                unset($array_tmp);
                //// prestamos

                $sql = "select sum(cuot_mot_capi) as valor, pret_cod_empl, tpre_cod_rubr
                    from saecuot  c
                     inner join saepret on pret_cod_pret = cuot_cod_pret
                    inner join saetpre on tpre_cod_tpre = pret_cod_tpre
                     where 
                    pret_cod_empr=tpre_cod_empr and 
                    pret_cod_empr='1' and cuot_est_cuot='0' group by pret_cod_empl , tpre_cod_rubr
                    ";;
                //echo $sql; exit;
                unset($array_prest_pen);
                if ($oIfx->Query($sql)) {
                    if ($oIfx->NumFilas() > 0) {
                        do {
                            $array_prest_pen[$oIfx->f('pret_cod_empl')][$oIfx->f('tpre_cod_rubr')] = $oIfx->f('valor');
                            //$array_prest_pen[$oIfx->f('pret_cod_empl')]['RCAFE']=$oIfx->f('valor');
                        } while ($oIfx->SiguienteRegistro());
                    }
                }


                // DESCUENTOS

                if (($id_mes < 12) && ($id_anio == 2017)) {
                    $sql = "select  
                            pago_cod_rubr, pago_cod_empl,   pago_per_pago, 
                            pago_val_pago,  rubr_des_rubr,
                            rubr_cod_trub
                            from saepago,
                            saerubr
                            where
                            rubr_cod_rubr = pago_cod_rubr and
                            rubr_cod_empr = $id_empresa and
                            pago_cod_empr = $id_empresa and
                            SUBSTR(cast (pago_per_pago as text), 0, 5) = '$id_anio' and
                            SUBSTR(cast (pago_per_pago as text), 5, 2) = '$id_mes' and
                            rubr_cod_trub = 'D' and rubr_rol_desp='1' AND rubr_cod_rubr<>'RIESS' and pago_fec_real='$pago_fec_real' and pago_ori_gene='R'
                            $sql_empl 
                            order by pago_cod_empl";
                    //echo $sql;
                    //echo 'hola 1 ';exit;
                } else {
                    $sql = "select  
                            pago_cod_rubr, pago_cod_empl,   pago_per_pago, 
                            pago_val_pago,  rubr_des_rubr,
                            rubr_cod_trub
                            from saepago,
                            saerubr
                            where
                            rubr_cod_rubr = pago_cod_rubr and
                            rubr_cod_empr = $id_empresa and
                            pago_cod_empr = $id_empresa and
                            SUBSTR(cast (pago_per_pago as text), 0, 5) = '$id_anio' and
                            SUBSTR(cast (pago_per_pago as text), 5, 2) = '$id_mes' and
                            rubr_cod_trub = 'D' and rubr_rol_desp='1' and pago_fec_real='$pago_fec_real' and pago_ori_gene='R'
                            $sql_empl 
                            order by pago_cod_empl";
                    //echo $sql;
                    //echo 'hola 2 ';exit;
                }

                unset($tot_egre);
                $tabla_egre = '';
                unset($array_table_egre);
                unset($array_tmp);

                $desc_faltas = 0;
                $desc_salud = 0;

                $tmp = 1;
                $ban = 0;
                $i = 0;
                if ($oIfx->Query($sql)) {
                    if ($oIfx->NumFilas() > 0) {
                        do {

                            $cod_empl = $oIfx->f('pago_cod_empl');
                            $rubr_cod = $oIfx->f('pago_cod_rubr');
                            $pago_per = $oIfx->f('pago_per_pago');
                            $pago_val = $oIfx->f('pago_val_pago');
                            $rubr_des = $oIfx->f('rubr_des_rubr');
                            $rubr_tipo = $oIfx->f('rubr_cod_trub');

                            if ($rubr_cod == 'RFALT') {
                                $desc_faltas += $pago_val;
                            }
                            if ($rubr_cod == 'RSCSA') {
                                $desc_salud += $pago_val;
                            }
                            if ($rubr_cod == 'RSSAL') {
                                $desc_salud += $pago_val;
                            }

                            $array_tmp[$tmp] = $cod_empl;
                            if ($tmp > 1) {
                                if ($array_tmp[$tmp] != $array_tmp[$tmp - 1]) {
                                    $tabla_egre = '';
                                }
                            }
                            if (($tmp == 1)  && ($id_anio >= 2017) && ($ban == 0) && ($ban_anti == false)) {
                                $sql = "select estr_cod_ppag from saeestr where estr_cod_estr='$id_depar' and estr_cod_empr='$id_empresa'";
                                $control_estr = consulta_string($sql, 'estr_cod_ppag', $oIfxA, '');
                                //echo $sql;exit;
                                if ($control_estr == 1) {
                                    unset($tablaAnticipo);
                                    $sql = "SELECT 
                                            anti_obs_anti,
                                            anti_val_anti 
                                        FROM saeanti 
                                            where anti_cod_empl= '$cod_empl' and 
                                            DATE_PART('year',anti_fec_anti) = $id_anio and
                                            DATE_PART('month',anti_fec_anti) = $id_mes";

                                    if ($oIfxC->Query($sql)) {
                                        if ($oIfxC->NumFilas() > 0) {
                                            $obs = $oIfxC->f('anti_obs_anti');
                                            $val = $oIfxC->f('anti_val_anti');
                                            //echo $val; exit;
                                            $ban = 1;
                                            //if($val!='0.0'){
                                            if (!empty($val) || $val != '0.0') {
                                                $tabla_egre .= '<tr>';
                                                $tabla_egre .= '<td  width="75%" align="left">' . $obs . ':</td>';
                                                $tabla_egre .= '<td  width="25%" align="right"><strong>' . number_format($val, 2, '.', ',') . '</strong></td>';
                                                //$tabla_egre .= '<td  align="right">&nbsp;</td>';
                                                $tabla_egre .= '</tr>';
                                            }
                                        }
                                    }

                                    $tot_egre[$cod_empl] += $val;


                                    $ban_anti = true;
                                }
                            }

                            if ($sClass == 'off') $sClass = 'on';
                            else $sClass = 'off';
                            //var_dump($array_prest_pen);
                            //exit;
                            if (empty($array_prest_pen[$cod_empl][$rubr_cod])) {
                                //if(empty($array_prest_pen[$cod_empl])){
                                //echo 'hola tabla';
                                if ($rubr_cod != 'RANTI') {
                                    $tabla_egre .= '<tr>';
                                    $tabla_egre .= '<td align="left" width="75%">' . $rubr_des . ':</td>';
                                    $tabla_egre .= '<td  align="right" width="25%"><strong>' . number_format($pago_val, 2, '.', ',') . '</strong></td>';
                                    // $tabla_egre .= '<td>&nbsp;</td>';
                                    $tabla_egre .= '</tr>';
                                }
                            } else {

                                $tabla_egre .= '<tr>';
                                $tabla_egre .= '<td  align="left" width="75%">' . $rubr_des . ':</td>';
                                $tabla_egre .= '<td  align="right" width="25%"><strong>' . number_format($pago_val, 2, '.', ',') . '</strong></td>';
                                //$tabla_egre .= '<td >' . number_format($array_prest_pen[$cod_empl][$rubr_cod], 2, '.', ',') . '</td>';
                                $tabla_egre .= '</tr>';
                            }

                            //echo $tabla_egre;
                            //exit;

                            $array_table_egre[$cod_empl] = $tabla_egre;

                            if ($rubr_cod != 'RANTI') {
                                $tot_egre[$cod_empl] += $pago_val;
                            }

                            $tmp++;

                            //var_dump($array_table_egre);
                            $i++;
                            //var_dump($i.'p');
                        } while ($oIfx->SiguienteRegistro());
                    }
                }
                $oIfx->Free();

                //anticipo
                $anticipo = 0;
                if (($val == 0) && ($ban_anti == false)) {
                    $sql = "select estr_cod_ppag from saeestr where estr_cod_estr='$id_depar' and estr_cod_empr='$id_empresa'";
                    $control_estr = consulta_string($sql, 'estr_cod_ppag', $oIfx, '');
                    //echo $sql;exit;
                    if ($control_estr == 1) {
                        $sql = "select sum(anti_val_anti) as anticipo 
                        from saeanti
                        where anti_cod_empl = '$id_empl' and
                        anti_cod_empr = $id_empresa and
                        DATE_PART('year',anti_fec_anti) = $id_anio and
                        DATE_PART('month',anti_fec_anti) = $id_mes";
                        $anticipo = consulta_string_func($sql, 'anticipo', $oIfx, 0);
                        $ban_anti = true;
                    }
                }

                /*if($anticipo > 0){
            $tablaAnticipo .='<tr>';
            $tablaAnticipo .='<td align="left">PAGO QUINCENA</td>';
            $tablaAnticipo .='<td align="right">'.number_format($anticipo, 2, '.', ',').'</td>';
            $tablaAnticipo .='</tr>';

            $array_table_egre[$id_empl] .= $tablaAnticipo;
            $tot_egre[$id_empl] += $anticipo;
        }*/

                //$table_op .='<div width="80%" syle="margin-top: 5px;">';

                ///$table_op .='<div width="80%" syle="margin-top: 15px;">';


                $sql = "select pago_cod_empl
                from saepago, saerubr   where
                rubr_cod_rubr = pago_cod_rubr and
                rubr_cod_empr = pago_cod_empr and
                rubr_cod_empr = $id_empresa and                      
                pago_cod_empr = $id_empresa and
                SUBSTR(cast (pago_per_pago as text), 0, 5) = '$id_anio' and
                SUBSTR(cast (pago_per_pago as text), 5, 2) = '$id_mes' and pago_fec_real='$pago_fec_real' and pago_ori_gene='R'
                and rubr_rol_desp='1'
                $sql_empl 
                $sql_depar
                group by 1
                order by pago_cod_empl";
                //echo $sql;
                //exit;
                $ingr = 0;
                $egre = 0;
                if ($oIfx->Query($sql)) {
                    if ($oIfx->NumFilas() > 0) {
                        do {
                            $cod_empl = $oIfx->f('pago_cod_empl');

                            $tabla_ingr = $array_table_ingr[$cod_empl];
                            $tabla_egre = $array_table_egre[$cod_empl];

                            $egre = $tot_egre[$cod_empl];
                            $ingr = $tot_ingr[$cod_empl];

                            $sql = "select empl_ape_nomb , empl_sal_sala, empl_jor_trab, empl_val5_empl, empl_num_ctas , empl_cod_banc, empl_car_iess
                                from saeempl 
                                where empl_cod_empr = $id_empresa and 
                                empl_cod_empl = '$cod_empl' ";
                            $empl_nom = consulta_string_func($sql, 'empl_ape_nomb', $oIfxA, '');
                            $salario = consulta_string_func($sql, 'empl_sal_sala', $oIfxA, '');
                            $empl_jor_trab = consulta_string_func($sql, 'empl_jor_trab', $oIfxA, '');
                            $empl_val5_empl = consulta_string_func($sql, 'empl_val5_empl', $oIfxA, '');
                            $empl_num_ctas = consulta_string_func($sql, 'empl_num_ctas', $oIfxA, '');
                            $empl_cod_banc = consulta_string_func($sql, 'empl_cod_banc', $oIfxA, '');
                            $empl_car_iess = consulta_string_func($sql, 'empl_car_iess', $oIfxA, '');

                            $banco = '';
                            if (trim($empl_cod_banc) != '') {
                                $sql = "select banc_nom_banc
                                from saebanc
                                where banc_cod_banc= $empl_cod_banc";
                                $banco = consulta_string_func($sql, 'banc_nom_banc', $oIfxA, '');

                                if ($opAnio == 0) {
                                    $sueldo = $salario;
                                } else {
                                    $sueldo = $empl_val5_empl;
                                }
                            }


                            $sql = "select estr_des_estr, esem_fec_ingr  from saeesem, saeestr where
                                    estr_cod_estr = esem_cod_estr and
                                    estr_cod_empr = $id_empresa and
                                    esem_cod_empl = '$cod_empl' and 
                                    esem_cod_empr = $id_empresa 
                                    order by 2 desc";

                            $cargo = consulta_string_func($sql, 'estr_des_estr', $oIfxA, '');

                            $fech_ing_cargo = consulta_string_func($sql, 'esem_fec_ingr', $oIfxA, '');

                            $sql_tmp = "select estr_cod_padr  from saeesem, saeestr where
                                    estr_cod_estr = esem_cod_estr and
                                    estr_cod_empr = $id_empresa and
                                    esem_cod_empl =  '$cod_empl'  and 
                                    esem_cod_empr = $id_empresa ";



                            if ($oIfxB->Query($sql_tmp)) {
                                if ($oIfxB->NumFilas() > 0) {
                                    $estr_tmp = $oIfxB->f('estr_cod_padr');
                                }
                            }
                            $oIfxB->Free();

                            $sql_p = "select estr_des_estr  from saeestr where 
                                    estr_cod_empr = $id_empresa and
                                    estr_cod_estr = '$estr_tmp'  ";

                            if ($oIfxB->Query($sql_p)) {
                                if ($oIfxB->NumFilas() > 0) {
                                    $estr_nom = $oIfxB->f('estr_des_estr');
                                }
                            }
                            $oIfxB->Free();


                            // HORAS EXTRAS
                            $sql = "select  amem_hor_trab, amem_hor_atra, amem_hor_falt,
                                        amem_hor_perm, amem_hor_e025, amem_hor_lice,
                                        amem_hor_e050,  amem_hor_e100,  amem_hor_vaca, amem_hor_mate, amem_hor_efer, amem_hor_sein
                                from    saeamem where
                                        amem_cod_empr = $id_empresa and
                                        amem_cod_empl = '$cod_empl'
                                        and DATE_PART ('year',amem_fec_amem)= '$id_anio'
                                        and DATE_PART ('month',amem_fec_amem)= '$id_mes'
                                        ";


                            if ($oIfxB->Query($sql)) {
                                if ($oIfxB->NumFilas() > 0) {
                                    $hor_trab = $oIfxB->f('amem_hor_trab');
                                    $hor_atra = $oIfxB->f('amem_hor_atra');
                                    $hor_falt = $oIfxB->f('amem_hor_falt');
                                    $hor_perm = ($oIfxB->f('amem_hor_perm'));
                                    $hor_25 = $oIfxB->f('amem_hor_e025');
                                    $hor_50 = $oIfxB->f('amem_hor_e050');
                                    $hor_100 = $oIfxB->f('amem_hor_e100');
                                    $hor_vaca = $oIfxB->f('amem_hor_vaca');
                                    $hor_mater = $oIfxB->f('amem_hor_mate');
                                    $hor_sein = $oIfxB->f('amem_hor_sein');
                                    //$hor_sein= ($oIfxB->f('amem_hor_mate')/8);
                                    $hor_lice = $oIfxB->f('amem_hor_lice');
                                    $hor_efer = $oIfxB->f('amem_hor_efer');
                                }
                            }

                            // INGRESOS
                            $sql = "select  
                            pago_cod_rubr, pago_cod_empl,   pago_per_pago, 
                            pago_val_pago,  rubr_des_rubr,
                            rubr_cod_trub
                            from saepago,
                            saerubr
                            where
                            rubr_cod_rubr = pago_cod_rubr and
                            rubr_cod_empr = $id_empresa and                      
                            pago_cod_empr = $id_empresa and
                            SUBSTR(cast (pago_per_pago as text), 0, 5) = '$id_anio' and
                            SUBSTR(cast (pago_per_pago as text), 5, 2) = '$id_mes' and
                            rubr_cod_trub = 'I'  and rubr_rol_desp='1' and pago_fec_real='$pago_fec_real' and pago_ori_gene='R'
                            $sql_empl  
                            order by rubr_des_alia";


                            $tabla_ingr = '';
                            $tmp = 1;
                            $lic_con = 0;
                            $lic_sin = 0;
                            if ($oIfx->Query($sql)) {
                                if ($oIfx->NumFilas() > 0) {
                                    do {
                                        $cod_empl = $oIfx->f('pago_cod_empl');
                                        $rubr_cod = $oIfx->f('pago_cod_rubr');
                                        $pago_per = $oIfx->f('pago_per_pago');
                                        $pago_val = $oIfx->f('pago_val_pago');
                                        $rubr_des = $oIfx->f('rubr_des_rubr');
                                        $rubr_tipo = $oIfx->f('rubr_cod_trub');
                                        $array_tmp[$tmp] = $cod_empl;

                                        /*if($rubr_cod == $rubroSueldo){
                                        $salario = $pago_val;
                                    }*/

                                        if ($tmp > 1) {
                                            if ($array_tmp[$tmp] != $array_tmp[$tmp - 1]) {
                                                $tabla_ingr = '';
                                            }
                                        }

                                        $fecha_ingreso = ingreso_fecha($cod_empl);
                                        //$fecha_ingreso_cargo = ingreso_fecha_cargo($cod_empl);
                                        $ban = 0;
                                        $tabla_ingr .= '<tr>';
                                        $tabla_ingr .= '<td  align="left" width="70%">' . $rubr_des . ':</td>';
                                        if ($rubr_cod == 'RHEVC') {
                                            // $tabla_ingr .= '<td align="left"></td>';
                                            $tabla_ingr .= '<td  align="left" width="10%">' . $hor_25 . '</td>';
                                            $ban = 1;
                                        }
                                        if ($rubr_cod == 'RHECC') {
                                            //$tabla_ingr .= '<td align="left"></td>';
                                            $tabla_ingr .= '<td align="left" width="10%">' . $hor_50 . '</td>';
                                            //$tabla_ingr .='<td align="left"></td>';
                                            $ban = 1;
                                        }
                                        if ($rubr_cod == 'RHECI') {
                                            //$tabla_ingr .= '<td align="left"></td>';
                                            $tabla_ingr .= '<td  align="left" width="10%">' . $hor_100 . '</td>';
                                            //$tabla_ingr .='<td align="left"></td>';
                                            $ban = 1;
                                        }
                                        if ($rubr_cod == 'RSUEL') {
                                            //$tabla_ingr .='<td align="left">'.$hor_trab.'</td>';

                                            $dias_tra = (($empl_jor_trab - $hor_vaca - $hor_efer - $hor_lice - $hor_sein - $hor_atra - $hor_falt) * 30 / $empl_jor_trab);
                                            $tabla_ingr .= '<td  align="left" width="10%">' . $dias_tra . '</td>';
                                            //$tabla_ingr .= '<td align="left"></td>';
                                            $ban = 1;
                                        }
                                        if ($rubr_cod == 'RLICE') {
                                            //$tabla_ingr .='<td align="left">'.$hor_lice.'</td>';
                                            //$tabla_ingr .='<td align="left"></td>';
                                            $dias_lice = ($hor_lice * 30 / $empl_jor_trab);
                                            $tabla_ingr .= '<td  align="left" width="10%">' . $dias_lice . '</td>';
                                            //$tabla_ingr .= '<td align="left"></td>';
                                            $ban = 1;
                                        }
                                        if ($rubr_cod == 'RLICD') {
                                            //$tabla_ingr .='<td align="left">'.$hor_efer.'</td>';
                                            //$tabla_ingr .='<td align="left"></td>';
                                            $dias_efer = ($hor_efer * 30 / $empl_jor_trab);
                                            $tabla_ingr .= '<td  align="left" width="10%">' . $dias_efer . '</td>';
                                            // $tabla_ingr .= '<td align="left"></td>';
                                            $ban = 1;
                                        }
                                        if ($rubr_cod == 'RVACP') {
                                            //$tabla_ingr .='<td align="left">'.$hor_vaca.'</td>';
                                            //$tabla_ingr .='<td align="left"></td>';
                                            $dias_vaca = ($hor_vaca * 30 / $empl_jor_trab);
                                            $tabla_ingr .= '<td  align="left" width="10%">' . $dias_vaca . '</td>';
                                            //$tabla_ingr .= '<td align="left"></td>';
                                            $ban = 1;
                                        }
                                        if ($rubr_cod == 'RMATE') {
                                            //$tabla_ingr .='<td align="left">'.$hor_vaca.'</td>';
                                            //$tabla_ingr .='<td align="left"></td>';
                                            $dias_mater = ($hor_mater * 30 / $empl_jor_trab);
                                            $tabla_ingr .= '<td  align="left" width="10%">' . $dias_mater . '</td>';
                                            // $tabla_ingr .= '<td align="left"></td>';
                                            $ban = 1;
                                        }

                                        if ($rubr_cod == 'RELGH') {
                                            $lic_con += $pago_val;
                                        }
                                        if ($rubr_cod == 'RESGH') {
                                            $lic_sin += $pago_val;
                                        }
                                        $dias_efer = ($hor_efer * 30 / $empl_jor_trab);


                                        if ($ban == 0) {
                                            $tabla_ingr .= '<td width="10%"></td>';
                                        }
                                        $tabla_ingr .= '<td  align="right" width="20%"><strong>' . number_format($pago_val, 2, '.', ',') . '</strong></td>';
                                        $tabla_ingr .= '</tr>';

                                        $array_table_ingr[$cod_empl] = $tabla_ingr;


                                        $tot_ingr += $pago_val;
                                        $tmp++;
                                    } while ($oIfx->SiguienteRegistro());
                                }
                            }

                            //APORTES DEL EMPLEADOR

                            $sql = "select  r.rubr_cod_rubr, p.pnom_cod_pnom , pe.pemp_cod_empl, r.rubr_des_rubr,
                    sum(pe.pemp_val_mese) as pago
                    from saerubr r, saepnom p,  saepemp pe  where
                    p.pnom_cod_pnom   = pe.pemp_cod_pnom and 
                    r.rubr_cod_rubr   = p.pnom_cod_rubr  and
                    r.rubr_cod_empr   = $id_empresa and
                    p.pnom_cod_empr   = $id_empresa and
                    pe.pemp_cod_empr  = $id_empresa and
                    SUBSTR(cast (pe.pemp_per_pemp as text), 0, 5) = '$id_anio' and
                    SUBSTR(cast (pe.pemp_per_pemp as text), 5, 2) = '$id_mes' 
                    and rubr_rol_desp='1'
                    $sql_prov
                    group by 1,2,3,4 order by 3 ";
                            $tabla_apor = '';
                            $tot_prov = 0;
                            if ($oIfx->Query($sql)) {
                                if ($oIfx->NumFilas() > 0) {
                                    do {

                                        $pago_val = $oIfx->f('pago');
                                        $rubr_des = $oIfx->f('rubr_des_rubr');
                                        $tot_prov += $pago_val;
                                        $tabla_apor .= '<tr>';
                                        $tabla_apor .= '<td  align="left" width="70%">' . $rubr_des . ':</td>';
                                        $tabla_apor .= '<td  align="right" width="30%"><strong>' . number_format($pago_val, 2, '.', ',') . '</strong></td>';
                                        $tabla_apor .= '</tr>';
                                    } while ($oIfx->SiguienteRegistro());
                                }
                            }

                            $oIfx->Free();


                            //CONSULTA DIAS DE VACACIONES DEL EMPELADO TOMADO DENTRO DEL MES
                            $sqlnove = "select nove_fec_desde, nove_fec_hasta from saenove where nove_cod_tnov=4 and nove_cod_empl='$id_empl' and 
                            nove_fec_desde between '$fecha_ini_per' and '$fecha_fin_per'";
                            $periodo_vac = '';
                            if ($oIfx->Query($sqlnove)) {
                                if ($oIfx->NumFilas() > 0) {
                                    do {

                                        $fecha_desde = date('d-m-Y', strtotime($oIfx->f('nove_fec_desde')));
                                        $fecha_hasta = date('d-m-Y', strtotime($oIfx->f('nove_fec_hasta')));
                                        $periodo_vac = Mes_func($id_mes) . ' ' . $id_anio;
                                    } while ($oIfx->SiguienteRegistro());
                                }
                            }
                            $oIfx->Free();


                            $path_img = explode("/", $empr_path_logo);
                            $count = count($path_img) - 1;
                            $url_absoluta = url_actual() . '/WebApp';
                            $path_logo_img = $url_absoluta . '/Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];


                            //FORMATO DEL REPORTE ROL INDIVIDUAL PERSONALIZADO

                            $dias_falt = ($hor_falt * 30 / $empl_jor_trab);
                            $dias_vaca = ($hor_vaca * 30 / $empl_jor_trab);
                            $dias_lice_sin_goce = ($hor_sein * 30 / $empl_jor_trab);
                            $dias_atra = ($hor_atra * 30 / $empl_jor_trab);
                            $dias_lice = ($hor_lice * 30 / $empl_jor_trab);

                            $nboleta = 1;


                            $table_rol = '<table  align="left" border="0"   style="width: 100%;  margin-top: 0px; font-size:14px;" >';
                            $table_rol .= '<tr>';
                            $table_rol .= '<td>' . espacios_rol(50) . '<b>BOLETA DE REMUNERACIONES</b>' . espacios_rol(20) . '' . Mes_func($id_mes) . ' - ' . $id_anio . '</td>';
                            $table_rol .= '</tr>';

                            $table_rol .= '<tr>';
                            $table_rol .= '<td style="font-size:11px;"><br>' . espacios_rol(50) . '<b>DEL</b>' . espacios_rol(10) . '' . date('d/m/Y', strtotime($fecha_ini_per)) . '' . espacios_rol(10) . '<b>AL</b>' . espacios_rol(10) . '' . date('d/m/Y', strtotime($fecha_fin_per)) . '
                            ' . espacios_rol(15) . '<b>FECHA DE PAGO</b>' . espacios_rol(10) . '' . date('d/m/Y', strtotime($fecha_fin_per)) . '</td>';
                            $table_rol .= '</tr>';
                            $table_rol .= '</table>';

                            $table_rol .= '<table align="left" border="0" style="width: 100%; font-size:10px;border:2px solid black; ">';
                            $table_rol .= '<tr>
                                           <td width="14%"><strong>AP. Y NOMBRES</strong></td>
                                           <td width="30%">' . $empl_nom . '</td>
                                           <td width="20%"><strong>REMUNERACION</strong></td>
                                           <td width="10%" align="right">' . number_format($sueldo, 2, '.', ',') . '</td>
                                           <td width="16%"><strong>INICIO VACAC.</strong></td>
                                           <td width="10%" align="right">' . $fecha_desde . '</td>
                                        </tr>';
                            $table_rol .= '<tr>
                                           <td ><strong>CODIGO</strong></td>
                                           <td >' . $cod_empl . '</td>
                                           <td ><strong>DIAS TRAB.</strong></td>
                                           <td align="right">' . number_format($dias_tra, 2, '.', ',') . '</td>
                                           <td ><strong>FIN DE VACAC.</strong></td>
                                           <td align="right">' . $fecha_hasta . '</td>
                                        </tr>';
                            $table_rol .= '<tr>
                                        <td ><strong>DNI</strong></td>
                                        <td >' . $cod_empl . '</td>
                                        <td ><strong>DESC. VACAC</strong></td>
                                        <td align="right">' . number_format($dias_vaca, 2, '.', ',') . '</td>
                                        <td ><strong>PERIODO VACACIONAL</strong></td>
                                        <td align="right">' . $periodo_vac . '</td>
                                     </tr>';

                            $table_rol .= '<tr>
                                     <td ><strong>FECHA ING.</strong></td>
                                     <td >' . $fecha_ingreso . '' . espacios_rol(5) . '<strong>SITUACIÓN</strong> ' . $array_estado[$cod_empl] . '</td>
                                     <td ><strong>DESC. MÉDICOS/SUBS</strong></td>
                                     <td align="right">' . number_format($dias_efer, 2, '.', ',') . '</td>
                                     <td ><strong>HORAS TRAB.</strong></td>
                                     <td align="right">' . number_format(($empl_jor_trab - $hor_vaca - $hor_efer - $hor_lice - $hor_sein - $hor_atra - $hor_falt), 2, '.', ',') . '</td>
                                  </tr>';

                            $table_rol .= '<tr>
                                  <td ><strong>FECHA CESE</strong></td>
                                  <td ></td>
                                  <td ><strong>FALTAS Y SUSP.</strong></td>
                                  <td align="right">' . number_format($dias_falt, 2, '.', ',') . '</td>
                                  <td ><strong>HRS.EXT.SIMP.25%</strong></td>
                                  <td align="right">' . number_format($hor_25, 2, '.', ',') . '</td>
                               </tr>';


                            $table_rol .= '<tr>
                                  <td ><strong>CARGO</strong></td>
                                  <td >' . $cargo . '</td>
                                  <td ><strong>LIC. SIN GOCE HABER</strong></td>
                                  <td align="right">' . number_format($dias_lice_sin_goce, 2, '.', ',') . '</td>
                                  <td ><strong>HRS.EXT.SIMP.35%</strong></td>
                                  <td align="right">' . number_format($hor_50, 2, '.', ',') . '</td>
                               </tr>';

                            $table_rol .= '<tr>
                               <td ><strong>SISTEMA PENSIONARIO</strong></td>
                               <td >' . $array_onp[$cod_empl] . '' . espacios_rol(5) . '<strong>COMISION:</b></td>
                               <td ><strong>LIC. CON GOCE HABER</strong></td>
                               <td align="right">' . number_format($dias_lice, 2, '.', ',') . '</td>
                               <td ><strong>HRS.EXT.DOBLES</strong></td>
                               <td align="right">' . number_format($hor_100, 2, '.', ',') . '</td>
                            </tr>';

                            $table_rol .= '<tr>
                               <td ><strong>C.U.S.S.P.</strong></td>
                               <td >' . $empl_car_iess . '</td>
                               <td ><strong>TARDANZAS (Horas)</strong></td>
                               <td align="right">' . number_format($dias_atra, 2, '.', ',') . '</td>
                               <td ></td>
                               <td align="right"></td>
                            </tr>';

                            $table_rol .= '<tr>
                            <td ></td>
                            <td ></td>
                            <td ><strong>TOTAL DIAS</strong></td>
                            <td align="right">' . number_format(($dias_tra + $dias_falt + $dias_vaca + $dias_efer + $dias_lice_sin_goce + $dias_lice), 2, '.', ',') . '</td>
                            <td ></td>
                            <td align="right"></td>
                         </tr>';

                            $table_rol .= '</table>';

                            //INGRESOS , DESCUENTOS , APORTES DEL EMPLEADOR


                            $table_rol .= '<table style="width: 100%; font-size:9px; margin-top: 10px;" border="0" cellspacing="0" cellpadding="0">';
                            $table_rol .= '<tr>
                                        <td  align="center" width="33.33%" ><strong>INGRESOS </strong></td>
                                        <td  align="center" width="33.33%"><strong>DESCUENTOS</strong></td>
                                        <td  align="center" width="33.33%"><strong>APORTES DEL EMPLEADOR</strong></td>
                                    </tr>';
                            $table_rol .= '<tr>
                                        <td   valign="top "width = "33.33%">
                                            <table align="center" border="0" width="100%" >                                                
                                                ' . $tabla_ingr . '
                                            </table>
                                        </td>
                                        <td  valign="top" width = "33.33%">
                                            <table  align="center" border="0" width="100%" >                                      
                                                ' . $tabla_egre . '
                                            </table>    
                                        </td>
                                        <td  valign="top" width = "33.33%">
                                        <table  align="center" border="0" width="100%" >                                      
                                            ' . $tabla_apor . '
                                        </table>    
                                    </td>
                                    </tr>';


                            $table_rol .= '<tr>
                                        <td valign="top" width = "33.33%"><br><table  style="width: 100%;" border="1" cellspacing="1" cellpadding="1">        
                                                <tr>
                                                    <td align="left" width="70%" ><strong>TOTAL INGRESOS S/.</strong></td>
                                                    <td align="right" width="30%"><strong>' . number_format($tot_ingr, 2, '.', ',') . '</strong></td>
                                                </tr>
                                            </table>    
                                        </td>
                                        <td valign="top" width = "33.33%"><br><table  style="width: 100%;" border="1"cellspacing="1" cellpadding="1">         
                                                <tr>
                                                    <td align="left" width="70%;"><strong>TOTAL DESCUENTOS S/.</strong></td>
                                                    <td align="right" width="30%;"><strong>' . number_format($egre, 2, '.', ',') . '</strong></td>
                                                </tr>
                                            </table>    
                                        </td>
                                        <td valign="top" width = "33.33%"><br><table  style="width: 100%;" border="1"cellspacing="1" cellpadding="1">         
                                                <tr>
                                                    <td align="left" width="70%;"><strong>TOTAL APORTES S/.</strong></td>
                                                    <td align="right" width="30%;"><strong>' . number_format($tot_prov, 2, '.', ',') . '</strong></td>
                                                </tr>
                                            </table>    
                                        </td>
                                    </tr>';
                            $total_recibi = $tot_ingr - $egre;
                            $table_rol .= '</table>';

                            $table_rol .= '<table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">';
                            $table_rol .= '<tr>
                                                <td  valign="top" width="33.33%"></td>
                                                <td  valign="top " width="33.33%"><table  style="width: 100%;" border="1" cellspacing="1" cellpadding="1">         
                                                                <tr>
                                                    
                                                                    <td class="fecha_letra" align="right"><strong>NETO A PAGAR ' . espacios_rol(10) . '' . number_format($total_recibi, 2, '.', ',') . '</strong></td>
                                                                
                                                                </tr>
                                                            </table>    
                                                </td>
                                                <td  valign="top" width="33.33%"></td>

                                                </tr>';
                            $table_rol .= '</table>';
                        } while ($oIfx->SiguienteRegistro());
                    }
                }

                $sql_empr = "select empr_repres from saeempr where empr_cod_empr = $id_empresa ";
                $empr_repres1 = consulta_string_func($sql_empr, 'empr_repres', $oIfxA, '');
                $sql_empr1 = "select empr_ced_repr from saeempr where empr_cod_empr = $id_empresa ";
                $empr_ced_repr = consulta_string_func($sql_empr1, 'empr_ced_repr', $oIfxA, '');

                //$table_op .="</div>";
                $legend .= ' <br><br>
                        <table style="width: 100%; " border="0" >
                            <tr>
                            <td style="width: 50%;" align="center">' . $logofirma . '</td>
                            <td style="width: 50%;" align="center"></td>
                            </tr>
                            <tr>
                                <td style="width: 50%;" align="center"><strong>______________________________________________________</strong><br>' . $jefe_rrh . '<br>' . $cargo_rrh . '</td>
                                <td style="width: 50%;" align="center"><strong>______________________________________________________</strong><br>RECIBÍ CONFORME : ' . $empl_nom . '</td>
                            </tr>
                    </table><br><br><br>';




                $documento = '';

                $original = '<table  align="left" border="0"   style="width: 100%;  margin-top: 10px; font-size:10px;" >';
                if ($empr_path_logo != '') {
                    $original .= '<tr>
                    <td align="left" width="25%">' . $empr_logo . '</td>
                    <td align="left" width="75%" style="color: black;" ><b><font style="font-size:14px;">' . $razonSocial . '</font><br>' . $dirMatriz . '<br>R.U.C.' . $ruc_empr . '
                    ' . espacios_rol(50) . 'NRO BOLETA:</b>' . espacios_rol(10) . '' . $nboleta . ' <b>ORIGINAL</b></td>
                        </tr>
                        
                        ';
                }
                $original .= '</table>';

                $copia = '<table  align="left" border="0"   style="width: 100%;  margin-top: 10px; font-size:10px;" >';
                if ($empr_path_logo != '') {
                    $copia .= '<tr>
                    <td align="left" width="25%">' . $empr_logo . '</td>
                    <td align="left" width="75%" style="color: black;" ><b><font style="font-size:14px;">' . $razonSocial . '</font><br>' . $dirMatriz . '<br>R.U.C.' . $ruc_empr . '
                    ' . espacios_rol(50) . 'NRO BOLETA:</b>' . espacios_rol(10) . '' . $nboleta . ' <b>COPIA</b></td>
                        </tr>
                        
                        ';
                }
                $copia .= '</table>';

                //VALIDAICON OFROMATO IMPRESION Y COOREO

                if ($tipo_formato == 1) {

                    if ($tipo_formato_copia == 'SI') {
                        //VERTICAL
                        $tabla = '<table style="width: 100%; margin-top: 10px;" border="0" style="border-collapse: collapse;">
                        <tr>
                        <td >' . $original . '' . $table_rol . '' . $legend . '</td>
                        </tr>
                        <tr>
                        <td >' . $copia . '' . $table_rol . '' . $legend . '</td>
                        </tr>
                        </table>
                        ';

                        //HORIZONTAL
                        /*$tabla='<table style="width: 100%; margin-top: 10px;" border="0" style="border-collapse: collapse;">
                        <tr>
                        <td width="50%">'. $original.''.$table_rol.''. $legend.'</td>
                        <td width="50%">'. $copia.''.$table_rol.''. $legend.'</td>
                        </tr>
                        </table>
                        ';*/
                    } else {

                        $tabla = $original . $table_rol . $legend;
                    }
                }


                if ($tipo_formato == 2) {

                    if ($tipo_formato_correo == 'SI') {

                        $tabla = '<table style="width: 100%; margin-top: 10px;" border="0" style="border-collapse: collapse;">
                        <tr>
                        <td >' . $original . '' . $table_rol . '' . $legend . '</td>
                        </tr>
                        <tr>
                        <td >' . $copia . '' . $table_rol . '' . $legend . '</td>
                        </tr>
                        </table>
                        ';
                    } else {

                        $tabla = $copia . $table_rol . $legend;
                    }
                }



                $documento .= $tabla;
                $documento .= '||||||||||';
                $nboleta++;
            } while ($oIfxD->SiguienteRegistro());
        } else {
            $documento .= '<div align="center"><span><font color="red" size="30%">EL EMPLEADO NO CUMPLE EL TIEMPO NECESARIO PARA LA GENERACIÓN DEL ROL</font></span></div>';
            $documento .= '||||||||||';
        }
    }


    $pdf = new TCPDF2('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetMargins(7, 4, 7, true);
    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    // set font
    $pdf->SetFont('helvetica', 'N', 8);
    $pdf->AddPage();

    $docupdf = str_replace('||||||||||', '', $documento);
    $pdf->writeHTMLCell(0, 0, '', '', $docupdf, 0, 1, 0, true, '', true);

    $ruta = DIR_FACTELEC . 'Include/archivos/' . $nombre_archivo . '.pdf';

    $pdf->Output($ruta, 'F');


    return $documento;
}


/// impresion masiva de roles
function rol_individual_formato_masivo($id_empresa = "", $id_anio = "", $id_mes = "", $array_empleados, $id_depar = "", $id_proyecto = "")
{

    //Definiciones
    global $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();


    $oIfxB = new Dbo;
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();


    $oIfxC = new Dbo;
    $oIfxC->DSN = $DSN_Ifx;
    $oIfxC->Conectar();


    $oIfxD = new Dbo;
    $oIfxD->DSN = $DSN_Ifx;
    $oIfxD->Conectar();

    $cont = count($array_empleados);
    $documento = '';
    if ($cont > 0) {

        foreach ($array_empleados as $array) {
            $id_empl = $array;
            $documento .= rol_individual_formato($id_empresa, $id_anio, $id_mes, $id_empl, $id_depar, $id_proyecto, 1);
        }
    }


    return $documento;
}

//ESPACIOS EN BLANCO
function espacios_rol($cant)
{
    $n = "";
    for ($i = 0; $i <= $cant; $i++) {
        $n .= "&nbsp;";
    }
    return $n;
}
