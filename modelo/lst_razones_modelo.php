<?php
class lst_razones_model{
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

	public function select_razones($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *
				from lst_razones where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_razones($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            lst_razones
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_razon($codrazon){
		
		$sql="SELECT * from lst_razones where codrazon=$codrazon ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_razones($razon,$id_pais,$usuario,$ip){

        $sql="insert into lst_razones (razon,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$razon','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_razones($codrazon,$razon,$id_pais,$usuario,$ip){
	   
        $sql="update lst_razones 
				set razon='$razon',usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codrazon=$codrazon and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_razones($codrazon){
	   
        $sql="update lst_razones set flag='0' where codrazon=$codrazon";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

}
?>