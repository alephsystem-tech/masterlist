<!-- JQVMap -->
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?php echo $lang_homes[0]?></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#"><?php echo $lang_home?></a></li>
              <li class="breadcrumb-item active"><?php echo $lang_homes[1]?></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
				<a href="javascript:js_triggert('auditoractividad','index');" class="text">
                <h3><?php //echo $data_componente['total']?></h3>
                <p><?php echo $lang_homes[2]?> <br> <span style="color: transparent;">.</span></p>
              </div>
              <div class="icon">
				<h3><?php //echo $data_observacion['total']?></h3>
              </div>
              </a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
				<a href="javascript:js_triggert('kpireporte','reporte');" class="text">
                <h3><?php //echo $data_ppt['total']?><!--<sup style="font-size: 20px">%</sup>--></h3>
				<p><?php echo $lang_homes[3]?></p>
              </div>
              <div class="icon">
              </div>
              </a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
				<a href="javascript:js_triggert('calendarios','index');" class="text">
                <h3><?php //echo $data_expediente['total']?></h3>
                <p><?php echo $lang_homes[4]?><br> <span style="color: transparent;">.</span></p>
              </div>
              <div class="icon">
              </div>
              </a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
				<a href="javascript:js_triggert('proycomercial','index');" class="text" > 
                <h3><?php //echo $data_observacion['total']?></h3>
				<p><?php echo $lang_homes[5]?></p>
              </div>
              <div class="icon">
              </div>
             </a>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->
        <div class="row">
			<section class="col-lg-6 connectedSortable">
                <h3 class="card-title">
                  <i class="fas fa-chart-pie mr-1"></i>
                 <?php echo $lang_homes[6]?> <?php echo (date("Y")-1)?> - <?php echo date("Y")?>
                </h3>
                <canvas id="lineChart"></canvas>
			</section>
			<section class="col-lg-6 connectedSortable">
				<h3 class="card-title">
                  <i class="fas fa-chart-pie mr-1"></i>
                 <?php echo $lang_homes[7]?> <?php echo (date("Y")-1)?> - <?php echo date("Y")?>
                </h3>
				<canvas id="lineChart2"></canvas>
			</section>
		</div>
		<!-- Main row -->
        <div class="row">
          <!-- Left col -->
          <section class="col-lg-4 connectedSortable ">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card  min-height">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-chart-pie mr-1"></i>
                  <?php echo $lang_homes[8]?> <?php echo (date("Y")-1)?>-<?php echo date("Y")?>
                </h3>
                <div class="card-tools">

                </div>
              </div><!-- /.card-header -->
              <div class="card-body">
                <table id="TblVentas" class="table table-striped size_min" cellspacing="0" width="100%">
						<thead>
						<tr>
							<th><?php echo $lang_homes[9]?></th>
							<th>US$ <?php echo (date("Y")-1)?></th>
							<th>US$ <?php echo date("Y")?></th>
							<th class="text-nowrap">%</th>
						</tr>
						</thead>
						<tbody>
							<?php 
							$sumAnio1=0;
							$sumAnio2=0;
							if(!empty($resVentasCliente)){
							foreach($resVentasCliente as $row){
								$sumAnio1+=$row['totalpas'];
								$sumAnio2+=$row['total'];
								?>
							<tr>
								<td><?php echo $row['f_cliente']?></td>
								<td class="text-nowrap"><?php if($row['totalpas']>0) echo number_format($row['totalpas'],0)?></td>
								<td class="text-nowrap"><?php echo number_format($row['total'],0)?></td>
								<td class="text-nowrap"><?php if($row['totalpas']>0) echo number_format(($row['total']-$row['totalpas'])*100/$row['totalpas'],0)." %"?></td>
							</tr>
							<?php } 
							}?>
						</tbody>
						<tfoot>
						<tr>
							<td>Total</td>
							<td><?php echo number_format($sumAnio1,1)?></td>
							<td><?php echo number_format($sumAnio2,1)?></td>
							<td><?php if($sumAnio1>0) echo number_format(($sumAnio2-$sumAnio1)*100/$sumAnio1,0)." %"?></td>
						</tr>
						</tfoot>
				 </table>
				 <div>
					<canvas id="labelChart"></canvas>
				 </div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
            <!-- /.card -->
          </section>
		  <section class="col-lg-4 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card min-height">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-chart-pie mr-1"></i>
                  <?php echo $lang_homes[10]?> <?php echo (date("Y")-1)?>-<?php echo date("Y")?>
                </h3>
                <div class="card-tools">
                </div>
              </div><!-- /.card-header -->
              <div class="card-body">
                <table id="TblVentas" class="table table-striped size_min" cellspacing="0" width="100%">
						<thead>
						<tr>
							<th>Categoria</th>
							<th>US$ <?php echo (date("Y")-1)?></th>
							<th>US$ <?php echo date("Y")?></th>
							<th class="text-nowrap">%</th>
						</tr>
						</thead>
						<tbody>
							<?php 
							if(!empty($resVentasLab)){
								$totcatA=0;
								$totcatB=0;
								
							foreach($resVentasLab as $row){
								$aa=0;
								$ao=0;
								$anio=date("Y");
								$anio_old=date("Y") - 1;
								if(!empty($arrayCatDatos[$row['id_categoria']][$anio_old]))
									$totcatA+=$arrayCatDatos[$row['id_categoria']][$anio_old];
								if(!empty($arrayCatDatos[$row['id_categoria']][$anio]))
									$totcatB+=$arrayCatDatos[$row['id_categoria']][$anio];
								?>
							<tr>
								<td><?php echo $row['categoria']?></td>
								<td class="text-nowrap"><?php if(!empty($arrayCatDatos[$row['id_categoria']][$anio_old])) echo number_format($arrayCatDatos[$row['id_categoria']][$anio_old],1)?></td>
								<td class="text-nowrap"><?php if(!empty($arrayCatDatos[$row['id_categoria']][$anio])) echo number_format($arrayCatDatos[$row['id_categoria']][$anio],1)?></td>
								<td class="text-nowrap"><?php if(!empty($arrayCatDatos[$row['id_categoria']][$anio_old])){
									if(!empty($arrayCatDatos[$row['id_categoria']][$anio_old]))
										$ao=$arrayCatDatos[$row['id_categoria']][$anio_old];
									if(!empty($arrayCatDatos[$row['id_categoria']][$anio]))
										$aa=$arrayCatDatos[$row['id_categoria']][$anio];
									echo number_format(($aa-$ao)*100/$ao,0)." %";
									unset($ao);
									unset($aa);
									}?></td>
							</tr>
							<?php }
							}
							?>
						</tbody>
						<tfoot>
						<tr>
							<th>Total</th>
							<td><?php echo number_format($totcatA,1)?></td>
							<td><?php echo number_format($totcatB,1)?></td>
							<td><?php if($totcatA>0) echo number_format(($totcatB-$totcatA)*100/$totcatA,0)." %";?></td>
						</tr>
						</tfoot>
				 </table>
				 <div>
					<canvas id="labelChartTlr"></canvas>
				 </div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
            <!-- /.card -->
          </section>
		<section class="col-lg-4 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card min-height">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-chart-pie mr-1"></i>
                  <?php echo $lang_homes[12]?>  <?php echo (date("Y")-1)?>-<?php echo date("Y")?>
                </h3>
                <div class="card-tools">
                </div>
              </div><!-- /.card-header -->
              <div class="card-body">
                <table id="TblVentas" class="table table-striped size_min" cellspacing="0" width="100%">
						<thead>
						<tr>
							<th><?php echo $lang_homes[11]?></th>
							<th>US$ <?php echo (date("Y")-1)?></th>
							<th>US$ <?php echo date("Y")?></th>
							<th class="text-nowrap">%</th>
						</tr>
						</thead>
						<tbody>
							<?php 
							if(!empty($resVentasTC)){
							foreach($resVentasTC as $row){?>
							<tr>
								<td><?php echo namemes($row['mes'])?></td>
								<td class="text-nowrap"><?php if($row['totalpas']>0) echo number_format($row['totalpas'],0)?></td>
								<td class="text-nowrap"><?php echo number_format($row['total'],0)?></td>
								<td class="text-nowrap"><?php if($row['totalpas']>0) echo number_format(($row['total']-$row['totalpas'])*100/$row['totalpas'],0)." %"?></td>
							</tr>
							<?php }
							}
							?>
						</tbody>
					</table>
					 <div>
						<canvas id="labelChart3"></canvas>
					</div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
            <!-- /.card -->
          </section>
		</div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
          <!-- /.Left col -->
          <!-- right col (We are only adding the ID to make the widgets sortable)-->
          <!-- right col -->
        </div>
        <!-- /.row (main row) -->
		<!-- other Main row -->
    <!-- /.content -->

