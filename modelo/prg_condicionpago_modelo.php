<?php
class prg_condicionpago_model{
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

	public function select_condicionpago($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT * from prg_condicionpago where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

	public function select_condicionpagoByPais($id_pais,$id_condicion=null){
		unset($this->listas);
		$sql="SELECT distinct p.* FROM prg_condicionpago p INNER JOIN 
				prg_condicionpago_pais pp ON p.id_condicion=pp.id_condicion AND pp.id_pais='$id_pais' ";
		if(!empty($id_condicion))
				$sql.=" or p.id_condicion=$id_condicion ";
				
		$sql.="	
				WHERE p.flag='1'
				ORDER BY p.descripcion";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_condicionpago($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_condicionpago
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_condicionpago($id_condicion){
		
		$sql="SELECT * from prg_condicionpago where id_condicion=$id_condicion ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_condicionpago($descripcion,$dia,$usuario,$ip){

        $sql="insert into prg_condicionpago(descripcion,dia,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$descripcion','$dia','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_condicionpago($id_condicion,$descripcion,$dia,$usuario,$ip){
	   
        $sql="update prg_condicionpago 
				set descripcion='$descripcion',dia='$dia',usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_condicion=$id_condicion ";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_condicionpago($id_condicion){
	   
        $sql="update prg_condicionpago set flag='0' where id_condicion=$id_condicion";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	


	public function select_condicionpago_pais($id_condicion){
		unset($this->listas);
		$sql="SELECT prg_paises.id_pais, nombre ,IFNULL(prg_condicionpago_pais.id_pais,'') AS  refpais 
			FROM prg_paises LEFT JOIN 
				prg_condicionpago_pais ON prg_paises.id_pais=prg_condicionpago_pais.id_pais 
					AND prg_condicionpago_pais.id_condicion=$id_condicion
			WHERE flag='1' 
			ORDER BY nombre";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function delete_condicionpago_pais($id_condicion){
	   
        $sql="delete from prg_condicionpago_pais where id_condicion=$id_condicion";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function insert_condicionpago_pais($id_condicion,$id_pais){
	   
        $sql="insert into prg_condicionpago_pais(id_condicion,id_pais) values ($id_condicion,'$id_pais')";
		echo $sql;
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	


}
?>