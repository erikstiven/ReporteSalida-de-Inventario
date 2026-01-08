<? 	
	$jirehUri = $_COOKIE["JIREH_URI"];

	$oCon->Conectar();
	$sql= "SELECT * 
			FROM comercial.MAETAB
			WHERE NUMTAB='01' AND CODTAB IS NOT NULL 
			ORDER BY CODTAB";
	if($oCon->Query($sql)){
		do {
			$aEmpresa[$oCon->f('dato1')]=$oCon->f('dato2');
		}while($oCon->SiguienteRegistro());
	}
	
	if(empty($aEmpresa['SISTEMA_ICONO'])){
		$iconoSistema = 'jireh';
	}else{
		$iconoSistema = $aEmpresa['SISTEMA_ICONO'];
	}
?>

	<!--META-->
	<meta http-equiv="Content-Type" content="text/html; charset=uft-8">
	<!-- Tell the browser to be responsive to screen width -->
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	
	<!--CSS-->
	<link REL="SHORTCUT ICON" HREF="<?=$jirehUri?>imagenes/<?=$iconoSistema?>.ico">
	
	<!-- Bootstrap 3.3.7 -->
	<link rel="stylesheet" href="<?=$jirehUri?>Include/Componentes/bower_components/bootstrap/dist/css/bootstrap.min.css">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="<?=$jirehUri?>Include/Componentes/bower_components/font-awesome/css/font-awesome.min.css">
	<!-- Ionicons -->
	<link rel="stylesheet" href="<?=$jirehUri?>Include/Componentes/bower_components/Ionicons/css/ionicons.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="<?=$jirehUri?>Include/Componentes/dist/css/AdminLTE.min.css">
	<!-- iCheck -->
	<link rel="stylesheet" href="<?=$jirehUri?>Include/Componentes/plugins/iCheck/square/blue.css">
	
	<!--JavaScript-->
	<script src="<?=$jirehUri?>Include/Componentes/bower_components/jquery/dist/jquery.min.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/js/comun.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/Clases/fc/js/FusionCharts.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?=$jirehUri?>Include/Clases/Formulario/Js/Formulario.js"></script>
	
	<title><?=$aEmpresa['SISTEMA_NOMBRE']?> <?=$aEmpresa['SISTEMA_EMPRESA'];?></title>
    
      