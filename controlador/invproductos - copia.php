<?php
include("../com/db.php");
include("../com/variables.php");
include("../com/funciones.php");

include("../modelo/inv_producto_modelo.php");

$invproducto=new inv_producto_model();

// VARIABLES DE SESSION
$sess_codusuario=$_SESSION['codusuario'];
$sess_codauditor=$_SESSION['id_auditor'];
$sess_codpais=$_SESSION['id_pais'];
$sess_codrol=$_SESSION['id_rol'];

$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];

//***********************************************************

if(!empty($_POST['accion']) and $_POST['accion']=='index'){
	//**********************************
	// mostrar index de calendario
	//**********************************
	$usuario_res=$invproducto->selec_usuarios($sess_codpais);
	$tipo_res=$invproducto->selec_tipodsc();
	
    include("../vista/invproductos/index.php");	


}else if(!empty($_POST['accion']) and $_POST['accion']=='index_producto'){
	
	//***********************************************************
	// funcion buscador tabla lista actividades
	//***********************************************************
	
	## Read value
	$descripcion = $_POST['descripcion'];
	$codusuario = $_POST['codusuario'];
	$codtipo = $_POST['codtipo'];
	
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
	$searchQuery = " ";

	## Total number of records without filtering
	$data_maxOF=$invproducto->selec_total_producto($searchQuery);
	$totalRecords = $data_maxOF['total'];

	if(!empty($descripcion))
		$searchQuery.=" and  ( producto like '%$descripcion%' or  serie like '%$descripcion%' or modelo like '%$descripcion%')";
	
	if(!empty($codusuario))
		$searchQuery.=" and  coddestino=$codusuario ";
	
	if(!empty($codtipo))
		$searchQuery.=" and  inv_producto.codtipo=$codtipo ";

	
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
			$codtransaccion=$row['codtransaccion'];
			
			$edita="<button type='button' id='estproy_". $id ."'  class='btn  btn_ediinvproducto'><i class='fas fa-edit'></i> </button>";
			$elimina="<button type='button' id='estproy_". $id ."'  class='btn  btn_eliinvproducto'><i class='fas fa-trash'></i> </button>";
			$asigna="<button type='button' id='estproy_". $id ."'  class='btn  btn_asiginvproducto'><i class='fas fa-user'></i> </button>$row[usuariodestino]";
			
			$desasigna="";
			if($row['coddestino']!=''){
				$desasigna="<button type='button' id='estproy_". $codtransaccion ."'  class='btn  btn_desasiginvpro'><i class='fas fa-reply'></i>";
				$elimina="";
			}
		
		   $data[] = array( 
			   "producto"=>str_replace('"','',json_encode($row['producto'],JSON_UNESCAPED_UNICODE)),
			   "descripcion"=>str_replace('"','',json_encode($row['descripcion'],JSON_UNESCAPED_UNICODE)),
			   "modelo"=>str_replace('"','',json_encode($row['modelo'],JSON_UNESCAPED_UNICODE)),
			   "serie"=>str_replace('"','',json_encode($row['serie'],JSON_UNESCAPED_UNICODE)),
			   "usuariodestino"=>$asigna,
			   "desasigna"=>$desasigna,
			   "tipodsc"=>$row['tipodsc'],
			   "marca"=>$row['marca'],
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

		 
}else if(!empty($_POST['accion']) and $_POST['accion']=='editinvproducto'){
	$codproducto="";
	$data_tipo=$invproducto->selec_tipodsc();
	$data_marca=$invproducto->selec_marca();
	
	if(!empty($_POST['codproducto']))
		$data_res=$invproducto->selec_one_producto($_POST['codproducto']);

    include("../vista/invproductos/frm_detalle.php");
	
}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_detProducto'){
    // proceso update a la base de datos usuarios
	
	$producto=$_POST['producto'];
	$descripcion=$_POST['descripcion'];
	$codmarca=$_POST['codmarca'];
	$codtipo=$_POST['codtipo'];
	$modelo=$_POST['modelo'];
	$serie=$_POST['serie'];
	
	$host=$_POST['host'];
	$hd1=$_POST['hd1'];
	$procesador=$_POST['procesador'];
	$ram=$_POST['ram'];

	if(empty($_POST['codproducto']))
		$codproducto=$invproducto->insert_producto($producto,$descripcion,$codmarca,$codtipo,$modelo,$serie,$ram,$procesador,$host,$hd1,$sess_codpais,$usuario_name,$ip);
	else{
		$codproducto=$_POST['codproducto']; // id
		$invproducto->update_producto($codproducto,$producto,$descripcion,$codmarca,$codtipo,$modelo,$serie,$ram,$procesador,$host,$hd1,$usuario_name,$ip);
	}	
	 echo "Se actualizo el registro";

}else if(!empty($_POST['accion']) and $_POST['accion']=='asiginvproducto'){
	$codproducto=$_POST['codproducto'];
	
	$data_usuario=$invproducto->selec_usuarios($sess_codpais);
	$data_res=$invproducto->selec_one_producto($codproducto);
	
	$data_tra=$invproducto->selec_transacc_producto($codproducto);
	
    include("../vista/invproductos/frm_asignacion.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_asigProducto'){
    // proceso update a la base de datos usuarios
	
	$codproducto=$_POST['codproducto'];
	$coddestino=$_POST['coddestino'];
	$descripcion=$_POST['descripcion'];
	$fecha=formatdatedos($_POST['fechai']);

	$so=$_POST['so'];
	$office=$_POST['office'];
	$dominio=$_POST['dominio'];
	
	$antivirus="NO";
	$onedrive="NO";
	$monitor="NO";
	$mouse="NO";
	$audifonos="NO";
	
	if(!empty($_POST['antivirus']))
		$antivirus="SI";

	if(!empty($_POST['onedrive']))
		$onedrive="SI";

	if(!empty($_POST['monitor']))
		$monitor="SI";

	if(!empty($_POST['mouse']))
		$mouse="SI";

	if(!empty($_POST['audifonos']))
		$audifonos="SI";


	if(empty($_POST['codtransaccion']))
		$codtransaccion=$invproducto->insert_transaccion($codproducto,$coddestino,$descripcion,$fecha,$so,$office,$dominio,$antivirus,$onedrive,$monitor,$mouse,$audifonos,$sess_codpais,$usuario_name,$ip);
	else{
		$codtransaccion=$_POST['codtransaccion']; // id
		$invproducto->update_transaccion($codtransaccion,$codproducto,$coddestino,$descripcion,$fecha,$so,$office,$dominio,$antivirus,$onedrive,$monitor,$mouse,$audifonos,$usuario_name,$ip);
	}	
	
	$invproducto->update_transaccion_usuario($codtransaccion);
	
	 echo "Se asigno el producto.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='desasiginvproducto'){
	$codtransaccion=$_POST['codtransaccion'];
	
	$data_usuario=$invproducto->selec_usuarios($sess_codpais);
	
	$data_tra=$invproducto->selec_one_transacccion($codtransaccion);
	$codproducto=$data_tra['codproducto'];
	$data_res=$invproducto->selec_one_producto($codproducto);
	
    include("../vista/invproductos/frm_desasignacion.php");

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_desasigProducto'){
    // proceso update a la base de datos usuarios
	
	$codtransaccion=$_POST['codtransaccion'];
	$descripcion=$_POST['descripcion'];
	$fecha=formatdatedos($_POST['fechai']);
	$invproducto->update_transaccion_des($codtransaccion,$descripcion,$fecha,$usuario_name,$ip);
	
	 echo "Se retiro el producto.";

}else if(!empty($_POST['accion']) and $_POST['accion']=='proc_desasigProducto'){
    // proceso update a la base de datos usuarios
	
	$codtransaccion=$_POST['codtransaccion'];
	$descripcion=$_POST['descripcion'];
	$fecharetiro=formatdatedos($_POST['fechai']);
	$invproducto->update_transaccion_des($codtransaccion,$descripcion,$fecharetiro,$usuario_name,$ip);
	
	 echo "Se retiro el producto.";

	 
}else if(!empty($_POST['accion']) and $_POST['accion']=='delinvProducto'){
    // delete a la base de datos usuarios
	$codproducto=$_POST['codproducto']; 
    $invproducto->delete_producto($codproducto);
    echo "Se elimino el registro.";
	
//******************************************************************	
// 2. reporte de kardex	
//******************************************************************
}else if(!empty($_POST['accion']) and $_POST['accion']=='index_kardex'){
	//**********************************
	// mostrar index reporte
	//**********************************
	$usuario_res=$invproducto->selec_usuarios($sess_codpais);
	$tipo_res=$invproducto->selec_tipodsc();
	$data_marca=$invproducto->selec_marca();
	
    include("../vista/invproductos/index_kardex.php");	
	
}


?>
