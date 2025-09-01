<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/kpi_importar_modelo.php");

$kpiimportar=new kpi_importar_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

$pathKpiUpload="archivos/kpi/";


// liberias composer de excel
//******************************************
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
//******************************************

require_once "../lib/swift_required.php";
$transport = Swift_SmtpTransport::newInstance($server_mail, $puerto_mail);
//          ->setUsername($user_mail)
//          ->setPassword($clave_mail)
$mailer = Swift_Mailer::newInstance($transport); 


//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	$tipokpi='aud';
    include("../vista/kpiimportar/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_cer'){
	$tipokpi='cer';
	include("../vista/kpiimportar/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_kpiimportar'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	$tipokpi = $_POST['tipokpi'];
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" programa";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and kpi_programa.id_pais = '$sess_codpais' and tipokpi='$tipokpi' ";

		
	## Total number of records without filtering
	$data_maxOF=$kpiimportar->selec_total_kpiimportar($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( programa like '%$descripcion%'  )";
	
	## Total number of record with filtering
	$data_maxOF2=$kpiimportar->selec_total_kpiimportar($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$kpiimportar->select_kpiimportar($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codprograma'];
			
			$descargar="<button type='button' id='estproy_". $id ."'  class='btn  btn_impkpidownload'><i class='fas fa-download'></i> </button>";
			$importar="<button type='button' id='estproy_". $id ."'  class='btn  btn_impkpiupload'><i class='fas fa-upload'></i> </button>";
			$ver="<button type='button' id='estproy_". $id ."'  class='btn  btn_impkpiver'><i class='fas fa-file'></i> </button>";
		
		   $data[] = array( 
			   "programa"=>str_replace('"','',json_encode($row['programa'],JSON_UNESCAPED_UNICODE)),
			   "codprograma"=>$id,
			   "descargar"=>$descargar,
			   "importar"=>$importar,
			   "ver"=>$ver,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='xlsFormatoProg'){
	$codprograma=$_POST['codprograma'];
	$tipokpi=$_POST['tipokpi'];
	
	$data_OF=$kpiimportar->select_kpiformatoxls($codprograma,$sess_codpais);
	foreach($data_OF as $row) {
		 $programa=$row['programa'];
		 $flgfail=$row['flgfail'];
		 $indicador=$row['indicador'];
	 } 	
	if($tipokpi=='cer')
		include("../vista/kpiimportar/data_formato_cer.php");
	else
		include("../vista/kpiimportar/data_formato.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='impKpiUpload'){
    $codprograma=$_POST['codprograma'];
	$tipokpi=$_POST['tipokpi'];
    include("../vista/kpiimportar/frm_upload.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detKpiUpload'){
    $codprograma=$_POST['codprograma'];
	$seconds = strtotime(date("Y-m-d H:i:s") . " UTC");
	$tipokpi=$_POST['tipokpi'];
	$numcfijas=20;
	if($tipokpi=='cer')
		$numcfijas=12;

	$data_OF=$kpiimportar->selec_one_kpiprograma($codprograma);
	$flgfail=$data_OF['flgfail'];

	$data_OF=$kpiimportar->select_kpiindicadores($codprograma);
	$i=0;
	
//	print_r($data_OF);
	
	
	foreach($data_OF as $row) {
		 $arrayInd[$i]=$row['codindicador'];
		 $i++;
	 } 
	 
	

	$valid_extensions = array('xls','xlsx'); // valid extensions
	// upload directory $pathListasInt
	if(isset($_FILES['fileexcel'])){
		
		$img = $_FILES['fileexcel']['name'];
		$tmp = $_FILES['fileexcel']['tmp_name'];

		$temp = explode(".", $_FILES["fileexcel"]["name"]);
		
		//echo "Inicia evaluacion<br>";    
						  
		// get uploaded file's extension
		$ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
		// can upload same image using rand function
		// check's valid format
		
		$img = round(microtime(true)) . '.' . end($temp);
		$new_file_name=$img;
		if(in_array($ext, $valid_extensions)){		
			
			$path=$pathKpiUpload.strtolower($img);
			//echo "Es el $formato <br>";    ;


				
			if(move_uploaded_file($tmp,"../".$path)){
				
				// leer archivo
				$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load("../".$path);
				$sheetCount = $spreadsheet->getSheetCount();
				$sheetNames = $spreadsheet->getSheetNames();
				
				$sheet = $spreadsheet->getSheet(0);
				
				$sheetData = $sheet->toArray(null, true, true, true);
				$highestRow = $sheet->getHighestRow(); // e.g. 10
				$highestColumn = $sheet->getHighestColumn(); // e.g 'F'
				$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
				// fin data
				
			
				$data_sql="";
				$datadet_sql="";
				$data_partdet="";
				$numFilas=0;

				// inicio datos de excel de auditor
				//************************************** nroduplica
				//echo $highestColumnIndex."<br>";
				//echo $highestRow."<br>";
				for ($i = 6; $i <= $highestRow; ++$i) {

					
					$valueProj = $sheet->getCellByColumnAndRow(1, $i)->getValue();
					
					if($valueProj!=''){
						$data_part="($seconds,$i,$codprograma,'$tipokpi' ";
						$numFilas++;
						for ($j = 1; $j <= $highestColumnIndex; $j++) {
							$value = $sheet->getCellByColumnAndRow($j, $i)->getValue();
							if(strstr($value,'='))
								$value = $sheet->getCellByColumnAndRow($j, $i)->getOldCalculatedValue();
									
							if($j<= ($numcfijas+1)){
								if($j==9){
									$fecha=$value;
									$fecha=str_replace('-','/',$fecha);
									if(!strpos($fecha,'/') and $fecha!='')
										$fecha=convertDate($fecha);
									elseif($fecha!=''){
										$arr=explode('/',$fecha);
										$fecha=$arr[2]."/".$arr[1]."/". $arr[0];
									}

									$data_part.=",'$fecha'";
								}elseif($j==5 and $tipokpi=='aud'){
									$fechadraft=$value;
									$fechadraft=str_replace('-','/',$fechadraft);
									if(!strpos($fechadraft,'/') and $fechadraft!='')
										$fechadraft=convertDate($fechadraft);
									elseif($fechadraft!=''){
										$arr=explode('/',$fechadraft);
										$fechadraft=$arr[2]."/".$arr[1]."/". $arr[0];
									}

									$data_part.=",'$fechadraft'";
								}elseif($j==$numcfijas-1  and $tipokpi=='aud'){
									$fechaeval=$value;
									$fechaeval=str_replace('-','/',$fechaeval);
									if(!strpos($fechaeval,'/') and $fechaeval!='')
										$fechaeval=convertDate($fechaeval);
									elseif($fechaeval!=''){
										$arr=explode('/',$fechaeval);
										$fechaeval=$arr[2]."/".$arr[1]."/". $arr[0];

									}
									$data_part.=",'$fechaeval'";							
								}else 
									// campos textos hay que quitar caracter especial
									if(!empty($value)){
										$valTexto=str_replace("'","",$value);
										$data_part.=",'" . $valTexto."'";
									}else
										$data_part.=",''";
							}else{
								$indice=$j- ($numcfijas+2);
								$codindicador="";
								if(!empty($arrayInd[$indice]))
									$codindicador=$arrayInd[$indice];
								
								if($datadet_sql=='') $datadet_sql="($seconds,$i,'$codindicador','" . $value. "') ";
								else $datadet_sql.=" , ($seconds,$i,'$codindicador','" . $value . "') ";

							}
						}
					
						$data_part.=")";
						
						if($data_sql=='') $data_sql=$data_part;
						else $data_sql.=",".$data_part;
					}
					
					
				}


				// fin  datos de excel de auditor
				//**************************************
            
				if($data_sql!='' and $datadet_sql!=''){
					
					$kpiimportar->insert_kpiimportar($data_sql,$datadet_sql,$tipokpi);
					
					if($tipokpi=='aud'){
						$data=$kpiimportar->select_kpiauditorvacio();
						$auditorVacio = $data['total'];
						$ColsauditorVacio = $data['item'];
					
						$data=$kpiimportar->select_kpicolumnaentero();
						$noProyEnteros=$data['total'];
						$ColsProyNoEnteros=$data['item'];
					
					
						$data=$kpiimportar->select_kpicolumnainspentero();
						$noInsEnteros=$data['total'];
						$ColsInsNoEnteros=$data['item'];
					}else if($tipokpi=='cer'){
						$data=$kpiimportar->select_kpicertivacio();
						$auditorVacio = $data['total'];
						$ColsauditorVacio = $data['item'];
						
					}
					
					
					$data=$kpiimportar->select_kpivaloresvacio();
					$nroValores=$data['total'];
					$ColsValores=$data['item'];

					if($tipokpi=='aud'){
						$data=$kpiimportar->select_columnasvacio();
						$nrovacios=$data['total'];
						$ColsVacios=$data['item'];
				
						
						$data=$kpiimportar->select_kpiduplicados($codprograma);
						$nroduplica=$data['total'];
						$ColsDuplica=$data['item'];
						
						$data=$kpiimportar->select_kpinoespais();
						$nropais=$data['total'];
						$Colsnopais=$data['item'];
						
						
					}
					
					$data=$kpiimportar->select_kpiinconsistencia();
					$amigrar=$data['total'];
					
					$DatTempoporal=$kpiimportar->select_kpitemporal($codprograma,$seconds,$sess_codpais,$tipokpi);
					// new_file_name
					if($tipokpi=='cer')
						include("../vista/kpiimportar/temp_importar_cer.php");
					else
						include("../vista/kpiimportar/temp_importar.php");
				}
			}else
				echo "No se pudo copiar en el directorio";
     	}else
			echo "Extension no valida";
	}else{
		echo 'Archivo invalido';
	}

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_confKpiUpload'){
    $codprograma=$_POST['codprograma'];
	$nombrefile=$_POST['nombrefile'];
	$seconds=$_POST['seconds'];
	$tipokpi=$_POST['tipokpi'];
	
	$data=$kpiimportar->procesa_uploadKpiDatos($codprograma,$nombrefile,$seconds,$usuario_name,$ip,$sess_codpais,$tipokpi);
	echo "Se registro los datos correctamente.";
    
}else if(!empty($_POST['accion']) and $_POST['accion']=='viewKpiImportar'){
    $codprograma=$_POST['codprograma'];
	$tipokpi=$_POST['tipokpi'];
    include("../vista/kpiimportar/index_resultados.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_kpiresultados'){
    $codprograma=$_POST['codprograma'];
	$tipokpi=$_POST['tipokpi'];
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" fecha_ingreso";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and id_pais = '$sess_codpais'  and codprograma=$codprograma  ";

		
	## Total number of records without filtering
	$data_maxOF=$kpiimportar->selec_total_kpianalisis($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and ( fecha_ingreso like '%$descripcion%'  )";
	
	## Total number of record with filtering
	$data_maxOF2=$kpiimportar->selec_total_kpianalisis($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$columnName=" fecha_ingreso";
	$columnSortOrder=" desc";
	$data_OF=$kpiimportar->select_kpianalisis($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['id'];
			$nombrefile=strtolower($row['nombrefile']);
			$ver="";
			
			$eliminar="<button type='button' id='estproy_".$id."_".$codprograma."'  class='btn  btn_impkpidelDatos'><i class='fas fa-trash'></i> </button>";
			$analisis="<button type='button' id='esproy_".$id."_".$codprograma."'  class='btn  btn_impkpiAnalisis'><i class='fas fa-chart-area'></i> </button>";
			if($nombrefile!='')
				$ver="<button type='button' id='".$nombrefile."'  class='btn  btn_impkpiverFile'><i class='fas fa-file'></i> </button>";
		
		   $data[] = array( 
			   "fechaf"=>$row['fechaf'],
			   "dsccerrado"=>$row['dsccerrado'],
			   "usuario_ingreso"=>$row['usuario_ingreso'],
			   "analisis"=>$analisis,
			   "eliminar"=>$eliminar,
			   "ver"=>$ver,
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='delKpiImportar'){
    $codprograma=$_POST['codprograma'];
	$idt=$_POST['idt'];
	
	$data=$kpiimportar->delete_detetDatosKpi($idt,$codprograma);
	echo "Se elimino los datos correctamente.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='AnalisisKpiImportar'){
    $codprograma=$_POST['codprograma'];
	$idt=$_POST['idt'];
	$tipokpi=$_POST['tipokpi'];
    
	$data1=$kpiimportar->select_kpianalisisCab($idt,$codprograma);
	$flgcerrado=$data1['flgcerrado'];
	
	$data2=$kpiimportar->select_kpianalisisIndicador($codprograma,$sess_codpais);
	$programa=$data2['programa'];
	$indicador=$data2['indicador'];
	$flgfail=$data2['flgfail'];
	
	$data3=$kpiimportar->select_kpianalisisDet($idt);
	foreach($data3 as $row) {
		$arrayData[$row['codindicador']][$row['item']]=$row['valor'];
	}
	
	$data4=$kpiimportar->select_kpianalisisAccion($tipokpi,$sess_codpais);
	if(!empty($data4)){
		foreach($data4 as $row) {
			 $arrayPraEval[$row['codaccion']]=$row['maximo']."*".$row['minimo']."*".$row['valor'];
		}
	}
	
	$data5=$kpiimportar->select_kpianalisisDatos($codprograma,$idt);
	if(!empty($data5)){
		foreach($data5 as $row) {
			$arrayScore[$row['codcategoria']][$row['item']]=$row['score'];
			$arrayScoreFin[$row['codcategoria']][$row['item']]=$row['score2'];
		}
	}
	// arrayPraEval
	$data6=$kpiimportar->select_kpianalisisDatosValor($idt);
	if(!empty($data6)){
		foreach($data6 as $row) {
			$arrayScore[0][$row['item']]=$row['valor'];
		}
	}
	//datoAnalisis
	$datoAnalisis=$kpiimportar->select_kpianalisisImportar($idt);
	
	if($tipokpi=='aud')
		include("../vista/kpiimportar/index_analisis.php");    
	else if($tipokpi=='cer')
		include("../vista/kpiimportar/index_analisis_cer.php");    


}else if(!empty($_POST['accion']) and $_POST['accion']=='procesaKpiImportar'){


    $codprograma=$_POST['codprograma'];
	$tipokpi=$_POST['tipokpi'];
	$idt=$_POST['idt'];
	
	if($tipokpi=='aud')
		$data=$kpiimportar->procesa_OkImportaKpi($idt,$codprograma,$sess_codpais);
	else if($tipokpi=='cer')
		$data=$kpiimportar->procesa_OkImportaKpi_cer($idt,$codprograma,$sess_codpais);
	
	/********************************************************
	POCESO DE ENVIO DE CORREOS PERSONALZIADOS
	******************************************************/
	
	// debemos obtener la lista de auditores en este analisis
	
	
	$data2=$kpiimportar->select_kpianalisisIndicador($codprograma,$sess_codpais);
	$programa=$data2['programa'];
	$indicador=$data2['indicador'];
	$flgfail=$data2['flgfail'];
	
	$data4=$kpiimportar->select_kpianalisisAccion($tipokpi,$sess_codpais);
	if(!empty($data4)){
		foreach($data4 as $row) {
			 $arrayPraEval[$row['codaccion']]=$row['maximo']."*".$row['minimo']."*".$row['valor'];
		}
	}
	
	// luego por filtro item se identifica el auditor
	$data3=$kpiimportar->select_kpianalisisDet($idt);
		foreach($data3 as $row) {
			$arrayData[$row['codindicador']][$row['item']]=$row['valor'];
	}
	
	$data5=$kpiimportar->select_kpianalisisDatos($codprograma,$idt);
	if(!empty($data5)){
		foreach($data5 as $row) {
			$arrayScore[$row['codcategoria']][$row['item']]=$row['score'];
			$arrayScoreFin[$row['codcategoria']][$row['item']]=$row['score2'];
		}
	}
	
	$data6=$kpiimportar->select_kpianalisisDatosValor($idt);
	if(!empty($data6)){
		foreach($data6 as $row) {
			$arrayScore[0][$row['item']]=$row['valor'];
		}
	}
	
	// preparando datos para envio de email
	$dataEmail=$kpiimportar->select_emailPrograma($codprograma);
	
	if($tipokpi=='aud' or $tipokpi=='cer'){
		if($tipokpi=='aud')
			$datAuditor=$kpiimportar->select_auditorByEmail($idt,$codprograma,$sess_codpais);	
		if($tipokpi=='cer')
			$datAuditor=$kpiimportar->select_certificadorByEmail($idt,$codprograma,$sess_codpais);	
			
		if(!empty($datAuditor)){
			
			foreach($datAuditor as $rowAu){
				$auditorname=$rowAu['auditorname'];
				$auditoremail=$rowAu['email'];
				$auditorcode=$rowAu['auditor'];

				$datoAnalisis=$kpiimportar->select_kpianalisisImportar($idt,$auditorcode);
				$body="";
				ob_start();
				include("../vista/kpiimportar/plantilla_correo.php");
				$body = ob_get_clean();

				if(!empty($auditoremail)){
					
					if(!empty($dataEmail))	
						$auditoremail.="," . $dataEmail['emailsuper'];
					
					
					//$auditoremail="alephsystem@gmail.com,smostiga@controlunion.com,pkuriyama@controlunion.com";
					$asunto=$lang_kpi[69]. $auditorname;			
					$message = Swift_Message::newInstance($asunto)
						->setFrom(array($user_mail =>  $name_mail))
						->setTo(explode(",",$auditoremail))
						->setBody($body, 'text/html', 'iso-8859-2')
					;
					$numSent = $mailer->send($message);
					printf("Enviado: %d mensajes a $auditorname al correo $auditoremail<br>", $numSent);
					// print "Envio a $auditorname al correo $auditoremail";
				}

			}
		}
	}
	
	
	echo "Se actualizo los datos correctamente.";
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delAnalisisKpiImportar'){
	$idt=$_POST['idt'];
	$item=$_POST['item'];
	$tipoKpi=$_POST['tipoKpi'];
	
	$data=$kpiimportar->delete_analisisKpiImportar($idt,$item,$tipoKpi);
	echo "Se eliminó el dato correctamente.";

}


?>
