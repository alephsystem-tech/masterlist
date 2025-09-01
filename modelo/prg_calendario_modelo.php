<?php
class prg_calendario_model{
    private $db;
	private $listas;
    public function __construct(){
        $this->db=new DBManejador();
		$this->listas=array();
    }
    /* MODELO para seleccionar  paises
        junio 2021
		Autor: Enrique Bazalar alephsystem@gmail.com
    */

	public function select_calendario_vacacion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT Calendario.id, Actividad.descripcion, CONCAT_WS(' ',Auditor.nombre,Auditor.apepaterno,Auditor.apematerno) AS auditor, 
				DATE_FORMAT(Calendario.inicio_evento, '%d/%m/%Y %H:%i') AS fec_inicio, 
				DATE_FORMAT(Calendario.fin_evento, '%d/%m/%Y %H:%i') AS fec_final, 
				
				DATE_FORMAT(Calendario.rend_fecha_aprobado, '%d/%m/%Y %H:%i') AS fec_aprueba, 
				ifnull(rend_usuario_aprobado,'') as rend_usuario_aprobado,
				
				Calendario.observacion, Calendario.flag_rendicion ,
				case Calendario.flag_rendicion when '1' then 'Pendiente' when '3' then 'Aprobado' else '' end as estado 
			FROM prg_calendario AS Calendario INNER JOIN
				prg_auditor AS Auditor ON (Auditor.id_auditor = Calendario.id_auditor) INNER JOIN 
				prg_tipoactividad AS Actividad ON (Actividad.id_tipoactividad = Calendario.id_tipoactividad) 
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
	
	
	// total de registros por auditor fecha
	public function selec_total_calendario_vacacion($searchQuery=null){
		$sql=" SELECT COUNT(Calendario.id) AS total 
			FROM prg_calendario AS Calendario INNER JOIN
				prg_auditor AS Auditor ON (Auditor.id_auditor = Calendario.id_auditor) INNER JOIN 
				prg_tipoactividad AS Actividad ON (Actividad.id_tipoactividad = Calendario.id_tipoactividad) 
			WHERE Calendario.flag = '1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	// valida si obliga o no ingresar programa en planning
	public function validar_programa($id_tipoactividad,$id_pais){
		$sql=" SELECT case ifnull(flgobligacal,'')  when 1 then '1' else '0' end as valor
			FROM prg_tipoactividad
			WHERE id_tipoactividad=$id_tipoactividad and id_pais='$id_pais' " ;
			
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_calendario_complex($id){
		
		$sql="SELECT 
				CONCAT_WS(' ', A.nombre, A.apepaterno, A.apematerno) AS auditor, 
				T.descripcion, Proyecto.proyect, C.id_calendario, 
				C.id_tipoactividad, C.id_proyecto, C.id_auditor, 
				C.asunto, C.lugares, 
				C.id_asignacion_viaticos, 
				CASE C.id_asignacion_viaticos WHEN '1' THEN 'Cliente' WHEN '2' THEN 'Cuperu' WHEN '3' THEN 'Por reembolsar' ELSE '' END AS asignacion,
				C.nro_muestra, C.por_dia, C.id_mediotransporte, 
				C.horas_viaje, C.monto_soles, C.monto_dolares, 
				C.utilizado_hospedaje_dolares, C.utilizado_movilidad_dolares, 
				C.utilizado_alimentacion_dolares, C.utilizado_contingencia_dolares, 
				C.utizado_monto_dolares, C.utilizado_hospedaje_soles, 
				C.utilizado_movilidad_soles, C.utilizado_alimentacion_soles, 
				C.utilizado_contingencia_soles, C.utizado_monto_soles, 
				C.id_estadoactividad, C.observacion, C.hora_inicial, 
				C.hora_inicio, C.dia_inicio, C.mes_inicio, 
				C.anio_inicio, C.hora_final, C.hora_fin, C.dia_fin, 
				C.mes_fin, C.anio_fin, C.id_pais, C.motivo_desaprobacion, 
				C.flag_rendicion, ((TO_DAYS (C.fin_evento) - TO_DAYS(C.inicio_evento)) + 1) AS dif_dias, 
				C.is_sabado, C.is_domingo, 
				obtenerDiasEntreFecha(6,DATE_FORMAT(C.inicio_evento,'%Y-%m-%d'),DATE_FORMAT(C.fin_evento,'%Y-%m-%d')) AS sabado_dia, 
				obtenerDiasEntreFecha(7,DATE_FORMAT(C.inicio_evento,'%Y-%m-%d'),DATE_FORMAT(C.fin_evento,'%Y-%m-%d')) AS domingo_dia ,
				DATE_FORMAT(C.inicio_evento,'%d/%m/%Y') AS fechai,
				DATE_FORMAT(C.fin_evento,'%d/%m/%Y') AS fechaf,
				DATE_FORMAT(C.rend_fecha_aprobado,'%d/%m/%Y') AS rend_fecha_aprobadof,
				
				Proyecto.city, Proyecto.country,
				flghorariofijo,
				-- DATE_FORMAT(C.rend_fecha_rendido,'%d/%m/%Y') AS fecha_rendicion
				DATE_FORMAT(C.rend_fecha_rendido,'%d/%m/%Y %H:%i') AS fecha_rendicion
			FROM 
				prg_calendario AS C LEFT JOIN 
				prg_auditor AS A ON (A.id_auditor = C.id_auditor) LEFT JOIN
				prg_tipoactividad AS T ON (T.id_tipoactividad = C.id_tipoactividad) LEFT JOIN 
				prg_proyecto AS Proyecto ON (Proyecto.project_id = C.id_proyecto)  AND  Proyecto.id_pais=C.id_pais and Proyecto.flag='1'
			WHERE C.id = $id";
			
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}

	public function update_calendario_vacacion($id,$flag_rendicion,$id_pais,$fechahora,$usuario,$ip){
		if($flag_rendicion==1)
			$sql="update prg_calendario set flag_rendicion='$flag_rendicion',
					usuario_modifico='$usuario',ip_modifico='$ip',fecha_modifico='$fechahora'
				WHERE id in ($id)";
		else if($flag_rendicion==3)
			$sql="update prg_calendario set flag_rendicion='$flag_rendicion',
					rend_usuario_aprobado='$usuario',rend_fecha_aprobado='$fechahora'
				WHERE id in ($id)";
		else	
			$sql="update prg_calendario set flag_rendicion='$flag_rendicion',
					usuario_modifico='$usuario',ip_modifico='$ip',fecha_modifico='$fechahora'
				WHERE id in ($id)";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
		
	}
	
	public function update_calendario_interno($id,$is_facturado,$id_pais,$usuario,$ip){
		$sql="update prg_calendario set is_facturado='$is_facturado',
					usuario_modifico='$usuario',ip_modifico='$ip',fecha_modifico=now()
				WHERE id in ($id)";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
		
	}
	
	/*
	RENDICION
	*/
	
	public function select_calendario_rendicion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT DISTINCT  
				Calendario.id, Calendario.id_auditor, Proyecto.project_id, Proyecto.proyect, 
				 CONCAT_WS(' ',Auditor.nombre, Auditor.apepaterno, Auditor.apematerno) AS fullname, 
				 Calendario.asunto, 
				 Calendario.monto_dolares, Calendario.monto_soles, 
				 DATE_FORMAT(Calendario.inicio_evento,'%d/%m/%Y') AS fecha_inicio_evento, 
				 DATE_FORMAT(Calendario.fin_evento,'%d/%m/%Y') AS fecha_fin_evento, 
				 DATE_FORMAT(Calendario.rend_fecha_aprobado,'%d/%m/%Y') AS rend_fecha_aprobado,
				ifnull(rend_usuario_aprobado,'')	 rend_usuario_aprobado,			 
				 DATE_FORMAT(Calendario.rend_fecha_rendido,'%d/%m/%Y') AS fecha_rendicion,
				 Calendario.is_facturado, ifnull(Calendario.nro_factura,'') as nro_factura, 
				 ifnull(Calendario.observacion_facturado,'') observacion_facturado,
				 Calendario.flag_rendicion,
				 CASE flag_rendicion WHEN '1' THEN 'Entregado' WHEN '2' THEN 'Rendido' WHEN '3' THEN 'Aprobado' WHEN '4' THEN 'Desaprobado' ELSE '' END AS rendicion,
				 id_estadoactividad,
				 CASE Calendario.id_asignacion_viaticos WHEN '1' THEN 'Cliente' WHEN '2' THEN 'Cuperu' WHEN '3' THEN 'Por reembolsar' ELSE '' END AS asignacion_viaticos,
				 ifnull(vista.programa,'') as programa,
				 prg_tipoactividad.descripcion as actividad
			  FROM prg_calendario AS Calendario LEFT JOIN 
				prg_auditor AS Auditor ON (Auditor.id_auditor = Calendario.id_auditor) LEFT JOIN 
				prg_proyecto AS Proyecto ON (Proyecto.project_id = Calendario.id_proyecto AND Proyecto.id_pais = Calendario.id_pais) and Proyecto.flag='1'
				left join (
					SELECT id, group_concat(pg.iniciales) as programa
					FROM prg_calendario_programa cp INNER JOIN prg_programa pg ON cp.id_programa=pg.id_programa
					WHERE pg.flag='1'
					GROUP BY id 
				) as vista on Calendario.id=vista.id left join
				prg_tipoactividad on Calendario.id_tipoactividad=prg_tipoactividad.id_tipoactividad
			  WHERE  Calendario.flag = '1'  AND Proyecto.flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_calendario_rendicion($searchQuery=null){
		$sql=" SELECT COUNT(Calendario.id) AS total 
				  FROM prg_calendario AS Calendario LEFT JOIN 
					prg_auditor AS Auditor ON (Auditor.id_auditor = Calendario.id_auditor) LEFT JOIN 
					prg_proyecto AS Proyecto ON (Proyecto.project_id = Calendario.id_proyecto AND Proyecto.id_pais = Calendario.id_pais) 
				  WHERE Calendario.flag = '1'   $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_detalle_viatico($id,$nocontingencia=null){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT distinct id_detalle,id_calendario,tipo,tipo_moneda, descripcion,monto,fecha,observacion,
				adjunto, date_format(fecha,'%d/%m/%Y') as fechaf,
				case tipo 
					when 'HOSP' then 'Hospedaje' 
					when 'MOVI' then 'Movilidad' 
					when 'ALIM' then 'Alimentaci&oacute;n' 
					when 'CONT' then 'Contingencia' 
					else '' end as tipo_dsc
			FROM prg_detalle_viaticos WHERE id_calendario=$id and flag='1'";
			 if(!empty($nocontingencia)) $sql.=" and tipo!='CONT' ";
			$sql.=" order by fecha";
			
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_one_detalle_viatico($id_calendario,$id_detalle){
		unset($this->listas);
		$sql="SELECT * , date_format(fecha,'%d/%m/%Y') as fechaf,
				case tipo 
					when 'HOSP' then 'Hospedaje' 
					when 'MOVI' then 'Movilidad' 
					when 'ALIM' then 'Alimentaci&oacute;n' 
					when 'CONT' then 'Contingencia' 
					else '' end as tipo_dsc
			FROM prg_detalle_viaticos WHERE id_calendario=$id_calendario ";
		if(!empty($id_detalle))
			$sql.=" and id_detalle=$id_detalle";
		$consulta=$this->db->consultarOne($sql);		
		return $consulta;
	}
	
	
	// total de registros por auditor fecha
	public function update_detalle_viatico($id_detalle,$id_calendario,$tipo,$moneda,$descripcion,$monto,$fecha,$observacion,$usuario,$id_pais,$ip){
		$sql=" update prg_detalle_viaticos set
					tipo='$tipo',tipo_moneda='$moneda',descripcion='$descripcion',monto='$monto',fecha='$fecha',observacion='$observacion',
					usuario_modifica='$usuario',fecha_modifica=now(), ip_modifica='$ip'
				where id_detalle=$id_detalle and id_calendario=$id_calendario " ;
		$consulta=$this->db->execute($sql);
		return $consulta;	
	}
	
	public function update_detalle_viaticoFile($id_detalle,$id_calendario,$ruta){
		$sql=" update prg_detalle_viaticos set
					adjunto='$ruta'
				where id_detalle=$id_detalle and id_calendario=$id_calendario " ;
		$consulta=$this->db->execute($sql);
		return $consulta;	
	}
	
	public function insert_detalle_viatico($id_calendario,$tipo,$moneda,$descripcion,$monto,$fecha,$observacion,$usuario,$id_pais,$ip){
		$sql=" insert into prg_detalle_viaticos 	(id_calendario,tipo,tipo_moneda,descripcion,monto,fecha,observacion,usuario_ingreso,fecha_ingreso,ip_ingreso)
			values ($id_calendario,'$tipo','$moneda','$descripcion','$monto','$fecha','$observacion','$usuario',now(),'$ip')" ;
			
		$consulta=$this->db->executeIns($sql);
		return $consulta;	
	}
	
	
	public function delete_detalle_viatico($id_detalle,$id_calendario,$usuario,$id_pais,$ip){
		$sql=" update prg_detalle_viaticos set
					flag='0', usuario_modifica='$usuario',fecha_modifica=now(), ip_modifica='$ip'
				where id_detalle=$id_detalle and id_calendario=$id_calendario " ;
		$consulta=$this->db->execute($sql);
		return $consulta;	
	}

	public function regula_detalle_viatico($id_calendario){
		$sql=" UPDATE prg_calendario C INNER JOIN
				( SELECT 
					SUM(CASE WHEN tipo='HOSP' AND tipo_moneda='USD' THEN  monto END)  AS hospedaje_dolares,
					SUM(CASE WHEN tipo='MOVI' AND tipo_moneda='USD' THEN  monto END)  AS movilidad_dolares,
					SUM(CASE WHEN tipo='ALIM' AND tipo_moneda='USD' THEN  monto END)  AS alimentacion_dolares,
					SUM(CASE WHEN tipo='CONT' AND tipo_moneda='USD' THEN  monto END)  AS contingencia_dolares,
					SUM(CASE WHEN tipo='HOSP' AND tipo_moneda='S' THEN  monto END)  AS hospedaje_soles,
					SUM(CASE WHEN tipo='MOVI' AND tipo_moneda='S' THEN  monto END)  AS movilidad_soles,
					SUM(CASE WHEN tipo='ALIM' AND tipo_moneda='S' THEN  monto END)  AS alimentacion_soles,
					SUM(CASE WHEN tipo='CONT' AND tipo_moneda='S' THEN  monto END)  AS contingencia_soles,
					SUM(CASE WHEN tipo_moneda='S' THEN  monto END)  AS soles,
					SUM(CASE WHEN tipo_moneda='USD' THEN  monto END)  AS dolares,
					id_calendario
				FROM prg_detalle_viaticos
				WHERE flag='1' AND id_calendario=$id_calendario
				GROUP BY id_calendario
				) AS vista ON C.id=vista.id_calendario
			SET
				C.utilizado_hospedaje_dolares=vista.hospedaje_dolares, 
				C.utilizado_movilidad_dolares=vista.movilidad_dolares, 
				C.utilizado_alimentacion_dolares=vista.alimentacion_dolares, 
				C.utilizado_contingencia_dolares=vista.contingencia_dolares, 
				C.utizado_monto_dolares=vista.dolares, 
				C.utilizado_hospedaje_soles=vista.hospedaje_soles, 
				C.utilizado_movilidad_soles=vista.movilidad_soles, 
				C.utilizado_alimentacion_soles=vista.alimentacion_soles, 
				C.utilizado_contingencia_soles=vista.contingencia_soles, 
				C.utizado_monto_soles=vista.soles
			WHERE 	C.id=$id_calendario " ;
		$consulta=$this->db->execute($sql);
		return $consulta;	
	}
	
	public function rendir_calendario($id_calendario,$flag_rendicion,$usuario,$id_pais,$ip){
		$sql=" update prg_calendario set
					flag_rendicion='$flag_rendicion', rend_usuario_rendido='$usuario',rend_fecha_rendido=now()
				where id=$id_calendario " ;
			
		$consulta=$this->db->execute($sql);
		return $consulta;	
	}
	
	public function facturar_calendario($observacion_facturado,$cadena,$facturado,$nrofactura){
		$sql=" UPDATE prg_calendario SET    is_facturado = '$facturado',  nro_factura = '$nrofactura',  
				observacion_facturado = '$observacion_facturado' 
                WHERE id in ($cadena) " ;
				
		$consulta=$this->db->execute($sql);
		return $consulta;	
	}
	
	
	
	//********************************
	// rendicion  cerrar
	//********************************
	
	public function selec_medio_transporte($id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT * from prg_mediotransporte 
			WHERE flag='1' AND id_pais='$id_pais' order by descripcion";
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function select_calendario_cerrar($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_pais){
		unset($this->listas); // 
		$this->listas=[];
		$sql="SELECT  distinct
			c.id, c.id_auditor,asunto, 
			monto_soles,monto_dolares,
			utizado_monto_dolares, c.id_estadoactividad, ea.descripcion as estadoplani,
			p.project_id, p.proyect, CONCAT_WS(' ',a.nombre, a.apepaterno, a.apematerno) AS auditor,
			utizado_monto_soles, dia_fin, mes_fin, anio_fin, 
			DATE_FORMAT(inicio_evento,'%d/%m/%Y') AS fecha_inicio_evento, DATE_FORMAT(fin_evento,'%d/%m/%y') AS fecha_fin_evento, 
			flag_rendicion, rend_usuario_rendido, DATE_FORMAT(rend_fecha_rendido,'%d/%m/%y') AS fecha_rendicion, 
			rend_usuario_aprobado, DATE_FORMAT(rend_fecha_aprobado,'%d/%m/%y') AS fecha_aprobacion, is_facturado,
			nro_factura, observacion_facturado , 
			CASE IFNULL(is_facturado,'n') 
				WHEN 's' THEN 'Si' 
				WHEN 'i' THEN 'Interno' 
				ELSE 'No' END AS facturado,
			
			CASE IFNULL(id_asignacion_viaticos,'0') 
				WHEN '1' THEN 'NOMBRE CLIENTE' 
				WHEN '2' THEN 'CUPERU' 
				WHEN '3' THEN 'POR REEMBOLSAR' 
				ELSE '' 
			END AS viatico,			
				
			ifnull(GROUP_CONCAT(distinct programa SEPARATOR '<br>'),'') AS programas, 
			ifnull(GROUP_CONCAT(distinct programa),'') AS programas2, 
			prg_region.descripcion as region,
			case flag_rendicion 
				when 1 then 'Entregado'
				when 2 then 'Rendido'
				when 3 then 'Aprobado'
				when 4 then 'Desaprobado'
				when 5 then 'Cerrado'
				else ''
			end as estado,
			ifnull(adjunto,'') as adjunto,
			tpa.descripcion as tipoactividad,
			ifnull(med.monto,0) as monto
		 FROM prg_calendario  c inner JOIN 
			prg_auditor AS a ON a.id_auditor = c.id_auditor LEFT JOIN
			prg_proyecto AS p ON p.project_id = c.id_proyecto and p.flag='1' AND p.id_pais = '$id_pais'  LEFT JOIN 
			 prg_proyecto_programa ON c.id_proyecto= prg_proyecto_programa.project_id left join
			 prg_calendario_region on c.id=prg_calendario_region.id left join
			 prg_region on prg_calendario_region.id_region=prg_region.id_region left join
			 (select id_calendario, group_concat(adjunto) as adjunto 
				from prg_detalle_viaticos where flag='1' group by id_calendario) as vista
				on c.id=vista.id_calendario
			left join prg_tipoactividad tpa on c.id_tipoactividad=tpa.id_tipoactividad
			left join prg_estadoactividad ea on c.id_estadoactividad=ea.id_estadoactividad
			left join (SELECT sum(ifnull(monto_dolares,0) + ifnull(monto_soles,0) + ifnull(penalidad_dolares,0) + ifnull(penalidad_soles,0))  as monto, id
						FROM prg_calendario_mediotransporte 
						group by id
				) as med on c.id=med.id
			 WHERE c.flag='1' and tpa.descripcion!='FERIADO' $searchQuery 
			GROUP BY c.id ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;

		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_grupoviatico_calendario(){
		$this->listas=[];
		$sql=" SELECT 	tipo_moneda, SUM(monto) AS monto, id_calendario,tipo
			FROM 	prg_detalle_viaticos
			WHERE flag='1'
			GROUP BY tipo_moneda,id_calendario,tipo";
			
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_calendario_cerrar($searchQuery=null,$id_pais=null){
		$sql=" SELECT COUNT(c.id) AS total 
			FROM prg_calendario  c inner JOIN 
			prg_auditor AS a ON a.id_auditor = c.id_auditor LEFT JOIN
			prg_proyecto AS p ON p.project_id = c.id_proyecto  
			inner join (SELECT sum(ifnull(monto_dolares,0) + ifnull(monto_soles,0) + ifnull(penalidad_dolares,0) + ifnull(penalidad_soles,0))  as monto, id
						FROM prg_calendario_mediotransporte 
						group by id
				) as med on c.id=med.id
			 WHERE c.flag='1'  $searchQuery  " ;
			 
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function select_calendario_detalle($project_id,$fec_proyi,$fec_proyf, $flag_rendicion,$is_facturado,$monto,$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql=" SELECT 
			c.id,
			c.id_mediotransporte,
			c.monto_dolares,
			c.monto_soles,
			c.penalidad_dolares,
			c.penalidad_soles,
			c.horas_viaje
			FROM prg_calendario_mediotransporte c inner join prg_calendario p on c.id=p.id
			where p.flag='1' AND p.id_estadoactividad!=3 AND IFNULL(p.id_proyecto,'')!='' and id_pais='$id_pais'";
		
	//	if($project_id!='') $sql.=" and p.id_proyecto='$project_id' ";	
		if($fec_proyi!='') $sql.= " and to_days(inicio_evento) >= to_days('$fec_proyi')";
		if($fec_proyf!='') $sql.= " and to_days(fin_evento) <= to_days('$fec_proyf')";		
		if($flag_rendicion!='') $sql.= " and flag_rendicion='$flag_rendicion'";
		if($is_facturado!='') $sql.= " and ifnull(is_facturado,'n')='$is_facturado'";
		if($monto!='') $sql.= " and (p.monto_dolares<= $monto or p.monto_soles<=$monto)";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	/***************************************************************************************
	modulo de planificacion calendarios
	***************************************************************************************/

	public function select_calendario_datos($id_pais,$sqlquery,$noferiado=null){
		unset($this->listas);
		$this->listas=[];
		$sql=" SELECT Calendario.id, replaceCarEspecial(Calendario.asunto) AS asunto, 
				inicio_evento AS fecha_inicio, 
				fin_evento AS fecha_final,
				to_days(fin_evento) - to_days(inicio_evento) as dias,
				 Calendario.id_tipoactividad,Calendario.id_proyecto, Calendario.nro_muestra, Calendario.por_dia, 
				 Calendario.id_auditor, Calendario.id_mediotransporte, Calendario.horas_viaje, Calendario.monto_soles,
				 Calendario.monto_dolares, ifnull(Calendario.observacion,'') as observacion,
				 Calendario.id_asignacion_viaticos, Calendario.id_estadoactividad , 
				 (SELECT COUNT(*) FROM prg_calendario calParent 
				   WHERE calParent.parent =Calendario.id AND calParent.flag = 1 
				   AND calParent.id_pais='$id_pais') AS parent, 
				 (SELECT IF(calParent.causa_cuperu=1,'CUPERU',IF(calParent.causa_cliente=1,'Cliente','')) 
				  FROM prg_calendario calParent
				  WHERE calParent.parent =Calendario.id AND calParent.flag = 1 AND calParent.id_pais='$id_pais' limit 0,1) AS causa,
				 IFNULL(Auditor.color,'#efefde') AS color,
				 Auditor.colortexto,Calendario.nombre_cliente,Calendario.flag,Calendario.is_sabado,
				 concat_ws(' ',Auditor.nombre,Auditor.apepaterno) as nameauditor,
				 Calendario.is_domingo,Calendario.flag_rendicion, 
				 (SELECT COUNT(*) FROM v_deudaproyecto WHERE project_id = Calendario.id_proyecto) AS is_deuda_proyecto, 
				 (SELECT COUNT(*) FROM prg_calendario_comercial WHERE coddetalle>0 AND prg_calendario_comercial.id=Calendario.id) AS is_vinculo_comecial,
				 RelacionCalendario.descripcion, Calendario.descripcion_sin_asignar, Calendario.audit_id, Calendario.id_type ,
				 prg_tipoactividad.descripcion as tipoactividad, ifnull(e.imagen,'') as imagen
			  
			  FROM prg_calendario Calendario LEFT JOIN 
				prg_auditor Auditor ON Calendario.id_auditor = Auditor.id_auditor LEFT JOIN
				prg_relacioncalendario RelacionCalendario ON Calendario.id = RelacionCalendario.id 
				left join prg_tipoactividad on Calendario.id_tipoactividad=prg_tipoactividad.id_tipoactividad
				left join prg_estadoactividad e on Calendario.id_estadoactividad=e.id_estadoactividad
			 WHERE  Calendario.id_pais='$id_pais' $sqlquery";
			 
		if(!empty($noferiado))
				$sql.=" and Calendario.id_tipoactividad!=378 ";
			
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	
	public function selec_one_calendario($id){
		$sql=" SELECT *, 
				DATE_FORMAT(inicio_evento, '%d/%m/%Y') AS fec_inicio, 
				DATE_FORMAT(fin_evento, '%d/%m/%Y') AS fec_final,
				DATE_FORMAT(fecha_ingreso, '%d/%m/%Y %H:%i') AS fecha_ingresof,
				DATE_FORMAT(fecha_modifico, '%d/%m/%Y %H:%i') AS fecha_modificof
			FROM prg_calendario
			WHERE  id=$id " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}


	public function select_calendario_programa($id_pais,$project_id){
		unset($this->listas);
		$this->listas=[];
		$sql=" SELECT distinct pp.programa as descripcion, id_programa 
				FROM prg_proyecto_programa pp INNER JOIN prg_programa p ON  pp.programa=p.iniciales
				WHERE  pp.id_pais='$id_pais' AND p.id_pais='$id_pais' AND project_id='$project_id'
				AND flgactivo='1'
				ORDER BY 1";
			
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	public function select_calendario_id_programa($id_pais,$id){
		unset($this->listas);
		$this->listas=[];
		$sql=" SELECT DISTINCT p.descripcion, p.id_programa 
				FROM prg_programa p 
				INNER JOIN prg_calendario_programa cp ON p.id_programa=cp.id_programa
				WHERE  p.id_pais='$id_pais' AND cp.id=$id
				ORDER BY 1";
			
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	public function select_calendario_proyectoprograma($id){
		unset($this->listas);
		$this->listas=[];
		$sql=" SELECT * from prg_calendario_programa
				WHERE  id=$id";
			
		$consulta=$this->db->consultar($sql);
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	public function select_calendario_unidad($project_id){
		unset($this->listas);
		$this->listas=[];
		$sql=" SELECT TRIM(unit_ref) unit_ref, TRIM(IFNULL(relation ,unit_name)) AS unidad 
				FROM prg_processingunits WHERE project_ref='$project_id' AND flag='1'
				UNION
				SELECT TRIM(unit_ref) unit_ref,TRIM(IFNULL(relation ,unit_name)) AS unidad  
				FROM prg_productionunits WHERE project_ref='$project_id' AND flag='1'";
			
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	public function select_calendario_proyectounidad($id){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT distinct TRIM(codigo_lugar) AS codigo_lugar FROM prg_calendario_lugar WHERE id=$id order by 1";
			//echo $sql;
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	public function select_calendario_comercial($project_id,$id_pais){
		unset($this->listas);
		$this->listas=[];
		if($id_pais=='col'){
		$sql = "SELECT prg_proyecto_detalle.coddetalle AS coddetalle, 
				CONCAT_WS(' ',CAST(prg_estadoproyecto.descripcion AS CHAR CHARACTER SET utf8), t_meses.mes,anio,observacion) AS descripcion
				FROM prg_proyecto_detalle INNER JOIN prg_proyecto ON prg_proyecto_detalle.id_proyecto = prg_proyecto.id_proyecto
				INNER JOIN prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
				INNER JOIN t_meses ON prg_proyecto_detalle.mes=t_meses.id_mes
				WHERE project_id='$project_id' AND prg_proyecto.id_pais='$id_pais' ";
		}else if($id_pais=='chi' or $id_pais=='esp'){
			$sql = "SELECT prg_proyecto_detalle.coddetalle AS coddetalle, 
				CONCAT_WS(' ',CAST(prg_estadoproyecto.descripcion AS CHAR CHARACTER SET utf8), t_meses.mes,anio,dsc_programaren) AS descripcion
				FROM prg_proyecto_detalle INNER JOIN prg_proyecto ON prg_proyecto_detalle.id_proyecto = prg_proyecto.id_proyecto
				INNER JOIN prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
				INNER JOIN t_meses ON prg_proyecto_detalle.mes=t_meses.id_mes
				WHERE project_id='$project_id' AND prg_proyecto.id_pais='$id_pais' and prg_proyecto_detalle.flag='1' ";			
		}else{		
			$sql = "SELECT prg_proyecto_detalle.coddetalle AS coddetalle, 
				CONCAT_WS(' ',CAST(prg_estadoproyecto.descripcion AS CHAR CHARACTER SET utf8), t_meses.mes,anio) AS descripcion
				FROM prg_proyecto_detalle INNER JOIN prg_proyecto ON prg_proyecto_detalle.id_proyecto = prg_proyecto.id_proyecto
				INNER JOIN prg_estadoproyecto ON prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
				INNER JOIN t_meses ON prg_proyecto_detalle.mes=t_meses.id_mes
				WHERE project_id='$project_id' AND prg_proyecto.id_pais='$id_pais' and prg_proyecto_detalle.flag='1'";		
		}
		
			
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	
	public function select_calendario_proyectocomercial($id){
		unset($this->listas);
		$this->listas=[];
		$sql = "SELECT * from prg_calendario_comercial where id=$id";		
					
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	public function select_type($id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql = "SELECT id_type,nombre from prg_type where flag='1' order by nombre";		
					
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	public function select_actividadesCal($id_pais,$id_auditor,$fechaini,$fechafin){
		unset($this->listas);
		$this->listas=[];
		$sql = "SELECT 
			   tmp_actividad,tmp_subprograma,ref_programa,ref_actividad,ref_auditor,ref_proyecto,
			   DATE_FORMAT(fecha,'%d-%m-%Y') AS fecha, 
			   porcentaje, 
			   IFNULL(nota,'') AS nota,
			   IFNULL(project_id,'') AS project_id, 
			   CASE oferta WHEN 's' THEN 'SI'  WHEN 'n' THEN 'NO' END AS oferta_dsc ,
			   CASE flgfinalizo WHEN 's' THEN 'SI'  WHEN 'n' THEN 'NO' END AS finalizo_dsc
			FROM 
				prg_auditoractividad 
			WHERE fecha>='$fechaini' AND fecha<='$fechafin' AND flag='1' AND id_auditor=$id_auditor";		
					
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	public function select_mediotransporte($id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql = "SELECT id_mediotransporte,descripcion 
				FROM prg_mediotransporte WHERE id_pais='$id_pais' AND flag='1' ORDER BY descripcion";		
					
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	public function select_mediotransporte_calenda($id){
		unset($this->listas);
		$this->listas=[];
		$sql = "SELECT m.*, t.descripcion 
				FROM prg_calendario_mediotransporte m INNER JOIN prg_mediotransporte t ON m.id_mediotransporte=t.id_mediotransporte
				WHERE id=$id AND t.flag='1' ";		
					
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	
	
	public function select_auditorprograma_calendar($id_pais,$cod_programas){
		unset($this->listas);
		$this->listas=[];
		$sql = "SELECT Auditor.id_auditor,Auditor.iniciales,
			CONCAT_WS(' ',Auditor.nombre, Auditor.apepaterno,Auditor.apematerno) as nombreCompleto 
			FROM prg_auditor Auditor INNER JOIN 
			prg_auditor_programa AuditorPrograma ON Auditor.id_auditor =AuditorPrograma.id_auditor INNER JOIN 
			prg_programa Programa ON Programa.id_programa =AuditorPrograma.id_programa 
			and Programa.id_pais = '$id_pais'
			WHERE Programa.flag = 1 AND Auditor.flag =1 AND Auditor.id_pais = '$id_pais'";
		if(!empty($cod_programas)){
			$sql.= " AND AuditorPrograma.id_programa IN (".$cod_programas.") ";
		}
		$sql.= " GROUP BY Auditor.id_auditor
			order by 2";
					
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	public function select_deudaproyecto_calendar($id_pais,$project_id){
		unset($this->listas);
		
		$sql = "Select facturas from v_deuda where proyecto='$project_id'";
					
		$consulta=$this->db->consultarOne($sql);
		return $consulta;
		
	}
	
	public function select_auditorregion($id_pais,$id_auditor){
		unset($this->listas);
		$this->listas=[];
		
		$sql = "SELECT distinct Region.id_region,Region.descripcion FROM prg_auditor Auditor
			INNER JOIN prg_auditor_region AuditorRegion ON Auditor.id_auditor =AuditorRegion.id_auditor
			INNER JOIN prg_region Region ON AuditorRegion.id_region =Region.id_region 
			WHERE Region.flag = 1 AND Auditor.flag =1 AND Auditor.id_pais='$id_pais' ";
			if(!empty($id_auditor)){
				$sql.= " AND Auditor.id_auditor in ($id_auditor)";
			}
			$sql.= " order by 2";
			//echo $sql;
					
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
			return $this->listas;	
		}
		else return "";
		
       
		
	}
	
	public function select_auditorregion_calendar($id_pais,$id){
		unset($this->listas);
		
		$sql = "SELECT CalendarioRegion.id_region,
				  Region.descripcion
				FROM
				  prg_calendario_region CalendarioRegion 
				  INNER JOIN prg_region Region ON Region.id_region = CalendarioRegion.id_region 
				  where Region.id_pais = '$id_pais' and id= $id";
					
		$consulta=$this->db->consultarOne($sql);
		return $consulta;
		
	}
	
	public function insert_calendar($id_pais,$id_tipoactividad, $project_id,$nro_muestra, $por_dia, $id_auditor, $monto_dolares, $monto_soles,
	 $id_estadoactividad, $auditoria, $id_type, $observacion,$hora_inicial, $hora_final, $dia_inicio, $mes_inicio,
	 $anio_inicio, $dia_fin, $mes_fin, $anio_fin, $inicio_evento,  $fin_evento, $is_sabado, $is_domingo,
	 $hora_inicio, $hora_fin, $asunto, $id_calendario, $id_asignacion_viaticos,	 $flag_rendicion, $flghorariofijo,$usuario, $ip,
	 $invoice_number,$invoice_date,$invoice_amount,$id_comercial_executive,$id_estado_proyecto){
		
		
		$sql = "insert into prg_calendario (
			id_pais,id_tipoactividad, id_proyecto,nro_muestra, por_dia, id_auditor, 
			 monto_dolares, monto_soles, id_estadoactividad, audit_id, id_type, observacion, 
			 hora_inicial, hora_final, dia_inicio, mes_inicio, anio_inicio, dia_fin, mes_fin,
			 anio_fin, inicio_evento, fin_evento, is_sabado, is_domingo, hora_inicio, hora_fin,
			 asunto, id_calendario, id_asignacion_viaticos, flag_rendicion, flghorariofijo,usuario_ingreso,ip_ingreso,fecha_ingreso,
			 flag,invoice_number,invoice_date,invoice_amount,id_comercial_executive,id_estado_proyecto
		)
		 values ('$id_pais',$id_tipoactividad, '$project_id','$nro_muestra', '$por_dia', $id_auditor,
		 '$monto_dolares', '$monto_soles', $id_estadoactividad, '$auditoria', '$id_type', '$observacion', 
		 '$hora_inicial', '$hora_final', '$dia_inicio', '$mes_inicio', '$anio_inicio', '$dia_fin', '$mes_fin',
		 '$anio_fin', '$inicio_evento',  '$fin_evento', '$is_sabado', '$is_domingo', '$hora_inicio', '$hora_fin',
		 '$asunto', $id_calendario, '$id_asignacion_viaticos',	 '$flag_rendicion', '$flghorariofijo','$usuario','$ip',now(),'1',
		 '$invoice_number','$invoice_date','$invoice_amount','$id_comercial_executive','$id_estado_proyecto'
		 )";
		 
		$consulta=$this->db->executeIns($sql);
		return $consulta;
	}
	
	public function update_calendar_comodin($id){
		$sql = "update  prg_calendario  set fecha_modifico=now() where id=$id";
		$consulta=$this->db->execute($sql);
		return $consulta;
	}
	
	public function update_calendar($id,$id_pais,$id_tipoactividad, $project_id,$nro_muestra, $por_dia, $id_auditor, $monto_dolares, $monto_soles,
	 $id_estadoactividad, $auditoria, $id_type, $observacion,$hora_inicial, $hora_final, $dia_inicio, $mes_inicio,
	 $anio_inicio, $dia_fin, $mes_fin, $anio_fin, $inicio_evento,  $fin_evento, $is_sabado, $is_domingo,
	 $hora_inicio, $hora_fin, $asunto, $id_calendario, $id_asignacion_viaticos,	 $flag_rendicion, $flghorariofijo,$usuario, $ip,
	 $invoice_number,$invoice_date,$invoice_amount,$id_comercial_executive,$id_estado_proyecto){
		
		
		$sql = "update  prg_calendario  set
					id_tipoactividad=$id_tipoactividad, 
					id_proyecto='$project_id',
					nro_muestra='$nro_muestra', 
					por_dia='$por_dia', 
					id_auditor= $id_auditor, 
					monto_dolares='$monto_dolares', 
					monto_soles='$monto_soles', 
					id_estadoactividad=$id_estadoactividad, 
					audit_id='$auditoria', 
					id_type='$id_type', 
					observacion='$observacion', 
					hora_inicial='$hora_inicial', 
					hora_final='$hora_final', 
					dia_inicio='$dia_inicio', 
					mes_inicio='$mes_inicio', 
					anio_inicio='$anio_inicio', 
					dia_fin='$dia_fin', 
					mes_fin='$mes_fin',
					anio_fin='$anio_fin', 
					inicio_evento='$inicio_evento', 
					fin_evento= '$fin_evento', 
					is_sabado='$is_sabado', 
					is_domingo='$is_domingo', 
					hora_inicio='$hora_inicio', 
					hora_fin='$hora_fin',
					asunto='$asunto', 
					flghorariofijo='$flghorariofijo',
					id_asignacion_viaticos='$id_asignacion_viaticos', 
					invoice_number= '$invoice_number',
					invoice_date= '$invoice_date',
					invoice_amount= '$invoice_amount',
					id_comercial_executive= '$id_comercial_executive',
					id_estado_proyecto= '$id_estado_proyecto',
					usuario_modifico='$usuario',
					ip_modifico='$ip',
					fecha_modifico=now()
				 where id=$id and id_pais='$id_pais' ";
				 
		$consulta=$this->db->execute($sql);
		return $consulta;
	}
	
	
	public function proceso_calendareUpdateMove($id,$id_pais,$hora_inicial, $hora_final, $dia_inicio, $mes_inicio,
	 $anio_inicio, $dia_fin, $mes_fin, $anio_fin, $inicio_evento,  $fin_evento, $hora_inicio, $hora_fin, $usuario, $ip){
		
		
		$sql = "update  prg_calendario  set
					hora_inicial='$hora_inicial', 
					hora_final='$hora_final', 
					dia_inicio='$dia_inicio', 
					mes_inicio='$mes_inicio', 
					anio_inicio='$anio_inicio', 
					dia_fin='$dia_fin', 
					mes_fin='$mes_fin',
					anio_fin='$anio_fin', 
					inicio_evento='$inicio_evento', 
					fin_evento= '$fin_evento', 
					hora_inicio='$hora_inicio', 
					hora_fin='$hora_fin'
				 where id=$id and id_pais='$id_pais' ";
				 
		$consulta=$this->db->execute($sql);
		return $consulta;
	}
	
	public function delete_calendarprograma($id){
		$sql = "DELETE FROM prg_calendario_programa WHERE id = $id";
		$consulta=$this->db->execute($sql);
		return $consulta;
	}
	
	public function insert_calendarprograma($id_programa,$id){
		$sql = "INSERT INTO prg_calendario_programa(id_programa,id) VALUES ('$id_programa',$id)";
		$consulta=$this->db->executeIns($sql);
		return $consulta;
	}
	
	public function delete_calendarregion($id){
		$sql = "DELETE FROM prg_calendario_region WHERE id = $id";
		$consulta=$this->db->execute($sql);
		return $consulta;
	}
	
	public function insert_calendarregion($id_region,$id){
		$sql = "INSERT INTO prg_calendario_region(id_region,id) VALUES ('$id_region',$id)";
		$consulta=$this->db->executeIns($sql);
		return $consulta;
	}
	
	public function delete_calendarunidad($id){
		$sql = "DELETE FROM prg_calendario_lugar WHERE id = $id";
		$consulta=$this->db->execute($sql);
		return $consulta;
	}
	
	public function insert_calendarunidad($id_unidad,$id){
		$sql = "INSERT INTO prg_calendario_lugar(codigo_lugar,id) VALUES ('$id_unidad',$id)";
		$consulta=$this->db->executeIns($sql);
		return $consulta;
	}
	
	public function delete_calendarcomercial($id){
		$sql = "DELETE FROM prg_calendario_comercial WHERE id = $id";
		$consulta=$this->db->execute($sql);
		return $consulta;
	}
	
	public function insert_calendarcomercial($id_proyectocomercial,$id){
		$sql = "INSERT INTO prg_calendario_comercial(coddetalle,id) VALUES ('$id_proyectocomercial',$id)";
		$consulta=$this->db->executeIns($sql);
		return $consulta;
	}
	
	public function delete_calendartransporte($id){
		$sql = "DELETE FROM prg_calendario_mediotransporte WHERE id = $id";
		
		$consulta=$this->db->execute($sql);
		return $consulta;
	}
	
	public function insert_calendartransporte($id,$idmedio,$usd,$mn,$penausdv,$penamn,$horavia){
		
		$usd = !empty($usd) ? "'$usd'" : "NULL";
		$mn = !empty($mn) ? "'$mn'" : "NULL";
		$penausdv = !empty($penausdv) ? "'$penausdv'" : "NULL";
		$penamn = !empty($penamn) ? "'$penamn'" : "NULL";
		$horavia = !empty($horavia) ? "'$horavia'" : "NULL";
		
		$sql = "INSERT INTO prg_calendario_mediotransporte (id,id_mediotransporte,monto_dolares,monto_soles,
				penalidad_dolares,penalidad_soles,horas_viaje)
				VALUES ($id,$idmedio,$usd,$mn,$penausdv,$penamn,$horavia)";
		$consulta=$this->db->executeIns($sql);
		return $consulta;
		
	}
	
	// reprogramar actividad
	
	public function insert_calendarreprograma($id,$hora_inicial,$hora_inicio,$dia_inicio,$mes_inicio,$anio_inicio,$hora_final,
	$hora_fin,$dia_fin,$mes_fin,$anio_fin,$usuario,$ip,$causa_cuperu,$causa_cliente,$ini_evento,$fin_evento,$is_sabado,$is_domingo){
		
	
		$sql = " INSERT INTO prg_calendario 
				(id_tipoactividad, id_proyecto, id_auditor, asunto, id_asignacion_viaticos, nro_muestra, 
				por_dia, id_mediotransporte, horas_viaje, monto_soles, monto_dolares, 
				id_estadoactividad, observacion, hora_inicial, hora_inicio, dia_inicio, 
				mes_inicio, anio_inicio, hora_final, hora_fin, dia_fin, mes_fin, anio_fin, flag, 
				parent, usuario_ingreso, fecha_ingreso, ip_ingreso,causa_cuperu,causa_cliente,inicio_evento,
				fin_evento,nombre_cliente,id_pais, is_sabado, is_domingo)
			SELECT id_tipoactividad, id_proyecto, id_auditor, asunto, id_asignacion_viaticos, nro_muestra, 
				por_dia, id_mediotransporte, horas_viaje, monto_soles, monto_dolares, 
				'1', observacion, '$hora_inicial', '$hora_inicio' ,'$dia_inicio' , 
				'$mes_inicio', '$anio_inicio','$hora_final' ,'$hora_fin' ,'$dia_fin' ,'$mes_fin' , '$anio_fin' ,'1', 
				id, '$usuario', now(), '$ip','$causa_cuperu','$causa_cliente' ,'$ini_evento' ,'$fin_evento',
				nombre_cliente,id_pais,'$is_sabado', '$is_domingo'					
				FROM 	prg_calendario WHERE id = ".$id;
		$consulta=$this->db->executeIns($sql);
		return $consulta;
		
	}
	
	public function proceso_calendarreprograma($eventID,$id){
		
		$sql="INSERT INTO prg_calendario_programa(id_programa,id) 
			SELECT  id_programa, $id FROM prg_calendario_programa  WHERE id = $eventID";
		$consulta=$this->db->executeIns($sql);

		$sql="INSERT INTO prg_calendario_lugar(codigo_lugar,id) 
			SELECT codigo_lugar,$id FROM prg_calendario_lugar WHERE id = $eventID";
		$consulta=$this->db->executeIns($sql);

		$sql = " INSERT INTO prg_calendario_mediotransporte(id,id_mediotransporte,monto_dolares,monto_soles,penalidad_dolares,penalidad_soles,horas_viaje) ";
		$sql.= " SELECT $id,id_mediotransporte, monto_dolares,monto_soles,penalidad_dolares,penalidad_soles,horas_viaje 
				 FROM prg_calendario_mediotransporte WHERE id = $eventID";
		$consulta=$this->db->executeIns($sql);

		$sql = " INSERT INTO prg_calendario_region(id,id_region) 
				select $id , id_region from prg_calendario_region where id=$eventID";
		$consulta=$this->db->executeIns($sql);
		
		$sql = " INSERT INTO prg_calendario_comercial(coddetalle,id) 
				select coddetalle,$id from prg_calendario_comercial where id=$eventID";
		$consulta=$this->db->executeIns($sql);
		
		$sql = " UPDATE prg_calendario SET  id_estadoactividad = '2' WHERE id =$eventID";
		$consulta=$this->db->execute($sql);
		
		return $consulta;
	}
	
	
	public function proceso_calendareliminar($id,$usuario,$ip){
		
		$sql = "UPDATE prg_calendario SET   flag = '0', usuario_elimino = '$usuario', fecha_elimino = now(), 
				ip_elimino = '$ip' WHERE id =$id ";
		$consulta=$this->db->execute($sql);
		
		return $consulta;
	}
	
	public function proceso_calendarreactivar($id,$usuario,$ip){
		$sql = "UPDATE prg_calendario SET   flag = '1' , usuario_reactivado = '$usuario', fecha_reactivado = now(), 
				ip_reactivado = '$ip' WHERE id =$id";
		$consulta=$this->db->execute($sql);
		
		return $consulta;
	}
	
	
	public function select_factura_calendar($id_pais,$id){
		unset($this->listas);
		$sql = "SELECT * , date_format(invoice_date,'%d/%m/%Y') as invoice_date2
			FROM prg_comercial_factura
			WHERE flag = 1 AND id='$id' 
			order by id_factura ";
			//echo $sql;
					
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
			return $this->listas;	
		}
		else return "";
	}
	
	public function update_calendarfactura($id,$id_factura,$invoice_number,$invoice_date,$remark_invoice,$invoice_amount,$id_comercial_executive,$id_estado_proyecto,$usuario,$ip){
		$sql = "UPDATE prg_comercial_factura
				set  
					invoice_number='$invoice_number',
					invoice_date='$invoice_date',
					remark_invoice='$remark_invoice',
					invoice_amount='$invoice_amount',
					id_comercial_executive='$id_comercial_executive',
					id_estado_proyecto='$id_estado_proyecto'
				WHERE id_factura =$id_factura";
				
		$consulta=$this->db->execute($sql);
		
		return $consulta;
	}
	
	public function insert_calendarfactura($id,$invoice_number,$invoice_date,$remark_invoice,$invoice_amount,$id_comercial_executive,$id_estado_proyecto,$usuario,$ip){
		$sql = "insert into prg_comercial_factura (id,invoice_number,invoice_date,remark_invoice,invoice_amount,id_comercial_executive,id_estado_proyecto)
				values( $id,'$invoice_number','$invoice_date','$remark_invoice','$invoice_amount','$id_comercial_executive','$id_estado_proyecto')";
				
		$consulta=$this->db->execute($sql);
		
		return $consulta;
	}
	
	// public function insert_calendario_log($accion,$id,$ip,$usuario){
	// 	$sql = "insert into prg_calendario_log(accion,id,ip,usuario,fecha)
	// 			values( '$accion',$id,'$ip','$usuario',now())";
				
	// 	$consulta=$this->db->execute($sql);
		
	// 	return $consulta;
	// } CAMBIADO POR AMENA SE PUSO ESTO DE ABAJO 

	// public function insert_calendario_log($accion, $id, $ip, $usuario, $id_estadoactividad) {
	// 	$sql = "INSERT INTO prg_calendario_log (accion, id, ip, usuario, fecha, id_estadoactividad, estado_actividad_log)
	// 			VALUES ('$accion', $id, '$ip', '$usuario', NOW(), $id_estadoactividad,
	// 			(SELECT descripcion FROM prg_estadoactividad WHERE id_estadoactividad = $id_estadoactividad))";
	// 	return $this->db->execute($sql);
	// }

	// public function insert_calendario_log($accion, $id, $ip, $usuario, $id_estadoactividad) {
	// 	$sql = "INSERT INTO prg_calendario_log (accion, id, ip, usuario, fecha, id_estadoactividad)
	// 			VALUES ('$accion', $id, '$ip', '$usuario', NOW(), $id_estadoactividad)";
	
	// 	$consulta = $this->db->execute($sql);
	// 	return $consulta;
	// }
	
	public function insert_calendario_log($accion, $id, $ip, $usuario, $id_estadoactividad) {
		// Obtener el nombre del estado desde la tabla de estados
		// $sql_estado = "SELECT descripcion FROM estado_actividad WHERE id_estadoactividad = $id_estadoactividad";
		$sql_estado = "SELECT descripcion FROM prg_estadoactividad WHERE id_estadoactividad = $id_estadoactividad";

		$res_estado = $this->db->consultarOne($sql_estado);
		$estado_actividad_log = (!empty($res_estado)) ? $res_estado['descripcion'] : '';
	
		// Insertar en el log
		$sql = "INSERT INTO prg_calendario_log (
					accion, id, ip, usuario, fecha, id_estadoactividad, estado_actividad_log
				) VALUES (
					'$accion', $id, '$ip', '$usuario', NOW(), $id_estadoactividad, '$estado_actividad_log'
				)";
		
		return $this->db->execute($sql);
	}
	
	
	
	public function select_calendario_log($id){
		unset($this->listas);
		$sql = "SELECT usuario,DATE_FORMAT(fecha,'%d/%m/%Y %H:%i') AS fechaf,accion,ip, estado_actividad_log
				FROM prg_calendario_log WHERE id=$id
				ORDER BY fecha ASC";

					
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
			return $this->listas;	
		}
		else return "";
	}
	
}
?>