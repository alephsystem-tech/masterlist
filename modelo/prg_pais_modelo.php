<?php
class prg_pais_model{
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
		$sql="SELECT * from prg_paises where flag='1' order by nombre";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_one_pais($id_pais){
		$sql="SELECT * from prg_paises where id_pais='$id_pais'";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
	}
	

}
?>