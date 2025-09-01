<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_categoria_proy_modelo.php");

$categoriaproy=new prg_categoria_proy_model();

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
    include("../vista/categoriaproy/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_categoriaproy'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" categoria";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais'";

		
	## Total number of records without filtering
	$data_maxOF=$categoriaproy->selec_total_categoriaproy($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( categoria like '%$descripcion%') ";
	
	
	## Total number of record with filtering
	$data_maxOF2=$categoriaproy->selec_total_categoriaproy($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$categoriaproy->select_categoriaproy($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codcategoria'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_edicategoriaproy'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_elicategoriaproy'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "categoria"=>str_replace('"','',json_encode($row['categoria'],JSON_UNESCAPED_UNICODE)),
			   "usuario_ingreso"=>$row['usuario_ingreso'],
			   "usuario_modifica"=>$row['usuario_modifica'],
			   "fecha_ingreso"=>$row['fecha_ingreso'],
			   "fecha_modifica"=>$row['fecha_modifica'],
			   "codcategoria"=>$id,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editcategoriaproy'){
	
	$codcategoria="";
	if(!empty($_POST['codcategoria']))
		$data_res=$categoriaproy->selec_one_categoriaproy($_POST['codcategoria']);

    include("../vista/categoriaproy/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detcategoriaproy'){
    // proceso update a la base de datos usuarios
	
	$categoria=$_POST['categoria'];

	if(empty($_POST['codcategoria']))
		$codcategoria=$categoriaproy->insert_categoriaproy($categoria,$sess_codpais,$usuario_name,$ip);
	else{
		$codcategoria=$_POST['codcategoria']; // id
		$categoriaproy->update_categoriaproy($codcategoria,$categoria,$sess_codpais,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delcategoriaproy'){
    // delete a la base de datos usuarios
	$codcategoria=$_POST['codcategoria']; 
    $categoriaproy->delete_categoriaproy($codcategoria);
    echo "Se elimino el registro.";
}


?>
