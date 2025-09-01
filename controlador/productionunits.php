<?php

include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_proyecto_modelo.php");
include("../modelo/prg_productionunits_modelo.php");

$proyecto=new prg_proyecto_model();
$productionunits=new prg_productionunits_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

$pathProductionunits = '../archivos/productionunits/'; // upload directory
$valid_extensions = array('xls','xlsx'); // valid extensions


// liberias composer de excel
//******************************************
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
//******************************************

//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='web_index'){
	//**********************************
	// mostrar index de calendario
	//**********************************
    include("../vista/productionunits/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_productionunits'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" id_product_unit";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais='$sess_codpais'";
	
	## Total number of records without filtering
	$data_maxOF=$productionunits->selec_total_productionunits($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( unit_ref like '%$descripcion%' or  unit_name like '%$descripcion%') ";
	
	## Total number of record with filtering
	$data_maxOF2=$productionunits->selec_total_productionunits($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$productionunits->select_productionunits($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id_product_unit'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediProduct'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliProduct'><i class='fas fa-trash'></i> </button>";

		   $data[] = array( 
			   "unit_ref"=>str_replace('"','',json_encode($row['unit_ref'],JSON_UNESCAPED_UNICODE)),
			   "unit_name"=>str_replace('"','',json_encode($row['unit_name'],JSON_UNESCAPED_UNICODE)),
			   "relation"=>str_replace('"','',json_encode($row['relation'],JSON_UNESCAPED_UNICODE)),
			   "city"=>str_replace('"','',json_encode($row['city'],JSON_UNESCAPED_UNICODE)),
			   "country"=>str_replace('"','',json_encode($row['country'],JSON_UNESCAPED_UNICODE)),
			   "project_ref"=>str_replace('"','',json_encode($row['project_ref'],JSON_UNESCAPED_UNICODE)),
			   "id_product_unit"=>$id,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editProduct'){
	$id_product_unit="";
	if(!empty($_POST['id_product_unit'])){
		$data_res=$productionunits->selec_one_productionunits($_POST['id_product_unit']);
	}	
	$data_proyecto=$proyecto->select_proyecto_Select($sess_codpais);
	
    include("../vista/productionunits/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detProduct'){
    // proceso update a la base de datos usuarios
	$unit_ref=$_POST['unit_ref'];
	$unit_name=$_POST['unit_name'];
	$relation=$_POST['relation'];
	$city=$_POST['city'];
	$country=$_POST['country'];
	$project_ref=$_POST['project_ref'];
	
	if(empty($_POST['id_product_unit']))
		$id_product_unit=$productionunits->insert_productionunits($unit_ref,$unit_name,$relation,$city,$country,$project_ref,$sess_codpais,$usuario_name,$ip);
	else{
		$id_product_unit=$_POST['id_product_unit']; // id
		$productionunits->update_productionunits($id_product_unit,$unit_ref,$unit_name,$relation,$city,$country,$project_ref,$sess_codpais,$usuario_name,$ip);
	}	

	echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delProduct'){
    // delete a la base de datos usuarios
	$id_product_unit=$_POST['id_product_unit']; 
    $productionunits->delete_productionunits($id_product_unit);
    echo "Se elimino el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='impProduct'){
    include("../vista/productionunits/frm_importar.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_impProduct'){
    
    $nunafectadas="";
	$codunico=strtotime(date('Y-m-d h:m:s'));	
	
	if(isset($_FILES['fileexcel'])){
		$img = $_FILES['fileexcel']['name'];
		$tmp = $_FILES['fileexcel']['tmp_name'];

		// get uploaded file's extension
		$ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
		// can upload same image using rand function
		// check's valid format
		if(in_array($ext, $valid_extensions)){					
			$pathProductionunits = $pathProductionunits.strtolower($img);	
			if(move_uploaded_file($tmp,$pathProductionunits)){
				
				// leer archivo
				$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($pathProductionunits);
				$sheetCount = $spreadsheet->getSheetCount();
				$sheetNames = $spreadsheet->getSheetNames();
				
				$sheet = $spreadsheet->getSheet(0);
				
				$sheetData = $sheet->toArray(null, true, true, true);
				$highestRow = $sheet->getHighestRow(); // e.g. 10
				$highestColumn = $sheet->getHighestColumn(); // e.g 'F'
				$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
				// fin data


				$data_sql="";
				for ($i = 1; $i <= $highestRow; ++$i) {
					
					if($i>1){ // no cabeceras
						$valueproj = $sheet->getCellByColumnAndRow(1, $i)->getValue();
						if(!empty($valueproj)){
							
							$data_part="('$codunico'";
							for ($j = 1; $j <=25 ; $j++) { 
								$value = $sheet->getCellByColumnAndRow($j, $i)->getValue();
							
								if(strstr($value,'='))
									$value = $sheet->getCellByColumnAndRow($j, $i)->getOldCalculatedValue();
								
								$data_part.=",'". caracterBad($value)."'";
							}
							$data_part.=" ,'$sess_codpais') ";
						
							if($data_sql=='') $data_sql=$data_part;
							else $data_sql.=",".$data_part;
						}
					}
				}
				 
				if($data_sql!=''){
					$productionunits->insert_productionunitsFormFile($data_sql,$codunico,$sess_codpais);
					$res=$productionunits->select_productionunitsFormFile($codunico,$sess_codpais);
					$nunafectadas=$res['total'];
					
					$res=$productionunits->select_productionunitsFormFileMigrado($codunico,$sess_codpais);
					$nunmigradas="0";
					if(!empty($res['total']))
						$nunmigradas=$res['total'];
					
					$productionunits->procedure_productionunitsFormFile($codunico,$sess_codpais);
					echo "Se migraron $nunmigradas registros de $nunafectadas importados del archivo.";
				}
			}
		}else{
			echo 'Archivo invalid';
		}
	}	
	
	
}


?>
