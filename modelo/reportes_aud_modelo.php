<?php
class reportes_aud_model{
    private $db;
	private $listas;
    public function __construct(){
        $this->db=new DBManejador();
		$this->listas=array();
    }
    /****************************************
		reporte de detalle de proyecto
	*****************************************/

	public function select_actByAuditor($id_auditor,$id_pais,$start,$end){
		unset($this->listas);
		$sql="	SELECT id, DATE_FORMAT(inicio_evento,'%Y-%m-%d') AS inicio ,
				DATE_FORMAT(fin_evento,'%Y-%m-%d') AS fin 
				FROM prg_calendario  
				WHERE flag='1' AND id_pais='$id_pais'
					AND id_auditor=$id_auditor 
					AND DATE_ADD(inicio_evento,INTERVAL 30 DAY) >= '$start' 
					AND DATE(fin_evento) <= '$end'" ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_total_reporte_detProyecto($searchQuery){
		
		$sql="SELECT COUNT(DISTINCT prg_proyecto.id_proyecto) AS total
			FROM prg_proyecto LEFT JOIN 
				 prg_proyecto_programa ON prg_proyecto.project_id= prg_proyecto_programa.project_id   INNER JOIN
				  prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto 
			WHERE  prg_proyecto.flag = '1'  $searchQuery " ;
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
}	
?>