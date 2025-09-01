<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_proyecto_tc_modelo.php");
include("../modelo/prg_proyecto_modelo.php");
include("../modelo/prg_auditor_modelo.php");
include("../modelo/prg_pais_modelo.php");
include("../modelo/prg_rol_modelo.php");
include("../modelo/prg_enlace_modelo.php");
include("../modelo/prg_proyecto_programa_modelo.php");

$prgtc=new prg_proyecto_tc_model();
$pais=new prg_pais_model();
$auditor=new prg_auditor_model();
$proyecto=new prg_proyecto_model();
$roles=new prg_rol_model();
$prgenlace=new prg_enlace_model();
$proyectoprograma=new prg_proyecto_programa_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];


	// *************** inicio de ver rol *****************************
	// modificacion sobre el nivel del rol
	$data_enlace=$prgenlace->selec_one_enlace_bycontrol($sess_codpais,'proycomercial','index');
	if(!empty($data_enlace)){
		$id_enlace=$data_enlace['id_enlace'];
		$data_nivel=$roles->selec_enlacenivelbyPais($sess_codpais,$sess_codrol,$id_enlace);
		if(!empty($data_nivel)){
			foreach($data_nivel as $row){
				$isnivel=1;
				$isread=$row['isread'];
				$isupdate=$row['isupdate'];
				$isdelete=$row['isdelete'];
			}
		}
	}
	// *************** fin de ver rol *****************************
	//*************************************************************

//***********************************************************
// proyecto comercial index
if(!empty($_POST['accion']) and $_POST['accion']=='index_cuadro'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	$dataAuditor=$prgtc->select_responsabletc($sess_codpais);
    include("../vista/tc/index_cuadro.php");	

// proyecto comercial index
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_cuadro_detalle'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$id_auditortc = $_POST['id_auditortc'];


	$inconsistencia="0";
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" proyect";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and prg_proyecto.id_pais='$sess_codpais' ";

	## Total number of records without filtering
	$data_maxOF=$prgtc->selec_total_proyectotc($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and (proyect like '%$descripcion%' or prg_proyecto.project_id like '%$descripcion%'  )";
	
	if(!empty($id_auditortc))
		$searchQuery.=" and prg_proyecto.id_auditortc=$id_auditortc ";
	
	## Total number of record with filtering
	$data_maxOF2=$prgtc->selec_total_proyectotc($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgtc->select_proyectotc($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
			$id=$row['id_proyecto'];
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediProyTC'><i class='fas fa-edit'></i> </button>";
			$data[] = array( 
			    "proyect"=>str_replace('"','',json_encode($row['proyect'],JSON_UNESCAPED_UNICODE)),
			    "project_id"=>str_replace('"','',json_encode($row['project_id'],JSON_UNESCAPED_UNICODE)),
				"modules"=>str_replace('"','',json_encode($row['modules'],JSON_UNESCAPED_UNICODE)),
				"programas"=>str_replace('"','',json_encode($row['programas'],JSON_UNESCAPED_UNICODE)),
				"city"=>str_replace('"','',json_encode($row['city'],JSON_UNESCAPED_UNICODE)),
				"state"=>str_replace('"','',json_encode($row['state'],JSON_UNESCAPED_UNICODE)),
				"country"=>str_replace('"','',json_encode($row['country'],JSON_UNESCAPED_UNICODE)),
				"estado"=>str_replace('"','',json_encode($row['estado'],JSON_UNESCAPED_UNICODE)),
				"responsable"=>str_replace('"','',json_encode($row['responsable'],JSON_UNESCAPED_UNICODE)),
			    "id_proyecto"=>$id,
			    "cantidad"=>$row['cantidad'],
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='editProyectoTC'){
	
	$id_proyecto=$_POST['id_proyecto'];

	// dats geenrales del proyecto
	$data_oneProy=$proyectoprograma->selec_one_proyectoprograma($id_proyecto,$sess_codpais);
	$project_id=$data_oneProy['project_id'];
	
	// botones de detalle proyecto
	//$data_DetProyAnio=$proyectoprograma->selec_anios_detalle_proyectoprograma($id_proyecto,$sess_codpais);
	$data_DetProy=$proyectoprograma->selec_detalle_proyectoprograma($id_proyecto,$sess_codpais);
	
	
	include("../vista/tc/frm_detalle.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='addEddTcproyecto'){
	
	$id_proyecto=$_POST['id_proyecto'];	
	
	$datapais=$pais->selec_paises();
	$data_tipoproyecto=$proyecto->selec_categoria($sess_codpais);
	include("../vista/tc/vista_addtc.php");	
	
}


?>
