<?php
class prg_auditor_model{
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

	public function select_auditor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT 
				Auditor.id_auditor, Auditor.nombre, Auditor.apepaterno, Auditor.apematerno, 
				IFNULL(Auditor.nom_usuario,'') AS nom_usuario, Auditor.codigo,
				Auditor.iniciales, Auditor.email, Auditor.movil, Auditor.telefono, Auditor.color, Auditor.colortexto, Auditor.dni, 
				Auditor.pasaporte, Usuario.usuario, Usuario.id_rol, IFNULL(Auditor.costo,'') AS costo,  
				Auditor.id_pais, prg_roles.nombre AS roles,
				CONCAT_WS(' ',Auditor.nombre, Auditor.apepaterno,Auditor.apematerno) AS nombreCompleto ,
				case flgcomercial when '1' then 'Si' else 'No' end as dsccomercial,
				case flgemailcuota when '1' then 'Si' else 'No' end as dsccuota,
				case flgemailvencido when '1' then 'Si' else 'No' end as dscvencido,
				case flgemailfactura when '1' then 'Si' else 'No' end as dscfactura,
				flgstatus,flgtipo, case flgstatus when '1' then 'Activo' else 'No activo' end as dscestatus,
				GROUP_CONCAT(prg_region.descripcion,'') AS region,
				azuread
			 FROM prg_auditor AS Auditor LEFT JOIN 
				prg_usuarios AS Usuario ON Usuario.id_auditor = Auditor.id_auditor AND Usuario.flag = '1' LEFT JOIN
				prg_roles ON Usuario.id_rol=prg_roles.id_rol
				LEFT JOIN prg_auditor_region ON  Auditor.id_auditor=prg_auditor_region.id_auditor
				LEFT JOIN prg_region ON  prg_auditor_region.id_region=prg_region.id_region
			 WHERE  Auditor.flag = '1' 
				AND Auditor.id_auditor <> 0 AND Usuario.id_auditor > 0 AND Usuario.flag = '1'  $searchQuery ";
		$sql.=" GROUP BY Auditor.id_auditor
				order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_auditor_select($id_pais){
		unset($this->listas);
		$sql="SELECT 
				Auditor.id_auditor,
				CONCAT_WS(' ',Auditor.nombre, Auditor.apepaterno,Auditor.apematerno) AS nombreCompleto 
			 FROM prg_auditor AS Auditor inner JOIN 
				prg_usuarios AS Usuario ON Usuario.id_auditor = Auditor.id_auditor 
			 WHERE  Auditor.flag = '1' 
				AND Auditor.id_auditor <> 0 AND Usuario.id_auditor > 0 AND Usuario.flag = '1'  
				and Auditor.id_pais='$id_pais' and flgstatus=1
			order by 2 asc";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_auditor_selectall($id_pais){
		unset($this->listas);
		$sql="SELECT 
				Auditor.id_auditor,
				CONCAT_WS(' ',Auditor.nombre, Auditor.apepaterno,Auditor.apematerno) AS nombreCompleto 
			 FROM prg_auditor AS Auditor inner JOIN 
				prg_usuarios AS Usuario ON Usuario.id_auditor = Auditor.id_auditor 
			 WHERE  Auditor.flag = '1' 
				AND Auditor.id_auditor <> 0 AND Usuario.id_auditor > 0 AND Usuario.flag = '1'  
				and Auditor.id_pais='$id_pais'
			order by 2 asc";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_auditor_selectFull($id_pais){
		unset($this->listas);
		$sql="SELECT 
				Auditor.id_auditor,Auditor.iniciales ,
				CONCAT_WS(' ',Auditor.nombre, Auditor.apepaterno,Auditor.apematerno) AS nombreCompleto 
			 FROM prg_auditor AS Auditor inner JOIN 
				prg_usuarios AS Usuario ON Usuario.id_auditor = Auditor.id_auditor 
			 WHERE  Auditor.flag = '1' 
				AND Auditor.id_auditor <> 0 AND Usuario.id_auditor > 0 AND Usuario.flag = '1'  
				and Auditor.id_pais='$id_pais' and Auditor.flgstatus=1
			order by 2 asc";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_auditor($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
             prg_auditor AS Auditor LEFT JOIN 
				prg_usuarios AS Usuario ON Usuario.id_auditor = Auditor.id_auditor AND Usuario.flag = '1'
			 WHERE  Auditor.flag = '1' 
				AND Auditor.id_auditor <> 0 AND Usuario.id_auditor > 0 AND Usuario.flag = '1'  $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_auditor($id_auditor){
		
		$sql="SELECT Auditor.id_auditor, Auditor.id_region, Auditor.nombre, Auditor.apepaterno, Auditor.apematerno, Auditor.nom_usuario, 
				Auditor.codigo, Auditor.iniciales, Auditor.email, Auditor.movil, Auditor.telefono, Auditor.dni, Auditor.pasaporte, 
				Auditor.foto, Auditor.color, Auditor.colortexto, Auditor.flag, Auditor.skype, Auditor.fec_nacimiento, Auditor.id_pais, 
				Auditor.costo, Auditor.def_programa, (CONCAT_WS(' ',Auditor.nombre, Auditor.apepaterno,Auditor.apematerno)) AS 
				Auditor__nombreCompleto, Usuario.id_usuario, Usuario.id_auditor, Usuario.id_rol, Usuario.nombres, 
				Usuario.usuario, Usuario.clave, Usuario.contrasena, Usuario.tipo, Usuario.flag, Usuario.fecha_registro, 
				Usuario.flgcomercial, Usuario.flgemailcuota, Usuario.flgadminsli, Usuario.flgemailvencido, Usuario.flgemailfactura, Usuario.ofi_vendedor,
				ifnull(group_concat(distinct ap.id_programa),'') as gprograma,
				flgstatus,flgtipo, ifnull(Usuario.azuread,'') azuread, ifnull(flgudcal,0) flgudcal
			FROM prg_auditor AS Auditor LEFT JOIN 
				prg_usuarios AS Usuario ON Usuario.id_auditor = Auditor.id_auditor AND Usuario.flag = '1' left join
				prg_auditor_programa ap on Auditor.id_auditor=ap.id_auditor
			WHERE Auditor.id_auditor = $id_auditor 
			group by Auditor.id_auditor";
			
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_one_auditor_byuser($usuario){
		
		$sql="SELECT Auditor.id_auditor, Auditor.id_region, Auditor.nombre, Auditor.apepaterno, Auditor.apematerno, Auditor.nom_usuario, 
				Auditor.codigo, Auditor.iniciales, Auditor.email, Auditor.movil, Auditor.telefono, Auditor.dni, Auditor.pasaporte, 
				Auditor.foto, Auditor.color, Auditor.colortexto, Auditor.flag, Auditor.skype, Auditor.fec_nacimiento, Auditor.id_pais, 
				Auditor.costo, Auditor.def_programa, (CONCAT_WS(' ',Auditor.nombre, Auditor.apepaterno,Auditor.apematerno)) AS 
				Auditor__nombreCompleto, Usuario.id_usuario, Usuario.id_auditor, Usuario.id_rol, Usuario.nombres, 
				Usuario.usuario, Usuario.clave, Usuario.contrasena, Usuario.tipo, Usuario.flag, Usuario.fecha_registro, 
				Usuario.flgcomercial, Usuario.flgemailcuota, Usuario.flgadminsli, Usuario.flgemailvencido, Usuario.flgemailfactura, Usuario.ofi_vendedor,
				ifnull(group_concat(ap.id_programa),'') as gprograma,
				flgstatus,flgtipo, ifnull(Usuario.azuread,'') azuread
			FROM prg_auditor AS Auditor LEFT JOIN 
				prg_usuarios AS Usuario ON Usuario.id_auditor = Auditor.id_auditor AND Usuario.flag = '1' left join
				prg_auditor_programa ap on Auditor.id_auditor=ap.id_auditor
			WHERE Usuario.usuario = '$usuario'
			group by Auditor.id_auditor";
			
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_one_auditorRol_byuser($id_auditor){
		
		$sql="SELECT * FROM prg_auditorxrol WHERE id_auditor=$id_auditor and  id_rol=1";

		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_data_rol($id_auditor){
		
		$sql="SELECT ifnull(group_concat(id_rol),'') as grol
				from prg_auditorxrol 
				WHERE id_auditor = $id_auditor 
				group by id_auditor";

		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_data_roldetalle($id_auditor){
		
		$sql="SELECT prg_roles.*
				from prg_auditorxrol inner join prg_roles on prg_auditorxrol.id_rol=prg_roles.id_rol
				WHERE id_auditor = $id_auditor";
			
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function selec_data_region($id_auditor){
		
		$sql="SELECT ifnull(group_concat(id_region),'') as gregion
				from prg_auditor_region 
				WHERE id_auditor = $id_auditor 
				group by id_auditor";
			
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_auditor($nombre,$apepaterno,$apematerno, $pasaporte,$dni,$id_region ,$def_programa,$codigo,$iniciales, $email, $telefono,$movil,$costo,$colortexto,$color,$id_rol,$usuariosis,$clave,$flgstatus,$flgtipo,$azuread,$id_pais,$usuario,$ip){

        $sql="insert into prg_auditor( nombre,apepaterno,apematerno,codigo, iniciales, email,pasaporte,dni,telefono,movil, colortexto, color,costo,def_programa,id_pais, id_region,flgstatus,flgtipo, flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica,fechaclave)
         values('$nombre','$apepaterno','$apematerno','$codigo','$iniciales', '$email','$pasaporte','$dni', '$telefono', '$movil', '$colortexto', '$color','$costo','$def_programa','$id_pais', '$id_region','$flgstatus','$flgtipo', '1','$usuario',now(),'$ip','$usuario',now(),'$ip',now())";
		echo $sql;
		$id_auditor=$this->db->executeIns($sql);

		$clave=md5($clave);
		$sql=" insert into prg_usuarios( id_auditor,id_rol,nombres,usuario,contrasena,tipo,flag, fecha_registro,azuread) 
				values ($id_auditor,'$id_rol','$nombre $apepaterno $apematerno','$usuariosis','$clave','u','1', now(),'$azuread')";

		$consulta=$this->db->executeIns($sql);
        return $id_auditor;
    }	


	// 060322 insertar relacion auditor x rol
	public function insert_auditorxrol($sqltxt,$id_auditor){

        $sql="delete from prg_auditorxrol where id_auditor=$id_auditor";
		$consulta=$this->db->execute($sql);

		$consulta=$this->db->execute($sqltxt);

        return $consulta;
    }

	public function insert_auditorxregion($sqltxt,$id_auditor){

        $sql="delete from prg_auditor_region where id_auditor=$id_auditor";
		$consulta=$this->db->execute($sql);

		$consulta=$this->db->execute($sqltxt);

        return $consulta;
    }


	// update usuario
    public function update_auditor($id_auditor,$nombre,$apepaterno,$apematerno, $pasaporte,$dni,$id_region ,$def_programa,$codigo,$iniciales, $email, $telefono,$movil,$costo,$colortexto,$color,$id_rol,$usuario2,$clave,$flgstatus,$flgtipo,$azuread,$id_pais,$usuario,$ip){
	   
        $sql="update prg_auditor 
				set 
				nombre='$nombre',apepaterno='$apepaterno',apematerno='$apematerno',
				codigo='$codigo', iniciales='$iniciales', email='$email',
				pasaporte='$pasaporte',dni='$dni',telefono='$telefono',
				movil='$movil', colortexto='$colortexto', color='$color',
				flgstatus='$flgstatus', flgtipo='$flgtipo',
				
				costo='$costo',def_programa='$def_programa', id_region='$id_region',
				usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_auditor=$id_auditor ";
		$consulta=$this->db->execute($sql);
		
		$sql="update prg_usuarios  set 
					id_rol=$id_rol,nombres='$nombre $apepaterno $apematerno',usuario='$usuario2',
					azuread='$azuread'
				where id_auditor=$id_auditor ";
		
		$consulta=$this->db->execute($sql);
		
		if($clave!=''){
			$clave=md5($clave);
			$sql="update prg_usuarios  set 
					contrasena='$clave',
					fechaclave=now()
				where id_auditor=$id_auditor ";
			
			$consulta=$this->db->execute($sql);
		}
		
        return $consulta;
    }	
	
	
	public function update_auditorClave($id_auditor,$nombre,$apepaterno,$apematerno, $pasaporte,$dni,$codigo,$iniciales, $email, $telefono,$movil,$clave,$id_pais,$usuario,$ip){
	   
        $sql="update prg_auditor 
				set 
				nombre='$nombre',apepaterno='$apepaterno',apematerno='$apematerno',
				codigo='$codigo', iniciales='$iniciales', email='$email',
				pasaporte='$pasaporte',dni='$dni',telefono='$telefono',	movil='$movil',
				usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_auditor=$id_auditor ";
		
		$consulta=$this->db->execute($sql);
		
		$sql="update prg_usuarios  set 
					nombres='$nombre $apepaterno $apematerno'
				where id_auditor=$id_auditor ";
		
		$consulta=$this->db->execute($sql);
		
		if($clave!=''){
			$clave=md5($clave);
			$sql="update prg_usuarios  set 
					contrasena='$clave',
					fechaclave=now()
				where id_auditor=$id_auditor";
		
			$consulta=$this->db->execute($sql);
		}
		
        return $consulta;
    }	


	public function update_auditorFoto($id_auditor,$foto){
		$sql="update prg_auditor set foto='$foto' where id_auditor=$id_auditor";
		$consulta=$this->db->execute($sql);
		
        return $consulta;
	
	}

	public function update_usuario_accion($id_usuario,$flgcomercial,$flgemailcuota,$flgemailvencido,$flgemailfactura,$flgadminsli,$flgudcal){
	
		$sql="UPDATE prg_usuarios SET
					flgadminsli='$flgadminsli',
					flgcomercial='$flgcomercial',
					flgemailcuota='$flgemailcuota',
					flgemailvencido='$flgemailvencido',
					flgemailfactura='$flgemailfactura',
					flgudcal='$flgudcal'
		 WHERE id_usuario=$id_usuario";
		 
		$consulta=$this->db->execute($sql);
		
        return $consulta;
	
	}

    public function delete_auditor($id_auditor){
	   
        $sql="update prg_auditor set flag='0' where id_auditor=$id_auditor";
		$consulta=$this->db->execute($sql);
		
		$sql="update prg_usuarios set flag='0' where id_auditor=$id_auditor";
		$consulta=$this->db->execute($sql);
		
        return $consulta;
    }	

	public function delete_auditorPrograma($id_auditor){
	   
        $sql="delete from prg_auditor_programa where id_auditor=$id_auditor";
		$consulta=$this->db->execute($sql);
		
        return $consulta;
    }
	public function delete_auditorProgramaModulo($id_auditor){
	   
        $sql="delete from prg_auditor_programa_modulo where id_auditor=$id_auditor";
		$consulta=$this->db->execute($sql);
		
        return $consulta;
    }

	
	public function select_auditorPrograma($id_auditor){
	   
        $sql="select GROUP_CONCAT( CONCAT_WS('_',id_rol,id_programa)) AS gprograma 
				from prg_auditor_programa where id_auditor=$id_auditor";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
    }

	public function insert_auditorPrograma($id_auditor,$id_programa,$id_rol){
	   
        $sql="insert into prg_auditor_programa(id_auditor,id_programa,id_rol) values ($id_auditor,$id_programa,$id_rol)";
		
		$consulta=$this->db->executeIns($sql);
		
        return $consulta;
    }
	
	public function insert_auditorProgramaModulo($id_auditor,$id_programa,$id_rol,$id_modulo){
	   
        $sql="insert into prg_auditor_programa_modulo(id_auditor,id_programa,id_rol,id_modulo) values ($id_auditor,$id_programa,$id_rol,$id_modulo)";
		
		$consulta=$this->db->executeIns($sql);
		
        return $consulta;
    }

	public function select_auditorByID($id_auditor,$id_pais){
		unset($this->listas);
	
		$sql="SELECT DISTINCT prg_auditor.id_auditor, CONCAT_WS(' ',nombre,apepaterno,apematerno) AS nombres 
				FROM prg_auditor
				WHERE  (prg_auditor.id_pais='$id_pais'  or prg_auditor.id_auditor='$id_auditor') and prg_auditor.flag=1 
				and prg_auditor.flgstatus=1 and prg_auditor.id_auditor>0
				ORDER BY 2";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_audixprogramaxmodulo($id_auditor){
		unset($this->listas);
	
		$sql="SELECT prg_roles.nombre AS rol, prg_programa.descripcion, prg_programa.id_programa,  prg_roles.id_rol,
					IFNULL(GROUP_CONCAT(m.id_modulo),'') AS gmodulo
				FROM prg_auditor_programa a INNER JOIN prg_programa ON a.id_programa=prg_programa.id_programa
					INNER JOIN prg_roles ON a.id_rol=prg_roles.id_rol
					LEFT JOIN prg_auditor_programa_modulo m ON a.id_rol=m.id_rol AND a.id_programa=m.id_programa AND a.id_auditor=m.id_auditor
				WHERE a.id_auditor=$id_auditor
				GROUP BY a.id_rol, a.id_programa 
				ORDER BY 1,2";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_one_auditorSimpl($id_auditor){
		unset($this->listas);
	
		$sql="SELECT *, CONCAT_WS(' ',nombre,apepaterno,apematerno) AS nombres 
				FROM prg_auditor
				WHERE  id_auditor='$id_auditor'";
		
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
		
	}
	
	public function select_rol_by_auditor($id_auditor){
		$sql="SELECT prg_roles.nombre AS rol FROM prg_roles INNER JOIN prg_usuarios ON prg_roles.id_rol = prg_usuarios.id_rol
				WHERE id_auditor='$id_auditor'";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
		
	}
	
	public function select_auditorForProyCome($id_pais,$codejecutivo=null){
		unset($this->listas);
	
		$sql="SELECT nombres,id_usuario
				FROM prg_usuarios  INNER JOIN prg_auditor ON prg_usuarios.id_auditor  = prg_auditor.id_auditor
				WHERE prg_usuarios.flag='1' AND prg_auditor.flag='1' AND id_pais='$id_pais' ";
		if(!empty($codejecutivo))
			$sql.="		AND (flgcomercial='1' or id_usuario=$codejecutivo) ";
		else
			$sql.="		AND flgcomercial='1'";
		
		$sql.="		ORDER BY nombres";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function trigert_actividad($id_auditor){
	   
        $sql="INSERT INTO prg_auditoractividad(flgfinalizo,oferta,id_actividad,id_programa,id_pais,
			nota,porcentaje,project_id,id_auditor,flag,
			fecha,ref_pais,fechac,ciclo,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica) 
			SELECT '','','62','851','175','',100,'',id_auditor,'1',fecha,
				prg_auditor.id_pais,NULL,'','feriado',NOW(),'trigert',prg_auditor.usuario_ingreso
			FROM prg_auditor INNER JOIN prg_feriado ON prg_feriado.id_pais=prg_auditor.id_pais
			WHERE prg_feriado.flag='1'  AND id_auditor=$id_auditor";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function trigert_calendario($id_auditor){
		$sql = "insert into prg_calendario (
			id_pais,id_tipoactividad, id_proyecto,nro_muestra, por_dia, id_auditor, 
			 monto_dolares, monto_soles, id_estadoactividad, audit_id, id_type, observacion, 
			 hora_inicial, hora_final, dia_inicio, mes_inicio, anio_inicio, dia_fin, mes_fin,
			 anio_fin, inicio_evento, fin_evento, is_sabado, is_domingo, hora_inicio, hora_fin,
			 asunto, id_calendario, id_asignacion_viaticos, flag_rendicion, 
			 usuario_ingreso,ip_ingreso,fecha_ingreso,flag	)
			 
		 SELECT prg_auditor.id_pais,378, '','', '', id_auditor,
		 '', '', 1, '', '', '', 
		 '01:00', '23:30', DAY(fecha), MONTH(fecha), YEAR(fecha), DAY(fecha), MONTH(fecha), YEAR(fecha),
		 CONCAT_WS(' ',fecha,'01:00'),  CONCAT_WS(' ',fecha,'23:30'), '0', '0', 60, 1410,
		 prg_feriado.descripcion, 2147483647, '','1', 
		 prg_auditor.usuario_ingreso,'trigert',NOW(),'1'
		 FROM prg_auditor INNER JOIN prg_feriado ON prg_feriado.id_pais=prg_auditor.id_pais
			WHERE prg_feriado.flag='1' AND flgstatus='1' AND id_auditor=$id_auditor	 ";
		$consulta=$this->db->execute($sql);
	
		return $consulta;
	}
	
}
?>