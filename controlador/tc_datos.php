<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_usuario_modelo.php");
include("../modelo/auditor_modelo.php");
include("../modelo/tc_datos_modelo.php");
include("../modelo/mae_pais_modelo.php");
include("../modelo/prg_pais_modelo.php");

$usuario=new prg_usuario_model();
$tc_datos=new tc_datos_model();
$auditor=new auditor_model();
$pais=new mae_pais_model();
$prgpais=new prg_pais_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

// liberias composer de excel
//******************************************
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
//******************************************


$pathTc = '../archivos/tcDatos/'; // upload directory
$valid_extensions = array('xls'); // valid extensions

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	//***********************************************************
	// funcion index actividades de auditor por fecha
	//***********************************************************
	$anio=date("Y");
	
	$dataPais=$prgpais->selec_one_pais($sess_codpais);
	$porigv=$dataPais['impuesto'];
	$tcEuUS=$dataPais['tceu_dol'];
	$G_tc=$dataPais['tc'];
	
	$dataEstadi=$tc_datos->select_data_estadistica($sess_codpais,$anio,$tcEuUS);
	if(!empty($dataEstadi)){
		foreach($dataEstadi as $row){
			$arrayMonto[$row['anio']][$row['mes']]=$row['costo'];
			$arrayNum[$row['anio']][$row['mes']]=$row['numero'];
		}
	}
	
	include("../vista/tcdatos/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_result'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$proyecto = $_POST['proyecto'];
	$asistente = $_POST['asistente'];
	$subprograma = $_POST['subprograma'];
	$itc = $_POST['itc'];
	$consignie = $_POST['consignie'];
	$facturado = $_POST['facturado'];
	
	$producto = $_POST['producto'];
	$pais_origen = $_POST['pais_origen'];

	$fechai = $_POST['fechai'];
	$fechaf = $_POST['fechaf'];
	$fechafaci = $_POST['fechafaci'];
	$fechafacf = $_POST['fechafacf'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" fecha_emision ";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais' ";
	

	## Total number of records without filtering
	$data_maxOF=$tc_datos->selec_total_resultado($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($proyecto)) 
		$searchQuery.= " and (clientefinal like '%$proyecto%' or  proyecto like '%$proyecto%' or tc_datos.project_id like '%$proyecto%')";
	if(!empty($asistente)) $searchQuery.=" and asistente like '%$asistente%' ";
	if(!empty($subprograma)) $searchQuery.=" and subprograma like '%$subprograma%' ";
	if(!empty($itc)) $searchQuery.=" and itc like '%$itc%' ";
	if(!empty($consignie)) $searchQuery.=" and consignie like '%$consignie%' ";
	if(!empty($producto)) $searchQuery.=" and producto like '%$producto%' ";
	if(!empty($pais_origen)) $searchQuery.=" and pais_origen like '%$pais_origen%' ";
	if(!empty($fechai)){
		$fechai = DateTime::createFromFormat('d/m/Y', $fechai)->format('Y-m-d');
		$searchQuery.=" and to_days(fecha_emision)>= to_days('$fechai') ";
	} 
	if(!empty($fechaf)){
		$fechaf = DateTime::createFromFormat('d/m/Y', $fechaf)->format('Y-m-d');
		$searchQuery.=" and to_days(fecha_emision)<= to_days('$fechaf') ";
	} 
	if(!empty($fechafaci)){
		$fechafaci = DateTime::createFromFormat('d/m/Y', $fechafaci)->format('Y-m-d');
		$searchQuery.=" and to_days(fechafactura)>= to_days('$fechafaci') ";
	} 
	if(!empty($fechafacf)){
		$fechafacf = DateTime::createFromFormat('d/m/Y', $fechafacf)->format('Y-m-d');
		$searchQuery.=" and to_days(fechafactura)<= to_days('$fechafacf') ";
	} 
	if($facturado=='s') $searchQuery.=" and ifnull(fechafactura,'')!='' ";
	if($facturado=='n') $searchQuery.=" and ifnull(fechafactura,'')='' ";
	
	## Total number of record with filtering
	$data_maxOF2=$tc_datos->selec_total_resultado($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$tc_datos->select_resultado($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		
		foreach($data_OF as $row) {
				 
			$id=$row['codtc'];
				
			$edita="<button type='button' id='tc_". $id ."'  class='btn  btn_editc'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='tc_". $id ."'  class='btn  btn_elitc'><i class='fas fa-trash'></i> </button>";
			
		   $data[] = array( 
			"codresultado"=>$id,
			"fecha_cis"=>str_replace('"','',json_encode($row['fecha'],JSON_UNESCAPED_UNICODE)),
			"tipo"=>str_replace('"','',json_encode($row['tipo'],JSON_UNESCAPED_UNICODE)),
			"trc"=>str_replace('"','',json_encode($row['trc'],JSON_UNESCAPED_UNICODE)),
			"traces"=>str_replace('"','',json_encode($row['traces'],JSON_UNESCAPED_UNICODE)),
			"lote"=>str_replace('"','',json_encode($row['lote'],JSON_UNESCAPED_UNICODE)),
			"volumen"=>str_replace('"','',json_encode($row['volumen'],JSON_UNESCAPED_UNICODE)),
			"cliente"=>str_replace('"','',json_encode($row['cliente'],JSON_UNESCAPED_UNICODE)),

			"itc"=>str_replace('"','',json_encode($row['itc'],JSON_UNESCAPED_UNICODE)),
			"fecha_emision"=>str_replace('"','',json_encode($row['fecha_emision'],JSON_UNESCAPED_UNICODE)),
			"fechafactura"=>str_replace('"','',json_encode($row['fechafactura'],JSON_UNESCAPED_UNICODE)),
			"costo_eu"=>str_replace('"','',json_encode($row['costo_eu'],JSON_UNESCAPED_UNICODE)),
			"costo_usd"=>str_replace('"','',json_encode($row['costo_usd'],JSON_UNESCAPED_UNICODE)),
			"cos_courier_usd"=>str_replace('"','',json_encode($row['cos_courier_usd'],JSON_UNESCAPED_UNICODE)),
			
			"lote"=>str_replace('"','',json_encode($row['lote'],JSON_UNESCAPED_UNICODE)),
			"nrotrk"=>str_replace('"','',json_encode($row['nrotrk'],JSON_UNESCAPED_UNICODE)),
			"consignie"=>str_replace('"','',json_encode($row['consignie'],JSON_UNESCAPED_UNICODE)),
			"cu"=>str_replace('"','',json_encode($row['cu'],JSON_UNESCAPED_UNICODE)),
			
			"asistente"=>str_replace('"','',json_encode($row['asistente'],JSON_UNESCAPED_UNICODE)),
			"clientefinal"=>str_replace('"','',json_encode($row['clientefinalfull'],JSON_UNESCAPED_UNICODE)),
			"subprograma"=>str_replace('"','',json_encode($row['subprograma'],JSON_UNESCAPED_UNICODE)),
			"proyecto"=>str_replace('"','',json_encode($row['proyectofull'],JSON_UNESCAPED_UNICODE)),
			"producto"=>str_replace('"','',json_encode($row['producto'],JSON_UNESCAPED_UNICODE)),
			"pais_origen"=>str_replace('"','',json_encode($row['pais_origen'],JSON_UNESCAPED_UNICODE)),
			"pais_destino"=>str_replace('"','',json_encode($row['pais_destino'],JSON_UNESCAPED_UNICODE)),
			"modo_envio"=>str_replace('"','',json_encode($row['modo_envio'],JSON_UNESCAPED_UNICODE)),
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='impTc'){
    // open formualario para editar
	
    include("../vista/tcdatos/frm_importar.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_impTc'){
    // proceso update a la base de datos usuarios

	$dataPais=$prgpais->selec_one_pais($sess_codpais);
	$tcEuUS=$dataPais['tceu_dol'];

	
	$valid_extensions = array('xls','xlsxs'); // valid extensions
	$nunafectadas=0;
	$monto=0;
	$codunico=strtotime(date('Y-m-d h:m:s'));			
	if(isset($_FILES['fileexcel'])){
		$img = $_FILES['fileexcel']['name'];
		$tmp = $_FILES['fileexcel']['tmp_name'];

		// get uploaded file's extension
		$ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));

		if(in_array($ext, $valid_extensions)){					
			$pathTc = $pathTc.strtolower($img);	
			if(move_uploaded_file($tmp,$pathTc)){
				
				// leer archivo
				$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($pathTc);
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
				//for ($i = 1; $i <= $data->sheets[0]['numRows'] ; $i++) {
					unset($array);
					
					for ($col = 1; $col <= 33; $col++) {
						$value = $sheet->getCellByColumnAndRow($col, $row)->getValue();
					
						if(strstr($value,'='))
							$value = $sheet->getCellByColumnAndRow($col, $row)->getOldCalculatedValue();
						
					//for ($j = 1; $j <= 31; $j++) {
						if(!empty($value) or $value==0){
							$value=str_replace("'","",$value);
							
							if($col==2 or $col==13 or $col==31){
								
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
						if($array[1]!='' and $array[1]!='ASISTENTE'){
						
							$array[10]=preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $array[10]);
							$array[30]=preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $array[30]);
							
							
							$data_part="(
							'$array[1]','$array[2]','$array[3]','$array[4]','$array[5]','$array[6]','$array[7]','$array[8]','$array[9]',
							'$array[10]','$array[11]','$array[12]','$array[13]','$array[14]','$array[15]','$array[16]','$array[17]','$array[18]',
							'$array[19]','$array[20]','$array[21]','$array[22]','$array[23]','$array[24]','$array[25]','$array[26]','$array[27]','$array[28]','$array[29]','$array[30]','$array[31]'
							)";
							if($data_sql=='') $data_sql=$data_part;
							else $data_sql.=",".$data_part;
						}
					}
                }
				 
				if($data_sql!=''){
					$nunafectadas=$tc_datos->insert_resultado($data_sql,$codunico,$sess_codpais);
					$monto=$tc_datos->monto_insert_resultado($codunico,$sess_codpais,$tcEuUS);
				}
			}
		}else{
			echo 'invalid';
		}
	}	
	
	$DatTempoporal=$tc_datos->select_data_temporalTC($sess_codpais);
	include("../vista/tcdatos/frm_importar_2.php");
	// echo $nunafectadas;	  

}else if(!empty($_POST['accion']) and $_POST['accion']=='finmigra'){
	$nunafectadas=$tc_datos->insert_resultado_2($sess_codpais);
	echo $nunafectadas;	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='expTc'){
    // delete a la base de datos usuarios
	$proyecto = $_POST['proyecto'];
	$asistente = $_POST['asistente'];
	$subprograma = $_POST['subprograma'];
	$itc = $_POST['itc'];
	$facturado = $_POST['facturado'];
	
	$producto = $_POST['producto'];
	
	if(!empty($_POST['pais_origen']))
		$pais_origen = $_POST['pais_origen'];

	$fechai = $_POST['fechai'];
	$fechaf = $_POST['fechaf'];
	$fechafaci = $_POST['fechafaci'];
	$fechafacf = $_POST['fechafacf'];
	
	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais' ";
	if(!empty($proyecto)) 
		$searchQuery.= " and (clientefinal like '%$proyecto%' or  proyecto like '%$proyecto%' or tc_datos.project_id like '%$proyecto%')";
	if(!empty($asistente)) $searchQuery.=" and asistente like '%$asistente%' ";
	if(!empty($subprograma)) $searchQuery.=" and subprograma like '%$subprograma%' ";
	if(!empty($itc)) $searchQuery.=" and itc like '%$itc%' ";
	if(!empty($producto)) $searchQuery.=" and producto like '%$producto%' ";
	if(!empty($pais_origen)) $searchQuery.=" and pais_origen like '%$pais_origen%' ";
	if(!empty($fechai)){
		$fechai = DateTime::createFromFormat('d/m/Y', $fechai)->format('Y-m-d');
		$searchQuery.=" and to_days(fecha_emision)>= to_days('$fechai') ";
	} 
	if(!empty($fechaf)){
		$fechaf = DateTime::createFromFormat('d/m/Y', $fechaf)->format('Y-m-d');
		$searchQuery.=" and to_days(fecha_emision)<= to_days('$fechaf') ";
	} 
	if(!empty($fechafaci)){
		$fechafaci = DateTime::createFromFormat('d/m/Y', $fechafaci)->format('Y-m-d');
		$searchQuery.=" and to_days(fechafactura)>= to_days('$fechafaci') ";
	} 
	if(!empty($fechafacf)){
		$fechafacf = DateTime::createFromFormat('d/m/Y', $fechafacf)->format('Y-m-d');
		$searchQuery.=" and to_days(fechafactura)<= to_days('$fechafacf') ";
	} 
	if($facturado=='s') $searchQuery.=" and ifnull(fechafactura,'')!='' ";
	if($facturado=='n') $searchQuery.=" and ifnull(fechafactura,'')='' ";
	
	
	$columnName=" fecha_emision ";
	$columnSortOrder=" desc ";
	$row=0;
	$rowperpage=100000;
	$data_OF=$tc_datos->select_resultado($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	include("../vista/tcdatos/data_exporta.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='delTc'){
    // delete a la base de datos usuarios
	$codtc=$_POST['codtc']; 
    $tc_datos->delete_Tc($codtc);
    echo "Se elimino el registro.";
}


?>
