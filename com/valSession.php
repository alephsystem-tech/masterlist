<?php
if(!isset($_SESSION['usuario'])){
?>
	<script language="javascript">
		window.top.location.href = "index.php"
	</script> 
<?php 
exit();
} ?>