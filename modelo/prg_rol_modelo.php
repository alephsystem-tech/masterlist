<?php
class prg_rol_model{
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

	
	public function select_rol($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_pais){
		unset($this->listas);
		$sql="SELECT m.* , ifnull(group_concat(distinct ifnull(ep.nombre,e.nombre) ORDER BY e.nombre separator '<br>' ),'') as enlace
			from prg_roles m left join 
				 prg_roles_enlaces re on m.id_rol=re.id_rol and re.id_pais='$id_pais'  left join 
				 prg_enlaces e on re.id_enlace=e.id_enlace and e.flag='1' and e.id_pais='esp'
					AND e.id_enlace NOT IN (SELECT id_enlace FROM prg_enlace_inactivo WHERE id_pais='$id_pais')
					left join prg_enlace_pais ep on e.id_enlace=ep.id_enlace  and ep.id_pais='$id_pais'
			where m.flag='1'  $searchQuery ";
		$sql.=" group by m.id_rol";	
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;

	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_roles($id_pais){
		unset($this->listas);
		$sql="SELECT id_rol,nombre	from prg_roles 	where flag='1' order by nombre";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	

	
	public function selec_enlacebyPais($id_pais,$id_rol){
		unset($this->listas);
		
		$sql=" SELECT DISTINCT e.id_enlace, 
					ifnull(ep.nombre,e.nombre) as nombre, ";
					
		if(!empty($id_rol)) $sql.="	IFNULL(re.id_enlace ,0) AS relacion ";
		else $sql.="	0 as relacion ";
		
		$sql.="	FROM  prg_enlaces e LEFT JOIN 
					  prg_enlace_pais ep on e.id_enlace=ep.id_enlace and ep.id_pais='$id_pais' ";
		if(!empty($id_rol)) $sql.="	left join prg_roles_enlaces re ON e.id_enlace=re.id_enlace and re.id_pais='$id_pais' AND id_rol=$id_rol ";

		$sql.="	WHERE e.flag='1'  AND e.id_pais='esp' and e.id_enlace not in (select id_enlace from prg_enlace_inactivo where id_pais='$id_pais') 
				ORDER BY 2 ";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function selec_enlacenivelbyPais($id_pais,$id_rol,$id_enlace=null){
		unset($this->listas);
		
		$sql=" select * from prg_enlacenivel 
			where id_rol=$id_rol";
		if(!empty($id_enlace))
			$sql.=" and id_enlace=$id_enlace ";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_rol($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_roles m
        WHERE m.flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_rol($id_rol){
		
		$sql="SELECT * from prg_roles where id_rol=$id_rol ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	

	 public function insert_rol($nombre,$tipohome,$id_pais,$usuario,$ip){

        $sql="insert into prg_roles(nombre,tipohome,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$nombre','$tipohome','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_rol($id_rol,$nombre,$tipohome,$id_pais,$usuario,$ip){
	   
        $sql="update prg_roles 
				set nombre='$nombre' ,
					tipohome='$tipohome',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_rol=$id_rol ";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	 public function califica_rol($id_rol,$flgcalifica,$usuario,$ip){
	   
        $sql="update prg_roles 
				set flgcalifica='$flgcalifica',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_rol=$id_rol ";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }

    public function delete_rol($id_rol){
	   
        $sql="update prg_roles set flag='0' where id_rol=$id_rol";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

	public function delete_enlacexrol($id_rol,$id_pais){
		$sql="delete from prg_roles_enlaces where id_rol=$id_rol and id_pais='$id_pais' and id_enlace in (select id_enlace from prg_enlaces where id_pais='esp')";
		$consulta=$this->db->execute($sql);
        return $consulta;
	}
	
	 public function insert_enlacexrol($id_rol,$id_enlace,$id_pais){
        $sql="insert into prg_roles_enlaces(id_rol,id_enlace,id_pais) values($id_rol,$id_enlace,'$id_pais')";
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }
	
	public function delete_enlacenivelxrol($id_rol,$id_pais){
		$sql="delete from prg_enlacenivel where id_rol=$id_rol and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
	}
	
	 public function insert_enlacenivelxrol($id_pais,$id_rol,$id_enlace,$isread,$isupdate,$isdelete){
        $sql="insert into prg_enlacenivel(id_pais,id_rol,id_enlace,isread,isupdate,isdelete) 
			values('$id_pais',$id_rol,$id_enlace,'$isread','$isupdate','$isdelete')";
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }

}
?>