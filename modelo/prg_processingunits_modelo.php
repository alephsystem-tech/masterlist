<?php
class prg_processingunits_model{
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

	public function select_processingunits($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT id_process_unit,unit_ref,unit_name,relation,city,country,project_ref
		from prg_processingunits where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_processingunits($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_processingunits
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_processingunits($id_process_unit){
		
		$sql="SELECT *
				FROM prg_processingunits
				WHERE id_process_unit=$id_process_unit";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_processingunits($unit_ref,$unit_name,$relation,$city,$country,$project_ref,$id_pais,$usuario,$ip){

        $sql="insert into prg_processingunits
			(unit_ref,unit_name,relation,city,country,project_ref,
			id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$unit_ref','$unit_name','$relation','$city','$country','$project_ref','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";

		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_processingunits($id_process_unit,$unit_ref,$unit_name,$relation,$city,$country,$project_ref,$id_pais,$usuario,$ip){
	   
        $sql="update prg_processingunits
				set 
				unit_ref='$unit_ref',unit_name='$unit_name',
				relation='$relation',city='$city',country='$country',
				project_ref='$project_ref',
				usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_process_unit=$id_process_unit and id_pais='$id_pais'";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_processingunits($id_process_unit){
	   
        $sql="update prg_processingunits set flag='0' where id_process_unit=$id_process_unit";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
		
	
	public function insert_processingunitsFormFile($data_sql,$codunico,$id_pais){
		$sql=" insert into prg_tmp_processing(
				fecha_importacion,
				unit_ref,
				unit_name,
				client_ref,
				europe_id,
				ships,
				unit_status,
				unit_certification,
				validity,
				relation_ID,
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
				prj_status,
				proj_certif_date,
				products,
				project_relation,
				project_address,
				project_postal_code,
				project_city,
				project_country,
				project_telephone,
				project_fax_vc,
				project_email_vc,
				last_date,
				certified,
				website,
				responsible_office,
				id_pais
		) values $data_sql";
		$consulta=$this->db->executeIns($sql);
		
        return $consulta;
	}
	
	public function select_processingunitsFormFile($codunico,$id_pais){
		$sql=" select count(*) as total from prg_tmp_processing where id_pais='$id_pais' and fecha_importacion='$codunico'";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	
	public function select_processingunitsFormFileMigrado($codunico,$id_pais){
		$sql="SELECT count(*) as total
			FROM  prg_tmp_processing 
			WHERE fecha_importacion = '$codunico' AND is_importar = 1 
				AND id_pais='$id_pais' AND TRIM(unit_ref) NOT IN 
						(SELECT TRIM(unit_ref) 
									FROM prg_processingunits WHERE id_pais=_id_pais
						) ";
			
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	public function procedure_processingunitsFormFile($codunico,$id_pais){
		//$sql="call prg_sp_procesarImportacionProcessing('$codunico','$id_pais')";
		//$consulta=$this->db->execute($sql);
		
		$sql="call prg_sp_actualizarInformacionProcessing('$codunico','$id_pais')";
		$consulta=$this->db->execute($sql);
		
        return $consulta;
	}
	
}
?>