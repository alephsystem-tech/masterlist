<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_proyectocosto_modelo.php");
$proyectocosto=new prg_proyectocosto_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$pathProyCosto = '../archivos/proyectocosto/'; // upload directory
$valid_extensions = array('xls','xlsx'); // valid extensions

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
	
	include("../vista/proyectocosto/index.php");	

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_impproyCosto'){
    // proceso update a la base de datos usuarios
	
	$nunafectadas=0;
	$codunico=strtotime(date('Y-m-d h:m:s'));			
	if(isset($_FILES['fileexcel'])){
		$img = $_FILES['fileexcel']['name'];
		$tmp = $_FILES['fileexcel']['tmp_name'];

		// get uploaded file's extension
		$ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
		// can upload same image using rand function
		// check's valid format
		if(in_array($ext, $valid_extensions)){					
			$pathProyCosto = $pathProyCosto.strtolower($img);	
			if(move_uploaded_file($tmp,$pathProyCosto)){
				
				// leer archivo
				$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($pathProyCosto);
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
					for ($j = 1; $j <= $highestColumnIndex; $j++) {
					
						$value = $sheet->getCellByColumnAndRow($j, $i)->getValue();
						if(strstr($value,'='))
							$value = $sheet->getCellByColumnAndRow($j, $i)->getOldCalculatedValue();
					
						if($j==2 or $j==3 or $j==8 or $j==9){
								
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
					
						$array[$j]=$value;
					}
					if($i>1){ // no cabeceras
						$data_part="(
							'$array[1]','$array[2]','$array[3]','$array[4]','$array[5]','$array[6]','$array[7]','$array[8]','$array[9]','$sess_codpais'
						)";
						if($data_sql=='') $data_sql=$data_part;
						else $data_sql.=",".$data_part;
					}
				}
				 
				if($data_sql!=''){
					//$nunafectadas=$proyectocosto->insert_proyectocosto($data_sql,$codunico,$sess_codpais);
					$rows=$proyectocosto->insert_proyectocosto($data_sql,$codunico,$sess_codpais);
				}else
					$norows=1;
			}
		}else{
			// echo 'invalid';
			$isinvalido=1;
		}
	}else
		$isnofile=1;
	// echo $nunafectadas;	  
	
	include("../vista/proyectocosto/frm_detalle.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_newproycosto'){
	$proyectocosto->regula_proyectocosto($sess_codpais);
	echo "Exito";

}else if(!empty($_POST['accion']) and $_POST['accion']=='reporte'){
	//***********************************************************
	// funcion index actividades de auditor por fecha
	//***********************************************************

	include("../vista/proyectocosto/reporte.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='reporte_data'){	
//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$fechai="";
	$fechaf="";
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	
	$fechavi="";
	$fechavf="";
	if(!empty($_POST['fechavi']))
		$fechavi = formatdatedos($_POST['fechavi']);
	if(!empty($_POST['fechavf']))
		$fechavf = formatdatedos($_POST['fechavf']);
	
	$proyecto = $_POST['proyecto'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" proyecto ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery =" and prg_proyectocosto.id_pais='$sess_codpais' " ;
	
	## Total number of records without filtering
	$data_maxOF=$proyectocosto->selec_total_reporte_deuda($searchQuery);
	$totalRecords = $data_maxOF['total'];

		
	if($fechai!='') 
		$searchQuery.=" and to_days(fecha_f)>= to_days('$fechai')";
    if($fechaf!='') 
		$searchQuery.=" and to_days(fecha_f)<= to_days('$fechaf')";
	if($fechavi!='') 
		$searchQuery.=" and to_days(vencimiento_f)>= to_days('$fechavi')";
    if($fechavf!='') 
		$searchQuery.=" and to_days(vencimiento_f)<= to_days('$fechavf')";
	if($proyecto!='') 
		$searchQuery.= " and (proyecto like '%".$proyecto."%'  or prg_proyecto.proyect like '%".$proyecto."%')";

	## Total number of record with filtering
	$data_maxOF2=$proyectocosto->selec_total_reporte_deuda($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$proyectocosto->select_reporte_deuda($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	
	
	//print_r($data_OF);
	$data = array();
	if(!empty($data_OF)){
	 foreach($data_OF as $row) {
		
	  $data[] = array( 
		 "concepto"=>str_replace('"','',json_encode($row['concepto'],JSON_UNESCAPED_UNICODE)),
		 "proyecto"=>str_replace('"','',json_encode($row['proyecto'],JSON_UNESCAPED_UNICODE)),
		 "nombre_proyecto"=>str_replace('"','',json_encode($row['nombre_proyecto'],JSON_UNESCAPED_UNICODE)),
		 "nrofactura"=>str_replace('"','',json_encode($row['nrofactura'],JSON_UNESCAPED_UNICODE)),
		 "importe"=>$row['importe'],
		 "fecha_f"=>$row['fecha'],
		 "vencimiento_f"=>$row['vencimiento'],
		 "recordatorio1_f"=>$row['recordatorio1'],
		 "recordatorio2_f"=>$row['recordatorio2'],
		 "f_auditor"=>$row['f_auditor'],
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='expDeuda'){	
//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$fechai="";
	$fechaf="";
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	
	$fechavi="";
	$fechavf="";
	if(!empty($_POST['fechavi']))
		$fechavi = formatdatedos($_POST['fechavi']);
	if(!empty($_POST['fechavf']))
		$fechavf = formatdatedos($_POST['fechavf']);
	
	$proyecto = $_POST['proyecto'];
	
	$searchQuery =" and prg_proyectocosto.id_pais='$sess_codpais' " ;
	
	if($fechai!='') 
		$searchQuery.=" and to_days(fecha_f)>= to_days('$fechai')";
    if($fechaf!='') 
		$searchQuery.=" and to_days(fecha_f)<= to_days('$fechaf')";
	if($fechavi!='') 
		$searchQuery.=" and to_days(vencimiento_f)>= to_days('$fechavi')";
    if($fechavf!='') 
		$searchQuery.=" and to_days(vencimiento_f)<= to_days('$fechavf')";
	if($proyecto!='') 
		$searchQuery.= " and (proyecto like '%".$proyecto."%'  or prg_proyecto.proyect like '%".$proyecto."%')";

	$columnName=" proyecto ";
	$columnSortOrder=" asc ";
	$row=0;
	$rowperpage=10000;
	
	$data_OF=$proyectocosto->select_reporte_deuda($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	include("../vista/proyectocosto/xlsdeudas.php");	
}


?>
