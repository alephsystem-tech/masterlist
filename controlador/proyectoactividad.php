<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_proyectoactividad_modelo.php");
include("../modelo/mae_pais_modelo.php");
$proyectoactividad=new prg_proyectoactividad_model();
$pais=new mae_pais_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$pathProyActividad = 'archivos/proyectoactividad/'; // upload directory
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
	
	include("../vista/proyectoactividad/index.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_result'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];

	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" proyect ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and admin_pais='$sess_codpais' ";

	if(!empty($descripcion))
		$searchQuery.=" and (proyect like '%".$descripcion."%' or project_id='$descripcion') ";
	
	
	## Total number of records without filtering
	$data_maxOF=$proyectoactividad->selec_total_proyectoactividad($searchQuery);
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$proyectoactividad->selec_total_proyectoactividad($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$proyectoactividad->select_proyectoactividad($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		
		foreach($data_OF as $row) {
	
			$chk=" ";
			$id=$row['id_proy'];
			if($row['flgactivo']=='1')
				$chk=" checked";
			$flgactivo="<div class='custom-control custom-switch  custom-switch-off-danger custom-switch-on-success'>
						  <input type=checkbox class='custom-control-input'onchange='js_changeactive($id)' name='flgstatus_$id' id='flgstatus_$id' $chk >
						  <label class='custom-control-label' for='flgstatus_$id'></label>
						</div>";
			
			
			$edita="<button type='button' id='labRes_". $id ."'  class='btn  btn_ediproyact'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='labRes_". $id ."'  class='btn  btn_eliproyact'><i class='fas fa-trash'></i> </button>";
			
		   $data[] = array( 
			   "id_proy"=>$id,
			   "project_id"=>$row['project_id'],
			   "pais"=>$row['pais'],
			   "oficina"=>$row['oficina'],
			   "proyect"=>str_replace('"','',json_encode($row['proyect'],JSON_UNESCAPED_UNICODE)),
			   "edita"=>$edita,
			    "flgactivo"=>$flgactivo,
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

		
}else if(!empty($_POST['accion']) and $_POST['accion']=='impProyActividad'){
	//***********************************************************
	// funcion index actividades de auditor por fecha
	//***********************************************************
	
	include("../vista/proyectoactividad/frm_importar.php");	

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_impProyActividad'){
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
			$pathProyActividad = "../".$pathProyActividad.strtolower($img);	
			if(move_uploaded_file($tmp,$pathProyActividad)){
				
				// leer archivo
				$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($pathProyActividad);
				$sheetCount = $spreadsheet->getSheetCount();
				$sheetNames = $spreadsheet->getSheetNames();
				
				$sheet = $spreadsheet->getSheet(0);
				
				$sheetData = $sheet->toArray(null, true, true, true);
				$highestRow = $sheet->getHighestRow(); // e.g. 10
				$highestColumn = $sheet->getHighestColumn(); // e.g 'F'
				$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
				// fin data
				
				$data_sql="";
				for ($i = 1; $i <= $highestRow ; $i++) {
					unset($array);
					for ($j = 1; $j <= 17; $j++) {
						$value = $sheet->getCellByColumnAndRow($j, $i)->getValue();
						if(strstr($value,'='))
							$value = $sheet->getCellByColumnAndRow($j, $i)->getOldCalculatedValue();
								
						if(!empty($value) or $value=='0')
							$array[$j]=$value;
						else
							$array[$j]="";
					}
					if($i>1 and $array[1]!=''){ // no cabeceras
						
						if($array[3]!=''){
							$array3=str_replace("'","",$array[3]);
							$array[4]=str_replace("'","",$array[4]);
							$array[6]=str_replace("'","",$array[6]);
							$array[8]=str_replace("'","",$array[8]);
							$array[16]=str_replace("'","",$array[16]);
							$array[17]=str_replace("'","",$array[17]);
							$data_part="(
							'$array[1]','$array[3]','$array[4]','$array[6]','$array[8]','$array[10]','$array[11]','$array[16]','$array[17]'
							)";
							if($data_sql=='') $data_sql=$data_part;
							else $data_sql.=",".$data_part;
						}
					}
				}
				 
				if($data_sql!=''){
					$nunafectadas=$proyectoactividad->insert_ImportProyectoactividad($data_sql,$codunico,$sess_codpais,$usuario_name);
				}
			}
		}else{
			echo 'invalid';
		}
	}	
	echo $nunafectadas;	  

}else if(!empty($_POST['accion']) and $_POST['accion']=='editProyectoActividad'){
	$id_proy="";
	if(!empty($_POST['id_proy'])){
		$id_proy=$_POST['id_proy'];
		$data_res=$proyectoactividad->selec_one_proyectoactividad($id_proy);
	}
	$data_pais =$pais->selec_paises();
    include("../vista/proyectoactividad/frm_detalle.php");

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_editProyectoActividad'){
    // proceso update a la base de datos usuarios
	
	$project_id=$_POST['project_id'];
	$proyect=$_POST['proyect'];
	$oficina=$_POST['oficina'];
	$id_pais=$_POST['id_pais'];

	
	
	if(empty($_POST['id_proy']))
		$id_proy=$proyectoactividad->insert_proyectoactividad($project_id,$proyect,$oficina, $id_pais,$sess_codpais,$usuario_name,$ip);
	else{
		$id_proy=$_POST['id_proy']; // id
		$proyectoactividad->update_proyectoactividad($id_proy,$project_id,$proyect,$oficina, $id_pais,$sess_codpais,$usuario_name,$ip);
	}	
	$proyectoactividad->update_paisProyectoactividad($id_proy);
	
	 echo $id_proy;
	 
}else if(!empty($_POST['accion']) and $_POST['accion']=='delProyectoActividad'){
	$id_proy=$_POST['id_proy'];
	$data_res=$proyectoactividad->delete_proyectoactividad($id_proy);
	echo "Se elimino el registro.";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='activoProyecto'){
    // delete a la base de datos usuarios
	$id_proy=$_POST['id_proy']; 
	$flgactivo=$_POST['flgactivo']; 
    $proyectoactividad->activa_proyecto($id_proy,$flgactivo);
    echo "Se actualizo el registro.";

	
}


?>
