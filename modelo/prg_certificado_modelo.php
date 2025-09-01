<?php
class prg_certificado_model{
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

	public function select_certificados($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *
				from lst_certificadora where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_certificado($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            lst_certificadora
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_certificado($codcertificadora){
		
		$sql="SELECT * from lst_certificadora where codcertificadora=$codcertificadora ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_certificado($certificadora,$id_pais,$usuario,$ip){

        $sql="insert into lst_certificadora (certificadora,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$certificadora','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_certificado($codcertificadora,$certificadora,$id_pais,$usuario,$ip){
	   
        $sql="update lst_certificadora 
				set certificadora='$certificadora',usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codcertificadora=$codcertificadora and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_certificado($codcertificadora){
	   
        $sql="update lst_certificadora set flag='0' where codcertificadora=$codcertificadora";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

}
?>