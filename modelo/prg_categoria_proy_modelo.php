<?php
class prg_categoria_proy_model{
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

	public function select_categoriaproy($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *	from prg_categoria_proy where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}


	// total de registros por auditor fecha
	public function selec_total_categoriaproy($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_categoria_proy
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_categoriaproy($codcategoria){
		
		$sql="SELECT *
				from prg_categoria_proy where codcategoria=$codcategoria ";
			
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_categoriaproy($categoria,$id_pais,$usuario,$ip){

        $sql="insert into prg_categoria_proy(categoria,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$categoria','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_categoriaproy($codcategoria,$categoria,$id_pais,$usuario,$ip){
	   
        $sql="update prg_categoria_proy 
				set categoria='$categoria',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codcategoria=$codcategoria ";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_categoriaproy($codcategoria){
	   
        $sql="update prg_categoria_proy set flag='0' where codcategoria=$codcategoria";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

}
?>