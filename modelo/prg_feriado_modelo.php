<?php
class prg_feriado_model{
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

	public function select_feriadoCalendar($inicio,$fin,$id_pais){
		unset($this->listas);
		$sql="SELECT id_feriado,descripcion,fecha, 
				date_format(fecha,'%Y-%m-%d 08:00:00') as inicio,
				date_format(fecha,'%Y-%m-%d 17:00:00') as fin
				from prg_feriado 
				where flag='1' and to_days(fecha)>=to_days('$inicio')   and to_days(fecha)<=to_days('$fin') 
					and id_pais='$id_pais' ";
		
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	public function select_feriado($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *, date_format(fecha,'%d/%m/%Y') as fechaf 
				from prg_feriado where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_feriado($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_feriado
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_feriado($id_feriado){
		
		$sql="SELECT *, date_format(fecha,'%d/%m/%Y') as fechaf  
				from prg_feriado where id_feriado=$id_feriado ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_feriado($descripcion,$fecha,$id_pais,$usuario,$ip){

        $sql="insert into prg_feriado(descripcion,fecha,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$descripcion','$fecha','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_feriado($id_feriado,$descripcion,$fecha,$id_pais,$usuario,$ip){
	   
        $sql="update prg_feriado 
				set descripcion='$descripcion',fecha='$fecha',usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_feriado=$id_feriado and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_feriado($id_feriado){
	   
        $sql="update prg_feriado set flag='0' where id_feriado=$id_feriado";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	
	public function trigert_actividad($id_feriado,$fecha,$descripcion,$id_pais){
	   
        $sql="INSERT INTO prg_auditoractividad(flgfinalizo,oferta,id_actividad,id_programa,id_pais,
			nota,porcentaje,project_id,id_auditor,flag,
			fecha,ref_pais,fechac,ciclo,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica) 
			SELECT '','','62','851','175','$descripcion',100,'',id_auditor,'1','$fecha',
				'$id_pais',NULL,'','feriado',NOW(),'trigert','$id_feriado'
			FROM prg_auditor 
			WHERE id_pais='$id_pais' AND flag='1' AND flgstatus='1';";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function trigert_calendario($id_feriado,$id_pais,$id_tipoactividad, $project_id,$nro_muestra, $por_dia, $id_auditor, $monto_dolares, $monto_soles,
	 $id_estadoactividad, $auditoria, $id_type, $observacion,$hora_inicial, $hora_final, $dia_inicio, $mes_inicio,
	 $anio_inicio, $dia_fin, $mes_fin, $anio_fin, $inicio_evento,  $fin_evento, $is_sabado, $is_domingo,
	 $hora_inicio, $hora_fin, $asunto, $id_calendario, $id_asignacion_viaticos,	 $flag_rendicion, $usuario, $ip){
		
		
		$sql = "insert into prg_calendario (
			id_pais,id_tipoactividad, id_proyecto,nro_muestra, por_dia, id_auditor, 
			 monto_dolares, monto_soles, id_estadoactividad, audit_id, id_type, observacion, 
			 hora_inicial, hora_final, dia_inicio, mes_inicio, anio_inicio, dia_fin, mes_fin,
			 anio_fin, inicio_evento, fin_evento, is_sabado, is_domingo, hora_inicio, hora_fin,
			 asunto, id_calendario, id_asignacion_viaticos, flag_rendicion, 
			 usuario_ingreso,ip_ingreso,fecha_ingreso,flag	)
			 
		 select '$id_pais',$id_tipoactividad, '$project_id','$nro_muestra', '$por_dia', id_auditor,
		 '$monto_dolares', '$monto_soles', $id_estadoactividad, '$auditoria', '$id_type', '$observacion', 
		 '$hora_inicial', '$hora_final', '$dia_inicio', '$mes_inicio', '$anio_inicio', '$dia_fin', '$mes_fin',
		 '$anio_fin', '$inicio_evento',  '$fin_evento', '$is_sabado', '$is_domingo', '$hora_inicio', '$hora_fin',
		 '$asunto', $id_calendario, '$id_asignacion_viaticos',	 '$flag_rendicion', 
		 '$id_feriado','trigert',now(),'1'
		 from prg_auditor 
			WHERE id_pais='$id_pais' AND flag='1' AND flgstatus='1' ";
		
		$consulta=$this->db->execute($sql);
		
		return $consulta;
	}
	
	
    public function delete_actividad($id_feriado){
	   
        $sql="update prg_auditoractividad 
				set flag='0' 
				where usuario_modifica=$id_feriado and usuario_ingreso='feriado' and ip_ingreso='trigert'";
		$consulta=$this->db->execute($sql);
		
		 $sql="update prg_calendario 
				set flag='0' 
				where usuario_ingreso=$id_feriado and ip_ingreso='trigert'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function selec_data_auditor($id_feriado){
		
		$sql="SELECT ifnull(group_concat(id_auditor),'') as gauditor
				from prg_feriadoxauditor
				WHERE id_feriado = $id_feriado 
				group by id_feriado";
			
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function delete_auditorxferiado($id_feriado){
	   $sql="delete from  prg_feriadoxauditor where id_feriado=$id_feriado ";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	 public function insert_auditorxferiado($id_feriado,$id_auditor){
	   $sql="insert into prg_feriadoxauditor(id_feriado,id_auditor) values ($id_feriado,$id_auditor)";
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }
	
}
?>