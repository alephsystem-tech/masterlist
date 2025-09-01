<?php
class centrocosto_model{
    private $db;
	private $listas;
    public function __construct(){
        $this->db=new DBManejador();
		$this->listas=array();
    }
    /* MODELO para seleccionar  usuarios
        mayo 2020
		Autor: Enrique Bazalar alephsystem@gmail.com
    */
	

	public function selec_centrocosto(){
		unset($this->listas);
		$sql="SELECT * from t_mae_centrocosto where flag='1'  order by nombre";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}


	// seleccionar un usuario
    public function select_one_centrocosto($id_centrocosto){
        
        $sql="select * from t_mae_centrocosto where id_centro_costo=$id_centrocosto ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
    }
	
	

}
?>