<?php

include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_proyecto_programa_modelo.php");
include("../modelo/prg_proyecto_modelo.php");
include("../modelo/prg_auditor_modelo.php");
include("../modelo/prg_pais_modelo.php");
include("../modelo/prg_condicionpago_modelo.php");
include("../modelo/prg_estadoproyecto_modelo.php");
include("../modelo/prg_rol_modelo.php");
include("../modelo/prg_enlace_modelo.php");
include("../modelo/prg_proyectocosto_modelo.php");
include("../modelo/prg_programa_modelo.php");

$proyectoprograma=new prg_proyecto_programa_model();
$pais=new prg_pais_model();
$auditor=new prg_auditor_model();
$condicion=new prg_condicionpago_model();
$estado=new prg_estadoproyecto_model();
$proyecto=new prg_proyecto_model();
$roles=new prg_rol_model();
$prgenlace=new prg_enlace_model();
$proycosto=new prg_proyectocosto_model();
$programa=new prg_programa_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

$isnivel=0;
	// *************** inicio de ver rol *****************************
	// modificacion sobre el nivel del rol
	$data_enlace=$prgenlace->selec_one_enlace_bycontrol($sess_codpais,'proycomercial','index');
	if(!empty($data_enlace)){
		$id_enlace=$data_enlace['id_enlace'];
		$data_nivel=$roles->selec_enlacenivelbyPais($sess_codpais,$sess_codrol,$id_enlace);
		if(!empty($data_nivel)){
			foreach($data_nivel as $row){
				$isnivel=1;
				$isnivel=1;
				$isread=$row['isread'];
				$isupdate=$row['isupdate'];
				$isdelete=$row['isdelete'];
			}
		}
	}
	// *************** fin de ver rol *****************************
	//*************************************************************

//***********************************************************
// proyecto comercial index
if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	$dataEstado=$estado->select_estadoproyectoByPais($sess_codpais);
	$data_programa=$programa->selec_programasbypais($sess_codpais);
    include("../vista/proyectocomercial/index_plan.php");	

