<?php

class IntegracionComercial
{
    /**
     * Clase para validar la tabla de la integracion y columnas adicionales
    */

    private $oIfx;
    private $oCon;
    private $pais_codigo_inter;
    private $idempresa;
    private $idusuario;

    function __construct($oIfx, $oCon, $idempresa,$idusuario = 0)
    {
        $this->oIfx = $oIfx;
        $this->oCon = $oCon;
        $this->idempresa = $idempresa;
        $this->idusuario = $idusuario;


        $this->tabla_integraciones();//crea la tabla si no existe

        $sql = "SELECT 
                    pais_codigo_inter 
                FROM saeempr 
                INNER JOIN saepais on empr_cod_pais = pais_cod_pais
                and empr_cod_empr = $idempresa";

        if ($this->oCon->Query($sql)) {
            if ($this->oCon->NumFilas() > 0) {
                do {
                    $this->pais_codigo_inter    = $this->oCon->f('pais_codigo_inter');
                } while ($this->oCon->SiguienteRegistro());
            }
        }
        $this->oCon->Free();
    }

    public function obtener_integracion($empresa_id=0,$nombre_integracion='',$ambiente = '',$column_target='*'){

        $filtro_nombre = !empty($nombre_integracion)?" and nombre_integracion = '$nombre_integracion' ":"";
        $filtro_ambiente = !empty($ambiente)?" and ambiente = '$ambiente' ":"";       

        $column_target = (empty($column_target) || $column_target == '*')?"estado_sn,id,descripcion,nombre_integracion,ambiente,url_api,request_autorizacion,tipo_api,token":$column_target;

        $sql = "SELECT 
                    $column_target
                FROM comercial.integraciones
                where empresa_id = '$empresa_id'
                $filtro_nombre
                $filtro_ambiente
                ";

        $integracion_data = [];
        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {
                do {
                    $id_integracion_tmp         = $this->oIfx->f('id')?$this->oIfx->f('id'):0;
                    if(!empty($column_target) && $id_integracion_tmp){
                        $column_target_tmp = explode(',',$column_target);
                        $array1 = [];
                        foreach ($column_target_tmp as $key => $column_indi) {
                            $array1["$column_indi"] = $this->oIfx->f("$column_indi")?$this->oIfx->f("$column_indi"):'';
                        }
                        $integracion_data[$id_integracion_tmp] = $array1;
                    }
                } while ($this->oIfx->SiguienteRegistro());

            }   
        }
        $this->oIfx->Free();

