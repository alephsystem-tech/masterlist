<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/home_modelo.php");
include("../modelo/prg_usuario_modelo.php");
include("../modelo/prg_pais_modelo.php");

include("../modelo/reportes_proy_modelo.php");
$reporteproy=new reportes_proy_model();

$pais=new prg_pais_model();
$home=new home_model();
$usuario=new prg_usuario_model();

$ses_codusuario=$_SESSION['codusuario'];
$ses_idrol=$_SESSION['id_rol'];
$ses_idpais=$_SESSION['id_pais'];
$ses_idauditor=$_SESSION['id_auditor'];


$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

	
if(!empty($_POST['accion']) and $_POST['accion']=='home'){

	$anioFinal=date("Y");

	$anioInicial = date("Y")-1;

	$data_pais=$pais->selec_one_pais($ses_idpais);
	
	$tceu_dol=$data_pais['tceu_dol'];
	
	$tipo=$_POST['tipo'];

	if($tipo=='A'){
		$aniof=date("Y");

		$anio = date("Y");
		$dataPais=$pais->selec_one_pais($ses_idpais);
		$tcEuUS=$dataPais['tceu_dol'];
		
		/* Modificación de Dashboard: Montos facturados - No Intercompany */ 
		
		$tipo='1,7';
		$proyecto="";
		$codestado="";
		$anio="";
		$codejecutivo="";
		
		$data_res=$reporteproy->selec_res_xaniomes($ses_idpais,$proyecto,$tcEuUS,$tipo,$codestado,$anio,$codejecutivo);
		
		foreach($data_res as $rowP){
			$arrayCosto1[$rowP['anio']][$rowP['mes']]=$rowP['costo'] - $rowP['notacredito'];
			$arrayCantidad1[$rowP['anio']][$rowP['mes']]=$rowP['numero'];
		}
		
		// agregar TC al cuadro
		$data_res=$reporteproy->selec_res_TC_analis($ses_idpais,$proyecto,$anio,$tcEuUS);
		
		if(!empty($data_res)){
			foreach($data_res as $rowP) {
				$arrayCosto1[$rowP['anio']][$rowP['mes']]+=$rowP['costo'];
				$arrayCantidad1[$rowP['anio']][$rowP['mes']]+=$rowP['numero'];
			}
		}

		// agregar lab al cuadro
		$data_res=$reporteproy->selec_res_labresultado($ses_idpais,$proyecto,$anio);
		
		if(!empty($data_res)){
			foreach($data_res as $rowP) {
				$arrayCosto1[$rowP['anio']][$rowP['mes']]+=$rowP['costo'];
				$arrayCantidad1[$rowP['anio']][$rowP['mes']]+=$rowP['numero'];
			}
		}

		//Dashboard: Montos facturados - No Intercompany
		/*$tipo='1';
	
		$data_res=$reporteproy->selec_res_xaniomes($ses_idpais,'',$tcEuUS,$tipo,'','','');
		if(!empty($data_res)){
			foreach($data_res as $rowP){
				// se resta notacredito,a solicitud de stef.230422
				$arrayCosto1[$rowP['anio']][$rowP['mes']]=$rowP['costo'] - $rowP['notacredito'];
				$arrayCantidad1[$rowP['anio']][$rowP['mes']]=$rowP['numero'];
			}
		}

		// agregar TC al cuadro
		$data_res=$reporteproy->selec_res_TC_analis($ses_idpais,'','',$tcEuUS);
		
		if(!empty($data_res)){
			foreach($data_res as $rowP) {
				$arrayCosto1[$rowP['anio']][$rowP['mes']]+=$rowP['costo'];
				$arrayCantidad1[$rowP['anio']][$rowP['mes']]+=$rowP['numero'];
			}
		}*/
		
		
		$resVentasMesAnioTlr=$home->select_perdidaMesAnio($ses_idpais,$aniof,$tceu_dol);

		/* INICIO Reporte Monto Total de Ventas */

		$resVentasMesAnioTotal=$reporteproy->selec_res_proyxgrupoxanio_total($tcEuUS,$ses_idpais,$anioFinal);

		$resVentasMesAnioTotalOld=$reporteproy->selec_res_proyxgrupoxanio_total($tcEuUS,$ses_idpais,$anioInicial);
		/* FIN Reporte Monto Total de Ventas */
	
		$resVentasCliente=$home->select_ventasClienteAnio($ses_idpais);
		$resVentasTC=$home->select_ventasTcAnio($ses_idpais,$tceu_dol);
		//$resVentasLab=$home->select_ventasLabAnio($ses_idpais);
		$resVentasLab=$home->select_ventasCategoriaNombre($ses_idpais);
		$resVentasLabDatos=$home->select_ventasCategoriaAnio($ses_idpais);
		if(!empty($resVentasLabDatos)){
			foreach($resVentasLabDatos as $row){
				$arrayCatDatos[$row['id_categoria']][$row['anio']]=$row['subtotal'];
			}
		}

		include("../vista/home.php");
	}else if($tipo=='C'){
		
		$id_categoria=1;
		$resdiasAuditoria=$home->select_diasauditoria($ses_idpais,$id_categoria);
		if(!empty($resdiasAuditoria)){
			foreach($resdiasAuditoria as $row){
				$arrayAudit[$row['fecha']]=$row['dias'];
			}
		}
		
		$id_categoria=5;
		//$resdiasDecision=$home->select_diasauditoria($ses_idpais,$id_categoria);
		$resdiasDecision=$home->select_diasauditoria_mod($ses_idpais,$id_categoria);
		if(!empty($resdiasDecision)){
			foreach($resdiasDecision as $row){
				$arrayDecision[$row['fecha']]=$row['dias'];
			}
		}
		
		
		$resdiasActividad=$home->select_diasactividad($ses_idpais);
		if(!empty($resdiasActividad)){
			foreach($resdiasActividad as $row){
				$arrayActividad[$row['fecha']]=$row['dias'];
			}
		}
		
		$resdiasPrograma=$home->select_diasprograma($ses_idpais);
		if(!empty($resdiasPrograma)){
			foreach($resdiasPrograma as $row){
				$arrayPrograma[$row['fecha']]=$row['dias'];
			}
		}
		
		$catActividad=$home->select_cat_actividad();
		$catPrograma=$home->select_cat_programa();
		
		include("../vista/home_certi.php");
		
	}else if($tipo=='D'){
		include("../vista/home_inv.php");
	}

}else if(!empty($_POST['accion']) and $_POST['accion']=='menu'){
	// open formualrio para agregar
	$res_enlacesPri=$usuario->enlacesPrimary_new($ses_idauditor,$ses_idpais);
	
	$res_enlacesSec=$usuario->enlacesSecundar_new($ses_idauditor,$ses_idpais);
	
	if(!empty($res_enlacesSec)){
		foreach($res_enlacesSec as $row){
			$array_enlacesId[$row['id_enlace']]=$row['id_menu'];
			$array_enlacesName[$row['id_enlace']]=$row['nombre'];
			$array_enlacesFuncion[$row['id_enlace']]=$row['control'];
			$array_enlacesAccion[$row['id_enlace']]=$row['accion'];
			$array_enlacesIcono[$row['id_enlace']]=$row['icono'];
		}
	}

	include("../vista/menu.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='logout'){
	 if(!empty($_SESSION['sessionkey']))
		 $url="https://login.microsoftonline.com/common/wsfederation?wa=wsignout1.0";
	 else
		 $url="index.php?v1";
	 session_destroy();
	 
	 echo $url;
}	


?>
