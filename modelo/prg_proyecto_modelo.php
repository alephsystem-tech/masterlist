<?php
class prg_proyecto_model{
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

	public function select_proyecto_selectLista($id_pais){
		unset($this->listas);
		$sql="SELECT CAST(project_id AS CHAR) project_id, concat_ws(' ',project_id,trim(proyect)) as proyect,
					id_proyecto
				from prg_proyecto where flag='1'  and id_pais='$id_pais' 
				union
				SELECT project_id, CONCAT_WS(' ',project_id,TRIM(proyect)) AS proyect,id_proyecto
				FROM lst_prg_proyecto WHERE flag='1' AND id_pais='$id_pais'	
				order by 2" ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

	public function select_proyecto_Select($id_pais){
		unset($this->listas);
		$sql="SELECT project_id, concat_ws(' ',project_id,trim(proyect)) as proyect,id_proyecto
				from prg_proyecto where flag='1'  and id_pais='$id_pais' ";
		$sql.=" order by 2" ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_proyecto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_pais){
		$this->listas=[];
		$sql="SELECT id_proyecto,
			ifnull(p.project_id,'') project_id,
			trim(ifnull(proyect,'')) proyect,
			city,state,ifnull(country,'') country,
			ifnull(replace(dsc_programa,',','<br>'),'') dsc_programa,
			ifnull(replace(dsc_producto,',','<br>'),'') dsc_producto,
			ifnull(telephone,'') telephone,
			ifnull(mobile,'') mobile,
			ifnull(fax,'') fax,
			ifnull(p.flgactivo,'0') flgactivo,
			ruc,
			replace(email,';','<br>') as email,
			direccion
		from prg_proyecto p 
			LEFT JOIN prg_proyecto_programa pp ON p.project_id=pp.project_id AND pp.id_pais='$id_pais'
			LEFT JOIN prg_programa g ON pp.programa=g.iniciales AND g.id_pais='$id_pais'
		where p.flag='1'  and ifnull(proyect,'')!='' $searchQuery 
		GROUP BY p.project_id	";
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
	public function selec_total_proyecto($searchQuery=null,$id_pais){
		$sql=" SELECT COUNT(distinct p.project_id) AS total 
			FROM 
            prg_proyecto  p 
			LEFT JOIN prg_proyecto_programa pp ON p.project_id=pp.project_id AND pp.id_pais='$id_pais'
			LEFT JOIN prg_programa g ON pp.programa=g.iniciales AND g.id_pais='$id_pais'
        WHERE p.flag='1' $searchQuery	";
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_country_proyecto($id_pais){
		$this->listas=null;
		$sql="SELECT distinct country from prg_proyecto where flag='1' and id_pais='$id_pais' order by 1";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;
		
	}
	
	public function selec_one_proyecto($id_proyecto){
		
		$sql="SELECT p.*, 
				GROUP_CONCAT(distinct pp.programa) as programa, 
				GROUP_CONCAT(DISTINCT id_producto) as producto,
				GROUP_CONCAT(DISTINCT codcategoria) as categoria
				FROM prg_proyecto p  LEFT JOIN 
					prg_proyecto_programa pp ON p.project_id=pp.project_id AND pp.programa!='' and p.id_pais=pp.id_pais LEFT JOIN
					prg_proyecto_producto pf ON p.project_id=pf.project_id LEFT JOIN
					prg_proyecto_categoria pc ON p.project_id=pc.project_id and p.id_pais=pc.id_pais 
					
				WHERE p.id_proyecto=$id_proyecto";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_proyecto($project_id,$proyect,$direccion,$ruc,$city,$state,$country,$telephone,$mobile,$fax,$email,$modules,$is_viatico,$id_pais,$usuario,$ip){

        $sql="insert into prg_proyecto(project_id,proyect,ruc,city,state,country,telephone,mobile,fax,email,modules,is_viatico,
			id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica,direccion)
        values('$project_id','$proyect','$ruc','$city','$state','$country','$telephone','$mobile','$fax','$email',
			'$modules','$is_viatico','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip','$direccion')";
		
		$consulta=$this->db->executeIns($sql);
        
		/*$sql="INSERT INTO prg_proyectoactividad (project_id, proyect, id_pais) VALUES ('$project_id', '$proyect', '141'); ";
		$consulta2=$this->db->executeIns($sql);*/
		
		return $consulta;
    }	

	// update usuario
    public function update_proyecto($id_proyecto,$project_id,$proyect,$direccion,$ruc,$city,$state,$country,$telephone,$mobile,$fax,$email,$modules,$is_viatico,$id_pais,$usuario,$ip){
	   
        $sql="update prg_proyecto
				set 
				project_id='$project_id',proyect='$proyect',
				direccion='$direccion',
				ruc='$ruc',city='$city',state='$state',country='$country',
				telephone='$telephone',mobile='$mobile',fax='$fax',
				email='$email',modules='$modules',is_viatico='$is_viatico',
				usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_proyecto=$id_proyecto and id_pais='$id_pais'";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_proyecto($id_proyecto){
	   
        $sql="update prg_proyecto set flag='0' where id_proyecto=$id_proyecto";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	 public function activa_proyecto($id_proyecto,$flgactivo){
		 
		$sql="update prg_proyecto set flgactivo='$flgactivo' where id_proyecto=$id_proyecto";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
	 }
	
	public function selec_producto($id_pais){
		unset($this->listas);
		$sql="SELECT  codproducto,producto FROM prg_producto WHERE flag='1' AND id_pais='$id_pais' ORDER BY 2";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function selec_categoria($id_pais){
		unset($this->listas);
		$sql="SELECT  codcategoria,categoria FROM prg_categoria_proy WHERE flag='1' AND id_pais='$id_pais' ORDER BY 2";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function delete_proyectoxproducto($project_id,$id_pais){
		$sql="delete from prg_proyecto_producto where project_id='$project_id' and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
	}
	
	public function insert_proyectoxproducto($project_id,$id_producto,$id_pais){
		$sql="insert into prg_proyecto_producto(project_id,id_producto,id_pais)
				values ('$project_id','$id_producto','$id_pais') ";
				
		$consulta=$this->db->executeIns($sql);
        return $consulta;
	}
	
	public function insert_proyectoxcategoria($project_id,$codcategoria,$id_pais){
		$sql="insert into prg_proyecto_categoria(project_id,codcategoria,id_pais)
				values ('$project_id','$codcategoria','$id_pais') ";
				
		$consulta=$this->db->executeIns($sql);
        return $consulta;
	}
	

	public function delete_proyectoxprograma($project_id,$id_pais){
		$sql="delete from prg_proyecto_programa where project_id='$project_id' and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
	}

	public function insert_proyectoxprograma($project_id,$programa,$id_pais){
		$sql="insert into prg_proyecto_programa(project_id,programa,id_pais)
				values ('$project_id','$programa','$id_pais') ";
		$consulta=$this->db->executeIns($sql);
        return $consulta;
	}
	
	public function update_proyecto_referencia($id_proyecto,$id_pais,$project_id){
		$sql="UPDATE prg_proyecto p  INNER JOIN 
				(SELECT GROUP_CONCAT(programa) AS programa, project_id 
					FROM prg_proyecto_programa WHERE programa!='' 
					AND project_id='$project_id' AND id_pais='$id_pais' 
				GROUP BY project_id) AS vista
				ON p.project_id=vista.project_id 
			SET p.dsc_programa=vista.programa
			WHERE id_proyecto=$id_proyecto and id_pais='$id_pais' ";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE prg_proyecto p  INNER JOIN 
					(SELECT GROUP_CONCAT(producto) AS producto, project_id 
					 FROM prg_proyecto_producto INNER JOIN prg_producto ON prg_proyecto_producto.id_producto=prg_producto.codproducto
					 GROUP BY project_id
					 ) AS vista
					ON p.project_id=vista.project_id 
			SET p.dsc_producto=vista.producto
			WHERE id_proyecto=$id_proyecto and id_pais='$id_pais' ";
		$consulta=$this->db->execute($sql);
		
        return $consulta;
	}
	
	public function insert_proyectoFormFile($data_sql,$codunico,$id_pais){
		$sql=" insert into prg_tmp_proyectos(
			  fecha_importacion,
			  project_id,
			  project,
			  pl_st_dt,
			  pl_end_dt,
			  real_sdate,
			  real_edate,
			  insp_days,
			  lead_auditor,
			  shadow_auditor,
			  insp_type,
			  spg_name,
			  spg_status,
			  insp_module,
			  relation_cl_ref,
			  address,
			  postalcode,
			  city,
			  state,
			  country,
			  telephone,
			  mobile,
			  fax,
			  email,
			  responsible_office,
			  certifying_office,
			  certificate_validtillorperdate,
			  processing_units,
			  production_units,
			  t_cluster,
			  audit_id,
			  inspection_id,
			  inspection_skal_no,
			  draft_date,
			  draft,
			  final_draft_date,
			  final_draft,
			  final_report_date,
			  final_report,
			  printed_date,
			  printed_by,
			  nc_count,
			  ggn,
			  appointment_start_date,
			  appointment_end_date,
			  assessment_done_by,
			  assessment_done_date,
			  id_pais
		) values $data_sql";
		$consulta=$this->db->executeIns($sql);
		
        return $consulta;
	}
	
	public function select_proyectoFormFile($codunico,$id_pais){
		$sql=" select count(*) as total from prg_tmp_proyectos where id_pais='$id_pais' and fecha_importacion='$codunico'";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	
	public function select_proyectoFormFileMigrado($codunico,$id_pais){
		$sql="SELECT  count(*) as total 
			FROM prg_tmp_proyectos 
			WHERE TRIM(project_id) NOT IN (SELECT TRIM(project_id) FROM prg_proyecto  WHERE id_pais='$id_pais')
			AND fecha_importacion = '$codunico' AND is_importar = 1  AND id_pais='$id_pais'
			GROUP BY project_id ";
			
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	public function procedure_proyectoFormFile($codunico,$id_pais){

		$sql="call prg_sp_actualizarInformacion('$codunico','$id_pais')";
		$consulta=$this->db->execute($sql);
		
        return $consulta;
	}
	
}
?>