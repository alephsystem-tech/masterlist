<?php
	/*
	$link = mssql_connect('10.0.97.219', 'UsrMasterPlanner', '*Master123');
	if (!$link)	die('Unable to connect!');
	
	if (!mssql_select_db('CUPERU', $link))
	die('Unable to select database!');
	*/
 
	$connectionInfo = array( "Database"=>"CUPERU",
                         "Encrypt"=>true,
                         "TrustServerCertificate"=>true,
						 "UID"=>"UsrMasterPlanner",
                         "PWD"=>"*Master123",
						 "ReturnDatesAsStrings"=>true,
						 "ConnectionPooling"=>false,
						 "CharacterSet"=> 'UTF-8');
	$serverName = "10.0.97.219"; //serverName\instanceName	
	$conn  = sqlsrv_connect( $serverName, $connectionInfo);

	if($conn) {
	//	 echo "Conexión establecida.<br />";
	}else{
		 echo "Conexion no se pudo establecer.<br />";
		 die( print_r( sqlsrv_errors(), true));
	}

?>
