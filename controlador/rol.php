<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_rol_modelo.php");
$rol=new prg_rol_model();

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
    include("../vista/rol/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_rol'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" m.id_rol";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	//$searchQuery = " and m.id_rol='$sess_codpais' ";
	$searchQuery = "";
	
	
		
	## Total number of records without filtering
	$data_maxOF=$rol->selec_total_rol($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and m.nombre like '%$descripcion%' ";
	
	## Total number of record with filtering
	$data_maxOF2=$rol->selec_total_rol($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$rol->select_rol($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_rol'];
			$chk=" ";
			
			if($row['flgcalifica']=='1')
				$chk=" checked";
			$flgcalifica="<div class='custom-control custom-switch  custom-switch-off-danger custom-switch-on-success'>
						  <input type=checkbox class='custom-control-input'onchange='js_changeactive($id)' name='flgstatus_$id' id='flgstatus_$id' $chk >
						  <label class='custom-control-label' for='flgstatus_$id'></label>
						</div>";
						
			$edita="<button type='button' id='estrol_". $id ."'  class='btn  btn_edirol'><i class='fas fa-edit'></i> </button>";
			$nivel="<button type='button' id='estrol_". $id ."'  class='btn  btn_nivelrol'><i class='fas fa-clone'></i> </button>";
			$elimina="<button type='button' id='estrol_". $id ."'  class='btn  btn_elirol'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "nombre"=>str_replace('"','',json_encode($row['nombre'],JSON_UNESCAPED_UNICODE)),
			   "enlace"=>str_replace('"','',json_encode($row['enlace'],JSON_UNESCAPED_UNICODE)),
			   "id_menu"=>$id,
			   "edita"=>$edita,
			   "nivel"=>$nivel,
			   "califica"=>$flgcalifica,
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='calificaRol'){
    // delete a la base de datos usuarios
	$id_rol=$_POST['id_rol']; 
	$flgcalifica=$_POST['flgcalifica']; 
    $rol->califica_rol($id_rol,$flgcalifica,$usuario_name,$ip);
    echo "Se actualizo el registro.";
	
		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editRol'){
	$id_rol="";
	if(!empty($_POST['id_rol'])){
		$id_rol=$_POST['id_rol'];
		$data_res=$rol->selec_one_rol($id_rol);
	}
	$data_enlace=$rol->selec_enlacebyPais($sess_codpais,$id_rol);

    include("../vista/rol/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detRol'){
    // proceso update a la base de datos usuarios
	
	$nombre=$_POST['nombre'];
	$tipohome=$_POST['tipohome'];

	if(empty($_POST['id_rol']))
		$id_rol=$rol->insert_rol($nombre,$tipohome,$sess_codpais,$usuario_name,$ip);
	else{
		$id_rol=$_POST['id_rol']; // id
		$error=$rol->update_rol($id_rol,$nombre,$tipohome,$sess_codpais,$usuario_name,$ip);
		
	}	
	
	$rol->delete_enlacexrol($id_rol,$sess_codpais);
	foreach($_POST['id_enlace'] as $id_enlace){
		$rol->insert_enlacexrol($id_rol,$id_enlace,$sess_codpais);
	}
	
	 echo $id_rol;

}else if(!empty($_POST['accion']) and $_POST['accion']=='nivelRol'){

	$id_rol=$_POST['id_rol'];
	$data_res=$rol->selec_one_rol($id_rol);
	$data_enlace=$rol->selec_enlacebyPais($sess_codpais,$id_rol);

	$data_nivelenlace=$rol->selec_enlacenivelbyPais($sess_codpais,$id_rol);

	//var_dump($data_nivelenlace);
	foreach($data_nivelenlace as $row){
		$arrayR[$row['id_enlace']]=0;
		$arrayU[$row['id_enlace']]=0;
		$arrayD[$row['id_enlace']]=0;

		if($row['isread']==="1") $arrayR[$row['id_enlace']]=1;
		if($row['isupdate']==="1") $arrayU[$row['id_enlace']]=1;
		if($row['isdelete']==="1") $arrayD[$row['id_enlace']]=1;
	}

    include("../vista/rol/frm_nivel.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detnivelRol'){
    // proceso update a la base de datos usuarios
	$id_rol=$_POST['id_rol'];
	$data_enlace=$rol->selec_enlacebyPais($sess_codpais,$id_rol);
	
	$rol->delete_enlacenivelxrol($id_rol,$sess_codpais);
	foreach($data_enlace as $row){
		$id_enlace=$row['id_enlace'];
		$isread="0";
		$isupdate="0";
		$isdelete="0";
		
		if($_POST['r_'.$id_enlace])
			$isread="1";
		if($_POST['u_'.$id_enlace])
			$isupdate="1";
		if($_POST['d_'.$id_enlace])
			$isdelete="1";
		
		if($isread=='1' or $isupdate=='1' or $isdelete=='1')
			$rol->insert_enlacenivelxrol($sess_codpais,$id_rol,$id_enlace,$isread,$isupdate,$isdelete);
	}
	
	 echo "Se actualizo el registro.";

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delRol'){
    // delete a la base de datos usuarios
	$id_rol=$_POST['id_rol']; 
    $rol->delete_rol($id_rol);
    echo "Se elimino el registro.";
}


?>
