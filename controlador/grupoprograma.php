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
    include("../vista/grupoprograma/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_grupoprograma'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" id_grupoprograma";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais'";
		
	## Total number of records without filtering
	$data_maxOF=$programa->selec_total_grupoprograma($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( abreviatura like '%$descripcion%' or  grupo like '%$descripcion%') ";
	
	## Total number of record with filtering
	$data_maxOF2=$programa->selec_total_grupoprograma($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$programa->select_grupoprograma($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_grupoprograma'];
			$chk="";
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_edigrupoPrograma'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eligrupoPrograma'><i class='fas fa-trash'></i> </button>";
			
		   $data[] = array( 
			   "grupo"=>str_replace('"','',json_encode($row['grupo'],JSON_UNESCAPED_UNICODE)),
			   "abreviatura"=>str_replace('"','',json_encode($row['abreviatura'],JSON_UNESCAPED_UNICODE)),  
			   "id_grupoprograma"=>$id,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editgrupoPrograma'){
	$id_grupoprograma="";
	if(!empty($_POST['id_grupoprograma']))
		$data_res=$programa->selec_one_grupoprograma($_POST['id_grupoprograma']);

    include("../vista/grupoprograma/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detgrupoPrograma'){
    // proceso update a la base de datos usuarios
	$grupo=$_POST['desc_programa'];
	$iniciales=$_POST['iniciales'];
	
	if(empty($_POST['id_grupoprograma']))
		$id_programa=$programa->insert_grupoprograma($grupo,$iniciales,$sess_codpais,$usuario_name,$ip);
	else{
		$id_grupoprograma=$_POST['id_grupoprograma']; // id
		$programa->update_grupoprograma($id_grupoprograma,$grupo,$iniciales,$sess_codpais,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delgrupoPrograma'){
    // delete a la base de datos usuarios
	$id_grupoprograma=$_POST['id_grupoprograma']; 
    $programa->delete_grupoprograma($id_grupoprograma);
    echo "Se elimino el registro.";


}


?>
