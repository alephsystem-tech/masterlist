<?php
class auditoractividad_model{
    private $db;
	private $listas;
    public function __construct(){
        $this->db=new DBManejador();
		$this->listas=array();
    }
    /*****************************************************
		MODELO para tabla prg_auditoractividad
        junio 2021
		Autor: Enrique Bazalar alephsystem@gmail.com
    *******************************************************/

	// select de registros por auditor fecha
	public function selec_actividadesxauditor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$noferiado=null){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT distinct
            tmp_actividad,tmp_subprograma,prg_auditoractividad.id_actividad,prg_auditoractividad.id_auditor,
           id_auditactiv, t_mae_pais.nombre AS pais,prg_actividad.actividad , 
           prg_programa.descripcion AS subprograma,ifnull(comentario,'') comentario,
           DATE_FORMAT(prg_auditoractividad.fecha,'%d-%m-%Y') AS fecha, prg_auditoractividad.porcentaje, IFNULL(nota,'') AS nota,
           IFNULL(prg_auditoractividad.project_id,'') AS project_id, IFNULL(prg_proyectoactividad.proyect,'') AS proyecto,
           ifnull(id,0) as id,
           ifnull(flgeditacalendar,'0') as flgeditacalendar,
           ifnull(prg_actividadxrol.id_rol,0) as rol,
           case oferta when 's' then 'SI'  when 'n' then 'NO' end as oferta_dsc ,
		   ifnull(flgfinalizo,'') as flgfinalizo,
		   case flgfinalizo when 's' then 'SI'  when 'n' then 'NO' end as finalizo_dsc
        FROM 
            prg_auditoractividad LEFT JOIN 
            t_mae_pais ON prg_auditoractividad.id_pais=t_mae_pais.id_pais INNER JOIN
            prg_actividad ON prg_auditoractividad.id_actividad=prg_actividad.id_actividad left JOIN
            prg_programa ON prg_auditoractividad.id_programa=prg_programa.id_programa LEFT JOIN
            prg_proyectoactividad ON prg_auditoractividad.project_id=prg_proyectoactividad.project_id and ifnull(prg_proyectoactividad.project_id,'')!='' and prg_proyectoactividad.flag='1' left join
            prg_actividadxrol on prg_actividad.id_actividad=prg_actividadxrol.id_actividad 
        WHERE prg_auditoractividad.flag='1'  $searchQuery ";
        
		if(!empty($noferiado))
			$sql.="  AND IFNULL(prg_auditoractividad.usuario_ingreso,'')!='feriado'  ";
		
		$sql.=" group by   prg_auditoractividad.id_auditactiv" ;
		
		
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;	

		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
			 
		}
        return $this->listas;	
		
	}
	
	// total de registros por auditor fecha
	public function selec_total_activixauditor($searchQuery=null,$noferiado=null){
		$sql=" SELECT COUNT(distinct id_auditactiv) AS total 
			FROM 
            prg_auditoractividad 
        WHERE flag='1' " ;
		
		if(!empty($noferiado))
			$sql.="  AND IFNULL(usuario_ingreso,'')!='feriado'  ";
		
		if(!empty($searchQuery)) $sql.="  $searchQuery ";
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	// total one registro
	public function selec_one_actividadesxauditor($id_auditactiv){
		$sql=" SELECT *, date_format(fechac,'%d/%m/%Y') as fechacf ,
			date_format(fecha_mc,'%d/%m/%Y') as fecha_mcf 
				fROM prg_auditoractividad   
				WHERE id_auditactiv=$id_auditactiv" ;
			
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	
	
	// select % avance en dia 
	public function selec_avance_activixauditor($searchQuery,$noferiado=null){
		unset($this->listas);
		$sql="SELECT ifnull(sum(prg_auditoractividad.porcentaje),0) as total
                 FROM prg_auditoractividad 
				 WHERE prg_auditoractividad.flag='1'  $searchQuery" ;
				
		if(!empty($noferiado))
			$sql.="  AND IFNULL(prg_auditoractividad.usuario_ingreso,'')!='feriado'  ";
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
		
	}
	
	
	
	// insert usuario
    public function insert_auditActivi($comentario,$flgfinalizo,$oferta,$id_actividad,$id_programa,$nota,$porcentaje,$project_id,$id_auditor,$fecha,$fechac,$ciclo,$id_pais,$usuario,$ip,$ref_pais,$fecha_mc){
       $id_actividad = !empty($id_actividad) ? "'$id_actividad'" : "NULL";
	   $id_programa = !empty($id_programa) ? "'$id_programa'" : "NULL";
	   $id_pais = !empty($id_pais) ? "'$id_pais'" : "NULL";
	   $id_auditor = !empty($id_auditor) ? "'$id_auditor'" : "NULL";
	   $fechac = !empty($fechac) ? "'$fechac'" : "NULL";

	   $fecha_mc = !empty($fecha_mc) ? "'$fecha_mc'" : "NULL";
   
        $sql="insert into prg_auditoractividad(comentario,flgfinalizo,oferta,id_actividad,id_programa,id_pais,nota,porcentaje,project_id,id_auditor,flag,fecha,ref_pais,fechac,ciclo,fecha_mc)
        values('$comentario','$flgfinalizo','$oferta',$id_actividad,$id_programa,$id_pais,'$nota',$porcentaje,'$project_id',$id_auditor,'1','$fecha','$ref_pais',$fechac,'$ciclo',$fecha_mc)";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_auditoractividad($comentario,$id_auditactiv,$flgfinalizo,$oferta,$id_actividad,$id_programa,$nota,$porcentaje,$project_id,$id_auditor,$fecha,$fechac,$ciclo,$id_pais,$usuario,$ip,$fecha_mc){
		$id_actividad = !empty($id_actividad) ? "'$id_actividad'" : "NULL";
		$id_programa = !empty($id_programa) ? "'$id_programa'" : "NULL";
		$id_pais = !empty($id_pais) ? "'$id_pais'" : "NULL";
		$id_auditor = !empty($id_auditor) ? "'$id_auditor'" : "NULL";
		$fechac = !empty($fechac) ? "'$fechac'" : "NULL";

		$fecha_mc = !empty($fecha_mc) ? "'$fecha_mc'" : "NULL";
		  
        $sql="update prg_auditoractividad set 
					flgfinalizo='$flgfinalizo',
					ciclo='$ciclo',
					comentario='$comentario',
					fechac=$fechac,
                    oferta='$oferta',id_actividad=$id_actividad,id_programa=$id_programa,id_pais=$id_pais,nota='$nota',
                    porcentaje=$porcentaje,project_id='$project_id',id_auditor=$id_auditor,
					fecha_mc=$fecha_mc
                     where id_auditactiv=$id_auditactiv";
					echo $sql; 
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	public function update_auditoractividad_file($img,$id_auditactiv){
   
        $sql="update prg_auditoractividad set oferta_file='$img' where id_auditactiv=$id_auditactiv";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function delete_fileAudActi($id_auditactiv){
   
        $sql="update prg_auditoractividad set oferta_file='' where id_auditactiv=$id_auditactiv";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function delete_AudActi($id_auditactiv){
   
        $sql="update prg_auditoractividad set flag='0' where id_auditactiv=$id_auditactiv";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }


    public function regula_AudActi($id_auditactiv){
		
		$sql="UPDATE prg_auditoractividad SET diasreales= porcentaje/100  
			WHERE IFNULL(id,0)=0 AND flag='1' and  id_auditactiv=$id_auditactiv";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE prg_auditoractividad INNER JOIN prg_proyectoactividad ON prg_auditoractividad.project_id=prg_proyectoactividad.project_id 
		SET ref_proyecto=prg_proyectoactividad.proyect
		WHERE id_auditactiv=$id_auditactiv and prg_proyectoactividad.project_id!=''";
		$consulta=$this->db->execute($sql);
	
		$sql="UPDATE prg_auditoractividad INNER JOIN prg_proyecto ON prg_auditoractividad.project_id=prg_proyecto.project_id 
		SET ref_proyecto=prg_proyecto.proyect
		WHERE id_auditactiv=$id_auditactiv AND IFNULL(prg_auditoractividad.ref_proyecto,'')=''";
		$consulta=$this->db->execute($sql);
		
	
		$sql="UPDATE prg_auditoractividad INNER JOIN prg_actividad ON prg_auditoractividad.id_actividad=prg_actividad.id_actividad 
		SET ref_actividad=prg_actividad.actividad
		WHERE id_auditactiv=$id_auditactiv";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE prg_auditoractividad INNER JOIN prg_programa ON prg_auditoractividad.id_programa=prg_programa.id_programa 
		SET ref_programa=prg_programa.descripcion
		WHERE id_auditactiv=$id_auditactiv";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE prg_auditoractividad INNER JOIN prg_auditor ON prg_auditoractividad.id_auditor=prg_auditor.id_auditor 
			INNER JOIN prg_usuarios ON prg_auditor.id_auditor=prg_usuarios.id_auditor
		SET ref_rol=prg_usuarios.id_rol
		WHERE id_auditactiv=$id_auditactiv";	
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE prg_auditoractividad INNER JOIN prg_auditor ON prg_auditoractividad.id_auditor=prg_auditor.id_auditor 
		SET ref_auditor= CONCAT_WS(' ',prg_auditor.nombre,apepaterno,apematerno)
		WHERE id_auditactiv=$id_auditactiv";	
		$consulta=$this->db->execute($sql);

        return $consulta;
    }


	public function select_feriadoCalendar($fecha,$id_pais,$id_auditor=null){
		unset($this->listas);
		$sql="SELECT *
				from prg_feriado 
				where flag='1' and to_days(fecha)=to_days('$fecha')  and id_pais='$id_pais' ";
		
		if(!empty($id_auditor))
			$sql.=" and id_feriado not in (select id_feriado from prg_feriadoxauditor where id_auditor=$id_auditor)";

		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

}
?>