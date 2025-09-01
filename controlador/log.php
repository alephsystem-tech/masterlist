<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/log_modelo.php");

$tblog=new log_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	//***********************************************************
	// funcion index actividades de auditor por fecha
	//***********************************************************
	$dataModulo=$tblog->selec_modulos($sess_codpais);
	
	include("../vista/log/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_result'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$modulo = $_POST['modulo'];
	
	$fechai = $_POST['fechai'];
	$fechaf = $_POST['fechaf'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" fecha_ingreso ";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais' ";

	if(!empty($modulo))
		$searchQuery.=" and modulo = '$modulo' ";
	
	if($fechai!='') 
		$searchQuery.=" and to_days(fecha_ingreso)>= to_days('".formatdatedos($fechai)."') ";
    if($fechaf!='') 
		$searchQuery.=" and to_days(fecha_ingreso)<= to_days('".formatdatedos($fechaf)."') ";

	
	## Total number of records without filtering
	$data_maxOF=$tblog->selec_total_log($searchQuery);
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$tblog->selec_total_log($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$tblog->select_log($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
			$id=$row['codlog'];
			$data[] = array( 
			   "codlog"=>$id,
			   "campo"=>$row['campo'],
			   "fecha"=>$row['fecha'],
			   "fecha_ingreso"=>$row['fecha_ingreso'],
			   "modulo"=>$row['modulo'],
			   "usuario_ingreso"=>$row['usuario_ingreso'],
				"ip_ingreso"=>$row['ip_ingreso'],
			   "final"=>str_replace('"','',json_encode($row['final'],JSON_UNESCAPED_UNICODE)),
				"inicial"=>str_replace('"','',json_encode($row['inicial'],JSON_UNESCAPED_UNICODE))
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

}


?>
