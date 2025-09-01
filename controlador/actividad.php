<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_actividad_modelo.php");
$actividad=new prg_actividad_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	//**********************************
	// mostrar index de calendario
	//**********************************
    include("../vista/actividad/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_actividad'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" prg_actividad.actividad ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and prg_actividad.id_pais='$sess_codpais' ";

	if(!empty($descripcion))
		$searchQuery.=" and prg_actividad.actividad like '%$descripcion%' ";
		
	## Total number of records without filtering
	$data_maxOF=$actividad->selec_total_actividad();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$actividad->selec_total_actividad($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$actividad->select_actividad($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
			$id=$row['id_actividad'];
			$chk="";
			if($row['flgactivo']=='1')
				$chk=" checked";
			
			$edita="<button type='button' id='activi_". $id ."'  class='btn  btn_ediactivi'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='activi_". $id ."'  class='btn  btn_eliactivi'><i class='fas fa-trash'></i> </button>";
			
			$edirelacion="<button type='button' id='aci_". $id ."'  class='btn  btn_relactivi'><i class='fas fa-compress-alt'></i> </button>";
			$edirol="<button type='button' id='aci_". $id ."'  class='btn  btn_rolactivi'><i class='fas fa-address-card'></i> </button>";
			
			$flgactivo="<div class='custom-control custom-switch  custom-switch-off-danger custom-switch-on-success'>
						  <input type=checkbox class='custom-control-input'onchange='js_changeactive($id)' name='flgstatus_$id' id='flgstatus_$id' $chk >
						  <label class='custom-control-label' for='flgstatus_$id'></label>
						</div>";
			
		   $data[] = array( 
			   "actividad"=>str_replace('"','',json_encode($row['actividad'],JSON_UNESCAPED_UNICODE)),
			   "relacion"=>str_replace('"','',json_encode($row['relacion'],JSON_UNESCAPED_UNICODE)),
			   "roles"=>str_replace('"','',json_encode($row['roles'],JSON_UNESCAPED_UNICODE)),
			   "dscanalisis"=>$row['dscanalisis'],
			   "dscproyecto"=>$row['dscproyecto'],
			   "dscedita"=>$row['dscedita'],
			   "dscrendir"=>$row['dscrendir'],
			   "edirelacion"=>$edirelacion,
			   "edirol"=>$edirol,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editEstadProyecto'){
	$id_actividad="";
	if(!empty($_POST['id_actividad']))
		$data_res=$actividad->selec_one_actividad($_POST['id_actividad']);

    include("../vista/actividad/frm_detalle.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='relactividad'){
	
	$id_actividad=$_POST['id_actividad'];
	$data_res=$actividad->selec_one_actividad($id_actividad);
	$data_relacion=$actividad->selec_relacion_actividad($id_actividad,$sess_codpais);

    include("../vista/actividad/frm_relacion.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='rolactividad'){
	$id_actividad=$_POST['id_actividad'];
	$data_res=$actividad->selec_one_actividad($id_actividad);
	$data_rol=$actividad->selec_rol_actividad($id_actividad,$sess_codpais);

    include("../vista/actividad/frm_rol.php");	

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detActividad'){
    // proceso update a la base de datos usuarios
	
	$desc_actividad=$_POST['desc_actividad'];
	$flganalisis=$_POST['flganalisis'];
	$flgproyecto=$_POST['flgproyecto'];
	$flgeditacalendar=$_POST['flgeditacalendar'];
	$flgrendir=$_POST['flgrendir'];

	if(empty($_POST['id_actividad']))
		$id_actividad=$actividad->insert_actividad($desc_actividad,$flganalisis,$flgproyecto,$flgeditacalendar,$flgrendir,$sess_codpais,$usuario_name,$ip);
	else{
		$id_actividad=$_POST['id_actividad']; // id
		$actividad->update_actividad($id_actividad,$desc_actividad,$flganalisis,$flgproyecto,$flgeditacalendar,$flgrendir,$sess_codpais,$usuario_name,$ip);
	}	
	 echo $id_actividad;

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detRelacion'){
    // proceso update a la base de datos usuarios
	
	$id_actividad=$_POST['id_actividad'];
	$actividad->delete_actividadRelacion($id_actividad,$sess_codpais,$usuario_name,$ip);
	foreach($_POST['rolxactividad'] as $id_tipoactividad){
		$actividad->insert_actividadRelacion($id_actividad,$id_tipoactividad,$sess_codpais,$usuario_name,$ip);
	}

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detRol'){
    // proceso update a la base de datos usuarios
	
	$id_actividad=$_POST['id_actividad'];
	$actividad->delete_actividadRol($id_actividad,$sess_codpais,$usuario_name,$ip);
	foreach($_POST['rolxactividad'] as $id_rol){
		$actividad->insert_actividadRol($id_actividad,$id_rol,$sess_codpais,$usuario_name,$ip);
	}
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delActividad'){
    // delete a la base de datos usuarios
	$id_actividad=$_POST['id_actividad']; 
    $actividad->delete_actividad($id_actividad);
    echo "Se elimino el registro.";


}else if(!empty($_POST['accion']) and $_POST['accion']=='expActividad'){
	$descripcion=$_POST['descripcion'];
	
	$columnName=" prg_actividad.actividad ";
	$columnSortOrder=" asc ";
	## Search  oculto
	$searchQuery = " and prg_actividad.id_pais='$sess_codpais' ";

	if(!empty($descripcion))
		$searchQuery.=" and prg_actividad.actividad like '%$descripcion%' ";
	
	$row	=0;
	$rowperpage=1000000;
	## Fetch records
	$data_OF=$actividad->select_actividad($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais);
	
	include("../vista/actividad/data_exporta.php");
	
	}else if(!empty($_POST['accion']) and $_POST['accion']=='activoActividad'){
    // delete a la base de datos usuarios
	$id_actividad=$_POST['id_actividad']; 
	$flgactivo=$_POST['flgactivo']; 
    $actividad->activa_actividad($id_actividad,$flgactivo);
    echo "Se actualizo el registro.";	
	
}


?>
