<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_certificado_modelo.php");

$prgcertificado=new prg_certificado_model();

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
    include("../vista/certificado/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_certificado'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" codcertificadora";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais'";
	
	## Total number of records without filtering
	$data_maxOF=$prgcertificado->selec_total_certificado($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and certificadora like '%$descripcion%' ";
	
	## Total number of record with filtering
	$data_maxOF2=$prgcertificado->selec_total_certificado($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgcertificado->select_certificados($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codcertificadora'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediCertificado'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliCertificado'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "certificadora"=>str_replace('"','',json_encode($row['certificadora'],JSON_UNESCAPED_UNICODE)),
			   "codcertificadora"=>$id,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editCertificado'){
	$codcertificadora="";
	if(!empty($_POST['codcertificadora']))
		$data_res=$prgcertificado->selec_one_certificado($_POST['codcertificadora']);

    include("../vista/certificado/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detCertificado'){
    // proceso update a la base de datos usuarios
	
	$certificado=$_POST['certificado'];
	

	if(empty($_POST['codcertificadora']))
		$codcertificadora=$prgcertificado->insert_certificado($certificado,$sess_codpais,$usuario_name,$ip);
	else{
		$codcertificadora=$_POST['codcertificadora']; // id
		$prgcertificado->update_certificado($codcertificadora,$certificado,$sess_codpais,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delCertificado'){
    // delete a la base de datos usuarios
	$codcertificadora=$_POST['codcertificadora']; 
    $prgcertificado->delete_certificado($codcertificadora);
    echo "Se elimino el registro.";
}


?>
