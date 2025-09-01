<?php
class prg_actividad_model{
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

	public function selec_actividadesByRol($id_rol){
		unset($this->listas);
		$sql="select distinct prg_actividad.* 
			from prg_actividad inner join prg_actividadxrol on prg_actividad.id_actividad = prg_actividadxrol.id_actividad 
			where prg_actividad.flag='1' and id_rol in ($id_rol) order by actividad";
			
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_actividadesByAuditor($id_auditor,$id_pais,$flgactivo=null){
		unset($this->listas);
		$sql="select distinct prg_actividad.* 
			from prg_actividad inner join prg_actividadxrol on prg_actividad.id_actividad = prg_actividadxrol.id_actividad 
			where prg_actividad.flag='1' and id_rol in (select id_rol from prg_auditorxrol where id_auditor=$id_auditor) 
				and prg_actividad.id_pais='$id_pais' ";
		if(!empty($flgactivo)) $sql.=" and prg_actividad.flgactivo='$flgactivo'";
		$sql.="	order by actividad";
			
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_actividad_flag($id_actividad){

        unset($this->listas);
		$sql="select 
			ifnull(flgproyecto,0) as flgproyecto ,
			ifnull(flgrendir,0) as flgrendir 
		from prg_actividad 
		where flag='1' and id_actividad=$id_actividad";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	
	
	public function select_actividad($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_pais){
		unset($this->listas);
		$sql="SELECT DISTINCT prg_actividad.id_actividad, prg_actividad.actividad ,
					ifnull(prg_auditoractividad.id_actividad,0) as flgactividad,
					ifnull(prg_actividad.flgactivo,0) as flgactivo,
					ifnull(group_concat(distinct prg_tipoactividad.descripcion separator  ', '),'') as relacion,
					flganalisis, case flganalisis when '1' then 'Si' else 'No' end as dscanalisis,
					flgproyecto, case flgproyecto when '1' then 'Si' else 'No' end as dscproyecto,
					flgeditacalendar, case flgeditacalendar  when '1' then 'Si' else 'No' end as dscedita,
					flgrendir, case flgrendir  when '1' then 'Si' else 'No' end as dscrendir,
					ifnull(vista.rol,'') as roles
				FROM prg_actividad left join 
					 prg_auditoractividad on   prg_actividad.id_actividad =prg_auditoractividad .id_actividad 
						AND prg_auditoractividad.id_pais='$id_pais' left join 
					 prg_tipoactividad on prg_actividad.id_actividad=prg_tipoactividad.codrelacion  
						AND prg_tipoactividad.id_pais='$id_pais' left join 
						(	select group_concat(distinct prg_roles.nombre separator  ', ') as rol, id_actividad
							from prg_actividadxrol inner join 
								 prg_roles on prg_actividadxrol.id_rol=prg_roles.id_rol
							group by id_actividad ) as vista on prg_actividad.id_actividad=vista.id_actividad
				where prg_actividad.flag='1'  $searchQuery ";
		$sql.=" group by id_actividad ";	
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_relacion_actividad($id_actividad,$id_pais){
		unset($this->listas);
		$sql="SELECT id_tipoactividad, descripcion, IFNULL(codrelacion,'0') AS codrelacion
              FROM prg_tipoactividad 
              WHERE flag='1' AND id_pais='$id_pais' and ( ifnull(codrelacion,0)=0 or codrelacion='$id_actividad') 
              ORDER BY descripcion" ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_rol_actividad($id_actividad,$id_pais){
		unset($this->listas);
		 $sql="select prg_roles.id_rol, prg_roles.nombre, ifnull(prg_actividadxrol.id_rol,0) as flgrol
                 from prg_roles left join prg_actividadxrol on prg_roles.id_rol=prg_actividadxrol.id_rol 
					and id_actividad=$id_actividad
				where prg_roles.flag='1' 
				order by nombre";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	// total de registros por auditor fecha
	public function selec_total_actividad($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_actividad
        WHERE flag='1' $searchQuery " ;
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_actividad($id_actividad){
		
		$sql="SELECT * from prg_actividad where id_actividad=$id_actividad ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_actividad($actividad,$flganalisis,$flgproyecto,$flgeditacalendar,$flgrendir,$id_pais,$usuario,$ip){

        $sql="insert into prg_actividad(actividad,flganalisis,flgproyecto,flgeditacalendar,flgrendir,id_pais,flag,usuario_ingreso,
					fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$actividad','$flganalisis','$flgproyecto','$flgeditacalendar','$flgrendir','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_actividad($id_actividad,$actividad,$flganalisis,$flgproyecto,$flgeditacalendar,$flgrendir,$id_pais,$usuario,$ip){
	   
        $sql="update prg_actividad 
				set actividad='$actividad',
				flganalisis='$flganalisis',
				flgproyecto='$flgproyecto',
				id_pais='$id_pais',
				flgeditacalendar='$flgeditacalendar',
				flgrendir='$flgrendir',
				usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_actividad=$id_actividad ";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_actividad($id_actividad){
        $sql="update prg_actividad set flag='0' where id_actividad=$id_actividad";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function activa_actividad($id_actividad,$flgactivo){
        $sql="update prg_actividad set flgactivo='$flgactivo' where id_actividad=$id_actividad";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	public function delete_actividadRelacion($id_actividad,$id_pais,$usuario,$ip){
       $sql="update prg_tipoactividad set codrelacion=0 where codrelacion=$id_actividad and id_pais='$id_pais' ";  
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

	public function insert_actividadRelacion($id_actividad,$id_tipoactividad,$id_pais,$usuario,$ip){
       $sql="update prg_tipoactividad set codrelacion=$id_actividad where id_tipoactividad=$id_tipoactividad ";  
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	
	public function delete_actividadRol($id_actividad,$id_pais,$usuario,$ip){
        $sql="delete from prg_actividadxrol where id_actividad=$id_actividad"; 
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

	public function insert_actividadRol($id_actividad,$id_rol,$id_pais,$usuario,$ip){
        $sql="insert into prg_actividadxrol(id_actividad,id_rol)values ($id_actividad,$id_rol) ";  
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
}
?>