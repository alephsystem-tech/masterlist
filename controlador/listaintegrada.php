<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/lst_listaintegrada_modelo.php");
include("../modelo/prg_proyecto_modelo.php");
include("../modelo/prg_pais_modelo.php");
include("../modelo/prg_auditor_modelo.php");

$mlauditor=new prg_auditor_model();
$listaintegrada=new lst_listaintegrada_model();
$proyecto=new prg_proyecto_model();
$prgpais=new prg_pais_model();

// liberias composer de excel
//******************************************
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
//******************************************
	
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

$pathListasInt="uploads/listasintegradas/";
$pathListasIntanexo="uploads/listasintegradas/anexo/";

if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	$data_pais=$prgpais->selec_paises();
    include("../vista/listaintegrada/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_listaintegrada'){
	
	//***********************************************************
	// funcion buscador tabla lista integrada
	//***********************************************************
	
	## Read value
	$pais = $_POST['pais'];
	$proyecto = $_POST['proyecto'];
	
	$fechai = $_POST['fechai'];
	$fechaf = $_POST['fechaf'];
	$fechasubei = $_POST['fechasubei'];
	$fechasubef = $_POST['fechasubef'];
	//
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" dat.fecha";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and c.id_pais='$sess_codpais'";
	$searchQuery = " and 1=1 ";

	if(!empty($proyecto))
		$searchQuery.=" and (c.proyecto like '%$proyecto%' or c.codproyecto like '%$proyecto%' ) ";
	
	if(!empty($pais))
		$searchQuery.=" and c.ref_pais = '$pais'  ";
	
	if(!empty($fechai)){
		$fechai=formatdatedos($fechai);
		$searchQuery.=" and to_days(c.fechainicio)= to_days('$fechai')  ";
	}
	
	if(!empty($fechaf)){
		$fechaf=formatdatedos($fechaf);
		$searchQuery.=" and to_days(c.fechatermino)= to_days('$fechaf')  ";
	}
	
	if(!empty($fechasubei)){
		$fechasubei=formatdatedos($fechasubei);
		$searchQuery.=" and to_days(c.fecha_ingreso)>= to_days('$fechasubei')  ";
	}
	
	if(!empty($fechasubef)){
		$fechasubef=formatdatedos($fechasubef);
		$searchQuery.=" and to_days(c.fecha_ingreso	)<= to_days('$fechasubef')  ";
	}
	
	## Total number of records without filtering
	$data_maxOF=$listaintegrada->selec_total_listaintegrada();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$listaintegrada->selec_total_listaintegrada($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$listaintegrada->select_listaintegrada($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	

	

	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {
			$searchQueryCultivo = "codlista=".$row['codlista'];
			$data_OF_cultivototal=$listaintegrada->select_listaintegrada_cultivototalmayor5($searchQueryCultivo);
			$data_OF_cultivo=$listaintegrada->select_listaintegrada_cultivomayor5($searchQueryCultivo);
			$total_cultivototal = isset($data_OF_cultivototal) ? count($data_OF_cultivototal) : 0;	
			$total_cultivo = isset($data_OF_cultivo) ? count($data_OF_cultivo) : 0;	

			$id=$row['codlista'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediListaintegrada'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliListaintegrada'><i class='fas fa-trash'></i> </button>";
			
			$estado_activo = $row['flgestado']==1 ? 'checked' : '';
			
			$estado='<div class="form-check form-switch">';
			$estado.='<input class="form-check-input" type="checkbox" value="1" id="checklista_'.$id.'" onclick="btn_cambiarEstadoListaintegrada('.$id.')" '.$estado_activo.'>';
			$estado.='</div>';
			
			$datosfecha="";
			foreach(array_unique(explode(',',$row['datosfecha'])) as $ruta){ 
				$Arrdato=explode('&&',$ruta);
				$fechaFileO=	$Arrdato[0];
				$Arrname=explode('/',$Arrdato[1]);
				$nameFileO=	$Arrname[2];
				$datosfecha.="<button type='button' id='".$Arrdato[1]."' style='margin:2px;font-size:10px'
					class='btn  btn-primary btn_verFileLista'>$nameFileO ($fechaFileO) </button>";
			}
		
		   $data[] = array( 
				"proyecto"=>str_replace('"','',json_encode($row['proyecto'],JSON_UNESCAPED_UNICODE)),
				"codproyecto"=>str_replace('"','',json_encode($row['codproyecto'],JSON_UNESCAPED_UNICODE)),
				"datosfecha"=>$datosfecha,
				"fechainicio"=>$row['fecha_f'],
				"fechatermino"=>$row['fechafin_f'],
				"total"=>$row['total'],
				"usuario_ingreso"=>$row['usuario_ingreso'],
				"cultivo"=>$row['cultivo'],
				"tipo_lista"=>$row['tipo_lista'],
				"hastotales"=>$row['hastotales'],
				"hascultivo"=>$row['hascultivo'],
				"rendimiento"=>number_format($row['rendimiento'],2),
				"tipo"=>$row['tipo'],
				"caso"=>$row['casodsc'],
				"cultivo_total_mayor_5"=>$total_cultivototal,
				"cultivo_mayor_5"=>$total_cultivo,
				"referencia"=>$row['referencia'],
				"tolerancia"=>$row['tolerancia'],
				"codlista"=>$id,
				"edita"=>$edita,
				"elimina"=>$elimina,
				"estado"=>$estado,
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
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='xls_explistaInt'){
	$bloqueXls="border=1";
	$pais = $_POST['pais'];
	$proyecto = $_POST['proyecto'];
	
	$fechai = $_POST['fechai'];
	$fechaf = $_POST['fechaf'];
	$fechasubei = $_POST['fechasubei'];
	$fechasubef = $_POST['fechasubef'];
	
	$searchQuery = " and c.id_pais='$sess_codpais'";
	$searchQuery = " and 1=1 ";

	if(!empty($proyecto))
		$searchQuery.=" and (c.proyecto like '%$proyecto%' or c.codproyecto like '%$proyecto%' ) ";
	
	if(!empty($pais))
		$searchQuery.=" and c.ref_pais = '$pais'  ";
	
	if(!empty($fechai)){
		$fechai=formatdatedos($fechai);
		$searchQuery.=" and to_days(c.fechainicio)= to_days('$fechai')  ";
	}
	
	if(!empty($fechaf)){
		$fechaf=formatdatedos($fechaf);
		$searchQuery.=" and to_days(c.fechatermino)= to_days('$fechaf')  ";
	}
	
	if(!empty($fechasubei)){
		$fechasubei=formatdatedos($fechasubei);
		$searchQuery.=" and to_days(c.fecha_ingreso)>= to_days('$fechasubei')  ";
	}
	
	if(!empty($fechasubef)){
		$fechasubef=formatdatedos($fechasubef);
		$searchQuery.=" and to_days(c.fecha_ingreso	)<= to_days('$fechasubef')  ";
	}
	
	$columnName=" dat.fecha";
	$columnSortOrder=" desc ";
	$row = 0;
	$rowperpage = 10000;
	$data_OF=$listaintegrada->select_listaintegrada($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	
	include("../vista/listaintegrada/vistaTem_listaintexls.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='productores'){
	
	$data_pais=$prgpais->selec_paises();
	$data_proyecto=$listaintegrada->select_proyectos($sess_codpais);
	$data_cultivo=$listaintegrada->select_cultivos_sel($sess_codpais);
	
	
    include("../vista/listaintegrada/productor.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_productor'){

	//***********************************************************
	// funcion buscador tabla lista integrada
	//***********************************************************
	
	## Read value
	
	$cedula = $_POST['cedula'];
	$codcultivo = $_POST['codcultivo'];
	$project_id = $_POST['project_id'];
	$activo = $_POST['activo'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" c.codagricultor";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	if(empty($_POST['pais']))
		$pais=$sess_codpais;
	else
		$pais = $_POST['pais'];
	
	## Search  oculto
	$searchQuery = " and c.id_pais='$pais' ";

	if(!empty($cedula))
		$searchQuery.=" and d.cedula = '$cedula' ";
	
	if(!empty($codcultivo))
		$searchQuery.=" and d.codcultivo = $codcultivo ";
	
	if(!empty($project_id))
		$searchQuery.=" and c.codproyecto = '$project_id' ";
	
	if($activo=='SI')
		$searchQuery.=" and c.flgestado = '1' ";

	if($activo=='NO')
		$searchQuery.=" and c.flgestado != '1' ";


	## Total number of records without filtering
	$data_maxOF=$listaintegrada->selec_total_productor();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$listaintegrada->selec_total_productor($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$listaintegrada->select_productor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

		   $data[] = array( 
			   "agricultor"=>str_replace('"','',json_encode($row['agricultor'],JSON_UNESCAPED_UNICODE)),
			   "codproyecto"=>str_replace('"','',json_encode($row['codproyecto'],JSON_UNESCAPED_UNICODE)),
			   "proyecto"=>str_replace('"','',json_encode($row['proyecto'],JSON_UNESCAPED_UNICODE)),
			   "cultivo"=>str_replace('"','',json_encode($row['cultivo'],JSON_UNESCAPED_UNICODE)),
			   "unidad"=>str_replace('"','',json_encode($row['unidad'],JSON_UNESCAPED_UNICODE)),
			   "codagricultor"=>$row['codagricultor'],
			   "cedula"=>$row['cedula'],
			   "codcampo"=>$row['codcampo'],
			   "area_total"=>$row['area_total'],
			   "area_cultivo"=>$row['area_cultivo'],
			   "rendimiento"=>$row['rendimiento'],
			   "fechainiciof"=>$row['fechainiciof'],
			   "fechaterminof"=>$row['fechaterminof'],
			   "pais"=>$row['pais'],
			   "activo"=>$row['activo'],
			   "usuario_ingreso"=>$row['usuario_ingreso'],
			   "masproyecto"=>$row['masproyecto'],
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
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='xls_productores'){

	
	$cedula = $_POST['cedula'];
	$codcultivo = $_POST['codcultivo'];
	$project_id = $_POST['project_id'];
	$activo = $_POST['activo'];

	$columnName=" c.codagricultor";
	$columnSortOrder=" asc ";

	if(empty($_POST['pais']))
		$pais=$sess_codpais;
	else
		$pais = $_POST['pais'];
	
	## Search  oculto
	$searchQuery = " and c.id_pais='$pais' ";

	if(!empty($cedula))
		$searchQuery.=" and d.cedula = '$cedula' ";
	
	if(!empty($codcultivo))
		$searchQuery.=" and d.codcultivo = $codcultivo ";
	
	if(!empty($project_id))
		$searchQuery.=" and c.codproyecto = '$project_id' ";
	
	if($activo=='SI')
		$searchQuery.=" and c.flgestado = '1' ";

	if($activo=='NO')
		$searchQuery.=" and c.flgestado != '1' ";

	$row=0;
	$rowperpage=100000;
	$data_OF=$listaintegrada->select_productor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	include("../vista/listaintegrada/xls_productor.php");	
		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editListaintegrada'){
	$codlista="";
	if(!empty($_POST['codlista'])){
		$codlista=$_POST['codlista'];
		$data_res=$listaintegrada->selec_one_listaintegrada($codlista);
		$data_cultivo=$listaintegrada->select_cultivos($codlista);
	}
    include("../vista/listaintegrada/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delListaintegrada'){
    // delete a la base de datos lista integrada
	$codlista=$_POST['codlista']; 
    $listaintegrada->delete_listaintegrada($codlista);
    echo "Se elimino el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='cambiarEstadoListaintegrada'){
    // delete a la base de datos lista integrada
	$codlista=$_POST['codlista']; 
	$flgestado=$_POST['flgestado'];
    $listaintegrada->update_estadolistaintegrada($codlista,$flgestado);
    echo "Se actualizó el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='dataconfigura'){
    // configurar valores de lista
	$codlista=$_POST['codlista']; 
	$codcultivo=$_POST['codcultivo']; 
	$project_id=$_POST['project_id']; 
	
    $dataCon=$listaintegrada->select_configura($codlista,$codcultivo);
	if(!empty($dataCon)){
		foreach($dataCon as $row) {
			$arrValor[$row['id_mes']][$row['codunidad']]=$row['valor'];
		}
	}
	$arrMes=$listaintegrada->select_mesconfigura();
	$arrUnidad=$listaintegrada->select_unidad($codlista);
    include("../vista/listaintegrada/frm_configura.php");
	 
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detConfiguraLista'){
	// grabar la configuracion
	$codlista=$_POST['codlista']; 
	$codcultivo=$_POST['codcultivo']; 
	$project_id=$_POST['project_id']; 
	
    $listaintegrada->delete_configuralistaintegrada($codlista,$codcultivo);
	
	$arrUnidadMes=$listaintegrada->select_unidadxMes($codlista);
	foreach($arrUnidadMes as $row){
		$id_mes=$row['id_mes'];
		$id_unidad=$row['id'];
		$nomcontrol='unidadxmes_' . $id_mes . '_'. $id_unidad;
		if(!empty($_POST[$nomcontrol])){
			$valor=$_POST[$nomcontrol];
			$listaintegrada->insert_configura($id_mes,$codcultivo,$id_unidad,$codlista,$project_id,$valor);
		}	
	}
	echo "Se actualizo el registro";	 

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detListaTolerancia'){
	// grabar la configuracion
	$codlista=$_POST['codlista']; 
	$codcultivo=$_POST['codcultivo']; 
	$project_id=$_POST['project_id']; 
	
    $listaintegrada->delete_configuralistatolerancia($codlista,$codcultivo);
	
	$data_cultivo=$listaintegrada->select_cultivos($codlista);
	foreach($data_cultivo as $row){
		$codcultivo=$row['codcultivo'];
		$nomcontrol='tolerancia_' . $codcultivo;
		if(!empty($_POST[$nomcontrol])){
			$valor=$_POST[$nomcontrol];
			$listaintegrada->insert_configuraTolerancia($codcultivo,$codlista,$project_id,$valor,$ip);
		}	
	}
	echo "Se actualizo el registro";
	
// IMPORTAR LISTA
//*************************************************************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_importar'){
	
	$data_pais=$prgpais->selec_paises();
	$data_proyecto=$proyecto->select_proyecto_Select($sess_codpais);
	
    include("../vista/listaintegrada/index_importar.php");	

// carga proyectos del pais
}else if(!empty($_POST['accion']) and $_POST['accion']=='cargaPaisLista'){
	
	$post_id_pais=$_POST['id_pais'];
	$data_proyecto=$proyecto->select_proyecto_Select($post_id_pais);
	
    include("../vista/listaintegrada/select_proyectos.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_valimpLista'){
	
	$codproyecto=$_POST['codproyecto'];
	$codlista=$_POST['masterlista'];
	$caso=$_POST['caso'];
	
	$data_proyecto=$proyecto->selec_one_proyecto($codproyecto);
	$proyect=$data_proyecto['proyect'];
	$project_id=$data_proyecto['project_id'];
	
	
	$seconds = strtotime(date("Y-m-d H:i:s") . " UTC"). $usuario_name;

	if(!empty($_POST['fecha']))
		$fecha=formatdatedos($_POST['fecha']);
	else
		$fecha="01/01/2019";
	if(!empty($_POST['fechaf']))
		$fechaf=formatdatedos($_POST['fechaf']);
	else
		$fechaf="01/01/2019";
	
	$valid_extensions = array('xls','xlsx'); // valid extensions
 // upload directory $pathListasInt
	if(isset($_FILES['fileexcel'])){
		$img = $_FILES['fileexcel']['name'];
		$tmp = $_FILES['fileexcel']['tmp_name'];
		$img=$project_id."_".rand(1,99)."_".$img; // renombrando 050724
	//	echo "Inicia evaluacion<br>";    
						  
		// get uploaded file's extension
		$ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
		// can upload same image using rand function
		// check's valid format
		if(in_array($ext, $valid_extensions)){		
			$path=$pathListasInt.strtolower($img);
		//	echo "Es el formato <br>";    path
				
			if(move_uploaded_file($tmp,"../".$path)){
				
				// leer archivo
				$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load("../".$path);
				$sheetCount = $spreadsheet->getSheetCount();
				$sheetNames = $spreadsheet->getSheetNames();

				$s=0;
				$data_sql="";
				// barremos cada hoja existente
				
				foreach ($sheetNames as $sheetIndex => $sheetName) {
					$sheet = $spreadsheet->getSheet($sheetIndex);
					
					$codunidad=$sheetName;
					
					
					// traer la data
					$sheetData = $sheet->toArray(null, true, true, true);
					$highestRow = $sheet->getHighestRow(); // e.g. 10
					$highestColumn = $sheet->getHighestColumn(); // e.g 'F'
					$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
					// fin data
						
					if(substr($codunidad,0,1)=='F'){
						
						
						$cultivo="OTROS";
						$numfilas=34;
						$fincad=",'$cultivo')";
						// validar si es banano
						
						$valueTitle = $sheet->getCellByColumnAndRow(2,1)->getValue();
						
						if(!empty($valueTitle)){
							$cultivo=$valueTitle;
							
							if($cultivo=="SOLO PARA BANANO"){
								$cultivo="BANANO";
								$numfilas=45; // se aumento 9 columnas 221221
								$fincad=",0,'$cultivo')";
							} //else $cultivo="OTROS";
						}	
						//**************************************************
					
						// inicio leer cabeceras
						$data_part="";
						for ($j = 1; $j <= $numfilas; ++$j) {

							 if($data_part!='') $data_part.=", ";
							 if($j==3 or $j==4)
								 $titulo=$sheet->getCellByColumnAndRow($j,10)->getValue();
							 else	 
								$titulo=$sheet->getCellByColumnAndRow($j,9)->getValue();
							
							 $titulo=str_replace("(1)","",$titulo);
							 $titulo=trim($titulo);
							 $titulo=preg_replace("/\r|\n/", "", $titulo);
							 $titulo=str_replace(" ", "", $titulo);
							 $data_part.="('$seconds','$project_id','$proyect','$codunidad','".($titulo)."')";
						}	
						

						if($data_part!=''){
							$listaintegrada->insert_predataCab_excel($data_part);
						}
					
						$data_part="";
						// fin leer cabeceras
						// leer cada fila
						$tmpunidad="";


						for ($i = 10; $i <= $highestRow; ++$i) {
							// leer cada columna
							$value = $sheet->getCellByColumnAndRow(1, $i)->getValue();
							if(!empty($value) and is_numeric($value) ){ // con valor

								for ($j = 1; $j <= $numfilas; ++$j) {
									$value = $sheet->getCellByColumnAndRow($j, $i)->getValue();
									
									if(strstr($value,'='))
										$value = $sheet->getCellByColumnAndRow($j, $i)->getOldCalculatedValue();
					
									 if(!empty($value) or $value==0)
										$array[$j]=$value;
									 else 
										 $array[$j]='';
								}
								
								$data_part="('$seconds','$project_id','$proyect','$codunidad'";
								
								if($tmpunidad!=$codunidad){
									$fila=11;
									$tmpunidad=$codunidad;
								}
								for($d=1;$d<=$numfilas;$d++){ // cantidad de columnas
									if($d==1)
										$data_part.=",'$fila'";
									$array[$d]=str_replace("'","",$array[$d]);
									if($array[$d]=='')
										$data_part.=",null";
									else
										$data_part.=",'".utf8_encode($array[$d])."'";
								}
								$fila++;
								
								$data_part.=$fincad;
								if($data_sql=='') $data_sql=$data_part;
								else $data_sql.=",".$data_part;
								
								
							}
						}
						
					
					}else if($codunidad=='Registro del SIC'){
						
						// se agrego esta importacion de pestana. 221221
						
						$data_sic="";
						for ($i = 9; $i <= $highestRow; ++$i) {
						//for ($i = 9; $i <= $data->sheets[$s]['numRows']; $i++) {
							// leer cada columna
							$value = $sheet->getCellByColumnAndRow(1, $i)->getValue();
							if(strstr($value,'='))
								$value = $sheet->getCellByColumnAndRow(1, $i)->getOldCalculatedValue();
									
							if(!empty($value) and is_numeric($value) ){ // con valor
							
							//if(!empty($data->sheets[$s]['cells'][$i][1]) and is_numeric($data->sheets[$s]['cells'][$i][1]) ){ // con valor
								$data_part="('$seconds','$codproyecto'";
								for ($j = 1; $j <= 13; $j++) { // 
									
									$value = $sheet->getCellByColumnAndRow($j, $i)->getValue();
									
									if(strstr($value,'='))
										$value = $sheet->getCellByColumnAndRow($j, $i)->getOldCalculatedValue();
									
									if(!empty($value)){
										$array[$j]=$value;
										
										if($j==2 or $j==13){
											
											if(strpos($array[$j],'/')){
												$arr=explode('/',$array[$j]);
												if(strlen($arr[2])==4)
													$array[$j]=formatdatedos($array[$j]);
											}else if(strpos($array[$j],'-')){
												$arr=explode('/',$array[$j]);
												if(strlen($arr[2])==4)
													$array[$j]=formatdateCal($array[$j]);
											}else if(is_numeric($array[$j])){
												$array[$j]=convertDate($array[$j]);
											}
										}	
									 }else 
										 $array[$j]='';
									 
									 if($array[$j]=='' or str_contains($array[$j],'#REF!'))
										 $data_part.=",null";
									 else
										 $data_part.=",'".($array[$j])."'";
								}
								$data_part.=")";
								if($data_sic=='') $data_sic=$data_part;
								else $data_sic.=",".$data_part;
							}
							
							
						}
						
					}
					$s++;
				}			
				

				if(!empty($data_sic)){
					// 2. crea el log de los datos a importar
					$listaintegrada->insert_predataSic_excel($data_sic);
				}
				
				if($data_sql!=''){
					// 2. crea el log de los datos a importar
					if($cultivo=='BANANO')
						$listaintegrada->insert_predataDet_excel($data_sql);
					else
						$listaintegrada->insert_predataDet_excel_nobanano($data_sql);
				}
				//******************************************
				// inicio validar errores del archivo
				//******************************************
				$cadenaVacio="";
				$dataCab=$listaintegrada->select_cabecera($cultivo);
				$i=0;
				if(!empty($dataCab)){
					foreach($dataCab as $row) {
						$arrayCabe[$i]=preg_replace("/\r|\n/", "", $row['nombre']);
						$arrayCabe[$i]=str_replace(" ", "", $arrayCabe[$i]);

						$arrayCabeColu[$i]=$row['columna'];
						$arrayCabeNum[$i]=$row['isnumero'];
						$arrayCabeFec[$i]=$row['isfecha'];
						$arrayCabeAni[$i]=$row['isanio'];
						$arrayCabeVal[$i]=$row['valores'];
						$arrayCabeVacio[$i]=$row['isvacio'];
						$i++;
					}
				}
				
				$dataCabtmp=$listaintegrada->select_cabecera_tmp($seconds);
				
				$i=0;
				if(!empty($dataCabtmp)){
					foreach($dataCabtmp as $row) {
						$arrayCabeFile[$i]=$row['nombre'];
						$i++;
					}
				}
				
				$dataLog=$listaintegrada->select_data_log($cultivo,$seconds);
				if(!empty($dataLog)){
					foreach($dataLog as $row) {
						$column=-1;
						foreach($row as $value){
							$column++;
							if($column>2 and $column< ($numfilas+1)){
								$x=$column-3;
								if(($value=='' or $value==null) and is_numeric($column) and !empty($arrayCabeFile[$x]) and $arrayCabeVacio[$x]!='1'){ // is numeric evalua solo una vez el array, vienen doble
								
									//$arrayData[$i][$column]=$value;
									$cadenaVacio.="Columna '". ($arrayCabeFile[$x]) ."' en unidad $row[codunidad] y la fila $row[fila] es vacio!.$column<br>";
									//break;
								}
								if(!empty($arrayCabeNum[$x]) and $arrayCabeNum[$x]=='1' and !is_numeric($value) and is_numeric($column) and $column>2 and $arrayCabeVacio[$x]!='1'){ // debe ser numerico
									$cadenaVacio.="Columna  '". ($arrayCabeFile[$x]) ."' en unidad $row[codunidad] y la fila $row[fila]debe ser numerico. Actualmente es $value. <br>";
									// break;
								}
								
								if(!empty($arrayCabeAni[$x]) and $arrayCabeAni[$x]=='1' and is_numeric($column) and $column>2
										and (!is_numeric($value) or $value>date("Y")) and $value!='N.A.' and $arrayCabeVacio[$x]!='1'
									){ // debe ser anio
									$cadenaVacio.="Columna  '". ($arrayCabeFile[$x]) ."' en unidad $row[codunidad] y la fila $row[fila] debe ser un A&ntilde;o.";
									if(str_replace(" ","",$value)=="")
										$cadenaVacio.="Actualmente es vacio.<br>";
									elseif(substr($value,-1)==" ")
										$cadenaVacio.="Corregir el espacio al final.<br>";
									elseif(substr($value,0,1)==" ")
										$cadenaVacio.="Corregir el espacio al inicio.<br>";
									else
										$cadenaVacio.="Actualmente es $value.<br>";
									// break;
								}
								
								if((substr($value,-1)==" " or substr($value,0,1)==" ") and is_numeric($column) and $arrayCabeVacio[$x]!='1'){ // no tener espacio blanco al inicio o final
									$cadenaVacio.="Columna '". ($arrayCabeFile[$x]) ."' en unidad $row[codunidad] y la fila $row[fila] tiene un espacio vacio. Valor es #$value#";
									if(str_replace(" ","",$value)=="")
										$cadenaVacio.="Actualmente es vacio.<br>";
									elseif(substr($value,-1)==" ")
										$cadenaVacio.="Corregir el espacio al final.<br>";
									elseif(substr($value,0,1)==" ")
										$cadenaVacio.="Corregir el espacio al inicio.<br>";
									else
										$cadenaVacio.="Actualmente es $value.<br>";
									// break;
								}
											
								if(!empty($arrayCabeFec[$x]) and $arrayCabeFec[$x]=='1' and is_numeric($column) and  !empty($arrayCabeVal[$x]) and $arrayCabeVal[$x]!=''){ // es fecha y valores
									$arrayCom=explode("/",$value);
									if((!empty($arrayCom[2]) and !checkdate($arrayCom[1],$arrayCom[0],$arrayCom[2])) and !strpos($arrayCabeVal[$x],$value))
										$cadenaVacio.="Columna '". ($arrayCabeFile[$x]) ."' en unidad $row[codunidad] y la fila $row[fila] debe ser fecha o el valor debe ser $arrayCabeVal[$x]. El valor actual es $value <br>";
									unset($arrayCom);
								}else if(!empty($arrayCabeFec[$x]) and $arrayCabeFec[$x]=='1' and !strpos($value,"/") and is_numeric($column)){ // debe ser fecha
									$arrayCom=explode("/",$value);
									if(!empty($arrayCom[2]) and !checkdate($arrayCom[1],$arrayCom[0],$arrayCom[2]))
										$cadenaVacio.="Columna '". ($arrayCabeFile[$x]) ."' en unidad $row[codunidad] y la fila $row[fila] debe ser fecha. El valor es $value <br>";
									unset($arrayCom);
								}else if(!empty($arrayCabeVal[$x]) and !empty($value) and $arrayCabeVal[$x]!='' and is_numeric($column) and !strpos($arrayCabeVal[$x],$value)){ // rango de valores
									//$arrayCom=explode(",",$arrayCabeVal[$x]);
									//if(!in_array($value,$arrayCom))
										$cadenaVacio.="Columna '". ($arrayCabeFile[$x]) ."' en unidad $row[codunidad] y la fila $row[fila] no tiene un valor permitido ($arrayCabeVal[$x]). Actualmente es $value.  <br>";
									//unset($arrayCom);
								}
							 }
							
						}
					}
				}
				// valida nombe de columnas y posicion
				if(!empty($arrayCabe)){
					foreach($arrayCabe as $clave =>  $valor){
						if($valor!=$arrayCabeFile[$clave]){
							$cadenaVacio.= "<br>La columna ".$arrayCabeColu	[$clave]." es diferente. Dice" . ($arrayCabeFile[$clave]) ." debe decir ". ($valor) .".<hr>";
						}		
					}
				}
				
				$dataLogDup=$listaintegrada->select_data_log_duplica($seconds);
				if(!empty($dataLogDup)){
					foreach($dataLogDup as $rowCo){
						$cadenaVacio.= "Nombre del agricultor diferente para un mismo c&oacute;digo de campo: $rowCo[codcampo] para los agricultores $rowCo[nombre] <br>";
					}
				}
				
				//stef 21082021
				$dataAgriDup=$listaintegrada->select_data_agricu_duplica($seconds);
				if(!empty($dataAgriDup)){
					foreach($dataAgriDup as $rowCo){
						$cadenaVacio.= "Nombre del agricultor diferente para un mismo c&oacute;digo de agricultor: $rowCo[codagricultor] para los agricultores $rowCo[nombre] <br>";
					}
				}
				//fany 25022023
				$dataCampoDupCod=$listaintegrada->select_data_campo_duplicaCodigo($seconds);
				if(!empty($dataCampoDupCod)){
					foreach($dataCampoDupCod as $rowCo){
						$cadenaVacio.= "El campo con código $rowCo[codcampo]  $rowCo[donde]  tienen el mismo nombre de campo $rowCo[finca]. Este debe ser diferente ya que representa situaciones diferentes <br>";
					}
				}
				
				//fany 23042022
				$dataAgriDupCod=$listaintegrada->select_data_agricu_duplicaCodigo($seconds);
				if(!empty($dataAgriDupCod)){
					foreach($dataAgriDupCod as $rowCo){
						$cadenaVacio.= "Nombre del agricultor con diferentes códigos de agricultor: $rowCo[nombre] con los c&oacute;digos $rowCo[codagricultor] <br>";
					}
				}
				
				$dataAgriDupCedula=$listaintegrada->select_data_agricu_duplicaCedula($seconds);
				if(!empty($dataAgriDupCedula)){
					foreach($dataAgriDupCedula as $rowCo){
						$cadenaVacio.= "Nombre del agricultor con diferentes cedula: $rowCo[nombre] con $rowCo[cedula] <br>";
					}
				}
				
				$dataAgriDupCedula=$listaintegrada->select_data_cedula_duplica($seconds);
				if(!empty($dataAgriDupCedula)){
					foreach($dataAgriDupCedula as $rowCo){
						$cadenaVacio.= "Cedula que se duplica en mas de un agricultor: $rowCo[cedula] para los agricultores $rowCo[nombre] <br>";
					}
				}
				
				//******************************************
				// fin validar errores de archivo
				//dataAgric, dataCampo_new, dataAgric_new, dataCampo_out, dataAgric_out
				//******************************************
				$dataAgric=$listaintegrada->select_data_agricultores($codlista,$seconds);
				$dataStatus=$listaintegrada->select_data_status($codlista,$seconds);
				$dataAgric_new=$listaintegrada->select_data_agricultores_news($codlista,$seconds);
				$dataCampo_new=$listaintegrada->select_data_campo_news($codlista,$seconds);
				
				$dataCampo_out=$listaintegrada->select_data_campo_out($codlista,$seconds);
				$dataAgric_out=$listaintegrada->select_data_agricultores_out($codlista,$seconds);

				if(empty($cadenaVacio)){
					// calcular proceso de REPORTE PARA CERTIFICADO MASTER
					$listaintegrada->procedimiento_reporte_master($seconds);
				}
				
				include("../vista/listaintegrada/vistaTem_importa.php");	
			}
     	}
	}else{
		echo 'Archivo invalido';
	}

}else if(!empty($_POST['accion']) and $_POST['accion']=='cargaLista'){
	
	$codproyecto=$_POST['codproyecto'];
	$data_proyecto=$proyecto->selec_one_proyecto($codproyecto);
	$project_id=$data_proyecto['project_id'];
	
	$data_lista=$listaintegrada->select_listaxproyecto($project_id,'1','');
	
	include("../vista/listaintegrada/verListaxProyecto.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_grafico'){
	
	if(!empty($_POST['from']) and $_POST['from']=='lista')
		include("../assets/lang/language_esp.php");
	// data_2
	$array = array('eu', 'usda', 'rpto' );
	$codproyecto=$_POST['codproyecto'];
	$seconds=$_POST['seconds'];

	$varExcel="";
	if(!empty($_POST['excel'])){
		$varExcel=" border=1 ";
		if(!empty($_POST['codlista'])){	
			$codlista=$_POST['codlista'];
			$dataAgric=$listaintegrada->select_data_agricultores($codlista,$seconds);
			$dataStatus=$listaintegrada->select_data_status($codlista,$seconds);
			$dataAgric_new=$listaintegrada->select_data_agricultores_news($codlista,$seconds);
			$dataCampo_new=$listaintegrada->select_data_campo_news($codlista,$seconds);
					
			$dataCampo_out=$listaintegrada->select_data_campo_out($codlista,$seconds);
			$dataAgric_out=$listaintegrada->select_data_agricultores_out($codlista,$seconds);
					
			include("../vista/listaintegrada/xlsTem_importa.php");	
		}	
	}	
	
	
	$data_proyecto=$proyecto->selec_one_proyecto($codproyecto);
	$project_id=$data_proyecto['project_id'];
	
	// data_certificado_
	$data_uniImp=$listaintegrada->select_unidad_importarlog($seconds);
	$data_cultImp=$listaintegrada->select_cultivo_importarlog($seconds);
	$cultivo=$data_cultImp['cultivo'];
	if($cultivo=='BANANO' or $cultivo=='CAVENDISH') $flgisbanano=1;
	else $flgisbanano=0;
	
	$i=0;
	foreach($data_uniImp as $rowUni) {
		$codunidad=$rowUni['codunidad'];
		$arraUnidad[$i]=$codunidad;

		if($i > 0){
			for($a=0;$a<$i;$a++){
				$stringCondition .= $data_uniImp[$a]['codunidad'].",";
			}
		}
		$condition = substr($stringCondition, 0, -1);

		foreach($array as $variable){

			$data_[$variable.$codunidad]=$listaintegrada->select_areacultivo_unidad($variable,$codunidad,$seconds);
			$data_1[$variable.$codunidad]=$listaintegrada->select_areacultivo_unidadUSDA($variable,$codunidad,$seconds);
			$data_2[$variable.$codunidad]=$listaintegrada->select_agri_unidad_new($variable,$codunidad,$seconds);
			$data_21[$variable.$codunidad]=$listaintegrada->select_agri_unidad_newUSDA($variable,$codunidad,$seconds);
			$data_3[$variable.$codunidad]=$listaintegrada->select_rdto_unidad($variable,$codunidad,$seconds);
			$data_31[$variable.$codunidad]=$listaintegrada->select_rdto_unidadUSDA($variable,$codunidad,$seconds);
			
			//Reporte: Certificado Master data_certificado_
			$not_in_cedula = 1;
			$data_certificado_[$variable.$codunidad]=$listaintegrada->select_certificado_master($variable,$codunidad,$seconds,$not_in_cedula);
		}
		$i++;
	}
	//print_r($arraUnidad); data_2

	$data_paisPro=$listaintegrada->select_pais_proyecto($project_id);
	$codpais_ori=$data_paisPro['id_pais'];
	
	$data_canAgri=$listaintegrada->select_cantidad_agri($seconds);
	$tot_agricultor=$data_canAgri['cantidad'];
	//data_
	$data_canCam=$listaintegrada->select_cantidad_campo($seconds);
	$tot_campo=$data_canCam['cantidad'];
	
	$data_canCedu=$listaintegrada->select_cantidad_cedulabyunidad($seconds,$project_id);
	$data_canCampo=$listaintegrada->select_cantidad_campobyunidad($seconds,$project_id);
	
	// datos de promedios data_promedio
	$data_promedio=$listaintegrada->select_promedio_byunidad($project_id,$seconds);
	
	$data_cedulaxunidad=$listaintegrada->select_duplica_cedulaxunidad($project_id,$seconds);
	$data_cedulaxcampo=$listaintegrada->select_duplica_cedulaxcampo($project_id,$seconds);
	
	// visitas mayores a 365 dias  arraUnidad arraUnidad
	$data_visitas_anio=$listaintegrada->select_predataSic_fecha($seconds);
	
	
	/* calculo de reporte de certificado master*/
	
	//$listaintegrada->procedimiento_reporte_master($seconds);
	$data_rep_org=$listaintegrada->select_certificado_master_new($seconds,'ORG');
	$data_rep_usda=$listaintegrada->select_certificado_master_new($seconds,'USDA');
	$data_rep_eu=$listaintegrada->select_certificado_master_new($seconds,'EU');
	
	include("../vista/listaintegrada/verGrafico.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='to_excel4'){

	require_once '../assets/PHPExcel/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->
	getProperties()
	->setCreator("enrique bazalar")
	->setTitle("Exportar")
	->setSubject("Reportes")
	->setKeywords("control union reportes")
	->setCategory("reportes");

	// hoja 1
	$border_style= array('borders' => array('allborders' => array('style' => 
		PHPExcel_Style_Border::BORDER_THIN,'color' => array('argb' => '766f6e'),)));
		
	$background_style= array('fill' => array('type' =>  
		PHPExcel_Style_Fill::FILL_SOLID,'color' => array('argb' => 'D6EAF8'),));

	$font_style= array('font' =>  array('bold' => true,'color' =>array('argb' => 'ffffff') ),);
	$center_style= array('alignment' =>  array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER ),);

	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B1', 'TABLA DE USUARIOS')
		;
		

	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B2', 'Nombre')
		->setCellValue('C2', 'E-mail')
		->setCellValue('D2', 'Twitter')
		->setCellValue('B3', 'David')
		->setCellValue('C3', 'dvd@gmail.com')
		->setCellValue('D3', '@davidvd');

	$objPHPExcel->getActiveSheet()->setTitle('Usuarios');
	$objPHPExcel->getActiveSheet()
					->getStyle('B2:D3')->applyFromArray($border_style);
	$objPHPExcel->getActiveSheet()
					->getStyle('B2:D2')->applyFromArray($font_style);
	$objPHPExcel->getActiveSheet()
					->getStyle('B2:D2')->applyFromArray($background_style);			

	// hoja 2
	$objPHPExcel->createSheet();
	$objPHPExcel->setActiveSheetIndex(1)
		->setCellValue('A1', 'Apellido')
		->setCellValue('B1', 'mail')
		->setCellValue('C1', 'Twitter')
		->setCellValue('A2', 'David')
		->setCellValue('B2', 'dvd@gmail.com')
		->setCellValue('C2', '@davidvd');
	$objPHPExcel->getActiveSheet()->setTitle('Clientes');

	$objPHPExcel->setActiveSheetIndex(0);
/*
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="exportarLista.xlsx"');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');

	// If you're serving to IE over SSL, then the following may be needed
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header ('Pragma: public'); // HTTP/1.0
*/	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
	ob_start();
	$objWriter->save('php://output');
	$xlsData = ob_get_contents();
	ob_end_clean();
	
	echo  "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,". base64_encode($xlsData);
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='to_excel'){

	$array = array('eu', 'usda', 'rpto' );
	$codproyecto=$_POST['codproyecto'];
	$seconds=$_POST['seconds'];
	
	$varExcel="";
	if(!empty($_POST['excel']))
		$varExcel=" border=1 ";
	
	
	$data_proyecto=$proyecto->selec_one_proyecto($codproyecto);
	$project_id=$data_proyecto['project_id'];
	
	$data_uniImp=$listaintegrada->select_unidad_importarlog($seconds);
	$data_cultImp=$listaintegrada->select_cultivo_importarlog($seconds);
	$cultivo=$data_cultImp['cultivo'];
	if($cultivo=='BANANO' or $cultivo=='CAVENDISH') $flgisbanano=1;
	else $flgisbanano=0;
	
	$i=0;
	foreach($data_uniImp as $rowUni) {
		$codunidad=$rowUni['codunidad'];
		$arraUnidad[$i]=$codunidad;
		foreach($array as $variable){
			$data_[$variable.$codunidad]=$listaintegrada->select_areacultivo_unidad($variable,$codunidad,$seconds);
			$data_2[$variable.$codunidad]=$listaintegrada->select_agri_unidad($variable,$codunidad,$seconds);
			$data_3[$variable.$codunidad]=$listaintegrada->select_rdto_unidad($variable,$codunidad,$seconds);
		}
		$i++;
	}
	//print_r($arraUnidad);

	$data_paisPro=$listaintegrada->select_pais_proyecto($project_id);
	$codpais_ori=$data_paisPro['id_pais'];
	
	$data_canAgri=$listaintegrada->select_cantidad_agri($seconds);
	$tot_agricultor=$data_canAgri['cantidad'];
	
	$data_canCam=$listaintegrada->select_cantidad_campo($seconds);
	$tot_campo=$data_canCam['cantidad'];
	
	$data_canCedu=$listaintegrada->select_cantidad_cedulabyunidad($seconds,$project_id);
	$data_canCampo=$listaintegrada->select_cantidad_campobyunidad($seconds,$project_id);
	
	// datos de promedios
	$data_promedio=$listaintegrada->select_promedio_byunidad($project_id,$seconds);
	
	$data_cedulaxunidad=$listaintegrada->select_duplica_cedulaxunidad($project_id,$seconds);
	$data_cedulaxcampo=$listaintegrada->select_duplica_cedulaxcampo($project_id,$seconds);
	
	// visitas mayores a 365 dias
	$data_visitas_anio=$listaintegrada->select_predataSic_fecha($seconds);
	
	// preparacion del componente
	require_once '../assets/PHPExcel/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->
	getProperties()
	->setCreator("enrique bazalar")
	->setTitle("Exportar")
	->setSubject("Reportes")
	->setKeywords("control union reportes")
	->setCategory("reportes");

	// hoja 1
	$border_style= array('borders' => array('allborders' => array('style' => 
		PHPExcel_Style_Border::BORDER_THIN,'color' => array('argb' => '766f6e'),)));
		
	$background_style= array('fill' => array('type' =>  
		PHPExcel_Style_Fill::FILL_SOLID,'color' => array('argb' => 'cdcdcd'),));

	$font_style6= array('font' =>  array('size' => 12,'bold' => true,'color' =>array('argb' => '000000') ),);
	$font_style= array('font' =>  array('size' => 10,'bold' => false,'color' =>array('argb' => '000000') ),);

	$center_style= array('alignment' =>  array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER ),);

	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B2', 'RESUMEN GENERAL')
		;

	// table 1 datos
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B4', $lang_listasint[31])
		->setCellValue('B5', $lang_listasint[32])
		->setCellValue('C4', $tot_agricultor)
		->setCellValue('C5', $tot_campo);

	// table 2 datos
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B7', $lang_listasint[33])
		->setCellValue('B8', $lang_analisis_unid)
		->setCellValue('C8', $lang_listasint[30]);
	
	$objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
	
	$itable2=8;
	foreach($data_canCedu as $row) {
		$itable2++;
		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B'.$itable2, $row['codunidad'])
		->setCellValue('C'.$itable2, number_format($row['cantidad'],0));
		
	}	
	
	// table 3 datos
	$itable3=$itable2+4;
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B'.($itable3-1), '# total de campos por unidad')
		->setCellValue('B'.$itable3, $lang_analisis_unid)
		->setCellValue('C'.$itable3, 'Campos');
	
	$objPHPExcel->getActiveSheet()->mergeCells('B'.($itable3-1).':C'.($itable3-1));
	
	foreach($data_canCampo as $row) {
		$itable3++;
		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B'.$itable3, $row['codunidad'])
		->setCellValue('C'.$itable3, number_format($row['cantidad'],0));
		
	}	

	$objPHPExcel->getActiveSheet()->setTitle('General');
	
	// table 1
	$objPHPExcel->getActiveSheet()
					->getStyle('B4:C5')->applyFromArray($border_style);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(false);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('40');
	
	// td
	$objPHPExcel->getActiveSheet()
					->getStyle('C4:C5')->applyFromArray($font_style);
	
	// th
	$objPHPExcel->getActiveSheet()
					->getStyle('B4:B5')->applyFromArray($font_style6);
	$objPHPExcel->getActiveSheet()
					->getStyle('B4:B5')->applyFromArray($background_style);	
	$objPHPExcel->getActiveSheet()
					->getStyle('B4:B5')->applyFromArray($center_style);	
						

	// table 2
	
	$objPHPExcel->getActiveSheet()
					->getStyle('B7:C'.$itable2)->applyFromArray($border_style);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(false);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('20');
	
	// th
	$objPHPExcel->getActiveSheet()
					->getStyle('B7:C8')->applyFromArray($font_style6);
	$objPHPExcel->getActiveSheet()
					->getStyle('B7:C7')->applyFromArray($background_style);		
	$objPHPExcel->getActiveSheet()
					->getStyle('B7:C8')->applyFromArray($center_style);					

	
	// table 3
	$objPHPExcel->getActiveSheet()
					->getStyle('B'.($itable2+3).':C'.$itable3)->applyFromArray($border_style);
	$objPHPExcel->getActiveSheet()
					->getStyle('B'.($itable2+3).':C'.($itable2+4))->applyFromArray($font_style6);
	$objPHPExcel->getActiveSheet()
					->getStyle('B'.($itable2+3).':C'.($itable2+3))->applyFromArray($background_style);	
	
	$objPHPExcel->getActiveSheet()
					->getStyle('B'.($itable2+3).':C'.($itable2+4))->applyFromArray($center_style);	
					
	
	// hoja 2 
	//***************************************************
	$objPHPExcel->createSheet();
	$objPHPExcel->setActiveSheetIndex(1);
	$objPHPExcel->getActiveSheet()->setTitle('Unidad');
	


	$objPHPExcel->setActiveSheetIndex(0);

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
	ob_start();
	$objWriter->save('php://output');
	$xlsData = ob_get_contents();
	ob_end_clean();
	
	echo  "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,". base64_encode($xlsData);

	exit;

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_rendimiento'){
	
	$seconds=$_POST['seconds'];
	$txtrendimiento=$_POST['txtrendimiento'];
	
	$data_cultImp=$listaintegrada->select_cultivo_importarlog($seconds);
	$cultivo=$data_cultImp['cultivo'];
	if($cultivo=='BANANO' or $cultivo=='CAVENDISH' or $cultivo=='Bananas') $flgisbanano=1;
	else $flgisbanano=0;
	
	
	if(!empty($_POST['project_id']))
		$project_id=$_POST['project_id'];
	
	else{
		$codproyecto=$_POST['codproyecto'];
		$data_proyecto=$proyecto->selec_one_proyecto($codproyecto);
		$project_id=$data_proyecto['project_id'];
	}
	
	$data_rdto=$listaintegrada->select_rendimiento($project_id,$txtrendimiento,$seconds);
	include("../vista/listaintegrada/verRendimiento.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_sobreproduce'){
	
	
	$seconds=$_POST['seconds'];
	$txtsobreproduce=$_POST['txtsobreproduce'];
	$txtsobreproduce_por=$_POST['txtsobreproduce_por'];
	
	$data_cultImp=$listaintegrada->select_cultivo_importarlog($seconds);
	$cultivo=$data_cultImp['cultivo'];
	if($cultivo=='BANANO' or $cultivo=='CAVENDISH') $flgisbanano=1;
	else $flgisbanano=0;
	
	if(!empty($_POST['project_id']))
		$project_id=$_POST['project_id'];
	else{
		$codproyecto=$_POST['codproyecto'];
		$data_proyecto=$proyecto->selec_one_proyecto($codproyecto);
		$project_id=$data_proyecto['project_id'];
	}	
	
	$data_rdto=$listaintegrada->select_sobreproduce($project_id,$txtsobreproduce,$txtsobreproduce_por,$seconds);
	include("../vista/listaintegrada/verSobreproduce.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='xls_comparacion'){
	$bloqueXls="1";
	$codlista=$_POST['codlista'];
	$dataAgric=$listaintegrada->select_data_agricultores($codlista);
	$dataAgric_new=$listaintegrada->select_data_agricultores_news($codlista);
	$dataAgric_out=$listaintegrada->select_data_agricultores_out($codlista);
	include("../vista/listaintegrada/vistaTem_importa.php");	



}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_importar'){
	
	$codproyecto=$_POST['codproyecto'];
	$data_proyecto=$proyecto->selec_one_proyecto($codproyecto);
	$project_id=$data_proyecto['project_id'];
	$proyect=$data_proyecto['proyect'];
	$ref_pais=$data_proyecto['id_pais'];
	
	$path=$_POST['path'];
	$fecha=formatdatedos($_POST['fecha']);
	$fechaf=formatdatedos($_POST['fechaf']);
	$cultivo=$_POST['cultivo'];
	$masterlista=$_POST['masterlista']; // referencia de lista asociada
	
	
	
	$caso=$_POST['caso']; // caso de importacion

	if(!empty($masterlista) and $caso=='A'){
		$data_=$listaintegrada->selec_one_listaintegrada($masterlista);
		$fecha=$data_['fechainicio'];
		$fechaf=$data_['fechatermino'];
	}

	$pico_i = $_POST['pico_i'];
	$pico_f = $_POST['pico_f'];
	$semanai = $_POST['semanai'];
	$semanaf = $_POST['semanaf'];
	$baja_i = $_POST['baja_i'];
	$baja_f = $_POST['baja_f'];
	$tolerancia=$_POST['tolerancia'];

	$tipo_lista=$cultivo;
	if($cultivo!='BANANO')
		$tipo_lista='OTROS';
	// $tipo_lista = strlen(stristr($cultivo,'menos'))>0 ? "OTROS" : "BANANO";

	$quehacer=$_POST['quehacer'];
	$seconds=$_POST['seconds'];
	
	if($caso=='N' or $caso=='R' or $caso=='A'){
		$codlista=$listaintegrada->paso_01($caso,$ref_pais,$project_id,$sess_codpais,$proyect,$fecha,$fechaf,$usuario_name,$ip,$tipo_lista,$masterlista);	
		$listaintegrada->paso_02($codlista,$path,$project_id,$sess_codpais,$usuario_name,$ip,$quehacer,$seconds);
		
		if($caso=='N' or $caso=='R' or $caso=='A')
			$codlista=$listaintegrada->update_conf_lista($codlista,$pico_i,$pico_f,$semanai,$semanaf,$baja_i,$baja_f,$tolerancia);
		
		if($caso=='R' or $caso=='A')
			$codlista=$listaintegrada->update_estadolistaintegrada($masterlista,'0'); // desactivar la referencia
		
	}else if($caso=='E'){
		$res=$listaintegrada->update_fechafin_lista($masterlista,$fechaf,$ref_pais,$caso,$usuario_name,$ip);
	
	}else if($caso=='XX'){
		// borra y actualiza el detalle
		$res=$listaintegrada->paso_03($masterlista,$path,$project_id,$sess_codpais,$usuario_name,$ip,$seconds);
	}
	
	
	if($caso=='N'){
		/*************************************************************
		 8. CREAR EL USUARIO Y CLAVE DE ACCESO si no existiera en el sistema
		 tabla: lst_proyecto
		************************************************************/

		$dataPr=$listaintegrada->select_lstproyecto($project_id,$sess_codpais);

		if(empty($dataPr)){
			$listaintegrada->insert_lstproyecto($project_id,$usuario_name,$ip,$sess_codpais);	
			echo "<p>Se creo el usuario y clave $project_id para el acceso del Proyecto.</p>";
		}	
	}

	echo "<p>Se actualizo la lista del proyecto $project_id </p>";
		
	/****************************************************
	fin de crear
	******************************************************/

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_temporal'){
	
	$data_auditor="0";
	$data_auditor=$mlauditor->selec_one_auditor($sess_codauditor);
	$data_motivo=$listaintegrada->selec_motivos();
	
	if(!empty($data_auditor))
		$flgadminsli=$data_auditor['flgadminsli'];
	
	if($flgadminsli=='1')
		$data_pais=$prgpais->selec_paises();
    
	include("../vista/listaintegrada/index_temporal.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_temporal_res'){
	
	//***********************************************************
	// funcion buscador tabla lista integrada
	//***********************************************************
	
	## Read value
	$proyecto = $_POST['proyecto'];
	$codmotivo = $_POST['codmotivo'];
	$estado = $_POST['estado'];
	$fechai = $_POST['fechai'];
	$fechaf = $_POST['fechaf'];
	
	
	if(!empty($_POST['var_pais']))
		$var_pais = $_POST['var_pais'];
	else
		$var_pais=$sess_codpais;
	
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" c.project_id";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and p.id_pais='$var_pais'";

	if(!empty($proyecto))
		$searchQuery.=" and (p.proyecto like '%$proyecto%' or p.project_id like '%$proyecto%' ) ";
	
	if(!empty($estado))
		$searchQuery.=" and c.estado='$estado'";
		
	if(!empty($codmotivo))
		$searchQuery.=" and c.codmotivo=$codmotivo ";


	if(!empty($fechai)){
		$fechai=formatdatedos($fechai);
		$searchQuery.=" and to_days(c.fecha_ingreso)>= to_days('$fechai')  ";
	}
	
	if(!empty($fechaf)){
		$fechaf=formatdatedos($fechaf);
		$searchQuery.=" and to_days(c.fecha_ingreso)<= to_days('$fechaf')  ";
	}
	
	
	## Total number of records without filtering
	$data_maxOF=$listaintegrada->selec_total_liTemporal();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$listaintegrada->selec_total_liTemporal($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$listaintegrada->select_liTemporal($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codfile'];
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliListatemporal'><i class='fas fa-trash'></i> </button>";
			$popup="<button type='button' id='popup_". $id ."_".$row['project_id']."'  class='btn  btn_verliAnexos'><i class='fas fa-edit'></i> </button>";
			if($row['estado']=='CERRADOOSP')
				$estado='CERRADO OSP';
			else 
				$estado=$row['estado'];
			
		   $data[] = array( 
			   "proyecto"=>str_replace('"','',json_encode($row['proyecto'],JSON_UNESCAPED_UNICODE)),
			   "project_id"=>str_replace('"','',json_encode($row['project_id'],JSON_UNESCAPED_UNICODE)),
			   "fecha"=>$row['fecha'],
			   "motivo"=>$row['motivo'],
			   "nombrelista"=>$row['nombrelista'],
			   "estado"=>$estado,
			   "codfile"=>$id,
			   "elimina"=>$elimina,
			   "popup"=>$popup,
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='delListaTemproal'){
    // delete a la base de datos lista integrada
	$codfile=$_POST['codfile']; 
    $listaintegrada->delete_listaTemporal($codfile);
    echo "Se elimino el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_sli'){
  include("../vista/listaintegrada/index_sli.php");	    

}else if(!empty($_POST['accion']) and $_POST['accion']=='verliAnexos'){
  $codfile=$_POST['codfile']; 
  $project_id=$_POST['project_id']; 
  $data_proyecto=$listaintegrada->selec_one_proyectobyid($project_id);  
  $datafile=$listaintegrada->selec_one_liTemporal($codfile);
  $datafileanexo=$listaintegrada->selec_liTemporalanexo($codfile);
  
  include("../vista/listaintegrada/frm_anexos.php");	 

}else if(!empty($_POST['accion']) and $_POST['accion']=='logfileanexo'){
    // delete a la base de datos lista integrada
	$codanexo=$_POST['codanexo']; 
	
	$row=$listaintegrada->selec_one_liTemporalfileanexo($codanexo);
	$codile=$row['codfile'];
    $listaintegrada->log_listaTemporalanexo($codile,$codanexo,$sess_codauditor,$usuario_name,$ip);
    echo "Se registro el log.";
	

}else if(!empty($_POST['accion']) and $_POST['accion']=='verliAnexoslog'){
  $codanexo=$_POST['codanexo']; 
  $datalog=$listaintegrada->selec_logliTemporalanexo($codanexo);
  
  include("../vista/listaintegrada/frm_anexoslog.php");
  
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detConfile'){
  $codfile=$_POST['codfile']; 
  
  $estado="ATENDIDO";
  if(!empty($_POST['flgcerrado'])){
	$estado="CERRADO"; 
	$datalog=$listaintegrada->update_estadofileanexo($codfile,$estado,$sess_codauditor,$usuario_name);
  }else if(!empty($_POST['flgcerradoosp'])){
	$estado="CERRADOOSP"; 
	$datalog=$listaintegrada->update_estadofileanexo_osp($codfile,$estado,$sess_codauditor,$usuario_name);
  }
}
?>
