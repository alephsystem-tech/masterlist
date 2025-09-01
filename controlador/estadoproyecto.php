<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_estadoproyecto_modelo.php");

$estadoproyecto=new prg_estadoproyecto_model();

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
    include("../vista/estadoproyecto/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_estaproyecto'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" codestado";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and e.id_pais='$sess_codpais'";

	## Total number of records without filtering
	$data_maxOF=$estadoproyecto->selec_total_estadoproyecto($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and e.descripcion like '%$descripcion%' ";
	
	## Total number of record with filtering
	$data_maxOF2=$estadoproyecto->selec_total_estadoproyecto($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$estadoproyecto->select_estadoproyecto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codestado'];
			$chk=" ";
			
			if($row['flgactivo']=='1')
				$chk=" checked";
			$flgactivo="<div class='custom-control custom-switch  custom-switch-off-danger custom-switch-on-success'>
						  <input type=checkbox class='custom-control-input'onchange='js_changeactive($id)' name='flgstatus_$id' id='flgstatus_$id' $chk >
						  <label class='custom-control-label' for='flgstatus_$id'></label>
						</div>";
			
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediestProy'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliestProy'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			   "grupo"=>$row['grupo'],
			   "codestado"=>$id,
			   "edita"=>$edita,
			    "flgactivo"=>$flgactivo,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editEstadProyecto'){
	$codestado="";
	if(!empty($_POST['codestado']))
		$data_res=$estadoproyecto->selec_one_estadoproyecto($_POST['codestado']);

	$grupo_res=$estadoproyecto->select_grupoestadoproyecto($sess_codpais);

    include("../vista/estadoproyecto/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detEstProyecto'){
    // proceso update a la base de datos usuarios
	
	$descripcion=$_POST['desc_estadoproy'];
	$id_grupo=$_POST['id_grupo'];

	if(empty($_POST['codestado']))
		$codestado=$estadoproyecto->insert_estadoproyecto($descripcion,$id_grupo,$sess_codpais,$usuario_name,$ip);
	else{
		$codestado=$_POST['codestado']; // id
		$estadoproyecto->update_estadoproyecto($codestado,$descripcion,$id_grupo,$sess_codpais,$usuario_name,$ip);
	}	
	 echo $codestado;
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delEstaProyecto'){
    // delete a la base de datos usuarios
	$codestado=$_POST['codestado']; 
    $estadoproyecto->delete_estaproyecto($codestado);
    echo "Se elimino el registro.";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='activoEstado'){
    // delete a la base de datos usuarios
	$codestado=$_POST['codestado']; 
	$flgactivo=$_POST['flgactivo']; 
    $estadoproyecto->activa_estado($codestado,$flgactivo);
    echo "Se actualizo el registro.";
		
}


?>
