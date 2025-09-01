<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_cat_programa_modelo.php");

$catprograma=new prg_cat_programa_model();

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
    include("../vista/cat_programa/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_catprograma'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" id_categoria";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " ";

	## Total number of records without filtering
	$data_maxOF=$catprograma->selec_total_categoria($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and categoria like '%$descripcion%' ";
	## Total number of record with filtering
	$data_maxOF2=$catprograma->selec_total_categoria($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$catprograma->select_categoria($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_categoria'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediCategoria'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliCategoria'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "categoria"=>str_replace('"','',json_encode($row['categoria'],JSON_UNESCAPED_UNICODE)),
			   "id_categoria"=>$id,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editCategoria'){
	$id_categoria="";
	if(!empty($_POST['id_categoria']))
		$data_res=$catprograma->selec_one_categoria($_POST['id_categoria']);

    include("../vista/cat_programa/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detCategoria'){
    // proceso update a la base de datos usuarios
	
	$categoria=$_POST['categoria'];

	if(empty($_POST['id_categoria']))
		$id_categoria=$catprograma->insert_categoria($categoria,$usuario_name,$ip);
	else{
		$id_categoria=$_POST['id_categoria']; // id
		$catprograma->update_categoria($id_categoria,$categoria,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delCategoria'){
    // delete a la base de datos usuarios
	$id_categoria=$_POST['id_categoria']; 
    $catprograma->delete_categoria($id_categoria);
    echo "Se elimino el registro.";
}


?>
