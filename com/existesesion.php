<?php
    if(empty($_SESSION['usuario'])){
         header("Location: index.php?v1");
        die();
    }
?>