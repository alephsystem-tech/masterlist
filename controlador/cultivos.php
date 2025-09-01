<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_cultivo_modelo.php");

$prgcultivo=new prg_cultivo_model();

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
    include("../vista/cultivo/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_cultivo'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" codcultivo";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais'";
	
	## Total number of records without filtering
	$data_maxOF=$prgcultivo->selec_total_cultivo($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and cultivo like '%$descripcion%' ";
	
	## Total number of record with filtering
	$data_maxOF2=$prgcultivo->selec_total_cultivo($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgcultivo->select_cultivos($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codcultivo'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediCultivo'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliCultivo'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "cultivo"=>str_replace('"','',json_encode($row['cultivo'],JSON_UNESCAPED_UNICODE)),
			   "codcultivo"=>$id,
			   "pesos"=>str_replace("\n", '<br>', $row['pesos']),
			   "semana_antes"=>$row['semana_antes'],
			   "semana_despues"=>$row['semana_despues'],
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editCultivo'){
	$codcultivo="";
	if(!empty($_POST['codcultivo']))
		$data_res=$prgcultivo->selec_one_cultivo($_POST['codcultivo']);

    include("../vista/cultivo/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detCultivo'){
    // proceso update a la base de datos usuarios
	
	$cultivo=$_POST['cultivo'];
	$pesos=$_POST['pesos'];
	$semana_antes=$_POST['semana_antes'];
	$semana_despues=$_POST['semana_despues'];

	if(empty($_POST['codcultivo']))
		$codcultivo=$prgcultivo->insert_cultivo($cultivo,$pesos,$semana_antes,$semana_despues,$sess_codpais,$usuario_name,$ip);
	else{
		$codcultivo=$_POST['codcultivo']; // id
		$prgcultivo->update_cultivo($codcultivo,$cultivo,$pesos,$semana_antes,$semana_despues,$sess_codpais,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delCultivo'){
    // delete a la base de datos usuarios
	$codcultivo=$_POST['codcultivo']; 
    $prgcultivo->delete_cultivo($codcultivo);
    echo "Se elimino el registro.";
}


?>
