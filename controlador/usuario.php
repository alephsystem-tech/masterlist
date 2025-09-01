<?php
include("../com/db.php");
include("../com/variables.php");
include("../modelo/prg_usuario_modelo.php");
include("../modelo/prg_rol_modelo.php");

$usuario=new prg_usuario_model();
$rol=new prg_rol_model();

$sess_codusuario=$_SESSION['codusuario'];
$ip=$_SERVER['REMOTE_ADDR'];
$usuario_name=$_SESSION['usuario'];



if(!empty($_POST['accion']) and $_POST['accion']=='login'){
	
	$login=$_POST['usuario'];
	$password=$_POST['password'];
	$id_pais=$_POST['id_pais'];
	$result=$usuario->login($login,$password,$id_pais);
	
    if(!empty($result)){
		$datarol=$rol->selec_one_rol($result['id_rol']);
	//	$_SESSION['azuread']=$result['azuread'];
        $_SESSION['usuario']=$login;
		$_SESSION['codusuario']=$result['id_usuario'];
		$_SESSION['id_auditor']=$result['id_auditor'];
		$_SESSION['id_pais']=$result['id_pais'];
		$_SESSION['fullname']=$result['nombres'];
		$_SESSION['id_rol']=$result['id_rol'];
		$_SESSION['foto']=$result['foto'];
		$_SESSION['tipohome']=$datarol['tipohome'];

        header("Location: ../inicio.php");
        die();
    }else{
        header("Location: ../index.php?v1");
        die();
    }

}

else if(!empty($_POST['accion']) and $_POST['accion']=='azuread'){
	
	$email=$_SESSION['azuread'];
	//$email="lcorreia@pcugroup.com";
	$id_pais=$_POST['id_pais'];
	$result=$usuario->login_azuread($email,$id_pais);
	
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
    }
}else if(!empty($_POST['accion']) and $_POST['accion']=='logout'){
	 
	 if(!empty($_SESSION['sessionkey']))
		 $url="https://login.microsoftonline.com/common/wsfederation?wa=wsignout1.0";
	 else
		 $url="index.php?v1";
	 session_destroy();
	 
	 echo $url;
     //header("Location: ../index.php?v1");
     //die();
}


?>
