<?

function rol_individual_formato($id_empresa = 0, $id_anio = 0, $id_mes = 0, $id_empl = '', $id_depar = 0, $id_proyecto = 0, $tipo_formato=0)
{
    //Definiciones
    global $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}


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


     //PARAMETROS FORMATO 

     $sqlp="select prrh_val_prrh from saeprrh where prrh_cod_prrh='ROTIP' and prrh_cod_empr=$id_empresa";
     $tipo_formato_copia=consulta_string($sqlp,'prrh_val_prrh',$oIfx,0);


    //anio - mes
    $opAnio = 0;
    if ($id_anio == 2017) {
        $opAnio = 1;
    }
    $condicion_fecha = '';
    if ($fecha_rol != '') {
        $condicion_fecha = "and pago_fec_real='$fecha_rol'";
    }
    $nombre_archivo = 'RECIBO_PAGO_' . $id_anio . '_' . $id_mes . '_' . $id_empl;
    $sql = "select pago_fec_real, pago_cod_estr
            from saepago 
            where pago_cod_empl='$id_empl' and 
                pago_cod_empr='$id_empresa' and
                SUBSTR(cast (pago_per_pago as text), 0, 5) = '$id_anio' and
                SUBSTR(cast (pago_per_pago as text), 5, 2) = '$id_mes'  and pago_ori_gene='R'
                $condicion_fecha
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
                }
                else{
                    $empr_logo ='<span><font color="red">SIN LOGO</font></span>';
                }

                //MONEDA BASE


                $sql = "select pcon_mon_base, pcon_seg_mone from saepcon where pcon_cod_empr = $id_empresa ";
                $mone_cod = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');
        
        
        
                $sql_mone_principal = "select * from saemone
                    where mone_cod_empr = $id_empresa
                    and mone_cod_mone = $mone_cod";
        
                if ($oIfx->Query($sql_mone_principal)) {
                    if ($oIfx->NumFilas() > 0) {
                        do {
                            $moneda_principal = trim($oIfx->f('mone_des_mone'));


                        } while ($oIfx->SiguienteRegistro());
                    }
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
                    ";
                ;
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

                //  var_dump($array_prest_pen);exit;
                // EGRESOS
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
                            rubr_cod_trub = 'D' AND rubr_cod_rubr<>'RIESS' and pago_fec_real='$pago_fec_real' and pago_ori_gene='R'
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
                            rubr_cod_trub = 'D'  and pago_fec_real='$pago_fec_real' and pago_ori_gene='R'
                            $sql_empl 
                            order by pago_cod_empl";
                    //echo $sql;
                    //echo 'hola 2 ';exit;
                }


                $tot_egre= Array();
                $tabla_egre = '';
                unset($array_table_egre);
                unset($array_tmp);

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

                            $array_tmp [$tmp] = $cod_empl;
                            if ($tmp > 1) {
                                if ($array_tmp[$tmp] != $array_tmp[$tmp - 1]) {
                                    $tabla_egre = '';
                                }
                            }
                            if (($tmp == 1) && ($id_mes >= 12) && ($id_anio >= 2017) && ($ban == 0) && ($ban_anti == false)) {
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
                                    //echo $sql;exit;
                                    if ($oIfxC->Query($sql)) {
                                        $obs = $oIfxC->f('anti_obs_anti');
                                        $val = $oIfxC->f('anti_val_anti');
                                        //echo $val; exit;
                                        $ban = 1;
                                        //if($val!='0.0'){
                                        if (!empty($val) || $val != '0.0') {

                                            $tabla_egre .= '<tr>';
                                            $tabla_egre .= '<td style="font-size:75%;" width="75%" align="left">' . $obs . ':</td>';
                                            $tabla_egre .= '<td style="font-size:75%;" width="25%" align="right"><strong>' . number_format($val, 2, '.', ',') . '</strong></td>';
                                            //$tabla_egre .= '<td  align="right">&nbsp;</td>';
                                            $tabla_egre .= '</tr>';
                                        }

                                    }

                                    $tot_egre [$cod_empl] += $val;
                                    $ban_anti = true;
                                }
                            }

                            if ($sClass == 'off') $sClass = 'on'; else $sClass = 'off';
                            //var_dump($array_prest_pen);
                            //exit;
                            if (empty($array_prest_pen[$cod_empl][$rubr_cod])) {
                                //if(empty($array_prest_pen[$cod_empl])){
                                //echo 'hola tabla';

                                
                                $tabla_egre .= '<tr>';
                                $tabla_egre .= '<td style="font-size:75%;" align="left" width="75%">' . $rubr_des . ':</td>';
                                $tabla_egre .= '<td style="font-size:75%;" align="right" width="25%"><strong>' . number_format($pago_val, 2, '.', ',') . '</strong></td>';
                                // $tabla_egre .= '<td>&nbsp;</td>';
                                $tabla_egre .= '</tr>';
                            } else {

                                if ($rubr_cod == 'RPRES') {
                                    $sql = "
                                        SELECT
                                            sum(cuot_mot_capi) valor
                                            FROM
                                            saepret,
                                            saetpre,
                                            saecuot 
                                        WHERE
                                            pret_cod_tpre = tpre_cod_tpre 
                                            AND pret_cod_pret = cuot_cod_pret 
                                            AND tpre_cod_empr = pret_cod_empr 
                                            AND cuot_est_cuot = 0
                                            AND pret_cod_empl = '$cod_empl' 
                                            AND pret_cod_empr = $id_empresa
                                            AND tpre_cod_rubr = 'RPRES'
                                        ";

                                    $saldo_prestamo = consulta_string($sql, 'valor', $oIfxB, 0);
                                }


                                $tabla_egre .= '<tr>';
                                $tabla_egre .= '<td style="font-size:75%;" align="left" width="75%">' . $rubr_des . ':</td>';
                                $tabla_egre .= '<td style="font-size:75%;" align="right" width="25%"><strong>' . number_format($pago_val, 2, '.', ',') . '</strong></td>';
                                //$tabla_egre .= '<td >' . number_format($array_prest_pen[$cod_empl][$rubr_cod], 2, '.', ',') . '</td>';
                                $tabla_egre .= '</tr>';

                            }

                            //echo $tabla_egre;
                            //exit;

                            $array_table_egre[$cod_empl] = $tabla_egre;

                            $tot_egre [$cod_empl] += $pago_val;
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
                $sql_empl 
                $sql_depar
                group by 1
                order by pago_cod_empl";
                //echo $sql;exit;
                $ingr = 0;
                $egre = 0;
                if ($oIfx->Query($sql)) {
                    if ($oIfx->NumFilas() > 0) {
                        do {
                            $cod_empl = $oIfx->f('pago_cod_empl');
                            $tabla_egre = $array_table_egre[$cod_empl];

                            $egre = $tot_egre[$cod_empl];


                            $sql = "select empl_ape_nomb , empl_sal_sala, empl_jor_trab, empl_val5_empl, empl_num_ctas , empl_cod_banc
                                from saeempl 
                                where empl_cod_empr = $id_empresa and 
                                empl_cod_empl = '$cod_empl' ";
                            $empl_nom = consulta_string_func($sql, 'empl_ape_nomb', $oIfxA, '');
                            $salario = consulta_string_func($sql, 'empl_sal_sala', $oIfxA, '');
                            $empl_jor_trab = consulta_string_func($sql, 'empl_jor_trab', $oIfxA, '');
                            $empl_val5_empl = consulta_string_func($sql, 'empl_val5_empl', $oIfxA, '');
                            $empl_num_ctas = consulta_string_func($sql, 'empl_num_ctas', $oIfxA, '');
                            $empl_cod_banc = consulta_string_func($sql, 'empl_cod_banc', $oIfxA, '');

                            $fecha_consulta=$id_anio.'-'.$id_mes.'-01';
                            $fecha_consulta=(date("Y-m-t",(strtotime($fecha_consulta))));

                            $sql = "SELECT hisa_fec_apli, hisa_val_sala FROM saehisa where hisa_cod_empr='$id_empresa' and his_cod_empl='$cod_empl' and hisa_fec_apli <= '$fecha_consulta' order by hisa_fec_apli DESC limit 1";
                            //echo $sql; exit;
                            $hisa_val_sala  = consulta_string_func($sql, 'hisa_val_sala', $oIfxA, 0);

                            if ($hisa_val_sala>0) {
                                $salario = $hisa_val_sala;
                            }else{
                                $sql = "SELECT hisa_fec_apli, hisa_val_sala FROM saehisa where his_cod_empl='$cod_empl' order by hisa_fec_apli ASC limit 1";
                                $hisa_val_sala  = consulta_string_func($sql, 'hisa_val_sala', $oIfxA, 0);
                                if ($hisa_val_sala>0) {
                                    $salario = $hisa_val_sala;
                                }
                            }

                            $banco='';
                            if (trim($empl_cod_banc)!='') {
                                $sql = "select banc_nom_banc
                                from saebanc
                                where banc_cod_banc= $empl_cod_banc";
                                $banco = consulta_string_func($sql, 'banc_nom_banc', $oIfxA, '');

                                if ($opAnio == 0) {
                                    $sueldo = $salario;
                                } else {
                                    $sueldo = $empl_val5_empl;
                                }
                            }else{
                                $sueldo = $salario;
                            }


                            $sql = "select estr_des_estr, esem_fec_ingr  from saeesem, saeestr where
                                    estr_cod_estr = esem_cod_estr and
                                    estr_cod_empr = $id_empresa and
                                    esem_cod_empl = '$cod_empl' and 
                                    esem_cod_empr = $id_empresa and
                                    esem_fec_ingr <= '$fecha_consulta'
                                    order by 2 desc limit 1";

                            $cargo = consulta_string_func($sql, 'estr_des_estr', $oIfxA, '');

                            $fech_ing_cargo=consulta_string_func($sql, 'esem_fec_ingr', $oIfxA, '');

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
                                    $hor_atra = ($oIfxB->f('amem_hor_atra') / 8);
                                    $hor_falt = ($oIfxB->f('amem_hor_falt') / 8);
                                    $hor_perm = ($oIfxB->f('amem_hor_perm'));
                                    $hor_25 = $oIfxB->f('amem_hor_e025');
                                    $hor_50 = $oIfxB->f('amem_hor_e050');
                                    $hor_100 = $oIfxB->f('amem_hor_e100');
                                    $hor_vaca = $oIfxB->f('amem_hor_vaca');
                                    $hor_mater = $oIfxB->f('amem_hor_mate');
                                    $hor_sein = ($oIfxB->f('amem_hor_sein') / 8);
                                    //$hor_sein= ($oIfxB->f('amem_hor_mate')/8);
                                    $hor_lice = vacios($oIfxB->f('amem_hor_lice'), '0.0');
                                    $hor_efer = vacios($oIfxB->f('amem_hor_efer'), '0.0');
                                    $hor_trab = ($hor_trab);

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
                            rubr_cod_trub = 'I' and rubr_cod_rubr<>'RSUEN' AND   rubr_cod_rubr<>'RIEAS' and pago_fec_real='$pago_fec_real' and pago_ori_gene='R'
                            $sql_empl  
                            order by rubr_des_alia";


                            $tabla_ingr = '';
                            $tmp = 1;
                            if ($oIfx->Query($sql)) {
                                if ($oIfx->NumFilas() > 0) {
                                    do {
                                        $cod_empl = $oIfx->f('pago_cod_empl');
                                        $rubr_cod = $oIfx->f('pago_cod_rubr');
                                        $pago_per = $oIfx->f('pago_per_pago');
                                        $pago_val = $oIfx->f('pago_val_pago');
                                        $rubr_des = $oIfx->f('rubr_des_rubr');
                                        $rubr_tipo = $oIfx->f('rubr_cod_trub');
                                        $array_tmp [$tmp] = $cod_empl;

                                        /*if($rubr_cod == $rubroSueldo){
                                        $salario = $pago_val;
                                    }*/

                                        if ($tmp > 1) {
                                            if ($array_tmp[$tmp] != $array_tmp[$tmp - 1]) {
                                                $tabla_ingr = '';
                                            }
                                        }

                                        $fecha_ingreso='';
                                        $sql="SELECT min(esem_fec_ingr) as fecha FROM saeesem WHERE esem_cod_empl='$cod_empl' and esem_fec_sali is null";

                                        if($oIfxB->Query($sql)){
                                            if($oIfxB->NumFilas()>0){
                                                $fecha_ingreso=$oIfxB->f('fecha');
                                            }
                                        }



                                        
                                        //$fecha_ingreso_cargo = ingreso_fecha_cargo($cod_empl);
                                        $ban = 0;
                                        $tabla_ingr .= '<tr>';
                                        $tabla_ingr .= '<td style="font-size:75%;" width="75%" align="left">' . $rubr_des . ': ';
                                        if ($rubr_cod == 'RHECC') {
                                            $tabla_ingr .= ' <strong>'. $hor_50 . ' horas </strong></td>';
                                            $ban = 1;
                                        }

                                        if ($rubr_cod == 'RHEVC') {
                                            $tabla_ingr .= ' <strong>'. $hor_25 . ' horas </strong></td>';
                                            $ban = 1;
                                        }

                                        if ($rubr_cod == 'RHECI') {
                                            $tabla_ingr .= ' <strong>'. $hor_100 . ' horas </strong></td>';
                                            $ban = 1;
                                        }
                                        
                                        if ($rubr_cod == 'RSUEL') {
                                            $dias_tra=$hor_trab*30/$empl_jor_trab;
                                            $tabla_ingr .= ' <strong>'. $dias_tra . ' dias </strong></td>';
                                            $ban = 1;
                                        }



                                        if ($rubr_cod == 'RLICE') {
                                            //$tabla_ingr .='<td align="left">'.$hor_lice.'</td>';
                                            //$tabla_ingr .='<td align="left"></td>';
                                            $dias_lice = ($hor_lice / 8);
                                            //$tabla_ingr .= '<td align="left">' . $dias_lice . '</td>';
                                            //$tabla_ingr .= '<td align="left"></td>';
                                            $ban = 1;
                                        }
                                        if ($rubr_cod == 'RLICD') {
                                            //$tabla_ingr .='<td align="left">'.$hor_efer.'</td>';
                                            //$tabla_ingr .='<td align="left"></td>';
                                            $dias_efer = ($hor_efer / 8);
                                            // $tabla_ingr .= '<td align="left">' . $dias_efer . '</td>';
                                            // $tabla_ingr .= '<td align="left"></td>';
                                            $ban = 1;
                                        }
                                        if ($rubr_cod == 'RVACP') {
                                            //$tabla_ingr .='<td align="left">'.$hor_vaca.'</td>';
                                            //$tabla_ingr .='<td align="left"></td>';
                                            $dias_vaca = ($hor_vaca / 8);
                                            //$tabla_ingr .= '<td align="left">' . $dias_vaca . '</td>';
                                            //$tabla_ingr .= '<td align="left"></td>';
                                            $ban = 1;
                                        }
                                        if ($rubr_cod == 'RMATE') {
                                            //$tabla_ingr .='<td align="left">'.$hor_vaca.'</td>';
                                            //$tabla_ingr .='<td align="left"></td>';
                                            $dias_mater = ($hor_mater / 8);
                                            //$tabla_ingr .= '<td align="left">' . $dias_mater . '</td>';
                                            // $tabla_ingr .= '<td align="left"></td>';
                                            $ban = 1;
                                        }

                                        if ($ban == 0) {
                                            $tabla_ingr .= '</td><td style="font-size:75%;" align="right" width="25%"> <strong>' . number_format($pago_val, 2, '.', ',') . '</strong></td>';
                                        }else {
                                            $tabla_ingr .= ' <td style="font-size:75%;" align="right" width="25%"><strong>' . number_format($pago_val, 2, '.', ',') . '</strong></td>';
                                        }
                                        $tabla_ingr .= '</tr>';

                                        $array_table_ingr [$cod_empl] = $tabla_ingr;


                                        $tot_ingr += $pago_val;
                                        $tmp++;
                                    } while ($oIfx->SiguienteRegistro());
                                }
                            }


                            $path_img = explode("/", $empr_path_logo);
                            $count = count($path_img) - 1;
                            $url_absoluta = url_actual() . '/WebApp';
                            $path_logo_img = $url_absoluta . '/Include/Clases/Formulario/Plugins/reloj/' . $path_img[$count];

                            //FORMATO DEL REPORTE ROL INDIVIDUAL PERSONALIZADO


                            $table_rol = '<table  align="left" border="0"   style="width: 100%;  margin-top: 10px; " >';
                            if ($empr_path_logo != '') {
                                $table_rol .= '<tr>
                                <td align="left">' . $empr_logo . '</td>
                                <td align="right" style="color: black;" ><strong>ROL DE PAGOS<br>Per&iacute;odo '.strtoupper( Mes_func($id_mes)).' ' . $id_anio . '</strong></td>
                                    </tr>
                                    
                                    <tr><td colspan="2"  ><hr></td></tr>';
                            }
                            $table_rol .= '</table>';





                            $table_rol .= '<br><br><table align="left" width="100%" style="width: 100%; border:1px solid black; border-radius: 5px; margin-top: 20px;">';
                            $table_rol .= '<tr>
                                              <td  bgcolor="black" style="width:100%"><font color="white">'.$empl_nom.'</font></td>
                                         </tr>';
                            $table_rol .='</table>';

                            $table_rol .= '<br><br><table bgcolor="#C9C2C1" align="left" width="100%" style="width: 100%; border:3px solid black; ">';
                            $table_rol .='<tr>
                                           <td style="font-size:90%;"><strong>N° C&eacute;dula</strong></td>
                                           <td style="font-size:90%;">'.$cod_empl.'</td>
                                           <td style="font-size:90%;"><strong>D&iacute;as per&iacute;odo</strong></td>
                                           <td style="font-size:90%;">30.00</td>
                                        </tr>';
                            $table_rol .='<tr>
                                        <td style="font-size:90%;"><strong>Sueldo B&aacute;sico</strong></td>
                                        <td style="font-size:90%;">'.number_format($sueldo, 2, '.', ',').'</td>
                                        <td style="font-size:90%;"><strong>Cargo</strong> '.$cargo.'</td>
                                     </tr>';

                            $fecha_uno = strtotime($fecha_ingreso);
                            $fecha_dos = strtotime($fech_ing_cargo);

                            if($fecha_uno > $fecha_dos){
                                $fecha_ingreso=$fech_ing_cargo;
                                //echo "La fecha de iingreso a la empmresa es mayor a la fecha de cargo";
                            }

                            $table_rol .='<tr>
                                        <td style="font-size:90%;"><strong>Fecha Ingreso Empresa</strong></td>
                                        <td style="font-size:90%;">'.$fecha_ingreso.'</td>
                                        <td><strong>Jornada Trabajo</strong></td>
                                        <td>'.$empl_jor_trab.'</td>
                                     </tr>';

                            $table_rol .='<tr>
                                        <td style="font-size:90%;"><strong>Fecha Ingreso Cargo</strong></td>
                                        <td style="font-size:90%;" colspan="3">'.$fech_ing_cargo.'</td>
                                        
                                     </tr>';

                            $table_rol .='</table>';
//INGRESOS Y EGRESOS

                            $table_rol .= '<br><br><table style="width: 100%; margin-top: 10px;" border="0" style="border-collapse: collapse;">';
                            $table_rol .= '<tr>
                                        <td style="font-size:80%;" align="center" width="50%" ><strong>_______________ INGRESOS _______________</strong></td>
                                        <td style="font-size:80%;" align="center" width="50%"><strong>_______________ EGRESOS _______________</strong></td>
                                    </tr>';
                            $table_rol .= '<tr>
                                        <td   valign="top" style="width: 50%;">
                                            <table align="center" border="0" width="100%" >                                                
                                                ' . $tabla_ingr . '
                                            </table>
                                        </td>
                                        <td  valign="top" style="width: 50%;">
                                            <table  align="center" border="0" width="100%" >                                      
                                                ' . $tabla_egre . '
                                            </table>    
                                        </td>
                                    </tr>';
                            $table_rol .= '<tr>
                                    <td colspan="2" style="font-size:80%;" align="center"><strong>___________________________________________________________________________________<br></strong></td>
                                  
                                </tr>';

                            $table_rol .= '<tr>
                                        <td valign="top" style="width: 50%;">
                                            <table bgcolor="yellow" style="width: 100%; border:2px solid black; border-radius: 2px;">        
                                                <tr>
                                                    <td align="left" width="70%" style="font-size:80%;"><strong>TOTAL INGRESOS:</strong></td>
                                                    <td align="right" width="30%" style="font-size:80%;"><strong>' . number_format($tot_ingr, 2, '.', ',') . '</strong></td>
                                                </tr>
                                            </table>    
                                        </td>
                                        <td valign="top" style="width: 50%;">
                                            <table bgcolor="yellow" style="width: 100%; border:2px solid black; border-radius: 2px;">         
                                                <tr>
                                                    <td align="left" width="70%;" style="font-size:80%;"><strong>TOTAL EGRESOS:</strong></td>
                                                    <td align="right" width="30%;" style="font-size:80%;"><strong>' . number_format($egre, 2, '.', ',') . '</strong></td>
                                                </tr>
                                            </table>    
                                        </td>
                                    </tr>';
                            $total_recibi = $tot_ingr - $egre;
                            $table_rol .= '</table>';

                            $table_rol .= '<br><br><table style="width: 100%; margin-top: 10px;" border="0" style="border-collapse: collapse;">';
                            $table_rol .= '<tr bgcolor="#C9C2C1">
                                       
                                                    <td style="font-size:90%;" class="fecha_letra" align="left" width="80%;"><strong>NETO A RECIBIR PER&Iacute;ODO '.strtoupper( Mes_func($id_mes)).' ' . $id_anio . '</strong></td>
                                                    <td style="font-size:90%;" class="fecha_letra" align="right" width="20%;"><strong>' . number_format($total_recibi, 2, '.', ',') . '</strong></td>
                                                </tr>';
                            $table_rol .= '</table>';
                            $formatter = new EnLetras();
                            $valtotal=$formatter->ValorEnLetrasMonePeru($total_recibi, $moneda_principal);

                            $table_rol .= '<br><br><table style="width: 100%; margin-top: 10px;" border="0" style="border-collapse: collapse;">';
                            $table_rol .= '<tr>
                                                <td style="font-size:90%;" class="fecha_letra" align="left" width="100%;"><strong>SON: '.strtoupper($valtotal).'</strong></td>
                                            </tr>';
                            if ($saldo_prestamo!=0) {
                                $table_rol .= '<tr>
                                                <td style="font-size:90%;" class="fecha_letra" align="left" width="100%;"><strong>SALDO PRESTAMO: ' . $saldo_prestamo . '</strong></td>
                                            </tr>';
                            }

                            $table_rol .= '</table>';


                            $table_rol .= '<br><br><table style="width: 100%; margin-top: 10px;" border="0" style="border-collapse: collapse;">';
                            $table_rol .= '<tr>
                                                    <td style="font-size:70%;" class="fecha_letra" align="left" width="100%;"><p align="justify">Certifico que he recibido a entera satisfacción los valores contenidos en el presente comprobante de pago de remuneraciones,
                                                    valor que ha sido acreditado en mi cuenta de BANCO '.$banco.' número: '.$empl_num_ctas.', por lo que no tengo ningún reclamo</p></td>
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
                $legend .= ' <br><br><br><br><br>
                        <table style="width: 100%; " border="0" >
                            <tr>
                                
                                <td style="width: 50%;" align="center">
                                    _______________________________ <br>
                                        TALENTO HUMANO<br><br>
                                    
                                </td>
                                <td style="width: 50%;" align="center">
                                    _______________________________ <br>
                                        RECIBÍ CONFORME <br>CI:' . $cod_empl . '<br> FECHA:'.date('d-m-Y').'<br><br> 
                                </td>
                            </tr>
                    </table>';

                $emisor=' <table style="width: 100%; " border="0" >
                    <tr>
                    <td style="width: 100%;" align="center"><strong>ORIGINAL EMISOR</strong></td>
                    </tr>
                    </table>';

                $receptor=' <table style="width: 100%; " border="0" >
                    <tr>
                    <td style="width: 100%;" align="center"><strong>COPIA RECEPTOR</strong></td>
                    </tr>
                    </table>';


                $documento .= '<page backimgw="10%" backtop="10mm" backbottom="10mm" backleft="10mm" backright="10mm">';

                
                  if($tipo_formato_copia=='SI'){

                    $tabla='<table style="width: 100%; margin-top: 10px;" border="0" style="border-collapse: collapse;">
                    <tr>
                        <td valign="top" width="50%" >'.$table_rol.''. $legend.''. $emisor.'</td>';
                        $tabla .= '        <td valign="top" style="width: 2%;"></td>
                        <td valign="top" width="50%" >' . $table_rol . '' . $legend . '' . $receptor . '</td>';
                        $tabla.='</tr>
                        </table>
                        ';
                
                }
                else{

                    $tabla='<table style="width: 100%; margin-top: 10px;" border="0" style="border-collapse: collapse;">
                    <tr>
                        <td valign="top">'.$table_rol.''. $legend.''. $emisor.'</td>';
                       
                        $tabla.='</tr>
                        </table>
                        ';

                }
                /*$documento =<<<EOD
                $tabla
                EOD;*/
                // $documento .= $table_op;
                $documento .= $tabla;
                $documento .= '</page>||||||||||';

            } while ($oIfxD->SiguienteRegistro());
        }
        else{
            $documento='<div align="center"><span><font color="red" size="30%">EL EMPLEADO NO CUMPLE EL TIEMPO NECESARIO PARA LA GENERACIÓN DEL ROL</font></span></div>';
        }
    }


    $html2pdf = new HTML2PDF('P', 'A3', 'fr');
    $doc=str_replace('||||||||||','',$documento);
    $html2pdf->WriteHTML($doc);
    $ruta = DIR_FACTELEC . 'Include/archivos/' . $nombre_archivo . '.pdf';
    $html2pdf->Output($ruta, 'F');
    $rutaPdf = $ruta;

    return $documento;

}


/// impresion masiva de roles
function rol_individual_formato_masivo($id_empresa = "", $id_anio = "", $id_mes = "", $array_empleados , $id_depar = "", $id_proyecto = "")
{

    //Definiciones
    global $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

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

    $cont=count($array_empleados);
    $documento='';
    if($cont>0){

        foreach ($array_empleados as $array){
            $id_empl=$array;
            $documento .= rol_individual_formato($id_empresa, $id_anio, $id_mes, $id_empl, $id_depar, $id_proyecto,1);

        }
    }


    return $documento;


}

//ESPACIOS EN BLANCO
function espacios_rol($cant)
{
    $n="";
    for ($i=0; $i <=$cant ; $i++) {
        $n.="&nbsp;";
    }
    return $n;
}


?>