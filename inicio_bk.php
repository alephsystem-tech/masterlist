<?php 
include("com/valSession.php");
$folder="1";
include("com/db.php");
include("com/variables.php");
?>

<!DOCTYPE html>
<html>
<head>
  <!--<meta charset="iso-8859-1">-->
  
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link href="assets/img/favicon.png" type="image/x-icon" rel="icon" />
  <link href="assets/img/favicon.png" type="image/x-icon" rel="shortcut icon" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  	
  <meta http-equiv="Content-Security-Policy" content="default-src *;img-src 'self' data:; style-src * 'self' 'unsafe-inline'; script-src * 'self' 'unsafe-inline' 'unsafe-eval' http://www.google.com">
  
  <title>MASTER PLANNER </title>
  <link href="assets/plugins/jquery-ui/jquery-ui.css" rel="stylesheet" />
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="assets/dist/css/ionicons.min.css">
  <!-- Tempusdominus Bbootstrap 4 -->
  <link rel="stylesheet" href="assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css?v=2">
  <link rel="stylesheet" href="assets/css/style.css?v=7">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="assets/plugins/daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="assets/plugins/datetime/bootstrap-datetimepicker.css">


 <!-- tipo de letra -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;700&display=swap" rel="stylesheet">
  <!-- summernote -->
  <link rel="stylesheet" href="assets/plugins/summernote/summernote-bs4.css">

    <!-- Bootstrap4 Duallistbox -->
  <link rel="stylesheet" href="assets/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">

  <!-- Select2 -->
  <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

  <!-- DataTables -->
  <link rel="stylesheet" href="assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- fullCalendar -->
  <!--
  <link rel="stylesheet" href="assets/plugins/fullcalendar/main.min.css">
  <link rel="stylesheet" href="assets/plugins/fullcalendar-daygrid/main.min.css">
  <link rel="stylesheet" href="assets/plugins/fullcalendar-timegrid/main.min.css">
  <link rel="stylesheet" href="assets/plugins/fullcalendar-bootstrap/main.min.css">
-->


  <!-- Theme style -->
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">  
    <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

	<!--<script src="assets/plugins/jquery/jquery.min.js"></script>-->
	
	<link rel="stylesheet" href="assets/css/fullcalendar.css">
     
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>  
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>  
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>  
	<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.min.js"></script>  
	<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/locale/es.js"></script> 

    
	    <style>
        .modal-header, h4, .close {
            background-color: #666666;
            color:white !important;
            text-align: center;
            font-size: 30px;
        }
        .modal-footer {
            background-color: #f9f9f9;
        }

		.rounded {
			border-radius:.25rem!important
		}

		.min-height {
			min-height:650px
		}
		.table-condensed{
		  font-size: 11px;
		   padding-right: 3px;
		   padding-left: 3px;
		}
		
		.table-condensed9{
		  font-size: 9px;
		   padding-right: 1px;
		   padding-left: 1px;
		}
		
		.table-condensed10{
		  font-size: 10px;
		   padding-right: 1px;
		   padding-left: 1px;
		}
		
		.size_min td{
		  font-size: 11px;
		   padding-right: 1px;
		   padding-left: 1px;
		}	
		.table-condensed td{
		   padding-right: 3px;
		   padding-left: 3px;
		}
		
		.table-condensed9 td{
		   padding-right: 1px;
		   padding-left: 1px;
		}

		.table-condensed9 th{
		   padding-right: 1px;
		   padding-left: 1px;
		}
		
		.table-condensed10 td{
		   padding-right: 1px;
		   padding-left: 1px;
		}

		.table-condensed10 th{
		   padding-right: 1px;
		   padding-left: 1px;
		}
		
		.table-msize{
		  font-size: 11px;
		   padding-right: 1px;
		   padding-left: 1px;
		}
		
		.cuperbar{
			/*background-color:#586f70;*/
			background: linear-gradient(to bottom right, #697B7D, #A7B6B7) !important;
			font-size:12px !important;
		}
		
		.cuperblue{
			background-color:#1b1e42;
		}
		
		.cupertop{
			background-color:#39c1d9 !important;
		}
		
		.link-cupe:hover{
			background-color: #1b1e42;
		}
		
		.btnviewDet{
			font-size:10px;
			margin:2px;
		}
		
		.vieDetCuacomer{
			font-color:orange;
		}
		
		.cursor-pointer{
		  cursor: pointer;
		  font-color:blue;
		}
		
		.container {
			overflow-x: auto;
			white-space: nowrap;
		}
		
		#ui-datepicker-div {
			z-index: 10000 !important;
		}
		
		.lb-sm {
			font-size: 11px;
		}

		.hideDiv
		{
			display:none;
		}
		
		.border_focus{
		  border:1px solid red !important;
		}
    </style>

	
