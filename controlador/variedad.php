<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_variedad_modelo.php");

$prgvariedad=new prg_variedad_model();

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
    include("../vista/variedad/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_variedad'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" codvariedad";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and v.id_pais='$sess_codpais'";
	
	## Total number of records without filtering
	$data_maxOF=$prgvariedad->selec_total_variedad($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and variedad like '%$descripcion%' ";
	
	## Total number of record with filtering
	$data_maxOF2=$prgvariedad->selec_total_variedad($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgvariedad->select_variedad($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codvariedad'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediVariedad'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliVariedad'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "cultivo"=>str_replace('"','',json_encode($row['cultivo'],JSON_UNESCAPED_UNICODE)),
			   "variedad"=>str_replace('"','',json_encode($row['variedad'],JSON_UNESCAPED_UNICODE)),
			   "codvariedad"=>$id,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editVariedad'){
	$codvariedad="";
	$data_cultivo=$prgvariedad->selec_cultivos($sess_codpais);
	if(!empty($_POST['codvariedad']))
		$data_res=$prgvariedad->selec_one_variedad($_POST['codvariedad']);

    include("../vista/variedad/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detVariedad'){
    // proceso update a la base de datos usuarios
	
	$codcultivo=$_POST['codcultivo'];
	$variedad=$_POST['variedad'];
	
	if(empty($_POST['codvariedad']))
		$codvariedad=$prgvariedad->insert_variedad($codcultivo,$variedad,$sess_codpais,$usuario_name,$ip);
	else{
		$codvariedad=$_POST['codvariedad']; // id
		$prgvariedad->update_variedad($codvariedad,$codcultivo,$variedad,$sess_codpais,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delVariedad'){
    // delete a la base de datos usuarios
	$codvariedad=$_POST['codvariedad']; 
    $prgvariedad->delete_variedad($codvariedad);
    echo "Se elimino el registro.";
}


?>
