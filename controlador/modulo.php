<?php

include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_modulo_modelo.php");

$prgmodulo=new prg_modulo_model();

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
    include("../vista/modulo/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_modulo'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" modulo";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais'";

	if(!empty($descripcion))
		$searchQuery.=" and modulo like '%$descripcion%' ";
		
	## Total number of records without filtering
	$data_maxOF=$prgmodulo->selec_total_modulo();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$prgmodulo->selec_total_modulo($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgmodulo->select_modulo($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_modulo'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediModulo'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliModulo'><i class='fas fa-trash'></i> </button>";
			
		   $data[] = array( 
			   "modulo"=>str_replace('"','',json_encode($row['modulo'],JSON_UNESCAPED_UNICODE)),
			   "id_modulo"=>$id,
			   "iniciales"=>$row['iniciales'],
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editModulo'){
	$id_modulo="";
	if(!empty($_POST['id_modulo']))
		$data_res=$prgmodulo->selec_one_modulo($_POST['id_modulo']);

    include("../vista/modulo/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detModulo'){
    // proceso update a la base de datos usuarios
	
	$modulo=$_POST['desc_modulo'];
	$iniciales=$_POST['iniciales'];

	if(empty($_POST['id_modulo']))
		$id_modulo=$prgmodulo->insert_modulo($modulo,$iniciales,$sess_codpais,$usuario_name,$ip);
	else{
		$id_modulo=$_POST['id_modulo']; // id
		$prgmodulo->update_modulo($id_modulo,$modulo,$iniciales,$sess_codpais,$usuario_name,$ip);
	}	
	 echo "Se actualizo la informacion.";

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delModulo'){
    // delete a la base de datos usuarios
	$id_modulo=$_POST['id_modulo']; 
    $prgmodulo->delete_modulo($id_modulo);
    echo "Se elimino el registro.";
}


?>
