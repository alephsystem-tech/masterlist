<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_auditor_modelo.php");
include("../modelo/prg_rol_modelo.php");
include("../modelo/prg_enlace_modelo.php");
include("../modelo/prg_region_modelo.php");
include("../modelo/prg_programa_modelo.php");
include("../modelo/log_modelo.php");

$auditor=new prg_auditor_model();
$roles=new prg_rol_model();
$prgenlace=new prg_enlace_model();
$region=new prg_region_model();
$programa=new prg_programa_model();
$tblog=new log_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

$pathFoto = 'archivos/auditorFoto/'; // upload directory
$valid_extensions = array('jpg','gif','png','jpeg','bmp'); // valid extensions

	// *************** inicio de ver rol *****************************
	// modificacion sobre el nivel del rol
	$data_enlace=$prgenlace->selec_one_enlace_bycontrol($sess_codpais,'auditores','web_index');
	if(!empty($data_enlace)){
		$id_enlace=$data_enlace['id_enlace'];
		$data_nivel=$roles->selec_enlacenivelbyPais($sess_codpais,$sess_codrol,$id_enlace);
		if(!empty($data_nivel)){
			foreach($data_nivel as $row){
				$isnivel=1;
				$isread=$row['isread'];
				$isupdate=$row['isupdate'];
				$isdelete=$row['isdelete'];
			}
		}
	}
	// *************** fin de ver rol *****************************
	//*************************************************************
	
	

