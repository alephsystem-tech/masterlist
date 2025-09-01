<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_tipoactividad_modelo.php");

$tipoactividad=new prg_tipoactividad_model();

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
    include("../vista/tipoactividad/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_tipoactividad'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" id_tipoactividad";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais'";

		
	## Total number of records without filtering
	$data_maxOF=$tipoactividad->selec_total_tipoactividad($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( descripcion like '%$descripcion%' or  detalle like '%$descripcion%') ";
	
	
	## Total number of record with filtering
	$data_maxOF2=$tipoactividad->selec_total_tipoactividad($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$tipoactividad->select_tipoactividad($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_tipoactividad'];
			$chk=" ";
			if($row['flgactivo']=='1')
				$chk=" checked";
			$flgactivo="<div class='custom-control custom-switch  custom-switch-off-danger custom-switch-on-success'>
						  <input type=checkbox class='custom-control-input'onchange='js_changeactive($id)' name='flgstatus_$id' id='flgstatus_$id' $chk >
						  <label class='custom-control-label' for='flgstatus_$id'></label>
						</div>";
			
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediTipoactividad'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliTipoactividad'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			   "detalle"=>str_replace('"','',json_encode($row['detalle'],JSON_UNESCAPED_UNICODE)),
			   "id_tipoactividad"=>$id,
			   "enviar_email"=>$row['enviar_email'],
			   "obligacal"=>$row['obligacal'],
			   "edita"=>$edita,
			   "flgactivo"=>$flgactivo,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editTipoactividad'){
	$datacat=$tipoactividad->selec_cat_tipoactividad();
	
	$id_tipoactividad="";
	if(!empty($_POST['id_tipoactividad']))
		$data_res=$tipoactividad->selec_one_tipoactividad($_POST['id_tipoactividad']);

    include("../vista/tipoactividad/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detTipoactividad'){
    // proceso update a la base de datos usuarios
	
	$descripcion=$_POST['desc_tipoactividad'];
	$detalle=$_POST['detalle'];
	$id_categoria=$_POST['id_categoria'];
	$is_enviar_email="0";
	if(!empty($_POST['is_enviar_email']))
		$is_enviar_email="1";

	$flgobligacal="0";
	if(!empty($_POST['flgobligacal']))
		$flgobligacal="1";
	
	
	if(empty($_POST['id_tipoactividad']))
		$id_tipoactividad=$tipoactividad->insert_tipoactividad($descripcion,$detalle,$is_enviar_email,$id_categoria,$flgobligacal,$sess_codpais,$usuario_name,$ip);
	else{
		$id_tipoactividad=$_POST['id_tipoactividad']; // id
		$tipoactividad->update_tipoactividad($id_tipoactividad,$descripcion,$detalle,$is_enviar_email,$id_categoria,$flgobligacal,$sess_codpais,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delTipoactividad'){
    // delete a la base de datos usuarios
	$id_tipoactividad=$_POST['id_tipoactividad']; 
    $tipoactividad->delete_tipoactividad($id_tipoactividad);
    echo "Se elimino el registro.";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='activoActividad'){
    // delete a la base de datos usuarios
	$id_tipoactividad=$_POST['id_tipoactividad']; 
	$flgactivo=$_POST['flgactivo']; 
    $tipoactividad->activa_actividad($id_tipoactividad,$flgactivo);
    echo "Se actualizo el registro.";	
}


?>