<script>
		var arrdata=[];
		var arrlabels=[];
		var arrdataTlr=[];
		var arrlabelsTlr=[];
		var arrdata2=[];
		var arrlabels2=[];
		var arrdata3=[];
		var arrlabels3=[];
		var arrdata0=[];
		var arrdata1=[];
		var arrdata01=[];
		var arrdata11=[];
<?php 
	if(!empty($resVentasCliente)){
	foreach($resVentasCliente as $row){ 
		$cliente=$row['project_id'];
	?>
		arrdata.push(<?php echo $row['total']/1000?>);
		arrlabels.push('<?php echo $cliente?>');
<?php	}
	}

	if(!empty($resVentasLab)){
		$anio=date("Y");
	foreach($resVentasLab as $row){ 
		$descripcionF=$row['categoria'];
		
		if(!empty($arrayCatDatos[$row['id_categoria']][$anio]))
				$totcat=$arrayCatDatos[$row['id_categoria']][$anio];
		else	
			$totcat=0;
?>
		arrdataTlr.push(<?php echo $totcat/1000?>);
		arrlabelsTlr.push('<?php echo $descripcionF?>');
<?php	}
		unset($anio);
		unset($totcat);
	}

	if(!empty($resVentasTC)){
		foreach($resVentasTC as $row){ 
		$descripcionF=namemes($row['mes']);
?>
		arrdata3.push(<?php echo $row['total']/1000?>);
		arrlabels3.push('<?php echo $descripcionF?>');
<?php	}
	}?>

