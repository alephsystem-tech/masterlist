<?php
class mae_pais_model{
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

	public function selec_paises(){
		unset($this->listas);
		$sql="select *,ifnull(otronombre,nombre) as paisfull from t_mae_pais where flag='1' order by nombre";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_one_paisby_pais($id_pais){
		unset($this->listas);
		$sql="select * from t_mae_pais where codpostal='$id_pais' ";
		$consulta=$this->db->consultarOne($sql);
		
        return $consulta;
		
	}
	
	

}
?>