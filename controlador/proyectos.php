<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/prg_proyecto_modelo.php");
include("../modelo/prg_programa_modelo.php");

include("../modelo/prg_proyectoactividad_modelo.php");
include("../modelo/mae_pais_modelo.php");

$proyecto=new prg_proyecto_model();
$programa=new prg_programa_model();

$proyectoactividad=new prg_proyectoactividad_model();
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

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

$pathProyecto = '../archivos/proyecto/'; // upload directory
$valid_extensions = array('xls','xlsx'); // valid extensions

//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='web_index'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	$programa_res=$programa->selec_programasbypais($sess_codpais);
	$country_res=$proyecto->selec_country_proyecto($sess_codpais);
    include("../vista/proyecto/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_proyecto'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	if(!empty($_POST['id_programa'])){
		foreach($_POST['id_programa'] as $id){
			if(empty($id_programa)) $id_programa="$id";
			else $id_programa.=",$id";
		}
	}
	
	$country = $_POST['country'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" id_proyecto";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and p.id_pais='$sess_codpais'";
	
	

	## Total number of records without filtering
	$data_maxOF=$proyecto->selec_total_proyecto($searchQuery,$sess_codpais);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( p.project_id like '%$descripcion%' or  p.proyect like '%$descripcion%') ";
	
	if(!empty($country))
		$searchQuery.=" and  country='$country' ";
	
	if(!empty($id_programa))
		$searchQuery.=" and  g.id_programa in ($id_programa) ";
		
	## Total number of record with filtering
	$data_maxOF2=$proyecto->selec_total_proyecto($searchQuery,$sess_codpais);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$proyecto->select_proyecto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
			$chk=" ";
			$id=$row['id_proyecto'];
			if($row['flgactivo']=='1')
				$chk=" checked";
			$flgactivo="<div class='custom-control custom-switch  custom-switch-off-danger custom-switch-on-success'>
						  <input type=checkbox class='custom-control-input'onchange='js_changeactive($id)' name='flgstatus_$id' id='flgstatus_$id' $chk >
						  <label class='custom-control-label' for='flgstatus_$id'></label>
						</div>";
						
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediProyecto'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliProyecto'><i class='fas fa-trash'></i> </button>";

		   $data[] = array( 
			   "project_id"=>str_replace('"','',json_encode($row['project_id'],JSON_UNESCAPED_UNICODE)),
			   "proyect"=>$row['proyect'],
			   "city"=>str_replace('"','',json_encode($row['city'],JSON_UNESCAPED_UNICODE)),
			   "state"=>str_replace('"','',json_encode($row['state'],JSON_UNESCAPED_UNICODE)),
			   "country"=>str_replace('"','',json_encode($row['country'],JSON_UNESCAPED_UNICODE)),
			   "dsc_programa"=>str_replace('"','',json_encode($row['dsc_programa'],JSON_UNESCAPED_UNICODE)),
			   "dsc_producto"=>str_replace('"','',json_encode($row['dsc_producto'],JSON_UNESCAPED_UNICODE)),
			    "telephone"=>str_replace('"','',json_encode($row['telephone'],JSON_UNESCAPED_UNICODE)),
				"mobile"=>str_replace('"','',json_encode($row['mobile'],JSON_UNESCAPED_UNICODE)),
				"fax"=>str_replace('"','',json_encode($row['fax'],JSON_UNESCAPED_UNICODE)),
			   "id_proyecto"=>$id,
			   "ruc"=>$row['ruc'],
			   "email"=>$row['email'],
			   "edita"=>$edita,
			   "elimina"=>$elimina,
			   "flgactivo"=>$flgactivo,
			   
			   "direccion"=>str_replace('"','',json_encode($row['direccion'],JSON_UNESCAPED_UNICODE)),
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='expProyecto'){
	$descripcion=$_POST['descripcion'];
	
	if(!empty($_POST['id_programa']))
		$id_programa = $_POST['id_programa'];
	
	if(!empty($_POST['country']))
		$country = $_POST['country'];

	$searchQuery = " and p.id_pais='$sess_codpais'";
	
	if(!empty($descripcion))
		$searchQuery.=" and ( p.project_id like '%$descripcion%' or  proyect like '%$descripcion%') ";
	
	if(!empty($country))
		$searchQuery.=" and  country='$country' ";
	
	if(!empty($_POST['id_programa'])){
		foreach($_POST['id_programa'] as $id){
			if(empty($id_programa)) $id_programa="$id";
			else $id_programa.=",$id";
		}
	}
	
	$row	=0;
	$columnName=" id_proyecto";
	$rowperpage=1000000;
	$columnSortOrder=" desc";
	## Fetch records
	$data_Per=$proyecto->select_proyecto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais);
	
	
	include("../vista/proyecto/data_exporta.php");
		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editProyecto'){
	$id_proyecto="";
	if(!empty($_POST['id_proyecto'])){
		$data_res=$proyecto->selec_one_proyecto($_POST['id_proyecto']);
		$arr_gproducto=explode(",",$data_res['producto']);
		$arr_gprograma=explode(",",$data_res['programa']);
		$arr_gcategoria=explode(",",$data_res['categoria']);
	}	
	
	$data_producto=$proyecto->selec_producto($sess_codpais);
	$data_programa=$programa->selec_programasbypais($sess_codpais,$flgactivo=1);
	
	$data_categoria=$proyecto->selec_categoria($sess_codpais);

    include("../vista/proyecto/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detProyecto'){
    // proceso update a la base de datos usuarios
	$project_id=$_POST['project_id'];
	$proyect=htmlspecialchars($_POST['proyect'], ENT_QUOTES);
	$ruc=$_POST['ruc'];
	$city=$_POST['city'];
	$state=$_POST['state'];
	$country=$_POST['country'];
	$telephone=$_POST['telephone'];
	$mobile=$_POST['mobile'];
	$fax=$_POST['fax'];
	$email=$_POST['email'];
	$modules=$_POST['modules'];
	$is_viatico="0";
	$direccion=$_POST['direccion'];
	if(!empty($_POST['is_viatico']))
		$is_viatico="1";
		
	$paisID_res=$pais->selec_one_paisby_pais($sess_codpais);
	$t_id_pais="";
	if(!empty($paisID_res)){
		$t_id_pais=$paisID_res['id_pais'];
	}
	
	if(empty($_POST['id_proyecto'])){
		$id_proyecto=$proyecto->insert_proyecto($project_id,$proyect,$direccion,$ruc,$city,$state,$country,$telephone,$mobile,$fax,$email,$modules,$is_viatico,$sess_codpais,$usuario_name,$ip);
		$proyectoactividad->insert_proyectoactividad($project_id,$proyect,'', $t_id_pais,$sess_codpais,$usuario_name,$ip);
	}else{
		$id_proyecto=$_POST['id_proyecto']; // id
		$proyecto->update_proyecto($id_proyecto,$project_id,$proyect,$direccion,$ruc,$city,$state,$country,$telephone,$mobile,$fax,$email,$modules,$is_viatico,$sess_codpais,$usuario_name,$ip);
		
		// 7NOV24, VALIDAR QUE  EXISTA PROYECTO
		$res_existe=$proyectoactividad->selec_one_proyecto($project_id,$sess_codpais);
		
		if(empty($res_existe))
			$proyectoactividad->insert_proyectoactividad($project_id,$proyect,'', $t_id_pais,$sess_codpais,$usuario_name,$ip);
		else
			$proyectoactividad->update_proyectoactividad_short($project_id,$proyect,$sess_codpais,$usuario_name,$ip);
	}	
	
	$proyecto->delete_proyectoxproducto($project_id,$sess_codpais);
	
	if(!empty($_POST['producto'])){
		foreach($_POST['producto'] as $id_producto){
			$proyecto->insert_proyectoxproducto($project_id,$id_producto,$sess_codpais);
		}
	}
	
	if(!empty($_POST['categoria'])){
		foreach($_POST['categoria'] as $codcategoria){
			$proyecto->insert_proyectoxcategoria($project_id,$codcategoria,$sess_codpais);
		}
	}
	
	$proyecto->delete_proyectoxprograma($project_id,$sess_codpais);

	if(!empty($_POST['programa'])){
		foreach($_POST['programa'] as $id_programa){
			$proyecto->insert_proyectoxprograma($project_id,$id_programa,$sess_codpais);
		}
	}
	
	$proyecto->update_proyecto_referencia($id_proyecto,$sess_codpais,$project_id);
	
	echo "Se actualizo el registro";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delProyecto'){
    // delete a la base de datos usuarios
	$id_proyecto=$_POST['id_proyecto']; 
    $proyecto->delete_proyecto($id_proyecto);
    echo "Se elimino el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='impProyecto'){
    include("../vista/proyecto/frm_importar.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_impProyecto'){
    
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
			$pathProyecto = $pathProyecto.strtolower($img);	
			if(move_uploaded_file($tmp,$pathProyecto)){
				
				// leer archivo
				$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($pathProyecto);
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
							for ($j = 1; $j <=46 ; $j++) { // $data->sheets[0]['numCols']
								
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
					$proyecto->insert_proyectoFormFile($data_sql,$codunico,$sess_codpais);
					$res=$proyecto->select_proyectoFormFile($codunico,$sess_codpais);
					$nunafectadas=$res['total'];
					
					$res=$proyecto->select_proyectoFormFileMigrado($codunico,$sess_codpais);
					$nunmigradas="0";
					if(!empty($res['total']))
						$nunmigradas=$res['total'];

					$proyecto->procedure_proyectoFormFile($codunico,$sess_codpais);
					echo "Se migraron $nunmigradas registros de $nunafectadas importados del archivo.";
				}
			}
		}else{
			echo 'Archivo invalid';
		}
	}	
}else if(!empty($_POST['accion']) and $_POST['accion']=='activoProyecto'){
    // delete a la base de datos usuarios
	$id_proyecto=$_POST['id_proyecto']; 
	$flgactivo=$_POST['flgactivo']; 
    $proyecto->activa_proyecto($id_proyecto,$flgactivo);
    echo "Se actualizo el registro.";
	
}


?>
