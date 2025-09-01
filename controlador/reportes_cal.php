<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_calendario_modelo.php");
include("../modelo/reportes_cal_modelo.php");
include("../modelo/reportes_proy_modelo.php");
include("../modelo/prg_tipoactividad_modelo.php");
include("../modelo/prg_estadoactividad_modelo.php");
include("../modelo/prg_auditor_modelo.php");
include("../modelo/prg_proyecto_modelo.php");
include("../modelo/prg_pais_modelo.php");
include("../modelo/prg_estadoproyecto_modelo.php");
include("../modelo/prg_programa_modelo.php");

$calendario=new prg_calendario_model();
$reportecal=new reportes_cal_model();
$reporteproy=new reportes_proy_model();
$prgauditor=new prg_auditor_model();
$tipoactividad=new prg_tipoactividad_model();
$estadoactividad=new prg_estadoactividad_model();
$prgproyecto=new prg_proyecto_model();
$pais=new prg_pais_model();
$estadoproyecto=new prg_estadoproyecto_model();	
$programa=new prg_programa_model();

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
if(!empty($_POST['accion']) and $_POST['accion']=='reporte_capacidad_auditor'){

	$fechaini=date("01/m/Y");
	$auditor_res=$prgauditor->select_auditor_select($sess_codpais);
	$tipoact_res=$tipoactividad->select_tipoactividad_select($sess_codpais);
	$estadoact_res=$estadoactividad->select_estadoactividad_select($sess_codpais);
    include("../vista/reportecal/index_capacidad_aud.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_rep_cap_auditor'){

	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$fechai="";
	$fechaf="";
	$id_auditor = $_POST['id_auditor'];
	$id_estadoactividad = $_POST['id_estadoactividad'];
	$id_tipoactividad = $_POST['id_tipoactividad'];
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$programa = $_POST['programa'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" Calendario.inicio_evento,Programa.id_programa ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery =" AND Calendario.id_pais ='$sess_codpais' " ;
	
	## Total number of records without filtering
	$data_maxOF=$reportecal->selec_total_reporte_cap_aud($searchQuery);
	$totalRecords = $data_maxOF['total'];
	
	$searchQuery.= "  ";
	if($id_auditor!='') 
		$searchQuery.= " and Calendario.id_auditor =$id_auditor ";	
	
	
	if($id_estadoactividad!='') 
		$searchQuery.= " and Calendario.id_estadoactividad =$id_estadoactividad ";	
	
	if($id_tipoactividad!='') 
		$searchQuery.= " and Calendario.id_tipoactividad =$id_tipoactividad ";	
	
	if($programa!='') $searchQuery.= " and (Programa.descripcion like '%$programa%' )";
	if($fechai!='') $searchQuery.= " and to_days(Calendario.inicio_evento)>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(fin_evento) <= to_days('$fechaf')";
		
	

	## Total number of record with filtering
	$data_maxOF2=$reportecal->selec_total_reporte_cap_aud($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$reportecal->select_reporte_cap_aud($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	
	
	//print_r($data_OF);
	$data = array();
	if(!empty($data_OF)){
	 foreach($data_OF as $row) {
		 
		 list($hora,$minute,$segundo) = explode(":",$row['dif_horas']);
		 $minute_ = (($minute/60)/8);
		 $dias=($hora/8)+$minute_;
	  $data[] = array( 
		 "project_id"=>str_replace('"','',json_encode($row['project_id'],JSON_UNESCAPED_UNICODE)),
		 "proyecto"=>str_replace('"','',json_encode($row['proyecto'],JSON_UNESCAPED_UNICODE)),
		 "auditor"=>str_replace('"','',json_encode($row['nombreCompleto'],JSON_UNESCAPED_UNICODE)),
		 "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
		 "programas"=>$row['programas'],
		 "dif_horas"=>$row['dif_horas'],
		 "dias"=>$dias,
		 "inicio_evento"=>$row['inicio_evento'],
		 "fin_evento"=>$row['fin_evento'],
		 "observa"=>str_replace('"','',json_encode($row['observacion'],JSON_UNESCAPED_UNICODE)),
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


}else if(!empty($_POST['accion']) and $_POST['accion']=='xlsReporteCapAudi'){
	## Read value
	$fechai="";
	$fechaf="";
	$id_auditor = $_POST['id_auditor'];
	$id_estadoactividad = $_POST['id_estadoactividad'];
	$id_tipoactividad = $_POST['id_tipoactividad'];
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$programa = $_POST['programa'];
	
	$columnName=" Calendario.inicio_evento,Programa.id_programa ";
	$columnSortOrder=" asc ";
	
	## Search  oculto
	$searchQuery =" AND Calendario.id_pais ='$sess_codpais' " ;
	
	## Total number of records without filtering
	
	$searchQuery.= " and Calendario.id_auditor='$id_auditor' ";
	if($id_estadoactividad!='') 
		$searchQuery.= " and Calendario.id_estadoactividad =$id_estadoactividad ";	
	
	if($id_tipoactividad!='') 
		$searchQuery.= " and Calendario.id_tipoactividad =$id_tipoactividad ";	
	
	if($programa!='') $searchQuery.= " and (Programa.descripcion like '%$programa%' )";
	if($fechai!='') $searchQuery.= " and to_days(Calendario.inicio_evento)>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(Calendario.fin_evento) <= to_days('$fechaf')";
	
	$row=0;
	$rowperpage=999999;
	$data_OF=$reportecal->select_reporte_cap_aud($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	include("../vista/reportecal/excel_capacidad_aud.php");	

	//**********************************
	// mostrar identificador calendario
	//**********************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='reporte_programa_calendario'){

	$fechaini=date("01/m/Y");
	$auditor_res=$prgauditor->select_auditor_select($sess_codpais);
	$estadoact_res=$estadoactividad->select_estadoactividad_select($sess_codpais);
    include("../vista/reportecal/index_ind_programa.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_rep_programa_calendario'){

	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$fechai="";
	$fechaf="";
	$id_auditor = $_POST['id_auditor'];
	$id_estadoactividad = $_POST['id_estadoactividad'];
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
		
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" Calendario.id  ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery =" AND Calendario.id_pais ='$sess_codpais'  AND Calendario.id_tipoactividad=5  " ;
	
	## Total number of records without filtering
	$data_maxOF=$reportecal->selec_total_reporte_ind_programa($searchQuery);
	$totalRecords = $data_maxOF['total'];
	
	$searchQuery.= " and Calendario.id_auditor='$id_auditor' ";
	if($id_estadoactividad!='') 
		$searchQuery.= " and Calendario.id_estadoactividad =$id_estadoactividad ";	
	
	if($fechai!='') $searchQuery.= " and to_days(Calendario.inicio_evento)>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(Calendario.fin_evento) <= to_days('$fechaf')";
		
	

	## Total number of record with filtering
	$data_maxOF2=$reportecal->selec_total_reporte_ind_programa($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$reportecal->select_reporte_ind_programa($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	
	
	//print_r($data_OF);
	$data = array();
	if(!empty($data_OF)){
	 
	 foreach($data_OF as $row) {
		 
		 list($hora,$minute,$segundo) = explode(":",$row['dif_horas']);
		 $minute_ = (($minute/60)/8);
		 $dias=($hora/8)+$minute_;
	  $data[] = array( 
		 "programas"=>str_replace('"','',json_encode($row['programas'],JSON_UNESCAPED_UNICODE)),
		 "auditor"=>str_replace('"','',json_encode($row['auditor'],JSON_UNESCAPED_UNICODE)),
		 "id"=>$row['id'],
		 "asunto"=>$row['asunto'],
		 "dif_horas"=>$row['dif_horas'],
		 "dias"=>$dias
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='xlsReporteIndPrograma'){
	## Read value
	$fechai="";
	$fechaf="";
	$id_auditor = $_POST['id_auditor'];
	$id_estadoactividad = $_POST['id_estadoactividad'];
	$id_tipoactividad = $_POST['id_tipoactividad'];
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$programa = $_POST['programa'];
	
	$columnName=" Calendario.inicio_evento ";
	$columnSortOrder=" asc ";
	
	## Search  oculto
	$searchQuery =" AND Calendario.id_pais ='$sess_codpais' AND Calendario.id_tipoactividad=5  " ;
	
	## Total number of records without filtering
	
	$searchQuery.= " and Calendario.id_auditor='$id_auditor' ";
	if($id_estadoactividad!='') 
		$searchQuery.= " and Calendario.id_estadoactividad =$id_estadoactividad ";	
	
	if($fechai!='') $searchQuery.= " and to_days(Calendario.inicio_evento)>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(Calendario.fin_evento) <= to_days('$fechaf')";
	
	$row=0;
	$rowperpage=999999;
	$data_OF=$reportecal->select_reporte_ind_programa($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	include("../vista/reportecal/excel_ind_programa.php");	

	//**********************************
	// mostrar capacidad auditor
	//**********************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='rendicion_read'){

	//$fechaini=date("01/m/Y");
	$auditor_res=$prgauditor->select_auditor_select($sess_codpais);
    include("../vista/reportecal/index_rep_rendicion_read.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_rep_rendicion_read'){
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	## Read value
	
	$arrayValor=$calendario->selec_grupoviatico_calendario();
	if(!empty($arrayValor)){
		foreach($arrayValor as $row){
			$arrayData[$row['tipo_moneda'].$row['tipo'].$row['id_calendario']]=$row['monto'];
		}
	}
	
	$fechai="";
	$fechaf="";
	$id_auditor = $_POST['id_auditor'];
	$proyecto = $_POST['proyecto'];
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
		
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" Calendario.inicio_evento ";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery =" AND Calendario.id_pais ='$sess_codpais' 
			and (Calendario.monto_dolares > 0 OR Calendario.monto_soles > 0) 
			AND Calendario.flag_rendicion = '3'  AND Calendario.id_estadoactividad <> 3 " ;
	
	## Total number of records without filtering
	$data_maxOF=$reportecal->selec_total_reporte_rend_viaticos($searchQuery);
	$totalRecords = $data_maxOF['total'];
	
	$searchQuery.= " ";
	if($id_auditor!='') 
		$searchQuery.= " and Calendario.id_auditor =$id_auditor ";	
	if($proyecto!='') 
		$searchQuery.= " and Calendario.id_proyecto  like '%$proyecto%'";	
	if($fechai!='') $searchQuery.= " and to_days(Calendario.inicio_evento)>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(Calendario.fin_evento) <= to_days('$fechaf')";
		
	

	## Total number of record with filtering
	$data_maxOF2=$reportecal->selec_total_reporte_rend_viaticos($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$reportecal->select_reporte_rend_viaticos($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	
	
	//print_r($data_OF);
	$data = array();
	if(!empty($data_OF)){
	 
	 foreach($data_OF as $row) {
	  $id=$row['id_calendario'];
	  $accion="<button type='button' id='estproy_".$row['idcalendar']."'  class='btn  btn_printRendicion'><i class='fas fa-edit'></i> </button>";
	  $data[] = array( 
		 "asunto"=>str_replace('"','',json_encode($row['asunto'],JSON_UNESCAPED_UNICODE)),
		 "auditor"=>str_replace('"','',json_encode($row['auditor'],JSON_UNESCAPED_UNICODE)),
		 "monto_dolares"=>$row['monto_dolares'],
		 "monto_soles"=>$row['monto_soles'],
		  "utizado_monto_dolares"=>$row['utizado_monto_dolares'],
		  "utizado_monto_soles"=>$row['utizado_monto_soles'],
		 "fecha_inicio_evento"=>$row['fecha_inicio_evento'],
		 "fecha_fin_evento"=>$row['fecha_fin_evento'],
		 "fecha_rendicion"=>$row['fecha_rendicion'],
		 "estado"=>'Aprobado',
		 "id"=>$row['idcalendar'],
		 "accion"=>$accion,
		 "MOVI_soles"=>$arrayData['SMOVI'.$id],
		   "ALIM_soles"=>$arrayData['SALIM'.$id],
		   "HOSP_soles"=>$arrayData['SHOSP'.$id],
		   "CONT_soles"=>$arrayData['SCONT'.$id],
		   "MOVI_dolar"=>$arrayData['USDMOVI'.$id],
		   "ALIM_dolar"=>$arrayData['USDALIM'.$id],
		   "HOSP_dolar"=>$arrayData['USDHOSP'.$id],
		   "CONT_dolar"=>$arrayData['USDCONT'.$id],
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='xlsReporterendAprobado'){
	## Read value
	$fechai="";
	$fechaf="";
	$id_auditor = $_POST['id_auditor'];
	$proyecto = $_POST['proyecto'];

	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	
	$columnName=" Calendario.inicio_evento ";
	$columnSortOrder=" asc ";
	
	## Search  oculto
	$searchQuery =" AND Calendario.id_pais ='$sess_codpais' 
			and (Calendario.monto_dolares > 0 OR Calendario.monto_soles > 0) 
			AND Calendario.flag_rendicion = '3'  AND Calendario.id_estadoactividad <> 3 " ;
	
	## Total number of records without filtering
	
	if($id_auditor!='') 
		$searchQuery.= " and Calendario.id_auditor =$id_auditor ";	
	if($proyecto!='') 
		$searchQuery.= " and Calendario.id_proyecto  like '%$proyecto%'";	
	if($fechai!='') $searchQuery.= " and to_days(Calendario.inicio_evento)>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(Calendario.fin_evento) <= to_days('$fechaf')";
		
	
	$row=0;
	$rowperpage=999999;
	$data_OF=$reportecal->select_reporte_rend_viaticos($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	include("../vista/reportecal/excel_rend_viatico.php");	

	//**********************************
	// reporte planififcacion
	//**********************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='reporte_planifica'){

	$fechaini=date("01/m/Y");
	$auditor_res=$prgauditor->select_auditor_select($sess_codpais);
	$tipoact_res=$tipoactividad->select_tipoactividad_select($sess_codpais);
	$estadoact_res=$estadoactividad->select_estadoactividad_select($sess_codpais);
	
    include("../vista/reportecal/index_rep_planifica.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_rep_planifica'){

	## Read value
	$fechai="";
	$fechaf="";
	$id_auditor = $_POST['id_auditor'];
	$id_estadoactividad = $_POST['id_estadoactividad'];
	$id_tipoactividad = $_POST['id_tipoactividad'];
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" inicio_origen_evento ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery =" AND calendario.id_pais ='$sess_codpais' " ; //AND calendario.id_estadoactividad NOT IN(2,4)
	
	## Total number of records without filtering
	$data_maxOF=$reportecal->selec_total_reporte_planificacion($searchQuery);
	$totalRecords = $data_maxOF['total'];
	
	if($id_auditor!='') 
		$searchQuery.= " and calendario.id_auditor =$id_auditor ";
	
	if($id_estadoactividad!='') 
		$searchQuery.= " and calendario.id_estadoactividad =$id_estadoactividad ";	
	
	if($id_tipoactividad!='') 
		$searchQuery.= " and calendario.id_tipoactividad =$id_tipoactividad ";	
	
	if($proyecto!='') 
		$searchQuery.= " and (proyecto.proyect like '%".$proyecto."%' or proyecto.project_id like '%".$proyecto."%' )";
	
	if($fechai!=''){
		$searchQuery.= " and ( to_days(calendario.inicio_evento)>= to_days('$fechai') or 
				to_days(calendario.fin_evento)  >= to_days('$fechai') ) ";
	}	
	if($fechaf!=''){
		$searchQuery.= " and ( to_days(calendario.fin_evento) <= to_days('$fechaf') 
			or to_days(calendario.inicio_evento) <= to_days('$fechaf') ) ";
	}	
		
	

	## Total number of record with filtering
	$data_maxOF2=$reportecal->selec_total_reporte_planificacion($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$reportecal->select_reporte_planificacion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$fechai,$fechaf);
	
	
	
	//print_r($data_OF);
	$data = array();
	if(!empty($data_OF)){
	 foreach($data_OF as $row) {
		$resta=0;
		$masinfo="";
		if($row['is_sabado']){
			$masinfo.= "(".$row['sabado_dia'].") S&aacute;bado";
			
		}else
			if($row['sabado_dia']>0) $resta+=$row['sabado_dia'];
		
		if($row['is_domingo']){
			if(!empty($row['is_sabado']))
				$masinfo.='<br>';
			$masinfo.= "(".$row['domingo_dia'].") Domingo";
		}else	
			if($row['domingo_dia']>0) $resta+=$row['domingo_dia'];
	
	
		if($row['horas2']<8) $basehorar=$row['horas2'];
		else $basehorar=$row['horas2']-1;
		
		if($row['id_tipoactividad']!='2'){
			if($row['flghorariofijo']=='1'){
				// todos los dias el horario marcado
				// if($row['dif_dias']==1) $resta=0;
				$porc=($row['dif_dias']- $resta) * ($basehorar/8) ;
				
			}else{
				// todos los dias horario completo, menos ultimo dia es diferencia horas
				$porc=($row['dif_dias']-1- $resta)  +  ($basehorar/8) ;
				
			}
		}else{
			if($row['flghorariofijo']=='1'){
				$porc=($row['dif_dias']- $resta) * ($row['horas2']/8) ;
				
			}else{
				$porc=$row['horas']/8 ;
				
			}
			
		}
		
	
	  $data[] = array( 
		 "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
		 "proyect"=>str_replace('"','',json_encode($row['proyect'],JSON_UNESCAPED_UNICODE)),
		 "proycomercial"=>str_replace('"','',json_encode($row['proycomercial'],JSON_UNESCAPED_UNICODE)),
		 "estadoactividad"=>str_replace('"','',json_encode($row['estadoactividad'],JSON_UNESCAPED_UNICODE)),
		 "prod_and_proc"=>str_replace('"','',json_encode($row['prod_and_proc'],JSON_UNESCAPED_UNICODE)),
		 "programa"=>str_replace('"','',json_encode($row['programa'],JSON_UNESCAPED_UNICODE)),
		 "nro_muestra"=>$row['nro_muestra'],
		 "por_dia"=>$row['por_dia'],
		 "nombreCompleto"=>$row['nombreCompleto'],
		 "inicio_evento"=>$row['inicio_evento'],
		 "fin_evento"=>$row['fin_evento'],
		 "dif_dias"=>$row['dif_dias'] - $resta,
		 "horas"=>$row['horas'],
		 "horas2"=>$row['horas2'],
		 "id"=>$row['id'],
		 "dif_horas"=>round($porc,1), // $row['dif_horas'],
		 "masinfo"=>$masinfo,
		 "observacion"=>str_replace('"','',json_encode($row['observacion'],JSON_UNESCAPED_UNICODE)),
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='xlsReportePlanifica'){
	## Read value
	$fechai="";
	$fechaf="";
	$id_auditor = $_POST['id_auditor'];
	$id_estadoactividad = $_POST['id_estadoactividad'];
	$id_tipoactividad = $_POST['id_tipoactividad'];
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	$proyecto = $_POST['proyecto'];
	
	$columnName=" inicio_origen_evento";
	$columnSortOrder=" asc ";
	
	## Search  oculto
	$searchQuery =" AND calendario.id_pais ='$sess_codpais' " ;
	
	if($id_auditor!='') 
		$searchQuery.= " and calendario.id_auditor =$id_auditor ";
	
	if($id_estadoactividad!='') 
		$searchQuery.= " and calendario.id_estadoactividad =$id_estadoactividad ";	
	
	if($id_tipoactividad!='') 
		$searchQuery.= " and calendario.id_tipoactividad =$id_tipoactividad ";	
	
	if($proyecto!='') 
		$searchQuery.= " and (proyecto.proyect like '%".$proyecto."%' or proyecto.project_id like '%".$proyecto."%' )";

	if($fechai!=''){
		$searchQuery.= " and ( to_days(calendario.inicio_evento)>= to_days('$fechai') or 
				to_days(calendario.fin_evento)  >= to_days('$fechai') ) ";
	}	
	if($fechaf!=''){
		$searchQuery.= " and ( to_days(calendario.fin_evento) <= to_days('$fechaf') 
			or to_days(calendario.inicio_evento) <= to_days('$fechaf') ) ";
	}
		
	
	$row=0;
	$rowperpage=999999;
	$data_OF=$reportecal->select_reporte_planificacion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$fechai,$fechaf);
	include("../vista/reportecal/excel_rep_planifica.php");		
	
	//**********************************
	// reporte reprogramacion
	//**********************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='reporte_reprogramacion'){

	//$fechaini=date("01/m/Y");
	$auditor_res=$prgauditor->select_auditor_select($sess_codpais);
	$proyecto_res=$prgproyecto->select_proyecto_Select($sess_codpais);
	
    include("../vista/reportecal/index_rep_reprogramacion.php");		

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_reporte_reprogramacion'){

	## Read value
	$fechai="";
	$fechaf="";
	$id_auditor = $_POST['id_auditor'];
	$id_proyecto = $_POST['id_proyecto'];
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" Calendario.parent ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery ="  AND Calendario.id IN (SELECT parent FROM prg_calendario WHERE flag = 1 
		AND IFNULL(parent,'') !='' AND id_pais ='$sess_codpais' ) 
		AND Calendario.id_pais ='$sess_codpais' " ;
	
	## Total number of records without filtering
	$data_maxOF=$reportecal->selec_total_reporte_reprogramacion($searchQuery);
	$totalRecords = $data_maxOF['total'];
	
	$searchQuery.= " and Proyecto.id_proyecto='$id_proyecto' ";
	
	if($id_auditor!='') 
		$searchQuery.= " and Calendario.id_auditor =$id_auditor ";
	
	if($fechai!='') $searchQuery.= " and to_days(Calendario.inicio_evento)>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(Calendario.fin_evento) <= to_days('$fechaf')";
		
	

	## Total number of record with filtering
	$data_maxOF2=$reportecal->selec_total_reporte_reprogramacion($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$reportecal->select_reporte_reprogramacion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	
	
	//print_r($data_OF);
	$data = array();
	if(!empty($data_OF)){
	 foreach($data_OF as $row) {
	
		  $editar="<button type='button' id='estproy_".$row['parent_id']."'  class='btn  btn_printReprograma'><i class='fas fa-edit'></i> </button>";
		  
	  $data[] = array( 
		 
		 "proyect"=>str_replace('"','',json_encode($row['proyect'],JSON_UNESCAPED_UNICODE)),
		 "auditor"=>str_replace('"','',json_encode($row['auditor'],JSON_UNESCAPED_UNICODE)),
		 "fechainicio"=>$row['fechainicio'],
		 "fechafinal"=>$row['fechafinal'],
		 "descripcion"=>$row['descripcion'],
		 "id"=>$row['id'],
		 "parent_id"=>$row['parent_id'],
		 "editar"=>$editar,
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='xlsReporteReprograma'){
	## Read value
	$fechai="";
	$fechaf="";
	$id_auditor = $_POST['id_auditor'];
	$id_proyecto = $_POST['id_proyecto'];
	
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	
	$columnName=" Calendario.parent ";
	$columnSortOrder=" asc ";
	
	$searchQuery ="  AND Calendario.id IN (SELECT parent FROM prg_calendario WHERE flag = 1 
		AND IFNULL(parent,'') !='' AND id_pais ='$sess_codpais' ) 
		AND Calendario.id_pais ='$sess_codpais' " ;

	$searchQuery.= " and Proyecto.id_proyecto='$id_proyecto' ";
	
	if($id_auditor!='') 
		$searchQuery.= " and Calendario.id_auditor =$id_auditor ";
	
	if($fechai!='') $searchQuery.= " and to_days(Calendario.inicio_evento)>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(Calendario.fin_evento) <= to_days('$fechaf')";
		
	
	$row=0;
	$rowperpage=999999;
	$data_OF=$reportecal->select_reporte_reprogramacion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	include("../vista/reportecal/excel_rep_reprograma.php");	

	//**********************************
	// reporte reprogramacion
	//**********************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='vinculopc'){
	$fechaini=date("01/m/Y");
    include("../vista/reportecal/index_vinculopc.php");		

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_reporte_vinculopc'){

	## Read value
	$fechai="";
	$fechaf="";
	$flgvnculo = $_POST['flgvnculo'];
	$proyecto = $_POST['proyecto'];
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" inicio_evento ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery ="  AND c.id_pais='$sess_codpais' 
				AND c.id_tipoactividad IN (5,225,158,309,182,41,117,197) 
				and id_estadoactividad  in (3,1) " ;
	
	## Total number of records without filtering
	$data_maxOF=$reportecal->selec_total_reporte_vinculopc($searchQuery);
	$totalRecords = $data_maxOF['total'];
	
	$searchQuery.= " and ( p.project_id like '%$proyecto%' or p.proyect like '%$proyecto%') ";
	
	if($flgvnculo=='s') $searchQuery.= " and IFNULL(e.descripcion,'')!='' ";
	if($flgvnculo=='n') $searchQuery.= " and IFNULL(e.descripcion,'')='' ";
	
	if($fechai!='') $searchQuery.= " and to_days(c.inicio_evento)>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(c.fin_evento) <= to_days('$fechaf')";
		
	

	## Total number of record with filtering
	$data_maxOF2=$reportecal->selec_total_reporte_vinculopc($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$reportecal->select_reporte_vinculopc($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	
	
	//print_r($data_OF);
	$data = array();
	if(!empty($data_OF)){
	 foreach($data_OF as $row) {
		$masinfo=$row['estadoproyecto'] ." ".$row['mes']." ".$row['anio'];
		$data[] = array( 
		 
		 "codproyecto"=>str_replace('"','',json_encode($row['codproyecto'],JSON_UNESCAPED_UNICODE)),
		 "proyecto"=>str_replace('"','',json_encode($row['proyecto'],JSON_UNESCAPED_UNICODE)),
		 "programa"=>$row['programa'],
		 "inicio"=>$row['inicio'],
		 "fin"=>$row['fin'],
		 "auditor"=>$row['auditor'],
		 "tipoactividad"=>$row['tipoactividad'],
		 "masinfo"=>$masinfo,
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='xlsReporteVinculoC'){
	## Read value
	
	$fechai="";
	$fechaf="";
	$flgvnculo = $_POST['flgvnculo'];
	$proyecto = $_POST['proyecto'];
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	
	$columnName=" inicio_evento ";
	$columnSortOrder=" asc ";
	
	## Search  oculto
	$searchQuery ="  AND c.id_pais='$sess_codpais' 
				AND c.id_tipoactividad IN (5,225,158,309,182,41,117,197) 
				and id_estadoactividad  in (3,1) " ;
	
	$searchQuery.= " and ( p.project_id like '%$proyecto%' or p.proyect like '%$proyecto%') ";
	
	if($flgvnculo=='s') $searchQuery.= " and IFNULL(e.descripcion,'')!='' ";
	if($flgvnculo=='n') $searchQuery.= " and IFNULL(e.descripcion,'')='' ";
	
	if($fechai!='') $searchQuery.= " and to_days(c.inicio_evento)>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(c.fin_evento) <= to_days('$fechaf')";
		
	
	$row=0;
	$rowperpage=999999;
	$data_OF=$reportecal->select_reporte_vinculopc($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	include("../vista/reportecal/excel_rep_vinculopc.php");	

	//**********************************
	// reporte reprogramacion
	//**********************************

}else if(!empty($_POST['accion']) and $_POST['accion']=='comparafactura'){
	$anio=date("Y");
    include("../vista/reportecal/index_comparafactura.php");	    	

}else if(!empty($_POST['accion']) and $_POST['accion']=='comparafact_detalle'){
	$anio=$_POST['anio'];
	
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	
	
	// agregar cuadro
	if($sess_codpais=='esp')
		$title="COMPARATIVO MENSUAL USD-COMERCIAL CUP (VN+RENOV+OTROS+ANALISIS +TCS +AMPLIACIÓN+AUDITORIA ADICIONAL+NO ANUNCIADA+INVESTIGACION+CURSOS ACADEMY+CARTA COR)
)";
	else
		$title=$lang_reporte_comparativo_comercial;
	
	$ids="1";
	$idPro=1;
	
	$codejecutivo = "";
	$codestado = "";
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	$tipo='1,7';
	$proyecto="";
	$anio3="";
	 
	$data_res=$reporteproy->selec_res_xaniomes($sess_codpais,$proyecto,$tcEuUS,$tipo,$codestado,$anio3,$codejecutivo);
	
	foreach($data_res as $rowP){
		// se resta notacredito,a solicitud de stef.230422
		$arrayCosto1[$rowP['anio']][$rowP['mes']]=$rowP['costo'] - $rowP['notacredito'];
		$arrayCantidad1[$rowP['anio']][$rowP['mes']]=$rowP['numero'];
	}
	
	// agregar TC al cuadro 
	$data_res=$reporteproy->selec_res_TC_analis($sess_codpais,$proyecto,$anio3,$tcEuUS);
	
	foreach($data_res as $rowP) {
		$arrayCosto1[$rowP['anio']][$rowP['mes']]+=$rowP['costo'];
		$arrayCantidad1[$rowP['anio']][$rowP['mes']]+=$rowP['numero'];
	}
	
	// agregar analisis laboratorio
	$data_res=$reporteproy->selec_res_labresultado($sess_codpais,$proyecto,$anio3);
	
	foreach($data_res as $rowP) {
		$arrayCosto1[$rowP['anio']][$rowP['mes']]+=$rowP['costo'];
		$arrayCantidad1[$rowP['anio']][$rowP['mes']]+=$rowP['numero'];
	}

	// agregar etiqueta
	$data_res=$reporteproy->selec_res_etiqueta_logo($sess_codpais,$anio);
	if(!empty($data_res)){
		foreach($data_res as $rowP) {
			$arrayCosto1[$rowP['anio']][$rowP['mes']]+=$rowP['costo'];
			$arrayCantidad1[$rowP['anio']][$rowP['mes']]+=$rowP['numero'];
		}
	}


	$resultadoCMComercialCUP = $arrayCosto1;
	
	$data_OF=$reportecal->select_proyeccion_mes($sess_codpais,$idPro);
	if(!empty($data_OF)){
		foreach($data_OF as $row){
			$arrayProy[$row['anio']][$row['mes']]=$row['monto'];
		}
	}
	
	include("../vista/reportecal/detalle_comparafactura_venta.php");
	
	
	// inicio presentacion del cuadro title
	//***********************************
	//$title="COMPARATIVO MENSUAL USD-SERVICIOS INTEROFFICE ";
	$title=$lang_reporte_comparativo_servicios;
	$ids="5";
	$idPro=5;
	
	unset($sum11);
	unset($sum12);
	unset($Sum2);
	unset($Sum1);	
	unset($Sum3);
	unset($Sum4);	
	unset($arrayProy);
	unset($arrayProyTot);
	
	$data_OF=$reportecal->select_proyeccion_mes($sess_codpais,$idPro);
	if(!empty($data_OF)){
		foreach($data_OF as $row){
			$arrayProy[$row['anio']][$row['mes']]=$row['monto'];
		}
	}
	
	$dataRow=$reportecal->select_proyeccion_mes_venta($anio,$sess_codpais,$ids,$tcEuUS);

	$resultadoCMServInter = $dataRow;
	    
	include("../vista/reportecal/detalle_comparafactura.php");

	// fin presentacion del cuadro
	//***********************************
	
	
	// inicio presentacion del cuadro
	//***********************************
	//$title="COMPARATIVO MENSUAL USD-SOLICITUDES INTERNAS ";
	$title=$lang_reporte_comparativo_solicitudes;
	$ids="3";
	$idPro=3;
	$montoadd=1;
	
	unset($sum11);
	unset($sum12);
	unset($Sum2);
	unset($Sum1);	
	unset($Sum3);
	unset($Sum4);
	
	unset($arrayProy);
	unset($arrayProyTot);
	$sum11=0;
	$sum12=0;
	
	$data_OF=$reportecal->select_proyeccion_mes($sess_codpais,$idPro);
	if(!empty($data_OF)){
		foreach($data_OF as $row){
			$arrayProy[$row['anio']][$row['mes']]=$row['monto'];
		}
	}
	$dataRow=$reportecal->select_proyeccion_mes_venta($anio,$sess_codpais,$ids,$tcEuUS);

	$resultadoCMSolicitudInterna = $dataRow;
	    
	include("../vista/reportecal/detalle_comparafactura.php");
	
	
	// fin presentacion del cuadro
	//***********************************
	
	// inicio presentacion del cuadro
	//***********************************
	//$title="COMPARATIVO MENSUAL USD PERU ";
	$title=$lang_reporte_comparativo_peru;
	if($sess_codpais=='esp')
		$title.=" PERU ";
	$ids="1,3,5"; // 9 es artificio para todos
	$todos="1";
	$idPro="1,3,5,7";
	
	unset($sum11);
	unset($sum12);
	unset($Sum2);
	unset($Sum1);	
	unset($Sum3);
	unset($Sum4);
	
	unset($arrayProy);
	unset($arrayProyTot);
	
	$data_OF=$reportecal->select_proyeccion_mes($sess_codpais,$idPro);
	if(!empty($data_OF)){
		foreach($data_OF as $row){
			$arrayProy[$row['anio']][$row['mes']]=$row['monto'];
		}
	}
	
	unset($dataRow);
	
	for($i=1;$i<=12;$i++){
		$dataRow[$i]['montoanio_ante'] = $resultadoCMComercialCUP[$anio-1][$i] + $resultadoCMServInter[$i-1]['montoanio_ante'] + $resultadoCMSolicitudInterna[$i-1]['montoanio_ante'];
		
		$dataRow[$i]['montoanio']=0;
		if(!empty($resultadoCMComercialCUP[$anio][$i]))
			$dataRow[$i]['montoanio']+= $resultadoCMComercialCUP[$anio][$i];

		if(!empty($resultadoCMServInter[$i-1]['montoanio']))
			$dataRow[$i]['montoanio']+= $resultadoCMServInter[$i-1]['montoanio'];
	
		if(!empty($resultadoCMSolicitudInterna[$i-1]['montoanio']))
			$dataRow[$i]['montoanio']+= $resultadoCMSolicitudInterna[$i-1]['montorestecnico'];
		
		$dataRow[$i]['montorestecnico_ante'] = $resultadoCMSolicitudInterna[$i-1]['montorestecnico_ante'];
		$dataRow[$i]['montorestecnico'] =  $resultadoCMSolicitudInterna[$i-1]['montorestecnico'];
		
		$dataRow[$i]['mes'] = $i;
	}

	include("../vista/reportecal/detalle_comparafactura.php");
	
	// fin presentacion del cuadro
	//***********************************
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='ingProyeccionFac'){	
	$anio=$_POST['anio'];
	$idPro=$_POST['idPro'];
	$dataRow=$reportecal->select_proyeccion_mes_tipo($sess_codpais,$idPro,$anio);
    include("../vista/reportecal/frm_comparafactura.php");	    	

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_proyeccionfactu'){	
	$anio=$_POST['anio'];
	$idPro=$_POST['idPro'];
	
	$reportecal->delete_proyeccionFac($idPro,$anio,$sess_codpais);
	$dataRow=$reportecal->select_mes();
	foreach($dataRow as $row){
		$id_mes=$row['id_mes'];
		if(!empty($_POST['monto_'.$id_mes])){
			$monto=$_POST['monto_'.$id_mes];
			$reportecal->insert_proyeccionFac($idPro,$anio,$sess_codpais,$id_mes,$monto);
		}
	}
	echo "Se registro la informacion";

// 18122021. reporte resumen de viaticos
}else if(!empty($_POST['accion']) and $_POST['accion']=='reporte_viaticos'){
	if(empty($_POST['anio']))
		$anio=date("Y");
	else
		$anio=$_POST['anio'];
	
    include("../vista/reportecal/reporte_viaticos.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='resumen_viaticos'){
	$anio=$_POST['anio'];
	
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	$dataRow=$reportecal->select_resumen_viaticos($sess_codpais,$anio,$G_tc);
	foreach($dataRow as $rowP) {
		$arrayCosto[$rowP['id_auditor']][$rowP['mes_inicio']]=$rowP['entdolares'];
	}
	
	$dataAud=$reportecal->select_resumen_viaticos_auditor($sess_codpais,$anio,$G_tc);
	$dataMonto=$reportecal->select_resumen_viaticos_auditor_monto($sess_codpais,$anio,$G_tc);
	
	include("../vista/reportecal/resumen_viaticos.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='xlsResumenViaticos'){
	$anio=$_POST['anio'];
	$mes=$_POST['mes'];
	$id_auditor=$_POST['id_auditor'];
	
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$G_tc=$dataPais['tc'];
	$dataAud=$reportecal->select_resumen_viaticos_excel($sess_codpais,$anio,$id_auditor,$mes,$G_tc);
	include("../vista/reportecal/xls_resumen_viaticos.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='repProgrammedServices'){
//***********************************************************
// funcion index actividades de auditor por fecha
//***********************************************************


$estadoactividad_res=$estadoactividad->select_estadoactividad_select($sess_codpais);
$auditor_proycomercial_res=$prgauditor->select_auditorForProyCome($sess_codpais);
$estado_proyecto_res=$estadoproyecto->select_estadoproyectoByPais($sess_codpais);

include("../vista/reportecal/repProgrammedServices.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='repProgramedServicesData'){
//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	
	$proyecto = $_POST['proyecto'];
	$id_estadoactividad = $_POST['id_estadoactividad'];
	$id_comercial_executive = $_POST['id_comercial_executive'];
	$id_estado_proyecto = $_POST['id_estado_proyecto'];
	
	$invoice_date_i = $_POST['invoice_date_i'];
	$invoice_date_f = $_POST['invoice_date_f'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" nombre_proyecto ";
	$columnSortOrder=" asc ";

	## Search  oculto
	$searchQuery =" and prg_calendario.id_pais='$sess_codpais' " ;
	
	## Total number of records without filtering
	$data_maxOF=$reportecal->selec_total_reporte_programmed_services($searchQuery);
	$totalRecords = $data_maxOF['total'];

		
	if($id_estadoactividad!='') 
		$searchQuery.=" and prg_calendario.id_estadoactividad='$id_estadoactividad' ";
		
	if($id_comercial_executive!='') 
		$searchQuery.=" and prg_calendario.id_comercial_executive='$id_comercial_executive' ";
	
	if($id_estado_proyecto!='') 
		$searchQuery.=" and prg_calendario.id_estado_proyecto='$id_estado_proyecto' ";

	if($proyecto!='') 
		$searchQuery.= " and (prg_calendario.id_proyecto like '%".$proyecto."%'  or prg_proyecto.proyect like '%".$proyecto."%')";

	if($invoice_date_i!='') 
		$searchQuery.=" and date_format(str_to_date(invoice_date,'%d/%m/%Y'),'%Y-%m-%d') >= (date_format(str_to_date('$invoice_date_i','%d/%m/%Y'),'%Y-%m-%d'))";
    if($invoice_date_f!='') 
		$searchQuery.=" and date_format(str_to_date(invoice_date,'%d/%m/%Y'),'%Y-%m-%d') <= (date_format(str_to_date('$invoice_date_f','%d/%m/%Y'),'%Y-%m-%d'))";

	## Total number of record with filtering
	$data_maxOF2=$reportecal->selec_total_reporte_programmed_services($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$reportecal->selec_reporte_programmed_services($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	
	//print_r($data_OF);
	$data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
		
		$data[] = array( 
			"cu_proyecto"=>str_replace('"','',json_encode($row['cu_proyecto'],JSON_UNESCAPED_UNICODE)),
			"nombre_proyecto"=>str_replace('"','',json_encode($row['nombre_proyecto'],JSON_UNESCAPED_UNICODE)),
			"invoice_number"=>$row['invoice_number'],
			"invoice_date"=>$row['invoice_date2'],
			"remark_invoice"=>$row['remark_invoice'],
			"invoice_amount"=>number_format($row['invoice_amount'],2),
			"nombre_estadoproyecto"=>str_replace('"','',json_encode($row['nombre_estadoproyecto'],JSON_UNESCAPED_UNICODE)),
			"auditor"=>str_replace('"','',json_encode($row['auditor'],JSON_UNESCAPED_UNICODE)),
			"comercial_executive"=>str_replace('"','',json_encode($row['comercial_executive'],JSON_UNESCAPED_UNICODE)),
			"inicio_evento"=>date('d/m/Y', strtotime($row['inicio_evento'])),
			"fin_evento"=>date('d/m/Y', strtotime($row['fin_evento'])),
			"tipo_actividad"=>str_replace('"','',json_encode($row['tipo_actividad'],JSON_UNESCAPED_UNICODE)),
			"programa"=>str_replace('"','',json_encode($row['programa'],JSON_UNESCAPED_UNICODE)),
			"planned_status"=>str_replace('"','',json_encode($row['planned_status'],JSON_UNESCAPED_UNICODE)),
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='repProgramedServicesDataXLS'){	
//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************

	
	$proyecto = $_POST['proyecto'];
	$id_estadoactividad = $_POST['id_estadoactividad'];
	$id_comercial_executive = $_POST['id_comercial_executive'];
	$id_estado_proyecto = $_POST['id_estado_proyecto'];
	
	$invoice_date_i = $_POST['invoice_date_i'];
	$invoice_date_f = $_POST['invoice_date_f'];
	
	$mes = $_POST['mes'];
	$anio = $_POST['anio'];
	
	
	$searchQuery =" and prg_calendario.id_pais='$sess_codpais' " ;
	
	if($id_estadoactividad!='') 
		$searchQuery.=" and prg_calendario.id_estadoactividad='$id_estadoactividad' ";
		
	if($id_comercial_executive!='') 
		$searchQuery.=" and prg_calendario.id_comercial_executive='$id_comercial_executive' ";
	
	if($id_estado_proyecto!='') 
		$searchQuery.=" and prg_calendario.id_estado_proyecto='$id_estado_proyecto' ";


	if($mes!='') 
		$searchQuery.=" and month(prg_comercial_factura.invoice_date)='$mes' ";
	if($anio!='') 
		$searchQuery.=" and year(prg_comercial_factura.invoice_date)='$anio' ";
	
	if($proyecto!='') 
	$searchQuery.= " and (prg_calendario.id_proyecto like '%".$proyecto."%'  or prg_proyecto.proyect like '%".$proyecto."%')";

	if($invoice_date_i!='') 
		$searchQuery.=" and date_format(str_to_date(prg_comercial_factura.invoice_date,'%d/%m/%Y'),'%Y-%m-%d') >= (date_format(str_to_date('$invoice_date_i','%d/%m/%Y'),'%Y-%m-%d'))";
    if($invoice_date_f!='') 
		$searchQuery.=" and date_format(str_to_date(prg_comercial_factura.invoice_date,'%d/%m/%Y'),'%Y-%m-%d') <= (date_format(str_to_date('$invoice_date_f','%d/%m/%Y'),'%Y-%m-%d'))";

	$columnName=" nombre_proyecto ";
	$columnSortOrder=" asc ";
	$row=0;
	$rowperpage=10000;
	
	$data_OF=$reportecal->selec_reporte_programmed_services($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	include("../vista/reportecal/xlsProgrammedServices.php");	
}else if(!empty($_POST['accion']) and $_POST['accion']=='repDueAudits'){
	//***********************************************************
	// funcion index actividades de auditor por fecha
	//***********************************************************
	
	$auditor_res=$prgauditor->select_auditor_select($sess_codpais);
	$data_programa=$programa->selec_programasbypais($sess_codpais);
	
	include("../vista/reportecal/repDueAudits.php");	
	
	}else if(!empty($_POST['accion']) and $_POST['accion']=='repDueAuditsData'){
	//***********************************************************
		// funcion buscador tabla lista actividades
		//***********************************************************
		
		## Read value
		
		$proyecto = $_POST['proyecto'];
		
		$fecha_mc_i = $_POST['fecha_mc_i'];
		$fecha_mc_f = $_POST['fecha_mc_f'];

		$id_auditor = $_POST['id_auditor'];
		$id_programa = $_POST['id_programa'];
		
		$draw = $_POST['draw'];
		$row = $_POST['start'];
		$rowperpage = $_POST['length']; // Rows display per page
		$columnIndex = $_POST['order'][0]['column']; // Column index
		
		$columnName=" nombre_proyecto ";
		$columnSortOrder=" asc ";
	
		## Search  oculto
		$searchQuery =" and ref_pais='$sess_codpais' " ;
		
		
		## Total number of records without filtering
		$data_maxOF=$reportecal->selec_total_reporte_due_audits($searchQuery);
		$totalRecords = $data_maxOF['total'];

		if($proyecto!='') 
			$searchQuery.= " and (prg_auditoractividad.project_id like '%".$proyecto."%'  or prg_proyecto.proyect like '%".$proyecto."%')";
		if($fecha_mc_i!='') 
			$searchQuery.=" and prg_auditoractividad.fecha_mc >= (date_format(str_to_date('$fecha_mc_i','%d/%m/%Y'),'%Y-%m-%d'))";
		if($fecha_mc_f!='') 
			$searchQuery.=" and prg_auditoractividad.fecha_mc <= (date_format(str_to_date('$fecha_mc_f','%d/%m/%Y'),'%Y-%m-%d'))";
		if($id_auditor!='') 
			$searchQuery.= " and prg_auditoractividad.id_auditor = '$id_auditor'";
		if($id_programa!='') 
			$searchQuery.= " and prg_auditoractividad.id_programa = '$id_programa'";
		
		## Total number of record with filtering
		$data_maxOF2=$reportecal->selec_total_reporte_due_audits($searchQuery);
		$totalRecordwithFilter = $data_maxOF2['total'];
	
		## Fetch records
		$data_OF=$reportecal->selec_reporte_due_audits($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
		
		
		//print_r($data_OF);
		$data = array();
		if(!empty($data_OF)){
			foreach($data_OF as $row) {
			
			$data[] = array( 
				"cu_proyecto"=>str_replace('"','',json_encode($row['cu_proyecto'],JSON_UNESCAPED_UNICODE)),
				"nombre_proyecto"=>str_replace('"','',json_encode($row['nombre_proyecto'],JSON_UNESCAPED_UNICODE)),
				"pais"=>str_replace('"','',json_encode($row['pais'],JSON_UNESCAPED_UNICODE)),
				"email_proyecto"=>str_replace('"','',json_encode($row['email_proyecto'],JSON_UNESCAPED_UNICODE)),
				"telefono_proyecto"=>str_replace('"','',json_encode($row['telefono_proyecto'],JSON_UNESCAPED_UNICODE)),
				"auditor"=>str_replace('"','',json_encode($row['auditor'],JSON_UNESCAPED_UNICODE)),
				"programa"=>str_replace('"','',json_encode($row['programa'],JSON_UNESCAPED_UNICODE)),
				"fecha"=>str_replace('"','',json_encode($row['fecha'],JSON_UNESCAPED_UNICODE)),
				"fecha_mc"=>str_replace('"','',json_encode($row['fecha_mc'],JSON_UNESCAPED_UNICODE)),
				"fecha_mc_90_days"=>str_replace('"','',json_encode($row['fecha_mc_90_days'],JSON_UNESCAPED_UNICODE)),
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
	
	}else if(!empty($_POST['accion']) and $_POST['accion']=='repDueAuditsDataXLS'){	
	//***********************************************************
		// funcion buscador tabla lista actividades
		//***********************************************************
	
		$proyecto = $_POST['proyecto'];
		
		$fecha_mc_i = $_POST['fecha_mc_i'];
		$fecha_mc_f = $_POST['fecha_mc_f'];
		
		$id_auditor = $_POST['id_auditor'];
		$id_programa = $_POST['id_programa'];

		## Search  oculto
		$searchQuery =" and t_mae_pais.codpostal='$sess_codpais' " ;
		
		if($proyecto!='') 
			$searchQuery.= " and (prg_auditoractividad.project_id like '%".$proyecto."%'  or prg_proyecto.proyect like '%".$proyecto."%')";
		if($fecha_mc_i!='') 
			$searchQuery.=" and prg_auditoractividad.fecha_mc >= (date_format(str_to_date('$fecha_mc_i','%d/%m/%Y'),'%Y-%m-%d'))";
		if($fecha_mc_f!='') 
			$searchQuery.=" and prg_auditoractividad.fecha_mc <= (date_format(str_to_date('$fecha_mc_f','%d/%m/%Y'),'%Y-%m-%d'))";
		if($id_auditor!='') 
			$searchQuery.= " and prg_auditoractividad.id_auditor = '$id_auditor'";
		if($id_programa!='') 
			$searchQuery.= " and prg_auditoractividad.id_programa = '$id_programa'";

		$columnName=" nombre_proyecto ";
		$columnSortOrder=" asc ";
		$row=0;
		$rowperpage=10000;
		
		$data_OF=$reportecal->selec_reporte_due_audits($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
		
		include("../vista/reportecal/xlsRepDueAudits.php");	
	
	}else if(!empty($_POST['accion']) and $_POST['accion']=='repprograservices'){	
	//***********************************************************
		// funcion buscador tabla lista actividades
		//***********************************************************
	

		## Search  oculto
		$searchQuery =" and id_pais='$sess_codpais' " ;
		$res_mes=$reportecal->select_meses();
		$data_OF=$reportecal->selec_reporte_facturas($sess_codpais);
		foreach($data_OF as $row){
			$dataCan[$row['aniomes']]=$row['total'];
			$dataMon[$row['aniomes']]=$row['monto'];
		}
		
		include("../vista/reportecal/data_repprograservices.php");		
	}



?>
