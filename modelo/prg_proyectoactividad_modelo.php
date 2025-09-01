<?php
class prg_proyectoactividad_model{
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

	public function selec_proyectos($id_pais,$phrase=null){
		unset($this->listas);
		$sql="SELECT * from prg_proyectoactividad where flag='1' "; //  and admin_pais='$id_pais' 
		if(!empty($phrase))
			$sql.="	( project_id like '%$phrase%' or  proyect like '%$phrase%') ";
		$sql.="	order by proyect";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_proyectosGroup($phrase=null,$id_pais=null,$id_proy){
		unset($this->listas);
		$sql="SELECT * from prg_proyectoactividad where ( flgactivo='1' and flag='1' "; //  and admin_pais='$id_pais' 
		if(!empty($phrase)) $sql.="	( project_id like '%$phrase%' or  proyect like '%$phrase%') ";
		if(!empty($id_pais)) $sql.="	and admin_pais='$id_pais'  ";
		if(!empty($id_proy)) $sql.="	) or (id_proy='$id_proy'  ";
		
		
		$sql.=" )	group by project_id
			order by proyect";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_proyectoactividad($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT project_id,proyect,id_pais,pais,oficina ,id_proy,flgactivo
				from prg_proyectoactividad
				WHERE flag = '1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_proyectoactividad($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_proyectoactividad
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_proyectoactividad($id_proy){
		
		$sql="SELECT * from prg_proyectoactividad where id_proy=$id_proy ";
		
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_one_proyecto($project_id,$id_pais){
		
		$sql="SELECT * from prg_proyectoactividad where project_id='$project_id' and admin_pais='$id_pais' ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function insert_ImportProyectoactividad($data_sql,$codunico,$id_pais,$usuario){
		
		$sql="truncate  prg_proyectoactividad_tmp";
		$consulta=$this->db->execute($sql);
		
		$sql="insert into prg_proyectoactividad_tmp(project_id,proyect,pais,ciudad,responsable,subprograma,modulos,oficina,reemplazo) 
				values $data_sql";
		$consulta=$this->db->execute($sql);
		
		
		
		$sql="update prg_proyectoactividad set flag='0' 
				where admin_pais='$_SESSION[var_id_pais]' 
					AND project_id in (select project_id from prg_proyectoactividad_tmp where reemplazo='1' )";
		$consulta=$this->db->execute($sql);
		
		$sql="select count(*) as total
				from prg_proyectoactividad_tmp where ( project_id not in 
					(select project_id from prg_proyectoactividad where flag='1') or reemplazo='1' )";
		$consulta=$this->db->consultarOne($sql);
		$total=$consulta['total'];
		
		$sql="insert into prg_proyectoactividad (project_id,proyect,pais,oficina,admin_pais) 
			select distinct project_id,proyect,pais,oficina,'$id_pais' 
				from prg_proyectoactividad_tmp where ( project_id not in 
					(select project_id from prg_proyectoactividad where flag='1') or reemplazo='1' ) ";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE prg_proyectoactividad LEFT JOIN t_mae_pais ON CONVERT(prg_proyectoactividad.pais,CHAR)= IFNULL(CONVERT(t_mae_pais.otronombre,CHAR),CONVERT(t_mae_pais.nombre,CHAR))
		SET prg_proyectoactividad.id_pais=t_mae_pais.id_pais, fecha_modifica=now(), usuario_modifica='$usuario'";
		$consulta=$this->db->execute($sql);
		
        return $total;	
		
	}
	
	public function insert_proyectoactividad($project_id,$proyect,$oficina, $id_pais,$sess_codpais,$usuario,$ip){

        $sql="insert into prg_proyectoactividad (project_id,proyect,oficina,id_pais,flag,fecha_modifica,admin_pais) 
			values ('$project_id','$proyect','$oficina',$id_pais,'1',now(),'$sess_codpais'	)";
		
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_proyectoactividad($id_proy,$project_id,$proyect,$oficina, $id_pais,$sess_codpais,$usuario,$ip){
	   
        $sql="UPDATE prg_proyectoactividad SET
                        project_id='$project_id',
                        proyect='$proyect',
                        oficina='$oficina',
                        id_pais=$id_pais,
						fecha_modifica=now(),
						ip_modifica='$ip',
						usuario_modifica='$usuario'
			 WHERE id_proy=$id_proy ";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	 public function update_proyectoactividad_short($project_id,$proyect,$id_pais,$usuario,$ip){
	   
        $sql="UPDATE prg_proyectoactividad SET
                        
                        proyect='$proyect',
						fecha_modifica=now(),
						ip_modifica='$ip',
						usuario_modifica='$usuario'
			 WHERE project_id='$project_id' and admin_pais='$id_pais'";
			 
		$consulta=$this->db->execute($sql);
        return $consulta;
    }

	public function update_paisProyectoactividad($id_proy){
	   
        $sql="UPDATE prg_proyectoactividad LEFT JOIN t_mae_pais ON prg_proyectoactividad.id_pais=t_mae_pais.id_pais
			  SET prg_proyectoactividad.pais= IFNULL(CONVERT(t_mae_pais.otronombre,CHAR),CONVERT(t_mae_pais.nombre,CHAR))
			  where id_proy=$id_proy";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
    public function delete_proyectoactividad($id_proy){
	   
        $sql="update prg_proyectoactividad set flag='0' where id_proy=$id_proy";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	public function activa_proyecto($id_proy,$flgactivo){
	   
        $sql="UPDATE prg_proyectoactividad set flgactivo='$flgactivo' where id_proy=$id_proy";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
	}	

}
?>