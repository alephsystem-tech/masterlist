<?php
class home_model{
    private $db;
    private $lista;
    public function __construct(){
        $this->db=new DBManejador();
        $this->lista=array();

    }
    /* MODELO para seleccionar  usuarios
        mayo 2020
		Autor: Enrique Bazalar alephsystem@gmail.com
    */


	public function select_ventasMesAnio($id_pais,$anio,$tcEuUS){
        unset($this->lista);
		
		 $sqlUnion="SELECT  
			 IFNULL(SUM(CASE  WHEN YEAR(fecha_emision)=$anio THEN costo_usd + cos_courier_usd END),0)	AS montoanio,
			 0 as montorestecnico,
			 IFNULL(SUM(CASE  WHEN YEAR(fecha_emision)=$anio-1 THEN costo_usd + cos_courier_usd END),0)	AS montoanio_ante,
			 0 as montorestecnico_ante,
			 'US$' as moneda,
			 MONTH(fecha_emision) AS mes
		FROM tc_datos
		WHERE  flag = '1' and cu not in ('800375','801671','858303','856622','812111','817070','803692','835575','800000','849379','849452')
			 AND id_pais='$id_pais' and  year(fecha_emision) >=$anio-1
	   GROUP BY MONTH(fecha_emision)  
	   union
	   SELECT 
			 IFNULL(SUM(CASE  WHEN YEAR(fechaenvio)=$anio THEN montocliente END),0)	AS montoanio,
			 0 as montorestecnico,
			 IFNULL(SUM(CASE  WHEN YEAR(fechaenvio)=$anio-1 THEN montocliente END),0)	AS montoanio_ante,
			 0 as montorestecnico_ante,
			 'US$' as moneda,
			 MONTH(fechaenvio) AS mes
		FROM lab_resultado
			WHERE flag='1' AND id_pais='$id_pais' AND YEAR(fechaenvio)>=$anio-1	
			GROUP BY MONTH(fechaenvio) ";
	 
		// monto y monto tecnico
		$sql="SELECT 
			IFNULL(SUM(CASE  
					WHEN moneda='EUR' and anio=$anio THEN montototal*$tcEuUS
					WHEN moneda='US$' and anio=$anio THEN montototal 
					WHEN anio=$anio then montototal/tipocambio END),0)
			AS montoanio,
			IFNULL(SUM(CASE  
					WHEN moneda='EUR' and anio=$anio THEN montorestecnico*$tcEuUS
					WHEN moneda='US$' and anio=$anio THEN montorestecnico 
					WHEN anio=$anio then montorestecnico/tipocambio END),0)
			AS montorestecnico,
			IFNULL(SUM(CASE  
					WHEN moneda='EUR' and anio=($anio-1) THEN montototal*$tcEuUS
					WHEN moneda='US$' and anio=($anio-1) THEN montototal 
					WHEN (anio=$anio-1) then montototal/tipocambio END),0)
			AS montoanio_ante,
			IFNULL(SUM(CASE  
					WHEN moneda='EUR' and anio=($anio-1) THEN montorestecnico*$tcEuUS
					WHEN moneda='US$' and anio=($anio-1) THEN montorestecnico 
					WHEN (anio=$anio-1) then montorestecnico/tipocambio END),0)
			AS montorestecnico_ante,
			prg_proyecto_detalle.moneda,
			mes
		FROM prg_proyecto INNER JOIN
		 prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
		 prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado INNER JOIN
		 prg_estadoproyecto_grupo ON prg_estadoproyecto.id_grupo=prg_estadoproyecto_grupo.id_grupo
		WHERE  prg_proyecto.flag = '1'  
			AND prg_proyecto_detalle.flag='1' AND tipo IN (1)
			AND prg_proyecto.id_pais= '$id_pais'  AND (anio=$anio or anio=($anio-1))
		GROUP BY mes";

			
	$sql=" SELECT SUM(montoanio) AS montoanio,SUM(montoanio_ante) AS montoanio_ante, moneda,mes
				FROM ( $sql union $sqlUnion ) as vista 
				GROUP BY mes "; 
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	

    public function select_ventasMesAnioOld($id_pais){
        unset($this->lista);
        $sql="SELECT  mes,  anio,
					ROUND(SUM(CASE moneda WHEN 'US$' THEN montototal ELSE montototal/tipocambio END),2) AS total
				FROM prg_proyecto INNER JOIN
					prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto
				WHERE  prg_proyecto.flag = '1' 
					AND prg_proyecto_detalle.flag='1' 
					AND prg_proyecto.id_pais= '$id_pais'  
					AND (anio=year(now()) or anio+1=year(now())) 
				GROUP BY 1,2
				ORDER BY 1,2 DESC";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	  
	  
	 public function select_perdidaMesAnio($id_pais,$anio,$tc){
        unset($this->lista);
        $sql="SELECT anio,mes, SUM(costo) AS total FROM (
				SELECT  mes,anio,	SUM(ROUND(ABS(IFNULL(CASE  WHEN prg_proyecto_detalle.moneda='EUR' THEN reduccion*$tc
						WHEN prg_proyecto_detalle.moneda<>'US$' THEN reduccion/tipocambio ELSE reduccion END,0)),2)) AS costo
				FROM prg_proyecto INNER JOIN
					prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
					prg_programacosto ON prg_proyecto_detalle.coddetalle=prg_programacosto.coddetalle
				WHERE prg_programacosto.flag='1'  AND prg_programacosto.flag='1' AND
					prg_proyecto.id_pais= '$id_pais'   AND anio>=$anio-1 AND anio<=$anio
				GROUP BY mes,anio
				UNION

				SELECT  mes,anio,
						 ROUND(SUM(ABS(CASE  WHEN moneda='EUR' THEN montototal*$tc WHEN moneda<>'US$' THEN montototal/tipocambio ELSE montototal END)),2) AS costo
						FROM prg_proyecto INNER JOIN
							prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
							prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
						WHERE  prg_proyecto.flag = '1' 
						  AND prg_proyecto_detalle.flag='1' 
						 AND prg_proyecto.id_pais= '$id_pais'  
						 AND anio>=$anio-1 AND anio<=$anio AND id_grupo  IN (2) 
						 GROUP BY mes,anio
			) AS vista GROUP BY anio,mes
			ORDER BY mes,anio";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	
	 public function select_perdidaMesAnioOld($id_pais){
        unset($this->lista);
        $sql="SELECT  anio,mes,id_grupo,
		 ROUND(SUM(ABS(CASE  WHEN moneda='EUR' THEN montototal*1.1 WHEN moneda<>'US$' THEN montototal/tipocambio ELSE montototal END)),2) AS total,
		 SUM(IFNULL(CASE  WHEN moneda='EUR' THEN reduccion*1.1 WHEN moneda<>'US$' THEN reduccion/tipocambio ELSE reduccion END,0)) AS reduccion
		FROM prg_proyecto INNER JOIN
			prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
			prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
			
			LEFT JOIN ( SELECT IFNULL(SUM(reduccion),0) AS reduccion,
					coddetalle
				FROM prg_programacosto 
				WHERE flag='1' 
				GROUP BY coddetalle
			) AS vista ON prg_proyecto_detalle.coddetalle=vista.coddetalle
	
		WHERE  prg_proyecto.flag = '1' 
		  AND prg_proyecto_detalle.flag='1' 
		 AND prg_proyecto.id_pais= '$id_pais'  
		 AND (anio=year(now()) or anio+1=year(now())) AND id_grupo =2
		 GROUP BY anio,mes,id_grupo
		ORDER BY anio,mes ASC ";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	  
	   
	  
	//  ventas top por clientes
    public function select_ventasClienteAnio($id_pais){
        unset($this->lista);
		
        $sql="SELECT  CONCAT_WS('-',prg_proyecto.project_id, proyect) AS f_cliente,anio,
				ROUND(SUM(CASE  WHEN moneda='US$' and anio=year(now()) THEN montototal 
					 WHEN moneda!='US$' and anio=year(now()) THEN  montototal/tipocambio END),2) AS total,
				ROUND(SUM(CASE  WHEN moneda='US$' and anio=year(now())-1 THEN montototal 
					 WHEN moneda!='US$' and anio=year(now())-1 THEN  montototal/tipocambio END),2) AS totalpas,
					 prg_proyecto.project_id
				FROM prg_proyecto INNER JOIN
					prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto
				WHERE  prg_proyecto.flag = '1' 
				  AND prg_proyecto_detalle.flag='1' 
				 AND prg_proyecto.id_pais= '$id_pais'  
				 AND (anio=year(now()) or anio+1=year(now()))
				 and mes<=month(now())	  
			GROUP BY 1
			ORDER BY 3 DESC	 
			LIMIT 0,10";
			
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    }

     
	// ventas por servicio 
    public function select_ventasTcAnio($id_pais,$tceu_dol){
        unset($this->lista);
        $sql="SELECT MONTH(fecha_emision) AS mes,
				ROUND(SUM(CASE WHEN YEAR(fecha_emision)=YEAR(NOW()) THEN  IFNULL(costo_usd,0) + IFNULL(cos_courier_usd,0) + (IFNULL(costo_eu,0)*$tceu_dol) END),1) AS total,
				ROUND(SUM( CASE WHEN YEAR(fecha_emision)+1=YEAR(NOW()) THEN IFNULL(costo_usd,0) + IFNULL(cos_courier_usd,0) + (IFNULL(costo_eu,0)*$tceu_dol) END),1) AS totalpas
				FROM tc_datos 
				WHERE flag='1' AND id_pais = '$id_pais' AND (year(fecha_emision)=year(now()) or year(fecha_emision)+1=year(now()))
				GROUP BY 1
				ORDER BY 1";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	  
	 // ventas por servicio 
    public function select_ventasLabAnio($id_pais){
        unset($this->lista);
        $sql="SELECT MONTH(fecha) AS mes,
				ROUND(SUM(CASE WHEN YEAR(fecha)=YEAR(NOW()) THEN  IFNULL(preciodol,0) END),1) AS total,
				ROUND(SUM( CASE WHEN YEAR(fecha)+1=YEAR(NOW()) THEN IFNULL(preciodol,0) END),1) AS totalpas
				FROM lab_resultado 
				WHERE flag='1' AND id_pais = '$id_pais' AND (year(fecha)=year(now()) or year(fecha)+1=year(now()))
				GROUP BY 1
				ORDER BY 1";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	
	 // ventas por categoria
    public function select_ventasCategoriaNombre($id_pais){
        unset($this->lista);
        $sql="SELECT id_categoria,categoria 
			from prg_cat_programa where flag='1' order by categoria";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	
	 // ventas por categoria
    public function select_ventasCategoriaAnio($id_pais){
        unset($this->lista);
        $sql="SELECT 
			SUM(
			case prg_programacosto.moneda when 'US$' then 
			 IFNULL(montoviatico,0) + IFNULL(montofeecert,0) +  IFNULL(montofee,0) +IFNULL(montocourier,0) + IFNULL(montoservicio,0) +
			 IFNULL(cursos,0)  + IFNULL(ampliacion,0)  + IFNULL(reduccion,0) + IFNULL(cartacor,0)  + IFNULL(analisis,0)
			 else 
				 (IFNULL(montoviatico,0) + IFNULL(montofeecert,0) +  IFNULL(montofee,0) +IFNULL(montocourier,0) + IFNULL(montoservicio,0) +
				  IFNULL(cursos,0)  + IFNULL(ampliacion,0)  + IFNULL(reduccion,0) + IFNULL(cartacor,0)  + IFNULL(analisis,0))/prg_proyecto_detalle.tipocambio
			 end	
			 
			 )  AS subtotal,
			 id_categoria, anio
			FROM prg_programacosto INNER JOIN prg_proyecto_detalle ON prg_programacosto.coddetalle=prg_proyecto_detalle.coddetalle
			INNER JOIN prg_proyecto ON prg_proyecto_detalle.id_proyecto=prg_proyecto.id_proyecto
			INNER JOIN prg_programa ON prg_programacosto.programa=prg_programa.iniciales
			WHERE prg_programacosto.flag='1'  AND prg_proyecto_detalle.flag='1' AND mes<=MONTH(NOW())
				AND anio>=YEAR(NOW())-1	AND anio<=YEAR(NOW()) AND prg_proyecto.id_pais='$id_pais'
				AND IFNULL(id_categoria,0)>0 and prg_programa.flag=1
			GROUP BY id_categoria,anio";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	
	// consultas para certificador
	 public function select_diasauditoria($id_pais,$id_categoria){
        unset($this->lista);
		$sql = "SELECT ROUND(sum(dias),2) as dias, fecha
					FROM
					(
					SELECT CONCAT_WS('_',anio_inicio,mes_inicio) AS fecha,
						sum(dias) as dias
					from resumen_calendario_fin
							INNER JOIN prg_tipoactividad
										ON (resumen_calendario_fin.id_tipoactividad = prg_tipoactividad.id_tipoactividad)
					where resumen_calendario_fin.id_estadoactividad IN (1)
						AND resumen_calendario_fin.id_pais = '$id_pais'
						AND prg_tipoactividad.id_categoria = $id_categoria
						AND anio_inicio >= YEAR(NOW()) - 1
						AND anio_inicio <= YEAR(NOW())
						
						GROUP BY mes_fin, anio_inicio) AS vista
				group by fecha";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	
	// consultas para certificador modificado
	 public function select_diasauditoria_mod($id_pais,$id_categoria){
        unset($this->lista);
        $sql="SELECT SUM(a.diasreales) AS dias, DATE_FORMAT(a.fecha,'%Y_%m') AS fecha 
				FROM prg_auditoractividad a inner join prg_auditor u on a.id_auditor=u.id_auditor 
				WHERE a.flag='1' AND u.id_pais='$id_pais' AND a.id_actividad=1 AND u.flgstatus=1 AND porcentaje>0 
				GROUP BY 2 ";
			
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	
	public function select_cat_actividad(){
        unset($this->lista);
        $sql="SELECT id_categoria, categoria 
				from prg_cat_tipoactividad 
				where flag='1' order by categoria";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	
	public function select_cat_programa(){
        unset($this->lista);
        $sql="SELECT id_categoria, categoria 
				from prg_cat_programa 
				where flag='1' order by categoria";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	
	public function select_diasactividad2($id_pais){
        unset($this->lista);
        $sql="SELECT CONCAT_WS('_',anio_inicio,a.id_categoria) AS fecha ,a.id_categoria,
			SUM((TO_DAYS(c.fin_evento) - TO_DAYS(c.inicio_evento)) + 1) 
			 - SUM(obtenerDiasEntreFecha(6,DATE_FORMAT(c.inicio_evento,'%Y-%m-%d'),DATE_FORMAT(c.fin_evento,'%Y-%m-%d')))  
			 - SUM(obtenerDiasEntreFecha(0,DATE_FORMAT(c.inicio_evento,'%Y-%m-%d'),DATE_FORMAT(c.fin_evento,'%Y-%m-%d')))  
				 AS dias
			FROM prg_calendario c INNER JOIN prg_tipoactividad a ON c.id_tipoactividad=a.id_tipoactividad
			WHERE c.flag = 1   AND id_estadoactividad IN (1) AND c.id_pais= '$id_pais'
			--	AND anio_inicio>=YEAR(NOW())-1
			--	AND anio_inicio<=YEAR(NOW())
			--	AND mes_inicio<=month(NOW())
			 --   AND case when mes_inicio=month(NOW()) then dia_inicio<=day(now())  else 1=1  end 
				AND TO_DAYS(c.fin_evento) - TO_DAYS(c.inicio_evento)>0
				AND IFNULL(a.id_categoria,0)>0
			GROUP BY anio_inicio ,a.id_categoria ";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	
	
	public function select_diasactividad($id_pais){
        unset($this->lista);
        $sql="SELECT CONCAT_WS('_',anio_inicio,a.id_categoria) AS fecha ,a.id_categoria,
			SUM(dias) AS dias
			FROM resumen_calendario_fin c INNER JOIN prg_tipoactividad a ON c.id_tipoactividad=a.id_tipoactividad
			WHERE id_estadoactividad IN (1) AND c.id_pais= '$id_pais'
				AND IFNULL(a.id_categoria,0)>0
			GROUP BY anio_inicio ,a.id_categoria	 ";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	
	public function select_diasprograma($id_pais){
        unset($this->lista);
		$sql ="
				SELECT CONCAT_WS('_',anio_inicio,t.id_categoria) AS fecha ,t.id_categoria,
				SUM(dias) AS dias
				FROM resumen_calendario_fin c INNER JOIN prg_calendario_programa a ON c.id=a.id
				INNER JOIN prg_programa t ON a.id_programa=t.id_programa INNER JOIN prg_tipoactividad
										ON (c.id_tipoactividad = prg_tipoactividad.id_tipoactividad)
				WHERE   id_estadoactividad IN (1)
				AND c.id_pais= '$id_pais'
				AND prg_tipoactividad.id_categoria =1
				-- AND c.anio_inicio>=YEAR(NOW())-1
				-- AND c.anio_inicio<=YEAR(NOW())
				-- AND c.mes_inicio<=MONTH(NOW())
				-- AND case when c.mes_inicio=month(NOW()) then DAY(c.inicio_origen_evento)<=DAY(now())  else 1=1  end
				AND IFNULL(t.id_categoria,0)>0
				GROUP BY  anio_inicio ,t.id_categoria
			";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->lista[]=$filas;
        }
        return $this->lista;	
    } 
	
						
}
?>