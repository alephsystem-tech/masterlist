<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_calendario_modelo.php");
include("../modelo/prg_auditor_modelo.php");
include("../modelo/prg_pais_modelo.php");
include("../modelo/prg_feriado_modelo.php");
include("../modelo/prg_estadoactividad_modelo.php");

include("../modelo/prg_region_modelo.php");
include("../modelo/prg_programa_modelo.php");
include("../modelo/prg_tipoactividad_modelo.php");
include("../modelo/prg_proyecto_modelo.php");
include("../modelo/prg_estadoproyecto_modelo.php");


require_once "../lib/swift_required.php";
$transport = Swift_SmtpTransport::newInstance($server_mail, $puerto_mail);
//          ->setUsername($user_mail)
//          ->setPassword($clave_mail)
$mailer = Swift_Mailer::newInstance($transport); 

$calendario=new prg_calendario_model();
$auditor=new prg_auditor_model();
$pais=new prg_pais_model();
$feriado=new prg_feriado_model();
$estadoactividad=new prg_estadoactividad_model();

$prgregion=new prg_region_model();
$prgprograma=new prg_programa_model();
$prgtipoactividad=new prg_tipoactividad_model();
$prgproyecto=new prg_proyecto_model();	
$estadoproyecto=new prg_estadoproyecto_model();	

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];


$data_rolg=$auditor->selec_data_rol($sess_codauditor);
$ses_grol = explode(",", $data_rolg['grol']); // array

// roles con permisos
$array_rol_neces=array(1,10,11,24,2,8,17,21);
$array_rol_filtro=array(1,10,11,24,2,8,17);


$result_com=array_intersect($ses_grol,$array_rol_neces);
$flgchangeauditor=0;
if(count($result_com)>0){
	$flgchangeauditor=1;
}	


$array_rol_filtro_rend=array(1 ,11,17,21,5,2,9,20 );
$result_rendi=array_intersect($ses_grol,$array_rol_filtro_rend);
$flgchangeauditor_rend=0;
if(count($result_rendi)>0){
	$flgchangeauditor_rend=1;
}	



$result_com2=array_intersect($ses_grol,$array_rol_filtro);
$flgfiltroauditor=0;
if(count($result_com2)>0){
	$flgfiltroauditor=1;
}	

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

$pathCalendarioViatic = 'archivos/calendario/viatico/'; // upload directory
$valid_extensions = array('xls'); // valid extensions

	
$data_pais=$pais->selec_one_pais($sess_codpais);
$moneda_pais=$data_pais['monedaabv'];


//***********************************************
// index de aprobacion de vacaciones
//***********************************************

