<?php
class prg_mediotransporte_model{
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

	public function select_mediotransporte($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *	from prg_mediotransporte where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_mediotransporte($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_mediotransporte
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_mediotransporte($id_mediotransporte){
		
		$sql="SELECT * from prg_mediotransporte where id_mediotransporte=$id_mediotransporte ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_mediotransporte($descripcion,$id_pais,$usuario,$ip){

        $sql="insert into prg_mediotransporte(descripcion,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$descripcion','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_mediotransporte($id_mediotransporte,$descripcion,$id_pais,$usuario,$ip){
	   
        $sql="update prg_mediotransporte 
				set descripcion='$descripcion',usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_mediotransporte=$id_mediotransporte and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_mediotransporte($id_mediotransporte){
	   
        $sql="update prg_mediotransporte set flag='0' where id_mediotransporte=$id_mediotransporte";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

}
?>