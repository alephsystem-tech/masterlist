<?php
class prg_producto_model{
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

	public function select_producto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *	from prg_producto where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_producto($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
				FROM prg_producto
				WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_producto($codproducto){
		
		$sql="SELECT * from prg_producto where codproducto=$codproducto ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_producto($producto,$id_pais,$usuario,$ip){

        $sql="insert into prg_producto(producto,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$producto','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_producto($codproducto,$producto,$usuario,$ip){
	   
        $sql="update prg_producto 
				set producto='$producto',usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codproducto=$codproducto";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_producto($codproducto){
	   
        $sql="update prg_producto set flag='0' where codproducto=$codproducto";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

}
?>