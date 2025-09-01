<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/kpi_programa_modelo.php");

$kpiprograma=new kpi_programa_model();

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
	include("../vista/kpiprograma/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_cer'){
	$tipokpi='cer';
	include("../vista/kpiprograma/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_com'){
	$tipokpi='com';
	include("../vista/kpiprograma/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_cor'){
	$tipokpi='cor';
	include("../vista/kpiprograma/index.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_kpiprograma'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	$tipokpi = $_POST['tipokpi'];
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" kpi_programa.codprograma";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and kpi_programa.id_pais='$sess_codpais'  and kpi_programa.tipokpi='$tipokpi'";

	
	## Total number of records without filtering
	$data_maxOF=$kpiprograma->selec_total_kpiprograma($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( kpi_programa.programa like '%$descripcion%' )";
			
	## Total number of record with filtering
	$data_maxOF2=$kpiprograma->selec_total_kpiprograma($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$kpiprograma->select_kpiprograma($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codprograma'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediKpiprograma'><i class='fas fa-edit'></i> </button>";
			$config="<button type='button' id='estproy_". $id ."'  class='btn  btn_confKpiprograma'><i class='fas fa-link'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliKpiprograma'><i class='fas fa-trash'></i> </button>";
			
		
		   $data[] = array( 
			   "programa"=>str_replace('"','',json_encode($row['programa'],JSON_UNESCAPED_UNICODE)),
			   "codprograma"=>$id,
			   "indicador"=>$row['indicador'],
			   "emailsuper"=>$row['emailsuper'],
			   "edita"=>$edita,
			   "elimina"=>$elimina,
			   "config"=>$config,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editKpiprograma'){
	$codprograma="";
	$tipokpi = $_POST['tipokpi'];
	if(!empty($_POST['codprograma'])){
		$data_res=$kpiprograma->selec_one_kpiprograma($_POST['codprograma']);
		$data_indProg=$kpiprograma->selec_kpiprograma_indicad($_POST['codprograma']);
	}	

	$data_pro=$kpiprograma->selec_prgprograma($sess_codpais);
	
    include("../vista/kpiprograma/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detKpiprograma'){
    // proceso update a la base de datos usuarios
	$tipokpi = $_POST['tipokpi'];
	$programa=$_POST['programa'];
	$emailsuper=$_POST['emailsuper'];
	$id_programa=$_POST['id_programa'];
	
	$flgfail="0";
	if(!empty($_POST['flgfail']))
		$flgfail="1";
	
	$flgvalida="0";
	if(!empty($_POST['flgvalida']))
		$flgvalida="1";

	if(empty($_POST['codprograma']))
		$codprograma=$kpiprograma->insert_kpiprograma($tipokpi,$emailsuper,$programa,$id_programa,$flgfail,$flgvalida,$sess_codpais,$usuario_name,$ip);
	else{
		$codprograma=$_POST['codprograma']; // id
		$kpiprograma->update_kpiprograma($tipokpi,$emailsuper,$codprograma,$programa,$id_programa,$flgfail,$flgvalida,$sess_codpais,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delKpiprograma'){
    // delete a la base de datos usuarios
	$codprograma=$_POST['codprograma']; 
    $kpiprograma->delete_kpiprograma($codprograma);
    echo "Se elimino el registro.";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='addIndKpiprograma'){
	$codprograma=$_POST['codprograma'];
	$tipokpi = $_POST['tipokpi'];
	if(!empty($_POST['codindicador'])){
		$codindicador=$_POST['codindicador'];
		$data_res=$kpiprograma->selec_one_kpiprograma_ind($codprograma,$codindicador);
	}
	$data_peso=$kpiprograma->selec_pesoindicador($sess_codpais,$codprograma);
	$pesofalta=1;
	if(!empty($data_peso))
		$pesofalta-=$data_peso['peso'];

	$data_ind=$kpiprograma->selec_indicador($sess_codpais,$codprograma,$tipokpi);
	
	if($pesofalta>0)
		include("../vista/kpiprograma/frm_indicador.php");
	else
		include("../vista/kpiprograma/frm_mensaje.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_addIndKpiprograma'){
    // proceso update a la base de datos usuarios
	
	$codprograma=$_POST['codprograma'];
	$codindicador=$_POST['codindicador'];
	$peso=$_POST['peso'];
	
	$codprograma=$kpiprograma->insert_kpiprograma_ind($codprograma,$codindicador,$peso,$sess_codpais,$usuario_name,$ip);
	
	 echo "Se actualizo el registro";	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delKpiprogramaxindicador'){
    // delete a la base de datos usuarios
	$codprograma=$_POST['codprograma']; 
	$codindicador=$_POST['codindicador']; 
    $kpiprograma->delete_kpiprogramaxindicador($codprograma,$codindicador);
    echo "Se elimino el registro.";
	
}


?>
