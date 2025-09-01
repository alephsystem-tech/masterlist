<?php
class lst_listaintegrada_model{
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

	public function select_listaintegrada($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		$this->listas=null;
			$sql="SELECT 
					case caso 
						when 'N' then 'Lista inicial'
						when 'A' then 'Actualizaci&oacute;n'
						when 'R' then 'Renovaci&oacute;n'
						when 'E' then 'Extensi&oacute;n'
						else ''
					end casodsc,
					case c.flgestado 
						when '1' then 'SI'
						else 'NO'
					end flgestadodsc,					
					c.codlista,c.codproyecto,c.proyecto,
					date_format(fechainicio,'%d/%m/%Y') as fecha_f, 
					date_format(fechatermino,'%d/%m/%Y') as fechafin_f, 
					count(distinct d.cedula) as total,
					dat.rutafile,
					ifnull(count(s.codlista),0) as totsolicitud,
					CONCAT_WS('&&',DATE_FORMAT(dat.fecha,'%d/%m/%Y'),dat.rutafile) AS datosfecha,
					c.usuario_ingreso,
					GROUP_CONCAT(DISTINCT cultivo) AS cultivo,
					ROUND(avg(IFNULL(rdtoalta,0)+ IFNULL(rdtobaja,0)),2) AS rendimiento,
					ROUND(SUM(area_cultivo),2) AS hascultivo,
					ROUND(SUM(area_total),2) AS hastotales,
					vista.categoriadsc as tipo,
					c.tipo as tipo_lista,
					c.flgestado,
					'' as referencia,
					ifnull(tole.tolerancia,'') as tolerancia,
					p.country AS pais			
				
				FROM lst_listaintegrada c left JOIN 
					lst_listaintegrada_det d ON c.codlista=d.codlista left join
					lst_solicitud s on c.codlista=s.codlista  AND c.id_pais=s.id_pais  and s.flag='1' left join
					lst_listaintegrada_file dat ON c.codlista=dat.codlista left join
					(SELECT p.project_id, GROUP_CONCAT(distinct categoria) AS categoriadsc
						FROM prg_proyecto_categoria p INNER JOIN prg_categoria_proy c ON p.codcategoria=c.codcategoria
						where c.flag='1'
						GROUP BY p.project_id) as vista on c.codproyecto=vista.project_id
					LEFT JOIN (
						SELECT GROUP_CONCAT(CONCAT(cultivo,' ',tolerancia,' %')) AS tolerancia, codlista
						FROM lst_listadetxtolerancia tol INNER JOIN lst_cultivo c ON tol.codcultivo=c.codcultivo  
						WHERE tol.flag='1'
						GROUP BY codlista
					) AS tole ON c.codlista=tole.codlista	
					INNER JOIN prg_proyecto p ON c.codproyecto=p.project_id and c.id_pais=p.id_pais
				WHERE c.flag='1'  $searchQuery 
					group by c.codlista";
    
		$sql.=" order by dat.fecha DESC limit ".$row.",".$rowperpage ;
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

	
	public function select_listaintegrada_cultivototalmayor5($searchQuery){
		$this->listas=null;
			$sql="SELECT
			DISTINCT (ldet.cedula) AS cedula_mayor_5,
			ldet.codlista
			FROM lst_listaintegrada_det ldet 
			WHERE $searchQuery  AND ldet.area_total > 5
			GROUP BY cedula_mayor_5,codlista
			HAVING SUM(ldet.area_total) > 5";
    
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

	
	public function select_listaintegrada_cultivomayor5($searchQuery){
		$this->listas=null;
			$sql="SELECT
			DISTINCT (ldet.cedula) AS cedula_mayor_5,
			ldet.codlista
			FROM lst_listaintegrada_det ldet
			WHERE $searchQuery  AND ldet.area_total > 5
			GROUP BY cedula_mayor_5,codlista
			HAVING SUM(ldet.area_cultivo_tot) > 5";
    
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_productor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		$this->listas=null;
		
			$sql="SELECT 
					c.codlista,c.codproyecto,c.proyecto,
					s.cultivo,d.cedula, d.codagricultor, CONCAT_WS(' ',d.nombres,d.apellidos) AS agricultor,
					d.codcampo,
					codunidad AS unidad, area_total, area_cultivo, 
					IFNULL(rdtoalta,0) + IFNULL(rdtobaja,0) AS rendimiento,
					DATE_FORMAT(fechainicio,'%d/%m/%Y') AS fechainiciof,
					DATE_FORMAT(fechatermino,'%d/%m/%Y') AS fechaterminof,
					p.nombre AS pais,
					CASE c.flgestado WHEN '1' THEN 'SI' ELSE 'NO' END AS activo,
					c.usuario_ingreso,
					CASE WHEN vista.total>1 THEN 'SI' ELSE 'NO' END AS masproyecto

				FROM lst_listaintegrada c INNER JOIN 
					prg_paises p ON c.id_pais=p.id_pais INNER JOIN
					lst_listaintegrada_det d ON c.codlista=d.codlista LEFT JOIN
					lst_cultivo s ON d.codcultivo=s.codcultivo inner join
					(
						select count(*) as total, cedula
						from lst_listaintegrada  INNER JOIN 
							lst_listaintegrada_det  ON lst_listaintegrada.codlista=lst_listaintegrada_det.codlista
						where 	 lst_listaintegrada.flag='1'  and lst_listaintegrada.flgestado='1'
						group by cedula
						-- having total>1
					) as vista on d.cedula=vista.cedula
				WHERE c.flag='1'  $searchQuery ";
    
		$sql.=" order by d.cedula DESC limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_total_productor($searchQuery=null){
		$sql=" SELECT COUNT(distinct d.coddetalle) AS total 
				FROM lst_listaintegrada c INNER JOIN 
					prg_paises p ON c.id_pais=p.id_pais INNER JOIN
					lst_listaintegrada_det d ON c.codlista=d.codlista LEFT JOIN
					lst_cultivo s ON d.codcultivo=s.codcultivo inner join
					(
						select count(*) as total, cedula
						from lst_listaintegrada  INNER JOIN 
							lst_listaintegrada_det  ON lst_listaintegrada.codlista=lst_listaintegrada_det.codlista
						where 	 lst_listaintegrada.flag='1'  and lst_listaintegrada.flgestado='1'
						group by cedula
						having total>1
					) as vista on d.cedula=vista.cedula
				WHERE c.flag='1'  $searchQuery " ;
					
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function select_cultivos($codlista){
			$this->listas=null;
			$sql="SELECT DISTINCT c.* , IFNULL(t.tolerancia,'') AS tolerancia
				FROM lst_cultivo c INNER JOIN lst_listaintegrada_det d ON c.codcultivo=d.codcultivo
					LEFT JOIN lst_listadetxtolerancia t ON c.codcultivo=t.codcultivo AND t.codlista=$codlista
				WHERE d.codlista=$codlista";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	// total de registros por auditor fecha
	public function selec_total_listaintegrada($searchQuery=null){
		$sql=" SELECT COUNT(distinct c.codlista) AS total 
			FROM lst_listaintegrada c INNER JOIN 
					lst_listaintegrada_det d ON c.codlista=d.codlista left join
					lst_solicitud s on c.codlista=s.codlista  and s.flag='1' left join
					(select codlista, group_concat(distinct concat_ws('&&',date_format(fecha,'%d/%m/%Y'),rutafile)) as filefecha 
						from lst_listaintegrada_file 
						
						group by codlista
						) as data on c.codlista=data.codlista
				WHERE c.flag='1' $searchQuery " ;
					
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	
	
	
	public function selec_one_listaintegrada($codlista){
		
		$sql="SELECT * from lst_listaintegrada where codlista=$codlista ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_listaintegrada($proyecto,$codproyecto,$fechainicio,$fechatermino,$id_pais,$usuario,$ip){

        $sql="insert into lst_listaintegrada (proyecto,codproyecto,fechainicio,fechatermino,id_pais, 
			flag,usuario_ingreso,fecha_ingreso,ip_ingreso, usuario_modifica,fecha_modifica,ip_modifica)
        values('$proyecto','$codproyecto','$fechainicio','$fechatermino','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_listaintegrada($codlista,$proyecto,$codproyecto,$fechainicio,$fechatermino,$id_pais,$usuario,$ip){
	   
        $sql="update lst_listaintegrada 
				set proyecto='$proyecto',
				codproyecto='$codproyecto',fechainicio='$fechainicio',fechatermino='$fechatermino',
				usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codlista=$codlista and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_listaintegrada($codlista){
	   
        $sql="update lst_listaintegrada set flag='0' where codlista=$codlista";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

	
    public function update_estadolistaintegrada($codlista,$flgestado){
	   
        $sql="update lst_listaintegrada set flgestado='$flgestado' where codlista=$codlista";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	


	public function select_configura($codlista,$codcultivo){
			$this->listas=null;
			$sql="select * from lst_configura where codlista=$codlista and codcultivo=$codcultivo";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_mesconfigura(){
			$this->listas=null;
			$sql="SELECT id_mes,substring(mes,1,3) as mes  FROM t_meses	order by id_mes";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_unidad($codlista){
			$this->listas=null;
			$sql="SELECT id,unidad  FROM lst_unidad where codlista=$codlista and flag='1' order by unidad";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function delete_configuralistaintegrada($codlista,$codcultivo){
        $sql="delete from lst_configura where codlista=$codlista and codcultivo=$codcultivo";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function delete_configuralistatolerancia($codlista,$codcultivo){
        $sql="delete from lst_listadetxtolerancia where codlista=$codlista ";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function select_unidadxMes($codlista){
			$this->listas=null;
			$sql="SELECT id,id_mes,unidad  FROM lst_unidad INNER JOIN t_meses 
			WHERE codlista=$codlista AND lst_unidad.flag='1'";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	 public function insert_configura($id_mes,$codcultivo,$idunidad,$codlista,$project_id,$valor){
        $sql="insert lst_configura(id_mes,codcultivo,codunidad,codlista,project_id,valor)
				values ($id_mes,$codcultivo,$idunidad,$codlista,$project_id,$valor) ";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }
	
	 public function insert_configuraTolerancia($codcultivo,$codlista,$project_id,$valor,$ip){
        $sql="insert lst_listadetxtolerancia(codcultivo,codlista,tolerancia,usuario_ingreso,fecha_ingreso,ip_ingreso)
				values ($codcultivo,$codlista,$valor,'$project_id',now(),'$ip') ";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }
	
	// importar excel
	
	 public function insert_predataCab_excel($data_part){
		//$sql="truncate  lst_cabecera_tmp";
		//$consulta=$this->db->execute($sql);
		
		$sql="insert into lst_cabecera_tmp values $data_part ";
		
		$consulta=$this->db->executeIns($sql);
		
		return $consulta;
	 }	
	
	 public function insert_predataDet_excel($data_sql){
		//$sql="truncate  lst_importar_log";
		//$consulta=$this->db->execute($sql);
		
        $sql="insert into lst_importar_log(
							codunico,
							codproyecto,
							proyecto,
							codunidad,
							fila,
							id,
							codagricultor,
							apellidos,
							nombres,
							cedula,
							codcampo,
							finca,
							ubicacion,
							gps,
							fechaprim_aplica,
							fechault_aplica,
							quimico,
							area_total,
							area_cultivo,
							sistema_cultivo,
							nroplanta_ha,
							rdto_planta,
							rdto,
							rdto_promedio,
							cultivo,
							variedad,
							inicio,
							inicio_org,
							fecha_siembra,
							epoca_cosecha,
							eu_campo,
							eu_cosecha,
							usda_campo,
							usda_cosecha,
							rpto_campo,
							rpto_cosecha,
							nrovisita,
							ultima_visita,
							status_hasta,
							rdtoalta,
							rdtobaja,
							racimoalta,
							racimobaja,
							ratiopromedioalta,
							ratiopromediobaja,
							rdtocaja,
							rdtoultima,
							racimoultima,
							ratioultima,
							rdtocajareal,							
							stock,
							tipo
							
						) values $data_sql ";
		
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }


	public function insert_predataDet_excel_nobanano($data_sql){
		//$sql="truncate  lst_importar_log";
		//$consulta=$this->db->execute($sql);
		
        $sql="insert into lst_importar_log(
							codunico,
							codproyecto,
							proyecto,
							codunidad,
							fila,
							id,
							codagricultor,
							apellidos,
							nombres,
							cedula,
							codcampo,
							finca,
							ubicacion,
							gps,
							fechaprim_aplica,
							fechault_aplica,
							quimico,
							area_total,
							area_cultivo,
								
							nroplanta_ha,
								
							rdto,
								
							cultivo,
							variedad,
							inicio,
							inicio_org,
							fecha_siembra,
							epoca_cosecha,
							eu_campo,
							eu_cosecha,
							usda_campo,
							usda_cosecha,
							rpto_campo,
							rpto_cosecha,
							nrovisita,
							ultima_visita,
							status_hasta,
							rdtoalta,
							
							rdtoultima,
													
							stock,
							tipo
							
						) values $data_sql ";
		
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }
	
	
	public function insert_predataSic_excel($data_sql){
		//$sql="truncate  lst_importar_logsic";
		//$consulta=$this->db->execute($sql);
		
        $sql="insert into lst_importar_logsic values $data_sql ";
		
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }
	
	// mostrar los que no cumple con visita en un anio
	public function select_predataSic_fecha($codunico){
		$this->listas=null;
		$sql="SELECT agricultor,codcampo,DATE_FORMAT(fechaauditoria,'%d/%m/%Y') fechaauditoria,
					DATE_FORMAT(fechaanterior,'%d/%m/%Y') AS fechaanterior, TO_DAYS(fechaauditoria)-TO_DAYS(fechaanterior) AS dias
				FROM lst_importar_logsic 
				WHERE codunico='$codunico' and  TO_DAYS(fechaauditoria)-TO_DAYS(fechaanterior) >365";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
    }

	public function select_listaxproyecto($project_id,$flgestado=null,$cultivo=''){
		$this->listas=null;
		$sql="SELECT fecha, REPLACE(d.rutafile,'uploads/listasintegradas/','') as ruta, d.codlista ,
			p.flag, p.tipo, date_format(p.fechainicio,'%d/%m/%Y') as fechaf
			FROM lst_listaintegrada p INNER JOIN lst_listaintegrada_file d ON p.codlista=d.codlista
			WHERE p.flag='1' and codproyecto='$project_id' ";
		if($cultivo!='') $sql.=" and tipo='$cultivo' ";
		if(!empty($flgestado))	$sql.=" and p.flgestado='$flgestado'";
		$sql.="	ORDER BY 1 DESC";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}


	public function select_cabecera($cultivo){
		$this->listas=null;
		$sql="SELECT * from lst_cabecera where tipo='$cultivo'";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_cabecera_sli($cultivo){
		$this->listas=null;
		$sql="SELECT * from lst_cabecera_sli where tipo='$cultivo'";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_cabecera_tmp($codunico){
		$this->listas=null;
		$sql="SELECT * from lst_cabecera_tmp where codunico='$codunico'";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_data_log($cultivo,$codunico){
		$this->listas=null;
		if($cultivo=='BANANO')
			$sql="SELECT * from lst_importar_log where codunico='$codunico'";
		else
			$sql="SELECT codproyecto, proyecto, codunidad,id, codagricultor, apellidos, nombres, 
				cedula, codcampo, finca, ubicacion, gps, 
				fechaprim_aplica, fechault_aplica, 
				quimico, area_total, area_cultivo, nroplanta_ha, rdto, 
				 cultivo, variedad, inicio, inicio_org, fecha_siembra, epoca_cosecha, 
				 eu_campo, eu_cosecha, usda_campo, usda_cosecha, rpto_campo, rpto_cosecha, 
				nrovisita, ultima_visita, status_hasta, rdtoalta, rdtoultima, stock ,ident, fila
			from lst_importar_log
			where codunico='$codunico' ";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}


	public function select_data_log_duplica($codunico){
		$this->listas=null;
		$sql="SELECT codcampo, 
				COUNT(DISTINCT CONCAT_WS(' ',nombres,apellidos))  AS total, 
				GROUP_CONCAT(DISTINCT CONCAT(nombres,' ',apellidos,' ubicado en fila ', fila, ' unidad ', codunidad)) AS nombre
			FROM lst_importar_log
			where codunico='$codunico'
			GROUP BY codcampo
			HAVING total>1";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_data_cedula_duplica($codunico){
		$this->listas=null;
		$sql="SELECT cedula, 
				COUNT(DISTINCT CONCAT_WS(' ',nombres,apellidos))  AS total, 
				GROUP_CONCAT(DISTINCT CONCAT(nombres,' ',apellidos,' ubicado en fila ', fila, ' unidad ', codunidad)) AS nombre
			FROM lst_importar_log
			where codunico='$codunico'
			GROUP BY cedula
			HAVING total>1";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	public function select_data_agricu_duplica($codunico){
		$this->listas=null;
		$sql="SELECT codagricultor, 
				COUNT(DISTINCT CONCAT_WS(' ',nombres,apellidos))  AS total, 
				GROUP_CONCAT(DISTINCT CONCAT(nombres,' ',apellidos,' ubicado en fila ', fila, ' unidad ', codunidad))  AS nombre
			FROM lst_importar_log
			where codunico='$codunico'
			GROUP BY codagricultor
			HAVING total>1";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_data_agricu_duplicaCodigo($codunico){
		$this->listas=null;
		$sql="SELECT CONCAT_WS(' ',nombres,apellidos) AS nombre , 
				COUNT(DISTINCT codagricultor)  AS total,
				GROUP_CONCAT(DISTINCT CONCAT(codagricultor,' ubicado en fila ', fila, ' unidad ', codunidad)) AS codagricultor
			FROM lst_importar_log
			where codunico='$codunico'
			GROUP BY nombre
			HAVING total>1";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_data_campo_duplicaCodigo($codunico){
		$this->listas=null;
		$sql="SELECT CONCAT_WS(' ',codcampo,finca) AS nombre , 
			codcampo, finca,
			COUNT(ident)  AS total,
			GROUP_CONCAT(DISTINCT CONCAT(' ubicado en unidad ', codunidad, '  en fila ', fila, ' unidad ', codunidad)) AS donde
		FROM lst_importar_log
		WHERE codunico='$codunico'
		GROUP BY CONCAT_WS(' ',codcampo,finca)
		HAVING total>1";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_data_agricu_duplicaCedula($codunico){
		$this->listas=null;
		$sql="SELECT CONCAT_WS(' ',nombres,apellidos) AS nombre , 
				COUNT(DISTINCT cedula)  AS total,
				GROUP_CONCAT(DISTINCT CONCAT('cedula ',cedula,' ubicado en fila ', fila, ' unidad ', codunidad)) AS cedula
			FROM lst_importar_log
			where codunico='$codunico'
			GROUP BY nombre
			HAVING total>1";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_data_gps_duplica($codunico){
		$this->listas=null;
		$sql="SELECT gps, COUNT(*) AS total, COUNT(DISTINCT cedula) AS cantcedula,
				GROUP_CONCAT((ident+10)) AS fila,
				codunidad, 
				GROUP_CONCAT(CONCAT('campo ',codcampo,' ubicado en fila ', fila, ' unidad ', codunidad)) campo
				
			FROM lst_importar_log
			where codunico='$codunico'
			GROUP BY gps,codunidad
			HAVING total>1 AND cantcedula>1
			ORDER BY codunidad";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

	public function select_data_agricultores($codlista,$codunico){
		$this->listas=null;
		$sql="SELECT l.*, 
				concat_ws(' ',d.nombres,d.apellidos) as nombrel_original,
				concat_ws(' ',l.nombres,l.apellidos) as nombrel_actual,
				ROUND((l.nroplanta_ha*l.area_cultivo*l.rdto_planta),2) AS produccion ,
				l.area_total as area_totaln,
				d.area_total ,
				l.area_cultivo area_cultivon,
				d.area_cultivo , 
				l.nroplanta_ha nroplanta_han,
				d.nroplanta_ha , 
				l.rdto_promedio rdto_plantan,
				d.rdto_promedio rdto_planta, 
				d.rdto as rdto_n,
				d.cultivo as cultivo_ori,
				d.codunidad as codunidad_ori,
				d.codcampo as codcampo_ori,
				d.cedula as cedula_ori
			FROM lst_importar_log l INNER JOIN lst_listaintegrada_det  d ON l.codcampo=d.codcampo and l.finca=d.finca
			WHERE l.codunico='$codunico' and d.codlista=$codlista  AND (
			 l.codcampo<>d.codcampo or  l.area_total<>d.area_total OR l.area_cultivo<>d.area_cultivo OR l.nombres<>d.nombres  or
			 l.apellidos<>d.apellidos 
			 or l.rdto_promedio<>d.rdto_promedio
			) ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}


	public function select_data_status($codlista,$codunico){
		$this->listas=null;
		$sql="SELECT 
			l.codcampo, l.codunidad, d.codunidad codunidadn,
			CONCAT_WS(' ',d.nombres,d.apellidos) AS nombre_original,
			CONCAT_WS(' ',l.nombres,l.apellidos) AS nombre_actual,
			l.eu_campo,d.eu_campo eu_campon , l.eu_cosecha,d.eu_cosecha eu_cosechan , l.usda_campo,d.usda_campo usda_campon,
			l.usda_cosecha,d.usda_cosecha  usda_cosechan, l.rpto_campo,d.rpto_campo rpto_campon, l.rpto_cosecha,d.rpto_cosecha rpto_cosechan
		 
		FROM lst_importar_log l INNER JOIN lst_listaintegrada_det  d ON l.codcampo=d.codcampo AND l.finca=d.finca
		WHERE l.codunico='$codunico' AND d.codlista=$codlista AND (
		 l.eu_campo<>d.eu_campo OR  l.eu_cosecha<>d.eu_cosecha OR l.usda_campo<>d.usda_campo OR l.usda_cosecha<>d.usda_cosecha  OR
		 l.rpto_campo<>d.rpto_campo OR l.rpto_cosecha<>d.rpto_cosecha
		) ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}

	public function select_data_agricultores_news($codlista,$codunico){
		$this->listas=null;
		$sql="SELECT distinct codunidad,codcampo,nombres,apellidos,area_cultivo,area_total,codagricultor, (
				rdtoalta+rdtobaja) as produccion 
				FROM lst_importar_log 
				WHERE codunico='$codunico' and cedula NOT IN (SELECT cedula FROM lst_listaintegrada_det WHERE codlista=$codlista)";
	
		$consulta=$this->db->consultar($sql);	
		if(!empty($consulta)){		
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}	
        return $this->listas;	
		
	}
	
	public function select_data_campo_news($codlista,$codunico){
		$this->listas=null;
		$sql="SELECT distinct codunidad,codcampo,nombres,apellidos,area_cultivo, 
				(IFNULL(rdtoalta,0)+ IFNULL(rdtobaja,0)) as produccion 
				FROM lst_importar_log 
				WHERE codunico='$codunico' and codcampo NOT IN (SELECT codcampo FROM lst_listaintegrada_det WHERE codlista=$codlista)";
	
		$consulta=$this->db->consultar($sql);	
		if(!empty($consulta)){		
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}	
        return $this->listas;	
		
	}
	
	public function select_data_campo_out($codlista,$codunico){
		$this->listas=null;
		$sql="SELECT *, (IFNULL(rdtoalta,0)+ IFNULL(rdtobaja,0)) as produccion 
			FROM lst_listaintegrada_det 
			WHERE codlista=$codlista AND concat_ws('_',cedula,codcampo) NOT IN
			(SELECT concat_ws('_',cedula,codcampo) FROM lst_importar_log where codunico='$codunico') ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}	
        return $this->listas;	
		
	}
	
	public function select_data_agricultores_out($codlista,$codunico){
		$this->listas=null;
		$sql="SELECT *, (rdtoalta+rdtobaja) as produccion 
			FROM lst_listaintegrada_det 
			WHERE  codlista=$codlista AND cedula NOT IN	(SELECT cedula FROM lst_importar_log where codunico='$codunico') ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}	
        return $this->listas;	
		
	}
	
	/****************
	GRAFICOS
	****************/
	public function select_unidad_importarlog($codunico,$codlista=null){
		$this->listas=null;
		$sql="select distinct codunidad from lst_importar_log where codunico='$codunico'";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_cultivo_importarlog($codunico,$codlista=null){
		$this->listas=null;
		$sql="select distinct cultivo from lst_importar_log where codunico='$codunico'";
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	public function select_pais_proyecto($project_id){
		$sql="select id_pais from prg_proyecto where project_id='$project_id' limit 0,1";
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	public function selec_one_proyectobyid($project_id){
		
		$sql="SELECT * from lst_proyecto where project_id='$project_id' and flag='1'";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	
	public function select_cantidad_agri($codunico){
		$sql="SELECT COUNT(DISTINCT cedula) AS cantidad  FROM lst_importar_log where codunico='$codunico'";
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	public function select_cantidad_campo($codunico){
		$sql="SELECT  COUNT(DISTINCT codcampo) AS cantidad  FROM lst_importar_log where codunico='$codunico' ";
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	// total agricultores x unidad
	public function select_cantidad_cedulabyunidad($codunico,$project_id=null){
		$this->listas=null;
		$sql="SELECT codunidad, COUNT(DISTINCT cedula) AS cantidad 
				FROM lst_importar_log where codunico='$codunico'  
				GROUP BY codunidad";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	// promedios x unidad
	public function select_promedio_byunidad($project_id,$codunico){
		$this->listas=null;
		$sql="SELECT codunidad, 
				ROUND(AVG((ifnull(rdtoalta,0) + ifnull(rdtobaja,0) )),2) AS rdto, 
				ROUND(AVG(ifnull(rdtoalta,0)),2) AS rdto2, 
				ROUND(AVG(area_cultivo),2) area_cultivo,
				ROUND(AVG((ratiopromedioalta+ratiopromediobaja)/2),2) ratiopromedio,
				ROUND(AVG(ifnull(rdtoultima,0)),2) rdtoultima,
				ROUND(AVG(ratioultima),2) ratioultima
			FROM lst_importar_log 
			where codunico='$codunico'
			GROUP BY codunidad";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_duplica_cedulaxunidad($project_id,$codunico){
		$this->listas=null;
		$sql="SELECT COUNT(*) AS total, 
				GROUP_CONCAT(CONCAT(codunidad,' en fila ',fila) order by codunidad,fila) AS codunidad,
				cedula, GROUP_CONCAT(distinct codcampo) as codcampo,
				CONCAT_WS(' ',cedula,nombres,apellidos) AS nombre
				FROM lst_importar_log
				where codunico='$codunico'
				GROUP BY cedula
				HAVING total>1";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_duplica_cedulaxcampo($project_id,$codunico){
		$this->listas=null;
		$sql="SELECT COUNT(*) AS total, codcampo, cedula, CONCAT_WS(' ',cedula,nombres,apellidos) AS nombre
				FROM lst_importar_log
				where codunico='$codunico'
				GROUP BY codcampo, cedula
				HAVING total>1";
						$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	// total campos x unidad
	public function select_cantidad_campobyunidad($codunico,$project_id=null){
		$this->listas=null;
		$sql="SELECT codunidad, COUNT(DISTINCT codcampo) AS cantidad FROM lst_importar_log where codunico='$codunico' GROUP BY codunidad";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_areacultivo_unidad($variable,$codunidad,$codunico){
		$sql="SELECT 
					ROUND(SUM(CASE  WHEN ".$variable."_campo='ORG' AND ".$variable."_cosecha IN ('ORG') THEN area_cultivo ELSE 0 END),2) AS area_org,
					ROUND(SUM(CASE  WHEN ".$variable."_campo='IC3' AND ".$variable."_cosecha IN ('IC3','IC') THEN area_cultivo ELSE 0 END),2) AS area_ic3,
					ROUND(SUM(CASE  WHEN ".$variable."_campo='IC2' AND ".$variable."_cosecha IN ('IC2','IC') THEN area_cultivo ELSE 0 END),2) AS area_ic2,
					ROUND(SUM(CASE  WHEN ".$variable."_campo='IC1' AND ".$variable."_cosecha IN ('IC1','CO') THEN area_cultivo ELSE 0 END),2) AS area_ic1,
					ROUND(SUM(CASE  WHEN ".$variable."_cosecha='SUSP' THEN area_cultivo ELSE 0 END),2) AS area_susp,
					ROUND(SUM(CASE  WHEN ".$variable."_cosecha='NA' THEN area_cultivo ELSE 0 END),2) AS area_na
					FROM lst_importar_log where codunidad='$codunidad' and codunico='$codunico'";
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;
		
	}
	
	public function select_areacultivo_unidadUSDA($variable,$codunidad,$codunico){
		$sql="SELECT 
					ROUND(SUM(CASE  WHEN ".$variable."_campo='ORG' AND ".$variable."_cosecha IN ('ORG') THEN area_cultivo ELSE 0 END),2) AS area_org,
					ROUND(SUM(CASE  WHEN ".$variable."_campo='IC3' AND ".$variable."_cosecha IN ('CO') THEN area_cultivo ELSE 0 END),2) AS area_ic3,
					ROUND(SUM(CASE  WHEN ".$variable."_campo='IC2' AND ".$variable."_cosecha IN ('CO') THEN area_cultivo ELSE 0 END),2) AS area_ic2,
					ROUND(SUM(CASE  WHEN ".$variable."_campo='IC1' AND ".$variable."_cosecha IN ('CO') THEN area_cultivo ELSE 0 END),2) AS area_ic1,
					ROUND(SUM(CASE  WHEN ".$variable."_cosecha='SUSP' THEN area_cultivo ELSE 0 END),2) AS area_susp,
					ROUND(SUM(CASE  WHEN ".$variable."_cosecha='NA' THEN area_cultivo ELSE 0 END),2) AS area_na
					FROM lst_importar_log where codunidad='$codunidad' and codunico='$codunico'";
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;
		
	}
	
	public function select_agri_unidad($variable,$codunidad,$codunico){
		$sql="SELECT 
				(SELECT COUNT(DISTINCT cedula) AS agri_org
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='ORG' AND ".$variable."_campo IN ('CO','IC','ORG') AND ".$variable."_cosecha!='SUSP'
				) AS org,
				(
				SELECT COUNT(DISTINCT cedula) AS agri_ic3
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC3' AND ".$variable."_cosecha IN ('CO','IC','ORG') AND cedula NOT IN 
				(SELECT cedula FROM lst_importar_log WHERE codunidad='$codunidad' and codunico='$codunico' and ".$variable."_campo='ORG' AND ".$variable."_cosecha!='SUSP')
				) AS ic3,

				(
				SELECT COUNT(DISTINCT cedula) AS agri_ic2
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC2' AND ".$variable."_cosecha IN ('CO','IC','ORG') AND cedula NOT IN 
				(SELECT cedula FROM lst_importar_log WHERE codunidad='$codunidad' and codunico='$codunico' and ".$variable."_campo IN ('ORG','IC3') AND ".$variable."_cosecha!='SUSP')
				) AS ic2,

				(
				SELECT COUNT(DISTINCT cedula) AS agri_ic2
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC1' AND ".$variable."_cosecha IN ('CO','IC','ORG') AND cedula NOT IN 
				(SELECT cedula FROM lst_importar_log WHERE codunidad='$codunidad' and codunico='$codunico' and ".$variable."_campo IN ('ORG','IC3','IC2') AND ".$variable."_cosecha!='SUSP')
				) AS ic1,

				(
				SELECT COUNT(DISTINCT cedula) AS agri_ic2
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_cosecha='SUSP' AND cedula NOT IN 
				(SELECT cedula FROM lst_importar_log WHERE codunidad='$codunidad' and codunico='$codunico' and ".$variable."_campo IN ('ORG','IC3','IC2','IC1') AND ".$variable."_cosecha!='SUSP' )
				) AS susp";

		$consulta=$this->db->consultarOne($sql);		
        return $consulta;
		
	}

	public function select_agri_unidad_new($variable,$codunidad,$codunico){
		$sql="SELECT 
				(SELECT COUNT(DISTINCT cedula) AS agri_org
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='ORG' AND ".$variable."_cosecha='ORG'
				) AS org,
				(
				SELECT COUNT(DISTINCT cedula) AS agri_ic3
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC3' AND ".$variable."_cosecha='IC'
					and cedula not in (select cedula from lst_importar_log	WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo in ('ORG'))
				) AS ic3,

				(
				SELECT COUNT(DISTINCT cedula) AS agri_ic2
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC2' AND ".$variable."_cosecha='IC'
					and cedula not in (select cedula from lst_importar_log	WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo in ('ORG','IC3'))
				) AS ic2,

				(
				SELECT COUNT(DISTINCT cedula) AS agri_ic1
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC1' AND ".$variable."_cosecha='CO'
					and cedula not in (select cedula from lst_importar_log	WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo in ('ORG','IC3','IC2'))
				) AS ic1,
				(
				SELECT COUNT(DISTINCT cedula) AS agri_susp
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_cosecha='SUSP'
				) AS susp,
				(
				SELECT COUNT(DISTINCT cedula) AS agri_na
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and  ".$variable."_cosecha='NA'
					and cedula not in (select cedula from lst_importar_log	WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_cosecha ='SUSP')
				) AS na";

		$consulta=$this->db->consultarOne($sql);		
        return $consulta;
		
	}

	public function select_agri_unidad_newUSDA($variable,$codunidad,$codunico){
		$sql="SELECT 
				(SELECT COUNT(DISTINCT cedula) AS agri_org
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='ORG' AND ".$variable."_cosecha='ORG'
				) AS org,
				(
				SELECT COUNT(DISTINCT cedula) AS agri_ic3
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC3' AND ".$variable."_cosecha='CO'
					and cedula not in (select cedula from lst_importar_log	WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo in ('ORG'))
				) AS ic3,

				(
				SELECT COUNT(DISTINCT cedula) AS agri_ic2
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC2' AND ".$variable."_cosecha='CO'
					and cedula not in (select cedula from lst_importar_log	WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo in ('ORG','IC3'))
				) AS ic2,

				(
				SELECT COUNT(DISTINCT cedula) AS agri_ic1
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC1' AND ".$variable."_cosecha='CO'
					and cedula not in (select cedula from lst_importar_log	WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo in ('ORG','IC3','IC2'))
				) AS ic1,
				(
				SELECT COUNT(DISTINCT cedula) AS agri_susp
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_cosecha='SUSP'
				) AS susp,
				(
				SELECT COUNT(DISTINCT cedula) AS agri_na
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and  ".$variable."_cosecha='NA'
					and cedula not in (select cedula from lst_importar_log	WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_cosecha  in ('SUSP'))
				) AS na";

		$consulta=$this->db->consultarOne($sql);		
        return $consulta;
		
	}

	public function procedimiento_reporte_master($seconds){
		$sql=" call  execute_reporte_master('$seconds')";
		$consulta=$this->db->execute($sql);
		return $consulta;
		
		
	}
	
	public function select_certificado_master_new($codunico,$programa){
		$this->listas=null;
		$sql="SELECT * FROM prg_reporte_cer WHERE codunico='$codunico' AND programa='$programa'";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;
		
	}
	public function select_certificado_master($variable,$codunidad,$codunico,$not_in_cedula=null){
		$sql="SELECT 
				(SELECT SUM(area_cultivo) AS ha_org
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='ORG' AND ".$variable."_cosecha='ORG'
				) AS ha_org,
				(SELECT COUNT(DISTINCT cedula) AS agri_org
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='ORG' AND ".$variable."_cosecha='ORG'";
				
		$sql.=") AS agri_org,
				(SELECT SUM(area_cultivo) AS ha_trans
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo IN ('IC2','IC3') AND ".$variable."_cosecha='IC'
				) AS ha_trans,
				(SELECT COUNT(DISTINCT cedula) AS agri_trans
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo IN ('IC2','IC3') AND ".$variable."_cosecha='IC'";
		
		if(!empty($not_in_cedula)){
			$sql.="AND cedula not in (
					SELECT
						distinct(cedula)
					FROM
						lst_importar_log
					WHERE
					codunico='$codunico' and codunidad in ('$codunidad') and ".$variable."_campo IN ('ORG') AND ".$variable."_cosecha IN ('ORG')
				)";
		}

		$sql.=") AS agri_trans,";
		$condicion_usda = " ";
		if($variable == 'usda'){
			$condicion_usda = ",'IC2','IC3'";		
		}
		$sql.="
				(SELECT SUM(area_cultivo) AS ha_conv
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo IN ('IC1'".$condicion_usda.") AND ".$variable."_cosecha='CO'
				) AS ha_conv,
				(SELECT COUNT(DISTINCT cedula) AS agri_conv
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo IN ('IC1'".$condicion_usda.") AND ".$variable."_cosecha='CO'";
		
		if(!empty($not_in_cedula)){
			$sql.="AND cedula not in (
					SELECT
						distinct(cedula)
					FROM
						lst_importar_log
					WHERE
					codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo IN ('IC2', 'IC3','ORG') AND ".$variable."_cosecha IN ('IC','ORG')
				)";
		}
				
		$sql.=") AS agri_conv,
				(SELECT SUM(area_cultivo) AS ha_susp
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo IN ('ORG','IC3','IC2','IC1','SUSP','NA') AND ".$variable."_cosecha IN ('SUSP','NA')
				) AS ha_susp,
				(SELECT COUNT(DISTINCT cedula) AS agri_susp
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo IN ('ORG','IC3','IC2','IC1','SUSP','NA') AND ".$variable."_cosecha IN ('SUSP','NA')";
		
		if(!empty($not_in_cedula)){
			$sql.="AND cedula not in (
					SELECT
						distinct(cedula)
					FROM
						lst_importar_log
					WHERE
					codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo IN ('IC1','IC2', 'IC3','ORG') AND ".$variable."_cosecha IN ('CO','IC','ORG')
				)";
		}

		$sql.=") AS agri_susp
				";

		$consulta=$this->db->consultarOne($sql);		
        return $consulta;
		
	}
	
	public function select_rdto_unidad($variable,$codunidad,$codunico){
		$sql="SELECT 
				(SELECT SUM(ifnull(rdtoalta,0) + ifnull(rdtobaja,0))
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='ORG' AND ".$variable."_cosecha IN ('ORG')
				) AS org,
				(
				SELECT SUM(ifnull(rdtoalta,0) + ifnull(rdtobaja,0))
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC3' AND ".$variable."_cosecha IN ('IC') 
				) AS ic3,

				(
				SELECT SUM(ifnull(rdtoalta,0) + ifnull(rdtobaja,0))
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC2' AND ".$variable."_cosecha IN ('IC') 
				) AS ic2,

				(
				SELECT SUM(ifnull(rdtoalta,0) + ifnull(rdtobaja,0))
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC1' AND ".$variable."_cosecha IN ('CO') 
				) AS ic1,
				(
				SELECT SUM(ifnull(rdtoalta,0) + ifnull(rdtobaja,0))
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_cosecha='SUSP' 
				) AS susp,
				(
				SELECT SUM(ifnull(rdtoalta,0) + ifnull(rdtobaja,0))
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_cosecha='NA' 
				) AS na";
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;
		
	}
	
	public function select_rdto_unidadUSDA($variable,$codunidad,$codunico){
		$sql="SELECT 
				(SELECT SUM(ifnull(rdtoalta,0) + ifnull(rdtobaja,0))
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='ORG' AND ".$variable."_cosecha IN ('ORG')
				) AS org,
				(
				SELECT SUM(ifnull(rdtoalta,0) + ifnull(rdtobaja,0))
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC3' AND ".$variable."_cosecha IN ('CO') 
				) AS ic3,

				(
				SELECT SUM(ifnull(rdtoalta,0) + ifnull(rdtobaja,0))
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC2' AND ".$variable."_cosecha IN ('CO') 
				) AS ic2,

				(
				SELECT SUM(ifnull(rdtoalta,0) + ifnull(rdtobaja,0))
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_campo='IC1' AND ".$variable."_cosecha IN ('CO') 
				) AS ic1,
				(
				SELECT SUM(ifnull(rdtoalta,0) + ifnull(rdtobaja,0))
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_cosecha='SUSP' 
				) AS susp,
				(
				SELECT SUM(ifnull(rdtoalta,0) + ifnull(rdtobaja,0))
				FROM lst_importar_log
				WHERE codunico='$codunico' and codunidad='$codunidad' and ".$variable."_cosecha='NA' 
				) AS na";
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;
		
	}
	
	public function select_rendimiento($project_id,$txtrendimiento,$codunico){
		$this->listas=null;
		$sql="SELECT *,
			ifnull(rdtoalta,0) + ifnull(rdtobaja,0) as rdto
			FROM lst_importar_log 
				WHERE codproyecto='$project_id' and codunico='$codunico'
					and (ifnull(rdtoalta,0) + ifnull(rdtobaja,0))/1000 > $txtrendimiento";

		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function select_sobreproduce($project_id,$txtsobreproduce,$txtsobreproduce_por,$codunico){
		$this->listas=null;
	$sql="SELECT *, (IFNULL(rdtoalta,0)+ IFNULL(rdtobaja,0) ) as rdto,  
			((IFNULL(rdtoalta,0)+ IFNULL(rdtobaja,0))- IFNULL(rdtoultima,0))/1000 as sobre,
				(((IFNULL(rdtoalta,0)+ IFNULL(rdtobaja,0))/ IFNULL(rdtoultima,0))-1)*100 as porcentaje, 
				codunidad , (id +10) as id
				FROM lst_importar_log 
				WHERE codproyecto='$project_id' and codunico='$codunico'
					and ( IFNULL(rdtoalta,0)+ IFNULL(rdtobaja,0))- IFNULL(rdtoultima,0)>0";
		if(!empty($txtsobreproduce))
			$sql.="	and (( IFNULL(rdtoalta,0)+ IFNULL(rdtobaja,0))- IFNULL(rdtoultima,0))/1000 > $txtsobreproduce";
		
		if(!empty($txtsobreproduce_por))
			$sql.="	and ((( IFNULL(rdtoalta,0)+ IFNULL(rdtobaja,0))/ IFNULL(rdtoultima,0))-1)*100 > $txtsobreproduce_por";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	/****************************************
	PROCESO DE REGISTRO LA LISTA : GRABAR
	*****************************************/
	
	public function paso_01($caso,$ref_pais,$project_id,$id_pais,$proyect,$fecha,$fechaf,$usuario,$ip,$tipo=null,$referencia=null){
		/*$sql=" update lst_listaintegrada set flgestado='0' where codproyecto='$project_id'";
		
		$consulta=$this->db->execute($sql);*/
        
		$sql="insert into lst_listaintegrada(caso,ref_pais,id_pais,codproyecto,proyecto,fechainicio,fechatermino,usuario_ingreso,fecha_ingreso,ip_ingreso,tipo,referencia)
			values('$caso','$ref_pais','$ref_pais','$project_id','$proyect','$fecha','$fechaf','$usuario',now(),'$ip','$tipo','$referencia')";
		
		$consulta=$this->db->executeIns($sql);
        return $consulta;	
	}

	public function update_conf_lista($codlista,$pico_i,$pico_f,$semanai,$semanaf,$baja_i,$baja_f,$tolerancia){
		$sql="update lst_listaintegrada
				set 
					pico_i='$pico_i',
					pico_f='$pico_f',
					semanai='$semanai',
					semanaf='$semanaf',
					baja_i='$baja_i',
					baja_f='$baja_f',
					tolerancia='$tolerancia'
				where codlista=$codlista ";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
	}

	public function paso_02($codlista,$path,$project_id,$id_pais,$usuario,$ip,$quehacer,$codunico){
		$sql="insert into lst_listaintegrada_file(codlista,fecha,rutafile) values($codlista,now(),'$path')";
		$consulta=$this->db->executeIns($sql);
        
		$sql="INSERT INTO lst_cultivo(cultivo,id_pais,usuario_ingreso,fecha_ingreso,ip_ingreso)
		SELECT DISTINCT cultivo,'$id_pais','$usuario',NOW(),'$ip' 
		FROM lst_importar_log  
		WHERE codunico='$codunico' and cultivo NOT IN (SELECT cultivo FROM lst_cultivo WHERE flag='1' AND id_pais='$id_pais')";
		$consulta=$this->db->executeIns($sql);
		
		$sql="INSERT INTO lst_unidad(unidad,flag,project_id,codlista)
		SELECT distinct codunidad,'1',codproyecto,$codlista from lst_importar_log where codunico='$codunico'";
		$consulta=$this->db->executeIns($sql);
		
		
		$sql="insert into lst_listaintegrada_det( 
			codlista,codunidad,codcampo,apellidos,nombres,cedula,finca,ubicacion,gps,fechault_aplica,
			quimico,area_total,area_cultivo,sistema_cultivo,nroplanta_ha,rdto_planta,rdto,rdto_promedio,
			cultivo,variedad,inicio,inicio_org,fecha_siembra,epoca_cosecha,eu_campo,eu_cosecha,usda_campo,usda_cosecha,
			rpto_campo,rpto_cosecha,nrovisita,ultima_visita,status_hasta,codagricultor,unidadempaque,fechaprim_aplica,area_cultivo_tot,stock,rdtoalta,rdtobaja,tipo)
			select
				$codlista,codunidad,codcampo,UPPER(apellidos),
				UPPER(nombres),cedula,
				UPPER(finca),UPPER(ubicacion),gps,fechault_aplica,
				quimico,area_total,area_cultivo,UPPER(sistema_cultivo),nroplanta_ha,rdto_planta,rdto,rdto_promedio,
				UPPER(cultivo),UPPER(variedad),UPPER(inicio),UPPER(inicio_org),UPPER(fecha_siembra),UPPER(epoca_cosecha),eu_campo,eu_cosecha,usda_campo,usda_cosecha,
				UPPER(rpto_campo),UPPER(rpto_cosecha),nrovisita,UPPER(ultima_visita),UPPER(status_hasta),codagricultor,UPPER('unidadempaque'),fechaprim_aplica,UPPER(area_cultivo),stock,rdtoalta,rdtobaja,tipo
			from lst_importar_log
			where codproyecto='$project_id' and codunico='$codunico'";
		if($quehacer=='a')
			$sql.=" and codagricultor not in (select codagricultor from lst_listaintegrada_det where codlista=$codlista);";
	
		$consulta=$this->db->executeIns($sql);
		
		$sql="UPDATE lst_listaintegrada_det INNER JOIN lst_cultivo ON lst_listaintegrada_det.cultivo=lst_cultivo.cultivo
				SET lst_listaintegrada_det.codcultivo=lst_cultivo.codcultivo
				WHERE lst_cultivo.flag='1' AND lst_cultivo.id_pais='$id_pais' 
					AND lst_listaintegrada_det.codlista=$codlista";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE lst_listaintegrada_det INNER JOIN lst_unidad 
				ON lst_listaintegrada_det.codunidad=lst_unidad.unidad and lst_listaintegrada_det.codlista=lst_unidad.codlista
			SET lst_listaintegrada_det.idunidad=lst_unidad.id
			WHERE lst_unidad.flag='1' AND lst_listaintegrada_det.codlista=$codlista";
		$consulta=$this->db->execute($sql);
		
        return $consulta;	
	}
	
	
	public function paso_03($codlista,$path,$project_id,$id_pais,$usuario,$ip,$codunico){
		$sql="update lst_listaintegrada_file 
			set rutafile='$path', fecha=now()
			where codlista=$codlista ";
		$consulta=$this->db->execute($sql);
        
		$sql="INSERT INTO lst_cultivo(cultivo,id_pais,usuario_ingreso,fecha_ingreso,ip_ingreso)
		SELECT DISTINCT cultivo,'$id_pais','$usuario',NOW(),'$ip' 
		FROM lst_importar_log  
		WHERE codunico='$codunico' and cultivo NOT IN (SELECT cultivo FROM lst_cultivo WHERE flag='1' AND id_pais='$id_pais')";
		$consulta=$this->db->executeIns($sql);
		
		$sql="INSERT INTO lst_unidad(unidad,flag,project_id,codlista)
		SELECT distinct codunidad,'1',codproyecto,$codlista from lst_importar_log where codunico='$codunico'";
		$consulta=$this->db->executeIns($sql);
		
		// borramos la lista existente
		$sql=" delete from lst_listaintegrada_det where codlista=$codlista ";
		$consulta=$this->db->execute($sql);
		
		$sql="insert into lst_listaintegrada_det(
			codlista,codunidad,codcampo,apellidos,nombres,cedula,finca,ubicacion,gps,fechault_aplica,
			quimico,area_total,area_cultivo,sistema_cultivo,nroplanta_ha,rdto_planta,rdto,rdto_promedio,
			cultivo,inicio,inicio_org,fecha_siembra,epoca_cosecha,eu_campo,eu_cosecha,usda_campo,usda_cosecha,
			rpto_campo,rpto_cosecha,nrovisita,ultima_visita,status_hasta,codagricultor,unidadempaque,fechaprim_aplica,area_cultivo_tot,stock,rdtoalta,rdtobaja,tipo)
			select
				$codlista,codunidad,codcampo,UPPER(apellidos),
				UPPER(nombres),cedula,
				UPPER(finca),UPPER(ubicacion),gps,fechault_aplica,
				quimico,area_total,area_cultivo,UPPER(sistema_cultivo),nroplanta_ha,rdto_planta,rdto,rdto_promedio,
				UPPER(cultivo),UPPER(inicio),UPPER(inicio_org),UPPER(fecha_siembra),UPPER(epoca_cosecha),eu_campo,eu_cosecha,usda_campo,usda_cosecha,
				UPPER(rpto_campo),UPPER(rpto_cosecha),nrovisita,UPPER(ultima_visita),UPPER(status_hasta),codagricultor,UPPER('unidadempaque'),fechaprim_aplica,UPPER(area_cultivo),stock,rdtoalta,rdtobaja,tipo
			from lst_importar_log
			where codproyecto='$project_id' and codunico='$codunico'";
		
		$consulta=$this->db->executeIns($sql);
		
		$sql="UPDATE lst_listaintegrada_det INNER JOIN lst_cultivo ON lst_listaintegrada_det.cultivo=lst_cultivo.cultivo
				SET lst_listaintegrada_det.codcultivo=lst_cultivo.codcultivo
				WHERE lst_cultivo.flag='1' AND lst_cultivo.id_pais='$id_pais' 
					AND lst_listaintegrada_det.codlista=$codlista";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE lst_listaintegrada_det INNER JOIN lst_unidad 
				ON lst_listaintegrada_det.codunidad=lst_unidad.unidad and lst_listaintegrada_det.codlista=lst_unidad.codlista
			SET lst_listaintegrada_det.idunidad=lst_unidad.id
			WHERE lst_unidad.flag='1' AND lst_listaintegrada_det.codlista=$codlista";
		$consulta=$this->db->execute($sql);
		
        return $consulta;	
	}
	
	
	public function selec_one_liTemporalfileanexo($codanexo){
		$sql=" SELECT *	FROM lst_temporalfileanexo	WHERE  codanexo=$codanexo " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	
	public function insert_cultivoperiodo($project_id){
		 
		$sql="insert into lst_cultivoxperiodoxlista(codlista,project_id,codcultivo,semanas,flag) 
			SELECT DISTINCT codlista,'$project_id',codcultivo,
				CASE cultivo WHEN 'BANANO' THEN 1 ELSE 52 END,'1' 
			FROM lst_listaintegrada_det WHERE codlista=codlista";
		$consulta=$this->db->executeIns($sql);
        return $consulta;	
	}
	
	public function insert_lstproyecto($project_id,$usuario,$ip,$id_pais){
		 
		$sql="insert into lst_proyecto(project_id,id_pais,proyecto,password,email,contacto,
					flag,usuario_ingreso,fecha_ingreso,ip_ingreso,id_rol)
					select project_id,'$id_pais',proyect,md5(project_id),email,fax,'1','$usuario',now(),'$ip',2
					from prg_proyecto where project_id='$project_id' limit 0,1";
				
		$consulta=$this->db->executeIns($sql);
        return $consulta;	
	}
	
	public function select_lstproyecto($project_id,$id_pais){
		$this->listas=null;
		$sql="select * from lst_proyecto where project_id='$project_id' and flag='1' and id_pais='$id_pais'";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	//LISTA INTEGRADA TEMPROAL SUBIDA POR CLIENTES
	// total de registros por auditor fecha
	public function selec_total_liTemporal($searchQuery=null){
		$sql=" SELECT count(c.codfile) as total
			FROM lst_temporalfile c INNER JOIN 
				 lst_proyecto p on	c.project_id=p.project_id
				WHERE c.flag='1' $searchQuery " ;
				
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function select_liTemporal($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		$this->listas=null;
		
			$sql="SELECT c.*, p.proyecto, date_format(c.fecha_ingreso,'%d/%m/%Y') as fecha,
					ifnull(lst_motivo.motivo,'') as motivo,
					IFNULL(vista.visitado,0) AS visitado, 
					IFNULL(vista.total,0) AS total,
					IFNULL(vista.nombrelista,'') AS nombrelista
					
				FROM lst_temporalfile c INNER JOIN 
				 lst_proyecto p on	c.project_id=p.project_id left join
				 lst_motivo on c.codmotivo=lst_motivo.codmotivo LEFT JOIN (
					SELECT SUM(CASE WHEN t.usuario_ingreso IS NULL THEN 0 ELSE 1 END ) AS visitado,
						COUNT(DISTINCT codanexo) AS total,
						group_concat(t.archivo separator '<br>') nombrelista,
						t.codfile
					FROM lst_temporalfileanexo t 
					GROUP BY codfile
				 ) AS vista ON c.codfile=vista.codfile
				WHERE c.flag='1' $searchQuery";
    
		$sql.=" order by c.fecha_ingreso desc limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function delete_listaTemporal($codfile){
		 
		$sql="update lst_temporalfile set flag='0' where codfile=$codfile";
				
		$consulta=$this->db->execute($sql);
        return $consulta;	
	}
	
	public function selec_one_liTemporal($codfile){
		$sql=" SELECT c.*, ifnull(p.motivo,'') as motivo
				FROM lst_temporalfile c INNER JOIN 
				 lst_motivo p on	c.codmotivo=p.codmotivo
				WHERE c.flag='1' and codfile=$codfile " ;
				
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_liTemporalanexo($codfile){
		$this->listas=null;
		
			$sql="SELECT a.archivo, a.codanexo,IFNULL(a.usuario_ingreso,'') AS usuario_ingreso, 
					IFNULL(a.ip_ingreso,'') AS ip_ingreso, 
					IFNULL(a.fecha_ingreso,'') AS fecha_ingreso,
					group_concat(distinct l.usuario_ingreso) as histousuario,
					COUNT(id) AS total 
			FROM lst_temporalfileanexo a LEFT JOIN lst_temporalfileanexolog l ON a.codanexo=l.codanexo
			WHERE a.codfile=$codfile
			GROUP BY a.codanexo";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function log_listaTemporalanexo($codfile,$codanexo,$id_auditor,$usuario,$ip){
		$sql="insert into lst_temporalfileanexolog(codanexo,usuario_ingreso,fecha_ingreso,ip_ingreso,id_auditor)
					values ($codanexo,'$usuario',now(),'$ip',$id_auditor)";
		$consulta=$this->db->executeIns($sql);

		$sql="update lst_temporalfileanexo set
				usuario_ingreso='$usuario',fecha_ingreso=now(),ip_ingreso='$ip'
				where codanexo=$codanexo";
		$consulta=$this->db->execute($sql);


		$sql="UPDATE lst_temporalfile c INNER JOIN 
			 lst_proyecto p ON	c.project_id=p.project_id
			 LEFT JOIN (
				SELECT SUM(CASE WHEN t.usuario_ingreso IS NULL THEN 0 ELSE 1 END ) AS visitado,
					COUNT(DISTINCT codanexo) AS total,
					t.codfile
				FROM lst_temporalfileanexo t 
				GROUP BY codfile
			 ) AS vista ON c.codfile=vista.codfile
			SET estado='ATENDIDO'	
			WHERE c.flag='1'  and c.codfile=$codfile 
			AND visitado=total AND visitado>0;";
		$consulta=$this->db->execute($sql);
		
		
		$sql="UPDATE lst_temporalfile c INNER JOIN 
			 lst_proyecto p ON	c.project_id=p.project_id
			 LEFT JOIN (
				SELECT SUM(CASE WHEN t.usuario_ingreso IS NULL THEN 0 ELSE 1 END ) AS visitado,
					COUNT(DISTINCT codanexo) AS total,
					t.codfile
				FROM lst_temporalfileanexo t 
				GROUP BY codfile
			 ) AS vista ON c.codfile=vista.codfile
			SET estado='EN PROCESO'	
			WHERE c.flag='1'  and c.codfile=$codfile
			AND visitado!=total AND visitado>0;";
		$consulta=$this->db->execute($sql);

        return $consulta;		
		
	}
	
	public function selec_motivos(){
		
		$this->listas=null;
		
			$sql="SELECT codmotivo, motivo from lst_motivo where flag='1' order by motivo";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;
	}
	
	public function selec_logliTemporalanexo($codanexo){
		
		$this->listas=null;
		$sql="SELECT usuario_ingreso,fecha_ingreso 
				from lst_temporalfileanexolog 
				where codanexo=$codanexo 
				order by fecha_ingreso asc";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;
	}
	
	public function update_estadofileanexo($codfile,$estado,$codauditor,$usuario){
	
		$sql="UPDATE lst_temporalfile
				SET estado='$estado', 
					fecha_cierra=now(),
					usuario_cierra='$usuario',
					codusuario_cierra=$codauditor
			WHERE codfile=$codfile";
		$consulta=$this->db->execute($sql);

        return $consulta;	
	}
	
	public function update_estadofileanexo_osp($codfile,$estado,$codauditor,$usuario){
	
		$sql="UPDATE lst_temporalfile
				SET estado='$estado', 
					fecha_cierraosp=now(),
					usuario_cierraosp='$usuario',
					codusuario_cierraosp=$codauditor
			WHERE codfile=$codfile";
		$consulta=$this->db->execute($sql);

        return $consulta;	
	}
	
	
	public function update_fechafin_lista($codlista,$fechaf,$id_pais,$caso,$usuario,$ip){
		$sql="UPDATE lst_listaintegrada
				SET fechatermino='$fechaf', 
					caso='$caso',
					fecha_modifica=now(),
					usuario_modifica='$usuario',
					ip_modifica='$ip'
			WHERE codlista=$codlista and id_pais='$id_pais'";
			
		$consulta=$this->db->execute($sql);

        return $consulta;	
	}
	
	public function select_proyectos($id_pais){
		
		$this->listas=null;
		$sql="SELECT project_id, concat_ws(' ',project_id,proyect) proyect 
			FROM prg_proyecto 
			WHERE flag='1' AND  id_pais='$id_pais' AND IFNULL(project_id,'')!='' 
			ORDER BY 2";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;
	}
	
	public function select_cultivos_sel($id_pais){
		
		$this->listas=null;
		$sql="SELECT codcultivo,TRIM(cultivo) AS cultivo  
			FROM lst_cultivo 
			WHERE flag='1' AND id_pais='$id_pais' 
			ORDER BY 2";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;
	}
	
}
?>