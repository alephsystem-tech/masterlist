<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_usuario_modelo.php");
include("../modelo/prg_auditor_modelo.php");
include("../modelo/prg_auditoractividad_modelo.php");

include("../modelo/prg_actividad_modelo.php");
include("../modelo/prg_programa_modelo.php");
include("../modelo/prg_proyectoactividad_modelo.php");
include("../modelo/mae_pais_modelo.php");

$usuario=new prg_usuario_model();
$auditoractividad=new auditoractividad_model();
$auditor=new prg_auditor_model();
$actividad=new prg_actividad_model();
$programa=new prg_programa_model();
$proyectoactividad=new prg_proyectoactividad_model();
$pais=new mae_pais_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$pathAudActi = 'archivos/ofertaActiviAuditor/'; // upload directory
$valid_extensions = array('xls','xlsx','doc','docx','pdf'); // valid extensions

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	include('class.calendar.php');
	
	if(!empty($_POST['id_auditor']))
		$consultaauditor=$_POST['id_auditor'];
	else
		$consultaauditor=$sess_codauditor;
	
	$auditor_res=$auditor->select_auditorByID($consultaauditor,$sess_codpais);
	
	$db=new DBManejador(); // crea instancio de bd
	$phpCalendar = new PHPCalendar ();
	$calendarHTML = $phpCalendar->getCalendarHTML($db,$consultaauditor,$weekDayName);

	$id_auditor=$consultaauditor;
    include("../vista/auditoractividad/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='calendar'){
	
	//**********************************
	// funcion recargar calendario calendario
	//**********************************
	
	include('class.calendar.php');
    // cargar index actividades x auditores
	$id_auditor=$_POST['id_auditor'];

	$db=new DBManejador(); // crea instancio de bd
	$phpCalendar = new PHPCalendar ();
	$calendarHTML = $phpCalendar->getCalendarHTML($db,$id_auditor,$weekDayName);
	echo $calendarHTML;

}else if(!empty($_POST['accion']) and $_POST['accion']=='indexCalAud'){

	//***********************************************************
	// funcion index actividades de auditor por fecha
	//***********************************************************
	
	$id_auditor=$_POST['id_auditor'];
	$fecha=$_POST['fecha'];
	
	$auditor_res=$auditor->select_one_auditorSimpl($id_auditor);
	
	$searchQuery="";
	if(!empty($fecha))
		$searchQuery.=" and fecha='$fecha' ";

	if(!empty($id_auditor)){
		$searchQuery.=" and prg_auditoractividad.id_auditor='$id_auditor' ";
	}		 
	
	## Total number of records without filtering data_avance
	$data_avance=$auditoractividad->selec_avance_activixauditor($searchQuery,1);
	
	$data_fer=$auditoractividad->select_feriadoCalendar($fecha,$sess_codpais,$id_auditor);
	if(!empty($data_fer))
		$data_avance['total']=100;
	
		
	include("../vista/auditoractividad/indexCalAuditor.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_audacti'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$id_auditor = $_POST['id_auditor'];
	$fecha = $_POST['fecha'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" prg_auditoractividad.id_auditactiv ";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	//$searchQuery = " and prg_actividadxrol.id_rol=$sess_codrol";
	$searchQuery="";
	if(!empty($fecha))
		$searchQuery.=" and fecha='$fecha' ";
	
	if(!empty($id_auditor)){
		$searchQuery.=" and prg_auditoractividad.id_auditor='$id_auditor' ";
	}
			 
	
	## Total number of records without filtering
	$data_maxOF=$auditoractividad->selec_total_activixauditor($searchQuery,1);
	if(!empty($data_maxOF))
		$totalRecords = $data_maxOF['total'];
	else
		$totalRecords = 0;

	
	
	## Total number of record with filtering
	$data_maxOF2=$auditoractividad->selec_total_activixauditor($searchQuery,1);
	$totalRecordwithFilter = $data_maxOF2['total'];

	$data_fer=$auditoractividad->select_feriadoCalendar($fecha,$sess_codpais,$id_auditor);
	if(!empty($data_fer))
		$totalRecordwithFilter++;
	
	## Fetch records
	$data_OF=$auditoractividad->selec_actividadesxauditor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$noferiado=1);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_auditactiv'];
			
			if( ($sess_codauditor==$row['id_auditor']) and ($row['flgeditacalendar']=='1' or $row['id']==0) or $row['rol']>0 ){
				$edita="<button type='button' id='ActAud_". $id ."'  class='btn  btn_ediActAud'><i class='fas fa-edit'></i> </button>";
				$elimina="<button type='button' id='ActAud_". $id ."'  class='btn  btn_eliActAud'><i class='fas fa-trash'></i> </button>";
			}else{
				$edita="";
				$elimina="";
			}
			
			if($row['id']<1){
			   $actividad=$row['actividad'];
			   $subprograma=str_replace(',',' ,',$row['subprograma']);
			}else{
				$actividad=$row['tmp_actividad'];
			    $subprograma=str_replace(',',' ,',$row['tmp_subprograma']);
			}
			
			// inicio data array
		   $data[] = array( 
				"actividad"=>str_replace('"','',json_encode($actividad,JSON_UNESCAPED_UNICODE)),
				"subprograma"=>str_replace('"','',json_encode($subprograma,JSON_UNESCAPED_UNICODE)),
			   "id_auditactiv"=>$id,
			   "id_actividad"=>$row['id_actividad'],
			   "id_auditor"=>$row['id_auditor'],
			   "pais"=>$row['pais'],
			   "porcentaje"=>$row['porcentaje'],
			    "comentario"=>$row['comentario'],
			   "fecha"=>$row['fecha'],
			   "project_id"=>$row['project_id'],
			   "id"=>$row['id'],
			   "flgeditacalendar"=>$row['flgeditacalendar'],
			   "rol"=>$row['rol'],
			   "oferta_dsc"=>$row['oferta_dsc'],
			   "flgfinalizo"=>$row['flgfinalizo'],
			   "finalizo_dsc"=>$row['finalizo_dsc'],
			   "proyecto"=>str_replace('"','',json_encode($row['proyecto'],JSON_UNESCAPED_UNICODE)),
				"nota"=>str_replace('"','',json_encode($row['nota'],JSON_UNESCAPED_UNICODE)),
			   "edita"=>$edita,
			   "elimina"=>$elimina,
		   );
		   // fin data array
		   
		  
		   
		}
	}


	// inicio data array feriados
	if(!empty($data_fer)){
		foreach($data_fer as $row){
		    $data[] = array( 
				"actividad"=>'Feriado',
				"subprograma"=>'Feriado',
			   "id_auditactiv"=>0,
			   "id_actividad"=>0,
			   "id_auditor"=>0,
			   "pais"=>'',
			   "porcentaje"=>100,
			    "comentario"=>'',
			   "fecha"=>$fecha,
			   "project_id"=>'',
			   "id"=>'',
			   "flgeditacalendar"=>0,
			   "rol"=>'',
			   "oferta_dsc"=>'',
			   "flgfinalizo"=>'',
			   "finalizo_dsc"=>'',
			   "proyecto"=>'',
				"nota"=>$row['descripcion'],
			   "edita"=>'',
			   "elimina"=>'',
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editAuditActivi'){
    // open formualario para editar
	
	
	$id_auditactiv="";
	if(!empty($_POST['id_auditactiv']))
		$id_auditactiv=$_POST['id_auditactiv']; 
	
	$id_auditor=$_POST['id_auditor']; 
	$fecha=$_POST['fecha']; 
	
	$ff=explode("-",$fecha);
	$quefechaes=$ff[2] ." ". $queMesFull[intval($ff[1])]." ". $ff[0];
	
	$porcentaje=0;
	$id_proy="";
	$data_res=$auditoractividad->selec_one_actividadesxauditor($id_auditactiv);
	if(!empty($data_res)){
		$porcentaje=$data_res['porcentaje'];
		$id_proy=$data_res['id_proy'];
	
	}
	// $actividad_res=$actividad->selec_actividadesByRol($sess_codrol);
	$flgactivo=1;
	$actividad_res=$actividad->selec_actividadesByAuditor($sess_codauditor,$sess_codpais,$flgactivo);
	$programa_res=$programa->selec_programasbypais($sess_codpais,$flgactivo=1);
	$project_res=$proyectoactividad->selec_proyectosGroup('',$sess_codpais,$id_proy);
	$pais_res=$pais->selec_paises();
	$paisID_res=$pais->selec_one_paisby_pais($sess_codpais);
	$t_id_pais="";
	if(!empty($paisID_res))
		$t_id_pais=$paisID_res['id_pais'];

	$searchQuery="";
	if(!empty($fecha))
		$searchQuery.=" and fecha='$fecha' ";

	if(!empty($id_auditor)){
		$searchQuery.=" and prg_auditoractividad.id_auditor='$id_auditor' ";
	}
	

	$total=0;
	$data_avance=$auditoractividad->selec_avance_activixauditor($searchQuery,1);
	if(!empty($data_avance))
		$total=$data_avance['total'];

	 $maximo=100 - $total + $porcentaje;
	
	if(empty($id_auditactiv))
		$porc=100 - $total;
	else
		$porc=$porcentaje;
		
    include("../vista/auditoractividad/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detAudiActiv'){
    // proceso update a la base de datos usuarios
	
	$fecha=$_POST['fecha'];
	$id_auditor=$_POST['id_auditor'];
	$project_id=$_POST['project_id'];
        
	$id_actividad=$_POST['id_actividad'];
	$id_programa=$_POST['id_programa'];
	$id_pais=$_POST['id_pais'];
	$nota=f_limpiarcaracter($_POST['nota']);
	$porcentaje=$_POST['porcentaje'];
	$oferta=$_POST['oferta'];
	$flgfinalizo=$_POST['flgfinalizo'];
	$comentario=$_POST['comentario'];
	$ciclo=$_POST['ciclo'];
	$fecha_mc="";
	if(!empty($_POST['fecha_mc']))
		$fecha_mc=formatdatedos($_POST['fecha_mc']);

	$fechac="";
	if(!empty($_POST['fechac']))
		$fechac=formatdatedos($_POST['fechac']);
		
	
	if(empty($_POST['id_auditactiv']))
		$id_auditactiv=$auditoractividad->insert_auditActivi($comentario,$flgfinalizo,$oferta,$id_actividad,$id_programa,$nota,$porcentaje,$project_id,$id_auditor,$fecha,$fechac,$ciclo,$id_pais,$usuario_name,$ip,$sess_codpais,$fecha_mc);
	else{
		$id_auditactiv=$_POST['id_auditactiv']; // id
		$auditoractividad->update_auditoractividad($comentario,$id_auditactiv,$flgfinalizo,$oferta,$id_actividad,$id_programa,$nota,$porcentaje,$project_id,$id_auditor,$fecha,$fechac,$ciclo,$id_pais,$usuario_name,$ip,$fecha_mc);
	}	
	
	// regularizaciones
	$auditoractividad->regula_AudActi($id_auditactiv);
	
	if(!empty($_FILES)){
		$nombreFile=uploadFile($_FILES,$pathAudActi,'oferta_file');
		
		if(substr($nombreFile,0,5)!='Error')
			$auditoractividad->update_auditoractividad_file($nombreFile,$id_auditactiv);
	}
	 echo $id_auditactiv;
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delFileAudiActi'){
    // delete a la base de datos usuarios
	$id_auditactiv=$_POST['id_auditactiv']; 
    $auditoractividad->delete_fileAudActi($id_auditactiv);
    echo "Se elimino el archivo.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='delAudiActi'){
    // delete a la base de datos usuarios
	$id_auditactiv=$_POST['id_auditactiv']; 
    $auditoractividad->delete_AudActi($id_auditactiv);
    echo "Se elimino el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='mostrarControl'){
	$id_actividad=$_POST['id_actividad']; 
    $row=$actividad->select_actividad_flag($id_actividad);
	if(!empty($row))
        echo $row['flgproyecto']."*".$row['flgrendir'];

}else if(!empty($_POST['accion']) and $_POST['accion']=='mostrarPais'){
	$project_id=$_POST['project_id']; 
    $row=$proyectoactividad->selec_one_proyecto($project_id,$sess_codpais);
	if(!empty($row))
        echo $row['id_pais'];
}


?>