        return $integracion_data;

    } 

    public function obtener_integracion_config($id_integracion,$id_integracion_config,$empresa_id,$column_target='*'){

        $filtro_id_integracion = !empty($id_integracion)?" and id_integracion = '$id_integracion' ":"";
        $filtro_id_integracion_config = !empty($id_integracion_config)?" and id = '$id_integracion_config' ":"";

        $column_target = (empty($column_target) || $column_target == '*')?"id,id_integracion,estado,clave,valor,descripcion":$column_target;

        $sql = "SELECT 
                    $column_target
                FROM comercial.integraciones_configuracion
                where id_empresa = '$empresa_id'
                and estado = 'S'
                $filtro_id_integracion
                $filtro_id_integracion_config
                ";
        
        $integracion_data = [];
        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {
                do {
                    $id_integracion_tmp         = $this->oIfx->f('id')?$this->oIfx->f('id'):0;
                    if(!empty($column_target) && $id_integracion_tmp){
                        $column_target_tmp = explode(',',$column_target);
                        $array1 = [];
                        foreach ($column_target_tmp as $key => $column_indi) {
                            $array1["$column_indi"] = $this->oIfx->f("$column_indi")?$this->oIfx->f("$column_indi"):'';
                        }
                        $integracion_data[$id_integracion_tmp] = $array1;
                    }
                } while ($this->oIfx->SiguienteRegistro());

            }   
        }
        $this->oIfx->Free();

        return $integracion_data;

    } 

    public function obtener_integracion_by_id($empresa_id,$id_integracion,$column_target=''){

        $column_target = (empty($column_target) || $column_target == '*')?"estado_sn,id,descripcion,nombre_integracion,ambiente,url_api,request_autorizacion,tipo_api,token":$column_target;

        $filtro_id = !empty($id_integracion)?" and id = '$id_integracion' ":"";
        $sql = "SELECT 
                    $column_target
                FROM comercial.integraciones
                where empresa_id = '$empresa_id'
                $filtro_id
                order by nombre_integracion asc, fecha_creacion asc,fecha_modificacion asc
                ";
        
        $integracion_data = [];
        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {
                do {
                    $id_integracion_tmp         = $this->oIfx->f('id')?$this->oIfx->f('id'):0;
                    if(!empty($column_target) && $id_integracion_tmp){
                        $column_target_tmp = explode(',',$column_target);
                        $array1 = [];
                        foreach ($column_target_tmp as $key => $column_indi) {
                            $array1["$column_indi"] = $this->oIfx->f("$column_indi")?$this->oIfx->f("$column_indi"):'';
                        }
                        $integracion_data[$id_integracion_tmp] = $array1;
                    }
                } while ($this->oIfx->SiguienteRegistro());

            }   
        }
        $this->oIfx->Free();

        return $integracion_data;

    } 

    private function tabla_integraciones(){
        $table_name = "integraciones";
        $table_schema = "comercial";
        $pk_column_name = "id";
  
        $sql = "CREATE TABLE $table_schema.$table_name (
                            id serial,
                            empresa_id int4,
                            estado_sn varchar(2),
                            nombre_integracion varchar(100),
                            descripcion text,
                            fecha_creacion timestamp,
                            fecha_modificacion timestamp,
                            fecha_expiracion timestamp,
                            ambiente text,
                            url_api text,
                            auth_autorizacion text,
                            request_autorizacion text,
                            tipo_api text,
                            usuario text,
                            clave text,
                            token text,
                            token_jwt text,
                            token_expira text,
                            token_tiempo text,
                            parametro_auth1 text,
                            parametro_auth2 text,
                            parametro_auth3 text,
                            parametro_auth4 text,
                            parametro_request1 text,
                            parametro_request2 text,
                            parametro_request3 text,
                            parametro_request4 text,
                            otro_parametro1 text,
                            otro_parametro2 text,
                            otro_parametro3 text
                            )";
        $add_column_array = array("descripcion" => "text");
        $update_column_array = array();

        $this->create_table_general($table_name, $table_schema, $pk_column_name, $sql, $add_column_array, $update_column_array,[], ($this->oIfx), $oReturn='');   

        $current_table_data = array(
            "table_name"=>$table_name,
            "table_schema"=>$table_schema,
            "column_name"=>$pk_column_name
        );
        $this->tabla_integraciones_configuracion($current_table_data);        
            
    }

    private function tabla_integraciones_configuracion($current_table_data){
        $table_name = "integraciones_configuracion";
        $table_schema = "comercial";
        $pk_column_name = "id";
        $fk_column_name = "id_integracion";
  
        $sql = "CREATE TABLE $table_schema.$table_name (
                            id serial,
                            id_integracion int4,
                            id_empresa int4,
                            estado varchar(2),
                            clave text,
                            valor text,
                            descripcion text,
                            fecha_creacion timestamp,
                            fecha_modificacion timestamp
                            )";
        $add_column_array = array();
        $update_column_array = array();
        $foreign_column_array = $current_table_data?array(array("parent"=> $current_table_data,
                                                          "child"=> array(
                                                                        "table_name"=>$table_name,
                                                                        "table_schema"=>$table_schema,
                                                                        "column_name"=>$fk_column_name
                                                            ),
                                                          "constraint_name"=>"fk_".$table_name.'_'.$current_table_data['table_name']."",
                                                          "constraint_type"=>"FOREIGN KEY",
                                                          "constraint_unique_name"=>$table_name.'_'.$pk_column_name."_unique_key"
                                        )):array();
        $this->create_table_general($table_name, $table_schema, $pk_column_name, $sql, $add_column_array, $update_column_array,$foreign_column_array, ($this->oIfx), $oReturn);
            
    }

    private function obtener_parametros($empresa_id,$sucursal_id,$nse,$tdoc='FAC',$fecha_doc){

        $fecha_servidor = date("Y-m-d");


        $sql = "select
                    COALESCE(para_sec_usu,'N') as para_sec_usu, 
                    para_pro_bach ,
                    para_fac_cxc, 
                    COALESCE(para_sec_fac::INTEGER,0) as para_sec_fac,
                    para_pre_fact, 
                    para_fac_trans, 
                    para_cod_tarj, 
                    para_punt_emi, 
                    para_ndb_cxc 
                from saepara
                where para_cod_empr = '$empresa_id'
                and para_cod_sucu = '$sucursal_id'";

        if ($this->oIfx->Query($sql)) {
            if ($this->oIfx->NumFilas() > 0) {
                $j = 0;
                do {
                    $para_sec_usu   = $this->oIfx->f('para_sec_usu');
                    $para_pro_bach  = $this->oIfx->f('para_pro_bach');
                    $para_fac_cxc   = $this->oIfx->f('para_fac_cxc');
                    $para_sec_fac   = $this->oIfx->f('para_sec_fac');
                    $para_pre_fact  = $this->oIfx->f('para_pre_fact');

                    $para_fac_trans = $this->oIfx->f('para_fac_trans');
                    $para_cod_tarj  = $this->oIfx->f('para_cod_tarj');
                    $para_punt_emi  = $this->oIfx->f('para_punt_emi');
                    $para_ndb_cxc   = $this->oIfx->f('para_ndb_cxc');

                } while ($this->oIfx->SiguienteRegistro());
            }
        }
        $this->oIfx->Free();

        if($para_punt_emi == 'S'){
            // emifa activado
            $sql = "SELECT 
                        emifa_auto_emifa, 
                        emifa_auto_desde, 
                        emifa_auto_hasta, 
                        emifa_fec_ini, 
                        emifa_fec_fin 
                    from saeemifa 
                    where 
                        emifa_tip_doc='$tdoc' and 
                        emifa_est_emifa='S' and 
                        emifa_cod_pto='$nse' and 
                        emifa_fec_ini>='$fecha_doc' and
                        emifa_fec_fin <='$fecha_doc' ";

        }else{
            // aufa activado

             $sql = "SELECT 
                        aufa_nse_fact,aufa_nau_fact,aufa_ffi_fact FROM saeaufa 
                    WHERE 
                        aufa_cod_empr = $empresa_id and 
                        aufa_cod_sucu = $sucursal_id and 
                        aufa_est_fact = 'A' and 
                        aufa_ffi_fact >= '$fecha_doc' and 
                        aufa_fin_fact <= '$fecha_doc'";

        }

    }

    public function registrar_integracion($id_integracion,$empresa_id,$estado_sn,$nombre_integracion,$descripcion,$ambiente='',$url_api,$tipo_api,$auth_autorizacion='',$request_autorizacion='',$usuario,$clave,$token,$token_edit_sn,$token_jwt,$token_expira,$token_tiempo,$sql_inyection = []){
        try{
            $this->oIfx->QueryT('BEGIN;');

            $filtro_ambiente = !empty($ambiente)?" and ambiente = '$ambiente' ":"";
            $filtro_nombre_integracion = !empty($nombre_integracion)?" and nombre_integracion = '$nombre_integracion' ":"";

            $filtro_integracion = $filtro_ambiente.$filtro_nombre_integracion ;
            if($id_integracion){
                $filtro_integracion = " and id = $id_integracion";
            }
            $sql = "SELECT * 
                    FROM comercial.integraciones 
                    WHERE empresa_id = '$empresa_id' 
                    $filtro_integracion
                    limit 1
                    ";
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $id_integracion             = $this->oIfx->f('id');
                        $empresa_id_int             = $this->oIfx->f('empresa_id');
                        $estado_sn_int              = $this->oIfx->f('estado_sn');
                        $nombre_integracion_int     = $this->oIfx->f('nombre_integracion');
                        $descripcion_int            = $this->oIfx->f('descripcion');
                        $fecha_creacion_int         = $this->oIfx->f('fecha_creacion');
                        $ambiente_int               = $this->oIfx->f('ambiente');
                        $url_api_int                = $this->oIfx->f('url_api');
                        $tipo_api_int               = $this->oIfx->f('tipo_api');
                        $auth_autorizacion_int      = $this->oIfx->f('auth_autorizacion');
                        $request_autorizacion_int   = $this->oIfx->f('request_autorizacion');
                        $usuario_int                = $this->oIfx->f('usuario');
                        $clave_int                  = $this->oIfx->f('clave');
                        $token_int                  = $this->oIfx->f('token');
                        $token_jwt_int              = $this->oIfx->f('token_jwt');
                        $token_expira_int           = $this->oIfx->f('token_expira');
                        $token_tiempo_int           = $this->oIfx->f('token_tiempo');

                    } while ($this->oIfx->SiguienteRegistro());
                }
            }

            $nombre_integracion = $nombre_integracion?$nombre_integracion:$nombre_integracion_int;
            $ambiente = $ambiente?$ambiente:$ambiente_int;
            $descripcion = $descripcion?$descripcion:$descripcion_int;
            $fecha_servidor_this = "'".date("Y-m-d H:i:s")."'";               


            $inset_aditional = [];
            $update_aditional = [];
            if(!empty($sql_inyection)){
                $inset_aditional = $sql_inyection['insert'];
                $update_aditional = $sql_inyection['update'];
            }

            if($id_integracion > 0){
                // update

                $adicional_update = '';
                foreach ($update_aditional as $key => $update_indi) {
                    $column = $update_indi['column'];
                    $data = $update_indi['data'];
                    if($column){
                        $adicional_update .= ",$column = '$data'";
                    }
                }

                $update_token_jwt       = !empty($token_jwt)?", token_jwt = '$token_jwt'":"";
                $update_token_expira    = !empty($token_expira)?" ,token_expira = '$token_expira'":"";
                $update_token_tiempo    = !empty($token_tiempo)?" ,token_tiempo = '$token_tiempo'":"";
                $update_tipo_api        = !empty($tipo_api)?" ,tipo_api = '$tipo_api'":"";

                $update_estado_sn       = !empty($estado_sn)?" ,estado_sn = '$estado_sn'":"";

                $update_auth_autorizacion = !empty($auth_autorizacion)?" ,auth_autorizacion = '$auth_autorizacion'":"";
                $update_request_autorizacion = !empty($request_autorizacion)?" ,request_autorizacion = '$request_autorizacion'":"";


                $token_data = ($token_edit_sn=='S')?" token                   = '$token',":"";

                $sql = "UPDATE comercial.integraciones SET 
                            nombre_integracion      = '$nombre_integracion',
                            fecha_modificacion      = $fecha_servidor_this,
                            url_api                 = '$url_api',
                            usuario                 = '$usuario',
                            clave                   = '$clave',

                            $token_data

                            descripcion             = '$descripcion',
                            ambiente                = '$ambiente'
                            $update_estado_sn
                            $update_tipo_api
                            $update_token_jwt
                            $update_token_expira
                            $update_token_tiempo

                            $update_auth_autorizacion
                            $update_request_autorizacion
                            $adicional_update
                        WHERE 
                            id                      = '$id_integracion' 
                            AND empresa_id          = '$empresa_id'";  

                $this->oIfx->QueryT($sql);

            }else{
                // INSERT

                $adicional_insert_tag = '';
                $adicional_insert_value = '';
                foreach ($inset_aditional as $key => $insert_indi) {
                    $column = $insert_indi['column'];
                    $data = $insert_indi['data'];
                    if($column){
                        $adicional_insert_tag .= ",$column";
                        $adicional_insert_value .= ",'$data'";
                    }
                }

                $insert_estado_sn_tag = !empty($estado_sn)?", estado_sn":"";
                $insert_tipo_api_tag = !empty($tipo_api)?", tipo_api":"";

                $insert_token_jwt_tag = !empty($token_jwt)?", token_jwt":"";
                $insert_token_expira_tag = !empty($token_expira)?" ,token_expira":"";
                $insert_token_tiempo_tag = !empty($token_tiempo)?" ,token_tiempo":"";

                $insert_auth_autorizacion_tag = !empty($auth_autorizacion)?" ,auth_autorizacion":"";
                $insert_request_autorizacion_tag = !empty($request_autorizacion)?" ,request_autorizacion":"";

                $insert_estado_sn_value = !empty($estado_sn)?",'$estado_sn'":"";
                $insert_tipo_api_value = !empty($tipo_api)?",'$tipo_api'":"";
                $insert_token_jwt_value = !empty($token_jwt)?",'$token_jwt'":"";
                $insert_token_expira_value = !empty($token_expira)?" ,'$token_expira'":"";
                $insert_token_tiempo_value = !empty($token_tiempo)?" ,'$token_tiempo'":"";

                $insert_auth_autorizacion_value = !empty($auth_autorizacion)?" ,'$auth_autorizacion'":"";
                $insert_request_autorizacion_value = !empty($request_autorizacion)?" ,'$request_autorizacion'":"";


                $sql = "INSERT INTO comercial.integraciones 
                (
                    empresa_id,
                    nombre_integracion,
                    descripcion,
                    fecha_creacion,
                    url_api,
                    auth_autorizacion,
                    request_autorizacion,
                    usuario,
                    clave,
                    token,
                    ambiente
                    $insert_estado_sn_tag
                    $insert_tipo_api_tag
                    $insert_token_jwt_tag           
                    $insert_token_expira_tag           
                    $insert_token_tiempo_tag   

                    $insert_auth_autorizacion_tag        
                    $insert_request_autorizacion_tag  

                    $adicional_insert_tag      
                ) 
                VALUES 
                (
                    '$empresa_id',
                    '$nombre_integracion',
                    '$descripcion',
                    $fecha_servidor_this,
                        '$url_api',
                    '$auth_autorizacion',
                    '$request_autorizacion',
                    '$usuario',
                    '$clave',
                    '$token',
                    '$ambiente'
                    $insert_estado_sn_value
                    $insert_tipo_api_value
                    $insert_token_jwt_value
                    $insert_token_expira_value
                    $insert_token_tiempo_value

                    $insert_auth_autorizacion_value
                    $insert_request_autorizacion_value

                    $adicional_insert_value
                ) RETURNING id";

                $this->oIfx->QueryT($sql);
                $this->id_integracion = $this->oIfx->ResRow['id'];
            }
            
            $this->oIfx->QueryT('COMMIT;');
            $this->oIfx->Free();

            return  array(
                'estado'=>'exito',
                'procesado'=>true,
                'data'=>['Ejecucion Exitosa']
            );
        } catch (Exception $ex) {

            $error_data =  ($this->error_message_handler($ex->getMessage(),', ERROR:',[0]));
            $this->oIfx->QueryT('ROLLBACK');

            return array(
                'estado'=>'error',
                'procesado'=>false,
                'data'=>$error_data
            );
        }

    }

    public function registrar_integracion_config($id_integracion,$empresa_id,$id_integracion_config,$clave_integracion_config,$valor_integracion_config,$desc_integracion_config){
        try{
            $this->oIfx->QueryT('BEGIN;');

            $filtro_id_integracion_config = !empty($id_integracion_config)?" and id = '$id_integracion_config' ":"";
            $filtro_id_integracion = !empty($id_integracion)?" and id_integracion = '$id_integracion' ":"";
            $filtro_empresa_id = !empty($empresa_id)?" and id_empresa = '$empresa_id' ":"";


            $filtro_clave_integracion_config = !empty($clave_integracion_config)?" and clave = '$clave_integracion_config' ":"";
            $filtro_valor_integracion_config = !empty($valor_integracion_config)?" and valor = '$valor_integracion_config' ":"";

            $filtro_integracion_config = $filtro_empresa_id.$filtro_id_integracion.$filtro_clave_integracion_config.$filtro_valor_integracion_config ;
            if($id_integracion_config){
                $filtro_integracion_config = $filtro_id_integracion_config;
            }
            $sql = "SELECT * 
                    FROM comercial.integraciones_configuracion 
                    WHERE id_empresa = '$empresa_id' 
                    $filtro_integracion_config
                    limit 1
                    ";
            $id_integracion_config_int = 0;           
            if ($this->oIfx->Query($sql)) {
                if ($this->oIfx->NumFilas() > 0) {
                    do {
                        $id_integracion_config_int  = $this->oIfx->f('id');
                        $id_integracion_int         = $this->oIfx->f('id_integracion');
                        $estado_int                 = $this->oIfx->f('estado');
                        $clave_int                  = $this->oIfx->f('clave');
                        $valor_int                  = $this->oIfx->f('valor');
                        $descripcion_int            = $this->oIfx->f('descripcion');

                    } while ($this->oIfx->SiguienteRegistro());
                }
            }

            $clave = $clave_integracion_config?$clave_integracion_config:$clave_int;
            $valor = $valor_integracion_config?$valor_integracion_config:$valor_int;
            $descripcion = $desc_integracion_config?$desc_integracion_config:$descripcion_int;
            $fecha_servidor_this = "'".date("Y-m-d H:i:s")."'";               

            if($id_integracion_config > 0){
                // update

                $sql = "UPDATE comercial.integraciones_configuracion SET 
                            clave      = '$clave',
                            valor      = '$valor',
                            descripcion = '$descripcion',
                            fecha_modificacion = $fecha_servidor_this
                        WHERE 
                            id                      = '$id_integracion_config' 
                            and id_integracion      = '$id_integracion' 
                            AND id_empresa          = '$empresa_id'";                  

                $this->oIfx->QueryT($sql);

            }else{
                // INSERT

                $sql = "INSERT INTO comercial.integraciones_configuracion 
                (
                    id_empresa,
                    estado,
                    id_integracion,
                    clave,
                    valor,
                    descripcion,
                    fecha_creacion       
                ) 
                VALUES 
                (
                    '$empresa_id',
                    'S',
                    '$id_integracion',
                    '$clave',
                    '$valor',
                    '$descripcion',
                    $fecha_servidor_this
                ) RETURNING id";

                $this->oIfx->QueryT($sql);
                $this->id_integracion = $this->oIfx->ResRow['id'];
            }
            
            $this->oIfx->QueryT('COMMIT;');
            $this->oIfx->Free();

            return  array(
                'estado'=>'exito',
                'procesado'=>true,
                'data'=>['Ejecucion Exitosa']
            );
        } catch (Exception $ex) {

            $error_data =  ($this->error_message_handler($ex->getMessage(),', ERROR:',[0]));
            $this->oIfx->QueryT('ROLLBACK');

            return array(
                'estado'=>'error',
                'procesado'=>false,
                'data'=>$error_data
            );
        }

    }

    private function create_table_general($table_name, $table_schema, $pk_column_name, $sql, $add_column_array, $update_column_array,$foreign_column_array, $oCon, $oReturn='') {
        try {

            $oCon->QueryT('BEGIN;');
           
            $ctralterpresu = 0;
            $sql_ctl = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE  TABLE_NAME = '$table_name' and table_schema='$table_schema'";
            if ($oCon->Query($sql_ctl)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $ctralterpresu = ($oCon->f('conteo')) ? ($oCon->f('conteo')) : 0;
                    } while ($oCon->SiguienteRegistro());
                }
            }


            if ($ctralterpresu == 0) {
                // create table
                $oCon->QueryT($sql);

                if (!empty($pk_column_name)) {
                    $sql = "ALTER TABLE $table_schema.$table_name ADD CONSTRAINT pk_$table_name".'_'."$pk_column_name PRIMARY KEY ($pk_column_name);";
                    $oCon->QueryT($sql);

                    $sql = 'ALTER TABLE '.$table_schema.'.'.$table_name.' ADD CONSTRAINT '.$table_name.'_'.$pk_column_name.'_unique_key UNIQUE ('.$pk_column_name.');';
                    $oCon->QueryT($sql);
                }
            }else{

                $sql = "SELECT COUNT(*) as conteo
                        FROM information_schema.table_constraints
                        WHERE 
                            constraint_name = '".$table_name.'_'.$pk_column_name."_unique_key' 
                            and constraint_type = 'UNIQUE' 
                            and constraint_schema = '".$table_schema."' 
                            AND TABLE_NAME = '".$table_name."'";

                $ctl_column_unique = 1;                

                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        do {
                            $ctl_column_unique = ($oCon->f('conteo')) ? ($oCon->f('conteo')) : 0;
                        } while ($oCon->SiguienteRegistro());
                    }
                }
                if($ctl_column_unique==0){                
                    $sql = 'ALTER TABLE '.$table_schema.'.'.$table_name.' ADD CONSTRAINT '.$table_name.'_'.$pk_column_name.'_unique_key UNIQUE ('.$pk_column_name.');';
                    $oCon->QueryT($sql);
                }

                $sql = "SELECT COUNT(*) as conteo
                        FROM information_schema.table_constraints
                        WHERE 
                            constraint_type = 'PRIMARY KEY' 
                            and constraint_schema = '".$table_schema."' 
                            AND TABLE_NAME = '".$table_name."'";
                
                $ctl_column_pk = 1;
                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        do {
                            $ctl_column_pk = ($oCon->f('conteo')) ? ($oCon->f('conteo')) : 0;
                        } while ($oCon->SiguienteRegistro());
                    }
                }
                if($ctl_column_pk==0){                
                    $sql = "ALTER TABLE $table_schema.$table_name ADD CONSTRAINT pk_$table_name".'_'."$pk_column_name PRIMARY KEY ($pk_column_name);";
                    $oCon->QueryT($sql);
                    
                }

               
            }

            foreach ($add_column_array as $colum_name => $colum_type) {
                $ctl_column = 0;
                $sql = '';

                $sql = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE 
                            COLUMN_NAME = '$colum_name' 
                            AND TABLE_NAME = '$table_name' 
                            and table_schema = '$table_schema'";

                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        do {
                            $ctl_column = ($oCon->f('conteo')) ? ($oCon->f('conteo')) : 0;
                        } while ($oCon->SiguienteRegistro());
                    }
                }

                if ($ctl_column == 0) {
                    $sql = "ALTER TABLE $table_schema.$table_name ADD $colum_name $colum_type;";
                    $oCon->QueryT($sql);
                }
            }

            foreach ($update_column_array as $colum_name => $colum_type) {
                $ctl_column = 0;
                $sql = '';

                $sql = "SELECT count(*) as conteo
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE 
                            COLUMN_NAME = '$colum_name' 
                            AND TABLE_NAME = '$table_name' 
                            and table_schema = '$table_schema'";

                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        do {
                            $ctl_column = ($oCon->f('conteo')) ? ($oCon->f('conteo')) : 0;
                        } while ($oCon->SiguienteRegistro());
                    }
                }

                if ($ctl_column != 0) {
                    $sql = "ALTER TABLE $table_schema.$table_name ALTER COLUMN $colum_name TYPE $colum_type;";
                    $oCon->QueryT($sql);
                }
            }


            foreach ($foreign_column_array as $key => $foreign_data_indi) {
                $ctl_column = 0;
                $sql = '';



                $parent = $foreign_data_indi['parent'];
                $child= $foreign_data_indi['child'];

                $sql = "SELECT COUNT(*) as conteo
                        FROM information_schema.table_constraints
                        WHERE 
                            constraint_name = '".$foreign_data_indi['constraint_unique_name']."' 
                            and constraint_type = 'UNIQUE' 
                            and constraint_schema = '".$child['table_schema']."' 
                            AND TABLE_NAME = '".$child['table_name']."'";
                
                $ctl_column_uq = 1;
                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        do {
                            $ctl_column_uq = ($oCon->f('conteo')) ? ($oCon->f('conteo')) : 0;
                        } while ($oCon->SiguienteRegistro());
                    }
                }
                if ($ctl_column_uq == 0) {
                    $sql = 'ALTER TABLE '.$child['table_schema'].".".$child['table_name'].' ADD CONSTRAINT '.$foreign_data_indi['constraint_unique_name'].' UNIQUE ('.$pk_column_name.');';
                    $oCon->QueryT($sql);
                }

                $sql = "SELECT COUNT(*) as conteo
                        FROM information_schema.table_constraints
                        WHERE 
                            constraint_name = '".$foreign_data_indi['constraint_name']."' 
                            and constraint_type = '".$foreign_data_indi['constraint_type']."' 
                            and constraint_schema = '".$child['table_schema']."' 
                            AND TABLE_NAME = '".$child['table_name']."'";
                
                $ctl_column_fk = 1;
                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        do {
                            $ctl_column_fk = ($oCon->f('conteo')) ? ($oCon->f('conteo')) : 0;
                        } while ($oCon->SiguienteRegistro());
                    }
                }

                if ($ctl_column_fk == 0) {
                    $sql = "ALTER TABLE ".$child['table_schema'].".".$child['table_name']." ADD CONSTRAINT ".$foreign_data_indi['constraint_name']." ".$foreign_data_indi['constraint_type']." (".$child['column_name'].") REFERENCES ".$parent['table_schema'].".".$parent['table_name']." (".$parent['column_name'].");";
                    $oCon->QueryT($sql);
                }
            }

            $oCon->QueryT('COMMIT;');
            $oCon->Free();
            return 1;
        } catch (Exception $ex) {
            print_r($ex->getMessage());
            print_r(PHP_EOL);
            $oCon->QueryT('ROLLBACK');

            return 0;
        }
    }

    public function eliminar_integracion($id_integracion){
        try{
            $this->oIfx->QueryT('BEGIN;');

            $sql = "DELETE FROM comercial.integraciones WHERE ID = '$id_integracion'";

            $this->oIfx->QueryT($sql);
            $this->oIfx->QueryT('COMMIT;');
            $this->oIfx->Free();
            return array(
                'estado'=>'exito',
                'procesado'=>true,
                'data'=>['Ejecucion Exitosa']
            );

        } catch (Exception $ex) {
            $this->oIfx->QueryT('ROLLBACK');
            $error_data =  ($this->error_message_handler($ex->getMessage(),', ERROR:',[0]));

            return array(
                'estado'=>'error',
                'procesado'=>false,
                'data'=>$error_data
            );

        }       
    }

    public function eliminar_integracion_config($id_integracion_config){
        try{
            $this->oIfx->QueryT('BEGIN;');

            $sql = "DELETE FROM comercial.integraciones_configuracion WHERE ID = '$id_integracion_config'";

            $this->oIfx->QueryT($sql);
            $this->oIfx->QueryT('COMMIT;');
            $this->oIfx->Free();
            return array(
                'estado'=>'exito',
                'procesado'=>true,
                'data'=>['Ejecucion Exitosa']
            );

        } catch (Exception $ex) {
            $this->oIfx->QueryT('ROLLBACK');
            $error_data =  ($this->error_message_handler($ex->getMessage(),', ERROR:',[0]));

            return array(
                'estado'=>'error',
                'procesado'=>false,
                'data'=>$error_data
            );

        }       
    }

    private function error_message_handler($message_string,$delimiter,$index=[0]){
        $message = [];
        if(!empty($message_string)){
            $message_string_array = explode($delimiter,$message_string);
            foreach ($message_string_array as $key => $value) {
                if($index[0]==0){
                    $message[$key]= $value;
                }else{
                    foreach ($index as $i => $v) {
                        if($key==$v){
                            $message[$key]= $value;
                        }
                    }
                }
            }
        }
        return $message?$message:['',"Error Desconocido"];
    }
}

?>