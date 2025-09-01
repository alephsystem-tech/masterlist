<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_programa_modelo.php");

$programa=new prg_programa_model();

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
    include("../vista/programa/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_programa'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" id_programa";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and p.id_pais='$sess_codpais'";
		
	## Total number of records without filtering
	$data_maxOF=$programa->selec_total_programa($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( descripcion like '%$descripcion%' or  iniciales like '%$descripcion%') ";
	
	## Total number of record with filtering
	$data_maxOF2=$programa->selec_total_programa($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$programa->select_programa($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_programa'];
			$chk="";
			if($row['flgactivo']=='1')
				$chk=" checked";
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediPrograma'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliPrograma'><i class='fas fa-trash'></i> </button>";
			$flgactivo="<div class='custom-control custom-switch  custom-switch-off-danger custom-switch-on-success'>
						  <input type=checkbox class='custom-control-input'onchange='js_changeactive($id)' name='flgstatus_$id' id='flgstatus_$id' $chk >
						  <label class='custom-control-label' for='flgstatus_$id'></label>
						</div>";
		
		   $data[] = array( 
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			   "iniciales"=>str_replace('"','',json_encode($row['iniciales'],JSON_UNESCAPED_UNICODE)),
			   "codigo"=>str_replace('"','',json_encode($row['codigo'],JSON_UNESCAPED_UNICODE)),
			   "id_programa"=>$id,
			   "hora_informe"=>$row['hora_informe'],
			   "general"=>$row['general'],
			    "grupo"=>$row['grupo'],
			   "negocio"=>$row['negocio'],
			   "categoria"=>$row['categoria'],
			   "modulo"=>$row['modulo'],
			   "flgactivo"=>$flgactivo,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editPrograma'){
	$datacat=$programa->selec_cat_programa();
	$datagrupo=$programa->selec_grupoprogramasbypais($sess_codpais);
	$id_programa="";
	if(!empty($_POST['id_programa'])){
		$id_programa=$_POST['id_programa'];
		$data_res=$programa->selec_one_programa($id_programa);
		
		$data_gmodulo=$programa->selec_data_modulo($id_programa);
		if($data_gmodulo['gmodulo']!='')
			$arr_modulo=explode(",",$data_gmodulo['gmodulo']);	
	}	
	
	$datamodulo=$programa->selec_modulosbyselec($sess_codpais);

    include("../vista/programa/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detPrograma'){
    // proceso update a la base de datos usuarios
	$id_categoria=$_POST['id_categoria'];
	$descripcion=$_POST['desc_programa'];
	$hora_informe=$_POST['hora_informe'];
	$general=$_POST['general'];
	$negocio=$_POST['negocio'];
	$codigo=$_POST['codigo'];
	$iniciales=$_POST['iniciales'];
	$id_grupoprograma=$_POST['id_grupoprograma'];
	

	if(empty($_POST['id_programa']))
		$id_programa=$programa->insert_programa($descripcion,$iniciales,$hora_informe,$codigo,$id_categoria,$sess_codpais,$usuario_name,$ip,$general,$negocio,$id_grupoprograma);
	else{
		$id_programa=$_POST['id_programa']; // id
		$programa->update_programa($id_programa,$descripcion,$iniciales,$hora_informe,$codigo,$id_categoria,$sess_codpais,$usuario_name,$ip,$general,$negocio,$id_grupoprograma);
	}	
	
	$res=$programa->delete_programaxmodulo($id_programa);
	foreach($_POST['id_modulo'] as $id_modulo){
		$res=$programa->insert_programaxmodulo($id_programa,$id_modulo);		
	}
	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delPrograma'){
    // delete a la base de datos usuarios
	$id_programa=$_POST['id_programa']; 
    $programa->delete_programa($id_programa);
    echo "Se elimino el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='activoPrograma'){
    // delete a la base de datos usuarios
	$id_programa=$_POST['id_programa']; 
	$flgactivo=$_POST['flgactivo']; 
    $programa->activa_programa($id_programa,$flgactivo);
    echo "Se actualizo el registro.";

}


?>
