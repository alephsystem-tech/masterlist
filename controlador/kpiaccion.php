<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/kpi_accion_modelo.php");

$kpiaccion=new kpi_accion_model();

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
	$tipokpi='aud';
    include("../vista/kpiaccion/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_cer'){
	$tipokpi='cer';
	include("../vista/kpiaccion/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_com'){
	$tipokpi='com';
	include("../vista/kpiaccion/index.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_cor'){
	$tipokpi='cor';
	include("../vista/kpiaccion/index.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_kpiaccion'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	$tipokpi = $_POST['tipokpi'];
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" codaccion";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and tipokpi='$tipokpi' and id_pais='$sess_codpais' ";

	if(!empty($descripcion))
		$searchQuery.=" and ( valor like '%$descripcion%' or accion like '%$descripcion%' ) ";
		
	## Total number of records without filtering
	$data_maxOF=$kpiaccion->selec_total_kpiaccion();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$kpiaccion->selec_total_kpiaccion($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$kpiaccion->select_kpiaccion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codaccion'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediKpiaccion'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliKpiaccion'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "accion"=>str_replace('"','',json_encode($row['accion'],JSON_UNESCAPED_UNICODE)),
			   "codaccion"=>$id,
			   "valor"=>$row['valor'],
			   "maximo"=>$row['maximo'],
			   "minimo"=>$row['minimo'],
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editKpiaccion'){
	$codaccion="";
	$tipokpi = $_POST['tipokpi'];
	if(!empty($_POST['codaccion']))
		$data_res=$kpiaccion->selec_one_kpiaccion($_POST['codaccion']);

    include("../vista/kpiaccion/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detKpiaccion'){
    // proceso update a la base de datos usuarios
	$tipokpi = $_POST['tipokpi'];
	$dscaccion=$_POST['dscaccion'];
	$valor=$_POST['valor'];
	$minimo=$_POST['minimo'];
	$maximo=$_POST['maximo'];

	if(empty($_POST['codaccion']))
		$codaccion=$kpiaccion->insert_kpiaccion($tipokpi,$dscaccion,$valor,$minimo,$maximo,$sess_codpais,$usuario_name,$ip);
	else{
		$codaccion=$_POST['codaccion']; // id
		$kpiaccion->update_kpiaccion($tipokpi,$codaccion,$dscaccion,$valor,$minimo,$maximo,$sess_codpais,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delKpiaccion'){
    // delete a la base de datos usuarios
	$tipokpi = $_POST['tipokpi'];
	$codaccion=$_POST['codaccion']; 
    $kpiaccion->delete_kpiaccion($codaccion);
    echo "Se elimino el registro.";
}


?>
