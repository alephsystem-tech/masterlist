<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/kpi_auditor_modelo.php");

$kpiauditor=new kpi_auditor_model();

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
	$title=$land_auditor;
    include("../vista/kpiauditor/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_cer'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	$tipokpi='cer';
	$title=$land_certificador;
    include("../vista/kpiauditor/index.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_com'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	$tipokpi='com';
	$title=$land_comercial;
    include("../vista/kpiauditor/index.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_cor'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	$tipokpi='cor';
	$title=$land_coordinador;
    include("../vista/kpiauditor/index.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_kpiauditor'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$tipokpi = $_POST['tipokpi'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" codauditor";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais' and tipokpi='$tipokpi'";

	## Total number of records without filtering
	$data_maxOF=$kpiauditor->selec_total_kpiauditor($searchQuery);
	$totalRecords = $data_maxOF['total'];


	if(!empty($descripcion))
		$searchQuery.=" and ( nombres like '%$descripcion%' or codigo like '%$descripcion%' )";
		
	## Total number of record with filtering
	$data_maxOF2=$kpiauditor->selec_total_kpiauditor($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$kpiauditor->select_kpiauditor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codauditor'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediKpiauditor'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliKpiauditor'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "nombres"=>str_replace('"','',json_encode($row['nombres'],JSON_UNESCAPED_UNICODE)),
			   "codauditor"=>$id,
			   "email"=>$row['email'],
			   "codigo"=>$row['codigo'],
			   "ref_pais"=>$row['ref_pais'],
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editKpiauditor'){
	$codauditor="";
	$tipokpi = $_POST['tipokpi'];
	
	$title=$land_auditor;
	if($tipokpi=='cer')
		$title=$land_certificador;
	else if($tipokpi=='com')
		$title=$land_comercial;
	else if($tipokpi=='cor')
		$title=$land_coordinador;

	
	if(!empty($_POST['codauditor']))
		$data_res=$kpiauditor->selec_one_kpiauditor($_POST['codauditor']);

    include("../vista/kpiauditor/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detKpiauditor'){
    // proceso update a la base de datos usuarios
	
	$nombres=$_POST['nombres'];
	$codigo=$_POST['codigo'];
	$ref_pais=$_POST['ref_pais'];
	$email=$_POST['email'];
	$tipokpi = $_POST['tipokpi'];
	
	if(empty($_POST['codauditor']))
		$codauditor=$kpiauditor->insert_kpiauditor($nombres,$codigo,$email,$ref_pais,$tipokpi,$sess_codpais,$usuario_name,$ip);
	else{
		$codauditor=$_POST['codauditor']; // id
		$kpiauditor->update_kpiauditor($codauditor,$nombres,$codigo,$email,$ref_pais,$tipokpi,$sess_codpais,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delKpiauditor'){
    // delete a la base de datos usuarios
	$codauditor=$_POST['codauditor']; 
    $kpiauditor->delete_kpiauditor($codauditor);
    echo "Se elimino el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='valUsuarioKpi'){	
	
	$codigo=$_POST['codigo'];
	$email=$_POST['email'];

	$data=$kpiauditor->validate_kpiauditor($codigo,$email,$codauditor);
	if($data)
		echo "<center><font color=red>El usuario ya existe.</font></center>";

}else if(!empty($_POST['accion']) and $_POST['accion']=='viewKpiauditor'){
	//**********************************
	// mostrar index de old auditores
	//**********************************
	$tipokpi=$_POST['tipokpi'];
	if($tipokpi=='aud')
		$title="Auditor";
	else if($tipokpi=='cer')
		$title="Certificador";
	else if($tipokpi=='com')
		$title="Comercial";
	else if($tipokpi=='cor')
		$title="Coordinador";
	
    include("../vista/kpiauditor/index_old.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_oldauditor'){
	
	//***********************************************************
	// 
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$tipokpi=$_POST['tipokpi'];
	
	if($tipokpi=='aud')
		$querol="3";
	else if($tipokpi=='cer')
		$querol="10";
	else if($tipokpi=='com')
		$querol="7";
	else if($tipokpi=='cor')
		$querol="11";
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" 2";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	//$searchQuery = " and prg_usuarios.id_rol in ($querol)";
	$searchQuery = " AND $querol IN (SELECT id_rol FROM prg_auditorxrol WHERE id_auditor=a.id_auditor) ";

	if(!empty($descripcion))
		$searchQuery.=" and ( concat_ws(' ',a.nombre,a.apepaterno,a.apematerno) like '%$descripcion%' 
		or p.nombre like '%$descripcion%' )";
		
	## Total number of records without filtering
	$data_maxOF=$kpiauditor->selec_total_oldauditor();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$kpiauditor->selec_total_oldauditor($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$kpiauditor->select_oldauditor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
		   $data[] = array( 
			   "auditor"=>str_replace('"','',json_encode($row['auditor'],JSON_UNESCAPED_UNICODE)),
			   "pais"=>$row['pais'],
			   "usuario"=>$row['usuario'],
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
	

}


?>
