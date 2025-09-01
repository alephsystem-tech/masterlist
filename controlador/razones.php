<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/lst_razones_modelo.php");

$lstrazon=new lst_razones_model();

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
    include("../vista/razones/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_razon'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" codrazon";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais'";

		
	## Total number of records without filtering
	$data_maxOF=$lstrazon->selec_total_razones($searchQuery);
	$totalRecords = $data_maxOF['total'];


	if(!empty($descripcion))
		$searchQuery.=" and razon like '%$descripcion%' ";
	
	## Total number of record with filtering
	$data_maxOF2=$lstrazon->selec_total_razones($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$lstrazon->select_razones($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codrazon'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediRazon'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliRazon'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "razon"=>str_replace('"','',json_encode($row['razon'],JSON_UNESCAPED_UNICODE)),
			   "codrazon"=>$id,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editRazon'){
	$codrazon="";
	if(!empty($_POST['codrazon']))
		$data_res=$lstrazon->selec_one_razon($_POST['codrazon']);

    include("../vista/razones/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detRazon'){
    // proceso update a la base de datos usuarios
	
	$razon=$_POST['razon'];
	

	if(empty($_POST['codrazon']))
		$codrazon=$lstrazon->insert_razones($razon,$sess_codpais,$usuario_name,$ip);
	else{
		$codrazon=$_POST['codrazon']; // id
		$lstrazon->update_razones($codrazon,$razon,$sess_codpais,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delRazon'){
    // delete a la base de datos usuarios
	$codrazon=$_POST['codrazon']; 
    $lstrazon->delete_razones($codrazon);
    echo "Se elimino el registro.";
}


?>
