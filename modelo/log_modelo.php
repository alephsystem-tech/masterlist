<?php
class log_model{
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

	public function select_log($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *,date_format(fecha_ingreso,'%d/%m/%y') as fecha
              FROM mp_log
	      WHERE 1 = 1  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	// total de registros por auditor fecha
	public function selec_total_log($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total FROM mp_log WHERE 1=1 $searchQuery " ;
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	// total de registros por auditor fecha
	public function selec_modulos($id_pais){
		$sql=" SELECT distinct modulo FROM mp_log WHERE id_pais='$id_pais' " ;
		
		$consulta=$this->db->consultar($sql);		
		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_one_log($codlog){
		
		$sql="SELECT * from mp_log where codlog=$codlog ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function insert_log($id,$tabla,$campo,$modulo,$final,$inicial,$id_pais,$usuario,$ip){
        $sql="insert mp_log(id,tabla, campo,modulo, final,inicial,id_pais,usuario_ingreso,ip_ingreso,fecha_ingreso) 
			values ($id,'$tabla', '$campo','$modulo', '$final','$inicial','$id_pais','$usuario','$ip',now())";
		
		$consulta=$this->db->executeIns($sql);
		return $consulta;
    }	


	public function delete_log($codlog){
        $sql="update etiqueta set flag='0' where codetiqueta=$codlog";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	
}
?>