<?php

include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_condicionpago_modelo.php");

$condicionpago=new prg_condicionpago_model();

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
    include("../vista/condicionpago/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_condicionpago'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" id_condicion";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " ";

	if(!empty($descripcion))
		$searchQuery.=" and descripcion like '%$descripcion%' ";
		
	## Total number of records without filtering
	$data_maxOF=$condicionpago->selec_total_condicionpago();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$condicionpago->selec_total_condicionpago($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$condicionpago->select_condicionpago($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_condicion'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediCondicion'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliCondicion'><i class='fas fa-trash'></i> </button>";
			$pais="<button type='button' id='estproy_". $id ."'  class='btn  btn_paisCondicion'><i class='fas fa-edit'></i> </button>";
		
		   $data[] = array( 
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			   "id_condicion"=>$id,
			   "dia"=>$row['dia'],
			   "edita"=>$edita,
			   "pais"=>$pais,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editCondicion'){
	$id_condicion="";
	if(!empty($_POST['id_condicion']))
		$data_res=$condicionpago->selec_one_condicionpago($_POST['id_condicion']);

    include("../vista/condicionpago/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detCondicion'){
    // proceso update a la base de datos usuarios
	
	$descripcion=$_POST['desc_condicion'];
	$dia=$_POST['dia'];

	if(empty($_POST['id_condicion']))
		$id_condicion=$condicionpago->insert_condicionpago($descripcion,$dia,$usuario_name,$ip);
	else{
		$id_condicion=$_POST['id_condicion']; // id
		$condicionpago->update_condicionpago($id_condicion,$descripcion,$dia,$usuario_name,$ip);
	}	
	 echo $id_condicion;

}else if(!empty($_POST['accion']) and $_POST['accion']=='paisCondicion'){

	$id_condicion=$_POST['id_condicion'];
	$data_res=$condicionpago->selec_one_condicionpago($_POST['id_condicion']);
	$data_pai=$condicionpago->select_condicionpago_pais($id_condicion);

    include("../vista/condicionpago/frm_pais.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_paisCondicion'){
    // delete a la base de datos usuarios
	$id_condicion=$_POST['id_condicion']; 
	
	$condicionpago->delete_condicionpago_pais($id_condicion);
	foreach($_POST['chkpais'] as $id_pais){
		$condicionpago->insert_condicionpago_pais($id_condicion,$id_pais);
	}	
    echo "Se actualizo el registro.";

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delCondicion'){
    // delete a la base de datos usuarios
	$id_condicion=$_POST['id_condicion']; 
    $condicionpago->delete_condicionpago($id_condicion);
    echo "Se elimino el registro.";
}


?>
