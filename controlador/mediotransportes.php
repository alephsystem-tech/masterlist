<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_mediotransporte_modelo.php");

$mediotransporte=new prg_mediotransporte_model();

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
    include("../vista/mediotransporte/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_mediotra'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" id_mediotransporte";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais'";

	
	## Total number of records without filtering
	$data_maxOF=$mediotransporte->selec_total_mediotransporte($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and descripcion like '%$descripcion%' ";
		
	## Total number of record with filtering
	$data_maxOF2=$mediotransporte->selec_total_mediotransporte($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$mediotransporte->select_mediotransporte($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_mediotransporte'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediMediotra'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliMediotra'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			   "id_mediotransporte"=>$id,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editMediotra'){
	$id_mediotransporte="";
	if(!empty($_POST['id_mediotransporte']))
		$data_res=$mediotransporte->selec_one_mediotransporte($_POST['id_mediotransporte']);

    include("../vista/mediotransporte/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detMediotra'){
    // proceso update a la base de datos usuarios
	
	$descripcion=$_POST['desc_mediotra'];

	if(empty($_POST['id_mediotransporte']))
		$id_mediotransporte=$mediotransporte->insert_mediotransporte($descripcion,$sess_codpais,$usuario_name,$ip);
	else{
		$id_mediotransporte=$_POST['id_mediotransporte']; // id
		$mediotransporte->update_mediotransporte($id_mediotransporte,$descripcion,$sess_codpais,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delMediotra'){
    // delete a la base de datos usuarios
	$id_mediotransporte=$_POST['id_mediotransporte']; 
    $mediotransporte->delete_mediotransporte($id_mediotransporte);
    echo "Se elimino el registro.";
}


?>