if(!empty($_POST['accion']) and $_POST['accion']=='web_index'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	$rol_res=$roles->select_roles($sess_codpais);
    include("../vista/auditor/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_auditor'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	if(!empty($_POST['flgtipo']))
		$flgtipo = $_POST['flgtipo'];
	
	if(!empty($_POST['id_rol']))
		$id_rol = $_POST['id_rol'];
	
	if(!empty($_POST['flgstatus']))
		$flgstatus = $_POST['flgstatus'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" Auditor.nombre";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and Auditor.id_pais = '$sess_codpais' ";

	if(!empty($id_rol))
		$searchQuery.= " and Usuario.id_rol = '$id_rol' ";
	
	if(!empty($flgtipo))
		$searchQuery.= " and Auditor.flgtipo = '$flgtipo' ";
	
	if(!empty($flgstatus) and $flgstatus=='1')
		$searchQuery.= " and Auditor.flgstatus = '$flgstatus' ";
	else if(!empty($flgstatus) and $flgstatus=='2')
		$searchQuery.= " and Auditor.flgstatus = '0' ";
	
	## Total number of records without filtering
	$data_maxOF=$auditor->selec_total_auditor($searchQuery);
	$totalRecords = $data_maxOF['total'];


	if(!empty($descripcion))
		$searchQuery.=" and CONCAT_WS(' ',Auditor.nombre, Auditor.apepaterno,Auditor.apematerno) like '%$descripcion%'  ";
		
	## Total number of record with filtering
	$data_maxOF2=$auditor->selec_total_auditor($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$auditor->select_auditor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
		 
			$id=$row['id_auditor'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediAuditor'><i class='fas fa-edit'></i> </button>";
			$programa="<button type='button' id='esprog_". $id ."'  class='btn  btn_proAuditor'><i class='fas fa-edit'></i> </button>";
			$modulo="<button type='button' id='esprog_". $id ."'  class='btn  btn_modAuditor'><i class='fas fa-file'></i> </button>";
			
			$acciones="<button type='button' id='estproy_". $id ."'  class='btn  btn_accAuditor'><i class='fas fa-envelope'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliAuditor'><i class='fas fa-trash'></i> </button>";
			
			$color="<p style='color:$row[color]'>". $row['color'] ."</p>";
			
		
		   $data[] = array( 
			   "nombreCompleto"=>str_replace('"','',json_encode($row['nombreCompleto'],JSON_UNESCAPED_UNICODE)),
				"roles"=>str_replace('"','',json_encode($row['roles'],JSON_UNESCAPED_UNICODE)),
				"iniciales"=>str_replace('"','',json_encode($row['iniciales'],JSON_UNESCAPED_UNICODE)),
				"color"=>str_replace('"','',json_encode($row['color'],JSON_UNESCAPED_UNICODE)),
			   "id_auditor"=>$id,
			   "usuario"=>$row['usuario'],
			   "costo"=>$row['costo'],
			   "dsccomercial"=>$row['dsccomercial'],
			   "dsccuota"=>$row['dsccuota'],
			   "dscvencido"=>$row['dscvencido'],
			   "dscfactura"=>$row['dscfactura'],
			   "dscestatus"=>$row['dscestatus'],
			   "flgtipo"=>$row['flgtipo'],
			   "acciones"=>$acciones,
			   "edita"=>$edita,
			   "modulo"=>$modulo,
			   "elimina"=>$elimina,
			   "programa"=>$programa,
			   "vacio"=>"",
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editAuditor'){
	$id_auditor="";
	if(!empty($_POST['id_auditor'])){
		$id_auditor=$_POST['id_auditor'];
		$data_res=$auditor->selec_one_auditor($id_auditor);
		if($data_res['gprograma']!='')
			$arr_gprograma=explode(",",$data_res['gprograma']);
		
		$data_grol=$auditor->selec_data_rol($id_auditor);
		if($data_grol['grol']!='')
			$arr_rol=explode(",",$data_grol['grol']);

		$data_gregi=$auditor->selec_data_region($id_auditor);
		if($data_gregi['gregion']!='')
			$arr_region=explode(",",$data_gregi['gregion']);
	}
	
	
	$data_region=$region->select_regiones($sess_codpais);
	$flgnoadm="1";
	if($usuario_name=='cgarcia' or $usuario_name=='pkuriyama' or $usuario_name=='smostiga' or $usuario_name=='lcorreia')
		$flgnoadm="0";
	$data_rol=$roles->select_roles($sess_codpais,$flgnoadm);
	$data_programa=$programa->selec_programasbypais($sess_codpais,$flgactivo=1);
		
    include("../vista/auditor/frm_detalle.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proAuditor'){
	
	$id_auditor=$_POST['id_auditor'];
	//$data_res=$auditor->selec_one_auditor($id_auditor);
	
	$data_res=$auditor->select_auditorPrograma($id_auditor);
	if($data_res['gprograma']!='')
		$arr_gprograma=explode(",",$data_res['gprograma']);

	
	$data_grol=$auditor->selec_data_roldetalle($id_auditor);
		
	$data_programa=$programa->selec_programasbypais($sess_codpais,$flgactivo=1);
		
    include("../vista/auditor/frm_programa.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='modAuditor'){

	$id_auditor=$_POST['id_auditor'];
	
	$data_au=$auditor->selec_one_auditor($id_auditor);

	$data_res=$auditor->selec_audixprogramaxmodulo($id_auditor);
	
	$datamodulo=$programa->selec_modulosxpogramabyselec($sess_codpais);
	foreach($datamodulo as $row){
		
		$arraymodulo[$row['id_programa']][$row['id_modulo']]=$row['modulo'];
	}
	
    include("../vista/auditor/frm_modulo.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detAuditor'){
    // proceso update a la base de datos usuarios
	
	$nombre=$_POST['nombre'];
	$azuread=$_POST['azuread'];
	$apepaterno=$_POST['apepaterno'];
	$apematerno=$_POST['apematerno'];
	$pasaporte=$_POST['pasaporte'];
	$dni=$_POST['dni'];
	//$id_region=$_POST['id_region'];
	$id_region=0;
	$def_programa=$_POST['def_programa'];
	$codigo=$_POST['codigo'];
	$iniciales=$_POST['iniciales'];
	$email=$_POST['email2'];
	$telefono=$_POST['telefono'];
	$movil=$_POST['movil'];
	$costo=$_POST['costo'];
	$colortexto=$_POST['colortexto'];
	$color=$_POST['color'];
	
	$usuario=$_POST['usuario'];
	$clave=$_POST['clave'];
	$flgstatus="0";
	if(!empty($_POST['flgstatus']))
		$flgstatus="1";
	$flgtipo=$_POST['flgtipocontrato'];
	// foto
	// id_programa

	$cadrol="";
	if(!empty($_POST['id_rol'])){
		foreach($_POST['id_rol'] as $id_rol){
			$xvs=1;
		}
	}else
		$id_rol=0;
	
	if(empty($_POST['id_auditor'])){
		$id_auditor=$auditor->insert_auditor($nombre,$apepaterno,$apematerno, $pasaporte,$dni,$id_region ,$def_programa,$codigo,$iniciales, $email, $telefono,$movil,$costo,$colortexto,$color,$id_rol,$usuario,$clave,$flgstatus,$flgtipo,$azuread,$sess_codpais,$usuario_name,$ip);
		// triggert feriados
		$auditor->trigert_actividad($id_auditor);
		//$auditor->trigert_calendario($id_auditor);

	}else{
		$id_auditor=$_POST['id_auditor']; // id
		
		// validacion de si hay cambios para auditoria 18032022
		//************************************************************
		$res=$auditor->selec_one_auditor($id_auditor);
		if($res['id_region']!=$id_region)
			$tblog->insert_log($id_auditor,'prg_auditor','Region','auditor',$id_region,$res['id_region'],$sess_codpais,$usuario_name,$ip);
		
		if($res['nombre']!=$nombre)
			$tblog->insert_log($id_auditor,'prg_auditor','Nombre','auditor',$nombre,$res['nombre'],$sess_codpais,$usuario_name,$ip);
		
		if($res['apepaterno']!=$apepaterno)
			$tblog->insert_log($id_auditor,'prg_auditor','Ape paterno','auditor',$apepaterno,$res['apepaterno'],$sess_codpais,$usuario_name,$ip);
		
		if($res['apematerno']!=$apematerno)
			$tblog->insert_log($id_auditor,'prg_auditor','Ape materno','auditor',$apematerno,$res['apematerno'],$sess_codpais,$usuario_name,$ip);

		if($res['pasaporte']!=$pasaporte)
			$tblog->insert_log($id_auditor,'prg_auditor','Pasaporte','auditor',$pasaporte,$res['pasaporte'],$sess_codpais,$usuario_name,$ip);
		
		if($res['dni']!=$dni)
			$tblog->insert_log($id_auditor,'prg_auditor','Dni','auditor',$dni,$res['dni'],$sess_codpais,$usuario_name,$ip);
		
		if($res['codigo']!=$codigo)
			$tblog->insert_log($id_auditor,'prg_auditor','Codigo','auditor',$codigo,$res['codigo'],$sess_codpais,$usuario_name,$ip);
		
		if($res['iniciales']!=$iniciales)
			$tblog->insert_log($id_auditor,'prg_auditor','Iniciales','auditor',$iniciales,$res['iniciales'],$sess_codpais,$usuario_name,$ip);
		
		if($res['email']!=$email)
			$tblog->insert_log($id_auditor,'prg_auditor','Email','auditor',$email,$res['email'],$sess_codpais,$usuario_name,$ip);
		
		if($res['telefono']!=$telefono)
			$tblog->insert_log($id_auditor,'prg_auditor','Telefono','auditor',$telefono,$res['telefono'],$sess_codpais,$usuario_name,$ip);

		if($res['movil']!=$movil)
			$tblog->insert_log($id_auditor,'prg_auditor','Movil','auditor',$movil,$res['movil'],$sess_codpais,$usuario_name,$ip);

		if($res['costo']!=$costo)
			$tblog->insert_log($id_auditor,'prg_auditor','Costo','auditor',$costo,$res['costo'],$sess_codpais,$usuario_name,$ip);

		if($res['colortexto']!=$colortexto)
			$tblog->insert_log($id_auditor,'prg_auditor','Color texto','auditor',$colortexto,$res['colortexto'],$sess_codpais,$usuario_name,$ip);

		if($res['color']!=$color)
			$tblog->insert_log($id_auditor,'prg_auditor','Color','auditor',$color,$res['color'],$sess_codpais,$usuario_name,$ip);
		
		if($res['flgstatus']!=$flgstatus)
			$tblog->insert_log($id_auditor,'prg_auditor','Status','auditor',$flgstatus,$res['flgstatus'],$sess_codpais,$usuario_name,$ip);

		if($res['flgtipo']!=$flgtipo)
			$tblog->insert_log($id_auditor,'prg_auditor','Tipo','auditor',$flgtipo,$res['flgtipo'],$sess_codpais,$usuario_name,$ip);

		// tabla prg_usuarios
		
		if($res['id_rol']!=$id_rol)
			$tblog->insert_log($id_auditor,'prg_usuarios','Rol','auditor',$id_rol,$res['id_rol'],$sess_codpais,$usuario_name,$ip);

		
		if($res['contrasena']!=md5($clave) and $$clave!='')
			$tblog->insert_log($id_auditor,'prg_usuarios','Clave','auditor',$clave,$res['clave'],$sess_codpais,$usuario_name,$ip);

		if($res['azuread']!=$azuread)
			$tblog->insert_log($id_auditor,'prg_usuarios','Azure','auditor',$azuread,$res['azuread'],$sess_codpais,$usuario_name,$ip);

		if($res['usuario']!=$usuario)
			$tblog->insert_log($id_auditor,'prg_usuarios','Usuario','auditor',$usuario,$res['usuario'],$sess_codpais,$usuario_name,$ip);

		//************************************************************
		//************************************************************
		
		$auditor->update_auditor($id_auditor,$nombre,$apepaterno,$apematerno, $pasaporte,$dni,$id_region ,$def_programa, $codigo,$iniciales, $email, $telefono,$movil,$costo,$colortexto,$color,$id_rol,$usuario,$clave,$flgstatus,$flgtipo,$azuread,$sess_codpais,$usuario_name,$ip);
		
	}	
	
	// para obtener el rol e insertar 
	$cadrol="";
	if(!empty($_POST['id_rol'])){
		foreach($_POST['id_rol'] as $id_rol){
			if($cadrol=='')
				$cadrol= "($id_auditor, $id_rol,'$sess_codpais')";
			else
				$cadrol.= ",($id_auditor, $id_rol,'$sess_codpais')";
		}
	
		if($cadrol!=''){
			$cadrol="insert into prg_auditorxrol (id_auditor, id_rol,id_pais) values " . $cadrol;
			$auditor->insert_auditorxrol($cadrol,$id_auditor);
			
		}
	}
	// fin insertar relacion rol
	
	// para obtener la region e insertar 
	$cadregion="";
	if(!empty($_POST['id_region'])){
		foreach($_POST['id_region'] as $id_region){
			if($cadregion=='')
				$cadregion= "($id_auditor, $id_region)";
			else
				$cadregion.= ",($id_auditor, $id_region)";
		}
	
		if($cadregion!=''){
			$cadregion="insert into prg_auditor_region (id_auditor, id_region) values " . $cadregion;
			$auditor->insert_auditorxregion($cadregion,$id_auditor);
			
		}
	}
	// fin insertar relacion la region
	
	// subir la foto
	$foto=uploadFile($_FILES,$pathFoto,'foto');
	if(substr($foto, 0,5)!='Error' )
		$auditor->update_auditorFoto($id_auditor,$foto);
	// foto
	
	/*
	$auditor->delete_auditorPrograma($id_auditor);
	if(!empty($_POST['programa'])){
		foreach($_POST['programa'] as $id_programa){
			$auditor->insert_auditorPrograma($id_auditor,$id_programa);
		}
	}
	*/
	
	 echo $id_auditor;

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_modAuditor'){
    
	$id_auditor=$_POST['id_auditor']; // id
	$auditor->delete_auditorProgramaModulo($id_auditor);
	
	$data_res=$auditor->selec_audixprogramaxmodulo($id_auditor);
	
	foreach($data_res as $row){
		$id_programa=$row['id_programa'];
		$id_rol=$row['id_rol'];
		$llave="id_modulo_".$id_rol."_".$id_programa;
		
		if(!empty($_POST[$llave])){
			foreach($_POST[$llave] as $id_modulo){
				$auditor->insert_auditorProgramaModulo($id_auditor,$id_programa,$id_rol,$id_modulo);
			}
		}
	}
	 echo $id_auditor;

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_proAuditor'){
    
	$id_auditor=$_POST['id_auditor']; // id
	
	$auditor->delete_auditorPrograma($id_auditor);
	$data_grol=$auditor->selec_data_roldetalle($id_auditor);
	
	foreach($data_grol as $row){
		$id_rol=$row['id_rol'];
		if(!empty($_POST['programa_'.$id_rol])){
			foreach($_POST['programa_'.$id_rol] as $id_programa){
				$auditor->insert_auditorPrograma($id_auditor,$id_programa,$id_rol);
			}
		}
	}
	 echo $id_auditor;

	 
}else if(!empty($_POST['accion']) and $_POST['accion']=='accAuditor'){
	$id_auditor=$_POST['id_auditor'];
	$data_res=$auditor->selec_one_auditor($id_auditor);
	$id_usuario=$data_res['id_usuario'];
	include("../vista/auditor/frm_accion.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_accAuditor'){
    // delete a la base de datos usuarios
	$id_auditor=$_POST['id_auditor']; 
	$id_usuario=$_POST['id_usuario']; 
	
	$flgcomercial="0";
	$flgemailcuota="0";
	$flgemailvencido="0";
	$flgemailfactura="0";
	$flgadminsli="0";
	$flgudcal="0";

	if(!empty($_POST['flgadminsli']))
		$flgadminsli=1;
	if(!empty($_POST['flgcomercial']))
		$flgcomercial=1;
	if(!empty($_POST['flgemailcuota']))
		$flgemailcuota=1;
	if(!empty($_POST['flgemailvencido']))
		$flgemailvencido=1;
	if(!empty($_POST['flgemailfactura']))
		$flgemailfactura=1;
	if(!empty($_POST['flgudcal']))
		$flgudcal=1;
	
    $auditor->update_usuario_accion($id_usuario,$flgcomercial,$flgemailcuota,$flgemailvencido,$flgemailfactura,$flgadminsli,$flgudcal);
    echo "Se elimino el registro.";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delAuditor'){
    // delete a la base de datos usuarios
	$id_auditor=$_POST['id_auditor']; 
    $auditor->delete_auditor($id_auditor);
    echo "Se elimino el registro.";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='modClave'){
	
	$data_res=$auditor->selec_one_auditor($sess_codauditor);
	$id_usuario=$data_res['id_usuario'];
	include("../vista/auditor/frm_clave.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_claveAuditor'){
    // proceso update a la base de datos usuarios
	
	$nombre=$_POST['nombre'];
	$apepaterno=$_POST['apepaterno'];
	$apematerno=$_POST['apematerno'];
	$pasaporte=$_POST['pasaporte'];
	$dni=$_POST['dni'];
	$codigo=$_POST['codigo'];
	$iniciales=$_POST['iniciales'];
	$email=$_POST['email2'];
	$telefono=$_POST['telefono'];
	$movil=$_POST['movil'];
	$clave=$_POST['clave'];
	
	
	$auditor->update_auditorClave($sess_codauditor,$nombre,$apepaterno,$apematerno, $pasaporte,$dni,$codigo,$iniciales, $email, $telefono,$movil,$clave,$sess_codpais,$usuario_name,$ip);
	
	
	// subir la foto
	$foto=uploadFile($_FILES,$pathFoto,'foto');
	if(substr($foto, 0,5)!='Error' )
		$auditor->update_auditorFoto($sess_codauditor,$foto);
	// foto
	
	 echo "Se actualizo los datos del registro";

}else if(!empty($_POST['accion']) and $_POST['accion']=='expAuditor'){
	$descripcion=$_POST['descripcion'];
	
	if(!empty($_POST['flgtipo']))
		$flgtipo = $_POST['flgtipo'];
	
	if(!empty($_POST['id_rol']))
		$id_rol = $_POST['id_rol'];
	
	if(!empty($_POST['flgstatus']))
		$flgstatus = $_POST['flgstatus'];

	$columnName=" Auditor.nombre";
	$columnSortOrder=" asc ";
	$searchQuery = " and Auditor.id_pais = '$sess_codpais' ";

	if(!empty($id_rol))
		$searchQuery.= " and Usuario.id_rol = '$id_rol' ";
	
	if(!empty($flgtipo))
		$searchQuery.= " and Auditor.flgtipo = '$flgtipo' ";

	if(!empty($descripcion))
		$searchQuery.=" and CONCAT_WS(' ',Auditor.nombre, Auditor.apepaterno,Auditor.apematerno) like '%$descripcion%'  ";
	
	if(!empty($flgstatus) and $flgstatus=='1')
		$searchQuery.= " and Auditor.flgstatus = '$flgstatus' ";
	else if(!empty($flgstatus) and $flgstatus=='2')
		$searchQuery.= " and Auditor.flgstatus = '0' ";
	
	$row	=0;
	$rowperpage=1000000;
	## Fetch records
	$data_Per=$auditor->select_auditor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	include("../vista/auditor/data_exporta.php");
	
	/*
	require_once '../assets/PHPExcel/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->
	getProperties()
	->setCreator("enrique bazalar")
	->setTitle("Exportar")
	->setSubject("Reportes")
	->setKeywords("control union reportes")
	->setCategory("reportes");

	// hoja 1
	$border_style= array('borders' => array('allborders' => array('style' => 
		PHPExcel_Style_Border::BORDER_THIN,'color' => array('argb' => '766f6e'),)));
		
	$background_style= array('fill' => array('type' =>  
		PHPExcel_Style_Fill::FILL_SOLID,'color' => array('argb' => 'cdcdcd'),));

	$font_style6= array('font' =>  array('size' => 12,'bold' => true,'color' =>array('argb' => '000000') ),);
	$font_style16= array('font' =>  array('size' => 16,'bold' => true,'color' =>array('argb' => '000000') ),);
	$font_style= array('font' =>  array('size' => 10,'bold' => false,'color' =>array('argb' => '000000') ),);

	$center_style= array('alignment' =>  array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER ),);

	$bloquefiltro="";
	if(!empty($descripcion))
		 $bloquefiltro="$lang_buscar : $descripcion";
					 
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B2', 'Reporte de Auditores')
		->setCellValue('B3', $bloquefiltro)
		;

	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B5', '#')
		->setCellValue('C5', caracterlimpia($lang_nombre))
		->setCellValue('D5', caracterlimpia($lang_auditores[12]))
		->setCellValue('E5', caracterlimpia($lang_auditores[25]))
		->setCellValue('F5', caracterlimpia($lang_auditores[7]))
		->setCellValue('G5', caracterlimpia($lang_auditores[22]))
		->setCellValue('H5', caracterlimpia($lang_auditores[23]))
		->setCellValue('I5', caracterlimpia($lang_auditores[15]))
		->setCellValue('J5', caracterlimpia($lang_auditores[20]))
		->setCellValue('K5', caracterlimpia($lang_ejecutivo_comercial))
		->setCellValue('L5', caracterlimpia($lang_cuota_por_vencer))
		->setCellValue('M5', caracterlimpia($lang_email_cuota_vencida))
		->setCellValue('N5', caracterlimpia($lang_email_facturacion));
	
	$itable2=5;
	foreach($data_Per as $row) {
		$itable2++;
		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B'.$itable2, $itable2-5)
		->setCellValue('C'.$itable2, $row['nombreCompleto'])
		->setCellValue('D'.$itable2, $row['usuario'])
		->setCellValue('E'.$itable2, $row['roles'])
		->setCellValue('F'.$itable2, $row['iniciales'])
		->setCellValue('G'.$itable2, $row['costo'])
		->setCellValue('H'.$itable2, $row['color'])
		->setCellValue('I'.$itable2, $row['flgtipo'])
		->setCellValue('J'.$itable2, $row['dscestatus'])
		->setCellValue('K'.$itable2, $row['dscestatus'])
		->setCellValue('L'.$itable2, $row['dsccuota'])
		->setCellValue('M'.$itable2, $row['dscvencido'])
		->setCellValue('N'.$itable2, $row['dscfactura']);
	}	
	
	
	$objPHPExcel->getActiveSheet()->setTitle('Auditores');
	// estilos
	$objPHPExcel->getActiveSheet()
					->getStyle('B2:C2')->applyFromArray($font_style16);
					
	$objPHPExcel->getActiveSheet()
					->getStyle('B5:N'.$itable2)->applyFromArray($border_style);
	$objPHPExcel->getActiveSheet()
					->getStyle('B5:N5')->applyFromArray($font_style6);
	$objPHPExcel->getActiveSheet()
					->getStyle('B6:N'.$itable2)->applyFromArray($font_style);
					
	$objPHPExcel->getActiveSheet()
					->getStyle('B5:N5')->applyFromArray($background_style);	
	
	$objPHPExcel->getActiveSheet()
					->getStyle('B5:N5')->applyFromArray($center_style);	
					
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
					
	$objPHPExcel->setActiveSheetIndex(0);

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
	ob_start();
	$objWriter->save('php://output');
	$xlsData = ob_get_contents();
	ob_end_clean();
	echo  "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,". base64_encode($xlsData);

	exit;
	*/
}


?>
