<?php
class reportes_cal_model{
    private $db;
	private $listas;
    public function __construct(){
        $this->db=new DBManejador();
		$this->listas=array();
    }
    /****************************************
		reporte de capacidad de auditor
	*****************************************/

	public function select_meses(){
		unset($this->listas);
		$this->listas=[];
		$sql=" select * from t_meses";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_reporte_facturas($id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql=" SELECT 
				SUM(invoice_amount) AS monto,
				COUNT(id_factura) AS total,
				YEAR(invoice_date) AS anio,
				MONTH(invoice_date) AS mes,
				CONCAT_WS('_',YEAR(invoice_date),MONTH(invoice_date)) AS aniomes
			FROM prg_comercial_factura
			WHERE flag='1' AND id IN (SELECT id FROM prg_calendario WHERE flag='1' AND id_pais='$id_pais')
			GROUP BY anio,mes";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}

	public function select_reporte_cap_aud($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT IFNULL(prg_proyecto.project_id,'') AS project_id, 
			IFNULL(prg_proyecto.proyect,'') AS proyecto, 
			CONCAT_WS(' ',Auditor.nombre,Auditor.apepaterno,Auditor.apematerno) AS nombreCompleto, 
			Actividad.descripcion,GROUP_CONCAT(Programa.descripcion, ' ') AS programas,
			getDiferenciaTiempo(Calendario.fin_evento ,Calendario.inicio_evento) AS dif_horas, 
			DATE_FORMAT(Calendario.inicio_evento,'%d/%m/%Y %H:%i') AS inicio_evento,
			DATE_FORMAT(Calendario.fin_evento,'%d/%m/%Y %H:%i') AS fin_evento, 
			Calendario.observacion 
			FROM prg_calendario Calendario LEFT JOIN 
			prg_proyecto ON Calendario.id_proyecto=prg_proyecto.project_id 
				AND Calendario.id_proyecto!='' INNER JOIN 
			prg_auditor Auditor ON Auditor.id_auditor = Calendario.id_auditor INNER JOIN 
			prg_tipoactividad Actividad ON Actividad.id_tipoactividad = Calendario.id_tipoactividad LEFT JOIN 
			prg_calendario_programa CalendarioPrograma ON Calendario.id = CalendarioPrograma.id LEFT JOIN
			prg_programa Programa ON Programa.id_programa = CalendarioPrograma.id_programa 
		  WHERE Calendario.flag=1 $searchQuery ";
		$sql.=" GROUP BY Calendario.id 
				order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_total_reporte_cap_aud($searchQuery){
		
		$sql="SELECT COUNT(DISTINCT Calendario.id) AS total
			FROM prg_calendario Calendario LEFT JOIN 
			prg_proyecto ON Calendario.id_proyecto=prg_proyecto.project_id 
				AND Calendario.id_proyecto!='' INNER JOIN 
			prg_auditor Auditor ON Auditor.id_auditor = Calendario.id_auditor INNER JOIN 
			prg_tipoactividad Actividad ON Actividad.id_tipoactividad = Calendario.id_tipoactividad LEFT JOIN 
			prg_calendario_programa CalendarioPrograma ON Calendario.id = CalendarioPrograma.id LEFT JOIN
			prg_programa Programa ON Programa.id_programa = CalendarioPrograma.id_programa 
		  WHERE Calendario.flag=1 $searchQuery " ;
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	/****************************************
		reporte de indicador de programa
	*****************************************/
	
	public function select_reporte_ind_programa($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT Calendario.id,GROUP_CONCAT(Programa.descripcion, ' ') AS programas, 
				SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(Calendario.fin_evento,Calendario.inicio_evento)))/((SELECT COUNT(*) FROM prg_calendario_programa A WHERE A.id = CalendarioPrograma.id)))AS dif_horas_n, 
				SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(Calendario.fin_evento,Calendario.inicio_evento))))AS dif_horas, Calendario.asunto,
				GROUP_CONCAT(DISTINCT id_proyecto) AS id_proyecto,
				CONCAT_WS(' ',Auditor.nombre,Auditor.apepaterno,Auditor.apematerno) AS auditor 
				FROM prg_calendario Calendario INNER JOIN 
					prg_calendario_programa CalendarioPrograma ON Calendario.id = CalendarioPrograma.id INNER JOIN 
					prg_programa Programa ON CalendarioPrograma.id_programa = Programa.id_programa INNER JOIN 
					prg_auditor Auditor ON Auditor.id_auditor = Calendario.id_auditor 
				WHERE Calendario.flag = 1 $searchQuery ";
		$sql.=" GROUP BY Calendario.id 
				order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_total_reporte_ind_programa($searchQuery){
		
		$sql="SELECT COUNT(DISTINCT Calendario.id) AS total
			FROM prg_calendario Calendario INNER JOIN 
					prg_calendario_programa CalendarioPrograma ON Calendario.id = CalendarioPrograma.id INNER JOIN 
					prg_programa Programa ON CalendarioPrograma.id_programa = Programa.id_programa INNER JOIN 
					prg_auditor Auditor ON Auditor.id_auditor = Calendario.id_auditor 
				WHERE Calendario.flag = 1 $searchQuery " ;
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	/****************************************
		reporte de rendicion de viaticos
	*****************************************/
	
	public function select_reporte_rend_viaticos($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT DISTINCT (Calendario.id) as idcalendar, 
			Calendario.id_auditor, 
			CONCAT_WS(' ',Auditor.nombre, Auditor.apepaterno, Auditor.apematerno) AS auditor,
			Calendario.asunto, 
			Calendario.monto_dolares, Calendario.monto_soles, utizado_monto_dolares,utizado_monto_soles,
			Calendario.dia_fin, Calendario.mes_fin,  Calendario.anio_fin, 
			DATE_FORMAT(Calendario.inicio_evento,'%d/%m/%Y') AS fecha_inicio_evento, 
			DATE_FORMAT(Calendario.fin_evento,'%d/%m/%Y') AS fecha_fin_evento, 
			Calendario.flag_rendicion, Calendario.rend_usuario_rendido,
			DATE_FORMAT(Calendario.rend_fecha_rendido,'%d/%m/%Y') AS fecha_rendicion,
			Calendario.rend_usuario_aprobado, 
			DATE_FORMAT(Calendario.rend_fecha_aprobado,'%d/%m/%Y') AS fecha_aprobacion 
		 FROM prg_calendario AS Calendario LEFT JOIN 
			prg_auditor AS Auditor ON (Auditor.id_auditor = Calendario.id_auditor)
		 WHERE Calendario.flag = '1'  $searchQuery ";
			$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_total_reporte_rend_viaticos($searchQuery){
		
		$sql="SELECT COUNT(DISTINCT Calendario.id) AS total
			FROM prg_calendario AS Calendario LEFT JOIN 
				 prg_auditor AS Auditor ON (Auditor.id_auditor = Calendario.id_auditor)
			WHERE Calendario.flag = 1 $searchQuery " ;
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	/****************************************
		reporte de planifificacion
	*****************************************/
	
	public function select_reporte_planificacion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$fechai,$fechaf){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT DISTINCT
			  calendario.id                     AS id,
			  calendario.asunto                 AS asunto,
			  tipoactividad.descripcion         AS descripcion,
			  concat_ws('-',proyecto.project_id,proyecto.proyect)                  AS proyect,
			  calendario.id_proyecto            AS id_proyecto,
			  calendario.nro_muestra            AS nro_muestra,
			  calendario.por_dia                AS por_dia,
			  calendario.id_auditor             AS id_auditor,
			  calendario.id_estadoactividad     AS id_estadoactividad,
			  (CASE calendario.id_estadoactividad 
			  WHEN 1 THEN 'PLANIFICADO' 
			  WHEN 4 THEN 'CANCELADO' 
			  WHEN 2 THEN 'REPROGRAMADO' 
			  WHEN 3 THEN 'EJECUTADO' 
			  WHEN 5 THEN 'PROVISIONAL' 
			  WHEN 9 THEN 'CONTRATADO' 
			  WHEN 10 THEN 'FACTURADO' 
			  WHEN 11 THEN 'INCOMPLETO' 
			  END) AS estadoactividad,
			  CONCAT_WS(' ',auditor.nombre,auditor.apepaterno,auditor.apematerno) AS nombreCompleto,
			  calendario.id_asignacion_viaticos AS id_asignacion_viaticos,
			  calendario.monto_soles            AS monto_soles,
			  calendario.monto_dolares          AS monto_dolares,
			  calendario.utizado_monto_dolares  AS utizado_monto_dolares,
			  calendario.utizado_monto_soles    AS utizado_monto_soles,
			  calendario.observacion            AS observacion,
			  DATE_FORMAT(calendario.inicio_evento,'%d/%m/%Y %H:%i') AS inicio_evento,
			  DATE_FORMAT(calendario.fin_evento,'%d/%m/%Y %H:%i') AS fin_evento,
			  calendario.inicio_evento          AS inicio_origen_evento,
			  calendario.fin_evento             AS fin_origen_evento,
			  calendario.causa_cliente          AS causa_cliente,
			  calendario.causa_cuperu           AS causa_cuperu,
			  calendario.id_tipoactividad       AS id_tipoactividad,
			  calendario.flghorariofijo,
			  case 
				when '$fechaf'!='' and TO_DAYS(calendario.fin_evento)>=to_days('$fechaf') then 
					TO_DAYS('$fechaf') - TO_DAYS(calendario.inicio_evento) + 1
				when '$fechai'!='' and TO_DAYS(calendario.inicio_evento)<to_days('$fechai') then 
					TO_DAYS(calendario.fin_evento) - TO_DAYS('$fechai') + 1		
				else
					TO_DAYS(calendario.fin_evento) - TO_DAYS(calendario.inicio_evento) + 1  end 
				AS dif_dias,
			  SEC_TO_TIME((TIMESTAMPDIFF(MINUTE,calendario.inicio_evento,calendario.fin_evento) * 60)) AS dif_horas,
			  TIMESTAMPDIFF(MINUTE,inicio_evento,fin_evento)/60 AS horas,
			  round((hora_fin -  hora_inicio),1)/60 as horas2,
			  calendario.id_pais                AS id_pais,
			  calendario.is_sabado              AS is_sabado,
			  calendario.is_domingo             AS is_domingo,
				case 
				when '$fechaf'!='' and '$fechai'!='' and  TO_DAYS(calendario.fin_evento)>to_days('$fechaf') and TO_DAYS(calendario.inicio_evento)< to_days('$fechai') then 
					obtenerDiasEntreFecha(6,DATE_FORMAT('$fechai','%Y-%m-%d'),DATE_FORMAT('$fechaf','%Y-%m-%d'))  
				when '$fechai'!='' and   TO_DAYS(calendario.inicio_evento)< to_days('$fechai') then 
					obtenerDiasEntreFecha(6,DATE_FORMAT('$fechai','%Y-%m-%d'),DATE_FORMAT(calendario.fin_evento,'%Y-%m-%d'))  
				
				when '$fechaf'!='' and TO_DAYS(calendario.fin_evento)>=to_days('$fechaf') then 
					obtenerDiasEntreFecha(6,DATE_FORMAT(calendario.inicio_evento,'%Y-%m-%d'),DATE_FORMAT('$fechaf','%Y-%m-%d'))  
				else
					obtenerDiasEntreFecha(6,DATE_FORMAT(calendario.inicio_evento,'%Y-%m-%d'),DATE_FORMAT(calendario.fin_evento,'%Y-%m-%d'))  
				end AS sabado_dia,
				
				case 
				when '$fechaf'!='' and '$fechai'!='' and  TO_DAYS(calendario.fin_evento)>=to_days('$fechaf') and TO_DAYS(calendario.inicio_evento)< to_days('$fechai') then 
					obtenerDiasEntreFecha(0,DATE_FORMAT('$fechai','%Y-%m-%d'),DATE_FORMAT('$fechaf','%Y-%m-%d'))  
				when '$fechai'!='' and   TO_DAYS(calendario.inicio_evento)< to_days('$fechai') then 
					obtenerDiasEntreFecha(0,DATE_FORMAT('$fechai','%Y-%m-%d'),DATE_FORMAT(calendario.fin_evento,'%Y-%m-%d'))  
				
				when '$fechaf'!='' and TO_DAYS(calendario.fin_evento)>=to_days('$fechaf') then 
					obtenerDiasEntreFecha(0,DATE_FORMAT(calendario.inicio_evento,'%Y-%m-%d'),DATE_FORMAT('$fechaf','%Y-%m-%d'))
				else
					obtenerDiasEntreFecha(0,DATE_FORMAT(calendario.inicio_evento,'%Y-%m-%d'),DATE_FORMAT(calendario.fin_evento,'%Y-%m-%d'))  
				end AS domingo_dia,
			  
			  TRUNCATE(((((TO_DAYS(calendario.fin_evento) - TO_DAYS(calendario.inicio_evento)) - WEEKDAY((calendario.fin_evento + INTERVAL (-(6) + 1) DAY))) + 7) / 7),0) AS cant_sabado,
			  TRUNCATE(((((TO_DAYS(calendario.fin_evento) - TO_DAYS(calendario.inicio_evento)) - WEEKDAY((calendario.fin_evento + INTERVAL (-(0) + 1) DAY))) + 7) / 7),0) AS cant_domingo,
			 
			 (CASE WHEN ((calendario.is_sabado = '0') AND (calendario.is_domingo = '0')) THEN ((((TO_DAYS(calendario.fin_evento) - TO_DAYS(calendario.inicio_evento)) + 1) - TRUNCATE(((((TO_DAYS(calendario.fin_evento) - TO_DAYS(calendario.inicio_evento)) - WEEKDAY((calendario.fin_evento + INTERVAL (-(6) + 1) DAY))) + 7) / 7),0)) - TRUNCATE(((((TO_DAYS(calendario.fin_evento) - TO_DAYS(calendario.inicio_evento)) - WEEKDAY((calendario.fin_evento + INTERVAL (-(0) + 1) DAY))) + 7) / 7),0)) WHEN (calendario.is_sabado = '0') THEN (((TO_DAYS(calendario.fin_evento) - TO_DAYS(calendario.inicio_evento)) + 1) - TRUNCATE(((((TO_DAYS(calendario.fin_evento) - TO_DAYS(calendario.inicio_evento)) - WEEKDAY((calendario.fin_evento + INTERVAL (-(6) + 1) DAY))) + 7) / 7),0)) WHEN (calendario.is_domingo = '0') THEN (((TO_DAYS(calendario.fin_evento) - TO_DAYS(calendario.inicio_evento)) + 1) - TRUNCATE(((((TO_DAYS(calendario.fin_evento) - TO_DAYS(calendario.inicio_evento)) - WEEKDAY((calendario.fin_evento + INTERVAL (-(0) + 1) DAY))) + 7) / 7),0)) ELSE ((TO_DAYS(calendario.fin_evento) - TO_DAYS(calendario.inicio_evento)) + 1) END) AS diasreal,
			  IFNULL(GROUP_CONCAT(distinct prg_programa.descripcion),'') AS programa,
			  ifnull(SUBSTRING(group_concat(distinct unidades.codigo_lugar separator '<br>'),1,60),'') as prod_and_proc,
			  GROUP_CONCAT(DISTINCT CONCAT_WS(' ',CONVERT(prg_estadoproyecto.descripcion,char),MONTHNAME(STR_TO_DATE(prg_proyecto_detalle.mes, '%m')),prg_proyecto_detalle.anio)) AS proycomercial,
			  calendario.usuario_ingreso		AS usuario_ingreso
			FROM    prg_calendario calendario LEFT JOIN 
				prg_proyecto proyecto ON calendario.id_proyecto = proyecto.project_id AND proyecto.project_id <> '' and proyecto.id_pais=calendario.id_pais LEFT JOIN 
				prg_tipoactividad tipoactividad ON calendario.id_tipoactividad = tipoactividad.id_tipoactividad JOIN 
				prg_auditor auditor ON calendario.id_auditor = auditor.id_auditor LEFT JOIN 
				prg_calendario_lugar unidades ON calendario.id = unidades.id LEFT JOIN
				prg_calendario_programa ON calendario.id=prg_calendario_programa.id LEFT JOIN
				prg_programa ON prg_calendario_programa.id_programa=prg_programa.id_programa LEFT JOIN
				prg_calendario_comercial ON calendario.id=prg_calendario_comercial.id  LEFT JOIN
				prg_proyecto_detalle ON prg_calendario_comercial.coddetalle=prg_proyecto_detalle.coddetalle AND prg_proyecto_detalle.flag='1' LEFT JOIN
				prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado 
			WHERE 
				calendario.flag = 1  $searchQuery 
				group BY calendario.id 
				order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_total_reporte_planificacion($searchQuery){
		// AND calendario.id_estadoactividad NOT IN(2,4) 
		$sql="SELECT COUNT(DISTINCT calendario.id) AS total
				FROM    prg_calendario calendario LEFT JOIN 
					prg_proyecto proyecto ON calendario.id_proyecto = proyecto.project_id AND proyecto.project_id <> '' and proyecto.id_pais=calendario.id_pais LEFT JOIN 
					prg_tipoactividad tipoactividad ON calendario.id_tipoactividad = tipoactividad.id_tipoactividad JOIN 
					prg_auditor auditor ON calendario.id_auditor = auditor.id_auditor LEFT JOIN 
					prg_calendario_lugar unidades ON calendario.id = unidades.id LEFT JOIN
					prg_calendario_programa ON calendario.id=prg_calendario_programa.id LEFT JOIN
					prg_programa ON prg_calendario_programa.id_programa=prg_programa.id_programa LEFT JOIN
					prg_calendario_comercial ON calendario.id=prg_calendario_comercial.id  LEFT JOIN
					prg_proyecto_detalle ON prg_calendario_comercial.coddetalle=prg_proyecto_detalle.coddetalle AND prg_proyecto_detalle.flag='1' LEFT JOIN
					prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
				WHERE 
					calendario.flag = 1   
					and EXTRACT(HOUR FROM TIMEDIFF(calendario.fin_evento,calendario.inicio_evento))>0$searchQuery " ;
		
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	/****************************************
		reporte de planifificacion
	*****************************************/
	
	public function select_reporte_reprogramacion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT Calendario.id,Proyecto.proyect, 
				DATE_FORMAT(Calendario.inicio_evento,'%d/%m/%Y %H:%i') AS fechainicio,DATE_FORMAT(Calendario.fin_evento,'%d/%m/%Y %H:%i') AS fechafinal,
				CONCAT_WS(' ',Auditor.nombre,Auditor.apepaterno,Auditor.apematerno) AS auditor,
				IFNULL(Calendario.parent,id) AS parent_id,Estado.descripcion 
			FROM prg_calendario  Calendario 
				INNER JOIN prg_proyecto  Proyecto ON Calendario.id_proyecto =  Proyecto.project_id
				INNER JOIN prg_auditor Auditor ON Auditor.id_auditor = Calendario.id_auditor
				INNER JOIN prg_estadoactividad  Estado ON Estado.id_estadoactividad = Calendario.id_estadoactividad
			WHERE Calendario.flag = 1  $searchQuery ";
		$sql.="	order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_total_reporte_reprogramacion($searchQuery){
		
		$sql="SELECT COUNT(DISTINCT calendario.id) AS total
				FROM prg_calendario  Calendario 
					INNER JOIN prg_proyecto  Proyecto ON Calendario.id_proyecto =  Proyecto.project_id
					INNER JOIN prg_auditor Auditor ON Auditor.id_auditor = Calendario.id_auditor
					INNER JOIN prg_estadoactividad  Estado ON Estado.id_estadoactividad = Calendario.id_estadoactividad
				WHERE 
					calendario.flag = 1  $searchQuery " ;
		
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}

	
	/****************************************
		reporte de VINCULO COMERCIAL
	*****************************************/
	
	public function select_reporte_vinculopc($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT  IFNULL(c.id_proyecto,'') AS codproyecto,IFNULL(p.proyect,'') AS proyecto,
				IFNULL(GROUP_CONCAT(pg.descripcion),'') AS programa,
				DATE_FORMAT(inicio_evento,'%d/%m/%Y %H:%i') AS inicio, 
				DATE_FORMAT(fin_evento,'%d/%m/%Y %H:%i') AS fin,
				t.descripcion AS tipoactividad,CONCAT_WS('',nombre,apepaterno,apematerno) AS auditor,
				IFNULL(e.descripcion,'Sin vinculo comercial') AS estadoproyecto,IFNULL(t_meses.mes,'') AS mes, IFNULL(d.anio,'') AS anio
			FROM prg_calendario c LEFT JOIN 
				prg_calendario_comercial ON c.id=prg_calendario_comercial.id LEFT JOIN
				prg_proyecto_detalle d ON prg_calendario_comercial.coddetalle=d.coddetalle AND d.flag='1' LEFT JOIN
				prg_proyecto p ON c.id_proyecto=p.project_id LEFT JOIN
				prg_calendario_programa cp ON c.id=cp.id LEFT JOIN
				prg_programa pg ON  cp.id_programa=pg.id_programa INNER JOIN
				prg_auditor a ON c.id_auditor=a.id_auditor INNER JOIN
				prg_tipoactividad t ON c.id_tipoactividad=t.id_tipoactividad LEFT JOIN
				prg_estadoproyecto e ON d.codestado=e.codestado AND e.flag='1' LEFT JOIN
				t_meses ON d.mes=t_meses.id_mes
			WHERE c.flag='1' $searchQuery ";
		$sql.="	
		GROUP BY c.id
		order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_total_reporte_vinculopc($searchQuery){
		
		$sql="SELECT COUNT(DISTINCT c.id) AS total
				FROM prg_calendario c LEFT JOIN 
				prg_calendario_comercial ON c.id=prg_calendario_comercial.id LEFT JOIN
				prg_proyecto_detalle d ON prg_calendario_comercial.coddetalle=d.coddetalle AND d.flag='1' LEFT JOIN
				prg_proyecto p ON c.id_proyecto=p.project_id LEFT JOIN
				prg_calendario_programa cp ON c.id=cp.id LEFT JOIN
				prg_programa pg ON  cp.id_programa=pg.id_programa INNER JOIN
				prg_auditor a ON c.id_auditor=a.id_auditor INNER JOIN
				prg_tipoactividad t ON c.id_tipoactividad=t.id_tipoactividad LEFT JOIN
				prg_estadoproyecto e ON d.codestado=e.codestado AND e.flag='1' LEFT JOIN
				t_meses ON d.mes=t_meses.id_mes
				WHERE 
					c.flag = 1  $searchQuery " ;
		
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	/****************************************
		reporte de comprara factura
	*****************************************/
	
	public function select_proyeccion_mes($id_pais,$idPro){
		unset($this->listas);
		$this->listas=[];
		$sql="select sum(monto) as monto ,mes,anio
			from prg_proyeccion_mes 
			where flag='1' and id_pais='$id_pais'";
		if(!empty($idPro))	$sql.=" and tipo in ($idPro) ";
		$sql.=" group by anio,mes";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_proyeccion_mes_venta($anio,$id_pais,$ids,$tcEuUS){
		unset($this->listas);
		$this->listas=[];
		
		 $sqlUnion="SELECT  
			 IFNULL(SUM(CASE  WHEN YEAR(fecha)=$anio THEN IFNULL(costo_usd,0) + IFNULL(cos_courier_usd,0) + (IFNULL(costo_eu,0)*$tcEuUS) END),0)	AS montoanio,
			 0 as montorestecnico,
			 IFNULL(SUM(CASE  WHEN YEAR(fecha)=$anio-1 THEN IFNULL(costo_usd,0) + IFNULL(cos_courier_usd,0) + (IFNULL(costo_eu,0)*$tcEuUS) END),0)	AS montoanio_ante,
			 0 as montorestecnico_ante,
			 'US$' as moneda,
			 MONTH(fecha) AS mes
		FROM tc_datos
		WHERE  flag = '1'  AND id_pais='$id_pais' and  year(fecha) >=$anio-1
	   GROUP BY MONTH(fecha)  
	   union
	   SELECT 
			 IFNULL(SUM(CASE  WHEN YEAR(fecha)=$anio THEN preciodol END),0)	AS montoanio,
			 0 as montorestecnico,
			 IFNULL(SUM(CASE  WHEN YEAR(fecha)=$anio-1 THEN preciodol END),0)	AS montoanio_ante,
			 0 as montorestecnico_ante,
			 'US$' as moneda,
			 MONTH(fecha) AS mes
		FROM lab_resultado
			WHERE flag='1' AND id_pais='$id_pais' AND YEAR(fecha)>=$anio-1	
			GROUP BY MONTH(fecha) ";
	 
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
		 prg_estadoproyecto_grupo ON prg_estadoproyecto.id_grupo=prg_estadoproyecto_grupo.id_grupo and
			prg_estadoproyecto.id_pais=prg_estadoproyecto_grupo.id_pais
		WHERE  prg_proyecto.flag = '1'  
			AND prg_proyecto_detalle.flag='1' AND tipo IN ($ids)
			AND prg_proyecto.id_pais= '$id_pais'  AND (anio=$anio or anio=($anio-1))
		GROUP BY mes";

		//  monto tecnico proyecto 
		$sqlAll="SELECT 
				IFNULL(SUM(CASE  
						WHEN moneda='EUR' and anio=$anio THEN montorestecnico*$tcEuUS
						WHEN moneda='US$' and anio=$anio THEN montorestecnico 
						WHEN anio=$anio then montorestecnico/tipocambio END),0)
				AS montoanio,
				0
				AS montorestecnico,
				IFNULL(SUM(CASE  
						WHEN moneda='EUR' and anio=($anio-1) THEN montorestecnico*$tcEuUS
						WHEN moneda='US$' and anio=($anio-1) THEN montorestecnico 
						WHEN (anio=$anio-1) then montorestecnico/tipocambio END),0)
				AS montoanio_ante,
				0
				AS montorestecnico_ante,
			prg_proyecto_detalle.moneda,
			mes
		FROM prg_proyecto INNER JOIN
		 prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto INNER JOIN
		 prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado INNER JOIN
		 prg_estadoproyecto_grupo ON prg_estadoproyecto.id_grupo=prg_estadoproyecto_grupo.id_grupo
		WHERE  prg_proyecto.flag = '1'  
			AND prg_proyecto_detalle.flag='1' AND tipo IN (3)
			AND prg_proyecto.id_pais= '$id_pais'  AND (anio=$anio or anio=($anio-1))
		GROUP BY mes";
		
		if($ids==1)
			$sql=" SELECT SUM(montoanio) AS montoanio, SUM(montorestecnico) AS montorestecnico, SUM(montoanio_ante) AS montoanio_ante,
					SUM(montorestecnico_ante) AS montorestecnico_ante,moneda,mes
				FROM ( $sql union $sqlUnion ) as vista 
				GROUP BY mes 
				ORDER BY mes asc
				"; 
		else if($ids==3 or $ids==5)
			$sql=" SELECT SUM(montoanio) AS montoanio, SUM(montorestecnico) AS montorestecnico, SUM(montoanio_ante) AS montoanio_ante,
					SUM(montorestecnico_ante) AS montorestecnico_ante,moneda,mes
				FROM ( $sql  ) as vista 
				GROUP BY mes 
				ORDER BY mes asc
				"; 
		else
			$sql=" SELECT SUM(montoanio) AS montoanio, SUM(montorestecnico) AS montorestecnico, SUM(montoanio_ante) AS montoanio_ante,
					SUM(montorestecnico_ante) AS montorestecnico_ante,moneda,mes
				FROM ( $sql union $sqlUnion union $sqlAll) as vista 
				GROUP BY mes 
				ORDER BY mes asc
				"; 

		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_proyeccion_mes_tipo($id_pais,$idPro,$anio){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT m.mes,m.id_mes, p.monto
					FROM t_meses m LEFT JOIN prg_proyeccion_mes p ON m.id_mes=p.mes 
					AND  p.flag='1' and 	id_pais='$id_pais' and anio=$anio and tipo=$idPro";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	 public function delete_proyeccionFac($idPro,$anio,$id_pais){
	   
        $sql="delete from prg_proyeccion_mes where tipo=$idPro and anio=$anio and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }

	public function insert_proyeccionFac($idPro,$anio,$id_pais,$id_mes,$monto){
	   
        $sql="insert into prg_proyeccion_mes (tipo,anio,mes,id_pais,monto,flag,fecha_ingreso)
				values($idPro,$anio,$id_mes,'$id_pais',$monto,'1',now())";
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	
	
	public function select_mes(){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT mes,id_mes	FROM t_meses";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}

	// reporte viaticos
	public function select_resumen_viaticos($id_pais,$anio,$G_tc){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT 
			COUNT(DISTINCT prg_calendario.id) total,
			SUM(CASE flag_rendicion WHEN 1 THEN IFNULL(monto_dolares,0) + IFNULL(monto_soles/$G_tc,0) ELSE 0 END) AS entdolares,
			mes_inicio, ifnull(id_auditor,0) as id_auditor
		FROM prg_calendario  
		WHERE flag='1' AND id_pais='$id_pais' AND  anio_inicio=$anio 
		GROUP BY mes_inicio,id_auditor
		HAVING IFNULL(entdolares,0) >0 ";
		
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_resumen_viaticos_auditor($id_pais,$anio){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT distinct prg_auditor.id_auditor, CONCAT_WS(' ',nombre,apepaterno,apematerno) AS auditor
				FROM prg_auditor inner join prg_calendario on prg_auditor.id_auditor=prg_calendario.id_auditor
				WHERE prg_auditor.flag='1' AND prg_auditor.id_pais='$id_pais'
					and flag_rendicion='1' AND  anio_inicio=$anio  and (monto_dolares>0 or monto_soles>0)
				ORDER BY 2";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function select_resumen_viaticos_auditor_monto($id_pais,$anio,$G_tc){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT
			COUNT(DISTINCT prg_calendario.id) total,
			SUM(CASE  WHEN flag_rendicion in (1,2,3) THEN ifnull(monto_dolares,0) + ifnull(monto_soles/$G_tc,0) ELSE 0 END) AS entdolares,
			SUM(CASE flag_rendicion WHEN 2 THEN ifnull(utizado_monto_dolares,0) + ifnull(utizado_monto_soles/$G_tc,0) ELSE 0 END) AS dolares,
			SUM(CASE is_facturado WHEN 's' THEN ifnull(utizado_monto_dolares,0) + ifnull(utizado_monto_soles/$G_tc,0) ELSE 0 END) AS apbdolares,

			mes_inicio
		FROM prg_calendario
		WHERE flag='1' AND id_pais='$id_pais' AND  anio_inicio=$anio
		GROUP BY mes_inicio";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function select_resumen_viaticos_excel($id_pais,$anio,$id_auditor,$mes,$tc){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT asunto, DATE_FORMAT(inicio_evento,'%d/%m/%Y %H:%i') AS fechaini, project_id,
				DATE_FORMAT(fin_evento,'%d/%m/%Y %H:%i') AS fin_evento,IFNULL(proyect,'') AS proyecto, prg_calendario.id_proyecto,
				CONCAT_WS(' ', nombre,apepaterno,apematerno) AS auditor,
				CASE flag_rendicion WHEN 1 THEN IFNULL(monto_dolares,0) + IFNULL(monto_soles/$tc,0) ELSE 0 END AS importe
			FROM prg_calendario INNER JOIN prg_auditor ON prg_calendario.id_auditor = prg_auditor.id_auditor LEFT JOIN
				prg_proyecto ON prg_calendario.id_proyecto=prg_proyecto.project_id
			WHERE prg_calendario.flag='1' AND prg_calendario.id_pais= '$id_pais'  AND prg_proyecto.id_pais='$id_pais' ";

		if($id_auditor!='') $sql.=" and prg_calendario.id_auditor=$id_auditor ";	
		if($anio!='') $sql.=" and YEAR(inicio_evento)= $anio ";
		if($mes!='') $sql.=" and MONTH(inicio_evento)= $mes ";

		$sql.="	ORDER BY inicio_evento ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}

	public function selec_total_reporte_programmed_services($searchQuery){
		
		$sql="SELECT COUNT(*) AS total
			FROM prg_calendario
			WHERE  1=1 and invoice_number is not null  $searchQuery " ;
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}

	public function selec_reporte_programmed_services($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT prg_comercial_factura.*,
				date_format(prg_comercial_factura.invoice_date,'%d/%m/%Y') as invoice_date2,
				prg_proyecto.proyect as nombre_proyecto,
				prg_proyecto.project_id as cu_proyecto,
				prg_estadoproyecto.descripcion as nombre_estadoproyecto,
				CONCAT_WS(' ',prg_auditor.nombre,prg_auditor.apepaterno,prg_auditor.apematerno) as auditor,
				CONCAT_WS(' ',pa.nombre,pa.apepaterno,pa.apematerno) as comercial_executive,
				prg_tipoactividad.descripcion as tipo_actividad,
                IFNULL(GROUP_CONCAT(distinct prg_programa.descripcion),'') AS programa,
				prg_estadoactividad.descripcion as planned_status,
				prg_calendario.inicio_evento,
				prg_calendario.fin_evento,
				prg_comercial_factura.id_comercial_executive
			FROM prg_calendario
				INNER JOIN prg_proyecto on prg_proyecto.project_id=prg_calendario.id_proyecto
				
				INNER JOIN prg_auditor on prg_calendario.id_auditor = prg_auditor.id_auditor
				LEFT JOIN prg_tipoactividad prg_tipoactividad on prg_tipoactividad.id_tipoactividad = prg_calendario.id_tipoactividad
				LEFT JOIN prg_calendario_programa ON prg_calendario.id=prg_calendario_programa.id
				LEFT JOIN prg_programa ON prg_calendario_programa.id_programa=prg_programa.id_programa

				INNER JOIN prg_estadoactividad ON prg_estadoactividad.id_estadoactividad = prg_calendario.id_estadoactividad
				inner join prg_comercial_factura on prg_calendario.id=prg_comercial_factura.id
				INNER JOIN prg_estadoproyecto on prg_comercial_factura.id_estado_proyecto = prg_estadoproyecto.codestado
				LEFT JOIN prg_usuarios on prg_comercial_factura.id_comercial_executive = prg_usuarios.id_usuario
				LEFT JOIN prg_auditor pa on prg_usuarios.id_auditor = pa.id_auditor
			WHERE  prg_calendario.flag='1' $searchQuery ";
		$sql.=" group BY prg_comercial_factura.id_factura  order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ."";
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}

	public function selec_total_reporte_due_audits($searchQuery){
		
		$sql="
			SELECT
				COUNT(vista.id_auditactiv) AS total
			FROM
			(SELECT
			prg_auditoractividad.id_auditactiv
			FROM prg_auditoractividad
			INNER JOIN prg_proyecto ON prg_proyecto.project_id=prg_auditoractividad.project_id
			INNER JOIN t_mae_pais ON prg_auditoractividad.id_pais = t_mae_pais.id_pais
			INNER JOIN prg_auditor ON prg_auditoractividad.id_auditor = prg_auditor.id_auditor
			INNER JOIN prg_programa ON prg_auditoractividad.id_programa=prg_programa.id_programa
			WHERE
			prg_auditoractividad.id_actividad = 1
			AND prg_auditoractividad.flag = 1
			AND prg_auditoractividad.fecha_mc is not null 
			AND YEAR(prg_auditoractividad.fecha_mc) >= 2023  $searchQuery
			GROUP BY prg_auditoractividad.id_auditactiv  ) AS vista " ;
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	public function selec_reporte_due_audits($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="
			SELECT
				prg_auditoractividad.*,
				prg_proyecto.proyect as nombre_proyecto,
				prg_proyecto.project_id as cu_proyecto,
				t_mae_pais.nombre as pais,
				prg_proyecto.email as email_proyecto,
				prg_proyecto.telephone as telefono_proyecto,
				CONCAT_WS(' ',prg_auditor.nombre,prg_auditor.apepaterno,prg_auditor.apematerno) as auditor,
				IFNULL(GROUP_CONCAT(distinct prg_programa.descripcion),'') as programa,
				prg_auditoractividad.fecha as fecha,
				prg_auditoractividad.fecha_mc as fecha_mc,
				DATE(DATE_ADD(prg_auditoractividad.fecha_mc, INTERVAL 90 DAY)) as fecha_mc_90_days

			FROM prg_auditoractividad
			INNER JOIN prg_proyecto ON prg_proyecto.project_id=prg_auditoractividad.project_id
			INNER JOIN t_mae_pais ON prg_auditoractividad.id_pais = t_mae_pais.id_pais
			INNER JOIN prg_auditor ON prg_auditoractividad.id_auditor = prg_auditor.id_auditor
			LEFT JOIN prg_programa ON prg_auditoractividad.id_programa=prg_programa.id_programa

			WHERE prg_auditoractividad.id_actividad = 1
					AND prg_auditoractividad.flag = 1
					AND prg_auditoractividad.fecha_mc is not null
					AND YEAR(prg_auditoractividad.fecha_mc) >= 2023 $searchQuery ";
		$sql.=" GROUP BY prg_auditoractividad.id_auditactiv order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ."";
		
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