if(!empty($_POST['accion']) and $_POST['accion']=='programacion'){
	//**********************************
	// para aprovar las vacaciones
	//**********************************
    include("../vista/calendario/index_aprobacion.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_aprobacion_search'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$flag_rendicion = $_POST['flag_rendicion'];
	$descripcion = $_POST['descripcion'];
	if(!empty($_POST['fechaini']))
		$fechaini = formatdatedos($_POST['fechaini']);
	$fechafin="";
	if(!empty($_POST['fechafin']))
		$fechafin = formatdatedos($_POST['fechafin']);
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" id_proyecto";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and Calendario.id_pais='$sess_codpais' AND Calendario.id_tipoactividad in ( 3,258,233)";
	
		
	## Total number of records without filtering
	$data_maxOF=$calendario->selec_total_calendario_vacacion($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if($flag_rendicion)
		$searchQuery.=" AND Calendario.flag_rendicion= '$flag_rendicion' ";
	if($fechaini)
		$searchQuery.=" AND TO_DAYS(Calendario.inicio_evento)>= TO_DAYS('$fechaini') ";
	if($fechafin)
		$searchQuery.=" AND TO_DAYS(Calendario.inicio_evento)<= TO_DAYS('$fechafin') ";
						
	if(!empty($descripcion))
		$searchQuery.=" and ( Auditor.nombre like '%$descripcion%' or Auditor.apepaterno like '%$descripcion%' 
					or  Calendario.observacion like '%$descripcion%') ";
					
	## Total number of record with filtering
	$data_maxOF2=$calendario->selec_total_calendario_vacacion($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$calendario->select_calendario_vacacion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
			$id=$row['id'];
			$edita="<button type='button' id='estproy_". $id ."_".$row['flag_rendicion']."'  class='btn  btn_okVacacion'><i class='fas fa-edit'></i> </button>";
			
			if($row['flag_rendicion']==3) $estado="<font color=blue>$row[estado]</font>";
			else $estado=$row['estado'];
			
			$data[] = array( 
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			   "auditor"=>str_replace('"','',json_encode($row['auditor'],JSON_UNESCAPED_UNICODE)),
			   "observacion"=>str_replace('"','',json_encode($row['observacion'],JSON_UNESCAPED_UNICODE)),
			   "estado"=>$estado,
			   "id"=>$id,
			   "fec_aprueba"=>$row['fec_aprueba'],
			   "rend_usuario_aprobado"=>$row['rend_usuario_aprobado'],
			   "fec_inicio"=>$row['fec_inicio'],
			   "fec_final"=>$row['fec_final'],
			   "edita"=>$edita
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

		 
//***********************************************
// accion de aprobar / rendir vacaciones
//***********************************************

}else if(!empty($_POST['accion']) and $_POST['accion']=='expVacacionCalexp'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$flag_rendicion = $_POST['flag_rendicion'];
	$descripcion = $_POST['descripcion'];
	if(!empty($_POST['fechaini']))
		$fechaini = formatdatedos($_POST['fechaini']);
	$fechafin="";
	if(!empty($_POST['fechafin']))
		$fechafin = formatdatedos($_POST['fechafin']);
	
	$row = 0;
	$rowperpage = 1000000; // Rows display per page
	
	
	$columnName=" id_proyecto";
	$columnSortOrder=" desc ";
	
	## Search  oculto
	$searchQuery = " and Calendario.id_pais='$sess_codpais' AND Calendario.id_tipoactividad in ( 3,258,233)";
	
		
	
	if($flag_rendicion)
		$searchQuery.=" AND Calendario.flag_rendicion= '$flag_rendicion' ";
	if($fechaini)
		$searchQuery.=" AND TO_DAYS(Calendario.inicio_evento)>= TO_DAYS('$fechaini') ";
	if($fechafin)
		$searchQuery.=" AND TO_DAYS(Calendario.inicio_evento)<= TO_DAYS('$fechafin') ";
						
	if(!empty($descripcion))
		$searchQuery.=" and ( Auditor.nombre like '%$descripcion%' or Auditor.apepaterno like '%$descripcion%' 
					or  Calendario.observacion like '%$descripcion%') ";
					
	## Fetch records
	$data_OF=$calendario->select_calendario_vacacion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	include("../vista/calendario/vacacion_exporta.php");	
		 
//***********************************************
// accion de aprobar / rendir vacaciones
//***********************************************


}else if(!empty($_POST['accion']) and $_POST['accion']=='estadoVacacion'){
    // proceso update a la base de datos usuarios
	$flag_rendicion=$_POST['flag_rendicion'];
	$id=$_POST['id'];
	$fechahora=$_POST['fechahora'];
	if($flag_rendicion==1) $flag_rendicion=3;
	else if($flag_rendicion==3) $flag_rendicion=1;
		
		$calendario->update_calendario_vacacion($id,$flag_rendicion,$sess_codpais,$fechahora,$usuario_name,$ip);
	
	if($flag_rendicion==3 and ($sess_codpais=='ess' or $sess_codpais=='POR')){
		// se aprobo vacacion
		
		$data_rend=$calendario->selec_one_calendario_complex($id);
		if(!empty($data_rend)){
			$id_auditor=$data_rend['id_auditor'];
			$auditor_res=$auditor->select_one_auditorSimpl($id_auditor);
			$auditorMail.=$auditor_res['email'];
			$nombres=$auditor_res['nombres'];
		}
		
		// el que aprueba
		
		$arpueba_res=$auditor->select_one_auditorSimpl($sess_codauditor);
		if(!empty($arpueba_res)){
			$auditorMail.=",".$arpueba_res['email'];
			$apruebanombres=$arpueba_res['nombres'];
		}
		
		if($sess_codpais=='POR'){
			$asunto="APROVACAO DE FERIAS";
			$body="$nombres <br><br>
			Suas feiras solicitadas de $data_rend[fechai] a $data_rend[fechaf] foram aprovadas por $apruebanombres  dia $data_rend[rend_fecha_aprobadof].<br><br>
			Com os melhores cumprimentos,<br>
			CONTROL UNION PORTUGAL";
			$auditorMail.=",portugal.contabilidade@controlunion.com, masterplanner@controlunion.com";
		}else{	
			$asunto="Aprobacion de vacaciones";
			$body="Buenas $nombres <br><br>
				Tus vacaciones solicitadas dede el dia $data_rend[fechai] hasta el $data_rend[fechaf] fueron aprobadas por $apruebanombres  el dia $data_rend[rend_fecha_aprobadof].<br><br>
				Saludos,<br>
				CONTROL UNION ESPA&Ntilde;A";
			$auditorMail.=",recursoshumanos.espana@controlunion.com,masterplanner@controlunion.com";	
		}
		
		if(!empty($auditorMail)){
			$message = Swift_Message::newInstance($asunto)
				->setFrom(array($user_mail =>  $name_mail))
				->setTo(explode(",",$auditorMail))
				->setBody($body, 'text/html', 'iso-8859-2')
			;
			$numSent = $mailer->send($message);
			printf("Enviado: %d mensajes a $auditorMail<br>", $numSent);
		}
	}
	
	echo "Se actualizo el registro";


//***********************************************
//***********************************************
// index de modulo de rendiciones
//***********************************************

}else if(!empty($_POST['accion']) and $_POST['accion']=='rendicion'){
	
	// $array_rol_filtro_rend;
	$auditor_res=$auditor->select_auditorByID(0,$sess_codpais);
	
	if($sess_codpais=='bra') $mon='REA';
	else $mon='S/.';
	
    include("../vista/calendario/index_rendicion.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_rendicion_search'){

	## Read value
	$descripcion = $_POST['descripcion'];
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$estado_rendicion = $_POST['estado_rendicion'];
	$facturado = $_POST['facturado'];
	$fechaini="";
	$fechafin="";
	
	if(!empty($_POST['fechaini']))
		$fechaini = formatdatedos($_POST['fechaini']);
	
	if(!empty($_POST['fechafin']))
		$fechafin = formatdatedos($_POST['fechafin']);
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" Calendario.inicio_evento ";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto // and Calendario.id_estadoactividad <> 3 
	$searchQuery = " AND Calendario.id_pais = '$sess_codpais' 
		AND Calendario.flag_rendicion <> '3'
		and  ( ifnull(Calendario.monto_dolares,0) >0 or ifnull(Calendario.monto_soles,0) >0)";
	
	## Total number of records without filtering
	$data_maxOF=$calendario->selec_total_calendario_rendicion($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if($fechaini)
		$searchQuery.=" AND TO_DAYS(Calendario.inicio_evento)>= TO_DAYS('$fechaini') ";
	
	if($fechafin)
		$searchQuery.=" AND TO_DAYS(Calendario.inicio_evento)<= TO_DAYS('$fechafin') ";
						
	if(!empty($descripcion))
		$searchQuery.=" and ( Calendario.asunto like '%$descripcion%' or  Calendario.observacion_facturado like '%$descripcion%') ";
	
	if(!empty($proyecto))
		$searchQuery.=" and ( Proyecto.project_id like '%$proyecto%' or  Proyecto.proyect  like '%$proyecto%') ";
	
	if(!empty($id_auditor))
		$searchQuery.=" and  Calendario.id_auditor =$id_auditor ";
	
	if(!empty($estado_rendicion))
		$searchQuery.=" and  Calendario.flag_rendicion =$estado_rendicion ";
	
	if(!empty($facturado) and $facturado=='s')
		$searchQuery.=" and  Calendario.is_facturado ='s' ";
	
	else if(!empty($facturado) and $facturado=='n')
		$searchQuery.=" and  ifnull(Calendario.is_facturado,'n') ='n' ";
	
	
	## Total number of record with filtering
	$data_maxOF2=$calendario->selec_total_calendario_rendicion($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$calendario->select_calendario_rendicion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
			$id=$row['id'];
			$edita="<button type='button' id='estproy_". $id ."_".$row['flag_rendicion']."'  class='btn  btn_edtRendicion'><i class='fas fa-edit'></i> </button>";
			
			if($row['nro_factura']!='') $factura='Nro. '.str_replace('"','',json_encode($row['nro_factura'],JSON_UNESCAPED_UNICODE));
			else if($row['flag_rendicion']==2) $factura="<input type='checkbox' name='facturar' value=$id id='facturar_$id'>";
			else $factura="";
			
			$data[] = array( 
			   "asunto"=>str_replace('"','',json_encode($row['asunto'],JSON_UNESCAPED_UNICODE)),
			    "actividad"=>str_replace('"','',json_encode($row['actividad'],JSON_UNESCAPED_UNICODE)),
			   "fullname"=>str_replace('"','',json_encode($row['fullname'],JSON_UNESCAPED_UNICODE)),
			   "nro_factura"=>$factura,
			   "monto_soles"=>$row['monto_soles'],
			   "monto_dolares"=>$row['monto_dolares'],
			   "fecha_rendicion"=>$row['fecha_rendicion'],
			   "rendicion"=>$row['rendicion'],
			   "id"=>$id,
			   "fecha_inicio_evento"=>$row['fecha_inicio_evento'],
			    "proyect"=>$row['project_id'] ." ".$row['proyect'],
				"programa"=>$row['programa'],
			   "fecha_fin_evento"=>$row['fecha_fin_evento'],
			   "edita"=>$edita
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


}else if(!empty($_POST['accion']) and $_POST['accion']=='expRendicionbase'){	

	$descripcion = $_POST['descripcion'];
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$estado_rendicion = $_POST['estado_rendicion'];
	$facturado = $_POST['facturado'];
	$fechaini="";
	$fechafin="";
	
	if($sess_codpais=='bra') $mon='REA';
	else $mon='S/.';
	
	
	if(!empty($_POST['fechaini']))
		$fechaini = formatdatedos($_POST['fechaini']);
	
	if(!empty($_POST['fechafin']))
		$fechafin = formatdatedos($_POST['fechafin']);
		
	$columnName=" Calendario.inicio_evento ";
	$columnSortOrder=" desc ";
	
	## Search  oculto // and Calendario.id_estadoactividad <> 3 
	$searchQuery = " AND Calendario.id_pais = '$sess_codpais' 
		AND Calendario.flag_rendicion <> '3'
		and  ( ifnull(Calendario.monto_dolares,0) >0 or ifnull(Calendario.monto_soles,0) >0)";
	
	if($fechaini)
		$searchQuery.=" AND TO_DAYS(Calendario.inicio_evento)>= TO_DAYS('$fechaini') ";
	
	if($fechafin)
		$searchQuery.=" AND TO_DAYS(Calendario.inicio_evento)<= TO_DAYS('$fechafin') ";
						
	if(!empty($descripcion))
		$searchQuery.=" and ( Calendario.asunto like '%$descripcion%' or  Calendario.observacion_facturado like '%$descripcion%') ";
	
	if(!empty($proyecto))
		$searchQuery.=" and ( Proyecto.project_id like '%$proyecto%' or  Proyecto.proyect  like '%$proyecto%') ";
	
	if(!empty($id_auditor))
		$searchQuery.=" and  Calendario.id_auditor =$id_auditor ";
	
	if(!empty($estado_rendicion))
		$searchQuery.=" and  Calendario.flag_rendicion =$estado_rendicion ";
	
	if(!empty($facturado) and $facturado=='s')
		$searchQuery.=" and  Calendario.is_facturado ='s' ";
	
	else if(!empty($facturado) and $facturado=='n')
		$searchQuery.=" and  ifnull(Calendario.is_facturado,'n') ='n' ";
	
	
	## Fetch records
	$row=0;
	$rowperpage=100000;
	$data_OF=$calendario->select_calendario_rendicion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);

	include("../vista/calendario/rendicionbase_exporta.php");
	
//***********************************************
// exportar excel la rendiciones
//***********************************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='expRendicionCal'){	 
	
	$descripcion = $_POST['descripcion'];
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$flag_rendicion = $_POST['flag_rendicion'];
	$estado_rendicion = $_POST['estado_rendicion'];
	
	if(!empty($_POST['id_auditor'])){
		$id_auditor=$_POST['id_auditor'];
		$auditor_res=$auditor->select_one_auditorSimpl($id_auditor);
		$auditorNombre=$auditor_res['nombres'];
	}
	
	if($estado_rendicion==1) $estado='Entregado';
	else if($estado_rendicion==2) $estado='Rendido';
	else if($estado_rendicion==4) $estado='Desaprobado';
	
	$facturado = $_POST['facturado'];
		
	if(!empty($_POST['fechaini'])){
		$fechainif = $_POST['fechaini'];
		$fechaini = formatdatedos($_POST['fechaini']);
	}	
	
	if(!empty($_POST['fechafin'])){
		$fechafinf = $_POST['fechafin'];
		$fechafin = formatdatedos($_POST['fechafin']);
	}	
	
	$searchQuery = " and Calendario.id_estadoactividad <> 3 AND Calendario.id_pais = '$sess_codpais' ";
	
	if($flag_rendicion)
		$searchQuery.=" AND flag_rendicion= $flag_rendicion ";
	
	if($fechaini)
		$searchQuery.=" AND TO_DAYS(Calendario.inicio_evento)>= TO_DAYS('$fechaini') ";
	if($fechafin)
		$searchQuery.=" AND TO_DAYS(Calendario.inicio_evento)<= TO_DAYS('$fechafin') ";
						
	if(!empty($descripcion))
		$searchQuery.=" and ( Calendario.asunto like '%$descripcion%' or  Calendario.observacion_facturado like '%$descripcion%') ";
	
	if(!empty($proyecto))
		$searchQuery.=" and ( Proyecto.project_id like '%$proyecto%' or  Proyecto.proyect  like '%$proyecto%') ";
	
	if(!empty($id_auditor))
		$searchQuery.=" and  Calendario.id_auditor =$id_auditor ";
	
	if(!empty($estado_rendicion))
		$searchQuery.=" and  Calendario.flag_rendicion =$estado_rendicion ";
	
	if(!empty($facturado) and $facturado=='s')
		$searchQuery.=" and  Calendario.is_facturado ='s' ";
	
	else if(!empty($facturado) and $facturado=='n')
		$searchQuery.=" and  ifnull(Calendario.is_facturado,'') ='' ";
	
	$columnName=" Calendario.inicio_evento ";
	$columnSortOrder=" desc ";
	$row=0;
	$rowperpage=100000;
	$data_OF=$calendario->select_calendario_rendicion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	include("../vista/calendario/rendicion_exporta.php");


//***********************************************
// editar una rendicion
//***********************************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='ediRendicion'){
	$data_pais=$pais->selec_one_pais($sess_codpais);
	$moneda=$data_pais['monedaabv'];
	$id = $_POST['id'];
	$data_rend=$calendario->selec_one_calendario_complex($id);
	$data_res=$calendario->selec_detalle_viatico($id);
	
    include("../vista/calendario/detalle_rendicion.php");

//***********************************************
// abrir para agregar/editar un viatico de una rendicion
//***********************************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='setRendicion'){
	//**********************************
	// para aprobar las vacaciones
	//**********************************
	$id_calendario = $_POST['id'];
	$tipo = $_POST['tipo'];
	$moneda = $_POST['moneda'];
	
	if($tipo=='HOSP') $dsctipo='Hospedaje' ;
	else if($tipo=='MOVI') $dsctipo= 'Movilidad';
	else if($tipo=='ALIM') $dsctipo= 'Alimentaci&oacute;n';
	else if($tipo=='CONT') $dsctipo= 'Contingencia' ;
	
	if(!empty($_POST['id_detalle'])){
		$id_detalle=$_POST['id_detalle'];
		$data_res=$calendario->selec_one_detalle_viatico($id_calendario,$id_detalle);
	}	
	include("../vista/calendario/form_rendicion.php");

//***********************************************
// procesar agregar/editar un viatico de una rendicion
//***********************************************
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_setRendicion'){	
	$id_calendario = $_POST['id_calendario'];
	$tipo = $_POST['tipo'];
	$moneda = $_POST['moneda'];
	$descripcion = $_POST['descripcion'];
	$monto = $_POST['monto'];
	if(!empty($_POST['fecha']))
		$fecha = formatdatedos($_POST['fecha']);
	
	$observacion = $_POST['observacion'];
	
	if(!empty($_POST['id_detalle'])){
		$id_detalle=$_POST['id_detalle'];
		$calendario->update_detalle_viatico($id_detalle,$id_calendario,$tipo,$moneda,$descripcion,$monto,$fecha,$observacion,$usuario_name,$sess_codpais,$ip);
	}else{
		$id_detalle=$calendario->insert_detalle_viatico($id_calendario,$tipo,$moneda,$descripcion,$monto,$fecha,$observacion,$usuario_name,$sess_codpais,$ip);
	}	
	
	/*
	SUBIR FILE
	*/
	$filenombre=uploadFile($_FILES,$pathCalendarioViatic,'adjunto');
	if(substr($foto, 0,5)!='Error' )
		$calendario->update_detalle_viaticoFile($id_detalle,$id_calendario,$filenombre);
	
	$calendario->regula_detalle_viatico($id_calendario);
	echo "Se actualizo el registro";

//***********************************************
// eliminar agregar/editar un viatico que pertenece a una rendicion
//***********************************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='delViatico'){	
	$id_calendario = $_POST['id_calendario'];
	$id_detalle=$_POST['id_detalle'];
	$calendario->delete_detalle_viatico($id_detalle,$id_calendario,$usuario_name,$sess_codpais,$ip);
	
	$calendario->regula_detalle_viatico($id_calendario);
	echo "Se elimino el registro";	

}else if(!empty($_POST['accion']) and $_POST['accion']=='setRendido'){	
	$id_calendario = $_POST['id_calendario'];
	$flag_rendicion = $_POST['flag_rendicion'];
	$calendario->rendir_calendario($id_calendario,$flag_rendicion,$usuario_name,$sess_codpais,$ip);
	echo "Se actualizo el registro";	

}else if(!empty($_POST['accion']) and $_POST['accion']=='setFacturar'){	
	$cadena = $_POST['cadena'];
	$tipo = $_POST['tipo'];
	if($tipo="u"){
		
		$row=$calendario->selec_one_calendario($cadena);
		$facturadof=$row['is_facturado'];
		$nrofacturaf=$row['nro_factura'];
		$observacion=$row['observacion_facturado'];
	}
	include("../vista/calendario/form_facturar.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_setFacturar'){	
	$cadena = $_POST['cadena'];
	$facturado = $_POST['facturadof'];
	$nrofactura=$_POST['nrofacturaf'];
	$observacion=$_POST['observacion'];
	
	if(!empty($_POST['id_calendario']))
		$cadena=$_POST['id_calendario'];
		
	$calendario->facturar_calendario($observacion,$cadena,$facturado,$nrofactura);
	echo "Se facturo el registro";	


//***********************************************
//***********************************************
// index de modulo de rendiciones aprobar
//***********************************************

}else if(!empty($_POST['accion']) and $_POST['accion']=='aprobar'){
	
	$auditor_res=$auditor->select_auditorByID(0,$sess_codpais);
	if($sess_codpais=='bra') $mon='REA';
	else $mon='S/.';
    include("../vista/calendario/index_rendicion_aprobar.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_rendicion_aprob_search'){

	## Read value
	$descripcion = $_POST['descripcion'];
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$estado_rendicion = $_POST['estado_rendicion'];
	$facturado = $_POST['facturado'];
	$fechaini="";
	$fechafin="";
	
	if(!empty($_POST['fechaini']))
		$fechaini = formatdatedos($_POST['fechaini']);
	
	if(!empty($_POST['fechafin']))
		$fechafin = formatdatedos($_POST['fechafin']);
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" Calendario.inicio_evento ";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and Calendario.id_estadoactividad <> 3 AND Calendario.id_pais = '$sess_codpais' 
		AND Calendario.flag_rendicion <> '3'
		and  ( ifnull(Calendario.monto_dolares,0) >0 or ifnull(Calendario.monto_soles,0) >0)";
	
	## Total number of records without filtering
	$data_maxOF=$calendario->selec_total_calendario_rendicion($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if($fechaini)
		$searchQuery.=" AND TO_DAYS(Calendario.inicio_evento)>= TO_DAYS('$fechaini') ";
	if($fechafin)
		$searchQuery.=" AND TO_DAYS(Calendario.inicio_evento)<= TO_DAYS('$fechafin') ";
						
	if(!empty($descripcion))
		$searchQuery.=" and ( Calendario.asunto like '%$descripcion%' or  Calendario.observacion_facturado like '%$descripcion%') ";
	
	if(!empty($proyecto))
		$searchQuery.=" and ( Proyecto.project_id like '%$proyecto%' or  Proyecto.proyect  like '%$proyecto%') ";
	
	if(!empty($id_auditor))
		$searchQuery.=" and  Calendario.id_auditor =$id_auditor ";
	
	if(!empty($estado_rendicion))
		$searchQuery.=" and  Calendario.flag_rendicion =$estado_rendicion ";
	
	if(!empty($facturado) and $facturado=='s')
		$searchQuery.=" and  Calendario.is_facturado ='s' ";
	
	else if(!empty($facturado) and $facturado=='n')
		$searchQuery.=" and  ifnull(Calendario.is_facturado,'') ='' ";
	
	
	## Total number of record with filtering
	$data_maxOF2=$calendario->selec_total_calendario_rendicion($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$calendario->select_calendario_rendicion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
			$id=$row['id'];
			$edita="";
			if($row['is_facturado']=='s')
				$edita="<button type='button' id='isfac_". $id ."_".$row['is_facturado']."_'  class='btn  btn_edtRendicionApob'><i class='fas fa-edit'></i> </button>";
			
			if($row['nro_factura']!='') $factura='Nro. '.str_replace('"','',json_encode($row['nro_factura'],JSON_UNESCAPED_UNICODE));
			else if($row['flag_rendicion']==2) $factura="<input type='checkbox' name='facturar' value=$id id='facturar_$id'>";
			else $factura="";
			
			$data[] = array( 
			   "asunto"=>str_replace('"','',json_encode($row['asunto'],JSON_UNESCAPED_UNICODE)),
			   "fullname"=>str_replace('"','',json_encode($row['fullname'],JSON_UNESCAPED_UNICODE)),
			    "actividad"=>str_replace('"','',json_encode($row['actividad'],JSON_UNESCAPED_UNICODE)),
			   "nro_factura"=>$factura,
			   "monto_soles"=>$row['monto_soles'],
			   "monto_dolares"=>$row['monto_dolares'],
			   "fecha_rendicion"=>$row['fecha_rendicion'],
			   "rendicion"=>$row['rendicion'],
			   "id"=>$id,
			   "fecha_inicio_evento"=>$row['fecha_inicio_evento'],
			   "fecha_fin_evento"=>$row['fecha_fin_evento'],
			    "programa"=>$row['programa'],
				"proyect"=>$row['project_id'] ." ".$row['proyect'],
			   "edita"=>$edita,
			   "asignacion_viaticos"=>$row['asignacion_viaticos']
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

//***********************************************
// exportar excel la rendiciones
//***********************************************

//***********************************************
//***********************************************
// index de modulo de rendiciones cerrar
//***********************************************

}else if(!empty($_POST['accion']) and $_POST['accion']=='cerrar'){
	
	$auditor_res=$auditor->select_auditorByID(0,$sess_codpais);
	$arrayMedio=$calendario->selec_medio_transporte($sess_codpais);
	
	if($sess_codpais=='bra') $mon='REA';
	else $mon='S/.';
	
	if($sess_codpais=='bra') include("../vista/calendario/index_cerrar_bra.php");	
	else include("../vista/calendario/index_cerrar.php");	
    
	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_cerrar_search'){

	$arrayMedio=$calendario->selec_medio_transporte($sess_codpais);
	
	## Read value costoUSD_
	$descripcion = $_POST['descripcion'];
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$estado_rendicion = $_POST['estado_rendicion'];
	$facturado = $_POST['facturado'];
	$fechaini="";
	$fechafin="";
	
	if(!empty($_POST['fechaini']))
		$fechaini = formatdatedos($_POST['fechaini']);
	
	if(!empty($_POST['fechafin']))
		$fechafin = formatdatedos($_POST['fechafin']);
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" inicio_evento ";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " AND c.id_estadoactividad!=3  AND c.id_pais = '$sess_codpais'
			AND p.id_pais = '$sess_codpais' ";
			
	if($sess_codpais=='esp')
		$searchQuery.=	" and  ( ifnull(c.monto_dolares,0) >0 or ifnull(c.monto_soles,0) >0 or ifnull(med.monto,0)>0 ) "; 
	// 
	
	## Total number of records without filtering
	$data_maxOF=$calendario->selec_total_calendario_cerrar($searchQuery);
	$totalRecords = $data_maxOF['total'];

	
	if(!empty($proyecto))
		$searchQuery.=" and ( p.project_id like '%$proyecto%' or  p.proyect  like '%$proyecto%') ";
	
	if(!empty($id_auditor))
		$searchQuery.=" and  c.id_auditor =$id_auditor ";
	
	if(!empty($estado_rendicion))
		$searchQuery.=" and  flag_rendicion =$estado_rendicion ";
	
	if(!empty($facturado) and $facturado=='s')
		$searchQuery.=" and  is_facturado ='s' ";
	
	else if(!empty($facturado) and $facturado=='n')
		$searchQuery.=" and  ifnull(is_facturado,'') ='' ";
	
	
	if($fechaini)
		$searchQuery.=" AND TO_DAYS(inicio_evento)>= TO_DAYS('$fechaini') ";
	if($fechafin)
		$searchQuery.=" AND TO_DAYS(inicio_evento)<= TO_DAYS('$fechafin') ";
						
	if(!empty($descripcion))
		$searchQuery.=" and (monto_dolares<= $descripcion or monto_soles<=$descripcion)";
	
	## Total number of record with filtering
	$data_maxOF2=$calendario->selec_total_calendario_cerrar($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$calendario->select_calendario_cerrar($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais);
	
	$arrayValor=$calendario->select_calendario_detalle($proyecto,$fechaini,$fechafin, $estado_rendicion,$facturado,$descripcion,$sess_codpais);
	if(!empty($arrayValor)){
		foreach($arrayValor as $row){
			$sufi=$row['id']."_".$row['id_mediotransporte'];
			$arrayData[$sufi]=$row;
		}
	}
	
	
	$arrayValor=$calendario->selec_grupoviatico_calendario();
	if(!empty($arrayValor)){
		foreach($arrayValor as $row){
			$arrayData[$row['tipo_moneda'].$row['tipo'].$row['id_calendario']]=$row['monto'];
		}
	}
	// arrayMedio

    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
			$id=$row['id'];
			$is_facturado=$row['is_facturado'];
			
			if($sess_codpais=='bra'){
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_printRendicion'><i class='fas fa-print'></i> </button>";
			}else{
				$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_printRendicion'><i class='fas fa-print'></i> </button>";
				$edita .="<button type='button' id='estproy_". $id ."'  class='btn  btn_printRendicionAuditoria'><i class='fas fa-print'></i> </button>";
			
			}
			$adjunto="";
			if($row['adjunto']!=''){
				foreach(explode(',',$row['adjunto']) as $file){ 
					if($file!=''){
						$adjunto.="&nbsp;<a href='#'><img onclick=javascript:js_download('".str_replace('/','&&',$pathCalendarioViatic).$file."') ";
						$adjunto.=" src='assets/img/btn_ver.png' border=0 title='".$file."' alt='".$file."'></a>";
					} 
				}
			}
			
		
			if($estado_rendicion==1 or $estado_rendicion==2 or ($estado_rendicion==3 and $is_facturado!='s' and $is_facturado!='i'))
				$accion="<input type='checkbox' name='facturar' value=$id id='facturar_$id'>";
			else $accion="";
		
		
		
			if(empty($arrayData['SMOVI'.$id]))
				$arrayData['SMOVI'.$id]="";
			if(empty($arrayData['SALIM'.$id]))
				$arrayData['SALIM'.$id]="";
			if(empty($arrayData['SHOSP'.$id]))
				$arrayData['SHOSP'.$id]="";
			if(empty($arrayData['SCONT'.$id]))
				$arrayData['SCONT'.$id]="";
			if(empty($arrayData['USDMOVI'.$id]))
				$arrayData['USDMOVI'.$id]="";
			if(empty($arrayData['USDALIM'.$id]))
				$arrayData['USDALIM'.$id]="";
			if(empty($arrayData['USDHOSP'.$id]))
				$arrayData['USDHOSP'.$id]="";
			if(empty($arrayData['USDCONT'.$id]))
				$arrayData['USDCONT'.$id]="";
			   
			$utilizado_monto_dolares_scontingencia = (float) $row['utizado_monto_dolares'] - (float)$arrayData['USDCONT'.$id]; 
			$utilizado_monto_soles_scontingencia = (float) $row['utizado_monto_soles'] - (float)$arrayData['SCONT'.$id]; 

			$array = array( 
			   "accion"=>$accion,
			   "project_id"=>str_replace('"','',json_encode($row['project_id']." ".$row['proyect'],JSON_UNESCAPED_UNICODE)),
			   "programas"=>str_replace('"','',json_encode($row['programas'],JSON_UNESCAPED_UNICODE)),
			   "proyect"=>str_replace('"','',json_encode($row['proyect'],JSON_UNESCAPED_UNICODE)),
			   "region"=>str_replace('"','',json_encode($row['region'],JSON_UNESCAPED_UNICODE)),
			   "auditor"=>str_replace('"','',json_encode($row['auditor'],JSON_UNESCAPED_UNICODE)),
			   "estadoplani"=>str_replace('"','',json_encode($row['estadoplani'],JSON_UNESCAPED_UNICODE)),
			   
			   "id"=>$id,
			   
			   "monto_dolares"=>$row['monto_dolares'],
			   "utizado_monto_dolares"=>$row['utizado_monto_dolares'],
			   "utizado_monto_dolares_scontingencia"=>$utilizado_monto_dolares_scontingencia, //Creado solo para Perú
			   
			   
			   "MOVI_soles"=>$arrayData['SMOVI'.$id],
			   "ALIM_soles"=>$arrayData['SALIM'.$id],
			   "HOSP_soles"=>$arrayData['SHOSP'.$id],
			   "CONT_soles"=>$arrayData['SCONT'.$id],
			   "MOVI_dolar"=>$arrayData['USDMOVI'.$id],
			   "ALIM_dolar"=>$arrayData['USDALIM'.$id],
			   "HOSP_dolar"=>$arrayData['USDHOSP'.$id],
			   "CONT_dolar"=>$arrayData['USDCONT'.$id],
			   
			   "monto_soles"=>$row['monto_soles'],
			   "utizado_monto_soles"=>$row['utizado_monto_soles'],
			   "utizado_monto_soles_scontingencia"=>$utilizado_monto_soles_scontingencia, //Creado solo para Perú

			   "fecha_inicio_evento"=>$row['fecha_inicio_evento'],
			   "fecha_fin_evento"=>$row['fecha_fin_evento'],
			   "fecha_rendicion"=>$row['fecha_rendicion'],
			   "tipoactividad"=>$row['tipoactividad'],
			   "estado"=>$row['estado'],
			   "facturado"=>$row['facturado'],
			   "edita"=>$edita,
			   "adjunto"=>$adjunto,
			 );  
			
			foreach($arrayMedio as $row2){
				$idmedio=$row2['id_mediotransporte'];  
				
				$sufi=$id."_".$idmedio;
				$data1="0";
				$data2="0";
				$data3="0";
				$data4="0";
				
				if(!empty($arrayData[$sufi])){
					$data1=$arrayData[$sufi]['monto_dolares'];
					$data2=$arrayData[$sufi]['penalidad_dolares'];
					$data3=$arrayData[$sufi]['monto_soles'];
					$data4=$arrayData[$sufi]['penalidad_soles'];
				}
					
				$array2=array(
				   "costoUSD_$idmedio"=>$data1,
				   "auditorUSD_$idmedio"=>$data2,
				   "costoS_$idmedio"=>$data3,
				   "auditorS_$idmedio"=>$data4,
				); 
				$array=array_merge($array, $array2);
			}  
			 
		   $data[] =$array;
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
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='setAprobar'){	
	$cadena = $_POST['cadena'];
	$flag_rendicion = $_POST['flag_rendicion'];
	$fechahora=date('y/m/d H:m:s' );
	$calendario->update_calendario_vacacion($cadena,$flag_rendicion,$sess_codpais,$fechahora,$usuario_name,$ip);
	echo "Se actualizo el registro";	

}else if(!empty($_POST['accion']) and $_POST['accion']=='setInterno'){	
	$cadena = $_POST['cadena'];
	$is_facturado = $_POST['is_facturado'];
	$calendario->update_calendario_interno($cadena,$is_facturado,$sess_codpais,$usuario_name,$ip);
	echo "Se actualizo el registro";	

}else if(!empty($_POST['accion']) and $_POST['accion']=='expRendicionCerrar'){	 
	
	// $arrayMedio=$calendario->selec_medio_transporte($sess_codpais);
	
	$descripcion = $_POST['descripcion'];
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$estado_rendicion = $_POST['estado_rendicion'];
	
	if(!empty($_POST['id_auditor'])){
		$id_auditor=$_POST['id_auditor'];
		$auditor_res=$auditor->select_one_auditorSimpl($id_auditor);
		$auditorNombre=$auditor_res['nombres'];
	}
	
	if($estado_rendicion==1) $estado='Entregado';
	else if($estado_rendicion==2) $estado='Rendido';
	else if($estado_rendicion==3) $estado='Aprobado';
	else if($estado_rendicion==4) $estado='Desaprobado';
	else if($estado_rendicion==5) $estado='Cerrado';
	
	$facturado = $_POST['facturado'];
		
	if(!empty($_POST['fechaini'])){
		$fechainif = $_POST['fechaini'];
		$fechaini = formatdatedos($_POST['fechaini']);
	}	
	
	if(!empty($_POST['fechafin'])){
		$fechafinf = $_POST['fechafin'];
		$fechafin = formatdatedos($_POST['fechafin']);
	}	
	
	$arrayValor=$calendario->selec_grupoviatico_calendario();
	if(!empty($arrayValor)){
		foreach($arrayValor as $row){
			$arrayData[$row['tipo_moneda'].$row['tipo'].$row['id_calendario']]=$row['monto'];
		}
	}
	//arrayData
	$arrayValor=$calendario->select_calendario_detalle($proyecto,$fechaini,$fechafin, $estado_rendicion,$facturado,$descripcion,$sess_codpais);
	if(!empty($arrayValor)){
		foreach($arrayValor as $row){
			$sufi=$row['id']."_".$row['id_mediotransporte'];
			$arrayData[$sufi]=$row;
		}
	}
	
	$searchQuery = " AND c.id_estadoactividad!=3  AND c.id_pais = '$sess_codpais' "; 
	
	if($sess_codpais=='esp')
		$searchQuery.= " and  ( ifnull(c.monto_dolares,0) >0 or ifnull(c.monto_soles,0) >0 or ifnull(med.monto,0)>0 )";
	
	if(!empty($proyecto))
		$searchQuery.=" and ( p.project_id like '%$proyecto%' or  p.proyect  like '%$proyecto%') ";
	
	if(!empty($id_auditor))
		$searchQuery.=" and  c.id_auditor =$id_auditor ";
	
	if(!empty($estado_rendicion))
		$searchQuery.=" and  flag_rendicion =$estado_rendicion ";
	
	if(!empty($facturado) and $facturado=='s')
		$searchQuery.=" and  is_facturado ='s' ";
	
	else if(!empty($facturado) and $facturado=='n')
		$searchQuery.=" and  ifnull(is_facturado,'') ='' ";
	
	
	if($fechaini)
		$searchQuery.=" AND TO_DAYS(inicio_evento)>= TO_DAYS('$fechaini') ";
	if($fechafin)
		$searchQuery.=" AND TO_DAYS(inicio_evento)<= TO_DAYS('$fechafin') ";
						
	if(!empty($descripcion))
		$searchQuery.=" and (monto_dolares<= $descripcion or monto_soles<=$descripcion)";
	
	$columnName=" inicio_evento ";
	$columnSortOrder=" desc ";
	$row=0;
	$rowperpage=100000;
	$data_OF=$calendario->select_calendario_cerrar($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais);
	
	if($sess_codpais=='bra') include("../vista/calendario/rendicionCerrar_exporta_bra.php");
	else include("../vista/calendario/rendicionCerrar_exporta.php");
	

}else if(!empty($_POST['accion']) and $_POST['accion']=='expRendicionCerrar2'){	 
	
	// $arrayMedio=$calendario->selec_medio_transporte($sess_codpais);
	
	$descripcion = $_POST['descripcion'];
	$proyecto = $_POST['proyecto'];
	$id_auditor = $_POST['id_auditor'];
	$estado_rendicion = $_POST['estado_rendicion'];
	
	if(!empty($_POST['id_auditor'])){
		$id_auditor=$_POST['id_auditor'];
		$auditor_res=$auditor->select_one_auditorSimpl($id_auditor);
		$auditorNombre=$auditor_res['nombres'];
	}
	
	if($estado_rendicion==1) $estado='Entregado';
	else if($estado_rendicion==2) $estado='Rendido';
	else if($estado_rendicion==3) $estado='Aprobado';
	else if($estado_rendicion==4) $estado='Desaprobado';
	else if($estado_rendicion==5) $estado='Cerrado';
	
	$facturado = $_POST['facturado'];
		
	if(!empty($_POST['fechaini'])){
		$fechainif = $_POST['fechaini'];
		$fechaini = formatdatedos($_POST['fechaini']);
	}	
	
	if(!empty($_POST['fechafin'])){
		$fechafinf = $_POST['fechafin'];
		$fechafin = formatdatedos($_POST['fechafin']);
	}	
	
	$arrayValor=$calendario->selec_grupoviatico_calendario();
	if(!empty($arrayValor)){
		foreach($arrayValor as $row){
			$arrayData[$row['tipo_moneda'].$row['tipo'].$row['id_calendario']]=$row['monto'];
		}
	}
	
	$arrayValor=$calendario->select_calendario_detalle($proyecto,$fechaini,$fechafin, $estado_rendicion,$facturado,$descripcion,$sess_codpais);
	if(!empty($arrayValor)){
		foreach($arrayValor as $row){
			$sufi=$row['id']."_".$row['id_mediotransporte'];
			$arrayData[$sufi]=$row;
		}
	}
	
	$searchQuery = " AND c.id_estadoactividad!=3  AND c.id_pais = '$sess_codpais'
			AND p.id_pais = '$sess_codpais' "; 
	
	if($sess_codpais=='esp')
		$searchQuery.= " and  ( ifnull(c.monto_dolares,0) >0 or ifnull(c.monto_soles,0) >0 or ifnull(med.monto,0)>0 )";
	
	
	if(!empty($proyecto))
		$searchQuery.=" and ( p.project_id like '%$proyecto%' or  p.proyect  like '%$proyecto%') ";
	
	if(!empty($id_auditor))
		$searchQuery.=" and  c.id_auditor =$id_auditor ";
	
	if(!empty($estado_rendicion))
		$searchQuery.=" and  flag_rendicion =$estado_rendicion ";
	
	if(!empty($facturado) and $facturado=='s')
		$searchQuery.=" and  is_facturado ='s' ";
	
	else if(!empty($facturado) and $facturado=='n')
		$searchQuery.=" and  ifnull(is_facturado,'') ='' ";
	
	
	if($fechaini)
		$searchQuery.=" AND TO_DAYS(inicio_evento)>= TO_DAYS('$fechaini') ";
	if($fechafin)
		$searchQuery.=" AND TO_DAYS(inicio_evento)<= TO_DAYS('$fechafin') ";
						
	if(!empty($descripcion))
		$searchQuery.=" and (monto_dolares<= $descripcion or monto_soles<=$descripcion)";
	
	$columnName=" inicio_evento ";
	$columnSortOrder=" desc ";
	$row=0;
	$rowperpage=100000;
	$data_OF=$calendario->select_calendario_cerrar($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais);
	
	
	if($sess_codpais=='bra') include("../vista/calendario/rendicionCerrar_exporta2_bra.php");
	else include("../vista/calendario/rendicionCerrar_exporta2.php");
	

}else if(!empty($_POST['accion']) and $_POST['accion']=='printViatico'){	
	$id_calendario = $_POST['id_calendario'];
	$data_viat=$calendario->selec_detalle_viatico($id_calendario);
	$data_cal=$calendario->selec_one_calendario_complex($id_calendario);
	
	$id_estadoactividad=$data_cal['id_estadoactividad'];
	$idauditor=$data_cal['id_auditor'];
	$data_estado=$estadoactividad->select_one_estadoactividad($id_estadoactividad);
	
	$data_rol=$auditor->select_rol_by_auditor($idauditor);
	include("../vista/calendario/print_viatico.php");

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='printViaticoAuditoria'){	
	$id_calendario = $_POST['id_calendario'];
	$data_viat=$calendario->selec_detalle_viatico($id_calendario,$nocontingencia=1);
	$data_cal=$calendario->selec_one_calendario_complex($id_calendario);
	$data_cal_det=$calendario->select_mediotransporte_calenda($id_calendario);
	$id_estadoactividad=$data_cal['id_estadoactividad'];
	$idauditor=$data_cal['id_auditor'];
	$data_estado=$estadoactividad->select_one_estadoactividad($id_estadoactividad);
	
	$data_rol=$auditor->select_rol_by_auditor($idauditor);
	include("../vista/calendario/print_viatico_auditoria.php");


/***************************************************************************************
 modulo de planificacion calendarios
***************************************************************************************/
}else if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	//**********************************
	// para aprovar las vacaciones
	//**********************************
	$data_pais=$pais->selec_one_pais($sess_codpais);
	$id_template=$data_pais['id_template'];
	
	$auditor_res=$auditor->select_auditor_selectall($sess_codpais);
	$region_res=$prgregion->select_regiones($sess_codpais);
	$proyecto_res=$prgproyecto->select_proyecto_Select($sess_codpais);
	$programa_res=$prgprograma->selec_programasbypais($sess_codpais);
	$estadoactividad_res=$estadoactividad->select_estadoactividad_select($sess_codpais);
	$tipoactividad_res=$prgtipoactividad->select_tipoactividad_select($sess_codpais);

	include("../vista/calendario/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='verCalendario'){

	if($flgfiltroauditor==1){
		if(!empty($_POST['id_auditor']))
			$id_auditor = implode(",",$_POST['id_auditor']);
	}else
		$id_auditor = $sess_codauditor;
	
	$id_region = $_POST['id_region'];
	//$id_programa = $_POST['id_programa'];
	if(!empty($_POST['id_programa']))
			$id_programa = implode(",",$_POST['id_programa']);
	$project_id = $_POST['project_id'];
	$estado = $_POST['estado'];
	$filtro_comercial = $_POST['filtro_comercial'];
	$id_actividad = $_POST['id_actividad'];
	$id_estadoactividad = $_POST['id_estadoactividad'];
	$start = explode("-",$_POST['start']);
	$end =  explode("-",$_POST['end']);
	
	$sqlquery=" AND Calendario.mes_inicio >= '$start[1]' AND Calendario.mes_fin <='$end[1]' 
			AND Calendario.anio_inicio ='$start[0]' ";
	
	$sqlquery=" AND to_days(Calendario.inicio_evento) >= to_days('$_POST[start]') -100
					AND to_days(Calendario.fin_evento) <= to_days('$_POST[end]') +100 ";
	
	
	if(!empty($id_auditor))
		$sqlquery.=" and Calendario.id_auditor in( $id_auditor)";
	if(!empty($id_region))
		$sqlquery.=" AND Calendario.id IN ( SELECT CalendarioRegion.id FROM prg_calendario_region CalendarioRegion WHERE id_region IN ($id_region) )";

	if(!empty($id_programa))
		//$sqlquery.=" AND Calendario.id IN (SELECT id FROM prg_calendario_programa WHERE id_programa = $id_programa)";
		$sqlquery.=" AND Calendario.id IN (SELECT id FROM prg_calendario_programa WHERE id_programa IN ($id_programa))";
	
	if(!empty($project_id))
		$sqlquery.=" AND ( Calendario.id_proyecto IN (
			SELECT project_id FROM prg_proyecto 
			WHERE Calendario.id_pais='$sess_codpais' AND (project_id LIKE '%$project_id%' OR proyect LIKE '%$project_id%') AND flag = 1) 
			or Calendario.asunto LIKE '%$project_id%' )";
	
	if(!empty($id_actividad))
		$sqlquery.="  AND Calendario.id_tipoactividad = $id_actividad ";
	if(!empty($id_estadoactividad))
		$sqlquery.="   AND Calendario.id_estadoactividad = $id_estadoactividad	";
	
	//$sqlquery.="   AND Calendario.id not IN( 103400)";
	
	
	if(!empty($estado) and $estado=='off')
		$sqlquery.=" and Calendario.flag='0' ";
	else 
		$sqlquery.=" and Calendario.flag='1' ";	
	
	if(!empty($filtro_comercial) and $filtro_comercial=='s')
		$sqlquery.=" AND Calendario.id IN (SELECT id FROM prg_calendario_comercial WHERE coddetalle>0) ";
	else if(!empty($filtro_comercial) and $filtro_comercial=='n')
		$sqlquery.=" AND Calendario.id not IN (SELECT id FROM prg_calendario_comercial WHERE coddetalle>0) ";

	$data_OF=$calendario->select_calendario_datos($sess_codpais,$sqlquery);
	//echo json_encode($data_OF);
	//print_r($data_OF);	
	$data = array();
	if(!empty($data_OF)){
	 foreach($data_OF as $row) {
		 
		
		$observacionC="";
		$observacion=str_replace('"','',json_encode($row['observacion'],JSON_UNESCAPED_UNICODE));

		$asu=caracterBad($row['asunto']);
		$asunto=str_replace('"','',json_encode($asu,JSON_UNESCAPED_UNICODE));
		

		
		$estadoActividad=$row['id_estadoactividad'];
		$imagen_comercial="";
		$imagen_deuda ="";
		$imagen_provisional="";
		$txt_observa="";
		$texto_asunto="";
		
		$truncar=100;
		//if($sess_codpais=='ess' or $sess_codpais=='POR')
		//	$truncar=100;
			
		if($row['observacion']!='')
			$txt_observa="<b> Obs:</b>". str_replace ('"','',$row['observacion']);
		
			
		if(!empty($row['is_vinculo_comecial']))
			$imagen_comercial = "<img src='assets/img/ico_cerrar.png' title='".$observacionC."' alt='".$observacionC."' height='20' width='20'>";
		
			
		if(!empty($row['is_deuda_proyecto']))
			$imagen_deuda = "<img src='assets/img/pesada-deuda.jpg' title='".$observacion."' alt='".$observacion."' height='30' width='30'>";
		
		if(!empty($row['imagen'])){
			$imgest=$row['imagen'];
			$texto_asunto.= "&nbsp;<img src='assets/img/$imgest' height='30' width='30'>&nbsp;";
		}
		
		if($estadoActividad==5)
			$imagen_provisional = "<img src='assets/img/provisional.png' title='Provisional' alt='Provisional' height='30' width='30'>";

		if($estadoActividad=='1'){	//Planificado
			$texto_asunto.= substr($asunto,0,$truncar).$imagen_deuda.$imagen_comercial;
			
		}else if($estadoActividad=='5'){ //Provisional
			$texto_asunto.= '<i>'.substr($asunto,0,$truncar).'</i>'.$imagen_deuda.$imagen_comercial.$imagen_provisional;
		}else if($estadoActividad=='4'){ //Cancelado
			$texto_asunto.= '<s>'.substr($asunto,0,$truncar).'</s>'.$imagen_deuda.$imagen_comercial;
		}else{ // otros
			$texto_asunto.= substr($asunto,0,$truncar).$imagen_deuda.$imagen_comercial;
		}

		if($sess_codpais == 'bra'){
			$texto_tool = $row['nameauditor']." ".$asunto.strip_tags($txt_observa);
		}else{
			$texto_tool = $row['nameauditor']." ".$asunto.strip_tags($txt_observa);
		}
		
		if($asunto=='sin asignar'){
			$texto_asunto.= "(". $texto_asunto .") <br>" . $row['descripcion_sin_asignar'];
		}
		
		if($sess_codpais=='Mal')
			$texto_asunto.= "<br>Type: " . $row['tipoactividad'];
			
		$classname="fechas";
		$editable=true;
	//	if($row['id_tipoactividad']==378)
	//		$editable=false;	

		//echo json_encode($texto_asunto);

	  $data[] = array( 
		 "id"=>$row['id'],
		 "start"=>$row['fecha_inicio'],
		 "end"=>$row['fecha_final'],
		 "title"=>$texto_asunto,
		 "tooltip"=>$texto_tool,
		 "className"=> $classname,
		 "allDay"=> false,
		 "textColor"=> $row['colortexto'],
		 "color"=> $row['color'],
	//	 "displayEventTime"=> false,
		 "editable"=> $editable,
		 "resourceEditable"=> true ,
		 
		);
		
		
	 }

	//echo json_encode($data);
	}
	
		$data_=$feriado->select_feriadoCalendar($_POST['start'],$_POST['end'],$sess_codpais);
		
		if(!empty($data_)){
			foreach($data_ as $row){
			// agregar los feriados
				$data[] = array( 
				 "id"=>$row['id_feriado'],
				 "start"=>$row['inicio'],
				 "end"=>$row['fin'],
				 "title"=>'Feriado: '.$row['descripcion'],
				 "tooltip"=>'Feriado: '.$row['descripcion'],
				 "className"=> $classname,
				 "allDay"=> false,
				 "textColor"=> "#ffffff",
				 "color"=> "#c53434",
				 "editable"=> false,
				 "resourceEditable"=> true ,
				);
			}	
		}
	
	
	
	echo json_encode($data);

}else if(!empty($_POST['accion']) and $_POST['accion']=='verDetCalendario'){
	//**********************************
	// para aprovar las vacaciones
	//**********************************
	
	$proyecto_res=$prgproyecto->select_proyecto_Select($sess_codpais);
	$auditor_res=$auditor->select_auditor_selectFull($sess_codpais);
	$estadoactividad_res=$estadoactividad->select_estadoactividad_select($sess_codpais);

		
	$auditor_proycomercial_res=$auditor->select_auditorForProyCome($sess_codpais);
	
	$estado_proyecto_res=$estadoproyecto->select_estadoproyectoByPais($sess_codpais);
	
	$type_res=$calendario->select_type($sess_codpais);
	$mediotraCal_res=$calendario->select_mediotransporte($sess_codpais);
	
	$fechaini=formatdateCal($_POST['start']);
	
	$enddate= date("Y-m-d",strtotime($_POST['end']."- 1 days")); 
	$fechafin=formatdateCal($enddate); // end no suma bien
	
	$hora_inicial="08:00";
	$hora_final="17:00";
	$id_region="";
	$id_tipoactividad=0;
	
	if(!empty($_POST['eventID']) and $_POST['eventID']>0){
		$eventID = $_POST['eventID'];
	
		$cal_res=$calendario->selec_one_calendario($eventID);
		
		$project_id=$cal_res['id_proyecto'];
		$fechaini=$cal_res['fec_inicio'];
		$fechafin=$cal_res['fec_final'];
		$hora_inicial=$cal_res['hora_inicial'];
		$hora_final=$cal_res['hora_final'];
		$id_auditor=$cal_res['id_auditor'];
		$id_tipoactividad=$cal_res['id_tipoactividad'];
	
		$programa_res=$calendario->select_calendario_programa($sess_codpais,$project_id);
		$programaproy_res=$calendario->select_calendario_proyectoprograma($eventID);
		$dataproyprog=[];
		if(!empty($programaproy_res)){
			foreach($programaproy_res as $row){
				$dataproyprog[]=$row['id_programa'];
			}
		}
		
		$unidad_res=$calendario->select_calendario_unidad($project_id);
		$unidadproy_res=$calendario->select_calendario_proyectounidad($eventID);
		$dataproylugar=[];
		if(!empty($unidadproy_res)){
			foreach($unidadproy_res as $row){
				$dataproylugar[]=$row['codigo_lugar'];
			}
		}
	
		$comercial_res=$calendario->select_calendario_comercial($project_id,$sess_codpais);
		$comercialproy_res=$calendario->select_calendario_proyectocomercial($eventID);
		$datacomercial=[];
		if(!empty($comercialproy_res)){
			foreach($comercialproy_res as $row){
				$datacomercial[]=$row['coddetalle'];
			}
		}
	
	
		$actividadCalend_res=$calendario->select_actividadesCal($sess_codpais,$id_auditor,$fechaini,$fechafin);


		
		$mediotraCalenda_res=$calendario->select_mediotransporte_calenda($eventID);
		if(!empty($mediotraCalenda_res)){
			foreach($mediotraCalenda_res as $row){
				$dataMovil[$row['id_mediotransporte']]=json_encode($row);
			}
		}

		$regionCalenda_res=$calendario->select_auditorregion_calendar($sess_codpais,$eventID);
		$id_region=$regionCalenda_res['id_region'];
		
		$factura_res=$calendario->select_factura_calendar($sess_codpais,$eventID);
	}
	
	
	
	$tipoactividad_res=$prgtipoactividad->select_tipoactividad_select($sess_codpais,$id_tipoactividad,'1');
	
	$datalog_res=$calendario->select_calendario_log($eventID);
	
	include("../vista/calendario/verDetalleCal.php");	
	
	

}else if(!empty($_POST['accion']) and $_POST['accion']=='cargarProgramas'){
	$project_id = $_POST['project_id'];
	$programa_res=$calendario->select_calendario_programa($sess_codpais,$project_id);
	include("../vista/calendario/select_programa.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='cargarUnidades'){
	$project_id = $_POST['project_id'];
	$unidad_res=$calendario->select_calendario_unidad($project_id);
	include("../vista/calendario/select_unidades.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='cargarComercial'){
	$project_id = $_POST['project_id'];
	$comercial_res=$calendario->select_calendario_comercial($project_id,$sess_codpais);
	include("../vista/calendario/select_comercial.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='cargarAuditor'){
	$id_tipoactividad = $_POST['id_tipoactividad'];
	$eventID = $_POST['eventID'];
	if(!empty($_POST['id_programa']))
		$id_programa = implode(",",$_POST['id_programa']);
	$auditor_res=$calendario->select_auditorprograma_calendar($sess_codpais,$id_programa);
	include("../vista/calendario/select_auditor.php");		

}else if(!empty($_POST['accion']) and $_POST['accion']=='cargarDeuda'){
	$project_id = $_POST['project_id'];
	
	$deuda_res=$calendario->select_deudaproyecto_calendar($sess_codpais,$project_id);
	if(!empty($deuda_res)){
		echo "CONTACTAR EQUIPO COMERCIAL. Facturas: ".$deuda_res['facturas'];
	}

}else if(!empty($_POST['accion']) and $_POST['accion']=='cargarRegion'){
	
	$id_region=$_POST['id_region'];
	if(!empty($_POST['id_auditor']))
		 $id_auditor =implode(",",$_POST['id_auditor']);

	if(!empty($id_auditor)){
		$region_res=$calendario->select_auditorregion($sess_codpais,$id_auditor);
	}
	include("../vista/calendario/select_region.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_setCalendario'){
	
	
	 $id_tipoactividad=$_POST['id_tipoactividad2'];// => 118 
	 $project_id=$_POST['project_id2'];// => 804240 
	
		 
	 $nro_muestra=$_POST['nromuestra'];// => 1 
	 $por_dia=$_POST['totaldias'];// => 1 
	
	 $id_region=$_POST['id_region2'] ;//=> 1 
	 
	 $flag_rendicion=1;// => 1 
	 $id_asignacion_viaticos=$_POST['viatico'];
	 
	 $monto_dolares=$_POST['montousd'];// => 
	 $monto_soles=$_POST['montomn'];// => 
	 $id_estadoactividad=$_POST['id_estado'];// => 1 
	 $auditoria=$_POST['auditoria'];// => 32323 
	 $id_type=$_POST['id_type'];// => 21 
	 $observacion= preg_replace("/\r\n/","<br>",$_POST['observacion']);// => ninguna 
	 $hora_inicial=$_POST['horaini'];// => 08:00 
	 $fechai= formatdatedos($_POST['fechai']);// => 05/09/2021 
	 $hora_final=$_POST['horafin'];// => 17:00 
	 $fechaf= formatdatedos($_POST['fechaf']);// => 05/09/2021 
	 
	 $datai=explode("/",$fechai);
	 $dia_inicio=$datai['2'];
	 $mes_inicio=$datai['1'];
	 $anio_inicio=$datai['0'];
	 
	 $dataf=explode("/",$fechaf);
	 $dia_fin=$dataf['2'];
	 $mes_fin=$dataf['1'];
	 $anio_fin=$dataf['0'];
	 
	 $inicio_evento=$fechai . " " .$hora_inicial;
	 $fin_evento=$fechaf . " " .$hora_final;
	 
	 $is_sabado="0";
	 if(!empty($_POST['is_sabado']))
		 $is_sabado="1";// => on
	 
	 $is_domingo="0";
	 if(!empty($_POST['is_domingo']))
		 $is_domingo="1";// => on
	 
	$hi=explode(":",$hora_inicial);
	$hora_inicio=($hi[0]*60) + $hi[1];
	
	$hf=explode(":",$hora_final);
	$hora_fin=($hf[0]*60) + $hf[1];
	$asunto=f_limpiar_save($_POST['asunto']);
	
	$id_calendario="2147483647";

	$flghorariofijo="0";
	if(!empty($_POST['flghorariofijo']))
		$flghorariofijo="1";


	// agregar calendario
	
	if(!empty($_POST['eventID'])){
		foreach($_POST['id_auditor2'] as $id_auditor){
			$eventID=$_POST['eventID'];
			$calendario->update_calendar($eventID,$sess_codpais,$id_tipoactividad, $project_id,$nro_muestra, $por_dia, $id_auditor,
			 $monto_dolares, $monto_soles, $id_estadoactividad, $auditoria, $id_type, $observacion,$hora_inicial, $hora_final, 
			 $dia_inicio, $mes_inicio, $anio_inicio, $dia_fin, $mes_fin, $anio_fin, $inicio_evento,  $fin_evento, 
			 $is_sabado, $is_domingo, $hora_inicio, $hora_fin, $asunto, $id_calendario, $id_asignacion_viaticos, $flag_rendicion,$flghorariofijo,
			 $usuario_name, $ip,$invoice_number,$invoice_date,$invoice_amount,$id_comercial_executive,$id_estado_proyecto);
		}
		
		$accion="Actualizacion de la planificacion";
		
		
		$calendario->delete_calendarprograma($eventID);	 
		 if(!empty($_POST['id_programa2'])){
			foreach($_POST['id_programa2'] as $id_programa){
				$calendario->insert_calendarprograma($id_programa,$eventID);
			}
		}
		

	}else{
 
		foreach($_POST['id_auditor2'] as $id_auditor){
			$eventID=$calendario->insert_calendar($sess_codpais,$id_tipoactividad, $project_id,$nro_muestra, $por_dia, $id_auditor,
			 $monto_dolares, $monto_soles, $id_estadoactividad, $auditoria, $id_type, $observacion,$hora_inicial, $hora_final, 
			 $dia_inicio, $mes_inicio, $anio_inicio, $dia_fin, $mes_fin, $anio_fin, $inicio_evento,  $fin_evento, 
			 $is_sabado, $is_domingo, $hora_inicio, $hora_fin, $asunto, $id_calendario, $id_asignacion_viaticos, $flag_rendicion,$flghorariofijo,
			 $usuario_name, $ip,$invoice_number,$invoice_date,$invoice_amount,$id_comercial_executive,$id_estado_proyecto);
			 
			 $accion="Ingreso de la planificacion";
			 
			if(!empty($_POST['id_programa2'])){
				foreach($_POST['id_programa2'] as $id_programa){
					$calendario->insert_calendarprograma($id_programa,$eventID);
				}	
			}
			
			// calendario log
			//***************************************
			$calendario->update_calendar_comodin($eventID);
			
			
		}	
		
		
	}
	
	// $calendario->insert_calendario_log($accion,$eventID,$ip,$usuario_name); ORIGINAL CAMBIADO POR AMENA

	$id_estadoactividad = $_POST['id_estado']; 
	$calendario->insert_calendario_log($accion, $eventID, $ip, $usuario_name, $id_estadoactividad);
	 
	//***************************************
	// aqui debe validar si envia correo
	//***************************************
	$pre_id_estado=$_POST['pre_id_estado'];
	$dataact=$prgtipoactividad->selec_one_tipoactividad($id_tipoactividad);			
	if($dataact['is_enviar_email']=='1' 
		and (empty($pre_id_estado) or $pre_id_estado!=$id_estadoactividad ) 
		and ($id_estadoactividad==1 
				or ($id_estadoactividad==5 and ($sess_codpais=='are' or $sess_codpais=='bra')) //Provisional
				or ($id_estadoactividad==4 and ($sess_codpais=='bra' or $sess_codpais=='esp') ) //Cancelado
			)
	){
		if($project_id!='' and $sess_codpais=='can') $txtname=" - Project: $project_id";
		else if($project_id!='' and $sess_codpais!='can') $txtname=" - Proyecto: $project_id";
		else $txtname="";
		 // si se envia por ese tipo
		
		if($sess_codpais=='can')
			$asunto = 'PLANNING ' . $txtname; 
		else
			$asunto = 'PLANIFICACION' . $txtname; 
		
		
		if($sess_codpais=='are' ){
			if($id_estadoactividad==1){
				$asunto.=" ( PLANIFICADO)";
			}else if($id_estadoactividad==4){
				$asunto.="(PROVISIONAL)";
			}else{
				$asunto.=" (CANCELADO)";
			}
		}else if($sess_codpais=='esp'){
			if($id_estadoactividad==1){
				$asunto.=" ( PLANIFICADO)";
			}else if($id_estadoactividad==4){
				$asunto.=" ( CANCELADO)";
			}
		}	
		
		
		
		if($sess_codpais=='can')
			$mensaje = 'The following activity has been programmed <br>';
		else
			$mensaje = 'Se ha programado la siguiente actividad <br>';
		
		$rowcal=$calendario->selec_one_calendario_complex($eventID);
		$rowprograma=$calendario->select_calendario_id_programa($sess_codpais,$eventID);
		$rowestado=$estadoactividad->select_one_estadoactividad($id_estadoactividad);
		$dscestado=$rowestado['descripcion'];
		
		if(!empty($id_auditor)){
			$auditor_res=$auditor->select_one_auditorSimpl($id_auditor);
			$auditorMail=$auditor_res['email'];
		}
		
		if($sess_codpais=='can')
			$body=$mensaje.plantillaEmailInformacion_en($rowcal,$rowprograma,$dscestado);
		else
			$body=$mensaje.plantillaEmailInformacion($rowcal,$rowprograma,$dscestado);
		
		if(!empty($auditorMail)){
			$message = Swift_Message::newInstance($asunto)
				->setFrom(array($user_mail =>  $name_mail))
				->setTo(explode(",",$auditorMail))
				->setBody($body, 'text/html', 'iso-8859-2')
			;
			$numSent = $mailer->send($message);
			printf("Enviado: %d mensajes a $auditorMail<br>", $numSent);
		}				
	}
	//***************************************
	// aqui deve validar si envia correo
	//***************************************
			
	//Para USA  ************************************************
	if($sess_codpais=='eng'){
		if(!empty($_POST['invoice_number'])){
			foreach($_POST['invoice_number'] as $key =>$invoice_number){
				echo $key;
				$cinvoice_date=$_POST['invoice_date'];
				$cremark_invoice=$_POST['remark_invoice'];
				$cinvoice_amount=$_POST['invoice_amount'];
				$cid_comercial_executive=$_POST['id_comercial_executive'];
				$cid_estado_proyecto=$_POST['id_estado_proyecto'];
				
				$invoice_date=$cinvoice_date[$key];
				$remark_invoice=$cremark_invoice[$key];
				$invoice_amount=str_replace(',','',$cinvoice_amount[$key]);
				$id_comercial_executive=$cid_comercial_executive[$key];
				$id_estado_proyecto=$cid_estado_proyecto[$key];
							
				if(!empty($invoice_number) or !empty($invoice_date))
					 $invoice_date= formatdatedos($invoice_date);// => 05/09/2021 
					$calendario->insert_calendarfactura($eventID,$invoice_number,$invoice_date,$remark_invoice,$invoice_amount,$id_comercial_executive,$id_estado_proyecto,$usuario_name,$ip);
			
			}
		}
	
		if(!empty($_POST['id_factura'])){
			foreach($_POST['id_factura'] as $id_factura){
				
				$invoice_number=$_POST['invoice_number'.$id_factura];
				$invoice_date=$_POST['invoice_date'.$id_factura];
				$remark_invoice=$_POST['remark_invoice'.$id_factura];
				$invoice_amount=str_replace(',','',$_POST['invoice_amount'.$id_factura]);
				$id_comercial_executive=$_POST['id_comercial_executive'.$id_factura];
				$id_estado_proyecto=$_POST['id_estado_proyecto'.$id_factura];
				$invoice_date= formatdatedos($invoice_date);// => 05/09/2021 
				$calendario->update_calendarfactura($eventID,$id_factura,$invoice_number,$invoice_date,$remark_invoice,$invoice_amount,$id_comercial_executive,$id_estado_proyecto,$usuario_name,$ip);
			}
		}
	}
	 // fin usa ************************************************
	 //***************************************
	 //***************************************

	$calendario->delete_calendarunidad($eventID);	 
	 if(!empty($_POST['id_unidad'])){
		foreach($_POST['id_unidad'] as $id_unidad){
			$calendario->insert_calendarunidad($id_unidad,$eventID);
		}
	}
	
	
	$calendario->delete_calendarcomercial($eventID);	 
	 if(!empty($_POST['id_proyectocomercial'])){
		foreach($_POST['id_proyectocomercial'] as $id_proyectocomercial){
			$calendario->insert_calendarcomercial($id_proyectocomercial,$eventID);
		}
	}
	

	$calendario->delete_calendarregion($eventID);	 
	$calendario->insert_calendarregion($id_region,$eventID);
	
	$calendario->delete_calendartransporte($eventID);	 
	$mediotraCalenda_res=$calendario->select_mediotransporte($sess_codpais);
	if(!empty($mediotraCalenda_res)){
		foreach($mediotraCalenda_res as $row){
			$idmedio=$row['id_mediotransporte'];
			$usd=$_POST['usd_'.$idmedio] ;
			$mn=$_POST['mn_'.$idmedio];
			$penausdv=$_POST['penausdv_'.$idmedio];
			$penamn=$_POST['penamn_'.$idmedio];
			$horavia=$_POST['horavia_'.$idmedio];
			if($usd!='' or $mn!='' or $penausdv!='' or $penamn!='' or $horavia!='')
				$calendario->insert_calendartransporte($eventID,$idmedio,$usd,$mn,$penausdv,$penamn,$horavia);
		}
	}


	

	echo "Se registro exitosamente el registro";

}else if(!empty($_POST['accion']) and $_POST['accion']=='reprogramarCal'){

	 $eventID=$_POST['eventID'];
	 $opcion=$_POST['opcion'];

	 $hora_inicial=$_POST['horaini'];// => 17:00 
	 $hora_final=$_POST['horafin'];// => 17:00 
	 $fechai= formatdatedos($_POST['fechai']);// => 05/09/2021 
	 $fechaf= formatdatedos($_POST['fechaf']);// => 05/09/2021 
	 
	 $datai=explode("/",$fechai);
	 $dia_inicio=$datai['2'];
	 $mes_inicio=$datai['1'];
	 $anio_inicio=$datai['0'];
	 
	 $dataf=explode("/",$fechaf);
	 $dia_fin=$dataf['2'];
	 $mes_fin=$dataf['1'];
	 $anio_fin=$dataf['0'];
	 
	 $inicio_evento=$fechai . " " .$hora_inicial;
	 $fin_evento=$fechaf . " " .$hora_final;
	 
	 $is_sabado="0";
	 if(!empty($_POST['is_sabado']))
		 $is_sabado="1";// => on
	 
	 $is_domingo="0";
	 if(!empty($_POST['is_domingo']))
		 $is_domingo="1";// => on
	 
	$hi=explode(":",$hora_inicial);
	$hora_inicio=($hi[0]*60) + $hi[1];
	
	$hf=explode(":",$hora_final);
	$hora_fin=($hf[0]*60) + $hf[1];

	if($opcion=='1'){
		$causa_cuperu = '1';
		$causa_cliente = '0';
	}else if($opcion=='2'){
		$causa_cuperu = '0';
		$causa_cliente = '1';
	}

	$idnew=$calendario->insert_calendarreprograma($eventID,$hora_inicial,$hora_inicio,$dia_inicio,$mes_inicio,$anio_inicio,$hora_final,
		$hora_fin,$dia_fin,$mes_fin,$anio_fin,$usuario_name,$ip,$causa_cuperu,$causa_cliente,$inicio_evento,$fin_evento,$is_sabado,$is_domingo);
		
	 $calendario->proceso_calendarreprograma($eventID,$idnew);	
	 
	 echo "Se ha reprogramado la actividad.";
	 
}else if(!empty($_POST['accion']) and $_POST['accion']=='updateCalendarMove'){

	$eventID=$_POST['eventID'];
	$fechaii=$_POST['fechai'];
	$fechaff=$_POST['fechaf'];
	
	$fechai=substr($fechaii,0,10);
	$fechaf=substr($fechaff,0,10);
	
	$hora_inicial=substr($fechaii,11,5);
	$hora_final=substr($fechaff,11,5);
	
	$datai=explode("-",$fechai);
	 $dia_inicio=$datai['2'];
	 $mes_inicio=$datai['1'];
	 $anio_inicio=$datai['0'];
	 
	 $dataf=explode("-",$fechaf);
	 $dia_fin=$dataf['2'];
	 $mes_fin=$dataf['1'];
	 $anio_fin=$dataf['0'];
	 
	 $inicio_evento=$fechai . " " .$hora_inicial;
	 $fin_evento=$fechaf . " " .$hora_final;
	 
	$hi=explode(":",$hora_inicial);
	$hora_inicio=($hi[0]*60) + $hi[1];
	
	$hf=explode(":",$hora_final);
	$hora_fin=($hf[0]*60) + $hf[1];
	
	$calendario->proceso_calendareUpdateMove($eventID,$sess_codpais,$hora_inicial, $hora_final, $dia_inicio, $mes_inicio, $anio_inicio, $dia_fin, $mes_fin, $anio_fin, $inicio_evento,  $fin_evento, $hora_inicio, $hora_fin, $usuario_name, $ip);	
	echo "Se actualizo el registro.";

// mueve en el calendario
}else if(!empty($_POST['accion']) and $_POST['accion']=='eliminarCal'){

	$eventID=$_POST['eventID'];
	$calendario->proceso_calendareliminar($eventID,$usuario_name,$ip);	
	echo "Se ha eliminado la actividad.";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='reactivarCal'){

	$eventID=$_POST['eventID'];
	$calendario->proceso_calendarreactivar($eventID,$usuario_name,$ip);	
	echo "Se ha reactivado la actividad.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='validarPrograma'){
	$id_tipoactividad=$_POST['id_tipoactividad'];
	// artificio, ver si pertenece a categoria auditoria 070322
	if($id_tipoactividad==41)
		echo 1;
	else{ 
		$data=$calendario->validar_programa($id_tipoactividad,$sess_codpais);	
		echo $data['valor'];
	}	

}else if(!empty($_POST['accion']) and $_POST['accion']=='div_factura'){	
	$auditor_proycomercial_res=$auditor->select_auditorForProyCome($sess_codpais);
	$estado_proyecto_res=$estadoproyecto->select_estadoproyectoByPais($sess_codpais);
	include("../vista/calendario/div_invoice.php");	
}

function plantillaEmailInformacion($rowcal,$rowprograma,$dscestado){
		
		$border_celda = 'style="border:1px solid #999"';
		$html ="";
		$html.='<table style="border-spacing:5px;width:auto;border:1px solid #999" >';
		$html.='<tr>';
			$html.='<th style="width: 140px;border:1px solid #999">Programa</th>';
			$html.='<td '.$border_celda.' colspan="3">';
				foreach($rowprograma as $item){
						$html.= "- ".$item['descripcion']."\n <br>";
				}
			$html.='</td>';
			$html.='</tr>';
		$html.='<tr>';
			$html.='<th '.$border_celda.' >Tipo Actividad</th>';
			$html.='<td '.$border_celda.' colspan="3">'.$rowcal["descripcion"].'</td>';
		$html.='</tr>';
		$html.='<tr>';
			$html.='<th '.$border_celda.'>Fecha</th>';
			
			$fechai=$rowcal["dia_inicio"] ."/". $rowcal["mes_inicio"]."/".$rowcal["anio_inicio"];
			$fechai.="  ". $rowcal["hora_inicial"];
			$fechaf=$rowcal["dia_fin"] ."/". $rowcal["mes_fin"]."/".$rowcal["anio_fin"];
			$fechaf.="  ". $rowcal["hora_final"];
			$html.='<td '.$border_celda.' colspan="3">'. $fechai.' hasta '.$fechaf.'</td>';
		$html.='</tr>';		
		$html.='<tr>';
			$html.='<th '.$border_celda.'>Proyectos</th>';
			$html.='<td '.$border_celda.' colspan="3">'. $rowcal["proyect"].'</td>';
		$html.='</tr>';
		$html.='<tr>';
			$html.='<th '.$border_celda.'>Nro de Muestra</th>';
			$html.='<td '.$border_celda.'>'. $rowcal["nro_muestra"].'</td>';
			$html.='<th '.$border_celda.'>total, Por d&iacute;a</th>';
			$html.='<td '.$border_celda.'>'. $rowcal["por_dia"].'</td>';
		$html.='</tr>';
		$html.='<tr>';
			$html.='<th '.$border_celda.'>Auditores</th>';
			$html.='<td '.$border_celda.' colspan="3">';
				$html.= $rowcal["auditor"]." ";
			$html.='</td>';
		$html.='</tr>';
		if($rowcal["monto_dolares"]>0 || $rowcal["monto_soles"]>0){
			$html.='<tr>';
				$html.='<th colspan="4" '.$border_celda.' >Asignacion de viaticos::';
				$html.= $rowcal["asignacion"];
				$html.='</th>';
			$html.='</tr>';
			$html.='<tr>';
			if($rowcal["monto_dolares"]>0){
				$html.='<th '.$border_celda.'>Monto Dolares</th>';
				$html.='<td '.$border_celda.'>'. $mon_dolares = $rowcal["monto_dolares"];
				$html.='</td>';
			}
			if($rowcal["monto_soles"]>0){
				$html.='<th '.$border_celda.'>Monto Soles</th>';
				$html.='<td '.$border_celda.'>'. $mon_soles = $rowcal["monto_soles"];
				$html.='</td>';
			}
			$html.='</tr>';
		}
		if($rowcal["utizado_monto_dolares"]>0 || $rowcal["utizado_monto_soles"]>0){
			$html.='<tr>';
				$html.='<th colspan="4" '.$border_celda.' >Utilizados::';
				$html.= $rowcal["asignacion"].'</th>';
			$html.= '</tr>';
			$html.='<tr>';
				$html.='<th '.$border_celda.'>Monto Hospedaje</th>';
				$html.='<td '.$border_celda.'>'. "US$ ".$rowcal["utilizado_hospedaje_dolares"].'</td>';
				$html.='<th '.$border_celda.'>Monto Hospedaje</th>';
				$html.='<td '.$border_celda.'>'.  "S/. ".$rowcal["utilizado_hospedaje_soles"].'</td>';
			$html.='</tr>';
			$html.='<tr>';
				$html.='<th '.$border_celda.'>Monto Movilidad</th>';
				$html.='<td '.$border_celda.'>'. "US$ ". $rowcal["utilizado_movilidad_dolares"] .'</td>';
				$html.='<th '.$border_celda.'>Monto Movilidad</th>';
				$html.='<td '.$border_celda.'>'. "S/. ".$rowcal["utilizado_movilidad_soles"].'</td>';
			$html.='</tr>';
			$html.='<tr>';
				$html.='<th '.$border_celda.'>Monto Alimentaci&oacute;n</th>';
				$html.='<td '.$border_celda.'>'. "US$ ".$rowcal["utilizado_alimentacion_dolares"] .'</td>';
				$html.='<th '.$border_celda.'>Monto Alimentaci&oacute;n</th>';
				$html.='<td '.$border_celda.'>'. "S/. ".$rowcal["utilizado_alimentacion_soles"].'</td>';
			$html.='</tr>';
			$html.='<tr>';
				$html.='<th '.$border_celda.'>Monto Contingencia</th>';
				$html.='<td '.$border_celda.'>'. "US$ ".$rowcal["utilizado_contingencia_dolares"] .'</td>';
				$html.='<th '.$border_celda.'>Monto Contingencia</th>';
				$html.='<td '.$border_celda.'>'. "S/. ".$rowcal["utilizado_contingencia_soles"].'</td>';
			$html.='</tr>';
			$html.='<tr>';
				$html.='<th ></th>';
				$html.='<td>----------------------</td>';
				$html.='<th ></th>';
				$html.='<td>----------------------</td>';
			$html.='</tr>';
			$html.= '<tr>';
				if($rowcal["Calendario"]["utizado_monto_dolares"]>0){
					$html.= '<th '.$border_celda.'>Monto Dolares</th>';
					$html.= '<td '.$border_celda.'>'.$rowcal["utizado_monto_dolares"].'</td>';
				}
				if($rowcal["Calendario"]["utizado_monto_soles"]>0){
					$html.= '<th '.$border_celda.'>Monto Soles</th>';
					$html.= '<td '.$border_celda.'>'.$rowcal["utizado_monto_soles"].'</td>';
				}
			$html.= '</tr>';
		}
		$html.= '<tr>';
			$html.= '<th '.$border_celda.'>Estado</th>';
			$html.= '<td '.$border_celda.' colspan="3">'.$dscestado.'</td>';
		$html.= '</tr>';
		$html.= '<tr>';
			$html.= '<th '.$border_celda.'>Observaci&oacute;n</th>';
			$html.= '<td '.$border_celda.' colspan="3">'. $rowcal["observacion"].'</td>';
		$html.= '</tr>';
		$html.= '</table>';
		return $html;
	}


function plantillaEmailInformacion_en($rowcal,$rowprograma,$dscestado){
		
		$border_celda = 'style="border:1px solid #999"';
		$html ="";
		$html.='<table style="border-spacing:5px;width:auto;border:1px solid #999" >';
		$html.='<tr>';
			$html.='<th style="width: 140px;border:1px solid #999">Program</th>';
			$html.='<td '.$border_celda.' colspan="3">';
				foreach($rowprograma as $item){
						$html.= "- ".$item['descripcion']."\n <br>";
				}
			$html.='</td>';
			$html.='</tr>';
		$html.='<tr>';
			$html.='<th '.$border_celda.' >Type of activity</th>';
			$html.='<td '.$border_celda.' colspan="3">'.$rowcal["descripcion"].'</td>';
		$html.='</tr>';
		$html.='<tr>';
			$html.='<th '.$border_celda.'>Date</th>';
			
			$fechai=$rowcal["dia_inicio"] ."/". $rowcal["mes_inicio"]."/".$rowcal["anio_inicio"];
			$fechai.="  ". $rowcal["hora_inicial"];
			$fechaf=$rowcal["dia_fin"] ."/". $rowcal["mes_fin"]."/".$rowcal["anio_fin"];
			$fechaf.="  ". $rowcal["hora_final"];
			$html.='<td '.$border_celda.' colspan="3">'. $fechai.' hasta '.$fechaf.'</td>';
		$html.='</tr>';		
		$html.='<tr>';
			$html.='<th '.$border_celda.'>Project</th>';
			$html.='<td '.$border_celda.' colspan="3">'. $rowcal["proyect"].'</td>';
		$html.='</tr>';
		$html.='<tr>';
			$html.='<th '.$border_celda.'>Sample number</th>';
			$html.='<td '.$border_celda.'>'. $rowcal["nro_muestra"].'</td>';
			$html.='<th '.$border_celda.'>Total per day</th>';
			$html.='<td '.$border_celda.'>'. $rowcal["por_dia"].'</td>';
		$html.='</tr>';
		$html.='<tr>';
			$html.='<th '.$border_celda.'>Auditors</th>';
			$html.='<td '.$border_celda.' colspan="3">';
				$html.= $rowcal["auditor"]." ";
			$html.='</td>';
		$html.='</tr>';
		if($rowcal["monto_dolares"]>0 || $rowcal["monto_soles"]>0){
			$html.='<tr>';
				$html.='<th colspan="4" '.$border_celda.' >Asignacion de viaticos';
				$html.= $rowcal["asignacion"];
				$html.='</th>';
			$html.='</tr>';
			$html.='<tr>';
			if($rowcal["monto_dolares"]>0){
				$html.='<th '.$border_celda.'>Monto Dolares</th>';
				$html.='<td '.$border_celda.'>'. $mon_dolares = $rowcal["monto_dolares"];
				$html.='</td>';
			}
			if($rowcal["monto_soles"]>0){
				$html.='<th '.$border_celda.'>Monto Soles</th>';
				$html.='<td '.$border_celda.'>'. $mon_soles = $rowcal["monto_soles"];
				$html.='</td>';
			}
			$html.='</tr>';
		}
		if($rowcal["utizado_monto_dolares"]>0 || $rowcal["utizado_monto_soles"]>0){
			$html.='<tr>';
				$html.='<th colspan="4" '.$border_celda.' >Utilizados';
				$html.= $rowcal["asignacion"].'</th>';
			$html.= '</tr>';
			$html.='<tr>';
				$html.='<th '.$border_celda.'>Monto Hospedaje</th>';
				$html.='<td '.$border_celda.'>'. "US$ ".$rowcal["utilizado_hospedaje_dolares"].'</td>';
				$html.='<th '.$border_celda.'>Monto Hospedaje</th>';
				$html.='<td '.$border_celda.'>'.  "S/. ".$rowcal["utilizado_hospedaje_soles"].'</td>';
			$html.='</tr>';
			$html.='<tr>';
				$html.='<th '.$border_celda.'>Monto Movilidad</th>';
				$html.='<td '.$border_celda.'>'. "US$ ". $rowcal["utilizado_movilidad_dolares"] .'</td>';
				$html.='<th '.$border_celda.'>Monto Movilidad</th>';
				$html.='<td '.$border_celda.'>'. "S/. ".$rowcal["utilizado_movilidad_soles"].'</td>';
			$html.='</tr>';
			$html.='<tr>';
				$html.='<th '.$border_celda.'>Monto Alimentaci&oacute;n</th>';
				$html.='<td '.$border_celda.'>'. "US$ ".$rowcal["utilizado_alimentacion_dolares"] .'</td>';
				$html.='<th '.$border_celda.'>Monto Alimentaci&oacute;n</th>';
				$html.='<td '.$border_celda.'>'. "S/. ".$rowcal["utilizado_alimentacion_soles"].'</td>';
			$html.='</tr>';
			$html.='<tr>';
				$html.='<th '.$border_celda.'>Monto Contingencia</th>';
				$html.='<td '.$border_celda.'>'. "US$ ".$rowcal["utilizado_contingencia_dolares"] .'</td>';
				$html.='<th '.$border_celda.'>Monto Contingencia</th>';
				$html.='<td '.$border_celda.'>'. "S/. ".$rowcal["utilizado_contingencia_soles"].'</td>';
			$html.='</tr>';
			$html.='<tr>';
				$html.='<th ></th>';
				$html.='<td>----------------------</td>';
				$html.='<th ></th>';
				$html.='<td>----------------------</td>';
			$html.='</tr>';
			$html.= '<tr>';
				if($rowcal["Calendario"]["utizado_monto_dolares"]>0){
					$html.= '<th '.$border_celda.'>Monto Dolares</th>';
					$html.= '<td '.$border_celda.'>'.$rowcal["utizado_monto_dolares"].'</td>';
				}
				if($rowcal["Calendario"]["utizado_monto_soles"]>0){
					$html.= '<th '.$border_celda.'>Monto Soles</th>';
					$html.= '<td '.$border_celda.'>'.$rowcal["utizado_monto_soles"].'</td>';
				}
			$html.= '</tr>';
		}
		$html.= '<tr>';
			$html.= '<th '.$border_celda.'>Status</th>';
			$html.= '<td '.$border_celda.' colspan="3">'.$dscestado.'</td>';
		$html.= '</tr>';
		$html.= '<tr>';
			$html.= '<th '.$border_celda.'>Observation</th>';
			$html.= '<td '.$border_celda.' colspan="3">'. $rowcal["observacion"].'</td>';
		$html.= '</tr>';
		$html.= '</table>';
		return $html;
	}
?>