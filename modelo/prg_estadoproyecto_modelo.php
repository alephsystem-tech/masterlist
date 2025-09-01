<?php
class prg_estadoproyecto_model{
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

	public function select_estadoproyecto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT e.* , ifnull(g.descripcion,'') as grupo
			from prg_estadoproyecto e left join prg_estadoproyecto_grupo g on e.id_grupo=g.id_grupo
				and e.id_pais=g.id_pais
			where e.flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_estadoproyectoByPais($id_pais,$flgactivo=null,$codestado=null){
		unset($this->listas);
		$sql="select * from prg_estadoproyecto where flag='1' and id_pais = '$id_pais'";
		
		if(!empty($flgactivo) and !empty($codestado))
			$sql.=" and ( flgactivo='$flgactivo' or codestado=$codestado ) ";
			
		else if(!empty($flgactivo))
			$sql.=" and flgactivo='$flgactivo'";
		
		$sql.=" order by descripcion ";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	

	// total de registros por auditor fecha
	public function selec_total_estadoproyecto($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_estadoproyecto e
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_estadoproyecto($codestado){
		
		$sql="SELECT * from prg_estadoproyecto where codestado=$codestado ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function select_grupoestadoproyecto($id_pais){
		unset($this->listas);
		$sql="select * from prg_estadoproyecto_grupo where flag='1' and id_pais='$id_pais' order by descripcion ";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	 public function insert_estadoproyecto($descripcion,$id_grupo,$id_pais,$usuario,$ip){

        $sql="insert into prg_estadoproyecto(descripcion,id_grupo,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$descripcion','$id_grupo','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_estadoproyecto($codestado,$descripcion,$id_grupo,$id_pais,$usuario,$ip){
	   
        $sql="update prg_estadoproyecto 
				set 
					descripcion='$descripcion',id_grupo='$id_grupo',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codestado=$codestado and id_pais='$id_pais'";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_estaproyecto($codestado){
	   
        $sql="update prg_estadoproyecto set flag='0' where codestado=$codestado";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

	public function activa_estado($codestado,$flgactivo){
		 
		$sql="update prg_estadoproyecto set flgactivo='$flgactivo' where codestado=$codestado";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
	 }
	
	
}
?>