<?php 
	// sentencia para no tlr
	$totalanio=0;
	$totalanio_old=0;
	// if(!empty($resVentasMesAnio)){
	if(!empty($arrayCosto1)){
		$thisyear=date("Y");
		$oldyear=date("Y")-1;
		for($i=1;$i<=12;$i++){ 
			if(!empty($arrayCosto1[$thisyear][$i])){
				$arrayAnio[$i]=round($arrayCosto1[$thisyear][$i],0);
				$totalanio+=$arrayCosto1[$thisyear][$i];
			}
			if(!empty($arrayCosto1[$oldyear][$i])){
				$arrayAnioOld[$i]=round($arrayCosto1[$oldyear][$i],0);
				$totalanio_old+=$arrayCosto1[$oldyear][$i];
			}
				
		}
		/*
		foreach($resVentasMesAnio as $row){ 
			$arrayAnio[$row['mes']]=round($row['montoanio'],0);
			$totalanio+=$row['montoanio'];
			$arrayAnioOld[$row['mes']]=round($row['montoanio_ante'],0);
			$totalanio_old+=$row['montoanio_ante'];
		} 
		*/

		for($i=1;$i<=12;$i++){ 
?>		
			arrdata0.push(<?php if(!empty($arrayAnioOld[$i])) echo $arrayAnioOld[$i]; else echo 0;?>);
			arrdata1.push(<?php if(!empty($arrayAnio[$i])) echo $arrayAnio[$i]; else echo 0;?>);	
<?php
		}
	}

	// sentencia para si tlr
	$totalanio2=0;
	$totalanio_old2=0;

	if(!empty($resVentasMesAnioTlr)){
		foreach($resVentasMesAnioTlr as $row){ 
			if($row['anio']==date("Y")){
				$arrayAnio2[$row['mes']]=round($row['total'],0);
				$totalanio2+=$row['total'];
			}

			if($row['anio']==date("Y")-1){
				$arrayAnioOld2[$row['mes']]=round($row['total'],0);
				$totalanio_old2+=$row['total'];
			}	
		} 

		for($i=1;$i<=12;$i++){ 
?>		
			arrdata01.push(<?php if(!empty($arrayAnioOld2[$i])) echo $arrayAnioOld2[$i]; else echo 0;?>);
			arrdata11.push(<?php if(!empty($arrayAnio2[$i])) echo $arrayAnio2[$i]; else echo 0;?>);	
<?php
		}
	}