// proyecto comercial index
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_proycomercial'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$codestado = $_POST['codestado'];
	$tipofactura = $_POST['tipofactura'];
	$programa = $_POST['programa'];
	/*
	$txtprograma="";
	if(!empty($_POST['programa'])){
		foreach($_POST['programa'] as $codprograma){
			if($txtprograma=='') $txtprograma=$codprograma;
			else $txtprograma=",".$codprograma;
		}
	}
	*/
	
	$inconsistencia="0";
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
	$searchQuery = " and prg_proyecto.id_pais='$sess_codpais' and prg_proyecto.flgactivo='1' ";

	## Total number of records without filtering
	$data_maxOF=$proyectoprograma->selec_total_proyectocomercial($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and (proyect like '%$descripcion%' or prg_proyecto.project_id like '%$descripcion%'  )";
	
	if(!empty($codestado))
		$searchQuery.=" and prg_proyecto_detalle.codestado=$codestado ";
	
	if(!empty($tipofactura))
		$searchQuery.=" and tipofactura='$tipofactura' ";
	
	if(!empty($programa))
		$searchQuery.=" and prg_proyecto.dsc_programa like '%$programa%' ";
	
	
	## Total number of record with filtering
	$data_maxOF2=$proyectoprograma->selec_total_proyectocomercial($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$proyectoprograma->select_proyectocomercial($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$inconsistencia,$sess_codpais);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_proyecto'];
			$inconsistencia="";
			if($row['montototal']!=$row['montodetalle']) $inconsistencia="Error";
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediProyComercial'><i class='fas fa-edit'></i> </button>";
			
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
			   "montodetalle"=>$row['montodetalle'],
			   "montototal"=>$row['montototal'],
			   "inconsistencia"=>$inconsistencia,
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
	 // delete a la base de datos usuarios
	$descripcion = $_POST['descripcion'];
	## Search  oculto
	$searchQuery = " and prg_proyecto.id_pais='$sess_codpais' ";

	if(!empty($descripcion))
		$searchQuery.=" and (proyect like '%$descripcion%' or prg_proyecto.project_id like '%$descripcion%'  )";
	
	
	$columnName=" proyect";
	$columnSortOrder=" asc ";
	$row=0;
	$rowperpage=100000;
	$data_OF=$proyectoprograma->select_proyectoprograma($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	include("../vista/proyectocomercial/data_exporta.php");

// proyecto comercial editar	
}else if(!empty($_POST['accion']) and $_POST['accion']=='editProyectocomercial'){
    // proceso update a la base de datos usuarios
	
	$id_proyecto=$_POST['id_proyecto'];

	// dats geenrales del proyecto
	$data_oneProy=$proyectoprograma->selec_one_proyectoprograma($id_proyecto,$sess_codpais);
	$project_id=$data_oneProy['project_id'];
	
	// botones de detalle proyecto
	//$data_DetProyAnio=$proyectoprograma->selec_anios_detalle_proyectoprograma($id_proyecto,$sess_codpais);
	$data_DetProy=$proyectoprograma->selec_detalle_proyectoprograma($id_proyecto,$sess_codpais);
	
	
	include("../vista/proyectocomercial/frm_detalle.php");	

// vista de un form proyecto comercial
}else if(!empty($_POST['accion']) and $_POST['accion']=='viewDetProyectoComercial'){
    // proceso update a la base de datos usuarios
	
	
	$id_proyecto=$_POST['id_proyecto'];

	$data_oneProy=$proyectoprograma->selec_one_proyectoprograma($id_proyecto,$sess_codpais);
	$project_id=$data_oneProy['project_id'];
	$ruc=$data_oneProy['ruc'];
	
	if($data_oneProy['programas']!='') 
		$arrayProg=explode("<br>",$data_oneProy['programas']);
	
	$data_onePais=$pais->selec_one_pais($sess_codpais);
	
	if(!empty($data_onePais))
		$porigv=$data_onePais['impuesto'];
		$impuesto=$data_onePais['impuesto'];
	$codejecutivo="";
	$id_condicion="";
	// dats geenrales del proyecto
	if(!empty($_POST['coddetalle'])){
		$coddetalle=$_POST['coddetalle'];
		$data_oneDetProy=$proyectoprograma->selec_one_detalle_proyectoComercial($id_proyecto,$coddetalle,$sess_codpais);
		
		
		if(!empty($data_oneDetProy['impuesto'])){
			$porigv=$data_oneDetProy['impuesto'];
			$impuesto=$data_oneDetProy['impuesto'];
			$is_igv=$data_oneDetProy['is_igv'];
			$codejecutivo=$data_oneDetProy['codejecutivo'];
		}

		
		$montototal=$data_oneDetProy['montototal'];
		$moneda=$data_oneDetProy['moneda'];
		$tipofactura=$data_oneDetProy['tipofactura'];
		$codestado=$data_oneDetProy['codestado'];
		$id_condicion=$data_oneDetProy['id_condicion'];
	
		// data cronograma data_crono
		$data_crono=$proyectoprograma->selec_cronogramapago($id_proyecto,$coddetalle,$sess_codpais);
		$data_progCosto=$proyectoprograma->selec_progCostobyProy($coddetalle,$id_proyecto,$sess_codpais);
		$data_progDat=$proyectoprograma->selec_progDatosbyProy($coddetalle,$id_proyecto,$sess_codpais);
		$dataRelacion=$proyectoprograma->select_detallexproyecto($id_proyecto,$coddetalle);
		
		// monto que falta en cronograma dataEstado
		$dataFaltaCrono=$proyectoprograma->selec_total_cronogramabyProy($id_proyecto,$coddetalle);
		
		if(!empty($dataFaltaCrono['total']))
			$pendienteCrono=$montototal-$dataFaltaCrono['total'];
		else
			$pendienteCrono=$montototal;
		
	}
	
	$dataEjecutivo=$auditor->select_auditorForProyCome($sess_codpais,$codejecutivo);
	$dataCondicion=$condicion->select_condicionpagoByPais($sess_codpais,$id_condicion);
	
	$dataEstado=$estado->select_estadoproyectoByPais($sess_codpais,$flgactivo=1,$codestado);
	$dataProyecto=$proyecto->select_proyecto_Select($sess_codpais);
	$dataProducto=$proyectoprograma->select_productoxproyecto($sess_codpais,$project_id);
	
	$dataPais=$pais->selec_one_pais($sess_codpais);
	$g_moneda=$dataPais['monedaabv'];
	$getIgvPais=$dataPais['impuesto'];
	$tipocambio=$dataPais['tc'];
	if(!empty($data_oneDetProy['tipocambio'])) $tipocambio=$data_oneDetProy['tipocambio'];
	
	// datos de deuda  dataEjecutivo  dataCondicion data_oneDetProy
	$data_deudaPro=$proycosto->selec_DeudabyProy($project_id,$sess_codpais);

	include("../vista/proyectocomercial/frm_oneDetProyecto.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_addProyComercial'){
    // proceso update a la base de datos usuarios
	
	$id_proyecto=$_POST['id_proyecto'];
	$analisisdsc="";
	$is_viatico="0";
	$is_analisis="0";
	$is_igv="0";
	$isanulado="0";
	$igv="0";
	$is_curso="0";
	$codpuerto="";
	$tonelada="";
	$noaplica="0";
	$id_condicion="0";
	$observacion=$_POST['observacion'];
	$anio=$_POST['anio'];
	$igv=$_POST['igv'];
	$tipofactura=$_POST['tipofactura'];
	$impuesto=$_POST['txtimpuesto'];

	$flgampliacion="";
	$montoampliacion="";
	$flgreduccion="";
	$montoreduccion="";
	$parent="";
	$montorestecnico="";

	if(!empty($_POST['id_condicion'])) $id_condicion=$_POST['id_condicion'];
	if(!empty($_POST['analisisdsc'])) $analisisdsc=$_POST['analisisdsc'];
	if(!empty($_POST['anioo'])) $anioo=$_POST['anioo'];
	if(!empty($_POST['meso'])) $meso=$_POST['meso'];
	if(!empty($_POST['codpuerto'])) $codpuerto=$_POST['codpuerto'];
	if(!empty($_POST['tonelada'])) $tonelada=$_POST['tonelada'];
	

	if(!empty($_POST['flgampliacion'])) $flgampliacion=$_POST['flgampliacion'];
	if(!empty($_POST['montoampliacion'])) $montoampliacion=$_POST['montoampliacion'];
	if(!empty($_POST['flgreduccion'])) $flgreduccion=$_POST['flgreduccion'];
	if(!empty($_POST['montoreduccion'])) $montoreduccion=$_POST['montoreduccion'];
	if(!empty($_POST['montorestecnico'])) $montorestecnico=$_POST['montorestecnico'];

	

	$mes=$_POST['mes'];
	$codestado=$_POST['codestado'];
	$codejecutivo=$_POST['codejecutivo'];
	$montototal=$_POST['montototal'];
	$moneda=$_POST['moneda'];
	$tipocambio=$_POST['tipocambio'];
	$codproducto=$_POST['codproducto'];
	$parent=$_POST['parent'];
	$project_id_adm="";
	$proyecto_adm="";
	$id_proyecto_adm=$_POST['project_id_adm'];
		
	$is_viatico="0";
	$is_igv="0";
	$is_curso="0";
	$is_analisis="0";
	
	if(!empty($_POST['noaplica'])) $noaplica="1";
	if(!empty($_POST['is_viatico'])) $is_viatico="1";
	if(!empty($_POST['is_igv'])) $is_igv="1";
	if(!empty($_POST['is_curso'])) $is_curso="1";
	if(!empty($_POST['is_analisis'])) $is_analisis="1";
	if(!empty($_POST['isanulado'])) $isanulado="1";

	
	if(!empty($_POST['coddetalle'])){
		$coddetalle=$_POST['coddetalle'];
		$proyectoprograma->update_detalleProyCom($id_proyecto,$coddetalle,$impuesto,$parent,$tipofactura,$montorestecnico,$igv,$montoampliacion,$montoreduccion,$codpuerto,$tonelada,$flgampliacion,$flgreduccion, $meso,$anioo,$noaplica,$is_igv,$is_curso,$analisisdsc,$observacion,$anio,$mes,$codestado,$codejecutivo,$montototal,$moneda,$is_analisis,$id_condicion, $is_viatico,$project_id_adm,$proyecto_adm,$id_proyecto_adm,$tipocambio,$codproducto,$usuario_name,$ip,$sess_codpais,$isanulado);
		$proyectoprograma->updateProgramaCosto($id_proyecto,$coddetalle,$moneda);
		$proyectoprograma->updateCronogramaPago($id_proyecto,$coddetalle,$moneda);
	}else{
		$coddetalle=$proyectoprograma->insert_detalleProyCom($id_proyecto,$impuesto,$parent,$tipofactura,$montorestecnico,$igv,$montoampliacion,$montoreduccion,$codpuerto,$tonelada,$flgampliacion,$flgreduccion, $meso,$anioo,$noaplica,$is_igv,$is_curso,$analisisdsc,$observacion,$anio,$mes,$codestado,$codejecutivo,$montototal,$moneda,$is_analisis,$id_condicion, $is_viatico,$project_id_adm,$proyecto_adm,$id_proyecto_adm,$tipocambio,$codproducto,$usuario_name,$ip,$sess_codpais,$isanulado);
	}
	
	echo $coddetalle;
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='ediproycosto'){
	$coddetalle=$_POST['coddetalle'];

	$row=$proycosto->selec_one_DeudabyProy($coddetalle,$id_pais);

	include("../vista/proyectocomercial/frm_oneDetproycosto.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_ediOproydeuda'){
    // proceso update a la base de datos usuarios
	
	$coddetalle=$_POST['coddetalled'];
	$concepto=$_POST['asunto'];
	$importe=$_POST['importe'];
	$fecha=formatdatedos($_POST['fecha']);
	$vencimiento=formatdatedos($_POST['vencimiento']);
	$recordatorio1=formatdatedos($_POST['recordatorio1']);
	$recordatorio2=formatdatedos($_POST['recordatorio2']);
	$nrofactura=$_POST['nrofactura'];
	$observacion=$_POST['observacion'];

	$proycosto->update_detalleProyDeuda($coddetalle,$concepto,$importe,$fecha,$vencimiento,$recordatorio1,$recordatorio2,$nrofactura,$observacion,$usuario_name,$ip,$sess_codpais);
		
	echo $coddetalle;

// eliminar proyecto comercial
}else if(!empty($_POST['accion']) and $_POST['accion']=='del_proycomercial'){
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];
	$proyectoprograma->delete_one_proyectoprograma($id_proyecto,$coddetalle,$sess_codpais,$ip,$usuario_name);
	
	echo "Se elimino el proyecto correctamente.";
	
//*********************************************************************
// modulo del cronograma proyecto
//*********************************************************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='addEddCronogramaComercial'){
    // proceso update a la base de datos usuarios
	
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];

	// dats generales del proyecto
	$data_oneProy=$proyectoprograma->selec_one_proyectoprograma($id_proyecto,$sess_codpais);
	
	$data_nota=$proyectoprograma->selec_data_nota($sess_codpais);

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
	
	
	$moneda=$data_oneDetProy['moneda'];
	
	if(!empty($_POST['id_cronograma'])){
		$id_cronograma=$_POST['id_cronograma'];
		$data_crono=$proyectoprograma->selec_one_cronogramapago($id_cronograma,$sess_codpais);
		
		$tmpigv=0;
		if($is_igv=='Si') 
			$tmpigv=$data_crono['importe']*$porigv;
		$tmptotal=$data_crono['importe']+$tmpigv;
	}
	include("../vista/proyectocomercial/frm_cronograma.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detEstCronProy'){
    // proceso update a la base de datos usuarios
	
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];
	$fecha=formatdatedos($_POST['fecha']);
	$importe=$_POST['importe'];
	$observacion=$_POST['observacion'];
	$moneda=$_POST['moneda'];
	$fechanc=formatdatedos($_POST['fechanc']);
	$montonc=$_POST['montonc'];
	$numeronc=$_POST['numeronc'];
	$montoservicio=$_POST['montoservicio'];
	$nota=$_POST['nota'];
	$montoncneto=$_POST['montoncneto'];
	
	
	if(!empty($_POST['id_cronograma'])){
		$id_cronograma=$_POST['id_cronograma'];
		$result=$proyectoprograma->update_cronogramaProy($id_cronograma,$id_proyecto,$coddetalle,$fecha,$importe,$moneda,$observacion,$fechanc,$montonc,$numeronc,$montoservicio,$nota,$montoncneto,$usuario_name,$ip,$sess_codpais);
	}else{
		$result=$proyectoprograma->insert_cronogramaProy($id_proyecto,$coddetalle,$fecha,$importe,$moneda,$observacion,$fechanc,$montonc,$numeronc,$montoservicio,$nota,$montoncneto,$usuario_name,$ip,$sess_codpais);
	}
	
	$proyectoprograma->regula_cronogramaProy($id_proyecto,$coddetalle);
	
	echo "Se actualizo el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='delCronoProyComercial'){
    // proceso update a la base de datos usuarios
	
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];
	$id_cronograma=$_POST['id_cronograma'];
	
	$proyectoprograma->delete_cronogramaProy($id_proyecto,$coddetalle,$id_cronograma);
	echo "Se actualizo el registro.";	

//*********************************************************************
// modulo del facturar proyecto
//*********************************************************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='factCronogramaComercial'){
    // proceso update a la base de datos usuarios data_crono data_fact
	
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];

	// dats generales del proyecto
	$data_oneProy=$proyectoprograma->selec_one_proyectoprograma($id_proyecto,$sess_codpais);

	$project_id=$data_oneProy['project_id'];
	$proyect=$data_oneProy['proyect'];
	$ruc=$data_oneProy['ruc'];

	$data_oneVendedor=$proyectoprograma->selec_one_vendedor($coddetalle);
	if(!empty($data_oneVendedor))
		$vendedor=$data_oneVendedor['nombre'];
	
	$data_onePais=$pais->selec_one_pais($sess_codpais);
	if(!empty($data_onePais))
		$porigv=$data_onePais['impuesto'];
	
	// dats generales del proyecto
	$data_oneDetProy=$proyectoprograma->selec_one_detalle_proyecto($id_proyecto,$coddetalle,$sess_codpais);
	
	if(!empty($data_oneDetProy['impuesto'])){
		$porigv=$data_oneDetProy['impuesto'];
	}
	
	$is_igv=$data_oneDetProy['is_igv'];
//	echo "xxx--->".$is_igv;
	if($is_igv!='Si') 
		$porigv=0;
	
	
	$moneda=$data_oneDetProy['moneda'];
	
	if(!empty($_POST['id_cronograma'])){
		$id_cronograma=$_POST['id_cronograma'];
		$data_crono=$proyectoprograma->selec_one_cronogramapago($id_cronograma,$sess_codpais);
		
		$tmpigv=0;
		if($is_igv=='Si') 
			$tmpigv=$data_crono['importe']*$porigv;
		$tmptotal= round($data_crono['importe']+$tmpigv,2);
	}
	$flg_ofisis=$data_crono['flg_ofisis'];
	
	/******************************
	CREAR ITEM AUTOMATICO MEXICO
	******************************/
	$data_=$proyectoprograma->selectall_ofi_factura($id_cronograma);
	if(empty($data_) and $sess_codpais=='mex'){
		
		$montosin="";
		// datos por defecto del programa
		$datadet_=$proyectoprograma->selectall_ofi_detfactura($coddetalle,$sess_codpais);
		 
		foreach($datadet_ as $row){
			
			$sqlinsert="insert into ofi_detfactura(id_producto,id_subcuenta,monto,id_cronograma,descripcion,flag,codunegocio)  ";
			$codunegocio=$row['codunegocio'];
			$id_producto=$row['id_producto'];
			$montoservicio=$row['montoservicio'];
			$montoviatico=$row['montoviatico'];
			$montofee=$row['montofee'];
			$montofeecert=$row['montofeecert'];

			 $partInst="";
			 if(!empty($montoservicio) and $montoservicio>0){
				 $montosin=$montoservicio*(1+$porigv);
				 $subcuenta="CERT 1";
				 $id_subcuenta=6170;
				 $partInst.= "values ($id_producto,$id_subcuenta,$montosin,$id_cronograma,'','1',$codunegocio)";
			 }
			 if(!empty($montoviatico) and $montoviatico>0){
				 $montosin=$montoviatico*(1+$porigv);
				 $subcuenta="CERT 7";
				 $id_subcuenta=6184;
				 if($partInst=='') $partInst.= "values ($id_producto,$id_subcuenta,$montosin,$id_cronograma,'','1',$codunegocio)";
				 else $partInst.= ",  ($id_producto,$id_subcuenta,$montosin,$id_cronograma,'','1',$codunegocio)";
			 }
			 if(!empty($montofee) and $montofee>0){
				 $montosin=$montofee*(1+$porigv);
				 $subcuenta="CERT 4";
				 $id_subcuenta=6181;
				 if($partInst=='') $partInst.= "values ($id_producto,id_$subcuenta,$montosin,$id_cronograma,'','1',$codunegocio)";
				 else $partInst.= ",  ($id_producto,$id_subcuenta,$montosin,$id_cronograma,'','1',$codunegocio)";
			 }
			 if(!empty($montofeecert) and $montofeecert>0){
				 $montosin=$montofeecert*(1+$porigv);
				 $subcuenta="CERT 5";
				 $id_subcuenta=6182;
				 if($partInst=='') $partInst.= "values ($id_producto,$id_subcuenta,$montosin,$id_cronograma,'','1',$codunegocio)";
				 else $partInst.= ",  ($id_producto,$id_subcuenta,$montosin,$id_cronograma,'','1',$codunegocio)";
			 }	 
			 
			// echo $sqlinsert.$partInst;
			 
			 if($partInst!=''){
				//echo $sqlinsert.$partInst;
				$proyectoprograma->execute_ofi_factura($sqlinsert.$partInst);
			 }
		}
		 if(!empty($montosin))
			$montocon=$montosin*(1 + $porigv);
		else
			$montocon=0;
	}	
	
	/***********************************
	FIN CREAR ITEM AUTOMATICO MEXICO tmptotal data_cuenta
	************************************/
	
	$data_fact=$proyectoprograma->selec_factOfisis($porigv,$id_cronograma);
	
	$data_cuenta=$proyectoprograma->selec_cuentaOfisis($sess_codpais);
	$data_produc=$proyectoprograma->selec_productoOfisis($sess_codpais);
	$data_unegocio=$proyectoprograma->selec_unegocioOfisis($sess_codpais);
	
	$data_local=$proyectoprograma->selec_localizacion($sess_codpais);
	
	include("../vista/proyectocomercial/frm_facturaERP.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_facturaCronProy'){
    // proceso update a la base de datos usuarios
	
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];
	$id_cronograma=$_POST['id_cronograma'];
	$codlocalizacion=$_POST['codlocalizacion'];
	$serie="";
	if(!empty($_POST['serie']))
		$serie=$_POST['serie'];
	
	$proyectoprograma->update_cronolocalizacion($id_cronograma,$codlocalizacion,$serie);

	$data_crono=$proyectoprograma->selec_factOfisis_simple($id_cronograma);
	if(!empty($data_crono)){
		foreach($data_crono as $row){
			$id=$row['id_detalle'];
			$id_producto=$_POST['id_producto_'.$id];
			$codunegocio=$_POST['codunegocio_'.$id];
			$monto=$_POST['monto_'.$id];
			$descripcion=f_limpiarcaracter($_POST['descripcion_'.$id]);
			$arra=explode("->",$_POST['id_subcuenta_'.$id]);
			$id_subcuenta=$_POST['id_subcuenta_'.$id];

			if($id_producto!='' and $id_subcuenta!='' and $monto!='')
				$proyectoprograma->update_ERP($id_producto,$id_subcuenta,$monto,$descripcion,$id_cronograma,$id,$codunegocio);
		}
	}
	

	$id_producto=$_POST['id_producto'];
	$codunegocio=$_POST['codunegocio'];
	$id_subcuenta=$_POST['id_subcuenta'];
	$monto=$_POST['monto'];
	$descripcion=f_limpiarcaracter($_POST['descripcion']);
	
	if(!empty($id_producto) and !empty($id_subcuenta) and !empty($monto))
		$proyectoprograma->insert_ERP($id_producto,$id_subcuenta,$monto,$descripcion,$id_cronograma,$codunegocio);
	
	echo "Se actualizo el registro.";		

//*********************************************************************
// modulo del costo proyecto
//*********************************************************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='addEddCostoProyComercial'){
    // proceso update a la base de datos usuarios arrayProg
	
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];

	// dats generales del proyecto
	$data_oneProy=$proyectoprograma->selec_one_proyectoprograma($id_proyecto,$sess_codpais);
	
	$project_id=$data_oneProy['project_id'];
	
	$arrayProg=$proyectoprograma->selec_proyectoxprograma($id_proyecto);
	
	//if($data_oneProy['programas']!='') 
	//	$arrayProg=explode("<br>",$data_oneProy['programas']);

	$data_onePais=$pais->selec_one_pais($sess_codpais);
	if(!empty($data_onePais))
		$porigv=$data_onePais['impuesto'];
	
	// dats geenrales del proyecto
	$data_oneDetProy=$proyectoprograma->selec_one_detalle_proyecto($id_proyecto,$coddetalle,$sess_codpais);
	
	if(!empty($data_oneDetProy['impuesto'])){
		$porigv=$data_oneDetProy['impuesto'];
	}
	
	$is_igv=$data_oneDetProy['is_igv'];
	$moneda=$data_oneDetProy['moneda'];
	
	$programa="";
	if(!empty($_POST['id_costo'])){
		$id_costo=$_POST['id_costo'];
		$data_costo=$proyectoprograma->selec_one_progCostobyProy($id_costo,$coddetalle,$id_proyecto,$sess_codpais);
		//$programa= explode("||",$data_costo['programa']); arrayProg
	}
	
	$arrayprograma= explode("||",$programa);
	include("../vista/proyectocomercial/frm_costo.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detEstCostoProyCom'){
    // proceso update a la base de datos usuarios
	
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];
	$programa=$_POST['programa'];
	$preparacion=$_POST['preparacion'];
	$auditoria=$_POST['auditoria'];
	$reporte=$_POST['reporte'];
	$certificacion=$_POST['certificacion'];
	$viaje=$_POST['viaje'];
	$moneda=$_POST['moneda'];
	$montofee=$_POST['montofee'];
	$montofeecert=$_POST['montofeecert'];
	$montocourier=$_POST['montocourier'];
	$montoservicio=$_POST['montoservicio'];
	$montoviatico=$_POST['montoviatico'];
	$intercompany=$_POST['intercompany'];
	$pm=$_POST['pm'];
	
	if(!empty($_POST['comentario']))
		$comentario=$_POST['comentario'];	
	else
		$comentario=$programa;
	
	$ampliacion=$_POST['ampliacion'];
	$reduccion=$_POST['reduccion'];
	$cartacor=$_POST['cartacor'];
	$analisis=$_POST['analisis'];
	$cursos=$_POST['cursos'];
	$notacredito=$_POST['notacredito'];
	
	$auditoria_no_anunciada=$_POST['auditoria_no_anunciada'];
	$investigacion=$_POST['investigacion'];
	$otros=$_POST['otros'];
	
	if(!empty($_POST['id_costo'])){
		$id_costo=$_POST['id_costo'];
		$result=$proyectoprograma->update_costoProyCom($id_costo,$id_proyecto,$coddetalle,$programa,$preparacion,$auditoria,$reporte,$certificacion,$viaje,$moneda,$montofee,$montofeecert,$montocourier,$montoservicio,$montoviatico,$comentario,$ampliacion,$reduccion,$cartacor,$analisis,$cursos,$notacredito,$intercompany,$usuario_name,$ip,$sess_codpais,$auditoria_no_anunciada,$investigacion,$otros,$pm);
	}else{
		$result=$proyectoprograma->insert_costoProyCom($id_proyecto,$coddetalle,$programa,$preparacion,$auditoria,$reporte,$certificacion,$viaje,$moneda,$montofee,$montofeecert,$montocourier,$montoservicio,$montoviatico,$comentario,$ampliacion,$reduccion,$cartacor,$analisis,$cursos,$notacredito,$intercompany,$usuario_name,$ip,$sess_codpais,$auditoria_no_anunciada,$investigacion,$otros,$pm);
	}
	
	$proyectoprograma->regula_costoProyCom($id_proyecto,$coddetalle);
	
	echo "Se actualizo el registro.";	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delCostoProyComercial'){
    // proceso update a la base de datos usuarios
	
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];
	$id_costo=$_POST['id_costo'];
	
	$proyectoprograma->delete_costoProyCom($id_proyecto,$coddetalle,$id_costo);
	echo "Se actualizo el registro.";	