</head>
<body class="hold-transition sidebar-mini sidebar-collapse layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  
  <nav class="main-header navbar navbar-expand navbar-white navbar-light cupertop">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
	   <?php if($_SESSION['tipohome']=='A'){?>
		  <li class="nav-item d-none d-sm-inline-block">
			<a href="javascript:inicio('<?php echo $_SESSION['tipohome']?>');" class="nav-link"><?php echo $lang_dashboard1?></a>
		  </li>
	  <?php } ?>
	  <?php if($_SESSION['tipohome']=='C'){?>
		  <li class="nav-item d-none d-sm-inline-block">
			<a href="javascript:inicio('<?php echo $_SESSION['tipohome']?>');" class="nav-link"><?php echo $lang_dashboard2?></a>
		  </li>
	  <?php } ?>
	  <?php if($_SESSION['tipohome']=='A'){?>
	   <li class="nav-item d-none d-sm-inline-block">
        <a href="javascript:inicio('C');" class="nav-link"><?php echo $lang_dashboard2?></a>
      </li>
	  <?php } ?>
	  
      <li class="nav-item d-none d-sm-inline-block">
        <a href="javascript:salir();" class="nav-link"><?php echo $lang_homes[14]?></a>
      </li>
    </ul>

    <!-- SEARCH FORM -->
    <form class="form-inline ml-3">
      <div class="input-group input-group-sm">
        <input class="form-control form-control-navbar" type="search" placeholder="<?php echo $lang_buscar?>" aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-navbar" type="submit">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </form>
	<?php if(file_exists('assets/img/banderitas/banderita_'.$_SESSION['id_pais'].'.png')){?>
	<img src="assets/img/banderitas/banderita_<?php echo $_SESSION['id_pais']?>.png" width="60" height="32">
    <?php }?>
	
	<?php if(!empty($_SESSION['azuread'])){
		include("modelo/prg_usuario_modelo.php");
		$usuario=new prg_usuario_model();
		$azuread=$_SESSION['azuread'];
		$sess_id_pais=$_SESSION['id_pais'];
		//$azuread="lcorreia@pcugroup.com";
		$result=$usuario->quepais($azuread);
		?>
	<select name="id_pais_change" id="id_pais_change" class="form-control form-control-sm select2" 
			style="width: 200px;" onChange="js_changeLoginPais();">
		<?php foreach($result as $row){?>
			<option value="<?php echo $row['id_pais']?>" <?php if($row['id_pais']==$sess_id_pais) echo "selected"?> ><?php echo $row['nombre']?></option>
		<?php } ?>
	</select>
    <?php }?>
	
	<!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Messages Dropdown Menu -->
      
        <?php // include("com/box_alert1.php")?>

      <!-- Notifications Dropdown Menu -->
        <?php // include("com/box_alert2.php")?>

	  <!--
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
	  -->
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4 cuperbar">
    <!-- Brand Logo -->
    <a href="javascript:inicio('<?php echo $_SESSION['tipohome']?>')" class="brand-link">
      <img src="assets/img/cuperu.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
           style="opacity: .8">
      <span class="brand-text font-weight-light"><b>MASTER</b>PLANNER</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex" style="border-bottom: 1px solid #fff !important;">
        <div class="image">
		<?php 
		
			if($_SESSION['foto']!='' and is_file("archivos/auditorFoto/".$_SESSION['foto'])) 
				$rutaFoto="archivos/auditorFoto/".$_SESSION['foto'];
			else $rutaFoto="assets/dist/img/user2-160x160.jpg";
		?>
          <img src="<?php echo $rutaFoto?>" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="javascript:js_changeClave()"  class="d-block"><?php echo $_SESSION['fullname']?></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
		<nav class="mt-2 " >
			  <!-- Add icons to the links using the .nav-icon class
		   with font-awesome or any other icon font library -->
			<ul class="nav nav-pills nav-sidebar flex-column " data-widget="treeview" id="divmenu" role="menu" data-accordion="false">
				<?php //include("vista/menu.php")?>	
			</ul>
		</nav>

		
       
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
    
  <div class="content-wrapper">
	<div id="results"></div>
	
	 <div class="content-header">
      <div class="container-fluid">
		<div class="alert" id="resp"> </div>

	  </div>
	  </div>
	  
	  <div id="myModalInd" class="modal" role="dialog"></div>
	  
   
	

  </div>

	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModal" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-body">
					<img src="assets/img/avisos/Comunicado-Master-Planner.png"  class="img-thumbnail">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary"  data-dismiss="modal">Entendido</button>
				</div>
			</div>
		</div>
	</div>

  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2021 <a href="http://adminlte.io">CUPERU.MASTERPLANNER</a>.</strong>
    <?php echo $lang_homes[15]?>
    <div class="float-right d-none d-sm-inline-block">
      <b><?php echo $lang_homes[16]?></b> 1.0.0
    </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->


<!-- jQuery UI 1.11.4 -->
<!--<script src="assets/plugins/jquery-ui/jquery-ui.min.js"></script>-->
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- daterangepicker -->

