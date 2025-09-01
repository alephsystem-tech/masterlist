<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/reportes_proy_modelo.php");
include("../modelo/prg_pais_modelo.php");
include("../modelo/prg_proyectocosto_modelo.php");
include("../modelo/prg_auditor_modelo.php");
include("../modelo/prg_tipoactividad_modelo.php");
include("../modelo/tc_datos_modelo.php");
include("../modelo/lab_resultado_modelo.php");

$reporteproy=new reportes_proy_model();
$pais=new prg_pais_model();
$proyectocosto=new prg_proyectocosto_model();
$prgauditor=new prg_auditor_model();
$tipoactividad=new prg_tipoactividad_model();
$tcdatos=new tc_datos_model();
$labresultado=new lab_resultado_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

//***********************************************************

	//**********************************
	// mostrar capacidad auditor
	//**********************************
if(!empty($_POST['accion']) and $_POST['accion']=='repDetalle'){

    include("../vista/reporteproy/index_detalleProy.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_repDetalle'){

	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$fechai="";
	$fechaf="";
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" proyect ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery =" AND prg_proyecto.id_pais ='$sess_codpais' " ;
	
	## Total number of records without filtering
	$data_maxOF=$reporteproy->selec_total_reporte_detProyecto($searchQuery);
	$totalRecords = $data_maxOF['total'];

		
	if($fechai!='') 
		$searchQuery.=" and to_days(concat_ws('/',anio,mes,28))>= to_days('$fechai')";
    if($fechaf!='') 
		$searchQuery.=" and to_days(concat_ws('/',anio,mes,28))<= to_days('$fechaf')";
	if($proyecto!='') 
		$searchQuery.= " and (proyect like '%".$proyecto."%' or prg_proyecto.project_id like '%".$proyecto."%' )";

	## Total number of record with filtering
	$data_maxOF2=$reporteproy->selec_total_reporte_detProyecto($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$reporteproy->select_reporte_detProyecto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	
	
	//print_r($data_OF);
	$data = array();
	if(!empty($data_OF)){
	 foreach($data_OF as $row) {
		$id_proyecto=$row['id_proyecto'];
		$sufi=$id_proyecto ."_".$fechai."_".$fechaf;	
		$edita="<button type='button' id='estproy_".$sufi."'  class='btn  btn_detProyecto'><i class='fas fa-edit'></i> </button>";
		$ubicacion=$row['city']." ".$row['country'];
	  $data[] = array( 
		 "project_id"=>str_replace('"','',json_encode($row['project_id'],JSON_UNESCAPED_UNICODE)),
		 "proyect"=>str_replace('"','',json_encode($row['proyect'],JSON_UNESCAPED_UNICODE)),
		 "programas"=>str_replace('"','',json_encode($row['programas'],JSON_UNESCAPED_UNICODE)),
		 "fax"=>str_replace('"','',json_encode($row['fax'],JSON_UNESCAPED_UNICODE)),
		 "programas"=>$row['programas'],
		 "ubicacion"=>$ubicacion,
		 "telephone"=>$row['telephone'],
		  "edita"=>$edita
		);
	 }
	}

	## Response
	$response = array(
	  "draw" => intval($draw),
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data
	);

	echo json_encode($response);

}else if(!empty($_POST['accion']) and $_POST['accion']=='verDetalleProy'){

	$fechai="";
	$fechaf="";
	if(!empty($_POST['fechai']))
		$fechai = $_POST['fechai'];
	if(!empty($_POST['fechaf']))
		$fechaf = $_POST['fechaf'];
	
	if(!empty($_POST['xls']))
		$xls = $_POST['xls'];
	
	$id_proyecto = $_POST['id_proyecto'];

	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];

	$data_pro=$reporteproy->select_reporte_one_detProyecto($sess_codpais,$id_proyecto);
	$project_id=$data_pro['project_id'];
	$rowCrono=$reporteproy->select_reporte_cronogra_detProyecto($id_proyecto,$fechai,$fechaf);
	$rowAct=$reporteproy->select_reporte_actividad_detProyecto($project_id,$fechai,$fechaf,$sess_codpais);
	$rowDeuCusi=$proyectocosto->select_reporte_proyectocosto($project_id);
    include("../vista/reporteproy/index_detalleProyHistori.php");

	//**********************************
	// mostrar capacidad auditor
	//**********************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='cuadrocomercial'){
	//arrayMontosRes
	
	$anio_fin=date("Y");
	if(!empty($_POST['anio']))
		$anio_fin=$_POST['anio'];
	
	$anio_ini=$anio_fin-1;
	
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	
	
	$id_pais=$sess_codpais;

	$sescosestado="0";
	if($id_pais=='esp')$sescosestado=1;
	elseif(($id_pais=='dom')) $sescosestado=51;
	
	
	/*******************************
		DATOS DE LOS EJECUTIVOS
	********************************/
	$dataAuditor=$reporteproy->select_cuadcomer_auditor($sess_codpais);
	if(!empty($dataAuditor)){
		foreach($dataAuditor as $row) {
			 $arrayEjecutivo[$row['codejecutivo']]=$row['nombres'];
		} 
	}
	
	/****************************************
		DATOS DE LAS RENOVACIONES, VENTAS
	*****************************************/
	/*
	id_grupo
	4 otros
	5 renovacion
	6 venta nueva
	1 ampliacion
	2 baja
	*/

	// resumen x mes y año
	$dataResAnio=$reporteproy->select_cuadcomer_resumenxanio($tcEuUS,$sess_codpais,$anio_fin);
	if(!empty($dataResAnio)){
		foreach($dataResAnio as $rowP){
			$key=$rowP['id_grupo']."-".$rowP['mes']."-".$rowP['codejecutivo'];
			$arrayCosto[$key]=$rowP['costo'];
		}
	}
	
	// añadir ampliacion o reduccion
	$dataAmplia=$reporteproy->select_cuadcomer_ampliaredu($sescosestado,$sess_codpais,$anio_fin);
	if(!empty($dataAmplia)){
		foreach($dataAmplia as $row) {
			$key1="1-".$row['meso']."-".$row['codejecutivo']; // ampliacion
			$key2="2-".$row['meso']."-".$row['codejecutivo']; // reduccion

			if($row['montoservicio']>0)
				$arrayMontos[$row['meso'].".".$row['anioo']][$row['codejecutivo']]=$row['montoservicio'];

			if($row['ampliacion']>0){
				if(!empty($arrayCosto[$key1]))
					$arrayCosto[$key1]+=$row['ampliacion'];
				else	
					$arrayCosto[$key1]=$row['ampliacion'];
				
				$arrayAmp[$key1]=abs($row['ampliacion']);
			}	
			
			if($row['reduccion']<>0){
				if(!empty($arrayCosto[$key2]))
					$arrayCosto[$key2]+=$row['reduccion'];
				else	
					$arrayCosto[$key2]=$row['reduccion'];		
		
				$arrayRed[$key2]=abs($row['reduccion']);
			}	
		}
	}
	
	// monto facturacion total
	$dataAmplia=$reporteproy->select_cuadcomer_ampliaredu(0,$sess_codpais,$anio_fin);
	if(!empty($dataAmplia)){
		foreach($dataAmplia as $row) {
			if($row['montoservicio']>0)
				$arrayFactura[$row['meso'].".".$row['anioo']][$row['codejecutivo']]=$row['montoservicio'];
		}
	}
	
	// resumen x  año
	$dataResumenxAnio=$reporteproy-> select_cuadcomer_resumenxanioxGrupo($tcEuUS,$sess_codpais,$anio_fin);
	if(!empty($dataResumenxAnio)){
		foreach($dataResumenxAnio as $rowP) {
			$key=$rowP['id_grupo']."-".$rowP['codejecutivo'];
			$arrayCostoRes[$key]=$rowP['costo'];
		}
	}
	
	
	// añadir ampliacion o reduccion anual
	$dataResumenxAnio=$reporteproy->select_cuadcomer_ampliareducexanio($sescosestado,$sess_codpais,$anio_fin);
	if(!empty($dataResumenxAnio)){
		foreach($dataResumenxAnio as $row) {
			$key1="1-".$row['codejecutivo']; // ampliacion
			$key2="2-".$row['codejecutivo']; // reduccion

			if($row['montoservicio']>0)
				$arrayMontosRes[$row['anioo']][$row['codejecutivo']]=$row['montoservicio'];


			if($row['ampliacion']>0){
				if(!empty($arrayCostoRes[$key1]))
					$arrayCostoRes[$key1]+=$row['ampliacion'];
				else	
					$arrayCostoRes[$key1]=$row['ampliacion'];
				
				$arrayAmpxAud[$key1]=$row['ampliacion'];
			}	
			
			if($row['reduccion']<>0){
				if(!empty($arrayCostoRes[$key2]))
					$arrayCostoRes[$key2]+=$row['reduccion'];
				else	
					$arrayCostoRes[$key2]=$row['reduccion'];		
				
				$arrayRedxAud[$key2]=abs($row['reduccion']);		
			}
		}
	}
	
	/***********************************************************************
		DATOS DE METAS MONTOS X EJECUTIVO X MES PUESTAS POR JEFATURA
	************************************************************************/

	//*******************************************************************
	// trer montos del año pasado totales del cuadro temporal x añio x mes
	$dataResumenxAnio=$reporteproy->select_cuadcomer_ventaanterior($anio_ini,$sess_codpais);
	if(!empty($dataResumenxAnio)){
		foreach($dataResumenxAnio as $row) {
			$arrayMontos[$row['mes'].".".$row['anio']][$row['codejecutivo']]=$row['monto'];
			$arrayCuantos[$row['mes'].".".$row['anio']][$row['codejecutivo']]=$row['cuanto'];
			$arrayComercial[$row['mes'].".".$row['anio']][$row['codejecutivo']]=$row['comercial'];
		}
	}
	
	// trer montos del año pasado totales del cuadro temporal x añio
	$dataResumenxAnio=$reporteproy->select_cuadcomer_ventaanteriorxAudit($anio_ini,$sess_codpais);
	if(!empty($dataResumenxAnio)){
		foreach($dataResumenxAnio as $row) {
			$arrayMontosRes[$row['anio']][$row['codejecutivo']]=$row['monto'];
			$arrayCuantosRes[$row['anio']][$row['codejecutivo']]=$row['cuanto'];
		}
	}
	
	// tarer datos de cta x cobrar
	
	$dataResumenxAnio=$reporteproy->select_cuadcomer_ctacobrar($anio_ini,$sess_codpais);
	if(!empty($dataResumenxAnio)){
		foreach($dataResumenxAnio as $row) {
			$arrayCtacobrar[$row['mes'].".".$row['anio']][$row['codejecutivo']]=$row['monto'];
		}
	}
	
	
	// fin tabla temporal
	//*******************************************************************

	/***********************************************************************
		DATOS DE  MONTOS ANIO ACTUAL
	************************************************************************/
	
	// RESUMEN X AÑO X MES
	$dataResumenxAnio=$reporteproy->select_cuadcomer_resxanioxmesxaudt($anio_fin,$sess_codpais,$sescosestado);
	if(!empty($dataResumenxAnio)){
		foreach($dataResumenxAnio as $row) {
			 $arrayMontosFoto[$row['meso'].".".$row['anioo']][$row['codejecutivo']]=$row['totalFoto'];
			 // tare el monto del año en curso- falta sumar el disgregado de cuadro comercial
			 $arrayCuantos[$row['meso'].".".$row['anioo']][$row['codejecutivo']]=$row['cuantos'];
		}
	}
	
	// RESUMEN X AÑO
	$dataResumenxAnio=$reporteproy->select_cuadcomer_resxanioxaudt($anio_fin,$id_pais,$sescosestado);
	if(!empty($dataResumenxAnio)){
		foreach($dataResumenxAnio as $row) {
			 $arrayCuantosRes[$row['anioo']][$row['codejecutivo']]=$row['cuantos'];
		}
	}
	
	/***********************************************************************
		DATOS DE  MONTOS ANIO ACTUAL PERO MES ORGINAL. YA NO USADO
	************************************************************************/
	
    include("../vista/reporteproy/index_cuadrocomercial.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='editMetaAnio'){

	$mes = $_POST['mes'];
	$anio = $_POST['anio'];
	
	$sumotros=0;
	$dataRes=$reporteproy->select_mestaanio($mes,$sess_codpais,$anio);
	include("../vista/reporteproy/frm_metaanio.php");	
	 
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_metaanio'){

	$mes = $_POST['mes'];
	$anio = $_POST['anio'];
	
	$reporteproy->delete_mestaanio($mes,$sess_codpais,$anio);

	$dataRes=$reporteproy->select_mestaanioSave($sess_codpais);
	foreach($dataRes as $row){
		$codejecutivo=$row['codejecutivo'];
		if(!empty($_POST['monto_'.$codejecutivo])){
			$monto=$_POST['monto_'.$codejecutivo];
			$cuanto=$_POST['cuanto_'.$codejecutivo];
			$comercial=$_POST['comercial_'.$codejecutivo];
			
			$reporteproy->insert_mestaanio($mes,$sess_codpais,$anio,$codejecutivo,$monto,$cuanto,$comercial);
		}
	}
	
	echo "Se registro los datos.";	

}else if(!empty($_POST['accion']) and $_POST['accion']=='editCtaCobrar'){

	$mes = $_POST['mes'];
	$anio = $_POST['anio'];
	
	$sumotros=0;
	$dataRes=$reporteproy->select_ctacobraranio($mes,$sess_codpais,$anio);
	include("../vista/reporteproy/frm_ctacobraranio.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_ctacobraranio'){

	$mes = $_POST['mes'];
	$anio = $_POST['anio'];
	
	$reporteproy->delete_ctacobraranio($mes,$sess_codpais,$anio);

	$dataRes=$reporteproy->select_mestaanioSave($sess_codpais);
	foreach($dataRes as $row){
		$codejecutivo=$row['codejecutivo'];
		if(!empty($_POST['monto_'.$codejecutivo])){
			$monto=$_POST['monto_'.$codejecutivo];
			$reporteproy->insert_ctacobraranio($mes,$sess_codpais,$anio,$codejecutivo,$monto);
		}
	}
	echo "Se registro los datos.";	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='xlsDetalleProy'){

	$mes = $_POST['mes'];
	$anio = $_POST['anio'];
	$id_auditor = $_POST['id_auditor'];
	
	$sumotros=0;
	$data_OF=$reporteproy->select_dataDetalleAuditorCuacro($sess_codpais,$id_auditor,$anio,$mes);
	include("../vista/reporteproy/data_detalleAuditorComerc.php");		
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='repcuotasxproyectos'){

	$estado_res=$reporteproy->selec_total_reporte_estado($sess_codpais);
    include("../vista/reporteproy/index_repcuotasxproyectos.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_repcuotasxproyectos'){

	## Read value
	$fec_proyi="";
	$fec_proyf="";
	if(!empty($_POST['fec_proyi']))
		$fec_proyi = formatdatedos($_POST['fec_proyi']);
	if(!empty($_POST['fec_proyf']))
		$fec_proyf = formatdatedos($_POST['fec_proyf']);
	
	$fec_facturadoi="";
	$fec_facturadof="";
	if(!empty($_POST['fec_facturadoi']))
		$fec_facturadoi = formatdatedos($_POST['fec_facturadoi']);
	if(!empty($_POST['fec_facturadof']))
		$fec_facturadof = formatdatedos($_POST['fec_facturadof']);
	
	$proyecto = "";
	$codestado = "";
	$project_id = "";
	
	if(!empty($_POST['proyecto']))
		$proyecto = $_POST['proyecto'];
	if(!empty($_POST['codestado']))
		$codestado = $_POST['codestado'];
	
	if(!empty($_POST['project_id']))
		$project_id = $_POST['project_id'];
	
	$isjoin= 0;
	if( $fec_facturadoi!='' or $fec_facturadof!='') 
		$isjoin= 1;
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" prg_proyecto_detalle.coddetalle ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery =" AND prg_proyecto.id_pais ='$sess_codpais' " ;
	
	## Total number of records without filtering
	$data_maxOF=$reporteproy->selec_total_reporte_plancuota($searchQuery);
	$totalRecords = $data_maxOF['total'];

	
	if($proyecto!='') $searchQuery.= " and (proyect like '%".$proyecto."%' or prg_proyecto.project_id like '%".$proyecto."%' )";
	if($project_id!='') $searchQuery.=" and prg_proyecto.project_id='$project_id' ";	
	if($codestado!='') $searchQuery.=" and prg_proyecto_detalle.codestado=$codestado ";	
	if($fec_proyi!='') $searchQuery.=" and to_days(concat_ws('/',anio,mes,28))>= to_days('$fec_proyi')";
    if($fec_proyf!='') $searchQuery.=" and to_days(concat_ws('/',anio,mes,28))<= to_days('$fec_proyf')";
		
	if( $isjoin=='1') $searchQuery.=" and prg_proyecto_detalle.coddetalle in (
		select coddetalle from prg_cronogramapago where flag='1' ";

		if($fec_facturadoi!='') $searchQuery.= " and to_days(fechafactura) >= to_days('$fec_facturadoi')";
		if($fec_facturadof!='') $searchQuery.= " and to_days(fechafactura) <= to_days('$fec_facturadof')";

	if( $isjoin=='1') $searchQuery.= " ) ";

	## Total number of record with filtering
	$data_maxOF2=$reporteproy->selec_total_reporte_plancuota($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$reporteproy->select_reporte_plancuota($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	$data = array();
	if(!empty($data_OF)){
	 foreach($data_OF as $row) {
		$proyect=$row['project_id'] ." ". $row['proyect'];
		$ubicacion=$row['city']." ".$row['country'];
		$fecha1=namemes($row['mes'])." / ".$row['anio'];
		$fecha2="";
		if(!empty($row['meso']) and !empty($row['anioo']))
			$fecha2=namemes($row['meso'])." / ".$row['anioo'];
		$analisis=$row['is_analisis'] ." ". $row['analisisdsc'];
	  
	  $data[] = array( 
		 "proyect"=>str_replace('"','',json_encode($proyect,JSON_UNESCAPED_UNICODE)),
		 "ubicacion"=>str_replace('"','',json_encode($ubicacion,JSON_UNESCAPED_UNICODE)),
		 "analisis"=>str_replace('"','',json_encode($analisis,JSON_UNESCAPED_UNICODE)),
		 "programas"=>str_replace('"','',json_encode($row['programas'],JSON_UNESCAPED_UNICODE)),
		 "comercial"=>str_replace('"','',json_encode($row['comercial'],JSON_UNESCAPED_UNICODE)),
		 "estado"=>str_replace('"','',json_encode($row['estado'],JSON_UNESCAPED_UNICODE)),
		 "condicionpago"=>str_replace('"','',json_encode($row['condicionpago'],JSON_UNESCAPED_UNICODE)),
		 "fax"=>$row['fax'],
		 "ubicacion"=>$ubicacion,
		 "is_viatico"=>$row['is_viatico'],
		 "moneda"=>$row['moneda'],
		 "importe"=>$row['importe'],
		 "numcobranza"=>$row['numcobranza'],
		 "fecha1"=>$fecha1,
		 "fecha2"=>$fecha2
		);
	 }
	}

	## Response
	$response = array(
	  "draw" => intval($draw),
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data
	);

	echo json_encode($response);

}else if(!empty($_POST['accion']) and $_POST['accion']=='verexpAnalRes'){

	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	## Read value
	$fec_proyi="";
	$fec_proyf="";
	if(!empty($_POST['fec_proyi']))
		$fec_proyi = formatdatedos($_POST['fec_proyi']);
	if(!empty($_POST['fec_proyf']))
		$fec_proyf = formatdatedos($_POST['fec_proyf']);
	
	$fec_facturadoi="";
	$fec_facturadof="";
	if(!empty($_POST['fec_facturadoi']))
		$fec_facturadoi = formatdatedos($_POST['fec_facturadoi']);
	if(!empty($_POST['fec_facturadof']))
		$fec_facturadof = formatdatedos($_POST['fec_facturadof']);
	
	$proyecto = $_POST['proyecto'];
	$codestado = $_POST['codestado'];

	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];

	if(empty($_POST['detalle'])){
		$data_pro=$reporteproy->selec_analisis_xproyecto($sess_codpais,$proyecto,$fec_proyi,$fec_proyf);
		foreach($data_pro as $row) {
             $array[$row['id_proyecto']][0]=$row['Real_auditoria'];
             $array[$row['id_proyecto']][1]=$row['Real_viajes'];
             $array[$row['id_proyecto']][2]=$row['Real_decision'];
             $array[$row['id_proyecto']][3]=$row['Real_reporte'];
			 $array[$row['id_proyecto']][4]=$row['Real_otros'];
             $array[$row['id_proyecto']][5]=$row['costoReal'];
		}
	}		
	
	
	if(!empty($_POST['detalle'])){
		$data_OF=$reporteproy->selec_anal_xdetalle($sess_codpais,$proyecto,$fec_proyi,$fec_proyf,$codestado,$tcEuUS,$G_tc);	
		include("../vista/reporteproy/data_datosUtilidad.php");
	}else if($sess_codpais=='col'){
		$data_OF=$reporteproy->selec_anal_xproDetCol($sess_codpais,$proyecto,$fec_proyi,$fec_proyf,$codestado,$tcEuUS,$G_tc);
		include("../vista/reporteproy/data_AnalResumenCol.php");
	}else{
		$data_OF=$reporteproy->selec_anal_xproDet($sess_codpais,$proyecto,$fec_proyi,$fec_proyf,$codestado,$tcEuUS,$G_tc);
		include("../vista/reporteproy/data_AnalResumen.php");
	}	

}else if(!empty($_POST['accion']) and $_POST['accion']=='verexpPorfactura'){

	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	## Read value
	$fec_proyi="";
	$fec_proyf="";
	if(!empty($_POST['fec_proyi']))
		$fec_proyi = formatdatedos($_POST['fec_proyi']);
	if(!empty($_POST['fec_proyf']))
		$fec_proyf = formatdatedos($_POST['fec_proyf']);
	
	$fec_facturadoi="";
	$fec_facturadof="";
	if(!empty($_POST['fec_facturadoi']))
		$fec_facturadoi = formatdatedos($_POST['fec_facturadoi']);
	if(!empty($_POST['fec_facturadof']))
		$fec_facturadof = formatdatedos($_POST['fec_facturadof']);
	
	$proyecto = $_POST['proyecto'];
	$codestado = $_POST['codestado'];

	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];

	
	$data_OF=$reporteproy->selec_por_fact($sess_codpais,$proyecto,$fec_proyi,$fec_proyf,$codestado,$tcEuUS,$G_tc,$fec_facturadoi,$fec_facturado);	
	include("../vista/reporteproy/data_proyctoxfactura.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='verexpPorDetalle'){

	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	## Read value
	$fec_proyi="";
	$fec_proyf="";
	if(!empty($_POST['fec_proyi']))
		$fec_proyi = formatdatedos($_POST['fec_proyi']);
	if(!empty($_POST['fec_proyf']))
		$fec_proyf = formatdatedos($_POST['fec_proyf']);
	
	$fec_facturadoi="";
	$fec_facturadof="";
	if(!empty($_POST['fec_facturadoi']))
		$fec_facturadoi = formatdatedos($_POST['fec_facturadoi']);
	if(!empty($_POST['fec_facturadof']))
		$fec_facturadof = formatdatedos($_POST['fec_facturadof']);
	
	$proyecto = $_POST['proyecto'];
	$codestado = $_POST['codestado'];

	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];

	$isjoin= 0;
	if($fec_facturadoi!='' or $fec_facturadof!='') 
		$isjoin= 1;
		
	$data_OF=$reporteproy->selec_por_detalle($sess_codpais,$proyecto,$fec_proyi,$fec_proyf,$codestado,$fec_facturadoi,$fec_facturado,$isjoin);	
	include("../vista/reporteproy/data_proyctoxdetalle.php");

// reporte resumen

}else if(!empty($_POST['accion']) and $_POST['accion']=='repcuotasxproyectos_res'){
	// $anio=date("Y");
	$estado_res=$reporteproy->selec_total_reporte_estado($sess_codpais);
	$auditor_res=$prgauditor->select_auditor_select($sess_codpais);
    include("../vista/reporteproy/index_repcuotasxproyectos_res.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='ver_repcuotasxproy_res'){
	
	$codejecutivo = $_POST['codejecutivo'];
	$codestado = $_POST['codestado'];
	
	
	$anio = $_POST['anio'];
	//data_proyres
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	$tipo='1,7';
	$proyecto="";
	 
	$data_res=$reporteproy->selec_res_xaniomes($sess_codpais,$proyecto,$tcEuUS,$tipo,$codestado,$anio,$codejecutivo);
	
	foreach($data_res as $rowP){
		// se resta notacredito,a solicitud de stef.230422
		$arrayCosto1[$rowP['anio']][$rowP['mes']]=$rowP['costo'] - $rowP['notacredito'];
		$arrayCantidad1[$rowP['anio']][$rowP['mes']]=$rowP['numero'];
	}
	
	
	
	// agregar TC al cuadro
	$data_res=$reporteproy->selec_res_TC_analis($sess_codpais,$proyecto,$anio,$tcEuUS);
	
	if(!empty($data_res)){
		foreach($data_res as $rowP) {
			$arrayCosto1[$rowP['anio']][$rowP['mes']]+=$rowP['costo'];
			$arrayCantidad1[$rowP['anio']][$rowP['mes']]+=$rowP['numero'];
		}
	}
	
	// agregar lab al cuadro
	$data_res=$reporteproy->selec_res_labresultado($sess_codpais,$proyecto,$anio);
	
	if(!empty($data_res)){
		foreach($data_res as $rowP) {
			$arrayCosto1[$rowP['anio']][$rowP['mes']]+=$rowP['costo'];
			$arrayCantidad1[$rowP['anio']][$rowP['mes']]+=$rowP['numero'];
		}
	}

		// agregar etiqueta logos 15102024
	$data_res=$reporteproy->selec_res_etiqueta_logo($sess_codpais,$anio);
	
	if(!empty($data_res)){
		foreach($data_res as $rowP) {
			$arrayCosto1[$rowP['anio']][$rowP['mes']]+=$rowP['costo'];
			$arrayCantidad1[$rowP['anio']][$rowP['mes']]+=$rowP['numero'];
		}
	}

	$tipo='5';
	$data_res=$reporteproy->selec_res_xaniomes($sess_codpais,$proyecto,$tcEuUS,$tipo,$codestado,$anio,$codejecutivo);
	foreach($data_res as $rowP){
		$arrayCosto2[$rowP['anio']][$rowP['mes']]=$rowP['costo'];
		$arrayCantidad2[$rowP['anio']][$rowP['mes']]=$rowP['numero'];
	}
	
	//********************************************************************************
	// monto de analisis tc
	$data_res=$reporteproy->selec_res_TC($sess_codpais,$proyecto,$anio,$tcEuUS);
	if(!empty($data_res)){
		foreach($data_res as $rowP){
			$arrayCosto3[$rowP['anio']][$rowP['mes']]=$rowP['costo'];
			$arrayCantidad3[$rowP['anio']][$rowP['mes']]=$rowP['numero'];
		}
	}
	//********************************************************************************
	
	// top20
	$data_proyres=$reporteproy->selec_res_proyectos($sess_codpais,$proyecto,$anio,$codejecutivo,$tcEuUS,$codestado);
	
	//reporte ventas
	
	if(!empty($_POST['anio']))
		$anio = $_POST['anio'];
	else
		$anio=date("Y");
	

	// Monto Proyectos Analisis x mes x anio
	$data_res=$reporteproy->selec_res_labresultado($sess_codpais,$proyecto,$anio);
	if(!empty($data_res)){
		foreach($data_res as $rowP){
			$arrayCosto[8][$rowP['mesanio']]=$rowP['costo'];
			$arrayNumero[8][$rowP['mesanio']]=$rowP['numero'];
		}
	}
	
	unset($SumNumero);
	unset($SumDolares);
	
	// suma ampliacion + carta cor 14
	$data_carta=$reporteproy->selec_cartaxampliacionxanio($tcEuUS,$sess_codpais,$anio);
	foreach($data_carta as $row) {
		
		 if(!empty($arrayCosto[14][$row['mes']]))
			$arrayCosto[14][$row['mes']]+=$row['cartacor'];
		else
			$arrayCosto[14][$row['mes']]=$row['cartacor'];
	}	
	
	// suma ampiacion 1
	$data_carta=$reporteproy->selec_cartaxampliacionxanio($tcEuUS,$sess_codpais,$anio);
	foreach($data_carta as $row) {
		 if(!empty($arrayCosto[1][$row['mes']]))
			$arrayCosto[1][$row['mes']]+=$row['ampliacion'];
		else
			$arrayCosto[1][$row['mes']]=$row['ampliacion'];
	}	
			
	

	// 6 es venta nueva, 8 analisis, sons acados de otra tabla
	$data_res=$reporteproy->selec_res_proyxgrupoxanio($tcEuUS,$sess_codpais,$anio,$proyecto,$codestado,$codejecutivo);
	foreach($data_res as $rowP) {
	    //tipo 1 ventas arrayCosto3
		if(empty($arrayCosto[$rowP['id_grupo']][$rowP['mes']]))
			$arrayCosto[$rowP['id_grupo']][$rowP['mes']]=0;

		if($rowP['id_grupo']==4 ){
			if(!empty($arrayCosto[$rowP['id_grupo']][$rowP['mes']]))
				$arrayCosto[$rowP['id_grupo']][$rowP['mes']]+=$rowP['servicio'] - $rowP['tnotacredito'] + $rowP['tcourier'];
			else 
				$arrayCosto[$rowP['id_grupo']][$rowP['mes']]=$rowP['servicio'] - $rowP['tnotacredito'] + $rowP['tcourier'];
			
			// venta nueva
			if(!empty($arrayCosto[6][$rowP['mes']]))
				$arrayCosto[6][$rowP['mes']]+=$rowP['tcursos']; // pedido de stefany
			else
			$arrayCosto[6][$rowP['mes']]=$rowP['tcursos']; // pedido de stefany

		}else if($rowP['id_grupo']==5){ //Grupo: renovación
			 $arrayCosto[$rowP['id_grupo']][$rowP['mes']]=$rowP['mampliacion'] ;

			 if(!empty($arrayCosto[6][$rowP['mes']]))
				$arrayCosto[6][$rowP['mes']]+=$rowP['tcursos']; // pedido de stefany
			else
				$arrayCosto[6][$rowP['mes']]=$rowP['tcursos']; // pedido de stefany 10
	 
		}else if( $rowP['id_grupo']==6){

			if(!empty($arrayCosto[$rowP['id_grupo']][$rowP['mes']]))
				$arrayCosto[$rowP['id_grupo']][$rowP['mes']]+=$rowP['servicio'] + $rowP['tcursos'] - $rowP['tnotacredito'];
			else 
				$arrayCosto[$rowP['id_grupo']][$rowP['mes']]=$rowP['servicio'] + $rowP['tcursos'] - $rowP['tnotacredito'];
			
		}else if($rowP['id_grupo']==1){ // Grupo: ampliacion
				$arrayCosto[$rowP['id_grupo']][$rowP['mes']]+=$rowP['servicio'] - $rowP['tnotacredito'] ; //+ $rowP['tampliacion']
			
			if(!empty($arrayCosto[6][$rowP['mes']]))
				$arrayCosto[6][$rowP['mes']]+=$rowP['tcursos']; // pedido de stefany
			else
				$arrayCosto[6][$rowP['mes']]=$rowP['tcursos']; // pedido de stefany
			
		}else if($rowP['id_grupo']==11){ // Grupo: investigación
			$arrayCosto[$rowP['id_grupo']][$rowP['mes']]+=$rowP['servicio'] - $rowP['tnotacredito'] ; //+ $rowP['tampliacion']
		
			if(!empty($arrayCosto[6][$rowP['mes']]))
				$arrayCosto[6][$rowP['mes']]+=$rowP['tcursos']; // pedido de stefany
			else
				$arrayCosto[6][$rowP['mes']]=$rowP['tcursos']; // pedido de stefany
		
		}else if($rowP['id_grupo']==12){ // Grupo: auditoría adicional
			$arrayCosto[$rowP['id_grupo']][$rowP['mes']]+=$rowP['servicio'] - $rowP['tnotacredito'] ; //+ $rowP['tampliacion']
		
			if(!empty($arrayCosto[6][$rowP['mes']]))
				$arrayCosto[6][$rowP['mes']]+=$rowP['tcursos']; // pedido de stefany
			else
				$arrayCosto[6][$rowP['mes']]=$rowP['tcursos']; // pedido de stefany
			
		}else if($rowP['id_grupo']==13){ // Grupo: no anunciada
			$arrayCosto[$rowP['id_grupo']][$rowP['mes']]+=$rowP['servicio'] - $rowP['tnotacredito'] ; //+ $rowP['tampliacion']
		
			if(!empty($arrayCosto[6][$rowP['mes']]))
				$arrayCosto[6][$rowP['mes']]+=$rowP['tcursos']; // pedido de stefany
			else
				$arrayCosto[6][$rowP['mes']]=$rowP['tcursos']; // pedido de stefany
			
		}else if($rowP['id_grupo']==14){ // Grupo: carta cor
			$arrayCosto[$rowP['id_grupo']][$rowP['mes']]+=$rowP['servicio'] - $rowP['tnotacredito'] ; //+ $rowP['tampliacion']
		
			if(!empty($arrayCosto[6][$rowP['mes']]))
				$arrayCosto[6][$rowP['mes']]+=$rowP['tcursos']; // pedido de stefany
			else
				$arrayCosto[6][$rowP['mes']]=$rowP['tcursos']; // pedido de stefany
			
		}else if($rowP['id_grupo']==10){ //Grupo: cursos academy x
			if(!empty($arrayCosto[$rowP['id_grupo']][$rowP['mes']]))
				$arrayCosto[$rowP['id_grupo']][$rowP['mes']]+=$rowP['servicio'] - $rowP['tnotacredito'] + $rowP['tcursos'] + $rowP['tintercompany'] ;
			else
				$arrayCosto[$rowP['id_grupo']][$rowP['mes']]=$rowP['servicio'] - $rowP['tnotacredito'] + $rowP['tcursos']  + $rowP['tintercompany'];
			
		}else if($rowP['id_grupo']==9){ //Grupo: Intercompany x
			$arrayCosto[$rowP['id_grupo']][$rowP['mes']]=$rowP['costo'];
			
			//$arrayCosto[$rowP['id_grupo']][$rowP['mes']]+=$rowP['servicio'] - $rowP['tnotacredito'] + $rowP['tcursos'] + $rowP['tintercompany'] ;
			
			if($sess_codpais=='esp')
				$arrayCosto[10][$rowP['mes']]+=$rowP['tcursos'] + $rowP['tintercompany'];//medaly 220824 **************************

		}else if($rowP['id_grupo']==2){ //Grupo: Bajas x
			$arrayCosto[$rowP['id_grupo']][$rowP['mes']]=$rowP['servicio'];
		
		}else if($rowP['id_grupo']==15){ // gasto viaje mexico
			$arrayCosto[$rowP['id_grupo']][$rowP['mes']]=$rowP['servicio'] + $rowP['tmontoviatico'];		
				
		}else
			$arrayCosto[$rowP['id_grupo']][$rowP['mes']]+=$rowP['costo'];
		
		
		if($rowP['tauditoria_no_anunciada']>0)
			$arrayCosto[13][$rowP['mes']]+=$rowP['tauditoria_no_anunciada'];
	
		// carta cor, nuevo grupo 10
		//if($rowP['tcartacor']>0)
		//	$arrayCosto[14][$rowP['mes']]+=$rowP['tcartacor'];
		
		if($rowP['tinvestigacion']>0)
			$arrayCosto[11][$rowP['mes']]+=$rowP['tinvestigacion'];
		
		if($rowP['totros']>0)
			$arrayCosto[4][$rowP['mes']]+=$rowP['totros'];
		
		$arrayNumero[$rowP['id_grupo']][$rowP['mes']]=$rowP['numero'];
		
	}
	
	if($sess_codpais!='esp'){	
	// etiqueta y logo. PARA RESUMEN DE PROYECTOS X VENTS
	$data_Logo=$reporteproy->selec_res_etiqueta_logo($sess_codpais,$anio);
	if(!empty($data_Logo)){
		foreach($data_Logo as $row){
			$data_eti[$row['mes']]=$row['costo'];
			
		}
	}
	
	// TCs PARA RESUMEN DE PROYECTOS X VENTS
	$data_restc=$reporteproy->selec_res_TC_analis($sess_codpais,$proyecto,$anio,$tcEuUS);
	if(!empty($data_restc)){
		foreach($data_restc as $row){
			$data_tc[$row['mes']]=$row['costo'];
			
		}
	}
	
	// Muestra laboratorios PARA RESUMEN DE PROYECTOS X VENTS
	$data_resml=$reporteproy->selec_res_labresultado($sess_codpais,$proyecto,$anio);
	if(!empty($data_resml)){
		foreach($data_resml as $row){
			$data_ml[$row['mes']]=$row['costo'];
			
		}
	}
}	
	
	
	//id_grupo=99 es ampliacion, hay que sumar
	unset($data_res);
	$data_res=$reporteproy->selec_res_proyxgrupoxmes_amplia($tcEuUS,$sess_codpais,$anio,$proyecto,$codestado,$codejecutivo);
	
	 foreach($data_res as $rowF) {
		if(!empty($arrayCosto[1][$rowF['mes']]))
			$arrayCosto[99][$rowF['mes']]+=$rowF['costo'];
		else
			$arrayCosto[99][$rowF['mes']]=$rowF['costo'];
	}
	
	//id_grupo=98 es bajas, hay que sumar
	unset($data_res);
	$data_res=$reporteproy->selec_res_proyxgrupoxmes_reduce($tcEuUS,$sess_codpais,$anio,$proyecto,$codestado,$codejecutivo);
	
	 foreach($data_res as $rowF) {
		if(!empty($arrayCosto[2][$rowF['mes']]))
			$arrayCosto[98][$rowF['mes']]+=$rowF['costo'];
		else
			$arrayCosto[98][$rowF['mes']]=$rowF['costo'];
	}

	//´parte semifinal arrayCosto arraResCal arraFee arrayCosto data_res1
	
	$data_res1=$reporteproy->selec_res_estado_grupo($sess_codpais,$tipo=1); // para ventas
	
	if($sess_codpais=='esp')
		$data_res7=$reporteproy->selec_res_estado_grupo($sess_codpais,$tipo=7); // para  ventas
	
	
	$data_res2=$reporteproy->selec_res_estado_grupo($sess_codpais,$tipo=2); // para bajas
	

	$data_resCal=$reporteproy->selec_res_calendario($sess_codpais,$anio);
	foreach($data_resCal as $row){
		$arraResCal[$row['aniomes']]=$row['subtotal'];
	}
	
	$data_resFee=$reporteproy->selec_res_Fess($sess_codpais,$anio,$tcEuUS);
	foreach($data_resFee as $row){
		$arraFee[$row['aniomes']]=$row['subtotal'];
	}
	//data_proyres
	
	$anioinic=date("Y")-3;
    include("../vista/reporteproy/ver_repcuotasxproy_res.php");	

//  reporte excel de varios años data_res2

}else if(!empty($_POST['accion']) and $_POST['accion']=='xlsrepventaxanio'){
	$tipo="1";
	if(!empty($_POST['tipo']))
		$tipo=$_POST['tipo'];
	
	
	$data_res1=$reporteproy->selec_res_estado_grupo($sess_codpais,$tipo);
	
	$proyecto="";
	$anio="";
	$codestado="";
	$codejecutivo="";
	
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	// Monto Proyectos Analisis x mes x anio
	$data_res=$reporteproy->selec_res_labresultado($sess_codpais,$proyecto,$anio);
	if(!empty($data_res)){
		foreach($data_res as $rowP){
			$arrayCosto[8][$rowP['mesanio']]=$rowP['costo'];
			$arrayNumero[8][$rowP['mesanio']]=$rowP['numero'];
		}
	}
	
	
	// 14c arta cor y 1 ampliacion
	unset($data_res);
	$data_res=$reporteproy->selec_anio_cartayamplia($tcEuUS,$sess_codpais);
	 foreach($data_res as $rowP) {
		$arrayCosto[14][$rowP['mesanio']]=$rowP['cartacor'];
		$arrayCosto[1][$rowP['mesanio']]=$rowP['ampliacion'];
	}
	
	// 6 es venta nueva, 8 analisis, sons acados de otra tabla data_res1 10
	unset($data_res);
	$data_res=$reporteproy->selec_anio_ventas($tcEuUS,$sess_codpais,$proyecto,$codestado,$codejecutivo);
	 foreach($data_res as $rowP) {
		if($rowP['id_grupo']==14 or $rowP['id_grupo']==1){
			$arrayCosto[$rowP['id_grupo']][$rowP['mesanio']]+=$rowP['servicio'] - $rowP['tnotacredito'] ; //+ $rowP['tampliacion']
		}
		
		if($rowP['id_grupo']==4 or $rowP['id_grupo']==5 or $rowP['id_grupo']==1 or $rowP['id_grupo']==11 or $rowP['id_grupo']==5 or $rowP['id_grupo']==12 or $rowP['id_grupo']==13 or $rowP['id_grupo']==14){
			
			if($rowP['id_grupo']==4 ){ // otros 
				if(!empty($arrayCosto[$rowP['id_grupo']][$rowP['mesanio']]))
					$arrayCosto[$rowP['id_grupo']][$rowP['mesanio']]+=$rowP['servicio'] - $rowP['tnotacredito'] + $rowP['tcourier'];
				else 
					$arrayCosto[$rowP['id_grupo']][$rowP['mesanio']]=$rowP['servicio'] - $rowP['tnotacredito'] + $rowP['tcourier'];
				
			}else if($rowP['id_grupo']==5){ //Grupo: renovación
				$arrayCosto[$rowP['id_grupo']][$rowP['mesanio']]=$rowP['mampliacion'] ;
				
			}else if($rowP['id_grupo']==11 or $rowP['id_grupo']==12 or $rowP['id_grupo']==13){ // INVESTIGACION, AUDITORIA ADICIONAL,NO ANUNCIADA
				$arrayCosto[$rowP['id_grupo']][$rowP['mesanio']]+=$rowP['servicio'] - $rowP['tnotacredito'] ; 
			}
			
			if(!empty($arrayCosto[6][$rowP['mesanio']]))
				$arrayCosto[6][$rowP['mesanio']]+=$rowP['tcursos']; // pedido de stefany
			else
				$arrayCosto[6][$rowP['mesanio']]=$rowP['tcursos']; // pedido de stefany
		}else if( $rowP['id_grupo']==6){

			if(!empty($arrayCosto[$rowP['id_grupo']][$rowP['mesanio']]))
				$arrayCosto[$rowP['id_grupo']][$rowP['mesanio']]+=$rowP['servicio'] + $rowP['tcursos'] - $rowP['tnotacredito'];
			else 
				$arrayCosto[$rowP['id_grupo']][$rowP['mesanio']]=$rowP['servicio'] + $rowP['tcursos'] - $rowP['tnotacredito'];

		}else if($rowP['id_grupo']==10){ //Grupo: cursos academy x
			if(!empty($arrayCosto[$rowP['id_grupo']][$rowP['mesanio']]))
				$arrayCosto[$rowP['id_grupo']][$rowP['mesanio']]+=$rowP['servicio'] - $rowP['tnotacredito'] + $rowP['tcursos'] + $rowP['tintercompany'] ;
			else
				$arrayCosto[$rowP['id_grupo']][$rowP['mesanio']]=$rowP['servicio'] -  $rowP['tnotacredito'] + $rowP['tcursos']  + $rowP['tintercompany'];
			
		}
		
		// add 5 set 24
		if($rowP['id_grupo']==9 and $sess_codpais=='esp') //Grupo: Intercompany x
			$arrayCosto[10][$rowP['mesanio']]+=$rowP['tcursos'] + $rowP['tintercompany'];//medaly 220824 **************************
		//********	
		
		if($rowP['tauditoria_no_anunciada']>0)
			$arrayCosto[13][$rowP['mesanio']]+=$rowP['tauditoria_no_anunciada'];
	
		if($rowP['tinvestigacion']>0)
			$arrayCosto[11][$rowP['mesanio']]+=$rowP['tinvestigacion'];
		
		if($rowP['totros']>0)
			$arrayCosto[4][$rowP['mesanio']]+=$rowP['totros'];
		
		//$arrayCosto[$rowP['id_grupo']][$rowP['mesanio']]=$rowP['servicio'];
	}
	
	//id_grupo=99 es ampliacion, hay que sumar
	unset($data_res);
	$data_res=$reporteproy->selec_res_proyxgrupoxmes_amplia($tcEuUS,$sess_codpais,$anio,$proyecto,$codestado,$codejecutivo);
	
	 foreach($data_res as $rowF) {
		if(!empty($arrayCosto[1][$rowF['mesanio']]))
			$arrayCosto[99][$rowF['mesanio']]+=$rowF['costo'];
		else
			$arrayCosto[99][$rowF['mesanio']]=$rowF['costo'];
	}
	
	//id_grupo=98 es bajas, hay que sumar
	unset($data_res);
	$data_res=$reporteproy->selec_res_proyxgrupoxmes_reduce($tcEuUS,$sess_codpais,$anio,$proyecto,$codestado,$codejecutivo);
	
	 foreach($data_res as $rowF) {
		if(!empty($arrayCosto[2][$rowF['mesanio']]))
			$arrayCosto[98][$rowF['mesanio']]+=$rowF['costo'];
		else
			$arrayCosto[98][$rowF['mesanio']]=$rowF['costo'];
	}

	include("../vista/reporteproy/xlsrepventaxanio.php");	


	
}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsViaticos'){

	$columnName=" ";
	$columnSortOrder=" ";

	$anio = $_POST['anio'];
	$mes= $_POST['mes'];

	$searchQuery = "";

	if($mes != 0){
		$searchQuery = " and prg_calendario.mes_inicio = $mes and prg_calendario.anio_inicio = $anio and prg_calendario.id_pais='$sess_codpais'";
	}else{
		$searchQuery .= "  and prg_calendario.anio_inicio = $anio and prg_calendario.id_pais='$sess_codpais'";
	}

	$data_OF=$reporteproy->selec_res_calendarioXls($columnName,$columnSortOrder,$searchQuery,$sess_codpais);
	include("../vista/reporteproy/data_xlsViaticos.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsFee'){

	$columnName=" ";
	$columnSortOrder=" ";

	$anio = $_POST['anio'];
	$mes= $_POST['mes'];

	$searchQuery = "";

	if($mes != 0){
		$searchQuery = " and mes = $mes and anio = $anio and prg_proyecto.id_pais='$sess_codpais'";
	}else{
		$searchQuery .= "  and anio = $anio and prg_proyecto.id_pais='$sess_codpais'";
	}

	$data_OF=$reporteproy->selec_res_FeesXls($columnName,$columnSortOrder,$searchQuery,$sess_codpais);
	include("../vista/reporteproy/data_xlsFees.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='xlsrepbajaxanio'){

	$data_res1=$reporteproy->selec_res_estado_grupo($sess_codpais,$tipo=2);
	$proyecto="";
	
	/*
	$anio=date("Y");
	if(!empty($_POST['anio']))
		$anio=$_POST['anio'];
	$codestado=$_POST['codestado'];
	$codejecutivo=$_POST['id_auditor'];
	*/
	
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
		
	// 6 es venta nueva, 8 analisis, sons acados de otra tabla
	
	$data_res=$reporteproy->selec_servicio_mesanio_baja($tcEuUS,$sess_codpais,$proyecto,$codestado,$codejecutivo);
	 foreach($data_res as $rowP) {
		$arrayCosto[2][$rowP['mesanio']]=$rowP['servicio'];
		$arrayNumero[2][$rowP['mesanio']]=$rowP['numero'];
	}
	
	//id_grupo=98 es bajas, hay que sumar
	unset($data_res);
	$data_res=$reporteproy->selec_res_proyxgrupoxmes_reduce($tcEuUS,$sess_codpais,$anio,$proyecto,$codestado,$codejecutivo);
	
	 foreach($data_res as $rowF) {
		if(!empty($arrayCosto[2][$rowF['mesanio']]))
			$arrayCosto[98][$rowF['mesanio']]+=$rowF['costo'];
		else
			$arrayCosto[98][$rowF['mesanio']]=$rowF['costo'];
	}
	
	include("../vista/reporteproy/xlsrepbajaxanio.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsNoIntercompany'){

	
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	## Read value data_res1
	
	$anio = $_POST['anio'];
	$mes="";
	if(!empty($_POST['mes']) and $_POST['mes']!='0') 
		$mes = $_POST['mes'];
	$codestado= $_POST['codestado'];
	$codejecutivo= $_POST['codejecutivo'];
	$tipo="";
	if(!empty($_POST['tipo']) and $_POST['tipo']!='0')
		$tipo= $_POST['tipo'];
	
	$id_proyecto="";
	$nofiltro= $_POST['nofiltro'];
	if(!empty($_POST['id_proyecto'])){
		$id_proyecto= $_POST['id_proyecto'];
		$nofiltro=1;
		
		// agregados al reporte 15102024
		$data_proy=$reporteproy->select_reporte_one_detProyecto($sess_codpais,$id_proyecto);
		$project_id=$data_proy['project_id'];
		$data_TC=$reporteproy->selec_res_TC_analis($sess_codpais,$project_id,$anio,$tcEuUS);
	}	
	
	$data_OF=$reporteproy->selec_res_xlsnointercompany($sess_codpais,$anio,$mes,$tcEuUS,$codestado,$codejecutivo,$tipo,$id_proyecto,$nofiltro);	
	
	if($tipo=='1' or $tipo=='1,3,5,7' or $tipo=='1,7'){
		$data_TC=$reporteproy->selec_res_TC_analis_xls($sess_codpais,$anio,$mes,$tcEuUS);	
		
		$data_add_eti=$reporteproy->selec_res_etiqueta($sess_codpais,$anio,$mes);	
	}	
	
	
	
	
	include("../vista/reporteproy/data_xlsNoIntercompany.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsNoIntercompany2'){

	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	## Read value data_res1
	
	$anio = $_POST['anio'];
	$mes="";
	
	
	if(empty($anio))
			$anio=date("Y");
	
	if(!empty($_POST['mes']) and $_POST['mes']!='0') 
		$mes = $_POST['mes'];
	$codestado= $_POST['codestado'];
	$codejecutivo= $_POST['codejecutivo'];
	$tipo="";
	if(!empty($_POST['tipo']) and $_POST['tipo']!='0')
		$tipo= $_POST['tipo'];
	
	$id_proyecto="";
	$nofiltro= $_POST['nofiltro'];
	if(!empty($_POST['id_proyecto'])){
		$id_proyecto= $_POST['id_proyecto'];
		$nofiltro=1;
		
		// agregados al reporte 15102024
		$data_proy=$reporteproy->select_reporte_one_detProyecto($sess_codpais,$id_proyecto);
		$project_id=$data_proy['project_id'];
		$data_TC=$reporteproy->selec_res_TC_analis($sess_codpais,$project_id,$anio,$tcEuUS);
	}	
	
	$data_OF=$reporteproy->selec_res_xlsnointercompany2($sess_codpais,$anio,$mes,$tcEuUS,$codestado,$codejecutivo,$tipo,$id_proyecto,$nofiltro);	
	
	include("../vista/reporteproy/data_xlsNoIntercompany.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsTC'){
	// data_OF
	$sumaCostoEU=0;
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$tcEuUS=$dataPais['tceu_dol'];

	$columnName=" fecha_emision ";
	$columnSortOrder=" desc ";

	$anio = $_POST['anio'];
	$mes= $_POST['mes'];

	$searchQuery = "";

	if($mes != 0){
		$searchQuery = " and year(fecha_emision) =$anio and month(fecha_emision) =$mes and id_pais='$sess_codpais'";
	}else{
		$searchQuery .= "  and year(fecha_emision) =$anio and id_pais='$sess_codpais'";
	}

	$data_OF=$tcdatos->select_resultadoXls($columnName,$columnSortOrder,$searchQuery,$tcEuUS);

	include("../vista/reporteproy/data_xlsTC.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsAnalisis'){
	
	$columnName=" fechaenvio ";
	$columnSortOrder=" desc ";

	$anio = $_POST['anio'];
	$mes= $_POST['mes'];

	$searchQuery = "";

	if($mes != 0){
		$searchQuery = " and year(fecha_emision) =$anio and month(fecha_emision) =$mes and id_pais='$sess_codpais'";
	}else{
		$searchQuery .= "  and year(fecha_emision) =$anio and id_pais='$sess_codpais'";
	}

	$data_OF=$labresultado->select_resultadoXls($columnName,$columnSortOrder,$searchQuery);

	include("../vista/reporteproy/data_xlsAnalisis.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsServicios'){
	
	$anio = $_POST['anio'];
	$mes= $_POST['mes'];

	$searchQuery = " AND id_grupo IN (7) AND IFNULL(prg_proyecto_detalle.isanulado,'0')='0' ";

	if($mes != 0){
		$searchQuery.= " and anio =$anio and mes =$mes and prg_proyecto.id_pais='$sess_codpais'";
	}else{
		$searchQuery .= "  and anio =$anio and prg_proyecto.id_pais='$sess_codpais'";
	}

	$data_OF=$reporteproy->select_ServiciosXls($searchQuery);

	include("../vista/reporteproy/data_xlsServicios.php");


}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsDetCuotas'){

	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	## Read value
	
	$anio = $_POST['anio'];
	$mes="";
	$id_grupo="";
	$tipo="";
	$all="";
	if(!empty($_POST['tipo'])) $tipo = $_POST['tipo'];
	if(!empty($_POST['mes'])) $mes = $_POST['mes'];
	if(!empty($_POST['id_grupo'])) $id_grupo = $_POST['id_grupo'];
	elseif($id_grupo=='' and $tipo!=''){
		// recuperar los id de bd
		$dataGrupo=$reporteproy->selec_grupobytipo($sess_codpais,$tipo);
		$id_grupo=$dataGrupo['grupo'];
	}
		
	
	
	if(!empty($_POST['all'])){ 
		$all = trim($_POST['all']);
	}
	
	
	$proyecto= $_POST['proyecto'];
	$codestado= $_POST['codestado'];
	$codejecutivo= $_POST['codejecutivo'];
		
	$data_OF=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,$id_grupo,$tcEuUS,$proyecto,$codestado,$codejecutivo,$tipo);	
	
	$arr_grupo=explode(",",$id_grupo);
	
	
	
	if(in_array(6,$arr_grupo) or  (!empty($all) and $all=='all2')){
		//Grupo: Renovación 10
		$data_add1=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,5,$tcEuUS,$proyecto,$codestado,$codejecutivo,'');	
		
		//Grupo: Ampliación
		$data_add2=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,1,$tcEuUS,$proyecto,$codestado,$codejecutivo,'');
		
		//Grupo: Otros
		$data_add_g4=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,4,$tcEuUS,$proyecto,$codestado,$codejecutivo,'');		
		
		//Grupo: Investigación
		$data_add_g11=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,11,$tcEuUS,$proyecto,$codestado,$codejecutivo,'');		
		
		//Grupo: Auditoría adicional
		$data_add_g12=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,12,$tcEuUS,$proyecto,$codestado,$codejecutivo,'');		

		//Grupo: No anunciada
		$data_add_g12=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,13,$tcEuUS,$proyecto,$codestado,$codejecutivo,'');		

		//Grupo: Venta nueva
		$data_add_g6=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,6,$tcEuUS,$proyecto,$codestado,$codejecutivo,'');		

		//Grupo: coea
		$data_add_g14=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,14,$tcEuUS,$proyecto,$codestado,$codejecutivo,'');		

		

	}
	
	// medaly
	if($sess_codpais=='esp'){
		if($id_grupo==10 or $all=='all2'){
			$data_add_g10=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,9,$tcEuUS,$proyecto,$codestado,$codejecutivo,'');		
		}
	}
	
	if(in_array(1,$arr_grupo) or (!empty($all) and $all!='all2')){
		$data_add4=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,'',$tcEuUS,$proyecto,$codestado,$codejecutivo,'',$grupo1456);	
	}
	
	if(in_array(98,$arr_grupo) or (!empty($all) and $all!='all2')){
		$data_add5=$reporteproy->selec_res_proyxgrupoxmes_reduce_lista($tcEuUS,$sess_codpais,$anio,$proyecto,$codestado,$codejecutivo,$mes);		
	}
	

	if(in_array(13,$arr_grupo) or (!empty($all) and $all!='all2')){ //Auditoria No Anunciada
		$data_add_auditoria_no_anunciada=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,'',$tcEuUS,$proyecto,$codestado,$codejecutivo,'',$grupo1456);	
	}
	
	if(in_array(11,$arr_grupo) or (!empty($all) and $all!='all2')){ //Investigación
		$data_add_investigacion=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,'',$tcEuUS,$proyecto,$codestado,$codejecutivo,'',$grupo1456);	
		
	}
	
	if(in_array(14,$arr_grupo) or (!empty($all) and $all!='all2')){ //carta cor
		$data_add_cartacor=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,'',$tcEuUS,$proyecto,$codestado,$codejecutivo,'',$grupo1456);	
		
	}

	if(in_array(4,$arr_grupo) or (!empty($all) and $all!='all2')){ //Otros data_add5
		$data_add_otros=$reporteproy->selec_res_xlsdetalle_xglosa($sess_codpais,$anio,$mes,'',$tcEuUS,$proyecto,$codestado,$codejecutivo,'',$grupo1456);	
	}
	
	if((!empty($all) and $all!='all2')){ //Otros
		$data_add_eti=$reporteproy->selec_res_etiqueta($sess_codpais,$anio,$mes);	
		$data_add_tc=$reporteproy->selec_res_TC_analis_xls($sess_codpais,$anio,$mes,$tcEuUS);	
	}
	
	include("../vista/reporteproy/data_xlsproyctoxdetalle.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsDetEtiqueta'){

	
	## Read value
	
	$anio = $_POST['anio'];
	if(!empty($_POST['mes'])) $mes = $_POST['mes'];
		
	$data_OF=$reporteproy->selec_res_etiqueta($sess_codpais,$anio,$mes);	
	
	include("../vista/reporteproy/data_xlsproyctoxetiqueta.php");

// modulo de proyectos
}else if(!empty($_POST['accion']) and $_POST['accion']=='repcuotasvencer'){
	$estado_res=$reporteproy->select_estado_select($sess_codpais);
	$auditor_res=$prgauditor->select_auditor_select($sess_codpais);
	
    include("../vista/reporteproy/index_repcuotasvencer.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_repcuotasvencer'){
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	## Read value
	$fechai="";
	$fechaf="";
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$codestado = $_POST['codestado'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" proyect ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery =" AND prg_proyecto.id_pais='$sess_codpais' " ;
	
	## Total number of records without filtering
	$data_maxOF=$reporteproy->selec_total_cuota_proyectos($searchQuery);
	$totalRecords = $data_maxOF['total'];

	
	if($proyecto!='') 
		$searchQuery.= " and (proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%')";
		
	if($codestado!='') $searchQuery.=" and prg_proyecto_detalle.codestado=$codestado ";	
	if($id_auditor!='') $searchQuery.=" and prg_proyecto_detalle.codejecutivo=$id_auditor ";	
	if($fechai!='') $searchQuery.=" and to_days(concat_ws('/',anio,mes,01))>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.=" and to_days(concat_ws('/',anio,mes,01))<= to_days('$fechaf')";

	
	## Total number of record with filtering
	$data_maxOF2=$reporteproy->selec_total_cuota_proyectos($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$reporteproy->selec_cuota_proyectos($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	
	
	//print_r($data_OF);
	$data = array();
	if(!empty($data_OF)){
	 foreach($data_OF as $row) {
		$id_proyecto=$row['id_proyecto'];
		
		$ubicacion=$row['city']." ".$row['country'];
	  $data[] = array( 
		 "project_id"=>str_replace('"','',json_encode($row['project_id'],JSON_UNESCAPED_UNICODE)),
		 "proyect"=>str_replace('"','',json_encode($row['proyect'],JSON_UNESCAPED_UNICODE)),
		 "programas"=>str_replace('"','',json_encode($row['programas'],JSON_UNESCAPED_UNICODE)),
		 "telephone"=>str_replace('"','',json_encode($row['telephone'],JSON_UNESCAPED_UNICODE)),
		 "comercial"=>str_replace('"','',json_encode($row['comercial'],JSON_UNESCAPED_UNICODE)),
		 "observacion"=>str_replace('"','',json_encode($row['observacion'],JSON_UNESCAPED_UNICODE)),
		 "estado"=>str_replace('"','',json_encode($row['estado'],JSON_UNESCAPED_UNICODE)),
		 "aniomes"=>$row['aniomes'],
		 "ubicacion"=>$ubicacion,
		 "moneda"=>$row['moneda'],
		 "montototal"=>$row['montototal'],
		);
	 }
	}

	## Response
	$response = array(
	  "draw" => intval($draw),
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data
	);

	echo json_encode($response);

}else if(!empty($_POST['accion']) and $_POST['accion']=='graf_ProyPersonal'){
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	$fechai="";
	$fechaf="";
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	$codejecutivo = $_POST['id_auditor'];
	$codestado = $_POST['codestado'];
	
	$data_res=$reporteproy->selec_grafPers_porcentaje($G_tc,$sess_codpais,$codestado,$codejecutivo,$fechai,$fechai,$proyecto);
	 foreach($data_res as $row) {
       $total=$row['porcentaje'];
     }
	
	$proy_res=$reporteproy->selec_grafPers_detalle($G_tc,$sess_codpais,$codestado,$codejecutivo,$fechai,$fechai,$proyecto,$total);
	
	if(!empty($proy_res)){
		$tmpactividad="";
		$tmpvalor="";
		foreach($proy_res as $row) {
		   if($tmpactividad=='') $tmpactividad="'".caracterquitaFile($row['actividad'])."($row[porcentaje])(US$ $row[venta])'";
		   else $tmpactividad.=",'".caracterquitaFile($row['actividad'])."($row[porcentaje])(US$. $row[venta] )'";
			
		   if($tmpvalor=='') $tmpvalor=$row['venta'];
		   else $tmpvalor.=",".$row['venta'];
		}
	}
    include("../vista/reporteproy/graf_personalProyecto.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='graf_ProyEstado'){
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	$fechai="";
	$fechaf="";
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	$codejecutivo = $_POST['id_auditor'];
	$codestado = $_POST['codestado'];
	
	$data_res=$reporteproy->selec_grafPers_porcentaje($G_tc,$sess_codpais,$codestado,$codejecutivo,$fechai,$fechai,$proyecto);
	 foreach($data_res as $row) {
       $total=$row['porcentaje'];
     }
	
	$proy_res=$reporteproy->selec_grafEsta_detalle($tcEuUS,$G_tc,$sess_codpais,$codestado,$codejecutivo,$fechai,$fechai,$proyecto,$total);
	
	if(!empty($proy_res)){
		$tmpactividad="";
		$tmpvalor="";
		foreach($proy_res as $row) {
			if($tmpactividad=='') $tmpactividad="'".caracterquitaFile($row['actividad'])."($row[porcentaje])(US$ $row[venta])'";
			else $tmpactividad.=",'".caracterquitaFile($row['actividad'])."($row[porcentaje])(US$. $row[venta] )'";
			
			if($tmpvalor=='') $tmpvalor=$row['venta'];
			else $tmpvalor.=",".$row['venta'];
		}
	}
    include("../vista/reporteproy/graf_estadoProyecto.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsCuoProyecto'){
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	$fechai="";
	$fechaf="";
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	$codejecutivo = $_POST['codejecutivo'];
	$codestado = $_POST['codestado'];
	
	$data_OF=$reporteproy->selec_xls_proyecto($tcEuUS,$G_tc,$sess_codpais,$codestado,$codejecutivo,$fechai,$fechaf,$proyecto,$total);
	
    include("../vista/reporteproy/data_XlsCuoProyecto.php");
	

}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlssinCuoProyecto'){
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	$fechai="";
	$fechaf="";
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	$codejecutivo = $_POST['codejecutivo'];
	$codestado = $_POST['codestado'];
	
	$data_OF=$reporteproy->selec_xls_sincuotaproyecto($sess_codpais,$fechai,$fechai);
	
    include("../vista/reporteproy/data_XlssinCuoProyecto.php");	

// reporte de actividades
}else if(!empty($_POST['accion']) and $_POST['accion']=='repactividad'){
	$activi_res=$reporteproy->select_actividad_select($sess_codpais);
	$auditor_res=$prgauditor->select_auditor_select($sess_codpais);
	$rol_res=$reporteproy->select_roles_select($sess_codpais);
	// roles
	$fechai=date("01/m/Y");
    include("../vista/reporteproy/index_repactividad.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_repactividad'){
	
	## Read value
	$fechai="";
	$fechaf="";
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$id_actividad = $_POST['id_actividad'];
	$id_rol = "";
	$flgfinalizo = $_POST['flgfinalizo'];
	
	if(!empty($_POST['id_rol'])){
		if(is_array($_POST['id_rol'])){
			foreach($_POST['id_rol'] as $id){
				if($id_rol=='') $id_rol.="$id";
				else	$id_rol.=",$id";
			}
		}else $id_rol=$_POST['id_rol'];
    }
		
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" fecha ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery =" AND porcentaje>0 and u.id_pais='$sess_codpais' " ;
	
	## Total number of records without filtering
	$data_maxOF=$reporteproy->selec_total_act_index($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if($proyecto!='') $searchQuery.= " and (ref_proyecto like '%$proyecto%' or a.project_id like '%$proyecto%' )";
	if($id_rol!='' ) $searchQuery.=" and ref_rol in ($id_rol) ";	
    //if($project_id!='') $searchQuery.=" and  ";	
	if($id_auditor!='') $searchQuery.=" and a.id_auditor=$id_auditor ";
	if($id_actividad!='') $searchQuery.=" and id_actividad=$id_actividad ";
	if($flgfinalizo!='') $searchQuery.=" and flgfinalizo='$flgfinalizo' ";
        
	if($fechai!='') $searchQuery.= " and to_days(fecha) >= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(fecha) <= to_days('$fechaf')";
			
	## Total number of record with filtering
	$data_maxOF2=$reporteproy->selec_total_act_index($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$reporteproy->selec_act_index_lig($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);

	//print_r($data_OF);
	$data = array();
	if(!empty($data_OF)){
	 foreach($data_OF as $row) {
	  $data[] = array( 
		 "auditor"=>str_replace('"','',json_encode($row['auditor'],JSON_UNESCAPED_UNICODE)),
		 "actividad"=>str_replace('"','',json_encode($row['actividad'],JSON_UNESCAPED_UNICODE)),
		 "subprograma"=>str_replace('"','',json_encode($row['subprograma'],JSON_UNESCAPED_UNICODE)),
		 "project_id"=>str_replace('"','',json_encode($row['project_id'],JSON_UNESCAPED_UNICODE)),
		 "proyecto"=>str_replace('"','',json_encode($row['proyecto'],JSON_UNESCAPED_UNICODE)),
		 "nota"=>str_replace('"','',json_encode($row['nota'],JSON_UNESCAPED_UNICODE)),
		  "comentario"=>str_replace('"','',json_encode($row['comentario'],JSON_UNESCAPED_UNICODE)),
		 "dscflgfinalizo"=>str_replace('"','',json_encode($row['dscflgfinalizo'],JSON_UNESCAPED_UNICODE)),
		 "fecha_f"=>$row['fecha_f'],
		 "diasreales"=>$row['diasreales'],
		 "porcentaje"=>$row['porcentaje'],
		);
	 }
	}

	## Response
	$response = array(
	  "draw" => intval($draw),
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data
	);

	echo json_encode($response);


}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsActividad'){
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	$fechai="";
	$fechaf="";
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$id_actividad = $_POST['id_actividad'];
	$flgfinalizo = $_POST['flgfinalizo'];
	$id_rol = "";
	if(!empty($_POST['id_rol'])){
		if(is_array($_POST['id_rol'])){
			foreach($_POST['id_rol'] as $id){
				if($id_rol=='') $id_rol.="$id";
				else	$id_rol.=",$id";
			}
		}else $id_rol=$_POST['id_rol'];
    }
	
	
	## Search  oculto
	$searchQuery =" AND porcentaje>0 and u.id_pais='$sess_codpais' " ;
	
	if($proyecto!='') 
		$searchQuery.= " and (ref_proyecto like '%$proyect%' or a.project_id like '%$project_id%' )";
	if($id_rol!='' ) $searchQuery.=" and ref_rol in ($id_rol) ";	
    // if($project_id!='') $searchQuery.=" and  ";	
	if($id_auditor!='') $searchQuery.=" and a.id_auditor=$id_auditor ";
	if($id_actividad!='') $searchQuery.=" and id_actividad=$id_actividad ";
	if($flgfinalizo!='') $searchQuery.=" and flgfinalizo='$flgfinalizo' ";
        
	if($fechai!='') $searchQuery.= " and to_days(fecha) >= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(fecha) <= to_days('$fechaf')";
	
	$columnName="  fecha ";
	$columnSortOrder=" asc ";
	$row = 0;
	$rowperpage = 100000;
	
	$data_OF=$reporteproy->selec_act_index_lig($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
    include("../vista/reporteproy/data_XlsActividad.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsActPlan'){
	
	$fechai="";
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);

	$id_auditor = $_POST['id_auditor'];
	$totalpormes=2200;
	
	$data_OF=$reporteproy->select_repActPlanilla($fechai,$sess_codpais,$totalpormes,$id_auditor);
	
    include("../vista/reporteproy/data_XlsActPlanilla.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsDiasFaltan'){
	
	$fechai="";
	$fechaf="";
	
	
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$id_actividad = $_POST['id_actividad'];
	$flgfinalizo = $_POST['flgfinalizo'];
	$id_rol = "";
	if(!empty($_POST['id_rol'])){
		if(is_array($_POST['id_rol'])){
			foreach($_POST['id_rol'] as $id){
				if($id_rol=='') $id_rol.="$id";
				else	$id_rol.=",$id";
			}
		}else $id_rol=$_POST['id_rol'];
    }
	
	$date1 = new DateTime($fechai);
    $date2 = new DateTime($fechaf);
    $diff = $date1->diff($date2);
    $diferencia= $diff->days;
		
	$data=$reporteproy->select_repActocupaAuditor($proyecto,$sess_codpais,$fechai,$fechaf,$id_auditor);
	 foreach($data as $row) {
         $arrayData[$row['id_auditor']][$row['diad']]=$row['porcentaje'];
     } 
	 
		
	$data_OF=$reporteproy->select_auditorlanilla($sess_codpais,$id_auditor);
	
	$data_PR=$reporteproy->select_proyecDinRegistro($sess_codpais,$proyecto,$id_auditor,$fechai,$fechaf);
	
    include("../vista/reporteproy/data_XlsActDiasFaltan.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='viewXlsDiasxmes'){
	
	$fechai="";
	$fechaf="";
	
	
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	
	$anio=explode("/",$fechai)[0];
	
	$id_auditor = $_POST['id_auditor'];
	$id_rol = "";
	if(!empty($_POST['id_rol'])){
		if(is_array($_POST['id_rol'])){
			foreach($_POST['id_rol'] as $id){
				if($id_rol=='') $id_rol.="$id";
				else	$id_rol.=",$id";
			}
		}else $id_rol=$_POST['id_rol'];
    }
	
			
	$data=$reporteproy->select_repdiasmesAuditor($sess_codpais,$fechai,$id_auditor);
	 foreach($data as $row) {
         $arrayData[$row['id_auditor']][$row['mes']]=$row['porcentaje'];
     } 
		
	$data=$reporteproy->select_repdiasmesanio($fechai);
	 foreach($data as $row) {
         $arrayDatames[$row['mes']]=$row['diaslab'];
     } 

	
	$data_OF=$reporteproy->select_auditorlanilla($sess_codpais,$id_auditor);

    include("../vista/reporteproy/data_XlsActDiasxmes.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='grafActProyecto'){
	
	$fechai="";
	$fechaf="";

	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$id_actividad = $_POST['id_actividad'];
	$flgfinalizo = $_POST['flgfinalizo'];
	$id_rol = "";
	if(!empty($_POST['id_rol'])){
		if(is_array($_POST['id_rol'])){
			foreach($_POST['id_rol'] as $id){
				if($id_rol=='') $id_rol.="$id";
				else	$id_rol.=",$id";
			}
		}else $id_rol=$_POST['id_rol'];
    }
	
	
	$data_PR=$reporteproy->select_porcetAuditor($sess_codpais,$proyecto,$id_auditor,$fechai,$fechaf,$id_actividad,$id_rol);
	foreach($data_PR as $row) {
        $total=$row['porcentaje'];
    }
	
	$tmpactividad="";
    $tmpvalor="";
	$data_PR=$reporteproy->select_datActiAuditor($sess_codpais,$proyecto,$id_auditor,$fechai,$fechaf,$id_actividad,$id_rol,$total);
	 foreach($data_PR as $row) {
        if($tmpactividad=='') 
			$tmpactividad="'".utf8_encode($row['actividad'])."($row[porcentaje])($row[dias] dias)'";
        else 
			$tmpactividad.=",'".utf8_encode($row['actividad'])."($row[porcentaje])($row[dias] dias)'";
        
        if($tmpvalor=='') $tmpvalor=$row['porcentaje'];
        else $tmpvalor.=",".$row['porcentaje'];
    }
	$titulo=$lang_reporte_actproy ." (* Top 40 $lang_proyecto)";
	$label="% Ocupacion $lang_proyecto";
    include("../vista/reporteproy/graf_ActProyecto.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='grafActActividad'){
	
	$fechai="";
	$fechaf="";

	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$id_actividad = $_POST['id_actividad'];
	$flgfinalizo = $_POST['flgfinalizo'];
	$id_rol = "";
	if(!empty($_POST['id_rol'])){
		if(is_array($_POST['id_rol'])){
			foreach($_POST['id_rol'] as $id){
				if($id_rol=='') $id_rol.="$id";
				else	$id_rol.=",$id";
			}
		}else $id_rol=$_POST['id_rol'];
    }
	
	
	$data_PR=$reporteproy->select_porcetAuditor($sess_codpais,$proyecto,$id_auditor,$fechai,$fechaf,$id_actividad,$id_rol);
	foreach($data_PR as $row) {
        $total=$row['porcentaje'];
    }
	
	$tmpactividad="";
    $tmpvalor="";
	$data_PR=$reporteproy->select_datActiActividad($sess_codpais,$proyecto,$id_auditor,$fechai,$fechaf,$id_actividad,$id_rol,$total);
	 foreach($data_PR as $row) {
        if($tmpactividad=='') 
			$tmpactividad="'".($row['actividad'])."($row[porcentaje])($row[dias] dias)'";
        else 
			$tmpactividad.=",'".($row['actividad'])."($row[porcentaje])($row[dias] dias)'";
        
        if($tmpvalor=='') $tmpvalor=$row['porcentaje'];
        else $tmpvalor.=",".$row['porcentaje'];
    }
	
	$titulo=$lang_reporte_ocupaact;
	$label=$lang_reporte_ocupaact;
    include("../vista/reporteproy/graf_ActProyecto.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='grafActUSuario'){
	
	$fechai="";
	$fechaf="";

	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$id_actividad = $_POST['id_actividad'];
	$flgfinalizo = $_POST['flgfinalizo'];
	$id_rol = "";
	if(!empty($_POST['id_rol'])){
		if(is_array($_POST['id_rol'])){
			foreach($_POST['id_rol'] as $id){
				if($id_rol=='') $id_rol.="$id";
				else	$id_rol.=",$id";
			}
		}else $id_rol=$_POST['id_rol'];
    }
	
	
	$data_PR=$reporteproy->select_porcetAuditor($sess_codpais,$proyecto,$id_auditor,$fechai,$fechaf,$id_actividad,$id_rol);
	foreach($data_PR as $row) {
        $total=$row['porcentaje'];
    }
	
	$tmpactividad="";
    $tmpvalor="";
	$data_PR=$reporteproy->select_datActiProyecto($sess_codpais,$proyecto,$id_auditor,$fechai,$fechaf,$id_actividad,$id_rol,$total);
	 foreach($data_PR as $row) {
        if($tmpactividad=='') 
			$tmpactividad="'".utf8_encode($row['actividad'])."($row[porcentaje])($row[dias] dias)'";
        else 
			$tmpactividad.=",'".utf8_encode($row['actividad'])."($row[porcentaje])($row[dias] dias)'";
        
        if($tmpvalor=='') $tmpvalor=$row['porcentaje'];
        else $tmpvalor.=",".$row['porcentaje'];
    }
	
	$titulo=$lang_reporte_activi;
	$label='% '. $lang_reporte_ocupapers;
    include("../vista/reporteproy/graf_ActProyecto.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='repSeguimientoRenovacion'){
	$activi_res=$reporteproy->select_actividad_select($sess_codpais);
	$auditor_res=$prgauditor->select_auditor_select($sess_codpais);
	$rol_res=$reporteproy->select_roles_select($sess_codpais);
	// roles
	$fechai=date("01/m/Y");
    include("../vista/reporteproy/index_repseguimientorenovacion.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='repSeguimientoRenovacionData'){
	$proyecto = $_POST['proyecto'];
	$estado_renovacion = $_POST['estado_renovacion'];
	
	$fecha_renovacion_actual = $_POST['fecha_renovacion_actual'];
	$fecha_renovacion_original = $_POST['fecha_renovacion_original'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" nombre_proyecto ";
	$columnSortOrder=" asc ";

	## Search  oculto
	$searchQuery =" and prg_proyecto.id_pais='$sess_codpais' " ;
	
	## Total number of records without filtering
	$data_maxOF=$reporteproy->selec_total_reporte_seguimiento_renovacion($searchQuery);

	$totalRecords = $data_maxOF['total'];


	if($proyecto!='') 
		$searchQuery.= " and (prg_proyecto.project_id like '%".$proyecto."%'  or prg_proyecto.proyect like '%".$proyecto."%')";
	if($estado_renovacion!='') 
		$searchQuery.= " and prg_programacosto.estado_renovacion = $estado_renovacion";

	if($fecha_renovacion_actual!='') {
		$searchQuery.=" and prg_proyecto_detalle.mes >= (date_format(str_to_date('$fecha_renovacion_actual','%m/%Y'),'%m'))*1 ";
		$searchQuery.=" and prg_proyecto_detalle.anio >= (date_format(str_to_date('$fecha_renovacion_actual','%m/%Y'),'%Y'))*1 ";
	}
	if($fecha_renovacion_original!='') {
		$searchQuery.=" and prg_proyecto_detalle.meso >= (date_format(str_to_date('$fecha_renovacion_original','%m/%Y'),'%m'))*1 ";
		$searchQuery.=" and prg_proyecto_detalle.anioo >= (date_format(str_to_date('$fecha_renovacion_original','%m/%Y'),'%Y'))*1 ";
	}
	
	## Total number of record with filtering
	$data_maxOF2=$reporteproy->selec_total_reporte_seguimiento_renovacion($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$reporteproy->selec_reporte_seguimiento_renovacion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	$data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
		
		$data[] = array( 
			"cu_proyecto"=>str_replace('"','',json_encode($row['cu_proyecto'],JSON_UNESCAPED_UNICODE)),
			"nombre_proyecto"=>$row['nombre_proyecto'],
			"ciudad_proyecto"=>str_replace('"','',json_encode($row['ciudad_proyecto'],JSON_UNESCAPED_UNICODE)),
			"pais_proyecto"=>str_replace('"','',json_encode($row['pais_proyecto'],JSON_UNESCAPED_UNICODE)),
			"nombres_ejecutivo_comercial"=>str_replace('"','',json_encode($row['nombres_ejecutivo_comercial'],JSON_UNESCAPED_UNICODE)),
			"nombre_programa"=>str_replace('"','',json_encode($row['nombre_programa'],JSON_UNESCAPED_UNICODE)),
			"nombre_sub_programa"=>str_replace('"','',json_encode($row['nombre_sub_programa'],JSON_UNESCAPED_UNICODE)),
			"fecha_actual_renovacion"=>str_replace('"','',json_encode($row['fecha_actual_renovacion'],JSON_UNESCAPED_UNICODE)),
			"fecha_original_renovacion"=>str_replace('"','',json_encode($row['fecha_original_renovacion'],JSON_UNESCAPED_UNICODE)),
			"moneda_proyecto"=>str_replace('"','',json_encode($row['moneda_proyecto'],JSON_UNESCAPED_UNICODE)),
			"monto_servicio_proyecto"=>str_replace('"','',json_encode($row['monto_servicio_proyecto'],JSON_UNESCAPED_UNICODE)),
			"estado_renovacion"=>str_replace('"','',json_encode($row['estado_renovacion'],JSON_UNESCAPED_UNICODE)),
			);
		}
	}

	## Response
	$response = array(
		"draw" => intval($draw),
		"iTotalRecords" => $totalRecords,
		"iTotalDisplayRecords" => $totalRecordwithFilter,
		"aaData" => $data
	);

	echo json_encode($response);

}else if(!empty($_POST['accion']) and $_POST['accion']=='repSeguimientoRenovacionDataXLS'){	
	
	$proyecto = $_POST['proyecto'];
	$estado_renovacion = $_POST['estado_renovacion'];
	
	$fecha_renovacion_actual = $_POST['fecha_renovacion_actual'];
	$fecha_renovacion_original = $_POST['fecha_renovacion_original'];


	$columnName=" nombre_proyecto ";
	$columnSortOrder=" asc ";

	## Search  oculto
	$searchQuery =" and prg_proyecto.id_pais='$sess_codpais' " ;
		
		

	if($proyecto!='') 
	$searchQuery.= " and (prg_proyecto.project_id like '%".$proyecto."%'  or prg_proyecto.proyect like '%".$proyecto."%')";
	if($estado_renovacion!='') 
		$searchQuery.= " and prg_programacosto.estado_renovacion = $estado_renovacion";

	if($fecha_renovacion_actual!='') {
		$searchQuery.=" and prg_proyecto_detalle.mes >= (date_format(str_to_date('$fecha_renovacion_actual','%m/%Y'),'%m'))*1 ";
		$searchQuery.=" and prg_proyecto_detalle.anio >= (date_format(str_to_date('$fecha_renovacion_actual','%m/%Y'),'%Y'))*1 ";
	}
	if($fecha_renovacion_original!='') {
		$searchQuery.=" and prg_proyecto_detalle.meso >= (date_format(str_to_date('$fecha_renovacion_original','%m/%Y'),'%m'))*1 ";
		$searchQuery.=" and prg_proyecto_detalle.anioo >= (date_format(str_to_date('$fecha_renovacion_original','%m/%Y'),'%Y'))*1 ";
	}

	$row=0;
	$rowperpage=10000;
	
	$data_OF=$reporteproy->selec_reporte_seguimiento_renovacion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	include("../vista/reporteproy/xlsRepSeguimientoRenovacion.php");	
}



?>
