<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_enlace_modelo.php");
include("../modelo/prg_menu_modelo.php");

$enlace=new prg_enlace_model();
$menu=new prg_menu_model();

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
    include("../vista/enlace/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_enlace'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" prg_enlaces.id_enlace";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and prg_enlaces.id_pais='esp'  ";

		
	## Total number of records without filtering
	$data_maxOF=$enlace->selec_total_enlace($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( prg_enlaces.nombre like '%$descripcion%' or 
			prg_enlace_pais.nombre like '%$descripcion%')";
			
	## Total number of record with filtering
	$data_maxOF2=$enlace->selec_total_enlace($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$enlace->select_enlace($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_enlace'];
			$chk=" ";
			
			if($row['flgactivo']=='1')
				$chk=" checked";
			$edita="<button type='button' id='estenl_". $id ."'  class='btn  btn_edienlace'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estenl_". $id ."'  class='btn  btn_elienlace'><i class='fas fa-trash'></i> </button>";
			$flgactivo="<div class='custom-control custom-switch  custom-switch-off-danger custom-switch-on-success'>
						  <input type=checkbox class='custom-control-input'onchange='js_changeactive($id)' name='flgstatus_$id' id='flgstatus_$id' $chk >
						  <label class='custom-control-label' for='flgstatus_$id'></label>
						</div>";
		
		   $data[] = array( 
			   "menu"=>str_replace('"','',json_encode($row['menu'],JSON_UNESCAPED_UNICODE)),
			   "nombre"=>str_replace('"','',json_encode($row['nombre'],JSON_UNESCAPED_UNICODE)),
			   "accion"=>str_replace('"','',json_encode($row['accion'],JSON_UNESCAPED_UNICODE)),
			   "controlador"=>str_replace('"','',json_encode($row['controlador'],JSON_UNESCAPED_UNICODE)),
			   "id_enlace"=>$id,
			   "flgactivo"=>$flgactivo,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editEnlace'){
	$id_enlace="";
	if(!empty($_POST['id_enlace']))
		$data_res=$enlace->selec_one_enlace($_POST['id_enlace'],$sess_codpais);

	$menu_res=$menu->select_menubypais($sess_codpais);
	$menualt_res=$enlace->select_enlacemenu($sess_codpais);

    include("../vista/enlace/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detEnlace'){
    // proceso update a la base de datos usuarios
	
	$nombre=$_POST['nombre'];
	$dscaccion=$_POST['dscaccion'];
	$controlador=$_POST['controlador'];
	$id_menu=$_POST['id_menu'];

	if(empty($_POST['id_enlace'])){
		$id_enlace=$enlace->insert_enlace($id_menu,$nombre,$dscaccion,$controlador,$sess_codpais,$usuario_name,$ip);
		$enlace->insert_enlace_pais($id_enlace,$nombre,$sess_codpais);
		
	}else{
		$id_enlace=$_POST['id_enlace']; // id
		$enlace->update_enlace($id_menu,$id_enlace,$nombre,$dscaccion,$controlador,$sess_codpais,$usuario_name,$ip);
		
		$data=$enlace->select_enlace_pais($id_enlace,$sess_codpais);
		
		if($data['total']<1)
			$enlace->insert_enlace_pais($id_enlace,$nombre,$sess_codpais);
		else
			$enlace->update_enlace_pais($id_enlace,$nombre,$sess_codpais);
		
		
	}	
	 echo $id_enlace;
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delEnlace'){
    // delete a la base de datos usuarios
	$id_enlace=$_POST['id_enlace']; 
    $enlace->delete_enlace($id_enlace);
    echo "Se elimino el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='activoEnlace'){
    // delete a la base de datos usuarios
	$id_enlace=$_POST['id_enlace']; 
	$flgactivo=$_POST['flgactivo']; 
    $enlace->activa_enlace($id_enlace,$flgactivo,$sess_codpais);
    echo "Se actualizo el registro.";
	
}


?>