?>
	//********************************************************************
	// graficos principal no tlr
	//********************************************************************
	var ctxL = document.getElementById("lineChart").getContext('2d');
	var myLineChart = new Chart(ctxL, {
	type: 'line',
	data: {
		labels: ["<?php echo $lang_nmes[1]?>", "<?php echo $lang_nmes[2]?>", "<?php echo $lang_nmes[3]?>", "<?php echo $lang_nmes[4]?>", "<?php echo $lang_nmes[5]?>", "<?php echo $lang_nmes[6]?>", "<?php echo $lang_nmes[7]?>", "<?php echo $lang_nmes[8]?>", "<?php echo $lang_nmes[9]?>", "<?php echo $lang_nmes[10]?>", "<?php echo $lang_nmes[11]?>", "<?php echo $lang_nmes[12]?>"],
		datasets: [{
			label: "US$ <?php echo number_format($totalanio_old,0)?> <?php echo caracterlimpia($lang_homes[13])?> <?php echo (date('Y')-1)?>",
			data: arrdata0,
			backgroundColor: [
			'rgba(105, 0, 132, .2)',
			],
			borderColor: [
			'rgba(200, 99, 132, .7)',
			],
			borderWidth: 2
		},
		{
			label: "US$ <?php echo number_format($totalanio,0)?> <?php echo caracterlimpia($lang_homes[13])?>  <?php echo date('Y')?>",
			data: arrdata1,
			backgroundColor: [
			'rgba(0, 137, 132, .2)',
			],
			borderColor: [
			'rgba(0, 10, 130, .7)',
			],
			borderWidth: 2
		}
		]
	},
	options: {
	responsive: true
	}
	});
	//********************************************************************
	// graficos principal si tlr
	//********************************************************************
	var ctxL2 = document.getElementById("lineChart2").getContext('2d');
	var myLineChart = new Chart(ctxL2, {
		type: 'line',
		data: {
		labels: ["<?php echo $lang_nmes[1]?>", "<?php echo $lang_nmes[2]?>", "<?php echo $lang_nmes[3]?>", "<?php echo $lang_nmes[4]?>", "<?php echo $lang_nmes[5]?>", "<?php echo $lang_nmes[6]?>", "<?php echo $lang_nmes[7]?>", "<?php echo $lang_nmes[8]?>", "<?php echo $lang_nmes[9]?>", "<?php echo $lang_nmes[10]?>", "<?php echo $lang_nmes[11]?>", "<?php echo $lang_nmes[12]?>"],
			datasets: [{
				label: "US$ <?php echo number_format($totalanio_old2,0)?> <?php echo caracterlimpia($lang_homes[13])?> <?php echo (date('Y')-1)?>",
				data: arrdata01,
				backgroundColor: [
				'rgba(105, 0, 132, .2)',
				],
				borderColor: [
				'rgba(200, 99, 132, .7)',
				],
				borderWidth: 2
			},
			{
				label: "US$ <?php echo number_format($totalanio2,0)?> <?php echo caracterlimpia($lang_homes[13])?>  <?php echo date('Y')?>",
				data: arrdata11,
				backgroundColor: [
				'rgba(0, 137, 132, .2)',
				],
				borderColor: [
				'rgba(0, 10, 130, .7)',
				],
				borderWidth: 2
			}
			]
		},
		options: {
			responsive: true
		}
	});

	//********************************************************************
	// graficos 01 clientes top survey
	//********************************************************************
	var ctxP = document.getElementById("labelChart").getContext('2d');
	var myPieChart = new Chart(ctxP, {
	  plugins: [ChartDataLabels],
	  type: 'pie',
	  data: {
		labels: arrlabels,
		datasets: [{
		  data: arrdata,
		  backgroundColor: ["#F7464A", "#46BFBD", "#FDB45C", "#949FB1", "#4D5360", "#345623", "#673214"],
		  hoverBackgroundColor: ["#FF5A5E", "#5AD3D1", "#FFC870", "#A8B3C5", "#616774", "#245623", "#273214"]
		}]
	  },
	  options: {
		responsive: true,
		legend: {
		  position: 'right',
		  labels: {
			padding: 5,
			boxWidth: 15,
			fontSize: 10
		  }
		},
		plugins: {
		  datalabels: {
			formatter: (value, ctx) => {
			  let sum = 0;
			  let dataArr = ctx.chart.data.datasets[0].data;
			  dataArr.map(data => {
				sum += data;
			  });
			  let percentage = (value * 100 / sum).toFixed(0) + "%";
			  return percentage;
			},
			color: 'white',
			labels: {
			  title: {
				font: {
				  size: '12'
				}
			  }
			}
		  }
		}
	  }
	});	

	//********************************************************************
	// graficos 01 clientes top tlr
	//********************************************************************
	var ctxP2 = document.getElementById("labelChartTlr").getContext('2d');
	var myPieChart = new Chart(ctxP2, {
	  plugins: [ChartDataLabels],
	  type: 'pie',
	  data: {
		labels: arrlabelsTlr,
		datasets: [{
		  data: arrdataTlr,
		  backgroundColor: ["#F7464A", "#46BFBD", "#FDB45C", "#949FB1", "#4D5360", "#345623", "#673214", "#4D9960", "#995623", "#423214", "#4D1160", "#991223", "#883214"],
		  hoverBackgroundColor: ["#FF5A5E", "#5AD3D1", "#FFC870", "#A8B3C5", "#616774", "#245623", "#273214"]
		}]
	  },
	  options: {
		responsive: true,
		legend: {
		  position: 'right',
		  labels: {
			padding: 5,
			boxWidth: 15,
			fontSize: 10
		  }
		},
		plugins: {

	  datalabels: {
			formatter: (value, ctx) => {
			  let sum = 0;
			  let dataArr = ctx.chart.data.datasets[0].data;
			  dataArr.map(data => {
				sum += data;
			  });
			  let percentage = (value * 100 / sum).toFixed(0) + "%";
			  return percentage;
			},
			color: 'white',
			labels: {
			  title: {
				font: {
				  size: '12'
				}
			  }
			}
		  }
		}
	  }
	});	

	//********************************************************************
	// graficos 03
	//********************************************************************
	var ctxP = document.getElementById("labelChart3").getContext('2d');
	var myPieChart = new Chart(ctxP, {
	  plugins: [ChartDataLabels],
	  type: 'pie',
	  data: {
		labels: arrlabels3,
		datasets: [{
		  data: arrdata3,
		  backgroundColor: ["#F7464A", "#46BFBD", "#FDB45C", "#949FB1", "#4D5360", "#345623", "#673214"],
		  hoverBackgroundColor: ["#FF5A5E", "#5AD3D1", "#FFC870", "#A8B3C5", "#616774", "#245623", "#273214"]
		}]
	  },
	  options: {
		responsive: true,
		legend: {
		  position: 'right',
		  labels: {
			padding: 5,
			boxWidth: 15,
			fontSize: 10
	  }
		},
		plugins: {
		  datalabels: {
			formatter: (value, ctx) => {
			  let sum = 0;
			  let dataArr = ctx.chart.data.datasets[0].data;
			  dataArr.map(data => {
				sum += data;
			  });
			  let percentage = (value * 100 / sum).toFixed(0) + "%";
			  return percentage;
			},
			color: 'white',
			labels: {
			  title: {
				font: {
				  size: '12'
				}
			  }
			}
		  }
		}
	  }
	});	
</script>