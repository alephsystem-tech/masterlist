<?php
class prg_programa_model{
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

	public function selec_programasbypais($id_pais,$flgactivo=null){
		unset($this->listas);
		$sql="select *, CONCAT_WS('=>',iniciales, descripcion) AS programa , iniciales, id_grupoprograma
				from prg_programa where flag='1' AND descripcion!=''  and id_pais='$id_pais' ";
		if($flgactivo) 		$sql.="	and flgactivo='$flgactivo'";
		$sql.="		order by descripcion";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_cat_programa(){
		unset($this->listas);
		$sql="select * from prg_cat_programa where flag='1' order by categoria";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_data_modulo($id_programa){
		
		$sql="SELECT ifnull(group_concat(id_modulo),'') as gmodulo
				from prg_programaxmodulo 
				WHERE id_programa = $id_programa 
				group by id_programa";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	
	
	public function select_programa($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT p.id_programa,iniciales, IFNULL(codigo,'') AS codigo ,descripcion,prg_programa_grupo.id_grupoprograma,
					hora_informe,categoria,	GENERAL,negocio,flgactivo,
					IFNULL(prg_programa_grupo.grupo,'') AS grupo,
					ifnull(vista.modulo,'') as modulo
					
				FROM prg_programa p LEFT JOIN 
					prg_cat_programa c ON p.id_categoria=c.id_categoria LEFT JOIN
					prg_programa_grupo ON p.id_grupoprograma=prg_programa_grupo.id_grupoprograma left JOIN
					(	SELECT 
							GROUP_CONCAT(modulo separator  ' , ') AS modulo, 
							id_programa 
						FROM prg_prog_modulo INNER JOIN prg_programaxmodulo m ON prg_prog_modulo.id_modulo=m.id_modulo
						WHERE prg_prog_modulo.flag='1'
						GROUP BY id_programa
					) as vista on p.id_programa=vista.id_programa
				WHERE p.flag='1' AND p.descripcion!=''  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_modulosbyselec($id_pais){
		unset($this->listas);
		$sql="select id_modulo, modulo 
			from prg_prog_modulo 
			where flag='1' and id_pais='$id_pais' 
			order by modulo " ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_modulosxpogramabyselec($id_pais){
		unset($this->listas);
		$sql="SELECT m.id_modulo, modulo, p.id_programa
				FROM prg_prog_modulo m INNER JOIN prg_programaxmodulo p ON m.id_modulo=p.id_modulo
				WHERE flag='1' AND id_pais='$id_pais' 
				ORDER BY modulo " ;

		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_programa($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_programa p
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_programa($id_programa){
		
		$sql="SELECT * from prg_programa where id_programa=$id_programa ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_programa($descripcion,$iniciales,$hora_informe,$codigo,$id_categoria,$id_pais,$usuario,$ip,$general,$negocio,$id_grupoprograma){

        $sql="insert into prg_programa(descripcion,iniciales,hora_informe,codigo,id_categoria,general,negocio,id_pais,id_grupoprograma,flag,
				usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$descripcion','$iniciales','$hora_informe','$codigo','$id_categoria','$general','$negocio','$id_pais','$id_grupoprograma','1',
				'$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_programa($id_programa,$descripcion,$iniciales,$hora_informe,$codigo,$id_categoria,$id_pais,$usuario,$ip,$general,$negocio,$id_grupoprograma){
	   
        $sql="update prg_programa 
				set descripcion='$descripcion',
					id_categoria='$id_categoria',
					id_grupoprograma='$id_grupoprograma',
					general='$general',
					negocio='$negocio',
					iniciales='$iniciales',codigo='$codigo',hora_informe='$hora_informe',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_programa=$id_programa and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_programa($id_programa){
	   
        $sql="update prg_programa set flag='0' where id_programa=$id_programa";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }

	public function delete_programaxmodulo($id_programa){
	   
        $sql="delete from prg_programaxmodulo where id_programa=$id_programa";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	public function insert_programaxmodulo($id_programa,$id_modulo){
	   
        $sql="insert into prg_programaxmodulo (id_programa,id_modulo) values ($id_programa,$id_modulo)";
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }
	
	
	
	 public function activa_programa($id_programa,$flgactivo){
	   
        $sql="update prg_programa set flgactivo='$flgactivo' where id_programa=$id_programa";
		echo $sql;
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	
	// grupo de programa
	//********************************

	public function selec_grupoprogramasbypais($id_pais){
		unset($this->listas);
		$sql="select *, CONCAT_WS('=>',abreviatura, grupo) AS grupo , abreviatura
				from prg_programa_grupo where flag='1'  and id_pais='$id_pais'
				order by grupo";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	public function select_grupoprograma($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *
				FROM prg_programa_grupo
				WHERE flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_grupoprograma($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_programa_grupo
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_grupoprograma($id_grupoprograma){
		
		$sql="SELECT * from prg_programa_grupo where id_grupoprograma=$id_grupoprograma ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function insert_grupoprograma($grupo,$abreviatura,$id_pais,$usuario,$ip){

        $sql="insert into prg_programa_grupo(grupo,abreviatura,id_pais,flag,
				usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$grupo','$abreviatura','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_grupoprograma($id_grupoprograma,$grupo,$abreviatura,$id_pais,$usuario,$ip){
	   
        $sql="update prg_programa_grupo 
				set grupo='$grupo',
					abreviatura='$abreviatura',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_grupoprograma=$id_grupoprograma and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
		
        return $consulta;
    }
	
	 public function delete_grupoprograma($id_grupoprograma){
	   
        $sql="update prg_programa_grupo set flag='0' where id_grupoprograma=$id_grupoprograma";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	
}
?>