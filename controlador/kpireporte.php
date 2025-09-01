<?php
include("../com/valSession.php");
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/kpi_reporte_modelo.php");

$kpireporte=new kpi_reporte_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='reporte'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	$tipokpi='aud';
	$pais_res=$kpireporte->selec_pais_reporte($sess_codpais,$tipokpi);
	$oficina_res=$kpireporte->selec_oficina_reporte($sess_codpais,$tipokpi);
	
	$certificador_res=$kpireporte->selec_certificador_reporte($sess_codpais,$tipokpi);
	$oficinacontrato_res=$kpireporte->selec_oficinacontrato_reporte($sess_codpais,$tipokpi);
	
	
	$programa_res=$kpireporte->selec_programa_reporte($sess_codpais,$tipokpi);
    include("../vista/kpireporte/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='reporte_cer'){
	//**********************************
	// mostrar index de calendario pais_res
	//**********************************
	$tipokpi='cer';
	$pais_res=$kpireporte->selec_certificador_reporte($sess_codpais,$tipokpi);
	$oficina_res=$kpireporte->selec_oficina_reporte($sess_codpais,$tipokpi);
	$programa_res=$kpireporte->selec_programa_reporte($sess_codpais,$tipokpi);
    include("../vista/kpireporte/index_cer.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_kpireporte'){

	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$fechai="";
	$fechaf="";
	$oficina="";
	$certificador="";
	$oficinacontrato="";
	$certificadorname="";
	$certificador="";
	$anio="";
	$aniocer="";
	$fechaevali="";
	$fechaevalf="";
	$auditor = "";
	$tipokpi = "";
	$pais = "";
	$codprograma = "";
	
	if(!empty($_POST['auditor']))
		$auditor = $_POST['auditor'];
	if(!empty($_POST['tipokpi']))
		$tipokpi = $_POST['tipokpi'];
	if(!empty($_POST['pais']))
		$pais = $_POST['pais'];
	
	
	if(!empty($_POST['certificadorname']))
		$certificadorname = $_POST['certificadorname'];
	
	
	if(!empty($_POST['certificador']))
		$certificador=$_POST['certificador'];
	
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);

	if(!empty($_POST['fechaevali']))
		$fechaevali = formatdatedos($_POST['fechaevali']);
	if(!empty($_POST['fechaevalf']))
		$fechaevalf = formatdatedos($_POST['fechaevalf']);
	
	//$codprograma = $_POST['codprograma'];
	if(!empty($_POST['codprograma']) and is_array($_POST['codprograma']))
			$codprograma = implode(",",$_POST['codprograma']);
	elseif(!empty($_POST['codprograma']))	
		$codprograma = $_POST['codprograma'];
	
	if(!empty($_POST['oficina']))
		$oficina = $_POST['oficina'];
	if(!empty($_POST['oficinacontrato']))
		$oficinacontrato = $_POST['oficinacontrato'];
	if(!empty($_POST['anio']))
		$anio = $_POST['anio'];
	if(!empty($_POST['aniocer']))
		$aniocer = $_POST['aniocer'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" pais,auditor ";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery =" and ifnull(certificadorname,'')!='' " ;
	
	if($codprograma=='' and $oficina=="" and $certificador=="" and $pais=="" and $oficinacontrato=="" and $auditor=='') 
		$searchQuery.= " and 1=2 ";
	
	## Total number of records without filtering
	$data_maxOF=$kpireporte->selec_total_kpireporte_cert($sess_codpais,$tipokpi,$searchQuery);
	$totalRecords = $data_maxOF['total'];

	if($codprograma!='') $searchQuery.= " and kpi_importar.codprograma in ($codprograma) ";
	if($auditor!='') $searchQuery.= " and (fullauditor like '%".$auditor."%' )";
	if($certificadorname!='') $searchQuery.= " and (certificadorname like '%".$certificadorname."%' )";
	if($pais!='') $searchQuery.= " and pais = '$pais' ";
	if($certificador!='') $searchQuery.= " and certificador = '$certificador' ";
	if($oficinacontrato!='') $searchQuery.= " and oficinacontrato = '$oficinacontrato' ";
	if($oficina!='') $searchQuery.= " and oficina = '$oficina' ";
	if($anio!='') $searchQuery.= " and year(fecha)=$anio";
	if($aniocer!='') $searchQuery.= " and year(fechaevaluacion)=$aniocer";
	
	if($fechai!='') $searchQuery.= " and to_days(fecha)>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(fecha) <= to_days('$fechaf')";
	
	if($fechaevali!='') $searchQuery.= " and to_days(fechaevaluacion)>= to_days('$fechaevali')";
	if($fechaevalf!='') $searchQuery.= " and to_days(fechaevaluacion) <= to_days('$fechaevalf')";
	
	
	## Total number of record with filtering
	$data_maxOF2=$kpireporte->selec_total_kpireporte_cert($sess_codpais,$tipokpi,$searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$kpireporte->select_kpireporte($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais,$tipokpi);
	
	$data_accion=$kpireporte->select_kpiaccion($tipokpi,$sess_codpais);
	if(!empty($data_accion)){
		foreach($data_accion as $row){
			$arrayPraEval[$row['codaccion']]=$row['maximo']."*".$row['minimo']."*".$row['valor'];
		}
	}
	
	//print_r($data_OF);
	$data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$promedio=$row['promedio'];
			$valorItem="";
			if(!empty($arrayPraEval)){
				foreach($arrayPraEval as $scorVa){
					$arraSco=explode("*",$scorVa);
					if($promedio<=$arraSco[0] and $promedio>=$arraSco[1]){
						$valorItem=$arraSco[2];
						break;
					}
				}
			}
			// valorItem
			if($row['auditor']!='')
				$userd=$row['auditor'];
			else if($row['usercertificador']!='')
				$userd=$row['usercertificador'];
			$edita="<button type='button' id='estproy_". $userd ."_".$row['oficina']."'  class='btn  btn_ediKpireporte'><i class='fas fa-chart-pie'></i> </button>";
			
		   $nombreauditor=json_encode(($row['nombreauditor']),JSON_UNESCAPED_UNICODE);
		   $data[] = array( 
			   "oficina"=>str_replace('"','',json_encode($row['oficina'],JSON_UNESCAPED_UNICODE)),
			   "nombreauditor"=>str_replace('"','',$nombreauditor),
			   "certificadorname"=>str_replace('"','',$row['certificadorname']),
			   "total"=>$row['total'],
			   "stiempo"=>$row['stiempo'],
			   "scalidad"=>$row['scalidad'],
			   "svolumen"=>$row['svolumen'],
			   "sgestion"=>$row['sgestion'],
			   "sproc"=>$row['sproc'],
			   "promedio"=>$row['promedio'],
			   "auditor"=>$row['auditor'],
			   "usercertificador"=>$row['usercertificador'],
			   "valorItem"=>$valorItem,
			   "edita"=>$edita,
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='editkpireporte'){
	$codindicador="";
	if(!empty($_POST['codindicador']))
		$data_res=$kpireporte->selec_one_kpireporte($_POST['codindicador']);

	$data_categ=$kpireporte->selec_categoria($sess_codpais);
    include("../vista/kpireporte/frm_detalle.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='grafReporte'){
	
	$fechai="";
	$fechaf="";
	
	$auditor = "";
	$oficina = "";
	$certificador = "";
	$oficinacontrato = "";
	$anio = "";
	$tipokpi = "";
	$pais = "";
	
	if(!empty($_POST['auditor']))
		$auditor = $_POST['auditor'];
	if(!empty($_POST['oficina']))
		$oficina = $_POST['oficina'];
	if(!empty($_POST['oficinacontrato']))
		$oficinacontrato = $_POST['oficinacontrato'];
	if(!empty($_POST['certificador']))
		$certificador = $_POST['certificador'];
	if(!empty($_POST['anio']))
		$anio = $_POST['anio'];
	if(!empty($_POST['tipokpi']))
		$tipokpi = $_POST['tipokpi'];
	if(!empty($_POST['pais']))
		$pais = $_POST['pais'];
	
	
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	
	//$codprograma = $_POST['codprograma'];
	if(!empty($_POST['codprograma']))
	$codprograma = implode(",",$_POST['codprograma']);

	
	$data_accion=$kpireporte->select_kpiaccion($tipokpi,$sess_codpais);
	foreach($data_accion as $row){
		$arrayPraEval[$row['codaccion']]=$row['maximo']."*".$row['minimo']."*".$row['valor'];
	}
	
	//*****************************************************************************
	//  grafico comparando por paises
	//*****************************************************************************
	$tmppais="";
	$tmpscorpais="";
	if($tipokpi=='aud'){
		$data_grafpais=$kpireporte->select_kpigraf_pais($codprograma,$fechai,$fechaf,$auditor,$pais,$oficina,$oficinacontrato,$certificador,$anio,$sess_codpais);
	}else if($tipokpi=='cer'){	
		$data_grafpais=$kpireporte->select_kpigraf_pais_cer($codprograma,$fechai,$fechaf,$auditor,$pais,$oficina,$anio,$sess_codpais);
	}

	if(!empty($data_grafpais)){
		foreach($data_grafpais as $row) {
			if($tmppais=='') 
				  $tmppais="'".utf8_encode($row['oficina'])."'";
			else $tmppais.=",'".utf8_encode($row['oficina'])."'";

			if($tmpscorpais=='') $tmpscorpais=$row['promedio'];
			else $tmpscorpais.=",".$row['promedio'];		
		 }
	}
	
	//*****************************************************************************
	//  grafico comparando por meses un pais
	//*****************************************************************************
	
	$tmpmes="";
	$tmpscormes="";
	
	if($tipokpi=='aud'){
		$data_grafpais_mes=$kpireporte->select_kpigraf_pais_mes($codprograma,$pais,$auditor,$anio,$oficina,$oficinacontrato,$certificador,$sess_codpais);
	}else if($tipokpi=='cer'){	
		$data_grafpais_mes=$kpireporte->select_kpigraf_pais_mes_cer($codprograma,$pais,$auditor,$anio,$oficina,$sess_codpais);
	}
	
	
	if(!empty($data_grafpais_mes)){
		foreach($data_grafpais_mes as $row) {
		 
			if($tmpmes=='')  $tmpmes="'".namemes($row['mes'])."'";
			else $tmpmes.=",'".namemes($row['mes'])."'";

			if($tmpscormes=='') $tmpscormes=$row['promedio'];
			else $tmpscormes.=",".$row['promedio'];
		 }	
	}
	 /***********************************************
	 obtener datos graficos individuales- 11.10.21
	 ************************************************/
	$sqlDet=" ";
	// if($codprograma!='') $sqlDet.= " and kpi_importar.codprograma=$codprograma ";
	if($codprograma!='') $sqlDet.= " and kpi_importar.codprograma in ($codprograma) ";
	if($auditor!='') $sqlDet.= " and (fullauditor like '%".$auditor."%' )";
	if($pais!='') $sqlDet.= " and pais = '$pais' ";
	if($oficinacontrato!='') $sqlDet.= " and oficinacontrato = '$oficinacontrato' ";
	if($oficina!='') $sqlDet.= " and oficinacontrato = '$oficina' ";
	if($certificador!='') $sqlDet.= " and certificador = '$certificador' ";
	if($anio!='') $sqlDet.= " and year(fecha)=$anio";
	if($fechai!='') $sqlDet.= " and to_days(fecha)>= to_days('$fechai')";
	if($fechaf!='') $sqlDet.= " and to_days(fecha) <= to_days('$fechaf')";
	 
	if($tipokpi=='aud'){
		$camponame="fullauditor";
	}else if($tipokpi=='cer'){	
		$camponame="certificadorname";
	}
	 
	 // para cuadro de tiempo
	// **********************************************************
	$campo="stiempo";
	$data_grafDetalle=$kpireporte->select_kpigrafDetalle($campo,$sqlDet,$sess_codpais,$camponame);
	 
	$tmpTiauditor="";
	$tmptiempo="";	
		
	$numrowsf=0;	
	if(!empty($data_grafDetalle)){
		foreach($data_grafDetalle as $row) {
			$numrowsf++;
			if($tmpTiauditor=='') $tmpTiauditor="'".($row['fullauditor'])."'";
			else $tmpTiauditor.=",'".($row['fullauditor'])."'";
		
			if($tmptiempo=='') $tmptiempo=$row['valor'];
			else $tmptiempo.=",".$row['valor'];
		}	
	}

	// **********************************************************
	// para cuadro de tmpcalidad
	// **********************************************************
	$campo="scalidad";
	$data_grafDetalle=$kpireporte->select_kpigrafDetalle($campo,$sqlDet,$sess_codpais,$camponame);
	
	$tmpCaauditor="";
	$tmpcalidad="";	
	
	if(!empty($data_grafDetalle)){
		foreach($data_grafDetalle as$row) {	
			if($tmpCaauditor=='') $tmpCaauditor="'".($row['fullauditor'])."'";
			else $tmpCaauditor.=",'".($row['fullauditor'])."'";

			if($tmpcalidad=='') $tmpcalidad=$row['valor'];
			else $tmpcalidad.=",".$row['valor'];
		}	
	}

	// **********************************************************
	// para cuadro de tmpproc
	// **********************************************************
	$campo="sproc";
	$data_grafDetalle=$kpireporte->select_kpigrafDetalle($campo,$sqlDet,$sess_codpais,$camponame);
		
	$tmpPrauditor="";
	$tmpproc="";	
	
	if(!empty($data_grafDetalle)){
		foreach($data_grafDetalle as$row){	
			if($tmpPrauditor=='') $tmpPrauditor="'".($row['fullauditor'])."'";
			else $tmpPrauditor.=",'".($row['fullauditor'])."'";

			if($tmpproc=='') $tmpproc=$row['valor'];
			else $tmpproc.=",".$row['valor'];
		}	
	}
	
	// **********************************************************
	// para cuadro de tmpvolumen
	// **********************************************************
	$campo="svolumen";
	$data_grafDetalle=$kpireporte->select_kpigrafDetalle($campo,$sqlDet,$sess_codpais,$camponame);
		
	$tmpVolauditor="";
	$tmpvolumen="";	
	
	if(!empty($data_grafDetalle)){
		foreach($data_grafDetalle as$row){	
			if($tmpVolauditor=='') $tmpVolauditor="'".($row['fullauditor'])."'";
			else $tmpVolauditor.=",'".($row['fullauditor'])."'";

			if($tmpvolumen=='') $tmpvolumen=$row['valor'];
			else $tmpvolumen.=",".$row['valor'];
		}	
	}
	
	// **********************************************************
	// para cuadro de tmpgestion
	// **********************************************************
	$campo="sgestion";
	$data_grafDetalle=$kpireporte->select_kpigrafDetalle($campo,$sqlDet,$sess_codpais,$camponame);
		
	$tmpGeauditor="";
	$tmpgestion="";	
	
	if(!empty($data_grafDetalle)){
		foreach($data_grafDetalle as$row){	
			if($tmpGeauditor=='') $tmpGeauditor="'".($row['fullauditor'])."'";
			else $tmpGeauditor.=",'".($row['fullauditor'])."'";

			if($tmpgestion=='') $tmpgestion=$row['valor'];
			else $tmpgestion.=",".$row['valor'];
		}	
	}
	
	
	// **********************************************************
	// para cuadro de tmpPromauditor
	// **********************************************************
	if($tipokpi=='aud'){
		$campo="sfinal";
		//$campo="(stiempo+scalidad+sproc)/3";
	}else if($tipokpi=='cer'){	
		$campo="(stiempo+scalidad+svolumen+sgestion)/4";
	}
	
	$data_grafDetalle=$kpireporte->select_kpigrafDetalle($campo,$sqlDet,$sess_codpais,$camponame);
	
	$tmppromedio="";
	$tmpPromauditor="";	
		
	if(!empty($data_grafDetalle)){	
		foreach($data_grafDetalle as$row){	
			if($tmpPromauditor=='') $tmpPromauditor="'".($row['fullauditor'])."'";
			else $tmpPromauditor.=",'".($row['fullauditor'])."'";

			if($tmppromedio=='') $tmppromedio=$row['valor'];
			else $tmppromedio.=",".$row['valor'];
		 }	
	}
	//*****************************************************************************
	//  fin graficos 
	//*****************************************************************************	
	
	
	$width=1300;
	if($numrowsf>35) $width=9500;
	else if($numrowsf>25) $width=4500;
	else if($numrowsf>20) $width=3500;
	else if($numrowsf>15) $width=2200;
	else if($numrowsf>10) $width=1800;
	
    include("../vista/kpireporte/frm_grafico.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='xlsReporte'){
	## Read value
	$fechai="";
	$fechaf="";
	$auditor = "";
	$pais = "";
	$oficinacontrato = "";
	$certificador = "";
	$oficina = "";
	$anio = "";
	$tipokpi="";
	
	if(!empty($_POST['tipokpi']))
		$tipokpi = $_POST['tipokpi'];
	if(!empty($_POST['auditor']))
		$auditor = $_POST['auditor'];
	if(!empty($_POST['pais']))
		$pais = $_POST['pais'];
	if(!empty($_POST['oficinacontrato']))
		$oficinacontrato = $_POST['oficinacontrato'];
	if(!empty($_POST['oficina']))
		$oficina = $_POST['oficina'];
	if(!empty($_POST['certificador']))
		$certificador = $_POST['certificador'];
	if(!empty($_POST['anio']))
		$anio = $_POST['anio'];
	
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	
	//$codprograma = $_POST['codprograma'];
	if(!empty($_POST['codprograma']))
			$codprograma = implode(",",$_POST['codprograma']);

	
	
	$data_accion=$kpireporte->select_kpiaccion($tipokpi,$sess_codpais);
	foreach($data_accion as $row){
		$arrayPraEval[$row['codaccion']]=$row['maximo']."*".$row['minimo']."*".$row['valor'];
	}
	
	$searchQuery =" and ifnull(certificadorname,'')!='' " ;
	
	if($codprograma=='' and $pais=="" and $certificador=="" and $oficina=="" and $oficinacontrato=="" and $auditor=='') 
		$searchQuery.= " and 1=2 ";
	
	
	if($codprograma!='') $searchQuery.= " and kpi_importar.codprograma in ($codprograma) ";
	if($auditor!='') $searchQuery.= " and (fullauditor like '%".$auditor."%' )";
	if($pais!='') $searchQuery.= " and pais = '$pais' ";
	if($oficinacontrato!='') $searchQuery.= " and oficinacontrato = '$oficinacontrato' ";
	if($oficina!='') $searchQuery.= " and oficina = '$oficina' ";
	if($certificador!='') $searchQuery.= " and certificador = '$certificador' ";
	if($anio!='') $searchQuery.= " and year(fecha)=$anio";
	if($fechai!='') $searchQuery.= " and to_days(fecha)>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(fecha) <= to_days('$fechaf')";
	
	$columnName=" pais,auditor";
	$columnSortOrder=" asc ";
	$row=0;
	$rowperpage=100000;
	$data_OF=$kpireporte->select_kpireporte($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais,$tipokpi);
	//print_r($data_OF);
	include("../vista/kpireporte/data_exporta_cer.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='xlsReporteDet'){
	## Read value
	$fechai="";
	$fechaf="";
	$auditor = "";
	$pais = "";
	$oficinacontrato = "";
	$certificador = "";
	$oficina = "";
	$anio = "";
	$tipokpi="";
	
	if(!empty($_POST['tipokpi']))
		$tipokpi = $_POST['tipokpi'];
	if(!empty($_POST['auditor']))
		$auditor = $_POST['auditor'];
	if(!empty($_POST['pais']))
		$pais = $_POST['pais'];
	if(!empty($_POST['oficinacontrato']))
		$oficinacontrato = $_POST['oficinacontrato'];
	if(!empty($_POST['oficina']))
		$oficina = $_POST['oficina'];
	if(!empty($_POST['certificador']))
		$certificador = $_POST['certificador'];
	if(!empty($_POST['anio']))
		$anio = $_POST['anio'];
	
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	
	//$codprograma = $_POST['codprograma'];
	if(!empty($_POST['codprograma']))
			$codprograma = implode(",",$_POST['codprograma']);

	
	
	$data_accion=$kpireporte->select_kpiaccion($tipokpi,$sess_codpais);
	foreach($data_accion as $row){
		$arrayPraEval[$row['codaccion']]=$row['maximo']."*".$row['minimo']."*".$row['valor'];
	}
	
	$searchQuery =" " ;
	
	if($codprograma=='' and $pais=="" and $certificador=="" and $oficina=="" and $oficinacontrato=="" and $auditor=='') 
		$searchQuery.= " and 1=2 ";
	
	
	if($codprograma!='') $searchQuery.= " and kpi_importar.codprograma in ($codprograma) ";
	if($auditor!='') $searchQuery.= " and (fullauditor like '%".$auditor."%' )";
	if($pais!='') $searchQuery.= " and pais = '$pais' ";
	if($oficinacontrato!='') $searchQuery.= " and oficinacontrato = '$oficinacontrato' ";
	if($oficina!='') $searchQuery.= " and oficina = '$oficina' ";
	if($certificador!='') $searchQuery.= " and certificador = '$certificador' ";
	if($anio!='') $searchQuery.= " and year(fecha)=$anio";
	if($fechai!='') $searchQuery.= " and to_days(fecha)>= to_days('$fechai')";
	if($fechaf!='') $searchQuery.= " and to_days(fecha) <= to_days('$fechaf')";
	
	$columnName=" pais,auditor";
	$columnSortOrder=" asc ";
	$row=0;
	$rowperpage=100000;
	$data_OF=$kpireporte->select_kpireporte_det($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$sess_codpais,$tipokpi);
	//print_r($data_OF);
	include("../vista/kpireporte/data_exporta_det.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='grafOneAuditor'){
	
	$fechai="";
	$fechaf="";
	$auditor = $_POST['auditor'];
	$pais = $_POST['pais'];
	$oficina = $_POST['oficina'];
	$anio = $_POST['anio'];
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	if(!empty($_POST['fechaf']))
		$fechaf = formatdatedos($_POST['fechaf']);
	//$codprograma = $_POST['codprograma']; dataAuditor
	if(!empty($_POST['codprograma']))
			$codprograma = implode(",",$_POST['codprograma']);
	if(!empty($_POST['oficinacontrato']))
			$oficinacontrato = $_POST['oficinacontrato'];

	$tipokpi = $_POST['tipokpi'];
	
	$dataAccion=$kpireporte->select_kpiaccion($tipokpi,$sess_codpais);
	foreach($dataAccion as $row) {
		 $arrayPraEval[$row['codaccion']]=$row['maximo']."*".$row['minimo']."*".$row['valor'];
	} 
	
	$dataAuditor=$kpireporte->select_kpigraf_oneAudit($tipokpi,$auditor,$codprograma,$fechai,$fechaf,$oficina,$anio,$pais,$oficinacontrato);
	
	$data_=$kpireporte->select_kpigraf_oneAuditMes($tipokpi,$auditor,$codprograma,$fechai,$fechaf,$oficina,$anio,$pais,$oficinacontrato);
	
	$tmpactividad="";
    $tmptiempo="";
	$tmpcalidad="";
	$tmpproc="";
	$tmpgestion="";
	$tmpvol="";
	$tmppromedio="";
	
	if(!empty($data_)){
		foreach($data_ as $row) {
			if($tmpactividad=='') $tmpactividad="'".namemes($row['mes'])."-".$row['anio']."'";
			else $tmpactividad.=",'".namemes($row['mes'])."-".$row['anio']."'";
			
			if($tmptiempo=='') $tmptiempo=$row['stiempo'];
			else $tmptiempo.=",".$row['stiempo'];
			
			if($tmpcalidad=='') $tmpcalidad=$row['scalidad'];
			else $tmpcalidad.=",".$row['scalidad'];
			
			if($tmpproc=='') $tmpproc=$row['sproc'];
			else $tmpproc.=",".$row['sproc'];
			
			if($tmpvol=='') $tmpvol=$row['svolumen'];
			else $tmpvol.=",".$row['svolumen'];
			
			if($tmpgestion=='') $tmpgestion=$row['sgestion'];
			else $tmpgestion.=",".$row['sgestion'];
			
			if($tmppromedio=='') $tmppromedio=$row['promedio'];
			else $tmppromedio.=",".$row['promedio'];
		}
	}
	
	if($tipokpi=='aud')
		include("../vista/kpireporte/frm_detalle.php");
	else if($tipokpi=='cer')
		include("../vista/kpireporte/frm_detalle_cer.php");
	
}



?>
