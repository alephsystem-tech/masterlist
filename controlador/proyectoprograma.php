<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_proyecto_programa_modelo.php");
include("../modelo/prg_pais_modelo.php");
include("../modelo/prg_proyectocosto_modelo.php");

$proyectoprograma=new prg_proyecto_programa_model();
$pais=new prg_pais_model();
$proycosto=new prg_proyectocosto_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='index_plan'){
	//**********************************
	// mostrar index de calendario
	//**********************************
    include("../vista/proyectoprograma/index_plan.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_plan_load'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" proyect";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and prg_proyecto.id_pais='$sess_codpais' ";

	
	## Total number of records without filtering
	$data_maxOF=$proyectoprograma->selec_total_proyectoprograma($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and (proyect like '%$descripcion%' or prg_proyecto.project_id like '%$descripcion%'  )";
		
		
	## Total number of record with filtering
	$data_maxOF2=$proyectoprograma->selec_total_proyectoprograma($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$proyectoprograma->select_proyectoprograma($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_proyecto'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediProyCom'><i class='fas fa-edit'></i> </button>";
			
		   $data[] = array( 
			   "proyect"=>str_replace('"','',json_encode($row['proyect'],JSON_UNESCAPED_UNICODE)),
			   "project_id"=>str_replace('"','',json_encode($row['project_id'],JSON_UNESCAPED_UNICODE)),
				"modules"=>str_replace('"','',json_encode($row['modules'],JSON_UNESCAPED_UNICODE)),
				"programas"=>str_replace('"','',json_encode($row['programas'],JSON_UNESCAPED_UNICODE)),
				"city"=>str_replace('"','',json_encode($row['city'],JSON_UNESCAPED_UNICODE)),
				"state"=>str_replace('"','',json_encode($row['state'],JSON_UNESCAPED_UNICODE)),
				"country"=>str_replace('"','',json_encode($row['country'],JSON_UNESCAPED_UNICODE)),
				"telephone"=>str_replace('"','',json_encode($row['telephone'],JSON_UNESCAPED_UNICODE)),
				"mobile"=>str_replace('"','',json_encode($row['mobile'],JSON_UNESCAPED_UNICODE)),
				"fax"=>str_replace('"','',json_encode($row['fax'],JSON_UNESCAPED_UNICODE)),
				"dsccronograma"=>str_replace('"','',json_encode($row['dsccronograma'],JSON_UNESCAPED_UNICODE)),
			   "id_proyecto"=>$id,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='expProyectoComecial'){
	 
	$descripcion = $_POST['descripcion'];
	$codestado = $_POST['codestado'];
	$tipofactura = $_POST['tipofactura'];
	$programa = $_POST['programa'];
	
	## Search  oculto
	$searchQuery = " and prg_proyecto.id_pais='$sess_codpais' ";

	if(!empty($descripcion))
		$searchQuery.=" and (proyect like '%$descripcion%' or prg_proyecto.project_id like '%$descripcion%'  )";
	
	if(!empty($codestado))
		$searchQuery.=" and prg_proyecto_detalle.codestado=$codestado ";
	
	if(!empty($tipofactura))
		$searchQuery.=" and prg_proyecto_detalle.tipofactura='$tipofactura' ";
	
	if(!empty($programa))
		$searchQuery.=" and prg_proyecto.dsc_programa like '%$programa%' ";
	
	$columnName=" proyect";
	$columnSortOrder=" asc ";
	$row=0;
	$rowperpage=100000;
	$data_OF=$proyectoprograma->select_proyectoprograma($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	include("../vista/proyectoprograma/data_exporta.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='editProyectoprograma'){
    // proceso update a la base de datos usuarios
	
	$id_proyecto=$_POST['id_proyecto'];

	// dats geenrales del proyecto
	$data_oneProy=$proyectoprograma->selec_one_proyectoprograma($id_proyecto,$sess_codpais);
	$project_id=$data_oneProy['project_id'];
	
	// botones de detalle proyecto
	//$data_DetProyAnio=$proyectoprograma->selec_anios_detalle_proyectoprograma($id_proyecto,$sess_codpais);
	$data_DetProy=$proyectoprograma->selec_detalle_proyectoprograma($id_proyecto,$sess_codpais);
	
	// existe laboratorio
	$data_Laboratorio=$proyectoprograma->selec_resulLaboratorio($project_id,$sess_codpais);
	// existe analisis
	$data_Analisis=$proyectoprograma->selec_resulAnalisis($project_id,$sess_codpais);
	
	include("../vista/proyectoprograma/frm_detalle.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='viewDetProyecto'){
    // proceso update a la base de datos usuarios
	
	$coddetalle=$_POST['coddetalle'];
	$id_proyecto=$_POST['id_proyecto'];

	$data_oneProy=$proyectoprograma->selec_one_proyectoprograma($id_proyecto,$sess_codpais);
	$project_id=$data_oneProy['project_id'];

	$data_onePais=$pais->selec_one_pais($sess_codpais);
	if(!empty($data_onePais))
		$porigv=$data_onePais['impuesto'];
	
	// dats geenrales del proyecto
	$data_oneDetProy=$proyectoprograma->selec_one_detalle_proyecto($id_proyecto,$coddetalle,$sess_codpais);
	if(!empty($data_oneDetProy['impuesto'])){
		$porigv=$data_oneDetProy['impuesto'];
		$is_igv=$data_oneDetProy['is_igv'];
	}	
	
	// data cronograma
	$data_crono=$proyectoprograma->selec_cronogramapago($id_proyecto,$coddetalle,$sess_codpais);
	
	$data_tc_fact=$proyectoprograma->selec_tc_facturabyProy($id_proyecto,$sess_codpais);
	$data_cobros_fact=$proyectoprograma->selec_lab_resultadobyProy($project_id,$sess_codpais);
	$data_progCosto=$proyectoprograma->selec_progCostobyProy($coddetalle,$id_proyecto,$sess_codpais);
	$data_progDat=$proyectoprograma->selec_progDatosbyProy($coddetalle,$id_proyecto,$sess_codpais);
	$data_deudaPro=$proycosto->selec_DeudabyProy($project_id,$sess_codpais);

	include("../vista/proyectoprograma/frm_oneDetProyecto.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='addEddCronograma'){
    // proceso update a la base de datos usuarios
	
	$coddetalle=$_POST['coddetalle'];
	$data_proyDet=$proyectoprograma->selec_one_detalle_proyectobyId($coddetalle);
	$is_igv=$data_proyDet['is_igv'];
	
	if(!empty($_POST['id_cronograma'])){
		$id_cronograma=$_POST['id_cronograma'];
		$data_crono=$proyectoprograma->selec_one_cronogramapago($id_cronograma,$sess_codpais);
		
		$tmpigv=0;
		if($is_igv=='Si') 
			$tmpigv=$data_crono['importe']*$porigv;
		$tmptotal=$data_crono['importe']+$tmpigv;
	}
	include("../vista/proyectoprograma/frm_conograma.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detEstCronProy'){
    // proceso update a la base de datos usuarios
	
	$coddetalle=$_POST['coddetalle'];
	$data_crono=$proyectoprograma->selec_one_condicionpago($coddetalle);
	$dia="0";
	if(!empty($data_crono['dia']))
		$dia=$data_crono['dia'];
	
	$id_proyecto=$_POST['id_proyecto'];
	
	$fechafactura="";
	$fechavencimiento="";
	$fechacobro="";
	$cobrado=0;
	$noemail=0;
	
	if(!empty($_POST['fechafactura']))
		$fechafactura=formatdatedos($_POST['fechafactura']);
	if(!empty($_POST['fechavencimiento']))
		$fechavencimiento=formatdatedos($_POST['fechavencimiento']);
	if(!empty($_POST['fechacobro']))
		$fechacobro=formatdatedos($_POST['fechacobro']);
	
	if(!empty($_POST['cobrado']))
		$cobrado=1;
	if(!empty($_POST['noemail']))
		$noemail=1;
	

	$nrofactura=$_POST['nrofactura'];
	
	if(!empty($_POST['id_cronograma'])){
		$id_cronograma=$_POST['id_cronograma'];
		$proyectoprograma->update_cronogramapago($id_cronograma,$coddetalle,$id_proyecto,$nrofactura,$fechafactura,$fechavencimiento,$fechacobro,$cobrado,$noemail,$usuario_name,$ip,$sess_codpais,$dia);
	}
	echo "Registrado";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delCronoProyecto'){
    // proceso update a la base de datos usuarios
	
	$coddetalle=$_POST['coddetalle'];
	$id_cronograma=$_POST['id_cronograma'];
	$id_proyecto=$_POST['id_proyecto'];
	
	$proyectoprograma->delete_cronogramapago($id_cronograma,$coddetalle,$id_proyecto,$usuario_name,$ip,$sess_codpais);
	
	echo "Eliminado";	

}else if(!empty($_POST['accion']) and $_POST['accion']=='EdFactProyecto'){
    // abrir modal para datos de factura tc
    	
	$codfactura=$_POST['codfactura'];
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];
	$data_proyFac=$proyectoprograma->selec_one_factura_proyectobyId($codfactura,$id_proyecto);

	include("../vista/proyectoprograma/frm_factura.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detFacturaTc'){
    // proceso update a la base de datos usuarios
	
	$codfactura=$_POST['codfactura'];
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];

	$fechacobro="";
	$cobrado=0;
	$noemail=0;
	
	if(!empty($_POST['fechacobro']))
		$fechacobro=formatdatedos($_POST['fechacobro']);
	
	if(!empty($_POST['cobrado']))
		$cobrado=1;
	if(!empty($_POST['noemail']))
		$noemail=1;
	
	
	$proyectoprograma->update_facturaTc($codfactura,$coddetalle,$id_proyecto,$fechacobro,$cobrado,$noemail,$usuario_name,$ip,$sess_codpais);
	
	echo "Registrado Tc";

//**********************************************************************************	
//  MODULO DE RESULTADOS DE LABORATORIO
//**********************************************************************************	
}else if(!empty($_POST['accion']) and $_POST['accion']=='viewDetProyectoLab'){
    // proceso update a la base de datos usuarios
	
	$anio=$_POST['anio'];
	$id_proyecto=$_POST['id_proyecto'];

	$data_oneProy=$proyectoprograma->selec_one_proyectoprograma($id_proyecto,$sess_codpais);
	$project_id=$data_oneProy['project_id'];
	// data laboratorio
	$data_Lab=$proyectoprograma->selec_lab_resultadoByAnio($project_id,$anio,$sess_codpais);
	include("../vista/proyectoprograma/frm_oneDetProyectoLab.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_saveLabResultado'){
    // proceso update a la base de datos usuarios
	$faccliente=$_POST['faccliente'];
	$monto=$_POST['montofactura'];
	$fecha=formatdatedos($_POST['fechafacturacliente']);
	$cadena=implode(",", $_POST['labchk']);
	$anio=$_POST['anio'];			
	$project_id=$_POST['project_id'];
	$id_proyecto=$_POST['id_proyecto'];
	$moneda='US$';

	// data laboratorio
	$codfactura=$proyectoprograma->insert_lab_resultadoFactura($faccliente,$monto,$fecha,$moneda,$project_id,$id_proyecto,$usuario_name,$ip);
	$proyectoprograma->update_lab_resultadoFactura($faccliente,$fecha,$moneda,$project_id,$codfactura,$anio,$cadena);
	
	echo "Se registro los datos de factura";
	
//**********************************************************************************	
//  MODULO DE tc
//**********************************************************************************	
}else if(!empty($_POST['accion']) and $_POST['accion']=='viewDetProyectoTc'){
    // proceso update a la base de datos usuarios
	
	$anio=$_POST['anio'];
	$mes=$_POST['mes'];
	$id_proyecto=$_POST['id_proyecto'];

	$data_oneProy=$proyectoprograma->selec_one_proyectoprograma($id_proyecto,$sess_codpais);
	$project_id=$data_oneProy['project_id'];
	
	// data tc
	$data_Tc=$proyectoprograma->selec_TcByAnioMes($project_id,$anio,$mes,$sess_codpais);
	include("../vista/proyectoprograma/frm_oneDetProyectoTC.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_saveTc'){
    // proceso update a la base de datos usuarios
	$faccliente=$_POST['faccliente'];
	$monto=$_POST['montofactura'];
	$fecha=formatdatedos($_POST['fechafacturacliente']);
	$cadena=implode(",", $_POST['labchk']);
	$anio=$_POST['anio'];		
	$mes=$_POST['mes'];	
	$project_id=$_POST['project_id'];
	$id_proyecto=$_POST['id_proyecto'];
	$moneda='US$';

	// data tc
	$codfactura=$proyectoprograma->insert_Tc_resultadoFactura($faccliente,$monto,$fecha,$moneda,$project_id,$id_proyecto,$usuario_name,$ip);
	$proyectoprograma->update_Tc_resultadoFactura($faccliente,$fecha,$moneda,$project_id,$codfactura,$anio,$mes,$cadena);
	
	echo "Se registro los datos de factura";	
}


?>
