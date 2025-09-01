<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_menu_modelo.php");
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
    include("../vista/menu/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_menu'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" m.id_menu";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and m.id_pais='esp' ";

	## Total number of records without filtering
	$data_maxOF=$menu->selec_total_menu($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and m.nombre like '%$descripcion%' ";
	
	## Total number of record with filtering
	$data_maxOF2=$menu->selec_total_menu($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$menu->select_menu($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_menu'];
			
			$edita="<button type='button' id='estenl_". $id ."'  class='btn  btn_edimenu'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estenl_". $id ."'  class='btn  btn_elimenu'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "nombre"=>str_replace('"','',json_encode($row['nombre'],JSON_UNESCAPED_UNICODE)),
			   "enlace"=>str_replace('"','',json_encode($row['enlace'],JSON_UNESCAPED_UNICODE)),
			   "id_menu"=>$id,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editMenu'){
	$id_menu="";
	if(!empty($_POST['id_menu']))
		$data_res=$menu->selec_one_menu($_POST['id_menu']);

    include("../vista/menu/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detMenu'){
    // proceso update a la base de datos usuarios
	
	$nombre=$_POST['nombre'];

	if(empty($_POST['id_menu']))
		$id_menu=$menu->insert_menu($nombre,$sess_codpais,$usuario_name,$ip);
	else{
		$id_menu=$_POST['id_menu']; // id
		$menu->update_menu($id_menu,$nombre,$sess_codpais,$usuario_name,$ip);
	}	
	 echo $id_menu;
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delMenu'){
    // delete a la base de datos usuarios
	$id_menu=$_POST['id_menu']; 
    $menu->delete_menu($id_menu);
    echo "Se elimino el registro.";
}


?>
