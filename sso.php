<?php
// Load the auth module, this will redirect us to login if we aren't already logged in.
include 'inc/auth.php';
$Auth = new modAuth();
include 'inc/graph.php';
$Graph = new modGraph();
//Display the username, logout link and a list of attributes returned by Azure AD.
$photo = $Graph->getPhoto();
$profile = $Graph->getProfile();

// si llega aqui es porque ya se logueo. recuperemos la sessiones

include("com/db.php");
include("com/variables.php");
include("modelo/prg_usuario_modelo.php");
include("modelo/prg_rol_modelo.php");
$usuario=new prg_usuario_model();
$rol=new prg_rol_model();

$email=$Auth->userName;
$result=$usuario->login_azuread($email);

	
   if(!empty($result)){

		$datarol=$rol->selec_one_rol($result['id_rol']);
		$_SESSION['azuread']=$email;
        $_SESSION['usuario']=$result['usuario'];
		$_SESSION['codusuario']=$result['id_usuario'];
		$_SESSION['id_auditor']=$result['id_auditor'];
		$_SESSION['id_pais']=$result['id_pais'];
		$_SESSION['fullname']=$result['nombres'];
		$_SESSION['id_rol']=$result['id_rol'];
		$_SESSION['foto']=$result['foto'];
		$_SESSION['tipohome']=$datarol['tipohome'];
        header("Location: inicio.php");
        die();
    }else{
        header("Location: index.php?v1");
        die();
    }
	


//var_dump($Auth);
	
//echo '<h1>Bienvenido, ' . $profile->displayName . ' (' . $Auth->userName . ')</h1>';
//echo '<h2><a href="/?action=logout">Log out</a></h2>';

?>


