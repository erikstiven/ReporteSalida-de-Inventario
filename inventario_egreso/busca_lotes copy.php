<?
header("Content-Type: text/html; charset=ISO-8859-1");
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <link rel="stylesheet" type = "text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/general.css">
            <link href="<?=$_COOKIE["JIREH_INCLUDE"]?>Clases/Formulario/Css/Formulario.css" rel="stylesheet" type="text/css"/>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>LOTES</title>

			<!--CSS--> 
			<link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen" />
			<link rel="stylesheet" type="text/css" href="js/jquery/plugins/simpleTree/style.css" />
			<link rel="stylesheet" href="media/css/bootstrap.css">
			<link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css">
			<link rel="stylesheet" href="media/font-awesome/css/font-awesome.css">
			<link type="text/css" href="css/style.css" rel="stylesheet"></link>

			<!--Javascript--> 
			<script type="text/javascript" src="js/jquery.js"></script>
			<script type="text/javascript" src="js/jquery.min.js"></script>
			<script src="media/js/jquery-1.10.2.js"></script>
			<script src="media/js/jquery.dataTables.min.js"></script>
			<script src="media/js/dataTables.bootstrap.min.js"></script>          
			<script src="media/js/bootstrap.js"></script>
			<script type="text/javascript" language="javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>    
			<script src="media/js/lenguajeusuario_producto.js"></script>   
	
            <script>
                function cargar_datos( id, lote, fcad, cant, felab ) {
                    if (cant > 0) {
                        if (id == null) {
                            var id_var 		= 'loteProd';
                            var fcad_var 	= 'fCadLoteProd';
							var fela_var 	= 'fElaLoteProd';
                        } else {
                            var id_var 		= id + '_lotes_prod';
                            var fcad_var 	= id + '_fCadLoteProd';
							var fela_var 	= id + '_fElaLoteProd';
                        }

                        window.opener.document.getElementById(id_var).value 			= lote;
                        window.opener.document.getElementById(fcad_var).value 			= fcad;
                        window.opener.document.getElementById('stock').value 			= cant;
						window.opener.document.getElementById(fela_var).value 			= felab;

						//alert(felab);
						
                        if (id != null) {
                            window.opener.cargar_update_cant2(id);
                        }

                        window.opener.document.getElementById('cantidad').focus();
                        window.close();
						
						
                    } else {
                        alert('No puede seleccionar lote: ' + lote + ' con cantidad negativa: ' + cant)
                    }
                }

            </script>
    </head>


    <body>

        <?
        global $DSN_Ifx, $DSN;

        $oIfx = new Dbo;
        $oIfx->DSN = $DSN_Ifx;
        $oIfx->Conectar();

        $oIfxA = new Dbo;
        $oIfxA->DSN = $DSN_Ifx;
        $oIfxA->Conectar();

        $oIfxB = new Dbo;
        $oIfxB->DSN = $DSN_Ifx;
        $oIfxB->Conectar();

        $oCon = new Dbo;
        $oCon->DSN = $DSN;
        $oCon->Conectar();

        $id = $_GET['id'];
        $dpef_cod_bode = $_GET['bode'];
        $dpef_cod_prod = $_GET['prod'];
        $idSucursal = $_GET['sucu'];

        //  LECTURA SUCIA
        

        $id_user = $_SESSION['U_ID'];
        $idEmpresa = $_SESSION['U_EMPRESA'];
        $fecha_server = date("d/m/Y");

        // FECHA DEL SALDO INCIAL ULTIMO
        $sql = "select t.tran_cod_tran from tran_sald_ini t where
                t.empr_cod_empr = $idEmpresa and
                t.sucu_cod_sucu = $idSucursal ";
        $tran_ini = consulta_string_func($sql, 'tran_cod_tran', $oCon, '');

        $sql = "select  max(minv_fmov) as fecha 
			from saeminv where
			minv_cod_tran = '$tran_ini' and
			minv_cod_empr = $idEmpresa and
			minv_est_minv <> '0' ";
			//echo $sql;
        $fecha_ini = consulta_string_func($sql, 'fecha', $oIfx, '');

        $fecha_fin = date("m/d/Y");

        $sql_sp = "execute procedure sp_lotes_productos_web( $idEmpresa, $idSucursal, $dpef_cod_bode, '$fecha_ini', '$fecha_fin', '$dpef_cod_prod', '$dpef_cod_prod', 2 , $id_user) ";
        $oIfx->Query($sql_sp);
        //echo $sql_sp;

        $Html_reporte .='<div class="table-responsive">';
        $Html_reporte .='<table class="table table-bordered table-hover" align="center" style="width: 98%;">';
        $Html_reporte .='<tr>
							<td class="bg-primary" align="center">N.-</td>
							<td class="bg-primary" align="center">Codigo</td>
							<td class="bg-primary" align="center">Producto</td>
							<td class="bg-primary" align="center">SETED</td>
							<td class="bg-primary" align="center">Fecha Elaboracion</td>
							<td class="bg-primary" align="center">Fecha Caducidad</td>
							<td class="bg-primary" align="center">Cantidad</td>
							<td class="bg-primary" align="center">Reserva</td>
							<td class="bg-primary" align="center">Disponible</td>
							<td class="bg-primary" align="center">Costo</td>
							<td class="bg-primary" align="center">Costo Promedio</td>
							<td class="bg-primary" align="center">Dias</td>
					</tr>';

        $sql = "select t.prod_cod_prod, t.bode_cod_bode, t.cant_lote, t.num_lote,
			t.fecha_ela_lote, t.fecha_cad_lote, t.prod_nom_prod, t.costo, t.costo_prom
			from tmp_lote t where
			t.empr_cod_empr = $idEmpresa  and
			t.sucu_cod_sucu = $idSucursal and
			t.bode_cod_bode = $dpef_cod_bode and
			t.prod_cod_prod = '$dpef_cod_prod'";

            
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $prod_cod 		= $oCon->f('prod_cod_prod');
                    $bode_cod 		= $oCon->f('bode_cod_bode');
                    $cant_lote 		= $oCon->f('cant_lote');
                    $num_lote 		= $oCon->f('num_lote');
                    $fec_ela 		= fecha_informix($oCon->f('fecha_ela_lote'));
                    $fec_cad 		= fecha_informix($oCon->f('fecha_cad_lote'));
                    $prod_nom 		= $oCon->f('prod_nom_prod');
                    $costo 			= $oCon->f('costo');
                    $costo_pro 		= $oCon->f('costo_prom');

                    $sql = "insert into tmp_prod_lote_web (prod_cod_prod, bode_cod_bode, cant_lote, num_lote,
							fecha_ela_lote, fecha_cad_lote, prod_nom_prod ,
							costo   ,  costo_prom , empr_cod_empr, sucu_cod_sucu , user_cod_web )
							values( '$prod_cod', '$bode_cod', '$cant_lote', '$num_lote', '$fec_ela',
						   '$fec_cad', '$prod_nom', $costo, $costo_pro, 
							$idEmpresa, $idSucursal, $id_user ) ";
                    $oIfx->QueryT($sql);
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $sql = "select  sum(cant_lote) as cant, num_lote,  MAX(fecha_ela_lote) as felab, MAX(fecha_cad_lote) as fcad, 
			prod_cod_prod, prod_nom_prod, costo, costo_prom
			from tmp_prod_lote_web where
			user_cod_web  = $id_user and
			bode_cod_bode = $dpef_cod_bode and
			empr_cod_empr = $idEmpresa and
			sucu_cod_sucu = $idSucursal
			group by 2, 5, 6, 7, 8
			having  sum(cant_lote) <> 0
			order by fcad ";

            echo $sql;exit;
        unset($array_prod);
        $i = 1;
        $total = 0;
        $totalPedido = 0;
        $totalDisponible = 0;
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $prod_cod_prod 		= $oIfx->f('prod_cod_prod');
                    $prod_nom_prod 		= $oIfx->f('prod_nom_prod');
                    $cantidad 			= $oIfx->f('cant');
                    $lote 				= $oIfx->f('num_lote');
                    $fecha_ela 			= $oIfx->f('felab');
                    $fecha_cad 			= $oIfx->f('fcad');
                    $costo 				= $oIfx->f('costo');
                    $activo 			= $oIfx->f('activo');
                    $costo_prom 		= $oIfx->f('costo_prom');

                    if ($activo == 1) {
                        $activo = 'Activo';
                    } elseif ($activo == 0) {
                        $activo = 'Inactivo';
                    }
                    $dia = 0;

                    // DATOS
                    $array_prod [$i] = $prod_cod_prod;

                    if (!empty($fecha_ela)) {
                        $fecha_ela = fecha_mysql_funcYmd($fecha_ela);
                    }

                    if (!empty($fecha_cad)) {
                        $fecha_cad	 	= fecha_mysql_funcYmd($fecha_cad);
                        $dia 			= restaFechas($fecha_server, fecha_mysql2($oIfx->f('fcad')));
                    }

                    $color = '';
                    if ($dia <= 365) {
                        $color = 'red';
                    } else {
                        $color = 'green';
                    }

                    $colorCant = '';
                    if ($cantidad <= 0) {
                        $colorCant = 'red';
                    } else {
                        $colorCant = 'blue';
                    }

                    // costo
                    if ($costo == 0) {
                        //$costo = ultimo_costo_func($id_empresa, $id_sucursal, $prod_cod_prod, $id_bodega, $fecha_ini, $oIfxA);
                    }

                    // stock pedido
                    $sqlStockPedido = "select nvl(sum(dpef_cant_dfac),0) as dpef_cant_dfac 
                                        from saepedf p, saedpef d 
                                        where
                                        p.pedf_cod_pedf = d.dpef_cod_pedf and
                                        p.pedf_cod_empr = $idEmpresa and
                                        p.pedf_cod_sucu = $idSucursal and
                                        p.pedf_est_fact = 'PE' and
                                        d.dpef_cod_prod = '$prod_cod_prod' and
                                        d.dpef_cod_bode = $dpef_cod_bode and
                                        d.dpef_cod_lote = '$lote'";
                    //echo $sqlStockPedido;
                    $dpef_cant_dfac = consulta_string_func($sqlStockPedido, "dpef_cant_dfac", $oIfxA, 0);

                    //disponible
                    $disponible = $cantidad - $dpef_cant_dfac;

                    if ($i > 1) {
                        if ($array_prod[$i] != $array_prod[$i - 1]) {
                            // total
                            if ($sClass == 'off')
                                $sClass = 'on';
                            else
                                $sClass = 'off';
                            $Html_reporte .='<tr height="20"  class="' . $sClass . '"
                                            onMouseOver="javascript:this.className=\'link\';"
                                            onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                            $Html_reporte .= '<td  align= "right"></td>';
                            $Html_reporte .= '<td  align= "LEFT"></td>';
                            $Html_reporte .= '<td  align= "left"></td>';
                            $Html_reporte .= '<td  align= "right"></td>';
                            $Html_reporte .= '<td  align= "left"></td>';
                            $Html_reporte .= '<td  align= "left" class="frm_td">TOTAL:</td>';
                            $Html_reporte .= '<td  align= "right" class="fecha_grande">' . $total . '</td>';
                            $Html_reporte .= '<td  align= "right"></td>';
                            $Html_reporte .= '<td  align= "right"></td>';
                            $Html_reporte .= '<td  align= "right"></td>';
                            $Html_reporte .= '</tr>';
                            // siguietne registro
                            if ($sClass == 'off')
                                $sClass = 'on';
                            else
                                $sClass = 'off';
                            $Html_reporte .='<tr height="20"  class="' . $sClass . '"
										onMouseOver="javascript:this.className=\'link\';"
										onMouseOut="javascript:this.className=\'' . $sClass . '\';"
										onclick="cargar_datos(' . $id . ', \'' . $lote . '\', \'' . $fecha_cad . '\', \'' . $disponible . '\' , \'' . $fecha_ela . '\' );" style="cursor: pointer;">';
                            $Html_reporte .= '<td  align= "right">' . $i . '</td>';
                            $Html_reporte .= '<td  align= "left">' . $prod_cod_prod . '</td>';
                            $Html_reporte .= '<td  align= "left">' . $prod_nom_prod . '</td>';
                            $Html_reporte .= '<td  align= "left" style="color: ' . $color . '; font-weight: bold;">' . $lote . '</td>';
                            $Html_reporte .= '<td  align= "right">' . $fecha_ela . '</td>';
                            $Html_reporte .= '<td  align= "right" style="color: ' . $color . '; font-weight: bold;">' . $fecha_cad . '</td>';
                            $Html_reporte .= '<td  align= "right" style="color: ' . $colorCant . '; font-weight: bold;">' . $cantidad . '</td>';
                            $Html_reporte .= '<td  align= "right" style="font-weight: bold;">' . $dpef_cant_dfac . '</td>';
                            $Html_reporte .= '<td  align= "right" style="font-weight: bold;">' . $disponible . '</td>';
                            $Html_reporte .= '<td  align= "right">' . $costo . '</td>';
                            $Html_reporte .= '<td  align= "right">' . $costo_prom . '</td>';
                            $Html_reporte .= '<td  align= "right">' . $dia . '</td>';
                            $Html_reporte .= '</tr>';
                            $total = 0;
                        }else {
                            if ($sClass == 'off')
                                $sClass = 'on';
                            else
                                $sClass = 'off';
                            $Html_reporte .='<tr height="20"  class="' . $sClass . '"
										onMouseOver="javascript:this.className=\'link\';"
										onMouseOut="javascript:this.className=\'' . $sClass . '\';"
										onclick="cargar_datos(' . $id . ', \'' . $lote . '\', \'' . $fecha_cad . '\', \'' . $disponible . '\' , \'' . $fecha_ela . '\' );" style="cursor: pointer;">';
                            $Html_reporte .= '<td  align= "right"></td>';
                            $Html_reporte .= '<td  align= "left">' . $prod_cod_prod . '</td>';
                            $Html_reporte .= '<td  align= "left">' . $prod_nom_prod . '</td>';
                            $Html_reporte .= '<td  align= "left" style="color: ' . $color . '; font-weight: bold;">' . $lote . '</td>';
                            $Html_reporte .= '<td  align= "right">' . $fecha_ela . '</td>';
                            $Html_reporte .= '<td  align= "right" style="color: ' . $color . '; font-weight: bold;">' . $fecha_cad . '</td>';
                            $Html_reporte .= '<td  align= "right" style="color: ' . $colorCant . '; font-weight: bold;">' . $cantidad . '</td>';
                            $Html_reporte .= '<td  align= "right" style="font-weight: bold;">' . $dpef_cant_dfac . '</td>';
                            $Html_reporte .= '<td  align= "right" style="font-weight: bold;">' . $disponible . '</td>';
                            $Html_reporte .= '<td  align= "right">' . $costo . '</td>';
                            $Html_reporte .= '<td  align= "right">' . $costo_prom . '</td>';
                            $Html_reporte .= '<td  align= "right">' . $dia . '</td>';
                            $Html_reporte .= '</tr>';
                        }
                    } else {
                        if ($sClass == 'off')
                            $sClass = 'on';
                        else
                            $sClass = 'off';
                        $Html_reporte .='<tr height="20"  class="' . $sClass . '"
									onMouseOver="javascript:this.className=\'link\';"
									onMouseOut="javascript:this.className=\'' . $sClass . '\';"
									onclick="cargar_datos(' . $id . ', \'' . $lote . '\', \'' . $fecha_cad . '\', \'' . $disponible . '\' , \'' . $fecha_ela . '\'  );" style="cursor: pointer;">';
                        $Html_reporte .= '<td  align= "right">' . $i . '</td>';
                        $Html_reporte .= '<td  align= "left">' . $prod_cod_prod . '</td>';
                        $Html_reporte .= '<td  align= "left">' . $prod_nom_prod . '</td>';
                        $Html_reporte .= '<td  align= "left" style="color: ' . $color . '; font-weight: bold;">' . $lote . '</td>';
                        $Html_reporte .= '<td  align= "right">' . $fecha_ela . '</td>';
                        $Html_reporte .= '<td  align= "right" style="color: ' . $color . '; font-weight: bold;">' . $fecha_cad . '</td>';
                        $Html_reporte .= '<td  align= "right" style="color: ' . $colorCant . '; font-weight: bold;">' . $cantidad . '</td>';
                        $Html_reporte .= '<td  align= "right" style="font-weight: bold;">' . $dpef_cant_dfac . '</td>';
                        $Html_reporte .= '<td  align= "right" style="font-weight: bold;">' . $disponible . '</td>';
                        $Html_reporte .= '<td  align= "right">' . $costo . '</td>';
                        $Html_reporte .= '<td  align= "right">' . $costo_prom . '</td>';
                        $Html_reporte .= '<td  align= "right">' . $dia . '</td>';
                        $Html_reporte .= '</tr>';
                    }

                    $total += $cantidad;
                    $totalPedido += $dpef_cant_dfac;
                    $totalDisponible += $disponible;
                    $i++;
                }while ($oIfx->SiguienteRegistro());
                // total
                if ($sClass == 'off')
                    $sClass = 'on';
                else
                    $sClass = 'off';
                $Html_reporte .='<tr height="20"  class="' . $sClass . '"
														onMouseOver="javascript:this.className=\'link\';"
														onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                $Html_reporte .= '<td  align= "right"></td>';
                $Html_reporte .= '<td  align= "right"></td>';
                $Html_reporte .= '<td  align= "LEFT"></td>';
                $Html_reporte .= '<td  align= "left"></td>';
                $Html_reporte .= '<td  align= "right"></td>';
                $Html_reporte .= '<td  align= "left" class="frm_td">TOTAL:</td>';
                $Html_reporte .= '<td  align= "right" class="fecha_grande">' . $total . '</td>';
                $Html_reporte .= '<td  align= "right" class="fecha_grande">' . $totalPedido . '</td>';
                $Html_reporte .= '<td  align= "right" class="fecha_grande">' . $totalDisponible . '</td>';
                $Html_reporte .= '<td  align= "right"></td>';
                $Html_reporte .= '<td  align= "right"></td>';
                $Html_reporte .= '<td  align= "right"></td>';
                $Html_reporte .= '</tr>';
            }else {
                $Html_reporte = '<span class=fecha_letra">No existen datos... </span>';
            }
        }
        $oIfx->Free();

        $Html_reporte .='</table>
					     </div>';

        echo $Html_reporte;

       
        function fecha_informix($fecha) {
            $m = substr($fecha, 5, 2);
            $y = substr($fecha, 0, 4);
            $d = substr($fecha, 8, 2);

            return ( $m . '/' . $d . '/' . $y );
        }

        function fecha_mysql($fecha) {
            $fecha_array = explode('/', $fecha);
            $m = $fecha_array[0];
            $y = $fecha_array[2];
            $d = $fecha_array[1];

            return ( $d . '/' . $m . '/' . $y );
        }

        function fecha_mysql2($fecha) {
            $fecha_array = explode('/', $fecha);
            $m = $fecha_array[0];
            $y = $fecha_array[2];
            $d = $fecha_array[1];
            return ( $d . '/' . $m . '/' . $y );
        }

        function restaFechas($dFecIni, $dFecFin) {
            $dFecIni = str_replace("-", "", $dFecIni);
            $dFecIni = str_replace("/", "", $dFecIni);
            $dFecFin = str_replace("-", "", $dFecFin);
            $dFecFin = str_replace("/", "", $dFecFin);

            ereg("([0-9]{1,2})([0-9]{1,2})([0-9]{2,4})", $dFecIni, $aFecIni);

            ereg("([0-9]{1,2})([0-9]{1,2})([0-9]{2,4})", $dFecFin, $aFecFin);

            $date1 = mktime(0, 0, 0, $aFecIni[2], $aFecIni[1], $aFecIni[3]);
            $date2 = mktime(0, 0, 0, $aFecFin[2], $aFecFin[1], $aFecFin[3]);

            return round(($date2 - $date1) / (60 * 60 * 24));
        }
        ?>    
    </body>
</html>