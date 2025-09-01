<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_producto_modelo.php");

$prgproducto=new prg_producto_model();

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
    include("../vista/productos/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_producto'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" producto";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " ";

	## Total number of records without filtering
	$data_maxOF=$prgproducto->selec_total_producto($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and producto like '%$descripcion%' ";
	## Total number of record with filtering
	$data_maxOF2=$prgproducto->selec_total_producto($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgproducto->select_producto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codproducto'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediProducto'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliProducto'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "producto"=>str_replace('"','',json_encode($row['producto'],JSON_UNESCAPED_UNICODE)),
			   "codproducto"=>$id,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editProducto'){
	$codproducto="";
	if(!empty($_POST['codproducto']))
		$data_res=$prgproducto->selec_one_producto($_POST['codproducto']);

    include("../vista/productos/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detProducto'){
    // proceso update a la base de datos usuarios
	
	$producto=$_POST['producto'];

	if(empty($_POST['codproducto']))
		$codproducto=$prgproducto->insert_producto($producto,$sess_codpais,$usuario_name,$ip);
	else{
		$codproducto=$_POST['codproducto']; // id
		$prgproducto->update_producto($codproducto,$producto,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delProducto'){
    // delete a la base de datos usuarios
	$codproducto=$_POST['codproducto']; 
    $prgproducto->delete_producto($codproducto);
    echo "Se elimino el registro.";
}


?>
