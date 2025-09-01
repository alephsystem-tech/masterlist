<?php
class prg_variedad_model{
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

	public function select_variedad($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT v.*, ifnull(c.cultivo,'') as cultivo 
				FROM lst_variedad v LEFT JOIN lst_cultivo c ON v.codcultivo=c.codcultivo
				WHERE v.flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_variedad($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total  
			FROM 
            lst_variedad v
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_variedad($codvariedad){
		
		$sql="SELECT * from lst_variedad where codvariedad=$codvariedad ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_variedad($codcultivo,$variedad,$id_pais,$usuario,$ip){

        $sql="insert into lst_variedad(codcultivo,variedad,
			id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$codcultivo','$variedad','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_variedad($codvariedad,$codcultivo,$variedad,$id_pais,$usuario,$ip){
	   
        $sql="update lst_variedad 
				set codcultivo='$codcultivo',variedad='$variedad',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codvariedad=$codvariedad and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_variedad($codvariedad){
	   
        $sql="update lst_variedad set flag='0' where codvariedad=$codvariedad";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	public function selec_cultivos($id_pais){
		unset($this->listas);
		$sql="SELECT *
				FROM lst_cultivo
				WHERE flag='1'  and id_pais='$id_pais'";
		$sql.=" order by cultivo ";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}

}
?>