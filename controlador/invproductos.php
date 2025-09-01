<?php
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");
include("../modelo/inv_producto_modelo.php");
include("../modelo/prg_auditor_modelo.php");

$invproducto=new inv_producto_model();
$auditor=new prg_auditor_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$iselimina=0;
if($sess_codrol==32)
	$iselimina=1;

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

$pathFile = 'archivos/inventarioFile/'; // upload directory
$pathFileDev = 'archivos/inventarioFileDev/'; // upload directory
$valid_extensions = array('jpg','gif','png','jpeg','bmp'); // valid extensions



require_once "../lib/swift_required.php";
$transport = Swift_SmtpTransport::newInstance($server_mail, $puerto_mail);
//          ->setUsername($user_mail)
//          ->setPassword($clave_mail)
$mailer = Swift_Mailer::newInstance($transport); 
	
 //***********************************************************
 // 1. ingresos
 //***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	if(!empty($_POST['valor1']))
		$tipo=$_POST['valor1'];
	if($tipo=='a')
		$tipodsc='Consumibles';
	else if($tipo=='i')
		$tipodsc='Productos';
	else if($tipo=='c')
		$tipodsc='Celulares';
	
	
    include("../vista/invproductos/index.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_prod'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	
	$tipo="";
	if(!empty($_POST['tipo']))
		$tipo=$_POST['tipo'];
	
	$tipo_res=$invproducto->selec_tipodsc($tipo);
	
	if($tipo=='i')
		include("../vista/invproductos/index_prod.php");
	else if($tipo=='c')
		include("../vista/invproductos/index_prod_cel.php");
	else
		include("../vista/invproductos/index_prod_adm.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_producto'){

	

	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$codtipo = $_POST['codtipo'];
	$clase = $_POST['clase'];
	$asignado = $_POST['asignado'];
	$tipo = $_POST['tipo'];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" producto";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and ifnull(vista.tipo,'')!='s' ";

	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_producto($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and  ( producto like '%$descripcion%' or modelo like '%$descripcion%' or modelo like '%$descripcion%' or serief like '%$descripcion%' or numero like '%$descripcion%' or imei like '%$descripcion%')";
	
	if(!empty($codtipo))
 		$searchQuery.=" and  inv_producto.codtipo=$codtipo ";
	
	if(!empty($tipo))
		$searchQuery.=" and  inv_tipo.categoria='$tipo' ";
	
	if(!empty($clase))
		$searchQuery.=" and  inv_producto.clase='$clase' ";

	if(!empty($asignado) and $asignado=='NO')
		$searchQuery.=" and  vista.usuariodestino is null ";
	
	if(!empty($asignado) and $asignado=='SI')
		$searchQuery.="  and  vista.usuariodestino is not null ";
	
	
	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_producto($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_producto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codproducto'];
			
			$edita="<button type='button' id='estproy_". $id ."_".$tipo."'  class='btn  btn_ediproducto'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."_".$tipo."'  class='btn  btn_eliproducto'><i class='fas fa-trash'></i> </button>";

			if($row['coddestino']!='' or $iselimina==0){
				// $elimina="";
			}
			
			$barcode="<img height=75 src='com/barcode.php?s=qrl&d=$row[parabarra]'>";
		
		   $data[] = array( 
			   "producto"=>str_replace('"','',json_encode($row['producto'],JSON_UNESCAPED_UNICODE)),
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			   "modelo"=>str_replace('"','',json_encode($row['modelo'],JSON_UNESCAPED_UNICODE)),
			  "clase"=>$row['clase'],
			   "tipodsc"=>$row['tipodsc'],
			    "numero"=>$row['numero'],
				"usuariodestino"=>$row['usuariodestino'],
				"cargador"=>$row['cargadordsc'],
				"simcard"=>$row['simcarddsc'],
				"fechacompra"=>$row['fechacompraf'],
				"dias"=>$row['dias'],
				"imei"=>$row['imei'],
			   "serief"=>$row['serief'],
			   "fecha_ingreso"=>$row['fecha_ingreso'],
			   "marca"=>$row['marca'],
			   "codproducto"=>$id,
			   "barcode"=>$barcode,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editproducto'){
	
	$tipo="";
	if(!empty($_POST['tipo']))
		$tipo=$_POST['tipo'];
	

	$codproducto="";
	$data_tipo=$invproducto->selec_tipodsc($tipo);
	
	if($tipo=='c')
		$data_marca=$invproducto->selec_marca($flgcelular='1','0');
	else
		$data_marca=$invproducto->selec_marca('0','1');
	
	$data_empresa=$invproducto->selec_empresa($sess_codpais);
	$data_producto=$invproducto->select_producto_byadmin($sess_codpais);
	
	
	if(!empty($_POST['codproducto']) and $_POST['codproducto']!='-1'){
		$data_res=$invproducto->selec_one_producto($_POST['codproducto']);
	}
	
	if(!empty($_POST['coddetalle']) ){
		$coddetalle=$_POST['coddetalle'];
		$data_detra=$invproducto->selec_one_dettransacccion($coddetalle);
		$codproducto=$data_detra['codproducto'];
		$codtransaccion=$data_detra['codtransaccion'];
		$data_res=$invproducto->selec_one_producto($codproducto);
		$data_tra=$invproducto->selec_one_transacccion($codtransaccion);
		
	}
	
	

	$codtipo="";
	if(!empty($data_res['codtipo']))
		$codtipo=$data_res['codtipo'];
	else if(!empty($_POST['codtipo']))
		$codtipo=$_POST['codtipo'];

	if($tipo=='a' and $_POST['codproducto']=='0')
		include("../vista/invproductos/frm_detallemix.php");
	elseif($tipo=='a' and $_POST['codproducto']!='0')
		include("../vista/invproductos/frm_detallemix_one.php");
	elseif($tipo=='c'){
		include("../vista/invproductos/frm_detallecel.php");
	}else
		include("../vista/invproductos/frm_detalle.php");
	

}else if(!empty($_POST['accion']) and $_POST['accion']=='busc_tipo'){
	$codtipo=$_POST['codtipo'];
	$restipo=$invproducto->selec_one_tipoproducto($codtipo);
	if($restipo)
		echo $restipo['flgdato'];
	else
		echo "0";

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detProducto'){

	$producto="";
	$stock_min=""; 
	$codigo=""; 
	$imei=""; 
	$numero=""; 
	$periodo="";
	
	if(!empty($_POST['producto']))
		$producto=$_POST['producto'];
	
	if(!empty($_POST['stock_min']))
		$stock_min=$_POST['stock_min']; 
	
	if(!empty($_POST['codigo']))
		$codigo=$_POST['codigo']; 

	if(!empty($_POST['imei']))
		$imei=$_POST['imei']; 

	if(!empty($_POST['numero']))
		$numero=$_POST['numero']; 

	if(!empty($_POST['periodo']))
		$periodo=$_POST['periodo'];
	
	$estado=$_POST['estado']; 
	$codempresa=$_POST['codempresa']; 

	
	$simcard="";
	if(!empty($_POST['simcard']))
		$simcard="SI";
	
	$cargador="";
	if(!empty($_POST['cargador']))
		$cargador="SI";
	
	$so=$_POST['so'];
	$office=$_POST['office'];
	$dominio=$_POST['dominio'];
	$tamanio=$_POST['tamanio'];
	$codmarca=$_POST['codmarca'];
	$codtipo=$_POST['codtipo'];
	$modelo=$_POST['modelo'];
	$serief=$_POST['serief'];
	$host=$_POST['host'];
	$hd1=$_POST['hd1'];
	$procesador=$_POST['procesador'];
	$ram=$_POST['ram'];
	
	$clase=$_POST['clase'];
	
	$proveedor=$_POST['proveedor'];
	$fechacompra="";
	$inigarantia="";
	$fingarantia="";
	$fechainicio="";
	
	if(!empty($_POST['costo']))
		$costo=$_POST['costo'];
	
	if(!empty($_POST['fechai']))
		$fechainicio=formatdatedos($_POST['fechai']);
	
	if(!empty($_POST['fechacompra']))
		$fechacompra=formatdatedos($_POST['fechacompra']);
	
	if(!empty($_POST['inigarantia']))
		$inigarantia=formatdatedos($_POST['inigarantia']);
	
	if(!empty($_POST['fingarantia']))
		$fingarantia=formatdatedos($_POST['fingarantia']);


	$antivirus="";
	if(!empty($_POST['antivirus']))
		$antivirus="SI";
	
	$onedrive="";
	if(!empty($_POST['onedrive']))
		$onedrive="SI";


	$nrodocumento=$_POST['nrodocumento'];
	$moneda=$_POST['moneda'];
	
	
	if(empty($_POST['codproducto'])){
		$codproducto=$invproducto->insert_producto($numero,$codempresa,$codigo,$cargador,$simcard,$estado,$imei,$stock_min,$producto,$onedrive,$antivirus,$clase,$so,$office,$dominio,$tamanio,$codmarca,$codtipo,$modelo,$serief,$ram,$procesador,$host,$hd1,$fechainicio,$periodo,$proveedor,$fechacompra,$nrodocumento,$moneda,$costo,$inigarantia,$fingarantia,$sess_codpais,$usuario_name,$ip);
	
		// transaccion
		if(!empty($_POST['cantidad'])){
			$cantidad=$_POST['cantidad'];
	
			$codarea="";
			$codsede="";
			$codmotivo=10;
			$coddestino="";
			$descripcion="Compra";
			$ubicacion="";
			$agencia="";
			$precio=$costo;
			
			$codtransaccion=$invproducto->insert_transaccion($ubicacion,$codarea,$codsede,$codmotivo,$coddestino,$descripcion,$fechacompra,$sess_codpais,$usuario_name,$ip);
			$coddetalle=$invproducto->insert_dettransaccion($cantidad,$codtransaccion,$codproducto,$descripcion,$fechacompra,$numero,$agencia,$moneda,$precio,$usuario_name,$ip);
			// transaccion
		}
	}else{
		$codproducto=$_POST['codproducto']; // id
		$invproducto->update_producto($numero,$codempresa,$codigo,$cargador,$simcard,$estado,$imei,$stock_min,$producto,$onedrive,$antivirus,$clase,$codproducto,$so,$office,$dominio,$tamanio,$codmarca,$codtipo,$modelo,$serief,$ram,$procesador,$host,$hd1,$fechainicio,$periodo,$proveedor,$fechacompra,$nrodocumento,$moneda,$costo,$inigarantia,$fingarantia,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";


}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detProducto2'){
    // proceso update a la base de datos usuarios
	
	$codempresa=$_POST['codempresa']; 
	$clase=$_POST['clase'];
	$proveedor=$_POST['proveedor'];
	$fechacompra="";
	if(!empty($_POST['fechacompra']))
		$fechacompra=formatdatedos($_POST['fechacompra']);
	$nrodocumento=$_POST['nrodocumento'];
	
	$codarea="";
	$codsede="";
	$codmotivo=10;
	$coddestino="";
	$descripcion="Compra";
	$codtransaccion=$invproducto->insert_transaccion2($nrodocumento,$codempresa,$proveedor,$codarea,$codsede,$codmotivo,$coddestino,$descripcion,$fechacompra,$sess_codpais,$usuario_name,$ip);
	
	// detalle
	for($i=1;$i<=20;$i++){
		$stock_min=$_POST['stock_min_'.$i]; 
		$imei=$_POST['imei_'.$i]; 
		$estado=$_POST['estado_'.$i]; 
		
		$simcard="";
		if(!empty($_POST['simcard_'.$i]))
			$simcard="SI";
		
		$cargador="";
		if(!empty($_POST['cargador_'.$i]))
			$cargador="SI";
		
		$codmarca=$_POST['codmarca_'.$i];
		$modelo=$_POST['modelo_'.$i];
		
		if(!empty($_POST['costo_'.$i]))
			$costo=$_POST['costo_'.$i];

		$moneda=$_POST['moneda_'.$i];
		$numtelefono="";
		$agencia="";
		
		//$codproducto=$invproducto->insert_producto($codempresa,$codigo,$cargador,$simcard,$estado,$imei,$stock_min,$producto,$onedrive,$antivirus,$clase,$so,$office,$dominio,$tamanio,$codmarca,$codtipo,$modelo,$serief,$ram,$procesador,$host,$hd1,$fechainicio,$periodo,$proveedor,$fechacompra,$nrodocumento,$moneda,$costo,$inigarantia,$fingarantia,$sess_codpais,$usuario_name,$ip);
		
			// transaccion
		if(!empty($_POST['cantidad_'.$i]) and !empty($_POST['codproducto_'.$i])){
			$cantidad=$_POST['cantidad_'.$i];
			$codproducto=$_POST['codproducto_'.$i];
				
			$coddetalle=$invproducto->insert_dettransaccion($cantidad,$codtransaccion,$codproducto,'',$fechacompra,$numtelefono,$agencia,$moneda,$costo,$usuario_name,$ip);
			// transaccion
		}
	}	
	
	 echo "Se actualizo el registro";

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detProducto3'){
    // proceso update a la base de datos usuarios
	
	$codtransaccion=$_POST['codtransaccion']; 
	$coddetalle=$_POST['coddetalle']; 
	$codproducto=$_POST['codproducto']; 
	$codempresa=$_POST['codempresa']; 
	$clase=$_POST['clase'];
	$proveedor=$_POST['proveedor'];
	$fechacompra="";
	if(!empty($_POST['fechacompra']))
		$fechacompra=formatdatedos($_POST['fechacompra']);
	$nrodocumento=$_POST['nrodocumento'];
	
	$codtransaccion=$invproducto->update_transaccion2($codtransaccion,$nrodocumento,$codempresa,$proveedor,$fechacompra,$sess_codpais,$usuario_name,$ip);
		
	$cantidad=$_POST['cantidad'];
	$costo=$_POST['costo'];
	
	$coddetalle=$invproducto->update_dettransaccion2($cantidad,$coddetalle,$codproducto,$fechacompra,$costo,$usuario_name,$ip);
	
	 echo "Se actualizo el registro";



}else if(!empty($_POST['accion']) and $_POST['accion']=='delProducto'){
    // delete a la base de datos usuarios
	$codproducto=$_POST['codproducto'];

	$invproducto->delete_producto($codproducto);
    echo "Se elimino el registro.";	

}else if(!empty($_POST['accion']) and $_POST['accion']=='busc_serie'){
 	$serief=$_POST['serief'];
	$res_data=$invproducto->buscarserie_producto($serief);
	if(!empty($res_data['serief']))
		echo "Se encuentra ya registrado esta serie.";
	else
		echo ""; 

}else if(!empty($_POST['accion']) and $_POST['accion']=='expProducto'){
    // delete a la base de datos usuarios
	$tipo=$_POST['tipo'];
	$asignado=$_POST['asignado'];
	$codtipo=$_POST['codtipo'];
	$clase=$_POST['clase'];
	$descripcion=$_POST['descripcion'];
	$simple=$_POST['simple'];
	
	$searchQuery = " and ifnull(vista.tipo,'')!='s' ";
	
	if(!empty($descripcion))
		$searchQuery.=" and  ( producto like '%$descripcion%' or modelo like '%$descripcion%' or modelo like '%$descripcion%' or serief like '%$descripcion%' or numero like '%$descripcion%' or imei like '%$descripcion%')";
	
	if(!empty($codtipo))
 		$searchQuery.=" and  inv_producto.codtipo=$codtipo ";
	
	if(!empty($tipo))
		$searchQuery.=" and  inv_tipo.categoria='$tipo' ";
	
	if(!empty($clase))
		$searchQuery.=" and  inv_producto.clase='$clase' ";

	if(!empty($asignado) and $asignado=='NO')
		$searchQuery.=" and  vista.usuariodestino is null ";
	
	if(!empty($asignado) and $asignado=='SI')
		$searchQuery.="  and  vista.usuariodestino is not null ";
	
	
	
	

	## Fetch records
	$columnName=" inv_producto.fecha_ingreso ";
	$columnSortOrder=" desc ";
	$row=0;
	$rowperpage=10000;
	$data_OF=$invproducto->select_producto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
		if($tipo=='i')
		include("../vista/invproductos/exporta_producto.php");
		else
		include("../vista/invproductos/exporta_celular.php");



} else if(!empty($_POST['accion']) and $_POST['accion']=='expReporte') {//Agregado amena BOTON NUEVO   
	$tipo=$_POST['tipo'];
	$asignado=$_POST['asignado'];
	$codtipo=$_POST['codtipo'];
	$clase=$_POST['clase'];
	$descripcion=$_POST['descripcion'];
	$simple=$_POST['simple'];
	
	$searchQuery = " and ifnull(vista.tipo,'')!='s' ";
	
	if(!empty($descripcion))
		$searchQuery.=" and  ( producto like '%$descripcion%' or modelo like '%$descripcion%' or modelo like '%$descripcion%' or serief like '%$descripcion%' or numero like '%$descripcion%' or imei like '%$descripcion%')";
	
	if(!empty($codtipo))
 		$searchQuery.=" and  inv_producto.codtipo=$codtipo ";
	
	if(!empty($tipo))
		$searchQuery.=" and  inv_tipo.categoria='$tipo' ";
	
	if(!empty($clase))
		$searchQuery.=" and  inv_producto.clase='$clase' ";

	if(!empty($asignado) and $asignado=='NO')
		$searchQuery.=" and  vista.usuariodestino is null ";
	
	if(!empty($asignado) and $asignado=='SI')
		$searchQuery.="  and  vista.usuariodestino is not null ";
	
	

	## Fetch records
	$columnName=" inv_producto.fecha_ingreso ";
	$columnSortOrder=" desc ";
	$row=0;
	$rowperpage=10000;
	$data_OF=$invproducto->select_producto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
include("../vista/invproductos/exporta_reporte.php");
	

}else if(!empty($_POST['accion']) and $_POST['accion']=='expProducto_simple'){
    // delete a la base de datos usuarios
	$tipo=$_POST['tipo'];
	$asignado=$_POST['asignado'];
	$codtipo=$_POST['codtipo'];
	$clase=$_POST['clase'];
	$descripcion=$_POST['descripcion'];
	$simple=$_POST['simple'];
	
	$searchQuery = " and ifnull(vista.tipo,'')!='s' ";
	
	if(!empty($descripcion))
		$searchQuery.=" and  ( producto like '%$descripcion%' or modelo like '%$descripcion%' or modelo like '%$descripcion%' or serief like '%$descripcion%' or numero like '%$descripcion%' or imei like '%$descripcion%')";
	
	if(!empty($codtipo))
 		$searchQuery.=" and  inv_producto.codtipo=$codtipo ";
	
	if(!empty($tipo))
		$searchQuery.=" and  inv_tipo.categoria='$tipo' ";
	
	if(!empty($clase))
		$searchQuery.=" and  inv_producto.clase='$clase' ";

	if(!empty($asignado) and $asignado=='NO')
		$searchQuery.=" and  vista.usuariodestino is null ";
	
	if(!empty($asignado) and $asignado=='SI')
		$searchQuery.="  and  vista.usuariodestino is not null ";
	
	

	## Fetch records
	$columnName=" inv_producto.fecha_ingreso ";
	$columnSortOrder=" desc ";
	$row=0;
	$rowperpage=10000;
	$data_OF=$invproducto->select_producto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	include("../vista/invproductos/exporta_producto_simple.php");
	
		
	
 //***********************************************************
 // 2. Asignaciones
 //***********************************************************

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_asigna'){
	
	if(!empty($_POST['valor1']))
		$tipo=$_POST['valor1'];
	
	if($tipo=='i'){
		$asignarpro="Asignar de productos";
		$buscarasigna="Buscar Asignaciones";
	}else if($tipo=='a'){
		$asignarpro="Salida consumibles"; //se cambio esto amena020725
		$buscarasigna="Buscar salida de consumibles";//se cambio esto amena020725
	}else{
		$asignarpro="Asignacion de celulares";
		$buscarasigna="Buscar Asignacion de celulares";
	}
	
    include("../vista/invproductos/index_asigna.php");	
	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_transac'){
	//**********************************
	// mostrar index reporte
	//**********************************
	$tipo = $_POST['tipo'];
	
	$usuario_res=$invproducto->selec_usuarios($sess_codpais);
	$tipo_res=$invproducto->selec_tipodsc($tipo);
	
	if($tipo=='c')
		$data_marca=$invproducto->selec_marca($flgcelular='1','0');
	else
		$data_marca=$invproducto->selec_marca('0','1');
	

	
	$area_res=$invproducto->selec_areas();
	$sede_res=$invproducto->selec_sedes();
	
	
	if($tipo=='c')
		include("../vista/invproductos/index_transac_cel.php");	
	else if($tipo=='a')
		include("../vista/invproductos/index_transac_adm.php");	
	else
		include("../vista/invproductos/index_transac.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_datatransac'){
	

	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	$tipo = $_POST['tipo'];
	## Read value
	$descripcion = $_POST['descripcion'];
	$serie = $_POST['serie'];
	$codtipo = $_POST['codtipo'];
	$codusuario = $_POST['codusuario'];
	$clase = $_POST['clase'];
	$ubicacion = $_POST['ubicacion2'];
	
	$codarea = $_POST['codarea'];
	$codsede = $_POST['codsede'];
	
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" producto";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto categoria
	if($tipo=='a')
		$searchQuery = " and inv_motivo.tipo in ('z') and ifnull(d.fecharetiro,'')='' ";
	else
		$searchQuery = " and inv_motivo.tipo='x' and ifnull(d.fecharetiro,'')='' ";
	
	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_movimento($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and  ( producto like '%$descripcion%' or modelo like '%$descripcion%' or serief like '%$descripcion%' or imei like '%$descripcion%' or numtelefono like '%$descripcion%' or codigo like '%$descripcion%' or inv_producto.numero like '%$descripcion%'  )";
	
	if(!empty($serie))
		$searchQuery.=" and  (  serief = '$serie')";
	
	if(!empty($ubicacion))
		$searchQuery.=" and  (  ubicacion = '$ubicacion')";
	
	if(!empty($codtipo))
		$searchQuery.=" and  inv_producto.codtipo=$codtipo ";
	
	if(!empty($tipo))
		$searchQuery.=" and  inv_tipo.categoria='$tipo' ";
	
	if(!empty($codarea))
		$searchQuery.=" and  inv_area.codarea='$codarea' ";
	
	if(!empty($codsede))
		$searchQuery.=" and  inv_sede.codsede='$codsede' ";
	
	if(!empty($fechai))
		$searchQuery .= " and  to_days(t.fecha)=to_days('$fechai') ";




	$datechk="";	
	if(!empty($codusuario))
		$searchQuery.=" and  t.coddestino=$codusuario ";
	
	if(!empty($clase))
		$searchQuery.=" and  inv_producto.clase='$clase' ";

	
	
	
	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_movimento($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_movimento($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['llave'];
			$codproducto=$row['codproducto'];
			$codtransaccion=$row['codtransaccion'];
			$download="";
			
			if(!empty($codusuario) or !empty($serie))
				$datechk="<input type=checkbox name='ind_devol' value='$row[coddetalle]' id='ind_$row[coddetalle]'>";
			
			$imprimir="<button type='button' id='printproducto_". $id ."'  class='btn  btn_printproducto'><i class='fas fa-print'></i> </button>";
			$imprimir2="<button type='button' id='printproductosal_". $id ."'  class='btn  btn_printproducto'><i class='fas fa-print'></i> </button>";
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_asiginvproducto'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliinvproducto'><i class='fas fa-trash'></i> </button>";
			$desasigna="<button type='button' id='estproy_". $id ."'  class='btn  btn_desasiginvpro'><i class='fas fa-reply'></i>";
			$subir="<button type='button' id='estproy_". $id ."'  class='btn  btn_uplFileAsigna'><i class='fas fa-upload'></i> </button>";
			
			$observacion="";
			if($row['precio']!='')
				$observacion.="$row[moneda] $row[precio]";
			
			if($row['agencia']!='')
				$observacion.="<br>Agencia: $row[agencia]";
			
			if($row['area']!='')
				$observacion.="<br>Area: $row[area]";
			if($row['numtelefono']!='')
				$observacion.="<br>Telefono: $row[numtelefono]";
		
			if($row['fecharetirof']!='' ){
				$desasigna="";
				$edita="";
				$elimina="";
				$subir="";
			}
			
		
			if($row['archivo']!=''){
				$file=$pathFile.$row['archivo'];
				$download="<button type='button' id='$file'  class='btn  btn_verFileLista'><i class='fas fa-download'></i> </button>";
			
			}			

		   $data[] = array( 
			   "producto"=>str_replace('"','',json_encode($row['producto'],JSON_UNESCAPED_UNICODE)),
			   "procesador"=>str_replace('"','',json_encode($row['procesador'],JSON_UNESCAPED_UNICODE)),
			   "modelo"=>str_replace('"','',json_encode($row['modelo'],JSON_UNESCAPED_UNICODE)),
			   "usuariodestino"=>$row['usuariodestino'],
			   "desasigna"=>$desasigna,
			   "tipodsc"=>$row['tipodsc'],
			   "numero"=>$row['numero'],
			   "serie"=>$row['serief'],
			   "dias"=>$row['dias'],
			   "ubicacion"=>$row['ubicacion'],
			   "marca"=>$row['marca'],
			   "fecha"=>$row['fechaf'],
			   "imei"=>$row['imei'],
			    "numtelefono"=>$row['numerotel'],
			   "fecharetiro"=>$row['fecharetirof'],
			    "cargadordsc"=>$row['cargadordsc'],
				"simcarddsc"=>$row['simcarddsc'],
				"codigo"=>$row['codigo'],
				"umedida"=>$row['umedida'],
				"cantidad"=>$row['cantidad'],
				"sede"=>$row['sede'],
				"area"=>$row['area'],
			   "codproducto"=>$id,
			   "observacion"=>$observacion,
			   "subir"=>$subir,
			   "edita"=>$edita,
			   "datechk"=>$datechk,
			   "download"=>$download,
			   "imprimir"=>$imprimir,
			   "imprimir2"=>$imprimir2,
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



}else if(!empty($_POST['accion']) and $_POST['accion']=='asiginvproducto'){
	$flginformatica='';
	$tipo=$_POST['tipo'];
	if($tipo=='i' or $tipo=='a')
		$flginformatica='1';
	$data_usuario=$invproducto->selec_usuarios($sess_codpais);
	
	$data_area=$invproducto->selec_areas();
	$data_sede=$invproducto->selec_sedes();
	$data_motivo=$invproducto->selec_motivos("x",$flginformatica);
	
	$codtipo="";
	
	if(!empty($_POST['codtransaccion'])){
		$codtransaccion=$_POST['codtransaccion'];
		$data_tra=$invproducto->selec_one_transacccion($codtransaccion);
	}	
	
	if(!empty($_POST['coddetalle'])){
		$coddetalle=$_POST['coddetalle'];
		$data_detra=$invproducto->selec_one_dettransacccion($coddetalle);
	}
	
	if(!empty($_POST['codproducto'])){
		$codproducto=$_POST['codproducto'];
		$data_res=$invproducto->selec_one_producto($codproducto);
		$codtipo=$data_res['codtipo'];
		$fechproximo=$data_res['fechainiciof'];
		$data_stock=$invproducto->selec_stock($codproducto);
	}else{
		$data_pro=$invproducto->select_producto_noselect($sess_codpais,$tipo);
		$fechproximo="";
	}
	
	// fechproximo
	if($tipo=='a'){
		$data_con=$invproducto->select_consumible_noselect($sess_codpais);
		include("../vista/invproductos/frm_asignacion_con.php");
	}else
		include("../vista/invproductos/frm_asignacion.php");


}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_asigProducto'){
    
	$codproducto=$_POST['codproducto'];
	
	$codarea=$_POST['codarea'];
	$codsede=$_POST['codsede'];
	$codmotivo=$_POST['codmotivo'];
	
	$coddestino=$_POST['coddestino'];
	$descripcion=$_POST['descripcion'];
	$fecha=formatdatedos($_POST['fechai']);
	$fechainicio=formatdatedos($_POST['fecham']);

	$ubicacion=$_POST['ubicacion'];
	$numtelefono=$_POST['numtelefono'];
	$agencia=$_POST['agencia'];
	$moneda=$_POST['moneda'];
	$precio=$_POST['precio'];
	
	$flgactivo	='1'; // asigna se pone 1
	
	if(!empty($_POST['salida']))
		$cantidad=$_POST['salida'];
	else
		$cantidad=1;
	
	
	$invproducto->update_fecproxmant($fechainicio,$codproducto,$usuario_name,$ip);
	
	if(empty($_POST['codtransaccion']))
		$codtransaccion=$invproducto->insert_transaccion($ubicacion,$codarea,$codsede,$codmotivo,$coddestino,$descripcion,$fecha,$sess_codpais,$usuario_name,$ip);
	else{
		$codtransaccion=$_POST['codtransaccion']; // id
		$invproducto->update_transaccion($codtransaccion,$ubicacion,$codarea,$codsede,$codmotivo,$coddestino,$descripcion,$fecha,$usuario_name,$ip);
	}

	if(empty($_POST['coddetalle']))
		$coddetalle=$invproducto->insert_dettransaccion($cantidad,$codtransaccion,$codproducto,$descripcion,$fecha,$numtelefono,$agencia,$moneda,$precio,$usuario_name,$ip);
	else{
		$coddetalle=$_POST['coddetalle']; // id
		$invproducto->update_dettransaccion($coddetalle,$cantidad,$codtransaccion,$codproducto,$descripcion,$fecha,$numtelefono,$agencia,$moneda,$precio,$usuario_name,$ip);
	}	
	
	// aqui debemos ver si asigna otros equipos
	if(!empty($_POST['codproducto2']) and ( !empty($_POST['salida2']) or $tipo!='a')){
		$codproducto2=$_POST['codproducto2'];
		
		if(!empty($_POST['salida2']))
			$cantidad2=$_POST['salida2'];
		else
			$cantidad2=1;
		
		$coddetalle2=$invproducto->insert_dettransaccion($cantidad2,$codtransaccion,$codproducto2,$descripcion,$fecha,$numtelefono,$agencia,$moneda,$precio,$usuario_name,$ip);	
		$invproducto->update_fecproxmant($fechainicio,$codproducto2,$usuario_name,$ip);
	}
	
	if(!empty($_POST['codproducto3']) and (!empty($_POST['salida3']) or $tipo!='a') ){
		$codproducto3=$_POST['codproducto3'];
		
		
		if(!empty($_POST['salida3']))
			$cantidad3=$_POST['salida3'];
		else
			$cantidad3=1;
		
		$coddetalle3=$invproducto->insert_dettransaccion($cantidad3,$codtransaccion,$codproducto3,$descripcion,$fecha,$numtelefono,$agencia,$moneda,$precio,$usuario_name,$ip);	
		$invproducto->update_fecproxmant($fechainicio,$codproducto3,$usuario_name,$ip);
	}
	
	if(!empty($_POST['codproducto4']) and (!empty($_POST['salida4'])or $tipo!='a') ){
		$codproducto4=$_POST['codproducto4'];
		
		
		if(!empty($_POST['salida4']))
			$cantidad4=$_POST['salida4'];
		else
			$cantidad4=1;
		
		$coddetalle4=$invproducto->insert_dettransaccion($cantidad4,$codtransaccion,$codproducto4,$descripcion,$fecha,$numtelefono,$agencia,$moneda,$precio,$usuario_name,$ip);	
		$invproducto->update_fecproxmant($fechainicio,$codproducto4,$usuario_name,$ip);
	}
	
	// fin asignacion otros equipos
	
	
	//******************************************************
	// solo cuando se asigna a un usuario. se enviara correo
	//******************************************************
	if(!empty($_POST['coddestino']))
		$invproducto->update_transaccion_usuario($codtransaccion);
	
	$categoria="";
	
	if(empty($_POST['codtransaccion'])){
		$res_pro=$invproducto->selec_one_producto($codproducto);
		$codtipo=$res_pro['codtipo'];
		if($codtipo){
			$res_tipo=$invproducto->selec_one_tipo($codtipo);
			$categoria=$res_tipo['categoria'];
		}	
		
		$res_tra=$invproducto->selec_one_transacccion($codtransaccion);
		$res_dettra=$invproducto->selec_one_dettransacccion_bycod($codtransaccion);
		
		$asunto="Asignacion de equipo";
		if($categoria=='i'){
			$auditorMail="pvizcarra@controlunion.com";
			$body="Estimado Usuario $res_tra[usuariodestino]: <br><br>
			Se le ha asignado un equipo $res_pro[producto] con las siguientes caracteristicas:
			Marca: $res_pro[marca]<br>
			Modelo: $res_pro[modelo]<br>
			Serie: $res_pro[serief]<br>
			<br><br>
			Atentamente
			<br><br>
			CONTROL UNION PERU SAC
			";
		
		}else if($categoria=='a'){
			$auditorMail="kasato@controlunion.com";
		
			$body="Estimado Usuario $res_tra[usuariodestino]: <br><br>
			Se le ha asignado un equipo $res_pro[producto] con las siguientes caracteristicas:
			Marca: $res_pro[marca]<br>
			Modelo: $res_pro[modelo]<br>
			Imei: $res_pro[imei]<br>
			Numero : $res_dettra[numtelefono]<br>
			<br><br>
			Atentamente
			<br><br>
			Administracion de CONTROL UNION PERU SAC
			";
		}else{
			$auditorMail="pvizcarra@controlunion.com";
		
			$body="Estimado Usuario $res_tra[usuariodestino]: <br><br>
			Se le ha asignado un equipo $res_pro[producto] con las siguientes caracteristicas:
			Marca: $res_pro[marca]<br>
			Modelo: $res_pro[modelo]<br>
			Serie: $res_pro[serief]<br>
			<br><br>
			Atentamente
			<br><br>
			CONTROL UNION PERU SAC
			";
		}
		
		
		// enviar correo solo para ti
		if(!empty($auditorMail) and $tipo=='i'){
			$message = Swift_Message::newInstance($asunto)
				->setFrom(array($user_mail =>  $name_mail))
				->setTo(explode(",",$auditorMail))
				->setBody($body, 'text/html', 'iso-8859-2')
			;
			$numSent = $mailer->send($message);
			printf("Enviado: %d mensajes a $auditorMail<br>", $numSent);
		}
	}	
		
	echo "Se asigno el producto.";


}else if(!empty($_POST['accion']) and $_POST['accion']=='upload_asigna'){
	$codtransaccion=$_POST['codtransaccion'];
	$codproducto=$_POST['codproducto'];
	$coddetalle=$_POST['coddetalle'];
	$tipo=$_POST['tipo'];

	$data_res=$invproducto->selec_all_producto($codtransaccion);
	
	include("../vista/invproductos/frm_asignacion_upload.php");	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_upload_asigna'){

	$codtransaccion=$_POST['codtransaccion'];
	$codproducto=$_POST['codproducto'];
	$coddetalle=$_POST['coddetalle'];
	$tipo=$_POST['tipo'];
    
	// procesar el adjunto
	
	$file=uploadFile($_FILES,$pathFile,'archivo',$codtransaccion);
	if(substr($file, 0,5)!='Error' )
		$invproducto->update_fileTransaccion($codtransaccion,$file);
	
	// funcion para enviar correo
	//*****************************
	if(!empty($_POST['codtransaccion'])){
		
		$res_tra=$invproducto->selec_one_transacccion($codtransaccion);
		
		
	
		if($tipo=='i'){
			$asunto="Acta de entrega - Asignacion de equipo";
			$auditorMail="itsupport.peru@controlunion.com";
			
			$body="Estimado Usuario $res_tra[usuariodestino]: <br><br>
			Le informamos que se le ha asignado equipos de computo para facilitar sus tareas diarias.
			En adjunto le envio el documento firmado de ACTA DE ENTREGA para su correspondiente referencia.
			<br>
			Para dudas y/o consultas puede escribirnos al correo: helpdesk.peru@controlunion.com
			<br><br>
			Atentamente
			<br><br>
			DEPARTAMENTO DE TI<br>
			<img src='https://masterplanner.controlunion.com/assets/img/culogo_small.png'>
			<br><br>
			Control Union Services SAC | Inspecciones y Certificaciones
			<br>
			Av. Petit Thouars 4653 Piso 6 Ofic. 603 - Miraflores | Lima | Postcode 15046 | Peru
			";
		
		}else{
			$asunto="Acta de entrega - Asignacion de equipo Celular";
			$auditorMail="cpadro@controlunion.com,kasato@controlunion.com";
			
			$body="Estimado Usuario $res_tra[usuariodestino]: <br><br>
			Le informamos que se le ha asignado un equipo celular para facilitar sus tareas diarias.
			En adjunto le envio el documento firmado de ACTA DE ENTREGA para su correspondiente referencia.
			<br>
			Para dudas y/o consultas puede escribirnos al correo: cpadro@controlunion.com; kasato@controlunion.com
			<br><br>
			Atentamente
			<br><br>
			DEPARTAMENTO DE ADMINISTRACION<br>
			<img src='https://masterplanner.controlunion.com/assets/img/culogo_small.png'>
			<br><br>
			Control Union Services SAC | Inspecciones y Certificaciones
			<br>
			Av. Petit Thouars 4653 Piso 6 Ofic. 603 - Miraflores | Lima | Postcode 15046 | Peru
			";
		}
		
		
		if($res_tra['email'])
			$auditorMail.=','.$res_tra['email'];
		

	
		$fileruta="";
		if($file)
			$fileruta=$pathFile.$file;
		
		// enviar correo
		
	
		if(!empty($auditorMail)){
			$message = Swift_Message::newInstance($asunto)
				->setFrom(array($user_mail =>  $name_mail))
				->setTo(explode(",",$auditorMail))
				->setBody($body, 'text/html', 'iso-8859-2')
			;
			$message->attach(
				Swift_Attachment::fromPath('../'.$fileruta)->setFilename('Acta de entrega')
			);

			$numSent = $mailer->send($message);
			printf("Enviado: %d mensajes a $auditorMail<br>", $numSent);
		}
	}	
	
	//*****************************
	echo "Se actualizo el archivo.";	

}else if(!empty($_POST['accion']) and $_POST['accion']=='deltranProducto'){
    // delete a la base de datos usuarios
	$codtransaccion=$_POST['codtransaccion'];
	$codproducto=$_POST['codproducto'];
	$coddetalle=$_POST['coddetalle'];
    
	$invproducto->delete_dettransaccion($codtransaccion,$coddetalle,$codproducto);
    echo "Se elimino el registro.";	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='edittransaccion'){
	
	$data_usuario=$invproducto->selec_usuarios($sess_codpais);
    include("../vista/invproductos/frm_transaccion.php");	
	

/* **************************************************
3. devolucion de productos
 ***************************************************/


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_devol'){
	if(!empty($_POST['valor1']))
		$tipo=$_POST['valor1'];
	
	if($tipo=='i'){
		$asignarpro="Realizar Devoluciones tecnologicas";
		$buscarasigna="Ver Devoluciones tecnologicas";
	}else{
		$asignarpro="Devoluci&oacute;n de celulares";
		$buscarasigna="Ver Devoluciones de celulares";
	}
	
    include("../vista/invproductos/index_devol.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_devolucion'){
	//**********************************
	// mostrar index reporte
	//**********************************
	$tipo = $_POST['tipo'];
	
	$usuario_res=$invproducto->selec_usuarios($sess_codpais);
	$tipo_res=$invproducto->selec_tipodsc($tipo);
	
	if($tipo=='c')
		$data_marca=$invproducto->selec_marca($flgcelular='1','0');
	else
		$data_marca=$invproducto->selec_marca('0','1');
	
	if($tipo=='c')
		include("../vista/invproductos/index_devolucion_cel.php");	
	else
		include("../vista/invproductos/index_devolucion.php");		

// imprimir y upload de devolucion de productos
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_devolucion_ver'){
	$tipo = $_POST['tipo'];
	
	$usuario_res=$invproducto->selec_usuarios($sess_codpais);

    include("../vista/invproductos/index_devolucion_ver.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_devolucion_ver_data'){	
	//***********************************************************
	$tipo = $_POST['tipo'];
	## Read value
	$descripcion = $_POST['descripcion'];
	$numero = $_POST['numero'];
	$codusuario = $_POST['codusuario'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" d.coddevolcuion";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and d.tipo='$tipo' ";

	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_devolucion($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and   d.descripcion like '%$descripcion%' ";
	
	if(!empty($numero))
		$searchQuery.=" and   CONCAT_WS('-',LPAD(d.coddevolucion,4,'0'),YEAR(d.fecha)) like '%$numero%' ";
	
	if(!empty($codusuario))
		$searchQuery.=" and  d.codusuario=$codusuario ";
		
	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_devolucion($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_devolucion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['coddevolucion'];
			$download="";
			
			$imprimir="<button type='button' id='printproducto_". $id ."'  class='btn  btn_printdevolucion'><i class='fas fa-print'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."_$tipo'  class='btn  btn_eliinvdevolucion'><i class='fas fa-trash'></i> </button>";
			$upload="<button type='button' id='estproy_". $id ."_$tipo'  class='btn  btn_uplFiledevolucion'><i class='fas fa-upload'></i> </button>";
		
			if($row['archivo']!=''){
				$file=$pathFileDev.$row['archivo'];
				$download="<button type='button' id='$file'  class='btn  btn_verFileLista'><i class='fas fa-download'></i> </button>";
			
			}			

		   $data[] = array( 
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			   "fullusuario"=>$row['fullusuario'],
			   "numero"=>$row['numero'],
			   "fecha"=>$row['fechaf'],
			   "upload"=>$upload,
			   "download"=>$download,
			   "imprimir"=>$imprimir,
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='devolucion_upload'){
	$tipo=$_POST['tipo'];
	$coddevolucion=$_POST['coddevolucion'];
	
	$data_tra=$invproducto->select_one_devolucion($coddevolucion);
	$data_detra=$invproducto->selec_all_dettransacccion_bydevol($coddevolucion);
	
	include("../vista/invproductos/frm_desasignacion_upload.php");


}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_uploaddevolucion'){
	$tipo=$_POST['tipo'];
	$coddevolucion=$_POST['coddevolucion'];


	$file=uploadFile($_FILES,$pathFileDev,'archivo',$coddevolucion);
	if(substr($file, 0,5)!='Error' )
		$invproducto->update_fileTransaccion_dev($coddevolucion,$file);
	
	echo "Se subio el archivo $file correctamente.";


}else if(!empty($_POST['accion']) and $_POST['accion']=='deldevolucion'){

	$coddevolucion=$_POST['coddevolucion'];

	$invproducto->delete_devolucion($coddevolucion,$usuario_name,$ip);
	$invproducto->update_transaccion_anula_devolucion($coddevolucion,$usuario_name,$ip);
	
	echo "Se elimino la devolucion correctamente.";

// FIN DE devolucion
//+++++++++++++++++++	

// formulario de desasignar	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='desasiginvproducto'){

	// codmotivo
	$tipo=$_POST['tipo'];
	$coddetalle=$_POST['coddetalle'];
	$codusuario=$_POST['codusuario'];
	$serie=$_POST['serie'];
	
	$data_motivo=$invproducto->selec_motivos('s');
	
	$columnName="producto";
	$columnSortOrder=" desc"; 
	$row=0;
	$rowperpage=100;
	
	$searchQuery=" and inv_motivo.tipo='x' and ifnull(d.fecharetiro,'')='' and d.coddetalle in ($coddetalle) and  ( t.coddestino='$codusuario' or serief='$serie') and  inv_tipo.categoria='$tipo'";
	$data_OF=$invproducto->select_movimento($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	$searchQuery=" and inv_motivo.tipo='x' and ifnull(d.fecharetiro,'')='' and d.coddetalle not in ($coddetalle) and  ( t.coddestino='$codusuario' ";
	if($serie!='')
		$searchQuery.="  or serief='$serie'";
	$searchQuery.=" ) and  inv_tipo.categoria='$tipo'";
	
	
	$data_baja=$invproducto->select_movimento($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
    include("../vista/invproductos/frm_desasignacion.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_desasigProducto'){
    // proceso update a la base de datos usuarios
	
	$tipo=$_POST['tipo'];
	$codbaja=$_POST['codbaja'];
	$codusuario=$_POST['codusuario'];
	$coddetalle=$_POST['coddetalle'];
	$descripcion=$_POST['descripcionf'];
	$fecha=formatdatedos($_POST['fechai']);

	$codtotal=$coddetalle;
	
	
	
	// se retira todos los productos del usuario
	
	$coddevolucion=$invproducto->insert_devolucion($descripcion,$fecha,$codusuario,$tipo,$usuario_name,$ip);
	$invproducto->update_transaccion_des($coddevolucion,$codtotal,$descripcion,$fecha,$usuario_name,$ip);
	
	
	/*
	YA NO SUBUIRA FILE AQUI
	$file=uploadFile($_FILES,$pathFile,'archivo');
	if(substr($file, 0,5)!='Error' )
		$invproducto->update_fileTransaccion_dev($coddetalle,$file);
	*/
	
	if(!empty($_POST['codbaja'])){
		// por codbaja hacer el retiro
		$coddestino=0;
		$numtelefono="";
		$agencia="";
		$moneda="";
		$precio="";
		$cantidad=1;
		$codsede="";
		$codarea="";
		
		if(!empty($_POST['codmotivo'])) $codmotivo=$_POST['codmotivo'];
		else $codmotivo=6;
		
		$codtransaccion=$invproducto->insert_transaccion('',$codarea,$codsede,$codmotivo,$coddestino,$descripcion,$fecha,$sess_codpais,$usuario_name,$ip);
		foreach(explode("," , $_POST['codbaja']) as $coddetallef){
			
			// retirar del usuario pero no inlcuir en devolucion
			$invproducto->update_transaccion_des(0,$coddetallef,$descripcion,$fecha,$usuario_name,$ip);
			
			// obtener el codproducto desde el coddetalle
			$resdetalle=$invproducto->selec_one_dettransacccion($coddetallef);
			$codproductof=$resdetalle['codproducto'];
			$invproducto->insert_dettransaccion($cantidad,$codtransaccion,$codproductof,$descripcion,$fecha,$numtelefono,$agencia,$moneda,$precio,$usuario_name,$ip);
		}
	}
	
	echo "Se retiro el producto.";


}else if(!empty($_POST['accion']) and $_POST['accion']=='printdevproducto'){
	$codtransaccion=$_POST['codtransaccion'];
	$codproducto=$_POST['codproducto'];
	$coddetalle=$_POST['coddetalle'];
	
	$data_usuario=$invproducto->selec_usuarios($sess_codpais);
	
	$data_tra=$invproducto->selec_one_transacccion($codtransaccion);
	$data_detra=$invproducto->selec_one_dettransacccion($coddetalle);
	
	$data_res=$invproducto->selec_one_producto($codproducto);
	$codtipo=$data_res['codtipo'];
	
    include("../vista/invproductos/frm_printdevproducto.php");


/*
4. 
*/

}else if(!empty($_POST['accion']) and $_POST['accion']=='delinvProducto'){
    // delete a la base de datos usuarios
	$codtransaccion=$_POST['codtransaccion'];
	$codproducto=$_POST['codproducto'];
	$coddetalle=$_POST['coddetalle'];
    
	$invproducto->delete_dettransaccion($codtransaccion,$coddetalle,$codproducto);
    echo "Se elimino el registro.";	


}else if(!empty($_POST['accion']) and $_POST['accion']=='cgeProducto'){
    // delete a la base de datos usuarios
	
	$codproducto=$_POST['codproducto'];
	
	$res_data=$invproducto->selec_one_producto($codproducto);
    echo $res_data['codtipo'].'&'.$res_data['numero'].'&'.$res_data['fechainiciofprox'];	

}else if(!empty($_POST['accion']) and $_POST['accion']=='printproducto'){

	$codtransaccion=$_POST['codtransaccion'];
	$codproducto=$_POST['codproducto'];
	$coddetalle=$_POST['coddetalle'];
	$tipo=$_POST['tipo'];
	
	$data_usuario=$invproducto->selec_usuarios($sess_codpais);
	
	$data_tra=$invproducto->selec_one_transacccion($codtransaccion);
	$data_detra=$invproducto->selec_all_dettransacccion($codtransaccion);
	$data_aud=$auditor->selec_one_auditor($sess_codauditor);
	$data_emp=$invproducto->selec_one_empresabytransac($codtransaccion);
	
	
	require '../../vendor_2/autoload.php';
	$mpdf = new \Mpdf\Mpdf();
	
	ob_start();
	if($tipo=='i')
		include("../vista/invproductos/frm_printproducto.php");
	else
		include("../vista/invproductos/frm_printproducto_cel.php");

	$html =ob_get_contents();
	ob_end_clean();

	$mpdf->SetDisplayMode('fullpage');
	$stylesheet = file_get_contents('../assets/plugins/bootstrap/bootstrap.min.css');
	$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
	$mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);
	$file="ActaEntrega_". $codtransaccion.".pdf";
	$mpdf->Output('../archivos/inventarioRtf/'.$file,'F');
	echo 'archivos/inventarioRtf/'.$file;
	exit;

}else if(!empty($_POST['accion']) and $_POST['accion']=='printproductosal'){
	
	$codtransaccion=$_POST['codtransaccion'];
	$codproducto=$_POST['codproducto'];
	$coddetalle=$_POST['coddetalle'];
	
	$data_usuario=$invproducto->selec_usuarios($sess_codpais);
	
	$data_tra=$invproducto->selec_one_transacccion($codtransaccion);
	$data_detra=$invproducto->selec_one_dettransacccion($coddetalle);
	
	$data_res=$invproducto->selec_one_producto($codproducto);
	$codtipo=$data_res['codtipo'];
	$categoria=$data_res['categoria'];
	
	$file_contents = file_get_contents('../vista/invproductos/formato_ti_salida.rtf'); 
	
	$tamanio=" ";
	if($data_res['tamanio']!='') $tamanio=$data_res['tamanio'];
	
	if($categoria=='a')
		$entrega="Paolo Vizcarra";
	else
		$entrega="kevin Asato";
	
	$file_contents = str_replace('#bloque#','',$file_contents);
	$file_contents = str_replace('#num#',$data_tra['numero'],$file_contents);
	$file_contents = str_replace('#fullusuario#',$data_tra['fullusuario'],$file_contents);
	$file_contents = str_replace('#tipo#',$data_res['tipodsc'],$file_contents);
	$file_contents = str_replace('#host#',$data_res['host'],$file_contents); 
	$file_contents = str_replace('#proc#',$data_res['procesador'],$file_contents);
	$file_contents = str_replace('#marca#',$data_res['marca'],$file_contents);
	$file_contents = str_replace('#entrega#',$entrega,$file_contents);
	$file_contents = str_replace('#modelo#',$data_res['modelo'],$file_contents);
	$file_contents = str_replace('#numtele#',$data_detra['numtelefono'],$file_contents);
	$file_contents = str_replace('#imei#',$data_res['imei'],$file_contents);
	$file_contents = str_replace('#ram#',$data_res['ram'],$file_contents);
	$file_contents = str_replace('#serie#',$data_res['serief'],$file_contents);
	$file_contents = str_replace('#hd1#',$data_res['hd1'],$file_contents);
	$file_contents = str_replace('#tam#',$tamanio,$file_contents);
	$file_contents = str_replace('#estado#',"",$file_contents);
	$file_contents = str_replace('#so#',$data_res['so'],$file_contents);
	$file_contents = str_replace('#ant#',$data_res['antivirus'],$file_contents);
	$file_contents = str_replace('#on#',$data_res['onedrive'],$file_contents);
	$file_contents = str_replace('#office#',$data_res['office'],$file_contents);
	$file_contents = str_replace('#dominio#',$data_res['dominio'],$file_contents);
	$file_contents = str_replace('#comentarios#',$data_detra['descripcion'],$file_contents);
	
	$ruta="archivos/inventarioRtf/ActaSalida_". $codtransaccion.".rtf";
	file_put_contents('../'.$ruta, $file_contents);
	echo $ruta;


}else if(!empty($_POST['accion']) and $_POST['accion']=='printdevolucion'){
	
	$tipo=$_POST['tipo'];
	$coddevolucion=$_POST['coddevolucion']; // llegan todos los id
	$data_aud=$auditor->selec_one_auditor($sess_codauditor);
	$data_tra=$invproducto->select_one_devolucion($coddevolucion);
	$data_detra=$invproducto->selec_all_dettransacccion_bydevol($coddevolucion);


	require '../../vendor_2/autoload.php';
	$mpdf = new \Mpdf\Mpdf();
	
	ob_start();
	
	if($tipo=='i')
		include("../vista/invproductos/frm_printproducto_dev.php");
	else
		include("../vista/invproductos/frm_printproducto_dev_cel.php");
	
	$html =ob_get_contents();
	ob_end_clean();

	$mpdf->SetDisplayMode('fullpage');
	$stylesheet = file_get_contents('../assets/plugins/bootstrap/bootstrap.min.css');
	$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
	$mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);
	$file="ActaDevolucion_". $coddevolucion.".pdf";
	$mpdf->Output('../archivos/inventarioRtf/'.$file,'F');
	echo 'archivos/inventarioRtf/'.$file;
	exit;
	
	
	//*******************************************************************************************
	// 4. bajas y salidas
	//*******************************************************************************************
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_mov'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	if(!empty($_POST['valor1']))
		$tipo=$_POST['valor1'];
	
	$titulo="Salidas / Bajas";
	$titulo2="Movimientos de productoss";
	
	if($tipo=='a'){
		$titulo="Bajas y salidas de celulares";
		$titulo2="Movimientos de celulares";
	}
	
	include("../vista/invproductos/index_mov.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_movimiento'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	$tipo = $_POST['tipo'];
	$tipo_res=$invproducto->selec_tipodsc($tipo);
	
	if($tipo=='c')
		include("../vista/invproductos/index_movimiento_cel.php");	
	else
		include("../vista/invproductos/index_movimiento.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_datamovimiento'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$codtipo = $_POST['codtipo'];
	
	$tipo = $_POST['tipo'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" producto";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and inv_tipo.categoria='$tipo' and inv_motivo.codmotivo!='11' ";

	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_mov_ingsal($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and  ( inv_producto.serief like '%$descripcion%' or producto like '%$descripcion%' or modelo like '%$descripcion%'  or imei like '%$descripcion%')";
	
	if(!empty($codtipo))
		$searchQuery.=" and  inv_producto.codtipo=$codtipo ";
	
	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_mov_ingsal($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_mov_ingsal($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

		$id=$row['codtransaccion'];
		$editar="<button type='button' id='estproy_". $id ."'  class='btn  btn_edimov'><i class='fas fa-edit'></i> </button>";
		
		$eliminar="";
		$eliminar="<button type='button' id='estproy_". $id ."'  class='btn  btn_elimov'><i class='fas fa-trash'></i> </button>";

		
		$data[] = array( 
		   "detalle"=>str_replace('"','',json_encode($row['detalle'],JSON_UNESCAPED_UNICODE)),
		    "detalle2"=>str_replace('"','',json_encode($row['detalle2'],JSON_UNESCAPED_UNICODE)),
		   "mov"=>$row['mov'],
		   "motivo"=>$row['motivo'],
		   "fecha"=>$row['fecha'],
		   "id"=>$id,
		   "editar"=>$editar,
		   "eliminar"=>$eliminar
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


// 5. mantenimento
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_datamov'){
	//**********************************
	// mostrar index de 
	//**********************************
	$tipo="";
	if(!empty($_POST['valor1']))
		$tipo=$_POST['valor1'];
	$tipo_res=$invproducto->selec_tipodsc($tipo);
	
	if($tipo=='c')
		include("../vista/invproductos/index_datamov_cel.php");	
	else
		include("../vista/invproductos/index_datamov.php");	
	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_datamovdetalle'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$codtipo = $_POST['codtipo'];
	$tipo = $_POST['tipo'];
	$clase = $_POST['clase'];
	
	if(!empty($_POST['dias']))
		$dias = $_POST['dias'];
	
	else	
		$dias=30;
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" producto";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and categoria='$tipo' ";

	if($tipo=='i')
		$searchQuery.="and to_days(inv_producto.fechainicio) - to_days(now()) < $dias ";

	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_producto_mant($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and  ( producto like '%$descripcion%' or modelo like '%$descripcion%' or serief like '%$descripcion%' or imei like '%$descripcion%' or numero like '%$descripcion%')";
	
	if(!empty($codtipo))
		$searchQuery.=" and  inv_producto.codtipo=$codtipo ";
	
	if(!empty($clase))
		$searchQuery.=" and  inv_producto.clase='$clase' ";

	
	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_producto_mant($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_producto_mant($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

		$id=$row['llave'];
		$envia="<button type='button' id='estproy_". $id ."'  class='btn  btn_edimant'><i class='fas fa-edit'></i> </button>";
		
		$retorna="";
		if(!empty($row['fechamant'])){
			$retorna="<button type='button' id='estproy_". $id ."'  class='btn  btn_backmant'><i class='fas fa-edit'></i> </button>";
		}	
			
		$data[] = array( 
		   "tipodsc"=>str_replace('"','',json_encode($row['tipodsc'],JSON_UNESCAPED_UNICODE)),
		   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
		   "modelo"=>str_replace('"','',json_encode($row['modelo'],JSON_UNESCAPED_UNICODE)),
		   "serief"=>$row['serief'],
		   "marca"=>$row['marca'],
		   "programado"=>$row['programado'],
		   "usuariodestino"=>$row['usuariodestino'],
		   "dias"=>$row['dias'],
		   "dias2"=>$row['dias2'],
			"imei"=>$row['imei'],
			"numero"=>$row['numero'],
		   "periodo"=>$row['periodo'],
		   "id"=>$id,
		   "fechamant"=>$row['fechamant'],
		   "fechadevuelve"=>$row['fechadevuelve'],
		   "envia"=>$envia,
		   "retorna"=>$retorna
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='editmovimiento'){
	$tipo=$_POST['tipoprod'];
	$ingsal=$_POST['ingsal'];
	
	// data_sede
	$data_area=$invproducto->selec_areas();
	$data_sede=$invproducto->selec_sedes();
	$data_motivo=$invproducto->selec_motivos($ingsal);
	
	
	
	if($ingsal=='i'){
		$data_pro=$invproducto->select_producto_noselect($sess_codpais,$tipo);
		include("../vista/invproductos/frm_movimiento.php");
	}else{
		$data_pro=$invproducto->select_producto_noselect($sess_codpais,$tipo,1);
		include("../vista/invproductos/frm_movimiento_sal.php");
	}	


}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_movimiento'){

	$codproducto=$_POST['codproducto'];
	$fecha=formatdatedos($_POST['fechai']);
	$descripcion=$_POST['descripcion'];
	$codmotivo=$_POST['codmotivo'];
	$cantidad=$_POST['cantidad'];
	$codsede=$_POST['codsede'];
	$codarea=$_POST['codarea'];
	$coddestino=0;
	$numtelefono="";
	$agencia="";
	$moneda="";
	$precio="";
	$ubicacion="";
	
	// primero debemos inactivas el producto si estuviera asignado
	$invproducto->update_transaccion_des_byprod($codproducto,$usuario_name,$ip);
	
	// se crea la salida
	$codtransaccion=$invproducto->insert_transaccion($ubicacion,$codarea,$codsede,$codmotivo,$coddestino,$descripcion,$fecha,$sess_codpais,$usuario_name,$ip);
	$coddetalle=$invproducto->insert_dettransaccion($cantidad,$codtransaccion,$codproducto,$descripcion,$fecha,$numtelefono,$agencia,$moneda,$precio,$usuario_name,$ip);
			
			
	echo "Se actualizo el registro.";
		
}else if(!empty($_POST['accion']) and $_POST['accion']=='editmanten'){
	$codproducto=$_POST['codproducto'];
	$id=$_POST['id'];
	$tipo=$_POST['tipo'];
	
	$data_usuario=$invproducto->selec_usuarios($sess_codpais);
	$motivo_res=$invproducto->selec_motivos('y');
	
	$data_res=$invproducto->selec_one_producto($codproducto);
	
	$data_tra=$invproducto->selec_transacc_producto($codproducto);
	
	if(!empty($id))
		$data_mov=$invproducto->selec_one_movimiento($id);
	
    include("../vista/invproductos/frm_mantenimiento.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_mantenimiento'){

	$codproducto=$_POST['codproducto'];
	$id=$_POST['id'];
	$fecha=formatdatedos($_POST['fechai']);
	$usuariodestino=$_POST['usuariodestino'];
	$descripcion=$_POST['descripcionf'];
	$codmotivo=$_POST['codmotivo'];
	$tipom=$_POST['tipom'];
	$flgregreso="0";
	
	if(!empty($id)){
		$invproducto->update_mantenimiento($id,$codproducto,$fecha,$usuariodestino,$descripcion,$codmotivo,$tipom,$usuario_name,$ip);
	}else{
		$id=$invproducto->insert_mantenimiento($codproducto,$fecha,$usuariodestino,$descripcion,$codmotivo,$tipom,$flgregreso,$sess_codpais,$usuario_name,$ip);
	}
	
	echo "Se actualizo el registro.";
	

}else if(!empty($_POST['accion']) and $_POST['accion']=='edit_backmanten'){
	$codproducto=$_POST['codproducto'];
	$id=$_POST['id'];
	$tipo=$_POST['tipo'];
	
	$data_usuario=$invproducto->selec_usuarios($sess_codpais);
	
	$data_res=$invproducto->selec_one_producto($codproducto);
	
	$data_mov=$invproducto->selec_one_movimiento($id);
	
    include("../vista/invproductos/frm_backmantenimiento.php");


}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_backmantenimiento'){

	$codproducto=$_POST['codproducto'];
	$id=$_POST['id'];
	$fecha=formatdatedos($_POST['fechai']);
	$fechainicio=formatdatedos($_POST['fecham']);
	$descripcionback=$_POST['descripcionbackf'];
	$flgregreso="1";
	
	$usuarioretorno="";
	$resdata=$invproducto-> selec_transacc_producto($codproducto);
	if($resdata)
		$usuarioretorno=$resdata['usuariodestino'];
	
	$invproducto-> update_fecproxmant($fechainicio,$codproducto,$usuario_name,$ip);
	
	$invproducto->update_backmantenimiento($id,$codproducto,$flgregreso,$fecha,$descripcionback,$usuarioretorno,$usuario_name,$ip);
	
	
	echo "Se actualizo el registro.";

	/************************************************
	6. REPORTE DE MOVIMIENTOS
	*************************************************/
}else  if(!empty($_POST['accion']) and $_POST['accion']=='index_ingegr'){
	//**********************************
	// mostrar index de REPORTE
	//**********************************
	if(!empty($_POST['valor1']))
		$tipo=$_POST['valor1'];
	
	$tipo_res=$invproducto->selec_tipodsc($tipo);
	
	if($tipo=='i')
		include("../vista/invproductos/reporte_mov.php");
	else if($tipo=='c')
		include("../vista/invproductos/reportes_adm.php");	
	else
		include("../vista/invproductos/reporte_mov_con.php");
	
}else  if(!empty($_POST['accion']) and $_POST['accion']=='index_reportemov'){
	//**********************************
	// mostrar index de REPORTE
	//**********************************
	if(!empty($_POST['valor1']))
		$tipo=$_POST['valor1'];
	
	$tipo_res=$invproducto->selec_tipodsc($tipo);
	
	if($tipo=='i')
		include("../vista/invproductos/reporte_mov.php");
	else
		include("../vista/invproductos/reporte_mov_adm.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_ingegr_data'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$codtipo = $_POST['codtipo'];
	$clase = $_POST['clase'];
	$tipo = $_POST['tipo'];
	$asignado = $_POST['asignado'];
	
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" producto";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " and categoria='$tipo' ";

	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_producto($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and   ( serief like '%$descripcion%' or modelo like '%$descripcion%' or imei like '%$descripcion%' or numero like '%$descripcion%' )  ";
	
	if(!empty($codtipo))
		$searchQuery.=" and  inv_producto.codtipo=$codtipo ";
	
	if(!empty($clase))
		$searchQuery.=" and  inv_producto.clase='$clase' ";

	if(!empty($asignado) and $asignado=='NO')
		$searchQuery.=" and  vista.usuariodestino is null ";
	
	if(!empty($asignado) and $asignado=='SI')
		$searchQuery.="  and  vista.usuariodestino is not null ";
	
	
	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_producto($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_producto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codproducto'];
			
			$edita="<button type='button' id='estproy_". $id ."_$tipo'  class='btn  btn_repproducto'><i class='fas fa-print'></i> </button>";
			
			if($row['coddestino']!=''){
				$elimina="";
			}
			
			$barcode="<img height=75 src='com/barcode.php?s=qrl&d=$row[parabarra]'>";
		
			if($row['codtipo']==4)
				$producto=$row['producto']. " Marca: ". $row['marca'] . " Modelo: ". $row['modelo'] . " Serie: ". $row['serie']. " IMEI: ". $row['imei'];
			else	
				$producto=str_replace('"','',json_encode($row['producto'],JSON_UNESCAPED_UNICODE));
			
		   $data[] = array( 
			   "producto"=>$producto,
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			   "modelo"=>str_replace('"','',json_encode($row['modelo'],JSON_UNESCAPED_UNICODE)),
			   "usuariodestino"=>str_replace('"','',json_encode($row['usuariodestino'],JSON_UNESCAPED_UNICODE)),
			   "clase"=>$row['clase'],
			   "tipodsc"=>$row['tipodsc'],
			   "serief"=>$row['serief'],
			   "marca"=>$row['marca'],
			   "codproducto"=>$id,
			   "estado"=>$row['estado'],
			   "imei"=>$row['imei'],
			  "numero"=>$row['numero'],
			   "procesador"=>$row['procesador'],
			   "edita"=>$edita,
			   "ram"=>$row['ram'],
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

}else if(!empty($_POST['accion']) and $_POST['accion']=='repproducto'){
	$codproducto=$_POST['codproducto'];
	$tipo=$_POST['tipo'];
	
	$data_res=$invproducto->selec_one_producto($codproducto);
	$data_mov=$invproducto->selec_movimiento($codproducto);
	$data_stock=$invproducto->selec_stock($codproducto);

	if($tipo=='c')
		include("../vista/invproductos/frm_kardex_cel.php");
	else if($tipo=='a')
		include("../vista/invproductos/frm_kardex_adm.php");
	else
		include("../vista/invproductos/frm_kardex.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='delmovimiento'){
	$codtransaccion=$_POST['codtransaccion'];
	
	$data_mov=$invproducto->delete_movimiento($codtransaccion);

    echo "Se actualizo el registro.";

}else  if(!empty($_POST['accion']) and $_POST['accion']=='report_telefono'){
	//**********************************
	// mostrar index de REPORTE
	//**********************************
	if(!empty($_POST['valor1']))
		$tipo=$_POST['valor1'];
	
	$codtipo=4;
	include("../vista/invproductos/report_telefono.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='report_telefono_data'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$codtipo = 4;
	
	$asignado = $_POST['asignado'];
	$tipo = $_POST['tipo'];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
		
	$columnName=" producto";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = "  and  inv_producto.codtipo=4";

	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_producto($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and  ( producto like '%$descripcion%' or modelo like '%$descripcion%' or modelo like '%$descripcion%' or serief like '%$descripcion%' or numero like '%$descripcion%' or imei like '%$descripcion%')";
	
	if(!empty($tipo))
		$searchQuery.=" and  inv_tipo.categoria='$tipo' ";
	
	
	if(!empty($asignado) and $asignado=='NO')
		$searchQuery.=" and  vista.usuariodestino is null ";
	
	if(!empty($asignado) and $asignado=='SI')
		$searchQuery.="  and  vista.usuariodestino is not null ";
	
	
	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_producto($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_producto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codproducto'];
		
		   $data[] = array( 
			   "producto"=>str_replace('"','',json_encode($row['producto'],JSON_UNESCAPED_UNICODE)),
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			   "modelo"=>str_replace('"','',json_encode($row['modelo'],JSON_UNESCAPED_UNICODE)),
			  "clase"=>$row['clase'],
			   "tipodsc"=>$row['tipodsc'],
			    "numero"=>$row['numero'],
				"imei"=>$row['imei'],
			   "fecha_ingreso"=>$row['fecha_ingreso'],
			   "marca"=>$row['marca'],
			   "area"=>$row['area'],
			      "usuariodestino"=>$row['usuariodestino'],
			   "codproducto"=>$id,
			 
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
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='expCelular'){
    // delete a la base de datos usuarios
	$tipo=$_POST['tipo'];
	$asignado=$_POST['asignado'];
	
	$descripcion=$_POST['descripcion'];
	$codtipo=$_POST['codtipo'];
	
	$searchQuery = " and categoria='$tipo'";
	if(!empty($descripcion))
		$searchQuery.=" and  ( producto like '%$descripcion%' or modelo like '%$descripcion%' or modelo like '%$descripcion%' or serief like '%$descripcion%' or numero like '%$descripcion%' or imei like '%$descripcion%')";
	
	if(!empty($codtipo))
 		$searchQuery.=" and  inv_producto.codtipo=$codtipo ";
	
	if(!empty($tipo))
		$searchQuery.=" and  inv_tipo.categoria='$tipo' ";
	
	
	if(!empty($asignado) and $asignado=='NO')
		$searchQuery.=" and  vista.usuariodestino is null ";
	
	if(!empty($asignado) and $asignado=='SI')
		$searchQuery.="  and vista.usuariodestino is not  null ";
	
	

	## Fetch records
	$columnName=" inv_producto.fecha_ingreso ";
	$columnSortOrder=" desc ";
	$row=0;
	$rowperpage=10000;
	$data_OF=$invproducto->select_producto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	include("../vista/invproductos/exporta_celular.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='expSalida'){
    // delete a la base de datos usuarios
	$tipo=$_POST['tipo'];
	$codtipo=$_POST['codtipo'];
	//$codtipo = 4;
	$descripcion=$_POST['descripcion'];
	
	$searchQuery = " and inv_tipo.categoria='$tipo' and inv_motivo.codmotivo!='11' ";
	
	if(!empty($descripcion))
		$searchQuery.=" and  ( serief like '%$descripcion%' or producto like '%$descripcion%' or modelo like '%$descripcion%'  or imei like '%$descripcion%')";
	
	if(!empty($codtipo))
		$searchQuery.=" and  inv_producto.codtipo=$codtipo ";
	
	## Fetch records
	$columnName=" producto";
	$columnSortOrder=" desc ";
	$row=0;
	$rowperpage=10000;
	$data_OF=$invproducto->select_mov_ingsal($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	include("../vista/invproductos/exporta_salida.php");	



/**************************************
MAESTROS para inventario
 1. usuarios
**************************************/
}else if(!empty($_POST['accion']) and $_POST['accion']=='usuarios'){
	//**********************************
	// mostrar index de calendario
	//**********************************
    include("../vista/invmaestros/index_usuario.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_usuario'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" fullusuario ";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " ";

	if(!empty($descripcion))
		$searchQuery.=" and fullusuario like '%$descripcion%' ";
		
	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_usuario();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_usuario($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_usuarios($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codusuario'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediUsuario'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliUsuario'><i class='fas fa-trash'></i> </button>";
			
		   $data[] = array( 
			   "fullusuario"=>str_replace('"','',json_encode($row['fullusuario'],JSON_UNESCAPED_UNICODE)),
			   "codusuario"=>$id,
			   "area"=>$row['area'],
			   "email"=>$row['email'],
			   "sede"=>$row['sede'],
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editUsuario'){
	$codusuario="";
	
	$data_area=$invproducto->selec_areas();
	$data_sede=$invproducto->selec_sedes();
	$data_staff=$invproducto->selec_staff($sess_codpais);
	
	if(!empty($_POST['codusuario']))
		$data_res=$invproducto->selec_one_usuario($_POST['codusuario']);

    include("../vista/invmaestros/frm_usuario.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detUsuario'){
    // proceso update a la base de datos usuarios
	
	$nombres=$_POST['nombres'];
	$apepaterno=$_POST['apepaterno'];
	$apematerno=$_POST['apematerno'];
	$codarea=$_POST['codarea'];
	$codsede=$_POST['codsede'];
	$email=$_POST['email'];
	$dni=$_POST['dni'];
	$id_auditor=$_POST['id_auditor'];
	$fullusuario="$nombres $apepaterno $apematerno";

	if(empty($_POST['codusuario']))
		$codusuario=$invproducto->insert_usuario($id_auditor,$dni,$fullusuario,$nombres,$apepaterno,$apematerno,$codarea,$codsede,$email,$usuario_name,$ip);
	else{
		$codusuario=$_POST['codusuario']; // id
		$invproducto->update_usuario($codusuario,$id_auditor,$dni,$fullusuario,$nombres,$apepaterno,$apematerno,$codarea,$codsede,$email,$usuario_name,$ip);
	}	
	 echo "Se actualizo la informacion.";

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delUsuario'){
    // delete a la base de datos usuarios
	$codusuario=$_POST['codusuario']; 
    $invproducto->delete_usuario($codusuario);
    echo "Se elimino el registro.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='busc_auditor'){
    // delete a la base de datos usuarios
	$id_auditor=$_POST['id_auditor']; 
    $res=$invproducto->selec_one_auditor($id_auditor);
    if($res)
		echo "$res[email]&&$res[nombre]&&$res[apepaterno]&&$res[apematerno]&&$res[dni]";
	
	
	/**************************************
	MAESTROS para inventario
	2. areas
	**************************************/
}else if(!empty($_POST['accion']) and $_POST['accion']=='areas'){
    include("../vista/invmaestros/index_area.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_area'){

	$descripcion = $_POST['descripcion'];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" area ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	$searchQuery = " ";

	if(!empty($descripcion))
		$searchQuery.=" and area like '%$descripcion%' ";
		
	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_area();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_area($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_areas($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codarea'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediArea'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliArea'><i class='fas fa-trash'></i> </button>";
			
		   $data[] = array( 
			   "codarea"=>$id,
			   "area"=>$row['area'],
			   "usuario_ingreso"=>$row['usuario_ingreso'],
			   "fecha_ingreso"=>$row['fecha_ingreso'],
			   "usuario_modifica"=>$row['usuario_modifica'],
			   "fecha_modifica"=>$row['fecha_modifica'],
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editArea'){
	$codarea="";
	
	if(!empty($_POST['codarea']))
		$data_res=$invproducto->selec_one_area($_POST['codarea']);

    include("../vista/invmaestros/frm_area.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detArea'){
    // proceso update a la base de datos usuarios
	
	$area=$_POST['area'];

	if(empty($_POST['codarea']))
		$codusuario=$invproducto->insert_area($area,$usuario_name,$ip);
	else{
		$codarea=$_POST['codarea']; // id
		$invproducto->update_area($codarea,$area,$usuario_name,$ip);
	}	
	 echo "Se actualizo la informacion.";

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delArea'){
    // delete a la base de datos usuarios
	$codarea=$_POST['codarea']; 
    $invproducto->delete_area($codarea);
    echo "Se elimino el registro.";

	/**************************************
	MAESTROS para inventario
	3. marcas
	**************************************/
}else if(!empty($_POST['accion']) and $_POST['accion']=='marcas'){
    include("../vista/invmaestros/index_marca.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_marca'){

	$descripcion = $_POST['descripcion'];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" marca ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	$searchQuery = " ";

	if(!empty($descripcion))
		$searchQuery.=" and marca like '%$descripcion%' ";
		
	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_marca();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_marca($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_marcas($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codmarca'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediMarca'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliMarca'><i class='fas fa-trash'></i> </button>";
			
		   $data[] = array( 
			   "codmarca"=>$id,
			   "marca"=>$row['marca'],
			   "usuario_ingreso"=>$row['usuario_ingreso'],
			   "fecha_ingreso"=>$row['fecha_ingreso'],
			   "usuario_modifica"=>$row['usuario_modifica'],
			   "fecha_modifica"=>$row['fecha_modifica'],
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editMarca'){
	$codmarca="";
	
	if(!empty($_POST['codmarca']))
		$data_res=$invproducto->selec_one_marca($_POST['codmarca']);

    include("../vista/invmaestros/frm_marca.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detMarca'){
    // proceso update a la base de datos usuarios
	
	$marca=$_POST['marca'];
	$flgcelular="0";
	$flgit="0";
	
	if($_POST['flgcelular'])
		$flgcelular=$_POST['flgcelular'];
	
	if($_POST['flgit'])
		$flgit=$_POST['flgit'];

	if(empty($_POST['codmarca']))
		$codusuario=$invproducto->insert_marca($marca,$flgcelular,$flgit,$usuario_name,$ip);
	else{
		$codmarca=$_POST['codmarca']; // id
		$invproducto->update_marca($codmarca,$marca,$flgcelular,$flgit,$usuario_name,$ip);
	}	
	 echo "Se actualizo la informacion.";

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delMarca'){
    // delete a la base de datos usuarios
	$codmarca=$_POST['codmarca']; 
    $invproducto->delete_marca($codmarca);
    echo "Se elimino el registro.";	
	
	
/**************************************
	MAESTROS para inventario
	4. sedes
	**************************************/
}else if(!empty($_POST['accion']) and $_POST['accion']=='sedes'){
    include("../vista/invmaestros/index_sede.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_sede'){

	$descripcion = $_POST['descripcion'];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" sede ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	$searchQuery = " ";

	if(!empty($descripcion))
		$searchQuery.=" and sede like '%$descripcion%' ";
		
	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_sede();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_sede($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_sedes($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codsede'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediSede'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliSede'><i class='fas fa-trash'></i> </button>";
			
		   $data[] = array( 
			   "codsede"=>$id,
			   "sede"=>$row['sede'],
			   "usuario_ingreso"=>$row['usuario_ingreso'],
			   "fecha_ingreso"=>$row['fecha_ingreso'],
			   "usuario_modifica"=>$row['usuario_modifica'],
			   "fecha_modifica"=>$row['fecha_modifica'],
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editSede'){
	$codsede="";
	
	if(!empty($_POST['codsede']))
		$data_res=$invproducto->selec_one_sede($_POST['codsede']);

    include("../vista/invmaestros/frm_sede.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detSede'){
    // proceso update a la base de datos usuarios
	
	$sede=$_POST['sede'];

	if(empty($_POST['codsede']))
		$codusuario=$invproducto->insert_sede($sede,$usuario_name,$ip);
	else{
		$codsede=$_POST['codsede']; // id
		$invproducto->update_sede($codsede,$sede,$usuario_name,$ip);
	}	
	 echo "Se actualizo la informacion.";

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delSede'){
    // delete a la base de datos usuarios
	$codsede=$_POST['codsede']; 
    $invproducto->delete_sede($codsede);
    echo "Se elimino el registro.";		

/**************************************
	MAESTROS para inventario
	5. tipo producto
	**************************************/
}else if(!empty($_POST['accion']) and $_POST['accion']=='tipoproducto'){
    include("../vista/invmaestros/index_tipoproducto.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_tipoproducto'){

	$descripcion = $_POST['descripcion'];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" tipodsc ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	$searchQuery = " ";

	if(!empty($descripcion))
		$searchQuery.=" and tipodsc like '%$descripcion%' ";
		
	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_tipoproducto();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_tipoproducto($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_tipoproducto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codtipo'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediTipoproducto'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliTipoproducto'><i class='fas fa-trash'></i> </button>";
			
		   $data[] = array( 
			   "codtipo"=>$id,
			   "tipodsc"=>$row['tipodsc'],
			   "flgmantenimientodsc"=>$row['flgmantenimientodsc'],
			   "categoriadsc"=>$row['categoriadsc'],
			   "tiempo"=>$row['tiempo'],
			    "dato"=>$row['dato'],
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editTipoproducto'){
	$codtipo="";
	
	if(!empty($_POST['codtipo']))
		$data_res=$invproducto->selec_one_tipoproducto($_POST['codtipo']);

    include("../vista/invmaestros/frm_tipoproducto.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detTipoproducto'){
    // proceso update a la base de datos usuarios
	
	$tipodsc=$_POST['tipodsc'];
	$flgmantenimiento=$_POST['flgmantenimiento'];
	$categoria=$_POST['categoria'];
	$tiempo=$_POST['tiempo'];
	$flgdato=$_POST['flgdato'];

	if(empty($_POST['codtipo']))
		$codtipo=$invproducto->insert_tipoproducto($flgdato,$tipodsc,$flgmantenimiento,$categoria,$tiempo,$usuario_name,$ip);
	else{
		$codtipo=$_POST['codtipo']; // id
		$invproducto->update_tipoproducto($codtipo,$flgdato,$tipodsc,$flgmantenimiento,$categoria,$tiempo,$usuario_name,$ip);
	}	
	 echo "Se actualizo la informacion.";

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delTipoproducto'){
	
    // delete a la base de datos usuarios
	$codtipo=$_POST['codtipo']; 
    $invproducto->delete_tipoproducto($codtipo);
    echo "Se elimino el registro.";			

	/**************************************
	MAESTROS para consumibles
	6. consumibles
	**************************************/
}else if(!empty($_POST['accion']) and $_POST['accion']=='consumibles'){
    include("../vista/invmaestros/index_consumible.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_consumibles'){

	$descripcion = $_POST['descripcion'];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	$columnName=" producto ";
	$columnSortOrder=" asc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	$searchQuery = " and categoria='a' ";

	if(!empty($descripcion))
		$searchQuery.=" and producto like '%$descripcion%' ";
		
	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_consumible();
	$totalRecords = $data_maxOF['total'];

	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_consumible($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_consumible($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codproducto'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediConsumible'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliConsumible'><i class='fas fa-trash'></i> </button>";
			if($row['activo']=='1')
				$chk=" checked";
			$estado="<div class='custom-control custom-switch  custom-switch-off-danger custom-switch-on-success'>
						  <input type=checkbox class='custom-control-input'onchange='js_changeactiveprod($id)' name='flgstatus_$id' id='flgstatus_$id' $chk >
						  <label class='custom-control-label' for='flgstatus_$id'></label>
						</div>";
			
		   $data[] = array( 
			   "codproducto"=>$id,
			   "producto"=>$row['producto'],
			   "tipodsc"=>$row['tipodsc'],
			   "umedida"=>$row['umedida'],
			   "codigo"=>$row['codigo'],
			   "stock_minimo"=>$row['stock_min'],
			    "estado"=>$estado,
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editConsumible'){
	$codproducto="";
	$data_categoria=$invproducto->selec_tipodsc('a');
	
	if(!empty($_POST['codproducto']))
		$data_res=$invproducto->selec_one_consumible($_POST['codproducto']);

    include("../vista/invmaestros/frm_consumible.php");
	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='activoProducto'){
	$codproducto=$_POST['codproducto'];
	$flgactivo=$_POST['flgactivo'];
	$invproducto->update_estadoproducto($codproducto,$flgactivo);	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detConsumible'){
    // proceso update a la base de datos usuarios
	
	$producto=$_POST['producto'];
	$codtipo=$_POST['codtipo'];
	$umedida=$_POST['umedida'];
	$stock_minimo=$_POST['stock_minimo'];
	$codigo=$_POST['codigo'];

	if(empty($_POST['codproducto']))
		$codproducto=$invproducto->insert_consumible($producto,$codtipo,$umedida,$stock_minimo,$codigo,$usuario_name,$ip);
	else{
		$codproducto=$_POST['codproducto']; // id
		$invproducto->update_consumible($codproducto,$producto,$codtipo,$umedida,$stock_minimo,$codigo,$usuario_name,$ip);
	}	
	 echo "Se actualizo la informacion.";

	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delConsumible'){
	
    // delete a la base de datos usuarios
	$codproducto=$_POST['codproducto']; 
    $invproducto->delete_consumible($codproducto);
    echo "Se elimino el registro.";			
		
	
	/************************
	 SUMINISTROS
	************************/
	
	}else if(!empty($_POST['accion']) and $_POST['accion']=='index_suministro'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$codtipo = $_POST['codtipo'];
	$tipo = $_POST['tipo'];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	if(!empty($_POST['fechai']))
		$fechai=formatdatedos($_POST['fechai']);
	
	if(!empty($_POST['fechaf']))
		$fechaf=formatdatedos($_POST['fechaf']);
	
	$columnName=" producto";
	$columnSortOrder=" desc ";
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = "  AND categoria='a' and inv_motivo.tipo='i'  ";

	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_suministros($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and  ( producto like '%$descripcion%' or codigo like '%$descripcion%')";
	
	if(!empty($codtipo))
 		$searchQuery.=" and  t.codtipo=$codtipo ";
	
	
	if(!empty($fechai))
 		$searchQuery.=" and  to_days(tr.fecha) >= to_days('$fechai') ";
	
	if(!empty($fechaf))
 		$searchQuery.=" and  to_days(tr.fecha) <= to_days('$fechaf') ";
	
	
	## Total number of record with filtering
	$data_maxOF2=$invproducto->selec_total_suministros($searchQuery);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_suministros($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['coddetalle'];
			
			$edita="<button type='button' id='estproy_". $id ."_".$tipo."'  class='btn  btn_ediproductodet'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."_".$tipo."'  class='btn  btn_elidettransac'><i class='fas fa-trash'></i> </button>";

			if($row['coddestino']!='' or $iselimina==0){
				// $elimina="";
			}
			
			$barcode="<img height=75 src='com/barcode.php?s=qrl&d=$row[parabarra]'>";
		
		   $data[] = array( 
			   "producto"=>str_replace('"','',json_encode($row['producto'],JSON_UNESCAPED_UNICODE)),
			   "tipodsc"=>$row['tipodsc'],
			    "codigo"=>$row['codigo'],
				"umedida"=>$row['umedida'],
				"moneda"=>$row['moneda'],
				"cantidad"=>$row['cantidad'],
				"precio"=>$row['precio'],
				"fechacompra"=>$row['fechacompraf'],
				"dias"=>$row['dias'],
				"subtotal"=>$row['subtotal'],
			   "proveedor"=>$row['proveedor'],
			   "fecha_ingreso"=>$row['fecha_ingreso'],
			   "codproducto"=>$id,
			 
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
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='delDetalle'){
    // delete a la base de datos usuarios
	$coddetalle=$_POST['coddetalle'];

	$invproducto->delete_detalle($coddetalle);
    echo "Se elimino el registro.";	

}else if(!empty($_POST['accion']) and $_POST['accion']=='expSuministros'){
    // delete a la base de datos usuarios
	$tipo=$_POST['tipo'];
	$codtipo=$_POST['codtipo'];
	$descripcion=$_POST['descripcion'];
	
	if(!empty($_POST['fechai']))
		$fechai=formatdatedos($_POST['fechai']);
	
	if(!empty($_POST['fechaf']))
		$fechaf=formatdatedos($_POST['fechaf']);
	
	$searchQuery = " and inv_motivo.tipo='i'";
	
	if(!empty($descripcion))
		$searchQuery.=" and  ( producto like '%$descripcion%' or  codigo like '%$descripcion%')";
	
	if(!empty($codtipo))
 		$searchQuery.=" and  t.codtipo=$codtipo ";
	
	if(!empty($tipo))
		$searchQuery.=" and  categoria='$tipo' ";
	
	if(!empty($fechai))
 		$searchQuery.=" and  to_days(tr.fecha) >= to_days('$fechai') ";
	
	if(!empty($fechaf))
 		$searchQuery.=" and  to_days(tr.fecha) <= to_days('$fechaf') ";
	
	## Fetch records
	$columnName=" p.fecha_ingreso ";
	$columnSortOrder=" desc ";
	$row=0;
	$rowperpage=10000;
	$data_OF=$invproducto->select_suministros($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	include("../vista/invproductos/exporta_suministros.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='expAsigna'){
    // delete a la base de datos usuarios
	$tipo = $_POST['tipo'];
	## Read value
	$descripcion = $_POST['descripcion'];
	$serie = $_POST['serie'];
	$codtipo = $_POST['codtipo'];
	$codusuario = $_POST['codusuario'];
	$clase = $_POST['clase'];
	$ubicacion = $_POST['ubicacion2'];
	
	$codarea = $_POST['codarea'];
	$codsede = $_POST['codsede'];
	
	if(!empty($_POST['fechai']))
		$fechai = formatdatedos($_POST['fechai']);
	
	## Fetch records
	$columnName=" producto";
	$columnSortOrder=" desc ";
	
	if($tipo=='a')
		$searchQuery = " and inv_motivo.tipo='z' and ifnull(d.fecharetiro,'')='' ";
	else
		$searchQuery = " and inv_motivo.tipo='x' and ifnull(d.fecharetiro,'')='' ";
		
	if(!empty($descripcion))
		$searchQuery.=" and  ( producto like '%$descripcion%' or modelo like '%$descripcion%' or serief like '%$descripcion%' or imei like '%$descripcion%' or numtelefono like '%$descripcion%' or codigo like '%$descripcion%')";
	
	if(!empty($serie))
		$searchQuery.=" and  (  serief = '$serie')";
	
	if(!empty($ubicacion))
		$searchQuery.=" and  (  ubicacion = '$ubicacion')";
	
	if(!empty($codtipo))
		$searchQuery.=" and  inv_producto.codtipo=$codtipo ";
	
	if(!empty($tipo))
		$searchQuery.=" and  inv_tipo.categoria='$tipo' ";
	
	if(!empty($codarea))
		$searchQuery.=" and  inv_area.codarea='$codarea' ";
	
	if(!empty($codsede))
		$searchQuery.=" and  inv_sede.codsede='$codsede' ";
	
	if(!empty($fechai))
	$searchQuery .= " and  to_days(t.fecha)=to_days('$fechai') ";




	$datechk="";	
	if(!empty($codusuario))
		$searchQuery.=" and  t.coddestino=$codusuario ";
	
	if(!empty($clase))
		$searchQuery.=" and  inv_producto.clase='$clase' ";
	
	$row=0;
	$rowperpage=10000;
	$data_OF=$invproducto->select_movimento($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage);
	
	include("../vista/invproductos/exporta_asigna.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='index_ingegr_data_con'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$codtipo = $_POST['codtipo'];
	$estado = $_POST['estado'];
	$tipo = $_POST['tipo'];
		
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	
	
	$columnName=" producto";
	$columnSortOrder=" desc ";
	
	if(!empty($_POST['columns'][$columnIndex]['data']))
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	if(!empty($_POST['order'][0]['dir']))
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	## Search  oculto
	$searchQuery = " AND categoria='a'  ";

	## Total number of records without filtering
	$data_maxOF=$invproducto->select_total_suministros_repor($searchQuery,$having);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and   ( codigo like '%$descripcion%' or producto like '%$descripcion%' )  ";
	
	if(!empty($codtipo))
		$searchQuery.=" and  p.codtipo=$codtipo ";
	
	$having="";
	if(!empty($estado) and $estado=='CRITICO')
		$having.=" having stock_min>=cantidad ";
	
	if(!empty($estado) and $estado=='SUFICIENTE')
		$having.="  having stock_min<cantidad  ";
	
	## Total number of record with filtering
	$data_maxOF2=$invproducto->select_total_suministros_repor($searchQuery,$having);
	$totalRecordwithFilter = $data_maxOF2['total'];

	## Fetch records
	$data_OF=$invproducto->select_suministros_repor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$having);
	
	//print_r($data_OF);
    $data = array();
	if(!empty($data_OF)){
		foreach($data_OF as $row) {

			$id=$row['codproducto'];
			$estado='SUFICIENTE';
			if($row['stock_min']>=$row['cantidad'])
				$estado='CRITICO';
			
			$edita="<button type='button' id='estproy_". $id ."_$tipo'  class='btn  btn_repproducto'><i class='fas fa-print'></i> </button>";
			
			$data[] = array( 
			   "producto"=>str_replace('"','',json_encode($row['producto'],JSON_UNESCAPED_UNICODE)),
			   "codigo"=>$row['codigo'],
			   "tipodsc"=>$row['tipodsc'],
			   "estado"=>$estado,
			   "umedida"=>$row['umedida'],
			   "codproducto"=>$id,
			   "stock_min"=>$row['stock_min'],
			   "cantidad"=>$row['cantidad'],
			   "fechaadq"=>$row['fechaadq'],
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
	
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='expConsumible'){
    // delete a la base de datos usuarios
	$descripcion = $_POST['descripcion'];
	$codtipo = $_POST['codtipo'];
	$estado = $_POST['estado'];
	$tipo = $_POST['tipo'];
	
	
	## Fetch records
	$columnName=" producto";
	$columnSortOrder=" desc ";
	
	$searchQuery = " AND categoria='a'  ";

	if(!empty($descripcion))
		$searchQuery.=" and   ( codigo like '%$descripcion%' or producto like '%$descripcion%' )  ";
	
	if(!empty($codtipo))
		$searchQuery.=" and  p.codtipo=$codtipo ";
	
	$having="";
	if(!empty($estado) and $estado=='CRITICO')
		$having.=" having stock_min>=cantidad ";
	
	if(!empty($estado) and $estado=='SUFICIENTE')
		$having.="  having stock_min<cantidad  ";
	
	$row=0;
	$rowperpage=10000;
	$data_OF=$invproducto->select_suministros_repor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$having);
	
	include("../vista/invproductos/exporta_suministros_kardex.php");	

}else if(!empty($_POST['accion']) and $_POST['accion']=='busc_codigo'){
 	$codigo=$_POST['codigo'];
	$res_data=$invproducto->buscarcodigo_producto($codigo);
	if(!empty($res_data['codigo']))
		echo "Se encuentra ya registrado este codigo.";
	else
		echo ""; 
	
}


?>
