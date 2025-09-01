<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_feriado_modelo.php");
include("../modelo/prg_auditor_modelo.php");
$feriado=new prg_feriado_model();
$prg_auditor=new prg_auditor_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='web_index'){
	//**********************************
	// mostrar index de calendario
	//**********************************
    include("../vista/feriado/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_feriado'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" id_feriado";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais'";

	## Total number of records without filtering
	$data_maxOF=$feriado->selec_total_feriado($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and descripcion like '%$descripcion%' ";
	## Total number of record with filtering
	$data_maxOF2=$feriado->selec_total_feriado($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$feriado->select_feriado($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_feriado'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediFeriado'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliFeriado'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			   "id_feriado"=>$id,
			   "fecha"=>$row['fechaf'],
			   "edita"=>$edita,
			   "elimina"=>$elimina,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editFeriado'){
	$id_feriado="";
	if(!empty($_POST['id_feriado'])){
		$id_feriado=$_POST['id_feriado'];
		
		$data_res=$feriado->selec_one_feriado($id_feriado);
		
		$data_gaud=$feriado->selec_data_auditor($id_feriado);
		if($data_gaud['gauditor']!='')
			$arr_aud=explode(",",$data_gaud['gauditor']);
		
	}

	$data_audi=$prg_auditor->select_auditor_select($sess_codpais);

    include("../vista/feriado/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detFeriado'){
    // proceso update a la base de datos usuarios
	
	$descripcion=$_POST['desc_feriado'];
	$fecha=formatdatedos($_POST['fecha']);

	$datai=explode("/",$fecha);
	$dia=$datai['2'];
	$mes=$datai['1'];
	$anio=$datai['0'];
	 
	if(empty($_POST['id_feriado'])){
		$id_feriado=$feriado->insert_feriado($descripcion,$fecha,$sess_codpais,$usuario_name,$ip);
		// crear actividad feriado 100%
		if($sess_codpais=='esp'){
			// prg_auditoractividad
			$feriado->trigert_actividad($id_feriado,$fecha,$descripcion,$sess_codpais);
			
			// prg_calendario
			$id_tipoactividad=378; 
			$id_estadoactividad=1;
			$asunto=$descripcion;
			$project_id="";
			$nro_muestra="";
			$por_dia="";
			$id_auditor=0;
			$monto_dolares=0;
			$monto_soles=0;
			$auditoria="";
			$id_type="";
			$observacion="";
			$hora_inicial="01:00";
			$hora_final="23:30"; 
			$dia_inicio=$dia;
			$mes_inicio=$mes;
			$anio_inicio=$anio;
			$dia_fin=$dia;
			$mes_fin=$mes;
			$anio_fin=$anio;
			$inicio_evento="$fecha 08:00:00";
			$fin_evento="$fecha 17:00:00"; 
			$hora_inicio=480;
			$hora_fin=1020; 
			$is_sabado="0";
			$is_domingo="0";
			$id_asignacion_viaticos="";
			$id_calendario="2147483647";
			$flag_rendicion=1;
			
			$feriado->trigert_calendario($id_feriado,$sess_codpais,$id_tipoactividad, $project_id,$nro_muestra, $por_dia, $id_auditor,
		 $monto_dolares, $monto_soles, $id_estadoactividad, $auditoria, $id_type, $observacion,$hora_inicial, $hora_final, 
		 $dia_inicio, $mes_inicio, $anio_inicio, $dia_fin, $mes_fin, $anio_fin, $inicio_evento,  $fin_evento, 
		 $is_sabado, $is_domingo, $hora_inicio, $hora_fin, $asunto, $id_calendario, $id_asignacion_viaticos, $flag_rendicion,
		 $usuario_name, $ip);
		}	
	}else{
		$id_feriado=$_POST['id_feriado']; // id
		$feriado->update_feriado($id_feriado,$descripcion,$fecha,$sess_codpais,$usuario_name,$ip);
	}	
	
	// escepciones
	$feriado->delete_auditorxferiado($id_feriado);
	if(!empty($_POST['id_auditor'])){
		foreach($_POST['id_auditor'] as $id_auditor){
			$feriado->insert_auditorxferiado($id_feriado,$id_auditor);
		}
	}
	
	 echo "Se actualizo el registro.";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delFeriado'){
    // delete a la base de datos usuarios
	$id_feriado=$_POST['id_feriado']; 
    $feriado->delete_feriado($id_feriado);
	if($sess_codpais=='esp')
		$feriado->delete_actividad($id_feriado);
    echo "Se elimino el registro.";
}


?>
