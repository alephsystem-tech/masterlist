<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/kpi_indicador_modelo.php");

$kpiindicador=new kpi_indicador_model();

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
    include("../vista/kpiindicador/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_cer'){
	//**********************************
	// mostrar index de calendario
	//**********************************
    $tipokpi='cer';
	include("../vista/kpiindicador/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_com'){
	//**********************************
	// mostrar index de calendario
	//**********************************
    $tipokpi='com';
	include("../vista/kpiindicador/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_cor'){
	//**********************************
	// mostrar index de calendario
	//**********************************
    $tipokpi='cor';
	include("../vista/kpiindicador/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_kpiindicador'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$tipokpi = $_POST['tipokpi'];
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" kpi_indicador.codindicador";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and kpi_indicador.id_pais='$sess_codpais' and kpi_indicador.tipokpi='$tipokpi'";

		
	## Total number of records without filtering
	$data_maxOF=$kpiindicador->selec_total_kpiindicador($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( kpi_indicador.indicador like '%$descripcion%' or kpi_indicador.codigo like '%$descripcion%' )";
	
	## Total number of record with filtering
	$data_maxOF2=$kpiindicador->selec_total_kpiindicador($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$kpiindicador->select_kpiindicador($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codindicador'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediKpiindicador'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliKpiindicador'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "indicador"=>str_replace('"','',json_encode($row['indicador'],JSON_UNESCAPED_UNICODE)),
			   "codindicador"=>$id,
			   "codigo"=>$row['codigo'],
			   "codcategoria"=>$row['codcategoria'],
			   "categoria"=>$row['categoria'],
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editKpiindicador'){
	$codindicador="";
	$tipokpi = $_POST['tipokpi'];
	
	if(!empty($_POST['codindicador']))
		$data_res=$kpiindicador->selec_one_kpiindicador($_POST['codindicador']);

	$data_categ=$kpiindicador->selec_categoria($sess_codpais,$tipokpi);
    include("../vista/kpiindicador/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detKpiindicador'){
    // proceso update a la base de datos usuarios
	
	$indicador=$_POST['indicador'];
	$codcategoria=$_POST['codcategoria'];
	$codigo=$_POST['codigo'];
	$tipokpi = $_POST['tipokpi'];

	if(empty($_POST['codindicador']))
		$codindicador=$kpiindicador->insert_kpiindicador($tipokpi,$indicador,$codigo,$codcategoria,$sess_codpais,$usuario_name,$ip);
	else{
		$codindicador=$_POST['codindicador']; // id
		$kpiindicador->update_kpiindicador($tipokpi,$codindicador,$indicador,$codigo,$codcategoria,$sess_codpais,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delKpiindicador'){
    // delete a la base de datos usuarios
	$codindicador=$_POST['codindicador']; 
	$tipokpi = $_POST['tipokpi'];
    $kpiindicador->delete_kpiindicador($codindicador);
    echo "Se elimino el registro.";
}


?>
