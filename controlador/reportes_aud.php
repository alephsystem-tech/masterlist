<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/reportes_aud_modelo.php");
include("../modelo/prg_auditor_modelo.php");


$reporteaud=new reportes_aud_model();
$prgauditor=new prg_auditor_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

	//***********************************************************

	//**********************************
	// mostrar capacidad auditor
	//**********************************
if(!empty($_POST['accion']) and $_POST['accion']=='disponible'){

	$auditor_res=$prgauditor->select_auditor_select($sess_codpais);
    include("../vista/reporteaud/index_disponible.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='verCalendario'){

	$id_auditor = $_POST['id_auditor'];
	$start = $_POST['start'];
	$end = $_POST['end'];
	
	if($id_auditor!=''){
		$data_OF=$reporteaud->select_actByAuditor($id_auditor,$sess_codpais,$start,$end);
		
		$data = array();
		if(!empty($data_OF)){
		 foreach($data_OF as $row) {
		  $data[] = array( 
			 "id"=>$row['id'],
			 "start"=>$row['inicio']. " 08:00:00",
			 "end"=>$row['fin']. " 17:00:00",
			 "title"=>'Ocupado' ,
			 "className"=> "holiday",
			 "allDay"=> true,
			 "textColor"=> " #e2b8ac",
			 "displayEventTime"=> false,
			 "editable"=> false,
			 "resourceEditable"=> false 
			);
		 }
		}
		echo json_encode($data);
		
	}
}



?>
