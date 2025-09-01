<?php
class prg_region_model{
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

	
	public function select_regiones($id_pais){
		unset($this->listas);
		$sql="SELECT id_region,descripcion	from prg_region where flag='1' and id_pais='$id_pais' order by descripcion";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
}
?>