<?php
 if(empty($_GET['novalsession']))
//	include("valSession.php");

 $server_mail="email.tmsnet.org";
 $puerto_mail="2705";
 
 $user_mail = "masterplanner@controlunion.com";
 $clave_mail = "4l3rt4$";
 
 $user_mailcobra = "cobranzas@controlunion.com";
 $clave_mailcobra = "4l3rt4$";
 
 $user_mailcobracus = "cobranzascus@controlunion.com";
 $clave_mailcobracus = "4l3rt4$";
 
 $name_mail = "Sistema de Alertas CUPERU";

 $user_mail2 = "masterplanner@controlunion.com";
 $name_mail2 = "Sistema de Alertas";


 $user_mail3 = "cobranzas@tlr.pe";
 $name_mail3 = "TLR INTERNACIONAL LABORATORIES";
 
  if(empty($folder))
	$folder="../";
  else
	$folder="";

  if(empty($noidioma) and !empty($_SESSION['id_pais'])){ 
	if($_SESSION['id_pais']=='eng' or $_SESSION['id_pais']=='Mal' or $_SESSION['id_pais']=='pet' or $_SESSION['id_pais']=='san' or $_SESSION['id_pais']=='can')
		include($folder."assets/lang/language_eng.php");
	else if($_SESSION['id_pais']=='bra' or $_SESSION['id_pais']=='POR')
		include($folder."assets/lang/language_por.php");
	else
		include($folder."assets/lang/language_esp.php");
			
 }
 
 
	// dejar file ftp en server mexico
	//$ftp_server="proveedoresespana.controlunion.com";
	$ftp_server="217.71.201.232";
	//$ftp_user_name="controlmx";
	//$ftp_user_pass="%kZ0bp87";

	$ftp_user_name="admin_controlmx";
	$ftp_user_pass="KiBDrfqm6lP3uqtl";
?>