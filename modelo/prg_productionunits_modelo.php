<?php
class prg_productionunits_model{
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

	public function select_productionunits($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT id_product_unit,unit_ref,unit_name,relation,city,country,project_ref
		from prg_productionunits where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_productionunits($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_productionunits
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_productionunits($id_product_unit){
		
		$sql="SELECT *
				FROM prg_productionunits
				WHERE id_product_unit=$id_product_unit";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_productionunits($unit_ref,$unit_name,$relation,$city,$country,$project_ref,$id_pais,$usuario,$ip){

        $sql="insert into prg_productionunits
			(unit_ref,unit_name,relation,city,country,project_ref,
			id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$unit_ref','$unit_name','$relation','$city','$country','$project_ref','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";

		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_productionunits($id_product_unit,$unit_ref,$unit_name,$relation,$city,$country,$project_ref,$id_pais,$usuario,$ip){
	   
        $sql="update prg_productionunits
				set 
				unit_ref='$unit_ref',unit_name='$unit_name',
				relation='$relation',city='$city',country='$country',
				project_ref='$project_ref',
				usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_product_unit=$id_product_unit and id_pais='$id_pais'";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_productionunits($id_product_unit){
	   
        $sql="update prg_productionunits set flag='0' where id_product_unit=$id_product_unit";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
		
	
	public function insert_productionunitsFormFile($data_sql,$codunico,$id_pais){
		$sql=" insert into prg_tmp_production(
				fecha_importacion,
				unit_ref,
				unit_name,
				client_ref,
				total_area,
				`fields`,
				wild_coll,
				small_farm,
				farmer,
				`current`,
				subprogram,
				validity,
				relation_id,
				relation,
				address,
				postal_code,
				city,
				country,
				telephone,
				fax_vc,
				email_vc,
				modules,
				project_ref,
				project_name,
				project_status,
				project_certification,
				id_pais
		) values $data_sql";
		$consulta=$this->db->executeIns($sql);
		
        return $consulta;
	}
	
	public function select_productionunitsFormFile($codunico,$id_pais){
		$sql=" select count(*) as total from prg_tmp_production where id_pais='$id_pais' and fecha_importacion='$codunico'";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	
	public function select_productionunitsFormFileMigrado($codunico,$id_pais){
		$sql="SELECT count(*) as total
			FROM  prg_tmp_production 
			WHERE fecha_importacion = '$codunico' AND is_importar = 1 
				AND id_pais='$id_pais' AND TRIM(unit_ref) NOT IN 
						(SELECT TRIM(unit_ref) 
									FROM prg_productionunits WHERE id_pais=_id_pais
						) ";
			
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	public function procedure_productionunitsFormFile($codunico,$id_pais){
		//$sql="call prg_sp_procesarImportacionProcessing('$codunico','$id_pais')";
		//$consulta=$this->db->execute($sql);
		
		$sql="call prg_sp_actualizarInformacionProduction('$codunico','$id_pais')";
		$consulta=$this->db->execute($sql);
		
        return $consulta;
	}
	
}
?>