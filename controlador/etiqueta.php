<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");
include("../modelo/prg_usuario_modelo.php");
include("../modelo/etiqueta_modelo.php");
include("../modelo/mae_pais_modelo.php");

$usuario=new prg_usuario_model();
$tbletiqueta=new etiqueta_model();
$pais=new mae_pais_model();

// liberias composer de excel
//******************************************
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
//******************************************


// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$pathEtiqueta = '../archivos/etiqueta/'; // upload directory
$valid_extensions = array('xls','xlsx','doc','docx','pdf'); // valid extensions

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	//***********************************************************
	// funcion index actividades de auditor por fecha
	//***********************************************************
	
	$anio=date("Y");
	$dataEstadi=$tbletiqueta->select_data_estadistica($sess_codpais,$anio);
	foreach($dataEstadi as $row){
		$arrAnioNum[$row['anio']][$row['mes']]=$row['numero'];
		$arrAnioCos[$row['anio']][$row['mes']]=$row['costo'];
	}
	
	include("../vista/etiqueta/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_result'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$proyect = $_POST['proyect'];
	
	$fechai = $_POST['fechai'];
	$fechaf = $_POST['fechaf'];
	$fechafaci = $_POST['fechafaci'];
	$fechafacf = $_POST['fechafacf'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" fecaprobacion ";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais' ";

	if(!empty($proyect))
		$searchQuery.=" and (proyecto like '%".$proyect."%' or project_id like '%$proyect%') ";
	
	if($fechai!='') 
		$searchQuery.=" and to_days(fecrecepcion)>= to_days('".formatdatedos($fechai)."') ";
    if($fechaf!='') 
		$searchQuery.=" and to_days(fecrecepcion)<= to_days('".formatdatedos($fechaf)."') ";

	if($fechafaci!='') 
		$searchQuery.=" and to_days(fecaprobacion)>= to_days('".formatdatedos($fechafaci)."') ";
    if($fechafacf!='') 
		$searchQuery.=" and to_days(fecaprobacion)<= to_days('".formatdatedos($fechafacf)."') ";

	
	## Total number of records without filtering
	$data_maxOF=$tbletiqueta->selec_total_etiqueta($searchQuery);
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$tbletiqueta->selec_total_etiqueta($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$tbletiqueta->select_etiqueta($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		
		foreach($data_OF as $row) {
				 
			$id=$row['codetiqueta'];
				
			$edita="<button type='button' id='etiq_". $id ."'  class='btn  btn_ediEtiqueta'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='etiq_". $id ."'  class='btn  btn_eliEtiqueta'><i class='fas fa-trash'></i> </button>";
			
		   $data[] = array( 
			   "codetiqueta"=>$id,
			   "codigo"=>$row['codigo'],
			   "fecrecepcion"=>$row['fecrecepcion_f'],
			   "fecaprobacion"=>$row['fecaprobacion_f'],
			   "duracion"=>$row['duracion'],
			   "project_id"=>$row['project_id'],
			   "proyecto"=>$row['proyecto'],
			   "pais"=>$row['pais'],
			   "asistente"=>$row['asistente'],
			   "private"=>$row['private'],
			   "preciodol"=>$row['preciodol'],
			   "accion"=>$row['accion'],
			   "proyectofull"=>str_replace('"','',json_encode($row['proyectofull'],JSON_UNESCAPED_UNICODE)),
				"producto"=>str_replace('"','',json_encode($row['producto'],JSON_UNESCAPED_UNICODE)),
				"solicitante"=>str_replace('"','',json_encode($row['solicitante'],JSON_UNESCAPED_UNICODE)),
				"cliente"=>str_replace('"','',json_encode($row['cliente'],JSON_UNESCAPED_UNICODE)),
				"comentarios"=>str_replace('"','',json_encode($row['comentarios'],JSON_UNESCAPED_UNICODE)),
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='impEtiqueta'){
    // open formualario para editar
	
    include("../vista/etiqueta/frm_importar.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_impEtiqueta'){
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
			$pathEtiqueta = $pathEtiqueta.strtolower($img);	
			//echo $pathLabRes;
			if(move_uploaded_file($tmp,$pathEtiqueta)){
				
				// leer archivo
				$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($pathEtiqueta);
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
					for ($col = 1; $col <= 15; $col++) {
						$value = $sheet->getCellByColumnAndRow($col, $row)->getValue();
					
						if(strstr($value,'='))
							$value = $sheet->getCellByColumnAndRow($col, $row)->getOldCalculatedValue();
					
			
						if(!empty($value) or $value==0){
							$value=str_replace("'","",$value);
							
							if($col==2 or $col==3){
								
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
						if($array[1]!=''){
							$data_part="(
							'$array[1]','$array[2]','$array[3]','$array[4]','$array[5]','$array[6]','$array[7]','$array[8]','$array[9]',
							'$array[10]','$array[11]','$array[12]','$array[13]','$array[14]','$array[15]',
							'$sess_codpais','$usuario_name','4ip','$codunico'
							)";
							if($data_sql=='') $data_sql=$data_part;
							else $data_sql.=",".$data_part;
							
							//echo $data_part."<br>";
						}
					
					}
				}
				
				if($data_sql!=''){
					$nunafectadas=$tbletiqueta->insert_resultado($data_sql,$codunico,$sess_codpais);
					$monto=$tbletiqueta->monto_insert_resultado($codunico,$sess_codpais);
				}
			}
		}else{
			echo 'invalid';
		}
	}	
	
	$DatTempoporal=$tbletiqueta->select_data_temporalEtiqueta($codunico);
	include("../vista/etiqueta/frm_importar_2.php");
	
	//echo $nunafectadas;	  
}else if(!empty($_POST['accion']) and $_POST['accion']=='finmigra'){
	
	$codunico=$_POST['codunico'];
	
	$nunafectadas=$tbletiqueta->insert_resultado_2($codunico,$sess_codpais);
	echo $nunafectadas;		
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='expEtiqueta'){
    // delete a la base de datos usuarios
	$proyect = $_POST['proyect'];
	$fechai = $_POST['fechai'];
	$fechaf = $_POST['fechaf'];
	$fechafaci = $_POST['fechafaci'];
	$fechafacf = $_POST['fechafacf'];
	
	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais' ";

	if(!empty($proyect))
		$searchQuery.=" and (proyecto like '%".$proyect."%' or project_id='$proyect') ";
	
		
	if($fechai!='') 
		$searchQuery.=" and to_days(fecrecepcion)>= to_days('".formatdatedos($fechai)."') ";
    if($fechaf!='') 
		$searchQuery.=" and to_days(fecrecepcion)<= to_days('".formatdatedos($fechaf)."') ";

	if($fechafaci!='') 
		$searchQuery.=" and to_days(fecaprobacion)>= to_days('".formatdatedos($fechafaci)."') ";
    if($fechafacf!='') 
		$searchQuery.=" and to_days(fecaprobacion)<= to_days('".formatdatedos($fechafacf)."') ";
	
	
	$columnName=" fecrecepcion ";
	$columnSortOrder=" desc ";
	$row=0;
	$rowperpage=100000;
	$data_OF=$tbletiqueta->select_etiqueta($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	include("../vista/etiqueta/data_exporta.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='delEtiqueta'){
    // delete a la base de datos usuarios
	$codetiqueta=$_POST['codetiqueta']; 
    $tbletiqueta->delete_etiqueta($codetiqueta);
    echo "Se elimino el registro.";
}


?>
