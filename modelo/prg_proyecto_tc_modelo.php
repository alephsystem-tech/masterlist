<?php
class prg_proyecto_tc_model{
    private $db;
	private $listas;
    public function __construct(){
        $this->db=new DBManejador();
		$this->listas=array();
    }
    /* MODELO para seleccionar  tc
        abril 2023
		Autor: Enrique Bazalar alephsystem@gmail.com
    */

	public function select_responsabletc($id_pais){
		unset($this->listas);
		$sql="SELECT 
				Auditor.id_auditor,
				CONCAT_WS(' ',Auditor.nombre, Auditor.apepaterno,Auditor.apematerno) AS nombreCompleto 
			 FROM prg_auditor AS Auditor inner JOIN 
				prg_usuarios AS Usuario ON Usuario.id_auditor = Auditor.id_auditor 
			 WHERE  Auditor.flag = '1' 
				AND Auditor.id_auditor <> 0 AND Usuario.id_auditor > 0 AND Usuario.flag = '1'  
				and Auditor.id_pais='$id_pais' and flgtc='1'
			order by 2 asc";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

	public function select_proyectotc($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT prg_proyecto.id_proyecto, prg_proyecto.project_id, trim(proyect) as proyect, city, state, country, 
					telephone, mobile, fax, prg_auditor.email,
						ifnull(vista.cantidad,0) AS cantidad,
						'' estado,
						concat_ws(' ',nombre,apepaterno,apematerno) responsable
					FROM prg_proyecto left JOIN 
						 prg_auditor ON prg_proyecto.id_auditortc= prg_auditor.id_auditor	
					LEFT JOIN 
					(
						SELECT id_proyecto, 
							count(*) AS cantidad
						FROM prg_proyectotc
						WHERE  flag='1'  
						GROUP BY coddetalle  
					) AS vista ON prg_proyecto.id_proyecto=vista.id_proyecto  
					WHERE prg_proyecto.flag = '1' and prg_proyecto.project_id!=''   $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;

		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_proyectotc($searchQuery=null){
		$sql=" SELECT COUNT(DISTINCT prg_proyecto.project_id) AS total 
			FROM prg_proyecto 
        WHERE flag='1' $searchQuery ";
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
}
?>