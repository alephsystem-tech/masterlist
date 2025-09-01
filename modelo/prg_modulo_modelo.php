<?php
class prg_modulo_model{
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

	
	public function select_modulo($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *
				FROM prg_prog_modulo 
				WHERE flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_modulo($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_prog_modulo
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_modulo($id_modulo){
		
		$sql="SELECT * from prg_prog_modulo where id_modulo=$id_modulo ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_modulo($modulo,$iniciales,$id_pais,$usuario,$ip){

        $sql="insert into prg_prog_modulo(modulo,iniciales,id_pais,flag,
				usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$modulo','$iniciales','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_modulo($id_modulo,$modulo,$iniciales,$id_pais,$usuario,$ip){
	   
        $sql="update prg_prog_modulo 
				set modulo='$modulo',
					iniciales='$iniciales',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_modulo=$id_modulo and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_modulo($id_modulo){
	   
        $sql="update prg_prog_modulo set flag='0' where id_modulo=$id_modulo";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	
	
}
?>