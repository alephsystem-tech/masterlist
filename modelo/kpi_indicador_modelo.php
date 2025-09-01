<?php
class kpi_indicador_model{
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

	public function selec_categoria($id_pais,$tipokpi){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT * from kpi_categoria where flag='1' and tipokpi='$tipokpi' order by categoria";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}

	public function select_kpiindicador($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT kpi_indicador.*,categoria
				from kpi_indicador inner join kpi_categoria on kpi_indicador.codcategoria=kpi_categoria.codcategoria
				where kpi_indicador.flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	// total de registros 
	public function selec_total_kpiindicador($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            kpi_indicador
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_kpiindicador($codindicador){
		
		$sql="SELECT *
				from kpi_indicador where codindicador=$codindicador ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_kpiindicador($tipokpi,$indicador,$codigo,$codcategoria,$id_pais,$usuario,$ip){

        $sql="insert into kpi_indicador (tipokpi,indicador,codigo,codcategoria,id_pais,flag, usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$tipokpi','$indicador','$codigo','$codcategoria','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_kpiindicador($tipokpi,$codindicador,$indicador,$codigo,$codcategoria,$id_pais,$usuario,$ip){
	   
        $sql="update kpi_indicador 
				set indicador='$indicador',codigo='$codigo',codcategoria='$codcategoria',
				tipokpi='$tipokpi',usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codindicador=$codindicador and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_kpiindicador($codindicador){
	   
        $sql="update kpi_indicador set flag='0' where codindicador=$codindicador";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

}
?>