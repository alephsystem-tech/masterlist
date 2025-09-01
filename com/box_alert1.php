<?php
include("com/db.php");

include("modelo/home_modelo.php");
$home=new home_model();
$resServicios=$home->select_servicios();

?>
<li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge"><?php echo  count($resServicios)?></span>
        </a>
		<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          
		<?php foreach($resServicios as $row){ ?>  
		  <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <!--<img src="assets/dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">-->
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  <?php echo $row['ordenserviciomostrar']?> (<?php echo $row['fecha']?>)
                   <!--<span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>-->
                </h3>
                <p class="text-sm"><?php echo $row['f_cliente']?></p>
                <!--<p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
				-->
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
		<?php } ?> 


         <!-- <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>-->
        </div>
</li>		