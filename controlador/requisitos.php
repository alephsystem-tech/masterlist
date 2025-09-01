<?php

include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_requisito_modelo.php");
include("../modelo/prg_modulo_modelo.php");
include("../modelo/prg_pais_modelo.php");

$prgrequisito=new prg_requisito_model();
$prgmodulo=new prg_modulo_model();
$prgpais=new prg_pais_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

$pathFile='archivos/calificaDocumento/';
//***********************************************************

	//**********************************
	// configuracion de requisitos
	//**********************************
	
if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	
	$data_categoria=$prgrequisito->selec_categoria($sess_codpais);
	$dataprograma=$prgrequisito->selec_programa($sess_codpais);
	
    include("../vista/requisitos/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_requisito'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	$descripcion = $_POST['descripcion'];
	$codcategoria = $_POST['codcategoria'];
	
	$id_programa="";
	foreach($_POST['id_programa'] as $id){
		if($id_programa=='') $id_programa=$id;
		else $id_programa.=",".$id;
	}
	
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" u.nombres";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " ";
	
	## Total number of records without filtering
	$data_maxOF=$prgrequisito->selec_total_requisito($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( codigo like '%$descripcion%' or requisito like '%$descripcion%' )";
	
	if(!empty($codcategoria))
		$searchQuery.=" and prg_requisito.codcategoria=$codcategoria ";
	
	## Total number of record with filtering
	$data_maxOF2=$prgrequisito->selec_total_requisito($searchQuery,$id_programa);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgrequisito->select_requisitos($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_programa);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codrequisito'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_edirequisito'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_elirequisito'><i class='fas fa-trash'></i> </button>";
			// $roles="<button type='button' id='estproy_". $id ."'  class='btn  btn_rolrequisito'><i class='fas fa-user-circle'></i> </button>";

			if($row['novence']=='1')
				$tctfrec="No vence";
			else
				$tctfrec=$row['frecuencia'];
			
		   $data[] = array( 
			   "requisito"=>str_replace('"','',json_encode($row['requisito'],JSON_UNESCAPED_UNICODE)),
			   "codrequisito"=>$id,
			   "codcategoria"=>$row['codcategoria'],
			   "codigo"=>$row['codigo'],
			   "codtipo"=>$row['codtipo'],
			   "frecuencia"=>$tctfrec,
				"descripcion"=>str_replace("\n", '<br>', $row['descripcion']),
				"comentario"=>str_replace("\n", '<br>', $row['comentario']),
			   "categoria"=>$row['categoria'],
			   "tipo"=>$row['tipo'],
			   "novence"=>$row['novence'],
			   "programa"=>$row['programa'],
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='expRequisito'){

$descripcion = $_POST['descripcion'];
	$codcategoria = $_POST['codcategoria'];
	$id_programa = $_POST['id_programa'];
	
	$columnName=" prg_requisito.codrequisito ";
	$columnSortOrder=" desc ";
	
	## Search  oculto
	$searchQuery = " ";
	
	if(!empty($descripcion))
		$searchQuery.=" and ( codigo like '%$descripcion%' or requisito like '%$descripcion%' )";
	
	if(!empty($codcategoria))
		$searchQuery.=" and prg_requisito.codcategoria=$codcategoria ";
	
	$row=0;
	$rowperpage=10000;
	$data_OF=$prgrequisito->select_requisitos($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_programa);
	
	include("../vista/requisitos/xls_requisito.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='expAsigRequisito'){

	$descripcion = $_POST['descripcion'];
	
	$columnName=" 2 ";
	$columnSortOrder=" asc ";
	
	## Search  oculto
	$searchQuery = " p.flag='1' AND m.flag='1' AND c.id_usuario=$sess_codusuario ";
	
	if(!empty($descripcion))
		$searchQuery.=" and ( p.descripcion like '%$descripcion%' or m.modulo like '%$descripcion%' )";
	
	
	$row=0;
	$rowperpage=10000;
	$data_OF=$prgrequisito->selec_programaxmodulo($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_programa);
	
	include("../vista/requisitos/xls_asigrequisito.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='editrequisito'){
	$codrequisito="";
	$data_categoria=$prgrequisito->selec_categoria($sess_codpais);
	$data_tipo=$prgrequisito->selec_tipo($sess_codpais);
	
	if(!empty($_POST['codrequisito'])){
		$codrequisito=$_POST['codrequisito'];
		$data_res=$prgrequisito->selec_one_requisito($codrequisito);
	
		$data_gprg=$prgrequisito->selec_reqxprograma($codrequisito);
			if($data_gprg['gprograma']!='')
				$arr_gprograma=explode(",",$data_gprg['gprograma']);
	}	
	
	$dataprograma=$prgrequisito->selec_programa($sess_codpais);

    include("../vista/requisitos/frm_detalle.php");


}else if(!empty($_POST['accion']) and $_POST['accion']=='busrequisito'){
	$codrequisito=$_POST['codrequisito'];
	$codigo=$_POST['codigo'];
	$dataTo=$prgrequisito->select_exis_requisito($codrequisito,$codigo);
	if($dataTo['total']>0)
		echo "El codigo de requisito ya esta registrado.";	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detrequisito'){
    // proceso update a la base de datos usuarios
	
	$requisito=$_POST['requisito'];
	$codcategoria=$_POST['codcategoria'];
	$codigo=$_POST['codigo'];
	$codtipo=$_POST['codtipo'];
	$frecuencia=$_POST['frecuencia'];
	$descripcion=$_POST['desc_requisito'];
	$comentario=$_POST['comentario'];
	$novence=0;
	if(!empty($_POST['novence']))
		$novence=1;
	
	if(empty($_POST['codrequisito']))
		$codrequisito=$prgrequisito->insert_requisito($requisito,$codcategoria,$codigo,$codtipo,$frecuencia,$descripcion,$comentario,$novence,$sess_codpais,$usuario_name,$ip);
	else{
		$codrequisito=$_POST['codrequisito']; // id
		$prgrequisito->update_requisito($codrequisito,$requisito,$codcategoria,$codigo,$codtipo,$frecuencia,$descripcion,$comentario,$novence,$sess_codpais,$usuario_name,$ip);
	}	
	
	
	$prgrequisito->delete_requisitoxprog($codrequisito);
	foreach($_POST['id_programa'] as $id_programa){
		$prgrequisito->insert_requisitoxprog($codrequisito,$id_programa);
	}
	
	 echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delrequisito'){
    // delete a la base de datos usuarios
	$codrequisito=$_POST['codrequisito']; 
    $prgrequisito->delete_requisito($codrequisito);
    echo "Se elimino el registro.";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='rolrequisito'){
	$codrequisito=$_POST['codrequisito']; 
	$data_res=$prgrequisito->selec_one_requisito($_POST['codrequisito']);
	$codtipo=$data_res['codtipo']; //3 calidad,1 y 2 otros
	
	$data_rol=$prgrequisito->selec_rol($codrequisito);
	$data_programa=$prgrequisito->selec_programa($sess_codpais);
	
    include("../vista/requisitos/frm_roldetalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detrolrequisito'){
   
	$codrequisito=$_POST['codrequisito']; 
	$prgrequisito->delete_reqxrolxprograma($codrequisito);
	
	$data_rol=$prgrequisito->selec_rol($codrequisito);
	 foreach($data_rol as $row){
		 $id_rol=$row['id_rol'];
		 $control='id_programa_'.$id_rol;
		
		 if(!empty($_POST[$control])){
			
			 foreach($_POST[$control] as $id_programa){
				 $prgrequisito->insert_reqxrolxprograma($id_rol,$id_programa,$codrequisito);
			 }
		 }else if(!empty($_POST['rolchk_'.$id_rol])){
			  $prgrequisito->insert_reqxrolxprograma($id_rol,0,$codrequisito);
			 
		 }
	 }
	

    echo "Se actualizo el registro.";
	
	//**********************************
	// calificadores de programa
	//**********************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_calif'){

    include("../vista/requisitos/index_calif.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_califica'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" descripcion";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " AND flgactivo='1' AND descripcion!='' "; // and prg_programa.id_pais='$sess_codpais'
	
	## Total number of records without filtering
	$data_maxOF=$prgrequisito->selec_total_programa($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and descripcion like '%$descripcion%' ";
	
	## Total number of record with filtering
	$data_maxOF2=$prgrequisito->selec_total_programa($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgrequisito->select_programa_lst($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_programa'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_edicalifica'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_elicalifica'><i class='fas fa-trash'></i> </button>";
					
		   $data[] = array( 
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			    "calificadores"=>str_replace('"','',json_encode($row['calificadores'],JSON_UNESCAPED_UNICODE)),
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
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='editcalifica'){
	
	$data_usuario=$prgrequisito->selec_usuarios($sess_codpais);
	$id_programa=$_POST['id_programa'];
	$data_programa=$prgrequisito->selec_one_programa($id_programa);
	if($data_programa['arr_gusuario']!='')
		$arr_gusuario=explode(",",$data_programa['arr_gusuario']);
	   
	include("../vista/requisitos/frm_califica.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detcalifica'){
   
	$id_programa=$_POST['id_programa']; 
	$prgrequisito->delete_calificaxuser($id_programa);
	
	 foreach($_POST['califica'] as $id_usuario)
		$prgrequisito->insert_calificaxuser($id_programa,$id_usuario);

    echo "Se actualizo el registro.";		
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delcalifica'){
    // delete a la base de datos usuarios
	$id_programa=$_POST['id_programa']; 
    $prgrequisito->delete_calificaxuser($id_programa);
    echo "Se elimino el registro.";	
	
	//**********************************
	// seleccion de usuarios
	//**********************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_selec'){

    include("../vista/requisitos/index_selec.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_select_user'){	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" u.nombres ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " ";
	
	## Total number of records without filtering
	$data_maxOF=$prgrequisito->selec_total_usuarios($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and u.nombres like '%$descripcion%' ";
	
	## Total number of record with filtering
	$data_maxOF2=$prgrequisito->selec_total_usuarios($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgrequisito->selec_usuarios_cal($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_auditor'];
			$chec="";
			if($row['flgcalifica']=='1') $chec=" checked ";
			
			$detalle="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediseleccion'><i class='fas fa-edit'></i> </button>";
			$seleccion="<div class='custom-control custom-switch  custom-switch-off-danger custom-switch-on-success'>
						  <input type='checkbox' onChange='js_changeseleccion($id)' class='custom-control-input' name='seleccion_". $id ."' id='seleccion_". $id ."' $chec >
						  <label class='custom-control-label' for='seleccion_". $id ."'></label>
					</div> ";
			
		
		   $data[] = array( 
			   "auditor"=>str_replace('"','',json_encode($row['auditor'],JSON_UNESCAPED_UNICODE)),
			   "id_auditor"=>$id,
			   "pais"=>$row['pais'],
			   "usuario"=>$row['usuario'],
			   "rol"=>$row['rol'],
			   "programa"=>$row['programa'],
			   "id_usuario"=>$row['id_usuario'],
			   "detalle"=>$detalle,
			   "seleccion"=>$seleccion,
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
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delevaluacion'){
    // delete a la base de datos usuarios
	$id_auditor=$_POST['id_auditor']; 
	$flgcalifica=$_POST['flgcalifica']; 
    $prgrequisito->delete_evaluacionxuser($id_auditor,$flgcalifica);
    echo "Se elimino el registro.";	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='verauditor'){
	$id_auditor=$_POST['id_auditor'];
	$data_res=$prgrequisito->selec_one_auditor($id_auditor);
	$data_resdetalle=$prgrequisito->selec_det_usuarios($id_auditor);

    include("../vista/requisitos/frm_detalleselec.php");	

	//**********************************
	// autorizacion de usuarios
	//**********************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_aut'){

	$data_res=$prgrequisito->selec_one_auditor($sess_codauditor);
	$flgcalifica=$data_res['flgcalifica'];
	$flgudcal=$data_res['flgudcal'];
	$id_auditor=$sess_codauditor;
	$id_pais=$sess_codpais;
    
	if($flgcalifica=='1'){
		
		// entra seccion
		if($flgudcal=='1'){
			// permiso de ver lista
			$data_audcal=$prgrequisito->selec_all_usercalifica($sess_codpais);
			$data_pais=$prgpais->selec_paises();
			
			if(!empty($data_audcal) and empty($flgcalifica))
				$id_auditor=$data_audcal[0]['id_auditor'];
		}else
			$id_auditor=$sess_codauditor;
		
		include("../vista/requisitos/index_aut.php");
	}else
		echo "<br><br><center><p>Ud. no tiene acceso a esta secci&oacute;n.</p></center>";


}else if(!empty($_POST['accion']) and $_POST['accion']=='changePais'){	
	## Read value
	$id_pais = $_POST['id_pais'];
	$data_audcal=$prgrequisito->selec_all_usercalifica($id_pais);
	include("../vista/requisitos/frm_pais.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_aut_user'){	
	## Read value
	$descripcion = $_POST['descripcion'];
	$id_auditor = $_POST['id_auditor'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" prg_programa.descripcion ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " ";
	
	## Total number of records without filtering
	$data_maxOF=$prgrequisito->selec_total_estado_calif($searchQuery,$id_auditor);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and (programa  like '%$descripcion%' or modulo  like '%$descripcion%') ";
	
	## Total number of record with filtering
	$data_maxOF2=$prgrequisito->selec_total_estado_calif($searchQuery,$id_auditor);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgrequisito->selec_estado_calif($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_auditor);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['llave'];
			$estado='NO AUTORIZADO';
			$vigencia="";
			
			$tt=$row['tt'];
			
			
			$usuario_califica="";
			if($row['tot_req_eval']==$tt){
				$estado='AUTORIZADO';
				$vigencia=$tt[1];
				$usuario_califica='pkuriyama';
			}	
			
			$data[] = array( 
			   "programa"=>str_replace('"','',json_encode($row['programa'],JSON_UNESCAPED_UNICODE)),
			   "rol"=>$row['rol'],
			   "modulo"=>$row['modulo'],
			   "tot_req_eval"=>$row['tot_req_eval'],
			    "usuario_califica"=>$usuario_califica,
			   "tot_req_cal"=>$tt,
			   "vigencia"=>$vigencia,
			   "estado"=>$estado,
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
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='edicaliguser'){
	
	$id_auditor = $_POST['id_auditor'];
	$data_aud=$prgrequisito->selec_one_auditor($id_auditor);
	$id_pais=$data_aud['id_pais'];
	$dataprograma=$prgrequisito->selec_programa($id_pais,$id_auditor);
	$datamodulo=$prgrequisito->selec_modulobyaud($id_auditor,$id_pais);
	$datarol=$prgrequisito->selec_rolbyaud($id_auditor);
	
    include("../vista/requisitos/detalle_aut.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='edicaliguser_data'){	
	## Read value
	$descripcion = $_POST['descripcion'];
	$id_auditor = $_POST['id_auditor'];
	
	
	$id_programa="";
	foreach($_POST['id_programa'] as $id){
		if($id_programa=='') $id_programa=$id;
		else $id_programa.=",".$id;
	}
	
	$id_rol="";
	foreach($_POST['id_rol'] as $id){
		if($id_rol=='') $id_rol=$id;
		else $id_rol.=",".$id;
	}
	
	$id_modulo="";
	foreach($_POST['id_modulo'] as $id){
		if($id_modulo=='') $id_modulo=$id;
		else $id_modulo.=",".$id;
	}
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" r.codrequisito ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " AND apm.id_auditor=$id_auditor ";
	
	## Total number of records without filtering
	$data_maxOF=$prgrequisito->selec_total_estado_req($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and (requisito like '%$descripcion%' or r.codigo like '%$descripcion%' )";
	
	if(!empty($id_rol))
		$searchQuery.=" and prg_roles.id_rol in ($id_rol)";
	
	if(!empty($id_modulo))
		$searchQuery.=" and prg_prog_modulo.id_modulo in ($id_modulo)";
	
	
	if(!empty($id_programa))
		$searchQuery.=" and prg_programa.id_programa in ($id_programa)";
	
	## Total number of record with filtering
	$data_maxOF2=$prgrequisito->selec_total_estado_req($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgrequisito->selec_estado_req($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['llave'];
			$detalle="<button type='button' id='estproy_". $id ."'  class='btn  btn_addcalguser'><i class='fas fa-edit'></i> </button>";
			$historico="<button type='button' id='estproy_". $id ."'  class='btn  btn_histcalguser'><i class='fas fa-table'></i> </button>";
		
			$vigencia="";
			if($row['estado']=='CALIFICADO')
				$vigencia=$row['vigenciatxt'];
		
			if($row['estado']=='PENDIENTE')
				$ingreso='';
			else		
				$ingreso=$row['ingreso'];
		
			$data[] = array( 
			   "categoria"=>str_replace('"','',json_encode($row['categoria'],JSON_UNESCAPED_UNICODE)),
			   "requisito"=>str_replace('"','',json_encode($row['requisito'],JSON_UNESCAPED_UNICODE)),
			   "codigo"=>$row['codigo'],
			   "codrequisito"=>$row['codrequisito'],
			   "estado"=>$row['estado'],
			   "fecha"=>$row['fecha'],
			   "ingreso"=>$ingreso,
			   "calificacion"=>$row['calificacion'],
			   "vigencia"=>$vigencia,
			   "modulo"=>$row['modulo'],
			   "programa"=>$row['programa'],
			   "rol"=>$row['rol'],
			   "detalle"=>$detalle,
			   "historico"=>$historico,
			   "id"=>$id,
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
	

// delete documento de requisito
}else if(!empty($_POST['accion']) and $_POST['accion']=='deleteDocumentoReq'){
	
	$id=$_POST['id'];
	$data_cal=$prgrequisito->delete_one_doccalificauser($id);	
	echo "Se elimino el documento adjunto.";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='vercalifuser'){
	
	$codrequisito=$_POST['codrequisito'];
	$id_auditor=$_POST['id_auditor'];
	$id="";
	if(!empty($_POST['id'])){
		$id=$_POST['id'];
		$data_cal=$prgrequisito->selec_one_calificauser($id);	
	}	

	$data_aud=$prgrequisito->selec_one_auditor($id_auditor);
	$data_req=$prgrequisito->selec_one_requisito($codrequisito);
	
    include("../vista/requisitos/frm_upload.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_vercalifuser'){
	
	$codrequisito=$_POST['codrequisito'];
	$comentario=$_POST['comentario'];
	$id_auditor=$_POST['id_auditor'];
	
	$data_aud=$prgrequisito->selec_one_auditor($id_auditor);
	$id_pais=$data_aud['id_pais'];
	
	$id=0;
	
	$codestado='ENTREGADO';
	if(!empty($_POST['id'])){
		// update
		$id=$_POST['id'];
		$prgrequisito->update_evidenciacalifica($id,$comentario,$id_pais,$usuario_name,$ip);
		
		$data=$prgrequisito->selec_one_calificauser($id);
		//if($data['codestado']=='NO CALIFICADO')
			
	}else{
		// insert
		$id=$prgrequisito->insert_evidenciacalifica($id_auditor,$codestado,$codrequisito,$comentario,$id_pais,$usuario_name,$ip);
	}
	
	$file=uploadFile($_FILES,$pathFile,'file');
	if(substr($file, 0,5)!='Error' ){
		$prgrequisito->update_evidenciacalifica_file($id,$file);
		$prgrequisito->update_evidenciacalifica_estado($id,$codestado);
	}else{
		$codestado='PENDIENTE';
		$prgrequisito->update_evidenciacalifica_estado($id,$codestado);
	}
	echo "Se adjunto el documento.";
	
//**********************************************
// reporte de calificaciones
//**********************************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='reporte_cal'){

	$dataprograma=$prgrequisito->selec_programa($sess_codpais);
	$data_usuario=$prgrequisito->selec_usuarios_all('1');
	$data_rol=$prgrequisito->selec_rolbypais();
	$data_requisito=$prgrequisito->selec_reqbypais($sess_codpais);
	
	include("../vista/requisitos/reporte_cal.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='reporte_cal_data'){	
	## Read fechavigf
	$descripcion = $_POST['descripcion'];
	
	$id_programa = $_POST['id_programa'];
	$id_auditor=$_POST['id_auditor'];
	$id_rol=$_POST['id_rol'];
	$codrequisito=$_POST['codrequisito'];
	$codestado=$_POST['codestado'];
	
	
	if(!empty($_POST['fechasoli']))
		$fechasoli=formatdatedos($_POST['fechasoli']);
	
	if(!empty($_POST['fechasolf']))
		$fechasolf=formatdatedos($_POST['fechasolf']);
		
	if(!empty($_POST['fechavigi']))
		$fechavigi=formatdatedos($_POST['fechavigi']);
	
	if(!empty($_POST['fechavigf']))
		$fechavigf=formatdatedos($_POST['fechavigf']);
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" r.codrequisito ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " ru.codestado != 'PENDIENTE' and ru.flag='1' AND q.flag='1'   and flgisactivo='1' AND ppc.id_usuario=$sess_codusuario ";
	## Total number of records without filtering
	$data_maxOF=$prgrequisito->selec_total_repcalifica_req($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( requisito like '%$descripcion%' or q.codigo like '%$descripcion%') ";
	
	if(!empty($codestado))
		$searchQuery.=" and ru.codestado = '$codestado' ";
	
	if(!empty($id_auditor))
		$searchQuery.=" and a.id_auditor=$id_auditor ";
	
	if(!empty($codrequisito))
		$searchQuery.=" and q.codrequisito=$codrequisito ";
	
	if(!empty($id_programa))
		$searchQuery.=" and rrp.id_programa=$id_programa ";
	
	if(!empty($id_rol))
		$searchQuery.=" and rrp.id_rol=$id_rol ";
	
	if(!empty($fechavigi))
		$searchQuery.=" and to_days(ru.vigencia) >= to_days('$fechavigi') ";
	
	if(!empty($fechavigf))
		$searchQuery.=" and to_days(ru.vigencia) <= to_days('$fechavigf') ";
	
	if(!empty($fechasoli))
		$searchQuery.=" and to_days(q.fecha_ingreso) >= to_days('$fechasoli') ";
	
	if(!empty($fechasolf))
		$searchQuery.=" and to_days(q.fecha_ingreso) <= to_days('$fechasolf') ";
	
	## Total number of record with filtering
	$data_maxOF2=$prgrequisito->selec_total_repcalifica_req($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgrequisito->selec_repcalifica_req($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_rol,$id_programa);
	
	//print_r($data_OF); vigenciatxt
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id'];
			$resetear="";
			$aprobar="";
			$file=$pathFile.$row['adjunto'];
			$fechacal=$row['fechacal'];
			
			if($row['codestado']!='ENTREGADO'){
				$resetear="<button type='button' id='estproy_". $id ."'  class='btn  btn_resetcalguser'><i class='fas fa-undo'></i> </button>";
				$fechacal.=" <button type='button' id='estproy_". $id ."'  class='btn  btn_fechacal'><i class='fas fa-file'></i> </button>";
			}
			if($row['codestado']=='ENTREGADO'){
				$aprobar="<input type='checkbox' name='chkaprob' id='aprob_". $id ."' value='$id'>";
				
			}
			
			$adjunto="<button type='button' id='$file'  class='btn  btn_verFileLista'><i class='fas fa-file'></i> </button>";
			
			$texto="<table class='table table-bordered'>";
			$detalle=$row['detalle'];
			$detalle=explode("!!", $detalle);
			foreach($detalle as $linea){
				$dta=explode("&&", $linea);
				$roled=explode("/", $dta[0]);
				$texto.="<tr><td>$roled[0] </td><td> $dta[1] </td><td> $dta[2] </td><td> $dta[3] </td></tr>";
			}
			$texto.="</table>";
			
			if($row['vigenciatxt']=='NO VENCE'){
				$dias='NO VENCE';
				$frecuencia='NO VENCE';
			}else{
				$dias=$row['dias'];
				$frecuencia=$row['frecuencia'];
			}
/*		
			}else if($row['vigenciatxt']=='NO VENCE' and $row['codestado']!='CALIFICADO'){
				$dias=$row['diascalificado'];
				$frecuencia=$row['frecuencia'];
				
			}else if($row['codestado']!='ENTREGADO'){
				$dias=$row['diasentregado'];
				$frecuencia=$row['frecuencia'];
			}else{
				$dias=$row['diasentregado'];
				$frecuencia=$row['frecuencia'];
			}
*/			
			$vigenciatxt=$row['vigenciatxt'];
			if($row['codestado']!='CALIFICADO')
				$vigenciatxt="";
			
			$data[] = array( 
			   "coment_evid"=>str_replace('"','',json_encode($row['coment_evid'],JSON_UNESCAPED_UNICODE)),
			   "comentario"=>str_replace('"','',json_encode($row['comentario'],JSON_UNESCAPED_UNICODE)),
			   "codestado"=>$row['codestado'],
			   "fechasol"=>$row['fechasol'],
			   "fechaing"=>$row['fechaing'],
			   "fechacal"=>$fechacal,
			   "evidencia"=>$row['evidencia'],
			   "vigenciatxt"=>$vigenciatxt,
			   "vigencia"=>$row['vigencia'],
			   "fullusuario"=>$row['fullusuario'],
			   "comentario_calif"=>$row['comentario_calif'],
			   "texto"=>$texto,
		   
			   "tipo"=>$row['tipo'],
			   "categoria"=>$row['categoria'],
			   "requisito"=>$row['requisito'],
			   "frecuencia"=>$frecuencia,
			   "codigo"=>$row['codigo'],
			   "dias"=>$dias,
			   "usuarioaprueba"=>$row['usuarioaprueba'],
			   "descripcion"=>$row['descripcion'],
			   "novence"=>$row['novence'],
			   "resetear"=>$resetear,
			   "adjunto"=>$adjunto,
			   "aprobar"=>$aprobar,
			   "id"=>$id,
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='expaprobcal'){

	$descripcion = $_POST['descripcion'];
	$id_programa = $_POST['id_programa'];
	$id_auditor=$_POST['id_auditor'];
	$id_rol=$_POST['id_rol'];
	$codrequisito=$_POST['codrequisito'];
	
	
	$searchQuery = " ru.codestado != 'PENDIENTE' and ru.flag='1' AND q.flag='1'   and flgisactivo='1' AND ppc.id_usuario=$sess_codusuario ";
		
	if(!empty($descripcion))
		$searchQuery.=" and ( requisito like '%$descripcion%' or q.codigo like '%$descripcion%') ";
	
	if(!empty($id_auditor))
		$searchQuery.=" and a.id_auditor=$id_auditor ";
	
	if(!empty($codrequisito))
		$searchQuery.=" and q.codrequisito=$codrequisito ";
	
	if(!empty($id_programa))
		$searchQuery.=" and rrp.id_programa=$id_programa ";
	
	if(!empty($id_rol))
		$searchQuery.=" and rrp.id_rol=$id_rol ";
	
	$columnName=" q.codrequisito ";
	$columnSortOrder=" asc ";
	$row=0;
	$rowperpage=10000;
	## Search  oculto
	
	
	if(!empty($descripcion))
		$searchQuery.=" and requisito like '%$descripcion%' ";
	
	
	$data_OF=$prgrequisito->selec_repcalifica_req($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_rol,$id_programa);
	
    include("../vista/requisitos/xls_expaprobcal.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='changeEstado'){
	$cadena = $_POST['cadena'];
			
    include("../vista/requisitos/frm_changestado.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_changeEstado'){
	$cadena = $_POST['cadena'];
	$codestado = $_POST['codestado'];
	$comentario = $_POST['comentario'];
	$prgrequisito->update_changeestado($cadena,$codestado,$comentario,$sess_codusuario,$sess_codpais,$usuario_name,$ip);
	
	echo "Se califico los registros.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='resetcalguser'){
	$id = $_POST['id'];
	$codestado='ENTREGADO';
	$prgrequisito->update_resetestado($id,$codestado,$sess_codusuario,$sess_codpais,$usuario_name,$ip);
	
	echo "Se reseteo el registro.";	


}else if(!empty($_POST['accion']) and $_POST['accion']=='changeFecha'){
	$id = $_POST['id'];
	$data_res=$prgrequisito->selec_one_calificauser($id);

    include("../vista/requisitos/frm_changfecha.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_changeFecha'){
	$id = $_POST['id'];
	$vigencia="";
	$fechacal = formatdatedos($_POST['fechacal']);
	if(!empty($_POST['vigencia']))
		$vigencia = formatdatedos($_POST['vigencia']);
	
	$novence=0;
	if(!empty($_POST['novence']))
		$novence=1;
	
	$prgrequisito->update_fechacal($id,$fechacal,$vigencia,$novence,$usuario_name,$ip);
	
	echo "Se actualizo el registro.";	

	/******************************************
		asignacion de requisitos
		insert tabla prg_programaxmodulo
	********************************************/
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_asig'){
			
    include("../vista/requisitos/index_asigna.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_asig_data'){	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" 2 ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " p.flag='1' AND m.flag='1' AND c.id_usuario=$sess_codusuario ";
	
	## Total number of records without filtering
	$data_maxOF=$prgrequisito->selec_total_programaxmodulo($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( p.descripcion like '%$descripcion%' or m.modulo like '%$descripcion%' )";
	
	## Total number of record with filtering
	$data_maxOF2=$prgrequisito->selec_total_programaxmodulo($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgrequisito->selec_programaxmodulo($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['llave'];
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediprogxmodulo'><i class='fas fa-edit'></i> </button>";
		
			$data[] = array( 
			   "programa"=>str_replace('"','',json_encode($row['programa'],JSON_UNESCAPED_UNICODE)),
			   "modulo"=>str_replace('"','',json_encode($row['modulo'],JSON_UNESCAPED_UNICODE)),
			   "requisito"=>str_replace('"','',json_encode($row['requisito'],JSON_UNESCAPED_UNICODE)),
			   "edita"=>$edita,
			   "id"=>$id,
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
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='ediprogxmodulo'){
			
	$id_programa = $_POST['id_programa'];
	$id_modulo = $_POST['id_modulo'];	
	
	$data_progra=$prgrequisito->selec_one_programa($id_programa);
	$data_mod=$prgmodulo->selec_one_modulo($id_modulo);
	
	$data_res=$prgrequisito->selec_requisito_by_programa($id_programa);
	
	$data_rol=$prgrequisito->selec_rol_habilitado($sess_codpais);
	// array con los seleccinados
	$data_sel=$prgrequisito->selec_rolxprogxmodxreq($id_programa,$id_modulo);
	$i=0;
	foreach($data_sel as $rowse){
		//
		$arrayMd[$i]=$rowse['llave'];
		$i++;
	}
			
    include("../vista/requisitos/detalle_asigna.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='insRolReqProMod'){
			
	$id_programa = $_POST['id_programa'];
	$id_modulo = $_POST['id_modulo'];
	$id_rol = $_POST['id_rol'];
	$codrequisito = $_POST['codrequisito'];
	$flgactivo = $_POST['flgactivo'];
	
	if($flgactivo=='1'){
		// insert
		$prgrequisito->insert_rol_habilitado($id_programa,$id_modulo,$id_rol,$codrequisito);
	}else{
		// delete
		$prgrequisito->delete_rol_habilitado($id_programa,$id_modulo,$id_rol,$codrequisito);
	}
	
	echo "Se registro exitosamente.";
	
}else if(!empty($_GET['accion']) and $_GET['accion']=='eliminadata'){
			
	
	$prgrequisito->eliminadata();
	
	
	echo "Se elimino exitosamente.";	
	
// reporte de calificados

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_repcal'){

	$dataprograma=$prgrequisito->selec_programa($sess_codpais);
	$data_usuario=$prgrequisito->selec_usuarios_all('1');
	$data_rol=$prgrequisito->selec_rolbypais();
	$data_requisito=$prgrequisito->selec_reqbypais($sess_codpais);
	
	include("../vista/requisitos/index_repcal.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_repcal_data'){	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$id_programa = $_POST['id_programa'];
	$id_auditor=$_POST['id_auditor'];
	$id_rol=$_POST['id_rol'];
	$codrequisito=$_POST['codrequisito'];
	$codestado=$_POST['codestado'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" r.codrequisito ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " ru.flag='1' AND q.flag='1' and flgisactivo='1' AND codestado in ('CALIFICADO','NO CALIFICADO')";
	## Total number of records without filtering
	$data_maxOF=$prgrequisito->selec_total_repcalifica_req($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and requisito like '%$descripcion%' ";
	
	
	if(!empty($codestado))
		$searchQuery.=" and ru.codestado = '$codestado' ";
	
	if(!empty($id_auditor))
		$searchQuery.=" and a.id_auditor=$id_auditor ";
	
	if(!empty($codrequisito))
		$searchQuery.=" and q.codrequisito=$codrequisito ";
	
	if(!empty($id_programa))
		$searchQuery.=" and rrp.id_programa=$id_programa ";
	
	if(!empty($id_rol))
		$searchQuery.=" and rrp.id_rol=$id_rol ";
	
	## Total number of record with filtering
	$data_maxOF2=$prgrequisito->selec_total_repcalifica_req($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgrequisito->selec_repcalifica_req($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_rol,$id_programa);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id'];
			$resetear="";
			$aprobar="";
			$file=$pathFile.$row['adjunto'];
			$fechacal=$row['fechacal'];
			
			$adjunto="<button type='button' id='$file'  class='btn  btn_verFileLista'><i class='fas fa-file'></i> </button>";
			
			$texto="<table class='table table-bordered'>";
			$detalle=$row['detalle'];
			$detalle=explode("!!", $detalle);
			foreach($detalle as $linea){
				$dta=explode("&&", $linea);
				$roled=explode("/", $dta[0]);
				$texto.="<tr><td>$roled[0] </td><td> $dta[4] </td><td> $dta[1] </td><td> $dta[2] </td><td> $dta[3] </td></tr>";
			}
			$texto.="</table>";
			
			
		
			$data[] = array( 
			   "coment_evid"=>str_replace('"','',json_encode($row['coment_evid'],JSON_UNESCAPED_UNICODE)),
			   "comentario"=>str_replace('"','',json_encode($row['comentario'],JSON_UNESCAPED_UNICODE)),
			   "codestado"=>$row['codestado'],
			   "fechasol"=>$row['fechasol'],
			   "fechaing"=>$row['fechaing'],
			   "fechacal"=>$fechacal,
			   "evidencia"=>$row['evidencia'],
			   "vigenciatxt"=>$row['vigenciatxt'],
			   "vigencia"=>$row['vigencia'],
			   "fullusuario"=>$row['fullusuario'],
			   "comentario_calif"=>$row['comentario_calif'],
			   "texto"=>$texto,
		   
			   "tipo"=>$row['tipo'],
			   "categoria"=>$row['categoria'],
			   "requisito"=>$row['requisito'],
			   "frecuencia"=>$row['frecuencia'],
			   "codigo"=>$row['codigo'],
			   "dias"=>$row['dias'],
			   "usuarioaprueba"=>$row['usuarioaprueba'],
			   "descripcion"=>$row['descripcion'],
			   "novence"=>$row['novence'],
			   "resetear"=>$resetear,
			   "adjunto"=>$adjunto,
			   "aprobar"=>$aprobar,
			   "id"=>$id,
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
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='expreqcal'){

	
	$id_programa = $_POST['id_programa'];
	$id_auditor=$_POST['id_auditor'];
	$id_rol=$_POST['id_rol'];
	$codrequisito=$_POST['codrequisito'];
	
	
	$searchQuery = " ru.flag='1' AND q.flag='1' AND codestado in ('CALIFICADO','NO CALIFICADO')";
	
	if(!empty($id_auditor))
		$searchQuery.=" and a.id_auditor=$id_auditor ";
	
	if(!empty($codrequisito))
		$searchQuery.=" and q.codrequisito=$codrequisito ";
	
	if(!empty($id_programa))
		$searchQuery.=" and rrp.id_programa=$id_programa ";
	
	if(!empty($id_rol))
		$searchQuery.=" and rrp.id_rol=$id_rol ";
	
	$columnName=" q.codrequisito ";
	$columnSortOrder=" asc ";
	$row=0;
	$rowperpage=10000;
	## Search  oculto
	
	
	if(!empty($descripcion))
		$searchQuery.=" and requisito like '%$descripcion%' ";
	
	
	$data_OF=$prgrequisito->selec_repcalifica_req($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_rol,$id_programa);
	
    include("../vista/requisitos/xls_expreqcal.php");

//*********************************
// reporte de pendientes
//*********************************
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_pend'){

	$dataprograma=$prgrequisito->selec_programa($sess_codpais);
	$data_usuario=$prgrequisito->selec_usuarios_all('1');
	$data_rol=$prgrequisito->selec_rolbypais();
	$data_requisito=$prgrequisito->selec_reqbypais($sess_codpais);
	
	include("../vista/requisitos/index_pendiente.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_pend_data'){	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$id_programa = $_POST['id_programa'];
	$id_auditor=$_POST['id_auditor'];
	$id_rol=$_POST['id_rol'];
	$codrequisito=$_POST['codrequisito'];
	
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" r.codrequisito ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " r.flag='1'  AND (codestado IS NULL OR codestado='PENDIENTE') ";
	## Total number of records without filtering
	$data_maxOF=$prgrequisito->selec_total_pendiente_req($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and r.requisito like '%$descripcion%' ";
		
	if(!empty($id_auditor))
		$searchQuery.=" and au.id_auditor=$id_auditor ";
	
	if(!empty($codrequisito))
		$searchQuery.=" and r.codrequisito=$codrequisito ";
	
	if(!empty($id_programa))
		$searchQuery.=" and prg_programa.id_programa=$id_programa ";
	
	if(!empty($id_rol))
		$searchQuery.=" and prg_roles.id_rol=$id_rol ";
	
	## Total number of record with filtering
	$data_maxOF2=$prgrequisito->selec_total_pendiente_req($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgrequisito->selec_pendiente_req($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codrequisito'];
		
			$data[] = array( 
			   "categoria"=>str_replace('"','',json_encode($row['categoria'],JSON_UNESCAPED_UNICODE)),
			   "requisito"=>str_replace('"','',json_encode($row['requisito'],JSON_UNESCAPED_UNICODE)),
			   "codigo"=>$row['codigo'],
			   "estado"=>$row['estado'],
			   "fecha"=>$row['fecha'],
			   "vigenciatxt"=>$row['vigenciatxt'],
			   "rol"=>$row['rol'],
			   "programa"=>$row['programa'],
			   "modulo"=>$row['modulo'],
			    "tipo"=>$row['tipo'],
				 "dias"=>$row['dias'],
			   "fullauditor"=>$row['fullauditor'],
			   "id_auditor"=>$row['id_auditor'],
			   "pais"=>$row['pais'],
			   "grupo"=>$row['grupo'],
			   "id"=>$id,
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='exppencal'){

	
	$id_programa = $_POST['id_programa'];
	$id_auditor=$_POST['id_auditor'];
	$id_rol=$_POST['id_rol'];
	$codrequisito=$_POST['codrequisito'];
	
	
	$searchQuery = " r.flag='1'  AND (codestado IS NULL OR codestado='PENDIENTE') ";
	
	if(!empty($descripcion))
		$searchQuery.=" and r.requisito like '%$descripcion%' ";
		
	if(!empty($id_auditor))
		$searchQuery.=" and au.id_auditor=$id_auditor ";
	
	if(!empty($codrequisito))
		$searchQuery.=" and r.codrequisito=$codrequisito ";
	
	if(!empty($id_programa))
		$searchQuery.=" and prg_programa.id_programa=$id_programa ";
	
	if(!empty($id_rol))
		$searchQuery.=" and prg_roles.id_rol=$id_rol ";
	
	$columnName=" r.codrequisito ";
	$columnSortOrder=" asc ";
	$row=0;
	$rowperpage=10000;
	## Search  oculto
	
	
	$data_OF=$prgrequisito->selec_pendiente_req($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
    include("../vista/requisitos/xls_expreqpen.php");	
	
//*********************************
// reporte de usuarios autorizados
//*********************************
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_repaut'){

	$dataprograma=$prgrequisito->selec_programa($sess_codpais);
	$data_usuario=$prgrequisito->selec_usuarios_all('1');
	$data_rol=$prgrequisito->selec_rolbypais();
	$data_requisito=$prgrequisito->selec_reqbypais($sess_codpais);
	
	include("../vista/requisitos/index_autorizado.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_repaut_data'){	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$id_programa = $_POST['id_programa'];
	$id_auditor=$_POST['id_auditor'];
	$id_rol=$_POST['id_rol'];
	$codrequisito=$_POST['codrequisito'];
	
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" r.codrequisito ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " r.flag='1'  AND codestado IS NULL ";
	## Total number of records without filtering
	$data_maxOF=$prgrequisito->selec_total_pendiente_req($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and r.requisito like '%$descripcion%' ";
		
	if(!empty($id_auditor))
		$searchQuery.=" and au.id_auditor=$id_auditor ";
	
	if(!empty($codrequisito))
		$searchQuery.=" and r.codrequisito=$codrequisito ";
	
	if(!empty($id_programa))
		$searchQuery.=" and prg_programa.id_programa=$id_programa ";
	
	if(!empty($id_rol))
		$searchQuery.=" and prg_roles.id_rol=$id_rol ";
	
	## Total number of record with filtering
	$data_maxOF2=$prgrequisito->selec_total_pendiente_req($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$prgrequisito->selec_pendiente_req($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codrequisito'];
		
			$data[] = array( 
			   "categoria"=>str_replace('"','',json_encode($row['categoria'],JSON_UNESCAPED_UNICODE)),
			   "requisito"=>str_replace('"','',json_encode($row['requisito'],JSON_UNESCAPED_UNICODE)),
			   "codigo"=>$row['codigo'],
			   "estado"=>$row['estado'],
			   "fecha"=>$row['fecha'],
			   "vigenciatxt"=>$row['vigenciatxt'],
			   "rol"=>$row['rol'],
			   "programa"=>$row['programa'],
			   "modulo"=>$row['modulo'],
			    "tipo"=>$row['tipo'],
			   "fullauditor"=>$row['fullauditor'],
			   "id_auditor"=>$row['id_auditor'],
			   "pais"=>$row['pais'],
			   "grupo"=>$row['grupo'],
			   "id"=>$id,
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

}


?>
