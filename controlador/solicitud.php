<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");
include("../modelo/prg_pais_modelo.php");
include("../modelo/lst_solicitud_modelo.php");

$solicitud=new lst_solicitud_model();
$prgpais=new prg_pais_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

//***********************************************************

//******************************************
// seccion de ver listas subidas y acciones
//******************************************

if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	$data_pais=$prgpais->selec_paises();
    include("../vista/solicitud/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_solicitud'){
	
	//***********************************************************
	// funcion buscador tabla lista integrada
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$nrotrc = $_POST['nrotrc'];
	$estado = $_POST['estado'];
	
	if(!empty($_POST['id_pais']))
		$id_pais = $_POST['id_pais'];
	//else
	//	$id_pais=$sess_codpais;
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" s.numero ";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	
	$searchQuery="";
	## Total number of records without filtering
	$data_maxOF=$solicitud->selec_total_solicitud($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($id_pais))
		$searchQuery.= " AND s.id_pais='$id_pais' ";
	
	
	if(!empty($descripcion))
		$searchQuery.=" and ( p.proyect like '%$descripcion%' or s.project_id  like '%$descripcion%' ) ";
	
	
	if(!empty($nrotrc))
		$searchQuery.=" and  s.nrotrc like '%$nrotrc%' ";
	
	
	
	if(!empty($estado))
		$searchQuery.=" and  s.estado ='$estado'  ";
	
	## Total number of record with filtering
	$data_maxOF2=$solicitud->selec_total_solicitud($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$solicitud->select_solicitud($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
			$id=$row['codsolicitud'];
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliSolicitud'><i class='fas fa-trash'></i> </button>";
			$usuariodsc=$row['usuario_modifica'];
			$accion="<div class='dropdown'>
				  <button class='btn btn-primary dropdown-toggle' type='button' id='dropdownMenuButton' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
					Acciones
				  </button>
				  <div class='dropdown-menu' aria-labelledby='dropdownMenuButton'>";
			$accion.=" <a class='dropdown-item btn_verSolicitud' id='sol_". $id ."' href='#'>Ver</a>";
			$accion.=" <a class='dropdown-item btn_xlsSol' id='sol_". $id ."' href='#'>Exportar</a>";
			if($row['estado']=='e'){
				$accion.=" <a class='dropdown-item btn_aprobSolicitud' id='sol_". $id ."_p' href='#'>Pendiente</a>";
				$accion.=" <a class='dropdown-item btn_aprobSolicitud' id='sol_". $id ."_a' href='#'>Aprobar</a>";
				$accion.=" <a class='dropdown-item btn_aprobSolicitud' id='sol_". $id ."_d' href='#'>Desaprobar</a>";
				$usuariodsc="";
			}elseif($row['estado']=='a'){
				// $accion.=" <a class='dropdown-item btn_eliSolicitud' id='estproy_". $id ."' href='#'>Eliminar</a>";
				$accion.=" <a class='dropdown-item btn_aprobSolicitud' id='sol_". $id ."_p' href='#'>Pendiente</a>";
				$accion.=" <a class='dropdown-item btn_aprobSolicitud' id='sol_". $id ."_d' href='#'>Desaprobar</a>";
			}elseif($row['estado']=='d'){
				$accion.=" <a class='dropdown-item btn_aprobSolicitud' id='sol_". $id ."_p' href='#'>Pendiente</a>";
				$accion.=" <a class='dropdown-item btn_aprobSolicitud' id='sol_". $id ."_a' href='#'>Aprobar</a>";
			}
			
			$accion.="	  </div>
				</div>";
		
		   $data[] = array( 
			   "lote"=>$row['lote'],
			   "numero_mostrar"=>$row['numero_mostrar'],
			   "codsolicitud"=>$row['id'],
			   "semana"=>$row['tipo']=='OTROS' ? '' : $row['semanadsc'],
			   "nrofactura"=>$row['nrofactura'],
			   "nrotrc"=>$row['nrotrc'],
			   "fecha_f"=>$row['fecha_f'],
			   "project_id"=>$row['project_id'],
			    "pais"=>$row['pais'],
			    "proyecto"=>$row['proyecto'],
				"tipocertificado"=>$row['tipocertificado'],
				"cultivo"=>isset($row['tipo']) ? $row['tipo'] : $row['tipo_sol'], 
			   "sumagricultor"=>number_format($row['sumagricultor'],5),
			   "sumcompra"=>number_format($row['sumcompra'],5),
			   "sumprov"=>number_format($row['sumprov'],5),
			   "usuario"=>$usuariodsc,
			   "dscestado"=>$row['dscestado'],
			   "suma"=>number_format($row['sumagricultor']+$row['sumprov'],5),
			   "codsolicitud"=>$id,
			   "accion"=>$accion,
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='versol'){
	
	$border="";
	if(!empty($_POST['border']))
		$border=" border=1 ";
	
	$codsolicitud = $_POST['codsolicitud'];
	$data_sol=$solicitud->get_one_solicitud($codsolicitud);
	
	
	$codtiponormativaad=$data_sol['codtiponormativaad'];
	$data_soladd=$solicitud->select_one_tiponormativa_ad($codtiponormativaad);
	$codlista=$data_sol['codlista'];
	$project_id=$data_sol['project_id'];
	$flgstatus=$data_sol['flgstatus'];
	$proyecto_res=$solicitud->get_one_proyecto($project_id);
	
	$data_detlista=$solicitud->select_detlistaint_solrend($codlista,$codsolicitud,'inner');
	$data_detcompra=$solicitud->select_detlistacompras($project_id,$codsolicitud,'inner');
	$data_emp=$solicitud->select_tipoempaque($id_pais);

	$res_listaventa=$solicitud->select_resumen_listaint($codsolicitud,$codlista);
	$res_provventa=$solicitud->select_resumen_proventa($codsolicitud);
	
	$data_cul=$solicitud->get_one_lista_cultivo($codlista);
	$lista_res=$solicitud->get_one_listabyId($codlista);
	$tipo=$lista_res['tipo'];
	
	$data_file=$solicitud->get_filebyLista($codlista);
	if(!empty($data_file[0]['rutafile']))
		$rutafile=$data_file[0]['rutafile'];
	$rutaa=explode("/",$rutafile);
	// data_res_det
	
	$data_res_det=[];
	if($codlista!=0){
		$data_res_det=$solicitud->get_one_solicitud_detalle($codlista);
		$tipo=$data_res_det['tipo'];
	}
	
    include("../vista/solicitud/frm_resumen_print.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='verLisImport'){
	
	$border="";
	if(!empty($_POST['border']))
		$border=" border=1 ";
	
	$id_pais = $_POST['id_pais'];
	$estado = $_POST['estado'];
	$nrotrc = $_POST['nrotrc'];
	$descripcion = $_POST['descripcion'];
	
	$columnName=" s.numero ";
	$columnSortOrder=" desc ";

	$searchQuery="";

	if(!empty($id_pais))
		$searchQuery.= " AND s.id_pais='$id_pais' ";
	
	if(!empty($descripcion))
		$searchQuery.=" and ( p.proyect like '%$descripcion%' or s.project_id  like '%$descripcion%' ) ";
		
	if(!empty($nrotrc))
		$searchQuery.=" and  s.nrotrc like '%$nrotrc%' ";
	
	if(!empty($estado))
		$searchQuery.=" and  s.estado ='$estado'  ";
	
	## Fetch records
	$row=0;
	$rowperpage=100000;
	
	$data_OF=$solicitud->select_solicitud($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	include("../vista/solicitud/data_exporta.php");	
	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='verSolicitud'){
    // delete a la base de datos lista integrada data_detlista res_listaventa
	$codsolicitud=$_POST['codsolicitud']; 
	
	$data_sol=$solicitud->get_one_solicitud($codsolicitud);
	$flgstatus=$data_sol['flgstatus'];
	$codtiponormativaad=$data_sol['codtiponormativaad'];
	$data_soladd=$solicitud->select_one_tiponormativa_ad($codtiponormativaad);
	$codlista=$data_sol['codlista'];
	$project_id=$data_sol['project_id'];
	$proyecto_res=$solicitud->get_one_proyecto($project_id);
	
	$data_detlista=$solicitud->select_detlistaint_solrend($codlista,$codsolicitud,'inner');
	$data_detcompra=$solicitud->select_detlistacompras($project_id,$codsolicitud,'inner');
	$data_emp=$solicitud->select_tipoempaque($id_pais);

	$res_listaventa=$solicitud->select_resumen_listaint($codsolicitud,$codlista);
	$res_provventa=$solicitud->select_resumen_proventa($codsolicitud);
	
	$data_cul=$solicitud->get_one_lista_cultivo($codlista);
	$lista_res=$solicitud->get_one_listabyId($codlista);
	$tipo=$lista_res['tipo'];
	$tipo_sol=$data_sol['tipo_sol'];
	
	$data_file=$solicitud->get_filebyLista($codlista);
	if(!empty($data_file[0]['rutafile']))
		$rutafile=$data_file[0]['rutafile'];
	$rutaa=explode("/",$rutafile);
	
	$data_res_det=[];
	if($codlista!=0){
		$data_res_det=$solicitud->get_one_solicitud_detalle($codlista);
		$tipo=$data_res_det['tipo'];
	}
		
    include("../vista/solicitud/frm_resumen_print.php");	
  
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delSolicitud'){
    // delete a la base de datos lista integrada
	$codsolicitud=$_POST['codsolicitud']; 
    $solicitud->estado_solicitud($codsolicitud,'d',$usuario_name);
    echo "Se elimino el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='aproSolicitud'){
    // aprobar a la base de datos lista integrada
	$codsolicitud=$_POST['codsolicitud']; 
	$estado=$_POST['estado']; 
	
    $solicitud->estado_solicitud($codsolicitud,$estado,$usuario_name);
    echo "Se actualizó el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_compra'){
    $data_pais=$solicitud->selec_paises();
	
	$dataidpais=$solicitud->selec_oneid_paises($sess_codpais);
	$id_pais=$dataidpais['id_pais'];
	include("../vista/solicitud/index_compra.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_compra_data'){
	
	//***********************************************************
	// funcion buscador tabla lista integrada
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$estado = $_POST['estado'];
	$codpais = $_POST['codpais'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" s.numero ";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " ";
	if(!empty($codpais))
		$searchQuery.="   and  s.codpais='$codpais'";
	
	## Total number of records without filtering
	$data_maxOF=$solicitud->selec_total_solicitud_compra($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( p.project_id like '%$descripcion%' or p.proyect like '%$descripcion%' or s.proveedor  like '%$descripcion%' or s.nrotc like '%$descripcion%' ) ";
	
	if(!empty($estado))
		$searchQuery.=" and  s.estado='$estado' ";
	

	
	## Total number of record with filtering
	$data_maxOF2=$solicitud->selec_total_solicitud_compra($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$solicitud->select_solicitud_compra($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
			$id=$row['codtransaccion'];
			$accion="<div class='dropdown'>
				  <button class='btn btn-primary dropdown-toggle' type='button' id='dropdownMenuButton' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
					Acciones
				  </button>
				  <div class='dropdown-menu' aria-labelledby='dropdownMenuButton'>";
			if($row['estado']=='p'){
				$accion.=" <a class='dropdown-item btn_aprobSolCompra' id='sol_". $id ."' href='#'>Aprobar</a>";
				$accion.=" <a class='dropdown-item btn_eliSolCompra' id='estproy_". $id ."' href='#'>No aprobar</a>";

			} 
			
			if($row['estado']=='a'){
				$accion.=" <a class='dropdown-item btn_pendienteSolCompra' id='sol_". $id ."' href='#'>Pendiente</a>";
				$accion.=" <a class='dropdown-item btn_eliSolCompra' id='estproy_". $id ."' href='#'>No aprobar</a>";
			} 
			
			if($row['estado']=='d'){
				$accion.=" <a class='dropdown-item btn_aprobSolCompra' id='sol_". $id ."' href='#'>Aprobar</a>";
				$accion.=" <a class='dropdown-item btn_pendienteSolCompra' id='sol_". $id ."' href='#'>Pendiente</a>";
			} 
			$accion.=" <a class='dropdown-item btn_verSolCompra' id='estproy_". $id ."' href='#'>Ver</a>";
			$accion.="	  </div>
				</div>";
		
		   $data[] = array( 
			   "lote"=>$row['lote'],
			   "programa"=>$row['programadsc'],
			   "fechatcf"=>$row['fechatcf'],
			   "nrotc"=>$row['nrotc'],
			   "fechaf"=>$row['fechaf'],
			   "project_id"=>$row['project_id'],
			   "proyecto"=>$row['proyecto'],
			   "proveedor"=>$row['proveedor'],
			   "cultivo"=>$row['cultivodsc'],
			   "variedad"=>$row['variedaddsc'],
			   "status"=>$row['status'],
			    "dias"=>$row['dias'],
			   "total"=>number_format($row['total'],5),
			   "codtransaccion"=>$id,
			   "dscestado"=>$row['dscestado'],
			   "accion"=>$accion,
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='delSolCompra'){
    // delete a la base de datos lista integrada
	$codtransaccion=$_POST['codtransaccion']; 
    $solicitud->estado_solicitudcompra($codtransaccion,'d');
    echo "Se elimino el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='aproSolCompra'){
    // aprobar a la base de datos lista integrada
	$codtransaccion=$_POST['codtransaccion']; 
    $solicitud->estado_solicitudcompra($codtransaccion,'a');	
	echo "Se aprobo el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='pendienteSolCompra'){
    // pendiente a la base de datos lista integrada
	$codtransaccion=$_POST['codtransaccion']; 
    $solicitud->estado_solicitudcompra($codtransaccion,'p');	
	echo "Se actualizó el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='verSolCompra'){
    // aprobar a la base de datos lista integrada
	$codtransaccion=$_POST['codtransaccion']; 
   $rescompra=$solicitud->selec_one_compra($codtransaccion);
  
   include("../vista/solicitud/ver_compra.php");

}	




?>
