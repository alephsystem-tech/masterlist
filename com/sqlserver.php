<?php

	$connectionInfo = array( "Database"=>"CUPERU",
                         "Encrypt"=>true,
                         "TrustServerCertificate"=>true,
			 "UID"=>"UsrMasterPlanner2",
                         "PWD"=>"Miraflores202514#",
			 "ReturnDatesAsStrings"=>true,
			"ConnectionPooling"=>false,
			 "CharacterSet"=> 'UTF-8');
			$serverName = "10.0.97.14"; //serverName\instanceName	
	$conn  = sqlsrv_connect( $serverName, $connectionInfo);

	if($conn) {
		// echo "Conexion establecida.<br />";
	}else{
		 echo "Conexion no se pudo establecer.<br />";
		 die( print_r( sqlsrv_errors(), true));
	}

?>
