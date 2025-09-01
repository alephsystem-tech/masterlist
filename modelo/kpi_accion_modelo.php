<?php
class kpi_accion_model{
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

	public function select_kpiaccion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT * from kpi_accion where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	// total de registros 
	public function selec_total_kpiaccion($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            kpi_accion
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_kpiaccion($codaccion){
		
		$sql="SELECT *
				from kpi_accion where codaccion=$codaccion ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_kpiaccion($tipokpi,$dscaccion,$valor,$minimo,$maximo,$id_pais,$usuario,$ip){

        $sql="insert into kpi_accion (id_pais,tipokpi,accion, valor,minimo,maximo,  flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$id_pais','$tipokpi','$dscaccion','$valor','$minimo','$maximo','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_kpiaccion($tipokpi,$codaccion,$dscaccion,$valor,$minimo,$maximo,$id_pais,$usuario,$ip){
	   
        $sql="update kpi_accion 
				set accion='$dscaccion',valor='$valor',
				minimo='$minimo',
				maximo='$maximo',
				tipokpi='$tipokpi',
				usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codaccion=$codaccion ";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_kpiaccion($codaccion){
	   
        $sql="update kpi_accion set flag='0' where codaccion=$codaccion";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

}
?>