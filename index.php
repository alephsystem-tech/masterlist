<?php 
$noidioma="1";
include("com/db.php");
include("modelo/prg_pais_modelo.php");
$pais=new prg_pais_model();
$pais_res=$pais->selec_paises();
echo date('Y-m-d H:i:s')
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>CUPERU MASTER PLANNER</title>
    <link href="assets/img/favicon.png" type="image/x-icon" rel="icon" />
	<link href="assets/img/favicon.png" type="image/x-icon" rel="shortcut icon" />

  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <!--link rel="stylesheet" href="assets/dist/css/ionicons.min.css"-->
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <!--link rel="stylesheet" href="assets/dist/css/adminlte.min.css"-->
  <link rel="stylesheet" href="assets/css/login.css?v=5">
  <link rel="stylesheet" href="assets/css/login.min.css?v=2">
  <link rel="stylesheet" href="assets/css/icono.css?v=1">

  <!-- Google Font: Source Sans Pro -->
  <!--link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet"-->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300&display=swap" rel="stylesheet">
</head>
<body class="hold-transition login-page">
	<div class="d-lg-flex half">
		<div class="bg order-1 order-md-2" style="background-image: url('assets/img/bg_1.jpg');width: 100%;"></div>
		<br><br>
		<div class="contents order-2 order-md-1">
			<div class="container">
				<div class="row align-items-center justify-content-center">
					<div class="col-md-7">
					<h3><img width="100%" src="assets/img/logo.png" alt=""></h3>
					<p class="mb-4"><b>Master</b> <span style="font-weight: 300">PLANNER</span></p>
					<form action="controlador/usuario.php" method="post" autocomplete=off>
						<div class="form-group first mb-3">
						  <select class="form-control form-control-sm select2"  style="width: 100%; border: 2px solid #fff"required="true"  id="id_pais" name="id_pais" >
						  <option selected="selected">- - - - -- - -</option>
						  <?php foreach($pais_res as $row_tipo){?>
						  <option value="<?php echo $row_tipo['id_pais']?>"><?php echo utf8_encode($row_tipo['nombre'])?></option>
						  <?php } ?>
						  </select>   
						</div>
						<div class="form-group first">
							  <input type="usuario" name="usuario" class="form-control" placeholder="Usuario" required>
						</div>
						<div class="form-group last mb-3">
							<input type="password" name="password" class="form-control" placeholder="Contraseña" required>
						</div>
						<div class="d-flex mb-5 align-items-center">
							<label class="control control--checkbox mb-0"><span class="caption">Recordar Usuario</span>
							<input type="checkbox" checked="checked"/>
							<div class="control__indicator"></div>
							</label>
						</div>
						<input type="hidden" name="accion" value="login">
						<input type="submit" value="Iniciar Sesión" class="btn btn-block btn-primary">
						<br>
						<div align="center">
						<a href="sso.php" style="text-decoration:none" >
						<button type="button" class="btn btn-block btn-warning"><i class="fab fa-windows" aria-hidden="true"></i>  INGRESO CON PCU</button>
						</a>
						
						</div>
      </form>

          </div>
        </div>
      </div>
    </div>
  
  </div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="assets/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="assets/dist/js/adminlte.min.js"></script>

</body>
</html>