<!--<script src="assets/plugins/moment/moment.min.js"></script>-->

<script src="assets/plugins/daterangepicker/daterangepicker.js"></script>
<script src="assets/plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
  
<script src="assets/plugins/datetime/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.4.1/jquery.maskedinput.min.js"></script>




<!-- Tempusdominus Bootstrap 4 -->
<script src="assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="assets/plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="assets/dist/js/adminlte.js"></script>

<!-- DataTables -->
<script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script src="assets/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/plugins/datatables-buttons/js/buttons.html5.js"></script>
<!-- Select2 -->
<script src="assets/plugins/select2/js/select2.full.min.js"></script>

<!-- bs-custom-file-input -->
<script src="assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>

<script src="assets/plugins/alerts/jquery.alerts.js"></script>
<link href="assets/plugins/alerts/jquery.alerts.css" rel="stylesheet">
<script src="assets/plugins/alerts/jquery-confirm.min.js"></script>
<link rel="stylesheet" href="assets/plugins/alerts/jquery-confirm.min.css">


<script src="assets/plugins/fullcalendar/main.min.js"></script>
<script src="assets/plugins/fullcalendar-daygrid/main.min.js"></script>
<script src="assets/plugins/fullcalendar-timegrid/main.min.js"></script>
<script src="assets/plugins/fullcalendar-interaction/main.min.js"></script>
<script src="assets/plugins/fullcalendar-bootstrap/main.min.js"></script>

<!-- ChartJS -->
<script src="assets/plugins/chart.js/Chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>
<!-- AdminLTE for demo purposes -->
<script src="assets/dist/js/demo.js"></script>

<?php
if($_SESSION['id_pais']=='eng' or $_SESSION['id_pais']=='Mal' or $_SESSION['id_pais']=='pet' or $_SESSION['id_pais']=='san' or $_SESSION['id_pais']=='can'){ ?>
	<script src='assets/js/js_idioma_en.js?v=5' type='text/javascript'></script>
<?php }else if($_SESSION['id_pais']=='bra' or $_SESSION['id_pais']=='POR'){ ?>
	<script src='assets/js/js_idioma_br.js?v=5' type='text/javascript'></script>
<?php }else{ ?>
	<script src='assets/js/js_idioma_es.js?v=5' type='text/javascript'></script>
<?php } ?>
	

<script src="assets/js/js_funciones.js?v=1" type="text/javascript"></script>
<script src="assets/js/inicio.js?v=9" type="text/javascript"></script>
<script src="assets/js/prg_usuario.js?v=4" type="text/javascript"></script>
<script src="assets/js/prg_auditoractividad.js?v=20" type="text/javascript"></script>
<script src="assets/js/mantenimientos.js?v=24" type="text/javascript"></script>
<script src="assets/js/lab_resultado.js?v=12" type="text/javascript"></script>
<script src="assets/js/etiqueta.js?v=8" type="text/javascript"></script>
<script src="assets/js/tc_datos.js?v=10" type="text/javascript"></script>
<script src="assets/js/prg_proyecto_programa.js?v=37" type="text/javascript"></script>
<script src="assets/js/prg_auditor.js?v=33" type="text/javascript"></script>
<script src="assets/js/prg_proyectoactividad.js?v=8" type="text/javascript"></script>
<script src="assets/js/prg_proyecto.js?v=20" type="text/javascript"></script>
<script src="assets/js/prg_proyecto_tc.js?v=6" type="text/javascript"></script>
<script src="assets/js/prg_calendario.js?v=9" type="text/javascript"></script>
<script src="assets/js/lst_listaintegrada.js?v=7" type="text/javascript"></script>
<script src="assets/js/kpi.js?v=37" type="text/javascript"></script>
<script src="assets/js/reportes_cal.js?v=22" type="text/javascript"></script>
<script src="assets/js/reportes_proy.js?v=31" type="text/javascript"></script>

<script src="assets/plugins/autocomplete/bootstrap-autocomplete.min.js"></script>
<script src="assets/plugins/jquery.table2excel.js"></script>

<script>

	// $(document).ready(function(){
	// 	$('#myModal').modal();
	// });

		inicio('<?php echo $_SESSION['tipohome']?>');
		
		function js_changeLoginPais(){
			id_pais_change=$('#id_pais_change').val();
			 var parametros = {
				"id_pais" : id_pais_change,
				"accion" : "azuread",
			};

			$.confirm({
				title: var_confirmar,
				content: var_cambiarpais,
				buttons: {
					confirm: function () {
						$.ajax({                        
							type: "POST",                 
							url: 'controlador/usuario.php',                     
							data: parametros,
							success: function(response)             
							{
								setTimeout(function(){
									$(location).attr('href', 'inicio.php');
								}, 1000);
								
							}
						});
					},
					cancel: function () {
						// $('#resp').html('Accion cancelada');
					}
				}
			});
		}
</script>	


</body>
</html>
