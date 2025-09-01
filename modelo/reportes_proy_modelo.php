<?php
class reportes_proy_model{
    private $db;
	private $listas;
    public function __construct(){
        $this->db=new DBManejador();
		$this->listas=array();
    }
    /****************************************
		reporte de detalle de proyecto
	*****************************************/

	public function select_reporte_detProyecto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT prg_proyecto.id_proyecto, prg_proyecto.project_id, proyect, city, state,
				country, telephone, mobile, modules, fax,
				GROUP_CONCAT(distinct programa SEPARATOR '<br>') AS programas
			FROM prg_proyecto LEFT JOIN 
				 prg_proyecto_programa ON prg_proyecto.project_id= prg_proyecto_programa.project_id   INNER JOIN
				  prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto 
			WHERE  prg_proyecto.flag = '1'  $searchQuery ";
		$sql.=" 
		  GROUP BY prg_proyecto.id_proyecto
			order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);	
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function select_ServiciosXls($searchQuery){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT  CONCAT_WS(' ',prg_proyecto.project_id,prg_proyecto.proyect) AS proyecto,
					prg_usuarios.nombres,moneda,mes,anio,prg_estadoproyecto.descripcion,
					country AS pais,dsc_programa AS programa, fax AS contacto,
					CASE  WHEN moneda='EUR' THEN montototal*1.41 WHEN moneda<>'US$' THEN montototal/tipocambio ELSE montototal END AS costo
						
			FROM prg_proyecto INNER JOIN
				prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
				prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado INNER JOIN 
				prg_usuarios ON prg_usuarios.id_usuario= codejecutivo
					
			WHERE  prg_proyecto.flag = '1' AND prg_proyecto_detalle.flag='1'   $searchQuery ";
	
		$consulta=$this->db->consultar($sql);	
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_total_reporte_detProyecto($searchQuery){
		
		$sql="SELECT COUNT(DISTINCT prg_proyecto.id_proyecto) AS total
			FROM prg_proyecto LEFT JOIN 
				 prg_proyecto_programa ON prg_proyecto.project_id= prg_proyecto_programa.project_id   INNER JOIN
				  prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto 
			WHERE  prg_proyecto.flag = '1'  $searchQuery " ;
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	/****************************************
		detalle de proyectos
	*****************************************/
	public function select_reporte_one_detProyecto($id_pais,$id_proyecto){
		
		$sql="SELECT id_proyecto, prg_proyecto.project_id, proyect, prg_proyecto.city, prg_proyecto.state, 
				prg_proyecto.country, prg_proyecto.telephone, prg_proyecto.mobile, prg_proyecto.fax, prg_proyecto.email, modules, 
							insp_type,
				GROUP_CONCAT(distinct programa SEPARATOR '<br>') AS programas
				FROM prg_proyecto INNER JOIN 
					 prg_proyecto_programa ON prg_proyecto.project_id= prg_proyecto_programa.project_id left join
					 prg_proyecto_importar on prg_proyecto.project_id=prg_proyecto_importar.project_id
				WHERE prg_proyecto.flag = '1' AND prg_proyecto.id_pais = '$id_pais' ";
		if($id_proyecto!='') $sql.=" and prg_proyecto.id_proyecto=$id_proyecto ";	
		$sql.="GROUP BY prg_proyecto.project_id ";
		
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
	}
	
	public function select_reporte_cronogra_detProyecto($id_proyecto,$fec_proyi,$fec_proyf){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT  anio,mes,
			DATE_FORMAT(fecha,'%d/%m/%Y') AS fecha_f ,
			DATE_FORMAT(fechafactura,'%d/%m/%Y') AS fechafactura_f ,
			DATE_FORMAT(fechacobro,'%d/%m/%Y') AS fechacobro_f,
			DATE_FORMAT(fechavencimiento,'%d/%m/%Y') AS fechavencimiento_f ,
			prg_proyecto_detalle.observacion as obs_det,
			prg_proyecto_detalle.montototal as monto_det, 
			tipocambio,
			prg_proyecto_detalle.moneda as mon_det,
			prg_cronogramapago.moneda,importe,
			prg_cronogramapago.observacion,
			id_cronograma,nrofactura,
			TO_DAYS(fechavencimiento) - TO_DAYS(NOW()) AS dias,
			IFNULL(prg_condicionpago.descripcion,'') AS condicionpago,
			IFNULL(prg_usuarios.nombres,'') AS comercial,
			IFNULL(prg_estadoproyecto.descripcion,'') AS estado	,
			analisisdsc,
			CASE tipofactura WHEN 'L' THEN 'Local' ELSE 'Exterior' END AS dsctipo,
			nrofactura
			
		FROM
			prg_proyecto_detalle INNER JOIN
			prg_cronogramapago ON prg_proyecto_detalle.id_proyecto=prg_cronogramapago.id_proyecto AND
				prg_proyecto_detalle.coddetalle=prg_cronogramapago.coddetalle	LEFT JOIN
			prg_condicionpago ON prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion LEFT JOIN 
			prg_usuarios ON prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario LEFT JOIN 
			prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
		WHERE prg_cronogramapago.flag='1' AND prg_proyecto_detalle.id_proyecto=$id_proyecto";
	
		if($fec_proyi!='') $sql.=" and to_days(concat_ws('/',anio,mes,28))>= to_days('$fec_proyi')";
    	if($fec_proyf!='') $sql.=" and to_days(concat_ws('/',anio,mes,28))<= to_days('$fec_proyf')";
	
		$sql.="  GROUP BY prg_cronogramapago.id_cronograma
		  order by anio,mes";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_reporte_actividad_detProyecto($project_id,$fec_proyi,$fec_proyf,$id_pais){
		unset($this->listas);
		$this->listas=[];
			$sql="SELECT distinct 
                ref_auditor as auditor,
                tmp_actividad,tmp_subprograma,
                fecha,
				ifnull(ref_actividad,tmp_actividad) as actividad , 
                ifnull(ref_programa,tmp_subprograma) AS subprograma,
                DATE_FORMAT(fecha,'%d-%m-%Y') AS fecha_f, 
				porcentaje, 
				IFNULL(nota,'') AS nota,
                IFNULL(project_id,'') AS project_id,
				IFNULL(ref_proyecto,'') AS proyecto,ifnull(id,0) as id
            FROM 	
                prg_auditoractividad 
			WHERE flag='1' and porcentaje>0 and ref_pais='$id_pais' and project_id='$project_id' 	";
	
		if($fec_proyi!='') $sql.=" and to_days(fecha)>= to_days('$fec_proyi')";
		if($fec_proyf!='') $sql.=" and to_days(fecha)<= to_days('$fec_proyf')";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	/****************************************
		cuadro comerciales
	*****************************************/
	
	public function select_cuadcomer_auditor($id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT DISTINCT codejecutivo, prg_usuarios.nombres
		FROM prg_proyecto_detalle INNER JOIN 
			prg_proyecto ON prg_proyecto_detalle.id_proyecto=prg_proyecto.id_proyecto	INNER JOIN 
			prg_usuarios ON prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario
		WHERE prg_proyecto_detalle.flag='1' AND prg_proyecto.flag='1' AND prg_proyecto.id_pais='$id_pais'";
		if($id_pais=='esp')	$sql.=" and codejecutivo in (296,45,439,51,82,64) ";
		$sql.=" order by 2";
	
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_cuadcomer_resumenxanio($tcEuUS,$id_pais,$anio_fin){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT  mes,id_grupo,codejecutivo,
			ROUND(SUM(CASE  WHEN moneda='EUR' THEN montototal*$tcEuUS WHEN moneda<>'US$' THEN montototal/tipocambio ELSE montototal END),2) AS costo
			FROM prg_proyecto INNER JOIN
				prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto inner join
				prg_estadoproyecto on prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
			WHERE  prg_proyecto.flag = '1' 
			  AND prg_proyecto_detalle.flag='1' 
			 AND prg_proyecto.id_pais= '$id_pais'  
			 and anio=$anio_fin
		 GROUP BY mes,id_grupo,codejecutivo
		 ORDER BY mes asc  ";
	
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function select_cuadcomer_ampliaredu($sescosestado,$id_pais,$anio_fin){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT 
				IFNULL(SUM(CASE prg_programacosto.moneda WHEN 'US$' THEN ampliacion ELSE ampliacion/tipocambio END),0) AS ampliacion,
				IFNULL(SUM(CASE prg_programacosto.moneda WHEN 'US$' THEN reduccion ELSE reduccion/tipocambio END),0) AS reduccion,
				IFNULL(SUM(CASE prg_programacosto.moneda WHEN 'US$' THEN montoservicio ELSE montoservicio/tipocambio END),0) AS montoservicio,
				anioo,meso, codejecutivo
			FROM prg_proyecto_detalle INNER JOIN prg_proyecto ON prg_proyecto_detalle.id_proyecto=prg_proyecto.id_proyecto
			INNER JOIN prg_programacosto ON prg_proyecto_detalle.coddetalle=prg_programacosto.coddetalle AND prg_proyecto_detalle.id_proyecto=prg_programacosto.project_id
			WHERE prg_proyecto_detalle.isanulado!='1' and prg_proyecto_detalle.flag='1' AND prg_proyecto.flag='1'  
				 AND prg_programacosto.flag='1'	AND prg_proyecto.id_pais='$id_pais' AND anioo IN ($anio_fin) ";
		if($sescosestado>0) $sql.=" AND codestado IN ($sescosestado) ";		 
		if($id_pais=='esp') $sql.="		AND codejecutivo IN (296,45,439,51,82,64) ";
		$sql.=" 	GROUP BY codejecutivo,anioo,meso
				HAVING  ampliacion <>0 OR reduccion <>0 or montoservicio>0";
	
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_cuadcomer_resumenxanioxGrupo($tcEuUS,$id_pais,$anio_fin){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT  id_grupo,codejecutivo,
			ROUND(SUM(CASE  WHEN moneda='EUR' THEN montototal*$tcEuUS WHEN moneda<>'US$' THEN montototal/tipocambio ELSE montototal END),2) AS costo
			FROM prg_proyecto INNER JOIN
				prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto inner join
				prg_estadoproyecto on prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
			WHERE  prg_proyecto.flag = '1' 
			  AND prg_proyecto_detalle.flag='1' 
			 AND prg_proyecto.id_pais= '$id_pais'  
			 and anio=$anio_fin
		 GROUP BY id_grupo,codejecutivo  ";
		
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function select_cuadcomer_ampliareducexanio($sescosestado,$id_pais,$anio_fin){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT 
				IFNULL(SUM(CASE prg_programacosto.moneda WHEN 'US$' THEN ampliacion ELSE ampliacion/tipocambio END),0) AS ampliacion,
				IFNULL(SUM(CASE prg_programacosto.moneda WHEN 'US$' THEN reduccion ELSE reduccion/tipocambio END),0) AS reduccion,
				IFNULL(SUM(CASE prg_programacosto.moneda WHEN 'US$' THEN montoservicio ELSE montoservicio/tipocambio END),0) AS montoservicio,
				anioo, codejecutivo
			FROM prg_proyecto_detalle INNER JOIN prg_proyecto ON prg_proyecto_detalle.id_proyecto=prg_proyecto.id_proyecto
			INNER JOIN prg_programacosto ON prg_proyecto_detalle.coddetalle=prg_programacosto.coddetalle
			WHERE prg_proyecto_detalle.flag='1' AND prg_proyecto.flag='1'  AND codestado IN ($sescosestado) AND prg_programacosto.flag='1'
					AND prg_proyecto.id_pais='$id_pais' AND anioo IN ($anio_fin) ";
		if($id_pais=='esp') $sql.="		AND codejecutivo IN (296,45,439,51,82,64)";
		$sql.=" GROUP BY codejecutivo,anioo
				HAVING  ampliacion <>0 OR reduccion <>0 or montoservicio<>0";
		
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_cuadcomer_ventaanterior($anio_ini,$id_pais){
		unset($this->listas);
		$this->listas=[];
		
		$sql="select * from prg_venta_auditor 
			where flag='1' and id_pais='$id_pais' and anio=$anio_ini";
		
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_cuadcomer_ctacobrar($anio_ini,$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="select * from prg_ctacobrar_auditor 
			where flag='1' and id_pais='$id_pais' and anio=$anio_ini";
		
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_cuadcomer_ventaanteriorxAudit($anio_ini,$id_pais){
		unset($this->listas);
		$this->listas=[];
		
		$sql="select sum(monto) as monto, sum(cuanto) as cuanto, codejecutivo,anio 
			from prg_venta_auditor 
			where flag='1' and id_pais='$id_pais' and anio=$anio_ini 
			group by codejecutivo";
		
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_cuadcomer_resxanioxmesxaudt($anio_fin,$id_pais,$sescosestado){
		unset($this->listas);
		$this->listas=[];
		
		$sql="	SELECT 
			SUM(CASE moneda WHEN 'US$' THEN montototal ELSE montototal/tipocambio END) AS total,
			SUM(CASE  	WHEN month(prg_proyecto_detalle.fecha_ingreso)<=meso and moneda='US$' THEN montototal 
						when month(prg_proyecto_detalle.fecha_ingreso)<=meso and moneda!='US$' then montototal/tipocambio 
						else 0 END) AS totalFoto,
			COUNT(prg_proyecto_detalle.coddetalle) AS cuantos,
			anioo,meso, codejecutivo
		FROM prg_proyecto_detalle INNER JOIN prg_proyecto ON prg_proyecto_detalle.id_proyecto=prg_proyecto.id_proyecto
		WHERE prg_proyecto_detalle.flag='1' AND prg_proyecto.flag='1'  and codestado in ($sescosestado)
				AND prg_proyecto.id_pais='$id_pais' AND anioo IN ($anio_fin) ";
		if($id_pais=='esp') $sql.="		and codejecutivo in (296,45,439,51,82,64) ";
		$sql.="	GROUP BY codejecutivo,anioo,meso";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_cuadcomer_resxanioxaudt($anio_fin,$id_pais,$sescosestado){
		unset($this->listas);
		$this->listas=[];
		
		$sql="	SELECT SUM(CASE moneda WHEN 'US$' THEN montototal ELSE montototal/tipocambio END) AS total,
			COUNT(prg_proyecto_detalle.coddetalle) AS cuantos,
			anioo, codejecutivo
		FROM prg_proyecto_detalle INNER JOIN prg_proyecto ON prg_proyecto_detalle.id_proyecto=prg_proyecto.id_proyecto
		WHERE prg_proyecto_detalle.flag='1' AND prg_proyecto.flag='1'  and codestado in ($sescosestado)
				AND prg_proyecto.id_pais='$id_pais' AND anioo IN ($anio_fin) ";
		if($id_pais=='esp') $sql.="	and codejecutivo in (296,45,439,51,82,64) ";
		$sql.="	GROUP BY codejecutivo,anioo";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_mestaanio($mes,$id_pais,$anio){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT DISTINCT prg_proyecto_detalle.codejecutivo, prg_usuarios.nombres, 
				ifnull(v.monto,'') as monto,ifnull(v.cuanto,'') as cuanto,ifnull(v.comercial,'') as comercial
			FROM prg_proyecto_detalle INNER JOIN 
				prg_proyecto ON prg_proyecto_detalle.id_proyecto=prg_proyecto.id_proyecto	INNER JOIN 
				prg_usuarios ON prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario left join
				prg_venta_auditor v on prg_proyecto_detalle.codejecutivo=v.codejecutivo and 
				v.anio=$anio and v.mes=$mes
			WHERE prg_proyecto_detalle.flag='1' AND prg_proyecto.flag='1' AND prg_proyecto.id_pais='$id_pais'
			and prg_proyecto_detalle.codejecutivo in (296,45,439,51,82,64) 
			order by 2";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_ctacobraranio($mes,$id_pais,$anio){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT DISTINCT prg_proyecto_detalle.codejecutivo, prg_usuarios.nombres, 
				ifnull(v.monto,'') as monto
			FROM prg_proyecto_detalle INNER JOIN 
				prg_proyecto ON prg_proyecto_detalle.id_proyecto=prg_proyecto.id_proyecto	INNER JOIN 
				prg_usuarios ON prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario left join
				prg_ctacobrar_auditor v on prg_proyecto_detalle.codejecutivo=v.codejecutivo and 
				v.anio=$anio and v.mes=$mes
			WHERE prg_proyecto_detalle.flag='1' AND prg_proyecto.flag='1' AND prg_proyecto.id_pais='$id_pais'
			and prg_proyecto_detalle.codejecutivo in (296,45,439,51,82,64) 
			order by 2";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function delete_mestaanio($mes,$id_pais,$anio){
		$sql="delete from prg_venta_auditor where mes=$mes and anio=$anio and id_pais='$id_pais'";
		
		$consulta=$this->db->execute($sql);		
        return $consulta;	
	}

	public function delete_ctacobraranio($mes,$id_pais,$anio){
		$sql="delete from prg_ctacobrar_auditor where mes=$mes and anio=$anio and id_pais='$id_pais'";
		
		$consulta=$this->db->execute($sql);		
        return $consulta;	
	}
	
	public function select_mestaanioSave($id_pais){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT DISTINCT codejecutivo, prg_usuarios.nombres, 0 as monto
			FROM prg_proyecto_detalle INNER JOIN 
				prg_proyecto ON prg_proyecto_detalle.id_proyecto=prg_proyecto.id_proyecto	INNER JOIN 
				prg_usuarios ON prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario
			WHERE prg_proyecto_detalle.flag='1' AND prg_proyecto.flag='1' AND prg_proyecto.id_pais='$id_pais'
			and codejecutivo in (64,296,45,439,51,82) 
			order by 2 ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function insert_mestaanio($mes,$id_pais,$anio,$codejecutivo,$monto,$cuanto,$comercial){
		$sql="insert into prg_venta_auditor(codejecutivo,anio,mes,id_pais,monto,cuanto,comercial,flag,fecha_ingreso)
			values($codejecutivo,$anio,$mes,'$id_pais','$monto','$cuanto','$comercial','1',now())";
			
		$consulta=$this->db->executeIns($sql);		
        return $consulta;	
	}
	
	
	public function insert_ctacobraranio($mes,$id_pais,$anio,$codejecutivo,$monto){
		$sql="insert into prg_ctacobrar_auditor(codejecutivo,anio,mes,id_pais,monto,flag,fecha_ingreso)
			values($codejecutivo,$anio,$mes,'$id_pais','$monto','1',now())";
			
		$consulta=$this->db->executeIns($sql);		
        return $consulta;	
	}
	
	public function select_dataDetalleAuditorCuacro($id_pais,$codejecutivo,$anio,$mes){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT CASE prg_proyecto_detalle.moneda WHEN 'US$' THEN montototal ELSE montototal/tipocambio END AS total,
			anioo,meso,anio,mes, codejecutivo,prg_proyecto.project_id,
			prg_proyecto.proyect,
			prg_estadoproyecto.descripcion,
			SUM(CASE prg_proyecto_detalle.moneda WHEN 'US$' THEN montoservicio ELSE montoservicio/tipocambio END) AS monto
		FROM prg_proyecto_detalle INNER JOIN 
			prg_proyecto ON prg_proyecto_detalle.id_proyecto=prg_proyecto.id_proyecto INNER JOIN
			prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
			INNER JOIN prg_programacosto as vista on  
				prg_proyecto_detalle.coddetalle=vista.coddetalle AND prg_proyecto_detalle.id_proyecto=vista.project_id
				
		WHERE prg_proyecto_detalle.isanulado!='1' and prg_proyecto_detalle.flag='1' AND prg_proyecto.flag='1'  AND prg_proyecto_detalle.codestado in (1)
				AND prg_proyecto.id_pais='esp' AND anioo IN ($anio)
				AND codejecutivo=$codejecutivo AND meso=$mes and vista.flag='1'
				 AND prg_proyecto.id_pais= '$id_pais' 
		GROUP BY prg_proyecto_detalle.coddetalle
		";
		$consulta=$this->db->consultar($sql);	
		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	 /****************************************
		reporte de detalle de proyecto
	*****************************************/

	public function select_reporte_plancuota($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT distinct
			prg_proyecto_detalle.coddetalle as id,
			prg_proyecto.id_proyecto, 
			prg_proyecto.project_id, 
			proyect, city, state,
			country, telephone, mobile, modules, anio,mes,fax,
			ifnull(dsc_programaren,'') AS programas,
			ifnull(prg_proyecto_detalle.montototal,0) as importe,
			prg_proyecto_detalle.moneda,
			ifnull(prg_condicionpago.descripcion,'') as condicionpago,
			ifnull(prg_usuarios.nombres,'') as comercial,
			ifnull(prg_estadoproyecto.descripcion,'') as estado	,
			analisisdsc,
			case is_analisis when '1' then 'Si' else 'No' end as is_analisis,
			case prg_proyecto.is_viatico when '1' then 'Si' else 'No' end as is_viatico,
			ifnull(numcobranza,'') as numcobranza
		FROM prg_proyecto INNER JOIN
			 prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto left join
			 prg_condicionpago on prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion left join 
			 prg_usuarios on prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario left join 
			 prg_estadoproyecto on prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
			 inner JOIN prg_programacosto costo on costo.coddetalle = prg_proyecto_detalle.coddetalle
		WHERE   costo.flag = '1'
    		AND prg_proyecto.flag = '1' 
			AND prg_proyecto_detalle.flag='1' $searchQuery ";
		$sql.=" 
		  GROUP BY prg_proyecto_detalle.coddetalle
			order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;

		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_total_reporte_plancuota($searchQuery){
		
		$sql="SELECT COUNT(DISTINCT prg_proyecto_detalle.coddetalle) AS total
				FROM prg_proyecto INNER JOIN
					 prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto left join
					 prg_condicionpago on prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion left join 
					 prg_usuarios on prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario left join 
					 prg_estadoproyecto on prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
					 inner JOIN prg_programacosto costo on costo.coddetalle = prg_proyecto_detalle.coddetalle
				WHERE 
					costo.flag = '1'
					AND prg_proyecto.flag = '1' 
					AND prg_proyecto_detalle.flag='1'  $searchQuery " ;
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	public function selec_total_reporte_estado($id_pais){
		unset($this->listas);
		$this->listas=[];
		
		$sql="select * from prg_estadoproyecto where flag='1' and id_pais = '$id_pais' order by descripcion ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	// excel resumen analisis
	public function selec_analisis_xproyecto($id_pais,$proyecto,$fec_proyi,$fec_proyf){
		
		 unset($this->listas);
		 $this->listas=[];
		 
        $sql="select prg_proyecto.id_proyecto,
                SUM( CASE WHEN prg_auditoractividad.id_actividad in (4,20) THEN porcentaje/100 ELSE 0 END ) AS Real_auditoria,
                SUM( CASE WHEN prg_auditoractividad.id_actividad=28 THEN porcentaje/100 ELSE 0 END ) AS Real_viajes,
                SUM( CASE WHEN prg_auditoractividad.id_actividad=1 THEN porcentaje/100 ELSE 0 END ) AS Real_decision,
                SUM( CASE WHEN prg_auditoractividad.id_actividad=19 THEN porcentaje/100 ELSE 0 END ) AS Real_reporte,
                SUM( CASE WHEN prg_auditoractividad.id_actividad IN (4,20,28,1,19) THEN 0 ELSE porcentaje/100 END ) AS Real_otros,
                SUM(prg_auditor.costo*porcentaje/100) AS costoReal
              from prg_proyecto INNER JOIN 
                   prg_auditoractividad on prg_proyecto.id_proyecto=prg_auditoractividad.ref_idproyecto 
                        and  prg_auditoractividad.flag='1' AND YEAR(prg_auditoractividad.fecha)>=2020 left join
                   prg_auditor ON prg_auditoractividad.id_auditor=prg_auditor.id_auditor  inner join
                   prg_actividad on prg_auditoractividad.id_actividad=prg_actividad.id_actividad
              where  flganalisis='1' and ifnull(porcentaje,0)>0 and prg_proyecto.flag = '1' 
				AND prg_proyecto.id_pais= '$id_pais'";
        
        if($proyecto!='') 
			$sql.= " and (proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%')";
		if($fec_proyi!='') $sql.=" and to_days(prg_auditoractividad.fecha)>= to_days('$fec_proyi')";
        if($fec_proyf!='') $sql.=" and to_days(prg_auditoractividad.fecha)<= to_days('$fec_proyf')";
        
        $sql.=" GROUP BY 1 ";
		$sql.="	ORDER BY proyect asc  ";
    
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_anal_xproDet($id_pais,$proyecto,$fec_proyi,$fec_proyf,$codestado,$tcEuUS,$G_tc){
		
		 unset($this->listas);
		 $this->listas=[];
		 
       $sql="SELECT 
			IF(IFNULL(prg_proyecto_detalle.id_proyecto_adm,0)>0,prg_proyecto_detalle.id_proyecto_adm,prg_proyecto.id_proyecto) id_proyecto, 
			TRIM(IF(IFNULL(prg_proyecto_detalle.id_proyecto_adm,0)>0,prg_proyecto_detalle.project_id_adm,prg_proyecto.project_id)) project_id, 
			TRIM(IF(IFNULL(prg_proyecto_detalle.id_proyecto_adm,0)>0,prg_proyecto_detalle.proyecto_adm,prg_proyecto.proyect)) proyect, 
			city, country, 
			(SELECT GROUP_CONCAT(DISTINCT programa SEPARATOR ', ') FROM prg_proyecto_programa WHERE prg_proyecto.project_id= prg_proyecto_programa.project_id) AS programas,
			'US$' moneda,
			SUM(CASE prg_proyecto_detalle.moneda WHEN 'US$' THEN  montototal   WHEN 'EUR' THEN  montototal*$tcEuUS ELSE montototal/$G_tc   END)
			    AS importe,
			SUM(auditoria) AS auditoria,
			SUM(viaje) AS viaje,
			SUM(otros) AS otros,
			SUM(fee) AS fee,
			SUM(servicio) AS servicio,
			SUM(otromonto) AS otromonto,
			SUM(subtotal) AS subtotal
		FROM prg_proyecto  INNER JOIN
		     prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto LEFT JOIN
		     (
				SELECT 
				 coddetalle,
				 SUM(auditoria) AS auditoria,
				 SUM(viaje) AS viaje,
				 SUM(preparacion + reporte + certificacion) AS otros,
				 SUM(IFNULL(CASE moneda WHEN 'US$' THEN  montofee  WHEN 'EUR' THEN  montofee*$tcEuUS ELSE montofee/$G_tc   END,0)) AS fee,
				 SUM(IFNULL(CASE moneda WHEN 'US$' THEN  montofeecert  WHEN 'EUR' THEN  montofeecert*$tcEuUS ELSE montofeecert/$G_tc   END,0)) AS feecert,
				 SUM(IFNULL(CASE moneda WHEN 'US$' THEN  montoservicio   WHEN 'EUR' THEN  montoservicio*$tcEuUS ELSE montoservicio/$G_tc   END,0)) AS servicio,
				 SUM(IFNULL(CASE moneda WHEN 'US$' THEN  montocourier   WHEN 'EUR' THEN  montocourier*$tcEuUS ELSE montocourier/$G_tc   END,0) 
					+  IFNULL(CASE moneda WHEN 'US$' THEN  montoviatico   WHEN 'EUR' THEN  montoviatico*$tcEuUS ELSE montoviatico/$G_tc   END,0)) AS otromonto,
				 SUM(
				   IFNULL(CASE moneda WHEN 'US$' THEN  montofee   WHEN 'EUR' THEN  montofee*$tcEuUS ELSE montofee/3.35   END,0) + 
				   IFNULL(CASE moneda WHEN 'US$' THEN  montofeecert   WHEN 'EUR' THEN  montofeecert*$tcEuUS ELSE montofeecert/3.35   END,0) + 
				   IFNULL(CASE moneda WHEN 'US$' THEN  montoservicio   WHEN 'EUR' THEN  montoservicio*$tcEuUS ELSE montoservicio/$G_tc   END,0) +
				   IFNULL(CASE moneda WHEN 'US$' THEN  montocourier   WHEN 'EUR' THEN  montocourier*$tcEuUS ELSE montocourier/$G_tc  END,0)  +
				   IFNULL(CASE moneda WHEN 'US$' THEN  montoviatico   WHEN 'EUR' THEN  montoviatico*$tcEuUS ELSE montoviatico/$G_tc   END,0)) AS subtotal
				FROM prg_programacosto 
				WHERE flag='1' 
				GROUP BY coddetalle
		     ) AS vista ON prg_proyecto_detalle.coddetalle=vista.coddetalle
		WHERE  prg_proyecto.flag = '1' AND prg_proyecto_detalle.flag='1'
	           AND prg_proyecto.id_pais= '$id_pais' ";

		 if($proyecto!='') 
			$sql.= " and (proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%')";
		if($fec_proyi!='') $sql.=" and to_days(concat_ws('/',anio,mes,28))>= to_days('$fec_proyi')";
        if($fec_proyf!='') $sql.=" and to_days(concat_ws('/',anio,mes,28))<= to_days('$fec_proyf')";
        if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";	
			
		$sql.=" GROUP BY 1 ";
		$sql.="	ORDER BY 3 asc  ";
    
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	public function selec_anal_xproDetCol($id_pais,$proyecto,$fec_proyi,$fec_proyf,$codestado,$tcEuUS,$G_tc){
		
		 unset($this->listas);
		 $this->listas=[];
		 
       $sql="SELECT 
			ruc,IFNULL(prg_producto.producto,'') AS producto, 
			GROUP_CONCAT(DISTINCT IFNULL(programa,'')) AS programadsc,
			GROUP_CONCAT(DISTINCT IFNULL(comentario,'')) AS comentariodsc,
			(SELECT GROUP_CONCAT(DISTINCT prg_programa.`codigo` SEPARATOR ', ') 
				FROM prg_proyecto_programa INNER JOIN prg_programa ON prg_proyecto_programa.`programa`=prg_programa.`descripcion` AND 
					prg_proyecto_programa.`id_pais`=prg_programa.`id_pais`
				WHERE prg_proyecto.project_id= prg_proyecto_programa.project_id
			) AS cod_programas, prg_estadoproyecto.descripcion as estado,
	
			if(ifnull(prg_proyecto_detalle.id_proyecto_adm,0)>0,prg_proyecto_detalle.id_proyecto_adm,prg_proyecto.id_proyecto) id_proyecto, 
                        prg_proyecto.project_id,
                        trim(if(ifnull(prg_proyecto_detalle.id_proyecto_adm,0)>0,prg_proyecto_detalle.project_id_adm,prg_proyecto.proyect)) proyect, 
                        city, country, 
			(SELECT GROUP_CONCAT(DISTINCT programa SEPARATOR ', ') FROM prg_proyecto_programa WHERE prg_proyecto.project_id= prg_proyecto_programa.project_id) AS programas,
			'US$' moneda,
			SUM(CASE prg_proyecto_detalle.moneda WHEN 'US$' THEN  montototal   WHEN 'EUR' THEN  montototal*$tcEuUS ELSE montototal/tipocambio  END)
				AS importe,
			SUM(PC.auditoria) AS auditoria,
			SUM(PC.viaje) AS viaje,
			SUM(PC.preparacion + PC.reporte + PC.certificacion) AS otros,
			SUM(IFNULL(CASE PC.moneda WHEN 'US$' THEN  PC.montofee WHEN 'EUR' THEN  PC.montofee/tipocambio ELSE PC.montofee/tipocambio  END,0)) AS fee,
			SUM(IFNULL(CASE PC.moneda WHEN 'US$' THEN  PC.montofeecert WHEN 'EUR' THEN  PC.montofeecert/tipocambio ELSE PC.montofeecert/tipocambio  END,0)) AS feecert,
			SUM(IFNULL(CASE PC.moneda WHEN 'US$' THEN  PC.montoservicio   WHEN 'EUR' THEN  PC.montoservicio*$tcEuUS ELSE PC.montoservicio/tipocambio  END,0)) AS servicio,
			SUM(IFNULL(CASE PC.moneda WHEN 'US$' THEN  PC.montocourier   WHEN 'EUR' THEN  PC.montocourier*$tcEuUS ELSE PC.montocourier/tipocambio  END,0) 
				+  IFNULL(CASE PC.moneda WHEN 'US$' THEN  PC.montoviatico   WHEN 'EUR' THEN  PC.montoviatico*$tcEuUS ELSE PC.montoviatico/tipocambio  END,0)) AS otromonto,
			SUM(
			   IFNULL(CASE PC.moneda WHEN 'US$' THEN  PC.montofee   WHEN 'EUR' THEN  PC.montofee*$tcEuUS ELSE PC.montofee/tipocambio  END,0) + 
			   IFNULL(CASE PC.moneda WHEN 'US$' THEN  PC.montofeecert   WHEN 'EUR' THEN  PC.montofeecert*$tcEuUS ELSE PC.montofeecert/tipocambio  END,0) + 
			   IFNULL(CASE PC.moneda WHEN 'US$' THEN  PC.montoservicio   WHEN 'EUR' THEN  PC.montoservicio*$tcEuUS ELSE PC.montoservicio/tipocambio  END,0) +
			   IFNULL(CASE PC.moneda WHEN 'US$' THEN  PC.montocourier   WHEN 'EUR' THEN  PC.montocourier*$tcEuUS ELSE PC.montocourier/tipocambio  END,0)  +
			   IFNULL(CASE PC.moneda WHEN 'US$' THEN  PC.montoviatico   WHEN 'EUR' THEN  PC.montoviatico*$tcEuUS ELSE PC.montoviatico/tipocambio  END,0)) AS subtotal
			   
		FROM prg_proyecto  INNER JOIN
			 prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto left join
             prg_programacosto PC ON prg_proyecto_detalle.coddetalle=PC.coddetalle and PC.flag='1' LEFT JOIN 
			 prg_producto ON prg_proyecto_detalle.codproducto=prg_producto.codproducto  LEFT JOIN
             prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado                
                         
		WHERE  prg_proyecto.flag = '1' and prg_proyecto_detalle.flag='1'
                       AND prg_proyecto.id_pais= '$id_pais' ";

		if($proyecto!='') 
			$sql.= " and (proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%')";
		if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";	
			
        if($fec_proyi!='') $sql.=" and to_days(concat_ws('/',anio,mes,28))>= to_days('$fec_proyi')";
        if($fec_proyf!='') $sql.=" and to_days(concat_ws('/',anio,mes,28))<= to_days('$fec_proyf')";
        
        
		$sql.=" GROUP BY 1 ";
		$sql.="	ORDER BY 3 asc  ";
    
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_anal_xdetalle($id_pais,$proyecto,$fec_proyi,$fec_proyf,$codestado,$tcEuUS,$G_tc){
		
		 unset($this->listas);
		 $this->listas=[];
		 
		   $sql="SELECT 
				prg_proyecto_detalle.coddetalle,
				IF(IFNULL(prg_proyecto_detalle.id_proyecto_adm,0)>0,prg_proyecto_detalle.id_proyecto_adm,prg_proyecto.id_proyecto) id_proyecto, 
				TRIM(IF(IFNULL(prg_proyecto_detalle.id_proyecto_adm,0)>0,prg_proyecto_detalle.project_id_adm,prg_proyecto.project_id)) project_id, 
				TRIM(IF(IFNULL(prg_proyecto_detalle.id_proyecto_adm,0)>0,prg_proyecto_detalle.proyecto_adm,prg_proyecto.proyect)) proyect, 
				
				city, state,
				country, telephone, mobile, modules, anio,mes,fax,
				(SELECT GROUP_CONCAT(DISTINCT programa SEPARATOR ', ') FROM prg_proyecto_programa WHERE prg_proyecto.project_id= prg_proyecto_programa.project_id) AS programas,
				'US$' moneda,
				CASE prg_proyecto_detalle.moneda WHEN 'US$' THEN  montototal WHEN 'EUR' THEN  montototal*$tcEuUS ELSE montototal/$G_tc  END
				   as importe,
				ifnull(prg_condicionpago.descripcion,'') as condicionpago,
				ifnull(prg_usuarios.nombres,'') as comercial,
				ifnull(prg_estadoproyecto.descripcion,'') as estado,
							
				auditoria,
				viaje,
				otros,
				fee,
				servicio,
				otromonto,
				subtotal

			FROM prg_proyecto INNER JOIN
				 prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto left JOIN
				 prg_condicionpago on prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion left join 
				 prg_usuarios on prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario left join 
				 prg_estadoproyecto on prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado LEFT JOIN
			   (
				SELECT 
				 coddetalle,
				 SUM(auditoria) AS auditoria,
				 SUM(viaje) AS viaje,
				 SUM(preparacion + reporte + certificacion) AS otros,
				 SUM(IFNULL(CASE moneda WHEN 'US$' THEN  montofee  WHEN 'EUR' THEN  montofee*$tcEuUS ELSE montofee/$G_tc   END,0)) AS fee,
				 SUM(IFNULL(CASE moneda WHEN 'US$' THEN  montofeecert  WHEN 'EUR' THEN  montofeecert*$tcEuUS ELSE montofeecert/$G_tc   END,0)) AS feecert,
				 SUM(IFNULL(CASE moneda WHEN 'US$' THEN  montoservicio   WHEN 'EUR' THEN  montoservicio*$tcEuUS ELSE montoservicio/$G_tc   END,0)) AS servicio,
				 SUM(IFNULL(CASE moneda WHEN 'US$' THEN  montocourier   WHEN 'EUR' THEN  montocourier*$tcEuUS ELSE montocourier/$G_tc   END,0) 
					+  IFNULL(CASE moneda WHEN 'US$' THEN  montoviatico   WHEN 'EUR' THEN  montoviatico*$tcEuUS ELSE montoviatico/$G_tc   END,0)) AS otromonto,
				 SUM(
				   IFNULL(CASE moneda WHEN 'US$' THEN  montofee   WHEN 'EUR' THEN  montofee*$tcEuUS ELSE montofee/$G_tc   END,0) + 
				   IFNULL(CASE moneda WHEN 'US$' THEN  montofeecert   WHEN 'EUR' THEN  montofeecert*$tcEuUS ELSE montofeecert/$G_tc  END,0) + 
				   IFNULL(CASE moneda WHEN 'US$' THEN  montoservicio   WHEN 'EUR' THEN  montoservicio*$tcEuUS ELSE montoservicio/$G_tc   END,0) +
				   IFNULL(CASE moneda WHEN 'US$' THEN  montocourier   WHEN 'EUR' THEN  montocourier*$tcEuUS ELSE montocourier/$G_tc  END,0)  +
				   IFNULL(CASE moneda WHEN 'US$' THEN  montoviatico   WHEN 'EUR' THEN  montoviatico*$tcEuUS ELSE montoviatico/$G_tc   END,0)) AS subtotal
				FROM prg_programacosto 
				WHERE flag='1' 
				GROUP BY coddetalle
		     ) AS vista ON prg_proyecto_detalle.coddetalle=vista.coddetalle
                             
                         
			WHERE  prg_proyecto.flag = '1' and prg_proyecto_detalle.flag='1'
				   AND prg_proyecto.id_pais= '$id_pais' ";

		if($proyecto!='') 
			$sql.= " and (proyect like '%$proyecto%' or  prg_proyecto.project_id like '%$proyecto%'  )";
		if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";	
		if($fec_proyi!='') $sql.=" and to_days(concat_ws('/',anio,mes,28))>= to_days('$fec_proyi')";
		if($fec_proyf!='') $sql.=" and to_days(concat_ws('/',anio,mes,28))<= to_days('$fec_proyf')";
			
			
		$sql.=" GROUP BY 1,3";
		$sql.="	ORDER BY 4 asc  ";
    
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	public function selec_por_fact($id_pais,$proyecto,$fec_proyi,$fec_proyf,$codestado,$tcEuUS,$G_tc,$fec_facturadoi,$fec_facturado){
		
		 unset($this->listas);
		 $this->listas=[];
		 
		   $sql="SELECT prg_cronogramapago.id_cronograma, 
			prg_proyecto.project_id,
			proyect, 
			city, state,
			IFNULL(prg_producto.producto,'') AS producto, 
			country, telephone, mobile, modules, anio,mes,
			anioo,meso,fax,
			ifnull(dsc_programaren,'') AS programas,
			DATE_FORMAT(fecha,'%d/%m/%Y') AS fecha_f ,
			
			DATE_FORMAT(fechafactura,'%d/%m/%Y') AS fechafactura_f ,
			DATE_FORMAT(fechacobro,'%d/%m/%Y') AS fechacobro_f,
			DATE_FORMAT(fechavencimiento,'%d/%m/%Y') AS fechavencimiento_f ,
			prg_cronogramapago.moneda,
			importe,montototal,
			round(CASE prg_cronogramapago.moneda WHEN 'US$' THEN  importe  WHEN 'EUR' THEN  importe*$tcEuUS ELSE importe/tipocambio END,2) AS importeus,
			prg_cronogramapago.observacion,
			id_cronograma,
			nrofactura,
			to_days(fechavencimiento) - to_days(now()) as dias,
			ifnull(prg_condicionpago.descripcion,'') as condicionpago,
			ifnull(prg_usuarios.nombres,'') as comercial,
			ifnull(prg_estadoproyecto.descripcion,'') as estado	,
			analisisdsc,
			ifnull(tonelada,'') as tonelada,
			
			ifnull(dsc_puerto,'') as dsc_puerto,
			case is_analisis when '1' then 'Si' else 'No' end as is_analisis,
			case prg_proyecto.is_viatico when '1' then 'Si' else 'No' end as is_viatico,
			1 as orden,
			ifnull(serie,'') as serie,
			ifnull(numeronc,'') as numeronc,
			ifnull(montonc,'') as montonc,
			ifnull(montoncneto,'') as montoncneto,
			ifnull(montoservicio,'') as montoservicio,
			DATE_FORMAT(fechanc,'%d/%m/%Y') AS fechanc_f ,
			prg_proyecto_detalle.tipocambio,
			ifnull(prg_cronogramapago_nota.descripcion,'') as notadsc
		FROM prg_proyecto INNER JOIN
			prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto inner join
			prg_cronogramapago ON prg_proyecto_detalle.id_proyecto=prg_cronogramapago.id_proyecto and
				prg_proyecto_detalle.coddetalle=prg_cronogramapago.coddetalle	left JOIN
			prg_condicionpago on prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion left join 
			prg_usuarios on prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario left join 
			prg_estadoproyecto on prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado LEFT JOIN 
			prg_producto ON prg_proyecto_detalle.codproducto=prg_producto.codproducto left join
			prg_cronogramapago_nota on prg_cronogramapago.nota=prg_cronogramapago_nota.nota
		WHERE  prg_proyecto.flag = '1' and prg_proyecto_detalle.flag='1'
			AND prg_cronogramapago.flag='1' 
			AND prg_proyecto.id_pais= '$id_pais' ";

		if($proyecto!='')		
			$sql.= " and (proyect like '%$proyecto%' or  prg_proyecto.project_id like '%$proyecto%'  )";
		
		if($fec_facturadoi!='') $sql.= " and to_days(fechafactura) >= to_days('$fec_facturadoi')";
		if($fec_facturadof!='') $sql.= " and to_days(fechafactura) <= to_days('$fec_facturadof')";
		
		if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";	
		if($fec_proyi!='') $sql.=" and to_days(concat_ws('/',anio,mes,28))>= to_days('$fec_proyi')";
		if($fec_proyf!='') $sql.=" and to_days(concat_ws('/',anio,mes,28))<= to_days('$fec_proyf')";

		$sql.=" GROUP BY prg_cronogramapago.id_cronograma";

		$sql.=" 
			union 
			SELECT
			1,
			cu AS project_id,proyecto AS proyect,
			'','','','','','','',
			YEAR(fecha_emision) AS anio,
			MONTH(fecha_emision) AS mes,
			'','','','TC','','','','','USD',
			ROUND(SUM(costo_usd + cos_courier_usd),2) AS importe,0,0,
			'','','','','','','','','','','','', 2 as orden,'',
			'' as numeronc,
			
			'' as montonc,
			'' as montoncneto,
			'' as montoservicio,
			'' as tipo,
			'' AS fechanc_f,''
		   FROM tc_datos
		   WHERE  flag = '1' AND id_pais= '$id_pais' 
			and cu not in ('800375','801671','858303','856622','812111','817070','803692','835575','800000','849379','849452') ";

		if($proyecto!='') 
			$sql.= " and (proyecto like '%$proyecto%' or  cu like '%$proyecto%' )";
		
		if($fec_proyi!='') $sql.=" and to_days(fecha_emision) >= to_days('$fec_proyi')";
		if($fec_proyf!='') $sql.=" and to_days(fecha_emision) <= to_days('$fec_proyf')";
		
		$sql.=" GROUP BY cu,mes,anio having importe >0";   
		$sql.="	ORDER BY orden,10,11,3 asc  ";

		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	public function selec_por_detalle($id_pais,$proyecto,$fec_proyi,$fec_proyf,$codestado,$fec_facturadoi,$fec_facturado,$isjoin){
		
		 unset($this->listas);
		 $this->listas=[];
		 //cartacor notacredito
		 $sql="SELECT prg_proyecto.id_proyecto, prg_proyecto.project_id, proyect, 
			city, state,
			IFNULL(prg_producto.producto,'') AS producto, 
			country, telephone, mobile, modules, anio,mes,anioo,meso,fax,
			ifnull(dsc_programaren,'') AS programas,
			ifnull(prg_usuarios.nombres,'') as comercial,
			ifnull(prg_estadoproyecto.descripcion,'') as estado	,
			analisisdsc,
			prg_condicionpago.descripcion as condicionpago,
			case is_analisis when '1' then 'Si' else 'No' end as is_analisis,
			case prg_proyecto.is_viatico when '1' then 'Si' else 'No' end as is_viatico,
			case tipofactura when 'L' then 'Local' else 'Exterior' end as dscfactura,
			case costo.moneda when 'US$' then costo.montoviatico else (costo.montoviatico/prg_proyecto_detalle.tipocambio) end as montoviatico, 
			case costo.moneda when 'US$' then costo.montofee else (costo.montofee/prg_proyecto_detalle.tipocambio) end as montofee, 
			case costo.moneda when 'US$' then costo.montofeecert else (costo.montofeecert/prg_proyecto_detalle.tipocambio) end as montofeecert, 
			case costo.moneda when 'US$' then costo.montocourier else (costo.montocourier/prg_proyecto_detalle.tipocambio) end as montocourier, 
			case costo.moneda when 'US$' then costo.montoservicio else (costo.montoservicio/prg_proyecto_detalle.tipocambio) end as montoservicio, 

			case costo.moneda when 'US$' then (costo.montoservicio + costo.montoviatico + costo.montofeecert + costo.montofee  + costo.montocourier ) 
				else ((costo.montoservicio + costo.montoviatico + costo.montofeecert + costo.montofee  + costo.montocourier )/prg_proyecto_detalle.tipocambio) end as subtotal, 
			case costo.moneda when 'US$' then costo.reduccion else (costo.reduccion/prg_proyecto_detalle.tipocambio) end as montoreduccion, 
			case costo.moneda when 'US$' then costo.ampliacion else (costo.ampliacion/prg_proyecto_detalle.tipocambio) end as montoextension, 
			case costo.moneda when 'US$' then costo.notacredito else (costo.notacredito/prg_proyecto_detalle.tipocambio) end as notacredito, 
			costo.auditoria, costo.reporte,  
			
			case costo.moneda when 'US$' then costo.cartacor else (costo.cartacor/prg_proyecto_detalle.tipocambio) end as cartacor, 
			case costo.moneda when 'US$' then costo.analisis else (costo.analisis/prg_proyecto_detalle.tipocambio) end as analisis, 
			case costo.moneda when 'US$' then costo.cursos else (costo.cursos/prg_proyecto_detalle.tipocambio) end as cursos, 
			case costo.moneda when 'US$' then costo.intercompany else (costo.intercompany/prg_proyecto_detalle.tipocambio) end as intercompany, 
			case costo.moneda when 'US$' then costo.auditoria_no_anunciada else (costo.auditoria_no_anunciada/prg_proyecto_detalle.tipocambio) end as auditoria_no_anunciada, 
			case costo.moneda when 'US$' then costo.investigacion else (costo.investigacion/prg_proyecto_detalle.tipocambio) end as investigacion, 
			case costo.moneda when 'US$' then costo.otros else (costo.otros/prg_proyecto_detalle.tipocambio) end as otros, 
			case costo.moneda when 'US$' then costo.pm else (costo.pm/prg_proyecto_detalle.tipocambio) end as pm, 
			ifnull(costo.programa,'') as programadet,
			ifnull(tonelada,'') as tonelada,
			ifnull(dsc_puerto,'') as dsc_puerto,
			ifnull(preparacion,0) + ifnull(certificacion,0) + ifnull(viaje,0) as  otrosdias, '1' as orden,
			avg(prg_proyecto_detalle.tipocambio) as trm

		FROM prg_proyecto  INNER JOIN
			prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto left JOIN
			prg_condicionpago on prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion left join 
			prg_usuarios on prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario left join 
			prg_estadoproyecto on prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado inner JOIN
			prg_programacosto costo on costo.coddetalle=prg_proyecto_detalle.coddetalle  LEFT JOIN 
			prg_producto ON prg_proyecto_detalle.codproducto=prg_producto.codproducto 
		WHERE  costo.flag='1' and prg_proyecto.flag = '1' and prg_proyecto_detalle.flag='1'
				 AND prg_proyecto.id_pais= '$id_pais' ";

		
		if( $isjoin=='1') $sql.=" and prg_proyecto_detalle.coddetalle in (
			select coddetalle from prg_cronogramapago where flag='1' ";
		if($fec_facturadoi!='') $sql.= " and to_days(fechafactura) >= to_days('$fec_facturadoi')";
		if($fec_facturadof!='') $sql.= " and to_days(fechafactura) <= to_days('$fec_facturadof')";
		
		if( $isjoin=='1') $sql.= " ) ";

		if($proyecto!='') 
			$sql.= " and (proyect like '%".$proyecto."%' or  prg_proyecto.project_id like '%".$proyecto."%' )";
		
		if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";	
		if($fec_proyi!='') $sql.=" and to_days(concat_ws('/',anio,mes,28))>= to_days('$fec_proyi')";
		if($fec_proyf!='') $sql.=" and to_days(concat_ws('/',anio,mes,28))<= to_days('$fec_proyf')";
		

		$sql.=" GROUP BY costo.id_costo";
		
		//$sql.=" GROUP BY prg_proyecto_detalle.coddetalle";
		$sql.="	ORDER BY orden, proyect asc,anio,mes  ";
			
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	// resumen proyectos
	
	
	
	public function selec_res_TC_analis($id_pais,$proyecto,$anio,$tcEU){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT  MONTH(fecha_emision) AS mes, year(fecha_emision) as anio,
				ROUND(SUM( IFNULL(costo_usd,0) + IFNULL(cos_courier_usd,0) + (IFNULL(costo_eu,0)*$tcEU)),2) AS costo,
						COUNT(codtc) AS numero
				FROM tc_datos
				WHERE  flag = '1' AND id_pais='$id_pais' ";
					 
		if($anio!='') $sql.=" and  year(fecha_emision) =$anio";			 
		if($proyecto!='') $sql.= " and (proyecto like '%$proyecto%' or cu like '%$proyecto%')";
		
		$sql.=" GROUP BY 1,2 ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_res_TC_analis_xls($id_pais,$anio,$mes,$tcEU){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT 'TC' as estado,codtc,day(fecha_emision) as dia,
				date_format(fecha_emision,'%d/%m/%Y') as fechaf, concat_ws('-',cu,proyecto) as proyecto,
				ROUND(( IFNULL(costo_usd,0) + IFNULL(cos_courier_usd,0) + (IFNULL(costo_eu,0)*$tcEU)),2) AS costo
				FROM tc_datos
				WHERE  flag = '1' AND id_pais='$id_pais' ";
		if($mes!='') $sql.= " 	and month(fecha_emision) =$mes";
		if($anio!='') $sql.= " 	and  year(fecha_emision) =$anio";			 
		if($proyect!='') $sql.= " and (proyecto like '%$proyecto%' or cu like '%$proyecto%')";
		
		$sql.=" 
			GROUP BY 2,4
			union
			SELECT  'Laboratorio' as estado, codresultado,day(fechaenvio) AS dia,date_format(fechaenvio,'%d/%m/%Y') as fechaf,
				concat_ws('-',project_id,proyecto) as proyecto,
				ROUND(SUM(montocliente),2) AS costo
			FROM lab_resultado
			WHERE  flag = '1'  AND id_pais='$id_pais'";
			if($anio!='') $sql.= " and year(fechaenvio) =$anio";
			if($mes!='') $sql.= " and month(fechaenvio) =$mes";
			if($proyect!='') $sql.= " and (proyecto like '%$proyecto%' or  project_id like '%$proyecto%')";

		$sql.=" 
			GROUP BY 2,4
			ORDER BY 1 ASC  ";

		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_res_TC($id_pais,$proyecto,$anio,$tcEU){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT  MONTH(fecha_emision) AS mes, year(fecha_emision) as anio,
				ROUND(SUM(
				IFNULL(costo_usd,0) + IFNULL(cos_courier_usd,0) + (IFNULL(costo_eu,0)*$tcEU)
				),2) AS costo,
				COUNT(codtc) AS numero
			FROM tc_datos
			WHERE  flag = '1' AND id_pais='$id_pais' ";
			 
		if($anio!='') $sql.=" and  year(fecha_emision) =$anio";			 
		if($proyecto!='') 
			$sql.= " and (proyecto like '%$proyecto%' or  project_id like '%$proyecto%')";
			
		 $sql.=" GROUP BY 1,2
		 ORDER BY mes ASC  ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_res_proyectos($id_pais,$proyecto,$anio,$codejecutivo,$tcEuUS,$codestado){
		unset($this->listas);
		$this->listas=[];
		if(empty($anio))
			$anio=date("Y");
		
		 $sql="SELECT 
				prg_proyecto_detalle.coddetalle, prg_proyecto.project_id,prg_proyecto.id_pais, proyect,
				prg_proyecto.id_proyecto,
				CONCAT_WS('-',prg_proyecto.project_id, proyect) AS proyecto, 
				SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montoservicio*$tcEuUS WHEN moneda<>'US$' 
							THEN montoservicio/tipocambio ELSE montoservicio END,0)) AS servicio,
				SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montoventa*$tcEuUS WHEN moneda<>'US$' 
							THEN montoventa/tipocambio ELSE montoventa END,0)) + (vista2.costo) AS montoventa,
				COUNT(prg_proyecto_detalle.coddetalle) AS numero
				FROM prg_proyecto INNER JOIN
				 prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto LEFT JOIN
				 prg_usuarios ON prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario LEFT JOIN 
					prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado INNER JOIN
					prg_estadoproyecto_grupo ON prg_estadoproyecto.id_grupo=prg_estadoproyecto_grupo.id_grupo and
					prg_estadoproyecto.id_pais=prg_estadoproyecto_grupo.id_pais LEFT JOIN 
					(SELECT 
						IFNULL(SUM(montoservicio),0) AS montoservicio,
						IFNULL(SUM(IFNULL(montoservicio,0) + IFNULL(montofee,0)),0) AS montoventa,
						coddetalle
					FROM prg_programacosto 
					WHERE flag='1' 
					GROUP BY coddetalle
					) AS vista ON prg_proyecto_detalle.coddetalle=vista.coddetalle LEFT JOIN 
					
					(SELECT 
						ROUND(SUM( IFNULL(costo_usd,0) + IFNULL(cos_courier_usd,0) + ifnull(costo_eu,0)*$tcEuUS ),2) AS costo,
						cu,
						COUNT(codtc) AS numero
					FROM tc_datos 
					WHERE flag='1' AND id_pais='$id_pais' AND YEAR(fecha_emision)=$anio
					GROUP BY cu
					) AS vista2 ON  prg_proyecto.project_id=vista2.cu
		
				WHERE  prg_proyecto.flag = '1' 
					AND prg_proyecto_detalle.flag='1' and prg_estadoproyecto.id_grupo not in (2,9,7)
					AND prg_proyecto.id_pais= '$id_pais' 
					and anio= $anio  ";
 
			if($proyecto!='') 
				$sql.= " and (prg_proyecto.proyect like '%$proyecto%' or  prg_proyecto.project_id like '%$proyecto%')";
			if($codestado!='') $sql.=" and codestado='$codestado' ";		
			if($codejecutivo!='') $sql.=" and codejecutivo='$codejecutivo' ";	
	
		 $sql.=" GROUP BY 2,3
				ORDER BY montoventa DESC
				LIMIT 0,20 ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	// reporte ventas
	public function selec_res_labresultado($id_pais,$proyecto,$anio){
		unset($this->listas);
		$this->listas=[];
		
		  $sql="SELECT 
				ROUND(SUM(montocliente),2) AS costo,
				MONTH(fechaenvio) AS mes,
				year(fechaenvio) AS anio,
				concat_ws('.',MONTH(fechaenvio),year(fechaenvio)) as mesanio,
				COUNT(codresultado) AS numero
			FROM lab_resultado
			WHERE flag='1' AND id_pais='$id_pais' ";
		 
		if($anio!='') 	$sql.= " and YEAR(fechaenvio)=$anio";
		 
		if($proyecto!='') 
			$sql.= " and (proyecto like '%".$proyecto."%' or project_id like '%".$proyecto."%' )";
		 
		 $sql.=" GROUP BY MONTH(fechaenvio) , year(fechaenvio)     
				ORDER BY 2 asc  ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_res_etiqueta_logo($id_pais,$anio){
		$this->listas=[];
		$sql="SELECT ROUND(SUM( IFNULL(preciodol,0) ),1) AS costo, 
				MONTH(fecaprobacion) AS mes,
				year(fecaprobacion) AS anio,
				COUNT(codetiqueta) AS numero
				FROM etiqueta
				WHERE flag='1' and id_pais='$id_pais' ";
		if(!empty($anio))
			$sql.="  AND YEAR(fecaprobacion)= $anio ";	
		$sql.="		GROUP BY 2,3
				ORDER BY 2";
		
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;
	}
	
	public function selec_res_etiqueta($id_pais,$anio,$mes){
		$this->listas=[];
		$sql="SELECT *,date_format(fecrecepcion,'%d/%m/%y') as fecrecepcion_f,
					 date_format(fecaprobacion,'%d/%m/%y') as fecaprobacion_f,
					 concat_ws(' ',project_id,proyecto) as proyectofull
              FROM etiqueta
			WHERE flag = '1' and id_pais='$id_pais' AND YEAR(fecaprobacion)= $anio ";
		if(!empty($mes))
			$sql.="  AND month(fecaprobacion)= $mes ";	
		$sql.="	ORDER BY fecaprobacion";
		
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;
	}
	
	public function selec_res_proyxgrupoxanio($tcEuUS,$id_pais,$anio,$proyecto,$codestado,$codejecutivo){
		unset($this->listas);
		$this->listas=[]; // servicio
		 $sql="SELECT  mes,anio,concat_ws('.',mes,anio) as mesanio, id_grupo,
			 ROUND(SUM(abs(CASE  WHEN moneda='EUR' THEN montototal*$tcEuUS WHEN moneda<>'US$' THEN montototal/tipocambio ELSE montototal END)),2) AS costo,
			
			COUNT(prg_proyecto_detalle.coddetalle) AS numero,
			 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montofeecert*$tcEuUS WHEN moneda<>'US$' THEN montofeecert/tipocambio ELSE montofeecert END,0)) AS fee,
			 SUM(IFNULL(abs(CASE  WHEN moneda='EUR' THEN notacredito*$tcEuUS WHEN moneda<>'US$' THEN notacredito/tipocambio ELSE notacredito END),0)) AS tnotacredito,
			 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montocursos*$tcEuUS WHEN moneda<>'US$' THEN montocursos/tipocambio ELSE montocursos END,0)) AS tcursos,
			 abs(SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montoservicio*$tcEuUS   WHEN moneda<>'US$' THEN montoservicio/tipocambio   ELSE montoservicio   END,0))) AS servicio,
			 abs(SUM(IFNULL(CASE  WHEN moneda='EUR' THEN moampliacion*$tcEuUS WHEN moneda<>'US$' THEN moampliacion/tipocambio ELSE moampliacion END,0))) AS mampliacion,
			 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN intercompany*$tcEuUS WHEN moneda<>'US$' THEN intercompany/tipocambio ELSE intercompany END,0)) AS tintercompany,
			 SUM(IFNULL(abs(CASE  WHEN moneda='EUR' THEN ampliacion*$tcEuUS WHEN moneda<>'US$' THEN ampliacion/tipocambio ELSE ampliacion END),0)) AS tampliacion,
			 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN courier*$tcEuUS WHEN moneda<>'US$' THEN courier/tipocambio ELSE courier END,0)) AS tcourier,
			 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montoventa*$tcEuUS WHEN moneda<>'US$' THEN montoventa/tipocambio ELSE montoventa END,0)) AS tmontoventa,
			 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN cartacor*$tcEuUS WHEN moneda<>'US$' THEN cartacor/tipocambio ELSE cartacor END,0)) AS tcartacor,
			 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN auditoria_no_anunciada*$tcEuUS WHEN moneda<>'US$' THEN auditoria_no_anunciada/tipocambio ELSE auditoria_no_anunciada END,0)) AS tauditoria_no_anunciada,
			 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN investigacion*$tcEuUS WHEN moneda<>'US$' THEN investigacion/tipocambio ELSE investigacion END,0)) AS tinvestigacion,
			 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN otros*$tcEuUS WHEN moneda<>'US$' THEN otros/tipocambio ELSE otros END,0)) AS totros,
			 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montoviatico*$tcEuUS WHEN moneda<>'US$' THEN montoviatico/tipocambio ELSE montoviatico END,0)) AS tmontoviatico
			FROM prg_proyecto INNER JOIN
				prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto inner join
				prg_estadoproyecto on prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
				
				LEFT JOIN ( SELECT 
								IFNULL(sum(montofeecert),0) AS montofeecert,
								IFNULL(sum(abs(notacredito)),0) AS notacredito,
								IFNULL(sum(montoservicio),0) AS montoservicio,
								IFNULL(sum(ampliacion),0) AS ampliacion,
								IFNULL(sum(montocourier),0) AS courier,
								IFNULL(sum(cartacor),0) AS cartacor,
								IFNULL(SUM(montoviatico),0) AS montoviatico,
								IFNULL(sum(
									IFNULL(montoservicio,0) + IFNULL(montofee,0) + IFNULL(montofeecert,0) + IFNULL(montocourier,0) + IFNULL(montoviatico,0) + IFNULL(cartacor,0)+ IFNULL(analisis,0) + IFNULL(cursos,0) - ABS(ifnull(notacredito,0))
								),0) AS montoventa,
								IFNULL(sum(
									IFNULL(montoservicio,0) -  abs(IFNULL(ampliacion,0)) - ABS(ifnull(notacredito,0))
								),0) AS moampliacion,
								IFNULL(sum(intercompany),0) AS intercompany,
								IFNULL(sum(cursos),0) AS montocursos,
								IFNULL(sum(auditoria_no_anunciada),0) AS auditoria_no_anunciada,
								IFNULL(sum(investigacion),0) AS investigacion,
								IFNULL(sum(otros),0) AS otros,
								coddetalle
					FROM prg_programacosto 
					WHERE flag='1' 
					GROUP BY coddetalle
				) AS vista ON prg_proyecto_detalle.coddetalle=vista.coddetalle
		
			WHERE  prg_proyecto.flag = '1' 
			  AND prg_proyecto_detalle.flag='1' 
			   AND ifnull(prg_proyecto_detalle.isanulado,'0')='0' 
			 AND prg_proyecto.id_pais= '$id_pais'  
			 and id_grupo not in (8) "; 
			 // 6 es venta nueva, 8 analisis, sons acados de otra tabla
		 
 
		if($proyecto!='') 
			$sql.= " and (prg_proyecto.proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%' )";
		if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado='$codestado' ";		
		if($codejecutivo!='') $sql.=" and codejecutivo='$codejecutivo' ";	
		if($anio!='') $sql.=" and anio=$anio ";			
		
		 $sql.=" GROUP BY mes,id_grupo,anio
		 ORDER BY mes asc  ";
		
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_servicio_mesanio_baja($tcEuUS,$id_pais,$proyecto,$codestado,$codejecutivo){
		unset($this->listas);
		$this->listas=[]; // servicio
		$sql="SELECT  CONCAT_WS('.',mes,anio) AS mesanio,
				COUNT(prg_proyecto_detalle.coddetalle) AS numero,
				ROUND(ABS(SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montoservicio*$tcEuUS   WHEN moneda<>'US$' THEN montoservicio/tipocambio   ELSE montoservicio   END,0))),0) AS servicio
				FROM prg_proyecto INNER JOIN
					prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
					prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
					LEFT JOIN ( SELECT 
							IFNULL(SUM(montoservicio),0) AS montoservicio,
							coddetalle
						FROM prg_programacosto 
						WHERE flag='1' 
						GROUP BY coddetalle
					) AS vista ON prg_proyecto_detalle.coddetalle=vista.coddetalle

				WHERE  prg_proyecto.flag = '1' 
				  AND prg_proyecto_detalle.flag='1' 
				   AND IFNULL(prg_proyecto_detalle.isanulado,'0')='0' 
				 AND prg_proyecto.id_pais= '$id_pais'  
				 AND id_grupo IN (2) ";
			if($proyecto!='') 
			$sql.= " and (prg_proyecto.proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%' )";
			if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado='$codestado' ";		
			if($codejecutivo!='') $sql.=" and codejecutivo='$codejecutivo' ";					 
			
			$sql.=" GROUP BY mesanio";
		
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_res_proyxgrupoxmes_amplia($tcEuUS,$id_pais,$anio,$proyecto,$codestado,$codejecutivo){
		unset($this->listas);
		$this->listas=[];
		 $sql="SELECT  mes,anio, concat_ws('.',mes,anio) as mesanio,
				SUM(ROUND(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN ampliacion*$tcEuUS 
						WHEN prg_proyecto_detalle.moneda<>'US$' THEN ampliacion/tipocambio ELSE ampliacion END,2)) AS costo
			FROM prg_proyecto INNER JOIN
				prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
				prg_programacosto ON prg_proyecto_detalle.coddetalle=prg_programacosto.coddetalle
			WHERE prg_programacosto.flag='1'  and
			prg_proyecto.id_pais= '$id_pais'";
			
			if($anio!='') $sql.=" and anio=$anio ";		 
			if($proyecto!='') 
				$sql.= " and (prg_proyecto.proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%' )";
			
			if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado='$codestado' ";		
			if($codejecutivo!='') $sql.=" and codejecutivo='$codejecutivo' ";					
			
			$sql.="	GROUP BY mes,anio";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}

	public function selec_res_proyxgrupoxmes_auditoria_no_anunciada($tcEuUS,$id_pais,$anio,$proyecto,$codestado,$codejecutivo){
		unset($this->listas);
		$this->listas=[];
		 $sql="SELECT  mes,anio, concat_ws('.',mes,anio) as mesanio,
				SUM(ROUND(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN auditoria_no_anunciada*$tcEuUS 
						WHEN prg_proyecto_detalle.moneda<>'US$' THEN auditoria_no_anunciada/tipocambio ELSE auditoria_no_anunciada END,2)) AS costo
			FROM prg_proyecto INNER JOIN
				prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
				prg_programacosto ON prg_proyecto_detalle.coddetalle=prg_programacosto.coddetalle
			WHERE prg_programacosto.flag='1'  and
			prg_proyecto.id_pais= '$id_pais'";
			
			if($anio!='') $sql.=" and anio=$anio ";		 
			if($proyect!='') 
				$sql.= " and (prg_proyecto.proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%' )";
			
			if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado='$codestado' ";		
			if($codejecutivo!='') $sql.=" and codejecutivo='$codejecutivo' ";					
			
			$sql.="	GROUP BY mes,anio";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function selec_grupobytipo($id_pais,$tipo){
		$sql="SELECT GROUP_CONCAT(id_grupo) as grupo 
			FROM prg_estadoproyecto_grupo WHERE flag='1' AND id_pais='$id_pais'  AND tipo='$tipo'";
		$consulta=$this->db->consultarOne($sql);		
		
        return $consulta;	
		
	}
	public function selec_res_proyxgrupoxmes_investigacion($tcEuUS,$id_pais,$anio,$proyecto,$codestado,$codejecutivo){
		unset($this->listas);
		$this->listas=[];
		 $sql="SELECT  mes,anio, concat_ws('.',mes,anio) as mesanio,
				SUM(ROUND(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN investigacion*$tcEuUS 
						WHEN prg_proyecto_detalle.moneda<>'US$' THEN investigacion/tipocambio ELSE investigacion END,2)) AS costo
			FROM prg_proyecto INNER JOIN
				prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
				prg_programacosto ON prg_proyecto_detalle.coddetalle=prg_programacosto.coddetalle
			WHERE prg_programacosto.flag='1'  and
			prg_proyecto.id_pais= '$id_pais'";
			
			if($anio!='') $sql.=" and anio=$anio ";		 
			if($proyect!='') 
				$sql.= " and (prg_proyecto.proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%' )";
			
			if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado='$codestado' ";		
			if($codejecutivo!='') $sql.=" and codejecutivo='$codejecutivo' ";					
			
			$sql.="	GROUP BY mes,anio";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_res_proyxgrupoxmes_otros($tcEuUS,$id_pais,$anio,$proyecto,$codestado,$codejecutivo){
		unset($this->listas);
		$this->listas=[];
		
		 $sql="SELECT  mes,anio, concat_ws('.',mes,anio) as mesanio,
				SUM(ROUND(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN otros*$tcEuUS 
						WHEN prg_proyecto_detalle.moneda<>'US$' THEN otros/tipocambio ELSE otros END,2)) AS costo
			FROM prg_proyecto INNER JOIN
				prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
				prg_programacosto ON prg_proyecto_detalle.coddetalle=prg_programacosto.coddetalle
			WHERE prg_programacosto.flag='1'  and
			prg_proyecto.id_pais= '$id_pais'";
			
			if($anio!='') $sql.=" and anio=$anio ";		 
			if($proyect!='') 
				$sql.= " and (prg_proyecto.proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%' )";
			
			if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado='$codestado' ";		
			if($codejecutivo!='') $sql.=" and codejecutivo='$codejecutivo' ";					
			
			$sql.="	GROUP BY mes,anio";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function selec_cartaxampliacionxanio($tcEuUS,$id_pais,$anio){
		unset($this->listas);
		$this->listas=[];
		
		 $sql="SELECT  mes,anio,
			ABS(SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN ampliacion*$tcEuUS  WHEN prg_proyecto_detalle.moneda<>'US$' 
							THEN ampliacion/tipocambio ELSE ampliacion END,0))) AS ampliacion,
			ABS(SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN cartacor*$tcEuUS  WHEN prg_proyecto_detalle.moneda<>'US$' 
							THEN cartacor/tipocambio ELSE cartacor END,0))) AS cartacor
		
			FROM prg_proyecto INNER JOIN
				prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
				prg_programacosto ON prg_proyecto_detalle.coddetalle=prg_programacosto.coddetalle inner join
				prg_estadoproyecto on prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
			WHERE prg_programacosto.flag='1'  and prg_proyecto.id_pais= '$id_pais' and anio=$anio 
			GROUP BY mes ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	
	
	public function selec_res_estado_grupo($id_pais,$tipo,$id=null){
		unset($this->listas);
		$this->listas=[];
		
		$sql="select id_grupo, upper(descripcion) as descripcion 
				from prg_estadoproyecto_grupo
				where flag='1' and id_pais='$id_pais' and tipo in ($tipo) ";
		if(!empty($id))
				$sql.=" and id_grupo in ($id)";
			
		$sql.="		order by orden,descripcion ";

	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function selec_res_calendario($id_pais,$anio){
		unset($this->listas);
		$this->listas=[];
		
		$sql = "
			SELECT
				count(distinct prg_calendario.id) total,
				concat_ws('.',mes_inicio,anio_inicio) as aniomes,
				IFNULL(SUM(utizado_monto_soles),0) AS soles,
				IFNULL(SUM(round(utizado_monto_soles/3.85,2)),0) AS soles_dolares,
				IFNULL(SUM(utizado_monto_dolares),0) AS dolares,
				IFNULL(SUM(round(utizado_monto_soles/3.85,2)),0) + IFNULL(SUM(utizado_monto_dolares),0) AS subtotal,
				mes_inicio
			FROM prg_calendario  WHERE flag='1' AND id_pais='$id_pais' AND mes_inicio>0
			GROUP BY mes_inicio,anio_inicio
		";

	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function selec_res_Fess($id_pais,$tcEuUS){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT  concat_ws('.',mes,anio) as aniomes,
			
				IFNULL(SUM(CASE  WHEN c.moneda='EUR' THEN pm*$tcEuUS WHEN c.moneda<>'US$' THEN pm/tipocambio ELSE pm END),0) 
				+
				IFNULL(SUM(CASE  WHEN c.moneda='EUR' THEN montofeecert*$tcEuUS WHEN c.moneda<>'US$' THEN montofeecert/tipocambio ELSE montofeecert END),0) 
				+ 
				IFNULL(SUM(CASE  WHEN c.moneda='EUR' THEN montofee*$tcEuUS WHEN c.moneda<>'US$' THEN montofee/tipocambio ELSE montofee END),0)
				AS subtotal, 
				mes,anio
			FROM 
				prg_proyecto INNER JOIN
				prg_proyecto_detalle  d ON prg_proyecto.id_proyecto= d.id_proyecto INNER JOIN
				 prg_programacosto c ON c.coddetalle=d.coddetalle
			WHERE c.flag='1'  AND id_pais='$id_pais' and mes>0
			GROUP BY mes,anio";
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	
	public function selec_res_proyxgrupoxmes_reduce($tcEuUS,$id_pais,$anio,$proyecto,$codestado,$codejecutivo){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT  mes,anio, concat_ws('.',mes,anio) as mesanio,
				SUM(ROUND(ABS(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN reduccion*$tcEuUS
			WHEN prg_proyecto_detalle.moneda<>'US$' THEN reduccion/tipocambio ELSE reduccion END,0)),2)) AS costo
			FROM prg_proyecto INNER JOIN
				prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
				prg_programacosto ON prg_proyecto_detalle.coddetalle=prg_programacosto.coddetalle
			WHERE prg_programacosto.flag='1'  AND 
			prg_proyecto.id_pais= '$id_pais'  ";
		if($anio!='') $sql.=" and anio=$anio ";		 
		
		if($proyecto!='') 
			$sql.= " and (prg_proyecto.proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%' )";
		if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado='$codestado' ";		
		if($codejecutivo!='') $sql.=" and codejecutivo='$codejecutivo' ";					
		
		$sql.="	GROUP BY mes,anio";

	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_res_proyxgrupoxmes_reduce_lista($tcEuUS,$id_pais,$anio,$proyecto,$codestado,$codejecutivo,$mes){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT  prg_programacosto.id_costo,
			prg_proyecto_detalle.coddetalle as id,prg_proyecto_detalle.observacion,
			prg_proyecto.id_proyecto, prg_proyecto.project_id, proyect, city, state,
			country, telephone, mobile, modules, anio,mes,fax,
			ifnull(dsc_programaren,'') AS programas,
			abs(ifnull(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN reduccion*$tcEuUS WHEN prg_proyecto_detalle.moneda<>'US$' 
				THEN reduccion/tipocambio ELSE reduccion END,0)) as importe,
			ifnull(prg_estadoproyecto.descripcion,'') as estado,	
			prg_proyecto_detalle.moneda,
			ifnull(prg_usuarios.nombres,'') as comercial
			FROM prg_proyecto INNER JOIN
				prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
				prg_programacosto ON prg_proyecto_detalle.coddetalle=prg_programacosto.coddetalle left join 
			 prg_estadoproyecto on prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado left join 
			 prg_usuarios on prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario
			WHERE prg_proyecto_detalle.flag='1'  and prg_programacosto.flag='1' AND 
			prg_proyecto.id_pais= '$id_pais'  and reduccion<>0";
	
			if($anio!='') $sql.=" and anio=$anio ";		 
			if($mes!='') $sql.=" and mes=$mes ";		
			if($proyecto!='') 
				$sql.= " and (prg_proyecto.proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%' )";
			if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado='$codestado' ";		
			if($codejecutivo!='') $sql.=" and codejecutivo='$codejecutivo' ";					
		
		 $sql.="	GROUP BY 1 ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_res_xlsdetalle($id_pais,$anio,$mes,$id_grupo,$tcEuUS,$proyecto,$codestado,$codejecutivo,$tipo,$grupo1456=null){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT distinct
			prg_proyecto_detalle.coddetalle as id,prg_proyecto_detalle.observacion,
			prg_proyecto.id_proyecto, prg_proyecto.project_id, proyect, city, state,
			country, telephone, mobile, modules, anio,mes,fax,
			ifnull(dsc_programaren,'') AS programas,
			abs(ifnull(CASE  WHEN moneda='EUR' THEN prg_proyecto_detalle.montototal*$tcEuUS WHEN moneda<>'US$' 
				THEN prg_proyecto_detalle.montototal/tipocambio ELSE prg_proyecto_detalle.montototal END,0)) as importe,
			prg_proyecto_detalle.moneda,
			ifnull(prg_condicionpago.descripcion,'') as condicionpago,
			ifnull(prg_usuarios.nombres,'') as comercial,
			ifnull(prg_estadoproyecto.descripcion,'') as estado	,
			analisisdsc,
			case is_analisis when '1' then 'Si' else 'No' end as is_analisis,
			case prg_proyecto.is_viatico when '1' then 'Si' else 'No' end as is_viatico,
			ifnull(numcobranza,'') as numcobranza,
			SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montoservicio*$tcEuUS WHEN moneda<>'US$' 
						THEN montoservicio/tipocambio ELSE montoservicio END,0)) AS servicio,
			SUM(IFNULL(CASE  WHEN moneda='EUR' THEN cursos*$tcEuUS WHEN moneda<>'US$' 
						THEN cursos/tipocambio ELSE cursos END,0)) AS cursos,
			SUM(IFNULL(CASE  WHEN moneda='EUR' THEN notacredito*$tcEuUS WHEN moneda<>'US$' 
						THEN notacredito/tipocambio ELSE notacredito END,0)) AS notacredito,
			SUM(IFNULL(CASE  WHEN moneda='EUR' THEN intercompany*$tcEuUS WHEN moneda<>'US$' 
						THEN intercompany/tipocambio ELSE intercompany END,0)) AS intercompany,			
			SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montoventa*$tcEuUS WHEN moneda<>'US$' 
						THEN montoventa/tipocambio ELSE montoventa END,0)) AS tmontoventa,
			SUM(IFNULL(CASE  WHEN moneda='EUR' THEN ampliacion*$tcEuUS WHEN moneda<>'US$' 
						THEN ampliacion/tipocambio ELSE ampliacion END,0)) AS ampliacion,			
			SUM(IFNULL(CASE  WHEN moneda='EUR' THEN cartacor*$tcEuUS WHEN moneda<>'US$' 
						THEN cartacor/tipocambio ELSE cartacor END,0)) AS cartacor,	
								
			SUM(IFNULL(CASE  WHEN moneda='EUR' THEN auditoria_no_anunciada*$tcEuUS WHEN moneda<>'US$' 
						THEN auditoria_no_anunciada/tipocambio ELSE auditoria_no_anunciada END,0)) AS auditoria_no_anunciada,				
			
			SUM(IFNULL(CASE  WHEN moneda='EUR' THEN investigacion*$tcEuUS WHEN moneda<>'US$' 
						THEN investigacion/tipocambio ELSE investigacion END,0)) AS investigacion,					
			
			SUM(IFNULL(CASE  WHEN moneda='EUR' THEN otros*$tcEuUS WHEN moneda<>'US$' 
						THEN otros/tipocambio ELSE otros END,0)) AS otros,	
			comentario,	
			id_grupo
		FROM prg_proyecto INNER JOIN
			 prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto left join
			 prg_condicionpago on prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion left join 
			 prg_usuarios on prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario left join 
			 prg_estadoproyecto on prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado 
			 LEFT JOIN ( SELECT 
						IFNULL(sum(montofeecert),0) AS montofeecert,
						IFNULL(sum(montofee),0) AS montofee,
						IFNULL(sum(montocourier),0) AS montocourier,
						IFNULL(sum(montoservicio),0) AS montoservicio,
						IFNULL(sum(montoviatico),0) AS montoviatico,
						IFNULL(sum(analisis),0) AS analisis,
						IFNULL(sum(cursos),0) AS cursos,
						IFNULL(sum(cartacor),0) AS cartacor,						
						IFNULL(sum(ampliacion),0) AS ampliacion,
						IFNULL(sum(intercompany),0) AS intercompany,
						IFNULL(sum(abs(notacredito)),0) AS notacredito,
						IFNULL(SUM(
									IFNULL(montoservicio,0) + IFNULL(montofee,0) + IFNULL(montofeecert,0) + IFNULL(montocourier,0) + IFNULL(montoviatico,0) + IFNULL(cartacor,0)+ IFNULL(analisis,0) + IFNULL(cursos,0) - ABS(IFNULL(notacredito,0))
								),0) AS montoventa,
								
						IFNULL(sum(abs(auditoria_no_anunciada)),0) AS auditoria_no_anunciada,
						IFNULL(sum(abs(investigacion)),0) AS investigacion,
						IFNULL(sum(abs(otros)),0) AS otros,
						
						comentario,
					coddetalle
				FROM prg_programacosto 
				WHERE flag='1' 
				GROUP BY coddetalle
			) AS vista ON prg_proyecto_detalle.coddetalle=vista.coddetalle
		WHERE  prg_proyecto.flag = '1' 
			AND prg_proyecto_detalle.flag='1'
			AND prg_proyecto.id_pais= '$id_pais' ";

		if($proyect!='') 
			$sql.= " and (proyect like '%$proyecto%' or prg_proyecto.project_id='$proyecto')";
		
		if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";	
		if($codejecutivo!='') $sql.=" and prg_proyecto_detalle.codejecutivo=$codejecutivo ";	
		if($id_grupo!='') $sql.=" and id_grupo in ($id_grupo) ";	
		if(!empty($grupo1456)) $sql.=" and id_grupo in (1,4,5,6)";	
		if($anio!='') $sql.=" and anio= $anio ";
		if($mes!='') $sql.=" and mes= $mes ";
		if($tipo!='') 
			$sql.=" and id_grupo in (SELECT id_grupo FROM prg_estadoproyecto_grupo 
									WHERE flag='1' AND tipo ='$tipo') ";		
		
		$sql.=" GROUP BY 1 ";

		$sql.="	ORDER BY 11,12,4 ";

		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_res_xlsdetalle_xglosa($id_pais,$anio,$mes,$id_grupo,$tcEuUS,$proyecto,$codestado,$codejecutivo,$tipo,$grupo1456=null){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT DISTINCT
				prg_proyecto_detalle.coddetalle AS id,prg_proyecto_detalle.observacion,
				prg_proyecto.id_proyecto, prg_proyecto.project_id, proyect, city, state,
				country, telephone, mobile, modules, anio,mes,fax,
				IFNULL(prg_programacosto.programa,'') AS programas,
				ABS(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN prg_proyecto_detalle.montototal*$tcEuUS WHEN prg_proyecto_detalle.moneda<>'US$' 
					THEN prg_proyecto_detalle.montototal/tipocambio ELSE prg_proyecto_detalle.montototal END,0)) AS importe,
				prg_proyecto_detalle.moneda,
				IFNULL(prg_condicionpago.descripcion,'') AS condicionpago,
				IFNULL(prg_usuarios.nombres,'') AS comercial,
				IFNULL(prg_estadoproyecto.descripcion,'') AS estado	,
				analisisdsc,
				CASE is_analisis WHEN '1' THEN 'Si' ELSE 'No' END AS is_analisis,
				CASE prg_proyecto.is_viatico WHEN '1' THEN 'Si' ELSE 'No' END AS is_viatico,
				IFNULL(numcobranza,'') AS numcobranza,
				SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN montoservicio*$tcEuUS WHEN prg_proyecto_detalle.moneda<>'US$' 
							THEN montoservicio/tipocambio ELSE montoservicio END,0)) AS servicio,
				SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN cursos*$tcEuUS WHEN prg_proyecto_detalle.moneda<>'US$' 
							THEN cursos/tipocambio ELSE cursos END,0)) AS cursos,
				SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN montoviatico*$tcEuUS WHEN prg_proyecto_detalle.moneda<>'US$' 
							THEN montoviatico/tipocambio ELSE montoviatico END,0)) AS montoviatico,
				abs(SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN notacredito*$tcEuUS WHEN prg_proyecto_detalle.moneda<>'US$' 
							THEN notacredito/tipocambio ELSE notacredito END,0))) AS notacredito,
				SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN intercompany*$tcEuUS WHEN prg_proyecto_detalle.moneda<>'US$' 
							THEN intercompany/tipocambio ELSE intercompany END,0)) AS intercompany,			
				SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN (IFNULL(montoservicio,0) + IFNULL(montofee,0) + IFNULL(montofeecert,0) + IFNULL(montocourier,0) + IFNULL(montoviatico,0) + IFNULL(cartacor,0)+ IFNULL(analisis,0) + IFNULL(cursos,0) - ABS(IFNULL(notacredito,0)))*$tcEuUS WHEN prg_proyecto_detalle.moneda<>'US$' 
							THEN (IFNULL(montoservicio,0) + IFNULL(montofee,0) + IFNULL(montofeecert,0) + IFNULL(montocourier,0) + IFNULL(montoviatico,0) + IFNULL(cartacor,0)+ IFNULL(analisis,0) + IFNULL(cursos,0) - ABS(IFNULL(notacredito,0)))/tipocambio 
							ELSE (IFNULL(montoservicio,0) + IFNULL(montofee,0) + IFNULL(montofeecert,0) + IFNULL(montocourier,0) + IFNULL(montoviatico,0) + IFNULL(cartacor,0)+ IFNULL(analisis,0) + IFNULL(cursos,0) - ABS(IFNULL(notacredito,0))) END,0)) AS tmontoventa,
				abs(SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN ampliacion*$tcEuUS WHEN prg_proyecto_detalle.moneda<>'US$' 
							THEN ampliacion/tipocambio ELSE ampliacion END,0))) AS ampliacion,			
				SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN cartacor*$tcEuUS WHEN prg_proyecto_detalle.moneda<>'US$' 
							THEN cartacor/tipocambio ELSE cartacor END,0)) AS cartacor,	
									
				SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN auditoria_no_anunciada*$tcEuUS WHEN prg_proyecto_detalle.moneda<>'US$' 
							THEN auditoria_no_anunciada/tipocambio ELSE auditoria_no_anunciada END,0)) AS auditoria_no_anunciada,				
				
				SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN investigacion*$tcEuUS when prg_proyecto_detalle.moneda<>'US$' 
							THEN investigacion/tipocambio ELSE investigacion END,0)) AS investigacion,					
				
				SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN otros*$tcEuUS WHEN prg_proyecto_detalle.moneda<>'US$' 
							THEN otros/tipocambio ELSE otros END,0)) AS otros,	
				comentario,
					
				id_grupo,
				prg_programacosto.id_costo
			FROM prg_proyecto INNER JOIN
				 prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto LEFT JOIN
				 prg_condicionpago ON prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion LEFT JOIN 
				 prg_usuarios ON prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario LEFT JOIN 
				 prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado 
				 inner JOIN prg_programacosto ON prg_proyecto_detalle.coddetalle=prg_programacosto.coddetalle AND prg_programacosto.flag='1'
			WHERE  prg_proyecto.flag = '1' 	AND prg_proyecto_detalle.flag='1'
			AND prg_proyecto.id_pais= '$id_pais' 
			 AND IFNULL(prg_proyecto_detalle.isanulado,'0')='0' ";

		if($proyect!='') 
			$sql.= " and (proyect like '%$proyecto%' or prg_proyecto.project_id='$proyecto')";
		
		if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";	
		if($codejecutivo!='') $sql.=" and prg_proyecto_detalle.codejecutivo=$codejecutivo ";	
		if($id_grupo!='') $sql.=" and id_grupo in ($id_grupo) ";	
		if(!empty($grupo1456)) $sql.=" and id_grupo in (1,4,5,6)";	
		if($anio!='') $sql.=" and anio= $anio ";
		if($mes!='') $sql.=" and mes= $mes ";
		if($tipo!='') 
			$sql.=" and id_grupo in (SELECT id_grupo FROM prg_estadoproyecto_grupo 
									WHERE flag='1' AND id_pais='$id_pais' and  tipo ='$tipo') ";		
		
		$sql.=" GROUP BY prg_programacosto.id_costo ";
		$sql.="	ORDER BY 11,12,4 ";

		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	// reporte cuota por proyectos
	
	public function select_estado_select($id_pais){
		unset($this->listas);
		$this->listas=[];
		
		$sql="select * from prg_estadoproyecto where flag='1' and id_pais='$id_pais' order by descripcion";
		
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_cuota_proyectos($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT prg_proyecto.id_proyecto, prg_proyecto.project_id, proyect, city, state, 
				country, telephone, mobile, modules, 
				GROUP_CONCAT(programa SEPARATOR ', ') AS programas, moneda, montototal, observacion,
				IFNULL(prg_condicionpago.descripcion,'') AS condicionpago,
				IFNULL(prg_usuarios.nombres,'') AS comercial,
				IFNULL(prg_estadoproyecto.descripcion,'') AS estado,
				concat_ws('/',mes,anio) as aniomes
			FROM prg_proyecto INNER JOIN 
				 prg_proyecto_detalle ON prg_proyecto.id_proyecto=prg_proyecto_detalle.id_proyecto LEFT JOIN 
				 prg_proyecto_programa ON prg_proyecto.project_id= prg_proyecto_programa.project_id left JOIN
				 prg_condicionpago ON prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion
					LEFT JOIN prg_usuarios ON prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario
					LEFT JOIN prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
			WHERE  prg_proyecto.flag = '1' and prg_proyecto_detalle.flag = '1' $searchQuery ";
	
			$sql.=" GROUP BY prg_proyecto_detalle.coddetalle 
					order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
		
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_total_cuota_proyectos($searchQuery){
		
		$sql="SELECT COUNT(DISTINCT prg_proyecto_detalle.coddetalle) AS total
			FROM prg_proyecto INNER JOIN 
				 prg_proyecto_detalle ON prg_proyecto.id_proyecto=prg_proyecto_detalle.id_proyecto LEFT JOIN 
				 prg_proyecto_programa ON prg_proyecto.project_id= prg_proyecto_programa.project_id left JOIN
				 prg_condicionpago ON prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion
					LEFT JOIN prg_usuarios ON prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario
					LEFT JOIN prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
			WHERE  prg_proyecto.flag = '1' and prg_proyecto_detalle.flag = '1' $searchQuery ";
		
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	// grafico personal
	public function selec_grafPers_porcentaje($G_tc,$id_pais,$codestado,$codejecutivo,$fechai,$fechaf,$proyecto){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT  
            ROUND(SUM(CASE moneda WHEN 'EUR' THEN montototal*$G_tc WHEN 'S/.' THEN montototal/$G_tc ELSE montototal END),2) AS porcentaje
          	FROM prg_proyecto INNER JOIN 
                  prg_proyecto_detalle ON prg_proyecto.id_proyecto=prg_proyecto_detalle.id_proyecto 
		WHERE  prg_proyecto.flag = '1' AND prg_proyecto_detalle.flag = '1'
			AND prg_proyecto.id_pais='$id_pais'  ";
          
        if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";
        if($codejecutivo!='') $sql.=" and prg_proyecto_detalle.codejecutivo=$codejecutivo ";
        if($proyecto!='') $sql.="  AND (proyect LIKE '%$proyecto%' OR prg_proyecto.project_id='$proyecto')  ";
        if($fechai!='') $sql.= " and to_days(concat_ws('-',anio,mes,01)) >= to_days('$fechai')";
		if($fechaf!='') $sql.= " and to_days(concat_ws('-',anio,mes,01)) <= to_days('$fechaf')";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_grafPers_detalle($G_tc,$id_pais,$codestado,$codejecutivo,$fechai,$fechaf,$proyecto,$total){
		unset($this->listas);
		$this->listas=[];
		
		 $sql="SELECT  
				ROUND(SUM((CASE moneda WHEN 'EUR' THEN montototal*$G_tc WHEN 'S/.' THEN montototal/$G_tc ELSE montototal END)*100/$total),2) AS porcentaje,
		 	    IFNULL(prg_usuarios.nombres,'') AS actividad,
			    round(sum(CASE moneda WHEN 'EUR' THEN montototal*$G_tc WHEN 'S/.' THEN montototal/$G_tc ELSE montototal END),0) as venta
			FROM prg_proyecto INNER JOIN 
				 prg_proyecto_detalle ON prg_proyecto.id_proyecto=prg_proyecto_detalle.id_proyecto INNER JOIN
				 prg_condicionpago ON prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion	inner JOIN 
				 prg_usuarios ON prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario inner join 
				 prg_auditor on prg_usuarios.id_auditor=prg_auditor.id_auditor and prg_auditor.flgstatus='1'
			WHERE  prg_proyecto.flag = '1' 	and flgstatus='1' and flgcomercial='1' AND prg_proyecto.id_pais='$id_pais' ";
          
          if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";
          if($codejecutivo!='') $sql.=" and prg_proyecto_detalle.codejecutivo=$codejecutivo ";
          if($proyecto!='') $sql.=" and project like '%$proyecot%' ";
          if($fechai!='') $sql.= " and to_days(concat_ws('/',anio,mes,28)) >= to_days('$fechai')";
		  if($fechaf!='') $sql.= " and to_days(concat_ws('/',anio,mes,28)) <= to_days('$fechaf')";
          
        $sql.=" GROUP BY prg_proyecto_detalle.codejecutivo
		ORDER BY 1 DESC ";  
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function selec_grafEsta_detalle($tcEuUS,$G_tc,$id_pais,$codestado,$codejecutivo,$fechai,$fechaf,$proyecto,$total){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT  
				ROUND(SUM((CASE moneda WHEN 'EUR' THEN montototal*$tcEuUS 
				WHEN 'S/.' THEN montototal/$G_tc ELSE 	montototal END)*100/$total),2) AS porcentaje,
				IFNULL(prg_estadoproyecto.descripcion,'S/E') AS actividad,
				round(sum(CASE moneda WHEN 'EUR' THEN montototal*$tcEuUS 
				WHEN 'S/.' THEN montototal/$G_tc ELSE 		montototal END),0) as venta
			FROM prg_proyecto INNER JOIN 
					 prg_proyecto_detalle ON prg_proyecto.id_proyecto=prg_proyecto_detalle.id_proyecto INNER JOIN
			 prg_usuarios ON prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario LEFT JOIN
					 prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
			WHERE  prg_proyecto.flag = '1' AND prg_proyecto_detalle.flag = '1'
				AND prg_proyecto.id_pais='$id_pais' ";
          
          if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";
          if($codejecutivo!='') $sql.=" and prg_proyecto_detalle.codejecutivo=$codejecutivo ";
          if($proyecto!='') $sql.="  AND (proyect LIKE '%$proyecto%' OR prg_proyecto.project_id='$proyecto') ";
          if($fechai!='') $sql.= " and to_days(concat_ws('/',anio,mes,01)) >= to_days('$fechai')";
		  if($fechaf!='') $sql.= " and to_days(concat_ws('/',anio,mes,01)) <= to_days('$fechaf')";
          
        $sql.=" GROUP BY prg_proyecto_detalle.codestado
		ORDER BY 1 DESC 
		limit 0,15";  
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_xls_proyecto($tcEuUS,$G_tc,$id_pais,$codestado,$codejecutivo,$fechai,$fechaf,$proyecto,$total){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT prg_proyecto.id_proyecto, prg_proyecto.project_id, proyect, city, 
				state, country, telephone, mobile, modules, 
				GROUP_CONCAT(programa SEPARATOR ', ') AS programas, moneda, montototal, observacion,
				IFNULL(prg_condicionpago.descripcion,'') AS condicionpago,
				IFNULL(prg_usuarios.nombres,'') AS comercial,
				IFNULL(prg_estadoproyecto.descripcion,'') AS estado,
				concat_ws('/',mes,anio) as aniomes,
				CASE moneda WHEN 'S/.' THEN  montototal/$G_tc  WHEN 'EUR' THEN  montototal*$tcEuUS ELSE montototal END
					as importeus
			FROM prg_proyecto INNER JOIN 
							 prg_proyecto_detalle ON prg_proyecto.id_proyecto=prg_proyecto_detalle.id_proyecto LEFT JOIN 
				 prg_proyecto_programa ON prg_proyecto.project_id= prg_proyecto_programa.project_id left JOIN
				 prg_condicionpago ON prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion
					LEFT JOIN prg_usuarios ON prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario
					LEFT JOIN prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
			WHERE  prg_proyecto.flag = '1'  AND prg_proyecto_detalle.flag = '1'
				AND prg_proyecto.id_pais='$id_pais' ";

		if($proyecto!='') $sql.= " and (proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%')";
		
		if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";	
		if($codejecutivo!='') $sql.=" and prg_proyecto_detalle.codejecutivo=$codejecutivo ";	
		if($fechai!='') $sql.=" and to_days(concat_ws('/',anio,mes,01))>= to_days('$fechai')";
		if($fechaf!='') $sql.=" and to_days(concat_ws('/',anio,mes,01))<= to_days('$fechaf')";
		
		$sql.=" GROUP BY prg_proyecto_detalle.coddetalle ";
		$sql.="	ORDER BY 1 asc  ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_xls_sincuotaproyecto($id_pais,$fechai,$fechaf){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT DISTINCT prg_proyecto.project_id, TRIM(prg_proyecto.proyect) AS proyecto, 
			prg_proyecto.city, prg_proyecto.country,
			prg_proyecto.telephone, prg_proyecto.mobile, prg_proyecto.fax AS contacto, prg_proyecto.email,prg_proyecto.ruc,
            COUNT(prg_calendario.id) AS eventos,
            GROUP_CONCAT(DATE_FORMAT(prg_calendario.inicio_evento,'%d.%m.%Y') SEPARATOR '/ ' ) AS fechas,
            SUM(TO_DAYS(prg_calendario.fin_evento) - TO_DAYS(prg_calendario.inicio_evento)+1) AS dias
        FROM prg_proyecto INNER JOIN prg_calendario ON prg_proyecto.project_id = prg_calendario.id_proyecto
        LEFT JOIN prg_proyecto_detalle ON prg_proyecto.id_proyecto=prg_proyecto_detalle.id_proyecto 
			AND	prg_proyecto_detalle.flag='1'
        WHERE prg_proyecto.id_pais='$id_pais' AND prg_proyecto.flag='1' 
		AND prg_proyecto_detalle.coddetalle IS NULL ";
 
        if($fechai!='') $sql.=" and to_days(inicio_evento)>= to_days('$fechai')";
        if($fechaf!='') $sql.=" and to_days(inicio_evento)<= to_days('$fechaf')";
	
		$sql.=" GROUP BY prg_proyecto.project_id
                ORDER BY 2  ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	// reporte de actividades
	//*************************************************
	public function selec_act_index($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
			
		$this->listas=[];
		
		$sql="SELECT distinct 
				ifnull(ref_auditor,'') AS auditor,
                ifnull(tmp_actividad,'') tmp_actividad,
				ifnull(tmp_subprograma,'') tmp_subprograma,
                fecha,
				ifnull(ref_actividad,tmp_actividad) as actividad , 
                ifnull(ref_programa,'') AS subprograma,
                DATE_FORMAT(fecha,'%d-%m-%Y') AS fecha_f, 
				porcentaje, 
				IFNULL(nota,'') AS nota,
                IFNULL(project_id,'') AS project_id,
				IFNULL(ref_proyecto,'') AS proyecto,
				ifnull(id,0) as id,
				round(porcentaje/100,2) diasreales,
				case flgfinalizo when 's' then 'Si' when 'n' then 'No' else '' end as dscflgfinalizo,
				ifnull((select general from prg_programa where id_programa=a.id_programa),'') as general
            FROM 	
                prg_auditoractividad a  ";
        $sql.="  
			WHERE a.flag='1' $searchQuery
			order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
		
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_act_index_lig($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
			
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT distinct 
				a.id_auditactiv, 
				GROUP_CONCAT(DISTINCT prg_roles.nombre) AS rol, 
				IFNULL(GROUP_CONCAT(DISTINCT categoria),'') AS categoria,
				ifnull(g.grupo,'') as grupoprograma,
				ifnull(ref_auditor,'') AS auditor,
				ifnull(comentario,'') AS comentario,
                ifnull(tmp_actividad,'') tmp_actividad,
				ifnull(tmp_subprograma,'') tmp_subprograma,
                fecha,
				ifnull(ref_actividad,tmp_actividad) as actividad , 
                ifnull(ref_programa,'') AS subprograma,
                DATE_FORMAT(fecha,'%d-%m-%Y') AS fecha_f, 
				porcentaje, 
				IFNULL(nota,'') AS nota,
                IFNULL(a.project_id,'') AS project_id,
				IFNULL(ref_proyecto,'') AS proyecto,
				IFNULL(dni,'') AS dni,
				IFNULL(pasaporte,'') AS pasaporte,
				ifnull(id,0) as id,
				round(porcentaje/100,2) as diasreales,
				case flgfinalizo when 's' then 'Si' when 'n' then 'No' else '' end as dscflgfinalizo,
				prg_programa.descripcion as programa
            FROM 	
                prg_auditoractividad a 
				inner join prg_auditor u on a.id_auditor=u.id_auditor
				LEFT JOIN prg_auditor_programa p  ON u.id_auditor=p.id_auditor 
				LEFT JOIN prg_roles ON p.id_rol=prg_roles.id_rol
				LEFT JOIN prg_programa ON a.id_programa=prg_programa.id_programa
				LEFT JOIN prg_cat_programa ON prg_programa.id_categoria=prg_cat_programa.id_categoria
				left join prg_programa_grupo g on prg_programa.id_grupoprograma=g.id_grupoprograma ";
        $sql.="  
			WHERE a.flag='1' AND u.flgstatus=1  $searchQuery
			GROUP BY a.id_auditactiv
			HAVING auditor != ''
			order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_total_act_index($searchQuery){
		
		$sql="SELECT COUNT(*) AS total
			FROM prg_auditoractividad a  inner join prg_auditor u on a.id_auditor=u.id_auditor
			WHERE  a.flag='1' $searchQuery " ;
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	
	public function select_actividad_select($id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="select id_actividad, actividad from prg_actividad where flag='1' and id_pais='$id_pais' order by 2";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function select_roles_select($id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT id_rol, nombre FROM prg_roles WHERE flag='1' ORDER BY 2";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	public function select_repActPlanilla($fechai,$id_pais,$totalpormes,$id_auditor){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT auditor,documento,mes,ccosto,project_id,proyecto, SUM(porcentaje) AS porcentaje FROM (
        SELECT 
                ref_auditor AS auditor,IFNULL(dni,'xxxx') AS documento,
                date_format('$fechai','%m/%y') AS mes,
                IFNULL(p.codigo,'zzzz') AS ccosto,
                100- ROUND(SUM(porcentaje*100/$totalpormes),2) AS porcentaje, 
                '' AS project_id,
                'xxxx' AS proyecto
        FROM  prg_auditoractividad INNER JOIN
           prg_auditor ON prg_auditoractividad.id_auditor=prg_auditor.id_auditor 
             AND TRIM(LPAD(project_id ,4,'0'))!='0000' LEFT JOIN   
           prg_programa ON prg_auditoractividad.id_programa=prg_programa.id_programa LEFT JOIN
           prg_programa p ON prg_auditor.def_programa=p.id_programa
         WHERE prg_auditoractividad.flag='1' AND porcentaje>0 AND ref_pais='$id_pais' 
                AND month(fecha) = month('$fechai')   
                AND year(fecha) = year('$fechai')
                AND WEEKDAY(fecha) <5    ";
		if(!empty($id_auditor))$sql.=" and prg_auditoractividad.id_auditor=$id_auditor";		
        $sql.=" GROUP BY 1,4,6

         UNION

        SELECT 
                ref_auditor AS auditor,
				IFNULL(dni,'xxxx') AS documento,
                date_format('$fechai','%m/%y') AS mes,
                IF(IFNULL(prg_programa.codigo,'')!='',IFNULL(prg_programa.codigo,''),IFNULL(p.codigo,'')) AS ccosto,
                ROUND(SUM(porcentaje*100/$totalpormes),2) AS porcentaje, 
                IFNULL(prg_proyecto.project_id,'') AS project_id,
                IFNULL(prg_proyecto.proyect,'xxxx') AS proyecto
        FROM  prg_auditoractividad INNER JOIN
              prg_auditor ON prg_auditoractividad.id_auditor=prg_auditor.id_auditor 
                AND TRIM(LPAD(project_id ,4,'0'))!='0000' LEFT JOIN   
              prg_programa ON prg_auditoractividad.id_programa=prg_programa.id_programa LEFT JOIN
              prg_programa p ON prg_auditor.def_programa=p.id_programa
			  LEFT JOIN prg_proyecto ON prg_proyecto.project_id=prg_auditoractividad.project_id
         WHERE prg_auditoractividad.flag='1' AND porcentaje>0 AND ref_pais='$id_pais'
                AND month(fecha) = month('$fechai')   
                AND year(fecha) = year('$fechai')
                AND WEEKDAY(fecha) <5    ";
		if(!empty($id_auditor))$sql.=" and prg_auditoractividad.id_auditor=$id_auditor";				
		$sql.=" GROUP BY 1,4,6
         ) AS vista
		GROUP BY 1,4,6 
		  ORDER BY  1  ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function select_repActocupaAuditor($proyecto,$id_pais,$fechai,$fechaf,$id_auditor){
		unset($this->listas);
		$this->listas=[];
		
		 $sql="SELECT 
                if(SUM(porcentaje)>100,100,SUM(porcentaje)) as porcentaje, 
                id_auditor, DATE_FORMAT(fecha,'%d/%m') AS diad
            FROM prg_auditoractividad inner join 
				prg_actividad on prg_auditoractividad.id_actividad=prg_actividad.id_actividad
            WHERE prg_auditoractividad.flag='1' AND IFNULL(id_auditor,0)>0  ";
        
          if($proyecto!='') 
			$sql.= " and (proyect like '%$proyecto%' or  prg_auditoractividad.project_id like '%$proyecto%')";
          if($id_auditor!='') $sql.=" and prg_auditoractividad.id_auditor=$id_auditor ";

          if($fechai!='') $sql.= " and to_days(fecha) >= to_days('$fechai')";
          if($fechaf!='') $sql.= " and to_days(fecha) <= to_days('$fechaf')";

          $sql.="  GROUP BY id_auditor, diad";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function select_auditorlanilla($id_pais,$id_auditor){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT prg_auditor.id_auditor,
                concat_ws(' ',prg_auditor.nombre,apepaterno,apematerno) as auditor
            FROM   prg_auditor 
            WHERE  flag='1' and id_pais='$id_pais'
					and prg_auditor.flgstatus=1
			";
	
		if($id_auditor!='') $sql.=" and id_auditor=$id_auditor ";

		$sql.=" ORDER BY 2 ";

	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function select_proyecDinRegistro($id_pais,$proyecto,$id_auditor,$fechai,$fechaf){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT distinct
                ifnull(ref_auditor,'') as auditor,
                tmp_actividad,
				tmp_subprograma,
                fecha, 
				ref_actividad as actividad , 
                ref_programa AS subprograma,
                DATE_FORMAT(fecha,'%d-%m-%Y') AS fecha_f, 
                porcentaje, 
                IFNULL(nota,'') AS nota,
                IFNULL(project_id,'') AS project_id, IFNULL(ref_proyecto,'') AS proyecto,ifnull(id,0) as id
            FROM 	
                prg_auditoractividad INNER JOIN
                prg_actividad ON prg_auditoractividad.id_actividad=prg_actividad.id_actividad  
            WHERE 
                prg_auditoractividad.flag='1' and porcentaje>0 
                and  IFNULL(prg_auditoractividad.project_id,'')='' 
				and  ifnull(prg_actividad.flgproyecto,'0')='1'
                and ref_pais='$id_pais'";
            

		if($proyecto!='') 
			$sql.= " and (ref_proyecto like '%$proyecto%' or project_id like '%$proyecto%' )";
		if($id_auditor!='') $sql.=" and id_auditor=$id_auditor ";
		if($fechai!='') $sql.= " and to_days(fecha) >= to_days('$fechai')";
		if($fechaf!='') $sql.= " and to_days(fecha) <= to_days('$fechaf')";
		

		$sql.="	ORDER BY fecha ";

	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	// graficos de actividad
	public function select_porcetAuditor($id_pais,$proyecto,$id_auditor,$fechai,$fechaf,$id_actividad,$id_rol){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT  ifnull(sum(porcentaje),0) as porcentaje
                FROM 
                    prg_auditoractividad 
				WHERE flag='1' and ifnull(project_id,'')!='' and ref_pais='$id_pais'";
            
		if($proyecto!='') $sql.= " and (ref_proyecto like '%$proyecto%' or project_id like '%$proyecto%')";
		if($id_auditor!='') $sql.=" and prg_auditoractividad.id_auditor=$id_auditor ";
		if($id_actividad!='') $sql.=" and id_actividad=$id_actividad ";
		if($id_rol!='') $sql.=" and ref_rol in ($id_rol) ";	
		if($fechai!='') $sql.= " and to_days(fecha) >= to_days('$fechai')";
		if($fechaf!='') $sql.= " and to_days(fecha) <= to_days('$fechaf')";

	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	public function select_datActiAuditor($id_pais,$proyecto,$id_auditor,$fechai,$fechaf,$id_actividad,$id_rol,$total){
		unset($this->listas);
		$this->listas=[];
		
		 $sql="SELECT 
               substr(concat_ws('-',project_id,ref_proyecto),1,30) actividad,
               round(sum(porcentaje*100/$total),1) as porcentaje,
              round(sum( porcentaje/100 ),1) AS dias    
        FROM 	
             prg_auditoractividad
		WHERE flag='1' and ifnull(project_id,'')!='' and ref_pais='$id_pais' ";
            
	if($proyecto!='') $sql.= " and (ref_proyecto like '%$proyecto%' or project_id like '%$proyecto%')";
	if($id_auditor!='') $sql.=" and id_auditor=$id_auditor ";
	if($id_actividad!='') $sql.=" and id_actividad=$id_actividad ";
    if($id_rol!='') $sql.=" and id_rol in ($id_rol) ";	
 	if($fechai!='') $sql.= " and to_days(fecha) >= to_days('$fechai')";
	if($fechaf!='') $sql.= " and to_days(fecha) <= to_days('$fechaf')";
        
        $sql.=" group by 1 order by 2 desc limit 0,40";

	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function select_datActiActividad($id_pais,$proyecto,$id_auditor,$fechai,$fechaf,$id_actividad,$id_rol,$total){
		unset($this->listas);
		$this->listas=[];
		
		 $sql="SELECT 
               substring(prg_actividad.actividad,1,35) as actividad,
               round(sum(au.porcentaje*100/$total),1) as porcentaje,
               round(sum( au.porcentaje/100 ),1) AS dias  
        FROM   prg_auditoractividad au inner join prg_actividad ON au.id_actividad=prg_actividad.id_actividad
        WHERE au.flag='1' and au.ref_pais='$id_pais' ";
            
		if($proyecto!='') $sql.= " and (au.ref_proyecto like '%$proyecto%' or au.project_id like '%$proyecto%')";
		if($id_auditor!='') $sql.=" and au.id_auditor=$id_auditor ";
		if($id_actividad!='') $sql.=" and au.id_actividad=$id_actividad ";
		if($id_rol!='') $sql.=" and au.id_rol in ($id_rol) ";	
		if($fechai!='') $sql.= " and to_days(au.fecha) >= to_days('$fechai')";
		if($fechaf!='') $sql.= " and to_days(au.fecha) <= to_days('$fechaf')";
			
        $sql.=" group by 1 order by 2 desc limit 0,40";

	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function select_datActiProyecto($id_pais,$proyecto,$id_auditor,$fechai,$fechaf,$id_actividad,$id_rol,$total){
		unset($this->listas);
		$this->listas=[];
		
		 $sql="SELECT 
               substr(ref_auditor,1,30) actividad,
               round(sum(porcentaje*100/$total),1) as porcentaje,
               round(sum( porcentaje/100 ),1) AS dias  
        FROM 	
             prg_auditoractividad
		WHERE flag='1' and ifnull(project_id,'')!='' and ref_pais='$id_pais' ";
				
		if($proyecto!='') $sql.= " and (ref_proyecto like '%$proyecto%' or project_id like '%$proyecto%')";
		if($id_auditor!='') $sql.=" and id_auditor=$id_auditor ";
		if($id_actividad!='') $sql.=" and id_actividad=$id_actividad ";
		if($id_rol!='') $sql.=" and id_rol in ($id_rol) ";	
		if($fechai!='') $sql.= " and to_days(fecha) >= to_days('$fechai')";
		if($fechaf!='') $sql.= " and to_days(fecha) <= to_days('$fechaf')";
			
        $sql.=" group by 1 order by 2 desc limit 0,40";

	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_res_calendarioXls($columnName,$columnSortOrder,$searchQuery,$sess_codpais){
		unset($this->listas);
		$this->listas=[];
		
		$sql = "
			SELECT
			prg_tipoactividad.descripcion as tipo_actividad,
			concat(prg_auditor.nombre,' ',prg_auditor.apepaterno,' ',prg_auditor.apematerno) as auditor,
			prg_calendario.asunto,
			prg_calendario.id_proyecto as cu_proyecto,
			prg_proyecto.proyect as nombre_proyecto,
			IFNULL(prg_calendario.utizado_monto_soles,0) as monto_soles,
			IFNULL(round(prg_calendario.utizado_monto_soles/3.85,2),0) as monto_soles_dolares,
			IFNULL(prg_calendario.utizado_monto_dolares,0) as monto_dolares,
			rend_fecha_rendido as fecha_rendicion

			FROM prg_calendario
			INNER JOIN prg_tipoactividad ON prg_calendario.id_tipoactividad = prg_tipoactividad.id_tipoactividad
			INNER JOIN prg_auditor ON prg_calendario.id_auditor = prg_auditor.id_auditor	
			LEFT JOIN prg_proyecto on (prg_proyecto.id_proyecto = prg_proyecto.project_id and prg_proyecto.id_pais='$sess_codpais')
			WHERE prg_calendario.flag='1'AND prg_calendario.mes_inicio>0  
				and (utizado_monto_soles>0 or utizado_monto_dolares>0)
			$searchQuery";

		$sql.=" order by cu_proyecto  ";

		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function selec_res_FeesXls($columnName,$columnSortOrder,$searchQuery,$sess_codpais){
		unset($this->listas);
		$this->listas=[];
		
		$sql = "
			SELECT  
				prg_proyecto.project_id, prg_proyecto.proyect,c.moneda,c.programa,anio,mes,
				IFNULL((CASE  WHEN c.moneda='EUR' THEN montofeecert*1.41 WHEN c.moneda<>'US$' THEN montofeecert/tipocambio ELSE montofeecert END),0)  as montofeecert,
				IFNULL((CASE  WHEN c.moneda='EUR' THEN montofee*1.41 WHEN c.moneda<>'US$' THEN montofee/tipocambio ELSE montofee END),0) as montofee,
				IFNULL((CASE  WHEN c.moneda='EUR' THEN pm*1.41 WHEN c.moneda<>'US$' THEN pm/tipocambio ELSE pm END),0)  as pm,
				
				a.nombres AS ejecutivo,prg_estadoproyecto.descripcion AS estado,
				IFNULL((CASE  WHEN c.moneda='EUR' THEN montofeecert*1.41 WHEN c.moneda<>'US$' THEN montofeecert/tipocambio ELSE montofeecert END),0) 
				+ 
				IFNULL((CASE  WHEN c.moneda='EUR' THEN montofee*1.41 WHEN c.moneda<>'US$' THEN montofee/tipocambio ELSE montofee END),0) 
				+ 
				IFNULL((CASE  WHEN c.moneda='EUR' THEN pm*1.41 WHEN c.moneda<>'US$' THEN pm/tipocambio ELSE pm END),0) 
				AS subtotal

			FROM 
				prg_proyecto INNER JOIN
				prg_proyecto_detalle  d ON prg_proyecto.id_proyecto= d.id_proyecto INNER JOIN
				 prg_programacosto c ON c.coddetalle=d.coddetalle INNER JOIN
				 prg_usuarios a ON d.codejecutivo= a.id_usuario INNER JOIN
				 prg_estadoproyecto ON d.codestado=prg_estadoproyecto.codestado
			WHERE c.flag='1'  $searchQuery
			HAVING subtotal>0
			order by 1  ";

		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}

	public function selec_total_reporte_seguimiento_renovacion($searchQuery){
		
		$sql="
			select
			count(1) AS total
			
			from prg_proyecto
			inner join prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto
			inner join prg_programacosto on prg_programacosto.coddetalle=prg_proyecto_detalle.coddetalle
			
			where prg_proyecto.flag=1 and prg_programacosto.flag=1 and prg_proyecto_detalle.anioo >= 2022 $searchQuery
			
			" ;
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}

	public function selec_reporte_seguimiento_renovacion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		
		$sql="
				select
				prg_proyecto.project_id as cu_proyecto,
				prg_proyecto.proyect as nombre_proyecto,
				prg_proyecto.city as ciudad_proyecto,
				prg_proyecto.country as pais_proyecto,
				prg_proyecto.proyect as nombre_proyecto_,
				prg_programacosto.programa as nombre_programa,
				prg_programacosto.programa as nombre_sub_programa,
				CONCAT (prg_proyecto_detalle.mes, '-', prg_proyecto_detalle.anio) as fecha_actual_renovacion,
				CONCAT (prg_proyecto_detalle.meso, '-', prg_proyecto_detalle.anioo) as fecha_original_renovacion,
				prg_proyecto_detalle.moneda as moneda_proyecto,
				prg_programacosto.montoservicio as monto_servicio_proyecto,
				prg_usuarios.nombres as nombres_ejecutivo_comercial,
				CASE prg_programacosto.estado_renovacion
					WHEN 0 THEN 'Venta nueva'
					WHEN 1 THEN 'Pendiete a renovar'
					WHEN 2 THEN 'Renovado'
					WHEN 3 THEN 'Baja'
					ELSE '---'
				end estado_renovacion
				
				from prg_proyecto
				inner join prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto
				inner join prg_programacosto on prg_programacosto.coddetalle=prg_proyecto_detalle.coddetalle
				left join prg_usuarios ON prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario
				where prg_proyecto.flag=1 and prg_programacosto.flag=1 and prg_proyecto_detalle.anioo >= 2022 $searchQuery ";

		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ."";
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	public function selec_res_xaniomes($id_pais,$proyecto,$tcEuUS,$tipo,$codestado,$anio,$codejecutivo){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT  prg_proyecto_detalle.mes, prg_proyecto_detalle.anio,
				SUM(round((CASE  WHEN moneda='EUR' THEN montototal*$tcEuUS WHEN moneda<>'US$' THEN montototal/tipocambio ELSE montototal END),2)) AS costo,
				COUNT(prg_proyecto_detalle.coddetalle) AS numero,
				
				IFNULL(vista.notacredito,0) AS notacredito
				FROM prg_proyecto INNER JOIN
					prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
					prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado INNER JOIN
					prg_estadoproyecto_grupo ON prg_estadoproyecto.id_grupo=prg_estadoproyecto_grupo.id_grupo
						and prg_estadoproyecto.id_pais=prg_estadoproyecto_grupo.id_pais LEFT JOIN 
					(SELECT 
						SUM(abs(ifnull(CASE WHEN d.moneda='EUR' THEN c.notacredito*$tcEuUS WHEN d.moneda<>'US$' THEN c.notacredito/tipocambio ELSE c.notacredito END,0))) AS notacredito,
					
						anio AS anio_c, mes AS mes_c
						FROM prg_programacosto c INNER JOIN prg_proyecto_detalle d ON d.coddetalle=c.coddetalle
							INNER JOIN prg_proyecto p ON p.id_proyecto=d.id_proyecto AND id_pais='$id_pais'
						WHERE c.flag='1' AND d.flag='1'
						GROUP BY d.mes, d.anio
						
					) AS vista ON prg_proyecto_detalle.mes=vista.mes_c AND prg_proyecto_detalle.anio=vista.anio_c
				WHERE  prg_proyecto.flag = '1' 
				  AND prg_proyecto_detalle.flag='1' 
				  AND ifnull(prg_proyecto_detalle.isanulado,'0')='0' 
				 AND prg_proyecto.id_pais= '$id_pais'  ";
		 
		if($tipo!='') $sql.=" and tipo in ($tipo)";	
		if($anio!='') $sql.=" and anio=$anio";		 
		if($proyecto!='') 
			$sql.= " and (prg_proyecto.proyect like '%$proyecto%' or prg_proyecto.project_id like '%$proyecto%' )";
		if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado='$codestado' ";		
		if($codejecutivo!='') $sql.=" and codejecutivo='$codejecutivo' ";		
		
		$sql.=" GROUP BY mes,anio 
				ORDER BY mes asc  ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	// no intercompany
	public function selec_res_xlsnointercompany($id_pais,$anio,$mes,$tcEuUS,$codestado,$codejecutivo,$tipo,$id_proyecto,$nofiltro){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT 
			prg_proyecto_detalle.coddetalle, prg_proyecto.project_id, proyect, city, 
			country, anio,mes,fax,
			IFNULL(dsc_programaren,'') AS programas,
			prg_proyecto_detalle.moneda,
			IFNULL(prg_usuarios.nombres,'') AS comercial,
			IFNULL(prg_estadoproyecto.descripcion,'') AS estado	,
			ROUND(CASE  WHEN moneda='EUR' THEN montototal*$tcEuUS WHEN moneda<>'US$' THEN montototal/tipocambio ELSE montototal END,2) AS servicio,
			ROUND(CASE  WHEN moneda='EUR' THEN notacredito*$tcEuUS WHEN moneda<>'US$' THEN notacredito/tipocambio ELSE notacredito END,2) AS notacredito,
			ROUND(CASE  WHEN moneda='EUR' THEN montoventa*$tcEuUS WHEN moneda<>'US$' THEN montoventa/tipocambio ELSE montoventa END,2) AS montoventa
		FROM prg_proyecto INNER JOIN
			 prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto left join
			 prg_usuarios on prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario left join 
			 	prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado INNER JOIN
				prg_estadoproyecto_grupo ON prg_estadoproyecto.id_grupo=prg_estadoproyecto_grupo.id_grupo and
					prg_estadoproyecto.id_pais=prg_estadoproyecto_grupo.id_pais LEFT JOIN 
					(SELECT 
						SUM(IFNULL(ABS(c.notacredito),0)) AS notacredito, 
						coddetalle as codet,
						IFNULL(SUM(IFNULL(montoservicio,0) + IFNULL(montofee,0)),0) AS montoventa_red,
						IFNULL(SUM(
							IFNULL(montoservicio,0) + IFNULL(montofee,0) + IFNULL(montoviatico,0) + IFNULL(cartacor,0)+ IFNULL(analisis,0) + IFNULL(intercompany,0) + IFNULL(cursos,0) - ABS(IFNULL(notacredito,0))
						),0) AS montoventa
						FROM prg_programacosto c INNER JOIN prg_proyecto d ON d.id_proyecto=c.project_id
						WHERE c.flag='1'  AND d.flag='1' AND id_pais='$id_pais'
						GROUP BY 2 ";
		if(empty($nofiltro))
			$sql.="	HAVING notacredito<>0";
		$sql.="			) AS vista ON prg_proyecto_detalle.coddetalle=vista.codet
		WHERE  prg_proyecto.flag = '1' 
			AND IFNULL(prg_proyecto_detalle.isanulado,'0')='0' 
			AND prg_proyecto_detalle.flag='1'
			AND prg_proyecto.id_pais= '$id_pais' ";

		if($tipo!='') $sql.=" AND prg_estadoproyecto_grupo.tipo in ($tipo)";
		if($id_proyecto!='0' and $id_proyecto!='') $sql.=" AND prg_proyecto.id_proyecto='$id_proyecto'";
		if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";	
		if($codejecutivo!='') $sql.=" and prg_proyecto_detalle.codejecutivo=$codejecutivo ";	
		if($anio!='0' and $anio!='') $sql.=" and anio= $anio ";
		if($mes!='0' and $mes!='') $sql.=" and mes= $mes ";
		$sql.="ORDER BY 3";
			
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	// no intercompany
	public function selec_res_xlsnointercompany2($id_pais,$anio,$mes,$tcEuUS,$codestado,$codejecutivo,$tipo,$id_proyecto,$nofiltro){
		unset($this->listas);
		$this->listas=[];
		
		$sql="SELECT 
			prg_proyecto_detalle.coddetalle, prg_proyecto.project_id, proyect, city, 
			country, anio,mes,fax,
			IFNULL(dsc_programaren,'') AS programas,
			prg_proyecto_detalle.moneda,
			IFNULL(prg_usuarios.nombres,'') AS comercial,
			IFNULL(prg_estadoproyecto.descripcion,'') AS estado	,
			ROUND(CASE  WHEN moneda='EUR' THEN montototal*$tcEuUS WHEN moneda<>'US$' THEN montototal/tipocambio ELSE montototal END,2) AS servicio,
			ROUND(CASE  WHEN moneda='EUR' THEN notacredito*$tcEuUS WHEN moneda<>'US$' THEN notacredito/tipocambio ELSE notacredito END,2) AS notacredito,
			ROUND(CASE  WHEN moneda='EUR' THEN montoventa*$tcEuUS WHEN moneda<>'US$' THEN montoventa/tipocambio ELSE montoventa END,2) AS montoventa
		FROM prg_proyecto INNER JOIN
			 prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto left join
			 prg_usuarios on prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario left join 
			 	prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado INNER JOIN
				prg_estadoproyecto_grupo ON prg_estadoproyecto.id_grupo=prg_estadoproyecto_grupo.id_grupo and
					prg_estadoproyecto.id_pais=prg_estadoproyecto_grupo.id_pais LEFT JOIN 
					(SELECT 
						SUM(IFNULL(ABS(c.notacredito),0)) AS notacredito, 
						coddetalle as codet,
						IFNULL(SUM(IFNULL(montoservicio,0) + IFNULL(montofee,0)),0) AS montoventa
						FROM prg_programacosto c INNER JOIN prg_proyecto d ON d.id_proyecto=c.project_id
						WHERE c.flag='1'  AND d.flag='1' AND id_pais='$id_pais'
						GROUP BY 2 ";
		if(empty($nofiltro))
			$sql.="	HAVING notacredito<>0";
		$sql.="			) AS vista ON prg_proyecto_detalle.coddetalle=vista.codet
		WHERE  prg_proyecto.flag = '1' 
			AND IFNULL(prg_proyecto_detalle.isanulado,'0')='0' 
			AND prg_proyecto_detalle.flag='1'
			AND prg_proyecto.id_pais= '$id_pais' ";

		if($tipo!='') $sql.=" AND prg_estadoproyecto_grupo.tipo in ($tipo)";
		if($id_proyecto!='0' and $id_proyecto!='') $sql.=" AND prg_proyecto.id_proyecto='$id_proyecto'";
		if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";	
		if($codejecutivo!='') $sql.=" and prg_proyecto_detalle.codejecutivo=$codejecutivo ";	
		if($anio!='0' and $anio!='') $sql.=" and anio= $anio ";
		if($mes!='0' and $mes!='') $sql.=" and mes= $mes ";
		$sql.="ORDER BY 3";
			
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}

	public function selec_res_proyxgrupoxanio_total($tcEuUS,$id_pais,$anio){
		unset($this->listas);
		$this->listas=[];
		$sql = "

		SELECT
			vista_ampliacion.mes,
			vista_ampliacion.anio,
			vista_ampliacion.mesanio,
			vista_ampliacion.total_ampliacion,
			vista_cartacor.total_cartacor,
			vista_curso.total_curso,
			vista.servicio,
			vista.tcursos,
			vista.tnotacredito,
			(
				(
					IFNULL(vista.servicio,0)+
					IFNULL(vista_ampliacion.total_ampliacion,0)+
					IFNULL(vista_cartacor.total_cartacor,0)+
					IFNULL(vista_curso.total_curso,0)

				) - IFNULL(vista.tnotacredito,0)
			) as total
			FROM
			(SELECT
				mes,
     			anio,
				concat_ws('.', mes, anio) as mesanio,
				ABS(
					SUM(
					IFNULL(
						CASE WHEN prg_proyecto_detalle.moneda = 'EUR' THEN ampliacion * $tcEuUS WHEN prg_proyecto_detalle.moneda <> 'US$' THEN ampliacion / tipocambio ELSE ampliacion END,
						0
					)
					)
				) AS total_ampliacion /*Se excluye todos menos renovación*/
				FROM
				prg_proyecto
				INNER JOIN prg_proyecto_detalle ON prg_proyecto.id_proyecto = prg_proyecto_detalle.id_proyecto
				INNER JOIN prg_programacosto ON prg_proyecto_detalle.coddetalle = prg_programacosto.coddetalle
				inner join prg_estadoproyecto on prg_proyecto_detalle.codestado = prg_estadoproyecto.codestado
				WHERE
				prg_programacosto.flag = '1'
				and prg_proyecto.id_pais = '$id_pais'
				and anio in ('$anio')


			GROUP BY
			mes
			) AS vista_ampliacion
			LEFT JOIN
			(SELECT
				concat_ws('.', mes, anio) as mesanio,
				ABS(
					SUM(
					IFNULL(
						CASE WHEN prg_proyecto_detalle.moneda = 'EUR' THEN cartacor * $tcEuUS WHEN prg_proyecto_detalle.moneda <> 'US$' THEN cartacor / tipocambio ELSE cartacor END,
						0
					)
					)
				) AS total_cartacor  /*Se excluye Solicitud Interna, Bajas, Intercompany*/

				FROM
				prg_proyecto
				INNER JOIN prg_proyecto_detalle ON prg_proyecto.id_proyecto = prg_proyecto_detalle.id_proyecto
				INNER JOIN prg_programacosto ON prg_proyecto_detalle.coddetalle = prg_programacosto.coddetalle
				inner join prg_estadoproyecto on prg_proyecto_detalle.codestado = prg_estadoproyecto.codestado
				WHERE
				prg_programacosto.flag = '1'
				and prg_proyecto.id_pais = '$id_pais'
				and anio in ('$anio')
				and id_grupo not in (2,9)

			GROUP BY
			mes
			) AS vista_cartacor ON vista_cartacor.mesanio=vista_ampliacion.mesanio
			LEFT JOIN
			(SELECT
				concat_ws('.', mes, anio) as mesanio,
				ABS(
					SUM(
					IFNULL(
						CASE WHEN prg_proyecto_detalle.moneda = 'EUR' THEN cursos * $tcEuUS WHEN prg_proyecto_detalle.moneda <> 'US$' THEN cursos / tipocambio ELSE cursos END,
						0
					)
					)
				) AS total_curso  /*Se excluye Solicitud Interna, Bajas, Intercompany*/

				FROM
				prg_proyecto
				INNER JOIN prg_proyecto_detalle ON prg_proyecto.id_proyecto = prg_proyecto_detalle.id_proyecto
				INNER JOIN prg_programacosto ON prg_proyecto_detalle.coddetalle = prg_programacosto.coddetalle
				inner join prg_estadoproyecto on prg_proyecto_detalle.codestado = prg_estadoproyecto.codestado
				WHERE
				prg_programacosto.flag = '1'
				and prg_proyecto.id_pais = '$id_pais'
				and anio in ('$anio')
				and id_grupo not in (7,2,9)

			GROUP BY
			mes
			) AS vista_curso ON vista_curso.mesanio=vista_ampliacion.mesanio
			LEFT JOIN
			(SELECT
			concat_ws('.', mes, anio) as mesanio,
			ROUND(
				SUM(
				abs(
					CASE WHEN moneda = 'EUR' THEN montototal * $tcEuUS WHEN moneda <> 'US$' THEN montototal / tipocambio ELSE montototal END
				)
				),
				2
			) AS costo,
			SUM(
				IFNULL(
				abs(
					CASE WHEN moneda = 'EUR' THEN notacredito * $tcEuUS WHEN moneda <> 'US$' THEN notacredito / tipocambio ELSE notacredito END
				),
				0
				)
			) AS tnotacredito,
			SUM(
				IFNULL(
				CASE WHEN moneda = 'EUR' THEN montocursos * $tcEuUS WHEN moneda <> 'US$' THEN montocursos / tipocambio ELSE montocursos END,
				0
				)
			) AS tcursos,
			abs(
				SUM(
				IFNULL(
					CASE WHEN moneda = 'EUR' THEN montoservicio * $tcEuUS WHEN moneda <> 'US$' THEN montoservicio / tipocambio ELSE montoservicio END,
					0
				)
				)
			) AS servicio

			FROM
			prg_proyecto
			INNER JOIN prg_proyecto_detalle ON prg_proyecto.id_proyecto = prg_proyecto_detalle.id_proyecto
			inner join prg_estadoproyecto on prg_proyecto_detalle.codestado = prg_estadoproyecto.codestado
			LEFT JOIN (
				SELECT
				IFNULL(
					sum(montofeecert),
					0
				) AS montofeecert,
				IFNULL(
					sum(
					abs(notacredito)
					),
					0
				) AS notacredito,
				IFNULL(
					sum(montoservicio),
					0
				) AS montoservicio,
				IFNULL(
					sum(ampliacion),
					0
				) AS ampliacion,
				IFNULL(
					sum(montocourier),
					0
				) AS courier,
				IFNULL(
					sum(cartacor),
					0
				) AS cartacor,
				IFNULL(
					SUM(montoviatico),
					0
				) AS montoviatico,
				IFNULL(
					sum(
					IFNULL(montoservicio, 0) + IFNULL(montofee, 0) + IFNULL(montofeecert, 0) + IFNULL(montocourier, 0) + IFNULL(montoviatico, 0) + IFNULL(cartacor, 0)+ IFNULL(analisis, 0) + IFNULL(cursos, 0) - ABS(
						ifnull(notacredito, 0)
					)
					),
					0
				) AS montoventa,
				IFNULL(
					sum(
					IFNULL(montoservicio, 0) - abs(
						IFNULL(ampliacion, 0)
					) - ABS(
						ifnull(notacredito, 0)
					)
					),
					0
				) AS moampliacion,
				IFNULL(
					sum(intercompany),
					0
				) AS intercompany,
				IFNULL(
					sum(cursos),
					0
				) AS montocursos,
				IFNULL(
					sum(auditoria_no_anunciada),
					0
				) AS auditoria_no_anunciada,
				IFNULL(
					sum(investigacion),
					0
				) AS investigacion,
				IFNULL(
					sum(otros),
					0
				) AS otros,
				coddetalle
				FROM
				prg_programacosto
				WHERE
				flag = '1'
				GROUP BY
				coddetalle
			) AS vista ON prg_proyecto_detalle.coddetalle = vista.coddetalle
			WHERE
			prg_proyecto.flag = '1'
			AND prg_proyecto_detalle.flag = '1'
			AND ifnull(
				prg_proyecto_detalle.isanulado,
				'0'
			)= '0'
			AND prg_proyecto.id_pais = '$id_pais'
			and id_grupo not in (8)
			and id_grupo in (6,1,14)
			and anio in ('$anio')

			GROUP BY
			mes,
			anio
			ORDER BY
			mes asc
			) AS vista ON vista.mesanio = vista_ampliacion.mesanio


		";

		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}


	public function select_repdiasmesAuditor($id_pais,$fecha,$id_auditor){
		unset($this->listas);
		$this->listas=[];
		
		 $sql="SELECT 
				SUM( porcentaje) AS porcentaje,
				COUNT(DISTINCT fecha) AS dias, 
				id_auditor, MONTH(fecha) AS mes
				FROM prg_auditoractividad 
				WHERE flag='1' AND IFNULL(id_auditor,0)>0 
				AND YEAR(fecha) = year('$fecha') AND WEEKDAY(fecha) <=4";
        
          if($id_auditor!='') 
			  $sql.=" and id_auditor=$id_auditor ";
          $sql.="  GROUP BY id_auditor, mes";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function select_repdiasmesanio($fecha){
		unset($this->listas);
		$this->listas=[];
		
		 $sql="SELECT mes,diaslab FROM prg_aniomesdia WHERE anio=year('$fecha')";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_anio_cartayamplia($tcEuUS,$codpais){
		$this->listas=[];
		$sql="SELECT  CONCAT_WS('.',mes,anio) AS mesanio,
				ROUND(ABS(SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN ampliacion*$tcEuUS  WHEN prg_proyecto_detalle.moneda<>'US$' 
								THEN ampliacion/tipocambio ELSE ampliacion END,0))),0) AS ampliacion,
				ROUND(ABS(SUM(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN cartacor*$tcEuUS  WHEN prg_proyecto_detalle.moneda<>'US$' 
								THEN cartacor/tipocambio ELSE cartacor END,0))),0) AS cartacor

				FROM prg_proyecto INNER JOIN
					prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
					prg_programacosto ON prg_proyecto_detalle.coddetalle=prg_programacosto.coddetalle INNER JOIN
					prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
				WHERE prg_programacosto.flag='1'  AND prg_proyecto.id_pais= '$codpais' 
				GROUP BY mes,anio";
		
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;
	}
	
	
	public function selec_anio_ventas($tcEuUS,$codpais,$proyecto,$codestado,$codejecutivo){
		$this->listas=[];
		$sql="SELECT  mes,anio,CONCAT_WS('.',mes,anio) AS mesanio, id_grupo,
					 ROUND(SUM(ABS(CASE  WHEN moneda='EUR' THEN montototal*$tcEuUS WHEN moneda<>'US$' THEN montototal/tipocambio ELSE montototal END)),2) AS costo,
					
					COUNT(prg_proyecto_detalle.coddetalle) AS numero,
					 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montofeecert*$tcEuUS WHEN moneda<>'US$' THEN montofeecert/tipocambio ELSE montofeecert END,0)) AS fee,
					 SUM(IFNULL(ABS(CASE  WHEN moneda='EUR' THEN notacredito*$tcEuUS WHEN moneda<>'US$' THEN notacredito/tipocambio ELSE notacredito END),0)) AS tnotacredito,
					 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montocursos*$tcEuUS WHEN moneda<>'US$' THEN montocursos/tipocambio ELSE montocursos END,0)) AS tcursos,
					 ABS(SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montoservicio*$tcEuUS   WHEN moneda<>'US$' THEN montoservicio/tipocambio   ELSE montoservicio   END,0))) AS servicio,
					 ABS(SUM(IFNULL(CASE  WHEN moneda='EUR' THEN moampliacion*$tcEuUS WHEN moneda<>'US$' THEN moampliacion/tipocambio ELSE moampliacion END,0))) AS mampliacion,
					 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN intercompany*$tcEuUS WHEN moneda<>'US$' THEN intercompany/tipocambio ELSE intercompany END,0)) AS tintercompany,
					 SUM(IFNULL(ABS(CASE  WHEN moneda='EUR' THEN ampliacion*$tcEuUS WHEN moneda<>'US$' THEN ampliacion/tipocambio ELSE ampliacion END),0)) AS tampliacion,
					 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN courier*$tcEuUS WHEN moneda<>'US$' THEN courier/tipocambio ELSE courier END,0)) AS tcourier,
					 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montoventa*$tcEuUS WHEN moneda<>'US$' THEN montoventa/tipocambio ELSE montoventa END,0)) AS tmontoventa,
					 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN cartacor*$tcEuUS WHEN moneda<>'US$' THEN cartacor/tipocambio ELSE cartacor END,0)) AS tcartacor,
					 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN auditoria_no_anunciada*$tcEuUS WHEN moneda<>'US$' THEN auditoria_no_anunciada/tipocambio ELSE auditoria_no_anunciada END,0)) AS tauditoria_no_anunciada,
					 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN investigacion*$tcEuUS WHEN moneda<>'US$' THEN investigacion/tipocambio ELSE investigacion END,0)) AS tinvestigacion,
					 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN otros*$tcEuUS WHEN moneda<>'US$' THEN otros/tipocambio ELSE otros END,0)) AS totros,
					 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN montoviatico*$tcEuUS WHEN moneda<>'US$' THEN montoviatico/tipocambio ELSE montoviatico END,0)) AS tmontoviatico
					FROM prg_proyecto INNER JOIN
						prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
						prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
						
						LEFT JOIN ( SELECT 
										IFNULL(SUM(montofeecert),0) AS montofeecert,
										IFNULL(SUM(ABS(notacredito)),0) AS notacredito,
										IFNULL(SUM(montoservicio),0) AS montoservicio,
										IFNULL(SUM(ampliacion),0) AS ampliacion,
										IFNULL(SUM(montocourier),0) AS courier,
										IFNULL(SUM(cartacor),0) AS cartacor,
										IFNULL(SUM(montoviatico),0) AS montoviatico,
										IFNULL(SUM(
											IFNULL(montoservicio,0) + IFNULL(montofee,0) + IFNULL(montofeecert,0) + IFNULL(montocourier,0) + IFNULL(montoviatico,0) + IFNULL(cartacor,0)+ IFNULL(analisis,0) + IFNULL(cursos,0) - ABS(IFNULL(notacredito,0))
										),0) AS montoventa,
										IFNULL(SUM(
											IFNULL(montoservicio,0) -  ABS(IFNULL(ampliacion,0)) - ABS(IFNULL(notacredito,0))
										),0) AS moampliacion,
										IFNULL(SUM(intercompany),0) AS intercompany,
										IFNULL(SUM(cursos),0) AS montocursos,
										IFNULL(SUM(auditoria_no_anunciada),0) AS auditoria_no_anunciada,
										IFNULL(SUM(investigacion),0) AS investigacion,
										IFNULL(SUM(otros),0) AS otros,
										coddetalle
							FROM prg_programacosto 
							WHERE flag='1' 
							GROUP BY coddetalle
						) AS vista ON prg_proyecto_detalle.coddetalle=vista.coddetalle
				
					WHERE  prg_proyecto.flag = '1' 
					  AND prg_proyecto_detalle.flag='1' 
					   AND IFNULL(prg_proyecto_detalle.isanulado,'0')='0' 
					 AND prg_proyecto.id_pais= '$codpais'  
					 AND id_grupo NOT IN (8)  ";
					
				if($proyecto!='') $sql.= " and (proyect like '%$proyecto%' or prg_proyecto.project_id='$proyecto')";
				if($codestado!='') $sql.=" and prg_proyecto_detalle.codestado=$codestado ";	
				if($codejecutivo!='') $sql.=" and prg_proyecto_detalle.codejecutivo=$codejecutivo ";	
		
				$sql.="	 GROUP BY mes,id_grupo,anio
				 ORDER BY mes ASC  ";
		
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;
	}
	
}	
?>