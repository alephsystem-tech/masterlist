<?php
/* TABLAS 
	prg_req*
	prg_programa_calif
	prg_prog_modulo
	prg_auditor_programa_modulo
	prg_programaxmodulo
	prg_auditor_programa
*/

class prg_requisito_model{
    private $db;
	private $listas;
    public function __construct(){
        $this->db=new DBManejador();
		$this->listas=array();
    }
    /* MODELO para seleccionar  requisitos
        agosto 2024
		Autor: Enrique Bazalar alephsystem@gmail.com
    */

	public function select_requisitos($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_programa){
		unset($this->listas);
		
		$union="left";
		if(!empty($id_programa)) 
			$union="inner";
		
		$sql="SELECT prg_requisito.*,  
					prg_requisito_tipo.tipo, prg_requisito_cat.categoria,
					ifnull(vista.programa,'') as programa
				from prg_requisito inner join
					prg_requisito_cat on prg_requisito.codcategoria=prg_requisito_cat.codcategoria inner join
					prg_requisito_tipo on prg_requisito.codtipo=prg_requisito_tipo.codtipo $union join
					(
						SELECT GROUP_CONCAT(descripcion separator ' , ') AS programa, codrequisito
						FROM prg_programa INNER JOIN prg_requisitoxprog ON prg_programa.id_programa=prg_requisitoxprog.id_programa 
						where prg_programa.flag='1' ";
						
		if(!empty($id_programa)) 
				$sql.=" and prg_programa.id_programa in ($id_programa) ";
			
		$sql.="			GROUP BY codrequisito 
					) as vista on prg_requisito.codrequisito=vista.codrequisito
				
				where prg_requisito.flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	// total de registros requisitos
	public function selec_total_requisito($searchQuery=null,$id_programa=null){
		$sql=" SELECT COUNT(*) AS total 
				FROM 
				prg_requisito
				WHERE flag='1' $searchQuery " ;
				
		if(!empty($id_programa))
			$sql.=" and codrequisito in (select codrequisito from prg_requisitoxprog where id_programa in ($id_programa))";
			
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function select_programa_lst($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT prg_programa.id_programa, CONCAT_WS('->',prg_programa.descripcion , prg_paises.nombre) AS descripcion  , 
				group_concat(ifnull(prg_usuarios.nombres,'') separator '<br>') as calificadores
			FROM prg_programa left join  prg_programa_calif on prg_programa.id_programa=prg_programa_calif.id_programa
				INNER JOIN prg_paises ON prg_programa.id_pais=prg_paises.id_pais
				left join prg_usuarios on  prg_programa_calif.id_usuario=prg_usuarios.id_usuario
				left join prg_auditor on prg_usuarios.id_auditor=prg_auditor.id_auditor and flgstatus='1'
			WHERE prg_programa.flag='1'  $searchQuery ";
		$sql.=" group by prg_programa.id_programa 
			order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_categoria($id_pais){
		unset($this->listas);
		$sql="SELECT * from prg_requisito_cat where flag='1' and id_pais='$id_pais' order by categoria";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_tipo($id_pais){
		unset($this->listas);
		$sql="SELECT * from prg_requisito_tipo where flag='1' and id_pais='$id_pais' order by tipo";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_rol($codrequisito){
		unset($this->listas);
		$sql="SELECT r.id_rol,r.nombre , GROUP_CONCAT(IFNULL(id_programa,'')) AS gprograma
			FROM prg_roles r LEFT JOIN prg_requisitoxrolxprog p ON r.id_rol=p.id_rol AND codrequisito=$codrequisito
			WHERE r.flag='1' and r.flgcalifica='1'
			GROUP BY id_rol
			ORDER BY nombre";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_programa($id_pais,$id_auditor=null){
		unset($this->listas);

		$sql="SELECT id_programa, CONCAT_WS('->',descripcion , nombre) AS descripcion
			FROM prg_programa INNER JOIN prg_paises ON prg_programa.id_pais=prg_paises.id_pais
			WHERE prg_programa.flag='1' AND flgactivo='1' AND descripcion!='' ";
		if(!empty($id_auditor)) 
				$sql.=" and id_programa in (select distinct id_programa from prg_auditor_programa where id_auditor=$id_auditor)";
		$sql.="	ORDER BY descripcion";
				
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	
	public function select_exis_requisito($codrequisito,$codigo){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_requisito
        WHERE flag='1' and codigo='$codigo' " ;
		if(!empty($codrequisito))
			$sql.=" and codrequisito!=$codrequisito";
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}

	public function selec_reqxprograma($codrequisito){
		$sql=" SELECT group_concat(id_programa) as gprograma
				FROM prg_requisitoxprog
				WHERE codrequisito=$codrequisito " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}


	public function selec_total_programa($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM prg_programa
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_requisito($codrequisito){
		
		$sql="SELECT * from prg_requisito where codrequisito=$codrequisito ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_requisito($requisito,$codcategoria,$codigo,$codtipo,$frecuencia,$descripcion,$comentario,$novence,$id_pais,$usuario,$ip){
       $frecuencia = !empty($frecuencia) ? "$frecuencia" : "NULL";

        $sql="insert into prg_requisito(requisito,codcategoria,codigo,codtipo,frecuencia,descripcion,comentario,novence,
			id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$requisito',$codcategoria,'$codigo',$codtipo,$frecuencia,'$descripcion','$comentario','$novence',
			'$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_requisito($codrequisito,$requisito,$codcategoria,$codigo,$codtipo,$frecuencia,$descripcion,$comentario,$novence,$id_pais,$usuario,$ip){
	   $frecuencia = !empty($frecuencia) ? "$frecuencia" : "NULL";
        $sql="update prg_requisito 
				set requisito='$requisito',
					codcategoria='$codcategoria',
					codigo='$codigo',
					frecuencia=$frecuencia,
					codtipo='$codtipo',
					descripcion='$descripcion',
					comentario='$comentario',
					novence='$novence',
					
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codrequisito=$codrequisito and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_requisito($codrequisito){
	   
        $sql="update prg_requisito set flag='0' where codrequisito=$codrequisito";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }

	public function delete_requisitoxprog($codrequisito){
	   
        $sql="delete from prg_requisitoxprog where codrequisito=$codrequisito";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }

	public function insert_requisitoxprog($codrequisito,$id_programa){
	   
        $sql="insert into  prg_requisitoxprog(codrequisito,id_programa) values ($codrequisito,$id_programa)";
		
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	
	
	
	public function insert_reqxrolxprograma($id_rol,$id_programa,$codrequisito){
	   
        $sql="insert into prg_requisitoxrolxprog(id_rol,id_programa,codrequisito) 
			values ($id_rol,$id_programa,$codrequisito)";
		$consulta=$this->db->execute($sql);
		
        return $consulta;
    }	
	
	 public function delete_reqxrolxprograma($codrequisito){
	   
        $sql="delete from  prg_requisitoxrolxprog where codrequisito=$codrequisito";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	
	public function selec_usuarios($id_pais,$flgcalifica=null){
		unset($this->listas);
		$sql="SELECT prg_usuarios.id_usuario, prg_usuarios.id_auditor,nombres 
			FROM prg_usuarios INNER JOIN prg_auditor ON prg_usuarios.id_auditor=prg_auditor.id_auditor
			WHERE prg_usuarios.flag='1' AND id_pais='$id_pais' and flgstatus='1'";
			
		if(!empty($flgcalifica))
			$sql.=" and flgcalifica='$flgcalifica'"	;
		$sql.="	ORDER BY nombres";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_usuarios_all($flgcalifica){
		unset($this->listas);
		$sql="SELECT prg_usuarios.id_usuario, prg_usuarios.id_auditor,nombres 
			FROM prg_usuarios INNER JOIN prg_auditor ON prg_usuarios.id_auditor=prg_auditor.id_auditor
			WHERE prg_usuarios.flag='1' and flgstatus='1' and flgcalifica='$flgcalifica'
			ORDER BY nombres";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_one_programa($id_programa){
		$sql="SELECT prg_programa.* , GROUP_CONCAT(IFNULL(id_usuario,'')) AS arr_gusuario
			from prg_programa LEFT JOIN prg_programa_calif p ON prg_programa.id_programa=p.id_programa
			where prg_programa.id_programa=$id_programa ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
		
	}


	public function delete_calificaxuser($id_programa){
	   
        $sql="delete from  prg_programa_calif where id_programa=$id_programa";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function insert_calificaxuser($id_programa,$id_usuario){
	   
        $sql="insert into prg_programa_calif(id_programa,id_usuario) values ($id_programa,$id_usuario)";
		$consulta=$this->db->execute($sql);
		
        return $consulta;
    }	
				
	// usuarios
	//*******************************************	
	public function selec_usuarios_cal($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT id_usuario, a.id_auditor, TRIM(u.nombres) AS auditor, p.nombre AS pais, u.usuario,
				GROUP_CONCAT(distinct ro.nombre SEPARATOR '<br>') AS rol,
				IFNULL(GROUP_CONCAT(DISTINCT pg.descripcion SEPARATOR '<br>'),'') AS programa,
				a.flgcalifica
			FROM prg_usuarios u INNER JOIN prg_auditor a ON u.id_auditor=a.id_auditor  
				INNER JOIN prg_paises p ON p.id_pais=a.id_pais
				inner JOIN prg_auditorxrol ar ON a.id_auditor=ar.id_auditor 
				inner JOIN prg_roles ro ON  ar.id_rol=ro.id_rol and ro.flgcalifica='1'
				LEFT JOIN prg_auditor_programa ap ON a.id_auditor=ap.id_auditor
				LEFT JOIN prg_programa pg ON ap.id_programa=pg.id_programa  
			WHERE a.flag='1' and flgstatus='1' and a.id_auditor>0  $searchQuery 
			GROUP BY a.id_auditor ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

	// total de registros requisitos
	public function selec_total_usuarios($searchQuery=null){
		$sql=" SELECT COUNT(DISTINCT a.id_auditor) AS total 
			FROM prg_usuarios u INNER JOIN prg_auditor a ON u.id_auditor=a.id_auditor  
				INNER JOIN prg_paises p ON p.id_pais=a.id_pais
				inner JOIN prg_auditorxrol ar ON a.id_auditor=ar.id_auditor 
				LEFT JOIN prg_roles ro ON  ar.id_rol=ro.id_rol and ro.flgcalifica='1'
				LEFT JOIN prg_auditor_programa ap ON a.id_auditor=ap.id_auditor
				LEFT JOIN prg_programa pg ON ap.id_programa=pg.id_programa  
			WHERE a.flag='1' and flgstatus='1'  and a.id_auditor>0 $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}

	public function delete_evaluacionxuser($id_auditor,$flgcalifica){
	   
        $sql="update prg_auditor set flgcalifica='$flgcalifica' where id_auditor=$id_auditor";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	
	public function selec_one_auditor($id_auditor){
		$sql="SELECT id_usuario,a.id_auditor, TRIM(u.nombres) AS auditor, p.nombre AS pais, u.usuario, 
				ifnull(flgcalifica,0) flgcalifica,
				ifnull(flgudcal,0) flgudcal, 
				p.id_pais
				FROM prg_usuarios u INNER JOIN prg_auditor a ON u.id_auditor=a.id_auditor  
					INNER JOIN prg_paises p ON p.id_pais=a.id_pais
				WHERE a.flag='1' AND a.id_auditor=$id_auditor";
				
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
		
	}
	
	public function selec_all_usercalifica($id_pais){
		unset($this->listas);
		$sql="SELECT id_usuario,a.id_auditor, TRIM(u.nombres) AS auditor, p.nombre AS pais, u.usuario,p.id_pais
				FROM prg_usuarios u INNER JOIN prg_auditor a ON u.id_auditor=a.id_auditor  
					INNER JOIN prg_paises p ON p.id_pais=a.id_pais
				WHERE a.flag='1' AND flgcalifica=1  and p.id_pais='$id_pais'
				ORDER BY 3" ;
					
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	public function selec_det_usuarios($id_auditor){
		unset($this->listas);
		$sql="SELECT ro.id_rol,ro.nombre,  IFNULL(m.modulo,'') AS modulo,
				IFNULL(GROUP_CONCAT( distinct pg.descripcion SEPARATOR '<br>'),'') AS programa
			FROM  prg_auditor_programa ap 
				INNER JOIN prg_programa pg ON ap.id_programa=pg.id_programa  
				INNER JOIN prg_roles ro ON  ap.id_rol=ro.id_rol
				INNER JOIN prg_auditorxrol ax ON ro.id_rol=ax.id_rol AND ax.id_auditor=ap.id_auditor
				LEFT JOIN prg_auditor_programa_modulo am ON ap.id_auditor=am.id_auditor AND ap.id_programa=am.id_programa
					AND ro.id_rol=am.id_rol
				LEFT JOIN prg_prog_modulo m ON am.id_modulo=m.id_modulo	
			WHERE  ap.id_auditor=$id_auditor
			GROUP BY am.id_rol, am.id_programa, am.id_modulo " ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	// seccion de calificaciones
	
	public function selec_estado_calif($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_auditor,$id_rol=null){
		unset($this->listas);
		// tot_req_eval tt
		$sql="SELECT vista.* 
		FROM (
			SELECT COUNT(prg_requisito.codrequisito) AS totreq,
				prg_roles.nombre AS rol,
				CONCAT_WS('_',rr.id_rol,rr.id_programa,rr.id_modulo) AS llave,
				prg_programa.descripcion AS programa,
				rr.id_rol,rr.id_programa, 
				pmm.modulo,
				COUNT(rr.codrequisito) AS tot_req_eval,
				COUNT(DISTINCT ruu.codrequisito) AS tt,
				
				GROUP_CONCAT(rr.codrequisito) AS req_eval,
				
				ifnull(usuario_califica,'') usuario_califica
			FROM prg_requisito INNER JOIN
				prg_requisitoxrolxprog rr ON prg_requisito.codrequisito=rr.codrequisito  INNER JOIN
				prg_roles ON rr.id_rol=prg_roles.id_rol INNER JOIN 
				prg_programa ON rr.id_programa=prg_programa.id_programa  INNER JOIN
				prg_prog_modulo pmm ON rr.id_modulo=pmm.id_modulo INNER JOIN
				prg_auditor_programa_modulo apm ON rr.id_programa=apm.id_programa AND rr.id_modulo=apm.id_modulo AND rr.id_rol=apm.id_rol 
				INNER JOIN prg_auditorxrol xr ON apm.id_rol=xr.id_rol AND apm.id_auditor=xr.id_auditor LEFT JOIN
				prg_requisito_usuario ruu ON rr.codrequisito=ruu.codrequisito AND codestado='CALIFICADO' 
					AND ruu.id_auditor=apm.id_auditor and (to_days(ruu.vigencia)>= to_days(now()) or ruu.vigencia is null) and flgisactivo='1'
			WHERE prg_requisito.flag='1' AND prg_programa.flag='1' AND apm.id_auditor=$id_auditor
			GROUP BY 3
		) AS vista ";
		
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

	public function selec_total_estado_calif($searchQuery=null,$id_auditor,$id_rol=null){
		$sql=" SELECT COUNT(vista.llave) AS total 
			FROM (
					SELECT COUNT(prg_requisito.codrequisito) AS totreq,
					prg_roles.nombre AS rol,
					CONCAT_WS('_',rr.id_rol,rr.id_programa,rr.id_modulo) AS llave,
					prg_programa.descripcion AS programa,
					rr.id_rol,rr.id_programa, 
					pmm.modulo,
					COUNT(rr.codrequisito) AS tot_req_eval,
					GROUP_CONCAT(rr.codrequisito) AS req_eval
				FROM prg_requisito INNER JOIN
					prg_requisitoxrolxprog rr ON prg_requisito.codrequisito=rr.codrequisito  INNER JOIN
					prg_roles ON rr.id_rol=prg_roles.id_rol INNER JOIN 
					prg_programa ON rr.id_programa=prg_programa.id_programa  INNER JOIN
					prg_prog_modulo pmm ON rr.id_modulo=pmm.id_modulo INNER JOIN
					prg_auditor_programa_modulo apm ON rr.id_programa=apm.id_programa AND rr.id_modulo=apm.id_modulo AND rr.id_rol=apm.id_rol 
					INNER JOIN prg_auditorxrol xr ON apm.id_rol=xr.id_rol AND apm.id_auditor=xr.id_auditor
				WHERE prg_requisito.flag='1' AND prg_programa.flag='1' AND apm.id_auditor=$id_auditor
				GROUP BY 3
			) AS vista
		    where 1=1 $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	
	public function selec_estado_req($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT  DISTINCT r.codrequisito,categoria,r.codigo,requisito, 
				IFNULL(codestado,'PENDIENTE') AS estado,
				DATE_FORMAT(r.fecha_ingreso,'%d/%m/%Y') AS fecha2,
				DATE_FORMAT(ru.fecha_modifica,'%d/%m/%Y') AS ingreso,
				DATE_FORMAT(ru.fechacal,'%d/%m/%Y') AS calificacion,
				
				CASE 
					WHEN IFNULL(codestado,'PENDIENTE')='PENDIENTE' and vistareq.vigencia is not null
						THEN DATE_FORMAT(vistareq.vigencia,'%d/%m/%Y') 
					ELSE DATE_FORMAT(r.fecha_ingreso,'%d/%m/%Y') 
				END AS fecha,
				
				CASE 
					WHEN IFNULL(ru.flgreqnovence,0)=1 THEN 'NO VENCE'
					WHEN IFNULL(ru.vigencia,'')!='' THEN DATE_FORMAT(ru.vigencia,'%d/%m/%Y')
					WHEN IFNULL(r.frecuencia,0)=0 THEN 'NO VENCE'
					ELSE DATE_FORMAT(DATE_ADD(r.fecha_ingreso, INTERVAL r.frecuencia MONTH),'%d/%m/%Y')
				END AS vigenciatxt,
	
				DATE_FORMAT(ru.vigencia,'%d/%m/%Y') AS vigencia,
				IFNULL(ru.id,0) AS id,
				CONCAT_WS('_',r.codrequisito,IFNULL(ru.id,0)) AS llave,
				GROUP_CONCAT(DISTINCT prg_roles.nombre) AS rol,
				GROUP_CONCAT(DISTINCT prg_programa.descripcion) AS programa,
				GROUP_CONCAT(DISTINCT prg_prog_modulo.modulo) AS modulo
				
			FROM 	prg_requisito r INNER JOIN 
				prg_requisito_cat ON r.codcategoria=prg_requisito_cat.codcategoria INNER JOIN
				prg_requisitoxrolxprog rp ON r.codrequisito=rp.codrequisito 
				INNER JOIN prg_programaxmodulo pxm ON rp.id_programa=pxm.id_programa AND rp.id_modulo=pxm.id_modulo
				INNER JOIN prg_auditor_programa_modulo apm ON rp.id_programa=apm.id_programa AND rp.id_modulo=apm.id_modulo AND rp.id_rol=apm.id_rol 
				LEFT JOIN prg_requisito_usuario ru ON r.codrequisito=ru.codrequisito  AND ru.id_auditor=apm.id_auditor
					and ( to_days(ru.vigencia) >= to_days(now()) or ru.vigencia is null ) and flgisactivo='1'
				INNER JOIN prg_roles ON rp.id_rol=prg_roles.id_rol
				INNER JOIN prg_programa ON rp.id_programa=prg_programa.id_programa
				INNER JOIN prg_prog_modulo ON rp.id_modulo=prg_prog_modulo.id_modulo 
				
				left join (
					select max(vigencia) as vigencia, codrequisito, id_auditor 
					from prg_requisito_usuario 
					where flgisactivo='0' and flag='1'
					group by codrequisito, id_auditor 
					) as vistareq on r.codrequisito=vistareq.codrequisito and apm.id_auditor=vistareq.id_auditor
				
				
			WHERE r.flag='1' $searchQuery 
			GROUP BY r.codrequisito   ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_total_estado_req($searchQuery=null){
		$sql="SELECT COUNT(distinct r.codrequisito) AS total
			FROM 	prg_requisito r INNER JOIN 
				prg_requisito_cat ON r.codcategoria=prg_requisito_cat.codcategoria INNER JOIN
				prg_requisitoxrolxprog rp ON r.codrequisito=rp.codrequisito 
				INNER JOIN prg_programaxmodulo pxm ON rp.id_programa=pxm.id_programa AND rp.id_modulo=pxm.id_modulo
				INNER JOIN prg_auditor_programa_modulo apm ON rp.id_programa=apm.id_programa AND rp.id_modulo=apm.id_modulo AND rp.id_rol=apm.id_rol 
				LEFT JOIN prg_requisito_usuario ru ON r.codrequisito=ru.codrequisito  AND ru.id_auditor=apm.id_auditor and flgisactivo='1'
				INNER JOIN prg_roles ON rp.id_rol=prg_roles.id_rol
				INNER JOIN prg_programa ON rp.id_programa=prg_programa.id_programa
				INNER JOIN prg_prog_modulo ON rp.id_modulo=prg_prog_modulo.id_modulo
			WHERE r.flag='1' $searchQuery " ;
			
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	
	public function selec_one_tipo($codtipo){
		$sql="SELECT tipo FROM prg_requisito_tipo WHERE codtipo=$codtipo" ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function update_evidenciacalifica_file($id,$adjunto){
	   
        $sql="update prg_requisito_usuario set adjunto='$adjunto' where id=$id";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	
	 public function insert_evidenciacalifica($id_auditor,$codestado,$codrequisito,$comentarios,$id_pais,$usuario_name,$ip){
       $comentarios = !empty($comentarios) ? "'$comentarios'" : "NULL";

        $sql="insert into prg_requisito_usuario(id_auditor,codrequisito,codestado,fechasol,fechaing,vigencia,comentarios,
			id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values($id_auditor,$codrequisito,'$codestado',now(),now(),null,$comentarios,
			'$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
		
		$consulta=$this->db->executeIns($sql);
		
		$sql="UPDATE  prg_requisito r INNER JOIN prg_requisito_usuario ru ON r.codrequisito=ru.codrequisito
			SET vigencia=CASE WHEN IFNULL(r.frecuencia,0)=0 THEN NULL
				ELSE DATE_FORMAT(DATE_ADD(r.fecha_ingreso, INTERVAL IFNULL(r.frecuencia,0) MONTH),'%Y/%m/%d')
				END
			WHERE ru.id=$consulta";
			
		$res=$this->db->execute($sql);
		
        return $consulta;
    }	

	// update usuario
    public function update_evidenciacalifica($id,$comentarios,$id_pais,$usuario,$ip){
	   $comentarios = !empty($comentarios) ? "'$comentarios'" : "NULL";
        $sql="update prg_requisito_usuario 
				set comentarios=$comentarios,
					fechasol=now(),
					usuario_modifica='$usuario',
					usuario_califica='$usuario',
					fecha_modifica=now(),ip_modifica='$ip'
                where id=$id";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	public function update_evidenciacalifica_estado($id,$codestado){
	   
        $sql="update prg_requisito_usuario 
				set codestado='$codestado',
					fechacal=null,
					id_usuario_califica=null,
					usuario_califica=null
                where id=$id";

		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	public function selec_one_calificauser($id){
		$sql="SELECT *, date_format(fechacal,'%d/%m/%Y') as fechacalf,
					date_format(vigencia,'%d/%m/%Y') as vigenciaf
			FROM prg_requisito_usuario WHERE id=$id" ;
			
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	// delete one documento
	public function delete_one_doccalificauser($id){
		$sql="update prg_requisito_usuario 
				set adjunto='', codestado='PENDIENTE',
					fechacal=null,
					id_usuario_califica=null,
					usuario_califica=null
				WHERE id=$id" ;
			
		$consulta=$this->db->execute($sql);
		return $consulta;	
	}
	
	//******************************************
	// reporte de calificacion
	//******************************************
	
	public function selec_repcalifica_req($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_rol,$id_programa){
		unset($this->listas); //usuarioaprueba
		//to_days(ru.vigencia) - to_days(now()) as diascalificado,
		//	to_days(ru.fechasol) - to_days(now()) as diasentregado,
				
		$sql="SELECT ru.codestado,  ru.id,
				DATE_FORMAT(q.fecha_ingreso,'%d/%m/%Y') AS fechasol, 
				DATE_FORMAT(ru.fecha_modifica,'%d/%m/%Y') AS fechaing, 
				IFNULL(DATE_FORMAT(ru.fechacal,'%d/%m/%Y'),'') AS fechacal,
				IFNULL(DATE_FORMAT(ru.vigencia,'%d/%m/%Y'),'') AS vigencia,
				ifnull(comentario_calif,'') as comentario_calif,
				
				CASE 
					WHEN IFNULL(ru.vigencia,'')!='' THEN DATE_FORMAT(ru.vigencia,'%d/%m/%Y')
					WHEN IFNULL(ru.flgreqnovence,0)='1' THEN 'NO VENCE'
					WHEN IFNULL(q.frecuencia,0)=0 THEN 'NO VENCE'
					ELSE DATE_FORMAT(DATE_ADD(q.fecha_ingreso, INTERVAL q.frecuencia MONTH),'%d/%m/%Y')
				END AS vigenciatxt,
				
						
				ru.adjunto, ru.comentarios AS coment_evid, 
				CONCAT_WS(' ',a.nombre,apepaterno,apematerno) AS fullusuario , 
				
				pt.tipo, pc.categoria, q.codigo, q.requisito, IFNULL(q.frecuencia,0) AS frecuencia, 
				q.descripcion, q.comentario, q.novence,
				ru.usuario_modifica AS usuarioaprueba,
				case when codestado='CALIFICADO' THEN
					TO_DAYS(ru.vigencia) - TO_DAYS(NOW()) 
				ELSE
					TO_DAYS(ru.fechasol) - TO_DAYS(NOW()) 
				END AS dias,
				vista.detalle
			FROM prg_requisito_usuario ru INNER JOIN 
				prg_requisito q ON ru.codrequisito=q.codrequisito INNER JOIN
				prg_auditor a ON ru.id_auditor=a.id_auditor INNER JOIN
				prg_requisito_tipo pt ON q.codtipo=pt.codtipo INNER JOIN
				prg_requisito_cat pc ON q.codcategoria=pc.codcategoria INNER JOIN
				prg_auditor_programa pap ON a.id_auditor=pap.id_auditor INNER JOIN
				prg_programa_calif ppc  ON pap.id_programa=ppc.id_programa INNER JOIN
				prg_requisitoxrolxprog rrp ON q.codrequisito=rrp.codrequisito 
					AND rrp.id_programa=ppc.id_programa and rrp.id_rol=pap.id_rol
				INNER JOIN
					(
					SELECT CONCAT_WS('_',rrp.codrequisito, apm.id_auditor) AS llave, 
					GROUP_CONCAT(CONCAT_WS('&&',r.nombre,prg_programa_grupo.grupo,p.descripcion,m.modulo,pa.nombre) SEPARATOR '!!') AS detalle
					FROM prg_programa_grupo INNER JOIN 
						prg_programa p ON p.id_grupoprograma=prg_programa_grupo.id_grupoprograma INNER JOIN
						prg_requisitoxrolxprog rrp ON rrp.id_programa=p.id_programa INNER JOIN
						prg_roles r ON rrp.id_rol=r.id_rol INNER JOIN
						prg_prog_modulo m ON rrp.id_modulo=m.id_modulo INNER JOIN
						prg_paises pa on p.id_pais=pa.id_pais inner join 
						prg_auditor_programa_modulo apm ON apm.id_programa=p.id_programa AND apm.id_modulo=m.id_modulo
							AND apm.id_rol=r.id_rol ";
				if(!empty($id_rol)) $sql.=" AND apm.id_rol=$id_rol";  	
				if(!empty($id_programa)) $sql.=" AND apm.id_programa=$id_programa";  					
				$sql.="	GROUP BY llave
					) AS vista ON CONCAT_WS('_',q.codrequisito,a.id_auditor)=vista.llave
			WHERE  $searchQuery
			GROUP BY q.codrequisito	, a.id_auditor	
			order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_total_repcalifica_req($searchQuery=null){
		$sql="SELECT COUNT(distinct concat_ws('_',q.codrequisito,a.id_auditor) ) AS total
			FROM prg_requisito_usuario ru INNER JOIN 
				prg_requisito q ON ru.codrequisito=q.codrequisito INNER JOIN
				prg_auditor a ON ru.id_auditor=a.id_auditor INNER JOIN
				prg_requisito_tipo pt ON q.codtipo=pt.codtipo INNER JOIN
				prg_requisito_cat pc ON q.codcategoria=pc.codcategoria INNER JOIN
				prg_auditor_programa pap ON a.id_auditor=pap.id_auditor INNER JOIN
				prg_programa_calif ppc  ON pap.id_programa=ppc.id_programa INNER JOIN
				prg_requisitoxrolxprog rrp ON q.codrequisito=rrp.codrequisito AND rrp.id_programa=ppc.id_programa and rrp.id_rol=pap.id_rol
			where  $searchQuery  " ;
			
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function update_changeestado($cadena,$codestado,$comentario,$id_usuario,$id_pais,$usuario,$ip){

        $sql="update prg_requisito_usuario 
				set codestado='$codestado',
					fechacal=now(),
					comentario_calif='$comentario',
					id_usuario_califica=$id_usuario,
					usuario_califica='$usuario',fechacal=now(),
					usuario_modifica='$usuario',
					fecha_modifica=now(),
					ip_modifica='$ip'
                where id in ($cadena)";
		$consulta=$this->db->execute($sql);
		
		 $sql="UPDATE prg_requisito q JOIN prg_requisito_usuario ru ON ru.codrequisito=q.codrequisito
				SET ru.vigencia=CASE WHEN IFNULL(q.frecuencia,0)=0 THEN NULL
					ELSE DATE_FORMAT(DATE_ADD(ru.fechasol, INTERVAL q.frecuencia MONTH),'%d/%m/%Y')
					END
				WHERE ru.id=$id";
		$consulta=$this->db->execute($sql);
		
        return $consulta;
    }
	
	public function update_resetestado($cadena,$codestado,$id_usuario,$id_pais,$usuario,$ip){

        $sql="update prg_requisito_usuario 
				set codestado='$codestado',
					fechacal=null,
					vigencia=null,
					id_usuario_califica=null,
					usuario_califica=null,
					usuario_modifica=null,
					comentario_calif=null,
					
					fecha_modifica=now(),ip_modifica='$ip'
                where id in ($cadena)";
		$consulta=$this->db->execute($sql);

		
        return $consulta;
    }
	
	 public function update_fechacal($id,$fechacal,$vigencia,$novence,$usuario,$ip){
	  
		$vigencia = !empty($vigencia) ? "'$vigencia'" : "NULL";
        $sql="update prg_requisito_usuario 
				set fechacal='$fechacal',
					vigencia=$vigencia,
					flgreqnovence='$novence',
					usuario_modifica='$usuario',
					usuario_califica='$usuario',
					fecha_modifica=now(),ip_modifica='$ip'
                where id=$id";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	//******************************************
	// reporte de pendientes|
	//******************************************
	
	public function selec_pendiente_req($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT  DISTINCT r.codrequisito,
				categoria,
				r.codigo,
				requisito, 
				IFNULL(codestado,'PENDIENTE') AS estado,
				DATE_FORMAT(r.fecha_ingreso,'%d/%m/%Y') AS fecha2,
				to_days(r.fecha_ingreso) - to_days(now()) as dias,

				CASE 
					WHEN IFNULL(codestado,'PENDIENTE')='PENDIENTE' and vistareq.vigencia is not null
						THEN DATE_FORMAT(vistareq.vigencia,'%d/%m/%Y') 
					ELSE DATE_FORMAT(r.fecha_ingreso,'%d/%m/%Y') 
				END AS fecha,
				
				CASE 
					WHEN IFNULL(r.frecuencia,0)=0 THEN 'NO VENCE'
					ELSE DATE_FORMAT(DATE_ADD(r.fecha_ingreso, INTERVAL r.frecuencia MONTH),'%d/%m/%Y')
				END AS vigenciatxt,

				CONCAT_WS('_',r.codrequisito,IFNULL(ru.id,0)) AS llave,
				GROUP_CONCAT(DISTINCT prg_roles.nombre) AS rol,
				GROUP_CONCAT(DISTINCT prg_programa.descripcion) AS programa,
				GROUP_CONCAT(DISTINCT prg_prog_modulo.modulo) AS modulo,
				CONCAT_WS(' ',au.nombre,apepaterno,apematerno) AS fullauditor, au.id_auditor,
				pa.nombre AS pais,
				prg_programa_grupo.grupo,
				pt.tipo
			FROM 	prg_requisito r INNER JOIN 
				prg_requisito_cat ON r.codcategoria=prg_requisito_cat.codcategoria INNER JOIN
				prg_requisitoxrolxprog rp ON r.codrequisito=rp.codrequisito 
				INNER JOIN prg_programaxmodulo pxm ON rp.id_programa=pxm.id_programa AND rp.id_modulo=pxm.id_modulo
				INNER JOIN prg_auditor_programa_modulo apm ON rp.id_programa=apm.id_programa AND rp.id_modulo=apm.id_modulo AND rp.id_rol=apm.id_rol 
				LEFT JOIN prg_requisito_usuario ru ON r.codrequisito=ru.codrequisito  AND ru.id_auditor=apm.id_auditor AND ru.flgisactivo='1' and ru.flag='1'
				INNER JOIN prg_roles ON rp.id_rol=prg_roles.id_rol
				INNER JOIN prg_programa ON rp.id_programa=prg_programa.id_programa
				INNER JOIN prg_prog_modulo ON rp.id_modulo=prg_prog_modulo.id_modulo 
				INNER JOIN prg_auditor au ON apm.id_auditor=au.id_auditor
				INNER JOIN prg_paises pa ON prg_programa.id_pais=pa.id_pais 
				INNER JOIN prg_programa_grupo ON prg_programa.id_grupoprograma=prg_programa_grupo.id_grupoprograma
				INNER JOIN prg_requisito_tipo pt ON r.codtipo=pt.codtipo 
				
				left join (
					select max(vigencia) as vigencia, codrequisito, id_auditor 
					from prg_requisito_usuario 
					where flgisactivo='0' and flag='1'
					group by codrequisito, id_auditor 
				) as vistareq on r.codrequisito=vistareq.codrequisito and apm.id_auditor=vistareq.id_auditor
				
				
			WHERE  $searchQuery
			GROUP BY r.codrequisito , au.id_auditor
			order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_total_pendiente_req($searchQuery=null){
		$sql="SELECT  COUNT(distinct  CONCAT_WS('.',au.id_auditor,r.codrequisito)) AS total
				FROM 	prg_requisito r INNER JOIN 
					prg_requisito_cat ON r.codcategoria=prg_requisito_cat.codcategoria INNER JOIN
					prg_requisitoxrolxprog rp ON r.codrequisito=rp.codrequisito 
					INNER JOIN prg_programaxmodulo pxm ON rp.id_programa=pxm.id_programa AND rp.id_modulo=pxm.id_modulo
					INNER JOIN prg_auditor_programa_modulo apm ON rp.id_programa=apm.id_programa AND rp.id_modulo=apm.id_modulo AND rp.id_rol=apm.id_rol 
					LEFT JOIN prg_requisito_usuario ru ON r.codrequisito=ru.codrequisito  AND ru.id_auditor=apm.id_auditor AND ru.flgisactivo='1' and ru.flag='1'
					INNER JOIN prg_roles ON rp.id_rol=prg_roles.id_rol
					INNER JOIN prg_programa ON rp.id_programa=prg_programa.id_programa
					INNER JOIN prg_prog_modulo ON rp.id_modulo=prg_prog_modulo.id_modulo 
					INNER JOIN prg_auditor au ON apm.id_auditor=au.id_auditor
					INNER JOIN prg_paises pa ON prg_programa.id_pais=pa.id_pais 
					INNER JOIN prg_programa_grupo ON prg_programa.id_grupoprograma=prg_programa_grupo.id_grupoprograma 
			where  $searchQuery  " ;
			
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	//***********************************++
	
	/***************************************
		asignacion de requisitos
	*****************************************/
	public function selec_programaxmodulo($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT  c.id_programa , 
					CONCAT_WS('->',p.descripcion , prg_paises.nombre) AS  programa, 
					m.modulo, m.id_modulo, CONCAT_WS('_',c.id_programa,m.id_modulo) AS llave, 
					IFNULL(vista.requisito,'') AS requisito
				FROM prg_programa_calif c INNER JOIN prg_programa p ON c.id_programa=p.id_programa 
					INNER JOIN prg_paises ON p.id_pais=prg_paises.id_pais
					INNER JOIN prg_programaxmodulo pm ON p.id_programa=pm.id_programa
					INNER JOIN prg_prog_modulo m ON pm.id_modulo=m.id_modulo
					LEFT JOIN (
						SELECT GROUP_CONCAT(DISTINCT prg_requisito.requisito separator ' , ') AS requisito, 
								CONCAT_WS('_', id_programa,id_modulo) AS llave
						FROM prg_requisitoxrolxprog INNER JOIN prg_requisito ON prg_requisitoxrolxprog.codrequisito=prg_requisito.codrequisito
						where prg_requisito.flag='1'
						GROUP BY llave
					) AS vista ON CONCAT_WS('_', pm.id_programa,pm.id_modulo)=vista.llave
				WHERE $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

	// total de registros requisitos
	public function selec_total_programaxmodulo($searchQuery=null){
		$sql=" SELECT COUNT(distinct CONCAT_WS('_',c.id_programa,m.id_modulo)) AS total 
			FROM prg_programa_calif c INNER JOIN prg_programa p ON c.id_programa=p.id_programa 
					INNER JOIN prg_programaxmodulo pm ON p.id_programa=pm.id_programa
					INNER JOIN prg_prog_modulo m ON pm.id_modulo=m.id_modulo
			WHERE $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_requisito_by_programa($id_programa){
		unset($this->listas);
		$sql="SELECT r.codrequisito, 
					r.requisito, r.codigo,
					r.descripcion, c.categoria, t.tipo
				FROM prg_requisitoxprog INNER JOIN 
					prg_requisito r ON prg_requisitoxprog.codrequisito=r.codrequisito inner join
					prg_requisito_cat c on r.codcategoria=c.codcategoria inner join 
					prg_requisito_tipo t on r.codtipo=t.codtipo
				WHERE id_programa=$id_programa AND r.flag='1'
				ORDER BY r.requisito" ;
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_rol_habilitado($id_pais){
		unset($this->listas);
		$sql="SELECT id_rol,nombre 
				FROM prg_roles 
				WHERE flag='1' AND flgcalifica='1' 
				ORDER BY nombre" ;
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	 public function delete_rol_habilitado($id_programa,$id_modulo,$id_rol,$codrequisito){
	  
        $sql="delete from prg_requisitoxrolxprog
               where id_programa=$id_programa and id_modulo=$id_modulo 
				and id_rol=$id_rol and codrequisito=$codrequisito";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function insert_rol_habilitado($id_programa,$id_modulo,$id_rol,$codrequisito){
	  
        $sql="insert into prg_requisitoxrolxprog(id_programa,id_modulo,id_rol,codrequisito)
				values ($id_programa,$id_modulo ,$id_rol,$codrequisito) ";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function selec_rolxprogxmodxreq($id_programa,$id_modulo){
		unset($this->listas);
		$sql="SELECT concat_ws('_',codrequisito,id_rol) as llave
				FROM prg_requisitoxrolxprog	WHERE id_programa=$id_programa and id_modulo=$id_modulo";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

	public function selec_modulobyaud($id_auditor,$id_pais){
		unset($this->listas);
		$sql="SELECT DISTINCT p.id_modulo, p.modulo 
			FROM prg_prog_modulo p INNER JOIN prg_auditor_programa_modulo m ON p.id_modulo=m.id_modulo 
				AND m.id_auditor=$id_auditor
			WHERE p.flag='1' AND p.id_pais='$id_pais'";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_rolbyaud($id_auditor){
		unset($this->listas);
		$sql="SELECT r.id_rol, r.nombre AS rol 
				FROM prg_roles r INNER JOIN prg_auditorxrol a ON r.id_rol=a.id_rol
				WHERE r.flag='1' AND a.id_auditor=$id_auditor";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_rolbypais(){
		unset($this->listas);
		$sql="SELECT r.id_rol, r.nombre AS rol 
				FROM prg_roles r 
				WHERE r.flag='1' and flgcalifica='1'
				order by nombre";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_reqbypais($id_pais){
		unset($this->listas);
		$sql="SELECT codrequisito, CONCAT_WS(' ',codigo,requisito) AS requisito
				FROM prg_requisito 
				WHERE flag='1' AND id_pais='$id_pais'
				ORDER BY 2";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function eliminadata(){
		
		$sql="truncate prg_requisitoxrolxprog";
		$consulta=$this->db->execute($sql);
        
		$sql="truncate prg_requisito";
		$consulta=$this->db->execute($sql);
		
		$sql="truncate prg_requisitoxprog";
		$consulta=$this->db->execute($sql);
		
		$sql="truncate prg_programa_calif";
		$consulta=$this->db->execute($sql);
		
		$sql="truncate prg_requisito_usuario";
		$consulta=$this->db->execute($sql);
		
		return $consulta;	
		
	}
	
	// reporte autorizado
	
	public function selec_usuario_autorizado(){
		unset($this->listas);
		$sql="SELECT vista.rol,programa,modulo,usuario_califica,fullusuario ,
				IFNULL(DATE_FORMAT(fechacal,'%d/%m/%Y'),'') AS fechacal,
				CASE WHEN tot_req_eval=tt THEN IFNULL(DATE_FORMAT(vigencia,'%d/%m/%Y'),'') ELSE '' END AS vigencia,
				grupo,pais,
				CASE WHEN tot_req_eval=tt THEN 'AUTORIZADO' ELSE 'NO AUTORIZADO' END AS estadod,
				CASE WHEN tot_req_eval=tt THEN 'pkuriyama' ELSE '' END AS usuario,
				CASE WHEN vigencia IS NULL THEN 'NO VENCE' ELSE TO_DAYS(vigencia)-TO_DAYS(NOW()) END AS dias  
			FROM (
				SELECT COUNT(prg_requisito.codrequisito) AS totreq,
					prg_roles.nombre AS rol,
					CONCAT_WS('_',rr.id_rol,rr.id_programa,rr.id_modulo) AS llave,
					prg_programa.descripcion AS programa,
					rr.id_rol,rr.id_programa, 
					pmm.modulo,
					COUNT(rr.codrequisito) AS tot_req_eval,
					COUNT(DISTINCT ruu.codrequisito) AS tt,
					
					GROUP_CONCAT(rr.codrequisito) AS req_eval,
					
					IFNULL(usuario_califica,'') usuario_califica,
					a.id_auditor,
					CONCAT_WS(' ', a.nombre,a.apepaterno, a.apematerno) AS fullusuario,
					MAX(fechacal) AS fechacal, 
					MAX(vigencia) AS vigencia,
					prg_programa_grupo.grupo,
					pa.nombre AS pais
				FROM prg_requisito INNER JOIN
					prg_requisitoxrolxprog rr ON prg_requisito.codrequisito=rr.codrequisito  INNER JOIN
					prg_roles ON rr.id_rol=prg_roles.id_rol INNER JOIN 
					prg_programa ON rr.id_programa=prg_programa.id_programa  INNER JOIN
					prg_prog_modulo pmm ON rr.id_modulo=pmm.id_modulo INNER JOIN
					prg_auditor_programa_modulo apm ON rr.id_programa=apm.id_programa AND rr.id_modulo=apm.id_modulo AND rr.id_rol=apm.id_rol INNER JOIN
					prg_auditorxrol xr ON apm.id_rol=xr.id_rol AND apm.id_auditor=xr.id_auditor LEFT JOIN
					prg_requisito_usuario ruu ON rr.codrequisito=ruu.codrequisito AND codestado='CALIFICADO' 
						AND ruu.id_auditor=apm.id_auditor AND (TO_DAYS(ruu.vigencia)>= TO_DAYS(NOW()) OR ruu.vigencia IS NULL) INNER JOIN
					prg_auditor a ON apm.id_auditor=a.id_auditor
					INNER JOIN prg_programa_grupo ON prg_programa.id_grupoprograma=prg_programa_grupo.id_grupoprograma 
					INNER JOIN prg_paises pa ON prg_programa.id_pais=pa.id_pais 
				WHERE prg_requisito.flag='1' AND prg_programa.flag='1' -- AND apm.id_auditor=$id_auditor
				GROUP BY 3
			) AS vista
			ORDER BY fullusuario";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
}
?>

