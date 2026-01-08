<?php

/*
  Contratos
  Daniel Castro - VII2019
  Clase para gestión y administración de notas
 */

require_once(DIR_INCLUDE.'comun.lib.php');

class Notas{
	
	var $oConexion; 

	function registraNotasContratos($oConexion, $idEmpresa, $idSucursal, $idClpv, $idContrato, $userWeb, $prioridad, $titulo, $msj, $adjunto, $usuarios, $fecha){
		
		$fechaServer = date("Y-m-d H:i:s");
		
		$sql = "insert into isp.int_notas(id_empresa, id_sucursal, id_clpv, id_contrato, prioridad, 
									titulo, nota, adjunto, estado, fecha, user_web, fecha_server)
								values($idEmpresa, $idSucursal, $idClpv, $idContrato, '$prioridad',
									'$titulo', '$msj', '$adjunto', 'A', '$fecha', $userWeb, '$fechaServer')";
		$oConexion->QueryT($sql);
		
		if(count($usuarios) > 0){
			$sql = "select max(id) as maximo from isp.int_notas
					where id_empresa = $idEmpresa and
					id_sucursal = $idSucursal and
					id_clpv = $idClpv and
					id_contrato = $idContrato";
			$idmaximo = consulta_string_func($sql, 'maximo', $oConexion, 0);

			if(count($usuarios) > 0){
				foreach ($usuarios as $val){
					$user_recibe = $val[0];
					$sql = "insert into isp.int_notas_user(id_nota, user_envia, user_recibe)
												values($idmaximo, $userWeb, $user_recibe)";
					$oConexion->QueryT($sql);
				}
			}
		}
		
		return 'OK';
		
	}
	
	function reporteNotasContratos($oCon, $oIfx, $idempresa, $idsucursal, $id_clpv, $id_contrato){
		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

		$sHtml = '';
		
		$sql = "select usuario_id, concat(usuario_nombre, ' ', usuario_apellido) as user
				from usuario
				where empresa_id = $idempresa";
		if($oCon->Query($sql)){
			if($oCon->NumFilas() > 0){
				unset($arrayUser);
				do{
					$arrayUser[$oCon->f('usuario_id')] = $oCon->f('user');
				}while($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();
		
		//contrato
		$sql = "select codigo, nom_clpv from isp.contrato_clpv where id_empresa = $idempresa and id_clpv = $id_clpv and id = $id_contrato";
		if($oCon->Query($sql)){
			if($oCon->NumFilas() > 0){
				do{
					$codigo = $oCon->f('codigo');
					$nom_clpv = $oCon->f('nom_clpv');
				}while($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();
		
		$sHtml .='<div class="alert alert-warning alert-dismissible" role="alert">
					<strong>'.$codigo.', </strong>'.$nom_clpv.'
				</div>';
				
        $sHtml .='<table class="table table-bordered table-striped table-condensed table-hover" style="width: 99%; margin-top: 10px;" align="center">';
        $sHtml .='<tr>
                    <td align="center" colspan="7" class="bg-primary">NOTAS REGISTRADAS</td>
                </tr>';
        //query clpv
        $sql = "select id, titulo, nota, estado, fecha, user_web, fecha_server
                from isp.int_notas 
                where
                id_empresa = $idempresa and
                id_sucursal = $idsucursal and
                id_clpv = $id_clpv and
                id_contrato = $id_contrato";
        if($oCon->Query($sql)){
            if($oCon->NumFilas() > 0){
				$sHtml .= '<tr>';
				$sHtml .= '<td>Fecha</td>';
				$sHtml .= '<td>Titulo</td>';
				$sHtml .= '<td>Nota</td>';
				$sHtml .= '<td>User</td>';
				$sHtml .= '</tr>';
                do{
					$id = $oCon->f('id');
                    $titulo = $oCon->f('titulo');
                    $nota = $oCon->f('nota');
                    $estado = $oCon->f('estado');
                    $fecha = $oCon->f('fecha');
                    $user_web = $oCon->f('user_web');
                    $fecha_server = $oCon->f('fecha_server');
					
                    $sHtml .= '<tr>';
                    $sHtml .= '<td align="left">'.fecha_mysql_dmy($fecha).'</td>';
					$sHtml .= '<td align="left">'.$titulo.'</td>';
                    $sHtml .= '<td align="left">'.$nota.'</td>';
                    $sHtml .= '<td align="left">'.$arrayUser[$user_web].'</td>';
                    $sHtml .= '</tr>';
                }while($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();
        
        $sHtml .= '</table>';
       
		return $sHtml;
	}
	
	function notasAsignadasUsuario($oCon, $idUser){
		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

		unset($array);
		
        $sql = "select n.id, n.prioridad, n.titulo, n.nota, n.user_web, n.fecha_server,
				c.abonado, c.nom_clpv
                from isp.int_notas n, isp.int_notas_user u, isp.contrato_clpv c				
                where
                n.id_contrato = c.id and
				n.id_clpv = c.id_clpv and
				n.id = u.id_nota and
				n.id_empresa = c.id_empresa and
				n.id_sucursal = c.id_sucursal and
				u.user_recibe = $idUser and
				u.estado = 'S'
				order by n.prioridad, n.fecha_server desc";
		if($oCon->Query($sql)){
			if($oCon->NumFilas() > 0){
				do{
					$array[] = array($oCon->f('id'), $oCon->f('prioridad'), $oCon->f('titulo'), $oCon->f('nota'), 
									$oCon->f('user_web'), $oCon->f('fecha_server'), $oCon->f('abonado'), $oCon->f('nom_clpv'));
				}while($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();
				
		return $array;
	}
	
	function detalleNotasAsignadasUsuario($oCon, $idUser, $idNota){
		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

		unset($array);
		
        $sql = "select n.id, n.prioridad, n.titulo, n.nota, n.user_web, n.fecha_server,
				c.abonado, c.nom_clpv, c.direccion, c.telefono, c.referencia, c.email,
				c.codigo, c.sobrenombre, c.id_clpv, c.id as idcontrato, u.id as id_nota_user
                from isp.int_notas n, isp.int_notas_user u, isp.contrato_clpv c				
                where
                n.id_contrato = c.id and
				n.id_clpv = c.id_clpv and
				n.id = u.id_nota and
				n.id_empresa = c.id_empresa and
				n.id_sucursal = c.id_sucursal and
				u.user_recibe = $idUser and
				n.id = $idNota and
				u.estado = 'S'
				order by n.prioridad, n.fecha_server desc";
		if($oCon->Query($sql)){
			if($oCon->NumFilas() > 0){
				do{
					$array[] = array($oCon->f('id'), $oCon->f('prioridad'), $oCon->f('titulo'), $oCon->f('nota'), 
									$oCon->f('user_web'), $oCon->f('fecha_server'), $oCon->f('abonado'), $oCon->f('nom_clpv'),
									$oCon->f('direccion'), $oCon->f('telefono'), $oCon->f('referencia'), $oCon->f('email'), 
									$oCon->f('codigo'), $oCon->f('sobrenombre'), $oCon->f('id_clpv'), $oCon->f('idcontrato'), $oCon->f('id_nota_user'));
				}while($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();
				
		return $array;
	}
	
}
?>