//*********************************************************************
// modulo de otra informacion proyectos
//*********************************************************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='addEddProgramadatos'){
    // proceso update a la base de datos usuarios
	
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];

	// dats generales del proyecto
	$data_oneProy=$proyectoprograma->selec_one_proyectoprograma($id_proyecto,$sess_codpais);
	
	$project_id=$data_oneProy['project_id'];
	if($data_oneProy['programas']!='') 
		$arrayProg=explode("<br>",$data_oneProy['programas']);

	$data_onePais=$pais->selec_one_pais($sess_codpais);
	if(!empty($data_onePais))
		$porigv=$data_onePais['impuesto'];
	
	// dats geenrales del proyecto
	$data_oneDetProy=$proyectoprograma->selec_one_detalle_proyecto($id_proyecto,$coddetalle,$sess_codpais);
	
	if(!empty($data_oneDetProy['impuesto'])){
		$porigv=$data_oneDetProy['impuesto'];
	}
	
	$is_igv=$data_oneDetProy['is_igv'];
	$moneda=$data_oneDetProy['moneda'];
	
	$data_audit=$proyectoprograma->selec_auditProyecto($project_id,$sess_codpais);
	
	if(!empty($_POST['id_datos'])){
		$id_datos=$_POST['id_datos'];
		$data_datos=$proyectoprograma->selec_one_progDatosbyProy($id_datos,$coddetalle,$id_proyecto,$sess_codpais);
	}
	
	include("../vista/proyectocomercial/frm_otrainfo.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_addEddProgramadatos'){
    // proceso update a la base de datos usuarios
	
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];
	$programa=$_POST['programa'];
	$productores=$_POST['productores'];
	$idcalendario=$_POST['idcalendario'];
	$auditorias=$_POST['auditorias'];
	$dias=$_POST['dias'];
	$tipo=$_POST['tipo'];
	
	
	if(!empty($_POST['id_datos'])){
		$id_datos=$_POST['id_datos'];
		$result=$proyectoprograma->update_programadatos($id_datos,$id_proyecto,$coddetalle,$programa,$productores,$idcalendario,$dias,$tipo,$auditorias,$usuario_name,$ip,$sess_codpais);
	}else{
		$result=$proyectoprograma->insert_programadatos($id_proyecto,$coddetalle,$programa,$productores,$idcalendario,$dias,$tipo,$auditorias,$usuario_name,$ip,$sess_codpais);
	}
	
	echo "Se actualizo el registro.";	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delProgramadatos'){
    // proceso update a la base de datos usuarios
	
	$id_proyecto=$_POST['id_proyecto'];
	$coddetalle=$_POST['coddetalle'];
	$id_datos=$_POST['id_datos'];
	
	$proyectoprograma->delete_programadatos($id_proyecto,$coddetalle,$id_datos,$usuario_name,$ip);
	echo "Se actualizo el registro.";	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delitemFactura'){
    // proceso update a la base de datos usuarios
	
	$id_detalle=$_POST['id_detalle'];
	$id_cronograma=$_POST['id_cronograma'];
	
	$proyectoprograma->delete_itemfactura($id_detalle,$id_cronograma);
	echo "Se elimino el registro.";	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='send_ofisis' and $sess_codpais=='esp'){
    // proceso update a la base de datos usuarios
	
	$coddetalle=$_POST['coddetalle'];
	$id_proyecto=$_POST['id_proyecto'];
	$id_cronograma=$_POST['id_cronograma'];

	$data_onePais=$pais->selec_one_pais($sess_codpais);
	if(!empty($data_onePais)){
		$porigv=$data_onePais['impuesto'];
		$tipocambio=$data_onePais['tc'];
	}	
	
	$row=$proyectoprograma->selec_pre_senOfisis($porigv,$coddetalle,$id_proyecto,$id_cronograma);
	
	if(!empty($row)){
		$fecha=$row['fecha_ff'];
		
		$is_igv=$row['is_igv'];
		$moneda=$row['moneda'];
		$montototal=$row['importe'];
		if($is_igv=='1')
			$igv=$row['igv'];
		else{
			$igv=0;
			$porigv=0;
		}
		$ruc=$row['ruc'];
		$codvendedor=$row['codvendedor'];
		$tipofactura=$row['tipofactura'];
		$tmptotal=round($montototal+$igv,2);
	 }  
	 
	//***************************************
	// conexion sqlserver
	
	include("../com/sqlserver.php");
	
	if($tipofactura=='L'){
		$CODEMP="01CU";
		$almacen="CUMIR01";
	}else{
		$CODEMP="01CX";
		$almacen="CUEMIR01";
	}

	$MODFOR = 'FC';
	$CODFOR = 'RPDV';
    $base = $montototal;
    $inafecto = '0';
    $impuesto = $igv;
    $total = $tmptotal;
	
	//if($link){
		$sql="delete from USR_TCPEVT where USR_TCPEVT_NROFOR=$id_cronograma";
		
		//mssql_query($sql);
		sqlsrv_query($conn, $sql);
		
		$sql="insert into USR_TCPEVT(
					USR_TCPEVT_MODFOR ,
					USR_TCPEVT_CODFOR ,
					USR_TCPEVT_NROFOR ,
					USR_TCPEVT_CIRCOM,
					USR_TCPEVT_CIRAPL ,
					USR_TCPEVT_SUCURS ,
					USR_TCPEVT_MODCOM,
					USR_TCPEVT_CODCOM ,
					USR_TCPEVT_FCHMOV ,
					USR_TCPEVT_CODCLI ,
					USR_TCPEVT_CNDPAG,
					USR_TCPEVT_DEPOSI ,
					USR_TCPEVT_SECTOR ,
					USR_TCPEVT_CODLIS ,
					USR_TCPEVT_VNDDOR ,
					USR_TCPEVT_TEXTOS ,
					USR_TCPEVT_CAMBIO ,
					USR_TCPEVT_TIPOPR ,
					USR_TCPEVT_CODOPR ,
					USR_TCPEVT_CODEMP ,
					USR_TCPEVT_OCCLIE ,
					USR_TCPEVT_IMBAFE ,
					USR_TCPEVT_IMBINA ,
					USR_TCPEVT_IMIGVS,
					USR_TCPEVT_IMTOTA ,
					USR_TCPEVT_COMPLA ,
					USR_TCPEVT_NUMPLA ,
					USR_TC_FECALT ,
					USR_TC_FECMOD ,
					USR_TC_USERID ,
					USR_TC_ULTOPR ,
					USR_TC_DEBAJA ,
					USR_TC_HORMOV ,
					USR_TC_MODULE ,
					USR_TC_OALIAS ,
					USR_TC_TSTAMP ,
					USR_TC_LOTTRA ,
					USR_TC_LOTREC ,
					USR_TC_LOTORI ,
					USR_TC_SYSVER ,
					USR_TC_CMPVER 
			) values(
					'$MODFOR' ,
					'$CODFOR' ,
					$id_cronograma ,
					'VLO200',
					'VLO200' ,
					'0001' ,
					'$MODFOR',
					'$CODFOR' ,
					'$fecha',
					'$ruc' ,
					'C000',
					'$almacen' ,
					0 ,
					'$moneda' ,
					'$codvendedor' ,
					'' ,
					$tipocambio ,
					'GEN' ,
					'$codvendedor' ,
					'$CODEMP' ,
					'' ,
					$base ,
					$inafecto ,
					$impuesto,
					$total ,
					'FAC' ,
					$id_cronograma ,
					GETDATE() ,
					GETDATE() ,
					null ,
					null ,
					null ,
					null ,
					null ,
					null ,
					null ,
					null ,
					null ,
					null ,
					null ,
					null
					)";
		
		// llamado sqlserver	
		//echo $sql;
		sqlsrv_query($conn, $sql);
		//$result = mssql_query($sql);
		//mssql_free_result($result);
		
		// tabla detallle
		$dataDet=$proyectoprograma->selec_pre_senOfisisDetalle($porigv,$id_cronograma);
		if(!empty($dataDet)){
			$NROITM=0;
			foreach($dataDet as $det){
				$NROITM++;
				$TIPPRO=utf8_decode($det['STMPDH_TIPPRO']);
				$COPPRO=utf8_decode($det['STMPDH_ARTCOD']);
				$PRECIO =$det['subtotal'];
				$PREIGV =$det['monto'];
				$CANTID='1';
				$UNIMED='UND';
				$glosa=caracterlimpia_alt($det['descripcion']);
				$subcuenta=caracterlimpia($det['cod_desc']);
				
				$sql="INSERT INTO USR_TDPEVT (USR_TDPEVT_CODEMP, USR_TDPEVT_MODFOR, USR_TDPEVT_CODFOR, USR_TDPEVT_NROFOR, USR_TDPEVT_NROITM,
					USR_TDPEVT_TIPPRO, USR_TDPEVT_COPPRO, USR_TDPEVT_MODCPT, USR_TDPEVT_TIPCPT, USR_TDPEVT_CODCPT, 
					USR_TDPEVT_PRECIO, USR_TDPEVT_PREIGV, USR_TDPEVT_CANTID, USR_TDPEVT_UNIMED, USR_TDPEVT_PORCE1, USR_TDPEVT_TEXTOS, USR_TDPEVT_CODDIS,
					USR_TD_FECALT, USR_TD_FECMOD, USR_TD_USERID, USR_TD_ULTOPR, USR_TD_DEBAJA, USR_TD_HORMOV, USR_TD_MODULE, USR_TD_OALIAS, 
					USR_TD_TSTAMP, USR_TD_LOTTRA, USR_TD_LOTREC, USR_TD_LOTORI, USR_TD_SYSVER, USR_TD_CMPVER) 
				VALUES ('$CODEMP', '$MODFOR', '$CODFOR', $id_cronograma, $NROITM, 
				'$TIPPRO', '$COPPRO', 'VT', 'A', 'AVG001', 
				$PRECIO, $PREIGV, $CANTID, '$UNIMED', 0, '$glosa', cast('$subcuenta' as nvarchar(40)), 
				NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,  null, NULL, NULL, NULL, NULL, NULL)";
				
			
				//$result = mssql_query($sql);
				sqlsrv_query($conn, $sql);
				//mssql_free_result($result);
			}
			
		}

	//}
	//**********************************************	
	 
	$proyectoprograma->update_senOfisis($id_cronograma,$usuario_name);
	echo "Se envio la orden de factura.";	

}else if(!empty($_POST['accion']) and $_POST['accion']=='send_ofisis' and $sess_codpais=='mex'){
    // proceso update a la base de datos usuarios

	$coddetalle=$_POST['coddetalle'];
	$id_proyecto=$_POST['id_proyecto'];
	$id_cronograma=$_POST['id_cronograma'];

	$data_onePais=$pais->selec_one_pais($sess_codpais);
	if(!empty($data_onePais)){
		$porigv=$data_onePais['impuesto'];
		$tipocambio=$data_onePais['tc'];
	}	

	//*****************************   ftp server *******************
		$data_=$proyectoprograma->selec_one_proyectoprograma($id_proyecto,$sess_codpais);
		// datos del proyecto
		if(!empty($data_)) {
			$proyectoname=$data_['project_id'];
			$ruc=$data_['ruc'];
		 }
	
		$conn_id = ftp_connect($ftp_server) or die("No se pudo conectar a $ftp_server"); ;
		// iniciar sesión con nombre de usuario y contraseña
		$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
		ftp_pasv($conn_id, true);

		$remote_file="factcabecera.csv";
		$fichero = '../archivos/facturas/factcabecera.csv';
		$ficheroDet = '../archivos/facturas/factdetalle.csv';
		$remoteDet_file="factdetalle.csv";		
		
		$data_=$proyectoprograma->selec_pre_senOfisis($porigv,$coddetalle,$id_proyecto,$id_cronograma);		
		if(!empty($data_)) {
			$fecha=$data_['fecha_fe'];
			$is_igv=$data_['is_igv'];
			$moneda=$data_['moneda'];
			$montototal=$data_['importe'];
			if($is_igv=='1')
				$igv=$data_['igv'];
			else{
				$igv=0;
				$porigv=0;
			}
			$ruc=$data_['ruc'];
			$codvendedor=$data_['codvendedor2'];
			$localizacion=$data_['localizacion'];
			$tipofactura=$data_['tipofactura'];
			$tmptotal=round($montototal+$igv,2);
			$fechahora= date("d/m/Y H:m:s");
			
			
			$dataFile="\n"."$id_cronograma;$fecha;$codvendedor; $moneda; ; $localizacion;$proyectoname;$ruc;$tipofactura;$fechahora ";
			
			
			// crear file y subir al ftp
			
			//******************************************************************
			// recuperar archivo
			$actual="";
	
			if (ftp_get($conn_id, $fichero, $remote_file, FTP_BINARY)) {
				//echo "Se ha recuperado satisfactoriamente en $local_file\n";
				
				if(is_file($fichero)){
					$actual = file_get_contents($fichero);
				}
			}

			if(empty($actual))
				$actual="codfactura;fecha;codvendedor;siglamoneda;terminopago;codlocalizacion;codproyecto;codcliente;tipo;fecharegistro";

			$actual.= $dataFile;
			
			

			// Escribe el contenido al fichero
			file_put_contents($fichero, $actual);

			// cargar un archivo
			if (ftp_put($conn_id, $remote_file, $fichero, FTP_BINARY)) {
				// echo "se ha cargado $remote_file con éxito\n";
			} else {
				echo "Hubo un problema durante la transferencia de $fichero\n";
			}

			//******************************************************************
		 }  


		//****************************************************************************
		// ahora detalle
		//****************************************************************************
		
		$datadet_=$proyectoprograma->selec_pre_senOfisisDetalleFtp($porigv,$id_cronograma);

		if(!empty($datadet_)) {
			$NROITM=0;
			foreach($datadet_ as $row){
				$id=$row['id_detalle'];
				$unegocio=$row['unegocio'];
								
				$NROITM++;
				$COPPRO=$row['producto'];
				$PRECIO =$row['subtotal'];
				$PREIGV =$row['igv'];
				$CANTID='1';
				$UNIMED='UND';
				$glosa=caracterlimpia_alt($row['descripcion']);
				$subcuenta=caracterlimpia($row['cod_desc']);
				$glosa=preg_replace("[\n|\r|\n\r]", "", $glosa);
				$dataFiledet.="\n"."$id_cronograma;$id;$subcuenta;$CANTID;$PRECIO;$PREIGV;$unegocio;$COPPRO;$glosa";	
			}
			
			//******************************************************************
			// recuperar archivo
			$actual="";
			
			if (ftp_get($conn_id, $ficheroDet, $remoteDet_file, FTP_BINARY)) {
				//echo "Se ha recuperado satisfactoriamente en $local_file\n";
			
				if(is_file($ficheroDet)){
					$actual = file_get_contents($ficheroDet);
				}
			}

			//  crear file y subir al ftp
			if(empty($actual))
					$actual="codfactura;coddetalle;codcuenta;cantidad;monto;impuesto;codunidad;codproducto;descripcion";
			$actual.= $dataFiledet;

			// Escribe el contenido al fichero
			
			
			file_put_contents($ficheroDet, $actual);
			
			if (ftp_put($conn_id, $remoteDet_file, $ficheroDet, FTP_ASCII)) {
				//echo "se ha cargado $remoteDet_file con éxito\n";
			} else {
				echo "Hubo un problema durante det la transferencia de $fichero\n";
			}
			
		} 


		/****************************************************************************
		FIN DE DETALLE 
		/****************************************************************************/
		//fin webservice

					
		// cerrar la conexión ftp
		ftp_close($conn_id);
		
	//******************************* fin ftp **********************

	$proyectoprograma->update_senOfisis($id_cronograma,$usuario_name);
	echo "Se envio la orden de factura.";	


}else if(!empty($_POST['accion']) and $_POST['accion']=='del_sendofisis'){
    // proceso update a la base de datos usuarios

	$id_cronograma=$_POST['id_cronograma'];
	if($sess_codpais=='esp'){
		include("../com/sqlserver.php");
		
		$sql="delete from USR_TDPEVT where USR_TDPEVT_NROFOR=$id_cronograma";
		//mssql_query($sql);	
		sqlsrv_query($conn, $sql);
		
		$sql="delete from USR_TCPEVT where USR_TCPEVT_NROFOR=$id_cronograma";
		
		//mssql_query($sql);
		sqlsrv_query($conn, $sql);
		
	}elseif($sess_codpais=='mex'){

		$conn_id = ftp_connect($ftp_server);
		// iniciar sesión con nombre de usuario y contraseña
		$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
		ftp_pasv($conn_id, true);

		$remote_file="factcabecera.csv";
		$fichero = '../archivos/facturas/factcabecera.csv';
		$ficheroDet = '../archivos/facturas/factdetalle.csv';
		$remoteDet_file="factdetalle.csv";	

		//***************************************************************
		// recuperar file del ftp
		//***********************************************************************
		if (ftp_get($conn_id, $fichero, $remote_file, FTP_BINARY)) {
			echo "Se ha recuperado satisfactoriamente en $local_file\n";
			
			$lineaFin="";
			if(is_file($fichero)){
				$actual = file_get_contents($fichero);
				$rows = explode("\n", $actual); 
				// leer cada linea y quitar el dato si existe
				foreach ($rows as $row) {
				   $dato = explode(";", $row);
				   
				   if($dato[0]!=$id_cronograma){
						if($lineaFin=='') $lineaFin=$row;
						else $lineaFin.="\n".$row;
				   }
				}
				
				// Escribe el contenido al fichero
				file_put_contents($fichero, $lineaFin);
				if (ftp_put($conn_id, $remote_file, $fichero, FTP_BINARY)) {
					echo "se ha cargado $remote_file con éxito\n";
				} else {
					echo "Hubo un problema durante la transferencia de $file\n";
				}
			}
		}
		//***********************************************************************
		//***********************************************************************
		if (ftp_get($conn_id, $ficheroDet, $remoteDet_file, FTP_BINARY)) {
			echo "Se ha recuperado satisfactoriamente en $local_file\n";
			
			$lineaFin="";
			if(is_file($ficheroDet)){
				$actual = file_get_contents($ficheroDet);
				$rows = explode("\n", $actual); 
				// leer cada linea y quitar el dato si existe
				foreach ($rows as $row) {
				   $dato = explode(";", $row);
				   
				   if($dato[0]!=$id_cronograma){
						if($lineaFin=='') $lineaFin=$row;
						else $lineaFin.="\n".$row;
				   }
				}
				
				// Escribe el contenido al fichero
				file_put_contents($ficheroDet, $lineaFin);
				if (ftp_put($conn_id, $remoteDet_file, $ficheroDet, FTP_BINARY)) {
					echo "se ha cargado $remote_file con éxito\n";
				} else {
					echo "Hubo un problema durante la transferencia de $file\n";
				}
			}
		}
		//*********************************************************************** data_fact
		//***********************************************************************	
	}	
	
	$proyectoprograma->delete_senOfisis($id_cronograma,$usuario_name);
	echo "Se elimino el envio de factura";


}else if(!empty($_POST['accion']) and $_POST['accion']=='buscarSubcuenta'){
	$txt_subcuenta=$_POST['txt_subcuenta'];
	$datares=$proyectoprograma->selec_cuentaOfisis($sess_codpais,$txt_subcuenta);
	
	$options[] = array('value' => '', 'text' =>'- - BUSCAR  - ');
	foreach($datares as $row){
		 $options[] = array('value' => $row['id_subcuenta'], 'text' => $row['cuenta']);
		
	}
	echo json_encode($options);
	
}


?>
