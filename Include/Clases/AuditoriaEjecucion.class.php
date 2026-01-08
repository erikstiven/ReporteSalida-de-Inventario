<?php

require_once(path(DIR_INCLUDE) . 'comun.lib.php');

class AuditoriaEjec
{

    var $oCon;
    var $oIfx;
    var $idEmpresa;
    var $idSucursal;
    var $idClpv;
    var $idContrato;

    function __construct($oCon, $oIfx, $idEmpresa, $idSucursal, $idClpv, $idContrato)
    {

        if (empty($idSucursal)) {
            $sql = "select id_sucursal from isp.contrato_clpv where id = $idContrato";
            $idSucursal = consulta_string_func($sql, 'id_sucursal', $oCon, 'id_sucursal');
        }

        if (empty($idClpv)) {
            $sql = "select id_clpv from isp.contrato_clpv where id = $idContrato";
            $idClpv = consulta_string_func($sql, 'id_sucursal', $oCon, 'id_clpv');
        }

        $this->oCon         = $oCon;
        $this->oIfx         = $oIfx;
        $this->idEmpresa    = $idEmpresa;
        $this->idSucursal   = $idSucursal;
        $this->idClpv       = $idClpv;
        $this->idContrato   = $idContrato;
    }

    function consultarAuditoriaEjec($id_ejecucion)
    {

        $oIfx       = $this->oIfx;
        $oCon       = $this->oCon;
        $idEmpresa  = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv     = $this->idClpv;
        $idContrato = $this->idContrato;

        $array = array();

        $sql = "SELECT * FROM isp.instalacion_ejecucion_audi a WHERE
                            a.id_empresa    = $idEmpresa AND
                            a.id_sucursal   = $idSucursal AND
                            a.id_clpv       = $idClpv AND
                            a.id_contrato   = $idContrato AND
                            a.id_iejec_audi = $id_ejecucion ";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $id_ejec_audi   = $oCon->f('id_ejec_audi');
                $id_ejecucion   = $oCon->f('id_ejecucion');
                $id_instalacion = $oCon->f('id_instalacion');
                $fecha          = $oCon->f('fecha');
                $observacion    = $oCon->f('observacion');
                $user_web       = $oCon->f('user_web');
                $fecha_server   = $oCon->f('fecha_server');
                $minv_fmov      = $oCon->f('minv_fmov');
                $minv_comp_egre = $oCon->f('minv_comp_egre');
                $minv_num_egre  = $oCon->f('minv_num_egre');
                $minv_tran_egre = $oCon->f('minv_tran_egre');
                $minv_comp_ing  = $oCon->f('minv_comp_ing');
                $minv_num_ing   = $oCon->f('minv_num_ing');
                $minv_tran_ing  = $oCon->f('minv_tran_ing');
            }
        }
        $oCon->Free();


        $array[] = array(
            $id_ejec_audi,      $id_ejecucion,     $id_instalacion,    $fecha,         $observacion,       $user_web,
            $fecha_server,      $minv_fmov,         $minv_comp_egre,    $minv_num_egre, $minv_tran_egre,    $minv_comp_ing,
            $minv_num_ing,      $minv_tran_ing
        );

        return $array;
    }

    function registraAuditoriaEjec(
        $id_ejecucion,
        $id_instalacion,
        $fecha,
        $observacion,
        $user_web,
        $minv_fmov,
        $minv_comp_egre,
        $minv_num_egre,
        $minv_tran_egre,
        $minv_comp_ing,
        $minv_num_ing,
        $minv_tran_ing
    ) {

        $oCon       = $this->oCon;
        $idEmpresa  = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv     = $this->idClpv;
        $idContrato = $this->idContrato;

        if (empty($minv_comp_egre)) {
            $minv_comp_egre = 0;
        }

        if (empty($minv_comp_ing)) {
            $minv_comp_ing = 0;
        }

        $fecha_server   = date('Y-m-d H:m:s');

        $sql_ultimo_id = "SELECT max(id_ejec_audi) as ultimo_id from isp.instalacion_materiales_audi";
        $ultimo_id = consulta_string_func($sql_ultimo_id, 'ultimo_id', $oCon, 0) + 1;


        $sql = "INSERT into isp.instalacion_ejecucion_audi (id_ejecucion,       id_empresa,         id_sucursal,        id_clpv,            id_contrato,        id_instalacion, 
                                                        fecha,              observacion,        user_web,           fecha_server,       minv_fmov,          minv_comp_egre, 
                                                        minv_num_egre,      minv_tran_egre,     minv_comp_ing,      minv_num_ing,       minv_tran_ing   )
                                                values( $id_ejecucion,     $idEmpresa,         $idSucursal,        $idClpv,            $idContrato,       '$id_instalacion',
                                                        '$fecha',           '$observacion',     '$user_web',        now(),              '$minv_fmov',      '$minv_comp_egre', 
                                                        '$minv_num_egre',   '$minv_tran_egre',  '$minv_comp_ing',   '$minv_num_ing',    '$minv_tran_ing'  )";
        //echo $sql;exit;
        $oCon->QueryT($sql);

        $sql = "select id_ejec_audi from isp.instalacion_ejecucion_audi  where 
                    id_empresa      = $idEmpresa and
                    id_sucursal     = $idSucursal and
                    id_clpv         = $idClpv and
                    id_contrato     = $idContrato and
                    id_instalacion  = $id_instalacion and
                    fecha           = '$fecha' and
                    user_web        = $user_web and
                    fecha_server    = '$fecha_server' and
                    id_ejecucion    = $id_ejecucion ";
        $idmaximo = consulta_string_func($sql, 'id_ejec_audi', $oCon, 0);

        return $idmaximo;
    }

    function registraAuditoriaEjecMat(
        $tipo_mov,
        $id_ejecucion,
        $id_ejec_audi,
        $id_instalacion,
        $id_bodega,
        $prod_cod_prod,
        $cantidad,
        $facturable,
        $user_web,
        $valor,
        $minv_num_comp,
        $minv_num_sec,
        $minv_cod_tran,
        $dmov_cod_dmov,
        $prod_nom_prod,
        $unid_cod_unid,
        $dmov_cun_dmov,
        $fecha
    ) {

        $oCon       = $this->oCon;
        $idEmpresa  = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv     = $this->idClpv;
        $idContrato = $this->idContrato;

        // $sql_ultimo_id = "SELECT max(id_ejec_audi) as ultimo_id from isp.instalacion_materiales_audi";
        // $ultimo_id = consulta_string_func($sql_ultimo_id, 'ultimo_id', $oCon, 0) + 1;


        $sql = "insert into isp.instalacion_materiales_audi (id_ejec_audi,       id_instalacion,         id_ejecucion,        id_empresa,       id_sucursal,    id_clpv,            
                                                         tipo_mov,           id_bodega,              prod_cod_prod,       cantidad,         facturable,     user_web,
                                                         fecha_server,       valor,                  minv_num_comp,       minv_num_sec,     minv_fmov,      minv_cod_tran,       
                                                         dmov_cod_dmov,      prod_nom_prod,          unid_cod_unid,       dmov_cun_dmov   )
                                                values(  $id_ejec_audi,      $id_instalacion,        $id_ejecucion,       $idEmpresa,       $idSucursal,    $idClpv,      
                                                         '$tipo_mov',        $id_bodega,            '$prod_cod_prod',     $cantidad,        '$facturable',  $user_web,
                                                         now(),              $valor,                 $minv_num_comp,      '$minv_num_sec',  '$fecha',       '$minv_cod_tran',
                                                         $dmov_cod_dmov,     '$prod_nom_prod',       $unid_cod_unid,      $dmov_cun_dmov
                                                          )";
        $oCon->QueryT($sql);

        return 'OK';
    }

    function ValorFacturableEjec($id_ejecucion,  $id_ejec_audi, $id_instalacion)
    {

        $oCon       = $this->oCon;
        $idEmpresa  = $this->idEmpresa;
        $idSucursal = $this->idSucursal;
        $idClpv     = $this->idClpv;
        $idContrato = $this->idContrato;

        $sql = "SELECT m.id_bodega, m.id_prod, m.cantidad, m.valor
                            FROM isp.instalacion_materiales m WHERE
                            m.id_instalacion = $id_instalacion AND
                            m.id_ejecucion   = $id_ejecucion AND
                            m.facturable     = 'S'                            
                            UNION                            
                SELECT i.id_bodega, i.prod_cod_prod, 
                            ( CASE
                                WHEN i.tipo_mov = 'I' THEN  i.cantidad*-1
                                WHEN i.tipo_mov = 'E' THEN i.cantidad
                                ELSE i.cantidad
                            END ) AS cantidad, i.valor
                            FROM isp.instalacion_materiales_audi i WHERE
                            i.ID_EMPRESA     = $idEmpresa AND
                            i.ID_SUCURSAL    = $idSucursal AND
                            i.ID_EJECUCION   = $id_ejecucion AND
                            i.ID_EJEC_AUDI   = $id_ejec_audi AND
                            i.ID_INSTALACION = $id_instalacion AND
                            i.cantidad > 0 AND
                            i.facturable     = 'S'
                            ORDER BY 2; ";
        $valor_fact = 0;
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $prod_cod = $oCon->f('id_prod');
                    $cantidad = $oCon->f('cantidad');
                    $valor    = $oCon->f('valor');
                    $valor_fact += $cantidad * $valor;
                } while ($oCon->SiguienteRegistro());
            }
        }


        return $valor_fact;
    }
}
