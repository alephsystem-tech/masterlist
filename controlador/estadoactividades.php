<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_estadoactividad_modelo.php");
$estadoactividad=new prg_estadoactividad_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

$pathFoto = 'assets/img/'; // upload directory
$valid_extensions = array('jpg','gif','png','jpeg','bmp'); // valid extensions

//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='web_index'){
	//**********************************
	// mostrar index de calendario
	//**********************************
    include("../vista/estadoactividad/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_estadoactividad'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" id_estadoactividad ";
	$columnSortOrder=" asc ";
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
	$data_maxOF=$estadoactividad->selec_total_estadoactividad();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$estadoactividad->selec_total_estadoactividad($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$estadoactividad->select_estadoactividad($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
			$id=$row['id_estadoactividad'];
			$paises="<button type='button' id='paisact_". $id ."'  class='btn  btn_paisActividad'><i class='fas fa-edit'></i> </button>";
			if($row['imagen']!='')
				$imagen="<button type='button' id='iconoact_". $id ."'  class='btn  btn_icoActividad'><img src='".$pathFoto.$row['imagen']."'></button>";
			else
				$imagen="";
			
		   $data[] = array( 
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			   "id_estadoactividad"=>$id,
			   "imagen"=>$imagen,
			   "paises"=>$paises,

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

}else if(!empty($_POST['accion']) and $_POST['accion']=='iconoEstadoact'){
	//**********************************
	
	//**********************************
    $id_estadoactividad = $_POST['id_estadoactividad'];
	$data_=$estadoactividad->select_one_estadoactividad($id_estadoactividad);
	include("../vista/estadoactividad/frm_upload.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detEstActUpload'){	

	$id_estadoactividad = $_POST['id_estadoactividad'];	 
	
	$foto=uploadFile($_FILES,$pathFoto,'fileico');
	if(substr($foto, 0,5)!='Error' )
		$estadoactividad->update_estadoIcono($id_estadoactividad,$foto);
	// foto
	echo "Se copio el archivo $foto";

}else if(!empty($_POST['accion']) and $_POST['accion']=='paisEstadoact'){
	//**********************************
	
	//**********************************
    $id_estadoactividad = $_POST['id_estadoactividad'];
	$data_=$estadoactividad->select_one_estadoactividad($id_estadoactividad);
	$data_relacion=$estadoactividad->select_estadoactividadxpais($id_estadoactividad);
	include("../vista/estadoactividad/frm_pais.php");


}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detPaisEstAct'){	

	$id_estadoactividad = $_POST['id_estadoactividad'];	 
	
	$sql="";
	foreach($_POST['paisxactividad'] as $id_pais){
		$estadoactividad->delete_estadoxpais($id_estadoactividad);
		if($sql=="")
			$sql="($id_estadoactividad ,'$id_pais')";
		else	
			$sql.=",($id_estadoactividad ,'$id_pais')";
		
		
	}
	if($sql!='')
		$estadoactividad->insert_estadoxpais($sql);
	echo "Se actualizo la relacion de paises.";
	
}


?>
