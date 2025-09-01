<?php
class prg_cat_programa_model{
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

	public function select_categoria($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *	from prg_cat_programa where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_categoria($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
				FROM prg_cat_programa
				WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_categoria($id_categoria){
		
		$sql="SELECT * from prg_cat_programa where id_categoria=$id_categoria ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_categoria($categoria,$usuario,$ip){

        $sql="insert into prg_cat_programa(categoria,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$categoria','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_categoria($id_categoria,$categoria,$usuario,$ip){
	   
        $sql="update prg_cat_programa 
				set categoria='$categoria',usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_categoria=$id_categoria";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_categoria($id_categoria){
	   
        $sql="update prg_cat_programa set flag='0' where id_categoria=$id_categoria";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

}
?>