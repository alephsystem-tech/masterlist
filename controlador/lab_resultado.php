<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_usuario_modelo.php");
include("../modelo/lab_resultado_modelo.php");
include("../modelo/mae_pais_modelo.php");

$usuario=new prg_usuario_model();
$lab_resultado=new lab_resultado_model();
$pais=new mae_pais_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$pathLabRes = '../archivos/labResultado/'; // upload directory
$valid_extensions = array('xls','xlsx','doc','docx','pdf'); // valid extensions

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];


// liberias composer de excel
//******************************************
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
//******************************************

//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	//***********************************************************
	// funcion index actividades de auditor por fecha
	//***********************************************************
	
	
	$laboratorio_res=$lab_resultado->select_laboratorios($sess_codpais);
	$cultivo_res=$lab_resultado->select_cultivos($sess_codpais);
	$producto_res=$lab_resultado->select_productos($sess_codpais);
	$anio=date("Y");
	$dataEstadi=$lab_resultado->select_data_estadistica($sess_codpais,$anio);
	
	include("../vista/labresultado/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_result'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$codlaboratorio = $_POST['codlaboratorio'];
	$nrolaboratorio = $_POST['nrolaboratorio'];
	$proyect = $_POST['proyect'];
	$codcultivo = $_POST['codcultivo'];
	$codproducto = $_POST['codproducto'];
	$facturado = $_POST['facturado'];
	
	$fechai = $_POST['fechai'];
	$fechaf = $_POST['fechaf'];
	$fechafaci = $_POST['fechafaci'];
	$fechafacf = $_POST['fechafacf'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" lab_resultado.fecha ";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais' ";

	if(!empty($proyect))
		$searchQuery.=" and (proyecto like '%".$proyect."%' or project_id='$proyect') ";
	
	if(!empty($codlaboratorio)){
		$searchQuery.=" and codlaboratorio='$codlaboratorio' ";
	}		 
	
	if(!empty($codcultivo)){
		$searchQuery.=" and codcultivo='$codcultivo' ";
	}
	if(!empty($nrolaboratorio)){
		$searchQuery.=" and nrolaboratorio like '%$nrolaboratorio%' ";
	}
	if(!empty($codproducto)){
		$searchQuery.=" and codproducto='$codproducto' ";
	}
	if($facturado=='s'){
		$searchQuery.=" and ifnull(nrofactura,'')!='' ";
	}
	if($facturado=='n'){
		$searchQuery.=" and ifnull(nrofactura,'')='' ";
	}
	
	if($fechai!='') 
		$searchQuery.=" and to_days(fechaenvio)>= to_days('".formatdatedos($fechai)."') ";
    if($fechaf!='') 
		$searchQuery.=" and to_days(fechaenvio)<= to_days('".formatdatedos($fechaf)."') ";

	if($fechafaci!='') 
		$searchQuery.=" and to_days(fechafacturacliente)>= to_days('".formatdatedos($fechafaci)."') ";
    if($fechafacf!='') 
		$searchQuery.=" and to_days(fechafacturacliente)<= to_days('".formatdatedos($fechafacf)."') ";

	
	## Total number of records without filtering
	$data_maxOF=$lab_resultado->selec_total_resultado($searchQuery);
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$lab_resultado->selec_total_resultado($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$lab_resultado->select_resultado($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		
		foreach($data_OF as $row) {
				 
			$id=$row['codresultado'];
				
			$edita="<button type='button' id='labRes_". $id ."'  class='btn  btn_edilabRes'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='labRes_". $id ."'  class='btn  btn_elilabRes'><i class='fas fa-trash'></i> </button>";
			
		   $data[] = array( 
			   "codresultado"=>$id,
			   "fecha"=>$row['fecha'],
			   "project_id"=>$row['project_id'],
			   "pais"=>$row['pais'],
			   "nrolaboratorio"=>$row['nrolaboratorio'],
			   "nromuestra_cliente"=>$row['nromuestra_cliente'],
			   "nromuestracu"=>$row['nromuestracu'],
			   "fechaenvio"=>$row['fechaenvio_f'],
			   "fechafacturacliente_f"=>$row['fechafacturacliente_f'],
			   "resultado"=>$row['resultado'],
			   "preciodol"=>$row['preciodol'],
			   "montocliente"=>$row['montocliente'],
			   "facturacu"=>$row['facturacu'],
			   "proyecto"=>str_replace('"','',json_encode($row['proyectofull'],JSON_UNESCAPED_UNICODE)),
				"laboratorio"=>str_replace('"','',json_encode($row['laboratorio'],JSON_UNESCAPED_UNICODE)),
				"responsable"=>str_replace('"','',json_encode($row['responsable'],JSON_UNESCAPED_UNICODE)),
				"analisis"=>str_replace('"','',json_encode($row['analisis'],JSON_UNESCAPED_UNICODE)),
				"cultivo"=>str_replace('"','',json_encode($row['cultivo'],JSON_UNESCAPED_UNICODE)),
				"nrofactura"=>str_replace('"','',json_encode($row['nrofactura'],JSON_UNESCAPED_UNICODE)),
				"producto"=>str_replace('"','',json_encode($row['producto'],JSON_UNESCAPED_UNICODE)),
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='impResLaboratorio'){
    // open formualario para editar
	
    include("../vista/labresultado/frm_importar.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_impLabRes'){
    // proceso update a la base de datos usuarios
	
	$valid_extensions = array('xls','xlsx'); // valid extensions
	$nunafectadas=0;
	$monto=0;
	$codunico=strtotime(date('Y-m-d h:m:s'));			
	if(isset($_FILES['fileexcel'])){
		$img = $_FILES['fileexcel']['name'];
		$tmp = $_FILES['fileexcel']['tmp_name'];

		// get uploaded file's extension
		$ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
		// can upload same image using rand function
		// check's valid format
		if(in_array($ext, $valid_extensions)){					
			$pathLabRes = $pathLabRes.strtolower($img);	
			//echo $pathLabRes;
			if(move_uploaded_file($tmp,$pathLabRes)){
				
				// leer archivo
				$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($pathLabRes);
				$sheetCount = $spreadsheet->getSheetCount();
				$sheetNames = $spreadsheet->getSheetNames();
				
				$sheet = $spreadsheet->getSheet(0);
				
				$sheetData = $sheet->toArray(null, true, true, true);
				$highestRow = $sheet->getHighestRow(); // e.g. 10
				$highestColumn = $sheet->getHighestColumn(); // e.g 'F'
				$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
				// fin data


				$data_sql="";
				for ($row = 1; $row <= $highestRow; ++$row) {
				
					unset($array);
					
					for ($col = 1; $col <= 33; $col++) {
						$value = $sheet->getCellByColumnAndRow($col, $row)->getValue();
					
						if(strstr($value,'='))
							$value = $sheet->getCellByColumnAndRow($col, $row)->getOldCalculatedValue();
					
			
						if(!empty($value) or $value==0){
							$value=str_replace("'","",$value);
							
							if($col==17 or $col==18 or $col==19 or $col==33 or $col==26){
								
								if(strpos($value,'/')){
									$arr=explode('/',$value);
									if(strlen($arr[2])==4)
										$value=formatdatedos($value);
								}else if(strpos($value,'-')){
									$arr=explode('/',$value);
									if(strlen($arr[2])==4)
										$value=formatdateCal($value);
								}else if(is_numeric($value)){
									$value=convertDate($value);
									
								}
							}
							
							$array[$col]=$value;
						}else $array[$col]=''; 
					}
					
					if($row>1){ // no cabeceras
						if($array[1]!='' and $array[1]!='CU'){
							$data_part="(
							'$array[1]','$array[2]','$array[3]','$array[4]','$array[5]','$array[6]','$array[7]','$array[8]','$array[9]',
							'$array[10]','$array[11]','$array[12]','$array[13]','$array[14]','$array[15]','$array[16]','$array[17]','$array[18]',
							'$array[19]','$array[20]','$array[21]','$array[22]','$array[23]','$array[24]','$array[25]','$array[26]','$array[27]','$array[28]',
							'$array[29]','$array[30]','$array[31]','$array[33]','$codunico'
							)";
							if($data_sql=='') $data_sql=$data_part;
							else $data_sql.=",".$data_part;
						}
					}						
					
				}
				// echo $data_sql;
				if($data_sql!=''){
					$nunafectadas=$lab_resultado->insert_resultado($data_sql,$codunico,$sess_codpais);
					$monto=$lab_resultado->monto_insert_resultado($codunico,$sess_codpais,$tcEuUS);
				}
			}
		}else{
			echo 'invalid';
		}
	}	
	
	$DatTempoporal=$lab_resultado->select_data_temporalLab($codunico);
	include("../vista/labresultado/frm_importar_2.php");
	
	//echo $nunafectadas;	  
}else if(!empty($_POST['accion']) and $_POST['accion']=='finmigra'){
	
	$codunico=$_POST['codunico'];
	
	$nunafectadas=$lab_resultado->insert_resultado_2($codunico,$sess_codpais);
	echo $nunafectadas;		
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='expRespLaboratorio'){
    // delete a la base de datos usuarios
	$codlaboratorio = $_POST['codlaboratorio'];
	$proyect = $_POST['proyect'];
	$codcultivo = $_POST['codcultivo'];
	$codproducto = $_POST['codproducto'];
	$facturado = $_POST['facturado'];
	$fechai = $_POST['fechai'];
	$fechaf = $_POST['fechaf'];
	$fechafaci = $_POST['fechafaci'];
	$fechafacf = $_POST['fechafacf'];
	
	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais' ";

	if(!empty($proyect))
		$searchQuery.=" and (proyecto like '%".$proyect."%' or project_id='$proyect') ";
	
	if(!empty($codlaboratorio)){
		$searchQuery.=" and codlaboratorio='$codlaboratorio' ";
		$data_lab=$lab_resultado->select_one_laboratorio($codlaboratorio);
	}		 
	
	if(!empty($codcultivo)){
		$searchQuery.=" and codcultivo='$codcultivo' ";
		$data_cul=$lab_resultado->select_one_cultivo($codcultivo);
	}
	if(!empty($codproducto)){
		$searchQuery.=" and codproducto='$codproducto' ";
		$data_prod=$lab_resultado->select_one_producto($codproducto);
	}
	
	if($facturado=='s')
		$searchQuery.=" and ifnull(nrofactura,'')!='' ";
	
	if($facturado=='n')
		$searchQuery.=" and ifnull(nrofactura,'')='' ";
		
	if($fechai!='') 
		$searchQuery.=" and to_days(fechaenvio)>= to_days('".formatdatedos($fechai)."') ";
    if($fechaf!='') 
		$searchQuery.=" and to_days(fechaenvio)<= to_days('".formatdatedos($fechaf)."') ";

	if($fechafaci!='') 
		$searchQuery.=" and to_days(fechafacturacliente)>= to_days('".formatdatedos($fechafaci)."') ";
    if($fechafacf!='') 
		$searchQuery.=" and to_days(fechafacturacliente)<= to_days('".formatdatedos($fechafacf)."') ";
	
	
	$columnName=" lab_resultado.fecha ";
	$columnSortOrder=" desc ";
	$row=0;
	$rowperpage=100000;
	$data_OF=$lab_resultado->select_resultado($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	include("../vista/labresultado/data_exporta.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='delLabResultado'){
    // delete a la base de datos usuarios
	$codresultado=$_POST['codresultado']; 
    $lab_resultado->delete_labResultado($codresultado);
    echo "Se elimino el registro.";
}


?>
