<?php
class prg_cultivo_model{
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

	public function select_cultivos($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *
				from lst_cultivo where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_cultivo($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            lst_cultivo
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_cultivo($codcultivo){
		
		$sql="SELECT * from lst_cultivo where codcultivo=$codcultivo ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_cultivo($cultivo,$pesos,$semana_antes,$semana_despues,$id_pais,$usuario,$ip){

        $sql="insert into lst_cultivo(cultivo,pesos,semana_antes,semana_despues,
			id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$cultivo','$pesos',
			'$semana_antes','$semana_despues',
			'$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_cultivo($codcultivo,$cultivo,$pesos,$semana_antes,$semana_despues,$id_pais,$usuario,$ip){
	   
        $sql="update lst_cultivo 
				set cultivo='$cultivo',pesos='$pesos',
					semana_antes='$semana_antes',
					semana_despues='$semana_despues',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codcultivo=$codcultivo and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_cultivo($codcultivo){
	   
        $sql="update lst_cultivo set flag='0' where codcultivo=$codcultivo";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

}
?>